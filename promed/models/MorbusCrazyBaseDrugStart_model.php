<?php

defined('BASEPATH') or die('No direct script access allowed');
/**
 * MorbusCrazyBaseDrugStart_model - Возраст начала употребления психоактивных средств
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
require_once('MorbusCrazyBase_model.php');

/**
 * @property int	$MorbusCrazyBaseDrugStart_id	Идентификатор
 * @property int	$MorbusCrazyBase_id				Идентификатор общего заболевания по психиатрии/наркологии
 * @property string	$MorbusCrazyBaseDrugStart_Name	Наименование
 * @property int	$CrazyDrugReceptType_id			Тип приема
 * @property int	$MorbusCrazyBaseDrugStart_Age	Число полных лет
 * @property int	$pmUser_id						Пользователь
 * @property MorbusCrazyBase_model $MorbusCrazyBase Модель специфики общего заболевания по психиатрии/наркологии
 */
class MorbusCrazyBaseDrugStart_model extends Abstract_model {

	var $fields = array(
		'MorbusCrazyBaseDrugStart_id' => null,
		'MorbusCrazyBaseDrugStart_Name' => null,
		'CrazyDrugReceptType_id' => null,
		'MorbusCrazyBaseDrugStart_Age' => null,
		'pmUser_id' => null
	);
	private $MorbusCrazyBase;

	/**
	 * @return string
	 */
    protected function getTableName()
    {
        return 'MorbusCrazyBaseDrugStart';
    }

	/**
	 * @return bool
	 */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

	/**
	 * MorbusCrazyBaseDrugStart_model constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->MorbusCrazyBase = new MorbusCrazyBase_model();
	}

	/**
	 * Проверки
	 */
	public function validate() {
		$this->valid = TRUE;
		$this->clearErrors();

		// Обязательные поля со ссылками на справочники
		$fields_required_reference = array(
			'Наименование' => 'MorbusCrazyBaseDrugStart_Name',
			'Число полных лет' => 'MorbusCrazyBaseDrugStart_Age'
		);

		// Проверки обязательных полей и существование в справочниках
		foreach ($fields_required_reference as $label => $name) {
			if ($this->validateRequired($name, $label)) {
				$this->validateByReference($name, $label);
			}
		}

		// Тип приема
		if ($this->validateRequired('CrazyDrugReceptType_id', 'Тип приема')) {
			$this->validateByReference('CrazyDrugReceptType', 'Тип приема');
		}

		return $this;
	}

	/**
	 * Загрузка
	 */
	public function load() {
		$params = array(
			'MorbusCrazyBase_id' => $this->MorbusCrazyBase_id
		);
		$query = "
			SELECT
				MorbusCrazyBaseDrugStart_id,
				MorbusCrazyBase_id,
				MorbusCrazyBaseDrugStart_Name,
				CrazyDrugReceptType_id,
				MorbusCrazyBaseDrugStart_Age,
				pmUser_insID,
				pmUser_updID,
				MorbusCrazyBaseDrugStart_insDT,
				MorbusCrazyBaseDrugStart_updDT
			FROM
				{$this->scheme}.v_MorbusCrazyBaseDrugStart
			WHERE
				MorbusCrazyBase_id = :MorbusCrazyBase_id
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
	 * @throws Exception
	 */
	public function assign($values) {
		parent::assign($values);
		if (isset($values['MorbusCrazyBase_id']) && $values['MorbusCrazyBase_id']) {
			$this->MorbusCrazyBase->MorbusCrazyBase_id = $values['MorbusCrazyBase_id'];
			$this->MorbusCrazyBase->load();
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
			'MorbusCrazyBaseDrugStart_id' => $data['MorbusCrazyBaseDrugStart_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$this->scheme}.p_MorbusCrazyBaseDrugStart_del
				@MorbusCrazyBaseDrugStart_id = :MorbusCrazyBaseDrugStart_id,
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
		$params['MorbusCrazyBase_id'] = $this->MorbusCrazyBase->MorbusCrazyBase_id;
		$query_paramlist = $query_paramlist . ' @MorbusCrazyBase_id = :MorbusCrazyBase_id,';
		return array($params, $query_paramlist);
	}

}
