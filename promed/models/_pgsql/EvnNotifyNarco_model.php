<?php
/**
* EvnNotifyNarco_model - модель для работы с таблицей EvnNotifyNarco
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
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
class EvnNotifyNarco_model extends SwPgModel
{
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyNarco';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'narc';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		return 4; //для всех регионов
	}

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Проверка наличия извещения
	* Проверка выполняется из Common_model->signedDocument
	*/
	function checkEvnNotifyNarco($data)
	{
		$tableName = $this->tableName();
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended($this->morbusTypeSysNick, $data['Person_id'], null,"
				,EN.{$tableName}_id as \"EvnNotifyBase_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"" ,"
				left join v_{$tableName} EN on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR on PR.Morbus_id = Morbus.Morbus_id"
		);
	}
	
	/**
	 * @param array $data
	 * @return array|bool
	 */
	function load($data)
	{
		$params = array();
		if (isset($data['EvnNotifyNarco_id'])) {
			$params['EvnNotifyNarco_id'] = $data['EvnNotifyNarco_id'];
			$query = '
				select
					ENO.EvnNotifyNarco_id as "EvnNotifyNarco_id",
					ENO.EvnNotifyNarco_pid as "EvnNotifyNarco_pid",
					ENO.Morbus_id as "Morbus_id",
					ENO.Server_id as "Server_id",
					ENO.PersonEvn_id as "PersonEvn_id",
					ENO.Person_id as "Person_id",
					ENO.NarcoReceiveType_id as "NarcoReceiveType_id",
					ENO.NarcoTreatInitiate_id as "NarcoTreatInitiate_id",
					ENO.NarcoUseType_id as "NarcoUseType_id",
					ENO.EvnNotifyNarco_NarcoDate as "EvnNotifyNarco_NarcoDate",
					ENO.EvnNotifyNarco_NarcoName as "EvnNotifyNarco_NarcoName",
					ENO.Diag_sid as "Diag_sid",
					ENO.Diag_id as "Diag_id",
					ENO.EvnNotifyNarco_JobPlace as "EvnNotifyNarco_JobPlace",
					ENO.Post_id as "Post_id",
					ENO.Lpu_id as "Lpu_id",
					Diag.Diag_id as "Diag_id",
					Diag.Diag_FullName as Diag_Name,
					to_char(ENO.EvnNotifyNarco_setDT,\'dd.mm.yyyy\') as "EvnNotifyNarco_setDT",
					ENO.MedPersonal_id as "MedPersonal_id"
				from
					v_EvnNotifyNarco ENO
					left join v_EvnVizitPL PL on ENO.EvnNotifyNarco_pid = PL.EvnVizitPL_id
					left join v_EvnSection ST on ENO.EvnNotifyNarco_pid = ST.EvnSection_id
					inner join v_Diag Diag on COALESCE(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				where
					ENO.EvnNotifyNarco_id = :EvnNotifyNarco_id
			';
		} else if (isset($data['EvnNotifyNarco_pid'])) {
			$params['EvnNotifyNarco_pid'] = $data['EvnNotifyNarco_pid'];
			$query = '
				select
					null as "EvnNotifyNarco_id",
					ENO.Evn_id as "EvnNotifyNarco_pid",
					ENO.Morbus_id as "Morbus_id",
					PS.Server_id as "Server_id",
					PS.PersonEvn_id as "PersonEvn_id",
					PS.Person_id as "Person_id",
					null as "NarcoReceiveType_id",
					null as "NarcoTreatInitiate_id",
					null as "NarcoUseType_id",
					extract(year from ENO.Evn_setDT) as "EvnNotifyNarco_NarcoDate",
					null as "EvnNotifyNarco_NarcoName",
					RTRIM(PJ.Org_Nick) as "EvnNotifyNarco_JobPlace",
					mop.OnkoOccupationClass_id as "Post_id",
					ENO.Lpu_id as "Lpu_id",
					Diag.Diag_id as "Diag_id",
					Diag.Diag_id as "Diag_sid",
					Diag.Diag_FullName as "Diag_Name",
					null as "EvnNotifyNarco_setDT",
					null as "MedPersonal_id"
				from
					v_Evn ENO
					left join EvnVizitPL PL on ENO.Evn_id = PL.Evn_id
					left join EvnSection ST on ENO.Evn_id = ST.Evn_id
					inner join v_Diag Diag on COALESCE(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
					LEFT JOIN LATERAL (
						select OnkoOccupationClass_id as OnkoOccupationClass_id
						from v_MorbusOnkoPerson
						where Person_id = ENO.Person_id
						order by MorbusOnkoPerson_insDT desc
						limit 1
					) as mop ON TRUE
					inner join v_PersonState PS on PS.Person_id = ENO.Person_id
					left join v_Job Job on Job.Job_id = PS.Job_id
					left join Org PJ on PJ.Org_id = Job.Org_id
				where
					ENO.Evn_id = :EvnNotifyNarco_pid
			';
		}
		if (empty($query) || empty($params)) {
			return false;
		}
		$res = $this->db->query($query, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}

	/**
	 * Сохранение объекта
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
			if (empty($data['Person_id']) || empty($data['PersonEvn_id']) || false == isset($data['Server_id'])) {
				throw new Exception('Не переданы параметры человека', 500);
			}
			if (empty($data['Lpu_id'])) {
				throw new Exception('Не передан параметр МО пользователя', 500);
			}
			if (empty($data['MedPersonal_id'])) {
				throw new Exception('Не передан параметр Врач, заполнивший извещение', 500);
			}
			if (empty($data[$tableName . '_pid'])) {
				throw new Exception('Не передан параметр Учетный документ', 500);
			}
			if (empty($data['EvnNotifyNarco_setDT'])) {
				throw new Exception('Не передан параметр Дата заполнения извещения', 500);
			}

			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session']);
			$data['MorbusType_id'] = $tmp['MorbusType_id'];
			$data['Morbus_id'] = $tmp['Morbus_id'];
			$data['Morbus_Diag_id'] = $tmp['Diag_id'];

			$queryParams = array(
				$tableName . '_pid' => $data[$tableName . '_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'EvnNotifyNarco_setDT' => $data['EvnNotifyNarco_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Diag_id' => $data['Diag_id'],
				'Diag_sid' => $data['Diag_sid'],
				'NarcoReceiveType_id' => $data['NarcoReceiveType_id'],
				'NarcoTreatInitiate_id' => $data['NarcoTreatInitiate_id'],
				'NarcoUseType_id' => $data['NarcoUseType_id'],
				'EvnNotifyNarco_NarcoDate' =>$data['EvnNotifyNarco_NarcoDate'],
				'EvnNotifyNarco_NarcoName' => $data['EvnNotifyNarco_NarcoName'],
				'EvnNotifyNarco_JobPlace' => $data['EvnNotifyNarco_JobPlace'],
				'Post_id' => $data['Post_id'],
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
			$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
				'EvnNotifyBase_id' => $data[$pk],
				'EvnNotifyBase_pid' => $data[$tableName . '_pid'],
				'EvnNotifyBase_setDate' => $data['EvnNotifyNarco_setDT'],
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
	 * @param array $data
	 * @return array|bool
	 */
	function del($data)
	{
		$query = '
			select
			    EvnNotifyNarco_id as "EvnNotifyNarco_id",
			    Error_Code as "Error_Code",
			    Error_Message as "Error_Msg"
			from p_EvnNotifyNarco_del (
				EvnNotifyNarco_id := :EvnNotifyNarco_id,
				pmUser_id := :pmUser_id
				)
		';
		
		$queryParams = array(
			'EvnNotifyNarco_id' => $data['EvnNotifyNarco_id'],
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
}
