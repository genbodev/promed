<?php
require_once('Collection_model.php');
/**
 * Модель Пробы на лабораторное исследование
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
 * @property int      EvnLabSample_id
 * @property int      EvnLabRequest_id
 * @property int      EvnLabSample_pid
 * @property int      EvnLabSample_rid
 * @property int      Lpu_id
 * @property int      Server_id
 * @property int      PersonEvn_id
 * @property datetime EvnLabSample_setDT
 * @property datetime EvnLabSample_disDT
 * @property datetime EvnLabSample_didDT
 * @property datetime EvnLabSample_insDT
 * @property datetime EvnLabSample_updDT
 * @property int      EvnLabSample_Index
 * @property int      EvnLabSample_Count
 * @property int      Morbus_id
 * @property int      EvnLabSample_IsSigned
 * @property int      pmUser_signID
 * @property datetime EvnLabSample_signDT
 * @property int      EvnDirection_id
 * @property int      UslugaExecutionType_id
 * @property string   EvnLabSample_Comment
 * @property int      EvnLabRequest_Num
 * @property int      RefSample_id
 * @property int      Lpu_did
 * @property int      LpuSection_did
 * @property int      MedPersonal_did
 * @property int      MedPersonal_sdid
 * @property datetime EvnLabSample_DelivDT
 * @property int      Lpu_aid
 * @property int      LpuSection_aid
 * @property int      MedPersonal_aid
 * @property int      MedPersonal_said
 * @property int      LabSampleDefectiveType_id
 * @property int      pmUser_id
 */
class EvnLabSample_model extends Collection_model
{
	protected $fields = array();//устанавливается в конструкторе с помощью setInputRules

	public $EvnUsluga_Result_Json;
	public $Person_id;
	private $sessionParams = [];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->setInputRules(array(
			array('field'=>'EvnLabSample_id'          ,'label' => 'EvnLabSample_id'                       ,'rules' => '', 'type' => 'int'),
			array('field'=>'EvnLabSample_pid'         ,'label' => 'EvnLabSample_pid'                      ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_rid'         ,'label' => 'EvnLabSample_rid'                      ,'rules' => '', 'type' => 'id'),
			array('field'=>'Lpu_id'                   ,'label' => 'Lpu_id'                                ,'rules' => '', 'type' => 'id'),
			array('field'=>'Server_id'                ,'label' => 'Server_id'                             ,'rules' => '', 'type' => 'int'),
			array('field'=>'PersonEvn_id'             ,'label' => 'PersonEvn_id'                          ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_setDT'       ,'label' => 'EvnLabSample_setDT'                    ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabSample_disDT'       ,'label' => 'EvnLabSample_disDT'                    ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabSample_didDT'       ,'label' => 'EvnLabSample_didDT'                    ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabSample_insDT'       ,'label' => 'EvnLabSample_insDT'                    ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabSample_updDT'       ,'label' => 'EvnLabSample_updDT'                    ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabSample_Index'       ,'label' => 'EvnLabSample_Index'                    ,'rules' => '', 'type' => 'int'),
			array('field'=>'EvnLabSample_Count'       ,'label' => 'EvnLabSample_Count'                    ,'rules' => '', 'type' => 'int'),
			array('field'=>'Morbus_id'                ,'label' => 'Morbus_id'                             ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_IsSigned'    ,'label' => 'EvnLabSample_IsSigned'                 ,'rules' => '', 'type' => 'id'),
			array('field'=>'pmUser_signID'            ,'label' => 'pmUser_signID'                         ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_signDT'      ,'label' => 'EvnLabSample_signDT'                   ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'EvnLabRequest_id'         ,'label' => 'Заявка на лабораторное исследование'   ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_Num'         ,'label' => 'Номер пробы'                           ,'rules' => '', 'type' => 'string'),
			array('field'=>'EvnLabSample_BarCode'         ,'label' => 'Штрих-код пробы'                           ,'rules' => '', 'type' => 'string'),
			array('field'=>'EvnLabSample_Comment'         ,'label' => 'Комментарий'                           ,'rules' => '', 'type' => 'string'),
			array('field'=>'RefSample_id'             ,'label' => 'Справочник проб'                       ,'rules' => '', 'type' => 'id'),
			array('field'=>'Lpu_did'                  ,'label' => 'ЛПУ взявшее пробу'                     ,'rules' => '', 'type' => 'id'),
			array('field'=>'LpuSection_did'           ,'label' => 'Отделение взявшее пробу'               ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedPersonal_did'          ,'label' => 'Врач взявший пробу'                    ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedPersonal_sdid'         ,'label' => 'Средний медперсонал взявший пробу'     ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedService_id'           ,'label' => 'Служба заявки'						  ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedService_did'          ,'label' => 'Служба взявшая пробу'   				  ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedService_sid'          ,'label' => 'Текущая служба'						  ,'rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_DelivDT'     ,'label' => 'Дата и время доставки пробы'           ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'Lpu_aid'                  ,'label' => 'ЛПУ выполнившее анализ'                ,'rules' => '', 'type' => 'id'),
			array('field'=>'LpuSection_aid'           ,'label' => 'Отделение выполнившее анализ'          ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedPersonal_aid'          ,'label' => 'Врач выполнивший анализ'               ,'rules' => '', 'type' => 'id'),
			array('field'=>'MedPersonal_said'         ,'label' => 'Средний медперсонал выполнивший анализ','rules' => '', 'type' => 'id'),
			array('field'=>'EvnLabSample_StudyDT'     ,'label' => 'Дата и время выполнения исследования'  ,'rules' => '', 'type' => 'datetime'),
			array('field'=>'LabSampleDefectiveType_id','label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'),
			array('field'=>'DefectCauseType_id','label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'),
			array('field'=>'Analyzer_id','label' => 'Анализатор','rules' => '', 'type' => 'id'),
			array('field'=>'pmUser_id'             ,'label' => 'идентификатор пользователя системы Промед','rules' => '', 'type' => 'id'),//
			array('field'=>'RecordStatus_Code'     ,'label' => 'идентификатор состояния записи','rules' => '', 'type' => 'int'),//
			array('field'=>'LabSample_Results'         ,'label' => 'Результаты пробы'                           ,'rules' => '', 'type' => 'string', 'onlyRule' => true),
		));

		$this->load->swapi('common');
		$this->load->library('textlog', array('file'=>'EvnLabSample_'.date('Y-m-d').'.log'), 'elslog');
	}

	/**
	 * Получение количества проб из лис с результатами
	 */
	function getEvnLabSampleFromLisWithResultCount($data)
	{
		$query = "
			select
				count(els.EvnLabSample_id) as cnt
			from
				v_EvnLabSample els
			where
				els.MedService_id = :MedService_id
				and exists(select Link_id from lis.v_Link where link_object = 'EvnLabSample' and object_id = els.EvnLabSample_id limit 1) -- отправлялась на анализатор
				and els.EvnLabSample_StudyDT IS NOT NULL -- получен результат
				and cast(els.EvnLabSample_StudyDT as date) = cast(dbo.tzgetdate() as date)
			limit 1
		";

		$count = $this->getFirstResultFromQuery($query, $data);
		if ($count === false) {
			return $this->createError('','Ошибка при получении количества проб из лис с результатами');
		}
		return array(array(
			'success' => true,
			'cnt' => $count
		));
	}

	/**
	 * Получение нового номера пробы
	 */
	function getNewLabSampleNum($data, $beginningOfNumbering = 0) {
		//Получаем список незакрытых проб данной ЛПУ и формируем из них массив
		$in_work_samples = $this->getInWorkSamples(array(
			'MedService_id' => $data['MedService_id']
		));
		$in_work_samples_array = array();
		foreach ($in_work_samples as $key => $value) {
			foreach ($value as $key2 => $value2) {
				array_push($in_work_samples_array, substr($value2, 5, 8));
			}
		}
		$this->elslog->add('Получили список незакрытых проб в службе и сформировали из них массив | Всего незакрытых проб = '.count($in_work_samples));
		// Получаем индивидуальный номер лаборатории
		$resp_ms = $this->getMedServiceCode(array(
			'MedService_id' => $data['MedService_id']
		));
		// Генерируем номер
		return $this->generateLabSampleNum(array(
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'MedService_Code' => $resp_ms[0]['MedService_Code']
		), $in_work_samples_array, 0, $beginningOfNumbering);
	}

	/**
	 * Генерация номера пробы и проверка отсту
	 */
	function generateLabSampleNum($data, $in_work_samples_array, $count = 0, $beginningOfNumbering = 0)
	{
		// генерируем номер пробы
		$this->load->library('swMongoExt');
		$this->elslog->add('Подключили swMongoExt');

		// count для защиты от зацикливания.
		$count_cycle = 0;
		$min = ($beginningOfNumbering) ? (int)$beginningOfNumbering : 1;
		do {
			$k = $this->swmongoext->generateCode('Samples','day', array('Lpu_id'=> $data['Lpu_id'], 'MedService_id'=> $data['MedService_id']), $min);
			$this->elslog->add('Выполнили swmongoext->generateCode | Lpu_id='.$data['Lpu_id'].' | MedService_id='.$data['MedService_id']);
			$LabSample_Num = substr($k, -4);
			if(!$beginningOfNumbering) $LabSample_Num = intval($LabSample_Num) + 1000;

			// увеличиваем счётчик локально без обращения к монго
			$currentNum = $LabSample_Num;
			while (in_array(str_pad($data['MedService_Code'], 4, 0, STR_PAD_LEFT).$currentNum, $in_work_samples_array) && $count_cycle < 10000) {
				$k++;
				$currentNum = substr($k, -4);
				$currentNum = intval($currentNum) + 1000;
				$min = $k;

				$count_cycle++;
			}
		} while (in_array(str_pad($data['MedService_Code'], 4, 0, STR_PAD_LEFT).$LabSample_Num, $in_work_samples_array) && $count_cycle < 10000);

		if ($count_cycle == 10000) {
			$this->elslog->add('Ошибка генерации номера пробы');
			return array('Error_Msg' => 'Ошибка генерации номера пробы');
		}

		// @task https://redmine.swan-it.ru/issues/191166
		$dayInc = 0;
		$yearPart = substr(date('Y'), -1);

		if ( '0' == $yearPart ) {
			$dayInc = 500;
			$yearPart = '9';
		}

		$EvnLabSample_Num = $yearPart . str_pad((Date('z') + 1 + $dayInc), 3, 0, STR_PAD_LEFT) . str_pad($data['MedService_Code'], 4, 0, STR_PAD_LEFT) . str_pad($LabSample_Num, 4, 0, STR_PAD_LEFT);

		// проверяем чтоб не было за сегодня пробы с таким же номером, если есть генерируем новый
		$sql_check = "
			select
				EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample
			where
				EvnLabSample_Num = :EvnLabSample_Num
				and cast(EvnLabSample_setDT as date) = cast(dbo.tzgetdate() as date)
            limit 1
			";
		$res_check = $this->db->query($sql_check, array(
			'EvnLabSample_Num' => $EvnLabSample_Num
		));
		$this->elslog->add('Проверили что нет за сегодня пробы с таким же номером');
		if ( !is_object($res_check) ) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей номеров пробы)');
		}
		$resp_check = $res_check->result('array');
		if ( is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]['EvnLabSample_id']) ) {
			// есть уже проба с таким номером, генерируем новый номер
			$count++;
			if ($count > 10) {
				$this->elslog->add('Ошибка получения номера пробы');
				return array('Error_Msg' => 'Ошибка получения номера пробы');
			}
			return $this->generateLabSampleNum($data, $in_work_samples_array, $count);
		}

		return $EvnLabSample_Num;
	}

	/**
	 * Взятие пробы
	 */
	function takeLabSample($data)
	{
		$resp = false;
		$this->elslog->add('Берем пробы | EvnLabSample_id='.$data['EvnLabSample_id']);

		//$this->db->trans_begin();

		// 0. для начала надо проверить, а не создана ли уже проба
		$sql_check = "
			select
				EvnLabSample_setDT  as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample
			where
				EvnLabSample_id = :EvnLabSample_id
				limit 1";
		$res_check = $this->db->query($sql_check, $data);
		if ( !is_object($res_check) ) {
			//$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей)');
		}
		$resp_check = $res_check->result('array');
		if ( is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]['EvnLabSample_setDT']) ) {
			//$this->db->trans_rollback();
			return array('Error_Msg' => 'Проба уже взята');
		}
		$this->elslog->add('Проверили не создана ли проба ранее | EvnLabRequest_id='.$data['EvnLabRequest_id']);

		// чтобы не запустили ещё раз взятие пробы, апдейтим дату взятия сразу.
		$this->db->query("
			with myvars as (
				select
					evnlabsample_pid
				from v_evnlabsample
				where evnlabsample_id = :EvnLabSample_id
			)
			update
				evn
			set
				Evn_setDT = dbo.tzGetDate()
			where
				Evn_id = (select evnlabsample_pid from myvars)
		", array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));
		$this->elslog->add('Проапдейтили дату взятия пробы | EvnLabSample_id='.$data['EvnLabSample_id']);

		// 1. получаем данные заявки
		$lrdata = $this->getDataFromEvnLabRequest(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));
		$lrdata['MedPersonal_id'] = $data['session']['medpersonal_id'];

		$this->elslog->add('Получили данные заявки | EvnLabRequest_id='.$data['EvnLabRequest_id']);

		// определяем Анализатор
		$data['Analyzer_id'] = null;
		$sendToLis = false;

		// получаем состав
		$tests = $this->getLabSampleResultGrid(array(
			'Lpu_id' => $lrdata['Lpu_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'EvnDirection_id' => $lrdata['EvnDirection_id']
		));

		if (empty($tests)) {
			//$this->db->trans_rollback();
			return array('Error_Msg' => 'Нельзя взять пробу, т.к. в ней отсутствуют исследования');
		}

		$this->elslog->add('Получили состав | EvnLabRequest_id='.$data['EvnLabRequest_id'].' | Всего тестов = '.count($tests));

		if (empty($data['Analyzer_id']) ) {
			$uccodes = array();
			foreach($tests as $test) {
				if (!in_array($test['UslugaComplex_id'], $uccodes) && $test['UslugaTest_Status'] != 'Не назначен') {
					$uccodes[] = array(
						'UslugaComplexTest_id' => $test['UslugaComplex_id'],
						'UslugaComplexTarget_id' => $test['UslugaComplexTarget_id']
					);
				}
			}

			// получаем список возможных анализаторов
			$this->load->model('Analyzer_model');
			$resp_analyzer = $this->Analyzer_model->loadList(array(
				'Analyzer_IsNotActive' => 1,
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'uccodes' => $uccodes,
				'MedService_id' => $lrdata['MedService_id']
			));
			if (count($resp_analyzer) == 1) {
				$data['Analyzer_id'] = $resp_analyzer[0]['Analyzer_id'];
				if ($resp_analyzer[0]['pmUser_insID'] != 1 || $resp_analyzer[0]['Analyzer_Code'] != '000') { // если не ручные методики
					$sendToLis = true;
				}
			} elseif (count($resp_analyzer) > 1) {
				if ($resp_analyzer[0]['pmUser_insID'] == 1 && $resp_analyzer[0]['Analyzer_Code'] == '000') { // если первый ручные методики
					$data['Analyzer_id'] = $resp_analyzer[1]['Analyzer_id']; // тогда берём второй
					$sendToLis = true;
				} else { // иначе берём первый
					$data['Analyzer_id'] = $resp_analyzer[0]['Analyzer_id']; // тогда берём первый
					$sendToLis = true;
				}
			}
			$this->elslog->add('Получили список возможных анализаторов и выбрали анализатор | EvnLabRequest_id='.$data['EvnLabRequest_id'].' | Analyzer_id='.$data['Analyzer_id']);
		}

		$EvnLabSample_DelivDTField = "(select curtime from myvars)";
		if (!empty($data['MedServiceType_SysNick']) && in_array($data['MedServiceType_SysNick'], array('pzm'))) {
			$EvnLabSample_DelivDTField = "null";
		}

		$UslugaComplexTargetIds = array();
		foreach($tests as $test) {
			if (empty($test['UslugaComplexTarget_id'])) continue;
			if (!in_array($test['UslugaComplexTarget_id'], $UslugaComplexTargetIds)) {
				$UslugaComplexTargetIds[] = $test['UslugaComplexTarget_id'];
			}
		}

		if (!empty($UslugaComplexTargetIds)) {
			$resp_rs = $this->queryResult("
				select
					COALESCE(ucms_child.RefSample_id, ucms.RefSample_id) as \"RefSample_id\"
				from
					v_UslugaComplexMedService ucms
					left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
				where
					ucms.UslugaComplex_id IN ('".implode("','", $UslugaComplexTargetIds)."')
					and ucms.MedService_id = :MedService_id
					and ucms.UslugaComplexMedService_pid is null
					and COALESCE(ucms_child.RefSample_id, ucms.RefSample_id) is not null
				limit 1
			", array(
				'MedService_id' => $lrdata['MedService_id'],
				'UslugaComplex_id' => $lrdata['UslugaComplex_id']
			));

			if (!empty($resp_rs[0]['RefSample_id'])) {
				$data['RefSample_id'] = $resp_rs[0]['RefSample_id'];
			}
		}

		$this->elslog->add('Получили биоматериал | EvnLabRequest_id=' . $data['EvnLabRequest_id']);

		$resp_num = $this->getNewLabSampleNum(array(
			'Lpu_id' => $lrdata['Lpu_id'],
			'MedService_id' => $lrdata['MedService_id']
		));

		if (is_array($resp_num)) {
			return $resp_num;
		}

		$data['EvnLabSample_Num'] = $resp_num;

		$this->elslog->add('Сгенерировали номер | EvnLabRequest_id='.$data['EvnLabRequest_id'].' | EvnLabSample_Num='.$resp_num);

		// создаём пробу в заявке и проставляем врача/отделение/дату взятия
		$query = "
			with myvars as (
				select
					dbo.tzgetdate() as curtime,
					MedService_id
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
				limit 1
			)

			select
				evnlabsample_id as \"EvnLabSample_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				to_char ((select curtime from myvars), 'YYYY-MM-DD HH24:MI:SS') as \"EvnLabSample_setDTForBD\",
            	to_char((select curtime from myvars), 'HH24:MI dd.mm.yyyy') as \"EvnLabSample_setDT\"
			from p_EvnLabSample_upd(
				evnlabsample_id := :EvnLabSample_id,
				evnlabrequest_id := :EvnLabRequest_id,
				refsample_id := :RefSample_id,
				lpu_id := :Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				evnlabsample_num := :EvnLabSample_Num,
				evnlabsample_barcode := :EvnLabSample_BarCode,
				medservice_id := (select MedService_id from myvars),
				medservice_did := :MedService_did,
				evnlabsample_setdt := (select curtime from myvars),
				evnlabsample_delivdt := {$EvnLabSample_DelivDTField},
				lpu_did := :Lpu_did,
				analyzer_id := :Analyzer_id,
				lpusection_did := :LpuSection_did,
				medpersonal_did := :MedPersonal_did,
				pmuser_id := :pmUser_id
			)
		";
		if (!empty($data['session']['CurLpuSection_id'])) {
			$data['Lpu_did'] = $data['session']['lpu_id'];
			$data['LpuSection_did'] = $data['session']['CurLpuSection_id'];
			$data['MedPersonal_did'] = (!empty($data['methodAPI']) && !empty($data['MedPersonal_did'])) ? $data['MedPersonal_did'] : $data['session']['medpersonal_id'];
		} else {
			$data['Lpu_did'] = $lrdata['Lpu_id'];
			$data['LpuSection_did'] = $lrdata['LpuSection_id'];
			$data['MedPersonal_did'] = (!empty($data['methodAPI']) && !empty($data['MedPersonal_did'])) ? $data['MedPersonal_did'] : $lrdata['MedPersonal_id'];
		}
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' => $lrdata['MedPersonal_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'RefSample_id' => $data['RefSample_id'],
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'EvnLabSample_Num' => $data['EvnLabSample_Num'],
			'EvnLabSample_BarCode' => $data['EvnLabSample_Num'],
			'MedService_did' => $data['MedService_did'],
			'Lpu_id' => $lrdata['Lpu_id'],
			'Lpu_did' => $data['Lpu_did'],
			'LpuSection_did' => $data['LpuSection_did'],
			'MedPersonal_did' => $data['MedPersonal_did'],
			'Analyzer_id' => $data['Analyzer_id'],
			'LpuSection_id' => $lrdata['LpuSection_id'],
			'PersonEvn_id' => $lrdata['PersonEvn_id'],
			'Server_id' => $lrdata['Server_id']
		);

		$result = $this->db->query($query, $params);

		// проверяем чтоб не было за сегодня пробы с таким же номером
		$sql_check = "
			select
				EvnLabSample_id as \"EvnLabSample_id\"
			from v_EvnLabSample
			where
				EvnLabSample_id <> :EvnLabSample_id
				and EvnLabSample_Num = :EvnLabSample_Num
				and cast(EvnLabSample_setDT as date) = cast(dbo.tzgetdate() as date)
            limit 1
			";
		$res_check = $this->db->query($sql_check, array(
			'EvnLabSample_Num' => $data['EvnLabSample_Num'],
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));
		$this->elslog->add('Повторно проверили что нет за сегодня пробы с таким же номером | EvnLabSample_id='.$data['EvnLabSample_id']);
		if ( !is_object($res_check) ) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих записей номеров пробы)');
		}
		$resp_check = $res_check->result('array');
		if ( is_array($resp_check) && count($resp_check) > 0 && !empty($resp_check[0]['EvnLabSample_id']) ) {
			$this->elslog->add('Обнаружен дубль | EvnLabSample_id='.$data['EvnLabSample_id']);
			return array('Error_Msg' => 'Обнаружено дублирование номеров проб, необходимо повторить взятие пробы');
		}

		if (is_object($result)) {
			$resp = $result->result('array');

			if (!is_array($resp)) {
				return $this->createError('','Ошибка при изменение пробы');
			}
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);

			$this->elslog->add('Создали пробу в заявке и проставили врача/отделение/дату взятия | EvnLabRequest_id='.$data['EvnLabRequest_id']);

			if (!empty($resp[0]['EvnLabSample_id'])) {

				//проверяем на дубли по штрих-кодам - такое редко, но встречается
				$EvnLabSampleNumCheck = $this->getFirstResultFromQuery("
					select
						els.Evn_id as \"EvnLabSample_id\"
					from
						EvnLabSample els
						inner join v_Evn e on e.Evn_id = els.Evn_id
					where els.EvnLabSample_Num = :EvnLabSample_Num
						and e.Evn_id != :EvnLabSample_id
						and cast(e.Evn_insDT as date) = cast(dbo.tzgetdate() as date)
					limit 1
				", array('EvnLabSample_id' => $resp[0]['EvnLabSample_id'],'EvnLabSample_Num' => $data['EvnLabSample_Num']));

				if (!empty($EvnLabSampleNumCheck)){
					return array('Error_Msg' => 'У пробы не уникальный штрих-код, пожалуйста возьмите пробу ещё раз.');
				}

				// сохраняем в пробе список тестов (с минимальным набором полей)
				$data['EvnLabSample_id'] = $resp[0]['EvnLabSample_id'];
				$data['EvnLabSample_setDT'] = $resp[0]['EvnLabSample_setDTForBD'];
				if ($data['MedServiceType_SysNick'] != 'microbiolab') {
					$this->saveLabSampleTests($data, $lrdata, null, $tests);
				}
				$this->elslog->add('Сохранили все тесты по созданной пробе (saveLabSampleTests) | EvnLabRequest_id='.$data['EvnLabRequest_id'].' | EvnLabSample_id='.$data['EvnLabSample_id']);
				$resp[0]['EvnLabSample_ShortNum'] = substr($data['EvnLabSample_Num'], -4);
				$resp[0]['EvnLabSample_BarCode'] = $data['EvnLabSample_Num'];
				$this->ReCacheLabSampleStatus(array(
					'EvnLabSample_id' => $resp[0]['EvnLabSample_id']
				));
				$this->elslog->add('Перекэшировали статусы (ReCacheLabSampleStatus) | EvnLabRequest_id='.$data['EvnLabRequest_id'].' | EvnLabSample_id='.$data['EvnLabSample_id']);
			} else {
				return $resp;
			}
		}

		// кэшируем статус заявки
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		$this->elslog->add('Закэшировали статус заявки (ReCacheLabRequestStatus) | EvnLabRequest_id='.$data['EvnLabRequest_id']);

		// кэшируем статус проб в заявке
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		$this->elslog->add('Закэшировали статус проб в заявке (ReCacheLabRequestSampleStatusType) | EvnLabRequest_id='.$data['EvnLabRequest_id']);

		// а это вне транзакцию можно делать, раз проба уже сохранена.
		if (!empty($data['sendToLis']) && !empty($data['Analyzer_id']) && $sendToLis) {
			$this->load->helper('Xml');
			// отправляем в АС МЛО
			$this->load->model('AsMlo_model', 'lab_model');
			$creation = $this->lab_model->createRequest2($data, true);
			if (is_array($creation) && !empty($creation['Error_Msg'])) {
				$resp[0]['Alert_Msg'] = $creation['Error_Msg'];
			}
			$this->elslog->add('Отправили в АС МЛО | EvnLabRequest_id='.$data['EvnLabRequest_id']);
		}
		$this->elslog->add("Финиш | EvnLabRequest_id=".$data['EvnLabRequest_id']."\n");

		return $resp;
	}

	/**
	 * Сохранение пробы
	 */
	function saveLabSample($data)
	{
		// 1. получаем данные заявки
		$lrdata = $this->getDataFromEvnLabRequest(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));
		// создаём пробу в заявке
		$query = "
            select
            	evnlabsample_id as \"EvnLabSample_id\",
            	error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
			from p_EvnLabSample_ins(
				EvnLabRequest_id := :EvnLabRequest_id,
				RefSample_id := :RefSample_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnLabSample_Num := null,
				EvnLabSample_BarCode := null,
				MedService_id := :MedService_id,
				MedService_did := null,
				Evnlabsample_setDT := null,
				EvnLabSample_DelivDT := null,
				Lpu_did := null,
				Analyzer_id := null,
				LpuSection_did := null,
				MedPersonal_did := null,
				Lpu_aid := null,
				LpuSection_aid := null,
				MedPersonal_aid := null,
				MedPersonal_said := null,
				EvnLabSample_StudyDT := null,
				pmUser_id := :pmUser_id
            )
		";

		if (!empty($data['MedService_id'])) {
			// Lpu_id должно быть равно МО лаборатории, иначе в лаборатории не отобразится и может некорректно считаться номер пробы
			$data['Lpu_id'] = $this->getFirstResultFromQuery("
				select
					Lpu_id as \"Lpu_id\"
				from
					v_MedService
				where
					MedService_id = :MedService_id
			", array(
				'MedService_id' => $data['MedService_id']
			));
			if (empty($data['Lpu_id'])) {
				return $this->createError('','Ошибка при получении МО службы');
			}
		}

		$result = $this->queryResult($query, array(
			'pmUser_id' => $data['pmUser_id'],
			'RefSample_id' => $data['RefSample_id'],
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'MedService_id' => $data['MedService_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonEvn_id' => $lrdata['PersonEvn_id'],
			'Server_id' => $lrdata['Server_id']
		));

		if (!is_array($result)) {
			return $this->createError('','Ошибка при создании заявки');
		}
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		collectEditedData('ins', 'EvnLabSample', $result[0]['EvnLabSample_id']);
		return $result;
	}

	/**
	 * Сохранение результата контрольного измерения
	 */
	function saveQcSampleTest($data) {

		$params = [
			'UslugaTest_ResultValue' => $data['UslugaTest_ResultValue'],
			'UslugaTest_SetDT' => $data['UslugaTest_SetDT'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $this->getPromedUserId()
		];

		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				Error_Message as \"Error_Msg\",
				Error_Code as \"Error_Code\"
			from p_UslugaTest_ins(
				UslugaTest_ResultValue := :UslugaTest_ResultValue,
				UslugaTest_SetDT := :UslugaTest_SetDT,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->getFirstRowFromQuery($query, $params);
		return $result;
	}

	/**
	 * Загрузка списка тестов для пробы и сохранение их во взятой пробе
	 */
	function saveLabSampleTests($data, $lrdata, $needtests = null, $tests = null) {
		// 1. загружаем список тестов
		if (empty($tests)) {
			$tests = $this->getLabSampleResultGrid(array(
				'Lpu_id' => $data['Lpu_id'],
				'EvnDirection_id' => $lrdata['EvnDirection_id'],
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'needtests' => $needtests,
				'ingorePrescr' => !empty($data['ingorePrescr'])?true:false
			));
		}

		// 2. получаем родительские услуги пробы
		$EvnUslugaPars = $this->getEvnUslugasRoot(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));
		foreach($EvnUslugaPars as $EvnUslugaPar) {
			$UslugaTest_pid = $EvnUslugaPar['EvnUslugaPar_id'];

			if (is_array($tests)) {
				$count = 0;
				$EvnUslugaRootXmlDataArray = array();

				$data['EvnPrescr_id'] = null;
				$data['PersonData'] = null;
				if (count($tests) > 0 && !empty($data['Analyzer_id'])) {
					// получаем необходимые данные для получения реф.значений
					$res = $this->common->GET('EvnPrescr/PrescrByDirection', $lrdata, 'single');
					if (!$this->isSuccessful($res)) {
						throw new Exception($res['Error_Msg']);
					}
					$data['EvnPrescr_id'] = $res['EvnPrescr_id'];

					if (empty($data['EvnPrescr_id'])) {
						$data['EvnPrescr_id'] = null;
					}

					if (!empty($data['EvnLabSample_setDT'])) {
						$data['EvnLabSample_setDT'] = explode(' ', $data['EvnLabSample_setDT'])[0];
					}

					$dt = [
						'Person_id' => $lrdata['Person_id'],
						'EvnPrescr_id' => $data['EvnPrescr_id'],
						'EvnLabSample_setDT' => $data['EvnLabSample_setDT']
					];
					$person = $this->common->GET('Person/PersonDataForRefValues', $dt, 'single');
					if (!$this->isSuccessful($person)) {
						throw new Exception($person['Error_Msg']);
					}
					$data['PersonData'] = $person;
				}

				$UslugaComplex_ids = array();
				foreach ($tests as $test) {
					if (empty($test['UslugaTest_pid'])) continue;
					if ($test['UslugaTest_pid'] == $UslugaTest_pid) {
						$UslugaComplex_ids[] = $test['UslugaComplex_id'];
					}
				}

				$refvalues = array();
				$resp_refvalues = $this->loadRefValues(array(
					'EvnLabSample_setDT' => $data['EvnLabSample_setDT'],
					'MedService_id' => $lrdata['MedService_id'],
					'EvnDirection_id' => $lrdata['EvnDirection_id'],
					'Person_id' => $lrdata['Person_id'],
					'UslugaComplexTarget_id' => $EvnUslugaPar['UslugaComplex_id'],
					'UslugaComplex_ids' => json_encode($UslugaComplex_ids),
					'Analyzer_id' => $data['Analyzer_id'],
					'EvnPrescr_id' => $data['EvnPrescr_id'],
					'PersonData' => $data['PersonData']
				));

				foreach ($resp_refvalues as $refvalue) {
					$refvalues[$refvalue['UslugaComplex_id']] = $refvalue;
				}

				foreach ($tests as $test) {
					if (empty($test['UslugaTest_pid'])) continue;
					if ($test['UslugaTest_pid'] == $UslugaTest_pid) {
						if (empty($test['UslugaTest_id']) && $test['inPrescr'] == 2) {
							$data['RefValues_id'] = null;
							$data['Unit_id'] = null;
							$data['UslugaTest_ResultQualitativeNorms'] = null;
							$data['UslugaTest_ResultQualitativeText'] = null;
							$data['UslugaTest_ResultLower'] = null;
							$data['UslugaTest_ResultUpper'] = null;
							$data['UslugaTest_ResultLowerCrit'] = null;
							$data['UslugaTest_ResultUpperCrit'] = null;
							$data['UslugaTest_ResultUnit'] = null;
							$data['UslugaTest_Comment'] = null;
							$data['LabTest_id'] = null;
							if (!empty($data['Analyzer_id'])) {
								if (!empty($refvalues[$test['UslugaComplex_id']]['AnalyzerTestRefValues_id'])) {
									$data['RefValues_id'] = $refvalues[$test['UslugaComplex_id']]['RefValues_id'];
									$data['Unit_id'] = $refvalues[$test['UslugaComplex_id']]['Unit_id'];
									$data['UslugaTest_ResultQualitativeNorms'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultQualitativeNorms'];
									$UslugaTest_ResultQualitativeText = '';
									if (!empty($refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultQualitativeNorms'])) {
										$UslugaTest_ResultQualitativeNorms = json_decode($refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultQualitativeNorms'], true);
										if (is_array($UslugaTest_ResultQualitativeNorms)) {
											foreach ($UslugaTest_ResultQualitativeNorms as $UslugaTest_ResultQualitativeNorm) {
												if (!empty($UslugaTest_ResultQualitativeText)) {
													$UslugaTest_ResultQualitativeText .= ', ';
												}
												$UslugaTest_ResultQualitativeText .= $UslugaTest_ResultQualitativeNorm;
											}
										}
									}
									$data['UslugaTest_ResultQualitativeText'] = $UslugaTest_ResultQualitativeText;
									$data['UslugaTest_ResultLower'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLower'];
									$data['UslugaTest_ResultUpper'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUpper'];
									$data['UslugaTest_ResultLowerCrit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLowerCrit'];
									$data['UslugaTest_ResultUpperCrit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUpperCrit'];
									$data['UslugaTest_ResultUnit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUnit'];
									$data['UslugaTest_Comment'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_Comment'];
									$data['LabTest_id'] = (!empty($refvalues[$test['UslugaComplex_id']]['LabTest_id']))?$refvalues[$test['UslugaComplex_id']]['LabTest_id']:null;
								} else {
									// получаем базовую единицу измерения
									$this->load->model('LisSpr_model');
									$resp_unit = $this->LisSpr_model->loadTestUnitList(array(
										'UslugaComplexTest_id' => $test['UslugaComplex_id'],
										'UslugaComplexTarget_id' => $EvnUslugaPar['UslugaComplex_id'],
										'UnitOld_id' => null,
										'MedService_id' => $lrdata['MedService_id'],
										'Analyzer_id' => $data['Analyzer_id'],
										'QuantitativeTestUnit_IsBase' => 2
									));
									if (!empty($resp_unit[0]['Unit_id'])) {
										$data['Unit_id'] = $resp_unit[0]['Unit_id'];
										$data['UslugaTest_ResultUnit'] = $resp_unit[0]['Unit_Name'];
									}
								}
							}
							$count++;
							$this->saveUslugaTest(array(
								'UslugaTest_id' => null,
								'PersonEvn_id' => $lrdata['PersonEvn_id'],
								'Server_id' => $lrdata['Server_id'],
								'Lpu_id' => $lrdata['Lpu_id'],
								'UslugaComplex_id' => $test['UslugaComplex_id'], // UslugaComplex_id
								'PayType_id' => $lrdata['PayType_id'],
								'UslugaTest_pid' => $UslugaTest_pid,
								'EvnLabSample_id' => $data['EvnLabSample_id'],
								'EvnDirection_id' => null,
								'ResultDataJson' => json_encode(array(
									'EUD_value' => null,
									'EUD_lower_bound' => toUtf(trim($data['UslugaTest_ResultLower'])),
									'EUD_upper_bound' => toUtf(trim($data['UslugaTest_ResultUpper'])),
									'EUD_unit_of_measurement' => toUtf(trim($data['UslugaTest_ResultUnit']))
								)),
								'UslugaTest_ResultLower' => $data['UslugaTest_ResultLower'],
								'UslugaTest_ResultUpper' => $data['UslugaTest_ResultUpper'],
								'UslugaTest_ResultLowerCrit' => $data['UslugaTest_ResultLowerCrit'],
								'UslugaTest_ResultUpperCrit' => $data['UslugaTest_ResultUpperCrit'],
								'UslugaTest_ResultQualitativeNorms' => $data['UslugaTest_ResultQualitativeNorms'],
								'UslugaTest_ResultQualitativeText' => $data['UslugaTest_ResultQualitativeText'],
								'RefValues_id' => $data['RefValues_id'],
								'Unit_id' => $data['Unit_id'],
								'UslugaTest_ResultValue' => null,
								'UslugaTest_ResultUnit' => $data['UslugaTest_ResultUnit'],
								'UslugaTest_ResultApproved' => null,
								'UslugaTest_CheckDT' => isset($data['UslugaTest_CheckDT']) ? $data['UslugaTest_CheckDT'] : null,
								'UslugaTest_Comment' => $data['UslugaTest_Comment'],
								'LabTest_id' => $data['LabTest_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!empty($test['UslugaComplex_ACode'])) {
								$EvnUslugaRootXmlDataArray[$test['UslugaComplex_ACode']] = null;
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Массовое одобрение результатов проб
	 */
	function approveEvnLabSampleResults($data) {

		if(isset($this->sessionParams))
			$this->sessionParams = !empty($data['session']) ? $data['session'] : null;
		$response = array('Error_Msg' => '');

		$EvnUslugaParChanged = array();
		$approvedCount = 0;

		// 1. идём по пробам
		if (!empty($data['EvnLabSamples'])) {
			$arrayId = json_decode($data['EvnLabSamples']);
			$one = (count($arrayId)==1)?true:false;
			foreach($arrayId as $id) {
				// 2. достаём результаты пробы
				$join = ""; $where = ""; $params = [];
				if ($data['MedService_IsQualityTestApprove'] == 1) {
					// свзязываем таблицы, чтобы отобрать только качественные тесты
					$join = "inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ut.UslugaTest_rid
						inner join v_EvnLabSample els on els.EvnLabSample_id = eup.EvnLabSample_id
						inner join UslugaComplexMedService ucms on ucms.UslugaComplex_id = ut.UslugaComplex_id and ucms.MedService_id = :MedService_id
						inner join lis.AnalyzerTest at on at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id and at.Analyzer_id = els.Analyzer_id
					";
					$where = " and at.AnalyzerTestType_id = 2";
					$params['MedService_id'] = $data['MedService_id'];
				}
				$query = "
					select
						ut.UslugaTest_id as \"UslugaTest_id\",
						ut.UslugaTest_Result as \"UslugaTest_Result\",
						ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
						ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
						ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
						ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
						ut.UslugaTest_Comment as \"UslugaTest_Comment\",
						ut.UslugaTest_pid as \"UslugaTest_pid\",
						ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\"
					from
						v_UslugaTest ut
					{$join}
					where
						ut.EvnLabSample_id = :EvnLabSample_id
						and ut.UslugaTest_ResultValue IS NOT NULL
						and ut.UslugaTest_ResultValue <> ''
						{$where}
				";

				$params['EvnLabSample_id'] = $id;
				$result = $this->db->query($query, $params);

				$operated = false;
				$resultCount = 0;
				$alreadyApproved = 0;

				if (is_object($result)) {
					$resp = $result->result('array');
					foreach($resp as $respone) {
						if (!empty($respone['UslugaTest_ResultApproved']) && $respone['UslugaTest_ResultApproved'] == 2) {
							$alreadyApproved++;
							continue;
						}
						$resultCount++;
						$data['UslugaTest_ResultApproved'] = 1;
						if (isset($data['onlyNormal']) && $data['onlyNormal'] == 1) {
							$data['UslugaTest_ResultApproved'] = 2;
						} else {
							if (!empty($respone['UslugaTest_ResultQualitativeNorms'])) {
								$UslugaTest_ResultQualitativeNorms = json_decode($respone['UslugaTest_ResultQualitativeNorms'], true);
								array_walk_recursive($UslugaTest_ResultQualitativeNorms, 'ConvertFromUTF8ToWin1251');
								if ((is_array($UslugaTest_ResultQualitativeNorms) && in_array($respone['UslugaTest_ResultValue'], $UslugaTest_ResultQualitativeNorms)) || $one) {
									$data['UslugaTest_ResultApproved'] = 2;
								}
							} else {
								// только числовые нормы можно сравнить
								if (is_numeric(trim(str_replace(",", ".", $respone['UslugaTest_ResultValue']))) && !$one) {
									if (
										(floatval(str_replace(",", ".", $respone['UslugaTest_ResultValue'])) >= floatval(str_replace(",", ".", $respone['UslugaTest_ResultLower'])) || !isset($respone['UslugaTest_ResultLower']))
										&& (floatval(str_replace(",", ".", $respone['UslugaTest_ResultValue'])) <= floatval(str_replace(",", ".", $respone['UslugaTest_ResultUpper'])) || !isset($respone['UslugaTest_ResultUpper']))
									) {
										$data['UslugaTest_ResultApproved'] = 2;
									}
								} else {
									$data['UslugaTest_ResultApproved'] = 2;
								}
							}
						}

						if ($data['UslugaTest_ResultApproved'] == 2) {
							if (!in_array($respone['UslugaTest_pid'], $EvnUslugaParChanged)) {
								$EvnUslugaParChanged[] = $respone['UslugaTest_pid'];
							}
							$query = "
								update
									UslugaTest
								set
									UslugaTest_ResultApproved = 2,
									UslugaTest_CheckDT = dbo.tzgetdate()
								where
									UslugaTest_id = :UslugaTest_id
							";
							$res = $this->db->query($query, array(
								'UslugaTest_id' => $respone['UslugaTest_id']
							));
							if ($res) {
								collectEditedData('upd', 'UslugaTest', $respone['UslugaTest_id']);
							}

							// добавляем запись в UslugaTestHistory
							$proc_query = "
								select *
								from p_UslugaTestHistory_ins(
									UslugaTestHistory_Comment := :UslugaTestHistory_Comment,
									MedStaffFact_id := :MedStaffFact_id,
									pmUser_id := :pmUser_id,
									UslugaTest_id := :UslugaTest_id,
									UslugaTestHistory_Result := :UslugaTestHistory_Result,
									UslugaTestHistory_CheckDT := dbo.tzgetdate()
								)
							";
							$uthParams = [
								'UslugaTestHistory_Comment' => $respone['UslugaTest_Comment'],
								'MedStaffFact_id' => $data['MedStaffFact_id'],
								'pmUser_id' => $data['pmUser_id'],
								'UslugaTest_id' => $respone['UslugaTest_id'],
								'UslugaTestHistory_Result' => $respone['UslugaTest_ResultValue']
							];
							$tmp = $this->db->query($proc_query, $uthParams);

							$operated = true;
						}
					}
				}

				if ($operated) {
					$approvedCount++;

					if (!empty($EvnUslugaParChanged)) {
						$this->onChangeApproveResults(array(
							'EvnUslugaParChanged' => $EvnUslugaParChanged,
							'session' => $data['session'],
							'pmUser_id' => $data['pmUser_id']
						));
					}

					$this->ReCacheLabSampleStatus(array(
						'EvnLabSample_id' => $id
					));

					$this->ReCacheLabRequestByLabSample(array(
						'EvnLabSample_id' => $id,
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id']
					));

					// если проба стала одобренной и в ней не заполнены данные о враче, то заполняем.
					$this->setEvnLabSampleDone(array(
						'EvnLabSample_id' => $id,
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id']
					));
					// Если на текущей службе есть признак, то тогда подготавливаем к отправке (для массового одобрения)
					$this->load->model('Mbu_model', 'Mbu_model');
					$data['EvnLabSample_id'] = $id;
					$this->Mbu_model->preSendMbu($data);
					//Удаляем статус печати протокола
					$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
					$this->EvnLabRequest_model->setProtocolPrintFlag([
						'EvnLabSamples' => [$data['EvnLabSample_id']]
					]);	
				} elseif ($resultCount > 0) {
					$response['Error_Msg'] = 'Нельзя одобрить пробу, т.к. отсутствуют результаты в пределах нормальных значений';
				} elseif ($alreadyApproved == 0) {
					$response['Error_Msg'] = 'Нельзя одобрить пробу, т.к. отсутствуют результаты';
				}
			}
		}

		if ($approvedCount < 1) { // выводить ошибку, только если ничего не одобрил.
			if ($data['MedService_IsQualityTestApprove'] == 1) {
				$response['Error_Msg'] = 'Ни один тест не одобрен. Групповое одобрение доступно только для тестов с типом "качественный"';
			}
			return $response;
		} else {
			return ['Error_Msg' => '', 'success' => true];
		}
	}

	/**
	 * Кэш статуса проб в заявку и создание протокола если надо
	 */
	function ReCacheLabRequestByLabSample($data) {
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');

		// 1. смотрим одобрены ли в заявке все пробы
		$resp_elr = $this->queryResult("
			select
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = els.MedService_id
			where
				els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		", array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		if (!empty($resp_elr[0]['EvnLabRequest_id'])) {
			$data['EvnLabRequest_id'] = $resp_elr[0]['EvnLabRequest_id'];
			$data['EvnDirection_id'] = $resp_elr[0]['EvnDirection_id'];
			$data['LpuSection_id'] = $resp_elr[0]['LpuSection_id'];
		} else {
			return false;
		}

		// Получаем список родительских услуг для данной пробы
		$EvnUslugaPars = $this->getEvnUslugasRoot(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		foreach($EvnUslugaPars as $EvnUslugaPar) {
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
				if (!empty($EvnUslugaPar['EvnPrescr_id'])) {
					$res = $this->common->POST('EvnPrescr/Execution/exec', [
						'EvnPrescr_id' => $EvnUslugaPar['EvnPrescr_id'],
						'pmUser_id' => $data['pmUser_id']
					], 'single');
					if (!$this->isSuccessful($res)) {
						throw new Exception($res['Error_Msg'], 400);
					}
				}

				// Создаем или пересоздаем документ (протокол)
				$this->pmUser_id = $data['pmUser_id'];
				$this->EvnLabRequest_model->assign(array('EvnLabRequest_id' => $data['EvnLabRequest_id'], 'pmUser_id' => $this->pmUser_id));
				$labrequest = $this->EvnLabRequest_model->load();
				$this->EvnLabRequest_model->EvnUslugaPar_oid = $EvnUslugaPar['EvnUslugaPar_id'];
				$samples = $this->loadList(array('EvnLabRequest_id' => $data['EvnLabRequest_id']));
				// Еще нужно заполнить объект $this->EvnLabRequest_model->EvnLabSample
				$this->EvnLabRequest_model->EvnLabSample->setItems($samples);

				// Сохраняем протокол
				$this->EvnLabRequest_model->saveEvnXml();
			} else {
				// нет одобренного теста
				if (!empty($EvnUslugaPar['EvnPrescr_id'])) {
					$dt = [
						'EvnPrescr_id' => $EvnUslugaPar['EvnPrescr_id'],
						'pmUser_id' => $data['pmUser_id']
					];
					$res = $this->common->PUT('EvnPrescr/Execution/rollback', $dt, 'single');
					if (!$this->isSuccessful($res)) {
						//в коде раньше не было проверки того, что воззвращается из метода
						//throw new Exception($res['Error_Msg'], 500);
					}
				}

				// Удаляем протокол( поиск документа вынесен в api)
				//evnxml создаются/лежат на основной, оттуда и стоит удалять
				$res = $this->common->DELETE('EvnXml/byEvn', [
					'Evn_id' => $EvnUslugaPar['EvnUslugaPar_id']
				], 'single');
				if (!$this->isSuccessful($res)) {
					throw new Exception($res['Error_Msg'], 500);
				}
			}
		}

		if (!empty($data['EvnLabRequest_id'])) {
			$counts = $this->EvnLabRequest_model->countTests(array('EvnLabRequest_id'=> $data['EvnLabRequest_id'])); // приходит массив c данными
			$data['UslugaExecutionType_id'] = null;
			if ($counts['approved_count'] > 0) {
				$data['UslugaExecutionType_id'] = 2;
				if ($counts['test_count'] == $counts['approved_count']) {
					$data['UslugaExecutionType_id'] = 1;
				}
			}

			if ($counts['sample_bad_count'] > 0 && $counts['sample_bad_count'] == $counts['sample_count']) {
				// все пробы забракованы = не выполнена (refs #54735)
				$data['UslugaExecutionType_id'] = 3;
			}

			// сохраняем выполнение
			$this->EvnLabRequest_model->saveUslugaExecutionType($data);

			// кэшируем статус заявки
			$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			// кэшируем названия услуг
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			// кэшируем статус проб в заявке
			$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		return array('UslugaExecutionType_id'=>(isset($data['UslugaExecutionType_id']))?$data['UslugaExecutionType_id']:null);
	}

	/**
	 * Получение нормальных значений для качественного текста
	 */
	function getRefValuesForQualitativeTest($AnalyzerTestRefValues_id)
	{
		$array = array();

		$query = "
			select
				qtaat.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			from
				lis.v_QualitativeTestAnswerReferValue qtarv
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where
				qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";

		$result = $this->db->query($query, array(
			'AnalyzerTestRefValues_id' => $AnalyzerTestRefValues_id
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				$array[] = toUtf($respone['QualitativeTestAnswerAnalyzerTest_Answer']);
			}
		}

		return json_encode($array);
	}

	/**
	 * Получение списка референсных значений пробы
	 */
	function loadRefValues($data)
	{
		$resp = array();
		$this->load->model('AnalyzerTestRefValues_model');
		$UslugaComplex_ids = json_decode($data['UslugaComplex_ids'], true);

		if (!array_key_exists('EvnPrescr_id', $data)) {
			$res = $this->common->GET('EvnPrescr/PrescrByDirection', $data, 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			$data['EvnPrescr_id'] = $res['EvnPrescr_id'];
		}

		if (empty($data['EvnPrescr_id'])) {
			$data['EvnPrescr_id'] = null;
		}

		if (!array_key_exists('PersonData', $data)) {
			$person = $this->common->GET('Person/PersonDataForRefValues', $data);
			if (!$this->isSuccessful($person)) {
				return $person;
			}
			$person = $person['data'][0];
		} else {
			$person = $data['PersonData'];
		}

		if (is_array($UslugaComplex_ids) && count($UslugaComplex_ids) > 0) {
			// одним запросом получаем референсные значения для всех услуг
			$refvalues = array();

			$query = "
				select
					ucms_at.UslugaComplex_id as \"UslugaComplex_id\",
					at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
					atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
					rv.RefValues_id as \"RefValues_id\",
					a.Analyzer_id as \"Analyzer_id\",
					rv.Unit_id as \"Unit_id\",
					rv.RefValues_Name || COALESCE(' (' || a.Analyzer_Name || ')','') as \"RefValues_Name\",
					'' as \"UslugaTest_ResultQualitativeNorms\",
					case when att.AnalyzerTestType_Code IN ('1','3') then cast(rv.RefValues_LowerLimit as varchar) else '' end as \"UslugaTest_ResultLower\",
					case when att.AnalyzerTestType_Code IN ('1','3') then cast(rv.RefValues_UpperLimit as varchar) else '' end as \"UslugaTest_ResultUpper\",
					case when att.AnalyzerTestType_Code IN ('1','3') then cast(rv.RefValues_BotCritValue as varchar) else '' end as \"UslugaTest_ResultLowerCrit\",
					case when att.AnalyzerTestType_Code IN ('1','3') then cast(rv.RefValues_TopCritValue as varchar) else '' end as \"UslugaTest_ResultUpperCrit\",
					u.Unit_Name as \"UslugaTest_ResultUnit\",
					at.LabTest_id as \"LabTest_id\",
					rv.RefValues_Description as \"UslugaTest_Comment\"
				from
					lis.v_AnalyzerTest at
					inner join v_UslugaComplexMedService ucms_at on ucms_at.UslugaComplexMedService_id = at.UslugaComplexMedService_id
					inner join lis.v_AnalyzerTestType att on att.AnalyzerTestType_id = at.AnalyzerTestType_id
					inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
					left join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					left join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
					left join lis.v_Unit u on u.Unit_id = rv.Unit_id
					left join lateral(
						select
							count(*) as cnt
						from
							v_LimitValues l
							inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
						where
							l.RefValues_id = rv.RefValues_id
							and (
								(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2)
								OR
								((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
							)
					) LIMIT_alias on true
				where
					a.MedService_id = :MedService_id
					and ucms_at.UslugaComplex_id IN ('".implode("','", $UslugaComplex_ids)."')
					and (
                    	at.AnalyzerTest_pid is null
                        or exists(
                        	select
                            	AnalyzerTest_id
                            from lis.v_AnalyzerTest at_parent
                            inner join v_UslugaComplexMedService ucms_at_parent on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id
                        	where at_parent.AnalyzerTest_id = at.AnalyzerTest_pid
                        		and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id
                        	limit 1
                        )
                    )
				order by
					case when a.Analyzer_id = COALESCE(CAST(:Analyzer_id as bigint), 0) then 0 else 1 end,
					LIMIT_alias.cnt desc,
					rv.RefValues_Name
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp_rv = $result->result('array');
				foreach ($resp_rv as $respone) {
					if ($respone['AnalyzerTestType_id'] == 2 && !empty($respone['AnalyzerTestRefValues_id'])) {
						$respone['UslugaTest_ResultQualitativeNorms'] = $this->AnalyzerTestRefValues_model->getRefValuesForQualitativeTestJSON($respone['AnalyzerTestRefValues_id']);
					}
					$refvalues[$respone['UslugaComplex_id']][] = $respone;
				}
			}

			foreach($refvalues as $ucid => $rvs) {
				// если услуга на двух и более анализаторах и среди них нет текущего анализатора, то автоматически нормы не берём.
				$analyzers = array();
				foreach($rvs as $rv) {
					if ($rv['Analyzer_id'] == $data['Analyzer_id']) { // если есть текущий анализатор, значит реф. значения будем брать.
						$analyzers = array();
						break;
					}
					if (!in_array($rv['Analyzer_id'], $analyzers)) {
						$analyzers[] = $rv['Analyzer_id'];
					}
				}
				if (count($analyzers) > 1) {
					unset($refvalues[$ucid]);
				}
			}

			foreach($UslugaComplex_ids as $UslugaComplex_id) {
				$data['UslugaComplexTest_id'] = $UslugaComplex_id;
				$rv = null;
				if (!empty($data['Analyzer_id'])) {
					$data['OrderByLimit'] = true;
					if (!empty($refvalues[$UslugaComplex_id])) {
						$Analyzer_id = null;
						foreach ($refvalues[$UslugaComplex_id] as $refvalue) {
							if ($Analyzer_id != null && $Analyzer_id != $refvalue['Analyzer_id']) {
								// если изменился анализатор, значит перешли с основного, а раз были реф.значения на оснвном то с ручных методик их не берём
								break;
							}
							$Analyzer_id = $refvalue['Analyzer_id'];
							if (!empty($refvalue['RefValues_id'])) {
								$limit_ok = true;

								// каждое референсное значение проверяем на ограничения
								$query = "
									select
										l.LimitType_id as \"LimitType_id\",
										lt.LimitType_SysNick as \"LimitType_SysNick\",
										l.Limit_Values as \"Limit_Values\",
										l.Limit_ValuesFrom as \"Limit_ValuesFrom\",
										l.Limit_ValuesTo as \"Limit_ValuesTo\"
									from
										v_LimitValues l
										inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
									where
										l.RefValues_id = :RefValues_id
										and (
											(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2)
											OR
											((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
										)
								";
								$result_limits = $this->db->query($query, $refvalue);
								if (is_object($result_limits)) {
									$resp_limits = $result_limits->result('array');
									foreach ($resp_limits as $resp_limit) {
										if ($resp_limit['LimitType_SysNick'] == 'PregnancyUnitType') {
											if (!isset($person['Pregnancy_Value'])) {
												$limit_ok = false;
											}
											if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['Pregnancy_Value']) {
												$limit_ok = false;
											}
											if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['Pregnancy_Value']) {
												$limit_ok = false;
											}
										}

										if ($resp_limit['LimitType_SysNick'] == 'HormonalPhaseType') {
											if (!isset($person['HormonalPhaseType_id'])) {
												$limit_ok = false;
											}
											if ($person['HormonalPhaseType_id'] != $resp_limit['Limit_Values']) {
												$limit_ok = false;
											}
										}

										if ($resp_limit['LimitType_SysNick'] == 'Sex' && $person['Sex_id'] != $resp_limit['Limit_Values']) {
											$limit_ok = false;
										}

										if ($resp_limit['LimitType_SysNick'] == 'AgeUnit') {
											switch ($resp_limit['Limit_Values']) {
												case 1:
													if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['Person_AgeYear']) {
														$limit_ok = false;
													}
													if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['Person_AgeYear']) {
														$limit_ok = false;
													}
													break;
												case 2:
													if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['Person_AgeMonth']) {
														$limit_ok = false;
													}
													if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['Person_AgeMonth']) {
														$limit_ok = false;
													}
													break;
												case 3:
												case 4:
													if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['Person_AgeDay']) {
														$limit_ok = false;
													}
													if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['Person_AgeDay']) {
														$limit_ok = false;
													}
													break;
												case 5:
													if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['Person_AgeWeek']) {
														$limit_ok = false;
													}
													if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['Person_AgeWeek']) {
														$limit_ok = false;
													}
													break;
											}
										}

										if ($resp_limit['LimitType_id'] == 7) {
											if (!empty($resp_limit['Limit_ValuesFrom']) && $resp_limit['Limit_ValuesFrom'] > $person['TimeOfDay']) {
												$limit_ok = false;
											}
											if (!empty($resp_limit['Limit_ValuesTo']) && $resp_limit['Limit_ValuesTo'] < $person['TimeOfDay']) {
												$limit_ok = false;
											}
										}
									}
								}

								if ($limit_ok) {
									$rv = $refvalue;
									break; // прерываем foreach
								}
							}
						}
						if (!empty($rv)) {
							$rv['UslugaComplex_id'] = $UslugaComplex_id;
							$resp[] = $rv;
						}
					}
				}
			}
		}
		return $resp;
	}

	/**
	 * Сохранение нового штрих-кода пробы
	 */
	function saveNewEvnLabSampleBarCode($data)
	{
		// 1. достаём данные из пробы
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				Lpu_id as \"Lpu_id\"
			from
				v_EvnLabSample
			where
				EvnLabSample_id = :EvnLabSample_id
			  limit 1
		";
		// echo getDebugSQL($query, $data);
		$resp = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных пробы');
		}

		// 2. заменяем последние 4 на новый штрих код
		$data['Lpu_id'] = $resp['Lpu_id'];
		$data['EvnLabSample_BarCode'] = $data['EvnLabSample_BarCode'];

		// 3. проверяем уникальность
		$response = $this->checkEvnLabSampleBarCodeUnique($data);
		if (!empty($response['Error_Msg'])) {
			return array($response);
		}

		// 4. обновляем в EvnLabSample
		$query = "
			update EvnLabSample
			set evnlabsample_barcode = :EvnLabSample_BarCode
			where evn_id = :EvnLabSample_id
		";
		$this->db->query($query, $data);

		collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);

		// кэшируем статус проб в заявке
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $resp['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array(array(
			'success' => true
		));
	}

	/**
	 * Сохранение нового номера пробы
	 */
	function saveNewEvnLabSampleNum($data)
	{
		if (!($data['EvnLabSample_ShortNum'] >= 1000 && $data['EvnLabSample_ShortNum'] <= 9999)) {
			return $this->createError('','Штрих-код должен быть 4-значным');
		}

		// 1. достаём данные из пробы
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				Lpu_id as \"Lpu_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\"
			from
				v_EvnLabSample
			where
				EvnLabSample_id = :EvnLabSample_id
		";
		// echo getDebugSQL($query, $data);
		$resp = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных пробы');
		}

		$data['Lpu_id'] = $resp['Lpu_id'];
		// заменяем последние 4 цифры на новый номер
		$data['EvnLabSample_Num'] = mb_substr($resp['EvnLabSample_Num'], 0, mb_strlen($resp['EvnLabSample_Num']) - 4).$data['EvnLabSample_ShortNum'];

		// 3. проверяем уникальность
		$response = $this->checkEvnLabSampleNumUnique($data);
		if (!empty($response['Error_Msg'])) {
			return array($response);
		}

		// 4. обновляем в EvnLabSample
		$query = "
			update EvnLabSample
			set evnlabsample_num = :EvnLabSample_Num
			where evn_id = :EvnLabSample_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		$res = $this->queryResult($query, $data);
		if (!is_array($res)) {
			return $this->createError('','Ошибка при изменении номера пробы');
		}
		if (!$this->isSuccessful($res)) {
			return $res;
		}

		collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);

		// кэшируем статус проб в заявке
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $resp['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return [[
			'success' => true
		]];
	}

	/**
	 * Сохранение отбраковки
	 */
	function saveEvnLabSampleDefect($data)
	{
		if (empty($data['EvnLabSample_id'])) {
			$data['EvnLabSample_id'] = $this->getFirstResultFromQuery("
						SELECT
							EvnLabSample_id as \"EvnLabSample_id\"
						FROM
							v_EvnLabSample
						WHERE
							EvnLabSample_BarCode = :EvnLabSample_BarCode
							AND Lpu_id = :Lpu_id
						",$data);
		}

		if (empty($data['EvnLabSample_id'])) {
			$MedService_Name = $this->getFirstResultFromQuery("
				SELECT
					MedService_Name as \"MedService_Name\"
				FROM
					v_MedService
				WHERE
					MedService_id = :MedService_sid
				AND Lpu_id = :Lpu_id
				",$data);
			return array('Error_Msg' => 'Проба с указанным штрих кодом не найдена в списке проб пункта забора ' . $MedService_Name . '.
				Проверьте, корректно ли указан штрих код пробы.',
				'YesNo' => true);
		}

		$MedService_sid = '';
		if ($this->regionNick == 'vologda' && $data['MedServiceType_SysNick'] == 'pzm') {
			$query = "
				select
					els.LabSampleStatus_id as \"Status\",
					ms.MedService_Name as \"MedService_Name\"
				from v_EvnLabSample els
				left join v_MedService ms on ms.MedService_id = els.MedService_did
				where
					els.EvnLabSample_BarCode = :EvnLabSample_BarCode
					and els.Lpu_id = :Lpu_id
					and els.MedService_did = :MedService_sid
			";

			$result = $this->db->query($query, $data);
			$result = $result->result('array');

			if (empty($result[0]) || $result[0]['Status'] != 1) {
				$MedService_Name = $this->getFirstResultFromQuery("
					SELECT
						MedService_Name as \"MedService_Name\"
					FROM
						v_MedService
					WHERE
						MedService_id = :MedService_sid
						AND Lpu_id = :Lpu_id
				",$data);
				return array('Error_Msg' => 'Проба с указанным штрих кодом не найдена в списке проб пункта забора ' . $MedService_Name . '.
				Проверьте, корректно ли указан штрих код пробы.',
					'YesNo' => true);
			} else {
				$MedService_sid = "MedService_sid = :MedService_sid,";
			}
		}

		$query = "
			UPDATE
				EvnLabSample els
			SET
				DefectCauseType_id = :DefectCauseType_id,
				{$MedService_sid}
				EvnLabSample_IsLIS = 1
			WHERE
				els.Evn_id = :EvnLabSample_id
				and els.Lpu_id = :Lpu_id
		";

		$res = $this->db->query($query, $data);
		if ($res) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
		}

		// рекэш статуса
		$this->ReCacheLabSampleStatus(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		$this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '', 'EvnLabSample_id' => $data['EvnLabSample_id'], 'success' => true);
	}

	/**
	 * Удаление отбраковки
	 */
	function deleteEvnLabSampleDefect($data)
	{
		$query = "
			UPDATE
				EvnLabSample
			SET
				DefectCauseType_id = NULL,
				EvnLabSample_IsLIS = 1
			WHERE
				Evn_id = :EvnLabSample_id
		";

		$res = $this->db->query($query, $data);
		if ($res) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
		}

		// рекэш статуса
		$this->ReCacheLabSampleStatus(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		$this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * Назначение параметра
	 */
	public function assign($values)
	{
		parent::assign($values);
		if (isset($values['LabSample_Results'])){
			$this->EvnUsluga_Result_Json = $values['LabSample_Results'];
		} else {
			$this->EvnUsluga_Result_Json = null;
		}
		if (isset($values['Person_id'])){
			$this->Person_id = $values['Person_id'];
		} else {
			$this->Person_id = null;
		}
	}

	public function unAssign($values)
	{
		$keysToUnassign = [
			'evnlabsample_rid',
			'evnlabsample_insdt',
			'evnlabsample_upddt',
			'evnlabsample_index',
			'evnlabsample_count'
		];
		$values = explode(',', $values);
		$result = [];
		foreach ($values as $param) {
			$param = explode(' := ', trim($param))[0];
			if (!in_array(strtolower($param), $keysToUnassign)) {
				$result[] = $param .' := :' . $param;
			}
		}
		return implode(",\n", $result);
	}

	/**
	 * Проверка возможности удаления
	 */
	protected function canDelete()
	{
		// TODO: Implement canDelete() method.
		return true;
	}

	/**
	 * Получение названия таблицы
	 */
	protected function getTableName()
	{
		return 'EvnLabSample';
	}

	/**
	 * Загрузка
	 */
	function load($field = null, $value = null, $selectFields = '*', $addNameEntries = true)
	{
		//имеет смысл убрать те поля, что не понадобятся
		$query = "
			select
				t.EvnClass_id as \"EvnClass_id\",
				t.EvnClass_Name as \"EvnClass_Name\",
				t.EvnLabSample_id as \"EvnLabSample_id\",
				t.EvnLabSample_pid as \"EvnLabSample_pid\",
				t.EvnLabSample_rid as \"EvnLabSample_rid\",
				t.Lpu_id as \"Lpu_id\",
				t.Server_id as \"Server_id\",
				t.PersonEvn_id as \"PersonEvn_id\",
				t.EvnLabSample_setDate as \"EvnLabSample_setDate\",
				t.EvnLabSample_setTime as \"EvnLabSample_setTime\",
				t.EvnLabSample_didDate as \"EvnLabSample_didDate\",
				t.EvnLabSample_diDTime as \"EvnLabSample_diDTime\",
				t.EvnLabSample_disDate as \"EvnLabSample_disDate\",
				t.EvnLabSample_disTime as \"EvnLabSample_disTime\",
				t.EvnLabSample_AnalyzerDate as \"EvnLabSample_AnalyzerDate\",
				t.EvnLabSample_StatusDate as \"EvnLabSample_StatusDate\",
				to_char(t.EvnLabSample_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_setDT\",
				to_char(t.EvnLabSample_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_disDT\",
				to_char(t.EvnLabSample_didDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_didDT\",
				to_char(t.EvnLabSample_insDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_insDT\",
				to_char(t.EvnLabSample_updDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_updDT\",
				to_char(t.EvnLabSample_signDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_signDT\",
				to_char(t.EvnLabSample_DelivDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_DelivDT\",
				to_char(t.EvnLabSample_StudyDT, 'DD.MM.YYYY HH24:MI') as \"EvnLabSample_StudyDT\",
				t.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT_ISO\",
				t.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT_ISO\",
				t.EvnLabSample_Index as \"EvnLabSample_Index\",
				t.EvnLabSample_Count as \"EvnLabSample_Count\",
				t.pmUser_insID as \"pmUser_insID\",
				t.pmUser_updID as \"pmUser_updID\",
				t.Person_id as \"Person_id\",
				t.Morbus_id as \"Morbus_id\",
				t.EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				t.pmUser_signID as \"pmUser_signID\",
				t.EvnLabSample_IsArchive as \"EvnLabSample_IsArchive\",
				t.EvnLabSample_Guid as \"EvnLabSample_Guid\",
				t.EvnLabSample_IndexMinusOne as \"EvnLabSample_IndexMinusOne\",
				t.EvnStatus_id as \"EvnStatus_id\",
				t.EvnLabSample_IsTransit as \"EvnLabSample_IsTransit\",
				t.EvnLabRequest_id as \"EvnLabRequest_id\",
				t.EvnLabSample_Num as \"EvnLabSample_Num\",
				t.RefSample_id as \"RefSample_id\",
				t.Lpu_did as \"Lpu_did\",
				t.LpuSection_did as \"LpuSection_did\",
				t.MedPersonal_did as \"MedPersonal_did\",
				t.MedPersonal_sdid as \"MedPersonal_sdid\",
				t.Lpu_aid as \"Lpu_aid\",
				t.LpuSection_aid as \"LpuSection_aid\",
				t.MedPersonal_aid as \"MedPersonal_aid\",
				t.MedPersonal_said as \"MedPersonal_said\",
				t.LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				t.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				t.MedService_id as \"MedService_id\",
				t.LabSampleStatus_id as \"LabSampleStatus_id\",
				t.DefectCauseType_id as \"DefectCauseType_id\",
				t.EvnLabSample_IsLis as \"EvnLabSample_IsLis\",
				t.Analyzer_id as \"Analyzer_id\",
				t.EvnLabSample_Test as \"EvnLabSample_Test\",
				t.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
				t.EvnLabSample_Barcode as \"EvnLabSample_Barcode\",
				t.MedService_did as \"MedService_did\",
				t.MedService_sid as \"MedService_sid\",
				r.RefMaterial_Name as \"RefMaterial_Name\",
				Lpu_id_ref.Lpu_Name as \"Lpu_id_Name\",
				Morbus_id_ref.Morbus_Name as \"Morbus_id_Name\",
				EvnLabSample_IsSigned_ref.YesNo_Name as \"EvnLabSample_IsSigned_Name\",
				RefSample_id_ref.RefSample_Name as \"RefSample_id_Name\",
				Lpu_did_ref.Lpu_Name as \"Lpu_did_Name\",
				LpuSection_did_ref.LpuSection_Name as \"LpuSection_did_Name\",
				Lpu_aid_ref.Lpu_Name as \"Lpu_aid_Name\",
				LpuSection_aid_ref.LpuSection_Name as \"LpuSection_aid_Name\",
				LabSampleDefectiveType_id_ref.LabSampleDefectiveType_Name as \"LabSampleDefectiveType_id_Name\",
				case
					when t.DefectCauseType_id is null
						then 0
						else 1
				end as \"EvnLabSample_IsDefect\"
			from
				dbo.v_EvnLabSample t
				LEFT JOIN v_Lpu Lpu_id_ref on Lpu_id_ref.Lpu_id = t.Lpu_id
				LEFT JOIN v_Morbus Morbus_id_ref on Morbus_id_ref.Morbus_id = t.Morbus_id
				LEFT JOIN v_YesNo EvnLabSample_IsSigned_ref on EvnLabSample_IsSigned_ref.YesNo_id = t.EvnLabSample_IsSigned
				LEFT JOIN v_RefSample RefSample_id_ref on RefSample_id_ref.RefSample_id = t.RefSample_id
				LEFT JOIN v_Lpu Lpu_did_ref on Lpu_did_ref.Lpu_id = t.Lpu_did
				LEFT JOIN v_LpuSection LpuSection_did_ref on LpuSection_did_ref.LpuSection_id = t.LpuSection_did
				LEFT JOIN v_Lpu Lpu_aid_ref on Lpu_aid_ref.Lpu_id = t.Lpu_aid
				LEFT JOIN v_LpuSection LpuSection_aid_ref on LpuSection_aid_ref.LpuSection_id = t.LpuSection_aid
				LEFT JOIN v_LabSampleDefectiveType LabSampleDefectiveType_id_ref on LabSampleDefectiveType_id_ref.LabSampleDefectiveType_id = t.LabSampleDefectiveType_id
				LEFT JOIN  dbo.v_RefSample s on s.RefSample_id = t.RefSample_id
				LEFT JOIN  dbo.v_RefMaterial r on s.RefMaterial_id = r.RefMaterial_id
			where
				EvnLabSample_id = :EvnLabSample_id
		";

		//echo getDebugSql($query, array( 'EvnLabSample_id' => $this->EvnLabSample_id)); die;
		$result = $this->db->query($query, array('EvnLabSample_id' => $this->EvnLabSample_id));
		if ( is_object($result) ) {
			// тут ещё был код который наследовался в абстрактной модели и потерялся 12 июля, возвращаю его сюда
			$response = $result->result('array');

			$response = $this->getMorbusNames($response);

			if (isset($response[0])) {
				parent::setRawLoadResult($response[0]);
				parent::assign($response[0]);
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * метод-кастыль, чтобы обойти отсутствие v_morbus на lis db
	 * @param $response
	 * @return array
	 */
	function getMorbusNames($response) {

		$morbus_ids = [];
		foreach ($response as $value) {
			$morbus_ids[] = trim($value['Morbus_id_Name']);
		}

		if (empty($morbus_ids[0])) {
			foreach ($response as $key => $value) {
				$response[$key]['Morbus_id_Name'] = null;
			}
			return $response;
		}

		$query = "
				select
					Morbus_id as \"Morbus_id\",
					Morbus_Name as \"Morbus_Name\"
				from v_Mordus
				where v_Morbus_id in (".implode(', ', $morbus_ids).")
			";

		$res = $this->db->query($query);
		$res = $res->result('array');

		$names = [];
		foreach ($res as $name) {
			$names[$name['Morbus_id']] = $name['Morbus_Name'];
		}

		foreach ($response as $key => $value) {
			$response[$key]['Morbus_id_Name'] = $names[$value['Morbus_id_Name']];
		}

		return $response;
	}
	/**
	 * Получает данные для формирования заявки ЛИС и последующей отправки в ЛИС
	 * @param $data
	 * @return bool
	 */
	function getRequest2Data($data){
		// Запрос надо не то чтобы проверять,а выстраивать логику по новой по нормальному проектированию, и по новой

		// смотрим есть ли сохраненный в заявке состав услуги:
		$UslugaComplex_id = $this->getFirstResultFromQuery("
			select
				elruc.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequestUslugaComplex elruc on elruc.EvnLabRequest_id = els.EvnLabRequest_id
			where
				els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		", array('EvnLabSample_id' => $data['EvnLabSample_id']));

		$filter_tests = "";
		// если есть состав - фильтруем по составу
		if (!empty($UslugaComplex_id)) {
			$filter_tests .= " and uc.UslugaComplex_id IN (select UslugaComplex_id from v_EvnLabRequestUslugaComplex where EvnLabRequest_id = lr.EvnLabRequest_id)";
		}

		$query = "
			select
				EvnLabSample.EvnClass_id as \"EvnClass_id\",
				EvnLabSample.EvnClass_Name as \"EvnClass_Name\",
				EvnLabSample.EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample.EvnLabSample_setDate as \"EvnLabSample_setDate\",
				EvnLabSample.EvnLabSample_settime as \"EvnLabSample_settime\",
				EvnLabSample.EvnLabSample_didDate as \"EvnLabSample_didDate\",
				EvnLabSample.EvnLabSample_diDTime as \"EvnLabSample_diDTime\",
				EvnLabSample.EvnLabSample_disDate as \"EvnLabSample_disDate\",
				EvnLabSample.EvnLabSample_distime as \"EvnLabSample_distime\",
				EvnLabSample.EvnLabSample_pid as \"EvnLabSample_pid\",
				EvnLabSample.EvnLabSample_rid as \"EvnLabSample_rid\",
				EvnLabSample.Lpu_id as \"Lpu_id\",
				EvnLabSample.Server_id as \"Server_id\",
				EvnLabSample.PersonEvn_id as \"PersonEvn_id\",
				EvnLabSample.EvnLabSample_setDT as \"EvnLabSample_setDT\",
				EvnLabSample.EvnLabSample_disDT as \"EvnLabSample_disDT\",
				EvnLabSample.EvnLabSample_didDT as \"EvnLabSample_didDT\",
				EvnLabSample.EvnLabSample_insDT as \"EvnLabSample_insDT\",
				EvnLabSample.EvnLabSample_updDT as \"EvnLabSample_updDT\",
				EvnLabSample.EvnLabSample_Index as \"EvnLabSample_Index\",
				EvnLabSample.EvnLabSample_Count as \"EvnLabSample_Count\",
				EvnLabSample.pmUser_insID as \"pmUser_insID\",
				EvnLabSample.pmUser_updID as \"pmUser_updID\",
				EvnLabSample.Person_id as \"Person_id\",
				EvnLabSample.Morbus_id as \"Morbus_id\",
				EvnLabSample.EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				EvnLabSample.pmUser_signID as \"pmUser_signID\",
				EvnLabSample.EvnLabSample_signDT as \"EvnLabSample_signDT\",
				EvnLabSample.EvnLabSample_IsArchive as \"EvnLabSample_IsArchive\",
				EvnLabSample.EvnLabSample_Guid as \"EvnLabSample_Guid\",
				EvnLabSample.EvnLabSample_IndexMinusOne as \"EvnLabSample_IndexMinusOne\",
				EvnLabSample.EvnStatus_id as \"EvnStatus_id\",
				EvnLabSample.EvnLabSample_StatusDate as \"EvnLabSample_StatusDate\",
				EvnLabSample.EvnLabSample_IsTransit as \"EvnLabSample_IsTransit\",
				EvnLabSample.EvnLabRequest_id as \"EvnLabRequest_id\",
				EvnLabSample.EvnLabSample_Num as \"EvnLabSample_Num\",
				EvnLabSample.RefSample_id as \"RefSample_id\",
				EvnLabSample.Lpu_did as \"Lpu_did\",
				EvnLabSample.LpuSection_did as \"LpuSection_did\",
				EvnLabSample.MedPersonal_did as \"MedPersonal_did\",
				EvnLabSample.MedPersonal_sdid as \"MedPersonal_sdid\",
				EvnLabSample.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				EvnLabSample.Lpu_aid as \"Lpu_aid\",
				EvnLabSample.LpuSection_aid as \"LpuSection_aid\",
				EvnLabSample.MedPersonal_aid as \"MedPersonal_aid\",
				EvnLabSample.MedPersonal_said as \"MedPersonal_said\",
				EvnLabSample.LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				EvnLabSample.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
				EvnLabSample.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				EvnLabSample.MedService_id as \"MedService_id\",
				EvnLabSample.LabSampleStatus_id as \"LabSampleStatus_id\",
				EvnLabSample.DefectCauseType_id as \"DefectCauseType_id\",
				EvnLabSample.EvnLabSample_IsLis as \"EvnLabSample_IsLis\",
				EvnLabSample.Analyzer_id as \"Analyzer_id\",
				EvnLabSample.EvnLabSample_Test as \"EvnLabSample_Test\",
				EvnLabSample.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
				EvnLabSample.EvnLabSample_Barcode as \"EvnLabSample_Barcode\",
				EvnLabSample.MedService_did as \"MedService_did\",
				EvnLabSample.EvnLabSample_AnalyzerDate as \"EvnLabSample_AnalyzerDate\",
				EvnLabSample.MedService_sid as \"MedService_sid\",
				ps.Person_id as \"Person_id\",
				m.MedPersonal_Code as \"MedPersonal_Code\", -- код врача
				m.Person_FIO as \"MedPersonal_FIO\", -- фио врача
				-- данные человека, для которого проводится исследование
				COALESCE(ps.Person_SurName,'') as \"Person_SurName\",
				COALESCE(ps.Person_FirName,'') as \"Person_FirName\",
				COALESCE(ps.Person_SecName,'') as \"Person_SecName\",
				date_part('year',ps.Person_BirthDay) as \"BirthDay_Year\",
				date_part('month',ps.Person_BirthDay) as \"BirthDay_Month\",
				date_part('day',ps.Person_BirthDay) as \"BirthDay_Day\",
				Sex_Code as \"Sex_Code\",
				-- адрес человека
				addr.KLCountry_Name as \"KLCountry_Name\",
				addr.KLCity_Name as \"KLCity_Name\",
				addr.KLStreet_Name as \"KLStreet_Name\",
				addr.Address_House as \"Address_House\",
				addr.Address_Flat as \"Address_Flat\",
				-- полисные данные человека
				case when p.PolisType_id = 4 then '' else p.Polis_Ser end as \"Polis_Ser\",
				case when p.PolisType_id = 4 then ps.Person_EdNum else p.Polis_Num end as \"Polis_Num\",
				os.OrgSmo_Nick as \"OrgSmo_Nick\",
				-- код подразделения (регистрационная форма)
				-- requestForm,
				-- исследование
				target.target_id as \"target_id\",
				-- тесты (получаем сразу в идентификаторах ЛИС)
				tests.ids as \"test_ids\",
				-- биоматериал
				rm.RefMaterial_id as \"biomaterial_id\"
			from
				v_EvnLabSample EvnLabSample
				left join v_PersonState ps on ps.Person_id = EvnLabSample.Person_id
				left join v_address_all addr on addr.address_id = ps.UAddress_id
				left join v_sex s on s.sex_id = ps.sex_id
				left join v_polis p on p.polis_id = ps.polis_id
				left join v_orgsmo os on os.orgsmo_id = p.orgsmo_id
				left join lateral (
					Select MedPersonal_Code, Person_FIO
					from v_medpersonal m
					where m.medpersonal_id = EvnLabSample.medpersonal_aid
                    limit 1
				) m on true
				left join v_EvnLabRequest lr on lr.EvnLabRequest_id = EvnLabSample.EvnLabRequest_id
				left join v_evnUslugaPar eup on eup.EvnDirection_id = lr.EvnDirection_id
				-- услугу выбираем везде по коду ГОСТ-2011
				left join v_UslugaComplex u on eup.UslugaComplex_id = u.UslugaComplex_id
				left join v_UslugaComplex uc2011 on u.UslugaComplex_2011id = uc2011.UslugaComplex_id
				-- подразделение
				--left join lis.DepartamentLink dl on dl.MedService_id = lr.MedService_id
				--left join lis.departament d on d.departament_id = dl.departament_id
				-- todo: таких услуг может быть несколько в одной лаборатории, может быть нужно уточнить по подразделению
				left join lateral (
					Select id as target_id
					from lis._target target
					where target.code = uc2011.UslugaComplex_Code
					limit 1
				) target on true
				-- для получения биоматериала
				-- LEFT JOIN v_UslugaComplexMedService us on us.UslugaComplex_id = u.UslugaComplex_id and us.MedService_id = lr.MedService_id
				LEFT JOIN dbo.v_RefSample r on r.RefSample_id = EvnLabSample.RefSample_id
				-- todo: здесь тоже надо делать через стыковочную таблицу, но сейчас id в промед и в ЛИС совпадают
				LEFT JOIN dbo.v_RefMaterial rm on r.RefMaterial_id = rm.RefMaterial_id
				left join lateral (
                    select distinct
                        string_agg(t.id, ',') as ids
                    from
                        v_UslugaTest UslugaTest
                        INNER JOIN v_UslugaComplex uc ON UslugaTest.UslugaComplex_id = uc.UslugaComplex_id
                        inner join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id and ucms.MedService_id = lr.MedService_id -- услуга на службе
                        inner join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id -- тест
                        inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid -- исследование
                        inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id and (a.Analyzer_Code <> '000' OR a.pmUser_insID <> 1) -- анализатор, только не с ручных методик
                        INNER JOIN dbo.v_UslugaComplex ucgost ON ucgost.UslugaComplex_id = uc.UslugaComplex_2011id --and uc.UslugaComplex_id != uc.UslugaComplex_2011id
                        left join lateral (
                            Select id
                            from lis.v__test t
                            where t.code = ucgost.UslugaComplex_Code
                        ) t on true
                    where
                        UslugaTest_pid = eup.EvnUslugaPar_id
                        and EvnLabSample_id = EvnLabSample.EvnLabSample_id
                        {$filter_tests}
				) as tests on true
		    where
				EvnLabSample.EvnLabSample_id = :EvnLabSample_id
		";
		//echo getDebugSql($query, array('EvnLabSample_id' => $data['EvnLabSample_id']));die();
		$result = $this->db->query($query, array('EvnLabSample_id' => $data['EvnLabSample_id']));
		//echo getDebugSql($query, array( 'EvnLabRequest_id' => $this->EvnLabRequest_id, 'EvnDirection_id' => $EvnDirection_id));exit;
		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response[0];
		}
		else {
			return false;
		}
	}

	/**
	 * Проставляем дату доставки пробы
	 */
	function setDelivDT($data) {
		$query = "
			update
				EvnLabSample
			set
				EvnLabSample_DelivDT = dbo.tzGetDate()
			where
				Evn_id = :EvnLabSample_id
				and EvnLabSample_DelivDT IS NULL
		";

		$res = $this->db->query($query, $data);
		if ($res) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
			return true;
		}

		return false;
	}

	/**
	 * Получает данные для формирования заявки АсМло и последующей отправки в АсМло
	 * @param $data
	 * @return bool
	 * todo convert(varchar(10), ps.Person_BirthDay, 120) --> to_char (ps.Person_BirthDay, 'YYYY-MM-DD HH24:MI:SS')
	 */
	function getRequest2DataForAsMlo($data) {
		$query = "
			select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnLabRequest_IsCito as \"EvnLabRequest_IsCito\",
				COALESCE(els.MedService_id, elr.MedService_id) as \"MedService_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
				els.Analyzer_id as \"Analyzer_id\",
				rm.RefMaterial_id as \"RefMaterial_id\",
				rm.RefMaterial_Code as \"RefMaterial_Code\",
				els.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Sex_id as \"Sex_id\",
				ps.Person_Snils as \"Person_Snils\",
				case when Polis.PolisType_id = 4 then '' else ps.Polis_Ser end as \"Polis_Ser\",
				case when Polis.PolisType_id = 4 then ps.Person_EdNum else ps.Polis_Num end as \"Polis_Num\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				els.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				elr.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fio as \"MedPersonal_Fio\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				WEIGHT.PersonWeight_Weight as \"PersonWeight_Weight\",
				pa.Address_Address as \"Address_Address\"
			from
				v_EvnLabSample els
				left join v_PersonState ps on ps.Person_id = els.Person_id
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
				left join v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
				left join v_MedPersonal mp on mp.MedPersonal_id = ed.MedPersonal_id
				left join v_Lpu l on l.Lpu_id = elr.Lpu_id
				left join v_RefSample r on r.RefSample_id = els.RefSample_id
				left join v_RefMaterial rm on r.RefMaterial_id = rm.RefMaterial_id
				LEFT JOIN v_Address pa on pa.Address_id = ps.PAddress_id
				left join v_Polis Polis on PS.Polis_id = Polis.Polis_id
				left join lateral (
					select
						case when pw.Okei_id = 37 then FLOOR(PersonWeight_Weight * 1000) else FLOOR(PersonWeight_Weight) end as PersonWeight_Weight
					from
						v_PersonWeight pw
					where
						pw.Person_id = ps.person_id
					order by
						PersonWeight_setDT desc
					limit 1
				) WEIGHT on true
		    where
				els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		// echo getDebugSql($query, array('EvnLabSample_id' => $data['EvnLabSample_id'])); die();
		$result = $this->db->query($query, array('EvnLabSample_id' => $data['EvnLabSample_id']));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnLabSample_id'])) {
				$resp[0]['tests'] = array();
				$resp[0]['targets'] = array();
				$filter = "";
				if (!empty($data['onlyNew']) && $data['onlyNew'] == 2) {
					$filter .= " and COALESCE(ut.UslugaTest_ResultValue, '') = ''";
				}
				// Получаем коды тестов
				$query = "
					select distinct
						ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
						ucpgost.UslugaComplex_Code as \"ParentUslugaComplex_Code\"
					from v_UslugaTest ut
						left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
						left join v_UslugaComplex uc ON ut.UslugaComplex_id = uc.UslugaComplex_id -- без вьюхи т.к. выбрать значение другого региона все равно не возможно
						left join v_UslugaComplex ucp ON eupp.UslugaComplex_id = ucp.UslugaComplex_id
						left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id -- услуга на службе
						left join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id -- тест
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid -- исследование
						left join lis.v_Analyzer a on a.Analyzer_id = coalesce(at.Analyzer_id, at_child.Analyzer_id) -- анализатор, только не с ручных методик
						--left join v_UslugaComplex ucgost ON ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
					  	left join lateral (
							Select UslugaComplex_Code from v_UslugaComplex ucgost where ucgost.UslugaComplex_id = uc.UslugaComplex_2011id limit 1
						) ucgost on true
						--left join v_UslugaComplex ucpgost ON ucpgost.UslugaComplex_id = ucp.UslugaComplex_2011id
						left join lateral(
							Select UslugaComplex_Code from v_UslugaComplex ucpgost where ucpgost.UslugaComplex_id = ucp.UslugaComplex_2011id limit 1
						) ucpgost on true
					where
						ut.EvnLabSample_id = :EvnLabSample_id
						and ucms.MedService_id = :MedService_id
						and (a.Analyzer_Code <> '000' OR a.pmUser_insID <> 1)
						{$filter}
				";
				$result_tests = $this->db->query($query, array(
					'EvnLabSample_id' => $data['EvnLabSample_id'],
					'MedService_id' => $resp[0]['MedService_id'],
					'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id']
				));
				if ( is_object($result_tests) ) {
					$resp_tests = $result_tests->result('array');
					foreach($resp_tests as $test) {
						if (!in_array($test['UslugaComplex_Code'], $resp[0]['tests'])) {
							$resp[0]['tests'][] = $test['UslugaComplex_Code'];
						}
						if (!in_array($test['ParentUslugaComplex_Code'], $resp[0]['targets'])) {
							$resp[0]['targets'][] = $test['ParentUslugaComplex_Code'];
						}
					}
				}

				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Возвращает список проб для еще не созданой заявки по выбранной комплексной услуге, службе и заявке
	 *
	 * @param int $UslugaComplex_id Выбранная корневая комплекснеая услуга в заявке
	 * @param int $MedService_id Служба
	 * @param int $EvnLabRequest_id заявка на проведеление лабораторного обследования
	 * @return bool|mixed
	 * @throws Exception
	 */

	function loadLabSampleFrame($data) {
		$query = "
			select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.RefSample_id as \"RefSample_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.MedService_id as \"MedService_id\",
				substring(els.EvnLabSample_Num from 9 for 4) as \"EvnLabSample_ShortNum\",
				els.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
				rm.RefMaterial_Name as \"RefMaterial_Name\",
				to_char (els.EvnLabSample_setDT, 'HH24:MI dd.mm.yyyy') as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample els
				left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
				left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
			where
				els.EvnLabRequest_id = :EvnLabRequest_id
		";

		$params = array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		);

		//return $this->queryResult($query, $params);
		$res = $this->db->query($query, $params);
		$res = $res->result('array');
		return $res;
	}

	/**
	 * Загрузка списка проб для рабочего списка
	 */
	function loadEvnLabSampleListForWorksheet($data) {
		$query = "
			SELECT
				-- select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				COALESCE(ps.Person_SurName||' ','') || COALESCE(ps.Person_FirName||' ','') || COALESCE(ps.Person_SecName,'') as \"EvnLabSample_Fio\",
				cast(awels.AnalyzerWorksheetEvnLabSample_X as varchar) || ' ' || cast(awels.AnalyzerWorksheetEvnLabSample_Y as varchar) as \"EvnLabSample_Position\",
				-- end select
			FROM
				-- from
				v_EvnLabSample els
				inner join lis.v_AnalyzerWorksheetEvnLabSample awels on els.EvnLabSample_id = awels.EvnLabSample_id
				left join v_PersonState ps on ps.Person_id = els.Person_id
				-- end from
			WHERE
				-- where
				awels.AnalyzerWorksheet_id = :AnalyzerWorksheet_id
				-- end where
			ORDER BY
				-- order by
				awels.AnalyzerWorksheetEvnLabSample_X, awels.AnalyzerWorksheetEvnLabSample_Y
				-- end order by
			";

		$params = array(
			'AnalyzerWorksheet_id' => $data['AnalyzerWorksheet_id']
		);

		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Функция кэширования нормальности результатов в пробе
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 */
	function ReCacheLabSampleIsOutNorm($data)
	{
		// достаём результаты пробы
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_Result as \"UslugaTest_Result\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\"
			from
				v_UslugaTest
			where
				EvnLabSample_id = :EvnLabSample_id
				and UslugaTest_ResultValue IS NOT NULL
				and UslugaTest_ResultValue <> ''
		";

		$result = $this->db->query($query, array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		$EvnLabSample_IsOutNorm = 1;

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				if (!empty($respone['UslugaTest_ResultQualitativeNorms'])) {
					$UslugaTest_ResultQualitativeNorms = json_decode($respone['UslugaTest_ResultQualitativeNorms'], true);
					array_walk_recursive($UslugaTest_ResultQualitativeNorms, 'ConvertFromUTF8ToWin1251');
					if (!(is_array($UslugaTest_ResultQualitativeNorms) && in_array($respone['UslugaTest_ResultValue'], $UslugaTest_ResultQualitativeNorms))) {
						$EvnLabSample_IsOutNorm = 2;
					}
				} else {
					// только числовые нормы можно сравнить
					if (!is_numeric(trim(str_replace(",", ".", $respone['UslugaTest_ResultLower'])))) {
						$respone['UslugaTest_ResultLower'] = null;
					}
					if (!is_numeric(trim(str_replace(",", ".", $respone['UslugaTest_ResultUpper'])))) {
						$respone['UslugaTest_ResultUpper'] = null;
					}
					if (
						is_numeric(trim(str_replace(",", ".", $respone['UslugaTest_ResultValue'])))
					) {
						if (!(
							(!isset($respone['UslugaTest_ResultLower']) || floatval(str_replace(",", ".", $respone['UslugaTest_ResultValue'])) >= floatval(str_replace(",", ".", $respone['UslugaTest_ResultLower'])))
							&& (!isset($respone['UslugaTest_ResultUpper']) || floatval(str_replace(",", ".", $respone['UslugaTest_ResultValue'])) <= floatval(str_replace(",", ".", $respone['UslugaTest_ResultUpper'])))
						)) {
							$EvnLabSample_IsOutNorm = 2;
						}
					}
				}
			}
		}
		$query = "
			update
				EvnLabSample
			set
				EvnLabSample_IsOutNorm = :EvnLabSample_IsOutNorm
			where
				Evn_id = :EvnLabSample_id
		";

		$result = $this->db->query($query, array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'EvnLabSample_IsOutNorm' => $EvnLabSample_IsOutNorm
		));
		if (is_object($result)) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Функция кэширования состава пробы
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 * todo будет ли тот же результат от string_agg?
	 */
	function ReCacheLabSampleTest($data)
	{
		$query = "

            with myvars as (
            	select
					substring(Tests.EvnLabSample_Nums from 1 for (length(Tests.EvnLabSample_Nums)-1)) as v_EvnLabSample_Test
				from
					v_EvnLabSample els
					left join lateral (
                        select
                        	string_agg(uc.UslugaComplex_Name, ', ') as EvnLabSample_Nums
                        from v_UslugaComplex uc
							inner join v_UslugaTest ut on ut.UslugaComplex_id = uc.UslugaComplex_id
						where ut.EvnLabSample_id = els.EvnLabSample_id
					) Tests on true
				where
					els.EvnLabSample_id = :EvnLabSample_id
                limit 1
            )

			update
				EvnLabSample
			set
				EvnLabSample_Test = (select v_EvnLabSample_Test from myvars)
			where
				Evn_id = :EvnLabSample_id
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Функция кэширования статуса пробы
	 * Должна вызываться при изменении объектов участвующих в запросе, связанных с данной пробой
	 */
	function ReCacheLabSampleStatus($data)
	{
		$join = "";
		$whereClause = "";
		if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'microbiolab') {
			if (!empty($data['action']) && $data['action'] == 'approve') {
				$join .= 'left join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
							left join v_BactMicroProbeAntibiotic bmpa on bmpa.UslugaTest_id = ut.UslugaTest_id';
				$whereClause .= ' and (bmp.BactMicroProbe_id is not null or bmpa.BactMicroProbeAntibiotic_id is not null)';
			} else {
				$join .= " inner join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id";
			}
		}
		$params = array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		);

		$query = "
			with myvar1 as (
				select
				    CASE
				        WHEN els.EvnLabSample_setDT is null then null
				        -- 5. Забракованные, если отмечен брак
				        WHEN els.DefectCauseType_id IS NOT NULL then 5
				        -- 4. Одобренные. Все результаты одобренны
				        WHEN EUP_EvnUslugaPar_id > 0 and NOTAPP_EvnUslugaPar_id = 0 then 4
				        -- 6. Частично одобренные. Есть одобренный результат и есть не одобренный результат
				        WHEN EUP_EvnUslugaPar_id > 0 then 6
				        -- 3. Выполненные. Получено с анализатора или заполнены хоть какие-то результаты
				        WHEN EUP_HAS_RESULT_EvnUslugaPar_id > 0 then 3
				        -- 2. В работе. Отправлено на анализатор
				        WHEN AN.Link_id IS NOT NULL then 2
				        -- 7. В работе (ручные методики)
				        WHEN a.Analyzer_Code = '000' and a.pmUser_insID = 1 and COALESCE(mstdid.MedServiceType_SysNick,'lab') <> 'pzm' then 7
				        -- 1. Новые
				        ELSE 1
				    END as LabSampleStatus_id
				from
				    v_EvnLabSample els
				    left join v_MedService msdid on msdid.MedService_id = els.MedService_did
				    left join v_MedServiceType mstdid on mstdid.MedServiceType_id = msdid.MedServiceType_id
				    left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
				    left join lateral(
				        select
				            Link_id
				        from
				            lis.v_Link
				        where
				            link_object = 'EvnLabSample'
				            and object_id = els.EvnLabSample_id
				        limit 1
				    ) AN on true
				    left join lateral (
				        select
				            max(case when ut.UslugaTest_ResultApproved = 2 then 1 else 0 end) as EUP_EvnUslugaPar_id,
				            max(case when COALESCE(ut.UslugaTest_ResultApproved, 1) = 1 then 1 else 0 end) as NOTAPP_EvnUslugaPar_id,
				            max(case when ut.UslugaTest_ResultValue IS NOT NULL and ut.UslugaTest_ResultValue <> '' then 1 else 0 end) as EUP_HAS_RESULT_EvnUslugaPar_id
				        from v_UslugaTest ut
						{$join}
						where
							ut.EvnLabSample_id = els.EvnLabSample_id
							and ut.EvnDirection_id is null -- только тесты
							{$whereClause}
				    ) EUP on true
				where
				    els.EvnLabSample_id = :EvnLabSample_id
				limit 1
            ),
            myvar2 as (
            	select
                	EvnLabSample_StudyDT
                from v_EvnLabSample
                where EvnLabSample_id = :EvnLabSample_id
            ),
            myvar3 as (
            	select
                	CASE
                    	when (select LabSampleStatus_id from myvar1) in (1,2,7) then null
                    	when ((select LabSampleStatus_id from myvar1) in (3,4,6) and (select EvnLabSample_StudyDT from myvar2) is null) then dbo.tzgetdate()
                        	else (select EvnLabSample_StudyDT from myvar2)
                    end as EvnLabSample_StudyDT
            )

			update
				EvnLabSample
			set
				LabSampleStatus_id = (select LabSampleStatus_id from myvar1),
				EvnLabSample_StudyDT = (select EvnLabSample_StudyDT from myvar3)
			where
				Evn_id = :EvnLabSample_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			collectEditedData('upd', 'EvnLabSample', $params['EvnLabSample_id']);
			return true;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return string
	 */
	function getIFAEvnLabSamples($data) {
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
				UT.EvnLabSample_id as \"EvnLabSample_id\"
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
		$EvnLabSamples_ids = [];
		foreach ($result as $obj) {
			$EvnLabSamples_ids[] = $obj['EvnLabSample_id'];
		}

		return implode(',', $EvnLabSamples_ids);
	}

	/**
	 * Функция чтения рабочего журнала проб
	 * @param $Lpu_id
	 * @param null $EvnDirection_IsCito
	 * @param null $begDate
	 * @param null $endDate
	 * @internal param array $data
	 * @return bool|mixed
	 */
	function loadWorkList($data){
		try {
			$filter = "";
			$msfilter = "";

			if(!empty($data['formMode']) && $data['formMode'] == 'ifa') {
				$EvnLabSamples_ids = $this->getIFAEvnLabSamples($data);
				if (empty($EvnLabSamples_ids)) {
					return [];
				}
				$filter .= " and els.EvnLabSample_id in ({$EvnLabSamples_ids})";
			}

			if (!empty($data['EvnDirection_IsCito'])) {
				$filter .= " and (COALESCE(elr.EvnLabRequest_IsCito, 1) = :EvnDirection_IsCito)";
			}

			if (!empty($data['EvnLabSample_IsOutNorm'])) {
				$filter .= " and (COALESCE(els.EvnLabSample_IsOutNorm, 1) = :EvnLabSample_IsOutNorm)";
			}

			if (!empty($data['Person_ShortFio'])) {
				if (allowPersonEncrypHIV()) {
					$filter .= " and (COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') LIKE upper(:Person_ShortFio) || '%' or peh.PersonEncrypHIV_Encryp LIKE :Person_ShortFio || '%')";
				} else {
					$filter .= " and COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','') LIKE upper(:Person_ShortFio) || '%'";
				}
			}

			if (!empty($data['EvnDirection_Num'])) {
				$filter .= " and ed.EvnDirection_Num LIKE '%' || :EvnDirection_Num || '%'";
			}

			if( !empty( $data['Lpu_sid']) ) {
				$filter .= " and ed.Lpu_sid = :Lpu_sid";
			}

			if( !empty( $data['LpuSection_id'] ) ) {
				$filter .= " and ed.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}

			if( !empty( $data['MedStaffFact_id'] ) ) {
				$filter .= " and ed.MedStaffFact_id = :MedStaffFact_id";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}

			if(!empty($data['UslugaComplex_id'])){
				$filter.=" and exists(
					select *
					from v_EvnUsluga EvnUsluga
					inner join v_Evn Evn  on Evn.Evn_id = EvnUsluga.EvnUsluga_id and Evn.EvnClass_id = 47
					where EvnUsluga.EvnDirection_id = elr.EvnDirection_id and EvnUsluga.UslugaComplex_id =  :UslugaComplex_id
				)";
				$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
			}

			if( !empty( $data['EvnLabRequest_RegNum'] ) ) {
				$filter .= " and elr.EvnLabRequest_RegNum = :EvnLabRequest_RegNum";
				$params['EvnLabRequest_RegNum'] = $data['EvnLabRequest_RegNum'];
			}

			if (!empty($data['EvnLabSample_BarCode'])) {
				$filter .= " and els.EvnLabSample_BarCode LIKE :EvnLabSample_BarCode || '%'";
			}

			if (!empty($data['EvnLabSample_ShortNum'])) {
				$filter .= " and substring(els.EvnLabSample_Num from 9 for 4) = :EvnLabSample_ShortNum";
			}

			if (!empty($data['LabSampleStatus_id'])) {
				$filter .= " and els.LabSampleStatus_id = :LabSampleStatus_id";
			}
			if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'reglab') {
				$msfilter = " or els.MedService_id IN (select MSL.MedService_lid from MedServiceLink MSL where msl.MedService_id = :MedService_id)";
			}
			$allow_encryp = allowPersonEncrypHIV()?'1':'0';

			$addWhenBeg = "when 1=0 then null";
			$addWhenEnd = "when 1=0 then null";
			$withoutDateStatus = array();
			if (empty($data['filterNewELSByDate'])) {
				$withoutDateStatus[] = 1;
			}
			if (empty($data['filterWorkELSByDate'])) {
				$withoutDateStatus[] = 2;
				$withoutDateStatus[] = 7;
			}
			if (empty($data['filterDoneELSByDate'])) {
				$withoutDateStatus[] = 3;
			}

			if (!empty($withoutDateStatus)) {
				$addWhenBeg = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :begDate";
				$addWhenEnd = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :endDate";
			}

			$datefilter = "
				and (:begDate::timestamp <= case
					{$addWhenBeg}
					else COALESCE(
						cast(els.EvnLabSample_StudyDT as date),
						cast(els.EvnLabSample_setDate as date),
						cast(elr.EvnLabRequest_didDate as date)
					) end)
				and (:endDate::timestamp >= case
					{$addWhenEnd}
					else COALESCE(
						cast(els.EvnLabSample_StudyDT as date),
						cast(els.EvnLabSample_setDate as date),
						cast(elr.EvnLabRequest_didDate as date)
					) end)
			";

			$bdzFields = '';
			$bdzJoin = '';
			if (getRegionNick() == 'kz') {
				$bdzFields = "
					,case
						when pers.Person_IsInFOMS = 1 then 'orange'
						when pers.Person_IsInFOMS = 2 then 'true'
						else 'false' 
					end as \"Person_IsBDZ\"
				";
				$bdzJoin = "
					inner join Person pers on pers.Person_id = table1.Person_id
				";
			}

			$query = "
				with tempTable as (
					SELECT
						els.EvnLabSample_id,
						substring(els.EvnLabSample_Num from 9 for 4) as EvnLabSample_ShortNum,
						CASE WHEN COALESCE(elr.EvnLabRequest_IsCito, 1) = 2 THEN '!' else '' END AS EvnDirection_IsCito,
						els.LabSampleStatus_id,
						els.EvnLabSample_Num,
						els.EvnLabSample_BarCode,
						els.EvnLabRequest_id,
						els.MedService_id,
						rm.RefMaterial_id,
						rm.RefMaterial_Name,
						ed.EvnDirection_id,
						ed.EvnDirection_Num,
						case
							when 1 = ed.PrehospDirect_id then COALESCE(ls.LpuSection_Name, Lpu.Lpu_Nick) -- 1 Отделение ЛПУ (Если не выбрали то ЛПУ)
							when 2 = ed.PrehospDirect_id then Lpu.Lpu_Nick -- 2 Другое ЛПУ --Lpu_sid - Направившее ЛПУ
							when ed.PrehospDirect_id in ( 3, 4, 5, 6 ) then Org.Org_nick -- 3 Другая организация -- 4 Военкомат -- 5 Скорая помощь -- 6 Администрация -- Org_sid - Направившая организация
							when 7 = ed.PrehospDirect_id then 'Пункт помощи на дому' --7Пункт помощи на дому
							else COALESCE(ls.LpuSection_Name, Lpu_Nick)
						end as PrehospDirect_Name,
						COALESCE(LSS.LabSampleStatus_SysNick, 'new') as ProbaStatus,
						ps.Person_id,
						case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
							else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| ps.Person_FirName,'') || COALESCE(' '|| ps.Person_SecName,'')
						end as Person_FIO,
						case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
							else COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName from 1 for 1) || '.','') || COALESCE(' '|| SUBSTRING(ps.Person_SecName from 1 for 1) || '.','')
						end as Person_ShortFio,
						to_char(PS.Person_Birthday, 'dd.mm.yyyy') as Person_Birthday,
						case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
						to_char (els.EvnLabSample_setDT, 'HH24:MI dd.mm.yyyy') as EvnLabSample_setDT,
						els.EvnLabSample_StudyDT as EvnLabSample_StudyDT,
						elr.EvnLabRequest_BarCode as EvnLabRequest_BarCode,
						elr.UslugaComplex_id as UslugaComplexTarget_id,
						a.Analyzer_id,
						a.Analyzer_Name,
						a.Analyzer_2wayComm,
						AWELS.AnalyzerWorksheetEvnLabSample_id,
						link.lis_id as lis_id, -- идентификатор объекта в LIS
						ls.LpuSection_Code,
						ls.LpuSection_Name,
						ms.MedService_Nick,
						MP.Person_SurName as EDMedPersonalSurname,
						elr.EvnLabRequest_RegNum,
						elr.EvnLabRequest_UslugaName,
						Lpu.Lpu_Nick,
						COALESCE(els.EvnLabSample_IsOutNorm, 1) as EvnLabSample_IsOutNorm
					FROM
						dbo.v_EvnLabSample els
						inner join v_EvnLabRequest elr on els.EvnLabRequest_id = elr.EvnLabRequest_id
						left join v_MedService ms on ms.MedService_id = elr.MedService_id
						left join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
						left join v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
						left join v_Lpu Lpu on Lpu.Lpu_id = ed.Lpu_sid
						left join v_Org Org on Org.Org_id = ed.Org_sid
						left join v_MedPersonal MP on MP.MedPersonal_id = ed.MedPersonal_id
						left join v_PersonState ps on elr.Person_id = ps.Person_id
						left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id
						left join v_LabSampleStatus lss on lss.LabSampleStatus_id = els.LabSampleStatus_id
						left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
						left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
						--left join lis.v_Link link on link.object_id = els.EvnLabSample_id and link.link_object = 'EvnLabSample'
						left join lateral (
							Select
								link.lis_id
							from lis.v_Link link
							where link.object_id = els.EvnLabSample_id and link.link_object = 'EvnLabSample'
							limit 1
						) link on true
						left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
						left join lateral(
							select
								AnalyzerWorksheetEvnLabSample_id
							from
								lis.v_AnalyzerWorksheetEvnLabSample
							where
								EvnLabSample_id = els.EvnLabSample_id
							limit 1
						) AWELS on true
					WHERE
						els.Lpu_id = :Lpu_id
						and els.EvnLabSample_setDT is not null
						and (els.MedService_id = :MedService_id {$msfilter})
						{$filter}
						{$datefilter}
				)

				SELECT
					table1.EvnLabSample_id as \"EvnLabSample_id\",
					table1.EvnLabSample_ShortNum as \"EvnLabSample_ShortNum\",
					table1.EvnDirection_IsCito as \"EvnDirection_IsCito\",
					table1.LabSampleStatus_id as \"LabSampleStatus_id\",
					table1.EvnLabSample_Num as \"EvnLabSample_Num\",
					table1.EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
					table1.EvnLabRequest_id as \"EvnLabRequest_id\",
					table1.MedService_id as \"MedService_id\",
					table1.RefMaterial_id as \"RefMaterial_id\",
					table1.RefMaterial_Name as \"RefMaterial_Name\",
					table1.EvnDirection_id as \"EvnDirection_id\",
					table1.EvnDirection_Num as \"EvnDirection_Num\",
					table1.PrehospDirect_Name as \"PrehospDirect_Name\",
					table1.ProbaStatus as \"ProbaStatus\",
					table1.Person_id as \"Person_id\",
					table1.Person_FIO as \"Person_FIO\",
					table1.Person_ShortFio as \"Person_ShortFio\",
					table1.Person_Birthday as \"Person_Birthday\",
					table1.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
					table1.EvnLabSample_setDT as \"EvnLabSample_setDT\",
					table1.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
					table1.EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
					table1.UslugaComplexTarget_id as \"UslugaComplexTarget_id\",
					table1.Analyzer_id as \"Analyzer_id\",
					table1.Analyzer_Name as \"Analyzer_Name\",
					table1.Analyzer_2wayComm as \"Analyzer_2wayComm\",
					table1.AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
					table1.lis_id, -- идентификатор объекта в LIS
					table1.LpuSection_Code as \"LpuSection_Code\",
					table1.MedService_Nick as \"MedService_Nick\",
					table1.EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\",
					table1.EDMedPersonalSurname as \"EDMedPersonalSurname\",
					table1.EvnLabRequest_RegNum as \"EvnLabRequest_RegNum\",
					table1.Lpu_Nick as \"Lpu_Nick\",
					table1.LpuSection_Name as \"LpuSection_Name\",
					table1.EvnLabRequest_UslugaName as \"EvnLabRequest_UslugaName\",
					ut.cnt as \"EvnLabSample_Tests\"
					{$bdzFields}
				FROM
					tempTable as table1
					left join lateral(
						select
							count(*) as cnt
						from
							v_UslugaTest ut
						where
							ut.EvnLabSample_id = table1.EvnLabSample_id
							and ut.EvnDirection_id is null
					) ut on true
					{$bdzJoin}
			";

			//echo getDebugSQL($query, $data); die();
			return $this->queryResult($query, $data);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Достаем услуги для проб
	 * @return array услуги
	 */
	function getSampleUsluga($data) {
		$EvnLabSample_id = json_decode($data['EvnLabSample_id']);
		$filter = "els.EvnLabSample_id IN (".implode(',', $EvnLabSample_id).")";

		$query = "
			with myvars as (
            	select dbo.tzgetdate() as curdate
            )
			Select distinct
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ucp.UslugaComplex_id as \"UslugaComplex_id\",
				COALESCE(analyzertest.UslugaComplex_ParentName, ucp.UslugaComplex_Name) as \"ResearchName\"
			From
				v_EvnLabSample els
				inner join v_UslugaTest ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join v_EvnUslugaPar eup on ut.UslugaTest_pid = eup.EvnUslugaPar_id and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = COALESCE(at_child.AnalyzerTest_pid, at.AnalyzerTest_id) -- родительское исследование, если есть
						inner join v_UslugaComplexMedService ucms_at on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = COALESCE(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id) -- родительская услуга, если есть
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						ucms_at.UslugaComplex_id = ut.UslugaComplex_id
						and ucms_parent.UslugaComplex_id = eup.UslugaComplex_id
						and ucms_at.MedService_id = els.MedService_id
						and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
						and COALESCE(a.Analyzer_IsNotActive, 1) = 1
						and (at.AnalyzerTest_endDT >= (select curdate from myvars) or at.AnalyzerTest_endDT is null)
						and (at_child.AnalyzerTest_endDT >= (select curdate from myvars) or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= (select curdate from myvars) or uctest.UslugaComplex_endDT is null)
					order by
						at_child.AnalyzerTest_pid desc -- в первую очередь ищем тест
                    limit 1
				) analyzertest on true
			where
			{$filter}
		";

		//echo getDebugSQL($query, array());exit;
		return $this->queryResult($query, array());
	}

	/**
	 * Функция чтения журнала отбраковки проб
	 * @param $Lpu_id
	 * @param null $EvnDirection_IsCito
	 * @param null $begDate
	 * @param null $endDate
	 * @internal param array $data
	 * @return bool|mixed
	 */
	function loadDefectList($data){
		try {
			$filter = "";

			if (!empty($data['EvnDirection_IsCito'])) {
				$filter .= " and (COALESCE(elr.EvnLabRequest_IsCito, 1) = :EvnDirection_IsCito)";
			}

			if (!empty($data['UslugaComplex_id'])) {
				$filter .= " and exists(
					select
						eu.EvnUslugaPar_id
					from
						v_EvnUslugaPar eu
					where
						eu.EvnLabSample_id = els.EvnLabSample_id and eu.UslugaComplex_id = :UslugaComplex_id
					limit 1
				)";
			}

			if (!empty($data['RefMaterial_id'])) {
				$filter .= " and rm.RefMaterial_id = :RefMaterial_id";
			}

			if (!empty($data['DefectCauseType_id'])) {
				$filter .= " and els.DefectCauseType_id = :DefectCauseType_id";
			}

			if (!empty($data['MedService_id']) && $this->regionNick == 'vologda') {
				$filter .= " and els.MedService_did = :MedService_sid";
			}

			$query = "
				SELECT
					els.EvnLabSample_id as \"EvnLabSample_id\",
					CASE WHEN COALESCE(elr.EvnLabRequest_IsCito, 1) = 2 THEN '!' else '' END AS \"EvnDirection_IsCito\",
					cast(lss.LabSampleStatus_Code as varchar) || '. ' || lss.LabSampleStatus_Name as \"EvnLabSample_Status\",
					EvnLabSample_BarCode as \"EvnLabSample_BarCode\",
					rm.RefMaterial_id as \"RefMaterial_id\",
					RefMaterial_Name as \"RefMaterial_Name\",
					ed.EvnDirection_Num as \"EvnDirection_Num\",
					els.DefectCauseType_id as \"DefectCauseType_id\",
					CASE WHEN (els.MedService_did = els.MedService_sid) THEN 2 else 0 END AS \"MedService_flag\", --флаг: если проба забракована там же, где взята, то 2, иначе 0
					dct.DefectCauseType_Name as \"DefectCauseType_Name\",
					null as \"EvnLabSample_UslugaList\", -- список исследований, но его еще надо будет как-то получать
					els.EvnLabSample_setDT as \"EvnLabSample_setDT\",
					els.EvnLabSample_StudyDT as \"EvnLabSample_StudyDT\",
					link.lis_id as \"lis_id\" -- идентификатор объекта в LIS
				FROM
					v_EvnLabSample els
					inner join v_EvnLabRequest elr on els.EvnLabRequest_id = elr.EvnLabRequest_id
					left join lis.v_DefectCauseType dct on dct.DefectCauseType_id = els.DefectCauseType_id
					left join v_EvnDirection_all ed on ed.EvnDirection_id = elr.EvnDirection_id
					left join v_LabSampleStatus lss on lss.LabSampleStatus_id = els.LabSampleStatus_id
					left join v_RefSample rs on rs.RefSample_id = els.RefSample_id
					left join v_RefMaterial rm on rm.RefMaterial_id = rs.RefMaterial_id
					left join lis.v_Link link on link.object_id = els.EvnLabSample_id and link.link_object = 'EvnLabSample'
				WHERE
					els.Lpu_id = :Lpu_id
					{$filter}
					AND els.DefectCauseType_id IS NOT NULL
					AND ((cast(COALESCE(els.EvnLabSample_setDT,elr.EvnLabRequest_didDT) as date) BETWEEN :begDate AND :endDate) OR :begDate is null OR :endDate is null )";

			$result = $this->db->query($query, $data);
			//print_r($result);echo getDebugSQL($query,$data);die();
			if (is_object($result)) {
				$response = $result->result('array');
				return $response;
			}
			else {
				return false;
			}
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	/**
	 * @return Abstract_model
	 */
	public function validate()
	{
		$this->valid = true;
		// TODO: Implement validate() method.
		return $this;
	}

	/**
	 * Проверка на возможность отмены теста
	 */
	function checkTestCanBeCanceled($data) {
		$query = "
			Select
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				COALESCE(ut.UslugaTest_ResultApproved,1) as \"UslugaTest_ResultApproved\"
			From
				v_EvnLabSample ls
				LEFT JOIN v_UslugaTest ut ON ut.EvnLabSample_id = ls.EvnLabSample_id
			where
				ut.UslugaTest_id = :UslugaTest_id and (COALESCE(ut.UslugaTest_ResultApproved, 1) = 2 OR (ut.UslugaTest_ResultValue IS NOT NULL and ut.UslugaTest_ResultValue <> ''))
			limit 1
			";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Обновление результата
	 */
	function updateResult($data) {
		$resp = array();

		// получаем данные о тесте
		$query = "
			select
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.Usluga_id as \"Usluga_id\",
				ut.PayType_id as \"PayType_id\",
				ut.UslugaPlace_id as \"UslugaPlace_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\",
				ut.UslugaTest_rid as \"UslugaTest_rid\",
				ut.UslugaTest_setDT as \"UslugaTest_setDT\",
				ut.Lpu_id as \"Lpu_id\",
				ut.Server_id as \"Server_id\",
				ut.PersonEvn_id as \"PersonEvn_id\",
				ut.EvnDirection_id as \"EvnDirection_id\",
				ut.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				ut.UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				--ut.LabTest_id as \"LabTest_id\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				a.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\",
				a.Analyzer_IsAutoGood as \"Analyzer_IsAutoGood\"
			from
				v_UslugaTest ut
				left join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
				left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
			where
				ut.UslugaTest_id = :UslugaTest_id
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$resp = $result->result('array');
		} else {
			return false;
		}

		$EvnUslugaParChanged = array();

		//to_timestamp не нужен
		if (count($resp) > 0) {
			$updDate = ':UslugaTest_setDT';
			$chkDate = 'null';

			// подменяем результат
			if ($data['updateType'] == 'fromLISwithRefValues') {
				$resp[0]['UslugaTest_ResultValue'] = $data['UslugaTest_ResultValue'];
				// при изменении результата убираем одобрение
				if (!in_array($resp[0]['UslugaTest_pid'], $EvnUslugaParChanged)) {
					$EvnUslugaParChanged[] = $resp[0]['UslugaTest_pid'];
				}
				$resp[0]['UslugaTest_ResultApproved'] = 1;

				if (isset($data['RefValues_id']))
					$resp[0]['RefValues_id'] = $data['RefValues_id'];
				if (isset($data['UslugaTest_ResultLower']))
					$resp[0]['UslugaTest_ResultLower'] = $data['UslugaTest_ResultLower'];
				if (isset($data['UslugaTest_ResultUpper']))
					$resp[0]['UslugaTest_ResultUpper'] = $data['UslugaTest_ResultUpper'];
				if (isset($data['UslugaTest_ResultUnit']))
					$resp[0]['UslugaTest_ResultUnit'] = $data['UslugaTest_ResultUnit'];
				
				if ($data['UslugaTest_ResultValue']!='') {
					if (!empty($data['UslugaTest_setDT'])) {
						$resp[0]['UslugaTest_setDT'] = $data['UslugaTest_setDT'];
					} else {
						$updDate='(dbo.tzGetDate())';
					}
				}
			}

			if ($data['updateType'] == 'fromLIS') {
				$resp[0]['UslugaTest_ResultValue'] = $data['UslugaTest_ResultValue'];
				// при изменении результата убираем одобрение
				if (!in_array($resp[0]['UslugaTest_pid'], $EvnUslugaParChanged)) {
					$EvnUslugaParChanged[] = $resp[0]['UslugaTest_pid'];
				}
				$resp[0]['UslugaTest_ResultApproved'] = 1;
				if ($data['UslugaTest_ResultValue']!='') {
					if (!empty($data['UslugaTest_setDT'])) {
						$resp[0]['UslugaTest_setDT'] = $data['UslugaTest_setDT'];
					} else {
						$updDate='(dbo.tzGetDate())';
					}
				}
			}

			if ($data['updateType'] == 'value') {
				$resp[0]['UslugaTest_ResultValue'] = $data['UslugaTest_ResultValue'];
				// при изменении результата убираем одобрение
				if (!in_array($resp[0]['UslugaTest_pid'], $EvnUslugaParChanged)) {
					$EvnUslugaParChanged[] = $resp[0]['UslugaTest_pid'];
				}
				$resp[0]['UslugaTest_ResultApproved'] = 1;
				if($data['UslugaTest_ResultValue']!=''){
					$updDate='(dbo.tzGetDate())';
				} else {
					$updDate='NULL';
				}
			}

			if ($data['updateType'] == 'comment') {
				$resp[0]['UslugaTest_Comment'] = $data['UslugaTest_Comment'];
			}

			$isSetResultValue = isset($data['UslugaTest_ResultValue']) && $data['UslugaTest_ResultValue'] != '';;
			$isForm250 = $isSetResultValue && isset($data['sourceName']) && $data['sourceName'] === 'form250';
			$isAutoOk = $isSetResultValue && $resp[0]['Analyzer_IsAutoOk'] == 2;
			$isAutoGood = $isAutoOk && $resp[0]['Analyzer_IsAutoGood'] == 2;

			//Автоодобрение без патологии
			if($isAutoGood) {
				$upperValue = $this->getFloatResult($resp[0]['UslugaTest_ResultUpper']);
				$lowerValue = $this->getFloatResult($resp[0]['UslugaTest_ResultLower']);
				$isQualitativeTest = !empty($resp[0]['UslugaTest_ResultQualitativeNorms']);
				$value = $isQualitativeTest ? $data['UslugaTest_ResultValue'] : $this->getFloatResult($data['UslugaTest_ResultValue']);
				if($isQualitativeTest)
					$qualitativeNorms = json_decode($resp[0]['UslugaTest_ResultQualitativeNorms'], true);
			}

			//Автоодобрение
			switch(true) {
				case !empty($data['isAutoApprove']): // если пришло из файлового обмена
				case $isForm250: // при заполнении формы 250У
				case $isAutoOk && !$isAutoGood:
				case $isAutoGood && !$isQualitativeTest && !$this->isPathologicalQuantitativeTest($value, $lowerValue, $upperValue):
				case $isAutoGood && $isQualitativeTest && !$this->isPathologicalQualitativeTest($value, $qualitativeNorms);
					$resp[0]['UslugaTest_ResultApproved'] = 2;
					$chkDate = '(dbo.tzgetdate())';
					break;
				default:
					$resp[0]['UslugaTest_ResultApproved'] = 1;
					break;
			}

			if (!empty($data['UslugaTest_RefValues'])) {
				$data['UslugaTest_RefValues'] = toUtf($data['UslugaTest_RefValues']);
				$UslugaTest_RefValues = json_decode($data['UslugaTest_RefValues'], true);
				array_walk($UslugaTest_RefValues, 'ConvertFromUTF8ToWin1251');
				$resp[0]['UslugaTest_ResultQualitativeNorms'] = $UslugaTest_RefValues['UslugaTest_ResultQualitativeNorms'];
				$UslugaTest_ResultQualitativeText = '';
				if (!empty($resp[0]['UslugaTest_ResultQualitativeNorms'])) {
					$UslugaTest_ResultQualitativeNorms = json_decode($resp[0]['UslugaTest_ResultQualitativeNorms'], true);
					if (is_array($UslugaTest_ResultQualitativeNorms)) {
						foreach($UslugaTest_ResultQualitativeNorms as $UslugaTest_ResultQualitativeNorm) {
							if (!empty($UslugaTest_ResultQualitativeText)) {
								$UslugaTest_ResultQualitativeText .= ', ';
							}
							$UslugaTest_ResultQualitativeText .= $UslugaTest_ResultQualitativeNorm;
						}
					}
				}
				$resp[0]['UslugaTest_ResultQualitativeText'] = $UslugaTest_ResultQualitativeText;
				$resp[0]['UslugaTest_ResultNorm'] = $UslugaTest_RefValues['UslugaTest_ResultNorm'];
				$resp[0]['UslugaTest_ResultCrit'] = $UslugaTest_RefValues['UslugaTest_ResultCrit'];
				$resp[0]['UslugaTest_ResultLower'] = $UslugaTest_RefValues['UslugaTest_ResultLower'];
				$resp[0]['UslugaTest_ResultUpper'] = $UslugaTest_RefValues['UslugaTest_ResultUpper'];
				$resp[0]['UslugaTest_ResultLowerCrit'] = $UslugaTest_RefValues['UslugaTest_ResultLowerCrit'];
				$resp[0]['UslugaTest_ResultUpperCrit'] = $UslugaTest_RefValues['UslugaTest_ResultUpperCrit'];
				$resp[0]['UslugaTest_ResultUnit'] = $UslugaTest_RefValues['UslugaTest_ResultUnit'];
				$resp[0]['UslugaTest_Comment'] = $UslugaTest_RefValues['UslugaTest_Comment'];
				$resp[0]['RefValues_id'] = $UslugaTest_RefValues['RefValues_id'];
				$resp[0]['Unit_id'] = $UslugaTest_RefValues['Unit_id'];
			}
			if ( !empty($data['UslugaTest_Comment']) && $data['updateType'] != 'comment' ) {
				if ( !empty( $resp[0]['UslugaTest_Comment'] ) ) $resp[0]['UslugaTest_Comment'] .= ' | ';
				//пишем комментарий для теста, полученный от анализатора (внешней службы)
				$resp[0]['UslugaTest_Comment'] .= $data['UslugaTest_Comment'];
			}

			$ResultDataJson = json_encode(array(
				'EUD_value'               => toUtf(trim($resp[0]['UslugaTest_ResultValue'])),
				'EUD_lower_bound'         => toUtf(trim($resp[0]['UslugaTest_ResultLower'])),
				'EUD_upper_bound'         => toUtf(trim($resp[0]['UslugaTest_ResultUpper'])),
				'EUD_unit_of_measurement' => toUtf(trim($resp[0]['UslugaTest_ResultUnit']))
			));

			// сохраняем
			$query = "
				select
					UslugaTest_id as \"UslugaTest_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_uslugatest_upd(
					UslugaTest_id := :UslugaTest_id, -- bigint
					UslugaTest_pid := :UslugaTest_pid,       -- bigint
					UslugaTest_rid := :UslugaTest_rid,       -- bigint
					UslugaTest_setDT := {$updDate},   -- date
					Lpu_id := :Lpu_id,                           -- bigint
					Server_id := :Server_id,                     -- bigint
					PersonEvn_id := :PersonEvn_id,               -- bigint
					UslugaComplex_id := :UslugaComplex_id,
					EvnDirection_id := :EvnDirection_id,
					Usluga_id := :Usluga_id,
					PayType_id := :PayType_id,                   -- bigint
					UslugaPlace_id := :UslugaPlace_id,           -- bigint
					UslugaTest_Result := :UslugaTest_Result, -- bigint
					UslugaTest_ResultApproved := :UslugaTest_ResultApproved,
					UslugaTest_CheckDT := {$chkDate},
					UslugaTest_Comment := :UslugaTest_Comment,
					UslugaTest_ResultLower := :UslugaTest_ResultLower,
					UslugaTest_ResultUpper := :UslugaTest_ResultUpper,
					UslugaTest_ResultQualitativeNorms := :UslugaTest_ResultQualitativeNorms,
					UslugaTest_ResultQualitativeText := :UslugaTest_ResultQualitativeText,
					RefValues_id := :RefValues_id,
					LabTest_id := :LabTest_id,
					Unit_id := :Unit_id,
					UslugaTest_ResultLowerCrit := :UslugaTest_ResultLowerCrit,
					UslugaTest_ResultUpperCrit := :UslugaTest_ResultUpperCrit,
					UslugaTest_ResultUnit := :UslugaTest_ResultUnit,
					UslugaTest_ResultValue := cast(:UslugaTest_ResultValue as varchar),
					EvnLabSample_id := :EvnLabSample_id,         -- bigint
					pmUser_id := :pmUser_id                     -- bigint
				)
			";

			$params = array(
				'UslugaTest_id'   => $data['UslugaTest_id'],
				'UslugaTest_pid'  => $resp[0]['UslugaTest_pid'], //$this->EvnLabSample_id,
				'UslugaTest_rid'  => $resp[0]['UslugaTest_rid'],
				'UslugaTest_setDT'  => $resp[0]['UslugaTest_setDT'],
				'Lpu_id'         => $resp[0]['Lpu_id'],
				'Server_id'      => $resp[0]['Server_id'],
				'PersonEvn_id'   => $resp[0]['PersonEvn_id'],
				'UslugaComplex_id' => $resp[0]['UslugaComplex_id'],
				'Usluga_id'      => $resp[0]['Usluga_id'],
				'PayType_id'     => $resp[0]['PayType_id'],
				'UslugaPlace_id' => $resp[0]['UslugaPlace_id'],
				'EvnDirection_id' => $resp[0]['EvnDirection_id'],
				'UslugaTest_Result' => $ResultDataJson,
				'EvnLabSample_id' => $resp[0]['EvnLabSample_id'],
				'pmUser_id'      => $data['pmUser_id'],
				'UslugaTest_ResultApproved' => $resp[0]['UslugaTest_ResultApproved'],
				'UslugaTest_Comment' => $resp[0]['UslugaTest_Comment'],
				'UslugaTest_ResultLower' => $resp[0]['UslugaTest_ResultLower'],
				'UslugaTest_ResultUpper' => $resp[0]['UslugaTest_ResultUpper'],
				'UslugaTest_ResultQualitativeNorms' => $resp[0]['UslugaTest_ResultQualitativeNorms'],
				'UslugaTest_ResultQualitativeText' => $resp[0]['UslugaTest_ResultQualitativeText'],
				'RefValues_id' => !empty($resp[0]['RefValues_id'])?$resp[0]['RefValues_id']:null,
				'LabTest_id' => !empty($resp[0]['LabTest_id'])?$resp[0]['LabTest_id']:null,
				'Unit_id' => !empty($resp[0]['Unit_id'])?$resp[0]['Unit_id']:null,
				'UslugaTest_ResultLowerCrit' => $resp[0]['UslugaTest_ResultLowerCrit'],
				'UslugaTest_ResultUpperCrit' => $resp[0]['UslugaTest_ResultUpperCrit'],
				'UslugaTest_ResultUnit' => $resp[0]['UslugaTest_ResultUnit'],
				'UslugaTest_ResultValue' => ($resp[0]['UslugaTest_ResultValue'])
			);

			$response = $this->queryResult($query, $params);
			if (!is_array($response)) {
				return $this->createError('','Ошибка при изменении результата');
			}
			if (!$this->isSuccessful($response)) {
				return $response;
			}
			collectEditedData('upd', 'UslugaTest', $data['UslugaTest_id']);

			if (!empty($EvnUslugaParChanged)) {
				$this->onChangeApproveResults(array(
					'EvnUslugaParChanged' => $EvnUslugaParChanged,
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			if (empty($data['disableRecache'])) {
				if ($data['updateType'] != 'fromLIS') {
					$this->ReCacheLabSampleIsOutNorm(array(
						'EvnLabSample_id' => $resp[0]['EvnLabSample_id']
					));
				}

				$this->ReCacheLabSampleStatus(array(
					'EvnLabSample_id' => $resp[0]['EvnLabSample_id']
				));

				// кэшируем статус заявки
				$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
				$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
					'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				// кэшируем названия услуг
				$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
					'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				// кэшируем статус проб в заявке
				$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
					'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				//Удаляем статус печати протокола
				$this->EvnLabRequest_model->setProtocolPrintFlag(array(
					'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id']
				));

				// создаём/обновляем протокол
				$this->ReCacheLabRequestByLabSample(array(
					'EvnLabSample_id' => $resp[0]['EvnLabSample_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			collectEditedData('upd', 'UslugaTest', $data['UslugaTest_id']);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Проставление данных о выполнении услуги
	 * (должно выполняться после любого одобрения результатов, либо снятия одобрения, только для тех услуг по которым производились данные действия)
	 */
	function onChangeApproveResults($data) {
		if (!empty($data['EvnUslugaParChanged'])) {
			foreach($data['EvnUslugaParChanged'] as $EvnUslugaPar_id) {
				$resp_eup = $this->queryResult("
					select
						eupp.EvnUslugaPar_id as \"EvnUslugaPar_pid\",
						eupp.UslugaComplex_id as \"UslugaComplex_id\",
						ut.UslugaTest_id as \"UslugaTest_id\",
						eupp.EvnDirection_id as \"EvnDirection_id\",
						eupp.EvnPrescr_id as \"EvnPrescr_id\",
						elr.EvnLabRequest_id as \"EvnLabRequest_id\",
						eupp.EvnUslugaPar_pid as \"Evn_pid\",
						e.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_EvnUslugaPar eupp
						inner join v_EvnLabRequest elr on elr.EvnDirection_id = eupp.EvnDirection_id
						left join v_UslugaTest ut on ut.UslugaTest_pid = eupp.EvnUslugaPar_id and ut.UslugaTest_ResultApproved = 2
					 	left join v_Evn e on e.Evn_id = eupp.EvnUslugaPar_pid
					where
						eupp.EvnUslugaPar_id = :EvnUslugaPar_id
					limit 1
				", array(
					'EvnUslugaPar_id' => $EvnUslugaPar_id
				));

				if (!empty($resp_eup[0]['EvnUslugaPar_pid'])) {
					// апдейтим дату выполнения услуги, а так же связь с родительским событием
					$Evn_setDT = "null";
					$data['EvnUslugaPar_pid'] = null;

					//костыль для изменений по задаче #160128 - начало
					$res = $this->common->GET('EvnMediaData/byEvn', [
						'Evn_id' => $EvnUslugaPar_id
					], 'single');
					if (!$this->isSuccessful($res)) {
						return $res;
					}

					$resp_eup[0]['EvnMediaData_id'] = (!empty($res) && !empty($res['EvnMediaData_id'])) ?
						$res['EvnMediaData_id'] : null;

					//костыль для изменений по задаче #160128 - конец
					if (!empty($resp_eup[0]['UslugaTest_id']) || !empty($resp_eup[0]['EvnMediaData_id'])) {
						// Если есть хотя бы один одобренный тест или один прикреплённый к исследованию файл
						$Evn_setDT = "dbo.tzGetDate()";

						$dt = [
							'EvnDirection_id' => $resp_eup[0]['EvnDirection_id'],
							'EvnPrescr_id' => $resp_eup[0]['EvnPrescr_id'],
							'UslugaComplex_id' => $resp_eup[0]['UslugaComplex_id'],
							'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
							'EvnUslugaPar_setDT' => 'curdate'
						];

						// оперделяем Evn_pid
						$uslugaParams = $this->common->GET('EvnPrescr/defineUslugaParams', $dt, 'single');
						if (!$this->isSuccessful($uslugaParams)) {
							$err = $uslugaParams['Error_Msg'];
							throw new \Exception($err, 400);
						}

						$data['EvnUslugaPar_pid'] = $uslugaParams['EvnUslugaPar_pid'];
					} else {
						$uslugaParams = array(
							'EvnUslugaPar_pid' => $resp_eup[0]['Evn_pid'],
							'needRecalcKSGKPGKOEF' => ($resp_eup[0]['EvnClass_SysNick'] == 'EvnSection') ? true : false
						);
					}

					// определяем открытое рабочее место врача, без врача услугу нельзя выполнить (refs #83806)
					if (!empty($data['session']['medpersonal_id'])) {
						$params = [
							'MedPersonal_id' => $data['session']['medpersonal_id']
						];

						if (!empty($data['session']['CurLpuSection_id'])) {
							// если задано отделение, фильтруем по отделению
							$params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
						}
						$resp_msf = $this->common->GET('MedStaffFact/msfData', $params, 'single');
						if (!$this->isSuccessful($resp_msf)) {
							return $resp_msf;
						}

						$data['MedStaffFact_id'] = null;
						if (!empty($resp_msf[0]['MedStaffFact_id'])) {
							$data['MedStaffFact_id'] = $resp_msf[0]['MedStaffFact_id'];
						}
						$data['LpuSection_uid'] = null;
						if (!empty($data['session']['CurLpuSection_id'])) {
							$data['LpuSection_uid'] = $data['session']['CurLpuSection_id'];
						} else if (count($resp_msf) > 0 && !empty($resp_msf[0]['LpuSection_id'])) {
							$data['LpuSection_uid'] = $resp_msf[0]['LpuSection_id'];
						}

						$this->saveEvnUslugaDone(array(
							'EvnLabRequest_id' => $resp_eup[0]['EvnLabRequest_id'],
							'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
							'Evn_setDT' => $Evn_setDT,
							'EvnUslugaPar_id' => $EvnUslugaPar_id,
							'Lpu_id' => $data['session']['lpu_id'],
							'LpuSection_uid' => $data['LpuSection_uid'],
							'MedPersonal_id' => $data['session']['medpersonal_id'],
							'MedStaffFact_id' => $data['MedStaffFact_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($uslugaParams['needRecalcKSGKPGKOEF'])) {
							$res = $this->common->POST('EvnSection/recalcKSGKPGKOEF', [
								'EvnSection_id' => $uslugaParams['EvnUslugaPar_pid']
							], 'single');
							if (!$this->isSuccessful($res)) {
								throw new Exception($res['Error_Msg'], 400);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Назначение теста
	 */
	function prescrTest($data) {
		if (!empty($data['tests'])) {
			// 1. меняем состав в заявке
			$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
			foreach ($data['tests'] as $test) {
				$this->EvnLabRequest_model->saveEvnLabRequestUslugaComplex(array(
					'EvnLabRequest_id' => $data['EvnLabRequest_id'],
					'EvnLabSample_id' => $data['EvnLabSample_id'],
					'EvnUslugaPar_id' => $test['UslugaTest_pid'],
					'UslugaComplex_id' => $test['UslugaComplex_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			// кэшируем количество тестов
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			// 2. если проба взята, то меняем тесты в самой пробе
			$query = "
				select
					els.RefSample_id as \"RefSample_id\",
					elr.EvnDirection_id as \"EvnDirection_id\",
					elr.MedService_id as \"MedService_id\",
					elr.Person_id as \"Person_id\",
					to_char(els.EvnLabSample_setDT, 'yyyy-mm-dd') as \"EvnLabSample_setDT\",
					elr.PersonEvn_id as \"PersonEvn_id\",
					elr.Server_id as \"Server_id\",
					ms.Lpu_id as \"Lpu_id\",
					els.Analyzer_id as \"Analyzer_id\",
					ms.LpuSection_id as \"LpuSection_id\",
					elr.PayType_id as \"PayType_id\",
					elr.Mes_id as \"Mes_id\",
					elr.Diag_id as \"Diag_id\"
				from
					v_EvnLabSample els
					inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					inner join v_MedService ms on ms.MedService_id = elr.MedService_id
				where
					els.EvnLabSample_id = :EvnLabSample_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnLabSample_setDT'])) {
					$lrdata = array(
						'EvnDirection_id' => $resp[0]['EvnDirection_id'],
						'MedService_id' => $resp[0]['MedService_id'],
						'Person_id' => $resp[0]['Person_id'],
						'PersonEvn_id' => $resp[0]['PersonEvn_id'],
						'Server_id' => $resp[0]['Server_id'],
						'Lpu_id' => $resp[0]['Lpu_id'],
						'LpuSection_id' => $resp[0]['LpuSection_id'],
						'PayType_id' => $resp[0]['PayType_id'],
						'MedPersonal_id' => $data['session']['medpersonal_id'],
						'Mes_id' => $resp[0]['Mes_id'],
						'Diag_id' => $resp[0]['Diag_id'],
					);
					$data['Analyzer_id']=$resp[0]['Analyzer_id'];
					$data['EvnLabSample_setDT'] = $resp[0]['EvnLabSample_setDT'];
					// 2. сохраняем нужные тесты
					$data['ingorePrescr'] = true;
					$this->saveLabSampleTests($data, $lrdata, $data['tests']);
				}
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Отмена исследования
	 */
	function cancelResearch($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$query = "
			select
				EvnUsluga_id as \"EvnUslugaPar_id\"
			from
				v_EvnUsluga
			where
				EvnDirection_id = :EvnDirection_id
				and UslugaComplex_id = :UslugaComplex_id
		";

		$resp = $this->queryResult($query, $queryParams);
		foreach($resp as $respone) {
			// удаляем записи из EvnLabRequestUslugaComplex
			$resp_elruc = $this->queryResult("
				select
					EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
				from
					v_EvnLabRequestUslugaComplex
				where
					EvnUslugaPar_id = :EvnUslugaPar_id
			", array(
				'EvnUslugaPar_id' => $respone['EvnUslugaPar_id']
			));
			foreach($resp_elruc as $one_elruc) {
				$query = "
                    select
                        error_code as \"Error_Code\",
                        error_message as \"Error_Msg\"
                    from p_evnlabrequestuslugacomplex_del(
                        evnlabrequestuslugacomplex_id := :EvnLabRequestUslugaComplex_id
                    )
				";

				$result = $this->db->query($query, array(
					'EvnLabRequestUslugaComplex_id' => $one_elruc['EvnLabRequestUslugaComplex_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			// удаляем исследование
			$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnuslugapar_del(
				evnuslugapar_id := :EvnUslugaPar_id,
				pmUser_id := :pmUser_id
			)
			";

			$result = $this->db->query($query, array(
				'EvnUslugaPar_id' => $respone['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
			select
				EvnLabRequest_id as \"EvnLabRequest_id\"
			from
				v_EvnLabRequest
			where
				EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (!empty($data['EvnLabRequest_id'])) {
			$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
			// кэшируем количество тестов
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnDirection_id' => $data['EvnDirection_id']
			));

			// кэшируем названия услуг
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnDirection_id' => $data['EvnDirection_id']
			));
		}
	}

	/**
	 * Возвращает id лунки в которую помещен тест
	 * @param $UslugaTest_id
	 * @return bool|float|int|string
	 */
	function testInHole ($UslugaTest_id) {
		$params = [];
		$params['UslugaTest_id'] = $UslugaTest_id;
		$query = "
			select
				Hole_id as \"Hole_id\"
			from v_Hole H
				inner join v_UslugaTest UT on UT.UslugaTest_id = H.UslugaTest_id
			where UT.UslugaTest_id = :UslugaTest_id
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Отмена теста
	 */
	function cancelTest($data) {
		if (!empty($data['tests'])) {

			if (is_string($data['tests'])) {
				$data['tests'] = json_decode($data['tests'], true);
			}

			// меняем состав в заявке
			foreach ($data['tests'] as $test) {
				$data['EvnLabRequestUslugaComplex_id'] = $this->getFirstResultFromQuery("
						select
							EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
						from
							v_EvnLabRequestUslugaComplex elruc
						where
							elruc.EvnLabRequest_id = :EvnLabRequest_id
							and elruc.EvnLabSample_id = :EvnLabSample_id
							and elruc.EvnUslugaPar_id = :EvnUslugaPar_id
							and elruc.UslugaComplex_id = :UslugaComplex_id
					",
					array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'EvnLabSample_id' => $data['EvnLabSample_id'],
						'EvnUslugaPar_id' => $test['UslugaTest_pid'],
						'UslugaComplex_id' => $test['UslugaComplex_id']
					)
				);
				if (!empty($data['EvnLabRequestUslugaComplex_id'])) {
					$query = "
                        select
                        	error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
                        from p_evnlabrequestuslugacomplex_del(
							evnlabrequestuslugacomplex_id := :EvnLabRequestUslugaComplex_id
                        )
					";

					$result = $this->db->query($query, $data);
				}
			}

			// 1. если проба взята, то меняем тесты в самой пробе
			foreach($data['tests'] as $test) {
				$data['UslugaTest_id'] = $this->getFirstResultFromQuery("
						select
							UslugaTest_id as \"UslugaTest_id\"
						from
							v_UslugaTest
						where
							EvnLabSample_id = :EvnLabSample_id
							and UslugaTest_pid = :UslugaTest_pid
							and UslugaComplex_id = :UslugaComplex_id
					",
					array(
						'EvnLabSample_id' => $data['EvnLabSample_id'],
						'UslugaTest_pid' => $test['UslugaTest_pid'],
						'UslugaComplex_id' => $test['UslugaComplex_id']
					)
				);
				if (!empty($data['UslugaTest_id'])) {
					// проверить статус (можно удалять только в статусе "Новый")
					if (!$this->checkTestCanBeCanceled($data)) {
						return array('Error_Msg' => 'Нельзя удалить тест, находящийся не в статусе "Новый"');
					}

					// тест не должен быть в лунке планшета
					if($this->testInHole($data['UslugaTest_id'])) {
						return $this->createError('', 'Тест находится в лунке');
					}

					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_uslugatest_del(
							uslugatest_id := :UslugaTest_id,
							pmUser_id := :pmUser_id
						)
					";

					$result = $this->db->query($query, $data);
				}
			}

			$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
			// кэшируем количество тестов
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			// кэшируем статус проб в заявке
			$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			// если по исследованию не осталось ни одного сохранённого/назначенного теста, то удаляем его.
			// получаем список затронутых исследований
			$EvnUslugaParPList = array();
			foreach($data['tests'] as $test) {
				if (!in_array($test['UslugaTest_pid'], $EvnUslugaParPList)) {
					$EvnUslugaParPList[] = $test['UslugaTest_pid'];
				}
			}

			foreach($EvnUslugaParPList as $EvnUslugaPar_pid) {
				// проверяем есть ли ещё тесты у данного исследования
				$query = "
				    select *
				    from (
				    	(select
							eup.EvnUslugaPar_id as \"id\"
						from
							v_EvnUslugaPar eup
						where
							eup.EvnUslugaPar_pid = :EvnUslugaPar_pid
                    	limit 1)

                    	union
						(select
							EvnLabRequestUslugaComplex_id as \"id\"
						from
							v_EvnLabRequestUslugaComplex elruc
						where
							elruc.EvnUslugaPar_id = :EvnUslugaPar_pid
                    	limit 1)
				    ) t
				";
				$resp_research = $this->queryResult($query, array(
					'EvnUslugaPar_pid' => $EvnUslugaPar_pid
				));

				// если тестов нет то удаляем
				if (empty($resp_research[0]['id'])) {
					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_evnuslugapar_del(
							EvnUslugaPar_id :=:EvnUslugaPar_id,
							pmUser_id :=:pmUser_id
					)
					";

					$result = $this->db->query($query, array(
						'EvnUslugaPar_id' => $EvnUslugaPar_pid,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			// кэшируем названия услуг
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Получение списка результатов пробы
	 */
	function getLabSampleResultGrid($data) {
		if (!empty($data['EvnLabSample_id'])) {
			if (empty($data['EvnDirection_id'])) {
				$data['EvnDirection_id'] = $this->getFirstResultFromQuery("
					select
						elr.EvnDirection_id as \"EvnDirection_id\"
					from
						v_EvnLabSample els
						inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					where
						els.EvnLabSample_id = :EvnLabSample_id
					", $data);
				if (empty($data['EvnDirection_id'])) {
					$data['EvnDirection_id'] = null;
				}
			}
		}

		$beforeQuery = "";
		$filter = "";
		$inPrescr = "1=1";
		if (empty($data['ingorePrescr'])) {
			// проверяем сохранен ли для исследования состав
			$EvnLabRequestUslugaComplex_id = $this->getFirstResultFromQuery("
				select
					elruc.EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
				from
					v_EvnLabRequestUslugaComplex elruc
					inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
				where
					elr.EvnDirection_id = :EvnDirection_id
				limit 1
			", $data);
			if (!empty($EvnLabRequestUslugaComplex_id)) {
				$beforeQuery = "
					,elruc as (
						select
							elruc.UslugaComplex_id,
							elruc.EvnLabSample_id,
							elruc.EvnUslugaPar_id
						from
							v_EvnLabRequestUslugaComplex elruc
							inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
						where
							elr.EvnDirection_id = :EvnDirection_id
					)
				";
				$inPrescr = "
					uc.UslugaComplex_id IN (
						select
							elruc.UslugaComplex_id
						from
							elruc
						where
							COALESCE(elruc.EvnLabSample_id, els.EvnLabSample_id) = els.EvnLabSample_id
							and COALESCE(elruc.EvnUslugaPar_id, eup.EvnUslugaPar_id) = eup.EvnUslugaPar_id
					)
				";
			}/* else {
				// достаём состав заказанный (из назначения)
				$query = "
					select
						educ.UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_EvnDirectionUslugaComplex educ
					where
						educ.EvnDirection_id = :EvnDirection_id
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					$OrderUslugaComplex_ids = array();
					foreach($resp as $respone) {
						$OrderUslugaComplex_ids[] = $respone['UslugaComplex_id'];
					}
					if (!empty($OrderUslugaComplex_ids)) {
						$inPrescr = "
							uc.UslugaComplex_id IN (
								" . implode(',', $OrderUslugaComplex_ids) . "
							)
						";
					}
				}
			}*/
		}

		if (!empty($data['UslugaComplex_ids'])) {
			$filter .= "
				and uc.UslugaComplex_id IN (
					".implode(',', $data['UslugaComplex_ids'])."
				)
			";
		}

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id']
		);

		if (!empty($data['needtests'])) {
			$uci = 0;
			$ucfilter = "";
			foreach($data['needtests'] as $test) {
				$uci++;
				if (!empty($ucfilter)) {
					$ucfilter .= " or ";
				}
				$ucfilter .= "(uc.UslugaComplex_id = :UslugaComplex{$uci}_id and eup.EvnUslugaPar_id = :EvnUslugaPar{$uci}_pid)";
				$queryParams["UslugaComplex{$uci}_id"] = $test['UslugaComplex_id'];
				$queryParams["EvnUslugaPar{$uci}_pid"] = $test['UslugaTest_pid'];
			}

			if (!empty($ucfilter)) {
				$filter .= " and ({$ucfilter})";
			}
		}

		if (!empty($data['EvnUslugaPar_pid'])) {
			$filter .= "
				and eup.EvnUslugaPar_id = :EvnUslugaPar_pid
			";
			$queryParams["EvnUslugaPar_pid"] = $data['EvnUslugaPar_pid'];
		}

		// @task https://redmine.swan.perm.ru/issues/99240
		// Оптимизация первого select: получаем список идентификаторов услуг отдельным запросом, указываем идентификаторы в in ()
		// Оптимизация второго select: получаем список идентификаторов услуг отдельным запросом, убираем cross apply, указываем идентификаторы в in ()
		// Если список идентификаторов пустой, то второй select можно в запрос не добавлять
		$evnUslugaParBySampleList = array(
			1 => array(0),
			2 => array()
		);

		$firstJoin = [];
		$firstWhere = [];
		$firstWhere[] = "UT.EvnLabSample_id = :EvnLabSample_id";

		if(!empty($data['formMode']) && $data['formMode'] == 'ifa') {
			$firstJoin[] = "left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid";
			$firstJoin[] = "left join v_UslugaComplex uc ON ut.UslugaComplex_id = uc.UslugaComplex_id";
			$firstJoin[] = "left join v_UslugaComplex ucp ON eupp.UslugaComplex_id = ucp.UslugaComplex_id";
			$firstJoin[] = "left join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id";
			$firstJoin[] = "left join lis.v_AnalyzerTest AnT on AnT.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id";
			$firstJoin[] = "inner join v_MethodsIFAAnalyzerTest MIAT on MIAT.AnalyzerTest_id = AnT.AnalyzerTest_id";
			$firstJoin[] = "inner join v_MethodsIFA MI on MI.MethodsIFA_id = MIAT.MethodsIFA_id";

			if(!empty($data['AnalyzerTest_id'])) {
				$firstWhere[] = 'AnT.AnalyzerTest_id = :AnalyzerTest_id';
				$queryParams['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
			}


			if(!empty($data['MethodsIFA_id'])) {
				$firstWhere[] = 'MI.MethodsIFA_id = :MethodsIFA_id';
				$queryParams['MethodsIFA_id'] = $data['MethodsIFA_id'];
			}
		}

		$firstJoin = implode( ' ', $firstJoin );
		$firstWhere = implode( ' and ', $firstWhere );
		// 1-й select
		$resp = $this->queryResult("
			select
				ut.UslugaTest_id as \"UslugaTest_id\"
			from
				v_UslugaTest ut
					left join v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
					left join v_BactMicroProbeAntibiotic bmpa on bmpa.UslugaTest_id = ut.UslugaTest_id
					{$firstJoin}
			where
				{$firstWhere}
				and bmp.BactMicroProbe_id is null
				and bmpa.BactMicroProbeAntibiotic_id is null
		", $queryParams);

		if ( is_array($resp) && count($resp) > 0 ) {
			foreach ( $resp as $row ) {
				if ( !in_array($row['UslugaTest_id'], $evnUslugaParBySampleList[1]) ) {
					$evnUslugaParBySampleList[1][] = $row['UslugaTest_id'];
				}
			}
		}

		// 2-й select
		$resp = $this->queryResult("
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnLabRequestUslugaComplex elruc
			where
				elruc.EvnLabSample_id = :EvnLabSample_id

			union all

			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnUslugaPar eup
			where
				eup.EvnLabSample_id = :EvnLabSample_id
				and not exists(
					select elruc.EvnUslugaPar_id
					from v_EvnLabRequestUslugaComplex elruc
					where elruc.EvnLabSample_id = :EvnLabSample_id
					limit 1
				) -- если вдруг состава нет, отображаем исследования напрямую связанные с данной пробой (старая логика)
		", $queryParams);

		if ( is_array($resp) && count($resp) > 0 ) {
			foreach ( $resp as $row ) {
				if ( !in_array($row['EvnUslugaPar_id'], $evnUslugaParBySampleList[2]) ) {
					$evnUslugaParBySampleList[2][] = $row['EvnUslugaPar_id'];
				}
			}
		}

		// 1 запрос - все услуги сохраненные в пробе, 2 запрос - неназначенные услуги из настроек
		$query = "

			with myvars as (
            	select dbo.tzgetdate() as curdate
            )
			{$beforeQuery}

			Select 
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				rv.RefValues_Name || COALESCE(' (' || analyzer.Analyzer_Name || ')','') as \"RefValues_Name\",
				COALESCE(ut.UslugaTest_ResultLower,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpper,'') as \"UslugaTest_ResultNorm\",
				COALESCE(ut.UslugaTest_ResultLowerCrit,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpperCrit,'') as \"UslugaTest_ResultCrit\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				case when {$inPrescr} then 2 else 1 end as \"inPrescr\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 'Назначен'
					else 'Не назначен'
				end as \"UslugaTest_Status\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 0
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 0
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 0
					else 1
				end as \"SortStatus\",
				COALESCE(analyzertest.AnalyzerTest_SortCode, analyzertest2.AnalyzerTest_SortCode, 999999999) as \"AnalyzerTest_SortCode\",
				to_char (ut.UslugaTest_setDT, 'HH24:MI DD.MM.YYYY') as \"UslugaTest_setDT\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				coalesce(analyzertest.AnalyzerTest_SysNick, analyzertest.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uc.UslugaComplex_ACode as \"UslugaComplex_ACode\",
				els.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				dct.DefectCauseType_Name as \"DefectCauseType_Name\",
				COALESCE(analyzertest.UslugaComplex_ParentName, ucp.UslugaComplex_Name) as \"ResearchName\",
				eup.EvnUslugaPar_id as \"UslugaTest_pid\",
				eup.UslugaComplex_id as \"UslugaComplexTarget_id\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_pComment\",
				a.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\",
				a.Analyzer_IsAutoGood as \"Analyzer_IsAutoGood\",
				els.Analyzer_id as \"Analyzer_id\"
			From
				v_EvnLabSample els
				inner join v_UslugaTest ut on ut.UslugaTest_id in (" . implode(',', $evnUslugaParBySampleList[1]) . ")
				inner join v_EvnUslugaPar eup on ut.UslugaTest_pid = eup.EvnUslugaPar_id and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaComplex uc on ut.UslugaComplex_id = uc.UslugaComplex_id
				left join v_RefValues rv on rv.RefValues_id = ut.RefValues_id
				left join lis.v_DefectCauseType dct on dct.DefectCauseType_id = els.DefectCauseType_id
				left join lis.v_Analyzer a on a.Analyzer_id = els.Analyzer_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = COALESCE(at_child.AnalyzerTest_pid, at.AnalyzerTest_id) -- родительское исследование, если есть
						inner join v_UslugaComplexMedService ucms_at on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = COALESCE(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id) -- родительская услуга, если есть
						inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						ucms_at.UslugaComplex_id = ut.UslugaComplex_id
						and ucms_parent.UslugaComplex_id = eup.UslugaComplex_id
						and ucms_at.MedService_id = els.MedService_id
						and (at.analyzertest_isnotactive is null or at.analyzertest_isnotactive = 1)
                        and (a.analyzer_isnotactive is null or a.analyzer_isnotactive = 1)
						and (at.AnalyzerTest_endDT >= (select curdate from myvars) or at.AnalyzerTest_endDT is null)
						and (at_child.AnalyzerTest_endDT >= (select curdate from myvars) or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= (select curdate from myvars) or uctest.UslugaComplex_endDT is null)
                    limit 1
				) analyzertest on true
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						ucms_parent.UslugaComplex_Name as UslugaComplex_ParentName
					from
						lis.v_AnalyzerTest at_child
						inner join lis.v_Analyzer a  on a.Analyzer_id = at_child.Analyzer_id
						inner join v_UslugaComplexMedService ucms_at  on at_child.UslugaComplexMedService_id = ucms_at.UslugaComplexMedService_id
						left join v_UslugaComplexMedService ucms_parent  on ucms_parent.UslugaComplexMedService_id = coalesce(ucms_at.UslugaComplexMedService_pid, ucms_at.UslugaComplexMedService_id)
						left join v_UslugaComplex uctest  on at_child.UslugaComplex_id = uctest.UslugaComplex_id
					where
						at_child.Analyzer_id = els.Analyzer_id
						and (at_child.AnalyzerTest_endDT > (select curdate from myvars) or at_child.AnalyzerTest_endDT is null)
						and at_child.UslugaComplex_id = ut.UslugaComplex_id
					limit 1
				) analyzertest2 on true
				left join lateral(
					select
						Analyzer_Name
					from
						lis.v_Analyzer a
						inner join lis.v_AnalyzerTest at on at.Analyzer_id = a.Analyzer_id
						inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					where
						atrv.RefValues_id = ut.RefValues_id
                    limit 1
				) analyzer on true
			where
				els.EvnLabSample_id = :EvnLabSample_id
				{$filter}

			" . (count($evnUslugaParBySampleList[2]) > 0 ? "
			union all

			Select distinct
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				els.EvnLabSample_id as \"EvnLabSample_id\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.RefValues_id as \"RefValues_id\",
				ut.Unit_id as \"Unit_id\",
				rv.RefValues_Name || COALESCE(' (' || analyzer.Analyzer_Name || ')','') as \"RefValues_Name\",
				COALESCE(ut.UslugaTest_ResultLower,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpper,'') as \"UslugaTest_ResultNorm\",
				COALESCE(ut.UslugaTest_ResultLowerCrit,'') || ' - ' || COALESCE(ut.UslugaTest_ResultUpperCrit,'') as \"UslugaTest_ResultCrit\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				ut.UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				ut.UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				ut.UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				case when {$inPrescr} then 2 else 1 end as \"inPrescr\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 'Назначен'
					else 'Не назначен'
				end as \"UslugaTest_Status\",
				case
					when ut.UslugaTest_ResultApproved = 2 then 0
					when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 0
					when (ut.UslugaTest_id is not null or {$inPrescr}) then 0
					else 1
				end as \"SortStatus\",
				COALESCE(analyzertest.AnalyzerTest_SortCode, 999999999) as \"AnalyzerTest_SortCode\",
				to_char (ut.UslugaTest_setDT, 'HH24:MI DD.MM.YYYY') as \"UslugaTest_setDT\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				coalesce(analyzertest.AnalyzerTest_SysNick, analyzertest.UslugaComplex_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uc.UslugaComplex_ACode as \"UslugaComplex_ACode\",
				'' as \"EvnLabSample_Comment\",
				'' as \"DefectCauseType_Name\",
				COALESCE(ucms_usluga_parent.UslugaComplex_Name, ucp.UslugaComplex_Name) as \"ResearchName\",
				eup.EvnUslugaPar_id as \"UslugaTest_pid\",
				eup.UslugaComplex_id as \"UslugaComplexTarget_id\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_pComment\",
				analyzertest.Analyzer_IsAutoOk as \"Analyzer_IsAutoOk\",
				analyzertest.Analyzer_IsAutoGood as \"Analyzer_IsAutoGood\",
				els.Analyzer_id
			From
				v_EvnLabSample els
				inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id in (" . implode(',', $evnUslugaParBySampleList[2]) . ") and eup.EvnDirection_id is not null
				left join v_UslugaComplex ucp on ucp.UslugaComplex_id = eup.UslugaComplex_id
				left join v_UslugaComplexMedService ucms_usluga_parent on eup.UslugaComplex_id = ucms_usluga_parent.UslugaComplex_id and ucms_usluga_parent.MedService_id = els.MedService_id and ucms_usluga_parent.UslugaComplexMedService_pid is null -- исследование
				left join v_UslugaComplexMedService ucms_usluga on ucms_usluga.UslugaComplexMedService_pid = ucms_usluga_parent.UslugaComplexMedService_id
				left join v_UslugaComplex uc on COALESCE(ucms_usluga.UslugaComplex_id, ucp.UslugaComplex_id, ucms_usluga_parent.UslugaComplex_id) = uc.UslugaComplex_id
				left join v_UslugaTest ut on ut.UslugaTest_pid = eup.EvnUslugaPar_id and ut.UslugaComplex_id = uc.UslugaComplex_id and ut.EvnLabSample_id = els.EvnLabSample_id -- Результат по пробе
				left join v_RefValues rv on rv.RefValues_id = ut.RefValues_id
				left join lateral(
					select
						at_child.AnalyzerTest_id,
						at_child.AnalyzerTest_SysNick,
						at_child.AnalyzerTest_SortCode,
						uctest.UslugaComplex_Name,
						a.Analyzer_IsAutoOk,
						a.Analyzer_IsAutoGood
					from
						lis.v_AnalyzerTest at_child
						left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a on a.Analyzer_id = COALESCE(at.Analyzer_id, at_child.Analyzer_id)
						left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					where
						at_child.UslugaComplexMedService_id = COALESCE(ucms_usluga.UslugaComplexMedService_id, ucms_usluga_parent.UslugaComplexMedService_id)
						and COALESCE(at.UslugaComplexMedService_id, 0) = COALESCE(ucms_usluga.UslugaComplexMedService_pid, 0)
						and COALESCE(at_child.AnalyzerTest_IsNotActive, 1) = 1
						and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
						and COALESCE(a.Analyzer_IsNotActive, 1) = 1
						and (at_child.AnalyzerTest_endDT >= (select curdate from myvars) or at_child.AnalyzerTest_endDT is null)
						and (uctest.UslugaComplex_endDT >= (select curdate from myvars) or uctest.UslugaComplex_endDT is null)
                    limit 1
				) analyzertest on true
				left join lateral(
					select
						Analyzer_Name
					from
						lis.v_Analyzer a
						inner join lis.v_AnalyzerTest at on at.Analyzer_id = a.Analyzer_id
						inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
					where
						atrv.RefValues_id = ut.RefValues_id
					limit 1
				) analyzer on true
			where
				els.EvnLabSample_id = :EvnLabSample_id and ut.UslugaTest_id is null
				and (
					analyzertest.AnalyzerTest_id is not null
					or exists (
						select
							EvnLabRequestUslugaComplex_id
						from
							v_EvnLabRequestUslugaComplex elruc_child
						where
							elruc_child.EvnUslugaPar_id = eup.EvnUslugaPar_id
							and elruc_child.EvnLabSample_id = els.EvnLabSample_id
							and elruc_child.UslugaComplex_id = ucp.UslugaComplex_id
                        limit 1
					)
				) -- назначен или действует на дату
				{$filter}
			" : "" ) . "

			order by
				\"SortStatus\", \"AnalyzerTest_SortCode\", \"UslugaComplex_Name\"
		";

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data = array())
	{
		if (!isset($this->RecordStatus_Code)){
			//если не указан идентификатор состояния - значит модель используется в "обычном единичном режиме",
			//для родительского метода сохранения проставим RecordStatus_Code = 2 (изменено), чтобы тот нормально отработал
			$this->RecordStatus_Code = 2;
		}
		if (empty($this->EvnLabSample_BarCode)) {
			$this->EvnLabSample_BarCode = $this->EvnLabSample_Num; // штрих-код = номеру пробы
		}
		// Если сохраняем существующую пробу, то проверяем был ли изменён номер пробы, если изменён - то убираем у пробы отправку в ЛИС.
		$EvnLabSampleNumChanged = false;
		if (!empty($this->EvnLabSample_id)) {
			$query = "
				select
					EvnLabSample_id as \"EvnLabSample_id\"
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
					and EvnLabSample_Num != :EvnLabSample_Num
			";

			$result = $this->db->query($query, array(
				'EvnLabSample_id' => $this->EvnLabSample_id,
				'EvnLabSample_Num' => $this->EvnLabSample_Num
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnLabSample_id'])) {
					$EvnLabSampleNumChanged = true;
				}
			}
		}

		if (!empty($this->MedService_id)) {
			// Lpu_id должно быть равно МО лаборатории, иначе в лаборатории не отобразится и может некорректно считаться номер пробы
			$resp_ms = $this->queryResult("
				select
					Lpu_id as \"Lpu_id\"
				from
					v_MedService
				where
					MedService_id = :MedService_id
			", array(
				'MedService_id' => $this->MedService_id
			));
			if (!empty($resp_ms[0]['Lpu_id'])) {
				$this->Lpu_id = $resp_ms[0]['Lpu_id'];
			}
		}

		// проверяем номер и штрих-код на уникальность
		$response_check = $this->checkEvnLabSampleBarCodeUnique(array(
			'Lpu_id' => $this->Lpu_id,
			'EvnLabSample_BarCode' => $this->EvnLabSample_BarCode,
			'EvnLabSample_id' => $this->EvnLabSample_id
		));
		if (!empty($response_check['Error_Msg'])) {
			return $response_check;
		}
		$response_check = $this->checkEvnLabSampleNumUnique(array(
			'Lpu_id' => $this->Lpu_id,
			'EvnLabSample_Num' => $this->EvnLabSample_Num,
			'EvnLabSample_id' => $this->EvnLabSample_id
		));
		if (!empty($response_check['Error_Msg'])) {
			return $response_check;
		}

		$result = parent::save();
		if (!self::save_ok($result)) {
			throw new Exception('При сохранении пробы произошли ошибки:'.$result[0]['Error_Code'].$result[0]['Error_Msg']);
		} else {
			if ($data['session']['region']['nick'] == 'ufa' && $EvnLabSampleNumChanged) {
				// 1. затираем связь с лис если есть
				$Link_id = $this->getFirstResultFromQuery("
					select
						Link_id as \"Link_id\"
					from
						lis.v_Link
					where
						link_object = 'EvnLabSample'
					and object_id = :EvnLabSample_id
					", array(
					$this->EvnLabSample_id
				));
				if (!empty($Link_id)) {
					$this->db->query("
						select
                    		error_code_ as \"Error_Code\",
                    		error_message_ as \"Error_Msg\"
                   		from p_Link_del(
                    		Link_id := :Link_id
                    	)
					", array(
						'Link_id' => $Link_id
					));
				}
				// 2. проверяем анализатор
				if (!empty($this->Analyzer_id)) {
					$Analyzer_id = $this->getFirstResultFromQuery("
						select
							Analyzer_id as \"Analyzer_id\"
						from
							lis.v_Analyzer
						where
							Analyzer_id = :Analyzer_id
							and (pmUser_insID != 1 or Analyzer_Code != '000')
					", array(
						'Analyzer_id' => $this->Analyzer_id
					));

					if (!empty($Analyzer_id)) {
						// 3. отправляем в ЛИС/АсМЛО
						$this->load->helper('Xml');
						// отправляем в АС МЛО
						$this->load->model('AsMlo_model', 'lab_model');
						$creation = $this->lab_model->createRequest2($data, true);
						if (is_array($creation) && !empty($creation['Error_Msg'])) {
							$result[0]['Alert_Msg'] = $creation['Error_Msg'];
						}
					}
				}
			}
			$this->ReCacheLabSampleStatus(array(
				'EvnLabSample_id' => $this->EvnLabSample_id
			));

			$resultReCache = $this->ReCacheLabRequestByLabSample(array(
				'EvnLabSample_id' => $this->EvnLabSample_id,
				'session' => $data['session'],
				'pmUser_id' => $this->pmUser_id
			));

			$result[0]['UslugaExecutionType_id'] = $resultReCache['UslugaExecutionType_id'];
		}
		$result = $result[0];
		$result['success'] = true;
		return $result;
	}

	/**
	 * Функция читает список проб
	 *
	 * @param $data array
	 * @return $result array
	 */
	function loadList($data) {
		$sql = "
			SELECT
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_pid as \"EvnLabSample_pid\",
				EvnLabSample_rid as \"EvnLabSample_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				to_char (EvnLabSample_setDT, 'dd.mm.yyyy') as \"EvnLabSample_setDT\",
				to_char (EvnLabSample_disDT, 'dd.mm.yyyy') as \"EvnLabSample_disDT\",
				to_char (EvnLabSample_didDT, 'dd.mm.yyyy') as \"EvnLabSample_didDT\",
				to_char (EvnLabSample_insDT, 'dd.mm.yyyy') as \"EvnLabSample_insDT\",
				to_char (EvnLabSample_updDT, 'dd.mm.yyyy') as \"EvnLabSample_updDT\",
				EvnLabSample_Index as \"EvnLabSample_Index\",
				EvnLabSample_Count as \"EvnLabSample_Count\",
				Morbus_id as \"Morbus_id\",
				EvnLabSample_IsSigned as \"EvnLabSample_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				to_char (EvnLabSample_signDT, 'dd.mm.yyyy') as \"EvnLabSample_signDT\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\",
				EvnLabSample_Comment as \"EvnLabSample_Comment\",
				RefSample_id as \"RefSample_id\",
				Lpu_did as \"Lpu_did\",
				LpuSection_did as \"LpuSection_did\",
				MedPersonal_did as \"MedPersonal_did\",
				MedPersonal_sdid as \"MedPersonal_sdid\",
				to_char (EvnLabSample_DelivDT, 'dd.mm.yyyy') as \"EvnLabSample_DelivDT\",
				Lpu_aid as \"Lpu_aid\",
				LpuSection_aid as \"LpuSection_aid\",
				MedPersonal_aid as \"MedPersonal_aid\",
				MedPersonal_said as \"MedPersonal_said\",
				to_char (EvnLabSample_StudyDT, 'dd.mm.yyyy') as \"EvnLabSample_StudyDT\",
				LabSampleDefectiveType_id as \"LabSampleDefectiveType_id\",
				Analyzer_id as \"Analyzer_id\",
				pmUser_insID as \"pmUser_id\",
				1 as \"RecordStatus_Code\"
			FROM
				v_EvnLabSample els
			WHERE
				els.EvnLabRequest_id = :EvnLabRequest_id
				";
		$params = array('EvnLabRequest_id' => $data['EvnLabRequest_id']);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return null;
		}
	}

	/**
	 * Функция получает родительскую услугу (заказ) из v_EvnUsluga
	 *
	 * @param $EvnDirection_id
	 * @return null
	 */
	function getEvnUslugaRoot($EvnLabSample_id, $UslugaComplex_id){
		$sql = "
			SELECT
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\"
			FROM
				v_EvnUslugaPar eu
			WHERE
				eu.EvnLabSample_id = :EvnLabSample_id
				and eu.UslugaComplex_id = :UslugaComplex_id
				and eu.EvnDirection_id is not null
		";
		$p = array('EvnLabSample_id' => $EvnLabSample_id, 'UslugaComplex_id' => $UslugaComplex_id);

		$result = $this->db->query($sql, $p);
		if (is_object($result)) {
			$response = $result->result('array');
			if (count($response)>0) {
				return $response[0];
			}
		}

		return null;
	}

	/**
	 * Функция получает родительскую услугу (заказ) из v_EvnUsluga
	 *
	 * @param $EvnDirection_id
	 * @return null
	 */
	function getEvnUslugasRoot($data) {
		$filter = "";
		$queryParams = array();
		if (!empty($data['EvnDirection_id'])) {
			$filter .= " and elr.EvnDirection_id = :EvnDirection_id";
			$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
		} else if (!empty($data['EvnLabSample_id'])) {
			$filter .= " and els.EvnLabSample_id = :EvnLabSample_id";
			$queryParams['EvnLabSample_id'] = $data['EvnLabSample_id'];
		} else {
			return false;
		}

		$query = "
			SELECT distinct
				eu.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eu.EvnPrescr_id as \"EvnPrescr_id\",
				to_char(eu.EvnUslugaPar_setDT, 'yyyy-mm-dd') as \"EvnUslugaPar_setDT\",
				eu.UslugaComplex_id as \"UslugaComplex_id\"
			FROM
				v_EvnLabRequest elr
				inner join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id
				inner join v_EvnUslugaPar eu on eu.EvnDirection_id = elr.EvnDirection_id
			WHERE
				1=1
				and eu.EvnDirection_id is not null
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (count($response)>0) {
				return $response;
			}
		}

		return array();
	}

	/**
	 *
	 * @param type $data
	 */
	function getInfoLabSample($data) {
		$query = "
			select
				els.RefSample_id as \"RefSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				els.MedService_id as \"MedService_id\",
				elr.Person_id as \"Person_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				to_char(els.EvnLabSample_setDT, 'yyyy-mm-dd') as \"EvnLabSample_setDT\",
				elr.Server_id as \"Server_id\",
				els.Analyzer_id as \"Analyzer_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				elr.PayType_id as \"PayType_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = els.MedService_id
			where
				els.EvnLabSample_id = :EvnLabSample_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			return array(
				'RefSample_id' => $resp[0]['RefSample_id'],
				'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
				'EvnDirection_id' => $resp[0]['EvnDirection_id'],
				'MedService_id' => $resp[0]['MedService_id'],
				'Person_id' => $resp[0]['Person_id'],
				'Analyzer_id' => $resp[0]['Analyzer_id'],
				'PersonEvn_id' => $resp[0]['PersonEvn_id'],
				'Server_id' => $resp[0]['Server_id'],
				'Lpu_id' => $resp[0]['Lpu_id'],
				'LpuSection_id' => $resp[0]['LpuSection_id'],
				'PayType_id' => $resp[0]['PayType_id'],
				'EvnLabSample_setDT' => $resp[0]['EvnLabSample_setDT'],
				'MedPersonal_id' => $data['session']['medpersonal_id'],
			);
		} else {
			return false;
		}
		return false;

	}

	/**
	 * Выбор анализатора для пробы
	 */
	function saveLabSamplesAnalyzer($data) {
		if (!empty($data['EvnLabSamples'])) {
			$EvnLabSamples = json_decode($data['EvnLabSamples'], true);
			if (count($EvnLabSamples) > 0) {
				// получаем только пробы по которым анализатор отличается от устанавливаемого, иначе будем делать ненужную работу.
				$query = "
					select
						EvnLabSample_id as \"EvnLabSample_id\"
					from
						v_EvnLabSample
					where
						EvnLabSample_id in ('".implode("','", $EvnLabSamples)."')
						and COALESCE(Analyzer_id,0) != COALESCE(cast(:Analyzer_id as bigint), 0)
				";
				$result = $this->queryResult($query, array(
					'Analyzer_id' => $data['Analyzer_id']
				));
				if (is_array($result)) {
					foreach($result as $EvnLabSample) {
						$data['EvnLabSample_id'] = $EvnLabSample['EvnLabSample_id'];
						$this->saveLabSampleAnalyzer($data);
					}
				}
			}
		}
		return array('Error_Msg' => '');
	}

	/**
	 * Получение необходимых тестов для смены в них реф. значений после смены анализатора
	 */
	function getLabSampleTestsForChangeAnalyzerValues($data) {
		$query = "
			select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				eupp.UslugaComplex_id as \"UslugaComplexTarget_id\"
			from
				v_UslugaTest ut
				left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
				left join lis.v_AnalyzerTestRefValues atrv on atrv.RefValues_id = ut.RefValues_id
				left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = atrv.AnalyzerTest_id
			where
				ut.EvnLabSample_id = :EvnLabSample_id
				and ut.EvnDirection_id is null
		";
		return $this->queryResult($query, array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'Analyzer_id' => $data['Analyzer_id']
		));
	}

	/**
	 * Выбор анализатора для пробы
	 */
	function saveLabSampleAnalyzer($data) {
		$query = "
			UPDATE
				EvnLabSample
			SET
				Analyzer_id = :Analyzer_id
			WHERE
				Evn_id = :EvnLabSample_id;
		";
		$response = $this->db->query($query, $data);
		if ($response) {
			collectEditedData('upd', 'Analyzer', $data['Analyzer_id']);
		}

		$lrdata = $this->getInfoLabSample($data);
		$data['RefSample_id'] = $lrdata['RefSample_id'];
		$data['EvnDirection_id'] = $lrdata['EvnDirection_id'];
		$tests = $this->getLabSampleTestsForChangeAnalyzerValues($data);

		$data['EvnPrescr_id'] = null;
		$data['PersonData'] = null;
		if (count($tests) > 0 && !empty($data['Analyzer_id'])) {
			// получаем необходимые данные для получения реф.значений
			$res = $this->common->GET('EvnPrescr/PrescrByDirection', [
				'EvnDirection_id' => $lrdata['EvnDirection_id']
			], 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			$data['EvnPrescr_id'] = $res['EvnPrescr_id'];

			if (!$data['EvnPrescr_id'] > 0) {
				$data['EvnPrescr_id'] = null;
			}

			if (!empty($lrdata['EvnLabSample_setDT']))
				$lrdata['EvnLabSample_setDT'] = explode(' ', $lrdata['EvnLabSample_setDT'])[0];

			$person = $this->common->GET('Person/PersonDataForRefValues', [
				'Person_id' => $lrdata['Person_id'],
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'EvnLabSample_setDT' => $lrdata['EvnLabSample_setDT']
			], 'single');
			if (!$this->isSuccessful($person)) {
				return $person;
			}
			$data['PersonData'] = $person;
		}

		$UslugaComplex_ids = array();
		foreach($tests as $test) {
			$UslugaComplex_ids[$test['UslugaComplexTarget_id']][] = $test['UslugaComplex_id'];
		}

		foreach(array_keys($UslugaComplex_ids) as $UslugaComplexTarget_id) {
			$refvalues = array();
			$resp_refvalues = $this->loadRefValues(array(
				'EvnLabSample_setDT' => $lrdata['EvnLabSample_setDT'],
				'MedService_id' => $lrdata['MedService_id'],
				'EvnDirection_id' => $lrdata['EvnDirection_id'],
				'Person_id' => $lrdata['Person_id'],
				'UslugaComplexTarget_id' => $UslugaComplexTarget_id,
				'UslugaComplex_ids' => json_encode($UslugaComplex_ids[$UslugaComplexTarget_id]),
				'Analyzer_id' => $data['Analyzer_id'],
				'EvnPrescr_id' => $data['EvnPrescr_id'],
				'PersonData' => $data['PersonData']
			));

			foreach ($resp_refvalues as $refvalue) {
				$refvalues[$refvalue['UslugaComplex_id']] = $refvalue;
			}

			foreach ($tests as $test) {
				if ($test['UslugaTest_ResultValue'] != null && $test['UslugaTest_ResultValue'] != '') {
					continue;
				}
				if ($test['UslugaComplexTarget_id'] == $UslugaComplexTarget_id) {
					$saveRefValues = array();
					$saveRefValues['RefValues_id'] = null;
					$saveRefValues['Unit_id'] = null;
					$saveRefValues['UslugaTest_ResultQualitativeNorms'] = null;
					$saveRefValues['UslugaTest_ResultLower'] = null;
					$saveRefValues['UslugaTest_ResultUpper'] = null;
					$saveRefValues['UslugaTest_ResultLowerCrit'] = null;
					$saveRefValues['UslugaTest_ResultUpperCrit'] = null;
					$saveRefValues['UslugaTest_ResultNorm'] = null;
					$saveRefValues['UslugaTest_ResultCrit'] = null;
					$saveRefValues['UslugaTest_ResultUnit'] = null;
					$saveRefValues['UslugaTest_Comment'] = null;
					if (!empty($data['Analyzer_id'])) {
						if (!empty($refvalues[$test['UslugaComplex_id']]['AnalyzerTestRefValues_id'])) {
							$saveRefValues['RefValues_id'] = $refvalues[$test['UslugaComplex_id']]['RefValues_id'];
							$saveRefValues['Unit_id'] = $refvalues[$test['UslugaComplex_id']]['Unit_id'];
							$saveRefValues['UslugaTest_ResultQualitativeNorms'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultQualitativeNorms'];
							$saveRefValues['UslugaTest_ResultLower'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLower'];
							$saveRefValues['UslugaTest_ResultUpper'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUpper'];
							$saveRefValues['UslugaTest_ResultNorm'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLower'];
							$saveRefValues['UslugaTest_ResultCrit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLowerCrit'];
							$saveRefValues['UslugaTest_ResultLowerCrit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultLowerCrit'];
							$saveRefValues['UslugaTest_ResultUpperCrit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUpperCrit'];
							$saveRefValues['UslugaTest_ResultUnit'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_ResultUnit'];
							$saveRefValues['UslugaTest_Comment'] = $refvalues[$test['UslugaComplex_id']]['UslugaTest_Comment'];
						} else {
							// получаем базовую единицу измерения
							$this->load->model('LisSpr_model');
							$resp_unit = $this->LisSpr_model->loadTestUnitList(array(
								'UslugaComplexTest_id' => $test['UslugaComplex_id'],
								'UslugaComplexTarget_id' => $test['UslugaComplexTarget_id'],
								'UnitOld_id' => null,
								'MedService_id' => $lrdata['MedService_id'],
								'Analyzer_id' => $data['Analyzer_id'],
								'QuantitativeTestUnit_IsBase' => 2
							));
							if (!empty($resp_unit[0]['Unit_id'])) {
								$saveRefValues['Unit_id'] = $resp_unit[0]['Unit_id'];
								$saveRefValues['UslugaTest_ResultUnit'] = $resp_unit[0]['Unit_Name'];
							}
						}
					}
					$data['UslugaTest_id'] = $test['UslugaTest_id'];
					$data['UslugaTest_ResultValue'] = '';
					$data['UslugaTest_Unit'] = '';
					$data['updateType'] = '';
					$data['UslugaTest_RefValues'] = json_encode($saveRefValues);

					$data['disableRecache'] = true;
					$this->updateResult($data);
				}
			}
		}

		$this->ReCacheLabSampleIsOutNorm(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		$this->ReCacheLabSampleStatus(array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		));

		// кэшируем статус заявки
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
			'EvnLabRequest_id' => $lrdata['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем статус проб в заявке
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $lrdata['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * Перенос тестов из одной пробы в другую
	 */
	function transferLabSampleResearches($data) {
		$researchData = array();

		// получаем данные заявки
		$lrdata = $this->getDataFromEvnLabRequest(array(
			'EvnLabSample_id' => $data['EvnLabSample_oldid']
		));

		foreach($data['tests'] as $test) {
			// 1. получаем данные по исследованию
			if (empty($researchData[$test['UslugaTest_pid']])) {
				$resp = $this->queryResult("
					select
						EvnUsluga_id as \"EvnUslugaPar_id\",
						UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_EvnUsluga
					where
						EvnUsluga_id = :UslugaTest_pid
				", array(
					'UslugaTest_pid' => $test['UslugaTest_pid']
				));

				if (!empty($resp[0])) {
					$researchData[$test['UslugaTest_pid']] = $resp[0];
				}
			}

			if (empty($researchData[$test['UslugaTest_pid']])) {
				return array('Error_Msg' => 'Ошибка получения данных по исследованию');
			}

			// назначем тест в новой пробе
			$data['EvnLabRequest_id'] = $lrdata['EvnLabRequest_id'];
			$data['tests'] = array(
				array(
					'UslugaTest_pid' => $test['UslugaTest_pid'],
					'UslugaComplex_id' => $test['UslugaComplex_id']
				)
			);
			$data['EvnLabSample_id'] = $data['EvnLabSample_newid'];
			$this->prescrTest($data);

			// отменяем тест в старой пробе
			$data['tests'] = array(
				array(
					'UslugaTest_pid' => $test['UslugaTest_pid'],
					'UslugaComplex_id' => $test['UslugaComplex_id']
				)
			);
			$data['EvnLabSample_id'] = $data['EvnLabSample_oldid'];
			$this->cancelTest($data);
		}

		// после переноса, если проба взята надо переопределить анализтор
		$resp = $this->queryResult("
			select
				EvnLabSample_setDT as \"EvnLabSample_setDT\"
			from
				v_EvnLabSample
			where
				EvnLabSample_id = :EvnLabSample_id
		", array(
			'EvnLabSample_id' => $data['EvnLabSample_oldid']
		));

		if (!empty($resp[0]['EvnLabSample_setDT'])) {
			$data['Analyzer_id'] = null;
			// получаем состав
			$tests = $this->getLabSampleResultGrid(array(
				'Lpu_id' => $lrdata['Lpu_id'],
				'EvnLabSample_id' => $data['EvnLabSample_oldid'],
				'EvnDirection_id' => $lrdata['EvnDirection_id']
			));

			$uccodes = array();
			foreach ($tests as $test) {
				if (!in_array($test['UslugaComplex_id'], $uccodes) && $test['UslugaTest_Status'] != 'Не назначен') {
					$uccodes[] = array(
						'UslugaComplexTest_id' => $test['UslugaComplex_id'],
						'UslugaComplexTarget_id' => $test['UslugaComplexTarget_id']
					);
				}
			}

			// получаем список возможных анализаторов
			$this->load->model('Analyzer_model');
			$resp_analyzer = $this->Analyzer_model->loadList(array(
				'Analyzer_IsNotActive' => 1,
				'EvnLabSample_id' => $data['EvnLabSample_oldid'],
				'uccodes' => $uccodes,
				'MedService_id' => $lrdata['MedService_id']
			));
			if (count($resp_analyzer) == 1) {
				$data['Analyzer_id'] = $resp_analyzer[0]['Analyzer_id'];
			} elseif (count($resp_analyzer) > 1) {
				if ($resp_analyzer[0]['pmUser_insID'] == 1 && $resp_analyzer[0]['Analyzer_Code'] == '000') { // если первый ручные методики
					$data['Analyzer_id'] = $resp_analyzer[1]['Analyzer_id']; // тогда берём второй
				} else { // иначе берём первый
					$data['Analyzer_id'] = $resp_analyzer[0]['Analyzer_id']; // тогда берём первый
				}
			}

			$this->saveLabSampleAnalyzer(array(
				'EvnLabSample_id' => $data['EvnLabSample_oldid'],
				'Analyzer_id' => $data['Analyzer_id'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return ['Error_Msg' => '', 'success' => true];
	}

	/**
	 * Одобрение результатов
	 */
	function approveResults($data) {
		// только где есть результат
		$filter = " and ut.UslugaTest_ResultValue IS NOT NULL and ut.UslugaTest_ResultValue <> ''";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id']
		);

		if (isset($data['onlyNorm']) && $data['onlyNorm']) {
			// TODO одобряем только результаты в норме
		}

		if (isset($data['UslugaTest_id'])) {
			$filter .= " and ut.UslugaTest_id = :UslugaTest_id";
			$params['UslugaTest_id'] = $data['UslugaTest_id'];
		}
		if (!empty($data['UslugaTest_ids'])) {
			$UslugaTest_ids = json_decode($data['UslugaTest_ids']);
			if (!empty($UslugaTest_ids)) {
				$filter .= " and ut.UslugaTest_id IN (".implode(',', $UslugaTest_ids).")";
			}
		}

		$query = "
			SELECT
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\",
				ls.Person_id as \"Person_id\"
			FROM
				v_UslugaTest ut
				inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
			where
				ls.EvnLabSample_id = :EvnLabSample_id
				and COALESCE(ut.UslugaTest_ResultApproved, 1) = 1
				{$filter}
		";

		$resp_eup = $this->queryResult($query, $params);
		if ($data['MedServiceType_SysNick'] == 'microbiolab' && !empty($UslugaTest_ids)) {
			$query_uta = "with UT as (
					select
						v_UslugaTest.UslugaTest_id,
						v_EvnLabSample.Person_id
					from dbo.v_UslugaTest
					inner join dbo.v_EvnLabSample on v_EvnLabSample.EvnLabSample_id = v_UslugaTest.EvnLabSample_id
					where UslugaTest_id in (".implode(',', $UslugaTest_ids).")
				)
				
				select
					uta.UslugaTest_id as \"UslugaTest_id\",
					uta.UslugaTest_pid as \"UslugaTest_pid\",
					ut.Person_id as \"Person_id\"
				from UT ut
				inner join dbo.v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
				inner join dbo.v_BactMicroProbeAntibiotic bmpa on bmpa.BactMicroProbe_id = bmp.BactMicroProbe_id
				inner join dbo.v_UslugaTest uta on uta.UslugaTest_id = bmpa.UslugaTest_id
				where uta.UslugaTest_ResultValue IS NOT NULL
			";
			$resp_uta = $this->queryResult($query_uta);
			$resp_eup = array_merge($resp_eup, $resp_uta);
		}

		$Person_id = null;

		$EvnUslugaParChanged = array();
		foreach($resp_eup as $respone) {
			$Person_id = $respone['Person_id'];

			if (!in_array($respone['UslugaTest_pid'], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $respone['UslugaTest_pid'];
			}

			$query = "
				UPDATE
					UslugaTest
				SET
					UslugaTest_ResultApproved = 2,
					UslugaTest_CheckDT = dbo.tzgetdate()
				WHERE
					UslugaTest_id = :UslugaTest_id
			";

			$res = $this->db->query($query, array(
				'UslugaTest_id' => $respone['UslugaTest_id']
			));
			if ($res) {
				collectEditedData('upd', 'UslugaTest', $respone['UslugaTest_id']);
			}

			$query2 = "
				select
					ls.Person_id as \"Person_id\",
					ut.UslugaTest_pid as \"UslugaTest_pid\",
					ut.PersonEvn_id as \"PersonEvn_id\",
					ut.Server_id as \"Server_id\",
					ut.Lpu_id as \"Lpu_id\",
					eup.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(t.Diag_id, d.Diag_id) as \"Diag_id\",
					uc.UslugaComplex_Code as \"UslugaComplex_Code\",
					ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
					ut.UslugaTest_Comment as \"UslugaTest_Comment\",
					ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\"
				from v_UslugaTest ut
					inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
					inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ut.UslugaTest_pid
					left join v_EvnLabRequest t on t.EvnLabRequest_id = ls.EvnLabRequest_id
					left join v_EvnDirection_all d on d.EvnDirection_id = t.EvnDirection_id
					left join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
				WHERE
					UslugaTest_id = :UslugaTest_id
			";

			$resp = $this->db->query($query2, array(
				'UslugaTest_id' => $respone['UslugaTest_id']
			));
			if(is_object($resp)){
				$resp = $resp->result('array');
			} else {
				$resp = array();
			}

			$uthParams = [
				'UslugaTestHistory_Comment' => $resp[0]['UslugaTest_Comment'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'pmUser_id' => $data['pmUser_id'],
				'UslugaTest_id' => $respone['UslugaTest_id'],
				'UslugaTestHistory_Result' => $resp[0]['UslugaTest_ResultValue']
			];

			$proc_query = "
				select *
				from p_UslugaTestHistory_ins(
					UslugaTestHistory_Comment := :UslugaTestHistory_Comment,
					MedStaffFact_id := :MedStaffFact_id,
					pmUser_id := :pmUser_id,
					UslugaTest_id := :UslugaTest_id,
					UslugaTestHistory_Result := :UslugaTestHistory_Result,
					UslugaTestHistory_CheckDT := dbo.tzgetdate()
				)
			";
			$tmp = $this->db->query($proc_query, $uthParams);

			// http://redmine.swan.perm.ru/issues/95959 Извещение по нефрологии при одобрении результатов
			if(in_array($this->getRegionNick(), array('perm', 'ufa'))){
				$query = "
					select
						ls.Person_id as \"Person_id\",
						ut.UslugaTest_pid as \"UslugaTest_pid\",
						ut.PersonEvn_id as \"PersonEvn_id\",
						ut.Server_id as \"Server_id\",
						ut.Lpu_id as \"Lpu_id\",
						eup.MedPersonal_id as \"MedPersonal_id\",
						COALESCE(t.Diag_id, d.Diag_id) as \"Diag_id\",
						uc.UslugaComplex_Code as \"UslugaComplex_Code\",
						ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
						ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\"
					from v_UslugaTest ut
						inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
						inner join v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ut.UslugaTest_pid
						left join v_EvnLabRequest t on t.EvnLabRequest_id = ls.EvnLabRequest_id
						left join v_EvnDirection_all d on d.EvnDirection_id = t.EvnDirection_id
						left join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
					WHERE
						UslugaTest_id = :UslugaTest_id
				";

				$resp = $this->db->query($query, array(
					'UslugaTest_id' => $respone['UslugaTest_id']
				));
				$resp = is_object($resp) ? $resp->result('array') : [];

				if (count($resp) > 0) {
					$UslugaTest_ResultValue = trim(str_replace(",", ".", $resp[0]['UslugaTest_ResultValue']));
					if (is_numeric($UslugaTest_ResultValue)) {
						$UslugaTest_ResultValue = floatval($UslugaTest_ResultValue);
						if (
							($resp[0]['UslugaComplex_Code'] == 'A09.28.006.001' && $resp[0]['UslugaTest_ResultUnit'] == 'мкмоль/л' && $UslugaTest_ResultValue > 97)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.28.009.001' && $resp[0]['UslugaTest_ResultUnit'] == 'ммоль/л' && $UslugaTest_ResultValue > 8.2)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.28.001.006' && $UslugaTest_ResultValue > 5)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.028.006.002' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.28.006.002' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.028.006.003' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.28.006.003' && $resp[0]['UslugaTest_ResultUnit'] == 'мл/мин' && $UslugaTest_ResultValue < 80)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.05.020.001' && $resp[0]['UslugaTest_ResultUnit'] == 'мкмоль/л' && $UslugaTest_ResultValue > 97)
							|| ($resp[0]['UslugaComplex_Code'] == 'A09.28.003' && $resp[0]['UslugaTest_ResultUnit'] == 'г/л' && $UslugaTest_ResultValue > 0.033)
						) {
							$this->load->library('swMorbus');
							$res = array(
								'data' => null,
								'session' => $data['session'],
								'pmUser_id' => $data['pmUser_id']
							);
							$res['data'] = $resp[0];
							$tmp = swMorbus::checkAndSaveEvnNotifyNephroFromLab($res);
						}
					}
				}
			}
		}

		//todo пока не до нотификации
		/*		foreach($EvnUslugaParChanged as $UslugaTest_pid) {
					//рассылка уведомлений врачам о выполнении параклинической услуги
					//в соответствии с настройками у каждого врача


					if(!empty($Person_id) && !empty($UslugaTest_pid)) {
						$this->load->helper('PersonNotice');
						$PersonNotice = new PersonNoticeEvn($Person_id, 'EvnUslugaParPolka', $UslugaTest_pid, true);
						$PersonNotice->loadPersonInfo();
						$PersonNotice->processStatusChange();//рассылаем
					}
				}*/

		if (!empty($EvnUslugaParChanged)) {
			$this->onChangeApproveResults(array(
				'EvnUslugaParChanged' => $EvnUslugaParChanged,
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$this->ReCacheLabSampleStatus($data);

		// если проба стала одобренной и в ней не заполнены данные о враче, то заполняем.
		$this->setEvnLabSampleDone(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		$resultReCache = $this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));
		// После успешного одобрения формируем данные для отправки в ПАК НИЦ МБУ, если подходит под условия (признак на службе)
		// Проверка на признак проверяется далее в preSendMbu
		// todo: По идее здесь надо просто положить данные в очередь activemq
		// $isMbu = (!empty($data['session']['CurARM']) && !empty($data['session']['CurARM']['MedService_IsSendMbu']))?$data['session']['CurARM']['MedService_IsSendMbu']:false;
		//if ($isMbu)
		{ // Если на текущей службе есть признак, то тогда подготавливаем к отправке
			$this->load->model('Mbu_model', 'Mbu_model');
			$this->Mbu_model->preSendMbu($data);
		}

		$this->EvnLabRequest_model->setProtocolPrintFlag([
			'EvnLabSamples' => [$data['EvnLabSample_id']]
		]);

		return array('Error_Msg' => '', 'UslugaExecutionType_id'=>$resultReCache['UslugaExecutionType_id']);
	}

	/**
	 * Проставление данных о выполнении пробы
	 */
	function setEvnLabSampleDone($data) {
		$query = "
			select
				els.LabSampleStatus_id as \"LabSampleStatus_id\",
				els.MedPersonal_aid as \"MedPersonal_aid\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			from
				v_EvnLabSample els
				inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				inner join v_MedService ms on ms.MedService_id = elr.MedService_id
			where
				els.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		$result_els = $this->db->query($query, $data);
		if (is_object($result_els)) {
			$resp_els = $result_els->result('array');
			if (!empty($resp_els[0]['LabSampleStatus_id']) && in_array($resp_els[0]['LabSampleStatus_id'], array(4,6))) {
				if (!empty($data['session']['CurLpuSection_id'])) {
					$data['Lpu_aid'] = $data['session']['lpu_id'];
					$data['LpuSection_aid'] = $data['session']['CurLpuSection_id'];
				} else {
					$data['Lpu_aid'] = $resp_els[0]['Lpu_id'];
					$data['LpuSection_aid'] = $resp_els[0]['LpuSection_id'];
				}
				$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];

				$query = "
					update
						EvnLabSample
					set
						Lpu_aid = :Lpu_aid,
						LpuSection_aid = :LpuSection_aid,
						MedPersonal_aid = :MedPersonal_aid
					where
						Evn_id = :EvnLabSample_id
				";

				$res = $this->db->query($query, $data);
				if ($res) {
					collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
				}
			} else {
				// если не одобрена очищаем поля
				$query = "
					update
						EvnLabSample
					set
						Lpu_aid = null,
						LpuSection_aid = null,
						MedPersonal_aid = null
					where
						Evn_id = :EvnLabSample_id
				";

				$res = $this->db->query($query, $data);
				if ($res) {
					collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
				}
			}
		}
	}

	/**
	 * Снятие одобрения результатов
	 */
	function unapproveResults($data) {
		// только где есть результат
		$filter = " and ut.UslugaTest_ResultValue IS NOT NULL and ut.UslugaTest_ResultValue <> ''";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabSample_id' => $data['EvnLabSample_id']
		);

		if (isset($data['UslugaTest_id'])) {
			$filter .= " and ut.UslugaTest_id = :UslugaTest_id";
			$params['UslugaTest_id'] = $data['UslugaTest_id'];
		}

		if (!empty($data['UslugaTest_ids'])) {
			$UslugaTest_ids = json_decode($data['UslugaTest_ids']);
			if (!empty($UslugaTest_ids)) {
				$filter .= " and ut.UslugaTest_id IN (".implode(',', $UslugaTest_ids).")";
			}
		}

		$query = "
			SELECT
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\"
			FROM
				v_UslugaTest ut
				inner join v_EvnLabSample ls on ut.EvnLabSample_id = ls.EvnLabSample_id
			where
				ls.EvnLabSample_id = :EvnLabSample_id
				{$filter}
		";

		$resp_eup = $this->queryResult($query, $params);
		if ($data['MedServiceType_SysNick'] == 'microbiolab' && !empty($UslugaTest_ids)) {
			$query_uta = "with UT as (
					select
						v_UslugaTest.UslugaTest_id,
						v_EvnLabSample.Person_id
					from dbo.v_UslugaTest
					inner join dbo.v_EvnLabSample on v_EvnLabSample.EvnLabSample_id = v_UslugaTest.EvnLabSample_id
					where UslugaTest_id in (".implode(',', $UslugaTest_ids).")
				)
				
				select
					uta.UslugaTest_id as \"UslugaTest_id\",
					uta.UslugaTest_pid as \"UslugaTest_pid\",
					ut.Person_id as \"Person_id\"
				from UT ut
				inner join dbo.v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
				inner join dbo.v_BactMicroProbeAntibiotic bmpa on bmpa.BactMicroProbe_id = bmp.BactMicroProbe_id
				inner join dbo.v_UslugaTest uta on uta.UslugaTest_id = bmpa.UslugaTest_id
			";
			$resp_uta = $this->queryResult($query_uta);
			$resp_eup = array_merge($resp_eup, $resp_uta);
		}

		$EvnUslugaParChanged = array();
		foreach($resp_eup as $respone) {
			if (!in_array($respone['UslugaTest_pid'], $EvnUslugaParChanged)) {
				$EvnUslugaParChanged[] = $respone['UslugaTest_pid'];
			}

			$query = "
				UPDATE
					UslugaTest
				SET
					UslugaTest_ResultApproved = 1,
					UslugaTest_CheckDT = null
				WHERE
					UslugaTest_id = :UslugaTest_id
			";

			$res = $this->db->query($query, array(
				'UslugaTest_id' => $respone['UslugaTest_id']
			));
			if ($res) {
				collectEditedData('upd', 'UslugaTest', $data['UslugaTest_id']);
			}
		}

		if (!empty($EvnUslugaParChanged)) {
			$this->onChangeApproveResults(array(
				'EvnUslugaParChanged' => $EvnUslugaParChanged,
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$this->ReCacheLabSampleStatus($data);

		// если проба стала не одобренной очищаем данные о выполнившем враче.
		$this->setEvnLabSampleDone(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		$resultReCache = $this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		//Удаляем статус печати протокола
		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->setProtocolPrintFlag([
			'EvnLabSamples' => [$data['EvnLabSample_id']]
		]);

		return array('Error_Msg' => '', 'UslugaExecutionType_id'=>$resultReCache['UslugaExecutionType_id']);
	}

	/**
	 * Функция данные из заявки (EvnDirection_id, UslugaComplex_id, PayType_id)
	 */
	function getDataFromEvnLabRequest($data) {
		$filter = "";
		$join = "";
		$queryParams = array();
		$MedServiceJoin = "ms.MedService_id = elr.MedService_id";

		if (!empty($data['EvnLabRequest_id'])) {
			$filter .= " and elr.EvnLabRequest_id = :EvnLabRequest_id";
			$queryParams['EvnLabRequest_id'] = $data['EvnLabRequest_id'];
		} else if (!empty($data['EvnLabSample_id'])) {
			$join .= "inner join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id";
			$filter .= " and els.EvnLabSample_id = :EvnLabSample_id";
			$queryParams['EvnLabSample_id'] = $data['EvnLabSample_id'];
			$MedServiceJoin = "ms.MedService_id = COALESCE(els.MedService_id, elr.MedService_id)";
		} else {
			return false;
		}

		$query = "
			SELECT
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.EvnDirection_id as \"EvnDirection_id\",
				elr.UslugaComplex_id as \"UslugaComplex_id\",
				elr.PayType_id as \"PayType_id\",
				elr.Server_id as \"Server_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				elr.Person_id as \"Person_id\",
				elr.Mes_id as \"Mes_id\",
				elr.Diag_id as \"Diag_id\",
				ms.MedService_id as \"MedService_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuSection_id as \"LpuSection_id\"
			FROM
				v_EvnLabRequest elr
				{$join}
				inner join v_MedService ms on {$MedServiceJoin}
			WHERE
				(1=1)
				{$filter}
		";

		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result) || count($result) == 0) {
			return null;
		}

		return $result[0];
	}

	/**
	 * Функция создает родительскую услугу для остальных в EvnUslugaPar
	 * TODO: Скорее всего эту функцию надо будет перенести в сохранение заявки
	 * @param $UslugaComplex_id
	 * @param $PayType_id
	 * @param $EvnDirection_id
	 * @return bool
	 */
	function saveEvnUslugaRoot($data) {
		// проверяем если уже создано такое исследование, то новое не создаём
		$resp = $this->queryResult("
			select
				EvnUsluga_id as \"EvnUslugaPar_id\"
			from
				v_EvnUsluga
			where
				EvnDirection_id = :EvnDirection_id
				and UslugaComplex_id = :UslugaComplex_id
		", array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		/*$this->load->database();
		$this->db = $this->load->database('lis', true);*/
		if (!empty($resp[0]['EvnUslugaPar_id'])) {
			return array(
				'new' => false,
				'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id']
			);
		}

		$resp = $this->saveEvnUslugaData(array(
			'EvnUslugaPar_id' => null,
			'EvnUslugaPar_setDT' => null,
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => null,
			'LpuSection_uid' => null,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Mes_id' => !empty($data['Mes_id'])?$data['Mes_id']:null,
			'Diag_id' => !empty($data['Diag_id'])?$data['Diag_id']:null,
			'Usluga_id' => null,
			'PayType_id' => $data['PayType_id'],
			'UslugaPlace_id' => 1,
			'EvnUslugaPar_pid' => null,
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnPrescr_id' => !empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : null,
			'ResultDataJson' => '',
			'RefValues_id' => null,
			'Unit_id' => null,
			'EvnUslugaPar_Comment' => null,
			'isReloadCount' => null,
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($data['EvnLabRequest_id'])) {
			// кэшируем названия услуг на заявке
			$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
			$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnDirection_id' => $data['EvnDirection_id']
			));
		}

		if (!empty($resp)) {
			return array(
				'new' => true,
				'EvnUslugaPar_id' => $resp
			);
		}
	}

	/**
	 * Сохранение параметров исследования
	 */
	function saveResearch($data) {
		//todo было select *
		//опять же имеет смысл убрать ненужные поля
		$EvnUslugaParData = $this->queryResult("
			select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				LpuDispContract_id as \"LpuDispContract_id\",
				EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				Mes_id as \"Mes_id\",
				UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				Registry_sid as \"Registry_sid\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimeTablePar_id as \"TimeTablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				MedProductCard_id as \"MedProductCard_id\",
				EvnRequest_id as \"EvnRequest_id\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				DeseaseType_id as \"DeseaseType_id\",
				EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				StudyResult_id as \"StudyResult_id\",
				EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				TumorStage_id as \"TumorStage_id\",
				EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (empty($EvnUslugaParData[0]['EvnUslugaPar_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по услуге');
		}

		if (empty($data['MedPersonal_aid']) && empty($data['MedPersonal_said'])) {
			return array('Error_Msg' => 'Должен быть выбран врач или ср. медперсонал');
		}

		$params = $EvnUslugaParData[0];
		$params['pmUser_id'] = $data['pmUser_id'];

		if (strtotime($data['EvnUslugaPar_setDate']) > strtotime(date('Y-m-d')) + 24*60*60) {
			return array('Error_Msg' => 'Дата выполнения исследования не может быть позже текущей');
		}

		$EvnPrescr_Date = $this->common->GET('EvnPrescr/setDateByUslugaPar', [
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		], 'single');
		if (!$this->isSuccessful($EvnPrescr_Date)) {
			return $EvnPrescr_Date;
		}
		$EvnPrescr_Date = $EvnPrescr_Date['EvnPrescr_Date'];

		if (!empty($EvnPrescr_Date) && strtotime($data['EvnUslugaPar_setDate']) < strtotime($EvnPrescr_Date)) {
			return array('Error_Msg' => 'Дата выполнения исследования не может быть раньше даты назначения');
		}

		$params['EvnUslugaPar_setDT'] = $data['EvnUslugaPar_setDate'];
		if (!empty($data['EvnUslugaPar_setTime'])) {
			$params['EvnUslugaPar_setDT'] .= ' '.$data['EvnUslugaPar_setTime'];
		}
		$params['LpuSection_uid'] = $data['LpuSection_aid'];
		$params['MedPersonal_id'] = $data['MedPersonal_aid'];
		$params['MedPersonal_sid'] = $data['MedPersonal_said'];
		$params['MedStaffFact_id'] = null; // будет определен на основании MedPersonal_id и LpuSection_uid, т.к. на форме выбирается врач, а не рабочее место врача.
		$params['EvnUslugaPar_Comment'] = $data['EvnUslugaPar_Comment'];
		$params['EvnUslugaPar_IndexRep'] = $data['EvnUslugaPar_IndexRep'];
		$params['EvnUslugaPar_IndexRepInReg'] = $data['EvnUslugaPar_IndexRepInReg'];
		$params['UslugaMedType_id'] = $data['UslugaMedType_id'];

		$this->saveEvnUslugaData($params);

		// обновляем протокол
		$this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $params['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '', 'EvnUslugaPar_id' => $data['EvnUslugaPar_id'], 'success' => true);
	}

	/**
	 * Обновление только комментария исследования
	 */
	function saveComment($data) {
		$EvnUslugaParData = $this->queryResult("
			select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				LpuDispContract_id as \"LpuDispContract_id\",
				EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				Mes_id as \"Mes_id\",
				UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				Registry_sid as \"Registry_sid\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimeTablePar_id as \"TimeTablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				MedProductCard_id as \"MedProductCard_id\",
				EvnRequest_id as \"EvnRequest_id\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				DeseaseType_id as \"DeseaseType_id\",
				EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				StudyResult_id as \"StudyResult_id\",
				EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				TumorStage_id as \"TumorStage_id\",
				EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (empty($EvnUslugaParData[0]['EvnUslugaPar_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по услуге');
		}

		$params = $EvnUslugaParData[0];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['EvnUslugaPar_Comment'] = $data['EvnUslugaPar_Comment'];

		$this->saveEvnUslugaData($params);

		// обновляем протокол
		$this->ReCacheLabRequestByLabSample(array(
			'EvnLabSample_id' => $params['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '', 'EvnUslugaPar_id' => $data['EvnUslugaPar_id']);
	}

	/**
	 * Обновление только выполения исследования
	 */
	function saveEvnUslugaDone($data) {
		$EvnUslugaParData = $this->queryResult("
			select
				eup.EvnClass_id as \"EvnClass_id\",
				eup.EvnClass_Name as \"EvnClass_Name\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup.EvnUslugaPar_setDate as \"EvnUslugaPar_setDate\",
				eup.EvnUslugaPar_setTime as \"EvnUslugaPar_setTime\",
				eup.EvnUslugaPar_didDate as \"EvnUslugaPar_didDate\",
				eup.EvnUslugaPar_didTime as \"EvnUslugaPar_didTime\",
				eup.EvnUslugaPar_disDate as \"EvnUslugaPar_disDate\",
				eup.EvnUslugaPar_disTime as \"EvnUslugaPar_disTime\",
				eup.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				eup.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				eup.Lpu_id as \"Lpu_id\",
				eup.Server_id as \"Server_id\",
				eup.PersonEvn_id as \"PersonEvn_id\",
				eup.EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				eup.EvnUslugaPar_disDT as \"EvnUslugaPar_disDT\",
				eup.EvnUslugaPar_didDT as \"EvnUslugaPar_didDT\",
				eup.EvnUslugaPar_insDT as \"EvnUslugaPar_insDT\",
				eup.EvnUslugaPar_updDT as \"EvnUslugaPar_updDT\",
				eup.EvnUslugaPar_Index as \"EvnUslugaPar_Index\",
				eup.EvnUslugaPar_Count as \"EvnUslugaPar_Count\",
				eup.pmUser_insID as \"pmUser_insID\",
				eup.pmUser_updID as \"pmUser_updID\",
				eup.Person_id as \"Person_id\",
				eup.Morbus_id as \"Morbus_id\",
				eup.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				eup.pmUser_signID as \"pmUser_signID\",
				eup.EvnUslugaPar_signDT as \"EvnUslugaPar_signDT\",
				eup.EvnUslugaPar_IsArchive as \"EvnUslugaPar_IsArchive\",
				eup.EvnUslugaPar_Guid as \"EvnUslugaPar_Guid\",
				eup.EvnUslugaPar_IndexMinusOne as \"EvnUslugaPar_IndexMinusOne\",
				eup.EvnStatus_id as \"EvnStatus_id\",
				eup.EvnUslugaPar_statusDate as \"EvnUslugaPar_statusDate\",
				eup.EvnUslugaPar_IsTransit as \"EvnUslugaPar_IsTransit\",
				eup.PayType_id as \"PayType_id\",
				eup.Usluga_id as \"Usluga_id\",
				eup.MedPersonal_id as \"MedPersonal_id\",
				eup.UslugaPlace_id as \"UslugaPlace_id\",
				eup.Lpu_uid as \"Lpu_uid\",
				eup.LpuSection_uid as \"LpuSection_uid\",
				eup.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				eup.Org_uid as \"Org_uid\",
				eup.UslugaComplex_id as \"UslugaComplex_id\",
				eup.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				eup.MedPersonal_sid as \"MedPersonal_sid\",
				eup.EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				eup.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				eup.EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				eup.MesOperType_id as \"MesOperType_id\",
				eup.EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				eup.EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				eup.EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				eup.EvnPrescr_id as \"EvnPrescr_id\",
				eup.EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				eup.EvnCourse_id as \"EvnCourse_id\",
				eup.EvnUslugaPar_IsVizitCode as \"EvnUslugaPar_IsVizitCode\",
				eup.EvnUslugaPar_IsInReg as \"EvnUslugaPar_IsInReg\",
				eup.EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				eup.MedStaffFact_id as \"MedStaffFact_id\",
				eup.MedSpecOms_id as \"MedSpecOms_id\",
				eup.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				eup.EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				eup.EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				eup.EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				eup.DiagSetClass_id as \"DiagSetClass_id\",
				eup.Diag_id as \"Diag_id\",
				eup.LpuDispContract_id as \"LpuDispContract_id\",
				eup.EvnUslugaPar_IsMinusUsluga as \"EvnUslugaPar_IsMinusUsluga\",
				eup.Mes_id as \"Mes_id\",
				eup.UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				eup.UslugaExecutionType_id as \"UslugaExecutionType_id\",
				eup.Registry_sid as \"Registry_sid\",
				eup.Lpu_oid as \"Lpu_oid\",
				eup.PrehospDirect_id as \"PrehospDirect_id\",
				eup.LpuSection_did as \"LpuSection_did\",
				eup.Lpu_did as \"Lpu_did\",
				eup.Org_did as \"Org_did\",
				eup.MedPersonal_did as \"MedPersonal_did\",
				eup.TimeTablePar_id as \"TimeTablePar_id\",
				eup.EvnLabSample_id as \"EvnLabSample_id\",
				eup.Study_uid as \"Study_uid\",
				eup.EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				eup.EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				eup.EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				eup.EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				eup.EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				eup.EvnUslugaPar_ResultAppDate as \"EvnUslugaPar_ResultAppDate\",
				eup.EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				eup.EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				eup.EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				eup.EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				eup.RefValues_id as \"RefValues_id\",
				eup.Unit_id as \"Unit_id\",
				eup.EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				eup.EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				eup.EvnDirection_Num as \"EvnDirection_Num\",
				eup.EvnDirection_setDT as \"EvnDirection_setDT\",
				eup.MedProductCard_id as \"MedProductCard_id\",
				eup.EvnRequest_id as \"EvnRequest_id\",
				eup.EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\",
				eup.DeseaseType_id as \"DeseaseType_id\",
				eup.EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				eup.StudyResult_id as \"StudyResult_id\",
				eup.EvnUslugaPar_AnalyzerDate as \"EvnUslugaPar_AnalyzerDate\",
				eup.TumorStage_id as \"TumorStage_id\",
				eup.EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\",
				{$data['Evn_setDT']} as \"Evn_setDT\",
				ms.LpuSection_id as \"LpuSection_uid\"
			from
				v_EvnUslugaPar eup
				left join v_EvnLabRequest elr on eup.EvnDirection_id = elr.EvnDirection_id
				left join v_MedService ms on elr.MedService_id = ms.MedService_id
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (empty($EvnUslugaParData[0]['EvnUslugaPar_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по услуге');
		}

		$params = $EvnUslugaParData[0];
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['EvnUslugaPar_setDT'] = $params['Evn_setDT'];
		$params['EvnUslugaPar_pid'] = $data['EvnUslugaPar_pid'];
		$params['MedPersonal_id'] = $data['MedPersonal_id'];
		$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$params['noNeedDefineUslugaParams'] = true;
		if (!empty($data['LpuSection_uid'])) { //берем, если явно указано, иначе оставляем ид отделения по службе
			$params['LpuSection_uid'] = $data['LpuSection_uid'];
		}

		$this->saveEvnUslugaData($params);

		return array('Error_Msg' => '', 'EvnUslugaPar_id' => $data['EvnUslugaPar_id']);
	}

	/**
	 * Сохранение параметров исследования
	 */
	function loadResearchEditForm($data) {
		$sql_fields = "";
		$sql_joins = "";
		if (getRegionNick() === 'kz') {
			$sql_fields .= "
		            UMTL.UslugaMedType_id as \"UslugaMedType_id\",
		        ";
			$sql_joins .= "
		            LEFT JOIN r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=eup.EvnUslugaPar_id
		        ";
		}

		// можно начитывать и делать p_EvnUslugaPar_upd
		$query = "
			select
				{$sql_fields}
				COALESCE(ls.Lpu_id, elr.Lpu_id) as \"Lpu_aid\",
				eup.LpuSection_uid as \"LpuSection_aid\",
				eup.MedPersonal_id as \"MedPersonal_aid\",
				eup.MedPersonal_sid as \"MedPersonal_said\",
				eup.EvnLabSample_id as \"EvnLabSample_id\",
				TO_CHAR(eup.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				to_char(eup.EvnUslugaPar_updDT, 'HH24:MI:SS') as \"EvnUslugaPar_setTime\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				to_char(dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnUslugaPar_maxDate\",
				eup.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				COALESCE(eup.EvnUslugaPar_IsPaid, 1) as \"EvnUslugaPar_IsPaid\",
				COALESCE(eup.EvnUslugaPar_IndexRep, 0) as \"EvnUslugaPar_IndexRep\",
				COALESCE(eup.EvnUslugaPar_IndexRepInReg, 1) as \"EvnUslugaPar_IndexRepInReg\"
			from
				v_EvnUslugaPar eup
				left join v_EvnLabRequest elr on elr.EvnDirection_id = eup.EvnDirection_id
				left join v_LpuSection ls on ls.LpuSection_id = eup.LpuSection_uid
				{$sql_joins}
			where
				eup.EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$resp = $this->queryResult($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (empty($resp[0]['EvnUslugaPar_setDate']) && !empty($resp[0]['EvnLabSample_id'])) {
			$sql_fields = "";
			$sql_joins = "";
			if (getRegionNick() === 'kz') {
				$sql_fields .= "
		            UMTL.UslugaMedType_id as \"UslugaMedType_id\",
		        ";
				$sql_joins .= "
		            LEFT JOIN r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=ELS.EvnLabSample_id
		        ";
			}

			// берём с пробы
			$query = "
				select
					{$sql_fields}
					ELS.Lpu_aid as \"Lpu_aid\",
					ELS.LpuSection_aid as \"LpuSection_aid\",
					ELS.MedPersonal_aid as \"MedPersonal_aid\",
					ELS.MedPersonal_said as \"MedPersonal_said\",
					ELS.EvnLabSample_id as \"EvnLabSample_id\",
					null as \"EvnUslugaPar_setDate\",
					null as \"EvnUslugaPar_setTime\",
					ELS.EvnLabSample_id as \"EvnLabSample_id\",
					to_char (dbo.tzGetDate(), 'dd.mm.yyyy') as \"EvnUslugaPar_maxDate\"
				from
					v_EvnLabSample ELS
					{$sql_joins}
				where
					EvnLabSample_id = :EvnLabSample_id
			";

			$resp = $this->queryResult($query, array(
				'EvnLabSample_id' => $resp[0]['EvnLabSample_id']
			));
		}

		if (!empty($resp[0])) {
			$resp[0]['EvnUslugaPar_minDate'] = null;
			if (!empty($resp[0]['EvnDirection_id'])) {

				$res = $this->common->GET('EvnPrescr/EvnPrescrInsDate', [
					'EvnDirection_id' => $resp[0]['EvnDirection_id']
				], 'single');
				if (!$this->isSuccessful($res)) {
					return $res;
				}

				if (!empty($res['EvnPrescr_insDate'])) {
					$resp[0]['EvnUslugaPar_minDate'] = $res['EvnPrescr_insDate'];
				}
			}
		}

		return $resp;
	}

	/**
	 * Сохраняет результаты анализов в услугу (с созданием услуги, если она еще не существует)
	 *  Возвращает идентификатор услуги EvnUslugaPar_id
	 *
	 * @param null $EvnUslugaPar_id
	 * @param $UslugaComplex_id
	 * @param $Usluga_id
	 * @param $PayType_id
	 * @param int $UslugaPlace_id
	 * @param $EvnUslugaPar_pid
	 * @param $EvnDirection_id
	 * @param $ResultDataJson
	 * @return bool
	 * @throws Exception
	 */
	function saveEvnUslugaData($data) {
		$isReloadCount = "";
		if ($data['EvnUslugaPar_id']>0){
			$ins_upd = 'upd';
		} else {
			$ins_upd = 'ins';
			$isReloadCount = ",isReloadCount := :isReloadCount";
		}

		if (empty($data['PayType_id'])) {
			$PayType_SysNick = $this->getPayTypeSysNick();
			$data['PayType_id'] = $this->getFirstResultFromQuery("
				select
					PayType_id as \"PayType_id\"
				from
					v_PayType
				where
					PayType_SysNick = '{$PayType_SysNick}'
				limit 1
				");
		}

		if (empty($data['MedStaffFact_id']) && !empty($data['MedPersonal_id']) && !empty($data['LpuSection_uid'])) {
			$MedStaffFact = $this->common->GET('MedStaffFact/Id', [
				'MedPersonal_id' => $data['MedPersonal_id'],
				'LpuSection_id' => $data['LpuSection_uid']
			], 'single');
			if (!$this->isSuccessful($MedStaffFact)) {
				throw new Exception($MedStaffFact['Error_Msg']);
			}

			$data['MedStaffFact_id'] = (isset($MedStaffFact['MedStaffFact_id']) && $MedStaffFact['MedStaffFact_id'] > 0) ?
				$MedStaffFact['MedStaffFact_id'] : null;
		}

		if (empty($data['EvnUslugaPar_setDT'])) {
			$data['EvnUslugaPar_setDT'] = null;
		} else if ($data['EvnUslugaPar_setDT'] instanceof DateTime) {
			$data['EvnUslugaPar_setDT'] = $data['EvnUslugaPar_setDT']->format('Y.m.d H:i:s');
		} else {
			$data['EvnUslugaPar_setDT'] = explode(' ', $data['EvnUslugaPar_setDT'])[0];
		}


		$dt = [
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnPrescr_id' => !empty($data['EvnPrescr_id']) ? $data['EvnPrescr_id'] : null,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDT']
		];

		if (!empty($data['noNeedDefineUslugaParams'])) {
			$uslugaParams = $this->common->GET('EvnPrescr/defineUslugaParams', $dt, 'single');
			if (!$this->isSuccessful($uslugaParams)) {
				$err = $uslugaParams['Error_Msg'];
				throw new \Exception($err, 400);
			}

			$data['EvnPrescr_id'] = ($uslugaParams['EvnPrescr_id'] > 0) ?
				$uslugaParams['EvnPrescr_id'] : null;
			$data['EvnUslugaPar_pid'] = ($uslugaParams['EvnUslugaPar_pid'] > 0) ?
				$uslugaParams['EvnUslugaPar_pid'] : null;
		}

		//доработки по https://redmine.swan.perm.ru/issues/119069 - добавим TumorStage_id, взяв его из EvnLabRequest
		$TumorStage_id = null;
		$query_get_Tumor = "
			select
				ELR.TumorStage_id as \"TumorStage_id\"
			from
				v_EvnLabSample ELS
				inner join v_EvnLabRequest ELR on ELR.EvnLabRequest_id = ELS.EvnLabRequest_id
			where
				ELS.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";
		if(!empty($data['EvnLabSample_id']))
		{
			$result_get_Tumor = $this->db->query($query_get_Tumor,array('EvnLabSample_id' => $data['EvnLabSample_id']));
			if(is_object($result_get_Tumor))
			{
				$result_get_Tumor = $result_get_Tumor->result('array');
				if(is_array($result_get_Tumor) && count($result_get_Tumor) > 0)
					$TumorStage_id = $result_get_Tumor[0]['TumorStage_id'];
			}
		}

		if (!empty($data['EvnDirection_id']) && empty($data['EvnUslugaPar_pid'])) {
			$query = "
				select
					EvnDirection_pid as \"EvnUslugaPar_pid\"
				from v_EvnDirection_all
				where EvnDirection_id = :EvnDirection_id
				limit 1
			";

			$pid = $this->queryResult($query, $data);
			if (isset($pid[0]['EvnUslugaPar_pid']))
				$data['EvnUslugaPar_pid'] = $pid[0]['EvnUslugaPar_pid'];
		}

		$query = "
			select
				evnuslugapar_id as \"EvnUslugaPar_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_EvnUslugaPar_{$ins_upd}(
				Evnuslugapar_id := :EvnUslugaPar_id,
				Evnuslugapar_pid := :EvnUslugaPar_pid,
				Evnuslugapar_setDT := :EvnUslugaPar_setDT,
				Lpu_id := :Lpu_id,
				MedPersonal_id := :MedPersonal_id,
				MedPersonal_sid := :MedPersonal_sid,
				MedStaffFact_id := :MedStaffFact_id,
				LpuSection_uid := :LpuSection_uid,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				UslugaComplex_id := :UslugaComplex_id,
				Mes_id := :Mes_id,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				EvnDirection_id := :EvnDirection_id,
				Usluga_id := :Usluga_id,
				PayType_id := :PayType_id,
				UslugaPlace_id := :UslugaPlace_id,
				EvnUslugaPar_Kolvo := 1,
				EvnUslugaPar_Result := :EvnUslugaPar_Result,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				EvnLabSample_id := :EvnLabSample_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := :EvnPrescr_id,
				PrehospDirect_id := :PrehospDirect_id,
				pmUser_id := :pmUser_id,
				EvnUslugaPar_IsSigned := :EvnUslugaPar_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnUslugaPar_signDT := :EvnUslugaPar_signDT
				{$isReloadCount}
			)
		";

		if (!empty($data['EvnUslugaPar_IsSigned']) && $data['EvnUslugaPar_IsSigned'] == 2) {
			// раз обновляем, значит подпись становится не актуальной
			$data['EvnUslugaPar_IsSigned'] = 1;
		}

		$params = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'EvnUslugaPar_setDT' => !empty($data['EvnUslugaPar_setDT']) ? $data['EvnUslugaPar_setDT'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedPersonal_sid' => !empty($data['MedPersonal_sid']) ? $data['MedPersonal_sid'] : null,
			'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null,
			'LpuSection_uid' => $data['LpuSection_uid'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Mes_id' => !empty($data['Mes_id']) ? $data['Mes_id'] : null,
			'Diag_id' => !empty($data['Diag_id']) ? $data['Diag_id'] : null,
			'TumorStage_id' => $TumorStage_id,
			'Usluga_id' => $data['Usluga_id'],
			'PayType_id' => $data['PayType_id'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnUslugaPar_Result' => null,
			'RefValues_id' => !empty($data['RefValues_id']) ? $data['RefValues_id'] : null,
			'Unit_id' => !empty($data['Unit_id']) ? $data['Unit_id'] : null,
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'pmUser_id' => $data['pmUser_id'],
			'isReloadCount' => !empty($data['isReloadCount']) ? $data['isReloadCount'] : null,
			'EvnUslugaPar_Index' => isset($data['EvnUslugaPar_Index']) ? $data['EvnUslugaPar_Index'] : null,
			'EvnUslugaPar_Comment' => $data['EvnUslugaPar_Comment'],
			'EvnUslugaPar_IndexRep' =>  (!empty($data['EvnUslugaPar_IndexRep']))?$data['EvnUslugaPar_IndexRep']: 0,
			'EvnUslugaPar_IndexRepInReg' =>  (!empty($data['EvnUslugaPar_IndexRepInReg']))?$data['EvnUslugaPar_IndexRepInReg']: 1,
			'EvnPrescr_id' => (!empty($data['EvnPrescr_id'])) ? $data['EvnPrescr_id'] : null,
			'PrehospDirect_id' => (isset($data['PrehospDirect_id']) && !empty($data['PrehospDirect_id'])) ? $data['PrehospDirect_id'] : null,
			'EvnUslugaPar_IsSigned' => (!empty($data['EvnUslugaPar_IsSigned'])) ? $data['EvnUslugaPar_IsSigned'] : null,
			'pmUser_signID' => (!empty($data['pmUser_signID'])) ? $data['pmUser_signID'] : null,
			'EvnUslugaPar_signDT' => (!empty($data['EvnUslugaPar_signDT'])) ? $data['EvnUslugaPar_signDT'] : null
		);

		$dbresponse = $this->db->query($query, $params);
		//echo getDebugSql($query, $params);
		if (is_object($dbresponse)) {
			$result = $dbresponse->result('array');
			$save_ok = self::save_ok($result);
			if (!$save_ok){
				throw new Exception(
					'При создании факта оказания услуги произошла ошибка: '.
					$result[0]['Error_Code'].
					$result[0]['Error_Msg']
				);
			} else {
				if (isset($result[0])) {
					if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($uslugaParams['needRecalcKSGKPGKOEF'])) {
						if (empty($data['session'])) {
							if (isset($this->sessionParams)) {
								$data['session'] = $this->sessionParams;
							} else {
								$sp = getSessionParams();
								$data['session'] = $sp['session'];
							}
						}
						$res = $this->common->POST('EvnSection/recalcKSGKPGKOEF', [
							'EvnSection_id' => $uslugaParams['EvnUslugaPar_pid']
						], 'single');
						if (!$this->isSuccessful($res)) {
							throw new Exception($res['Error_Msg'], 400);
						}
					}
					if (!empty($result[0]['EvnUslugaPar_id'])) {
						$this->saveUslugaMedTypeLink(
							$result[0]['EvnUslugaPar_id'],
							isset($data['UslugaMedType_id']) ? $data['UslugaMedType_id'] : null,
							$data['pmUser_id']
						);
					}

					collectEditedData($ins_upd, 'EvnUslugaPar', $data['EvnUslugaPar_id']);
					return $result[0]['EvnUslugaPar_id'];
				}
			}
		} else {
			throw new Exception(
				'При создании факта оказания услуги произошла ошибка: '.var_export($this->db->_error_message(), true)
			);
		}
		return true;
	}

	/**
	 * Сохраняет тесты
	 */
	function saveUslugaTest($data) {
		if ($data['UslugaTest_id']>0){
			$ins_upd = 'upd';
		} else {
			$ins_upd = 'ins';
		}

		if (empty($data['PayType_id'])) {
			$PayType_SysNick = $this->getPayTypeSysNick();
			$data['PayType_id'] = $this->getFirstResultFromQuery("
				select
					PayType_id as \"PayType_id\"
				from
					v_PayType
				where
					PayType_SysNick = '{$PayType_SysNick}'
				limit 1");
		}

		if (empty($data['UslugaTest_setDT'])) {
			$data['UslugaTest_setDT'] = null;
		}

		if (empty($data['ResultDataJson'])) {
			$data['ResultDataJson'] = json_encode(array(
				'EUD_value' => null,
				'EUD_lower_bound' => toUtf(trim($data['UslugaTest_ResultLower'])),
				'EUD_upper_bound' => toUtf(trim($data['UslugaTest_ResultUpper'])),
				'EUD_unit_of_measurement' => toUtf(trim($data['UslugaTest_ResultUnit']))
			));
		}

		$query = "
		    select
				uslugatest_id as \"UslugaTest_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_uslugatest_{$ins_upd}(
				UslugaTest_id := :UslugaTest_id, -- bigint
				UslugaTest_pid := :UslugaTest_pid,       -- bigint
				UslugaTest_rid := :UslugaTest_pid,       -- bigint
				UslugaTest_setDT := :UslugaTest_setDT,                   -- date
				Lpu_id := :Lpu_id,                           -- bigint
				Server_id := :Server_id,                     -- bigint
				PersonEvn_id := :PersonEvn_id,               -- bigint
				UslugaComplex_id := :UslugaComplex_id,
				EvnDirection_id := :EvnDirection_id,
				PayType_id := :PayType_id,                   -- bigint
				UslugaTest_Kolvo := 1,
				UslugaTest_Result := :UslugaTest_Result, -- bigint
				UslugaTest_ResultLower := :UslugaTest_ResultLower,
				UslugaTest_ResultUpper := :UslugaTest_ResultUpper,
				UslugaTest_ResultLowerCrit := :UslugaTest_ResultLowerCrit,
				UslugaTest_ResultUpperCrit := :UslugaTest_ResultUpperCrit,
				UslugaTest_ResultQualitativeNorms := :UslugaTest_ResultQualitativeNorms,
				UslugaTest_ResultQualitativeText := :UslugaTest_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				UslugaTest_ResultValue := cast(:UslugaTest_ResultValue as varchar),
				UslugaTest_ResultUnit := :UslugaTest_ResultUnit,
				UslugaTest_ResultApproved := :UslugaTest_ResultApproved,
				UslugaTest_CheckDT := :UslugaTest_CheckDT,
				UslugaTest_Comment := :UslugaTest_Comment,
				LabTest_id := :LabTest_id,
				EvnLabSample_id := :EvnLabSample_id,         -- bigint
				pmUser_id := :pmUser_id                    -- bigint
			)
		";
		$params = array(
			'UslugaTest_id' => $data['UslugaTest_id'],
			'UslugaTest_pid' => $data['UslugaTest_pid'],
			'UslugaTest_setDT' => !empty($data['UslugaTest_setDT']) ? $data['UslugaTest_setDT'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'PayType_id' => $data['PayType_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaTest_Result' => $data['ResultDataJson'],
			'UslugaTest_ResultLower' => $data['UslugaTest_ResultLower'],
			'UslugaTest_ResultUpper' => $data['UslugaTest_ResultUpper'],
			'UslugaTest_ResultLowerCrit' => $data['UslugaTest_ResultLowerCrit'],
			'UslugaTest_ResultUpperCrit' => $data['UslugaTest_ResultUpperCrit'],
			'UslugaTest_ResultQualitativeNorms' => $data['UslugaTest_ResultQualitativeNorms'],
			'UslugaTest_ResultQualitativeText' => $data['UslugaTest_ResultQualitativeText'],
			'RefValues_id' => !empty($data['RefValues_id']) ? $data['RefValues_id'] : null,
			'Unit_id' => !empty($data['Unit_id']) ? $data['Unit_id'] : null,
			'UslugaTest_ResultValue' => ($data['UslugaTest_ResultValue']),
			'UslugaTest_ResultUnit' => $data['UslugaTest_ResultUnit'],
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'pmUser_id' => $data['pmUser_id'],
			'isReloadCount' => !empty($data['isReloadCount']) ? $data['isReloadCount'] : null,
			'UslugaTest_Index' => isset($data['UslugaTest_Index']) ? $data['UslugaTest_Index'] : null,
			'UslugaTest_ResultApproved' => $data['UslugaTest_ResultApproved'],
			'UslugaTest_CheckDT' => isset($data['UslugaTest_CheckDT']) ? $data['UslugaTest_CheckDT'] : null,
			'UslugaTest_Comment' => $data['UslugaTest_Comment'],
			'LabTest_id' => isset($data['LabTest_id']) ? $data['LabTest_id'] : null
		);
		$dbresponse = $this->db->query($query, $params);
		//echo getDebugSql($query, $params);
		if (is_object($dbresponse)) {
			$result = $dbresponse->result('array');
			$save_ok = self::save_ok($result);
			if (!$save_ok){
				throw new Exception(
					'При создании факта оказания услуги произошла ошибка: '.
					$result[0]['Error_Code'].
					$result[0]['Error_Msg']
				);
			} else {
				if (isset($result[0])) {
					collectEditedData($ins_upd, 'UslugaTest', $result[0]['UslugaTest_id']);
					return $result[0]['UslugaTest_id'];
				}
			}
		} else {
			throw new Exception(
				'При создании факта оказания услуги произошла ошибка: '.var_export($this->db->_error_message(), true)
			);
		}
		return true;
	}

	/**
	 * Установка комментария
	 */
	function setComment($EvnLabSample_id, $EvnLabSample_Comment) {
		$res = $this->db->query(
			"UPDATE EvnLabSample SET EvnLabSample_Comment = :EvnLabSample_Comment WHERE Evn_id = :EvnLabSample_id",
			array(
				'EvnLabSample_id' => $EvnLabSample_id,
				'EvnLabSample_Comment'=> $EvnLabSample_Comment
			)
		);
		if ($res) {
			collectEditedData('upd', 'EvnLabSample', $EvnLabSample_id);
		}
		return $res;
	}

	/**
	 * Получение списка проб-кандидатов
	 */
	function loadListForCandiPicker($data) {
		$p = array('AnalyzerWorksheet_id'=>$data['AnalyzerWorksheet_id']);
		$where = array();
		if (isset($data['EvnLabRequest_BarCode'])) {
			$where[] = 'Right(elr.EvnLabRequest_BarCode,12) like :EvnLabRequest_BarCode || \'%\'';
			$p['EvnLabRequest_BarCode'] = $data['EvnLabRequest_BarCode'];
		}
		if (isset($data['EvnLabSample_Num'])) {
			$where[] = 'ves.EvnLabSample_Num like \'%\' || :EvnLabSample_Num || \'%\'';
			$p['EvnLabSample_Num'] = $data['EvnLabSample_Num'];
		}
		if (!empty($data['MedService_id'])) {
			$where[] = 'elr.MedService_id = :MedService_id';
			$p['MedService_id'] = $data['MedService_id'];
		}

		$where = implode(' and ', $where);
		if (strlen($where)) {
			$where = $where.' and ';
		}

		$p['Analyzer_id'] = $this->getFirstResultFromQuery("
			select
				Analyzer_id as \"Analyzer_id\"
			from
				lis.v_AnalyzerWorksheet
			where
				AnalyzerWorksheet_id = :AnalyzerWorksheet_id
			", $p);
		if (empty($p['Analyzer_id'])) {
			$p['Analyzer_id'] = null;
		}

		$q = "
			SELECT
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_Num as \"EvnLabSample_Num\",
				RefMaterial_Name as \"RefMaterial_Name\",
				EvnLabSample_Comment as \"EvnLabSample_Comment\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				elr.EvnLabRequest_BarCode as \"EvnLabRequest_BarCode\",
				to_char (ves.EvnLabSample_setDT, 'dd.mm.yyyy') as \"EvnLabSample_setDT\"
			FROM
				v_EvnLabSample ves
				INNER JOIN dbo.v_EvnLabRequest elr on elr.EvnLabRequest_id = ves.EvnLabRequest_id
				INNER JOIN dbo.v_UslugaComplex uc on uc.UslugaComplex_id = elr.UslugaComplex_id
				INNER JOIN dbo.v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				LEFT JOIN dbo.v_RefSample RefSample_id_ref on RefSample_id_ref.RefSample_id = ves.RefSample_id
				LEFT JOIN dbo.v_RefMaterial rm on rm.RefMaterial_id = RefSample_id_ref.RefMaterial_id
				left join lateral(
					select
						m_child.UslugaComplexMedService_id
					from
						v_UslugaComplexMedService m -- комплексная услуга
						inner join v_UslugaComplexMedService m_child on m.UslugaComplexMedService_id = m_child.UslugaComplexMedService_pid
					where
						m.MedService_id = elr.MedService_id
						and m.UslugaComplex_id = elr.UslugaComplex_id
						and m_child.RefSample_id = ves.RefSample_id
						and not exists(select AnalyzerTest_id from lis.v_AnalyzerTest where UslugaComplex_id = m_child.UslugaComplex_id and Analyzer_id = :Analyzer_id limit 1)
					limit 1
                ) UC_SOST_NOTINANALYZER on true
			WHERE
				{$where}
				uc.UslugaComplex_id IN (
					SELECT
						at.UslugaComplex_id
					FROM
						lis.v_AnalyzerTest at
					WHERE
						at.Analyzer_id = :Analyzer_id
				)
				AND UC_SOST_NOTINANALYZER.UslugaComplexMedService_id IS NULL
				AND NOT EXISTS (
					SELECT
						*
					FROM
						lis.v_AnalyzerWorksheetEvnLabSample ws
						INNER JOIN lis.v_AnalyzerWorksheet w ON ws.AnalyzerWorksheet_id = w.AnalyzerWorksheet_id
					WHERE
						ws.EvnLabSample_id = ves.EvnLabSample_id
						AND w.AnalyzerWorksheetStatusType_id IN ( 1, 2 )
                    limit 1
				)
				AND NOT EXISTS (
					SELECT
						*
					FROM
						v_UslugaTest ut
					where
						euin.EvnLabSample_id = ves.EvnLabSample_id
						and euin.UslugaTest_ResultValue is not null
						and euin.UslugaTest_ResultValue <> ''
                    limit 1
				)
		";

		//echo getDebugSQL($q, $p);
		$result = $this->db->query($q, $p);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка пробы-кандидата
	 */
	function loadBarCode($data) {

		$queryParams = array('AnalyzerWorksheet_id'=>$data['AnalyzerWorksheet_id']);

		$filter = "(1=1)";

		if (isset($data['EvnLabSample_Num'])) {
			$filter .= 'and ves.EvnLabSample_Num = :EvnLabSample_Num ';
			$queryParams['EvnLabSample_Num'] = $data['EvnLabSample_Num'];
		}

		/*if (isset($data['EvnLabSample_Num'])) {
			$where[] = 'ves.EvnLabSample_Num like :EvnLabSample_Num + \'%\'';
			$p['EvnLabSample_Num'] = $data['EvnLabSample_Num'];
		}*/
		//TODO непонятно что за таблица ucc
		$query =
        "
		SELECT
            EvnLabSample_Num as \"EvnLabSample_Num\",
            EvnLabSample_id as \"EvnLabSample_id\"
        FROM
            v_EvnLabSample ves
        WHERE {$filter} and ucc.uslugacomplex_id IN (
            SELECT
                uc.UslugaComplex_id
            FROM
                v_UslugaComplex uc
            WHERE
                uc.UslugaComplex_Code IN (
                SELECT
                    vuc.UslugaComplex_Code
                FROM
                    lis.v_AnalyzerWorksheet w
                    LEFT JOIN lis.v_AnalyzerWorksheetType wt ON w.AnalyzerWorksheetType_id = wt.AnalyzerWorksheetType_id
                    LEFT JOIN lis.v_AnalyzerTestWorksheetType twt ON wt.AnalyzerWorksheetType_id = twt.AnalyzerWorksheetType_id
                    INNER JOIN lis.v_AnalyzerTestUslugaComplex tu ON twt.AnalyzerTest_id = tu.AnalyzerTest_id
                    INNER JOIN dbo.v_UslugaComplex vuc ON tu.UslugaComplex_id = vuc.UslugaComplex_id
                WHERE
                    w.AnalyzerWorksheet_id = 1 )
            )
                    AND NOT EXISTS ( SELECT
                                1
                             FROM
                                lis.v_AnalyzerWorksheetEvnLabSample ws
                                INNER JOIN lis.v_AnalyzerWorksheet w ON ws.AnalyzerWorksheet_id = w.AnalyzerWorksheet_id
                             WHERE
                                ws.EvnLabSample_id = ves.EvnLabSample_id
                                AND w.AnalyzerWorksheetStatusType_id IN ( 1, 2 )
                    )
        ";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Удаление
	 */
	function delete($data = array()) {
		$EvnLabSample_ids = array();
		if (!empty($data['EvnLabSample_id'])) {
			$EvnLabSample_ids[] = $data['EvnLabSample_id'];
		} elseif (!empty($data['EvnLabSample_ids'])) {
			$EvnLabSample_ids = json_decode($data['EvnLabSample_ids'], true);
		}

		foreach($EvnLabSample_ids as $EvnLabSample_id) {
			$data['EvnLabSample_id'] = $EvnLabSample_id;

			// 1. получаем идентификатор заявки
			$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
				select
					EvnLabRequest_id as \"EvnLabRequest_id\"
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
				limit 1
				", array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));

			$data['LabSampleStatus_id'] = $this->getFirstResultFromQuery("
				select
					LabSampleStatus_id as \"LabSampleStatus_id\"
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
				limit 1
				", array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));

			// Нельзя удалять одобренные пробы
			if (empty($data['LabSampleStatus_id']) || !in_array($data['LabSampleStatus_id'], array(4,6))) {
				// 2.1 удаляем все результаты по пробе из UslugaTest
				$query = "
					select
						UslugaTest_id as \"UslugaTest_id\"
					from
						v_UslugaTest
					where
						EvnLabSample_id = :EvnLabSample_id
				";

				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					foreach($resp as $respone) {
						$query = "
							select
                    			error_code as \"Error_Code\",
                    			error_message as \"Error_Msg\"
                   			from p_uslugatest_del(
								UslugaTest_id := :UslugaTest_id,
								pmUser_id := :pmUser_id
							)
						";

						$result = $this->db->query($query, array(
							'UslugaTest_id' => $respone['UslugaTest_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				// 2.2 удаляем пробу
				$q = "
					select
                    	error_code as \"Error_Code\",
                    	error_message as \"Error_Msg\"
                    from p_EvnLabSample_del(
                    	EvnLabSample_id := :EvnLabSample_id,
                        pmUser_id := :pmUser_id
                    )
				";
				$r = $this->db->query($q, $data);

				// 3. рекэшируем статус заявки
				if (!empty($data['EvnLabRequest_id'])) {
					// кэшируем статус заявки
					$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
					$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем количество тестов
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем статус проб в заявке
					$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		return array('Error_Msg' => '');
	}


	/**
	 * Проверка находится ли тест пробы в лунке планшета
	 * @param $EvnLabSample_id
	 * @return bool|float|int|string
	 * @throws Exception
	 */
	function isLabSampleTestInHole($EvnLabSample_id) {
		$params = [];
		$params['EvnLabSample_id'] = $EvnLabSample_id;
		$query = "
			select
				T.Tablet_id as \"Tablet_id\"
			from v_EvnLabSample ELS
				inner join v_UslugaTest UT on UT.EvnLabSample_id = ELS.EvnLabSample_id
				inner join v_Hole H on UT.UslugaTest_id = H.UslugaTest_id
				inner join v_Tablet T on T.Tablet_id = H.Tablet_id
			where ELS.EvnLabSample_id = :EvnLabSample_id
				and T.Tablet_defectDT is null
		";
		$result = $this->getFirstResultFromQuery($query, $params, true);
		if($result === false) {
			throw new Exception('Ошибка при выполнении запроса');
		}
		return $result;
	}

	/**
	 * Отмена взятия пробы
	 */
	function cancel($data = array()) {
		$EvnLabSample_ids = array();
		if (!empty($data['EvnLabSample_id'])) {
			$EvnLabSample_ids[] = $data['EvnLabSample_id'];
		} elseif (!empty($data['EvnLabSample_ids'])) {
			$EvnLabSample_ids = json_decode($data['EvnLabSample_ids'], true);
		}

		foreach($EvnLabSample_ids as $EvnLabSample_id) {
			$data['EvnLabSample_id'] = $EvnLabSample_id;

			// 1. получаем идентификатор заявки
			$data['EvnLabRequest_id'] = $this->getFirstResultFromQuery("
				select
					EvnLabRequest_id as \"EvnLabRequest_id\"
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
				limit 1
				", array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));

			$data['LabSampleStatus_id'] = $this->getFirstResultFromQuery("
				select
					LabSampleStatus_id as \"LabSampleStatus_id\"
				from
					v_EvnLabSample
				where
					EvnLabSample_id = :EvnLabSample_id
				limit 1
				", array(
				'EvnLabSample_id' => $data['EvnLabSample_id']
			));

			// Нельзя отменять пробы с тестами в лунке планшета
			if($this->isLabSampleTestInHole($EvnLabSample_id)) {
				throw new Exception('Тест пробы находится в планшете');
			}

			// Нельзя отменять одобренные пробы
			if (empty($data['LabSampleStatus_id']) || !in_array($data['LabSampleStatus_id'], array(4,6))) {
				// новая хранимка для отмены взятия пробы, refs https://redmine.swan.perm.ru/issues/117750
				$query = "
					select
                    	error_code as \"Error_Code\",
                    	error_message as \"Error_Msg\"
                    from p_uslugatest_delall(
                    	EvnLabSample_id := :EvnLabSample_id,
                    	pmUser_id := :pmUser_id
                    )
				";

				$result = $this->db->query($query, array(
					'EvnLabSample_id' => $data['EvnLabSample_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				$this->ReCacheLabSampleStatus(array(
					'EvnLabSample_id' => $data['EvnLabSample_id']
				));

				// 3. рекэшируем статус паявки
				if (!empty($data['EvnLabRequest_id'])) {
					// кэшируем статус заявки
					$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
					$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем количество тестов
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем статус проб в заявке
					$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
						'EvnLabRequest_id' => $data['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		return array('Error_Msg' => '');
	}


	/**
	 * Получение уникального номера лаборатории для генерации номера направления
	 * @param $data
	 * @return bool
	 */
	function getMedServiceCode($data) {
		$query = "
   			select
   			    MedService_Code as \"MedService_Code\"
   			from
   			    v_MedService
   			where
   			    MedService_id = :MedService_id
   		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение номеров проб с прошлых дней, которые на данный момент ещё не закрыты
	 * @param $data
	 * @return bool
	 */
	function getInWorkSamples($data) {

		$queryParams = array(
			'MedService_id' => $data['MedService_id']
		);

		$query = "
   			select
				count(EvnLabSample_Num) as \"count\"
   			from
		        v_EvnLabSample ELS
	        where
		        LabSampleStatus_id in (1,2,3,7)
		        and length(ELS.EvnLabSample_Num) = 12
		        and ELS.MedService_id = :MedService_id
   		";
		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение количества просроченных проб
	 * @param $data
	 * @return bool
	 */
	function getOverdueSamples($data) {

		$queryParams = array(
			'MedService_id' => $data['MedService_id']
		);

		$query = "
   			select
				count(EvnLabSample_Num) as \"count\"
   			from
		        v_EvnLabSample
	        where
		        LabSampleStatus_id in (1,2,3,7)
		        and  EvnLabSample_insDT <= (dbo.tzGetDate() - interval '30 days')
		        and length(EvnLabSample_Num) = 12
				and MedService_id = :MedService_id
   		";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	function getPersonBySample($data) {
		$queryParams = array(
			'EvnLabSample_id' => $data['EvnLabSample_id']
		);

		$query = "
			select
				PS.Person_SurName || ' ' || PS.Person_FirName || coalesce(' ' || PS.Person_SecName, '') as \"Person_Fio\",
				PS.Sex_id as \"Sex_id\",
				date_part('year', age(PS.Person_BirthDay)) as \"Person_Age\"
			from
				v_EvnLabSample ELS
				inner join dbo.v_Person_all PS on PS.PersonEvn_id = ELS.PersonEvn_id
			where
				ELS.EvnLabSample_id = :EvnLabSample_id
			limit 1
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение истории исследований
	 * @param array $data
	 * @return array|false
	 */
	function loadResearchHistory($data) {
		$queryParams = array(
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'MinDate' => $data['MinDate'],
			'MaxDate' => $data['MaxDate'],
			'Server_id' => $data['Server_id']
		);

		$codes = $data['Codes'];

		$where = "";
		if ($data['MinDate'] != "" && $data['MaxDate'] != "") {
			$where = "and ut.UslugaTest_setDT BETWEEN cast(:MinDate as datetime) AND cast(:MaxDate as datetime)";
		}
		$query = "
			with vars as (select
				(select Person_id from v_EvnLabSample where EvnLabSample_id = :EvnLabSample_id limit 1) as Person_id
			)
			select
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				cast(ut.UslugaTest_ResultLower as varchar) || ' - ' || cast(ut.UslugaTest_ResultUpper as varchar) as \"UslugaTest_RefValues\",
				to_char(ut.UslugaTest_CheckDT, 'DD.MM.YYYY') as \"UslugaTest_CheckDT\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				(uc.UslugaComplex_Code || ' ' || uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				(select count(*) from v_UslugaTestHistory where UslugaTest_id = ut.UslugaTest_id) as \"UslugaTestHistory_Count\"
			from v_UslugaTest ut
			inner join v_Lpu lpu on lpu.Lpu_id = ut.Lpu_id
			inner join v_UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
			inner join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
			where
				els.Person_id = (select Person_id from vars)
				and ut.UslugaTest_CheckDT is not NULL
				and uc.UslugaComplex_id in ({$codes})
				{$where}
			order by ut.UslugaComplex_id ASC, ut.UslugaTest_CheckDT DESC
		";

		$res = $this->db->query($query, $queryParams);
		$res = $res->result('array');

		if (count($res) === 0) {
			return ['Error_Msg' => 'Отсутствуют данные для отображения'];
		}
		return $res;
	}

	function loadLabResearchResultHistory($data) {
		$queryParams = [
			'UslugaTest_id' => $data['UslugaTest_id']
		];

		$query = "
			select
				uth.UslugaTestHistory_id as \"UslugaTestHistory_id\",
				(uc.UslugaComplex_Code || ' ' || uc.UslugaComplex_Name) as \"UslugaComplex_Name\",
				uth.UslugaTest_id as \"UslugaTest_id\",
				uth.UslugaTestHistory_Result as \"UslugaTestHistory_Result\",
				uth.UslugaTestHistory_Comment as \"UslugaTestHistory_Comment\",
				to_char(uth.UslugaTestHistory_CheckDT, 'dd.mm.yyyy hh24:mi:ss') as \"UslugaTestHistory_CheckDT\",
				mp.Person_Fio as \"Person_Fio\"
			from v_UslugaTestHistory uth
				left join v_pmUserCache pmu on pmu.PMUser_id = uth.pmUser_insID
				left join v_MedPersonal mp on mp.MedPersonal_id = pmu.MedPersonal_id and mp.Lpu_id = pmu.Lpu_id
				inner join v_UslugaTest ut on ut.UslugaTest_id = uth.UslugaTest_id
				inner join UslugaComplex uc on uc.UslugaComplex_id = ut.UslugaComplex_id
			where uth.UslugaTest_id = :UslugaTest_id
			order by uth.UslugaTestHistory_CheckDT ASC
		";

		$res = $this->db->query($query, $queryParams);
		$res = $res->result('array');

		if (count($res) === 0) {
			return ['Error_Msg' => 'Отсутствуют данные для отображения'];
		}
		return $res;
	}

	function loadPathologySamples($data) {
		$query = "
			select
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabSample_IsOutNorm as \"EvnLabSample_IsOutNorm\"
			from v_EvnLabSample
			where EvnLabSample_id in({$data['EvnLabSample_id']})
		";

		$res = $this->db->query($query);
		$res = $res->result('array');
		return $res;
	}

	/**
	 * Проверка 12-ти значного списка пробы на уникальность
	 * @param $data
	 * @return bool
	 */
	function checkEvnLabSampleUnique($data) {
		// print 1;
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabSample_Num' => $data['EvnLabSample_Num'],
			'MedService_id' => $data['MedService_id']
		);

		$query = "
   			select
				count(EvnLabSample_Num) as \"count\"
   			from
		        v_EvnLabSample
	        where
	        	LabSampleStatus_id in (1,2,3,7)
	            and length(EvnLabSample_Num) = 12
		        and EvnLabSample_Num = :EvnLabSample_Num
		        and Lpu_id = :Lpu_id
   		";

		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp =  $result->result('array');
			if ($resp[0]['count'] > 0) {
				return array('Error_Msg' => 'Проба с суточным номером '.substr($data['EvnLabSample_Num'], -4).' уже существует');
			} else {
				return ['success' => true];
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка 12-ти значного штрих-кода пробы на уникальность
	 * @param $data
	 * @return bool
	 */
	function checkEvnLabSampleBarCodeUnique($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabSample_BarCode' => $data['EvnLabSample_BarCode']
		);

		$filter = "";
		if (!empty($data['EvnLabSample_id'])) {
			$queryParams['EvnLabSample_id'] = $data['EvnLabSample_id'];
			$filter .= "and EvnLabSample_id <> :EvnLabSample_id";
		}

		$query = "
   			select
   				EvnLabSample_id as \"EvnLabSample_id\"
   			from
		        v_EvnLabSample
	        where
	            EvnLabSample_BarCode = :EvnLabSample_BarCode
		        and Lpu_id = :Lpu_id
		        {$filter}
		    limit 1
   		";

		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnLabSample_id'])) {
				return ['Error_Msg' => 'Проба с штрих-кодом ' . $data['EvnLabSample_BarCode'] . ' уже существует'];
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Проверка 12-ти значного номера пробы на уникальность
	 * @param $data
	 * @return bool
	 */
	function checkEvnLabSampleNumUnique($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabSample_Num' => $data['EvnLabSample_Num']
		);

		$filter = "";
		if (!empty($data['EvnLabSample_id'])) {
			$queryParams['EvnLabSample_id'] = $data['EvnLabSample_id'];
			$filter .= " and EvnLabSample_id <> :EvnLabSample_id";
		}

		$query = "
   			select
				count(EvnLabSample_Num) as \"count\"
   			from
		        v_EvnLabSample
	        where
	            EvnLabSample_Num = :EvnLabSample_Num
		        and Lpu_id = :Lpu_id
		        {$filter}
   		";

		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if ($resp[0]['count'] > 0) {
				return ['Error_Msg' => 'Проба с номером ' . mb_substr($data['EvnLabSample_Num'], -4) . ' уже существует'];
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение вида оплаты по-умолчанию
	 */
	function getPayTypeSysNick() {
		switch ( $this->getRegionNick() ) {
			case 'kz': $PayType_SysNick = 'Resp'; break;
			default: $PayType_SysNick = 'oms'; break;
		}
		return $PayType_SysNick;
	}

    /**
     * получение параметров биоматериала и флага отдельной пробы
     * @param $data = [
     *      EvnLabSample_id - id пробы
     *      EvnLabRequest_id - id заявки
     *      MedService_id - id службы
     * ]
     * @return array
     */
    function getSampleParameters( $param_data ) {

        if( empty($param_data['EvnLabRequest_id']) || empty($param_data['MedService_id']) ){
            return false;
        }

        $resp = $this->queryResult("
			select distinct
			    ELRUC.EvnLabSample_id as \"EvnLabSample_id\",
				ELRUC.UslugaComplex_id as \"UslugaComplex_id\",
                ELRUC.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				rs.RefMaterial_id as \"RefMaterial_id\",
				rs.UslugaComplexMedService_IsSeparateSample as \"UslugaComplexMedService_IsSeparateSample\",
				rs.RefSample_id as \"RefSample_id\"
			from
				v_EvnLabRequestUslugaComplex ELRUC
				left join lateral (
					select
						rs.RefMaterial_id,
						rs.RefSample_id,
						ucms.UslugaComplexMedService_IsSeparateSample
					from
						v_UslugaComplexMedService ucms
                        inner join v_RefSample rs on rs.RefSample_id = ucms.RefSample_id
					where
						ucms.UslugaComplex_id = ELRUC.UslugaComplex_id
						and ucms.MedService_id = :MedService_id
					limit 1
				) rs on true
			where
				ELRUC.EvnLabRequest_id = :EvnLabRequest_id
		",
            [
                'EvnLabRequest_id' => $param_data['EvnLabRequest_id'],
                'MedService_id' => $param_data['MedService_id']
            ]
        );

        return $resp;
    }

    /**
     * получение биоматериал по исследоваию
     * @param $UslugaComplexMedService_id - id комплексного исследования \ теста
     * @return array
     */
    function getBioResearch( $UslugaComplexMedService_id ) {

        if( empty($UslugaComplexMedService_id) ){
            return false;
        }

        $resp = $this->queryResult("
				select distinct
					ucms.UslugaComplex_id as \"UslugaComplex_id\",
					rs.RefMaterial_id as \"RefMaterial_id\",
					ucms.UslugaComplexMedService_IsSeparateSample as \"UslugaComplexMedService_IsSeparateSample\",
					rs.RefSample_id as \"RefSample_id\"
				from
					v_UslugaComplexMedService ucms
					left join v_RefSample rs on rs.RefSample_id = ucms.RefSample_id
				where
					ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
			", array(
            'UslugaComplexMedService_id' => $UslugaComplexMedService_id
        ));
        if(!empty($resp[0])){
            return $resp[0];
        }
        return false;
    }

    /**
     * получение тестов в комплексной услуге
     * @param $UslugaComplexMedService_id - id комплексного исследования \ теста
     * @return array
     */
    function getComplexResearchTests( $UslugaComplexMedService_id ) {

        if( empty($UslugaComplexMedService_id) ){
            return false;
        }

        $resp = $this->queryResult("
				with myvars as (
					select dbo.tzgetdate() as curdate
				)

				select distinct
					ucms_usluga.UslugaComplex_id as \"UslugaComplex_id\",
					ucms_usluga_parent.UslugaComplex_id as \"parentUslugaComplex_id\",
					ucms_usluga.UslugaComplexMedService_IsSeparateSample as \"UslugaComplexMedService_IsSeparateSample\",
					rs.RefMaterial_id as \"RefMaterial_id\"
				from
					v_UslugaComplexMedService ucms_usluga_parent
					left join v_UslugaComplexMedService ucms_usluga on ucms_usluga.UslugaComplexMedService_pid = ucms_usluga_parent.UslugaComplexMedService_id
					inner join lis.v_AnalyzerTest at_child on at_child.UslugaComplexMedService_id = COALESCE(ucms_usluga.UslugaComplexMedService_id, ucms_usluga_parent.UslugaComplexMedService_id)
					left join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
					inner join lis.v_Analyzer a on a.Analyzer_id = COALESCE(at.Analyzer_id, at_child.Analyzer_id)
					left join v_UslugaComplex uctest on uctest.UslugaComplex_id = at_child.UslugaComplex_id
					left join v_RefSample rs on rs.RefSample_id = ucms_usluga.RefSample_id
				where
					ucms_usluga_parent.UslugaComplexMedService_id = :UslugaComplexMedService_id
					and COALESCE(at.UslugaComplexMedService_id, 0) = COALESCE(ucms_usluga.UslugaComplexMedService_pid, 0)
					and COALESCE(at_child.AnalyzerTest_IsNotActive, 1) = 1
					and COALESCE(at.AnalyzerTest_IsNotActive, 1) = 1
					and COALESCE(a.Analyzer_IsNotActive, 1) = 1
					and (at_child.AnalyzerTest_endDT >= (select curdate from myvars) or at_child.AnalyzerTest_endDT is null)
					and (uctest.UslugaComplex_endDT >= (select curdate from myvars) or uctest.UslugaComplex_endDT is null)
			", array(
            'UslugaComplexMedService_id' => $UslugaComplexMedService_id
        ));

        return $resp;
    }

    /**
     * получение услуги по UslugaComplexMedService_id
     * @param $UslugaComplexMedService_id - id комплексного исследования \ теста
     * @return array
     */
    function getUslugaComplexByUslugaComplexMedService( $UslugaComplexMedService_id ) {

        if( empty($UslugaComplexMedService_id) ){
            return false;
        }

        // получаем услугу и службу
        $resp = $this->queryResult("
				select
					MedService_id as \"MedService_id\",
					UslugaComplex_id as \"UslugaComplex_id\"
				from
					v_UslugaComplexMedService
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
			", array(
            'UslugaComplexMedService_id' => $UslugaComplexMedService_id
        ));

        return $resp;
    }

	/**
	 * Сохранение исследования
	 */
    function saveLabSampleResearches($data, $checked_tests = []) {
        // массив проб
        $labSamples[ $data['EvnLabSample_id'] ] = [
            'RefMaterials' => null,
            'empty' => true,
            'isSeparate' => false,
        ];
        $newLabSamples = [];

        // функция для проверки и выдачи id пробы по тесту или исследованию
        $checkAvailableSamples = function ( $test, $is_root = false ) use ( &$labSamples, $data) {
            foreach ($labSamples as $labSampleID => $sample){
                // если есть в списке проба с таким биоматериалом и не стоит флага "отдельная проба", берем из списка
                if($sample['isSeparate'] == true){
                    continue;
                }
                if(
                    // если подходит биоматериал и не стоит флага отдельная проба
                    $sample['RefMaterials'] == $test['RefMaterial_id'] &&
                    $test['UslugaComplexMedService_IsSeparateSample'] != 2
                    ||
                    // иначе берем пустую пробу
                    $sample['RefMaterials'] == null &&
                    $sample['isSeparate'] == false &&
                    $sample['empty'] == true
                ) {
                    if(!$is_root){
                        $labSamples[$labSampleID]['RefMaterials'] = $test['RefMaterial_id'];
                        $labSamples[$labSampleID]['isSeparate'] = ($test['UslugaComplexMedService_IsSeparateSample']==2? true: false);
                        $labSamples[$labSampleID]['empty'] = false;
                    }
                    return $labSampleID;
                }
            }

            // иначе создаем пробу для отдельного теста или нового биоматериала
            $resp = $this->saveLabSample(array(
                'EvnLabRequest_id' => $data['EvnLabRequest_id'],
                'MedService_id' => $data['MedService_id'],
                'RefSample_id'=> $data['RefSample_id'] ?? null,
                'Lpu_id' => $data['Lpu_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($resp[0]['EvnLabSample_id'])) {
                $newSabSampleId = $resp[0]['EvnLabSample_id'];
                // и заполняем массив проб новыми данными
                if(!$is_root) {
                    $labSamples[$newSabSampleId] = [
                        'created' => true,
                        'empty' => false,
                        'RefMaterials' => $test['RefMaterial_id'],
                        'isSeparate' => ($test['UslugaComplexMedService_IsSeparateSample'] == 2 ? true : false),
                    ];
                }else{
                    $labSamples[ $newSabSampleId ] = [
                        'created' => true,
                        'empty' => true,
                        'RefMaterials' => null,
                        'isSeparate' => false
                    ];
                }
                return $newSabSampleId;
            }
            return false;
        };

        // получаем данные текущей заявки
        $lrdata = $this->getDataFromEvnLabRequest(array(
            'EvnLabSample_id' => $data['EvnLabSample_id']
        ));
        $lrdata['pmUser_id'] = $data['pmUser_id'];
        $lrdata['EvnLabSample_id'] = $data['EvnLabSample_id'];

        // получаем данные текущей пробы
        $resp = $this->getSampleParameters([
            'EvnLabRequest_id' => $lrdata['EvnLabRequest_id'],
            'MedService_id' => $lrdata['MedService_id']
        ]);

        // проверяем что в комплексной услуге установлен флаг "отдельная проба"
        foreach($resp as $response) {
            $labSamples[ $response['EvnLabSample_id'] ] = [
                'RefMaterials' => $response['RefMaterial_id'],
                'empty' => empty($response['EvnUslugaPar_id']),
                'isSeparate' => ($response['UslugaComplexMedService_IsSeparateSample'] == 2),
            ];
        }

        if (is_string($data['researches'])) {
            $data['researches'] = json_decode($data['researches'], true);
        }

        foreach($data['researches'] as $UslugaComplexMedService_id) {
            $lrdata['EvnLabSample_id'] = null;
            $RefMats = array();

            // получаем данные и биоматериал по заказываемому исследованию (корневая услуга)
            $root_usluga = $this->getBioResearch( $UslugaComplexMedService_id );

            if(!$root_usluga){
                throw new Exception('Ошибка сохранения услуги'.(!empty($resp[0]['Error_Msg'])?': '.$resp[0]['Error_Msg']:''));
            }

            // получаем услугу и службу
            $uslugaData = $this->getUslugaComplexByUslugaComplexMedService($UslugaComplexMedService_id);
            if (!empty($uslugaData[0]['UslugaComplex_id'])) {
                $lrdata['UslugaComplex_id'] = $uslugaData[0]['UslugaComplex_id'];
            }

            // сохраняем выполнение услуги
            $uslugaRoot = $this->saveEvnUslugaRoot($lrdata);

            if ($uslugaRoot['new'] == false) {
                continue; // пропускаем добавление исследования, раз исследование уже есть
            }
            $EvnUslugaPar_id = $uslugaRoot['EvnUslugaPar_id'];

            // Получаем тесты комплексной услуги
            $tests = $this->getComplexResearchTests( $UslugaComplexMedService_id );

            foreach($tests as $test) {
                // для направления из ЭМК, добавляем только выбранные тесты
                if (!empty($checked_tests) && $test['UslugaComplex_id'] && !in_array($test['UslugaComplex_id'], $checked_tests) ) {
                    continue;
                }

                // у некоторых комплексных услуг нет ни одного теста, в этом случае берем корневой тест
                if(!$test['UslugaComplex_id'] && $test['parentUslugaComplex_id']){
                    $test = $root_usluga;
                }
                $test['UslugaTest_pid'] = $EvnUslugaPar_id;

                // получаем ID пробы для теста
                $labSampleId = $checkAvailableSamples( $test );

                // сохраняем тест
                $this->prescrTest(array(
                    'EvnLabRequest_id' => $lrdata['EvnLabRequest_id'],
                    'EvnLabSample_id' => $labSampleId,
                    'tests' => [$test], // ожидает массив тестов
                    'Lpu_id' => $data['Lpu_id'],
                    'session' => $data['session'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            }
        }

        foreach($labSamples as $key => $value) {
            // возвращаем все созданные пробы
            if (!empty($value['created'])) {
                $newLabSamples[] = $key;
            }
        }

        return array('Error_Msg' => '', 'newLabSamples' => $newLabSamples, 'success' => true);
    }

	/**
	 * Получение пробы
	 * @param $data
	 * @return bool
	 */
	function getEvnLabSample($data) {
		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		);
		$query = "
   			select
   			    EvnLabSample_id as \"EvnLabSample_id\"
   			from
		        v_EvnUslugaPar
	        where
	            EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
   		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * получить перечень номеров без привязки к пробе
	 */
	function getNewListEvnLabSampleNum($data){
		$startNum = 5001;
		$limit = 100;
		$arrayNum = array();

		$limit = ($data['quantity'] > $limit) ? $limit : $data['quantity'];

		$n = 1;
		while($n <= $limit)
		{
			$arrayNum[] = $this->getNewLabSampleNum($data, $startNum);
			$n++;
		}
		return array(
			'success' => true,
			'barcodesNums' => implode(",", $arrayNum)
		);
	}

	/**
	 * Получение списка всех тестов для анализа за выбранный день в выбранной лаборатории
	 * (для столбцов формы 250у)
	 */
	function getTestListForm250($data) {

		$filterUslugaTest = '';
		if (!empty($data['UslugaComplex_id'])) {

			$uslugaContent = $this->getUslugaComplexContent(array(
				'UslugaComplex_pid' => $data['UslugaComplex_id'],
				'MedService_id' => $data['MedService_id']
			));

			if (is_array($uslugaContent)) {
				$filterUslugaTest = ' WHERE usl.UslugaComplex_id IN ('.implode(',', $uslugaContent).')';
			} else {
				$filterUslugaTest = ' WHERE usl.UslugaComplex_id = :UslugaComplex_id ';
			}
		}


		$uslWhere = "els0.MedService_id IN (select MSL.MedService_lid from MedServiceLink MSL where msl.MedService_id = :MedService_id)";
		$msParam = "msl.MedService_lid";

		if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'lab') {
			$uslWhere = "els0.MedService_id = :MedService_id";
			$msParam = ":MedService_id";
		}

		$query = "
			with myvars as (
				select to_timestamp(:EvnLabSample_DelivDT, 'YYYY.MM.DD HH24:MI:SS') as paramDate
			)

			SELECT DISTINCT
				usl.UslugaComplex_id as \"UslugaComplex_id\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				ucgost.UslugaComplex_Name as \"UslugaComplex_Name_Gost\",
				coalesce(at.AnalyzerTest_SysNick, uc.UslugaComplex_Name, ucgost.UslugaComplex_Name,'NoName') as \"TestName\",
				COALESCE(at.AnalyzerTest_SortCode, '777') as \"AnalyzerTest_SortCode\",
				at2.UslugaComplex_id as \"UslugaComplex_pid\"
			FROM (
				SELECT DISTINCT ut.UslugaComplex_id
				FROM (
					SELECT EvnLabSample_id
					FROM v_EvnLabSample els0
					WHERE
						{$uslWhere}
						AND els0.EvnLabSample_setDT >= (select paramDate from myvars)
						AND els0.EvnLabSample_setDT < INTERVAL '1 day' + (select paramDate from myvars)
				) els
				LEFT JOIN dbo.v_UslugaTest ut ON ut.EvnLabSample_id = els.EvnLabSample_id
			) usl
			LEFT JOIN dbo.MedServiceLink msl ON msl.MedService_id = :MedService_id
			LEFT JOIN dbo.v_UslugaComplexMedService ucms ON ucms.MedService_id = {$msParam} AND ucms.UslugaComplex_id = usl.UslugaComplex_id
			LEFT JOIN lis.v_AnalyzerTest at ON at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			LEFT JOIN dbo.v_UslugaComplex uc ON uc.UslugaComplex_id = at.UslugaComplex_id
			LEFT JOIN dbo.v_UslugaComplex ucgost ON uc.UslugaComplex_2011id = ucgost.UslugaComplex_id
			LEFT JOIN lis.AnalyzerTest at2 ON at2.AnalyzerTest_id = at.AnalyzerTest_pid and ucms.MedService_id = {$msParam}
			" . $filterUslugaTest . "
			WHERE at.AnalyzerTest_pid is not null
			ORDER BY
				\"AnalyzerTest_SortCode\", usl.UslugaComplex_id, at.AnalyzerTest_SysNick DESC, uc.UslugaComplex_Name DESC, \"UslugaComplex_Name_Gost\" DESC
		";

		$result = $this->db->query($query, array(
			'MedService_id' => $data['MedService_id'],
			'EvnLabSample_DelivDT' => $data['Date'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение состава услуги
	 */
	function getUslugaComplexContent($data) {
		$query = "
			SELECT
				ucms.UslugaComplex_id as \"UslugaComplex_id\"
			FROM
				v_UslugaComplexMedService ucms
			WHERE
				ucms.UslugaComplexMedService_pid IN
				(
					SELECT ms.UslugaComplexMedService_id
					FROM v_UslugaComplexMedService ms
					WHERE ms.UslugaComplex_id = :UslugaComplex_id
						AND ms.MedService_id = :MedService_id
				)
		";

		$res = $this->db->query($query, array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_pid']
		));

		$resp_uslugaContent = array();
		if (is_object($res)) {
			$resp_uslugaContent = $res->result('array');
		}

		$result = '';
		$listUsluga = array();
		if (count($resp_uslugaContent) == 1) {

			$respUsluga = $resp_uslugaContent[0]['UslugaComplex_id'];
			if ($respUsluga != $data['UslugaComplex_pid']) {
				$listUsluga[] = $respUsluga;
				$listUsluga[] = $data['UslugaComplex_pid'];
				$result = $listUsluga;
			} else {
				$result = $data['UslugaComplex_pid'];
			}

		} elseif (count($resp_uslugaContent) > 1) {

			for($i = 0; $i < count($resp_uslugaContent); $i++) {
				$listUsluga[] = $resp_uslugaContent[$i]['UslugaComplex_id'];
			}
			$result = $listUsluga;

		} else {
			$result = $data['UslugaComplex_pid'];
		}
		return $result;
	}

	/**
	 * Получение списка взятых проб из лис с результатами
	 * (для формы 250у)
	 */
	function loadSampleListForm250($data) {

		$filterUslugaTest = '';
		if (!empty($data['UslugaComplex_id'])) {

			$uslugaContent = $this->getUslugaComplexContent(array(
				'UslugaComplex_pid' => $data['UslugaComplex_id'],
				'MedService_id' => $data['MedService_id']
			));

			if (is_array($uslugaContent)) {
				$filterUslugaTest = ' WHERE ut.UslugaComplex_id IN ('.implode(',', $uslugaContent).')';
			} else {
				$filterUslugaTest = ' WHERE ut.UslugaComplex_id = :UslugaComplex_id ';
			}
		}

		$elsWhere = ( !empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'lab' )
			? "els0.MedService_id = :MedService_id"
			: "els0.MedService_id IN (select MSL.MedService_lid from MedServiceLink MSL where msl.MedService_id = :MedService_id)";

		$query = "
			with mv as (
				select cast(:EvnLabSample_DelivDT as date) as dt
			)

			SELECT
				els.EvnLabSample_id as \"EvnLabSample_id\",
				els.EvnLabSample_DelivDT as \"EvnLabSample_DelivDT\",
				els.EvnLabSample_Num as \"EvnLabSample_Num\",
				els.EvnLabSample_Comment as \"EvnLabSample_Comment\",
				els.LpuSection_did as \"LpuSection_did\",
				els.Person_id as \"Person_id\",
				els.EvnLabRequest_id as \"EvnLabRequest_id\",
				els.Analyzer_id as \"Analyzer_id\",
				elr.EvnLabRequest_Ward as \"EvnLabRequest_Ward\",
				coalesce('Палата ' || elr.EvnLabRequest_Ward || '; ', '') || coalesce('Отделение ' || sec.LpuSection_Name, '') as \"LpuSection\",
				coalesce(elr.Diag_id, ev.Diag_id) as \"Diag_id\",
				coalesce(diag.Diag_Name, vizitdiag.Diag_Name) as \"DiagName\",
				coalesce(ps.Person_SurName || ' ', '') || coalesce(ps.Person_FirName || ' ', '') || coalesce(ps.Person_SecName, '') as \"PatientName\",
				ut.UslugaTest_id as \"UslugaTest_id\",
				CASE ut.UslugaTest_ResultApproved WHEN 2
					THEN ut.UslugaTest_ResultValue || coalesce(' ' || ut.UslugaTest_ResultUnit, '')
					ELSE '+'
				END as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				ut.UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				(
					select
						string_agg(CAST(uc.UslugaComplex_id as varchar), ',')
					from EvnUslugaPar eup
						inner join EvnUsluga eu on eu.Evn_id = eup.Evn_id
						inner join UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
					where EvnLabSample_id = ut.EvnLabSample_id
				) as \"UslugaComplexTest_pid\"
			FROM (
					SELECT
						els0.EvnLabSample_id,
						els0.EvnLabSample_DelivDT,
						els0.EvnLabSample_Num,
						els0.EvnLabSample_Comment,
						els0.LpuSection_did,
						els0.Person_id,
						els0.EvnLabRequest_id,
						els0.Analyzer_id
					FROM dbo.v_EvnLabSample els0
					WHERE
						{$elsWhere}
						AND els0.EvnLabSample_setDT >= (select dt from mv)
						AND els0.EvnLabSample_setDT < ((select dt from mv) + INTERVAL '1 day')
				) els
				LEFT JOIN dbo.v_EvnLabRequest elr ON els.EvnLabRequest_id = elr.EvnLabRequest_id
				LEFT JOIN dbo.v_UslugaTest ut ON ut.EvnLabSample_id = els.EvnLabSample_id
					AND ut.UslugaTest_pid IS NOT NULL
				LEFT JOIN dbo.v_PersonState ps ON ps.Person_id = els.Person_id
				LEFT JOIN dbo.v_Diag diag ON diag.Diag_id = elr.Diag_id
				LEFT JOIN dbo.v_EvnDirection_all da ON da.EvnDirection_id = elr.EvnDirection_id
				LEFT JOIN dbo.v_LpuSection sec ON sec.LpuSection_id = da.LpuSection_id
				LEFT JOIN dbo.v_EvnVizitPL ev ON ev.EvnVizitPL_id = da.EvnDirection_pid
				LEFT JOIN dbo.v_Diag vizitdiag ON vizitdiag.Diag_id = ev.Diag_id
			" . $filterUslugaTest;

		$result = $this->db->query($query, array(
			'MedService_id' => $data['MedService_id'],
			'EvnLabSample_DelivDT' => $data['Date'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение комментария к пробе
	 */
	function saveEvnLabSampleComment($data)
	{
		$query = "
			UPDATE
				EvnLabSample
			SET
				EvnLabSample_Comment = :EvnLabSample_Comment
			WHERE
				Evn_id = :EvnLabSample_id
		";

		$res = $this->db->query($query, $data);
		if ($res) {
			collectEditedData('upd', 'EvnLabSample', $data['EvnLabSample_id']);
		}

		return array('Error_Msg' => '', 'EvnLabSample_id' => $data['EvnLabSample_id']);
	}

	/**
	 * добавление результатов взятия пробы. Метод для API
	 */
	function createEvnLabSampleAPI($data){
		$EvnLabRequestUslugaComplexs = array();
		$EvnLabRequest_id = null;
		$EvnLabSample_id = null;
		$data['methodAPI'] = true;
		// ищем заявку на лабораторное исследование по EvnDirection_id
		$query = "
			SELECT
				d.EvnDirection_id as \"EvnDirection_id\",
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.MedService_id as \"MedService_id\"
			FROM
				v_EvnDirection_all d
				left join v_EvnStatus es on es.EvnStatus_id = d.EvnStatus_id
				left join v_EvnLabRequest elr on elr.EvnDirection_id = d.EvnDirection_id
			WHERE d.EvnDirection_id = :EvnDirection_id
			limit 1";
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['EvnDirection_id'])){
			return array('Error_Msg' => 'Не найдено направление на исследование');
		}

		//проверяем не отменили ли направление
		if (!empty($res['EvnStatus_SysNick']) && in_array($res['EvnStatus_SysNick'], array('Declined', 'Canceled'))) {
			$query = "
				SELECT
					es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
					elr.EvnLabRequest_id as \"EvnLabRequest_id\",
					elr.MedService_id as \"MedService_id\",
					ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
					ESC.EvnStatusCause_Name as \"EvnStatusCause_Name\",
					LCID.Lpu_Nick as \"LpuCID_Nick\"
				FROM
					v_EvnDirection_all d
					left join v_EvnStatus es on es.EvnStatus_id = d.EvnStatus_id
					left join v_EvnLabRequest elr on elr.EvnDirection_id = d.EvnDirection_id
					left join v_Lpu LCID on d.Lpu_cid = LCID.Lpu_id
					left join v_EvnStatusHistory ESH on ESH.Evn_id = d.EvnDirection_id
					left join v_EvnStatusCause ESC on ESC.EvnStatusCause_id = ESH.EvnStatusCause_id
				WHERE
					d.EvnDirection_id = :EvnDirection_id
					AND ESH.EvnStatusCause_id is not null
				limit 1
			";
			$resCancel = $this->getFirstRowFromQuery($query, $data);
			$msg = "Направление отменено";
			if(!empty($resCancel['LpuCID_Nick'])) $msg .= " МО ".$resCancel['LpuCID_Nick'];
			if(!empty($resCancel['LpuCID_Nick'])) $msg .= " по причине: ".$resCancel['EvnStatusCause_Name'];
			if(!empty($resCancel['EvnStatusHistory_Cause'])) $msg .= ", комментарий: ".$resCancel['EvnStatusHistory_Cause'];
			return array('Error_Msg' => $msg);
		}
		if(empty($res['EvnLabRequest_id'])){
			return array('Error_Msg' => 'Не найдена заявка на исследование');
		}
		$EvnLabRequest_id = $res['EvnLabRequest_id'];

		// сохраняем пробу в которую потом запишем услуги
		$resp = $this->saveLabSample(array(
			'pmUser_id' => $data['pmUser_id'],
			'RefSample_id' => null,
			'EvnLabRequest_id' => $EvnLabRequest_id,
			'MedService_id' => $res['MedService_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($resp[0]['EvnLabSample_id'])) {
			$EvnLabSample_id = $resp[0]['EvnLabSample_id'];
		} else {
			return array('Error_Msg' => 'Ошибка сохранения пробы'.(!empty($resp[0]['Error_Msg'])?': '.$resp[0]['Error_Msg']:''));
		}

		$query = "
			SELECT
				EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				EvnLabRequestUslugaComplex_insDT as \"EvnLabRequestUslugaComplex_insDT\",
				EvnLabRequestUslugaComplex_updDT as \"EvnLabRequestUslugaComplex_updDT\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnLabSample_id as \"EvnLabSample_id\"
			FROM
				v_EvnLabRequestUslugaComplex
			WHERE
				EvnLabRequest_id = :EvnLabRequest_id
				AND UslugaComplex_id IN (".implode(',', $data['UslugaComplexList']).")
		";

		$res = $this->db->query($query, array(
			'EvnLabRequest_id' => $EvnLabRequest_id
		));

		if ( is_object($res) ) {
			$EvnLabRequestUslugaComplexs = $res->result('array');
		}
		else {
			return array('Error_Msg' => 'Не найдены переданные услуги в заявке');
		}

		if(count($EvnLabRequestUslugaComplexs) == 0){
			return array('Error_Msg' => 'Не найдены переданные услуги в заявке');
		}

		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				evnlabrequestuslugacomplex_id as \"EvnLabRequestUslugaComplex_id\"
			from p_EvnLabRequestUslugaComplex_upd(
				EvnLabRequestUslugaComplex_id := :EvnLabRequestUslugaComplex_id ,
				EvnLabRequest_id := :EvnLabRequest_id,
				EvnLabSample_id := :EvnLabSample_id,
				EvnUslugaPar_id := :EvnUslugaPar_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id
			)
		";

		$params = array(
			'EvnLabRequest_id' => $EvnLabRequest_id,
			'EvnLabSample_id' => $EvnLabSample_id,
			'pmUser_id' => $data['pmUser_id']
		);
		$result = array();

		$oldSamples = [];
		foreach ($EvnLabRequestUslugaComplexs as $key => $value) {
			$params['EvnLabRequestUslugaComplex_id'] = $value['EvnLabRequestUslugaComplex_id'];
			$params['EvnUslugaPar_id'] = $value['EvnUslugaPar_id'];
			$params['UslugaComplex_id'] = $value['UslugaComplex_id'];

			if (!in_array($value['EvnLabSample_id'], $oldSamples)) {
				$oldSamples[] = $value['EvnLabSample_id'];
			}

			$res =  $this->queryResult($query, $params);
			if ($res) {
				collectEditedData('upd', 'EvnLabRequestUslugaComplex', $params['EvnLabRequestUslugaComplex_id']);
			}
			$result[] = $res;
		}

		//убираем из заявки пустые пробы
		foreach ($oldSamples as $id) {
			$this->queryResult("
				select
                	error_code as \"Error_Code\",
                	error_message as \"Error_Msg\"
                from p_EvnLabSample_del(
                	EvnLabSample_id := :EvnLabSample_id,
                    pmUser_id := :pmUser_id
                )
            ", [
            	'EvnLabSample_id' => $id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		//берем пробу
		$query = "
			SELECT
				EvnLabSample_id as \"EvnLabSample_id\",
				MedService_did as \"MedService_did\"
			FROM
				v_EvnLabSample
			WHERE
				EvnLabSample_id = :EvnLabSample_id";
		$res = $this->getFirstRowFromQuery($query, array('EvnLabSample_id' => $EvnLabSample_id ));
		if(empty($res['EvnLabSample_id'])){
			return array('Error_Msg' => 'ошибка при взятии пробы');
		}
		$data['EvnLabRequest_id'] = $EvnLabRequest_id;
		$data['EvnLabSample_id'] = $EvnLabSample_id;
		$data['MedServiceType_SysNick'] = 'lab';
		$data['RefSample_id'] = null;
		if(empty($data['MedService_did'])) $data['MedService_did'] = $res['MedService_did'];

		$result = $this->takeLabSample($data);

		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		// кэшируем количество тестов
		$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
			'EvnLabRequest_id' => $EvnLabRequest_id,
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем статус заявки
		$this->EvnLabRequest_model->ReCacheLabRequestStatus(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем названия услуг
		$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// кэшируем статус проб в заявке
		$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return $result;
	}

	/**
	 * Изменение информации о взятии пробы. Метод для API
	 */
	function updateEvnLabSampleAPI($data){
		$params = [
			'Evn_id' => $data['EvnLabSample_id']
		];
		//то, что pg возвращает имена полей в lower case, учтено
		$formerData = $this->getFirstRowFromQuery("
			select
				*
			from EvnLabSample
			where Evn_id = :Evn_id
		", $params);

		if (!is_array($formerData) || empty($formerData['evn_id'])) {
			return ['Error_Msg' => 'Не найдена проба с указанным EvnLabSample_id'];
		}

		$paramStrings = [];
		$data = array_change_key_case($data, CASE_LOWER);
		foreach ($formerData as $key => $value) {
			if ($key != 'evn_id' && isset($data[$key])) {
				$params[$key] = $data[$key];
				$paramStrings[] = "{$key} = :{$key}";
			}
		}

		if (isset($params['defectcausetype_id']) && $params['defectcausetype_id'] == 0) {
			$params['defectcausetype_id'] = null;
		}
		$result = $this->db->query("
			update EvnLabSample
			set
				" . implode(",\n", $paramStrings) . "
			where Evn_id = :Evn_id
		", $params);
		if (!$result) {
			return ['Error_Msg' => 'Ошибка при изменении пробы'];
		}

		return [
			'EvnLabSample_id' => $params['Evn_id']
		];
	}

	/**
	 * Изменение результатов лабораторного исследования. Метод для API
	 */
	function updateUslugaTestAPI($data){
		if(empty($data['UslugaTest_id'])){
			return ['Error_Msg' => 'Не передан параметр UslugaTest_id'];
		}
		//проверка существования извещения
		$arrUsluga = $this->getUslugaTest(['UslugaTest_id' => $data['UslugaTest_id']]);
		if(!isset($arrUsluga[0])) {
			return ['Error_Msg' => 'Услуга не найдена'];
		}
		//удостоверимся, что UslugaComplex_id соответствует тесту (приходит через POST)
		if(!empty($data['UslugaComplex_id']) && $data['UslugaComplex_id'] != $arrUsluga[0]['UslugaComplex_id']) {
			return ['Error_Msg' => 'Услуга не найдена'];
		}
		$params = $arrUsluga[0];
		$params['pmUser_id'] = $data['pmUser_id'];
		if (!empty($data['MedPersonal_aid'])) {
			$params['MedPersonal_aid'] = $data['MedPersonal_aid'];
		}

		foreach ($params as $key => $value) {
			if(!empty($data[$key])){
				$params[$key] = $data[$key];
			}
		}

		// меняю server_id на server_id  соответстствующей записи в v_personevn (иначе процедура может выкинуть ошибки)
		if(!empty($params['PersonEvn_id'])){
			$res = $this->common->GET('Person/serverByPersonEvn', [
				'PersonEvn_id' => $params['PersonEvn_id']
			], 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}

			$res = $res['Server_id'];
			$params['Server_id'] = !empty($res) ? $res : $params['Server_id'];
		}

		$result = $this->saveUslugaTest($params);
		if($result && !empty($data['UslugaTest_deleted']) && $data['UslugaTest_deleted'] == 2 && !empty($data['UslugaTest_delDT'])){
			//отменяем отдельно
			$params['tests'] = [[
				'UslugaTest_pid' => $params['UslugaTest_pid'],
				'UslugaComplex_id' => $params['UslugaComplex_id']
			]];
			$query = "
				SELECT
					EvnLabRequest_id as \"EvnLabRequest_id\"
				FROM
					v_EvnLabSample
				WHERE
					EvnLabSample_id = :EvnLabSample_id
				limit 1
				";
			$res = $this->getFirstRowFromQuery($query, $params);
			if(!empty($res['EvnLabRequest_id'])){
				$params['EvnLabRequest_id'] = $res['EvnLabRequest_id'];
				$query = "
					SELECT
						EvnLabRequestUslugaComplex_id as \"EvnLabRequestUslugaComplex_id\"
					FROM
						v_EvnLabRequestUslugaComplex
					WHERE (1=1)
						and EvnLabSample_id = :EvnLabSample_id
						AND EvnLabRequest_id = :EvnLabRequest_id
						and UslugaComplex_id = :UslugaComplex_id
					limit 1
					";
				$res = $this->getFirstRowFromQuery($query, $params);
				if(!empty($res['EvnLabRequestUslugaComplex_id'])){
					$params['EvnLabRequestUslugaComplex_id'] = $res['EvnLabRequestUslugaComplex_id'];
					$res = $this->cancelTest($params);
				}
			}
		}

		if($result && !empty($data['UslugaTest_ResultApproved']) && $data['UslugaTest_ResultApproved'] == 2) {
			// помечаем как одобренные
			if (!isset($data['EvnLabSample_id'])) {
				$data['EvnLabSample_id'] = $params['EvnLabSample_id'];
			}
			$this->approveResults($data);

			$this->onChangeApproveResults([
					'EvnUslugaParChanged' => [$params['UslugaTest_pid']],
					'session' => $data['session'],
					'pmUser_id' => $params['pmUser_id']
			]);
		}

		$this->ReCacheLabSampleStatus([
			'EvnLabSample_id' => $params['EvnLabSample_id']
		]);

		$this->ReCacheLabRequestByLabSample([
			'EvnLabSample_id' => $params['EvnLabSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $params['pmUser_id']
		]);
		// устанавливаем врача выполнившего анализ
		$this->setMedPersonalAID($params);
		return $result;
	}

	/**
	 * Добавление результатов лабароторного исследования. Метод для API
	 */
	function createUslugaTestAPI($data){
		if(empty($data['EvnLabSample_id']) || empty($data['UslugaComplex_id'])){
			return array('Error_Msg' => 'Не переданы обязательные параметры');
		}
		// найдем UslugaTest_id т.к. она должна существовать для метода добавления, этот UslugaTest_id мы возвращаем пользователю, типа мы ее создали (не я придумал такую кухню)
		$query = "
			SELECT
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_pid as \"UslugaTest_pid\",
				UslugaTest_rid as \"UslugaTest_rid\",
				UslugaTest_setDT as \"UslugaTest_setDT\",
				UslugaTest_disDT as \"UslugaTest_disDT\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnDirection_id as \"EvnDirection_id\",
				Usluga_id as \"Usluga_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				UslugaTest_ResultAppDate as \"UslugaTest_ResultAppDate\",
				UslugaTest_ResultCancelReason as \"UslugaTest_ResultCancelReason\",
				UslugaTest_Comment as \"UslugaTest_Comment\",
				UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				UslugaTest_Kolvo as \"UslugaTest_Kolvo\",
				UslugaTest_Result as \"UslugaTest_Result\",
				EvnLabsample_id as \"EvnLabsample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				UslugaTest_insDT as \"UslugaTest_insDT\",
				UslugaTest_updDT as \"UslugaTest_updDT\"
			FROM
				v_UslugaTest
			WHERE
				EvnLabSample_id = :EvnLabSample_id
				and UslugaComplex_id = :UslugaComplex_id";
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['UslugaTest_id'])){
			return array('Error_Msg' => 'Услуга не создана');
		}
		$data['UslugaTest_id'] = $res['UslugaTest_id'];
		$result = $this->updateUslugaTestAPI($data);
		return $result;
	}

	/**
	 * получаем данные о тесте
	 */
	function getUslugaTest($data){
		if(empty($data['UslugaTest_id'])) {
			return false;
		}
		$query = "
			select
				UslugaTest_id as \"UslugaTest_id\",
				UslugaTest_pid as \"UslugaTest_pid\",
				UslugaTest_rid as \"UslugaTest_rid\",
				UslugaTest_setDT as \"UslugaTest_setDT\",
				UslugaTest_disDT as \"UslugaTest_disDT\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnDirection_id as \"EvnDirection_id\",
				Usluga_id as \"Usluga_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				UslugaTest_ResultAppDate as \"UslugaTest_ResultAppDate\",
				UslugaTest_ResultCancelReason as \"UslugaTest_ResultCancelReason\",
				UslugaTest_Comment as \"UslugaTest_Comment\",
				UslugaTest_ResultLowerCrit as \"UslugaTest_ResultLowerCrit\",
				UslugaTest_ResultUpperCrit as \"UslugaTest_ResultUpperCrit\",
				UslugaTest_ResultQualitativeNorms as \"UslugaTest_ResultQualitativeNorms\",
				UslugaTest_ResultQualitativeText as \"UslugaTest_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				LabTest_id as \"LabTest_id\",
				UslugaTest_Kolvo as \"UslugaTest_Kolvo\",
				UslugaTest_Result as \"UslugaTest_Result\",
				EvnLabSample_id as \"EvnLabSample_id\",
				EvnLabRequest_id as \"EvnLabRequest_id\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				UslugaTest_insDT as \"UslugaTest_insDT\",
				UslugaTest_updDT as \"UslugaTest_updDT\"
			from
				v_UslugaTest
			where
				UslugaTest_id = :UslugaTest_id
			limit 1
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Установка врача выполнившего анализ
	 */
	function setMedPersonalAID($data) {
		if(empty($data['EvnLabSample_id']) || empty($data['MedPersonal_aid'])) return false;
		return $this->db->query(
			'UPDATE EvnLabSample SET MedPersonal_aid = :MedPersonal_aid WHERE Evn_id = :EvnLabSample_id',
			$data
		);
	}

	/**
	 * получить результаты услугм для портала
	 */
	function getUslugaTestResultForPortal($data) {

		$result = $this->queryResult("
			select
				up.UslugaComplex_id as \"UslugaComplex_id\",
				up.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				up.UslugaTest_ResultLower as \"EvnUslugaPar_ResultLower\",
				up.UslugaTest_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				up.UslugaTest_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				up.UslugaTest_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				up.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				up.UslugaTest_Comment as \"EvnUslugaPar_Comment\",
				up.UslugaTest_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\"
			from v_UslugaTest up
				left join v_UslugaComplex uc on up.UslugaComplex_id = uc.UslugaComplex_id
			where
				up.UslugaTest_pid = :UslugaTest_pid
				and up.UslugaTest_setDT is not null
		", array('UslugaTest_pid' => $data['UslugaTest_pid']));

		return $result;
	}

	/**
	 * @param string $params
	 * @param array $data
	 * @return string
	 * переопределяет метод из _pgsql/Abstract_model.php
	 */
	function processSaveParams($params, $data)
	{
		$params = explode(',', $params);
		$result = [];
		foreach ($params as $param) {
			$param = explode(' := ', trim($param))[0];
			if (array_key_exists($param, $data) && !empty($data[$param])) {
				$result[] = $param .' := :' . $param;
			}
		}
		return implode(",\n", $result);
	}
	#endregion common

	/**
	 * Чтение из строки
	 * @param $value
	 * @return float
	 */
	function getFloatResult($value) {
		$value = trim(str_replace(",", ".", $value));
		if(!is_numeric($value)) {
			return null;
		}
		return floatval($value);
	}

	/**
	 * Определение патологического количественного теста
	 * @param $value
	 * @param $lowerValue
	 * @param $upperValue
	 * @return bool
	 */
	function isPathologicalQuantitativeTest($value, $lowerValue = null, $upperValue = null) {
		if(!is_numeric($value)) {
			return true;
		}
		$lowerValue = isset($lowerValue) ? $lowerValue : -INF;
		$upperValue = isset($upperValue) ? $upperValue : INF;
		return $value < $lowerValue || $upperValue < $value;
	}

	/**
	 * Определение патологического качественного теста
	 * @param string $value
	 * @param array $qualitativeNorms
	 * @return bool
	 */
	function isPathologicalQualitativeTest($value = '', $qualitativeNorms = array()) {
		if(!is_array($qualitativeNorms)) {
			return true;
		}
		array_walk_recursive($qualitativeNorms, 'ConvertFromUTF8ToWin1251');
		return !in_array($value, $qualitativeNorms);
	}

	/**
	 * @param int $Evn_id
	 * @param int $UslugaMedType_id
	 * @throws Exception
	 */
	protected function saveUslugaMedTypeLink($Evn_id, $UslugaMedType_id, $pmUser_id)
	{
		if (getRegionNick() === 'kz') {
			$this->load->model('UslugaMedType_model');

			$result = $this->common->POST('UslugaMedType/link', [
				'UslugaMedType_id' => $UslugaMedType_id,
				'Evn_id' => $Evn_id,
				'pmUser_id' => $pmUser_id
			], 'single');

			if (!$this->isSuccessful($result)) {
				throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
			}
		}
	}
}
