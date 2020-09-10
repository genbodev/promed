<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnOnkoNotify - контроллер формы "Журнал Извещений об онкобольных"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 * 
 * @property EvnOnkoNotify_model EvnOnkoNotify
 */

class EvnOnkoNotify extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'EvnOnkoNotify_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'print' => array(
				array(
					'field' => 'EvnOnkoNotify_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnOnkoNotify_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnOnkoNotify_pid',
					'label' => 'Идентификатор движения или посещения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagPLSop_id',
					'label' => 'Идентификатор сопутствующего диагноза',
					'rules' => '',
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
					'field' => 'EvnOnkoNotify_setDT',
					'label' => 'Дата заболевания',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, заполнивший извещение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_sid',
					'label' => 'ЛПУ, куда направлено извещение',
					'rules' => 'required',
					'type' => 'id'
				),
				// Казахстан
				array(
					'field' => 'EvnOnkoNotify_setFirstDT',
					'label' => 'Дата первичного обращения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnOnkoNotify_setDiagDT',
					'label' => 'Дата установления диагноза',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Ethnos_id',
					'label' => 'Национальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TumorCircumIdentType_id',
					'label' => 'Обстоятельства выявления опухоли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TumorStage_id',
					'label' => 'Стадия опухолевого процесса',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoT_id',
					'label' => 'T',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoN_id',
					'label' => 'N',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'OnkoM_id',
					'label' => 'M',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getEvnOnkoNotifyList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getDataForSpecific' => array(
				array(
					'field' => 'Morbus_id',
					'label' => 'Идентификатор специфики',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
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
		$this->load->model('EvnOnkoNotify_model', 'EvnOnkoNotify');
	}
	
	/**
	 * Загрузка 
	 */
	function load() {
		
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnOnkoNotify->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение онко извещений пользователя(отправленных или включённых в регистр)
	 */
	function getEvnOnkoNotifyList() {
		$data = $this->ProcessInputData('getEvnOnkoNotifyList', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnOnkoNotify->getEvnOnkoNotifyList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Печать извещения 
	 */
	function getPrintForm() {
		
		$data = $this->ProcessInputData('print', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnOnkoNotify->getDataForPrint($data);
		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по извещению';
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
		$this->load->library('parser');
		return $this->parser->parse('print_evnonkonotify', $print_data);
	}
	
	
	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->EvnOnkoNotify->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	
	/**
	 * Список для специфики
	 */
	function getDataForSpecific()
	{
		$data = $this->ProcessInputData('getDataForSpecific', true);
		if ($data === false) { return false; }
		
		$response = $this->EvnOnkoNotify->getDataForSpecific($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
}