<?php

namespace Foodsharing\Modules\Store;

class StoreStatus
{
	// see db/sql: SELECT * FROM fs_betrieb_status

	public const NO_CONTACT = 1; // "Es besteht noch kein Kontakt"
	public const IN_NEGOTIATION = 2; // "Verhandlungen laufen"
	public const COOPERATION_STARTING = 3; // "Betrieb ist bereit zu spenden :)"
	public const DOES_NOT_WANT_TO_WORK_WITH_US = 4; // "Will nicht kooperieren"
	public const COOPERATION_ESTABLISHED = 5; // "Betrieb kooperiert bereits" (mit uns)
	public const GIVES_TO_OTHER_CHARITY = 6; // "Spendet an Tafel etc. und wirft nichts weg"
}
