<?php
class BetrieblistsControl extends ConsoleControl
{   
    private $model;
    
    public function __construct()
    {       
		error_reporting(E_ALL);
		ini_set('display_errors','1');
        $this->model = new BetrieblistsModel();
    }
    
    public function updateconversations()
    {
        $betriebe = $this->model->q('SELECT id, `name`, team_conversation_id, springer_conversation_id FROM fs_betrieb');
        foreach ($betriebe as $betrieb)
        {
            $cid = $betrieb['team_conversation_id'];
            if(is_null($betrieb['team_conversation_id']))
            {
                $cid = $this->model->insert('INSERT INTO fs_conversation (`name`, `locked`) VALUES("Team '.$betrieb['name'].'", 1)');
                if($cid > 0)
                {
                    $this->model->sql('UPDATE fs_betrieb SET team_conversation_id = '.$cid.' WHERE id = '.$betrieb['id']);
                }
            }
            $sid = $betrieb['springer_conversation_id'];
            if(is_null($betrieb['springer_conversation_id']))
            {
                $sid = $this->model->insert('INSERT INTO fs_conversation (`name`, `locked`) VALUES("Springer '.$betrieb['name'].'", 1)');
                if($sid > 0)
                {
                    $this->model->sql('UPDATE fs_betrieb SET springer_conversation_id = '.$sid.' WHERE id = '.$betrieb['id']);
                }
            }

            echo "Updating ".$betrieb['name']." (C: $cid, S: $sid)\n";
            $team = $this->model->getBetriebTeam($betrieb['id']);
            $springer = $this->model->getBetriebSpringer($betrieb['id']);
            $teamIds = array();
            foreach ($team as $user)
            {
              $teamIds[] = $user['id'];
            }
            $springerIds = array();
            if($springer) {
              foreach($springer as $user)
              {
                $springerIds[] = $user['id'];
              }
            }
            $msg = loadModel('msg');
            $msg->setConversationMembers($cid, $teamIds);
            $msg->setConversationMembers($sid, $springerIds);
        }
    }
}
