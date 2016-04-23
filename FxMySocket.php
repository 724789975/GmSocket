<?php
require_once("log.php");
class FxMySocket
{
	public function FxMySocket()
	{
		$this->m_hSocket = 0;
	}
	
	public function SetSocket($hSocket)
	{
		$this->m_hSocket = $hSocket;
		//MyLog::dbg(__FILE__ . " " . __FUNCTION__ . " socket : " . print_r($this->m_hSocket, true));
	}
	public function GetSocket()
	{
		//MyLog::dbg(__FILE__ . " " . __FUNCTION__ . " socket : " . print_r($this->m_hSocket, true));
		return $this->m_hSocket;
	}
	
	public function OnRead()
	{
		MyLog::crt("error read");
		assert_options(ASSERT_WARNING, 0);
	}
	public function OnWrite()
	{
		MyLog::crt("error write");
		assert_options(ASSERT_WARNING, 0);
	}
	
	private $m_hSocket;
}
?>