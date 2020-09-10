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
class EvnNotifyHIV_model extends swPgModel
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
				,EN.{$tableName}_id as \"EvnNotifyBase_id\"
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
		$query = "
			select
				ENO.EvnNotifyHIV_id as \"EvnNotifyHIV_id\",
				ENO.EvnNotifyHIV_pid as \"EvnNotifyHIV_pid\",
				ENO.Morbus_id as \"Morbus_id\",
				ENO.Server_id as \"Server_id\",
				ENO.PersonEvn_id as \"PersonEvn_id\",
				ENO.Person_id as \"Person_id\",
				Diag.Diag_id as \"Diag_id\",
				Diag.Diag_FullName as \"Diag_Name\",
				to_char(ENO.EvnNotifyHIV_setDT, 'dd.mm.yyyy') as \"EvnNotifyHIV_setDT\",
				ENO.MedPersonal_id as \"MedPersonal_id\",
				gr.HIVContingentType_id as \"HIVContingentType_pid\",
				con.HIVContingentType_id as \"HIVContingentType_id\",
				to_char(MV.MorbusHIV_confirmDate, 'dd.mm.yyyy') as \"MorbusHIV_confirmDate\",
				MV.MorbusHIV_EpidemCode as \"MorbusHIV_EpidemCode\",
				lab.MorbusHIVLab_id as \"MorbusHIVLab_id\",
				to_char(lab.MorbusHIVLab_BlotDT, 'dd.mm.yyyy') as \"MorbusHIVLab_BlotDT\",
				lab.MorbusHIVLab_TestSystem as \"MorbusHIVLab_TestSystem\",
				lab.MorbusHIVLab_BlotNum as \"MorbusHIVLab_BlotNum\",
				lab.MorbusHIVLab_BlotResult as \"MorbusHIVLab_BlotResult\",
				to_char(lab.MorbusHIVLab_IFADT, 'dd.mm.yyyy') as \"MorbusHIVLab_IFADT\",
				lab.MorbusHIVLab_IFAResult as \"MorbusHIVLab_IFAResult\",
				lab.Lpu_id as \"Lpuifa_id\",
				lab.LabAssessmentResult_iid as \"LabAssessmentResult_iid\",
				lab.LabAssessmentResult_cid as \"LabAssessmentResult_cid\"
			from
				v_EvnNotifyHIV ENO
				left join v_EvnVizitPL PL on ENO.EvnNotifyHIV_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST on ENO.EvnNotifyHIV_pid = ST.EvnSection_id
				inner join v_Diag Diag on coalesce(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				left join v_Morbus M on M.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIV MV on MV.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIVLab lab on ENO.EvnNotifyHIV_id = lab.EvnNotifyBase_id
					and lab.MorbusHIV_id is null
				left join v_MorbusHIVContingent gr on ENO.EvnNotifyHIV_id = gr.EvnNotifyBase_id
					and gr.MorbusHIV_id is null
					and gr.HIVContingentType_id in (100,200)
				left join v_MorbusHIVContingent con on ENO.EvnNotifyHIV_id = con.EvnNotifyBase_id
					and con.MorbusHIV_id is null
					and con.HIVContingentType_id not in (100,200)
			where
				ENO.EvnNotifyHIV_id = ?
		";
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

			$res = $this->getFirstResultFromQuery("
				select
					MorbusHIV_id as \"MorbusHIV_id\"
				from v_MorbusHIV
				where Morbus_id = :Morbus_id
				limit 1				
			", $params);

			if ($res) {
				$params['MorbusHIV_id'] = $res;
				$query = "
				update MorbusHiv
					set
						MorbusHIV_confirmDate = :MorbusHIV_confirmDate,
						MorbusHIV_EpidemCode = :MorbusHIV_EpidemCode
					where
						MorbusHiv_id = :MorbusHiv_id
				returning 0 as \"Error_Code\", '' as \"Error_Msg\";
			";
			} else {
				$result = false;
			}


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
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnNotifyHIV_del(
				EvnNotifyHIV_id := :EvnNotifyHIV_id,
				pmUser_id := :pmUser_id
			)
		";
		
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
				ENO.EvnNotifyHIV_id as \"EvnNotifyHIV_id\",
				ENO.Person_id as \"Person_id\",
				lab.MorbusHIVLab_id as \"MorbusHIVLab_id\",
				MV.MorbusHIV_id as \"MorbusHIV_id\",
				Diag.Diag_id as \"Diag_id\",
				gr.HIVContingentType_id as \"HIVContingentTypeP_id\",
				(
					select
						string_agg(cast(cont.HIVContingentType_id as varchar), ',') 
					from v_MorbusHIVContingent con
						left join v_HIVContingentType cont on cont.HIVContingentType_id = con.HIVContingentType_id
					where MV.MorbusHIV_id = con.MorbusHIV_id
						and con.EvnNotifyBase_id is null
						and con.HIVContingentType_id != gr.HIVContingentType_id
				) as \"HIVContingentType_Name_list\"
				,to_char(lab.MorbusHIVLab_BlotDT, 'dd.mm.yyyy') as \"MorbusHIVLab_BlotDT\"
				,lab.MorbusHIVLab_BlotNum as \"MorbusHIVLab_BlotNum\"
				,lab.MorbusHIVLab_BlotResult as \"MorbusHIVLab_BlotResult\"
				,lab.MorbusHIVLab_TestSystem as \"MorbusHIVLab_TestSystem\"
				,lab.LabAssessmentResult_iid as \"LabAssessmentResult_iid\"
				,lab.Lpu_id as \"Lpu_id\"
				,to_char(lab.MorbusHIVLab_IFADT, 'dd.mm.yyyy') as \"MorbusHIVLab_IFADT\"
				,lab.MorbusHIVLab_IFAResult as \"MorbusHIVLab_IFAResult\"
				,to_char(MV.MorbusHIV_confirmDate, 'dd.mm.yyyy') as \"MorbusHIV_confirmDate\"
				,ENO.MedPersonal_id as \"MedPersonal_id\"
				,to_char(ENO.EvnNotifyHIV_setDT, 'dd.mm.yyyy') as \"EvnNotifyHIV_setDT\"
				,PR.PersonRegisterType_id as \"PersonRegisterType_id\"
				,ENO.Lpu_niid as \"Lpu_niid\"
				,ENO.MedPersonal_niid as \"MedPersonal_niid\"
				,ENO.PersonRegisterFailIncludeCause_id as \"PersonRegisterFailIncludeCause_id\"
			from
				v_EvnNotifyHIV ENO
				left join v_EvnVizitPL PL on ENO.EvnNotifyHIV_pid = PL.EvnVizitPL_id
				left join v_EvnSection ST on ENO.EvnNotifyHIV_pid = ST.EvnSection_id
				inner join v_Diag Diag on coalesce(PL.Diag_id,ST.Diag_id) = Diag.Diag_id
				left join v_MorbusHIVLab lab on ENO.EvnNotifyHIV_id = lab.EvnNotifyBase_id
					and lab.MorbusHIV_id is null
				left join v_MorbusHIV MV on MV.Morbus_id = ENO.Morbus_id
				left join v_MorbusHIVContingent gr on MV.MorbusHIV_id = gr.MorbusHIV_id
					and gr.EvnNotifyBase_id is null
					and gr.HIVContingentType_id in (100,200)
				left join v_PersonRegister PR on PR.EvnNotifyBase_id = lab.EvnNotifyBase_id
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
