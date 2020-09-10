<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusVener - Специфика по венерическим заболеваниям
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-TODO: Do some explanation, preamble and describing
 * @package      Foobaring
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Markoff
 * @version      2012-10
 */
/**
 * @property MorbusVener_model $dbmodel
 */
class MorbusVener extends swController
{
    var $model_name = "MorbusVener_model";

	/**
	 * Method description
	 */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, 'dbmodel');
        $this->inputRules = array(
            'load' => array(
                array('field' => 'Morbus_id'                          ,'label' => 'Идентификатор заболевания'                                                  ,'rules' => 'required', 'type' => 'id'),
                array('field' => 'Evn_id'                             ,'label' => 'Идентификатор учетного документа'                                           ,'rules' => 'required', 'type' => 'id'),
            ),
            'loadMorbusVenerContact' => array(
				array('field' => 'MorbusVenerContact_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'Person_cid','label' => 'Человек','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerContact_RelationSick','label' => 'Отношение к больному','rules' => '', 'type' => 'string'),
				array('field' => 'MorbusVenerContact_IsSourceInfect','label' => 'Источник заражения','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerContact_IsFamSubjServey','label' => 'Член семьи или контакт, подлежащий обследованию','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerContact_CallDT','label' => 'Дата вызова','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerContact_PresDT','label' => 'Дата явки','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerContact_FirstDT','label' => 'Дата первичного осмотра','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerContact_FinalDT','label' => 'Дата заключительного осмотра','rules' => '', 'type' => 'date'),
				array('field' => 'Diag_id','label' => 'Диагноз','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerContact_Comment','label' => 'Примечание','rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusVenerTreatSyph' => array(
				array('field' => 'MorbusVenerTreatSyph_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerTreatSyph_NumCourse','label' => '№ курса','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusVenerTreatSyph_begDT','label' => 'Дата начала лечения','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerTreatSyph_endDT','label' => 'Дата окончания лечения','rules' => '', 'type' => 'date'),
				array('field' => 'Drug_id','label' => 'Препарат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerTreatSyph_SumDose','label' => 'Суммарная доза препарата','rules' => '', 'type' => 'float'),
				array('field' => 'MorbusVenerTreatSyph_RSSBegCourse','label' => 'Результаты серологического исследования до начала курса','rules' => '', 'type' => 'string'),
				array('field' => 'MorbusVenerTreatSyph_RSSEndCourse','label' => 'Результаты серологического исследования по окончании курса','rules' => '', 'type' => 'string'),
				array('field' => 'MorbusVenerTreatSyph_Comment','label' => 'Примечание','rules' => '', 'type' => 'string'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusVenerAccurTreat' => array(
				array('field' => 'MorbusVenerAccurTreat_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerAccurTreat_AbandDT','label' => 'Дата самовольного прекращения лечения','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerAccurTreat_CallDT','label' => 'Дата вызова','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerAccurTreat_PresDT','label' => 'Дата явки','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'loadMorbusVenerEndTreat' => array(
				array('field' => 'MorbusVenerEndTreat_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVenerEndTreat_setDT','label' => 'Дата назначенной явки','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerEndTreat_CallDT','label' => 'Дата вызова','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVenerEndTreat_PresDT','label' => 'Дата фактической явки','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Событие','rules' => '', 'type' => 'id')
			),
			'saveMorbusVener' => array(
				array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id','label' => 'Пациент','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз заболевания','rules' => '','type' => 'id'),
				array('field' => 'Evn_pid','label' => 'Идентификатор движения/посещения','rules' => '','type' => 'id'),
				array('field' => 'MorbusVener_id','label' => 'Специфика','rules' => '', 'type' => 'id'),

				array('field' => 'MorbusVener_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'VenerDetectType_id','label' => 'Обстоятельства выявления заболевания','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_IsVizitProf','label' => 'Посещал пункт индивидуальной профилактики венерических болезней','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_IsPrevent','label' => 'Ознакомлен с предупреждением','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_updDiagDT','label' => 'Дата изменения диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVener_HospDT','label' => 'Дата госпитализации','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusVener_BegTretDT','label' => 'Дата начала лечения','rules' => '', 'type' => 'date'),
				array('field' => 'Lpu_bid','label' => 'ЛПУ, где начал лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_EndTretDT','label' => 'Дата окончания лечения','rules' => '', 'type' => 'date'),
				array('field' => 'Lpu_eid','label' => 'ЛПУ, где окончил лечение','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusVener_DeRegDT','label' => 'Дата снятия с учета','rules' => '', 'type' => 'date'),
				array('field' => 'VenerDeRegCauseType_id','label' => 'Причина снятия с учета','rules' => '', 'type' => 'id'),
				array('field' => 'Mode','label' => 'Режим сохранения','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_LiveCondit','label' => 'Жилищно-бытовые условия','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_WorkCondit','label' => 'Условия работы','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_Heredity','label' => 'Наследственность','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_UseAlcoNarc','label' => 'Употребление алкоголя, наркотиков','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_PlaceInfect','label' => 'Где произошло заражение','rules' => '', 'type' => 'string'), 
				array('field' => 'MorbusVener_IsAlco','label' => 'Заражение произошло в состоянии опьянения','rules' => '', 'type' => 'id'), 
				array('field' => 'MorbusVener_MensBeg','label' => 'Менструация с (лет)','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_MensEnd','label' => 'Менструация по (лет)','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_MensOver','label' => 'Менструация через (дней)','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_MensLastDT','label' => 'Дата последней менструации','rules' => '', 'type' => 'date'), 
				array('field' => 'MorbusVener_SexualInit','label' => 'Половая жизнь с (лет)','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_CountPregnancy','label' => 'Количество беременностей','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_CountBirth','label' => 'Закончились родами','rules' => '', 'type' => 'int'), 
				array('field' => 'MorbusVener_CountAbort','label' => 'Прерваны абортом','rules' => '', 'type' => 'int')
			),
			'getVenerDiag' => array(
				array('field' => 'VenerDiag_id','label' => 'Идентификатор','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'МКБ-10','rules' => '', 'type' => 'id'),
				array('field' => 'VenerDiag_Code','label' => 'Код','rules' => '', 'type' => 'string'),
				array('field' => 'VenerDiag_Name','label' => 'Наименование','rules' => '', 'type' => 'string'),
				array('field' => 'query','label' => 'Запрос','rules' => '', 'type' => 'string')

			)

        );
    }

	/**
	 * Method description
	 */
    function loadMorbusVenerContact() {
		$data = $this->ProcessInputData('loadMorbusVenerContact', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusVenerContactViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function saveMorbusVenerContact() {
		$data = $this->ProcessInputData('loadMorbusVenerContact', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusVenerContact($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function loadMorbusVenerTreatSyph() {
		$data = $this->ProcessInputData('loadMorbusVenerTreatSyph', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusVenerTreatSyphViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function saveMorbusVenerTreatSyph() {
		$data = $this->ProcessInputData('loadMorbusVenerTreatSyph', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusVenerTreatSyph($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function loadMorbusVenerAccurTreat() {
		$data = $this->ProcessInputData('loadMorbusVenerAccurTreat', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusVenerAccurTreatViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function saveMorbusVenerAccurTreat() {
		$data = $this->ProcessInputData('loadMorbusVenerAccurTreat', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusVenerAccurTreat($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function loadMorbusVenerEndTreat() {
		$data = $this->ProcessInputData('loadMorbusVenerEndTreat', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusVenerEndTreatViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Method description
	 */
	function saveMorbusVenerEndTreat() {
		$data = $this->ProcessInputData('loadMorbusVenerEndTreat', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusVenerEndTreat($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение специцики по венерическим заболеваниям
	 */
	function saveMorbusVener()
	{
		$data = $this->ProcessInputData('saveMorbusVener', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusVener($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Method description
	 */
	function getVenerDiag() {
		$data = $this->ProcessInputData('getVenerDiag', true);
		if ($data) {
			$response = $this->dbmodel->getVenerDiag($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

}
