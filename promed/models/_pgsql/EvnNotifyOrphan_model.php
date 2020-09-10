<?php
/**
* EvnNotifyOrphan_model - модель для работы с направлениями,извещениями орфанных заболеваний
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* EvnNotifyOrphan - Направление на включение в регистр
* EvnDirectionOrphan - Направление на внесение изменений в регистр
* EvnNotifyOrphanOut - Извещение на исключение из регистра
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Permyakov 
* @version      10.2012
 *
 * Магические свойства
 * @property-read int $MorbusType_id
 * @property-read string $morbusTypeSysNick
 *
 * @todo extends EvnNotifyAbstract_model
 */

class EvnNotifyOrphan_model extends swModel
{
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyOrphan';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'orphan';
	}

	/**
	 * Определение типа заболевания
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		return 6; // для всех регионов
	}

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Проверка наличия направления на включение в регистр
	 */
	function checkEvnNotifyOrphan($data)
	{
		$tableName = 'EvnNotifyOrphan';
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended('orphan', $data['Person_id'], null,"
				,EN.{$tableName}_id as \"EvnNotifyBase_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"" ,"
				left join v_{$tableName} EN  on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR  on PR.Morbus_id = Morbus.Morbus_id"
		);
	}
	
	/**
	 * Чтение направления на включение в регистр
	 */
	function load($data)
	{
		$query = "
                select
				    ENO.EvnNotifyOrphan_id as \"EvnNotifyOrphan_id\",
				    ENO.EvnNotifyOrphan_pid as \"EvnNotifyOrphan_pid\",
				    ENO.Morbus_id as \"Morbus_id\",
				    ENO.Server_id as \"Server_id\",
				    ENO.PersonEvn_id as \"PersonEvn_id\",
				    ENO.Person_id as \"Person_id\",
				    Diag.Diag_id as \"Diag_id\",
				    Diag.Diag_FullName as \"Diag_Name\",
				    ENO.Lpu_oid as \"Lpu_oid\",
				    to_char(ENO.EvnNotifyOrphan_setDT,'DD.MM.YYYY') as \"EvnNotifyOrphan_setDT\",
				    ENO.MedPersonal_id as \"MedPersonal_id\"
			    from
				    v_EvnNotifyOrphan ENO 
				    left join v_EvnVizitPL PL  on ENO.EvnNotifyOrphan_pid = PL.EvnVizitPL_id
				    left join v_EvnSection ST  on ENO.EvnNotifyOrphan_pid = ST.EvnSection_id
				    inner join v_Diag Diag  on Coalesce(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
			    where
				    ENO.EvnNotifyOrphan_id = ?
		    ";
		$res = $this->db->query($query, array($data['EvnNotifyOrphan_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Сохранение направления на включение в регистр
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * Если массив не передается, то ранее должны быть установлены данные
	 * с помощью метода applyData($data) или методов setParams($data) и setAttributes($data)
	 * Также должен быть указан сценарий бизнес-логики с помощью метода setScenario
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Ответ модели в формате ассоциативного массива,
	 * пригодном для обработки методом ProcessModelSave контроллера
	 * Обязательно должны быть ключи: Error_Msg и идешник объекта
	 */
	public function doSave($data = array(), $isAllowTransaction = true)
	{
		try {
			$tableName = $this->tableName();
			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}

			//$this->_beforeSave($data);
			if (empty($data)) {
				throw new Exception('Не переданы параметры', 500);
			}
			if (!empty($data['scenario'])) {
				$this->setScenario($data['scenario']);
			}
			$this->setParams($data);
			//$this->setAttributes($data);
			//$this->_validate();
			if (in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE,self::SCENARIO_DO_SAVE,'onAddPersonRegister'))) {
				if (empty($data['Person_id']) || empty($data['PersonEvn_id']) || false == isset($data['Server_id'])) {
					throw new Exception('Не переданы параметры человека', 500);
				}
				if (empty($data['Lpu_id'])) {
					throw new Exception('Не передан параметр МО пользователя', 500);
				}
				if (empty($data['MedPersonal_id'])) {
					throw new Exception('Не передан параметр Врач, заполнивший извещение', 500);
				}
				if (empty($data['EvnNotifyOrphan_setDT'])) {
					throw new Exception('Не передан параметр Дата заполнения извещения', 500);
				}
			}
			if (in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE,self::SCENARIO_DO_SAVE))) {
				if (empty($data[$tableName . '_pid'])) {
					throw new Exception('Не передан параметр Учетный документ', 500);
				}
			}
			if (in_array($this->scenario, array('onAddPersonRegister'))) {
				$data['Lpu_oid'] = null;
				$data[$tableName . '_pid'] = null;
				if (empty($data['Morbus_id'])) {
					throw new Exception('Не передан параметр Заболевание', 500);
				}
				if (empty($data['MorbusType_id'])) {
					throw new Exception('Не передан параметр Тип заболевания', 500);
				}
			}

			if (in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE,self::SCENARIO_DO_SAVE))) {
				$this->load->library('swMorbus');
				$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session']);
				$data['MorbusType_id'] = $tmp['MorbusType_id'];
				$data['Morbus_id'] = $tmp['Morbus_id'];
				$data['Morbus_Diag_id'] = $tmp['Diag_id'];
			}

			$queryParams = array(
				$tableName . '_pid' => $data[$tableName . '_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'EvnNotifyOrphan_setDT' => $data['EvnNotifyOrphan_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],

				'Lpu_oid' => $data['Lpu_oid'],
			);
			$pk = $this->primaryKey();
			$queryParams[$pk] = array(
				'value' => empty($data[$pk]) ? null : $data[$pk],
				'out' => true,
				'type' => 'bigint',
			);
			$queryParams['pmUser_id'] = $this->promedUserId;
			$tmp = $this->_save($queryParams);
			//$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$data[$pk] = $tmp[0][$pk];

			//$this->_afterSave($tmp);
			if (in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE,self::SCENARIO_DO_SAVE))) {
				$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
					'EvnNotifyBase_id' => $data[$pk],
					'EvnNotifyBase_pid' => $data[$tableName . '_pid'],
					'EvnNotifyBase_setDate' => $data['EvnNotifyOrphan_setDT'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'Person_id' => $data['Person_id'],
					'Morbus_id' => $data['Morbus_id'],
					'MorbusType_id' => $data['MorbusType_id'],
					'Morbus_Diag_id' => $data['Morbus_Diag_id'],
					'Lpu_id' => $data['Lpu_id'],
					'MedPersonal_id' => $data['MedPersonal_id'],
					'session' => $this->sessionParams
				));
				$this->_saveResponse = array_merge($this->_saveResponse, $tmp);
			}

			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
			//$this->_saveResponse[$this->primaryKey(true)] = $this->id;
			$this->_saveResponse[$pk] = $data[$pk];
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			if ($this->isDebug && $e->getCode() == 500) {
				// только на тестовом и только, если что-то пошло не так
				$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_onSave();
		return $this->_saveResponse;
	}
	
	/**
	 * Удаление направления на включение в регистр
	 */
   function del($data)
	{
 
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            :EvnNotifyOrphan_id as \"EvnNotifyOrphan_id\"
        from p_EvnNotifyOrphan_del
            (
 				EvnNotifyOrphan_id := :EvnNotifyOrphan_id,
				pmUser_id := :pmUser_id
            )";


		$queryParams = array(
			'EvnNotifyOrphan_id' => $data['EvnNotifyOrphan_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* Проверка/получение врача по основному прикреплению пациента.
	*/
	function medpersonalBaseAttach($data)
	{
		$filters = '';
		$params = array('Person_id'=>$data['Person_id']);
		if(isset($data['MedPersonal_id']))
		{
			$filters .= 'and MSR.MedPersonal_id = :MedPersonal_id';
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		$query = "
			select 
				PC.Lpu_id as \"Lpu_id\",
				PC.LpuRegion_id as \"LpuRegion_id\",
				MSR.MedPersonal_id as \"MedPersonal_id\"
			from v_PersonCard PC 
			left join v_MedStaffRegion MSR  on MSR.LpuRegion_id = PC.LpuRegion_id and MSR.Lpu_id = PC.Lpu_id
			where
				PC.Person_id = :Person_id and PC.LpuAttachType_id = 1 and PC.PersonCard_endDate is null
				{$filters}
            limit 1
		";
		$res = $this->db->query($query, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return array();
	}
	
	/**
	* Получение данных из регистра для извещений/направлений
	*/
	function loadMorbusOrphanData($data)
	{
		$query = "
			select 
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				M.Morbus_id as \"Morbus_id\",
				MO.MorbusOrphan_id as \"MorbusOrphan_id\",
				M.Diag_id as \"Diag_id\"
			from v_PersonState PS 
			inner join v_Morbus M  on M.Person_id = PS.Person_id
			inner join v_MorbusOrphan MO  on M.Morbus_id = MO.Morbus_id
			where
				PS.Person_id = :Person_id
			order by M.Morbus_disDT ASC, M.Morbus_setDT DESC
            limit 1
		";
		$res = $this->db->query($query, array('Person_id'=>$data['Person_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return array();
	}
	
	/**
	* Создание направления на внесение изменений в регистр
	* Обязательные параметры:
	* session
	* Person_id
	* Lpu_id
	* pmUser_id
	*/
	function createEvnDirectionOrphan($data)
	{
		$data['MedPersonal_id'] = isset($data['MedPersonal_id'])?$data['MedPersonal_id']:$data['session']['medpersonal_id'];

		/* при создании направления на внесение изменений в регистр эта проверка вроде не нужна
		if(!isSuperadmin())
		{
        $tmp = $this->medpersonalBaseAttach($data);
        if(empty($tmp)) {
        return array(array('Error_Msg'=>'Пациент не имеет основного типа прикрепления или вы не являетесь врачом по основному прикреплению пациента'));
        }
        $data['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
        $data['Lpu_id'] = $tmp[0]['Lpu_id'];
		}
         */
		if(!isset($data['Server_id']) || empty($data['PersonEvn_id']) ||empty($data['Morbus_id']) ||empty($data['MorbusOrphan_id']) ||empty($data['Diag_id']))
		{
			$tmp = $this->loadMorbusOrphanData($data);
			if(empty($tmp)) {
				return array(array('Error_Msg'=>'Пациент не имеет орфанного заболевания'));
			}
			$data['Server_id'] = $tmp[0]['Server_id'];
			$data['PersonEvn_id'] = $tmp[0]['PersonEvn_id'];
			$data['Morbus_id'] = $tmp[0]['Morbus_id'];
			$data['MorbusOrphan_id'] = $tmp[0]['MorbusOrphan_id'];
			$data['Diag_id'] = $tmp[0]['Diag_id'];
		}



        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            EvnDirectionOrphan_id as \"EvnNotifyOrphan_id\"
        from p_EvnDirectionOrphan_ins
            (
 				EvnDirectionOrphan_id := null,
				EvnDirectionOrphan_pid := :EvnDirectionOrphan_pid,
				--EvnDirectionOrphan_rid := :EvnDirectionOrphan_rid,
				Lpu_id := :Lpu_id, -- required МО создания направления. МО прикрепления
				MedPersonal_id := :MedPersonal_id, -- Врач, заполнивший направление. Врач по основному прикреплению пациента.
				Server_id := :Server_id, -- required
				PersonEvn_id := :PersonEvn_id, -- required
				EvnDirectionOrphan_setDT := :EvnDirectionOrphan_setDT,
				EvnDirectionOrphan_Num := '0', -- required
				Morbus_id := :Morbus_id, -- required
				MorbusOrphan_id := :MorbusOrphan_id, -- required
				Diag_id := :Diag_id,  -- required Диагноз из записи регистра
				pmUser_id := :pmUser_id -- required
            )";


		$queryParams = array(
			'EvnDirectionOrphan_pid' => isset($data['EvnDirectionOrphan_pid'])?$data['EvnDirectionOrphan_pid']:null,
			'EvnDirectionOrphan_rid' => isset($data['EvnDirectionOrphan_rid'])?$data['EvnDirectionOrphan_rid']:null,
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Morbus_id' => $data['Morbus_id'],
			'MorbusOrphan_id' => $data['MorbusOrphan_id'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirectionOrphan_setDT' => isset($data['EvnDirectionOrphan_setDT'])?$data['EvnDirectionOrphan_setDT']:date('Y-m-d'),
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return array(array('Error_Msg'=>'Ошибка БД'));
		}
	}

	/**
	* Создание извещения на исключение из регистра
	* Обязательные параметры:
	* session
	* Person_id
	* Lpu_id
	* pmUser_id
	*/
   function createEvnNotifyOrphanOut($data)
	{
		//var_dump($data);
		$data['MedPersonal_id'] = isset($data['MedPersonal_id'])?$data['MedPersonal_id']:$data['session']['medpersonal_id'];

		if(!isSuperadmin())
		{
			$tmp = $this->medpersonalBaseAttach($data);
			if(empty($tmp)) {
				return array(array('Error_Msg'=>'Пациент не имеет основного типа прикрепления или вы не являетесь врачом по основному прикреплению пациента'));
			}
			$data['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
			$data['Lpu_id'] = $tmp[0]['Lpu_id'];
		}

		if(!isset($data['Server_id']) || empty($data['PersonEvn_id']) ||empty($data['Morbus_id']) ||empty($data['MorbusOrphan_id']) || empty($data['Diag_id']))
		{
			$tmp = $this->loadMorbusOrphanData($data);
			if(empty($tmp)) {
				return array(array('Error_Msg'=>'Пациент не имеет орфанного заболевания'));
			}
			$data['Server_id'] = $tmp[0]['Server_id'];
			$data['PersonEvn_id'] = $tmp[0]['PersonEvn_id'];
			$data['Morbus_id'] = $tmp[0]['Morbus_id'];
			$data['MorbusOrphan_id'] = $tmp[0]['MorbusOrphan_id'];
			$data['Diag_id'] = $tmp[0]['Diag_id'];
		}


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            EvnNotifyOrphanOut_id as \"EvnNotifyOrphan_id\"
        from p_EvnNotifyOrphanOut_ins
            (
 				EvnNotifyOrphanOut_id := null,
				EvnNotifyOrphanOut_pid := :EvnNotifyOrphanOut_pid,
				--EvnNotifyOrphanOut_rid := :EvnNotifyOrphanOut_rid,
				Lpu_id := :Lpu_id, -- required МО создания направления. МО прикрепления
				MedPersonal_id := :MedPersonal_id, -- Врач, заполнивший направление. Врач по основному прикреплению пациента.
				Server_id := :Server_id, -- required
				PersonEvn_id := :PersonEvn_id, -- required
				EvnNotifyOrphanOut_setDT := :EvnNotifyOrphanOut_setDT,
				Morbus_id := :Morbus_id, -- required
				MorbusOrphan_id := :MorbusOrphan_id, -- required
				pmUser_id := :pmUser_id -- required
            )";


		$queryParams = array(
			'EvnNotifyOrphanOut_pid' => isset($data['EvnNotifyOrphanOut_pid'])?$data['EvnNotifyOrphanOut_pid']:null,
			'EvnNotifyOrphanOut_rid' => isset($data['EvnNotifyOrphanOut_rid'])?$data['EvnNotifyOrphanOut_rid']:null,
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnNotifyOrphanOut_setDT' => isset($data['EvnNotifyOrphanOut_setDT'])?$data['EvnNotifyOrphanOut_setDT']:date('Y-m-d'),
			'Morbus_id' => $data['Morbus_id'],
			'MorbusOrphan_id' => $data['MorbusOrphan_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $queryParams);
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return array(array('Error_Msg'=>'Ошибка БД'));
		}
	}

}
