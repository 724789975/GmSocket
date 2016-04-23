<?php

/*
*/

date_default_timezone_set('Etc/GMT-8');

class MyLog
{
	const LOG_LVL_DBG = 0;
	const LOG_LVL_INF = 1;
	const LOG_LVL_WRN = 2;
	const LOG_LVL_CRT = 3;
	
    private static $_logFile = null;
    private static $_logLvl = self::LOG_LVL_DBG;
    private static $_logTxt = array(
    	self::LOG_LVL_DBG => "DBG",
    	self::LOG_LVL_INF => "INF",
    	self::LOG_LVL_WRN => "WRN",
    	self::LOG_LVL_CRT => "CRT",
    );

    private static function logInfo($lvl, $str)
    {
    	if (self::$_logFile == null)
        {
            self::$_logFile = fopen('log.txt', 'a');
            if (self::$_logFile == null)
            {
            	return;
            }
        }
       
    	if ($lvl >= self::$_logLvl)
    	{
	   	$now = date("Y/m/d G:i:s");
	   	$logTxt = self::$_logTxt[$lvl];
	       fwrite(self::$_logFile, "[$now][$logTxt]\t$str\r\n");
    	}
    }
   
    public static function dbg($str)
    {
    	self::logInfo(self::LOG_LVL_DBG, $str);
    }
   
    public static function inf($str)
    {
    	self::logInfo(self::LOG_LVL_INF, $str);
    }
   
    public static function wrn($str)
    {
    	self::logInfo(self::LOG_LVL_WRN, $str);
    }
   
    public static function crt($str)
    {
    	self::logInfo(self::LOG_LVL_CRT, $str);
    }
}
?>

