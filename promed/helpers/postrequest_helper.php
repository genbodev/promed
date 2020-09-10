<?php
/**
 * PostRequest_helper - хелпер с пост-запросом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Miyusov Alexandr
 * @version      ?
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Возвращает значение из массива по ключу, если значение не найдено
 * возвращает пустую строку. Используется для подавления ошибки "Index not found".
 *
 * @param array $ar
 * @param string $sIndex Индекс в ассоциативном массиве
 * @param string $sDefaultValue Значение возвращаемое если ключ не найден
 * @return string
 */

/**
* default desc
*/
function decode_header ($str) {
	$part = preg_split ( "/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY);
	$out = array ();
	for ($h=0;$h<sizeof($part);$h++) {
		if ($h!=0) {
			$pos = strpos($part[$h],':');
			$k = strtolower ( str_replace (' ', '', substr ($part[$h], 0, $pos )));
			$v = trim(substr($part[$h], ($pos + 1)));
		} else {
			$k = 'status';
			$v = explode (' ',$part[$h]);
			$v = $v[1];
		}
		if ($k=='set-cookie') {
			$out['cookies'][] = $v;
		} else if ($k=='content-type') {
			if (($cs = strpos($v,';')) !== false) {
				$out[$k] = substr($v, 0, $cs);
			} else {
				$out[$k] = $v;
			}
		} else {
			$out[$k] = $v;
		}
	}
	return $out;
}
/**
* default desc
*/
function decode_body($info,$str,$eol="\r\n" ) {
	$tmp=$str;
	$add=strlen($eol);
	if (isset($info['transfer-encoding']) && $info['transfer-encoding']=='chunked') {
		$str='';
		do {
			$tmp=ltrim($tmp);
			$pos=strpos($tmp, $eol);
			$len=hexdec(substr($tmp,0,$pos));
			if (isset($info['content-encoding'])) {
				$str.=gzinflate(substr($tmp,($pos+$add+10),$len));
			} else {
				$str.=substr($tmp,($pos+$add),$len);
			}
			$tmp = substr($tmp,($len+$pos+$add));
			$check = trim($tmp);
		} while(!empty($check));
	} elseif (isset($info['content-encoding'])) {
		$str=gzinflate(substr($tmp,10));
	}
	return $str;
}
/**
* default desc
*/	
function PostRequest($data, $hostname, $port) {
	if (!$data||!is_array($data)||(count($data)==0)) {
		return array(array('success' => false, 'Error_Msg' => 'Отсутствуют параметры сообщения'));
	}
	if (!$port || !$hostname) {
		return array(array('success' => false, 'Error_Msg' => 'Отсутствуют параметры соединения'));
	}

	$PostData=http_build_query($data,'','&');
	$len=strlen($PostData);
	$nn="\r\n";
	$send="POST / HTTP/1.0".$nn."Host: ".$hostname.$nn."Port: ".$port.$nn."Content-Type:application/x-www-form-urlencoded;;charset=windows-1251".$nn."Content-Length: $len".$nn.$nn.$PostData;
	flush();
	if(($fp = @fsockopen($hostname, $port, $errno, $errstr, 30))!==false) {
		fputs($fp,$send);
		$header='';
		do { 
			$header.= fgets($fp, 4096);
		} while (strpos($header,"\r\n\r\n")===false);
		if(get_magic_quotes_runtime())	$header=decode_header(stripslashes($header));
		else							$header=decode_header($header);

		$body='';
		while (!feof($fp))	
			$body.=fread($fp,8192);
		if(get_magic_quotes_runtime())	$body=decode_body($header, stripslashes($body));
		else							$body=decode_body($header, $body);

		fclose($fp);

		return array(array('success'=>true,'data'=>$body, "Error_Code"=>null,"Error_Msg"=>null));

	} else {
		return array(array('success'=>false, 'Err_Msg'=>'Невозвможно соединиться с сервером передачи вызовов. Обратитесь к администратору'));
	}
}
	
	
?>