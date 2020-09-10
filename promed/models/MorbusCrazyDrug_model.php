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
require_once('MorbusCrazySection_model.php');

/**
 * @property int	$MorbusCrazyDrug_id		Идентификатор
 * @property int	$MorbusCrazySection_id			Идентификатор движения по психиатрии/наркологии
 * @property string	$MorbusCrazyDrug_Name	Наименование
 * @property int	$CrazyDrugType_id				Вид вещества
 * @property int	$CrazyDrugReceptType_id			Тип приема
 * @property int	$pmUser_id						Пользователь
 * @property MorbusCrazySection_model $MorbusCrazySection Модель специфики движения по психиатрии/наркологии
 */
class MorbusCrazyDrug_model extends Abstract_model {

	var $fields = array(
		'MorbusCrazyDrug_id' => null,
		'MorbusCrazyDrug_Name' => null,
		'CrazyDrugType_id' => null,
		'CrazyDrugReceptType_id' => null,
		'pmUser_id' => null
	);

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
	function __construct() {
		parent::__construct();
		$this->MorbusCrazySection = new MorbusCrazySection_model();
	}

	/**
	 * Проверки
	 */
	public function validate() {
		$this->valid = TRUE;
		$this->clearErrors();

		// Наименование
		$this->validateRequired('MorbusCrazyDrug_Name', 'Наименование');

		// Обязательные поля со ссылками на справочники
		$fields_required_reference = array(
			'Вид вещества' => 'CrazyDrugType',
			'Тип приема' => 'CrazyDrugReceptType'
		);

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
	public function load() {
		$params = array(
			'MorbusCrazySection_id' => $this->MorbusCrazySection_id
		);
		$query = "
			SELECT
				MorbusCrazyDrug_id,
				MorbusCrazySection_id,
				MorbusCrazyDrug_Name,
				CrazyDrugType_id,
				CrazyDrugReceptType_id,
				pmUser_insID,
				pmUser_updID,
				MorbusCrazyDrug_insDT,
				MorbusCrazyDrug_updDT
			FROM
				{$this->scheme}.v_MorbusCrazyDrug
			WHERE
				MorbusCrazySection_id = :MorbusCrazySection_id
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result('array');
			$this->assign($response[0]);
			return $response;
		} else {
			return false;
		}
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
	 * @param $data
	 * @return array|bool
	 */
	public function delete($data) {
		$params = array(
			'MorbusCrazyDrug_id' => $data['MorbusCrazyDrug_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$this->scheme}.p_MorbusCrazyDrug_del
				@MorbusCrazyDrug_id = :MorbusCrazyDrug_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Добавляет параметр
	 *
	 * @param $params
	 * @param $query_paramlist_exclude
	 * @return array
	 */
	protected function getParamList($params, $query_paramlist_exclude) {
		list($params, $query_paramlist) = parent::getParamList($params, $query_paramlist_exclude);
		$params['MorbusCrazySection_id'] = $this->MorbusCrazySection->MorbusCrazySection_id;
		$query_paramlist = $query_paramlist . ' @MorbusCrazySection_id = :MorbusCrazySection_id,';
		return array($params, $query_paramlist);
	}

}
