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
		$this->m_arrSockets[] = $oReadSocket;
	}
	
	public function DelSocket($dwDelSocket)
	{
		foreach ($this->m_arrSockets as $key=>$value)
		{
			if ($value->GetSocket() === $dwDelSocket)
			{
				unset($this->m_arrSockets[$key]);
			}
		}
		//if(array_key_exists($dwReadSocket, $this->m_arrSockets))
		//{
			//unset($this->m_arrSockets[$dwReadSocket]);
		//}
	}
	
	public function Run()
	{
		while (true)
		{
			$arrErrorSockets = array();
			$arrReadSocket = array();
			foreach ($this->m_arrSockets as $value)
			{
				$arrReadSocket[] = $value->GetSocket();
			}
			if(false === socket_select($arrReadSocket, $write = NULL, $arrErrorSockets, 0))
			{
				MyLog::crt("socket_select() failed, reason : " . socket_strerror(socket_last_error()) . ", error no : " . socket_last_error());
				break;
			}; 
			
			foreach ($arrReadSocket as $hReadSocket)
			{
				foreach ($this->m_arrSockets as $value)
				{
					//MyLog::dbg(print_r($hReadSocket, true) . " " . print_r($value->GetSocket(), true));
					if ($value->GetSocket() === $hReadSocket)
					{
						$value->OnRead();
						break;
					}
				}
			}
			
			foreach ($arrErrorSockets as $hErrorSocket)
			{
				// 直接断开
				socket_close($hErrorSocket);
				$this->DelSocket($hErrorSocket);
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