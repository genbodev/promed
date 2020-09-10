<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnDiagAbstract_model.php');
/**
 * EvnDiagPS_model - Модель "Установка диагноза в стационаре"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      09.2014
 *
 * @property-read int $rid КВС
 * @property-read int $pid Движение в отделении или КВС (движение в приемном отделении)
 * @property-read int $DiagSetType_id Вид диагноза
 * @property-read int $DiagSetPhase_id Стадия/фаза заболевания
 * @property-read string $PhaseDescr Описание фазы
 *
 * @property EvnSection_model $EvnSection_model
 */
class EvnDiagPS_model extends EvnDiagAbstract_model
{
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_AUTO_CREATE,
		    self::SCENARIO_DO_SAVE,
		    self::SCENARIO_DELETE,
	    ));
    }

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( empty($this->DiagSetType_id) ) {
				throw new Exception('Не указан вид диагноза');
			}
			if ( empty($this->DiagSetPhase_id) ) {
				$this->setAttribute('diagsetphase_id', null);
				//throw new Exception('Не указана стадия/фаза заболевания');
			}

			if ($this->DiagSetClass_id != 1) {
				$osn_diag_id = null;

				switch ($this->DiagSetType_id) {
					case 1:  // Направившего учреждения
						$osn_diag_id = $this->getFirstResultFromQuery("
							select Diag_did from v_EvnPS where EvnPS_id = ?
						", [$this->pid]);
						break;
					case 2: // Предварительный (Приемного)
						$osn_diag_id = $this->getFirstResultFromQuery("
							select Diag_pid from v_EvnPS where EvnPS_id = ?
						", [$this->pid]);
						break;
					case 3: // Клинический (основной в движении)
						$osn_diag_id = $this->getFirstResultFromQuery("
							select Diag_id from v_EvnSection where EvnSection_id = ?
						", [$this->pid]);
						break;
				}

				if ($osn_diag_id == $this->Diag_id) {
					throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
				}
			}

			// проверка на дубликаты
			$query = "
				select
					count(ED.EvnDiagPS_id) as \"rec\"
				from v_EvnDiagPS ED
				where (1 = 1)
					and ED.Lpu_id = :Lpu_id -- в рамках ЛПУ
					and ED.EvnDiagPS_pid = :EvnDiagPS_pid -- в рамках КВС
					and ED.EvnDiagPS_id <> COALESCE(CAST(:EvnDiagPS_id as bigint), 0) -- исключая текущий диагноз
					and ED.Diag_id = COALESCE(:Diag_id, 0) -- там диагноз
					and cast(ED.EvnDiagPS_setDate as date) = cast(:EvnDiagPS_setDate as date) -- на эту же дату
					and ED.DiagSetClass_id = :DiagSetClass_id -- этот же класс
					and ED.DiagSetType_id = :DiagSetType_id -- этот же тип
					and ED.Person_id = :Person_id -- и по определенному человеку
			";
			$params = [
				'EvnDiagPS_id' => $this->id,
				'EvnDiagPS_pid' => $this->pid,
				'EvnDiagPS_setDate' => $this->setDate,
				'Diag_id' => $this->Diag_id,
				'DiagSetClass_id' => $this->DiagSetClass_id,
				'DiagSetType_id' => $this->DiagSetType_id,
				'Lpu_id' => $this->Lpu_id,
				'Person_id' => $this->Person_id
			];

			$result = $this->getFirstResultFromQuery($query, $params);
			if ( false === $result ) {
				throw new Exception('При выполнении проверки (контроль пересекающихся диагнозов пациента) сервер базы данных вернул ошибку.');
			}
			if ( $result > 0 ) {
				throw new Exception('Сохранение невозможно, поскольку диагноз данного вида дублируется.');
			}
		}
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnDiagPS_id';
		$arr['pid']['alias'] = 'EvnDiagPS_pid';
		$arr['setdate']['alias'] = 'EvnDiagPS_setDate';
		$arr['settime']['alias'] = 'EvnDiagPS_setTime';
		$arr['diagsettype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetType_id',
			'label' => 'Вид диагноза',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diagsetphase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_id',
			'label' => 'Стадия/фаза заболевания',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['phasedescr'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDiagPS_PhaseDescr',
			'label' => 'Описание фазы',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 33;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnDiagPS';
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if ($this->DiagSetType_id == 1 && $this->isNewRecord) {
			$this->setAttribute('diagsettype_id', 2);
			$id = $this->id;
			$this->setAttribute(self::ID_KEY, null);
			$this->_save();
			$this->setAttribute(self::ID_KEY, $id);
		}
	}
	/**
	 * Логика после успешного сохранения объекта в БД со всеми составными частями
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onSave()
	{
		try {
			// @todo переместить в _afterSave, если нужно, чтобы отменялась транзакция
			// но для этого потребуется доработать перерасчет, чтобы он правильно выполнялся внутри транзакции
			$this->_recalcKSGKPGKOEF();
		} catch (Exception $e) {
			$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
		}
	}

	/**
	 * Логика после успешного удаления объекта из БД со всеми составными частями
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onDelete()
	{
		try {
			// @todo переместить в _afterDelete, если нужно, чтобы отменялась транзакция
			// но для этого потребуется доработать перерасчет, чтобы он правильно выполнялся внутри транзакции
			$this->_recalcKSGKPGKOEF();
		} catch (Exception $e) {
			$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
		}
	}

	/**
	 * перерасчет КСГ/КПГ/Коэф в движении
	 */
	private function _recalcKSGKPGKOEF()
	{
		if ( !empty($this->pid)
			&& 'EvnSection' == $this->parentEvnClassSysNick
			&& $this->isAllowTransaction // не надо пересчитывать, если выполнялось внутри транзакции сохранения движения
		) {
			$this->load->model('EvnSection_model');
			$this->EvnSection_model->recalcKSGKPGKOEF($this->pid, $this->sessionParams);
		}		
	}
}