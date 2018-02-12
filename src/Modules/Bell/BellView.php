<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Modules\Core\View;

class BellView extends View
{
	public function bellList($bells)
	{
		$list = '';

		if (!empty($bells)) {
			foreach ($bells as $b) {
				$unread = 0;

				if ($b['seen'] == 0) {
					$unread = 1;
				}

				$attr = ' href="#" onclick="return false;"';
				if (!empty($b['attr'])) {
					$attr = '';
					foreach ($b['attr'] as $key => $a) {
						$attr .= ' ' . $key . '="' . $a . '"';
					}
				}

				$icon = '<i class="fa fa-bullhorn"></i>';
				if (!empty($b['icon'])) {
					if (substr($b['icon'], 0, 1) == '/') {
						$icon = '<span class="pics"><img src="' . $b['icon'] . '" /></span>';
					} else {
						$icon = '<span class="icon"><i class="' . $b['icon'] . '"></i></span>';
					}
				}

				$close = '';
				if ($b['closeable'] == 1) {
					$close = '<span onclick="info.delBell(' . $b['id'] . ');return false;" class="button close"><i class="fa fa-close"></i></span>';
				}

				$list .= '<li id="belllist-' . $b['id'] . '" class="unread-' . $unread . '"><a' . $attr . '>' . $close . $icon . '<span class="names">' . $this->func->sv($b['name'], $b['vars']) . '</span><span class="msg">' . $this->func->sv($b['body'], $b['vars']) . '</span><span class="time">' . $this->func->niceDate($b['time_ts']) . '</span><span class="clear"></span></a></li>';
			}
		} else {
			$list = '<li class="noconv">' . v_info($this->func->s('no_bells')) . '</li>';
		}

		return $list;
	}
}
