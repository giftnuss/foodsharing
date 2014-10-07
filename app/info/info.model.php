<?php
class InfoModel extends Model
{
	/**
	 * check if there are unread messages in conversation give back the conversation ids
	 *
	 * @return Ambigous <boolean, array >
	 */
	public function checkConversationUpdates()
	{
		return $this->qCol('SELECT conversation_id FROM '.PREFIX.'foodsaver_has_conversation WHERE foodsaver_id = '.(int)fsId().' AND unread = 1');
	}
}