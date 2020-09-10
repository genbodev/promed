<?php

defined('BASEPATH') or die('No direct script access allowed');
/**
 * MorbusCrazyDrug_model - Употребление психоактивных веществ на момент госпитализации
 *
 * Promed
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Alexander "Alf" Arefyev <avaref@gmail.com>
 * @version      12 2011
 */
require_once('Abstract_model.php');

/**
 * @property int	$MorbusCrazyDrug_id		Идентификатор
 * @property int	$MorbusCrazySection_id			Идентификатор движения по психиатрии/наркологии
 * @property string	$MorbusCrazyDrug_Name	Наименование
 * @property int	$CrazyDrugType_id				Вид вещества
 * @property int	$CrazyDrugReceptType_id			Тип приема
 * @property int	$pmUser_id						Пользователь
 * @property MorbusCrazySection_model $MorbusCrazySection Модель специфики движения по психиатрии/наркологии
 */
class MorbusCrazyDrug_model extends Abstract_model
{

	var $fields = [
		'MorbusCrazyDrug_id' => null,
		'MorbusCrazyDrug_Name' => null,
		'CrazyDrugType_id' => null,
		'CrazyDrugReceptType_id' => null,
		'pmUser_id' => null
	];

	/**
	 * @return string
	 */
    protected function getTableName()
    {
        return 'MorbusCrazyDrug';
    }

	/**
	 * @return bool
	 */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

	private $MorbusCrazySection;

	/**
	 * MorbusCrazyDrug_model constructor.
	 */
	function __construct()
    {
		parent::__construct();
		$this->load->model('MorbusCrazySection_model','MorbusCrazySection');
	}

	/**
	 * Проверки
	 */
	public function validate()
    {
		$this->valid = TRUE;
		$this->clearErrors();

		// Наименование
		$this->validateRequired('MorbusCrazyDrug_Name', 'Наименование');

		// Обязательные поля со ссылками на справочники
		$fields_required_reference = [
			'Вид вещества' => 'CrazyDrugType',
			'Тип приема' => 'CrazyDrugReceptType'
		];

		// Проверки обязательных полей и существование в справочниках
		foreach ($fields_required_reference as $label => $name) {
			if ($this->validateRequired($name . '_id', $label)) {
				$this->validateByReference($name, $label);
			}
		}

		return $this;
	}

	/**
	 * Загрузка
	 */
	public function load()
    {
		$params = [
			'MorbusCrazySection_id' => $this->MorbusCrazySection_id
		];

		$query = "
			select
				MorbusCrazyDrug_id as \"MorbusCrazyDrug_id\",
				MorbusCrazySection_id as \"MorbusCrazySection_id\",
				MorbusCrazyDrug_Name as \"MorbusCrazyDrug_Name\",
				CrazyDrugType_id as \"CrazyDrugType_id\",
				CrazyDrugReceptType_id as \"CrazyDrugReceptType_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				MorbusCrazyDrug_insDT as \"MorbusCrazyDrug_insDT\",
				MorbusCrazyDrug_updDT as \"MorbusCrazyDrug_updDT\"
			from
				{$this->scheme}.v_MorbusCrazyDrug
			where
				MorbusCrazySection_id = :MorbusCrazySection_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result))
            return false;

        $response = $result->result('array');
        $this->assign($response[0]);
        return $response;
	}

	/**
	 * @param array $values
	 */
	public function assign($values) {
		parent::assign($values);
		if (isset($values['MorbusCrazySection_id']) && $values['MorbusCrazySection_id']) {
			$this->MorbusCrazySection->MorbusCrazySection_id = $values['MorbusCrazySection_id'];
			$this->MorbusCrazySection->load();
		}
	}

	/**
	 * Удаление
	 * 
	 * @param array $data
	 * @return array|bool
	 */
	public function delete($data = [])
    {
		$params = [
			'MorbusCrazyDrug_id' => $data['MorbusCrazyDrug_id'],
			'pmUser_id' => $data['pmUser_id']
		];

		$query = "
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from {$this->scheme}.p_MorbusCrazyDrug_del
		    (
				MorbusCrazyDrug_id := :MorbusCrazyDrug_id,
				pmUser_id := :pmUser_id	    
		    )
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result))
			return false;

        return $result->result('array');
	}

	/**
	 * Добавляет параметр
	 *
	 * @param $params
	 * @param $query_paramlist_exclude
	 * @return array
	 */
	protected function getParamList($params, $query_paramlist_exclude)
    {
		list($params, $query_paramlist) = parent::getParamList($params, $query_paramlist_exclude);
		$params['MorbusCrazySection_id'] = $this->MorbusCrazySection->MorbusCrazySection_id;
		$query_paramlist = $query_paramlist . ' MorbusCrazySection_id := :MorbusCrazySection_id,';
		return [$params, $query_paramlist];
	}

}
