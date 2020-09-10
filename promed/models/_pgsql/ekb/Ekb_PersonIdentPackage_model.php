<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Person_model $Person_model
 */

require_once(APPPATH.'models/_pgsql/PersonIdentPackage_model.php');

class Ekb_PersonIdentPackage_model extends PersonIdentPackage_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Полученеи списка сформированных пакетов для идентификации
	 */
	function loadPersonIdentPackageGrid($data) {
		$params = array();
		$filters = array("1=1");

		$filters[] = "PIP.PersonIdentPackage_Name not in ('PersonIdentPackage_Name','tmpPersonIdentPackage')";

		if (!isSuperAdmin() && !empty($data['Lpu_id'])) {
			$query = "select Lpu_RegNomN2 as \"Lpu_RegNomN2\" from v_Lpu where Lpu_id = :Lpu_id";
			$mcode = $this->getFirstResultFromQuery($query, $data, true);
			if (!$mcode) return false;

			$filters[] = "right(PIP.PersonIdentPackage_Name,15) ilike '/' || :mcode || '%'";
			$params['mcode'] = sprintf('%04d', $this->cutMcode($mcode));
		}

		if (isset($data['PersonIdentPackage_DateRange']) && !empty($data['PersonIdentPackage_DateRange'][0]) && !empty($data['PersonIdentPackage_DateRange'][1])) {
			$filters[] = "PIP.PersonIdentPackage_begDate between :PersonIdentPackage_begDateRange and :PersonIdentPackage_endDateRange";
			$params['PersonIdentPackage_begDateRange'] = $data['PersonIdentPackage_DateRange'][0];
			$params['PersonIdentPackage_endDateRange'] = $data['PersonIdentPackage_DateRange'][1];
		}
		if (!empty($data['PersonIdentPackage_IsResponseRetrieved'])) {
			$filters[] = "PIP.PersonIdentPackage_IsResponseRetrieved = :PersonIdentPackage_IsResponseRetrieved";
			$params['PersonIdentPackage_IsResponseRetrieved'] = $data['PersonIdentPackage_IsResponseRetrieved'];
		}

		/*$filters[] = "not exists(
			select * from v_PersonIdentPackagePos with(nolock)
			where PersonIdentPackage_id = PIP.PersonIdentPackage_id and Evn_id is null
		)";*/

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				PIP.PersonIdentPackage_id as \"PersonIdentPackage_id\",
				to_char(PIP.PersonIdentPackage_begDate, 'dd.mm.yyyy') as \"PersonIdentPackage_begDate\",
				PIP.PersonIdentPackage_IsResponseRetrieved as \"PersonIdentPackage_IsResponseRetrieved\",
				PIP.PersonIdentPackage_Name as \"PersonIdentPackage_File\",
				ActualCount.Value as \"PersonIdentPackage_ActualCount\",
				ErrorCount.Value as \"PersonIdentPackage_ErrorCount\"
				-- end select
			from
				-- from
				v_PersonIdentPackage PIP
				left join lateral (
					select count(distinct PIPP.Person_id) as Value
					from v_PersonIdentPackagePos PIPP
					where PIPP.PersonIdentPackage_id = PIP.PersonIdentPackage_id
					and PIPP.PersonIdentState_id = 1
					limit 1
				) ActualCount on true
				left join lateral (
					select count(distinct PIPP.Person_id) as Value
					from v_PersonIdentPackagePos PIPP
					where PIPP.PersonIdentPackage_id = PIP.PersonIdentPackage_id
					and exists(
						select * from v_PersonIdentPackagePosError
						where PersonIdentPackagePos_id = PIPP.PersonIdentPackagePos_id
					)
					limit 1
				) ErrorCount on true
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PIP.PersonIdentPackage_begDate
				-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$count_result = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($result) || !is_array($count_result)) {
			return false;
		}

		$response = array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt'],
		);

		return $response;
	}

	/**
	 * Получение списка записей в пакете для идентификации
	 */
	function loadPersonIdentPackagePosGrid($data) {
		$params = array();
		$filters = array("1=1");

		$params['PersonIdentPackage_id'] = $data['PersonIdentPackage_id'];

		if (!empty($data['Person_FIO'])) {
			$filters[] = "PersonFIO.Value ilike :Person_FIO || '%'";
			$params['Person_FIO'] = $data['Person_FIO'];
		}
		if (!empty($data['PersonIdentPackagePosErrorType_Code'])) {
			$filters[] = "PIPPET.PersonIdentPackagePosErrorType_Code = :PersonIdentPackagePosErrorType_Code";
			$params['PersonIdentPackagePosErrorType_Code'] = $data['PersonIdentPackagePosErrorType_Code'];
		}
		if (!empty($data['PersonIdentState_id'])) {
			$filters[] = "PIPP2.PersonIdentState_id = :PersonIdentState_id";
			$params['PersonIdentState_id'] = $data['PersonIdentState_id'];
		}
		if (!empty($data['Evn_id'])) {
			$filters[] = ":Evn_id in (select Evn_id from pos_list where Person_id = P.Person_id)";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			-- addit with
			with pos_list as (
				select *
				from v_PersonIdentPackagePos
				where PersonIdentPackage_id = :PersonIdentPackage_id
			)
			-- end addit with
			select
				-- select
				PS.Person_id as \"Person_id\",
				to_char(PIPP1.PersonIdentPackagePos_identDT, 'dd.mm.yyyy') as \"PersonIdentPackage_identDT\",
				to_char(PIPP2.PersonIdentPackagePos_identDT2, 'dd.mm.yyyy') as \"PersonIdentPackage_identDT2\",
				PersonFIO.Value as \"Person_FIO\",
				PIPPE.PersonIdentPackagePosErrorType_id as \"PersonIdentPackagePosErrorType_id\",
				PIPPET.PersonIdentPackagePosErrorType_Code as \"PersonIdentPackagePosErrorType_Code\",
				PIPPET.PersonIdentPackagePosErrorType_Name as \"PersonIdentPackagePosErrorType_Name\",
				PIPPET.PersonIdentPackagePosErrorType_Decscription as \"PersonIdentPackagePosErrorType_Decscription\",
				PIS.PersonIdentState_id as \"PersonIdentState_id\",
				PIS.PersonIdentState_Code as \"PersonIdentState_Code\",
				PIS.PersonIdentState_Name as \"PersonIdentState_Name\",
				PIPP2.Evn_id as \"EvnList\",
				PIA.PersonIdentAlgorithm_Code as \"PersonIdentAlgorithm_Code\",
				PIA.PersonIdentAlgorithm_Value as \"PersonIdentAlgorithm_Value\",
				PIPP2.PersonIdentPackagePos_PolisNum as \"PersonIdentPackagePos_PolisNum\",
				SMO.OrgSMO_Nick as \"OrgSMO_Nick\",
				PT.PolisType_Name as \"PolisType_Name\",
				to_char(PIPP2.PersonIdentPackagePos_recDate, 'dd.mm.yyyy') as \"PersonIdentPackagePos_recDate\",
				to_char(PIPP2.PersonIdentPackagePos_insurEndDate, 'dd.mm.yyyy') as \"PersonIdentPackagePos_insurEndDate\",
				case 
					when length(PIPP2.PersonIdentPackagePos_Snils) = 11 
					then left(PIPP2.PersonIdentPackagePos_Snils,3) || '-' || substring(PIPP2.PersonIdentPackagePos_Snils from 4 for 3) || '-' || substring(PIPP2.PersonIdentPackagePos_Snils from 7 for 3) || ' ' || right(PIPP2.PersonIdentPackagePos_Snils,2)
					else PIPP2.PersonIdentPackagePos_Snils
				end as \"PersonIdentPackagePos_Snils\",
				to_char(PIPP2.PersonIdentPackagePos_BirthDay, 'dd.mm.yyyy') as \"PersonIdentPackagePos_BirthDay\",
				SexP.Sex_Name as \"Sex_Name\"
				-- end select
			from
				-- from
				Person P
				inner join lateral (
					select *
					from pos_list
					where Person_id = P.Person_id
					order by PersonIdentPackagePos_identDT asc
					limit 1
				) PIPP1 on true
				inner join lateral (
					select *
					from pos_list
					where Person_id = P.Person_id
					order by PersonIdentPackagePos_identDT2 desc
					limit 1
				) PIPP2 on true
				left join v_Evn E on E.Evn_id = PIPP2.Evn_id
				inner join lateral (
					select	PS.*
					from
						v_Person_all PS
					where
						(
							E.Evn_id is null
							and PS.Person_id = P.Person_id
						) or (
							E.Evn_id is not null
							and PS.PersonEvn_id = E.PersonEvn_id
							and PS.Server_id = E.Server_id
						)
					order by
						PS.PersonEvn_insDT desc
					limit 1
				) PS on true
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join lateral (
					select (
						concat_ws(' ', rtrim(PS.Person_SurName), rtrim(PS.Person_FirName), rtrim(PS.Person_SecName)) || ', ' || Sex.Sex_Name, ', ' || to_char(PS.Person_BirthDay, 'dd.mm.yyyy')
					) as Value
				) PersonFIO on true
				left join v_PersonIdentAlgorithm PIA on PIA.PersonIdentAlgorithm_id = PIPP2.PersonIdentAlgorithm_id
				left join v_PersonIdentPackagePosError PIPPE on PIPPE.PersonIdentPackagePos_id = PIPP2.PersonIdentPackagePos_id
				left join v_PersonIdentPackagePosErrorType PIPPET on PIPPET.PersonIdentPackagePosErrorType_id = PIPPE.PersonIdentPackagePosErrorType_id
				left join v_PersonIdentState PIS on PIS.PersonIdentState_id = PIPP2.PersonIdentState_id
				left join v_PolisType PT on PT.PolisType_id = PIPP2.PolisType_id
				left join v_OrgSMO SMO on SMO.OrgSMO_id = PIPP2.PersonIdentPackagePos_Smo
				left join v_Sex SexP on SexP.Sex_id = PIPP2.PersonIdentPackagePos_Sex
				-- end from
			where
				-- where
				P.Person_id in (select distinct Person_id from pos_list)
				and {$filters_str}
				-- end where
			order by
				-- order by
				PersonFIO.Value
				-- end order by
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$count_result = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($result) || !is_array($count_result)) {
			return false;
		}

		if (count($result) > 0) {
			$Person_ids = array();
			foreach($result as $item) {
				$Person_ids[] = $item['Person_id'];
			}
			$Person_ids_str = implode(",", $Person_ids);

			$query = "
			select
				PIPP.Person_id as \"Person_id\",
				E.PersonEvn_id as \"PersonEvn_id\",
				E.Server_id as \"Server_id\",
				E.Evn_id as \"Evn_id\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				EPLD.DispClass_id as \"DispClass_id\"
			from
				v_PersonIdentPackagePos PIPP
				inner join v_Evn E on E.Evn_id = PIPP.Evn_id
				left join v_EvnPLDisp EPLD on EPLD.EvnPLDisp_id = E.Evn_id
			where
				PIPP.PersonIdentPackage_id = :PersonIdentPackage_id
				and PIPP.Person_id in ({$Person_ids_str})
			order by
				E.Evn_id
		";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return false;
			}

			$EvnListByPerson = array();
			foreach($resp as $item) {
				$EvnListByPerson[$item['Person_id']][] = $item;
			}
			foreach($result as &$item) {
				if ( array_key_exists($item['Person_id'], $EvnListByPerson) ) {
					$item['EvnList'] = json_encode($EvnListByPerson[$item['Person_id']]);
				}
			}
		}

		$response = array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt'],
		);

		return $response;
	}

	/**
	 * @param int $mcode
	 * @param DateTime $date
	 * @return DateTime
	 */
	function getDateTimeForPackageName($mcode, $date) {
		$params = array(
			'name' => sprintf('%04d%s', $mcode, $date->format('dHi')),
			'mcode' => sprintf('%04d', $mcode),
			'dt' => $date->format('Y-m-d H:i'),
		);
		if($this->getFirstResultFromQuery("select count(*) from v_PersonIdentPackage where PersonIdentPackage_Name ilike '%/' || :name ||'.SCD'", $params))
		{
		  $query = "
		  	with Package1 as (
					select 
					left(right(PersonIdentPackage_Name, 14), 10) as Name,
					PersonIdentPackage_begDate as Date
					from v_PersonIdentPackage
					where PersonIdentPackage_Name ilike '%/' || :mcode || '%.SCD'
				),
				Package as (
					select
					Date || cast(substring(Name from length(Name)-3 for 2) || ':' || substring(Name from length(Name)-1 for 2) as datetime) as DT
					from Package1
				)
				select p.DT - '1 minute' as \"empty_dt\"
				from Package p
				WHERE (SELECT 1 FROM Package t WHERE t.DT = p.DT - '1 minute') IS NULL
				order by p.DT desc
				limit 1
			";
		}
		else
		{
		  $query = "select cast(:dt as datetime) as \"empty_dt\"";
		}
		$empty_dt = $this->getFirstResultFromQuery($query, $params);
		if ($empty_dt === false) {
			throw new Exception('Ошибка при получении даты формирования пакета');
		}
		return $empty_dt;
	}

	/**
	 * @param string $mcode
	 * @return string
	 */
	function cutMcode($mcode) {
		return substr($mcode, strlen($mcode)-4, 4);
	}

	/**
	 * @param int $mcode
	 * @param DateTime $date
	 * @return array
	 */
	function createPersonIdentFileParams($mcode, $date) {
		$mcode = $this->cutMcode($mcode);
		$dt = $this->getDateTimeForPackageName($mcode, $date);
		$package_sign = sprintf('%04d%s', $mcode, $dt->format('dHi'));
		$package_name = $package_sign.'.SCD';
		$file_name = 'QuerySCD.xml';
		$out_dir = EXPORTPATH_ROOT.'person_ident_package/'.time().'/';
		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}

		return array(
			'out_dir' => $out_dir,
			'file_name' => $file_name,
			'package_sign' => $package_sign,
			'package_name' => $package_name,
			'file_path' => $out_dir.$file_name,
			'package_path' => $out_dir.$package_name,
		);
	}

	/**
	 * Добавление пакетов для идентфикации
	 */
	function addPersonIdentPackage($data) {
		set_time_limit(0);

		$response = array(
			'success' => true,
			'PersonCount' => 0,
			'PackageCount' => 0,
			'PackageList' => array(),
		);

		$queryCount = function($query, $params = array()) {
			$query = getCountSQLPH($query);
			return $this->getFirstResultFromQuery($query, $params);
		};

		$this->beginTransaction();

		try {
			$mcode = $this->getFirstResultFromQuery("
				select Lpu_RegNomN2 as \"Lpu_RegNomN2\" from v_Lpu where Lpu_id = :Lpu_id limit 1
			", array('Lpu_id' => $data['Lpu_id']), true);
			if ($mcode === false) {
				throw new Exception('Ошибка при получении кода МО');
			}
			if (empty($mcode)) {
				throw new Exception('Отсутствует федеральный реестровый код МО');
			}

			$getSelector = function($tmp_table = null) {
				$filter = "";
				if (!empty($tmp_table)) {
					$filter = "and not exists (select t.personId from {$tmp_table} t where t.personId = Person_id limit 1)";
				}
				return "
					select
						-- select
						Person_id as \"Person_id\"
						-- end select
					from
						-- from
						PersonIdentPackagePos
						-- end from
					where
						-- where
						coalesce(PersonIdentPackage_id,0) = coalesce(:tmpPersonIdentPackage_id,0)
						and PersonIdentPackagePos_identDT2 is not null
						{$filter}
						-- end where
					order by
						-- order by
						Person_id
						-- end order by
				";
			};
			$countParams = array(
				'tmpPersonIdentPackage_id' => !empty($data['tmpPersonIdentPackage_id'])?$data['tmpPersonIdentPackage_id']:null,
			);

			while($queryCount($getSelector(), $countParams) > 0) {
				//Формирование названия файла
				$date = date_create();
				$fileParams = $this->createPersonIdentFileParams($mcode, $date);

				//Добавление нового пустого пакета для идентификации
				$resp = $this->savePersonIdentPackage(array(
					'PersonIdentPackage_id' => null,
					'PersonIdentPackage_Name' => $fileParams['package_path'],
					'PersonIdentPackage_begDate' => $date->format('Y-m-d'),
					'PersonIdentPackage_IsResponseRetrieved' => 1,
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$PersonIdentPackage_id = $resp[0]['PersonIdentPackage_id'];

				//Заполнения пакета записями для идентификации
				$tmp_table = '#tmp'.time().sprintf('%04d', mt_rand(0,9999));
				$tmp_person_table = $tmp_table.'_person';
				$tmp_pos_table = $tmp_table.'_pos';

				$this->db->query("
					DROP TABLE IF EXISTS {$tmp_table};
					create table {$tmp_table} (
						id bigint primary key,
						nrec bigint,
						personId bigint,
						date1 date,
						date2 date,
						fam varchar(30),
						im varchar(30),
						ot varchar(30),
						w bigint,
						dr date,
						vpolis int,
						npolis varchar(19),
						doctype int,
						docser varchar(10),
						docnum varchar(30),
						snils varchar(14),
						mr varchar(300)
					);
					
					DROP TABLE IF EXISTS {$tmp_person_table};
					create table {$tmp_person_table} (
						Person_id bigint primary key
					);
					
					DROP TABLE IF EXISTS {$tmp_pos_table};
					create table {$tmp_pos_table} (
						PersonIdentPackagePos_id bigint primary key,
						PersonIdentPackage_id bigint,
						Evn_id bigint,
						CmpCallCard_id bigint,
						Person_id bigint,
						PersonIdentPackagePos_NumRec int,
						PersonIdentPackagePos_identDT datetime,
						PersonIdentPackagePos_identDT2 datetime,
						PersonIdentState_id bigint
					);
				");

				$step_size = !empty($data['StepSize'])?$data['StepSize']:250;            //Обрабатывается запиcей ПАЦИЕНТОВ за один шаг
				$max_package_size = !empty($data['PackageSize'])?$data['PackageSize']:100000;    //Макс. кол-во записей ПАЦИЕНТОВ в пакете
				$current_package_size = 0;
				$selector = $getSelector($tmp_table);

				while (
					$current_package_size < $max_package_size &&
					$queryCount($selector, $countParams) > 0
				) {
					$_step_size = (($current_package_size + $step_size) >= $max_package_size)
						? $max_package_size - $current_package_size
						: $step_size;

					$params = array(
						'PersonIdentPackage_id' => $PersonIdentPackage_id,
						'tmpPersonIdentPackage_id' => !empty($data['tmpPersonIdentPackage_id']) ? $data['tmpPersonIdentPackage_id'] : null,
						'currentSize' => $current_package_size,
					);

					$person_query = getLimitSQLPH($selector, 0, $_step_size, 'distinct');

					$this->db->query("
						delete {$tmp_person_table};
						insert into {$tmp_person_table} {$person_query};
					", $params);
					$this->db->query("
						delete from {$tmp_pos_table};
						insert into {$tmp_pos_table}
						select
							PIPP.PersonIdentPackagePos_id,
							:PersonIdentPackage_id as PersonIdentPackage_id,
							PIPP.Evn_id,
							PIPP.CmpCallCard_id,
							P.Person_id,
							:currentSize + dense_rank() over(order by P.Person_id) as PersonIdentPackagePos_NumRec,
							PIPP.PersonIdentPackagePos_identDT,
							PIPP.PersonIdentPackagePos_identDT2,
							null as PersonIdentState_id
						from
							PersonIdentPackagePos PIPP
							inner join {$tmp_person_table} P on P.Person_id = PIPP.Person_id
						where
							coalesce(PIPP.PersonIdentPackage_id,0) = coalesce(:tmpPersonIdentPackage_id,0)
							and PIPP.PersonIdentPackagePos_identDT2 is not null;
					", $params);
					$this->db->query("
						update PIPP
						set PersonIdentPackage_id = PIPP1.PersonIdentPackage_id,
							PersonIdentPackagePos_NumRec = PIPP1.PersonIdentPackagePos_NumRec
						from PersonIdentPackagePos PIPP
						inner join {$tmp_pos_table} PIPP1 on PIPP1.PersonIdentPackagePos_id = PIPP.PersonIdentPackagePos_id;
					", $params);
					$res = $this->db->query("
						insert into {$tmp_table}
						select
							PIPP2.PersonIdentPackagePos_id as id,
							PIPP1.PersonIdentPackagePos_NumRec as nrec,
							PIPP1.Person_id as personId,
							to_char(PIPP1.PersonIdentPackagePos_identDT, 'yyyy-mm-dd') as date1,
							to_char(PIPP2.PersonIdentPackagePos_identDT2, 'yyyy-mm-dd') as date2,
							rtrim(PS.Person_SurName) as fam,
							rtrim(PS.Person_FirName) as im,
							rtrim(PS.Person_SecName) as ot,
							PS.Sex_id as w,
							to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as dr,
							PT.PolisType_CodeF008 as vpolis,
							rtrim(Polis.Polis_Num) as npolis,
							DT.DocumentType_Code as doctype,
							-- #111753
							case
								when DT.DocumentType_Code = 14 and length(D.Document_Ser) = 4 then LEFT(D.Document_Ser, 2) || ' ' || RIGHT(D.Document_Ser, 2)
								else D.Document_Ser
							end as docser,
							D.Document_Num as docnum,
							substring(PS.Person_Snils from 1 for 3) || '-' || substring(PS.Person_Snils from 4 for 3) || '-' || substring(PS.Person_Snils from 7 for 3) || ' ' || right(PS.Person_Snils,2) as snils,
							BA.Address_Address as mr
						from
							{$tmp_person_table} P
							inner join lateral (
								select PIPP.*
								from {$tmp_pos_table} PIPP
								where PIPP.Person_id = P.Person_id
								order by PIPP.PersonIdentPackagePos_identDT asc
								limit 1
							) PIPP1 on true
							inner join (
								select PIPP.*
								from {$tmp_pos_table} PIPP
								where PIPP.Person_id = P.Person_id
								order by PIPP.PersonIdentPackagePos_identDT2 desc
								limit 1
							) PIPP2 on true
							left join v_Evn E on E.Evn_id = PIPP2.Evn_id
							left join v_CmpCallCard CCC on CCC.CmpCallCard_id = PIPP2.CmpCallCard_id
							inner join (
								select PS.*
								from v_Person_all PS
								where
								(E.Evn_id is null and CCC.CmpCallCard_id is null and PS.Person_id = P.Person_id and PS.PersonEvn_insDT <= PIPP2.PersonIdentPackagePos_identDT) or
								(E.Evn_id is not null and P.Person_id = E.Person_id and PS.PersonEvn_id = E.PersonEvn_id and PS.Server_id = E.Server_id) or
								(CCC.CmpCallCard_id is not null and PS.Person_id = P.Person_id) or
								(E.Evn_id is not null and P.Person_id <> E.Person_id and PS.Person_id = P.Person_id and PersonEvn_insDT <= E.Evn_setDate)
								order by PS.PersonEvn_insDT desc
								limit 1
							) PS
							left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
							left join v_PolisType PT on PT.PolisType_id = Polis.PolisType_id
							left join v_Document D on D.Document_id = PS.Document_id
							left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
							left join v_PersonBirthPlace PBP on PBP.Person_id = PS.Person_id
							left join v_Address BA on BA.Address_id = PBP.Address_id;
					", $params);
					$this->db->query("
						update {$tmp_pos_table}
						set PersonIdentState_id = case when (
							(coalesce(T.FAM,'') <> '' and coalesce(T.IM,'') <> '' and coalesce(T.OT,'') <> '' and T.DR is not null) 
							or (T.VPOLIS is not null)
						) then 4 else 5 end
						from {$tmp_pos_table} PIPP
						inner join {$tmp_table} T on T.personId = PIPP.Person_id
					");
					$this->db->query("
						update PIPP with(rowlock)
						set PersonIdentState_id = PIPP1.PersonIdentState_id
						from PersonIdentPackagePos PIPP
						inner join {$tmp_pos_table} PIPP1 on PIPP1.PersonIdentPackage_id = PIPP.PersonIdentPackage_id
					", $params);
					$this->db->query("
						with stateList as (
							select distinct
								PIPP.Person_id,
								PIPP.PersonIdentState_id
							from PersonIdentPackagePos PIPP
							inner join {$tmp_pos_table} PIPP1 on PIPP1.PersonIdentPackagePos_id = PIPP.PersonIdentPackagePos_id
						)
						update Person
						set Person_identDT = dbo.tzGetDate(),
							PersonIdentState_id = S.PersonIdentState_id
						from Person P
						inner join stateList S on S.Person_id = P.Person_id;
					", $params);

					$current_package_size = $this->getFirstResultFromQuery("
						select count(*) as cnt from {$tmp_table}
					");
					if ($current_package_size === false) {
						throw new Exception('Ошибка при получении текущего количество записей в пакете');
					}
				}

				//Запрос данных из пакета для формирование файла
				$query = " 
					select
						-- select
						T.*
						-- end select
					from
						-- from
						{$tmp_table} T
						inner join v_PersonIdentPackagePos PIPP on PIPP.PersonIdentPackagePos_id = T.id
						-- end from
					where 
						-- where
						PIPP.PersonIdentState_id = 4
						-- end where
					order by
						-- order by
						NREC
						-- end order by
				";

				$this->packingPersonIdentData($query, array(), $fileParams, $response);
				$response['PackageList'][] = array(
					'id' => $PersonIdentPackage_id,
					'name' => $fileParams['package_name'],
					'path' => $fileParams['package_path'],
				);

				if (!empty($data['PackageCount']) && $response['PackageCount'] >= $data['PackageCount']) {
					break;
				}
			}

			if (!empty($_REQUEST['getDebug'])) {
				$this->rollbackTransaction();
			} else {
				$this->commitTransaction();
			}
		} catch (Exception $e) {
			return $this->createError($e->getCode(), $e->getMessage());
		}

		return array($response);
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function createCustomPersonIdentPackages($querySelector, $data, $isRegistry = false, &$stat = array('PackageCount' => 0, 'PersonCount' => 0)) {
		if ($isRegistry) {
			$this->db = $this->load->database('default', true);
		}

		if (empty($data['Lpu_id']) || empty($data['pmUser_id'])) {
			throw new Exception('Отсутствуют параметры для создания пакета');
		}

		$tmpPackage = $this->savePersonIdentPackage(array(
			'PersonIdentPackage_id' => null,
			'PersonIdentPackage_Name' => 'tmpPersonIdentPackage',
			'PersonIdentPackage_begDate' => date_create()->format('Y-m-d'),
			'PersonIdentPackage_IsResponseRetrieved' => 1,
			'pmUser_id' => $data['pmUser_id'],
		));
		if (!$this->isSuccessful($tmpPackage)) {
			throw new Exception($tmpPackage[0]['Error_Msg']);
		}
		$data['tmpPersonIdentPackage_id'] = $tmpPackage[0]['PersonIdentPackage_id'];

		if ($isRegistry) {
			$this->db = $this->load->database('registry', true);
		}

		//$this->insertPersonIdentPackagePosFromQuery($querySelector, $data, $isRegistry);
		$additParams = array(
			'PersonIdentPackage_id' => !empty($data['tmpPersonIdentPackage_id'])?$data['tmpPersonIdentPackage_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'DT' => date_create()->format('Y-m-d H:i:s'),
		);

		$page_size = 1000;
		$result = $this->db->query($querySelector);
		$currentRowOnPage = 1;
		$posList = [];
		while ($row = $result->_fetch_assoc()){
			$posList[] = $row;

			if ( $currentRowOnPage == $page_size ){
				$this->insertPersonIdentPackagePosList($posList, $additParams, $isRegistry);
				$currentRowOnPage = 1;
				$posList = [];
			}
			$currentRowOnPage = $currentRowOnPage + 1;
		}
		$this->insertPersonIdentPackagePosList($posList, $additParams, $isRegistry);

		if ($isRegistry) {
			$this->db = $this->load->database('default', true);
		}

		$resp = $this->addPersonIdentPackage($data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		$stat['PackageCount'] = $resp[0]['PackageCount'];
		$stat['PersonCount'] = $resp[0]['PersonCount'];
		$packageList = $resp[0]['PackageList'];
		$response = '';

		if (count($packageList) == 1) {
			$response = $packageList[0]['path'];
		} else if(count($packageList) > 1) {
			$response = EXPORTPATH_ROOT.'person_ident_package/'.time().'.zip';
			$zip = new ZipArchive();
			$zip->open($response, ZIPARCHIVE::CREATE);
			foreach($packageList as $package) {
				$zip->AddFile($package['path'], $package['name']);
			}
			$zip->close();
		}

		if ($isRegistry) {
			$this->db = $this->load->database('registry', true);
		}

		return $response;
	}

	/**
	 * @param $queryIdentData
	 * @param $queryIdentDataParams
	 * @param array $fileParams Параметры файла пакета, который нужно сформировать
	 * @param array $stat
	 * @return string|null Путь до сформированного архива
	 * @throws Exception
	 */
	function packingPersonIdentData($queryIdentData, $queryIdentDataParams, $fileParams, &$stat = array('PackageCount' => 0, 'PersonCount' => 0)) {
		$link = null;

		$dateConverter = function(&$value) {
			if ($value instanceof DateTime) $value = $value->format('Y-m-d');
		};

		$this->load->library('parser');
		$header_template = 'person_ident_package_ekb_header';
		$body_template = 'person_ident_package_ekb_body';
		$footer_template = 'person_ident_package_ekb_footer';

		$needAddPackagePos = false;
		$packagePosMap = function($item){return $item;};

		//Заполнения пакета записями для идентификации
		$limit = 1000;

		//echo getDebugSQL($queryIdentData, $queryIdentDataParams);exit;
		$resp = $this->queryResult(getCountSQLPH($queryIdentData), $queryIdentDataParams);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных для экспорта');
		}
		$count = $resp[0]['cnt'];
		$stat['PersonCount'] += $count;

		if ($count > 0) {
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse('export_xml/'.$header_template, array(), true, false, array(), true);
			$xml = toAnsi(str_replace('&', '&amp;', $xml), true);
			file_put_contents($fileParams['file_path'], $xml, FILE_APPEND);

			for ($start = 0; $start <= $count; $start += $limit) {
				$packagePosData = $this->queryResult(getLimitSQLPH($queryIdentData, $start, $limit), $queryIdentDataParams);
				if (!is_array($packagePosData)) {
					throw new Exception('Ошибка при получении данных для экспорта');
				}

				array_walk_recursive($packagePosData, $dateConverter);

				$xml = $this->parser->parse_ext('export_xml/'.$body_template, array('patient' => $packagePosData), true, false, array(), true);
				$xml = toAnsi(str_replace('&', '&amp;', $xml), true);
				file_put_contents($fileParams['file_path'], $xml, FILE_APPEND);
			}

			$xml = $this->parser->parse('export_xml/'.$footer_template, array(), true);
			$xml = toAnsi(str_replace('&', '&amp;', $xml), true);
			file_put_contents($fileParams['file_path'], $xml, FILE_APPEND);

			$zip = new ZipArchive();
			$zip->open($fileParams['package_path'], ZIPARCHIVE::CREATE);
			$zip->AddFile($fileParams['file_path'], $fileParams['file_name']);
			$zip->close();

			unlink($fileParams['file_path']);

			$stat['PackageCount']++;
			$link = $fileParams['package_path'];
		}

		return $link;
	}

	/**
	 * Удаление пакета для идентификации
	 */
	function deletePersonIdentPackage($data) {
		$params = array('PersonIdentPackage_id' => $data['PersonIdentPackage_id']);
		$response = array(array('success' => true));

		/**
		 * @param $dir
		 */
		function removeDirectory($dir) {
			if (file_exists($dir)) {
				if ($objs = glob($dir."/*")) {
					foreach($objs as $obj) {
						is_dir($obj) ? removeDirectory($obj) : unlink($obj);
					}
				}
				rmdir($dir);
			}
		}

		$this->beginTransaction();

		try {
			$package_dir = null;
			$package_sign = null;
			$package_name = $this->getFirstResultFromQuery("
				select PersonIdentPackage_Name as \"PersonIdentPackage_Name\"
				from v_PersonIdentPackage
				where PersonIdentPackage_id = :PersonIdentPackage_id
				limit 1
			", $params, true);
			if ($package_name === false) {
				throw new Exception('Ошибка при получении данных пакета');
			}

			if (preg_match('/^(.+)\/(\d+)\.SCD$/', $package_name, $match)) {
				$package_dir = $match[1];
				$package_sign = $match[2];
			}

			$query = "
				delete PersonIdentPackagePosError
				where PersonIdentPackagePos_id in (
					select PIPP.PersonIdentPackagePos_id
					from v_PersonIdentPackagePos PIPP 
					where PIPP.PersonIdentPackage_id = :PersonIdentPackage_id
				);
				
				/*delete PersonIdentPackagePos
				where PersonIdentPackage_id = :PersonIdentPackage_id*/
				update PersonIdentPackagePos
				set PersonIdentPackage_id = null,
					PersonIdentState_id = null
				where PersonIdentPackage_id = :PersonIdentPackage_id
				returning 0 as \"Error_Code\", '' as \"Error_Msg\"
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при удалении записей пакета');
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$query = "
				select	Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from	p_PersonIdentPackage_del(
					PersonIdentPackage_id = :PersonIdentPackage_id )
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при удалении пакета');
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			if (!empty($package_dir)) removeDirectory($package_dir);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Импорт файла-ответа от сервиса идентификации
	 * @param array $data
	 * @param array $file
	 * @param array $stat
	 * @return array
	 */
	function importPersonIdentPackageResponse($data, $file, &$stat = array('RecAll' => 0, 'RecIdent' => 0, 'RecOk' => 0, 'RecErr' => 0, 'Errors' => array())) {
		set_time_limit(0);

		$response = array('success' => true);

		$convertError = function($error) {
			$error = html_entity_decode($error);
			switch(true) {
				case (strpos($error, 'ERAR_BAD_ARCHIVE') !== false):
					return 'Импортируемый файл не является rar-архивом';
				case (strpos($error, 'cannot find file "ReplySCD.xml"') !== false):
					return 'В архиве не найден файл ReplySCD.xml';
			}
			return null;
		};

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$xml_file = 'ReplySCD.xml';
			$upload_path = IMPORTPATH_ROOT.'/person_ident_package/'.time().'/';

			//Получение имени файла и проверка его на соответствие формату
			$package_name = null;
			if (preg_match('/^(\d+)\.(ASC|RAR)/', mb_strtoupper($file['name']), $match)) {
				$package_name = $match[1];
			}
			if (empty($package_name)) {
				throw new Exception('Загружаемый файл не соответствует формату');
			}

			$this->textlog->add('package_name: ' . $package_name);
			//Поиск пакета по названию загружаемого файла
			$query = "
				select 
					PIP.PersonIdentPackage_id as \"PersonIdentPackage_id\",
					PIP.PersonIdentPackage_Name as \"PersonIdentPackage_Name\",
					PIP.PersonIdentPackage_begDate as \"PersonIdentPackage_begDate\",
					PIP.PersonIdentPackage_IsResponseRetrieved as \"PersonIdentPackage_IsResponseRetrieved\"
				from v_PersonIdentPackage PIP
				where PIP.PersonIdentPackage_Name ilike '%' || :package_name || '.SCD'
				order by PersonIdentPackage_insDT desc
				limit 1
			";
			$params = array('package_name' => $package_name);
			$package = $this->getFirstRowFromQuery($query, $params, true);
			if ($package === false) {
				throw new Exception('Ошибка при поиске пакета с данными для идентфикации');
			}
			if (empty($package)) {
				throw new Exception('Пакет с данными для идентификации не найден');
			}
			if ($package['PersonIdentPackage_IsResponseRetrieved'] == 2 && empty($_REQUEST['getDebug'])) {
				throw new Exception('Ответ уже был загружен ранее');
			}

			//Распаковка файла
			try {
				$rar = RarArchive::open($file['tmp_name']);
				$entry = $rar->getEntry('ReplySCD.xml');
			} catch (Exception $e) {
				$msg = $convertError($e->getMessage());
				throw $msg ? new Exception($msg) : $e;
			}

			if (!$entry || !$entry->extract($upload_path)) {
				$rar->close();
				throw new Exception('Ошибка при распаковке файла');
			}
			$rar->close();

			//Открытие xml-файла
			libxml_use_internal_errors(true);

			// Отсюда начинаем обработку по частям
			$xmlString = file_get_contents($upload_path.$xml_file);

			$checkString = mb_substr($xmlString, 0, 100);

			$header = mb_substr($checkString, 0, mb_strpos($checkString, '<answer>') + mb_strlen('<answer>'));
			$footer = '</answer>';

			unset($checkString);

			$xmlString = trim(mb_substr($xmlString, mb_strlen($header)));

			// 10 MB
			$chunkSize = 1024 * 1024 * 10;

			$tmpTableName = '#tmp'.time();
			$this->textlog->add('Временная таблица: ' . $tmpTableName);

			$cnt = 0;

			while ( !empty($xmlString) ) {
				// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
				if ( mb_strlen($xmlString) <= $chunkSize + mb_strlen($footer) ) {
					$xmlData = mb_substr($xmlString, 0, mb_strlen($xmlString) - mb_strlen($footer));
					$xmlString = '';
				}
				// или данные по $chunkSize МБ
				else {
					$xmlData = mb_substr($xmlString, 0, $chunkSize);
					$xmlString = mb_substr($xmlString, $chunkSize);

					if ( mb_strpos($xmlString, '</query>') !== false ) {
						$xmlData .= mb_substr($xmlString, 0, mb_strpos($xmlString, '</query>') + mb_strlen('</query>'));
						$xmlString = mb_substr($xmlString, mb_strpos($xmlString, '</query>') + mb_strlen('</query>'));

						if ( trim($xmlString) == $header ) {
							$xmlString = '';
						}
					}
				}

				$xml = new SimpleXMLElement($header . $xmlData . $footer);

				foreach ( libxml_get_errors() as $error ) {
					throw new Exception('Файл не является архивом реестра.');
				}

				libxml_clear_errors();

				if ( !property_exists($xml, 'query') ) {
					$xmlString = '';
					continue;
				}

				$cnt += $this->_saveResponseDataInTmpTable($xml, $tmpTableName);
			}

			$this->textlog->add('Данные загружены. Количество записей: ' . $cnt);

			$this->textlog->add('Запуск _identResponseData');
			$this->_identResponseData($tmpTableName, $package);
			$this->textlog->add('Запуск _processResponseData');
			$this->_processResponseData($tmpTableName, $data, $stat);

			if (!empty($package)) {
				//Проставление отметки у пакета о загрузке ответа
				$this->textlog->add('Проставление отметки у пакета о загрузке ответа');
				$resp = $this->savePersonIdentPackage(array_merge($package, array(
					'PersonIdentPackage_IsResponseRetrieved' => 2,
					'pmUser_id' => $data['pmUser_id']
				)));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}

			$this->textlog->add('Процедура импорта завершена');

			restore_exception_handler();
		} catch (Exception $e) {
			restore_exception_handler();
			$this->textlog->add($e->getMessage());
			return $this->createError($e->getCode(), $e->getMessage());
		}

		return array($response);
	}

	/**
	 * Сохранение данных из импортируемого файла во временную таблицу
	 * @param SimpleXMLElement $ResponseData
	 * @param string $tmpTableName
	 * @return int Количество обработанных записей
	 * @throws Exception
	 */
	protected function _saveResponseDataInTmpTable($ResponseData, $tmpTableName) {
		$response = 0;

		$configFields = array(
			'NREC' => 'int',
			'ACK' => 'int',
			'ALG' => 'varchar(3)',
			'ID' => 'int',
			'SNILS' => 'varchar(14)',
			'DR' => 'date',
			'W' => 'int',
			'DDEATH' => 'date',
			'SMO' => 'int',
			'VPOLIS' => 'int',
			'FPOLIS' => 'int',
			'NPOLIS' => 'varchar(16)',
			'DBEG' => 'date',
			'DEND' => 'date',
			'ERRCODE' => 'varchar(10)',
			'ERRTEXT' => 'varchar(200)'
		);

		$tableFieldFn = function($field, $type){return "$field $type";};
		$tableFieldsStr = implode(",\n", array_map($tableFieldFn, array_keys($configFields), $configFields));
		$createTmpTableQuery = "
			DROP TABLE IF EXISTS {$tmpTableName};
	
			create table {$tmpTableName} (
				{$tableFieldsStr},
				PersonIdentPackage_id bigint,
				PersonIdentPackagePos_id bigint,	--Идентификатор для сохранения ошибки
				Person_id bigint,
				PersonEvn_id bigint,
				Server_id bigint,
				processed int
			);
			select 0 as \"Error_Code\", '' as \"Error_Msg\"
		";

		$insertValuesFn = function($fields, $params){
			return array_map(function($field) use($params) {
				return (!empty($params[$field]) || $params[$field] === '0')?"'".str_replace("'", "''", $params[$field])."'":'null';
			}, $fields);
		};

		$insertQuery = function($tmpTableName, $fields, $values){
			return "
				insert into {$tmpTableName}
				({$fields})
				values
				{$values}
				returning 0 as \"Error_Code\", '' as \"Error_Msg\"
			";
		};

		$execInsertQuery = function($insertArr) use($tmpTableName, $configFields, $insertQuery) {
			$fields = implode(",", array_keys($configFields));
			$values = implode(",", $insertArr);
			$resp = $this->queryResult($insertQuery($tmpTableName, $fields, $values));
			if (!is_array($resp)) {
				return $this->createError('Ошибка при заполенении временной таблицы данными из файла');
			}
			return $resp;
		};

		$resp = $this->queryResult($createTmpTableQuery);
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		$insertArr = array();

		foreach ( $ResponseData->query as $query ) {
			if ( !property_exists($query, 'patient') ) {
				continue;
			}
			else if ( !property_exists($query, 'result') ) {
				continue;
			}

			$params = array(
				'NREC' => $query->patient->nrec->__toString(),
				'ACK' => $query->result->ack->__toString(),
				'ALG' => $query->result->alg->__toString(),
				'SMO' => $query->result->ins->smo->__toString(),
				'VPOLIS' => $query->result->ins->vpolis->__toString(),
				'FPOLIS' => $query->result->ins->fpolis->__toString(),
				'NPOLIS' => $query->result->ins->npolis->__toString(),
				//'DVISIT' => $query->result->ins->dvisit->__toString(),
				'DBEG' => $query->result->ins->dbeg->__toString(),
				'DEND' => $query->result->ins->dend->__toString(),
				//'REASON' => $query->result->ins->reason->__toString(),
				'DDEATH' => $query->result->ins->ddeath->__toString(),
				//'DOSTRDS' => $query->result->ins->dostrds->__toString(),
				'ID' => $query->result->ins->id->__toString(),
				'SNILS' => $query->result->ins->snils->__toString(),
				'DR' => $query->result->ins->dr->__toString(),
				'W' => $query->result->ins->w->__toString(),
				'ERRCODE' => '',
				'ERRTEXT' => '',
			);

			if ( property_exists($query->result, 'err') ) {
				$params['ERRCODE'] = $query->result->err->errcode->__toString();
				$params['ERRTEXT'] = $query->result->err->errtext->__toString();
			}

			$insertArr[] = "(".implode(",", $insertValuesFn(array_keys($configFields), $params)).")";
			if (count($insertArr) == 100) {
				$response += 100;
				$resp = $execInsertQuery($insertArr);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$insertArr = array();
			}
		}

		if (count($insertArr) > 0) {
			$response += count($insertArr);
			$resp = $execInsertQuery($insertArr);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}

		return $response;
	}

	/**
	 * Сохранение данных из импортируемого файла во временную таблицу
	 * @param DOMNode $ResponseData
	 * @return string Название временной таблицы
	 * @throws Exception
	 */
	function saveResponseDataInTmpTable($ResponseData) {
		/**
		 * Возращает список элементов по тегу
		 */
		function getListByTag($dom, $tag) {
			if (!($dom instanceof DOMNode)) {
				return null;
			}
			$list = $dom->getElementsByTagName($tag);
			if ($list->length == 0) {
				$list = $dom->getElementsByTagName(mb_strtolower($tag));
			}
			if ($list->length == 0) {
				$list = $dom->getElementsByTagName(mb_strtoupper($tag));
			}
			return $list;
		}

		/**
		 * Возращает олин элемент или его значение по тегу
		 */
		function getItemByTag($dom, $tag, $returnValue = true) {
			if ($list = getListByTag($dom, $tag)) {
				$result = $list->item(0);
				if ($returnValue && !empty($result)) {
					$result = $result->nodeValue;
				}
				return $result;
			}
			return null;
		}

		/**
		 * Возвращает данные из элемента dom по карте
		 */
		function parseDom($dom, $map) {
			$params = array();
			foreach ($map as $i => $v) {
				if (!is_array($v)) {
					$params[$v] = getItemByTag($dom, $v);
				} else {
					$params = array_merge($params, parseDom(getItemByTag($dom, $i, false), $map[$i]));
				}
			}
			return $params;
		}

		$map = array(
			'PATIENT' => array(
				'NREC'
			),
			'RESULT' => array(
				'ACK',
				'ALG',
				'INS' => array(
					'ID',
					'SNILS',
					'DR',
					'W',
					'DDEATH',
					//'DOSTRDS',
					'SMO',
					'VPOLIS',
					'FPOLIS',
					'NPOLIS',
					//'DVISIT',
					'DBEG',
					'DEND',
					//'REASON',
				),
				'ERR' => array(
					'ERRCODE',
					'ERRTEXT'
				)
			),
		);

		$configFields = array(
			'NREC' => 'int',
			'ACK' => 'int',
			'ALG' => 'varchar(3)',
			'ID' => 'int',
			'SNILS' => 'varchar(14)',
			'DR' => 'date',
			'W' => 'int',
			'DDEATH' => 'date',
			'SMO' => 'int',
			'VPOLIS' => 'int',
			'FPOLIS' => 'int',
			'NPOLIS' => 'varchar(16)',
			'DBEG' => 'date',
			'DEND' => 'date',
			'ERRCODE' => 'varchar(10)',
			'ERRTEXT' => 'varchar(200)'
		);

		$tmpTableName = '#tmp'.time();

		$tableFieldFn = function($field, $type){return "$field $type";};
		$tableFieldsStr = implode(",\n", array_map($tableFieldFn, array_keys($configFields), $configFields));
		$createTmpTableQuery = "
			DROP TABLE IF EXISTS {$tmpTableName};
	
			create table {$tmpTableName} (
				{$tableFieldsStr},
				PersonIdentPackage_id bigint,
				PersonIdentPackagePos_id bigint,	--Идентификатор для сохранения ошибки
				Person_id bigint,
				PersonEvn_id bigint,
				Server_id bigint,
				processed int
			);
			select 0 as \"Error_Code\", '' as \"Error_Msg\"
		";

		$insertValuesFn = function($fields, $params){
			return array_map(function($field) use($params) {
				return (!empty($params[$field]) || $params[$field] === '0')?"'".str_replace("'", "''", $params[$field])."'":'null';
			}, $fields);
		};

		$insertQuery = function($tmpTableName, $fields, $values){
			return "
				insert into {$tmpTableName}
				({$fields})
				values
				{$values}
				returning 0 as \"Error_Code\", '' as \"Error_Msg\"
			";
		};

		$execInsertQuery = function($insertArr) use($tmpTableName, $configFields, $insertQuery) {
			$fields = implode(",", array_keys($configFields));
			$values = implode(",", $insertArr);
			$resp = $this->queryResult($insertQuery($tmpTableName, $fields, $values));
			if (!is_array($resp)) {
				return $this->createError('Ошибка при заполенении временной таблицы данными из файла');
			}
			return $resp;
		};

		$resp = $this->queryResult($createTmpTableQuery);
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		$insertArr = array();
		$queryList = getListByTag($ResponseData, 'query');
		foreach($queryList as $query) {
			$patient_list = getListByTag($query, 'patient');
			$result_list = getListByTag($query, 'result');

			if (empty($patient_list)) {
				throw new Exception('Отсутствуют данные пациентов в файле');
			}
			if (empty($result_list)) {
				throw new Exception('Отсутствуют данные результатов в файле');
			}
			if ($patient_list->length != $result_list->length) {
				throw new Exception('В файле количество результатов не совпадает с количеством пациентов');
			}

			for($i = 0; $i < $patient_list->length; $i++) {
				$params = array_merge(
					parseDom($patient_list->item($i), $map['PATIENT']),
					parseDom($result_list->item($i), $map['RESULT'])
				);

				$insertArr[] = "(".implode(",", $insertValuesFn(array_keys($configFields), $params)).")";
				if (count($insertArr) == 100) {
					$resp = $execInsertQuery($insertArr);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$insertArr = array();
				}
			}
		}

		if (count($insertArr) > 0) {
			$resp = $execInsertQuery($insertArr);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}

		return $tmpTableName;
	}

	/**
	 * Идентификация записей, импортируемых из файла
	 * @param string $tmpTableName
	 * @param array $data
	 * @throws Exception
	 */
	protected function _identResponseData($tmpTableName, $data) {
		$params = array(
			'PersonIdentPackage_id' => $data['PersonIdentPackage_id'],
		);
		$query = "
			update {$tmpTableName}
			set PersonIdentPackage_id = pipp.PersonIdentPackage_id,
				PersonIdentPackagePos_id = pipp.PersonIdentPackagePos_id,
				Person_id = pe.Person_id,
				PersonEvn_id = pe.PersonEvn_id,
				Server_id = pe.Server_id
			from
				{$tmpTableName} pl
				inner join lateral (
					select
					pipp.Person_id,
					pipp.PersonIdentPackage_id,
					pipp.PersonIdentPackagePos_id
					from v_PersonIdentPackagePos pipp with(nolock)
					where pipp.PersonIdentPackagePos_NumRec = pl.NREC
					and pipp.PersonIdentPackage_id = :PersonIdentPackage_id
					order by pipp.PersonIdentPackagePos_identDT2 desc
					limit 1
				) pipp on true
				inner join lateral (
					select
					pe.Person_id,
					pe.PersonEvn_id,
					pe.Server_id
					from v_PersonEvn pe
					where pe.Person_id = pipp.Person_id
					and pe.PersonEvn_insDT <= pl.DBEG
					order by pe.PersonEvn_insDT desc
					limit 1
				) pe on true
			where
				pl.PersonEvn_id is null
			returning 0 as \"Error_Code\", '' as \"Error_Msg\"
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при поиске периодики');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}
	}

	/**
	 * Обработка записей, импортирумых из файла
	 * @param string $tmpTableName
	 * @param array $data
	 * @param array $stat
	 * @throws Exception
	 */
	protected function _processResponseData($tmpTableName, $data, &$stat = array('RecAll' => 0, 'RecIdent' => 0, 'RecOk' => 0 , 'RecErr' => 0, 'Errors' => array())) {
		$this->load->model('Person_model');


		$stat['RecAll'] = $this->getFirstResultFromQuery("select count(*) as cnt from {$tmpTableName}");

		$OMSSprTerr_id = $this->getFirstResultFromQuery("
			select OMSSprTerr_id as \"OMSSprTerr_id\" from v_OMSSprTerr where KLRgn_id = :KLRgn_id limit 1
		", array('KLRgn_id' => 66));

		$processDates = function($item) {
			return array_map(function($value) {
				if ($value instanceof DateTime) {
					$value = $value->format('Y-m-d');
					$value = ($value == '2099-12-31')?null:$value;
				}
				return $value;
			}, $item);
		};

		$isSame = function($item1, $item2, $fields){
			return array_reduce($fields, function($same, $field) use($item1, $item2){
				return $same && array_key_exists($field, $item1) && array_key_exists($field, $item2) && (
					$item1[$field] == $item2[$field] || empty($item1[$field]) && empty($item2[$field])
				);
			}, true);
		};

		$filterFields = function($item, $fields) {
			$arr = array();
			foreach($item as $key => $value) {
				if (in_array($key, $fields)) {
					$arr[$key] = $value;
				}
			}
			return $arr;
		};

		$personFields = array('BDZ_id', 'Person_deadDT', 'Person_IsInErz');
		$polisFields = array(
			'OMSSprTerr_id', 'PolisType_id', 'PolisFormType_id', 'OrgSMO_id',
			'Polis_Ser', 'Polis_Num', 'Federal_Num', 'Polis_begDate', 'Polis_endDate'
		);

		$setIdentStateQuery = "
			update PersonIdentPackagePos
			set
				PersonIdentState_id = :PersonIdentState_id,
				PersonIdentAlgorithm_id = :PersonIdentAlgorithm_id,
				PersonIdentPackagePos_Smo = :PersonIdentPackagePos_Smo,
				PolisType_id = :PolisType_id,
				PolisFormType_id = :PolisFormType_id,
				PersonIdentPackagePos_PolisNum = :PersonIdentPackagePos_PolisNum,
				PersonIdentPackagePos_recDate = :PersonIdentPackagePos_recDate,
				PersonIdentPackagePos_insurEndDate = :PersonIdentPackagePos_insurEndDate,
				PersonIdentPackagePos_Snils = :PersonIdentPackagePos_Snils,
				PersonIdentPackagePos_BirthDay = :PersonIdentPackagePos_BirthDay,
				PersonIdentPackagePos_Sex = :PersonIdentPackagePos_Sex
			where	Person_id = :Person_id
				and PersonIdentPackage_id = :PersonIdentPackage_id;

			select	Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from	p_Person_ident(
				Person_id = :Person_id,
				Person_identDT = dbo.tzGetDate(),
				PersonIdentState_id = :PersonIdentState_id,
				pmUser_id = :pmUser_id )
		";

		$queryPersonData = "
			select
			-- select
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				P.PersonIdentState_id as \"PersonIdentState_id\",
				Polis.OMSSprTerr_id as \"OMSSprTerr_id\",
				Polis.PolisType_id as \"PolisType_id\",
				Polis.PolisFormType_id as \"PolisFormType_id\",
				Polis.OrgSMO_id as \"OrgSMO_id\",
				null as \"Polis_Ser\",	--В файле нет серии
				Polis.Polis_Num as \"Polis_Num\",
				PS.Person_EdNum as \"Federal_Num\",
				Polis.Polis_begDate as \"Polis_begDate\",
				Polis.Polis_endDate as \"Polis_endDate\",
				P.BDZ_id as \"BDZ_id\",
				P.Person_deadDT as \"Person_deadDT\",
				P.Person_IsInErz as \"Person_IsInErz\"
			-- end select
			from
			-- from
				{$tmpTableName} pl
				left join Person P on P.Person_id = pl.Person_id
				left join v_Person_all PS on PS.PersonEvn_id = pl.PersonEvn_id and PS.Server_id = pl.Server_id
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
			-- end from
			where
			-- where
				pl.PersonEvn_id is not null
			-- end where
			order by
			-- order by
				PS.Person_id
			-- end order by
		";

		$queryResponseData = "
			select
			-- select
				pl.NREC as \"NREC\",
				pl.PersonIdentPackage_id as \"PersonIdentPackage_id\",
				pl.PersonIdentPackagePos_id as \"PersonIdentPackagePos_id\",
				PIPPE.PersonIdentPackagePosError_id as \"PersonIdentPackagePosError_id\",
				PIPPET.PersonIdentPackagePosErrorType_id as \"PersonIdentPackagePosErrorType_id\",
				PIPPET.PersonIdentPackagePosErrorType_Name as \"PersonIdentPackagePosErrorType_Name\",
				pl.Person_id as \"Person_id\",
				pl.PersonEvn_id as \"PersonEvn_id\",
				pl.Server_id as \"Server_id\",
				replace(replace(pl.SNILS, '-', ''), ' ', '') as \"Person_Snils\",
				to_char(pl.DR, 'yyyy-mm-dd') as \"Person_BirthDay\",
				pl.W as \"Sex_id\",
				pl.ACK as \"ACK\",
				case
					when pl.ACK = 0 then 1
					when pl.ACK = 2 and PIA.PersonIdentAlgorithm_Code in (
						'С01','С02','С03','C01','C02','C03'
					) and PT.PolisType_id is not null then 1
					else 2
				end as \"PersonIdentState_id\",
				PIA.PersonIdentAlgorithm_id as \"\",
				PIA.PersonIdentAlgorithm_Code as \"\",
				{$OMSSprTerr_id} as \"OMSSprTerr_id\",
				PT.PolisType_id as \"PolisType_id\",
				PFT.PolisFormType_id as \"PolisFormType_id\",
				SMO.OrgSMO_id as \"OrgSMO_id\",
				null as \"Polis_Ser\",	--В файле нет серии
				pl.NPOLIS as \"Polis_Num\",
				case when PT.PolisType_Code = 4 then pl.NPOLIS end as \"Federal_Num\",
				pl.DBEG as \"Polis_begDate\",
				pl.DEND as \"Polis_endDate\",
				pl.ID as \"BDZ_id\",
				pl.DDEATH as \"Person_deadDT\",
				2 as \"Person_IsInErz\"
			-- end select
			from
			-- from
				{$tmpTableName} pl
				left join v_PolisType PT on PT.PolisType_CodeF008 = pl.VPOLIS
				left join v_PolisFormType PFT on PFT.PolisFormType_Code = pl.FPOLIS
				left join lateral (
					select SMO.OrgSMO_id
					from v_OrgSMO SMO
					where SMO.Orgsmo_f002smocod ilike '66' || right('00' || cast(pl.SMO as varchar), 3)
					limit 1
				) SMO on true
				left join v_PersonIdentAlgorithm PIA on PIA.PersonIdentAlgorithm_Code = pl.ALG
				left join v_PersonIdentPackagePosErrorType PIPPET on PIPPET.PersonIdentPackagePosErrorType_Code = pl.ERRCODE
				left join v_PersonIdentPackagePosError PIPPE on PIPPE.PersonIdentPackagePos_id = pl.PersonIdentPackagePos_id
					and PIPPE.PersonIdentPackagePosErrorType_id = PIPPET.PersonIdentPackagePosErrorType_id
			-- end from
			where
			-- where
				pl.PersonEvn_id is not null
			-- end where
			order by
			-- order by
				pl.Person_id
			-- end order by
		";

		$start = 0;
		$limit = 1000;
		$count = $this->getFirstResultFromQuery(getCountSQLPH($queryResponseData));
		if ($count === false) {
			throw new Exception('Ошибка при запросе данных для обновления идентификации');
		}
		$stat['RecIdent'] = $count;

		$counter = 0;
		$debugLimit = !empty($_REQUEST['debugLimit'])?$_REQUEST['debugLimit']:null;

		while($start < $count) {
			$responseData = $this->queryResult(getLimitSQLPH($queryResponseData, $start, $limit));
			$personData = $this->queryResult(getLimitSQLPH($queryPersonData, $start, $limit));
			if (!is_array($responseData) || !is_array($personData)) {
				throw new Exception('Ошибка при запросе данных для обновления идентификации');
			}

			$personIData = array();
			foreach ($personData as $item) {
				$personIData[$item['PersonEvn_id']] = $item;
			}
			unset($personData);
			$b = &$responseData; //Ссылка нужна для уменьшения затрат памяти
			foreach($b as $key => &$item) {
				$this->beginTransaction();
				try {
					$item = $processDates($item);

					//Сохранение статуса идентификации
					if (!empty($item['PersonIdentPackage_id'])) {
						$params = array(
							'Person_id' => $item['Person_id'],
							'PersonIdentState_id' => $item['PersonIdentState_id'],
							'PersonIdentPackage_id' => $item['PersonIdentPackage_id'],
							'PersonIdentAlgorithm_id' => null,
							'PersonIdentPackagePos_Smo' => null,
							'PolisType_id' => null,
							'PolisFormType_id' => null,
							'PersonIdentPackagePos_PolisNum' => null,
							'PersonIdentPackagePos_recDate' => null,
							'PersonIdentPackagePos_insurEndDate' => null,
							'PersonIdentPackagePos_Snils' => null,
							'PersonIdentPackagePos_BirthDay' => null,
							'PersonIdentPackagePos_Sex' => null,
							'pmUser_id' => $data['pmUser_id']
						);
						if ($item['PersonIdentState_id'] == 2) {
							$params = array_merge($params, array(
								'PersonIdentAlgorithm_id' => !empty($item['PersonIdentAlgorithm_id'])?$item['PersonIdentAlgorithm_id']:null,
								'PersonIdentPackagePos_Smo' => !empty($item['OrgSMO_id'])?$item['OrgSMO_id']:null,
								'PolisType_id' => !empty($item['PolisType_id'])?$item['PolisType_id']:null,
								'PolisFormType_id' => !empty($item['PolisFormType_id'])?$item['PolisFormType_id']:null,
								'PersonIdentPackagePos_PolisNum' => !empty($item['Polis_Num'])?$item['Polis_Num']:null,
								'PersonIdentPackagePos_recDate' => !empty($item['Polis_begDate'])?$item['Polis_begDate']:null,
								'PersonIdentPackagePos_insurEndDate' => !empty($item['Polis_endDate'])?$item['Polis_endDate']:null,
								'PersonIdentPackagePos_Snils' => !empty($item['Person_Snils'])?$item['Person_Snils']:null,
								'PersonIdentPackagePos_BirthDay' => !empty($item['Person_BirthDay'])?$item['Person_BirthDay']:null,
								'PersonIdentPackagePos_Sex' => !empty($item['Sex_id'])?$item['Sex_id']:null,
							));
						}

						$resp = $this->queryResult($setIdentStateQuery, $params);
						if (!is_array($resp)) {
							throw new Exception('Ошибка при сохранении статуса идентификации');
						}
						if (!$this->isSuccessful($resp)) {
							throw new Exception($resp[0]['Error_Msg']);
						}
					}

					//В зависимости от статуса идентификация сохраняется ошибка либо данные человека
					if ($item['ACK'] == 2) {
						if (!empty($item['PersonIdentPackage_id']) && empty($item['PersonIdentPackagePosError_id'])) {
							//Сохранение ошибки, если такая ошибка ещё не сохранена
							$resp = $this->savePersonIdentPackagePosError(array(
								'PersonIdentPackagePosError_id' => null,
								'PersonIdentPackagePos_id' => $item['PersonIdentPackagePos_id'],
								'PersonIdentPackagePosErrorType_id' => $item['PersonIdentPackagePosErrorType_id'],
								'pmUser_id' => $data['pmUser_id'],
							));
							if (!$this->isSuccessful($resp)) {
								throw new Exception($resp[0]['Error_Msg']);
							}
						}
						$stat['RecErr']++;
						$stat['Errors'][] = "№{$item['NREC']}: {$item['PersonIdentPackagePosErrorType_Name']}";
					}

					if ($item['PersonIdentState_id'] == 1) {
						//Сохранение данных человека
						if (!isset($personIData[$item['PersonEvn_id']])) {
							throw new Exception("Не найдена периодика: {$item['PersonEvn_id']}");
						}

						//Сбор параметров для обновление периодики человека
						$params = array();
						$EvnTypeList = array();
						$NotEvnTypeList = array();

						if (!empty($item['PolisType_id']) && !$isSame($item, $personIData[$item['PersonEvn_id']], $polisFields)) {
							$EvnTypeList[] = 'Polis';
							$params = array_merge($params, $filterFields($item, $polisFields));
						}

						if (!$isSame($item, $personIData[$item['PersonEvn_id']], $personFields)) {
							$NotEvnTypeList[] = 'Person';
							$params = array_merge($params, $filterFields($item, $personFields));
						}

						//Обновление периодики человека
						if (count($EvnTypeList) > 0 || count($NotEvnTypeList) > 0) {
							$counter++;

							$params = array_merge($params, array(
								'EvnType' => implode('|', $EvnTypeList),
								'NotEvnType' => implode('|', $NotEvnTypeList),
								'pmUser_id' => $data['pmUser_id'],
								'Server_id' => $item['Server_id'],
								'session' => $data['session'],
								'Person_id' => $item['Person_id'],
								'PersonEvn_id' => $item['PersonEvn_id'],
								'PersonIdentState_id' => $item['PersonIdentState_id'],
							));

							$this->Person_model->exceptionOnValidation = true;    //Создает исключение при ошибке
							$resp = $this->Person_model->editPersonEvnAttributeNew($params);
							if (!empty($resp[0]['Error_Msg'])) {
								throw new Exception($resp[0]['Error_Msg']);
							}

							if ($debugLimit && $counter >= $debugLimit) {
								$start = $count;break;
							}
						}
						$stat['RecOk']++;
					}
				} catch (Exception $e) {
					$this->rollbackTransaction();
					$this->textlog->add("№{$item['NREC']}: ".$e->getMessage());
					$stat['Errors'][] = "№{$item['NREC']}: ".$e->getMessage();
					$stat['RecErr']++;
				}
				if (!empty($_REQUEST['getDebug'])) {
					$this->rollbackTransaction();
				} else {
					$this->commitTransaction();
				}
				unset($responseData[$key]);
			}

			$start += $limit;
		}
	}
}