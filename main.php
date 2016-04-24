<?php
require_once ("log.php");
require_once ("FxNet.php");
require_once ("FxListenSocket.php");
require_once ("FxConnectSocket.php");

$oServerListenSocket = new FxListenSocket();
if($oServerListenSocket->Initialize("127.0.0.1", 10000, new ServerConnectionFactory()) === false)
{
	exit(0);
}
FxNet::Instance()->AddSocket($oServerListenSocket);

$oGMListenSocket = new FxListenSocket();
if($oGMListenSocket->Initialize("127.0.0.1", 13000, new GMConnectionFactory()) === false)
{
	exit(0);
}
FxNet::Instance()->AddSocket($oGMListenSocket);

FxNet::Instance()->Run();

MyLog::dbg("bbbbbbbbbbbbbaaaaaaaaaaa");
?>