<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugRequestProperty_model extends swModel {
	private $DrugRequestProperty_id;//DrugRequestProperty_id
	private $DrugRequestProperty_Name;//Справочник нормативных перечней
	private $DrugRequestPeriod_id;//Рабочий период списка
	private $PersonRegisterType_id;//Тип списка
	private $DrugFinance_id;//Источник финансирования
	private $DrugGroup_id;//Группа медикаментов
	private $Org_id;//Организация
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Получение параметра
	 */
	public function getDrugRequestProperty_id() { return $this->DrugRequestProperty_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestProperty_id($value) { $this->DrugRequestProperty_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestProperty_Name() { return $this->DrugRequestProperty_Name;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestProperty_Name($value) { $this->DrugRequestProperty_Name = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestPeriod_id() { return $this->DrugRequestPeriod_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestPeriod_id($value) { $this->DrugRequestPeriod_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getPersonRegisterType_id() { return $this->PersonRegisterType_id;}

	/**
	 * Установка параметра
	 */
	public function setPersonRegisterType_id($value) { $this->PersonRegisterType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugFinance_id() { return $this->DrugFinance_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugFinance_id($value) { $this->DrugFinance_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugGroup_id() { return $this->DrugGroup_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugGroup_id($value) { $this->DrugGroup_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getOrg_id() { return $this->Org_id;}

	/**
	 * Установка параметра
	 */
	public function setOrg_id($value) { $this->Org_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка параметра
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Загрузка данных
	 */
	function load() {
		$q = "
			select
				DrugRequestProperty_id, DrugRequestProperty_Name, DrugRequestPeriod_id, PersonRegisterType_id, DrugFinance_id, DrugGroup_id, Org_id
			from
				dbo.v_DrugRequestProperty with (nolock)
			where
				DrugRequestProperty_id = :DrugRequestProperty_id
		";
		$r = $this->db->query($q, array('DrugRequestProperty_id' => $this->DrugRequestProperty_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->DrugRequestProperty_id = $r[0]['DrugRequestProperty_id'];
				$this->DrugRequestProperty_Name = $r[0]['DrugRequestProperty_Name'];
				$this->DrugRequestPeriod_id = $r[0]['DrugRequestPeriod_id'];
				$this->PersonRegisterType_id = $r[0]['PersonRegisterType_id'];
				$this->DrugFinance_id = $r[0]['DrugFinance_id'];
				$this->DrugGroup_id = $r[0]['DrugGroup_id'];
				$this->Org_id = $r[0]['Org_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['Year']) && $filter['Year'] > 0) {
			$where[] = 'DATEPART(year,DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) = :Year';
			$p['Year'] = $filter['Year'];
		}
		if (isset($filter['DrugRequestProperty_id']) && $filter['DrugRequestProperty_id']) {
			$where[] = 'v_DrugRequestProperty.DrugRequestProperty_id = :DrugRequestProperty_id';
			$p['DrugRequestProperty_id'] = $filter['DrugRequestProperty_id'];
		}
		if (isset($filter['DrugRequestProperty_Name']) && $filter['DrugRequestProperty_Name']) {
			$where[] = 'v_DrugRequestProperty.DrugRequestProperty_Name = :DrugRequestProperty_Name';
			$p['DrugRequestProperty_Name'] = $filter['DrugRequestProperty_Name'];
		}
		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id']) {
			$where[] = 'v_DrugRequestProperty.DrugRequestPeriod_id = :DrugRequestPeriod_id';
			$p['DrugRequestPeriod_id'] = $filter['DrugRequestPeriod_id'];
		}
		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id']) {
			$where[] = 'v_DrugRequestProperty.PersonRegisterType_id = :PersonRegisterType_id';
			$p['PersonRegisterType_id'] = $filter['PersonRegisterType_id'];
		}
		if (isset($filter['DrugFinance_SysNick']) && !empty($filter['DrugFinance_SysNick'])) {
			$where[] = 'DrugFinance_id_ref.DrugFinance_SysNick = :DrugFinance_SysNick';
			$p['DrugFinance_SysNick'] = $filter['DrugFinance_SysNick'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT				
				v_DrugRequestProperty.DrugRequestProperty_id,
				v_DrugRequestProperty.DrugRequestProperty_Name,
				v_DrugRequestProperty.DrugRequestPeriod_id,
				v_DrugRequestProperty.PersonRegisterType_id,
				v_DrugRequestProperty.DrugFinance_id
				,DrugRequestPeriod_id_ref.DrugRequestPeriod_Name DrugRequestPeriod_Name,
				PersonRegisterType_id_ref.PersonRegisterType_Name PersonRegisterType_Name,
				DrugFinance_id_ref.DrugFinance_Name DrugFinance_Name,
				mnn_cnt.cnt as Mnn_Count,
				Org.Org_Name as DrugRequestProperty_OrgName,
				v_DrugRequestProperty.Org_id as DrugRequestProperty_Org
			FROM
				dbo.v_DrugRequestProperty WITH (NOLOCK)
				LEFT JOIN dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref WITH (NOLOCK) ON DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequestProperty.DrugRequestPeriod_id
				LEFT JOIN dbo.v_PersonRegisterType PersonRegisterType_id_ref WITH (NOLOCK) ON PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugRequestProperty.PersonRegisterType_id
				LEFT JOIN dbo.v_DrugFinance DrugFinance_id_ref WITH (NOLOCK) ON DrugFinance_id_ref.DrugFinance_id = v_DrugRequestProperty.DrugFinance_id
				left join dbo.v_Org Org with (nolock) on org.Org_id = v_DrugRequestProperty.Org_id
				outer apply (
					select count(DrugListRequest_id) as cnt
					from DrugListRequest with (nolock)
					where
						DrugRequestProperty_id = v_DrugRequestProperty.DrugRequestProperty_id
				) mnn_cnt
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_DrugRequestProperty_ins';
		if ( $this->DrugRequestProperty_id > 0 ) {
			$procedure = 'p_DrugRequestProperty_upd';
		}
		$q = "
			declare
				@DrugRequestProperty_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugRequestProperty_id = :DrugRequestProperty_id;
			exec dbo." . $procedure . "
				@DrugRequestProperty_id = @DrugRequestProperty_id output,
				@DrugRequestProperty_Name = :DrugRequestProperty_Name,
				@DrugRequestPeriod_id = :DrugRequestPeriod_id,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@DrugFinance_id = :DrugFinance_id,
				@DrugGroup_id = :DrugGroup_id,
				@Org_id = :Org_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugRequestProperty_id as DrugRequestProperty_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id,
			'DrugRequestProperty_Name' => $this->DrugRequestProperty_Name,
			'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
			'DrugFinance_id' => $this->DrugFinance_id,
			'DrugGroup_id' => $this->DrugGroup_id,
			'Org_id' => $this->Org_id,
			'pmUser_id' => $this->pmUser_id
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->DrugRequestProperty_id = $result[0]['DrugRequestProperty_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		//проверяем не используется ли список в заявке
		$q = "
			select
				dr.DrugRequest_Name
			from
				v_DrugRequest dr with (nolock)
			where
				dr.DrugRequestProperty_id = :DrugRequestProperty_id;
		";
		$request_data = $this->getFirstResultFromQuery($q, array(
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id
		));
		if (!empty($request_data)) {
			return array(array('Error_Msg' => "Данный список используется в заявке \"{$request_data}\". Удаление невозможно."));
		}

		//удаляем записи в таблицах DrugListRequestTorg и DrugListRequest
		$q = "
			delete from DrugListRequestTorg
			where
				DrugListRequest_id in (
					select DrugListRequest_id
					from DrugListRequest with (nolock)
					where DrugRequestProperty_id = :DrugRequestProperty_id
				);
			
			delete from DrugListRequest
			where DrugRequestProperty_id = :DrugRequestProperty_id;
		";
		$r = $this->db->query($q, array(
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id
		));
	
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_DrugRequestProperty_del
				@DrugRequestProperty_id = :DrugRequestProperty_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение из сериализованного массива
	 */
	function saveDrugListRequestFromJSON($data) {				  
		if (!empty($data['DrugListRequest_JsonData']) && $data['DrugRequestProperty_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['DrugListRequest_JsonData']);
			$dt = (array) json_decode($data['DrugListRequest_JsonData']);           
            
            foreach($dt as $record) {
				if ($record->state == 'add' || $record->state == 'edit') {
					if ($record->state == 'add') {
						$record->DrugListRequest_id = 0;
					}
                    
					$ins = $this->editDrugListRequest(array_merge((array)$record, array(
						'DrugRequestProperty_id' => $data['DrugRequestProperty_id'],
						'pmUser_id' => $data['pmUser_id']
					)));
                    
                    //Обновление порядкового номера записи
					if (!empty($record->DrugListRequest_Number) && !empty($ins[0]['DrugListRequest_id'])) {
						$this->DrugListRequestNumberUpdate(
							array(
								'DrugListRequest_id' => $ins[0]['DrugListRequest_id'],
								'DrugListRequest_Number' => $record->DrugListRequest_Number,
								'pmUser_id' => $data['pmUser_id']
							)
						);
					}
				} else if ($record->state == 'delete') {
					if (!empty($record->DrugListRequest_id)) {
						$this->deleteDrugListRequest($record->DrugListRequest_id);
					}
				}
			}               
		}
		
		return array(array('Error_Code' => '', 'Error_Msg' => ''));
	}

    /**
     *  Редактирования порядкового номера записи
     */ 
    function DrugListRequestNumberUpdate($data){       
        $params = array(
            'DrugListRequest_id'=>$data['DrugListRequest_id'],
            'DrugListRequest_Number'=>$data['DrugListRequest_Number'],
            'pmUser_id'=>$data['pmUser_id']
        );
        
        $query = "
			declare
			    @DrugListRequest_id bigint,
				@DrugListRequest_Number varchar(20),
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec dbo.p_DrugListRequest_updNumber

				@DrugListRequest_id = :DrugListRequest_id,
				@DrugListRequest_Number = :DrugListRequest_Number,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;        
        ";
        
		$result = $this->db->query($query, $params);


        //echo getDebugSql($query, $params);
        //exit;
     }

	/**
	 * Редактирование позиции
	 */
	function editDrugListRequest($data) {
		$procedure = 'p_DrugListRequest_ins';
		
		if ( $data['DrugListRequest_id'] > 0 ) {
			$procedure = 'p_DrugListRequest_upd';
		}
        
		$q = "
			declare
				@DrugListRequest_id bigint,
				@DrugListRequest_IsProblem bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @DrugListRequest_id = :DrugListRequest_id;
			set @DrugListRequest_IsProblem = (select YesNo_id from YesNo with (nolock) where YesNo_Code = :DrugListRequest_IsProblem);

			exec dbo." . $procedure . "
				@DrugListRequest_id = @DrugListRequest_id output,
				@DrugRequestProperty_id = :DrugRequestProperty_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@DrugListRequest_Price = :DrugListRequest_Price,
				@DrugTorgUse_id = :DrugTorgUse_id,
				@DrugListRequest_Code = null,
				@DrugListRequest_IsProblem = @DrugListRequest_IsProblem,
				@DrugListRequest_Comment = :DrugListRequest_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugListRequest_id as DrugListRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if (!isset($data['DrugListRequest_Price'])) {
			$data['DrugListRequest_Price'] = null;
		}
		if (!isset($data['DrugTorgUse_id']) || $data['DrugTorgUse_id'] <= 0) {
			$data['DrugTorgUse_id'] = null;
		}
		if (empty($data['DrugListRequest_Comment'])) {
			$data['DrugListRequest_Comment'] = null;
		}

		$r = $this->db->query($q, $data);

		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $DrugListRequest_id = $result[0]['DrugListRequest_id'];

			//Сохранения списка
			if ($DrugListRequest_id && !empty($data['DrugListRequestTorg_JsonData'])) {
				$dt = (array) json_decode($data['DrugListRequestTorg_JsonData']);

				foreach($dt as $record) {
					if ($record->state == 'add' || $record->state == 'edit') {
						if ($record->state == 'add')
							$record->DrugListRequestTorg_id = 0;
						$this->editDrugListRequestTorg(array_merge((array)$record, array(
							'DrugListRequest_id' => $DrugListRequest_id,
							'pmUser_id' => $data['pmUser_id']
						)));
					} else if ($record->state == 'delete') {
						if (isset($record->DrugListRequestTorg_id) && !empty($record->DrugListRequestTorg_id))
							$this->deleteDrugListRequestTorg($record->DrugListRequestTorg_id);
					}
				}
			}
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $data, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление позиции
	 */
	function deleteDrugListRequest($id) {
		if ($id > 0) {
			//удаляем запись из DrugListRequestTorg
			$q = "
				delete from DrugListRequestTorg
				where
					DrugListRequest_id = :DrugListRequest_id
			";
			$r = $this->db->query($q, array(
				'DrugListRequest_id' => $id
			));

			//удаляем запись из DrugListRequest
			$q = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec dbo.p_DrugListRequest_del
					@DrugListRequest_id = :DrugListRequest_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$r = $this->db->query($q, array(
				'DrugListRequest_id' => $id
			));
			if ( is_object($r) ) {
				return $r->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка списка позиций
	 */
	function loadDrugListRequestList($filter) {

    	$q = "
			select
             -- select
				v_DrugListRequest.DrugListRequest_id,
				v_DrugListRequest.DrugRequestProperty_id,
				v_DrugListRequest.DrugComplexMnn_id,
				DrugComplexMnnCode.DrugComplexMnnCode_Code as DrugComplexMnn_Code,
				DrugComplexMnn_id_ref.DrugComplexMnn_RusName,
				ActMatters.STRONGGROUPID,
				ActMatters.NARCOGROUPID,
				v_DrugListRequest.DrugListRequest_Price,
				v_DrugListRequest.DrugTorgUse_id,
				DrugTorgUse_id_ref.DrugTorgUse_Name,
				v_DrugListRequest.DrugListRequest_Code,
				DrugTorgUse_id_ref.DrugTorgUse_Name DrugTorgUse_Name,
				isnull(isProblem.YesNo_Code, 0) as DrugListRequest_IsProblem,
                v_DrugListRequest.DrugListRequest_Number,
                v_DrugListRequest.DrugListRequest_Comment,
				replace(replace((
					select cast(dlrt.TRADENAMES_ID as varchar)+',' as 'data()'
					from DrugListRequestTorg dlrt with (nolock)
					where
						dlrt.DrugListRequest_id = v_DrugListRequest.DrugListRequest_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as TRADENAMES_ID_list,
				replace(replace((
					select
						t.NAME+',' as 'data()'
					from
						DrugListRequestTorg dlrt with (nolock)
						left join rls.v_TRADENAMES t with (nolock) on t.TRADENAMES_ID = dlrt.TRADENAMES_ID
					where
						dlrt.DrugListRequest_id = v_DrugListRequest.DrugListRequest_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as TRADENAMES_NAME_list,
				(
					select distinct
						','+SUBSTRING(ca.NAME, 1, CHARINDEX(' ',ca.NAME)-1) as 'data()'
					from
						rls.PREP_ACTMATTERS pam with (nolock)
						left join rls.PREP_ATC pa with (nolock) on pa.PREPID = pam.PREPID
						inner join rls.CLSATC ca with (nolock) on ca.CLSATC_ID =pa.UNIQID
					where
						pam.MATTERID = DrugComplexMnnName.ActMatters_id
					for xml path('')
				) as ATX_CODE_list,
				(
					select top 1
						cn.NAME
					from
						rls.v_Drug d with (nolock)
						left join rls.v_prep p with (nolock) on p.Prep_id = d.DrugPrep_id
						left join rls.CLSNTFR cn with (nolock) on cn.CLSNTFR_ID = p.NTFRID
					where
						d.DrugComplexMnn_id = DrugComplexMnn_id_ref.DrugComplexMnn_id
				) as NTFR_Name,
				DrugComplexMnnName.DrugComplexMnnName_Name,
				CLSDRUGFORMS.NAME as ClsDrugForms_Name,
				DrugComplexMnnDose.DrugComplexMnnDose_Name,
				DrugComplexMnnFas.DrugComplexMnnFas_Name
            -- end select
			from
            -- from
				dbo.v_DrugListRequest with (nolock)
				left join rls.v_DrugComplexMnn DrugComplexMnn_id_ref with (nolock) on DrugComplexMnn_id_ref.DrugComplexMnn_id = v_DrugListRequest.DrugComplexMnn_id
				left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = DrugComplexMnn_id_ref.DrugComplexMnnName_id
				left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = DrugComplexMnn_id_ref.DrugComplexMnnDose_id
				left join rls.DrugComplexMnnFas with (nolock) on DrugComplexMnnFas.DrugComplexMnnFas_id = DrugComplexMnn_id_ref.DrugComplexMnnFas_id
				left join rls.CLSDRUGFORMS with (nolock) on CLSDRUGFORMS.CLSDRUGFORMS_ID = DrugComplexMnn_id_ref.CLSDRUGFORMS_ID
				left join rls.v_ACTMATTERS ActMatters with (nolock) on ActMatters.ACTMATTERS_ID = DrugComplexMnnName.ActMatters_id
				left join dbo.v_DrugTorgUse DrugTorgUse_id_ref with (nolock) on DrugTorgUse_id_ref.DrugTorgUse_id = v_DrugListRequest.DrugTorgUse_id
				left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = v_DrugListRequest.DrugListRequest_IsProblem
				outer apply (
					select top 1
						v_DrugComplexMnnCode.DrugComplexMnnCode_Code
					from
						rls.v_DrugComplexMnnCode with (nolock)
					where
						v_DrugComplexMnnCode.DrugComplexMnnCode_Code is not null and
						v_DrugComplexMnnCode.DrugComplexMnn_id = v_DrugListRequest.DrugComplexMnn_id
					order by
						v_DrugComplexMnnCode.DrugComplexMnnCode_id
				) DrugComplexMnnCode
            -- end from
			where
            -- where
				DrugRequestProperty_id = :DrugRequestProperty_id
            -- end where
            order by
            -- order by
            DrugRequestProperty_id
            -- end order by
		";


        /*
        $q = "
            select
            -- select
            *
            -- end select
            from
            -- from
                [r2].[v_DrugListRequestList]
            -- end from
            where
            -- where
                DrugRequestProperty_id = :DrugRequestProperty_id
            -- end where
            order by
            -- order by
                DrugRequestProperty_id
            -- end order by
        ";
        */

		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

        /**
		$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);

        //echo getDebugSql($q, $filter);
        //exit;

		$result_count = $this->db->query(getCountSQLPH($q), $filter);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;

			return $response;
		}
		else
		{
			return false;
		}
        */

	}

	/**
	 * Редактирование позиции списка торговых наименований
	 */
	function editDrugListRequestTorg($data) {
		$procedure = 'p_DrugListRequestTorg_ins';

		if ( $data['DrugListRequestTorg_id'] > 0 ) {
			$procedure = 'p_DrugListRequestTorg_upd';
		}
		$q = "
			declare
				@DrugListRequestTorg_id bigint,
				@DrugListRequestTorg_IsProblem bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @DrugListRequestTorg_id = :DrugListRequestTorg_id;
			set @DrugListRequestTorg_IsProblem = (select YesNo_id from YesNo with (nolock) where YesNo_Code = :DrugListRequestTorg_IsProblem);

			exec dbo." . $procedure . "
				@DrugListRequestTorg_id = @DrugListRequestTorg_id output,
				@DrugListRequest_id = :DrugListRequest_id,
				@TRADENAMES_id = :TRADENAMES_id,
				@Drug_id = NULL,
				@OrgFarmacyPrice_Min = :OrgFarmacyPrice_Min,
				@OrgFarmacyPrice_Max = :OrgFarmacyPrice_Max,
				@DrugRequestPrice_Min = :DrugRequestPrice_Min,
				@DrugRequestPrice_Max = :DrugRequestPrice_Max,
				@DrugRequest_Price = :DrugRequest_Price,
				@DrugListRequestTorg_IsProblem = @DrugListRequestTorg_IsProblem,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugListRequestTorg_id as DrugListRequestTorg_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		if ($data['TRADENAMES_id'] <= 0)
			$data['TRADENAMES_id'] = null;

		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $data, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление позиции списка торговых наименований
	 */
	function deleteDrugListRequestTorg($id) {
		if ($id > 0) {
			//удаляем запись из DrugListRequestTorg
			$q = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec dbo.p_DrugListRequestTorg_del
					@DrugListRequestTorg_id = :DrugListRequestTorg_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$r = $this->db->query($q, array(
				'DrugListRequestTorg_id' => $id
			));
			if ( is_object($r) ) {
				return $r->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка списка торговых наименований
	 */
	function loadDrugListRequestTorgList($filter) {
		$q = "
			select
				v_DrugListRequestTorg.DrugListRequestTorg_id,
				v_DrugListRequestTorg.DrugListRequest_id,
				v_DrugListRequestTorg.TRADENAMES_id,
				TRADENAMES.NAME as TRADENAMES_Name,
				v_DrugListRequestTorg.Drug_id,
				v_DrugListRequestTorg.OrgFarmacyPrice_Min,
				v_DrugListRequestTorg.OrgFarmacyPrice_Max,
				v_DrugListRequestTorg.DrugRequestPrice_Min,
				v_DrugListRequestTorg.DrugRequestPrice_Max,
				v_DrugListRequestTorg.DrugRequest_Price,
				isnull(isProblem.YesNo_Code, 0) as DrugListRequestTorg_IsProblem
			from
				dbo.v_DrugListRequestTorg with (nolock)
				left join rls.TRADENAMES with (nolock) on TRADENAMES.TRADENAMES_id = v_DrugListRequestTorg.TRADENAMES_id
				left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = v_DrugListRequestTorg.DrugListRequestTorg_IsProblem
			where
				DrugListRequest_id = :DrugListRequest_id;
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для синхронизации
	 */
	function loadSynchronizeList($filter) {
	    $inline_input = false; //признак поточного ввода
        $params = array();

        //https://redmine.swan.perm.ru/issues/73492
        //если один из 4-х текстовых фильтров не пуст, значит функция используется для поточного ввода
        if (!empty($filter['DrugComplexMnnName_Name'])) {
            $params[] = "dcmn.DrugComplexMnnName_Name like :DrugComplexMnnName_Name";
            $filter['DrugComplexMnnName_Name'] = $filter['DrugComplexMnnName_Name'].'%';
            $inline_input = true;
        }

        if (!empty($filter['CLSDRUGFORMS_fullname'])) {
            $params[] = "cdf.fullname like :CLSDRUGFORMS_fullname+'%' ";
            $filter['CLSDRUGFORMS_fullname'] = $filter['CLSDRUGFORMS_fullname'].'%';
            $inline_input = true;
        }

        if (!empty($filter['DrugComplexMnnDose_Name'])) {
            $params[] = "dcmd.DrugComplexMnnDose_Name like :DrugComplexMnnDose_Name";
            $filter['DrugComplexMnnDose_Name'] = $filter['DrugComplexMnnDose_Name'].'%';
            $inline_input = true;
        }

        if (!empty($filter['DrugComplexMnnFas_Name'])) {
            $params[] = "dcmf.DrugComplexMnnFas_Name like :DrugComplexMnnFas_Name";
            $filter['DrugComplexMnnFas_Name'] = '%№ '.$filter['DrugComplexMnnFas_Name'].'%';
            $inline_input = true;
        }

        if ($inline_input) { //поточный ввод
            $params[] = "dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug with (nolock))";
            $paramsString = implode(" and ", $params);

            $query = "
                declare
                    @DrugNormativeList_id int = :DrugNormativeList_id;

                select
                    ppp.DrugComplexMnn_id,
                    ppp.DrugComplexMnn_RusName,
                    ppp.DrugComplexMnnName_Name,
                    ppp.FULLNAME,
                    ppp.namE,
                    ppp.DrugComplexMnnDose_Name,
                    ppp.DrugComplexMnnFas_Name,
                    ppp.state,
                    (
                        case
                        when state = 'add' then 'Добавить'
                        when state = 'delete' then 'Удалить'
                        end
                    ) as action_name
                from
                    (
                        select
                            isnull(dcm.DrugComplexMnn_id, dlr.DrugComplexMnn_id) as DrugComplexMnn_id,
                            (
                                case
                                when dcm.DrugComplexMnn_id is not null and dlr.DrugComplexMnn_id is null then 'add'
                                when dcm.DrugComplexMnn_id is not null and dlr.DrugComplexMnn_id is not null then null
                                when dcm.DrugComplexMnn_id is null and dlr.DrugComplexMnn_id is not null then 'delete'
                                end
                            ) as state,
                            dcm.DrugComplexMnn_pid,
                            dcm.DrugComplexMnn_RusName,
                            dcmn.DrugComplexMnnName_Name,
                            dcmn.ActMatters_id,
                            dcmd.DrugComplexMnnDose_Name,
                            dcmf.DrugComplexMnnFas_Name,
                            cdf.FULLNAME,
                            cdf.NAME
                        from
                            rls.DrugComplexMnn dcm with (nolock)
                            full outer join (select DrugComplexMnn_id from rls.DrugComplexMnn with (nolock) where DrugComplexMnn_id in (NULL)) dlr on dlr.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                            left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                            left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                            left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                            left join rls.CLSDRUGFORMS cdf with (nolock) on dcm.CLSDRUGFORMS_ID=cdf.CLSDRUGFORMS_ID
                        where
                            -- ставим условия по фильтрам
                            {$paramsString}
                    ) ppp
                where
                    ppp.state is not null
                    or not exists (select top 1 DrugComplexMnn_pid from rls.DrugComplexMnn with (nolock) where DrugComplexMnn_pid is not null and ppp.DrugComplexMnn_id = DrugComplexMnn_pid)
                order by
                    ppp.state,
                    ppp.DrugComplexMnn_RusName;
            ";
        } else {
                $query = "
                declare
                    @DrugNormativeList_id int = :DrugNormativeList_id

                select
                    ppp.DrugComplexMnn_id,
                    DrugComplexMnnCode.DrugComplexMnnCode_Code as DrugComplexMnn_Code,
                    ppp.state,
                    (
                        case
                            when state = 'add' then 'Добавить'
                            when state = 'delete' then 'Удалить'
                        end
                    ) as action_name,
                    ppp.DrugComplexMnn_RusName,
                    ppp.DrugComplexMnnName_Name,
                    ppp.DrugComplexMnnDose_Name,
                    ppp.DrugComplexMnnFas_Name,
                    (
                        select distinct
                            ','+SUBSTRING(ca.NAME, 1, CHARINDEX(' ',ca.NAME)-1) as 'data()'
                        from
                            rls.PREP_ACTMATTERS pam with (nolock)
                            left join rls.PREP_ATC pa with (nolock) on pa.PREPID = pam.PREPID
                            inner join rls.CLSATC ca with (nolock) on ca.CLSATC_ID =pa.UNIQID
                        where
                            pam.MATTERID = ppp.ActMatters_id
                        for xml path('')
                    ) as ATX_CODE_list,
                    (
                        select top 1
                            cn.NAME
                        from
                            rls.v_Drug d with (nolock)
                            left join rls.v_prep p with (nolock) on p.Prep_id = d.DrugPrep_id
                            left join rls.CLSNTFR cn with (nolock) on cn.CLSNTFR_ID = p.NTFRID
                        where
                            d.DrugComplexMnn_id = ppp.DrugComplexMnn_id
                    ) as NTFR_Name,
                    am.STRONGGROUPID,
                    am.NARCOGROUPID,
                    cdf.NAME as ClsDrugForms_Name,
                    cdf.FULLNAME
                from (
                    select
                        isnull(dcm.DrugComplexMnn_id, dlr.DrugComplexMnn_id) as DrugComplexMnn_id,
                        (case
                            when dcm.DrugComplexMnn_id is not null and dlr.DrugComplexMnn_id is null then 'add'
                            when dcm.DrugComplexMnn_id is not null and dlr.DrugComplexMnn_id is not null then null
                            when dcm.DrugComplexMnn_id is null and dlr.DrugComplexMnn_id is not null then 'delete'
                        end
                        ) as state,
                        cm.DrugComplexMnn_RusName,
                        dcmn.DrugComplexMnnName_Name,
                        dcmn.ActMatters_id,
                        dcmd.DrugComplexMnnDose_Name,
                        dcmf.DrugComplexMnnFas_Name,
                        cm.CLSDRUGFORMS_ID
                    from (
                        select * from (
                            select distinct
                                dnls.DrugNormativeListSpecMNN_id,
                                dnlsfl.DrugNormativeListSpecForms_id
                            from
                                DrugNormativeListSpec dnls with (nolock)
                                left join DrugNormativeListSpecFormsLink dnlsfl with (nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
                            where
                                @DrugNormativeList_id is null or
                                dnls.DrugNormativeList_id = @DrugNormativeList_id
                        ) p
                        outer apply (
                            select
                                COUNT(dnlsfl.DrugNormativeListSpecForms_id) cnt
                            from
                                v_DrugNormativeListSpec dnls with (nolock)
                                left join DrugNormativeListSpecFormsLink dnlsfl with (nolock) on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
                            where
                                (
                                    @DrugNormativeList_id is null or
                                    dnls.DrugNormativeList_id = @DrugNormativeList_id
                                ) and
                                dnls.DrugNormativeListSpecMNN_id = p.DrugNormativeListSpecMNN_id
                        ) forms_cnt
                        where
                            p.DrugNormativeListSpecForms_id is not null or
                            (p.DrugNormativeListSpecForms_id is null and forms_cnt.cnt <= 0)
                    ) pp
                    inner join rls.DrugComplexMnn dcm with (nolock) on exists(
                        select DrugComplexMnnName.ActMatters_id from rls.DrugComplexMnnName with (nolock)
                        where DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id and DrugComplexMnnName.ActMatters_id = pp.DrugNormativeListSpecMNN_id
                        )
                        and (pp.DrugNormativeListSpecForms_id is null or dcm.CLSDRUGFORMS_ID = pp.DrugNormativeListSpecForms_id)
                    full outer join (
                        select DrugComplexMnn_id from rls.DrugComplexMnn with (nolock) where DrugComplexMnn_id in (".(!empty($filter['DrugComplexMnn_id_list']) ? $filter['DrugComplexMnn_id_list'] : "NULL").")
                    ) dlr on dlr.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                    left join rls.DrugComplexMnn cm with (nolock) on cm.DrugComplexMnn_id = dcm.DrugComplexMnn_id or cm.DrugComplexMnn_id = dlr.DrugComplexMnn_id
                    left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = cm.DrugComplexMnnName_id
                    left join rls.DrugComplexMnnDose dcmd  with (nolock) on dcmd.DrugComplexMnnDose_id = cm.DrugComplexMnnDose_id
                    left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = cm.DrugComplexMnnFas_id
                    where --исключаем мнн-родителей
                        dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug) and
                        (
                            dcm.DrugComplexMnn_pid is not null
                            or dcm.DrugComplexMnn_id not in (
                                select
                                    DrugComplexMnn_pid
                                from
                                    rls.DrugComplexMnn with (nolock)
                                where
                                    DrugComplexMnn_pid is not null
                            )
                        )
                ) ppp
                left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = ppp.ActMatters_id
                outer apply (
                    select top 1
                        v_DrugComplexMnnCode.DrugComplexMnnCode_Code
                    from
                        rls.v_DrugComplexMnnCode with (nolock)
                    where
                        v_DrugComplexMnnCode.DrugComplexMnnCode_Code is not null and
                        v_DrugComplexMnnCode.DrugComplexMnn_id = ppp.DrugComplexMnn_id
                    order by
                        v_DrugComplexMnnCode.DrugComplexMnnCode_id
                ) DrugComplexMnnCode
                left join rls.CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = ppp.CLSDRUGFORMS_ID
                where
                    ppp.state is not null
                order by
                    ppp.state, ppp.DrugComplexMnn_RusName;
            ";
        }

        //echo getDebugSql($q, $filter);
        //exit;
        $result = $this->db->query($query, $filter);

        if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка торговых наименований
	 */
	function loadTradenames($filter) {
		if (isset($filter['DrugComplexMnn_id']) && $filter['DrugComplexMnn_id'] > 0) {
			$q = "
				select distinct
					tn.TRADENAMES_ID,
					tn.NAME
				from
					rls.DrugComplexMnn dcm with (nolock)
					inner join rls.Drug d with (nolock) on d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					inner join rls.PREP p with (nolock) on p.Prep_id = d.DrugPrep_id
					inner join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				where
					dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
			";
		} else {
			$q = "
				select top 500
					tn.TRADENAMES_ID,
					tn.NAME
				from
					rls.TRADENAMES tn with (nolock);
			";
		}

		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка комплексных МНН
	 */
	function loadDrugComplexMnnList($data) {
		$select = "";
		$where = '(1 = 1)';
		$join = '';

		if ($data['DrugComplexMnn_id'] > 0) {
			$where = 'dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
		} else {
            $where = "dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug)";

			if (strlen($data['query']) > 0) {
				$data['query'] = '%'.$data['query'].'%';
				$where .= ' and dcm.DrugComplexMnn_RusName LIKE :query';
			} else {
				return false;
			}

			if ($data['DrugRequestProperty_id'] > 0) {
				$query = "
					select
						dg.DrugGroup_Code
					from
						v_DrugRequestProperty drp with (nolock)
						left join v_DrugGroup dg with (nolock) on dg.DrugGroup_id = drp.DrugGroup_id
					where
						DrugRequestProperty_id = :DrugRequestProperty_id;
				";

				$group_code = $this->getFirstResultFromQuery($query, array(
					'DrugRequestProperty_id' => $data['DrugRequestProperty_id']
				));

				$ntfr_array = array(
					2 => 1, //0010 Лекарственные средства
					3 => 4, //0010 Изделия медицинского назначения
					4 => 213 //0600 Средства дезинфицирующие
				);

				if (array_key_exists($group_code, $ntfr_array)) {
					$where .= "
						and exists (
							select
								cn.NAME
							from
								rls.v_Drug d with (nolock)
								left join rls.v_prep p  with (nolock) on p.Prep_id = d.DrugPrep_id
								left join rls.CLSNTFR cn  with (nolock) on cn.CLSNTFR_ID = p.NTFRID
							where
								d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
								(cn.CLSNTFR_ID = {$ntfr_array[$group_code]} or cn.PARENTID = {$ntfr_array[$group_code]})
						)
					";
				}
			}
		}

		if (!$data['paging']) {
			$select = "
				,
				dcmn.DrugComplexMnnName_Name,
				cdf.NAME as ClsDrugForms_Name,
				dcmd.DrugComplexMnnDose_Name,
				dcmf.DrugComplexMnnFas_Name,
				replace(replace((
					select distinct
						ca.NAME+', '
					from
						rls.PREP_ACTMATTERS pam with (nolock)
						left join rls.PREP_ATC pa with (nolock) on pa.PREPID = pam.PREPID
						inner join rls.CLSATC ca with (nolock) on ca.CLSATC_ID = pa.UNIQID
					where
						pam.MATTERID = dcmn.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				(
					select top 1
						cn.NAME
					from
						rls.v_Drug d with (nolock)
						left join rls.v_prep p  with (nolock) on p.Prep_id = d.DrugPrep_id
						left join rls.CLSNTFR cn with (nolock) on cn.CLSNTFR_ID = p.NTFRID
					where
						d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				) as NTFR_Name
			";
		}

		$query = "
			select distinct TOP 250
				-- select
				dcm.DrugComplexMnn_id,
				dcm.DrugComplexMnn_RusName as DrugComplexMnn_Name,
				DrugComplexMnnDose.DrugComplexMnnDose_Name as DrugComplexMnn_Dose,
				dcm.CLSDRUGFORMS_ID as RlsClsdrugforms_id
				{$select}
				-- end select
			from
				-- from
				rls.v_DrugComplexMnn dcm with (NOLOCK)
				left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				{$join}
				-- end from
			where
				-- where
				{$where}
				and (
					dcm.DrugComplexMnn_pid is not null
					and dcm.DrugComplexMnn_id not in (
						select
							DrugComplexMnn_pid
						from
							rls.DrugComplexMnn with (nolock)
						where
							DrugComplexMnn_pid is not null
					)
				)
				-- end where
			order by
				-- order by
				dcm.DrugComplexMnn_RusName
				-- end order by
		";
		if ($data['paging']) {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);

			if (is_object($result_count))
			{
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		} else {
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Рассчет средней цены
	 */
	function getAveragePrice($filter) {
		$q = "
			declare
                @CurrentDate date = null,
                @DrugListRequest_id bigint = :DrugListRequest_id,
                @DrugComplexMnn_id bigint = :DrugComplexMnn_id;

            set @CurrentDate = dbo.tzGetDate();

            with drug_list as (
                select
                    i_dlr.DrugComplexMnn_id,
                    i_dlrt.TRADENAMES_id as Tradenames_id,
                    i_d.Drug_id
                from
                    v_DrugListRequest i_dlr with (nolock)
                    left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id
                    left join rls.v_Drug i_d with (nolock) on i_d.DrugComplexMnn_id = i_dlr.DrugComplexMnn_id and (i_dlrt.TRADENAMES_id is null or i_d.DrugTorg_id = i_dlrt.TRADENAMES_id)
                where
                    i_dlr.DrugListRequest_id = @DrugListRequest_id
            )
			select
				round(avg(price), 2) as AveragePrice
			from (
				select
                    wdss.WhsDocumentSupplySpec_PriceNDS as price
                from
                    (
                        select top 2
                            p.WhsDocumentSupply_id, p.WhsDocumentUc_Date
                        from
                            v_WhsDOcumentSupply p with (nolock)
                        where
                            p.WhsDocumentSupply_id in (
                                select
                                    i_wds.WhsDocumentSupply_id
                                from
                                    v_WhsDocumentSupply i_wds with (nolock)
                                    left join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                                    inner join drug_list i_dl with (nolock) on i_dl.Drug_id = i_wdss.Drug_id or (i_wdss.Drug_id is null and i_dl.DrugComplexMnn_id = i_wdss.DrugComplexMnn_id)
                                    left join v_WhsDocumentStatusType i_wdst with (nolock) on i_wdst.WhsDocumentStatusType_id = i_wds.WhsDocumentStatusType_id
                                where
                                    i_wds.WhsDocumentUc_Date <= @CurrentDate and
                                    i_wdst.WhsDocumentStatusType_Code = 2 and -- 2 - Действующий
                                    (
                                        i_wdss.DrugComplexMnn_id is null or
                                        i_wdss.DrugComplexMnn_id = @DrugComplexMnn_id
                                    )
                            )
                        order by
                            p.WhsDocumentUc_Date desc
                    ) wds
                    left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                where
                    exists(
                        select
                            i_dl.DrugComplexMnn_id
                        from
                            drug_list i_dl with (nolock)
                        where
                            i_dl.Drug_id = wdss.Drug_id or (
                                wdss.Drug_id is null and
                                i_dl.DrugComplexMnn_id = wdss.DrugComplexMnn_id
                            )
                    )
                union all
                select
                    drps.DrugRequestPurchaseSpec_Price as price
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestPurchaseSpec drps with (nolock) on drps.DrugRequest_id = dr.DrugRequest_id
                where
                    dr.DrugRequest_id in (
                        select top 1
                            i_dr.DrugRequest_id
                        from
                            v_DrugRequest i_dr with (nolock)
                            left join v_DrugRequestStatus i_drs with (nolock) on i_drs.DrugRequestStatus_id = i_dr.DrugRequestStatus_id
                            left join v_DrugRequestPurchaseSpec i_drps with (nolock) on i_drps.DrugRequest_id = i_dr.DrugRequest_id
                            inner join drug_list i_dl with (nolock) on i_dl.DrugComplexMnn_id = i_drps.DrugComplexMnn_id
                        where
                            i_drs.DrugRequestStatus_Code = 3 and  -- 3 - Утвержденная
                            (
                                i_dl.Tradenames_id is null or
                                i_drps.TRADENAMES_id is null or
                                i_dl.Tradenames_id = i_drps.TRADENAMES_id
                            )
                        order by
                            i_dr.DrugRequest_insDT desc
                    ) and
                    exists(
                        select
                            i_dl.DrugComplexMnn_id
                        from
                            drug_list i_dl with (nolock)
                        where
                            i_dl.DrugComplexMnn_id = drps.DrugComplexMnn_id and (
                                i_dl.Tradenames_id is null or
                                drps.TRADENAMES_id is null or
                                i_dl.Tradenames_id = drps.TRADENAMES_id
                            )
                    ) and
                    drps.DrugComplexMnn_id = @DrugComplexMnn_id
			) pp;
		";
		$r = $this->db->query($q, $filter);
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение минимальной, максимальной и средней цены из прошлогодних ГК
	 */
	function getLastYearSupplyPrices($filter) {
		$q = "
			declare
				@year int = null;

			select
				@year = datepart(year, DrugRequestPeriod_begDate) - 1
			from v_DrugRequestPeriod with (nolock) where DrugRequestPeriod_id = (
				select
					DrugRequestPeriod_id
				from
					DrugRequestProperty with (nolock)
				where
					DrugRequestProperty_id = :DrugRequestProperty_id
			);

			select
				cast(max(wdss.WhsDocumentSupplySpec_Price) as decimal(12,2)) as max_price,
				cast(min(wdss.WhsDocumentSupplySpec_Price) as decimal(12,2)) as min_price,
				cast((max(wdss.WhsDocumentSupplySpec_Price)+min(wdss.WhsDocumentSupplySpec_Price))/2 as decimal(12,2)) as avg_price
			from
				v_WhsDocumentSupplySpec wdss with (nolock)
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
				left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
				left join rls.v_Prep p with (nolock) on p.Prep_id = d.DrugPrep_id
			where
				wdst.WhsDocumentStatusType_Code <> 1 and -- 1 - Новый
				p.TRADENAMEID = :TRADENAMES_ID and
				datepart(year, wds.WhsDocumentUc_Date) = @year;
		";
		$r = $this->db->query($q, $filter);
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Рассчет списка цен для списка медикаментов
	 */
	function getPriceList($data) {
		$query = "";

		switch($data['mode']) {
			case 'wholesale_price':
			case 'retail_price':
				if ($data['mode'] == 'wholesale_price') {
					$select_price = "cast(round((p.Price+round(p.Price*DrugMarkup.Wholesale/100, 2))*1.1, 2) as decimal(12,2))";
				} else {
					$select_price = "cast(round((p.Price+round(p.Price*DrugMarkup.Wholesale/100, 2)+round(p.Price*DrugMarkup.Retail/100, 2))*1.1, 2) as decimal(12,2))";
				}

				$query = "
					declare
						@begDate date = null,
						@endDate date = null;

					select
						@begDate = DrugRequestPeriod_begDate,
						@endDate = DrugRequestPeriod_endDate
					from v_DrugRequestPeriod with (nolock) where DrugRequestPeriod_id = (
						select
							DrugRequestPeriod_id
						from
							v_DrugRequestProperty with (nolock)
						where
							DrugRequestProperty_id = :DrugRequestProperty_id
					);

					select
						p.DrugComplexMnn_id,
						isnull({$select_price}, 0) as Price
					from
						(
							select
								avg(n.PRICEINRUB) as Price,
								d.DrugComplexMnn_id
							from
								rls.NOMEN n	with (nolock)
								left join rls.v_Drug d with (nolock) on d.Drug_id = n.NOMEN_ID
							where
								d.DrugComplexMnn_id in ({$data['DrugComplexMnn_List']}) and
								n.PRICEINRUB is not null and
								isnull(Nomen_deleted, 1) <> 2
							group by
								d.DrugComplexMnn_id
						) p
						left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = p.DrugComplexMnn_id
						left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = DrugComplexMnnName.ActMatters_id
						outer apply (
							select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
						) IsNarko
						outer apply (
							select top 1
								DrugMarkup_Wholesale as Wholesale,
								DrugMarkup_Retail as Retail
							from
								v_DrugMarkup dm with (nolock)
								left join v_YesNo is_narko with (nolock) on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug
							where
								p.Price between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
								isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
								(
									DrugMarkup_begDT <= @endDate and
									(
										DrugMarkup_endDT is null or
										DrugMarkup_endDT >= @begDate
									)
								)
							order by
								DrugMarkup_begDT
						) DrugMarkup
				";
				break;
			case 'drug_request':
				$query = "
					declare
						@begDate date = null,
						@endDate date = null,
						@DrugFinance_id bigint,
						@PersonRegisterType_id bigint;

					select
						@begDate = DrugRequestPeriod_begDate,
						@endDate = DrugRequestPeriod_endDate,
						@DrugFinance_id = DrugFinance_id,
						@PersonRegisterType_id = PersonRegisterType_id
					from
						v_DrugRequestProperty drp with (nolock)
						left join v_DrugRequestPeriod period with (nolock) on period.DrugRequestPeriod_id = drp.DrugRequestPeriod_id
					where
						DrugRequestProperty_id = :DrugRequestProperty_id;

					select
						dcm.DrugComplexMnn_id,
						cast(isnull(LastDrugRequest.Price, 0) as decimal(14,2)) as Price
					from
						rls.DrugComplexMnn dcm with (nolock)
						outer apply (
							select top 1
								case
									when
										drr.DrugRequestRow_Kolvo > 0
									then
										cast(drr.DrugRequestRow_Summa/drr.DrugRequestRow_Kolvo as decimal(14,2))
									else
										0
								end as Price
							from
								v_DrugRequest dr with (nolock)
								left join v_DrugRequestRow drr with (nolock) on drr.DrugRequest_id = dr.DrugRequest_id
								left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
							where
								dr.DrugRequest_Version is null and
								dr.PersonRegisterType_id = @PersonRegisterType_id and
								drp.DrugRequestPeriod_begDate < @begDate and
								drr.DrugFinance_id = @DrugFinance_id and
								drr.DrugComplexMnn_id = dcm.DrugComplexMnn_id
							order by
								drp.DrugRequestPeriod_begDate desc, dr.DrugRequest_id desc
						) LastDrugRequest
					where
						DrugComplexMnn_id in ({$data['DrugComplexMnn_List']});
				";
				break;
			case 'average_value':
				$query = "
					declare
                        @CurrentDate date = null;

                    set @CurrentDate = dbo.tzGetDate();

                    select
                        dcm.DrugComplexMnn_id,
                        cast(isnull(LastDocuments.Price, 0) as decimal(14,2)) as Price
                    from
                        rls.DrugComplexMnn dcm with (nolock)
                        outer apply (
                            select
                                avg(ppp.Price) as Price
                            from (
                                select
                                    wdss.WhsDocumentSupplySpec_PriceNDS as Price
                                from
                                    (
                                        select top 2
                                            p.WhsDocumentSupply_id, p.WhsDocumentUc_Date
                                        from
                                            v_WhsDOcumentSupply p with (nolock)
                                        where
                                            p.WhsDocumentSupply_id in (
                                                select
                                                    i_wds.WhsDocumentSupply_id
                                                from
                                                    v_WhsDocumentSupply i_wds with (nolock)
                                                    left join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id
                                                    left join v_WhsDocumentStatusType i_wdst with (nolock) on i_wdst.WhsDocumentStatusType_id = i_wds.WhsDocumentStatusType_id
                                                    left join rls.v_Drug i_d with (nolock) on i_d.Drug_id = i_wdss.Drug_id
                                                where
                                                    i_wds.WhsDocumentUc_Date <= @CurrentDate and
                                                    i_wdst.WhsDocumentStatusType_Code = 2 and -- 2 - Действующий
                                                    (
                                                        i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id or
                                                        i_wdss.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                                    )
                                            )
                                        order by
                                            p.WhsDocumentUc_Date desc
                                    ) wds
                                    left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                                    left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
                                where
                                    d.DrugComplexMnn_id = dcm.DrugComplexMnn_id or
                                    wdss.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                union all
                                select
                                    drps.DrugRequestPurchaseSpec_Price
                                from
                                    v_DrugRequest dr with (nolock)
                                    left join v_DrugRequestPurchaseSpec drps with (nolock) on drps.DrugRequest_id = dr.DrugRequest_id
                                where
                                    dr.DrugRequest_id in (
                                        select top 1
                                            i_dr.DrugRequest_id
                                        from
                                            v_DrugRequest i_dr with (nolock)
                                            left join v_DrugRequestStatus i_drs with (nolock) on i_drs.DrugRequestStatus_id = i_dr.DrugRequestStatus_id
                                            left join v_DrugRequestPurchaseSpec i_drps with (nolock) on i_drps.DrugRequest_id = i_dr.DrugRequest_id
                                        where
                                            i_drs.DrugRequestStatus_Code = 3 and  -- 3 - Утвержденная
                                            i_drps.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                        order by
                                            i_dr.DrugRequest_insDT desc
                                    ) and
                                    drps.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                            ) ppp
                        ) LastDocuments
                    where
                        dcm.DrugComplexMnn_id in ({$data['DrugComplexMnn_List']});
				";
				break;
		}

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0])) {
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Копирование спиcка медикаментов
	 */
	function CopyDrugListRequestList($data) {
		$data['NewDrugRequestProperty_id'] = $this->getDrugRequestProperty_id();

		//Копируем позиции без списков торговых наименований
		$query = "
			insert into
				DrugListRequest (
					DrugRequestProperty_id,
					DrugComplexMnn_id,
					DrugListRequest_Price,
					DrugTorgUse_id,
					DrugListRequest_Code,
					pmUser_insID,
					pmUser_updID,
					DrugListRequest_insDT,
					DrugListRequest_updDT,
					DrugListRequest_IsProblem
				)
			select
				:NewDrugRequestProperty_id,
				DrugComplexMnn_id,
				DrugListRequest_Price,
				DrugTorgUse_id,
				DrugListRequest_Code,
				:pmUser_id,
				:pmUser_id,
				dbo.tzGetDate(),
				dbo.tzGetDate(),
				DrugListRequest_IsProblem
			from
				DrugListRequest with (nolock)
			where
				DrugRequestProperty_id = :OriginalDrugRequestProperty_id and
						DrugListRequest_id not in (
						select
						DrugListRequest_id
					from
						DrugListRequestTorg with (nolock)
				)
		";
		$result = $this->db->query($query, $data);

		//Получаем список идентификаторов со списками торговых наименований
		$query = "
			select
				DrugListRequest_id,
				DrugComplexMnn_id,
				DrugListRequest_Price,
				DrugTorgUse_id,
				DrugListRequest_Code,
				DrugListRequest_IsProblem
			from
				DrugListRequest with (nolock)
			where
				DrugRequestProperty_id = :OriginalDrugRequestProperty_id and
				DrugListRequest_id in (
					select
						DrugListRequest_id
					from
						DrugListRequestTorg with (nolock)
				)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$arr = $result->result('array');
			foreach($arr as $item) {
				//Копируем конкретную позицию
				$query = "
					declare
						@DrugListRequest_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @DrugListRequest_id = null;
					exec dbo.p_DrugListRequest_ins
						@DrugListRequest_id = @DrugListRequest_id output,
						@DrugRequestProperty_id = :NewDrugRequestProperty_id,
						@DrugComplexMnn_id = :DrugComplexMnn_id,
						@DrugListRequest_Price = :DrugListRequest_Price,
						@DrugTorgUse_id = :DrugTorgUse_id,
						@DrugListRequest_Code = :DrugListRequest_Code,
						@DrugListRequest_IsProblem = :DrugListRequest_IsProblem,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @DrugListRequest_id as DrugListRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array_merge(
					$item,
					array(
						'NewDrugRequestProperty_id' => $data['NewDrugRequestProperty_id'],
						'pmUser_id' => $data['pmUser_id']
					)
				));
				if (is_object($result)) {
					$res = $result->result('array');
					$new_id = $res[0]['DrugListRequest_id'];

					//Копируем список торговых наименований для конкретной позиции
					$query = "
						insert into
							DrugListRequestTorg
						select
							:NewDrugListRequest_id,
							TRADENAMES_id,
							Drug_id,
							OrgFarmacyPrice_Min,
							OrgFarmacyPrice_Max,
							DrugRequestPrice_Min,
							DrugRequestPrice_Max,
							DrugRequest_Price,
							:pmUser_id,
							:pmUser_id,
							dbo.tzGetDate(),
							dbo.tzGetDate(),
							DrugListRequestTorg_IsProblem
						from
							DrugListRequestTorg with (nolock)
						where
							DrugListRequest_id = :DrugListRequest_id;
					";
					$result = $this->db->query($query, array(
						'NewDrugListRequest_id' => $new_id,
						'DrugListRequest_id' => $item['DrugListRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
	}

	/**
	 * Получение дополнительных данных для позиции списка медикаментов для заявки
	 */
	function getDrugListRequestContext($data) {
		$q = "
			select
				dcmc.DrugComplexMnnCode_Code as DrugComplexMnn_Code,
				(
					select distinct
						','+SUBSTRING(ca.NAME, 1, CHARINDEX(' ',ca.NAME)-1) as 'data()'
					from
						rls.PREP_ACTMATTERS pam with (nolock)
						left join rls.PREP_ATC pa with (nolock) on pa.PREPID = pam.PREPID
						inner join rls.CLSATC ca with (nolock) on ca.CLSATC_ID =pa.UNIQID
					where
						pam.MATTERID = dcmn.ActMatters_id
					for xml path('')
				) as ATX_CODE_list,
				am.STRONGGROUPID,
				am.NARCOGROUPID,
				cdf.NAME as ClsDrugForms_Name,
				dcmd.DrugComplexMnnDose_Name,
				dcmf.DrugComplexMnnFas_Name,
				(
					select top 1
						cn.NAME
					from
						rls.v_Drug d with (nolock)
						left join rls.v_prep p with (nolock) on p.Prep_id = d.DrugPrep_id
						left join rls.CLSNTFR cn with (nolock) on cn.CLSNTFR_ID = p.NTFRID
					where
						d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				) as NTFR_Name
			from
				rls.v_DrugComplexMnn dcm with (nolock)
				left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
				outer apply (
					select top 1
						DrugComplexMnnCode_Code
					from
						rls.v_DrugComplexMnnCode with (nolock)
					where
						v_DrugComplexMnnCode.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				) dcmc
			where
				dcm.DrugComplexMnn_id = :DrugComplexMnn_id
		";
		$r = $this->db->query($q, array(
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
		));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Рассчет цен из справочника ЖНВЛП
	 */
	function getJNVLPPrices($data) {
		$where = "";

		if (!empty($data['TRADENAMES_ID_List'])) {
			$where .= " and p.TRADENAMEID in ({$data['TRADENAMES_ID_List']})";
		}

		$query = "
			select
				min(p.Price) as MinPrice,
				max(p.Price) as MaxPrice,
				min(p.Wholesale_Price) as Wholesale_MinPrice,
				max(p.Wholesale_Price) as Wholesale_MaxPrice
			from (
				select
					n.PRICEINRUB as Price,
					cast(n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2) as decimal(12,2)) as Wholesale_Price --Предельно допустимая оптовая цена без НДС (руб.)
				from
					rls.v_Nomen n with (nolock)
					left join rls.v_prep p with (nolock) on p.Prep_id = n.PREPID
					left join rls.v_Drug d with (nolock) on d.Drug_id = n.NOMEN_ID
					left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
					outer apply (
						select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
					) IsNarko
					outer apply (
						select
							DrugMarkup_Wholesale as Wholesale
						from
							v_DrugMarkup dm with (nolock)
							left join v_YesNo is_narko with (nolock) on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug
						where
							n.PRICEINRUB between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
							isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
							(
								n.PRICEDATE is null or (
									DrugMarkup_begDT <= n.PRICEDATE and
									(
										DrugMarkup_endDT is null or
										DrugMarkup_endDT >= n.PRICEDATE
									)
								)
							)
					) DrugMarkup
				where
					n.PRICEINRUB is not null and
					(:TRADENAMES_ID is null or p.TRADENAMEID = :TRADENAMES_ID) and
					(:DrugComplexMnn_id is null or d.DrugComplexMnn_id = :DrugComplexMnn_id)
					{$where}
			) p
		";
		$result = $this->getFirstRowFromQuery($query, $data);

		return is_array($result) ? array($result) : false;
	}

	/**
	 * Загрузка списка организаций для комбобокса
	 */
	function loadOrgCombo($data) {
		$filters = array();
		$params = array();
		$where = "";

		if (!empty($data['Org_id'])) {
			$filters[] = "o.Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		} else {
			$filters[] = "ot.OrgType_Code in ('11', '13')"; //11 - МО, 13 - ТОУЗ;
			if (!empty($data['query'])) {
				$filters[] = "o.Org_Nick like :query or o.Org_Name like :query";
				$params['query'] = "%".$data['query']."%";
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
        	select top 500
        		o.Org_id,
        		o.Org_Name
			from
				v_Org o with (nolock)
				left join v_OrgType ot with (nolock) on ot.OrgType_id = o.OrgType_id
				{$where}
			order by
				o.Org_Name
    	";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}