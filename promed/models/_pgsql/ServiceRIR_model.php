<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceRIR_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */

class ServiceRIR_model extends swPgModel {
	protected $startDate;
	protected $_soapClient;
	protected $_config = [];
	private $ServiceList_id;
	private $success = true;
	private $log;
	private $id;
	private $limit;
	private $allowed_lpus;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->helper('xml');
		$this->load->helper('ServiceListLog');
		$this->load->library('textlog', array('file'=>'ServiceRIR_'.date('Y-m-d').'.log'));

		$this->_config = $this->config->item('ServiceRIR');

		$this->_soapClient = new SoapClient($this->_config['apiurl'], [
			'trace' => 1,
			'connection_timeout' => $this->_config['timeout'] ?? 30
		]);
		$this->limit = $this->_config['limit'] ?? 100;
		$this->allowed_lpus = $this->_config['allowed_lpus'] ?? [];

		$this->ServiceList_id = 113;
	}

	/**
	 * @param $method
	 * @param $packageType
	 * @param array $params
	 * @return mixed
	 */
    private function exec($method, $packageType, $params = []) {
        $original_timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 10);

        $log_param = $params;
        if (isset($log_param['PASSWORD'])) {
            unset($log_param['PASSWORD']);
        }
        $this->textlog->add(sprintf('exec:%s %s', $method, print_r($log_param, true)));
        unset($log_param);

        try {
            if (!empty($params)) {
                $soap_params = new SoapParam($params, $method);
                $soap_result = $this->_soapClient->$method($soap_params);
            } else {
                $soap_result = $this->_soapClient->$method();
            }
        } catch (SoapFault $e) {
            $this->textlog->add(sprintf('error: %s, message: %s, data:%s', $method, $e->getMessage(), print_r(isset($soap_result) ? $soap_result : null, true)));
			throw new RuntimeException('Error during soap function call', 0, $e);
        }

        if (isset($_REQUEST['getDebug'])) {
            echo "<br><br>REQUEST:<br><textarea cols=150 rows=20>" . $this->_soapClient->__getLastRequest() . "</textarea><br>";
            echo "<br><br>RESPONSE:<br><textarea cols=150 rows=20>" . $this->_soapClient->__getLastResponse() . "</textarea><br>";
        }

		$request = $params['ServicePackage_Data'] = preg_replace("#<ns1:PASSWORD>(.*?)</ns1:PASSWORD>#uis", "", $this->_soapClient->__getLastRequest());

		$resp = $this->log->addPackage($params['object'], $params['id'], null, $params['Lpu_id'], $packageType, null, $request);
		$packageId = $resp[0]['ServiceListPackage_id'] ?? null;

        $this->textlog->add(sprintf('response:%s, data: %s', $method, print_r($soap_result, true)));

        ini_set('default_socket_timeout', $original_timeout);

        return [$soap_result, $packageId];
    }

	/**
	 * Запуск сервиса
	 * @param $data
	 * @return array|mixed
	 */
    public function syncAll($data) {

    	$this->id = $data['id'];

        $sql_methods = [
            'NEW_RUID' => 'Get_New_RUID', 
            'CANCEL_HOSPITALISATION_REFERRAL' => 'AppCancel', 
            'HOSPITALISATION' => 'Hospital',
            'HOSPITALISATION_CORR' => 'HospitalCorr', 
            'HOSPITALISATION_EXT' => 'HospitalExt', 
            'HOSPITALISATION_EXT_CORR' => 'HospitalExtCorr',
            'MOTION_IN_HOSPITAL' => 'PacientOut',
			'MOTION_IN_HOSPITAL_CORR' => 'PacientOutCorr',
			'HOSPITAL_FREE_BEDS' => 'AvailableBerths',
			'TELECONSULT' => 'Consult',
        ];
        if (isset($_REQUEST['sqlMethods'])) {
            $sql_methods = array_intersect($sql_methods, explode(',', $_REQUEST['sqlMethods']));
        }

		$this->log = new ServiceListLog($this->ServiceList_id, 1);

		$this->log->start();

        $errors = [];

        $this->startDate = $this->getFirstResultFromQuery("
			select to_char(ServiceListLog_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"dt\" 
			from stg.ServiceListLog sll  
			where ServiceList_id = 113 and ServiceListResult_id = 1
			order by ServiceListLog_id desc
			limit 1
"		);

        if ($this->startDate === false) {
			$this->startDate = '2020-01-01 00:00:00';
		}

        foreach ($sql_methods as $slpt => $sql_method) {

			try {

				if ($sql_method == 'Get_New_RUID') {
					$data = $this->getRUID();
				} else {
					$data = $this->getData($sql_method, $slpt);
				}

				if (isset($_REQUEST['getDebug'])) {
					echo '<br><br>'. $sql_method . ':<br><textarea cols=150 rows=20>' . htmlspecialchars(print_r($data, true)) . '</textarea><br>';
				}
			} catch (Exception $e) {
				$this->logError(['Во время получения данных для отправки произошла ошибка:', $e->getMessage()]);

				if (!in_array($e->getMessage(), $errors)) {
					$errors[] = $e->getMessage();
				}
				continue;
			}

			try {
				if (!empty($data)) {

					foreach ($data as $dt) {
						list($response, $packageId) = $this->exec("WEB_{$sql_method}", $slpt, $dt);
						$this->processResponse($response, $packageId, $dt, $slpt);
					}
				}
			} catch (Exception $e) {
				$this->logError(['Во время передачи данных в РИР произошла ошибка:', $e->getMessage()]);

				if (!in_array($e->getMessage(), $errors)) {
					$errors[] = $e->getMessage();
				}
			}
		}

        if ($errors) {
            $this->log->finish(false);

            $response = $this->createError('', 'Во время экспорта данных были ошибки');

            $this->textlog->add('Во время экспорта данных произошли ошибки' . "\n" . implode("\n", $errors));

            return $response;
        } else {
            $this->log->finish($this->success);

            return [
                ['success' => true]
            ];
        }
	}

    /**
     * @return int
     */
    private function getPayTypeOmsId() {
        static $pay_type_money_id = null;

        if ($pay_type_money_id === null) {
            $sql = "select PayType_id as \"PayType_id\" from v_PayType where PayType_SysNick='oms' limit 1";

            $pay_type_money_id = (int)$this->getFirstResultFromQuery($sql);
        }

        return $pay_type_money_id;
    }

	/**
	 * @param $ServiceListPackageType_Name
	 * @return int
	 */
    private function getSlpId($ServiceListPackageType_Name) {
		$sql = "select ServiceListPackageType_id as \"ServiceListPackageType_id\" from stg.ServiceListPackageType  where ServiceListPackageType_Name = ? limit 1";

		return $this->getFirstResultFromQuery($sql, [$ServiceListPackageType_Name]) ?? '';
    }
	
	/**
	 * Аналог getData для получения RUID
	 */
    private function getRUID() {
		
		$data = [];
		
		$result = $this->queryResult($this->Hospital(true), [
            'id' => $this->id,
            'startDate' => $this->startDate,
            'PayType_id' => $this->getPayTypeOmsId(),
            'ServiceListPackageType_id' => $this->getSlpId('HOSPITALISATION'),
        ]);
		
		$result = array_merge($result, $this->queryResult($this->HospitalExt(true), [
            'id' => $this->id,
            'startDate' => $this->startDate,
            'PayType_id' => $this->getPayTypeOmsId(),
            'ServiceListPackageType_id' => $this->getSlpId('HOSPITALISATION_EXT'),
        ]));
		
		foreach($result as $v) {
			if (in_array($v['Lpu_id'], [13100875, 13100922])) {
				$v['KODLPU_S'] = '160101';
			}
			$data[] = [
        		'_in' => [
					'LOGIN' => $v['LOGIN'],
					'PASSWORD' => $v['PASSWORD'],
					'CDLPU' => $v['KODLPU_S'] ?? $v['KODLPU_R']
				],
				'id' => $v['pid'],
				'object' => null,
				'Lpu_id' => $v['Lpu_id'],
				'D_NAPR' => null,
				'D_GOSP' => null,
				'D_VYB' => null,
				'PR_DVIGEN' => null,
				'RIRMOParameter_id' => null,
			];
		}
		
        return $data;
    }

	/**
	 * @param string $sql_method
	 * @param $slpt
	 * @return array
	 */
    private function getData($sql_method, $slpt) {
        $result = $this->queryResult($this->$sql_method(), [
            'id' => $this->id,
            'startDate' => $this->startDate,
            'PayType_id' => $this->getPayTypeOmsId(),
            'ServiceListPackageType_id' => $this->getSlpId($slpt),
			
        ]);

        if (!$result) {
            return [];
        }

        $data = [];
        foreach ($result as $v) {

        	$zap = $v;

        	unset($zap['id']);
        	unset($zap['pid']);
        	unset($zap['object']);
        	unset($zap['Lpu_id']);
        	unset($zap['LOGIN']);
        	unset($zap['PASSWORD']);
			unset($zap['LpuSection_id']);
			unset($zap['LpuSectionBedProfile_id']);
			unset($zap['RIRMOParameter_id']);

			if ($slpt == 'HOSPITAL_FREE_BEDS') {
				$zap['NUM_K'] = max($zap['NUM_K'], 0);
				$zap['NUM_K_M'] = max($zap['NUM_K_M'], 0);
				$zap['NUM_K_W'] = max($zap['NUM_K_W'], 0);
				$zap['NUM_K_C'] = max($zap['NUM_K_C'], 0);
			}

			$zap = ArrayToXml($zap, 'ZAP');
			$zap = trim(str_replace(['<?xml version="1.0" encoding="utf-8"?>'], '', $zap));

			/*if ($slpt == 'HOSPITAL_FREE_BEDS') {
				$isp = $this->getIsp($v);
				if (!empty($isp)) {
					$zap = str_replace('<ISP/>', $isp, $zap);
				}
			}*/

        	$dt = [
        		'_in' => [
					'LOGIN' => $v['LOGIN'],
					'PASSWORD' => $v['PASSWORD'],
					'VERS' => '1.6',
					'ZAP' => $zap
				],
				'id' => $v['id'],
				'object' => $v['object'],
				'Lpu_id' => $v['Lpu_id'],
				'D_NAPR' => $v['D_NAPR'] ?? null,
				'D_GOSP' => $v['D_GOSP'] ?? null,
				'D_VYB' => $v['D_VYB'] ?? null,
				'PR_DVIGEN' => $v['PR_DVIGEN'] ?? null,
				'RIRMOParameter_id' => $v['RIRMOParameter_id'] ?? null,
			];

			$data[] = $dt;
        }

        return $data;
    }
	
    /**
     * Функция отправки информации об отмене направлении на госпитализацию
     * @return string
     */
    private function AppCancel() {

    	$filter = $this->getFilter('ED.EvnDirection_id');

        return <<<SQL
			select
				1 as "N_ZAP"
				,L.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,ED.EvnDirection_id as "id"
				,'EvnDirection' as "object"
				,CAST(cLB.LpuBuilding_Code as varchar(10))
				|| RIGHT(CAST(date_part('YEAR', ED.EvnDirection_setDT) as varchar), 2) || '4'
				|| RIGHT('000000' || ED.EvnDirection_Num, 6) 
				as "RUID_NAPR"
				,case 
					when ED.Lpu_cid = ED.Lpu_did then 2 
					else 3
				end as "ORG"
				,cL.Lpu_f003mcod as "CODE"
				,cLB.LpuBuilding_Code as "KODLPU"
				,to_char(ED.EvnDirection_failDT, 'YYYY-MM-DD') as "D_CANC"
				,case 
					when ESH.EvnStatusCause_id = 18 then 1 -- Неявка пациента на госпитализацию
					when ESH.EvnStatusCause_id = 22 then 2 -- не предоставление необходимого пакета документов 
					when ESH.EvnStatusCause_id = 1 then 3 -- инициативный отказ от госпитализации пациентом
					when ESH.EvnStatusCause_id = 5 then 4 -- Смерть пациента
					when ESH.EvnStatusCause_id = 6 then 6 -- отказ в госпитализации из-за отсутствия медицинских показаний
					when ESH.EvnStatusCause_id = 13 then 7 -- отказ пациента от госпитализации по эпидемиологическим показаниям
					when ESH.EvnStatusCause_id = 20 then 8 -- карантин в стационарном отделении
					when ESH.EvnStatusCause_id = 12 then 9 -- направление пациента не по профилю заболевания
					when ESH.EvnStatusCause_id = 10 then 10 -- экстренная госпитализация в период ожидания плановой
					else 5 -- прочие
				end as "CODE_PR"
			from
				v_EvnDirection ED 
				inner join v_Lpu L  on L.Lpu_id = ED.Lpu_id
				inner join v_Lpu cL  on cL.Lpu_id = ED.Lpu_cid
				left join v_MedStaffFact cMSF  on cMSF.MedStaffFact_id = ED.MedStaffFact_fid
				inner join v_LpuSection cLS  on cLS.LpuSection_id = COALESCE(cMSF.LpuSection_id, ED.LpuSection_did)
				inner join v_LpuBuilding cLB  on cLB.LpuBuilding_id = cLS.LpuBuilding_id
				LEFT JOIN LATERAL(
					select ESH.EvnStatusCause_id
					from EvnStatusHistory ESH 
					where ESH.Evn_id = ED.EvnDirection_id and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
                    limit 1
				) ESH On true
				left join stg.ServiceListPackage SLP on 
					SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
					SLP.ServiceListPackage_ObjectID = ED.EvnDirection_id and 
					SLP.PackageStatus_id = 5
				inner join DataStorage dsl  on dsl.Lpu_id = ED.Lpu_cid and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = ED.Lpu_cid and dsp.DataStorage_Name = 'rir_pass'
			where
				SLP.ServiceListPackage_id is null
				and ED.DirType_id in (1,5)
				and ED.PayType_id = :PayType_id
				and ED.EvnDirection_failDT is not null
				and nullif(nullif(cL.Lpu_f003mcod,'0'),'') is not null
				{$filter}
			limit {$this->limit}
SQL;
	}
	
    /**
     * Отправка информации о госпитализации по направлению
     * @return string
     */
    private function Hospital($isGetRuid = false) {

		$filter = $this->getFilter('EPS.EvnPS_id');
		
		$ruid_join = $isGetRuid ? 'left' : 'inner';
		$ruid_filter = $isGetRuid ? ' and rd.RUIDDirection_id is null ' : '';

        return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,EPS.EvnPS_id as id
					,COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id) as pid
					,'EvnPS' as object
					,rd.RUIDDirection_RUID as RUID_NAPR
					,to_char(COALESCE(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 'YYYY-MM-DD') as D_NAPR
					,3 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,ES.LpuBuilding_Code as KODLPU_R
					,COALESCE(dL.Lpu_f003mcod, '500000') as CODE_MO_S
					,dLB.LpuBuilding_Code as KODLPU_S
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) || 
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(ES.LpuSectionBedProfile_Code, '0') as PROFIL
					,ES.LpuSectionProfile_Code as PODR
					,EPS.EvnPS_NumCard as NHISTORY
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') as D_GOSP
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_LpuSection dLS  on dLS.LpuSection_id = COALESCE(ED.LpuSection_id, EPS.LpuSection_pid)
					left join v_LpuBuilding dLB  on dLB.LpuBuilding_id = dLS.LpuBuilding_id
					left join v_Lpu_all dL  on dL.Lpu_id = coalesce(ED.Lpu_id, EPS.Lpu_did, dLS.Lpu_id)
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					LEFT JOIN LATERAL(
						select  
							ES.EvnSection_id, 
							ES.Diag_id, 
							LB.LpuBuilding_Code,
							LSP.LpuSectionProfile_Code,
							fLSBP.LpuSectionBedProfile_Code
						from v_EvnSection ES 
							inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
							inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
							inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							LEFT JOIN LATERAL (
								select LSBP.*
								from fed.v_LpuSectionBedProfileLink LSBPL 
								inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
								where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
                                limit 1
							) fLSBP On true
						where 
							ES.EvnSection_pid = EPS.EvnPS_id 
							and COALESCE(es.EvnSection_IsPriem, 1) = 1
						order by 
							ES.EvnSection_setDate 
                        limit 1
					) ES On true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLP.PackageStatus_id = 5
					{$ruid_join} join r50.RUIDDirection rd on rd.Evn_id = COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id)
				where
					SLP.ServiceListPackage_id is null
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospType_id = 2
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_id is not null
					and (ED.EvnDirection_id is not null or (
						EPS.PrehospDirect_id in (1, 2) and 
						EPS.EvnDirection_Num is not null and 
						EPS.EvnDirection_setDT is not null
					))
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
					{$ruid_filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,pid as "pid"
				,object as "object"
				,RUID_NAPR as "RUID_NAPR"
				,D_NAPR as "D_NAPR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,CODE_MO_S as "CODE_MO_S"
				,KODLPU_S as "KODLPU_S"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,NHISTORY as "NHISTORY"
				,D_GOSP as "D_GOSP"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}
	
    /**
     * Файл со скорректированными сведениями о госпитализации по направлению 
     * @return string
     */
    private function HospitalCorr() {

		$filter = $this->getFilter('EPS.EvnPS_id');

        return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,EPS.EvnPS_id as id
					,'EvnPS' as object
					,rmp.RIRMOParameter_id
					,rd.RUIDDirection_RUID as RUID_NAPR
					,to_char(rmp.RIRMOParameter_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') OLD_D_GOSP
					,to_char(COALESCE(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 'YYYY-MM-DD') as D_NAPR
					,3 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,ES.LpuBuilding_Code as KODLPU_R
					,COALESCE(dL.Lpu_f003mcod, '500000') as CODE_MO_S
					,dLB.LpuBuilding_Code as KODLPU_S
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) || 
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(ES.LpuSectionBedProfile_Code, '0') as PROFIL
					,ES.LpuSectionProfile_Code as PODR
					,EPS.EvnPS_NumCard as NHISTORY
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') as D_GOSP
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_LpuSection dLS  on dLS.LpuSection_id = COALESCE(ED.LpuSection_id, EPS.LpuSection_pid)
					left join v_LpuBuilding dLB  on dLB.LpuBuilding_id = dLS.LpuBuilding_id
					left join v_Lpu_all dL  on dL.Lpu_id = coalesce(ED.Lpu_id, EPS.Lpu_did, dLS.Lpu_id)
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					LEFT JOIN LATERAL(
						select 
							ES.EvnSection_id, 
							ES.Diag_id, 
							LB.LpuBuilding_Code,
							LSP.LpuSectionProfile_Code,
							fLSBP.LpuSectionBedProfile_Code
						from v_EvnSection ES 
							inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
							inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
							inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							LEFT JOIN LATERAL (
								select LSBP.*
								from fed.v_LpuSectionBedProfileLink LSBPL 
								inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
								where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
								limit 1
							) fLSBP ON true
						where 
							ES.EvnSection_pid = EPS.EvnPS_id 
							and COALESCE(es.EvnSection_IsPriem, 1) = 1
						order by ES.EvnSection_setDate 
                        limit 1
					) ES ON true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLP.ServiceListPackage_insDT > EPS.EvnPS_updDT and 
						SLP.PackageStatus_id = 5
					inner join stg.ServiceListPackage SLPH on 
						SLPH.ServiceListPackageType_id = 3 and -- HOSPITALISATION
						SLPH.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLPH.PackageStatus_id = 5
					inner join r50.RIRMOParameter rmp  on rmp.EvnPS_id = EPS.EvnPS_id
					inner join r50.RUIDDirection rd on rd.Evn_id = COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id)
				where
					SLP.ServiceListPackage_id is null
					and EPS.EvnPS_insDT != EPS.EvnPS_updDT
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospType_id = 2
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_id is not null
					and (ED.EvnDirection_id is not null or (
						EPS.PrehospDirect_id in (1, 2) and 
						EPS.EvnDirection_Num is not null and 
						EPS.EvnDirection_setDT is not null
					))
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,object as "object"
				,RIRMOParameter_id as "RIRMOParameter_id"
				,RUID_NAPR as "RUID_NAPR"
				,OLD_D_GOSP as "OLD_D_GOSP"
				,ps_old.Person_SurName as "OLD_FAM"
				,ps_old.Person_FirName as "OLD_IM"
				,ps_old.Person_SecName as "OLD_OT"
				,to_char(ps_old.Person_BirthDay, 'YYYY-MM-DD') as "OLD_DR"
				,D_NAPR as "D_NAPR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,CODE_MO_S as "CODE_MO_S"
				,KODLPU_S as "KODLPU_S"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,NHISTORY as "NHISTORY"
				,D_GOSP as "D_GOSP"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				LEFT JOIN LATERAL (
					select 
						PersonSurName_SurName Person_SurName,
						PersonFirName_FirName Person_FirName,
						PersonSecName_SecName Person_SecName,
						PersonBirthDay_BirthDay Person_BirthDay
					from v_PersonStateAll psa  
					where psa.Person_id = PS.Person_id and psa.PersonEvn_updDT < CAST(OLD_D_GOSP as date)
					order by psa.PersonEvn_id desc
                    limit 1
				) ps_old ON true
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Файл со сведениями о госпитализации без направления, в том числе экстренной
	 * @return string
	 */
	private function HospitalExt($isGetRuid = false) {

		$filter = $this->getFilter('EPS.EvnPS_id');
		
		$ruid_join = $isGetRuid ? 'left' : 'inner';
		$ruid_filter = $isGetRuid ? ' and rd.RUIDDirection_id is null ' : '';

		return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,EPS.EvnPS_id as id
					,EPS.EvnPS_id as pid
					,'EvnPS' as object
					,rd.RUIDDirection_RUID as RUID_NAPR
					,1 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,ES.LpuBuilding_Code as KODLPU_R
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) ||
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(ES.LpuSectionBedProfile_Code, '0') as PROFIL
					,ES.LpuSectionProfile_Code as PODR
					,EPS.EvnPS_NumCard as NHISTORY
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') as D_GOSP
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					LEFT JOIN LATERAL(
						select 
							ES.EvnSection_id, 
							ES.Diag_id, 
							LB.LpuBuilding_Code,
							LSP.LpuSectionProfile_Code,
							fLSBP.LpuSectionBedProfile_Code
						from v_EvnSection ES 
							inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
							inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
							inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							LEFT JOIN LATERAL (
								select LSBP.*
								from fed.v_LpuSectionBedProfileLink LSBPL 
								inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
								where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
								limit 1
							) fLSBP ON true
						where 
							ES.EvnSection_pid = EPS.EvnPS_id 
							and COALESCE(es.EvnSection_IsPriem, 1) = 1
						order by 
							ES.EvnSection_setDate 
                        limit 1
					) ES ON true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLP.PackageStatus_id = 5
					{$ruid_join} join r50.RUIDDirection rd on rd.Evn_id = EPS.EvnPS_id
				where
					SLP.ServiceListPackage_id is null
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospType_id in (1,3)
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_id is not null
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
					{$ruid_filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,pid as "pid"
				,object as "object"
				,RUID_NAPR as "RUID_NAPR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,lpu.Lpu_f003mcod as "KODLPU_A"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,NHISTORY as "NHISTORY"
				,D_GOSP as "D_GOSP"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				left join v_Lpu lpu  on lpu.Lpu_id = PS.Lpu_id
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Файл со скорректированными сведениями о госпитализации без направления, в том числе экстренной
	 * @return string
	 */
	private function HospitalExtCorr() {

		$filter = $this->getFilter('EPS.EvnPS_id');

		return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,EPS.EvnPS_id as id
					,'EvnPS' as object
					,rmp.RIRMOParameter_id
					,rd.RUIDDirection_RUID as RUID_NAPR
					,to_char(rmp.RIRMOParameter_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') OLD_D_GOSP
					,1 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,ES.LpuBuilding_Code as KODLPU_R
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) || 
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(ES.LpuSectionBedProfile_Code, '0') as PROFIL
					,ES.LpuSectionProfile_Code as PODR
					,EPS.EvnPS_NumCard as NHISTORY
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD"T"HH24:MI:SS') as D_GOSP
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					LEFT JOIN LATERAL(
						select 
							ES.EvnSection_id, 
							ES.Diag_id, 
							LB.LpuBuilding_Code,
							LSP.LpuSectionProfile_Code,
							fLSBP.LpuSectionBedProfile_Code
						from v_EvnSection ES 
							inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
							inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
							inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							LEFT JOIN LATERAL (
								select LSBP.*
								from fed.v_LpuSectionBedProfileLink LSBPL 
								inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
								where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
                                limit 1
							) fLSBP ON true
						where 
							ES.EvnSection_pid = EPS.EvnPS_id 
							and COALESCE(es.EvnSection_IsPriem, 1) = 1
						order by 
							ES.EvnSection_setDate 
                        limit 1
					) ES ON true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLP.ServiceListPackage_insDT > EPS.EvnPS_updDT and 
						SLP.PackageStatus_id = 5
					inner join stg.ServiceListPackage SLPH on 
						SLPH.ServiceListPackageType_id = 59 and -- HOSPITALISATION_EXT
						SLPH.ServiceListPackage_ObjectID = EPS.EvnPS_id and 
						SLPH.PackageStatus_id = 5
					inner join r50.RIRMOParameter rmp  on rmp.EvnPS_id = EPS.EvnPS_id
					inner join r50.RUIDDirection rd on rd.Evn_id = EPS.EvnPS_id
				where
					SLP.ServiceListPackage_id is null
					and EPS.EvnPS_insDT != EPS.EvnPS_updDT
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospType_id in (1,3)
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_id is not null
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,object as "object"
				,RIRMOParameter_id as "RIRMOParameter_id"
				,RUID_NAPR as "RUID_NAPR"
				,OLD_D_GOSP as "OLD_D_GOSP"
				,ps_old.Person_SurName as "OLD_FAM"
				,ps_old.Person_FirName as "OLD_IM"
				,ps_old.Person_SecName as "OLD_OT"
				,to_char(ps_old.Person_BirthDay, 'YYYY-MM-DD') as "OLD_DR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,lpu.Lpu_f003mcod as "KODLPU_A"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,NHISTORY as "NHISTORY"
				,D_GOSP as "D_GOSP"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				LEFT JOIN LATERAL (
					select 
						PersonSurName_SurName Person_SurName,
						PersonFirName_FirName Person_FirName,
						PersonSecName_SecName Person_SecName,
						PersonBirthDay_BirthDay Person_BirthDay
					from v_PersonStateAll psa  
					where psa.Person_id = PS.Person_id and psa.PersonEvn_updDT < CAST(OLD_D_GOSP as date)
					order by psa.PersonEvn_id desc
                    limit 1
				) ps_old ON true
				left join v_Lpu lpu  on lpu.Lpu_id = PS.Lpu_id
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Файл со сведениями о пациентах, выбывших из медицинских организаций,
	 * оказывающих медицинскую помощь в стационарных условиях (и переведенных в другие отделения МО)
	 * @return string
	 */
	private function PacientOut() {

		$filter = $this->getFilter('ES.EvnSection_id');

		return <<<SQL
			with t as (
				select
					1 as N_ZAP
					,L.Lpu_id
					,ES.EvnSection_id as id
					,'EvnSection' as object
					,rd.RUIDDirection_RUID as RUID_NAPR
					,1 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,LB.LpuBuilding_Code as KODLPU_R
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) ||
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(fLSBP.LpuSectionBedProfile_Code, '0') as PROFIL
					,LSP.LpuSectionProfile_Code as PODR
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD') as D_GOSP
					,to_char(ES.EvnSection_disDT, 'YYYY-MM-DD') as D_VYB
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					inner join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id and COALESCE(es.EvnSection_IsPriem, 1) = 1
					inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
					inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					LEFT JOIN LATERAL (
						select LSBP.*
						from fed.v_LpuSectionBedProfileLink LSBPL 
						inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
						limit 1
					) fLSBP ON true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = ES.EvnSection_id and 
						SLP.PackageStatus_id = 5
					inner join r50.RUIDDirection rd on rd.Evn_id = COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id)
				where
					SLP.ServiceListPackage_id is null
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_disDate is not null
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,object as "object"
				,RUID_NAPR as "RUID_NAPR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,D_GOSP as "D_GOSP"
				,D_VYB as "D_VYB"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				left join v_Lpu lpu  on lpu.Lpu_id = PS.Lpu_id
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Файл со скорректированными сведениями о пациентах, выбывших из медицинских организаций,
	 * оказывающих медицинскую помощь в стационарных условиях (и переведенных в другие отделения МО)
	 * @return string
	 */
	private function PacientOutCorr() {

		$filter = $this->getFilter('ES.EvnSection_id');

		return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,ES.EvnSection_id as id
					,'EvnSection' as object
					,rmp.RIRMOParameter_id
					,rd.RUIDDirection_RUID as RUID_NAPR
					,to_char(rmp.RIRMOParameter_disDate, 'YYYY-MM-DD') OLD_D_VYB
					,COALESCE(rmp.RIRMOParameter_IsTransfer, 0) as OLD_PR_DVIGEN
					,1 as FOR_POM
					,L.Lpu_f003mcod as CODE_MO_R
					,LB.LpuBuilding_Code as KODLPU_R
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EPS.EvnPS_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) || 
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') ||
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,trim(trailing '.' from d.Diag_Code) as DS
					,COALESCE(fLSBP.LpuSectionBedProfile_Code, '0') as PROFIL
					,LSP.LpuSectionProfile_Code as PODR
					,to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD') as D_GOSP
					,to_char(ES.EvnSection_disDT, 'YYYY-MM-DD') as D_VYB
					,case 
						when EPS.EvnPS_IsCont = 2 then 1 
						else 0 
					end as PR_DVIGEN
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					inner join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id and COALESCE(es.EvnSection_IsPriem, 1) = 1
					inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
					inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					LEFT JOIN LATERAL (
						select LSBP.*
						from fed.v_LpuSectionBedProfileLink LSBPL 
						inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						where LSBPL.LpuSectionBedProfile_id = COALESCE(ES.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
						limit 1
					) fLSBP ON true
					inner join v_Diag d  on d.Diag_id = COALESCE(EPS.Diag_pid, ES.Diag_id)
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = ES.EvnSection_id and 
						SLP.ServiceListPackage_insDT > ES.EvnSection_updDT and 
						SLP.PackageStatus_id = 5
					inner join stg.ServiceListPackage SLPH on 
						SLPH.ServiceListPackageType_id = 4 and -- MOTION_IN_HOSPITAL
						SLPH.ServiceListPackage_ObjectID = ES.EvnSection_id and 
						SLPH.PackageStatus_id = 5
					inner join r50.RIRMOParameter rmp  on rmp.EvnSection_id = ES.EvnSection_id
					inner join r50.RUIDDirection rd on rd.Evn_id = COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id)
				where
					SLP.ServiceListPackage_id is null
					and ES.EvnSection_updDT != ES.EvnSection_insDT
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospWaifRefuseCause_id is null
					and ES.EvnSection_disDate is not null
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
                limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,object as "object"
				,OLD_D_VYB as "OLD_D_VYB"
				,OLD_PR_DVIGEN as "OLD_PR_DVIGEN"
				,ps_old.Person_SurName as "OLD_FAM"
				,ps_old.Person_FirName as "OLD_IM"
				,ps_old.Person_SecName as "OLD_OT"
				,RUID_NAPR as "RUID_NAPR"
				,FOR_POM as "FOR_POM"
				,CODE_MO_R as "CODE_MO_R"
				,KODLPU_R as "KODLPU_R"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,PS.Person_Phone as "PHONE"
				,DS as "DS"
				,PROFIL as "PROFIL"
				,PODR as "PODR"
				,D_GOSP as "D_GOSP"
				,D_VYB as "D_VYB"
				,PR_DVIGEN as "PR_DVIGEN"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				LEFT JOIN LATERAL (
					select 
						PersonSurName_SurName Person_SurName,
						PersonFirName_FirName Person_FirName,
						PersonSecName_SecName Person_SecName,
						PersonBirthDay_BirthDay Person_BirthDay
					from v_PersonStateAll psa  
					where psa.Person_id = PS.Person_id and psa.PersonEvn_updDT < CAST(OLD_D_VYB as date)
					order by psa.PersonEvn_id desc
                    limit 1
				) ps_old ON true
				left join v_Lpu lpu  on lpu.Lpu_id = PS.Lpu_id
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Файл со сведениями о наличии свободных мест на госпитализацию,
	 * движении пациентов в разрезе профилей и о выполненных объемах медицинской помощи
	 * @return string
	 */
	private function AvailableBerths() {

		$filter = $this->getFilter('LS.LpuSection_id');

		return <<<SQL
			with cte as ( 
				select 
                	dbo.tzGetDate() as curDt,
					dateadd('day', -2, CAST(cast(dbo.tzGetDate() as varchar(10))||' 20:00:00' as timestamp)) as  startDt,
              		dateadd('day', -1, CAST(cast(dbo.tzGetDate() as varchar(10))||' 19:59:59' as timestamp)) as endDt,
					dateadd('day', -1, CAST(cast(dbo.tzGetDate() as varchar(10))||' 20:00:00' as timestamp)) as startDtNext,
					CAST(cast(dbo.tzGetDate() as varchar(10))||' 19:59:59' as timestamp) as endDtNext
             )

			select
				1 as "N_ZAP"
				,L.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,LS.LpuSection_id as "id"
				,'LpuSection' as "object"
				,LS.LpuSection_id as "LpuSection_id"
				,LSBP.LpuSectionBedProfile_id as "LpuSectionBedProfile_id"
				,to_char((select curDt from cte), 'YYYY-MM-DD') as "DATE_S"
				,L.Lpu_f003mcod as "CODE_MO_R"
				,LB.LpuBuilding_Code as "KODLPU_R"
				,COALESCE(fLSBP.LpuSectionBedProfile_Code, '0') as "PROFIL"
				,LSP.LpuSectionProfile_Code as "PODR"
				,PAC_VSEGO.cnt as "PAC_VSEGO"
				,PAC_IN.cnt as "PAC_IN"
				,PAC_OUT.cnt as "PAC_OUT"
				,NUM_GOSP.cnt as "NUM_GOSP"
				,COALESCE(LSBS.LpuSectionBedState_Fact, 0) - NUM_GOSP.cnt - COALESCE(busyCount.busy, 0) as "NUM_K"
				,COALESCE(LSBS.LpuSectionBedState_MaleFact, 0) - NUM_GOSP.cntMale - busyCount.busyMale as "NUM_K_M"
				,COALESCE(LSBS.LpuSectionBedState_FemaleFact, 0) - NUM_GOSP.cntFemale - busyCount.busyFemale as "NUM_K_W"
				,COALESCE(LSBS.LpuSectionBedState_ChildFact, 0) - NUM_GOSP.cntChild - busyCount.busyChild as "NUM_K_C"
				/*,'' as ISP*/
			from
				v_LpuSection LS 
				inner join v_Lpu L  on L.Lpu_id = LS.Lpu_id
				inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
				inner join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedState LSBSt  on LSBSt.LpuSection_id = LS.LpuSection_id
				inner join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = COALESCE(LSBSt.LpuSectionBedProfile_id, LS.LpuSectionBedProfile_id)
				LEFT JOIN LATERAL (
					select LSBP.*
					from fed.v_LpuSectionBedProfileLink LSBPL 
					inner join fed.v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					where LSBPL.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
                    limit 1
				) fLSBP ON true
				LEFT JOIN LATERAL (
					select COUNT(ES.EvnSection_id) cnt
					from v_EvnSection ES 
					where 
						ES.LpuSection_id = LS.LpuSection_id 
						and ES.EvnSection_setDate <= (select endDt from cte)
						and COALESCE(ES.EvnSection_disDate, '2099-12-31') >= (select endDt from cte)
				) PAC_VSEGO on true
				LEFT JOIN LATERAL (
					select COUNT(ES.EvnSection_id) cnt
					from v_EvnSection ES 
					where 
						ES.LpuSection_id = LS.LpuSection_id 
						and ES.EvnSection_setDate >= (select startDt from cte)
						and ES.EvnSection_setDate <= (select endDt from cte)
				) PAC_IN on true
				LEFT JOIN LATERAL (
					select COUNT(ES.EvnSection_id) cnt
					from v_EvnSection ES 
					where 
						ES.LpuSection_id = LS.LpuSection_id 
						and ES.EvnSection_disDate >= (select startDt from cte)
						and ES.EvnSection_disDate <= (select endDt from cte)
				) PAC_OUT on true
				LEFT JOIN LATERAL (
					select 
						COUNT(ED.EvnDirection_id) cnt,
						COALESCE(sum(case when ps.Sex_id = 1 and dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) >= 18 then 1 else 0 end), 0) as cntMale,
						COALESCE(sum(case when ps.Sex_id = 2 and dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) >= 18 then 1 else 0 end), 0) as cntFemale,
						COALESCE(sum(case when dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) < 18 then 1 else 0 end), 0) as cntChild
					from v_EvnDirection ED 
					inner join v_TimetableStac_lite tts  on tts.EvnDirection_id = ed.EvnDirection_id
					inner join v_PersonState ps  on ps.Person_id = ED.Person_id
					where 
						ED.LpuSection_did = LS.LpuSection_id 
						and tts.TimetableStac_setDate >= (select startDtNext from cte)
						and tts.TimetableStac_setDate <= (select endDtNext from cte)
				) NUM_GOSP on true
				LEFT JOIN LATERAL (
					select 
						sum(LSBS.LpuSectionBedState_Fact) LpuSectionBedState_Fact,
						sum(case when COALESCE(LSBP.LpuSectionBedProfile_IsChild, 1) != 2 then LSBS.LpuSectionBedState_MaleFact else 0 end) LpuSectionBedState_MaleFact,
						sum(case when COALESCE(LSBP.LpuSectionBedProfile_IsChild, 1) != 2 then LSBS.LpuSectionBedState_FemaleFact else 0 end) LpuSectionBedState_FemaleFact,
						sum(case when COALESCE(LSBP.LpuSectionBedProfile_IsChild, 1) = 2 then LSBS.LpuSectionBedState_Fact else 0 end) LpuSectionBedState_ChildFact
					from v_LpuSectionBedState LSBS 
					inner join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
					inner join v_LpuSection LSS  on LSS.LpuSection_id = LSBS.LpuSection_id
					where 
						LSBS.LpuSectionBedState_id = LSBSt.LpuSectionBedState_id
				) LSBS on true
				LEFT JOIN LATERAL (
					select
						count(*) as busy,
						COALESCE(sum(case when ps.Sex_id = 1 and dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) >= 18 then 1 else 0 end), 0) as busyMale,
						COALESCE(sum(case when ps.Sex_id = 2 and dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) >= 18 then 1 else 0 end), 0) as busyFemale,
						COALESCE(sum(case when dbo.Age2(ps.Person_BirthDay, (select curDt from cte)) < 18 then 1 else 0 end), 0) as busyChild
					from
						v_EvnSection ES 
						inner join v_PersonState ps  on ps.Person_id = ES.Person_id
					where
						ES.LpuSection_id = LS.LpuSection_id
						and ES.LpuSectionWard_id is not null
						and ES.EvnSection_setDate <= (select endDt from cte)
						and (ES.EvnSection_disDate > (select endDt from cte) or ES.EvnSection_disDate is null)
				) busyCount on true
				inner join DataStorage dsl  on dsl.Lpu_id = LS.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = LS.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
			where 
				LU.LpuUnitType_id in (1, 6, 7, 9)
				and L.Lpu_f003mcod is not null
				{$filter}
SQL;
	}

	/**
	 * Файл со сведениями о консультации НМИЦ с применением телемедицины
	 * @return string
	 */
	private function Consult() {

		$filter = $this->getFilter('EUT.EvnUslugaTelemed_id');

		return <<<SQL
			with t as (
				select 
					1 as N_ZAP
					,L.Lpu_id
					,EUT.EvnUslugaTelemed_id as id
					,'EvnUslugaTelemed' as object
					,2 as TYPE
					,rd.RUIDDirection_RUID as RUID_NAPR
					,COALESCE(L.Lpu_f003mcod, '500000') as CODE_MO_S
					,LB.LpuBuilding_Code as KODLPU_S
					,COALESCE(uL.Lpu_f003mcod, '500000') as CODE_MO_R
					,case 
						when MPNP.MedPersonalNotPromed_id is not null then
						'ФИО: ' || MPNP.MedPersonalNotPromed_Description
						|| ', Специальность: ' || COALESCE(MS.MedSpec_Name, '')
					else 
						'ФИО: ' || msf.Person_Fio
						|| ', Должность: ' || pst.name
						|| ', Специальность: ' || COALESCE(Speciality.name, '')
						|| ', Квалификационная категория: ' || COALESCE(Category.name, '')
						|| ', Дата присвоения: ' || COALESCE(to_char(qc.AssigmentDate, 'YYYY-MM-DD'), '')
					end as INFO_DOCTORS
					,to_char(EUT.EvnUslugaTelemed_setDate, 'YYYY-MM-DD') as DATE_CONSULT
					,case 
						when DATEDIFF('month', PS.Person_BirthDay, EUT.EvnUslugaTelemed_setDate) > 2 or PS.Person_EdNum is not null then '0'
						else 
							CAST(ps.Sex_id as varchar(1)) || 
							replace(to_char(PS.Person_BirthDay, 'DD-MM-YY') , '-', '') || 
							CAST(COALESCE(pc.PersonChild_CountChild, 1) as varchar(1))
					end as NOVOR
					,case 
						when EUT.UslugaTelemedResultType_id = 6 then 1
						when EUT.UslugaTelemedResultType_id = 8 then 2
						when EUT.UslugaTelemedResultType_id in (2,3) then 3
						else 4
					end as ID_R
					,EPS.Person_id
					,pr.Person_id as Person_pid
				from
					v_EvnPS EPS 
					inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
					left join v_EvnDirection ED  on ED.EvnDirection_id = EPS.EvnDirection_id
					left join v_PersonChild pc  on pc.Person_id = EPS.Person_id
					left join v_BirthSpecStac bss  on bss.BirthSpecStac_id = pc.BirthSpecStac_id
					left join v_PersonRegister pr  on pr.PersonRegister_id = bss.PersonRegister_id
					inner join v_EvnDirection EDT  on EDT.EvnDirection_rid = EPS.EvnPS_id and EDT.DirType_id = 17
					inner join v_EvnUslugaTelemed EUT  on EUT.EvnDirection_id = EDT.EvnDirection_id
					left join r50.MedPersonalNotPromed MPNP  on MPNP.EvnUslugaTelemed_id = EUT.EvnUslugaTelemed_id
					left join fed.MedSpec MS  on MS.MedSpec_id = MPNP.MedSpec_id
					inner join v_LpuSection LS  on LS.LpuSection_id = EUT.LpuSection_uid
					inner join v_LpuBuilding LB  on LB.LpuBuilding_id = LS.LpuBuilding_id
					inner join v_Lpu uL  on uL.Lpu_id = EUT.Lpu_id
					left join v_MedStaffFact msf  on msf.MedStaffFact_id = EUT.MedStaffFact_id
					left join persis.Post as pst  on pst.id = msf.Post_id
					LEFT JOIN LATERAL (
						select *
						from persis.QualificationCategory qc 
						where qc.MedWorker_id = msf.MedStaffFact_id
						order by AssigmentDate desc
                        limit 1
					) qc on true
					left join persis.Category Category  on qc.Category_id = Category.id
					left join persis.Speciality Speciality  on qc.Speciality_id = Speciality.id
					left join stg.ServiceListPackage SLP on 
						SLP.ServiceListPackageType_id = :ServiceListPackageType_id and 
						SLP.ServiceListPackage_ObjectID = EUT.EvnUslugaTelemed_id and 
						SLP.PackageStatus_id = 5
					inner join r50.RUIDDirection rd on rd.Evn_id = COALESCE(EPS.EvnDirection_id, EPS.EvnPS_id)
				where
					SLP.ServiceListPackage_id is null
					and EPS.PayType_id = :PayType_id
					and EPS.PrehospWaifRefuseCause_id is null
					and (ED.EvnDirection_id is not null or (
						EPS.PrehospDirect_id in (1, 2) and 
						EPS.EvnDirection_Num is not null and 
						EPS.EvnDirection_setDT is not null
					))
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					{$filter}
               limit {$this->limit}
			)
				
			select 
				N_ZAP as "N_ZAP"
				,t.Lpu_id as "Lpu_id"
				,dsl.DataStorage_Value as "LOGIN"
				,dsp.DataStorage_Value as "PASSWORD"
				,id as "id"
				,object as "object"
				,TYPE as "TYPE"
				,RUID_NAPR as "RUID_NAPR"
				,CODE_MO_S as "CODE_MO_S"
				,KODLPU_S as "KODLPU_S"
				,CODE_MO_R as "CODE_MO_R"
				,INFO_DOCTORS as "INFO_DOCTORS"
				,DATE_CONSULT as "DATE_CONSULT"
				,case 
					when PS.PolisType_id = 1 then 1 
					when PS.PolisType_id = 3 then 2
					when PS.PolisType_id = 4 then 3
				end as "VPOLIS"
				,PS.Polis_Ser as "SPOLIS"
				,PS.Polis_Num as "NPOLIS"
				,os.Orgsmo_f002smocod as "SMO"
				,case when os.Orgsmo_f002smocod is null then os.OrgSMO_Name end as "SMO_NAM"
				,ost.OMSSprTerr_OKATO as "SMO_OK"
				,DocTP.DocumentType_Code as "DOCTYPE"
				,PS.Document_Ser as "DOCSER"
				,PS.Document_Num as "DOCNUM"
				,NOVOR as "NOVOR"
				,PS.Person_SurName as "FAM"
				,PS.Person_FirName as "IM"
				,PS.Person_SecName as "OT"
				,case when ps.Sex_id = 3 then 1 else ps.Sex_id end as "W"
				,to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as "DR"
				,ID_R as "ID_R"
				,null as "COMMENT"
			from t
				inner join DataStorage dsl  on dsl.Lpu_id = t.Lpu_id and dsl.DataStorage_Name = 'rir_login'
				inner join DataStorage dsp  on dsp.Lpu_id = t.Lpu_id and dsp.DataStorage_Name = 'rir_pass'
				inner join v_PersonState PS  on PS.Person_id = (case when t.NOVOR = '0' then t.Person_id else t.Person_pid end)
				left join v_Polis p  on ps.Polis_id = p.Polis_id
				left join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
				left join v_OMSSprTerr ost  on ost.OMSSprTerr_id = p.OMSSprTerr_id
				left join v_Document Doc  on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP  on Doc.DocumentType_id = DocTP.DocumentType_id

SQL;
	}

	/**
	 * Получение блока ISP для AvailableBerths
	 * @param $data
	 * @return array|false
	 */
	private function getIsp($data) {

		$data['PayType_id'] = $this->getPayTypeOmsId();

		return $this->getFirstResultFromQuery("
			SELECT query_to_xml($$
			select 
				ROW_NUMBER() OVER(ORDER BY os.Orgsmo_f002smocod) AS N_ISP
				,os.Orgsmo_f002smocod as SMO
				,COUNT(*) as NUM_SLUCH
				,sum(DATEDIFF('day', EvnSection_setDate, EvnSection_disDate)) as NUM_KD
			from
				v_EvnPS EPS 
				inner join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id
				inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
				inner join v_Polis p  on ps.Polis_id = p.Polis_id
				inner join v_OrgSmo os  on p.OrgSmo_id = os.OrgSMO_id
			where 
				EPS.PayType_id = :PayType_id
				and EPS.EvnPS_disDate is not null
				and EPS.EvnPS_disDate >= CAST(to_char(GETDATE(),'YYYY')||'.01.01' AS date)
				and EPS.EvnPS_disDate <= dateadd('day', -1, CAST(cast(dbo.tzGetDate() as varchar(10))||' 19:59:59' as timestamp))
				and os.Orgsmo_f002smocod is not null
				and ES.LpuSection_id = :LpuSection_id
				and ES.LpuSectionBedProfile_id = :LpuSectionBedProfile_id
			group by 
				os.Orgsmo_f002smocod
                $$, false, false,'ISP')
		", $data, true);
	}

	/**
	 * Обработка ответа
	 * @param $response
	 * @param $packageId
	 * @param $data
	 * @param $slpt
	 */
	private function processResponse($response, $packageId, $data, $slpt) {

		$response = current((Array)$response);

		if (empty($response->CNT_OSHIB) || $response->CNT_OSHIB == 0) {

			switch ($slpt) {
				case 'NEW_RUID':
					$this->execCommonSP('r50.p_RUIDDirection_ins', [
						'RUIDDirection_RUID' => $response->RUID_OUT,
						'Evn_id' => $data['id'],
						'RUIDDirection_setDate' => date('Y-m-d H:i:s'),
						'pmUser_id' => 1
					], 'array_assoc');
					break;
				case 'HOSPITALISATION':
				case 'HOSPITALISATION_EXT':
					$this->execCommonSP('r50.p_RIRMOParameter_ins', [
						'EvnPS_id' => $data['id'],
						'RIRMOParameter_setDT' => $data['D_GOSP'],
						'pmUser_id' => 1
					], 'array_assoc');
					break;
				case 'MOTION_IN_HOSPITAL':
					$this->execCommonSP('r50.p_RIRMOParameter_ins', [
						'EvnSection_id' => $data['id'],
						'RIRMOParameter_disDate' => $data['D_VYB'],
						'RIRMOParameter_IsTransfer' => $data['PR_DVIGEN'] > 0 ? $data['PR_DVIGEN'] : null,
						'pmUser_id' => 1
					], 'array_assoc');
					break;
				case 'HOSPITALISATION_CORR':
					$this->execCommonSP('r50.p_RIRMOParameter_upd', [
						'RIRMOParameter_id' => $data['RIRMOParameter_id'],
						'EvnPS_id' => $data['id'],
						'RIRMOParameter_setDT' => $data['D_GOSP'],
						'pmUser_id' => 1
					], 'array_assoc');
					break;
				case 'MOTION_IN_HOSPITAL_CORR':
					$this->execCommonSP('r50.p_RIRMOParameter_upd', [
						'RIRMOParameter_id' => $data['RIRMOParameter_id'],
						'EvnSection_id' => $data['id'],
						'RIRMOParameter_disDate' => $data['D_VYB'],
						'RIRMOParameter_IsTransfer' => $data['PR_DVIGEN'] > 0 ? $data['PR_DVIGEN'] : null,
						'pmUser_id' => 1
					], 'array_assoc');
					break;
			}

			$this->log->setPackageStatus($packageId, 'AcceptedTFOMS');
			$this->log->add(true, '', $packageId);
		} else {
			$this->success = false;
			$this->log->setPackageStatus($packageId, 'RejectedTFOMS');
			$json_error = json_encode([
				'RUID_OUT' => $response->RUID_OUT ?? '',
				'STR_OUT' => $response->STR_OUT ?? '',
				'PR' => $response->PR
			], JSON_UNESCAPED_UNICODE);
			$this->log->add(false, $json_error, $packageId);
		}
	}

	/**
	 * Логирование ошибки
	 * @param $error
	 */
	private function logError($error) {

		$this->success = false;
		$this->log->add(false, $error);
	}

	/**
	 * Фильтрация по id
	 * @param $field_name
	 * @return string
	 */
	private function getFilter($field_name) {

		$filter = '';
		if (count($this->allowed_lpus)) {
			$allowed_lpus_str = implode(",", $this->allowed_lpus);
			$filter .= " and L.Lpu_id in ({$allowed_lpus_str}) ";
		}
		if (!empty($this->id)) {
			$filter .= " and {$field_name} = :id ";
		}
		return $filter;
	}
}
