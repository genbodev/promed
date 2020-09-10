<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLStom - контроллер для работы с талонами по стоматологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package				Polka
 * @copyright			Copyright (c) 2009-2011 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage1981@gmail.com)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				13.11.2011
 */

/**
 * @property EvnDiagPLStom_model $dbmodel
 */
class EvnDiagPLStom extends swController {
	public $inputRules = array(
		'loadEvnDiagPLStomPanel' => array(
			array(
				'field' => 'EvnDiagPLStom_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'updateMesId' => array(
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Идентификатор заболевания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Mes_id',
				'label' => 'Идентификатор КСГ',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteEvnDiagPLStom' => array(
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDiagPLStomEditForm' => array(
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDiagPLStomGrid' => array(
			array(
				'field' => 'pid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'rid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnDiagPLStomCombo' => array(
			array(
				'field' => 'EvnDiagPLStom_rid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnDiagPLStom' => array(
			array(
				'field' => 'ignoreCheckKSGPeriod',
				'label' => 'Признак игнорирования проверки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDiagPLStom_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDiagPLStom_setDate',
				'label' => 'Дата начала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDiagPLStom_disDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'date'
			),
			array('field' => 'Mes_id', 'label' => 'КСГ', 'rules' => '', 'type' => 'id'),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array('field' => 'Tooth_Code', 'label' => 'Код зуба', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tooth_id', 'label' => 'Зуб', 'rules' => '', 'type' => 'id'),
			array('field' => 'ToothSurfaceType_id_list', 'label' => 'Поверхность зуба', 'rules' => '', 'type' => 'string'),
			array('field' => 'isAutoCreate', 'label' => 'Флаг автоматического сохранения', 'rules' => 'trim', 'type' => 'int', 'default' => 0 /* нет */ ),
			array('field' => 'ignoreEmptyKsg', 'label' => 'Флаг игнорирования пустой КСГ', 'rules' => 'trim', 'type' => 'int', 'default' => 0 /* нет */ ),
			array('field' => 'ignoreUetSumInNonMorbusCheck', 'label' => 'Флаг игнорирования проверки превышения суммы УЕТ в услугах максимального КСГ по указанному диагнозу', 'rules' => 'trim', 'type' => 'int', 'default' => 0 /* нет */ ),
			array('field' => 'ignoreMorbusOnkoDrugCheck', 'label' => 'Флаг игнорирования проверки препаратов в онко заболевании', 'rules' => 'trim', 'type' => 'int', 'default' => 0 /* нет */ ),
			array('field' => 'KSGlist', 'label' => 'Список доступных КСГ', 'rules' => 'trim', 'type' => 'json_array', 'default' => 0 /* нет */ ),
			array('field' => 'EvnDiagPLStom_IsClosed', 'label' => 'Заболевание закрыто', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'EvnDiagPLStom_IsZNO', 'label' => 'Подозрение на ЗНО', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'EvnDiagPLStom_IsZNORemove', 'label' => 'Признак снятия подозрения на ЗНО', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'EvnDiagPLStom_BiopsyDate', 'label' => 'Дата взятия биопсии', 'rules' => '', 'type' => 'date'),
			array('field' => 'Diag_spid', 'label' => 'Подозрение на диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'PainIntensity_id', 'label' => 'Интенсивность боли', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_KPU', 'label' => 'Индекс КПУ', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnDiagPLStom_CarriesTeethCount', 'label' => 'Количество нелеченых незапломбированных кариозных поражений зубов', 'rules' => '', 'type' => 'int'),
			array('field' => 'BlackClass_id', 'label' => 'Класс по Блэку', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPLStom_HalfTooth', 'label' => 'Разрушение коронки зуба более 50%', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreCheckTNM', 'label' => 'Флаг игнорирования проверки соответствия диагноза и TNM', 'rules' => 'trim', 'type' => 'int', 'default' => 0 /* нет */ ),
			array('field' => 'ignoreCheckMorbusOnko', 'label' => 'Признак игнорирования проверки перед удалением специфики', 'rules' => 'trim', 'type' => 'int', 'default' => 0 ),
			array('field' => 'CurMedStaffFact_id', 'label' => 'Текущее место работы врача', 'rules' => '', 'type' => 'id'),//yl:
		)
	);

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDiagPLStom_model', 'dbmodel');
	}


	/**
	* Сохранение КСГ и апдейт услуг
	*/
	function updateMesId() {
        $data = $this->ProcessInputData('updateMesId', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->updateMesId($data);
        $this->ProcessModelSave($response,true,'При сохранении КСГ возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Удаление диагноза
	*  Входящие данные: $_POST['EvnDiagPLStom_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения пациентом поликлиники (стоматология)
	*/
	function deleteEvnDiagPLStom() {
		$data = array();

        $data = $this->ProcessInputData('deleteEvnDiagPLStom', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->deleteEvnDiagPLStom($data);
        $this->ProcessModelSave($response,true,'При удалении сопутствующего диагноза возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение данных для формы редактирования стоматологического диагноза
	*  Входящие данные: $_POST['EvnDiagPLStom_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования стоматологического диагноза
	*/
	function loadEvnDiagPLStomEditForm() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomEditForm', true);
        if($data)
		{
			$response = $this->dbmodel->loadEvnDiagPLStomEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
            $this->ReturnError('Ошибка при получении данных');
			return false;
		}
	}


	/**
	*  Получение списка диагнозов
	*  Входящие данные: $_POST['EvnDiagPLStom_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения пациентом поликлиники (стоматология)
	*/
	function loadEvnDiagPLStomGrid() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDiagPLStomGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка диагнозов для комбо
	*  Входящие данные: $_POST['EvnDiagPLStom_rid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования стомат. услуги
	*/
	function loadEvnDiagPLStomCombo() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDiagPLStomCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Сохранение стоматологического диагноза
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования стоматологического диагноза
	*/
	function saveEvnDiagPLStom() {
		$response = array();
		$data = $this->ProcessInputData('saveEvnDiagPLStom', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnDiagPLStom($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка заболеваний для панели направлений в ЭМК
	 */
	function loadEvnDiagPLStomPanel() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomPanel', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDiagPLStomPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}
