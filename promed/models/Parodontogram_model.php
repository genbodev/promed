<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		05.2014
 */

/**
 * Модель пародонтограммы
 *
 * @package		Stom
 * @author		Александр Пермяков
 *
 * @property PersonToothCard_model $PersonToothCard_model
 */
class Parodontogram_model extends swModel
{
	private $_removeList = array();
	/**
	 * Список имен операций, которые реализованы в модели
	 *
	 * @var array
	 */
	protected $_operationList = array(
		'doPrint', // печать пародонтограммы
		'doLoadMarkerData', // загрузка данных пародонтограммы для маркера
		'doLoadViewData', // загрузка данных пародонтограммы
		'doLoadNewViewData', // загрузка данных пародонтограммы для нового случая
		'doLoadSavedViewData', // загрузка данных пародонтограммы для сохраненного случая
		'doLoadHistory', // загрузка данных списка истории
		'doLoadToothStateValues', // загрузка расчетных значений выносливости зуба
		'doSave', // сохранение данных пародонтограммы
		'doRemove', // удаление пародонтограммы
	);

	/**
	 * Имя текущей операции
	 * @var string
	 */
	protected $_operation = '';

	/**
	 * @param string $operation
	 */
	public function setOperation($operation)
	{
		$this->_operation = $operation;
	}

	/**
	 * @return string
	 */
	public function getOperation()
	{
		return $this->_operation;
	}

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $operation
	 * @return array
	 */
	public function getInputRules($operation)
	{

		$rules = array();
		switch ($operation) {
			case 'doLoadMarkerData':
				$rules = array(
					array('field' => 'EvnVizitPLStom_id','label' => 'Стоматологическое посещение', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoadToothStateValues':
				$rules = array(
					array('field' => 'Person_id','label' => 'человек', 'rules' => '', 'type' =>  'id'),
				);
				break;
			case 'doRemove':
			case 'doPrint':
				$rules = array(
					array('field' => 'EvnUslugaStom_id','label' => 'случай оказания услуги "Пародонтограмма"', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoadHistory':
				$rules = array(
					array('field' => 'Person_id','label' => 'человек', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoadViewData':
				$rules = array(
					array('field' => 'Person_id','label' => 'человек', 'rules' => 'required', 'type' =>  'id'),
					array('field' => 'EvnUslugaStom_id','label' => 'случай оказания услуги "Пародонтограмма"', 'rules' => '', 'type' =>  'id'),
				);
				break;
			case 'doSave':
				$rules = array(
					array('field' => 'EvnUslugaStom_id','label' => 'случай оказания услуги "Пародонтограмма"', 'rules' => 'required', 'type' =>  'id'),
					array('field' => 'state','label' => 'состояние пародонтограммы', 'rules' => 'trim|required', 'type' =>  'string'),
				);
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение пародонтограммы
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array
	 * @throws Exception
	 */
	public function doSave($data = array(), $isAllowTransaction = true)
	{
		$this->setOperation('doSave');
		if (empty($data['EvnUslugaStom_id'])) {
			throw new Exception('Не указан случай оказания услуги "Пародонтограмма"!', 400);
		}
		if (empty($data['state'])) {
			throw new Exception('Не указано состояние пародонтограммы!', 400);
		}
		if (empty($data['pmUser_id'])) {
			throw new Exception('Не указан пользователь!', 400);
		}
		$state = json_decode($data['state'], true);
		if (!is_array($state)) {
			throw new Exception('Неправильный формат состояния пародонтограммы!', 400);
		}
		//нужен ли $data['Person_id'] ?

		$response = array(array('Error_Msg' => null, 'Error_Code' => null,));
		$this->beginTransaction();
		try {
			$this->_setRemoveListByEvn($data['EvnUslugaStom_id'], 'EvnUslugaStom');
			$this->_clear();
			$nothingList = array();
			foreach ($state as $Tooth_id => $ToothStateType_id) {
				if (empty($Tooth_id) || //empty($ToothStateType_id) ||
					!is_numeric($Tooth_id) || !is_numeric($ToothStateType_id)
				) {
					throw new Exception('Неправильный формат данных состояния пародонтограммы!', 400);
				}
				$data['Tooth_id'] = $Tooth_id;
				$data['ToothStateType_id'] = (empty($ToothStateType_id)) ? null : $ToothStateType_id;
				if (2 == $ToothStateType_id) {
					$nothingList[] = $Tooth_id;
				}
				$this->_save($data);
			}

			// синхронизация с зубной картой
			$this->load->model('PersonToothCard_model', 'PersonToothCard_model');
			$this->PersonToothCard_model->applyParodontogramChanges($data, $nothingList);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
			$response[0]['Error_Code'] = $e->getCode();
		}
		$this->commitTransaction();
		//$response[0]['nothingList'] = var_export($nothingList, true);
		return $response;
	}

	/**
	 * Удаление по идешнику ТАП или посещения или услуги
	 *
	 * Вызывается из EvnPLStom_model::_beforeDelete
	 * Вызывается из EvnVizitPLStom_model::_beforeDelete
	 * Вызывается из self::doRemove
	 *
	 * @param int $id
	 * @param string $sysnick
	 * @param bool $isAllowTransaction
	 * @return array
	 */
	public function doRemoveByEvn($id, $sysnick, $isAllowTransaction = true)
	{
		$response = array(array('Error_Msg' => null, 'Error_Code' => null,));
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();
		try {
			$this->_setRemoveListByEvn($id, $sysnick);
			$this->_clear();
			if ('EvnUslugaStom' === $sysnick) {
				// Также надо удалить из ЗК состояния, которые были установлены из этой ПГ
				$this->load->model('PersonToothCard_model', 'PersonToothCard_model');
				$this->PersonToothCard_model->setParams(array('session'=>$this->sessionParams));
				$tmp = $this->PersonToothCard_model->doRemoveByEvn($id, $sysnick, false);
				if (!empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				/*
				if ($this->PersonToothCard_model->isAllowEdit($this->id, $this->Person_id, $this->evnClassId)) {
					// если стомат. услуга последняя, то удаляем все записи из ЗК в рамках услуги
					$this->PersonToothCard_model->setParams(array('session'=>$this->sessionParams));
					$tmp = $this->PersonToothCard_model->doRemoveByEvn($this->id, 'EvnVizitPLStom', false);
					if (!empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
				}
				*/
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
			$response[0]['Error_Code'] = $e->getCode();
		}
		return $response;
	}

	/**
	 * Удаление пародонтограммы
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array
	 * @throws Exception
	 */
	public function doRemove($data)
	{
		$this->setOperation('doRemove');
		if (empty($data['EvnUslugaStom_id'])) {
			throw new Exception('Не указан случай оказания услуги "Пародонтограмма"!', 400);
		}
		$this->setParams($data);
		return $this->doRemoveByEvn($data['EvnUslugaStom_id'], 'EvnUslugaStom');
	}

	/**
	 * Загрузка расчетных значений выносливости зуба
	 * для редактирования пародонтограммы
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array
	 * @throws Exception
	 */
	public function doLoadToothStateValues($data)
	{
		$this->setOperation('doLoadToothStateValues');
		$query = "
			select
				t.Tooth_id,
				t.JawPartType_id as JawPartType_Code,-- === JawPartType_Code
				t.Tooth_Code,
				s.ToothStateType_id,-- === ToothStateType_Code
				s.ToothStateType_Code,
				s.ToothStateType_Name,
				s.ToothStateType_Nick,
				v.ToothStateValues_id,
				v.ToothStateValues_Values as ToothStateType_Value
			from v_Tooth t with(nolock)
			inner join v_ToothStateValues v with(nolock) on t.Tooth_id = v.Tooth_id
			inner join v_ToothStateType s with (nolock) on s.ToothStateType_id = v.ToothStateType_id
			order by
				t.Tooth_Code, v.ToothStateType_id
		";
		/*
		 * сейчас значения есть только для коренных зубов, если это изменится то
			where
				t.Tooth_Code > 10 and t.Tooth_Code < 49 -- коренные
				--t.Tooth_Code > 50 and t.Tooth_Code < 86 -- молочные
		 */
		//$queryParams = array('Person_id' => $data['Person_id']);
		$queryParams = array();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			throw new Exception('Ошибка при запросе списка расчетных значений выносливости зуба', 500);
		}
	}

    /**
     * Возвращает данные для левой панели просмотра и редактирования пародонтограммы
     * @param array $data Массив, полученный методом ProcessInputData контроллера
     * @return array
     * @throws Exception
     */
    public function doLoadHistory($data)
    {
        $this->setOperation('doLoadHistory');
        if (empty($data['Person_id'])) {
            throw new Exception('Не указан человек!', 400);
        }
        $query = "
            SELECT
                e.EvnUslugaStom_id,
                convert(varchar(10), v.EvnVizitPLStom_setDate, 104) as EvnUslugaStom_setDate,
                v.EvnVizitPLStom_setTime as EvnUslugaStom_setTime,
                e.Lpu_id,
                e.MedPersonal_id,
                lpu.Lpu_Nick,
                mp.Person_SurName + ' ' 
                    + LEFT(mp.Person_FirName, 1)  + '. ' 
                    + ISNULL(LEFT(mp.Person_SecName, 1) + '.', '') as MedPersonal_Fin
            FROM v_EvnUslugaStom e with (nolock)
            inner join v_EvnVizitPLStom v with (nolock) on v.EvnVizitPLStom_id = e.EvnUslugaStom_pid
            left join v_Lpu lpu with (nolock) on lpu.Lpu_id = e.Lpu_id
            left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = e.MedPersonal_id and mp.Lpu_id = e.Lpu_id
            WHERE e.Person_id = :Person_id
            and exists(
                select top 1 p.EvnUslugaStom_id
                from v_Parodontogram p with (nolock)
                where p.EvnUslugaStom_id = e.EvnUslugaStom_id
				union all
				select top 1 e.EvnUslugaStom_id
				from v_UslugaComplexAttribute uca with (nolock)
				inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					and ucat.UslugaComplexAttributeType_SysNick like 'parondontogram'
				where uca.UslugaComplex_id = e.UslugaComplex_id
            )
            ORDER BY v.EvnVizitPLStom_setDT desc
        ";
        $queryParams = array('Person_id' => $data['Person_id']);
        $result = $this->db->query($query, $queryParams);
        if ( !is_object($result) ) {
            throw new Exception('Ошибка при запросе списка дат оказания услуги "Пародонтограмма"', 500);
        }
        $tmp = $result->result('array');
        foreach($tmp as $i => $row) {
            $tmp[$i]['EvnUslugaStom_Display'] = $row['EvnUslugaStom_setDate'] 
                . ' ' . $row['EvnUslugaStom_setTime']
                . ' ' . $row['Lpu_Nick']
                . ' ' . $row['MedPersonal_Fin'];
        }
        return $tmp;
    }

	/**
	 * Получение данных пародонтограммы для панели просмотра и редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array
	 * @throws Exception
	 */
	public function doLoadViewData($data)
	{
		if (empty($data['EvnUslugaStom_id'])) {
			$this->setOperation('doLoadNewViewData');
			if (empty($data['Person_id'])) {
				throw new Exception('Не указан человек!', 400);
			}
		} else {
			$this->setOperation('doLoadSavedViewData');
		}
		return $this->_doLoadViewData($data);
	}

	/**
	 * Получение пародонтограммы для маркера
	 * @param array $data
	 * @return array|boolean
	 */
	public function doLoadMarkerData($data)
	{
		$this->setOperation('doLoadMarkerData');
		if (empty($data['EvnVizitPLStom_id'])) {
			return false; //маркер должен быть замещен пустой строкой
		}
		try {
			/*
			 * Должна отображаться та пародонтограмма, которая максимальна по дате, но меньше или равна дате посещения
			 */
			$query = "
				declare
					@EvnVizitPLStom_id bigint = :EvnVizitPLStom_id,
					@max_dt datetime,
					@Person_id bigint;

				select @max_dt = EvnVizitPLStom_setDT, @Person_id = Person_id
				from v_EvnVizitPLStom with (nolock)
				where EvnVizitPLStom_id = @EvnVizitPLStom_id

				select top 1
					eu.EvnUslugaStom_id
				from v_EvnVizitPLStom ev with (nolock)
				inner join v_EvnUslugaStom eu with (nolock) on eu.EvnUslugaStom_pid = ev.EvnVizitPLStom_id
				where ev.Person_id = @Person_id and exists(
					select top 1 p.Parodontogram_id
					from v_Parodontogram p with (nolock)
					where p.EvnUslugaStom_id = eu.EvnUslugaStom_id
				) and ev.EvnVizitPLStom_setDT <= @max_dt
				order by ev.EvnVizitPLStom_setDT desc
			";
			$queryParams = array('EvnVizitPLStom_id' => $data['EvnVizitPLStom_id']);
			$data['EvnUslugaStom_id'] = $this->getFirstResultFromQuery($query, $queryParams);
			if (empty($data['EvnUslugaStom_id'])) {
				//маркер должен быть замещен пустой строкой
				return false;
			}
			$response = $this->loadEvnUslugaStomData($data['EvnUslugaStom_id'], 0);
			$data['Person_id'] = $response['Person_id'];
			// считываем для шаблона данные пародонтограммы
			$toothStateValues = $this->loadToothStateValues($response, $data['EvnUslugaStom_id']);

			$this->setOperation('doPrint');
			$this->load->library('parser');
			return $this->_getParseData($response, $toothStateValues);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Проверка наличия пародонтограммы
	 * @param int $EvnUslugaStom_id
	 * @param int $EvnVizitPLStom_id
	 * @return int EvnUslugaStom_id или 0
	 * @throws Exception
	 */
	public function hasParodontogram($EvnUslugaStom_id=0, $EvnVizitPLStom_id=0)
	{
		if ($EvnUslugaStom_id > 0) {
			$query = "
				select top 1 p.EvnUslugaStom_id
				from v_Parodontogram p with (nolock)
				where p.EvnUslugaStom_id = :EvnUslugaStom_id
			";
			$queryParams = array('EvnUslugaStom_id' => $EvnUslugaStom_id);
		} else if ($EvnVizitPLStom_id > 0) {
			$query = "
				select top 1
					e.EvnUslugaStom_id
				from v_EvnUslugaStom e with (nolock)
				where e.EvnUslugaStom_pid = :EvnVizitPLStom_id
					and exists(
						select top 1 p.Parodontogram_id
						from v_Parodontogram p with (nolock)
						where p.EvnUslugaStom_id = e.EvnUslugaStom_id
					)
				order by e.EvnUslugaStom_setDate desc
			";
			$queryParams = array('EvnVizitPLStom_id' => $EvnVizitPLStom_id);
		} else {
			return 0;
		}
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
		} else {
			throw new Exception('Ошибка при проверке наличия пародонтограммы', 500);
		}
		if (count($tmp) > 0) {
			return $tmp[0]['EvnUslugaStom_id'];
		} else {
			return 0;
		}
	}

	/**
	 * Получение данных случая оказания услуги пародонтограмма
	 * @param int $EvnUslugaStom_id Вернутся данные указанного случая
	 * @param int $Person_id Вернутся данные последнего случая
	 * @return array
	 * @throws Exception
	 */
	public function loadEvnUslugaStomData($EvnUslugaStom_id=0, $Person_id=0)
	{
		if ($Person_id > 0) {
			$queryParams = array('Person_id' => $Person_id);
			$where_clause = 'PS.Person_id = :Person_id';
			$from_clause = "v_PersonState PS with (nolock)
			inner join v_UslugaComplex uc2011 with (nolock) on uc2011.UslugaCategory_id = 4 and
				uc2011.UslugaComplex_Code like 'A02.07.009'
			outer apply (
				select top 1
					e.EvnUslugaStom_id,
					e.EvnUslugaStom_pid,
					null as EvnUslugaStom_setDT
				from v_EvnUslugaStom e with (nolock)
				where e.Person_id = PS.Person_id
					and exists(
						select top 1 p.Parodontogram_id
						from v_Parodontogram p with (nolock)
						where p.EvnUslugaStom_id = e.EvnUslugaStom_id
					)
				order by e.EvnUslugaStom_setDT desc
			) e";
			/*
					and exists(
						select uc.UslugaComplex_id
						from v_UslugaComplex uc with (nolock)
						inner join v_UslugaComplex uc2011 with (nolock) on uc2011.UslugaComplex_id = uc.UslugaComplex_2011id
						where uc.UslugaComplex_id = e.UslugaComplex_id
						and uc2011.UslugaComplex_Code like 'A02.07.009'
					)
			 */
		} else if ($EvnUslugaStom_id > 0) {
			$queryParams = array('EvnUslugaStom_id' => $EvnUslugaStom_id);
			$where_clause = "e.EvnUslugaStom_id = :EvnUslugaStom_id
				and exists (
					select top 1 uca.UslugaComplex_id
					from v_UslugaComplexAttribute uca with (nolock)
					inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						and ucat.UslugaComplexAttributeType_SysNick like 'parondontogram'
					where uca.UslugaComplex_id = e.UslugaComplex_id
				)";
			$from_clause = "v_EvnUslugaStom e with (nolock)
			inner join v_PersonState PS with (nolock) on PS.Person_id = e.Person_id
			inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = e.UslugaComplex_id
			left join v_UslugaComplex uc2011 with (nolock) on uc2011.UslugaComplex_id = uc.UslugaComplex_2011id
						and uc2011.UslugaComplex_Code like 'A02.07.009'";
		} else {
			throw new Exception('Неправильные параметры для получения данных случая оказания услуги "Пародонтограмма"', 400);
		}
		$query = "
			select top 1
				PS.Person_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, isnull(e.EvnUslugaStom_setDT, dbo.tzGetDate())) as Person_Age,
				e.EvnUslugaStom_id,
				e.EvnUslugaStom_pid,
				uc2011.UslugaComplex_Code,
				uc2011.UslugaComplex_Name,
				convert(varchar(10), isnull(e.EvnUslugaStom_setDT, dbo.tzGetDate()), 104) as EvnUslugaStom_setDate
			from {$from_clause}
			where {$where_clause}
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
			if( empty($tmp) )
			{
				throw new Exception('Данные случая оказания услуги "Пародонтограмма" не найдены '/* . getDebugSQL($query, $queryParams)*/, 404);
			}
			return $tmp[0];
		} else {
			throw new Exception('Ошибка при запросе данных случая оказания услуги "Пародонтограмма"', 500);
		}
	}

	/**
	 * Получение данных услуги пародонтограмма для постоянных и молочных зубов
	 *
	 * В БД есть значения выносливости только для постоянных зубов,
	 * поэтому для молочных зубов устанавливаем значение выносливости 0,
	 * без возможности изменить
	 * @param array $data Данные полученные методом loadEvnUslugaStomData
	 * @param int $EvnUslugaStom_id Если не указано, вернутся данные по умолчанию
	 * @return array
	 * @throws Exception
	 */
	public function loadToothStateValues($data, $EvnUslugaStom_id)
	{
		$where_clause = '';
		$from_clause = '';
		$tm_select = 'null as PersonToothCard_id,';
		$before_select = 'declare @history_date datetime = dbo.tzGetDate();

            with ToothAll (Tooth_id, JawPartType_id, Tooth_Code, Tooth_Num)
            AS (
                SELECT
                    v_Tooth.Tooth_id,
                    v_Tooth.JawPartType_id,
                    v_Tooth.Tooth_Code,
                    substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as Tooth_Num
                FROM v_Tooth with (nolock)
            )';
		$queryParams = array();
		//throw new Exception($this->getOperation().$EvnUslugaStom_id);
		if ($EvnUslugaStom_id > 0 && $this->getOperation() != 'doLoadNewViewData') {
			$queryParams['EvnUslugaStom_id'] = $EvnUslugaStom_id;
			$from_clause = '
				v_Parodontogram p with (nolock)
				inner join ToothAll with(nolock) on ToothAll.Tooth_id = p.Tooth_id
				left join v_ToothStateValues v with (nolock) on v.ToothStateType_id = p.ToothStateType_id
					and v.Tooth_id = p.Tooth_id
				left join v_ToothStateType s with (nolock) on s.ToothStateType_id = v.ToothStateType_id';
			$where_clause = 'p.EvnUslugaStom_id = :EvnUslugaStom_id';
		}
		if ($this->getOperation() == 'doLoadNewViewData' || empty($EvnUslugaStom_id)) {
			/*
			 * Надо подставлять ToothStateType_id из записей ЗК с активным состоянием "отсутствует"
			 * Если в ЗК есть записи с типом зуба, то брать его номер,
			 * Если в предыдущей пародонтограмме есть запись по зубу, то брать её,
			 * иначе брать номер зуба по умолчанию
			 */
			$this->load->library('ToothMap');
			$queryParams['Person_id'] = $data['Person_id'];
			$before_select .= ',
            ToothMap (PersonToothCard_id, ToothStateClass_id, ToothStateType_id, Tooth_id, JawPartType_id, Tooth_Num)
            AS (
                SELECT
                    v_PersonToothCard.PersonToothCard_id,
                    v_PersonToothCard.ToothStateClass_id,
                    case when v_PersonToothCard.ToothStateClass_id = 14 then 2 else null end as ToothStateType_id,
                    v_Tooth.Tooth_id,
                    v_Tooth.JawPartType_id,
                    substring(cast(v_Tooth.Tooth_Code as varchar(2)), 2, 1) as Tooth_Num
                FROM v_Tooth with (nolock)
                inner join v_PersonToothCard with (nolock) on v_PersonToothCard.Tooth_id = v_Tooth.Tooth_id
                WHERE v_PersonToothCard.Person_id = :Person_id
                    and v_PersonToothCard.PersonToothCard_begDate < @history_date
                    and v_PersonToothCard.PersonToothCard_endDate is null
            )';
			$from_clause = 'ToothAll
                left join ToothMap tm with(nolock) on tm.Tooth_id = ToothAll.Tooth_id
                    and tm.ToothStateClass_id in (12,13,14)';
			$tm_select = 'tm.PersonToothCard_id,';
			$where_clause = 'tm.PersonToothCard_id is not null or
			 (
                 not exists (
                    select top 1 PersonToothCard_id
                    from ToothMap with(nolock) where ToothMap.JawPartType_id = ToothAll.JawPartType_id
                    and ToothMap.Tooth_Num = ToothAll.Tooth_Num
                    and ToothMap.ToothStateClass_id in (12,13,14)
                 )';
			if ($EvnUslugaStom_id > 0) {
				// для зубов загружаем данные предыдущей пародонтограммы,
				$queryParams['EvnUslugaStom_id'] = $EvnUslugaStom_id;
				$before_select .= ',
				PrevParodontogram (ToothStateType_id, Tooth_id)
				AS (
	                SELECT
	                    v_Parodontogram.ToothStateType_id,
                        v_Parodontogram.Tooth_id
	                FROM v_Parodontogram with (nolock)
	                WHERE v_Parodontogram.EvnUslugaStom_id = :EvnUslugaStom_id
	            )';
				$from_clause .= '
				left join PrevParodontogram p with(nolock) on p.Tooth_id = ToothAll.Tooth_id
					and tm.PersonToothCard_id is null
                left join v_ToothStateValues v with (nolock) on v.Tooth_id = ToothAll.Tooth_id
                    and v.ToothStateType_id = coalesce(tm.ToothStateType_id, p.ToothStateType_id, 1)
				';
			} else {
				$from_clause .= '
				left join v_ToothStateValues v with (nolock) on v.Tooth_id = ToothAll.Tooth_id
					and v.ToothStateType_id = isnull(tm.ToothStateType_id, 1)
				';
			}
			$from_clause .= '
				left join v_ToothStateType s with (nolock) on s.ToothStateType_id = v.ToothStateType_id';
			$where_clause .= '
			    and ' . SwTooth::getDefaultFilter($data['Person_Age'], 'ToothAll')
			. '
			)';

		}
		if ( empty($from_clause) ) {
			throw new Exception('Неправильный вариант выборки данных пародонтограммы!', 500);
		}
		$query = "
			{$before_select}

			select
				{$tm_select}
				ToothAll.JawPartType_id as JawPartType_Code,-- === JawPartType_Code
				ToothAll.Tooth_id,
				ToothAll.Tooth_Code,
				isnull(s.ToothStateType_id, 0) as ToothStateType_id,-- === ToothStateType_Code
				isnull(s.ToothStateType_Code, '') as ToothStateType_Code,
				isnull(s.ToothStateType_Nick,' ') as ToothStateType_Nick,
				isnull(v.ToothStateValues_Values,0) as ToothState_Value
			from {$from_clause}
			where {$where_clause}
			order by
				ToothAll.JawPartType_id, ToothAll.Tooth_Num
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при запросе данных пародонтограммы', 500);
		}
		$tmp = $result->result('array');
		if ( empty($tmp) ) {
			throw new Exception('Данные пародонтограммы не найдены!', 404);
		}
        $rows = array();
        foreach ($tmp as $row) {
            $rows[$row['Tooth_id']] = $row;
        }
        $tmp = array();
        foreach ($rows as $row) {
            $tmp[] = $row;
        }
		return $tmp;
	}

	/**
	 * Получение пародонтограммы для печати
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return string
	 * @throws Exception
	 */
	public function doPrint($data)
	{
		$this->setOperation('doPrint');
		if (empty($data['EvnUslugaStom_id'])) {
			throw new Exception('Не указан случай оказания услуги "Пародонтограмма"!', 400);
		}
		//проверяем есть ли пародонтограмма
		$tmp = $this->hasParodontogram($data['EvnUslugaStom_id']);
		if ( empty($tmp) ) {
			throw new Exception('Данные услуги "Пародонтограмма" не найдены', 404);
		}
		//будем выводить данные указанного случая
		$output = $this->loadEvnUslugaStomData($data['EvnUslugaStom_id']);
		$data['Person_id'] = $output['Person_id'];
		// считываем для шаблона данные пародонтограммы
		$toothStateValues = $this->loadToothStateValues($output, $data['EvnUslugaStom_id']);
		$this->load->library('parser');
		$data = $this->_getParseData($output, $toothStateValues);
		return $this->parser->parse('stom/parodontogramma_layout', $data, true);
	}
	/**
	 * Возвращает данные для шаблона просмотра пародонтограммы
	 *
	 * Если передается EvnUslugaStom_id,
	 * то возвращает данные указанного случая оказания услуги "Пародонтограмма"
	 * Если EvnUslugaStom_id не передается, то должен передаваться Person_id и
	 * возвращает данные последнего сохраненного случая оказания услуги "Пародонтограмма"
	 * Если нет ни одного сохраненного случая,
	 * то возвращает данные по умолчанию
	 *
	 * Используется при печати, при загрузке данных панели просмотра и редактирования
	 * пародонтограммы
	 *
	 * При печати, при загрузке данных панели просмотра и редактирования
	 * сохраненной пародонтограммы EvnUslugaStom_id обязателен
	 *
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|string данные для шаблона просмотра пародонтограммы или пародонтограмму
	 * @throws Exception
	 */
	private function _doLoadViewData($data)
	{
		if (empty($data['EvnUslugaStom_id'])) {
			//для редактирования будем выводить данные предыдущего случая, если он есть
			$output = $this->loadEvnUslugaStomData(0, $data['Person_id']);
			$data['EvnUslugaStom_id'] = $output['EvnUslugaStom_id'];
		} else {
			//проверяем есть ли пародонтограмма
			$tmp = $this->hasParodontogram($data['EvnUslugaStom_id']);
			if ( empty($tmp) ) {
				//пародонтограмма не сохранялась или была удалена
				//для редактирования будем выводить предыдущие данные
				$output = $this->loadEvnUslugaStomData(0, $data['Person_id']);
				$data['EvnUslugaStom_id'] = $output['EvnUslugaStom_id'];
			} else {
				//для печати или редактирования будем выводить данные указанного случая
				$output = $this->loadEvnUslugaStomData($data['EvnUslugaStom_id']);
			}
			$data['Person_id'] = $output['Person_id'];
		}
		// считываем для шаблона данные пародонтограммы
		$toothStateValues = $this->loadToothStateValues($output, $data['EvnUslugaStom_id']);
		$this->load->library('parser');
		$data = $this->_getParseData($output, $toothStateValues);
		$output['parodontogramma'] = $this->parser->parse('stom/parodontogramma_layout', $data, true);
		return array($output);
	}

	/**
	 * Метод очистки таблицы пародонтограммы
	 * @param array $output
	 * @param array $toothStateValues
	 * @return array
	 * @throws Exception
	 */
	protected function _getParseData(&$output, $toothStateValues)
	{
		$isForPrint = ($this->getOperation() == 'doPrint');
		$data = array();
		//данные для печати
		$data['UslugaComplex_Code'] = $output['UslugaComplex_Code'];
		$data['UslugaComplex_Name'] = $output['UslugaComplex_Name'];
		$data['Person_SurName'] = $output['Person_SurName'];
		$data['Person_FirName'] = $output['Person_FirName'];
		$data['Person_SecName'] = $output['Person_SecName'];
		$data['Person_BirthDay'] = $output['Person_BirthDay'];
		//могут быть региональные отличия, поэтому беру из БД
		$types = $this->_loadToothStateType();
		foreach ($types as $row) {
			$nick = 'ToothStateType_Nick' . $row['ToothStateType_Code'];
			$name = 'ToothStateType_Name' . $row['ToothStateType_Code'];
			$data[$nick] = $row['ToothStateType_Nick'];
			$data[$name] = $row['ToothStateType_Name'];
		}
		$jawPartTpl = 'stom/parodontogramma_jaw_part';
		if ($isForPrint) {
			$data['EvnUslugaStom_setDate'] = $output['EvnUslugaStom_setDate'];
		} else {
			//данные, которые возможно будет обновить
			$data['EvnUslugaStom_setDate'] = '{EvnUslugaStom_setDate}';
		}

		$jawParts = $this->_processingToothStateValues($toothStateValues);
		if (!$isForPrint) {
			//значения для редактирования
			$output['state'] = array();
			foreach ($toothStateValues as $row) {
				$code = $row['Tooth_Code'].'';
				$output['state'][$code] = array(
					//для сохранения
					'Tooth_id' => $row['Tooth_id'],
					'ToothStateType_id' => $row['ToothStateType_id'],
					//для отображения
					'JawPartType_Code' => $row['JawPartType_Code'],
					'ToothStateType_Code' => $row['ToothStateType_Code'],
					'ToothStateType_Nick' => $row['ToothStateType_Nick'],
					'ToothState_Value' => floatval($row['ToothState_Value']),
				);
			}
		}
		// правый верхний
		$data['JawPart1'] = $this->parser->parse($jawPartTpl, $jawParts['1'], true);
		// левый верхний
		$data['JawPart2'] = $this->parser->parse($jawPartTpl, $jawParts['2'], true);
		// левый нижний
		$data['JawPart3'] = $this->parser->parse($jawPartTpl, $jawParts['3'], true);
		// правый нижний
		$data['JawPart4'] = $this->parser->parse($jawPartTpl, $jawParts['4'], true);
		return $data;
	}

	/**
	 * Обработка для шаблона
	 * @param array $toothStateValues
	 * @return array
	 * @throws Exception
	 */
	protected function _processingToothStateValues($toothStateValues)
	{
		$isForPrint = ($this->getOperation() == 'doPrint');
		$jawParts = array();
		foreach ($toothStateValues as $row) {
			$code = $row['JawPartType_Code'].'';
			if (empty($jawParts[$code])) {
				$jawParts[$code] = array();
				$jawParts[$code]['SumStateValues'] = 0;
				$jawParts[$code]['ToothCodeList'] = array();
				$jawParts[$code]['ToothStateTypeNickList'] = array();
				$jawParts[$code]['ToothStateValueList'] = array();
			}
			$jawParts[$code]['ToothCodeList'][] = array(
				'Tooth_Code' => $row['Tooth_Code'],
			);
			if ($isForPrint) {
				$jawParts[$code]['ToothStateTypeNickList'][] = array(
					'Tooth_Code' => $row['Tooth_Code'],
					'ToothStateType_Code' => $row['ToothStateType_Code'],
					'ToothStateType_Nick' => $row['ToothStateType_Nick'],
				);
				$jawParts[$code]['ToothStateValueList'][] = array(
					'ToothState_Value' => floatval($row['ToothState_Value']),
				);
				$jawParts[$code]['SumStateValues'] += $row['ToothState_Value'];
			} else {
				//данные, которые возможно будет обновить
				$jawParts[$code]['ToothStateTypeNickList'][] = array(
					'Tooth_Code' => $row['Tooth_Code'],
					'ToothStateType_Code' => '{TypeCode'. $row['Tooth_Code'] .'}',
					'ToothStateType_Nick' => '{TypeNick'. $row['Tooth_Code'] .'}',
				);
				$jawParts[$code]['ToothStateValueList'][] = array(
					'ToothState_Value' => '{Value'. $row['Tooth_Code'] .'}',
				);
				$jawParts[$code]['SumStateValues'] = '{Sum'. $code .'}';
			}
		}
		foreach ($jawParts as $code => $jawPart) {
			$code += 0;
			if (in_array($code, array(1,4))) {
				$jawParts[$code]['ToothCodeList'] = array_reverse($jawPart['ToothCodeList']);
				$jawParts[$code]['ToothStateTypeNickList'] = array_reverse($jawPart['ToothStateTypeNickList']);
				$jawParts[$code]['ToothStateValueList'] = array_reverse($jawPart['ToothStateValueList']);
			}
		}
		return $jawParts;
	}

	/**
	 * Загрузка типов выносливости зуба для шаблона
	 * @return array
	 * @throws Exception
	 */
	protected function _loadToothStateType()
	{
		$query = "
			select
				s.ToothStateType_Code,
				s.ToothStateType_Name,
				s.ToothStateType_Nick
			from v_ToothStateType s with (nolock)
			order by ToothStateType_Code
		";
		$queryParams = array();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
			foreach ($tmp as &$row) {
				$val = explode(',', $row['ToothStateType_Name']);
				if (empty($val[1])) {
					$row['ToothStateType_Name'] = $val[0];
				} else {
					$row['ToothStateType_Name'] = $val[1];
				}
				$row['ToothStateType_Name'] = trim($row['ToothStateType_Name']);
			}
			//@todo кэшировать результат
			return $tmp;
		}
		else {
			throw new Exception('Ошибка при запросе списка типов выносливости зуба', 500);
		}
	}

	/**
	 * @param $id
	 * @param $sysnick
	 * @throws Exception
	 */
	protected function _setRemoveListByEvn($id, $sysnick)
	{
		$queryParams = array('id' => $id);
		$query = null;
		switch($sysnick) {
			case 'EvnUslugaStom':
				$query = "
					select Parodontogram_id from v_Parodontogram with (nolock)
					where EvnUslugaStom_id = :id
				";
				break;
			case 'EvnVizitPLStom':
				$query = "
					select p.Parodontogram_id from v_EvnUslugaStom e with (nolock)
					inner join v_Parodontogram p with (nolock) on p.EvnUslugaStom_id = e.EvnUslugaStom_id
					where e.EvnUslugaStom_pid = :id
				";
				break;
			case 'EvnPLStom':
				$query = "
					select p.Parodontogram_id from v_EvnUslugaStom e with (nolock)
					inner join v_Parodontogram p with (nolock) on p.EvnUslugaStom_id = e.EvnUslugaStom_id
					where e.EvnUslugaStom_rid = :id
				";
				break;
		}
		if (isset($query)) {
			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при запросе данных пародонтограммы', 500);
			}
			$this->_removeList = $result->result('array');
		} else {
			throw new Exception('Неправильные параметры для удаления пародонтограммы', 500);
		}
	}

	/**
	 * Метод очистки таблицы пародонтограммы
	 * @throws Exception
	 */
	protected function _clear()
	{
		if(!empty($this->_removeList)) {
			foreach($this->_removeList as $row) {
				$this->_destroy($row);
			}
		}
	}

	/**
	 * Удаление записи пародонтограммы
	 * @param array $data
	 * @throws Exception
	 */
	protected function _destroy($data)
	{
		if (empty($data['Parodontogram_id'])) {
			throw new Exception('Отсутствует ключ');
		}
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Parodontogram_del
				@Parodontogram_id = :Parodontogram_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при удалении записи');
		}
		$response = $result->result('array');
		if ( !empty($response[0]['Error_Msg']) )
		{
			throw new Exception($response[0]['Error_Msg']);
		}
	}

	/**
	 * Сохранение записи пародонтограммы
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	protected function _save($data = array()) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :Parodontogram_id;

			exec p_Parodontogram_ins
				@Parodontogram_id = @Res output,
				@EvnUslugaStom_id = :EvnUslugaStom_id,
				@Person_id = :Person_id,
				@Tooth_id = :Tooth_id,
				@ToothStateType_id = :ToothStateType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as Parodontogram_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Parodontogram_id' => NULL,
			'Person_id' => empty($data['Person_id']) ? NULL : $data['Person_id'],
			'EvnUslugaStom_id' => $data['EvnUslugaStom_id'],
			'Tooth_id' => $data['Tooth_id'],
			'ToothStateType_id' => $data['ToothStateType_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['Parodontogram_id'];
		}
		else {
			throw new Exception('Ошибка при запросе к БД при сохранении', 500);
		}
	}
}
