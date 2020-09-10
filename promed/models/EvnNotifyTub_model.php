<?php
/**
* EvnNotifyTub_model - модель для работы с таблицей EvnNotifyTub
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

class EvnNotifyTub_model extends swModel
{
	protected $_MorbusType_id = null;
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyTub';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'tub';
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
	 * Проверка наличия извещения/записи регистра
	 */
	function checkEvnNotifyTub($data)
	{
		$tableName = 'EvnNotifyTub';
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended('tub', $data['Person_id'], null,"
				,EN.{$tableName}_id as EvnNotifyBase_id
				,PR.PersonRegister_id
				,PR.PersonRegisterOutCause_id" ,"
				left join v_{$tableName} EN with (nolock) on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = Morbus.Morbus_id"
		);
	}

	/**
	 * Проверка наличия записи регистра
	 */
	function checkTubRegistryEntry($data)
	{
		$query = "
			select
				PersonRegister_id
			from
				v_PersonRegister with (nolock)
			where
				Person_id = :Person_id 
				and MorbusType_id = 7 -- Туберкулез
				and PersonRegister_disDate is null
		";
		$res = $this->db->query($query, array('Person_id'=>$data['Person_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			return $res;
		} else
			return false;
	}

	/**
	 * Проверка наличия записи регистра
	 */
	function checkHIVRegistryEntry($data)
	{
		$query = "
			select
				PersonRegister_id
			from
				v_PersonRegister with (nolock)
			where
				Person_id = :Person_id 
				and MorbusType_id = 9 -- ВИЧ
		";
		$res = $this->db->query($query, array('Person_id'=>$data['Person_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			return $res;
		} else
			return false;
	}

	/**
	 * Получение записи журнала извещений
	 */
	function getEvnNotifyTub($data)
	{
		$query = "
			select
				EvnNotifyTub_id
			from
				v_EvnNotifyTub ent with (nolock)
			where
				Person_id = :Person_id 
		";
		$res = $this->db->query($query, array('Person_id'=>$data['Person_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			return $res;
		} else
			return false;
	}

	/**
	 * Method description
	 */
	function load($data)
	{
		$query = '
			select
				ENO.EvnNotifyTub_id,
				ENO.EvnNotifyTub_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				Diag.Diag_id,
				Diag.Diag_FullName as Diag_Name,
				convert(varchar,ENO.EvnNotifyTub_setDT,104) as EvnNotifyTub_setDT,
				ENO.PersonCategoryType_id,
				ENO.PersonLivingFacilies_id,
				ENO.EvnNotifyTub_OtherPersonCategory,
				ENO.PersonDecreedGroup_id,
				ENO.EvnNotifyTub_IsDecreeGroup,
				ENO.TubFluorSurveyPeriodType_id,
				ENO.TubDetectionPlaceType_id,
				ENO.EvnNotifyTub_OtherDetectionPlace,
				ENO.DrugResistenceTest_id,
				convert(varchar,ENO.EvnNotifyTub_FirstDT,104) as EvnNotifyTub_FirstDT,
				convert(varchar,ENO.EvnNotifyTub_RegDT,104) as EvnNotifyTub_RegDT,
				ENO.TubDetectionFactType_id,
				ENO.TubSurveyGroupType_id,
				ENO.TubDetectionMethodType_id,
				ENO.EvnNotifyTub_OtherDetectionMethod,
				ENO.TubDiagNotify_id,
				ENO.TubDiagForm8_id,
				ENO.EvnNotifyTub_IsFirstDiag,
				ENO.EvnNotifyTub_IsDestruction,
				ENO.EvnNotifyTub_IsConfirmBact,
				ENO.TubBacterialExcretion_id,
				ENO.TubMethodConfirmBactType_id,
				ENO.EvnNotifyTub_IsRegCrazy,
				ENO.EvnNotifyTub_IsConfirmedDiag,
				ENO.TubRegCrazyType_id,
				convert(varchar,ENO.EvnNotifyTub_DiagConfirmDT,104) as EvnNotifyTub_DiagConfirmDT,
				ENO.PersonDispGroup_id,
				ENO.EvnNotifyTub_Comment,
				ENO.MedPersonal_id,
				ENO.Lpu_id,
				TDSL.TubDiagSopLink_Descr
			from
				v_EvnNotifyTub ENO with (nolock)
				left join v_EvnVizitPL PL with (nolock) on ENO.EvnNotifyTub_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST with (nolock) on ENO.EvnNotifyTub_pid = ST.EvnSection_id
				inner join v_Diag Diag with (nolock) on coalesce(ENO.Diag_id,PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				left join v_TubDiagSopLink TDSL with (nolock) on TDSL.EvnNotifyTub_id = ENO.EvnNotifyTub_id and TDSL.TubDiagSop_id = 7
			where
				ENO.EvnNotifyTub_id = ?
		';
		$res = $this->db->query($query, array($data['EvnNotifyTub_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			$res[0]['TubDiagSop'] = $this->loadTubDiagSop($data['EvnNotifyTub_id']);
			$res[0]['TubRiskFactorType'] = $this->loadTubRiskFactorType($data['EvnNotifyTub_id']);
			return $res;
		} else
			return false;		
	}
	
	/**
	 * Возвращает список сопутствующих заболеваний
	 */
	function loadTubDiagSop($EvnNotifyTub_id) {		
		$query = '
			select
				TubDiagSop_id
			from
				v_TubDiagSopLink with (nolock)
			where
				EvnNotifyTub_id = ?
		';
		$res = $this->db->query($query, array($EvnNotifyTub_id));
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as &$r) $r = $r['TubDiagSop_id'];
			return join(',', $res);
		} else
			return false;		
	}

	/**
	 * Возвращает список факторов риска
	 */
	function loadTubRiskFactorType($EvnNotifyTub_id) {		
		$query = '
			select
				TubRiskFactorType_id
			from
				v_TubRiskFactorTypeLink with (nolock)
			where
				EvnNotifyTub_id = ?
		';
		$res = $this->db->query($query, array($EvnNotifyTub_id));
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as &$r) $r = $r['TubRiskFactorType_id'];
			return join(',', $res);
		} else
			return false;		
	}

	/**
	 * Возвращает MorbusTub_id
	 */
	function loadMorbusTub($Morbus_id) {		
		$query = '
			select
				MorbusTub_id
			from
				v_MorbusTub with (nolock)
			where
				Morbus_id = ?
		';
		$res = $this->db->query($query, array($Morbus_id));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(!empty($res[0]['MorbusTub_id']))
				return $res[0]['MorbusTub_id'];
			else
				return null;
		} else
			return null;		
	}

	/**
	 * Возвращает список сопутствующих заболеваний с описанием
	 */
	function loadTubDiagSopWithDescr($EvnNotifyTub_id) {		
		$query = '
			select
				TubDiagSop_id,
				TubDiagSopLink_Descr
			from
				v_TubDiagSopLink with (nolock)
			where
				EvnNotifyTub_id = ?
		';
		$resp = array('TubDiagSopLink_Descr'=>null,'EvnNotifyTub_id'=>$EvnNotifyTub_id);
		$res = $this->db->query($query, array($EvnNotifyTub_id));
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as $val) {
				if($val['TubDiagSop_id'] == 7){
					$resp['TubDiagSopLink_Descr'] = $val['TubDiagSopLink_Descr'];
				}
			}
			foreach ($res as &$r) $r = $r['TubDiagSop_id'];
			$resp['TubDiagSop'] = join(',', $res);
			return $resp;
		} else
			return false;		
	}

	/**
	 * Сохранение объекта «Извещение»
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
			if (empty($data[$tableName . '_pid']) && empty($data['saveFromJournal'])) {
				throw new Exception('Не передан параметр Учетный документ', 500);
			}
			if (empty($data['Diag_id']) && !empty($data['saveFromJournal'])) {
				throw new Exception('Не передан параметр Диагноз по МКБ', 500);
			}
			if (empty($data['EvnNotifyTub_setDT'])) {
				throw new Exception('Не передан параметр Дата заполнения извещения', 500);
			}

			$this->load->library('swMorbus');
			if(!empty($data[$tableName . '_pid'])){
				$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session']);
			} else {
				$tmp = swMorbus::checkByEvn($this->morbusTypeSysNick, array('Diag_id'=>$data['Diag_id'],'Person_id'=>$data['Person_id'],'Evn_pid'=>null,'session'=>$data['session']),'onBeforeSaveEvnNotifyFromJrn');
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
				'EvnNotifyTub_setDT' => $data['EvnNotifyTub_setDT'],
				'MedPersonal_id' => $data['MedPersonal_id'],

				'PersonCategoryType_id' => $data['PersonCategoryType_id'],
				'PersonLivingFacilies_id' => $data['PersonLivingFacilies_id'],
				'EvnNotifyTub_OtherPersonCategory' => $data['EvnNotifyTub_OtherPersonCategory'],
				'PersonDecreedGroup_id' => $data['PersonDecreedGroup_id'],
				'EvnNotifyTub_IsDecreeGroup' => $data['EvnNotifyTub_IsDecreeGroup'],
				'TubFluorSurveyPeriodType_id' => $data['TubFluorSurveyPeriodType_id'],
				'TubDetectionPlaceType_id' => $data['TubDetectionPlaceType_id'],
				'EvnNotifyTub_OtherDetectionPlace' => $data['EvnNotifyTub_OtherDetectionPlace'],
				'DrugResistenceTest_id' => $data['DrugResistenceTest_id'],
				'EvnNotifyTub_FirstDT' => $data['EvnNotifyTub_FirstDT'],
				'EvnNotifyTub_RegDT' => $data['EvnNotifyTub_RegDT'],
				'TubDetectionFactType_id' => $data['TubDetectionFactType_id'],
				'TubSurveyGroupType_id' => $data['TubSurveyGroupType_id'],
				'TubDetectionMethodType_id' => $data['TubDetectionMethodType_id'],
				'EvnNotifyTub_OtherDetectionMethod' => $data['EvnNotifyTub_OtherDetectionMethod'],
				'Diag_id' => $data['Diag_id'],
				'TubDiagNotify_id' => $data['TubDiagNotify_id'],
				'TubDiagForm8_id' => $data['TubDiagForm8_id'],
				'EvnNotifyTub_IsFirstDiag' => $data['EvnNotifyTub_IsFirstDiag'],
				'EvnNotifyTub_IsDestruction' => $data['EvnNotifyTub_IsDestruction'],
				'EvnNotifyTub_IsConfirmBact' => $data['EvnNotifyTub_IsConfirmBact'],
				'TubBacterialExcretion_id' => $data['TubBacterialExcretion_id'],
				'TubMethodConfirmBactType_id' => $data['TubMethodConfirmBactType_id'],
				'EvnNotifyTub_IsRegCrazy' => $data['EvnNotifyTub_IsRegCrazy'],
				'EvnNotifyTub_IsConfirmedDiag' => $data['EvnNotifyTub_IsConfirmedDiag'],
				'TubRegCrazyType_id' => $data['TubRegCrazyType_id'],
				'EvnNotifyTub_DiagConfirmDT' => $data['EvnNotifyTub_DiagConfirmDT'],
				'PersonDispGroup_id' => $data['PersonDispGroup_id'],
				'EvnNotifyTub_Comment' => $data['EvnNotifyTub_Comment'],
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
			$this->onAfterSaveEvnNotify($data);
			$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
				'EvnNotifyBase_id' => $data[$pk],
				'EvnNotifyBase_pid' => $data[$tableName . '_pid'],
				'EvnNotifyBase_setDate' => $data['EvnNotifyTub_setDT'],
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
			$hiv = $this->checkHIVRegistryEntry(array('Person_id'=>$data['Person_id']));
			if(is_array($hiv) && count($hiv)>0){
				if(empty($data['TubDiagSop'])){
					$data['TubDiagSop'] = '8';
				} else {
					$data['TubDiagSop'] = $data['TubDiagSop'].',8';
				}
			}
			if(!empty($data['Morbus_id']))
				$data['MorbusTub_id'] = $this->loadMorbusTub($data['Morbus_id']);
			else 
				$data['MorbusTub_id'] = null;
			
			if(!empty($data['TubDiagSop'])){
				$this->saveTubDiagSop($data);
			}
			if(!empty($data['TubRiskFactorType'])){
				$this->saveTubRiskFactorType($data);
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
	 * @param array $data
	 * @throws Exception
	 */
	function onAfterSaveEvnNotify($data) {
		if (!empty($data['Morbus_id']) && (
			!empty($data['PersonDecreedGroup_id']) ||
			!empty($data['PersonLivingFacilies_id']) ||
			!empty($data['PersonDispGroup_id'])
		)) {
			$params = array(
				'Morbus_id' => $data['Morbus_id'],
				'PersonDecreedGroup_id' => !empty($data['PersonDecreedGroup_id'])?$data['PersonDecreedGroup_id']:null,
				'PersonLivingFacilies_id' => !empty($data['PersonLivingFacilies_id'])?$data['PersonLivingFacilies_id']:null,
				'PersonDispGroup_id' => !empty($data['PersonDispGroup_id'])?$data['PersonDispGroup_id']:null,
			);

			$query = "
				declare @Error_Code bigint = null
				declare @Error_Message varchar(4000) = ''
				set nocount on
				begin try
					declare @MorbusTub_id bigint = (
						select MorbusTub_id
						from v_MorbusTub with(nolock)
						where Morbus_id = :Morbus_id
					)
					if (@MorbusTub_id is not null)
					update
						MorbusTub with(rowlock)
					set
						PersonDecreedGroup_id = :PersonDecreedGroup_id,
						PersonLivingFacilies_id = :PersonLivingFacilies_id,
						PersonDispGroup_id = :PersonDispGroup_id
					where
						MorbusTub_id = @MorbusTub_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->queryResult($query, $params);
			if (!is_array($result)) {
				throw new Exception('Ошибка при обновлении данных туберкулезного заболевания');
			}
			if (!$this->isSuccessful($result)) {
				throw new Exception($result[0]['Error_Msg']);
			}
		}
	}
	
	/**
	 * Сохранение сопутствующих заболеваний
	 */
	function saveTubDiagSop($data) {
	
		$TubDiagSop = explode(',', $data['TubDiagSop']);
		
		$query = "
			select TubDiagSopLink_id
			from v_TubDiagSopLink with (nolock)
			where EvnNotifyTub_id = :EvnNotifyTub_id
		";
		
		$res = $this->db->query($query, array(
			'EvnNotifyTub_id' => $data['EvnNotifyTub_id']
		));
		
		if (!is_object($res)) {
			return false;
		}
		
		$old = $res->result('array');
		
		$query = '			
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :TubDiagSopLink_id;
			exec p_TubDiagSopLink_del
				@TubDiagSopLink_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as TubDiagSopLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		foreach ($old as $item) {
			$this->db->query($query, array(
				'TubDiagSopLink_id' => $item['TubDiagSopLink_id']
			));
		}
		
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :TubDiagSopLink_id;
			exec p_TubDiagSopLink_ins
				@TubDiagSopLink_id = @Res,
				@TubDiagSop_id = :TubDiagSop_id,
				@EvnNotifyTub_id = :EvnNotifyTub_id,
				@MorbusTub_id = :MorbusTub_id,
				@TubDiagSopLink_Descr = :TubDiagSopLink_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as TubDiagSopLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		if (count($TubDiagSop) && !empty($TubDiagSop[0])) {
			foreach ($TubDiagSop as $item) {
				$this->db->query($query, array(
					'TubDiagSopLink_id' => null,
					'TubDiagSop_id' => $item,
					'EvnNotifyTub_id' => $data['EvnNotifyTub_id'],
					'MorbusTub_id' => $data['MorbusTub_id'],
					'TubDiagSopLink_Descr' => ($item == 7) ? $data['TubDiagSopLink_Descr'] : null,
					'pmUser_id' => $data['pmUser_id'],
				));			
			}
		}
		
		return true;
	}

	/**
	 * Сохранение факторов риска
	 */
	function saveTubRiskFactorType($data) {
	
		$TubRiskFactorType = explode(',', $data['TubRiskFactorType']);
		
		$query = "
			select TubRiskFactorTypeLink_id
			from v_TubRiskFactorTypeLink with (nolock)
			where EvnNotifyTub_id = :EvnNotifyTub_id
		";
		
		$res = $this->db->query($query, array(
			'EvnNotifyTub_id' => $data['EvnNotifyTub_id']
		));
		
		if (!is_object($res)) {
			return false;
		}
		
		$old = $res->result('array');
		
		$query = '			
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :TubRiskFactorTypeLink_id;
			exec p_TubRiskFactorTypeLink_del
				@TubRiskFactorTypeLink_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as TubRiskFactorTypeLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		foreach ($old as $item) {
			$this->db->query($query, array(
				'TubRiskFactorTypeLink_id' => $item['TubRiskFactorTypeLink_id']
			));
		}
		
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :TubRiskFactorTypeLink_id;
			exec p_TubRiskFactorTypeLink_ins
				@TubRiskFactorTypeLink_id = @Res,
				@TubRiskFactorType_id = :TubRiskFactorType_id,
				@EvnNotifyTub_id = :EvnNotifyTub_id,
				@MorbusTub_id = :MorbusTub_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as TubRiskFactorTypeLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		if (count($TubRiskFactorType) && !empty($TubRiskFactorType[0])) {
			foreach ($TubRiskFactorType as $item) {
				$this->db->query($query, array(
					'TubRiskFactorTypeLink_id' => null,
					'TubRiskFactorType_id' => $item,
					'EvnNotifyTub_id' => $data['EvnNotifyTub_id'],
					'MorbusTub_id' => $data['MorbusTub_id'],
					'pmUser_id' => $data['pmUser_id'],
				));			
			}
		}
		
		return true;
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
			set @Res = :EvnNotifyTub_id;
			exec p_EvnNotifyTub_del
				@EvnNotifyTub_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyTub_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyTub_id' => $data['EvnNotifyTub_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);
		
		// Удаляем запись регистра
		if (!empty($data['PersonRegister_id'])) {
			$this->load->model('PersonRegister_model', 'prmodel');
			$this->prmodel->doDelete($data);
		}

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Создание извещения о больном туберкулезом. Метод API
	 */
	function saveEvnNotifyTubAPI($data){
		//проверка существования извещения
		$query = 'SELECT EvnNotifyTub_id FROM v_EvnNotifyTub WHERE MorbusType_id = 7 AND Person_id = :Person_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(!empty($res['EvnNotifyTub_id'])){
			return array('Error_Msg' => 'Извещение по данному заболеванию существует');
		}
		
		//обязательные поля в зависимости от диагноза
		$query = 'select Diag_Code * from v_Diag where Diag_id = :Diag_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(!empty($res['Diag_Code'])){
			return array('Error_Msg' => 'данные по диагнозу не найдены');
		}else{
			if(empty($data['TubClinicStateClass_id']) && (substr($res['Diag_Code'], 0, 3) == 'А15' || substr($res['Diag_Code'], 0, 3) == 'А16')){
				return array('Error_Msg' => 'не передан параметр TubClinicStateClass_id');
			}
			
			$arrDiag = array('А17.0', 'А17.1', 'А17.8', 'А17.9', 'А19.0', 'А19.0', 'А19.2', 'А19.8', 'А19.9');
			if(empty($data['TubLocalizationClass_id']) && (substr($res['Diag_Code'], 0, 3) == 'А18' || in_array($res['Diag_Code'], $arrDiag)) ){
				return array('Error_Msg' => 'не передан параметр TubLocalizationClass_id');
			}
		}
		
		$query = 'SELECT PersonEvn_id, Person_id FROM v_Evn WHERE Evn_id = :Evn_pid AND Person_id = :Person_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		
		if(empty($res['PersonEvn_id'])){
			return array('Error_Msg' => 'случай не найден');
		}
		
		$data['PersonEvn_id'] = $res['PersonEvn_id'];
		$data['EvnNotifyTub_pid'] = $data['Evn_pid'];
		
		$this->load->model("Options_model");
		$this->globalOptions = $this->Options_model->getOptionsGlobals($data);
		$register_tub_auto_include = (!empty($this->globalOptions['globals']['register_tub_auto_include'])) ? $this->globalOptions['globals']['register_tub_auto_include'] : null;
		if($register_tub_auto_include){
			//$existPR = $this->checkTubRegistryEntry($data);
			//if(empty($$existPR[0]['PersonRegister_id'])){}
			if(empty($data['Lpu_iid']) || empty($data['PersonRegister_setDate']) || empty($data['MedPersonal_iid']) || empty($data['PersonRegisterType_id'])){
				return array(
					'Error_Msg' => 'Не передан один из обязательных параметров: Lpu_iid, PersonRegister_setDate, MedPersonal_iid, PersonRegisterType_id'
				);
			}
		}
		
		$res = $this->doSave($data);
		return $res;
	}
	
	/**
	 * Изменение извещения о больном туберкулезом. Метод API
	 */
	function updateEvnNotifyTubAPI($data){
		//проверка существования извещения
		$query = 'SELECT * FROM v_EvnNotifyTub WHERE EvnNotifyTub_id = :EvnNotifyTub_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['EvnNotifyTub_id'])){
			return array('Error_Msg' => 'Извещение по данному заболеванию не найдено');
		}
		$data['PersonEvn_id'] = $res['PersonEvn_id'];
		$data['EvnNotifyTub_pid'] = $res['EvnNotifyTub_pid'];
		
		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}			
		}
		
		$res = $this->doSave($data);
		return $res;
	}
	
	/**
	 * Получение извещения о больном туберкулезом. Метод API
	 */
	function getEvnNotifyTubForAPI($data){
		if(empty($data['EvnNotifyTub_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['EvnNotifyTub_id'])){
			$where .= ' AND ENO.EvnNotifyTub_id = :EvnNotifyTub_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND ENO.Person_id = :Person_id';
		}
		$query = "
			select
				ENO.EvnNotifyTub_id,
				ENO.Person_id,
				ENO.PersonCategoryType_id,
				ENO.PersonLivingFacilies_id,
				ENO.TubDetectionFactType_id,
				ENO.EvnNotifyTub_IsAutopsied,
				ENO.EvnNotifyTub_deathDT,
				ENO.TubResultDeathType_id,
				ENO.TubDetectionMethodType_id,
				ENO.TubDetectionMethodType_id,
				ENO.EvnNotifyTub_OtherDetectionMethod,
				ENO.DrugResistenceTest_id,
				ENO.Diag_id,
				ENO.TubDiagNotify_id,
				ENO.TubDiagForm8_id,
				ENO.EvnNotifyTub_IsFirstDiag,
				ENO.TubClinicStateClass_id,
				ENO.TubLocalizationClass_id,
				ENO.TubBacterialExcretion_id,
				ENO.TubMethodConfirmBactType_id,
				ENO.TubRegCrazyType_id,
				ENO.TubDetectionPlaceType_id,
				ENO.EvnNotifyTub_OtherDetectionPlace,
				ENO.PersonDecreedGroup_id,
				ENO.EvnNotifyTub_IsDecreeGroup,
				ENO.EvnNotifyTub_IsDestruction,
				ENO.EvnNotifyTub_IsConfirmBact,
				ENO.EvnNotifyTub_IsRegCrazy,
				ENO.EvnNotifyTub_IsConfirmedDiag,
				ENO.EvnNotifyTub_DiagConfirmDT,
				ENO.PersonDispGroup_id,
				ENO.EvnNotifyTub_Comment,
				ENO.EvnNotifyTub_FirstDT,
				ENO.EvnNotifyTub_RegDT,
				convert(varchar,ENO.EvnNotifyTub_setDT,104) as EvnNotifyTub_setDT,
				ENO.Lpu_id,
				ENO.MedPersonal_id,
				ENO.TubFluorSurveyPeriodType_id,
				ENO.TubDiagSop_id,
				TDSL.TubDiagSopLink_Descr,
				--ENO.TubRiskFactorType_id,
				ENO.TubDetectionFactType_id,
				ENO.TubPostmortalDecision_id,
				ENO.TubSurveyGroupType_id,
				PR.Lpu_iid,
				convert(varchar,PR.PersonRegister_setDate,104) as PersonRegister_setDate,
				PR.MedPersonal_iid,
				PR.PersonRegisterType_id,
				ENO.Lpu_niid ,
				ENO.MedPersonal_niid,
				ENO.PersonRegisterFailIncludeCause_id
				,ENO.EvnNotifyTub_pid as Evn_pid
				,stuff((
					select concat(',',TDSL.TubDiagSop_id ) 
					from v_TubDiagSopLink TDSL with (nolock)
					where TDSL.EvnNotifyTub_id = ENO.EvnNotifyTub_id
					for XML path('')
				),1,1,'')TubDiagSop_id_List
				,stuff((
					select concat(',',TRFTL.TubRiskFactorType_id ) 
					from v_TubRiskFactorTypeLink TRFTL with (nolock)
					where TRFTL.EvnNotifyTub_id = ENO.EvnNotifyTub_id
					for XML path('')
				),1,1,'')TubRiskFactorType_id_List 
			from
				v_EvnNotifyTub ENO with (nolock)
				left join v_PersonRegister PR with(nolock) on PR.EvnNotifyBase_id = ENO.EvnNotifyTub_id
				left join v_TubDiagSopLink TDSL with (nolock) on TDSL.EvnNotifyTub_id = ENO.EvnNotifyTub_id and TDSL.TubDiagSop_id = 7
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}
}
