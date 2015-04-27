<?php
class ManualTest extends PHPUnit_Framework_TestCase
{
  /* Hamburg is in Europa->Deutschland->Hamburg, so we have
    Europa, Deutschland, Hamburg as parents */
  public function testParentBezirkeHamburg()
  {
    $db = new ManualDb(DB_HOST, DB_USER, DB_PASS, DB_DB);
    $hh_parents = $db->getParentBezirke(31);
    $this->assertCount(3, $hh_parents);
    $this->assertTrue(in_array(31, $hh_parents));
    $this->assertTrue(in_array(1, $hh_parents));
    $this->assertTrue(in_array(741, $hh_parents));
  }

  /* Same as above but array also containing one parent */
  public function testParentBezirkeHamburgArray()
  {
    $db = new ManualDb(DB_HOST, DB_USER, DB_PASS, DB_DB);
    $hh_parents = $db->getParentBezirke(31, 1);
    $this->assertCount(3, $hh_parents);
    $this->assertTrue(in_array(31, $hh_parents));
    $this->assertTrue(in_array(1, $hh_parents));
    $this->assertTrue(in_array(741, $hh_parents));
  }
}

