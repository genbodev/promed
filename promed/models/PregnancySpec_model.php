<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Created by JetBrains PhpStorm.
 * User: IGabdushev
 * Date: 24.10.11
 * Time: 10:43
 */

class PregnancySpec_model extends CI_Model
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
SELECT  PregnancySpec_id ,
        PersonDisp_id ,
        Lpu_aid ,
        PregnancySpec_Period ,
        PregnancySpec_Count ,
        PregnancySpec_CountBirth ,
        PregnancySpec_CountAbort ,
        CONVERT(VARCHAR(10), PregnancySpec_BirthDT, 104) AS PregnancySpec_BirthDT ,
        BirthResult_id ,
        PregnancySpec_OutcomPeriod ,
        CONVERT(VARCHAR(10), PregnancySpec_OutcomDT, 104) AS PregnancySpec_OutcomDT ,
        BirthSpec_id ,
        PregnancySpec_IsHIVtest ,
        PregnancySpec_IsHIV ,
        ( SELECT    EvnSection_id
          FROM      v_evnSection with(nolock)
          WHERE     evnSection_id IN ( SELECT   top 1 EvnSection_id
                                       FROM     dbo.v_BirthSpecStac with(nolock)
                                       WHERE    PregnancySpec_id = :PregnancySpec_id
                                        order BY BirthSpecStac_updDT DESC)
        ) AS EvnSection_id ,
        ( SELECT    EvnSection_pid
          FROM      v_evnSection with(nolock)
          WHERE     evnSection_id IN ( SELECT   top 1 EvnSection_id
                                       FROM     dbo.v_BirthSpecStac with(nolock)
                                       WHERE    PregnancySpec_id = :PregnancySpec_id
                                        order BY BirthSpecStac_updDT DESC)
        ) AS EvnSection_pid,
		CONVERT(VARCHAR(10), ( SELECT
								d.PersonDisp_begDate
							   FROM
								dbo.PersonDisp d with(nolock)
							   WHERE
								d.PersonDisp_id = s.personDisp_id
							 ), 104) AS PersonDisp_begDate
       --pmUser_insID ,
       --pmUser_updID ,
       --PregnancySpec_insDT ,
       --PregnancySpec_updDT
FROM    dbo.PregnancySpec s with(nolock)
WHERE   PregnancySpec_id = :PregnancySpec_id";
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

					SELECT TOP 1
					    'edit' as accessType,
						EUC.EvnUslugaPregnancySpec_id AS EvnUslugaCommon_id,
						EUC.EvnUslugaPregnancySpec_pid as EvnUslugaCommon_pid,
						EUC.EvnUslugaPregnancySpec_pid as EvnUslugaCommon_rid,
						EUC.Person_id,
						EUC.PersonEvn_id,
						EUC.Server_id,
						convert(varchar(10), EUC.EvnUslugaPregnancySpec_setDate, 104) as EvnUslugaCommon_setDate,
						ISNULL(EUC.EvnUslugaPregnancySpec_setTime, '') as EvnUslugaCommon_setTime,
						convert(varchar(10), EUC.EvnUslugaPregnancySpec_disDate, 104) as EvnUslugaCommon_disDate,
						ISNULL(EUC.EvnUslugaPregnancySpec_disTime, '') as EvnUslugaCommon_disTime,
						EUC.UslugaPlace_id,
						EUC.Lpu_uid,
						EUC.Org_uid,
						EUC.LpuSection_uid,
						EUC.LpuSectionProfile_id,
						EUC.MedPersonal_id,
						EUC.UslugaComplex_id,
						EUC.PayType_id,
						ROUND(ISNULL(EUC.EvnUslugaPregnancySpec_Kolvo, 0), 2) as EvnUslugaCommon_Kolvo
					FROM
						dbo.v_EvnUslugaPregnancySpec EUC with (nolock)
					WHERE (1 = 1)
						and EUC.EvnUslugaPregnancySpec_id = :id

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
SELECT  EvnUslugaPregnancySpec_id ,
        CONVERT(VARCHAR(10), p.EvnUslugaPregnancySpec_setDate, 104) AS EUPS_setDate ,
        -- EvnUslugaPregnancySpec_setTime as EUPS_setTime,
        p.EvnUslugaPregnancySpec_pid ,
        p.EvnUslugaPregnancySpec_rid ,
        p.Server_id ,
        p.PersonEvn_id ,
        p.Person_id ,
        p.PayType_id ,
        uc.UslugaComplex_Name AS Usluga_Name ,
        uc.UslugaComplex_Code AS Usluga_Code ,
        p.Usluga_id ,
        p.MedPersonal_id ,
        p.UslugaPlace_id ,
        CASE p.UslugaPlace_id
          WHEN 1 --1 Отделение ЛПУ
               THEN ( SELECT    lpu_Nick
                      FROM      v_lpu l with(nolock)
                      WHERE     l.lpu_id = p.lpu_id
                    )
          WHEN 2 --2 Другое ЛПУ
               THEN ( SELECT    lpu_Nick
                      FROM      v_lpu l with(nolock)
                      WHERE     l.lpu_id = p.lpu_uid
                    )
          ELSE --3 Другая организация
               ( SELECT Org_Nick
                 FROM   v_org o with(nolock)
                 WHERE  o.Org_id = p.org_uid
               )
        END AS lpu_name ,
        p.LpuSection_uid ,
        p.EvnUslugaPregnancySpec_Kolvo AS EUPS_Kolvo ,
        p.Org_uid ,
        p.UslugaComplex_id ,
        p.EvnUslugaPregnancySpec_isCito AS EUPS_isCito ,
        p.MedPersonal_sid ,
        p.PregnancySpec_id-- ,        EvnUslugaPregnancySpec_PregnancyTerm as EUPS_PregnancyTerm
FROM    dbo.v_EvnUslugaPregnancySpec p with(nolock)
	left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = p.UslugaComplex_id
WHERE   PregnancySpec_id =  :PregnancySpec_id";
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUslugaPregnancySpec_id;
			exec " . $procedure . "
				@EvnUslugaPregnancySpec_id            = @Res output                          , -- bigint
				@EvnUslugaPregnancySpec_pid           = :EvnUslugaPregnancySpec_pid          , -- bigint
				@Lpu_id                               = :Lpu_id                              , -- bigint
				@Server_id                            = :Server_id                           , -- bigint
				@PersonEvn_id                         = :PersonEvn_id                        , -- bigint
				@EvnUslugaPregnancySpec_setDT         = :EvnUslugaPregnancySpec_setDT        , -- datetime
				@EvnUslugaPregnancySpec_disDT         = :EvnUslugaPregnancySpec_disDT        , -- datetime
				@PayType_id                           = :PayType_id                          , -- bigint
				@UslugaComplex_id                            = :UslugaComplex_id                           , -- bigint
				@MedPersonal_id                       = :MedPersonal_id                      , -- bigint
				@UslugaPlace_id                       = :UslugaPlace_id                      , -- bigint
				@Lpu_uid                              = :Lpu_uid                             , -- bigint
				@LpuSection_uid                       = :LpuSection_uid                      , -- bigint
				@LpuSectionProfile_id                 = :LpuSectionProfile_id                , -- bigint
				@EvnUslugaPregnancySpec_Kolvo         = :EvnUslugaPregnancySpec_Kolvo        , -- float
				@Org_uid                              = :Org_uid                             , -- bigint
				@PregnancySpec_id                     = :PregnancySpec_id                    , -- bigint
		--		@EvnUslugaPregnancySpec_PregnancyTerm = EvnUslugaPregnancySpec_PregnancyTerm, -- int
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id                            = :pmUser_id                           , -- bigint
				@Error_Code                           = @ErrCode output                      , -- int
				@Error_Message                        = @ErrMessage output                     -- varchar(4000)
			select @Res as EvnUslugaCommon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PregnancySpec_id;

			EXEC dbo.$procedure
				@PregnancySpec_id           = @Res OUTPUT   , -- bigint
				@PersonDisp_id              = :PersonDisp_id             , -- bigint
				@Lpu_aid                    = :Lpu_aid                   , -- bigint
				@PregnancySpec_Period       = :PregnancySpec_Period      , -- int
				@PregnancySpec_Count        = :PregnancySpec_Count       , -- int
				@PregnancySpec_CountBirth   = :PregnancySpec_CountBirth  , -- int
				@PregnancySpec_CountAbort   = :PregnancySpec_CountAbort  , -- int
				@PregnancySpec_BirthDT      = :PregnancySpec_BirthDT     , -- datetime
				@BirthResult_id             = :BirthResult_id            , -- bigint
				@PregnancySpec_OutcomPeriod = :PregnancySpec_OutcomPeriod, -- int
				@PregnancySpec_OutcomDT     = :PregnancySpec_OutcomDT    , -- datetime
				@BirthSpec_id               = :BirthSpec_id              , -- bigint
				@PregnancySpec_IsHIVtest    = :PregnancySpec_IsHIVtest   , -- bigint
				@PregnancySpec_IsHIV        = :PregnancySpec_IsHIV       , -- bigint
				@pmUser_id                  = :pmUser_id                 , -- bigint
				@Error_Code                 = @ErrCode OUTPUT, -- int
				@Error_Message              = @ErrMessage OUTPUT -- varchar(4000)

			select @Res as PregnancySpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		$procedure = "p_PregnancySpec_upd";
		$query = "
			DECLARE
				@PregnancySpec_id BIGINT,
				@PersonDisp_id  BIGINT,
				@Lpu_aid  BIGINT,
				@PregnancySpec_Period  INT,
				@PregnancySpec_Count  INT,
				@PregnancySpec_CountBirth  INT,
				@PregnancySpec_CountAbort  INT,
				@PregnancySpec_BirthDT DATETIME,
				@BirthResult_id  BIGINT,
				@PregnancySpec_OutcomPeriod  INT,
				@PregnancySpec_OutcomDT DATETIME,
				@BirthSpec_id  BIGINT,
				@PregnancySpec_IsHIVtest  BIGINT,
				@PregnancySpec_IsHIV  BIGINT,
				@pmUser_id  BIGINT,
				@Error_Code INT,
				@Error_Message VARCHAR(4000);
			SELECT
				@PregnancySpec_id           = PregnancySpec_id,
				@PersonDisp_id              = PersonDisp_id,
				@Lpu_aid                    = Lpu_aid,
				@PregnancySpec_Period       = PregnancySpec_Period,
				@PregnancySpec_Count        = PregnancySpec_Count,
				@PregnancySpec_CountBirth   = PregnancySpec_CountBirth,
				@PregnancySpec_CountAbort   = PregnancySpec_CountAbort,
				@PregnancySpec_BirthDT      = PregnancySpec_BirthDT,
				@BirthResult_id             = BirthResult_id,
				@PregnancySpec_OutcomPeriod = PregnancySpec_OutcomPeriod,
				@PregnancySpec_OutcomDT     = PregnancySpec_OutcomDT,
				@BirthSpec_id               = BirthSpec_id,
				@PregnancySpec_IsHIVtest    = PregnancySpec_IsHIVtest,
				@PregnancySpec_IsHIV        = PregnancySpec_IsHIV
			FROM dbo.PregnancySpec with(nolock) WHERE PregnancySpec_id = :PregnancySpec_id;
			IF @BirthResult_id             IS NULL SET @BirthResult_id             = :BirthResult_id            ; -- bigint
			IF @PregnancySpec_OutcomPeriod IS NULL SET @PregnancySpec_OutcomPeriod = :PregnancySpec_OutcomPeriod; -- int
			IF @PregnancySpec_OutcomDT     IS NULL SET @PregnancySpec_OutcomDT     = :PregnancySpec_OutcomDT    ; -- datetime
			IF @BirthSpec_id               IS NULL SET @BirthSpec_id               = :BirthSpec_id              ; -- bigint
			IF @pmUser_id                  IS NULL SET @pmUser_id                  = :pmUser_id                 ; -- bigint
			EXEC dbo.p_PregnancySpec_upd
				@PregnancySpec_id           = @PregnancySpec_id          ,
				@PersonDisp_id              = @PersonDisp_id             ,
				@Lpu_aid                    = @Lpu_aid                   ,
				@PregnancySpec_Period       = @PregnancySpec_Period      ,
				@PregnancySpec_Count        = @PregnancySpec_Count       ,
				@PregnancySpec_CountBirth   = @PregnancySpec_CountBirth  ,
				@PregnancySpec_CountAbort   = @PregnancySpec_CountAbort  ,
				@PregnancySpec_BirthDT      = @PregnancySpec_BirthDT     ,
				@BirthResult_id             = @BirthResult_id            ,
				@PregnancySpec_OutcomPeriod = @PregnancySpec_OutcomPeriod,
				@PregnancySpec_OutcomDT     = @PregnancySpec_OutcomDT    ,
				@BirthSpec_id               = @BirthSpec_id              ,
				@PregnancySpec_IsHIVtest    = @PregnancySpec_IsHIVtest   ,
				@PregnancySpec_IsHIV        = @PregnancySpec_IsHIV       ,
				@pmUser_id                  = @pmUser_id                 ,
				@Error_Code                 = @Error_Code                ,
				@Error_Message              = @Error_Message
			SELECT @PregnancySpec_id AS PregnancySpec_id, @Error_Code AS Error_Code, @Error_Message AS Error_Msg;		";
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PregnancySpecComplication_id;
			EXEC dbo.$procedure
				@PregnancySpecComplication_id    = @Res output,
				@PregnancySpec_id                = :PregnancySpec_id               , -- bigint
				@PregnancySpecComplication_setDT = :PregnancySpecComplication_setDT, -- datetime
				@Diag_id                         = :Diag_id                        , -- bigint
				@pmUser_id                       = :pmUser_id                      , -- bigint
				@Error_Code                      = @ErrCode output,
				@Error_Message                   = @ErrMessage output;
			select @Res as PregnancySpecComplication_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PSED_id;
			EXEC dbo.$procedure
				@PregnancySpecExtragenitalDisease_id    = @Res output,
				@PregnancySpec_id                = :PregnancySpec_id               , -- bigint
				@PregnancySpecExtragenitalDisease_setDT = :PregnancySpecExtragenitalDisease_setDT, -- datetime
				@Diag_id                         = :Diag_id                        , -- bigint
				@pmUser_id                       = :pmUser_id                      , -- bigint
				@Error_Code                      = @ErrCode output,
				@Error_Message                   = @ErrMessage output;
			select @Res as PSED_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
SELECT  PregnancySpecComplication_id ,
        PregnancySpec_id ,
        CONVERT(VARCHAR(10), PregnancySpecComplication_setDT, 104) AS PSC_setDT ,
        Diag_id ,
        ( SELECT    diag_fullname
          FROM      v_Diag d with(nolock)
          WHERE     diag_id = c.diag_id
        ) AS Diag_Name,
        1 AS RecordStatus_Code
FROM    dbo.v_PregnancySpecComplication c with(nolock)
WHERE   PregnancySpec_id = :PregnancySpec_id";

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
SELECT  PregnancySpecExtragenitalDisease_id as PSED_id ,
        PregnancySpec_id ,
        CONVERT(VARCHAR(10), PregnancySpecExtragenitalDisease_setDT, 104) AS PSED_setDT ,
        Diag_id ,
        ( SELECT    diag_fullname
          FROM      v_Diag d with(nolock)
          WHERE     diag_id = c.diag_id
        ) AS Diag_Name,
        1 AS RecordStatus_Code
FROM    dbo.v_PregnancySpecExtragenitalDisease c with(nolock)
WHERE   PregnancySpec_id = :PregnancySpec_id";

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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PregnancySpecComplication_del
				@PregnancySpecComplication_id = :PregnancySpecComplication_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PregnancySpecExtragenitalDisease_del
				@PregnancySpecExtragenitalDisease_id = :PregnancySpecExtragenitalDisease_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
SELECT  ( SELECT    di.Diag_Code + '. ' + di.Diag_Name
          FROM      dbo.v_Diag di with(nolock)
          WHERE     di.Diag_id = d.Diag_id
        ) + ' ' + ( SELECT  Lpu_Nick
                    FROM    dbo.v_Lpu l with(nolock)
                    WHERE   l.Lpu_id = d.Lpu_id
                  ) + ' (c ' + CONVERT(VARCHAR(10), d.PersonDisp_begDate, 104)
        + ' по '
        + ( CASE WHEN d.PersonDisp_endDate IS NULL THEN 'текущий момент'
                 ELSE CONVERT(VARCHAR(10), d.PersonDisp_endDate, 104)
            END ) + ')' AS PersonDisp_Name ,
        PersonDisp_id ,
        ( SELECT TOP 1
                    PregnancySpec_id
          FROM      dbo.PregnancySpec s with(nolock)
          WHERE     s.PersonDisp_id = d.PersonDisp_id
          ORDER BY  d.PersonDisp_updDT DESC
        ) AS PregnancySpec_id
FROM    personDisp d with(nolock)
WHERE   person_id IN ( SELECT   person_id
                       FROM     PersonDisp with(nolock)
                       WHERE    PersonDisp_id = ? )
        AND lpu_id NOT IN ( SELECT  lpu_id
                            FROM    PersonDisp with(nolock)
                            WHERE   PersonDisp_id = ? )
        AND diag_id IN ( SELECT diag_id
                         FROM   dbo.SicknessDiag with(nolock)
                         WHERE  PrivilegeType_id = 509 )		";
        $result = $this->db->query($sql, array($data['PersonDisp_id'], $data['PersonDisp_id']));

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
		$sql = "(SELECT '--Не наблюдалась--' as opt, null as PregnancySpec_id, NULL as Person_id, NULL as Server_id, NULL as PersonDisp_id) UNION ALL SELECT
					( SELECT
						diag_FullName
					  FROM
						dbo.v_Diag di with(nolock)
					  WHERE
						di.Diag_id = d.Diag_id
					) + ' (' +
					( SELECT
						lpu_nick
					  FROM
						dbo.v_lpu l with(nolock)
					  WHERE
						l.lpu_id = d.lpu_id
					) + ', c ' +
					CONVERT(VARCHAR, d.PersonDisp_begDate, 104) + ' по ' +
					CASE WHEN d.PersonDisp_endDate IS NULL THEN 'текущий момент'
						 ELSE CONVERT(VARCHAR(10), d.PersonDisp_endDate, 104)
					END + ')' AS opt,
					(select top 1 PregnancySpec_id FROM dbo.PregnancySpec s with(nolock) WHERE s.PersonDisp_id = d.PersonDisp_id order by PregnancySpec_id DESC) AS PregnancySpec_id,
					d.Person_id,
					d.Server_id,
					d.PersonDisp_id
				FROM
					dbo.v_personDisp d with(nolock)
				WHERE
					person_id = ?
					AND EXISTS (SELECT PregnancySpec_id FROM dbo.PregnancySpec s with(nolock) WHERE s.PersonDisp_id = d.PersonDisp_id)
					AND d.Diag_id IN (SELECT Diag_id FROM dbo.SicknessDiag with(nolock) WHERE Sickness_id = 9)";
        $q = $this->db->query($sql, array($data['Person_id']));
        if (is_object($q)){
             $result = $q->result('array');
        }
		return $result;
	}

}
