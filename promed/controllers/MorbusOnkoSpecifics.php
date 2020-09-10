<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 */

/**
 * MorbusOnkoSpecifics - Контроллер логического объекта "Специфика (онкология)"
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2015
 *
 * @property MorbusOnkoSpecifics_model $MorbusOnkoSpecifics_model
 * @property MorbusOnkoSpecTreat_model $MorbusOnkoSpecTreat_model
 * @property MorbusOnkoRefusal_model $MorbusOnkoRefusal_model
 */
class MorbusOnkoSpecifics extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->inputRules = array(
			'loadMorbusSpecific' => array(
				array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_pid', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'), // EvnVizitPL_id / EvnSection_id
				array('field' => 'EvnDiagPLSop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			),
			'saveMorbusOnkoLink' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор заболевания', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoLink_id', 'label' => 'Идентификатор результата диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoDiagConfType_id', 'label' => 'Метод подтверждения диагноза', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusOnkoLink_takeDT', 'label' => 'Дата взятия материала', 'rules' => '', 'type' => 'date'),
				array('field' => 'DiagAttribType_id', 'label' => 'Тип диагностического показателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagResult_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoVizitPLDop_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoLeave_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoDiagPLStom_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagAttribDict_id', 'label' => 'Диагностический показатель', 'rules' => '', 'type' => 'id')
			),
			'loadMorbusOnkoLinkDiagnosticsForm' => array(
				array('field' => 'MorbusOnkoLink_id', 'label' => 'Идентификатор результата диагностики', 'rules' => 'required', 'type' => 'id')
			),
			'deleteMorbusOnkoLink' => array(
				array('field' => 'MorbusOnkoLink_id', 'label' => 'Идентификатор результата диагностики', 'rules' => 'required', 'type' => 'id')
			),
			'getDiagnosticsFormParams' => array(
				array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Diag_id', 'label' => 'Диагнозн', 'rules' => 'required', 'type' => 'id')
			),
			'loadMorbusOnkoLinkList' => array(
				array('field' => 'Morbus_id', 'label' => 'Заболевание', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Событие', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusOnkoVizitPLDop_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoLeave_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnkoDiagPLStom_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id')
			),
			'loadPreOnkoRegister' => [
				['field' => 'Person_SurName', 'label' => '', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'Person_FirName', 'label' => '', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'Person_SecName', 'label' => '', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'Person_BirthDayYear', 'label' => '', 'rules' => '', 'type' => 'int'],
				['field' => 'Person_BirthDay', 'label' => '', 'rules' => '', 'type' => 'date'],
				['field' => 'Sex_id', 'label' => '', 'rules' => '', 'type' => 'id'],
				['field' => 'Diag_Code_From', 'label' => '', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'Diag_Code_To', 'label' => '', 'rules' => 'trim', 'type' => 'string'],
				['field' => 'MorbusOnko_setDateRange', 'label' => '', 'rules' => '', 'type' => 'daterange'],
				['field' => 'start', 'default' => 0, 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'],
				['field' => 'limit', 'default' => 100, 'label' => 'Количество записей', 'rules' => '', 'type' => 'int']
			],
			'doRegisterOut' => [
				['field' => 'Morbus_id', 'label' => '', 'rules' => 'required', 'type' => 'id'],
			],
			'checkRegister' => [
				['field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'],
			],
			'checkMorbusExists' => [
				['field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'],
				['field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'],
			]
		);
	}

	/**
	 * Сохранение онкоспецифики
	 * Вызывается с клиента из:
	 * 1) Панели просмотра движения/посещения в ЭМК (\jscore\Forms\Common\swPersonEmkWindow.js)
	 * 2) Панели формы просмотра регистра (\jscore\Forms\Morbus\Specifics\swMorbusOnkoWindow.js)
	 * @author Alexander Permyakov aka Alexpm
	 * @comment Так сделано, чтобы вся логика по сохранению онкоспецифики была в одном месте в MorbusOnkoSpecifics_model->saveMorbusSpecific для удобства поддержки.
	 */
	function saveMorbusSpecific()
	{
		//getMorbusOnkoSpecificsRules()['save'] не подходит, т.к. там есть данные, которые с клиента не приходят и могут быть затерты
		$this->inputRules['saveMorbusSpecific'] = array(
			array('field' => 'Mode','label' => 'Режим сохранения','rules' => 'trim|required','type' => 'string'),
			array('field' => 'Diag_id', 'label' => 'Диагноз                                                         ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Движение/Посещение                                              ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Человек', 'rules' => '', 'type' => 'id'),
			//ОнкоСпецифика заболевания
			array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания                                       ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор специфики заболевания                         ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusOnko_firstSignDT', 'label' => 'Дата появления первых признаков заболевания                     ', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusOnko_firstVizitDT', 'label' => 'Дата первого обращения                                          ', 'rules' => '', 'type' => 'date'),
			array('field' => 'Lpu_foid', 'label' => 'В какое медицинское учреждение                                  ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRegType_id', 'label' => 'Взят на учет в ОД                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRegOutType_id', 'label' => 'Причина снятия с учета                                          ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoLesionSide_id', 'label' => 'Сторона поражения                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoDiag_mid', 'label' => 'Морфологический тип опухоли. (Гистология опухоли)               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_NumHisto', 'label' => 'Номер гистологического исследования                             ', 'rules' => '', 'type' => 'string'),
			array('field' => 'MorbusOnko_NumTumor', 'label' => 'Порядковый номер данной опухоли                             ', 'rules' => 'required', 'type' => 'int', 'default' => 0),
			array('field' => 'OnkoT_id', 'label' => 'T                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoN_id', 'label' => 'N                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoM_id', 'label' => 'M                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorStage_id', 'label' => 'Стадия опухолевого процесса                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoT_fid', 'label' => 'T                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoN_fid', 'label' => 'N                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoM_fid', 'label' => 'M                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorStage_fid', 'label' => 'Стадия опухолевого процесса                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagAttribType_id', 'label' => 'Тип диагностического показателя                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagAttribDict_id', 'label' => 'Диагностический показатель                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagResult_id', 'label' => 'Результат диагностики                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoUnknown', 'label' => 'Локализация отдаленных метастазов: Неизвестна                   ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLympha', 'label' => 'Локализация отдаленных метастазов: Отдаленные лимфатические узлы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoBones', 'label' => 'Локализация отдаленных метастазов: Кости                        ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLiver', 'label' => 'Локализация отдаленных метастазов: Печень                       ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLungs', 'label' => 'Локализация отдаленных метастазов: Легкие и/или плевра          ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoBrain', 'label' => 'Локализация отдаленных метастазов: Головной мозг                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoSkin', 'label' => 'Локализация отдаленных метастазов: Кожа                         ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoKidney', 'label' => 'Локализация отдаленных метастазов: Почки                        ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoOvary', 'label' => 'Локализация отдаленных метастазов: Яичники                      ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoPerito', 'label' => 'Локализация отдаленных метастазов: Брюшина                      ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoMarrow', 'label' => 'Локализация отдаленных метастазов: Костный мозг                 ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoOther', 'label' => 'Локализация отдаленных метастазов: Другие органы                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoMulti', 'label' => 'Множественные                                                   ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorCircumIdentType_id', 'label' => 'Обстоятельства выявления опухоли                                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoLateDiagCause_id', 'label' => 'Причины поздней диагностики                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'AutopsyPerformType_id', 'label' => 'Аутопсия', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorAutopsyResultType_id', 'label' => 'Результат аутопсии применительно к данной опухоли               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsMainTumor', 'label' => 'Призак основной опухоли', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusOnko_NumTumor', 'label' => 'Порядковый номер данной опухоли', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusOnko_setDiagDT', 'label' => 'Дата установления диагноза', 'rules' => '', 'type' => 'date'),
			//ОнкоСпецифика общего заболевания
			array('field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор онкоспецифики общего заболевания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusBase_id', 'label' => 'Общее заболевание', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_NumCard', 'label' => 'Порядковый номер регистрационной карты', 'rules' => 'trim', 'type' => 'string'),
			// с клиента не приходит array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному (онкологическому) заболеванию', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_deadDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
			array('field' => 'Diag_did', 'label' => 'Диагноз причины смерти', 'rules' => '', 'type' => 'id'),
			// с клиента не приходит array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorPrimaryMultipleType_id', 'label' => 'Первично-множественная опухоль', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoStatusYearEndType_id', 'label' => 'Клиническая группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному заболеванию', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRegOutType_id', 'label' => 'Причина снятия с учета', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRegType_id', 'label' => 'взят на учет в ОД', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorPrimaryMultipleType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoDiagConfType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoDiagConfTypes', 'label' => 'Методы подтверждения диагноза', 'rules' => '', 'type' => 'string'),
			array('field' => 'MorbusOnko_takeDT', 'label' => 'Дата взятия материала', 'rules' => '', 'type' => 'date'),
			array('field' => 'HistologicReasonType_id', 'label' => 'Отказ / противопоказание', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_histDT', 'label' => 'Дата регистрации отказа / противопоказания', 'rules' => '', 'type' => 'date'),
			array('field' => 'OnkoPostType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoVariance_id', 'label' => 'Вариантность', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRiskGroup_id', 'label' => 'Группа риска', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoResistance_id', 'label' => 'Резистентность', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoStatusBegType_id', 'label' => 'Клиническая группа при взятии на учет', 'rules' => '', 'type' => 'id'),
			//Атрибуты общего заболевания
			array('field' => 'MorbusBase_setDT', 'label' => 'Дата взятия на учет в ОД', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusBase_disDT', 'label' => 'Дата снятия с учета в ОД', 'rules' => '', 'type' => 'date'),
			array('field' => 'OnkoTreatment_id', 'label' => 'Повод обращения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoDiagPLStom_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoVizitPLDop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoLeave_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPLStomSop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPLSop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
		);

		$data = $this->ProcessInputData('saveMorbusSpecific', true);
		if ($data) {
			$this->load->model('MorbusOnkoSpecifics_model');
			$response = $this->MorbusOnkoSpecifics_model->saveMorbusSpecific($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы "Специальное лечение"
	 */
	function loadMorbusOnkoSpecTreatList()
	{
		$this->load->model('MorbusOnkoSpecTreat_model');
		$this->inputRules['loadMorbusOnkoSpecTreatList'] = $this->MorbusOnkoSpecTreat_model->getInputRules(swModel::SCENARIO_VIEW_DATA);
		$data = $this->ProcessInputData('loadMorbusOnkoSpecTreatList', true);
		if (!$data) return false;
		$response = $this->MorbusOnkoSpecTreat_model->getViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных для формы "Специальное лечение"
	 */
	function loadMorbusOnkoSpecTreatEditForm()
	{
		$this->load->model('MorbusOnkoSpecTreat_model');
		$this->inputRules['loadMorbusOnkoSpecTreatEditForm'] = $this->MorbusOnkoSpecTreat_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadMorbusOnkoSpecTreatEditForm', true);
		if ($data) {
			$response = $this->MorbusOnkoSpecTreat_model->doLoadEditForm($data);
			$this->ProcessModelList($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление "Специального лечения"
	 */
	function deleteMorbusOnkoSpecTreat()
	{
		$this->load->model('MorbusOnkoSpecTreat_model');
		$this->inputRules['deleteMorbusOnkoSpecTreat'] = array(
			array('field' => 'id','label' => 'Идентификатор Специального лечения','rules' => 'trim|required','type' => 'id')
		);
		$data = $this->ProcessInputData('deleteMorbusOnkoSpecTreat', true);
		if ($data) {
			$data['MorbusOnkoSpecTreat_id'] = $data['id'];
			$response = $this->MorbusOnkoSpecTreat_model->deleteMorbusOnkoSpecTreat($data);
			$this->ProcessModelSave($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы "Специальное лечение"
	 */
	function getMorbusOnkoSpecTreatDisabledDates()
	{
		$this->load->model('MorbusOnkoSpecTreat_model');
		$this->inputRules['getMorbusOnkoSpecTreatDisabledDates'] = $this->MorbusOnkoSpecTreat_model->getInputRules('doLoadDisabledDates');
		$data = $this->ProcessInputData('getMorbusOnkoSpecTreatDisabledDates', true);
		if ($data) {
			$response = $this->MorbusOnkoSpecTreat_model->doLoadDisabledDates($data);
			$this->ProcessModelList($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение данных из формы "Специальное лечение"
	 */
	function saveMorbusOnkoSpecTreatEditForm()
	{
		$this->load->model('MorbusOnkoSpecTreat_model');
		$this->inputRules['saveMorbusOnkoSpecTreatEditForm'] = $this->MorbusOnkoSpecTreat_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveMorbusOnkoSpecTreatEditForm', true);
		if ($data) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			$response = $this->MorbusOnkoSpecTreat_model->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение данных из формы "Сопуствующее заболевание"
	 */
	function saveMorbusOnkoSopDiagEditForm()
	{
		$this->load->model('MorbusOnkoSopDiag_model');
		$this->inputRules['saveMorbusOnkoSopDiag'] = $this->MorbusOnkoSopDiag_model->inputRules['saveMorbusOnkoSopDiag'];
		$data = $this->ProcessInputData('saveMorbusOnkoSopDiag', true);
		if ($data) {
			$response = $this->MorbusOnkoSopDiag_model->saveMorbusOnkoSopDiag($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление "Сопуствующее заболевание"
	 */
	function deleteMorbusOnkoSopDiag()
	{
		$this->load->model('MorbusOnkoSopDiag_model');
		$this->inputRules['deleteMorbusOnkoSopDiag'] = array(
			array('field' => 'id','label' => 'Идентификатор Сопуствующего заболевания','rules' => 'trim|required','type' => 'id')
		);
		$data = $this->ProcessInputData('deleteMorbusOnkoSopDiag', true);
		if ($data) {
			$data['MorbusOnkoBaseDiagLink_id'] = $data['id'];
			$response = $this->MorbusOnkoSopDiag_model->deleteMorbusOnkoSopDiag($data);
			$this->ProcessModelSave($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных формы "Сопуствующее заболевание"
	 */
	function loadMorbusOnkoSopDiagEditForm()
	{
		$this->load->model('MorbusOnkoSopDiag_model');
		$this->inputRules['loadMorbusOnkoSopDiag'] = $this->MorbusOnkoSopDiag_model->inputRules['loadMorbusOnkoSopDiag'];
		$data = $this->ProcessInputData('loadMorbusOnkoSopDiag', true);
		if ($data) {
			$response = $this->MorbusOnkoSopDiag_model->loadMorbusOnkoSopDiag($data);
			$this->ProcessModelList($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Выгрузка регистра онкобольных
	 */
	function exportMorbusOnkoData() {
		$data = array();
		$response = array('Link' => '', 'Error_Msg' => '', 'success' => false);

		$this->load->model('MorbusOnkoSpecifics_model');
		$export_data = $this->MorbusOnkoSpecifics_model->exportMorbusOnkoData($data);
		if (!empty($export_data['Error_Msg'])) {
			$this->ReturnError($export_data['Error_Msg']);
			return false;
		}

		$export_data['region'] = $_SESSION['region']['number'];
		$export_data['region_name'] = $_SESSION['region']['name'];
		$export_data['system'] = 'Promed';
		$export_data['modulename'] = 'MorbusOnkoSpecifics';
		$export_data['haspd'] = 1;
		$export_data['usehash'] = 0;

		$this->load->library('parser');
		$template = 'export_morbus_onko_data';

		$path = EXPORTPATH_ROOT."export_morbus_onko_data/";

		if (!file_exists($path)) {
			mkdir( $path );
		}

		$file_zip_sign = 'export_morbus_onko_data_'.time();
		$file_zip_name = $path."/".$file_zip_sign.".zip";

		$zip = new ZipArchive();

		$file_name = 'export_morbus_onko_data_'.time();
		$file_path = $path."/".$file_name.".xml";

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/'.$template, $export_data, true), true);

		file_put_contents($file_path, $xml);

		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_path, $file_name . ".xml" );
		$zip->close();

		unlink($file_path);

		if (file_exists($file_zip_name)) {
			$response['success'] = true;
			$response['Link'] = $file_zip_name;
		} else {
			$response['Error_Msg'] = 'Ошибка создания архива!';
		}
		$this->ReturnData($response);
		return true;
	}
	
	/**
	 * Получение стыковочной таблицы диагноза и результата диагностики
	 */
	public function loadDiagnosisResultDiagLinkStore() {
		$this->load->model('MorbusOnkoSpecifics_model');
		$result = $this->MorbusOnkoSpecifics_model->loadDiagnosisResultDiagLinkStore();
		$this->ProcessModelList($result, true, false)->ReturnData();
	}

	/**
	 * Загрузка онкоспецифики
	 */
	function loadMorbusSpecific()
	{
		$this->load->model('MorbusOnkoSpecifics_model');
		$data = $this->ProcessInputData('loadMorbusSpecific', true);
		if (!$data) return false;

		$response = $this->MorbusOnkoSpecifics_model->getViewData($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
	}

	/**
	 * Чистка онкоспецифики
	 */
	function clearMorbusOnkoSpecifics()
	{
		$this->inputRules['clearMorbusOnkoSpecifics'] = array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'int')
		);
		$this->load->model('MorbusOnkoSpecifics_model');
		$data = $this->ProcessInputData('clearMorbusOnkoSpecifics', true);
		if (!$data) return false;

		$response = $this->MorbusOnkoSpecifics_model->clearMorbusOnkoSpecifics($data);
		$this->ProcessModelSave($response, true, false)->ReturnData();
	}

	/**
	 * Проверка корректного заполнения услуг в специфике
	 */
	function checkMorbusOnkoSpecificsUsluga()
	{
		$this->inputRules['checkMorbusOnkoSpecificsUsluga'] = array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'int')
		);
		$this->load->model('MorbusOnkoSpecifics_model');
		$data = $this->ProcessInputData('checkMorbusOnkoSpecificsUsluga', true);
		if (!$data) return false;

		$response = $this->MorbusOnkoSpecifics_model->checkMorbusOnkoSpecificsUsluga($data);
		$this->ProcessModelSave($response, true, false)->ReturnData();
	}

	/**
	 * Получение списка "Данные об отказах / противопоказаниях"
	 */
	public function loadMorbusOnkoRefusalList()
	{
		$this->load->model('MorbusOnkoRefusal_model');
		$this->inputRules['loadMorbusOnkoRefusalList'] = $this->MorbusOnkoRefusal_model->getInputRules(swModel::SCENARIO_VIEW_DATA);
		$data = $this->ProcessInputData('loadMorbusOnkoRefusalList', true);
		if (!$data) return false;
		$response = $this->MorbusOnkoRefusal_model->getViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных для формы "Данные об отказах / противопоказаниях"
	 */
	public function loadMorbusOnkoRefusalEditForm()
	{
		$this->load->model('MorbusOnkoRefusal_model');
		$this->inputRules['loadMorbusOnkoRefusalEditForm'] = $this->MorbusOnkoRefusal_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadMorbusOnkoRefusalEditForm', true);
		if ($data) {
			$response = $this->MorbusOnkoRefusal_model->doLoadEditForm($data);
			$this->ProcessModelList($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление "Данных об отказах / противопоказаниях"
	 */
	public function deleteMorbusOnkoRefusal()
	{
		$this->load->model('MorbusOnkoRefusal_model');
		$this->inputRules['deleteMorbusOnkoRefusal'] = array(
			array('field' => 'id','label' => 'Идентификатор данных об отказах / противопоказаниях','rules' => 'trim|required','type' => 'id')
		);
		$data = $this->ProcessInputData('deleteMorbusOnkoRefusal', true);
		if ($data) {
			$data['MorbusOnkoRefusal_id'] = $data['id'];
			$response = $this->MorbusOnkoRefusal_model->deleteMorbusOnkoRefusal($data);
			$this->ProcessModelSave($response, true, false)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение данных из формы "Данные об отказах / противопоказаниях"
	 */
	public function saveMorbusOnkoRefusalEditForm()
	{
		$this->load->model('MorbusOnkoRefusal_model');
		$this->inputRules['saveMorbusOnkoRefusalEditForm'] = $this->MorbusOnkoRefusal_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveMorbusOnkoRefusalEditForm', true);
		if ($data) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			$response = $this->MorbusOnkoRefusal_model->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	  Сохранение результата диагностики
	 */
	public function saveMorbusOnkoLink(){
		$data = $this->ProcessInputData('saveMorbusOnkoLink', true);
		if ($data === false) { return false; }

		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->saveMorbusOnkoLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	  Загрузка формы результата диагностики
	 */
	public function loadMorbusOnkoLinkDiagnosticsForm(){
		$data = $this->ProcessInputData('loadMorbusOnkoLinkDiagnosticsForm', true);

		if ($data === false) {
			return false;
		}
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->loadMorbusOnkoLinkDiagnosticsForm($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Удаление результата диагностики
	 */
	public function deleteMorbusOnkoLink(){
		$data = $this->ProcessInputData('deleteMorbusOnkoLink');
		if($data === false) { return false; }
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->deleteMorbusOnkoLink($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Доп параметры для формы "Результаты диагностики"
	 */
	public function getDiagnosticsFormParams(){
		$data = $this->ProcessInputData('getDiagnosticsFormParams', true);

		if ($data === false) {
			return false;
		}
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->getDiagnosticsFormParams($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Получение данных для таблицы "Результаты диагностики"
	 */
	function loadMorbusOnkoLinkList()
	{
		$this->load->model('MorbusOnkoSpecifics_model');
		$data = $this->ProcessInputData('loadMorbusOnkoLinkList', true);
		if (!$data) return false;
		$response = $this->MorbusOnkoSpecifics_model->getMorbusOnkoLinkViewData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Регистр пациентов с предраковым состоянием
	 */
	function loadPreOnkoRegister() {
		$this->load->model('MorbusOnkoSpecifics_model');
		$data = $this->ProcessInputData('loadPreOnkoRegister', true);
		if (!$data) return false;
		$response = $this->MorbusOnkoSpecifics_model->loadPreOnkoRegister($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Исключение из регистра
	 */
	function doRegisterOut() {
		$data = $this->ProcessInputData('doRegisterOut');
		if($data === false) { return false; }
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->doRegisterOut($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Проверка, что у пациента нет записи в регистре по онкологии и нет извещения
	 */
	function checkRegister() {
		$data = $this->ProcessInputData('checkRegister');
		if($data === false) { return false; }
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->checkRegister($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Проверяется наличие специфики и заболевания (у которого не проставлена дата окончания заболевания) 
	 */
	function checkMorbusExists() {
		$data = $this->ProcessInputData('checkMorbusExists');
		if($data === false) { return false; }
		$this->load->model('MorbusOnkoSpecifics_model');
		$response = $this->MorbusOnkoSpecifics_model->checkMorbusExists($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}