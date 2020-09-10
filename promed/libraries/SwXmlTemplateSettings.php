<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		05.2013
 */

/**
 * Вспомогательная библиотека для работы с настройками печати Xml-документов
 *
 * @package		XmlTemplate
 * @author		Alexander Permyakov
 */
class SwXmlTemplateSettings
{
	public static $paperFormatArr = array('1'=>'A4','2'=>'A5');
	public static $paperOrientArr = array('1'=>'Альбомная','2'=>'Книжная');

	/**
	 * Конвертирует json-строку с настройками в читабельный для человека вид
	 * @param string $json_str
	 * @return string
	 */
	public static function getStringFromJson($json_str) {
		if(empty($json_str))
		{
			return '';
		}
		else
		{
			$settings = self::getArrFromJson($json_str);
			if(empty($settings))
			{
				return 'Неправильный формат строки настроек';
			}

			$text = 'Формат '. self::$paperFormatArr[$settings['PaperFormat_id']]
				.', размер шрифта: '.$settings['FontSize_id']
				.', ориентация: '.self::$paperOrientArr[$settings['PaperOrient_id']]
				.', Отступы, мм - правый: '.$settings['margin_right']
				.', левый: '.$settings['margin_left']
				.', верхний: '.$settings['margin_top']
				.', нижний: '.$settings['margin_bottom'];

			if (!empty($settings['base_fontsize'])) {
				$text .= ', базовый размер шрифта: '.$settings['base_fontsize'];
			}

			$fonts = array(
				'Arial',
				'Comic Sans MS',
				'Courier New',
				'Georgia',
				'Lucida Sans Unicode',
				'Lucida Grande',
				'Tahoma',
				'Times New Roman',
				'Trebuchet MS',
				'Verdana'
			);
			
			if (!empty($settings['base_fontfamily']) && !empty($fonts[$settings['base_fontfamily'] - 1])) {
				$font = $fonts[$settings['base_fontfamily'] - 1];
				$text .= ', базовый шрифт: ' . $font;
			}

			return $text;
		}
	}

	/**
	 * Возвращает массив с настройками по умолчанию
	 * @return array
	 */
	public static function getArrSettingsDefault() {
		$row = array();
		// настройки по умолчанию
		$row['PaperFormat_id']='1';
		$row['PaperOrient_id']='2';
		$row['FontSize_id']='10';
		$row['margin_left']='10';
		$row['margin_top']='10';
		$row['margin_bottom']='10';
		$row['margin_right']='10';
		$row['base_fontsize']='10';
		$row['base_fontfamily']='8'; // Times New Roman
		return $row;
	}

	/**
	 * Конвертирует json-строку с настройками в массив
	 * @param string $json_str
	 * @return array Если передан неправильный параметр, то возвращает настройки по умолчанию
	 */
	public static function getArrFromJson($json_str, $allowDefaultSettings = true) {
		$row = self::getArrSettingsDefault();
		if(empty($json_str) || !is_string($json_str))
		{
			if ($allowDefaultSettings) {
				// настройки по умолчанию
				return $row;
			} else {
				return array();
			}
		}
		$settings=json_decode($json_str, true);
		if(is_array($settings))
		{
			$row['PaperFormat_id']=$settings['pf'];
			$row['PaperOrient_id']=$settings['po'];
			$row['FontSize_id']=$settings['fs'];
			$row['margin_left']=$settings['ml'];
			$row['margin_top']=$settings['mt'];
			$row['margin_bottom']=$settings['mb'];
			$row['margin_right']=$settings['mr'];
			$row['base_fontsize']=!empty($settings['bfs'])?$settings['bfs']:null;
			$row['base_fontfamily']=!empty($settings['bff'])?$settings['bff']:null;
		}
		return $row;
	}

	/**
	 * Конвертирует массив с настройками в json-строку
	 * @param array
	 * @return string $json_str Если передан неправильный параметр или массив имеет неправильный формат,
	 * то возвращает настройки по умолчанию
	 */
	public static function getJsonFromArr($data) {
		if( empty($data) || !is_array($data)
			|| empty($data['PaperFormat_id'])
			|| empty($data['PaperOrient_id'])
			|| empty($data['FontSize_id'])
			|| empty($data['margin_left'])
			|| empty($data['margin_top'])
			|| empty($data['margin_bottom'])
			|| empty($data['margin_right'])
		) {
			// настройки по умолчанию
			$data = self::getArrSettingsDefault();
		}

		return json_encode(array(
			'pf'=>$data['PaperFormat_id'],
			'po'=>$data['PaperOrient_id'],
			'fs'=>$data['FontSize_id'],
			'ml'=>$data['margin_left'],
			'mt'=>$data['margin_top'],
			'mb'=>$data['margin_bottom'],
			'mr'=>$data['margin_right'],
			'bfs'=>!empty($data['base_fontsize'])?$data['base_fontsize']:null,
			'bff'=>!empty($data['base_fontfamily'])?$data['base_fontfamily']:null
		));
	}

}