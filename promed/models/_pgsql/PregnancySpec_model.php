<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Created by JetBrains PhpStorm.
 * User: IGabdushev
 * Date: 24.10.11
 * Time: 10:43
 */

class PregnancySpec_model extends SwPgModel
{
	/**
	 * Конструктор
	 */
	function PersonWeight_model()
	{
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data)
	{
		$query = "
			with mv as (
				SELECT EvnSection_id
				FROM dbo.v_BirthSpecStac
				WHERE PregnancySpec_id = :PregnancySpec_id
				order BY BirthSpecStac_updDT DESC
				limit 1
			)
			SELECT
				PregnancySpec_id  as \"PregnancySpec_id\",
				d.PersonDisp_id  as \"PersonDisp_id\",
				Lpu_aid  as \"Lpu_aid\",
				PregnancySpec_Period  as \"PregnancySpec_Period\",
				PregnancySpec_Count  as \"PregnancySpec_Count\",
				PregnancySpec_CountBirth  as \"PregnancySpec_CountBirth\",
				PregnancySpec_CountAbort  as \"PregnancySpec_CountAbort\",
				to_char(PregnancySpec_BirthDT, 'dd.mm.yyyy') AS \"PregnancySpec_BirthDT \",
				BirthResult_id  as \"BirthResult_id\",
				PregnancySpec_OutcomPeriod  as \"PregnancySpec_OutcomPeriod\",
				to_char(PregnancySpec_OutcomDT, 'dd.mm.yyyy') AS \"PregnancySpec_OutcomDT \",
				BirthSpec_id  as \"BirthSpec_id\",
				PregnancySpec_IsHIVtest  as \"PregnancySpec_IsHIVtest\",
				PregnancySpec_IsHIV  as \"PregnancySpec_IsHIV\",
				(SELECT EvnSection_id
				 FROM v_evnSection
				 WHERE evnSection_id IN (select EvnSection_id from mv)
				) AS \"EvnSection_id \",
				(SELECT EvnSection_pid
				 FROM v_evnSection
				 WHERE evnSection_id IN (select EvnSection_id from mv)
				) AS \"EvnSection_pid\",
				to_char(d.PersonDisp_begDate, 'dd.mm.yyyy') AS \"PersonDisp_begDate\"
			FROM    dbo.PregnancySpec s
			left join dbo.PersonDisp d on d.PersonDisp_id = s.personDisp_id
			WHERE   PregnancySpec_id = :PregnancySpec_id
		";
		$result = $this->db->query($query, array(
			'PregnancySpec_id' => $data['PregnancySpec_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadEvnUslugaPregnancySpecForm($data) {
		$query = '';
		$queryParams = array(
			'id' => $data['id'],
			//'Lpu_id' => $data['Lpu_id']
		);

		$query = "

					SELECT
					    'edit' as \"accessType\",
						EUC.EvnUslugaPregnancySpec_id AS \"EvnUslugaCommon_id\",
						EUC.EvnUslugaPregnancySpec_pid as \"EvnUslugaCommon_pid\",
						EUC.EvnUslugaPregnancySpec_pid as \"EvnUslugaCommon_rid\",
						EUC.Person_id as \"Person_id\",
						EUC.PersonEvn_id as \"PersonEvn_id\",
						EUC.Server_id as \"Server_id\",
						to_char(EUC.EvnUslugaPregnancySpec_setDate, 'dd.mm.yyyy') as \"EvnUslugaCommon_setDate\",
						coalesce(EUC.EvnUslugaPregnancySpec_setTime, '') as \"EvnUslugaCommon_setTime\",
						to_char(EUC.EvnUslugaPregnancySpec_disDate, 'dd.mm.yyyy') as \"EvnUslugaCommon_disDate\",
						coalesce(EUC.EvnUslugaPregnancySpec_disTime, '') as \"EvnUslugaCommon_disTime\",
						EUC.UslugaPlace_id as \"UslugaPlace_id\",
						EUC.Lpu_uid as \"Lpu_uid\",
						EUC.Org_uid as \"Org_uid\",
						EUC.LpuSection_uid as \"LpuSection_uid\",
						EUC.LpuSectionProfile_id as \"LpuSectionProfile_id\",
						EUC.MedPersonal_id as \"MedPersonal_id\",
						EUC.UslugaComplex_id as \"UslugaComplex_id\",
						EUC.PayType_id as \"PayType_id\",
						ROUND(coalesce(EUC.EvnUslugaPregnancySpec_Kolvo, 0), 2) as \"EvnUslugaCommon_Kolvo\"
					FROM
						dbo.v_EvnUslugaPregnancySpec EUC
					WHERE (1 = 1)
						and EUC.EvnUslugaPregnancySpec_id = :id
					limit 1
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadEvnUslugaPregnancySpecGrid($data)
	{
		$query = "
		SELECT
			EvnUslugaPregnancySpec_id as \"EvnUslugaPregnancySpec_id\",
			to_char(p.EvnUslugaPregnancySpec_setDate, 'dd.mm.yyyy') AS \"EUPS_setDate\",
			p.EvnUslugaPregnancySpec_pid as \"EvnUslugaPregnancySpec_pid\",
			p.EvnUslugaPregnancySpec_rid as \"EvnUslugaPregnancySpec_rid\",
			p.Server_id as \"Server_id\",
			p.PersonEvn_id as \"PersonEvn_id\",
			p.Person_id as \"Person_id\",
			p.PayType_id as \"PayType_id\",
			uc.UslugaComplex_Name AS \"Usluga_Name\",
			uc.UslugaComplex_Code AS \"Usluga_Code\",
			p.Usluga_id as \"Usluga_id\",
			p.MedPersonal_id as \"MedPersonal_id\",
			p.UslugaPlace_id as \"UslugaPlace_id\",
			CASE p.UslugaPlace_id
				WHEN 1 --1 Отделение ЛПУ
					THEN ( SELECT    lpu_Nick
							  FROM      v_lpu l
							  WHERE     l.lpu_id = p.lpu_id
							)
				WHEN 2 --2 Другое ЛПУ
					THEN ( SELECT    lpu_Nick
							  FROM      v_lpu l
							  WHERE     l.lpu_id = p.lpu_uid
							)
					ELSE --3 Другая организация
					( SELECT Org_Nick
						 FROM   v_org o
						 WHERE  o.Org_id = p.org_uid
					)
			END AS \"lpu_name\",
			p.LpuSection_uid as \"LpuSection_uid\",
			p.EvnUslugaPregnancySpec_Kolvo AS \"EUPS_Kolvo\",
			p.Org_uid as \"Org_uid\",
			p.UslugaComplex_id as \"UslugaComplex_id\",
			p.EvnUslugaPregnancySpec_isCito AS \"EUPS_isCito\",
			p.MedPersonal_sid as \"MedPersonal_sid\",
			p.PregnancySpec_id as \"PregnancySpec_id\"
		FROM dbo.v_EvnUslugaPregnancySpec p
			left join v_UslugaComplex uc on uc.UslugaComplex_id = p.UslugaComplex_id
		WHERE PregnancySpec_id =  :PregnancySpec_id
		";
		$result = $this->db->query($query, array(
			'PregnancySpec_id' => (int)$data['PregnancySpec_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение..
	 */
	function saveEvnUslugaPregnancySpec($data)
	{
		$procedure = '';

		if (!isset($data['EvnUslugaPregnancySpec_id']) || $data['EvnUslugaPregnancySpec_id'] <= 0) {
			$procedure = 'p_EvnUslugaPregnancySpec_ins';
		}
		else {
			$procedure = 'p_EvnUslugaPregnancySpec_upd';
		}

		if (!isset($data['EvnUslugaPregnancySpec_pid'])) {
			$data['EvnUslugaPregnancySpec_pid'] = $data['EvnUslugaPregnancySpec_rid'];
		}

		if (isset($data['EvnUslugaCommon_setTime'])) {
			$data['EvnUslugaCommon_setDate'] .= ' ' . $data['EvnUslugaCommon_setTime'] . ':00:000';
		}
		if (isset($data['EvnUslugaCommon_disTime'])) {
			$data['EvnUslugaCommon_disDate'] .= ' ' . $data['EvnUslugaCommon_disTime'] . ':00:000';
		}
		//пересчет срока беременности на момент лаб обследования
		//$uslugaSetDT = DateTime::createFromFormat('Y-m-d H:i:s:u', $data['EvnUslugaCommon_setDate']);
		//$personDispBegDate = DateTime::createFromFormat('Y-m-d', $data['PersonDisp_begDate']);
		//$data['EvnUslugaPregnancySpec_PregnancyTerm'] = ((int)$data['PregnancySpec_Period']) + floor( $personDispBegDate->diff($uslugaSetDT)->days / 7);

		$query = "
			select
				EvnUslugaPregnancySpec_id as \"EvnUslugaPregnancySpec_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				EvnUslugaPregnancySpec_id := EvnUslugaPregnancySpec_id,
				EvnUslugaPregnancySpec_pid := :EvnUslugaPregnancySpec_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPregnancySpec_setDT := :EvnUslugaPregnancySpec_setDT,
				EvnUslugaPregnancySpec_disDT := :EvnUslugaPregnancySpec_disDT,
				PayType_id := :PayType_id,
				UslugaComplex_id :=      = :UslugaComplex_id,
				MedPersonal_id := :MedPersonal_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				EvnUslugaPregnancySpec_Kolvo := :EvnUslugaPregnancySpec_Kolvo,
				Org_uid := :Org_uid,
				PregnancySpec_id := :PregnancySpec_id,
				EvnUslugaPregnancySpec_PregnancyTerm := EvnUslugaPregnancySpec_PregnancyTerm,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				pmUser_id := :pmUser_id
			)

		";

		$queryParams = array(
			'EvnUslugaPregnancySpec_id' => $data['EvnUslugaCommon_id'],
			'EvnUslugaPregnancySpec_pid' => $data['EvnUslugaPregnancySpec_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaPregnancySpec_setDT' => $data['EvnUslugaCommon_setDate'],
			'EvnUslugaPregnancySpec_disDT' => $data['EvnUslugaCommon_disDate'],
			'PayType_id' => $data['PayType_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'Lpu_uid' => $data['Lpu_uid'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'EvnUslugaPregnancySpec_Kolvo' => $data['EvnUslugaCommon_Kolvo'],
			'Org_uid' => $data['Org_uid'],
			'PregnancySpec_id' => $data['PregnancySpec_id'],
			//'EvnUslugaPregnancySpec_PregnancyTerm' => $data['EvnUslugaPregnancySpec_PregnancyTerm'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data)
	{
		$procedure = "p_PregnancySpec_ins";

		if (!isset($data['PregnancySpec_id'])) {
			$data['PregnancySpec_id'] = null;
		}
		if ($data['PregnancySpec_id'] > 0) {
			$procedure = "p_PregnancySpec_upd";
		}

		$query = "
			select
				PregnancySpec_id as \"PregnancySpec_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				PregnancySpec_id := :PregnancySpec_id,
				PersonDisp_id := :PersonDisp_id,
				Lpu_aid := :Lpu_aid,
				PregnancySpec_Period := :PregnancySpec_Period,
				PregnancySpec_Count := :PregnancySpec_Count,
				PregnancySpec_CountBirth := :PregnancySpec_CountBirth,
				PregnancySpec_CountAbort := :PregnancySpec_CountAbort,
				PregnancySpec_BirthDT := :PregnancySpec_BirthDT,
				BirthResult_id := :BirthResult_id,
				PregnancySpec_OutcomPeriod := :PregnancySpec_OutcomPeriod,
				PregnancySpec_OutcomDT := :PregnancySpec_OutcomDT,
				BirthSpec_id := :BirthSpec_id,
				PregnancySpec_IsHIVtest  := :PregnancySpec_IsHIVtest ,
				PregnancySpec_IsHIV := :PregnancySpec_IsHIV,
				pmUser_id := :pmUser_id
			)
		";
		if (!isset($data['BirthResult_id'])) {
			$data['BirthResult_id'] = null;
		}
		if (!isset($data['PregnancySpec_OutcomPeriod'])) {
			$data['PregnancySpec_OutcomPeriod'] = null;
		}
		if (!isset($data['PregnancySpec_OutcomDT'])) {
			$data['PregnancySpec_OutcomDT'] = null;
		}
		if (!isset($data['BirthSpec_id'])) {
			$data['BirthSpec_id'] = null;
		}
		$queryParams = array(
			'PregnancySpec_id' => $data['PregnancySpec_id'],
			'PersonDisp_id' => $data['PersonDisp_id'],
			'Lpu_aid' => $data['Lpu_aid'],
			'PregnancySpec_Period' => $data['PregnancySpec_Period'],
			'PregnancySpec_Count' => $data['PregnancySpec_Count'],
			'PregnancySpec_CountBirth' => $data['PregnancySpec_CountBirth'],
			'PregnancySpec_CountAbort' => $data['PregnancySpec_CountAbort'],
			'PregnancySpec_BirthDT' => $data['PregnancySpec_BirthDT'],
			'BirthResult_id' => $data['BirthResult_id'],
			'PregnancySpec_OutcomPeriod' => $data['PregnancySpec_OutcomPeriod'],
			'PregnancySpec_OutcomDT' => $data['PregnancySpec_OutcomDT'],
			'BirthSpec_id' => $data['BirthSpec_id'],
			'PregnancySpec_IsHIVtest' => $data['PregnancySpec_IsHIVtest'],
			'PregnancySpec_IsHIV' => $data['PregnancySpec_IsHIV'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)'));
		}
	}

	/**
	 * Сохранение из дивижения
	 */
	function saveFromEvnSection($data)
	{
		//сохранение полей, которые редактируются из движения
		//dataToSave.BirthResult_id             = this.formFields.BirthResult_id            .getValue();
		//dataToSave.PregnancySpec_OutcomPeriod = this.formFields.PregnancySpec_OutcomPeriod.getValue();
		//dataToSave.PregnancySpec_OutcomDT     = this.formFields.PregnancySpec_OutcomDT    .getValue().format('d.m.Y');
		//dataToSave.BirthSpec_id               = this.formFields.BirthSpec_id              .getValue();
		$query = "
			with mv as (
				select
					PregnancySpec_id,
					PersonDisp_id,
					Lpu_aid,
					PregnancySpec_Period,
					PregnancySpec_Count,
					PregnancySpec_CountBirth,
					PregnancySpec_CountAbort,
					PregnancySpec_BirthDT,
					coalesce(BirthResult_id, :BirthResult_id) as BirthResult_id,
					coalesce(PregnancySpec_OutcomPeriod, :PregnancySpec_OutcomPeriod) as PregnancySpec_OutcomPeriod,
					coalesce(PregnancySpec_OutcomDT, :PregnancySpec_OutcomDT) as PregnancySpec_OutcomDT,
					coalesce(BirthSpec_id, :BirthSpec_id) as BirthSpec_id,
					PregnancySpec_IsHIVtest,
					PregnancySpec_IsHIV,
					:pmUser_id as pmUser_id
				FROM dbo.PregnancySpec
				WHERE PregnancySpec_id = :PregnancySpec_id
				limit 1
			)
			
			select
				PregnancySpec_id as \"PregnancySpec_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_PregnancySpec_upd(
				PregnancySpec_id := (select PregnancySpec_id from mv)
				PersonDisp_id := (select PersonDisp_id from mv)
				Lpu_aid := (select Lpu_aid from mv)
				PregnancySpec_Period := (select PregnancySpec_Period from mv)
				PregnancySpec_Count := (select PregnancySpec_Count from mv)
				PregnancySpec_CountBirth := (select PregnancySpec_CountBirth from mv)
				PregnancySpec_CountAbort := (select PregnancySpec_CountAbort from mv)
				PregnancySpec_BirthDT := (select PregnancySpec_BirthDT from mv)
				BirthResult_id := (select BirthResult_id from mv)
				PregnancySpec_OutcomPeriod := (select PregnancySpec_OutcomPeriod from mv)
				PregnancySpec_OutcomDT := (select PregnancySpec_OutcomDT from mv)
				BirthSpec_id := (select BirthSpec_id from mv)
				PregnancySpec_IsHIVtest := (select PregnancySpec_IsHIVtest from mv)
				PregnancySpec_IsHIV := (select PregnancySpec_IsHIV from mv)
				pmUser_id := (select pmUser_id from mv)
			)
		";
		if (!isset($data['BirthResult_id'])) $data['BirthResult_id'] = null;
		if (!isset($data['BirthSpecStac_OutcomPeriod'])) $data['BirthSpecStac_OutcomPeriod'] = null;
		if (!isset($data['BirthSpecStac_OutcomDT'])) $data['BirthSpecStac_OutcomDT'] = null;
		if (!isset($data['BirthSpec_id'])) $data['BirthSpec_id'] = null;
		$queryParams = array(
			'PregnancySpec_id' => $data['PregnancySpec_id'],
			'BirthResult_id' => $data['BirthResult_id'],
			'PregnancySpec_OutcomPeriod' => $data['BirthSpecStac_OutcomPeriod'],
			'PregnancySpec_OutcomDT' => $data['BirthSpecStac_OutcomDT'],
			'BirthSpec_id' => $data['BirthSpec_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)'));
		}
	}

	/**
	 * Сохранение осложнений беременности
	 */
	function savePregnancySpecComplication($data) {
		//сохранение осложнений беременности

		$procedure = "p_PregnancySpecComplication_ins";
		if ( (!empty($data['PregnancySpecComplication_id'])) &&($data['PregnancySpecComplication_id']>0)) {
			$procedure = "p_PregnancySpecComplication_upd";
		} else {
			$data['PregnancySpecComplication_id'] = null;
		}
		$query = "
			select
				PregnancySpecComplication_id as \"PregnancySpecComplication_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.{$procedure}(
				PregnancySpecComplication_id := :PregnancySpecComplication_id,
				PregnancySpec_id := :PregnancySpec_id, -- bigint
				PregnancySpecComplication_setDT := :PregnancySpecComplication_setDT,
				Diag_id := :Diag_id, -- bigint
				pmUser_id := :pmUser_id, -- bigint
			)
		";
		$queryParams = array(
			'PregnancySpecComplication_id'    => $data['PregnancySpecComplication_id'   ],
			'PregnancySpec_id'                => $data['PregnancySpec_id'               ],
			'PregnancySpecComplication_setDT' => $data['PSC_setDT'                      ],
			'Diag_id'                         => $data['Diag_id'                        ],
			'pmUser_id'                       => $data['pmUser_id'                      ]
		);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}

	/**
	 * Сохранение осложнений беременности
	 */
	function savePregnancySpecExtragenitalDisease($data) {
		//сохранение осложнений беременности

		$procedure = "p_PregnancySpecExtragenitalDisease_ins";
		if ( (!empty($data['PSED_id'])) &&($data['PSED_id']>0)) {
			$procedure = "p_PregnancySpecExtragenitalDisease_upd";
		} else {
			$data['PSED_id'] = null;
		}
		$query = "
			select
				PregnancySpecExtragenitalDisease_id as \"PSED_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.{$procedure}(
				PregnancySpecExtragenitalDisease_id := :PSED_id,
				PregnancySpec_id := :PregnancySpec_id, -- bigint
				PregnancySpecExtragenitalDisease_setDT = :PregnancySpecExtragenitalDisease_setDT,
				Diag_id := :Diag_id, -- bigint
				pmUser_id := :pmUser_id, -- bigint
			)
		";
		$queryParams = array(
			'PSED_id'    => $data['PSED_id'   ],
			'PregnancySpec_id'                => $data['PregnancySpec_id'               ],
			'PregnancySpecExtragenitalDisease_setDT' => $data['PSED_setDT'],
			'Diag_id'                         => $data['Diag_id'                        ],
			'pmUser_id'                       => $data['pmUser_id'                      ]
		);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function loadPregnancySpecComplication($data) {
		$params = array(
			'PregnancySpec_id' => $data['PregnancySpec_id']
		);
		$query = "
			SELECT
				PregnancySpecComplication_id as \"PregnancySpecComplication_id\",
				PregnancySpec_id as \"PregnancySpec_id\",
				to_char(PregnancySpecComplication_setDT, 'dd.mm.yyyy') AS \"PSC_setDT\",
				c.Diag_id as \"Diag_id\",
				d.diag_fullname AS \"Diag_Name\",
				1 AS \"RecordStatus_Code\"
			FROM dbo.v_PregnancySpecComplication c
				left join v_Diag d on d.diag_id = c.diag_id
			WHERE PregnancySpec_id = :PregnancySpec_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function loadPregnancySpecExtragenitalDisease($data) {
		$params = array(
			'PregnancySpec_id' => $data['PregnancySpec_id']
		);
		$query = "
			SELECT
				PregnancySpecExtragenitalDisease_id as \"PSED_id\",
				PregnancySpec_id as \"PregnancySpec_id\",
				to_char(PregnancySpecExtragenitalDisease_setDT, 'dd.mm.yyyy') AS \"PSED_setDT\",
				c.Diag_id as \"Diag_id\",
				d.diag_fullname  AS \"Diag_Name\",
				1 AS \"RecordStatus_Code\"
			FROM dbo.v_PregnancySpecExtragenitalDisease c
				left join v_Diag d on d.diag_id = c.diag_id
			WHERE PregnancySpec_id = :PregnancySpec_id
			";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function deletePregnancySpecComplication($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PregnancySpecComplication_del(
				PregnancySpecComplication_id := :PregnancySpecComplication_id
			)
		";

		$result = $this->db->query($query, array(
			'PregnancySpecComplication_id' => $data['PregnancySpecComplication_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function deletePregnancySpecExtragenitalDisease($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PregnancySpecExtragenitalDisease_del(
				PregnancySpecExtragenitalDisease_id := :PregnancySpecExtragenitalDisease_id
			)
		";

		$result = $this->db->query($query, array(
			'PregnancySpecExtragenitalDisease_id' => $data['PregnancySpecExtragenitalDisease_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка другого лпу
	 */
	function loadAnotherLpuList($data){
		$sql = "
			SELECT
				(
					di.Diag_Code
						|| '. ' || di.Diag_Name
						|| ' ' || Lpu_Nick
						|| ' (c ' || to_char(d.PersonDisp_begDate, 'dd.mm.yyyy')
						|| ' по ' ||
					(CASE WHEN d.PersonDisp_endDate IS NULL
						THEN 'текущий момент'
						ELSE to_char(d.PersonDisp_endDate, 'dd.mm.yyyy')
					END) || ')'
				) AS \"PersonDisp_Name\",
				PersonDisp_id as \"PersonDisp_id\",
				ps.PregnancySpec_id as \"PregnancySpec_id\"
			FROM personDisp d
				inner join on PersonDisp pd on d.person_id = pd.person_id and d.lpu_id = pd.lpu_id
				inner join on SicknessDiag sd on d.diag_id = sd.diag_id and sd.PrivilegeType_id = 509
				left join v_Diag di on di.Diag_id = d.Diag_id
				left join v_lpu l on l.Lpu_id = d.Lpu_id
				left join lateral(
					SELECT PregnancySpec_id
					FROM dbo.PregnancySpec s
					WHERE s.PersonDisp_id = d.PersonDisp_id
					ORDER BY  d.PersonDisp_updDT DESC
					limit 1
				) ps on true
			where
				pd.PersonDisp_id = :PersonDisp_id
		";
		$result = $this->db->query($sql, ['PersonDisp_id' => $data['PersonDisp_id']]);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadPregnancySpecList($data){
		$result = false;
		$sql = "
			(SELECT
				'--Не наблюдалась--' as \"opt\",
				null as \"PregnancySpec_id\",
				NULL as \"Person_id\",
				NULL as \"Server_id\",
				NULL as \"PersonDisp_id\"
			)
			
			UNION ALL
			
			SELECT
				(
					di.diag_FullName
					|| ' (' || l.lpu_nick
					|| ', c ' || to_char(d.PersonDisp_begDate, 'dd.mm.yyyy')
					|| ' по ' ||
					CASE WHEN d.PersonDisp_endDate IS NULL
						THEN 'текущий момент'
						ELSE to_char(d.PersonDisp_endDate, 'dd.mm.yyyy')
					END || ')'
				) AS \"opt\",
				ps.PregnancySpec_id AS \"PregnancySpec_id\",
				d.Person_id as \"Person_id\",
				d.Server_id as \"Server_id\",
				d.PersonDisp_id as \"PersonDisp_id\"
			FROM dbo.v_personDisp d
				left join v_Diag di on di.Diag_id = d.Diag_id
				inner join SicknessDiag sd on d.diag_id = sd.diag_id and sd.Sickness_id = 9
				left join v_lpu l on l.lpu_id = d.lpu_id
				inner join lateral(
					select
						PregnancySpec_id
					FROM dbo.PregnancySpec s
					WHERE s.PersonDisp_id = d.PersonDisp_id
					order by PregnancySpec_id DESC
					limit 1
				) ps on true
			WHERE
				person_id = :Person_id
				--AND EXISTS (SELECT PregnancySpec_id FROM dbo.PregnancySpec s WHERE s.PersonDisp_id = d.PersonDisp_id)
		";
		$q = $this->db->query($sql, ['Person_id' => $data['Person_id']]);
		if (is_object($q)){
			$result = $q->result('array');
		}
		return $result;
	}

}
