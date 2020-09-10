<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		12.2014
 */

/**
 * Вспомогательная библиотека для работы с записями регистра
 * с использованием справочников PersonRegisterDiag, PersonRegisterType
 *
 * @package		PersonRegister
 * @author		Alexander Permyakov
 */
class SwPersonRegister
{
	private static $ci_instance = null;
	private static $typeList = null;
	private static $staticPersonRegisterModels = array();

	/**
	 * @return CI_Controller
	 */
	private static function getCiInstance()
	{
		if (isset(self::$ci_instance)) {
			return self::$ci_instance;
		}
		self::$ci_instance =& get_instance();
		return self::$ci_instance;
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return string
	 */
	public static function getPersonRegisterTypeClassName($type)
	{
		if (in_array($type, array('base'))) {
			$type = 'base';
		}
		$type = str_replace(' ', '_', $type);
		$words = explode('_', $type);
		foreach ($words as $i => $word) {
			$words[$i] = ucfirst($word);
		}
		return implode('', $words);
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return PersonRegisterBase_model
	 */
	public static function getStaticPersonRegister($type = null)
	{
		if (empty($type)) {
			$type = 'base';
		}
		$modelName = self::getModelName($type);
		$modelAlias = 'staticPersonRegister'.$type;
		if (empty(self::$staticPersonRegisterModels[$type])) {
			self::getCiInstance()->load->model($modelName, $modelAlias);
			self::$staticPersonRegisterModels[$type] = self::getCiInstance()->{$modelAlias};
		}
		return self::$staticPersonRegisterModels[$type];
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return string
	 */
	public static function getModelName($type)
	{
		switch ($type) {
			case 'onko':
				$name = 'PersonRegisterBase_model';
				break;
			default:
				$name = 'PersonRegister' . self::getPersonRegisterTypeClassName($type) . '_model';
				break;
		}
		return $name;
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return string
	 */
	public static function getEvnNotifyRegisterModelName($type)
	{
		switch ($type) {
			default:
				$name = 'EvnNotifyRegister_model';
				break;
		}
		return $name;
	}

	/**
	 * @return array
	 */
	public static function listPersonRegisterTypeOneByPerson()
	{
		return array(/*'acs','pregnancy','crazy','hepa','tub','hiv','nephro','prof','ibs'*/);
	}
	
	/**
	 * @param string $type PersonRegisterType_SysNick
	 * Может использоваться для временного отключения регистров работающих с использованием справочника PersonRegisterType
	 * @return bool
	 */
	public static function isAllow($type)
	{
		$regionNick = self::getStaticPersonRegister()->getRegionNick();
		switch ($type) {
			/*
			Для этих пока оставляем работу по старой схеме
			case 'crazy'://Психиатрия
			case 'narko'://Наркология
			case 'hepa'://гепатит
			case 'tub'://туберкулез
			case 'vener'://Венерология
			case 'hiv': // HIV
			case 'infect058':// только для видимости кнопки добавления экстренного извещения об инфекционном заболевании, отравлении
				$isAllow = true; // для всех регионов
				break;
			 */
			case 'onko'://Онкология
			case 'orphan'://Орфанное
			case 'nolos': // ВЗН (7 нозологий)
			case 'prof': // профзаболевания
			case 'suicide': // суицид
			case 'palliat': // паллиативная помощь
				//$isAllow = ($regionNick != 'saratov'); // для всех регионов, кроме Саратова
				$isAllow = true; // для всех регионов
				break;
			case 'fmba': // ФМБА
				$isAllow = ($regionNick == 'saratov'); // для Саратова
				//$isAllow = true; // для всех регионов
				break;
			/*
			Для этих пока оставляем работу по старой схеме
			case 'nephro': // Нефрология в PersonRegisterType для всех регионов
			case 'ibs':
				$isAllow = ('perm' == $regionNick);
				break;
			case 'acs': // ОКС только для 2,30
				$isAllow = (in_array($regionNick, array('ufa','astra')));
				break;
			case 'prof': // Профзаболевания временно закрыты
			*/
			default:
				$isAllow = false;
				break;
		}
		return $isAllow;
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return bool
	 */
	public static function isMorbusRegister($type)
	{
		$response = false;
		// 'acs','prof' сейчас как заболевания, по может будут переделаны по новой схеме
		if (in_array($type, array(/*'onko','crazy','narko','hepa','tub','vener','hiv','nephro','ibs'*/))) {
			$response = true;
		}
		return $response;
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return bool
	 */
	public static function isAllowMorbusType($type)
	{
		$response = false;
		if (self::isMorbusRegister($type) || in_array($type, array('nolos','orphan','onko'))) {
			$response = true;
		}
		return $response;
	}

	/**
	 * @param string $type PersonRegisterType_SysNick
	 * @return string Код группы пользователей «Регистр по ...»
	 */
	public static function getUserGroupCode($type)
	{
		return self::getStaticPersonRegister($type)->userGroupCode;
	}

	/**
	 * @return array Список типов в формате PersonRegisterType_id => array('PersonRegisterType_SysNick' => 'onko')
	 * @throws Exception
	 */
	public static function getTypeListAll()
	{
		if (isset(self::$typeList)) {
			return self::$typeList;
		}
		self::$typeList = self::getStaticPersonRegister()->loadTypeList();
		return self::$typeList;
	}

	/**
	 * @param $sysnick
	 * @return int|null
	 */
	public static function getTypeIdBySysNick($sysnick)
	{
		$arr = self::getTypeListAll();
		$id = null;
		foreach ($arr as $key => $row) {
			if (strtolower($sysnick) == strtolower($row['PersonRegisterType_SysNick'])) {
				$id = $key;
				break;
			}
		}
		return $id;
	}

	/**
	 * Обработка проставления в системе у персона атрибута «Даты смерти»
	 *
	 * При проставлении в системе у персона атрибута «Даты смерти» проводить контроль на наличие в системе записи регистра
	 * с пустым атрибутом «Дата исключения из регистра» на данного персона.
	 * Если такая запись существует, то формировать сообщение пользователям с указанной группой «Регистр по 7 нозологиям»:
	 * «Пациент ФИО ДР включен в регистр по 7 нозологиям, но у него указана дата смерти. Возможно, его нужно исключить из регистра. ОК».
	 * (ФИО отображать в виде ссылки, при нажатии на которую открывать ЭМК пациента)
	 * @param array $query_params Параметры, с которыми была вызвана хранимка p_Person_kill
	 * @throws Exception
	 * @return bool
	 */
	public static function onPersonDead($query_params)
	{
		if (empty($query_params['Person_id'])) {
			throw new Exception ('Где-то в метод onPersonDead не был передан параметр Person_id', 500);
		}
		if (empty($query_params['pmUser_id'])) {
			throw new Exception ('Где-то в метод onPersonDead не был передан параметр pmUser_id', 500);
		}
		if (empty($query_params['Person_deadDT'])) {
			return true;
		}
		$types_arr = array('nolos','orphan');
		$types_str = array();
		// исключаем регистры, которые отключены для этого региона
		foreach ($types_arr as $type) {
			if (self::isAllow($type)) {
				$types_str[] = "'{$type}'";
			}
		}
		if (empty($types_str)) {
			return true;
		}
		$types_str = implode(',', $types_str);
		// смотрим в каких регистрах есть человек
		$result = self::getStaticPersonRegister()->db->query("
			select
				PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
			from v_PersonRegisterType PRT
			inner join v_PersonRegister PR on PR.PersonRegisterType_id = PRT.PersonRegisterType_id
				and PR.Person_id = :Person_id
				and PR.PersonRegister_disDate is null
			inner join v_PersonState PS on PS.Person_id = PR.Person_id
			where PRT.PersonRegisterType_SysNick in ({$types_str})
		", array(
			'Person_id' => $query_params['Person_id'],
		));
		if (!is_object($result)) {
			return true;
		}
		$tmp = $result->result('array');
		if (empty($tmp)) {
			return true;
		}
		// обрабатываем ответ
		$reg_data = array();
		foreach ($tmp as $msgData) {
			$type = $msgData['PersonRegisterType_SysNick'];
			switch ($type) {
				case 'orphan': $name = 'по орфанным заболеваниям'; break;
				case 'nolos': $name = 'по ВЗН'; break;
				default: $name = ''; break;
			}
			if (empty($name)) {
				continue;
			}
			if (empty($reg_data[$type])) {
				$reg_data[$type] = $msgData;
				$reg_data[$type]['name'] = $name;
				$reg_data[$type]['users'] = self::getStaticPersonRegister($type)->loadUsers((array(
					'limit' => 20,
				)));
			}
		}
		unset($tmp);
		// отсылаем сообщения пользователям регистра
		self::getCiInstance()->load->model('Messages_model');
		foreach ($reg_data as $msgData) {
			if (empty($msgData['users'])) {
				continue;
			}
			foreach ($msgData['users'] as $user) {
				self::getCiInstance()->Messages_model->autoMessage(array(
					'autotype' => 5, // - изменение состояния пациента
					'pmUser_id' => $query_params['pmUser_id'],
					'User_rid' => $user['PMUser_id'],//  - Пользователю с ID
					'type' => 1,
					'title' => 'Возможно нужно исключить пациента из регистра',
					'text' => "Пациент {$msgData['Person_SurName']} {$msgData['Person_FirName']} {$msgData['Person_SecName']}
						{$msgData['Person_BirthDay']} включен в регистр {$msgData['name']}, но у него указана дата смерти. Возможно, его нужно исключить из регистра.",
				));
			}
		}
		return true;
	}

	/**
	 * Проверка возможности изменить диагноз в учетном документе
	 * @param EvnAbstract_model $evn
	 * @throws Exception
	 * @return bool
	 */
	public static function onBeforeChangeDiag(EvnAbstract_model $evn)
	{
		// только для 'EvnVizitPL', 'EvnVizitDispDop', 'EvnVizitPLStom', 'EvnSection'
		if (!in_array($evn->evnClassId, array(11, 13, 14, 32))) {
			return true;
		}
		$tmp = self::getStaticPersonRegister()->getFirstResultFromQuery('
			select
				EvnNotifyRegister_id as "EvnNotifyRegister_id"
			from v_EvnNotifyRegister
			where v_EvnNotifyRegister.EvnNotifyRegister_pid = :Evn_id and NotifyType_id = 1 and EvnNotifyRegister_niDate is not null
			limit 1
		', array('Evn_id' => $evn->id));
		if ($tmp > 0) {
			//нужно откатить транзакцию
			throw new Exception('Если из учетного документа было создано направление на включение в регистр, то в учетном документе нельзя менять диагноз');
		}
		return true;
	}

	/**
	 * Логика перед удалением объекта
	 *
	 * Проверка возможности удалить: нельзя, если из случая создано направление на включение в регистр.
	 * @param EvnAbstract_model $evn
	 * @throws Exception
	 * @return bool
	 */
	public static function onBeforeDeleteEvn(EvnAbstract_model $evn)
	{
		// только для 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection', 'EvnPL', 'EvnPLStom', 'EvnPS'
		if (!in_array($evn->evnClassId, array(11, 13, 32, 3, 6, 30))) {
			return true;
		}
		$diag_list = array();
		$evn_list = array();
		switch (true) {
			case (in_array($evn->evnClassId, array(11, 13, 32))):
				if (!empty($evn->Diag_id)) {
					$diag_list[] = $evn->Diag_id;
					$evn_list[] = $evn->id;
				}
				break;
			case (in_array($evn->evnClassId, array(3, 6))):
				/**
				 * @var EvnPL_Model $evn
				 */
				// нужно получить диагнозы, введенные в посещениях
				foreach ($evn->evnVizitList as $id => $row) {
					if (!empty($row['Diag_id'])) {
						$diag_list[] = $row['Diag_id'];
						$evn_list[] = $id;
					}
				}
				break;
			case (in_array($evn->evnClassId, array(30))):
				/**
				 * @var EvnPS_Model $evn
				 */
				// нужно получить диагнозы, введенные в движениях
				foreach ($evn->listEvnSectionData as $id => $row) {
					if (!empty($row['Diag_id'])) {
						$diag_list[] = $row['Diag_id'];
						$evn_list[] = $id;
					}
				}
				break;
		}
		if (empty($evn_list)) {
			return true;
		}
		$evn_list = implode(',', $evn_list);
		$tmp = self::getStaticPersonRegister()->getFirstResultFromQuery("
			select
				EvnNotifyRegister_id as \"EvnNotifyRegister_id\"
			from v_EvnNotifyRegister
			where v_EvnNotifyRegister.EvnNotifyRegister_pid in ({$evn_list}) and NotifyType_id = 1
			limit 1
		", array());
		if ($tmp > 0) {
			//нужно откатить транзакцию
			throw new Exception('Если из учетного документа было создано направление на включение в регистр, то учетный документ нельзя удалить');
		}
		return true;
	}

	/**
	 * Добавляем в ответ список типов регистра для учетного документа
	 * @param EvnAbstract_model $evn
	 * @throws Exception
	 * @return array
	 */
	public static function onAfterSaveEvn(EvnAbstract_model $evn)
	{
		// только для 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection'
		if (!in_array($evn->evnClassId, array(11, 13, 32))) {
			return array();
		}
		$response = array();
		$response['listPersonRegister'] = array();
		if (empty($evn->Diag_id)) {
			return $response;
		} else {
			$types = self::getStaticPersonRegister()->loadTypeListByDiag($evn->Diag_id);
		}
		foreach($types as $key => $row) {
			$PersonRegisterType_SysNick = $row['PersonRegisterType_SysNick'];
			if (false == self::isAllow($PersonRegisterType_SysNick)) {
				continue;
			}
			$response['listPersonRegister'][$PersonRegisterType_SysNick] = $row;
		}
		return $response;
	}

	/**
	 * Добавляем в ответ список типов регистра для учетного документа
	 * @param array $evnDataList
	 * @param string $sectionCode
	 * @return array
	 */
	public static function processingEvnData($evnDataList, $sectionCode)
	{
		if (false == in_array($sectionCode, array('EvnVizitPL','EvnVizitPLStom','EvnSection'))) {
			return $evnDataList;
		}
		$idKey = $sectionCode . '_id';
		if (empty($evnDataList) || empty($evnDataList[0]['Person_id']) || empty($evnDataList[0][$idKey])) {
			return $evnDataList;
		}
		$diag_list = array(); // список идешников диагнозов
		$diag_evn_list = array(); // список идешников учетных документов
		foreach ($evnDataList as $i => $row) {
			if (!empty($row['Diag_id'])) {
				$diag = $row['Diag_id'].'';
				if (false == in_array($diag, $diag_list)) {
					$diag_list[] = $diag;
				}
				if (empty($diag_evn_list[$diag])) {
					$diag_evn_list[$diag] = array();
				}
				$diag_evn_list[$diag][] = $row[$idKey];
			}
		}
		if (empty($diag_list)) {
			return $evnDataList;
		}
		$arr = self::getStaticPersonRegister()->loadTypeListByDiagList($diag_list);
		$diag_data = array();
		foreach ($diag_list as $diag) {
			$diag_data[$diag] = array();
			foreach ($arr as $key => $row) {
				if ($diag == $row['Diag_id']) {
					unset($row['Diag_id']);
					$diag_data[$diag][$key] = $row;
				}
			}
		}
		foreach($evnDataList as &$evn) {
			$evn['listPersonRegister'] = array();
			if (empty($evn['Diag_id'])) {
				continue;
			}
			$evn_diag = $evn['Diag_id'];
			if (empty($diag_data[$evn_diag])) {
				continue;
			}
			foreach ($diag_data[$evn_diag] as $key => $row) {
				$PersonRegisterType_SysNick = $row['PersonRegisterType_SysNick'];
				if (false == self::isAllow($PersonRegisterType_SysNick)) {
					continue;
				}
				$evn['listPersonRegister'][$PersonRegisterType_SysNick] = $row;
			}
			if (isset($evn['listPersonRegister']['prof'])) {
				$isDisabledAddEvnNotify = (empty($evn['accessType']) || 'edit'!=$evn['accessType']);
				$evn['isVisibleProf'] = 1;
				$evn['isDisabledAddEvnNotifyProf'] = ($isDisabledAddEvnNotify);
			}
		}
		return $evnDataList;
	}

}
