<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Utils - контроллер для вспомогательных операций
 * 1. Объединение записей
 * 2. Объединение людей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      15.07.2009
 */
/**
 * @property Utils_model $dbmodel
 * @property Utils_model $umodel
 *
 */
class Utils extends swController
{

	private $moduleMethods = [
		'GetObjectList',
		'withFileIntegration',
		'checkMainDBConnection'
	];

	/**
	 *	Конструктор
	 */
    function __construct()
    {
        parent::__construct();
        $method = $this->router->fetch_method();

        if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('Utils_model', 'dbmodel');
		}

		$this->inputRules = array(
			'getTableData' => array(
				array('field' => 'table', 'label' => 'Наименование таблицы', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'schema', 'label' => 'Схема', 'rules' => '', 'type' => 'string', 'default' => 'dbo'),
				array('field' => 'id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'returnHTML', 'label' => 'Признак необходимости вернуть данные в формате HTML', 'rules' => '', 'type' => 'int'),
			),
			'convertMedSpecOmsCodes' => array(
				//
			),
			'getObjectNameWithPath' => array(
				array(
					'field' => 'object',
					'label' => 'Наименование справочника',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Sub_SysNick',
					'label' => 'Подуровень',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => ' / ',
					'field' => 'separator',
					'label' => 'Разделитель строк',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'dbo',
					'field' => 'scheme',
					'label' => 'Схема',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getParentNodeList' => array(
				array(
					'field' => 'object',
					'label' => 'Наименование справочника',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Sub_SysNick',
					'label' => 'Подуровень',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'dbo',
					'field' => 'scheme',
					'label' => 'Схема',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор записи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getSelectionTreeData' => array(
				array(
					'field' => 'object',
					'label' => 'Наименование справочника',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'node',
					'label' => 'Уровень вложенности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Sub_SysNick',
					'label' => 'Подуровень',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'default' => 'dbo',
					'field' => 'scheme',
					'label' => 'Схема',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'pid',
					'label' => 'Идентификатор родительской записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'showCodeMode',
					'label' => 'Признак необходимости добавлять код к наименованиям',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onlyActual',
					'label' => 'Флаг "Только актуальные на текущую дату"',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => '0000',
					'field' => 'treeSortMode',
					'label' => 'Режим сортировки записей в дереве',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'getSubdivisionData' => array(

			),
			'doFileUpload' => array(
				array(
					'field' => 'UploadFieldName',
					'label' => 'Наименование поля экспорта файла',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AllowedTypes',
					'label' => 'Разрешенные типы файлов к загрузке',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getObjectSearchData' => array(
				array(
					'field' => 'code',
					'label' => 'Код',
					'rules' => 'ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'name',
					'label' => 'Наименование',
					'rules' => 'ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Наименование справочника',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'onlyActual',
					'label' => 'Флаг "Только актуальные на текущую дату"',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 'dbo',
					'field' => 'scheme',
					'label' => 'Схема',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'getUnionHistory' =>array(
				array(
					'field' => 'PersonDoubles_insDT_Range',
					'label' => 'Дата отправки на модерацию',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'zLpu_id',
					'label' => 'МО запроса модерации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDoubles_updDT_Range',
					'label' => 'Дата модерации',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'pLpu_id',
					'label' => 'МО прикрепления',
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
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PersonDoublesStatus',
					'label' => 'Результат',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'groups',
					'label' => 'Группы пользователя',
					'rules' => '',
					'type' => 'string',
					'session_value' => 'groups' //параметр в сессии из которого можно взять значение если пришедшее значение пусто
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
			),
            'doPlanPersonUnion' => array(
                array(
                    'field' => 'Records',
                    'label' => 'Данные по объединяемой строке',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type'  => 'id'
                )
            ),
            'doPersonUnion' => array(
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Records',
                    'label' => 'Данные по объединяемой строке',
                    'rules' => '',
                    'type'  => 'string'
                ),
				array(
					'field' => 'PersonDoubles_id',
					'label' => 'PersonDoubles_id',
					'rules' => '',
					'type'  => 'id'
				),
                array(
                    'field' => 'fromRegistry',
                    'label' => 'fromRegistry',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'fromModeration',
                    'label' => 'fromModeration',
                    'rules' => '',
                    'type'  => 'int'
                )
            ),
            'doRecordUnion' => array(
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Records',
                    'label' => 'Данные по объединяемой строке',
                    'rules' => '',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Table',
                    'label' => 'Таблица',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
            'checkRecordsForUnion' => array(
                array(
                    'field' => 'Records',
                    'label' => 'Данные по объединяемой строке',
                    'rules' => 'required',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Table',
                    'label' => 'Таблица',
                    'rules' => 'required',
                    'type'  => 'string'
                )
            ),
            'getRecordUnionSettings' => array(
                array(
                    'field' => 'Table',
                    'label' => 'Таблица',
                    'rules' => 'required',
                    'type'  => 'string'
                ),
				array(
					'field' => 'mainRecord',
					'label' => 'Основная запись',
					'rules' => 'required',
					'type'  => 'string'
				),
				array(
					'field' => 'minorRecord',
					'label' => 'Второстепеннная запись',
					'rules' => 'required',
					'type'  => 'string'
				),
            ),
            'doRecordUnionWithSettings' => array(
                array(
                    'field' => 'Table',
                    'label' => 'Таблица',
                    'rules' => 'required',
                    'type'  => 'string'
                ),
				array(
					'field' => 'mainRecord',
					'label' => 'Основная запись',
					'rules' => 'required',
					'type'  => 'string'
				),
				array(
					'field' => 'minorRecord',
					'label' => 'Второстепеннная запись',
					'rules' => 'required',
					'type'  => 'string'
				),
				array(
					'field' => 'settings',
					'label' => 'Настройки',
					'rules' => 'required',
					'type'  => 'string'
				),
            ),
            'doPersonEvnTransfer' => array(
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => '',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'Records',
                    'label' => 'Данные по объединяемой строке',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
			'loadDiagList' => array(
                array(
                    'field' => 'MorbusProfDiag_id',
                    'label' => 'MorbusProfDiag_id',
                    'rules' => '',
                    'type'  => 'id'
				),
                array(
                    'field' => 'PersonRegisterType_SysNick',
                    'label' => 'Тип регистра',
                    'rules' => 'trim',
                    'type'  => 'string'
				),
                array(
                    'field' => 'MorbusType_SysNick',
                    'label' => 'Тип заболевания/нозологии',
                    'rules' => 'trim',
                    'type'  => 'string'
				),
				array(
                    'field' => 'MKB',
                    'label' => 'MKB',
                    'rules' => '',
                    'type'  => 'string'
				),
				array(
                    'field' => 'isMain',
                    'label' => 'Основной?',
                    'rules' => '',
                    'type'  => 'string'
				),
                array(
                    'field' => 'PersonSickness_id',
                    'label' => 'PersonSickness_id',
                    'rules' => '',
                    'type'  => 'id'
				),
                array(
                    'field' => 'Diag_Name',
					'label' => 'Наименование диагноза',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'filterDate',
					'label' => 'Дата актуальности диагноза',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'filterDiag',
					'label' => 'Список допустимых диагнозов',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'deathDiag',
					'label' => 'Параметры диагнозов смерти',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'Diag_id',
                    'label' => 'Идентификатор диагноза',
                    'rules' => '',
                    'type'  => 'id'
				),
                array(
                    'field' => 'filterDate',
                    'label' => 'Дата для фильтрации',
                    'rules' => '',
                    'type'  => 'date'
				),
                array(
                    'field' => 'withGroups',
                    'label' => 'Признак с группами',
                    'rules' => '',
                    'type'  => 'int'
				),
                array(
                    'field' => 'formMode',
                    'label' => 'Тип формы',
                    'rules' => '',
                    'type'  => 'string'
				),
                array(
                    'field' => 'isHeredityDiag',
                    'label' => 'Признак',
                    'rules' => '',
                    'type'  => 'int'
				),
                array(
                    'field' => 'isEvnDiagDopDispDiag',
                    'label' => 'Признак',
                    'rules' => '',
                    'type'  => 'int'
				),
				array(
					'field' => 'registryType',
					'label' => 'Тип регистра',
					'rules' => '',
					'type'  => 'string'
				),
                array(
                    'field' => 'isInfectionAndParasiteDiag',
                    'label' => 'Фильтр по диагнозам группы A и B',
                    'rules' => '',
                    'type'  => 'int'
				),
                array(
                    'field' => 'checkAccessRights',
                    'label' => 'Проверить права доступа',
                    'rules' => '',
                    'type'  => 'checkbox'
				)
			),
			'withFileIntegration' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор мед. службы',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getReplicationInfo' => array(
				array('field' => 'db', 'label' => 'БД', 'rules' => 'required', 'type' => 'string'),
			)
		);

    }

	/**
	 * Проводит объединение записей в заданной таблице 
	 */
	function doRecordUnion()
	{
		$this->load->model("Utils_model", "umodel");

        $data = $this->ProcessInputData('doRecordUnion',true);
        if ($data === false) {return false;}
		
		ConvertFromWin1251ToUTF8($data['Records']);
		$data['Records'] = json_decode($data['Records'], true);

		$response = $this->umodel->doRecordUnion($data);
        $this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();
		return true;
	}

	/**
	 * Проверка записей для объединения
	 */
	function checkRecordsForUnion()
	{
		$this->load->model("Utils_model", "umodel");

        $data = $this->ProcessInputData('checkRecordsForUnion',true);
        if ($data === false) {return false;}

		ConvertFromWin1251ToUTF8($data['Records']);
		$data['Records'] = json_decode($data['Records'], true);

		$response = $this->umodel->checkRecordsForUnion($data);
        $this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();
		return true;
	}

	/**
	 * Получение настроек для объединения записей
	 */
	function getRecordUnionSettings()
	{
		$this->load->model("Utils_model", "umodel");

        $data = $this->ProcessInputData('getRecordUnionSettings',true);
        if ($data === false) {return false;}

		$response = $this->umodel->getRecordUnionSettings($data);
        $this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Объединение записей с настройками
	 */
	function doRecordUnionWithSettings() {
		$this->load->model("Utils_model", "umodel");

		$data = $this->ProcessInputData('doRecordUnionWithSettings',true);
		if ($data === false) {return false;}

		$response = $this->umodel->doRecordUnionWithSettings($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Проводит объединение записей по человеку
	 */
	function doPersonUnion()
	{
    	$this->load->model("Utils_model", "umodel");

        $data = $this->ProcessInputData('doPersonUnion',true);
		if ($data === false) {return false;}

		ConvertFromWin1251ToUTF8($data['Records']);
		$data['Records'] = json_decode($data['Records'], true);

		//Проверяем что среди записей нет ПОКОмонов (Потенциально-опасных к объединению, это люди, где полученные от ТФОМС периодики заменили сразу ФИО+ДР)
		//Временно отключаем проверку
		/*foreach ($data['Records'] as $key => $value) {
			foreach ($data['Records'] as $key2 => $value2) {
				if (!empty($value['Person_Surname']) && !empty($value2['Person_Surname']) && $value['Person_Surname'] != $value2['Person_Surname']
					&& !empty($value['Person_Firname']) && !empty($value2['Person_Firname']) && $value['Person_Firname'] != $value2['Person_Firname']
					&& !empty($value['Person_Secname']) && !empty($value2['Person_Secname']) && $value['Person_Secname'] != $value2['Person_Secname']
					&& !empty($value['Person_Birthdate']) && !empty($value2['Person_Birthdate']) && $value['Person_Birthdate'] != $value2['Person_Birthdate']
				) {
					DieWithError('Объединение двойников, у которых отличаются ФИО и ДР запрещено!');
				}
			}
		}*/

		$response = $this->umodel->doPersonUnion($data);
        $this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();
	}
	
	/**
	 * Проводит перенос случаев по человеку
	 */
	function doPersonEvnTransfer()
	{
		if ( !isSuperadmin() ) {
			DieWithError('Доступ запрещен!');
		}
		$this->load->model("Utils_model", "umodel");
        $data = $this->ProcessInputData('doPersonEvnTransfer',true);
        if ($data === false) {return false;}
		
		ConvertFromWin1251ToUTF8($data['Records']);
		$data['Records'] = json_decode($data['Records'], true);

		$response = $this->umodel->doPersonEvnTransfer($data);
        $this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();

	}
	
	
	/**
	 * Планирует объединение записей по человеку
	 */
	function doPlanPersonUnion()
	{
		$this->load->model("Utils_model", "umodel");

        $data = $this->ProcessInputData('doPlanPersonUnion',true);
        if ($data === false) {return false;}

		ConvertFromWin1251ToUTF8($data['Records']);
		$data['Records'] = json_decode($data['Records'], true);

		$response = $this->umodel->doPlanPersonUnion($data);
        $this->ProcessModelSave($response,true,'Извините, в данный момент сервис недоступен!')->ReturnData();

	}
	

	/**
	 * Удаление записи из таблицы с зеркала
	 */
	function ObjectRecordMirrorDelete()
	{
		$this->ObjectRecordDelete(true);
	}


	/**
	 * Общее удаление записи из таблицы
	 */
	function ObjectRecordDelete($isMirror=false)
	{
        if ($isMirror)
        {
			$this->db = null;
            $this->load->database('registry', false);
        }
		$this->load->model('Utils_model', 'umodel');
		$ids = array();
		$linkedTables = array();
		$object = '';
		$post = $_POST;
		$val = array();
		$obj_isEvn = false;
		
		$data = array();
		$data = array_merge($data, getSessionParams());
		
		if ((isset($post['ids'])))
		{
			$ids = json_decode($post['ids']);
		}
		if ((isset($post['id'])) && (is_numeric($post['id'])) && ($post['id'] > 0))
		{
			array_push($ids,$post['id']);
		}
		if (!empty($ids)) {
			$object = $post['object'];
			$obj_isEvn = (isset($post['obj_isEvn']))?$post['obj_isEvn']:false;
		}
		$scheme = 'dbo';
		if (isset($post['scheme'])){
			$scheme = $post['scheme'];
		}

		if (!empty($post['linkedTables'])) {
			foreach (explode(", ", $post['linkedTables']) as $key => $value){
				array_push($linkedTables, array('schema' => $scheme, 'table' => $value));
			}
		}

		if ((count($ids) > 0) && (strlen($object)>0))
		{
			if ($object == 'LpuSection') //Если удаляем отделение
			{
				$this->load->model('LpuStructure_model','lsmodel');
				$checkStaff_err = $this->lsmodel->checkStaff($data); // Проверяем штатное расписание (в рамках задачи http://redmine.swan.perm.ru/issues/17622)
				if ( !empty($checkStaff_err) ) {
					$this->ReturnError($checkStaff_err);
					return false;
				}
				$linksExists_err = $this->lsmodel->CheckLpuSectionLinksExists($ids); // Проверка ссылок на отделения в документах https://redmine.swan.perm.ru/issues/34278
				if ( !empty($linksExists_err) ) {
					$this->ReturnError('Удаление невозможно. ' . $linksExists_err);
					return false;
				}
				$hasChildObjects = $this->lsmodel->checkLpuSectionHasChildObjects($ids); // Проверка наличия дочерних объектов
				if ( !empty($hasChildObjects) ) {
					$this->ReturnError('Удаление невозможно. ' . $hasChildObjects);
					return false;
				}
			}
			if ($object == 'Org') //Если удаляем организацию
			{
				$this->load->model('Org_model', 'orgmodel');
				$checkData = array('Org_id' => $post['id']);
				if ($result = $this->orgmodel->checkOrgHasMIID($checkData, 'Удаление')) {
					$this->ReturnData($result);
					echo json_return_errors($result["Error_Msg"]);
					exit;
				}
			}
			
			if ($object == 'LpuDispContract') //Если удаляем договоры по сторонним специалистам
			{
				$this->load->model('LpuPassport_model', 'LpuPassport_model');
				// удаление записей "Услуга договора"
				$params_del = array(
					'LpuDispContract_id' => $ids,
					'pmUser_id' => $data['session']['pmuser_id']
				);
				$lpu_data = $this->LpuPassport_model->deleteServiceContracts($params_del);
			}
			$result = $this->umodel->ObjectRecordsDelete($data, $object, $obj_isEvn, $ids, $scheme, $linkedTables);
			if (is_array($result) && (count($result) == 1))
			{
				if ($result[0]['Error_Code']>0)
				{
					switch ($result[0]['Error_Code'])
					{
						case 547:
						case 23503:
							$result[0]['Error_Message'] = 'Удаление невозможно, т.к. существуют объекты в БД, ссылающиеся на ' . (count($ids) == 1 ? 'удаляемую запись' : 'удаляемые записи') . '.';
						break;
					}
					$result[0]['success'] = false;
				}
				else 
				{
					$result[0]['success'] = true;
				}
				$val = $result[0];
			}
			else
			{
				$val = array('success' => false, 'Error_Code' => 100002, 'Error_Message' => 'Системная ошибка при выполнении скрипта');
			}
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}
	
	
	/**
	 * Функция получения массива данных из любой таблицы-справочника
	 * @author Night
	 */
	function GetObjectList()
	{
		$this->load->model("Utils_model", "umodel");
		$this->load->helper('Options');
		$data = $_POST;
		$data['session'] = $_SESSION;
		$prefix = "";

		// перенесено сюда из dlo_ivp. Нужно ли?
		if ((isset($data['Server_id'])) && (!empty($data['Server_id'])) && ($data['Server_id']=='check_it'))
			$data['Server_id'] = $_SESSION['server_id'];

		// savage: Зачем нужен Lpu_id при загрузке справочников? o_O
		// night: функция юзается не только на загрузке справочников
		if (isset($data['Lpu_id']))
		{
			if (!isMinZdrav() && !isSuperAdmin()) 
			{
				$data['Lpu_id'] = $_SESSION['lpu_id'];
			}
		}
		// комбик Подразделение в Аптеке
		if (!empty($data['AptLpu_id']))
		{
			$data['Lpu_id'] = $data['AptLpu_id'];
			unset($data['AptLpu_id']);
		}
		// ---
		$val = array();
		if ((isset($data['prefix'])) && (!empty($data['prefix']))) {
			$prefix = $data['prefix'];
		}

		if ($this->usePostgreLis && isset($_SESSION['CurArmType']) && in_array($_SESSION['CurArmType'], ['lab', 'reglab', 'pzm']) && isset($data['LpuSection']) && isset($data['filterLpu_id'])) {
			$list = $this->lis->GET('Utils/List', $data, 'list');
			if (!$this->isSuccessful($list)) {
				return $list;
			}
		} else {
			$this->load->database();
			$list = $this->umodel->GetObjectList($data);
		}

		if ( $list != false && count($list) > 0 )
		{
			foreach ($list as $index => $row)
			{
				unset($vals);
				foreach ($row as $i => $irow)
				{
					if (!is_object($irow)) {
						if (mb_strtolower(trim($irow)) != 'null')
							$vals[$prefix.$i] = toUTF(trim($irow)); // $prefix.$i.'Edit'
						else 
							$vals[$prefix.$i] = '';
					}
				}
				$val[] = $vals;
			}
		}
		$this->ReturnData($val);
	}


	/**
	 *  Получение справочника диагнозов
	 *  Входящие данные: $_POST['Diag_id'],
	 *                   $_POST['Diag_Name']
	 *  На выходе: JSON-строка
	 *  Используется: форма поиска диагноза
	 */
	function loadDiagList() {

		$this->load->model('Utils_model', 'dbmodel');

		$data = $this->ProcessInputData('loadDiagList', true);
		if ($data === false) { return false; }

		$data['search_mode'] = 0;
		$data['query'] = '';

		if (!empty($data['isEvnDiagDopDispDiag']) && $data['isEvnDiagDopDispDiag'] == 1) {
			$data['isEvnDiagDopDispDiag'] = true;
		} else {
			$data['isEvnDiagDopDispDiag'] = false;
		}
		
		if (!empty($data['isHeredityDiag']) && $data['isHeredityDiag'] == 1) {
			$data['isHeredityDiag'] = true;
		} else {
			$data['isHeredityDiag'] = false;
		}
		
		if (!empty($data['isInfectionAndParasiteDiag']) && $data['isInfectionAndParasiteDiag'] == 1) {
			$data['isInfectionAndParasiteDiag'] = true;
		} else {
			$data['isInfectionAndParasiteDiag'] = false;
		}
		
		if (!empty($data['withGroups']) && $data['withGroups'] == 1) {
			$data['withGroups'] = true;
		} else {
			$data['withGroups'] = false;
		}
		
		if ( (isset($data['Diag_id'])) && (is_numeric($data['Diag_id'])) && ($data['Diag_id'] > 0) ) {
			$data['search_mode'] = 1;
		}
		else if ( (isset($data['Diag_Name'])) && (strlen(trim($data['Diag_Name'])) > 0) ) {
			$data['query'] = trim($data['Diag_Name']); // убрал конвертирование, оно выполняется в ProcessInputData сейчас #21065
			if ( preg_match('/^\w{1}\d{3}$/', $data['query']) ) {
				$data['query'] = sw_translit($data['query']);
				$data['query'] = mb_substr($data['query'], 0, 3) . '.' . mb_substr($data['query'], 3);
				$data['search_mode'] = 2;
			}
			else if ( preg_match('/^\w{1}\d{2}\.\d{1}$/', $data['query']) ) {
				$data['query'] = sw_translit($data['query']);
				$data['search_mode'] = 2;
			}
			else {
				$data['search_mode'] = 3;
			}
		}
		else if ((isset($data['PersonSickness_id'])) && ($data['PersonSickness_id'] > 0))
		{
			$data['query'] = '';
			$data['search_mode'] = 4;
		}

		$response = $this->dbmodel->loadDiagList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}


	/**
	 *  Получение справочника услуг
	 *  Входящие данные: $_POST['query'],
	 *                   $_POST['Usluga_id'],
	 *                   $_POST['Usluga_Code']
	 *                   $_POST['Usluga_Name']
	 *                   $_POST['Usluga_date'] дата оказания услуги в формате дд.мм.гггг
	 *  На выходе: JSON-строка
	 *  Используется: форма поиска ТАП
	 */
	function loadUslugaList() {
		$this->load->model('Utils_model', 'dbmodel');

		$data = array();
		$val  = array();

		$data['search_mode'] = 0;
		$data['Usluga_Code'] = '';
		$data['allowedCatCode'] = NULL;
		$data['Usluga_id']   = 0;
		$data['Usluga_date'] = NULL;
		$data['Usluga_Price'] = 0;
		
		if ( isset($_POST['allowedCatCode']) && strlen($_POST['allowedCatCode']) > 0 ) {
			$data['allowedCatCode'] = $_POST['allowedCatCode'];
		}
		
		if ( isset($_POST['allowedCodeList']) && strlen($_POST['allowedCodeList']) > 0 ) {
			$data['allowedCodeList'] = $_POST['allowedCodeList'];
		}

		if ( (isset($_POST['Usluga_id'])) && (is_numeric($_POST['Usluga_id'])) && ($_POST['Usluga_id'] > 0) ) {
			$data['Usluga_id'] = $_POST['Usluga_id'];
			$data['search_mode'] = 1;
		}
		else if ( (isset($_POST['query'])) && (strlen(trim($_POST['query'])) > 0) ) {
			$data['Usluga_Code'] = toAnsi(trim($_POST['query']));
			$data['search_mode'] = 2;
		}
		else {
			if ( (isset($_POST['Usluga_Name'])) && (strlen(trim($_POST['Usluga_Name'])) > 0) ) {
				$data['Usluga_Name'] = toAnsi(trim($_POST['Usluga_Name']));
				$data['search_mode'] = 3;
			}

			if ( (isset($_POST['Usluga_Code'])) && (strlen(trim($_POST['Usluga_Code'])) > 0) ) {
				$data['Usluga_Code'] = toAnsi(trim($_POST['Usluga_Code']));
				$data['search_mode'] = 3;
			}
		}

		if ( (isset($_POST['Usluga_date'])) && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $_POST['Usluga_date']))
		{
			$data['Usluga_date'] = ConvertDateFormat($_POST['Usluga_date']);
		}

		if ( $data['search_mode'] > 0 ) {
			$response = $this->dbmodel->loadUslugaList($data);

			/*if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
			}*/
            $this->ProcessModelList($response,true,true)->ReturnData();
		}

		//$this->ReturnData($val);
	}
	
	
	/**
	 * Аудит записей
	 * Входящие данные: $_POST['key_id'],
	 *                  $_POST['key_field']
	 * На выходе: JSON-строка
	 * Используется: компонент AuditWindow
	 */
	function getAudit() {

		$this->load->model('Utils_model', 'utilsmodel');
		$this->load->helper('Text');

		$data = array();
		$val  = array();

		$data['key_id'] = 0;
		$data['key_field'] = '';
		$data['deleted'] = 0;

		if ((isset($_POST['key_id'])) && (is_numeric($_POST['key_id'])) && ($_POST['key_id'] > 0)) {
			$data['key_id'] = $_POST['key_id'];
			if (isset($_POST['key_field'])) {
				$data['key_field'] = $_POST['key_field'];
			}

			if ( isset($_POST['Server_id']) ) {
				$data['Server_id'] = (int)$_POST['Server_id'];
			}

			if (in_array($data['key_field'], array('Registry_id'))) {
				$this->db = null;
				$this->load->database('registry');
			}
		}
		else{ //КОСТЫЛЬ!!!!11 https://redmine.swan.perm.ru/issues/59658
			if(isset($_POST['key_id']) && strpos($_POST['key_id'],'XmlTemplateCat') !== false)
			{
				$data['key_field'] = 'XmlTemplateCat_id';
				$data['key_id'] = substr($_POST['key_id'],strpos($_POST['key_id'],'_')+1);
			}
			else if(isset($_POST['key_id']) && strpos($_POST['key_id'],'XmlTemplate') !== false)
			{
				$data['key_field'] = 'XmlTemplate_id';
				$data['key_id'] = substr($_POST['key_id'],strpos($_POST['key_id'],'_')+1);
			}
		}
		//var_dump($_POST);die;
		if(isset($_POST['registry_id'])){
			$data['registry_id'] = $_POST['registry_id'];
		}
		if(isset($_POST['deleted']) && $_POST['deleted'] == 1){
			$data['deleted'] = 1;
		}

		if ( !empty($_POST['schema']) ) {
			$data['schema'] = $_POST['schema'];
		}

		$response = $this->utilsmodel->getAudit($data);
		
		/*if (is_array($response) && count($response) > 0) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$val[] = $response[0];
		}

		$this->ReturnData($val);*/
        $this->ProcessModelList($response,true,true)->ReturnData();
	}
	
	
	/**
	 * История модерации двойников, показывает статус всех двойников,
	 * посланных на модерацию текущим пользователем
	 * На выходе: JSON-строка
	 * Используется: компонент swPersonUnionHistoryWindow
	 */
	function getUnionHistory() {
		$this->load->model('Utils_model', 'utilsmodel');
		$data = $this->ProcessInputData('getUnionHistory', true);
		if ($data) {
			$response = $this->utilsmodel->getUnionHistory($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение записи таблицы
	 */
	public function getTableData() {
		$data = $this->ProcessInputData('getTableData', true);
		if ($data === false) { return false; }

		if ( !isSuperAdmin() ) {
			echo "Функционал недоступен";
			return false;
		}

		$this->load->model('SysObjects_model');
		$response = $this->SysObjects_model->table($data['table'])->schema($data['schema'])->id($data['id'])->convertDates(true)->processData()->result();

		if ( !empty($data['returnHTML']) ) {
			if ( !is_array($response) ) {
				echo $response;
				return false;
			}

			foreach ( $response as $row ) {
				foreach ( $row as $field ) {
					echo "<div>";

					if ( !empty($field['description']) ) {
						echo $field['description'], " (", $field['field'], ")";
					}
					else {
						echo $field['field'];
					}

					echo ": ", $field['value'];

					if ( !empty($field['nameValue']) ) {
						echo " (", $field['nameValue'], ")";
					}

					echo "</div>";
				}
			}
		}
		else {
			$this->ProcessModelList(is_array($response) ? $response[0] : $response, true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Запись лога открытия клиентских форм
	 */
	function saveLog() {
		$this->load->library('textlog', array('file'=>'client_FormOpen_'.date('Y-m-d').'.log'));
		if (isset($_REQUEST['log'])) {
			$this->textlog->add($_REQUEST['log']);
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('success'=>false));
		}
		return true;
	}

	/**
	 *	Функция читает ветку справочника, указанного в $data['object']
	 */
	function getSelectionTreeData() {
		$this->load->model('Utils_model', 'utilsmodel');

		$data = $this->ProcessInputData('getSelectionTreeData', true);
		if ( $data === false ) { return false; }
		
		$response = $this->utilsmodel->getSelectionTreeData($data);
		$this->ProcessModelList($response, true, true);

		// Обработка для дерева 
		$field = array(
			'object' => $data['object'],
			'showCodeMode' => $data['showCodeMode'],
			'id' => "id",
			'name' => "name",
			'code' => "code",
			'Sub_SysNick' => "Sub_SysNick",
			'iconCls' => 'uslugacomplex-16',
			'leaf' => false,
			'cls' => "folder"
		);

		$this->ReturnData($this->getTreeNodes($this->OutData, $field));

		return true;
	}

	/**
	 *	Функция получает список идентификаторов родительских узлов для выбранного значения справочника, указанного в $data['object']
	 */
	function getParentNodeList() {
		$this->load->model('Utils_model', 'utilsmodel');

		$data = $this->ProcessInputData('getParentNodeList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->utilsmodel->getParentNodeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *	Функция получает полное наименование для выбранного значения справочника, указанного в $data['object']
	 */
	function getObjectNameWithPath() {
		$this->load->model('Utils_model', 'utilsmodel');

		$data = $this->ProcessInputData('getObjectNameWithPath', true);
		if ( $data === false ) { return false; }
		
		$response = $this->utilsmodel->getObjectNameWithPath($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *	Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes, $field) {
		$i = 0;
		$val = array();

		if ( is_array($nodes) && count($nodes) > 0 ) {
			foreach ( $nodes as $row ) {
				if ( empty($row['childrenCnt']) ) {
					$field['leaf'] = true;
				} else {
					$field['leaf'] = false;
				}

				$node = array(
					'id' => $row[$field['id']],
					'text' => $row[$field['name']],
					'Sub_SysNick' => $row[$field['Sub_SysNick']],
					'object' => $field['object'],
					'object_id' => $field['id'],
					'object_value' => $row[$field['id']],
					'object_code' => $row[$field['code']],
					'leaf' => $field['leaf'],
					'iconCls' => (empty($row['iconCls']) ? $field['iconCls'] : $row['iconCls']),
					'cls' => $field['cls']
				);

				if ( !empty($field['showCodeMode']) && !empty($row[$field['code']]) ) {
					switch ( $field['showCodeMode'] ) {
						case 1:
							$node['text'] = $row[$field['code']] . ". " . $node['text'];
						break;
						
						case 2:
							$node['text'] .= " (" . $row[$field['code']] . ")";
						break;
					}
				}

				$val[] = $node;
			}
		}

		return $val;
	}


	/**
	 * Получение данных для формы поиска
	 */
	function getObjectSearchData() {
		$this->load->model('Utils_model', 'dbmodel');

		$data = $this->ProcessInputData('getObjectSearchData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getObjectSearchData($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	 * Конвертация старых кодов в новые
	 * @task https://redmine.swan.perm.ru/issues/51359
	 */
	function convertMedSpecOmsCodes() {
		$this->load->model('Utils_model', 'dbmodel');

		$data = $this->ProcessInputData('convertMedSpecOmsCodes', true);
		if ($data === false) { return false; }

		/*if ( !isSuperadmin() ) {
			echo 'Доступ запрещен!';
			return false;
		}*/

		$response = $this->dbmodel->convertMedSpecOmsCodes($data);

		if ( $response === false ) {
			echo 'Ошибка при конвертации данных!';
		}
		else {
			echo $response;
		}

		return true;
	}


	/**
	*  Загрузка файла на сервер, c возвратом пути к файлу. Используется при импорте больших файлов, т.к. при загрузке вызывается стандартный сабмит и при больших файлах запрос обрывается через 5 минут.
	*  Входящие данные: файл реестра
	*  На выходе: JSON-строка
	*/
	function doFileUpload() {
        $data = $this->ProcessInputData('doFileUpload', true);
        if ($data === false) { return false; }

        set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = !empty($data['AllowedTypes'])?explode('|',$data['AllowedTypes']):explode('|','dbf');

		if (!isset($_FILES[$data['UploadFieldName']])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Не выбран файл для импорта.')));
			return false;
		}

		if (!is_uploaded_file($_FILES[$data['UploadFieldName']]['tmp_name'])) {
			$error = (!isset($_FILES[$data['UploadFieldName']]['error'])) ? 4 : $_FILES[$data['UploadFieldName']]['error'];
			switch($error) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => iconv('windows-1251', 'utf-8', $message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES[$data['UploadFieldName']]['name']);
		$file_data['file_ext'] = end($x);

		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => 'Данный тип файла не разрешен.'));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
        {
            mkdir( $upload_path );
        }

		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => iconv('windows-1251', 'utf-8', 'Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		if (!file_exists($upload_path.$_FILES[$data['UploadFieldName']]["name"])){
			$zip = new ZipArchive;
			if ($zip->open($_FILES[$data['UploadFieldName']]["tmp_name"]) === TRUE)
			{
				$zip->extractTo( $upload_path );
				$zip->close();
			} else {
				copy($_FILES[$data['UploadFieldName']]["tmp_name"], $upload_path.$_FILES[$data['UploadFieldName']]["name"]);
			}
		}

		$dbffile = $_FILES['DrugsFile']['name'];
		$this->ReturnData(array('success' => true,'filePath' => $upload_path.$dbffile));
		return true;
    }
	/**
	*  Загрузка данных из файла
	*  Входящие данные: Txt файл, содержащий номера кард вызовов 110/у
	*  На выходе: JSON-строка
	*  Используется: в swSmoCallCardWindow
	*/
	function doTxtFileUpload() {
		$data = $this->ProcessInputData('doFileUpload', true);
		if ($data === false) {
			return false;
		}
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = !empty($data['AllowedTypes'])?explode('|',$data['AllowedTypes']):explode('|','txt');
		if (!isset($_FILES[$data['UploadFieldName']])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' =>'windows-1251', 'utf-8', 'Не выбран файл для импорта.'));
			return false;
		}

		if (!is_uploaded_file($_FILES[$data['UploadFieldName']]['tmp_name'])) {
			$error = (!isset($_FILES[$data['UploadFieldName']]['error'])) ? 4 : $_FILES[$data['UploadFieldName']]['error'];
			switch($error) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' =>  $message));
			return false;
		}
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES[$data['UploadFieldName']]['name']);
		$file_data['file_ext'] = end($x);

		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => 'Данный тип файла не разрешен.'));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
                {
            mkdir( $upload_path );
        }

		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => 'windows-1251', 'utf-8', 'Путь для загрузки файлов некорректен.'));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => 'windows-1251', 'utf-8', 'Загрузка файла не возможна из-за прав пользователя.'));
			return false;
		}

		if (!file_exists($upload_path.$_FILES[$data['UploadFieldName']]["name"])){
			$zip = new ZipArchive;
			if ($zip->open($_FILES[$data['UploadFieldName']]["tmp_name"]) === TRUE)
			{
				$zip->extractTo( $upload_path );
				$zip->close();
			} 
			else 
			{
				copy($_FILES[$data['UploadFieldName']]["tmp_name"], $upload_path.$_FILES[$data['UploadFieldName']]["name"]);
			}
		}

		$file = $_FILES['CallCardsFile']['name'];
		
		$valid = true;
		$context = null;
		$mask = "/[0-9]{14}\_[0-9]{1,7}\_[0-9]{1,7}$/i";
		if(file_exists ( $upload_path.$file )){
			$context = array_map('trim', file($upload_path.$file));
			unlink($upload_path.$file);
		}
		if(empty($context)){
			$valid = false;
		}
		else {
			foreach($context as $cardNumber){
				if(preg_match_all($mask,$cardNumber) == 0){
					$valid = false;
					break;
				}
			}
		}
		if($valid)
			$this->ReturnData(array('success' => true, 'cardNumbers' => $context));
		else
			$this->ReturnData(array('success' => false, 'Error_Code' => 1 , 'Error_Msg' => 'Номера карт должны быть в формате ХХХХХХХХХХХХХХ_ХХХХХХ_Х.'));	
		return true;
	}

	/**
	 * Проверка, установлен ли для данной мед. службы флаг "файловая интеграция"
	 */
	function withFileIntegration()
	{
		$data = $this->ProcessInputData('withFileIntegration', true);
		if ($data === false) {return false;}

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('Utils/withFileIntegration', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->withFileIntegration($data);			
			$this->ProcessModelList(array('success' => $response), true, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Проверка соединения с основной БД
	*/
	function checkMainDBConnection()
	{
		try {
			$this->load->database();
			$res = [
				'success' => true
			];
			$this->ProcessModelSave($res, true)->ReturnData();
		} catch (Exception $e) {
			$res = [
				'success' => false,
				'Error_Msg' => json_encode($e)
			];
			$this->ProcessModelSave($res, true, 'Ошибка подключения к основной БД')->ReturnData();
		}
	}

	/**
	 * Получение PayType_id ОМС
	 */
	function getOMSId()
	{
		$this->load->model("Utils_model", "umodel");

		$resp = $this->umodel->getOMSId();
		$this->ProcessModelList($resp, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных о состоянии репликации и актуальности данных
	 */
	public function getReplicationInfo() {
		$this->load->model("Utils_model", "model");
		$data = $this->ProcessInputData('getReplicationInfo', true);
		if ($data === false) { return false; }
		$resp = $this->model->getReplicationInfo($data);
		$this->ReturnData($resp);
		return true;
	}
}
