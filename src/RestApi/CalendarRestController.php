<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Settings\SettingsGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Jsvrcek\ICS\Model\CalendarEvent;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Welp\IcalBundle\Factory\Factory;
use Welp\IcalBundle\Response\CalendarResponse;

/**
 * Provides endpoints for exporting pickup dates to iCal and managing access tokens.
 */
class CalendarRestController extends AbstractFOSRestController
{
	private Session $session;
	private SettingsGateway $settingsGateway;
	private ProfileGateway $profileGateway;
	private Factory $icalFactory;

	private const TOKEN_LENGTH_IN_BYTES = 10;

	public function __construct(
		Session $session,
		SettingsGateway $settingsGateway,
		ProfileGateway $profileGateway,
		Factory $icalFactory
	) {
		$this->session = $session;
		$this->settingsGateway = $settingsGateway;
		$this->profileGateway = $profileGateway;
		$this->icalFactory = $icalFactory;
	}

	/**
	 * Returns the user's current access token.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="404", description="User does not have a token")
	 * @OA\Tag(name="calendar")
	 *
	 * @Rest\Get("calendar/token")
	 */
	public function getTokenAction(): Response
	{
		$userId = $this->session->id();
		if (!$userId) {
			throw new HttpException(401);
		}

		$token = $this->settingsGateway->getApiToken($userId);
		if (empty($token)) {
			throw new HttpException(404);
		}

		return $this->handleView($this->view(['token' => $token]));
	}

	/**
	 * Creates a new random access token for the user. An existing token will be overwritten. Returns
	 * the created token.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Tag(name="calendar")
	 *
	 * @Rest\Put("calendar/token")
	 */
	public function createTokenAction(): Response
	{
		$userId = $this->session->id();
		if (!$userId) {
			throw new HttpException(401);
		}

		$token = bin2hex(openssl_random_pseudo_bytes(self::TOKEN_LENGTH_IN_BYTES));
		$this->settingsGateway->removeApiToken($userId);
		$this->settingsGateway->saveApiToken($userId, $token);

		return $this->handleView($this->view(['token' => $token]));
	}

	/**
	 * Removes the user's token. If the user does not have a token nothing will happen.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Tag(name="calendar")
	 *
	 * @Rest\Delete("calendar/token")
	 */
	public function deleteTokenAction(): Response
	{
		$userId = $this->session->id();
		if (!$userId) {
			throw new HttpException(401);
		}

		$this->settingsGateway->removeApiToken($userId);

		return $this->handleView($this->view());
	}

	/**
	 * Returns the user's future pickup dates as iCal.
	 *
	 * @OA\Parameter(name="token", in="path", @OA\Schema(type="string"), description="Access tken")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions or invalid token.")
	 * @OA\Tag(name="calendar")
	 *
	 * @Rest\Get("calendar/{token}")
	 * @Rest\QueryParam(name="token", description="Access token")
	 */
	public function listPickupDatesAction(string $token): Response
	{
		// check access token
		$userId = $this->settingsGateway->getUserForToken($token);
		if (!$userId) {
			throw new HttpException(403);
		}

		// create iCal of all future pickup dates
		$dates = $this->profileGateway->getNextDates($userId);
		$calendar = $this->icalFactory->createCalendar();
		foreach ($dates as $date) {
			$calendar->addEvent($this->createPickupEvent($date, $userId));
		}

		return new CalendarResponse($calendar, 200, []);
	}

	private function createPickupEvent(array $pickup, int $userId): CalendarEvent
	{
		$start = Carbon::createFromTimestamp($pickup['date_ts']);

		$summary = $pickup['betrieb_name'] . ' Abholung';
		$status = 'CONFIRMED';
		if (!$pickup['confirmed']) {
			$summary .= ' (unbestätigt)';
			$status = 'TENTATIVE';
		}

		$event = $this->icalFactory->createCalendarEvent();
		$event->setStart($start);
		$event->setEnd($start->clone()->addMinutes(30));
		$event->setSummary($summary);
		$event->setUid($userId . $pickup['date_ts'] . '@fetch.foodsharing.de');
		$event->setDescription('foodsharing Abholung bei ' . $pickup['betrieb_name']);
		$event->setUrl(BASE_URL . '/?page=fsbetrieb&id=' . $pickup['betrieb_id']);
		$event->setStatus($status);

		return $event;
	}
}
