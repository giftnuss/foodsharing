<?php

namespace Foodsharing\Modules\Event;

class InvitationStatus
{
	const invited = 0; // invited
	const accepted = 1; // will join
	const maybe = 2; // might join
	const wont_join = 3; // will not join (but has been invited)
}
