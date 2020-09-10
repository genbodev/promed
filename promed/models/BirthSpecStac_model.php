<?php defined('BASEPATH') or die ('No direct script access allowed');
/**одель для работы с спецификой "Беременность и роды" в КВС
 *
 * @author: gabdushev
 * @copyright  
 */
 
class BirthSpecStac_model extends swModel{
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->isAllowControlTransaction = true;
	}

	/**
	 * Сохранение данных специфики "Беременность и роды"
	 */
	function save($data){
		$trans_good = true;
		$trans_result = array();

		if ( (!isset($data['BirthSpecStac_id'])) || ($data['BirthSpecStac_id'] <= 0) ) {
			$procedure = 'p_BirthSpecStac_ins';
			if(isset($data['EvnSection_id'])){
				$query ="select top 1 BirthSpecStac_id
from v_BirthSpecStac BSS with (nolock)
inner join v_EvnSection EvnSection with (nolock) on EvnSection.EvnSection_id=BSS.EvnSection_id
where EvnSection.EvnSection_id =:EvnSection_id";
				$result = $this->db->query($query, array('EvnSection_id'=>$data['EvnSection_id']));
				$response = $result->result('array');
				if(count($response)>0){
					$data['BirthSpecStac_id']=$response[0]['BirthSpecStac_id'];
					$procedure = 'p_BirthSpecStac_upd';
				}
			}
		}
		else {
			$procedure = 'p_BirthSpecStac_upd';
		}

		//todo: надо сделать проверку на уникальность по EvnSection_id
		//todo: при характере родов=аборт сделать проверку на заполненность обязательных при аборте полей
		//todo: надо сделать проверку на число в кровопотерях, чтобы оно не было более 9999,99

		if ($trans_good === true) {
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :BirthSpecStac_id;
				exec " . $procedure . "
					@BirthSpecStac_id = @Res output, -- bigint
					@EvnSection_id = :EvnSection_id, -- bigint
					@BirthSpecStac_CountPregnancy = :BirthSpecStac_CountPregnancy, -- int
					@BirthSpecStac_CountBirth = :BirthSpecStac_CountBirth, -- int
					@BirthSpecStac_CountChild = :BirthSpecStac_CountChild, -- int
					@BirthSpecStac_CountChildAlive = :BirthSpecStac_CountChildAlive, -- int
					@BirthResult_id = :BirthResult_id, -- bigint
					@BirthPlace_id = :BirthPlace_id, -- bigint
					@BirthSpecStac_OutcomPeriod = :BirthSpecStac_OutcomPeriod, -- int
					@BirthSpecStac_OutcomDT = :BirthSpecStac_OutcomDT, -- datetime
					@BirthSpec_id = :BirthSpec_id, -- bigint
					@BirthSpecStac_IsHIVtest = :BirthSpecStac_IsHIVtest, -- bigint
					@BirthSpecStac_IsHIV = :BirthSpecStac_IsHIV, -- bigint
					@AbortType_id = :AbortType_id, -- bigint
					@BirthSpecStac_IsMedicalAbort = :BirthSpecStac_IsMedicalAbort, -- bigint
					@BirthSpecStac_BloodLoss = :BirthSpecStac_BloodLoss, -- numeric
    				@PregnancySpec_id = :PregnancySpec_id, -- bigint
					@pmUser_id = :pmUser_id, -- bigint
					@Error_Code = @ErrCode output, -- int
					@Error_Message = @ErrMessage output  -- varchar(4000)
    				select @Res as BirthSpecStac_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			//параметры, которые надо передать в запрос
			$paramset = array(
				'BirthSpecStac_id',
				'EvnSection_id',
				'BirthSpecStac_CountPregnancy',
				'BirthSpecStac_CountBirth',
				'BirthSpecStac_CountChild',
				'BirthSpecStac_CountChildAlive',
				'BirthResult_id',
				'BirthPlace_id',
				'BirthSpecStac_OutcomPeriod',
				'BirthSpecStac_OutcomDT',
				'BirthSpec_id',
				'BirthSpecStac_IsHIVtest',
				'BirthSpecStac_IsHIV',
				'AbortType_id',
				'BirthSpecStac_IsMedicalAbort',
				'BirthSpecStac_BloodLoss',
				'PregnancySpec_id',
				'pmUser_id'
			);
			//формируем массив параметров
			$queryParams = array();
			foreach ($paramset as $p)  {
				if (isset($data[$p])) {
					$queryParams[$p] = $data[$p];
				} else {
					$queryParams[$p] = null;
				}
			}

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				//todo: ошибка при сохранении
			}
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				//ошибка при сохранении, база ничего не сказала
			}
			else {
				//если была ошибка при сохранении, база вернула сообщение об ошибке в $response[0]['Error_Msg']
				$trans_result = $response;
			}
		}
		return $trans_result;
	}//function save()

	/**
	 * Загрузка данных формы специфики "Беременность и роды"
	 */
	function load($evnSectionId){
		$result = false;
		$query = "SELECT TOP 1
        BirthSpecStac_id,
        EvnSection_id,
        BirthSpecStac_CountPregnancy,
        BirthSpecStac_CountBirth,
        BirthSpecStac_CountChild,
        BirthSpecStac_CountChildAlive,
        BirthResult_id,
        BirthPlace_id,
        BirthSpecStac_OutcomPeriod,
        convert(varchar(19),BirthSpecStac_OutcomDT,120) as BirthSpecStac_OutcomDT,
        BirthSpec_id,
        BirthSpecStac_IsHIVtest,
        BirthSpecStac_IsHIV,
        AbortType_id,
        BirthSpecStac_IsMedicalAbort,
        BirthSpecStac_BloodLoss,
        PregnancySpec_id,
        pmUser_insID,
        pmUser_updID,
        BirthSpecStac_insDT,
        BirthSpecStac_updDT
FROM    dbo.v_BirthSpecStac WITH ( NOLOCK )
WHERE   EvnSection_id = :EvnSection_id
ORDER BY BirthSpecStac_id DESC";

		$result = $this->db->query($query, array(
			'EvnSection_id' => $evnSectionId
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение данных по мертворожденному ребенку
	 */
	function saveChildDeath($childDeathData){
		$procedure = "p_ChildDeath_ins";

		if ( $childDeathData['ChildDeath_id'] > 0 ) {
			$procedure = "p_ChildDeath_upd";
		} else {
			$childDeathData['ChildDeath_id'] = null;
		}

		if ( $childDeathData['BirthSvid_id'] <= 0 ) {
			$childDeathData['BirthSvid_id'] = null;
		}
		if(isset($childDeathData['PntDeathSvid_id'])){
			if ( $childDeathData['PntDeathSvid_id'] <= 0 ) {
				$childDeathData['PntDeathSvid_id'] = null;
			}
		}else{
			$childDeathData['PntDeathSvid_id'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :ChildDeath_id;

			EXEC dbo.$procedure
				@ChildDeath_id     = @Res              output, -- bigint
				@BirthSpecStac_id  = :BirthSpecStac_id , -- bigint
				@MedStaffFact_id   = :MedStaffFact_id  , -- bigint
				@Diag_id           = :Diag_id          , -- bigint
				@Sex_id            = :Sex_id           , -- bigint
				@ChildDeath_Weight = :ChildDeath_Weight, -- int
				@ChildDeath_Height = :ChildDeath_Height, -- int
				@PntDeathTime_id   = :PntDeathTime_id  , -- bigint
				@ChildTermType_id  = :ChildTermType_id , -- bigint
				@ChildDeath_Count  = :ChildDeath_Count , -- int
				@BirthSvid_id      = :BirthSvid_id     , -- bigint
				@PntDeathSvid_id   = :PntDeathSvid_id  , -- bigint
				@Okei_wid          = :Okei_wid, -- int
				@pmUser_id         = :pmUser_id        , -- bigint
				@Error_Code        = @ErrCode          output, -- int
				@Error_Message     = @ErrMessage       output -- varchar(4000)

			select @Res as ChildDeath_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if (!isset($childDeathData['BirthSvid_id'])||(''===$childDeathData['BirthSvid_id'])){
			$childDeathData['BirthSvid_id'] = null;
		}
		if (!isset($childDeathData['PntDeathSvid_id'])||(''===$childDeathData['PntDeathSvid_id'])){
			$childDeathData['PntDeathSvid_id'] = null;
		}

		$queryParams = array(
			'ChildDeath_id'     => $childDeathData['ChildDeath_id'    ],
			'BirthSpecStac_id'  => $childDeathData['BirthSpecStac_id' ],
			'MedStaffFact_id'   => $childDeathData['MedStaffFact_id'  ],
			'Diag_id'           => $childDeathData['Diag_id'          ],
			'Sex_id'            => $childDeathData['Sex_id'           ],
			'ChildDeath_Weight' => $childDeathData['ChildDeath_Weight'],
			'ChildDeath_Height' => $childDeathData['ChildDeath_Height'],
			'PntDeathTime_id'   => $childDeathData['PntDeathTime_id'  ],
			'ChildTermType_id'  => $childDeathData['ChildTermType_id' ],
			'ChildDeath_Count'  => $childDeathData['ChildDeath_Count' ],
			'BirthSvid_id'      => $childDeathData['BirthSvid_id'     ],
			'PntDeathSvid_id'   => $childDeathData['PntDeathSvid_id'  ],
			'Okei_wid'          => $childDeathData['Okei_wid'         ],
		    'pmUser_id'         => $childDeathData['pmUser_id'        ]
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)'));
		}

	}

	/**
	 * Загргрузка данных по метворожденным
	 */
	function loadChildDeathGridData($data) {
		if (empty($data['BirthSpecStac_id']) && empty($data['EvnSection_id'])) {
			//return $this->createError('','Отсутвуют параметры для получения списка мертворожденных');
			return array();
		}

		$params = array();
		$where = "";

		if (!empty($data['BirthSpecStac_id'])) {
			$where = "CD.BirthSpecStac_id = :BirthSpecStac_id";
			$params['BirthSpecStac_id'] = $data['BirthSpecStac_id'];
		} else if (!empty($data['EvnSection_id'])) {
			$where = "CD.BirthSpecStac_id in (
				select top 1 BirthSpecStac_id
				from BirthSpecStac with(nolock)
				where EvnSection_id = :EvnSection_id
				order by BirthSpecStac_id desc
			)";
			$params['EvnSection_id'] = $data['EvnSection_id'];
		}

		$query = "
			select
				ChildDeath_id ,
				MedStaffFact_id ,
				(select Person_Fio from v_MedStaffFact with (nolock) where MedStaffFact_id = cd.MedStaffFact_id) AS MedStaffFact_Name ,
				Diag_id ,
				(SELECT diag_name FROM dbo.Diag AS d WITH (NOLOCK) WHERE cd.diag_id = d.diag_id) AS Diag_Name,
				Sex_id ,
				(SELECT Sex_Name FROM sex AS s WITH (NOLOCK) WHERE s.sex_id = cd.sex_id) AS Sex_Name,
				ChildDeath_Weight ,
				ChildDeath_Height ,
				PntDeathTime_id ,
				(SELECT PntDeathTime_Name FROM dbo.PntDeathTime dt with (nolock) WHERE dt.PntDeathTime_id  = cd.PntDeathTime_id) as PntDeathTime_Name,
				ChildTermType_id ,
				(SELECT ChildTermType_Name FROM ChildTermType tt with (nolock) WHERE tt.ChildTermType_id = cd.ChildTermType_id) as ChildTermType_Name,
				ChildDeath_Count ,
				BirthSvid_id ,
				(SELECT BirthSvid_Num FROM BirthSvid AS bs WITH (NOLOCK) WHERE bs.BirthSvid_id = cd.BirthSvid_id) AS BirthSvid_Num,
				PntDeathSvid.PntDeathSvid_id ,
				PntDeathSvid.PntDeathSvid_Num,
				pmUser_insID ,
				pmUser_updID ,
				ChildDeath_insDT ,
				ChildDeath_updDT,
				Okei_wid,
				CAST(ChildDeath_Weight as VARCHAR) + ' '+ ISNULL((SELECT Okei_NationSymbol FROM v_Okei o with (nolock) WHERE Okei_id = cd.Okei_wid), '') as ChildDeath_Weight_text,
				1 AS RecordStatus_Code
			from
				v_ChildDeath AS CD with(nolock)
				outer apply(
					SELECT top 1
						pds.PntDeathSvid_id,
						pds.PntDeathSvid_Num
					FROM PntDeathSvid AS pds WITH (NOLOCK)
					WHERE pds.PntDeathSvid_id = cd.PntDeathSvid_id
					and isnull(pds.PntDeathSvid_isBad, 1) = 1
					and isnull(pds.PntDeathSvid_IsLose, 1) = 1
					and isnull(pds.PntDeathSvid_IsActual, 1) = 2
				) PntDeathSvid
			where
				{$where}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка данных по рожденным детям
	 */
	function loadChildGridData($data) {
		
		$where='pch.BirthSpecStac_id = :BirthSpecStac_id';
		$join = '';
		if(empty($data['BirthSpecStac_id'])){
			$where='e.EvnSection_pid = :EvnSection_pid';
			$join = 'inner join v_EvnSection e with(nolock) on e.EvnSection_id = BSS.EvnSection_id';
		}
		
		$query = "
			declare @Person_id bigint = :Person_id;
			declare @EvnSection_pid bigint = :EvnSection_pid;
			select
				pch.PersonNewBorn_id,
				CPS.EvnPS_id as ChildEvnPS_id,
				child.Person_SurName AS Person_F,
				child.Person_FirName AS Person_I,
				child.Person_SecName AS Person_O,
				child.PersonEvn_id,
				convert(varchar(10), child.Person_BirthDay,104) AS Person_Bday,
				Sex.Sex_name,
				Sex.Sex_id,
				pch.EvnSection_mid,
				cast(W.PersonWeight_Weight as int) as Person_Weight,
				cast(W.PersonWeight_Weight as int) AS PersonWeight_text,
				cast(H.PersonHeight_Height as int) as Person_Height,
				BirthSvid.BirthSvid_id,
				BirthSvid.BirthSvid_Num,
				LT.LeaveType_Name as BirthResult,
				PersonNewBorn_CountChild as CountChild,
				PntDeathSvid.PntDeathSvid_id,
				PntDeathSvid.PntDeathSvid_Num,
				0 AS RecordStatus_Code,
				EL.EvnLink_id AS EvnLink_id,
				mother.Person_id,
				child.Server_id AS Server_id,
				child.Person_id AS Person_cid,
				BSS.BirthSpecStac_id
			from
				v_PersonNewBorn pch with(nolock)
				left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.BirthSpecStac_id
				left join v_PersonRegister PR with(nolock) on PR.PersonRegister_id = BSS.PersonRegister_id

				inner join v_PersonState child with(nolock) on child.Person_id = pch.Person_id
				left join v_Sex sex with(nolock) on sex.Sex_id = child.Sex_id

				left join v_PersonState mother with(nolock) on mother.Person_id = isnull(@Person_id, PR.Person_id)

				{$join}

				left join v_EvnPS CPS with(nolock) on CPS.EvnPS_id = pch.EvnPS_id
				left join v_LeaveType LT with(nolock) on LT.LeaveType_id = CPS.LeaveType_id
				left join v_EvnLink EL with(nolock) on EL.Evn_lid = CPS.EvnPS_id and EL.Evn_id = @EvnSection_pid

				outer apply(
					select top 1 ph.PersonHeight_Height,ph.PersonHeight_id
					from v_personHeight ph with(nolock)
					where ph.person_id = child.Person_id and ph.HeightMeasureType_id = 1
				) H
				outer apply(
					select top 1
						pw.PersonWeight_id,
						case
							when pw.Okei_id=37 then pw.PersonWeight_Weight*1000
							else pw.PersonWeight_Weight
						end as PersonWeight_Weight
					from v_personWeight pw with(nolock)
					where pw.person_id = child.Person_id and pw.WeightMeasureType_id = 1
				) W
				outer apply(
					SELECT TOP 1 pds.PntDeathSvid_id,pds.PntDeathSvid_Num
					FROM dbo.v_PntDeathSvid pds WITH (NOLOCK)
					WHERE pds.Person_cid = child.Person_id AND pds.Person_id = mother.Person_id
					and isnull(pds.PntDeathSvid_isBad, 1) = 1 
					and isnull(pds.PntDeathSvid_IsLose, 1) = 1
					and isnull(pds.PntDeathSvid_IsActual, 1) = 2
				) as PntDeathSvid
				outer apply(
					SELECT TOP 1 BirthSvid_id,BirthSvid_Num
					FROM dbo.v_BirthSvid bs WITH (NOLOCK)
					WHERE bs.Person_cid = pch.Person_id AND bs.Person_id = mother.Person_id
					and isnull(bs.BirthSvid_IsBad, 1) = 1
				) AS BirthSvid

			where {$where}
		";

		if ($this->regionNick == 'ufa' && (!empty($data['BirthSpecStac_id']))) {
			$query = "
				declare @Person_id bigint = :Person_id;
				declare @BirthSpecStac_id bigint = :BirthSpecStac_id;
				declare @EvnSection_pid bigint = NULL;
				select
					pch.PersonNewBorn_id,
					CPS.EvnPS_id as ChildEvnPS_id,
					child.Person_SurName AS Person_F,
					child.Person_FirName AS Person_I,
					child.Person_SecName AS Person_O,
					child.PersonEvn_id,
					convert(varchar(10), child.Person_BirthDay,104) AS Person_Bday,
					Sex.Sex_name,
					Sex.Sex_id,
					pch.EvnSection_mid,
					cast(W.PersonWeight_Weight as int) as Person_Weight,
					cast(W.PersonWeight_Weight as int) AS PersonWeight_text,
					cast(H.PersonHeight_Height as int) as Person_Height,
					BirthSvid.BirthSvid_id,
					BirthSvid.BirthSvid_Num,
					LT.LeaveType_Name as BirthResult,
					PersonNewBorn_CountChild as CountChild,
					PntDeathSvid.PntDeathSvid_id,
					PntDeathSvid.PntDeathSvid_Num,
					0 AS RecordStatus_Code,
					EL.EvnLink_id AS EvnLink_id,
					mother.Person_id,
					child.Server_id AS Server_id,
					child.Person_id AS Person_cid,
					@BirthSpecStac_id as BirthSpecStac_id
				from
					--v_PersonNewBorn pch with(nolock)
					v_PersonState child with(nolock)
					outer apply (
						select distinct pch0.person_id
						from v_PersonNewBorn pch0
						where pch0.BirthSpecStac_id = @BirthSpecStac_id
						--order by pch0.PersonNewBorn_insDT asc
					)pchd
					outer apply(
								select
									top 1 *
								from
									v_PersonNewBorn pch1 with (nolock)
								where
									--pch0.Person_id = ps.Person_id
									pch1.birthspecstac_id = @BirthSpecStac_id and pch1.Person_id=pchd.Person_id and  pch1.EvnPS_id is null
								order by PersonNewBorn_insDT desc
					)pch
					left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.PersonRegister_id
					left join v_PersonRegister PR with(nolock) on PR.PersonRegister_id = BSS.PersonRegister_id

					--inner join v_PersonState child with(nolock) on child.Person_id = pch.Person_id
					left join v_Sex sex with(nolock) on sex.Sex_id = child.Sex_id

					left join v_PersonState mother with(nolock) on mother.Person_id = isnull(@Person_id, PR.Person_id)

					left join v_EvnPS CPS with(nolock) on CPS.EvnPS_id = pch.EvnPS_id
					left join v_LeaveType LT with(nolock) on LT.LeaveType_id = CPS.LeaveType_id
					left join v_EvnLink EL with(nolock) on EL.Evn_lid = CPS.EvnPS_id and EL.Evn_id = @EvnSection_pid
					outer apply(
						select top 1 ph.PersonHeight_Height,ph.PersonHeight_id
						from v_personHeight ph with(nolock)
						where ph.person_id = child.Person_id and ph.HeightMeasureType_id = 1
					) H
					outer apply(
						select top 1
							pw.PersonWeight_id,
							case
								when pw.Okei_id=37 then pw.PersonWeight_Weight*1000
								else pw.PersonWeight_Weight
							end as PersonWeight_Weight
						from v_personWeight pw with(nolock)
						where pw.person_id = child.Person_id and pw.WeightMeasureType_id = 1
					) W
					outer apply(
						SELECT TOP 1 pds.PntDeathSvid_id,pds.PntDeathSvid_Num
						FROM dbo.v_PntDeathSvid pds WITH (NOLOCK)
						WHERE pds.Person_cid = child.Person_id AND pds.Person_id = mother.Person_id
						and isnull(pds.PntDeathSvid_isBad, 1) = 1
						and isnull(pds.PntDeathSvid_IsLose, 1) = 1
						and isnull(pds.PntDeathSvid_IsActual, 1) = 2
					) as PntDeathSvid
					outer apply(
						SELECT TOP 1 BirthSvid_id,BirthSvid_Num
						FROM dbo.v_BirthSvid bs WITH (NOLOCK)
						WHERE bs.Person_cid = pch.Person_id AND bs.Person_id = mother.Person_id
						and isnull(bs.BirthSvid_IsBad, 1) = 1
					) AS BirthSvid
				where pchd.Person_id=child.Person_id
			";
		}

		/*echo "<pre>".getDebugSQL($query, array(
			'EvnSection_pid' => $data['EvnSection_pid'],
			'Person_id' => $data['Person_id'],
			'BirthSpecStac_id' => $data['BirthSpecStac_id']
		))."</pre>";exit();*/
		
		$result = $this->db->query($query, array(
			'EvnSection_pid' => $data['EvnSection_pid'],
			'Person_id' => $data['Person_id'],
			'BirthSpecStac_id' => $data['BirthSpecStac_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение данных о рожденном ребенке
	 */
	function saveChild($childData){
		$procedure = "p_EvnLink_ins";

		$childData['EvnLink_id'] = null;
		/*Изменение (update) записи этой таблицы по постановке не должно потребоваться,
		поскольку при изменении детей изменяется их КВС а не непосредственно запись в EvnLink
		if ( $childData['EvnLink_id'] > 0 ) {
			$procedure = "p_EvnLink_upd";
		} else {
			$childData['EvnLink_id'] = null;
		}
		*/

		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(4000);

			set @Res = :EvnLink_id;

			EXEC dbo.$procedure
				@EvnLink_id    = @Res    output, -- bigint
				@Evn_id        = :Evn_id       , -- bigint
				@Evn_lid       = :Evn_lid      , -- bigint
				@pmUser_id     = :pmUser_id    , -- bigint
				@Error_Code    = @Error_Code    output, -- int
				@Error_Message = @Error_Message output -- varchar(4000)

			select @Res as EvnLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$queryParams = array(
			'EvnLink_id'    => $childData['EvnLink_id'],
			'Evn_id'        => $childData['Evn_id'   ],
			'Evn_lid'       => $childData['Evn_lid'  ],
			'pmUser_id'     => $childData['pmUser_id'],
		);
		//echo getDebugSQL($query, $queryParams); return false;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)'));
		}

	}
	/**
	 *
	 * @param type $data 
	 */
	function deleteChild($data, $isAllowTransaction = true){
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$this->load->model('PersonNewBorn_model');
		$this->load->model('Person_model');
		$this->load->model('MedSvid_model', 'MedSvid_model');

		if (empty($data['PersonNewBorn_id'])) {
			$data['PersonNewBorn_id'] = $this->getFirstResultFromQuery("
				select top 1 PersonNewBorn_id from v_PersonNewBorn with(nolock) where Person_id = :Person_id
			", $data, true);
			if ($data['PersonNewBorn_id'] === false) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при получении идентификатора специфики новорожденного');
			}
		}

		$query="
			select count(evn_id) as cnt 
			from v_evn with(nolock) 
			where 
				person_id = :Person_id 
				and Evn_id != isnull(:Evn_id,0)
		";
		$params = array(
			'Person_id' => $data["Person_id"],
			'Evn_id' =>isset($data['ChildEvnPS_id'])?$data['ChildEvnPS_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		$EvnCount = $this->getFirstResultFromQuery($query, $params);
		if ($EvnCount === false) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при запросе количества событий у ребенка.');
		}
		if ($EvnCount > 0 && (!isset($data['type']) || !in_array($data['type'], array('kvs', 'cancel')))) {
			$this->rollbackTransaction();
			return $this->createError('','У ребенка имеются события, удаление невозможно.');
		}

		$cnt = $this->getFirstResultFromQuery("
			select (
				select top 1 count(*) as cnt
				from v_BirthSvid with(nolock)
				where Person_cid = :Person_id 
				and isnull(BirthSvid_isBad, 1) = 1
			) + (
				select top 1 count(*) as cnt
				from v_PntDeathSvid with(nolock)
				where Person_cid = :Person_id 
				and isnull(PntDeathSvid_isBad, 1) = 1 
				and isnull(PntDeathSvid_IsLose, 1) = 1
				and isnull(PntDeathSvid_IsActual, 1) = 2 
			) as cnt
		", $params);
		if ($cnt === false) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при проверке существования свидетельств.');
		}
		if ($cnt > 0) {
			$this->rollbackTransaction();
			return $this->createError('','У ребенка имеется мед.свидетельство, удаление не возможно.');
		}

		if (!empty($data['PersonNewBorn_id'])) {
			$this->PersonNewBorn_model->setPersonNewBornEvnPS(array(
				'PersonNewBorn_id' => $data['PersonNewBorn_id'],
				'EvnPS_id' => null
			));
			if(!empty($data['PntDeathSvid_id']) && $data['PntDeathSvid_id'] > 0){
				$this->MedSvid_model->deleteMedSvid($data, 'pntdeath');
			}
			if(!empty($data['EvnLink_id']) && $data['EvnLink_id'] > 0){
				$resp = $this->deleteEvnLink($data['EvnLink_id']);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
			if(!empty($data['ChildEvnPS_id']) && $data['ChildEvnPS_id'] > 0){
				$data['EvnPS_id'] = $data['ChildEvnPS_id'];
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$data['isExecCommonChecksOnDelete']= true;
				$resp = $this->EvnPS_model->deleteEvnPS($data, false);
				//print_r($resp);exit();
				if(!$this->isSuccessful($resp)){
					$this->rollbackTransaction();
					return $resp;
				}
			}
			if(isset($data['type']) && $data['type']=='kvs'){
				$this->commitTransaction();
				return array(array('success'=>true,'Error_Code'=>null,'Error_Msg'=>null));
			}

			$query = "
				set nocount on;
			
				declare @PersonNewBorn_id bigint = :PersonNewBorn_id;
				
				declare cur1 cursor read_only for
				select PersonBirthTrauma_id from v_PersonBirthTrauma with(nolock) where PersonNewborn_id=@PersonNewBorn_id;
				declare cur2 cursor read_only for
				select NewbornApgarRate_id from v_NewbornApgarRate with(nolock) where PersonNewborn_id=@PersonNewBorn_id;
				
				declare @Error_Code bigint
				declare @Error_Message varchar(4000)
				declare @NewbornApgarRate_id bigint
				declare @PersonBirthTrauma_id bigint
				
				open cur1
				fetch next from cur1 into @PersonBirthTrauma_id
				while @@FETCH_STATUS = 0
				begin
					set @Error_Code = null
					set @Error_Message = null
					exec p_PersonBirthTrauma_del
					@PersonBirthTrauma_id = @PersonBirthTrauma_id,
					@Error_Code = @Error_Code,
					@Error_Message = @Error_Message;
					fetch next from cur1 into @PersonBirthTrauma_id
				end
				
				close cur1
				deallocate cur1
				
				open cur2
				fetch next from cur2 into @NewbornApgarRate_id
				while @@FETCH_STATUS = 0
				begin
					set @Error_Code = null
					set @Error_Message = null
					exec p_NewbornApgarRate_del
					@NewbornApgarRate_id = @NewbornApgarRate_id,
					@Error_Code = @Error_Code,
					@Error_Message = @Error_Message;
					fetch next from cur2 into @NewbornApgarRate_id
				end
				
				close cur2
				deallocate cur2
				
				update EvnObservNewborn with(rowlock) 
				set PersonNewBorn_id = null 
				where PersonNewBorn_id = @PersonNewBorn_id
				
				set @Error_Code = null
				set @Error_Message = null
				exec p_PersonNewBorn_del
					@PersonNewBorn_id = @PersonNewBorn_id,  
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
					
				set nocount off;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			$resp = $this->queryResult($query, $data);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при удалении данных новорожденного');
			}
			if (!$this->isSuccessful($resp)){
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$resp = $this->Person_model->deletePerson($params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array(array('success'=>true,'Error_Code'=>null,'Error_Msg'=>null));
	}

	/**
	 * Удаление детей из списка
	 */
	function deleteChildren($data) {
		if (count($data['PersonNewBorn_ids']) == 0) {
			return array(array('success' => true));
		}

		$ids_string = implode(",", $data['PersonNewBorn_ids']);

		$query = "
			SELECT
				PNB.PersonNewBorn_id,
				PNB.Person_id,
				EL.EvnLink_id,
				EL.Evn_lid as ChildEvnPS_id
			FROM
				v_PersonNewBorn PNB with(nolock)
				left join v_BirthSpecStac BSS with (nolock) on BSS.BirthSpecStac_id = PNB.BirthSpecStac_id
				left join v_EvnSection ES with (nolock) ON ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnPS EPS with (nolock) ON EPS.EvnPS_id = ES.EvnSection_pid
				left join v_EvnLink EL with (nolock) ON EL.Evn_id = EPS.EvnPS_id
			WHERE PNB.PersonNewBorn_id in ($ids_string)
		";
		$PersonNewBornList = $this->queryResult($query);
		if (!is_array($PersonNewBornList)) {
			return $this->createError('','Ошибка при получении данных новорожденных');
		}

		foreach ($PersonNewBornList as $PersonNewBorn) {
			//Удаление всех данных новорожденного, в том числе Person
			$resp = $this->deleteChild(array(
				'PersonNewBorn_id' => $PersonNewBorn['PersonNewBorn_id'],
				'Person_id' => $PersonNewBorn['Person_id'],
				'ChildEvnPS_id' => $PersonNewBorn['ChildEvnPS_id'],
				'EvnLink_id' => $PersonNewBorn['EvnLink_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Удаление ребенка из КВС матери
	 * @param $ChildEvnPS_id
	 * @param $EvnLink_id
	 * @param $pmUser_id
	 */
	function delChild($ChildEvnPS_id, $EvnLink_id, $pmUser_id){
		//при удалении дитя надо удалить связь мать-дитя и КВС дитя
		//удаление КВС дитя
		require_once('EvnPS_model.php');
		$EvnPS = new EvnPS_model();
		$EvnPS->deleteEvnPS(array('EvnPS_id' => $ChildEvnPS_id, 'pmUser_id' => $pmUser_id));
		$this->deleteEvnLink($EvnLink_id);
	}

	/**
	 * Удаление связи КВС матери и КВС ребенка
	 * @param $EvnLink_id
	 * @return array|mixed
	 */
	function deleteEvnLink($EvnLink_id){
		$procedure = "p_EvnLink_del";
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			EXEC dbo.$procedure
				@EvnLink_id    = :EvnLink_id    , -- bigint
				@Error_Code    = @Error_Code    output, -- int
				@Error_Message = @Error_Message output -- varchar(4000)

			select :EvnLink_id as EvnLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$queryParams = array(
			'EvnLink_id'    => $EvnLink_id
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связи КВС матери и КВС ребенка)'));
		}
	}

	/**
	 * Удаление данных по метровожденному
	 */
	function deleteChildDeath($ChildDeath_id){
		$PntDeathSvid_id = $this->getFirstResultFromQuery("
			select top 1 
				PntDeathSvid.PntDeathSvid_id 
			from 
				v_ChildDeath CD with(nolock)
				outer apply(
					SELECT top 1 pds.PntDeathSvid_id
					FROM PntDeathSvid AS pds WITH (NOLOCK)
					WHERE pds.PntDeathSvid_id = cd.PntDeathSvid_id
					and isnull(pds.PntDeathSvid_isBad, 1) = 1
					and isnull(pds.PntDeathSvid_IsLose, 1) = 1
					and isnull(pds.PntDeathSvid_IsActual, 1) = 2
				) PntDeathSvid
			where CD.ChildDeath_id = :ChildDeath_id
		", array('ChildDeath_id' => $ChildDeath_id), true);
		if ($PntDeathSvid_id === false) {
			return $this->createError('','Ошибка при получении мед.свидетельсвта мертворожденного');
		}
		if (!empty($PntDeathScvid_id)) {
			return $this->createError('','Нельзя удалить мертворожденного, т.к. выписано свидетельство о смерти');
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_ChildDeath_del
				@ChildDeath_id= :ChildDeath_id    , -- bigint
				@Error_Code    = @Error_Code    output, -- int
				@Error_Message = @Error_Message output -- varchar(4000);
			select :ChildDeath_id as EvnLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$queryParams = array(
			'ChildDeath_id'    => $ChildDeath_id
		);
		$response = $this->queryResult($query, $queryParams);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении мертворожденного');
		}
		return $response;
	}

	/**
	 * Проверка
	 */
	function checkChild($data){
		$childEvnPS_id = !empty($data['childEvnPS_id'])?$data['childEvnPS_id']:null;
		$motherEvnPS_id = !empty($data['motherEvnPS_id'])?$data['motherEvnPS_id']:null;
		if (!empty($data['BirthSpecStac_OutcomeDate'])) {
			$BirthSpecStac_OutcomeDate = ':BirthSpecStac_OutcomeDate';
			$queryParams['BirthSpecStac_OutcomeDate'] = $data['BirthSpecStac_OutcomeDate'];
		} else {
			if (!$data['motherEvnSection_id']) {
				return $this->createError('','Не указан идентификатор движения матери');
			}
			$BirthSpecStac_OutcomeDate = "(
				SELECT top 1 b.BirthSpecStac_OutcomDT
				FROM dbo.v_BirthSpecStac b with (nolock)
				WHERE b.EvnSection_id = :motherEvnSection_id
				ORDER BY BirthSpecStac_id desc
			)";
			$queryParams['motherEvnSection_id'] = $data['motherEvnSection_id'];
		}
		if (isset($data['mother_Person_id']) && $data['mother_Person_id']) {
			$mother_Person_id = ':mother_Person_id';
			$queryParams['mother_Person_id'] = $data['mother_Person_id'];
			$queryParams['motherEvnPS_id'] = null;
		} else {
			if (!$data['motherEvnPS_id']) {
				return $this->createError('','Не указан идентификатор КВС матери');
			}
			$mother_Person_id = '( SELECT    person_id
			  FROM v_evnPS with (nolock)
			  WHERE evnPS_id = :motherEvnPS_id
			)';
			$queryParams['motherEvnPS_id'] = $motherEvnPS_id;
		}
		if (isset($data['child_Person_id']) && $data['child_Person_id']) {
			$child_Person_id = ':child_Person_id';
			$queryParams['child_Person_id'] = $data['child_Person_id'];
			$queryParams['childEvnPS_id'] = null;
		} else {
			$queryParams['child_Person_id']=null;
			if (!$data['childEvnPS_id']) {
				return $this->createError('','Не указан идентификатор КВС ребенка');
			}
			$child_Person_id = '( SELECT person_id
			  FROM v_EvnPS with (nolock)
			  WHERE EvnPS_id = :childEvnPS_id
			)';
			$queryParams['childEvnPS_id'] = $childEvnPS_id;
		}
		$result = array(array(
			'Success' => true
		));
		$query = "
			declare @child_Person_id bigint = {$child_Person_id};
			declare @mother_Person_id bigint = {$mother_Person_id};
			declare @childEvnPS_id bigint = :childEvnPS_id;
			declare @BirthSpecStac_OutcomeDate date = {$BirthSpecStac_OutcomeDate};
			declare @Person_BirthDay date = (
				SELECT Person_BirthDay FROM dbo.v_PersonState with (nolock) WHERE Person_id = @child_Person_id
			);
			
			SELECT  
				case when @child_Person_id = @mother_Person_id then 1 else 0 end as isSamePerson,
				case when @child_Person_id in (
					SELECT (
						SELECT Person_id FROM v_EvnPS ps with (nolock) 
						WHERE ps.EvnPS_id = el.Evn_lid and ps.EvnPS_id != @childEvnPS_id
					)
					FROM dbo.v_EvnLink el with (nolock)
					WHERE Evn_id IN (SELECT EvnPS_id FROM v_EvnPS with (nolock) WHERE Person_id = @mother_Person_id)
				) then 1 else 0 end as alredyBindedThis,
				ISNULL((
					SELECT
						ISNULL(Person_SurName,'') + ' ' + ISNULL(Person_FirName,'') + ' ' + ISNULL(Person_SecName,'')
					FROM
						dbo.v_PersonState with (nolock)
					WHERE   
						Person_id = (
							SELECT Person_id
							FROM dbo.v_EvnPS with (nolock)
							WHERE EvnPS_id = ( 
								SELECT TOP 1 evn_id
								FROM dbo.v_EvnLink with (nolock)
								WHERE Evn_lid IN (
									SELECT EvnPS_id
									FROM v_EvnPS with (nolock)
									WHERE person_id = @child_Person_id 
								)
								AND Evn_id not in (SELECT EvnPS_id FROM v_EvnPS with (nolock) WHERE person_id = @mother_Person_id)
							)
						)
				),0) AS alredyBindedAnother,
				DATEDIFF(dd, @BirthSpecStac_OutcomeDate, @Person_BirthDay) as datediff
		";
		//echo getDebugSQL($query, $queryParams);exit;
		$query_result = $this->db->query($query, $queryParams);
		if ($query_result) {
			$query_result_array = $query_result->result('array');
			if (1==count($query_result_array)){
				//принимаем и расшифровываем пользователю сообщения об ошибках, переданные базой
				//childAndMotherIsSamePerson должно быть 0, иначе "Мать добавляется в список рожденных ею детей"
				$err = '';
				if (!(0==$query_result_array[0]['isSamePerson'])) {
					$err = 'Мать добавляется в список рожденных ею детей';
				} else {
					//childAlredyBindedToThisMother должно быть 0, иначе "Ребенок к матери добавляется повторно"
					
					//childAlredyBindedToAnotherMother должно быть 0, инчае "Ребенок уже привязан к другой матери (ФИО матери - в поле)"
					if (!('0'==$query_result_array[0]['alredyBindedAnother'])) {
						$err = "Ребенок уже привязан к другой матери ({$query_result_array[0]['alredyBindedAnother']})";
					} else {
						//datediffOutcomBateOfBirth должно быть
						//  более 0, иначе "Добавляется ребенок, дата рождения которого наступила на N дней раньше даты этих родов"
						//  менее или равна 2, иначе "Добавляется ребенок, дата рождения которого наступила на N дней позже даты этих родов"
						$days = (int)$query_result_array[0]['datediff'];
						if ($days<0) {
							$days = -1*$days;
							$word_case = $this->ru_word_case('день', 'дня', 'дней', $days);
							$err = "Добавляется ребенок, дата рождения которого наступила на $days $word_case раньше даты этих родов";
						} else {
							if ($days>2) {
								//  менее или равна 2, иначе "Добавляется ребенок, дата рождения которого наступила на N дней позже даты этих родов"
								$word_case = $this->ru_word_case('день', 'дня', 'дней', $days);
								$err = "Добавляется ребенок, дата рождения которого наступила на $days $word_case позже даты этих родов";
							}
						}
					}
					
				}
				if ($err){
					$result = array(
						array(
							'Success' => false,
							'Error_Msg' => $err
						)
					);
				}
			} else {
				$result = array(
					array(
						'Success' => false,
						'Error_Msg' => 'Ошибочный результат запроса (проверка связи мать-ребенок)'
					)
				);
			}
		} else {
			$result = array(
				array(
					'Success' => false,
					'Error_Msg' => 'Ошибка запроса к БД (проверка связи мать-ребенок)'
				)
			);
		}
		
		$query = "
			declare @pers_id bigint = (
				SELECT person_id
				FROM v_EvnPS with (nolock)
				WHERE EvnPS_id = :childEvnPS_id
			)
			if (@pers_id is null) begin set @pers_id = :child_Person_id end
			select
				ps.Person_id,
				el.EvnLink_id,
				pc.PersonNewBorn_id,
				pc.EvnPS_id
			from v_personstate ps with(nolock)
			outer apply (
				select top 1 el.EvnLink_id
				FROM dbo.v_EvnLink el with (nolock)
				WHERE el.Evn_id = :motherEvnPS_id and el.Evn_lid = :childEvnPS_id
			) as el
			left join v_PersonNewBorn pc with(nolock) on pc.person_id = ps.person_id
			where ps.person_id =@pers_id
		";
		$res = $this->db->query($query, $queryParams);
		$res = $res->result('array');
		
		if(count($res)>0){

			if($query_result_array[0]['alredyBindedThis']>0){
				$result = array(array('success' => false,'Error_Msg' => 'Ребенок к матери добавляется повторно'));
			}else{
				if($res[0]['EvnLink_id']==null){
					$arr=array(
						'EvnLink_id'    => null,
						'Evn_id'        => $motherEvnPS_id,
						'Evn_lid'       => $childEvnPS_id,
						'pmUser_id'     => $data['pmUser_id']
						);
					$this->saveChild($arr);
				}
				$result[0]['person_id'] = $res[0]['Person_id'];
				if($res[0]['PersonNewBorn_id']==null){
					$result[0]['add'] = 1;
				}
			}

			if (!empty($res[0]['PersonNewBorn_id']) && empty($res[0]['EvnPS_id'])) {
				$this->load->model('PersonNewBorn_model');
				$this->PersonNewBorn_model->setPersonNewBornEvnPS(array(
					'PersonNewBorn_id' => $res[0]['PersonNewBorn_id'],
					'EvnPS_id' => $childEvnPS_id
				));
			}
		}else{
			/*$result = array(
				array(
					'Success' => false,
					'Error_Msg' => 'Ребенок к матери добавляется повторно'
				)
			);*/

		}
		 
		return $result;
	}

	/**
	 * Склоняем слово по числам
	 * на входе
	 * $case1 - ед. число,
	 * $case2 - мн. число для 2, 3, 4 или оканчивающихся на 2, 3, 4
	 * $case3 - мн. число для 5-20 (включительно), и всех что кончаются на любые кроме 2, 3, 4
	 * $anInteger - число
	 * пример:
	 *   '1 '.ru_word_case('день', 'дня', 'дней', 1) // output: 1 день
	 *   '2 '.ru_word_case('день', 'дня', 'дней', 2) // output: 2 дня
	 *   '11 '.ru_word_case('день', 'дня', 'дней', 11) // output: 11 дней
	 *   '21 '.ru_word_case('день', 'дня', 'дней', 21) // output: 21 день
	 */
	function ru_word_case($case1, $case2, $case3, $anInteger){
		$result = $case3;
		if (($anInteger < 5)||(20 < $anInteger)) {
			$days = (string)$anInteger;
			$lastSymbol =  $days[strlen($anInteger)-1];
			switch ($lastSymbol) {
				case '1':
					$result = $case1;
					break;
				case '2':
				case '3':
				case '4':
					$result = $case2;
					break;
				default:
					break;
			}
		}
		return $result;
	}

	/**
	 * Удаление специфики по беременности
	 * @param $BirthSpecStac_id
	 * @param $pmUser_id
	 * @return bool|mixed
	 */
	function del($BirthSpecStac_id, $pmUser_id, $isAllowTransaction = true) {
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		//получение списка детей и удаление
		$childLinks = $this->getChildLinks($BirthSpecStac_id);
		foreach ($childLinks as $childLink) {
			//Удаление всех данных новорожденного, в том числе Person
			$resp = $this->deleteChild(array(
				'PersonNewBorn_id' => $childLink['PersonNewBorn_id'],
				'Person_id' => $childLink['Person_id'],
				'ChildEvnPS_id' => $childLink['ChildEvnPS_id'],
				'EvnLink_id' => $childLink['EvnLink_id'],
				'pmUser_id' => $pmUser_id
			), false);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$ChildDeaths = $this->getChildDeaths($BirthSpecStac_id);
		foreach ($ChildDeaths as $ChildDeath) {
			$resp = $this->deleteChildDeath($ChildDeath['ChildDeath_id']);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$PersonChildList = $this->queryResult("
			select PersonChild_id from v_PersonChild where BirthSpecStac_id = :BirthSpecStac_id
		", array('BirthSpecStac_id' => $BirthSpecStac_id));
		if (!is_array($PersonChildList)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при удалении PersonChild');
		}
		foreach($PersonChildList as $PersonChild) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec dbo.p_PersonChild_del
					@PersonChild_id = :PersonChild_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$resp = $this->queryResult($query, $PersonChild);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при удалении PersonChild');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_BirthSpecStac_del
				@BirthSpecStac_id = :BirthSpecStac_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array('BirthSpecStac_id' => $BirthSpecStac_id);
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при удалении исхода беременности');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return $response;
	}

	/**
	 * Получение данных о специфике родов. Метод для API.
	 */
	function getBirthSpecStacForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['EvnSection_id'])) {
			$filter .= " and bss.EvnSection_id = :EvnSection_id";
			$queryParams['EvnSection_id'] = $data['EvnSection_id'];
		}
		if (!empty($data['PregnancyResult_id'])) {
			$filter .= " and bss.PregnancyResult_id = :PregnancyResult_id";
			$queryParams['PregnancyResult_id'] = $data['PregnancyResult_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				bss.BirthSpecStac_id,
				bss.EvnSection_id,
				bss.BirthSpecStac_CountPregnancy,
				bss.BirthSpecStac_CountBirth,
				bss.BirthSpecStac_CountChild,
				bss.BirthSpecStac_CountChildAlive,
				bss.BirthResult_id,
				bss.BirthPlace_id,
				bss.BirthSpecStac_OutcomPeriod,
				bss.BirthSpecStac_OutcomDT,
				bss.BirthSpec_id,
				case when bss.BirthSpecStac_IsHIVtest = 2 then 1 else 0 end as BirthSpecStac_IsHIVtest,
				case when bss.BirthSpecStac_IsHIV = 2 then 1 else 0 end as BirthSpecStac_IsHIV,
				bss.AbortType_id,
				case when bss.BirthSpecStac_IsMedicalAbort = 2 then 1 else 0 end as BirthSpecStac_IsMedicalAbort,
				bss.BirthSpecStac_BloodLoss,
				bss.PregnancySpec_id,
				bss.Evn_id,
				bss.Lpu_id,
				bss.PregnancyResult_id,
				bss.BirthCharactType_id,
				bss.AbortLpuPlaceType_id,
				case when bss.BirthSpecStac_IsRWtest = 2 then 1 else 0 end as BirthSpecStac_IsRWtest,
				case when bss.BirthSpecStac_IsRW = 2 then 1 else 0 end as BirthSpecStac_IsRW,
				case when bss.BirthSpecStac_IsHBtest = 2 then 1 else 0 end as BirthSpecStac_IsHBtest,
				case when bss.BirthSpecStac_IsHB = 2 then 1 else 0 end as BirthSpecStac_IsHB,
				case when bss.BirthSpecStac_IsHCtest = 2 then 1 else 0 end as BirthSpecStac_IsHCtest,
				case when bss.BirthSpecStac_IsHC = 2 then 1 else 0 end as BirthSpecStac_IsHC,
				bss.AbortLawType_id,
				bss.AbortMethod_id,
				bss.BirthSpecStac_InjectVMS,
				bss.BirthSpecStac_Info,
				bss.BirthSpecStac_SurgeryVolume,
				bss.AbortIndicat_id,
				bss.BirthSpecStac_IsContrac,
				bss.BirthSpecStac_ContracDesc,
				bss.PersonRegister_id
			from
				v_BirthSpecStac bss with (nolock)
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение списка КВС детей связанных с КВС матери
	 *
	 * @param $BirthSpecStac_id
	 * @return bool|mixed
	 */
	function getChildLinks($BirthSpecStac_id){
		$q = "
			SELECT
				PNB.PersonNewBorn_id,
				PNB.Person_id,
				el.EvnLink_id,
				el.Evn_lid as ChildEvnPS_id
			FROM
				v_BirthSpecStac bs with (nolock)
				inner join v_PersonNewBorn PNB with(nolock) on PNB.BirthSpecStac_id = bs.BirthSpecStac_id
				left join v_EvnSection es with (nolock) ON bs.EvnSection_id = es.EvnSection_id
				left join v_EvnPS ps with (nolock) ON ps.EvnPS_id = es.EvnSection_pid
				left join v_EvnLink el with (nolock) ON el.Evn_id = ps.EvnPS_id
			WHERE bs.BirthSpecStac_id = :BirthSpecStac_id
		";
		$p = array('BirthSpecStac_id' => $BirthSpecStac_id);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
		} else {
			log_message('error', 'BirthSpecStac::getChildLinks query error: '. var_dump($q, true).' '.var_dump($p, true));
			$result =  false;
		}
		return $result;
	}

	/**
	 * список мертворожденных
	 * @param $BirthSpecStac_id
	 * @return bool|mixed
	 */
	function getChildDeaths($BirthSpecStac_id){
		$q = <<<Q
SELECT ChildDeath_id FROM ChildDeath with (nolock) WHERE BirthSpecStac_id = :BirthSpecStac_id
Q;
		$p = array('BirthSpecStac_id' => $BirthSpecStac_id);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
		} else {
			log_message('error', 'BirthSpecStac::getChildDeaths query error: '. var_dump($q, true).' '.var_dump($p, true));
			$result =  false;
		}
		return $result;
	}

	/**
	 * метод проверки связи движения и исхода беременности
	 */
	function mCheckingMovementAndPregnancyOutcomeForAPI($data){
		if(empty($data['EvnSection_id'])) return false;
		$query = "
			SELECT
				convert(varchar(19),BirthSpecStac_OutcomDT,120) as BirthSpecStac_OutcomDT,
				BSS.BirthSpecStac_CountPregnancy,
				PR.PregnancyResult_Name,
				BSS.BirthSpecStac_OutcomPeriod,
				BSS.BirthSpecStac_CountChild,
				BSS.BirthSpecStac_BloodLoss,
				ALPT.AbortLpuPlaceType_Name,
				ALT.AbortLawType_Name,
				AM.AbortMethod_Name,
				AI.AbortIndicat_Name,
				BSS.BirthSpecStac_InjectVMS,
				BP.BirthPlace_Name,
				BSS.BirthSpecStac_CountBirth,
				BS.BirthSpec_Name,
				--BR.BirthResult_Name,
				BCT.BirthCharactType_Name as BirthCharactType_Name,
				BSS.BirthSpecStac_CountChildAlive
			FROM v_BirthSpecStac BSS WITH(NOLOCK)
				LEFT JOIN dbo.PregnancyResult PR WITH(NOLOCK) ON PR.PregnancyResult_id = BSS.PregnancyResult_id
				LEFT JOIN v_AbortLpuPlaceType ALPT WITH(NOLOCK) ON ALPT.AbortLpuPlaceType_id = BSS.AbortLpuPlaceType_id
				LEFT JOIN v_AbortLawType ALT WITH(NOLOCK) ON ALT.AbortLawType_id = BSS.AbortLawType_id
				LEFT JOIN v_AbortMethod AM WITH(NOLOCK) ON AM.AbortMethod_id = BSS.AbortMethod_id
				LEFT JOIN dbo.v_AbortIndicat AI WITH(NOLOCK) ON AI.AbortIndicat_id = BSS.AbortIndicat_id
				LEFT JOIN v_BirthPlace BP WITH(NOLOCK) ON BP.BirthPlace_id = BSS.BirthPlace_id
				LEFT JOIN v_BirthSpec BS WITH(NOLOCK) ON BS.BirthSpec_id = BSS.BirthSpec_id
				LEFT JOIN v_BirthResult BR WITH(NOLOCK) ON BR.BirthResult_id = BSS.BirthResult_id
				LEFT JOIN v_BirthCharactType BCT WITH(NOLOCK) ON BCT.BirthCharactType_id = BSS.BirthCharactType_id
			WHERE BSS.EvnSection_id = :EvnSection_id
		";
		$r = $this->db->query($query, $data);
		if (is_object($r)) {
			$result = $r->result('array');
		} else {
			$result =  false;
		}
		return $result;
	}
}
