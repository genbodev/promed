<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Evn - обобщенные методы для работы с событиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009-2012 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			2012-11-28
 *
 * @property Evn_model $dbmodel
 */
class Evn extends swController {
	public $inputRules = array(
		'deleteEvn' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ArmType', 'label' => 'Текущий АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'DeleteEvnParent', 'label' => 'Флаг удаления радительского события', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreDoc', 'label' => 'Игнорировать прикрепленные документы', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreEvnDrug', 'label' => 'Игнорировать использование медикаментов', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreCheckEvnUslugaChange', 'label' => 'Игнорировать проверку наличия паракл. услуг', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreHomeVizit', 'label' => 'Игнорировать проверку вызовов на дом', 'rules' => '', 'type' => 'checkbox'),
		),
		'updateEvnStatus' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatus_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnClass_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnClass_SysNick', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusHistory_Cause', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'string'),
			
		),
		'CommonChecksForEdit' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'from', 'label' => 'Откуда открыта форма', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'ArmType', 'label' => 'Текущий АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'isCMPCloseCard', 'label' => 'Карта СМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'CmpCallCard_id', 'label' => 'Карта СМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'isForm', 'label' => 'Какая открыта форма', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnUslugaPar_id', 'label' => 'Карта СМП', 'rules' => '', 'type' => 'id')
		),
		'deleteFromArm' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача-пользователя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeleteEvnParent', 'label' => 'Флаг удаления радительского события', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreDoc', 'label' => 'Игнорировать прикрепленные документы', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreEvnDrug', 'label' => 'Игнорировать использование медикаментов', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreCheckEvnUslugaChange', 'label' => 'Игнорировать проверку наличия паракл. услуг', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'ignoreHomeVizit', 'label' => 'Игнорировать проверку вызовов на дом', 'rules' => '', 'type' => 'checkbox'),
		),
		'getEvnJournal' => array (
			array('field' => 'Person_id','label' => 'Идентификатор текущего человека','rules' => 'required','type' => 'id'),
			array('field' => 'isMseDepers','label' => 'Признак деперсонализации','rules' => '','type' => 'id'),
			array('field' => 'start','label' => '','rules' => '','type' => 'int','default' => 0),
			array('field' => 'limit','label' => '','rules' => '','type' => 'int','default' => 10),
			array('field' => 'query','label' => '','rules' => '','type' => 'string'),
		),
		'setEvnIsTransit' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_IsTransit', 'label' => 'Признак "Переходный случай между МО"', 'rules' => 'required', 'type' => 'id'),
			
		),
		'getEvnData' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'mode', 'label' => 'Вид загрузки данных', 'rules' => '', 'type' => 'string')
		),
		'getParentEvn' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id')
		),
		'checkOnOnlyOneExist' => [
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'isStom', 'label' => 'Признак стоматологии', 'rules' => 'required', 'type' => 'checkbox')
		]
	);

    /**
     * Конструктор
     */
    function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Evn_model', 'dbmodel');
	}


	/**
	 *	Удаление события лечения (КВС, ТАП) из БД через АРМ врача с проверками
	 *	Входящие данные: $_POST['Evn_id']
	 *	На выходе: JSON-строка
	 *	Используется: при удалении из АРМ врача
	 */
	function deleteFromArm() {
		$data = $this->ProcessInputData('deleteFromArm', true);
		if ( $data === false ) { return false; }
		try {
			$this->dbmodel->isAllowTransaction = true;
			if (false === $this->dbmodel->beginTransaction()) {
				$this->dbmodel->isAllowTransaction = false;
				throw new Exception('Не удалось начать транзакцию', 500);
			}
			// Общие проверки
			$checkResult = $this->doCommonChecksOnDelete($data);
			if ( is_array($checkResult) && count($checkResult) > 0 && !empty($checkResult[0]['Error_Msg']) ) {
				throw new Exception($checkResult[0]['Error_Msg'], 500);
			}
			if ( is_array($checkResult) && count($checkResult) > 0 && !empty($checkResult[0]['Alert_Msg']) ) {
				throw new Exception($checkResult[0]['Alert_Msg'], $checkResult[0]['Alert_Code']);
			}
			$response = $this->dbmodel->deleteFromArm($data);
			if ( !empty($response[0]['Error_Msg']) && $response[0]['Error_Msg'] == 'YesNo' ) {
				throw new Exception($response[0]['Alert_Msg'], $response[0]['Error_Code']);
			}
			if ( !is_array($response) || count($response) == 0 ) {
				throw new Exception('Ошибка при удалении события');
			}
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			$this->dbmodel->commitTransaction();
		} catch (Exception $e) {
			//select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			$this->dbmodel->rollbackTransaction();
			if ($e->getCode() >= 700) {
				$response = array(
					'Alert_Msg'=>$e->getMessage(),
					'Alert_Code'=>$e->getCode(),
				);
			} else {
				$response = array(
					'Error_Msg'=>$e->getMessage(),
					'Error_Code'=>$e->getCode(),
				);
			}
		}

		if (!empty($response) && !empty($response[0]) && empty($response[0]['Error_Msg'])) {
			if (in_array($data['EvnClass_SysNick'], ['EvnPL', 'EvnPS'])) {
				$params = $data;
				$params['object'] = $data['EvnClass_SysNick'];
				$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
				$this->CVIRegistry_model->deleteCVI($params);
			}
		}

		$this->ProcessModelSave($response, true, 'При удалении документа возникли ошибки')->ReturnData();
		return true;
	}
	
	/**
	 * ggg
	 */
	function updateEvnStatus(){
		$data = $this->ProcessInputData('updateEvnStatus', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->updateEvnStatus($data);
		if (!$response) {
			$this->ProcessModelSave($response, true, 'При удалении документа возникли ошибки')->ReturnData();
		}
		$this->ReturnData(['success' => true]);
		return true;
	}

	/**
	 * Установка признака "Переходный случай между МО"
	 */
	function setEvnIsTransit(){
		$data = $this->ProcessInputData('setEvnIsTransit', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setEvnIsTransit($data);
		$this->ProcessModelSave($response, true, 'Ошибка при изменении признака "Переходный случай между МО"')->ReturnData();
		return true;
	}

	/**
	 *	Удаление события из БД с проверками
	 *	Входящие данные: $_POST['Evn_id']
	 *	На выходе: JSON-строка
	 *	Используется: дофига где
	 */
	function deleteEvn() {
		$data = $this->ProcessInputData('deleteEvn', true);
		if ( $data === false ) { return false; }
		try {
			$this->dbmodel->isAllowTransaction = true;
			if (false === $this->dbmodel->beginTransaction()) {
				$this->dbmodel->isAllowTransaction = false;
				throw new Exception('Не удалось начать транзакцию', 500);
			}
			// Общие проверки
			$checkResult = $this->doCommonChecksOnDelete($data);
			if ( is_array($checkResult) && count($checkResult) > 0 && !empty($checkResult[0]['Error_Msg']) ) {
				throw new Exception($checkResult[0]['Error_Msg'], 500);
			}
			if ( is_array($checkResult) && count($checkResult) > 0 && !empty($checkResult[0]['Alert_Msg']) ) {
				throw new Exception($checkResult[0]['Alert_Msg'], $checkResult[0]['Alert_Code']);
			}
			$response = $this->dbmodel->deleteEvn($data);
			if ( !is_array($response) || count($response) == 0 ) {
				throw new Exception('Ошибка при удалении события');
			}
			if ( !empty($response[0]['Error_Msg']) && $response[0]['Error_Msg'] == 'YesNo' ) {
				throw new Exception($response[0]['Alert_Msg'], $response[0]['Error_Code']);
			}
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			$this->dbmodel->commitTransaction();
		} catch (Exception $e) {
			//select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			$this->dbmodel->rollbackTransaction();
			if ($e->getCode() >= 700) {
				$response = array(
					'Alert_Msg'=>$e->getMessage(),
					'Alert_Code'=>$e->getCode(),
				);
			} else {
				$response = array(
					'Error_Msg'=>$e->getMessage(),
					'Error_Code'=>$e->getCode(),
				);
			}
		}

		if (!empty($response) && !empty($response[0]) && empty($response[0]['Error_Msg'])) {
			if (in_array($data['EvnClass_SysNick'], ['EvnPL', 'EvnPS'])) {
				$params = $data;
				$params['object'] = $data['EvnClass_SysNick'];
				$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
				$this->CVIRegistry_model->deleteCVI($params);
			}
		}

		$this->ProcessModelSave($response, true, 'При удалении документа возникли ошибки')
			->ReturnData();
		return true;
	}


	/**
	 *	Общие проверки, выполняемые при удалении события
	 */
	private function doCommonChecksOnDelete(&$data) {

		// Получаем список связанных событий
		$evnTreeData = $this->dbmodel->getRelatedEvnList($data);


		if ( !is_array($evnTreeData) || count($evnTreeData) == 0 ) {
			return array(array('Error_Code' => 1,'Error_Msg'=>'Ошибка при получении списка связанных событий'));
		}
		$data['EvnClass_SysNick'] = $this->dbmodel->getEvnClassSysNick($data['Evn_id']);
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$enableEmkEmdDocControls = $this->config->item('ENABLE_EMK_EMD_DOCUMENT_CONTROLS');
			// проверяем на наличие документов в базе РЭМД
			if (!empty($enableEmkEmdDocControls)) {

				$this->load->model('EMD_model');
				$checkResult = $this->EMD_model->getEMDDocumentListByEvn($data);
				if (!empty($checkResult)) {
					return array(array('Error_Code' => 1, 'Error_Msg' => 'Удаление документа невозможно, т.к. он сам или входящие в него документы зарегистрированны в РЭМД'));
				}
			}
		}
		
		// при удалении последнего посещения случай АПЛ удаляется, проверяем, что в ТАП нет ЛВН
		if ($data['EvnClass_SysNick'] == 'EvnVizitPL') { 
			$params = array(
				'Evn_id' => $data['Evn_id'],
				'mode' => 'getPid'
			);
			$pid_resp = $this->dbmodel->getDataByEvn($params);

			if (!empty($pid_resp[0]['Evn_pid'])) {
				$Evn_pid = $pid_resp[0]['Evn_pid'];

				$params = array('Evn_id' => $Evn_pid, 'EvnClass_SysNick' => $data['EvnClass_SysNick']);
				$vizitCount = $this->dbmodel->getCountChildren($params);

				if (is_numeric($vizitCount) && $vizitCount == 1) {
					$this->load->model('Stick_model', 'Stick_model');
					$lvn_exist = $this->Stick_model->checkLvnExist($params);
					if (!empty($lvn_exist)) {
						return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются выданные листы временной нетрудоспособности'));
					}
				}
			}

		}

		// Получаем класс и идентификатор удаляемого случая с учетом класса события
		foreach ( $evnTreeData as $evnData ) {
			if ( $evnData['Evn_id'] == $data['Evn_id'] ) {
				if ( $evnData['Lpu_id'] != $data['Lpu_id'] ) {
					return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. он был создан в другой МО'));
				}

				$data['EvnClass_SysNick'] = $evnData['EvnClass_SysNick'];
				$data[$evnData['EvnClass_SysNick'] . '_id'] = $evnData['Evn_id'];
				$data['Person_id'] = $evnData['Person_id'];
				break;
			}
		}

		// Проверяем, чтобы в случае не было ЛВН, не было подписанных документов, не было рецептов, не было выполненного назначения, не было списания медикаментов
		// https://redmine.swan.perm.ru/issues/18053
		// https://redmine.swan.perm.ru/issues/47549
		// https://redmine.swan.perm.ru/issues/73268
		// https://redmine.swan.perm.ru//issues/109266
		foreach ( $evnTreeData as $evnData ) {
			// Проверяем признак подписания документа
			if ( $evnData['Evn_IsSigned'] == 2 ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются подписанные документы'));
			}
			// Наличие дочерних ЛВН в случае, если удаляем не ЛВН
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnStick', 'EvnStickDop', 'EvnStickStudent')) && in_array($evnData['EvnClass_SysNick'], array('EvnStick', 'EvnStickDop', 'EvnStickStudent')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются выданные листы временной нетрудоспособности'));
			}
			// Наличие рецептов в случае, если удаляем не рецепт
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnRecept')) && in_array($evnData['EvnClass_SysNick'], array('EvnRecept')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Удаление документа невозможно, т.к. в рамках случая имеются выписанные рецепты'));
			}
			// Наличие назначений, если удаляем не назначение
			else if ( !preg_match('/^EvnPrescr/', $data['EvnClass_SysNick']) && preg_match('/^EvnPrescr/', $evnData['EvnClass_SysNick']) && !in_array($evnData['EvnClass_SysNick'], array('EvnPrescrMse', 'EvnPrescrVK')) ) {
				// @task https://redmine.swan.perm.ru/issues/74589
				return array(array('Error_Code' => 1, 'Error_Msg' => 'Удаление документа невозможно, т.к. в рамках случая имеются назначения'));
				/*$this->load->model('EvnPrescr_model');
				$response = $this->EvnPrescr_model->getEvnPrescrIsExec(array('EvnPrescr_id' => $evnData['Evn_id'], 'EvnClass_SysNick' => $evnData['EvnClass_SysNick']));
				if (!is_array($response) || !isset($response[0]) || !isset($response[0]['EvnPrescr_IsExec'])) {
					return array(array('Error_Code' => 10, 'Error_Msg' => 'Ошибка при получении статуса выполнения назначения'));
				}
				if ($response[0]['EvnPrescr_IsExec'] == 2) {
					return array(array('Error_Code' => 1, 'Error_Msg' => 'Удаление документа невозможно, т.к. в рамках случая имеются выполненные назначения'));
				}*/
			}
			// Наличие списания медикаментов в случае, если удаляем не факт списания медикамента
			else if ( !in_array($data['EvnClass_SysNick'], array('EvnDrug')) && in_array($evnData['EvnClass_SysNick'], array('EvnDrug')) ) {
				return array(array('Error_Code' => 1,'Error_Msg'=>'Данные о случаях лечения, содержащие сведения об использовании медикаментов, не могут быть удалены'));
			}
		}

		// Проверка есть ли в реестрах записи об этом случае
		if ( in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection')) ) {
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');

			// Соединение с реестровой БД происходит в методе checkEvnAccessInRegistry
			$registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

			if (is_array($registryData)) {
				return array($registryData);
			}
		}

		//Проверка посещения стоматологии или поликлиники на наличие вызова на дом
		if (getRegionNick() != 'kz' && in_array($data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom'))
			&& (!isset($data['ignoreHomeVizit']) || $data['ignoreHomeVizit'] == false)) {

			$this->load->model('HomeVisit_model', 'HomeVisit_model');
			$checkResult = $this->HomeVisit_model->checkHomeVizit($data);
			if ($checkResult) {
				return array(
					array(
						'Alert_Code' => 808,
						'Alert_Msg' => 'Для данного посещения имеется обслуженный вызов на дом. В случае удаления посещения, статус "Обслужено" будет снят с вызова, и вызову будет присвоен предыдущий статус.'
					)
				);
			}
		}

		//проверка ТАП поликлиники или стоматологии на наличие посещений с вызовами на дом
		if (getRegionNick() != 'kz' && in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom'))
			&& (!isset($data['ignoreHomeVizit']) || $data['ignoreHomeVizit'] == false)) {

			$this->load->model('HomeVisit_model', 'HomeVisit_model');
			$checkResults = [];
			foreach ($evnTreeData as $one) {
				$temp = $this->HomeVisit_model->checkHomeVizit($one);

				if ($temp) {
					$checkResults[] = $temp;
					break;
				}

			}

			if (!empty($checkResults[0])) {
				return array(
					array(
						'Alert_Code' => 809,
						'Alert_Msg' => 'У посещений, которые входят в ТАП, имеются обслуженные вызовы на дом. В случае удаления посещений, статус "Обслужено" будет снят с вызова, и вызову будет присвоен предыдущий статус. Удалить ТАП?'
					)
				);
			}
		}

		// Проверка, есть ли в стомат. посещении услуги
		if ( in_array($data['EvnClass_SysNick'], array('EvnVizitPLStom')) ) {
			$this->load->model('EvnVizitPLStom_model', 'EvnVizitPLStom_model');
			$checkResult = $this->EvnVizitPLStom_model->checkEvnUslugaStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}

			$checkResult = $this->EvnVizitPLStom_model->checkEvnDiagPLStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}
		}

		// Проверка, есть ли в стомат. заболевании услуги
		if ( in_array($data['EvnClass_SysNick'], array('EvnDiagPLStom')) ) {
			$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
			$checkResult = $this->EvnDiagPLStom_model->checkEvnUslugaStomCount($data);

			if ( !empty($checkResult) ) {
				return array(
					array('Error_Msg' => $checkResult)
				);
			}
		}
		
		// Проверка на связь с анкетой по беременности 
		if( in_array($data['EvnClass_SysNick'],array('EvnPL','EvnVizitPL')) && getRegionNick()=='khak'){
			$msgErr = $data['EvnClass_SysNick']=='EvnPL' ? 'Удаление случая лечения невозможно, так как с ним связана анкета в регистре по беременности.':'Удаление посещения невозможно, так как с ним связана анкета в регистре по беременности.';
			$this->load->model('PersonPregnancy_model');
			foreach ( $evnTreeData as $evnData ) {				
				$result=$this->PersonPregnancy_model->checkLinkEvn($evnData);
				if ($result>0){
					return array(array('Error_Code' => 1,'Error_Msg'=>$msgErr));	
				} 				
			}			
		}

		$error_arr = $this->dbmodel->onBeforeDelete($data, $evnTreeData);

		if ( is_array($error_arr) ) {
			return $error_arr;
		}

		return true;
	}

	/**
	 *	Проверка на доступность редактирования события
	 */
	function CommonChecksForEdit() {
	
		$data = $this->ProcessInputData('CommonChecksForEdit', true);
		if ( $data === false ) { return false; }

		if(empty($data['isCMPCloseCard'])) {
			// Получаем список связанных событий
			$evnTreeData = $this->dbmodel->getRelatedEvnList($data);

			if ( !is_array($evnTreeData) || count($evnTreeData) == 0 ) {
				$this->ReturnError('Ошибка при получении списка связанных событий');
				return false;
			}

			// Получаем класс и идентификатор удаляемого случая с учетом класса события
			foreach ( $evnTreeData as $evnData ) {
				if ( $evnData['Evn_id'] == $data['Evn_id'] ) {

					$data['EvnClass_SysNick'] = $evnData['EvnClass_SysNick'];
					$data[$evnData['EvnClass_SysNick'] . '_id'] = $evnData['Evn_id'];
					break;
				}
			}
		} else {
			if(!empty($data['isForm']) && ($data['isForm'] == 'CmpCallCardEditWindow') ) {
				$data['CmpCallCard_id'] = $data['Evn_id'];
			}else{
				if($this->dbmodel->getRegionNick() != 'kareliya') {
				    $data['CmpCloseCard_id'] = $data['Evn_id'];
                    $data['EvnClass_SysNick'] = 'CmpCloseCard';
                }
				else {
					$data['CmpCallCard_id'] = $data['Evn_id'];
				}
			}
			$evnTreeData = null;
		}

		// Проверка есть ли в реестрах записи об этом случае
		// Только для Бурятии
		// и Карелии https://redmine.swan.perm.ru/issues/74209
		if ( in_array($this->dbmodel->getRegionNick(), array('buryatiya'/*,'kareliya'*/)) && in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPS', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection', 'EvnUslugaPar', 'CmpCloseCard')) ) {
			// Цепляем реестровую БД
			$this->db = null;
			$this->load->database('registry');
			$this->load->model('Registry_model', 'Reg_model');

			$registryData = $this->Reg_model->checkEvnInRegistry($data, 'edit');

			if ( is_array($registryData)&& count($registryData) > 0 && !empty($registryData[0]['Error_Msg']) ) {
				$this->ReturnError($registryData[0]['Error_Msg']);
				return false;
			}
			/*
			// если есть реестр, помечаем его нуждающимся в переформировании
			$resposne = $this->Reg_model->setRegistryIsNeedReformByEvnId(array(
				'Evn_id' => $data['Evn_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			*/
			// Цепляем рабочую БД
			$this->db = null;
			$this->load->database();
		}

		if ( $this->dbmodel->getRegionNick() == 'ufa' ) {
			$this->load->model('RegistryUfa_model', 'Reg_model');
		}
		else {
			$this->load->model('Registry_model', 'Reg_model');
		}

		if (getRegionNick() == 'vologda' && isset($data['EvnClass_SysNick'])){

			if(in_array($data['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom'))) {
				$IsFinish = $this->dbmodel->getIsFinish([
					'Evn_id' => $data['Evn_id'],
					'Evn' => $data['EvnClass_SysNick']
				]);
			}

			if(in_array($data['EvnClass_SysNick'], array('EvnPS'))) {
				$IsFinishPS = $this->dbmodel->getIsFinish([
					'Evn_id' => $data['Evn_id'],
					'Evn' => 'EvnPS'
				]);
			}
		}

		if (getRegionNick() != 'vologda' || (!empty($IsFinish) && $IsFinish == 2) || !empty($IsFinishPS)) {
			
			$registryData = $this->Reg_model->checkEvnAccessInRegistry($data, 'edit');
			
			if (is_array($registryData)) {
				if (!empty($registryData['Error_Msg'])) {
					$this->ReturnError($registryData['Error_Msg']);
					return false;
				} elseif (!empty($registryData['Alert_Msg'])) {
					$this->ReturnData($registryData);
					return false;
				}
			}
		}

		if(empty($data['isCMPCloseCard'])) {
			// эта логика не нужна для карт СМП
			$error_arr = $this->dbmodel->CommonChecksForEdit($data, $evnTreeData);

			if (is_array($error_arr) && count($error_arr) > 0 && !empty($error_arr[0]['Error_Msg'])) {
				$this->ReturnError($error_arr[0]['Error_Msg']);
				return false;
			}
		}
		
		$this->ReturnData(['success' => true]);
		return true;
	}

	/**
	 * Получение журнала событий
	 */
	function getEvnJournal() {
		$data = $this->ProcessInputData('getEvnJournal', true);
		if (!$data) {
			return false;
		}

		$response = $this->dbmodel->getEvnJournalData($data);

		$this->load->model('PersonNotice_model', 'pnmodel');
		$response['notice'] = $this->pnmodel->getPersonNotice($data);
		$response['isMseDepers'] = $data['isMseDepers'] == 1;

		$this->load->library('parser');

		$html = $this->parser->parse('evn_journal_view', $response, true);

		ConvertFromWin1251ToUTF8($html);
		array_walk_recursive($response,'ConvertFromWin1251ToUTF8');
		$result = array('success'=>true, 'html' => $html, 'data' => $response);
		$this->ReturnData($result);

		return true;
	}

	/**
	 * Получение данных событий
	 */
	function getEvnData() {
		$data = $this->ProcessInputData('getEvnData', true);
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->getDataByEvn($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение родительского события
	 */
	function getParentEvn() {
		$data = $this->ProcessInputData('getParentEvn', true);
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->getParentEvn($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Проверка на последний визит в АПЛ
	 * @return bool
	 */
	function checkOnOnlyOneExist() {
		$data = $this->ProcessInputData('checkOnOnlyOneExist', true);
		if (!$data) {
			return false;
		}
		$response = $this->dbmodel->checkOnOnlyOneExist($data);

		if ($response[0]['count'] > 1) {
			$response = $data['EvnVizitPL_id'];
		} else {
			$response = $response[0];
		}

		$this->ReturnData($response);

		return true;
	}
}
