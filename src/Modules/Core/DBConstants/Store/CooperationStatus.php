<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

/**
 * column `betrieb_status_id`
 * status of the cooperation between foodsharing and a store
 * INT(10)          UNSIGNED NOT NULL.
 */
class CooperationStatus
{
	public const UNCLEAR = 0; // "Status unklar"
	public const NO_CONTACT = 1; // "Es besteht noch kein Kontakt"
	public const IN_NEGOTIATION = 2; // "Verhandlungen laufen"
	public const COOPERATION_STARTING = 3; // "Betrieb ist bereit zu spenden :)"
	public const DOES_NOT_WANT_TO_WORK_WITH_US = 4; // "Will nicht kooperieren"
	public const COOPERATION_ESTABLISHED = 5; // "Betrieb kooperiert bereits" (mit uns)
	public const GIVES_TO_OTHER_CHARITY = 6; // "Spendet an Tafel etc. und wirft nichts weg"
	public const PERMANENTLY_CLOSED = 7; // "Betrieb existiert nicht mehr"
}
