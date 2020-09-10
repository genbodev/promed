<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Ufa_BSK_Register_User_model - молеь для работы с данными БСК (Башкирия)
 * пользовательская  часть
 *
 * @package			BSK
 * @author			Valery Bondarev
 * @version			05.01.2020
 */

class Ufa_BSK_Register_User_model extends swPgModel
{

    var $scheme = "r2";
    var $listMorbusType = '(84,88,89,50,19)';

    /**
     * comments
     */
    function __construct()
    {
        parent::__construct();
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
                        MT.MorbusType_Name as \"MorbusType_Name\",
                        R.MorbusType_id as \"MorbusType_id\",
                        O.BSKObject_id as \"BSKObject_id\",
                        case when R.MorbusType_id = 84 then 'Группа риска: ' || cast(R.BSKRegistry_riskGroup as varchar(2))
                             when R.MorbusType_id = 88 then 'Функциональный класс: ' || cast(R.BSKRegistry_riskGroup as varchar(2))
                                 when R.MorbusType_id = 89 then 'Степень риска: ' || cast(R.BSKRegistry_riskGroup as varchar(2))
                        else ''
                        end  as \"BSKRegistry_riskGroup\",
                        substring(to_char(R.BSKRegistry_insDT,'yyyy-mm-dd hh24:mi'),1,10) as \"BSKRegistry_insDT\",
                        R.pmUser_insID as \"pmUser_insID\" 
                    from r2.BSKRegistry R 
                    left join dbo.MorbusType MT on MT.MorbusType_id = R.MorbusType_id
                    left join r2.BSKObject O on R.MorbusType_id = O.MorbusType_id
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
                        PR.Person_id as \"Person_id\",
                        MT.MorbusType_id as \"MorbusType_id\",
                        MT.MorbusType_Name as \"MorbusType_Name\",
                        BSKRegistry.BSKRegistry_setDate as \"BSKRegistry_setDate\",
                        case 
                                when MT.MorbusType_id = 84 then cast(BSKRegistry.BSKRegistry_riskGroup as varchar(10))
                                when MT.MorbusType_id = 89 then 
                                        (select RD.BSKRegistryData_data from r2.BSKRegistryData RD 
                                         where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269)
                                when MT.MorbusType_id = 88 then 
                                        (select RD.BSKRegistryData_data from r2.BSKRegistryData RD 
                                         where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151)
                                else NULL end as \"BSKRegistry_riskGroup\",
                        --BSKRegistry.BSKRegistry_riskGroup,
						BSKRegistry.BSKRegistry_id
                from PersonRegister PR 
                left join dbo.MorbusType MT  on MT.MorbusType_id = PR.MorbusType_id
                left join lateral (
                 select 
                        to_char(R.BSKRegistry_setDate, 'yyyy-mm-dd') as BSKRegistry_setDate,
                        R.BSKRegistry_riskGroup ,
						R.BSKRegistry_id
                 from r2.BSKRegistry R
                 where R.MorbusType_id = MT.MorbusType_id and R.Person_id = :Person_id

                 order by R.BSKRegistry_setDate DESC
                 limit 1
                ) as BSKRegistry on true
                where PR.MorbusType_id in (84,88,89,19,50)
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

        $query = "
			select
				BSKObservElement_id as \"BSKObservElement_id\",
				BSKRegistryData_data as \"BSKRegistryData_data\"
			from r2.getLastAnketData (:Person_id)
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
     *  Список возможных МО для маршрутизации при ОКС
     */
    function getMOforOKS(){
        $params = array();

        $query = "
            WITH Lpu_CTE (Lpu_id)
            as 
            (
            	select AMO.withSTLpu_id as Lpu_id from r2.AreaofResponsibilityMO AMO where COALESCE(AMO.withSTLpu_id, 0) > 0
            	union all
            	select AMO.nwithoutSTLpu_id as Lpu_id from r2.AreaofResponsibilityMO AMO where COALESCE(AMO.nwithoutSTLpu_id, 0) > 0
            	union all
            	select AMO.CHKVLpu_id as Lpu_id from r2.AreaofResponsibilityMO AMO  where COALESCE(AMO.CHKVLpu_id, 0) > 0
            )
            select 
            	Lpu.Lpu_id as \"Lpu_id\", 
            	Lpu.Org_Nick as \"Org_Nick\" 
            from Lpu_CTE
            left join dbo.v_Lpu Lpu on Lpu.Lpu_id = Lpu_CTE.Lpu_id
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
     *  Данные из регистра БСК на конкретную карту вызова СМП
     */
    function getOKSdata($data){
        $params = array(
            'CmpCallCard_id'=>$data['CmpCallCard_id']
        );

        $query = "  
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
              when BSKObservElement_id = 302 then 'ZonaCHKV'
              when BSKObservElement_id = 304 then 'MOHospital'
              when BSKObservElement_id = 305 then 'MedStaffFact_num'              
            end as \"formName\", 
            case when (BSKRegistryData_data ilike 'Да%') and (BSKObservElement_id between 277 and 299) then 1 else 0 end as \"checked\",
            case when right(BSKRegistryData_data, 1) != '.'  and BSKObservElement_id between 277 and 299 then 1 else 0 end as \"isDoctor\"
            ,*             
            
            from r2.BSKRegistryData
            where BSKRegistry_id = 
            (
                                    select  BSKRegistry_id 
                                    from r2.BSKRegistryData 
                                    where BSKObservElement_id = 303 and BSKRegistryData_data = :CmpCallCard_id
                                    limit 1
            )
        ";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
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
            select
                R.BSKRegistry_id as \"BSKRegistry_id\",
                R.Person_id as \"Person_id\",
                R.MorbusType_id as \"MorbusType_id\",
                to_char(R.BSKRegistry_setDate,'yyyy-mm-dd') as \"BSKRegistry_setDate\"
            from r2.BSKRegistry R
                inner join r2.BSKRegistryData BRD on BRD.BSKRegistry_id = r.BSKRegistry_id and BRD.BSKObservElement_id = 398 and BRD.BSKRegistryData_data is null
            where (1=1) and
                R.Person_id = :Person_id
                and R.MorbusType_id = 19
                and to_char(R.BSKRegistry_setDate,'yyyy-mm-dd') = :BSKRegistry_setDate
            limit 1  
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
            BSKObservRecomendation_id as \"BSKObservRecomendation_id\",
            BSKObservRecomendation_text as \"BSKObservRecomendation_text\",
            case 
               when BSKObservRecomendation_id = 310 then 'Рекомендуемый алгоритм действий на догоспитальном этапе при выявлении острого коронарного синдрома без подъема сегмента ST (OKCбпST) для бригад СМП'
               when BSKObservRecomendation_id = 311 then 'Рекомендуемый алгоритм действий врача СМП при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
               when BSKObservRecomendation_id = 312 then 'Рекомендуемый алгоритм действий фельдшера СМП при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
               when BSKObservRecomendation_id = 313 then 'Рекомендуемый алгоритм действий врача поликлиники при выявлении острого коронарного синдрома с подъемом сегмента ST (OKCпST)'
            end as \"forWhom\"
            from 
                r2.BSKObservRecomendation
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
            select Dolgnost_Name as \"Dolgnost_Name\"  from v_MedPersonal where MedPersonal_id = :MedPersonal_id
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
            select 
                Person_id as \"Person_id\" 
            from 
                dbo.CmpCallCard
            where 
                CmpCallCard_id = :CmpCallCard_id
            limit 1
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
        if($this->checkPersonInRegister($params) !== false){
            //Записать пациента в dbo.PersonRegister с MorbusType = 19
            $params = array(
                'Person_id' => $data['Person_id'],
                'MorbusType_id' => $data['MorbusType_id'],
                'Diag_id' => $data['Diag_id'],
                'PersonRegister_Code' =>null,
                'PersonRegister_setDate' => date('Y-m-d H:i:s'),
                'PersonRegister_disDate' => null,
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
                302=>array(
                    'BSKObservElement_id'=>302,
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
                LR.Lpu_id as \"Lpu_id\",
				--LpuRegionStreet.Server_id,
				LpuRegionStreet_id as \"LpuRegionStreet_id\",
				LpuRegionStreet.LpuRegion_id as \"LpuRegion_id\",
				LpuRegionStreet.KLCountry_id as \"KLCountry_id\",
				KLRGN_id as \"KLRGN_id\",
				--KLSubRGN_id,
				KLCity_id as \"KLCity_id\",
				--LpuRegionStreet.KLTown_id,
				case COALESCE(LpuRegionStreet.KLTown_id,'')
				when '' then RTrim(c.KLArea_Name)||' '||cs.KLSocr_Nick
				else RTrim(t.KLArea_Name)||' '||ts.KLSocr_Nick
				end as \"KLTown_Name\",
				LpuRegionStreet.KLStreet_id as \"KLStreet_id\",
				RTrim(KLStreet_FullName) as \"KLStreet_Name\",
				LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
			from LpuRegionStreet
			left join KLArea t on t.KLArea_id = LpuRegionStreet.KLTown_id
			left join KLSocr ts on ts.KLSocr_id = t.KLSocr_id
			left join v_KLStreet KLStreet on KLStreet.KLStreet_id = LpuRegionStreet.KLStreet_id
			left join KLArea c on c.Klarea_id = LpuRegionStreet.KLCity_id
			left join KLSocr cs on cs.KLSocr_id = c.KLSocr_id
			left join v_LpuRegion LR on LpuRegionStreet.LpuRegion_id = LR.LpuRegion_id
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
    			END as \"LpuMO_Nick\",
    			LpuChkv.Lpu_Nick as \"LpuChkv_Nick\"
    			FROM r2.AreaofResponsibilityMO as \"armo\"
    				LEFT JOIN v_KLArea as area on (area.KLArea_id = armo.KLArea_id)
    				LEFT JOIN v_KLStreet as street on (street.KLArea_id = area.KLArea_id)
    				LEFT JOIN v_PersonSprTerrDop as pstp on (pstp.KLAdr_Ocatd = street.KLAdr_Ocatd)
    				LEFT JOIN v_Lpu as LpuWithSt on(LpuWithSt.Lpu_id = armo.withSTLpu_id)
    				LEFT JOIN v_Lpu as LpuWithoutSt on(LpuWithoutSt.Lpu_id = armo.nwithoutSTLpu_id)
    				LEFT JOIN v_Lpu as LpuChkv on(LpuChkv.Lpu_id = armo.CHKVLpu_id)
    				" . ImplodeWherePH($ufaAddWhere) . "
    				AND ( armo.AreaofResponsibilityMO_WeekDay IS NULL OR armo.AreaofResponsibilityMO_WeekDay = (date_part ( 'dow' , dbo.tzGetDate() )-1) )
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
             *  Задача - определить зоны ответственности МО по справочнику маршрутизации [r2].[AreaofResponsibilityMO]
             *  если указан KLSubRgn_id -  то поиск относительно района
             *  если KLArea_id указан, но не Уфа - значит поиск по районным центрам
             *  если KLArea_id Уфы, то для начала необходимо определить зону ответственности по улице и номеру дома, потом по справочнику
             */
            /*
           if($data['KLSubRgn_id'] != 0){
               $withSTLpu_id = in_array($data['KLSubRgn_id'], array(99,109,110,11,112,149,120,125,130,135,137,97,144,145)) ? $this->scheduleMO() : 'ARMO.withSTLpu_id';

               $query = "
                    select
                       (select Lpu_Name from r2.getLpu_Name({$withSTLpu_id})) as withSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name
                   from r2.AreaofResponsibilityMO ARMO with(nolock)
                   where ARMO.KLSubRgn_id = :KLSubRgn_id
               ";
           }
           elseif($data['KLSubRgn_id'] == 0 && !in_array($data['KLArea_id'], array(0, 268687//244440)) ){
               $query = "
                    select
                       (select Lpu_Name from r2.getLpu_Name(ARMO.withSTLpu_id)) as withSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name
                   from r2.AreaofResponsibilityMO ARMO with(nolock)
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
                       (select Lpu_Name from r2.getLpu_Name({$withSTLpu_id})) as withSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.nwithoutSTLpu_id)) as withoutSTLpu_Name,
                       (select Lpu_Name from r2.getLpu_Name(ARMO.CHKVLpu_id)) as CHKVLpu_Name
                   from r2.AreaofResponsibilityMO ARMO with(nolock)
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
                BSKObservRecomendation_id as \"BSKObservRecomendation_id\",
                BSKObservRecomendationType_id as \"BSKObservRecomendationType_id\",
                BSKObservRecomendation_text as \"BSKObservRecomendation_text\"
                --,CONVERT(char(10), BSKObservRecomendation_insDT,120) as BSKObservRecomendation_insDT
             from r2.BSKObservRecomendation  where BSKObservRecomendationType_id = :BSKObservRecomendationType_id
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
                $checked = count($this->getDiagUslugaResult($filter));
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
                'Evn_setDate'=>"and D.Evn_setDate < dateadd('HOUR', -24, dbo.tzGetDate())"
            );  //24 часа от даты вызова СМП
                break;

            case 296: $diagsUsluga = array(
                'UslugaComplex_code'=>'(А11.23.001)'
            );
                break;

            case 297: $diagsUsluga = array(
                'Diag_code'=>'(G45)',
                'Evn_setDate'=>"and D.Evn_setDate < dateadd('month', -6, dbo.tzGetDate())"
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
            ? 'inner join dbo.UslugaComplex UC on EPL.UslugaComplex_id = UC.UslugaComplex_id'
            : 'inner join dbo.Diag D on D.Diag_id = EPL.Diag_id';

        $fieldParentField = (isset($filter['UslugaComplex_code']))
            ? 'D.UslugaComplex_code as "UslugaComplex_code"'
            : 'D.Diag_Code as "Diag_Code"';

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
			(SELECT
                {$fieldParentField}
            FROM
			(
            Select  
            		EPL.Person_id,
            		EPL.EvnSection_insDT as Evn_insDT,
                    EPL.EvnSection_setDate as Evn_setDate,
                    {$fieldSupQuery}
            from v_EvnSection EPL
            {$UslugaOrDiag}		
            limit 1)			               
            
            
            union all
            
            Select
            		E.Person_id,
            		E.Evn_insDT,
                    EPL.EvnVizitPL_setDT as Evn_setDate,
                    {$fieldSupQuery}
            from v_EvnVizitPL EPL 
            left join Evn E  on EPL.EvnVizitPL_id = E.Evn_id  and coalesce(E.Evn_deleted,1) = 1
            left join dbo.v_EvnUsluga EU on EPL.EvnVizitPL_id = EU.EvnUsluga_pid
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
                	ReferenceECGResult_code as \"ReferenceECGResult_code\",
                	ReferenceECGResult_Name as \"ReferenceECGResult_Name\",
                    subgroupOKC as \"subgroupOKC\" 
                from 
                    r2.ReferenceECGResult
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
            select
                 Error_Code as \"Error_Code\",
				 Error_Message as \"Error_Msg\"
		    from r2.p_BSKEvents_ins (
                BSKEvents_Type := :BSKEvents_Type,
                Person_id := :Person_id,
                BSKEvents_setDT := :BSKEvents_setDT,
                BSKEvents_Code := :BSKEvents_Code,
                BSKEvents_Name := :BSKEvents_Name,
                BSKEvents_Description := :BSKEvents_Description,
                pmUser_id := :pmUser_id
            );
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
            select
                 Error_Code as \"Error_Code\",
				 Error_Message as \"Error_Msg\"
            from r2.p_BSKEvents_upd (
                BSKEvents_Type := :BSKEvents_Type,
                BSKEvents_id := :BSKEvents_id,
                BSKEvents_setDT := :BSKEvents_setDT,
                BSKEvents_Code := :BSKEvents_Code,
                BSKEvents_Name := :BSKEvents_Name,
                BSKEvents_Description := :BSKEvents_Description,
                pmUser_id := :pmUser_id
            );
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
            select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r2.p_BSKEvents_del (
				BSKEvents_id := :BSKEvents_id
			);
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
                $filtersUslugaComplex_Code = " (ltrim(uc.UslugaComplex_Code) = 'A{$dot}06.10.006' or ltrim(uc.UslugaComplex_Code) like 'A{$dot}16%')";
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A{$dot}06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A{$dot}16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
                break;
            case 19 :
                $filterDiag_id = " ltrim(d.Diag_Code) like 'i%'";
                $filtersUslugaComplex_Code = "UslugaComplex_Code in('A05.10.006',
                        'A06.10.006','A16.12.004.009','A16.12.026.003','A16.12.026.004','A16.12.026.005','A16.12.026.006','A16.12.026.007''A16.12.028', 
                        'A11.12.008','A11.12.003.002')";
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A{$dot}06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A{$dot}16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
                break;
            default :
                $filterDiag_id = " ltrim(d.Diag_Code) like 'i%'";
                $filtersUslugaComplex_Code = " (ltrim(uc.UslugaComplex_Code) = 'A{$dot}06.10.006' or ltrim(uc.UslugaComplex_Code) like 'A{$dot}16%')";
                $filterEventsTable = "ltrim(BSKE.BSKEvents_Code) = 'A{$dot}06.10.006' or ltrim(BSKE.BSKEvents_Code) like 'A{$dot}16%' or ltrim(BSKE.BSKEvents_Code) like 'i%'";
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
                                min(RTrim(COALESCE(to_char(cast(Events_setDate as datetime),'dd.mm.yyyy'),''))),
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
                				from v_EvnSection E
                				inner join dbo.Diag D on D.Diag_id = E.Diag_id		               
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
                				from EvnPL EPL 
                				inner join Evn E on EPL.EvnPL_id = E.Evn_id  and coalesce(E.Evn_deleted,1) = 1
                				inner join dbo.Diag D on D.Diag_id = EPL.Diag_id		          
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
                            RTrim(COALESCE(to_char(cast(Events_setDate as datetime),'dd.mm.yyyy'),'')) as Events_setDate,
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
            				from v_EvnUsluga us
            				inner join v_UslugaComplex uc on uc.UslugaComplex_id = us.UslugaComplex_id
            				left join EvnUslugaCommon EUC on EUC.EvnUslugaCommon_id = us.EvnUsluga_id
            				left join AttributeSignValue ASV on ASV.AttributeSignValue_TablePKey = EUC.EvnUslugaCommon_id
            				left join AttributeValue AV on AV.AttributeSignValue_id = ASV.AttributeSignValue_id   and AV.Attribute_id = 47
            				left join Attribute A on A.Attribute_id = AV.AttributeValue_id
            				left join ECGResult ER on ER.ECGResult_id =AV.AttributeValue_ValueIdent
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
            				from r2.BSKEvents BSKE	          
            				where  (
            						$filterEventsTable
            						)
            				  and BSKE.Person_id = :Person_id    			
            			
            			) as D
            )	
            
            select * from EventsCTE 	
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
            from r2.BSKEvents BSKE WITH (NOLOCK)
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
                R.BSKRegistry_id as \"BSKRegistry_id\", 
                to_char(BSKRegistry_setDate,'dd.mm.yyyy') as \"BSKRegistry_setDate\",
                E.BSKObservElement_id as \"BSKObservElement_id\",
                E.BSKObservElement_name as \"BSKObservElement_name\",
                RD.BSKRegistryData_data as \"BSKRegistryData_data\",
                RD.BSKUnits_name as \"BSKUnits_name\",
                to_char(RD.BSKRegistryData_insDT,'dd.mm.yyyy') as \"BSKRegistryData_insDT\"
             from r2.BSKRegistryData RD 
            left join r2.BSKRegistry R on RD.BSKRegistry_id = R.BSKRegistry_id
            left join r2.BSKObservElement E on RD.BSKObservElement_id = E.BSKObservElement_id
            left join r2.BSKObservElementGroup EG on E.BSKObservElementGroup_id = EG.BSKObservElementGroup_id
            where EG.BSKObservElementGroup_id in (10,23,28,42)
            and R.Person_id = :Person_id
            order by  right(to_char(BSKRegistry_setDate,'dd.mm.yyyy'), 4) DESC       
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

        $query = "select * from r2.getPersonInfo(:Person_id)";
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
            default:
                $BSKObservUnits_ids = array();
                break;
        }

        if (empty($BSKObservUnits_ids)) {
            $query = "select BSKObservUnits_id as \"BSKObservUnits_id\", BSKObservUnits_name as \"BSKObservUnits_name\" from r2.BSKObservUnits";
        } else {
            $query = "select BSKObservUnits_id as \"BSKObservUnits_id\", BSKObservUnits_name as \"BSKObservUnits_name\" from r2.BSKObservUnits where BSKObservUnits_id in (" . implode(',', $BSKObservUnits_ids) . ")";
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
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r2.p_getRecomendationByDate (
                MorbusType_id := :MorbusType_id,
                Person_id := :Person_id,
                Sex_id := :Sex_id,
                BSKRegistry_setDate := :BSKRegistry_setDate,
                BSKObservRecomendationType_id := :BSKObservRecomendationType_id
            );
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
                to_char(BSKRegistry_setDate,'yyyy-mm-dd') as \"text\",
                'true' as \"leaf\"             
            from r2.BSKRegistry
            where MorbusType_id = :MorbusType_id and  Person_id = :Person_id  
            group by  to_char(BSKRegistry_setDate,'yyyy-mm-dd') 
            order by to_char(BSKRegistry_setDate,'yyyy-mm-dd') DESC             
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
    function saveRegistry($data, $registryData)
    {

        $params = array(
            'pmUser_id' => $data['pmUser_id'],
            'riskGroup' => $registryData['riskGroup'],
            'MorbusType_id' => $registryData['MorbusType_id'],
            'Person_id' => $registryData['Person_id'],
            'setDate' => $registryData['setDate'],
            'isBrowsed' => $registryData['MorbusType_id']==19 ? 1 : 2
        );

        $query = "
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                BSKRegistry_id as \"BSKRegistry_id\"
            from r2.p_BSKRegistry_insNEW
			    BSKRegistry_id := :BSKRegistry_id,
			    BSKRegistry_setDate := :setDate,
			    BSKRegistry_riskGroup := :riskGroup,
			    BSKRegistry_isBrowsed := :isBrowsed,
				Person_id := :Person_id,
				MorbusType_id := :MorbusType_id,
				pmUser_id := :pmUser_id
			);
		";

        $result = $this->db->query( $query, $params );
        $res = $result->result( 'array' );
        if ( is_object( $result ) ) {
            foreach ($registryData['ListAnswers'] as $k => $v) {

                $value = htmlspecialchars(isset($v['value']) ? $v['value'] : $v['BSKRegistryData_data']);
                $unit  = htmlspecialchars(isset($v['unit']) ? $v['unit'] : (isset($v['BSKUnits_name']) ? $v['BSKUnits_name'] : ''));

                if (trim($value) != '' || $registryData['MorbusType_id'] == 19) {

                    $params = array(
                        'BSKRegistry_id' => $res[0]['BSKRegistry_id'],
                        'pmUser_id' => $data['pmUser_id'],
                        'BSKObservElement_id' => $k,
                        'BSKRegistryData_data' => $value,
                        'BSKUnits_name' => $unit
                    );

                    $query = "
                        select
                            Error_Code as \"Error_Code\",
				            Error_Message as \"Error_Msg\",
                            BSKRegistryData_id as \"BSKRegistryData_id\"
                        from r2.p_BSKRegistryData_insNEW (
                            BSKRegistryData_id  := :BSKRegistryData_id,
                            BSKRegistry_id  := :BSKRegistry_id,
                            pmUser_id := :pmUser_id,
                            BSKObservElement_id := :BSKObservElement_id,
                            BSKRegistryData_data := :BSKRegistryData_data,
                            BSKUnits_name := :BSKUnits_name,
                        );
                    ";

                    //echo getDebugSQL($query, $params);
                    $r = $this->db->query($query, $params);
                }
            }

        }



        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }

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
            from r2.BSKRegistry where MorbusType_id = :MorbusType_id
            and Person_id = :Person_id and BSKRegistry_setDate BETWEEN to_date(:day_start,'yyyy-mm-dd') AND to_date(:day_end,'yyyy-mm-dd')
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
            select * from r2.BSKRegistry
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
                    V.BSKObservElementValues_id as \"BSKObservElementValues_id\",
                    V.BSKObservElementValues_sign as \"BSKObservElementValues_sign\",
                    E.BSKObservElement_id as \"BSKObservElement_id\",
                    E.BSKObservElement_name as \"BSKObservElement_name\",
                    E.BSKObservElementFormat_id as \"BSKObservElementFormat_id\",
                    V.BSKObservElementValues_data as \"BSKObservElementValues_data\" 
                from r2.BSKObservElementValues V
                left join r2.BSKObservElement E on E.BSKObservElement_id = V.BSKObservElement_id 
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
           select
    		    PT.PrivilegeType_Name as \"diagstring\"
    	   from dbo.PersonPrivilege PP
           left join dbo.PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
           where PT.PrivilegeType_id in $this->listMorbusType and PP.PersonPrivilege_endDate IS NULL
           and PP.Person_id  = :Person_id   
           limit 1    
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
			(SELECT
                D.Diag_Code || ' '|| D.Diag_Name as diagstring
            FROM
			(
            Select  
            		E.Person_id as \"Person_id\", 
            		D.Diag_Code as \"Diag_Code\",
            		D.Diag_Name as \"Diag_Name\",
            		E.EvnSection_insDT as \"Evn_insDT\",
                    E.EvnSection_setDate as \"Evn_setDate\"
            from v_EvnSection E	
            inner join dbo.Diag D on D.Diag_id = E.Diag_id		
            limit 1)               
            union all
            
            Select
            		E.Person_id as \"Person_id\", 
            		D.Diag_Code as \"Diag_Code\",
            		D.Diag_Name as \"Diag_Name\",
            		E.Evn_insDT as \"Evn_insDT\",
                    E.Evn_setDT as \"Evn_setDate\"
            from EvnPL EPL 
            inner join Evn E on EPL.EvnPL_id = E.Evn_id  and COALESCE(E.Evn_deleted,1) = 1
            inner join dbo.Diag D  on D.Diag_id = EPL.Diag_id		          
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
            select PercentileWaist_Percentile as \"prc\" from r2.PercentileWaist
            where 
            PercentileWaist_Age = :age 
            and 
            Sex_id = :Sex_id 
            and
            PercentileWaist_Circle >= :waist 
            limit 1       
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
               BSKRegistry_id as \"BSKRegistry_id\",
               left(to_char(BSKRegistry_setDate,'dd.mm.yyyy'),10) as \"BSKRegistry_setDate\",
               BSKRegistry_riskGroup as \"BSKRegistry_riskGroup\"
            from r2.BSKRegistry
            where Person_id = :Person_id
            and MorbusType_id = :MorbusType_id
            -- Все 3 группы попадают в сравнение
            --and BSKRegistry_riskGroup in(2,3)
            order by right(to_char(BSKRegistry_setDate,'dd.mm.yyyy'), 4) DESC
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
                V.BSKObservElementValues_id as \"BSKObservElementValues_id\",
                V.BSKObservElementValues_data as \"BSKObservElementValues_data\",
                V.BSKObservElement_id as \"BSKObservElement_id\",
                E.BSKObservElement_name as \"BSKObservElement_name\",
                G.BSKObservElementGroup_id as \"BSKObservElementGroup_id\",
                G.BSKObservElementGroup_name as \"BSKObservElementGroup_name\"
            from r2.BSKObservElementValues V
            inner join r2.BSKObservElement E on E.BSKObservElement_id = V.BSKObservElement_id 
                       and E.BSKObservElement_deleted = 1
                       and E.BSKObservElement_stage = 1
            inner join r2.BSKObservElementGroup G on G.BSKObservElementGroup_id = E.BSKObservElementGroup_id 
                       and 1=1
            inner join r2.BSKObservElementLink L on L.BSKObservElement_id = E.BSKObservElement_id
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
        $p = array('BSKRegistry_id' => $data['Registry_method']);
        $q = "SELECT BSKRegistryData_id as \"BSKRegistryData_id\", BSKObservElement_id as \"BSKObservElement_id\" FROM r2.BSKRegistryData WHERE BSKRegistry_id = :BSKRegistry_id";
        $res = $this->db->query($q, $p);

        if (is_object($res)) {
            $oks_data = $res->result('array');

            foreach($oks_data as $ke => $va) {
                foreach ($registryData['ListAnswers'] as $k => $v) {

                    if($va['BSKObservElement_id'] == $k) {
                        $value = htmlspecialchars(isset($v['value']) ? $v['value'] : $v['BSKRegistryData_data']);
                        $unit  = htmlspecialchars(isset($v['unit']) ? $v['unit'] : (isset($v['BSKUnits_name']) ? $v['BSKUnits_name'] : ''));

                        if (trim($value) != '' || $data['MorbusType_id'] == 19) {
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
                select
                   Error_Code as \"Error_Code\",
				   Error_Message as \"Error_Msg\",
                   riskGroupStatus as \"riskGroupStatus\"
                from r2.p_BSKRegistryData_upd (
                    xml = :xml,
                    pmUser_id = :pmUser_id,
                    riskGroup = :riskGroup,
                    BSKRegistry_id = :BSKRegistry_id,
                    riskGroupStatus = :riskGroupStatus
                    );
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
            $query = "
                 select *, 
                 (select Lpu_id from pmUserCache where pmUser_id = R.pmUser_insID limit 1) as \"Lpu_id\",
                 R.BSKRegistry_riskGroup as \"BSKRegistry_riskGroup\",
                 (select BSKObservElementFormat_id from r2.BSKObservElement E where E.BSKObservElement_id = RD.BSKObservElement_id) as \"BSKObservElementFormat_id\"
                 from r2.BSKRegistryData RD
                 left join r2.BSKRegistry R on R.BSKRegistry_id = RD.BSKRegistry_id
                 where R.Person_id = :Person_id and R.MorbusType_id = :MorbusType_id 
                 and R.BSKRegistry_id = (select BSKRegistry_id from r2.BSKRegistry R where R.Person_id = :Person_id and R.MorbusType_id = :MorbusType_id  order by R.BSKRegistry_setDate DESC limit 1)
             ";
        } else {
            $query = "
                 select *, 
                 (select Lpu_id from pmUserwhere pmUser_id = R.pmUser_insID) as \"Lpu_id\",
                 (select BSKObservElementFormat_id from r2.BSKObservElement E where E.BSKObservElement_id = RD.BSKObservElement_id) as \"BSKObservElementFormat_id\"
                 from r2.BSKRegistryData RD
                 left join r2.BSKRegistry Ron R.BSKRegistry_id = RD.BSKRegistry_id
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
                    V.BSKObservElementValues_id as \"BSKObservElementValues_id\",
                    V.BSKObservElementValues_data as \"BSKObservElementValues_data\",
                    V.BSKObservElement_id as \"BSKObservElement_id\",
                    E.BSKObservElement_name as \"BSKObservElement_name\",
                    
                    E.BSKObservElement_stage as \"BSKObservElement_stage\",
                    E.BSKObservElement_minAge as \"BSKObservElement_minAge\",
                    E.BSKObservElement_maxAge as \"BSKObservElement_maxAge\",
                    E.BSKObservElement_Sex_id as \"BSKObservElement_Sex_id\",
                    
                    E.BSKObservElementFormat_id as \"BSKObservElementFormat_id\",
                    G.BSKObservElementGroup_id as \"BSKObservElementGroup_id\",
                    G.BSKObservElementGroup_name as \"BSKObservElementGroup_name\"
                from r2.BSKObservElementValues V 
                inner join r2.BSKObservElement E on E.BSKObservElement_id = V.BSKObservElement_id 
                           and E.BSKObservElement_deleted = 1
                           --
                           -- Для анкеты самое то
                           -- and E.BSKObservElement_stage = 1
                           --
                inner join r2.BSKObservElementGroup G on G.BSKObservElementGroup_id = E.BSKObservElementGroup_id 
                           and 1=1
                inner join r2.BSKObservElementLink L  on L.BSKObservElement_id = E.BSKObservElement_id
                           and L.BSKObservElementLink_deleted = 1   
                           and L.BSKObject_id = :BSKObject_id 
                
                --Финт для определения сортировки
                left join r2.BSKObject BO on BO.BSKObject_id = L.BSKObject_id
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
                             when 193 then 30
                             when 194 then 29
                             when 195 then 28
                             when 196 then 27
                             when 197 then 26
                             when 198 then 25
                             when 199 then 24
                             when 200 then 23
                             when 201 then 22
                             when 202 then 21
                             when 203 then 20

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
                   M.MorbusType_id as \"MorbusType_id\",
                   M.MorbusType_name as \"MorbusType_name\"
                 from dbo.MorbusType M 
                 where M.MorbusType_id in(84,88,89,50, 19) ";
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

        $query = "
			select
				O.BSKObject_id as \"BSKObject_id\",
				MT.MorbusType_id as \"MorbusType_id\",
				MT.MorbusType_name as \"MorbusType_name\"
			from
				r2.BSKObject O
				left join dbo.MorbusType MT on MT.MorbusType_id = O.MorbusType_id
			where
				O.MorbusType_id in($this->listMorbusType)
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
                        O.BSKObject_id as \"BSKObject_id\", 
                        R.Person_id as \"Person_id\", 
                        MT.MorbusType_Name as \"MorbusType_Name\",
                        MT.MorbusType_id as \"MorbusType_id\",
                        to_char(ps.Person_deadDT , 'dd.mm.yyyy') as \"Person_deadDT\" 
                    from v_PersonState PS 
		    inner join dbo.PersonRegister R  on R.Person_id = PS.Person_id
                    left join dbo.MorbusType MT on MT.MorbusType_id = R.MorbusType_id
                    left join r2.BSKObject O on O.MorbusType_id = R.MorbusType_id
                    where R.Person_id = :Person_id and O.BSKObject_id is not null
                    order by O.BSKObject_id
                  ";
        //         $query = "  select
        //                        O.BSKObject_id,
        //                        R.Person_id,
        //                        MT.MorbusType_Name,
        //                        MT.MorbusType_id
        //                    from dbo.PersonRegister R with(nolock)
        //                    left join dbo.MorbusType MT with(nolock) on MT.MorbusType_id = R.MorbusType_id
        //                    left join r2.BSKObject O with(nolock) on O.MorbusType_id = R.MorbusType_id
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
                    Person_id as \"Person_id\", 
                    MorbusType_id as \"MorbusType_id\"
                  from dbo.PersonRegister
                  where Person_id = :Person_id
                  and MorbusType_id = :MorbusType_id 
                  
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
        $InnField = "coalesce(ps.Person_Inn,'') as Person_Inn";
        if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id'] > 0)) {
            $object              = "v_Person_bdz";
            $params['Server_id'] = $data['Server_id'];
            $filter .= " and PS.Server_id = :Server_id";
            $params['PersonEvn_id'] = $data['PersonEvn_id'];
            $filter .= " and PS.PersonEvn_id = :PersonEvn_id";
            $InnField = "COALESCE(ps.PersonInn_Inn,'') as \"Person_Inn\"";
        } else {
            $params['Person_id'] = $data['Person_id'];
            $filter .= " and PS.Person_id = :Person_id";
            $InnField = "COALESCE(ps.Person_Inn,'') as \"Person_Inn\"";
        }
        $extendFrom   = "";
        $extendSelect = "";
        if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
            $params['EvnDirection_id'] = $data['EvnDirection_id'];
            $extendSelect              = "
				,ED.EvnDirection_id as \"EvnDirection_id\"
				,ED.EvnDirection_Num as \"EvnDirection_Num\"
				,ED.EvnDirection_setDT as \"EvnDirection_setDT\"
			";
            $extendFrom .= "
				LEFT JOIN LATERAL
				(SELECT 
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					COALESCE(to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy'), '') as EvnDirection_setDT
					
				FROM
					v_EvnDirection_all ED
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
				LIMIT 1
				) as ED on true
				";
        }


        $query  = "
			SELECT
				ps.Person_id as \"Person_id\",
                (select BSKRegistry_riskGroup from r2.BSKRegistry where MorbusType_id = 84 and Person_id =  ps.Person_id order by BSKRegistry_setDate DESC limit 1) as \"BSKRegistry_riskGroup\",
                
                (select BSKRegistryData_data from r2.BSKRegistryData where BSKRegistry_id = 
                (
                select BSKRegistry_id from r2.BSKRegistry
                where 
                  Person_id = ps.Person_id  
                and
                  MorbusType_id = 88  
                order by BSKRegistry_setDate DESC
                limit 1
                )
                and BSKObservElement_id = 151) as \"BSKRegistry_functionClass\",            
                (select BSKRegistryData_data from r2.BSKRegistryData where BSKRegistry_id = 
                (
                select BSKRegistry_id from r2.BSKRegistry
                where 
                  Person_id = ps.Person_id  
                and
                  MorbusType_id = 89  
                order by   BSKRegistry_setDate DESC
                limit 1
                )
                and BSKObservElement_id = 269) as \"BSKRegistry_gegreeRisk\",                   
				{$InnField},
				dbo.getPersonPhones(ps.Person_id, ',') as \"Person_Phone\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN COALESCE(RTRIM(lpu.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: '||COALESCE(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '')||')' ELSE COALESCE(RTRIM(lpu.Lpu_Nick), '') end as \"Lpu_Nick\",
				PersonState.Lpu_id as \"Lpu_id\",
				pcard.PersonCard_id as \"PersonCard_id\",
				COALESCE(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				COALESCE(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				COALESCE(RTRIM(PS.Person_SecName), '') as \"Person_Secname\",
				COALESCE(RTRIM(PS.PersonEvn_id), '') as \"PersonEvn_id\",
				COALESCE(to_char(PS.Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_Birthday\",
				(date_part('year', dbo.tzGetDate()) - date_part('year', PS.Person_Birthday)
					+ case when EXTRACT(MONTH FROM PS.Person_Birthday) > EXTRACT(MONTH FROM dbo.tzGetDate())
					or (EXTRACT(MONTH FROM PS.Person_Birthday) = EXTRACT(MONTH FROM dbo.tzGetDate()) and EXTRACT(DAY FROM PS.Person_Birthday) > EXTRACT(DAY FROMdbo.tzGetDate()))
					then -1 else 0 end) as \"Person_Age\",
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as \"KLAreaType_id\",
				COALESCE(RTRIM(PS.Person_Snils), '') as \"Person_Snils\",
				COALESCE(RTRIM(Sex.Sex_Name), '') as \"Sex_Name\",
				COALESCE(RTRIM(Sex.Sex_Code), '') as \"Sex_Code\",
				COALESCE(RTRIM(Sex.Sex_id), '') as \"Sex_id\",
				COALESCE(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\",
				ps.SocStatus_id as \"SocStatus_id\",
				RTRIM(COALESCE(UAddress.Address_Nick, UAddress.Address_Address)) as \"Person_RAddress\",
				RTRIM(COALESCE(PAddress.Address_Nick, PAddress.Address_Address)) as \"Person_PAddress\",
				COALESCE(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				COALESCE(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				COALESCE(to_char(Document.Document_begDate, 'dd.mm.yyyy'), '') as \"Document_begDate\",
				COALESCE(RTRIM(DO.Org_Name), '') as \"OrgDep_Name\",
				COALESCE(OmsSprTerr.OmsSprTerr_id, 0) as \"OmsSprTerr_id\",
				COALESCE(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE COALESCE(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then COALESCE(RTRIM(vper.Person_EdNum), '') ELSE COALESCE(RTRIM(Polis.Polis_Num), '') END AS \"Polis_Num\",
				COALESCE(to_char(pcard.PersonCard_begDate, 'dd.mm.yyyy'), '') as \"PersonCard_begDate\",
				COALESCE(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '') as \"PersonCard_endDate\",
				COALESCE(to_char(pcard.LpuRegion_Name, 'dd.mm.yyyy'), '') as \"LpuRegion_Name\",
				COALESCE(to_char(Polis.Polis_begDate, 'dd.mm.yyyy'), '') as \"Polis_begDate\",
				COALESCE(to_char(Polis.Polis_endDate, 'dd.mm.yyyy'), '') as \"Polis_endDate\",
				COALESCE(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\",
				COALESCE(RTRIM(PJ.Org_id), '') as \"JobOrg_id\",
				COALESCE(RTRIM(PJ.Org_Name), '') as \"Person_Job\",
				COALESCE(RTRIM(PP.Post_Name), '') as \"Person_Post\",
				'' as \"Ident_Lpu\",
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
				/* -- в v_Person_all (reg) нет этих полей, надо Тарасу сказать чтобы добавил 
				isnull(convert(varchar(10), ps.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), ps.Person_closeDT, 104), '') as Person_closeDT,
				ps.Person_IsDead,
				ps.PersonCloseCause_id
				*/
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS \"Person_IsBDZ\",
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END AS \"Person_IsFedLgot\",
				COALESCE(to_char(Person.Person_deadDT, 'dd.mm.yyyy'), '') as \"Person_deadDT\",
				COALESCE(to_char(Person.Person_closeDT, 'dd.mm.yyyy'), '') as \"Person_closeDT\",
				Person.Person_IsDead as \"Person_IsDead\",
				Person.PersonCloseCause_id as \"PersonCloseCause_id\",
				0 as \"Children_Count\"
				,PersonPrivilegeFed.PrivilegeType_id as \"PrivilegeType_id\"
				,PersonPrivilegeFed.PrivilegeType_Name as \"PrivilegeType_Name\"
				{$extendSelect}
			FROM {$object} PS  
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join SocStatus on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Address UAddress on UAddress.Address_id = PS.UAddress_id
				left join v_KLArea KLArea on KLArea.KLArea_id = UAddress.KLTown_id
				left join Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_Job Job on Job.Job_id = PS.Job_id
				left join Org PJ on PJ.Org_id = Job.Org_id
				left join Post PP on PP.Post_id = Job.Post_id
				left join Document on Document.Document_id = PS.Document_id
				left join OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org DO on DO.Org_id = OrgDep.Org_id
				left join Polis on Polis.Polis_id = PS.Polis_id
				left join PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join OmsSprTerr on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PO on PO.Org_id = OrgSmo.Org_id
				left join Person on Person.Person_id = PS.Person_id
				left join PersonState on PS.Person_id = PersonState.Person_id
				left join lateral (
				select vper.Person_edNum
				from v_Person_all vper
				where vper.Person_id = PS.Person_id
				and vper.PersonEvn_id = PS.PersonEvn_id
				limit 1
				) vper on true
				left join lateral
				(SELECT 
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.PrivilegeType_id <= 150 AND
					PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
					PP.Person_id = PS.Person_i
					limit 1	
				) PersonPrivilegeFed on true
				left join lateral (select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
					) as pcard on true
				left join v_Lpu lpu on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR  ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = EXTRACT(YEAR FROM dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
			LIMIT 1
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
            select
                 Error_Code as \"Error_Code\",
				 Error_Message as \"Error_Msg\"
			from r2.p_PersonRegister_ins
                   Person_id  := :Person_id,
                   MorbusType_id  := :MorbusType_id,
                   Diag_id  := :Diag_id,
                   PersonRegister_Code  := :PersonRegister_Code,
                   PersonRegister_setDate  := :PersonRegister_setDate,
                   MedPersonal_iid  := :MedPersonal_iid,
                   Lpu_iid  := :Lpu_iid,
                   EvnNotifyBase_id  := :EvnNotifyBase_id,
                   pmUser_id  := :pmUser_id,
                   MedPersonal_did  := :MedPersonal_did,
                   Lpu_did  := :Lpu_did,
                   Morbus_id  := :Morbus_id,
                   PersonRegisterOutCause_id  := :PersonRegisterOutCause_id
                  );";
        //echo getDebugSql($query, $params);
        //exit;

        $result = $this->db->query($query, $params);

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
                        uc.pmUser_id as \"pmUser_id\"
                        ,RTRIM(uc.pmUser_Name) || ' (' ||  RTRIM(uc.pmUser_Login) || ')' as \"pmUser_FioL\"
                        ,RTRIM(uc.pmUser_Name) as \"pmUser_Fio\"
                        ,RTRIM(uc.pmUser_Login) as \"pmUser_Login\"
                        ,RTRIM(mp.MedPersonal_Code) as \"MedPersonal_Code\"
                        ,RTRIM(mp.MedPersonal_TabCode) as \"MedPersonal_TabCode\"
                from pmUserCache uc
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
                'PersonRegister_disDate' => null,
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
     * Получаем тип МО по идентификатору МО (Lpu_id)
     */
    function getLpuType($data) {

        $params = array(
            'Lpu_id' => $data['Lpu_id']
        );

        $query = "
            select LpuType_id as \"LpuType_id\" from v_Lpu where Lpu_id = :Lpu_id limit 1
        ";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение идентификатора анкеты по дате содания квс
     */
    function getOksId($data) {

        $params = array(
            'Person_id' => $data['Person_id'],
            'EvnPS_NumCard' => $data['EvnPS_NumCard']
        );

        $query = "
			SELECT
				P.Person_id as \"Person_id\",
				P.Person_deadDT as \"Person_deadDT\",
				BRD.BSKRegistry_id as \"BSKRegistry_id\"
			FROM
				v_Person P
				left join lateral (
					select BSKRegistry_id
					from r2.BSKRegistryData
					where BSKObservElement_id = 398
					and BSKRegistryData_data = :EvnPS_NumCard
					limit 1
				) BRD on true
			WHERE
				P.Person_id = :Person_id
			LIMIT 1
        ";

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
}
?>