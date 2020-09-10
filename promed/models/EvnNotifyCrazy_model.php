<?php
/**
* EvnNotifyCrazy_model - модель для работы с таблицей EvnNotifyCrazy
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
class EvnNotifyCrazy_model extends swModel
{
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyCrazy';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'crazy';
	}

	/**
	 * @return int
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
	function checkEvnNotifyCrazy($data)
	{
		$tableName = $this->tableName();
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended($this->morbusTypeSysNick, $data['Person_id'], null,"
				,EN.{$tableName}_id as EvnNotifyBase_id
				,PR.PersonRegister_id
				,PR.PersonRegisterOutCause_id" ,"
				left join v_{$tableName} EN with (nolock) on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = Morbus.Morbus_id"
		);
	}

	/**
	 * Method description
	 */
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyCrazy_id,
				ENO.EvnNotifyCrazy_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				Diag.Diag_id,
				Diag.Diag_FullName as Diag_Name,
				convert(varchar,ENO.EvnNotifyCrazy_setDT,104) as EvnNotifyCrazy_setDT,
				ENO.MedPersonal_id
			from
				v_EvnNotifyCrazy ENO with (nolock)
				left join v_EvnVizitPL PL with (nolock) on ENO.EvnNotifyCrazy_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST with (nolock) on ENO.EvnNotifyCrazy_pid = ST.EvnSection_id
				inner join v_Diag Diag with (nolock) on ISNULL(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
			where
				ENO.EvnNotifyCrazy_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyCrazy_id']));
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
			if (empty($data['EvnNotifyCrazy_setDT'])) {
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
				'EvnNotifyCrazy_setDT' => $data['EvnNotifyCrazy_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],
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
				'EvnNotifyBase_setDate' => $data['EvnNotifyCrazy_setDT'],
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
	 * Method description
	 */
	function del($data)
	{
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnNotifyCrazy_id;
			exec p_EvnNotifyCrazy_del
				@EvnNotifyCrazy_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyCrazy_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyCrazy_id' => $data['EvnNotifyCrazy_id'],
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
