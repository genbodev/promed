<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*c
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require_once(APPPATH.'controllers/Registry.php');

class Perm_Registry extends Registry {
	var $scheme = "dbo";
	var $model_name = "Registry_model";
	var $upload_path = 'RgistryFields/';
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['importRegistryFromXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['exportUnionRegistryToXmlCheckExist'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['sendUnionRegistryToTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['exportUnionRegistryToXml'] = array(
			array(
				'field' => 'UseDebug',
				'label' => 'Флаг вывода отладочной информации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideControlFlkStatus',
				'label' => 'Флаг пропуска контроля на статус Проведен контроль ФЛК',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Флаг использования существующего или экспорта нового XML',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyLink',
				'label' => 'Флаг вывода только ссылки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorType_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorClass_id',
				'label' => 'Вид ошибки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryErrorTFOMS_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryErrorBDZ'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryErrorBDZType_id',
				'label' => 'Тип ошибки БДЗ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['saveUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			),
			array(
				'field' => 'PayType_SysNick',
				'label' => 'Вид оплаты',
				'rules' => '',
				'default' => null,
				'type' => 'string'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryGrid'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsNew',
				'label' => 'Признак новых реестров',
				'rules' => '',
				'default' => null,
				'type' => 'id'
			),
			array(
				'field' => 'PayType_SysNick',
				'label' => 'Вид оплаты',
				'rules' => '',
				'default' => null,
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['getUnionRegistryNumber'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryEditForm'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['deleteUnionRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['setUnionRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);


		$this->inputRules['loadUnionRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NumCard',
				'label' => 'Номер документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Полис',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			)
		);

		$archive_database_enable = $this->config->item('archive_database_enable');
		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$this->db = null;
			$this->load->database('archive', false);
		}
	}

	/**
	 * Изменение статуса счета-реестра
	 * Входящие данные: ID рееестра и значение статуса
	 * На выходе: JSON-строка
	 * Используется: форма просмотра реестра (счета)
	 */
	function setUnionRegistryStatus()
	{
		$data = $this->ProcessInputData('setUnionRegistryStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setUnionRegistryStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Импорт ошибок МЭК от СМО
	 */
	function importRegistryFromXml()
	{
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importRegistryFromXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при загрузке протокола МЭК')->ReturnData();
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXmlCheckExist()
	{
		$data = $this->ProcessInputData('exportRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);
		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0 && (
				$res[0]['RegistryStatus_id'] == 4 || (!in_array($res[0]['RegistryCheckStatus_Code'],array(-1,2,5,6,10)))
			)) // если уже выгружен реестр и оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath'])>0)
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			} else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		} else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToXmlCheckExist()
	{
		$data = $this->ProcessInputData('exportUnionRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);
		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0 && (
				$res[0]['RegistryStatus_id'] == 4 || (in_array($res[0]['RegistryCheckStatus_Code'],array(0,1,3,8,10)))
			)) // если уже выгружен реестр и оплаченный или с определенными статусами
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0)
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			} else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		} else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Отправка в ТФОМС
	 */
	function sendUnionRegistryToTFOMS()
	{
		$data = $this->ProcessInputData('sendUnionRegistryToTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendUnionRegistryToTFOMS($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отправке реестра в ТФОМС')->ReturnData();
	}

	/**
	 * Обновление Registry_EvnNum (добавление Evn_rid)
	 */
	function addRidInRegistryEvnNum() {
		if (!isSuperAdmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return;
		}

		$this->dbmodel->addRidInRegistryEvnNum();
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXml() {
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$before2015 = false;

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск');

		// Определяем надо ли при успешном формировании проставлять статус и соответсвенно не выводить ссылки
		if (!isset($data['send']))
			$data['send'] = 0;

		$type = 0;
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр

		$this->textlog->add('exportRegistryToXml: GetRegistryXmlExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		if (!empty($res[0]['Registry_endDate']) && strtotime($res[0]['Registry_endDate']) < strtotime('2015-01-01')) {
			$before2015 = true;
		}
		if (!empty($res[0]['RegistryCheckStatus_id'])) {
			$data['RegistryCheckStatus_id'] = $res[0]['RegistryCheckStatus_id'];
		}

		// Нельзя отправлять в ТФОМС реестры со статусом "В работе"
		// @task https://redmine.swan.perm.ru/issues/98428
		if ( $res[0]['RegistryStatus_id'] == 3 && $data['send'] == 1 ) {
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: Нельзя отправлять в ТФОМС реестры со статусом "В работе".');
			$this->ReturnError('Нельзя отправлять в ТФОМС реестры со статусом "В работе"');
			return false;
		}

		// Запрет отправки в ТФОМС реестра "Проведён контроль ФЛК"
		if (!isSuperAdmin() && $data['send'] == 1 && $res[0]['RegistryCheckStatus_id'] === '5') {
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС.');
			$this->ReturnError('При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС');
			return false;
		}

		// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
		if (!empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2) {
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			$this->ReturnError('Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			return false;
		}

		// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
		if (!empty($res[0]['Registry_SumDifference'])) {
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: Неверная сумма по счёту и реестрам.');
			// добавляем ошибку
			// $data['RegistryErrorType_Code'] = 3;
			// $res = $this->dbmodel->addRegistryErrorCom($data);
			$this->ReturnError('Экспорт невозможен. Неверная сумма по счёту и реестрам.', '12');
			return false;
		}

		// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
		if (empty($res[0]['RegistryData_Count'])) {
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: Нет записей в реестре.');
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', '13');
			return false;
		}

		$this->textlog->add('exportRegistryToXml: Получили путь из БД:'.$res[0]['Registry_xmlExportPath']);
		if ($data['send'] == 1) {
			if (($res[0]['RegistryCheckStatus_Code'] == 4) && (empty($data['OverrideControlFlkStatus']))) {
				$this->textlog->add('exportRegistryToXml: Выход с вопросом: Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?');
				$this->ReturnError('Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?', '10');
				return false;
			}

			$data['OverrideExportOneMoreOrUseExist'] = 1;
		}

		if ($res[0]['Registry_xmlExportPath'] == '1')
		{
			$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
		{
			$this->textlog->add('exportRegistryToXml: Реестр уже выгружен');

			if (empty($data['OverrideExportOneMoreOrUseExist'])) {
				$this->textlog->add('exportRegistryToXml: Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', '11');
				return false;
			} elseif ($data['OverrideExportOneMoreOrUseExist'] == 1) {
				// Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено
				if ($data['send']==1) {
					$this->textlog->add('exportRegistryToXml: Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено ');
					if (($res[0]['RegistryCheckStatus_id']>0) && (!in_array($res[0]['RegistryCheckStatus_Code'],array(4,6)))) {
						$this->textlog->add('exportRegistryToXml: Выход с сообщением: Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра не возможна. ');
						$this->ReturnError('Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра не возможна.');
						return false;
					} else {
						// Хотим отправить уже сформированный реестр
						$data['RegistryCheckStatus_id'] = 1;
						$data['Status'] = $res[0]['Registry_xmlExportPath'];
						$this->dbmodel->SetXmlExportStatus($data);
						$this->textlog->add('exportRegistryToXml: SetXmlExportStatus: Отправка сформированного реестра. Путь: '.$res[0]['Registry_xmlExportPath']);
						$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id']));
						return true;
					}
				}
				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				}
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';
				if (empty($data['onlyLink'])) {
					$usePrevXml = 'usePrevXml: true, ';
				}
				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('exportRegistryToXml: Выход с передачей ссылкой: '.$link);
				return true;
			} else {
				// https://redmine.swan.perm.ru/issues/48575
				if (
					(!empty($res[0]['RegistryCheckStatus_Code']) || $res[0]['RegistryCheckStatus_Code'] == 0)
					&& !in_array($res[0]['RegistryCheckStatus_Code'],array(-1,2,5,6,10))
				) {
					$this->textlog->add('exportRegistryToXml: Выход с сообщением: Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!');
					$this->ReturnError('Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!');
					return false;
				}

				$type = $res[0]['RegistryType_id'];
			}
		}
		else
		{
			$type = $res[0]['RegistryType_id'];
		}

		$this->textlog->add('exportRegistryToXml: GetRegistryXmlExport: Проверка закончена');

		$data['PayType_SysNick'] = null;

		// Если вернули тип оплаты реестра, то будем его использовать
		if ( isset($res[0]['PayType_SysNick']) ) {
			$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		}

		$this->textlog->add('exportRegistryToXml: Тип оплаты реестра: ' . $data['PayType_SysNick']);
		$this->textlog->add('exportRegistryToXml: Тип реестра: '.$type);

		// Формирование XML в зависимости от типа.
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$this->load->model('RegistryLog_model');

			// пишем в лог начало
			$RegistryLogSaveResp = $this->RegistryLog_model->saveRegistryLog(array(
				'Registry_id' => $data['Registry_id'],
				'RegistryLog_begDate' => '@curDate',
				'RegistryActionType_id' => 3, // Экспорт
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($RegistryLogSaveResp[0]['Error_Msg'])) {
				$this->ReturnError($RegistryLogSaveResp[0]['Error_Msg']);
				return false;
			}

			if (empty($RegistryLogSaveResp[0]['RegistryLog_id'])) {
				$this->ReturnError('Ошибка записи в RegistryLog');
				return false;
			}

			$RegistryLog_id = $RegistryLogSaveResp[0]['RegistryLog_id'];
			$RegistryLog_begDate = $RegistryLogSaveResp[0]['curDate'];

			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('exportRegistryToXml: SetXmlExportStatus: Установили статус реестра в 1');
			$data['before2015'] = $before2015;

			if (!empty($res[0]['Registry_IsRepeated'])) {
				$data['Registry_IsRepeated'] = $res[0]['Registry_IsRepeated'];
			}

			$oldRegistry_EvnNums = $this->dbmodel->getOldRegistryEvnNums($data);

			if (!empty($oldRegistry_EvnNums['Error_Msg'])) {
				$this->ReturnError($oldRegistry_EvnNums['Error_Msg']);
				return false;
			}
			
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsingCommonCustom($type, $data);

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_".time()."_".$data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY.$out_dir );

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
				$Recipient = 'C159';

				$PayTypeModificator = "B";

				switch ( $type ) {
					case 1: // stac
						$RegistryTypePrefix = "S";
						break;

					case 2: //polka
					case 16: //stom
						$RegistryTypePrefix = "P";
						break;

					case 6: //smp
						$RegistryTypePrefix = "C";
						break;

					case 14: //htm
						$RegistryTypePrefix = "V";
						break;

					case 15: //par
						$RegistryTypePrefix = "PU";
						break;

					default:
						return false;
						break;
				}

				$rname = $PayTypeModificator . '_' . $RegistryTypePrefix . "_HM" . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1";
				$pname = $PayTypeModificator . '_' . $RegistryTypePrefix . "_LM" . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1";
			}
			else {
				$KatNaselModificator = ($data['KatNasel_id'] == 2 && !in_array($type, array(4, 5, 9))? "R" : "");
				$Recipient = "T" . $data['session']['region']['number'];

				switch ( $data['PayType_SysNick'] ) {
					case 'ovd':
						if ( 6 != $type ) {
							$PayTypeModificator = "O_";
						}
						break;

					default:
						$PayTypeModificator = "";
						break;
				}

				switch ( $type ) {
					case 1: // stac
						$RegistryTypePrefix = "S";
						break;

					case 2: //polka
					case 16: //stom
						$RegistryTypePrefix = "P";
						break;

					case 4: //dd
						$RegistryTypePrefix = "D";
						break;

					case 5: //orp
						$RegistryTypePrefix = "U";
						break;

					case 6: //smp
						$RegistryTypePrefix = "C";
						break;

					case 7: //dd
						$RegistryTypePrefix = "D";
						break;

					case 9: //orp
						$RegistryTypePrefix = "U";
						break;

					case 11: //teen inspection
						$RegistryTypePrefix = "F";
						break;

					case 12: //teen inspection
						$RegistryTypePrefix = "G";
						break;

					case 14: //htm
						$RegistryTypePrefix = "T";
						break;

					case 15: //par
						$RegistryTypePrefix = "PU";
						break;

					default:
						return false;
						break;
				}

				if (in_array($data['PayType_SysNick'], array('mbudtrans', 'mbudtrans_mbud'))) {

					$Recipient = 'T59';

					if ($data['PayType_SysNick'] == 'mbudtrans') {
						$params = 'NZ';
					} else {
						$params = 'SB';
					}

					$rname = $RegistryTypePrefix . '_HM' . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1_" . $params;
					$pname = $RegistryTypePrefix . '_LM' . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1_" . $params;

				} else{
					$rname = $PayTypeModificator . $RegistryTypePrefix . $KatNaselModificator . "_HM" . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1";
					$pname = $PayTypeModificator . $RegistryTypePrefix . $KatNaselModificator . "_LM" . $SCHET[0]['CODE_MO'] . $Recipient . "_" . date('ym') . "1";
				}
			}

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
			// временный файл-тело реестра
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign."_tmp.xml";
			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_pers_data_sign.".xml";

			$templ_header = "registry_pl_header";
			$templ_footer = "registry_pl_footer";
			$templ_person_header = "registry_person_header";
			$templ_person_footer = "registry_person_footer";

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
				$templ_header .= '_bud';
				$templ_person_header .= '_bud';
			}
			else if ( $before2015 ) {
				$templ_header .= '_2014';
				$templ_person_header .= '_2014';
			}

			$this->load->library('parser');

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$ZGLV = array();
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;
			$ZGLV[0]['VERSION'] = (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'mbudtrans','mbudtrans_mbud')) ? '1.0' : '3.1');

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_person_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			$Registry_EvnNum = array();
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$loadRegistryDataForXmlByFunc = 'loadRegistryDataForXmlUsingCommonCustom';
				$getSDZ = 'getSDZ';
			} else {
				$loadRegistryDataForXmlByFunc = 'loadRegistryDataForXmlByFunc2018';
				$getSDZ = 'getSDZ2018';
			}

			$this->dbmodel->$loadRegistryDataForXmlByFunc(false, $type, $data, $Registry_EvnNum, $oldRegistry_EvnNums, $file_re_data_name_tmp, $file_re_pers_data_name);
			$SD_Z = $this->dbmodel->$getSDZ();

			if ( $SD_Z === false ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: Выход с ошибкой дедлока');
				$this->ReturnError($this->error_deadlock);
				return false;
			}

			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsingCommon: Выбрали данные');

			$SCHET[0]['SD_Z'] = $SD_Z; // кол-во случаев
			$SCHET[0]['VERSION'] = in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud')) ? '1.0' : '3.1';

			// Заголовок файла со случаями
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_header, $SCHET[0], true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

				if ( $fh === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('exportRegistryToXml: Ошибка при открытии файла');
					$this->ReturnError('Ошибка при открытии файла');
					return false;
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_re_data_name_tmp);
			}

			// Конец
			$xml = $this->parser->parse('export_xml/' . $templ_footer, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			// Конец для файла person
			$xml_pers = $this->parser->parse('export_xml/' . $templ_person_footer, array(), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			$this->textlog->add('exportRegistryToXml: Создали XML-файлы: ('.$file_re_data_name.' и '.$file_re_pers_data_name.')');
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP ' . $file_zip_name);
			
			$PersonData_registryValidate = true;
			$EvnData_registryValidate = true;
			if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				// если включена проверка ФЛК в параметрах системы
				// получим xsd шаблон для проверки
				$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);
				if(count($settingsFLK) > 0){
					//если запись найдена
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;

					//Проверка со случаями
					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$this->upload_path.$dirTpl."/".$tplEvnDataXSD;
						//Файл с ошибками
						$validateEvnData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';
						if(file_exists($fileEvnDataXSD)) {
							$EvnData_registryValidate = $this->dbmodel->Reconciliation($file_re_data_name, $fileEvnDataXSD, 'file', $validateEvnData_err_file);
						}
					}
					//Проверка с персональными данными
					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$filePersonDataXSD = IMPORTPATH_ROOT.$this->upload_path.$dirTpl."/".$tplPersonDataXSD;
						//Файл с ошибками
						$validatePersonData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_PersonData_".$dirTpl.'.html';
						if(file_exists($filePersonDataXSD)) {
							$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
						}
					}
				}
			}

			if($PersonData_registryValidate) unlink($file_re_data_name);
			if($EvnData_registryValidate) unlink($file_re_pers_data_name);
			if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('exportRegistryToXml: Почистили папку за собой');

			// пишем в лог окончание
			$this->RegistryLog_model->saveRegistryLog(array(
				'RegistryLog_id' => $RegistryLog_id,
				'Registry_id' => $data['Registry_id'],
				'RegistryLog_CountEvn' => $SD_Z,
				'RegistryLog_begDate' => $RegistryLog_begDate,
				'RegistryLog_endDate' => '@curDate',
				'RegistryActionType_id' => 3, // Экспорт
				'pmUser_id' => $data['pmUser_id']
			));

			if(!$PersonData_registryValidate || !$EvnData_registryValidate){
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				return false;
			}elseif (!$PersonData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				return false;
			} elseif (!$EvnData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
				return false;
			}elseif ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;
				// Если не только формируем, но и хотим отправить, то пишем статус = готов к отправке
				if ($data['send']==1) {
					$data['RegistryCheckStatus_id'] = 1;
				}
				$this->dbmodel->SetXmlExportStatus($data);

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);

				if ( $data['send'] == 1 ) {
					$this->textlog->add('exportRegistryToXml: Реестр успешно отправлен');
					$this->ReturnData(array('success' => true));
				} else if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				} else {
					$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
				}
				$this->textlog->add("exportRegistryToXml: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");
			}
			else{
				$this->textlog->add("exportRegistryToXml: Ошибка создания архива реестра!");
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
			}
			$this->textlog->add("exportRegistryToXml: Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml:".toUtf($e->getMessage()));
			$this->ReturnError($e->getMessage());
		}

		return true;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToXml()
	{
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		ini_set("memory_limit", "2048M");

		$data = $this->ProcessInputData('exportUnionRegistryToXml', true);
		if ($data === false) { return false; }
		/* тест */
		/*
		$data = array_merge($data, getSessionParams());
		$out_dir = "re_xml_1111111_";
		@mkdir( EXPORTPATH_REGISTRY.$out_dir );
		$file_zip_sign = 'HM5705003T59_11091';
		$file_re_data_sign = "HM5705003T59_11091";
		$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
		$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";

		$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/r1.xml";
		$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/l1.xml";
		$zip=new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_re_data_name, "r1" . ".xml" );
		$zip->AddFile( $file_re_pers_data_name, "l1" . ".xml" );
		$zip->close();
		if (file_exists($file_zip_name))
		{
			echo "{'success':true,'Link':'{$file_zip_name}'}";
		}
		else{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
		}
		return false;
		*/
		/*if (!isSuperadmin()) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Выгрузка и отправка в XML временно недоступна!')));
			return false;
		}*/

		if ( isset($data['Registry_id']) && trim($data['Registry_id']) > 0 )
		{
			$this->load->library('textlog', array('file'=>'exportUnionRegistryToXml.log'));
			$this->textlog->add('');
			$this->textlog->add('exportUnionRegistryToXml: Запуск');

			// Определяем надо ли при успешном формировании проставлять статус и соответсвенно не выводить ссылки
			if (!isset($data['send']))
				$data['send'] = 0;

			// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр

			$this->textlog->add('exportUnionRegistryToXml: GetRegistryXmlExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
			$res = $this->dbmodel->GetRegistryXmlExport($data);
			$this->textlog->add('exportUnionRegistryToXml: GetRegistryXmlExport: Проверка закончена');
			if (is_array($res) && count($res) > 0)
			{
				if (!empty($res[0]['RegistryCheckStatus_id'])) {
					$data['RegistryCheckStatus_id'] = $res[0]['RegistryCheckStatus_id'];
				}

				// Запрет отправки в ТФОМС реестра "Проведён контроль ФЛК"
				if (!isSuperAdmin() && $data['send'] == 1 && $res[0]['RegistryCheckStatus_id'] === '5') {
					$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС.');
					$this->ReturnError('При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС');
					return;
				}

				// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
				if (!empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2) {
					$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
					$this->ReturnError('Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
					return;
				}

				// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
				if (empty($res[0]['RegistryData_Count'])) {
					$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: Нет записей в реестре.');
					$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', '13');
					return;
				}

				$this->textlog->add('exportUnionRegistryToXml: Получили путь из БД:'.$res[0]['Registry_xmlExportPath']);
				if ($data['send'] == 1) {
					if (($res[0]['RegistryCheckStatus_Code'] == 4) && (empty($data['OverrideControlFlkStatus']))) {
						$this->textlog->add('exportUnionRegistryToXml: Выход с вопросом: Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?');
						$this->ReturnError('Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?', '10');
						return;
					}

					// $data['OverrideExportOneMoreOrUseExist'] = 1;
				}

				if ($res[0]['Registry_xmlExportPath'] == '1')
				{
					$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
					$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
					return;
				}
				elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
				{
					$this->textlog->add('exportUnionRegistryToXml: Реестр уже выгружен');

					if ($res[0]['RegistryStatus_id'] == 4 || (in_array($res[0]['RegistryCheckStatus_Code'],array(0,1,3,8,10)))) {
						$data['OverrideExportOneMoreOrUseExist'] = 1; // можно лишь скачать существующий
						$data['send'] = 0; // отправить нельзя
					}

					if (empty($data['OverrideExportOneMoreOrUseExist'])) {
						$this->textlog->add('exportUnionRegistryToXml: Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
						$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', '11');
						return;
					} elseif ($data['OverrideExportOneMoreOrUseExist'] == 1) {
						// Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено
						if ($data['send']==1) {
							$this->textlog->add('exportUnionRegistryToXml: Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено ');
							if (($res[0]['RegistryCheckStatus_id']>0) && (!in_array($res[0]['RegistryCheckStatus_Code'],array(2,5,6,10)))) {
								$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра не возможна. ');
								$this->ReturnError('Реестр уже отправлен в ТФОМС. Повторная отправка данного реестра не возможна.');
								return;
							} else {
								// Хотим отправить уже сформированный реестр
								// $data['RegistryCheckStatus_id'] = 1;
								// $data['Status'] = $res[0]['Registry_xmlExportPath'];
								// $this->dbmodel->SetXmlExportStatus($data);

								$this->textlog->add('exportUnionRegistryToXml: Возвращаем пользователю файл в base64');
								$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
								$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
								return;
							}
						}
						$link = $res[0]['Registry_xmlExportPath'];
						$usePrevXml = '';
						if (empty($data['onlyLink'])) {
							$usePrevXml = 'usePrevXml: true, ';
						}
						echo "{'success':true, $usePrevXml'Link':'$link'}";
						$this->textlog->add('exportUnionRegistryToXml: Выход с передачей ссылкой: '.$link);
						return;
					} else {
						// https://redmine.swan.perm.ru/issues/48575
						if (
							(!empty($res[0]['RegistryCheckStatus_Code']) || $res[0]['RegistryCheckStatus_Code'] == 0)
							&& !in_array($res[0]['RegistryCheckStatus_Code'],array(-1,2,5,6,12))
						) {
							$this->textlog->add('exportUnionRegistryToXml: Выход с сообщением: Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!');
							$this->ReturnError('Формирование и отправка файла реестра в ТФОМС невозможна, т.к. <br/>текущий статус реестра: '.(($res[0]['RegistryCheckStatus_Name']=='')?'Не отправлен':$res[0]['RegistryCheckStatus_Name']).'!');
							return false;
						}
					}
				}
			}
			else
			{
				$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
				return;
			}

			$this->textlog->add('exportUnionRegistryToXml: refreshRegistry: Пересчитываем реестр');
			// Удаление помеченных на удаление записей и пересчет реестра
			if ($this->refreshRegistry($data)===false) {
				// выход с ошибкой
				$this->textlog->add('exportUnionRegistryToXml: refreshRegistry: При обновлении данных реестра произошла ошибка.');
				$this->ReturnError('При обновлении данных реестра произошла ошибка.');
				return;
			}
			$this->textlog->add('exportUnionRegistryToXml: refreshRegistry: Реестр пересчитали');


			// Формирование XML в зависимости от типа.
			// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
			try
			{
				// Объединенные реестры могут содержать данные любого типа
				// Получаем список типов реестров, входящих в объединенный реестр
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);// array(1, 2, 6, 7, 9, 11, 12, 16);
				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					// выход с ошибкой
					$this->textlog->add('exportUnionRegistryToXml: getUnionRegistryTypes: При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					$this->ReturnError('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					return false;
				}

				$this->load->model('RegistryLog_model');
				// пишем в лог начало
				$RegistryLogSaveResp = $this->RegistryLog_model->saveRegistryLog(array(
					'Registry_id' => $data['Registry_id'],
					'RegistryLog_begDate' => '@curDate',
					'RegistryActionType_id' => 3, // Экспорт
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($RegistryLogSaveResp[0]['Error_Msg'])) {
					$this->ReturnError($RegistryLogSaveResp[0]['Error_Msg']);
					return false;
				}

				if (empty($RegistryLogSaveResp[0]['RegistryLog_id'])) {
					$this->ReturnError('Ошибка записи в RegistryLog');
					return false;
				}

				$RegistryLog_id = $RegistryLogSaveResp[0]['RegistryLog_id'];
				$RegistryLog_begDate = $RegistryLogSaveResp[0]['curDate'];

				$data['RegistryCheckStatus_id'] = null; // сбрасываем статус при новом экспорте.
				$data['Status'] = '1';
				$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportUnionRegistryToXml: SetXmlExportStatus: Установили статус реестра в 1');
				// по задаче #57351 нам понадобяться Registry_EvnNum из предыдущих реестров (за 2 предыдущих периода)
				$data['Registry_IsRepeated'] = '';
				$oldRegistry_EvnNums = $this->dbmodel->getOldRegistryEvnNums($data);

				if (!empty($oldRegistry_EvnNums['Error_Msg'])) {
					$this->ReturnError($oldRegistry_EvnNums['Error_Msg']);
					return false;
				}

				// каталог в котором лежат выгружаемые файлы
				$out_dir = "re_xml_".time()."_".$data['Registry_id'];
				mkdir( EXPORTPATH_REGISTRY.$out_dir );

				$templ_header = "registry_pl_header";
				$templ_footer = "registry_pl_footer";
				$templ_person_header = "registry_person_header";
				$templ_person_footer = "registry_person_footer";

				// RegistryData
				$Registry_EvnNum = array();
				$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsingCommonUnion($data);
				/*
					Имена файлов должный удовлетворять нижеследующим условиям:
					Файлы пакета информационного обмена должны быть упакованы в архив формата ZIP. Имя файла формируется по следующему принципу:
					HMNiT59_YYMM1.XML, где
					H – константа, обозначающая передаваемые данные (H/L).
					Ni – Номер источника - реестровый номер МО.
					YY – две последние цифры порядкового номера года отчетного периода.
					MM – порядковый номер месяца отчетного периода:
				*/
				$file_re_data_sign = "HM".$SCHET[0]['CODE_MO']."T59_".date('ym')."1";
				$file_re_pers_data_sign = "LM".$SCHET[0]['CODE_MO']."T59_".date('ym')."1";

				if (in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud'))) {
					if ($data['PayType_SysNick'] == 'mbudtrans') {
						$file_re_data_sign .= '_NZ';
						$file_re_pers_data_sign .= '_NZ';
					} else {
						$file_re_data_sign .= '_SB';
						$file_re_pers_data_sign .= '_SB';
					}
				}

				// файл-тело реестра
				$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
				// временный файл
				$file_re_data_name_tmp = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign."_tmp.xml";
				// файл-перс. данные
				$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_pers_data_sign.".xml";

				$SCHET[0]['FILENAME'] = $file_re_data_sign;
				$ZGLV = array();
				$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
				$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;
				$ZGLV[0]['VERSION'] = in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud')) ? '1.0' : '3.1';

				$this->load->library('parser');

				// Заголовок для файла person
				$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_person_header, $ZGLV[0], true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers);

				foreach($registrytypes as $type) {
					$this->textlog->add('exportUnionRegistryToXml: Тип реестров: ' . $type);

					$result = $this->dbmodel->loadRegistryDataForXmlByFunc2018(true, $type, $data, $Registry_EvnNum, $oldRegistry_EvnNums, $file_re_data_name_tmp, $file_re_pers_data_name);

					$this->textlog->add('exportUnionRegistryToXml: Получили все данные из БД ');

					if ($result === false)
					{
						$data['Status'] = '';
						$this->dbmodel->SetXmlExportStatus($data);
						$this->textlog->add('exportUnionRegistryToXml: Выход с ошибкой дедлока');
						$this->ReturnError($this->error_deadlock);
						return false;
					}
				}

				// Заголовок
				$SCHET[0]['SD_Z'] = $this->dbmodel->getSDZ2018();
				$SCHET[0]['VERSION'] = in_array($data['PayType_SysNick'], array('mbudtrans','mbudtrans_mbud')) ? '1.0' : '3.1';

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_header, $SCHET[0], true);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);

				// пишем хедер в новый файл
				file_put_contents($file_re_data_name, $xml);

				// Тело файла с данными начитываем из временного (побайтно)
				if ( file_exists($file_re_data_name_tmp) ) {
					// Устанавливаем начитываемый объем данных
					$chunk = 10 * 1024 * 1024; // 10 MB

					$fh = @fopen($file_re_data_name_tmp, "rb");

					if ( $fh === false ) {
						$data['Status'] = '';
						$this->dbmodel->SetXmlExportStatus($data);
						$this->textlog->add('exportRegistryToXml: Ошибка при открытии файла');
						$this->ReturnError('Ошибка при открытии файла');
						return false;
					}

					while ( !feof($fh) ) {
						file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
					}

					fclose($fh);

					unlink($file_re_data_name_tmp);
				}

				// Конец
				$xml = $this->parser->parse_ext('export_xml/' . $templ_footer, array(), true);
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);

				// Конец для файла person
				$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_footer, array(), true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

				$file_zip_sign = $file_re_data_sign;
				$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
				
				$this->textlog->add('exportUnionRegistryToXml: Создали XML-файлы: ('.$file_re_data_name.' и '.$file_re_pers_data_name.')');
				$zip=new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
				$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
				$zip->close();
				$this->textlog->add('exportUnionRegistryToXml: Упаковали в ZIP '.$file_zip_name);

				$PersonData_registryValidate = true;
				$EvnData_registryValidate = true;
				if($data['session']['setting']['server']['check_implementFLK'] && $res[0]){
					// если включена проверка ФЛК в параметрах системы
					// получим xsd шаблон для проверки
					$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);
					if(count($settingsFLK) > 0){
						//если запись найдена
						$settingsFLK = $settingsFLK[0];
						$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
						$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	
						
						//Проверка со случаями
						if($tplEvnDataXSD){
							//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
							$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
							//путь к файлу XSD
							$fileEvnDataXSD = IMPORTPATH_ROOT.$this->upload_path.$dirTpl."/".$tplEvnDataXSD;
							//Файл с ошибками					
							$validateEvnData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';						
							if(file_exists($fileEvnDataXSD)) {
								$EvnData_registryValidate = $this->dbmodel->Reconciliation($file_re_data_name, $fileEvnDataXSD, 'file', $validateEvnData_err_file);
							}
						}
						//Проверка с персональными данными
						if($tplPersonDataXSD){
							//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
							$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
							//путь к файлу XSD
							$filePersonDataXSD = IMPORTPATH_ROOT.$this->upload_path.$dirTpl."/".$tplPersonDataXSD;
							//Файл с ошибками					
							$validatePersonData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';
							if(file_exists($filePersonDataXSD)) {
								$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
							}
						}
					}
				}
				
				if($EvnData_registryValidate) unlink($file_re_data_name);
				if($PersonData_registryValidate) unlink($file_re_pers_data_name);
				if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('exportUnionRegistryToXml: Почистили папку за собой ');

				// пишем в лог окончание
				$this->RegistryLog_model->saveRegistryLog(array(
					'RegistryLog_id' => $RegistryLog_id,
					'Registry_id' => $data['Registry_id'],
					'RegistryLog_CountEvn' => count($Registry_EvnNum),
					'RegistryLog_begDate' => $RegistryLog_begDate,
					'RegistryLog_endDate' => '@curDate',
					'RegistryActionType_id' => 3, // Экспорт
					'pmUser_id' => $data['pmUser_id']
				));
				
				if(!$PersonData_registryValidate && !$EvnData_registryValidate){
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnError('Реестр не прошёл проверку ФЛК: <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
							<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
							<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
							<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
					return false;
				}elseif (!$PersonData_registryValidate) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);

					$this->ReturnError('Реестр не прошёл проверку ФЛК: <br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
							<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip</a>');
					return false;
				}elseif (!$EvnData_registryValidate) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);

					$this->ReturnError('Реестр не прошёл проверку ФЛК: <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
							<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
							<br><a href="'.$file_zip_name.'" target="_blank">zip</a>');
					return false;
				}elseif (file_exists($file_zip_name)){
					$data['Status'] = $file_zip_name;
					// Если не только формируем, но и хотим отправить, то пишем статус = готов к отправке
					if ($data['send']==1) {
						// $data['RegistryCheckStatus_id'] = 1;
					}
					$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);
					$this->dbmodel->SetXmlExportStatus($data);
					/*
					header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
					header("Content-Type: text/html");
					header("Pragma: no-cache");
					*/
					if ($data['send']==1) {
						$this->textlog->add('exportUnionRegistryToXml: Возвращаем пользователю файл в base64');
						$filebase64 = base64_encode(file_get_contents($file_zip_name));
						$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					} else {
						$this->textlog->add('exportUnionRegistryToXml: Передача ссылки: '.$file_zip_name);
						//echo "{'success':true,'Link':'{$file_zip_name}'}";
					}
					$this->textlog->add("exportUnionRegistryToXml: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");

					// Пишем информацию о выгрузке в историю
					$this->dbmodel->dumpRegistryInformation($data, 2);
				}
				else{
					$this->textlog->add("exportUnionRegistryToXml: Ошибка создания архива реестра!");
					$this->ReturnError('Ошибка создания архива реестра!');
				}
				$this->textlog->add("exportUnionRegistryToXml: Финиш");
				return true;
			}
			catch (Exception $e)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add("exportUnionRegistryToXml:".toUtf($e->getMessage()));
				$this->ReturnError($e->getMessage());
			}
		}
		else
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка. Не верно задан идентификатор счета!')));
			return false;
		}
	}

	/**
	 *  Функция возвращает запись/записи реестра
	 *  Входящие данные: _POST['Registry_id'],
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования реестра (счета)
	 */
	function loadRegistry()
	{
		$data = $this->ProcessInputData('loadRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistry($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}
			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array();
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
			$this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		if (mb_strpos($node, 'PayType.1.bud') !== false) {
			if ($data['level'] >= 2) {
				$data['level']++; // для бюджета нет объединённых реестров
			}
			$data['PayType_SysNick'] = 'bud';
		}
		if(mb_strpos($node, 'PayType.1.mbudtrans') !== false){
			$data['PayType_SysNick'] = 'mbudtrans';
		}

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. ЛПУ
			{
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 1: // Уровень 1. ОМС или бюджет
			{
				$childrens = array(
					array('PayType_SysNick' => 'oms', 'PayType_Name' => 'ОМС'),
					array('PayType_SysNick' => 'bud', 'PayType_Name' => 'Местный и федеральный бюджет'),
					array('PayType_SysNick' => 'mbudtrans', 'PayType_Name' => 'Межбюджетный трансферт')
				);
				$field = Array('object' => "PayType",'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Объединённые реестры
			{
				$childrens = array(
					array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
				);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
			case 3: // Уровень 3. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
			case 4: // Уровень 4. Статусы реестров
			{
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получение номера объединенного реестра
	 */
	function getUnionRegistryNumber()
	{
		$data = $this->ProcessInputData('getUnionRegistryNumber', true);
		if ($data === false) { return false; }

		$LpuRegNum = $this->dbmodel->getFirstResultFromQuery('select top 1 ISNULL(Lpu_f003mcod, Lpu_interCode) from v_Lpu where Lpu_id = :Lpu_id', $data);

		$previous_month = date('m', strtotime('first day of previous month'));
		$year_of_previous_month = date('Y', strtotime('first day of previous month'));
		$Registry_Num =$LpuRegNum.'_'.$previous_month.$year_of_previous_month;
		$this->ReturnData(array(
			'UnionRegistryNumber' => $Registry_Num
		));
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry()
	{
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$checkRegistryNumFormat = $this->dbmodel->checkRegistryNumFormat($data['Registry_Num'], $data['Lpu_id']);
		if (is_array($checkRegistryNumFormat) && !empty($checkRegistryNumFormat['Error_Msg'])) {
			$this->ReturnError($checkRegistryNumFormat['Error_Msg']);
			return false;
		}

		if (!$this->dbmodel->checkUnionRegistryNumUnique($data)) {
			$this->ReturnError('Невозможно сохранить реестр с таким номером. Номер должен быть уникальным по МО в году вне зависимости от того объединенный он или предварительный (требование ТФОМС)');
			return false;
		}

		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm()
	{
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry()
	{
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Получение списка данных объединённого реестра
	 */
	function loadUnionRegistryData()
	{
		$data = $this->ProcessInputData('loadUnionRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadUnionRegistryErrorTFOMS()
	{
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMS($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Функция возвращает ошибки данных реестра стадии БДЗ
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadUnionRegistryErrorBDZ()
	{
		$data = $this->ProcessInputData('loadUnionRegistryErrorBDZ', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorBDZ($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление дублей
	 */
	public function deleteRegistryDouble() {
		$data = $this->ProcessInputData('deleteRegistryDouble', true);
		if ($data === false) return false;
		
		// Удаляем записи о дублях
		switch ( $data['mode'] ) {
			case 'current':
				$response = $this->dbmodel->deleteRegistryDouble($data);
				break;

			case 'all':
				$response = $this->dbmodel->deleteRegistryDoubleAll($data);
				break;
		}

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}
