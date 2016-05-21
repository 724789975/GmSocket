<?php
require_once("FxMySocket.php");

class FxNet
{
	private function __construct()   
	{   
	}   
	private function __clone()  
	{  
	}//覆盖__clone()方法，禁止克隆 
	
	static public function Instance()
	{
		if(! (self::$m_Instance instanceof self) )   
		{    
			self::$m_Instance = new self();    
		}  
    return self::$m_Instance;
	}
	
	public function AddSocket($oReadSocket)
	{
		$this->m_arrSockets[print_r($oReadSocket->GetSocket(), true)] = $oReadSocket;
// 		$this->m_arrSockets[] = $oReadSocket;
	}
	
	public function DelSocket($dwDelSocket)
	{
		MyLog::dbg(print_r($dwDelSocket, true) . " will remove");
// 		MyLog::dbg(print_r(debug_backtrace(), true));
		if(array_key_exists(print_r($dwDelSocket, true), $this->m_arrSockets))
		{
			MyLog::dbg("socket  " . print_r($dwDelSocket, true) . " remove from select");
			$this->m_arrSockets[print_r($dwDelSocket, true)]->SetSocket(null);
			unset($this->m_arrSockets[print_r($dwDelSocket, true)]);
			socket_close($dwDelSocket);
		}
		if(array_key_exists(print_r($dwDelSocket, true), $this->m_arrSockets))
		{
			MyLog::dbg("remove failed");
		}
	}
	
	public function Run()
	{
		while (true)
		{
			$arrErrorSockets = array();
			$arrReadSocket = array();
			foreach ($this->m_arrSockets as $key => $value)
			{
				if ($value->GetSocket() == null)
				{
					MyLog::crt(__FILE__ . ", " . __FUNCTION__ . ", " . __LINE__ . " GetSocket() == null");
					unset($this->m_arrSockets[$key]);
				}
				else
				{
					$arrReadSocket[] = $value->GetSocket();
				}
			}
			if(false === socket_select($arrReadSocket, $write = NULL, $arrErrorSockets, 0))
			{
				MyLog::crt(__FILE__ . ", " . __FUNCTION__ . ", " . __LINE__ . "socket_select() failed, reason : " . socket_strerror(socket_last_error()) . ", error no : " . socket_last_error());
				break;
			}; 
			
			foreach ($arrErrorSockets as $hErrorSocket)
			{
				// 直接断开
				MyLog::crt(__FILE__ . ", " . __FUNCTION__ . ", " . __LINE__ . " errorno :  " . socket_last_error($hErrorSocket) . " reason : " . socket_strerror(socket_last_error($hErrorSocket)));
				$this->DelSocket($hErrorSocket);
// 				socket_close($hErrorSocket);
			}
			
			foreach ($arrReadSocket as $hReadSocket)
			{
				//foreach ($this->m_arrSockets as $value)
				if(array_key_exists(print_r($hReadSocket, true), $this->m_arrSockets))
				{
					try
					{
						$this->m_arrSockets[print_r($hReadSocket, true)]->OnRead();
					}
					catch (Exception $e)
					{
						MyLog::crt(__FILE__ . ", " . __FUNCTION__ . ", " . __LINE__ . " errorno :  " . socket_last_error($this->m_arrSockets[print_r($hReadSocket, true)]->GetSocket()) . " reason : " . socket_strerror(socket_last_error($this->m_arrSockets[print_r($hReadSocket, true)]->GetSocket())));
						$this->DelSocket($this->m_arrSockets[print_r($hReadSocket, true)]->GetSocket());
					}
				}
			}
			
			// sleep 0.1s
			//MyLog::dbg("ttttttttttttttttttttttttttttttttt");
			usleep(10000);
		}
	}
	
	private $m_arrSockets = array();
	
	static private $m_Instance = NULL;
}
?>