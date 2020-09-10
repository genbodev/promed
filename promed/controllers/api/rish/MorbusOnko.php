<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы со спецификой о новорождённом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class MorbusOnko extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MorbusOnkoSpecifics_model', 'dbmodel');
		$this->inputRules = array(
			'getMorbusOnko' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_pid', 'label' => 'Идентификатор события-родителя', 'rules' => '', 'type' => 'id'),
			),
			'createMorbusOnko' => array(
				//array('field' => 'PersonRegister_id', 'label' => 'PersonRegister_id', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_pid', 'label' => 'Движение/Посещение', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Человек', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_NumCard', 'label' => 'MorbusOnko_NumCard', 'rules' => '', 'type' => 'string'),
				array('field' => 'MorbusOnko_NumTumor', 'label' => 'MorbusOnko_NumTumor', 'rules' => '', 'type' => 'string'),
				array('field' => 'MorbusOnko_MorfoDiag', 'label' => 'MorbusOnko_MorfoDiag', 'rules' => '', 'type' => 'string'),
				array('field' => 'TumorPrimaryTreatType_id', 'label' => 'TumorPrimaryTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorRadicalTreatIncomplType_id', 'label' => 'TumorRadicalTreatIncomplType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_specSetDT', 'label' => 'MorbusOnko_specSetDT', 'rules' => '', 'type' => 'date'),
				array('field' => 'orbusOnko_specDisDT', 'label' => 'orbusOnko_specDisDT', 'rules' => '', 'type' => 'date'),
				array('field' => 'OnkoCombiTreatType_id', 'label' => 'OnkoCombiTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLateComplTreatType_id', 'label' => 'OnkoLateComplTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoTumorStatusType_id', 'label' => 'Состояние опухолевого процесса (справочник dbo.OnkoTumorStatusType)', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_Deleted', 'label' => 'Признак удаления', 'rules' => '', 'type' => 'int'),
					//ОнкоСпецифика заболевания
					//array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания                                       ', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusOnko_id', 'label' => 'Идентификатор специфики заболевания                         ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_firstSignDT', 'label' => 'Дата появления первых признаков заболевания', 'rules' => '', 'type' => 'date'),
				array('field' => 'MorbusOnko_firstVizitDT', 'label' => 'Дата первого обращения', 'rules' => '', 'type' => 'date'),
				array('field' => 'Lpu_foid', 'label' => 'В какое медицинское учреждение', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoRegType_id', 'label' => 'Взят на учет в ОД', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoRegOutType_id', 'label' => 'Причина снятия с учета                                          ', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLesionSide_id', 'label' => 'Сторона поражения', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoDiag_mid', 'label' => 'Морфологический тип опухоли. (Гистология опухоли)', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_NumHisto', 'label' => 'Номер гистологического исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'OnkoT_id', 'label' => 'T', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'OnkoN_id', 'label' => 'N', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'OnkoM_id', 'label' => 'M', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'TumorStage_id', 'label' => 'Стадия опухолевого процесса', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DiagAttribType_id', 'label' => 'Тип диагностического показателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagAttribDict_id', 'label' => 'Диагностический показатель', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagResult_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoUnknown', 'label' => 'Локализация отдаленных метастазов: Неизвестна', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLympha', 'label' => 'Локализация отдаленных метастазов: Отдаленные лимфатические узлы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoBones', 'label' => 'Локализация отдаленных метастазов: Кости', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLiver', 'label' => 'Локализация отдаленных метастазов: Печень', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLungs', 'label' => 'Локализация отдаленных метастазов: Легкие и/или плевра', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoBrain', 'label' => 'Локализация отдаленных метастазов: Головной мозг', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoSkin', 'label' => 'Локализация отдаленных метастазов: Кожа', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoKidney', 'label' => 'Локализация отдаленных метастазов: Почки', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoOvary', 'label' => 'Локализация отдаленных метастазов: Яичники', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoPerito', 'label' => 'Локализация отдаленных метастазов: Брюшина', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoMarrow', 'label' => 'Локализация отдаленных метастазов: Костный мозг', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoOther', 'label' => 'Локализация отдаленных метастазов: Другие органы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoMulti', 'label' => 'Множественные', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorCircumIdentType_id', 'label' => 'Обстоятельства выявления опухоли', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLateDiagCause_id', 'label' => 'Причины поздней диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'AutopsyPerformType_id', 'label' => 'Аутопсия', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorAutopsyResultType_id', 'label' => 'Результат аутопсии применительно к данной опухоли               ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsMainTumor', 'label' => 'Призак основной опухоли', 'rules' => '', 'type' => 'int'),
				array('field' => 'MorbusOnko_setDiagDT', 'label' => 'Дата установления диагноза', 'rules' => '', 'type' => 'date'),
					//ОнкоСпецифика общего заболевания
					//array('field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор онкоспецифики общего заболевания', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusBase_id', 'label' => 'Общее заболевание', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusOnkoBase_NumCard', 'label' => 'Порядковый номер регистрационной карты', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'MorbusOnkoBase_deadDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
				array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'причина смерти', 'rules' => '', 'type' => 'string'),
				array('field' => 'Diag_did', 'label' => 'диагноз причины смерти', 'rules' => '', 'type' => 'id'),
					// с клиента не приходит array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorPrimaryMultipleType_id', 'label' => 'Первично-множественная опухоль', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoStatusYearEndType_id', 'label' => 'клиническая группа', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному заболеванию', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoRegOutType_id', 'label' => 'Причина снятия с учета', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoRegType_id', 'label' => 'взят на учет в ОД', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoDiagConfType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoDiagConfTypes', 'label' => 'Методы подтверждения диагноза', 'rules' => '', 'type' => 'string'),
				array('field' => 'MorbusOnko_takeDT', 'label' => 'Дата взятия материала', 'rules' => '', 'type' => 'date'),
				array('field' => 'HistologicReasonType_id', 'label' => 'Отказ / противопоказание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_histDT', 'label' => 'Дата регистрации отказа / противопоказания', 'rules' => '', 'type' => 'date'),
				array('field' => 'OnkoPostType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoVariance_id', 'label' => 'Вариантность', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoRiskGroup_id', 'label' => 'Группа риска', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoResistance_id', 'label' => 'Резистентность', 'rules' => '', 'type' => 'id'),
					//array('field' => 'OnkoStatusBegType_id', 'label' => 'Клиническая группа при взятии на учет', 'rules' => '', 'type' => 'id'),
					//Атрибуты общего заболевания
					//array('field' => 'MorbusBase_setDT', 'label' => 'Дата взятия на учет в ОД', 'rules' => '', 'type' => 'date'),
					//array('field' => 'MorbusBase_disDT', 'label' => 'Дата снятия с учета в ОД', 'rules' => '', 'type' => 'date'),
				array('field' => 'OnkoTreatment_id', 'label' => 'Повод обращения', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusOnkoDiagPLStom_id', 'label' => '', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusOnkoVizitPLDop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
					//array('field' => 'MorbusOnkoLeave_id', 'label' => '', 'rules' => '', 'type' => 'id'),
					//array('field' => 'EvnDiagPLStomSop_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			),
			'updateMorbusOnko' => array(
				array('field' => 'MorbusOnko_id', 'label' => 'MorbusOnko_id', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'Morbus_id', 'label' => 'Morbus_id', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_pid', 'label' => 'Движение/Посещение', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Человек', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_NumCard', 'label' => 'MorbusOnko_NumCard', 'rules' => '', 'type' => 'string'),
				array('field' => 'MorbusOnko_NumTumor', 'label' => 'MorbusOnko_NumTumor', 'rules' => '', 'type' => 'string'),
				array('field' => 'MorbusOnko_MorfoDiag', 'label' => 'MorbusOnko_MorfoDiag', 'rules' => '', 'type' => 'string'),
				array('field' => 'TumorPrimaryTreatType_id', 'label' => 'TumorPrimaryTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorRadicalTreatIncomplType_id', 'label' => 'TumorRadicalTreatIncomplType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_specSetDT', 'label' => 'MorbusOnko_specSetDT', 'rules' => '', 'type' => 'date'),
				array('field' => 'orbusOnko_specDisDT', 'label' => 'orbusOnko_specDisDT', 'rules' => '', 'type' => 'date'),
				array('field' => 'OnkoCombiTreatType_id', 'label' => 'OnkoCombiTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLateComplTreatType_id', 'label' => 'OnkoLateComplTreatType_id', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoTumorStatusType_id', 'label' => 'Состояние опухолевого процесса (справочник dbo.OnkoTumorStatusType)', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_Deleted', 'label' => 'Признак удаления', 'rules' => '', 'type' => 'int'),
				array('field' => 'MorbusOnko_firstSignDT', 'label' => 'Дата появления первых признаков заболевания', 'rules' => '', 'type' => 'date'),
				array('field' => 'MorbusOnko_firstVizitDT', 'label' => 'Дата первого обращения', 'rules' => '', 'type' => 'date'),
				array('field' => 'Lpu_foid', 'label' => 'В какое медицинское учреждение', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLesionSide_id', 'label' => 'Сторона поражения', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoDiag_mid', 'label' => 'Морфологический тип опухоли. (Гистология опухоли)', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_NumHisto', 'label' => 'Номер гистологического исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'OnkoT_id', 'label' => 'T', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoN_id', 'label' => 'N', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoM_id', 'label' => 'M', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorStage_id', 'label' => 'Стадия опухолевого процесса', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagAttribType_id', 'label' => 'Тип диагностического показателя', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagAttribDict_id', 'label' => 'Диагностический показатель', 'rules' => '', 'type' => 'id'),
				array('field' => 'DiagResult_id', 'label' => 'Результат диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoUnknown', 'label' => 'Локализация отдаленных метастазов: Неизвестна', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLympha', 'label' => 'Локализация отдаленных метастазов: Отдаленные лимфатические узлы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoBones', 'label' => 'Локализация отдаленных метастазов: Кости', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLiver', 'label' => 'Локализация отдаленных метастазов: Печень', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoLungs', 'label' => 'Локализация отдаленных метастазов: Легкие и/или плевра', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoBrain', 'label' => 'Локализация отдаленных метастазов: Головной мозг', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoSkin', 'label' => 'Локализация отдаленных метастазов: Кожа', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoKidney', 'label' => 'Локализация отдаленных метастазов: Почки', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoOvary', 'label' => 'Локализация отдаленных метастазов: Яичники', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoPerito', 'label' => 'Локализация отдаленных метастазов: Брюшина', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoMarrow', 'label' => 'Локализация отдаленных метастазов: Костный мозг', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoOther', 'label' => 'Локализация отдаленных метастазов: Другие органы', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsTumorDepoMulti', 'label' => 'Множественные', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorCircumIdentType_id', 'label' => 'Обстоятельства выявления опухоли', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoLateDiagCause_id', 'label' => 'Причины поздней диагностики', 'rules' => '', 'type' => 'id'),
				array('field' => 'AutopsyPerformType_id', 'label' => 'Аутопсия', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorAutopsyResultType_id', 'label' => 'Результат аутопсии применительно к данной опухоли               ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_IsMainTumor', 'label' => 'Призак основной опухоли', 'rules' => '', 'type' => 'int'),
				array('field' => 'MorbusOnko_setDiagDT', 'label' => 'Дата установления диагноза', 'rules' => '', 'type' => 'date'),
				array('field' => 'MorbusOnkoBase_deadDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
				array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'причина смерти', 'rules' => '', 'type' => 'string'),
				array('field' => 'Diag_did', 'label' => 'диагноз причины смерти', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorPrimaryMultipleType_id', 'label' => 'Первично-множественная опухоль', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoStatusYearEndType_id', 'label' => 'клиническая группа', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному заболеванию', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoDiagConfType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_takeDT', 'label' => 'Дата взятия материала', 'rules' => '', 'type' => 'date'),
				array('field' => 'HistologicReasonType_id', 'label' => 'Отказ / противопоказание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MorbusOnko_histDT', 'label' => 'Дата регистрации отказа / противопоказания', 'rules' => '', 'type' => 'date'),
				array('field' => 'OnkoPostType_id', 'label' => 'Первично', 'rules' => '', 'type' => 'id'),
				array('field' => 'OnkoTreatment_id', 'label' => 'Повод обращения', 'rules' => '', 'type' => 'id'),
			)
		);
	}

	/**
	 *  Получение информации
	 */
	function index_get() {
		$data = $this->ProcessInputData('getMorbusOnko');

		if (empty($data['Person_id']) && empty($data['Evn_pid'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->getMorbusOnkoForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Изменение данных по специфике онкологии
	 */
	function index_post(){
		$this->response(array(
			'error_code' => 1,
			'Error_Msg' => 'Метод не предусмотрен'
		));
	}
	
	/**
	 * Изменение данных по специфике онкологии
	 */
	function index_put(){
		$data = $this->ProcessInputData('updateMorbusOnko', null, true);
		$arrayParmsOneTwo = array(
			'MorbusOnko_IsMainTumor',
			'MorbusOnko_IsTumorDepoUnknown',
			'MorbusOnko_IsTumorDepoLympha',
			'MorbusOnko_IsTumorDepoBones',
			'MorbusOnko_IsTumorDepoLiver',
			'MorbusOnko_IsTumorDepoLungs',
			'MorbusOnko_IsTumorDepoBrain',
			'MorbusOnko_IsTumorDepoSkin',
			'MorbusOnko_IsTumorDepoKidney',
			'MorbusOnko_IsTumorDepoOvary',
			'MorbusOnko_IsTumorDepoPerito',
			'MorbusOnko_IsTumorDepoMarrow',
			'MorbusOnko_IsTumorDepoOther',
			'MorbusOnko_IsTumorDepoMulti'
		);
		foreach ($arrayParmsOneTwo as $value) {
			if(!empty($data[$value]) && !in_array($data[$value], array(1,2))){
				$this->response(array(
					'error_code' => 1,
					'Error_Msg' => 'Пареметр '.$value.' может иметь только занчение 1 или 2'
				));
			}
		}
		if(!empty($data['MorbusOnko_NumCard'])){
			$data['MorbusOnkoBase_NumCard'] = $data['MorbusOnko_NumCard'];
		}
		$resp = $this->dbmodel->updateMorbusOnkoForAPI($data);
		
		if(empty($resp['Error_Msg']) && !empty($resp[0]['Morbus_id'])){
			$this->response(array(
				'error_code' => 0
			));
		}else{
			$this->response(array(
				'error_code' => 1,
				'Error_Msg' => (!empty($resp['Error_Msg'])) ? $resp['Error_Msg'] : $resp
			));
		}
	}
}