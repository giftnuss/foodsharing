<?php

namespace Foodsharing\Modules\Store;

class StoreStatus
{
	// see: SELECT * FROM `fs_betrieb_status`

	const no_contact = 1; // "Es besteht noch kein Kontakt"
	const in_negotiation = 2; // "Verhandlungen laufen"
	const cooperation_starting = 3; // "Betrieb ist bereit zu spenden :)"
	const does_not_want_to_work_with_us = 4; // "Will nicht kooperieren"
	const cooperation_established = 5; // "Betrieb kooperiert bereits" (mit uns)
	const gives_to_other_charity = 6; // "Spendet an Tafel etc. und wirft nichts weg"
}
