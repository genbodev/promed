<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * SportRegister - контроллер для регистра спортсменов (Башкирия) *
 *
 * @package            SportRegistry
 * @author             Хамитов Марат
 * @version            12.2018
 */
class SportRegister extends swController
{
	var $model = "SportRegister_model";
	
	
	/**
	 * comment
	 */
	function __construct ()
	{
		$this->result = array();
		$this->start = true;
		
		parent::__construct();
		
		
		$this->load->database();
		$this->load->model($this->model, 'dbmodel');
		
		$this->inputRules = array(
			'getSportType' => array(),
			'getSportStage' => array(),
			'getSportCategory' => array(),
			'getSportOrg' => array(),
			'getMedPersonalP' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalS' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedPersonalFilter' => array(),
			'getSportTrainer' => array(
				array(
					'field' => 'SportTrainer_name',
					'label' => 'ФИО тренера',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getDisabilityGroup' => array(),
			'getSportParaGroup' => array(),
			'getUMOResult' => array(),
			'getPersonUMODates' => array(
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор регистра спортсмена',
					'rules' => '',
					'type' => 'id'
				)),
			'addSportRegister' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'SportRegisterDateUpdate' => array (
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор регистра спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkInSportRegister' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
			),
			'checkInSportTrainer' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
			),
			'addSportTrainer' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'addSportRegisterUMO' => array(
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор регистра спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_UMODate',
					'label' => 'Дата УМО',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'InvalidGroupType_id',
					'label' => 'Идентификатор группы инвалидности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportParaGroup_id',
					'label' => 'Идентификатор паралимпийской группы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_IsTeamMember',
					'label' => 'Признак спортсмена-сборника',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_pid',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_sid',
					'label' => 'Идентификатор медсестры',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportType_id',
					'label' => 'Идентификатор вида спорта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportOrg_id',
					'label' => 'Идентификатор спортивной организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportTrainer_id',
					'label' => 'Идентификатор тренера',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportStage_id',
					'label' => 'Идентификатор этапа спортивной подготовки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportCategory_id',
					'label' => 'Идентификатор спортивного разряда',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UMOResult_id',
					'label' => 'Идентификатор заключения врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_AdmissionDtBeg',
					'label' => 'Дата начала допуска',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'SportRegisterUMO_AdmissionDtEnd',
					'label' => 'Дата окончания допуска',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'UMOResult_comment',
					'label' => 'Примечание к результату УМО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'updateSportRegisterUMO' => array(
				array(
					'field' => 'SportRegisterUMO_id',
					'label' => 'Идентификатор УМО спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор регистра спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_UMODate',
					'label' => 'Дата УМО',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'InvalidGroupType_id',
					'label' => 'Идентификатор группы инвалидности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportParaGroup_id',
					'label' => 'Идентификатор паралимпийской группы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_IsTeamMember',
					'label' => 'Признак спортсмена-сборника',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_pid',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_sid',
					'label' => 'Идентификатор медсестры',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportType_id',
					'label' => 'Идентификатор вида спорта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportOrg_id',
					'label' => 'Идентификатор спортивной организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportTrainer_id',
					'label' => 'Идентификатор тренера',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportStage_id',
					'label' => 'Идентификатор этапа спортивной подготовки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportCategory_id',
					'label' => 'Идентификатор спортивного разряда',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UMOResult_id',
					'label' => 'Идентификатор заключения врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegisterUMO_AdmissionDtBeg',
					'label' => 'Дата начала допуска',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'SportRegisterUMO_AdmissionDtEnd',
					'label' => 'Дата окончания допуска',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'UMOResult_comment',
					'label' => 'Примечание к результату УМО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteSportRegister' => array(
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterOutCause_id',
					'label' => 'Идентификатор причины исключения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SportRegister_detDT',
					'label' => 'Идентификатор причины исключения',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteSportRegisterUMO' => array(
				array(
					'field' => 'SportRegisterUMO_id',
					'label' => 'Идентификатор УМО спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'restoreSportRegister' => array(
				array(
					'field' => 'SportRegister_id',
					'label' => 'Идентификатор спортсмена',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getSportRegisterUMO' => array(
				array(
					'field' => 'SportRegisterUMO_id',
					'label' => 'Идентификатор УМО спортсмена',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getRawSportTrainer' => array(
				array(
					'field' => 'SportTrainer_id',
					'label' => 'Идентификатор тренера',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getNames' => array(
				array(
					'field' => 'SportRegisterUMO_id',
					'label' => 'Идентификатор УМО спортсмена',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadPersonData' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getOutCauses' => array(),
			'PrintSportRegisterUMO' => array(
			array('field' => 'SportRegisterUMO_id','label' => '','rules' => '','type' => 'id')
				)
		);
		
	}
	
	/**
	 *  Получаем виды спорта
	 */
	function getSportType ()
	{
		$data = $this->ProcessInputData('getSportType', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportType($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем этапы спортивной подготовки
	 */
	function getSportStage ()
	{
		$data = $this->ProcessInputData('getSportStage', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportStage($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем спортивные разряды
	 */
	function getSportCategory ()
	{
		$data = $this->ProcessInputData('getSportCategory', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportCategory($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем список спортивных школ
	 */
	function getSportOrg ()
	{
		$data = $this->ProcessInputData('getSportOrg', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportOrg($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем список врачей
	 */
	function getMedPersonalP ()
	{
		$data = $this->ProcessInputData('getMedPersonalP', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getMedPersonalP($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем список медсестер
	 */
	function getMedPersonalS ()
	{
		$data = $this->ProcessInputData('getMedPersonalS', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getMedPersonalS($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем список тренеров
	 */
	function getSportTrainer ()
	{
		$data = $this->ProcessInputData('getSportTrainer', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportTrainer($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем группы инвалидности
	 */
	function getDisabilityGroup ()
	{
		$data = $this->ProcessInputData('getDisabilityGroup', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getDisabilityGroup($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем паралимпийские группы
	 */
	function getSportParaGroup ()
	{
		$data = $this->ProcessInputData('getSportParaGroup', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportParaGroup($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем даты всех анкет УМО спортсмена
	 */
	function getPersonUMODates ()
	{
		$data = $this->ProcessInputData('getPersonUMODates', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getPersonUMODates($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем заключения УМО
	 */
	function getUMOResult ()
	{
		$data = $this->ProcessInputData('getUMOResult', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getUMOResult($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Добавляем спортсмена в регистр спортсменов
	 */
	function addSportRegister ()
	{
		$data = $this->ProcessInputData('addSportRegister', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->addSportRegister($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Добавляем спортсмена в регистр спортсменов
	 */
	function SportRegisterDateUpdate ()
	{
		$data = $this->ProcessInputData('SportRegisterDateUpdate', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->SportRegisterDateUpdate($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Проверяем спортсмена в регистре спортсменов по идентификатору человека
	 */
	function checkInSportRegister ()
	{
		$data = $this->ProcessInputData('checkInSportRegister', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->checkInSportRegister($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Проверяем наличие тренера в регистре спорсменов по идентификатору человека
	 */
	function checkInSportTrainer ()
	{
		$data = $this->ProcessInputData('checkInSportTrainer', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->checkInSportTrainer($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Добавляем тренера в регистр спортсменов
	 */
	function addSportTrainer ()
	{
		$data = $this->ProcessInputData('addSportTrainer', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->addSportTrainer($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Добавляем анкету УМО спортсмена
	 */
	function addSportRegisterUMO ()
	{
		$data = $this->ProcessInputData('addSportRegisterUMO', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->addSportRegisterUMO($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Обновляем анкету УМО спортсмена
	 */
	function updateSportRegisterUMO ()
	{
		$data = $this->ProcessInputData('updateSportRegisterUMO', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->updateSportRegisterUMO($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Удаляем человека из регистра спортсменов
	 */
	function deleteSportRegister ()
	{
		$data = $this->ProcessInputData('deleteSportRegister', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->deleteSportRegister($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Восстанавливаем человека в регистр спортсменов
	 */
	function restoreSportRegister ()
	{
		$data = $this->ProcessInputData('restoreSportRegister', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->restoreSportRegister($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем данные УМО по идентификатору
	 */
	function getSportRegisterUMO ()
	{
		$data = $this->ProcessInputData('getSportRegisterUMO', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getSportRegisterUMO($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Получаем получаем причины исключения из регистра
	 */
	function getOutCauses ()
	{
		$data = $this->ProcessInputData('getOutCauses', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getOutCauses($data);
		return $this->ReturnData($list);
	}
	
	/**
	 *  Функция перекраски шрифта в синий цвет
	 */
	function blue($text)
	{
		return '<font style="color: blue;">' . $text . '</font>';
	}
	
	/**
	 * Получаем сведения о пациенте
	 */
	function loadPersonData ()
	{
		$data = $this->ProcessInputData('loadPersonData', true);
		if ($data === false) {
			return false;
		}
		
		$dbAnswer = $this->dbmodel->loadPersonData($data);
		
		$titleInfo = $dbAnswer[0]['Person_Surname'] . ' ' . $dbAnswer[0]['Person_Firname'] . ' ' . $dbAnswer[0]['Person_Secname'] . ', ' . $dbAnswer[0]['Person_Birthday'];
		$textInfo = 'ФИО: <b>' . $this->blue($dbAnswer[0]['Person_Surname'] . ' ' . $dbAnswer[0]['Person_Firname'] . ' ' . $dbAnswer[0]['Person_Secname']) . '</b> 
		Д/р: ' . $this->blue($dbAnswer[0]['Person_Birthday']) . ' Пол: ' . $this->blue($dbAnswer[0]['Sex_Name']) . '<br/> 
		Соц. статус: ' . $this->blue($dbAnswer[0]['SocStatus_Name']) . ' СНИЛС: ' . $this->blue($dbAnswer[0]['Person_Snils']) . '<br/>
		Регистрация: ' . $this->blue($dbAnswer[0]['Person_RAddress']) . '<br/>
		Проживает: ' . $this->blue($dbAnswer[0]['Person_PAddress']) . '<br/>
		Полис: ' . $this->blue($dbAnswer[0]['Polis_Num']) . ' Выдан: ' . $this->blue($dbAnswer[0]['Polis_begDate'] . ', ' . $dbAnswer[0]['OrgSmo_Name']) . ' Закрыт: ' . $this->blue($dbAnswer[0]['Polis_endDate']) . '<br/>
		Документ: ' . $this->blue($dbAnswer[0]['Document_Num'] . ' ' . $dbAnswer[0]['Document_Ser']) . ' Выдан: ' . $this->blue($dbAnswer[0]['Document_begDate']) . '<br/>
		Работа: ' . $this->blue($dbAnswer[0]['Person_Job']) . ' Должность: ' . $this->blue($dbAnswer[0]['Person_Post']) . '<br/>
		МО: ' . $this->blue($dbAnswer[0]['Lpu_Nick']) . ' Участок: ' . $this->blue($dbAnswer[0]['LpuRegion_Name']) . ' Дата прикрепления: ' . $this->blue($dbAnswer[0]['PersonCard_begDate']) . '
		';
		
		$personInfo = $dbAnswer[0];
		
		$personData = json_encode(array(
			'title' => $titleInfo,
			'text' => $textInfo,
			'personInfo' => $personInfo
		));
		
		echo $personData;
	}
	
	/**
	 * Печать УМО смортсмена
	 */
	function PrintSportRegisterUMO() {
		$this->load->library('parser'); 
		
		$data = $this->ProcessInputData('PrintSportRegisterUMO', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->PrintSportRegisterUMO($data);//$data['SportRegisterUMO_id']);
		
		if ( is_array($response) && count($response) > 0 ) {
			$template = 'print_SportRegisterUMO';
			$parse_data = array(
				'SportRegisterUMO_UMODate' => $response[0]['SportRegisterUMO_UMODate'],
				'SFS' => $response[0]['SFS'],
				'Person_BirthDay' => $response[0]['Person_BirthDay'],
				'InvalidGroupType_name' => $response[0]['InvalidGroupType_name'],
				'SportParaGroup_name' => $response[0]['SportParaGroup_name'],
				'SportRegisterUMO_IsTeamMember' => $response[0]['SportRegisterUMO_IsTeamMember'],
				'SportTrainer_name' => $response[0]['SportTrainer_name'],
				'MedPersonal_pname' => $response[0]['MedPersonal_pname'],
				'MedPersonal_sname' => $response[0]['MedPersonal_sname'],
				'SportType_name' => $response[0]['SportType_name'],
				'SportOrg_name' => $response[0]['SportOrg_name'],
				'SportStage_name' => $response[0]['SportStage_name'],
				'SportCategory_name' => $response[0]['SportCategory_name'],
				'UMOResult_name' => $response[0]['UMOResult_name'],
				'UMOResult_comment' => $response[0]['UMOResult_comment'],
				'SportRegisterUMO_AdmissionDtBeg' => $response[0]['SportRegisterUMO_AdmissionDtBeg'],
				'SportRegisterUMO_AdmissionDtEnd' => $response[0]['SportRegisterUMO_AdmissionDtEnd'],
				'SportRegister_updDT' => $response[0]['SportRegister_updDT']
			);
			/*$val = array();
			foreach ($response[0] as $row) {
				$val[$row] = $row;
			}*/
			$this->parser->parse($template, $parse_data);
			return true;
		}
		
	}
	
}
