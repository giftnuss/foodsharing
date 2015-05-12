<?php
class ManualTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobalsBlacklist = array('db');
	public function setUp()
	{
		global $db;
		$this->db = $db;
	}
  /* Hamburg is in Europa->Deutschland->Hamburg, so we have
  Europa, Deutschland, Hamburg as parents */
	public function testParentBezirkeHamburg()
	{
		$hh_parents = $this->db->getParentBezirke(31);
		$this->assertCount(3, $hh_parents);
		$this->assertTrue(in_array(31, $hh_parents));
		$this->assertTrue(in_array(1, $hh_parents));
		$this->assertTrue(in_array(741, $hh_parents));
	}

	/* Same as above but array also containing one parent */
	public function testParentBezirkeHamburgArray()
	{
		$hh_parents = $this->db->getParentBezirke(31, 1);
		$this->assertCount(3, $hh_parents);
		$this->assertTrue(in_array(31, $hh_parents));
		$this->assertTrue(in_array(1, $hh_parents));
		$this->assertTrue(in_array(741, $hh_parents));
	}

	public function testParentBezirkeUhlenhorst()
	{
		$hh_parents = $this->db->getParentBezirke(467);
		$this->assertCount(5, $hh_parents);
		$this->assertTrue(in_array(31, $hh_parents));
		$this->assertTrue(in_array(1, $hh_parents));
		$this->assertTrue(in_array(741, $hh_parents));
		$this->assertTrue(in_array(226, $hh_parents));
		$this->assertTrue(in_array(467, $hh_parents));
	}

	public function testGetBiebs()
	{
		$uhlenhorst_biebs = $this->db->getBiebIds(467);
		/* a bit stupid: Matthias is an uhlenhorst bieb, raphael not :D */
		$this->assertTrue(in_array(7955, $uhlenhorst_biebs));
		$this->assertFalse(in_array(56, $uhlenhorst_biebs));

		$hh_biebs = $this->db->getBiebIds(31);
		$this->assertTrue(in_array(7955, $hh_biebs));
	}

	public function testGetBots()
	{
		$uhlenhorst_bots = $this->db->getBotIds(467);
		if($uhlenhorst_bots === false)
		{
			$uhlenhorst_bots = array();
		}
		$this->assertFalse(in_array(7955, $uhlenhorst_bots));
		$this->assertCount(0, $uhlenhorst_bots);
	}

	public function testUpdateGroupMembers()
	{
		$id = 9999999;
		/* cleanup first... */
		$cnt = $this->db->updateGroupMembers($id, array(), 1);

		$cnt = $this->db->updateGroupMembers($id, array(7955), 1);
		$this->assertTrue(in_array($id, $this->db->getFsBezirkIds(7955)));
		$this->assertEquals(array(1, 0), $cnt);

		$cnt = $this->db->updateGroupMembers($id, array(), 1);
		$this->assertFalse(in_array($id, $this->db->getFsBezirkIds(7955)));
		$this->assertEquals(array(0, 1), $cnt);

		$cnt = $this->db->updateGroupMembers($id, array(56, 7955), 1);
		$this->assertTrue(in_array($id, $this->db->getFsBezirkIds(7955)));
		$this->assertTrue(in_array($id, $this->db->getFsBezirkIds(56)));
		$this->assertEquals(array(2, 0), $cnt);

		$cnt = $this->db->updateGroupMembers($id, array(7955), 1);
		$this->assertTrue(in_array($id, $this->db->getFsBezirkIds(7955)));
		$this->assertFalse(in_array($id, $this->db->getFsBezirkIds(56)));
		$this->assertEquals(array(0, 1), $cnt);

		$this->db->updateGroupMembers($id, array(), 1);
	}


}

