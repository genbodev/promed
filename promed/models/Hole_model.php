<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Hole_model - Модель для работы с лункой планшета
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      28.11.2019
 */

require_once('Scenario_model.php');
class Hole_model extends Scenario_model {
	var $table_name = 'Hole';
	var $scheme = 'dbo';
	const SCENARIO_ADD_USLUGATESTS = 'addUslugaTests';
	const setEmptyControlHole = 'setEmptyControlHole';
	const createControlHole = 'createControlHole';
	const clearHole = 'clearHole';
	const setDefect = 'setDefect';
	const setCalibrator = 'setCalibratorHole';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DO_SAVE_JSON_MULTIPLE,
			self::SCENARIO_ADD_USLUGATESTS,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DELETE,
			self::setEmptyControlHole,
			self::clearHole,
			self::createControlHole,
			self::setDefect,
			self::setCalibrator
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'Hole_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'tablet_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_id',
				'label' => 'Планшет',
				'save' => '',
				'type' => 'int'
			),
			'hole_number' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Hole_Number',
				'label' => 'Номер лунки',
				'save' => '',
				'type' => 'int'
			),
			'holestate_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'HoleState_id',
				'label' => 'Состояние лунки',
				'save' => '',
				'type' => 'int'
			),
			'hole_isdefect' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Hole_IsDefect',
				'label' => 'Брак лунки',
				'save' => '',
				'type' => 'int'
			),
			'hole_defectdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Hole_defectDT',
				'label' => 'Дата отбраковки',
				'save' => '',
				'type' => 'datetime'
			),
			'defectcausetype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'DefectCauseType_id',
				'label' => 'Причина брака',
				'save' => '',
				'type' => 'int'
			),
			'hole_comment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Hole_Comment',
				'label' => 'Комментарий',
				'save' => '',
				'type' => 'string'
			),
			'uslugatest_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'UslugaTest_id',
				'label' => 'Тест',
				'save' => '',
				'type' => 'int'
			),
			'data' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'label' => 'JSON Массив для сохранения',
				'type' => 'string'
			),
			'uslugatest_ids' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'UslugaTest_ids',
				'label' => 'JSON Массив UslugaTest_id',
				'type' => 'string'
			)
		);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function doLoadGrid($data = array())
	{
		$params = [
			'Tablet_id' => $data['Tablet_id']
		];

		$allow_encryp = allowPersonEncrypHIV() ? '1' : '0';

		$query = "
			select
				H.Hole_id,
				H.Tablet_id,
				H.Hole_Number,
				H.HoleState_id,
				H.Hole_IsDefect,
				H.Hole_defectDT,
				H.DefectCauseType_id,
				DCT.DefectCauseType_Name,
				H.Hole_Comment,
				H.UslugaTest_id,
				HS.HoleState_Name,
				UT.UslugaTest_ResultValue,
				ELS.EvnLabSample_BarCode,
				UC.UslugaComplex_Name,
				RF.RefValues_id,
				RF.RefValues_UpperLimit,
				RF.RefValues_LowerLimit,
				case when {$allow_encryp} = 1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp
					else COALESCE(ps.Person_SurName, '') + COALESCE(' '+ SUBSTRING(ps.Person_FirName, 1, 1) + '.','') + COALESCE(' '+ SUBSTRING(ps.Person_SecName,1,1) + '.','')
				end as Person_ShortFio
			from v_Hole H with(nolock)
			left join v_HoleState HS with(nolock) on HS.HoleState_id = H.HoleState_id
			left join v_Tablet T with(nolock) on T.Tablet_id = H.Tablet_id
			left join v_UslugaTest UT with(nolock) on UT.UslugaTest_id = H.UslugaTest_id
			left join v_RefValues RF with(nolock) on RF.RefValues_id = UT.RefValues_id 
			left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = UT.UslugaComplex_id
			left join v_EvnLabSample ELS with(nolock) on ELS.EvnLabSample_id = UT.EvnLabSample_id
			left join v_PersonState PS with(nolock) on ELS.Person_id = PS.Person_id
			left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
			left join lis.v_DefectCauseType DCT with(nolock) on DCT.DefectCauseType_id = H.DefectCauseType_id
			where T.Tablet_id = :Tablet_id
			order by H.Hole_Number
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 * @throws Exception
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);

		switch($name) {
			case self::SCENARIO_LOAD_GRID:
			case self::SCENARIO_LOAD_COMBO_BOX:
			case self::createControlHole:
				$attributes = [ 'tablet_id' ];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_ADD_USLUGATESTS:
				$attributes = [ 'tablet_id', 'uslugatest_ids' ];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::setEmptyControlHole:
			case self::clearHole:
			case self::setCalibrator:
				$attributes = [ self::ID_KEY ];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::setDefect:
				$attributes = [ self::ID_KEY, 'defectcausetype_id', 'hole_comment' ];
				$rules = $this->getInputRulesByAttributes($attributes);
		}
		
		return $rules;
	}

	/**
	 * Проверка на забракованность проб
	 * @param $UslugaTest_ids
	 * @throws Exception
	 */
	function isSampleDefect($UslugaTest_ids) {
		$query = "
			select count(UT.UslugaTest_id)
			from v_UslugaTest UT with(nolock)
			inner join v_EvnLabSample ELS with(nolock) on ELS.EvnLabSample_id = UT.EvnLabSample_id
			where ELS.DefectCauseType_id is not null and UT.UslugaTest_id in ({$UslugaTest_ids})
		";

		$defectCount = $this->getFirstResultFromQuery($query);

		if(!is_int($defectCount)) {
			throw new Exception('Не удалось проверить статус пробы');
		}

		if($defectCount > 0) {
			throw new Exception('Нельзя добавить пробу из числа забракованных');
		}
	}

	/**
	 * @param $Tablet_id
	 * @return array|false
	 */
	function getEmptyHoles($Tablet_id) {
		$params = [
			'Tablet_id' => $Tablet_id,
			'HoleState_id' => 1
		];
		$query = "
			select
				Hole_id,
				Hole_Number,
				Hole_IsDefect
			from v_Hole
			where Tablet_id = :Tablet_id and HoleState_id = :HoleState_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Добавление тестов в лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doAddUslugaTest($data) {

		$uslugatest_ids = [];
		$holes = [];

		if (empty($data['Tablet_id'])) {
			return $this->getErrorMessage('Планшет не определен');
		}
		$Tablet_id = $data['Tablet_id'];

		//загрузка модели и данных планшета
		$this->load->model('Tablet_model');
		$TabletObj = $this->Tablet_model;
		$TabletObj->_load($Tablet_id);

		$Tablet_isTabletTest = $TabletObj->getAttribute('tablet_istesttablet');
		$Tablet_isDoublesFill = $TabletObj->getAttribute('tablet_isdoublesfill');
		$Tablet_HorizSize = $TabletObj->getAttribute('tablet_horizsize');
		$Tablet_VertSize = $TabletObj->getAttribute('tablet_vertsize');

		//проверка на брак
		$defectDT = $TabletObj->getAttribute('tablet_defectdt');
		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		if(empty($data['UslugaTest_ids'])) {
			return $this->getErrorMessage('Не переданы тесты');
		}

		if(is_array($data['UslugaTest_ids'])) {
			$uslugatest_ids = $data['UslugaTest_ids'];
		} else if (!empty($data['UslugaTest_ids'])) {
			$uslugatest_ids = json_decode($data['UslugaTest_ids'], true);
		};

		$countUslugaTests = count($uslugatest_ids);
		
		if (!$countUslugaTests) {
			return $this->getErrorMessage('Не переданы тесты');
		}

		//получаем пустые лунки
		$holeObjs = $this->getEmptyHoles($Tablet_id);

		$allEmptyHolesCount = count($holeObjs);

		//размещаем столько тестов сколько возможно
		//для тестов которые не вошли в этот планшет создадим новый
		$Tablet_HoleCount = $TabletObj->getAttribute('tablet_holecount');
		if($Tablet_isTabletTest == 2) {
			//todo количество свободных = общее - отбраковынных - тестовых - занятых
			//$emptyHolesCount = $allEmptyHolesCount - $Tablet_HoleCount;
			//пока так
			$emptyHolesCount = $allEmptyHolesCount;
		} else if($Tablet_isDoublesFill == 2) {
		//если планшет с дублями то:  тестов*2 < лунок
			$emptyHolesCount =  floor($allEmptyHolesCount / 2); //todo подумать для нечетного количества свободных лунок
		} else {
			$emptyHolesCount = $allEmptyHolesCount;
		}

		//разобъем массив тестов по количеству пустых ячеек в планшете, остальное отправим в новый
		$currentUslugaTestIds = array_slice($uslugatest_ids,0, $emptyHolesCount);

		$otherUslugaTestsIds = array_slice($uslugatest_ids, $emptyHolesCount, count($uslugatest_ids));

		$strUslugaTestIds = implode(',', $currentUslugaTestIds);

		//если проба забракована то выкинем исключение
		$this->isSampleDefect($strUslugaTestIds);

		$this->checkDoubles($Tablet_id, $strUslugaTestIds);

		//формирование лунок для сохранения
		$i = 0;
		foreach ($currentUslugaTestIds as $UslugaTest_id) {
			$holeObj = $holeObjs[$i++];
			$hole = [
				'UslugaTest_id' => $UslugaTest_id,
				'Hole_id' => $holeObj['Hole_id'],
				'HoleState_id' => 4,
				'Tablet_id' => $Tablet_id,
				'Hole_Number' => $holeObj['Hole_Number']
			];
			$holes[] = $hole;
			//для планшета с дублями формируем по 2 записи
			if($Tablet_isDoublesFill == 2) {
				$holeObj = $holeObjs[$i++];
				$hole['Hole_id'] = $holeObj['Hole_id'];
				$hole['Hole_Number'] = $holeObj['Hole_Number'];
				$holes[] = $hole;
			}
		}

		$params = [
			'data' => $holes
		];

		//сохранение массива лунок без, трансакции тк уже запущено
		$result = $this->doSaveMultiple($params, false);
		if (!$result || !$result['success']) {
			return $this->getErrorMessage($result['Error_Msg'],$result['Error_Code']);
		}

		//остальную часть тестов сохраняем в новый планшет
		if(!count($otherUslugaTestsIds)) {
			return $result;
		}

		//создаем копию планшета
		$params = [];
		$params['Tablet_id'] = $Tablet_id;
		$params['Tablet_IsTestTablet'] = 1;
		$result = $TabletObj->doCreateChild($params);
		if(empty($result['success'])) {
			return $this->getErrorMessage($result['Error_Msg'],$result['Error_Code']);
		}

		//сохраняем оставшиеся тесты
		$data['Tablet_id'] = $result['Tablet_id'];
		$data['UslugaTest_ids'] = $otherUslugaTestsIds;
		$result = $this->doAddUslugaTest($data);
		if(empty($result['success'])) {
			return $this->getErrorMessage($result['Error_Msg'],$result['Error_Code']);
		}
		return $result;
	}

	/**
	 * Проверка на дубли в планшете
	 * @param $Tablet_id
	 * @param $UslugaTest_ids
	 * @return void
	 * @throws Exception
	 */
	function checkDoubles($Tablet_id, $UslugaTest_ids) {
		$allow_encryp = allowPersonEncrypHIV() ? '1' : '0';

		$query = "
				select H.Hole_id,
					case when {$allow_encryp} = 1 and PEH.PersonEncrypHIV_id is not null
						then PEH.PersonEncrypHIV_Encryp
						else COALESCE(ps.Person_SurName, '') + COALESCE(' '+ SUBSTRING(ps.Person_FirName, 1, 1) + '.','') + COALESCE(' '+ SUBSTRING(ps.Person_SecName,1,1) + '.','')
					end as Person_ShortFio
				from v_Hole H with(nolock)
				left join v_UslugaTest UT with(nolock) on UT.UslugaTest_id = H.UslugaTest_id
				left join v_EvnLabSample ELS with(nolock) on ELS.EvnLabSample_id = UT.EvnLabSample_id
				left join v_PersonState PS with(nolock) on ELS.Person_id = PS.Person_id
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
				where H.Tablet_id = :Tablet_id and H.UslugaTest_id in ({$UslugaTest_ids})
			";

		$result = $this->queryResult($query, ['Tablet_id' => $Tablet_id]);
		if (!is_array($result)) {
			throw new Exception('Ошибка при валидации');
		}
		if (count($result)) {
			$msg = ' ';
			foreach ($result as $obj) {
				$msg .= $obj['Person_ShortFio'] . '<br>';
			}
			throw new Exception('На планшете уже есть данные пробы пациентов: ' . $msg);
		}
	}
	
	/**
	 * Добавление тестов в лунки
	 * @param $data
	 * @return array
	 */
	function addUslugaTests($data) {
		return $this->doTransaction($data,'doAddUslugaTest');
	}
	
	/**
	 * Получение первой не заполненной лунки
	 * @param $Tablet_id
	 * @return bool|float|int|string
	 */
	function getFirstEmptyHole($Tablet_id) {
		$params = [
			'Tablet_id' => $Tablet_id
		];
		$query = "
			select top 1 Hole_id
			from v_Hole
			where Tablet_id = :Tablet_id and HoleState_id = 1
			order by Hole_Number
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Установление пустой контрольной лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doEmptyControlHole($data) {
		if(empty($data['Hole_id'])) {
			throw new Exception('Не передан идентификатор');
		}

		$id = $data['Hole_id'];

		$this->_requestSavedData($id);
		$tablet_id = $this->getAttribute('tablet_id');

		$this->load->model('Tablet_model');
		$this->Tablet_model->_load($tablet_id);
		$defectDT = $this->Tablet_model->getAttribute('tablet_defectdt');
		
		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$firstEmptyHoleId = $this->getFirstEmptyHole($tablet_id);
		
		if($id != $firstEmptyHoleId) {
			throw new Exception('Доступно только для первой пустой лунки');
		}
		
		$params = [
			'HoleState_id' => 2,
			'Hole_id' => $id
		];
		
		return $this->SafelyUpdate($params, false);
	}
	
	
	/**
	 * Получаем лунку которую можно удалить
	 * @param $Tablet_id
	 * @return bool|float|int|string
	 */
	function getHoleIdForClear($Tablet_id) {
		$params = [
			'Tablet_id' => $Tablet_id
		];
		$query = "
			select top 1
				Hole_id
			from v_Hole H with(nolock)
			left join v_UslugaTest UT with(nolock) on UT.UslugaTest_id = H.UslugaTest_id
			where H.Tablet_id = :Tablet_id and isnull(H.HoleState_id,1) <> 1
			order by H.Hole_Number desc
		";
		$Hole_id = $this->getFirstResultFromQuery($query, $params);
		return $Hole_id;
	}
	
	/**
	 * Получение первой лунки
	 * @param $Tablet_id
	 * @return bool|float|int|string
	 */
	function getFirstHoleId($Tablet_id) {
		$params = [
			'Tablet_id'=> $Tablet_id
		];
		$query = "
			select top 1 Hole_id
			from v_Hole
			where Tablet_id = :Tablet_id and Hole_Number = 1
		";
		return $this->getFirstResultFromQuery($query,$params);
	}
	
	/**
	 * получение первой пустой лунки
	 * @param $Tablet_id
	 * @return array|false
	 */
	function getFirstEmptyHoleId($Tablet_id) {
		$params = [
			'Tablet_id'=>$Tablet_id
		];

		$query = "
			select top 1 Hole_id
			from v_Hole with(nolock)
			where Tablet_id = :Tablet_id and HoleState_id in (1,2)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Очистка лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doClearHole($data) {
		if(empty($data['Hole_id'])) {
			throw new Exception('Не передан идентификатор');
		}

		$id = $data['Hole_id'];

		$this->_requestSavedData($id);
		$tablet_id = $this->getAttribute('tablet_id');

		$this->load->model('Tablet_model');
		$this->Tablet_model->_load($tablet_id);
		$defectDT = $this->Tablet_model->getAttribute('tablet_defectdt');

		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$holeIdForClear = $this->getHoleIdForClear($tablet_id);

		if($id != $holeIdForClear) {
			throw new Exception('Можно отчистить только последнюю лунку без результата.');
		}

		$params = [
			'Hole_id'=>$id,
			'HoleState_id' => 1,
			'UslugaTest_id' => null,
			'Hole_defectDT' => null,
			'Hole_IsDefect' => null,
			'DefectCauseType_id' => null,
			'Hole_Comment' => null
		];
		return $this->SafelyUpdate($params, false);
	}
	
	/**
	 * Создание контрольной лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doCreateControlHole($data) {

		if(empty($data['Tablet_id'])) {
			throw new Exception('Не указан планшет');
		}
		$tablet_id = $data['Tablet_id'];

		$this->load->model('Tablet_model');
		$this->Tablet_model->_load($tablet_id);
		$defectDT = $this->Tablet_model->getAttribute('tablet_defectdt');
		
		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$HoleId = $this->getFirstEmptyHole($data['Tablet_id']);
		$params = [
			'Hole_id' => $HoleId,
			'HoleState_id' => 3
		];

		return $this->SafelyUpdate($params, false);
	}

	/**
	 * Установление пустой контрольной лунки
	 * @param $data
	 * @return array
	 */
	function setEmptyControlHole($data) {
		return $this->doTransaction($data, 'doEmptyControlHole');
	}
	
	/**
	 * Очистка лунки
	 * @param $data
	 * @return array
	 */
	function clearHole($data) {
		return $this->doTransaction($data, 'doClearHole');
	}
	
	/**
	 * Создание пустой контрольной лунки
	 * @param $data
	 * @return array
	 */
	function createControlHole($data) {
		return $this->doTransaction($data, 'doCreateControlHole');
	}
	
	/**
	 * Установление брака лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doSetDefect($data) {
		if(empty($data['Hole_id'])) {
			throw new Exception('Не передан идентификатор');
		}

		$id = $data['Hole_id'];

		if(empty($data['DefectCauseType_id'])) {
			return $this->getErrorMessage('Не передана причина брака');
		}

		$this->_requestSavedData($id);
		$tablet_id = $this->getAttribute('tablet_id');
		
		$this->load->model('Tablet_model');
		$this->Tablet_model->_load($tablet_id);
		$defectDT = $this->Tablet_model->getAttribute('tablet_defectdt');
		
		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$params = [
			'Hole_id' => $id,
			'Hole_defectDT' => $this->getCurrentDT(),
			'UslugaTest_id' => null,
			'Hole_IsDefect' => 2,
			'DefectCauseType_id' => $data['DefectCauseType_id'],
			'Hole_Comment' => $data['Hole_Comment'],
			'HoleState_id' => 5
		];

		return $this->SafelyUpdate($params,false);
	}

	/**
	 * Установление брака лунки
	 * @param $data
	 * @return array
	 */
	function setDefect($data) {
		return $this->doTransaction($data, 'doSetDefect');
	}

	/**
	 * Получение первой лунки с тестом
	 * @param $Tablet_id
	 * @return array
	 * @throws Exception
	 */
	function getFirstHoleWithTest($Tablet_id) {
		$params = [];
		$params['Tablet_id'] = $Tablet_id;
		$query = "
			select Hole_id
			from v_Hole with(nolock)
			where UslugaTest_id is not null
			and Tablet_id = :Tablet_id
		";
		$result =  $this->queryResult($query,$params);
		if(!is_array($result)) {
			throw new Exception('Ошибка при поиске лунки');
		}
		return $result;
	}

	/**
	 * Установка лунки калибратора
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function doSetCalibratorHole($data) {
		if(empty($data['Hole_id'])) {
			return $this->getErrorMessage('Не передан идентификатор');
		}

		$id = $data['Hole_id'];

		$this->_requestSavedData($id);
		$tablet_id = $this->getAttribute('tablet_id');

		if($this->getAttribute('holestate_id') != 1) {
			return $this->getErrorMessage('Доступно только для пустой лунки');
		}

		$this->load->model('Tablet_model');
		$this->Tablet_model->_load($tablet_id);
		$defectDT = $this->Tablet_model->getAttribute('tablet_defectdt');

		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$params = [
			'HoleState_id' => 7,
			'Hole_id' => $id
		];

		return $this->SafelyUpdate($params, false);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function setCalibratorHole($data) {
		return $this->doTransaction($data, 'doSetCalibratorHole');
	}
}