<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * Person_model - модель для работы с людьми (Бурятия)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
require(APPPATH . 'models/Person_model.php');

class Buryatiya_Person_model extends Person_model {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 */
	function exportPersonPolisToDBF($data) {
		$params = array(
			'AttachLpu_id' => $data['AttachLpu_id'],
			'PersonPolis_Date' => $data['PersonPolis_Date']
		);

		$query = "
			declare @PersonPolis_Date date = :PersonPolis_Date;

			with PersonD (
				Person_id,Person_insDT
			) as (
				select 
					P1.Person_id, P1.Person_insDT
				from
					v_Person P1 with (nolock)
					cross apply (
						select top 1 t.PersonCard_id
						from v_PersonCard_all t with (nolock)
						where t.Person_id = P1.Person_id
							and t.Lpu_id = :AttachLpu_id
							and t.LpuAttachType_id = 1
							and t.PersonCard_begDate <= @PersonPolis_Date
							and ISNULL(t.PersonCard_endDate, '2030-12-31') > @PersonPolis_Date
					) v
				where
					P1.Person_deadDT is null
					and cast(P1.Person_insDT as date) <= @PersonPolis_Date
			),
			PersonData (
				Person_id,
				Person_Snils,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				Person_BirthDay,
				Sex_id,
				Document_id,
				NationalityStatus_id,
				SocStatus_id,
				Polis_id,
				PAddress_id,
				UAddress_id,
				Person_deadDT
			) as (
				select 
					P.Person_id,
					P.Person_Snils,
					RTRIM(P.Person_SurName) as Person_SurName,
					RTRIM(P.Person_FirName) as Person_FirName,
					RTRIM(P.Person_SecName) as Person_SecName,
					P.Person_BirthDay,
					P.Sex_id,
					P.Document_id,
					P.NationalityStatus_id,
					P.SocStatus_id,
					P.Polis_id,
					P.PAddress_id,
					P.UAddress_id,
					P.Person_deadDT
				from
					PersonD P1 with (nolock)
					cross apply (
						select top 1
							t.Person_id,
							t.Person_Snils,
							RTRIM(t.Person_SurName) as Person_SurName,
							RTRIM(t.Person_FirName) as Person_FirName,
							RTRIM(t.Person_SecName) as Person_SecName,
							t.Person_BirthDay,
							t.Sex_id,
							t.Document_id,
							t.NationalityStatus_id,
							t.SocStatus_id,
							t.Polis_id,
							t.PAddress_id,
							t.UAddress_id,
							t.Person_deadDT
						from v_Person_all t with (nolock)
						where
							t.PersonEvn_insDT is not null
							and cast(t.PersonEvn_insDT as date) <= @PersonPolis_Date
							and t.Person_id = P1.Person_id
						order by t.PersonEvn_insDT desc
					) P
					left join v_Polis Polis with (nolock) on Polis.Polis_id = P.Polis_id
				where
					P.Person_deadDT is null
					and cast(P1.Person_insDT as date) <= @PersonPolis_Date
					and Polis.Polis_begDate <= @PersonPolis_Date
					and ISNULL(Polis.Polis_endDate, '2030-12-31') > @PersonPolis_Date
			)

			select
				P.Person_id as NOM, -- Номер получателя по списку
				cast(P.Person_SurName as varchar(30)) as FM, -- Фамилия
				cast(P.Person_FirName as varchar(30)) as IM, -- Имя
				cast(P.Person_SecName as varchar(30)) as OT, -- Отчество
				case
					when S.Sex_SysNick = 'woman' then 'Ж'
					when S.Sex_SysNick in ('man', 'issex') then 'М'
					else ''
				end as V_P, -- Пол
				convert(varchar(8), P.Person_BirthDay, 112) as DR, -- Дата рождения
				AB.Address_Address as MR, -- Место рождения
				cast(KLC.KLCountry_Name as varchar(50)) as GRAG, -- Гражданство
				DT.DocumentType_Name as DOCTYPE, -- Документ, удостоверяющий личность
				cast(D.Document_Ser as varchar(10)) as PASPS, -- Серия документа
				cast(D.Document_Num as varchar(10)) as PASPN, -- Номер документа
				convert(varchar(8), D.Document_begDate, 112) as PASPD, -- Дата выдачи документа
				cast(OD.OrgDep_Name as varchar(30)) as PASPA, -- Кем выдан документ
				AP.Address_Address as MG, -- Место жительства
				AU.Address_Address as MREG, -- Место регистрации
				convert(varchar(8), AU.Address_begDate, 112) as DREG, -- Дата регистрации 
				P.Person_Snils as SNILS, -- СНИЛС (страховой номер индивидуального лицевого счета)
				cast(Polis.Polis_Ser as varchar(10)) as POLS, -- Серия полиса ОМС
				cast(Polis.Polis_Num as varchar(20)) as POLN, -- Номер полиса ОМС  
				cast(OS.OrgSmo_Name as varchar(100)) as STRAHORGS, -- Наименование страховой медицинской организации, выбранной застрахованным лицом
				convert(varchar(8), Polis.Polis_begDate, 112) as STRAHORGD, -- Дата регистрации в качестве застрахованного лица 
				SC.SocStatus_Name as STATUS -- Статус застрахованного лица (неработающий)
			from
				PersonData P
				inner join v_SocStatus SC with (nolock) on SC.SocStatus_id = P.SocStatus_id
				left join v_Polis Polis with (nolock) on Polis.Polis_id = P.Polis_id
				left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Polis.OrgSmo_id
				left join v_Sex S with (nolock) on S.Sex_id = P.Sex_id
				left join v_PersonBirthPlace PBP with (nolock) on PBP.Person_id = P.Person_id
				left join [Address] AB with (nolock) on AB.Address_id = PBP.Address_id
				left join [Address] AP with (nolock) on AP.Address_id = P.PAddress_id
				left join [Address] AU with (nolock) on AU.Address_id = P.UAddress_id
				left join v_Document D with (nolock) on D.Document_id = P.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_NationalityStatus NS with (nolock) on NS.NationalityStatus_id = P.NationalityStatus_id
				left join v_OrgDep OD with (nolock) on OD.OrgDep_id = D.OrgDep_id
				left join v_KLCountry KLC with (nolock) on KLC.KLCountry_id = NS.KLCountry_id
			where
				SC.SocStatus_SysNick in ('child_doma', 'child_yasli', 'study', 'nrab', 'pen', 'bomzh')

			union

			select
				P.Person_id as NOM, -- Номер получателя по списку
				cast(P.Person_SurName as varchar(30)) as FM, -- Фамилия
				cast(P.Person_FirName as varchar(30)) as IM, -- Имя
				cast(P.Person_SecName as varchar(30)) as OT, -- Отчество
				case
					when S.Sex_SysNick = 'woman' then 'Ж'
					when S.Sex_SysNick in ('man', 'issex') then 'М'
					else ''
				end as V_P, -- Пол
				convert(varchar(8), P.Person_BirthDay, 112) as DR, -- Дата рождения
				AB.Address_Address as MR, -- Место рождения
				cast(KLC.KLCountry_Name as varchar(50)) as GRAG, -- Гражданство
				DT.DocumentType_Name as DOCTYPE, -- Документ, удостоверяющий личность
				cast(D.Document_Ser as varchar(10)) as PASPS, -- Серия документа
				cast(D.Document_Num as varchar(10)) as PASPN, -- Номер документа
				convert(varchar(8), D.Document_begDate, 112) as PASPD, -- Дата выдачи документа
				cast(OD.OrgDep_Name as varchar(30)) as PASPA, -- Кем выдан документ
				AP.Address_Address as MG, -- Место жительства
				AU.Address_Address as MREG, -- Место регистрации
				convert(varchar(8), AU.Address_begDate, 112) as DREG, -- Дата регистрации 
				P.Person_Snils as SNILS, -- СНИЛС (страховой номер индивидуального лицевого счета)
				cast(Polis.Polis_Ser as varchar(10)) as POLS, -- Серия полиса ОМС
				cast(Polis.Polis_Num as varchar(20)) as POLN, -- Номер полиса ОМС  
				cast(OS.OrgSmo_Name as varchar(100)) as STRAHORGS, -- Наименование страховой медицинской организации, выбранной застрахованным лицом
				convert(varchar(8), Polis.Polis_begDate, 112) as STRAHORGD, -- Дата регистрации в качестве застрахованного лица 
				SC.SocStatus_Name as STATUS -- Статус застрахованного лица (неработающий)
			from
				PersonData P
				left join v_SocStatus SC with (nolock) on SC.SocStatus_id = P.SocStatus_id
				left join v_Polis Polis with (nolock) on Polis.Polis_id = P.Polis_id
				left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Polis.OrgSmo_id
				left join v_Sex S with (nolock) on S.Sex_id = P.Sex_id
				left join v_PersonBirthPlace PBP with (nolock) on PBP.Person_id = P.Person_id
				left join [Address] AB with (nolock) on AB.Address_id = PBP.Address_id
				left join [Address] AP with (nolock) on AP.Address_id = P.PAddress_id
				left join [Address] AU with (nolock) on AU.Address_id = P.UAddress_id
				left join v_Document D with (nolock) on D.Document_id = P.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_NationalityStatus NS with (nolock) on NS.NationalityStatus_id = P.NationalityStatus_id
				left join v_OrgDep OD with (nolock) on OD.OrgDep_id = D.OrgDep_id
				left join v_KLCountry KLC with (nolock) on KLC.KLCountry_id = NS.KLCountry_id
			where
				ISNULL(SC.SocStatus_SysNick, '') not in ('child_doma', 'child_yasli', 'study', 'nrab', 'pen', 'bomzh')
				and dbo.Age2(P.Person_BirthDay, @PersonPolis_Date) <= 7
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}

		return $result;
	}

	/**
	 * Поулчение федерального кода и наименования МО
	 */
	function getLpuData($data) {
		$query = "
			select top 1 Lpu_f003mcod, Lpu_Nick
			from v_Lpu with (nolock)
			where Lpu_id = :AttachLpu_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}