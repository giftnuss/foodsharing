<?php
function userdataTestBotHamburg() {
  global $_SESSION;
  $_SESSION['client'] = array('botschafter' => array(array('bezirk_id' => 31, 'type' => 3)));
}

class funcTest extends PHPUnit_Framework_TestCase
{
  protected $backupGlobalsBlacklist = array('db');
  public function setUp()
  {
    global $db;
    $this->db = $db;
  }

  public function testIsBotForADirect()
  {
    userdataTestBotHamburg();
    $this->assertTrue(isBotForA(array(31)));
  }

  public function testIsBotForAHierarchy()
  {
    userdataTestBotHamburg();
    // 467 is Hamburg->HH-Nord->Uhlenhorst
    $this->assertFalse(isBotForA(array(467)));
    // now enable hierarchical search
    $this->assertTrue(isBotForA(array(467), false, true));
  }
}

