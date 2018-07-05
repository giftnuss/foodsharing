<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Core\Model;

class LoginModel extends Model
{
	/**
	 * @var LoginGateway
	 */
	private $loginGateway;

	/**
	 * @required
	 */
	public function setLoginGateway(LoginGateway $loginGateway)
	{
		$this->loginGateway = $loginGateway;
	}

	public function activate($email, $token)
	{
		if ((int)$this->update('UPDATE fs_foodsaver SET `active` = 1 WHERE email = ' . $this->strval($email) . ' AND `token` = ' . $this->strval($token)) > 0) {
			return true;
		}

		return false;
	}

	public function insertNewUser($data, $token)
	{
		/*
				 [iam] => org
				[name] => Peter
				[email] => peter@pan.de
				[pw] => 12345
				[avatar] => 5427fb55f3a5d.jpg
				[phone] => 02261889971
				[lat] => 48.0649838
				[lon] => 7.885475300000053
				[str] => Bauerngasse
				[nr] => 6
				[plz] => 79211
				[country] => DE
		*/

		return $this->insert('
			INSERT INTO 	`fs_foodsaver`
			(
				`rolle`,
				`type`,
				`active`,
				`plz`,
				`email`,
				`password`,
				`name`,
				`nachname`,
				`anschrift`,
				`geb_datum`,
				`handy`,
				`newsletter`,
				`geschlecht`,
				`anmeldedatum`,
				`stadt`,
				`lat`,
				`lon`,
				`token`,
				`photo`
			)
			VALUES
			(
				0,
				' . (int)$data['type'] . ',
				0,
				' . $this->strval($data['plz']) . ',
				' . $this->strval($data['email']) . ',
				' . $this->strval($this->loginGateway->password_hash($data['pw'])) . ',
				' . $this->strval($data['name']) . ',
				' . $this->strval($data['surname']) . ',
				' . $this->strval($data['str'] . ' ' . trim($data['nr'])) . ',
				' . $this->strval($data['birthdate']) . ',
				' . $this->strval($data['mobile_phone']) . ',
				' . (int)$data['newsletter'] . ',
				' . (int)$data['gender'] . ',
				NOW(),
				' . $this->strval($data['city']) . ',
				' . $this->strval($data['lat']) . ',
				' . $this->strval($data['lon']) . ',
				' . $this->strval($token) . ',
				' . $this->strval($data['avatar']) . '
			)');
	}

	public function checkResetKey($key)
	{
		return $this->qOne('SELECT `foodsaver_id` FROM `fs_pass_request` WHERE `name` = ' . $this->strval($key));
	}

	public function newPassword($data)
	{
		if ((int)strlen($data['pass1']) > 4) {
			if ($fsid = $this->qOne('SELECT `foodsaver_id` FROM `fs_pass_request` WHERE `name` = ' . $this->strval($data['k']))) {
				$this->del('DELETE FROM `fs_pass_request` WHERE `foodsaver_id` = ' . (int)$fsid);

				return $this->update('UPDATE `fs_foodsaver` SET `password` = ' . $this->strval($this->loginGateway->password_hash($data['pass1'])) . ',`passwd`=NULL,`fs_password`=NULL WHERE `id` = ' . (int)$fsid);
			}
		}

		return false;
	}

	public function addPassRequest($email, $mail = true)
	{
		if ($fs = $this->qRow('SELECT fs.`id`,fs.`email`,fs.`name`,fs.`geschlecht` FROM `fs_foodsaver` fs WHERE fs.deleted_at IS NULL AND fs.`email` = ' . $this->strval($email))) {
			$k = uniqid();
			$key = md5($k);

			$this->insert('
			REPLACE INTO 	`fs_pass_request`
			(
				`foodsaver_id`,
				`name`,
				`time`
			)
			VALUES
			(
				' . (int)$fs['id'] . ',
				' . $this->strval($key) . ',
				NOW()
			)');

			if ($mail) {
				$vars = array(
					'link' => BASE_URL . '/?page=login&sub=passwordReset&k=' . $key,
					'name' => $fs['name'],
					'anrede' => $this->func->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r')
				);

				$this->func->tplMail(10, $fs['email'], $vars);

				return true;
			} else {
				return $key;
			}
		}

		return false;
	}
}
