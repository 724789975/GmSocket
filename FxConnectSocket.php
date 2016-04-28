<?php
require_once("FxMySocket.php");
require_once("FxNet.php");
require_once("log.php");
require_once("BigEndianBytesBuffer.php");

class FxHeader
{
	function __construct()
	{
	}
	public  function  GetHeaderLength(){return 0;}
	public  function  GetPkgHeader(){return NULL;}
	public  function  BuildSendPkgHeader($dwDataLen){}
	public  function  BuildRecvPkgHeader($strData){}
	
	public function ParsePacket()
	{
	}
}

class ServerHeader extends FxHeader
{
	function __construct()
	{
		$this->m_oHeaderBuffer = new BigEndianBytesBuffer("");
	}
	
	public  function  GetHeaderLength()
	{
		return 4;
	}
	public  function  GetPkgHeader()
	{
		return $this->m_oHeaderBuffer->readAllBytes();
	}
	public function ParsePacket()
	{
		return $this->m_oHeaderBuffer->readInt();
	}
	public  function  BuildSendPkgHeader($dwDataLen)
	{
		$this->m_oHeaderBuffer->clear();
		$this->m_oHeaderBuffer->writeInt($dwDataLen);
	}
	public  function  BuildRecvPkgHeader($strData)
	{
		if (strlen($strData) < $this->GetHeaderLength())
		{
			return FALSE;
		}
		
		$this->m_oHeaderBuffer->clear();
		
		$str = substr($strData, 0, $this->GetHeaderLength());
		$this->m_oHeaderBuffer->writeBytes($str);
	}
	
	protected $m_oHeaderBuffer;
}

class FxConnectSocket extends FxMySocket
{
	function __construct()
	{
		$this->m_dataRecvBuffer = new BigEndianBytesBuffer("");
		$this->m_dataSendBuffer = new BigEndianBytesBuffer("");
	}
	
	public function Initialize($strIp, $dwPort)
	{
	}

	public function OnRead()
	{
		// 接到的数据 如果满足包长久立马处理掉 不然 放到$m_strRecvBuffer中 下次继续放
		$strRecvBuffer = "";
		$dwRecvLength = socket_recv($this->GetSocket(), $strRecvBuffer, 2048, MSG_DONTWAIT);
		if ($dwRecvLength === false)
		{
			MyLog::crt("socket_recv() failed; errorno :  " . socket_last_error($this->GetSocket()) . " reason : " . socket_strerror(socket_last_error($this->GetSocket())));
			socket_close($this->GetSocket());
			
			FxNet::Instance()->DelSocket($this->GetSocket());
			return false;
		}
		
		if ($dwRecvLength == 0)
		{
			MyLog::dbg("socket disconnect fd : " . $this->GetSocket());
			socket_close($this->GetSocket());
			
			FxNet::Instance()->DelSocket($this->GetSocket());
			return false;
		}
		
		$this->GetRecvBuffer()->writeBytes($strRecvBuffer);
		
		// 读完后 执行处理函数
		$this->OnReadEnd();
	}
	public function OnWrite()
	{
	}
	
	public function OnReadEnd()
	{
		MyLog::crt("error read end and clear");
		$this->GetRecvBuffer()->clear();
	}
	
	public function Send($strSend, $dwLength)
	{
		if($dwLength <= 0)
		{
			return;
		}
		while(true)
		{
			if(($dwSendResult = socket_write($this->GetSocket(), $strSend, $dwLength)) === FALSE)
			{
				MyLog::crt("socket_recv() failed; errorno :  " . socket_last_error($this->GetSocket()) . " reason : " . socket_strerror(socket_last_error($this->GetSocket())));
				socket_close($this->GetSocket());
				
				FxNet::Instance().DelSocket($this->GetSocket());
				return;
			}
			$dwLength -= $dwSendResult;
			if ($dwLength <= 0)
			{
				return;
			}
		}
	}
	
	public  function SendMsg()
	{
		$this->Send($this->GetSendBuffer()->readAllBytes(), strlen($this->GetSendBuffer()->readAllBytes()));
		$this->GetSendBuffer()->clear();
	}
	
	public function GetRecvBuffer()
	{
		if ($this->m_dataRecvBuffer == NULL)
		{
			$this->m_dataRecvBuffer = new BigEndianBytesBuffer("");
		}
		return $this->m_dataRecvBuffer;
	}
	
	public function GetSendBuffer()
	{
		if ($this->m_dataSendBuffer == NULL)
		{
			$this->m_dataSendBuffer = new BigEndianBytesBuffer("");
		}
		return $this->m_dataSendBuffer;
	}
	
	protected $m_dataRecvBuffer;
	protected $m_dataSendBuffer;
}

class FxServerConnectSocket extends FxConnectSocket
{
	function __construct()
	{
		$this->m_dwServerId = 0;
		$this->m_oServerHeader = new ServerHeader();
	}
	public function OnReadEnd()
	{
		MyLog::dbg("recv buffer length : " . $this->GetRecvBuffer()->GetBytesLength());
		
		while($this->m_oServerHeader->BuildRecvPkgHeader($this->GetRecvBuffer()->readAllBytes()) !== FALSE)
		{
			$dwParsePacketLen = $this->m_oServerHeader->ParsePacket();
			if($this->GetRecvBuffer()->GetBytesLength() < ($dwParsePacketLen + $this->m_oServerHeader->GetHeaderLength()))
			{
				return;
			}
			
			$this->GetRecvBuffer()->readBytes($this->m_oServerHeader->GetHeaderLength());
			
			$strData = $this->GetRecvBuffer()->readBytes($dwParsePacketLen);
			
			$oHeader = new ServerHeader();
			$oHeader->BuildSendPkgHeader(strlen($strData));
			$this->GetSendBuffer()->writeBytes($oHeader->GetPkgHeader());
			$this->GetSendBuffer()->writeBytes($strData);
			$this->SendMsg();
		}
	}	
	
	private $m_dwServerId;
	
	private $m_oServerHeader;
}

class FxGMConnectSocket extends FxConnectSocket
{
	function FxGMConnectSocket()
	{
	}

	public function OnReadEnd()
	{
		;
	}

}

class FxConnectionFactory
{
	function CreateConnection()
	{
		return NULL;
	}
}

class ServerConnectionFactory extends FxConnectionFactory
{
	function CreateConnection()
	{
		return  new FxServerConnectSocket();
	}
}

class GMConnectionFactory extends FxConnectionFactory
{
	function CreateConnection()
	{
		return new FxGMConnectSocket();
	}
}

?>
