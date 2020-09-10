<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Person_model - модель, для работы с людьми
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
require_once(APPPATH.'models/_pgsql/Person_model.php');

class Person_model4E extends Person_model {

	protected $loadMongoCacheLib = false;


	/**
	 * Возвращает список статусов из справочника
	 *
	 * @param type $data
	 * @return array or false
	 */
	public function loadSocStatusList( $data ){
		$sql = "
			SELECT
				SocStatus_id as \"SocStatus_id\",
				SocStatus_Code as \"SocStatus_Code\",
				SocStatus_Name as \"SocStatus_Name\",
				SocStatus_SysNick as \"SocStatus_SysNick\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				SocStatus_insDT as \"SocStatus_insDT\",
				SocStatus_updDT as \"SocStatus_updDT\",
				Region_id as \"Region_id\",
				SocStatus_AgeFrom as \"SocStatus_AgeFrom\",
				SocStatus_AgeTo as \"SocStatus_AgeTo\",
				SocStatus_begDT as \"SocStatus_begDT\",
				SocStatus_endDT as \"SocStatus_endDT\",
				SocStatusRPN_id as \"SocStatusRPN_id\"
			FROM v_SocStatus";
		$query = $this->db->query( $sql, array(
			'Lpu_id' => $data[ 'Lpu_id' ]
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getPersonByAddress($data){

		$where = array();
		$params = array();

		if ( !empty( $data[ 'Area_pid' ] ) ) {
			$where[] = "uaddr.KLRgn_id = :Area_pid";
			$params['Area_pid'] = $data[ 'Area_pid' ];
		}

		if ( !empty( $data[ 'Town_id' ] ) &&  $data[ 'Area_pid' ] != $data[ 'Town_id' ]) {
			$where[] = "(uaddr.KLCity_id = :Town_id or uaddr.KLTown_id = :Town_id)";
			$params['Town_id'] = $data[ 'Town_id' ];
		}

		if ( !empty( $data[ 'KLStreet_id' ] ) ) {
			$where[] = "uaddr.KLStreet_id = :KLStreet_id";
			$params['KLStreet_id'] = $data[ 'KLStreet_id' ];
		}else{
			$where[] = "(uaddr.KLStreet_id is null or uaddr.Address_Flat = '')";
		}

		if ( !empty( $data[ 'Address_House' ] ) ) {
			$where[] = "uaddr.Address_House = :Address_House";
			$params['Address_House'] = $data[ 'Address_House' ];
		}else{
			$where[] = "(uaddr.Address_House is null or uaddr.Address_Flat = '')";
		}

		if ( !empty( $data[ 'Address_Corpus' ] ) ) {
			$where[] = "uaddr.Address_Corpus = :Address_Corpus";
			$params['Address_Corpus'] = $data[ 'Address_Corpus' ];
		}else{
			$where[] = "(uaddr.Address_Corpus is null or uaddr.Address_Corpus = '')";
		}

		if ( !empty( $data[ 'Address_Flat' ] ) ) {
			$where[] = "uaddr.Address_Flat = :Address_Flat";
			$params['Address_Flat'] = $data[ 'Address_Flat' ];
		}else{
			$where[] = "(uaddr.Address_Flat is null or uaddr.Address_Flat = '')";
		}
		$this->load->model("Options_model", "opmodel");
		$daysByDeath = 0;
		$daysByDeath = $this->opmodel->getOptionsGlobals($data,'limit_days_after_death_to_create_call');
		if ( !empty( $data[ 'isNotDead' ] ) && $daysByDeath >= 0 ) {
			$where[] = "((ps.Person_deadDT <= dbo.tzgetdate() AND DATEDIFF('day', cast( ps.Person_deadDT as timestamp), dbo.tzgetdate()) <= ".$daysByDeath." ) OR ps.Person_deadDT is null)";
		}

		$query="
			SELECT
				uaddr.Address_id as \"Address_id\",
				ps.Person_id as \"Person_id\",
				uaddr.Address_Address as \"Address_Full\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				ps.Person_edNum as \"Polis_EdNum\",
				dbo.Age(ps.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				to_char(cast(ps.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"PersonBirthDay_BirthDay\",
				to_char(cast(ps.Person_deadDT as timestamp), 'dd.mm.yyyy') as \"Person_deadDT\",
				ps.Sex_id as \"Sex_id\",
				CASE WHEN coalesce(OC.OftenCallers_id, 0) = 0 THEN 1 ELSE 2 END as \"Person_isOftenCaller\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				lpu.Lpu_id as \"CmpLpu_id\",
				countCallCards as \"countCallCards\"
			FROM v_Personstate ps
				LEFT JOIN v_Address uaddr on ps.PAddress_id = uaddr.Address_id
				left join lateral(
					select
						OftenCallers_id
					from v_OftenCallers ofc
					where ofc.Person_id = ps.Person_id
					limit 1
				) OC on true
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard pc
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
						and (1=1)
					order by
						PersonCard_begDate desc
					limit 1
				) as pcard on true
				LEFT JOIN v_Lpu lpu on pcard.Lpu_id=lpu.Lpu_id
				left join lateral(
					--колво закрытых карт на персоне
                    SELECT
                    	COUNT(*) as countCallCards 
					FROM v_CmpCallCard as CCC
						left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
					WHERE CCC.Person_id =ps.Person_id
                ) as countCallCards on true
			".ImplodeWherePH( $where )."				
		";

		//var_dump(getDebugSQL($query, $params)); exit;

		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			$response = $res->result('array');
			return $response;
		}
		else
			return false;
	}




	/**
	 * Получение истории вызовов по указанному пациенту
	 *
	 * @param int $Person_id
	 * @return array
	 */
	public function getPersonCallsHistory($data){
		//для правильного отображения диагноза. Только для Уфы из r2.v_CmpCloseCard
		$scheme = ($data['session']['region']['nick'] == 'ufa') ? 'r2.' : '';

		$sql = "
			SELECT
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), 'dd Mon YYYY HH24:MI:SS') as \"AcceptTime\",
				CCC.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(cast(ps.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
                RTRIM(case when CR.CmpReason_id is not null
                	then CR.CmpReason_Code || '. '
                	else ''
                end || coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				RTRIM(coalesce(CLD.Diag_Code, '') || ' ' || coalesce(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
				case when City.KLCity_Name is not null
					then 'г. ' || City.KLCity_Name
					else ''
				end ||
				case when Town.KLTown_FullName is not null
					then case when City.KLCity_Name is not null
						then ', '
						else ''
					end || coalesce(LOWER(Town.KLSocr_Nick)|| '. ','') || Town.KLTown_Name
					else '' 
				end ||
				case when Street.KLStreet_FullName is not null
					then ', ' || LOWER(socrStreet.KLSocr_Nick) || '. ' || Street.KLStreet_Name
					else ''
				end ||
				case when CCC.CmpCallCard_Dom is not null
					then ', д.' || CCC.CmpCallCard_Dom
					else ''
				end ||
				case when CCC.CmpCallCard_Korp is not null
					then ', к.' || CCC.CmpCallCard_Korp
					else ''
				end ||
				case when CCC.CmpCallCard_Kvar is not null
					then ', кв.' || CCC.CmpCallCard_Kvar
					else ''
				end ||
				case when CCC.CmpCallCard_Room is not null
					then ', ком. ' || CCC.CmpCallCard_Room
					else ''
				end ||
					case when UAD.UnformalizedAddressDirectory_Name is not null
					then ', Место: ' || UAD.UnformalizedAddressDirectory_Name
				else ''
				end as \"Address_Name\",
				case when (countCallCards > 0)
					then 2
					else 1
				end as \"Hospitalized\"
			FROM v_CmpCallCard as CCC
				left join ".$scheme."v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Personstate PS on PS.Person_id = CCC.Person_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id			
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
                left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join lateral(
					SELECT
						COUNT(*) as countCallCards 
					FROM v_CmpCloseCardRel cccRel
					left join v_CmpCloseCardCombo cccCombo on cccCombo.CmpCloseCardCombo_id = cccRel.CmpCloseCardCombo_id
					WHERE cccRel.CmpCloseCard_id = CLC.CmpCloseCard_id
					and cccCombo.CmpCloseCardCombo_Code = 241
				) as countCallCards on true
			WHERE CCC.Person_id = :Person_id
				AND DATEDIFF('day', cast( CCC.CmpCallCard_prmDT as timestamp), dbo.tzgetdate()) <= 30
		";
		return $this->db->query($sql, array(
			'Person_id' => $data['Person_id']
		))->result_array();
	}

	/**
	 * Получение истории вызовов по указанному пациенту
	 *
	 * @param int $Person_id
	 * @return array
	 */
	public function getCountCallByPersonId($data){

		$sql = "
			SELECT
				COUNT(CCC.CmpCallCard_id) as \"CountCard\"
			FROM v_CmpCallCard as CCC
				left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
			WHERE
				CCC.Person_id = :Person_id
				AND DATEDIFF('day', cast( CCC.CmpCallCard_prmDT as timestamp), dbo.tzgetdate()) <= 30

		";
		return $this->db->query($sql, array(
			'Person_id' => $data['Person_id']
		))->result_array();
	}
}
