<?php
/**
* EvnNotifyVener_model - модель для работы с таблицей EvnNotifyVener
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

class EvnNotifyVener_model extends SwPgModel
{
	protected $_MorbusType_id = null;
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyVener';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'vener';
	}

	/**
	 * Определение типа заболевания
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->morbusTypeSysNick);
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Проверка наличия извещения и записи регистра
	 * Для заболеваний с диагнозами В35.0-В35.9 и В86 включение в регистр производит оператор
	 * Для остальных венерозаболеваний человек включается в регистр автоматически при создании извещения
	 * и для каждого венерозаболевания человека должна быть своя запись регистра (неважно есть или нет извещение)
	 */
	function checkEvnNotifyVener($data)
	{
		$tableName = 'EvnNotifyVener';
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended('vener', $data['Person_id'], $data['Diag_id'],"
				,coalesce(PR.EvnNotifyBase_id, EN.EvnNotifyVener_id) as \"EvnNotifyBase_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"" ,"
				left join v_{$tableName} EN on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR on PR.Morbus_id = Morbus.Morbus_id"
		);
	}
	
	/**
	 * Method description
	 */
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyVener_id as "EvnNotifyVener_id",
				ENO.EvnNotifyVener_pid as "EvnNotifyVener_pid",
				ENO.Morbus_id as "Morbus_id",
				ENO.Server_id as "Server_id",
				ENO.PersonEvn_id as "PersonEvn_id",
				ENO.Person_id as "Person_id",
				Diag.Diag_id as "Diag_id",
				Diag.Diag_FullName as "Diag_Name",
				to_char(ENO.EvnNotifyVener_setDT,\'dd.mm.yyyy\') as "EvnNotifyVener_setDT",
				ENO.MedPersonal_id as "MedPersonal_id",
				ENO.PersonCategoryType_id as "PersonCategoryType_id",
				ENO.EvnNotifyVener_OtherPersonCategory as "EvnNotifyVener_OtherPersonCategory",
				to_char(ENO.EvnNotifyVener_DiagDT,\'dd.mm.yyyy\') as "EvnNotifyVener_DiagDT",
				ENO.EvnNotifyVener_IsReInfect as "EvnNotifyVener_IsReInfect",
				ENO.VenerPathTransType_id as "VenerPathTransType_id",
				ENO.VenerPregPeriodType_id as "VenerPregPeriodType_id",
				ENO.VenerLabConfirmType_id as "VenerLabConfirmType_id",
				ENO.EvnNotifyVener_OtherLabConfirm as "EvnNotifyVener_OtherLabConfirm",
				ENO.VenerDetectionPlaceType_id as "VenerDetectionPlaceType_id",
				ENO.LpuSectionProfile_id as "LpuSectionProfile_id",
				ENO.MedPersonal_pid as "MedPersonal_pid",
				ENO.EvnNotifyVener_OtherDetectPlace as "EvnNotifyVener_OtherDetectPlace",
				ENO.VenerDetectionFactType_id as "VenerDetectionFactType_id",
				ENO.MedPersonal_fid as "MedPersonal_fid",
				ENO.EvnNotifyVener_OtherDetectFact as "EvnNotifyVener_OtherDetectFact",
				ENO.EvnNotifyVener_IsIFA as "EvnNotifyVener_IsIFA",
				ENO.EvnNotifyVener_IsImmun as "EvnNotifyVener_IsImmun",
				ENO.EvnNotifyVener_IsKSR as "EvnNotifyVener_IsKSR",
				ENO.EvnNotifyVener_IsRIBT as "EvnNotifyVener_IsRIBT",
				ENO.EvnNotifyVener_IsRIF as "EvnNotifyVener_IsRIF",
				ENO.EvnNotifyVener_IsRMP as "EvnNotifyVener_IsRMP",
				ENO.EvnNotifyVener_IsRPGA as "EvnNotifyVener_IsRPGA",
				ENO.EvnNotifyVener_IsRPR as "EvnNotifyVener_IsRPR",
				ENO.EvnNotifyVener_IsTPM as "EvnNotifyVener_IsTPM",
				ENO.VenerSocGroup_id as "VenerSocGroup_id",
				ENO.EvnNotifyVener_Pathogen as "EvnNotifyVener_Pathogen"
			from v_EvnNotifyVener ENO
				left join v_EvnVizitPL PL on ENO.EvnNotifyVener_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST on ENO.EvnNotifyVener_pid = ST.EvnSection_id
				inner join v_Diag Diag on COALESCE(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
			where
				ENO.EvnNotifyVener_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyVener_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Сохранение объекта «Извещение» с типом «Гепатит».
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
			if (empty($data['EvnNotifyVener_setDT'])) {
				throw new Exception('Не передан параметр Дата заполнения извещения', 500);
			}

			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session']);
			if (!empty($tmp['disableAddEvnNotify'])) {
				throw new Exception('По данному заболеванию извещение было создано ранее', 400);
			}
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
				'EvnNotifyVener_setDT' => $data['EvnNotifyVener_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Diag_id' => $data['Diag_id'],
				'PersonCategoryType_id' => $data['PersonCategoryType_id'],
				'EvnNotifyVener_OtherPersonCategory' => $data['EvnNotifyVener_OtherPersonCategory'],
				'EvnNotifyVener_DiagDT' => $data['EvnNotifyVener_DiagDT'],
				'EvnNotifyVener_IsReInfect' => $data['EvnNotifyVener_IsReInfect'],
				'VenerPathTransType_id' => $data['VenerPathTransType_id'],
				'VenerPregPeriodType_id' => $data['VenerPregPeriodType_id'],
				'VenerLabConfirmType_id' => $data['VenerLabConfirmType_id'],
				'EvnNotifyVener_OtherLabConfirm' => $data['EvnNotifyVener_OtherLabConfirm'],
				'VenerDetectionPlaceType_id' => $data['VenerDetectionPlaceType_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'MedPersonal_pid' => $data['MedPersonal_pid'],
				'EvnNotifyVener_OtherDetectPlace' => $data['EvnNotifyVener_OtherDetectPlace'],
				'VenerDetectionFactType_id' => $data['VenerDetectionFactType_id'],
				'MedPersonal_fid' => $data['MedPersonal_fid'],
				'EvnNotifyVener_OtherDetectFact' => $data['EvnNotifyVener_OtherDetectFact'],
				'EvnNotifyVener_IsIFA' => (!empty($data['EvnNotifyVener_IsIFA'])) ? $data['EvnNotifyVener_IsIFA'] : null,
				'EvnNotifyVener_IsImmun' => (!empty($data['EvnNotifyVener_IsImmun'])) ? $data['EvnNotifyVener_IsImmun'] : null,
				'EvnNotifyVener_IsKSR' => (!empty($data['EvnNotifyVener_IsKSR'])) ? $data['EvnNotifyVener_IsKSR'] : null,
				'EvnNotifyVener_IsRIBT' => (!empty($data['EvnNotifyVener_IsRIBT'])) ? $data['EvnNotifyVener_IsRIBT'] : null,
				'EvnNotifyVener_IsRIF' => (!empty($data['EvnNotifyVener_IsRIF'])) ? $data['EvnNotifyVener_IsRIF'] : null,
				'EvnNotifyVener_IsRMP' => (!empty($data['EvnNotifyVener_IsRMP'])) ? $data['EvnNotifyVener_IsRMP'] : null,
				'EvnNotifyVener_IsRPGA' => (!empty($data['EvnNotifyVener_IsRPGA'])) ? $data['EvnNotifyVener_IsRPGA'] : null,
				'EvnNotifyVener_IsRPR' => (!empty($data['EvnNotifyVener_IsRPR'])) ? $data['EvnNotifyVener_IsRPR'] : null,
				'EvnNotifyVener_IsTPM' => (!empty($data['EvnNotifyVener_IsTPM'])) ? $data['EvnNotifyVener_IsTPM'] : null,
				'VenerSocGroup_id' => (!empty($data['VenerSocGroup_id'])) ? $data['VenerSocGroup_id'] : null,
				'EvnNotifyVener_Pathogen' => (!empty($data['EvnNotifyVener_Pathogen'])) ? $data['EvnNotifyVener_Pathogen'] : null
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
				'EvnNotifyBase_setDate' => $data['EvnNotifyVener_setDT'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Person_id' => $data['Person_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Morbus_Diag_id' => $data['Diag_id'],
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
	 * Method description
	 */
	function del($data)
	{
		$query = '
			select
			    EvnNotifyVener_id as "EvnNotifyVener_id",
			    Error_Code as "Error_Code",
			    Error_Message as "Error_Msg"
			from p_EvnNotifyVener_del (
				EvnNotifyVener_id := :EvnNotifyVener_id,
				pmUser_id := :pmUser_id
			)
		';
		
		$queryParams = array(
			'EvnNotifyVener_id' => $data['EvnNotifyVener_id'],
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
