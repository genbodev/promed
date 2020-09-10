<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugRequestProperty_model extends swPgModel {
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
			select DrugRequestProperty_id as \"DrugRequestProperty_id\",
                   DrugRequestProperty_Name as \"DrugRequestProperty_Name\",
                   DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
                   PersonRegisterType_id as \"PersonRegisterType_id\",
                   DrugFinance_id as \"DrugFinance_id\",
                   DrugGroup_id as \"DrugGroup_id\",
                   Org_id as \"Org_id\"
            from dbo.v_DrugRequestProperty
            where DrugRequestProperty_id =:DrugRequestProperty_id
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
			$where[] = 'DATE_PART(\'year\',DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) = :Year';
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
				v_DrugRequestProperty.DrugRequestProperty_id as \"DrugRequestProperty_id\",
				v_DrugRequestProperty.DrugRequestProperty_Name as \"DrugRequestProperty_Name\",
				v_DrugRequestProperty.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				v_DrugRequestProperty.PersonRegisterType_id as \"PersonRegisterType_id\",
				v_DrugRequestProperty.DrugFinance_id as \"DrugFinance_id\"
				,DrugRequestPeriod_id_ref.DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\",
				PersonRegisterType_id_ref.PersonRegisterType_Name as \"PersonRegisterType_Name\",
				DrugFinance_id_ref.DrugFinance_Name as \"DrugFinance_Name\",
				mnn_cnt.cnt as \"Mnn_Count\",
				Org.Org_Name as \"DrugRequestProperty_OrgName\",
				v_DrugRequestProperty.Org_id as \"DrugRequestProperty_Org\"
			FROM
				dbo.v_DrugRequestProperty 

				LEFT JOIN dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref  ON DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequestProperty.DrugRequestPeriod_id

				LEFT JOIN dbo.v_PersonRegisterType PersonRegisterType_id_ref  ON PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugRequestProperty.PersonRegisterType_id

				LEFT JOIN dbo.v_DrugFinance DrugFinance_id_ref  ON DrugFinance_id_ref.DrugFinance_id = v_DrugRequestProperty.DrugFinance_id

				left join dbo.v_Org Org  on org.Org_id = v_DrugRequestProperty.Org_id

				LEFT JOIN LATERAL (

					select count(DrugListRequest_id) as cnt
					from DrugListRequest 

					where
						DrugRequestProperty_id = v_DrugRequestProperty.DrugRequestProperty_id
				) mnn_cnt ON true
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
			select DrugRequestProperty_id as \"DrugRequestProperty_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				DrugRequestProperty_id := :DrugRequestProperty_id,
				DrugRequestProperty_Name := :DrugRequestProperty_Name,
				DrugRequestPeriod_id := :DrugRequestPeriod_id,
				PersonRegisterType_id := :PersonRegisterType_id,
				DrugFinance_id := :DrugFinance_id,
				DrugGroup_id := :DrugGroup_id,
				Org_id := :Org_id,
				pmUser_id := :pmUser_id);


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
				dr.DrugRequest_Name as \"DrugRequest_Name\"
			from
				v_DrugRequest dr 

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
					from DrugListRequest 

					where DrugRequestProperty_id = :DrugRequestProperty_id
				);
			
			delete from DrugListRequest
			where DrugRequestProperty_id = :DrugRequestProperty_id;
		";
		$r = $this->db->query($q, array(
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id
		));
	
		$q = "
		    select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_DrugRequestProperty_del(
				DrugRequestProperty_id := :DrugRequestProperty_id);


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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_DrugListRequest_updNumber(
				DrugListRequest_id := :DrugListRequest_id,
				DrugListRequest_Number := :DrugListRequest_Number,
				pmUser_id := :pmUser_id);     
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
			select DrugListRequest_id as \"DrugListRequest_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				DrugListRequest_id := :DrugListRequest_id,
				DrugRequestProperty_id := :DrugRequestProperty_id,
				DrugComplexMnn_id := :DrugComplexMnn_id,
				DrugListRequest_Price := cast( case when cast( :DrugListRequest_Price as varchar ) = '' then '0' else :DrugListRequest_Price end as float ),
				DrugTorgUse_id := :DrugTorgUse_id,
				DrugListRequest_Code := null,
				DrugListRequest_IsProblem := (select YesNo_id from YesNo  where YesNo_Code = :DrugListRequest_IsProblem),
				DrugListRequest_Comment := :DrugListRequest_Comment,
				pmUser_id := :pmUser_id);


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
			    select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_DrugListRequest_del(
					DrugListRequest_id := :DrugListRequest_id);
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
				v_DrugListRequest.DrugListRequest_id as \"DrugListRequest_id\",
				v_DrugListRequest.DrugRequestProperty_id as \"DrugRequestProperty_id\",
				v_DrugListRequest.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DrugComplexMnnCode.DrugComplexMnnCode_Code as \"DrugComplexMnn_Code\",
				DrugComplexMnn_id_ref.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				ActMatters.STRONGGROUPID as \"STRONGGROUPID\",
				ActMatters.NARCOGROUPID as \"NARCOGROUPID\",
				v_DrugListRequest.DrugListRequest_Price as \"DrugListRequest_Price\",
				v_DrugListRequest.DrugTorgUse_id as \"DrugTorgUse_id\",
				DrugTorgUse_id_ref.DrugTorgUse_Name as \"DrugTorgUse_Name\",
				v_DrugListRequest.DrugListRequest_Code as \"DrugListRequest_Code\",
				DrugTorgUse_id_ref.DrugTorgUse_Name as \"DrugTorgUse_Name\",
				COALESCE(isProblem.YesNo_Code, 0) as \"DrugListRequest_IsProblem\",

                v_DrugListRequest.DrugListRequest_Number as \"DrugListRequest_Number\",
                v_DrugListRequest.DrugListRequest_Comment as \"DrugListRequest_Comment\",
				(
                    select 
                        string_agg(cast(dlrt.TRADENAMES_ID as varchar),',')
                    from DrugListRequestTorg dlrt 
                    where
                        dlrt.DrugListRequest_id = v_DrugListRequest.DrugListRequest_id
                ) as \"TRADENAMES_ID_list\",
				(
                    select
                        string_agg(t.NAME,',')
                    from
                        DrugListRequestTorg dlrt 
                        left join rls.v_TRADENAMES t  on t.TRADENAMES_ID = dlrt.TRADENAMES_ID
                    where
                        dlrt.DrugListRequest_id = v_DrugListRequest.DrugListRequest_id
                
                ) as \"TRADENAMES_NAME_list\",
				(
                    SELECT string_agg(data,',') 
                    FROM (
                    select distinct
                        SUBSTRING(ca.NAME, 1, strpos(ca.NAME, ' ')-1) as data
                    from
                        rls.PREP_ACTMATTERS pam 
                        left join rls.PREP_ATC pa  on pa.PREPID = pam.PREPID
                        inner join rls.CLSATC ca  on ca.CLSATC_ID =pa.UNIQID
                    where
                        pam.MATTERID = DrugComplexMnnName.ActMatters_id
                    ) t
                ) as \"ATX_CODE_list\",
				(
					select 
						cn.NAME
					from
						rls.v_Drug d 

						left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id

						left join rls.CLSNTFR cn  on cn.CLSNTFR_ID = p.NTFRID

					where
						d.DrugComplexMnn_id = DrugComplexMnn_id_ref.DrugComplexMnn_id
                    limit 1
				) as \"NTFR_Name\",
				DrugComplexMnnName.DrugComplexMnnName_Name as \"DrugComplexMnnName_Name\",
				CLSDRUGFORMS.NAME as \"ClsDrugForms_Name\",
				DrugComplexMnnDose.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
				DrugComplexMnnFas.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\"
            -- end select
			from
            -- from
				dbo.v_DrugListRequest 

				left join rls.v_DrugComplexMnn DrugComplexMnn_id_ref  on DrugComplexMnn_id_ref.DrugComplexMnn_id = v_DrugListRequest.DrugComplexMnn_id

				left join rls.DrugComplexMnnName  on DrugComplexMnnName.DrugComplexMnnName_id = DrugComplexMnn_id_ref.DrugComplexMnnName_id

				left join rls.DrugComplexMnnDose  on DrugComplexMnnDose.DrugComplexMnnDose_id = DrugComplexMnn_id_ref.DrugComplexMnnDose_id

				left join rls.DrugComplexMnnFas  on DrugComplexMnnFas.DrugComplexMnnFas_id = DrugComplexMnn_id_ref.DrugComplexMnnFas_id

				left join rls.CLSDRUGFORMS  on CLSDRUGFORMS.CLSDRUGFORMS_ID = DrugComplexMnn_id_ref.CLSDRUGFORMS_ID

				left join rls.v_ACTMATTERS ActMatters  on ActMatters.ACTMATTERS_ID = DrugComplexMnnName.ActMatters_id

				left join dbo.v_DrugTorgUse DrugTorgUse_id_ref  on DrugTorgUse_id_ref.DrugTorgUse_id = v_DrugListRequest.DrugTorgUse_id

				left join dbo.v_YesNo isProblem  on isProblem.YesNo_id = v_DrugListRequest.DrugListRequest_IsProblem

				LEFT JOIN LATERAL (

					select 
						v_DrugComplexMnnCode.DrugComplexMnnCode_Code
					from
						rls.v_DrugComplexMnnCode 

					where
						v_DrugComplexMnnCode.DrugComplexMnnCode_Code is not null and
						v_DrugComplexMnnCode.DrugComplexMnn_id = v_DrugListRequest.DrugComplexMnn_id
					order by
						v_DrugComplexMnnCode.DrugComplexMnnCode_id
                    limit 1
				) DrugComplexMnnCode ON true
            -- end from
			where
            -- where
				DrugRequestProperty_id = :DrugRequestProperty_id
            -- end where
            order by
            -- order by
            \"DrugRequestProperty_id\"
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
			select DrugListRequestTorg_id as \"DrugListRequestTorg_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				DrugListRequestTorg_id := :DrugListRequestTorg_id,
				DrugListRequest_id := :DrugListRequest_id,
				TRADENAMES_id := :TRADENAMES_id,
				Drug_id := NULL,
				OrgFarmacyPrice_Min := cast( case when cast( :OrgFarmacyPrice_Min as varchar ) = '' then '0' else :OrgFarmacyPrice_Min end as float ),
				OrgFarmacyPrice_Max := cast( case when cast( :OrgFarmacyPrice_Max as varchar ) = '' then '0' else :OrgFarmacyPrice_Max end as float ),
				DrugRequestPrice_Min := cast( case when cast( :DrugRequestPrice_Min as varchar ) = '' then '0' else :DrugRequestPrice_Min end as float ),
				DrugRequestPrice_Max := cast( case when cast( :DrugRequestPrice_Max as varchar ) = '' then '0' else :DrugRequestPrice_Max end as float ),
				DrugRequest_Price := cast( case when cast( :DrugRequest_Price as varchar ) = '' then '0' else :DrugRequest_Price end as float ),
				DrugListRequestTorg_IsProblem := (select YesNo_id from YesNo  where YesNo_Code = :DrugListRequestTorg_IsProblem),
				pmUser_id := :pmUser_id);


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
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from dbo.p_DrugListRequestTorg_del(
					DrugListRequestTorg_id := :DrugListRequestTorg_id);
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
				v_DrugListRequestTorg.DrugListRequestTorg_id as \"DrugListRequestTorg_id\",
				v_DrugListRequestTorg.DrugListRequest_id as \"DrugListRequest_id\",
				v_DrugListRequestTorg.TRADENAMES_id as \"TRADENAMES_id\",
				TRADENAMES.NAME as \"TRADENAMES_Name\",
				v_DrugListRequestTorg.Drug_id as \"Drug_id\",
				v_DrugListRequestTorg.OrgFarmacyPrice_Min as \"OrgFarmacyPrice_Min\",
				v_DrugListRequestTorg.OrgFarmacyPrice_Max as \"OrgFarmacyPrice_Max\",
				v_DrugListRequestTorg.DrugRequestPrice_Min as \"DrugRequestPrice_Min\",
				v_DrugListRequestTorg.DrugRequestPrice_Max as \"DrugRequestPrice_Max\",
				v_DrugListRequestTorg.DrugRequest_Price as \"DrugRequest_Price\",
				COALESCE(isProblem.YesNo_Code, 0) as \"DrugListRequestTorg_IsProblem\"

			from
				dbo.v_DrugListRequestTorg 

				left join rls.TRADENAMES  on TRADENAMES.TRADENAMES_id = v_DrugListRequestTorg.TRADENAMES_id

				left join dbo.v_YesNo isProblem  on isProblem.YesNo_id = v_DrugListRequestTorg.DrugListRequestTorg_IsProblem

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
            $params[] = "dcmn.DrugComplexMnnName_Name iLIKE :DrugComplexMnnName_Name";

            $filter['DrugComplexMnnName_Name'] = $filter['DrugComplexMnnName_Name'].'%';
            $inline_input = true;
        }

        if (!empty($filter['CLSDRUGFORMS_fullname'])) {
            $params[] = "cdf.fullname iLIKE :CLSDRUGFORMS_fullname||'%' ";

            $filter['CLSDRUGFORMS_fullname'] = $filter['CLSDRUGFORMS_fullname'].'%';
            $inline_input = true;
        }

        if (!empty($filter['DrugComplexMnnDose_Name'])) {
            $params[] = "dcmd.DrugComplexMnnDose_Name iLIKE :DrugComplexMnnDose_Name";

            $filter['DrugComplexMnnDose_Name'] = $filter['DrugComplexMnnDose_Name'].'%';
            $inline_input = true;
        }

        if (!empty($filter['DrugComplexMnnFas_Name'])) {
            $params[] = "dcmf.DrugComplexMnnFas_Name iLIKE :DrugComplexMnnFas_Name";

            $filter['DrugComplexMnnFas_Name'] = '%№ '.$filter['DrugComplexMnnFas_Name'].'%';
            $inline_input = true;
        }

        if ($inline_input) { //поточный ввод
            $params[] = "dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug )";

            $paramsString = implode(" and ", $params);

            $query = "
                select
                    ppp.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                    ppp.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                    ppp.DrugComplexMnnName_Name as \"DrugComplexMnnName_Name\",
                    ppp.FULLNAME as \"FULLNAME\",
                    ppp.namE as \"namE\",
                    ppp.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
                    ppp.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\",
                    ppp.state,
                    (
                        case
                        when state = 'add' then 'Добавить'
                        when state = 'delete' then 'Удалить'
                        end
                    ) as \"action_name\"
                from
                    (
                        select
                            COALESCE(dcm.DrugComplexMnn_id, dlr.DrugComplexMnn_id) as DrugComplexMnn_id,

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
                            rls.DrugComplexMnn dcm 

                            full outer join (select DrugComplexMnn_id from rls.DrugComplexMnn  where DrugComplexMnn_id in (NULL)) dlr on dlr.DrugComplexMnn_id = dcm.DrugComplexMnn_id

                            left join rls.DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

                            left join rls.DrugComplexMnnDose dcmd  on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id

                            left join rls.DrugComplexMnnFas dcmf  on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id

                            left join rls.CLSDRUGFORMS cdf  on dcm.CLSDRUGFORMS_ID=cdf.CLSDRUGFORMS_ID

                        where
                            -- ставим условия по фильтрам
                            {$paramsString}
                    ) ppp
                where
                    ppp.state is not null
                    or not exists (select DrugComplexMnn_pid from rls.DrugComplexMnn  where DrugComplexMnn_pid is not null and ppp.DrugComplexMnn_id = DrugComplexMnn_pid limit 1)

                order by
                    ppp.state,
                    ppp.DrugComplexMnn_RusName;
            ";
        } else {
                $query = "
                select
                    ppp.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                    DrugComplexMnnCode.DrugComplexMnnCode_Code as \"DrugComplexMnn_Code\",
                    ppp.state,
                    (
                        case
                            when state = 'add' then 'Добавить'
                            when state = 'delete' then 'Удалить'
                        end
                    ) as \"action_name\",
                    ppp.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                    ppp.DrugComplexMnnName_Name as \"DrugComplexMnnName_Name\",
                    ppp.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
                    ppp.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\",
                    (
                    	select string_agg(data,',')
                        from (
                        select distinct
                            SUBSTRING(ca.NAME, 1, strpos(ca.NAME, ' ')-1) as data
                        from
                            rls.PREP_ACTMATTERS pam 

                            left join rls.PREP_ATC pa  on pa.PREPID = pam.PREPID

                            inner join rls.CLSATC ca  on ca.CLSATC_ID =pa.UNIQID

                        where
                            pam.MATTERID = ppp.ActMatters_id
						) t
                    ) as \"ATX_CODE_list\",
                    (
                        select 
                            cn.NAME
                        from
                            rls.v_Drug d 

                            left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id

                            left join rls.CLSNTFR cn  on cn.CLSNTFR_ID = p.NTFRID

                        where
                            d.DrugComplexMnn_id = ppp.DrugComplexMnn_id
                        limit 1
                    ) as \"NTFR_Name\",
                    am.STRONGGROUPID as \"STRONGGROUPID\",
                    am.NARCOGROUPID as \"NARCOGROUPID\",
                    cdf.NAME as \"ClsDrugForms_Name\",
                    cdf.FULLNAME as \"FULLNAME\"
                from (
                    select
                        COALESCE(dcm.DrugComplexMnn_id, dlr.DrugComplexMnn_id) as DrugComplexMnn_id,

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
                                DrugNormativeListSpec dnls 

                                left join DrugNormativeListSpecFormsLink dnlsfl  on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
                            where
                                :DrugNormativeList_id is null or
                                dnls.DrugNormativeList_id = :DrugNormativeList_id
                        ) p
                        LEFT JOIN LATERAL (

                            select
                                COUNT(dnlsfl.DrugNormativeListSpecForms_id) cnt
                            from
                                v_DrugNormativeListSpec dnls 

                                left join DrugNormativeListSpecFormsLink dnlsfl  on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
                            where
                                (
                                    :DrugNormativeList_id is null or
                                    dnls.DrugNormativeList_id = :DrugNormativeList_id
                                ) and
                                dnls.DrugNormativeListSpecMNN_id = p.DrugNormativeListSpecMNN_id
                        ) forms_cnt ON true
                        where
                            p.DrugNormativeListSpecForms_id is not null or
                            (p.DrugNormativeListSpecForms_id is null and forms_cnt.cnt <= 0)
                    ) pp
                    inner join rls.DrugComplexMnn dcm  on exists(

                        select DrugComplexMnnName.ActMatters_id from rls.DrugComplexMnnName 

                        where DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id and DrugComplexMnnName.ActMatters_id = pp.DrugNormativeListSpecMNN_id
                        )
                        and (pp.DrugNormativeListSpecForms_id is null or dcm.CLSDRUGFORMS_ID = pp.DrugNormativeListSpecForms_id)
                    full outer join (
                        select DrugComplexMnn_id from rls.DrugComplexMnn  
                        where DrugComplexMnn_id in (".(!empty($filter['DrugComplexMnn_id_list']) ? $filter['DrugComplexMnn_id_list'] : "NULL").")

                    ) dlr on dlr.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                    left join rls.DrugComplexMnn cm  on cm.DrugComplexMnn_id = dcm.DrugComplexMnn_id or cm.DrugComplexMnn_id = dlr.DrugComplexMnn_id

                    left join rls.DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = cm.DrugComplexMnnName_id

                    left join rls.DrugComplexMnnDose dcmd   on dcmd.DrugComplexMnnDose_id = cm.DrugComplexMnnDose_id

                    left join rls.DrugComplexMnnFas dcmf  on dcmf.DrugComplexMnnFas_id = cm.DrugComplexMnnFas_id

                    where --исключаем мнн-родителей
                        dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug) and
                        (
                            dcm.DrugComplexMnn_pid is not null
                            or dcm.DrugComplexMnn_id not in (
                                select
                                    DrugComplexMnn_pid
                                from
                                    rls.DrugComplexMnn 

                                where
                                    DrugComplexMnn_pid is not null
                            )
                        )
                ) ppp
                left join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = ppp.ActMatters_id

                LEFT JOIN LATERAL (

                    select 
                        v_DrugComplexMnnCode.DrugComplexMnnCode_Code
                    from
                        rls.v_DrugComplexMnnCode 

                    where
                        v_DrugComplexMnnCode.DrugComplexMnnCode_Code is not null and
                        v_DrugComplexMnnCode.DrugComplexMnn_id = ppp.DrugComplexMnn_id
                    order by
                        v_DrugComplexMnnCode.DrugComplexMnnCode_id
                    limit 1
                ) DrugComplexMnnCode ON true
                left join rls.CLSDRUGFORMS cdf  on cdf.CLSDRUGFORMS_ID = ppp.CLSDRUGFORMS_ID

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
					tn.TRADENAMES_ID as \"TRADENAMES_ID\",
					tn.NAME as \"NAME\"
				from
					rls.DrugComplexMnn dcm 

					inner join rls.Drug d  on d.DrugComplexMnn_id = dcm.DrugComplexMnn_id

					inner join rls.PREP p  on p.Prep_id = d.DrugPrep_id

					inner join rls.TRADENAMES tn  on tn.TRADENAMES_ID = p.TRADENAMEID

				where
					dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
			";
		} else {
			$q = "
				select 
					tn.TRADENAMES_ID as \"TRADENAMES_ID\",
					tn.NAME as \"NAME\"
				from
					rls.TRADENAMES tn 
			    LIMIT 500;

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
		$join = "
			left join lateral (select replace(replace(
                	string_agg(distinct ca.NAME,',')||',,', ', ,,', ''), ',,', '') as name
                	from rls.PREP_ACTMATTERS pam
                	    left join rls.PREP_ATC pa  on pa.PREPID = pam.PREPID
                        inner join rls.CLSATC ca  on ca.CLSATC_ID = pa.UNIQID
                    where pam.MATTERID = dcmn.ActMatters_id
                    ) as tmp on true
                    
				 left join lateral (
				    select cn.NAME
                    from rls.v_prep p
                    left join rls.CLSNTFR cn  on cn.CLSNTFR_ID = p.NTFRID
                    where p.Prep_id = drug.DrugPrep_id
                    limit 1
                 ) as tmp2 on true
                 ";

		if ($data['DrugComplexMnn_id'] > 0) {
			$where = 'dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
		} else {
            $where = "dcm.DrugComplexMnn_id in (select DrugComplexMnn_id from rls.Drug)";

			if (strlen($data['query']) > 0) {
				$data['query'] = '%'.$data['query'].'%';
				$where .= ' and lower(dcm.DrugComplexMnn_RusName) LIKE lower(:query)';

			} else {
				return false;
			}

			if ($data['DrugRequestProperty_id'] > 0) {
				$query = "
					select
						dg.DrugGroup_Code as \"DrugGroup_Code\"
					from
						v_DrugRequestProperty drp 

						left join v_DrugGroup dg  on dg.DrugGroup_id = drp.DrugGroup_id

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
								rls.v_Drug d 

								left join rls.v_prep p   on p.Prep_id = d.DrugPrep_id

								left join rls.CLSNTFR cn   on cn.CLSNTFR_ID = p.NTFRID

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
				dcmn.DrugComplexMnnName_Name as \"DrugComplexMnnName_Name\",
				cdf.NAME as \"ClsDrugForms_Name\",
				dcmd.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
				dcmf.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\",
				tmp.name as \"ATX_Code\",
                tmp2.name as \"NTFR_Name\" 
			";
		}

		$query = "
			select distinct
				-- select
				dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\",
				DrugComplexMnnDose.DrugComplexMnnDose_Name as \"DrugComplexMnn_Dose\",
				dcm.CLSDRUGFORMS_ID as \"RlsClsdrugforms_id\"
				{$select}
				-- end select
			from
				-- from
				rls.v_DrugComplexMnn dcm 
				
				inner join rls.Drug drug on drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id

				left join rls.DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				left join rls.DrugComplexMnnDose dcmd  on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id

				left join rls.DrugComplexMnnFas dcmf  on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id

				left join rls.CLSDRUGFORMS cdf  on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID

				left join rls.DrugComplexMnnDose  on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id

				{$join}
				-- end from
			where
				-- where
				{$where}
				and 
                (
					dcm.DrugComplexMnn_pid is not null
					and not exists (
						select
                            DrugComplexMnn_pid
                        from
                            rls.DrugComplexMnn 
                        where
                            DrugComplexMnn_pid = dcm.DrugComplexMnn_id
                        limit 1
					)
				)
				-- end where
			order by
				-- order by
				dcm.DrugComplexMnn_RusName
				-- end order by
            limit 250
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
			with drug_list as (
                select
                    i_dlr.DrugComplexMnn_id,
                    i_dlrt.TRADENAMES_id as Tradenames_id,
                    i_d.Drug_id
                from
                    v_DrugListRequest i_dlr 

                    left join v_DrugListRequestTorg i_dlrt  on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id

                    left join rls.v_Drug i_d  on i_d.DrugComplexMnn_id = i_dlr.DrugComplexMnn_id and (i_dlrt.TRADENAMES_id is null or i_d.DrugTorg_id = i_dlrt.TRADENAMES_id)

                where
                    i_dlr.DrugListRequest_id = :DrugListRequest_id
            )
			select
				round(cast(avg(price) as numeric(18,2)), 2) as \"AveragePrice\"
			from (
				select
                    wdss.WhsDocumentSupplySpec_PriceNDS as price
                from
                    (
                        select 
                            p.WhsDocumentSupply_id, p.WhsDocumentUc_Date
                        from
                            v_WhsDOcumentSupply p 

                        where
                            p.WhsDocumentSupply_id in (
                                select
                                    i_wds.WhsDocumentSupply_id
                                from
                                    v_WhsDocumentSupply i_wds 

                                    left join v_WhsDocumentSupplySpec i_wdss  on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id

                                    inner join drug_list i_dl  on i_dl.Drug_id = i_wdss.Drug_id or (i_wdss.Drug_id is null and i_dl.DrugComplexMnn_id = i_wdss.DrugComplexMnn_id)

                                    left join v_WhsDocumentStatusType i_wdst  on i_wdst.WhsDocumentStatusType_id = i_wds.WhsDocumentStatusType_id

                                where
                                    i_wds.WhsDocumentUc_Date <= dbo.tzGetDate() and
                                    i_wdst.WhsDocumentStatusType_Code = '2' and -- 2 - Действующий
                                    (
                                        i_wdss.DrugComplexMnn_id is null or
                                        i_wdss.DrugComplexMnn_id = :DrugComplexMnn_id
                                    )
                            )
                        order by
                            p.WhsDocumentUc_Date desc
                        limit 2
                    ) wds
                    left join v_WhsDocumentSupplySpec wdss  on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id

                where
                    exists(
                        select
                            i_dl.DrugComplexMnn_id
                        from
                            drug_list i_dl 

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
                    v_DrugRequest dr 

                    left join v_DrugRequestPurchaseSpec drps  on drps.DrugRequest_id = dr.DrugRequest_id

                where
                    dr.DrugRequest_id in (
                        select 
                            i_dr.DrugRequest_id
                        from
                            v_DrugRequest i_dr 

                            left join v_DrugRequestStatus i_drs  on i_drs.DrugRequestStatus_id = i_dr.DrugRequestStatus_id

                            left join v_DrugRequestPurchaseSpec i_drps  on i_drps.DrugRequest_id = i_dr.DrugRequest_id

                            inner join drug_list i_dl  on i_dl.DrugComplexMnn_id = i_drps.DrugComplexMnn_id

                        where
                            i_drs.DrugRequestStatus_Code = 3 and  -- 3 - Утвержденная
                            (
                                i_dl.Tradenames_id is null or
                                i_drps.TRADENAMES_id is null or
                                i_dl.Tradenames_id = i_drps.TRADENAMES_id
                            )
                        order by
                            i_dr.DrugRequest_insDT desc
                        limit 1
                    ) and
                    exists(
                        select
                            i_dl.DrugComplexMnn_id
                        from
                            drug_list i_dl 

                        where
                            i_dl.DrugComplexMnn_id = drps.DrugComplexMnn_id and (
                                i_dl.Tradenames_id is null or
                                drps.TRADENAMES_id is null or
                                i_dl.Tradenames_id = drps.TRADENAMES_id
                            )
                    ) and
                    drps.DrugComplexMnn_id = :DrugComplexMnn_id
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
			with cte as (
			select
				date_part('year', DrugRequestPeriod_begDate) - 1 as year
			from v_DrugRequestPeriod  where DrugRequestPeriod_id = (

				select
					DrugRequestPeriod_id
				from
					DrugRequestProperty 

				where
					DrugRequestProperty_id = :DrugRequestProperty_id
			))

			select
				cast(max(wdss.WhsDocumentSupplySpec_Price) as decimal(12,2)) as \"max_price\",
				cast(min(wdss.WhsDocumentSupplySpec_Price) as decimal(12,2)) as \"min_price\",
				cast((max(wdss.WhsDocumentSupplySpec_Price)+min(wdss.WhsDocumentSupplySpec_Price))/2 as decimal(12,2)) as \"avg_price\"
			from
				v_WhsDocumentSupplySpec wdss 

				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id

				left join rls.v_Drug d  on d.Drug_id = wdss.Drug_id

				left join rls.v_Prep p  on p.Prep_id = d.DrugPrep_id

			where
				wdst.WhsDocumentStatusType_Code <> '1' and -- 1 - Новый
				p.TRADENAMEID = :TRADENAMES_ID 
                and
				date_part('year', wds.WhsDocumentUc_Date) = (SELECT year FROM cte);
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
					with cte as (
					select
						DrugRequestPeriod_begDate as begDate,
						DrugRequestPeriod_endDate as endDate
					from v_DrugRequestPeriod  where DrugRequestPeriod_id = (

						select
							DrugRequestPeriod_id
						from
							v_DrugRequestProperty 

						where
							DrugRequestProperty_id = :DrugRequestProperty_id
					))

					select
						p.DrugComplexMnn_id as \"DrugComplexMnn_id\",
						COALESCE({$select_price}, 0) as \"Price\"

					from
						(
							select
								avg(n.PRICEINRUB) as Price,
								d.DrugComplexMnn_id
							from
								rls.NOMEN n	

								left join rls.v_Drug d  on d.Drug_id = n.NOMEN_ID

							where
								d.DrugComplexMnn_id in ({$data['DrugComplexMnn_List']}) and
								n.PRICEINRUB is not null and
								COALESCE(Nomen_deleted, 1) <> 2

							group by
								d.DrugComplexMnn_id
						) p
						left join rls.DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = p.DrugComplexMnn_id

						left join rls.DrugComplexMnnName  on DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

						left join rls.ACTMATTERS am  on am.ACTMATTERS_ID = DrugComplexMnnName.ActMatters_id

						LEFT JOIN LATERAL (

							select (case when COALESCE(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code

						) IsNarko ON true
						LEFT JOIN LATERAL (

							select 
								DrugMarkup_Wholesale as Wholesale,
								DrugMarkup_Retail as Retail
							from
								v_DrugMarkup dm 

								left join v_YesNo is_narko  on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug

							where
								p.Price between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
								COALESCE(is_narko.YesNo_Code, 0) = IsNarko.Code and

								(
									DrugMarkup_begDT <= (SELECT endDate FROM cte) and
									(
										DrugMarkup_endDT is null or
										DrugMarkup_endDT >= (SELECT begDate FROM cte)
									)
								)
							order by
								DrugMarkup_begDT
                            limit 1
						) DrugMarkup ON true
				";
				break;
			case 'drug_request':
				$query = "
					with cte as (
					select
						DrugRequestPeriod_begDate as begDate,
						DrugRequestPeriod_endDate as endDate,
						DrugFinance_id as DrugFinance_id,
						PersonRegisterType_id as PersonRegisterType_id
					from
						v_DrugRequestProperty drp 

						left join v_DrugRequestPeriod period  on period.DrugRequestPeriod_id = drp.DrugRequestPeriod_id

					where
						DrugRequestProperty_id = :DrugRequestProperty_id
					)
					select
						dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
						cast(COALESCE(LastDrugRequest.Price, 0) as decimal(14,2)) as \"Price\"

					from
						rls.DrugComplexMnn dcm 

						LEFT JOIN LATERAL (

							select
								case
									when
										drr.DrugRequestRow_Kolvo > 0
									then
										cast(drr.DrugRequestRow_Summa/drr.DrugRequestRow_Kolvo as decimal(14,2))
									else
										0
								end as Price
							from
								v_DrugRequest dr 

								left join v_DrugRequestRow drr  on drr.DrugRequest_id = dr.DrugRequest_id

								left join v_DrugRequestPeriod drp  on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id

							where
								dr.DrugRequest_Version is null and
								dr.PersonRegisterType_id = (SELECT PersonRegisterType_id FROM cte) and
								drp.DrugRequestPeriod_begDate < (SELECT begDate FROM cte) and
								drr.DrugFinance_id = (SELECT DrugFinance_id FROM cte) and
								drr.DrugComplexMnn_id = dcm.DrugComplexMnn_id
							order by
								drp.DrugRequestPeriod_begDate desc, dr.DrugRequest_id desc
                            limit 1
						) LastDrugRequest ON true
					where
						DrugComplexMnn_id in ({$data['DrugComplexMnn_List']});
				";
				break;
			case 'average_value':
				$query = "
					select
                        dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                        cast(COALESCE(LastDocuments.Price, 0) as decimal(14,2)) as \"Price\"

                    from
                        rls.DrugComplexMnn dcm 

                        LEFT JOIN LATERAL (

                            select
                                avg(ppp.Price) as Price
                            from (
                                select
                                    wdss.WhsDocumentSupplySpec_PriceNDS as Price
                                from
                                    (
                                        select 
                                            p.WhsDocumentSupply_id, p.WhsDocumentUc_Date
                                        from
                                            v_WhsDOcumentSupply p 

                                        where
                                            p.WhsDocumentSupply_id in (
                                                select
                                                    i_wds.WhsDocumentSupply_id
                                                from
                                                    v_WhsDocumentSupply i_wds 

                                                    left join v_WhsDocumentSupplySpec i_wdss  on i_wdss.WhsDocumentSupply_id = i_wds.WhsDocumentSupply_id

                                                    left join v_WhsDocumentStatusType i_wdst  on i_wdst.WhsDocumentStatusType_id = i_wds.WhsDocumentStatusType_id

                                                    left join rls.v_Drug i_d  on i_d.Drug_id = i_wdss.Drug_id

                                                where
                                                    i_wds.WhsDocumentUc_Date <= dbo.tzGetDate() and
                                                    i_wdst.WhsDocumentStatusType_Code = '2' and -- 2 - Действующий
                                                    (
                                                        i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id or
                                                        i_wdss.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                                    )
                                            )
                                        order by
                                            p.WhsDocumentUc_Date desc
                                        limit 2
                                    ) wds
                                    left join v_WhsDocumentSupplySpec wdss  on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id

                                    left join rls.v_Drug d  on d.Drug_id = wdss.Drug_id

                                where
                                    d.DrugComplexMnn_id = dcm.DrugComplexMnn_id or
                                    wdss.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                union all
                                select
                                    drps.DrugRequestPurchaseSpec_Price
                                from
                                    v_DrugRequest dr 

                                    left join v_DrugRequestPurchaseSpec drps  on drps.DrugRequest_id = dr.DrugRequest_id

                                where
                                    dr.DrugRequest_id in (
                                        select 
                                            i_dr.DrugRequest_id
                                        from
                                            v_DrugRequest i_dr 

                                            left join v_DrugRequestStatus i_drs  on i_drs.DrugRequestStatus_id = i_dr.DrugRequestStatus_id

                                            left join v_DrugRequestPurchaseSpec i_drps  on i_drps.DrugRequest_id = i_dr.DrugRequest_id

                                        where
                                            i_drs.DrugRequestStatus_Code = '3' and  -- 3 - Утвержденная
                                            i_drps.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                                        order by
                                            i_dr.DrugRequest_insDT desc
                                        limit 1
                                    ) and
                                    drps.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                            ) ppp
                        ) LastDocuments ON true
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
				DrugListRequest 

			where
				DrugRequestProperty_id = :OriginalDrugRequestProperty_id and
						DrugListRequest_id not in (
						select
						DrugListRequest_id
					from
						DrugListRequestTorg 

				)
		";
		$result = $this->db->query($query, $data);

		//Получаем список идентификаторов со списками торговых наименований
		$query = "
			select
				DrugListRequest_id as \"DrugListRequest_id\",
				DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DrugListRequest_Price as \"DrugListRequest_Price\",
				DrugTorgUse_id as \"DrugTorgUse_id\",
				DrugListRequest_Code as \"DrugListRequest_Code\",
				DrugListRequest_IsProblem as \"DrugListRequest_IsProblem\"
			from
				DrugListRequest 

			where
				DrugRequestProperty_id = :OriginalDrugRequestProperty_id 
                and
				DrugListRequest_id in (
					select
						DrugListRequest_id
					from
						DrugListRequestTorg 

				)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$arr = $result->result('array');
			foreach($arr as $item) {
				//Копируем конкретную позицию
				$query = "
					select DrugListRequest_id as \"DrugListRequest_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_DrugListRequest_ins(
						DrugListRequest_id := null,
						DrugRequestProperty_id := :NewDrugRequestProperty_id,
						DrugComplexMnn_id := :DrugComplexMnn_id,
				                DrugListRequest_Price := cast( case when cast( :DrugListRequest_Price as varchar ) = '' then '0' else :DrugListRequest_Price end as float ),
						DrugTorgUse_id := :DrugTorgUse_id,
						DrugListRequest_Code := :DrugListRequest_Code,
						DrugListRequest_IsProblem := :DrugListRequest_IsProblem,
						pmUser_id := :pmUser_id);


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
							DrugListRequestTorg (
								druglistrequest_id,
								tradenames_id,
								drug_id,
								orgfarmacyprice_min,
								orgfarmacyprice_max,
								drugrequestprice_min,
								drugrequestprice_max,
								drugrequest_price,
								pmuser_insid,
								pmuser_updid,
								druglistrequesttorg_insdt,
								druglistrequesttorg_upddt,
								druglistrequesttorg_isproblem
							)
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
							DrugListRequestTorg 

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
				dcmc.DrugComplexMnnCode_Code as \"DrugComplexMnn_Code\",
				(
                	select string_agg(data, ',')
                    from (
					select distinct
						SUBSTRING(ca.NAME, 1, strpos(ca.NAME, ' ')-1) as data
					from
						rls.PREP_ACTMATTERS pam 

						left join rls.PREP_ATC pa  on pa.PREPID = pam.PREPID

						inner join rls.CLSATC ca  on ca.CLSATC_ID =pa.UNIQID

					where
						pam.MATTERID = dcmn.ActMatters_id
                    ) t
				) as \"ATX_CODE_list\",
				am.STRONGGROUPID as \"STRONGGROUPID\",
				am.NARCOGROUPID as \"NARCOGROUPID\",
				cdf.NAME as \"ClsDrugForms_Name\",
				dcmd.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
				dcmf.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\",
				(
					select
						cn.NAME
					from
						rls.v_Drug d 

						left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id

						left join rls.CLSNTFR cn  on cn.CLSNTFR_ID = p.NTFRID

					where
						d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                    limit 1
				) as \"NTFR_Name\"
			from
				rls.v_DrugComplexMnn dcm 

				left join rls.DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				left join rls.DrugComplexMnnDose dcmd  on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id

				left join rls.DrugComplexMnnFas dcmf  on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id

				left join rls.CLSDRUGFORMS cdf  on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID

				left join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = dcmn.ActMatters_id

				LEFT JOIN LATERAL (

					select 
						DrugComplexMnnCode_Code
					from
						rls.v_DrugComplexMnnCode 

					where
						v_DrugComplexMnnCode.DrugComplexMnn_id = dcm.DrugComplexMnn_id
                    limit 1
				) dcmc ON true
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
				min(p.Price) as \"MinPrice\",
				max(p.Price) as \"MaxPrice\",
				min(p.Wholesale_Price) as \"Wholesale_MinPrice\",
				max(p.Wholesale_Price) as \"Wholesale_MaxPrice\"
			from (
				select
					n.PRICEINRUB as Price,
					cast(n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2) as decimal(12,2)) as Wholesale_Price --Предельно допустимая оптовая цена без НДС (руб.)
				from
					rls.v_Nomen n 

					left join rls.v_prep p  on p.Prep_id = n.PREPID

					left join rls.v_Drug d  on d.Drug_id = n.NOMEN_ID

					left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

					left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

					left join rls.v_ACTMATTERS am  on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id

					LEFT JOIN LATERAL (

						select (case when COALESCE(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code

					) IsNarko ON true
					LEFT JOIN LATERAL (

						select
							DrugMarkup_Wholesale as Wholesale
						from
							v_DrugMarkup dm 

							left join v_YesNo is_narko  on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug

						where
							n.PRICEINRUB between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
							COALESCE(is_narko.YesNo_Code, 0) = IsNarko.Code and

							(
								n.PRICEDATE is null or (
									DrugMarkup_begDT <= n.PRICEDATE and
									(
										DrugMarkup_endDT is null or
										DrugMarkup_endDT >= n.PRICEDATE
									)
								)
							)
					) DrugMarkup ON true
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
				$filters[] = "o.Org_Nick iLIKE :query or o.Org_Name iLIKE :query";

				$params['query'] = "%".$data['query']."%";
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
        	select 
        		o.Org_id as \"Org_id\",
        		o.Org_Name as \"Org_Name\"
			from
				v_Org o 

				left join v_OrgType ot  on ot.OrgType_id = o.OrgType_id

				{$where}
			order by
				o.Org_Name
	        limit 500
    	";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}