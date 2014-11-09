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
            $conversation = $betrieb['team_conversation_id'];
            if(is_null($betrieb['team_conversation_id']))
            {
                $conversation = $this->model->insert('INSERT INTO fs_conversation (`name`, `locked`) VALUES("Team '.$betrieb['name'].'", 1)');
                if($conversation > 0)
                {
                    $this->model->sql('UPDATE fs_betrieb SET team_conversation_id = '.$conversation.' WHERE id = '.$betrieb['id']);
                }
            }

            echo "Updating ".$betrieb['name']." (C: $conversation)\n";
            $team = $this->model->getBetriebTeam($betrieb['id']);
            $q = "";
            $first = true;
            foreach ($team as $user)
            {
                if(!$first)
                {
                    $q .= ",";
                }
                $q .= "(".$user['id'].", $conversation, 0)";
                $first = false;
                echo "Inserted user".$user['name']."\n";
            }
            $this->model->sql('INSERT IGNORE INTO fs_foodsaver_has_conversation (foodsaver_id, conversation_id, unread) VALUES '.$q);
        }
    }
}
