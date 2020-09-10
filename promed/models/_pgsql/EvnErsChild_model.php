<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnErsChild_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */
 
require_once('EvnErsAbstract_model.php');

class EvnErsChild_model extends EvnErsAbstract_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName() {
		return 'EvnErsChild';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnErsChild_id';
		$arr[self::ID_KEY]['label'] = 'Талон';
		$arr['pid']['alias'] = 'EvnErsChild_pid';
		$arr['pid']['label'] = 'ЭРС';
		$arr['setdate']['label'] = 'Дата формирования';
		$arr['setdate']['alias'] = 'EvnErsChild_setDate';
		$arr['polisnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsChild_PolisNoReason',
		);
		$arr['snilsnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsChild_SnilsNoReason',
		);
		$arr['addressnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsChild_AddressNoReason',
		);
		$arr['docnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsChild_DocNoReason',
		);
		return $arr;
	}

	/**
	 * Загрузка списка новорожденных
	 * @param $data
	 * @return array|false
	 */
	function loadChildGrid($data) {

		return $this->queryResult("
			select 
				enb.ErsChildInfo_id as \"ErsChildInfo_id\",
				enb.Person_id as \"Person_id\",
				PS.Person_Surname as \"Person_Surname\",
				PS.Person_Firname as \"Person_Firname\",
				PS.Person_Secname as \"Person_Secname\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				PS.Polis_Num as \"Polis_Num\",
				to_char(enb.ERSChildInfo_WatchBegDate, 'dd.mm.yyyy') as \"ERSChildInfo_WatchBegDate\",
				1 as \"RecordStatus_Code\"
			from v_ErsChildInfo enb
				inner join v_PersonState ps on ps.Person_id = enb.Person_id
			where 
				enb.EvnERSChild_id = :EvnErsChild_id
		", $data);
	}

	/**
	 * Загрузка журнала учёта детей
	 * @param $data
	 * @return array|false
	 */
	function loadJournal($data) {

		$filters = '1 = 1';
		$queryParams = [];

		if (!empty($data['ERSRequestType_id'])) {
			$filters .= ' and ER.ERSRequestType_id = :ERSRequestType_id ';
			$queryParams['ERSRequestType_id'] = $data['ERSRequestType_id'];
		}

		if (!empty($data['ERSRequestStatus_id'])) {
			$filters .= ' and ER.ERSRequestStatus_id = :ERSRequestStatus_id ';
			$queryParams['ERSRequestStatus_id'] = $data['ERSRequestStatus_id'];
		}

		if (!empty($data['EvnERSBirthCertificate_Number'])) {
			$filters .= ' and ers.EvnERSBirthCertificate_Number = :EvnERSBirthCertificate_Number ';
			$queryParams['EvnERSBirthCertificate_Number'] = $data['EvnERSBirthCertificate_Number'];
		}

		if (!empty($data['Person_SurName'])) {
			$filters .= ' and PS.Person_SurName ilike :Person_SurName ';
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']) . '%';
		}

		if (!empty($data['Person_FirName'])) {
			$filters .= ' and PS.Person_FirName ilike :Person_FirName ';
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']) . '%';
		}

		if (!empty($data['Person_SecName'])) {
			$filters .= ' and PS.Person_SecName ilike :Person_SecName ';
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']) . '%';
		}

		if (!empty($data['ERSStatus_id'])) {
			$filters .= ' and ers.ERSStatus_id = :ERSStatus_id ';
			$queryParams['ERSStatus_id'] = $data['ERSStatus_id'];
		}

		if (!empty($data['EvnERSBirthCertificate_CreateDate_Range'])
			&& count($data['EvnERSBirthCertificate_CreateDate_Range']) == 2
			&& !empty($data['EvnERSBirthCertificate_CreateDate_Range'][0])
			&& !empty($data['EvnERSBirthCertificate_CreateDate_Range'][1])) {
			$filters .= ' and ers.EvnErsBirthCertificate_setDT between :EvnERSBirthCertificate_CreateDate_RangeStart
			and :EvnERSBirthCertificate_CreateDate_RangeEnd ';
			$queryParams['EvnERSBirthCertificate_CreateDate_RangeStart'] = $data['EvnERSBirthCertificate_CreateDate_Range'][0];
			$queryParams['EvnERSBirthCertificate_CreateDate_RangeEnd'] = $data['EvnERSBirthCertificate_CreateDate_Range'][1];
		}

		$childFilters = '';

		if (!empty($data['PersonChild_SurName'])) {
			$childFilters .= ' and PS.Person_SurName ilike :PersonChild_SurName ';
			$filters .= ' and CF.Person_ChildFio is not null';
			$queryParams['PersonChild_SurName'] = rtrim($data['PersonChild_SurName']) . '%';
		}

		if (!empty($data['PersonChild_FirName'])) {
			$childFilters .= ' and PS.Person_FirName ilike :PersonChild_FirName ';
			$filters .= ' and CF.Person_ChildFio is not null';
			$queryParams['PersonChild_FirName'] = rtrim($data['PersonChild_FirName']) . '%';
		}

		if (!empty($data['PersonChild_SecName'])) {
			$childFilters .= ' and PS.Person_SecName ilike :PersonChild_SecName ';
			$filters .= ' and CF.Person_ChildFio is not null';
			$queryParams['PersonChild_SecName'] = rtrim($data['PersonChild_SecName']) . '%';
		}

		if (!empty($data['ERSStatus_ChildId'])) {
			$filters .= ' and eet.ERSStatus_id = :ERSStatus_ChildId ';
			$queryParams['ERSStatus_ChildId'] = $data['ERSStatus_ChildId'];
		}

		return $this->queryResult("
			select 
				eet.EvnErsChild_id as \"EvnErsChild_id\",
				ers.Person_id as \"Person_id\",
				ers.ERSStatus_id as \"ERSStatus_id\",
				ers.EvnERSBirthCertificate_Number as \"EvnERSBirthCertificate_Number\",
				coalesce(PS.Person_Surname, '')
					|| ' ' || coalesce(PS.Person_Firname, '')
					|| ' ' || coalesce(PS.Person_Secname, '')
				as \"Person_Fio\",
				substring(CF.Person_ChildFio, 1, length(CF.Person_ChildFio) - 1) as \"Person_ChildFio\",
				to_char(ers.EvnErsBirthCertificate_setDT, 'dd.mm.yyyy') as \"EvnErsBirthCertificate_setDT\",
				ers.EvnERSBirthCertificate_id as \"EvnERSBirthCertificate_id\",
				eet.ERSRequest_id as \"ERSRequest_id\",
				to_char(eet.EvnErsChild_setDate, 'dd.mm.yyyy') as \"EvnErsChild_setDate\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				lpu.Lpu_OGRN as \"Lpu_OGRN\",
				es.ERSStatus_Name as \"ERSStatus_Name\",
				est.ERSStatus_Name as \"ERSStatus_ChildName\",
				ERT.ErsRequestType_Name as \"ErsRequestType_Name\",
				ERSt.ErsRequestStatus_Name as \"ErsRequestStatus_Name\",
				substring(ERE.ERSRequestError, 1, length(ERE.ERSRequestError) - 1) as \"ErsRequestError\"
			from v_EvnERSBirthCertificate ers
				inner join v_Person_all PS on ers.PersonEvn_id = ps.PersonEvn_id
					and ers.Server_id = ps.Server_id
				left join v_EvnErsChild eet on ers.EvnERSBirthCertificate_id = eet.EvnErsChild_pid
				left join v_ERSStatus es on es.ERSStatus_id = ers.ERSStatus_id
				left join v_ERSStatus est on est.ERSStatus_id = eet.ERSStatus_id
				left join lateral(
					select
						*
					from v_ErsRequest ER
					where ER.EvnERS_id = eet.EvnErsChild_id
					order by ER.ERSRequest_insDT desc
					limit 1
				) as ER on true
				left join v_ErsRequestType ERT on ERT.ErsRequestType_id = ER.ErsRequestType_id
				left join v_ErsRequestStatus ERSt on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
				left join v_Lpu lpu on lpu.Lpu_id = eet.Lpu_id
				left join lateral(
					select
						string_agg(ere.ERSRequestError_Descr,', ') as ERSRequestError
					from ERSRequestError ere
					where ere.ERSRequest_id = ER.ErsRequest_id
				) as ERE on true
				left join lateral(
					select
						string_agg(coalesce(PS.Person_Surname, '')
							|| ' ' || coalesce(PS.Person_Firname, '')
							|| ' ' || coalesce(PS.Person_Secname, '')
						, ', ') as Person_ChildFio
					from v_ErsChildInfo enb
					inner join v_PersonState ps on ps.Person_id = enb.Person_id
					where enb.EvnERSChild_id = eet.EvnErsChild_id
						{$childFilters}
				) as CF on true
			where 
				{$filters}
			order by 
				eet.EvnErsChild_setDate desc,
				ers.EvnErsBirthCertificate_PregnancyRegDate desc
		", $queryParams);
	}

	/**
	 * Загрузка постановки на учёт
	 * @param $data
	 * @return array|false
	 */
	function load($data) {

		return $this->queryResult("
			select 
				ers.EvnErsChild_id as \"EvnErsChild_id\",
				ers.EvnErsChild_pid as \"EvnErsChild_pid\",
				ers.EvnErsChild_PolisNoReason as \"EvnErsChild_PolisNoReason\",
				ers.EvnErsChild_SnilsNoReason as \"EvnErsChild_SnilsNoReason\",
				ers.EvnErsChild_DocNoReason as \"EvnErsChild_DocNoReason\",
				ers.EvnErsChild_AddressNoReason as \"EvnErsChild_AddressNoReason\",
				ers.LpuFSSContract_id as \"LpuFSSContract_id\",
				ers.ERSStatus_id as \"ERSStatus_id\",
				erbs.EvnERSBirthCertificate_Number as \"EvnERSBirthCertificate_Number\",
				-- данные по пациенту --
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				PS.Person_Snils as \"Person_Snils\",
				dt.DocumentType_Name as \"DocumentType_Name\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				to_char(doc.Document_begDate, 'dd.mm.yyyy') as \"Document_begDate\",
				OD.OrgDep_Name as \"OrgDep_Name\",
				pls.Polis_Num as \"Polis_Num\",
				to_char(cast(pls.Polis_begDate as timestamp), 'dd.mm.yyyy') as \"Polis_begDate\",
				adr.Address_Address as \"Address_Address\",
				-- данные по МО --
				Lpu.Lpu_id as \"Lpu_id\",
				Org.Org_INN as \"Org_INN\",
				Org.Org_KPP as \"Org_KPP\",
				Org.Org_OGRN as \"Org_OGRN\"
			from v_EvnErsChild ers
				inner join v_Person_all PS on ers.PersonEvn_id = ps.PersonEvn_id
					and ers.Server_id = ps.Server_id
				inner join v_EvnERSBirthCertificate erbs on erbs.EvnERSBirthCertificate_id = ers.EvnErsChild_pid
				left join Document doc on doc.Document_id = PS.Document_id
				left join DocumentType dt on dt.DocumentType_id = doc.DocumentType_id
				left join v_OrgDep as OD on OD.OrgDep_id = doc.OrgDep_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join[Address adr on adr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
				inner join v_Lpu Lpu on Lpu.Lpu_id = ers.Lpu_id
				left join v_Org as Org on Lpu.Org_id = Org.Org_id
			where 
				ers.EvnErsChild_id = :EvnErsChild_id
		", $data);
	}

	/**
	 * Сохранение постановки на учёт
	 * @param $data
	 * @return array
	 */
	function save($data) {
		
		$this->setScenario(self::SCENARIO_DO_SAVE);
		
		$this->setAttributes($data);
			
		if (empty($data['EvnErsChild_id'])) {
			$this->setAttribute('ersstatus_id', 21);
			$this->setAttribute('setdt', $this->currentDT);
			$this->setAttribute('setdate', $this->currentDT->format('Y-m-d'));
		}

		$resp = $this->doSave();
		
		if ($this->isSuccessful($resp)) {
			$data['EvnERSChild_id'] = $resp['EvnErsChild_id'];
			$this->saveChildGridData($data);
		}
		
		return $resp;
	}

	/**
	 * Сохранение сведений о детях
	 * @param $data
	 */
	function saveChildGridData($data) {
		foreach($data['ChildGridData'] as $ErsChildInfo) {
			$ErsChildInfo = (array)$ErsChildInfo;
			$ErsChildInfo['EvnERSChild_id'] = $data['EvnERSChild_id'];
			$ErsChildInfo['pmUser_id'] = $data['pmUser_id'];
			switch($ErsChildInfo['RecordStatus_Code']) {
				case 0:
				case 2:
					$resp = $this->saveErsChildInfo($ErsChildInfo);
					break;
				case 3:
					$resp = $this->deleteErsChildInfo($ErsChildInfo);
			}
		}
	}

	/**
	 * Сохранение сведений о детях
	 * @param $data
	 */
	function saveErsChildInfo($data) {
		$proc = $data['ErsChildInfo_id'] < 0 ? 'p_ErsChildInfo_ins' : 'p_ErsChildInfo_upd';
		$this->execCommonSP($proc, [
			'ErsChildInfo_id' => $data['ErsChildInfo_id'] > 0 ? $data['ErsChildInfo_id'] : null,
			'EvnERSChild_id' => $data['EvnERSChild_id'],
			'Person_id' => $data['Person_id'],
			'ERSChildInfo_WatchBegDate' => date('Y-m-d', strtotime($data['ERSChildInfo_WatchBegDate'])),
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * Удаление сведений о детях
	 * @param $data
	 */
	function deleteErsChildInfo($data) {
		$this->execCommonSP('p_ErsChildInfo_del', [
			'ErsChildInfo_id' => $data['ErsChildInfo_id']
		], 'array_assoc');
	}

	/**
	 * Отправка в ФСС
	 * @param $data
	 * @return mixed
	 */
	function sendToFss($data) {
		$this->db->query("
			update EvnERS
			set ERSStatus_id = 25
			where Evn_id = ?
		", [$data['EvnERS_id']]);
		return $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequestType_id' => 6, // Постановка детей на учет
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * Удаление
	 * @param $data
	 * @return mixed
	 */
	function delete($data) {
		return $this->execCommonSP('p_EvnErsChild_del', [
			'EvnErsChild_id' => $data['EvnErsChild_id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}
}
