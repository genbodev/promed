<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с направлениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property EvnDirection_model dbmodel
*/
class EvnDirection extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnDirection_model', 'dbmodel');
		$this->inputRules = array(
			'loadEvnDirection' => array(
				array('field' => 'Evn_pid', 'label' => 'Идентификатор случая-родителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => '', 'type' => 'date'),
				array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО, куда направили', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_beg', 'label' => 'Дата и время начала периода изменения направления на исследование', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'EvnDirection_end', 'label' => 'Дата и время окончания периода изменения направления на исследование', 'rules' => '', 'type' => 'datetime')
			),
			'getTimeTableStacById' => array(
				array('field' => 'TimeTableStac_id', 'label' => 'Идентификатор бирки по стационару', 'rules' => 'required', 'type' => 'id')
			),
			'getTimeTableMedServiceById' => array(
				array('field' => 'TimeTableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id')
			),
			'getTimeTableResourceById' => array(
				array('field' => 'TimeTableResource_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id')
			),
			'createEvnDirection' => array(
				array('field' => 'Evn_pid', 'label' => 'Идентификатор случая-родителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnDirection_Descr', 'label' => 'Обоснование', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_sid', 'label' => 'Направившее МО', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'LpuSection_id', 'label' => 'Направившее отделение МО', 'rules' => 'required', 'type' => 'id'),
				//array('field' => 'MedPersonal_id', 'label' => 'Направивший врач', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы направившего врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_zid', 'label' => 'Заведующий направившего отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_did', 'label' => 'МО, куда направили', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuSection_did', 'label' => 'Отделение, куда направили', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_did', 'label' => 'Группа отделений, куда направили', 'rules' => '', 'type' => 'id'),//required
				array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль, куда направили', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_did', 'label' => 'Врач, к кому направили', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableStac_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableMedService_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableResource_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id'),
				array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPrescr_IsCito', 'label' => 'Признак "Cito"', 'rules' => '', 'type' => 'api_flag'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
				array('field' => 'Resource_id', 'label' => 'Идентификатор ресурса', 'rules' => '', 'type' => 'id'),
				array('field' => 'fromMobile', 'label' => 'Признак моб. устройства', 'rules' => '', 'type' => 'boolean'),
				array('field' => 'StudyTarget_id', 'label' => 'Цель исследования', 'rules' => '', 'type' => 'id'),
				array('field' => 'RemoteConsultCause_id', 'label' => 'Цель удаленной консультации', 'rules' => '', 'type' => 'id'),
			),
			'cancelEvnDirection' => array(
				array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnStatusCause_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
				//array('field' => 'EvnComment_Comment', 'label' => 'Комментарий отмены направления', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnStatusHistory_Cause', 'label' => 'Комментарий к причине отмены направления', 'rules' => '', 'type' => 'string')
			),
			'loadEvnDirectionList' => array(
				array('field' => 'Evn_pid', 'label' => 'Идентификатор случая-родителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id')
			),
			'mloadEvnDirectionPanel' => array(
				array('field' => 'EvnDirection_pid', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id')
			),
			'loadEvnDirectionEditForm' => array(
				array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id'),
				array('field' => 'EvnVizitPL_id', 'label' => 'Посещение', 'rules' => '', 'type' => 'id'),
				array('field' => 'TimetableGraf_id', 'label' => 'Расписание', 'rules' => '', 'type' => 'id')
			),
			'EvnDirectionUslugaComplex' => array(
				array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'идентификатор МО', 'rules' => '', 'type' => 'id')
			),
			'createEvnLabSample' => array(
				array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления на лабораторное исследование', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplexList', 'label' => 'Список  идентфикаторов услуг, по которым взята проба', 'rules' => 'required', 'type' => 'string'),				
				array('field' => 'MedStaffFact_did', 'label' => 'Место работы врача, взявшего пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_did', 'label' => 'МО, взявшая пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_did', 'label' => 'Отделение, взявшее пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_did', 'label' => 'Врач, взявший пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_sdid', 'label' => 'Средний медперсонал, взявший пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_did', 'label' => 'Служба, в которой взята проба', 'rules' => '', 'type' => 'id'),
				//array('field' => 'MedService_id', 'label' => 'Служба, в которой выполняется заявка', 'rules' => '', 'type' => 'id'),
				//array('field' => 'MedService_sid', 'label' => 'Служба, забраковавшая пробу', 'rules' => '', 'type' => 'id'),
				//array('field' => 'MedPersonal_said', 'label' => 'Средний медперсонал, выполнивший анализ', 'rules' => '', 'type' => 'id'),
				//array('field' => 'MedPersonal_aid', 'label' => 'Врач, выполнивший анализ', 'rules' => '', 'type' => 'id'),
				//array('field' => 'LpuSection_aid', 'label' => 'Отделение, выполнившее анализ', 'rules' => '', 'type' => 'id'),
				//array('field' => 'Lpu_aid', 'label' => 'МО, выполнившая анализ', 'rules' => '', 'type' => 'id'),
			),
			'updateEvnLabSample' => array(		
				array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DefectCauseType_id', 'label' => 'Идентификатор причины брака пробы', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnLabSample_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
				array('field' => 'EvnLabSample_AnalyzerDate', 'label' => 'Дата и время выполнения пробы на анализаторе', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'EvnLabSample_DelivDT', 'label' => 'Дата и время доставки пробы' , 'rules' => '', 'type' => 'datetime'),
				array('field' => 'EvnLabSample_StudyDT', 'label' => 'Дата и время выполнения ислледования', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'LabSampleStatus_id', 'label' => 'Идентификатор статуса пробы (справочное значение)', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_aid', 'label' => 'МО, выполнившая анализ', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_did', 'label' => 'МО, взявшая пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_aid', 'label' => 'Отделение, выполнившее анализ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_did', 'label' => 'Отделение, взявшее пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_aid', 'label' => 'Врач, выполнивший анализ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_did', 'label' => 'Врач, взявший пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_said', 'label' => 'Средний медперсонал, выполнивший анализ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_sdid', 'label' => 'Средний медперсонал, взявший пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_did', 'label' => 'Служба, в которой взята проба', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Служба, в которой выполняется заявка', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_sid', 'label' => 'Служба, забраковавшая пробу', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_aid', 'label' => 'Место работы врача, выполневшего анализ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_did', 'label' => 'Место работы врача, взявшего пробу', 'rules' => '', 'type' => 'id'),
			),
			'createUslugaTest' => array(
				array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы.', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО, в которой выполнено исследование', 'rules' => '', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Услуга (значение справочника dbo.UslugaComplex)', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaTest_ResultValue', 'label' => 'Результат выполнения исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Unit_id', 'label' => 'Единица измерения (справочник)', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaTest_ResultUnit', 'label' => 'Единица измерения (текстовое значение)', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_setDT', 'label' => 'Дата выполнения', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaTest_ResultLower (', 'label' => 'Нижнее референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultLowerCrit', 'label' => 'Нижнее критическое референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultUpper', 'label' => 'Верхнее референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultUpperCrit', 'label' => 'Верхнее критическое референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultApproved', 'label' => 'Статус теста («Null» - не проводился, «1» - выполнен, «2» - одобрен)', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaTest_deleted', 'label' => 'Признак удаления («1» - не удален, «2» - удален)', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaTest_delDT', 'label' => 'Дата удаления теста (обязательно для заполнения, если UslugaTest_deleted=2)', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaTest_ResultCancelReason', 'label' => 'Причина отмены результата теста', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultAppDate', 'label' => 'Дата обновления значения результата выполнения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача, выполневшего тест', 'rules' => '', 'type' => 'id'),
			),
			'updateUslugaTest' => array(
				array('field' => 'UslugaTest_id', 'label' => 'Идентификатор лабораторного исследования ', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО, в которой выполнено исследование', 'rules' => '', 'type' => 'id'),
				array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),				
				array('field' => 'UslugaTest_ResultValue', 'label' => 'Результат выполнения исследования', 'rules' => '', 'type' => 'string'),
				array('field' => 'Unit_id', 'label' => 'Единица измерения (справочник)', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaTest_ResultUnit', 'label' => 'Единица измерения (текстовое значение)', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_setDT', 'label' => 'Дата выполнения', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaTest_ResultLower', 'label' => 'Нижнее референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultLowerCrit', 'label' => 'Нижнее критическое референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultUpper', 'label' => 'Верхнее референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultUpperCrit', 'label' => 'Верхнее критическое референсное значение', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultApproved', 'label' => 'Статус теста («Null» - не проводился, «1» - выполнен, «2» - одобрен)', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaTest_deleted', 'label' => 'Признак удаления («1» - не удален, «2» - удален)', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaTest_delDT', 'label' => 'Дата удаления теста (обязательно для заполнения, если UslugaTest_deleted=2)', 'rules' => '', 'type' => 'date'),
				array('field' => 'UslugaTest_ResultCancelReason', 'label' => 'Причина отмены результата теста', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTest_ResultAppDate', 'label' => ' Дата обновления значения результата выполнения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача, выполневшего тест', 'rules' => '', 'type' => 'id'),
			),
			'mLoadEvnDirectionList' => array(
				array('field' => 'useCase', 'label' => 'Вариант использования', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор ТАП/КВС', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'onDate', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
				array('field' => 'parentClass', 'label' => 'Тип формы', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'formType', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'string'),
				array('field' => 'DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_did', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'EvnDirection_pid', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'LpuSectionProfile_did', 'label' => 'Идентификатор', 'rules' => 'trim', 'type' => 'id')
			)
		);
	}

	/**
	 *  Получение информации о направлении
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadEvnDirection');

		if (empty($data['Evn_pid']) && empty($data['Person_id']) && empty($data['EvnDirection_id']) && empty($data['EvnDirection_Num']) && empty($data['EvnDirection_setDate']) && empty($data['EvnDirection_beg']) && empty($data['EvnDirection_end'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->loadEvnDirectionForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение информации о стационарной бирке
	 */
	function TimeTableStacById_get() {
		$data = $this->ProcessInputData('getTimeTableStacById');

		$resp = $this->dbmodel->getTimeTableStacById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение информации о бирке
	 */
	function TimeTableMedServiceById_get() {
		$data = $this->ProcessInputData('getTimeTableMedServiceById');

		$resp = $this->dbmodel->getTimeTableMedServiceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение информации о бирке
	 */
	function TimeTableResourceById_get() {
		$data = $this->ProcessInputData('getTimeTableResourceById');

		$resp = $this->dbmodel->getTimeTableResourceById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание направления
	 */
	function index_post() {

		$data = $this->ProcessInputData('createEvnDirection');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['session'] = $sp['session'];

		if (!empty($data['DirType_id']) && in_array($data['DirType_id'], array(10, 11, 15, 20))) {
			if (empty($data['PrescriptionType_id'])) {
				$this->response(array(
					'error_msg' => 'Не указан параметр PrescriptionType_id',
					'error_code' => '6'
				));
			}

			switch ($data['DirType_id']) {
				case 10: // На исследование
					if (!in_array($data['PrescriptionType_id'], array(11, 12))) {
						$this->response(array(
							'error_msg' => 'Значение параметра PrescriptionType_id должно быть 11 или 12',
							'error_code' => '6'
						));
					}
					break;
				case 11: // В консультационный кабинет
					if (!in_array($data['PrescriptionType_id'], array(13))) {
						$this->response(array(
							'error_msg' => 'Значение параметра PrescriptionType_id должно быть 13',
							'error_code' => '6'
						));
					}
					break;
				case 15: // В процедурный кабинет
					if (!in_array($data['PrescriptionType_id'], array(6))) {
						$this->response(array(
							'error_msg' => 'Значение параметра PrescriptionType_id должно быть 6',
							'error_code' => '6'
						));
					}
					break;
				case 20: // В операционный блок
					if (!in_array($data['PrescriptionType_id'], array(7))) {
						$this->response(array(
							'error_msg' => 'Значение параметра PrescriptionType_id должно быть 7',
							'error_code' => '6'
						));
					}
					break;
			}

			if (!isset($data['EvnPrescr_IsCito'])) {
				$this->response(array(
					'error_msg' => 'Не указан параметр EvnPrescr_IsCito',
					'error_code' => '6'
				));
			}

			if (empty($data['UslugaComplex_id'])) {
				$this->response(array(
					'error_msg' => 'Не указан параметр UslugaComplex_id',
					'error_code' => '6'
				));
			}

			if (empty($data['MedService_id'])) {
				$this->response(array(
					'error_msg' => 'Не указан параметр MedService_id',
					'error_code' => '6'
				));
			}
		}

		$resp = $this->dbmodel->saveEvnDirectionFromAPI($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnDirection_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp_data = array(
			'EvnDirection_id' => $resp[0]['EvnDirection_id'],
			'Evn_id' => $resp[0]['EvnDirection_id']
		);

		if (!empty($resp[0]['EvnQueue_id'])) {
			$resp_data['EvnQueue_id'] = $resp[0]['EvnQueue_id'];
		}

		if (!empty($resp[0]['EvnPrescr_id'])) {
			$resp_data['EvnPrescr_id'] = $resp[0]['EvnPrescr_id'];
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp_data
		));
	}

	/**
	 * Отмена направления
	 */
	function EvnDirectionCancel_put() {
		$data = $this->ProcessInputData('cancelEvnDirection', false, true);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['session'] = $sp['session'];
		
		$data['EvnComment_Comment'] = (!empty($data['EvnStatusHistory_Cause'])) ? $data['EvnStatusHistory_Cause'] : null;

		$resp = $this->dbmodel->cancelEvnDirectionFromAPI($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		} else if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение списка направлений
	 */
	function EvnDirectionList_get() {
		$data = $this->ProcessInputData('loadEvnDirectionList');

		if (empty($data['Evn_pid']) && empty($data['Person_id'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->loadEvnDirectionListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Загрузка списка направлений для мобильного приложения
	 */
	function mloadEvnDirectionPanel_get() {
		$data = $this->ProcessInputData('mloadEvnDirectionPanel');

		$resp = $this->dbmodel->loadEvnDirectionPanel($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Отмена направления - мобильная
	 */
	function mCancelEvnDirection_post() {
		$this->EvnDirectionCancel_put();
	}

	/**
	 * Создание направления - мобильная
	 */
	function mSaveEvnDirection_post() {
		if(empty($this->_args['Evn_pid'])){
			$this->response(array(
				'error_msg' => 'Не передан обязательный параметр Evn_pid',
				'error_code' => '6'
			));
		}
		else{
			$this->_args['Evn_pid'] = (int)$this->_args['Evn_pid'];
		}

		// дружественное преобразование, потому что тип api_flag
		if (isset($this->_args['EvnPrescr_IsCito']) && is_int($this->_args['EvnPrescr_IsCito'])) {
			if ($this->_args['EvnPrescr_IsCito'] === 1) $this->_args['EvnPrescr_IsCito'] = "0";
			if ($this->_args['EvnPrescr_IsCito'] === 2) $this->_args['EvnPrescr_IsCito'] = "1";
		}

		$this->_args['fromMobile'] = true;
		$this->index_post();
	}

	/**
	 * Получение данных по направлению - мобильная
	 * Обязательный параметр EvnDirection_id
	 */
	function mloadEvnDirectionEditForm_get(){
		$data = $this->ProcessInputData('loadEvnDirectionEditForm', null, true);

		if(empty($data['EvnDirection_id'])){
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->loadEvnDirectionEditForm($data);
		if(!is_array($resp)){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение информации об услуге по идентификатору направления
	 * Обязательный параметр EvnDirection_id
	 */
	function EvnDirectionUslugaComplex_get()
	{
		$data = $this->ProcessInputData('EvnDirectionUslugaComplex', null, false);

		if (empty($data['EvnDirection_id'])) {
			$this->response(array(
				'error_msg' => 'Не переданы входящие параметры',
				'error_code' => '6'
			));
		}

		$result = $this->dbmodel->EvnDirectionUslugaComplex($data);
		if(!is_array($result)){
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array(
			'error_code' => 0,
			'data' => $result,
		));
	}
	
	/**
	 * добавление результатов взятия пробы
	 */
	function EvnLabSample_post() {
		$data = $this->ProcessInputData('createEvnLabSample', null, true);

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->POST('EvnLabSample/createFromAPI', $data);
			if (!$this->isSuccessful($response)) {
				$this->response(array(
					'error_code' => $response['error_code'],
					'ErrorMsg' => $response['Error_Msg']
				));
			} else {
				$this->response([
					'error_code' => 0,
					'EvnLabSample_id' => $response['EvnLabSample_id']
				]);
			}
		} else {
			if(empty($data['MedPersonal_did']) && !empty($data['MedStaffFact_did'])){
				$this->load->model('MedPersonal_model', 'mpmodel');
				$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_did']));
				if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_did'] = $medStaffFact[0]['MedPersonal_id'];
			}else if(empty($data['MedPersonal_did']) && !empty($data['session']['medpersonal_id'])){
				$data['MedPersonal_did'] = $data['session']['medpersonal_id'];
			}
			if(empty($data['MedPersonal_did'])){
				$this->response(array(
					'error_code' => 5,
					'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
				));
			}

			$data['UslugaComplexList'] = array_map('intval', explode(',', $data['UslugaComplexList']));
			if(count($data['UslugaComplexList']) > 0){
				$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
				$result = $this->EvnLabSample_model->createEvnLabSampleAPI($data);
				if(!empty($result[0]['EvnLabSample_id'])){
					$this->response(array(
						'error_code' => 0,
						'EvnLabSample_id' => $result[0]['EvnLabSample_id']
					));
				}else{
					$this->response(array(
						'error_code' => 6,
						'ErrorMsg' => ($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка при добавлении результатов взятия пробы'
					));
				}
			}else{
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
	}
	
	/**
	 * добавление результатов взятия пробы
	 */
	function EvnLabSample_put() {
		$data = $this->ProcessInputData('updateEvnLabSample', null, true);
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$result = $this->lis->PUT('EvnLabSample/updateFromAPI', $data);

			if (!$this->isSuccessful($result)) {
				$this->response(array(
					'error_code' => $result['error_code'],
					'ErrorMsg' => $result['Error_Msg']
				));
			} else {
				if (empty($result['error_code'])) {
					$this->response([
						'error_code' => 0
					]);
				} else {
					$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
		} else {
			$this->load->model('MedPersonal_model', 'mpmodel');
			if(empty($data['MedPersonal_did']) && !empty($data['MedStaffFact_did'])){
				$this->load->model('MedPersonal_model', 'mpmodel');
				$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_did']));
				if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) {
					$data['MedPersonal_did'] = $medStaffFact[0]['MedPersonal_id'];
				}
			}else if(empty($data['MedPersonal_did']) && !empty($data['session']['medpersonal_id'])){
				$data['MedPersonal_did'] = $data['session']['medpersonal_id'];
			}
			if(empty($data['MedPersonal_did'])){
				$this->response(array(
					'error_code' => 5,
					'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
				));
			}
			if(empty($data['MedPersonal_aid']) && !empty($data['MedStaffFact_aid'])){
				$this->load->model('MedPersonal_model', 'mpmodel');
				$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_aid']));
				if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) {
					$data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
				}
			}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])){
				$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
			}
			if(empty($data['MedPersonal_aid'])){
				$this->response(array(
					'error_code' => 5,
					'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
				));
			}

			$result = $this->EvnLabSample_model->updateEvnLabSampleAPI($data);

			if(!empty($result['EvnLabSample_id'])){
				$this->response(array(
					'error_code' => 0
				));
			}else{
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
	}
	
	/**
	 * добавление результатов взятия пробы
	 * PS: считается, что проба взята в промеде, ее надо найти по параметрам и подставить значения. По сути это обновление записи, а не создание.
	 */
	function UslugaTest_post() {
		$data = $this->ProcessInputData('createUslugaTest', null, true);
		if(!empty($data['UslugaTest_deleted']) && $data['UslugaTest_deleted'] == 2 && empty($data['UslugaTest_delDT'])){
			$this->response(array(
				'error_msg' => 'Не передан обязательный параметр UslugaTest_delDT',
				'error_code' => '6'
			));
		}
		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$resp = $this->lis->POST('EvnLabSample/createUslugaTest', $data);
			if (!$this->isSuccessful($resp)) {
				$this->response(array(
					'error_code' => $resp['error_code'],
					'ErrorMsg' => $resp['Error_Msg']
				));
			} else {
				if (empty($result['error_code'])) {
					$this->response(array(
						'UslugaTest_id' => $resp['UslugaTest_id']
					));
				} else {
					$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
		} else {
			if(!empty($data['MedStaffFact_id'])){
				$this->load->model('MedPersonal_model', 'mpmodel');
				$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_id']));
				if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
			}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])){
				$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
			}
			if(empty($data['MedPersonal_aid'])){
				//MedPersonal_aid	врач выполнивший анализ
				$this->response(array(
					'error_code' => 5,
					'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
				));
			}

			$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
			$resp = $this->EvnLabSample_model->createUslugaTestAPI($data);
			if (!$resp) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$this->response(array(
				'UslugaTest_id' => $resp
			));
		}
	}
	
	/**
	 * добавление результатов взятия пробы
	 */
	function UslugaTest_put() {
		$data = $this->ProcessInputData('updateUslugaTest', null, true);
		
		if(!empty($data['UslugaTest_deleted']) && $data['UslugaTest_deleted'] == 2 && empty($data['UslugaTest_delDT'])){
			$this->response(array(
				'error_msg' => 'Не передан обязательный параметр UslugaTest_delDT',
				'error_code' => '6'
			));
		}
		if($this->usePostgreLis) {
			$this->load->swapi('lis');
			$resp = $this->lis->PUT('EvnLabSample/updateUslugaTest', $data);
			if (!$this->isSuccessful($resp)) {
				$this->response(array(
					'error_code' => $resp['error_code'],
					'ErrorMsg' => $resp['Error_Msg']
				));
			} else {
				if (empty($result['error_code'])) {
					$this->response(array(
						'error_code' => 0
					));
				} else {
					$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
		} else {
			if(!empty($data['MedStaffFact_id'])){
				$this->load->model('MedPersonal_model', 'mpmodel');
				$medStaffFact = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id' => $data['MedStaffFact_id']));
				if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
			}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])){
				$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
			}
			if(empty($data['MedPersonal_aid'])){
				//MedPersonal_aid	врач выполнивший анализ
				$this->response(array(
					'error_code' => 5,
					'ErrorMsg' => 'Не указан врач, обработка пакета невозможна'
				));
			}

			$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
			$resp = $this->EvnLabSample_model->updateUslugaTestAPI($data);
			if (!$resp) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}

			$this->response(array(
				'error_code' => 0
			));
		}
	}

	/**
	 * @OA\get(
	path="/api/EvnDirection/mLoadEvnDirectionList",
	tags={"EvnDirection"},
	summary="Загрузка списка направлений из формы выбора направления",

	@OA\Parameter(
	name="useCase",
	in="query",
	description="Вариант использования
	 *     load_evn_direction_all_info_panel
	 *     addEvnVizitPLStom
	 *     addEvnVizitPL
	 *     load_data_for_auto_create_tap
	 *     load_data_for_create_tap_consult -
	 *     choose_for_evnpl_stream_input - выбираем для ТАП, но при операторском вводе (не из АРМа)
	 *     choose_for_evnplstom_stream_input - выбираем для ТАП, но при операторском вводе (не из АРМа)
	 *     choose_for_evnpl - выбираем для ТАП из АРМа врача поликлиники
	 *     choose_for_evnplstom - выбираем для ТАП из АРМа стоматолога
	 *     create_evnplstom_without_recording - тоже самое, только выбираем при приеме без записи из формы АРМа врача
	 *     create_evnpl_without_recording - тоже самое, только выбираем при приеме без записи из формы АРМа врача
	 *     choose_for_evnps_stream_input - выбираем для КВС из форма редактирования КВС, но при операторском вводе (не из АРМа)
	 *     self_treatment-тоже самое, только выбираем при приеме без записи из рабочего места врача приемного отделения
	 *     choose_for_evnps-выбираем для КВС из форма редактирования КВС, открытой из АРМа врача стационара
	 *     create_evnps_from_workplacestac - тоже самое, только выбираем при приеме из формы АРМа врача стационара
	 *     check_exists_dir_stac_in_evn
	 *     choose_for_evnvizitpl_link",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="TimetableGraf_id",
	in="query",
	description="Идентификатор бирки",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Evn_id",
	in="query",
	description="Идентификатор ТАП/КВС",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_id",
	in="query",
	description="Идентификатор направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="onDate",
	in="query",
	description="Дата",
	required=false,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="parentClass",
	in="query",
	description="Тип формы",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор пациента",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Идентификатор рабочего места",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="formType",
	in="query",
	description="Идентификатор отделения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="DirType_id",
	in="query",
	description="Тип направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Lpu_did",
	in="query",
	description="Идентификатор ЛПУ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_pid",
	in="query",
	description="Родительский идентификатор направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSectionProfile_did",
	in="query",
	description="Идентификатор отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="MedicalCareFormType_id",
	description="Форма помощи",
	type="integer",

	)
	,

	@OA\Property(
	property="MedPersonal_id",
	description="Кэш врачей, идентификатор медицинского работника",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_id",
	description="Справочник диагнозов, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_IsAuto",
	description="Выписка направлений, автоматически созданное направление",
	type="boolean",

	)
	,
	@OA\Property(
	property="EvnDirection_IsReceive",
	description="Выписка направлений, признак создания направления принимающей стороной",
	type="boolean",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="Org_id",
	description="Cправочник организаций, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_id",
	description="Комплексные услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Комплексные услуги, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_Name",
	description="Наименование ЛПУ",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSectionProfile_Name",
	description="Профиль отделения в ЛПУ, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Timetable_begTime",
	description="Дата начала бирки",
	type="string",

	)
	,
	@OA\Property(
	property="DirType_Name",
	description="Справочник назначений направления, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_dNick",
	description="Ник ЛПУ куда направили пациента",
	type="string",

	)
	,
	@OA\Property(
	property="enabled",
	description="Пока хз что это",
	type="string",

	)
	,
	@OA\Property(
	property="LpuBuildingType_id",
	description="Тип здания ЛПУ, идентификатор",
	type="integer",

	),
	@OA\Property(
	property="EvnDirection_id",
	description="Выписка направлений, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnDirection_setDate",
	description="Дата направления",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_Num",
	description="Выписка направлений, номер направления",
	type="string",

	)
	,
	@OA\Property(
	property="LpuSection_id",
	description="Справочник ЛПУ: отделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="MedStaffFact_id",
	description="Кэш мест работы, идентификатор места работы",
	type="integer",

	)
	,
	@OA\Property(
	property="Diag_Name",
	description="Справочник диагнозов, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="DirType_id",
	description="Справочник назначений направления, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Lpu_sid",
	description="Направившее ЛПУ",
	type="string",

	)
	,

	@OA\Property(
	property="EvnStatus_id",
	description="статус события, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableGraf_id",
	description="Идентификатор бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableStac_id",
	description="Идентификатор бирки для стационара",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableMedService_id",
	description="Идентификатор бирки для службы",
	type="integer",

	)
	,
	@OA\Property(
	property="TimetableResource_id",
	description="Идентификатор бирки для ресурса",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnQueue_id",
	description="Постановка в очередь, Идентификатор постановки в очередь",
	type="integer",

	)
	,
	@OA\Property(
	property="EmergencyData_CallNum",
	description="Данные о вызове скорой помощи, номер вызова",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDirection_IsVMP",
	description="хз, что это. Там условие case when ed.DirType_id = 19 then 'true' else 'false' end as EvnDirection_IsVMP а у нас нет DirType=19 в тестовой базе",
	type="boolean",

	)
	,
	@OA\Property(
	property="MedStaffFact_did",
	description="Врач, к которому записываемся",
	type="string",

	)
	,
	@OA\Property(
	property="MSF_Person_Fin",
	description="Фио врача",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mLoadEvnDirectionList_get() {
		$data = $this->ProcessInputData('mLoadEvnDirectionList', false, true);
		if (!empty($data['useCase'])) {
			$this->load->model('EvnDirectionAll_model');
			$response = $this->EvnDirectionAll_model->mLoadEvnDirectionList($data);
			$this->response(array(
				'error_code' => 0,
				'data' => $response,
			));
		}
		$response = $this->dbmodel->mLoadEvnDirectionList($data);
		$this->response(array(
			'error_code' => 0,
			'data' => $response,
		));
	}
}
