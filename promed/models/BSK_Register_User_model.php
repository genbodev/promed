<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Ufa_BSK_Register_User_model - молеь для работы с данными БСК (Башкирия)
 * пользовательская  часть 
 * 
 * @package			BSK
 * @author			Васинский Игорь 
 * @version			01.12.2014
 */

class BSK_Register_User_model extends swModel
{
    
    var $scheme = "dbo";    
    var $listMorbusType = '(84,88,89,50,19)';
	
    /**
     * comments
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает список правил для входящих данных
     */
    function getInputRules($data) {
        switch($data) {
            case 'saveInOKS':
                return array(
                    array(
                        'field' => 'Registry_method',
                        'label' => 'Метод',
                        'rules' => 'trim',
                        'type' => 'string'
                    ),
                    array(
                        'field' => 'Person_id',
                        'label' => 'Person_id',
                        'rules' => '',
                        'type' => 'int'
                    ),
                    array(
                        'field' => 'CmpCallCard_id',
                        'label' => 'Идентификатор карты вызова',
                        'rules' => '',
                        'type' => 'int'
                    ),
                     array(
                        'field' => 'ArrivalDT',
                        'label' => 'Время прибытия к больному',
                        'rules' => '',
                        'type' => 'string',
                    ),  
                     array(
                        'field' => 'PainDT',
                        'label' => 'Время начала болевых симптомов',
                        'rules' => '',
                        'type' => 'string',
                    ),
                     array(
                        'field' => 'ECGDT',
                        'label' => 'Время проведения ЭКГ',
                        'rules' => '',
                        'type' => 'string',
                    ),  
                     array(
                        'field' => 'ResultECG',
                        'label' => 'Результат ЭКГ',
                        'rules' => '',
                        'type' => 'string',
                    ),  
                     array(
                        'field' => 'TLTDT',
                        'label' => 'Время проведения ТЛТ',
                        'rules' => '',
                        'type' => 'string',
                    ),
                     array(
                        'field' => 'FailTLT',
                        'label' => 'Причина отказа от ТЛТ',
                        'rules' => '',
                        'type' => 'string',
                    ),
                     array(
                        'field' => 'LpuDT',
                        'label' => 'Время прибытия в медицинскую организацию',
                        'rules' => '',
                        'type' => 'string',
                    ),  
                     array(
                        'field' => 'AbsoluteList',
                        'label' => 'Абсолютные противопоказания к проведению ТЛТ',
                        'rules' => '',
                        'type' => 'string', //json
                    ),      
                     array(
                        'field' => 'RelativeList',
                        'label' => 'Относительные противопоказания к проведению ТЛТ',
                        'rules' => '',
                        'type' => 'string', //json
                    ),  
                    array(
                        'field' => 'ZonaMO',
                        'label' => 'Зона ответственности МО',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'ZonaCHKV',
                        'label' => 'Зона ответственности проведения ЧКВ',
                        'rules' => '',
                        'type' => 'string'
                    ),     
                    array(
                        'field' => 'MorbusType_id',
                        'label' => 'окс=19',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'Diag_id',
                        'label' => 'Код диагноза',
                        'rules' => '',
                        'type' => 'int'
                    ),     
                    array(
                        'field' => 'DiagOKS',
                        'label' => 'код+диагноз',
                        'rules' => '',
                        'type' => 'string'
                    ),                    
                    array(
                        'field' => 'MOHospital',
                        'label' => 'МО госпитализации',
                        'rules' => '',
                        'type' => 'string'
                    ), 
                    /**
                     *  дополнительно
                     */ 
                    array(
                        'field' => 'MedStaffFact_num',
                        'label' => 'Номер фельдшера принявшего вызов',
                        'rules' => '',
                        'type' => 'string'
                    ),                  
                    array(
                        'field' => 'LpuBuilding_name',
                        'label' => 'Станция (подстанция), отделения',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'EmergencyTeam_number',
                        'label' => 'Бригада скорой медицинской помощи',
                        'rules' => '',
                        'type' => 'string'
                    ),   
                    array(
                        'field' => 'AcceptTime',
                        'label' => 'Время приема вызова',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'TransTime',
                        'label' => 'Передача вызова бригаде СМП',
                        'rules' => '',
                        'type' => 'string'
                    ), 
                    array(
                        'field' => 'GoTime',
                        'label' => 'Время выезда на вызов',
                        'rules' => '',
                        'type' => 'string'
                    ),   
                    array(
                        'field' => 'TransportTime',
                        'label' => 'Начало транспортировки больного',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'EndTime',
                        'label' => 'Время отзвона / Окончание вызова',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'BackTime',
                        'label' => 'Время возвращения на подстанцию',
                        'rules' => '',
                        'type' => 'string'
                    ),  
                    array(
                        'field' => 'SummTime',
                        'label' => 'Время, затраченное, на выполнение вызова',
                        'rules' => '',
                        'type' => 'string'
                    ),
					array(
						'field' => 'UslugaTLT',
						'label' => 'Проведение ТЛТ (код услуги)',
						'rules' => '',
						'type' => 'string'
					),
					array(
						'field' => 'TLTres',
						'label' => 'Проведение ТЛТ',
						'rules' => '',
						'type' => 'string'
					)
                );
        }
    }

    //https://redmine.swan.perm.ru/issues/88153
    /**
     * данные для ЭМК
     */    
    function getDataForEMK($data){
        $params = array(
            'Person_id'=>$data['Person_id']
        );
        
        $query = "  select 
                        MT.MorbusType_Name,
                        R.MorbusType_id,
                        O.BSKObject_id,
                        case when R.MorbusType_id = 84 then 'Группа риска: ' + cast(R.BSKRegistry_riskGroup as varchar(2))
                             when R.MorbusType_id = 88 then 'Функциональный класс: ' + cast(R.BSKRegistry_riskGroup as varchar(2))
                                 when R.MorbusType_id = 89 then 'Степень риска: ' + cast(R.BSKRegistry_riskGroup as varchar(2))
                        else ''
                        end  as BSKRegistry_riskGroup,
                        substring(convert(varchar,R.BSKRegistry_insDT,120),1,10) as BSKRegistry_insDT,
                        R.pmUser_insID 
                    from dbo.BSKRegistry R 
                    left join dbo.MorbusType MT on MT.MorbusType_id = R.MorbusType_id
                    left join dbo.BSKObject O on R.MorbusType_id = O.MorbusType_id
                    where R.Person_id = :Person_id and R.MorbusType_id != 19
                    order by R.BSKRegistry_insDT";
     
        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }           
    }    
    /**
     * Получение краткой информации о всех ПН пациента в ергистре БСК
     */
    function getInfoForPacientOnBSKRegistry($data){
        $params = array(
            'Person_id'=>$data['Person_id']
        );
        
        $query = "select 
                        PR.Person_id,
                        MT.MorbusType_id,
                        MT.MorbusType_Name,
                        case when PR.MorbusType_id in (110,111,112) then convert(varchar(10), PR.PersonRegister_setDate, 104) else convert(varchar(10), BSKRegistry.BSKRegistry_setDate, 104) end BSKRegistry_setDate,
						convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
						convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate,
						isnull(convert(varchar(10),BSKRegistry.BSKRegistry_nextDate,104),case 
							when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then convert(varchar(10), (dateadd(MONTH, 18, BSKRegistry.BSKRegistry_setDate)), 104)
							when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then convert(varchar(10), (dateadd(MONTH, 12, BSKRegistry.BSKRegistry_setDate)), 104)
							when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then convert(varchar(10), (dateadd(MONTH, 6,BSKRegistry.BSKRegistry_setDate)), 104)
							when MT.MorbusType_id = 50 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
							when MT.MorbusType_id = 89 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
							when MT.MorbusType_id = 88 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
							end) as BSKRegistry_setDateNext,
						 case 
							when MT.MorbusType_id = 84 then cast(BSKRegistry.BSKRegistry_riskGroup as varchar(10))
							when MT.MorbusType_id = 89 then 
									(select isnull(elVal.BSKObservElementValues_data,RD.BSKRegistryData_data) BSKRegistryData_data
									from dbo.v_BSKRegistryData RD with (nolock)
									left join dbo.v_BSKObservElementValues elVal with (nolock) on elVal.BSKObservElementValues_id = RD.BSKObservElementValues_id
									where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269)
							when MT.MorbusType_id = 88 then 
									(select isnull(elVal.BSKObservElementValues_data,RD.BSKRegistryData_data) BSKRegistryData_data
									from dbo.v_BSKRegistryData RD with (nolock)
									left join dbo.v_BSKObservElementValues elVal with (nolock) on elVal.BSKObservElementValues_id = RD.BSKObservElementValues_id
									where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151)
							else NULL end as BSKRegistry_riskGroup,
                        --BSKRegistry.BSKRegistry_riskGroup,
						BSKRegistry.BSKRegistry_id
                from dbo.PersonRegister PR with (nolock)
                left join dbo.MorbusType MT  with (nolock) on MT.MorbusType_id = PR.MorbusType_id
                outer apply(
                 select top 1 
                        R.BSKRegistry_setDate,
                        R.BSKRegistry_riskGroup ,
						R.BSKRegistry_id,
						R.BSKRegistry_nextDate
                 from dbo.BSKRegistry R with (nolock) 
                 where R.MorbusType_id = MT.MorbusType_id and R.Person_id = :Person_id

                 order by R.BSKRegistry_setDate DESC
                ) as BSKRegistry
                where PR.MorbusType_id in (84,88,89,19,50,110,111,112,113)
                and PR.Person_id = :Person_id
            ";
        
        //echo getDebugSql($query, $params);
        //exit;      

        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }          
    }   
    
    /**
     * Получение роста, веса, имт с последней анкеты
     */
    function getLastAnketData($data){
        $params = array(
            'Person_id'=>$data['Person_id']
        );
        
        $query = "select *  from dbo.getLastAnketData (:Person_id)";
        
        //echo getDebugSql($query, $params);
        //exit;      

        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }          
    }   
    
    /**
     *  Список возможных МО для маршрутизации при ОКС
     */    
    function getMOforOKS(){
        $params = array();
        
        $query = "
            WITH Lpu_CTE (Lpu_id)
            as 
            (
            	select AMO.withSTLpu_id as Lpu_id from dbo.AreaofResponsibilityMO AMO with(nolock) where isnull(AMO.withSTLpu_id, 0) > 0
            	union all
            	select AMO.nwithoutSTLpu_id as Lpu_id from dbo.AreaofResponsibilityMO AMO with(nolock) where isnull(AMO.nwithoutSTLpu_id, 0) > 0
            	union all
            	select AMO.CHKVLpu_id as Lpu_id from dbo.AreaofResponsibilityMO AMO with(nolock) where isnull(AMO.CHKVLpu_id, 0) > 0
            )
            select 
            	Lpu.Lpu_id, 
            	Lpu.Org_Nick 
            from Lpu_CTE with(nolock)
            left join dbo.v_Lpu Lpu with (nolock) on Lpu.Lpu_id = Lpu_CTE.Lpu_id
            group by Lpu.Lpu_id, Lpu.Org_Nick 
            order by replace('ГБУЗ РБ', '', Lpu.Org_Nick)         
        "; 
        
        //echo getDebugSql($query, $params);
        //exit;      

        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }         
    }

	/**
	 * @param $data
	 * @return array|false
	 * Данные из регистра БСК на конкретную карту вызова СМП
	 */
	function getOKSdata($data) {
		$params = [
			'CmpCallCard_id' => $data['CmpCallCard_id']
		];

		$BSKRegistry_id = $this->getFirstResultFromQuery("
			select top 1 BSKRegistry_id
			from dbo.v_BSKRegistryData with(nolock)
			where BSKObservElement_id = 303
				and cast(BSKRegistryData_AnswerInt as varchar) = :CmpCallCard_id
		", $params, true);

		if (empty($BSKRegistry_id)) {
			$BSKRegistry_id = $this->getFirstResultFromQuery("
				select top 1 BSKRegistry_id
				from dbo.v_BSKRegistryData with(nolock)
				where BSKObservElement_id = 303
					and BSKRegistryData_data = :CmpCallCard_id
			", $params, true);
		}

		if (!empty($BSKRegistry_id)) {
			$params['BSKRegistry_id'] = $BSKRegistry_id;

			return $this->queryResult("  
				select
					case 
						when BSKObservElement_id = 270 then 'ArrivalDT' 
						when BSKObservElement_id = 271 then 'PainDT'
						when BSKObservElement_id = 272 then 'ECGDT'
						when BSKObservElement_id = 273 then 'ResultECG'
						when BSKObservElement_id = 274 then 'TLTDT'
						when BSKObservElement_id = 275 then 'FailTLT'
						when BSKObservElement_id = 276 then 'LpuDT'
						when BSKObservElement_id = 300 then 'DiagECG'
						when BSKObservElement_id = 303 then 'CmpCallCard_id'
						when BSKObservElement_id between 277 and 286 then 'AbsoluteList'
						when BSKObservElement_id between 287 and 299 then 'RelativeList'
						when BSKObservElement_id = 301 then 'ZonaMO'
						when BSKObservElement_id = 302/*414*/ then 'ZonaCHKV'
						when BSKObservElement_id = 304 then 'MOHospital'
						when BSKObservElement_id = 305 then 'MedStaffFact_num'
					end as formName, 
					case when (BSKRegistryData_data like 'Да%') and (BSKObservElement_id between 277 and 299) then 1 else 0 end as checked,
					case when right(BSKRegistryData_data, 1) != '.'  and BSKObservElement_id between 277 and 299 then 1 else 0 end as isDoctor,
					rd.BSKRegistryData_id,
					rd.BSKRegistry_id,
					rd.BSKObservElement_id,
					coalesce(cast(rd.BSKRegistryData_AnswerInt as varchar),convert(varchar(16), rd.BSKRegistryData_AnswerDT, 120),rd.BSKRegistryData_AnswerText,rd.BSKRegistryData_data,'') BSKRegistryData_data,
					rd.BSKUnits_name,
					rd.BSKRegistryData_insDT,
					rd.BSKRegistryData_updDT,
					rd.pmUser_insID,
					rd.pmUser_updID,
					rd.BSKRegistryData_deleted
				from dbo.BSKRegistryData rd with(nolock)
					cross apply (
						select MAX(rdM.BSKRegistryData_id) BSKRegistryData_idMax
						from dbo.v_BSKRegistryData rdM with (nolock)
						where rdM.BSKRegistry_id = rd.BSKRegistry_id
							and rdM.BSKObservElement_id = rd.BSKObservElement_id
					) rdM
				where rd.BSKRegistry_id = :BSKRegistry_id 
					and rd.BSKRegistryData_id = rdM.BSKRegistryData_idMax
				order by rd.BSKObservElement_id
			", $params);
		}

		return [];
	}

    /**
     *  Проверка наличия данных по ОКС для пациента на дату
     */ 
    function checkInOKS($data){
        //$data['Person_id'] = 2586402;
        //$data['BSKRegistry_setDate'] = '2016-02-04';
        //echo '<pre>' . print_r($data,1) . '</pre>';
        $params = array(
            'Person_id'=>$data['Person_id'],
            'BSKRegistry_setDate'=>$data['BSKRegistry_setDate']
        );
        
        $query = "
            select top 1
            R.BSKRegistry_id,
            R.Person_id,
            R.MorbusType_id,
            convert(varchar(10), R.BSKRegistry_setDate,120) as BSKRegistry_setDate
            from dbo.BSKRegistry R with(nolock)
                inner join dbo.BSKRegistryData BRD on BRD.BSKRegistry_id = r.BSKRegistry_id and BRD.BSKObservElement_id = 398 and BRD.BSKRegistryData_data is null
            where (1=1) and
            R.Person_id = :Person_id
            and R.MorbusType_id = 19
            and convert(varchar(10), R.BSKRegistry_setDate,120) = :BSKRegistry_setDate       
        ";
        
        $result = $this->db->query($query, $params);               
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }         
    }
    /**
    * Получение Person_id по CmpCallCard_id
    */
    function getRecomendationOKS($data){
        $params = array(
            'BSKObservRecomendation_id'=>$data['BSKObservRecomendation_id']
        );
        
        $query = "
            select  
            BSKObservRecomendation_id,
            BSKObservRecomendation_text,
            case 
               when BSKObservRecomendation_id = 310 then 'Рекомендуемый алгоритм действий на догоспитальном этапе при выявлении острого коронарного синдрома без подъема сегмента ST (OKCбпST) для бригад СМП'
               when BSKObservRecomendation_id = 311 then 'Рекомендуемый алгоритм действий врача СМП при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
               when BSKObservRecomendation_id = 312 then 'Рекомендуемый алгоритм действий фельдшера СМП при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
               when BSKObservRecomendation_id = 313 then 'Рекомендуемый алгоритм действий врача поликлиники при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
            end as forWhom
            from 
                dbo.BSKObservRecomendation with(nolock)
            where 
                BSKObservRecomendation_id  in(310, 311, 312, 313) 
        ";

        
        //echo getDebugSql($query, $params);
        //exit;      

        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }       
    }
    /**
     *  Отличить врача от фельдшера смп
     */ 
    function getDolgnost($data){
        
        $params = array(
         'MedPersonal_id'=>$data['MedPersonal_id']
        );
        
        $query = "
            select Dolgnost_Name  from v_MedPersonal with (nolock) where MedPersonal_id = :MedPersonal_id
        ";
        
        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }         
    }    
        
    /**
    * Получение Person_id по CmpCallCard_id
    */
    function getPerson_id($data){
        $params = array(
            'CmpCallCard_id'=>$data['CmpCallCard_id']
        );
        
        $query = "
            select top 1 
                Person_id 
            from 
                dbo.CmpCallCard with (nolock) 
            where 
                CmpCallCard_id = :CmpCallCard_id
        ";

        
        //echo getDebugSql($query, $params);
        //exit;      

        $result = $this->db->query($query, $params);        
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }     
    }
    
    /**
     *  Запись в регистр БСК в ПН ОКС с АРМ Админситратора СМП / ... / Подстанции СМП
     */        
    function saveInOKS($data){
		$IsMainServer = $this->config->item('IsMainServer');
        $params = array(
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id']
        );
        //echo '<pre>' . print_r($params, 1) . '</pre>';
        if(is_null($data['Person_id']) || $data['Person_id'] == ''){
            return array('Error_Msg' => 'Ошибка сохранения ОКС! Не определен пациент!');
        }

        //Проверить наличия пациента в dbo.PersonRegister
        if($data['Person_id'] !=0 && $this->checkPersonInRegister($params) !== false){
            //Записать пациента в dbo.PersonRegister с MorbusType = 19
            $params = array(  
                'Person_id' => $data['Person_id'],
                'MorbusType_id' => $data['MorbusType_id'],
                'Diag_id' => $data['Diag_id'],
                'PersonRegister_Code' =>null,
                'PersonRegister_setDate' => date('Y-m-d H:i:s'),
                'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? date('Y-m-d', strtotime('+1 year')) : null,
                'Morbus_id' => null,
                'PersonRegisterOutCause_id' => null,
                'MedPersonal_iid' => null,
                'Lpu_iid' => null,
                'MedPersonal_did' => null,
                'Lpu_did' =>null,
                'EvnNotifyBase_id' => null,
                'pmUser_id' => $data['pmUser_id'],
                'PersonRegister_id'=>null
            );


			if($IsMainServer === true) {
				//проверяем подключение к СМП
				unset($this->db);

				try{
					$this->load->database('smp');
				} catch (Exception $e) {
					$this->load->database();
					$errMsg = "Нет связи с сервером: сохранение ОКС недоступно";
					$this->ReturnError($errMsg);
					return false;
				}

				$this->saveInPersonRegister($params);
				//возвращаемся на рабочую
				unset($this->db);
				$this->load->database();

			}else{
				$this->saveInPersonRegister($params);
			}

         }

         $registryData = array(
            'setDate'=>date('Y-m-d H:s'),
            'Person_id'=>$data['Person_id'],
            'MorbusType_id'=>$data['MorbusType_id'],
            'riskGroup'=>null,
            'pmUser_id'=>$data['pmUser_id'],
            'ListAnswers'=>array(
                //Время прибытия к пациенту
                270=>array(
                        'BSKObservElement_id'=>270,
                        'BSKRegistryData_data'=>$data['ArrivalDT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1                        
                ),
                //Время начала болевых симптомов
                271=>array(
                        'BSKObservElement_id'=>271,
                        'BSKRegistryData_data'=>$data['PainDT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),   
                //Время проведения ЭКГ
                272=>array(
                        'BSKObservElement_id'=>272,
                        'BSKRegistryData_data'=>$data['ECGDT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                //Результат ЭКГ
                273=>array(
                        'BSKObservElement_id'=>273,
                        'BSKRegistryData_data'=>$data['ResultECG'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                //Результат проведения ТЛТ
                274=>array(
                        'BSKObservElement_id'=>274,
                        'BSKRegistryData_data'=>$data['TLTDT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),
                //Причина отказа от ТЛТ
                275=>array(
                        'BSKObservElement_id'=>275,
                        'BSKRegistryData_data'=>$data['FailTLT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ), 
                //Время прибытия в МО
                276=>array(
                        'BSKObservElement_id'=>276,
                        'BSKRegistryData_data'=>$data['LpuDT'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),    
                //Зона ответственности МО
                301=>array(
                        'BSKObservElement_id'=>301,
                        'BSKRegistryData_data'=>$data['ZonaMO'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ), 
                //Зона ответственности ЧКВ
                414=>array(
                        'BSKObservElement_id'=>414,
                        'BSKRegistryData_data'=>$data['ZonaCHKV'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),
                //Диагноз
                300=>array(
                        'BSKObservElement_id'=>300,
                        'BSKRegistryData_data'=>$data['DiagOKS'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),     
                //CmpCallCard_id
                303=>array(
                        'BSKObservElement_id'=>303,
                        'BSKRegistryData_data'=>$data['CmpCallCard_id'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),   
                //MOHospital
                304=>array(
                        'BSKObservElement_id'=>304,
                        'BSKRegistryData_data'=>$data['MOHospital'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ), 
                
                /**
                 *  дополнительно
                 */ 
                305=>array(
                        'BSKObservElement_id'=>305,
                        'BSKRegistryData_data'=>$data['MedStaffFact_num'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),
                306=>array(
                        'BSKObservElement_id'=>306,
                        'BSKRegistryData_data'=>$data['LpuBuilding_name'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ), 
                307=>array(
                        'BSKObservElement_id'=>307,
                        'BSKRegistryData_data'=>$data['EmergencyTeam_number'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                308=>array(
                        'BSKObservElement_id'=>308,
                        'BSKRegistryData_data'=>$data['AcceptTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                309=>array(
                        'BSKObservElement_id'=>309,
                        'BSKRegistryData_data'=>$data['TransTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                310=>array(
                        'BSKObservElement_id'=>310,
                        'BSKRegistryData_data'=>$data['GoTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                311=>array(
                        'BSKObservElement_id'=>311,
                        'BSKRegistryData_data'=>$data['TransportTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                312=>array(
                        'BSKObservElement_id'=>312,
                        'BSKRegistryData_data'=>$data['EndTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),  
                313=>array(
                        'BSKObservElement_id'=>313,
                        'BSKRegistryData_data'=>$data['BackTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),     
                314=>array(
                        'BSKObservElement_id'=>314,
                        'BSKRegistryData_data'=>$data['SummTime'],
                        'BSKUnits_name'=>null,
                        'BSKRegistryData_insDT'=>"getdate()",
                        'pmUser_insID'=>$data['pmUser_id'],
                        'pmUser_updID'=>null,
                        'BSKRegistryData_deleted'=>1   
                ),
				415=>array(
						'BSKObservElement_id'=>415,
						'BSKRegistryData_data'=>$data['TLTres'],
						'BSKUnits_name'=>null,
						'BSKRegistryData_insDT'=>"getdate()",
						'pmUser_insID'=>$data['pmUser_id'],
						'pmUser_updID'=>null,
						'BSKRegistryData_deleted'=>1
				),
				416=>array(
						'BSKObservElement_id'=>416,
						'BSKRegistryData_data'=>$data['UslugaTLT'],
						'BSKUnits_name'=>null,
						'BSKRegistryData_insDT'=>"getdate()",
						'pmUser_insID'=>$data['pmUser_id'],
						'pmUser_updID'=>null,
						'BSKRegistryData_deleted'=>1
				)
            )
         );
         
         /**
          *  Противопоказания к проведения ТЛТ
          */ 
                
         //абсолютные противопоказания
         $AbsoluteList = json_decode($data['AbsoluteList']);
         //относительны противопоказания
         $RelativeList = json_decode($data['RelativeList']);    
         
         //echo '<pre>' . print_r($AbsoluteList, 1) . '</pre>';                
         //echo '<pre>' . print_r($RelativeList, 1) . '</pre>';         
         
        foreach($AbsoluteList as $k=>$v){
             $registryData['ListAnswers'][$this->getBSKObservElement_id($k)] = array(
                                                                                      'BSKObservElement_id'=>$k,
                                                                                      'BSKRegistryData_data'=>$v,
                                                                                      'BSKUnits_name'=>null,
                                                                                      'BSKRegistryData_insDT'=>"getdate()",
                                                                                      'pmUser_insID'=>$data['pmUser_id'],
                                                                                      'pmUser_updID'=>null,
                                                                                      'BSKRegistryData_deleted'=>1                                                                                      
                                                                                    );   
        }   
         
        foreach($RelativeList as $k=>$v){
             $registryData['ListAnswers'][$this->getBSKObservElement_id($k)] = array(
                                                                                      'BSKObservElement_id'=>$k,
                                                                                      'BSKRegistryData_data'=>$v,
                                                                                      'BSKUnits_name'=>null,
                                                                                      'BSKRegistryData_insDT'=>"getdate()",
                                                                                      'pmUser_insID'=>$data['pmUser_id'],
                                                                                      'pmUser_updID'=>null,
                                                                                      'BSKRegistryData_deleted'=>1
                                                                                    );   
        }       
         //echo '<pre>' . print_r($data, 1) . '</pre>';          
         // echo '<pre>' . print_r($registryData, 1) . '</pre>';
         // return; 


        if(!empty($data['CmpCallCard_id'])) {
            $bskparams = [
                'CmpCallCard_id' => $data['CmpCallCard_id']
            ];
            $BSKRegistry_id = $this->isExistObjectRecord('BSKRegistry',$bskparams);
            $data['Registry_method'] = !empty($BSKRegistry_id) ? $BSKRegistry_id : 'ins';
        }

        if($data['Registry_method'] == 'ins') {
            // добавляемe
            $result = $this->saveRegistry($data, $registryData);
        } else {
            // обноляем
            $result = $this->updateRegistry($data, $registryData);
        }

        //echo '<pre>' . print_r($result, 1) . '</pre>'; exit;
        
        return $result;
	}

    /**
     *  Метод получения Lpu_id по улицы и номеру дома - для Уфы
     */
    function getLpu_id($data){
        
        $params = array(
            'KLStreet_id'=>$data['KLStreet_id'], //'АЙСКАЯ УЛ',1037164
            'LpuRegionStreet_HouseSet'=>$data['LpuRegionStreet_HouseSet'] //'69/2А'
        );
        
        //echo '<pre>' . print_r($params, 1) . '</pre>';
        
        $query = "
			select
                LR.Lpu_id,
				--LpuRegionStreet.Server_id,
				LpuRegionStreet_id,
				LpuRegionStreet.LpuRegion_id,
				LpuRegionStreet.KLCountry_id,
				KLRGN_id,
				--KLSubRGN_id,
				KLCity_id,
				--LpuRegionStreet.KLTown_id,
				case IsNULL(LpuRegionStreet.KLTown_id,'')
				when '' then RTrim(c.KLArea_Name)+' '+cs.KLSocr_Nick
				else RTrim(t.KLArea_Name)+' '+ts.KLSocr_Nick
				end as KLTown_Name,
				LpuRegionStreet.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				LpuRegionStreet_HouseSet
			from LpuRegionStreet with (nolock)
			left join KLArea t with (nolock) on t.KLArea_id = LpuRegionStreet.KLTown_id
			left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = LpuRegionStreet.KLStreet_id
			left join KLArea c with (nolock) on c.Klarea_id = LpuRegionStreet.KLCity_id
			left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
			left join v_LpuRegion LR with (nolock) on LpuRegionStreet.LpuRegion_id = LR.LpuRegion_id
			WHERE
			(1=1)            
		    and KLStreet.KLStreet_id = :KLStreet_id   
            and KLCity_id =   244440    
       
        ";
                 
        //echo getDebugSql($query, $params);
        //exit;
        
        $result = $this->db->query($query, $params);
        
        
        $listDB = $result->result('array');
        
        //echo '<pre>' . print_r($listDB, 1) . '</pre>';
        
        $HouseSet = preg_replace("#([0-9]*).*#", "$1", $params['LpuRegionStreet_HouseSet']);
        
        foreach($listDB as $k=>$h){
            $houses = explode(',', $h['LpuRegionStreet_HouseSet']);
            
            //echo $h['Lpu_id'].' '.$h['KLStreet_Name'].' '.$h['LpuRegionStreet_HouseSet'].'<br/>';
            
            //Если улица совпала, но дома не указаны - значит вся улица в зоне ответственности данного МО
            if(empty($houses) && $h['KLStreet_Name'] == $params['KLStreet_FullName']){
                $Lpu_id = $h['Lpu_id'];
                //echo '<pre>' . print_r($h, 1) . '</pre>';
                break;  
            }
            elseif(in_array($params['LpuRegionStreet_HouseSet'], $houses)){
                  $Lpu_id = $h['Lpu_id'];
                  
                  //echo '<pre>1' . print_r($h, 1) . '</pre>';
                  break;
            }
            elseif(in_array($HouseSet, $houses)){
                  $Lpu_id = $h['Lpu_id'];
                  
                  //echo '<pre>2' . print_r($h, 1) . '</pre>';
                  break;                
            }
            else{
                foreach($houses as $v=>$house){

                    if(preg_match("#\-#", $house)){
                        $tmp = explode("-", $house);
                        $listHouses = range($tmp[0],$tmp[1]);
                        //echo '<pre>' . print_r($listHouses, 1) . '</pre>';
                        if(in_array($HouseSet, $listHouses)){
                            $Lpu_id = $h['Lpu_id'];
                            //echo '<pre>3' . print_r($h, 1) . '</pre>';
                            break; 
                        }
                    }
                }
            }
            
             

        }
        
        //echo '<pre><h1>' . print_r($Lpu_id, 1) . '</pre>';
        $Lpu_id = isset($Lpu_id) ? $Lpu_id : false;     
        
        //echo getDebugSql($query, $params);
        //exit;              
        
        //echo '<pre>' . print_r($Lpu_id, 1) . '</pre>';
        return array(array('Lpu_id'=>$Lpu_id));
        /*
        if (is_object($result)) {
            $result->result('array');
        } else {
            return false;
        } 
        */               
    } 
    /**
     *  График дежурств специализированных медицинских организаций (РСЦ)  в г. Уфа по госпитализации больных с ОКС  с подъемом сегмента ST
     */ 
    function scheduleMO(){
        switch(date("N")){
            case 1:   $Lpu_id = 171; break;
            case 2:   $Lpu_id = 21;  break;
            case 3:   $Lpu_id = 78;  break;
            case 4:   $Lpu_id = 336; break;
            case 5:   $Lpu_id = 171; break;
            case 6:   $Lpu_id = 21;  break;
            case 7:   $Lpu_id = 78;  break;
            default : $Lpu_id = 78;  break;
        }
        
        return $Lpu_id;
    }
    
    /**
     * Получение наименований МО для зоны ответственности при ОКС
     */       
    function getResponsibilityMOZone($data){
        if(is_numeric($data['EvnEKG_rezEKG'])){
            $params = array(
                'KLStreet_id'=>$data['KLStreet_id'],
                'KLArea_id'=>$data['KLArea_id'],
                'KLSubRgn_id'=>$data['KLSubRgn_id'],
                'EvnEKG_rezEKG'=>$data['EvnEKG_rezEKG']
            );
    		
    		$ufaAddWhere = array();
    		
    		if(empty($data['KLSubRgn_id'])){
    			//город какой-то
    			$ufaAddWhere[] = "(armo.KLArea_id = :KLArea_id)";
    			//город УФАлей по нему ищем еще и район города
    			if( $data['KLArea_id'] == '244440' && !empty($data['KLStreet_id']) ){
    				$ufaAddWhere[] = "street.KLStreet_id = :KLStreet_id AND armo.PersonSprTerrDop_id = pstp.PersonSprTerrDop_id";
    			}			
    		}
    		else{
    			$ufaAddWhere[] = "(armo.KLArea_id = :KLArea_id OR armo.KLSubRgn_id = :KLSubRgn_id)";
    		}
    		
    		//если результат ЭКГ в интервале 1-5(код) выбираем withSTLpu_id
    		//инече выбираем nwithoutSTLpu_id
    		$query = "
    			SELECT DISTINCT
    			CASE WHEN (:EvnEKG_rezEKG in(1,2,3,4,5))
    				THEN LpuWithSt.Lpu_Nick
    				ELSE LpuWithoutSt.Lpu_Nick
    			END as LpuMO_Nick,
    			LpuChkv.Lpu_Nick as LpuChkv_Nick
    			FROM dbo.AreaofResponsibilityMO as armo
    				LEFT JOIN v_KLArea as area on (area.KLArea_id = armo.KLArea_id)
    				LEFT JOIN v_KLStreet as street on (street.KLArea_id = area.KLArea_id)
    				LEFT JOIN v_PersonSprTerrDop as pstp on (pstp.KLAdr_Ocatd = street.KLAdr_Ocatd)
    				LEFT JOIN v_Lpu as LpuWithSt on(LpuWithSt.Lpu_id = armo.withSTLpu_id)
    				LEFT JOIN v_Lpu as LpuWithoutSt on(LpuWithoutSt.Lpu_id = armo.nwithoutSTLpu_id)
    				LEFT JOIN v_Lpu as LpuChkv on(LpuChkv.Lpu_id = armo.CHKVLpu_id)
    				" . ImplodeWherePH($ufaAddWhere) . "
    				AND ( armo.AreaofResponsibilityMO_WeekDay IS NULL OR armo.AreaofResponsibilityMO_WeekDay = (DATEPART ( dw , dbo.tzGetDate() )-1) )
    		";

    		$result = $this->db->query($query, $params);
    		//var_dump(getDebugSQL($query, $params)); exit;
            if (is_object($result)) {
                $response = $result->result('array');
    			
                return $response;
            } else {
                return false;
            }
            //echo '<pre>' . print_r($params, 1) . '</pre>';
            /**
             *  Задача - определить зоны ответственности МО по справочнику маршрутизации [dbo].[AreaofResponsibilityMO]
             *  если указан KLSubRgn_id -  то поиск относительно района 
             *  если KLArea_id указан, но не Уфа - значит поиск по районным центрам
             *  если KLArea_id Уфы, то для начала необходимо определить зону ответственности по улице и номеру дома, потом по справочнику
             */ 
             /*
            if($data['KLSubRgn_id'] != 0){
                $withSTLpu_id = in_array($data['KLSubRgn_id'], array(99,109,110,11,112,149,120,125,130,135,137,97,144,145)) ? $this->scheduleMO() : 'ARMO.withSTLpu_id';
                
                $query = "
                     select 
                        (select Lpu_Name from dbo.getLpu_Name({$withSTLpu_id})) as withSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name
                    from dbo.AreaofResponsibilityMO ARMO with(nolock)
                    where ARMO.KLSubRgn_id = :KLSubRgn_id
                ";
            }
            elseif($data['KLSubRgn_id'] == 0 && !in_array($data['KLArea_id'], array(0, 268687//244440)) ){
                $query = "
                     select 
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.withSTLpu_id)) as withSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name
                    from dbo.AreaofResponsibilityMO ARMO with(nolock)
                    where ARMO.KLArea_id = :KLArea_id
                ";
            }
             //Уфа, районы уфы
            elseif($data['PersonSprTerrDop_id'] != 0){
                $Lpu_id = $this->getLpu_id(array('LpuRegionStreet_HouseSet'=>$params['LpuRegionStreet_HouseSet'],'KLStreet_id'=>$params['KLStreet_id']));  
                $withSTLpu_id = $this->scheduleMO();
                $params = array(
                    'Lpu_id'=>$Lpu_id[0]['Lpu_id'],
                    'PersonSprTerrDop_id'=>$data['PersonSprTerrDop_id']
                );
                
                $query = "
                     select 
                        (select Lpu_Name from dbo.getLpu_Name({$withSTLpu_id})) as withSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                        (select Lpu_Name from dbo.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name 
                    from dbo.AreaofResponsibilityMO ARMO with(nolock)
                    where ARMO.PersonSprTerrDop_id = :PersonSprTerrDop_id
                    and ARMO.Lpu_id = :Lpu_id            
                ";
             }
             
             
            $result = $this->db->query($query, $params);
            //echo '<pre>' . print_r($params, 1) . '</pre>';
            //echo getDebugSql($query, $params);
            //exit;              
            //echo '<pre>' . print_r($result, 1) . '</pre>';
            if (is_object($result)) {
                $response = $result->result('array');
                $lpudata = !empty($response) ? $response : array('withSTLpu_Name'=>null, 'withoutSTLpu_Name'=>null, 'CHKVLpu_Name'=>null);
                return array($lpudata);//$result->result('array');
            } else {
                return false;
            }    
    		*/
        } else {
            return true;
        }
    }    
        
    /**
     *  Метод преобразования BSKObservRecomendation_id в BSKObservElement_id
     */ 
    function getBSKObservElement_id($BSKObservRecomendation_id){
        $ids = array(
            287 => 277,
            288 => 278,
            289 => 279,
            290 => 280,
            291 => 281,
            292 => 282,
            293 => 283,
            294 => 284,
            295 => 285,
            296 => 286,
            
            297 => 287,
            298 => 288,
            299 => 289,
            300 => 290,
            301 => 291,
            302 => 292,
            303 => 293,
            304 => 294,
            305 => 295,
            306 => 296,
            307 => 297,
            308 => 298,
            309 => 299                    
        );
        
        return $ids[$BSKObservRecomendation_id];
    } 
    /**
     * Список абсолютных и относительных противопоказаний для проведения ТЛТ
     */        
    function getContraindicationsTLT($data){
        
        $params = array(
            'BSKObservRecomendationType_id'=>$data['BSKObservRecomendationType_id']
        );
        
        $query = "
             select 
                BSKObservRecomendation_id,
                BSKObservRecomendationType_id,
                BSKObservRecomendation_text
                --,CONVERT(char(10), BSKObservRecomendation_insDT,120) as BSKObservRecomendation_insDT
             from dbo.BSKObservRecomendation with(nolock) where BSKObservRecomendationType_id = :BSKObservRecomendationType_id
        ";

        $result = $this->db->query($query, $params);
        
        $returnArray = $result->result('array');
        
        $dataTLT = array();
        
        //Не все противопоказания можно получить из бд, только эти
        $list = array(287,288,292,293,295,296,297,299,300,303,304);
        
        foreach($returnArray as $v){
            //Противопоказание отсутствует - по дефолту
            $checked = 0;
            //Поиск наличия противопоказаний в БД
            if(in_array($v['BSKObservRecomendation_id'], $list)){
                $filter = $this->getDiagUsluga($v['BSKObservRecomendation_id']);
                $filter['Person_id'] = $data['Person_id'];
                if ($this->getDiagUslugaResult($filter) == null) {
					$checked = 0;
				} else {
					$checked = count($this->getDiagUslugaResult($filter));
				}
            }
            
            $dataTLT[] = array(
                'BSKObservRecomendation_id'=>$v['BSKObservRecomendation_id'],
                'BSKObservRecomendationType_id'=>$v['BSKObservRecomendationType_id'],
                'BSKObservRecomendation_text'=>$v['BSKObservRecomendation_text'],
                'checked'=>$checked,
            );
        }
        
        return $dataTLT;
        /*
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        } 
        */
                  
    }
    
    /**
     *  Определение списка кодов диагноза/услуг - при  противопоказаниях к проведению ТЛТ (по BSKObservRecomendation_id) для BSKObservRecomendationType_id = 3
     */ 
    function getDiagUsluga($BSKObservRecomendation_id){
        switch($BSKObservRecomendation_id){
            case 287: $diagsUsluga = array(
                                           'Diag_code'=>'(I60.0,I60.1,I60.2,I60.3,I60.4,I60.5,I60.6,I60.7,I60.8,I60.9, 
                                                        I61.0,I61.1,I61.2,I61.3,I61.4,I61.5,I61.6,I61.7,I61.8,I61.9, 
                                                        I62.0,I62.1,I62.2,I62.3,I62.4,I62.5,I62.6,I62.7,I62.8,I62.9, 
                                                        I64)'
                      ); 
                      break;
            case 288: $diagsUsluga = array(
                                           'Diag_code'=>'(I63.0,I63.1,I63.2,I63.3,I63.4,I63.5,I63.6,I63.7,I63.8,I63.9)'
                      ); 
                      break;       
                      
            case 289: $diagsUsluga = array(
                                           'Diag_code'=>'(S06, S07, S09, C70, C71, C72, Q28, I67.1)'
                      ); 
                      break;                                         

            case 292: $diagsUsluga = array(
                                           'Diag_code'=>'(К22.6, К25.0, К25.2, К25.4, К25.6, К26.0, К26.2, К26.4, К26.6, К27.0, К27.2, К27.4, К27.6, К28.0, К28.2, К28.4, К28.6, К 29.0, К92.0, К92.1, К92.2)'
                      ); 
                      break;
                           
            case 293: $diagsUsluga = array(
                                           'Diag_code'=>'(I71.0, I71.1, I71.3, I71.5, I71.8)'
                      ); 
                      break;  
                      
            case 295: $diagsUsluga = array(
                                           'UslugaComplex_code'=>'(A11.14.001, A11.14.001.001, А11.14.003, А11.14.006)',
                                           'Evn_setDate'=>'and D.Evn_setDate < dateadd(HOUR, -24, getdate())'
                      );  //24 часа от даты вызова СМП
                      break;  
                      
            case 296: $diagsUsluga = array(
                                           'UslugaComplex_code'=>'(А11.23.001)'
                      ); 
                      break;  

            case 297: $diagsUsluga = array(
                                           'Diag_code'=>'(G45)',
                                           'Evn_setDate'=>'and D.Evn_setDate < dateadd(month, -6, getdate())'
                      ); //6 месяцев от даты вызова СМП
                      break;                        
          
            case 299: $diagsUsluga = array(
                                            'Diag_code'=>'(К70.4, 
                                                         К72.0,К72.1,К72.2,К72.3,К72.4,К72.5,К72.6,К72.7,К72.8,К72.9)'
                      ); 
                      break;            
 
            case 300: $diagsUsluga = array(
                                            'Diag_code'=>'(К70.3, К70.4, К71.1, К71.2, К71.7, 
                                              К72.0,К72.1,К72.2,К72.3,К72.4,К72.5,К72.6,К72.7,К72.8,К72.9, 
                                              К74.0,К74.1,К74.2,К74.3,К74.4,К74.5,74.6, 
                                              К76.2, К76.6)'
                      ); 
                      break;     
 
            case 303: $diagsUsluga = array(
                                            'Diag_code'=>'(I30)'
                       ); 
                      break;            
  
            case 304: $diagsUsluga = array(
                                            'Diag_code'=>'(I33)'
                      ); 
                      break;     
                                                                   
            default : $diagsUsluga = false;
        }
        
        $diagsUslugaList = isset($diagsUsluga['Diag_code']) ? $diagsUsluga['Diag_code'] : $diagsUsluga['UslugaComplex_code'];

        //для IN в кавычки взять каждый диагноз или услугу
        $diagsUslugaList = strtr($diagsUslugaList, array("("=>"('", ")"=>"')", ","=>"','"));
        
        isset($diagsUsluga['Diag_code']) 
        ? $diagsUsluga['Diag_code'] = $diagsUslugaList 
        : $diagsUsluga['UslugaComplex_code'] = $diagsUslugaList;
        
        //echo '<pre>' . print_r($diagsUsluga, 1) . '</pre>';
        
        return $diagsUsluga;
    }
        
    /**
     * Формирование шаблона запроса поиска определённых кодов диагнози или услуг / на определённый период
     */ 
    function getDiagUslugaResult($filter){
        $UslugaOrDiag = (isset($filter['UslugaComplex_code'])) 
                         ? 'inner join dbo.UslugaComplex UC with (nolock) on EPL.UslugaComplex_id = UC.UslugaComplex_id' 
                         : 'inner join dbo.Diag D with (nolock) on D.Diag_id = EPL.Diag_id';
        
        $fieldParentField = (isset($filter['UslugaComplex_code']))   
                            ? 'D.UslugaComplex_code' 
                            : 'D.Diag_Code';
                            
        $fieldSupQuery = (isset($filter['UslugaComplex_code']))   
                         ? 'UC.UslugaComplex_code' 
                         : 'D.Diag_Code';
                         
        $where = (isset($filter['UslugaComplex_code'])) 
                 ? 'and D.UslugaComplex_code in '.$filter['UslugaComplex_code'] 
                 : 'and D.Diag_code in '.$filter['Diag_code'] ;
                 
        $date = isset($filter['Evn_setDate'])
                ? $filter['Evn_setDate'] 
                : ''; 
                        
        $params = array(
                      'Person_id'=>$filter['Person_id']
                  );
        
        $query = "
			SELECT top 1
                {$fieldParentField}
            FROM
			(
            Select  
            		EPL.Person_id, 
            		EPL.EvnSection_insDT as Evn_insDT,
                    EPL.EvnSection_setDate as Evn_setDate,
                    {$fieldSupQuery}
            from v_EvnSection EPL with (nolock)	
            {$UslugaOrDiag}					               
            
            
            union all
            
            Select
            		E.Person_id, 
            		E.Evn_insDT,
                    EPL.EvnVizitPL_setDT as Evn_setDate,
                    {$fieldSupQuery}
            from v_EvnVizitPL EPL  WITH (NOLOCK)
            left join Evn E WITH (NOLOCK) on EPL.EvnVizitPL_id = E.Evn_id  and isnull(E.Evn_deleted,1) = 1
            left join dbo.v_EvnUsluga EU WITH (NOLOCK) on EPL.EvnVizitPL_id = EU.EvnUsluga_pid
            {$UslugaOrDiag}		
            	          
			) as D
        
			where D.Person_id = :Person_id
            {$where}
            {$date}   
        ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;              
        
        if (is_object($result)) {
            $result->result('array');
        } else {
            return false;
        }         
    }
    
    /**
     * Результаты ЭКГ - чтение справичника
     */        
    function getReferenceECGResult($data){
        $params = array(
            'KLrgn_id'=>$data['KLrgn_id']
        );
        
        $query = "select 
                	ReferenceECGResult_code,
                	ReferenceECGResult_Name,
                    subgroupOKC 
                from 
                    dbo.ReferenceECGResult with(nolock)
                where 
                    KLrgn_id = :KLrgn_id 
                    and referenceECGResult_isDeleted = 2
                ";

        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }                    
    }
    /**
     *  Добавление нового события в жизни пациента https://redmine.swan.perm.ru/issues/79693
     */
    function addEvent($data)
    {
        
        $params = array(
            'BSKEvents_Type'=>$data['BSKEvents_Type'],
            'Person_id'=>$data['Person_id'],
            'BSKEvents_setDT'=>$data['BSKEvents_setDT'],
            'BSKEvents_Code'=>$data['BSKEvents_Code'],
            'BSKEvents_Name'=>$data['BSKEvents_Name'],
            'BSKEvents_Description'=>$data['BSKEvents_Description'],
            'pmUser_id'=>$data['pmUser_id']
        );
        
        $query = "
            declare 
                 @BSKEvents_Type int,
                 @Person_id bigint,
                 @BSKEvents_setDT datetime,
                 @BSKEvents_Code varchar(10),
                 @BSKEvents_Name varchar(300),
                 @BSKEvents_Description nvarchar(4000),
                 @pmUser_id bigint,
                 @Error_Code int,
                 @Error_Message varchar(4000);
            exec dbo.p_BSKEvents_ins
                @BSKEvents_Type = :BSKEvents_Type,
                @Person_id = :Person_id,
                @BSKEvents_setDT = :BSKEvents_setDT,
                @BSKEvents_Code = :BSKEvents_Code,
                @BSKEvents_Name = :BSKEvents_Name,
                @BSKEvents_Description = :BSKEvents_Description,
                @pmUser_id = :pmUser_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;             
         ";
        
        
        //echo getDebugSql($query, $params);
        //exit;        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    } 
    /**
     *  Сохранение после редактирования события 
     */
    function saveEvent($data)
    {
        
        $params = array(
            'BSKEvents_Type'=>$data['BSKEvents_Type'],
            'BSKEvents_id'=>$data['BSKEvents_id'],
            'BSKEvents_setDT'=>$data['BSKEvents_setDT'],
            'BSKEvents_Code'=>$data['BSKEvents_Code'],
            'BSKEvents_Name'=>$data['BSKEvents_Name'],
            'BSKEvents_Description'=>$data['BSKEvents_Description'],
            'pmUser_id'=>$data['pmUser_id']
        );
        
        $query = "
            declare 
                 @BSKEvents_Type int,
                 @BSKEvents_id bigint,
                 @BSKEvents_setDT datetime,
                 @BSKEvents_Code varchar(10),
                 @BSKEvents_Name varchar(300),
                 @BSKEvents_Description nvarchar(4000),
                 @pmUser_id bigint,
                 @Error_Code int,
                 @Error_Message varchar(4000);
            exec dbo.p_BSKEvents_upd
                @BSKEvents_Type = :BSKEvents_Type,
                @BSKEvents_id = :BSKEvents_id,
                @BSKEvents_setDT = :BSKEvents_setDT,
                @BSKEvents_Code = :BSKEvents_Code,
                @BSKEvents_Name = :BSKEvents_Name,
                @BSKEvents_Description = :BSKEvents_Description,
                @pmUser_id = :pmUser_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;             
         ";
        
        
        //echo getDebugSql($query, $params);
        //exit;        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }      
    
    /**
     *  Удаление события 
     */
    function deleteEvent($data)
    {
        
        $params = array(
            'BSKEvents_id'=>$data['BSKEvents_id'],
        );
        
        $query = "
            declare 
                 @BSKEvents_id bigint,
                 @Error_Code int,
                 @Error_Message varchar(4000);
            exec dbo.p_BSKEvents_del
                @BSKEvents_id = :BSKEvents_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;             
         ";
        
        
        //echo getDebugSql($query, $params);
        //exit;        
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }        
      
    /**
     *  Информация о событиях, связанных с БСК по пациенту
     */ 
    function getListEvents($data)
    {
        $params = array(
            'Person_id'=>$data['Person_id'],
        );
        //Скриннинг id/code 84/121
        //ОКС Morbus_type = 19, услуги: ЭКГ,ЧКВ,ТЛТ
        //Данные на девелопе отличаются от данных на рабочем - коды услуг точно
        $dot = '.';
        
        switch($data['MorbusType_id']){ 
            case 84 : 
                $filterDiag_id = " ltrim(d.Diag_Code) like 'i%'";
                $filtersUslugaComplex_Code = " (ltrim(uc.UslugaComplex_Code) = 'A06.10.006' or ltrim(uc.UslugaComplex_Code) like 'A16%')"; 
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
                break;
            case 19 :
                $filterDiag_id = " ltrim(d.Diag_Code) like 'i%'";
                $filtersUslugaComplex_Code = "UslugaComplex_Code in('A05.10.006',
                        'A06.10.006','A16.12.004.009','A16.12.026.003','A16.12.026.004','A16.12.026.005','A16.12.026.006','A16.12.026.007''A16.12.028', 
                        'A11.12.008','A11.12.003.002')";
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
                break;
            default : 
                $filterDiag_id = " ltrim(d.Diag_Code) like 'i%'";
                $filtersUslugaComplex_Code = " (ltrim(uc.UslugaComplex_Code) = 'A06.10.006' or ltrim(uc.UslugaComplex_Code) like 'A16%')"; 
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
                break;        
       }
	    
        $query = "
            WITH EventsCTE
            (
                Events_id,   
            	Events_Code,
            	Events_Name,
                Events_setDate,
                Events_Type,
                Events_Description,
                Events_Edit 
            ) as 
            (		    --------
                        -- Т.к. диагнозы ставяться для каждого посещения (для ТФОМС) - нам нужны только 'точки' их появления
                        --------
            			select 
                                null as Events_id,   
                        		Events_Code,
                        		Events_Name,
                                min(RTrim(IsNull(convert(varchar,cast(Events_setDate as datetime),104),''))),
                                'Диагноз' as Events_Type,
                                null as Events_Description,
                                0 as Events_Edit 
            			from (
                				Select  
                						null as Events_id, 
                            			E.Person_id as Person_id, 
                            			D.Diag_Code as Events_Code,
                            			D.Diag_Name as Events_Name,
                            			--E.EvnSection_insDT as Events_insDate,
                						null as Events_insDate,
                						E.EvnSection_setDT as Events_setDate,
                						'Диагноз' as Events_Type,
                						null as Events_Description,
                						0 as Events_Edit 
                				from v_EvnSection E with (nolock)	
                				inner join dbo.Diag D with(nolock) on D.Diag_id = E.Diag_id		               
                				where   {$filterDiag_id} and E.Person_id = :Person_id   
                             
                				union all
                            
                				Select
                						null as Events_id, 
                            			E.Person_id as Person_id, 
                            			D.Diag_Code as Events_Code,
                            			D.Diag_Name as Events_Name,
                            			--E.Evn_insDT as Events_insDate,
                						null as Events_insDate,
                						E.Evn_setDT as Events_setDate,
                						'Диагноз' as Events_Type,
                						null as Events_Description,
                						0 as Events_Edit   
                				from EvnPL EPL  WITH (NOLOCK)
                				inner join Evn E WITH (NOLOCK) on EPL.EvnPL_id = E.Evn_id  and isnull(E.Evn_deleted,1) = 1
                				inner join dbo.Diag D with(nolock) on D.Diag_id = EPL.Diag_id		          
                				where   {$filterDiag_id} and E.Person_id = :Person_id     
                        ) as T
                        
                        group by Events_Code, Events_Name, Person_id
             
                        --------------------
                         
                        union all
                        
                        --------------------
             
                        SELECT
                            Events_id, 
                            Events_Code,
                            Events_Name,
                            RTrim(IsNull(convert(varchar,cast(Events_setDate as datetime),104),'')) as Events_setDate,
                            Events_Type,
                            Events_Description,
                            Events_Edit
                        FROM
            			
            			(
                            -------
                            -- Список услуг
                            -------
            				select 
            					null as Events_id, 
            					us.Person_id as Person_id,
            					uc.UslugaComplex_Code as Events_Code,
            					uc.UslugaComplex_Name as Events_Name,
            					us.EvnUsluga_setDate as Events_setDate,
            					'Услуга' as Events_Type,
            					ER.ECGResult_Name as Events_Description,
            						0 as Events_Edit   
            				from v_EvnUsluga us with (nolock)
            				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = us.UslugaComplex_id
            				left join EvnUslugaCommon EUC with(nolock) on EUC.EvnUslugaCommon_id = us.EvnUsluga_id
            				left join AttributeSignValue ASV with(nolock) on ASV.AttributeSignValue_TablePKey = EUC.EvnUslugaCommon_id
            				left join AttributeValue AV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id   and AV.Attribute_id = 47
            				left join Attribute A with(nolock) on A.Attribute_id = AV.AttributeValue_id
            				left join ECGResult ER with(nolock) on ER.ECGResult_id =AV.AttributeValue_ValueIdent
            				where  {$filtersUslugaComplex_Code}	and us.Person_id = :Person_id   	
            				
            				union all
                            ------
                            -- Поиск событий - введённыйх из регистра БСК
            				------
                            Select 
            						BSKE.BSKEvents_id as Events_id,  
                        			BSKE.Person_id as Person_id, 
                        			BSKE.BSKEvents_Code as Events_Code,
                        			BSKE.BSKEvents_Name as Events_Name,
            						BSKE.BSKEvents_setDT as Event_setDate,
            						case when BSKE.BSKEvents_Type = 1 then 'Диагноз'
            							 else 'Услуга' end  as Event_Type,
            						BSKE.BSKEvents_Description as Events_Description,
            						1 as Events_Edit        
            				from dbo.BSKEvents BSKE WITH (NOLOCK)	          
            				where  (
            						$filterEventsTable
            						)
            				  and BSKE.Person_id = :Person_id    			
            			
            			) as D
            )	
            
            select * from EventsCTE with(nolock)		
            order by Events_setDate DESC       
                        
        ";
        /*
        $query = "
 			SELECT
                Events_id, 
                Events_Code,
                Events_Name,
                RTrim(IsNull(convert(varchar,cast(Events_insDate as datetime),104),'')) as Events_insDate, 
                RTrim(IsNull(convert(varchar,cast(Events_setDate as datetime),104),'')) as Events_setDate,
                Events_Type,
                Events_Description,
                Events_Edit
            FROM
			
			(
            Select  
                    null as Events_id, 
            		E.Person_id as Person_id, 
            		D.Diag_Code as Events_Code,
            		D.Diag_Name as Events_Name,
            		--E.EvnSection_insDT as Events_insDate,
                    null as Events_insDate,
                    min(E.EvnSection_setDT) as Events_setDate,
                    'Диагноз' as Events_Type,
                    null as Events_Description,
                    0 as Events_Edit 
            from v_EvnSection E with (nolock)	
            inner join dbo.Diag D with(nolock) on D.Diag_id = E.Diag_id
            where  {$filterDiag_id} and E.Person_id = :Person_id   
            group by D.Diag_Code, D.Diag_Name, E.Person_id
            union all
            
            Select
                    null as Events_id, 
            		E.Person_id as Person_id, 
            		D.Diag_Code as Events_Code,
            		D.Diag_Name as Events_Name,
            		--E.Evn_insDT as Events_insDate,
                    null as Events_insDate,
                    min(E.Evn_setDT) as Events_setDate,
                    'Диагноз' as Events_Type,
                    null as Events_Description,
                    0 as Events_Edit   
            from EvnPL EPL  WITH (NOLOCK)
            inner join Evn E WITH (NOLOCK) on EPL.EvnPL_id = E.Evn_id  and isnull(E.Evn_deleted,1) = 1
            inner join dbo.Diag D with(nolock) on D.Diag_id = EPL.Diag_id
			where  {$filterDiag_id} and E.Person_id = :Person_id    
            group by D.Diag_Code, D.Diag_Name, E.Person_id
            
			union all
			
			select 
                null as Events_id, 
				us.Person_id as Person_id,
				uc.UslugaComplex_Code as Events_Code,
				uc.UslugaComplex_Name as Events_Name,
				us.EvnUsluga_insDT as Events_setDate,
				us.EvnUsluga_setDate as Events_setDate,
				'Услуга' as Events_Type,
				null as Events_Description,
                    0 as Events_Edit   
			from v_EvnUsluga us with (nolock)
			inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = us.UslugaComplex_id	
            where {$filtersUslugaComplex_Code}	and us.Person_id = :Person_id  	
			
			union all
            
            Select 
                    BSKE.BSKEvents_id as Events_id,  
            		BSKE.Person_id as Person_id, 
            		BSKE.BSKEvents_Code as Events_Code,
            		BSKE.BSKEvents_Name as Events_Name,
            		BSKE.BSKEvents_insDT as Event_insDate,
                    BSKE.BSKEvents_setDT as Event_setDate,
                    case when BSKE.BSKEvents_Type = 1 then 'Диагноз'
                         else 'Услуга' end  as Event_Type,
                    BSKE.BSKEvents_Description as Events_Description,
                    1 as Events_Edit        
            from dbo.BSKEvents BSKE WITH (NOLOCK)	          
			where  (
			        {$filterEventsTable}
			        )
			  and BSKE.Person_id = :Person_id   			
			) as D
			where D.Person_id = :Person_id  
            order by D.Events_setDate DESC       
                
      
        ";
        */
        //echo getDebugSql($query, $params);
        //exit;
        
  
        $result = $this->db->query($query, $params);
        
        //echo '<pre>' . print_r($result->result('array'), 1) . '</pre>';
                
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }        
     }
    
    /**
     *  Получение сведений о лекарственном лечении
     */
    function getDrugs($data)
    {
        $params = array(
            'Person_id' => $data['Person_id']
        );
        
        $query = "
         
             select 
            R.BSKRegistry_id, 
            CONVERT(char(10), BSKRegistry_setDate,104) as BSKRegistry_setDate,
            E.BSKObservElement_id,
            E.BSKObservElement_name,
            RD.BSKRegistryData_data,
            RD.BSKUnits_name,
            CONVERT(char(10), RD.BSKRegistryData_insDT,104) as BSKRegistryData_insDT
             from dbo.BSKRegistryData RD  with(nolock)
            left join dbo.BSKRegistry R with(nolock) on RD.BSKRegistry_id = R.BSKRegistry_id
            left join dbo.BSKObservElement E with(nolock) on RD.BSKObservElement_id = E.BSKObservElement_id
            left join dbo.BSKObservElementGroup EG with(nolock) on E.BSKObservElementGroup_id = EG.BSKObservElementGroup_id
            where EG.BSKObservElementGroup_id in (10,23,28,42)
            and R.Person_id = :Person_id
            order by  right(CONVERT(varchar(10), BSKRegistry_setDate,104), 4) DESC       
        ";
        
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
     * Для вывода на печать лекарственного лечения - необходимы данные о пациенте 
     */  
    function getPersonInfo($data){
        $params = array(
            'Person_id' => $data['Person_id']
        );
        
        $query = "select * from dbo.getPersonInfo(:Person_id)";
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }        
        //
    }
   
    /**
     * Список едениц измерения 
     */
    function getComboUnits($data)
    {
        $params             = array();
        $BSKObservUnits_ids = array();
        
        switch ($data['BSKObservElement_id']) {
            /**
             * 84 - Скрининг
             */ 
            //рост
            case 107:
                $BSKObservUnits_ids = array(
                    7
                );
                break;
            // Вес
            case 108:
                $BSKObservUnits_ids = array(
                    18
                    //17
                );
                break;
            //давление
            case 50:
            case 51:
            case 52:
            case 54:
            case 54:
            case 55:
            case 56:
            case 57:
                $BSKObservUnits_ids = array(
                    9
                );
                break;
            // Объём талии
            // 8 ?
            case 109:
                $BSKObservUnits_ids = array(
                    7
                );
                break;
            //Липопротеины низкой плотности
            case 88:
                //Липопротеины высокой плотности
            case 90:
                //Триглицериды
            case 91:
                //АпоВ-100 (вредный холестерин)
            case 92:
                //Апо А1 (полезный холестерин)
            case 93:
                //Липопротеин (а)
            case 94:
                //Общий холестерин
            case 89:
                $BSKObservUnits_ids = array(
                    10,
                    22
                );
                break;
            
            //Почечные пробы.Скорость клубочковой фильтрации.
            case 96:
                $BSKObservUnits_ids = array(
                    21
                );
                break;
            //Почечные пробы.Микроальбуминурия)/протеинурия (МАУ).
            case 97:
                $BSKObservUnits_ids = array(
                    15,
                    20
                );
                break;
            //Почечные пробы. Соотношение микроальбумина/ креатинина в моче
            case 98:
                $BSKObservUnits_ids = array(
                    15
                );
                break;
            //Глюкоза в крови (капиллярная)
            case 99:
                //Глюкоза в крови (венозная)
            case 100:
                //Через 2 часа после перорального глюкозо –толерантного теста (ПГТТ) (Капиллярная)
            case 101:
                $BSKObservUnits_ids = array(
                    10
                );
                break;
            
            //СРБ вч (Ц-реактивный белок высокочувствительным методом)
            case 95:
                $BSKObservUnits_ids = array(
                    11
                );
                break;
            /**
             * 88 - Легочная гипертензия
             */ 
            //рост
            case 142:
                $BSKObservUnits_ids = array(
                    7
                );
                break;
            // Вес
            case 143:
                $BSKObservUnits_ids = array(
                    18
                    //17
                );
                break; 
            //Систолическое ад    
            case 144:
            case 145:
                //Диастолическое ад,
            case 146: 
            case 147:
                //Среднее давление в легочной артерии
            case 160:            
                //Давление в правом предсердии
            case 163:  
                //Давление в легочной артерии: систолическое
            case 164: 
                //Давление в легочной артерии: диастолическое
            case 165: 
                //Давление в легочной артерии: среднее
            case 166: 
                //Давление заклинивания в лёгочной артерии
            case 167: 
                $BSKObservUnits_ids = array(
                    9
                );            
                break;  
            //Частота сердечных сокращений
            case 148:
                $BSKObservUnits_ids = array(
                    24
                );
                break;    
            //Тест с 6 минутной ходьбой   
            case 149:
                $BSKObservUnits_ids = array(
                    23
                );
                break;      
            //Гемоглобин
            case 152:
                $BSKObservUnits_ids = array(
                    11
                );
                break;  
            //Гематокрит
            case 153:
                $BSKObservUnits_ids = array(
                    25
                );
                break;    
            //Правый желудочек
            case 156:
                //Правое предсердие
            case 157:
                //Конечно-диастолический размер левого желудочка
            case 158:
                $BSKObservUnits_ids = array(
                    7
                );
                break;    
            //Фракция выброса левого желудочка
            case 159:
                $BSKObservUnits_ids = array(
                    25
                );
                break; 
            //Сердечный выброс
            case 168:
                $BSKObservUnits_ids = array(
                    26
                );
                break;   
            //Легочное сосудистое сопротивление, ед. Вуда
            case 169:
                //Системное сосудистое сопротивление   
            case 170:
                $BSKObservUnits_ids = array(
                    27
                );
                break;              
            //ЛЕЧЕНИЕ Дозировка
            case 186:
            case 192:
            case 194:
            case 196:
            case 198:
            case 200:
            case 202:
            case 204:
                $BSKObservUnits_ids = array(
                    14
                );
                break;                                                                                                                                                                                                                 
                
            /**
             * 89 - Артериальная гипертензия
             */ 
            //рост
            case 208:
                $BSKObservUnits_ids = array(
                    7
                );
                break;
            // Вес
            case 209:
                $BSKObservUnits_ids = array(
                    18
                    //17
                );
                break; 
            // Окружность талии
            case 211:
                $BSKObservUnits_ids = array(
                    7
                );
                break;
            //давление
            case 212:
            case 213:
            case 214:
            case 215:            
                $BSKObservUnits_ids = array(
                    9
                );
            break;     
            //чсс
            case 216:
                $BSKObservUnits_ids = array(
                    24
                );   
            break;
            //Общий холестерин
            case 224:
                //Липопротеины низкой плотности
            case 225:
                //Липопротеины высокой плотности
            case 226:
                //Триглицериды
            case 227:
                //Глюкоза, капиллярная кровь
            case 228:
                //Глюкоза, венозная кровь
            case 229:
                //Глюкоза в крови через 2 часа после перорального глюкозо-толерантного теста
            case 230:
                //Мочевая кислота
            case 231:
                $BSKObservUnits_ids = array(
                    10
                );
                break;  
            // Скорость клубочковой фильтрации
            case 232:
                $BSKObservUnits_ids = array(
                    21
                );
                break;
            // Микроальбуминурия
            case 233:
                $BSKObservUnits_ids = array(
                    20
                );
                break;
            // Отношение альбумин/креатинин
            case 234:
                $BSKObservUnits_ids = array(
                    16
                );
                break;     
            //Межжелудочковая перегородка
            case 235:
                //Толщина задней стенки
            case 236:
                //Конечно-диастолический размер ЛЖ
            case 237:
                //Левое предсердие
            case 238:
                //Фракция выброса левого желудочка
            case 239:
                //Индекс массы миокарда ЛЖ
            case 240:
                $BSKObservUnits_ids = array(
                    7
                );
                break;  
            //Скорость пульсовой волны (каротиднофеморальной)
            case 244:
                $BSKObservUnits_ids = array(
                    28
                );
                break;              
            //Дневной показатель СМАД
            case 247:
                //Ночной показатель СМАД
            case 248:
                //Суточный показатель СМАД
            case 249:
                $BSKObservUnits_ids = array(
                    9
                );
                break;     
            //дозировка
            case 260:
            case 261:
            case 262:
            case 263:
            case 264:
            case 265:
            case 266:
            case 267:
            case 268:
            case 269:
                $BSKObservUnits_ids = array(
                    14
                );
                break; 
            
            /**
             *  50 Ишемическая болезнь сердца
             */
            
            //Рост
            case 318:
                $BSKObservUnits_ids = array(
                    7
                );
                break;            
            //Возраст
            case 319:
                $BSKObservUnits_ids = array(
                    18
                );
                break; 
            //Окружность талии
            case 321:
                $BSKObservUnits_ids = array(
                    7
                );
                break;    
            //Систолическое давление
            case 322:
            case 323:
                //Диастолическое давление
            case 324:    
            case 325:    
                $BSKObservUnits_ids = array(
                    9
                );            
                break;  
            
            //Окружность талии
            case 326:
                $BSKObservUnits_ids = array(
                    24
                );
                break;                 
            //ЛЕЧЕНИЕ Дозировка
            case 366:
            case 368:
            case 370:
            case 372:
            case 374:
            case 376:
            case 378:
            case 380:
            case 382:    
                $BSKObservUnits_ids = array(
                    14
                );
                break;  
            //Лаба ИБС
            case 333:
            case 334:
            case 335:
            case 336:
            case 337:
            case 338:
            case 339:   
                $BSKObservUnits_ids = array(
                    10
                );
                break;
            //Эхокардиография ИБС
            case 392:
            case 393:
            case 394:
            case 395:
            case 396:
            case 397:   
                $BSKObservUnits_ids = array(
                    7
                );
                break;  
            //Коронароангиография
            case 350:
            case 351:
            case 352:
            case 353:
            case 354:
            case 355:
            case 356:
            case 357:
            case 358:
            case 359:
            case 360:
            case 361:
            case 362:
            case 363:
            case 364:  
                $BSKObservUnits_ids = array(
                    25
                );
				break;                 
			//Нарушения ритма сердца Синусовая брадикардия
			case 418:
				$BSKObservUnits_ids = array(
					24
				);
				break;
			//Нарушения ритма сердца Пауза
			case 419:
				$BSKObservUnits_ids = array(
					29
				);
				break;
            default:
                $BSKObservUnits_ids = array();
                break;
        }
        
        if (empty($BSKObservUnits_ids)) {
            $query = "select BSKObservUnits_id, BSKObservUnits_name from dbo.BSKObservUnits with(nolock)";
        } else {
            $query = "select BSKObservUnits_id, BSKObservUnits_name from dbo.BSKObservUnits with(nolock) where BSKObservUnits_id in (" . implode(',', $BSKObservUnits_ids) . ")";
        }
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        //echo '<pre>' . print_r($result->result('array'), 1) . '</pre>';
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Получение рекомендаций по регистрам
     */
    function getRecomendationByDate($data)
    {
        $params = array(
            'MorbusType_id' => $data['MorbusType_id'],
            'Person_id' => $data['Person_id'],
            'Sex_id' => $data['Sex_id'],
            'BSKRegistry_setDate' => $data['BSKRegistry_setDate'],
            'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id']
        );
        
        $query = "
            declare 
                @MorbusType_id bigint,
                @Person_id bigint,   
                @Sex_id int,          
                @BSKRegistry_setDate date,
                @BSKObservRecomendationType_id int,
                @Error_Code bigint,
                @Error_Message varchar(max);
            exec dbo.p_getRecomendationByDate
                @MorbusType_id = :MorbusType_id,
                @Person_id = :Person_id,
                @Sex_id = :Sex_id,
                @BSKRegistry_setDate = :BSKRegistry_setDate,
                @BSKObservRecomendationType_id = :BSKObservRecomendationType_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;             
         ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;   
        //echo '<pre>' . print_r($result, 1) . '</pre>';
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        
    }
    
    /**
     *  Построение древа рекомендаций по датам, относительно предмета наблюдения для конкретного пациента
     */
    function getTreeDatesRecomendations($data)
    {
        $params = array(
            'MorbusType_id' => $data['MorbusType_id'],
            'Person_id' => $data['Person_id']
        );
        
        $query = "
            select 
                CONVERT(char(10), BSKRegistry_setDate,120) as text,
                'true' as leaf             
            from dbo.BSKRegistry with(nolock) 
            where MorbusType_id = :MorbusType_id and  Person_id = :Person_id  
            group by  CONVERT(char(10), BSKRegistry_setDate,120) 
            order by CONVERT(char(10), BSKRegistry_setDate,120) DESC             
         ";
        
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
	 * Сохранение скрининга в БД
	 */
	public function saveRegistry($data, $registryData) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
			'BSKRegistry_id' => null
		];

		try {
			$this->beginTransaction();

			$result = $this->getFirstRowFromQuery("
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)
	
				exec dbo.p_BSKRegistry_insNEW
					@BSKRegistry_id = @Res output,
					@BSKRegistry_setDate = :setDate,
					@BSKRegistry_riskGroup = :riskGroup,
					@BSKRegistry_isBrowsed = :isBrowsed,
					@CmpCallCard_id = :CmpCallCard_id,
					@Person_id = :Person_id,
					@MorbusType_id = :MorbusType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @Res as BSKRegistry_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
				'pmUser_id' => $data['pmUser_id'],
				'riskGroup' => $registryData['riskGroup'],
				'MorbusType_id' => $registryData['MorbusType_id'],
				'Person_id' => $registryData['Person_id'],
				'setDate' => $registryData['setDate'],
				'isBrowsed' => $registryData['MorbusType_id']==19 ? 1 : 2,
				'CmpCallCard_id' => !empty($data['CmpCallCard_id'])?$data['CmpCallCard_id']:NULL
			]);

			if ( $result === false || !is_array($result) || count($result) == 0 ) {
				throw new Exception('Ошибка при добавлении пациента в регистр БСК');
			}
			else if ( !empty($result['Error_Msg']) ) {
				throw new Exception($result['Error_Msg']);
			}

			$response['BSKRegistry_id'] = $result['BSKRegistry_id'];

			if ( is_array($registryData['ListAnswers']) && count($registryData['ListAnswers']) > 0 ) {
				foreach ( $registryData['ListAnswers'] as $k => $v ) {
					$value = htmlspecialchars(isset($v['value']) ? $v['value'] : $v['BSKRegistryData_data']);
					$unit  = htmlspecialchars(isset($v['unit']) ? $v['unit'] : (isset($v['BSKUnits_name']) ? $v['BSKUnits_name'] : ''));

					if ( strlen(trim($value)) != 0 || $registryData['MorbusType_id'] == 19) {
						$result = $this->getFirstRowFromQuery("
							declare
								@BSKRegistryData_id bigint,
								@Error_Code bigint,
								@Error_Message varchar(4000);
							exec dbo.p_BSKRegistryData_insNEW
								@BSKRegistryData_id  = @BSKRegistryData_id output,
								@BSKRegistry_id  = :BSKRegistry_id,
								@pmUser_id = :pmUser_id,
								@BSKObservElement_id = :BSKObservElement_id,
								@BSKRegistryData_data = :BSKRegistryData_data,
								@BSKUnits_name = :BSKUnits_name,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
		
							select @BSKRegistryData_id as BSKRegistryData_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						", [
							'BSKRegistry_id' => $response['BSKRegistry_id'],
							'pmUser_id' => $data['pmUser_id'],
							'BSKObservElement_id' => $k,
							'BSKRegistryData_data' => $value,
							'BSKUnits_name' => $unit
						]);

						// тут можно прикрутить обработку ответа, но в изначальном коде ее не было
					}
				}
			}

			// Рассылка уведомлений
			$resUsers =  $this->queryResult('
				SELECT puc.pmUser_id
				FROM v_PersonCard pc WITH (nolock) 
					INNER JOIN v_LpuRegion lp ON lp.LpuRegion_id = pc.LpuRegion_id
					INNER JOIN v_MedStaffRegion msr ON lp.LpuRegion_id = msr.LpuRegion_id
						and msr.MedStaffRegion_isMain = 2
						and (msr.MedStaffRegion_begDate is null or msr.MedStaffRegion_begDate <= dbo.tzGetdate())
						and (msr.MedStaffRegion_endDate is null or msr.MedStaffRegion_endDate >= dbo.tzGetdate())
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id
					INNER JOIN dbo.pmUserCache puc on puc.MedPersonal_id = ISNULL(msf.MedPersonal_id, msr.MedPersonal_id)
				WHERE pc.LpuAttachType_id = 1
					and pc.Person_id = :Person_id
			', [
				'Person_id' => $registryData['Person_id']
			]);

			if ( is_array($resUsers) && count($resUsers) > 0 ) {
				$userList = [];

				foreach ( $resUsers as $row ) {
					if ( !in_array($row['pmUser_id'], $userList) ) {
						$userList[] = $row['pmUser_id'];
					}
				}

				$persData = $this->getFirstRowFromQuery("
					SELECT top 1
						ps.Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName,
						convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay
					FROM v_PersonState ps WITH (nolock)
					WHERE ps.Person_id = :Person_id
				", [
					'Person_id' => $registryData['Person_id']
				]);

				if ( $persData === false || !is_array($persData) || count($persData) == 0 ) {
					throw new Exception('Ошибка при получении данных пациента');
				}

				$datasend = [
					'Message_id' => null,
					'Message_pid' => null,
					'Message_Subject' => 'Пациент включен в регистр БСК',
					'Message_Text' => "Пациент вашего участка " . $persData['Person_SurName'] . " ". $persData['Person_FirName'] . " " . $persData['Person_SecName'] . ", дата рождения " . $persData['Person_BirthDay'] . " включен в регистр БСК",
					'pmUser_id' => $data['pmUser_id'],
					'UserSend_ID' => 0,
					'Lpus' => '',
					'pmUser_Group' => '',
					'Message_isSent' => 1,
					'NoticeType_id' => 5,
					'Message_isFlag' => 1,
					'Message_isDelete' => 1,
					'RecipientType_id' => 1,
					'action' => 'ins',
					'MessageRecipient_id' => null,
					'Message_isRead' => null,
				];

				$this->load->model("Messages_model", "msmodel");

				$result = $this->msmodel->insMessage($datasend);

				if ( !is_array($result) || count($result) == 0 || empty($result[0]['Message_id']) ) {
					throw new Exception('Ошибка при добавлении сообщения');
				}
				else if ( !empty($result[0]['Error_Msg']) ) {
					throw new Exception($result[0]['Error_Msg']);
				}

				$Message_id = $result[0]['Message_id'];

				foreach ( $userList as $pmUser_id ) {
					$result = $this->msmodel->insMessageLink($Message_id, $pmUser_id, $datasend);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageLink_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}

					$result = $this->msmodel->sendMessage($datasend, $pmUser_id, $Message_id);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageRecipient_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return [ $response ];
	}
    
    /**
     *  Проверка наличия регистра по заданному предмету наблюдения, дате и пациенту
     */
    function checkRegisterDate($data)
    {
        $date = new DateTime();
        $day = $date->format('Y-m-d');
        $day_start = $day.' 00:00:00.000';
        $day_end = $day.' 23:59:59.999';

	    $params = array
            (
                'Person_id' => $data['Person_id'],
                'MorbusType_id' => $data['MorbusType_id'],
                'day_start' => $day_start,
                'day_end' => $day_end
            );
        
        $query = " select *
            from dbo.BSKRegistry with(nolock) where MorbusType_id = :MorbusType_id
            and Person_id = :Person_id and BSKRegistry_setDate BETWEEN CONVERT(datetime, :day_start,120) AND CONVERT(datetime, :day_end,120)
        "; 
        $result = $this->db->query($query, $params);
        //echo getDebugSql($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Получение сведений о регистрах по заданному MorbusType_id
     */
    function getRegistryDates($data)
    {
        $params = array(
            'MorbusType_id' => $data['MorbusType_id'],
            'Person_id' => $data['Person_id'],
            'setData' => $data['setDate']
        );
        
        $query = "
            select * from dbo.BSKRegistry with(nolock) 
            where MorbusType_id = :MorbusType_id and Person_id = :Person_id
            order by BSKRegistry_setDate DESC
         ";
        
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
     *  Предварительная подготовка данных при добавление регистра
     */
    function preSaveRegistryData($data)
    {
        $preData = array(
            'BSKRegistry_id' => ($data['BSKRegistry_id']) ? $data['BSKRegistry_id'] : null,
            'BSKObject_id' => ($data['BSKObject_id']) ? $data['BSKObject_id'] : null,
            'PersonData' => $data['PersonData'],
            'ListAnswers' => $data['ListAnswers'],
            'questions_ids' => $data['questions_ids'],
            'MorbusType_id' => ($data['MorbusType_id']) ? $data['MorbusType_id'] : null,
            'Person_id' => ($data['Person_id']) ? $data['Person_id'] : null,
            'setDate' => ($data['setDate']) ? $data['setDate'] : null
        );
        
        $PersonData    = json_decode($preData['PersonData']);
        $ListAnswers   = json_decode($preData['ListAnswers']);
        $questions_ids = json_decode($preData['questions_ids']);
        
        //echo '<pre>' . print_r($PersonData, 1) . '</pre>';
        //echo $PersonData->age;
        //echo '<pre>' . print_r($ListAnswers, 1) . '</pre>';
        
        $params = array(
            'Person_age' => $PersonData->age,
            'Sex_id' => $PersonData->Sex_id
        );
        
        $query = "select 
                    V.BSKObservElementValues_id,
                    V.BSKObservElementValues_sign,
                    E.BSKObservElement_id,
                    E.BSKObservElement_name,
                    E.BSKObservElementFormat_id,
                    V.BSKObservElementValues_data 
                from dbo.BSKObservElementValues V with(nolock)
                left join dbo.BSKObservElement E with(nolock) on E.BSKObservElement_id = V.BSKObservElement_id 
                where E.BSKObservElement_id in(" . implode(',', $questions_ids) . ")
                ";
        
        $result = $this->db->query($query, $params);
        
        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            //Финт ушами. Новый год. Логику отсюда перенести в контроллер 
            $tempAnswers = $result->result('array');
            
            $answers = array();
            
            foreach ($tempAnswers as $k => $v) {
                
                $answers[$v['BSKObservElement_id']][] = array(
                    'BSKObservElement_id' => $v['BSKObservElement_id'],
                    'BSKObservElement_name' => $v['BSKObservElement_name'],
                    'BSKObservElementValues_id' => $v['BSKObservElementValues_id'],
                    'BSKObservElementValues_sign' => $v['BSKObservElementValues_sign'],
                    'BSKObservElementValues_data' => $v['BSKObservElementValues_data'],
                    'BSKObservElementFormat_id' => $v['BSKObservElementFormat_id']
                    
                    
                );
            }
            //echo '<pre>' . print_r($ListAnswers, 1) . '</pre>'; 
            
            return array(
                'answersDB' => $answers,
                'PersonData' => $PersonData,
                'ListAnswers' => $ListAnswers,
                'MorbusType_id' => $preData['MorbusType_id'],
                'Person_id' => $preData['Person_id'],
                'setDate' => $preData['setDate'],
                'BSKRegistry_id' => $preData['BSKRegistry_id']
            );
        } else {
            return false;
        }
    }
    
    
    /**
     * Получение сведений об инвалидности пациента
     */
    function havePrivilege($data)
    {
        $params = array(
            'Person_id' => $data['Person_id']
        );
        
        $query = "
           select top 1 
    		    PT.PrivilegeType_Name as diagstring
    	   from dbo.PersonPrivilege PP with(nolock)
           left join dbo.PrivilegeType PT with(nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
           where PT.PrivilegeType_id in $this->listMorbusType and PP.PersonPrivilege_endDate IS NULL
           and PP.Person_id  = :Person_id       
       ";
        
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
     * Формирование шаблона запроса
     */ 
    function getDiagnose($filter){
        return "
			SELECT top 1
                D.Diag_Code + ' ' + D.Diag_Name as diagstring
            FROM
			(
            Select  
            		E.Person_id, 
            		D.Diag_Code,
            		D.Diag_Name,
            		E.EvnSection_insDT as Evn_insDT,
                    E.EvnSection_setDate as Evn_setDate
            from v_EvnSection E with (nolock)	
            inner join dbo.Diag D with(nolock) on D.Diag_id = E.Diag_id		               
            union all
            
            Select
            		E.Person_id, 
            		D.Diag_Code,
            		D.Diag_Name,
            		E.Evn_insDT,
                    E.Evn_setDT as Evn_setDate
            from EvnPL EPL  WITH (NOLOCK)
            inner join Evn E WITH (NOLOCK) on EPL.EvnPL_id = E.Evn_id  and isnull(E.Evn_deleted,1) = 1
            inner join dbo.Diag D with(nolock) on D.Diag_id = EPL.Diag_id		          
			) as D
        
			where D.Person_id = :Person_id
            
            {$filter}
            --and  DATEADD(day,1,D.Evn_setDate) <= :Evn_insDT
            order by D.Evn_setDate DESC		
            
        ";
    }

    
    /**
     * Наличие диабета у пациента 
     */
    function checkDiabetes($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );
        
        //echo '<pre>' . print_r($params, 1) . '</pre>';
   
        /*
        $query = "
            SELECT top 1
                D.Diag_Code + ' ' + D.Diag_Name as diagstring
            FROM dbo.EvnDiag ED with(nolock)
            left join dbo.Diag D with(nolock) on D.Diag_id = ED.Diag_id
            left join dbo.Evn E with(nolock) on E.Evn_id = ED.Evn_id
            where E.Person_id = :Person_id
            
            and 
            
            D.Diag_Code in('E10.0','E10.1','E10.8','E10.9','E11.0','E11.1','E11.8','E14.0','E14.8','E12.0','E13.0',
                           'E10.2','E10.3','E10.4','E10.6','E11.2','E11.4','E11.6','E14.2','E14.2','E14.3','E14.4',
                           'E14.5','E14.7','N18.0','N18.9','N19'
            			   )
           
            order by E.Evn_insDT DESC
          ";
          */
        $filter = "
            and 
            
            D.Diag_Code in(
                           'E10.1','E10.8','E10.9','E11.0','E11.1','E11.8','E11.9','E14.0','E14.1','E14.8','E14.9','E12.0','E13.0','E10.2','E10.3','E10.4','E10.5','E10.6',
                           'E10.7','E11.2','E11.3','E11.4','E11.5','E11.6','E11.7','E14.2','E14.3','E14.4','E14.5','E14.6','E14.7','N08.3','E10.0'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     *  Наличие хронического заболевания почек
     */
    function checkDisease($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                            'N18.8','N18.9','N00.2','N00.3','N00.4','N00.5','N00.7','N01.2','N01.5','N01.7','N02.2','N02.3','N02.4','N02.5 ','N02.7','N03.2','N03.3',
                            'N03.4','N03.5','N03.7','N04.2','N04.3','N04.4','N04.5','N04.7','N05.2','N05.3','N05.4','N05.5','N05.7','N10','N11.8','N11.9','N12','N11.0',
                            'N11.1','N18.1','N18.2','N18.3','N18.4','N18.5','N18.0','N19','N19.'
            			   )       
        ";
          
        $query = $this->getDiagnose($filter); 

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
     *   Гипофункция щитовидной железы
     */
    function checkGypofunction($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('E02','E03.0','E03.1','E03.2','E03.3','E03.4','E03.8','E03.9')      
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     * Аутоиммунные заболевания
     */
    function checkAutoimmune($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('С90.0','С88.0','М32.0','М32.1','М32.8','М32.9','L10.0','L10.1','L10.2','L10.3','L10.4','L10.5','L10.8','L10.9','L40.0','L40.1','L40.2','L40.3','L40.4','L40.5','L40.8','L40.9')      
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     * Неалкогольная жировая болезнь печени 
     */
    function checkFattyLiver($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('K76.6')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     * Наличие ВИЧ
     */
    function checkHIV($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'B20.0','B20.1','B20.2','B20.3','B20.4','B20.5','B20.6','B20.7','B20.8','B20.9',
                           'B21.0','B21.1','B21.2','B21.3','B21.7','B21.8','B21.9',
                           'B22.0','B22.1','B22.2','B22.7',
                           'B23.0','B23.1','B23.2','B23.8',
                           'B24.'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Портальная гипертензия
     */
    function checkPortalHypertension($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'K76.6'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Патология легких
     */
    function checkLungPathology($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'J43.0','J43.1','J43.2','J43.8','J43.9',
                           'J44.0',
                           'J45.0','J45.1','J45.8','J45.9',
                           'J60.0','J60.1','J60.2','J60.3','J60.4','J60.5','J60.6','J60.7','J60.8','J60.9','J60.',
                           'J61.0','J61.1','J61.2','J61.3','J61.4','J61.5','J61.6','J61.8','J61.9','J61.',
                           'J62.0','J62.1','J62.8','J62.9',
                           'J63.0','J63.1','J63.2','J63.3','J63.4','J63.5','J63.6','J63.8','J63.9',
                           'J64.',
                           'J65.0','J65.1','J65.2','J65.3','J65.8','J65.9','J65.',
                           'J66.0','J66.1','J66.2','J66.3','J66.4','J66.8','J66.9',
                           'J67.0','J67.1','J67.2','J67.3','J67.4','J67.5','J67.6','J67.7','J67.8','J67.9', 
                           'J70.0','J70.1','J70.2','J70.3','J70.4','J70.8','J70.9', 
                           'J82.', 
                           'J84.0','J84.1','J84.8','J84.9'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Пороки сердца
     */
    function checkHeartDefects($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'Q21.0','Q21.1','Q21.2','Q21.3','Q21.4','Q21.8','Q21.9', 
                           'Q22.0','Q22.1','Q22.2','Q22.3','Q22.4','Q22.5','Q22.6','Q22.8','Q22.9', 
                           'Q23.0','Q23.1','Q23.2','Q23.3','Q23.4','Q23.8','Q23.9', 
                           'Q25.0','Q25.5','Q25.7'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Заболевания соединительной ткани
     */
    function checkTissueDiseases($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'M05.0','M05.1','M05.2','M05.3','M05.8','M05.9', 
                           'M06.0','M06.1','M06.2','M06.3','M06.4','M06.8','M06.9', 
                           'M30.0','M30.1','M30.2','M30.3','M30.8',
                           'M31.0','M31.1','M31.2','M31.3','M31.4','M31.5','M31.6','M31.7','M31.8','M31.9',
                           'M32.0','M32.1','M32.8','M32.9',
                           'M33.0','M33.1','M33.2','M33.9',
                           'M34.0','M34.1','M34.2','M34.8','M34.9', 
                           'M35.0','M35.1','M35.2','M35.3','M35.4','M35.5','M35.6','M35.7','M35.8','M35.9'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Синдром абструктивного апноэ сна
     */
    function checkSnoringDiag($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'G47.3'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Саркоидоз
     */
    function checkSarcoidosis($data)
    {
                   $params = array(
                       'Person_id' => $data['Person_id'],
                       'Evn_insDT' => $data['Evn_insDT']
             );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'D86.0','D86.1','D86.2','D86.3','D86.8','D86.9'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Гистиоцитоз
     */
    function checkHistiocytosis($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'D76.0', 'D76.3'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Шистосомоз
     */
    function checkSchistosomiasis($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                           'B65.0'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Диабет c диагнозом
     */
    function checkDiabetesDiag($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                            'E10.0','E10.1','E10.2','E10.3','E10.4','E10.5','E10.6','E10.7','E10.8','E10.9',
                            'E11.0','E11.1','E11.2','E11.3','E11.4','E11.5','E11.6','E11.7','E11.8','E11.9',
                            'E12.0','E12.1','E12.2','E12.3','E12.4','E12.5','E12.6','E12.7','E11.8','E12.9',
                            'E13.0','E13.1','E13.2','E13.3','E13.4','E13.5','E13.6','E13.7','E13.8','E13.9',
                            'E14.0','E14.1','E14.2','E14.3','E14.4','E14.5','E14.6','E14.7','E14.8','E14.9'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * ИБС c диагнозом
     */
    function checkIBS($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                            'I21.0','I21.1','I21.2','I21.3','I21.4','I21.9',
                            'I22.0','I22.1','I22.8','I22.9',
                            'I23.0','I23.1','I23.2','I23.3','I23.4','I23.5','I23.6','I23.8',
                            'I24.0','I24.1','I24.8','I24.9',
                            'I25.0','I25.1','I25.2','I25.3','I25.4','I25.5','I25.6','I25.8','I25.9'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Цереброваскулярная болезнь c диагнозом
     */
    function checkCerebrovascular($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                            'I60.0','I60.1','I60.2','I60.3','I60.4','I60.5','I60.6','I60.7','I60.8','I60.9',
                            'I61.0','I61.1','I61.2','I61.3','I61.4','I61.5','I61.6','I61.8','I61.9',
                            'I62.0','I62.1','I62.9',
                            'I63.0','I63.1','I63.2','I63.3','I63.4','I63.5','I63.6','I63.8','I63.9',
                            'I64.'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     * Хроническая болезнь почек c диагнозом
     */
    function checkDiseaseDiag($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in(
                            'N18.0',
                            'N18.3',
                            'N18.4',
                            'N18.5'
            			   )        
        ";
          
        $query = $this->getDiagnose($filter); 
        
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
     *  Объём талии в перцентелях
     */
    function getWaistPercentel($data)
    {
        $params = array(
            'age' => $data['age'],
            'waist' => strtr($data['waist'], array(
                ',' => '.'
            )),
            'Sex_id' => $data['Sex_id']
        );
        
        $query = "
            select top 1 PercentileWaist_Percentile as prc from dbo.PercentileWaist with(nolock)
            where 
            PercentileWaist_Age = :age 
            and 
            Sex_id = :Sex_id 
            and
            PercentileWaist_Circle >= :waist        
         ";
        
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
     * Наличие у пациента камней в желчном пузыре
     */
    function checkStonesInBubble($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );
        
        $filter = "
            and 
            
            D.Diag_Code in('K80.3','K80.5','K80.2','K80.4','K82.4', 'K80.8')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     * Синдром обструктивного апное сна (храп)
     */
    function checkSnoring($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('G47.3')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     *  ухудшение слуха
     */
    function checkBadHear($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );
        
        $filter = "
            and 
            
            D.Diag_Code in('H93.0','H90.3','H90.4','H90.5','H90.6','H90.7','H90.8')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     *  Эректильная дисфункция +
     */
    function checkDysfunction($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('N50.1')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     *  Поликистоз яичников +
     */
    function checkPolycystic($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('E 28.2')
        ";
          
        $query = $this->getDiagnose($filter); 
               
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
     *  подагра +
     */
    function checkGout($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('M10.0')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     *  Липодистрофия +
     */
    function checkLipodystrophy($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('E88.1')
        ";
          
        $query = $this->getDiagnose($filter); 
                
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
     *  Болезнь накопления гликогена +
     */
    function checkGlycogen($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'Evn_insDT' => $data['Evn_insDT']
        );

        $filter = "
            and 
            
            D.Diag_Code in('E74.0')
        ";
          
        $query = $this->getDiagnose($filter); 
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Получение данных регистра для сравнения
     */ 
    function getCompare($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id']
        );
        
        $query = "
            select 
               BSKRegistry_id,
               left(convert(varchar(10), BSKRegistry_setDate,104),10) as BSKRegistry_setDate,
               BSKRegistry_riskGroup
            from dbo.BSKRegistry with(nolock)
            where Person_id = :Person_id
            and MorbusType_id = :MorbusType_id
            -- Все 3 группы попадают в сравнение
            --and BSKRegistry_riskGroup in(2,3)
            order by right(CONVERT(varchar(10), BSKRegistry_setDate,104), 4) DESC
         ";
        
        
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
     * Получение вопросов для анкеты (пока метод не используется)
     */ 
    function getAnketsData($data)
    {
        $params = array(
            'BSKObject_id' => $data['BSKObject_id']
        );
        
        $query = "
            select 
                V.BSKObservElementValues_id,
                V.BSKObservElementValues_data,
                V.BSKObservElement_id,
                E.BSKObservElement_name,
                G.BSKObservElementGroup_id,
                G.BSKObservElementGroup_name
            from dbo.BSKObservElementValues V with(nolock)
            inner join dbo.BSKObservElement E with(nolock) on E.BSKObservElement_id = V.BSKObservElement_id 
                       and E.BSKObservElement_deleted = 1
                       and E.BSKObservElement_stage = 1
            inner join dbo.BSKObservElementGroup G with(nolock) on G.BSKObservElementGroup_id = E.BSKObservElementGroup_id 
                       and 1=1
            inner join dbo.BSKObservElementLink L with(nolock) on L.BSKObservElement_id = E.BSKObservElement_id
                       and L.BSKObservElementLink_deleted = 1   
                       and L.BSKObject_id = 2                    
            where V.BSKObservElementValues_deleted = 1         
         ";
        
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
     *  Обновление данных регистра
     */
    function updateRegistry($data, $registryData)
    {

        $xml   = array();
        $xml[] = "<BSKRegistry>";

        // вытаскиваем данные ОКС
		if (empty($data['Registry_method']) && !empty($data['BSKRegistry_id'])) {
			$data['Registry_method'] = $data['BSKRegistry_id'];
		}
        $p = array('BSKRegistry_id' => $data['Registry_method']);
        $q = 'SELECT BSKRegistryData_id, BSKObservElement_id FROM dbo.BSKRegistryData with(nolock) WHERE BSKRegistry_id = :BSKRegistry_id';
        $res = $this->db->query($q, $p);

        if (is_object($res)) {
            $oks_data = $res->result('array');

            foreach($oks_data as $ke => $va) {
                foreach ($registryData['ListAnswers'] as $k => $v) {

                    if($va['BSKObservElement_id'] == $k) {
                        $value = htmlspecialchars(isset($v['value']) ? $v['value'] : $v['BSKRegistryData_data']);
                        $unit  = htmlspecialchars(isset($v['unit']) ? $v['unit'] : (isset($v['BSKUnits_name']) ? $v['BSKUnits_name'] : ''));

                        if (trim($value) != '' || (!empty($data['MorbusType_id']) && $data['MorbusType_id'] == 19) || (!empty($registryData['MorbusType_id']) && $registryData['MorbusType_id'] == 19)) {
                            $xml[] = '<BSKRegistryData  BSKRegistry_id="1"
                                                        BSKObservElement_id="' . $k . '"
                                                        BSKRegistryData_data="' . $value . '"
                                                        BSKRegistryData_id="' . $va['BSKRegistryData_id'] . '"
                                                        BSKUnits_name="' . $unit . '"
                                                        BSKRegistryData_insDT="null"
                                                        BSKRegistryData_updDT="getdate()"
                                                        pmUser_insID="null"
                                                        pmUser_updID="' . $data['pmUser_id'] . '"
                                                        BSKRegistryData_deleted="1">
                                     </BSKRegistryData>
                                     ';
                        }
                    }

                }
            }

            $xml[] = "</BSKRegistry>";


            //echo htmlspecialchars(implode("", $xml));
            //exit;

            $params = array(
                'xml' => implode('', $xml),
                'pmUser_id' => $data['pmUser_id'],
                'riskGroup' => $registryData['riskGroup'],
                'BSKRegistry_id' => $data['Registry_method']
            );

            $query = "
                declare
                    @xml nvarchar(4000),
                    @pmUser_insID bigint,
                    @riskGroup int,
                    @riskGroupStatus int,
                    @BSKRegistry_id bigint,
                    @Error_Code bigint,
                    @Error_Message varchar(4000);
                exec dbo.p_BSKRegistryData_upd
                    @xml = :xml,
                    @pmUser_id = :pmUser_id,
                    @riskGroup = :riskGroup,
                    @BSKRegistry_id = :BSKRegistry_id,
                    @riskGroupStatus = @riskGroupStatus output,
                    @Error_Code = @Error_Code output,
                    @Error_Message = @Error_Message output;

                select @riskGroupStatus as riskGroupStatus, @Error_Code as Error_Code, @Error_Message as Error_Message;
            ";

            $result = $this->db->query($query, $params);

            //echo getDebugSql($query, $params);
            //exit;

            if (is_object($result)) {
                return $result->result('array');
            } else {
                return false;
            }

        } else {
            return false;
        }

    }
    
    /**
     *  Получение последнего свежего регистра по данному ПН для пациента
     */
    function getLastVizitRegistry($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id'],
            'setDate' => $data['setDate'],
            'BSKRegistry_id' => $data['BSKRegistry_id'],
            'pmUser_id' =>$data['pmUser_id']
        );
        
        //Ищем последний регистр на пациента по MorbusType_id + setDate
        if ($data['BSKRegistry_id'] == null) {
			if ($data['MorbusType_id'] == 19) {
				$query = "
					select 
					case 
					when T.BSKObservElementGroup_id = 29 then 1 
					when T.BSKObservElementGroup_id = 32 then 2 
					when T.BSKObservElementGroup_id = 44 then 3 
					when T.BSKObservElementGroup_id = 30 then 4 
					when T.BSKObservElementGroup_id = 31 then 5 
					end num,				
					*
					from (
					select 
					mt.MorbusType_id, 
					mt.MorbusType_Code, 
					mt.MorbusType_Name, 
					mt.MorbusType_SysNick, 
					o.BSKObject_id,
					oeg.BSKObservElementGroup_id, 
					oeg.BSKObservElementGroup_name, 
					case 
					when oe.BSKObservElement_id = 304 then 1
					when oe.BSKObservElement_id = 276 then 2
					when oe.BSKObservElement_id = 300 then 3
					when oe.BSKObservElement_id = 271 then 4
					when oe.BSKObservElement_id = 272 then 5
					when oe.BSKObservElement_id = 273 then 6
					when oe.BSKObservElement_id = 274 then 7
					when oe.BSKObservElement_id = 302 then 8
					--------------------------------------------------
					when oe.BSKObservElement_id = 398 then 9
					when oe.BSKObservElement_id = 410 then 10
					when oe.BSKObservElement_id = 411 then 11
					when oe.BSKObservElement_id = 412 then 12
					when oe.BSKObservElement_id = 399 then 13
					when oe.BSKObservElement_id = 413 then 14
					when oe.BSKObservElement_id = 400 then 15
					--------------------------------------------------
					when oe.BSKObservElement_id = 308 then 16
					when oe.BSKObservElement_id = 270 then 17
					when oe.BSKObservElement_id = 311 then 18
					when oe.BSKObservElement_id = 312 then 19
					when oe.BSKObservElement_id = 306 then 20
					when oe.BSKObservElement_id = 307 then 21
					when oe.BSKObservElement_id = 303 then 22
					when oe.BSKObservElement_id = 301 then 23
					when oe.BSKObservElement_id = 414 then 24
					when oe.BSKObservElement_id = 415 then 25
					when oe.BSKObservElement_id = 275 then 26
					when oe.BSKObservElement_id = 416 then 27
					end numA,
					oe.BSKObservElement_id, 
					oe.BSKObservElement_name, 
					BSKObservElement_stage, 
					BSKObservElement_symbol, 
					BSKObservElement_formula, 
					BSKObservElementFormat_id,
					BSKObservElement_IsRequire, 
					BSKObservElement_Sex_id, 
					BSKObservElement_minAge, 
					BSKObservElement_maxAge, 
					BSKObservElement_insDT, 
					BSKObservElement_updDT, 
					BSKObservElement_deleted, 
					BSKObservElement_Anketa,
					BSKRegistry.pmUser_insID, 
					BSKRegistry.pmUser_updID,
					BSKRegistry.BSKRegistry_id,  
					convert(varchar(10),BSKRegistry.BSKRegistry_setDate,104) BSKRegistry_setDateFormat, 
					convert(varchar,BSKRegistry.BSKRegistry_setDate,121) BSKRegistry_setDate, 
					BSKRegistry.BSKRegistry_riskGroup, 
					BSKRegistry.Person_id, 
					case 
					when oe.BSKObservElement_id = 273 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
					(select top 1 ReferenceECGResult_Name from dbo.ReferenceECGResult where cast(ReferenceECGResult_id as varchar) = BSKRegistry.BSKRegistryData_data)
					when oe.BSKObservElement_id = 303 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
					(select top 1 cast(ccc.CmpCallCard_Numv as varchar) from dbo.v_CmpCallCard ccc with (nolock) where cast(ccc.CmpCallCard_id as varchar) = BSKRegistry.BSKRegistryData_data)
					else BSKRegistry.BSKRegistryData_data end BSKRegistryData_data,
					BSKRegistry.Lpu_id,
					BSKRegistry.BSKRegistry_isBrowsed
					from dbo.BSKObject o with (nolock)
					inner join dbo.MorbusType mt with (nolock) on mt.MorbusType_id = o.MorbusType_id and mt.MorbusType_id = 19
					inner join dbo.BSKObservElementGroup oeg with (nolock) on oeg.BSKObject_id = o.BSKObject_id
					inner join dbo.BSKObservElement oe with (nolock) on oe.BSKObservElementGroup_id = oeg.BSKObservElementGroup_id
						and isnull(oe.BSKObservElement_deleted,1) <> 2
					outer apply (
						select r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id, rd.BSKRegistryData_data, 
						r.pmUser_insID, r.pmUser_updID, r.Lpu_id, r.BSKRegistry_isBrowsed
						from (
							select top 1 r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id,
							isnull(r.pmUser_insID,1) pmUser_insID, r.pmUser_updID, usr.Lpu_id, r.BSKRegistry_isBrowsed
							from dbo.BSKRegistry r with (nolock)
							left join dbo.v_pmUserCache usr with (nolock) on usr.PMUser_id = r.pmUser_insID
							where r.Person_id = :Person_id and r.MorbusType_id = :MorbusType_id
							order by r.BSKRegistry_setDate desc
						) r
						inner join dbo.BSKRegistryData rd with (nolock) on r.BSKRegistry_id = rd.BSKRegistry_id and rd.BSKObservElement_id = oe.BSKObservElement_id
					) BSKRegistry
					) as T
					order by num, isnull(numA, BSKObservElement_id)
				";
			} else {
            $query = "
                 select 
                    RD.BSKRegistryData_id,
                    RD.BSKRegistry_id,
                    RD.BSKObservElement_id,
                    replace(replace(RD.BSKRegistryData_data, '&lt;', '<'), '&gt;', '>') as BSKRegistryData_data,
                    RD.BSKUnits_name,
                    RD.BSKRegistryData_insDT,
                    RD.BSKRegistryData_updDT,
                    RD.pmUser_insID,
                    RD.pmUser_updID,
                    RD.BSKRegistryData_deleted,
                    R.*, 
                 (select top 1 Lpu_id from pmUserCache with(nolock) where pmUser_id = R.pmUser_insID) as Lpu_id,
                 R.BSKRegistry_riskGroup,
                 (select BSKObservElementFormat_id from dbo.BSKObservElement E with(nolock) where E.BSKObservElement_id = RD.BSKObservElement_id) as BSKObservElementFormat_id
                 from dbo.BSKRegistryData RD with(nolock)
                 left join dbo.BSKRegistry R with(nolock) on R.BSKRegistry_id = RD.BSKRegistry_id
                 where R.Person_id = :Person_id and R.MorbusType_id = :MorbusType_id 
                 and R.BSKRegistry_id = (select top 1 BSKRegistry_id from dbo.BSKRegistry R with(nolock) where R.Person_id = :Person_id and R.MorbusType_id = :MorbusType_id  order by R.BSKRegistry_setDate DESC )
             ";
		}
        } else if ($data['MorbusType_id'] == 19) {
			$query = "
				select 
				case 
				when T.BSKObservElementGroup_id = 29 then 1 
				when T.BSKObservElementGroup_id = 32 then 2 
				when T.BSKObservElementGroup_id = 44 then 3 
				when T.BSKObservElementGroup_id = 30 then 4 
				when T.BSKObservElementGroup_id = 31 then 5 
				end num,				
				*
				from (
				select 
				mt.MorbusType_id, 
				mt.MorbusType_Code, 
				mt.MorbusType_Name, 
				mt.MorbusType_SysNick, 
				o.BSKObject_id,
				oeg.BSKObservElementGroup_id, 
				oeg.BSKObservElementGroup_name, 
				case 
				when oe.BSKObservElement_id = 304 then 1
				when oe.BSKObservElement_id = 276 then 2
				when oe.BSKObservElement_id = 300 then 3
				when oe.BSKObservElement_id = 271 then 4
				when oe.BSKObservElement_id = 272 then 5
				when oe.BSKObservElement_id = 273 then 6
				when oe.BSKObservElement_id = 274 then 7
				when oe.BSKObservElement_id = 302 then 8
				--------------------------------------------------
				when oe.BSKObservElement_id = 398 then 9
				when oe.BSKObservElement_id = 410 then 10
				when oe.BSKObservElement_id = 411 then 11
				when oe.BSKObservElement_id = 412 then 12
				when oe.BSKObservElement_id = 399 then 13
				when oe.BSKObservElement_id = 413 then 14
				when oe.BSKObservElement_id = 400 then 15
				--------------------------------------------------
				when oe.BSKObservElement_id = 308 then 16
				when oe.BSKObservElement_id = 270 then 17
				when oe.BSKObservElement_id = 311 then 18
				when oe.BSKObservElement_id = 312 then 19
				when oe.BSKObservElement_id = 306 then 20
				when oe.BSKObservElement_id = 307 then 21
				when oe.BSKObservElement_id = 303 then 22
				when oe.BSKObservElement_id = 301 then 23
				when oe.BSKObservElement_id = 414 then 24
				when oe.BSKObservElement_id = 415 then 25
				when oe.BSKObservElement_id = 275 then 26
				when oe.BSKObservElement_id = 416 then 27
				end numA,
				oe.BSKObservElement_id, 
				oe.BSKObservElement_name, 
				BSKObservElement_stage, 
				BSKObservElement_symbol, 
				BSKObservElement_formula, 
				BSKObservElementFormat_id,
				BSKObservElement_IsRequire, 
				BSKObservElement_Sex_id, 
				BSKObservElement_minAge, 
				BSKObservElement_maxAge, 
				BSKObservElement_insDT, 
				BSKObservElement_updDT, 
				BSKObservElement_deleted, 
				BSKObservElement_Anketa,
				BSKRegistry.pmUser_insID, 
				BSKRegistry.pmUser_updID,
				BSKRegistry.BSKRegistry_id,  
				convert(varchar(10),BSKRegistry.BSKRegistry_setDate,104) BSKRegistry_setDateFormat, 
				convert(varchar,BSKRegistry.BSKRegistry_setDate,121) BSKRegistry_setDate, 
				BSKRegistry.BSKRegistry_riskGroup, 
				BSKRegistry.Person_id, 
				case 
				when oe.BSKObservElement_id = 273 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
				(select top 1 ReferenceECGResult_Name from dbo.ReferenceECGResult where cast(ReferenceECGResult_id as varchar) = BSKRegistry.BSKRegistryData_data)
				when oe.BSKObservElement_id = 303 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
				(select top 1 cast(ccc.CmpCallCard_Numv as varchar) from dbo.v_CmpCallCard ccc with (nolock) where cast(ccc.CmpCallCard_id as varchar) = BSKRegistry.BSKRegistryData_data)
				else BSKRegistry.BSKRegistryData_data end BSKRegistryData_data,
				BSKRegistry.Lpu_id,
				BSKRegistry.BSKRegistry_isBrowsed
				from dbo.BSKObject o with (nolock)
				inner join dbo.MorbusType mt with (nolock) on mt.MorbusType_id = o.MorbusType_id and mt.MorbusType_id = 19
				inner join dbo.BSKObservElementGroup oeg with (nolock) on oeg.BSKObject_id = o.BSKObject_id
				inner join dbo.BSKObservElement oe with (nolock) on oe.BSKObservElementGroup_id = oeg.BSKObservElementGroup_id
					and isnull(oe.BSKObservElement_deleted,1) <> 2
				outer apply (
					select r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id, rd.BSKRegistryData_data, 
					r.pmUser_insID, r.pmUser_updID, r.Lpu_id, r.BSKRegistry_isBrowsed
					from (
						select top 1 r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id,
						isnull(r.pmUser_insID,1) pmUser_insID, r.pmUser_updID, usr.Lpu_id, r.BSKRegistry_isBrowsed
						from dbo.BSKRegistry r with (nolock)
						left join dbo.v_pmUserCache usr with (nolock) on usr.PMUser_id = r.pmUser_insID
						where r.BSKRegistry_id = :BSKRegistry_id
						order by r.BSKRegistry_setDate desc
					) r
					inner join dbo.BSKRegistryData rd with (nolock) on r.BSKRegistry_id = rd.BSKRegistry_id and rd.BSKObservElement_id = oe.BSKObservElement_id
				) BSKRegistry
				) as T
				order by num, isnull(numA, BSKObservElement_id)
			";
		} else {

            $query = "
                 select 
                    RD.BSKRegistryData_id,
                    RD.BSKRegistry_id,
                    RD.BSKObservElement_id,
                    replace(replace(RD.BSKRegistryData_data, '&lt;', '<'), '&gt;', '>') as BSKRegistryData_data,
                    RD.BSKUnits_name,
                    RD.BSKRegistryData_insDT,
                    RD.BSKRegistryData_updDT,
                    RD.pmUser_insID,
                    RD.pmUser_updID,
                    RD.BSKRegistryData_deleted,
                    R.*, 
                 (select top 1 Lpu_id from pmUser with(nolock) where pmUser_id = R.pmUser_insID) as Lpu_id,
                 (select BSKObservElementFormat_id from dbo.BSKObservElement E with(nolock) where E.BSKObservElement_id = RD.BSKObservElement_id) as BSKObservElementFormat_id
                 from dbo.BSKRegistryData RD with(nolock)
                 left join dbo.BSKRegistry R with(nolock) on R.BSKRegistry_id = RD.BSKRegistry_id
                 where 
                 R.BSKRegistry_id =:BSKRegistry_id
             ";
        }
        /*
        $query .= "
                --сортируем блин
                
                order by case when RD.BSKObservElement_id = 24 then 33
    					     when RD.BSKObservElement_id = 25 then 32
    					     when RD.BSKObservElement_id = 26 then 31
    					     when RD.BSKObservElement_id = 31 then 30
    					     when RD.BSKObservElement_id = 32 then 29
    					     when RD.BSKObservElement_id = 33 then 28
    					     when RD.BSKObservElement_id = 38 then 27
    					     when RD.BSKObservElement_id = 39 then 26
    					     when RD.BSKObservElement_id = 40 then 25
    					     when RD.BSKObservElement_id = 41 then 24
    					     when RD.BSKObservElement_id = 42 then 23
    					     when RD.BSKObservElement_id = 45 then 22
    					     when RD.BSKObservElement_id = 46 then 21
    					     when RD.BSKObservElement_id = 47 then 20
    					     when RD.BSKObservElement_id = 48 then 19
    					     
    					     when RD.BSKObservElement_id = 25 then 18
    					     when RD.BSKObservElement_id = 25 then 17
    					     when RD.BSKObservElement_id = 25 then 16
    					     when RD.BSKObservElement_id = 25 then 15
    					     when RD.BSKObservElement_id = 25 then 14
    					     when RD.BSKObservElement_id = 25 then 13
    					     when RD.BSKObservElement_id = 25 then 12      
    					     else 0
    					     end DESC           
        ";
        */
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
     *  Построение опроснига для предметов наблюдения
     */


    function addRegistryData($data)
    {
        $params = array(
            'BSKObject_id' => $data['BSKObject_id'],
            'Person_id' => $data['Person_id']
        );
        
        $query = "
                select 
                    V.BSKObservElementValues_id,
                    V.BSKObservElementValues_data,
                    V.BSKObservElement_id,
                    E.BSKObservElement_name,
                    
                    E.BSKObservElement_stage,
                    E.BSKObservElement_minAge,
                    E.BSKObservElement_maxAge,
                    E.BSKObservElement_Sex_id,
                    
                    E.BSKObservElementFormat_id,
                    G.BSKObservElementGroup_id,
                    G.BSKObservElementGroup_name
                from dbo.BSKObservElementValues V with(nolock)
                inner join dbo.BSKObservElement E with(nolock) on E.BSKObservElement_id = V.BSKObservElement_id 
                           and E.BSKObservElement_deleted = 1
                           --
                           -- Для анкеты самое то
                           -- and E.BSKObservElement_stage = 1
                           --
                inner join dbo.BSKObservElementGroup G with(nolock) on G.BSKObservElementGroup_id = E.BSKObservElementGroup_id 
                           and 1=1
                inner join dbo.BSKObservElementLink L with(nolock) on L.BSKObservElement_id = E.BSKObservElement_id
                           and L.BSKObservElementLink_deleted = 1   
                           and L.BSKObject_id = :BSKObject_id 
                
                --Финт для определения сортировки
                left join dbo.BSKObject BO with(nolock) on BO.BSKObject_id = L.BSKObject_id
                -- конец финта
                                                 
                where V.BSKObservElementValues_deleted = 1
                
                -- Убираем диагнозы из варианта ответов
                
                and V.BSKObservElementValues_id not in(
                 --------------
                 -- 84 Скрининг
                 --------------
                 -- сахарный диабет
                 29,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,318,321,322,323,324,325,425,
                 -- хроническое заболевание почек
                 330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,376,377,378,379,380,422,424,
                 -- заболевания, связанные с гипофункцией щитовидной железы
                 33,367,368,369,370,371,372,373,
                 -- аутоиммунные заболевания
                 35,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,
                 --	Неалкогольная жировая болезнь печени
                 50,
                 -- Наличие у пациента камней в желчном пузыре
                 404,405,406,407,408,426,
                 -- Синдром обструктивного апное сна (храп)
                 95,
                 -- ухудшение слуха
                 97,410,411,412,413,414,415,
                 -- Эриктоильная дисфункция у мужчин
                 99,
                 -- Поликистоз яичников
                 101,
                 -- Подагра
                 109,
                 -- Липодистрафия
                 111,
                 -- Гликоген
                 113
                 --------------
                 -- 88 Лёгочная гипертензия 
                 --------------             
                 -- не указаны в админке т.к. вытаскиваются в анкету из БД    
                 
                )
                
                --сортируем блин
                order by /*
                    --84 Скрининг
                    case when BO.MorbusType_id = 84 then E.BSKObservElement_id
                    --88 Лёгочная гипертензия
                         when BO.MorbusType_id = 88 then E.BSKObservElement_id
                    --89 Артериальная гипертензия
                         when BO.MorbusType_id = 89 then E.BSKObservElement_id                       
                    else '' end,
                    */
                    case  E.BSKObservElement_id        
                             --Скрининг
                             when 24 then 445
                             when 25 then 440
                             when 26 then 435
                             when 31 then 430
                             when 32 then 425
                             when 33 then 420
                             
                             when 38 then 395
                             
                             when 50 then 394
                             when 51 then 393
                             when 54 then 391
                             when 55 then 391

                             when 39 then 390
                             when 40 then 385
                             when 41 then 380
                             when 42 then 375
                             when 45 then 360
                             when 46 then 355
                             when 47 then 350
                             when 48 then 345
                             when 49 then 340
                             when 50 then 335
                             when 51 then 330
                             when 54 then 325
                             
                             when 65 then 322
                             when 63 then 321
                             when 55 then 320
                             
                             when 34 then 320
                             when 44 then 319
                             when 43 then 318
                             when 37 then 317
                             when 36 then 316
                             when 35 then 316
                             
                  


                             when 58 then 315
                             when 59 then 310
                             when 60 then 305
                             when 61 then 300
                             when 62 then 295
                             
                             
                             when 67 then 280
                             when 78 then 275
                             when 79 then 270
                             



                             when 107 then 240
                             when 108 then 235
                             when 109 then 230
                             when 110 then 225
                             when 88 then 220
                             when 89 then 215
                             when 90 then 210
                             when 91 then 205
                             when 92 then 200
                             when 93 then 195
                             when 94 then 190
                             when 95 then 185
                             when 96 then 180
                             when 97 then 175
                             when 98 then 170
                             when 99 then 165
                             when 100 then 160
                             when 101 then 155
                             when 102 then 150
                             when 103 then 145
                             when 104 then 140
                             when 105 then 135
                             when 106 then 130
                             when 111 then 125
                             when 112 then 120
                             when 113 then 115
                             when 114 then 110
                             when 115 then 105
                             when 116 then 100
                             when 117 then 95
                             when 118 then 90
                             when 119 then 85
                             when 120 then 80
                             when 121 then 75
                             when 122 then 70
                             when 123 then 65
                             when 124 then 60
                             when 125 then 55
                             when 126 then 50
                             when 127 then 45
                             when 128 then 40
                             when 129 then 35
                             when 130 then 30
                             when 131 then 25
                             when 132 then 20
                             when 133 then 15
                             when 136 then 10
                             when 137 then 5

                             --Легочная гипертензия
                             when 173 then 82
                             when 174 then 81
                             when 175 then 80
                             when 142 then 79
                             when 143 then 78
                             when 172 then 77
                             when 144 then 74
                             when 145 then 73
                             when 146 then 72
                             when 147 then 71 
                             when 148 then 70
                             when 149 then 69
                             when 150 then 68
                             when 151 then 63
                             when 176 then 62
                             when 177 then 61
                             when 178 then 60
                             when 179 then 59
                             when 180 then 58
                             when 181 then 57
                             when 182 then 56
                             when 183 then 55
                             when 184 then 54
                             when 152 then 53
                             when 153 then 52
                             when 154 then 51
                             when 155 then 50
                             when 156 then 49
                             when 157 then 48
                             when 158 then 47
                             when 159 then 46
                             when 160 then 45
                             when 161 then 44
                             when 162 then 43
                             when 163 then 42
                             when 164 then 41
                             when 165 then 40
                             when 166 then 39
                             when 167 then 38
                             when 168 then 37
                             when 169 then 36
                             when 170 then 35
                             when 171 then 34
                             when 185 then 33
                             when 186 then 32
                             when 187 then 31
                             when 192 then 30
                             when 193 then 29
                             when 194 then 28
                             when 195 then 27
                             when 196 then 26
                             when 197 then 25
                             when 198 then 24
                             when 199 then 23
                             when 200 then 22
                             when 201 then 21
                             when 202 then 20
                             when 203 then 19

                             --Артериальная гипертензия

                             when 205 then 470
                             when 206 then 460
                             when 207 then 450
                             when 208 then 440
                             when 209 then 430
                             when 210 then 420
                             when 211 then 410
                             when 212 then 400
                             when 213 then 390
                             when 214 then 380
                             when 215 then 370
                             when 216 then 360
                             when 217 then 350
                             when 218 then 340
                             when 219 then 330
                             when 220 then 320
                             when 221 then 310
                             when 222 then 300
                             when 223 then 290
                             when 269 then 280
                             when 224 then 270
                             when 225 then 260
                             when 226 then 250
                             when 227 then 240
                             when 228 then 230
                             when 229 then 220
                             when 230 then 210
                             when 231 then 200
                             when 232 then 190
                             when 233 then 180
                             when 234 then 170
                             when 235 then 160
                             when 236 then 150
                             when 237 then 140
                             when 238 then 130
                             when 239 then 120
                             when 240 then 110
                             when 241 then 100
                             when 242 then 90
                             when 243 then 80
                             when 244 then 70
                             when 245 then 60
                             when 246 then 50
                             when 247 then 40
                             when 248 then 35
                             when 249 then 32
                             when 250 then 30
 
                             when 260 then 28
                             when 251 then 29
 
                             when 261 then 26
                             when 252 then 27  
                             
                             when 262 then 24
                             when 253 then 25
                             
                             when 263 then 22
                             when 254 then 23 
                             
                             when 264 then 20
                             when 255 then 21  
                             
                             when 265 then 18
                             when 256 then 19
                             
                             when 266 then 16
                             when 257 then 17
                             
                             when 267 then 14
                             when 258 then 15
                             
                             when 268 then 12
                             when 259 then 13

                             --ОКС
                             when 308 then 500
                             when 309 then 490
                             when 310 then 480
                             when 270 then 470
                             when 276 then 460
                             when 312 then 450
                             when 313 then 440
                             when 311 then 435
                             when 314 then 430
                             when 271 then 420
                             when 300 then 415
                             when 306 then 414
                             when 307 then 413
                             when 305 then 412
                             when 303 then 414
                             when 272 then 410
                             when 273 then 400
                             when 274 then 390
                             when 275 then 380
                             when 300 then 370
                             when 399 then 360
                             when 398 then 350
                             when 400 then 340

                             when 277  then 330
                             when 278  then 320
                             when 279  then 310
                             when 280  then 300
                             when 281  then 290
                             when 282  then 280
                             when 283  then 270
                             when 284  then 260
                             when 285  then 250
                             when 286  then 240
                             when 287  then 230
                             when 288  then 220
                             when 289  then 210
                             when 291  then 200
                             when 292  then 190
                             when 293  then 180
                             when 294  then 170
                             when 295  then 160
                             when 296  then 150
                             when 297  then 140
                             when 298  then 130
                             when 299  then 120
                             
                             when 301 then 110
                             when 302 then 100
                             when 304 then 90

                             --Ишемическая болезнь сердца
                             
                             when 315 then 95
                             when 316 then 94
                             when 317 then 93
                             when 318 then 92
                             when 319 then 91
                             when 320 then 90
                             when 321 then 89
                             when 322 then 88
                             when 323 then 87
                             when 324 then 86
                             when 325 then 85
                             when 326 then 84
                             when 327 then 83
                             when 328 then 82
                             when 383 then 82
                             when 329 then 81
                             when 330 then 80
                             when 331 then 79
                             when 332 then 78
                            
                             
                             when 333 then 75
                             when 334 then 74
                             when 335 then 73
                             when 336 then 72
                             when 337 then 71
                             when 338 then 70
                             when 339 then 69
                             when 340 then 68
                             when 341 then 67
                             when 385 then 66
                             when 343 then 65
                             when 344 then 64
                             when 345 then 63
                             when 346 then 62
                             when 347 then 61
                             when 348 then 60
                             when 349 then 59
                             when 350 then 58
                             when 351 then 57
                             when 352 then 56
                             when 353 then 55
                             when 354 then 54
                             when 355 then 53
                             when 356 then 52
                             when 357 then 51
                             when 358 then 50
                             when 359 then 49
                             when 360 then 48
                             when 361 then 47
                             when 362 then 46
                             when 363 then 45
                             when 364 then 44
                             when 392 then 43
                             when 393 then 42
                             when 394 then 41
                             when 395 then 40
                             when 396 then 39
                             when 397 then 38
                             when 365 then 37
                             when 366 then 36
                             when 367 then 35
                             when 368 then 34
                             when 369 then 33
                             when 370 then 32
                             when 371 then 31
                             when 372 then 30
                             when 373 then 29
                             when 374 then 28
                             when 375 then 27
                             when 376 then 26
                             when 377 then 25
                             when 378 then 24
                             when 379 then 23
                             when 380 then 22
                             when 381 then 21
                             when 382 then 20
							--Нарушения ритма и проводимости сердца 
							when 417 then 9
							when 418 then 8
							when 419 then 7
							when 420 then 6
							when 421 then 5
							when 422 then 4
							when 423 then 3
							when 424 then 2
							when 425 then 1

    					     else 0
    					     end DESC   
                              
           ";
        
        //echo getDebugSql($query, $params);
        //exit;
                
        $result = $this->db->query($query, $params);

        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     *  Список предметов наблюдения
     */ 
    function getBskObjects(){
        $params = array();
       
        $query = "select 
                   M.MorbusType_id,
                   M.MorbusType_name
                 from dbo.MorbusType M with(nolock)
                 where M.MorbusType_id in(84,88,89,50, 19,110,111,112,113) ";  
                 //,88,89,50
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
     * Получение списка предметов наблюдения для пациента
     */
    function getListBSKObjects()
    {
        $params = array();
        
        $query = "select 
                     O.BSKObject_id,
                     MT.MorbusType_id,
                     MT.MorbusType_name
                    from dbo.BSKObject O with(nolock)            	
                    left join dbo.MorbusType MT with(nolock) on MT.MorbusType_id = O.MorbusType_id 
                    
                    where O.MorbusType_id in($this->listMorbusType)
                  ";
        
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
     * Получение списка предметов наблюдения для конкретного пациента
     */
    function getListObjectsCurrentUser($data)
    {
        $params = array(
            'Person_id' => $data['Person_id']
        );
        
        $query = "  select 
                        O.BSKObject_id, 
                        R.Person_id, 
                        MT.MorbusType_Name,
                        MT.MorbusType_id,
						convert(varchar(10), R.PersonRegister_disDate, 104) as PersonRegister_disDate,
                        convert(varchar(10), ps.Person_deadDT , 104) as Person_deadDT 
                    from v_PersonState PS with (nolock)
		    inner join dbo.PersonRegister R with(nolock) on R.Person_id = PS.Person_id
                    left join dbo.MorbusType MT with(nolock) on MT.MorbusType_id = R.MorbusType_id
                    left join dbo.BSKObject O with(nolock) on O.MorbusType_id = R.MorbusType_id
                    where R.Person_id = :Person_id and O.BSKObject_id is not null and MT.MorbusType_id in (84,88,89,19,50,110,111,112,113)
                    order by O.BSKObject_id
                  ";
        //         $query = "  select 
        //                        O.BSKObject_id, 
        //                        R.Person_id, 
        //                        MT.MorbusType_Name,
        //                        MT.MorbusType_id 
        //                    from dbo.PersonRegister R with(nolock)
        //                    left join dbo.MorbusType MT with(nolock) on MT.MorbusType_id = R.MorbusType_id
        //                    left join dbo.BSKObject O with(nolock) on O.MorbusType_id = R.MorbusType_id
        //                    where R.Person_id = :Person_id and O.BSKObject_id is not null
        //                    order by O.BSKObject_id
        //                  ";
        
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
     *  Проверка наличия пациента в регистре по предмету наблюдения
     */
    function checkPersonInRegister($params)
    {
        $params = array(
            'Person_id' => $params['Person_id'],
            'MorbusType_id' => $params['MorbusType_id']
        );
        
        $query = "select 
                    Person_id, 
                    MorbusType_id
                  from dbo.PersonRegister with(nolock) 
                  where Person_id = :Person_id
                  and MorbusType_id in (:MorbusType_id )
                  
        ";
        
        //and PersonRegister_Deleted !=2
        //echo getDebugSql($query, $params);
        //exit;
        
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
     * Получение сведений о пациенте
     */
    function loadPersonData($data)
    {
        // Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
        $object   = "v_PersonState";
        $filter   = " (1=1)";
        $params   = array(
            'Person_id' => $data['Person_id']
        );
        $InnField = "isnull(ps.Person_Inn,'') as Person_Inn";
        if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id'] > 0)) {
            $object              = "v_Person_bdz";
            $params['Server_id'] = $data['Server_id'];
            $filter .= " and PS.Server_id = :Server_id";
            $params['PersonEvn_id'] = $data['PersonEvn_id'];
            $filter .= " and PS.PersonEvn_id = :PersonEvn_id";
            $InnField = "isnull(ps.PersonInn_Inn,'') as Person_Inn";
        } else {
            $params['Person_id'] = $data['Person_id'];
            $filter .= " and PS.Person_id = :Person_id";
            $InnField = "isnull(ps.Person_Inn,'') as Person_Inn";
        }
        $extendFrom   = "";
        $extendSelect = "";
        if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
            $params['EvnDirection_id'] = $data['EvnDirection_id'];
            $extendSelect              = "
				,ED.EvnDirection_id
				,ED.EvnDirection_Num
				,ED.EvnDirection_setDT
			";
            $extendFrom .= "
				OUTER apply
				(SELECT top 1
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					isnull(convert(varchar(10), ED.EvnDirection_setDT, 104), '') as EvnDirection_setDT
					
				FROM
					v_EvnDirection_all ED WITH (NOLOCK)
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
				) as ED
				";
        }
        
        
        $query  = "
			SELECT TOP 1
				ps.Person_id,
                (select top 1 BSKRegistry_riskGroup from dbo.BSKRegistry with(nolock) where MorbusType_id = 84 and Person_id =  ps.Person_id order by BSKRegistry_setDate DESC) as BSKRegistry_riskGroup,
                
                (select BSKRegistryData_data from dbo.BSKRegistryData with(nolock) where BSKRegistry_id = 
                (
                select top 1 BSKRegistry_id from dbo.BSKRegistry with(nolock) 
                where 
                  Person_id = ps.Person_id  
                and
                  MorbusType_id = 88  
                order by   BSKRegistry_setDate DESC
                )
                and BSKObservElement_id = 151) as BSKRegistry_functionClass,            
                (select BSKRegistryData_data from dbo.BSKRegistryData with(nolock) where BSKRegistry_id = 
                (
                select top 1 BSKRegistry_id from dbo.BSKRegistry with(nolock)
                where 
                  Person_id = ps.Person_id  
                and
                  MorbusType_id = 89  
                order by   BSKRegistry_setDate DESC
                )
                and BSKObservElement_id = 269) as BSKRegistry_gegreeRisk,                   
				{$InnField},
				[dbo].[getPersonPhones](ps.Person_id, ',') as Person_Phone,
				ps.Server_id as Server_id,
				ps.Server_pid as Server_pid,
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN isnull(RTRIM(lpu.Lpu_Nick), '') + ' (Прикрепление неактуально. Дата открепления: '+isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '')+')' ELSE isnull(RTRIM(lpu.Lpu_Nick), '') end as Lpu_Nick,
				PersonState.Lpu_id as Lpu_id,
				pcard.PersonCard_id,
				isnull(RTRIM(PS.Person_SurName), '') as Person_Surname,
				isnull(RTRIM(PS.Person_FirName), '') as Person_Firname,
				isnull(RTRIM(PS.Person_SecName), '') as Person_Secname,
				isnull(RTRIM(PS.PersonEvn_id), '') as PersonEvn_id,
				isnull(convert(varchar(10), PS.Person_BirthDay, 104), '') as Person_Birthday,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
					+ case when month(PS.Person_Birthday) > month(dbo.tzGetDate())
					or (month(PS.Person_Birthday) = month(dbo.tzGetDate()) and day(PS.Person_Birthday) > day(dbo.tzGetDate()))
					then -1 else 0 end) as Person_Age,
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as KLAreaType_id,
				isnull(RTRIM(PS.Person_Snils), '') as Person_Snils,
				isnull(RTRIM(Sex.Sex_Name), '') as Sex_Name,
				isnull(RTRIM(Sex.Sex_Code), '') as Sex_Code,
				isnull(RTRIM(Sex.Sex_id), '') as Sex_id,
				isnull(RTRIM(SocStatus.SocStatus_Name), '') as SocStatus_Name,
				ps.SocStatus_id,
				RTRIM(isnull(UAddress.Address_Nick, UAddress.Address_Address)) as Person_RAddress,
				RTRIM(isnull(PAddress.Address_Nick, PAddress.Address_Address)) as Person_PAddress,
				isnull(RTRIM(Document.Document_Num), '') as Document_Num,
				isnull(RTRIM(Document.Document_Ser), '') as Document_Ser,
				isnull(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				isnull(RTRIM(DO.Org_Name), '') as OrgDep_Name,
				isnull(OmsSprTerr.OmsSprTerr_id, 0) as OmsSprTerr_id,
				isnull(OmsSprTerr.OmsSprTerr_Code, 0) as OmsSprTerr_Code,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE isnull(RTRIM(Polis.Polis_Ser), '') END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(vper.Person_EdNum), '') ELSE isnull(RTRIM(Polis.Polis_Num), '') END AS Polis_Num,
				isnull(convert(varchar(10), pcard.PersonCard_begDate, 104), '') as PersonCard_begDate,
				isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '') as PersonCard_endDate,
				isnull(convert(varchar(10), pcard.LpuRegion_Name, 104), '') as LpuRegion_Name,
				isnull(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				isnull(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				isnull(RTRIM(PO.Org_Name), '') as OrgSmo_Name,
				isnull(RTRIM(PJ.Org_id), '') as JobOrg_id,
				isnull(RTRIM(PJ.Org_Name), '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				'' as Ident_Lpu,
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as Person_IsRefuse,
				/* -- в v_Person_all (reg) нет этих полей, надо Тарасу сказать чтобы добавил 
				isnull(convert(varchar(10), ps.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), ps.Person_closeDT, 104), '') as Person_closeDT,
				ps.Person_IsDead,
				ps.PersonCloseCause_id
				*/
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS Person_IsBDZ,
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END AS Person_IsFedLgot,
				isnull(convert(varchar(10), Person.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), Person.Person_closeDT, 104), '') as Person_closeDT,
				Person.Person_IsDead,
				Person.PersonCloseCause_id,
				0 as Children_Count
				,PersonPrivilegeFed.PrivilegeType_id
				,PersonPrivilegeFed.PrivilegeType_Name
				{$extendSelect}
			FROM [{$object}] [PS] WITH (NOLOCK)
				left join [Sex] WITH (NOLOCK) on [Sex].[Sex_id] = [PS].[Sex_id]
				left join [SocStatus] WITH (NOLOCK) on [SocStatus].[SocStatus_id] = [PS].[SocStatus_id]
				left join [Address] [UAddress] WITH (NOLOCK) on [UAddress].[Address_id] = [PS].[UAddress_id]
				left join [v_KLArea] [KLArea] WITH (NOLOCK) on [KLArea].[KLArea_id] = [UAddress].[KLTown_id]
				left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].[Address_id] = [PS].[PAddress_id]
				left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
				left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
				left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
				left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
				left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
				left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
				left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
				left join [PolisType] WITH (NOLOCK) on [PolisType].[PolisType_id] = [Polis].[PolisType_id]
				left join [OmsSprTerr] WITH (NOLOCK) on [OmsSprTerr].[OmsSprTerr_id] = [Polis].[OmsSprTerr_id]
				left join [OrgSmo] WITH (NOLOCK) on [OrgSmo].[OrgSmo_id] = [Polis].[OrgSmo_id]
				left join [Org] [PO] WITH (NOLOCK) on [PO].[Org_id] = [OrgSmo].[Org_id]
				left join [Person] WITH (NOLOCK) on [Person].[Person_id] = [PS].[Person_id]
				left join [PersonState] with (nolock) on [PS].[Person_id] = [PersonState].[Person_id]
				outer apply (
				select Top 1 vper.Person_edNum
				from v_Person_all vper with(nolock) 
				where vper.Person_id = [PS].[Person_id]
				and vper.PersonEvn_id = PS.PersonEvn_id
				)vper
				OUTER apply
				(SELECT top 1
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP WITH (NOLOCK)
					inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.PrivilegeType_id <= 150 AND
					PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
					PP.Person_id = PS.Person_id
				) PersonPrivilegeFed
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc WITH (NOLOCK)
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					) as pcard 
				left join v_Lpu lpu WITH (NOLOCK) on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR WITH (NOLOCK) ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
		";
        //echo getDebugSQL($query, $params); exit;
                        sql_log_message('error', 'Search_model loadPersonData: ', getDebugSql($query, $params));
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Добавления пациента в PersonRegister
     */
    function saveInPersonRegister($data)
    {
		try {
			//$this->beginTransaction();

			$params = array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'Person_id' => $data['Person_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Diag_id' => $data['Diag_id'],
				'PersonRegister_Code' => $data['PersonRegister_Code'],
				'PersonRegister_setDate' => $data['PersonRegister_setDate'],
				'PersonRegister_disDate' => $data['PersonRegister_disDate'],
				'Morbus_id' => $data['Morbus_id'],
				'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
				'MedPersonal_iid' => $data['MedPersonal_iid'],
				'Lpu_iid' => $data['Lpu_iid'],
				'MedPersonal_did' => $data['MedPersonal_did'],
				'Lpu_did' => $data['Lpu_did'],
				'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
				'pmUser_id' => $data['pmUser_id']
			);
        
        //echo '<pre>' . print_r($params, 1) . '</pre>';
        /*
        echo 1; 
        $checkPersonInRegister = $this->checkPersonInRegister($params);
        echo 3;
        if($checkPersonInRegister === false){
        return false;
        }
        else{
        */
        /*
        $query = "
                insert into dbo.PersonRegister
                (
                   Person_id
                  ,MorbusType_id
                  ,Diag_id
                  ,PersonRegister_Code
                  ,PersonRegister_setDate
                  ,PersonRegisterOutCause_id
                  ,MedPersonal_iid
                  ,Lpu_iid
                  ,EvnNotifyBase_id
                  ,pmUser_insID
                  ,pmUser_updID
                  ,PersonRegister_insDT
                  ,PersonRegister_updDT
                  ,PersonRegister_disDate
                  ,MedPersonal_did
                  ,Lpu_did
                  ,Morbus_id
                )
                values
                (
                  :Person_id,
                  :MorbusType_id,
                  null,
                  :PersonRegister_Code,
                  :PersonRegister_setDate,
                  null,
                  :MedPersonal_iid,
                  :Lpu_iid,
                  :EvnNotifyBase_id,
                  :pmUser_id,
                  :pmUser_id,
                  getdate(),
                  getdate(),
                  null,
                  :MedPersonal_did,
                  :Lpu_did,
                  :Morbus_id
                  
                )
            ";
        */
        $query = "
            declare 
                 @Person_id bigint,
                 @MorbusType_id bigint,
                 @Diag_id bigint,
                 @PersonRegister_setDate datetime = null,
                 @PersonRegister_Code varchar(20),
                 @Morbus_id bigint,
                 @PersonRegisterOutCause_id bigint,
                 @MedPersonal_iid bigint,
                 @MedPersonal_did bigint,
                 @Lpu_did bigint,
                 @Lpu_iid bigint,
                 @EvnNotifyBase_id bigint,
                 @pmUser_id bigint,
                 @Error_Code int,
                 @Error_Message varchar(4000)
                 
            exec dbo.p_PersonRegister_ins
               
                  @Person_id = :Person_id,
                  @MorbusType_id = :MorbusType_id,
                  @Diag_id = :Diag_id,
                  @PersonRegister_Code = :PersonRegister_Code,
                  @PersonRegister_setDate = :PersonRegister_setDate,
                  @PersonRegister_disDate = :PersonRegister_disDate,
                  @MedPersonal_iid = :MedPersonal_iid,
                  @Lpu_iid = :Lpu_iid,
                  @EvnNotifyBase_id = :EvnNotifyBase_id,
                  @pmUser_id = :pmUser_id,
                  @MedPersonal_did = :MedPersonal_did,
                  @Lpu_did = :Lpu_did,
                  @Morbus_id = :Morbus_id,
                  @PersonRegisterOutCause_id = :PersonRegisterOutCause_id, 
                  @Error_Code = @Error_Code output,
                  @Error_Message = @Error_Message output                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;";
        //echo getDebugSql($query, $params);
        //exit;
		$result = $this->db->query($query, $params);
		// Рассылка уведомлений
		$resUsers =  $this->queryResult('
				SELECT puc.pmUser_id
				FROM v_PersonCard pc WITH (nolock) 
					INNER JOIN v_LpuRegion lp ON lp.LpuRegion_id = pc.LpuRegion_id
					INNER JOIN v_MedStaffRegion msr ON lp.LpuRegion_id = msr.LpuRegion_id
						and msr.MedStaffRegion_isMain = 2
						and (msr.MedStaffRegion_begDate is null or msr.MedStaffRegion_begDate <= dbo.tzGetdate())
						and (msr.MedStaffRegion_endDate is null or msr.MedStaffRegion_endDate >= dbo.tzGetdate())
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id
					INNER JOIN dbo.pmUserCache puc on puc.MedPersonal_id = ISNULL(msf.MedPersonal_id, msr.MedPersonal_id)
				WHERE pc.LpuAttachType_id = 1
					and pc.Person_id = :Person_id
			', [
			'Person_id' => $data['Person_id']
		]);

		if ( is_array($resUsers) && count($resUsers) > 0 ) {
			$userList = [];

			foreach ( $resUsers as $row ) {
				if ( !in_array($row['pmUser_id'], $userList) ) {
					$userList[] = $row['pmUser_id'];
				}
			}

			$persData = $this->getFirstRowFromQuery("
					SELECT top 1
						ps.Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName,
						convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay
					FROM v_PersonState ps WITH (nolock)
					WHERE ps.Person_id = :Person_id
				", [
				'Person_id' => $data['Person_id']
			]);

			if ( $persData === false || !is_array($persData) || count($persData) == 0 ) {
				throw new Exception('Ошибка при получении данных пациента');
			}

			$datasend = [
				'Message_id' => null,
				'Message_pid' => null,
				'Message_Subject' => 'Пациент включен в регистр БСК',
				'Message_Text' => "Пациент вашего участка " . $persData['Person_SurName'] . " ". $persData['Person_FirName'] . " " . $persData['Person_SecName'] . ", дата рождения " . $persData['Person_BirthDay'] . " включен в регистр БСК",
				'pmUser_id' => $data['pmUser_id'],
				'UserSend_ID' => 0,
				'Lpus' => '',
				'pmUser_Group' => '',
				'Message_isSent' => 1,
				'NoticeType_id' => 5,
				'Message_isFlag' => 1,
				'Message_isDelete' => 1,
				'RecipientType_id' => 1,
				'action' => 'ins',
				'MessageRecipient_id' => null,
				'Message_isRead' => null,
			];

			$this->load->model("Messages_model", "msmodel");

			$result = $this->msmodel->insMessage($datasend);

			if ( !is_array($result) || count($result) == 0 || empty($result[0]['Message_id']) ) {
				throw new Exception('Ошибка при добавлении сообщения');
			}
			else if ( !empty($result[0]['Error_Msg']) ) {
				throw new Exception($result[0]['Error_Msg']);
			}

			$Message_id = $result[0]['Message_id'];

			foreach ( $userList as $pmUser_id ) {
				$result = $this->msmodel->insMessageLink($Message_id, $pmUser_id, $datasend);

				if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageLink_id']) ) {
					throw new Exception('Ошибка при добавлении сообщения');
				}
				else if ( !empty($result[0]['Error_Msg']) ) {
					throw new Exception($result[0]['Error_Msg']);
				}

				$result = $this->msmodel->sendMessage($datasend, $pmUser_id, $Message_id);

				if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageRecipient_id']) ) {
					throw new Exception('Ошибка при добавлении сообщения');
				}
				else if ( !empty($result[0]['Error_Msg']) ) {
					throw new Exception($result[0]['Error_Msg']);
				}
			}
		}

		//$this->commitTransaction();
		}
		catch ( Exception $e ) {
			//$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}



        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        //}
    }
    /**
     * Получение списка пользователей (только врачей)
     */
    function getCurrentOrgUsersList($lpu_id = null) {
        $query = "
                select distinct
                        uc.pmUser_id
                        ,RTRIM(uc.pmUser_Name) +' (' + RTRIM(uc.pmUser_Login) +')' as pmUser_FioL
                        ,RTRIM(uc.pmUser_Name) as pmUser_Fio
                        ,RTRIM(uc.pmUser_Login) as pmUser_Login
                        ,RTRIM(mp.MedPersonal_Code) as MedPersonal_Code
                        ,RTRIM(mp.MedPersonal_TabCode) as MedPersonal_TabCode
                from pmUserCache uc with (nolock)
                join MedPersonalCache mp on uc.MedPersonal_id = mp.MedPersonal_id  and uc.Lpu_id = mp.Lpu_id
                where uc.MedPersonal_id is not null
        "; 
        if ($lpu_id != null) {
            $query = $query.' and mp.lpu_id = :lpu_id';
        }
        $res = $this->db->query($query, array('lpu_id' => $lpu_id));
        if ( is_object($res) ) {
                return $res->result('array');
        }
        else {
                return false;
        }
    }    
    /**
     * Сохранение в регистр БСК (предмет наблюдения ОКС)
     */
    function saveKvsInOKS($data) {

        $params = array(
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id']
        );

        if(is_null($data['Person_id']) || $data['Person_id'] == ''){
            return array('Error_Msg' => 'Ошибка сохранения ОКС! Не определен пациент!');
        }

        //Проверить и записать пациента в dbo.PersonRegister с MorbusType = 19
        if($this->checkPersonInRegister($params) !== false){
            $params = array(  
                'Person_id' => $data['Person_id'],
                'MorbusType_id' => $data['MorbusType_id'],
                'Diag_id' => $data['Diag_id'],
                'PersonRegister_Code' =>null,
                'PersonRegister_setDate' => date('Y-m-d H:i:s'),
                'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? date('Y-m-d', strtotime('+1 year')) : null,
                'Morbus_id' => null,
                'PersonRegisterOutCause_id' => null,
                'MedPersonal_iid' => null,
                'Lpu_iid' => null,
                'MedPersonal_did' => null,
                'Lpu_did' =>null,
                'EvnNotifyBase_id' => null,
                'pmUser_id' => $data['pmUser_id'],
                'PersonRegister_id'=>null
            );
            $this->saveInPersonRegister($params);
         }
         $registryData = array(
            'setDate'=>date('Y-m-d H:s'),
            'Person_id'=>$data['Person_id'],
            'MorbusType_id'=>$data['MorbusType_id'],
            'riskGroup'=>null,
            'pmUser_id'=>$data['pmUser_id'],
            'ListAnswers'=>array(
                271=>array(
                    'BSKObservElement_id'=>271,
                    'BSKRegistryData_data'=>$data['PainDT'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                272=>array(
                    'BSKObservElement_id'=>272,
                    'BSKRegistryData_data'=>$data['ECGDT'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1   
                ),
                273=>array(
                    'BSKObservElement_id'=>273,
                    'BSKRegistryData_data'=>$data['ResultECG'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                274=>array(
                    'BSKObservElement_id'=>274,
                    'BSKRegistryData_data'=>$data['TLTDT'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                276=>array(
                    'BSKObservElement_id'=>276,
                    'BSKRegistryData_data'=>$data['LpuDT'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                300=>array(
                    'BSKObservElement_id'=>300,
                    'BSKRegistryData_data'=>$data['DiagOKS'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                302=>array(
                    'BSKObservElement_id'=>302,
                    'BSKRegistryData_data'=>$data['ZonaCHKV'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                304=>array(
                    'BSKObservElement_id'=>304,
                    'BSKRegistryData_data'=>$data['MOHospital'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                398=>array(
                    'BSKObservElement_id'=>398,
                    'BSKRegistryData_data'=>$data['EvnPS_NumCard'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                399=>array(
                    'BSKObservElement_id'=>399,
                    'BSKRegistryData_data'=>$data['TimeFromEnterToChkv'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
                400=>array(
                    'BSKObservElement_id'=>400,
                    'BSKRegistryData_data'=>$data['LeaveType_Name'],
                    'BSKUnits_name'=>null,
                    'BSKRegistryData_insDT'=>"getdate()",
                    'pmUser_insID'=>$data['pmUser_id'],
                    'pmUser_updID'=>null,
                    'BSKRegistryData_deleted'=>1
                ),
				410=>array(
					'BSKObservElement_id'=>410,
					'BSKRegistryData_data'=>$data['diagDir'],
					'BSKUnits_name'=>null,
					'BSKRegistryData_insDT'=>"getdate()",
					'pmUser_insID'=>$data['pmUser_id'],
					'pmUser_updID'=>null,
					'BSKRegistryData_deleted'=>1
				),
				411=>array(
					'BSKObservElement_id'=>411,
					'BSKRegistryData_data'=>$data['diagPriem'],
					'BSKUnits_name'=>null,
					'BSKRegistryData_insDT'=>"getdate()",
					'pmUser_insID'=>$data['pmUser_id'],
					'pmUser_updID'=>null,
					'BSKRegistryData_deleted'=>1
				),
				412=>array(
					'BSKObservElement_id'=>412,
					'BSKRegistryData_data'=>$data['LpuSection'],
					'BSKUnits_name'=>null,
					'BSKRegistryData_insDT'=>"getdate()",
					'pmUser_insID'=>$data['pmUser_id'],
					'pmUser_updID'=>null,
					'BSKRegistryData_deleted'=>1
				),
				413=>array(
					'BSKObservElement_id'=>413,
					'BSKRegistryData_data'=>$data['KAGDT'],
					'BSKUnits_name'=>null,
					'BSKRegistryData_insDT'=>"getdate()",
					'pmUser_insID'=>$data['pmUser_id'],
					'pmUser_updID'=>null,
					'BSKRegistryData_deleted'=>1
				)
            )
        );
        if($data['Registry_method'] == 'ins') {
            // добавляемe
            $result = $this->saveRegistry($data, $registryData);                           
        } else {
            // обноляем
            $result = $this->updateRegistry($data, $registryData);
        }

        return $result;
    }
    
    /**
     * Получение идентификатора анкеты по дате содания квс
     */
    function getOksId($data) {
        
        $params = array(
            'Person_id' => $data['Person_id'],
            'EvnPS_NumCard' => $data['EvnPS_NumCard']
        );

        $query = 'SELECT TOP 1
                        P.Person_id,
                        P.Person_deadDT,
                        BRD.BSKRegistry_id
                    FROM
                        v_Person P with(nolock)
                        outer apply (select BSKRegistry_id from dbo.BSKRegistryData with(nolock) where BSKObservElement_id = 398 and BSKRegistryData_data = :EvnPS_NumCard) BRD
                    WHERE
                        P.Person_id = :Person_id
        ';
        
        $result = $this->db->query($query, $params);
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Проставление признака просмотра анкеты
     */
    function setIsBrowsed($data) {

        $params = array(
            'BSKRegistry_id' => $data['BSKRegistry_id']
        );

        $query = "UPDATE {$this->scheme}.BSKRegistry
                SET BSKRegistry_isBrowsed = 2
                WHERE BSKRegistry_id = :BSKRegistry_id";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	* Основной диагноз из КВС
	*/
	function getDiagFromEvnPS($data){

		$params = array(
			'EvnPS_id' => $data['EvnPS_id']
		);

		$query = "
			select 
			EvnPS.EvnPS_id,
			EvnPS.Diag_id,
			Diag.Diag_Code,
			Diag.Diag_FullName
			from v_EvnPS EvnPS with (nolock)
			inner join v_Diag Diag with (nolock) on Diag.Diag_id = EvnPS.Diag_id
			where EvnPS.EvnPS_id = :EvnPS_id 
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     *  Проверка наличия пациента в регистре БСК
     */
	function checkPersonInRegisterforEMK($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "select 
					Person_id, 
					MorbusType_id
				from dbo.PersonRegister with(nolock) 
				where Person_id = :Person_id
                and MorbusType_id in (84,88,89,19,50,110,111,112,113)
		";
		$result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
	}
	/**
	 * Таблица «Услуги». Отображаются операционные и общие услуги, проведённые пациенту
	 */
	function getListUslugforEvents($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			with UslugaPid (
				EvnUsluga_id
			) as (
				select EvnUsluga_id from v_EvnUsluga EU with (nolock)
				where  (1=1)
				and EU.Person_id = :Person_id
				and (EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper'))
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			)
			select
				EU.EvnUsluga_id
				,EPS.EvnPS_id
				,EU.EvnUsluga_pid
				,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
				,EU.EvnUsluga_setTime
				,UC.UslugaComplex_Code as Usluga_Code
				,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
				,l.Lpu_Nick
			from
			v_EvnUsluga EU with (nolock)
				left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = EU.EvnUsluga_pid
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_Lpu l with (nolock) on EU.Lpu_id = l.Lpu_id
			where
				exists(
					Select top 1 1
					from UslugaPid with(nolock) 
					where UslugaPid.EvnUsluga_id = EU.EvnUsluga_id)
			
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
				and EPS.EvnPS_id is not null
				and UC.UslugaComplex_id in (select UslugaComplex_id from dbo.BSKRegistryUslugaComplex)
			order by EU.EvnUsluga_setDate desc 
		";
		//TODO: вынести сортировку в PHP
		//echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getBSKObjectWithoutAnket($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id'],
			'Person_id' => $data['Person_id'],
		);

		$query = "
			select 
				convert(varchar(10), pr.PersonRegister_setDate, 120) PersonRegister_setDate,
				pr.MorbusType_id,
				mt.MorbusType_Name,
			pr.Person_id
			from v_PersonRegister pr with (nolock)
			inner join dbo.MorbusType mt with (nolock) on mt.MorbusType_id = pr.MorbusType_id
			where Person_id = :Person_id
			and pr.MorbusType_id = :MorbusType_id
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Случаи оказания амбулаторно-поликлинической медицинской помощи
	 */
	function getListPersonCureHistoryPL($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				ec.EvnClass_SysNick + '_' + cast(epl.EvnPL_id as varchar(20)) as Evn_id
				,epl.EvnPL_id
				,epl.Person_id
				,epl.PersonEvn_id,
				convert(varchar(10), epl.EvnPL_setDate,104) as EvnPL_setDate
				,d.Diag_Code + ' ' + d.Diag_Name as Diag_Name
				,l.Lpu_Nick
				,ls.LpuSection_Name
				,MP.Person_Fio
				,epl.EvnPL_NumCard as EvnPL_NumCard
				,evpl.LpuSection_id
				,evpl.MedPersonal_id
				,evpl.MedStaffFact_id
				,epl.EvnPL_IsFinish
			from v_EvnPL epl with (nolock)
				inner join EvnClass ec with (nolock) on ec.EvnClass_id = epl.EvnClass_id
				left join v_EvnVizitPL evpl with (nolock) on evpl.EvnVizitPL_pid = epl.EvnPL_id and evpl.EvnVizitPL_Index = evpl.EvnVizitPL_Count - 1
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = evpl.LpuSection_id
				left join v_Diag d (nolock) on d.Diag_id = epl.Diag_id
				left join v_Lpu l (nolock) on l.Lpu_id = epl.Lpu_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = evpl.MedPersonal_id
				) MP
			where (1 = 1)
				and epl.Person_id = :Person_id
				and epl.EvnClass_id in (3)
				and ls.LpuSectionProfile_Code in (
					'625',
					'825',
					'525',
					'602',
					'802',
					'502',
					'623',
					'823',
					'523',
					'632',
					'832',
					'532',
					'655',
					'855',
                    '555')
            order by epl.EvnPL_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Таблица «Случаи оказания стационарной медицинской помощи»
	 */
	function getListPersonCureHistoryPS($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				ec.EvnClass_SysNick + '_' + cast(eps.EvnPS_id as varchar(20)) as Evn_id
				,eps.EvnPS_id
				,eps.Person_id
				,eps.PersonEvn_id
				,eps.Server_id,
				convert(varchar(10), eps.EvnPS_setDate,104) as EvnPS_setDate
				,d.Diag_Code + ' ' + d.Diag_Name as Diag_Name
				,l.Lpu_Nick
				,MP.Person_Fio
				,eps.EvnPS_NumCard
				,ls.LpuSection_Name
				,null as MedPersonal_Fio
				,eps.EvnPS_setDT as Evn_setDT
				,eps.EvnPS_disDT as Evn_disDT
			from v_EvnPS eps with (nolock)
				inner join EvnClass ec with (nolock) on ec.EvnClass_id = eps.EvnClass_id
				left join v_EvnSection es with (nolock) on es.EvnSection_pid = eps.EvnPS_id
					and es.EvnSection_Index = es.EvnSection_Count - 1
				left join v_Diag d (nolock) on d.Diag_id = eps.Diag_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = eps.LpuSection_id
				left join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = es.MedPersonal_id
				) MP
			where (1 = 1)
				and eps.Person_id = :Person_id
				and ls.LpuSectionProfile_Code in (
					'2032',	
					'1032',	
					'2008',
					'2058',
					'3058',
					'1058',
					'1059',
					'3008',
					'1008',
					'2100',
					'1009',
					'6012',
					'6013',
					'2024',
					'3024',
					'1024',
					'654',
					'854',
					'554',
					'2075',
					'3075',
					'1075'
                )
            order by eps.EvnPS_setDate desc
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
    }
    /**
	 * Таблица «Сопутствующие диагнозы»
	 */
	function getListPersonCureHistoryDiagSop($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
			with Evn(
				EvnClass_SysNick,
				Evn_id,
				Person_id,
				PersonEvn_id,
				Server_id,
				Diag_id,
				SetDate,
				Lpu_id,
				LpuSection_id, 
				MedPersonal_id,
				NumCard
			) as (
			
			select
				'EvnDiagPLSop',
				epl.EvnPL_id,
				epl.Person_id,
				epl.PersonEvn_id,
				epl.Server_id,
				DiagPS.Diag_id,
				DiagPS.EvnDiagPLSop_setDate,
				es.Lpu_id,
				es.LpuSection_id,
				es.MedPersonal_id,
				epl.EvnPL_NumCard
			from v_EvnPL epl with (nolock)
				inner join v_EvnVizitPL es with (nolock) on es.EvnVizitPL_pid = epl.EvnPL_id and es.EvnVizitPL_Index = 0
				cross apply (
					select DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_Name 
					from v_EvnDiagPLSop DiagPS with (nolock)
						inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPLSop_rid = epl.EvnPL_rid
						and (dPS.Diag_Code like 'I%' or dPS.Diag_Code like 'G45%' or dPS.Diag_Code like 'G46%' or dPS.Diag_Code like 'J44%' or dPS.Diag_Code like 'N18%'
						or dPS.Diag_Code like 'N19%' or dPS.Diag_Code like 'С[0-9][0-7]%' or dPS.Diag_Code like 'D[0-4][0-8]%' or dPS.Diag_Code like 'E10%'
						or dPS.Diag_Code like 'E11%' or dPS.Diag_Code like 'E12%' or dPS.Diag_Code like 'E13%' or dPS.Diag_Code like 'E14%')
					) DiagPS
			where (1=1)
			and epl.Person_id = :Person_id
			union all
			select
				'EvnDiagPS',
				EPS.EvnPS_id,
				EPS.Person_id,
				EPS.PersonEvn_id,
				EPS.Server_id,
				DiagPS.Diag_id,
				DiagPS.EvnDiagPS_setDate,
				es.Lpu_id,
				es.LpuSection_id,
				es.MedPersonal_id,
				EPS.EvnPS_NumCard
			from v_EvnPS EPS with (nolock)
				inner join v_EvnSection es with (nolock) on es.EvnSection_pid = EPS.EvnPS_id and es.EvnSection_Index = 0
				cross apply (
				select DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_Name
				from v_EvnDiagPS DiagPS with (nolock)
				inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
				where DiagPS.EvnDiagPS_rid = EPS.EvnPS_rid
				and (dPS.Diag_Code like 'I%' or dPS.Diag_Code like 'G45%' or dPS.Diag_Code like 'G46%' or dPS.Diag_Code like 'J44%' or dPS.Diag_Code like 'N18%'
				or dPS.Diag_Code like 'N19%' or dPS.Diag_Code like 'С[0-9][0-7]%' or dPS.Diag_Code like 'D[0-4][0-8]%' or dPS.Diag_Code like 'E10%'
				or dPS.Diag_Code like 'E11%' or dPS.Diag_Code like 'E12%' or dPS.Diag_Code like 'E13%' or dPS.Diag_Code like 'E14%')
				and DiagPS.DiagSetClass_id in (3)
				) DiagPS
			where (1=1)
			and EPS.Person_id = :Person_id
			)
			select *, convert(varchar(10), SetDate,104) as Diag_setDate from (
				select 
					row_number() over (partition by Evn.Diag_id order by Evn.Diag_id, Evn.SetDate asc ) num,
					Evn.EvnClass_SysNick,
					Evn.Evn_id,
					Evn.Person_id,
					Evn.PersonEvn_id,
					Evn.Diag_id,
					d.Diag_FullName,
					Evn.Server_id,
					Evn.SetDate,
					Lpu.Lpu_Nick,
					LS.LpuSection_Name,
					MP.Person_Fio,
					Evn.NumCard
				from Evn with (nolock)
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = Evn.Lpu_id
					left join v_Diag d with (nolock) on d.Diag_id = Evn.Diag_id
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = Evn.MedPersonal_id and MP.Lpu_id = Evn.Lpu_id
					left join v_LpuSection LS with (nolock) on LS.LpuSection_id = Evn.LpuSection_id
				where (1=1)
				) T
				where T.num = 1
				order by SetDate desc
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Таблица «Постинфарктный кардиосклероз»
	 */
	function getListPersonCureHistoryDiagKardio($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
		with Evn (
			EvnClass_SysNick,
			Person_id,
			Evn_id,
			EvnDiagPS_setDate,
			Diag_id,
			Diag_FullName,
			Lpu_id,
			Lpu_Nick,
			LpuSection_id,
			LpuSection_Name,
			MedPersonal_id,
			Person_Fio,
			NumCard
			) as (
				select 
					'EvnDiagPS',
					ps.Person_id,
					ps.EvnPS_id Evn_id,
					case when d.Diag_Code in ('I25.2') then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ('I25.2') then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ('I25.2') then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio,
					ps.EvnPS_NumCard
				from v_EvnPS ps with (nolock)
					inner join v_EvnSection es with (nolock) on es.EvnSection_pid = ps.EvnPS_id and es.EvnSection_Index = 0
					left join v_Diag d with (nolock) on d.Diag_id = ps.Diag_id 
					outer apply (
						select DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
						from v_EvnDiagPS DiagPS with (nolock)
						inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPS_rid = ps.EvnPS_rid
						and dPS.Diag_Code in ('I25.2')
						and DiagPS.DiagSetClass_id in (3,2)
					) DiagPS
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where ps.Person_id = :Person_id
					and (d.Diag_Code in ('I25.2')
					or DiagPS.Diag_id is not null)
					and LS.LpuSectionProfile_Code in ('2032','1032','2008','2058','3058','1058','1059','3008','1008','2100','1009','6012','6013','2024','3024','1024','654','854','554','2075','3075','1075')
				
				UNION ALL

				select 
					'EvnDiagPLSop',
					pl.Person_id,
					pl.EvnPL_id Evn_id,
					case when d.Diag_Code in ('I25.2') then es.EvnVizitPL_setDate else DiagPS.EvnDiagPLSop_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ('I25.2') then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ('I25.2') then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio,
					pl.EvnPL_NumCard
				from v_EvnPL pl with (nolock)
					inner join v_EvnVizitPL es with (nolock) on es.EvnVizitPL_pid = pl.EvnPL_id and es.EvnVizitPL_Index = 0
					left join v_Diag d with (nolock) on d.Diag_id = pl.Diag_id 
					outer apply (
						select DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_FullName
						from v_EvnDiagPLSop DiagPS with (nolock)
						inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPLSop_rid = pl.EvnPL_rid
						and dPS.Diag_Code in ('I25.2')
					) DiagPS
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
					left join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where pl.Person_id = :Person_id
					and (d.Diag_Code in ('I25.2')
					or DiagPS.Diag_id is not null)
					and LS.LpuSectionProfile_Code in ('625','825','525','602','802','502','623','823','523','632','832','532','655','855','555')
			  )
			  select *, convert(varchar(10), EvnDiagPS_setDate,104) as Diag_setDate from (
				  select 
						row_number() over (partition by Evn.Diag_id order by Evn.Diag_id, Evn.EvnDiagPS_setDate asc ) num,
						Evn.EvnClass_SysNick,
						Evn.Person_id,
						Evn.Evn_id,
						convert(varchar(10), Evn.EvnDiagPS_setDate, 104) as EvnDiagPS_setDate,
						Evn.Diag_id,
						Evn.Diag_FullName,
						Evn.Lpu_id,
						Evn.Lpu_Nick,
						Evn.LpuSection_id,
						Evn.LpuSection_Name,
						Evn.MedPersonal_id,
						Evn.Person_Fio,
						Evn.NumCard
					from Evn with (nolock)
					) T
					where T.num = 1
			
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Вкладка «Исследования»
	 */
	function getLabResearch($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				EUP.EvnUslugaPar_id,
				convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
				UslugaComplex.UslugaComplex_Code,
				UslugaComplex.UslugaComplex_Name,
				Lpu.Lpu_Nick,
				doc.EvnXml_id,
				doc.XmlTemplate_HtmlTemplate,
				doc.UslugaTest_ResultValue as EvnUslugaPar_ResultValue,
				doc.UslugaTest_ResultUnit as EvnUslugaPar_ResultUnit,
				EvnXml_id as prosmotr
			from
				v_PersonState PS with (nolock)
				inner join v_EvnUslugaPar EUP with (nolock) on EUP.Person_id = PS.Person_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EUP.Lpu_id
				outer apply (
						select top 1 EvnXml.EvnXml_id, xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate, ut.UslugaTest_ResultValue, ut.UslugaTest_ResultUnit
						from v_EvnXml  EvnXml with (NOLOCK)
						left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
						left join v_UslugaTest ut (nolock) on ut.UslugaTest_pid = EvnXml.Evn_id
						left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id
						where EvnXml.Evn_id = EUP.EvnUslugaPar_id
						order by EvnXml_insDT desc
				) doc
				left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id
			where
				(1=1)
				and UslugaComplex.UslugaComplex_Code in (
					'A09.05.026',
					'A09.05.028',
					'A09.05.004',
					'A09.05.025',
					'A09.05.027',
					'A09.05.023',
					'A09.05.023',
					'A12.22.005',
					'A09.05.042',
					'A09.05.177',
					'A09.05.042',
					'A09.05.009',
					'A09.05.003',
					'A12.05.118',
					'A12.30.014',
					'A09.28.006.002',
					'A09.28.003.005',
					'A09.28.010')
				and doc.EvnXml_id is not null
				and EUP.EvnUslugaPar_setDate is not null
				and PS.Person_id = :Person_id
			order by
				EUP.EvnUslugaPar_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Вкладка «Обследования»
	 */
	function getLabSurveys($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				EUP.EvnUsluga_id,
				convert(varchar(10), EUP.EvnUsluga_setDate, 104) as EvnUsluga_setDate,
				UslugaComplex.UslugaComplex_Code,
				UslugaComplex.UslugaComplex_Name,
				Lpu.Lpu_Nick,
				doc.EvnXml_id,
				EvnXml_id as prosmotr
			from
				v_PersonState PS with (nolock)
				inner join v_EvnUsluga EUP with (nolock) on EUP.Person_id = PS.Person_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EUP.Lpu_id
				outer apply (
						select top 1 EvnXml_id
						from v_EvnXml with (nolock)
						where Evn_id = EUP.EvnUsluga_id
						order by EvnXml_insDT desc 
					) doc
					left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id
			where
				(1=1)
				and UslugaComplex.UslugaComplex_id in (select UslugaComplex_id from dbo.BSKRegistryUslugaComplexSurvey)
				and doc.EvnXml_id is not null
				and EUP.EvnUsluga_setDate is not null
				and PS.Person_id = :Person_id
			order by
				EUP.EvnUsluga_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * loadBSKEvnGrid
	 */
	function loadBSKEvnGrid($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id'],
			'Person_id' => $data['Person_id'],
		);
		switch ($data['MorbusType_id']) {
			//ХСН
			case 110: 
				$filterDiag_Code = "'I50.0','I50.1','I50.9'";
				$query = "
					select 
						convert(varchar(10), pr.PersonRegister_setDate, 120) PersonRegister_setDate,
						pr.MorbusType_id,
						Evn.Person_id,
						Evn.Evn_id,
						convert(varchar(10), Evn.EvnDiagPS_setDate, 104) EvnDiagPS_setDate,
						Evn.Diag_id,
						Evn.Diag_FullName,
						Evn.Lpu_id,
						Evn.Lpu_Nick,
						Evn.LpuSection_id,
						Evn.LpuSection_Name,
						Evn.MedPersonal_id,
						Evn.Person_Fio,
						Evn.HSNStage_id,
						Evn.HSNStage_Name,
						Evn.HSNFuncClass_id,
						Evn.HSNFuncClass_Name
					from v_PersonRegister pr with (nolock) 
					left join (
					select 
						ps.Person_id,
						ps.EvnPS_id Evn_id,
						case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
						case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
						case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
						Lpu.Lpu_id,
						Lpu.Lpu_Nick,
						LS.LpuSection_id,
						LS.LpuSection_Name,
						mp.MedPersonal_id,
						mp.Person_Fio,
						HSNStage.HSNStage_id,
						HSNStage.HSNStage_Name,
						HSNFuncClass.HSNFuncClass_id,
						HSNFuncClass.HSNFuncClass_Name
					from v_EvnPS ps with (nolock)
					inner join v_EvnSection es with (nolock) on es.EvnSection_pid = ps.EvnPS_id
					inner join v_DiagHSNDetails HSNDet with (nolock) on HSNDet.Evn_id = es.EvnSection_id
					left join HSNStage with (nolock) on HSNStage.HSNStage_id = HSNDet.HSNStage_id
					left join HSNFuncClass with (nolock) on HSNFuncClass.HSNFuncClass_id = HSNDet.HSNFuncClass_id
					left join v_Diag d with (nolock) on d.Diag_id = ps.Diag_id 
					outer apply (
						select top 1 DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
						from v_EvnDiagPS DiagPS with (nolock)
						inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPS_pid = es.EvnSection_id
							and DiagPS.DiagSetClass_id = 2
							and dPS.Diag_Code in ({$filterDiag_Code})
						order by DiagPS.EvnDiagPS_setDate
					) DiagPS
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
					where ps.Person_id = :Person_id
					UNION ALL
					select 
						pl.Person_id,
						pl.EvnPL_id Evn_id,
						es.EvnVizitPL_setDate EvnDiagPS_setDate,
						case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else dPS.Diag_id end Diag_id,
						case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else dPS.Diag_FullName end Diag_FullName,
						Lpu.Lpu_id,
						Lpu.Lpu_Nick,
						LS.LpuSection_id,
						LS.LpuSection_Name,
						mp.MedPersonal_id,
						mp.Person_Fio,
						HSNStage.HSNStage_id,
						HSNStage.HSNStage_Name,
						HSNFuncClass.HSNFuncClass_id,
						HSNFuncClass.HSNFuncClass_Name
					from v_EvnPL pl with (nolock)
					inner join v_EvnVizitPL es with (nolock) on es.EvnVizitPL_pid = pl.EvnPL_id and es.EvnVizitPL_Index = 0
					inner join v_DiagHSNDetails HSNDet with (nolock) on HSNDet.Evn_id = es.EvnVizitPL_id
					left join HSNStage with (nolock) on HSNStage.HSNStage_id = HSNDet.HSNStage_id
					left join HSNFuncClass with (nolock) on HSNFuncClass.HSNFuncClass_id = HSNDet.HSNFuncClass_id
					left join v_Diag d with (nolock) on d.Diag_id = pl.Diag_id 
					left join v_Diag dPS with (nolock) on dPS.Diag_id = es.Diag_agid and dPS.Diag_Code in ({$filterDiag_Code})
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
					left join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
					where pl.Person_id = :Person_id
					) Evn on evn.Person_id = pr.Person_id
					where pr.Person_id = :Person_id
						and pr.MorbusType_id = :MorbusType_id
				";
				$result = $this->db->query($query, $params);

				if (is_object($result)) {
					return $result->result('array');
				} else {
					return false;
				}		
			break;
			//Приобретённые пороки сердца
			case 111: 
				$filterDiag_Code = "'I05.0','I05.1','I05.2','I06.0','I06.1','I06.2','I07.0','I07.1','I07.2','I08.0','I08.1',
				'I08.2','I08.3','I08.8','I34.0','I34.1','I34.2','I34.8','I35.0','I35.1','I35.2','I35.8','I36.0','I36.1','I36.2','I36.8','I37.0','I37.1','I37.2','I37.8','I33.0'";
			break;
			//Врождённые пороки сердца
			case 112: 
				$filterDiag_Code = "'Q20.0','Q20.1','Q20.2','Q20.3','Q20.4','Q20.5','Q20.6','Q20.8','Q21.0',
				'Q21.1','Q21.2','Q21.3','Q21.4','Q21.8','Q22.0','Q22.1','Q22.2','Q22.3','Q22.4','Q22.5','Q22.6','Q22.8','Q23.0','Q23.1','Q23.2','Q23.3',
				'Q23.4','Q23.8','Q24.0','Q24.1','Q24.2','Q24.3','Q24.4','Q24.5','Q24.6','Q24.8','Q25.0','Q25.1','Q25.2','Q25.3','Q25.4','Q25.5','Q25.6',
				'Q25.7','Q25.8','Q26.0','Q26.1','Q26.2','Q26.3','Q26.4','Q26.5','Q26.6','Q26.8'";
			break;
			default:
				return false;
			break;
		}

		$query = "
			select 
				convert(varchar(10), pr.PersonRegister_setDate, 120) PersonRegister_setDate,
				pr.MorbusType_id,
				Evn.Person_id,
				Evn.Evn_id,
				convert(varchar(10), Evn.EvnDiagPS_setDate, 104) EvnDiagPS_setDate,
				Evn.Diag_id,
				Evn.Diag_FullName,
				Evn.Lpu_id,
				Evn.Lpu_Nick,
				Evn.LpuSection_id,
				Evn.LpuSection_Name,
				Evn.MedPersonal_id,
				Evn.Person_Fio
			from v_PersonRegister pr with (nolock) 
			left join (
				select 
					ps.Person_id,
					ps.EvnPS_id Evn_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio
				from v_EvnPS ps with (nolock)
				inner join v_EvnSection es with (nolock) on es.EvnSection_pid = ps.EvnPS_id and es.EvnSection_Index = 0
				left join v_Diag d with (nolock) on d.Diag_id = ps.Diag_id 
				outer apply (
					select top 1 DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
					from v_EvnDiagPS DiagPS with (nolock)
					inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
					where DiagPS.EvnDiagPS_rid = ps.EvnPS_rid
						and dPS.Diag_Code in ({$filterDiag_Code})
							and DiagPS.DiagSetClass_id = 3
						order by DiagPS.EvnDiagPS_setDate
						) DiagPS
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
					where ps.Person_id = :Person_id
						and (d.Diag_Code in ({$filterDiag_Code})
							or DiagPS.Diag_id is not null)
				UNION ALL
				select 
					pl.Person_id,
					pl.EvnPL_id Evn_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnVizitPL_setDate else DiagPS.EvnDiagPLSop_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio
				from v_EvnPL pl with (nolock)
				inner join v_EvnVizitPL es with (nolock) on es.EvnVizitPL_pid = pl.EvnPL_id and es.EvnVizitPL_Index = 0
				left join v_Diag d with (nolock) on d.Diag_id = pl.Diag_id 
				outer apply (
					select top 1 DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_FullName
					from v_EvnDiagPLSop DiagPS with (nolock)
					inner join v_Diag dPS with (nolock) on dPS.Diag_id = DiagPS.Diag_id
					where DiagPS.EvnDiagPLSop_rid = pl.EvnPL_rid
						and dPS.Diag_Code in ({$filterDiag_Code})
						order by DiagPS.EvnDiagPLSop_setDate
						) DiagPS
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = es.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = es.LpuSection_id
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where pl.Person_id = :Person_id
					and (d.Diag_Code in ({$filterDiag_Code})
						or DiagPS.Diag_id is not null)
			) Evn on evn.Person_id = pr.Person_id
			where pr.Person_id = :Person_id
			and pr.MorbusType_id = :MorbusType_id
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
    /**
     * Получение Операции, услуги (ЧКВ, КАГ, АКШ) за предыдущие три года
     */
    function getListOperUslug($data)
    {
        $params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
		with UslugaPid (
				EvnUsluga_id
			) as (
				select EvnUsluga_id from v_EvnUsluga EU with (nolock)
				where  (1=1)
				and EU.Person_id = :Person_id
				and (EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper'))
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			)
			select
			EU.EvnUsluga_id
			,EU.EvnUsluga_pid
			,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
			,UC.UslugaComplex_Code as Usluga_Code
			,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
			,l.Lpu_Nick
			from
			v_EvnUsluga EU with (nolock)
			left join v_Evn EvnParent with(nolock) on EvnParent.Evn_id = EU.EvnUsluga_pid
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
			left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EU.EvnUsluga_pid
			left join v_Lpu l with (nolock) on EU.Lpu_id = l.Lpu_id
			left join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = EPS.LpuSection_id
			left join dbo.v_LpuUnit lu (nolock) on ls.LpuUnit_id=lu.LpuUnit_id
			left join dbo.v_LpuSection lss (nolock) on lss.LpuSection_id = ES.LpuSection_id
			left join dbo.v_LpuUnit luu (nolock) on lss.LpuUnit_id=luu.LpuUnit_id
			outer apply (
                SELECT top 1 
                    r.BSKRegistry_setDate
                from dbo.BSKRegistry r with (nolock)
                where (1=1)
                    and r.MorbusType_id = 19
                    and r.Person_id = EU.Person_id
                order by r.BSKRegistry_setDate desc
            ) as dt
			where
		
			exists(
				Select top 1 1
				from UslugaPid with(nolock) 
				where UslugaPid.EvnUsluga_id = EU.EvnUsluga_id)
		
			and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			and (ES.EvnSection_id is not null or EPS.EvnPS_id is not null)
			and (ES.LeaveType_id in (22,23,25) or EPS.LeaveType_id in (22,23,25))
			and EU.EvnUsluga_setDate >= DATEADD(YEAR, -3, dt.BSKRegistry_setDate)
			and (lu.LpuUnitType_id = 1 or luu.LpuUnitType_id = 1)
			--ЧКВ, КАГ, АКШ
			and UC.UslugaComplex_Code in(
				--ЧКВ
				'A16.12.004.009',
				'A16.12.004.010',
				'A16.12.004.012',
				'A16.12.004.013',
				'A16.12.026',
				'A16.12.026.011',
				'A16.12.026.012',
				'A16.12.028.003',
				'A16.12.028.017',
				--КАГ
				'A06.10.006',
				'A06.10.006.002',
				--АКШ
				'A16.12.004',
				'A16.12.004.001',
				'A16.12.004.002',
				'A16.12.004.003',
				'A16.12.004.004',
				'A16.12.004.005',
				'A16.12.004.006',
				'A16.12.004.007',
				'A16.12.004.011',
				'A16.10.031.008 ')
			order by EU.EvnUsluga_setDate desc
			";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
    }
    /**
	 * Случаи госпитализации с ОКС за предыдущие три года
     */
    function getListHospOKS($data)
    {
        $params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select 
				EPS.EvnPS_id,
				convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
				convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate,
				D.Diag_Code + ' ' + D.Diag_Name as Diag_Code,
				l.Lpu_Nick
			from dbo.v_EvnPS EPS with (nolock)
				left join dbo.Diag D with (nolock) on D.Diag_id = EPS.Diag_id
				left join v_Lpu l with (nolock) on EPS.Lpu_id = l.Lpu_id
				left join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = EPS.LpuSection_id
				left join dbo.v_LpuUnit lu (nolock) on ls.LpuUnit_id=lu.LpuUnit_id
				outer apply (
					SELECT top 1 
						r.BSKRegistry_setDate
					from dbo.BSKRegistry r with (nolock)
					where (1=1)
						and r.MorbusType_id = 19
						and r.Person_id = EPS.Person_id
					order by r.BSKRegistry_setDate desc
				) as dt
			where (1=1)
				and D.Diag_Code in ('I21.0','I21.1','I21.2','I21.3','I21.9','I22.0','I22.1','I22.8','I22.9','I20.0','I21.4')
				and EPS.EvnPS_disDate is not null
				and EPS.LeaveType_id in (22,23,25)
				and EPS.EvnPS_setDate >= DATEADD(YEAR, -3, dt.BSKRegistry_setDate)
				and lu.LpuUnitType_id = 1 -- круглосуточный стационар
				and EPS.Person_id = :Person_id
				order by EPS.EvnPS_setDate desc
				";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Диспансерное наблюдение
     */
    function getListDispViewData($data)
    {
        $params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
		select
			PD.PersonDisp_id,
			convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
			PD.Diag_id,
			l.Lpu_Nick,
			D.Diag_Code + ' ' + D.Diag_Name as Diag_Code
		
		from
			v_PersonDisp PD with (nolock)
			left join v_DispOutType DOT with (nolock) on DOT.DispOutType_id = PD.DispOutType_id
			left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
			left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
			left join v_Lpu l with (nolock) on PD.Lpu_id = l.Lpu_id
		where (1=1)
			--and coalesce(D.Diag_Code, '') not in('C30.1') 
			--and coalesce(PD.Lpu_id, 0) not in(150007,392) 
			and PD.Person_id = :Person_id
		order by
			PD.PersonDisp_begDate desc
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Для предмета наблюдения «Скрининг» исключить возможность добавления пациентов при следующих условиях:
	 * у пациентов в случаях лечения установлены диагнозы МКБ10 болезней системы кровообращения I00-I99;
	 * пациент уже состоит в одном из предмете наблюдений Регистра БСК.
     */
    function checkBSKforScreening($data)
    {
        $params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
		with ED as (
			--посещения
			select 	vpl.Person_id
			from v_EvnVizitPL vpl with (nolock)
			left join v_Diag d with (nolock) on d.Diag_id = vpl.Diag_id 
			where d.Diag_Code like 'I%'
			and vpl.Person_id = :Person_id
	
			union all
			--движение в КВС
			select 	vpl.Person_id
			from v_EvnSection vpl with (nolock)
			left join v_Diag d with (nolock) on d.Diag_id = vpl.Diag_id 
			where d.Diag_Code like 'I%'
			and vpl.Person_id = :Person_id
	
			union all
			--сопутствующие диагнозы в посещении
			select 	vpl.Person_id
			from v_EvnDiagPLSop vpl with (nolock)
			left join v_Diag d with (nolock) on d.Diag_id = vpl.Diag_id 
			where d.Diag_Code like 'I%'
			and vpl.Person_id = :Person_id
	
			union all
			--сопутствующие диагнозы в движении
			select 	vpl.Person_id
			from v_EvnDiagPS vpl with (nolock)
			left join v_Diag d with (nolock) on d.Diag_id = vpl.Diag_id 
			where d.Diag_Code like 'I%'
			and vpl.Person_id = :Person_id
	
			union all
			--есть уже в бск кроме скринига
			select r.Person_id
			from dbo.PersonRegister r with (nolock)
			where r.MorbusType_id != 84 -- скрининг
			and r.Person_id = :Person_id
		)
		select distinct
			PS.Person_id
		from ED PS with(nolock)
		where (1=1)
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *    Сохранение диагнозов
	 */
	function savePrognosDiseases($data)
	{
		$PrognosOslDiagList = array(); 
		$PrognosOslDiagArr = array(); 
		foreach($data['PrognosOslDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $PrognosOslDiagArr)) {
				throw new Exception('Ввод одинаковых осложнений заболевания не допускается', 500);
			}
			$PrognosOslDiagArr[] = $diag_id[0];
			$OslDiagList[] = $diag_id;
		}
		
		$this->deletePrognosDiseases($data);
	
		
		foreach($data['PrognosOslDiagList'] as $diag_id) {
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);
				exec p_BSKDiagPrognos_ins
					@BSKDiagPrognos_id = null,
					@Person_id = :Person_id,
					@Diag_id = :Diag_id,
					@BSKDiagPrognos_DescriptDiag = :DescriptDiag,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;
				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'Person_id' => $data['Person_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' =>$diag_id[0],
				'DescriptDiag' => $diag_id[1]
			));
		}
	}
	
	/**
	 *    Удаление диагнозов
	 */
	function deletePrognosDiseases($data)
	{
		$resp = $this->queryResult("
			select BSKDiagPrognos_id from BSKDiagPrognos with(nolock) where Person_id = :Person_id
		", $data);
		
		foreach($resp as $item) {
			$this->queryResult("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);
				exec p_BSKDiagPrognos_del
					@BSKDiagPrognos_id = :BSKDiagPrognos_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;
				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array(
				'BSKDiagPrognos_id' => $item['BSKDiagPrognos_id']
			));
		}
	}
	/**
	 *    Получение прогнозируемых осложнений основного заболевания
	 */
	function loadPrognosDiseases($data)
	{
		$query = "
			select
				BSKDiagPrognos_id, Person_id, Diag_id, BSKDiagPrognos_DescriptDiag as DescriptDiag
			from
				v_BSKDiagPrognos with(nolock)
			where
				Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
?>