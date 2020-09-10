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
require_once(APPPATH.'models/Person_model.php');

class Person_model4E extends Person_model {

	protected $loadMongoCacheLib = false;
	

	/**
	 * Возвращает список статусов из справочника
	 *
	 * @param type $data
	 * @return array or false
	 */
	public function loadSocStatusList( $data ){
		$sql = "SELECT * FROM v_SocStatus with(nolock)";
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
			$where[] = "((ps.Person_deadDT <= getdate() AND DATEDIFF(day,cast( ps.Person_deadDT as datetime), getdate()) <= ".$daysByDeath." ) OR ps.Person_deadDT is null)";
		}

		$query="
			SELECT
				uaddr.Address_id,
				ps.Person_id,
				uaddr.Address_Address as Address_Full,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName ,
				ps.Polis_Ser,
				ps.Polis_Num,
				ps.Person_edNum as Polis_EdNum,
				dbo.Age(ps.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				convert(varchar,cast(ps.Person_BirthDay as datetime),104) as PersonBirthDay_BirthDay,
				convert(varchar,cast(ps.Person_deadDT as datetime),104) as Person_deadDT,
				ps.Sex_id,
				CASE WHEN ISNULL(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as Person_isOftenCaller,
				lpu.Lpu_Nick as Lpu_Nick,
				lpu.Lpu_id as CmpLpu_id,
				countCallCards
				
			FROM v_Personstate ps with (nolock)
				LEFT JOIN v_Address uaddr with (nolock) on ps.PAddress_id = uaddr.Address_id
				outer apply (
					select top 1 OftenCallers_id
					from v_OftenCallers ofc WITH (nolock)
					where ofc.Person_id = ps.Person_id
				) OC
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard pc with (nolock)
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
						and (1=1)
					order by PersonCard_begDate desc
				) as pcard
				LEFT JOIN v_Lpu lpu with (nolock) on pcard.Lpu_id=lpu.Lpu_id
				outer APPLY(
						--колво закрытых карт на персоне
                    	SELECT COUNT(*) as countCallCards 
						FROM v_CmpCallCard as CCC with(nolock)
						left join v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
						WHERE
							CCC.Person_id =ps.Person_id
                    ) as countCallCards
			
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
				CLC.CmpCloseCard_id,
				--CLC.AcceptTime,
				convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as AcceptTime,
				CCC.Person_id,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar,cast(ps.Person_BirthDay as datetime),104) as Person_BirthDay,
                RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name,
				RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) as CmpDiag_Name,
				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end +
				  case when Town.KLTown_FullName is not null then
					  case when City.KLCity_Name is not null then ', ' else '' end 
					   +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else '' 
				  end +
				  case when Street.KLStreet_FullName is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				  case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				  case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				  case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				  case when CCC.CmpCallCard_Room is not null then ', ком. '+CCC.CmpCallCard_Room else '' end +
				  case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Address_Name,
				case when (countCallCards > 0) then 2 else 1 end as Hospitalized
			FROM v_CmpCallCard as CCC with(nolock)
				left join ".$scheme."v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Personstate PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id			
				left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City with(nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town with(nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street with(nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
                left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				outer APPLY(
					SELECT COUNT(*) as countCallCards 
					FROM v_CmpCloseCardRel cccRel with(nolock)
					left join v_CmpCloseCardCombo cccCombo with (nolock) on cccCombo.CmpCloseCardCombo_id = cccRel.CmpCloseCardCombo_id
					WHERE cccRel.CmpCloseCard_id = CLC.CmpCloseCard_id
					and cccCombo.CmpCloseCardCombo_Code = 241
				) as countCallCards

			WHERE CCC.Person_id = :Person_id
				AND DATEDIFF(day,cast( CCC.CmpCallCard_prmDT as datetime), getdate()) <= 30

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
				COUNT(CCC.CmpCallCard_id) as CountCard
			FROM v_CmpCallCard as CCC with(nolock)
				left join v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id

			WHERE
				CCC.Person_id = :Person_id
				AND DATEDIFF(day,cast( CCC.CmpCallCard_prmDT as datetime), getdate()) <= 30

		";
		return $this->db->query($sql, array(
			'Person_id' => $data['Person_id']
		))->result_array();
	}
}
