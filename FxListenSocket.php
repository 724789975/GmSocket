<?php
require_once("FxMySocket.php");
require_once("FxNet.php");
require_once("FxConnectSocket.php");
require_once("log.php");

class FxListenSocket extends FxMySocket
{
	function FxListenSocket()
	{
		$this->m_ConnectionFactroy = NULL;
	}
	
	public function Initialize($strIp, $dwPort, $pConnectionFactroy)
	{
		$this->SetSocket(socket_create(AF_INET, SOCK_STREAM, 0));
		if(false === socket_bind($this->GetSocket(), $strIp, $dwPort))
		{
			MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error($this->GetSocket())) . ", error no : " . socket_last_error($this->GetSocket()));
			return false;
		}
		if(false === socket_listen($this->GetSocket(), 16))
		{
			MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error($this->GetSocket())) . ", error no : " . socket_last_error($this->GetSocket()));
			return false;
		}
		if(false === socket_set_option($this->GetSocket(), SOL_SOCKET, SO_REUSEADDR, 1))
		{
			MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error($this->GetSocket())) . ", error no : " . socket_last_error($this->GetSocket()));
			return false;
		}
		if(false === socket_set_nonblock($this->GetSocket()))
		{
			MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error($this->GetSocket())) . ", error no : " . socket_last_error($this->GetSocket()));
			return false;
		}
		$this->m_ConnectionFactroy = $pConnectionFactroy;
		
		MyLog::dbg("listen ip : " . $strIp . ", port : " . $dwPort . ", fd : " . print_r($this->GetSocket(), true));
		
		return true;
	}

	public function OnRead()
	{
		if(($dwConnectSocket = socket_accept($this->GetSocket())) !== false)
		{
			if(socket_set_nonblock($dwConnectSocket) === false)
			{
				MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error($dwConnectSocket)) . ", error no : " . socket_last_error($dwConnectSocket));
				socket_close($dwConnectSocket);
				return;
			}

			$hConnectSocket = $this->m_ConnectionFactroy->CreateConnection();
			$hConnectSocket->SetSocket($dwConnectSocket);
			
			FxNet::Instance()->AddSocket($hConnectSocket);
			
			MyLog::dbg(print_r($this->GetSocket(), true) . " accept socket fd : " . print_r($dwConnectSocket, true));
		}
	}
	public function OnWrite()
	{
	}
	
	private $m_ConnectionFactroy;
}
?>
