<?php
class OftenCallers_model extends CI_Model {
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

		$queryParams['Lpu_id'] = !empty($data['Lpu_id']) ? $data['Lpu_id'] : $data['session']['lpu_id'];

		$query = "
			SELECT
				OC.OftenCallers_id
				,OC.Person_id
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, CCC.Person_FirName, '') + ' ' + COALESCE(PS.Person_Secname, CCC.Person_SecName, '') as Person_Fio
				,CR.CmpReason_Name
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,RGN.KLRgn_FullName + 
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end as Adress_Name
				,case when ABS(DATEDIFF(day,dbo.tzGetDate(),CCC.CmpCallCard_prmDT))>cast(DS.OftenCaller_FreeDays as int) OR ISNULL(CCC.CmpCallCard_prmDT,0) = 0  then 2 else 1 end as onDelete
				,lpu.Lpu_Nick
				
			FROM
				v_OftenCallers OC with (nolock)
				outer apply(
					select top 1
						v_CmpCallCard.CmpCallCard_insDT
						,v_CmpCallCard.CmpReason_Id
						,v_CmpCallCard.Person_SurName
						,v_CmpCallCard.Person_FirName
						,v_CmpCallCard.Person_Secname
						,v_CmpCallCard.KLRgn_id
						,v_CmpCallCard.KLSubRgn_id
						,v_CmpCallCard.KLCity_id
						,v_CmpCallCard.KLTown_id
						,v_CmpCallCard.KLStreet_id
						,v_CmpCallCard.CmpCallCard_Dom
						,v_CmpCallCard.CmpCallCard_Kvar
						,v_CmpCallCard.CmpCallCard_prmDT
					from
						v_CmpCallCard with (nolock)
					where
						v_CmpCallCard.Person_id = OC.Person_id
					order by CmpCallCard_insDT desc
				) as CCC
				outer apply(
					select top 1
						ISNULL(v_DataStorage.DataStorage_Value,365) as OftenCaller_FreeDays
					from
						v_DataStorage with (nolock)
					where
						v_DataStorage.DataStorage_Name = 'OftenCallers_FreeDays'
						and v_DataStorage.Lpu_id = :Lpu_id
				) as DS
				left join v_PersonState PS with (nolock) on PS.Person_id = OC.Person_id
				
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id

				left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City with(nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town with(nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street with(nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_Lpu lpu with(nolock) on lpu.Lpu_id = OC.Lpu_id
			WHERE
				OC.Lpu_id = :Lpu_id
			order by onDelete DESC, CmpCallCard_prmDT ASC
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_OftenCallers_del
				@OftenCallers_id = :OftenCallers_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			begin
			set nocount on;

			declare
				@InsertInRegister bigint,
                @OftenCallers_CallTimes bigint,
                @OftenCallers_SearchDays bigint,
				@SQLstring nvarchar(500),
				@ParamDefinition nvarchar(500),
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Lpu_id bigint,
				@Person_id bigint;

			SET @Lpu_id = :Lpu_id;
			SET @Person_id = :Person_id;

			SET @SQLString = N'SELECT @OftenCallers_CallTimes = CAST(ISNULL(DS.DataStorage_Value,3) AS INT)
			FROM
					v_DataStorage DS WITH (nolock)
				WHERE
					DS.DataStorage_Name = ''OftenCallers_CallTimes'' AND DS.Lpu_id = @Lpu_id

					SELECT @OftenCallers_SearchDays = CAST(ISNULL(DS.DataStorage_Value,30) AS INT)
				FROM
					v_DataStorage DS WITH (nolock)
				WHERE
					DS.DataStorage_Name = ''OftenCallers_SearchDays'' AND DS.Lpu_id = @Lpu_id
			';
			SET @ParamDefinition = N'@OftenCallers_CallTimes bigint OUTPUT,@OftenCallers_SearchDays bigint OUTPUT, @InsertInRegister bigint OUTPUT, @Lpu_id bigint';

			exec sp_executesql @SQLString, @ParamDefinition, @OftenCallers_CallTimes = @OftenCallers_CallTimes OUTPUT,
				@OftenCallers_SearchDays = @OftenCallers_SearchDays OUTPUT, @InsertInRegister = @InsertInRegister OUTPUT,@Lpu_id = @Lpu_id

			SET @SQLString = N'
			SELECT
				@InsertInRegister = CASE WHEN (COUNT(CCC.CmpCallCard_id) >= @OftenCallers_CallTimes) THEN 2 ELSE 1 END
			FROM
				v_CmpCallCard CCC WITH (NOLOCK)
			WHERE
				DATEDIFF(day,CCC.CmpCallCard_prmDT,dbo.tzGetDate()) <= @OftenCallers_SearchDays AND CCC.Person_id = @Person_id';
			SET @ParamDefinition = N'@OftenCallers_CallTimes bigint,@OftenCallers_SearchDays bigint, @InsertInRegister bigint OUTPUT, @Person_id bigint';

            exec sp_executesql @SQLString, @ParamDefinition, @OftenCallers_CallTimes = @OftenCallers_CallTimes, @OftenCallers_SearchDays = @OftenCallers_SearchDays,
				@InsertInRegister = @InsertInRegister OUTPUT,@Person_id = @Person_id

			IF @InsertInRegister=2
				BEGIN
					set @Res = 0;

					exec p_OftenCallers_ins
						@OftenCallers_id = @Res output,
						@Lpu_id = @Lpu_id,
						@Person_id = @Person_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as OftenCallers_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				END

			set nocount off;

			select @Res as Person_id
			end
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
