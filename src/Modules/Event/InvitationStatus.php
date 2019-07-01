<?php

namespace Foodsharing\Modules\Event;

class InvitationStatus
{
	const INVITED = 0; // invited
	const ACCEPTED = 1; // will join
	const MAYBE = 2; // might join
	const WONT_JOIN = 3; // will not join (but has been invited)
}
