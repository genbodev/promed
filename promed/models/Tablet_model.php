<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * Tablet_model - модель для работы с формой "Выбор планшета" (swTabletWindow)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      15.11.2019
 */

require_once('Scenario_model.php');
class Tablet_model extends Scenario_model
{
	var $table_name = 'Tablet';
	var $scheme = 'dbo';
	var $saveAsNewObject = true;
	const setDefect = 'setDefect';
	const createChild = 'createChild';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_LOAD_COMBO_BOX,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
			self::setDefect,
			self::createChild
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'Tablet_id',
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'id'
			),
			'tablet_ishorizfill' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_IsHorizFill',
				'label' => 'Методика ИФА',
				'save' => '',
				'type' => 'int'
			),
			'tablet_isdoublesfill' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_IsDoublesFill',
				'label' => 'Тест',
				'save' => '',
				'type' => 'int'
			),
			'tablet_istesttablet' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_IsTestTablet',
				'label' => 'Проверочный планшет',
				'save' => '',
				'type' => 'int'
			),
			'tablet_barcode' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_Barcode',
				'label' => 'Штрихкод',
				'save' => '',
				'type' => 'int'
			),
			'methodsifatablettype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MethodsIFATabletType_id',
				'label' => 'Связь типа планшета и методики',
				'save' => '',
				'type' => 'int'
			),
			'analyzer_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Analyzer_id',
				'label' => 'Анализатор',
				'save' => '',
				'type' => 'int'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedService_id',
				'label' => 'Служба',
				'save' => '',
				'type' => 'int'
			),
			'tablet_vertsize' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_VertSize',
				'label' => 'Размер по вертикали',
				'save' => '',
				'type' => 'int'
			),
			'tablet_horizsize' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_HorizSize',
				'label' => 'Размер по горизонтали',
				'save' => '',
				'type' => 'int'
			),
			'tablet_holecount' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_HoleCount',
				'label' => 'Количество лунок для проверочного теста',
				'save' => '',
				'type' => 'int'
			),
			'labsamplestatus_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'LabSampleStatus_id',
				'label' => 'Статус',
				'save' => '',
				'type' => 'int'
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
			'tablet_defectdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_defectDT',
				'label' => 'Дата установки брака',
				'save' => '',
				'type' => 'datetime'
			),
			'tablet_comment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Tablet_Comment',
				'label' => 'Комментарий',
				'save' => '',
				'type' => 'string'
			),
			'methodsifa_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'MethodsIFA_id',
				'label' => 'Методика ИФА',
				'type' => 'int'
			),
			'tabletstatus' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'tabletStatus',
				'label' => 'Статус планшета',
				'type' => 'string'
			),
			'analyzertest_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'AnalyzerTest_id',
				'label' => 'Тест',
				'type' => 'int'
			),
			'tablet_pid' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'Tablet_pid',
				'label' => 'Признак ',
				'type' => 'int'
			)
		);
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 * @throws Exception
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);

		switch ($name) {
			case self::createChild:
				$rules = $this->getInputRulesByAttributes(self::ID_KEY);
				break;
			case self::SCENARIO_LOAD_COMBO_BOX:
				$rules = $this->getInputRulesByAttributes('methodsifa_id');
				break;
			case self::setDefect:
				$attributes = [
					self::ID_KEY,
					'tablet_comment',
					'defectcausetype_id'
				];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_LOAD_GRID:
				$attributes = [
					'analyzertest_id',
					'methodsifa_id',
					'tabletstatus',
					'medservice_id'
				];
				$rules = $this->getInputRulesByAttributes($attributes);
		}

		return $rules;
	}

    /**
     * Загрузка формы и грида (Контроль качества/Контрольные материалы)
     * @param array $data
     * @return array|false
     */
	function doLoadCombo($data = array())
	{
		$params = [];
		$where = '';

		if(!empty($data['MethodsIFA_id'])) {
			$params['MethodsIFA_id'] = $data['MethodsIFA_id'];
			$where = ' and MITT.MethodsIFA_id = :MethodsIFA_id';
		}

		$query = "
			select
				MITT.MethodsIFATabletType_id,
				TT.TabletType_id,
				TT.TabletType_Code,
				TT.TabletType_VertSize,
				TT.TabletType_HorizSize
			from v_TabletType TT with(nolock)
			inner join v_MethodsIFATabletType MITT with(nolock) on TT.TabletType_id = MITT.TabletType_id
			where (1=1) {$where}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка данных для грида в арме лаборанта
	 * @param array $data
	 * @return array|false
	 * @throws Exception
	 */
	function doLoadData($data = array()) {
		if(empty($data['MedService_id'])) {
			throw new Exception('Служба не определена');
		}

		$params = [];
		$where = [];
		$where[] = '1=1';
		$join = "";

		$params['MedService_id'] = $data['MedService_id'];
		$where[] = "T.MedService_id = :MedService_id";

		if(!empty($data['MethodsIFA_id'])) {
			$params['MethodsIFA_id'] = $data['MethodsIFA_id'];
			$where[] = 'MITT.MethodsIFA_id = :MethodsIFA_id';
		}

		if(!empty($data['AnalyzerTest_id'])) {
			$params['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
			$join .= 'inner join v_MethodsIFAAnalyzerTest MIAT with(nolock) on MIAT.MethodsIFA_id = MI.MethodsIFA_id and MIAT.AnalyzerTest_id = :AnalyzerTest_id';
		}

		switch($data['tabletStatus']) {
			case 'defect':
				$where[] = "T.Tablet_defectDT is not null";
				break;
			case 'accepted':
				$where[] = "T.Tablet_defectDT is null";
				$where[] = "accepted.Hole_id is not null";
				break;
			case 'result':
				$where[] = "T.Tablet_defectDT is null";
				$where[] = "accepted.Hole_id is null";
				$where[] = "withResult.Hole_id is not null";
				break;
			case 'work':
				$where[] = "T.Tablet_defectDT is null";
				$where[] = "accepted.Hole_id is null";
				$where[] = "withResult.Hole_id is null";
				$where[] = "withTest.Hole_id is not null";
				break;
		}

		$where = implode(' and ', $where);

		$query = "
			select
				T.Tablet_id,
				T.Tablet_HoleCount,
				T.Tablet_VertSize,
				T.Tablet_HorizSize,
				T.Tablet_Barcode,
				T.Tablet_IsHorizFill,
				T.Tablet_defectDT,
				T.Tablet_IsDoublesFill,
				MI.MethodsIFA_Name,
				MI.MethodsIFA_id,
				emptyHoles.emptyHolesCount,
				case
				    when T.Tablet_defectDT is not null
						then 'Забракован'
					when accepted.Hole_id is not null
						then 'Одобрен'
					when withResult.Hole_id is not null
						then 'Выполнен'
					when withTest.Hole_id is not null
						then 'В работе'
					else 'Новый'
				end as statusName
			from v_Tablet T with(nolock)
			inner join v_MethodsIFATabletType MITT with(nolock) on MITT.MethodsIFATabletType_id = T.MethodsIFATabletType_id
			inner join v_MethodsIFA MI with(Nolock) on MI.MethodsIFA_id = MITT.MethodsIFA_id
			{$join}
			outer apply(
				select top 1 Hole_id
				from v_Hole H with(nolock)
				left join v_UslugaTest UT with(nolock) on UT.UslugaTest_id = H.UslugaTest_id
				where
					H.Tablet_id = T.Tablet_id
					and T.Tablet_defectDT is null
					and (isnull(UT.UslugaTest_ResultApproved,1) = 2)
			) accepted
			outer apply(
				select top 1 Hole_id
				from v_Hole H with(nolock)
				inner join v_UslugaTest UT with(nolock) on UT.UslugaTest_id = H.UslugaTest_id
				where
					H.Tablet_id = T.Tablet_id
					and accepted.Hole_id is null
					and T.Tablet_defectDT is null
					and isnull(UslugaTest_ResultValue,'') <> ''
			) withResult
			outer apply(
				select top 1 Hole_id
				from v_Hole H with(nolock)
				where
					H.Tablet_id = T.Tablet_id
					and T.Tablet_defectDT is null
					and accepted.Hole_id is null
					and withResult.Hole_id is null
					and H.UslugaTest_id is not null
			) withTest
			outer apply (
				select count(Hole_id) as emptyHolesCount
				from v_Hole with(nolock)
				where
					Tablet_id = T.Tablet_id
					and HoleState_id = 1
					and withTest.Hole_id is not null
			) emptyHoles
			where {$where}
			order by Tablet_defectDT
		";

		//echo getDebugSql($query,$params); die;
		return $this->queryResult($query, $params);
	}
	
	/**
	 * Сохранение составных частей объекта
	 * @param array $result
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		$this->load->model('Hole_model');
		$Tablet_id = $this->getAttribute(self::ID_KEY);
		$Tablet_pid = $this->getAttribute('tablet_pid');
		$delResp = $this->deleteHoles($Tablet_id);
		if(!empty($delResp['Error_Msg'])) {
			throw  new Exception($delResp['Error_Msg']);
		}

		$createResp = $this->createHoles($Tablet_id, $Tablet_pid);
		if(!$createResp['success']) {
			throw new Exception($createResp['Error_Msg']);
		}
	}

	/**
	 * Удаляет лунки
	 * @param $Tablet_id
	 * @throws Exception
	 */
	function deleteHoles($Tablet_id) {
		$params = [
			'Tablet_id' => $Tablet_id
		];

		$query = "
			select Hole_id
			from v_Hole
			where Tablet_id = :Tablet_id
		";
		
		$result = $this->queryResult($query,$params);
		
		
		foreach ($result as $hole) {
			$hole['session'] = $this->getSessionParams();
			$hole['pmUser_id'] = $this->getPromedUserId();
			$result = $this->Hole_model->doDelete($hole, false);
			if(!empty($result['Error_Msg'])) {
				throw new Exception('Ошибка при удалении');
			}
		}
	}
	
	/**
	 * Создает лунки для планшета
	 * @param $Tablet_id
	 * @return array
	 * @throws Exception
	 */
	function createHoles($Tablet_id, $Tablet_pid) {
		$vertSize = $this->getAttribute('tablet_vertsize');
		$horizSize = $this->getAttribute('tablet_horizsize');
		$holeCount = $vertSize * $horizSize;
		$isChild = !empty($Tablet_pid);
		$holes = [];
		for($i = 1; $i <= $holeCount; $i++) {
			$holes[] = [
				'Hole_Number' => $i,
				'Tablet_id' => $Tablet_id,
				'HoleState_id' => ($isChild || $i!=1) ? 1 : 2
			];
		}
		
		return $this->Hole_model->doSaveMultiple([ 'data' => $holes ], false);
	}
	
	/**
	 * Загрузка грида
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 * @throws Exception
	 */
	public function doLoadEditForm($data = array()) {
		$params = [
			"Tablet_id"=> $data["Tablet_id"]
		];
		$query = "
			select
				T.Tablet_id,
				T.Tablet_IsHorizFill,
				T.Tablet_IsDoublesFill,
				T.Tablet_IsTestTablet,
				T.Tablet_Barcode,
				MITT.MethodsIFATabletType_id,
				T.Analyzer_id,
				T.MedService_id,
				T.Tablet_VertSize,
				T.Tablet_HorizSize,
				T.Tablet_HoleCount,
				T.LabSampleStatus_id,
				T.DefectCauseType_id,
				T.Tablet_defectDT,
				T.Tablet_Comment,
				MI.MethodsIFA_id,
				case
					when isnull(T.Tablet_IsTestTablet,1) = 2 then 3
					when isnull(T.Tablet_IsDoublesFill,1) = 2 then 2
					else 1
				end as fillTypeRadioValue,
				case
					when T.Tablet_VertSize < TT.TabletType_VertSize then 2
					when T.Tablet_HorizSize < TT.TabletType_HorizSize then 3
					else 1
				end as sizeRadioValue
			from v_Tablet T with(nolock)
			left join v_MethodsIFATabletType MITT with(nolock) on MITT.MethodsIFATabletType_id = T.MethodsIFATabletType_id
			left join v_MethodsIFA MI with(nolock) on MI.MethodsIFA_id = MITT.MethodsIFA_id
			left join v_TabletType TT with(nolock) on TT.TabletType_id = MITT.TabletType_id
			where T.Tablet_id = :Tablet_id
		";
		return $this->queryResult($query, $params);
	}
	
	/**
	 * Установление брака лунки
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doSetDefect($data) {
		$params = [
			'Tablet_id' => $data['Tablet_id'],
			'DefectCauseType_id' => $data['DefectCauseType_id'],
			'Tablet_Comment' => $data['Tablet_Comment'],
			'LabSampleStatus_id' => 5,
			'Tablet_defectDT' => $this->getCurrentDT()
		];
		if(empty($data['DefectCauseType_id'])) {
			return $this->getErrorMessage('Не передана причина брака');
		}
		return $this->SafelyUpdate($params,false);
	}

	/**
	 * Установление статуса дефекта
	 * @param $data
	 * @return array
	 */
	function setDefect($data) {
		return $this->doTransaction($data,'doSetDefect');
	}

	/**
	 * Логика перед удалением
	 * @param array $data
	 * @throws Exception
	 */
	function _beforeDelete($data = array())
	{
		if (!empty($data)) {
			$this->applyData($data);
		}

		//удаление планшетов
		$this->load->model('Hole_model');
		$Hole_id = $this->Hole_model->getFirstHoleWithTest($data['Tablet_id']);
		$tablet_defectdt = $this->getAttribute('tablet_defectdt');
		if($Hole_id || $tablet_defectdt) {
			throw new Exception('Можно удалять планшеты только со статусом "Новый"');
		}
	}

	/**
	 * Удаление лунок после удаления планшета
	 * @param array $result
	 * @throws Exception
	 */
	function _afterDelete($result)
	{
		$this->load->model('Hole_model');
		$Tablet_id = $this->getAttribute(self::ID_KEY);
		$result = $this->deleteHoles($Tablet_id);
		if(!empty($result['Error_Msg'])) {
			throw  new Exception($result['Error_Msg']);
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		if (!empty($data)) {
			$this->applyData($data);
		}
		$defectDt = $this->getAttribute('tablet_defectdt');
		if($defectDt) {
			throw new Exception('Планшет забракован');
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function createChild($data) {
		return $this->doTransaction($data,'doCreateChild');
	}

	/**
	 * Создание дочерней копии планшета
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doCreateChild($data) {
		if(empty($data['Tablet_id'])) {
			return $this->getErrorMessage('Не передан идентификатор');
		}
		$Tablet_id = $data['Tablet_id'];
		$this->_load($Tablet_id);

		$defectDT = $this->getAttribute('tablet_defectdt');
		if($defectDT) {
			return $this->getErrorMessage('Планшет забракован');
		}

		$isTestTablet = $this->getAttribute('tablet_istesttablet');
		if(!empty($data['Tablet_IsTestTablet'])) {
			$isTestTablet = $data['Tablet_IsTestTablet'];
		}

		$params = [
			'Tablet_IsHorizFill' => $this->getAttribute('tablet_ishorizfill'),
			'Tablet_IsDoublesFill' => $this->getAttribute('tablet_isdoublesfill'),
			'Tablet_IsTestTablet' => $isTestTablet,
			'Tablet_Barcode' => $this->getAttribute('tablet_barcode'),
			'MethodsIFATabletType_id' => $this->getAttribute('methodsifatablettype_id'),
			'Analyzer_id' => $this->getAttribute('analyzer_id'),
			'MedService_id' => $this->getAttribute('medservice_id'),
			'Tablet_VertSize' => $this->getAttribute('tablet_vertsize'),
			'Tablet_HorizSize' => $this->getAttribute('tablet_horizsize'),
			'session' => $this->getSessionParams(),
			'Tablet_pid' => true //todo $this->>getAttribute('tablet_id');
		];

		$this->reset();
		$saveResp = $this->doSave($params, false);
		if(!empty($saveResp['Error_Msg'])) {
			return $this->getErrorMessage($saveResp['Error_Msg'],$saveResp['Error_Code']);
		}
		$saveResp['success'] = true;
		return $saveResp;
	}
}
