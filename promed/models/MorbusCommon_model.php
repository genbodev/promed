<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

/**
 * MorbusCommon_model - Модель "Общее заболевание"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      12.2014
 *
 * Отношения объекта "Общее заболевание"
 * MorbusBase has one Evn 1:0..1 (MorbusBase.Evn_pid = Evn.Evn_id)
 * MorbusBase has one Person 1:1 (MorbusBase.Person_id = Person.Person_id)
 * Person has one MorbusBase 1:0..1 (MorbusBase.Person_id = Person.Person_id and MorbusType_id = ? and MorbusBase_disDT is null)
 * MorbusBase has many Morbus 1:1..* (Morbus.MorbusBase_id = MorbusBase.MorbusBase_id)
 *
 * @property integer Person_id Человек
 * @property integer Evn_pid Учетный документ, в рамках которого было добавлено общее заболевание
 * @property integer MorbusType_id Тип заболевания (перечисление MorbusType)
 * @property string MorbusType_SysNick
 * @property datetime setDT Дата начала заболевания
 * @property datetime disDT Дата исхода заболевания
 * @property integer MorbusResult_id Результат (перечисление MorbusResult)
 *
 * @property array $listMorbusTypeOneByPerson У пациента может быть только одно заболевание с типом из этого списка
 * Если заболевания нет в этом списке, то считается, что оно должно быть одно для группы диагнозов (onko, vener, )
 */
class MorbusCommon_model extends swModel
{
	private $_MorbusType_SysNick = null;

	/**
	 * @return array
	 */
	function getListMorbusTypeOneByPerson()
	{
		return array('acs','pregnancy','crazy','orphan','hepa','tub','hiv','nephro','prof','ibs','narc','geriatrics','palliat');
	}

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
	    ));
    }

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusBase';
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	function getMorbusType_SysNick()
	{
		if (isset($this->_savedData['MorbusType_SysNick'])) {
			$this->_MorbusType_SysNick = $this->_savedData['MorbusType_SysNick'];
		}
		if (empty($this->MorbusType_id)) {
			$this->_MorbusType_SysNick = null;
		}
		if (empty($this->_MorbusType_SysNick) && !empty($this->MorbusType_id)) {
			$this->load->library('swMorbus');
			$arr = swMorbus::getMorbusTypeListAll();
			if (empty($arr[$this->MorbusType_id])) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
			$this->_MorbusType_SysNick = $arr[$this->MorbusType_id][0]['MorbusType_SysNick'];
		}
		return $this->_MorbusType_SysNick;
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'MorbusBase_id';
		$arr[self::ID_KEY]['label'] = 'Общее заболевание';
		unset($arr['code']);
		unset($arr['name']);
		$arr['insdt']['alias'] = 'MorbusBase_insDT';
		$arr['upddt']['alias'] = 'MorbusBase_updDT';
		$arr['morbustype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusType_id',
			'label' => 'Тип заболевания',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['morbustype_sysnick'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'MorbusType_SysNick',
			'select' => 'v_MorbusType.MorbusType_SysNick',
			'join' => 'inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_id = {ViewName}.MorbusType_id',
		);
		$arr['person_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Person_id',
			'label' => 'Человек',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['evn_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Evn_pid',
			'label' => 'Учетный документ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['setdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'MorbusBase_setDT',
			'applyMethod' => '_applySetDt',
			'label' => 'Дата начала заболевания',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['disdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'MorbusBase_disDT',
			'applyMethod' => '_applyDisDt',
			'label' => 'Дата исхода заболевания',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['morbusresult_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusResult_id',
			'label' => 'Результат',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDt($data)
	{
		return $this->_applyDate($data, 'setdt');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDisDt($data)
	{
		return $this->_applyDate($data, 'disdt');
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if (empty($this->MorbusType_id)) {
				throw new Exception('Не указан тип заболевания', 400);
			}
			// проверить существует ли тип заболевания с таким ид
			if (empty($this->MorbusType_SysNick)) {
				throw new Exception('Не указан тип заболевания', 500);
			}
			if (empty($this->Person_id)) {
				throw new Exception('Не указан человек', 400);
			}
			if ($this->isNewRecord) {
				$cnt = $this->getFirstResultFromQuery('
					SELECT count(MorbusBase_id)
					FROM v_MorbusBase (nolock)
					WHERE Person_id = :Person_id
					AND MorbusType_id = :MorbusType_id
					AND MorbusBase_disDT is null
				', array(
					'Person_id' => $this->Person_id,
					'MorbusType_id' => $this->MorbusType_id,
				));
				if ($cnt > 1) {
					throw new Exception('На этом человеке уже есть открытое заболевание с таким типом', 400);
				}
			}
			if ($this->isNewRecord && empty($this->setDT)) {
				$this->setAttribute('setdt', $this->currentDT);
			}
			if (empty($this->setDT)) {
				throw new Exception('Не указана дата начала общего заболевания', 400);
			}
			if (!is_object($this->setDT) || get_class($this->setDT) != 'DateTime') {
				throw new Exception('Неверный формат даты начала общего заболевания', 500);
			}
			// Дата окончания заболевания. Необязательный атрибут. Не может быть больше текущей даты и меньше даты начала заболевания.
			if (!empty($this->disDT)) {
				if (!is_object($this->setDT) || get_class($this->setDT) != 'DateTime') {
					throw new Exception('Неверный формат даты окончания общего заболевания', 500);
				}
				if ($this->disDT < $this->setDT){
					throw new Exception('Дата окончания общего заболевания наступила раньше даты начала общего заболевания');
				}
				// Исход заболевания. Обязательно для заполнения, если заполнен атрибут «Дата окончания» заболевания
				// пока поле не обязательное
				if (false && empty($this->MorbusResult_id)) {
					throw new Exception('Исход общего заболевания обязательно для заполнения, если указана дата окончания общего заболевания');
				}
			}
		}
	}

	/**
	 * Проверка, что пациент в регистре по паллиативной помощи
	 */
	public function checkPersonPalliat($data) {
		$resp = $this->queryResult("
			select
				PR.PersonRegister_id
			from
				v_PersonRegister PR with (nolock)
				inner join v_MorbusType MT with (nolock) on MT.MorbusType_id = PR.MorbusType_id
			where
				PR.Person_id = :Person_id
				and MT.MorbusType_SysNick = 'palliat'
				and PersonRegister_disDate is null
		", array(
			'Person_id' => $data['Person_id']
		));

		return !empty($resp[0]['PersonRegister_id']);
	}

	/**
	 * @param array $data Фильтры и параметры для получения списка типов заболеваний
	 * Может содержать ключи:
	 * Diag_id Диагноз, для фильтрации типов заболеваний, если не указан вернется список всех типов для данного региона
	 * diag_list Список диагнозов, для фильтрации типов заболеваний, если не указан вернется список всех типов для данного региона
	 * includeCommon Включать ли общетерапевтическую группу в список при фильтрации по диагнозам
	 * disableAddEvnNotify Получаем список MorbusType, по которым не надо создавать извещение
	 * Person_id Человек
	 * @return array Список типов заболеваний в формате MorbusType_id => array('MorbusType_SysNick' => 'onko')
	 * @throws Exception
	 */
	public function loadMorbusTypeList($data = array())
	{
		$queryParams = array();
		switch (true) {
			case (isset($data['Diag_id'])):
				$queryParams['Diag_id'] = $data['Diag_id'];
				$query = "
					SELECT
						mt.MorbusType_id,
						mt.MorbusType_SysNick,
						mt.MorbusType_SysNick as RegistryType
					FROM dbo.v_Diag (nolock)
					inner join dbo.v_MorbusDiag md (nolock) on md.Diag_id = v_Diag.Diag_id
					inner join dbo.v_MorbusType mt (nolock) on mt.MorbusType_id = md.MorbusType_id
					WHERE v_Diag.Diag_id = :Diag_id
				";
				if (!empty($data['includeCommon'])) {
					$query .= "
					union all
					SELECT MorbusType_id, MorbusType_SysNick, null as RegistryType FROM dbo.v_MorbusType (nolock)
					WHERE MorbusType_SysNick like 'common'
					";
				}
				if (!empty($data['includePalliat'])) {
					$query .= "
					union all
					SELECT MorbusType_id, MorbusType_SysNick, MorbusType_SysNick as RegistryType FROM dbo.v_MorbusType (nolock)
					WHERE MorbusType_SysNick like 'palliat'
					";
				}
				$query .= "
					order by MorbusType_id
				";
				break;
			case (isset($data['diag_list']) && is_array($data['diag_list'])):
				$data['diag_list'] = array_unique($data['diag_list']);
				$diag_list = implode(',', $data['diag_list']);
				$before_select = '';
				$add_select_md = '';
				$add_join_md = '';
				$add_select_mt = '';
				if ( isset($data['Person_id'])) {
					// получаем данные, которые определяют видимость разделов специфики, кнопок создания извещения
					// если извещения нет, но есть регистр по заболеванию, тогда тоже не надо создавать извещения
					$queryParams['Person_id'] = $data['Person_id'];
					$listMorbusTypeOneByPerson = "'" . implode("','", $this->listMorbusTypeOneByPerson) . "'";
					$before_select = "with TmpDiag as (
						select
						D.Diag_id as Diag_sid,
						isnull(DD.Diag_id, D.Diag_id) as Diag_gid
						from v_Diag D with (nolock)
						left join v_Diag DD with (nolock) on D.Diag_pid = DD.Diag_pid
						where D.Diag_id in ({$diag_list})
					),
					TmpTable as (
						select MorbusType_id, Morbus_id
						from v_EvnNotifyBase with (nolock)
						where Person_id = :Person_id
						and MorbusType_id is not null
						union all
						select MorbusType_id, Morbus_id
						from v_PersonRegister PR with (nolock)
						where Person_id = :Person_id
						and MorbusType_id is not null
						and PersonRegister_disDate is null
					)";
					$add_select_md = ', Diag.Diag_Code, ms.Morbus_id,
					case when exists(select top 1 TmpTable.Morbus_id
					from TmpTable with (nolock)
					where TmpTable.Morbus_id = ms.Morbus_id
					and TmpTable.MorbusType_id = md.MorbusType_id
					) then 1 else 0 end as disableAddEvnNotify';
					$add_join_md = "
					inner join dbo.v_Diag Diag (nolock) on Diag.Diag_id = md.Diag_id
					outer apply (
						select top 1 mb.MorbusBase_id
						from dbo.v_MorbusBase mb with(nolock)
						where mb.Person_id = :Person_id
						and mb.MorbusType_id = md.MorbusType_id 
						and (mb.MorbusBase_disDT is null or mb.MorbusType_id = 3)
						order by mb.MorbusBase_disDT asc, mb.MorbusBase_setDT desc
					) mb
					outer apply (
						select top 1 ms.Morbus_id
						from dbo.v_Morbus ms  with(nolock)
						where ms.MorbusBase_id = mb.MorbusBase_id
						and ms.Morbus_disDT is null
						and (
							mt.MorbusType_SysNick in ({$listMorbusTypeOneByPerson})
							-- проверка существования по той же группе
							OR exists(select top 1 TmpDiag.Diag_gid from TmpDiag with(nolock) where TmpDiag.Diag_sid = md.Diag_id and TmpDiag.Diag_sid = ms.Diag_id)
						)
					) ms
					";
					$add_select_mt = ', Diag.Diag_Code, null as Morbus_id, 0 as disableAddEvnNotify';
				}
				$query = "
					{$before_select}
					SELECT mt.MorbusType_id, mt.MorbusType_SysNick, md.Diag_id {$add_select_md}
					FROM dbo.v_MorbusDiag md (nolock)
					inner join dbo.v_MorbusType mt (nolock) on mt.MorbusType_id = md.MorbusType_id
					{$add_join_md}
					WHERE md.Diag_id in ({$diag_list})
				";
				$query .= "
					union all
					SELECT mt.MorbusType_id, mt.MorbusType_SysNick, Diag.Diag_id {$add_select_mt}
					FROM v_MorbusPalliat MP with(nolock)
					left join dbo.v_Diag Diag  (nolock) on Diag.Diag_id = MP.Diag_id
					inner join dbo.v_MorbusType mt (nolock) on mt.MorbusType_SysNick like 'palliat'
					inner join TmpTable on TmpTable.Morbus_id = MP.Morbus_id and TmpTable.MorbusType_id = mt.MorbusType_id
					--WHERE Diag.Diag_id in ({$diag_list})
					union all
					SELECT mt.MorbusType_id, mt.MorbusType_SysNick, Diag.Diag_id {$add_select_mt}
					FROM v_MorbusGeriatrics MG with(nolock)
					inner join dbo.v_Morbus M (nolock) on M.Morbus_id = MG.Morbus_id
					inner join dbo.v_Diag Diag  (nolock) on Diag.Diag_id = MG.Diag_id
					inner join dbo.v_MorbusType mt (nolock) on mt.MorbusType_SysNick = 'geriatrics'
					WHERE M.Person_id = :Person_id
				";
				if (!empty($data['includeCommon'])) {
					$query .= "
					union all
					SELECT MorbusType_id, MorbusType_SysNick, Diag.Diag_id {$add_select_mt}
					FROM dbo.v_Diag Diag  (nolock)
					inner join dbo.v_MorbusType (nolock) on MorbusType_SysNick like 'common'
					WHERE Diag.Diag_id in ({$diag_list})
					and not exists (select md.Diag_id from dbo.v_MorbusDiag md (nolock) where md.Diag_id = Diag.Diag_id)
					order by MorbusType_id
					";
				}
				break;
			default:
				$query = 'SELECT MorbusType_id, MorbusType_SysNick FROM dbo.v_MorbusType (nolock)';
				break;
		}
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		$tmp = $result->result('array');
		$response = array();
		foreach($tmp as $row) {
			$key = $row['MorbusType_id'];
			unset($row['MorbusType_id']);

			if ( !isset($response[$key]) ) {
				$response[$key] = array();
			}

			$response[$key][] = $row;
		}
		return $response;
	}

	/**
	 * Используется для проверок возможности удаления заболеваний
	 *
	 * @param array $data Фильтры и параметры для получения списка заболеваний
	 * Должен содержать ключи:
	 * mode может принимать значения
	 * - onBeforeDeleteEvn - при удалении учетного документа
	 * - onBeforeDeletePersonRegister - при удалении человека из регистра
	 * onlyOpen Искать только открытые заболевания
	 * diag_list Список диагнозов для фильтрации заболеваний
	 * Person_id Человек
	 * MorbusType_id Тип записи регистра
	 * evn_list Список учетных документов для получения информации об использовании заболевания в других учетных документах
	 * При удалении из регистра должен содержать ноль
	 * @return array Список заболеваний
	 * @throws Exception
	 */
	public function loadMorbusList($data)
	{
		if (false == array_key_exists('onlyOpen',$data) || empty($data['mode'])
			|| empty($data['diag_list']) || false == is_array($data['diag_list'])
			|| empty($data['Person_id'])
		) {
			throw new Exception('Отсутствуют параметры для получения списка заболеваний', 500);
		}
		if (empty($data['evn_list']) && 'onBeforeDeleteEvn' == $data['mode']) {
			throw new Exception('Отсутствует параметр список учетных документов', 500);
		}
		if (empty($data['MorbusType_id']) && 'onBeforeDeletePersonRegister' == $data['mode']) {
			throw new Exception('Отсутствует параметр тип записи регистра', 500);
		}
		$queryParams = array();
		$queryParams['Person_id'] = $data['Person_id'];
		$filtersMorbusBase = '';
		if (false == empty($data['MorbusType_id'])) {
			$filtersMorbusBase .= ' and mb.MorbusType_id = :MorbusType_id';
			$queryParams['MorbusType_id'] = $data['MorbusType_id'];
		}
		if ($data['onlyOpen']) {
			$onlyOpenMorbus = 'and ms.Morbus_disDT is null';
			$filtersMorbusBase .= ' and mb.MorbusBase_disDT is null';
		} else {
			$onlyOpenMorbus = '';
		}
		$data['diag_list'] = array_unique($data['diag_list']);
		$diag_list = implode(',', $data['diag_list']);
		$listMorbusTypeOneByPerson = "'" . implode("','", $this->listMorbusTypeOneByPerson) . "'";
		$before_select = "with TmpDiag as (
			select
			D.Diag_id as Diag_sid,
			isnull(DD.Diag_id, D.Diag_id) as Diag_gid
			from v_Diag D with (nolock)
			left join v_Diag DD with (nolock) on D.Diag_pid = DD.Diag_pid
			where D.Diag_id in ({$diag_list})
		),
		AllMorbusPerson as (
			select
			mb.Person_id,
			ms.Diag_id,
			mb.MorbusType_id,
			mb.MorbusBase_id,
			ms.Morbus_id,
			ms.Morbus_disDT
			from dbo.v_MorbusBase mb (nolock)
			inner join dbo.v_Morbus ms (nolock) on ms.MorbusBase_id = mb.MorbusBase_id
				{$onlyOpenMorbus}
			where mb.Person_id = :Person_id {$filtersMorbusBase}
		)";
		/*
		при вызове p_Morbus_del она удаляет также специфики по Morbus_id
		,
		coalesce(MorbusCrazy.MorbusCrazy_id,MorbusOnko.MorbusOnko_id, MorbusNephro.MorbusNephro_id,
		MorbusTub.MorbusTub_id, MorbusVener.MorbusVener_id, MorbusHiv.MorbusHiv_id,
		MorbusHepatitis.MorbusHepatitis_id, MorbusOrphan.MorbusOrphan_id, MorbusIBS.MorbusIBS_id) as MorbusSpecifics_id
		left join MorbusCrazy (nolock) on MorbusCrazy.Morbus_id = ms.Morbus_id and isnull(MorbusCrazy.MorbusCrazy_deleted,1) = 1
		left join MorbusOnko (nolock) on MorbusOnko.Morbus_id = ms.Morbus_id and isnull(MorbusOnko.MorbusOnko_deleted,1) = 1
		left join MorbusNephro (nolock) on MorbusNephro.Morbus_id = ms.Morbus_id and isnull(MorbusNephro.MorbusNephro_deleted,1) = 1
		left join MorbusTub (nolock) on MorbusTub.Morbus_id = ms.Morbus_id and isnull(MorbusTub.MorbusTub_deleted,1) = 1
		left join MorbusVener (nolock) on MorbusVener.Morbus_id = ms.Morbus_id and isnull(MorbusVener.MorbusVener_deleted,1) = 1
		left join MorbusHiv (nolock) on MorbusHiv.Morbus_id = ms.Morbus_id and isnull(MorbusHiv.MorbusHiv_deleted,1) = 1
		left join MorbusHepatitis (nolock) on MorbusHepatitis.Morbus_id = ms.Morbus_id and isnull(MorbusHepatitis.MorbusHepatitis_deleted,1) = 1
		left join MorbusOrphan (nolock) on MorbusOrphan.Morbus_id = ms.Morbus_id and isnull(MorbusOrphan.MorbusOrphan_deleted,1) = 1
		left join MorbusIBS (nolock) on MorbusIBS.Morbus_id = ms.Morbus_id and isnull(MorbusIBS.MorbusIBS_deleted,1) = 1

		left join MorbusProf (nolock) on MorbusProf.Morbus_id = ms.Morbus_id and isnull(MorbusProf.MorbusProf_deleted,1) = 1
		left join MorbusACS (nolock) on MorbusACS.Morbus_id = ms.Morbus_id and isnull(MorbusACS.MorbusACS_deleted,1) = 1

			-- проверка существования одно на человеке
			mt.MorbusType_SysNick in ({$listMorbusTypeOneByPerson})
			-- проверка существования по той же группе диагнозов
			OR exists(select top 1 TmpDiag.Diag_gid from TmpDiag with(nolock) where TmpDiag.Diag_sid = md.Diag_id and TmpDiag.Diag_gid = ms.Diag_id)
		 */
		$add_select = '';
		if ('onBeforeDeletePersonRegister' == $data['mode']) {
			$data['evn_list'] = array(0);
			$add_select .= ",
			ms.Morbus_disDT,
			case when exists(
				select top 1 EvnNotifyBase_id
				from v_EvnNotifyBase with (nolock)
				where v_EvnNotifyBase.Morbus_id = ms.Morbus_id
			) then 1 else 0 end as hasEvnNotify";
		}
		$data['evn_list'] = array_unique($data['evn_list']);
		$evn_list = implode(',', $data['evn_list']);
		$before_select .= ",
			OtherEvn as (
				select
				v_evn.Evn_id,
				AllMorbusPerson.Morbus_id
				from v_evn (nolock)
				left join EvnSection (nolock) on EvnSection.EvnSection_id = v_evn.Evn_id
				left join EvnVizitPl (nolock) on EvnVizitPl.EvnVizitPL_id = v_evn.Evn_id
				inner join v_MorbusDiag (nolock) on v_MorbusDiag.Diag_id = ISNULL(EvnSection.Diag_id, EvnVizitPl.Diag_id)
				inner join v_MorbusType (nolock) on v_MorbusType.MorbusType_id = v_MorbusDiag.MorbusType_id
				inner join AllMorbusPerson with(nolock) on AllMorbusPerson.MorbusType_id = v_MorbusDiag.MorbusType_id
				and (
					v_MorbusType.MorbusType_SysNick in ({$listMorbusTypeOneByPerson})
					OR exists(select top 1 TmpDiag.Diag_gid from TmpDiag with(nolock) where TmpDiag.Diag_sid = v_MorbusDiag.Diag_id and TmpDiag.Diag_gid = AllMorbusPerson.Diag_id)
				)
				where v_evn.Person_id = :Person_id
				and v_evn.Evn_id not in ({$evn_list})
			)";
		$add_select .= ",
			case when exists(
				select top 1 OtherEvn.Morbus_id
				from OtherEvn with(nolock)
				where OtherEvn.Morbus_id = ms.Morbus_id
			) then 1 else 0 end as hasOtherEvn";

		if ('onBeforeDeleteEvn' == $data['mode']) {
			$add_select .= ",
			case when exists(
				select top 1 PersonRegister_id
				from v_PersonRegister with (nolock)
				where v_PersonRegister.Morbus_id = ms.Morbus_id
			) then 1 else 0 end as hasPersonRegister";
			$add_select .= ",
			case 
			when mt.MorbusType_SysNick = 'onko' and exists(
				select top 1 EvnNotifyBase_id
				from v_EvnNotifyBase with (nolock)
				inner join v_EvnOnkoNotify EON with (nolock) on v_EvnNotifyBase.Morbus_id = EON.Morbus_id
				where v_EvnNotifyBase.Morbus_id = ms.Morbus_id
					and v_EvnNotifyBase.EvnNotifyBase_pid in ({$evn_list})
					and EON.EvnOnkoNotify_niDate is null
			) then 1
			when mt.MorbusType_SysNick <> 'onko' and exists(
				select top 1 EvnNotifyBase_id
				from v_EvnNotifyBase with (nolock)
				where v_EvnNotifyBase.Morbus_id = ms.Morbus_id
					and v_EvnNotifyBase.EvnNotifyBase_pid in ({$evn_list})
			) then 1 else 0 end as hasEvnNotify";
		}
		$query = "
			{$before_select}
			SELECT
			md.Diag_id,
			mt.MorbusType_id,
			mt.MorbusType_SysNick,
			ms.MorbusBase_id,
			ms.Morbus_id,
			case when exists(
				select top 1 AllMorbusPerson.Morbus_id
				from AllMorbusPerson with(nolock)
				where AllMorbusPerson.MorbusBase_id = ms.MorbusBase_id and AllMorbusPerson.Morbus_id <> ms.Morbus_id
			) then 1 else 0 end as hasOtherMorbus
			{$add_select}
			FROM AllMorbusPerson ms with(nolock)
			inner join dbo.v_MorbusType mt (nolock) on mt.MorbusType_id = ms.MorbusType_id
			inner join dbo.v_MorbusDiag md (nolock) on md.MorbusType_id = ms.MorbusType_id and md.Diag_id in ({$diag_list})
			WHERE
			mt.MorbusType_SysNick in ({$listMorbusTypeOneByPerson})
			OR exists(select top 1 TmpDiag.Diag_gid from TmpDiag with(nolock) where TmpDiag.Diag_sid = md.Diag_id and TmpDiag.Diag_gid = ms.Diag_id)
		";
		//throw new Exception(getDebugSQL($query, $queryParams), 600);
		$result = $this->db->query($query, $queryParams);
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		return $result->result('array');
	}

	/**
	 * Проверка наличия заболевания указанного типа с возможностью загрузки данных специфики
	 *
	 * @param string $MorbusType_SysNick
	 * @param int $Person_id
	 * @param int $Diag_id
	 * @param string $add_select
	 * @param string $add_join
	 * @param bool $onlyOpen Искать только открытые заболевания
	 * иначе при закрытом заболевании должен выходить вопрос о возвращении в регистр
	 * @return bool|array Если заболевание ещё не создано, возвращается пустой массив, а в случае ошибки - false
	 */
	public function checkExistsExtended($MorbusType_SysNick, $Person_id, $Diag_id = null, $add_select = '', $add_join = '', $onlyOpen = false)
	{
		if (empty($Person_id) || empty($MorbusType_SysNick)) {
			return false;
		}
		$this->load->library('swMorbus');
		$params = array(
			'Person_id' => $Person_id,
			'MorbusType_id' => swMorbus::getMorbusTypeIdBySysNick($MorbusType_SysNick),
		);
		if (empty($params['MorbusType_id'])) {
			return false;
		}
		$morbus_add_where = '';
		$morbus_base_add_where = '';
		if ($onlyOpen) {
			$morbus_add_where .= ' AND Morbus.Morbus_disDT is null';
			$morbus_base_add_where .= ' AND MorbusBase.MorbusBase_disDT is null';
		}
		if (false == in_array($MorbusType_SysNick, $this->listMorbusTypeOneByPerson)) {
			// требуется проверять заболевание также по группе диагнозов
			if (empty($Diag_id)) {
				return false;
			}
			$params['Diag_id'] = $Diag_id;
			if ($MorbusType_SysNick == 'onko') {
				$morbus_add_where .= ' AND Morbus.Diag_id = :Diag_id';
			} else {
				$morbus_add_where .= ' AND Morbus.Diag_id in (select DD.Diag_id from v_Diag D with (nolock) left join v_Diag DD with (nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id)';
			}
		}
		$query = "
			select top 1
				MorbusBase.MorbusBase_id,
				Morbus.Morbus_id
				{$add_select}
			from
				v_MorbusBase MorbusBase with (nolock)
				inner join v_Morbus Morbus with (nolock) on MorbusBase.MorbusBase_id = Morbus.MorbusBase_id {$morbus_add_where}
				{$add_join}
			where
				MorbusBase.Person_id = :Person_id
				AND MorbusBase.MorbusType_id = :MorbusType_id
				{$morbus_base_add_where}
			order by
				Morbus.Morbus_disDT ASC, Morbus.Morbus_setDT DESC
		";// MorbusBase.MorbusBase_disDT ASC, MorbusBase.MorbusBase_setDT DESC,
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Определение диагноза последнего учетного документа, относящегося к одному заболеванию
	 *
	 * @param string $MorbusType_SysNick
	 * @param int $Evn_pid
	 * @param int $Person_id
	 * @param int $Diag_id
	 * Обязательные параметры: $MorbusType_id, Evn_pid или пара Person_id и Diag_id
	 * @return array
	 * Пустой массив вернется,
	 * если не существует учетного документа $Evn_pid или в нем не указан диагноз
	 * если диагноз учетного документа $Evn_pid не относится к типу заболеваний
	 * если не существует ни одного движения/посещения с диагнозом той же группы заболеваний, что и у учетного документа или $Diag_id
	 * @comment Диагноз заболевания – это диагноз последнего учетного документа, относящегося к данному заболеванию
	 * @throws Exception
	 */
	public function loadLastEvnData($MorbusType_SysNick, $Evn_pid = null, $Person_id = null, $Diag_id = null, $sopid = null, $EvnDiagPLSop_id = null)
	{
		if (empty($MorbusType_SysNick)) {
			throw new Exception('Не передан параметр Тип заболевания', 500);
		}
		$params = array(
			'MorbusType_SysNick' => $MorbusType_SysNick,
		);
		if ($Evn_pid) {
			$params['Evn_pid'] = $Evn_pid;
			$params['EvnDiagPLStomSop_id'] = $sopid;
			$params['EvnDiagPLSop_id'] = $EvnDiagPLSop_id;
			$params['Person_id'] = null;
			$params['Diag_id'] = $Diag_id;
		} else if ($Person_id /*&& $Diag_id*/) {
			$params['Person_id'] = $Person_id;
			$params['EvnDiagPLStomSop_id'] = $sopid;
			$params['EvnDiagPLSop_id'] = $EvnDiagPLSop_id;
			$params['Diag_id'] = $Diag_id;
			$params['Evn_pid'] = null;
		} else {
			throw new Exception('Не переданы фильтры по заболеваниям ', 500);
		}
		$group_where = '';
		if (false == in_array($MorbusType_SysNick, $this->listMorbusTypeOneByPerson)) {
			// требуется фильтровать список учетных документов по группе диагнозов
			if ($MorbusType_SysNick == 'onko') {
				$group_where = 'AND v_MorbusDiag.Diag_id = @Diag_id';
			} else {
				$group_where = 'AND v_MorbusDiag.Diag_id in (select DD.Diag_id from v_Diag D with (nolock) left join v_Diag DD with (nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = @Diag_id)';
			}
		}
		$query = "
			declare
				@Person_id bigint = :Person_id,
				@Diag_id bigint = :Diag_id,
				@Evn_pid bigint = :Evn_pid,
				@EvnDiagPLStomSop_id bigint = :EvnDiagPLStomSop_id,
				@EvnDiagPLSop_id bigint = :EvnDiagPLSop_id;

			if isnull(@Evn_pid, 0) > 0 and @Diag_id is null
			begin
				select @Person_id = Evn.Person_id, @Diag_id = coalesce(EPLDSO.Diag_spid,PL.Diag_spid,ST.Diag_spid,EPLDD.Diag_spid,ED.Diag_id,EDPS.Diag_id,EPS.Diag_pid,PL.Diag_id,ST.Diag_id,DD.Diag_id) from v_Evn Evn with (nolock)
				left join v_EvnVizitDispDop DD with (nolock) on Evn.Evn_id = DD.EvnVizitDispDop_id
				left join v_EvnPLDispDop13 EPLDD with (nolock) on EPLDD.EvnPLDispDop13_id = DD.EvnVizitDispDop_pid
				left join v_EvnPLDispScreenOnko EPLDSO with (nolock) on EPLDSO.EvnPLDispScreenOnko_pid = Evn.Evn_id
				left join EvnVizitPL PL with (nolock) on Evn.Evn_id = PL.EvnVizitPL_id
				left join EvnSection ST with (nolock) on Evn.Evn_id = ST.EvnSection_id
				left join EvnPS EPS with (nolock) on Evn.Evn_id = EPS.EvnPS_id
				left join v_EvnDiagPLStom EDPS with (nolock) on EDPS.EvnDiagPLStom_id = Evn.Evn_id
				left join v_EvnDiag ED WITH (NOLOCK) on ED.EvnDiag_id = isnull(@EvnDiagPLStomSop_id, @EvnDiagPLSop_id)
				where Evn.Evn_id = @Evn_pid
				and Evn.EvnClass_id in (11,13,14,30,32,19)
			end
			
			else 
			
			if isnull(@Evn_pid, 0) > 0 
			begin
				select @Person_id = Evn.Person_id from v_Evn Evn with (nolock)
				where Evn.Evn_id = @Evn_pid
				and Evn.EvnClass_id in (11,13,14,30,32,19)
			end

			select top 1
				Evn.Evn_id,
				Evn.Person_id,
				@Diag_id as filter_Diag_id,
				D.Diag_Code,
				v_MorbusDiag.Diag_id as Diag_id
			from v_Evn Evn with (nolock)
			left join v_EvnVizitDispDop DD with (nolock) on Evn.Evn_id = DD.EvnVizitDispDop_id
			left join v_EvnPLDispDop13 EPLDD with (nolock) on EPLDD.EvnPLDispDop13_id = DD.EvnVizitDispDop_pid
			left join v_EvnPLDispScreenOnko EPLDSO with (nolock) on EPLDSO.EvnPLDispScreenOnko_pid = Evn.Evn_id
			left join EvnVizitPL PL with (nolock) on Evn.Evn_id = PL.EvnVizitPL_id
			left join EvnSection ST with (nolock) on Evn.Evn_id = ST.EvnSection_id
			left join EvnPS EPS with (nolock) on Evn.Evn_id = EPS.EvnPS_id
			left join v_EvnDiagPLStom EDPS with (nolock) on EDPS.EvnDiagPLStom_pid = Evn.Evn_id
			left join v_EvnDiag ED WITH (NOLOCK) on ED.EvnDiag_id = isnull(@EvnDiagPLStomSop_id, @EvnDiagPLSop_id)
			inner join v_MorbusType with (nolock) on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			cross apply (
				Select top 1 * from  v_MorbusDiag with (nolock) where v_MorbusDiag.MorbusType_id = v_MorbusType.MorbusType_id
					AND v_MorbusDiag.Diag_id = coalesce(EPLDSO.Diag_spid,PL.Diag_spid,ST.Diag_spid,EPLDD.Diag_spid,ED.Diag_id,EDPS.Diag_id,EPS.Diag_pid,PL.Diag_id,ST.Diag_id,DD.Diag_id)
					{$group_where}
			) v_MorbusDiag
			left join v_Diag D with (nolock) on D.Diag_id = v_MorbusDiag.Diag_id
			where
				Evn.Person_id = @Person_id
				and Evn.EvnClass_id in (11,13,14,30,32,19)
				and exists(select top 1 md.Diag_id from v_MorbusDiag md with (nolock) where md.MorbusType_id = v_MorbusType.MorbusType_id and md.Diag_id = @Diag_id)
			order by
				Evn.Evn_setDT desc
		";
		// throw new Exception(getDebugSQL($query, $params));
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $evn_list
	 * @return array
	 */
	public function loadEvnInfectNotifyList($evn_list)
	{
		if (empty($evn_list) || false == is_array($evn_list)) {
			return array();
		}
		$evn_list = array_unique($evn_list);
		$evn_list = implode(',', $evn_list);
		$query = "
			select EvnInfectNotify_id, EvnInfectNotify_pid
			from v_EvnInfectNotify with (nolock)
			where EvnInfectNotify_pid in ({$evn_list})
		";
		$result = $this->db->query($query, array());
		if ( !is_object($result) ) {
			return array();
		}
		return $result->result('array');
	}

	/**
	 * Создание общего заболевания, если его не существует
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['Person_id']) || empty($data['MorbusType_id'])) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$data['MorbusBase_id'] = $this->getFirstResultFromQuery('
					SELECT top 1 MorbusBase_id
					FROM v_MorbusBase (nolock)
					WHERE Person_id = :Person_id
					AND MorbusType_id = :MorbusType_id
					AND MorbusBase_disDT is null
					order by MorbusBase_setDT desc
				', array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id'],
		));
		if (empty($data['MorbusBase_id'])) {
			$data['MorbusBase_id'] = null;
		} else {
			$this->_saveResponse['MorbusBase_id'] = $data['MorbusBase_id'];
			return $this->_saveResponse;
		}
		$data['scenario'] = self::SCENARIO_AUTO_CREATE;
		$obj = new MorbusCommon_model();
		return $obj->doSave($data, $isAllowTransaction);
	}
}