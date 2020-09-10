<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnOnkoNotifyNeglected - контроллер формы "Протокол запущенной формы онкозаболевания"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 * 
 * @property EvnOnkoNotifyNeglected_model EvnOnkoNotifyNeglected
 */

class EvnOnkoNotifyNeglected extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(

	    'load' => array(
		    array(
			    'field' => 'EvnOnkoNotifyNeglected_id',
			    'label' => 'Идентификатор',
			    'rules' => 'required',
			    'type' => 'id'
		    )
	    ),
	    'print' => array(
		    array(
			    'field' => 'EvnOnkoNotifyNeglected_id',
			    'label' => 'Идентификатор',
			    'rules' => 'required',
			    'type' => 'id'
		    )
        ),
		'save' => array(
			array(
				'field' => 'EvnOnkoNotifyNeglected_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotify_id',
				'label' => 'Идентификатор извещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Morbus_id',
				'label' => 'Идентификатор заболевания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_cid',
				'label' => 'Наименование учреждения, где проведена конференция',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_ClinicalData',
				'label' => 'Данные клинического разбора настоящего случая',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_OrgDescr',
				'label' => 'Организационные выводы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setConfDT',
				'label' => 'Дата конференции',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setNotifyDT',
				'label' => 'Дата заполнения протокола',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setDT',
				'label' => 'Дата установления запущенности рака',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'OnkoLateDiagCause_id',
				'label' => 'Причина позднего установления диагноза',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setFirstDT',
				'label' => 'Дата появления первых признаков',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setFirstTreatmentDT',
				'label' => 'Первичное обращение больного за медицинской помощью по поводу заболевания - Дата',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_fid',
				'label' => 'Первичное обращение больного за медицинской помощью по поводу заболевания - в какую лечебную организацию',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_setFirstZODT',
				'label' => 'Дата установления первичного диагноза злокачественного новообразования',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_zid',
				'label' => 'Установление первичного диагноза злокачественного новообразования - в какой организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectLpuType_id',
				'label' => 'Несвоевременное обращение больного за медицинской помощью в лечебную организацию - Куда',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectLpuTime_id',
				'label' => 'Несвоевременное обращение больного за медицинской помощью в лечебную организацию - Обращение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_TreatFirstDate',
				'label' => 'Несвоевременное обращение больного за медицинской помощью в лечебную организацию - Дата обращения впервые',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'NeglectOnkoType_id',
				'label' => 'Несвоевременное обращение больного за медицинской помощью в онкологическую организацию - Куда',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectOnkoTime_id',
				'label' => 'Несвоевременное обращение больного за медицинской помощью в онкологическую организацию - Обращение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_ConfirmDate',
				'label' => 'Дата подтверждения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_ExceptionDate',
				'label' => 'Дата исключения диагноза «рак»',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'NeglectScreenLpuType_id',
				'label' => 'Длительное обследование в общей лечебной сети - Где',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_begScreenDate',
				'label' => 'Длительное обследование в общей лечебной сети - Дата начала обследования',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnOnkoNotifyNeglected_endScreenDate',
				'label' => 'Длительное обследование в общей лечебной сети - Дата окончания обследования',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'NeglectScreenOnkoType_id',
				'label' => 'Длительное обследование в онкологических организациях - Где',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectScreenOnkoTime_id',
				'label' => 'Длительное обследование в онкологических организациях - сроки обследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectHiddenType_id',
				'label' => 'Скрытое течение болезни',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectDiagnosticErrType_id',
				'label' => 'Ошибка в диагностике',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NeglectDiagnosticErrType_SecondComment',
				'label' => 'Дополнительные замечания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, заполнивший протокол',
				'rules' => '',
				'type' => 'id'
			)
		)
    );

	/**
	 * construct
	 */
	function __construct ()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnOnkoNotifyNeglected_model', 'EvnOnkoNotifyNeglected');
	}

	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		$response = $this->EvnOnkoNotifyNeglected->save($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Загрузка формы
	 */
	function load()
	{
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
		$response = $this->EvnOnkoNotifyNeglected->load($data);
		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Печать протокола 
	 */
	function getPrintForm() {
		
		$data = $this->ProcessInputData('print', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnOnkoNotifyNeglected->getDataForPrint($data);
		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных протокола';
			return false;
		}
		$print_data = $response[0];

		$f_arr = array(
			'IsTumorDepoUnknown' => 'неизвестна'
			,'IsTumorDepoBones' => 'кости'
			,'IsTumorDepoLiver' => 'печень'
			,'IsTumorDepoSkin' => 'кожа'
			,'IsTumorDepoKidney' => 'почки'
			,'IsTumorDepoOvary' => 'яичники'
			,'IsTumorDepoPerito' => 'брюшина'
			,'IsTumorDepoLympha' => 'отдаленные лимфатич. узлы'
			,'IsTumorDepoLungs' => 'легкие и/или плевра '
			,'IsTumorDepoBrain' => 'головной мозг'
			,'IsTumorDepoMarrow' => 'костный мозг'
			,'IsTumorDepoOther' => 'другие органы'
			,'IsTumorDepoMulti' => 'множественные'
		);
		$tmp_arr = array();
		foreach($f_arr as $f => $v) {
			if(isset($print_data[$f]) && $print_data[$f]== 2)
			{
				$tmp_arr[] = $v;
			}
		}
		$print_data['TumorDepo'] = (count($tmp_arr) > 0)?implode(', ', $tmp_arr):'';
		$print_data['CurData'] = array();
		$response = $this->EvnOnkoNotifyNeglected->getCurDataForPrint($print_data);
		if ( is_array($response) && count($response) > 0 ) {
			$print_data['CurData'] = $response;
		}
		$this->load->library('parser');
		return $this->parser->parse('print_evnonkonotifyneglected', $print_data);
	}
	
}