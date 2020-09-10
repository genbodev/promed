<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnErsTicket_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */
 
require_once('EvnErsAbstract_model.php');

class EvnErsTicket_model extends EvnErsAbstract_model {

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
		return 'EvnERSTicket';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnERSTicket_id';
		$arr[self::ID_KEY]['label'] = 'Талон';
		$arr['pid']['alias'] = 'EvnERSTicket_pid';
		$arr['pid']['label'] = 'ЭРС';
		$arr['setdate']['label'] = 'Дата формирования';
		$arr['setdate']['alias'] = 'EvnERSTicket_setDate';
		$arr['erstickettype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ERSTicketType_id',
		);
		$arr['polisnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_PolisNoReason',
		);
		$arr['snilsnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_SnilsNoReason',
		);
		$arr['addressnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_AddressNoReason',
		);
		$arr['docnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_DocNoReason',
		);
		$arr['pregnancyregistertime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsTicket_PregnancyRegisterTime',
		);
		$arr['pregnancyputtime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsTicket_PregnancyPutTime',
		);
		$arr['ismultiplepregnancy'] = array(
		 	'properties' => array(
		 		self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
		 	),
		 	'alias' => 'EvnErsTicket_IsMultiplePregnancy',
		);
		$arr['sticknumber'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsTicket_StickNumber',
		);
		$arr['cardnumber'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsTicket_CardNumber',
		);
		$arr['carddate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnErsTicket_CardDate',
		);
		$arr['arrivaldt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_ArrivalDT',
		);
		$arr['birthdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_DATE_TIME
			),
			'alias' => 'EvnERSTicket_BirthDT',
		);
		$arr['diag_id'] = array(
			'properties' => array(
		 		self::PROPERTY_IS_SP_PARAM,
		 	),
		 	'alias' => 'Diag_id',
		);
		$arr['deathreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_DeathReason',
		);
		$arr['childrencount'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSTicket_ChildrenCount',
		);
		return $arr;
	}

	/**
	 * Загрузка новорожденных и мертворожденных из специфики
	 * @param $data
	 * @return array|false
	 */
	function getPersonNewborn($data) {
		
		return $this->queryResult("
			select 
				pn.PersonNewborn_id,
				null as ChildDeath_id,
				p.Sex_id as ERSNewborn_Gender,
				sex.Sex_Name,
				pn.PersonNewborn_Height as ERSNewborn_Height,
				pn.PersonNewborn_Weight as ERSNewborn_Weight,
				null as ERSNewborn_DeathReason
			from v_PersonNewborn pn (nolock)
				inner join v_BirthSpecStac bss (nolock) on bss.BirthSpecStac_id = pn.BirthSpecStac_id
				inner join v_PersonRegister pr (nolock) on pr.PersonRegister_id = bss.PersonRegister_id
				left join v_PersonSex p (nolock) on p.Person_id = pn.Person_id
				left join v_Sex sex (nolock) on sex.Sex_id = p.Sex_id
			where 
				pr.Person_id = :Person_id
				
			union all 
			
			select 
				null as PersonNewborn_id,
				cd.ChildDeath_id,
				cd.Sex_id as ERSNewborn_Gender,
				sex.Sex_Name,
				cd.ChildDeath_Height as ERSNewborn_Height,
				cd.ChildDeath_Weight as ERSNewborn_Weight,
				d.Diag_FullName as ERSNewborn_DeathReason
			from v_ChildDeath cd (nolock)
				inner join v_BirthSpecStac bss (nolock) on bss.BirthSpecStac_id = cd.BirthSpecStac_id
				inner join v_PersonRegister pr (nolock) on pr.PersonRegister_id = bss.PersonRegister_id
				left join v_Diag d (nolock) on d.Diag_id = cd.Diag_id
				left join v_Sex sex (nolock) on sex.Sex_id = cd.Sex_id
			where 
				pr.Person_id = :Person_id
		", $data);
	}

	/**
	 * Загрузка списка новорожденных
	 * @param $data
	 * @return array|false
	 */
	function loadNewbornGrid($data) {
		
		return $this->queryResult("
			select 
				enb.ERSNewborn_id,
				enb.PersonNewborn_id,
				enb.ChildDeath_id,
				enb.ERSNewborn_Gender,
				sex.Sex_Name,
				enb.ERSNewborn_Height,
				enb.ERSNewborn_Weight,
				enb.ERSNewborn_DeathReason,
				1 as RecordStatus_Code
			from v_ERSNewborn enb (nolock)
				left join v_Sex sex (nolock) on sex.Sex_id = enb.ERSNewborn_Gender
			where 
				enb.EvnERS_id = :EvnERSTicket_id
		", $data);
	}

	/**
	 * список детей
	 * @param $data
	 * @return array|false
	 */
	function getErsChildInfo($data) {
		
		return $this->queryResult("
			select 
				eci.ErsChildInfo_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
				PS.Polis_Num,
				convert(varchar(10),eci.ErsChildInfo_WatchBegDate,104) as ErsChildInfo_WatchBegDate,
				case when :ERSTicketType_id = 3 
					then convert(varchar(10),eci.ErsChildInfo_fWatchEndDate,104)
					else convert(varchar(10),eci.ErsChildInfo_sWatchEndDate,104)
				end as ErsChildInfo_WatchEndDate,
				1 as RecordStatus_Code
			from v_ErsChildInfo eci (nolock)
				inner join v_EvnErsChild eet (nolock) on eci.EvnERSChild_id = eet.EvnErsChild_id
				inner join v_PersonState ps (nolock) on ps.Person_id = eci.Person_id
			where 
				eet.EvnErsChild_pid = :EvnErsChild_pid
		", $data);
	}

	/**
	 * Загрузка журнала талонов
	 * @param $data
	 * @return array|false
	 */
	function loadJournal($data) {
		
		$filters = '1 = 1 ';
		$queryParams = [];

		// if (!empty($data['Lpu_id'])) {
		// 	$filters .= ' and eet.Lpu_id = :Lpu_id ';
		// 	$queryParams['Lpu_id'] = $data['Lpu_id'];
		// }		
		
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
			$filters .= ' and PS.Person_SurName like :Person_SurName ';
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']) . '%';
		}

		if (!empty($data['Person_FirName'])) {
			$filters .= ' and PS.Person_FirName like :Person_FirName ';
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']) . '%';
		}

		if (!empty($data['Person_SecName'])) {
			$filters .= ' and PS.Person_SecName like :Person_SecName ';
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']) . '%';
		}

		if (!empty($data['ERSStatus_id'])) {
			$filters .= ' and eet.ERSStatus_id = :ERSStatus_id ';
			$queryParams['ERSStatus_id'] = $data['ERSStatus_id'];
		}	
		
		if (!empty($data['EvnERSTicket_setDate_Range']) 
				&& count($data['EvnERSTicket_setDate_Range']) == 2 
				&& !empty($data['EvnERSTicket_setDate_Range'][0])
				&& !empty($data['EvnERSTicket_setDate_Range'][1])) {
			$filters .= ' and eet.EvnERSTicket_setDate between :EvnERSTicket_setDate_RangeStart and :EvnERSTicket_setDate_RangeEnd ';
			$queryParams['EvnERSTicket_setDate_RangeStart'] = $data['EvnERSTicket_setDate_Range'][0];
			$queryParams['EvnERSTicket_setDate_RangeEnd'] = $data['EvnERSTicket_setDate_Range'][1];
		}

		return $this->queryResult("
			select 
				eet.EvnERSTicket_id,
				ers.EvnERSBirthCertificate_Number,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), ers.EvnErsBirthCertificate_setDT, 104) as EvnErsBirthCertificate_setDT,
				eet.EvnERSTicket_pid,
				eet.ERSTicketType_id,
				eet.ERSRequest_id,
				convert(varchar(10), eet.EvnERSTicket_setDate, 104) as EvnERSTicket_setDate,
				lpu.Lpu_Nick,
				ertt.ERSTicketType_Name,
				es.ERSStatus_Name,
				es.ERSStatus_Code,
				ERT.ErsRequestType_Name,
				ERT.ErsRequestType_id,
				ERSt.ErsRequestStatus_Name,
				ERSt.ErsRequestStatus_id,
				substring(ERE.ERSRequestError, 1, len(ERE.ERSRequestError)-1) as ErsRequestError
			from v_EvnERSTicket eet (nolock)
				inner join v_ERSTicketType ertt (nolock) on ertt.ERSTicketType_id = eet.ERSTicketType_id
				inner join v_EvnERSBirthCertificate ers (nolock) on ers.EvnERSBirthCertificate_id = eet.EvnERSTicket_pid
				inner join v_Person_all PS (nolock) on eet.PersonEvn_id = ps.PersonEvn_id and eet.Server_id = ps.Server_id
				left join v_ERSStatus es (nolock) on es.ERSStatus_id = eet.ERSStatus_id
				left join v_ErsRequest ER (nolock) on ER.ErsRequest_id = eet.ErsRequest_id
				left join v_ErsRequestType ERT (nolock) on ERT.ErsRequestType_id = ER.ErsRequestType_id
				left join v_ErsRequestStatus ERSt (nolock) on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
				left join v_Lpu lpu (nolock) on lpu.Lpu_id = eet.Lpu_id
				outer apply (
					Select (
						select ere.ERSRequestError_Descr + ', ' as 'data()'
						from ERSRequestError ere with(nolock)
						where ere.ERSRequest_id = ER.ErsRequest_id
						for xml path('')
					) as ERSRequestError
				) as ERE
			where 
				{$filters}
			order by 
				eet.EvnERSTicket_setDate desc
		", $queryParams);
	}

	/**
	 * Загрузка списка талонов
	 * @param $data
	 * @return array|false
	 */
	function loadList($data) {

		return $this->queryResult("
			select 
				eet.EvnERSTicket_id,
				eet.EvnERSTicket_pid,
				eet.ERSTicketType_id,
				eet.ERSRequest_id,
				convert(varchar(10), eet.EvnERSTicket_setDate, 104) as EvnERSTicket_setDate,
				ertt.ERSTicketType_Name,
				es.ERSStatus_Name,
				es.ERSStatus_Code,
				ERT.ErsRequestType_Name,
				ERT.ErsRequestType_id,
				ERSt.ErsRequestStatus_Name,
				ERSt.ErsRequestStatus_id,
				substring(ERE.ERSRequestError, 1, len(ERE.ERSRequestError)-1) as ErsRequestError
			from v_EvnERSTicket eet (nolock)
				inner join v_ERSTicketType ertt (nolock) on ertt.ERSTicketType_id = eet.ERSTicketType_id
				left join v_ERSStatus es (nolock) on es.ERSStatus_id = eet.ERSStatus_id
				left join v_ErsRequest ER (nolock) on ER.ErsRequest_id = eet.ErsRequest_id
				left join v_ErsRequestType ERT (nolock) on ERT.ErsRequestType_id = ER.ErsRequestType_id
				left join v_ErsRequestStatus ERSt (nolock) on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
				outer apply (
					Select (
						select ere.ERSRequestError_Descr + ', ' as 'data()'
						from ERSRequestError ere with(nolock)
						where ere.ERSRequest_id = ER.ErsRequest_id
						for xml path('')
					) as ERSRequestError
				) as ERE
			where 
				eet.EvnERSTicket_pid = :EvnERSTicket_pid
		", $data);
	}

	/**
	 * Загрузка талона
	 * @param $data
	 * @return array|false
	 */
	function load($data) {

		// convert(varchar,cast(ers.EvnERSTicket_BirthDT as date), 104) as EvnERSTicket_BirthDate,
		// convert(varchar,cast(ers.EvnERSTicket_BirthDT as time), 104) as EvnERSTicket_BirthTime,

		
		$responce = $this->queryResult("
			select 
				ers.EvnERSTicket_id,
				ers.EvnERSTicket_pid,
				ers.EvnERSTicket_PolisNoReason,
				ers.EvnERSTicket_SnilsNoReason,
				ers.EvnERSTicket_DocNoReason,
				ers.EvnERSTicket_AddressNoReason,
				ers.LpuFSSContract_id,
				ers.ERSStatus_id,
				ers.EvnErsTicket_PregnancyRegisterTime,
				ers.EvnErsTicket_PregnancyPutTime,
				ers.EvnErsTicket_IsMultiplePregnancy,
				ers.EvnErsTicket_StickNumber,
				ers.EvnErsTicket_CardNumber,
				convert(varchar(10), ers.EvnErsTicket_CardDate, 104) as EvnErsTicket_CardDate,
				convert(varchar(10), ers.EvnERSTicket_ArrivalDT, 104) as EvnERSTicket_ArrivalDT,
				ers.EvnERSTicket_BirthDT,
				convert(varchar(10), ers.EvnERSTicket_BirthDT, 104) as EvnERSTicket_BirthDate,
				convert(varchar(5), ers.EvnERSTicket_BirthDT, 108) as EvnERSTicket_BirthTime,
				ers.Diag_id,
				ers.EvnERSTicket_DeathReason,
				ers.EvnERSTicket_ChildrenCount,
				erbs.EvnERSBirthCertificate_Number,
				-- данные по пациенту --
				PS.Person_id,
				PS.PersonEvn_id,
				PS.Server_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
				PS.Person_Snils,
				dt.DocumentType_Name,
				doc.Document_Ser,
				doc.Document_Num,
				convert(varchar, doc.Document_begDate, 104) as Document_begDate,
				OD.OrgDep_Name,
				pls.Polis_Num,
				convert(varchar,cast(pls.Polis_begDate as datetime), 104) as Polis_begDate,
				adr.Address_Address,
				-- данные по МО --
				Lpu.Lpu_id,
				ers.ERSTicketType_id
			from v_EvnERSTicket ers (nolock)
				inner join v_Person_all PS (nolock) on ers.PersonEvn_id = ps.PersonEvn_id and ers.Server_id = ps.Server_id
				inner join v_EvnERSBirthCertificate erbs (nolock) on erbs.EvnERSBirthCertificate_id = ers.EvnERSTicket_pid
				left join Document doc (nolock) on doc.Document_id = PS.Document_id
				left join DocumentType dt (nolock) on dt.DocumentType_id = doc.DocumentType_id
				left join v_OrgDep as OD (nolock) on OD.OrgDep_id = doc.OrgDep_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join [Address] adr (nolock) on adr.Address_id = isnull(PS.PAddress_id, PS.UAddress_id)
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = ers.Lpu_id
			where 
				ers.EvnERSTicket_id = :EvnERSTicket_id
		", $data);

		return $responce;
	}

	/**
	 * Сохранение талона
	 * @param $data
	 * @return array
	 */
	function save($data) {

		$this->setScenario(self::SCENARIO_DO_SAVE);

		$this->setAttributes($data);
			
		if (empty($data['EvnERSTicket_id'])) {
			$this->setAttribute('ersstatus_id', 21);
			$this->setAttribute('setdt', $this->currentDT);
			$this->setAttribute('setdate', $this->currentDT->format('Y-m-d'));
		}

		$resp = $this->doSave();
		
		if ($data['ERSTicketType_id'] == 2 && $this->isSuccessful($resp)) {
			$data['EvnERS_id'] = $resp['EvnERSTicket_id'];
			$this->saveNewbornGridData($data);
		}
		
		if (in_array($data['ERSTicketType_id'], [3,4]) && $this->isSuccessful($resp)) {
			$data['EvnERS_id'] = $resp['EvnERSTicket_id'];
			$this->saveErsChildInfoData($data);
		}
		
		return $resp;
	}

	/**
	 * Сохранение сведений о новорожденных
	 * @param $data
	 */
	function saveNewbornGridData($data) {
		foreach($data['NewbornGridData'] as $ERSNewborn) {
			$ERSNewborn = (array)$ERSNewborn;
			$ERSNewborn['EvnERS_id'] = $data['EvnERS_id'];
			$ERSNewborn['pmUser_id'] = $data['pmUser_id'];
			switch($ERSNewborn['RecordStatus_Code']) {
				case 0:
				case 2:
					$resp = $this->saveERSNewborn($ERSNewborn);
					break;
				case 3:
					$resp = $this->deleteERSNewborn($ERSNewborn);
			}
		}
	}

	/**
	 * Сохранение сведений о новорожденных
	 * @param $data
	 */
	function saveERSNewborn($data) {
		$proc = $data['ERSNewborn_id'] < 0 ? 'p_ERSNewborn_ins' : 'p_ERSNewborn_upd';
		$this->execCommonSP($proc, [
			'ERSNewborn_id' => $data['ERSNewborn_id'] > 0 ? $data['ERSNewborn_id'] : null,
			'EvnERS_id' => $data['EvnERS_id'],
			'PersonNewborn_id' => !empty($data['PersonNewborn_id']) ? $data['PersonNewborn_id'] : null,
			'ChildDeath_id' => !empty($data['ChildDeath_id']) ? $data['ChildDeath_id'] : null,
			'ERSNewborn_Gender' => $data['ERSNewborn_Gender'],
			'ERSNewborn_Height' => intval($data['ERSNewborn_Height']),
			'ERSNewborn_Weight' => intval($data['ERSNewborn_Weight']),
			'ERSNewborn_DeathReason' => $data['ERSNewborn_DeathReason'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * Удаление сведений о новорожденных
	 * @param $data
	 */
	function deleteERSNewborn($data) {
		$this->execCommonSP('p_ERSNewborn_del', [
			'ERSNewborn_id' => $data['ERSNewborn_id']
		], 'array_assoc');
	}

	/**
	 * Сохранение сведений о новорожденных
	 * @param $data
	 */
	function saveErsChildInfoData($data) {
		foreach($data['NewbornGridData'] as $erc) {
			$erc = (array)$erc;
			$field = $data['ERSTicketType_id'] == 3 ? 'ErsChildInfo_fWatchEndDate' : 'ErsChildInfo_sWatchEndDate';
			
			$this->db->query("
				update 
					ErsChildInfo with(rowlock) 
				set 
					{$field} = :ErsChildInfo_WatchEndDate
				where 
					ErsChildInfo_id = :ErsChildInfo_id
			", [
				'ErsChildInfo_id' => $erc['ErsChildInfo_id'],
				'ErsChildInfo_WatchEndDate' => date('Y-m-d', strtotime($erc['ErsChildInfo_WatchEndDate'])),
			]);
		}
	}

	/**
	 * --------------
	 * @param $data
	 * @return array
	 */
	function SendTicketsToFss($data) {
		
		$ert = min($data['ERSTicketType_id']+1, 3);
		
		$tickets = $this->queryList("
			select EvnERSTicket_id
			from v_EvnERSTicket (nolock)
			where 
				ERSTicketType_id = :ERSTicketType_id 
				and ERSStatus_id = 24
		", $data);
		
		$res = $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'EvnERS_id' => null,
			'ERSRequestType_id' => $ert,
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		foreach($tickets as $t) {
			$this->db->query("
				update 
					EvnERS with(rowlock) 
				set 
					ERSStatus_id = 25, 
					ERSRequest_id = :ERSRequest_id
				where 
					EvnERS_id = :EvnERS_id
			", [
				'EvnERS_id' => $t,
				'ERSRequest_id' => $res['ERSRequest_id']
			]);
		}
		
		return ['success' => 1];
	}

	/**
	 * запрос результата
	 * @param $data
	 * @return array
	 */
	function getFssResult($data) {

		// пока имитация успешного ответа

		$res = $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
			'EvnERS_id' => null,
			'ERSRequestType_id' => 7, // Запрос результатов обработки из ФСС
			'ERSRequestStatus_id' => 4, // Получены данные от ФСС
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');

		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set 
				ERSStatus_id = 13, 
				ERSRequest_id = :ERSRequest_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERSTicket_id'],
			'ERSRequest_id' => $res['ERSRequest_id']
		]);

		return ['success' => 1];
	}
}