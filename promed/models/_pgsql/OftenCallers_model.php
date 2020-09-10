<?php
class OftenCallers_model extends SwPgModel {
	/**
	 * OftenCallers_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getOftenCallers($data) {
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		$query = "
			SELECT
				OC.OftenCallers_id as \"OftenCallers_id\"
				,OC.Person_id as \"Person_id\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, CCC.Person_FirName, '') || ' ' || COALESCE(PS.Person_Secname, CCC.Person_SecName, '') as \"Person_Fio\"
				,CR.CmpReason_Name as \"CmpReason_Name\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'dd m yyyy hh:mm:ss:mmm') as \"CmpCallCard_prmDate\"
				,RGN.KLRgn_FullName as \"KLRgn_FullName\",
				case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end ||
				case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end ||
				case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end ||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end as \"Adress_Name\"
				,case when ABS(DATEDIFF('day',dbo.tzGetDate(),CCC.CmpCallCard_prmDT))>cast(DS.OftenCaller_FreeDays as int) OR COALESCE(CCC.CmpCallCard_prmDT,null) = null  then 2 else 1 end as \"onDelete\"
				
			FROM
				v_OftenCallers OC
				LEFT JOIN LATERAL(
					select 
						v_CmpCallCard.CmpCallCard_insDT as CmpCallCard_insDT
						,v_CmpCallCard.CmpReason_Id as CmpReason_Id
						,v_CmpCallCard.Person_SurName as Person_SurName
						,v_CmpCallCard.Person_FirName as Person_FirName
						,v_CmpCallCard.Person_Secname as Person_Secname
						,v_CmpCallCard.KLRgn_id as KLRgn_id
						,v_CmpCallCard.KLSubRgn_id as KLSubRgn_id
						,v_CmpCallCard.KLCity_id as KLCity_id
						,v_CmpCallCard.KLTown_id as KLTown_id
						,v_CmpCallCard.KLStreet_id as KLStreet_id
						,v_CmpCallCard.CmpCallCard_Dom as CmpCallCard_Dom
						,v_CmpCallCard.CmpCallCard_Kvar as CmpCallCard_Kvar
						,v_CmpCallCard.CmpCallCard_prmDT as CmpCallCard_prmDT
					from
						v_CmpCallCard
					where
						v_CmpCallCard.Person_id = OC.Person_id
					order by CmpCallCard_insDT desc
					limit 1
				) as CCC ON TRUE
				LEFT JOIN LATERAL(
					select
						COALESCE(v_DataStorage.DataStorage_Value::int,365) as OftenCaller_FreeDays
					from
						v_DataStorage
					where
						v_DataStorage.DataStorage_Name = 'OftenCallers_FreeDays'
						and v_DataStorage.Lpu_id = :Lpu_id
					limit 1
				) as DS ON TRUE
				left join v_PersonState PS on PS.Person_id = OC.Person_id
				
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id

				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			WHERE
				OC.Lpu_id = :Lpu_id
			order by \"onDelete\" DESC, CmpCallCard_prmDT ASC
			";
		
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		
		$val = $result->result('array');
		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}

	/**
	 * @param $data
	 * @return array|mixed
	 */
	function deleteFromOftenCallers($data) {
		foreach ($data['OftenCallers_ids'] as $OftenCallers_id ) {
			$queryParams['OftenCallers_id'] = $OftenCallers_id;
			$queryParams['pmUser_id'] = $data['pmUser_id'];
			$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_OftenCallers_del (
				OftenCallers_id := :OftenCallers_id,
				pmUser_id := :pmUser_id
				)
				";
			
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление из регистра часто обращающихся)'));
			}
		}
		$val = $result->result('array');

		return $val;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function checkOftenCallers($data) {
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];


		$query = "
			CREATE OR REPLACE function tmp.PersonId(
				inout Lpu_id bigint,
				inout Person_id bigint,
				pmUser_id bigint=null,
				inout Error_Code text =null,
				inout Error_Message text=null
			)
			LANGUAGE 'plpgsql'

			AS $$
			DECLARE
			InsertInRegister bigint;
			OftenCallers_CallTimes bigint;
			OftenCallers_SearchDays bigint;
			SQLstring varchar(500);
			ParamDefinition varchar(500);
			Res bigint;

			BEGIN

			SELECT CAST(COALESCE(DS.DataStorage_Value,3) AS INT) into OftenCallers_CallTimes
			from v_DataStorage DS
			where DS.DataStorage_Name = 'OftenCallers_CallTimes' AND DS.Lpu_id = PersonId.Lpu_id;

			SELECT CAST(COALESCE(DS.DataStorage_Value,30) AS INT) into OftenCallers_SearchDays
			from v_DataStorage DS
			where DS.DataStorage_Name = 'OftenCallers_SearchDays' AND DS.Lpu_id = PersonId.Lpu_id;

			SELECT CASE WHEN (COUNT(CCC.CmpCallCard_id) >= OftenCallers_CallTimes) THEN 2 ELSE 1 end
			into InsertInRegister
			from v_CmpCallCard CCC
			where DATEDIFF('day',CCC.CmpCallCard_prmDT,dbo.tzGetDate()) <= OftenCallers_SearchDays AND CCC.Person_id = PersonId.Person_id;

			if InsertInRegister=2 then
			select OftenCallers_id,Error_Code , Error_Message
			into Res,Error_Code , Error_Message
			from p_OftenCallers_ins ( OftenCallers_id := 0, Lpu_id := Lpu_id,Person_id := Person_id,pmUser_id := pmUser_id);
			end if;
			exception
			when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;

			END;
			$$;
			
			select
			0 as \"Person_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
			from
			tmp.PersonId(Lpu_id:=:Lpu_id,Person_id:=:Person_id,pmUser_id:=:pmUser_id);
		";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query,$queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
?>
