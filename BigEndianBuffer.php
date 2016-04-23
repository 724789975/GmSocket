<?php
/**
 * PHP bytes array operation  API
 * 
 * Copyright (c) 2010 sunli <sunli1223ATgmail.com>
 * 
 * @version    $Id: BigEndianBuffer.php 5 2010-12-14 06:20:27Z sunli1223 $
 * @author     sunli <sunli1223ATgmail.com>
 * @link       http://sunli.cnblogs.com
 */
abstract class BigEndianBuffer {
	/**
	 *  Gets a 32-bit floating point number at the current readerIndex and increases the readerIndex by 4 in this buffer.
	 *
	 * @return float
	 */
	public function readFloat() {
		$bytes = $this->readBytes ( 4 );
		$result = unpack ( 'f', strrev ( $bytes ) );
		
		return $result [1];
	}
	public function readDouble() {
		$bytes = $this->readBytes ( 4 );
		$result = unpack ( 'd', strrev ( $bytes ) );
		return $result [1];
	}
	/**
	 * Gets a 32-bit integer at the current readerIndex and increases the readerIndex by 4 in this buffer.
	 * 获取4字节转换成int  unpack|N
	 * @return int
	 */
	public function readInt() {
		$bytes = $this->readBytes ( 4 );
		$result = unpack ( 'N', $bytes );
		$result = $result [1];
		return $result;
	}
	
	/**
	 * Gets $len bytes at the current readerIndex and increases the readerIndex by 4 in this buffer.
	 *
	 * @param int $len
	 * @return bytes
	 */
	public abstract function readBytes($len);
	/**
	 * Gets a 16-bit short integer at the current readerIndex and increases the readerIndex by 2 in this buffer.
	 * 获取2字节转换成short  unpack|s
	 * @return short
	 */
	public function readShort() {
		$bytes = $this->readBytes ( 2 );
		$result = unpack ( 'n', $bytes );
		return $result [1];
	}
	public function readMedium() {
		$r0 = $this->readChar ();
		$r1 = $this->readShort ();
		$result = (($r1 << 8) + $r0);
		return $result;
	}
	/**
	 * Gets a 64-bit integer at the current readerIndex and increases the readerIndex by 8 in this buffer.
	 * 获取8字节转换成long  _unpack64
	 * @return long
	 */
	public function readLong() {
		$bytes = $this->readBytes ( 8 );
		$result = $this->_unpack64 ( $bytes );
		return $result;
	}
	/**
	 * Gets a byte at the current readerIndex and increases the readerIndex by 1 in this buffer.
	 * 获取1字节 unpack|c
	 * @return byte
	 */
	public function readChar() {
		$bytes = $this->readBytes ( 1 );
		$result = unpack ( 'c', $bytes );
		return $result [1];
	}
	public function readUnsignedChar() {
		$bytes = $this->readBytes ( 1 );
		$result = unpack ( 'C', $bytes );
		return $result [1];
	}
	public abstract function writeBytes($bytes);
	/**
	 * Sets the specified byte at the current writerIndex and increases the writerIndex by 1 in this buffer.
	 * 写入1字节  pack|C
	 * @param char $char
	 */
	public function writeChar($char) {
		$this->writeBytes ( pack ( 'c', $char ) );
	}
	/**
	 *  写入short
	 *
	 * @param short $num
	 */
	public function writeShort($num) {
		$this->writeBytes ( pack ( 'n', $num ) );
	}
	public function writeMedium($num) {
		$this->writeBytes ( pack ( 'ccc', ($num & 0xff), ($num >> 8 & 0xff), ($num >> 16 & 0xff) ) );
	}
	
	/**
	 * 写入int
	 *
	 * @param int $num
	 */
	public function writeInt($num) {
		$this->writeBytes ( pack ( 'N', $num ) );
	}
	/**
	 * 批量写入int
	 *
	 * @param int $num
	 */
	public function writeIntArray($nums) {
		$len = count ( $nums );
		$format [] = str_repeat ( 'N', $len );
		$args = array (
				str_repeat ( 'N', $len ), 
				$nums );
		call_user_func_array ( 'pack', $args );
		$this->writeBytes ( $args );
		//	$this->writeBytes ( pack ( str_repeat('N',$len), $num ) );
	}
	/**
	 * 写入long
	 *
	 * @param long $num
	 */
	public function writeLong($num) {
		$this->writeBytes ( $this->_pack64 ( $num ) );
	}
	/**
	 * Portability function to pack a x64 value with PHP limitations
	 * @return   mixed   Packed number
	 */
	public function _pack64($v) {
		// x64
		if (PHP_INT_SIZE >= 8) {
			$v = ( int ) $v;
			return pack ( "NN", $v >> 32, $v & 0xFFFFFFFF );
		}
		// x32, int
		if (is_int ( $v )) {
			return pack ( "NN", $v < 0 ? - 1 : 0, $v );
		}
		// x32, bcmath	
		if (function_exists ( "bcmul" )) {
			if (bccomp ( $v, 0 ) == - 1) {
				$v = bcadd ( "18446744073709551616", $v );
			}
			$h = bcdiv ( $v, "4294967296", 0 );
			$l = bcmod ( $v, "4294967296" );
			return pack ( "NN", ( float ) $h, ( float ) $l ); // conversion to float is intentional; int would lose 31st bit
		}
		// x32, no-bcmath
		$p = max ( 0, strlen ( $v ) - 13 );
		$lo = abs ( ( float ) substr ( $v, $p ) );
		$hi = abs ( ( float ) substr ( $v, 0, $p ) );
		$m = $lo + $hi * 1316134912.0; // (10 ^ 13) % (1 << 32) = 1316134912
		$q = floor ( $m / 4294967296.0 );
		$l = $m - ($q * 4294967296.0);
		$h = $hi * 2328.0 + $q; // (10 ^ 13) / (1 << 32) = 2328
		if ($v < 0) {
			if ($l == 0) {
				$h = 4294967296.0 - $h;
			} else {
				$h = 4294967295.0 - $h;
				$l = 4294967296.0 - $l;
			}
		}
		return pack ( "NN", $h, $l );
	}
	/**
	 * Portability function to unpack a x64 value with PHP limitations
	 * @return   mixed   Might return a string of numbers or the actual value
	 */
	public function _unpack64($v) {
		list ( $hi, $lo ) = array_values ( unpack ( "N*N*", $v ) );
		// x64
		if (PHP_INT_SIZE >= 8) {
			if ($hi < 0)
				$hi += (1 << 32); // because php 5.2.2 to 5.2.5 is totally fucked up again
			if ($lo < 0)
				$lo += (1 << 32);
			return ($hi << 32) + $lo;
		}
		// x32, int
		if ($hi == 0) {
			if ($lo > 0) {
				return $lo;
			}
			return sprintf ( "%u", $lo );
		} elseif ($hi == - 1) {
			// x32, int
			if ($lo < 0) {
				return $lo;
			}
			return sprintf ( "%.0f", $lo - 4294967296.0 );
		}
		$neg = "";
		$c = 0;
		if ($hi < 0) {
			$hi = ~ $hi;
			$lo = ~ $lo;
			$c = 1;
			$neg = "-";
		}
		$hi = sprintf ( "%u", $hi );
		$lo = sprintf ( "%u", $lo );
		// x32, bcmath
		if (function_exists ( "bcmul" )) {
			return $neg . bcadd ( bcadd ( $lo, bcmul ( $hi, "4294967296" ) ), $c );
		}
		// x32, no-bcmath
		$hi = ( float ) $hi;
		$lo = ( float ) $lo;
		$q = floor ( $hi / 10000000.0 );
		$r = $hi - $q * 10000000.0;
		$m = $lo + $r * 4967296.0;
		$mq = floor ( $m / 10000000.0 );
		$l = $m - $mq * 10000000.0 + $c;
		$h = $q * 4294967296.0 + $r * 429.0 + $mq;
		$h = sprintf ( "%.0f", $h );
		$l = sprintf ( "%07.0f", $l );
		if ($h == "0") {
			return $neg . sprintf ( "%.0f", ( float ) $l );
		}
		return $neg . $h . $l;
	}
}
?>