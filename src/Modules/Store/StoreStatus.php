<?php

namespace Foodsharing\Modules\Store;

class StoreStatus
{
	// see db/sql: SELECT * FROM fs_betrieb_status

	const NO_CONTACT = 1; // "Es besteht noch kein Kontakt"
	const IN_NEGOTIATION = 2; // "Verhandlungen laufen"
	const COOPERATION_STARTING = 3; // "Betrieb ist bereit zu spenden :)"
	const DOES_NOT_WANT_TO_WORK_WITH_US = 4; // "Will nicht kooperieren"
	const COOPERATION_ESTABLISHED = 5; // "Betrieb kooperiert bereits" (mit uns)
	const GIVES_TO_OTHER_CHARITY = 6; // "Spendet an Tafel etc. und wirft nichts weg"
}
