<?php

namespace Foodsharing\Modules\Core\DBConstants\Region;

use Foodsharing\Modules\Settings\SettingsGateway;

/**
 * Types of region-specific settings. Corresponds to column 'option_type' in 'fs_region_options'.
 * See {@see RegionGateway::getRegionOption()} and {@see SettingsGateway::setRegionOption()}.
 */
class RegionOptionType
{
	public const ENABLE_REPORT_BUTTON = 1;
	public const ENABLE_MEDIATION_BUTTON = 2;
}
