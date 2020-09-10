<?php
/**
* EvnNotifyHIV_model - модель для работы с таблицей EvnNotifyHIV ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ (форма N 266/У-88)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Permyakov 
* @version      12.2012
 *
 * Магические свойства
 * @property-read int $MorbusType_id
 * @property-read string $morbusTypeSysNick
 * 
 * @property MorbusHIV_model MorbusHIV
 *
 * @todo extends EvnNotifyAbstract_model
 */
class EvnNotifyHIV_model extends swModel
{
	protected $_MorbusType_id = null;
	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EvnNotifyHIV';
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'hiv';
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
	* Получение данных для проверки перед созданием оперативного донесения
	* Используется в Common_model->signedDocument
	*/
	function checkEvnNotifyHIV($data)
	{
		$tableName = 'EvnNotifyHIV';
		$this->load->library('swMorbus');
		return swMorbus::getStaticMorbusCommon()->checkExistsExtended('hiv', $data['Person_id'], null,"
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
				ENO.EvnNotifyHIV_id,
				ENO.EvnNotifyHIV_pid,
				ENO.Morbus_id,
				ENO.Server_id,
				ENO.PersonEvn_id,
				ENO.Person_id,
				Diag.Diag_id,
				Diag.Diag_FullName as Diag_Name,
				convert(varchar,ENO.EvnNotifyHIV_setDT,104) as EvnNotifyHIV_setDT,
				ENO.MedPersonal_id,
				
				gr.HIVContingentType_id as HIVContingentType_pid,
				con.HIVContingentType_id,
				
				convert(varchar(10), MV.MorbusHIV_confirmDate, 104) as MorbusHIV_confirmDate,
				MV.MorbusHIV_EpidemCode,
				
				lab.MorbusHIVLab_id,
				convert(varchar,lab.MorbusHIVLab_BlotDT,104) as MorbusHIVLab_BlotDT,
				lab.MorbusHIVLab_TestSystem,
				lab.MorbusHIVLab_BlotNum,
				lab.MorbusHIVLab_BlotResult,
				convert(varchar,lab.MorbusHIVLab_IFADT,104) as MorbusHIVLab_IFADT,
				lab.MorbusHIVLab_IFAResult,
				lab.Lpu_id as Lpuifa_id,
				lab.LabAssessmentResult_iid,
				lab.LabAssessmentResult_cid
			from
				v_EvnNotifyHIV ENO with (nolock)
				left join v_EvnVizitPL PL with (nolock) on ENO.EvnNotifyHIV_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST with (nolock) on ENO.EvnNotifyHIV_pid = ST.EvnSection_id
				inner join v_Diag Diag with (nolock) on ISNULL(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				left join v_Morbus M with(nolock) on M.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIV MV with(nolock) on MV.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIVLab lab with (nolock) on ENO.EvnNotifyHIV_id = lab.EvnNotifyBase_id and lab.MorbusHIV_id is null
				left join v_MorbusHIVContingent gr with (nolock) on ENO.EvnNotifyHIV_id = gr.EvnNotifyBase_id and gr.MorbusHIV_id is null and gr.HIVContingentType_id in (100,200)
				left join v_MorbusHIVContingent con with (nolock) on ENO.EvnNotifyHIV_id = con.EvnNotifyBase_id and con.MorbusHIV_id is null and con.HIVContingentType_id not in (100,200)
			where
				ENO.EvnNotifyHIV_id = ?
		';
		//echo getDebugSQL($query, array($data['EvnNotifyHIV_id']));exit;
		$res = $this->db->query($query, array($data['EvnNotifyHIV_id']));
		if ( is_object($res) )
		{
			$tmp = $res->result('array');
			if(empty($tmp))
			{
				return $tmp;
			}
			else
			{
				$id_list = array();
				foreach($tmp as $row){
					$id_list[] = $row['HIVContingentType_id'] - $row['HIVContingentType_pid'];
				}
				unset($tmp[0]['HIVContingentType_id']);
				$tmp[0]['HIVContingentType_id_list'] = implode(',',$id_list);
				return array($tmp[0]);
			}
		}
		else
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
			if (empty($data[$tableName . '_pid'])) {
				throw new Exception('Не передан параметр Учетный документ', 500);
			}
			if (empty($data['EvnNotifyHIV_setDT'])) {
				throw new Exception('Не передан параметр Дата заполнения извещения', 500);
			}
			if ( false == empty($data['EvnNotifyHIV_id']) ) {
				throw new Exception('Редактирование извещения не предусмотрено!');
			}

			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $data[$tableName . '_pid'], $data['session']);
			$data['MorbusType_id'] = $tmp['MorbusType_id'];
			$data['Morbus_id'] = $tmp['Morbus_id'];
			$data['Morbus_Diag_id'] = $tmp['Diag_id'];
			$data['MorbusHIV_id'] = $tmp['MorbusHIV_id'];

			$queryParams = array(
				$tableName . '_pid' => $data[$tableName . '_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Morbus_id' => $data['Morbus_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'EvnNotifyHIV_setDT' => $data['EvnNotifyHIV_setDT'],
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
			$this->onAfterSaveEvnNotify($data);
			$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
				'EvnNotifyBase_id' => $data[$pk],
				'EvnNotifyBase_pid' => $data[$tableName . '_pid'],
				'EvnNotifyBase_setDate' => $data['EvnNotifyHIV_setDT'],
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

			$this->load->model('MorbusHIV_model','MorbusHIV');
			$data['EvnNotifyBase_id'] = $data[$pk];
			//Сохраняем MorbusHIVLab на извещении и копируем данные на заболевание
			$tmp = $this->MorbusHIV->saveMorbusHIVLabWithEvnNotifyBase_id($data);
			if ( isset($tmp[0]['Error_Msg']) ) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$this->_saveResponse['MorbusHIVLab_id'] = $tmp[0]['MorbusHIVLab_id'];
			if (isset($tmp[0]['MorbusHIVLab_id_copy'])) {
				$this->_saveResponse['MorbusHIVLab_id_copy'] = $tmp[0]['MorbusHIVLab_id_copy'];
			}

			//Сохраняем MorbusHIVContingent на извещении и копируем данные на заболевание
			$tmp = $this->MorbusHIV->saveMorbusHIVContingentListWithEvnNotifyBase_id($data);
			if ( isset($tmp[0]['Error_Msg']) ) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
			// Сохранение ВИЧ в извещении по туберкулезу
			$this->load->model('EvnNotifyTub_model','EvnNotifyTub');
			$evnNotifyTub = $this->EvnNotifyTub->getEvnNotifyTub(array('Person_id'=>$data['Person_id']));
			if(is_array($evnNotifyTub) && count($evnNotifyTub)>0){
				foreach ($evnNotifyTub as $value) {
					$tubDiagSop = $this->EvnNotifyTub->loadTubDiagSopWithDescr($value['EvnNotifyTub_id']);
					if(strpos($tubDiagSop['TubDiagSop'],'8') === false){
						$tubDiagSop['TubDiagSop'] = $tubDiagSop['TubDiagSop'].',8';
						$tubDiagSop['pmUser_id'] = $data['pmUser_id'];
						$this->EvnNotifyTub->saveTubDiagSop($tubDiagSop);
					}
				}
			}
			$this->_saveResponse['MorbusHIVContingent_id_list'] = $tmp[0]['MorbusHIVContingent_id_list'];
			$this->_saveResponse['MorbusHIVContingent_id_copy_list'] = $tmp[0]['MorbusHIVContingent_id_copy_list'];

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
			!empty($data['MorbusHIV_confirmDate']) ||
			!empty($data['MorbusHIV_EpidemCode'])
		)) {
			$params = array(
				'Morbus_id' => $data['Morbus_id'],
				'MorbusHIV_confirmDate' => !empty($data['MorbusHIV_confirmDate'])?$data['MorbusHIV_confirmDate']:null,
				'MorbusHIV_EpidemCode' => !empty($data['MorbusHIV_EpidemCode'])?$data['MorbusHIV_EpidemCode']:null,
			);

			$query = "
				declare @Error_Code bigint = null
				declare @Error_Message varchar(4000) = ''
				set nocount on
				begin try
					declare @MorbusHIV_id bigint = (
						select MorbusHIV_id
						from v_MorbusHIV with(nolock)
						where Morbus_id = :Morbus_id
					)
					if (@MorbusHiv_id is not null)
					update
						MorbusHiv with(rowlock)
					set
						MorbusHIV_confirmDate = :MorbusHIV_confirmDate,
						MorbusHIV_EpidemCode = :MorbusHIV_EpidemCode
					where
						MorbusHiv_id = @MorbusHiv_id
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
	 * Method description
	 */
	function del($data)
	{
		$query = '
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :EvnNotifyHIV_id;
			exec p_EvnNotifyHIV_del
				@EvnNotifyHIV_id = @Res,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as EvnNotifyHIV_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		';
		
		$queryParams = array(
			'EvnNotifyHIV_id' => $data['EvnNotifyHIV_id'],
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
	 * Получение  оперативного донесения о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ
	 */
	function getEvnNotifyHivAPI($data){
		$param = array();
		$where = '';
		if(!empty($data['Person_id'])){
			$params['Person_id'] = $data['Person_id'];
			$where .= ' AND ENO.Person_id = :Person_id ';
		}
		if(!empty($data['EvnNotifyHIV_id'])){
			$params['EvnNotifyHIV_id'] = $data['EvnNotifyHIV_id'];
			$where .= ' AND ENO.EvnNotifyHIV_id = :EvnNotifyHIV_id ';
		}
		if(count($params) == 0){
			return false;
		}
		$query = "
			SELECT
				ENO.EvnNotifyHIV_id,
				ENO.Person_id,
				lab.MorbusHIVLab_id,
				MV.MorbusHIV_id,
				Diag.Diag_id,
				gr.HIVContingentType_id as HIVContingentTypeP_id,
				stuff((
					select concat(',',cont.HIVContingentType_id ) 
					from v_MorbusHIVContingent con with (nolock)
						left join v_HIVContingentType cont with (nolock) on cont.HIVContingentType_id = con.HIVContingentType_id
					where MV.MorbusHIV_id = con.MorbusHIV_id and con.EvnNotifyBase_id is null and con.HIVContingentType_id != gr.HIVContingentType_id
					for XML path('')
				),1,1,'') HIVContingentType_Name_list,
				convert(varchar,lab.MorbusHIVLab_BlotDT,104) as MorbusHIVLab_BlotDT
				,lab.MorbusHIVLab_BlotNum
				,lab.MorbusHIVLab_BlotResult
				,lab.MorbusHIVLab_TestSystem
				,lab.LabAssessmentResult_iid
				,lab.Lpu_id
				,convert(varchar,lab.MorbusHIVLab_IFADT,104) as MorbusHIVLab_IFADT
				,lab.MorbusHIVLab_IFAResult
				,convert(varchar(10), MV.MorbusHIV_confirmDate, 104) as MorbusHIV_confirmDate
				,ENO.MedPersonal_id
				,convert(varchar,ENO.EvnNotifyHIV_setDT,104) as EvnNotifyHIV_setDT
				,PR.PersonRegisterType_id
				,ENO.Lpu_niid
				,ENO.MedPersonal_niid
				,ENO.PersonRegisterFailIncludeCause_id
			from
				v_EvnNotifyHIV ENO with (nolock)
				left join v_EvnVizitPL PL with (nolock) on ENO.EvnNotifyHIV_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST with (nolock) on ENO.EvnNotifyHIV_pid = ST.EvnSection_id
				inner join v_Diag Diag with (nolock) on ISNULL(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				left join v_MorbusHIVLab lab with (nolock) on ENO.EvnNotifyHIV_id = lab.EvnNotifyBase_id and lab.MorbusHIV_id is null
				left join v_MorbusHIV MV with(nolock) on MV.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIVContingent gr with (nolock) on MV.MorbusHIV_id = gr.MorbusHIV_id and gr.EvnNotifyBase_id is null and gr.HIVContingentType_id in (100,200)
				left join v_PersonRegister PR with(nolock) on PR.EvnNotifyBase_id = lab.EvnNotifyBase_id
			WHERE 1=1
				{$where}
		";
		//echo getDebugSQL($query, $params);exit;
		$res = $this->db->query($query, $params);
		if ( is_object($res) )
		{
			return $res->result('array');
		}else{
			return false;
		}
	}
}
