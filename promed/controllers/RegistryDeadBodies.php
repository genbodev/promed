<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * RegistryDeadBodies - контроллер для работы с журналом регистрации приема и выдачи тел умерших
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Shekunov Dmitiy
 * @property RegistryDeadBodies_model dbmodel
 */
class RegistryDeadBodies extends swController
{
	public $inputRules = array(
		'loadRegistryDeadBodiesListGrid' => array(
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Лимит записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => '',
				'field' => 'ReportPeriod',
				'label' => 'Отчетный период',
				'rules' => 'required|trim', 
				'type' => 'daterange'
			),
			array(
				'default' => '',
				'field' => 'Lpu_sid',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => '',
				'field' => 'Refuse_Exists',
				'label' => 'Отказ от вскрытия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'MorfoHistologicCorpse_recieptDate',
				'label' => 'Дата поступления тела',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => '',
				'field' => 'PersonDead_FIO',
				'label' => 'ФИО умершего',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnPS_NumCard',
				'label' => 'Номер мед.карты',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnMorfoHistologicProto_autopsyDate',
				'label' => 'Дата вскрытия тела',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => '',
				'field' => 'MorfoHistologicCorpse_giveawayDate',
				'label' => 'Дата выдачи тела',
				'rules' => 'trim',
				'type' => 'daterange'
			)
		)
	);

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('RegistryDeadBodies_model', 'dbmodel');
	}

	/**
	 *  Получение списка записей поступления и выдачи тел умерших за определенный период
	 *  Входящие данные: $_POST['ReportPeriod']
	 *  На выходе: JSON-строка
	 *  Используется: журнал регистрации поступления и выдачи тел умерших
	 */
	function loadRegistryDeadBodiesListGrid()
	{
		$data = $this->ProcessInputData('loadRegistryDeadBodiesListGrid', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadRegistryDeadBodiesListGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Печать журнала регистрации поступления и выдачи тел умерших
	 *  Входящие данные: $_POST['ReportPeriod']
	 *  На выходе: форма для печати журнала регистрации 
	 *  Используется: журнала регистрации поступления и выдачи тел умерших 
	 */
	function printRegistryDeadBodiesJournal()
	{
		$this->load->library('parser');

		$arMonthOf = array(
			'01' => "января",
			'02' => "февраля",
			'03' => "марта",
			'04' => "апреля",
			'05' => "мая",
			'06' => "июня",
			'07' => "июля",
			'08' => "августа",
			'09' => "сентября",
			'10' => "октября",
			'11' => "ноября",
			'12' => "декабря",
		);

		$input_data = $this->ProcessInputData('loadRegistryDeadBodiesListGrid', true);
		if ($input_data === false) {
			return false;
		}
		$data = $this->dbmodel->loadRegistryDeadBodiesListGrid($input_data);
		
		$this->load->model('User_model', 'umodel');
		// Получаем данные по ЛПУ
		$lpu_info = $this->umodel->getCurrentLpuName($input_data);
		$printArr['Lpu_Name'] = $lpu_info[0]['Lpu_Name'];
		$printArr['Lpu_Address'] = $lpu_info[0]['Lpu_Address'];

		$begDate = explode ('-', $input_data['ReportPeriod'][0]);
		$printArr['begDay'] = $begDate[2];
		$printArr['begMonth'] = $arMonthOf[$begDate[1]];
		$printArr['begYear'] = $begDate[0];

		$endDate = explode ('-', $input_data['ReportPeriod'][1]);
		$printArr['endDay'] = $endDate[2];
		$printArr['endMonth'] = $arMonthOf[$endDate[1]];
		$printArr['endYear'] = $endDate[0];
		
		$countPrintArr = count($data['data']);
		$printArr['list'] = "";
		$n = 1;
		
		for ($i = 0; $i < $countPrintArr; $i++) {

			if ($data["data"][$i]['EvnMorfoHistologicProto_autopsyDate'] == null) {
				$data["data"][$i]['EvnMorfoHistologicProto_autopsyDate'] = $data["data"][$i]['MorfoHistologicRefuseType_name'];
			} else {
				$data["data"][$i]['EvnMorfoHistologicProto_autopsyDate'];
			}

			$printArr['list'] .= "			
			<tr>
				<td>{$n}</td>
				<td>{$data['data'][$i]['MorfoHistologicCorpse_recieptDate']}</td>
				<td>{$data['data'][$i]['PersonDead_FIO']}</td>
				<td>{$data['data'][$i]['Lpu_Name']}</td>
				<td>{$data['data'][$i]['EvnPS_NumCard']}</td>
				<td>{$data['data'][$i]['EvnMorfoHistologicProto_autopsyDate']}</td>
				<td>{$data['data'][$i]['MorfoHistologicCorpse_giveawayDate']}</td>
				<td>{$data['data'][$i]['PersonRecipient_FIO']}</td>
				<td></td>
			</tr>";
			$n++;
		}
		
		return  $this->parser->parse('print_registry_dead_bodies_journal', $printArr);
		
	}

}