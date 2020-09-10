<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MorbusPalliat_model - модель для MorbusPalliat
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Пермяков Александр
* @version      10.2012
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
*/
class MorbusPalliat_model extends swModel
{
	protected $_MorbusType_id = 6;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'Palliat';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusPalliat';
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Lpu_id'] = isset($data['Lpu_id'])?$data['Lpu_id']:$this->sessionParams['lpu_id'];

		$addFields = '';

		$vkdata = $this->checkEvnVk($data);

		if ($vkdata !== false) {
			$addFields = "
				@MorbusPalliat_VKDate = :MorbusPalliat_VKDate,
				@MorbusPalliat_IsFamCare = :MorbusPalliat_IsFamCare,
				@PalliativeType_id = :PalliativeType_id,
				@MorbusPalliat_IsTIR = :MorbusPalliat_IsTIR,
				@MorbusPalliat_TextTIR = :MorbusPalliat_TextTIR,
			";
			$queryParams['MorbusPalliat_VKDate'] = $vkdata['MorbusPalliat_VKDate'];
			$queryParams['MorbusPalliat_IsFamCare'] = $vkdata['MorbusPalliat_IsFamCare'];
			$queryParams['PalliativeType_id'] = $vkdata['PalliativeType_id'];
			$queryParams['MorbusPalliat_IsTIR'] = $vkdata['MorbusPalliat_IsTIR'];
			$queryParams['MorbusPalliat_TextTIR'] = $vkdata['MorbusPalliat_TextTIR'];
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@pmUser_id bigint = :pmUser_id,
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@IsCreate int = 1;

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					{$addFields}
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				if isnull(@{$tableName}_id, 0) > 0
				begin
					set @IsCreate = 2;
				end
			end

			select @{$tableName}_id as {$tableName}_id, @IsCreate as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception("Не удалось создать объект {$tableName}", 500);
		}
		if ($vkdata !== false) {
			$vkdata['MorbusPalliat_id'] = $resp[0]['MorbusPalliat_id'];
			$vkdata['pmUser_id'] = $this->promedUserId;
			$this->saveMainSyndromeLink($vkdata);
			$this->saveTechnicInstrumRehabLink($vkdata);
			foreach($vkdata['PalliatFamilyCareList'] as $item) {
				$item['MorbusPalliat_id'] = $vkdata['MorbusPalliat_id'];
				$item['pmUser_id'] = $this->promedUserId;
				$this->savePalliatFamilyCare($item);
			}
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 */
	public function load($data) {
		$params = array(
			'MorbusPalliat_id' => $data['MorbusPalliat_id']
		);

		$query = "
			select
				MO.MorbusPalliat_id,
				MO.Morbus_id,
				MO.MorbusPalliat_IsIVL,
				MO.MorbusPalliat_IsAnesthesia,
				MO.MorbusPalliat_IsZond,
				MO.ViolationsDegreeType_id,
				case when MO.AnesthesiaType_id is null and MO.MorbusPalliat_IsAnesthesia = 1 then -1 else MO.AnesthesiaType_id end as AnesthesiaType_id,
				MO.Lpu_sid,
				MO.Lpu_aid,
				M.Diag_id,
				M.Person_id,
				convert(varchar(10), MO.MorbusPalliat_VKDate, 104) as MorbusPalliat_VKDate,
				convert(varchar(10), MO.MorbusPalliat_DiagDate, 104) as MorbusPalliat_DiagDate,
				convert(varchar(10), MO.MorbusPalliat_DisDetDate, 104) as MorbusPalliat_DisDetDate,
				MO.RecipientInformation_id,
				MO.MorbusPalliat_IsFamCare,
				MO.PalliativeType_id,
				convert(varchar(10), MO.MorbusPalliat_StomPrescrDate, 104) as MorbusPalliat_StomPrescrDate,
				convert(varchar(10), MO.MorbusPalliat_StomSetDate, 104) as MorbusPalliat_StomSetDate,
				case when MO.MorbusPalliat_VLbegDate is not null then
					convert(varchar(10), MO.MorbusPalliat_VLbegDate, 104)+' - '+convert(varchar(10), MO.MorbusPalliat_VLendDate, 104)
				end as MorbusPalliat_VLDateRange,
				MRL.MethodRaspiratAssist,
				MO.MorbusPalliat_IsTIR,
				convert(varchar(10), MO.MorbusPalliat_VKTIRDate, 104) as MorbusPalliat_VKTIRDate,
				convert(varchar(10), MO.MorbusPalliat_TIRDate, 104) as MorbusPalliat_TIRDate,
				MO.MorbusPalliat_TextTIR,
				left(PEVKD.MainSyndrome, len(PEVKD.MainSyndrome)-1) MainSyndrome,
				MO.PalliatIndicatChangeCondit_id,
				MO.MorbusPalliat_OtherIndicatChangeCondit,
				convert(varchar(10), MO.MorbusPalliat_ChangeConditDate, 104) as MorbusPalliat_ChangeConditDate,
				convert(varchar(10), MO.MorbusPalliat_SocialProtDate, 104) as MorbusPalliat_SocialProtDate,
				MO.MorbusPalliat_SocialProt
			from
				v_MorbusPalliat MO with (nolock)
				left join v_Morbus M with (nolock) on M.Morbus_id = MO.Morbus_id		
				outer apply (
						select top 1 (
						select cast(MethodRaspiratAssist_id as varchar)+',' as 'data()'
						from MethodRaspiratAssistLink (nolock)
						where MorbusPalliat_id = MO.MorbusPalliat_id
						for xml path('')
					) as MethodRaspiratAssist
				) MRL
				outer apply (
						select top 1 (
						select cast(MainSyndrome_id as varchar)+',' as 'data()'
						from MainSyndromeLink (nolock)
						where MorbusPalliat_id = MO.MorbusPalliat_id
						for xml path('')
					) as MainSyndrome
				) PEVKD
			where
				MO.MorbusPalliat_id = :MorbusPalliat_id
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $resp;
		}
		
		$resp[0]['MedProductCard'] = $this->queryResult("
			select MedProductCard_id
			from MedProductCardLink (nolock)
			where MorbusPalliat_id = ?
		", array($data['MorbusPalliat_id']));

		$tir = $this->queryResult("
			select 
				isnull(TechnicInstrumRehab_id, 9999) as id,
				convert(varchar(10), TechnicInstrumRehabLink_TIRDate, 104) as date
			from TechnicInstrumRehabLink (nolock)
			where MorbusPalliat_id = ?
		", array($data['MorbusPalliat_id']));
		$resp[0]['TechnicInstrumRehab'] = json_encode($tir);

		if (!empty($data['Evn_id'])) {
			$resp[0]['Evn_id'] = $data['Evn_id'];
		}

		return $resp;
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 */
	public function save($data) {
		$this->beginTransaction();

		if (!empty($data['MorbusPalliat_id'])) {
			$proc = 'p_MorbusPalliat_upd';
			// @task https://redmine.swan.perm.ru//issues/143613
			// Поле нельзя менять. Защитимся от хацкеров и будем тянуть значение из БД
			$data['Diag_id'] = $this->getFirstResultFromQuery("select top 1 Diag_id from v_MorbusPalliat with (nolock) where MorbusPalliat_id = :MorbusPalliat_id", array('MorbusPalliat_id' => $data['MorbusPalliat_id']));
		} else {
			$data['MorbusPalliat_id'] = null;
			$proc = 'p_MorbusPalliat_ins';
		}

		$data['MorbusPalliat_IsAnesthesia'] = null;
		if (!empty($data['AnesthesiaType_id'])) {
			if ($data['AnesthesiaType_id'] < 0) {
				$data['AnesthesiaType_id'] = null;
				$data['MorbusPalliat_IsAnesthesia'] = 1;
			} else {
				$data['MorbusPalliat_IsAnesthesia'] = 2;
			}
		}

		/*if (!empty($data['Diag_id'])) {
			// надо обновить диагноз в регистре и заболевании ?
			$this->db->query("
				update Morbus with (rowlock) set Diag_id = :Diag_id where Morbus_id = :Morbus_id;
				update PersonRegister with (rowlock) set Diag_id = :Diag_id where Morbus_id = :Morbus_id;
			", array(
				'Morbus_id' => $data['Morbus_id'],
				'Diag_id' => $data['Diag_id']
			));
		}*/

		if (!empty($data['TechnicInstrumRehab_id']) && $data['TechnicInstrumRehab_id'] < 0) {
			$data['TechnicInstrumRehab_id'] = null;
		}
		if (isset($data['MorbusPalliat_VLDateRange']) && !empty($data['MorbusPalliat_VLDateRange'][0])) {
			$data['MorbusPalliat_VLbegDate'] = $data['MorbusPalliat_VLDateRange'][0];
			$data['MorbusPalliat_VLendDate'] = $data['MorbusPalliat_VLDateRange'][1];
		} else {
			$data['MorbusPalliat_VLbegDate'] = null;
			$data['MorbusPalliat_VLendDate'] = null;
		}

		$PalliatFamilyCare = json_decode($data['PalliatFamilyCare'], true);

		$query = "
			declare
				@MorbusPalliat_id bigint = :MorbusPalliat_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@MorbusPalliat_id = @MorbusPalliat_id output,
				@Morbus_id = :Morbus_id,
				@MorbusPalliat_IsIVL = :MorbusPalliat_IsIVL,
				@MorbusPalliat_IsAnesthesia = :MorbusPalliat_IsAnesthesia,
				@MorbusPalliat_IsZond = :MorbusPalliat_IsZond,
				@ViolationsDegreeType_id = :ViolationsDegreeType_id,
				@AnesthesiaType_id = :AnesthesiaType_id,
				@Lpu_sid = :Lpu_sid,
				@Lpu_aid = :Lpu_aid,
				@MorbusPalliat_VKDate = :MorbusPalliat_VKDate,
				@MorbusPalliat_DiagDate = :MorbusPalliat_DiagDate,
				@RecipientInformation_id = :RecipientInformation_id,
				@MorbusPalliat_IsFamCare = :MorbusPalliat_IsFamCare,
				@PalliativeType_id = :PalliativeType_id,
				@MorbusPalliat_StomPrescrDate = :MorbusPalliat_StomPrescrDate,
				@MorbusPalliat_StomSetDate = :MorbusPalliat_StomSetDate,
				@MorbusPalliat_VLbegDate = :MorbusPalliat_VLbegDate,
				@MorbusPalliat_VLendDate = :MorbusPalliat_VLendDate,
				@MorbusPalliat_IsTIR = :MorbusPalliat_IsTIR,
				@MorbusPalliat_VKTIRDate = :MorbusPalliat_VKTIRDate,
				@MorbusPalliat_TIRDate = :MorbusPalliat_TIRDate,
				@MorbusPalliat_TextTIR = :MorbusPalliat_TextTIR,
				@PalliatIndicatChangeCondit_id = :PalliatIndicatChangeCondit_id,
				@MorbusPalliat_OtherIndicatChangeCondit = :MorbusPalliat_OtherIndicatChangeCondit,
				@MorbusPalliat_ChangeConditDate = :MorbusPalliat_ChangeConditDate,
				@MorbusPalliat_SocialProtDate = :MorbusPalliat_SocialProtDate,
				@MorbusPalliat_SocialProt = :MorbusPalliat_SocialProt,
				@MorbusPalliat_DisDetDate = :MorbusPalliat_DisDetDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MorbusPalliat_id as MorbusPalliat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$data['MorbusPalliat_id'] = $resp[0]['MorbusPalliat_id'];
		
		$this->saveMethodRaspiratAssist($data);
		$this->saveMedProductCard($data);
		$this->saveMainSyndromeLink($data);
		$this->saveTechnicInstrumRehabLink($data);

		if (!empty($data['Evn_id'])) {
			$data['MorbusPalliatEvn_id'] = $this->getFirstResultFromQuery("
				select top 1 MorbusPalliatEvn_id
				from v_MorbusPalliatEvn with(nolock)
				where Evn_id = :Evn_id
				order by MorbusPalliatEvn_id desc
			", $data);

			if (empty($data['MorbusPalliatEvn_id'])) {
				$proc = 'p_MorbusPalliatEvn_ins';
			} else {
				$proc = 'p_MorbusPalliatEvn_upd';
			}

			$query = "
				declare
					@MorbusPalliatEvn_id bigint = :MorbusPalliatEvn_id,
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec {$proc}
					@MorbusPalliatEvn_id = @MorbusPalliatEvn_id output,
					@Evn_id = :Evn_id,
					@MorbusPalliatEvn_IsIVL = :MorbusPalliat_IsIVL,
					@MorbusPalliatEvn_IsAnesthesia = :MorbusPalliat_IsAnesthesia,
					@MorbusPalliatEvn_IsZond = :MorbusPalliat_IsZond,
					@ViolationsDegreeType_id = :ViolationsDegreeType_id,
					@AnesthesiaType_id = :AnesthesiaType_id,
					@Lpu_sid = :Lpu_sid,
					@Lpu_aid = :Lpu_aid,
					@MorbusPalliatEvn_VKDate = :MorbusPalliat_VKDate,
					@MorbusPalliatEvn_DiagDate = :MorbusPalliat_DiagDate,
					@RecipientInformation_id = :RecipientInformation_id,
					@MorbusPalliatEvn_IsFamCare = :MorbusPalliat_IsFamCare,
					@PalliativeType_id = :PalliativeType_id,
					@MorbusPalliatEvn_StomPrescrDate = :MorbusPalliat_StomPrescrDate,
					@MorbusPalliatEvn_StomSetDate = :MorbusPalliat_StomSetDate,
					@MorbusPalliatEvn_VLbegDate = :MorbusPalliat_VLbegDate,
					@MorbusPalliatEvn_VLendDate = :MorbusPalliat_VLendDate,
					@MorbusPalliatEvn_IsTIR = :MorbusPalliat_IsTIR,
					@MorbusPalliatEvn_VKTIRDate = :MorbusPalliat_VKTIRDate,
					@MorbusPalliatEvn_TIRDate = :MorbusPalliat_TIRDate,
					@TechnicInstrumRehab_id = :TechnicInstrumRehab_id,
					@MorbusPalliatEvn_TextTIR = :MorbusPalliat_TextTIR,
					@PalliatIndicatChangeCondit_id = :PalliatIndicatChangeCondit_id,
					@MorbusPalliatEvn_OtherIndicatChangeCondit = :MorbusPalliat_OtherIndicatChangeCondit,
					@MorbusPalliatEvn_ChangeConditDate = :MorbusPalliat_ChangeConditDate,
					@MorbusPalliatEvn_SocialProt = :MorbusPalliat_SocialProt,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @MorbusPalliatEvn_id as MorbusPalliatEvn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$resp = $this->queryResult($query, $data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		if (!empty($PalliatFamilyCare) && is_array($PalliatFamilyCare)) {
			foreach($PalliatFamilyCare as $item) {
				$item['MorbusPalliat_id'] = $data['MorbusPalliat_id'];
				$item['pmUser_id'] = $data['pmUser_id'];

				switch($item['RecordStatus_Code']) {
					case 0:
						$item['PalliatFamilyCare_id'] = null;
						$resp = $this->savePalliatFamilyCare($item);
						break;
					case 2:
						$resp = $this->savePalliatFamilyCare($item);
						break;
					case 3:
						$resp = $this->deletePalliatFamilyCare($item);
						break;
				}

				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}

		$this->commitTransaction();

		return array(array(
			'success' => true,
			'MorbusPalliat_id' => $data['MorbusPalliat_id'],
		));
	}
	
	/**
	 *	Сохранение
	 */
	function saveMainSyndromeLink($data) {
		$tmp = $this->queryList("select MainSyndromeLink_id from MainSyndromeLink (nolock) where MorbusPalliat_id = ?", array($data['MorbusPalliat_id']));
		foreach($tmp as $row) {
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_MainSyndromeLink_del
					@MainSyndromeLink_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($row));
		}
		
		if(empty($data['MainSyndrome'])) return false;
		$data['MainSyndrome'] = explode(',', $data['MainSyndrome']);
		
		foreach($data['MainSyndrome'] as $row) {
			$sql = "
				declare
					@MainSyndromeLink_id bigint = :MainSyndromeLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_MainSyndromeLink_ins
					@MainSyndromeLink_id = @MainSyndromeLink_id output,
					@MorbusPalliat_id = :MorbusPalliat_id,
					@MainSyndrome_id = :MainSyndrome_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MainSyndromeLink_id as MainSyndromeLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			
			$this->queryResult($sql, array(
				'MainSyndromeLink_id' => null,
				'MorbusPalliat_id' => $data['MorbusPalliat_id'],
				'MainSyndrome_id' => $row,
				'pmUser_id' => $data['pmUser_id'],
			));
		}
	}
	
	/**
	 *	Сохранение
	 */
	function saveTechnicInstrumRehabLink($data) {
		$tmp = $this->queryList("select TechnicInstrumRehabLink_id from TechnicInstrumRehabLink (nolock) where MorbusPalliat_id = ?", array($data['MorbusPalliat_id']));
		foreach($tmp as $row) {
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_TechnicInstrumRehabLink_del
					@TechnicInstrumRehabLink_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($row));
		}
		
		if(empty($data['TechnicInstrumRehab'])) return false;
		$data['TechnicInstrumRehab'] = json_decode($data['TechnicInstrumRehab'], true);
		
		foreach($data['TechnicInstrumRehab'] as $row) {
			$sql = "
				declare
					@TechnicInstrumRehabLink_id bigint = :TechnicInstrumRehabLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_TechnicInstrumRehabLink_ins
					@TechnicInstrumRehabLink_id = @TechnicInstrumRehabLink_id output,
					@MorbusPalliat_id = :MorbusPalliat_id,
					@TechnicInstrumRehab_id = :TechnicInstrumRehab_id,
					@TechnicInstrumRehabLink_TIRDate = :TechnicInstrumRehabLink_TIRDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @TechnicInstrumRehabLink_id as TechnicInstrumRehabLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$params = array(
				'TechnicInstrumRehabLink_id' => null,
				'MorbusPalliat_id' => $data['MorbusPalliat_id'],
				'TechnicInstrumRehab_id' => ($row['id'] != 9999)?$row['id']:null,
				'TechnicInstrumRehabLink_TIRDate' => $row['date'],
				'pmUser_id' => $data['pmUser_id'],
			);

			//echo getDebugSQL($sql, $params);
			
			$this->queryResult($sql, $params);
		}
	}

	/**
	 * Сохранение Метода респираторной поддержки
	 */
	public function saveMethodRaspiratAssist($data) {
		$tmp = $this->queryList("select MethodRaspiratAssistLink_id from MethodRaspiratAssistLink (nolock) where MorbusPalliat_id = ?", array($data['MorbusPalliat_id']));
		foreach($tmp as $row) {
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_MethodRaspiratAssistLink_del
					@MethodRaspiratAssistLink_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($row));
		}
		
		if(empty($data['MethodRaspiratAssist'])) return false;
		$data['MethodRaspiratAssist'] = explode(',', $data['MethodRaspiratAssist']);
		
		foreach($data['MethodRaspiratAssist'] as $row) {
			$sql = "
				declare
					@MethodRaspiratAssistLink_id bigint = :MethodRaspiratAssistLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_MethodRaspiratAssistLink_ins
					@MethodRaspiratAssistLink_id = @MethodRaspiratAssistLink_id output,
					@MorbusPalliat_id = :MorbusPalliat_id,
					@MethodRaspiratAssist_id = :MethodRaspiratAssist_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MethodRaspiratAssistLink_id as MethodRaspiratAssistLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			
			$this->queryResult($sql, array(
				'MethodRaspiratAssistLink_id' => null,
				'MorbusPalliat_id' => $data['MorbusPalliat_id'],
				'MethodRaspiratAssist_id' => $row,
				'pmUser_id' => $data['pmUser_id'],
			));
		}
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function saveMedProductCard($data) {
		$mpc_list = array();
		$MedProductCardLink = $this->queryList("select MedProductCardLink_id from MedProductCardLink (nolock) where MorbusPalliat_id = ?", array($data['MorbusPalliat_id']));
		$data['MedProductCard'] = (array)$data['MedProductCard'];
		
		// добавляем/обновляем
		foreach($data['MedProductCard'] as $mpc) {
			if (empty($mpc->MedProductCard_id)) {
				continue;
			}
			$procedure = empty($mpc->MedProductCardLink_id) ? 'p_MedProductCardLink_ins' : 'p_MedProductCardLink_upd';
			$sql = "
				declare
					@MedProductCardLink_id bigint = :MedProductCardLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@MedProductCardLink_id = @MedProductCardLink_id output,
					@MedProductCard_id = :MedProductCard_id,
					@MorbusPalliat_id = :MorbusPalliat_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @MedProductCardLink_id as MedProductCardLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			$this->queryResult($sql, array(
				'MedProductCardLink_id' => $mpc->MedProductCardLink_id,
				'MedProductCard_id' => $mpc->MedProductCard_id,
				'MorbusPalliat_id' => $data['MorbusPalliat_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			
			if (!empty($mpc->MedProductCardLink_id)) {
				$mpc_list[] = $mpc->MedProductCardLink_id;
			}
		}
		
		// то, что было в БД, но уже нет на форме - удаляем
		$delmpc = array_diff($MedProductCardLink, $mpc_list);
		foreach($delmpc as $mpc) {
			$sql = "
				declare
					@MedProductCardLink_id bigint = :MedProductCardLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_MedProductCardLink_del
					@MedProductCardLink_id = @MedProductCardLink_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			
			$this->queryResult($sql, array(
				'MedProductCardLink_id' => $mpc,
			));
		}
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function savePalliatFamilyCare($data) {
		$params = array(
			'PalliatFamilyCare_id' => !empty($data['PalliatFamilyCare_id'])?$data['PalliatFamilyCare_id']:null,
			'MorbusPalliat_id' => $data['MorbusPalliat_id'],
			'FamilyRelationType_id' => !empty($data['FamilyRelationType_id'])?$data['FamilyRelationType_id']:null,
			'PalliatFamilyCare_Age' => !empty($data['PalliatFamilyCare_Age'])?$data['PalliatFamilyCare_Age']:null,
			'PalliatFamilyCare_Phone' => !empty($data['PalliatFamilyCare_Phone'])?$data['PalliatFamilyCare_Phone']:null,
			'EvnVK_id' => !empty($data['EvnVK_id'])?$data['EvnVK_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['PalliatFamilyCare_id'])) {
			$proc = 'p_PalliatFamilyCare_ins';
		} else {
			$proc = 'p_PalliatFamilyCare_upd';
		}

		$query = "
			declare
				@PalliatFamilyCare_id bigint = :PalliatFamilyCare_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$proc}
				@PalliatFamilyCare_id = @PalliatFamilyCare_id output,
				@MorbusPalliat_id = :MorbusPalliat_id,
				@FamilyRelationType_id = :FamilyRelationType_id,
				@PalliatFamilyCare_Age = :PalliatFamilyCare_Age,
				@PalliatFamilyCare_Phone = :PalliatFamilyCare_Phone,
				@EvnVK_id = :EvnVK_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PalliatFamilyCare_id as PalliatFamilyCare_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении сведений о родственниках, осуществляющих уход за пациентом');
		}

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function deletePalliatFamilyCare($data) {
		$params = array(
			'PalliatFamilyCare_id' => $data['PalliatFamilyCare_id'],
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PalliatFamilyCare_del
				@PalliatFamilyCare_id = :PalliatFamilyCare_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при уделении сведений о родственниках, осуществляющих уход за пациентом');
		}

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadPalliatFamilyCareList($data) {
		$params = array(
			'MorbusPalliat_id' => $data['MorbusPalliat_id'],
		);

		$query = "
			select
				PalliatFamilyCare_id,
				MorbusPalliat_id,
				FamilyRelationType_id,
				PalliatFamilyCare_Age,
				PalliatFamilyCare_Phone,
				EvnVK_id,
				1 as RecordStatus_Code
			from v_PalliatFamilyCare with(nolock)
			where MorbusPalliat_id = :MorbusPalliat_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadLpuList($data) {
		$params = array(
			'PalliativeType_id' => $data['PalliativeType_id'],
			'Date' => !empty($data['Date'])?$data['Date']:$this->currentDT->format('Y-m-d'),
		);

		$query = "
			declare @PalliativeType_id bigint = :PalliativeType_id;
			declare @date date = :Date;
			SELECT distinct
				Lpu.Lpu_id,
				Lpu.Org_id,
				Lpu.Org_tid,
				Lpu.Lpu_IsOblast,
				RTRIM(Lpu.Lpu_Name) as Lpu_Name,	
				RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,	
				Lpu.Lpu_Ouz,	
				Lpu.Lpu_RegNomC,	
				Lpu.Lpu_RegNomC2,	
				Lpu.Lpu_RegNomN2,	
				Lpu.Lpu_isDMS,	
				convert(varchar(10), Lpu.Lpu_DloBegDate, 104) as Lpu_DloBegDate,	
				convert(varchar(10), Lpu.Lpu_DloEndDate, 104) as Lpu_DloEndDate,	
				convert(varchar(10), Lpu.Lpu_BegDate, 104) as Lpu_BegDate,	
				convert(varchar(10), Lpu.Lpu_EndDate, 104) as Lpu_EndDate,	
				isnull(LpuLevel.LpuLevel_Code, 0) as LpuLevel_Code,	
				ISNULL(Org.Org_IsAccess, 1) as Lpu_IsAccess,	
				ISNULL(Org.Org_IsNotForSystem, 1) as Lpu_IsNotForSystem,	
				ISNULL(Lpu.Lpu_IsMse, 1) as Lpu_IsMse
			FROM 
				v_Lpu Lpu with (nolock)	
				inner join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id	
				left join LpuLevel with (nolock) on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
			where 
				Lpu.Lpu_endDate is null
				and 1 = case 
					when @PalliativeType_id = 5 and exists(
						select * from v_LpuUnit LU with(nolock)
						where LU.Lpu_id = Lpu.Lpu_id and isnull(LU.LpuUnit_isPallCC, 1) = 2
						and @date between LU.LpuUnit_begDate and isnull(LU.LpuUnit_endDate, @date)
					) then 1
					when exists (
						select * from v_LpuSection LS with(nolock)
						where LS.Lpu_id = Lpu.Lpu_id and LS.PalliativeType_id = @PalliativeType_id
						and @date between LS.LpuSection_setDate and isnull(LS.LpuSection_disDate, @date)
					) then 1
				end
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadMainSyndromeList($data) {
		$params = array();

		$query = "
			select
				MS.MainSyndrome_id,
				MS.MainSyndrome_Code,
				MS.MainSyndrome_Name
			from
				v_MainSyndrome MS with(nolock)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadMedProductCardList($data) {
		$params = array(
			'Lpu_did' => !empty($data['Lpu_did'])?$data['Lpu_did']:null,
		);
		$filters = "";

		if (!empty($data['query'])) {
			$filters .= " and MPClass.MedProductClass_Name like :query+'%'";
			$params['query'] = $data['query'];
		}

		$query = "
			select distinct
				MPCard.MedProductCard_id,
				MPClass.MedProductClass_id,
				MPClass.MedProductClass_Name
			from 
				passport.v_MedProductCard MPCard with(nolock)
				inner join passport.v_MedProductClass MPClass with(nolock) on MPClass.MedProductClass_id = MPCard.MedProductClass_id
				inner join passport.v_CardType CardType with(nolock) on CardType.CardType_id = MPClass.CardType_id
				inner join LpuBuilding LB with (nolock) on MPCard.LpuBuilding_id = LB.LpuBuilding_id
			where 
				LB.Lpu_id = :Lpu_did
				and CardType.CardType_Code between 826 and 831
				{$filters}
			order by
				MPClass.MedProductClass_Name
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getIdForEmk($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
		);

		$query = "
			select top 1 MO.MorbusPalliat_id
			from v_MorbusPalliat MO with(nolock)
			inner join v_Morbus M with(nolock) on M.Morbus_id = MO.Morbus_id
			where M.Person_id = :Person_id
			order by MO.MorbusPalliat_id desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Проверка наличия протокола ВК и загрузка данных из него
	 */
	function checkEvnVk($data)
	{
		$vk_data = $this->getFirstRowFromQuery("
			select top 1 
				EVK.EvnVK_id
				,convert(varchar(10), EVK.EvnVK_setDT, 120) as MorbusPalliat_VKDate
				,PEVK.PalliatEvnVK_id
				,PEVK.PalliativeType_id
				,PEVK.PalliatEvnVK_TextTIR as MorbusPalliat_TextTIR
				,left(PEVKD.PalliatEvnVKMainSyndrome, len(PEVKD.PalliatEvnVKMainSyndrome)-1) MainSyndrome
				,left(PEVKD.PalliatEvnVKTechnicInstrumRehab, len(PEVKD.PalliatEvnVKTechnicInstrumRehab)-1) TechnicInstrumRehab
			from v_EvnVK EVK
			inner join PalliatEvnVK PEVK (nolock) on PEVK.EvnVK_id = EVK.EvnVK_id
			outer apply (
					select top 1 (
					select cast(MainSyndrome_id as varchar)+',' as 'data()'
					from PalliatEvnVKMainSyndromeLink (nolock)
					where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
					for xml path('')
				) as PalliatEvnVKMainSyndrome, (
					select cast(TechnicInstrumRehab_id as varchar)+',' as 'data()'
					from PalliatEvnVKTechnicInstrumRehabLink (nolock)
					where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
					for xml path('')
				) as PalliatEvnVKTechnicInstrumRehab
			) PEVKD
			where 
				EVK.Person_id = :Person_id and 
				EVK.Diag_id = :Diag_id and 
				EVK.CauseTreatmentType_id = 21 
			order by EvnVK_setDate desc
		", [
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id']
		]);

		if ($vk_data === false) return false;

		$vk_data['PalliatFamilyCareList'] = $this->queryResult("select top 3 FamilyRelationType_id, PalliatFamilyCare_Age, PalliatFamilyCare_Phone from PalliatFamilyCare where EvnVK_id = ? and FamilyRelationType_id is not null", [$vk_data['EvnVK_id']]);
		$vk_data['MorbusPalliat_IsFamCare'] = count($vk_data['PalliatFamilyCareList']) ? 2 : 1;

		$vk_data['MorbusPalliat_IsTIR'] = !empty($vk_data['TechnicInstrumRehab']) ? 2 : 1;

		$vk_data['TechnicInstrumRehab'] = empty($vk_data['TechnicInstrumRehab']) ? [] : explode(',', $vk_data['TechnicInstrumRehab']);
		foreach($vk_data['TechnicInstrumRehab'] as &$row) {
			$row = ['id'=> $row, 'date' => null];
		}

		$vk_data['TechnicInstrumRehab'] = json_encode($vk_data['TechnicInstrumRehab']);

		return $vk_data;
	}
	/**
	 * @param array $data
	 * @return array|false
	 */
	function checkCanInclude($data) {

		$query = "
			select top 1
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				ps.Person_Firname,
				ps.Person_Secname,
				ps.Person_Surname,
				convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday
			from v_PersonState ps (nolock)
				left join v_PersonRegisterType prt (nolock) on prt.PersonRegisterType_SysNick = 'palliat'
				left join v_PersonRegister pr (nolock) on 
					pr.Person_id = ps.Person_id and 
					pr.PersonRegisterType_id = prt.PersonRegisterType_id and 
					pr.PersonRegister_disDate is null
			where 
				ps.Person_id = :Person_id and 
				pr.PersonRegister_id is null
		";

		return $this->queryResult($query, $data);
	}
	
	/**
	 * @param array $data
	 * @return array|false
	 */
	function getDirectionMSE($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
		select top 1
			EvnPrescrMse.EvnPrescrMse_id,
			EvnPrescrMse.Person_id,
			EvnPrescrMse.Server_id,
			EvnPrescrMse.EvnVK_id,
			EM.EvnMse_id,
			'№ ' + cast(EM.EvnMse_NumAct as varchar(10)) + ' от ' + convert(varchar(10), cast(EM.EvnMse_setDT as datetime), 104) as EvnMse,
			'Направление на МСЭ' as EvnClass_Name,
			isnull(convert(varchar,EvnPrescrMse.EvnPrescrMse_issueDT,104),'') as date_beg
			--, case when ISNULL(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
		from
			v_EvnPrescrMse EvnPrescrMse with (nolock)
			left join v_EvnMse EM (nolock) on EvnPrescrMse.EvnPrescrMse_id = EM.EvnPrescrMse_id
			left join v_EvnQueue eq with (nolock) on eq.EvnQueue_id = EvnPrescrMse.EvnQueue_id
			outer apply (
				select top 1 MseDirectionAimType_Code
				from v_MseDirectionAimTypeLink MDATL with (nolock)
				left join v_MseDirectionAimType MDAT with(nolock) on MDATL.MseDirectionAimType_id = MDAT.MseDirectionAimType_id
				where (1=1)
				and MDATL.EvnPrescrMse_id = EvnPrescrMse.EvnPrescrMse_id
				and MDAT.MseDirectionAimType_Code = 1 -- цель направления Установление группы инвалидности
			) as M
		where 
			EvnPrescrMse.Person_id = :Person_id
			and (eq.EvnQueue_id is null or eq.EvnQueue_failDT is null)
			and ISNULL(EvnPrescrMse.EvnPrescrMse_IsArchive, 1) = 1 -- только актуальные
			and M.MseDirectionAimType_Code is not null

		order by EvnPrescrMse.EvnPrescrMse_issueDT desc";

		return $this->queryResult($query, $params);
	}
}