<?php
require_once("Abstract_model.php");
require_once("EvnLabSample_model.php");
/**
 * Модель Заявки на лабораторное обследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       gabdushev
 * @version      март 2012
 *
 * @property int EvnDirection_id
 * @property int EvnDirection_Num
 * @property string EvnLabRequest_Comment
 * @property int EvnLabRequest_Count
 * @property datetime EvnLabRequest_didDT
 * @property datetime EvnLabRequest_disDT
 * @property int EvnLabRequest_id
 * @property int EvnLabRequest_Index
 * @property datetime EvnLabRequest_insDT
 * @property int EvnLabRequest_IsSigned
 * @property int EvnLabRequest_pid
 * @property int EvnLabRequest_rid
 * @property datetime EvnLabRequest_setDT
 * @property datetime EvnLabRequest_signDT
 * @property datetime EvnLabRequest_updDT
 * @property int Lpu_id
 * @property int Morbus_id
 * @property int PersonEvn_id
 * @property int pmUser_signID
 * @property int Server_id
 * @property int UslugaExecutionType_id
 * @property int UslugaComplex_id
 * @property int PayType_id
 * @property int pmUser_id
 * @property string EvnLabRequest_Ward
 * @property EvnLabSample_model EvnLabSample_model
 * @property Lis_EvnDirection_model Lis_EvnDirection_model
 * @property TimetableMedService_model TimetableMedService_model
 * @property string EvnLabRequest_RegNum
 */
class EvnLabRequest_model extends Abstract_model
{
	protected $fields = [
		"EvnDirection_id" => null,  //can be null: 0
		'EvnLabRequest_RegNum'   => null,
		"EvnLabRequest_Comment" => null,  //can be null: 1
		"EvnLabRequest_Count" => null,  //can be null: 1
		"EvnLabRequest_didDT" => null,  //can be null: 1
		"EvnLabRequest_IsCito" => null,  //can be null: 1
		"EvnLabRequest_disDT" => null,  //can be null: 1
		"EvnLabRequest_id" => null,  //can be null: 0
		"EvnLabRequest_Index" => null,  //can be null: 1
		"EvnLabRequest_insDT" => null,  //can be null: 1
		"EvnLabRequest_IsSigned" => null,  //can be null: 1
		"EvnLabRequest_pid" => null,  //can be null: 1
		"EvnLabRequest_rid" => null,  //can be null: 1
		"EvnLabRequest_setDT" => null,  //can be null: 1
		"EvnLabRequest_signDT" => null,  //can be null: 1
		"EvnLabRequest_updDT" => null,  //can be null: 1
		"Lpu_id" => null,  //can be null: 0
		"Morbus_id" => null,  //can be null: 1
		"PersonEvn_id" => null,  //can be null: 0
		"pmUser_signID" => null,  //can be null: 1
		"Server_id" => null,  //can be null: 0
		"UslugaExecutionType_id" => null,  //can be null: 0
		"UslugaComplex_id" => null,  //can be null: 0
		"EvnLabRequest_Ward" => null,
		"PayType_id" => null,  //can be null: 0
		"MedService_id" => null,
		"MedService_sid" => null,
		"pmUser_id" => null,  //can be null: 0,
		"EvnLabRequest_BarCode" => null,
		"EvnLabRequest_prmTime" => null,
		"EvnStatus_id" => null,
		"Diag_id" => null,
		"TumorStage_id" => null,
		"Mes_id" => null,
		"EvnLabRequest_statusDate" => null
	];

	public $EvnDirection_Num;
	public $EvnLabRequest_RegNum;
	public $Diag_id;
	public $Mes_id;
	public $EvnDirection_Org_sid;
	public $EvnDirection_Lpu_sid;
	public $EvnDirection_LpuSection_id;
	public $EvnDirection_MedStaffFact_id;
	public $EvnDirection_PrehospDirect_id;
	public $EvnDirection_setDT;
	public $EvnDirection_MedService_id;
	public $EvnDirection_IsCito;
	public $EvnDirection_Descr;
	public $EvnLabRequest_BarCode;
	private $EvnDirection_IsAuto;
	public $EvnLabSample;

	/**
	 * Заказ на проведение лабораторного исследования
	 * @var int
	 */
	public $EvnUslugaPar_oid;

	function __construct()
	{
		parent::__construct();
		$this->EvnLabSample = new EvnLabSample_model();
		$this->EvnLabSample->setTableName("EvnLabSample");
		$this->load->swapi("common");
	}

	/**
	 * Массовое взятие проб
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function takeLabSample($data)
	{
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		$response = array('Error_Msg' => '');

		if (!empty($data['EvnLabRequests'])) {
			$data['EvnLabRequests'] = json_decode($data['EvnLabRequests']);
			if (!empty($data['EvnLabRequests'])) {
				$isProb = false;
				foreach ($data['EvnLabRequests'] as $EvnLabRequest_id) {
					$data['EvnLabRequest_id'] = $EvnLabRequest_id;
					// достаём все невзятые пробы для заявки
					$result = $this->db->query("
						select
							EvnLabSample_id as \"EvnLabSample_id\"
						from
							v_EvnLabSample
						where
							EvnLabRequest_id = :EvnLabRequest_id
							and EvnLabSample_setDT is null
					", array(
						'EvnLabRequest_id' => $EvnLabRequest_id
					));

					if (is_object($result)) {
						$probes = $result->result('array');
						foreach ($probes as $key => $probe) {
							$isProb = true;
							$data['EvnLabSample_id'] = $probe['EvnLabSample_id'];
							$data['RefSample_id'] = null;
							$take_result = $this->EvnLabSample_model->takeLabSample($data);

							if (is_array($take_result) && !empty($take_result[0]['Alert_Msg'])) {
								$response['Alert_Msg'] = $take_result[0]['Alert_Msg'];
							}

							if (is_array($take_result) && !empty($take_result[0]['EvnLabSample_BarCode'])) {
								$response[$key]['EvnLabSample_id'] = $probe['EvnLabSample_id'];
								$response[$key]['EvnLabSample_BarCode'] = $take_result[0]['EvnLabSample_BarCode'];
								$response[$key]['success'] = true;
							}
						}
					}
				}

				if (!$isProb) {
					return array('Error_Msg' => 'В заявке отсутствуют невзятые пробы');
				}
			}
		}

		return $response;
	}

	/**
	 * Массовая отмена проб
	 */
	function cancelLabSample($data)
	{
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		$response = array(
			'Error_Msg' => '',
			'success' => true
		);

		if (!empty($data['EvnLabRequests'])) {
			$data['EvnLabRequests'] = json_decode($data['EvnLabRequests']);
			if (!empty($data['EvnLabRequests'])) {
				$isProb = false;
				foreach ($data['EvnLabRequests'] as $EvnLabRequest_id) {
					// достаём все взятые пробы для заявки
					$probes = $this->queryResult("
						select
							EvnLabSample_id as \"EvnLabSample_id\"
						from
							v_EvnLabSample
						where
							EvnLabRequest_id = :EvnLabRequest_id
					", array(
						'EvnLabRequest_id' => $EvnLabRequest_id
					));

					if (is_array($probes)) {
						foreach ($probes as $probe) {
							if (!empty($probe['EvnLabSample_id'])) {
								$isProb = true;
								$take_result = $this->EvnLabSample_model->cancel(array(
									'EvnLabSample_id' => $probe['EvnLabSample_id'],
									'pmUser_id' => $data['pmUser_id']
								));
								if (is_array($take_result) && !empty($take_result[0]['Alert_Msg'])) {
									$response['Alert_Msg'] = $take_result[0]['Alert_Msg'];
								}
							}
						}
					}
				}

				if (!$isProb) {
					return array('Error_Msg' => 'В заявке отсутствуют взятые пробы');
				}
			}
		}

		return $response;
	}

	/**
	 * Массовое одобрение результатов заявок
	 * @throws Exception
	 */
	function approveEvnLabRequestResults($data)
	{
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		$response = [
			'Error_Msg' => '',
			'success' => true
		];
		$filter = "";

		if (isset($data['onlyNormal']) && $data['onlyNormal'] == 2) {
			if ($data['onlyNormal'] == 2) {
				$filter = "and coalesce(EvnLabSample_IsOutNorm, 1) = 1";
			}
		} else {
			$data['onlyNormal'] = 1;
		}

		if (!empty($data['EvnLabRequests'])) {
			$data['EvnLabRequests'] = json_decode($data['EvnLabRequests']);
			if (!empty($data['EvnLabRequests'])) {
				// достаём пробы заявки
				$query = "
					select
						EvnLabSample_id as \"EvnLabSample_id\"
					from
						v_EvnLabSample
					where
						EvnLabRequest_id IN (" . implode(',', $data['EvnLabRequests']) . ")
						{$filter}
				";

				$result = $this->db->query($query);

				$data['EvnLabSamples'] = array();
				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$data['EvnLabSamples'][] = $respone['EvnLabSample_id'];
					}

					//нет проб, но поиск по всем пробам-> ошибка
					if (count($data['EvnLabSamples']) < 1 && $data['onlyNormal'] == 1) {
						return array('Error_Msg' => 'Нельзя одобрить заявку, т.к. отсутствуют взятые пробы');

						//нет проб, но поиск только по пробам без патологий -> пустой ответ
					} else if (count($data['EvnLabSamples']) < 1 && $data['onlyNormal'] == 1) {
						return $response;
					} else {
						$data['EvnLabSamples'] = json_encode($data['EvnLabSamples']);
						$approve_resp = $this->EvnLabSample_model->approveEvnLabSampleResults($data);
						if (!empty($approve_resp['Error_Msg'])) {
							$response = $approve_resp;
						}
					}
				}
			}
		}

		return $response;
	}

	/**
	 * @param $fieldname
	 * @return null
	 */
	public function getField($fieldname)
	{
		return isset($this->fields[$fieldname]) ? $this->fields[$fieldname] : null;
	}

	/**
	 * @param array $values
	 */
	public function assign($values)
	{
		//$values = $this->processParams($values);
		parent::assign($values);
		if (isset($values['EvnDirection_Num'])) {
			$this->EvnDirection_Num = $values['EvnDirection_Num'];
		}
		if (isset($values['Diag_id'])) {
			$this->Diag_id = $values['Diag_id'];
		}
		if (isset($values['Server_id'])) {
			$this->Server_id = $values['Server_id'];
		}
		if (isset($values['Mes_id'])) {
			$this->Mes_id = $values['Mes_id'];
		}
		if (isset($values['Org_sid'])) {
			$this->EvnDirection_Org_sid = $values['Org_sid'];
		}
		if (isset($values['Lpu_sid'])) {
			$this->EvnDirection_Lpu_sid = $values['Lpu_sid'];
		}
		if (isset($values['PrehospDirect_id'])) {
			$this->EvnDirection_PrehospDirect_id = $values['PrehospDirect_id'];
		}
		if (isset($values['EvnDirection_setDT'])) {
			$this->EvnDirection_setDT = $values['EvnDirection_setDT'];
		}
		if (empty($this->EvnLabRequest_setDT) && isset($values['EvnDirection_setDT'])) {
			$this->EvnLabRequest_setDT = $values['EvnDirection_setDT'];
		}
		if (isset($values['LpuSection_id'])) {
			$this->EvnDirection_LpuSection_id = $values['LpuSection_id'];
		}
		if (isset($values['MedStaffFact_id'])) {
			$this->EvnDirection_MedStaffFact_id = $values['MedStaffFact_id'];
		}
		if (isset($values['MedService_id'])) {
			$this->EvnDirection_MedService_id = $values['MedService_id'];
		}
		if (isset($values['EvnDirection_IsCito'])) {
			$this->EvnDirection_IsCito = 1;

			if ($values['EvnDirection_IsCito']) {
				$this->EvnDirection_IsCito = 2;
			}
		}
		if (isset($values['EvnDirection_Descr'])) {
			$this->EvnDirection_Descr = $values['EvnDirection_Descr'];
		}

		if (isset($values['LabSample'])) {
			$this->EvnLabSample->parseJson($values['LabSample']);
		}

		$this->EvnUslugaPar_oid = null;
		if (isset($values['EvnUsluga_id'])) {
			$this->EvnUslugaPar_oid = $values['EvnUsluga_id'];
		}

		// кэшируем параметры направления на заявку (дата направления и срочность)
		$this->fields['EvnLabRequest_didDT'] = $this->EvnDirection_setDT;
		$this->fields['EvnLabRequest_IsCito'] = $this->EvnDirection_IsCito;
	}

	/**
	 * @return bool
	 */
	protected function canDelete()
	{
		return $this->canBeDeleted(array(
			'EvnLabRequest_id' => $this->EvnLabRequest_id
		));
	}

	/**
	 * @return bool
	 */
	protected function canBeDeleted($data)
	{
		//удалять можно если новая
		$EvnStatus_SysNick = $this->getFirstResultFromQuery("
			SELECT
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			FROM
				v_EvnLabRequest elr
				inner join v_EvnStatus es on es.EvnStatus_id = elr.EvnStatus_id
			WHERE
				elr.EvnLabRequest_id = :EvnLabRequest_id
		",
		array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));

		$result = (empty($EvnStatus_SysNick) || $EvnStatus_SysNick == 'New'); // можно удалять если в статусе "Новая".
		return $result;
	}

	/**
	 * @return string
	 */
	protected function getTableName()
	{
		return 'EvnLabRequest';
	}

	/**
	 * @param null $EvnDirection_id
	 * @param null $EvnLabRequest_BarCode
	 * @return bool
	 * @throws Exception
	 */
	function load($EvnDirection_id = null, $EvnLabRequest_BarCode = null, $selectFields = '*', $addNameEntries = true)
	{
		$select = "";
		$join = [];
		$params = array(
			'EvnLabRequest_id' => $this->EvnLabRequest_id,
			'EvnDirection_id' => $EvnDirection_id,
			'EvnLabRequest_BarCode' => $EvnLabRequest_BarCode
		);
		if ($EvnDirection_id > 0) {
			$filter = "d.EvnDirection_id = :EvnDirection_id";
		} elseif ($this->EvnLabRequest_id > 0) {
			$filter = "EvnLabRequest_id = :EvnLabRequest_id";
		} elseif (!empty($EvnLabRequest_BarCode)) {
			$filter = "EvnLabRequest_BarCode = :EvnLabRequest_BarCode";
		} else {
			return false; // не прочитать данные заявки
		}

		if ( getRegionNick() == 'ufa' ) {
			$select .= "edpd.PersonDetailEvnDirection_id as \"PersonDetailEvnDirection_id\",";
			$select .= "edpd.HIVContingentTypeFRMIS_id as \"HIVContingentTypeFRMIS_id\",";
			$select .= "edpd.CovidContingentType_id as \"CovidContingentType_id\",";
			$select .= "edpd.HormonalPhaseType_id as \"HormonalPhaseType_id\",";
			$join[] = "left join v_PersonDetailEvnDirection edpd on edpd.PersonDetailEvnDirection_id = (
					select PersonDetailEvnDirection_id
					from v_PersonDetailEvnDirection
					where v_PersonDetailEvnDirection.EvnDirection_id = d.EvnDirection_id
					order by v_PersonDetailEvnDirection.PersonDetailEvnDirection_insDT desc
					limit 1
			)";
		}

		$join = implode(" ", $join);

		// todo: Запрос надо не то чтобы проверять,а выстраивать логику по новой по нормальному проектированию, и по новой
		$query = "
			select
				EvnLabRequest_id as \"EvnLabRequest_id\",
				EvnLabRequest_setDate as \"EvnLabRequest_setDate\",
				EvnLabRequest_setTime as \"EvnLabRequest_setTime\",
				EvnLabRequest_didDate as \"EvnLabRequest_didDate\",
				EvnLabRequest_didTime as \"EvnLabRequest_didTime\",
				EvnLabRequest_disDate as \"EvnLabRequest_disDate\",
				EvnLabRequest_disTime as \"EvnLabRequest_disTime\",
				EvnLabRequest_pid as \"EvnLabRequest_pid\",
				EvnLabRequest_rid as \"EvnLabRequest_rid\",
				COALESCE(t.Lpu_id, d.Lpu_id) AS \"Lpu_id\",
				COALESCE(t.Server_id, d.Server_id) AS \"Server_id\",
				COALESCE(t.PersonEvn_id, d.PersonEvn_id) AS \"PersonEvn_id\",
				EvnLabRequest_setDT as \"EvnLabRequest_setDT\",
				EvnLabRequest_disDT as \"EvnLabRequest_disDT\",
				EvnLabRequest_didDT as \"EvnLabRequest_didDT\",
				EvnLabRequest_insDT as \"EvnLabRequest_insDT\",
				EvnLabRequest_updDT as \"EvnLabRequest_updDT\",
				EvnLabRequest_Index as \"EvnLabRequest_Index\",
				EvnLabRequest_Count as \"EvnLabRequest_Count\",
				EvnLabRequest_RegNum as \"EvnLabRequest_RegNum\",
				COALESCE(t.Person_id, d.Person_id, ps.Person_id) AS \"Person_id\",
				COALESCE(t.Morbus_id, d.Morbus_id) AS \"Morbus_id\",
				EvnLabRequest_IsSigned as \"EvnLabRequest_IsSigned\",
				EvnLabRequest_signDT as \"EvnLabRequest_signDT\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				EvnLabRequest_Comment as \"EvnLabRequest_Comment\",
				d.PayType_id as \"EDPayType_id\",
				COALESCE(t.PayType_id, eu.PayType_id) AS \"PayType_id\",
				COALESCE(t.UslugaComplex_id, eu.UslugaComplex_id) AS \"UslugaComplex_id\",
				EvnLabRequest_Ward as \"EvnLabRequest_Ward\",
				d.EvnDirection_id as \"EvnDirection_id\",
				d.EvnDirection_Num as \"EvnDirection_Num\",
				COALESCE(t.Diag_id, d.Diag_id) as \"Diag_id\",
				t.TumorStage_id as \"TumorStage_id\",
				t.Mes_id as \"Mes_id\",
				d.EvnDirection_setDT as \"EvnDirection_setDT\",
				COALESCE(d.PrehospDirect_id, eu.PrehospDirect_id) as \"PrehospDirect_id\",
				coalesce(d.Lpu_sid, Lpu.Lpu_id) AS \"Lpu_sid\",
				COALESCE(d.LpuSection_id, eu.LpuSection_did) as \"LpuSection_id\",
				COALESCE(d.MedPersonal_id, eu.MedPersonal_did) as \"MedPersonal_id\",
				coalesce(d.Org_sid, Lpu.Org_id) AS \"Org_sid\",
				case when 2 = COALESCE(EvnDirection_IsCito, EvnUslugaPar_IsCito) then '1' else '0' end as \"EvnDirection_IsCito\",
				COALESCE(d.EvnDirection_Descr, '') as \"EvnDirection_Descr\",
				(select XmlTemplate_id from v_UslugaComplex u where u.UslugaComplex_id = COALESCE(t.UslugaComplex_id, eu.UslugaComplex_id)) as \"XmlTemplate_id\",
				d.EvnDirection_IsAuto as \"EvnDirection_IsAuto\",
				eu.EvnUslugaPar_id as \"EvnUsluga_id\",
				COALESCE(t.MedService_id, d.MedService_id) as \"MedService_id\",
				t.MedService_sid as \"MedService_sid\",
				EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
				eu.UslugaComplex_id as \"UslugaComplex_prescid\",
				d.MedStaffFact_id as \"MedStaffFact_id\",
				d.Post_id as \"Post_id\",
				d.MedPersonal_id as \"MedPersonal_id\",
				d.LpuSection_id as \"LpuSection_id\",
				d.MedPersonal_Code as \"MedPersonal_Code\",
				COALESCE(d.EvnDirection_IsReceive, 1) as \"EvnDirection_IsReceive\",
				{$select}
				d.pmUser_insID as \"pmUser_insID\"
     		from
		        v_EvnDirection_all d
		        left join v_EvnLabRequest t on t.EvnDirection_id = d.EvnDirection_id
				left join lateral(
					select
						eup.UslugaComplex_id,
						eup.PayType_id,
						eup.PrehospDirect_id,
						eup.LpuSection_did,
						eup.MedPersonal_did,
						eup.EvnUslugaPar_IsCito,
						eup.EvnUslugaPar_id,
						eup.Lpu_did
					from
						v_EvnUslugaPar eup
						--left join v_EvnPrescrLabDiag epr on epr.EvnPrescrLabDiag_id = eup.EvnPrescr_id
					where 
						eup.EvnDirection_id = d.EvnDirection_id
					limit 1	
				) eu on true
				left join v_PersonStateAll ps on ps.PersonEvn_id = t.PersonEvn_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(d.Lpu_sid, eu.Lpu_did)
				{$join}
		    where {$filter}
		";
		//echo getDebugSql($query, $params); exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		if (isset($response[0])) {
			if (empty($response[0]['MedStaffFact_id']) &&
				!empty($response[0]['dPost_id']) &&
				!empty($response[0]['dMedPersonal_id']) &&
				!empty($response[0]['dLpuSection_id'])
			) {
				$res = $this->common->GET('MedStaffFact/Id', $params, 'single');
				if (!$this->isSuccessful($res)) {
					return $res;
				}

				$response[0]['MedStaffFact_id'] = $res['MedStaffFact_id'];
			}

			if (!empty($response[0]['EvnDirection_id'])) {
				$resp_comm = $this->common->GET('EvnPrescr/EvnPrescrLabDiagDescr', [
					'EvnDirection_id' => $response[0]['EvnDirection_id']
				], 'list');
				if (!$this->isSuccessful($resp_comm)) {
					throw new \Exception($resp_comm['Error_Msg'], 400);
				}

				foreach ($resp_comm as $one_comm) {
					if (!empty($one_comm['EvnPrescrLabDiag_Descr'])) {
						$response[0]['EvnDirection_Descr'] = $one_comm['EvnPrescrLabDiag_Descr'] . PHP_EOL . $response[0]['EvnDirection_Descr'];
					}
				}
			}
			$this->assign($response[0]);
			$this->EvnDirection_IsAuto = $response[0]['EvnDirection_IsAuto'];
		}

		return $response;
	}

	/**
	 * @param null $EvnDirection_id
	 * @param null $EvnLabRequest_BarCode
	 * @return bool
	 */
	function loadForDelDocs($EvnDirection_id = null, $EvnLabRequest_BarCode = null, $selectFields = '*', $addNameEntries = true){
		$select = "";
		$join = [];
		$params = array(
			'EvnLabRequest_id' => $this->EvnLabRequest_id,
			'EvnDirection_id' => $EvnDirection_id,
			'EvnLabRequest_BarCode' => $EvnLabRequest_BarCode
		);
		if ($EvnDirection_id>0) {
			$filter = "d.Evn_id = :EvnDirection_id";
		} elseif ($this->EvnLabRequest_id>0) {
			$filter = "EvnLabRequest_id = :EvnLabRequest_id";
		} elseif (!empty($EvnLabRequest_BarCode)) {
			$filter = "EvnLabRequest_BarCode = :EvnLabRequest_BarCode";
		} else {
			return false; // не прочитать данные заявки
		}

		if ( getRegionNick() == 'ufa' ) {
			$select .= "edpd.PersonDetailEvnDirection_id as \"PersonDetailEvnDirection_id\",";
			$select .= "edpd.HIVContingentTypeFRMIS_id as \"HIVContingentTypeFRMIS_id\",";
			$select .= "edpd.CovidContingentType_id as \"CovidContingentType_id\",";
			$select .= "edpd.HormonalPhaseType_id as \"HormonalPhaseType_id\",";
			$join[] = "left join v_PersonDetailEvnDirection (nolock) edpd on edpd.PersonDetailEvnDirection_id = (
					select 
						PersonDetailEvnDirection_id
					from v_PersonDetailEvnDirection (nolock) 
					where 
						v_PersonDetailEvnDirection.EvnDirection_id = d.Evn_id
					order by v_PersonDetailEvnDirection.PersonDetailEvnDirection_insDT desc
					limit 1
			)";
		}

		$join = implode(" ", $join);

		// todo: Запрос надо не то чтобы проверять,а выстраивать логику по новой по нормальному проектированию, и по новой
		$query = "
			select
				'view' as \"accessType\",
				EE.Evn_id as \"EvnLabRequest_id\",
				to_char(EE.Evn_setDT, 'DD.MM.YYYY') as \"EvnLabRequest_setDate\",
				to_char(EE.Evn_setDT, 'HH24:MI:SS') as \"EvnLabRequest_setTime\",
				to_char(EE.Evn_didDT, 'DD.MM.YYYY') as \"EvnLabRequest_didDate\",
				to_char(EE.Evn_didDT, 'HH24:MI:SS') as \"EvnLabRequest_didTime\",
				to_char(EE.Evn_disDT, 'DD.MM.YYYY') as \"EvnLabRequest_disDate\",
				to_char(EE.Evn_disDT, 'HH24:MI:SS') as \"EvnLabRequest_disTime\",
				EE.Evn_pid as \"EvnLabRequest_pid\",
				EE.Evn_rid as \"EvnLabRequest_rid\",
				coalesce(EE.Lpu_id, ED.Lpu_id) as \"Lpu_id\",
				coalesce(EE.Server_id, ED.Server_id) as \"Server_id\",
				coalesce(EE.PersonEvn_id, ED.PersonEvn_id) as \"PersonEvn_id\",
				EE.Evn_setDT as \"EvnLabRequest_setDT\",
				EE.Evn_disDT as \"EvnLabRequest_disDT\",
				EE.Evn_didDT as \"EvnLabRequest_didDT\",
				EE.Evn_insDT as \"EvnLabRequest_insDT\",
				EE.Evn_updDT as \"EvnLabRequest_updDT\",
				EE.Evn_Index as \"EvnLabRequest_Index\",
				EE.Evn_Count as \"EvnLabRequest_Count\",
				EvnLabRequest_RegNum as \"EvnLabRequest_RegNum\",
				coalesce(EE.Person_id, ED.Person_id, ps.Person_id) as \"Person_id\",
				coalesce(EE.Morbus_id, ED.Morbus_id) as \"Morbus_id\",
				EE.Evn_IsSigned as \"EvnLabRequest_IsSigned\",
				EE.Evn_signDT as \"EvnLabRequest_signDT\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				EvnLabRequest_Comment as \"EvnLabRequest_Comment\",
				d.PayType_id as \"EDPayType_id\",
				coalesce(t.PayType_id, eu.PayType_id) as \"PayType_id\",
				coalesce(t.UslugaComplex_id, eu.UslugaComplex_id) as \"UslugaComplex_id\",
				EvnLabRequest_Ward as \"EvnLabRequest_Ward\",
				d.Evn_id as \"EvnDirection_id\",
				d.EvnDirection_Num as \"EvnDirection_Num\",
				coalesce(t.Diag_id, d.Diag_id) as \"Diag_id\",
				t.TumorStage_id as \"TumorStage_id\",
				t.Mes_id as \"Mes_id\",
				ED.Evn_setDT as \"EvnDirection_setDT\",
				coalesce(d.PrehospDirect_id, eu.PrehospDirect_id) as \"PrehospDirect_id\",
				coalesce(d.Lpu_sid, Lpu.Lpu_id) as \"Lpu_sid\",
				coalesce(d.LpuSection_id, eu.LpuSection_did) as \"LpuSection_id\",
				coalesce(d.MedPersonal_id, eu.MedPersonal_did) as \"MedPersonal_id\",
				coalesce(d.Org_sid, Lpu.Org_id) as \"Org_sid\",
				case when 2 = coalesce(EvnDirection_IsCito, EvnUslugaPar_IsCito) then '1' else '0' end as \"EvnDirection_IsCito\",
				coalesce(d.EvnDirection_Descr, '') as \"EvnDirection_Descr\",
				(
					select 
					XmlTemplate_id 
					from v_UslugaComplex u 
					where 
						u.UslugaComplex_id = coalesce(t.UslugaComplex_id, eu.UslugaComplex_id)
				) as \"XmlTemplate_id\",
				d.EvnDirection_IsAuto as \"EvnDirection_IsAuto\",
				eu.EvnUslugaPar_id as \"EvnUsluga_id\",
				coalesce(t.MedService_id, d.MedService_id) as \"MedService_id\",
				t.MedService_sid as \"MedService_sid\",
				EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
				eu.UslugaComplex_id as \"UslugaComplex_prescid\",
				d.MedStaffFact_id as \"MedStaffFact_id\",
				d.Post_id as \"dPost_id\",
				d.MedPersonal_id as \"dMedPersonal_id\",
				d.LpuSection_id as \"dLpuSection_id\",
				d.MedPersonal_Code as \"MedPersonal_Code\",
				coalesce(d.EvnDirection_IsReceive, 1) as \"EvnDirection_IsReceive\",
				{$select}
				ED.pmUser_insID
     		from
		        EvnDirection d
		        left join Evn ED on ED.Evn_id = d.Evn_id
		        left join EvnLabRequest t on t.EvnDirection_id = d.Evn_id
		        left join Evn EE on EE.Evn_id = t.Evn_id
				left join lateral (
					select
						eup.UslugaComplex_id,
						eup.PayType_id,
						eup.PrehospDirect_id,
						eup.LpuSection_did,
						eup.MedPersonal_did,
						eup.EvnUslugaPar_IsCito,
						eup.EvnUslugaPar_id,
						eup.Lpu_did
					from
						v_EvnUslugaPar eup
					where
						eup.EvnDirection_id = d.Evn_id
					limit 1
				) eu on true
				left join v_PersonState ps on ps.PersonEvn_id = EE.PersonEvn_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(d.Lpu_sid, eu.Lpu_did)
				{$join}
		    where {$filter}
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		if (isset($response[0])) {
			if (empty($response[0]['MedStaffFact_id']) &&
				!empty($response[0]['dPost_id']) &&
				!empty($response[0]['dMedPersonal_id']) &&
				!empty($response[0]['dLpuSection_id'])
			) {
				$res = $this->common->GET('MedStaffFact/Id', $params, 'single');
				if (!$this->isSuccessful($res)) {
					return $res;
				}

				$response[0]['MedStaffFact_id'] = $res['MedStaffFact_id'];
			}

			if (!empty($response[0]['EvnDirection_id'])) {
				$resp_comm = $this->common->GET('EvnPrescr/EvnPrescrLabDiagDescr', [
					'EvnDirection_id' => $response[0]['EvnDirection_id']
				], 'list');
				if (!$this->isSuccessful($resp_comm)) {
					throw new Exception($resp_comm['Error_Msg'], 400);
				}

				foreach ($resp_comm as $one_comm) {
					if (!empty($one_comm['EvnPrescrLabDiag_Descr'])) {
						$response[0]['EvnDirection_Descr'] = $one_comm['EvnPrescrLabDiag_Descr'] . PHP_EOL . $response[0]['EvnDirection_Descr'];
					}
				}
			}
			$this->assign($response[0]);
			$this->EvnDirection_IsAuto = $response[0]['EvnDirection_IsAuto'];
		}

		return $response;
	}


	/**
	 * Список услуг
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	function getNewEvnLabRequests($data)
	{
		return $this->loadEvnLabRequestList(array(
			'MedService_id' => $data['MedService_id'],
			'begDate' => date('Y-m-d', time() - 3 * 30 * 24 * 60 * 60), // созданных 3 месяца назад от текущей даты
			'endDate' => date('Y-m-d', time() + 24 * 60 * 60),
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnStatus_id' => 1
		));
	}

	/**
	 * @param $data
	 * @return string
	 */
	function getIFAEvnLabRequests($data) {
		$where = [];
		$params = [];

		$params['begDate'] = $data['begDate'];
		$params['endDate'] = $data['endDate'];
		$params['MedService_id'] = $data['MedService_id'];

		$where[] = "els.EvnLabSample_setDT is not null";
		$where[] = "ELS.MedService_id = :MedService_id";
		$where[] = ":begDate <= COALESCE( 
			cast(els.EvnLabSample_StudyDT as date),
			cast(els.EvnLabSample_setDate as date),
			cast(elr.EvnLabRequest_didDate as date)
		)";

		$where[] = ":endDate >= COALESCE(
			cast(els.EvnLabSample_StudyDT as date),
			cast(els.EvnLabSample_setDate as date),
			cast(elr.EvnLabRequest_didDate as date)
		)";

		if(!empty($data['AnalyzerTest_id'])) {
			$where[] = "AnT.AnalyzerTest_id = :AnalyzerTest_id";
			$params['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
		}


		if(!empty($data['MethodsIFA_id'])) {
			$params['MethodsIFA_id'] = $data['MethodsIFA_id'];
			$where[] = "MI.MethodsiFA_id = :MethodsIFA_id";
		}

		$where = implode(' and ', $where);

		$query = "
			select DISTINCT
				ELR.EvnLabRequest_id as \"EvnLabRequest_id\"
			from v_EvnLabSample ELS
			inner join v_EvnLabRequest ELR on ELR.EvnLabRequest_id = ELS.EvnLabRequest_id
			inner join v_UslugaTest UT on UT.EvnLabSample_id = ELS.EvnLabSample_id
			left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
			left join v_UslugaComplex uc ON ut.UslugaComplex_id = uc.UslugaComplex_id
			left join v_UslugaComplex ucp ON eupp.UslugaComplex_id = ucp.UslugaComplex_id
			left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id
			inner join lis.v_AnalyzerTest AnT on AnT.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			inner join lis.v_Analyzer A on A.Analyzer_id = AnT.Analyzer_id and A.MedService_id = ELR.MedService_id
			inner join v_MethodsIFAAnalyzerTest MIAT on MIAT.AnalyzerTest_id = AnT.AnalyzerTest_id
			inner join v_MethodsIFA MI on MI.MethodsIFA_id = MIAT.MethodsIFA_id
			where {$where}
		";
		//echo getDebugSQL($query, $params); die;
		$result = $this->queryResult($query, $params);
		if(!is_array($result)) {
			return "";
		}
		$EvnLabRequest_ids = [];
		foreach ($result as $obj) {
			$EvnLabRequest_ids[] = $obj['EvnLabRequest_id'];
		}

		return implode(',', $EvnLabRequest_ids);
	}

	/**
	 * Список услуг
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 * todo нужно перенести изменения по r152280
	 */
	function loadEvnLabRequestList($data)
	{
		$ed_filter = "(1=1) ";
		$elr_filter = "(1=1) ";
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate'],
			'MedService_id' => $data['MedService_id'],
			'EvnStatus_id' => !empty($data['EvnStatus_id']) ? $data['EvnStatus_id'] : null,
		);

		$isSearch = false;

		if(!empty($data['formMode']) && $data['formMode'] == 'ifa' && (!empty($data['AnalyzerTest_id'] || !empty($data['MethodsIFA_id'])))) {
			$EvnLabRequestIds = $this->getIFAEvnLabRequests($data);
			if(!$EvnLabRequestIds) {
				return [];
			}
			$elr_filter .= " and elr.EvnLabRequest_id in ({$EvnLabRequestIds})";
		}

		$params['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV()) {
				$elr_filter .= " and (COALESCE(ps.Person_SurName, '') || COALESCE(' ' || SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' ' || SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') ilike '%' || upper(:Person_SurName) || '%' or peh.PersonEncrypHIV_Encryp ilike '%' || upper(:Person_SurName) || '%')";
			} else {
				$elr_filter .= " and COALESCE(ps.Person_SurName, '') || COALESCE(' ' || SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' ' || SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') ilike '%' || upper(:Person_SurName) || '%'";
			}
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
			$isSearch = true;
		}

		if (!empty($data['Person_FirName'])) {
			$elr_filter .= " and ps.Person_FirName ilike '%' || upper(:Person_FirName) || '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
			$isSearch = true;
		}

		if (!empty($data['Person_SecName'])) {
			$elr_filter .= " and ps.Person_SecName ilike '%' || upper(:Person_SecName) || '%'";
			$params['Person_SecName'] = rtrim($data['Person_SecName']);
			$isSearch = true;
		}

		if (!empty($data['Person_BirthDay'])) {
			$elr_filter .= " and cast(ps.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
			$isSearch = true;
		}

		if (!empty($data['EvnDirection_Num'])) {
			$ed_filter .= " and ed.EvnDirection_Num ilike :EvnDirection_Num";
			$params['EvnDirection_Num'] = '%' . intval($data['EvnDirection_Num']) . '%';
			$isSearch = true;
		}

		if( !empty($data['EvnLabRequest_RegNum'])) {
			$elr_filter .= " and elr.EvnLabRequest_RegNum = :EvnLabRequest_RegNum";
			$params['EvnLabRequest_RegNum'] = $data['EvnLabRequest_RegNum'];
			$isSearch = true;
		}

		if( !empty( $data['Lpu_sid']) ) {
			$ed_filter .= " and ed.Lpu_sid = :Lpu_sid";
			$params['Lpu_sid'] = $data['Lpu_sid'];
			$isSearch = true;
		}

		if( !empty( $data['LpuSection_id'] ) ) {
			$ed_filter .= " and ed.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$isSearch = true;
		}

		if( !empty( $data['MedStaffFact_id'] ) ) {
			$ed_filter .= " and ed.MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$isSearch = true;
		}

		if (!empty($data['PrehospDirect_Name'])) {
			$ed_filter .= " and case 
						when 1 = ed.PrehospDirect_id then COALESCE(LpuSection.LpuSection_Name, Lpu.Lpu_Nick) -- 1 Отделение ЛПУ (Если не выбрали то ЛПУ)
						when 2 = ed.PrehospDirect_id then Lpu.Lpu_Nick -- 2 Другое ЛПУ --Lpu_sid - Направившее ЛПУ
						when ed.PrehospDirect_id in ( 3, 4, 5, 6 ) then Org.Org_nick -- 3 Другая организация -- 4 Военкомат -- 5 Скорая помощь -- 6 Администрация -- Org_sid - Направившая организация
						when 7 = ed.PrehospDirect_id then 'Пункт помощи на дому' --7Пункт помощи на дому
						else COALESCE(LpuSection.LpuSection_Name, Lpu_Nick)
					end ilike :PrehospDirect_Name";
			$params['PrehospDirect_Name'] = '%' . rtrim($data['PrehospDirect_Name']) . '%';
			$isSearch = true;
		}

		if (!empty($data['EvnLabSample_IsOutNorm'])) {
			$elr_filter .= " and COALESCE(elr.EvnLabRequest_IsOutNorm, 1) = :EvnLabSample_IsOutNorm";
			$params['EvnLabSample_IsOutNorm'] = $data['EvnLabSample_IsOutNorm'];
			$isSearch = true;
		}

		if (!empty($data['EvnLabRequest_FullBarCode'])) {
			$elr_filter .= " and elr.EvnLabRequest_BarCodes ilike '%'||:EvnLabRequest_FullBarCode||'%'";
			$params['EvnLabRequest_FullBarCode'] = $data['EvnLabRequest_FullBarCode'];
			$isSearch = true;
		}

		if (!empty($data['EvnDirection_IsCito'])) {
			$ed_filter .= " and COALESCE(ed.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$params['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
			$isSearch = true;
		}

		if (!empty($data['UslugaComplex_id'])) {
			$elr_filter .= " and exists(
				select
					EvnUsluga_id as \"EvnUslugaPar_id\"
				from v_EvnUsluga EvnUsluga
					inner join v_Evn Evn on Evn.Evn_id = EvnUsluga.EvnUsluga_id and Evn.EvnClass_id = 47
				where
					EvnUsluga.EvnDirection_id = elr.EvnDirection_id
					and EvnUsluga.UslugaComplex_id =  :UslugaComplex_id
				limit 1	
			)";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$isSearch = true;
		}

		if (!empty($data['filterSign'])) {
			$euFilter = "";
			switch ($data['filterSign']) {
				case 1:
					$euFilter = " and Evn.Evn_IsSigned = 2";
					break;
				case 2:
					$euFilter = " and Evn.Evn_IsSigned is null";
					break;
				case 3:
					$euFilter = " and Evn.Evn_IsSigned = 1";
					break;
			}
			$elr_filter .= " and exists(
				select
					EvnUsluga_id as EvnUslugaPar_id
				from v_EvnUsluga EvnUsluga
					inner join v_Evn Evn on Evn.Evn_id = EvnUsluga.EvnUsluga_id and Evn.EvnClass_id = 47
				where
					EvnUsluga.EvnDirection_id = elr.EvnDirection_id
					{$euFilter}
				limit 1	
			)";
			$isSearch = true;
		}

		$linkedMS = array(
			'MedService_id' => $params['MedService_id']
		);
		if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'pzm') {
			// для пункта забора не нужен список связанных лабораторий, т.к. фильтр идёт по пункту забора.
		} else if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'reglab') {
			// нужен список всех связанных лабораторий, а у них всех связанных ПЗ и рег. служб, т.к. заявка может быть создана в ПЗ или рег. службе
			$resp_linkedms = $this->queryResult("
				select 
					MSL.MedService_lid as \"MedService_id\"
				from 
					v_MedServiceLink MSL
				where msl.MedService_id = :MedService_id
				
				union
				
				select 
					MSL2.MedService_id as \"MedService_id\"
				from 
					v_MedServiceLink MSL
					inner join v_MedServiceLink MSL2 on MSL2.MedService_lid = MSL.MedService_lid
				where
					msl.MedService_id = :MedService_id
			", array(
				'MedService_id' => $params['MedService_id']
			));

			if (!empty($resp_linkedms)) {
				foreach ($resp_linkedms as $one_linkedms) {
					if (!in_array($one_linkedms['MedService_id'], $linkedMS)) {
						$linkedMS[] = $one_linkedms['MedService_id'];
					}
				}
			}
		} else {
			// для лаборатории нужен список всех связанных ПЗ и рег. служб
			$resp_linkedms = $this->queryResult("
				select distinct
					MSL.MedService_id as \"MedService_id\"
				from 
					v_MedServiceLink MSL
				where
					msl.MedService_lid = :MedService_id
			", array(
				'MedService_id' => $params['MedService_id']
			));

			if (!empty($resp_linkedms)) {
				foreach ($resp_linkedms as $one_linkedms) {
					if (!in_array($one_linkedms['MedService_id'], $linkedMS)) {
						$linkedMS[] = $one_linkedms['MedService_id'];
					}
				}
			}
		}

		$tmpQuery = "with ";
		$from = "v_EvnLabRequest elr";
		if (!empty($data['Person_id']) && !empty($data['EvnStatus_id']) && $data['EvnStatus_id'] == 1) {
			// поиск только новых заявок
			$elr_filter .= " and elr.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
			$elr_filter .= " and COALESCE(elr.EvnStatus_id, 1) = 1";

			if ($params['begDate'] == $params['endDate']) {
				$datefilter = "
					and :begDate = cast(elr.EvnLabRequest_insDT as date)
				";
			} else {
				$datefilter = "and cast(elr.EvnLabRequest_insDT as date) between :begDate and :endDate";
			}
		} else if (empty($data['EvnLabRequest_id'])) {
			if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'pzm') {
				$unionMsFilter = "and lr.MedService_sid = :MedService_id";
			} else {
				$unionMsFilter = "and lr.MedService_id in ('" . implode("','", $linkedMS) . "')";
			}

			$unionParts = array();
			if (empty($data['EvnStatus_id']) || $data['EvnStatus_id'] == 1) {
				if (getRegionNick() == 'kz' && $isSearch) {
					// поиск не должен учитывать дату для новых заявок.
					$unionParts[] = "
						Select
							lr.EvnLabRequest_id
						from
							v_EvnLabRequest lr
						where
							lr.EvnStatus_id = 1
							{$unionMsFilter}
					";
				} else {
					if ($params['begDate'] == $params['endDate']) {
						$filterUnionDate = "and cast(lr.EvnLabRequest_prmTime as date) = :begDate";
					} else {
						$filterUnionDate = "and cast(lr.EvnLabRequest_prmTime as date) between :begDate and :endDate";
					}
					$unionParts[] = "
						Select
							lr.EvnLabRequest_id
						from
							v_EvnLabRequest lr
							left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
						where
							lr.EvnStatus_id = 1
							{$filterUnionDate}
							{$unionMsFilter}
						"; // на дату записи или на дату постановки в очередь

					if ($params['begDate'] == $params['endDate']) {
						$filterUnionDate = "and cast(lr.EvnLabRequest_statusDate as date) = :begDate";
					} else {
						$filterUnionDate = "and cast(lr.EvnLabRequest_statusDate as date) between :begDate and :endDate";
					}
					$filterUnionDate .= ' and lr.EvnLabRequest_prmTime is null';
					$unionParts[] = "
						Select
							lr.EvnLabRequest_id
						from
							v_EvnLabRequest lr
							left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
						where
							lr.EvnStatus_id = 1
							{$filterUnionDate}
							{$unionMsFilter}
						"; // на дату записи или на дату постановки в очередь
					if ($params['begDate'] == $params['endDate']) {
						$filterUnionDate = "and cast(lr.EvnLabRequest_prmTime as date) < :begDate";
						$filterUnionDate .= " and ed.EvnDirection_setDate = :begDate";
						$unionParts[] = "
							Select
								lr.EvnLabRequest_id
							from
								v_EvnLabRequest lr 
								left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
							where
								lr.EvnStatus_id = 1
								{$filterUnionDate}
								{$unionMsFilter}
							";
					}
				}
			}

			if (empty($data['EvnStatus_id']) || $data['EvnStatus_id'] != 1) {
				if (!empty($data['EvnStatus_id'])) {
					$esFilter = "lr.EvnStatus_id = :EvnStatus_id";
				} else {
					$esFilter = "lr.EvnStatus_id <> 1";
				}
				if ($params['begDate'] == $params['endDate']) {
					$filterUnionDate = "and cast(lr.EvnLabRequest_statusDate as date) = :begDate";
				} else {
					$filterUnionDate = "and cast(lr.EvnLabRequest_statusDate as date) between :begDate and :endDate";
				}
				$unionParts[] = "
					Select
						lr.EvnLabRequest_id
					from
						v_EvnLabRequest lr
						left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
					where
						{$esFilter}
						{$filterUnionDate}
						{$unionMsFilter}
					"; // на дату смены статуса
			}
			$datefilter = "";
			$tmpQuery .= "
				tempTable as (
					select
						EvnLabRequest_id
					from (
						" . implode('
						union
						', $unionParts) . "
					) elr   
				),
			";

			$from = "
				tempTable as t
				--inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = t.EvnLabRequest_id
				inner join lateral(
					-- здесь связь идет уже с ранее выбранными ид заявок, поэтому выбирать можно не из вьюхи, а из таблицы
					select elr.* 
					from v_EvnLabRequest elr
					where elr.EvnLabRequest_id = t.EvnLabRequest_id
					limit 1
				) elr on true
			";
		}

		if (!empty($data['EvnLabRequest_id'])) {
			$elr_filter .= " and elr.EvnLabRequest_id = :EvnLabRequest_id";
			$params['EvnLabRequest_id'] = $data['EvnLabRequest_id'];
			$datefilter = "";
		}

		$fields = "";
		$join = "";
		$msdistinct = array();
		$msfilter = array();
		$msjoin = array();

		if (!empty($data['MedServiceType_SysNick']) && in_array($data['MedServiceType_SysNick'], array('pzm', 'reglab'))) {
			// для пункта забора и регистрац. службы нужно тянуть названия служб
			$join .= "
				left join lateral(
                	select
                    	string_agg(COALESCE(ms.MedService_Nick,''), '\n') as MedService_Nicks
                    from v_EvnLabSample ls
					inner join v_MedService ms on ms.MedService_id = ls.MedService_id
					where ls.EvnLabRequest_id = elr.EvnLabRequest_id
                ) ELSMSNicks on true
			";
			$fields .= ", substring(ELSMSNicks.MedService_Nicks from 1 for (length(ELSMSNicks.MedService_Nicks)-1)) as \"MedService_Nick\"";
		}

		if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'pzm') {
			// 1. Пункт забора
			// - заявка из данного пункта забора.
			$searchPerson = '';
			if (!empty($data['Person_id'])) {
				$searchPerson = ' and elr.Person_id = :Person_id ';
				$params['Person_id'] = $data['Person_id'];
			}
			$msfilter['MedService_sid'] = $searchPerson . " and elr.MedService_sid = :MedService_id";

			// для пункта забора нужно тянуть номера проб
			$join .= "
				left join lateral(
                	select
                    	string_agg(substring(ls.EvnLabSample_Num from 9 for 4), ', ') as EvnLabSample_Nums
                    from v_EvnLabSample ls
					where ls.EvnLabRequest_id = elr.EvnLabRequest_id and ls.EvnLabSample_Num is not null    
                ) ELSNums on true
			";
			$fields .= ", substring(ELSNums.EvnLabSample_Nums from 1 for (length(ELSNums.EvnLabSample_Nums)-1)) as \"EvnLabRequest_SampleNum\"";
		} else if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'reglab') {
			// 2. Регистрационная служба
			$searchPerson = '';
			if (!empty($data['Person_id'])) {
				$searchPerson = ' and elr.Person_id = :Person_id ';
				$params['Person_id'] = $data['Person_id'];
			}
			$msfilter['MedService_id'] = $searchPerson . " and (elr.MedService_id = :MedService_id or exists (
				select 1
				from v_EvnLabSample els
				where els.EvnLabRequest_id = elr.EvnLabRequest_id
					and els.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL where msl.MedService_id = :MedService_id)
				limit 1	
			))";
		} else {
			// 3. Лаборатория
			$searchPerson = '';
			if (!empty($data['Person_id'])) {
				$searchPerson = ' and elr.Person_id = :Person_id ';
				$params['Person_id'] = $data['Person_id'];
			}
			$msfilter['MedService_id'] = $searchPerson . " and (elr.MedService_id = :MedService_id or exists (
				select 1
				from v_EvnLabSample els
				where els.EvnLabRequest_id = elr.EvnLabRequest_id
					and els.MedService_id = :MedService_id
				limit 1	
			))";
			if (getRegionNick() == 'kz') {
				$fields .= ", 
				case
					when pers.Person_IsInFOMS = 1 then 'orange'
					when pers.Person_IsInFOMS = 2 then 'true'
					else 'false'
				end as \"Person_IsBDZ\"";
				$join .= "
					left join Person pers on pers.Person_id = elr.Person_id
				";
			}
		}

		if (!empty($data['MedServiceLab_id'])) {
			$elr_filter .= "
				and exists(
					select
						els1.EvnLabSample_id
					from
						v_EvnLabSample els1
					where
						els1.EvnLabRequest_id = elr.EvnLabRequest_id
						and els1.MedService_id = :MedServiceLab_id
					limit 1	
				)
			";
			$params['MedServiceLab_id'] = $data['MedServiceLab_id'];
		}

		$allow_encryp = allowPersonEncrypHIV() ? '1' : '0';

		if (getRegionNick() == 'kz') {
			$prehospDirect = "case 
						when ed.PrehospDirect_id in ( 8, 15 ) then COALESCE(LpuSection.LpuSection_Name, Lpu.Lpu_Nick) -- 8 ПСМП -- 15 Отделение МО - Отделение ЛПУ (Если не выбрали то ЛПУ)
						when ed.PrehospDirect_id in ( 9, 11, 16 ) then Lpu.Lpu_Nick -- 9 КДП -- 11 Другой стационар -- Другое МО - Lpu_sid - Направившее ЛПУ
						when ed.PrehospDirect_id in ( 10, 12, 13, 14 ) then Org.Org_nick -- 10 Скорая помощь -- 12 Военкомат -- 13 Роддом -- Org_sid - Направившая организация
						else null
					end as \"PrehospDirect_Name\",";
		} else {
			$prehospDirect = "case 
						when 1 = ed.PrehospDirect_id then COALESCE(LpuSection.LpuSection_Name, Lpu.Lpu_Nick) -- 1 Отделение ЛПУ (Если не выбрали то ЛПУ)
						when 2 = ed.PrehospDirect_id then Lpu.Lpu_Nick -- 2 Другое ЛПУ --Lpu_sid - Направившее ЛПУ
						when ed.PrehospDirect_id in ( 3, 4, 5, 6 ) then Org.Org_nick -- 3 Другая организация -- 4 Военкомат -- 5 Скорая помощь -- 6 Администрация -- Org_sid - Направившая организация
						when 7 = ed.PrehospDirect_id then 'Пункт помощи на дому' --7Пункт помощи на дому
						else COALESCE(LpuSection.LpuSection_Name, Lpu_Nick)
					end as \"PrehospDirect_Name\",";
		}

		try {
			$prequery = "
				select {{add_distinct}}
					elr.EvnDirection_id,
					COALESCE(elr.EvnStatus_id, 1) as EvnStatus_id,
					elr.EvnLabRequest_id,
					elr.Person_id,
					case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
						else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| ps.Person_FirName,'') || COALESCE(' '|| ps.Person_SecName,'') end as Person_FIO,
					case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
						else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') end as Person_ShortFio,
					case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
					elr.UslugaComplex_id,
					ps.Person_SurName as Person_Surname,
					ps.Person_SecName as Person_Secname,
					ps.Person_FirName as Person_Firname,
					to_char(PS.Person_Birthday, 'dd.mm.yyyy') as Person_Birthday,
					elr.EvnLabRequest_UslugaName,
					case when (:pmUser_id = elr.pmUser_insID or elr.EvnLabRequest_setDT is null) then 1 else 0 end as canEdit,
					elr.UslugaExecutionType_id,
					elr.EvnLabRequest_LabSampleNums as EvnLabSample_ids,
					elr.EvnLabRequest_BarCodes as EvnLabRequest_FullBarCode,
					to_char (elr.EvnLabRequest_prmTime, 'HH24:MI dd.mm.yyyy') as TimetableMedService_begTime,
					elr.EvnLabRequest_IsOutNorm as EvnLabSample_IsOutNorm,
					COALESCE(elr.EvnLabRequest_UslugaCount, 0) as EvnLabRequest_Tests,
					COALESCE(SST.SampleStatusType_SysNick, 'notall') as ProbaStatus,
					elr.MedService_id,
					elr.MedService_sid,
					elr.EvnLabRequest_RegNum,
					elr.EvnLabRequest_prmTime,
					elr.EvnLabRequest_statusDate,
					elr.EvnLabRequest_updDT,
					elr.EvnLabRequest_IsProtocolPrinted	
				from
					{$from}
					left join v_SampleStatusType sst on sst.SampleStatusType_id = elr.SampleStatusType_id
					left join v_PersonState ps on elr.Person_id = ps.Person_id
					left join v_PersonEncrypHIV PEH on PEH.Person_id = ps.Person_id
			";

			$prequeryAll = "";
			foreach ($msfilter as $key => $onemsfilter) {
				if (!empty($prequeryAll)) {
					$prequeryAll .= " union all ";
				}
				$prequeryAll .= str_replace("{{add_distinct}}", (!empty($msdistinct[$key]) ? 'distinct' : ''), $prequery) . " " . (!empty($msjoin[$key]) ? $msjoin[$key] : "") . " where {$elr_filter} {$datefilter} {$onemsfilter}";
			}

			$query = "
				{$tmpQuery}	elr as (
					{$prequeryAll}
				)
				
				Select
					elr.EvnDirection_id as \"EvnDirection_id\",
					elr.EvnStatus_id as \"EvnStatus_id\",
					elr.EvnLabRequest_id as \"EvnLabRequest_id\",
					ed.EvnDirection_pid as \"EvnDirection_pid\",
					elr.Person_id as \"Person_id\",
					elr.Person_FIO as \"Person_FIO\",
					elr.Person_ShortFio as \"Person_ShortFio\",
					elr.Person_Surname as \"Person_Surname\",
					elr.Person_Secname as \"Person_Secname\",
					elr.Person_Firname as \"Person_Firname\",
					elr.Person_Birthday as \"Person_Birthday\",
					case when exists(
						select * 
						from v_PersonQuarantine PQ
						where PQ.Person_id = elr.Person_id 
						and PQ.PersonQuarantine_endDT is null
					) then 2 else 1 end as \"PersonQuarantine_IsOn\",
					elr.EvnLabRequest_IsProtocolPrinted as \"EvnLabRequest_IsProtocolPrinted\",
					elr.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
					ed.Lpu_sid as \"Lpu_sid\",
					ed.Org_sid as \"Org_sid\",
					ed.LpuSection_id as \"LpuSection_id\",
					elr.UslugaComplex_id as \"UslugaComplex_id\",
					elr.EvnLabRequest_UslugaName as \"EvnLabRequest_UslugaName\",
					elr.canEdit as \"canEdit\",
					elr.UslugaExecutionType_id as \"UslugaExecutionType_id\",
					case when ed.EvnDirection_IsCito = 2 then '!' else '' end as \"EvnDirection_IsCito\",
					to_char (ed.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
					ed.PrehospDirect_id as \"PrehospDirect_id\",
					{$prehospDirect}
					ed.EvnDirection_Num as \"EvnDirection_Num\",
					elr.EvnLabSample_ids as \"EvnLabSample_ids\",
					elr.EvnLabRequest_FullBarCode as \"EvnLabRequest_FullBarCode\",
					elr.TimetableMedService_begTime as \"TimetableMedService_begTime\",
					LpuSection.LpuSection_Code as \"LpuSection_Code\",
					LpuSection.LpuSection_Name as \"LpuSection_Name\",
					elr.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
					elr.EvnLabRequest_Tests as \"EvnLabRequest_Tests\",
					elr.ProbaStatus as \"ProbaStatus\",
					case when COALESCE(elr.EvnStatus_id, 1) <= 2 and eup.cnt >= 1 then 1 else 0 end as \"needTestMenu\",
					elr.MedService_id as \"MedService_id\",
					elr.MedService_sid as \"MedService_sid\",
					ms.MedService_Nick as \"MedService_Nick\",
					to_char(elr.EvnLabRequest_prmTime,'dd.mm.yyyy') as \"TimetableMedService_Date\",
					elr.EvnLabRequest_RegNum as \"EvnLabRequest_RegNum\",
					MPPS.Person_SurName as \"EDMedPersonalSurname\",
					lpu.Lpu_Nick as \"Lpu_Nick\"
					{$fields}
				from
					elr
					inner join lateral(
						Select
							ed.EvnDirection_id, ed.EvnQueue_id, ed.LpuSection_id, ed.Lpu_sid, ed.Org_sid,
							ed.EvnDirection_IsCito, ed.EvnDirection_pid, ed.EvnDirection_setDate,
							ed.PrehospDirect_id, ed.EvnDirection_Num,
							ed.MedPersonal_id, ed.MedStaffFact_id
						from
							v_EvnDirection_all ed
							--left join v_EvnQueue eq on eq.EvnDirection_id = ed.EvnDirection_id
						where
							ed.dirtype_id in (10, 25) and ed.EvnDirection_id = elr.EvnDirection_id
							and ed.DirFailType_id is null 
							--and eq.QueueFailCause_id is null
							and (ELR.EvnStatus_id IN (3,4) OR COALESCE(ED.EvnStatus_id, 1) not in (12,13)) -- отменённые направление отображать только с выполненными/одобренными заявками
						limit 1
                    ) ed on true
					left join lateral(
						select
							count(eup.EvnUslugaPar_id) as cnt
						from
							v_EvnUslugaPar eup
						where
							eup.EvnDirection_id = ed.EvnDirection_id
					) eup on true -- придётся запрашивать количество услуг, чтобы поянть надо ли выводить меню с тестами
					--left join v_EvnQueue eq on eq.EvnQueue_id = ed.EvnQueue_id
					left join v_LpuSection LpuSection on LpuSection.LpuSection_id = ed.LpuSection_id
					left join v_Lpu Lpu on Lpu.Lpu_id = ed.Lpu_sid
					left join v_Org Org on Org.Org_id = ed.Org_sid
					left join v_MedService ms on ms.MedService_id = elr.MedService_id
					left join lateral(
						select 
							Person_id 
						from 
							dbo.MedPersonalCache 
						where 
							MedPersonal_id = ed.MedPersonal_id 
							and cast(dbo.tzgetdate() as date) between WorkData_begDate and coalesce(WorkData_endDate, dbo.tzgetdate())
						limit 1
					) MP on true
					left join dbo.v_PersonState as MPPS on MP.Person_id=MPPS.Person_id
					{$join}
				where
					{$ed_filter}
					and case when (select MedService_IsThisLPU from v_MedService where MedService_id = :MedService_id) = '2' then ed.Lpu_sid else :Lpu_id end = :Lpu_id
			";

// 			echo getDebugSQL($query, $params); die();
			$response = $this->queryResult($query, $params);
			
			
			
			if (!is_array($response)) { return false; }

			$result = array();

			foreach($response as &$resp) {

				if (!empty($resp['MedService_Nick'])) {
					$resp['MedService_Nick'] = nl2br($resp['MedService_Nick']);
				}

				// свой массив если включена ЭО
				if (!empty($data['ElectronicService_id'])) {
					$result[$resp['EvnDirection_id']] = $resp;
				}
			}

			if (!empty($data['ElectronicService_id'])) {

				// получаем список направлений
				$dirList = array_column($response,'EvnDirection_id');

				// получаем по ним связанные данные талонов ЭО
				$electronicQueueGridData = $this->common->POST('ElectronicTalon/getGridElectronicQueueData', array(
					'DirectionList' => $dirList,
					'ElectronicTalon_Num' => !empty($data['ElectronicTalon_Num']) ? $data['ElectronicTalon_Num'] : null,
					'ElectronicTalonPseudoStatus_id' => !empty($data['ElectronicTalonPseudoStatus_id']) ? $data['ElectronicTalonPseudoStatus_id'] : null,
				),'list', true);

				$eq_result = array();

				if ($this->isSuccessful($electronicQueueGridData)) {
					foreach ($electronicQueueGridData as $talonData) {
						if (isset($result[$talonData['EvnDirection_id']])) {
							// объединяем с данными по ЭО
							$eq_result[] = array_merge($result[$talonData['EvnDirection_id']], $talonData);
						}
					}

					unset($result);
					$response = $eq_result;
				}
			}

			return $response;
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Функция возвращает:
	 * sample_count - количество проб по заявке
	 * test_count - количество тестов всех проб по заявке
	 * approved_count - количество одобренных тестов всех проб заявки
	 * Для определения необходимости проставления заявке поля "Выполнение услуги" в значение "Полное"
	 */
	function countTests($data)
	{
		$query = "
			select 
				count(distinct els.EvnLabSample_id) as \"sample_count\",
				count(*) as \"test_count\",
				count(case when (ut.UslugaTest_ResultApproved = 2) then ut.UslugaTest_id else null end) as \"approved_count\",
				count(distinct case when els.LabSampleStatus_id = 5 then els.EvnLabSample_id else null end) as \"sample_bad_count\"
			from
				v_EvnLabSample els
				left join v_UslugaTest ut on ut.EvnLabSample_id = els.EvnLabSample_id
			where
				els.EvnLabRequest_id = :EvnLabRequest_id
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$r = $result->result('array');
			if (is_array($r) && (count($r) > 0) && is_array($r[0])) {
				$counts = $r[0];
				// сразу выясним все ли тесты одобрены
				$counts['request_approved'] = ($counts['test_count'] == $counts['approved_count'] && $counts['approved_count'] > 0) ? true : false;
				return $counts;
			}
		}
		return array('sample_count' => 0, 'test_count' => 0, 'approved_count' => 0, 'sample_bad_count' => 0, 'request_approved' => false);
	}

	/**
	 *  Загружает состав услуг в меню
	 * @throws Exception
	 */
	function loadCompositionMenu($data, $takeLab = true)
	{
		$filter = "(1=1)";

		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		$response = array();

		$lrdata = $this->queryResult("
			select
				Lpu_id as \"Lpu_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				MedService_id as \"MedService_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnLabRequest
			where
				EvnDirection_id = :EvnDirection_id
			limit 1	
		", $data);
		if (!is_array($lrdata)) {
			return false;
		}
		if (count($lrdata) == 0) {
			return $response;
		}

		$data['EvnLabRequest_id'] = $lrdata[0]['EvnLabRequest_id'];
		$data['MedService_id'] = $lrdata[0]['MedService_id'];
		$data['UslugaComplex_id'] = $lrdata[0]['UslugaComplex_id'];
		$data['EvnDirection_id'] = $lrdata[0]['EvnDirection_id'];
		$probes = $this->EvnLabSample_model->loadLabSampleFrame($data);
		if (!is_array($probes)) {
			return false;
		}
		foreach ($probes as $probe) {
			// достаём состав каждой пробы
			$tests = $this->EvnLabSample_model->getLabSampleResultGrid(array(
				'Lpu_id' => $lrdata[0]['Lpu_id'],
				'RefSample_id' => $probe['RefSample_id'],
				'EvnLabSample_id' => $probe['EvnLabSample_id'],
				'EvnDirection_id' => $lrdata[0]['EvnDirection_id']
			));

			foreach ($tests as $test) {
				$response[] = array(
					'UslugaComplex_id' => $test['UslugaComplex_id'],
					'UslugaComplex_InRequest' => ($test['UslugaTest_Status'] != 'Не назначен') ? 1 : 0,
					'UslugaComplex_Code' => $test['UslugaComplex_Code'],
					'UslugaComplex_Name' => $test['UslugaComplex_Name']
				);
			}
		}

		return $response;
	}

	/**
	 * Сохранение назначения теста для услуги/пробы
	 */
	function saveEvnLabRequestUslugaComplex($data)
	{
		if (empty($data['EvnLabSample_id'])) {
			return $this->createError('', 'Не указан id пробы. Обратитесь к разработчкам');
		}
		if (empty($data['EvnUslugaPar_id'])) {
			return $this->createError('', 'Не указан id исследования. Обратитесь к разработчкам');
		}

		$params = array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
            	EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\",
            	error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
            from p_EvnLabRequestUslugaComplex_ins(
				EvnLabRequest_id := :EvnLabRequest_id,
				EvnLabSample_id := :EvnLabSample_id,
				EvnUslugaPar_id := :EvnUslugaPar_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id
            )
		";
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('', 'Ошибка при сохранении теста');
		}

		collectEditedData('ins', 'EvnLabRequestUslugaComplex', $result[0]['EvnLabRequestUslugaComplex_id']);
		return $result;
	}

	/**
	 * Удаление назначения теста для услуги/пробы
	 * @param $data
	 */
	function deleteEvnLabRequestUslugaComplex($data)
	{
		$resp = $this->execCommonSP('p_EvnLabRequestUslugaComplex_del', array(
			'EvnLabRequestUslugaComplex_id' => $data['EvnLabRequestUslugaComplex_id'],
		));
		if (!is_array($resp)) {
			return $this->createError('', 'Ошибка запроса p_EvnLabRequestUslugaComplex_del');
		}
		collectEditedData('del', 'EvnLabRequestUslugaComplex', $data['EvnLabRequestUslugaComplex_id']);
		return $resp;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function ReCacheEvnUslugaPar($data)
	{
		$params = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
		);
		$uslugalist = json_decode($data['uslugaList']);
		$evnLabRequestList = array();
		$EvnLabRequest_id = null;
		$EvnLabSample_id = null;

		$result = $this->queryResult("
			select 
				r.EvnLabRequest_id as \"EvnLabRequest_id\",
				u.EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\",
				u.UslugaComplex_id as \"UslugaComplex_id\",
				coalesce(o.EvnLabSample_id, els.EvnLabSample_id) as \"EvnLabSample_id\"
			from v_EvnUslugaPar o
				inner join v_EvnLabRequest r on r.EvnDirection_id = o.EvnDirection_id
				inner join v_EvnLabSample els on els.EvnLabRequest_id = r.EvnLabRequest_id
				left join v_EvnLabRequestUslugaComplex u on u.EvnLabRequest_id = r.EvnLabRequest_id
			where
				o.EvnUslugaPar_id = :EvnUslugaPar_id and r.EvnStatus_id = 1
		", $params);
		if (!is_array($result)) {
			return $this->createError('', 'Ошибка при получении данных тестов');
		}

		foreach ($result as $row) {
			$EvnLabRequest_id = $row['EvnLabRequest_id'];
			$EvnLabSample_id = $row['EvnLabSample_id'];
			if (empty($evnLabRequestList[$EvnLabRequest_id])) {
				$evnLabRequestList[$EvnLabRequest_id] = array(
					'insUslugaComplexIds' => array(),
					'delIds' => array(),
					'savedUslugaComplexIds' => array(),
				);
			}
			if (in_array($row['UslugaComplex_id'], $uslugalist)) {
				$evnLabRequestList[$EvnLabRequest_id]['savedUslugaComplexIds'][] = $row['UslugaComplex_id'];
			} else {
				$evnLabRequestList[$EvnLabRequest_id]['delIds'][] = $row['EvnLabRequestUslugaComplex_id'];
			}
		}

		if (isset($EvnLabSample_id) && isset($EvnLabRequest_id) && isset($evnLabRequestList[$EvnLabRequest_id])) {
			foreach ($uslugalist as $UslugaComplex_id) {
				if (!in_array($UslugaComplex_id, $evnLabRequestList[$EvnLabRequest_id]['savedUslugaComplexIds'])) {
					$evnLabRequestList[$EvnLabRequest_id]['insUslugaComplexIds'][] = $UslugaComplex_id;
				}
			}
			foreach ($evnLabRequestList[$EvnLabRequest_id]['delIds'] as $id) {
				$resp = $this->deleteEvnLabRequestUslugaComplex(array(
					'EvnLabRequestUslugaComplex_id' => $id,
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
			foreach ($evnLabRequestList[$EvnLabRequest_id]['insUslugaComplexIds'] as $id) {
				$resp = $this->saveEvnLabRequestUslugaComplex(array(
					'EvnLabRequest_id' => $EvnLabRequest_id,
					'EvnLabSample_id' => $EvnLabSample_id,
					'EvnUslugaPar_id' => $params['EvnUslugaPar_id'],
					'UslugaComplex_id' => $id,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * Сохранение состава заявки
	 */
	function saveEvnLabRequestContent($data)
	{
		$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
			select
				EvnLabRequest_id as \"EvnLabRequest_id\"
			from
				v_EvnLabRequest
			where
				EvnDirection_id = :EvnDirection_id
			limit 1	
		", $data);

		if (empty($data['EvnLabRequest_id'])) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. исследование ещё не сохранено');
		}

		// проверяем статус проб в заявке
		$EvnLabSample_id = $this->getFirstResultFromQuery("
			select
				EvnLabSample_id as \"EvnLabSample_id\"
			from
				v_EvnLabSample
			where
				EvnLabRequest_id = :EvnLabRequest_id
				and COALESCE(LabSampleStatus_id, 1) <> 1
			limit 1	
		", $data);

		if (!empty($EvnLabSample_id)) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. есть пробы не в статусе "Новая"');
		}

		// должна быть одна проба в заявке
		$EvnLabSamples = $this->queryResult("
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_setDT as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample
			where
				EvnLabRequest_id = :EvnLabRequest_id
		", $data);

		if (empty($EvnLabSamples[0]['EvnLabSample_id'])) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. в заявке не найдено ни одной пробы');
		} else if (count($EvnLabSamples) > 1) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. в заявке больше одной пробы');
		}

		$data['EvnLabSample_id'] = $EvnLabSamples[0]['EvnLabSample_id'];

		// должна быть одна услуга в заявке
		$EvnUslugaPars = $this->queryResult("
			select
				EvnUsluga_id as \"EvnUslugaPar_id\"
			from
				v_EvnUsluga
			where
				EvnDirection_id = :EvnDirection_id
		", $data);

		if (empty($EvnUslugaPars[0]['EvnUslugaPar_id'])) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. в заявке не найдено ни одного исследования');
		} else if (count($EvnUslugaPars) > 1) {
			return array('Error_Msg' => 'Нельзя изменить состав исследования, т.к. в заявке больше одного исследования');
		}

		$data['EvnUslugaPar_id'] = $EvnUslugaPars[0]['EvnUslugaPar_id'];

		$UslugaComplex_ids = array();

		// сохраняем список услуг выбранных в составе исследования
		if (!empty($data['UslugaComplexContent_ids'])) {
			$UslugaComplexContent_ids = json_decode($data['UslugaComplexContent_ids'], true);
			if (!empty($UslugaComplexContent_ids)) {
				// 1. удаляем услуги которые не были отмечены
				$query = "
					delete from
						v_EvnLabRequestUslugaComplex
					where
						EvnLabRequest_id = :EvnLabRequest_id
						and UslugaComplex_id not in (" . implode(',', $UslugaComplexContent_ids) . ")
				";

				$this->db->query($query, array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id']
				));

				// 2. добавляем новые услуги
				$added_array = array();

				$query = "
					select
						UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_EvnLabRequestUslugaComplex
					where
						EvnLabRequest_id = :EvnLabRequest_id
				";

				$result = $this->db->query($query, array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id']
				));

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach ($resp as $respone) {
						$added_array[] = $respone['UslugaComplex_id'];
					}
				}

				foreach ($UslugaComplexContent_ids as $UslugaComplexContent_id) {
					if (!in_array($UslugaComplexContent_id, $added_array)) {
						$added_array[] = $UslugaComplexContent_id;

						$this->saveEvnLabRequestUslugaComplex(array(
							'EvnLabRequest_id' => $data['EvnLabRequest_id'],
							'UslugaComplex_id' => $UslugaComplexContent_id,
							'EvnLabSample_id' => $data['EvnLabSample_id'],
							'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				$UslugaComplex_ids = array_merge($UslugaComplexContent_ids, $added_array);
			}
		}

		// если проба уже взята
		if (!empty($EvnLabSamples[0]['EvnLabSample_setDT'])) {
			// надо удалить тесты из пробы, которых нет в выбранном составе
			$query = "
				select
					ut.UslugaTest_id as \"UslugaTest_id\"
				from
					v_EvnUslugaPar eup
					inner join v_UslugaTest ut on ut.UslugaTest_pid = eup.EvnUslugaPar_id
				where
					ut.UslugaComplex_id not in (
						select distinct
							UslugaComplex_id
						from
							v_EvnLabRequestUslugaComplex elruc
						where
							elruc.EvnUslugaPar_id = eup.EvnUslugaPar_id
					)
					and eup.EvnUslugaPar_id = :EvnUslugaPar_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$q = "
						select 
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from dbo.p_UslugaTest_del(
						    UslugaTest_id := :UslugaTest_id,
						    pmUser_id := :pmUser_id
						)
					";
					$this->db->query($q, array(
						'UslugaTest_id' => $respone['UslugaTest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			// надо досохранить в пробе тесты, которые выбраны в составе исследования
			if (!empty($UslugaComplex_ids)) {
				$tests = array();
				foreach ($UslugaComplex_ids as $UslugaComplex_id) {
					$tests[] = array(
						'UslugaTest_pid' => $data['EvnUslugaPar_id'],
						'UslugaComplex_id' => $UslugaComplex_id
					);
				}
				$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

				$query = "
					select
						els.EvnLabSample_id as \"EvnLabSample_id\",
						els.RefSample_id as \"RefSample_id\",
						elr.EvnDirection_id as \"EvnDirection_id\",
						els.MedService_id as \"MedService_id\",
						elr.Person_id as \"Person_id\",
						els.EvnLabSample_setDT as \"EvnLabSample_setDT\",
						elr.PersonEvn_id as \"PersonEvn_id\",
						elr.Server_id as \"Server_id\",
						ms.Lpu_id as \"Lpu_id\",
						els.Analyzer_id as \"Analyzer_id\",
						ms.LpuSection_id as \"LpuSection_id\",
						elr.PayType_id as \"PayType_id\"
					from
						v_EvnLabSample els
						inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
						inner join v_MedService ms on ms.MedService_id = elr.MedService_id
					where
						els.EvnLabRequest_id = :EvnLabRequest_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resps = $result->result('array');
					foreach ($resps as $resp) {
						$data['EvnLabSample_id'] = $resp['EvnLabSample_id'];
						$data['RefSample_id'] = $resp['RefSample_id'];
						$lrdata = array(
							'EvnDirection_id' => $resp['EvnDirection_id'],
							'MedService_id' => $resp['MedService_id'],
							'Person_id' => $resp['Person_id'],
							'PersonEvn_id' => $resp['PersonEvn_id'],
							'Server_id' => $resp['Server_id'],
							'Lpu_id' => $resp['Lpu_id'],
							'LpuSection_id' => $resp['LpuSection_id'],
							'PayType_id' => $resp['PayType_id'],
							'MedPersonal_id' => $data['session']['medpersonal_id'],
						);
						$data['Analyzer_id'] = $resp['Analyzer_id'];
						$data['EvnLabSample_setDT'] = $resp['EvnLabSample_setDT'];
						// 2. сохраняем нужные тесты
						$data['ingorePrescr'] = true;
						$this->EvnLabSample_model->saveLabSampleTests($data, $lrdata, $tests);
					}
				}
			}
		}

		// кэшируем количество тестов
		$this->ReCacheLabRequestUslugaCount(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * Удаление пустых проб заявки
	 */
	function deleteEmptySamples($data)
	{

		$query = "
			select 1
			from v_evnlabrequest
			where evnlabrequest_id = :EvnLabRequest_id
			limit 1
		";
		$check = $this->db->query($query, $data);
		$check = $check->result('array');
		if (empty($check[0]))
			return array('Error_Msg' => '');


		// надо удалить все пробы, оставщиеся без единого исследования
		$labsamples = $this->queryResult("
			select
				els.EvnLabSample_id as \"EvnLabSample_id\"
			from
				v_EvnLabSample els
			where
				els.EvnLabRequest_id = :EvnLabRequest_id
				and not exists( -- нет сохранённых тестов
					select eup.EvnUslugaPar_id from v_EvnUslugaPar eup where eup.EvnLabSample_id = els.EvnLabSample_id limit 1
				)
				and not exists( -- нет назначенных тестов
					select elruc.EvnLabRequestUslugaComplex_id from v_EvnLabRequestUslugaComplex elruc where elruc.EvnLabSample_id = els.EvnLabSample_id limit 1
				)
		", array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));

		foreach ($labsamples as $labsample) {
			$q = "
				select 
                	error_code as \"Error_Code\",
                	error_message as \"Error_Msg\"
                from p_EvnLabSample_del(
                	EvnLabSample_id := :EvnLabSample_id,
                    pmUser_id := :pmUser_id
                )
			";
			$r = $this->db->query($q, array(
				'EvnLabSample_id' => $labsample['EvnLabSample_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			collectEditedData('delete', 'EvnLabSample', $labsample['EvnLabSample_id']);
		}

		// кэшируем статус заявки
		$this->ReCacheLabRequestStatus(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем названия услуг
		$this->ReCacheLabRequestUslugaName(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем статус проб в заявке
		$this->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * @return array|false
	 */
	function getLabSampleResultList()
	{
		$LabSampleResultList = array();
		foreach ($this->EvnLabSample->getItems() as $item) {
			$resp = $this->EvnLabSample->getLabSampleResultGrid(array(
				'EvnLabSample_id' => $item['EvnLabSample_id'],
				'RefSample_id' => $item['RefSample_id'],
				'Lpu_id' => $this->getField('Lpu_id'),
				'EvnUslugaPar_pid' => $this->EvnUslugaPar_oid
			));
			if (!is_array($resp)) {
				return false;
			}
			$LabSampleResultList = array_merge($LabSampleResultList, $resp);
		}
		return $LabSampleResultList;
	}

	/**
	 * @return array
	 */
	function saveEvnXml()
	{
		$LabSampleResultList = $this->getLabSampleResultList();
		if ($LabSampleResultList === false) {
			return $this->createError('', 'Ошибка при получении проб');
		}

		if ($this->usePostgreLis) {
			if (count($LabSampleResultList) > 0) {
				$resp = $this->common->POST('EvnXml/EvnLabRequest', array(
					'EvnUslugaPar_oid' => $this->EvnUslugaPar_oid,
					'EvnLabRequest_Comment' => $this->EvnLabRequest_Comment,
					'LabSampleResultList' => $LabSampleResultList
				), 'single');
				if (!$this->isSuccessful($resp)) {
					return array($resp);
				}
			}
		} else {
			if (count($LabSampleResultList) > 0) {
				$this->load->model('EvnXmlBase_model');
				$resp = $this->EvnXmlBase_model->processingEvnLabRequest(array(
					'EvnUslugaPar_oid' => $this->EvnUslugaPar_oid,
					'EvnLabRequest_Comment' => $this->EvnLabRequest_Comment,
					'LabSampleResultList' => $LabSampleResultList,
					'session' => $this->sessionParams
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * аргументы хранимых функций sqlserver и postgre различаются
	 * в общем случае parent::save() пока не работает
	 */
	function onSave()
	{
		if ($this->validate()->isValid()) {
			//$proc = $this->chooseSaveProcedure();
			if (empty($this->EvnLabRequest_id))
				$proc = "p_evnlabrequest_ins";
			else $proc = "p_evnlabrequest_upd";
			$params = [];
			$query = "
			select
				evnlabrequest_id as \"EvnLabRequest_id\",
				error_message as \"Error_Msg\",
				error_code as \"Error_Code\"
			from {$proc}(
				EvnLabRequest_id := :EvnLabRequest_id,
				EvnLabRequest_pid := :EvnLabRequest_pid,
				EvnLabRequest_RegNum := :EvnLabRequest_RegNum,
				--EvnLabRequest_rid := :EvnLabRequest_rid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnLabRequest_setDT := :EvnLabRequest_setDT,
				EvnLabRequest_disDT := :EvnLabRequest_disDT,
				EvnLabRequest_didDT := :EvnLabRequest_didDT,
				--Evn_insDT := :EvnLabRequest_insDT, вычисляются в хранимых процедурах
				--Evn_updDT := :EvnLabRequest_updDT,
				--Evn_Index := :EvnLabRequest_Index,
				--Evn_Count := :EvnLabRequest_Count,
				Morbus_id := :Morbus_id,
				EvnLabRequest_IsSigned := :EvnLabRequest_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnLabRequest_signDT := :EvnLabRequest_signDT,
				EvnStatus_id := :EvnStatus_id,
				EvnLabRequest_statusDate := :EvnLabRequest_statusDate,
				EvnDirection_id := :EvnDirection_id,
				UslugaExecutionType_id := :UslugaExecutionType_id,
				EvnLabRequest_Comment := :EvnLabRequest_Comment,
				PayType_id := :PayType_id,
				UslugaComplex_id := :UslugaComplex_id,
				EvnLabRequest_Ward := :EvnLabRequest_Ward,
				MedService_id := :MedService_id,
				EvnLabRequest_BarCode := :EvnLabRequest_BarCode,
				EvnLabRequest_IsCito := :EvnLabRequest_IsCito,
				MedService_sid := :MedService_sid,
				EvnLabRequest_prmTime := :EvnLabRequest_prmTime,				
				Mes_id := :Mes_id,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				pmUser_id := :pmUser_id
			)
			";
			foreach ($this->fields as $key => $value) {
				$params[$key] = $value;
			}

			try {
				//echo getDebugSQL($query, $params);exit;
				$result = $this->db->query($query, $params);

				if (is_object($result)) {
					$result = $result->result('array');
					if (isset($result[0])) {
						$keyField = $this->getKeyFieldName();
						$this->__set($keyField, $result[0][$keyField]);
						collectEditedData(empty($params[$keyField]) ? 'ins' : 'upd', 'EvnLabRequest', $result[0][$keyField]);
					}
				} else {
					$result = array(
						0 => array(
							$this->getKeyFieldName() => null,
							'Error_Code' => null,
							'Error_Msg' => 'При сохранении произошли ошибки'
						)
					);
				}
				$save_ok = $this::save_ok($result);
				if (!$save_ok) {
					log_message('error', 'query error: ' . $query . ' params' . var_export($params, true));
				}
			} catch (Exception $e) {
				$result = array(
					0 => array(
						$this->getKeyFieldName() => null,
						'Error_Code' => null,
						'Error_Msg' => 'При вызове процедуры сохранения произошли ошибки: ' . str_replace(chr(13), ' ', str_replace(chr(10), '<br> ', $e->getCode() . ' ' . $e->getMessage()))
					)
				);
			}
		} else {
			$result = array(
				0 => array(
					$this->getKeyFieldName() => null,
					'Error_Code' => null,
					'Error_Msg' => 'При сохранении произошли ошибки: <br/>' . implode('<br/>', $this->getErrors())
				)
			);
		}
		return $result;
	}


	/**
	 * @return array
	 */
	function save($data = array())
	{
		if (empty($data['ignoreCheckPayType']) && !empty($this->EvnLabRequest_id) && !empty($this->PayType_id)) {
			// проверяем тип оплаты в исследованиях, если отличается то предлагаем обновить.
			$resp_eup = $this->queryResult("
				select
					eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					pt.PayType_Name as \"PayType_Name\"
				from
					v_EvnUslugaPar eup
					inner join v_EvnLabRequest elr on elr.EvnDirection_id = eup.EvnDirection_id
					left join v_PayType pt on pt.PayType_id = :PayType_id
				where
					elr.EvnLabRequest_id = :EvnLabRequest_id
					and eup.PayType_id <> :PayType_id
				limit 1	
			", array(
				'EvnLabRequest_id' => $this->EvnLabRequest_id,
				'PayType_id' => $this->PayType_id
			));

			if (!empty($resp_eup[0]['EvnUslugaPar_id'])) {
				return array(array(
					'Error_Msg' => 'YesNo',
					'Error_Code' => 101,
					'Alert_Msg' => 'Одно или несколько исследований имеют другой вид оплаты. Изменить вид оплаты исследований на ' . $resp_eup[0]['PayType_Name'] . '?'
				));
			}
		}

		// если направления ещё нет или если не автоматическое и своя МО.
		if (empty($this->EvnDirection_id) || ($this->EvnDirection_IsAuto != '2' && $this->EvnDirection_PrehospDirect_id == 1)) {
			$this->saveEvnDirection($data);
		}

		/** Обновляем дату направления refs #PROMEDWEB-6681 */
		if (!empty($this->EvnDirection_id) && !empty($data['EvnDirection_setDT'])) {
			$this->db->query("
				update Evn
				set Evn_setDT = :EvnDirection_setDT
				where Evn_id = :EvnDirection_id
			",
			[
				'EvnDirection_setDT' => $data['EvnDirection_setDT'],
				'EvnDirection_id' => $this->EvnDirection_id
			]);
		}

		if ($this->getRegionNick() == 'ufa' && !empty($this->EvnDirection_id) && ($data['HIVContingentTypeFRMIS_id'] || $data['HormonalPhaseType_id'])) {
			$this->load->model('PersonDetailEvnDirection_model', 'edpdmodel');
			$data['EvnDirection_id'] = $this->EvnDirection_id;
			$data['PersonDetailEvnDirection_id'] = $data['PersonDetailEvnDirection_id'] ?? 0;
			$pdresponse = $this->edpdmodel->doSave($data);
		}

		// для 1 EvnDirection_id всегда должна быть 1 заявка EvnLabRequest_id
		if (!empty($this->EvnDirection_id)) {
			$this->EvnLabRequest_id = $this->getFirstResultFromQuery("
				SELECT
					EvnLabRequest_id as \"EvnLabRequest_id\"
				FROM
					v_EvnLabRequest
				WHERE
					EvnDirection_id = :EvnDirection_id
				limit 1
				", array('EvnDirection_id' => $this->EvnDirection_id));
			if (empty($this->EvnLabRequest_id)) {
				$this->EvnLabRequest_id = null;
			}

			$this->load->swapi('common');
			$TimetableMedService = $this->common->GET('TimetableMedService', array(
				'EvnDirection_id' => $this->EvnDirection_id
			), 'single');
			if (!$this->isSuccessful($TimetableMedService)) {
				return array($TimetableMedService);
			}

			if (!empty($TimetableMedService)) {
				$this->EvnLabRequest_prmTime = $TimetableMedService['TimetableMedService_begTime'];
			} else {
				$this->EvnLabRequest_prmTime = null;
			}
		}

		if (!empty($this->EvnLabRequest_id)) {
			if (!empty($this->UslugaExecutionType_id) && in_array($this->UslugaExecutionType_id, array(1, 2))) {
				// должен быть хотя бы 1 одобренный результат в заявке
				$counts = $this->countTests(array('EvnLabRequest_id' => $this->EvnLabRequest_id)); // приходит массив c данными
				if ($counts['approved_count'] == 0) {
					return array(array('Error_Msg' => 'Нельзя выставить "Полное" или "Частичное" выполнение заявке, т.к. в заявке отсутствуют одобренные результаты'));
				}
			}

			$result = $this->db->query("
				select
				    PayType_id as \"PayType_id\",
					EvnStatus_id as \"EvnStatus_id\",
					to_char (EvnLabRequest_statusDate, 'YYYY-MM-DD HH24:MI:SS') as \"EvnLabRequest_statusDate\"
				from
					v_EvnLabRequest
				where
					EvnLabRequest_id = :EvnLabRequest_id
			", array(
				'EvnLabRequest_id' => $this->EvnLabRequest_id
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0])) {
					$this->EvnStatus_id = $resp[0]['EvnStatus_id'];
					$this->EvnLabRequest_statusDate = $resp[0]['EvnLabRequest_statusDate'];
					//если нажали что не сохраняем новый тип оплаты то ставим старый
					if (!empty($data['ignoreCheckPayType']) && $data['ignoreCheckPayType'] == 1) {
						$this->PayType_id = $resp[0]['PayType_id'];
					}
				}
			}
		}
		// var_dump($this->EvnLabRequest_statusDate);die();

		//перенесено из parent::save() в $this->>onSave()
		$response = $this->onSave();
		if (self::save_ok($response)) {
			if ( $this->EvnDirection_IsAuto == 2 ) {
                $updParams = [];

		        switch ((int)$this->EvnDirection_PrehospDirect_id) {
                    case 1: // 1 Отделение ЛПУ
					case 15: // Казахстан
                    case 8: // 8 ПСМП Казахстан
                        $updParams['Lpu_sid'] = $this->Lpu_id; //Направившее ЛПУ
                        $updParams['LpuSection_id'] = $this->EvnDirection_LpuSection_id;
                        $updParams['MedStaffFact_id'] = $this->EvnDirection_MedStaffFact_id;
                        //$updParams['Org_sid'] = $this->Lpu_id;
						$updParams['Org_sid'] = $this->getFirstResultFromQuery("
							SELECT
								Org_id as \"Org_id\"
							FROM
								v_Lpu
							WHERE
								Lpu_id = :Lpu_id
							LIMIT 1	
						",array(
							'Lpu_id' => $this->Lpu_id
						));
						if ( !empty($data['MedStaffFact_id']) ) {
							$tmp = $this->db->query(
								"select Post_id as \"Post_id\",MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1",
								[ 'MedStaffFact_id' => $data['MedStaffFact_id'] ]
							)->result('array');
							if ( !empty($tmp[0]) ) {
								$updParams['Post_id'] = $tmp[0]['Post_id'];
								$updParams['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
							}
						}
                        break;
                    case 2: // 2 Другое ЛПУ
					case 16: // Казахстан
                    case 9: // 9 Казахстан КДП
                        // по  $this->EvnDirection_Org_sid получаем Lpu_id
                        $Lpu_id = $this->getFirstResultFromQuery("
                            SELECT
                                Lpu_id as \"Lpu_id\"
                            FROM
                                v_Lpu
                            WHERE
                                Org_id = :Org_sid
                            LIMIT 1	
                        ",array(
                            'Org_sid' => $this->EvnDirection_Lpu_sid
                        ));
                        $updParams['Lpu_sid'] = empty($Lpu_id)?$data['Lpu_id']:$Lpu_id;//Направившее ЛПУ
                        $updParams['LpuSection_id'] = $data['LpuSection_id'];
                        $updParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
                        $updParams['Org_sid'] = $this->EvnDirection_Org_sid;
						if ( !empty($data['MedStaffFact_id']) ) {
							$tmp = $this->db->query(
								"select Post_id as \"Post_id\",MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1",
								[ 'MedStaffFact_id' => $data['MedStaffFact_id'] ]
							)->result('array');
							if ( !empty($tmp[0]) ) {
								$updParams['Post_id'] = $tmp[0]['Post_id'];
								$updParams['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
							}
						}
                    break;
                    case 3: // 3 Другая организация
                    case 4: // 4 Военкомат
                    case 5: // 5 Скорая помощь
                    case 6: // 6 Администрация
                    case 10: // Казахстан 3 Скорая помощь
                    case 11: // Казахстан 4 Другой стационар
                    case 12: // Казахстан 5 Военкомат
                    case 13: // Казахстан 6 Роддом
                    case 14: // Казахстан
                    //case 15: // Казахстан
                    //case 16: // Казахстан
                        $updParams['Org_sid'] = $this->EvnDirection_Org_sid;
                        $updParams['LpuSection_id'] = null;
                        $updParams['MedStaffFact_id'] = null;
                        break;
                    case 7: // 7 Пункт помощи на дому
                        break;
                }

                $updParams['PrehospDirect_id'] = $this->EvnDirection_PrehospDirect_id;
				$updParams['EvnDirection_IsCito'] = ($data['EvnDirection_IsCito']=='on')?2:1;
				$updParams['EvnDirection_Descr'] = (empty($data['EvnDirection_Descr']))?null:$data['EvnDirection_Descr'];

                $set = '';

		        foreach ( $updParams as $key=>$value ) {
                    $set = $set . "{$key} = :{$key},";
                }

		        $set = rtrim( $set, ',' );

		        $query = "
		            update
                        EvnDirection
                    set
                        {$set}
                    where
                        Evn_id = :EvnDirection_id
		        ";

		        $updParams['EvnDirection_id'] = $this->EvnDirection_id;

				//echo getDebugSQL($query,$updParams);
		        $this->db->query($query,$updParams);
            }

			$response[0]['EvnDirection_id'] = $this->EvnDirection_id;

			// обновляем вид оплаты в направлении, если оно с признаком "к себе"
			$query = "
				update
					EvnDirection
				set
					PayType_id = :PayType_id
				where
					Evn_id = :EvnDirection_id
					and EvnDirection_IsReceive = 2
			";
			$this->db->query($query, array(
				'PayType_id' => $this->PayType_id,
				'EvnDirection_id' => $this->EvnDirection_id
			));
			collectEditedData('upd', 'EvnDirection', $this->EvnDirection_id);

			$this->deleteEmptySamples(array(
				'EvnLabRequest_id' => $response[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			// если услуга выполнена полностью или частично, обрабатываем заявку и создаем или пересоздаем документ
			$EvnUslugaPars = $this->EvnLabSample->getEvnUslugasRoot(array(
				'EvnDirection_id' => $this->EvnDirection_id
			));
			foreach ($EvnUslugaPars as $EvnUslugaPar) {
				// обновляем МЭС и диагноз в услугах
				$query = "
					select
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					from p_evnusluga_upd(
						evnusluga_id := :EvnUsluga_id,
						diag_id := :Diag_id,
						mes_id := :Mes_id
					)	
				";
				$this->db->query($query, array(
					'EvnUsluga_id' => $EvnUslugaPar['EvnUslugaPar_id'],
					'Diag_id' => $this->Diag_id,
					'Mes_id' => $this->Mes_id
				));
				collectEditedData('upd', 'EvnUslugaPar', $EvnUslugaPar['EvnUslugaPar_id']);

				if (!empty($data['ignoreCheckPayType']) && $data['ignoreCheckPayType'] == 2) {
					// обновить PayType в услуге и тестах, если есть
					if (!empty($EvnUslugaPar['EvnUslugaPar_id'])) {
						$query = "
							select
								error_code as \"Error_Code\",
								error_message as \"Error_Msg\"
							from p_evnusluga_upd(
								evnusluga_id := :EvnUsluga_id,
								paytype_id := :PayType_id
							);
							
							with myvars as (
								select evnusluga_id
								from v_evnusluga
								where evnusluga_pid = :EvnUsluga_id
							)
							select
								error_code as \"Error_Code\",
								error_message as \"Error_Msg\"
							from p_evnusluga_upd(
								evnusluga_id := (select evnusluga_id from myvars),
								paytype_id := :PayType_id
							)	
						";
						$this->db->query($query, array(
							'EvnUsluga_id' => $EvnUslugaPar['EvnUslugaPar_id'],
							'PayType_id' => $this->PayType_id
						));
						collectEditedData('upd', 'EvnUslugaPar', $EvnUslugaPar['EvnUslugaPar_id']);
					}
				}

				// Если есть хотя бы один одобренный тест
				$test = $this->getFirstResultFromQuery("
					select
						UslugaTest_id as \"UslugaTest_id\"
					from
						v_UslugaTest
					where
						UslugaTest_pid = :EvnUslugaPar_id
						and UslugaTest_ResultApproved = 2
					limit 1
					", array(
					'EvnUslugaPar_id' => $EvnUslugaPar['EvnUslugaPar_id']
				));
				if (!empty($test)) {
					// пересоздаем документ (протокол)
					$this->pmUser_id = $data['pmUser_id'];
					$this->EvnUslugaPar_oid = $EvnUslugaPar['EvnUslugaPar_id'];
					$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
					$samples = $this->EvnLabSample_model->loadList(array('EvnLabRequest_id' => $data['EvnLabRequest_id']));

					// Еще нужно заполнить объект $this->EvnLabSample
					$this->EvnLabSample->setItems($samples);

					// Сохраняем протокол
					$resp = $this->saveEvnXml();
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
				}
			}

			// кэшируем статус заявки
			$this->ReCacheLabRequestStatus(array(
				'EvnLabRequest_id' => $response[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				//'EvnDirection_id' => $this->EvnDirection_id
			));

			// кэшируем количество тестов
			$this->ReCacheLabRequestUslugaCount(array(
				'EvnLabRequest_id' => $response[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				//'EvnDirection_id' => $this->EvnDirection_id
			));

			// кэшируем названия услуг
			$this->ReCacheLabRequestUslugaName(array(
				'EvnLabRequest_id' => $response[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				//'EvnDirection_id' => $this->EvnDirection_id
			));

			// кэшируем статус проб в заявке
			$this->ReCacheLabRequestSampleStatusType(array(
				'EvnLabRequest_id' => $response[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				//'EvnDirection_id' => $this->EvnDirection_id
			));
		}
		return $response;
	}

	/**
	 * Сохранение поля  "Выполнение услуги" UslugaExecutionType_id
	 */
	function saveUslugaExecutionType($data)
	{
		$query = "
			update
				EvnLabRequest
			set
				UslugaExecutionType_id = :UslugaExecutionType_id
			where
				Evn_id = :EvnLabRequest_id
		";
		$result = $this->db->query($query, $data);

		if ($result) {
			collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);
			return true;
		}

		return false;
	}

	/**
	 * + Кэширование времени записи услуги
	 */
	function ReCacheLabRequestPrmTime($data)
	{
		if (empty($data['EvnDirection_id'])) {
			return false;
		}

		$this->load->swapi('common');
		$TimetableMedService = $this->common->GET('TimetableMedService', array(
			'EvnDirection_id' => $data['EvnDirection_id']
		), 'single');
		if (!$this->isSuccessful($TimetableMedService)) {
			return false;
		}
		if (empty($TimetableMedService)) {
			return true;
		}

		$params = array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'EvnLabRequest_PrmTime' => $TimetableMedService['TimetableMedService_begTime']
		);

		$query = "
			update
				evnlabrequest
			set EvnLabRequest_PrmTime = :EvnLabRequest_PrmTime
			where evn_id = :EvnLabRequest_id
		";

		$resp = $this->db->query($query, $params);
		if (empty($resp)) {
			return false;
		}

		collectEditedData('upd', 'EvnLabRequest', $params['EvnLabRequest_id']);
		return true;
	}

	/**
	 * Кэширование названия услуг на заявке
	 */
	function ReCacheLabRequestUslugaName($data)
	{
		if (empty($data['EvnLabRequest_id'])) {
			return false;
		}

		$params = array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		);

		$query = "
			select
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				to_char(eup.EvnUslugaPar_setDate, 'yyyy-mm-dd') as \"EvnUslugaPar_setDate\",
				coalesce(ucms.UslugaComplex_Name, u.UslugaComplex_Name) as \"UslugaComplex_Name\"
			from
				v_EvnLabRequest elr
				inner join v_EvnUslugaPar eup on eup.EvnDirection_id = elr.EvnDirection_id and eup.EvnLabSample_id is not null
				left join v_UslugaComplex u on u.UslugaComplex_id = eup.UslugaComplex_id
				left join lateral(
					select
						ucms.UslugaComplex_Name
					from
						v_EvnLabSample els -- службу надо брать из пробы
						inner join v_UslugaComplexMedService ucms on els.MedService_id = ucms.MedService_id
					where
						els.EvnLabRequest_id = elr.EvnLabRequest_id
						and ucms.UslugaComplex_id = u.UslugaComplex_id
						and ucms.UslugaComplexMedService_pid IS NULL
					limit 1	
				) ucms on true
			where
				elr.EvnLabRequest_id = :EvnLabRequest_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}

		//в случае, если evnuslugapar не связалась с evnlabsample
		if (empty($resp[0])) {
			$query = "
				select distinct
                	EvnUslugaPar_id as \"EvnUslugaPar_id\",
					eup.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
					to_char(eup.EvnUslugaPar_setDate, 'yyyy-mm-dd') as \"EvnUslugaPar_setDate\",
					coalesce(uc.UslugaComplex_Name, ucms.UslugaComplex_Name) as \"UslugaComplex_Name\"
				from v_EvnUslugaPar eup
					inner join v_UslugaTest ut on eup.EvnUslugaPar_id = ut.UslugaTest_pid
					inner join v_EvnLabSample elss on elss.EvnLabSample_id = ut.EvnLabSample_id
					left join v_UslugaComplex uc on uc.UslugaComplex_id = eup.UslugaComplex_id
					left join lateral(
						select
							ucms.UslugaComplex_Name
						from
							v_EvnLabSample els
							inner join v_UslugaComplexMedService ucms on els.MedService_id = ucms.MedService_id
					where
						els.EvnLabRequest_id = elss.EvnLabRequest_id
						and ucms.UslugaComplex_id = uc.UslugaComplex_id
						and ucms.UslugaComplexMedService_pid IS NULL
					limit 1	
					) ucms on true
				where elss.EvnLabRequest_id = :EvnLabRequest_id
			";

			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return false;
			}
		}

		$data['EvnLabRequest_UslugaName'] = json_encode($resp);

		$query = "
			update EvnLabRequest
			set EvnLabRequest_UslugaName = :EvnLabRequest_UslugaName
			where Evn_id = :EvnLabRequest_id
		";
		$this->db->query($query, array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'EvnLabRequest_UslugaName' => $data['EvnLabRequest_UslugaName']
		));

		collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);

		return true;
	}

	/**
	 * Кэширование количества назначенных тестов в заявке
	 */
	function ReCacheLabRequestUslugaCount($data)
	{
		if (empty($data['EvnLabRequest_id'])) {
			return false;
		}

		$data['EvnDirection_id'] = $this->getFirstResultFromQuery("
			select
				EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnLabRequest
			where
				EvnLabRequest_id = :EvnLabRequest_id
			", $data);

		if (empty($data['EvnDirection_id'])) {
			return false;
		}

		$tests = $this->loadCompositionMenu(array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (is_array($tests)) {
			$count = 0;
			foreach ($tests as $test) {
				if ($test['UslugaComplex_InRequest'] == 1) {
					$count++;
				}
			}

			$query = "
				update
					evnlabrequest
				set EvnLabRequest_UslugaCount = :EvnLabRequest_UslugaCount
				where evn_id = :EvnLabRequest_id";

			$result = $this->db->query($query, array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'EvnLabRequest_UslugaCount' => $count
			));

			collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);
		}

		return true;
	}

	/**
	 * Кэширование статуса заявки
	 */
	function ReCacheLabRequestStatus($data)
	{
		if (empty($data['EvnLabRequest_id'])) {
			return false;
		}

		// достаем количество проб, количество одобренных проб, выполнение услуги, текущий статус
		$query = "
			select
				elr.EvnDirection_id as \"EvnDirection_id\",
				ttms.TimeTableMedService_id as \"TimeTableMedService_id\",
				labsample.labsamples_all as \"labsamples_all\",
				labsample.labsamples as \"labsamples\",
				labsample.labsamples_result as \"labsamples_result\",
				labsample.labsamplesapproved as \"labsamplesapproved\",
				elr.UslugaExecutionType_id as \"UslugaExecutionType_id\",
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				esed.EvnStatus_SysNick as \"EvnDirectionStatus_SysNick\"
			from
				v_EvnLabRequest elr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
				left join lateral(
					Select TimeTableMedService_id from v_TimetableMedService_lite ttms where ttms.EvnDirection_id = ed.EvnDirection_id limit 1
				) ttms on true
				left join v_EvnStatus esed on esed.EvnStatus_id = ed.EvnStatus_id
				left join v_EvnStatus es on es.EvnStatus_id = elr.EvnStatus_id
				left join lateral(
					select
						count(els.EvnLabSample_id) as labsamples_all,
						sum(case when els.EvnLabSample_setDT is not null then 1 else 0 end) as labsamples,
						sum(case when els.LabSampleStatus_id = 3 then 1 else 0 end) as labsamples_result,
						sum(case when els.LabSampleStatus_id IN (4,6) then 1 else 0 end) as labsamplesapproved
					from
						v_EvnLabSample els
					where
						els.EvnLabRequest_id = elr.EvnLabRequest_id					
				) labsample on true
			where
				elr.EvnLabRequest_id = :EvnLabRequest_id
		";

		$result = $this->db->query($query, array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));

		$EvnStatus_SysNick = 'New'; // Статус получается при создании
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				if ($resp[0]['labsamples'] > 0) { // Заявка, у которой взята хотя бы одна проба
					$EvnStatus_SysNick = 'Work'; // В работе
				}

				if ($resp[0]['labsamples'] > 0 && $resp[0]['labsamples'] == $resp[0]['labsamples_all'] && $resp[0]['labsamples_result'] > 0) { // Заявка у которой все пробы взяты и хотя бы одна с резульататом
					$EvnStatus_SysNick = 'Done'; // С результатами
				}

				if ($resp[0]['labsamples'] > 0 && $resp[0]['labsamples'] == $resp[0]['labsamples_all'] && $resp[0]['labsamplesapproved'] == $resp[0]['labsamples']) { // Заявка, у которой все пробы одобренные или частично одобренные
					$EvnStatus_SysNick = 'Approved'; // Одобрена
				}

				if ($resp[0]['labsamples'] > 0 && $resp[0]['UslugaExecutionType_id'] == 3) {
					$EvnStatus_SysNick = 'Notdone'; // Не выполнена
				}

				//для свойств
				$this->load->model('EvnDirectionAll_model');
				//для функции
				$this->load->model('Lis_EvnDirection_model');
				if ($resp[0]['labsamples'] > 0 && $resp[0]['EvnDirectionStatus_SysNick'] != EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED) {
					// если есть пробы, то направление считается обслуженным, а значит переводим в статус “Обслужено”
					$this->Lis_EvnDirection_model->setStatus(array(
						'Evn_id' => $resp[0]['EvnDirection_id'],
						'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $data['pmUser_id']
					));
					collectEditedData('upd', 'EvnDirection', $resp[0]['EvnDirection_id']);
				}

				if ($EvnStatus_SysNick != $resp[0]['EvnStatus_SysNick']) {
					$this->Lis_EvnDirection_model->updateEvnStatusPostgre(array(
						'Evn_id' => $data['EvnLabRequest_id'],
						'EvnStatus_SysNick' => $EvnStatus_SysNick,
						'EvnClass_SysNick' => 'EvnLabRequest',
						'pmUser_id' => $data['pmUser_id']
					));
					collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);
					if (!empty($resp[0]['EvnDirection_id'])) {
						$EDEvnStatus_SysNick = 'Serviced';
						if ($EvnStatus_SysNick == 'New') {
							if (!empty($resp[0]['TimeTableMedService_id'])) {
								$EDEvnStatus_SysNick = 'DirZap';
							} else {
								$EDEvnStatus_SysNick = 'Queued';
							}
						}
						$this->Lis_EvnDirection_model->updateEvnStatusPostgre(array(
							'Evn_id' => $resp[0]['EvnDirection_id'],
							'EvnStatus_SysNick' => $EDEvnStatus_SysNick,
							'EvnClass_SysNick' => 'EvnDirection',
							'pmUser_id' => $data['pmUser_id']
						));
						collectEditedData('upd', 'EvnDirection', $resp[0]['EvnDirection_id']);
					}
				} else if ($EvnStatus_SysNick == 'Work') {
					// обновляем дату статуса заявки в любом случае (чтобы при очередном взятии пробы дата заявки равнялась последнему взятию пробы)
					$query = "
						select
							MAX(EvnLabSample_setDT) as \"EvnLabSample_setDT\"
						from v_EvnLabSample
						where EvnLabRequest_id = :EvnLabRequest_id";
					$result = $this->db->query($query, $data);

					if (is_object($result)) {
						$result = $result->result('array');
						if (isset($result[0])) {
							$result = $result[0]['EvnLabSample_setDT'];
						} else {
							$result = null;
						}
					} else {
						$result = null;
					}

					$query = "
						update
							evn
						set evn_statusdate = :Evn_StatusDate
						where evn_id = :Evn_id
						";
					$this->db->query($query, array(
						'Evn_id' => $data['EvnLabRequest_id'],
						'Evn_StatusDate' => $result
					));
					collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);
				}
			}
		}

		return false;
	}

	/**
	 * Кэширование статуса проб внутри заявки
	 */
	function ReCacheLabRequestSampleStatusType($data)
	{
		if (empty($data['EvnLabRequest_id'])) {
			return false;
		}

		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');

		$query = "
			select
				elr.EvnStatus_id as \"EvnStatus_id\",
				elr.MedService_id as \"MedService_id\",
				elr.UslugaComplex_id as \"UslugaComplex_id\",
				elr.SampleStatusType_id as \"SampleStatusType_id\",
				labsample.labsamples, -- кол-во взятых проб
				labsample.labsamplesneed, -- кол-во невзятых проб
				labsample.labsamplesnew, -- кол-во новых проб
				labsample.labsampleswork, -- кол-во проб в работе (отправлены на анализатор)
				labsample.labsamplesdone, -- кол-во выполненных проб
				labsample.labsamplesapproved, -- кол-во одобренных проб
				labsample.labsamplesapprovedpart, -- кол-во частично одобренных проб
				labsample.labsamplesbad, -- кол-во забракованных проб
				case when labsample.labsamplesoutnorm > 0 then 2 else 1 end as \"EvnLabRequest_IsOutNorm\",
				LabSampleNums.EvnLabSample_ids as \"EvnLabRequest_LabSampleNums\",
				BarCodes.EvnLabSample_BarCodes as \"EvnLabRequest_BarCodes\"
			from
				v_EvnLabRequest elr
				left join lateral(
					select
						sum(case when els.EvnLabSample_setDT is not null then 1 else 0 end) as labsamples,
						sum(case when els.EvnLabSample_setDT is null then 1 else 0 end) as labsamplesneed,
						sum(case when els.LabSampleStatus_id IN (1,7) then 1 else 0 end) as labsamplesnew,
						sum(case when els.LabSampleStatus_id = 2 then 1 else 0 end) as labsampleswork,
						sum(case when els.LabSampleStatus_id = 3 then 1 else 0 end) as labsamplesdone,
						sum(case when els.LabSampleStatus_id = 4 then 1 else 0 end) as labsamplesapproved,
						sum(case when els.LabSampleStatus_id = 6 then 1 else 0 end) as labsamplesapprovedpart,
						sum(case when els.LabSampleStatus_id = 5 then 1 else 0 end) as labsamplesbad,
						sum(case when COALESCE(els.EvnLabSample_IsOutNorm, 1) = 2 then 1 else 0 end) as labsamplesoutnorm
					from
						v_EvnLabSample els
					where
						els.EvnLabRequest_id = elr.EvnLabRequest_id					
				) labsample on true
				left join lateral(
                	select
                    	string_agg(cast(EvnLabSample_id as varchar), ', ') as EvnLabSample_ids
                    from v_EvnLabSample ls
					where ls.EvnLabRequest_id = elr.EvnLabRequest_id and ls.EvnLabSample_Num is not null    
                ) LabSampleNums on true
				left join lateral(
                	select
                    	string_agg((cast(EvnLabSample_id as varchar) || ':' || COALESCE(EvnLabSample_BarCode,'')), ', ') as EvnLabSample_BarCodes
                    from v_EvnLabSample ls
					where ls.EvnLabRequest_id = elr.EvnLabRequest_id and ls.EvnLabSample_Num is not null
                ) BarCodes on true
			where
				elr.EvnLabRequest_id = :EvnLabRequest_id
			limit 1	
		";
		$resp = $this->queryResult($query, array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));
		if (!is_array($resp) || count($resp) == 0) {
			return false;
		}

		$SampleStatusType_id = 3;
		$data['EvnLabRequest_IsOutNorm'] = 1;
		$data['EvnLabRequest_LabSampleNums'] = '';
		$data['EvnLabRequest_BarCodes'] = '';

		$data['MedService_id'] = $resp[0]['MedService_id'];
		$data['UslugaComplex_id'] = $resp[0]['UslugaComplex_id'];
		$data['EvnLabRequest_IsOutNorm'] = $resp[0]['EvnLabRequest_IsOutNorm'];
		$data['EvnLabRequest_LabSampleNums'] = $resp[0]['EvnLabRequest_LabSampleNums'];
		$data['EvnLabRequest_BarCodes'] = $resp[0]['EvnLabRequest_BarCodes'];

		if ($resp[0]['labsamples'] > 0 && $resp[0]['labsamplesneed'] > 0) {
			// если есть взятые пробы и невзятые пробы, то статус notall
			$SampleStatusType_id = 3;
		} elseif ($resp[0]['labsamplesneed'] == 1) {
			// если есть невзятые пробы и их 1 - то статус needone
			$SampleStatusType_id = 2;
		} elseif ($resp[0]['labsamplesneed'] > 1) {
			// если есть невзятые пробы и их более 1, то статус needmore
			$SampleStatusType_id = 1;
		} elseif ($resp[0]['labsamplesnew'] > 0) {
			// если есть новые пробы то статус new
			$SampleStatusType_id = 4;
		} elseif ($resp[0]['labsampleswork'] > 0) {
			// если есть пробы в работе то статус toanaliz
			$SampleStatusType_id = 5;
		} elseif ($resp[0]['labsamplesdone'] > 0) {
			// если есть выполненные пробы то статус exec
			$SampleStatusType_id = 6;
		} elseif (($resp[0]['labsamplesapproved'] > 0 && $resp[0]['labsamplesapproved'] < $resp[0]['labsamples']) || ($resp[0]['labsamplesapprovedpart'] > 0)) {
			// если есть одобренные пробы и их меньше общего кол-ва или есть частично одобренные пробы то статус someOk
			$SampleStatusType_id = 7;
		} elseif ($resp[0]['labsamplesapproved'] > 0 && $resp[0]['labsamplesapproved'] == $resp[0]['labsamples']) {
			// если все пробы одобрены то статус Ok
			$SampleStatusType_id = 8;
		} elseif ($resp[0]['labsamplesbad'] > 0) {
			// если есть забракованные пробы то статус bad
			$SampleStatusType_id = 9;
		}


		// обновляем поля
		$query = "
			update
				EvnLabRequest
			set
				SampleStatusType_id = :SampleStatusType_id,
				EvnLabRequest_IsOutNorm = :EvnLabRequest_IsOutNorm,
				EvnLabRequest_LabSampleNums = :EvnLabRequest_LabSampleNums,
				EvnLabRequest_BarCodes = :EvnLabRequest_BarCodes
			where
				Evn_id = :EvnLabRequest_id
		";

		$result = $this->db->query($query, array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'SampleStatusType_id' => $SampleStatusType_id,
			'EvnLabRequest_IsOutNorm' => $data['EvnLabRequest_IsOutNorm'],
			'EvnLabRequest_LabSampleNums' => $data['EvnLabRequest_LabSampleNums'],
			'EvnLabRequest_BarCodes' => $data['EvnLabRequest_BarCodes']
		));

		collectEditedData('upd', 'EvnLabRequest', $data['EvnLabRequest_id']);

		return true;
	}

	/**
	 * @return Abstract_model
	 */
	public function validate()
	{
		$this->valid = true;
		// TODO: Implement validate() method.
		//проверяем уникальность штрих-кода
		$count = $this->getFirstResultFromQuery("
			SELECT
				COUNT(*)
			FROM
				v_EvnLabRequest 
			WHERE
				EvnLabRequest_BarCode = :EvnLabRequest_BarCode 
				AND (EvnLabRequest_id <> :EvnLabRequest_id OR :EvnLabRequest_id IS NULL)
		", array(
			'EvnLabRequest_BarCode' => $this->fields['EvnLabRequest_BarCode'],
			'EvnLabRequest_id' => $this->fields['EvnLabRequest_id']
		));

		//проверка на уникальность регистрационного номера
		if( !empty($this->fields['EvnLabRequest_RegNum']) ) {
			$query = "
					select
						EvnLabRequest_id 
					from 
						v_EvnLabRequest
					where 
						EvnLabRequest_RegNum = :EvnLabRequest_RegNum 
						and MedService_id = :MedService_id
					  	and (EvnLabRequest_id <> :EvnLabRequest_id or EvnLabRequest_id is null)
			";
			$params = [
				'EvnLabRequest_RegNum' => $this->fields['EvnLabRequest_RegNum'],
				'MedService_id' => $this->fields['MedService_id'],
				'EvnLabRequest_id' => $this->fields['EvnLabRequest_id']
			];
			$result = $this->getFirstResultFromQuery($query, $params, true);
			if( $result === false ) {
				throw new Exception('Ошибка при выполнении запроса.');
			}
			if( $result !== null ) {
				throw new Exception('Проверьте правильность ввода регистрационного номера. Заявка с данным регистрационным номером уже существует.');
			}
		}

		if ($count === false) {
			$this->addError('Ошибка при проверке уникальности штрих-кода.');
		} else if ($count > 0) {
			$this->addError('Заявка со штрихкодом ' . $this->fields['EvnLabRequest_BarCode'] . ' уже существует.');
		}
		return $this;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionNumber($data)
	{
		$params = array('Lpu_id' => $data['Lpu_id']);
		$query = "
			select
				objectid as \"EvnPL_NumCard\"
			from xp_genpmid(
				objectname := 'EvnDirection',
				lpu_id := :Lpu_id
			)
   		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function genEvnLabRequest_BarCode()
	{
		$query = "
			select objectid as \"EvnLabRequest_BarCode\"
			from xp_genpmid(ObjectName := 'EvnLabRequest_BarCode', Lpu_id := null)
		";
		$barCode = $this->getFirstResultFromQuery($query);
		if (empty($barCode)) {
			return false;
		}

		if (strlen($barCode) <= 12) {
			$barCode = str_pad($barCode, 12, '0', STR_PAD_LEFT);
		} else {
			throw new Exception('Генератор штрих-кодов достиг максимальной длины кода. Обратитесь к разработчкам.');
		}
		$barCode .= $this->calculateEan13Checksum($barCode);

		return array(array(
			'success' => true,
			'EvnLabrequest_BarCode' => $barCode
		));
	}

	/**
	 * @param $code
	 * @return bool|int
	 */
	static function calculateEan13Checksum($code)
	{
		// Calculating Checksum
		// Consider the right-most digit of the message to be in an "odd" position,
		// and assign odd/even to each character moving from right to left
		// Odd Position = 3, Even Position = 1
		// Multiply it by the number
		// Add all of that and do 10-(?mod10)
		$odd = true;
		$result = 0;
		$keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$c = strlen($code);
		for ($i = $c; $i > 0; $i--) {
			if ($odd === true) {
				$multiplier = 3;
				$odd = false;
			} else {
				$multiplier = 1;
				$odd = true;
			}

			if (!isset($keys[$code[$i - 1]])) {
				return false;
			}

			$result += $keys[$code[$i - 1]] * $multiplier;
		}

		$result = (10 - $result % 10) % 10;
		return $result;
	}

	/**
	 * @throws Exception
	 */
	private function saveEvnDirection($data)
	{
		$params = array(
			'EvnDirection_id' => $this->EvnDirection_id,
			'Server_id' => !empty($data['Server_id']) ? $data['Server_id'] : $this->Server_id,
			'PersonEvn_id' => $this->PersonEvn_id,
			'EvnDirection_Num' => $this->EvnDirection_Num,
			'Diag_id' => $this->Diag_id,
			'PrehospDirect_id' => $this->EvnDirection_PrehospDirect_id,
			'EvnDirection_setDT' => $this->EvnDirection_setDT,
			'EvnDirection_setDate' => $this->EvnDirection_setDT,
			'MedService_id' => $this->EvnDirection_MedService_id,
			'EvnDirection_IsCito' => $this->EvnDirection_IsCito,
			'EvnDirection_Descr' => $this->EvnDirection_Descr,
			'Lpu_id' => $this->Lpu_id,//ЛПУ, создавшее направление
			'Lpu_did' => $this->Lpu_id,//ЛПУ, куда был направлен пациент
			'DirType_id' => 10,//тип направления: "На исследование"
			'EvnDirection_IsAuto' => 2,//Это системное направление, т.к. электронное направление может создать только врач
			'EvnDirection_IsReceive' => 2,
			'LpuSection_id' => null,//Направившее отделение
			'From_MedStaffFact_id' => null,//Направивший врач
			'Lpu_sid' => null,//Направившее ЛПУ
			'Org_sid' => null,//Направившая организация
			'pmUser_id' => $this->pmUser_id,
			'MedPersonal_Code' => isset($data['MedPersonal_Code']) ? $data['MedPersonal_Code'] : null,//Код врача
			'Person_id' => $data['Person_id']
		);

		// получаем поля которые уже могли быть в направлении
		if (!empty($this->EvnDirection_id)) {
			$query = "
				select
					MedPersonal_zid as \"MedPersonal_zid\",
					Diag_id as \"Diag_id\",
					LpuSectionProfile_id as \"LpuSectionProfile_id\",
					EvnDirection_IsReceive as \"EvnDirection_IsReceive\",
					TimetableMedService_id as \"TimetableMedService_id\"
				from
					v_EvnDirection_all
				WHERE
					EvnDirection_id = :EvnDirection_id
				limit 1	
			";
			// default database
			$result = $this->db->query($query, array('EvnDirection_id' => $this->EvnDirection_id));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$params['MedPersonal_zid'] = $resp[0]['MedPersonal_zid'];
					$params['Diag_id'] = $resp[0]['Diag_id'];
					$params['LpuSectionProfile_id'] = $resp[0]['LpuSectionProfile_id'];
					$params['EvnDirection_IsReceive'] = $resp[0]['EvnDirection_IsReceive'];
					$params['TimetableMedService_id'] = $resp[0]['TimetableMedService_id'];
				}
			}
		}

		// если создаём заявку без бирки (приём без записи), то создаём доп. бирку и заявку кидаем на неё.
		if (empty($params['EvnDirection_id']) && empty($params['TimetableMedService_id'])) {
			$this->load->swapi('common');
			$ttmsdata = $this->common->POST('TimetableMedService/Dop', array(
				'MedService_id' => $params['MedService_id'],
				'TimetableExtend_Descr' => null,
				'withoutRecord' => true,
			));
			if (!$this->isSuccessful($ttmsdata)) {
				throw new Exception($ttmsdata['Error_Msg']);
			}
			if (!empty($ttmsdata['data']['TimetableMedService_id'])) {
				$params['TimetableMedService_id'] = $ttmsdata['data']['TimetableMedService_id'];
			}
		}

		//Кем направлен:
		// 1 Отделение ЛПУ
		// 2 Другое ЛПУ
		// 3 Другая организация
		// 4 Военкомат
		// 5 Скорая помощь
		// 6 Администрация
		// 7 Пункт помощи на дому
		switch ((int)$this->EvnDirection_PrehospDirect_id) {
			case 1: // 1 Отделение ЛПУ
			case 15: // Казахстан
			case 8: // 8 ПСМП Казахстан
				$params['Lpu_sid'] = $this->Lpu_id; //Направившее ЛПУ
				$params['LpuSection_id'] = $this->EvnDirection_LpuSection_id;
				$params['From_MedStaffFact_id'] = $this->EvnDirection_MedStaffFact_id;
				break;
			case 2: // 2 Другое ЛПУ
			case 16: // Казахстан
			case 9: // 9 Казахстан КДП
				// по  $this->EvnDirection_Org_sid получаем Lpu_id
				$Lpu_id = $this->getFirstResultFromQuery("
					SELECT
						Lpu_id
					FROM
						v_Lpu
					WHERE
						Org_id = :Org_sid
					limit 1	
					", array('Org_sid' => $this->EvnDirection_Lpu_sid));
				$params['Lpu_sid'] = $Lpu_id;//Направившее ЛПУ
				$params['LpuSection_id'] = $this->EvnDirection_LpuSection_id;
				$params['From_MedStaffFact_id'] = $this->EvnDirection_MedStaffFact_id;
				if ($this->getRegionNick() == 'kz')
					$params['Org_sid'] = $this->EvnDirection_Org_sid;
				break;
			case 3: // 3 Другая организация
			case 4: // 4 Военкомат
			case 5: // 5 Скорая помощь
			case 6: // 6 Администрация
			case 10: // Казахстан 3 Скорая помощь
			case 11: // Казахстан 4 Другой стационар
			case 12: // Казахстан 5 Военкомат
			case 13: // Казахстан 6 Роддом
			case 14: // Казахстан
			//case 15: // Казахстан
			//case 16: // Казахстан
				$params['Org_sid'] = $this->EvnDirection_Org_sid;
				break;
			case 7: // 7 Пункт помощи на дому
				break;
		}
		$this->load->model('Lis_EvnDirection_model');
		//чтобы модель направления не выдавала нотисы о несуществующих ключах, напихаем нуллов
		$tmp = array('LpuSectionProfile_id', 'Diag_id', 'LpuSection_did', 'MedPersonal_zid', 'EvnUsluga_id', 'EvnQueue_id', 'EvnDirection_pid', 'MedPersonal_id');
		foreach ($tmp as $k) {
			if (!array_key_exists($k, $params)) {
				$params[$k] = null;
			}
		}
		if (isset($data['session'])) {
			$params['session'] = $data['session'];
		}
		$result = $this->Lis_EvnDirection_model->saveEvnDirection($params);
		if (self::save_ok($result)) {
			if (isset($result[0])) {
				$this->EvnDirection_id = $result[0]['EvnDirection_id'];
				collectEditedData(empty($params['EvnDirection_id']) ? 'ins' : 'upd', 'EvnDirection', $result[0]['EvnDirection_id']);
			}
		} else {
			$errMsg = '';
			if (isset($result['Error_Msg'])) {
				$errMsg = $result['Error_Msg'];
			} elseif (isset($result[0]) && isset($result[0]['Error_Msg'])) {
				$errMsg = $result[0]['Error_Msg'];
			}
			throw new Exception(
				'При создании направления произошла ошибка: ' . $errMsg
			);
		}
	}

	/**
	 * Отмена направления
	 */
	function cancelDirection($data)
	{
		$directionData = array();

		// переопределяем
		$data['DirFailType_id'] = null;
		$data['QueueFailCause_id'] = null;
		switch ($data['EvnStatusCause_id']) {
			case 1:
				$data['DirFailType_id'] = 5;
				$data['QueueFailCause_id'] = 8;
				break;
			case 3:
				$data['DirFailType_id'] = 11;
				$data['QueueFailCause_id'] = 11;
				break;
			case 4:
				$data['DirFailType_id'] = 14;
				$data['QueueFailCause_id'] = 5;
				break;
			case 5:
				$data['DirFailType_id'] = 13;
				$data['QueueFailCause_id'] = 4;
				break;
			case 14:
				$data['DirFailType_id'] = 9;
				$data['QueueFailCause_id'] = 9;
				break;
			case 15:
				$data['DirFailType_id'] = 10;
				$data['QueueFailCause_id'] = 10;
				break;
			case 16:
				$data['DirFailType_id'] = 12;
				$data['QueueFailCause_id'] = null; // нет подходящего
				break;
			case 18:
				$data['DirFailType_id'] = 17;
				$data['QueueFailCause_id'] = 12;
				break;
		}

		// 1. получение данных направления
		$query = "
			select
				d.pmUser_insID as \"pmUser_insID\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				d.EvnDirection_Num as \"EvnDirection_Num\",
				ms.MedService_Name as \"MedService_Name\",
				COALESCE(PS.Person_SurName, '') || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"Person_Fio\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			from
				v_EvnDirection_all d
				left join v_EvnStatus es on es.EvnStatus_id = d.EvnStatus_id
				left join v_EvnLabRequest elr on elr.EvnDirection_id = d.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = d.Person_id
				left join v_MedService ms on ms.MedService_id = d.MedService_id
				left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
				left join v_EvnUslugaPar eup ON eup.EvnDirection_id = d.EvnDirection_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = COALESCE(elr.UslugaComplex_id, eup.UslugaComplex_id)
			where
				d.EvnDirection_id = :EvnDirection_id
			limit 1	
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$directionData = $result->result('array');

			$dirName = $this->db->query("
				select
					DirFailType_Name as \"DirFailType_Name\"
				from
					v_DirFailType
				where
					DirFailType_id = :DirFailType_id
				limit 1	
			", $data);

			$dirName = $dirName->result('array');
			$directionData[0]['DirFailType_Name'] = $dirName[0]['DirFailType_Name'];
		}

		if (count($directionData) == 0) {
			return array(array('Error_Msg' => 'Ошибка получения данных по направлению'));
		}

		if (in_array($directionData[0]['EvnStatus_SysNick'], array('Declined', 'Canceled'))) {
			return array(array('Error_Msg' => 'Направление уже отменено'));
		}

		// 2.0. удаляем заявку
		if (!empty($directionData[0]['EvnLabRequest_id'])) {
			if ($this->canBeDeleted(array(
				'EvnLabRequest_id' => $directionData[0]['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			))) {
				// последовательно удаляем все пробы
				$samplesIndaShit = $this->db->query("
					SELECT
						EvnLabSample_id as \"EvnLabSample_id\"
					FROM
						v_EvnLabSample
					WHERE
						EvnLabRequest_id = :EvnLabRequest_id
					", array('EvnLabRequest_id' => $directionData[0]['EvnLabRequest_id']));
				if (is_object($samplesIndaShit)) {
					$samplesIndaShit = $samplesIndaShit->result('array');
					$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
					$this->EvnLabSample_model->pmUser_id = $data['pmUser_id'];
					foreach ($samplesIndaShit as $sample) {
						$this->EvnLabSample_model->EvnLabSample_id = $sample['EvnLabSample_id'];
						if (!$this->EvnLabSample_model->delete(array(
							'EvnLabSample_id' => $sample['EvnLabSample_id'],
							'pmUser_id' => $data['pmUser_id']
						))) {
							throw new Exception("Ошибка удаления пробы {$sample['EvnLabSample_id']}");
						}
					}
				}
				// удаляем заявку
				$params = array(
					'EvnLabRequest_id' => $directionData[0]['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->deleteEvnLabRequest($params);
				if (!empty($result['Error_Msg'])) {
					return $result;
				}
			} else {
				return array(
					0 => array(
						'Error_Msg' => 'Нельзя удалить данную заявку, т.к. ее статус не "Новая"'
					)
				);
			}
		}

		// 2.1. удаляем услугу
		if (!empty($directionData[0]['EvnUslugaPar_id'])) {
			$params = array(
				'EvnUslugaPar_id' => $directionData[0]['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				select    
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_EvnUslugaPar_del(
					EvnUslugaPar_id := :EvnUslugaPar_id,
					pmUser_id := :pmUser_id
				)
			";
			$dbresponse = $this->db->query($query, $params);
			if (is_object($dbresponse)) {
				$result = $dbresponse->result('array');
				if ($result[0] && !empty($result[0]['Error_Msg'])) {
					return array(
						0 => array(
							'Error_Msg' => $result[0]['Error_Msg']
						)
					);
				}
			} else {
				return array(
					0 => array(
						'Error_Msg' => 'При удалении произошли ошибки'
					)
				);
			}
		}

		$this->load->model('Lis_EvnDirection_model');

		// Если назначение имеет направление, то нужно сначала отменить направление
		if (!empty($data['EvnDirection_id'])) {
			$sql = "
				select
					ed.TimetableMedService_id as \"TimetableMedService_id\",
					ed.EvnStatus_id as \"EvnStatus_id\"
				from
					v_EvnDirection_all ed
					left join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = ed.EvnDirection_id
				where
					ed.EvnDirection_id = :EvnDirection_id
			";
			$res = $this->db->query($sql, array('EvnDirection_id' => $data['EvnDirection_id']));
			if (is_object($res)) {
				$tmp = $res->result('array');
			}
			if (count($tmp) > 0) {
				$data['TimetableMedService_id'] = $tmp[0]['TimetableMedService_id'];
				$data['EvnStatus_id'] = $tmp[0]['EvnStatus_id'];
			}

			if (!empty($data['TimetableMedService_id'])) {
				// отмена направления и бирки
				$data['object'] = 'TimetableMedService';
				$data['EvnComment_Comment'] = $data['EvnStatusHistory_Cause'];

				$tmp = $this->common->DELETE('TimetableMedService', $data, 'single');
				if (!$this->isSuccessful($tmp)) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			} else if (!empty($data['EvnQueue_id'])) {
				// отмена направления и очереди
				// $data['EvnQueue_id'] не должно передаватсья
				//$this->load->model('Queue_model', 'Queue_model');
				//$data['EvnComment_Comment'] = $data['EvnStatusHistory_Cause'];
				//$tmp = $this->Queue_model->cancelQueueRecord($data);
				//if (!$tmp) {
				//	throw new Exception('Ошибка при удалении из очереди', 500);
				//}
				//if (isset($tmp[0]['Error_Msg'])) {
				//	throw new Exception($tmp[0]['Error_Msg'], 500);
				//}
			}

			// только отмена направления
			$params = array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'DirFailType_id' => $data['DirFailType_id'],
				'EvnComment_Comment' => $data['EvnStatusHistory_Cause'],
				'EvnStatusCause_id' => $data['EvnStatusCause_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Lpu_cid' => $data['session']['lpu_id'],
				'MedStaffFact_fid' => $data['session']['CurMedStaffFact_id']
			);
			$tmp = $this->Lis_EvnDirection_model->execDelDirection('p_EvnDirection_decline', $params);

			if (empty($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], 500);
			}

			// Направление помечать удаленным, если причина "Неверный ввод" или "Ошибочное направление"
			if (in_array($data['EvnStatusCause_id'], array(3, 4))) {
				$tmp = $this->Lis_EvnDirection_model->deleteEvnDirection(array(
					'EvnDirection_id' => $data['EvnDirection_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$tmp) {
					throw new Exception('Ошибка при удалении направления', 500);
				}
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
			}
		}

		$noticeData = array(
			'autotype' => 1,
			'User_rid' => $directionData[0]['pmUser_insID'],
			'type' => 1,
			'title' => 'Отмена направления',
			'text' => 'Направление №' . $directionData[0]['EvnDirection_Num'] . ' (' . $directionData[0]['Person_Fio'] . ') в лабораторию ' . $directionData[0]['MedService_Name'] . ' (' . $directionData[0]['Lpu_Nick'] . ', ' . $directionData[0]['LpuSection_Name'] . ') на услугу "' . $directionData[0]['UslugaComplex_Name'] . '" отменено по причине ' . $directionData[0]['DirFailType_Name'] . '. ' . $data['EvnStatusHistory_Cause']
		);
		$this->load->swapi('common');
		$this->common->POST('Messages/auto', $noticeData);

		return array('Error_Msg' => '', 'success' => true);
	}

	/**
	 * Удаление заявки
	 */
	function deleteEvnLabRequest($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_EvnLabRequest_del(
				EvnLabRequest_id := :EvnLabRequest_id,
				pmUser_id := :pmUser_id
			)
			";
		$resp = $this->db->query($query, $data);
		if (is_object($resp)) {
			return $resp->result('array');
		}

		return array('Error_Msg' => 'Ошибка удаления заявки');
	}

	/**
	 * Получение услуг по заявке для печати
	 */
	function getEvnUslugaParForPrint($data)
	{
		if (empty($data['EvnDirections'])) {
			return array();
		}

		if(!empty($data['isProtocolPrinted'])) {
			$EvnDirections = json_decode($data['EvnDirections'], false, 512, JSON_BIGINT_AS_STRING);
			$update = $this->setProtocolPrintFlag(['EvnDirections' => $EvnDirections], $data['isProtocolPrinted']);
		}

		if (is_string($data['EvnDirections'])) {
			$data['EvnDirections'] = json_decode($data['EvnDirections'], false, 512, JSON_BIGINT_AS_STRING);
			$sort = $this->sortEvnUslugaParForPrint($data['EvnDirections']);

			return $this->queryResult("
				select
					EvnUslugaPar_id as \"EvnUslugaPar_id\",
					EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
					EvnDirection_id \"EvnDirection_id\"
				from
					v_EvnUslugaPar
				where
					EvnDirection_id in (" . implode(',', $data['EvnDirections']) . ")
					and EvnUslugaPar_setDT is not null
				{$sort}
			");
		}
	}

	/**
	 * Сортируем результат запроса по отсортированным параметрам с клиента
	 * @param array $EvnDirections отсортированные направления с клиента
	 * @return array отсортированный массив
	 */
	public function sortEvnUslugaParForPrint($EvnDirections) {
		$sort = "order by case EvnDirection_id";

		for($i = 0; $i < count($EvnDirections); $i++) {
			$sort .= "\nwhen {$EvnDirections[$i]} then {$i}";
		}

		return $sort . "\nend";
	}

	/**
	 * Получение данных маркера для ЭМК
	 */
	function getLabTestsPrintData($data)
	{
		$resp = array();

		$resp_check = $this->queryResult("
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from v_EvnUslugaPar
			where
				EvnUslugaPar_pid = :EvnUslugaPar_pid
			limit 1
			", array(
			'EvnUslugaPar_pid' => $data['Evn_pid']
		));

		// выполняем основной запрос, только если действительно есть параклинические услуги в случае
		if (!empty($resp_check[0]['EvnUslugaPar_id'])) {
			$resp_uc = $this->queryResult("
			with eup as (
				select
					eup.EvnUslugaPar_id,
					eup.UslugaComplex_id,
					eup.EvnUslugaPar_setDate
				from
					v_EvnUslugaPar eup
				where
					eup.EvnUslugaPar_pid = :Evn_pid
			)

			select
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				COALESCE(analyzertest.UslugaComplex_ParentName, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				to_char (eup.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				to_char (els.EvnLabSample_updDT,'dd.mm.yyyy HH24:MI') as \"EvnLabSample_updDT\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				COALESCE('('||ut.UslugaTest_ResultUnit||')','') as \"UslugaTest_ResultUnit\",
				COALESCE(ut.UslugaTest_ResultLower,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpper,'') as \"UslugaTest_ResultNorm\",
				COALESCE(ut.UslugaTest_ResultLowerCrit,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpperCrit,'') as \"UslugaTest_ResultCrit\",
				uc_child.UslugaComplex_id as \"UslguaComplexTest_id\",
				coalesce(analyzertest.AnalyzerTest_SysNick, analyzertest.UslugaComplex_Name, uc_child.UslugaComplex_Name) as \"UslguaComplexTest_Name\"
			from eup
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = eup.UslugaComplex_id
				inner join v_UslugaTest ut on ut.UslugaTest_pid = eup.EvnUslugaPar_id
				inner join v_UslugaComplex uc_child on uc_child.UslugaComplex_id = ut.UslugaComplex_id
				inner join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
				left join lateral(
					select
						at_child.AnalyzerTest_SysNick,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = COALESCE(at_child.AnalyzerTest_pid, at_child.AnalyzerTest_id) -- родительское исследование, если есть
						inner join v_UslugaComplexMedService ucms_at on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = COALESCE(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id) -- родительская услуга, если есть
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						ucms_at.UslugaComplex_id = ut.UslugaComplex_id
						and ucms_parent.UslugaComplex_id = eup.UslugaComplex_id
						and ucms_at.MedService_id = els.MedService_id
						and (at.AnalyzerTest_IsNotActive is null or at.AnalyzerTest_IsNotActive = 1)
						and (a.Analyzer_IsNotActive is null or a.Analyzer_IsNotActive = 1)
						and (at.AnalyzerTest_endDT >= ut.UslugaTest_setDT or at.AnalyzerTest_endDT is null)
						and (at_child.AnalyzerTest_endDT >= ut.UslugaTest_setDT or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= ut.UslugaTest_setDT or uctest.UslugaComplex_endDT is null)
					order by
						at_child.AnalyzerTest_pid desc -- в первую очередь ищем тест
                    limit 1    
				) analyzertest on true
			where
				ut.UslugaTest_setDT is not null
			order by
				eup.EvnUslugaPar_setDate
		", array(
				'Evn_pid' => $data['Evn_pid']
			));

			if (!empty($resp_uc) && is_array($resp_uc)) {
				// Формируем удобные массивы (с выполненными услугами и с их тестами)
				$UslugaComplexList = array();
				$EvnUslugaPar = array();
				$EvnLabSample = array();
				foreach ($resp_uc as $one_uc) {
					if (empty($UslugaComplexList[$one_uc['UslugaComplex_id']])) {
						$UslugaComplexList[$one_uc['UslugaComplex_id']] = array(
							'UslugaComplex_Name' => $one_uc['UslugaComplex_Name'],
							'UslugaComplex_Code' => $one_uc['UslugaComplex_Code'],
							'EvnLabSample' => array(),
							'tests' => array()
						);
					}
					if (empty($EvnUslugaPar[$one_uc['UslugaComplex_id']][$one_uc['EvnUslugaPar_id']])) {
						$EvnUslugaPar[$one_uc['UslugaComplex_id']][$one_uc['EvnUslugaPar_id']] = array(
							'EvnUslugaPar_setDate' => $one_uc['EvnUslugaPar_setDate'],
							'EvnLabSample_updDT' => $one_uc['EvnLabSample_updDT'],
							'results' => array()
						);
					}
					$UslugaComplexList[$one_uc['UslugaComplex_id']]['tests'][$one_uc['UslguaComplexTest_id']] = $one_uc['UslguaComplexTest_Name'];
					$EvnUslugaPar[$one_uc['UslugaComplex_id']][$one_uc['EvnUslugaPar_id']]['results'][$one_uc['UslguaComplexTest_id']] = array(
						'result' => $one_uc['UslugaTest_ResultValue'],
						'unit' => $one_uc['UslugaTest_ResultUnit'],
						'norm' => $one_uc['UslugaTest_ResultNorm'],
						'crit' => $one_uc['UslugaTest_ResultCrit'],
					);

					$EvnLabSample[$one_uc['UslugaComplex_id']][$one_uc['EvnLabSample_id']] = $one_uc['EvnLabSample_updDT'];
				}

				array_walk($EvnLabSample, function (&$val) {
					$val = array_unique($val);
				});

				foreach ($UslugaComplexList as $UslugaComplex_id => $UslugaComplex) {
					$tests = array();
					foreach ($UslugaComplex['tests'] as $UslguaComplexTest_id => $UslguaComplexTest_Name) {
						$tests[] = array(
							'test_name' => $UslguaComplexTest_Name
						);
					}

					$one_uc = array(
						'UslugaComplex_Name' => $UslugaComplex['UslugaComplex_Name'],
						'UslugaComplex_Code' => $UslugaComplex['UslugaComplex_Code'],
						'EvnLabSample_updDT' => $EvnLabSample[$UslugaComplex_id],
						'tests' => $tests,
						'EvnUslugaPar' => array()
					);

					if (!empty($EvnUslugaPar[$UslugaComplex_id])) {
						foreach ($EvnUslugaPar[$UslugaComplex_id] as $EvnUslugaPar_id => $one_eup) {
							$one_result = array(
								'EvnUslugaPar_setDate' => $one_eup['EvnUslugaPar_setDate'],
								'EvnLabSample_updDT' => $one_eup['EvnLabSample_updDT'],
								'results' => array()
							);

							foreach ($UslugaComplex['tests'] as $UslguaComplexTest_id => $UslguaComplexTest_Name) {
								$result = "";
								$unit = "";
								$norm = "";
								$crit = "";
								if (!empty($one_eup['results'][$UslguaComplexTest_id])) {
									$result = $one_eup['results'][$UslguaComplexTest_id]['result'];
									$unit = $one_eup['results'][$UslguaComplexTest_id]['unit'];
									$norm = $one_eup['results'][$UslguaComplexTest_id]['norm'];
									$crit = $one_eup['results'][$UslguaComplexTest_id]['crit'];
								}
								$one_result['results'][] = array(
									'test_name' => $UslguaComplexTest_Name,
									'result' => $result,
									'unit' => $unit,
									'norm' => $norm,
									'crit' => $crit
								);
							}

							$one_uc['EvnUslugaPar'][] = $one_result;
						}
					}

					// надо разбить одну услугу на несколько, если тестов много, иначе не влезет табличка по ширине в ЭМК
					$maxCount = 9;
					$testsCount = count($one_uc['tests']);
					if (isset($data['isVert']) && $data['isVert']) {
						$one_uc['UslugaComplex_Name'] = '<tr><td colspan="4"><b>' . $one_uc['UslugaComplex_Name'] . '</b></td></tr>';
						$resp[] = $one_uc;
					} elseif ($testsCount > $maxCount) {
						for ($k = 0; $k <= ceil($testsCount / $maxCount) - 1; $k++) {
							$one_uc_reduced = $one_uc;
							if ($k > 0) {
								$one_uc_reduced['UslugaComplex_Name'] = '';
							} else {
								$one_uc_reduced['UslugaComplex_Name'] = '<tr><td colspan="' . (count($one_uc_reduced['tests']) + 2) . '"><b>' . $one_uc_reduced['UslugaComplex_Name'] . '</b></td></tr>';
							}
							$one_uc_reduced['tests'] = array_slice($one_uc_reduced['tests'], $k * $maxCount, $maxCount);
							foreach ($one_uc_reduced['EvnUslugaPar'] as $key => $one) {
								$one_uc_reduced['EvnUslugaPar'][$key]['results'] = array_slice($one_uc_reduced['EvnUslugaPar'][$key]['results'], $k * $maxCount, $maxCount);
							}
							$resp[] = $one_uc_reduced;
						}
					} else {
						$one_uc['UslugaComplex_Name'] = '<tr><td colspan="' . ($testsCount + 2) . '"><b>' . $one_uc['UslugaComplex_Name'] . '</b></td></tr>';
						$resp[] = $one_uc;
					}
				}
			}
		}
		return array(
			'UslugaComplex' => $resp
		);
	}

	/**
	 * Для уведомления о выполненном лаб. исследовании.
	 * Возвращает EvnUslugaPar_id только для выполненного исследования.
	 */
	function getEvnUslugaParId($EvnLabRequest_id)
	{
		$EvnUslugaPar_id = $this->getFirstResultFromQuery("
			SELECT 
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			FROM
				v_EvnUslugaPar eup
				inner join v_EvnLabRequest elr on elr.EvnDirection_id = eup.EvnDirection_id
				inner join v_Evn evn on evn.Evn_id = eup.EvnUslugaPar_id
			WHERE 
				elr.EvnLabRequest_id = :EvnLabRequest_id
				and ((evn.Evn_setDT is not null	and evn.EvnClass_SysNick != 'EvnReanimatPeriod') or evn.Evn_didDT is not null)
				and evn.EvnClass_SysNick in('EvnDoctor','EvnUslugaCommon','EvnUslugaOper','EvnUslugaPar','EvnPS','EvnSection','EvnDie','EvnLeave','EvnOtherLpu','EvnOtherSection','EvnOtherStac','StickFSSData','EvnReanimatPeriod')
				and not exists (
					select EvnUslugaPar_id
					from v_EvnUslugaPar
					where EvnUslugaPar_id = evn.Evn_id
						and EvnLabSample_id is not null
					limit 1	
				)
		",
			array(
				'EvnLabRequest_id' => $EvnLabRequest_id
			));
		return $EvnUslugaPar_id;
	}

	/**
	 * Изменение результата в заказе услуги
	 * @param array $data
	 * @return array
	 */
	function updateEvnUslugaParResult($data)
	{
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'EvnUslugaPar_Result' => !empty($data['EvnUslugaPar_Result']) ? $data['EvnUslugaPar_Result'] : null,
		);

		$query = "
			update Evn
			set
				Evn_updDT = dbo.tzgetdate(),
				pmUser_updID = :pmUser_id
			where
				Evn_id = :EvnUslugaPar_id;

			update EvnUsluga
			set
				EvnUsluga_Result = :EvnUslugaPar_Result
			where
				Evn_id = :EvnUslugaPar_id;
		";
		// echo getDebugSQL($query, $data); exit();
		$this->db->query($query, $params);

		return array(array(
			'success' => true
		));
	}

	/**
	 * Включение исследования в заявку по назначению
	 * @param array $data
	 * @return array
	 */
	function includeUslugaComplexForPrescr($data)
	{
		// нам понядобится EvnLabRequest_id, MedService_id, PersonEvn_id, Server_id
		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id']
		);
		$query = "
			select
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				ed.MedService_id as \"MedService_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				elr.Server_id as \"Server_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\"
			from
				v_EvnLabRequest elr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
				inner join v_EvnStatus es on es.EvnStatus_id = ed.EvnStatus_id
				left join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id
			where
				ed.EvnDirection_id = :EvnDirection_id
				and es.EvnStatus_SysNick in ('Queued', 'DirZap')
			limit 1
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (empty($resp)) {
			return $this->createError('', 'Ошибка получения данных по заявке');
		}
		$data = array_merge($data, $resp);

		$this->load->model('EvnLabSample_model');

		$this->beginTransaction();

		// добавляем исследование в заявку
		if (!empty($data['UslugaComplex_id'])) {
			if (empty($data['EvnLabSample_id'])) {
				$resp = $this->EvnLabSample_model->saveLabSample(array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id'],
					'RefSample_id' => null,
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'MedService_id' => $data['MedService_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
				$data['EvnLabSample_id'] = $resp[0]['EvnLabSample_id'];
			}

			// исследование
			$uslugaRoot = $this->EvnLabSample_model->saveEvnUslugaRoot(array(
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'PayType_id' => !empty($data['PayType_id']) ? $data['PayType_id'] : null,
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ($uslugaRoot['new'] == false) {
				$this->rollbackTransaction();
				return $this->createError('', 'Выбранное исследование уже есть в заявке');
			}
			$EvnUslugaPar_id = $uslugaRoot['EvnUslugaPar_id'];

			// заказ услуги сохраняем в EvnLabRequestUslugaComplex
			if (!empty($EvnUslugaPar_id)) {
				if (!empty($data['UslugaComplexListByPrescr'])) {
					$uslugaComplexListForSave = $data['UslugaComplexListByPrescr'];
				} else {
					$uslugaComplexListForSave = array($data['UslugaComplex_id']);
				}
				foreach ($uslugaComplexListForSave as $usluga) {
					$uslugaArray = (array) $usluga;
					$resp = $this->saveEvnLabRequestUslugaComplex(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'EvnLabSample_id' => $data['EvnLabSample_id'],
						'EvnUslugaPar_id' => $EvnUslugaPar_id,
						'UslugaComplex_id' => $uslugaArray['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}
			}
		}

		// кэшируем количество тестов
		$this->ReCacheLabRequestUslugaCount(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем статус проб в заявке
		$this->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		$this->commitTransaction();

		return array(array(
			'success' => true
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveEvnLabRequestPrmTime($data)
	{
		$params = array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'EvnLabRequest_prmTime' => $data['EvnLabRequest_prmTime'],
		);
		$query = "
			update EvnLabRequest
			set EvnLabRequest_prmTime = :EvnLabRequest_prmTime
			where Evn_id = :EvnLabRequest_id
		";
		$this->db->query($query, $params);

		return array(array(
			'success' => true
		));
	}

	/**
	 * Получение данных исследований в услуге
	 */
	function LabRequestUslugaComplexData($data)
	{
		return $this->queryResult("
			select
				UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnLabRequestUslugaComplex
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", $data);
	}

	/**
	 * Получение списка услуг в заявке
	 * @param $params
	 * @return array|false
	 * @throws Exception
	 */
	function getUslugaComplexList($params)
	{
		$query = 'select
			   elruc.UslugaComplex_id as "UslugaComplex_id"
			from v_EvnLabRequestUslugaComplex elruc
			where EvnLabRequest_id = :EvnLabRequest_id
		';
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Фильтрация заявок
	 * пока по атрибуту или статусу теста
	 * @param $data
	 * @return array|false
	 */
	function filterEvnLabRequests ( $data ) {

		$params = [];
		$where = "";

		if (!empty($data['UslugaComplexAttributeType_SysNick']) ) {
			$params['UslugaComplexAttributeType_SysNick'] = $data['UslugaComplexAttributeType_SysNick'];
			$where .= " and UCAT.UslugaComplexAttributeType_SysNick = :UslugaComplexAttributeType_SysNick";
		}

		if (!empty($data['UslugaTestStatuses'])) {
			$utStatuses = explode(",", $data['UslugaTestStatuses']);
			$isOk = in_array('ok', $utStatuses);
			$isExec = in_array('exec', $utStatuses);
			switch (true) {
				case $isOk && $isExec:
					$where .= " and (coalesce(UT.UslugaTest_ResultApproved,1) = 2 or UT.UslugaTest_ResultValue is not null)";
					break;
				case $isOk:
					$where .= " and coalesce(UT.UslugaTest_ResultApproved,1) = 2";
					break;
				case $isExec:
					$where .= " and UT.UslugaTest_ResultValue is not null";
					break;
			}
		}

		$query = "
			select distinct
				ELR.EvnLabRequest_id
			from v_EvnLabRequest ELR
			inner join v_EvnLabSample ELS on ELS.EvnLabRequest_id = ELR.EvnLabRequest_id
			inner join v_UslugaTest UT on UT.EvnLabSample_id = ELS.EvnLabSample_id
			inner join v_LabSampleStatus lss on LSS.LabSampleStatus_id = ELS.LabSampleStatus_id
			inner join v_UslugaComplexAttribute UCA on UCA.UslugaComplex_id = UT.UslugaComplex_id
			inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
			where
				ELR.EvnLabRequest_id in ({$data['EvnLabRequest_ids']})
				{$where}
		";
		//echo getDebugSQL($query); exit;
		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 * Устанавливаем/убираем флаг печати протокола исследования
	 * @param object идентификаторы (EvnDirection_id|EvnLabsample_id) для вложенного поиска EvnLabRequest_id
	 * @param int|null $isProtocolPrinted признак печати протокола
	 * @return bool 
	 */
	public function setProtocolPrintFlag($objectForSearch, $isProtocolPrinted = null) {
		$filter = 'and SampleStatusType_id in (7, 8)';

		if(empty($isProtocolPrinted)) {
			$filter = '';
		}

		if(array_key_exists('EvnDirections', $objectForSearch)) {
			$subquery = "
				select Evn_id 
				from EvnLabRequest 
				where EvnDirection_id in (" . implode(',', $objectForSearch['EvnDirections']) . ")
			";
		} else if(array_key_exists('EvnLabSamples', $objectForSearch)) {
			$subquery = "
				select distinct els.EvnLabRequest_id 
				from EvnLabSample els
				left join EvnLabRequest elr on els.EvnLabRequest_id = elr.Evn_id
				where els.Evn_id in (" . implode(',', $objectForSearch['EvnLabSamples']) . ")
			";
		} else if(array_key_exists('EvnLabRequest_id', $objectForSearch)) {
			$subquery = $objectForSearch['EvnLabRequest_id'];
		}

		$EvnLabRequest_id = $this->db->query("
			update EvnLabRequest 
			set EvnLabRequest_IsProtocolPrinted = :isProtocolPrinted
			where Evn_id in (
				{$subquery}
			) {$filter}
		", [
			'isProtocolPrinted' => $isProtocolPrinted
		]);

		return $EvnLabRequest_id;
	}
	
	/**
	 * Получение услуг, которые ещё не были отправлены в ЛИС
	*/
	function getLabRequestsForExport($data)
	{
		return $this->queryResult("
			with elr as (
				Select
					lr.EvnLabRequest_id
				from
					v_EvnLabRequest lr
					left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
				where
					lr.EvnStatus_id = 2 -- смотрим исследования со взятыми пробами
					and cast(lr.EvnLabRequest_prmTime as date) between cast(:endDate as date) and cast(:begDate as date)
							and lr.MedService_id = :MedService_id
				
				union
				Select
					lr.EvnLabRequest_id
				from
					v_EvnLabRequest lr
					left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
				where
					lr.EvnStatus_id = 2 -- смотрим исследования со взятыми пробами
					and cast(lr.EvnLabRequest_statusDate as date) between cast(:endDate as date) and cast(:begDate as date) and lr.EvnLabRequest_prmTime is null
							and lr.MedService_id = :MedService_id
				
				union
				Select
					lr.EvnLabRequest_id
				from
					v_EvnLabRequest lr 
					left join v_EvnDirection_all ed on lr.EvnDirection_id = ed.EvnDirection_id
				where
					lr.EvnStatus_id = 2 -- смотрим исследования со взятыми пробами
					and cast(lr.EvnLabRequest_statusDate as date) between cast(:endDate as date) and cast(:begDate as date)
						and lr.MedService_id = :MedService_id
			), eup as (
				select elruc.*
				from v_EvnLabRequestUslugaComplex elruc
                	inner join elr elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
			)
			
			select
				eup.EvnLabRequest_id as \"EvnLabRequest_id\",
				eup.UslugaComplex_id as \"UslugaComplex_id\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from eup
			where not exists (
				select *
				from lis.v_ResearchTransferList rtl
				where eup.EvnUslugaPar_id = rtl.EvnUslugaPar_id
			)
		", $data);
	}

	/**
	 * Список отменёных заявок
	 * @param $data
	 * @return array|bool|false
	 */
	function getCanceledEvnLabRequests($data)
	{
		$sql = "
		select 
			ELR.EvnDirection_id as \"EvnDirection_id\",
			COALESCE(PS.Person_SurName, '') || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"Person_Fio\",
			to_char(ELR.EvnLabRequest_prmTime,'dd.mm.yyyy') as \"EvnLabRequest_prmTime\",
			ELR.EvnLabRequest_UslugaName as \"EvnLabRequest_UslugaName\",
			ED.EvnDirection_Num as \"EvnDirection_Num\",
			to_char(ED.Evn_setDT,'dd.mm.yyyy') as \"EvnDirection_setDate\",
			vLPU.Lpu_Nick as \"Lpu_Nick\",
			vLS.LpuSection_Name as \"LpuSection_Name\",
			MP.Person_SurName as \"EDMedPersonalSurname\",
			vDFT.DirFailType_Name as \"DirFailType_Name\",
			vESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\"
		from EvnLabRequest ELR
			left join Evn E on ELR.Evn_id = E.Evn_id
			left join EvnDirection ED on ELR.EvnDirection_id = ED.Evn_id
			--получаем отклонившего и его тип службы
			left join v_MedServiceMedPersonal vMSMP on ED.MedStaffFact_fid = vMSMP.MedStaffFact_id
			left join v_MedService vMS on vMSMP.MedService_id = vMS.MedService_id
			left join v_MedServiceType vMST on vMS.MedServiceType_id = vMST.MedServiceType_id
			--инфо о направлении
			left join v_PersonState PS on PS.Person_id = ED.Person_id
			left join v_MedService MS on MS.MedService_id = ED.MedService_id
			left join v_Lpu vLPU on vLPU.Lpu_id = ED.Lpu_sid
			left join v_LpuSection vLS on vLS.LpuSection_id = ED.LpuSection_id
			left join lateral(
				select 
					Person_SurName 
				from 
					v_MedPersonal
				where 
					MedPersonal_id = ed.MedPersonal_id 
					and cast(dbo.tzgetdate() as date) between WorkData_begDate and coalesce(WorkData_endDate, dbo.tzgetdate())
				limit 1
			) MP on true
			--инфо по отменёной заявке
			left join v_DirFailType vDFT on vDFT.DirFailType_id = ED.DirFailType_id
			left join v_EvnStatusHistory vESH on vESH.Evn_id = ED.Evn_id and vESH.EvnStatusCause_id is not null
		where
			E.Evn_deleted = 2
			and ED.Evn_id IS NOT NULL
			and (
				vMSMP.MedStaffFact_id IS NULL 
				or vMST.MedServiceType_SysNick = 'lab' 
				or vMST.MedServiceType_SysNick = 'pzm'
				or vMST.MedServiceType_SysNick = 'reglab'
			)
			and cast(E.Evn_delDT as date) between :begDate and :endDate
			and (ELR.MedService_id = :MedService_id or ELR.MedService_sid = :MedService_id)
		";
		return $this->queryResult($sql, $data);
	}
}
