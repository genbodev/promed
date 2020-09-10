<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitis - контроллер для MorbusHepatitis
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      07.2012
 *
 * @property MorbusHepatitis_model $dbmodel
 */
class MorbusHepatitis extends swController
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		'saveMorbusSpecific' => array(
			array('field' => 'Mode','label' => 'Режим сохранения','rules' => 'trim|required','type' => 'string'),
			array('field' => 'MorbusHepatitis_id','label' => 'Идентификатор специфики заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Evn_pid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
			array('field' => 'Diag_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'MorbusBase_id','label' => 'Идентификатор базового заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'HepatitisEpidemicMedHistoryType_id','label' => 'Эпиданамнез','rules' => '','type' => 'id'),
			array('field' => 'MorbusHepatitis_EpidNum','label' => 'Эпидномер','rules' => '','type' => 'int')
		),
		'setParameter' => array(
			array('field' => 'Mode','label' => 'Режим сохранения','rules' => 'trim|required','type' => 'string'),
			array('field' => 'MorbusHepatitis_id','label' => 'Идентификатор специфики заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'MorbusBase_id','label' => 'Идентификатор базового заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Evn_pid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
			array('field' => 'Diag_id','label' => 'Диагноз заболевания','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
		)
    );

	/**
	 * Method description
	 */
	function __construct ()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('MorbusHepatitis_model', 'dbmodel');
	}
	
	/**
	 *  Сохранение/создание записи
	 *  Используется: пока нигде, сделано на случай сохранения из формы всех параметров специфики
     *
     * @return bool
	 */
	function saveMorbusSpecific() {
		$data = $this->ProcessInputData('saveMorbusSpecific', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра Эпидномер
	 *  Используется: ЭМК, Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setMorbusHepatitis_EpidNum() {
		$this->inputRules['setParameter'][] = array('field' => 'MorbusHepatitis_EpidNum','label' => 'Эпидномер','rules' => '','type' => 'int');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При записи параметра возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра Эпиданамнез
	 *  Используется: ЭМК, Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setHepatitisEpidemicMedHistoryType_id() {
		$this->inputRules['setParameter'][] = array('field' => 'HepatitisEpidemicMedHistoryType_id','label' => 'Эпиданамнез','rules' => '','type' => 'id');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При записи параметра возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра MorbusHepatitisFuncConfirm_Result
	 *  Используется: ЭМК, Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setMorbusHepatitisFuncConfirm_Result() {
		$this->inputRules['setParameter'][] = array('field' => 'MorbusHepatitisFuncConfirm_Result','label' => 'Результат исследования','rules' => 'trim','type' => 'string');
		$this->inputRules['setParameter'][] = array('field' => 'MorbusHepatitisFuncConfirm_id','label' => 'Идентификатор исследования','rules' => 'required','type' => 'id');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При записи параметра возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра MorbusHepatitisLabConfirm_Result
	 *  Используется: ЭМК, Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setMorbusHepatitisLabConfirm_Result() {
		$this->inputRules['setParameter'][] = array('field' => 'MorbusHepatitisLabConfirm_Result','label' => 'Результат исследования','rules' => 'trim','type' => 'string');
		$this->inputRules['setParameter'][] = array('field' => 'MorbusHepatitisLabConfirm_id','label' => 'Идентификатор исследования','rules' => 'required','type' => 'id');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При записи параметра возникли ошибки')->ReturnData();
		return true;
	}	
}