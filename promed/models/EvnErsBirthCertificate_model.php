<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnErsBirthCertificate_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */
 
require_once('EvnErsAbstract_model.php');

class EvnErsBirthCertificate_model extends EvnErsAbstract_model {

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
		return 'EvnERSBirthCertificate';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnERSBirthCertificate_id';
		$arr[self::ID_KEY]['label'] = 'ЭРС';
		$arr['pregnancyregdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_DATE_TIME
			),
			'alias' => 'EvnErsBirthCertificate_PregnancyRegDate',
		);
		$arr['setdt'] = array(
			'properties' => array(
		 		self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_DATE_TIME
		 	),
		 	'alias' => 'EvnErsBirthCertificate_setDT',
		);
		$arr['polisnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSBirthCertificate_PolisNoReason',
		);
		$arr['snilsnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSBirthCertificate_SnilsNoReason',
		);
		$arr['addressnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSBirthCertificate_AddressNoReason',
		);
		$arr['docnoreason'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnERSBirthCertificate_DocNoReason',
		);
		$arr['orgname'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnERSBirthCertificate_OrgName',
		);
		$arr['orginn'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnERSBirthCertificate_OrgINN',
		);
		$arr['orgogrn'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnERSBirthCertificate_OrgOGRN',
		);
		$arr['orgkpp'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnERSBirthCertificate_OrgKPP',
		);
		$arr['personregister_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonRegister_id',
		);
		return $arr;
	}

    /**
     * Контроль наличия действующего договора с ФСС + Контроль наличия данных МО
     * @param $data
     * @return array
     */
	function checkLpu($data) {
		
		$chk = $this->queryResult("
			declare @curDT date = dbo.tzGetdate();
			select top 1 LpuFSSContract_id
			from v_LpuFSSContract (nolock)
			where 
				Lpu_id = :Lpu_id and 
				@curDT between LpuFSSContract_begDate and isnull(LpuFSSContract_endDate, @curDT) 
		", $data);
		
		if (!count($chk)) {
			return [
				'success' => false, 
				'Error_Message' => "МО не имеет действующего договора с ФСС. Внесите действующий договор в Паспорт МО"
			];
		}
		
		$chk = $this->getFirstResultFromQuery("
			select 
				case when Org.Org_INN is null then 'ИНН, ' else '' end +
				case when Org.Org_OGRN is null then 'ОГРН, ' else '' end 
				as fields
			from v_Lpu Lpu (nolock)
			left join v_Org as Org (nolock) on Lpu.Org_id = Org.Org_id
			where 
				Lpu.Lpu_id = :Lpu_id
		", $data);
		
		if (!empty($chk)) {
			$chk = substr($chk, 0, -2);
			return [
				'success' => false, 
				'Error_Message' => "Для вашей МО не заполнены обязательные данные: {$chk}. Внесите данные в Паспорт МО"
			];
		}
		
		return ['success' => true];
	}

    /**
     * Контроль наличия действующего договора с ФСС по определенному Виду услуг
     * @param $data
     * @return array|bool
     */
	function checkLpuFSSContractType($data) {
		
		$chk = $this->getFirstRowFromQuery("
			declare @curDT date = dbo.tzGetDate();
			select 
				lct.LpuFSSContractType_Name,
				lc.LpuFSSContract_id
			from v_LpuFSSContractType lct (nolock)
				left join v_LpuFSSContract lc (nolock) on 
					lc.Lpu_id = :Lpu_id and
					lct.LpuFSSContractType_id = lc.LpuFSSContractType_id and
					@curDT between lc.LpuFSSContract_begDate and isnull(lc.LpuFSSContract_endDate, @curDT) 
			where 
				lct.LpuFSSContractType_id = :LpuFSSContractType_id
		", $data);
		
		// такого не должно получиться, как минимум должен вернуться тип договора
		if (empty($chk)) {
			return false;
		}
		
		if (empty($chk['LpuFSSContract_id'])) {
			return [
				'success' => false, 
				'Error_Message' => "МО не имеет действующего договора с ФСС по виду услуг «{$chk['LpuFSSContractType_Name']}». Внесите действующий договор в Паспорт МО. "
			];
		}
		
		return ['success' => true];
	}

	/**
	 * Проверика наличия ЭРС у пациентки
	 */
	function checkErsExists($data) {

		return $this->queryResult("
			select top 1 
				ers.EvnERSBirthCertificate_id,
				ers.ERSStatus_id,
				isnull(ps.Person_SurName, '') + ' ' + 
				isnull(ps.Person_FirName, '') + ' ' + 
				isnull(ps.Person_SecName, '') + 
				' имеет открытый Родовой сертификат' +
				isnull('№ ' + cast(ers.EvnERSBirthCertificate_Number as varchar), '')
				as msg
			from v_EvnERSBirthCertificate ers (nolock)
			inner join v_PersonState ps (nolock) on ers.Person_id = ps.Person_id
			where 
				ers.Person_id = :Person_id and 
				ers.ERSStatus_id <> 3
		", $data);
	}

    /**
     * Загрузка карт беременной
     * @param $data
     * @return array|false
     */
	function loadPersonRegisterList($data) {

		return $this->queryResult("
            select 
                pr.PersonRegister_id,
                pr.PersonRegister_Code,
                convert(varchar, cast(pr.PersonRegister_setDate as datetime), 104)  as PersonRegister_setDate,
                'Поставлена на учет ' +  
                convert(varchar, cast(pr.PersonRegister_setDate as datetime), 104) + 
				', Карта № ' + isnull(pr.PersonRegister_Code, '')
                as PersonRegister_Name
			from v_PersonRegister pr (nolock)
            where 
                pr.MorbusType_id = 2
                and pr.Person_id = :Person_id
		", $data);
	}

    /**
     * Загрузка необходимых данных в режиме добавления
     * @param bool $isByPersonEvn
     * @param array $params
     * @return array|false
     */
	function loadPersonData($isByPersonEvn = false, $params = []) {
		
		if (empty($params['Person_id'])) {
			return $this->queryResult("
				select 
					Org.Org_INN,
					Org.Org_KPP,
					Org.Org_OGRN
				from v_Lpu Lpu (nolock)
					left join v_Org as Org (nolock) on Lpu.Org_id = Org.Org_id
				where 
					Lpu.Lpu_id = :Lpu_id
			", $params);
		}
		
		return $this->queryResult("
			select 
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
				Org.Org_INN,
				Org.Org_KPP,
				Org.Org_OGRN,
				bss.EvnERSTicket_BirthDate,
				bss.EvnERSTicket_BirthTime
			from v_PersonState PS (nolock)
				left join Document doc (nolock) on doc.Document_id = PS.Document_id
				left join DocumentType dt (nolock) on dt.DocumentType_id = doc.DocumentType_id
				left join v_OrgDep as OD (nolock) on OD.OrgDep_id = doc.OrgDep_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join [Address] adr (nolock) on adr.Address_id = isnull(PS.PAddress_id, PS.UAddress_id)
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = :Lpu_id
				left join v_Org as Org (nolock) on Lpu.Org_id = Org.Org_id
				outer apply (
					select top 1
						convert(varchar(10), bss.BirthSpecStac_OutcomDT, 104) as EvnERSTicket_BirthDate,
						convert(varchar(5), bss.BirthSpecStac_OutcomDT, 108) as EvnERSTicket_BirthTime
					from v_BirthSpecStac bss (nolock)
					inner join v_EvnERSBirthCertificate ers (nolock) on bss.PersonRegister_id = ers.PersonRegister_id
					where ers.EvnERSBirthCertificate_id = :EvnERSBirthCertificate_id
					order by bss.BirthSpecStac_OutcomDT desc
				) bss
			where 
				PS.Person_id = :Person_id
		", $params);
	}

	/**
	 * Загрузка ЭРС
	 */
	function load($data) {
		
		return $this->queryResult("
			select 
				ers.EvnERSBirthCertificate_id,
				ers.EvnERSBirthCertificate_PolisNoReason,
				ers.EvnERSBirthCertificate_SnilsNoReason,
				ers.EvnERSBirthCertificate_DocNoReason,
				ers.EvnERSBirthCertificate_AddressNoReason,
				ers.LpuFSSContract_id,
				ers.ERSStatus_id,
				convert(varchar, ers.EvnErsBirthCertificate_PregnancyRegDate, 104) as EvnErsBirthCertificate_PregnancyRegDate,
				ers.PersonRegister_id,
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
				ers.EvnERSBirthCertificate_OrgINN as Org_INN,
				ers.EvnERSBirthCertificate_OrgKPP as Org_KPP,
				ers.EvnERSBirthCertificate_OrgOGRN as Org_OGRN
			from v_EvnERSBirthCertificate ers (nolock)
				inner join v_Person_all PS (nolock) on ers.PersonEvn_id = ps.PersonEvn_id and ers.Server_id = ps.Server_id
				left join Document doc (nolock) on doc.Document_id = PS.Document_id
				left join DocumentType dt (nolock) on dt.DocumentType_id = doc.DocumentType_id
				left join v_OrgDep as OD (nolock) on OD.OrgDep_id = doc.OrgDep_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join [Address] adr (nolock) on adr.Address_id = isnull(PS.PAddress_id, PS.UAddress_id)
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = ers.Lpu_id
				left join v_Org as Org (nolock) on Lpu.Org_id = Org.Org_id
			where 
				ers.EvnERSBirthCertificate_id = :EvnERSBirthCertificate_id
		", $data);
	}

    /**
     * Сохранение ЭРС
     * @param $data
     * @return array
     */
	function save($data) {

		$this->setScenario(self::SCENARIO_DO_SAVE);
		
		$this->setAttributes($data);
			
		if (empty($data['EvnERSBirthCertificate_id'])) {
			$this->setAttribute('setdt', $this->currentDT);
			$this->setAttribute('ersstatus_id', 21);
			$this->setAttribute('orgname', $data['EvnERSBirthCertificate_OrgName']);
			$this->setAttribute('orginn', $data['EvnERSBirthCertificate_OrgINN']);
			$this->setAttribute('orgogrn', $data['EvnERSBirthCertificate_OrgOGRN']);
			$this->setAttribute('orgkpp', $data['EvnERSBirthCertificate_OrgKPP']);
		}

		return $this->doSave();
	}

    /**
     * Сохранение ЭРС
     * @param $data
     * @return mixed
     */
	function doClose($data) {
		/*return $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => null,
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequestType_id' => 10, // закрытие
			'ERSRequestStatus_id' => 7, // в очереди
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');*/

		// пока имитация успешного ответа

		$res = $this->execCommonSP('p_ERSRequest_ins', [
			'ERSRequest_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequestType_id' => 10, // закрытие
			'ERSRequestStatus_id' => 4, // Получены данные от ФСС
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');

		$this->db->query("
			update 
				EvnERS with(rowlock) 
			set 
				ERSStatus_id = 3, 
				ERSRequest_id = :ERSRequest_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSRequest_id' => $res['ERSRequest_id']
		]);

		$this->db->query("
			update 
				EvnErsBirthCertificate with(rowlock) 
			set 
				ERSCloseCauseType_id = :ERSCloseCauseType_id
			where 
				EvnERS_id = :EvnERS_id
		", [
			'EvnERS_id' => $data['EvnERS_id'],
			'ERSCloseCauseType_id' => $data['ERSCloseCauseType_id']
		]);

		return ['success' => 1];
	}
}