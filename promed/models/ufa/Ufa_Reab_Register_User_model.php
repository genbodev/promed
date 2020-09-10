<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Ufa_Reab_Register_User_model - молеь для работы с данными регистра реабилитации(Башкирия)
 * пользовательская  часть
 *
 * @package
 * @author
 * @version			17.01.2017
 */
class Ufa_Reab_Register_User_model extends swModel {

	var $scheme = "r2";

	// Профили наблюдения
	//    var $SysNick1 = "cnsReab";
	//    var $SysNick2 = "cardiologyReab";
	//    var $SysNick3= "travmReab";
	//  var $listMorbusType_SysNick = "('cnsReab','cardiologyReab','travmReab')";
	/**
	 *  * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение сведений о пациенте (для первоначальной записи в регистр)
	 */
	function SeekRegistr($data) {
		$params = array(
				'Person_id' => $data['Person_id'],
				'MorbusType_id' => $data['MorbusType_id']
		);

		$query = "select 
                    Person_id, 
                    MorbusType_id
                  from dbo.PersonRegister with(nolock) 
                  where Person_id = :Person_id
                  and MorbusType_id = :MorbusType_id 
                  
        ";

		$result = $this->db->query($query, $params);



		if (is_object($result)) {
			$dataInDB = $result->result('array');

			if (!empty($dataInDB)) {
				return false;
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение параметров анкеты
	 */
	function saveRegistrAnketa($data) {
		$params = array(
				'pmUser_id' => $data['pmUser_id'],
				'ReabQuestion_setDate' => $data['ReabQuestion_setDate'],
				'MedPersonal_iid' => $data['MedPersonal_iid'],
				'Lpu_iid' => $data['Lpu_iid'],
				'ReabPotent' => $data['ReabPotent'],
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id'],
				'Person_id' => $data['Person_id'],
				'Param' => $data['parameter']
		);

		//          echo "Param";
		//         echo '<pre>' . print_r($data['parameter'], 1) . '</pre>';
		//echo "isButtonEdit";
		//echo '<pre>' . print_r($data['isButtonEdit'], 1) . '</pre>';

		if ($data['isButtonAdd'] === 'true')
		{
			//echo 'Будет insert';
			$query = "declare 
				@pmUser_id bigint,
				@ReabQuestion_Date datetime,
				@MedPersonal_iid bigint,
				@Lpu_iid  bigint,
				@ReabPotent numeric (5,2),
				@DirectType_id bigint,
				@StageType_id bigint = null,
				@Person_id bigint,
				@Param varchar(max),
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabQuestion_ins
            @pmUser_id = :pmUser_id,
            @ReabQuestion_Date = :ReabQuestion_setDate,
            @MedPersonal_iid  = :MedPersonal_iid,
	    @Lpu_iid  = :Lpu_iid,
	    @ReabPotent = :ReabPotent,
	    @DirectType_id = :DirectType_id,
	    @StageType_id = :StageType_id,
	    @Person_id = :Person_id,
	    @Param = :Param,
            @Error_Code = @Error_Code output,
            @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message";
		}

		if ($data['isButtonEdit'] === 'true') {
			// echo 'Будет update'; 
			$query = "declare 
						@pmUser_id bigint,
						@ReabQuestion_Date datetime,
						@MedPersonal_iid bigint,
						@Lpu_iid  bigint,
						@ReabPotent numeric (5,2),
						@DirectType_id bigint,
	@StageType_id bigint = null,
	@Person_id bigint,
	@Param varchar(max),
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabQuestion_upd
            @pmUser_id = :pmUser_id,
            @ReabQuestion_Date = :ReabQuestion_setDate,
            @MedPersonal_iid  = :MedPersonal_iid,
	    @Lpu_iid  = :Lpu_iid,
	    @ReabPotent = :ReabPotent,
	    @DirectType_id = :DirectType_id,
	    @StageType_id = :StageType_id,
	    @Person_id = :Person_id,
	    @Param = :Param,
            @Error_Code = @Error_Code output,
            @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message 
        ";
		}



		//echo getDebugSql($query, $params); exit;
		$result = $this->db->query($query, $params);
		//   sql_log_message('error', 'saveRegistrAnketa: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение параметров тестирования
	 */
	function saveRegistrTest($data) {
		$params = array(
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id'],
				'Person_id' => $data['Person_id'],
				'ReabTest_setDate' => $data['ReabTest_setDate'],
				'ReabResultTest_Parameter' => $data['ReabTestParam_id'],
				'ReabResultTest_Value' => $data['ReabTestValue_id'],
				'pmUser_id' => $data['pmUser_id'],
				'MedPersonal_iid' => $data['MedPersonal_iid'],
				'Lpu_iid' => $data['Lpu_iid'],
				'ReabResultTest_id' => $data['ReabResultTest_id']
		);

		//          echo "Param";
		//         echo '<pre>' . print_r($data['parameter'], 1) . '</pre>';
		if ($data['isButton'] == 'Add') {
			$query = "declare 
            @DirectType_id bigint,
	    @StageType_id bigint,
	    @Person_id bigint ,
	    @ReabTest_setDate datetime,	
            @ReabResultTest_Parameter bigint,
	    @ReabTestValue  bigint,
	    @pmUser_id bigint,
            @MedPersonal_iid bigint,
	    @Lpu_iid  bigint,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabResultTest_ins
              @DirectType_id = :DirectType_id,
	      @StageType_id = :StageType_id,
	      @Person_id = :Person_id,
	      @ReabTest_setDate = :ReabTest_setDate,
	      @ReabResultTest_Parameter = :ReabResultTest_Parameter,
	      @ReabTestValue  = :ReabResultTest_Value,
              @pmUser_id = :pmUser_id,
              @MedPersonal_iid  = :MedPersonal_iid,
	      @Lpu_iid  = :Lpu_iid,
              @Error_Code = @Error_Code output,
              @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}

		if ($data['isButton'] == 'Edit') {
			$query = "declare 
            @ReabResultTest_id bigint,
	    @ReabTest_setDate datetime,	
            @ReabResultTest_Parameter bigint,
	    @ReabTestValue  bigint,
	    @pmUser_id bigint,
            @MedPersonal_iid bigint,
	    @Lpu_iid  bigint,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabResultTest_upd
               @ReabResultTest_id = :ReabResultTest_id,
	       @ReabTest_setDate = :ReabTest_setDate,
	      @ReabResultTest_Parameter = :ReabResultTest_Parameter,
	      @ReabTestValue  = :ReabResultTest_Value,
              @pmUser_id = :pmUser_id,
              @MedPersonal_iid  = :MedPersonal_iid,
	      @Lpu_iid  = :Lpu_iid,
              @Error_Code = @Error_Code output,
              @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}

		if ($data['isButton'] == 'Delete') {
			$query = "declare 
            @ReabResultTest_id bigint,
	    @pmUser_id bigint,
            @MedPersonal_did bigint,
	    @Lpu_did  bigint,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabResultTest_del
              @ReabResultTest_id = :ReabResultTest_id,
              @pmUser_id = :pmUser_id,
              @MedPersonal_did  = :MedPersonal_iid,
	      @Lpu_did  = :Lpu_iid,
              @Error_Code = @Error_Code output,
              @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}
		//echo getDebugSql($query, $params); exit;
		//sql_log_message('error', 'saveRegistrTest: ', getDebugSql($query, $params));
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение измерений ЧСС
	 */
	function saveHeartRate($data) {
		$params = array(
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id'],
				'Person_id' => $data['Person_id'],
				'ReabHeartRate_setDate' => $data['ReabHeartRate_setDate'],
				'ReabHeartRate_peace' => $data['ReabHeartRate_peace'],
				'ReabHeartRate_max' => $data['ReabHeartRate_max'],
				'pmUser_id' => $data['pmUser_id'],
				'MedPersonal_iid' => $data['MedPersonal_iid'],
				'Lpu_iid' => $data['Lpu_iid'],
				'ReabHeartRate_id' => $data['ReabHeartRate_id']
		);

		//echo "Param";
		//echo '<pre>' . print_r($data['parameter'], 1) . '</pre>';
		if ($data['isButton'] == 'Add') {
			//echo "isButtonEdit";
			//echo '<pre>' . print_r($data['isButtonEdit'], 1) . '</pre>';
			//echo 'Будет insert';
			$query = "declare 
            @DirectType_id bigint,
	    @StageType_id bigint,
	    @Person_id bigint ,
	    @ReabHeartRate_setDate datetime,	
            @ReabHeartRate_peace int,
	    @ReabHeartRate_max int,
	    @pmUser_id bigint,
            @MedPersonal_iid bigint,
	    @Lpu_iid  bigint,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabHeartRate_ins
              @DirectType_id = :DirectType_id,
	      @StageType_id = :StageType_id,
	      @Person_id = :Person_id,
              @ReabHeartRate_setDate = :ReabHeartRate_setDate,
              @ReabHeartRate_peace = :ReabHeartRate_peace,
	      @ReabHeartRate_max  = :ReabHeartRate_max,
	      
              @pmUser_id = :pmUser_id,
              @MedPersonal_iid  = :MedPersonal_iid,
	      @Lpu_iid  = :Lpu_iid,
              @Error_Code = @Error_Code output,
              @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}

		if ($data['isButton'] == 'Delete') {
			$query = "declare 
            @ReabHeartRate_id bigint,
	    @pmUser_id bigint,
            @MedPersonal_did bigint,
	    @Lpu_did  bigint,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabHeartRate_del
              @ReabHeartRate_id = :ReabHeartRate_id,
              @pmUser_id = :pmUser_id,
              @MedPersonal_did  = :MedPersonal_iid,
	      @Lpu_did  = :Lpu_iid,
              @Error_Code = @Error_Code output,
              @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}

		//echo getDebugSql($query, $params); exit;
		$result = $this->db->query($query, $params);
		// sql_log_message('error', 'saveRegistrAnketa: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Закрытие этапа реабилитации
	 */
	function CloseRegistrStage($Inparams) {
		$params = array(
				'ReabEvent_id' => $Inparams['ReabEvent_id'],
				'ReabOutCause_id' => $Inparams['ReabOutCause_id'],
				'ReabRegister_Date' => $Inparams['ReabRegister_Date'],
				'MedPersonal_did' => $Inparams['MedPersonal_did'],
				'Lpu_did' => $Inparams['Lpu_did'],
				'pmUser_id' => $Inparams['pmUser_id']
		);

		$query = "declare 
                     @ReabEvent_id bigint,
					 @ReabOutCause_id bigint = null,
                     @ReabRegister_Date varchar(100),
                     @MedPersonal_did bigint = null,
                     @Lpu_did bigint = null,
                     @pmUser_id bigint,
                     @Error_Code int,
                     @Error_Message varchar(4000)
                exec r2.p_ReabEvent_upd
		     @ReabEvent_id = :ReabEvent_id,
		     @ReabOutCause_id = :ReabOutCause_id,
                     @ReabRegister_Date = :ReabRegister_Date,
                     @MedPersonal_did  = :MedPersonal_did,
                     @Lpu_did   = :Lpu_did,
                     @pmUser_id = :pmUser_id,
                     @Error_Code = @Error_Code output,
                     @Error_Message = @Error_Message output                
            select @Error_Code as Error_Code, @Error_Message as Error_Message";
		$result = $this->db->query($query, $params);
		//echo '<pre>' . print_r($result, 1) . '</pre>';
		// sql_log_message('error', 'saveInStage: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *  Отмена закрытия этапа реабилитации
	 */
	function CanselCloseStage($Inparams) {
		$params = array(
				'ReabEvent_id' => $Inparams['ReabEvent_id'],
				'Person_id' => $Inparams['Person_id'],
				'pmUser_id' => $Inparams['pmUser_id']
		);

		$query = "
			declare 
                @Error_Code int = null ,
				@Error_Message varchar(4000) = null ;
				set nocount on
				Begin TRY
					begin tran
					
					update r2.ReabEvent with (ROWLOCK) set
						ReabOutCause_id =  null,
						ReabEvent_disDate =  null,
						MedPersonal_did =  null,
						Lpu_did =  null,
						pmUser_updID = :pmUser_id,
						ReabEvent_updDT = dbo.tzGetDate()
					 where ReabEvent_id = :ReabEvent_id and Person_id = :Person_id and 
							ReabEvent_disDate is not null and ReabOutCause_id is not null

					commit tran

				End TRY
					Begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					End catch
				set nocount off
					SELECT
						@Error_Code as Error_Code,
						@Error_Message as Error_Msg;
					";
		$result = $this->db->query($query, $params);
		//		echo '<pre>' . print_r($result, 1) . '</pre>';
		//		sql_log_message('error', 'CanselCloseStage: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Проверка наличия пациента в регистре принципиально)
	 */
	function checkPersonInRegister($params) {
		// echo "666";
		$params = array(
				'Person_id' => $params['Person_id']
		);

		$query = "select top 1
                    d.Person_id, 
                    d.ReabDirectType_id
                 FROM  r2.ReabEvent d with(nolock)
                      where Person_id =:Person_id
                      and d.ReabEvent_Deleted = 1";

		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'checkPersonInRegister query: ', getDebugSql($query, $params));
		if (is_object($result)) {
			$dataInDB = $result->result('array');

			if (!empty($dataInDB)) {
				// echo 'Есть данные';
				return true;
			} else {
				//echo '!!!!!!!! данные';
				return false;
			}
		} else {
			//echo 'Отсутствуют данные';
			return false;
		}
	}

	/**
	 *  Проверка наличия пациента в регистре по профилю)
	 */
	function checkPersonReabRegister($params) {

		$params = array(
				'Person_id' => $params['Person_id'],
				'DirectType' => $params['DirectType_id']
		);

		$query = "Select r.ReabStageType_id, st.ReabStageType_SysNick as StageName,
					isnull(r.ReabOutCause_id,0) as ReabOutCause_id,  r.ReabEvent_disDate,
					r.ReabEvent_setDate, r.ReabEvent_id
                  FROM r2.ReabEvent r with(nolock)
		    left join r2.ReabStageType st on r.ReabStageType_id = st.ReabStageType_id
                  where r.Person_id = :Person_id and r.ReabDirectType_id = :DirectType and r.ReabEvent_Deleted = 1  
                  order by IsNULL(r.ReabEvent_disDate, '3000-01-01') desc";

		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'checkPersonReabRegister: ', getDebugSql($query, $params));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения Cправочников GRIDa
	 */
	function ReabSpr1($aparam) {
		$params = array(
				'ReabSpr_Cod' => $aparam['CodSpr'],
				'CodGroupSpr' => $aparam['CodGroupSpr']
		);
		$query = "select tt.ReabSpr_Elem_Name,
                            tt.ReabSpr_Elem_id,
                            tt.ReabSpr_Level,
                            'Нет' as selrow,
                            tt.ReabSpr_Elem_Weight
                            From r2.ReabSpr tt with(nolock)
                 where tt.ReabSpr_Cod = :ReabSpr_Cod and tt.ReabSpr_Group = :CodGroupSpr
                 order by tt.ReabSpr_Elem_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения  GRID4
	 */
	function ReabSpr($aparam) {
		$params = array(
				'ReabSpr_Cod' => $aparam['CodSpr'],
				'CodGroupSpr' => $aparam['CodGroupSpr']
		);

		$query = "select tt.ReabRegime_Name as paramName,
                            tt.ReabRegime_SprNum as paramId,
                            tt.ReabRegime_Level as ReabSprLevel,
                            tt.ReabRegime_SprWeight as paramWeight
                            From r2.ReabRegime tt with(nolock)
                 where tt.ReabRegime_Code = :ReabSpr_Cod and tt.ReabRegime_Group = :CodGroupSpr
                 order by tt.ReabRegime_SprNum
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения combo профилей реабилитации
	 */
	function SeekProfReab($aParam) {

		//   var_dump ($aParam);
		$filter = $aParam['Profil'];

		$params = array();

		$query = "select 
                   M.ReabDirectType_id as DirectType_id,
                   M.ReabDirectType_Name as DirectType_name
                 from  r2.ReabDirectType M with(nolock)
                  where M.ReabDirectType_SysNick in {$filter}
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения combo причин завершения этапа
	 */
	function SeekOutCauseReab($aParam) {

		//		$filter = $aParam['ListCombo'];
		$params = array();

		//		$query = "select M.ReabOutCause_id as OutCause_id,
		//                          M.ReabOutCause_Name as OutCause_Name
		//                   from  r2.ReabOutCause M with(nolock)
		//                   where m.reabOutCause_Code > 0 and m.ReabOutCause_id in {$filter}
		//				   order by m.ReabOutCause_Code
		//                ";
		$query = "select M.ReabOutCause_id as OutCause_id,
                          M.ReabOutCause_Name as OutCause_Name
                   from  r2.ReabOutCause M with(nolock)
                   where m.reabOutCause_Code > 0 
				   order by m.ReabOutCause_Code
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения combo профилей реабилитации
	 */
	function SeekStageReab($aParam) {

		$params = array();

		$query = "select 
                   M.ReabStageType_id as StageType_id,
                   M.ReabStageType_Name as StageType_Name,
	           M.ReabStageType_SysNick as StageType
                   from  r2.ReabStageType M with(nolock)
				 order by m.ReabStageType_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Справочник Выраженности нарушений/Оценки реализации (ICFSeverity) - 1-ый определитель
	 */
	function ICFSeverity($aParam) {

		$params = array();

		$query = "select  
                    m.ICFSeverity_id as ICFSeverity_id,
				    m.ICFSeverity_Code as Code,
				    m.ICFSeverity_Name as Name
                   from  dbo.ICFSeverity M with(nolock)
				   where m.ICFSeverity_Code is not null
				   order by m.ICFSeverity_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Справочник Характера нарушений (ICFNature) - 2-ый определитель
	 */
	function ICFNature($aParam) {

		$params = array();

		$query = "select  
                    m.ICFNature_id as ICFNature_id,
				    m.ICFNature_Code as Code,
				    m.ICFNature_Name as Name
                   from  dbo.ICFNature M with(nolock)
				   where m.ICFNature_Code is not null
				   order by m.ICFNature_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Справочник Локализации нарушений (ICFLocalization) - 3-ый определитель
	 */
	function ICFLocalization($aParam) {

		$params = array();

		$query = "select  
                    m.ICFLocalization_id as ICFLocalization_id,
				    m.ICFLocalization_Code as Code,
				    m.ICFLocalization_Name as Name
                   from  dbo.ICFLocalization M with(nolock)
				   where m.ICFLocalization_Code is not null
				   order by m.ICFLocalization_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Справочник степени выраженности среды (ICFEnvFactors) - e раздел
	 */
	function ICFEnvFactors($aParam) {

		$params = array();

		$query = "select   
					m.ICFEnvFactors_id as ICFEnvFactors_id,
				    m.ICFEnvFactors_Code as Code,
				    m.ICFEnvFactors_Name as Name
                   from  dbo.ICFEnvFactors M with(nolock)
				   where m.ICFEnvFactors_Code is not null
				   order by m.ICFEnvFactors_id
                ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Добавления пациента в таблицу случаев
	 */
	function saveInReabRegister($InParam) {

		$params = array(
				'Person_id' => $InParam['Person_id'],
				'DirectType_id' => $InParam['DirectType_id'],
				'ReabEvent_setDate' => $InParam['ReabEvent_setDate'],
				'StageType_id' => $InParam['StageType_id'],
				'MedPersonal_iid' => $InParam['MedPersonal_iid'],
				'Lpu_iid' => $InParam['Lpu_iid'],
				'pmUser_id' => $InParam['pmUser_id']
		);
		$query = " declare
                     @Person_id bigint,
                     @DirectType_id bigint = null,
                     @StageType_id bigint = null,
                     @ReabEvent_setDate datetime = null,
                     @MedPersonal_iid bigint = null,
                     @Lpu_iid bigint = null,
                     @pmUser_id bigint,
                     @Error_Code int,
                     @Error_Message varchar(4000)
                exec r2.p_ReabEvent_ins
                      @Person_id = :Person_id,
                      @DirectType_id = :DirectType_id,
                      @StageType_id = :StageType_id,
                      @ReabEvent_setDate = :ReabEvent_setDate,
                      @MedPersonal_iid = :MedPersonal_iid,
                      @Lpu_iid = :Lpu_iid,
                      @pmUser_id = :pmUser_id,
                      @Error_Code = @Error_Code output,
                      @Error_Message = @Error_Message output                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;
            ";

		$result = $this->db->query($query, $params);
		//echo 'отлавливаем ошибку1 = ';
		//echo '<pre>' . print_r($result, 1) . '</pre>';
		//sql_log_message('error', 'saveInReabRegister query: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Построение древа дат измерений по шкалам, относительно предмета наблюдения
	 */
	function getTreeDatesScales($data) {
		$params = array(
				'DirectType_id' => $data['DirectType_id'],
				'Person_id' => $data['Person_id'],
				'ScaleSysNick' => $data['Scale_SysNick'],
				'ReabEvent_id' => $data['ReabEvent_id']
		);

		$query = "select CONVERT(char(10), ReabScaleCondit_setDT,120) as text,
                    'true' as leaf             
                 from r2.ReabScaleCondit r with(nolock)  ,
	             (select * from dbo.ScaleType s with(nolock) where s.ScaleType_sysNick = :ScaleSysNick ) d
                 where r.Person_id = :Person_id  and r.ReabDirectType_id = :DirectType_id 
			and r.ScaleType_id = d.ScaleType_id and r.ReabScaleCondit_Deleted = 1 and r.ReabEvent_id = :ReabEvent_id
                 order by ReabScaleCondit_setDT DESC  ";

		$result = $this->db->query($query, $params);

		//echo getDebugSql($query, $params);
		//exit;



		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление измерений по шкалам
	 */
	function deleteRegistrScale($InParam) {
		$params = array(
				'pmUser_id' => $InParam['pmUser_id'],
				'ReabScaleCondit_setDate' => $InParam['ReabScale_setDate'],
				'MedPersonal_did' => $InParam['MedPersonal_did'],
				'Lpu_did' => $InParam['Lpu_did'],
				'DirectType_id' => $InParam['DirectType_id'],
				'Person_id' => $InParam['Person_id'],
				'Scale_SysNick' => $InParam['Scale_SysNick']
		);

		$query = "declare 
        @pmUser_id bigint,
        @ReabScaleCondit_setDate datetime,
        @MedPersonal_did bigint,
	@Lpu_did  bigint,
	@DirectType_id bigint,
	@Person_id bigint,
        @Scale_SysNick varchar(50),
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabScaleCondit_del
            @pmUser_id = :pmUser_id,
            @ReabScaleCondit_setDate = :ReabScaleCondit_setDate,
            @MedPersonal_did  = :MedPersonal_did,
	    @Lpu_did  = :Lpu_did,
	    @DirectType_id = :DirectType_id,
	    @Person_id = :Person_id,
            @Scale_SysNick = :Scale_SysNick,
            @Error_Code = @Error_Code output,
            @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";

		$result = $this->db->query($query, $params);
		// sql_log_message('error', 'ReabRegistScale_ins: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение измерений по шкалам
	 */
	function saveRegistrScale($InParam) {
		$params = array(
				'pmUser_id' => $InParam['pmUser_id'],
				'ReabScaleCondit_setDT' => $InParam['ReabScale_setDate'],
				'MedPersonal_iid' => $InParam['MedPersonal_iid'],
				'Lpu_iid' => $InParam['Lpu_iid'],
				'ReabScaleCondit_ScaleParameter' => $InParam['ReabScaleParameter'],
				'DirectType_id' => $InParam['DirectType_id'],
				'ReabScaleCondit_ScaleResult' => $InParam['ReabScaleResult'],
				'Person_id' => $InParam['Person_id'],
				'Scale_SysNick' => $InParam['Scale_SysNick'],
			//  'StageType_id' => $InParam['StageType_id'],
				'ReabEvent_id' => $InParam['ReabEvent_id'],
				'ReabScaleRefinement' => $InParam['ReabScaleRefinement']
		);


		// echo '<pre>' . print_r($InParam['isButtonAdd'], 1) . '</pre>';

		if ($InParam['isButtonAdd'] === 'true') {
			$query = "declare 
          @pmUser_id bigint,
        @ReabScaleCondit_setDT datetime,
        @MedPersonal_iid bigint,
	@Lpu_iid  bigint,
	@ReabScaleCondit_ScaleResult numeric (6,2),
	@DirectType_id bigint,
	@ReabScaleCondit_ScaleParameter varchar(450),
        @ReabScaleCondit_Refinement varchar(1000),
	@Person_id bigint,
        @Scale_SysNick varchar(50),
        @ReabEvent_id bigint ,
            @Error_Code int,
            @Error_Message varchar(4000)
          exec r2.p_ReabScaleCondit_ins
            @pmUser_id = :pmUser_id,
            @ReabScaleCondit_setDT = :ReabScaleCondit_setDT,
            @MedPersonal_iid  = :MedPersonal_iid,
	    @Lpu_iid  = :Lpu_iid,
	    @ReabScaleCondit_ScaleResult = :ReabScaleCondit_ScaleResult,
	    @DirectType_id = :DirectType_id,
	    @ReabScaleCondit_ScaleParameter = :ReabScaleCondit_ScaleParameter,
            @ReabScaleCondit_Refinement = :ReabScaleRefinement,
	    @Person_id = :Person_id,
            @Scale_SysNick = :Scale_SysNick,
            @ReabEvent_id = :ReabEvent_id,
            @Error_Code = @Error_Code output,
            @Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";

			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Наименование шкал
	 */
	function getListScales($data) {
		$filter = $data['ScaleType_SysNick'];
		$params = array(
				'LoadNameScales' => $data['ScaleType_SysNick']
		);
		//echo "$filter";
		//echo '<pre>' . print_r($filter, 1) . '</pre>';
		$query = "select  case 
                           when sc.ScaleType_SysNick = 'Bartel' then 0
                           else Sc.ScaleType_id
                           end as ScaleLine,
                           Sc.ScaleType_id as ScaleType_id, sc.ScaleType_SysNick as ScaleType_SysNick, sc.ScaleType_Name as ScaleType_Name
                   from dbo.ScaleType SC with(nolock)
                   where sc.ScaleType_SysNick in ( {$filter} )
                  order by ScaleLine";


		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Загрузка данных из справочника шкал
	 */
	function scaleSpr($data) {
		$params = array(
				'ReabSpr_Cod' => $data['SysNick']
		);
		//CONVERT(int,ScaleParameterResult_Value) 
		if ($data['SysNick'] == 'renkin' || $data['SysNick'] == 'rivermid' || $data['SysNick'] == 'Killip' ||
				$data['SysNick'] == 'Ashworth' || $data['SysNick'] == 'Hauser') {

			$query = "select dd.ScaleParameterResult_id,dd.ScaleParameterResult_Name,CONVERT(int,dd.ScaleParameterResult_Value ) as ScaleParameterResult_Value
                  FROM  v_Scale dd with(nolock)
                  where dd.scaletype_sysnick = :ReabSpr_Cod
                  order by dd.ScaleParameterResult_id asc
                  ";
		}
		if ($data['SysNick'] == 'glasgow' || $data['SysNick'] == 'Harris' || $data['SysNick'] == 'Alarm_HADS' ||
				$data['SysNick'] == 'Depression_HADS' || $data['SysNick'] == 'МоСА' || $data['SysNick'] == 'GRACE' ||
				$data['SysNick'] == 'Berg' || $data['SysNick'] == 'Frenchay' || $data['SysNick'] == 'VAScale' ||
				$data['SysNick'] == 'MedResCouncil' || $data['SysNick'] == 'Bartel' || $data['SysNick'] == 'Vasserman' ||
				$data['SysNick'] == 'FIM' || $data['SysNick'] == 'ARAT' || $data['SysNick'] == 'dysarthria' || $data['SysNick'] == 'rivermid_DAA' || $data['SysNick'] == 'nihss') {
			//           $query = "select dd.ScaleParameterResult_id,dd.ScaleParameterResult_Name,CONVERT(int,dd.ScaleParameterResult_Value ) as ScaleParameterResult_Value, dd.ScaleParameterType_id,dd.ScaleParameterType_Name
			//                  FROM dbo.Scales dd with(nolock)
			//                  where dd.EvnScaleType_SysNick = :ReabSpr_Cod
			//                  order by  dd.ScaleParameterType_id, dd.ScaleParameterResult_id asc
			//                  " ; 
			$query = "select dd.ScaleParameterResult_id,dd.ScaleParameterResult_Name,CONVERT(int,dd.ScaleParameterResult_Value ) as ScaleParameterResult_Value, dd.ScaleParameterType_id,dd.ScaleParameterType_Name
                  FROM  v_Scale dd with(nolock)
                  where dd.scaletype_sysnick  = :ReabSpr_Cod
                  order by  dd.ScaleParameterType_id, dd.ScaleParameterResult_id asc
                  ";
		}
		if ($data['SysNick'] == 'Lequesne') {
			$query = "select dd.ScaleParameterResult_id,dd.ScaleParameterResult_Name,
                cast(cast( dd.ScaleParameterResult_Value as decimal(3,1)) as varchar(3))  as ScaleParameterResult_Value, 
                dd.ScaleParameterType_id,dd.ScaleParameterType_Name
                 FROM  v_Scale dd with(nolock)
                  where dd.scaletype_sysnick  = 'Lequesne'
                  order by  dd.ScaleParameterType_id, dd.ScaleParameterResult_id asc";
		}

		if ($data['SysNick'] == 'GRACE-ST' || $data['SysNick'] == 'GRACE+ST') {
			$filter = "'" . $data['SysNick'] . "','GRACE'";
			$query = "select dd.ScaleType_SysNick, dd.ScaleParameterType_SysNick as ParameterType_SysNick,
                            dd.ScaleParameterResult_id,dd.ScaleParameterResult_Name,dd.ScaleParameterResult_Value,
                            dd.ScaleParameterType_id,dd.ScaleParameterType_Name
                    FROM  v_Scale dd with(nolock)
                    where dd.scaletype_sysnick  in ({$filter})
                  order by  dd.ScaleParameterType_id, dd.ScaleParameterResult_id asc";
		}

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Загрузка данных  шкал по пациенту
	 */
	function scaleDataPers($data) {
		$params = array(
				'SysNick' => $data['SysNick'],
				'Person_id' => $data['Person_id'],
				'DirectType_id' => $data['DirectType_id'],
				'ReabRegister_id' => $data['ReabEvent_id']
		);

		$query = "select rr.ReabScaleCondit_setDT as setDate, rr.ReabScaleCondit_ScaleParameter as scaleParam , Lpu_iid as LpuId,
					rr.MedPersonal_iid as MedPersonal,rr.ReabScaleCondit_ScaleResult as ScaleResult, ReabScaleCondit_Refinement as ScaleRefinement, ReabScaleCondit_id as ScaleId   
				from r2.ReabScaleCondit rr with(nolock),
				   (select * from dbo.ScaleType s with(nolock) where s.ScaleType_sysNick = :SysNick ) d
                   where rr.Person_id = :Person_id
                   and rr.ReabDirectType_id = :DirectType_id
		           and rr.ScaleType_id = d.ScaleType_id
                   and rr.ReabEvent_id = :ReabRegister_id
                   and rr.ReabScaleCondit_Deleted = 1
                   order by rr.ReabScaleCondit_setDT desc";

		//sql_log_message('error', 'ReabScaleCondit: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка профилей наблюдения для конкретного пациента
	 */
	function getListObjectsCurrentUser($data) {
		$filter = $data['Profil'];
		$params = array(
				'Person_id' => $data['Person_id']
		);


		$query = "select 
					tt.ReabEvent_id, 
					tt.Person_id,
					m.ReabDirectType_Name as DirectType_Name,
					m.ReabDirectType_SysNick as DirectType_SysNick,
					tt.ReabDirectType_id as DirectType_id,
					st.ReabStageType_id as StageType_id,
					st.ReabStageType_Name as StageName,
					CONVERT(varchar(10),  tt.ReabEvent_disDate, 104) as Event_disDate,
					CONVERT(varchar(10),  tt.ReabEvent_updDT, 104) as Event_updDT,
					CONVERT(varchar(10),  tt.ReabEvent_setDate, 104) as Event_setDate,
					tt.MedPersonal_did,
                    tt.Lpu_did,
					tt.ReabOutCause_id as OutCause_id,
					isnull(cc.ReabOutCause_Code,0) as OutCause_Code,
		    cc.ReabOutCause_Name as OutCause_Name
		  FROM 
		    r2.ReabEvent tt with(nolock)
			 left join r2.ReabDirectType m with(nolock) on tt.ReabDirectType_id = m.ReabDirectType_id
			 left join r2.ReabStageType st with(nolock) on tt.ReabStageType_id = st.ReabStageType_id
			 left join (select t.ReabDirectType_id, max(t.ReabEvent_updDT) as updDT from r2.ReabEvent t with(nolock) 
				   where t.Person_id = :Person_id and  t.ReabEvent_Deleted = 1  group by t.ReabDirectType_id) ff on tt.ReabDirectType_id = ff.ReabDirectType_id 
                         left join r2.ReabOutCause cc with(nolock) on tt.ReabOutCause_id = cc.ReabOutCause_id
                  where m.ReabDirectType_SysNick in {$filter}
                      and tt.ReabEvent_updDT = ff.updDT
                      and  tt.Person_id = :Person_id
                     order by   tt.ReabDirectType_id";

		//sql_log_message('error', 'model PersReab: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка тестов (по профилю и этапу) для конкретного пациента
	 */
	function getListTestUserReab($data) {

		$params = array(
				'Person_id' => $data['Person_id'],
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id']
		);

		$query = "select 
            tt.ReabResultTest_id as ReabTestId,
            CONVERT(varchar(10), tt.ReabResultTest_setDT, 104) + ' ' + CONVERT(varchar(5), tt.ReabResultTest_setDT, 108)  as TestSetDate,
            ss.ReabRegime_SprNum as ReabTestNameId,
            ss.ReabRegime_Name as ReabTestName,
            dd.ReabRegime_SprNum as ReabTestValueId,
            dd.ReabRegime_Name as ReabTestValue,
            dd.ReabRegime_SprWeight  as ReabTestWeight,
            tt.MedPersonal_iid  as MedPersonal_iid,
            tt.Lpu_iid  as Lpu_iid
	   from r2.ReabEvent r with(nolock), r2.ReabResultTest tt with(nolock),r2.ReabRegime ss,r2.ReabRegime dd with(nolock)
       where r.Person_id = :Person_id
        and r.ReabDirectType_id = :DirectType_id
        and r.ReabStageType_id = :StageType_id
         and tt.ReabEvent_id = r.ReabEvent_id and ss.ReabRegime_Code = 200 and dd.ReabRegime_Code = 201 and tt.ReabResultTest_Parameter = ss.ReabRegime_SprNum
         and tt.ReabResultTest_Value = dd.ReabRegime_SprNum and tt.ReabResultTest_Deleted = 1
         order by tt.ReabResultTest_setDT desc";


		sql_log_message('error', 'Search_model getListTestUserReab: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);

		//echo getDebugSql($query, $params);
		//exit;

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка измерений ЧСС (по профилю и этапу) для конкретного пациента
	 */
	function getListHeartRateUserReab($data) {

		$params = array(
				'Person_id' => $data['Person_id'],
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id']
		);


		$query = "select 
            tt.ReabHeartRate_id,
            CONVERT(varchar(10), tt.ReabHeartRate_setDT, 104) + ' ' + CONVERT(varchar(5), tt.ReabHeartRate_setDT, 108)  as HeartRate_setDate,
            tt.ReabHeartRate_peace as ReabHeartRate_peace,
            tt.ReabHeartRate_max as ReabHeartRate_max,
            tt.MedPersonal_iid  as MedPersonal_iid,
            tt.Lpu_iid  as Lpu_iid
       from r2.ReabEvent r with(nolock), r2.ReabHeartRate tt with(nolock)
       where r.Person_id = :Person_id
        and r.ReabDirectType_id = :DirectType_id
        and r.ReabStageType_id = :StageType_id
         and tt.ReabEvent_id = r.ReabEvent_id and  tt.ReabHeartRate_Deleted = 1
         order by tt.ReabHeartRate_setDT desc";

		//  sql_log_message('error', 'Search_model getListTestUserReab: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);

		//echo getDebugSql($query, $params);
		//exit;

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение диагнозов для анкеты(Травмвтология - 1 этап)
	 */
	function getDiagPerson($data) {

		$filter = $data['DiagMKB'];
		$queryParams = array(
				'Person_id' => $data['Person_id']
		);

		//$queryParams = array(
		//'Person_id' => '2549203'
		//);


		$query = "
               Declare
                @Person_id bigint = :Person_id
                
               Select top 10
	       RTRIM(ISNULL(dd.Diag_Code, '')) as Diag_CodeReab,
               RTRIM(ISNULL(dd.Diag_Name, '')) as Diag_Name,
			CONVERT(varchar(10),  tt.diagDate, 120) as diagDate,
               tt.diagDate as diagDate1
                from  r2.fn_ReabGetDiagPerson (@Person_id)   as tt,
			    dbo.Diag  dd with (nolock)
				where  dd.Diag_id = tt.DiagId and {$filter}
   order by dd.Diag_Code
    ";
		//sql_log_message('error', 'Grid7: ', getDebugSql($query, $queryParams));

		$result = $this->db->query($query, $queryParams);

		//$dbrep = $this->load->database('testUfa', true); 

		//$result = $dbrep->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение шаблона анкеты
	 */
	function CreateAnketa($data) {

		$params = array(
				'DirectType_id' => $data['DirectType_id'],
				'StageType_id' => $data['StageType_id']
		);

		$query = "select  tt.ReabTemplate_id as id, tt.ReabGroupQuestion_id as Group_id, gq.ReabGroupQuestion_Name as Group_Name, tt.ReabTemplate_ParameterNum as Parameter_id, 
                          tt.ReabTemplate_ParameterType as Elem_Type, tt.ReabTemplate_ParameterName as Parameter_Name,tt.ReabTemplate_Code as Spr_Cod, 
		          tt.ReabTemplate_IsGlobal as Global,tt.ReabTemplate_IsSumm as PriznSumm, tt.ReabTemplate_Num as Number,tt.ReabTemplate_Join as TemplJoin
                 from r2.ReabTemplate tt with(nolock),r2.ReabGroupQuestion gq with(nolock)
                 where tt.ReabDirectType_id = :DirectType_id and tt.ReabStageType_id = :StageType_id and tt.ReabTemplate_Deleted = 1 and tt.ReabTemplate_Num is not null
                        and tt.ReabGroupQuestion_id = gq.ReabGroupQuestion_id
                 order by tt.ReabTemplate_Num";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Для заполнения combo Cправочников(Всех)
	 */
	function ReabSpr11($aparam) {
		$params = array();

		$query = " select tt.ReabRegime_Name as SprName,
                            tt.ReabRegime_SprNum as ReabSpr_Elem_id,
                            tt.ReabRegime_Level as ReabSpr_Level,
                            tt.ReabRegime_SprWeight as ReabSpr_Elem_Weight,
		            tt.ReabRegime_Code as ReabSpr_Cod,
                            tt.ReabRegime_Group as ReabSpr_Group
                            From r2.ReabRegime tt with(nolock)
                 Where tt.ReabRegime_Code < 200
                 order by tt.ReabRegime_Code,tt.ReabRegime_Group,tt.ReabRegime_SprNum";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение всех шапок анкет по профилю
	 */
	function getListScalesDirectCurrentUser($aparam) {
		//echo "$aparam";
		//echo '<pre>' . print_r($aparam, 1) . '</pre>'; 
		$params = array(
				'Person_id' => $aparam['Person_id'],
				'DirectType_id' => $aparam['DirectType_id']
		);

		$query = "select rr.ReabQuestion_id as ReabAnketa_id,
						t.ReabStageType_id as StageType_id,
						t.ReabOutCause_id as ReabRegister_OutCause,
						st.ReabOutCause_Name as ReabOutCauseName,
                         t.ReabEvent_setDate as ReabRegister_setDate,
		                 t.ReabEvent_disDate as ReabRegister_disDate,
                         gg.ReabStageType_SysNick as StageTypeSysNick,
                         rr.ReabQuestion_Potent as ReabPotent,
                         CONVERT(varchar(10), rr.ReabQuestion_Date, 104) as ReabAnketa_Data,
                         rr.MedPersonal_iid,
                         rr.Lpu_iid
                 FROM r2.ReabEvent t with(nolock) 
			join r2.ReabQuestion  rr with(nolock) on  t.ReabEvent_id = rr.ReabEvent_id
			left join r2.ReabOutCause st on t.ReabOutCause_id = st.ReabOutCause_id
                        join r2.ReabStageType gg on gg.ReabStageType_id = t.ReabStageType_id
                 where t.Person_id = :Person_id and 
                       t.ReabDirectType_id = :DirectType_id and
                       t.ReabEvent_delDT is null and
                       rr.ReabQuestion_Deleted = 1 
                 order by rr.ReabQuestion_Date desc ";

		//sql_log_message('error', 'getListScalesDirectCurrentUser exec query: ', getDebugSql($query, $params));
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *  Получение шапок анкет
	 */
	function headAnketa($aparam)
	{
		//          echo "$aparam";
		//         echo '<pre>' . print_r($aparam, 1) . '</pre>'; 
		$params = array(
				'Person_id' => $aparam['Person_id'],
				'DirectType_id' => $aparam['DirectType_id'],
				'StageType_id' => $aparam['StageType_id']
		);

		$query = "select rr.ReabQuestion_id as ReabAnketa_id,
                         t.ReabStageType_id as StageType_id,
                        isnull(t.ReabOutCause_id,0)as ReabRegister_OutCause,
						 st.ReabOutCause_Name as ReabOutCauseName,
                         t.ReabEvent_setDate as ReabRegister_setDate,
		                 t.ReabEvent_disDate as ReabRegister_disDate,
                         gg.ReabStageType_SysNick as StageTypeSysNick,
                         rr.ReabQuestion_Potent as ReabPotent,
                         rr.ReabQuestion_Date as ReabAnketa_Data,
                         rr.MedPersonal_iid,
                         rr.Lpu_iid
                 FROM r2.ReabEvent t with(nolock) 
			join r2.ReabQuestion  rr with(nolock) on  t.ReabEvent_id = rr.ReabEvent_id
			left join r2.ReabOutCause st on t.ReabOutCause_id = st.ReabOutCause_id
                        join r2.ReabStageType gg on gg.ReabStageType_id = t.ReabStageType_id
                 where t.Person_id = :Person_id and 
                       t.ReabDirectType_id = :DirectType_id and 
                       t.ReabStageType_id = :StageType_id and 
                       t.ReabEvent_delDT is null and
                       rr.ReabQuestion_Deleted = 1 
                 order by rr.ReabQuestion_Date desc ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение идентификактора анкеты на дату
	 */
	function loadAnketa($aparam) {
		// echo 'Anketa1' ;
		// echo '<pre>' . print_r($aparam, 1) . '</pre>';  
		$params = array(
				'Person_id' => $aparam['Person_id'],
				'DirectType_id' => $aparam['DirectType_id'],
				'StageType_id' => $aparam['StageType_id'],
				'DateAnketa' => $aparam['DateAnketa']
		);

		$query = "select f.ReabQuestion_id as ReabAnketa_id
					from r2.ReabEvent r with(nolock),r2.ReabQuestion f with(nolock)
					where r.Person_id = :Person_id and 
                        r.ReabDirectType_id = :DirectType_id and
                        f.ReabQuestion_Date = :DateAnketa and
                        r.ReabStageType_id = :StageType_id and r.ReabEvent_Deleted = 1 and r.ReabEvent_id = f.ReabEvent_id and f.ReabQuestion_Deleted = 1 ";

		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'loadAnketa: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Вытягиваем тело анкеты
	 */
	function bodyAnketa($aparam) {
		$params = array(
				'ReabQuestion_id' => $aparam['ReabQuestion_id']
		);
		$query = "select  dd.ReabQuestionParam_Num as Param,
                         dd.ReabQuestionParam_Data as DataAnketa,
						 dd.ReabTemplate_Id
						  
                  FROM r2.ReabQuestionParam dd with(nolock)
                  where dd.ReabQuestion_id = :ReabQuestion_id
                ";
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Вытягиваем данные для Gridов
	 */
	function AnketaGrid($aparam) {
		$filter = $aparam['cKod'];
		// echo '$aparam' ;
		// echo '<pre>' . print_r($aparam, 1) . '</pre>';

		$query = " select ff.Diag_Code,ff.Diag_Name,ff.Diag_id
             from dbo.diag ff with(nolock)
             where ff.Diag_Code in ({$filter})";
		// sql_log_message('error', 'AnketaGrid: ', getDebugSql($query, $params));
		//sql_log_message('error', 'AnketaGrid: '.$filter, getDebugSql($query));

		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Контроль наличия шкалы на указанную дату по указанному критерию (Пока для Лекена)
	 */
	function contrScale($aparam) {
		$arr_param = explode(";", $aparam['ReabScaleParameter']);
		$arr_param1 = explode("-", $arr_param[0]);
		$arr_param2 = explode("-", $arr_param[1]);
		//        echo "$arr_param1";
		//        echo '<pre>' . print_r($arr_param1, 1) . '</pre>';
		//         echo "$arr_param2";
		//        echo '<pre>' . print_r($arr_param2, 1) . '</pre>';
		$query = "select count(*) as nKol
                  from r2.ReabScaleCondit ss with(nolock), dbo.ScaleType gg with(nolock)
                  where cast(ss.ReabScaleCondit_setDT as date ) = cast('{$aparam['ReabScale_setDate']}' as date)
                      and SUBSTRING(ss.ReabScaleCondit_ScaleParameter, 3,1) = '{$arr_param1[1]}'
                      and SUBSTRING(ss.ReabScaleCondit_ScaleParameter, 7,1) = '{$arr_param2[1]}'
					 and gg.ScaleType_SysNick =  '{$aparam['Scale_SysNick']}' 
					 and ss.Person_id = '{$aparam['Person_id']}'     and gg.ScaleType_id = ss.ScaleType_id and ss.ReabScaleCondit_Deleted = 1";
		//sql_log_message('error', 'contrScale: ', getDebugSql($query));

		$result = $this->db->query($query);
		//          echo '$result' ;
		//        echo '<pre>' . print_r($result, 1) . '</pre>';
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Возвращает данные для дерева справочника ICF
	 */
	function getICFTreeData($data)
	{
		$params = array();
		$where = '';
		$subselect = '';
		if ($data['node'] == 'root')
		{
			if ($data['ICF_code'] == 'null' && $data['ICF_code_filter'] == 'null' && $data['ICF_Name_filter'] == 'null')
			{
				$where = ' and D0.ICF_pid is null';
			} else
			{
				if($data['ICF_code_filter'] == 'null' && $data['ICF_Name_filter'] == 'null')
				{
					$filter = $data['ICF_code'];
					$where = " and D0.ICF_pid is null  and d0.ICF_Code = '{$filter}'";
				}
				else
				{
					if ($data['ICF_code_filter'] != 'null') {
						$filter = $data['ICF_code_filter'];
						$where =  "  and d0.ICF_pid = (select min(D2.ICF_pid) FROM dbo.ICF D2 with(nolock)
			                   where D2.ICF_Code like '{$filter}%' and  CHARINDEX ('-',D2.ICF_Code) = 0) and  D0.ICF_Code like '{$filter}%'";
					}
					if ($data['ICF_Name_filter'] != 'null') {
						$filter = $data['ICF_Name_filter'];
						$subselect = "with ttt (ICF_id) as 
									(SELECT d0.ICF_id as ICF_pid
									  from dbo.ICF D0 with(nolock)
									  where d0.ICF_Name like '%{$filter}%'
									  and  CHARINDEX ('-',d0.ICF_Code) = 0
									   and d0.ICF_pid in  
									   (select D1.ICF_Pid FROM dbo.ICF D1 with(nolock)
										where D1.ICF_Name like '%{$filter}%' and  CHARINDEX ('-',D1.ICF_Code) = 0 group by d1.ICF_pid) )  ";
						$filter1 = $data['ICF_code'];
						$where = " and d0.ICF_Name like '%{$filter}%' and  CHARINDEX ('-',ICF_Code) = 0 and D0.ICF_Code like '{$filter1}%'
									and d0.ICF_pid in 
									(select D1.ICF_Pid FROM dbo.ICF D1 with(nolock)
									 where D1.ICF_Name like '%{$filter}%' and  CHARINDEX ('-',D1.ICF_Code) = 0 group by d1.ICF_pid)
									and not Exists (select 1 from ttt where d0.ICF_pid = ttt.ICF_id )";
						if ($data['ICF_code_filter'] != 'null')
						{
							$filter = $data['ICF_code_filter'];
							$where = $where . " and  D0.ICF_Code like '{$filter}%'";
						}
					}
				}
				$filter = $data['ICF_code'];
				$params['ICF_code'] = $data['ICF_code'];
			}
		} else
		{

			$params['ICF_pid'] = $data['node'];
			$where = ' and D0.ICF_pid = :ICF_pid';

			if($data['ICF_Name_filter'] != 'null')
			{
				$filter = $data['ICF_Name_filter'];
				$where = $where . " and D0.ICF_Name like '%{$filter}%'";
			}
		}

		$query = $subselect."
			select
				D0.ICF_id,
				D0.ICF_Code,
				D0.ICF_Name,
				D0.ICF_pid,
				d0.ICF_Description,
				D0.ICF_id as id,
				(D0.ICF_Code +' '+D0.ICF_Name) as text,
				(case when D0.ICF_IsChild = 2 then 0 else 1 end) as leaf
			from dbo.ICF D0 with(nolock)
			where (1=1) 
				";
		$query .= $where;
		$query .= ' order by D0.ICF_id';

		//sql_log_message('error', 'getICFTreeData: ', getDebugSql($query));
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Работа с таблицей ReabICFRating (Оценка состояния здоровья по ICF - удаление)
	 */
	function DeleteICFRating($InParam) {
		$params = array(
				'Person_id' => $InParam['Person_id'],
				'ReabEvent_id' => $InParam['ReabEvent_id'],
				'pmUser_id' => $InParam['pmUser_id'],
				'ReabICFRating_id' => $InParam['ReabICFRating_id']
		);
		$query = "declare 
				@Person_id bigint,
				@ReabEvent_id bigint ,
				@ReabICFRating_id bigint,
				@pmUser_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000)
          exec r2.p_ReabICFRating_del
				@ReabICFRating_id = :ReabICFRating_id,
				@Person_id = :Person_id,
				@ReabEvent_id = :ReabEvent_id,
				@pmUser_id =:pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'SaveICFRating: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Работа с таблицей ReabICFRating (Оценка состояния здоровья по ICF- добавление, редактирование)
	 */
	function SaveICFRating($InParam) {
		$params = array(
				'Person_id' => $InParam['Person_id'],
				'ReabEvent_id' => $InParam['ReabEvent_id'],
				'ICFRating_setDate' => $InParam['ICFRating_setDate'],
				'ICF_id' => $InParam['ICF_id'],
				'ICFSeverity_id' => $InParam['ICFSeverity_id'],
				'ICFNature_id' => $InParam['ICFNature_id'],
				'ICFLocalization_id' => $InParam['ICFLocalization_id'],
				'ReabICFRating_TargetRealiz' => $InParam['ReabICFRating_TargetRealiz'],
				'ReabICFRating_TargetCapasit' => $InParam['ReabICFRating_TargetCapasit'],
				'ICFRating_CapasitEval' => $InParam['ICFRating_CapasitEval'],
				'ICFEnvFactors_id' => $InParam['ICFEnvFactors_id'],
				'ReabICFRating_FactorsTarget' => $InParam['ReabICFRating_FactorsTarget'],
				'MedStaffFact_id' => $InParam['MedStaffFact_id'],
				'pmUser_id' => $InParam['pmUser_id'],
				'ReabICFRating_id'  => $InParam['ReabICFRating_id']
		);

		// echo '<pre>' . print_r($InParam['isButtonAdd'], 1) . '</pre>';

		if ($InParam['Func'] === 'add') {
			$query = "declare 
				@Person_id bigint,
				@ReabEvent_id bigint ,
				@ReabICFRating_setDate datetime,
				@ICF_id bigint ,
				@ICFSeverity_id bigint ,
				@ICFNature_id bigint ,
				@ICFLocalization_id bigint ,
				@ReabICFRating_TargetRealiz int,
				@ReabICFRating_TargetCapasit int,
				@ReabICFRating_CapasitEval int,
				@ICFEnvFactors_id int,
				@ReabICFRating_FactorsTarget int,
				@MedStaffFact_id bigint ,
				@pmUser_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000)
          exec r2.p_ReabICFRating_ins
				@Person_id = :Person_id,
				@ReabEvent_id = :ReabEvent_id,
				@ReabICFRating_setDate = :ICFRating_setDate,
				@ICF_id = :ICF_id,
				@ICFSeverity_id = :ICFSeverity_id,
				@ICFNature_id = :ICFNature_id,
				@ICFLocalization_id = :ICFLocalization_id,
				@ReabICFRating_TargetRealiz = :ReabICFRating_TargetRealiz,
				@ReabICFRating_TargetCapasit = :ReabICFRating_TargetCapasit,
				@ReabICFRating_CapasitEval = :ICFRating_CapasitEval,
				@ICFEnvFactors_id = :ICFEnvFactors_id,
				@ReabICFRating_FactorsTarget = :ReabICFRating_FactorsTarget,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}
		else {
			$query = "declare 
				@ReabICFRating_id bigint,
				@ReabICFRating_setDate datetime,
				@ICFSeverity_id bigint,
				@ICFLocalization_id bigint,
				@ReabICFRating_TargetRealiz bigint,
				@ReabICFRating_TargetCapasit bigint,
				@ReabICFRating_CapasitEval bigint,
				@ICFEnvFactors_id bigint,
				@ReabICFRating_FactorsTarget bigint,
				@pmUser_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000)
          exec r2.p_ReabICFRating_upd
				@ReabICFRating_id = :ReabICFRating_id,
				@ReabICFRating_setDate =:ICFRating_setDate ,
				@ICFSeverity_id = :ICFSeverity_id,
				@ICFLocalization_id = :ICFLocalization_id,
				@ReabICFRating_TargetRealiz =:ReabICFRating_TargetRealiz,
				@ReabICFRating_TargetCapasit =:ReabICFRating_TargetCapasit,
				@ReabICFRating_CapasitEval =:ICFRating_CapasitEval,
				@ICFEnvFactors_id =:ICFEnvFactors_id,
				@ReabICFRating_FactorsTarget = :ReabICFRating_FactorsTarget,
				@pmUser_id =:pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		}

		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'SaveICFRating: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка Оценок по МКФ для пациента по данному случаю
	 */
	function getListICF_Verdict($data) {
		$params = array(
				'Person_id' => $data['Person_id'],
				'ReabEvent_id' => $data['ReabEvent_id']
		);
		$filter = "and f.ICF_Code like '".$data['Domen']."%' ";

		$query = "select m.ReabICFRating_id as ReabICFRating_id,
							CONVERT(varchar(10),  m.ReabICFRating_setDate, 104) as ICFSetDate,
							f.ICF_Code as ICF_Code,
							f.ICF_Description as ICF_Description,
							f.ICF_Name as ICF_Name,
							m.MedStaffFact_id as MedStaffFact_id,
							gg.ICFSeverity_Code as ICF_EvalRealiz,
							tt.ICFSeverity_Code as ICF_TargetRealiz,
							hh.ICFSeverity_Code as ICF_EvalCapasit,
							kk.ICFSeverity_Code as ICF_TargetCapasit,
							dd.ICFEnvFactors_Code as ICFEnvFactors,
							eee.ICFEnvFactors_Code as ICFEnvFactorsTarget,
							nn.ICFNature_Name as ICFNature_Name,
							pp.ICFLocalization_Name as ICFLocalization_Name,
							m.ICFSeverity_id as ICF_EvalRealiz_id,
							m.ReabICFRating_TargetRealiz as ICF_TargetRealiz_id,
							m.ReabICFRating_CapasitEval as ICF_CapasitEval_id,
							m.ReabICFRating_TargetCapasit as ICF_TargetCapasit_id,
							m.ICFEnvFactors_id as ICF_EnvFactors_id,
							m.ReabICFRating_FactorsTarget as ICF_FactorsTarget_id,
							m.ICFNature_id as ICFNature_id,
							m.ICFLocalization_id as ICFLocalization_id,
							rr.Person_Fio as MedPersonalFIO,
							ee1.name as MedPersonalPost
				from r2.ReabICFRating M with(nolock)
				inner join dbo.ICF f with(nolock) on f.ICF_id = m.ICF_id
				left join dbo.ICFSeverity gg on gg.ICFSeverity_id = m.ICFSeverity_id
				left join dbo.ICFSeverity tt on tt.ICFSeverity_id = m.ReabICFRating_TargetRealiz
				left join dbo.ICFSeverity hh on hh.ICFSeverity_id = m.ReabICFRating_CapasitEval
				left join dbo.ICFSeverity kk on kk.ICFSeverity_id = m.ReabICFRating_TargetCapasit
				left join dbo.ICFEnvFactors dd on dd.ICFEnvFactors_id = m.ICFEnvFactors_id
				left join dbo.ICFEnvFactors eee on eee.ICFEnvFactors_id = m.ReabICFRating_FactorsTarget
				left join dbo.ICFNature nn on nn.ICFNature_id = m.ICFNature_id
				left join dbo.ICFLocalization pp on pp.ICFLocalization_id = m.ICFLocalization_id
				left join dbo.v_MedStaffFact rr on rr.MedStaffFact_id = m.MedStaffFact_id
				left join persis.post ee1 on ee1.id = rr.Post_id
				where m.Person_id = :Person_id and m.ReabEvent_id = :ReabEvent_id and m.ReabICFRating_Deleted = 1 ".$filter. "order by m.ReabICFRating_setDate,ICF_Code";

		//sql_log_message('error', 'getListICF_Verdict: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Контроль наличия оценки ICF на указанную дату по указанному домену
	 */
	function contrICF($aparam) {
		if ($aparam['Func'] == 'add') {
			$params = array(
					'Person_id' => $aparam['Person_id'],
					'ReabEvent_id' => $aparam['ReabEvent_id'],
					'ICFRating_setDate' => $aparam['ICFRating_setDate'],
					'ICF_id' => $aparam['ICF_id']
			);
			$query = "select  count(*) as nKol
					from r2.ReabICFRating M with(nolock)
					where m.Person_id = :Person_id and m.ReabEvent_id = :ReabEvent_id and m.ReabICFRating_Deleted = 1
					and m.ICF_id =  :ICF_id and m.ReabICFRating_setDate = :ICFRating_setDate ";
		} else {
			$params = array(
					'Person_id' => $aparam['Person_id'],
					'ReabEvent_id' => $aparam['ReabEvent_id'],
					'ICFRating_setDate' => $aparam['ICFRating_setDate'],
					'ReabICFRating_id' => $aparam['ReabICFRating_id']
			);
			$query = "select  count(*) as nKol
					from r2.ReabICFRating M with(nolock), r2.ReabICFRating H with(nolock)
					where m.Person_id = :Person_id and m.ReabEvent_id = :ReabEvent_id and m.ReabICFRating_Deleted = 1
					and m.ReabICFRating_id = :ReabICFRating_id
					and h.Person_id = m.Person_id and h.ReabEvent_id = m.ReabEvent_id and h.ReabICFRating_Deleted = 1
					and m.ReabICFRating_id <> h.ReabICFRating_id 
					and m.ICF_id =  h.ICF_id  
					and H.ReabICFRating_setDate = :ICFRating_setDate ";
		}

		//		sql_log_message('error', 'SaveICFRating: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);
		//          echo '$result' ;
		//        echo '<pre>' . print_r($result, 1) . '</pre>';
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Проверка парности измерений по МКФ
	 */
	function checkrecordsICF($params) {

		$params = array(
				'Person_id' => $params['Person_id'],
				'ReabEvent_id' => $params['ReabEvent_id']
		);

		$query = "SELECT icf.ICF_id as ICF_id
					FROM r2.ReabICFRating icf
					where icf.Person_id = :Person_id and icf.ReabEvent_id = :ReabEvent_id
						and icf.ReabICFRating_Deleted = 1
					group by icf.ICF_id
					having count(*) < 2";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка Оценок по МКФ пациента для реабилитационного диагноза (анкета)
	 */
	function getReabDiagICF($data) {
		$params = array(
				'Person_id' => $data['Person_id'],
				'ReabEvent_id' => $data['ReabEvent_id']
		);

		$query = "select  ROW_NUMBER () over (order by ff.ICF_id ) as vID, icf.ICF_Code as ICF_Code, icf.ICF_Name as ICF_Name, 
					convert(varchar(10), ff.setDate1, 104) as setDate1,
				case
					when ff.setDate1 = ff.setDate2 then null
					else  convert(varchar(10), ff.setDate2, 104) 
				end setDate2,
				case
				 when (SUBSTRING ( icf.ICF_Code ,1, 1 ) = 'b' or SUBSTRING ( icf.ICF_Code ,1, 1 ) = 's' or SUBSTRING ( icf.ICF_Code ,1, 1 ) = 'd') and ff.setDate1 < ff.setDate2 
						and ww3.ICFSeverity_id < 6 and ww2.ICFSeverity_id < 6 and ww3.ICFSeverity_id <= ww2.ICFSeverity_id
					then  'V'
				when (SUBSTRING ( icf.ICF_Code ,1, 1 ) = 'b' or SUBSTRING ( icf.ICF_Code ,1, 1 ) = 's' or SUBSTRING ( icf.ICF_Code ,1, 1 ) = 'd') and ff.setDate1 < ff.setDate2 
					and ww3.ICFSeverity_id < 6 and ww2.ICFSeverity_id < 6 and ww3.ICFSeverity_id > ww2.ICFSeverity_id
					then  '!'
				 else null
			   end result,

				ww2.ICFSeverity_id,
				ww3.ICFSeverity_id,
				ww2.ReabICFRating_FactorsTarget,ww3.ReabICFRating_FactorsTarget

			from (select  ww1.ICF_id as ICF_id, max(ww1.ReabICFRating_setDate) as setDate2, min(ww1.ReabICFRating_setDate) as setDate1
				  from r2.ReabICFRating ww1 with(nolock)
				  where ww1.Person_id = :Person_id and ww1.ReabEvent_id = :ReabEvent_id and ww1.ReabICFRating_Deleted = 1
				  group by ww1.ICF_id) ff
			inner join r2.ReabICFRating ww2 with(nolock) on  ww2.ICF_id =  ff.ICF_id and ww2.ReabICFRating_setDate = ff.setDate1
			inner join r2.ReabICFRating ww3 with(nolock) on  ww3.ICF_id =  ff.ICF_id and ww3.ReabICFRating_setDate = ff.setDate2
			inner join dbo.ICF icf with(nolock) on icf.ICF_id = ff.ICF_id 

			where  ww2.Person_id = :Person_id and ww2.ReabEvent_id = :ReabEvent_id and ww2.ReabICFRating_Deleted = 1
			  and ww3.Person_id = :Person_id and ww3.ReabEvent_id = :ReabEvent_id and ww3.ReabICFRating_Deleted = 1
			  and SUBSTRING ( icf.ICF_Code ,1, 1 ) <> 'e'
			  order by ff.ICF_id";

		//sql_log_message('error', 'getListICF_Verdict: ', getDebugSql($query, $params));

		$result = $this->db->query($query, $params);


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}
