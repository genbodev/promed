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
 * Вспомогательная библиотека для работы со спецификой заболеваний
 *
 * @package		Common
 * @author		Alexander Permyakov
 */
class SwMorbus
{
	private static $ci_instance = null;
	private static $morbusTypeList = null;
	private static $staticMorbusCommon = null;
	private static $staticMorbusSimple = null;
	private static $staticPersonRegister = null;
	private static $staticNotifyNephro = null;
	private static $staticMorbusOnkoSpecTreat = null;
	private static $staticMorbusSpecifics = array();

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
	 * @return MorbusCommon_model
	 */
	public static function getStaticMorbusCommon()
	{
		if (empty(self::$staticMorbusCommon)) {
			self::getCiInstance()->load->model('MorbusCommon_model', 'staticMorbusCommon');
			self::$staticMorbusCommon = self::getCiInstance()->staticMorbusCommon;
		}
		return self::$staticMorbusCommon;
	}

	/**
	 * @return MorbusSimple_model
	 */
	public static function getStaticMorbusSimple()
	{
		if (empty(self::$staticMorbusSimple)) {
			self::getCiInstance()->load->model('MorbusSimple_model', 'staticMorbusSimple');
			self::$staticMorbusSimple = self::getCiInstance()->staticMorbusSimple;
		}
		return self::$staticMorbusSimple;
	}

	/**
	 * @return PersonRegister_model
	 */
	public static function getStaticPersonRegister()
	{
		if (empty(self::$staticPersonRegister)) {
			self::getCiInstance()->load->model('PersonRegister_model', 'staticPersonRegister');
			self::$staticPersonRegister = self::getCiInstance()->staticPersonRegister;
		}
		return self::$staticPersonRegister;
	}

	/**
	 * @return RegionNick
	 */
	public static function getRegionNick()
	{
		return self::getStaticMorbusCommon()->getRegionNick();
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return swModel|bool
	 */
	public static function getStaticMorbusSpecificsModel($morbus_type)
	{
		if (empty(self::$staticMorbusSpecifics[$morbus_type])) {
			$model_name = self::getNameMorbusSpecificsModel($morbus_type);
			if ($model_name) {
				$model_name = self::getNameMorbusSpecificsModel($morbus_type);
				$alias = $model_name . '_' . $morbus_type;
				self::getCiInstance()->load->model($model_name, $alias);
				self::$staticMorbusSpecifics[$morbus_type] = self::getCiInstance()->$alias;
			} else {
				self::$staticMorbusSpecifics[$morbus_type] = false;
			}
		}
		return self::$staticMorbusSpecifics[$morbus_type];
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return string
	 */
	public static function getNameMorbusSpecificsModel($morbus_type)
	{
		if (false == self::isAllow($morbus_type)) {
			return '';
		}
		$table_name = self::getNameMorbusSpecificsTable($morbus_type);
		if ($table_name) {
			switch ($morbus_type) {
				case 'onko'://онкоспецифика
					$model_name = 'MorbusOnkoSpecifics_model';
					break;
				default:
					$model_name = $table_name . '_model';
					break;
			}
			return $model_name;
		}
		return '';
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return bool
	 */
	public static function isMorbus($morbus_type)
	{
		return in_array($morbus_type, array('onko', 'crazy', 'narc', 'hepa', 'tub', 'vener', 'hiv', 'nephro', 'ibs', 'palliat'));
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return bool
	 */
	public static function isAllow($morbus_type)
	{
		switch ($morbus_type) {
			case 'onko'://Онкология
			case 'crazy'://Психиатрия (Наркология)
			case 'narc'://Психиатрия (Наркология)
			case 'hepa'://гепатит
			case 'orphan'://Орфанное
			case 'tub'://туберкулез
			case 'vener'://Венерология
			case 'hiv': // HIV
			case 'infect058':// только для видимости кнопки добавления экстренного извещения об инфекционном заболевании, отравлении
			case 'palliat': // Паллиативная помощь
			case 'geriatrics': // Гериатрия
			case 'gibt': // ГИБТ
				$isAllow = true; // для всех регионов
				break;
			case 'nephro': // Нефрология В БД MorbusType для всех регионов
			case 'ibs':
				$regionNick = self::getCiInstance()->load->getRegionNick();
				$isAllow = in_array($regionNick, array('perm', 'ufa'));
				break;
			case 'acs': // ОКС только для 2,30
				$regionNick = self::getCiInstance()->load->getRegionNick();
				$isAllow = (in_array($regionNick, array('ufa','astra','buryatiya')));
				break;
			case 'prof': // Профзаболевания временно закрыты
			default:
				$isAllow = false;
				break;
		}
		return $isAllow;
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return bool
	 */
	public static function isRequiredPersonRegisterMorbus_id($morbus_type)
	{
		$isRequired = false;
		/*if (self::isAllow($morbus_type)) {
			$isRequired = true;
		}*/
		if (in_array($morbus_type, array('acs','onko','crazy','narc','hepa','orphan','tub','vener','hiv','nephro','ibs','palliat','geriatrics','gibt'/*,'prof'*/))) {
			$isRequired = true;
		}
		return $isRequired;
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @return string
	 */
	public static function getNameMorbusSpecificsTable($morbus_type)
	{
		switch ($morbus_type) {
			case 'onko'://онкоспецифика
				$name = 'MorbusOnko';
				break;
			case 'crazy'://психи
				$name = 'MorbusCrazy';
			case 'narc'://психи
				$name = 'MorbusCrazy';
				break;
			case 'hepa'://гепатит
				$name = 'MorbusHepatitis';
				break;
			case 'orphan':
				$name = 'MorbusOrphan';
				break;
			case 'tub'://туберкулез
				$name = 'MorbusTub';
				break;
			case 'vener'://венеро
				$name = 'MorbusVener';
				break;
			case 'hiv': // HIV
				$name = 'MorbusHIV';
				break;
			case 'nephro': // Nephro
				$name = 'MorbusNephro';
				break;
			case 'ibs':
				$name = 'MorbusIBS';
				break;
			case 'prof': // Профзаболевания
				$name = 'MorbusProf';
				break;
			case 'acs': // ОКС
				$name = 'MorbusACS';
				break;
			case 'palliat': // Паллиативная помощь
				$name = 'MorbusPalliat';
				break;
			case 'geriatrics': // Гериатрия
				$name = 'MorbusGeriatrics';
				break;
			case 'gibt': // ГИБТ
				$name = 'MorbusGEBT';
				break;
			default:
				$name = '';
				break;
		}
		return $name;
	}

	/**
	 * @return array Список типов заболеваний в формате MorbusType_id => array('MorbusType_SysNick' => 'onko')
	 * @throws Exception
	 */
	public static function getMorbusTypeListAll()
	{
		if (isset(self::$morbusTypeList)) {
			return self::$morbusTypeList;
		}
		self::$morbusTypeList = self::getStaticMorbusCommon()->loadMorbusTypeList();
		return self::$morbusTypeList;
	}

	/**
	 * @param $Diag_id
	 * @param bool $includeCommon
	 * @return array Список типов заболеваний в формате 3 => array('MorbusType_SysNick' => 'onko', 'RegistryType' => 'onko')
	 * @throws Exception
	 */
	public static function getMorbusTypeListByDiag($Diag_id, $includeCommon = false, $includePalliat = false)
	{
		return self::getStaticMorbusCommon()->loadMorbusTypeList(array(
			'Diag_id' => $Diag_id,
			'includeCommon' => $includeCommon,
			'includePalliat' => $includePalliat,
		));
	}

	/**
	 * @param $sysnick
	 * @return int|null
	 */
	public static function getMorbusTypeIdBySysNick($sysnick)
	{
		$arr = self::getMorbusTypeListAll();
		$id = null;
		foreach ($arr as $key => $tmpArr) {
			foreach ($tmpArr as $row) {
				if (strtolower($sysnick) == strtolower($row['MorbusType_SysNick'])) {
					$id = $key;
					break;
				}
			}
		}
		return $id;
	}

	/**
	 * Обновляем связь между заболеванием и учетным документом
	 *
	 * Обновление ссылки на заболевание в учетном документе разрешено, когда
	 * 	сохраняется специфика по заболеванию (onAfterSaveMorbusSpecific)
	 * 	создается извещение на включение в регистр по заболеванию (onAfterSaveEvnNotify)
	 * @param array $data Ключи Evn_id Morbus_id session mode
	 * @return array
	 */
	public static function updateMorbusIntoEvn($data)
	{
		return self::getStaticMorbusSimple()->updateMorbusIntoEvn($data);
	}

	/**
	 * Обновляем связь между заболеванием и записью регистра
	 * @param array $data Ключи PersonRegister_id Morbus_id session
	 * @return array
	 */
	public static function updateMorbusIntoPersonRegister($data)
	{
		// ничего не делаем, т.к. заболевание создается при включении в регистр
		return array('Error_Msg'=>null);
	}

	/**
	 * Проверка возможности изменить диагноз в учетном документе
	 * @param EvnAbstract_model $evn
	 * @throws Exception
	 * @return bool
	 */
	public static function onBeforeChangeDiag(EvnAbstract_model $evn)
	{
		// только для 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection', #161204 'EvnVizitDispDop'
		if (!in_array($evn->evnClassId, array(11, 13, 14, 30, 32))) {
			return true;
		}
		// проверка наличия специфики по онкологии
		if (empty($evn->ignoreCheckMorbusOnko) && in_array($evn->evnClassId, array(11, 14, 32))) {
			$tmp = self::getStaticMorbusCommon()->getFirstResultFromQuery('
				select * from(
					(select MorbusOnkoVizitPLDop_id as "id" from v_MorbusOnkoVizitPLDop where EvnVizit_id = :Evn_id limit 1)

					union all
					(select MorbusOnkoLeave_id as "id" from v_MorbusOnkoLeave where EvnSection_id = :Evn_id limit 1)
				) t
			', array('Evn_id' => $evn->id));
			if ($tmp > 0) { 
				return array('ignoreParam' => 'ignoreCheckMorbusOnko', 'Alert_Msg' => 'При изменении диагноза данные раздела «Специфика (онкология)», связанные с текущим диагнозом, будут удалены.');
			}
		}
		// в КВС - приемное
		if (empty($evn->ignoreCheckMorbusOnko) && in_array($evn->evnClassId, array(30))) {
			$tmp = self::getStaticMorbusCommon()->getFirstResultFromQuery('
				select MorbusOnkoLeave_id as "id" from v_MorbusOnkoLeave where EvnSection_id = :Evn_id limit 1
			', array('Evn_id' => $evn->evnSectionPriemId));
			if ($tmp > 0) { 
				return array('ignoreParam' => 'ignoreCheckMorbusOnko', 'Alert_Msg' => 'При изменении диагноза данные раздела «Специфика (онкология)», связанные с текущим диагнозом, будут удалены.');
			}
		}
		$tmp = self::getStaticMorbusCommon()->getFirstResultFromQuery('
			select EvnNotifyBase_id as "EvnNotifyBase_id" 
			from v_EvnNotifyBase
			where EvnNotifyBase_pid = :Evn_id and EvnNotifyBase_niDate is null
			limit 1
		', array('Evn_id' => $evn->id));
		if ($tmp > 0) {
			//нужно откатить транзакцию
			throw new Exception('Смена диагноза недоступна. Имеется извещение о включении в регистр в статусе «Отправлено» или «Включено в регистр».');
		}
		return true;
	}

	/**
	 * Сохранение специфики заболевания после успешного сохранения объекта
	 *
	 * Создавать заболевание не нужно (даже если в учетном документе указан диагноз, входящих в какую-то группу заболеваний)
	 * кроме случаев:
	 * - Если создаются связанные объекты (Извещение, Запись регистра)
	 * - Если указан хотя бы один атрибут в специфике
	 * @param EvnAbstract_model $evn
	 * @throws Exception
	 * @return array
	 */
	public static function onAfterSaveEvn(EvnAbstract_model $evn)
	{
		// только для 'EvnVizitPL', 'EvnVizitPLStom', 'EvnSection', 'EvnVizitDispDop'
		if (!in_array($evn->evnClassId, array(11, 14, 13, 32))) {
			return array();
		}
		// тут нужен commit, иначе сохраняется неправильный диагноз в специфике заболевания
		/*if ( !$evn->commitTransaction() ) {
			$evn->isAllowTransaction = false;
			throw new Exception('Не удалось зафиксировать транзакцию', 500);
		}
		if ( !$evn->beginTransaction() ) {
			$evn->isAllowTransaction = false;
			throw new Exception('Ошибка при попытке запустить транзакцию 2', 500);
		}*/
		// спеицифику по паллативной помощи показывать только если пациент включен в регистр
		$includePalliat = self::getStaticMorbusCommon()->checkPersonPalliat(array(
			'Person_id' => $evn->Person_id
		));
		//определение типа диагноза
		if (empty($evn->Diag_id)) {
			$MorbusTypes = swMorbus::getMorbusTypeListByDiag(0, true, $includePalliat);
		} else {
			$MorbusTypes = swMorbus::getMorbusTypeListByDiag($evn->Diag_id, true, $includePalliat); // диагноз может подходить сразу под несколько специфик. (Пример: C02.1 - Онко и профзаболевания)
		}
		$response = array();
		$response['listMorbus'] = array();
		$isAllowClearEvnMorbus = true;
		
		// получение морбусов по сопутствующим для онкологии
		if (in_array($evn->evnClassId, array(11, 14, 32))) {
			$diag_list = array(); // список идешников диагнозов
			$diag_sop_idlist = array(); // список идешников сопутствующих диагнозов
			$diag_sop = self::getStaticMorbusCommon()->queryResult("
				select 
					ed.Diag_id as \"Diag_id\", 
					ed.EvnDiag_id as \"EvnDiag_id\"
				from v_EvnDiag ed
				inner join v_Diag d on ed.Diag_id = d.Diag_id
				where 
					ed.EvnDiag_pid = ? and 
					ed.EvnClass_id in (18,33) and 
					ed.DiagSetClass_id != 1 and 
					(left(d.Diag_Code, 1) = 'C' or left(d.Diag_Code, 2) = 'D0')
			", array($evn->id));
			$diag_sop_list = array();
			foreach($diag_sop  as $ds) {
				$diag_sop_list[] = $ds['Diag_id'];
				$diag_sop_idlist[$ds['Diag_id']] = $ds['EvnDiag_id'];
			}
			$diag_list = array_merge($diag_list, $diag_sop_list);
			// получаем данные, которые определяют видимость разделов специфики, кнопок создания извещения
			if (count($diag_list)) {
				$arr = self::getStaticMorbusCommon()->loadMorbusTypeList(array(
					'Person_id' => $evn->Person_id,
					'diag_list' => $diag_list,
					'includeCommon' => true,
				));
				$diag_data = array();
				$evn_list = array(); // список идешников учетных документов, которым надо проверять EvnInfectNotify
				foreach ($diag_list as $diag) {
					$diag_data[$diag] = array();
					foreach ($arr as $key => $tmpArr) {
						foreach ($tmpArr as $row) {
							if ($diag == $row['Diag_id']) {
								$diag_data[$diag][$key] = $row;
							}
						}
					}
				}
				foreach($diag_data as $key => $diag_data_item) {
					foreach($diag_data_item as $key => $row) {
						$morbusTypeSysNick = $row['MorbusType_SysNick'];
						if ($morbusTypeSysNick == 'onko') {
							$response['listMorbus'][$morbusTypeSysNick][] = array(
								'MorbusType_id' => $key,
								'Morbus_id' => $row['Morbus_id'],
								'Diag_Code' => $row['Diag_Code'],
								'Diag_id' => $row['Diag_id'],
								'diagIsMain' => false,
								'EvnDiagPLSop_id' => $diag_sop_idlist[$row['Diag_id']],
								'disableAddEvnNotify' => $row['disableAddEvnNotify'],
								'morbusTypeSysNick' => $row['MorbusType_SysNick'],
							);
						}
					}
				}
			}
		}
		
		foreach($MorbusTypes as $MorbusType_id => $arr) {
			foreach ( $arr as $row ) {
				$MorbusType_SysNick = $row['MorbusType_SysNick'];
				if (self::isAllow($MorbusType_SysNick) && self::isMorbus($MorbusType_SysNick)) {
					if ($MorbusType_SysNick != 'onko') {
						$response['listMorbus'][$MorbusType_SysNick] = array(
							'MorbusType_id' => $MorbusType_id,
						);
					}
					$tmp = self::checkByEvn($MorbusType_SysNick, array(
						'Evn_pid' => $evn->id,
						'Diag_id' => $evn->Diag_id, //если диагноз уточнился, заменяю диагноз заболевания диагнозом этого движения/посещения
						'Person_id' => $evn->Person_id,
						'Morbus_setDT' => $evn->setDate,
						'pmUser_id' => $evn->promedUserId,
						'session' => $evn->sessionParams,
						'Lpu_id' => $evn->Lpu_id, // for orphan
					), 'onAfterSaveEvn');
					if ($MorbusType_SysNick == 'onko') {
						$response['listMorbus'][$MorbusType_SysNick][] = array_merge(['MorbusType_id' => $MorbusType_id, 'diagIsMain' => true], $tmp);
					} else {
						$response['listMorbus'][$MorbusType_SysNick] = array_merge($response['listMorbus'][$MorbusType_SysNick],$tmp);
					}
					if (isset($tmp['Morbus_id'])) {
						$morbus_id = $tmp['Morbus_id'];
						$tmp = self::updateMorbusIntoEvn(array(
							'Evn_id' => $evn->id,
							'Morbus_id' => $tmp['Morbus_id'],
							'session' => $evn->sessionParams,
							'mode' => 'onAfterSaveEvn',
						));
						if (isset($tmp['Error_Msg'])) {
							//нужно откатить транзакцию
							throw new Exception($tmp['Error_Msg']);
						}
						$isAllowClearEvnMorbus = false;
						if($MorbusType_SysNick == 'nephro' && in_array(self::getRegionNick(), array('perm', 'ufa'))){
							self::checkAndSaveEvnNotifyNephro($evn,$morbus_id);
						}
					}
					if($MorbusType_SysNick == 'onko') {
						$subsect_list = array('OnkoConsult', 'MorbusOnkoLink', 'MorbusOnkoSpecTreat', 'MorbusOnkoDrug', 'MorbusOnkoRefusal');
						self::getCiInstance()->load->model('MorbusOnkoSpecTreat_model', 'staticMorbusOnkoSpecTreat');
						self::$staticMorbusOnkoSpecTreat = self::getCiInstance()->staticMorbusOnkoSpecTreat;
						$morbusSpecifics = self::getStaticMorbusSpecificsModel('onko');
						// если в специфике диагноз из другой группы - удаляем её, чтобы создалась новая
						$tmp = self::getStaticMorbusCommon()->getFirstRowFromQuery("
							select 
								MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\", 
								Diag_id as \"Diag_id\"
							from v_MorbusOnkoVizitPLDop
							where EvnVizit_id = :Evn_id and Diag_id != :Diag_id
							limit 1
						", array(
							'Evn_id' => $evn->id,
							'Diag_id' => $evn->Diag_id
						));
						if(!empty($tmp)) {
							foreach($subsect_list as $subsect) {
								$mol_list = self::getStaticMorbusCommon()->queryList("select {$subsect}_id AS \"{$subsect}_id\" from {$subsect} where MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id",
									array('MorbusOnkoVizitPLDop_id' => $tmp['MorbusOnkoVizitPLDop_id'])
								);
								foreach($mol_list as $ml) {
									self::getStaticMorbusCommon()->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
								}
							}
							// удаляем все связанные услуги
							self::removeEvnUslugaOnko(array(
								'Evn_id' => $evn->id,
								'pmUser_id' => $evn->promedUserId,
							));
							// И удаляем саму специфику
							try {
								$delParams = array('MorbusOnkoVizitPLDop_id' => $tmp['MorbusOnkoVizitPLDop_id']);
								$res = self::getStaticMorbusCommon()->execCommonSP('dbo.p_MorbusOnkoVizitPLDop_del', $delParams); 
							} catch (Exception $e) {
								self::getStaticMorbusCommon()->rollbackTransaction();
								throw new Exception('При удалении специфики произошла ошибка');
							}
							// в MorbusOnko пишем последнюю известную специфику
							self::revertMorbusOnko($evn, $tmp['Diag_id']);
							// сносим неактуальные морбусы
							$morbusSpecifics->clearMorbusOnkoSpecifics(array(
								'Person_id' => $evn->Person_id,
								'pmUser_id' => $evn->promedUserId,
								'session' => $evn->sessionParams,
							));
						}
						$tmp = self::getStaticMorbusCommon()->getFirstRowFromQuery("
							select MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\", Diag_id as \"Diag_id\"
							from v_MorbusOnkoLeave
							where EvnSection_id = :Evn_id and Diag_id != :Diag_id
							limit 1
						", array(
							'Evn_id' => $evn->id,
							'Diag_id' => $evn->Diag_id
						));
						if(!empty($tmp)) {
							foreach($subsect_list as $subsect) {
								$mol_list = self::getStaticMorbusCommon()->queryList("select {$subsect}_id AS \"{$subsect}_id\" from {$subsect} where MorbusOnkoLeave_id = :MorbusOnkoLeave_id",
									array('MorbusOnkoLeave_id' => $tmp['MorbusOnkoLeave_id'])
								);
								foreach($mol_list as $ml) {
									self::getStaticMorbusCommon()->execCommonSP("dbo.p_{$subsect}_del", array("{$subsect}_id" => $ml));
								}
							}
							// удаляем все связанные услуги
							self::removeEvnUslugaOnko(array(
								'Evn_id' => $evn->id,
								'pmUser_id' => $evn->promedUserId,
							));

							if (self::getRegionNick() == 'perm') {
								self::getStaticMorbusCommon()->db->query("update dbo.RegistryDataEvnPS set MorbusOnkoLeave_id = null where MorbusOnkoLeave_id = :MorbusOnkoLeave_id",
									array('MorbusOnkoLeave_id' => $tmp['MorbusOnkoLeave_id'])
								);
							}
							// И удаляем саму специфику
							try {
								$delParams = array('MorbusOnkoLeave_id' => $tmp['MorbusOnkoLeave_id']);
								$res = self::getStaticMorbusCommon()->execCommonSP('dbo.p_MorbusOnkoLeave_del', $delParams);
							} catch (Exception $e) {
								self::getStaticMorbusCommon()->rollbackTransaction();
								throw new Exception('При удалении специфики произошла ошибка');
							}
							// в MorbusOnko пишем последнюю известную специфику
							self::revertMorbusOnko($evn, $tmp['Diag_id']);
							// сносим неактуальные морбусы
							$morbusSpecifics->clearMorbusOnkoSpecifics(array(
								'Person_id' => $evn->Person_id,
								'pmUser_id' => $evn->promedUserId,
								'session' => $evn->sessionParams,
							));
						}
					}
				}
			}
		}
		if ($isAllowClearEvnMorbus) {
			$tmp = self::updateMorbusIntoEvn(array(
				'Evn_id' => $evn->id,
				'Morbus_id' => null,
				'session' => $evn->sessionParams,
				'mode' => 'onAfterSaveEvn',
			));
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		return $response;
	}
	
	/**
	 * Удаление услуг в онкоспецифике
	 */
	public static function removeEvnUslugaOnko($data) {
		$usluga_list = self::getStaticMorbusCommon()->queryResult("
			select EvnUsluga_id as \"EvnUsluga_id\", EvnClass_SysNick as \"EvnClass_SysNick\"
			from v_EvnUsluga
			where EvnUsluga_pid = :Evn_id and
				" . (!empty($data['Morbus_id']) ? " Morbus_id = :Morbus_id and " : "") . "
				EvnClass_SysNick in ('EvnUslugaOnkoChem','EvnUslugaOnkoBeam','EvnUslugaOnkoGormun','EvnUslugaOnkoSurg','EvnUslugaOnkoNonSpec')
		", array(
			'Evn_id' => $data['Evn_id'],
			'Morbus_id' => !empty($data['Morbus_id']) ? $data['Morbus_id'] : null
		));
		
		foreach($usluga_list as $row) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_{$row['EvnClass_SysNick']}_del (
					{$row['EvnClass_SysNick']}_id := :id,
					pmUser_id := :pmUser_id
				)	
			";
			self::getStaticMorbusCommon()->db->query($query, array(
				'id' => $row['EvnUsluga_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}		
	}
	
	/**
	 * Откат изменений в MorbusOnko до последней актуальной специфики
	 */
	public static function revertMorbusOnko(EvnAbstract_model $evn, $diag_id) 
	{
		$morbusSpecifics = self::getStaticMorbusSpecificsModel('onko');
		
		return $morbusSpecifics->revertMorbusOnko($evn, $diag_id);
	}

	/**
	 * Удаление заболеваний перед удалением записи регистра
	 *
	 * Удалять заболевание нельзя в случаях:
	 * - Если человек был включен в регистр по извещению
	 * - Если есть связь заболевания с учетными документами или извещениями
	 * @param PersonRegister_model $model
	 * @throws Exception
	 * @return bool
	 */
	public static function onBeforeDeletePersonRegister(PersonRegister_model $model)
	{
		if (empty($model->Morbus_id) || empty($model->Diag_id)) {
			// нет специфик
			return true;
		}
		// Так-то нужен признак в таблице, что включение в регистр было произведено автоматически,
		// но его нет, поэтому остается сделать так
		$isAutoIncluded = $model->isAllowAutoInclude($model->MorbusType_SysNick, $model->Diag_id);
		if ($isAutoIncluded && !empty($model->EvnNotifyBase_id)) {
			// должно удаляться извещение
			switch ($model->MorbusType_SysNick) {
				case 'crazy': $evnclass_sysnick = 'EvnNotifyCrazy'; break;
				case 'hepa': $evnclass_sysnick = 'EvnNotifyHepatitis'; break;
				case 'orphan': $evnclass_sysnick = 'EvnNotifyOrphan'; break;
				case 'tub': $evnclass_sysnick = 'EvnNotifyTub'; break;
				case 'vener': $evnclass_sysnick = 'EvnNotifyVener'; break;
				case 'hiv': $evnclass_sysnick = 'EvnNotifyHIV'; break;
				default: $evnclass_sysnick = null; break;
			}
			if (empty($evnclass_sysnick)) {
				throw new Exception('Не удалось определить класс извещения', 500);
			}
			$tmp = $model->execCommonSP("p_{$evnclass_sysnick}_del", array(
				"{$evnclass_sysnick}_id" => $model->EvnNotifyBase_id,
				'pmUser_id' => $model->promedUserId,
			), 'array_assoc');
			if (false === $tmp) {
				throw new Exception('Не удалось выполнить запрос удаления извещения', 500);
			}
			if (false == is_array($tmp) || false == array_key_exists('Error_Msg', $tmp)) {
				throw new Exception('Неправильный формат ответа при удалении извещения', 500);
			}
			if (!empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
		}
		$arr = self::getStaticMorbusCommon()->loadMorbusList(array(
			'mode' => 'onBeforeDeletePersonRegister',
			'Person_id' => $model->Person_id,
			'MorbusType_id' => $model->MorbusType_id,
			'onlyOpen' => empty($model->PersonRegisterOutCause_id),
			'diag_list' => array($model->Diag_id),
			/*'morbus_list' => array($model->Morbus_id),// для проверки наличия других заболеваний той же группы диагнозов
			'MorbusType_SysNick' => $model->MorbusType_SysNick,
			*/
		));
		$params = array();
		$paramsOn = array();
		foreach ($arr as $row) {
			$morbus_type = $row['MorbusType_SysNick'];
			if ((!empty($model->EvnNotifyBase_id) && false == $isAutoIncluded)
				|| 1 == $row['hasOtherEvn']
				|| 1 == $row['hasEvnNotify']
			) {
				// нельзя удалять, но возможно надо удалить данные специфик заболевания заведенные из регистра
				if (empty($paramsOn[$morbus_type])) {
					$paramsOn[$morbus_type] = array(
						'MorbusType_SysNick' => $row['MorbusType_SysNick'],
						'MorbusBase_id' => $row['MorbusBase_id'],
						'needOpenMorbusIdList' => array(),
						'MorbusIdList' => array(), //для общего заболевания по ошибке могло быть создано несколько простых заболеваний из одной группы диагнозов
					);
				}
				$paramsOn[$morbus_type]['MorbusIdList'][] = $row['Morbus_id'];
				if (false == empty($model->PersonRegisterOutCause_id) && !empty($row['Morbus_disDT'])) {
					// если человек был исключен из регистра и проставлена дата закрытия заболевания, то данное заболевание открывать
					$paramsOn[$morbus_type]['needOpenMorbusIdList'][] = $row['Morbus_id'];
				}
				continue;
			}
			if (empty($params[$morbus_type])) {
				$params[$morbus_type] = array(
					'MorbusType_SysNick' => $row['MorbusType_SysNick'],
					'MorbusBase_id' => $row['MorbusBase_id'],
					'hasOtherMorbus' => $row['hasOtherMorbus'], // есть ли другие простые заболевания в рамках общего заболевания, если нет, то общее заболевание тоже надо удалить
					'MorbusIdList' => array(), //для общего заболевания по ошибке могло быть создано несколько простых заболеваний из одной группы диагнозов
					'session' => $model->sessionParams,
				);
			}
			$params[$morbus_type]['MorbusIdList'][] = $row['Morbus_id'];
		}
		/*$test = array(count($params), $paramsOn, empty($model->PersonRegisterOutCause_id));
		throw new Exception(var_export($test, true), 600);*/
		// удаляем простые заболевания со спецификами
		foreach ($params as $morbus_type => $row) {
			$row['MorbusIdList'] = array_unique($row['MorbusIdList']);
			self::getStaticMorbusSimple()->doDeleteByList($row);
		}
		foreach ($paramsOn as $morbus_type => $row) {
			if (count($row['needOpenMorbusIdList']) > 0) {
				$row['needOpenMorbusIdList'] = array_unique($row['needOpenMorbusIdList']);
				self::getStaticMorbusSimple()->doOpenByList(array(
					'MorbusIdList' => $row['needOpenMorbusIdList'],
					'session' => $model->sessionParams,
				));
			}
			unset($row['needOpenMorbusIdList']);
			// Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
			$row['MorbusIdList'] = array_unique($row['MorbusIdList']);
			/**
			 * @var swModel $morbusSpecifics
			 */
			$morbusSpecifics = self::getStaticMorbusSpecificsModel($morbus_type);
			if (!empty($morbusSpecifics) && method_exists($morbusSpecifics, 'onBeforeDeletePersonRegister')) {
				$morbusSpecifics->isAllowTransaction = false;
				$morbusSpecifics->onBeforeDeletePersonRegister($model, $row);
			}
		}
		return true;
	}

	/**
	 * Логика перед удалением объекта
	 *
	 * Проверка возможности удалить: нельзя, если из случая создано извещение на включение в регистр.
	 * Удаление заболеваний, созданных из случая лечения
	 * Удалять заболевание нельзя в случаях:
	 * - Если есть связь заболевания с другими учетными документами
	 * - Если есть связь заболевания с регистром
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
		if (empty($diag_list)) {
			// нет диагнозов - нет заболеваний
			return true;
		}
		$arr = self::getStaticMorbusCommon()->loadMorbusList(array(
			'mode' => 'onBeforeDeleteEvn',
			'Person_id' => $evn->Person_id,
			'onlyOpen' => true,
			'diag_list' => $diag_list,
			'evn_list' => $evn_list,
		));
		$params = array();
		$paramsOn = array();
		foreach ($arr as $row) {
			$morbus_type = $row['MorbusType_SysNick'];
			if (empty($row['Morbus_id'])) {
				continue;
			}
			if (1 == $row['hasEvnNotify']) {
				//нужно откатить транзакцию
				throw new Exception('Если из учетного документа было создано извещение на включение в регистр, то учетный документ нельзя удалить');
			}
			if (1 == $row['hasPersonRegister'] || 1 == $row['hasOtherEvn']) {
				// нельзя удалять, но возможно надо удалить данные специфик заболевания заведенные в учетном документе
				if (empty($paramsOn[$morbus_type])) {
					$paramsOn[$morbus_type] = array(
						'MorbusBase_id' => $row['MorbusBase_id'],
						'MorbusIdList' => array(), //для общего заболевания по ошибке могло быть создано несколько простых заболеваний из одной группы диагнозов
					);
				}
				$paramsOn[$morbus_type]['MorbusIdList'][] = $row['Morbus_id'];
				continue;
			}
			if (empty($params[$morbus_type])) {
				$params[$morbus_type] = array(
					'MorbusType_SysNick' => $row['MorbusType_SysNick'],
					'MorbusBase_id' => $row['MorbusBase_id'],
					'hasOtherMorbus' => $row['hasOtherMorbus'], // есть ли другие простые заболевания в рамках общего заболевания, если нет, то общее заболевание тоже надо удалить
					'MorbusIdList' => array(), //для общего заболевания по ошибке могло быть создано несколько простых заболеваний из одной группы диагнозов
					'session' => $evn->sessionParams,
				);
			}
			$params[$morbus_type]['MorbusIdList'][] = $row['Morbus_id'];
		}
		// удаляем простые заболевания со спецификами
		foreach ($params as $morbus_type => $row) {
			$row['MorbusIdList'] = array_unique($row['MorbusIdList']);
			self::getStaticMorbusSimple()->doDeleteByList($row);
		}
		// Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
		foreach ($paramsOn as $morbus_type => $row) {
			$row['MorbusIdList'] = array_unique($row['MorbusIdList']);
			/**
			 * @var swModel $morbusSpecifics
			 */
			$morbusSpecifics = self::getStaticMorbusSpecificsModel($morbus_type);
			if (!empty($morbusSpecifics) && method_exists($morbusSpecifics, 'onBeforeDeleteEvn')) {
				$morbusSpecifics->isAllowTransaction = false;
				$morbusSpecifics->onBeforeDeleteEvn($evn, $row);
			}
		}
		return true;
	}

	/**
	 * @param array $diag_list
	 * @param bool $includeCommon
	 * @return array Список типов заболеваний в формате Diag_id => array('MorbusType_id' => array('MorbusType_SysNick' => 'onko'))
	 * @throws Exception
	 */
	public static function getMorbusTypeListByDiagList($diag_list, $includeCommon = false)
	{
		$arr = self::getStaticMorbusCommon()->loadMorbusTypeList(array(
			'diag_list' => $diag_list,
			'includeCommon' => $includeCommon
		));
		$response = array();
		foreach ($diag_list as $diag) {
			$response[$diag] = array();
			foreach ($arr as $key => $arrTmp) {
				foreach ($arrTmp as $row) {
					if ($diag == $row['Diag_id']) {
						unset($row['Diag_id']);
						$response[$diag][$key] = $row;
					}
					if ($includeCommon && 'common' == $row['MorbusType_SysNick']) {
						unset($row['Diag_id']);
						$response[$diag][$key] = $row;
					}
				}
			}
		}
		return $response;
	}

	/**
	 * Обработка данных для работы со спецификой заболевания в панели просмотра учетного документа
	 *
	 * Добавляет в ответ ключи, которые определяют видимость разделов специфики, кнопок создания извещения
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
		$diag_sop_idlist = array(); // список идешников сопутствующих диагнозов
		foreach ($evnDataList as $i => $row) {
			if (self::getRegionNick() == 'ufa' && !empty($row['EvnSection_IsPriem']) && $row['EvnSection_IsPriem'] == 2) {
				continue;
			}
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
			if (in_array($sectionCode, array('EvnVizitPL','EvnSection'))) {
				$diag_sop = self::getStaticMorbusCommon()->queryResult("
					select ed.Diag_id as \"Diag_id\", ed.EvnDiag_id as \"EvnDiag_id\" 
					from v_EvnDiag ed
					inner join v_Diag d on ed.Diag_id = d.Diag_id
					where 
						ed.EvnDiag_pid = ? and 
						ed.EvnClass_id in (18,33) and 
						ed.DiagSetClass_id != 1 and 
						(left(d.Diag_Code, 1) = 'C' or left(d.Diag_Code, 2) = 'D0')
				", array($row[$idKey]));
				$diag_sop_list = array();
				foreach($diag_sop  as $ds) {
					$diag_sop_list[] = $ds['Diag_id'];
					$diag_sop_idlist[$ds['Diag_id']] = $ds['EvnDiag_id'];
				}
				$diag_list = array_merge($diag_list, $diag_sop_list);
			}
		}
		if (empty($diag_list)) {
			$diag_list[] = '0';
		}
		// получаем данные, которые определяют видимость разделов специфики, кнопок создания извещения
		$arr = self::getStaticMorbusCommon()->loadMorbusTypeList(array(
			'Person_id' => $evnDataList[0]['Person_id'],
			'diag_list' => $diag_list,
			'includeCommon' => true,
		));
		$diag_data = array();
		$evn_list = array(); // список идешников учетных документов, которым надо проверять EvnInfectNotify
		foreach ($diag_list as $diag) {
			$diag_data[$diag] = array();
			foreach ($arr as $key => $tmpArr) {
				foreach ($tmpArr as $row) {
					if ($diag == $row['Diag_id']) {
						$diag_data[$diag][$key] = $row;
						if (preg_match('/^(A0[0-9]|A2[0-8]|A[3-4]|A7[5-9]|A[8-9]|B0[0-9]|B1[5-9]|B2|B3[0-4]|B[5-7]|B8[0-3]|B9[0-6]|B97.[0-8]|B99)/i', $row['Diag_Code'])) {
							$evn_list = array_merge($evn_list, $diag_evn_list[$diag]);
							$row['MorbusType_SysNick'] = 'infect058';
							$row['Morbus_id'] = null;
							$row['disableAddEvnNotify'] = 0;
							$diag_data[$diag]['-1'] = $row;
						}
					}
				}
			}
		}
		if (!empty($evn_list)) {
			$arr = self::getStaticMorbusCommon()->loadEvnInfectNotifyList($evn_list);
			foreach ($diag_data as $diag => $rows) {
				if (isset($rows['-1'])) {
					$disableAddEvnNotify = 0;
					foreach ($diag_evn_list[$diag] as $id) {
						foreach ($arr as $row) {
							if ($id == $row['EvnInfectNotify_pid']) {
								$disableAddEvnNotify = 1;
							}
						}
					}
					$diag_data[$diag]['-1']['disableAddEvnNotify'] = $disableAddEvnNotify;
				}
			}
		}
		$Person_id = $evnDataList[0]['Person_id'];
		self::getCiInstance()->load->model('EvnOnkoNotify_model', 'OnkoNotifyModel');
		$isDisabledAddEvnOnkoNotify = self::getCiInstance()->OnkoNotifyModel->checkNotifyExists($Person_id, 'onko');

		/*
		 * в панели просмотра движения в профильном отделении могут быть видимы: onko, hepa, crazy, tub, vener, prof
		 * в панели просмотра посещения полки могут быть видимы: onko, hepa, crazy, tub, vener, prof, pregnancy, nephro
		 * в панели просмотра посещения стоматки могут быть видимы: onko, hepa, crazy, tub, vener, prof
		 */
		foreach($evnDataList as &$evn) {
			$isDisabledAddEvnNotify = (empty($evn['accessType']) || 'edit'!=$evn['accessType']);
			$evn['listMorbus'] = array();
			$evn_diag = 0;
			if (!empty($evn['Diag_id'])) {
				$evn_diag = $evn['Diag_id'];
			}
			// особый костыль для онкологии, у которой может быть несколько специфик (с учётом сопутствующих)
			foreach($diag_data as $key => $diag_data_item) {
				foreach($diag_data_item as $key => $row) {
					$morbusTypeSysNick = $row['MorbusType_SysNick'];
					if ($morbusTypeSysNick == 'onko') {
						$evn['listMorbus'][$morbusTypeSysNick][] = array(
							'MorbusType_id' => $key,
							'Morbus_id' => $row['Morbus_id'],
							'Diag_Code' => $row['Diag_Code'],
							'Diag_id' => $row['Diag_id'],
							'diagIsMain' => intval($evn['Diag_id'] == $row['Diag_id']),
							'EvnDiagPLSop_id' => isset($diag_sop_idlist[$row['Diag_id']]) ? $diag_sop_idlist[$row['Diag_id']] : null,
							'disableAddEvnNotify' => $row['disableAddEvnNotify'],
							'morbusTypeSysNick' => $row['MorbusType_SysNick'],
						);
					}
				}
			}
			if (isset($diag_data[$evn_diag])) {
				foreach ($diag_data[$evn_diag] as $key => $row) {
					$morbusTypeSysNick = $row['MorbusType_SysNick'];
					if ($morbusTypeSysNick == 'narc') {
						$morbusTypeSysNick = 'crazy';
					}
					if (false == self::isAllow($morbusTypeSysNick)) {
						continue;
					}
					if ($morbusTypeSysNick != 'onko') {
						$evn['listMorbus'][$morbusTypeSysNick] = array(
							'MorbusType_id' => $key,
							'Morbus_id' => $row['Morbus_id'],
							'Diag_Code' => $row['Diag_Code'],
							'Diag_id' => $row['Diag_id'],
							'disableAddEvnNotify' => $row['disableAddEvnNotify'],
							'morbusTypeSysNick' => $row['MorbusType_SysNick'],
						);
					}
				}
			}
			$evn['isDisabledAddEvnInfectNotify'] = 1;
			if (isset($evn['listMorbus']['infect058'])) {
				$evn['isDisabledAddEvnInfectNotify'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['infect058']['disableAddEvnNotify'])) ? 1 : 0;
			}
			if (isset($evn['listMorbus']['onko'])) {
				$evn['isVisibleOnko'] = 0;
				if (('EvnSection' != $sectionCode || 2 != $evn['EvnSection_IsPriem'])) {
					$evn['isVisibleOnko'] = 1;
					//$evn['isDisabledAddEvnOnkoNotify'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['onko']['disableAddEvnNotify']));
					
					$evn['isDisabledAddEvnOnkoNotify'] = ($isDisabledAddEvnNotify || $isDisabledAddEvnOnkoNotify);
				}
				foreach ($evn['listMorbus']['onko'] as &$onko) {
					$onko['MorbusOnkoVizitPLDop_id'] = self::getStaticMorbusCommon()->getFirstResultFromQuery('
						select MorbusOnkoVizitPLDop_id  as "MorbusOnkoVizitPLDop_id" from MorbusOnkoVizitPLDop where EvnVizit_id = ? and Diag_id = ?
					', array($evn[$idKey], $onko['Diag_id']));
					$onko['MorbusOnkoLeave_id'] = self::getStaticMorbusCommon()->getFirstResultFromQuery('
						select MorbusOnkoLeave_id  as "MorbusOnkoLeave_id" from MorbusOnkoLeave where EvnSection_id = ? and Diag_id = ?
					', array($evn[$idKey], $onko['Diag_id']));
				}
			}
			if (isset($evn['listMorbus']['hepa'])) {
				$evn['isVisibleHepa'] = 0;
				if ('EvnSection' != $sectionCode || 2 != $evn['EvnSection_IsPriem']) {
					$evn['isVisibleHepa'] = 1;
					$evn['isDisabledAddEvnNotifyHepatitis'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['hepa']['disableAddEvnNotify']));
				}
			}
			if (isset($evn['listMorbus']['tub'])) {
				$evn['isVisibleTub'] = 0;
				if ('EvnSection' != $sectionCode || 2 != $evn['EvnSection_IsPriem']) {
					$evn['isVisibleTub'] = 1;
					$evn['isDisabledAddEvnNotifyTub'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['tub']['disableAddEvnNotify']));
				}
			}
			if (isset($evn['listMorbus']['vener'])) {
				$evn['isVisibleVener'] = 1;
				$evn['isDisabledAddEvnNotifyVener'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['vener']['disableAddEvnNotify'])) ? 1 : 0;
				// Работа со Спецификой по венерологии (аналог формы № 065/у) реализована для группы диагнозов А50-А64
				if (empty($evn['Diag_pid']) || false == in_array($evn['Diag_pid'],array(324,325,326,327,328,329,330,331,332,333,334,335,336))) {
					$evn['isVisibleVener'] = 0;
				}
				// Работа со Спецификой и с Извещением не доступна в приемном отделении
				if ('EvnSection' == $sectionCode && 2 == $evn['EvnSection_IsPriem']) {
					$evn['isVisibleVener'] = 0;
					$evn['isDisabledAddEvnNotifyVener'] = 1;
				}
				// Работа с Извещением по венерологии реализована для группы диагнозов  А50-А64, В35, В86
				if (empty($evn['Diag_pid']) || false == in_array($evn['Diag_pid'],array(324,325,326,327,328,329,330,331,332,333,334,335,336,394,441))) {
					$evn['isDisabledAddEvnNotifyVener'] = 1;
				}
			}
			if (isset($evn['listMorbus']['crazy'])) {
				$evn['isVisibleCrazy'] = 0;
				$evn['isCrazy'] = 0;
				$evn['isNarc'] = 0;
				if ($evn['listMorbus']['crazy']['morbusTypeSysNick'] == 'narc') {
					$evn['isNarc'] = 1;
				} else {
					$evn['isCrazy'] = 1;
				}
				if ('EvnSection' != $sectionCode || 2 != $evn['EvnSection_IsPriem']) {
					$evn['isVisibleCrazy'] = 1;
					$evn['isDisabledAddEvnNotifyCrazy'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['crazy']['disableAddEvnNotify']));
				}
			}
			if (isset($evn['listMorbus']['pregnancy']) && 'EvnSection' != $sectionCode) {
				$evn['isVisiblePregn'] = 1;
			}
			if (isset($evn['listMorbus']['nephro']) && isset($evn['VizitType_id']) && in_array($evn['VizitType_id'], array(2, 213)) && 'EvnVizitPL' == $sectionCode) {
				$evn['isVisibleNephro'] = 1;
				$evn['isDisabledAddEvnNotifyNephro'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['nephro']['disableAddEvnNotify']));
			}
			if (false && isset($evn['listMorbus']['prof'])) {
				$evn['isVisibleProf'] = 1;
				$evn['isDisabledAddEvnNotifyProf'] = ($isDisabledAddEvnNotify || false == empty($evn['listMorbus']['prof']['disableAddEvnNotify']));
			}
			if (isset($evn['listMorbus']['palliat'])) {
				$evn['isVisiblePalliat'] = 1;
			}
			if (isset($evn['listMorbus']['geriatrics'])) {
				$evn['isVisibleGeriatrics'] = 1;
			}
		}
		return $evnDataList;
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @param int $evn_pid
	 * @param array $session
	 * @return array
	 * @throws Exception Отменяет сохранение извещения
	 */
	public static function onBeforeSaveEvnNotify($morbus_type, $evn_pid, $session, $EvnDiagPLSop_id = null)
	{
		return self::checkByEvn($morbus_type, array(
			'Evn_pid' => $evn_pid,
			'session' => $session,
			'EvnDiagPLSop_id' => $EvnDiagPLSop_id,
		), 'onBeforeSaveEvnNotify');
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $params
	 * @return array
	 * @throws Exception Отменяет сохранение извещения
	 */
	public static function onAfterSaveEvnNotify($morbus_type, $params)
	{
		if (empty($params['Person_id'])
			|| empty($params['Morbus_id'])
			|| empty($params['MorbusType_id'])
			|| empty($params['Morbus_Diag_id'])
			|| empty($params['EvnNotifyBase_setDate'])
			|| empty($params['EvnNotifyBase_id'])
			|| empty($params['MedPersonal_id'])
			|| empty($params['Lpu_id'])
			|| empty($params['session'])
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		if (!empty($params['EvnNotifyBase_pid'])) {
			$tmp = self::updateMorbusIntoEvn(array(
				'Evn_id' => $params['EvnNotifyBase_pid'],
				'Morbus_id' => $params['Morbus_id'],
				'session' => $params['session'],
				'mode' => 'onAfterSaveEvnNotify',
			));
			if (isset($tmp['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($tmp['Error_Msg']);
			}
		}
		return self::getStaticPersonRegister()->autoInclude(array(
			'PersonRegister_id' => null,
			'Person_id' => $params['Person_id'],
			'Morbus_id' => $params['Morbus_id'],
			'MorbusType_SysNick' => $morbus_type,
			'MorbusType_id' => $params['MorbusType_id'],
			'Diag_id' => $params['Morbus_Diag_id'],
			'PersonRegister_setDate' => $params['EvnNotifyBase_setDate'],
			'Lpu_iid' => $params['Lpu_id'],
			'MedPersonal_iid' => $params['MedPersonal_id'],
			'EvnNotifyBase_id' => $params['EvnNotifyBase_id'],
			'session' => $params['session']
		));
	}

	/**
	 * Проверка существования заболевания
	 *
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $data Обязательные параметры: Evn_pid, session
	 * @param string $mode
	 * onAfterSaveEvn Вызывается после сохранения диагноза движения/посещения из формы редактирования или панели просмотра ЭМК
	 * onBeforeViewData Вызывается перед загрузкой данных специфики в панель просмотра ЭМК или формы записи регистра
	 * onBeforeSaveEvnNotify Вызывается перед сохранением извещения
	 * @return array
	 * @throws Exception
	 */
	public static function checkByEvn($morbus_type, $data, $mode)
	{
		if ((empty($data['Evn_pid']) && false == in_array($mode, array('onBeforeSaveEvnNotifyFromDispCard', 'onBeforeSaveEvnNotifyFromJrn')))
			|| empty($data['session'])
			|| false == in_array($mode, array('onAfterSaveEvn', 'onBeforeViewData', 'onBeforeSaveEvnNotify', 'onBeforeSaveEvnNotifyFromDispCard', 'onBeforeSaveEvnNotifyFromJrn'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$response = array(
			'MorbusType_id' => self::getMorbusTypeIdBySysNick($morbus_type),
			'Evn_pid' => $data['Evn_pid'],
			'Evn_aid' => null,
			'Person_id' => null,
			'Diag_id' => null,
			'Morbus_id' => null,
			'Morbus_isClose' => null,
			'MorbusBase_id' => null,
			'IsCreate' => 1,
			'disableAddEvnNotify' => 0,
		);
		if (empty($response['MorbusType_id'])) {
			throw new Exception('Передан неправильный код типа заболеваний', 500);
		}

		// Проверка существования у человека актуального учетного документа с данной группой диагнозов для определения последнего диагноза заболевания
		if ( in_array($mode, array('onAfterSaveEvn','onBeforeSaveEvnNotifyFromDispCard','onBeforeSaveEvnNotifyFromJrn')) ) {
			if (/*empty($data['Diag_id']) ||*/ empty($data['Person_id'])) {
				throw new Exception('Переданы неправильные параметры', 500);
			}
			// ищем по Person_id и Diag_id
			$data['Evn_pid'] = null;
		} else {
			// ищем по Evn_pid
			$data['Person_id'] = null;
			if ($morbus_type != 'onko') $data['Diag_id'] = null;
		}
		if(empty($data['Diag_id'])) $data['Diag_id'] = null;

		if(empty($data['sopid'])) $data['sopid'] = null;
		if(empty($data['EvnDiagPLSop_id'])) $data['EvnDiagPLSop_id'] = null;
		$tmp = self::getStaticMorbusCommon()->loadLastEvnData($morbus_type, $data['Evn_pid'], $data['Person_id'], $data['Diag_id'], $data['sopid'], $data['EvnDiagPLSop_id']);
		if (empty($tmp)) {
			if ( in_array($mode, array('onAfterSaveEvn','onBeforeSaveEvnNotifyFromDispCard','onBeforeSaveEvnNotifyFromJrn')) ) {
				// сохранение в транзакции, в БД не найдено других учетных документов
				$response['Evn_aid'] = $response['Evn_pid'];
				$response['Person_id'] = $data['Person_id'];
				$response['Diag_id'] = $data['Diag_id'];
				$response['filter_Diag_id'] = $data['Diag_id'];
			} else {
				throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием', 500);
			}
		} else {
			$response['Evn_aid'] = $tmp[0]['Evn_id'];
			$response['Person_id'] = $tmp[0]['Person_id'];
			$response['Diag_id'] = $tmp[0]['Diag_id'];
			$response['Diag_Code'] = $tmp[0]['Diag_Code'];
			$response['filter_Diag_id'] = $tmp[0]['filter_Diag_id'];
		}

		//Проверка существования открытого или закрытого заболевания у человека с данной группой диагнозов
		$specificTable = self::getNameMorbusSpecificsTable($morbus_type);
		$tmp = self::getStaticMorbusCommon()->checkExistsExtended($morbus_type, $response['Person_id'], $response['Diag_id'], $add_select = "
			,Morbus.Morbus_disDT as \"Morbus_disDT\"
			,Morbus.Evn_pid as \"Evn_pid\"
			,Morbus.Diag_id as \"Diag_id\"
			,to_char(Morbus.Morbus_setDT, 'YYYY-MM-DD') as \"Morbus_setDT\"
			,v_{$specificTable}.{$specificTable}_id as \"{$specificTable}_id\"
			,v_EvnNotifyBase.EvnNotifyBase_id as \"EvnNotifyBase_id\"
			,v_PersonRegister.PersonRegister_id as \"PersonRegister_id\"
		", $add_join = "
			inner join v_{$specificTable} on v_{$specificTable}.Morbus_id = Morbus.Morbus_id
			left join v_EvnNotifyBase on v_EvnNotifyBase.Morbus_id = Morbus.Morbus_id
			left join v_PersonRegister on v_PersonRegister.Morbus_id = Morbus.Morbus_id
		", false);
		if ( false == is_array($tmp) ) {
			throw new Exception('Неправильный ответ модели', 500);
		}
		if ( empty($tmp) ) {
			//Нет ни открытого ни закрытого заболевания
			if (in_array($mode, array('onBeforeViewData', 'onBeforeSaveEvnNotify', 'onBeforeSaveEvnNotifyFromJrn')) 
				|| ($mode == 'onAfterSaveEvn' && $morbus_type == 'nephro' && in_array(self::getRegionNick(), array('perm', 'ufa')))) 
			{
				if ($response['Evn_pid'] != $response['Evn_aid']) {
					// В системе есть более актуальный учетный документ по этому заболеванию
					if (in_array($mode, array('onBeforeViewData', 'onAfterSaveEvn'))) {
						// Создаем заболевание со спецификой с диагнозом из учетного документа $response['Evn_pid']
						$response['Diag_id'] = $response['filter_Diag_id'];
					} else {
						// запрещаем создание извещения из не актуального учетного документа?
						throw new Exception('В системе есть более актуальный учетный документ по этому заболеванию. Нельзя создать извещение', 500);
					}
				}
				// Создание заболевания со спецификой
				$tmp = self::createMorbusSpecific($morbus_type, array(
					'Evn_pid' => $response['Evn_aid'],// создаем с привязкой к актуальному учетному документу
					'Diag_id' => $response['Diag_id'],// создаем с диагнозом актуального учетного документа
					'Person_id' => $response['Person_id'],
					'MorbusType_id' => $response['MorbusType_id'],
					//'Morbus_setDT' => $evn->setDate, // создаем с текущей датой
					'session' => $data['session'],
				), $mode);
				$response = array_merge($response, $tmp);
				$response['IsCreate'] = 2;
				$response['Morbus_isClose'] = 1;
				return $response;
			} else {
				return $response;
			}
		} else {
			//В системе найдено заболеваниe
			if (empty($tmp[0][$specificTable . '_id'])) {
				$tmp[0][$specificTable . '_id'] = self::_repairSpecifics($morbus_type, array(
					'Person_id' => $response['Person_id'],
					'MorbusBase_id' => $tmp[0]['MorbusBase_id'],
					'MorbusType_id' => $response['MorbusType_id'],
					'Morbus_id' => $tmp[0]['Morbus_id'],
					'Diag_id' => $tmp[0]['Diag_id'],
					'Morbus_setDT' => $tmp[0]['Morbus_setDT'],
					'Evn_pid' => $tmp[0]['Evn_pid'],
					'session' => $data['session'],
				), 'onBeforeViewData' == $mode);
			}
			$response['MorbusBase_id'] = $tmp[0]['MorbusBase_id'];
			$response['Morbus_id'] = $tmp[0]['Morbus_id'];
			$response[$specificTable . '_id'] = $tmp[0][$specificTable . '_id'];
			if (isset($tmp[0]['EvnNotifyBase_id']) || isset($tmp[0]['PersonRegister_id'])) {
				$response['disableAddEvnNotify'] = 1;
			}
			if ($tmp[0]['Morbus_disDT']) {
				$response['Morbus_isClose'] = 2;
				// когда врач сохраняет учетный документ с диагнозом закрытого заболевания, новое заболевание не создаем и не даем редактировать специфику, т.к. открывать заболевание может только оператор по регистрам при добавлении новой записи.
				// Ничего не сохраняем, возвращаем результат проверки
				return $response;
			} else {
				$response['Morbus_isClose'] = 1;
				//есть открытое заболевание и сохранен НЕ актуальный учетный документ
				if ($response['Evn_pid'] != $response['Evn_aid']) {
					//Ничего не сохраняем, возвращаем результат проверки
					return $response;
				}
			}
			if ('onAfterSaveEvn' == $mode) {
				//Был сохранен актуальный учетный документ, обновляем диагноз заболевания
				self::getStaticMorbusSimple()->reset();
				self::getStaticMorbusSimple()->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
				self::getStaticMorbusSimple()->setParams(array('session' => $data['session']));
				$tmp = self::getStaticMorbusSimple()->updateDiag_id($response['Morbus_id'], $response['Diag_id'], false);
				if ($tmp['Error_Msg']) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			}
			return $response;
		}
	}

	/**
	 * Восстановление специфики заболевания
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $data Обязательные параметры: MorbusBase_id, MorbusType_id, Morbus_id, Morbus_setDT, Diag_id, Person_id, Evn_pid, session
	 * @param bool $isAllowTransaction
	 * @return int
	 * @throws Exception
	 */
	private static function _repairSpecifics($morbus_type, $data, $isAllowTransaction = true)
	{
		if (true || 'onko' == $morbus_type) {
			$specificTable = self::getNameMorbusSpecificsTable($morbus_type);
			/**
			 * @var MorbusOnkoSpecifics_model $morbusSpecifics
			 */
			$morbusSpecifics = self::getStaticMorbusSpecificsModel($morbus_type);
			if (empty($morbusSpecifics) || false == method_exists($morbusSpecifics, 'autoCreate')) {
				throw new Exception('Для этого типа заболевания не реализовано создание специфики заболевания', 500);
			}
			$tmp = $morbusSpecifics->autoCreate(array(
				'Person_id' => $data['Person_id'],
				'MorbusBase_id' => $data['MorbusBase_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Morbus_id' => $data['Morbus_id'],
				'Diag_id' => $data['Diag_id'],
				'Morbus_setDT' => $data['Morbus_setDT'],
				'Morbus_confirmDate' => !empty($data['Morbus_confirmDate'])?$data['Morbus_confirmDate']:null,
				'Morbus_EpidemCode' => !empty($data['Morbus_EpidemCode'])?$data['Morbus_EpidemCode']:null,
				'Evn_pid' => $data['Evn_pid'],
				'session' => $data['session'],
				'mode' => 'repairSpecifics',
			), $isAllowTransaction);
			if ($tmp['Error_Msg']) {
				throw new Exception($tmp['Error_Msg'], 500);
			}
			if (empty($tmp[$specificTable . '_id'])) {
				throw new Exception('Неправильный ответ модели', 500);
			}
			return $tmp[$specificTable . '_id'];
		} else {
			//для проверки будет ли сюда заходить
			throw new Exception('Заболевание есть, а специфика не создана', 500);
		}
	}

	/**
	 * Проверка существования заболевания
	 *
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $data Обязательные параметры: Morbus_setDT, Diag_id, Person_id,session
	 * @param string $mode
	 * onBeforeViewData Вызывается перед загрузкой данных специфики в панель просмотра формы записи регистра
	 * onBeforeSavePersonRegister Вызывается перед созданием записи регистра
	 * @return array
	 * @throws Exception
	 */
	public static function checkByPersonRegister($morbus_type, $data, $mode)
	{
		if (empty($data['Person_id'])
			|| empty($data['Diag_id'])
			|| empty($data['Morbus_setDT'])
			|| empty($data['session'])
			|| false == in_array($mode, array('onBeforeViewData', 'onBeforeSavePersonRegister'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$response = array(
			'MorbusType_id' => self::getMorbusTypeIdBySysNick($morbus_type),
			'Evn_pid' => null,
			'Evn_aid' => null,
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id'],
		);
		if (empty($response['MorbusType_id'])) {
			throw new Exception('Передан неправильный код типа заболеваний', 500);
		}
		// Есть закрытая запись регистра, но оператор ответил, что заболевание Новое
		$isDouble = !empty($data['isDouble']);

		// Проверка существования ОТКРЫТОГО заболевания у человека с данной группой диагнозов
		$specificTable = self::getNameMorbusSpecificsTable($morbus_type);
		$tmp = self::getStaticMorbusCommon()->checkExistsExtended($morbus_type, $response['Person_id'], $response['Diag_id'], $add_select = "
			,v_{$specificTable}.{$specificTable}_id as \"{$specificTable}_id\"
			,Morbus.Evn_pid as \"Evn_pid\"
			,Morbus.Diag_id as \"Diag_id\"
			,to_char(Morbus.Morbus_setDT, 'YYYY-MM-DD') as \"Morbus_setDT\"
		", $add_join = "
			left join v_{$specificTable} on v_{$specificTable}.Morbus_id = Morbus.Morbus_id
		", true);
		if ( false == is_array($tmp) ) {
			throw new Exception('Неправильный ответ модели', 500);
		}
		if ( empty($tmp) ) {
			//Нет открытого заболевания
			//Ищем последний учетный документ у человека с данной группой диагнозов для привязки заболевания к нему
			$tmp = self::getStaticMorbusCommon()->loadLastEvnData($morbus_type, null, $data['Person_id'], $data['Diag_id']);
			if (empty($tmp)) {
				$response['Evn_aid'] = null;
			} else {
				$response['Evn_aid'] = $tmp[0]['Evn_id'];
			}
			// Создание заболевания с диагнозом и датой, указанными в регистре
			$tmp = self::createMorbusSpecific($morbus_type, array(
				'Evn_pid' => $response['Evn_aid'],// создаем с привязкой к актуальному учетному документу, если он есть
				'Diag_id' => $response['Diag_id'],
				'Person_id' => $response['Person_id'],
				'MorbusType_id' => $response['MorbusType_id'],
				'Morbus_setDT' => $data['Morbus_setDT'],
				'Morbus_confirmDate' => !empty($data['Morbus_confirmDate'])?$data['Morbus_confirmDate']:null,
				'Morbus_EpidemCode' => !empty($data['Morbus_EpidemCode'])?$data['Morbus_EpidemCode']:null,
				'session' => $data['session'],
			), $mode);
			$response = array_merge($response, $tmp);
			$response['IsCreate'] = 2;
			$response['Morbus_isClose'] = 1;
			return $response;
		} else {
			//В системе найдено открытое заболеваниe
			$response['IsCreate'] = 1;
			$response['Morbus_isClose'] = 1;
			if (empty($tmp[0][$specificTable . '_id'])) {
				$tmp[0][$specificTable . '_id'] = self::_repairSpecifics($morbus_type, array(
					'Person_id' => $response['Person_id'],
					'MorbusBase_id' => $tmp[0]['MorbusBase_id'],
					'MorbusType_id' => $response['MorbusType_id'],
					'Morbus_id' => $tmp[0]['Morbus_id'],
					'Diag_id' => $tmp[0]['Diag_id'],
					'Morbus_setDT' => $tmp[0]['Morbus_setDT'],
					'Evn_pid' => $tmp[0]['Evn_pid'],
					'session' => $data['session'],
				), 'onBeforeViewData' == $mode);
			}
			$response['MorbusBase_id'] = $tmp[0]['MorbusBase_id'];
			$response['Morbus_id'] = $tmp[0]['Morbus_id'];
			$response[$specificTable . '_id'] = $tmp[0][$specificTable . '_id'];
			return $response;
		}
	}

	/**
	 * Создание заболевания со спецификой
	 *
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $data
	 * Обязательные параметры: Diag_id, Person_id, MorbusType_id, session
	 * Условно необязательные параметры: Evn_pid, Morbus_setDT
	 * @param string $mode
	 * onBeforeViewData Вызывается перед загрузкой данных специфики в панель просмотра ЭМК или формы записи регистра
	 * onBeforeSaveEvnNotify Вызывается перед сохранением извещения
	 * onBeforeSavePersonRegister Вызывается перед созданием записи регистра
	 * @return array Ответ всегда содержит ключи Morbus_id, MorbusBase_id и идешник таблицы со спецификой
	 * @throws Exception
	 */
	public static function createMorbusSpecific($morbus_type, $data, $mode)
	{
		$response = array();
		if (empty($data['Person_id'])
			|| empty($data['Diag_id'])
			|| empty($data['MorbusType_id'])
			|| empty($data['session'])
			|| false == in_array($mode, array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify', 'onBeforeSaveEvnNotifyFromJrn', 'onAfterSaveEvn'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		if ( 'onBeforeSaveEvnNotify' == $mode ) {
			if (empty($data['Evn_pid'])) {
				throw new Exception('Не указан учетный документ', 500);
			}
		}
		if ( 'onBeforeSavePersonRegister' == $mode ) {
			if (empty($data['Morbus_setDT'])) {
				throw new Exception('Не указана дата включения в регистр', 500);
			}
		}
		if (empty($data['Evn_pid'])) {
			$data['Evn_pid'] = null;
		}
		if (empty($data['Morbus_setDT'])) {
			$data['Morbus_setDT'] = self::getStaticMorbusCommon()->currentDT->format('Y-m-d');
		}

		// Создание общего заболевания, если его не существует
		$tmp = self::getStaticMorbusCommon()->autoCreate(array(
			'Evn_pid' => $data['Evn_pid'],
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'MorbusBase_setDT' => $data['Morbus_setDT'],
			'session' => $data['session'],
		), false);
		if ($tmp['Error_Msg']) {
			throw new Exception($tmp['Error_Msg'], 500);
		}
		$response['MorbusBase_id'] = $tmp['MorbusBase_id'];

		// Создание простого заболевания
		$tmp = self::getStaticMorbusSimple()->autoCreate(array(
			'MorbusBase_id' => $response['MorbusBase_id'],
			'Evn_pid' => $data['Evn_pid'],
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id'],
			'Morbus_setDT' => $data['Morbus_setDT'],
			'session' => $data['session'],
		), false);
		if ($tmp['Error_Msg']) {
			throw new Exception($tmp['Error_Msg'], 500);
		}
		$response['Morbus_id'] = $tmp['Morbus_id'];

		// Создание специфики заболевания
		$specificTable = self::getNameMorbusSpecificsTable($morbus_type);
		/**
		 * @var swModel $morbusSpecifics
		 */
		$morbusSpecifics = self::getStaticMorbusSpecificsModel($morbus_type);
		if (empty($morbusSpecifics) || false == method_exists($morbusSpecifics, 'autoCreate')) {
			throw new Exception('Для этого типа заболевания не реализовано создание специфики заболевания', 500);
		}
		$tmp = $morbusSpecifics->autoCreate(array(
			'Person_id' => $data['Person_id'],
			'MorbusBase_id' => $response['MorbusBase_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'Morbus_id' => $response['Morbus_id'],
			'Diag_id' => $data['Diag_id'],
			'Morbus_setDT' => $data['Morbus_setDT'],
			'Morbus_confirmDate' => !empty($data['Morbus_confirmDate'])?$data['Morbus_confirmDate']:null,
			'Morbus_EpidemCode' => !empty($data['Morbus_EpidemCode'])?$data['Morbus_EpidemCode']:null,
			'Evn_pid' => $data['Evn_pid'],
			'session' => $data['session'],
			'mode' => $mode,
		), false);
		if ($tmp['Error_Msg']) {
			throw new Exception($tmp['Error_Msg'], 500);
		}
		if (empty($tmp[$specificTable . '_id'])) {
			throw new Exception('Неправильный ответ модели', 500);
		}
		$response[$specificTable . '_id'] = $tmp[$specificTable . '_id'];
		return $response;
	}

	/**
	 * @param string $morbus_alias
	 * @param string $morbus_base_alias
	 * @param string $pid_parameter
	 * @param string $edit_value
	 * @param string $view_value
	 * @param string $type_alias
	 * @param string $specific_cause Контроль на редактирование специфики по актуальному документу
	 * @return string
	 */
	public static function getAccessTypeQueryPart($morbus_alias, $morbus_base_alias, $pid_parameter, $edit_value = 'edit', $view_value = 'view', $type_alias = 'accessType', $specific_cause = '')
	{
		if ('kareliya' == getRegionNick() && 'MorbusCrazy_pid' == $pid_parameter) {
			// временно #69630
			return "'{$edit_value}' as \"{$type_alias}\"";
		}
		return "case 
					when {$morbus_alias}.Morbus_disDT is null /* заболевание открыто */
					AND (
						{$morbus_base_alias}.Person_id = :{$pid_parameter} or exists(
							select * from v_Evn EvnEdit
							inner join v_Evn EvnRoot on EvnRoot.Evn_id = EvnEdit.Evn_rid
								AND COALESCE(EvnRoot.Evn_IsSigned,1) = 1 /* EvnEdit.Evn_rid не подписан */
							where EvnEdit.Evn_id = :{$pid_parameter}
								AND COALESCE(EvnEdit.Evn_IsSigned,1) = 1 /* {$pid_parameter} не подписан */
								{$specific_cause}
						)
					) then '{$edit_value}' else '{$view_value}' end as \"{$type_alias}\"";
	}

	/**
	 * @param string $morbus_type MorbusType_SysNick
	 * @param array $session
	 * @param int $pid
	 * @param int $PersonRegister_id
	 * @return array
	 */
	public static function onBeforeViewData($morbus_type, $session, $pid, $PersonRegister_id = null, $sopid = null, $EvnDiagPLSop_id = null)
	{
		$response = array(
			'Error_Msg' => null,
			'PersonRegister_id' => $PersonRegister_id,
		);
		try {
			self::getStaticMorbusCommon()->isAllowTransaction = false;
			if (empty($pid)) {
				throw new Exception('Не передан параметр pid');
			}
			if (empty($session)) {
				throw new Exception('Не переданы параметры сеанса пользователя');
			}
			//Ищем Diag_id Person_id по PersonRegister_id или по Evn_id
			$params = array();
			if ($PersonRegister_id > 0) {
				$mode = 'onBeforePersonRegisterViewData';
				$params['PersonRegister_id'] = $PersonRegister_id;
				$query = "
					select
						Morbus_id as \"Morbus_id\"
						,Person_id as \"Person_id\"
						,Diag_id as \"Diag_id\"
						,to_char(PersonRegister_setDate, 'YYYY-MM-DD') as \"Morbus_setDT\"
						,null as \"Evn_pid\"
						,null as \"EvnClass_SysNick\"
					from
						v_PersonRegister
					where
						PersonRegister_id = :PersonRegister_id
				";
			} else {
				$mode = 'onBeforeEvnViewData';
				$params['Evn_id'] = $pid;
				$params['EvnDiagPLSop_id'] = $EvnDiagPLSop_id;
				$params['MorbusType_id'] = self::getMorbusTypeIdBySysNick($morbus_type);
				$query = "
					select
						M.Morbus_id as \"Morbus_id\"
						,Evn.Person_id as \"Person_id\"
						,coalesce(EPLDSO.Diag_spid,PL.Diag_spid,ES.Diag_spid,EPLDD13.Diag_spid,ED.Diag_id,EDPS.Diag_id,EPS.Diag_pid,PL.Diag_id,ES.Diag_id,DD.Diag_id) as \"Diag_id\"
						,M.Diag_id as \"MorbusDiag_id\"
						,to_char(Evn.Evn_setDT, 'YYYY-MM-DD') as \"Morbus_setDT\"
						,Evn.Evn_id as \"Evn_pid\"
						,EvnClass.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_Evn Evn
						inner join EvnClass on Evn.EvnClass_id = EvnClass.EvnClass_id
						left join v_Morbus M on M.Morbus_id = Evn.Morbus_id and M.MorbusType_id = :MorbusType_id
						left join EvnVizitPL PL on PL.Evn_id = Evn.Evn_id
						left join v_EvnPLDispScreenOnko EPLDSO on EPLDSO.EvnPLDispScreenOnko_pid = Evn.Evn_id
						left join v_EvnVizitDispDop DD on DD.EvnVizitDispDop_id = Evn.Evn_id
						left join v_EvnPLDispDop13 EPLDD13 on EPLDD13.EvnPLDispDop13_id = Evn.Evn_pid
						left join EvnSection ES on ES.Evn_id = Evn.Evn_id
						left join EvnPS EPS on EPS.Evn_id = Evn.Evn_id
						left join v_EvnDiagPLStom EDPS on EDPS.EvnDiagPLStom_id = Evn.Evn_id
						left join v_EvnDiag ED on ED.EvnDiag_id = :EvnDiagPLSop_id
					where
						Evn.Evn_id = :Evn_id
				";
			}
			$tmp = self::getStaticMorbusCommon()->getFirstRowFromQuery($query, $params);
			if (!empty($tmp) && $tmp['EvnClass_SysNick'] == 'EvnDiagPLStom') {
				$params['EvnDiagPLStomSop_id'] = $sopid;
				$params['MorbusType_id'] = self::getMorbusTypeIdBySysNick($morbus_type);
				$query = "
					select
						M.Morbus_id as \"Morbus_id\"
						,Evn.Person_id as \"Person_id\"
						,coalesce(EDPLS.Diag_id,EDPS.Diag_id) as \"Diag_id\"
						,M.Diag_id as \"MorbusDiag_id\"
						,to_char(Evn.Evn_setDT, 'YYYY-MM-DD') as \"Morbus_setDT\"
						,Evn.Evn_pid as \"Evn_pid\"
						,EvnClass.EvnClass_SysNick as \"EvnClass_SysNick\"
					from
						v_Evn Evn
						inner join v_Evn EvnPid on EvnPid.Evn_id = Evn.Evn_pid
						inner join EvnClass on EvnClass.EvnClass_id = EvnPid.EvnClass_id
						left join v_Morbus M on M.Morbus_id = Evn.Morbus_id and M.MorbusType_id = :MorbusType_id
						left join v_EvnDiagPLStom EDPS on EDPS.EvnDiagPLStom_id = Evn.Evn_id
						left join v_EvnDiagPLStomSop EDPLS on EDPLS.EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id
					where
						Evn.Evn_id = :Evn_id
				";
				$tmp = self::getStaticMorbusCommon()->getFirstRowFromQuery($query, $params);
			}
			if ('onBeforePersonRegisterViewData' == $mode) {
				if (empty($tmp)) {
					throw new Exception('Не удалось получить данные по записи регистра заболеваний');
				}
				if (empty($tmp['Morbus_id'])) {
					//сделал так для проверки, что Morbus_id нигде не затирается
					throw new Exception('Не удалось получить заболевание по записи регистра заболеваний');
				}
				if (empty($tmp['Diag_id'])) {
					throw new Exception('Не удалось получить диагноз по записи регистра заболеваний');
				}
				if (empty($tmp['Morbus_setDT'])) {
					throw new Exception('Не удалось получить дату включения в регистр заболеваний');
				}
				if ($pid != $tmp['Person_id']) {
					throw new Exception('Получены данные другого человека');
				}
			} else {
				if (empty($tmp)) {
					throw new Exception('Не удалось получить данные по учетному документу');
				}
				if (empty($tmp['Diag_id'])) {
					throw new Exception('Не удалось получить диагноз по учетному документу');
				}
				if (empty($tmp['Person_id'])) {
					throw new Exception('Не удалось получить человека по учетному документу');
				}
			}
			$response['Morbus_id'] = $tmp['Morbus_id'];
			$response['Person_id'] = $tmp['Person_id'];
			$response['Diag_id'] = $tmp['Diag_id'];
			$response['Morbus_setDT'] = $tmp['Morbus_setDT'];
			$response['Evn_pid'] = $tmp['Evn_pid'];
			$response['EvnClass_SysNick'] = $tmp['EvnClass_SysNick'];
			$response['sopid'] = $sopid;
			$response['EvnDiagPLSop_id'] = $EvnDiagPLSop_id;
			
			if ($morbus_type == 'onko' && isset($tmp['MorbusDiag_id']) && $tmp['MorbusDiag_id'] != $tmp['Diag_id']) {
				$response['Morbus_id'] = null;
			}

			if (empty($response['Morbus_id'])) {
				/*
				Не нашли, возможные причины:
				1) Заболевание не создано
				2) Заболевание есть, но нет связи заболевания с PersonRegister или с Evn
				*/
				//Проверяем заболевание, если его нет, то создаем $morbus_type, $session,
				self::getStaticMorbusCommon()->isAllowTransaction = true;
				self::getStaticMorbusCommon()->beginTransaction();
				$specificTable = self::getNameMorbusSpecificsTable($morbus_type);
				$params = $response;
				$params['session'] = $session;
				if('onBeforePersonRegisterViewData' == $mode) {
					$tmp = self::checkByPersonRegister($morbus_type, $params, 'onBeforeViewData');
				} else {
					$tmp = self::checkByEvn($morbus_type, $params, 'onBeforeViewData');
				}
				if (empty($tmp['Morbus_id']) || empty($tmp[$specificTable . '_id']) ) {
					throw new Exception('Неправильный результат операции проверки и создания заболевания', 500);
				}
				$response[$specificTable . '_id'] = $tmp[$specificTable . '_id'];
				$response['Morbus_id'] = $tmp['Morbus_id'];
				if ('onBeforePersonRegisterViewData' == $mode) {
					$tmp = self::updateMorbusIntoPersonRegister(array(
						'PersonRegister_id' => $response['PersonRegister_id'],
						'Morbus_id' => $response['Morbus_id'],
						'session' => $session,
					));
					if (isset($tmp['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp['Error_Msg']);
					}
				} else {
					$tmp = self::updateMorbusIntoEvn(array(
						'Evn_id' => $response['Evn_pid'],
						'Morbus_id' => $response['Morbus_id'],
						'session' => $session,
						'mode' => $mode,
					));
					if (isset($tmp['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp['Error_Msg']);
					}
				}
				self::getStaticMorbusCommon()->commitTransaction();
			}
		} catch (Exception $e) {
			self::getStaticMorbusCommon()->rollbackTransaction();
			$response['Error_Msg'] = $e->getMessage();
		}
		return $response;
	}

	/**
	 * Проверка на существование и автоматическое добавление извещения по нефрологии при сохранении посещений и движений
	 * @return 
	 */
	public static function checkAndSaveEvnNotifyNephro(EvnAbstract_model $evn, $morbus_id)
	{
		if (empty(self::$staticNotifyNephro)) {
			self::getCiInstance()->load->model('EvnNotifyNephro_model', 'staticNotifyNephro');
			self::$staticNotifyNephro = self::getCiInstance()->staticNotifyNephro;
		}
		$check = self::$staticNotifyNephro->checkNephroRegAndNotify($morbus_id);
		$finded = false;
		foreach ($check as $value) {
			if(!empty($value['EvnNotifyNephro_id']) || !empty($value['PersonRegister_id'])){
				$finded = true;
			}
		}
		if($finded){
			return;
		}
		$params = array(
			'autoCreate'=>1,
			'pid'=>$evn->id,
			'EvnNotifyNephro_pid'=>$evn->id,
			'EvnNotifyNephro_setDate'=>date('Y-m-d'),
			'PersonEvn_id'=>$evn->personevn_id,
			'Server_id'=>$evn->server_id,
			'MorbusType_SysNick'=>'nephro',
			'Lpu_id'=>$evn->Lpu_id,
			'Diag_id'=>$evn->Diag_id,
			'session'=>$evn->sessionParams,
			'pmUser_id'=>$evn->promedUserId,
			'Person_id'=>$evn->person_id,
			'MedPersonal_id'=>$evn->medpersonal_id
		);
		self::$staticNotifyNephro->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = self::$staticNotifyNephro->doSave($params);
		if(!empty($response['Error_Msg'])) {
			throw new Exception($response['Error_Msg']);
		} else if(!empty($response['EvnNotifyNephro_id'])){
			$resp = self::$staticNotifyNephro->getNephroNotifyDoubles($response);
			if(is_array($resp) && count($resp) > 1 && !empty($resp[0]['EvnNotifyNephro_id'])){
				$delParams = array('EvnNotifyNephro_id'=>$resp[0]['EvnNotifyNephro_id'],'pmUser_id'=>$params['pmUser_id']);
				$res = self::$staticNotifyNephro->execCommonSP('dbo.p_EvnNotifyNephro_del',$delParams);
			}
		}
	}

	/**
	 * Проверка на существование и автоматическое добавление извещения по нефрологии при одобрении результатов исследований
	 * @return 
	 */
	public static function checkAndSaveEvnNotifyNephroFromLab($data)
	{
		if(empty($data['data']['Person_id'])
			||empty($data['data']['EvnUslugaPar_pid'])
			|| empty($data['data']['PersonEvn_id'])
			|| empty($data['data']['Lpu_id'])
			|| empty($data['session'])
			|| empty($data['pmUser_id'])
			|| empty($data['session']['medpersonal_id'])
		) {
			return;
		}
		if (empty(self::$staticNotifyNephro)) {
			self::getCiInstance()->load->model('EvnNotifyNephro_model', 'staticNotifyNephro');
			self::$staticNotifyNephro = self::getCiInstance()->staticNotifyNephro;
		}
		$check = self::$staticNotifyNephro->checkNephroRegAndNotifyByPerson(array('Person_id'=>$data['data']['Person_id']));
		$finded = false;
		foreach ($check as $value) {
			if(!empty($value['EvnNotifyNephro_id']) || !empty($value['PersonRegister_id'])){
				$finded = true;
			}
		}
		if($finded){
			return;
		}
		$params = array(
			'autoCreate'=>1,
			'pid'=>$data['data']['EvnUslugaPar_pid'],
			'EvnNotifyNephro_pid'=>$data['data']['EvnUslugaPar_pid'],
			'EvnNotifyNephro_setDate'=>date('Y-m-d'),
			'PersonEvn_id'=>$data['data']['PersonEvn_id'],
			'Server_id'=>$data['data']['Server_id'],
			'MorbusType_SysNick'=>'nephro',
			'Lpu_id'=>$data['data']['Lpu_id'],
			'Diag_id'=>(!empty($data['data']['Diag_id'])?$data['data']['Diag_id']:null),
			'session'=>$data['session'],
			'pmUser_id'=>$data['pmUser_id'],
			'Person_id'=>$data['data']['Person_id'],
			'MedPersonal_id'=>$data['session']['medpersonal_id'],
			'fromLab'=>1
		);
		$check = self::$staticNotifyNephro->checkDiagIsNephro(array('Diag_id'=>$params['Diag_id']));
		if(!is_array($check) || count($check) == 0){
			$params['Diag_id'] = 7330; // Если диагноз не нефрологический, то используем N18.9
		}
		self::$staticNotifyNephro->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = self::$staticNotifyNephro->doSave($params);
	}
}
