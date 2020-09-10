<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/MorbusNephro_model.php');

class Ufa_MorbusNephro_model extends MorbusNephro_model {



	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->addEntityField('MorbusNephro','Lpu_id');
		$this->addEntityField('MorbusNephro','MorbusNephro_DistanceToDialysisCenter');
		$this->addEntityField('MorbusNephro','MorbusNephro_MonitoringBegDate');
		$this->addEntityField('MorbusNephro','MorbusNephro_MonitoringEndDate');
		$this->addEntityField('MorbusNephro','MorbusNephro_transRejectDate');
		$this->addEntityField('MorbusNephro','MorbusNephro_dialEndDate');
		$this->addEntityField('MorbusNephro','NephroPersonStatus_id');
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
				$rules['distancetodialysiscenter'] = array( //#135648
					'field' => 'MorbusNephro_DistanceToDialysisCenter',
					'label' => 'Расстояние до диализного центра',
					'rules' => 'trim|max_length[4]',
					'type' => 'string'
				);
				$rules['lpu_id'] = array( //#135648
					'field' => 'DialysisCenter_id',
					'label' => 'Диализный центр',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['routinemonitoring_begdate'] = array( //#135648
					'field' => 'MorbusNephro_MonitoringBegDate',
					'label' => 'Плановое наблюдение. дата с',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['routinemonitoring_enddate'] = array( //#135648
					'field' => 'MorbusNephro_MonitoringEndDate',
					'label' => 'Плановое наблюдение. дата с',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['nephroresulttype_code'] = array(
					'field' => 'NephroResultType_Code',
					'label' => 'Текущий статус: код',
					'rules' => 'trim',
					'type' => 'int'
				);
				$rules['dialenddate'] = array(
					'field' => 'MorbusNephro_dialEndDate',
					'label' => 'Дата окончания диализа',
					'rules' => 'trim',
					'type'  => 'date'
				);
				$rules['transrejectdate'] = array(
					'field' => 'MorbusNephro_transRejectDate',
					'label' => 'Дата окончания диализа',
					'rules' => 'trim',
					'type'  => 'date'
				);
				$rules['nephropersonstatus_id'] = array(
					'field' => 'NephroPersonStatus_id',
					'label' => 'Статус пациента',
					'rules' => 'trim',
					'type'  => 'id'
				);
			break;
		}
		return $rules;
	}

	/**
	 * Проверка обязательных параметров специфики #135648
	 *
	 * @param Mode 
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- persondisp_form - это ввод данных специфики в форме "Диспансерные карты пациентов: Добавление / Редактирование"
	 */
	protected function checkParams($data)
	{
		parent::checkParams($data);

		$viewform = $data['Mode']== 'personregister_viewform';
		$nephroResType1 = $data['NephroResultType_Code'] == 1;
		$monitoringBegDate = $data['MorbusNephro_MonitoringBegDate'];
		$monitoringEndDate = $data['MorbusNephro_MonitoringEndDate'];
		$data['Lpu_id'] = $data['DialysisCenter_id'];
		unset($data['DialysisCenter_id']);
		if($viewform) {
			if( $nephroResType1 && empty($monitoringBegDate) ) //#135648
				throw new Exception('Поле "Дата c" не может быть пустой');
			if( $monitoringBegDate > $monitoringEndDate && $monitoringEndDate)       //#135648
				throw new Exception('Дата по" не может быть раньше "Дата с');
		}

		$transDate = $data['MorbusNephro_transDate'];
		$transRejectDate = $data['MorbusNephro_transRejectDate'];
		
		if(!empty($transDate) && !empty($transRejectDate) && $transDate > $transRejectDate)
			throw new Exception('Дата отторжения трансплантанта раньше даты трансплантации');

		$dialDate = $data['MorbusNephro_dialDate'];
		$dialEndDate = $data['MorbusNephro_dialEndDate'];

		if(!empty($dialDate) && !empty($dialEndDate) && $dialDate > $dialEndDate)
			throw new Exception('Дата окончания диализа раньше даты начала диализа');
		return $data;
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	function _beforeSave($data = array()) {
		if($data['Mode'] == 'personregister_viewform') {
			$params = array();
			$params['MorbusNephro_id']  = $data['MorbusNephro_id'];
			$params['pmUser_id']        = $data['session']['pmuser_id'];
			if(!empty($data['NephroCRIType_id'])) {
					$params['NephroCRIType_id'] = $data['NephroCRIType_id'];
					$result = $this->execCommonSP('r2.p_NephroCRITypeHistory_ins',$params);

					$this->checkResultQuery($result);
					unset($params['NephroCRIType_id']);
			}
			$params['Lpu_id'] = $data['Lpu_id'];

			$result = $this->execCommonSP('r2.p_NephroAttachmentHistory_ins',$params);
			$this->checkResultQuery($result);
		}
	}

	/**
	 * Проверка результата запроса на ошибки
	 */
	function checkResultQuery($result)
	{
		if (empty($result)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
		}
	}

	/**
	 * Метод получения данных для панели просмотра
	 * При вызове из формы просмотра записи регистра параметр MorbusNephro_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusNephro_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusNephro_pid'])) { $data['MorbusNephro_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusNephro_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusNephro_pid'] = $data['MorbusNephro_pid'];
		// предусмотрено создание специфических учетных документов (в которых есть ссылка на посещение/движение из которого они созданы)
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('MV', 'MB', 'MorbusNephro_pid', '1', '0', 'accessType', 'AND not exists(
									select top 1 Evn.Evn_id from v_Evn Evn with (nolock)
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = MV.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusNephro_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and exists (
											select top 1 v_PersonHeight.Evn_id
											from v_PersonHeight with (nolock)
											where v_PersonHeight.Evn_id = Evn.Evn_id
											union all
											select top 1 v_PersonWeight.Evn_id
											from v_PersonWeight with (nolock)
											where v_PersonWeight.Evn_id = Evn.Evn_id
										)
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . ",
				convert(varchar,MV.MorbusNephro_begDate,104) as MorbusNephro_begDate,
				convert(varchar,MV.MorbusNephro_firstDate,104) as MorbusNephro_firstDate,
				convert(varchar,MV.MorbusNephro_deadDT,104) as MorbusNephro_deadDT,
				convert(varchar,MV.MorbusNephro_dialDate,104) as MorbusNephro_dialDate,
				convert(varchar,MV.MorbusNephro_transDate,104) as MorbusNephro_transDate,
				ndst.NephroDiagConfType_id,
				ndst.NephroDiagConfType_Name,
				MV.NephroDiagConfType_cid,
				ndct.NephroDiagConfType_Name as NephroDiagConfType_cName,
				nct.NephroCRIType_id,
				nct.NephroCRIType_Name,
				ph.PersonHeight_id,
				pw.PersonWeight_id,
				cast(ph.PersonHeight_Height as int) PersonHeight_Height,
				cast(pw.PersonWeight_Weight as int) PersonWeight_Weight,
				ktt.KidneyTransplantType_id,
				ktt.KidneyTransplantType_Name,
				dt.DialysisType_id,
				dt.DialysisType_Name,
				dgt.DispGroupType_id,
				dgt.DispGroupType_Name,
				nrt.NephroResultType_id,
				nrt.NephroResultType_Name,
				nrt.NephroResultType_Code,
				MV.MorbusNephro_IsHyperten,
				IsHyperten.YesNo_Name as IsHyperten_Name,
				MV.MorbusNephro_CRIDinamic,
				MV.MorbusNephro_Treatment,
				MV.Diag_id,
				MV.MorbusNephro_id,
				MV.Morbus_id,
				MB.MorbusBase_id,
				:MorbusNephro_pid as MorbusNephro_pid,
				MB.Person_id,
				L.Lpu_id as Lpu_id,
				L.Lpu_Nick as Lpu_Nick,
				MV.MorbusNephro_DistanceToDialysisCenter,
				convert(varchar,MV.MorbusNephro_MonitoringBegDate,104) as MorbusNephro_MonitoringBegDate,
				convert(varchar,MV.MorbusNephro_MonitoringEndDate,104) as MorbusNephro_MonitoringEndDate,
				convert(varchar,MV.MorbusNephro_dialEndDate,104) as MorbusNephro_dialEndDate,
				convert(varchar,MV.MorbusNephro_transRejectDate,104) as MorbusNephro_transRejectDate,
				MV.NephroPersonStatus_id,
				NPS.NephroPersonStatus_Name
			from
				v_MorbusNephro MV with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MV.MorbusBase_id
				left join v_NephroDiagConfType ndst with (nolock) on ndst.NephroDiagConfType_id = MV.NephroDiagConfType_id
				left join v_NephroDiagConfType ndct with (nolock) on ndct.NephroDiagConfType_id = MV.NephroDiagConfType_cid
				left join v_NephroCRIType nct with (nolock) on nct.NephroCRIType_id = MV.NephroCRIType_id
				left join v_PersonHeight ph with (nolock) on ph.PersonHeight_id = MV.PersonHeight_id
				left join v_PersonWeight pw with (nolock) on pw.PersonWeight_id = MV.PersonWeight_id
				left join v_KidneyTransplantType ktt with (nolock) on ktt.KidneyTransplantType_id = MV.KidneyTransplantType_id
				left join v_DialysisType dt with (nolock) on dt.DialysisType_id = MV.DialysisType_id
				left join v_DispGroupType dgt with (nolock) on dgt.DispGroupType_id = MV.DispGroupType_id
				left join v_NephroResultType nrt with (nolock) on nrt.NephroResultType_id = MV.NephroResultType_id
				left join v_YesNo IsHyperten with (nolock) on IsHyperten.YesNo_id = MV.MorbusNephro_IsHyperten
				left join v_Lpu L with(nolock) on L.Lpu_id = MV.Lpu_id
				left join v_NephroPersonStatus NPS with(nolock) on NPS.NephroPersonStatus_id = MV.NephroPersonStatus_id 
			where
				MV.Morbus_id = :Morbus_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}