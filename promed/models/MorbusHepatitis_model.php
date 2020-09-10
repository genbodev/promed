<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MorbusHepatitis_model - модель для MorbusHepatitis
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Пермяков Александр
* @version      10.2012 года
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
*/

class MorbusHepatitis_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	private $entityFields = array(
		'MorbusHepatitisLabConfirm' => array( //allow Deleted
			'MorbusHepatitis_id'
			,'MorbusHepatitisLabConfirm_setDT'
			,'HepatitisLabConfirmType_id'
			,'MorbusHepatitisLabConfirm_Result'
			,'UslugaComplex_id'
			,'Evn_id'
		),
		'MorbusHepatitisFuncConfirm' => array( //allow Deleted
			'MorbusHepatitis_id'
			,'MorbusHepatitisFuncConfirm_setDT'
			,'HepatitisFuncConfirmType_id'
			,'MorbusHepatitisFuncConfirm_Result'
			,'UslugaComplex_id'
			,'Evn_id'
		),
		'MorbusHepatitis' => array( //allow Deleted
			'Morbus_id'
			,'MorbusHepatitis_EpidNum'
			,'HepatitisEpidemicMedHistoryType_id'
		),
		'Morbus' => array( //allow Deleted
			'MorbusBase_id'
			,'Evn_pid'
			,'Diag_id'
			,'MorbusKind_id'
			,'Morbus_Name'
			,'Morbus_Nick'
			,'Morbus_disDT'
			,'Morbus_setDT'
			,'MorbusResult_id'
		),
		'MorbusBase' => array( //allow Deleted
			'Person_id'
			,'Evn_pid'
			,'MorbusType_id'
			,'MorbusBase_setDT'
			,'MorbusBase_disDT'
			,'MorbusResult_id'
		)
	);

	protected $_MorbusType_id = null;//5

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
		return 'hepa';
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
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'HepatitisRegistry';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusHepatitis';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Сохранение специфики
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы объектов, которые были обновлены или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusHepatitis_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_setDT','Morbus_disDT','MorbusBase_setDT','MorbusBase_disDT');
		if(isset($data['field_notedit_list']) && is_array($data['field_notedit_list']))
		{
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}
		foreach($this->entityFields as $entity => $l_arr) {
			$allow_save = false;
			foreach($data as $key => $value) {
				if(in_array($key, $l_arr) && !in_array($key, $not_edit_fields))
				{
					$allow_save = true;
					break;
				}
			}

			if( $allow_save && !empty($data[$entity.'_id']) )
			{
				$q = 'select top 1 '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' WITH (NOLOCK) where '. $entity .'_id = :'. $entity .'_id';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$r = $this->db->query($q, $data);
				if (is_object($r))
				{
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 )
					{
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					foreach($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime)
						{
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data)?$data[$key]:$value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key]=='0')?null:$p[$key];
					}
				}
				else
				{
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach($l_arr as $key) {
					$field_str .= '
						@'. $key .' = :'. $key .',';
				}
				$q = '
					declare
						@'. $entity .'_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @'. $entity .'_id = :'. $entity .'_id;
					exec dbo.p_'. $entity .'_upd
						@'. $entity .'_id = @'. $entity .'_id output, '. $field_str .'
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @'. $entity .'_id as '. $entity .'_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			}
			else
			{
				continue;
			}
		}
		if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
			$this->load->library('swMorbus');
			$tmp = swMorbus::updateMorbusIntoEvn(array(
				'Evn_id' => $data['Evn_pid'],
				'Morbus_id' => $data['Morbus_id'],
				'session' => $data['session'],
				'mode' => 'onAfterSaveMorbusSpecific',
			));
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

 	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode 
	 *	- check_by_personregister - это создание нового заболевания при ручном вводе новой записи регистра из формы "Регистр по ..." (если есть открытое заболевание, то ничего не сохраняем. В регистре сохранится связь с открытым или созданным заболевание)
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- check_by_evn - это создание нового заболевания при редактировании данных движения/посещения (если есть открытое заболевание и диагноз уточнился и посещение/движение актуально, то сохраняем диагноз и привязываем заболевание к этому посещению/движению)
	*/
	private function checkParams($data)
	{
		if( empty($data['Mode']) )
		{
			throw new Exception('Не указан режим сохранения');
		}
		$check_fields_list = array();
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
			,'Person_id' => 'Идентификатор человека'
			,'Evn_pid' => 'Идентификатор движения/посещения'
			,'pmUser_id' => 'Идентификатор пользователя'
			,'Morbus_id' => 'Идентификатор заболевания'
			,'MorbusHepatitis_id' => 'Идентификатор специфики заболевания'
			,'Morbus_setDT' => 'Дата заболевания'
		);
		switch ($data['Mode']) {
			case 'personregister_viewform':
				$check_fields_list = array('MorbusHepatitis_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusHepatitis_id','Morbus_id','Evn_pid','pmUser_id'); 
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 )
		{
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}

	/**
	 * Сохранение специфики заболевания
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * 3) Mode 
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 * @param bool $isAllowTransaction
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function saveMorbusSpecific($data, $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = false;
			$data = $this->checkParams($data);

			$data['Evn_aid'] = null;
			if (in_array($data['Mode'],array(
				'evnsection_viewform','evnvizitpl_viewform'
			))) {
				// Проверка существования у человека актуального учетного документа с данной группой диагнозов для привязки к нему заболевания и определения последнего диагноза заболевания
				if (empty($data['Evn_pid'])) {
					$data['Evn_pid'] = null;
				}
				$this->load->library('swMorbus');
				$result = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], null, null);
				if ( !empty($result) ) {
					//учетный документ найден
					$data['Evn_aid'] = $result[0]['Evn_id'];
					$data['Diag_id'] = $result[0]['Diag_id'];
					$data['Person_id'] = $result[0]['Person_id'];
				} else {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
			}

			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа
				// или из панели просмотра в форме записи регистра, то сохраняем данные
				// Стартуем транзакцию
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}

				//update таблиц Morbus, MorbusHepatitis
				$tmp = $this->updateMorbusSpecific($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;

				$this->commitTransaction();
				return $response;
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));	
		}
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
		$queryParams['HepatitisEpidemicMedHistoryType_id'] = isset($data['HepatitisEpidemicMedHistoryType_id'])?$data['HepatitisEpidemicMedHistoryType_id']:null;
		$queryParams['MorbusHepatitis_EpidNum'] = isset($data['MorbusHepatitis_EpidNum'])?$data['MorbusHepatitis_EpidNum']:null;

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@pmUser_id bigint = :pmUser_id;

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					@HepatitisEpidemicMedHistoryType_id = :HepatitisEpidemicMedHistoryType_id,
					@MorbusHepatitis_EpidNum = :MorbusHepatitis_EpidNum,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @{$tableName}_id as {$tableName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
			else
			begin
				select @{$tableName}_id as {$tableName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
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
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	* Получение данных для раздела "Очередь" специфики по гепатиту
	*/
	function getMorbusHepatitisQueueViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = '
			select 
				case when MH.Morbus_disDT is null and isnull(EvnEdit.Evn_IsSigned,1) = 1 then \'edit\' else \'view\' end as AccessType
				,MHQ.MorbusHepatitisQueue_id
				,HQT.HepatitisQueueType_Name
				,MHQ.MorbusHepatitisQueue_Num
				,IsCure.YesNo_Name as MorbusHepatitisQueue_IsCure
				,MHQ.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisQueue MHQ  with (nolock)
				left join v_HepatitisQueueType HQT with (nolock) on MHQ.HepatitisQueueType_id = HQT.HepatitisQueueType_id
				left join v_YesNo IsCure with (nolock) on isnull(MHQ.MorbusHepatitisQueue_IsCure,1) = IsCure.YesNo_id
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHQ.MorbusHepatitis_id
				inner join v_MorbusBase MB with (nolock) on MH.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				MHQ.MorbusHepatitis_id = :MorbusHepatitis_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "План лечения Гепатита C" специфики по гепатиту
	*/
	function getMorbusHepatitisPlanViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = '
			select 
				case when MH.Morbus_disDT is null and isnull(EvnEdit.Evn_IsSigned,1) = 1 then \'edit\' else \'view\' end as AccessType
				,case when exists(select top 1 MHP.MorbusHepatitisPlan_id from v_MorbusHepatitisPlan MHP (nolock) where MHP.MorbusHepatitis_id = MH.MorbusHepatitis_id and ISNULL(MHP.MorbusHepatitisPlan_Treatment, 1) = 1) then 1 else 0 end as hasOpenPlan
				,MHP.MorbusHepatitisPlan_id
				,MHP.MorbusHepatitisPlan_Year
				,MHP.MorbusHepatitisPlan_Month
				,MCT.MedicalCareType_Name
				,L.Lpu_Nick
				,Treatment.YesNo_Name as MorbusHepatitisPlan_Treatment
				,MHP.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisPlan MHP  with (nolock)
				left join fed.v_MedicalCareType MCT with (nolock) on MHP.MedicalCareType_id = MCT.MedicalCareType_id
				left join v_YesNo Treatment with (nolock) on isnull(MHP.MorbusHepatitisPlan_Treatment,1) = Treatment.YesNo_id
				left join v_Lpu L with (nolock) on L.Lpu_id = MHP.Lpu_id
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHP.MorbusHepatitis_id
				inner join v_MorbusBase MB with (nolock) on MH.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				MHP.MorbusHepatitis_id = :MorbusHepatitis_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "Вакцинация" специфики по гепатиту
	*/
	function getMorbusHepatitisVaccinationViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = '
			select 
				case when ((MHV.Evn_id is null and MB.Person_id = :Evn_id) or (MHV.Evn_id = :Evn_id and isnull(EvnEdit.Evn_IsSigned,1) = 1)) and MH.Morbus_disDT is null then \'edit\' else \'view\' end as AccessType
				,MHV.MorbusHepatitisVaccination_id
				,convert(varchar(10),MHV.MorbusHepatitisVaccination_setDT,104) as MorbusHepatitisVaccination_setDate
				,Drug.Drug_Name
				,MHV.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisVaccination MHV with (nolock)
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHV.MorbusHepatitis_id
				inner join v_MorbusBase MB with (nolock) on MH.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
				left join rls.v_Drug Drug with (nolock) on MHV.Drug_id = Drug.Drug_id
			where
				MHV.MorbusHepatitis_id = :MorbusHepatitis_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "Лечение" специфики по гепатиту
	*/
	function getMorbusHepatitisCureViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = '
			select 
				case when ((MHC.Evn_id is null and MB.Person_id = :Evn_id) or (MHC.Evn_id = :Evn_id and isnull(EvnEdit.Evn_IsSigned,1) = 1)) and MH.Morbus_disDT is null then \'edit\' else \'view\' end as AccessType
				,MHC.MorbusHepatitisCure_id
				,convert(varchar(10),MHC.MorbusHepatitisCure_begDT,104) as MorbusHepatitisCure_begDate
				,convert(varchar(10),MHC.MorbusHepatitisCure_endDT,104) as MorbusHepatitisCure_endDate
				,Drug.Drug_Name
				,HRC.HepatitisResultClass_Name
				,HSET.HepatitisSideEffectType_Name
				,MHC.MorbusHepatitisCureEffMonitoring_id
				,MHC.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisCure MHC  with (nolock)
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id
				inner join v_MorbusBase MB with (nolock) on MH.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
				left join rls.v_Drug Drug with (nolock) on MHC.Drug_id = Drug.Drug_id
				left join v_HepatitisResultClass HRC with (nolock) on MHC.HepatitisResultClass_id = HRC.HepatitisResultClass_id
				left join v_HepatitisSideEffectType HSET with (nolock) on MHC.HepatitisSideEffectType_id = HSET.HepatitisSideEffectType_id
			where
				MHC.MorbusHepatitis_id = :MorbusHepatitis_id
		';
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "Инструментальные подтверждения" специфики по гепатиту
	*/
	function getMorbusHepatitisFuncConfirmViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = "
			select
				case when ((EvnUsluga.EvnUsluga_pid is null and EvnUsluga.Person_id = :Evn_id) or (EvnUsluga.EvnUsluga_pid = :Evn_id and isnull(EvnEdit.Evn_IsSigned,1) = 1)) and MH.Morbus_disDT is null then 'edit' else 'view' end as AccessType
				,EvnUsluga.EvnUsluga_id
				,EvnUsluga.EvnClass_SysNick
				,MHC.MorbusHepatitisFuncConfirm_id
				,convert(varchar(10),MHC.MorbusHepatitisFuncConfirm_setDT,104) as MorbusHepatitisFuncConfirm_setDate
				,MHC.MorbusHepatitisFuncConfirm_Result
				,MHC.UslugaComplex_id
				,HCT.HepatitisFuncConfirmType_Name
				,MHC.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisFuncConfirm MHC with (nolock)
				inner join v_EvnUsluga EvnUsluga with (nolock) on MHC.Evn_id = EvnUsluga.EvnUsluga_id
				left join v_HepatitisFuncConfirmType HCT with (nolock) on HCT.HepatitisFuncConfirmType_id = MHC.HepatitisFuncConfirmType_id
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and EvnUsluga.Person_id != :Evn_id
			where
				MHC.MorbusHepatitis_id = :MorbusHepatitis_id
		";
		//echo getDebugSQL($query, $params); exit();

		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "Лабораторные подтверждения" специфики по гепатиту
	*/
	function getMorbusHepatitisLabConfirmViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = "
			select
				case when ((EvnUsluga.EvnUsluga_pid is null and EvnUsluga.Person_id = :Evn_id) or (EvnUsluga.EvnUsluga_pid = :Evn_id and isnull(EvnEdit.Evn_IsSigned,1) = 1)) and MH.Morbus_disDT is null then 'edit' else 'view' end as AccessType
				,EvnUsluga.EvnUsluga_id
				,EvnUsluga.EvnClass_SysNick
				,MHC.MorbusHepatitisLabConfirm_id
				,convert(varchar(10),MHC.MorbusHepatitisLabConfirm_setDT,104) as MorbusHepatitisLabConfirm_setDate
				,MHC.MorbusHepatitisLabConfirm_Result
				,MHC.UslugaComplex_id
				,HCT.HepatitisLabConfirmType_Name
				,MHC.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisLabConfirm MHC with (nolock)
				inner join v_EvnUsluga EvnUsluga with (nolock) on MHC.Evn_id = EvnUsluga.EvnUsluga_id
				left join v_HepatitisLabConfirmType HCT with (nolock) on HCT.HepatitisLabConfirmType_id = MHC.HepatitisLabConfirmType_id
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and EvnUsluga.Person_id != :Evn_id
			where
				MHC.MorbusHepatitis_id = :MorbusHepatitis_id
		";
		//echo getDebugSQL($query, $params); exit();

		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Получение данных для раздела "Диагноз" специфики по гепатиту
	*/
	function getMorbusHepatitisDiagViewData($data)
	{
		if(empty($data['MorbusHepatitis_id']) or $data['MorbusHepatitis_id'] < 0)
		{
			return array();
		}
		$query = '
			select 
				case when ((MHD.Evn_id is null and MB.Person_id = :Evn_id) or (MHD.Evn_id = :Evn_id and isnull(EvnEdit.Evn_IsSigned,1) = 1)) and MH.Morbus_disDT is null then \'edit\' else \'view\' end as AccessType
				,MHD.MorbusHepatitisDiag_id
				,convert(varchar(10),MHD.MorbusHepatitisDiag_setDT,104) as MorbusHepatitisDiag_setDate
				,convert(varchar(10),MHD.MorbusHepatitisDiag_ConfirmDT,104) as MorbusHepatitisDiag_ConfirmDate
				,Lpu.Lpu_Nick
				,LpuSection.LpuSectionProfile_Name
				,MedPersonal.Person_Fin as MedPersonal_Name
				,HDAT.HepatitisDiagActiveType_Name
				,HDT.HepatitisDiagType_Name
				,HFT.HepatitisFibrosisType_Name
				,MHD.MorbusHepatitis_id
				,:Evn_id as MorbusHepatitis_pid
			from
				v_MorbusHepatitisDiag MHD  with (nolock)
				inner join v_MorbusHepatitis MH with (nolock) on MH.MorbusHepatitis_id = MHD.MorbusHepatitis_id
				inner join v_MorbusBase MB with (nolock) on MH.MorbusBase_id = MB.MorbusBase_id
				left join v_EvnSection ES with (nolock) on MHD.Evn_id = ES.EvnSection_id
				left join v_EvnVizitPl PL with (nolock) on MHD.Evn_id = PL.EvnVizitPl_id
				left join v_Lpu Lpu with (nolock) on isnull(ES.Lpu_id,PL.Lpu_id) = Lpu.Lpu_id
				left join v_LpuSection LpuSection with (nolock) on isnull(ES.LpuSection_id,PL.LpuSection_id) = LpuSection.LpuSection_id
				left join v_MedPersonal MedPersonal with (nolock) on MHD.MedPersonal_id = MedPersonal.MedPersonal_id and isnull(ES.Lpu_id,PL.Lpu_id) = MedPersonal.Lpu_id
				left join v_HepatitisDiagActiveType HDAT with (nolock) on MHD.HepatitisDiagActiveType_id = HDAT.HepatitisDiagActiveType_id
				left join v_HepatitisDiagType HDT with (nolock) on MHD.HepatitisDiagType_id = HDT.HepatitisDiagType_id
				left join v_HepatitisFibrosisType HFT with (nolock) on MHD.HepatitisFibrosisType_id = HFT.HepatitisFibrosisType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				MHD.MorbusHepatitis_id = :MorbusHepatitis_id
		';
		$result = $this->db->query($query, $data);
		// echo getDebugSQL($query, $data); exit();
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Метод получения записей Посещения/госпитализации по гепатиту
	*/
	function getMorbusHepatitisEvnViewData($data)
	{
		if(empty($data['Person_id']) and empty($data['Evn_id']))
		{
			return false;
		}
		$params = array();
		$params['Evn_id'] = $data['Evn_id'];
		if(empty($data['Person_id']))
		{
			$set_person_id = '(select Evn.Person_id from Evn with (nolock) where Evn.Evn_id = :Evn_id)';
		}
		else
		{
			$set_person_id = ':Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		$query = "
			declare @Person_id bigint;
			set @Person_id = {$set_person_id};

			select
				Evn.EvnVizitPL_id as Evn_id
				,Evn.EvnVizitPL_pid as Evn_pid
				,'EvnVizitPL' as EvnClass_sysNick
				,convert(varchar(10),Evn.EvnVizitPL_setDT,104) as Evn_setDate
				,Lpu.Lpu_Nick
				,LpuSection.LpuSectionProfile_Name
				,MP.Person_Fin
				,:Evn_id as MorbusHepatitis_pid
			from
				v_EvnVizitPL Evn with (nolock)
				inner join v_Diag d with (nolock) on Evn.Diag_id = d.Diag_id and (d.Diag_Code like 'B15.%' or d.Diag_Code like 'B16.%' or d.Diag_Code like 'B17.%' or d.Diag_Code like 'B18.%' or d.Diag_Code like 'B19.%')
				inner join v_Lpu Lpu with (nolock) on Evn.Lpu_id = Lpu.Lpu_id
				inner join v_LpuSection LpuSection with (nolock) on Evn.LpuSection_id = LpuSection.LpuSection_id
				left join v_MedPersonal MP with (nolock) on Evn.MedPersonal_id = MP.MedPersonal_id and Evn.Lpu_id = MP.Lpu_id
			where
				Evn.Person_id = @Person_id
			union all
			select
				Evn.EvnSection_id as Evn_id
				,Evn.EvnSection_pid as Evn_pid
				,'EvnSection' as EvnClass_sysNick
				,convert(varchar(10),Evn.EvnSection_setDT,104) as Evn_setDate
				,Lpu.Lpu_Nick
				,LpuSection.LpuSectionProfile_Name
				,MP.Person_Fin
				,:Evn_id as MorbusHepatitis_pid
			from
				v_EvnSection Evn with (nolock)
				inner join v_Diag d with (nolock) on Evn.Diag_id = d.Diag_id and (d.Diag_Code like 'B15.%' or d.Diag_Code like 'B16.%' or d.Diag_Code like 'B17.%' or d.Diag_Code like 'B18.%' or d.Diag_Code like 'B19.%')
				inner join v_Lpu Lpu with (nolock) on Evn.Lpu_id = Lpu.Lpu_id
				inner join v_LpuSection LpuSection with (nolock) on Evn.LpuSection_id = LpuSection.LpuSection_id
				left join v_MedPersonal MP with (nolock) on Evn.MedPersonal_id = MP.MedPersonal_id and Evn.Lpu_id = MP.Lpu_id
			where
				Evn.Person_id = @Person_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Метод получения сопутствующих диагнозов Посещений/госпитализаций по гепатиту
	*/
	function getMorbusHepatitisSopDiagViewData($data)
	{
		if(empty($data['Person_id']) and empty($data['Evn_id']))
		{
			return false;
		}
		$params = array();
		$params['Evn_id'] = $data['Evn_id'];
		if(empty($data['Person_id']))
		{
			$set_person_id = '(select Evn.Person_id from Evn with (nolock) where Evn.Evn_id = :Evn_id)';
		}
		else
		{
			$set_person_id = ':Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		$query = "
			declare @Person_id bigint;
			set @Person_id = {$set_person_id};

			select
				DSP.EvnDiagPLSop_id as MorbusHepatitisSopDiag_id
				,Diag.Diag_id
				,Diag.Diag_Code
				,Diag.Diag_Name
				,convert(varchar(10),Evn.EvnVizitPL_setDT,104) as Evn_setDate
				,:Evn_id as MorbusHepatitis_pid
			from
				v_EvnVizitPL Evn with (nolock)
				inner join v_Diag d with (nolock) on Evn.Diag_id = d.Diag_id and (d.Diag_Code like 'B15.%' or d.Diag_Code like 'B16.%' or d.Diag_Code like 'B17.%' or d.Diag_Code like 'B18.%' or d.Diag_Code like 'B19.%')
				inner join v_EvnDiagPLSop DSP with(nolock) on Evn.EvnVizitPL_id = DSP.EvnDiagPLSop_pid
				inner join v_Diag Diag with (nolock) on DSP.Diag_id = Diag.Diag_id
			where
				Evn.Person_id = @Person_id
			union all
			select
				DSP.EvnDiagPS_id as MorbusHepatitisSopDiag_id
				,Diag.Diag_id
				,Diag.Diag_Code
				,Diag.Diag_Name
				,convert(varchar(10),Evn.EvnSection_setDT,104) as Evn_setDate
				,:Evn_id as MorbusHepatitis_pid
			from
				v_EvnSection Evn with (nolock)
				inner join v_Diag d with (nolock) on Evn.Diag_id = d.Diag_id and (d.Diag_Code like 'B15.%' or d.Diag_Code like 'B16.%' or d.Diag_Code like 'B17.%' or d.Diag_Code like 'B18.%' or d.Diag_Code like 'B19.%')
				inner join v_EvnDiagPS DSP with(nolock) on Evn.EvnSection_id = DSP.EvnDiagPS_pid
				inner join DiagSetClass DSC with(nolock) on DSC.DiagSetClass_id = DSP.DiagSetClass_id and DSC.DiagSetClass_Code = 3
				inner join v_Diag Diag with (nolock) on DSP.Diag_id = Diag.Diag_id
			where
				Evn.Person_id = @Person_id
		";

		//echo getDebugSQL($query, $params); exit();

		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Метод получения данных по гепатиту
	* При вызове из формы просмотра записи регистра параметр MorbusHepatitis_pid будет содержать Person_id, также будет передан PersonRegister_id
	* При вызове из формы просмотра движения/посещения параметр MorbusHepatitis_pid будет содержать Evn_id просматриваемого движения/посещения
	*/
	function getMorbusHepatitisViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusHepatitis_pid'])) { $data['MorbusHepatitis_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusHepatitis_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusHepatitis_pid'] = $data['MorbusHepatitis_pid'];

		// предусмотрено создание специфических учетных документов (в которых есть ссылка на посещение/движение из которого они созданы)
		$query = '
			select top 1
				' . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusHepatitis_pid', 'edit', 'view', 'AccessType', 'AND not exists(
									select top 1 Evn.Evn_id from v_Evn Evn with (nolock)
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = M.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusHepatitis_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and (
											exists (
												select top 1 v_MorbusHepatitisVaccination.Evn_id
												from v_MorbusHepatitisVaccination with (nolock)
												where v_MorbusHepatitisVaccination.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_MorbusHepatitisCure.Evn_id
												from v_MorbusHepatitisCure with (nolock)
												where v_MorbusHepatitisCure.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_MorbusHepatitisDiag.Evn_id
												from v_MorbusHepatitisDiag with (nolock)
												where v_MorbusHepatitisDiag.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_EvnUsluga.EvnUsluga_pid as Evn_id
												from v_EvnUsluga with (nolock)
												inner join v_MorbusHepatitisFuncConfirm MHC with (nolock) on MHC.Evn_id = v_EvnUsluga.EvnUsluga_id
												where v_EvnUsluga.EvnUsluga_pid = Evn.Evn_id
											)
											OR exists (
												select top 1 v_EvnUsluga.EvnUsluga_pid as Evn_id
												from v_EvnUsluga with (nolock)
												inner join v_MorbusHepatitisLabConfirm MHC with (nolock) on MHC.Evn_id = v_EvnUsluga.EvnUsluga_id
												where v_EvnUsluga.EvnUsluga_pid = Evn.Evn_id
											)
										)
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . '
				,MH.HepatitisEpidemicMedHistoryType_id
				,HEMHT.HepatitisEpidemicMedHistoryType_Name
				,MH.MorbusHepatitis_EpidNum
				,MH.MorbusHepatitis_id
				,MH.Morbus_id
				,MB.MorbusBase_id
				,:MorbusHepatitis_pid as MorbusHepatitis_pid
				,MB.Person_id
				,M.Diag_id
				,case when exists(select top 1 MHP.MorbusHepatitisPlan_id from v_MorbusHepatitisPlan MHP (nolock) where MHP.MorbusHepatitis_id = MH.MorbusHepatitis_id and ISNULL(MHP.MorbusHepatitisPlan_Treatment, 1) = 1) then 1 else 0 end as hasOpenPlan
			from 
				v_Morbus M with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = M.MorbusBase_id
				inner join v_MorbusHepatitis MH with (nolock) on MH.Morbus_id = M.Morbus_id
				left join HepatitisEpidemicMedHistoryType HEMHT with (nolock) on HEMHT.HepatitisEpidemicMedHistoryType_id = MH.HepatitisEpidemicMedHistoryType_id
			where
				M.Morbus_id = :Morbus_id
		';
		//echo getDebugSQL($query, $params); exit();
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

}