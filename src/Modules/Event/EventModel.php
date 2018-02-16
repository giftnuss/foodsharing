<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\Model;

class EventModel extends Model
{
	public function addEvent($event)
	{
		$location_id = 0;
		if (isset($event['location_id'])) {
			$location_id = (int)$event['location_id'];
		}

		return $this->insert('
			INSERT INTO 	`' . PREFIX . 'event`
			(
				`foodsaver_id`,
				`bezirk_id`,
				`location_id`,
				`public`,
				`name`,
				`start`,
				`end`,
				`description`,
				`bot`,
				`online`
			)
			VALUES
			(
				' . (int)$this->func->fsId() . ',
				' . (int)$event['bezirk_id'] . ',
				' . (int)$location_id . ',
				' . (int)$event['public'] . ',
				' . $this->strval($event['name']) . ',
				' . $this->dateval($event['start']) . ',
				' . $this->dateval($event['end']) . ',
				' . $this->strval($event['description']) . ',
				0,
				' . (int)$event['online'] . '
			)
		');
	}

	public function deleteInvites($event_id)
	{
		return $this->del('
			DELETE FROM ' . PREFIX . 'foodsaver_has_event
			WHERE event_id = ' . (int)$event_id . '	
		');
	}

	public function updateEvent($id, $event)
	{
		$location_id = 0;
		if (isset($event['location_id'])) {
			$location_id = (int)$event['location_id'];
		}

		return $this->update('
			UPDATE 	
				`' . PREFIX . 'event`

			SET
				`location_id` = ' . (int)$location_id . ',
				`bezirk_id` = ' . (int)$event['bezirk_id'] . ',
				`public` = ' . (int)$event['public'] . ',
				`name` = ' . $this->strval($event['name']) . ',
				`start` = ' . $this->dateval($event['start']) . ',
				`end` = ' . $this->dateval($event['end']) . ',
				`description` = ' . $this->strval($event['description']) . ',
				`online` = ' . (int)$event['online'] . '
				
			WHERE
				`id` = ' . (int)$id . '
		');
	}

	public function getInviteStatus($event_id, $foodsaver_id)
	{
		$status = $this->qOne('
			SELECT `status` 
			FROM 	`' . PREFIX . 'foodsaver_has_event`
			WHERE 	event_id = ' . (int)$event_id . '	
			AND 	foodsaver_id = ' . (int)$foodsaver_id . '	
		');

		if ($status === false) {
			return -1;
		} else {
			return (int)$status;
		}
	}

	public function setInviteStatus($event_id, $status)
	{
		$this->update('
			UPDATE 	' . PREFIX . 'foodsaver_has_event
			SET 	`status` = ' . (int)$status . '
			WHERE 	foodsaver_id = ' . (int)$this->func->fsId() . '
			AND 	event_id = ' . (int)$event_id . '
		');

		return true;
	}

	public function addInviteStatus($event_id, $status)
	{
		$this->update('
			REPLACE INTO ' . PREFIX . 'foodsaver_has_event
			(`status`, `foodsaver_id`, `event_id`)
			VALUES
			(' . (int)$status . ', ' . (int)$this->func->fsId() . ', ' . (int)$event_id . ')
		');

		return true;
	}

	public function inviteBezirk($bezirk_id, $event_id, $invite_subs = false)
	{
		$b_sql = '= ' . (int)$bezirk_id;

		if ($invite_subs) {
			$bids = $this->getChildBezirke($bezirk_id);
			$b_sql = 'IN(' . implode(',', $bids) . ')';
		}

		if ($fsids = $this->qCol('
			SELECT 	foodsaver_id
			FROM	' . PREFIX . 'foodsaver_has_bezirk
			WHERE 	bezirk_id ' . $b_sql . ' 
			AND 	`active` = 1
		')
		) {
			$invited = array();
			if ($inv = $this->qCol(
				'
				SELECT foodsaver_id FROM ' . PREFIX . 'foodsaver_has_event
				WHERE event_id = ' . (int)$event_id
			)
			) {
				foreach ($inv as $i) {
					$invited[$i] = true;
				}
			}

			$sql = array();
			foreach ($fsids as $id) {
				if (!isset($invited[$id])) {
					$sql[(int)$id] = '(' . (int)$id . ',' . $event_id . ',0)';
				}
			}

			if (!empty($sql)) {
				return $this->sql('
					INSERT INTO ' . PREFIX . 'foodsaver_has_event
					(foodsaver_id,event_id,`status`)
					VALUES
					' . implode(',', $sql) . '
				');
			} else {
				return true;
			}
		}
	}
}
