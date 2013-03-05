<?php
error_reporting((E_ALL|E_STRICT)^E_DEPRECATED);
require_once('./qdmail_receiver.php');

class QdmailReceiverTest extends PHPUnit_Framework_TestCase
{
	public $qdm = null;

	/**
	 */
	public function testGenerate()
	{
		$mailsrc = <<<_END_
From - Mon Mar 04 12:48:45 2013
Date: Mon, 04 Mar 2013 12:48:05 +0900
From: Sample From <from@sample.foo.bar>
To: SampleTo <to@sample.foo.bar>
Subject: Sample subject
Content-Type: text/plain; charset=ISO-2022-JP
Content-Transfer-Encoding: 7bit

Hello.
This is sample mail for test.

--
Signature
_END_;
		$receiver = QdmailReceiver::start('direct', $mailsrc);
		$this->assertNotNull($receiver);
		$this->assertInstanceOf('QdDecodeDirect', $receiver);
	}
}
?>
