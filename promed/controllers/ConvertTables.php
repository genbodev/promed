<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LoadTables - контроллер для работы с таблицами:
 *     - Просмотр и выбор схем (LoadSchemes)
 *     - Просмотр и выбор таблиц (LoadTables)
 *     - Просмотр и выбор полей таблиц (LoadFields)
 *     - Конвертирование значений поля в другую кодировку (ConvertFields)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Khorev Sergey (ipshon@rambler.ru)
 * @version      03.02.2012
 */
class ConvertTables extends swController
{
	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model("ConvertTables_model","contab");
		$this->inputRules = array(
			"LoadTables" => array(
				array
				(
					'field' => 'TABLE_SCHEME',
					'label' => 'Имя схемы',
					'type'  => 'string',
					'rules' => 'required'
				)
			),
			"LoadFields" => array(
				array
				(
					'field' => 'TABLE_NAME',
					'label' => 'Имя таблицы',
					'type'  => 'string',
					'rules' => 'required'
				),
				array
				(
					'field' => 'TABLE_SCHEME',
					'label' => 'Имя схемы',
					'type'  => 'string',
					'rules' => 'required'
				)
			),
			"ConvertFields" => array(
				array
				(
					'field' => 'TABLE_NAME',
					'label' => 'Имя таблицы',
					'type'  => 'string',
					'rules' => 'required'
				),
				array
				(
					'field' => 'FIELD_NAME',
					'label' => 'Имя поля',
					'type'  => 'string',
					'rules' => 'required'
				),
				array
				(
					'field' => 'TABLE_SCHEME',
					'label' => 'Имя схемы',
					'type'  => 'string',
					'rules' => 'required'
				)
			)
		);
	}

	/**
	 * Список схем
	 */
	function LoadSchemes()
	{
		$response = $this->contab->LoadSchemes();
		$this->ProcessModelList($response, true,true)->ReturnData();
	}

	/**
	 * Список таблиц в выбранной схеме. Отображаются только те таблицы, которе содержат текстовые поля
	 */
	function LoadTables()
	{
		$data = $this->ProcessInputData('LoadTables',false);
		if ($data === false) {return false;}

		$response = $this->contab->LoadTables($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}

	/**
	 * Список текстовых полей в выбранной таблице
	 */
	function LoadFields()
	{
		$data = $this->ProcessInputData('LoadFields',false);
		if ($data === false) {return false;}
		$response = $this->contab->LoadFields($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/**
	 * Смена кодировки значений выбранного поля таблицы
	 */
	function ConvertFields()
	{
		$data = $this->ProcessInputData('ConvertFields',false);
		if ($data === false) {return false;}
		@ini_set('max_execution_time', 1200);
		$response = $this->contab->GetFieldsData($data);
		if($response != false and count($response)>0)
		{
			foreach ($response as $rows)
			{
				$value = $rows['FIELD_VALUE'];
				$id = $rows['FIELD_ID'];

				//условие на кодировку:
				if (mb_detect_encoding($value, 'UTF-8', TRUE)=='UTF-8')
				{
					//конвертируем
					$value = toAnsi($value);
					$response = $this->contab->ConvertField($data,$id,$value);
					if (!$response){
						$this->ReturnData(array('success' => false, 'Message' => toUTF('Произошла ошибка конвертации данных')));
					}
				}
			}
			$this->ReturnData(array('success' => true));
		}

		else
		{
			ajaxErrorReturn();
		}
		return true;
	}
}
?>