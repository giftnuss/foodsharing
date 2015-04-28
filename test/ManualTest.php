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

}

