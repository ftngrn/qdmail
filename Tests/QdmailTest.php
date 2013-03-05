<?php
error_reporting((E_ALL|E_STRICT)^E_DEPRECATED);
require_once('./qdmail.php');

class QdmailTest extends PHPUnit_Framework_TestCase
{
	public $qdm = null;

	/**
	 */
	public function testGenerate()
	{
		$this->qdm = new Qdmail();
		$this->assertNotNull($this->qdm);
		$this->assertInstanceOf('Qdmail', $this->qdm);
	}
}
?>
