<?php
/**
 * Image_helper - хелпер для работы с картинками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Markoff Andrew
 * @version      2015-12-11
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Задания размеров загружаемой картинки
 */
function createThumb($source,$type,$filename,$new_w,$new_h){
	
	if (!empty($type)) {
		$system = explode('/',$type);
		$last = count($system)-1;
	} else {
		$system = explode('.',$source);
		$last = count($system)-1;
	}
	//print $source."-".$filename." ".$new_w." ".$new_h;
	if (preg_match('/jpg|jpeg|pjpeg|JPG|JPEG|PJPEG/',$system[$last])){
		$src_img = imagecreatefromjpeg($source);
	}
	if (preg_match('/png|PNG/',$system[$last])){
		$src_img = imagecreatefrompng($source);
	}
	if (preg_match('/gif|GIF/',$system[$last])){
		$src_img = imagecreatefromgif($source);
	}
	if(!isset($src_img)){
		return 0;
	}
	$old_x = imageSX($src_img);
	$old_y = imageSY($src_img);
	if ($old_x > $old_y) {
		$thumb_w = $new_w;
		$thumb_h = $old_y*($new_w/$old_x);
	}
	if ($old_x < $old_y) {
		$thumb_w = $old_x*($new_h/$old_y);
		$thumb_h = $new_h;
	}
	if ($old_x == $old_y) {
		$thumb_w = $new_w;
		$thumb_h = $new_h;
	}
	$dst_img = ImageCreateTrueColor($thumb_w,$thumb_h);
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
	if (preg_match("/png/",$system[1]))
	{
		imagepng($dst_img,$filename); 
	} 
	if (preg_match("/gif/",$system[1]))
	{
		imagegif($dst_img,$filename);
	}
	else 
	{
		imagejpeg($dst_img,$filename); 
	}
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
	return 1;
}

/**
 * Проверка загружаемой картинки
 */
function checkImage($files, $name, $options = array()){
	// Первоначальные настройки
	$max_size = 2097152; //2 Mb
	$types = 'jpg|jpe|jpeg|png';
	if (is_array($options)) {
		if (isset($options['max_size'])) {
			$max_size = $options['max_size'];
		}
		if (isset($options['types'])) {
			$allowed_types = $options['types'];
		}
	} 
	$allowed_types = explode('|',$types);
	
	if (!isset($files[$name])) {
		return array('success'=>false, 'Error_Msg'=>'Вы не выбрали файл для загрузки.', 'Error_Code'=>701);
	}
	$pic = $files[$name];
	// Если файл не удалось загрузить
	if (!is_uploaded_file($pic['tmp_name'])) {
		$error = ( ! isset($pic['error'])) ? 4 : $pic['error'];
		switch($error) {
			case 1:
				$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
				break;
			case 2:
				$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
				break;
			case 3:
				$message = 'Этот файл был загружен не полностью.';
				break;
			case 4:
				$message = 'Вы не выбрали файл для загрузки.';
				break;
			case 6:
				$message = 'Каталог для временных загрузки файлов не найден.';
				break;
			case 7:
				$message = 'Файл не может быть записан на диск.';
				break;
			case 8:
				$message = 'Неверный формат файла.';
				break;
			default :
				$message = 'Вы не выбрали файл для загрузки.';
				break;
		}
		return array('success'=>false, $message, 'Error_Code'=>702);
	}

	// Проверка размера файла
	if ($pic['size'] > $max_size) {
		return array('success'=>false,  'Error_Msg'=>'Размер файла превышает установленный максимальный размер в '. round($max_size/1024) .'кб.', 'Error_Code'=>761);
	}

	// Проверка разрешен ли к загрузке тип файла
	$ext = strtolower(pathinfo($pic['name'], PATHINFO_EXTENSION));
	/*
	$x = explode('.', $pic['name']);
	$ext = strtolower(end($x));
	*/
	if (!in_array($ext, $allowed_types) ) {
		return array('success'=>false,  'Error_Msg'=>'Вы пытаетесь загрузить запрещенный тип файла.', 'Error_Code'=>703);
	}
	
	return true;
}
?>