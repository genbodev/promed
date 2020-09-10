<?php
/**
* User_model - модель для работы с учетными записями пользователей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      22.05.2009
*/

class User_model extends swModel {

	/**
	 * Префикс Arm_id для БСМЭ
	 */
	const BSME_ARM_ID_PREF = 200;

    /**
     * Это Doc-блок
     */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Дополнительное условие для отображения АРМ приемного отделения
	 * @task https://redmine.swan.perm.ru/issues/30589
	 */
	function getStacPriemAdditionalCondition($data) {
		return false;
	}

	/**
	 * Определение типа места работы врача
	 */
	function defineARMType($res, $groups = null) {
		$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = $this->config->item('LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS');
		$ALLOW_EXTJS6_ARMS_FOR_ALL = $this->config->item('ALLOW_EXTJS6_ARMS_FOR_ALL');

		if ( !is_array($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS) ) {
			$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = array();
		}

		// Проверка на АРМы служб
		if (isset($res['MedServiceType_id']) && ($res['MedServiceType_id'] > 0)) {
			switch ($res['MedServiceType_id']) {
				case 11:
					if (havingGroup('lpucadradmin', $groups)) {
						return 'lpucadradmin';
					} else {
						return 'lpucadrview';
					}
					break;
				case 16:
					$return_array = array();

					$return_array[] = $res['MedServiceType_SysNick'];

					if (!empty($res['Lpu_id']) && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
						$return_array[] = 'regpol6';
					}

					return $return_array;

					break;
				case 18:
					$return_array = array();

					if (havingGroup('ppdmedserviceoper', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'];
					}

					if (havingGroup('dispnmp', $groups)) {
						$return_array[] = 'dispnmp';
					}

					if (sizeof($return_array) == 1) {
						return $return_array[0];
					} else {
						return $return_array;
					}

					break;

				case 19:
					$return_array = array();

					if (havingGroup('smpmedserviceoper', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'reg';
					}

					if (havingGroup('smpdispatchdirections', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'dispatchdirect';
					}

					if (havingGroup('smpcalldispath', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'dispatchcall';
					}

					if (havingGroup('smpinteractivemap', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'interactivemap';
					}

					if (havingGroup('smpadmin', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'admin';
					}

					if (havingGroup('smpheadduty', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'headduty';
					}

					if (havingGroup('smpheadbrig', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'headbrig';
					}

					if (havingGroup('smpdispatchstation', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'dispatchstation';
					}

					if (havingGroup('smpheaddoctor', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'headdoctor';
					}

					if (($cnt = sizeof($return_array))) {
						if ($cnt == 1) {
							return $return_array[0];
						} else {
							return $return_array;
						}
					}
					break;
				case 20:
					if (havingGroup('minzdravdlo', $groups)) {
						return $res['MedServiceType_SysNick'];
					}
					break;
				case 27:
					if (havingGroup('ouzuser', $groups) || havingGroup('ouzadmin', $groups) || havingGroup('ouzchief', $groups)) {
						return $res['MedServiceType_SysNick'];
					}
					break;

				/*case 36:
					if (havingGroup('remoteconsultcenter', $groups)) {
						return $res['MedServiceType_SysNick'];
					}
					break;*/
				case 40:
				case 42:
				case 43:
				case 44:
				case 45:
				case 46:
				case 47:
				case 48:
				case 54:
					$return_array = array();
					if (havingGroup('bsmesecretary', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'bsmesecretary';
					}

					if (havingGroup('bsmehead', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'bsmehead';
					}

					if (havingGroup('bsmeexpert', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'bsmeexpert';
					}

					if (havingGroup('bsmeexpertassistant', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'bsmeexpertassistant';
					}

					if (havingGroup('bsmedprthead', $groups)) {
						$return_array[] = $res['MedServiceType_SysNick'] . 'bsmedprthead';
					}
					if ( ( $cnt = sizeof( $return_array ) ) ) {
						if ( $cnt == 1 ) {
							return $return_array[0];
						} else {
							return $return_array;
						}
					}
					break;
				case 57:
					return str_replace('_', '', $res['MedServiceType_SysNick']);
					break;
				case 61:
					if (havingGroup('lvn', $groups)) {
						return $res['MedServiceType_SysNick'];
					}
					break;
				case 63:
					if (havingGroup('zmk', $groups)) {
						return $res['MedServiceType_SysNick'];
					}
					break;

				case 64:
					return $res['MedServiceType_SysNick'];
					break;

				case 66:
					return 'paidservice';
					break;

				default:
					return strtolower($res['MedServiceType_SysNick']);
					break;
			}
		}

		// Проверка на АРМы отделений
		if (!empty($res['MedStaffFact_id']) && !empty($res['LpuUnitType_SysNick'])) {
			//Загрузка дополнительных профилей для отделений Екатеринбурга
			if ($_SESSION['region']['nick'] == 'ekb') {
				$this->load->model('LpuStructure_model', 'lsmodel');
				$profile_list = $this->lsmodel->loadLpuSectionProfileList(array(
					'LpuSection_id' => $res['LpuSection_id'],
					'additionWithDefault' => 2
				));
			}

			switch ($res['LpuUnitType_SysNick']) {
				case 'polka':
				case 'ccenter':
				case 'traumcenter':
				case 'fap':
					switch ( $this->regionNick ) {
						case 'ekb':
							$_REGION = $this->config->item($this->regionNick);
							$return_array = array();
							$temp = '';
							foreach($profile_list as $item) {
								if (isset($_REGION['STOM_LSP_CODE_LIST']) && in_array($item['LpuSectionProfile_Code'], $_REGION['STOM_LSP_CODE_LIST'], true)) {
									$temp = 'stom';
								}
								else {
									$temp = 'common';
								}
								if (!in_array($temp, $return_array)) {
									$return_array[] = $temp;
									if ($temp == 'common' && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
											$return_array[] = 'polka';
										}
									}
									if ($temp == 'stom' && (!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
											$return_array[] = 'stom6';
								}
							}
								}
							}
							if (count($return_array) == 1) {
								return $return_array[0];
							} else {
								return $return_array;
							}
							break;

						case 'ufa':
						case 'perm':
						case 'penza':
						case 'by':
						case 'khak':
						case 'astra':
						case 'kareliya':
						case 'buryatiya':
						case 'pskov':
						case 'kaluga':
						case 'krym':
						case 'kz':
						case 'komi':
						case 'vologda':
						case 'krasnoyarsk':
						case 'yaroslavl':
						case 'yakutiya':
							$return_array = array();
							$isPhys = false;
							$_REGION = $this->config->item($this->regionNick);
							if (isset($_REGION['STOM_LSP_CODE_LIST']) && in_array($res['LpuSectionProfile_Code'], $_REGION['STOM_LSP_CODE_LIST'], true)) {
								// АРМ стоматолога
								$return_array[] = 'stom';

								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
										$return_array[] = 'stom6';
							}
								}
							} else {
								// АРМ терапевта или физиотерапевта
								switch ($this->regionNick) {
									case 'kz':
										if ($res['LpuSectionProfile_Code'] == '1013') {
											$isPhys = true;
										}
										break;
									case 'ufa':
										if (in_array($res['LpuSectionProfile_Code'], ['572', '672', '10008'])) {
											$isPhys = true;
										}
										break;
									default:
										if ($res['LpuSectionProfile_Code'] == '109') {
											$isPhys = true;
										}
										break;
								}

								if (!$isPhys && ($this->regionNick != 'msk' || !havingGroup('OperLLO'))) {//если есть группа OperLLO, то АРМ врача не доступен #183310
									$return_array[] = 'common';

									if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
										if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
											$return_array[] = 'polka';
										}
									}
								} else if ($isPhys && !empty($res['LpuSection_id'])) {
									$isPhysMedService = $this->getFirstResultFromQuery("
										select top 1 MedService_id
										from v_MedService with (nolock)
										where MedServiceType_id = 13
											and LpuSection_id = :LpuSection_id 
									", array(
										'LpuSection_id' => $res['LpuSection_id']
									));
									if ($isPhysMedService !== false && !empty($isPhysMedService)) {
										$return_array[] = 'phys';
									}
								}
							}
							return $return_array;
							break;

						default:
							$return_array = array();
							if (substr($res['LpuSectionProfile_Code'], 0, 2) == '18') {
								$return_array[] = 'stom';

								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
										$return_array[] = 'stom6';
							}
								}
							}
							else if ($this->regionNick != 'msk' || !havingGroup('OperLLO')) { //если есть группа OperLLO, то АРМ врача не доступен #183310
								$return_array[] = 'common';

								if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($res['Lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS))) {
									if (empty($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection']) || in_array($res['LpuSection_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS[$res['Lpu_id']]['LpuSection'])) {
										$return_array[] = 'polka';
									}
								}
							}
							return $return_array;
							break;

					}
					break;
				/*
				case 'parka':
					return 'par';
				break;
				*/
				case 'stac':
				case 'hstac':
				case 'pstac':
				case 'dstac':
					if ($_SESSION['region']['nick'] == 'ekb') {
						$return_array = array();
						foreach($profile_list as $item) {
							$temp = '';
							// Доп. условие реализовано по задаче https://redmine.swan.perm.ru/issues/30589 для учета региональных особенностей
							if($item['LpuSectionProfile_SysNick']=='priem' || $this->getStacPriemAdditionalCondition($res))	{
								$temp = 'stacpriem';
							} else {
								// коды специальностей одинаковы (на Уфе тоже новый ЕРМП)
								$headnursecond = (in_array($res['PostMed_Code'], array('116')) && in_array($res['LpuUnitType_id'], array('1', '6', '9')));
								$stacnursecond = in_array($res['PostMed_Code'],array('126')) && in_array($res['LpuUnitType_id'], array('1', '6', '9'));

								if( $stacnursecond )
									$temp = 'stacnurse';
								else if( $headnursecond )
									$temp = 'headnurse';
								else if ( in_array($res['PostKind_id'], array(1, 10)) )
									$temp = 'stac';//array('stac','stachelpdesc');
							}
							if (!empty($temp) && !in_array($temp, $return_array)) {
								$return_array[] = $temp;
							}
						}
						if (count($return_array) == 1) {
							return $return_array[0];
						} else {
							return $return_array;
						}
					}

					// Доп. условие реализовано по задаче https://redmine.swan.perm.ru/issues/30589 для учета региональных особенностей
					if($res['LpuSectionProfile_SysNick']=='priem' || $this->getStacPriemAdditionalCondition($res))	{
						return 'stacpriem';
					}
					if ($_SESSION['region']['nick'] == 'kz') {
						// https://redmine.swan.perm.ru/issues/76440
						$headnursecond_postcode = ($this->config->item('IS_DEBUG') === '1' ? '116' : '108');
						$stacnursecond_postcode = ($this->config->item('IS_DEBUG') === '1' ? '126' : '117');
					}
					else {
						$headnursecond_postcode = '116';
						$stacnursecond_postcode = '126';
					}
					// коды специальностей одинаковы (на Уфе тоже новый ЕРМП)
					$headnursecond = (in_array($res['PostMed_Code'], array($headnursecond_postcode)) && in_array($res['LpuUnitType_id'], array('1', '6', '9')));
					$stacnursecond = in_array($res['PostMed_Code'],array($stacnursecond_postcode)) && in_array($res['LpuUnitType_id'], array('1', '6', '9'));

					if( $stacnursecond )
						return 'stacnurse';
					else if( $headnursecond )
						return 'headnurse';
					else if ( in_array($res['PostKind_id'], array(1, 10)) || $_SESSION['region']['nick'] == 'ufa' )
						return 'stac';//array('stac','stachelpdesc');
					else
						return '';
					break;

				// Это для Астрахани
				// https://redmine.swan.perm.ru/issues/30665
				case 'priem':
					return 'stacpriem';
					break;
			}
		} else {

		}
	}

	/**
	 * Получение списка армов для мест работы
	 */
	function getArmsForMedStaffFactList($data, $response) {
		if (!isset($response) || !is_array($response)) {
			$response = array();
		}

		if (empty($data['Groups'])) {
			$data['Groups'] = null;
		}

		// Получаем список всех АРМов
		$arms = $this->getARMList();

		if ( is_array( $response ) && sizeof( $response ) ) {
			// TODO: Получаем АРМ по умолчанию у текущего пользователя (из настоек пользователя)
			$doubles = array();
			for( $i=0, $cnt=sizeof($response); $i<$cnt; $i++ ){
				// Определяем тип АРМа для полученного места работы / службы

				$type = $this->defineARMType($response[$i], $data['Groups']);

				// Добавил !array_key_exists($type, $arms), т.к. в список попадают записи с сисниками служб, для которых нет АРМ
				// https://redmine.swan.perm.ru/issues/45267
				if (
					empty($type)
					|| (is_array($type) && count($type) == 0)
					|| (!is_array($type) && !array_key_exists($type, $arms))
				) {
					unset($response[$i]);
					continue;
				}
				if (!is_array($type)) {
					$type = array($type);
				}
				//Создаем дубли из записи по количеству возможных АРМов (если defineARMType вернул массив)
				foreach($type as $k=>$v) {
					$dbl = $response[$i];
					$dbl['ARMType'] = strtolower($v);
					// Определяем по типу допполя АРМов (наименования)
					if (isset($arms[$dbl['ARMType']])) { // Если тип АРМа описан
						$arm_name = $arms[$dbl['ARMType']]['Arm_Name'];
						if ($dbl['ARMType'] === 'operblock') {
							if (havingGroup('operblock_head', $data['Groups'])) {
								$arm_name = 'АРМ заведующего оперблоком';
							} else if (havingGroup('operblock_surg', $data['Groups'])) {
								$arm_name = 'АРМ хирурга оперблока';
							} else {
								unset($response[$i]); // если нет группы то АРМ оперблока не нужен.
								continue;
							}
						} else if ($dbl['ARMType'] === 'paidservice') {
							if (havingGroup('DrivingCommissionReg', $data['Groups'])) {
								$arm_name = 'АРМ регистратора платных услуг';
							} else if (havingGroup(array('DrivingCommissionOphth', 'DrivingCommissionPsych', 'DrivingCommissionPsychNark', 'DrivingCommissionTherap'), $data['Groups'])) {
								$arm_name = 'АРМ врача платных услуг';
							} else {
								unset($response[$i]); // если нет группы то АРМ не нужен.
								continue;
							}
						} else if ($dbl['ARMType'] === 'common') {
							if (havingGroup('DepHead', $data['Groups'])) {
								$arm_name = 'АРМ заведующего отделением поликлиники';
							}
						} else if ($dbl['ARMType'] === 'polka') {
							if (havingGroup('DepHead', $data['Groups'])) {
								$arm_name = 'АРМ заведующего отделением поликлиники (ExtJS 6)';
							}
						} else if ($dbl['ARMType'] === 'stac') {
							if (havingGroup('DepHead', $data['Groups'])) {
								$arm_name = 'АРМ заведующего отделением стационара';
							}
						}
						else if ($dbl['ARMType'] === 'phys') {
							//появились армы требующие как службы так и врача
							if(isset($data['ARMType'])){
								$physsql =	"
									select top 1
										ms.MedService_id,
										ms.MedService_Nick,
										ms.MedService_Name,
										ms.MedServiceType_id,
										ms.MedService_IsExternal,
										ms.MedService_IsLocalCMP,
										ms.MedService_LocalCMPPath,
										mst.MedServiceType_SysNick
									from v_MedService ms with (nolock)
										left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
										left join v_MedServiceMedPersonal msmp on msmp.MedService_id = ms.MedService_id and msmp.MedPersonal_id = :MedPersonal_id
										left join v_MedStaffFact msmpp on msmpp.MedPersonal_id = :MedPersonal_id
									where ms.LpuSection_id = :LpuSection_id and msmpp.MedStaffFact_id = :MedStaffFact_id
									";

								$physparams = array(
									'MedPersonal_id' => $dbl['MedPersonal_id'],
									'LpuSection_id' => $dbl['LpuSection_id'],
									'MedStaffFact_id' => $dbl['MedStaffFact_id']
								);

								$physres = $this->db->query($physsql, $physparams);
								if ( is_object($physres) ){
									$physres = $physres->result('array');
									if(count($physres)){
										foreach($physres[0] as $k=>$v) {
											$dbl[$k] = $v;
										}
									}
								}
							}
						}
						$dbl['ARMId'] = $arms[$dbl['ARMType']]['Arm_id']; // Ид АРМа
						$dbl['ARMType_id'] = $arms[$dbl['ARMType']]['ARMType_id']; // Название АРМа
						$dbl['ARMName'] = $arm_name; // Название АРМа
						$dbl['ARMNameLpu'] = $dbl['ARMName'].'<div style="color:#666;">'.$dbl['Lpu_Nick'].'&nbsp;</div>'; // Название АРМа + ЛПУ
						$dbl['ARMForm'] = $arms[$dbl['ARMType']]['Arm_Form']; // Форма АРМа
						$dbl['client'] = $arms[$dbl['ARMType']]['client']; // Тип клиента
                        $dbl['ShowMainMenu'] = $arms[$dbl['ARMType']]['ShowMainMenu'];
					}
					// Место работы одной строкой: подразделение и отделение
					$dbl['Name'] = ((!empty($dbl['LpuUnit_Name']))?'<div>'.$dbl['LpuUnit_Name'].'</div>':'').((!empty($dbl['LpuSection_Name']))?'<div>'.$dbl['LpuSection_Name'].'</div>':'');
					if ($dbl['MedService_id']>0) { // Если служба
						$dbl['Name'] = $dbl['Name'].'<div style="color:darkblue;">'.$dbl['MedService_Name'].'&nbsp;</div>'.(empty($dbl['Name'])?'<br/>':'');
					}
					$dbl['id'] = $dbl['ARMType'].'_'.$dbl['Lpu_id'].'_'.$dbl['MedStaffFact_id'].'_'.$dbl['LpuSection_id'].'_'.$dbl['LpuSectionProfile_id'].'_'.$dbl['MedService_id'];
					if ($k>0) {	// Если больше первой записи
						$doubles[] = $dbl;
					} else { // Иначе (первая запись)
						$response[$i] = $dbl;
						/*
						// Определяем по типу допполя АРМов (наименования)
						if (isset($arms[$response[$i]['ARMType']])) { // Если тип АРМа описан
							$response[$i]['ARMName'] = $arms[$response[$i]['ARMType']]['Arm_Name']; // Название АРМа
							$response[$i]['ARMNameLpu'] = $response[$i]['ARMName'].'<div style="color:#666;">'.$response[$i]['Lpu_Nick'].'&nbsp;</div>'; // Название АРМа + ЛПУ
							$response[$i]['ARMForm'] = $arms[$response[$i]['ARMType']]['Arm_Form']; // Форма АРМа
						}
						// Место работы одной строкой: подразделение и отделение
						$response[$i]['Name'] = ((!empty($response[$i]['LpuUnit_Name']))?'<div>'.$response[$i]['LpuUnit_Name'].'</div>':'').((!empty($response[$i]['LpuSection_Name']))?'<div>'.$response[$i]['LpuSection_Name'].'</div>':'');
						if ($response[$i]['MedService_id']>0) { // Если служба
							$response[$i]['Name'] = $response[$i]['Name'].'<div style="color:darkblue;">'.$response[$i]['MedService_Name'].'&nbsp;</div>'.(empty($response[$i]['Name'])?'<br/>':'');
						}*/
					}
				}
			}

			// Если были дубли, то надо их включить в список
			foreach($doubles as $k=>$v) {
				$response[] = $v;
			}
			//print_r($response); exit();
			// TODO: И возможно нужна сортировка
		}

		$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = $this->config->item('LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS');
		$ALLOW_EXTJS6_ARMS_FOR_ALL = $this->config->item('ALLOW_EXTJS6_ARMS_FOR_ALL');

		if ( !is_array($LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS) ) {
			$LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS = array();
		}

		// Добавляем армы которые не привязаны ни к месту работы, ни к службе
		// Это армы администратора, администратора ЛПУ и прочие
		foreach($arms as $k=>$v) {
			if (in_array($k, array('callcenter')) && !empty($data['session']['lpu_id']) && $_SESSION['region']['nick'] != 'saratov') {
				if (havingGroup(array('CallCenterAdmin', 'OperatorCallCenter'), $data['Groups'])) {
					$r = $this->getOtherARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Lpu_id'].'____';
                    $r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if ($k == 'regpolprivate6') {

				if (havingGroup('PrivateReg', $data['Groups'])) {

					$r = $this->getOtherOrgARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'] . '<div style="color:#666;">' . $r['Org_Nick'] . '&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k . '_' . $r['Org_id'] . '____';
					$r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if ( ($k == 'smo' && havingGroup('SMOUser', $data['Groups'])) || ($k == 'tfoms' && havingGroup('TFOMSUser', $data['Groups'])) ) {
				$r = $this->getOtherOrgARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Org_id'].'____';
                $r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			else if($k=='hn'){
				if( !empty($data['MedPersonal_id']) ) {
					$hnp = $this->getHeadNursePost($data);
					if( is_array($hnp) ) {
						$r = $this->getOtherOrgARMList($data);
						$r['ARMType'] = $k;
						$r['ARMType_id'] = $v['ARMType_id'];
						$r['ARMName'] = $v['Arm_Name'];
						$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
						$r['ARMForm'] = $v['Arm_Form'];
						$r['client'] = $v['client'];
						$r['id'] = $k.'_'.$r['Org_id'].'____';
						$r['ShowMainMenu'] = $v['ShowMainMenu'];
						$response[] = $r;
					}
				}
			}
			else if ( $k == 'zags' && havingGroup('ZagsUser', $data['Groups']) ) {
				$r = $this->getOtherOrgARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Org_id'].'____';
                $r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			//Специалист МЗ
			else if ( $k == 'spec_mz' && havingGroup('OuzSpec', $data['Groups']) ) {
				if($data['session']['orgtype'] == 'touz'  || $_SESSION['region']['nick'] <> 'perm')
				{
					$r = $this->getOtherOrgARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Org_id'].'____';
					$r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if (in_array($k, array('lpuadmin', 'superadmin', 'epidem', 'pm', 'miac')) && !empty($data['session']['lpu_id'])) {
				if (
					havingGroup($k, $data['Groups'])
					|| ($k == 'epidem' && havingGroup('epidem_ufa', $data['Groups']))
					|| ($k == 'miac' && havingGroup(['miacstat', 'miacmonitoring', 'miacsysadmin', 'miacsuperadmin', 'miacadminfrmr'], $data['Groups']))
				) {
					$r = $this->getOtherARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					if ($k == 'miac') {
						switch(true) {
							case havingGroup('miacsuperadmin'):
								$r['ARMName'] .= ' (Супер администратор)';
								break;
							case havingGroup('miacsysadmin'):
								$r['ARMName'] .= ' (Системный администратор ЦОД)';
								break;
							case havingGroup('miacmonitoring'):
								$r['ARMName'] .= ' (Мониторинг)';
								break;
							case havingGroup('miacstat'):
								$r['ARMName'] .= ' (Статистик)';
								break;
							case havingGroup('miacadminfrmr'):
								$r['ARMName'] .= ' (Администратор ФРМР, ФРМО)';
								break;
						}
					}
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Lpu_id'].'____';
                    $r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if (in_array($k, array('lpuadmin6')) && !empty($data['session']['lpu_id'])) {
				if ((!empty($ALLOW_EXTJS6_ARMS_FOR_ALL) || array_key_exists($data['session']['lpu_id'], $LPU_LIST_WITH_ALLOWED_EXTJS6_ARMS)) && havingGroup('lpuadmin', $data['Groups'])) {
					$r = $this->getOtherARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Lpu_id'].'____';
                    $r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if (in_array($k, array('zakup')) && !empty($data['session']['lpu_id'])) {
				if (havingGroup($k, $data['Groups']) || ($k == 'zakup' && havingGroup('zakup', $data['Groups']))) {
					$r = $this->getOtherARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Lpu_id'].'____';
                    $r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if (in_array($k, array('orgadmin')) && !empty($data['session']['org_id'])) {
				if (havingGroup($k, $data['Groups'])) {
					$r = $this->getOtherOrgARMList($data);
					$r['ARMType'] = $k;
					$r['ARMType_id'] = $v['ARMType_id'];
					$r['ARMName'] = $v['Arm_Name'];
					$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
					$r['ARMForm'] = $v['Arm_Form'];
					$r['client'] = $v['client'];
					$r['id'] = $k.'_'.$r['Org_id'].'____';
                    $r['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $r;
				}
			}
			else if ( in_array($k, array('polkallo')) && havingGroup('OperLLO', $data['Groups']) ) {
				$llo = false;
				if( !empty($data['MedPersonal_id']) ) {
					$llo = $this->getLLOData($data);
				}
				if( is_array($llo) ) {
					$llo['ARMType'] = $k;
					$llo['ARMType_id'] = $v['ARMType_id'];
					$llo['ARMName'] = $v['Arm_Name'];
					$llo['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$llo['Lpu_Nick'].'&nbsp;</div>';
					$llo['ARMForm'] = $v['Arm_Form'];
					$llo['client'] = $v['client'];
					$llo['id'] = $k.'_'.$llo['Lpu_id'].'____';
					$llo['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $llo;
				}
				elseif ( !empty($data['session']['lpu_id'])) {
					$llo = $this->getOtherARMList($data);
					$llo['ARMType'] = $k;
					$llo['ARMType_id'] = $v['ARMType_id'];
					$llo['ARMName'] = $v['Arm_Name'];
					$llo['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$llo['Lpu_Nick'].'&nbsp;</div>';
					$llo['ARMForm'] = $v['Arm_Form'];
					$llo['client'] = $v['client'];
					$llo['id'] = $k.'_'.$llo['Lpu_id'].'____';
					$llo['ShowMainMenu'] = $v['ShowMainMenu'];
					$response[] = $llo;
				}
			}
            else if ( $k == 'mzchieffreelancer' && $this->isHeadMedSpecMedPersonal() ) {
                $r = $this->getOtherOrgARMList($data);
                $r['ARMType'] = $k;
                $r['ARMType_id'] = $v['ARMType_id'];
                $r['ARMName'] = $v['Arm_Name'];
                $r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Org_Nick'].'&nbsp;</div>';
                $r['ARMForm'] = $v['Arm_Form'];
                $r['client'] = $v['client'];
                $r['id'] = $k.'_'.$r['Org_id'].'____';
                $r['ShowMainMenu'] = $v['ShowMainMenu'];
                $response[] = $r;
            }
			else if (in_array($k, array('lpupharmacyhead')) && !empty($data['session']['lpu_id']) && havingGroup('zavapt', $data['Groups'])) {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
                $r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			else if($k == 'communic' && havingGroup('Communic',$data['Groups'])) {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
 			}
			else if($k == 'dispcallnmp' && havingGroup('DispCallNMP',$data['Groups'])) {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			else if($k == 'dispdirnmp' && havingGroup('DispDirNMP',$data['Groups'])) {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			else if($k == 'nmpgranddoc' && havingGroup('NMPGrandDoc',$data['Groups'])) {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
 			} else if(in_array($k, array('lpuuser', 'lpuuser6')) && havingGroup('LpuUser',$data['Groups']) && havingGroup('RegistryUserReadOnly',$data['Groups']) && $_SESSION['region']['nick'] == 'yaroslavl') {
				$r = $this->getOtherARMList($data);
				$r['ARMType'] = $k;
				$r['ARMType_id'] = $v['ARMType_id'];
				$r['ARMName'] = $v['Arm_Name'];
				$r['ARMNameLpu'] = $v['Arm_Name'].'<div style="color:#666;">'.$r['Lpu_Nick'].'&nbsp;</div>';
				$r['ARMForm'] = $v['Arm_Form'];
				$r['client'] = $v['client'];
				$r['id'] = $k.'_'.$r['Lpu_id'].'____';
				$r['ShowMainMenu'] = $v['ShowMainMenu'];
				$response[] = $r;
			}
			
		}

		$IsMainServer = $this->config->item('IsMainServer');
		$IsSMPServer = $this->config->item('IsSMPServer');

		$armsByType = array();

		foreach ( $response as $arm ) {
			if ($IsSMPServer === true) {
				$arm['ShowMainMenu'] = 1; // На сервере СМП меню для всех АРМ скрыто
			}

			if ( !array_key_exists($arm['ARMType'], $armsByType) ) {
				$armsByType[$arm['ARMType']] = array();
			}

			$armsByType[$arm['ARMType']][] = $arm;
		}

		$response = array();
		$smpArms = array('smpadmin', 'smpdispatchcall', 'smpdispatchdirect', 'smpdispatchstation', 'smpheadduty', 'smpheadbrig', 'smpheaddoctor', 'zmk', 'smpinteractivemap');
		$additionalSMPArms = array();
		if($this->config->item('ADDITIONAL_SMP_ARMS') && is_array($this->config->item('ADDITIONAL_SMP_ARMS'))){
			$additionalSMPArms = $this->config->item('ADDITIONAL_SMP_ARMS');
		}

		$excArms = array();
		$DISABLED_ARMS = $this->config->item('DISABLED_ARMS');
		$DISABLED_ARMS_EXCEPT_LPU = $this->config->item('DISABLED_ARMS_EXCEPT_LPU');
		if (
			!empty($DISABLED_ARMS)
			&& (
				empty($DISABLED_ARMS_EXCEPT_LPU)
				|| !is_array($DISABLED_ARMS_EXCEPT_LPU)
				|| empty($data['session']['lpu_id'])
				|| !in_array($data['session']['lpu_id'], $DISABLED_ARMS_EXCEPT_LPU)
			)
		) {
			$excArms = array_merge($excArms, $DISABLED_ARMS); // отключаем АРМы, указанные в конифге
		}

		foreach ( $armsByType as $key => $arm ) {
			if (
				isset($data['Need_all'])
				|| (
					($IsSMPServer !== true || in_array($key, array_merge($smpArms, array('superadmin', 'communic'), $additionalSMPArms)))
					&& ($IsMainServer !== true || !in_array($key, $smpArms))
					&& (!in_array($key, $excArms))
				)
			) {
				$response = array_merge($response, $arm);
			}
		}

		return $response;
	}

	/**
	 * Получение списка всевозможных АРМов
	 */
	function loadARMList() {
		$region_nick = getRegionNick();
		//Закомментировал, ибо неактуально и вообще странно выглядело применительно к swWorkPlaceSMPDispatcherCallWindow, который сейчас есть только в Ext4
		//$client_ext_ver = in_array($region_nick, array('perm', 'pskov', 'astra', 'ufa', 'krym', 'kz', 'ekb', 'kareliya')) ? 'ext4' : 'ext2';

		$clientExt6 = 'ext6';
		$useExt6Only = $this->config->item('USE_EXTJS6_ONLY');
		if (!empty($useExt6Only)) {
			$clientExt6 = 'ext6only';
		}

		// в БД есть таблица ARMType, возможно стоит начать её использовать? todo
		return array(
			'common'=>array('Arm_id' => 1, 'Arm_Name' => 'АРМ врача поликлиники', 'Arm_Form' => 'swMPWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'stom'=>array('Arm_id' => 2, 'Arm_Name' => 'АРМ стоматолога', 'Arm_Form' => 'swWorkPlaceStomWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'stac'=>array('Arm_id' => 3, 'Arm_Name' => 'АРМ врача стационара', 'Arm_Form' => 'swMPWorkPlaceStacWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'stacpriem'=>array('Arm_id' => 4, 'Arm_Name' => 'АРМ врача приемного отделения', 'Arm_Form' => 'swMPWorkPlacePriemWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'stacnurse'=>array('Arm_id' => 5, 'Arm_Name' => 'АРМ постовой медсестры', 'Arm_Form' => 'swEvnPrescrJournalWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			//'par'=>array('Arm_id' => 6, 'Arm_Name' => 'АРМ врача параклиники', 'Arm_Form' => 'swMPWorkPlaceParWindow', 'client'=>'ext2'),
			// Службы
			'microbiolab'=>array('Arm_id' => 1050, 'Arm_Name' => 'АРМ Бактериолога', 'Arm_Form' => 'swBacteriologistWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lab'=>array('Arm_id' => 7, 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swAssistantWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'vk'=>array('Arm_id' => 8, 'Arm_Name' => 'АРМ врача ВК', 'Arm_Form' => 'swVKWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'mse'=>array('Arm_id' => 9, 'Arm_Name' => 'АРМ МСЭ', 'Arm_Form' => 'swMseWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'polkallo'=>array('Arm_id' => 10, 'Arm_Name' => 'АРМ врача ЛЛО поликлиники', 'Arm_Form' => 'swWorkPlacePolkaLLOWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'pzm'=>array('Arm_id' => 11, 'Arm_Name' => 'АРМ сотрудника пункта забора биоматериала', 'Arm_Form' => 'swAssistantWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'func'=>array('Arm_id' => 12, 'Arm_Name' => 'АРМ диагностики', 'Arm_Form' => 'swWorkPlaceFuncDiagWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'superadmin'=>array('Arm_id' => 13, 'Arm_Name' => 'АРМ администратора ЦОД', 'Arm_Form' => 'swAdminWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpuadmin'=>array('Arm_id' => 14, 'Arm_Name' => 'АРМ администратора МО', 'Arm_Form' => 'swLpuAdminWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'orgadmin'=>array('Arm_id' => 14, 'Arm_Name' => 'АРМ администратора организации', 'Arm_Form' => 'swOrgAdminWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'htm'=>array('Arm_id' => 61, 'Arm_Name' => 'АРМ ВМП', 'Arm_Form' => 'swHTMWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			// добавить армы
			'patb'=>array('Arm_id' => 16, 'Arm_Name' => 'АРМ патологоанатома', 'Arm_Form' => 'swWorkPlacePathoMorphologyWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'merch'=>array('Arm_id' => 17, 'Arm_Name' => 'АРМ товароведа', 'Arm_Form' => 'swWorkPlaceMerchandiserWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'dpoint'=>array('Arm_id' => 18, 'Arm_Name' => 'АРМ провизора', 'Arm_Form' => 'swWorkPlaceDistributionPointWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'ooa'=>array('Arm_id' => 18, 'Arm_Name' => 'АРМ провизора общего отдела', 'Arm_Form' => 'swWorkPlaceDistributionPointCommonWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpucadrview'=>array('Arm_id' => 19, 'Arm_Name' => 'АРМ специалиста отдела кадров', 'Arm_Form' => 'swWorkPlaceHRWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'mstat'=>array('Arm_id' => 20, 'Arm_Name' => 'АРМ медицинского статистика', 'Arm_Form' => 'swWorkPlaceMedStatWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'prock'=>array('Arm_id' => 21, 'Arm_Name' => 'АРМ медсестры процедурного кабинета', 'Arm_Form' => 'swWorkPlaceProcCabinetWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'stachelpdesc'=>array('Arm_id' => 22, 'Arm_Name' => 'АРМ сотрудника справочного стола стационара', 'Arm_Form' => 'swWorkPlaceStacHelpDeskWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			//'smpvr'=>array('Arm_id' => 23, 'Arm_Name' => 'АРМ врача СМП', 'Arm_Form' => 'swWorkPlaceSMPWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'smpreg'=>array('Arm_id' => 24, 'Arm_Name' => 'АРМ оператора СМП', 'Arm_Form' => 'swWorkPlaceSMPWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'regpol'=>array('Arm_id' => 25, 'Arm_Name' => 'АРМ регистратора поликлиники', 'Arm_Form' => 'swWorkPlacePolkaRegWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'callcenter'=>array('Arm_id' => 26, 'Arm_Name' => 'АРМ оператора call-центра', 'Arm_Form' => 'swWorkPlaceCallCenterWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'sprst'=>array('Arm_id' => 27, 'Arm_Name' => 'АРМ сотрудника справочного стола стационара', 'Arm_Form' => 'swWorkPlaceStacHelpDeskWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpucadradmin'=>array('Arm_id' => 28, 'Arm_Name' => 'АРМ специалиста отдела кадров/администратора', 'Arm_Form' => 'swWorkPlaceHRWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'), // временно
			'minzdravdlo'=>array('Arm_id' => 29, 'Arm_Name' => 'АРМ специалиста ЛЛО ОУЗ', 'Arm_Form' => 'swWorkPlaceMinzdravDLOWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'slneotl'=>array('Arm_id' => 30, 'Arm_Name' => 'АРМ оператора НМП', 'Arm_Form' => 'swWorkPlacePPDWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'dispnmp'=>array('Arm_id' => 142, 'Arm_Name' => 'АРМ диспетчера НМП', 'Arm_Form' => 'swWorkPlaceSMPDispatcherStationWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'smpdispatchdirect'=>array('Arm_id' => 31, 'Arm_Name' => 'АРМ диспетчера направлений СМП', 'Arm_Form' => 'swWorkPlaceSMPDispatcherDirectWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'smpdispatchcall'=>array('Arm_id' => 32, 'Arm_Name' => 'АРМ диспетчера по приёму вызовов', 'Arm_Form' => 'swWorkPlaceSMPDispatcherCallWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'dispcallnmp'=>array('Arm_id' => 140, 'Arm_Name' => 'АРМ диспетчера по приёму вызовов НМП', 'Arm_Form' => 'swWorkPlaceSMPDispatcherCallWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'smpheadduty'=>array('Arm_id' => 33, 'Arm_Name' => 'АРМ старшего смены СМП', 'Arm_Form' => 'swWorkPlaceSMPHeadDutyWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'smpheadbrig'=>array('Arm_id' => 34, 'Arm_Name' => 'АРМ старшего бригады СМП', 'Arm_Form' => 'swWorkPlaceSMPHeadBrigWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'leadermo'=>array('Arm_id' => 35, 'Arm_Name' => 'АРМ руководителя МО', 'Arm_Form' => 'swWorkPlaceLeaderMOWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'mekllo'=>array('Arm_id' => 36, 'Arm_Name' => 'АРМ МЭК ЛЛО', 'Arm_Form' => 'swWorkPlaceMEKLLOWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'spesexpertllo'=>array('Arm_id' => 93, 'Arm_Name' => 'АРМ специалиста по экспертизе ЛЛО', 'Arm_Form' => 'swWorkPlaceSpecMEKLLOWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'adminllo'=>array('Arm_id' => 94, 'Arm_Name' => $region_nick == 'msk' ? 'АРМ сотрудника ситуационного центра по ЛЛО' : 'АРМ администратора ЛЛО', 'Arm_Form' => 'swWorkPlaceAdminLLOWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'smpadmin'=>array('Arm_id' => 37, 'Arm_Name' => 'АРМ администратора СМП', 'Arm_Form' => 'swWorkPlaceSMPAdminWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'touz'=>array('Arm_id' => 38, 'Arm_Name' => 'АРМ специалиста ТОУЗ', 'Arm_Form' => 'swWorkPlaceTOUZWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'reglab'=>array('Arm_id' => 41, 'Arm_Name' => 'АРМ регистрационной службы лаборатории', 'Arm_Form' => 'swAssistantWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'konsult'=>array('Arm_id' => 39, 'Arm_Name' => 'АРМ сотрудника службы консультативного приема', 'Arm_Form' => 'swWorkPlaceConsultPriemWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'foodserv'=>array('Arm_id' => 40, 'Arm_Name' => 'АРМ сотрудника пищеблока', 'Arm_Form' => 'swCookWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'headnurse'=>array('Arm_id' => 137, 'Arm_Name' => 'АРМ старшей медсестры', 'Arm_Form' => 'swHeadNurseWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'tfoms'=>array('Arm_id' => 138, 'Arm_Name' => 'АРМ пользователя ТФОМС', 'Arm_Form' => 'swWorkPlaceTFOMSWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'smo'=>array('Arm_id' => 139, 'Arm_Name' => 'АРМ пользователя СМО', 'Arm_Form' => 'swWorkPlaceSMOWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'vac'=>array('Arm_id' => 51, 'Arm_Name' => 'АРМ медсестры кабинета вакцинации', 'Arm_Form' => 'amm_WorkPlaceVacCabinetWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'epidem'=>array('Arm_id' => 52, 'Arm_Name' => 'АРМ эпидемиолога', 'Arm_Form' => 'amm_WorkPlaceEpidemWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'remoteconsultcenter'=>array('Arm_id' => 53, 'Arm_Name' => 'АРМ сотрудника центра удалённой консультации', 'Arm_Form' => 'swWorkPlaceTelemedWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'epidem_mo'=>array('Arm_id' => 54, 'Arm_Name' => 'АРМ эпидемиолога МО', 'Arm_Form' => 'amm_WorkPlaceEpidemWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'zags'=>array('Arm_id' => 55, 'Arm_Name' => 'АРМ сотрудника ЗАГС', 'Arm_Form' => 'swZagsWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'smpdispatchstation'=>array('Arm_id' => 56, 'Arm_Name' => 'АРМ диспетчера подстанции СМП', 'Arm_Form' => 'swWorkPlaceSMPDispatcherStationWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'dispdirnmp'=>array('Arm_id' => 141, 'Arm_Name' => 'АРМ диспетчера направлений НМП', 'Arm_Form' => 'swWorkPlaceSMPDispatcherStationWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'nmpgranddoc'=>array('Arm_id' => 143, 'Arm_Name' => 'АРМ старшего врача НМП', 'Arm_Form' => 'swWorkPlaceSMPHeadDoctorWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'operblock'=>array('Arm_id' => 57, 'Arm_Name' => 'АРМ сотрудника оперблока', 'Arm_Form' => 'swWorkPlaceOperBlockWindow', 'client'=>'ext6', 'ShowMainMenu' => '2'),
			'smpheaddoctor'=>array('Arm_id' => 58, 'Arm_Name' => 'АРМ старшего врача СМП', 'Arm_Form' => 'swWorkPlaceSMPHeadDoctorWindow', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'pmllo'=>array('Arm_id' => 59, 'Arm_Name' => 'АРМ поставщика', 'Arm_Form' => 'swWorkPlaceSupplierWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'zmk'=>array('Arm_id' => 63, 'Arm_Name' => $region_nick=='ufa'?'Единый диспетчерский центр СМП и АБ – мониторинг СМП' : 'АРМ Центра медицины катастроф', 'Arm_Form' => 'swWorkPlaceCenterDisasterMedicineWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'spec_mz'=>array('Arm_id' => 111, 'Arm_Name' => 'АРМ специалиста Минздрава', 'Arm_Form' => 'swWorkPlaceMZSpecWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'communic'=> array('Arm_id' => 112, 'Arm_Name' => 'АРМ специалиста МИРС', 'Arm_Form' => 'swWorkPlaceCommunicWindow', 'client' => 'ext2', 'ShowMainMenu' => '1'),
			'phys'=>array('Arm_id' => 250, 'Arm_Name' => 'АРМ врача физиотерапевта', 'Arm_Form' => 'swWorkPlacePhysWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),

			// Блок армов БСМЭ
			//АРМы службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
			'forenbiodprtwithmolgenlabbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+1), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenBioSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenbiodprtwithmolgenlabbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+2), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenbiodprtwithmolgenlabbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+3), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swBSMEForenBioExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenbiodprtwithmolgenlabbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+4), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swBSMEForenBioDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Судебно-химическое отделение"
			'forenchemdprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+5), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenChemSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenchemdprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+6), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenchemdprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+7), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenchemdprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+8), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swBSMEForenChemDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Медико-криминалистическое отделение"
			'medforendprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+9), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenCrimSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'medforendprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+10), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'medforendprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+11), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'medforendprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+12), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swBSMEForenCrimDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Судебно-гистологическое отделение"
			'forenhistdprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+13), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenhistdprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+14), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenhistdprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+15), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '12'),
			'forenhistdprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+16), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swBSMEForenHistDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Отдел организационно-методический"
			'organmethdprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+17), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'organmethdprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+18), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'organmethdprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+19), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'organmethdprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+20), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swDefaultDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Отдел судебно-медицинской экспертизы трупов с судебно-гистологическим отделением"
			'forenmedcorpsexpdprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+21), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenCorpSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedcorpsexpdprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+22), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedcorpsexpdprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+23), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedcorpsexpdprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+24), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swDefaultDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Отдел судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
			'forenmedexppersdprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+25), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenPersSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedexppersdprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+26), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedexppersdprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+27), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swBSMEForenPersExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedexppersdprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+28), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swBSMEForenPersDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Отдел комиссионных и комплексных экспертиз"
			'commcomplexpbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+29), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenComplexSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'commcomplexpbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+30), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'commcomplexpbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+31), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'commcomplexpbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+32), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swDefaultDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//АРМы службы "Районное отделение БСМЭ"
			'forenareadprtbsmesecretary'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+33), 'Arm_Name' => 'АРМ регистратора БСМЭ', 'Arm_Form' => 'swBSMEForenAreaDprtSecretaryWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenareadprtbsmehead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+34), 'Arm_Name' => 'АРМ руководителя БСМЭ', 'Arm_Form' => 'swBSMEDefaultWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenareadprtbsmeexpert'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+35), 'Arm_Name' => 'АРМ эксперта', 'Arm_Form' => 'swDefaultExpertWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenareadprtbsmedprthead'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+36), 'Arm_Name' => 'АРМ заведующего отделением БСМЭ', 'Arm_Form' => 'swDefaultDprtHeadWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			// АРМ Лаборанта БСМЭ (закомментированные армы в резерве)
			//'forenbiodprtwithmolgenlabbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+37), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swBSMEForenBioExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'forenchemdprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+38), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'medforendprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+39), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'forenhistdprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+40), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '12'),
			//'organmethdprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+41), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'forenmedcorpsexpdprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+42), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'forenmedexppersdprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+43), 'Arm_Name' => 'АРМ лаборанта БСМЭ', 'Arm_Form' => 'swBSMEForenPersExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'commcomplexpbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+44), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			//'forenareadprtbsmeexpertassistant'=>array('Arm_id' => (self::BSME_ARM_ID_PREF+45), 'Arm_Name' => 'АРМ лаборанта', 'Arm_Form' => 'swDefaultExpertAssistantWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'zakup'=>array('Arm_id' => 60, 'Arm_Name' => ($_SESSION['region']['nick'] == 'ufa') ? 'АРМ специалиста ГКУ' : 'АРМ специалиста по закупам', 'Arm_Form' => 'swWorkPlaceGKUWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			// Конец  блока армов БСМЭ
			'lvn'=>array('Arm_id' => 95, 'Arm_Name' => 'АРМ регистратора ЛВН', 'Arm_Form' => 'swWorkPlaceEvnStickRegWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'hn'=>array('Arm_id' => 96, 'Arm_Name' => 'АРМ главной медсестры МО', 'Arm_Form' => 'swWorkPlaceHeadNurseWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'mzchieffreelancer'=>array('Arm_id' => 97, 'Arm_Name' => 'АРМ главного внештатного специалиста при МЗ', 'Arm_Form' => 'swWorkPlaceMinzdravChiefFreelancerWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpupharmacyhead'=>array('Arm_id' => 99, 'Arm_Name' => 'АРМ заведующего аптекой МО', 'Arm_Form' => 'swWorkPlaceLpuPharmacyHeadWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'paidservice'=>array('Arm_id' => 100, 'Arm_Name' => 'АРМ администратора платных услуг', 'Arm_Form' => 'swPaidServiceWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'polka'=>array('Arm_id' => 101, 'Arm_Name' => 'АРМ врача поликлиники (ExtJS 6)', 'Arm_Form' => 'swWorkPlacePolkaWindow', 'client'=>$clientExt6, 'ShowMainMenu' => '1'),
			'stom6'=>array('Arm_id' => 104, 'Arm_Name' => 'АРМ стоматолога (ExtJS 6)', 'Arm_Form' => 'swWorkPlaceStomWindowExt6', 'client'=>$clientExt6, 'ShowMainMenu' => '1'),
			'lpuadmin6'=>array('Arm_id' => 105, 'Arm_Name' => 'АРМ администратора МО (ExtJS 6)', 'Arm_Form' => 'swLpuAdminWorkPlaceWindowExt6', 'client'=>$clientExt6, 'ShowMainMenu' => '1'),
			'regpol6'=>array('Arm_id' => 106, 'Arm_Name' => 'АРМ регистратора поликлиники (ExtJS 6)', 'Arm_Form' => 'swWorkPlacePolkaRegWindowExt6', 'client'=>$clientExt6, 'ShowMainMenu' => '1'),
			'pm'=>array('Arm_id' => 107, 'Arm_Name' => 'АРМ менеджера проекта', 'Arm_Form' => 'swPMWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'miac'=>array('Arm_id' => 108, 'Arm_Name' => 'АРМ сотрудника МИАЦ', 'Arm_Form' => 'swMiacWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '1'),
			'smpinteractivemap'=>array('Arm_id' => 102, 'Arm_Name' => 'АРМ интерактивной карты', 'Arm_Form' => 'swInteractiveMapWorkPlace', 'client'=>'ext4', 'ShowMainMenu' => '2'),
			'profosmotr'=>array('Arm_id' => 103, 'Arm_Name' => 'АРМ профилактического осмотра', 'Arm_Form' => 'swProfServiceWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'reanimation'=>array('Arm_id' => 127, 'Arm_Name' => 'АРМ врача реаниматолога', 'Arm_Form' => 'swMPWorkPlaceStacWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),    //BOB - 03.02.2017
			'lpuuser'=>array('Arm_id' => 253, 'Arm_Name' => 'АРМ пользователя МО', 'Arm_Form' => 'swUserWorkPlaceWindow', 'client'=>'ext2', 'ShowMainMenu' => '2'),
			'lpuuser6'=>array('Arm_id' => 254, 'Arm_Name' => 'АРМ пользователя МО (ExtJS 6)', 'Arm_Form' => 'swLpuUserWorkPlaceWindowExt6', 'client'=>'ext6', 'ShowMainMenu' => '2')
		);
	}

	/**
	 * Функция возвращает список всех имеющихся в системе армов
	 * (пока хранится непосредственно в модели)
	 */
	function getARMList() {
		// todo: Надо что-то поменять (оптимизировать, может перенести в другое место, может в настройки или в БД и кешировать) в этой процедуре ...
		$sp = getSessionParams();

		$ARMList = $this->loadARMList();

		$_REGION = $this->config->item($_SESSION['region']['nick']);

		if ( isset($_REGION['DENIED_ARM_TYPES']) && is_array($_REGION['DENIED_ARM_TYPES']) && count($_REGION['DENIED_ARM_TYPES']) > 0 ) {
			foreach ( $_REGION['DENIED_ARM_TYPES'] as $ARMType ) {
				if ( array_key_exists($ARMType, $ARMList) ) {
					unset($ARMList[$ARMType]);
				}
			}
		}

		if ( isset($_REGION['ALLOWED_ARM_TYPES']) && is_array($_REGION['ALLOWED_ARM_TYPES']) && count($_REGION['ALLOWED_ARM_TYPES']) > 0 ) {
			foreach ( $ARMList as $ARMType => $ARM ) {
				if ( !in_array($ARMType, $_REGION['ALLOWED_ARM_TYPES']) ) {
					unset($ARMList[$ARMType]);
				}
			}
		}

		$ids = array();
		$this->load->library('swCache', [], 'swcache');
		$ARMTypeList = $this->swcache->get('ARMType');

		//Если в кэше нет ARMType, то запрашиваем из базы
		if (!is_array($ARMTypeList) || count($ARMTypeList) == 0) {
			$ARMTypeList = $this->getARMTypeList();
		}

		if (is_array($ARMTypeList)) {
			foreach($ARMTypeList as $item) {
				$ids[$item['ARMType_Code']] = $item['ARMType_id'];
			}
		}
		foreach($ARMList as $index => &$item) {
			$item['ARMType_id'] = isset($ids[$item['Arm_id']]) ? $ids[$item['Arm_id']] : null;
		}

		return $ARMList;
	}

	/**
	 * Возвращает список типов АРМ
	 */
	function getARMTypeList() {
		$query = "
			select
				ARMType_id,
				ARMType_Code,
				ARMType_Name
			from v_ARMType with(nolock)
		";

		return $this->queryResult($query);
	}

	/**
	 * Возвращает список типов АРМ для комбо на основании массива в методе loadARMList
	 */
	public function getPHPARMTypeList($data) {
		$ARMList = $this->loadARMList();

		$ARMList['_noarm_'] = array(
			'Arm_id' => -1,
			'Arm_Name' => 'Пользователи, работающие без АРМ'
		);

		$response = array();

		foreach ( $ARMList as $key => $array ) {
			if (
				empty($data['query'])
				|| mb_stripos($array['Arm_Name'], $data['query']) !== false
			) {
				$response[] = array(
					'ARMType_id' => $array['Arm_id'],
					'ARMType_Code' => $array['Arm_id'],
					'ARMType_Name' => $array['Arm_Name'],
					'ARMType_SysNick' => $key,
				);
			}
		}

		return $response;
	}

    /**
     * Это Doc-блок
     */
	function getLpuIdFromLpuNick($lpu_nick)
	{
		$sql = "SELECT Lpu_id FROM v_Lpu with(nolock) where Lpu_Nick like '%{$lpu_nick}%' ";
		$res = $this->db->query($sql);
		if ( is_object($res) )
		{
 	    	$rs = $res->result('array');
			if ( isset($rs[0]) )
			{
				return $rs[0]['Lpu_id'];
			}
			else
				return false;
		}
 	    else
 	    	return false;
	}
    /**
     * Это Doc-блок
     */
	function getLpuNickFromLpuId($lpu_id)
	{
		if (!empty($lpu_id)) {
			$sql = "SELECT Lpu_Nick FROM v_Lpu with(nolock) where Lpu_id = {$lpu_id}";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$rs = $res->result('array');
				if ( isset($rs[0]) )
				{
					return $rs[0]['Lpu_Nick'];
				}
				else
					return false;
			}
		}
 	    return false;
	}
    /**
     * Это Doc-блок
     */
	function getOrgNickFromOrgId($org_id) {
		$orgNick = '';

		if ( !empty($org_id) ) {
			$sql = "SELECT top 1 ISNULL(Org_Nick, Org_Name) as Lpu_Nick FROM v_Org with (nolock) where Org_id = {$org_id}";
			$res = $this->db->query($sql);

			if ( is_object($res) ) {
				$rs = $res->result('array');

				if ( is_array($rs) && count($rs) == 1 ) {
					$orgNick = $rs[0]['Lpu_Nick'];
				}
			}
		}

 	    return $orgNick;
	}
    /**
     * Это Doc-блок
     */
	function getLpuIdFromLpuName($lpu_name)
	{
		$sql = "SELECT Lpu_id FROM v_Lpu with(nolock) where Lpu_Nick like '%{$lpu_name}%' ";
		$res = $this->db->query($sql);
		if ( is_object($res) )
		{
 	    	$rs = $res->result('array');
			if ( isset($rs[0]) )
			{
				return $rs[0]['Lpu_id'];
			}
			else
				return false;
		}
 	    else
 	    	return false;
	}

	/**
	 * Возвращает список типов организаций
	 */
	function getOrgTypeTree($data) {
		$this->load->library('swCache');
		if ($resCache = $this->swcache->get("getOrgTypeTree")) {
			return $resCache;
		}

		$query = "
			SELECT
				ot.OrgType_id,
				ot.OrgType_Name
			FROM
				v_OrgType ot with (nolock)
			WHERE
				exists(
					select top 1
						o.Org_id
					from
						v_Org o with (nolock)
					where
						o.OrgType_id = ot.OrgType_id
						and o.Org_isAccess = 2
				)
				
			union
			
			SELECT
				ot.OrgType_id,
				ot.OrgType_Name
			FROM
				v_OrgType ot with (nolock)
			WHERE
				exists(
					select top 1
						o.Org_id
					from
						v_Org o with (nolock)
						inner join v_pmUserCacheOrg puco with (nolock) on puco.Org_id = o.Org_id
					where
						o.OrgType_id = ot.OrgType_id
				)
				
			ORDER BY
				OrgType_Name
		";

		$resp = $this->queryResult($query);
		if (!empty($resp)) {
			$this->swcache->set("getOrgTypeTree", $resp, array('ttl' => 3600));
		}
		return $resp;
	}

	/**
	 * Возвращает список организаций для дерева
	 */
	function getOrgUsersTree($data, $superadmin)
    {
		$where = "(1=1)";
		$queryParams = array();

		if ($superadmin) {
			if (!empty($data['node']) && $data['node'] != 'other') {
				$where .= " and OrgType_id = :OrgType_id";
				$queryParams['OrgType_id'] = $data['node'];
			} else {
				$where .= " and OrgType_id IS NULL";
			}

			$where .= " and (ISNULL(o.Org_isAccess,1) = 2 
				or exists(
					Select top 1 
						puco.pmUserCacheOrg_id 
					from v_pmUserCacheOrg puco with (nolock) 
						inner join pmUserCache puc with (nolock) on puc.pmUser_id = puco.pmUserCache_id
					where puco.Org_id = o.Org_id and ISNULL(puc.pmUser_deleted, 1) = 1
				))";
		} else {
			$where .= " and Org_id IN (".implode(',', $data['session']['orgs']).")";
		}

		$sql = "
			SELECT
				o.Org_id,
				o.Org_Nick,
				ISNULL(o.Org_isAccess,1) as Org_isAccess
			FROM
				v_Org o with (nolock)
			WHERE
				{$where}
				
			ORDER BY
				o.Org_Nick
		";
		// echo getDebugSql($sql, $queryParams); die();
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Возвращает список ЛПУ доступных для данного пользователя
	 */
	function getLpuUsersTree($data, $superadmin)
    {
		$where = "";
		$queryParams = array();

		if (!$superadmin && !empty($data['Lpu_id'])) {
			$where = "WHERE Lpu_id = ?";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$sql = "
			SELECT
				Lpu_id,
				Org_id,
				Lpu_Nick
			FROM
				v_Lpu with(nolock)
			{$where}
			ORDER BY
				Lpu_Nick
		";
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Возвращает список аптек доступных для данного пользователя
	 */
	function getOrgFarmacyUsersTree($data, $superadmin, $farmacynetadmin = false )
    {
		$where = "";
		$queryParams = array();
		if (!$superadmin) {
			if ( !empty($data['OrgFarmacy_id']) ) {
				$where = "WHERE OrgFarmacy_id = :OrgFarmacy_id";
				$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
			}
			else {
				$where = "WHERE (1 = 0)";
			}
		}

		if ( !$farmacynetadmin )
		{
			$sql = "
				SELECT
					OrgFarmacy_id,
					Org_id,
					OrgFarmacy_Nick
				FROM
					v_OrgFarmacy with(nolock)
				{$where}
				ORDER BY
					OrgFarmacy_Nick
			";
		}
		else
		{
			$sql = "
				select	distinct
					ct.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Nick
				from
					v_Contragent ct with(nolock)
					inner join	v_OrgFarmacy ofr with(nolock) on ct.OrgFarmacy_id = ofr.OrgFarmacy_id
				where
					ct.Org_pid = :OrgFarmacy_id
				ORDER BY
						OrgFarmacy_Nick
			";
			$queryParams['OrgFarmacy_id'] = $data['OrgNet_id'];
		}

		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Возвращает список информации о текущем ЛПУ
	 */
	function getCurrentLpuData($data)
    {
		$sql = "
			declare @date date = dbo.tzGetDate();
			declare @Lpu_id bigint = :Lpu_id;
			with Attributes as (
				select
					ASV.AttributeSignValue_TablePKey as Lpu_id,
					[AS].AttributeSign_Code as SignCode,
					A.Attribute_SysNick as SysNick,
					coalesce(
						cast(AV.AttributeValue_ValueIdent as varchar),
						cast(AV.AttributeValue_ValueInt as varchar),
						cast(AV.AttributeValue_ValueFloat as varchar),
						AV.AttributeValue_ValueString,
						cast(AV.AttributeValue_ValueBoolean as varchar),
						convert(varchar(10), AV.AttributeValue_ValueDate, 108)
					) as Value
				from
					v_AttributeSign [AS] with(nolock)
					inner join v_AttributeSignValue ASV on ASV.AttributeSign_id = [AS].AttributeSign_id
					inner join v_AttributeValue AV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
				where
					([AS].AttributeSign_TableName like 'dbo.Lpu' or [AS].AttributeSign_TableName like 'dbo.v_Lpu')
					and ASV.AttributeSignValue_TablePKey = @Lpu_id
					and ASV.AttributeSignValue_begDate <= @date
					and (ASV.AttributeSignValue_endDate is null or ASV.AttributeSignValue_endDate > @date)
					and A.Attribute_begDate <= @date
					and (A.Attribute_endDate is null or A.Attribute_endDate > @date)
			)
			SELECT
				rtrim(isnull(Lpu.Lpu_Name, '')) as Lpu_Name,
				rtrim(isnull(Lpu.Lpu_Nick, '')) as Lpu_Nick,
				rtrim(isnull(Lpu.Lpu_SysNick, '')) as Lpu_SysNick,
				rtrim(isnull(Lpu.Lpu_Email, '')) as Lpu_Email,
				isnull(LpuLevel.LpuLevel_id, 0) as LpuLevel_id,
				isnull(LpuLevel.LpuLevel_Code, 0) as LpuLevel_Code,
				isnull(Lpu.Lpu_RegNomC, 0) as Lpu_RegNomC,
				isnull(Lpu.Lpu_IsLab, 0) as Lpu_IsLab,
				Lpu.Org_id as Org_id,
				isnull(Lpu_IsDMS, 1) as Lpu_IsDMS,
				isnull(Lpu_IsSecret, 1) as Lpu_IsSecret,
				OST.KLRgn_id,
				OST.KLSubRgn_id,
				OST.KLCity_id,
				OST.KLTown_id,
				rtrim(isnull(Lpu.Lpu_Name, '')) as Org_Name,
				rtrim(isnull(Lpu.Lpu_Nick, '')) as Org_Nick,
				rtrim(isnull(Lpu.Lpu_Email, '')) as Org_Email,
				isnull(LpuType.LpuType_id, 0) as LpuType_id,
				isnull(LpuType.LpuType_Code, 0) as LpuType_Code,
				BirthMesLevel.MesLevel_id as BirthMesLevel_id,
				BirthMesLevel.MesLevel_Code as BirthMesLevel_Code
			FROM
				v_Lpu Lpu with (nolock)
				left join LpuLevel with (nolock) on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
				left join v_LpuType LpuType with (nolock) on LpuType.LpuType_id = Lpu.LpuType_id
				outer apply(
					select top 1
						KLRgn_id,
						KLSubRgn_id,
						KLCity_id,
						KLTown_id
					from
						v_OrgServiceTerr (nolock)
					where
						Org_id = Lpu.Org_id
				) OST -- территория обслуживания переехала в OrgServiceTerr
				outer apply(
					select top 1
						(select top 1 Value from Attributes where SignCode = 6 and SysNick like 'LevelROD') as LevelROD
				) A
				left join v_MesLevel BirthMesLevel with(nolock) on BirthMesLevel.MesLevel_id = A.LevelROD
			WHERE
				Lpu.Lpu_id = @Lpu_id
		";
		//echo getDebugSql($sql, array('Lpu_id' => $data['Lpu_id']));
		$res = $this->db->query($sql, array('Lpu_id' => $data['Lpu_id']));
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Возвращает список информации о текущей организации
	 */
	function getCurrentOrgData($data)
    {
		$sql = "
			SELECT
				rtrim(isnull(Org.Org_Name, '')) as Org_Name,
				rtrim(isnull(Org.Org_Nick, '')) as Org_Nick,
				rtrim(isnull(Org.Org_Email, '')) as Org_Email,
				Org_id as Org_id,
				OST.KLRgn_id,
				OST.KLSubRgn_id,
				OST.KLCity_id,
				OST.KLTown_id
			FROM
				v_Org Org with (nolock)
				outer apply(
					select top 1
						KLRgn_id,
						KLSubRgn_id,
						KLCity_id,
						KLTown_id
					from
						v_OrgServiceTerr (nolock)
					where
						Org_id = Org.Org_id
				) OST -- территория обслуживания переехала в OrgServiceTerr
			WHERE
				Org.Org_id = :Org_id
		";
		//echo getDebugSql($sql, array('Org_id' => $data['Org_id']));
		$res = $this->db->query($sql, array('Org_id' => $data['Org_id']));
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Возвращает список АРМов, не относящихся к службам и местам работы
	 */
	function getOtherARMList($data)
	{
		$response = array(
			'MedStaffFact_id' => null,
			'LpuSection_id' => null,
			'MedPersonal_id' => $data['session']['medpersonal_id'],
			'LpuSection_Name' => null,
			'LpuSection_Nick' => null,
			'PostMed_Name' => null,
			'PostMed_Code' => null,
			'PostMed_id' => null,
			'LpuUnit_Name' => null,
			'Timetable_isExists' => null,
			'LpuUnitType_SysNick' => null,
			'LpuUnitType_id' => null,
			'LpuSectionProfile_SysNick' => null,
			'LpuSectionProfile_Code' => null,
			'LpuSectionProfile_id' => null,
			'MedService_id' => null,
			'MedService_Nick' => null,
			'MedService_Name' => null,
			'MedServiceType_id' => null,
			'MedServiceType_SysNick' => null,
			'MedPersonal_FIO' => null,
			'Org_id' => $data['session']['org_id'],
			'Lpu_id' => $data['session']['lpu_id'],
			'Lpu_Nick' => $this->getLpuNickFromLpuId($data['session']['lpu_id'])
		);

		$response['Org_Nick'] = $response['Lpu_Nick'];

		return $response;
	}


	/**
	 * Возвращает список АРМов, относящихся к иным организациям
	 */
	function getOtherOrgARMList($data) {
		$response = array(
			'MedStaffFact_id' => null,
			'LpuSection_id' => null,
			'MedPersonal_id' => null,
			'LpuSection_Name' => null,
			'LpuSection_Nick' => null,
			'PostMed_Name' => null,
			'PostMed_Code' => null,
			'PostMed_id' => null,
			'LpuUnit_Name' => null,
			'Timetable_isExists' => null,
			'LpuUnitType_SysNick' => null,
			'LpuUnitType_id' => null,
			'LpuSectionProfile_SysNick' => null,
			'LpuSectionProfile_Code' => null,
			'LpuSectionProfile_id' => null,
			'MedService_id' => null,
			'MedService_Nick' => null,
			'MedService_Name' => null,
			'MedServiceType_id' => null,
			'MedServiceType_SysNick' => null,
			'MedPersonal_FIO' => null,
			'Org_id' => $data['session']['org_id'],
			'Org_Nick' => $this->getOrgNickFromOrgId($data['session']['org_id'])
		);

		return $response;
	}


	/**
	 * Возвращает список мест работы врача
	 */
	function getUserMedStaffFactList($data)
	{
		// TODO: Требует рефакторинга
		// TODO: Даты работы врачей, либо учитывать, либо не учитывать?
		// TODO: По идее в последнем запросе надо еще учитывать и места работы тоже? Пока оставил и не трогаю
		// TODO: Также ФИО врача и MedPersonal_id выбранный должен храниться в другом месте и получать мы его должны из другого места, сейчас фио дублируется на каждый доступный АРМ, но medpersonal_id есть в сессии...
		//       Надо понять как это лучше и эффективнее сделать в дальнейшем
		// Признак учитывать дату

		// Для Самары, Карелии и Астрахани метод вынесен в региональную модель


		// Берем организации пользователя (если указано больше одной организации) и по ним открываем доступ к АРМам
		// можно конечно по ЛПУшным фильтровать именно по Lpu_id (из $data['session']['setting']['server']['lpu']), но и так правильно тоже
		$orgmsfilter = "Org_id = :Org_id ";
		/*if (isset($data['session']) && isset($data['session']['orgs']) && is_array($data['session']['orgs']) && count($data['session']['orgs'])>1) {
			$orgs = "'".implode("','", $data['session']['orgs'])."'";
			$orgmsfilter = "Org_id in (".$orgs.") ";
		}
		if (isSuperAdmin() || havingGroup(array('medpersview', 'ouzuser', 'ouzadmin', 'ouzchief'))) { // но если суперадминистратор,  или ОУЗ, или medpersview, то он может быть привязан к любому ЛПУ
			$orgmsfilter = "Org_id is not null";
		}*/

		if ($data['session']['orgtype']!='lpu') { // если это не ЛПУ

			$filter = '';
			$params = array('Org_id'=>$data['session']['org_id'],'pmUser_id'=>$data['pmUser_id'],'pmUser_Name'=>toAnsi($data['session']['user']));
			if ($data['MedService_id']>0) {
				$params['MedService_id'] = $data['MedService_id'];
				$filter = ' and MS.MedService_id = :MedService_id';
			}

			if (havingGroup('orgadmin')) {
				//Админу организации доступны все службы его организации
				$sql = " 
					SELECT
						null as MedStaffFact_id,
						MS.LpuSection_id,
						:pmUser_id as MedPersonal_id,
						null as LpuSection_Name,
						null as LpuSection_Nick,
						null as PostMed_Name,
						null as PostMed_Code,
						null as PostMed_id,
						null as LpuBuilding_id,
						null as LpuBuilding_Name,
						null as LpuUnit_id,
						null as LpuUnitSet_id,
						null as LpuUnit_Name,
						null as Timetable_isExists, 
						null as LpuUnitType_SysNick,
						MS.LpuUnitType_id,
						null as LpuSectionProfile_SysNick,
						null as LpuSectionProfile_Code,
						null as LpuSectionProfile_id,
						MS.MedService_id,
						MS.MedService_Nick,
						MS.MedService_Name,
						MS.MedServiceType_id,
						mst.MedServiceType_SysNick, 
						ms.MedService_IsExternal,
						ms.MedService_IsLocalCMP,
						ms.MedService_LocalCMPPath,
						:pmUser_Name as MedPersonal_FIO,
						Org.Org_id,
						null as Lpu_id,
						Org.Org_Nick,
						null as Lpu_Nick,
						null as MedicalCareKind_id,
						null as PostKind_id,
						null as SmpUnitType_Code,
						null as SmpUnitParam_IsKTPrint
					FROM 
						v_MedService MS with (NOLOCK)
						left join v_Org Org with (nolock) on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = MS.MedServiceType_id
					where
						MS.Org_id = :Org_id 
						and MS.MedService_begDT <= @date and (MS.MedService_endDT >= @date or MS.MedService_endDT is null) 
						{$filter}
				";
			} else {
				$sql = " 
					SELECT
						null as MedStaffFact_id,
						MS.LpuSection_id,
						:pmUser_id as MedPersonal_id,
						null as LpuSection_Name,
						null as LpuSection_Nick,
						null as PostMed_Name,
						null as PostMed_Code,
						null as PostMed_id,
						null as LpuBuilding_id,
						null as LpuBuilding_Name,
						null as LpuUnit_id,
						null as LpuUnitSet_id,
						null as LpuUnit_Name,
						null as Timetable_isExists, 
						null as LpuUnitType_SysNick,
						MS.LpuUnitType_id,
						null as LpuSectionProfile_SysNick,
						null as LpuSectionProfile_Code,
						null as LpuSectionProfile_id,
						MS.MedService_id,
						MS.MedService_Nick,
						MS.MedService_Name,
						MS.MedServiceType_id,
						mst.MedServiceType_SysNick, 
						ms.MedService_IsExternal,
						ms.MedService_IsLocalCMP,
						ms.MedService_LocalCMPPath,
						:pmUser_Name as MedPersonal_FIO,
						Org.Org_id,
						null as Lpu_id,
						Org.Org_Nick,
						null as Lpu_Nick,
						null as MedicalCareKind_id,
						null as PostKind_id,
						null as SmpUnitType_Code,
						null as SmpUnitParam_IsKTPrint
					FROM 
						v_pmUserCacheOrg PUO with(nolock)
						inner join v_PersonWork PW with(nolock) on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
						inner join v_Org Org with (nolock) on Org.Org_id = PW.Org_id
						left join v_OrgStruct OS with(nolock) on OS.OrgStruct_id = PW.OrgStruct_id
						inner join v_MedService MS with(nolock) on MS.Org_id = Org.Org_id and isnull(MS.OrgStruct_id,0) = isnull(OS.OrgStruct_id,0)
						left join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where
						PUO.Org_id = :Org_id
						and PUO.pmUserCache_id = :pmUser_id
						and MS.MedService_begDT <= @date and (MS.MedService_endDT >= @date or MS.MedService_endDT is null)
						{$filter}
				";
			}
		} else {
			$filter_medstafffact = '';
			$filter_medservicemedpersonal = '';
			$filter_medservice = '';
			$use_date = true;
			if ($use_date) {
				$filter_medstafffact = 'and cast(msf.WorkData_begDate as date) <= @date and (cast(msf.WorkData_endDate as date) >= @date or msf.WorkData_endDate is null)';
				$filter_medservicemedpersonal = 'and (cast(msmp.MedServiceMedPersonal_begDT as date) <= @date and (cast(msmp.MedServiceMedPersonal_endDT as date) >= @date or msmp.MedServiceMedPersonal_endDT is null))';
				$filter_medservice = 'and cast(MS.MedService_begDT as date) <= @date and (cast(MS.MedService_endDT as date) >= @date or MS.MedService_endDT is null)';
			}
			$filter = '';
			$params = array('MedPersonal_id'=>$data['MedPersonal_id'],'Lpu_id'=>$data['Lpu_id'],'pmUser_id'=>$data['pmUser_id'],'pmUser_Name'=>toAnsi($data['session']['user']), 'Org_id'=>$data['session']['org_id']);
			if ($data['MedService_id']>0) {
				$params['MedService_id'] = $data['MedService_id'];
				$filter = ' and MedService_id = :MedService_id';
			} elseif ($data['MedStaffFact_id']>0) {
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
				$filter = ' and msf.MedStaffFact_id = :MedStaffFact_id';
				if ($data['LpuSection_id']>0) { // если передано отделение, то фильтруем и по отделению
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$filter .= ' and ls.LpuSection_id = :LpuSection_id';
				}
			}

			$filter_lpusection = '';
			if (isset($data['LpuSection_id']) && $data['LpuSection_id']>0) { // если передано отделение, то фильтруем и по отделению
				$params['LpuSection_id'] = $data['LpuSection_id'];
				$filter_lpusection = ' and ls.LpuSection_id = :LpuSection_id';
			}

			if ( $data['session']['region']['nick'] == 'ufa' ) {
				$persisFields = '
					,null as MedicalCareKind_id
					,null as PostKind_id
				';
			}
			else {
				$persisFields = '
					,msf.MedicalCareKind_id
					,msf.PostKind_id
				';
			}
			$farmacy_filter = "(1=0)";
			if (isset($_SESSION['OrgFarmacy_id'])) {
				$farmacy_filter = " exists(Select OrgFarmacy_id from v_OrgFarmacy with (nolock) where OrgFarmacy_id = :Lpu_id) ";
			}
			if (empty($data['MedPersonal_id'])) { // для пользователей, которые не связаны с врачами, нет смысла выполнять запрос
				return false;
			}
			else if (!empty($data['StacPriemOnly']) && $data['StacPriemOnly'] == 2) { // здесь нет выборки дополнительной информации по приемным отделениям, поэтому также нет смысла выполнять запрос
				return false;
			}

			$sql_medstafffact = "
				-- места работы 
				SELECT
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case 
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then 
							case when (select count(*) from v_TimeTableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then 
							case when (select count(*) from v_TimetablePar tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then 
							case when (select count(*) from v_TimetableStac_lite tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'				
					end as Timetable_isExists,
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr with(nolock) where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					ls.LpuSectionAge_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick, 
					msf.Person_FIO as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					eq.ElectronicQueueInfo_id,
					eq.ElectronicService_id,
					eq.ElectronicService_Num,
					eq.ElectronicQueueInfo_CallTimeSec,
					eq.ElectronicQueueInfo_PersCallDelTimeMin,
					eq.ElectronicQueueInfo_CallCount,
					eq.ElectronicService_isShownET,
					STUFF(CAST((
						select ISNULL(CAST(etl.ElectronicTreatment_id as VARCHAR),'') + ',' as 'data()'
						from v_ElectronicTreatmentLink etl with (nolock)
							inner join v_ElectronicQueueInfo eqio with (nolock) on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso with (nolock) on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						for xml path(''), TYPE) AS VARCHAR(MAX)), 1, 1, ''
					) as ElectronicTreatment_ids,
					eboard.ElectronicScoreboard_id,
					eboard.ElectronicScoreboard_IPaddress,
					eboard.ElectronicScoreboard_Port,
					null as SmpUnitType_Code,
					null as SmpUnitParam_IsKTPrint,
					null as Storage_id,
					null as Storage_pid
					" . $persisFields . "
				FROM
					v_MedStaffFact msf with (nolock)
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
					outer apply (
						select top 1
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq (nolock)
							left join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi with(nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 mseq.MedStaffFact_id = msf.MedStaffFact_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
					) eq
					outer apply (
						select top 1
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd with(nolock)
						left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
					) as eboard
				WHERE
					msf.MedPersonal_id = :MedPersonal_id
					--//and msf.Lpu_id = Lpu_id
					and lpu.{$orgmsfilter}
					and msf.MedStaffFact_Stavka > 0
					{$filter_medstafffact} {$filter}
				";
			$sql_workgraph = "
			SELECT
					msf.MedStaffFact_id,
					ls.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then
							case when (select count(*) from v_TimeTableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then
							case when (select count(*) from v_TimetablePar tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then
							case when (select count(*) from v_TimetableStac_lite tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'
					end as Timetable_isExists,
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr with(nolock) where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					ls.LpuSectionAge_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick,
					msf.Person_FIO as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					null as ElectronicQueueInfo_id,
					null as ElectronicService_id,
					null as ElectronicService_Num,
					null as ElectronicQueueInfo_CallTimeSec,
					null as ElectronicQueueInfo_PersCallDelTimeMin,
					null as ElectronicQueueInfo_CallCount,
					null as ElectronicService_isShownET,
					null as ElectronicTreatment_ids,
					null as ElectronicScoreboard_id,
					null as ElectronicScoreboard_IPaddress,
					null as ElectronicScoreboard_Port,
					null as SmpUnitType_Code,
					null as SmpUnitParam_IsKTPrint,
					null as Storage_id,
					null as Storage_pid
					" . $persisFields . "
				FROM
					v_MedStaffFact msf with (nolock)
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id

					inner join v_WorkGraph WG with (nolock) on (
						WG.MedStaffFact_id = msf.MedStaffFact_id and
						(
							CAST(WG.WorkGraph_begDT as date) <= @date
							and CAST(WG.WorkGraph_endDT as date) >= @date
						)
					)
					left join v_WorkGraphLpuSection WGLS with (nolock) on WGLS.WorkGraph_id = WG.WorkGraph_id
					--left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = WGLS.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id

					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					{$filter}
			";
			$sql_medservice = "
				-- сотрудники служб
				SELECT
					case 
						when mst.MedServiceType_SysNick = 'reanimation' then (
							select top 1 t1.MedStaffFact_id 
							from v_MedStaffFact t1 with (nolock)
								inner join dbo.v_LpuUnit t2 on t2.LpuUnit_id = t1.LpuUnit_id
							where t1.MedPersonal_id = msmp.MedPersonal_id 
								and t1.WorkData_endDate is null
								and t2.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')
						) else null 
					end as MedStaffFact_id,
					MS.LpuSection_id,
					msmp.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					null as PostMed_Name,
					null as PostMed_Code,
					null as PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					null as Timetable_isExists,
					lut.LpuUnitType_SysNick,
					MS.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					ls.LpuSectionAge_id,
					MS.MedService_id,
					MS.MedService_Nick,
					MS.MedService_Name,
					MS.MedServiceType_id,
					mst.MedServiceType_SysNick,
					mp.Person_FIO as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					null as MedStaffFactCache_IsDisableInDoc,
					eq.ElectronicQueueInfo_id,
					eq.ElectronicService_id,
					eq.ElectronicService_Num,
					eq.ElectronicQueueInfo_CallTimeSec,
					eq.ElectronicQueueInfo_PersCallDelTimeMin,
					eq.ElectronicQueueInfo_CallCount,
					eq.ElectronicService_isShownET,
					STUFF(CAST((
						select ISNULL(CAST(etl.ElectronicTreatment_id as VARCHAR),'') + ',' as 'data()'
						from v_ElectronicTreatmentLink etl with (nolock)
							inner join v_ElectronicQueueInfo eqio with (nolock) on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso with (nolock) on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						for xml path(''), TYPE) AS VARCHAR(MAX)), 1, 1, ''
					) as ElectronicTreatment_ids,
					eboard.ElectronicScoreboard_id,
					eboard.ElectronicScoreboard_IPaddress,
					eboard.ElectronicScoreboard_Port,
					sut.SmpUnitType_Code,
					sup.SmpUnitParam_IsKTPrint,
					strg.Storage_id,
					strg.Storage_pid,
					null as MedicalCareKind_id,
					msf.PostKind_id
				FROM
					v_MedService MS with (NOLOCK)
					cross apply (
						Select top 1 msmp.MedPersonal_id from v_MedServiceMedPersonal msmp with (NOLOCK)
						where msmp.MedService_id = MS.MedService_id
						and msmp.MedPersonal_id = :MedPersonal_id
						{$filter_medservicemedpersonal}
					) as msmp
					outer apply (
						select top 1
							msf.PostKind_id
						from
							v_MedStaffFact msf (nolock)
						where
							msf.MedPersonal_id = msmp.MedPersonal_id
							and msf.LpuSection_id = ms.LpuSection_id
					) msf
					outer apply (
						select top 1
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq (nolock)
							left join v_MedServiceMedPersonal msmp2 with(nolock) on msmp2.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							left join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi with(nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 msmp2.MedPersonal_id = msmp.MedPersonal_id
							 and msmp2.MedService_id = MS.MedService_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
					) eq
					outer apply (
						select top 1
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd with(nolock)
						left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
					) as eboard
					left join v_MedPersonal mp with (NOLOCK) on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = isnull(ls.LpuBuilding_id,MS.LpuBuilding_id)
					outer apply (
						select top 1 *
						from v_SmpUnitParam sup (nolock)
						where sup.LpuBuilding_id = lb.LpuBuilding_id
						order by sup.SmpUnitParam_id desc
					) sup
					left join v_SmpUnitType sut with (nolock) on sut.SmpUnitType_id = sup.SmpUnitType_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = isnull(ls.LpuUnit_id,MS.LpuUnit_id)
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = isnull(lu.LpuUnitType_id,MS.LpuUnitType_id)
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = MS.MedServiceType_id
					outer apply (
						select top 1						
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_StorageStructLevel i_ssl with (nolock)
							left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id
						where
							i_ssl.MedService_id = MS.MedService_id
						order by
							i_ssl.StorageStructLevel_id
					) strg
				where
					--// MS.Lpu_id = Lpu_id
					lpu.{$orgmsfilter} and (1=1)
					{$filter_medservice} {$filter}
					and msmp.MedPersonal_id = :MedPersonal_id
					and mst.MedServiceType_SysNick in ('HTM', 'vk', 'mse', 'lab', 'pzm', 'func', 'patb', 'mstat', 'prock', 'dpoint', 'ooa', 'merch', 'pmllo', 'regpol', 'sprst', 'okadr', 'minzdravdlo', 'leadermo', 'mekllo', 'spesexpertllo', 'adminllo', 'touz', 'reglab', 'oper_block', 'smp', 'slneotl', 'konsult', 'foodserv', 'vac', 'epidem_mo', 'remoteconsultcenter','smpdispatchstation','forenbiodprtwithmolgenlab','forenchemdprt','medforendprt','forenhistdprt','organmethdprt','forenmedcorpsexpdprt','forenmedexppersdprt','commcomplexp','forenareadprt', 'lvn', 'smpheaddoctor', 'zmk', 'rpo','spec_mz', 'medosv','reanimation', 'microbiolab')
			";

			$sql_medstafffact_linked = "
				-- связанные места работы
				SELECT
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case
						when (select count(*) from v_TimeTableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true'
						else 'false'
					end as Timetable_isExists,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					ls.LpuSectionAge_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick,
					msf.Person_FIO as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					msfl.MedStaffFactLink_id,
					convert(varchar(10), msfl.MedStaffFactLink_begDT, 104) as MedStaffFactLink_begDT,
					convert(varchar(10), msfl.MedStaffFactLink_endDT, 104) as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					null as ElectronicQueueInfo_id,
					null as ElectronicService_id,
					null as ElectronicService_Num,
					null as ElectronicQueueInfo_CallTimeSec,
					null as ElectronicQueueInfo_PersCallDelTimeMin,
					null as ElectronicQueueInfo_CallCount,
					null as ElectronicService_isShownET,
					null as ElectronicTreatment_ids,
					null as ElectronicScoreboard_id,
					null as ElectronicScoreboard_IPaddress,
					null as ElectronicScoreboard_Port,
					null as SmpUnitType_Code,
					null as SmpUnitParam_IsKTPrint,
					null as Storage_id,
					null as Storage_pid
					" . $persisFields . "
				FROM
					v_MedStaffFactLink msfl with (nolock)
					inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_MedStaffFact mmsf with (nolock) on mmsf.MedStaffFact_id = msfl.MedStaffFact_sid
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					mmsf.MedPersonal_id = :MedPersonal_id
					and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					and lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap')
					{$filter_medstafffact} {$filter}
			";

			if ($data['MedService_id']>0) {
				$sql = $sql_medservice;
			} elseif ($data['MedStaffFact_id']>0) {
				$sql = $sql_medstafffact . ' union all ' . $sql_workgraph . ' union all ' . $sql_medstafffact_linked;
			} else {
				$sql =  $sql_medstafffact . ' union all ' . $sql_workgraph . ' union all ' . $sql_medservice . ' union all ' . $sql_medstafffact_linked;
			}
		}

		$sql = "
			declare @date datetime = cast(dbo.tzGetDate() as date);
		" . $sql;
		$res = $this->db->query($sql, $params);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список мест работы
	 */
	function getMedStaffFact($data)
	{
		if (isset($data['MedPersonal_id']))
		{
			$sql = "
				SELECT
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					isnull(ls.LpuSection_FullName,'') + ', ' + isnull(lu.LpuUnit_Name,'') + ' ) ' as MedStaffFact_Name,
					lu.LpuUnitType_id,
					case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					case when (select count(*) from v_MedStaffRegion msr with(nolock) where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch
				FROM
					v_MedStaffFact msf with (nolock)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
			";

			$res = $this->db->query($sql, array('MedPersonal_id'=>$data['MedPersonal_id'], 'Lpu_id'=>$data['Lpu_id']));
			if ( is_object($res) )
				return $res->result('array');
			else
				return false;
		}
		else
		{
			return true;
		}
	}


	/**
	 * Возвращает список мест работы по идентификатору пользователя, включая места работы другого врача,
	 * где пользователь является средним медперсоналом для него
	 */
	function getMedStaffFactsBypmUser($pmUser_id)
	{
		$res = array();

		$sql = "
			SELECT
				msf.MedStaffFact_id
			FROM
				v_MedStaffFact msf with (nolock)
			inner join v_pmUser pu with (nolock) on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
			WHERE
				pu.pmUser_id = :pmUser_id
			UNION
			SELECT 
				msf.MedStaffFact_id
			from v_MedStaffFact msf with(nolock) where MedPersonal_id in (
				SELECT
					msf1.MedPersonal_id
				FROM
					v_MedStaffFact msf with (nolock)
				inner join MedStaffFactLink msfl with(nolock) on msf.MedStaffFact_id = msfl.MedStaffFact_sid
				inner join v_MedStaffFact msf1 with (nolock) on msf1.MedStaffFact_id = msfl.MedStaffFact_id
				inner join v_pmUser pu with (nolock) on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
				WHERE
					pu.pmUser_id = :pmUser_id
			)
		";

		$result = $this->db->query($sql, array('pmUser_id' => $pmUser_id) );
		if ( is_object($result) ) {
			$result = $result->result('array');
			foreach($result as $row) {
				$res[] = $row['MedStaffFact_id'];
			}
		}

		return $res;
	}

	/**
	 * Возвращает данные по месту работы
	 */
	function getMedStaffFactData($data)
	{
		if (isset($data['MedStaffFact_id']) && $data['MedStaffFact_id'] != '')
		{
			$sql = "
				SELECT
					msf.MedStaffFact_id,
					msf.MedPersonal_id,
					msf.LpuSection_id,
					pm.PostMed_id,
					pm.PostMed_Code,
					LpuSection_FullName +' ( ' + LpuUnit_Name + ' ) ' as MedStaffFact_Name,
					lu.LpuUnitType_id,
					lut.LpuUnitType_SysNick,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					ls.LpuSection_Name,
					msf.Person_FIO as MedPersonal_FIO
				FROM
					v_MedStaffFact msf with (nolock)
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed pm with (nolock) on pm.PostMed_id = msf.Post_id
				WHERE
					msf.MedStaffFact_id = ?
			";
			$res = $this->db->query($sql, array($data['MedStaffFact_id']));
			if ( is_object($res) ) {
				$d = $res->result('array');
				return $d[0];
			} else
				return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Определение наличия рабочего места с должностью «Главная медсестра»
	 * @param type $data
	 * @return type
	 */
	function getHeadNursePost($data){
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_id' => $data['Lpu_id'],
		);
		$query = "
			select top 1
				1 as true
			from v_MedStaffFact msf with(nolock)
			where
				msf.Lpu_id = :Lpu_id
				and (msf.WorkData_endDate is null or msf.WorkData_endDate >= dbo.tzGetDate())
				and msf.MedPersonal_id = :MedPersonal_id
				and msf.Post_id in( 10501,4,10261 )
		";
		//echo getDebugSQL($query, $params);
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			$result = $res->result('array');
			if( count($result)>0 ) {
				return $result[0];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 *	Возвращает данные по рабочему месту врача ЛЛО поликлинники (если врач ЛЛО) иначе false
	 */
	function getLLOData($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_id' => $data['Lpu_id'],
		);
		$filter = "mp.Lpu_id = :Lpu_id";
		$filter .= " and (mp.WorkData_endDate is null or mp.WorkData_endDate >= dbo.tzGetDate())";
		$filter .= " and mp.MedPersonal_id = :MedPersonal_id";
		switch ($data['session']['region']['nick']) {
			case 'saratov':
				// в Саратове должно быть доступно пока всем врачам поликлиники #14385
				// Условия доступа в данный АРМ - в пользователе указан врач
				$hasActiveDlo = false;
				break;
			case 'perm':
				// для Перми условие доступа в данный АРМ - в пользователе указан врач, имеющий активный код ЛЛО в данной МО #17490
				$hasActiveDlo = true;
				break;
			default:
				// в других регионах должно быть доступно только врачам ЛЛО #14385
				// Условия доступа в данный АРМ - в пользователе указан врач
				$filter .= " and mp.WorkData_IsDlo = 2";
				$hasActiveDlo = false;
				break;
		}
		if ($hasActiveDlo) {
			$query = '
			select top 1
				null as MedStaffFact_id
				,msf.LpuSection_id
				,msf.MedPersonal_id
				,ls.LpuSection_FullName as LpuSection_Name
				,ls.LpuSection_Name as LpuSection_Nick
				,PostMed.PostMed_Name
				,PostMed.PostMed_Code
				,PostMed.PostMed_id
				,LpuUnit.LpuUnit_Name
				,null as Timetable_isExists
				,LpuUnit.LpuUnitType_SysNick
				,LpuUnit.LpuUnitType_id
				,ls.LpuSectionProfile_SysNick
				,ls.LpuSectionProfile_Code
				,ls.LpuSectionProfile_id
				,null as MedService_id
				,null as MedService_Nick
				,null as MedService_Name
				,null as MedServiceType_id
				,null as MedServiceType_SysNick
				,msf.Person_Fio as MedPersonal_FIO
				,Lpu.Org_id
				,msf.Lpu_id
				,Lpu.Lpu_Nick
				,Lpu.Lpu_Nick as Org_Nick
			from v_MedStaffFact msf with(nolock)
				inner join v_Lpu Lpu with(nolock) on msf.Lpu_id = Lpu.Lpu_id
				inner join v_MedPersonal mp with(nolock) on msf.MedPersonal_id = mp.MedPersonal_id and msf.Lpu_id = mp.Lpu_id
				inner join v_LpuSection ls with(nolock) on msf.LpuSection_id = ls.LpuSection_id
				left join v_PostMed PostMed with(nolock) on msf.Post_id = PostMed.PostMed_id
				left join v_LpuUnit LpuUnit with(nolock) on msf.LpuUnit_id = LpuUnit.LpuUnit_id
			where
				msf.Lpu_id = :Lpu_id
				and msf.MedPersonal_id = :MedPersonal_id
				and mp.WorkData_IsDlo = 2
				and msf.WorkData_dlobegDate <= dbo.tzGetDate()
				and (msf.WorkData_dloendDate is null or msf.WorkData_dloendDate >= dbo.tzGetDate())
			';
		} else {
			$query = "
				select top 1
					null as MedStaffFact_id
					,null as LpuSection_id
					,mp.MedPersonal_id
					,null as LpuSection_Name
					,null as LpuSection_Nick
					,null as PostMed_Name
					,null as PostMed_Code
					,null as PostMed_id
					,null as LpuUnit_Name
					,null as Timetable_isExists
					,null as LpuUnitType_SysNick
					,null as LpuUnitType_id
					,null as LpuSectionProfile_SysNick
					,null as LpuSectionProfile_Code
					,null as LpuSectionProfile_id
					,null as MedService_id
					,null as MedService_Nick
					,null as MedService_Name
					,null as MedServiceType_id
					,null as MedServiceType_SysNick
					,mp.Person_Fio as MedPersonal_FIO
					,mp.Lpu_id
					,lpu.Org_id
					,Lpu.Lpu_Nick
					,Lpu.Lpu_Nick as Org_Nick
				from
					v_MedPersonal mp with(nolock)
					inner join v_Lpu Lpu with(nolock) on mp.Lpu_id = Lpu.Lpu_id
				where
					{$filter}
			";
		}
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			$result = $res->result('array');
			if( count($result)>0 ) {
				return $result[0];
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	/**
	* Возвращает идентификатор мед. персонала по номеру социальной карты
	*/
	function getMedPersonalBySocCardNum($data)
	{
		if ( isset($data['soccard_id']) && strlen($data['soccard_id']) >=25 )
		{
			$sql = "
				select top 1
					MedPersonal_id
				from
					MedPersonalCache with(nolock)
				where
					LEFT(MedPersonal_SocCardNum, 19) = ?
				order by WorkData_begdate
			";
			$res = $this->db->query($sql, array(substr($data['soccard_id'], 0, 19)));
			if ( is_object($res) )
				return $res->result('array');
			else
				return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Возвращает идентификатор мед. персонала по ИИН
	*/
	function getMedPersonalByIin($data)
	{
		return $this->queryResult("
			select top 1
				mp.MedPersonal_id
			from
				v_MedPersonal mp with (nolock)
				inner join v_PersonState ps with (nolock) on ps.Person_id = mp.Person_id
			where
				ps.Person_Inn = :Person_Inn
			order by WorkData_begdate
		", array(
			'Person_Inn' => $data['Person_Inn']
		));
	}

	/**
	 * Возвращает идентификатор мед. персонала по ФИО и ДР
	 */
	function getMedPersonalByFIODR($data)
	{
		if ( isset($data['surName']) && isset($data['firName']) && isset($data['secName']) && isset($data['birthDay']) && isset($data['polisNum']) )
		{
			$sql = "
				select top 1
					MedPersonal_id
				from
					MedPersonalCache with (nolock)
					left join v_PersonState ps  with (nolock) on ps.Person_id = MedPersonalCache.Person_id
				where
					ps.Person_surName = :surName and
					ps.Person_firName = :firName and
					ps.Person_secName = :secName and
					ps.Person_birthDay = :birthDay and
					ps.polis_num = :polisNum
				order by WorkData_begdate
			";
			//echo getDebugSQL($sql, $data);
			$res = $this->db->query($sql, $data);
			if ( is_object($res) )
				return $res->result('array');
			else
				return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Проверяет есть ли пользователь в организации
	 */
	function checkExistUserInOrg($data) {
		$query = "
			select top 1 
				pmUser_id
			from
				pmUserCache puc with (nolock)
				inner join pmUserCacheOrg puco with (nolock) on puco.pmUserCache_id = puc.pmUser_id
			where puco.Org_id = :Org_id
				and ISNULL(puc.pmUser_deleted, 1) = 1
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Возвращает список наименование текущей аптеки
	 */
	function getCurrentOrgFarmacyData($data)
    {
		$sql = "
			SELECT
				rtrim(ofr.OrgFarmacy_Nick) as OrgFarmacy_Nick,
				ofr.Org_id as Org_id,
				o.Org_Name
			FROM
				v_OrgFarmacy ofr with(nolock)
				left join v_Org o with(nolock) on o.Org_id = ofr.Org_id
			WHERE
				ofr.OrgFarmacy_id = ?
		";

		if ( isset($data['session']['OrgFarmacy_id']) ) {
			$res = $this->db->query($sql, array($data['session']['OrgFarmacy_id']));
			if ( is_object($res) )
				return $res->result('array');
			else
				return false;
		}
    }

	/**
	 * Возвращает текущего контрагента (по аптеке)
	 */
	function getCurrentOrgFarmacyContragent($data) {
		$query = "
			declare
				@OrgFarmacy_id bigint = :OrgFarmacy_id,
				@ContragentType_Code int;

			set @ContragentType_Code = (
				select
					case
						when OrgType_Code = 11 then 5 --МО
						when OrgType_Code = 5 then 6 --РАС
						when OrgType_Code = 16 then 1 --Поставщик, Организация
						when OrgType_Code = 4 then 3 --Аптека
						else null
					end
				from
					v_OrgFarmacy ofr with (nolock)
					left join v_Org o with (nolock) on o.Org_id = ofr.Org_id
					left join v_OrgType ot with(nolock) on ot.OrgType_id = o.OrgType_id
				where
					ofr.OrgFarmacy_id = @OrgFarmacy_id
			)

			select top 1
				Contragent_id as Contragent_id,
				rtrim(Contragent_Name) as Contragent_Name,
				Org_id,
				Org_pid,
				ContragentType_SysNick
			from
				v_Contragent c with (nolock)
				left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
			where
				c.OrgFarmacy_id = @OrgFarmacy_id and
				(
					@ContragentType_Code is null or
					ct.ContragentType_Code = @ContragentType_Code
				)
			order by
				Lpu_id, Contragent_id;
		";

		if (!empty($data['session']['OrgFarmacy_id'])) {
			$res = $this->db->query($query, array('OrgFarmacy_id' => $data['session']['OrgFarmacy_id']));
			if (is_object($res)) {
				return $res->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Возвращает текущего контрагента (по организации)
	 */
	function getCurrentOrgContragent($data) {
		$query = "
			declare
				@Org_id bigint = :Org_id,
				@ContragentType_Code int;

			set @ContragentType_Code = (
				select
					case
						when OrgType_Code in (11,14) then 5 --МО
						when OrgType_Code = 5 then 6 --РАС
						when OrgType_Code = 16 then 1 --Поставщик, Организация
						when OrgType_Code = 4 then 3 --Аптека
						else null
					end
				from
					v_Org o with (nolock)
					left join v_OrgType ot with(nolock) on ot.OrgType_id = o.OrgType_id
				where
					o.Org_id = @Org_id
			)

			select top 1
				Contragent_id as Contragent_id,
				rtrim(Contragent_Name) as Contragent_Name,
				Org_id,
				Org_pid,
                ContragentType_SysNick
			from
				v_Contragent c with (nolock)
				left join v_ContragentType ct with(nolock) on ct.ContragentType_id = c.ContragentType_id
			where
				c.Org_id = @Org_id and
				(
					@ContragentType_Code is null or
					ct.ContragentType_Code = @ContragentType_Code
				)
			order by
				Lpu_id, Contragent_id;
		";

		if (!empty($data['session']['org_id'])) {
			$res = $this->db->query($query, array('Org_id' => $data['session']['org_id']));
			if (is_object($res)) {
				return $res->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	* Возвращает список аптек сетевого админа
	*/
	function getNetAdminFarmacies($data)
	{
		$sql = "
			select
				distinct ct.OrgFarmacy_id
			from
				v_Contragent ct with(nolock)
				inner join	v_OrgFarmacy ofr with(nolock) on ct.OrgFarmacy_id = ofr.OrgFarmacy_id
			where
				ct.Org_pid = ?
		";
		$res = $this->db->query($sql, array($data['session']['OrgNet_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список наименование текущего ЛПУ
	 */
	function getCurrentLpuName($data)
    {
		$sql = "
			SELECT
				rtrim(L.Lpu_Nick) as Lpu_Nick,
				rtrim(L.Lpu_Name) as Lpu_Name,
				ISNULL(L.PAddress_Address,'') as Lpu_Address,
				ISNULL(L.UAddress_Address,'') as Lpu_UAddress,
				isnull(RTRIM(PS.Person_SurName), '') as Person_Surname,
				isnull(RTRIM(PS.Person_FirName), '') as Person_Firname,
				isnull(RTRIM(PS.Person_SecName), '') as Person_Secname
			FROM
				v_Lpu L (nolock)
				left join v_OrgHead OH with (nolock) on (OH.Lpu_id = L.Lpu_id and OH.OrgHeadPost_id = 1)
				left join v_PersonState PS with (nolock) on PS.Person_id = OH.Person_id
			WHERE
				L.Lpu_id = ?
		";
		$res = $this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Перекэширование логина и списка организаций пользователя
	 */
	function ReCacheOrgUserData( $user, $orgs ) {
		// сначала проверим есть ли пользователь в кэше и добавим его, если нет..
		$queryParams = array();
		$sql = "
			if NOT exists(
				select 1 from pmUserCache with(nolock) where pmUser_id = :pmUser_id
			)
			BEGIN
				DELETE pmUserCacheOrg FROM pmUserCacheOrg puco with(rowlock) INNER JOIN pmUserCache with(nolock) puc ON puc.PMUser_id = puco.pmUserCache_id WHERE puc.PMUser_Login = :pmUser_Login
				DELETE FROM pmUserCache WHERE pmUser_Login = :pmUser_Login
				INSERT INTO pmUserCache(
					pmUser_id,
					pmUser_Login,
					pmUser_Name,
					pmUser_surName,
					pmUser_firName,
					pmUser_secName,
					pmUser_updID,
					pmUserCache_updDT
				)
				values(
					:pmUser_id,
					:pmUser_Login,
					:pmUser_Name,
					:pmUser_surName,
					:pmUser_firName,
					:pmUser_secName,
					:pmUser_updID,
					dbo.tzGetDate()
				)
			END
			ELSE
			BEGIN
				DELETE pmUserCacheOrg FROM pmUserCacheOrg puco with(rowlock) INNER JOIN pmUserCache with(nolock) puc ON puc.PMUser_id = puco.pmUserCache_id WHERE puc.PMUser_Login = :pmUser_Login and puc.pmUser_id <> :pmUser_id
				DELETE FROM pmUserCache with(rowlock) WHERE pmUser_Login = :pmUser_Login AND pmUser_id <> :pmUser_id
				UPDATE pmUserCache
				SET
					pmUser_Login = :pmUser_Login,
					pmUser_Name = :pmUser_Name,
					pmUser_surName = :pmUser_surName,
					pmUser_firName = :pmUser_firName,
					pmUser_secName = :pmUser_secName,
					pmUser_updID = :pmUser_updID,
					pmUserCache_updDT = dbo.tzGetDate()
				WHERE
					pmUser_id = :pmUser_id
			END
		";

		$queryParams['pmUser_id'] = $user['pmuser_id'];
		$queryParams['pmUser_Login'] = toAnsi($user['login']);
		$queryParams['pmUser_Name'] = toAnsi(mb_substr($user['surname'] . " " . $user['firname'] . " " . $user['secname'], 0, 100));
		$queryParams['pmUser_surName'] = toAnsi(mb_substr($user['surname'], 0, 40));
		$queryParams['pmUser_firName'] = toAnsi(mb_substr($user['firname'], 0, 30));
		$queryParams['pmUser_secName'] = toAnsi(mb_substr($user['secname'], 0, 30));
		$queryParams['pmUser_updID'] = $_SESSION['pmuser_id'];

		$this->db->query($sql, $queryParams);

		$this->ReCacheUserOrgs($user, $orgs);

		return true;
	}

	/**
	 * Возвращаем данные по группам из БД в LDAP
	 */
	function recacheGroupFromDB( $data ) {
		set_time_limit(0);
		//$this->load->library('textlog', array('file'=>'recacheGroupFromDB_'.date('Y-m-d').'.log'));
		// запрос на выборку пользователей из БД
		$queryParams = array();
		$filter = "";

		if (isset($data['user'])) { // Если пользователь известен, то перекеширование групп сделаем только по этому пользователю
			$queryParams['pmUser_login'] = $data['user'];
			$filter .= "and pmUser_login = :pmUser_login";
		}
		$sql = "
			Select pmUser_id, pmUser_login, pmUser_groups from v_pmusercache (nolock) where pmUser_groups is not null and pmUser_groups!='[]' {$filter}
		";
		$result = $this->db->query($sql, $queryParams);
		$max = 10;
		$i = 0;
		if ( is_object($result) ) {
			$rows = $result->result('array');
			if (count($rows)>0) {
				foreach($rows as $row) { // перебираем найденные записи
					if (isset($row['pmUser_groups'])) {
						$groupsDB = json_decode($row['pmUser_groups']);
						if (count($groupsDB)>0) { // Если есть группы в БД, тогда и смотрим в LADP
							$user = pmAuthUser::find($row['pmUser_login']);
							$this->textlog->add("Check user ".$row['pmUser_login']);
							if ($user && (empty($user->groups) || count($user->groups)==0)) { // это условие можно убрать, в любом случае одинаковые группы не сохранятся
								// log
								$i++;
								echo $i." | User id = ".$user->pmuser_id.", login = ".$user->login.", name = ".toAnsi($user->surname . " " . $user->firname).", группы: ";
								$this->textlog->add($i." | User id = ".$user->pmuser_id.", login = ".$user->login.", name = ".toAnsi($user->surname . " " . $user->firname).", группы: ");
								// выбираем группы из БД
								foreach ($groupsDB as $group) {
									echo " ".$group->name;
									$user->addGroup($group->name);
								}
								// просто пересохраняем атрибуты
								foreach ( array_values($user->groups) as $group ) {
									ldap_insertattr($group->id, array("member" => $user->id));
								}
								echo "<br/>";
							}
							unset($user);
							if ($i>=$max) { // прерываем
								break;
							}
						}
						//var_dump($groupsDB);
					}
					//var_dump($user->groups);
				}
			}
			if ($i>=$max) {
				$msg = "Всего обработано {$i} записей, выполнение прервано, для остальных записей запустите функционал повторно<br/>";
			} else {
				$msg = "Всего обработано {$i} записей, выполнение успешно завершено.<br/>";
			}
			$this->textlog->add($msg);
			echo $msg;
		} else {
			DieWithError("Ошибка при выполнении запроса");
		}
	}

	/**
	 * По признаку из LDAP создаем группу
	 */
	function createGroupFromFlag( $data ) {
		set_time_limit(0);
		$i = 0;
		// запрос на выборку пользователей из LDAP
		$ldap_users = new pmAuthUsers("(&(medsvidgrantadd=1)(organizationalstatus=1))");
		foreach($ldap_users->users as $user) {
			if ($user) {
				$existGroup = $this->getFirstResultFromQuery("Select top 1 1 as ex from pmusercache (nolock) where pmUser_groups like '%MedSvidDeath%' where pmUser_id = :pmUser_id", array('pmUser_id'=>$user->pmuser_id));
				if (!$existGroup) {
					$i++;
					echo $i." | User id = ".$user->pmuser_id.", login = ".$user->login.", name = ".toAnsi($user->surname . " " . $user->firname)."";
					$this->textlog->add($i." | User id = ".$user->pmuser_id.", login = ".$user->login.", name = ".toAnsi($user->surname . " " . $user->firname)."");
					// todo: Если группа уже есть то добавлять не надо
					$user->addGroup('MedSvidDeath');
					// просто сохраняем новый атрибут
					foreach ( array_values($user->groups) as $group ) {
						if ($group->name == "MedSvidDeath")
							ldap_insertattr($group->id, array("member" => $user->id));
					}
					// Перекешируем
					$this->ReCacheUserData($user);
					echo "группа MedSvidDeath успешно добавлена<br/>";
				}
			}
		}
		$msg = "Всего обработано {$i} записей, выполнение успешно завершено.<br/>";
		$this->textlog->add($msg);
		echo $msg;
	}

	/**
	 * Это Doc-блок
	 */
	function ReCacheUserOrgs($user, $orgs)
	{
		$queryParams = array();
		$queryParams['pmUser_id'] = $user['pmuser_id'];

		//Если существет связь пользователя с сотрудником организации, то эти организации не перехешировать
		$sql = "
			select Org_id
			from v_pmUserCacheOrg PUO with(nolock)
			where PUO.pmUserCache_id = :pmUser_id
			and exists(
				select * from v_PersonWork PW with(nolock) 
				where PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
			)
		";
		$except_org_ids = $this->queryList($sql, $queryParams);
		$except_org_ids_str = implode(",", $except_org_ids);
		$filter = count($except_org_ids) > 0 ? " and Org_id not in ({$except_org_ids_str})" : "";

		// удалим те что были
		$sql = "
			delete from pmUserCacheOrg with(rowlock) where pmUserCache_id = :pmUser_id {$filter}
		";
		$res = $this->db->query($sql, $queryParams);

		// добавим новые
		foreach($orgs as $org) {
			if (in_array($org, $except_org_ids)) {
				continue;
			}

			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_pmUserCacheOrg_ins
					@pmUserCacheOrg_id = @Res output,
					@pmUserCache_id = :pmUser_id,
					@Org_id = :Org_id,
					@pmUser_id = :pmUser_updID,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as pmUserCacheOrg_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams['Org_id'] = $org;
			$queryParams['pmUser_updID'] = isset($_SESSION['pmuser_id']) ? $_SESSION['pmuser_id'] : 1;

			//echo getDebugSql($sql, $queryParams); exit();
			$res = $this->db->query($sql, $queryParams);
		}
	}

	/**
	 * Получение списка пользователей в организации
	 */
	function getUsersInOrg($org)
	{
		$filter = '';

		if(isset($org) && count($org) > 0) {
			$filter .= " and puco.Org_id in (".implode(',', $org).")";
		}

		$query = "
			select
				distinct puc.PMUser_id
			from
				pmUserCache puc with (nolock)
				left join pmUserCacheOrg puco with (nolock) on puco.pmUserCache_id = puc.pmUser_id
			where
				(1=1)
				{$filter}
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Перекеширование данных о человеке в базе
	 */
	function ReCacheUserData( $user, $data = NULL ) {
		if (empty($data)) {
			$data = getSessionParams();
		}
		// TODO: Нужны ли хранимки для всего этого?
		$queryParams = array();
		$sql = "
		if NOT exists(
			select 1 from pmUserCache with(nolock) where pmUser_id = :pmUser_id
		)
		BEGIN
			DELETE pmUserCacheOrg FROM pmUserCacheOrg puco with(rowlock) INNER JOIN pmUserCache puc with(nolock) ON puc.PMUser_id = puco.pmUserCache_id WHERE puc.PMUser_Login = :pmUser_Login
			DELETE FROM pmUserCache with(rowlock) WHERE pmUser_Login = :pmUser_Login
			INSERT INTO pmUserCache(
				pmUser_id,
				pmUser_Login,
				pmUser_Name,
				pmUser_surName,
				pmUser_firName,
				pmUser_secName,
				pmUser_Email,
				pmUser_Avatar,
				pmUser_About,
				pmUser_Blocked,
				Lpu_id, -- здесь возможно надо будет поменять и если у пользователя указано несколько ЛПУ - сохранять их всех 
				MedPersonal_id,
				pmUser_insID,
				pmUserCache_insDT,
				pmUser_updID,
				pmUserCache_updDT,
				pmUser_groups,
				pmUser_deleted,
				pmUser_desc,
				pmUser_AccessMatrix,
				pmUser_Phone,
				pmUser_PhoneAct,
				pmUser_IsMessage,
				pmUser_IsSMS,
				pmUser_IsEmail,
				pmUser_GroupType,
				pmUser_PolkaGroupType,
				pmUser_EvnClass
			)
			values(
				:pmUser_id,
				:pmUser_Login,
				:pmUser_Name,
				:pmUser_surName,
				:pmUser_firName,
				:pmUser_secName,
				:pmUser_Email,
				:pmUser_Avatar,
				:pmUser_About,
				:pmUser_Blocked,
				:Lpu_id,
				:MedPersonal_id,
				:pmUser_insID,
				dbo.tzGetDate(),
				:pmUser_updID,
				dbo.tzGetDate(),
				:pmUser_groups,
				1, 		--pmUser_deleted
				:pmUser_desc,
				:pmUser_AccessMatrix,
				:pmUser_Phone,
				:pmUser_PhoneAct,
				:pmUser_IsMessage,
				:pmUser_IsSMS,
				:pmUser_IsEmail,
				:pmUser_GroupType,
				:pmUser_PolkaGroupType,
				:pmUser_EvnClass
			)
		END
		ELSE
		BEGIN
			DELETE pmUserCacheOrg FROM pmUserCacheOrg puco with(rowlock) INNER JOIN pmUserCache puc with(nolock) ON puc.PMUser_id = puco.pmUserCache_id WHERE puc.PMUser_Login = :pmUser_Login AND puc.pmUser_id <> :pmUser_id
			DELETE FROM pmUserCache with(rowlock) WHERE pmUser_Login = :pmUser_Login AND pmUser_id <> :pmUser_id
			UPDATE pmUserCache
			SET
				pmUser_Login = :pmUser_Login,
				pmUser_Name = :pmUser_Name,
				pmUser_surName = :pmUser_surName,
				pmUser_firName = :pmUser_firName,
				pmUser_secName = :pmUser_secName,
				pmUser_Email = :pmUser_Email,
				pmUser_Avatar = :pmUser_Avatar,
				pmUser_About = :pmUser_About,
				pmUser_Blocked = :pmUser_Blocked,
				Lpu_id = :Lpu_id,
				MedPersonal_id = :MedPersonal_id,
				pmUser_updID = :pmUser_updID,
				pmUserCache_updDT = dbo.tzGetDate(),
				pmUser_groups = :pmUser_groups,
				pmUser_delDT = null,
				pmUser_desc = :pmUser_desc,
				pmUser_AccessMatrix = :pmUser_AccessMatrix,
				pmUser_Phone = :pmUser_Phone,
				pmUser_PhoneAct = :pmUser_PhoneAct,
				pmUser_IsMessage = :pmUser_IsMessage,
				pmUser_IsSMS = :pmUser_IsSMS,
				pmUser_IsEmail = :pmUser_IsEmail,
				pmUser_GroupType = :pmUser_GroupType,
				pmUser_PolkaGroupType = :pmUser_PolkaGroupType,
				pmUser_EvnClass = :pmUser_EvnClass
			WHERE
				pmUser_id = :pmUser_id
		END";

		$settings = @unserialize($user->settings);

		$queryParams['pmUser_id'] = $user->pmuser_id;
		$queryParams['pmUser_Login'] = toAnsi($user->login);
		$queryParams['pmUser_Name'] = toAnsi($user->surname . " " . $user->firname);
		$queryParams['pmUser_surName'] = empty($user->surname) ? null : mb_substr(toAnsi($user->surname), 0, 40);
		$queryParams['pmUser_firName'] = empty($user->firname) ? null : mb_substr(toAnsi($user->firname), 0, 30);
		$queryParams['pmUser_secName'] = empty($user->secname) ? null : mb_substr(toAnsi($user->secname), 0, 30);
		$queryParams['pmUser_Email'] = toAnsi($user->email);
		$queryParams['pmUser_Avatar'] = toAnsi($user->avatar);
		$queryParams['pmUser_About'] = toAnsi($user->about);
		$queryParams['pmUser_Blocked'] = $user->blocked;
		$queryParams['pmUser_desc'] = toAnsi($user->desc);
		$queryParams['pmUser_AccessMatrix'] = toAnsi($user->deniedarms);
		$queryParams['pmUser_Phone'] = $user->phone;
		$queryParams['pmUser_PhoneAct'] = $user->phone_act;
		$queryParams['pmUser_IsMessage'] = !empty($settings['notice']['evn_notify_is_message'])?$settings['notice']['evn_notify_is_message']:0;
		$queryParams['pmUser_IsSMS'] = !empty($settings['notice']['evn_notify_is_sms'])?$settings['notice']['evn_notify_is_sms']:0;
		$queryParams['pmUser_IsEmail'] = !empty($settings['notice']['evn_notify_is_email'])?$settings['notice']['evn_notify_is_email']:0;
		$queryParams['pmUser_GroupType'] = //для стационара
			!empty(	$settings['notice']['evn_notify_person_group_type'])
			?		$settings['notice']['evn_notify_person_group_type']
			:1;
		$queryParams['pmUser_PolkaGroupType'] = //для поликлиники
			!empty(	$settings['notice']['evn_notify_person_polka_group_type'])
			?		$settings['notice']['evn_notify_person_polka_group_type']
			:2;

		$this->load->model("MedPersonal_model", "MedPersonal_model");
		// проверка на существование MedPersonal_id в бд. (если не существует, записываем NULL в кэш.)
		if ($this->MedPersonal_model->checkMedPersonalExist($user->medpersonal_id)) {
			$queryParams['MedPersonal_id'] = empty($user->medpersonal_id) || !is_numeric($user->medpersonal_id) ? NULL : $user->medpersonal_id;
		} else {
			$queryParams['MedPersonal_id'] = NULL;
		}
		$queryParams['pmUser_insID'] = $data['pmUser_id']; // берем из сессии, так как из контроллера передаются только данные по редактируемому человеку
		$queryParams['pmUser_updID'] = $data['pmUser_id']; // берем из сессии, так как из контроллера передаются только данные по редактируемому человеку

		$groups = array();
		foreach($user->groups as $g) {
			$groups[]['name'] = $g->name;
		}
		$queryParams['pmUser_groups'] = json_encode($groups);

		$this->load->model('Org_model', 'orgmodel');

		if (isset($user->org[0])) {
			$queryParams['Lpu_id'] = $this->orgmodel->getLpuOnOrg(array('Org_id' => $user->org[0]['org_id'])); // для совсместимости со старым кодом. а вообще у пользователя несколько ЛПУ может быть.
		}
		else {
			$queryParams['Lpu_id'] = NULL;
		}

		$settings['notice']['is_perinatal_haemorrhage'] = empty($settings['notice']['is_perinatal_haemorrhage']) ? 1 : "";

		$EvnClass_arr = array();
		$notice_settings_arr = array('evn_notify_is_message','evn_notify_is_sms','evn_notify_is_email','evn_notify_person_group_type','evn_notify_person_polka_group_type','evn_notify_is_perinatal_haemorrhage');
		if (!empty($settings['notice'])) {
			foreach ($settings['notice'] as $key => $value) {
				if (!in_array($key, $notice_settings_arr)) {
					if ($value) {
						$EvnClass_arr[] = array('sysnick' => $key);
					}
				}
			}
		}
		$queryParams['pmUser_EvnClass'] = json_encode($EvnClass_arr);

		$res = $this->db->query($sql, $queryParams);
		
		//Обновление групп
		$this->removeGroupLink([
			'id' => $user->pmuser_id
		]);
		foreach($groups as $group) {
			if ( isSuperAdmin() || $group['name'] != 'SuperAdmin' ) {
				$group_id = $this->getFirstResultFromQuery("
					select top 1 pmUserCacheGroup_id
					from v_pmUserCacheGroup with(nolock)
					where pmUserCacheGroup_SysNick = :name
				", $group);
				if ($group_id) {
					$this->addGroupLink(array_merge($data, [
						'group' => $group_id,
						'id' => $user->pmuser_id
					]));
				}
			}
		}

		// перекэшируем организации пользователя
		$orgs = array();
		foreach($user->org as $org) {
			$orgs[] = $org['org_id'];
		}
		$userdata = array();
		$userdata['pmuser_id'] = $user->pmuser_id;
		$this->ReCacheUserOrgs($userdata, $orgs);

		return true;
	}

	/**
	*	Удаление пользователя из кэша (отметка удаленным)
	*/
	function deleteUserOfCache($user, $delete = false) {
		if( !$user || !$user['pmUser_id'] )
			return false;

		if ($delete) {
			$query = "	
				delete from pmUserCacheOrg with(rowlock) where pmUserCache_id = :pmUser_id
				delete from pmUserCache with(rowlock) where pmUser_id = :pmUser_id
			";
		} else {
			$query = "	
				declare @is_deleted int
				set @is_deleted = (select pmUser_deleted from pmUserCache with(nolock) where pmUser_id = :pmUser_id)
				if isnull(@is_deleted, 1) <> 2
				begin
					update
						pmUserCache
					set
						pmUser_deleted = 2,
						pmUser_updID = :pmUser_delID,
						pmUser_delDT = dbo.tzGetDate()
					where
						pmUser_id = :pmUser_id
				end
			";
		}

		$res = $this->db->query($query, array('pmUser_id' => $user['pmUser_id'], 'pmUser_delID' => (!empty($user['pmUser_delID']) ? $user['pmUser_delID'] : null)));
		if ( is_object($res) )
 	    	return true;
 	    else
 	    	return false;
	}

	/**
	 *	Восстановление пользователя в кэше
	 */
	function restoreUserOfCache($user) {
		if( !$user || !$user['pmUser_id'] )
			return false;

		$query = "	
			declare @is_deleted int
			set @is_deleted = (select pmUser_deleted from pmUserCache with(nolock) where pmUser_id = :pmUser_id)
			if isnull(@is_deleted, 1) <> 1
			begin
				update
					pmUserCache
				set
					pmUser_deleted = 1,
					pmUser_delDT = dbo.tzGetDate()
				where
					pmUser_id = :pmUser_id
			end
		";

		$res = $this->db->query($query, array('pmUser_id' => $user['pmUser_id']));
		if ( is_object($res) )
 	    	return true;
 	    else
 	    	return false;
	}

	/**
	 * Получение данных для дерева фильтрации в форме просмотра групп
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getGroupTree($data)
	{
		// В зависимости от уровня получаем разные данные
		switch ($data['level']) {
			case 0:
				// Для первого запроса это просто список
				$response = array(
					array('id'=>'All', 'text'=>'Все', 'iconCls'=>'group_all16', 'leaf' => true),
					// TODO: Продумать фильтрацию и открыть
					//array('id'=>'Common', 'text'=>'Общие', 'iconCls'=>'group_common16', 'leaf' => true),
					//array('id'=>'Org', 'text'=>'Организации', 'iconCls'=>'inbox16', 'leaf' => false)
					array('id'=>'Blocked', 'text'=>'Заблокированные', 'iconCls'=>'group_blocked16', 'leaf' => true),
				);
				return $response;
				break;
			case 1:
				if ($data['node'] == 'Org') {
					// Для второго запроса это список ЛПУ
					$query = "
						Select
							Lpu_id as id,
							'lpu16' as iconCls,
							'true' as leaf,
							Lpu_Nick as text
						from
							v_Lpu with (nolock)
						where
							Lpu_id = :Lpu_id or :Lpu_id is null
						order by
							Lpu_Nick
					";
					// TODO: Данное условие нужно будет поменять на более правильное
					if (isSuperAdmin()) {
						$data['Lpu_id'] = null;
					}
					$result = $this->db->query($query, $data);
					if ( is_object($result) ) {
						$response = $result->result('array');
						return $response;
					}
					else {
						return false;
					}
				}
				return false;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Получение списка объектов и ролей для определенного типа объекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectRoleList($data) {

		$list = $this->getObjectList($data, true);

		$role = pmAuthGroups::loadRole($data['Role_id']);
		if ((count($role)>0) && isset($role[$data['node']])) {
			// Если есть права, то берем их
			$c = $this->getObjectActionsList($data);
			for ($i=0; $i<count($list); $i++) {
				if (isset($role[$data['node']][$list[$i]['id']])) {
					$actions = $role[$data['node']][$list[$i]['id']];
					foreach ($actions as $key => $val) {
						if (isset($list[$i]['actions']) && (!in_array($key, array('view','edit'))) && !in_array($key, $list[$i]['actions'])) {
							$list[$i][$key] = 'hidden';
						} else {
							if ($val === 'hidden') { $val = 0; }
							$list[$i][$key] = $val;
						}
					}
				}

				// добавляем права которых нет..
				for ($ii=0; $ii<count($c); $ii++) {
					$key = $c[$ii]['id'];
					if (!array_key_exists($key, $list[$i])) {
						if (isset($list[$i]['actions']) && (!in_array($key, array('view','edit'))) && !in_array($key, $list[$i]['actions'])) {
							$list[$i][$key] = 'hidden';
						} else {
							$list[$i][$key] = false;
						}
					}
				}

				unset($list[$i]['actions']);
			}
		} else {
			// Еще нет никаких прав, мо умолчанию все запрещено
			$c = $this->getObjectActionsList($data);
			for ($i=0; $i<count($list); $i++) {
				for ($ii=0; $ii<count($c); $ii++) {
					$key = $c[$ii]['id'];
					if (isset($list[$i]['actions']) && (!in_array($key, array('view','edit'))) && !in_array($key, $list[$i]['actions'])) {
						$list[$i][$key] = 'hidden';
					} else {
						$list[$i][$key] = false;
					}
				}
				unset($list[$i]['actions']);
			}
		}
		// print_r($list);
		return $list;
	}

	/**
	 * Сохранение роли объекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function saveObjectRole($data, $roles) {
		//print_r(array($data['node']=>json_decode($data['data'], true)));
		$role = pmAuthGroups::loadRole($data['Role_id']);
		$role = array_merge($role, array($data['node']=>$roles)); // Спасибо, что массив
		// Объединяем два массива
		// $role_edit = array_merge($role, $role_edit);
		pmAuthGroups::saveRole($data['Role_id'], $role);
		return true;
	}

	/**
	 * Возвращает простой массив разрешенных акшенов по общему массиву всех групп из LDAP (пример формата возвращаемого файла: array('swAboutAction', 'swExitAction'))
	 * @return array
	 */
	function getSimpleMenuActions($roles) {
		$simple = array();
		foreach ($roles as $k=>$v) {
			if (isset($v['access']) && ($v['access'] == 1)) {
				$simple[] = $k;
			}
		}
		return $simple;
	}

	/**
	 * Возвращает список всех акшенов меню для формы просмотра и редактирования роли
	 * @return array
	 */
	function getMenusList() {
        /**
         * PHPDoc
         */
		function get($menu, $lvl, $actions, $group) {
			foreach ($menu as $k=>$v) {
				if (is_array($v)) {
					if (isset($v['action'])) {
						// Собираем акшены
						// Формат: Код / Наименование / Группа

						$actions[] = array('id' => $v['action'], 'code' => $v['action'], 'name' => $v['text'], 'group' => $group); // надо группу еще скорее всего
					}
					else {
						// Собираем название группы

						if (isset($v['text']) && ($v['text']!='-')) {
							$newgroup = '';
							if (strlen($group)>0)
								$newgroup = ' / ';
							$newgroup = $group.$newgroup.$v['text'];
						}
						if (isset($v['menu'])) {
							//if (isset($v['this']) && isset($v['menuName']) && ($v['this']=='client')) {
							$actions = get($v['menu'], $lvl+1, $actions, $newgroup);
						}
						if (in_array($k, array('menu_normal','menu_advanced'))) {
							$actions = get($v, $lvl+1, $actions, $newgroup);
						}
					}
				}
			}
			return $actions;
		}
		$this->load->helper('Config');
		$this->load->helper('Options');

		// выбираем установленное меню
		$menu = filetoarray(APPPATH.'config/menu.php');
		$menu = $menu['menu_normal'];
		if (count($menu)>0) {
			// Формирование меню с наложением прав на существующее меню
			return get($menu,0, array(), '');

		}
		return array();
	}


	/**
	 * Получение списка объектов для определенного типа объекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectList($data, $list = true) {
		// В зависимости от типа объекта получаем разные данные

		switch ($data['node']) {
			case 'menus':
				$menu = $this->getMenusList();
				if (count($menu)>0) {
					return $menu;
				}
				return false;
				break;
			case 'windows':
				$this->load->helper('Config');

				// выбираем список доступных файлов
				$files = filetoarray(APPPATH.'config/files.php');
				if (count($files)>0) {
					// Если возвращаем список файлов
					$f = array();
					foreach ($files as $key=>$value) {
						// TODO: Возможно здесь надо будет возвращать файлы для всех регионов
						if (isset($files[$key]['path'])) {
							$f = $value;
						} elseif (isset($value[$_SESSION['region']['nick']])) {
							$f = $value[$_SESSION['region']['nick']];
						} elseif (isset($value['default'])) {
							$f = $value['default'];
						}
						if ($list) {

							$r[] = array('id' => $key, 'code' => $key, 'name' => (isset($f['title']))?$f['title']:array(), 'actions' => (isset($f['actions']))?$f['actions']:array(), 'group' => (isset($f['group']))?$f['group']:null, 'region' => (isset($f['region']))?$f['region']:null, 'iconCls' => 'windows16', 'path' => $f['path']);
						}
						else
							$r[] = array('id' => $key, 'code' => $key, 'text' => $f['title'], 'iconCls' => 'windows16', 'path' => $f['path'], 'leaf' => false);
					}
					return $r;
				}
				return false;
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * Получение данных для дерева выбора типа объекта для фильтрации
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectTree($data) {
		// В зависимости от уровня получаем разные данные
		switch ($data['level']) {
			case 0:
				// Для первого запроса это просто список
				$response = $this->getObjectType($data);
				foreach ($response as $key => $val) {
					$response[$key]['leaf'] = true;
				}
				return $response;
				break;
			case 1:
				return $this->getObjectList($data, false);
				break;
			default:
				return false;
				break;
		}
	}


	/**
	 * Получение списка всех доступных типов объектов
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectType($data) {
		return array(
			/*
			array('id'=>'objects', 'text'=>'Бизнес-объекты', 'iconCls'=>'object16'),
			array('id'=>'documents', 'text'=>'Учетные документы', 'iconCls'=>'object16'),
			array('id'=>'events', 'text'=>'События', 'iconCls'=>'object16'),
			array('id'=>'directories', 'text'=>'Справочники', 'iconCls'=>'object16'),
			*/
			array('id'=>'menus', 'text'=>'Меню', 'iconCls'=>'object16'),
			array('id'=>'windows', 'text'=>'Окна', 'iconCls'=>'object16'),
			array('id'=>'actions', 'text'=>'Действия', 'iconCls'=>'object16')
		);
	}

	/**
	 * Получение списка всех доступных типов акшенов
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getActionType() {
		return array(
			array('id'=>'access', 'text'=>'Доступ'),
			array('id'=>'view', 'text'=>'Просмотр'),
			array('id'=>'add', 'text'=>'Добавление'),
			array('id'=>'edit', 'text'=>'Изменение'),
			array('id'=>'delete', 'text'=>'Удаление'),
			array('id'=>'import', 'text'=>'Импорт'),
			array('id'=>'export', 'text'=>'Экспорт'),
			array('id'=>'run', 'text'=>'Запуск'),
			array('id'=>'sign', 'text'=>'Подписание'),
			array('id'=>'print', 'text'=>'Печать')
		);
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного типа оъекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectActionType($objecttype) {
		switch ($objecttype) {
			case 'menus':
				return array('access');
				break;
			case 'windows':
				return array('view', 'add', 'edit', 'delete', 'import', 'export');
				break;
			case 'actions':
				return array('access');
				break;
		}
		return false;
	}

	/**
	 * Возвращает список разрешенных типов акшенов для определенного оъекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectActionsList($data) {
		$approved = $this->getObjectActionType($data['node']);
		$actions = array();
		foreach ($actiontypes = $this->getActionType() as $key => $val) {
			if ( in_array($actiontypes[$key]['id'], $approved) ) {
				$actions[] = $actiontypes[$key];
			}
		}
		return $actions;
	}

	/**
	 * Формирует заголовок для грида для определенного типа оъекта
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getObjectHeaderList($data) {
		$r = array();
		// Получаем разрешенные для данного типа объекта акшены
		$this->load->helper('Config');
		$actions = $this->getObjectActionsList($data);
		switch ($data['node']) {
			case 'menus':
				$r = array('id'=>'Id', 'code'=>'Код', 'name'=>'Название пункта меню');

				foreach ($actions as $key => $val) {
					//print_r($actions);
					$r[$val['id']] = $val['text'];
				}
				return array($r);
				break;
			case 'windows':

				// формируем заголовки для грида
				$r = array('id'=>'Id', 'code'=>'Код', 'name'=>'Название формы ввода');

				foreach ($actions as $key => $val) {
					//print_r($actions);
					$r[$val['id']] = $val['text'];
				}
				return array($r);
				break;
			default:
				break;
		}
		return false;
	}

    /**
     * выводит список организаций пользователя через запятую..
     */
	function getOrgsByUser($pmUser_id)
	{
		$query = "
			DECLARE @str AS varchar(MAX);
			DECLARE @separator AS varchar(50);
			SET @separator = ', ';

			SELECT @str = COALESCE(@str + @separator, '') + o.Org_Nick
			FROM v_pmUserCacheOrg puco with (nolock)
				left join v_Org o with (nolock) on o.Org_id = puco.Org_id
			WHERE puco.pmUserCache_id = :pmUser_id
			ORDER BY o.Org_Nick;
			
			SELECT @str as orgs;
		";

		$res = $this->db->query($query, array('pmUser_id' => $pmUser_id));
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['orgs'];
			}
		} else {
			return '';
		}
	}
    /**
     * Это Doc-блок
     */
	function getUsersList($data)
	{
		$filter = "1=1";
		$params = array();

		if (!empty($data['OrgType_id'])) {
			$filter .= " and exists(
				Select top 1 
					puco.pmUserCacheOrg_id 
				from v_pmUserCacheOrg puco with (nolock)
					inner join v_Org o with (nolock) on o.Org_id = puco.Org_id
				where o.OrgType_id = :OrgType_id and puco.pmUserCache_id = puc.PMUser_id)";
			$params['OrgType_id'] = rtrim($data['OrgType_id']);
			// $data['org'] = null;
		}
		// фильтр по выбранному узлу.
		if ($data['org'] !== null) {
			if ($data['org'] == 'deleted' && !isSuperadmin() && !isLpuAdmin()) {
				// DieWithError('У вас нет прав для просмотра этих пользователей');
				return false;
			}
			else if ($data['org'] == '0' && !isSuperadmin()) {
				// DieWithError('У вас нет прав для просмотра этих пользователей');
				return false;
			}

			// удалённые пользователи
			if ($data['org'] == 'deleted' || (isset($data['pmUser_deleted']) && $data['pmUser_deleted'] == 'deleted') ) {
				$filter .= " and isnull(pmUser_deleted, 1) = 2";
			} else {
				$filter .= " and isnull(pmUser_deleted, 1) = 1";
			}

			// прочие (те, у кого нет организации)
			if ($data['org'] == '0') {
				$filter .= " and not exists(Select top 1 puco.pmUserCacheOrg_id from v_pmUserCacheOrg puco with (nolock) where puco.pmUserCache_id = puc.PMUser_id)";
			}

			// администраторы сети аптек
			if ($data['org'] == 'farmnetadmin') {
				$filter .= " and puc.pmUser_groups like '%\"FarmacyNetAdmin\"%'";
			}

			// пользователи конкретной организации
			if (is_numeric($data['org']) && $data['org'] > 0) {
				$filter .= " and exists(Select top 1 puco.pmUserCacheOrg_id from v_pmUserCacheOrg puco with (nolock) where puco.Org_id = {$data['org']} and puco.pmUserCache_id = puc.PMUser_id)";
			}
		}

		// для админа лпу только своих пользователей.
		if (!isSuperadmin() && !defined('CRON')) {
			$filter .= " and exists(Select top 1 puco.pmUserCacheOrg_id from v_pmUserCacheOrg puco with (nolock) where puco.Org_id IN (".implode(',', $data['session']['orgs']).") and puco.pmUserCache_id = puc.PMUser_id)";
		}

		// фильтры на форме
		if(!empty($data['login'])) {
			$filter .= " and puc.PMUser_Login like :PMUser_Login + '%'";
			$params['PMUser_Login'] = $data['login'];
		}

		if (!empty($data['group'])) {
			$filter .= " and puc.pmUser_groups like '%\"'+:group+'\"%'";
			$params['group'] = $data['group'];
		}

		if (!empty($data['pmUser_surName'])) {
			$filter .= " and puc.PMUser_surName like :pmUser_surName + '%'";
			$params['pmUser_surName'] = rtrim($data['pmUser_surName']);
		}

		if (!empty($data['pmUser_firName'])) {
			$filter .= " and puc.PMUser_firName like :pmUser_firName + '%'";
			$params['pmUser_firName'] = rtrim($data['pmUser_firName']);
		}

		if (!empty($data['pmUser_desc'])) {
			$filter .= " and puc.pmUser_desc like + '%' + :pmUser_desc + '%'";
			$params['pmUser_desc'] = rtrim($data['pmUser_desc']);
		}

		if (!empty($data['pmUser_Blocked'])) {
			$filter .= " and isnull(puc.pmUser_Blocked, 0) = :pmUser_Blocked";
			$params['pmUser_Blocked'] = $data['pmUser_Blocked'] == 2 ? 1 : 0;
		}

		$query = "
			select
				-- select
				puc.PMUser_id as pmUser_id,
				rtrim(puc.PMUser_Login) as login,
				rtrim(puc.PMUser_surName) as PMUser_surName,
				rtrim(puc.PMUser_firName) as PMUser_firName,
				rtrim(puc.PMUser_secName) as PMUser_secName,
				rtrim(puc.PMUser_Name) as PMUser_Name,
				case when ISNULL(puc.PMUser_Blocked, 0) = 1
					then 'true' 
					else 'false'
				end as PMUser_Blocked,
				case when puc.MedPersonal_id is not null
					then 'true' 
					else 'false'
				end as IsMedPersonal,
				puc.pmUser_groups as groups,
				puc.pmUser_desc,
				puc.Lpu_id
				-- end select
			from
				-- from
				pmUserCache puc with (nolock)
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				puc.PMUser_Login
				-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
		if (!empty($data['withoutPaging']) && $data['withoutPaging']) {
			$res = $this->db->query($query, $params);
			if ( is_object($res) ) {
				return $res->result('array');
			} else {
				return array();
			}
		} else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
			$result_count = $this->db->query(getCountSQLPH($query), $params);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				foreach ($response['data'] as &$oneres) {
					if (!empty($oneres['groups'])) {
						$arr = array();
						$groups = json_decode($oneres['groups']);
						foreach($groups as $group) {
							$arr[] = $group->name;
						}
						$oneres['groups'] = implode(',',$arr);
					}
					$oneres['orgs'] = $this->getOrgsByUser($oneres['pmUser_id']);
				}
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		}
	}
    /**
     * Это Doc-блок
     */
	function getUsersListOfCache($data)
	{
		$filter = "1=1";
		$params = array();
		if( !empty($data['Org_id']) ) {
			$filter .= " and exists(Select top 1 puco.pmUserCacheOrg_id from v_pmUserCacheOrg puco with (nolock) where puco.Org_id = :Org_id and puco.pmUserCache_id = puc.PMUser_id)";
			$params['Org_id'] = $data['Org_id'];
		}
		if( !empty($data['login']) ) {
			$filter .= " and puc.PMUser_Login like :PMUser_Login + '%'";
			$params['PMUser_Login'] = $data['login'];
		}
		if( !empty($data['desc']) ) {
			$filter .= " and puc.PMUser_desc like '%' + :PMUser_desc + '%'";
			$params['PMUser_desc'] = $data['desc'];
		}
		if( !empty($data['Person_SurName']) ) {
			$filter .= " and puc.PMUser_surName like :Person_SurName + '%'";
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		if( !empty($data['Person_FirName']) ) {
			$filter .= " and puc.PMUser_firName like :Person_FirName + '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		if( !empty($data['Person_SecName']) ) {
			$filter .= " and puc.PMUser_secName like :Person_SecName + '%'";
			$params['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if( !empty($data['group']) ) {
			$params['pmUserCacheGroup_id'] = $this->getFirstResultFromQuery("select pmUserCacheGroup_id from v_pmUserCacheGroup (nolock) where pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick", array(
				'pmUserCacheGroup_SysNick' => $data['group']
			), true);
			$filter .= " and exists(Select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = :pmUserCacheGroup_id and pucgl.pmUserCache_id = puc.PMUser_id)";
			$params['group'] = $data['group'];
		}

		$filter .= " and isnull(pmUser_deleted, 1) <> 2";

		$query = "
			select
				-- select
				puc.PMUser_id as pmUser_id,
				puc.PMUser_Login as login,
				puc.PMUser_surName as surname,
				puc.PMUser_firName as name,
				puc.PMUser_secName as secname,
				case when puc.MedPersonal_id is not null
					then 1 
					else 0
				end as IsMedPersonal,
				puc.pmUser_groups as groups,
				puc.pmUser_desc as [desc]
				-- end select
			from
				-- from
				pmUserCache puc with (nolock)
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				puc.PMUser_surName
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

    /**
     * action == add    -->  Список АРМов (из v_ARMType), у которых еще нет доступа к нашему отчету
     * action == remove -->  Список АРМов (из v_ReportARM), у которых есть доступ к отчету
     */
    function GetARMSOnReport($data){
        $params = array();
        if($data['action'] == 'add'){

        	if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
				$subQuery = "
					select RCPL.ReportContentParameterLink_id
					from 
						rpt.v_ReportContentParameterLink RCPL (nolock)
					where 
						RCPL.ReportContentParameter_id = :ReportContentParameter_id
						and RCPL.ARMType_id = AT.ARMType_id
				";
				$params['ReportContentParameter_id'] = $data['ReportContentParameter_id'];
			} else {
				$subQuery = "
			        select RA.ReportARM_id
                    from 
                    	rpt.v_ReportARM RA (nolock)
                    where 
                    	RA.ARMType_id = AT.ARMType_id
                    	and RA.Report_id = :Report_id
			    ";
			    $params['Report_id'] = $data["Report_id"];
        	}
        		
            $query = "
                select AT.ARMType_id
                from v_ARMType AT (nolock)
                where not exists ({$subQuery})
            ";
        }
        else if($data['action'] == 'remove'){
        	
            
        	if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
				$query = "
					select RCPL.ReportContentParameterLink_id
					from rpt.v_ReportContentParameterLink RCPL (nolock)
					where RCPL.ReportContentParameter_id = :ReportContentParameter_id
				";
				$params['ReportContentParameter_id'] = $data['ReportContentParameter_id'];
			} else {
				$query = "
			        select RA.ReportARM_id
			        from rpt.v_ReportARM RA (nolock)
			        where RA.Report_id = :Report_id
			    ";
			    $params['Report_id'] = $data["Report_id"];
        	}
            
        }
        else{
            return false;
        }

        $result = $this->db->query($query,$params);
        if(is_object($result)){
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Это Doc-блок
     */
	function saveReportARM($data) {
		$proc = 'p_ReportARM_ins';
		$field = 'Report_id';
		$keyField = 'ReportARM_id';
		if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
			$proc = 'p_ReportContentParameterLink_ins';
			$field = $data['idField'];
			$keyField = "ReportContentParameterLink_id";
		}


		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rpt.{$proc}
				@{$keyField} = @Res output,
				@ARMType_id = :ARMType_id,
				@{$field} = :{$field},
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ReportARM_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSql($query, $data); exit();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function deleteReportARM($data) {

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rpt.p_ReportARM_del
				@ReportARM_id = :ReportARM_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if (!empty($data['idField']) && $data['idField'] == 'ReportContentParameter_id') {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec rpt.p_ReportContentParameterLink_del
					@ReportContentParameterLink_id = :ReportContentParameterLink_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		}
		
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	*	Проверка на существование связи АРМа и отчета (право доступа)
	*/
	function checkOnIssetReportARM(&$data) {

		$query = "
			select top 1
				ReportARM_id
			from
				rpt.v_ReportARM with(nolock)
			where
				ARMType_id = :ARMType_id
				and Report_id = :Report_id
		";

		if (!empty($data['ReportContentParameter_id'])) {
			$query = "
				select top 1
					ReportContentParameterLink_id
				from
					rpt.v_ReportContentParameterLink with(nolock)
				where
					ARMType_id = :ARMType_id
					and ReportContentParameter_id = :ReportContentParameter_id
			";
		}

		
		//echo getDebugSql($query, $data); exit();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if( !empty($res[0]['ReportARM_id']) ) {
				$data['ReportARM_id'] = $res[0]['ReportARM_id'];
			}
			if( !empty($res[0]['ReportContentParameterLink_id']) ) {
				$data['ReportContentParameterLink_id'] = $res[0]['ReportContentParameterLink_id'];
			}
			return count($res) >= 1;
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function getARMsAccessOnReport($data) {
		$fields = '';
		$join = '';
		$queryParams = array();
		if (!empty($data['ReportContentParameter_id'])) {
			$query = "
				select
					AT.ARMType_id
					,AT.ARMType_Code
					,AT.ARMType_Name + '(' + AT.ARMType_SysNick + ')' as ARMType_Name
					,:ReportContentParameter_id as ReportContentParameter_id
					,R.ReportContentParameterLink_id
					,null as ReportARM_id
					,null as Report_id
					,case when R.ReportContentParameterLink_id is not null then 1 else 0 end as isAccess
				from
					v_ARMType AT with(nolock)
					left join rpt.v_ReportContentParameterLink R with(nolock) on R.ARMType_id = AT.ARMType_id and R.ReportContentParameter_id = :ReportContentParameter_id
			";

			$queryParams['ReportContentParameter_id'] = $data['ReportContentParameter_id'];

		} else {
			$query = "
				select
					AT.ARMType_id
					,AT.ARMType_Code
					,AT.ARMType_Name + '(' + AT.ARMType_SysNick + ')' as ARMType_Name
					,:Report_id as Report_id
					,R.ReportARM_id
					,null as ReportContentParameterLink_id
					,null as ReportContentParameter_id
					,case when R.ReportARM_id is not null then 1 else 0 end as isAccess	
				from
					v_ARMType AT with(nolock)
					left join rpt.v_ReportARM R with(nolock) on R.ARMType_id = AT.ARMType_id  and R.Report_id = :Report_id
			";
			$queryParams['Report_id'] = $data['Report_id'];
		}

		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function getARMinDB($data) {
		$filter = "1=1";
		$qp = array();

		if( isset($data['ARMType_id']) && $data['ARMType_id'] > 0 ) {
			$qp['ARMType_id'] = $data['ARMType_id'];
			$filter .= " and ARMType_id = :ARMType_id";
		}
		if( isset($data['ARMType_Code']) && $data['ARMType_Code'] > 0 ) {
			$qp['ARMType_Code'] = $data['ARMType_Code'];
			$filter .= " and ARMType_Code = :ARMType_Code";
		}
		if( isset($data['ARMType_SysNick']) ) {
			$qp['ARMType_SysNick'] = $data['ARMType_SysNick'];
			$filter .= " and ARMType_SysNick = :ARMType_SysNick";
		}

		$query = "
			select
				ARMType_id
				,ARMType_Code
				,ARMType_Name as ARMType_realName
				,ARMType_Name + ' (' + ARMType_SysNick + ')' as ARMType_Name
				,ARMType_SysNick
			from
				v_ARMType with(nolock)
			where
				{$filter}
			order by
				ARMType_id
		";
		$res = $this->db->query($query, $qp);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function saveARMinDB($data) {
		$proc = 'p_ARMType_' . (!empty($data['ARMType_id']) ? 'upd' : 'ins');
		$query = "
			declare
				@Res bigint = :ARMType_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$proc}
				@ARMType_id = @Res output,
				@ARMType_Code = :ARMType_Code,
				@ARMType_Name = :ARMType_Name,
				@ARMType_SysNick = :ARMType_SysNick,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as ARMType_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function deleteARMinDB($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_ARMType_del
				@ARMType_id = :ARMType_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function getUsersWithInvalidMedPersonalId($data) {
		$query = "
			Select PMUser_Login, mold.medpersonal_id,m.id
			from pmUserCache 
			inner join tmp.MedPersonalD mold with(nolock) on mold.MedPersonal_id = pmUserCache.MedPersonal_id
			inner join persis.MedWorker m with(nolock) on m.person_id=mold.person_id
			where 
			m.id <>mold.MedPersonal_id
		";
		$res = $this->db->query($query, array());
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Это Doc-блок
     */
	function getCurrentOrgUsersList($Org_id = null) {
		$query = "
			select distinct
				 uc.pmUser_id
				,uc.pmUser_Name as pmUser_Fio
				,uc.pmUser_Login
			from pmUserCache uc with (nolock)
				inner join pmUserCacheOrg uco with (nolock) on uco.pmUserCache_id = uc.pmUser_id
			where
				uco.Org_id = :Org_id
		";
		$res = $this->db->query($query, array('Org_id' => $Org_id));

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Признак того, что пользователь является сотрудником службы мед. статистики
	 */
	function isMedStatUser() {
		$response = false;

		if ( !empty($_SESSION['lpu_id']) && is_numeric($_SESSION['lpu_id']) && !empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) ) {
			$query = "
				select top 1
					t1.MedPersonal_id
				from
					v_MedServiceMedPersonal t1 with (nolock)
					inner join v_MedService t2 with (nolock) on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3 with (nolock) on t3.MedServiceType_id = t2.MedServiceType_id
				where
					t1.MedPersonal_id = :MedPersonal_id
					and t2.Lpu_id = :Lpu_id
					and t3.MedServiceType_SysNick = 'mstat'
					and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
					and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
					and t2.MedService_begDT <= dbo.tzGetDate()
					and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
			";

			$queryParams = array(
				 'Lpu_id' => $_SESSION['lpu_id']
				,'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['MedPersonal_id']) ) {
					$response = true;
				}
			}
		}

		return $response;
	}

	/**
	 *	Признак того, что пользователь является Заведующим и существует действующаю служба с типом «Производственный отдел»
	 */
	function isHeadWithMedService() {
		$response = false;

		if ( !empty($_SESSION['lpu_id']) && is_numeric($_SESSION['lpu_id']) && !empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) ) {
			$query = "
			declare @date datetime = cast(dbo.tzGetDate() as date);
				SELECT top 1
					msmp.MedStaffFact_id as MedStaffFact_id,
					MS.LpuSection_id,
					msmp.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					'Руководитель аптеки' as PostMed_Name,
					6 as PostMed_Code,
					null as PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					null as Timetable_isExists, 
					lut.LpuUnitType_SysNick,
					MS.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					MS.MedService_id,
					MS.MedService_Nick,
					MS.MedService_Name,
					MS.MedServiceType_id,
					mst.MedServiceType_SysNick, 
					ms.MedService_IsExternal,
					msmp.Person_Fio as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					null as MedicalCareKind_id,
					null as PostKind_id,
					null as MedStaffFactCache_IsDisableInDoc
				FROM 
					v_MedService MS with (NOLOCK)
					cross apply (
						Select top 1 msf.MedPersonal_id, msf.MedStaffFact_id, msf.Person_Fio from v_MedStaffFact msf with (NOLOCK)
						left join v_MedPersonal mp with (NOLOCK) on msf.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
						left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
						where msf.MedPersonal_id = :MedPersonal_id and ps.PostMed_Code = 6 -- должность Заведующий
					) as msmp
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = isnull(ls.LpuBuilding_id,MS.LpuBuilding_id)
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = isnull(ls.LpuUnit_id,MS.LpuUnit_id)
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = isnull(lu.LpuUnitType_id,MS.LpuUnitType_id)
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = MS.MedServiceType_id
				where
					MS.Lpu_id = :Lpu_id and
					(1=1)
					and cast(MS.MedService_begDT as date) <= @date and (cast(MS.MedService_endDT as date) >= @date or MS.MedService_endDT is null) 
					and msmp.MedPersonal_id = :MedPersonal_id
					and mst.MedServiceType_SysNick = 'rpo'
			";

			$params = array(
				 'Lpu_id' => $_SESSION['lpu_id']
				,'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$res = $this->db->query($query, $params);
			if ( is_object($res) ) {
				$result = $res->result('array');
				if( count($result)>0 ) {
					return $result[0];
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		return $response;
	}

	/**
	 *	Признак того, что пользователь является патологоанатомом
	 */
	function isPathoMorphoUser() {
		$response = false;

		if ( !empty($_SESSION['lpu_id']) && is_numeric($_SESSION['lpu_id']) && !empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) ) {
			$query = "
				select top 1
					t1.MedPersonal_id
				from
					v_MedServiceMedPersonal t1 with (nolock)
					inner join v_MedService t2 with (nolock) on t2.MedService_id = t1.MedService_id
					inner join v_MedServiceType t3 with (nolock) on t3.MedServiceType_id = t2.MedServiceType_id
				where
					t1.MedPersonal_id = :MedPersonal_id
					and t2.Lpu_id = :Lpu_id
					and t3.MedServiceType_SysNick = 'patb'
					and t1.MedServiceMedPersonal_begDT <= dbo.tzGetDate()
					and (t1.MedServiceMedPersonal_endDT is null or t1.MedServiceMedPersonal_endDT >= dbo.tzGetDate())
					and t2.MedService_begDT <= dbo.tzGetDate()
					and (t2.MedService_endDT is null or t2.MedService_endDT >= dbo.tzGetDate())
			";

			$queryParams = array(
				 'Lpu_id' => $_SESSION['lpu_id']
				,'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['MedPersonal_id']) ) {
					$response = true;
				}
			}
		}

		return $response;
	}

	/**
	 *	Признак того, что врач пользователя включен в регистр главных специалистов
	 */
	function isHeadMedSpecMedPersonal() {
		$response = false;

		if (!empty($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id'])) {
			$query = "
				select top 1
                    hmc.HeadMedSpec_id
                from
                    dbo.v_MedPersonal mp with (nolock)
                    left join persis.v_MedWorker mw with (nolock) on mw.Person_id = mp.Person_id
                    left join dbo.v_HeadMedSpec hmc with (nolock) on hmc.MedWorker_id = mw.MedWorker_Id
                where
                    mp.MedPersonal_id = :MedPersonal_id
			";

			$queryParams = array(
			    'MedPersonal_id' => $_SESSION['medpersonal_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['HeadMedSpec_id']) ) {
					$response = true;
				}
			}
		}

		return $response;
	}

	function getUserSessionsByLogin($login){
		$CI =& get_instance();
		$DB1 = $CI->load->database('phplog', TRUE);
		if($DB1){
			$query = "
            select *
           	from UserSessions us with (nolock)
           	where Login = :login and LogoutTime is null
            order by us.LoginTime desc
        ";

			$sessions = $DB1->query($query, array('login' => $login));
			return $sessions->result('array');
		}

		return array();
	}

    /**
     * @param $data
     * @return array|bool
     */
    function getUserSessions($data) {
		$params = [];
		$filters = [];

		$filters[] = "convert(varchar(10),us.LoginTime,120) >= :Login_Range_0";
		$filters[] = "convert(varchar(10),us.LoginTime,120) <= :Login_Range_1";
		$params["Login_Range_0"] = (!empty($data["Login_Range"][0]) ? $data["Login_Range"][0] : date('Y-m-d'));
		$params["Login_Range_1"] = (!empty($data["Login_Range"][1]) ? $data["Login_Range"][1] : date('Y-m-d'));

	    if (isset($data["Logout_Range"][0])) {
		    $filters[] = "convert(varchar(10),us.LogoutTime,120) >= :Logout_Range_0";
		    $params["Logout_Range_0"] = $data["Logout_Range"][0];
	    }
	    if (isset($data["Logout_Range"][1])) {
		    $filters[] = "convert(varchar(10),us.LogoutTime,120) <= :Logout_Range_1";
		    $params["Logout_Range_1"] = $data["Logout_Range"][1];
	    }
	    if (isset($data["PMUser_Name"])) {
		    $filters[] = "pu.PMUser_Name like '%{$data["PMUser_Name"]}%'";
	    }
	    if (isset($data["PMUser_Login"])) {
		    $filters[] = "(pu.PMUser_Login like '%{$data["PMUser_Login"]}%' or us.Login like '%{$data["PMUser_Login"]}%')";
	    }
	    if (isset($data["IsMedPersonal"])) {
		    $filters[] = ($data["IsMedPersonal"] == 2)
			    ? "pu.MedPersonal_id is not null"
			    : "pu.MedPersonal_id is null";
	    }
	    if (isset($data["IP"])) {
		    $filters[] = "us.IP = :IP";
		    $params["IP"] = $data["IP"];
	    }
	    if (isset($data["AuthType_id"])) {
		    $filters[] = "us.AuthType_id = :AuthType_id";
		    $params["AuthType_id"] = $data["AuthType_id"];
	    }
	    if (isset($data["Status"])) {
		    $filters[] = "us.Status = :Status";
		    $params["Status"] = $data["Status"];
	    }
	    if (isset($data["onlyActive"]) && $data["onlyActive"]) {
		    $filters[] = "us.LogoutTime is null";
	    }
	    $defaultdatabase = $this->load->database("default", true)->database;

	    $mainDb = "{$defaultdatabase}.";
	    $dblink = $this->config->item("UserSessionDBLink");
	    if (!empty($dblink)) {
		    $mainDb = "{$dblink}.{$defaultdatabase}.dbo";
	    }
	    else{
	    	if(getRegionNick() == 'ufa'){
				$mainDb = "{$defaultdatabase}.dbo";
			}
		}
	    if (isSuperadmin()) {
		    if (!empty($data["Org_id"])) {
			    $filters[] = "pu.PMUser_id in (select puco.pmUserCache_id from {$mainDb}.v_pmUserCacheOrg puco (nolock) where puco.Org_id = :Org_id)";
			    $params["Org_id"] = $data["Org_id"];
		    }
	    } else {
		    if (!empty($data["userOrg_id"])) {
			    $filters[] = "pu.PMUser_id in (select puco.pmUserCache_id from {$mainDb}.v_pmUserCacheOrg puco (nolock) where puco.Org_id = :userOrg_id)";
			    $params["userOrg_id"] = $data["userOrg_id"];
		    }
	    }
	    if (!empty($data["PMUserGroup_Name"])) {
		    $filters[] = "pu.PMUser_groups like '%\"'+:PMUserGroup_Name+'\"%'";
		    $params["PMUserGroup_Name"] = $data["PMUserGroup_Name"];
	    }
	    $params["day"] = date("Y-m-d");
	    $filterString = (count($filters) != 0)
		    ? "
	           where
	            -- where
	                " . implode(" and ", $filters) . "
	            -- end where
		    "
		    : "";
	    $query = "
			select
            	-- select
            	REPLACE(REPLACE(REPLACE(REPLACE(convert(varchar(40), us.LoginTime, 121),'-',''),':',''),'.',''),' ','') as Unic_id,
              	us.Session_id,
              	us.IP,
              	pu.PMUser_id,
              	LTRIM(RTRIM(pu.PMUser_Name)) as PMUser_Name,
              	convert(varchar(20), us.LoginTime, 120) as LoginTime,
              	convert(varchar(20), us.LogoutTime, 120) as LogoutTime,
              	IIF(us.Status = 1, datediff(ss,us.LoginTime, ISNULL(us.LogoutTime, GETDATE())), null) as WorkTime,
              	CASE
              	    WHEN us.Status = 1 THEN 'удачный вход'
              	    WHEN us.Status = 2 THEN 'блокировка учетной записи'
              	    ELSE 'неудачный вход'
              	END as Status,
              	IIF(us.Status = 1, 1, 2) as Status_id,
				case
                	when us.AuthType_id = 1 then 'по логину/паролю'
                	when us.AuthType_id = 2 then 'по соцкарте'
                  	when us.AuthType_id = 3 then 'через УЭК'
                    when us.AuthType_id = 4 then 'через ЭЦП'
                    when us.AuthType_id = 5 then 'через ЕСИА'
					else ''
                end as AuthType_id,
              	IIF(pu.PMUser_Login is null, us.Login, pu.PMUser_Login) as PMUser_Login,
              	IIF(pu.MedPersonal_id is not null, 'true', 'false') as IsMedPersonal,
			  	(SELECT COUNT(*) FROM UserSessions with (nolock) WHERE pmUser_id = us.pmUser_id AND LoginTime > :day AND LogoutTime IS NULL) as ParallelSessions
              -- end select
           from
           -- from
	           UserSessions us with (nolock)
	           left join {$mainDb}.v_pmUserCache pu with (nolock) on us.pmUser_id = pu.pmUser_id
           -- end from
	        {$filterString}
            order by
            -- order by
            	us.LoginTime desc
		  	-- end order by
        ";
	    $response = $this->getPagingResponse($query, $params, $data["start"], $data["limit"], true);
	    if (is_array($response) && array_key_exists("data", $response)) {
		    foreach ($response["data"] as $k => $row) {
			    if (!empty($row["WorkTime"])) {
				    $wt = "";
				    $s = $row["WorkTime"];
				    if ($row["WorkTime"] < 60) {
					    $wt = "меньше минуты";
				    } else {
					    // дни
					    $d = floor($s / 86400);
					    $s = $s - ($d * 86400);
					    // часы
					    $h = floor($s / 3600);
					    $s = $s - ($h * 3600);
					    // минуты
					    $m = floor($s / 60);
					    if ($d > 0) {
						    $wt .= "{$d}д ";
					    }
					    if ($h > 0) {
						    $wt .= "{$h}ч ";
					    }
					    if ($m > 0) {
						    $wt .= "{$m}м ";
					    }
				    }
				    $response["data"][$k]["WorkTime"] = $wt;
			    }
		    }
	    }
	    return $response;
    }

	/**
	 * Получает список расшифровок методов
	 * @return array|bool
	 */
    function getMethods() {
	    $query = "
			select
			    Method_ID,
			    Method_Name_Ru
			from Methods
			order by Method_Name_Ru
	    ";
	    /**@var CI_DB_result $result */
	    $result = $this->db->query($query);
	    return (is_object($result)) ? $result->result_array() : false;
    }

	/**
	 * Блокирование пользователей
	 */
	function blockUsers($data) {
		$params = array('pmUser_Blocked' => $data['pmUser_Blocked'] ? 1 : 0);
		$pmuser_ids = json_decode($data['pmUser_ids'], true);

		if (count($pmuser_ids) == 0) {
			return $this->createError('', 'Не были переданы идентификаторы пользователей');
		}

		$query = "
			update pmUserCache
			set pmUser_Blocked = :pmUser_Blocked
			where pmUser_id in (".implode(',', $pmuser_ids).")
		";

		$res = $this->db->query($query, $params);

		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Блок по pmUserCache
	 */
	function getBlockedFromUserCache($login) {
		// TODO: Передалать после того, как весь pmUserCache будет храниться в MongoDB
		$params = array('login'=>$login);
		$query = "
			select top 1
				IsNull(PMUser_Blocked,0) as PMUser_Blocked
			from pmUserCache (nolock)
			where PMUser_Login = :login
		";
		$rs = 0;
		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$response = $result->result('array');
			if (count($response)>0) {
				$rs = $response[0]['PMUser_Blocked'];
			}
		}
		return $rs;
	}

	/**
	 * Проверка пароля
	 */
	function checkPasswordDate($time, $temp = 0) {
		// Новый пароль соответствует указанным требованиям
		$this->load->model("Options_model");
		$options = $this->Options_model->getOptionsGlobals(array('session' => array('login' => '')));

		if ($temp == 1) {
			if (!empty($options['globals']['password_tempexpirationperiod'])) {
				$days = intval($options['globals']['password_tempexpirationperiod']);
				$secs = $days * 24 * 60 * 60;
				if ($time + $secs - time() <= 0) {
					return array('Error_Msg' => 'Срок временного пароля истек. Обратитесь к администратору.');
				}
			}
		} else {
			if (!empty($options['globals']['password_expirationperiod'])) {
				$days = intval($options['globals']['password_expirationperiod']);
				$secs = $days * 24 * 60 * 60;
				if ($time + $secs - time() <= 0) {
					return array('Error_Msg' => 'Срок действия пароля истек. Необходимо ввести новый пароль.');
				}
			}
		}

		return array('Error_Msg' => '');
	}
	/**
	 * Берем один символ из строки в utf-кодировке
	 */
	function char($str, $pos) {
		return mb_substr($str,$pos,1,'UTF-8');
	}

	/**
	 * Проверка пароля
	 */
	function checkPassword($new_password, $old_password, &$user) {
		// Новый пароль соответствует указанным требованиям
		$this->load->model("Options_model");
		$options = $this->Options_model->getOptionsGlobals(array('session' => array('login' => '')));

		$minLength = 6;
		if (!empty($options['globals']['password_minlength'])) {
			$minLength = intval($options['globals']['password_minlength']);
		}

		if (mb_strlen($new_password) < $minLength) {
			return array('Error_Msg' => "Длина пароля должна быть не менее {$minLength} символов");
		}

		if (!empty($options['globals']['password_haslowercase'])) {
			if (preg_match('/[a-zа-я]/u', $new_password) == false) {
				return array('Error_Msg' => "Пароль должен содержать хотя бы одну строчную букву");
			}
		}

		if (!empty($options['globals']['password_hasuppercase'])) {
			if (preg_match('/[A-ZА-Я]/u', $new_password) == false) {
				return array('Error_Msg' => "Пароль должен содержать хотя бы одну прописную букву");
			}
		}

		if (!empty($options['globals']['password_hasnumber'])) {
			if (preg_match('/[0-9]/u', $new_password) == false) {
				return array('Error_Msg' => "Пароль должен содержать хотя бы одну цифру");
			}
		}

		if (!empty($options['globals']['password_hasspec'])) {
			if (preg_match('/[^A-Z^А-Я^a-z^а-я^0-9]/u', $new_password) == false) {
				return array('Error_Msg' => "Пароль должен содержать хотя бы один спецсимвол");
			}
		}

		// Новый пароль отличается от старого на количество символов (>=), указанного в параметрах системы
		$minDiff = 1;
		if (!empty($options['globals']['password_mindifference'])) {
			$minDiff = intval($options['globals']['password_mindifference']);
		}

		if (!empty($old_password)) {
			$diff = 0;
			for ($i = 0; $i < mb_strlen($new_password); $i++) {
				$o = $this->char($old_password, $i);
				$n = $this->char($new_password, $i);
				if (empty($o) || mb_strtolower($n) != mb_strtolower($o)) {
					$diff++;
				}

				if (mb_strlen($new_password) < mb_strlen($old_password)) {
					$diff += mb_strlen($old_password) - mb_strlen($new_password);
				}
			}

			if ($diff < $minDiff) {
				return array('Error_Msg' => "Новый пароль должен отличаться от старого на {$minDiff} " . ru_word_case('символ', 'символа', 'символов', $minDiff));
			}
		}

		$password_last = array();
		if (!empty($user->password_last)) {
			$passwordLast = json_decode($user->password_last);
			if (is_array($passwordLast)) {
				$password_last = $passwordLast;
			}
		}
		if (!empty($user->password_temp) && $user->password_temp != 0){
			array_push($password_last, $user->password_temp);
		}

		$error = "Новый пароль не должен совпадать с одним из предыдущих.";
		$pass = "{MD5}" . base64_encode(md5($new_password, TRUE));
		$checkAllPasswords = $this->getFirstResultFromQuery("select top 1 DataStorage_Value from v_DataStorage DS (nolock) where DS.DataStorage_Name = 'check_passwords_all' and DS.DataStorage_Value = 1");

		if (getRegionNick() == 'kz' || $checkAllPasswords) {
			if (in_array($pass, $password_last)) {
				return array('Error_Msg' => $error);
			}
		} else {
			$countCheckPasswords = $this->getFirstResultFromQuery("select top 1 DataStorage_Value from v_DataStorage DS (nolock) where DS.DataStorage_Name = 'count_check_passwords'");
			if ($countCheckPasswords != '') {
				$tmp_passwords = array_slice($password_last, '-'.$countCheckPasswords);
				if (in_array($pass, $tmp_passwords)) {
					return array('Error_Msg' => $error);
				}
			}
		}

		array_push($password_last, $pass);

		//TODO Какое кол-во старых паролей необходимо хранить?
		if (count($password_last) > 4) {
			// выкидываем первый
			array_shift($password_last);
		}

		$user->password_last = json_encode($password_last);

		return array('Error_Msg' => '');
	}

	/**
	 * Смена пароля
	 */
	function changePassword($data) {
		if (!empty($data['session']['login'])) {
			$user = pmAuthUser::find($data['session']['login']);
			if (empty($user)) {
				return array('Error_Msg' => 'Пользователь не найден');
			}

			// Старый пароль введен верно
			if ("{MD5}" . base64_encode(md5($data['old_password'], TRUE)) != $user->pass) {
				return array('Error_Msg' => 'Старый пароль введён неверно');
			}

			// Значения полей «Новый пароль» и «Повторите пароль» идентичны
			if ($data['new_password'] != $data['new_password_two']) {
				return array('Error_Msg' => 'Пароли не совпадают');
			}

			$check = $this->checkPassword($data['new_password'], $data['old_password'], $user);
			if (!empty($check['Error_Msg'])) {
				return $check;
			}

			$user->pass = "{MD5}" . base64_encode(md5($data['new_password'], TRUE));
			$user->password_temp = 0;
			$user->password_date = time();
			$user->post();

			return array('Error_Msg' => '');
		}

		return array('Error_Msg' => 'Ошибка доступа');
	}
	/**
	 * Получение списка групп пользователя
	 */
	function getUserGroups($data) {
		$params = array(
			'pmUser_id'=>$data['pmUser_id']
		);
		$query = "
			SELECT distinct
				pucg.pmUserCacheGroup_id as Group_id,
				pucg.pmUserCacheGroup_Name as Group_Desc,
				pucg.pmUserCacheGroup_SysNick as Group_Name,
				pucg.pmUserCacheGroup_IsOnly as Group_IsOnly
			FROM
				pmUserCacheGroup pucg with (nolock)
				LEFT JOIN pmUserCacheGroupLink pucgl with (nolock) ON pucgl.pmUserCacheGroup_id = pucg.pmUserCacheGroup_id
			WHERE
				pucgl.pmUserCache_id = :pmUser_id
				and pucg.pmUserCacheGroup_id != 19
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение списка групп
	 */
	function getGroupsDB() {
		$query = "
			SELECT 
				pmUserCacheGroup_id as 'id', 
				pmUserCacheGroup_SysNick as 'name', 
				pmUserCacheGroup_IsOnly as 'isonly',
				pmUserCacheGroup_IsBlocked as 'isblocked',  
				LTRIM(RTRIM(pmUserCacheGroup_Name)) as 'desc'
			FROM pmUserCacheGroup WITH (nolock)
			where pmUserCacheGroup_id != 19
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('object');
		} else {
			return false;
		}
	}
	/**
	 * Проверка на уникальность группы
	 */
	function checkSaveGroupDB($data) {
		$query = "
				SELECT pmUserCacheGroup_id
				FROM pmUserCacheGroup WITH (nolock) 
				WHERE pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick AND
				pmUserCacheGroup_id != ISNULL(:pmUserCacheGroup_id, 0)
			";
		$params = array(
			'pmUserCacheGroup_SysNick'=>$data['Group_Code'],
			'pmUserCacheGroup_id' => $data['pmUserCacheGroup_id']
		);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Сохранение группы
	 */
	function saveGroupDB($data) {
		if ((!isset($data['pmUserCacheGroup_id'])) || ($data['pmUserCacheGroup_id'] <= 0)) {
			$procedure = 'p_pmUserCacheGroup_ins';
		} else {
			$procedure = 'p_pmUserCacheGroup_upd';
		}
		$query = "
		declare
			@Res bigint,
			@ErrCode int,
			@ErrMessage varchar(4000);
		set @Res = :pmUserCacheGroup_id;
		exec " . $procedure . "
			@pmUserCacheGroup_id = @Res output,
			@pmUserCacheGroup_Code = :pmUserCacheGroup_SysNick,
			@pmUserCacheGroup_SysNick = :pmUserCacheGroup_SysNick,
			@pmUserCacheGroup_Name = :pmUserCacheGroup_Name,
			@pmUserCacheGroup_IsOnly = :pmUserCacheGroup_IsOnly,
			@pmUserCacheGroup_ParallelSessions = :pmUserCacheGroup_ParallelSessions,
			@pmUserCacheGroup_IsBlocked = :pmUserCacheGroup_IsBlocked,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
		select @Res as pmUserCacheGroup_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array(
			'pmUserCacheGroup_id' => $data['pmUserCacheGroup_id'],
			'pmUserCacheGroup_Code' => $data['Group_id'],
			'pmUserCacheGroup_SysNick' => $data['Group_Code'],
			'pmUserCacheGroup_ParallelSessions' => $data['Group_ParallelSessions'],
			'pmUserCacheGroup_Name' => $data['Group_Name'],
			'pmUserCacheGroup_IsOnly' => !empty($data['Group_IsOnly']) ? 2 : 1,
			'pmUserCacheGroup_IsBlocked' => !empty($data['Group_IsBlocked']) ? 2 : 1,
			'pmUser_id' => pmAuthUser::find($data['session']['login'])->pmuser_id
		);

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	/**
	 * Удаление группы
	 */
	function deleteGroupDB($data) {
		$params = array(
			'pmUserCacheGroup_id'=>$data['pmUserCacheGroup_id']
		);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :pmUserCacheGroup_id;
			exec p_pmUserCacheGroup_del
				@pmUserCacheGroup_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Добавление записи о группе пользователя
	 */
	function addGroupLink($data) {
		$params = array(
			'pmUserCache_id'=>$data['id'],
			'pmUserCacheGroup_id'=>$data['group'],
			'pmUser_id'=>pmAuthUser::find($data['session']['login'])->pmuser_id
		);
		$query = "
			declare
				@Res bigint,
				@Group_id bigint,
				@User_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = NULL;
			set @Group_id = :pmUserCacheGroup_id;
			set @User_id = :pmUserCache_id;
			exec p_pmUserCacheGroupLink_ins
				@pmUserCacheGroupLink_id = @Res output,
				@pmUserCacheGroup_id = @Group_id,
				@pmUserCache_id = @User_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as pmUserCacheGroup_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Удаление всех записей о группах пользователя
	 */
	function removeGroupLink($data) {
		$params = array(
			'PMUser_id'=>$data['id']
		);
		$query = "
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			set nocount on
			begin try
				delete from pmUserCacheGroupLink with (rowlock)	where pmUserCache_id = :PMUser_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение списка групп для Сервис -> Пользователи -> Группы
	 */
	function loadGroups() {

		$query = "
			SELECT 
				pmUserCacheGroup_id,
				pmUserCacheGroup_Code as Group_id,
				pmUserCacheGroup_Name as Group_Name,
				pmUserCacheGroup_SysNick as Group_Code,
				case when pmUserCacheGroup_IsOnly = 2 then 'true' else 'false' end as Group_IsOnly,
				case when pmUserCacheGroup_IsBlocked = 2 then 'true' else 'false' end as Group_IsBlocked,
				pmUserCacheGroup_ParallelSessions as Group_ParallelSessions,
				pmUserCache.PMUser_Login as pmUser_Name,
				(SELECT COUNT(*) FROM pmUserCacheGroupLink 
					WHERE pmUserCacheGroup_id = 
					pmUserCacheGroup.pmUserCacheGroup_id) as Group_UserCount
			FROM pmUserCacheGroup WITH(NOLOCK)
			LEFT JOIN pmUserCache ON
				pmUserCache.pmUser_id = 
				pmUserCacheGroup.pmUser_insID
		";
		$result = $this->db->query($query);
		return $result->result('array');
	}

	/**
	 * Получение пользователей онлайн
	 */
	function loadOnlineUsersList($data) {
		global $config;

		// тянем список армов
		$ARMList = $this->loadARMList();

		$ARMList['_noarm_'] = array(
			'Arm_id' => -1,
			'Arm_Name' => 'Пользователи, работающие без АРМ'
		);

		// тянем список организаций
		$OrgList = array();
		$resp_org = $this->queryResult("
			select
				Org_id,
				Org_Nick
			from
				Org (nolock)
			where
				Org_IsAccess = 2
		");
		foreach($resp_org as $one_org) {
			$OrgList[$one_org['Org_id']] = $one_org['Org_Nick'];
		}

		if (!empty($config['session_driver']) && $config['session_driver'] == 'mongodb') {
			// тянем все активные сессии из монго, по каждой разбираем сессию, фильтруем, считаем кол-во юзеров по АРМам.
			switch (checkMongoDb()) {
				case 'mongo':
					$this->load->library('swMongodb', array('config_file'=>'mongodbsessions'), 'swmongodb');
					break;
				case 'mongodb':
					$this->load->library('swMongodbPHP7', array('config_file'=>'mongodbsessions'), 'swmongodb');
					break;
			}

			$table = (isset($config['mongodb_session_settings']) && isset($config['mongodb_session_settings']['table']))?$config['mongodb_session_settings']['table']:'Session';

			$wheres = array(
				'logged' => 1
			);
			if (!empty($data['Org_id']) && is_numeric($data['Org_id'])) {
				$wheres['org_id'] = $data['Org_id'];
			}
			if (!empty($data['OrgType_id']) && is_numeric($data['Org_id'])) {
				$wheres['orgtype_id'] = $data['OrgType_id'];
			}
			if (!empty($data['ARMType_SysNick']) && $data['ARMType_SysNick'] != 'null') {
				if ($data['ARMType_SysNick'] == '_noarm_') {
					$wheres['armtype'] = null;
				} else {
					$wheres['armtype'] = $data['ARMType_SysNick'];
				}
			}
			$items = $this->swmongodb->where_gt('updated', time()-1800)->where($wheres)->get($table); // только залогиненные и активные последние полчаса

			$counts = array();
			foreach($items as $item) {
				if (empty($item['armtype'])) {
					$item['armtype'] = '_noarm_';
				}

				if (empty($counts[$item['armtype'].'_'.$item['org_id']])) {
					$counts[$item['armtype'].'_'.$item['org_id']] = array(
						'count' => 1,
						'armtype' => $item['armtype'],
						'org_id' => $item['org_id']
					);
				} else {
					$counts[$item['armtype'].'_'.$item['org_id']]['count']++;
				}
			}

			$resp = array();

			$id = 0;

			foreach($counts as $key => $value) {
				$org = 'Организация не определена (' . $value['org_id'] . ')';
				if (!empty($OrgList[$value['org_id']])) {
					$org = $OrgList[$value['org_id']];
				}

				$id++;
				if (!empty($ARMList[$value['armtype']])) {
					$resp[] = array(
						'OnlineUsers_id' => $id,
						'ARMType_Name' => $ARMList[$value['armtype']]['Arm_Name'],
						'Org_Nick' => $org,
						'Users_Count' => $value['count']
					);
				} else {
					$resp[] = array(
						'OnlineUsers_id' => $id,
						'ARMType_Name' => 'АРМ не определён (' . $value['armtype'] . ')',
						'Org_Nick' => $org,
						'Users_Count' => $value['count']
					);
				}
			}

			return $resp;
		}

		// заглушка
		return array();
	}

	/**
	 * Получение списка учетных данных пользователей по организации
	 */
	function loadPMUserCacheOrgList($data) {
		$params = array('Org_id' => $data['Org_id']);
		$filters = array("PUO.Org_id = :Org_id");

		if (!empty($data['pmUserCacheOrg_id'])) {
			$filters[] = "PUO.pmUserCacheOrg_id = :pmUserCacheOrg_id";
			$params['pmUserCacheOrg_id'] = $data['pmUserCacheOrg_id'];
		}
		if (!empty($data['query'])) {
			$filters[] = "(PU.pmUser_Login like :query+'%' or PU.pmUser_Name like :query+'%')";
			$params['query'] = $data['query'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				PUO.pmUserCacheOrg_id,
				PUO.Org_id,
				PU.pmUser_id,
				rtrim(PU.pmUser_Login) as pmUser_Login,
				rtrim(PU.pmUser_Name) as pmUser_Name
			from
				v_pmUserCacheOrg PUO with(nolock)
				inner join v_pmUserCache PU with(nolock) on PU.pmUser_id = PUO.pmUserCache_id
			where
				{$filters_str}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Список МО
	 */
	function getLpuList($data) {
		$filter = array();
		$join = array();

		if(!empty($data['MedServiceType_SysNick'])) {
			$filter[] = "MST.MedServiceType_SysNick = :MedServiceType_SysNick";

			$join[] = "left join v_MedService MS with (nolock) on MS.Lpu_id = Lpu.Lpu_id
					left join v_MedServiceType MST with (nolock) on MS.MedServiceType_id = MST.MedServiceType_id";
		}

		$sql = "
			with lpu_ids as (
				SELECT distinct
					Lpu.Lpu_id
				FROM v_Lpu Lpu with (nolock)
					". (count($join) > 0 ? implode(' ', $join) : "") ."
				where ".(count($filter) > 0 ? implode(' and ', $filter) : " 1=1"). "
			)
			select
				Lpu.Lpu_id,
				Lpu.Org_id,
				Lpu.Org_tid,
				Lpu.Lpu_IsOblast,
				RTRIM(Lpu.Lpu_Name) as Lpu_Name,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
				Lpu.Lpu_Ouz,
				Lpu.Lpu_RegNomC,
				Lpu.Lpu_RegNomC2,
				Lpu.Lpu_RegNomN2,
				Lpu.Lpu_isDMS,
				adr.Address_Nick as Address,
				convert(varchar(10), Lpu.Lpu_DloBegDate, 104) as Lpu_DloBegDate,
				convert(varchar(10), Lpu.Lpu_DloEndDate, 104) as Lpu_DloEndDate,
				convert(varchar(10), Lpu.Lpu_BegDate, 104) as Lpu_BegDate,
				convert(varchar(10), Lpu.Lpu_EndDate, 104) as Lpu_EndDate,
				isnull(LpuLevel.LpuLevel_Code, 0) as LpuLevel_Code,
				ISNULL(Org.Org_IsAccess, 1) as Lpu_IsAccess,
				ISNULL(Org.Org_IsNotForSystem, 1) as Lpu_IsNotForSystem,
				ISNULL(Lpu.Lpu_IsMse, 1) as Lpu_IsMse
			from lpu_ids
				inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = lpu_ids.Lpu_id
				inner join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id
				left join LpuLevel with (nolock) on LpuLevel.LpuLevel_id = Lpu.LpuLevel_id
				left join v_Address adr with (nolock) on Org.UAddress_id = adr.Address_id
			where
				Lpu.Lpu_endDate is null
		";

		$result = $this->db->query($sql,$data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	/**
	 * Проверка
	 * @param $data array
	 * @param $pmuser_id integer
	 * @return array
	 */
	function checkLoginDuplicate($data, $pmuser_id)
	{
		global $config;
		$dupl_login = $this->config->item('DUPL_LOGIN_DISABLED');

		if (!empty($config['session_driver']) && $config['session_driver'] == 'mongodb' && $dupl_login) {
			// тянем все активные сессии из монго, по каждой разбираем сессию, фильтруем, считаем кол-во юзеров по АРМам.
			switch (checkMongoDb()) {
				case 'mongo':
					$this->load->library('swMongodb', array('config_file' => 'mongodbsessions'), 'swmongodb');
					break;
				case 'mongodb':
					$this->load->library('swMongodbPHP7', array('config_file' => 'mongodbsessions'), 'swmongodb');
					break;
			}

			$table = (isset($config['mongodb_session_settings']) && isset($config['mongodb_session_settings']['table'])) ? $config['mongodb_session_settings']['table'] : 'Session';

			$wheres = array(
				'logged' => 1
			);
			if (!empty($pmuser_id) && is_numeric($pmuser_id)) {
				$wheres['pmuser_id'] = $pmuser_id;
			}

			$items = $this->swmongodb->where_gt('updated', time() - 7200)->where($wheres)->get($table); // только залогиненные и активные последние 2 часа

			if (!empty($items) && count($items) > 0)
				return array('Error_Msg' => '<br>Пользователь уже выполнил вход в систему,<br>авторизация под данной учетной записью недоступна.');

		}
		// заглушка
		return array();
	}
	/**
	 * Сохранение записи о показе сообщения пользователю
	 */
	function checkShownMsgArms($data) {

		if (!empty($data['session']['login'])) {
			$res = array('Error_Msg' => '');

			$user = pmAuthUser::find($data['session']['login']);
			if (empty($user)) {
				return array('Error_Msg' => 'Пользователь не найден');
			}
			if (empty($_SESSION['CurARM']) && empty($data['curARMType'])) {
				return array('Error_Msg' => 'Арм не определён');
			}
			$date = date('Y-m-d');
			$currArm = $_SESSION['CurARM'];


			if (!empty($currArm) && $currArm['ARMType'])
				$add_arm = $currArm['ARMType'];
			else
				$add_arm = $data['curARMType'];

			$shown_armlist = array();
			if (!empty($user->shown_armlist)) {
				$saved_shown_armlist = json_decode($user->shown_armlist,true);
				if (is_array($saved_shown_armlist))
					$shown_armlist = $saved_shown_armlist;
			}

			if (!empty($shown_armlist['Date']) && $shown_armlist['Date'] != $date && !empty($shown_armlist['Arms']))
				unset($shown_armlist['Arms']);

			$shown_armlist['Date'] = $date;

			if (empty($shown_armlist['Arms']))
				$shown_armlist['Arms'] = array();

			if(!empty($add_arm) && !in_array($add_arm,$shown_armlist['Arms'])){
				array_push($shown_armlist['Arms'], $add_arm);
				$res['showMsg'] = true;
				$user->shown_armlist = json_encode($shown_armlist);
				$user->post();
			}
			return $res;
		}
		return array('Error_Msg' => 'Ошибка доступа');
	}

	/**
	 *
	 */
	public function generateNewUsers() {
		$list = $this->queryResult("
			select
				msf.Person_SurName as \"Person_SurName\",
				msf.Person_FirName as \"Person_FirName\",
				msf.Person_SecName as \"Person_SecName\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				l.Org_id as \"Org_id\"
			from
				v_MedStaffFact msf with (nolock)
				inner join v_Lpu l on l.Lpu_id = msf.Lpu_id
			where not exists (select top 1 pmUser_id from pmUserCache with (nolock) where MedPersonal_id = msf.MedPersonal_id)
		", []);

		$doneList = [];
		$usedLogins = [];

		if ( is_array($list) && count($list) > 0 ) {
			$log_file = EXPORTPATH_ROOT . date('YmdHis') . '_' . swGenRandomString(32) . '_generateNewUsers.csv';
			$sessionParams = getSessionParams();
			//var_dump($sessionParams); die();

			// настройки для новых пользователей
			$opt = @serialize([
				'recepts' => [
					'print_extension' => 1
				]
			]);

			file_put_contents($log_file, toAnsi("ФИО;Логин;Пароль\r\n", true));

			foreach ( $list as $row ) {
				if ( isset($doneList[$row['MedPersonal_id']]) ) {
					continue;
				}

				$login = mb_ucfirst(mb_strtolower(translit(mb_substr($row['Person_SurName'], 0, 3))));
				$login .= mb_ucfirst(translit(mb_substr($row['Person_FirName'], 0, 1)));

				if ( !empty($row['Person_SecName']) ) {
					$login .= mb_ucfirst(translit(mb_substr($row['Person_SecName'], 0, 1)));
				}

				if ( !isset($usedLogins[$login]) ) {
					$usedLogins[$login] = 0;
				}
				else {
					$usedLogins[$login]++;
				}

				//$login = 'admin';

				$userData = [
					'login' => $login . (!empty($usedLogins[$login]) ? $usedLogins[$login] : ''),
					'pass' => swGenRandomString(6),
					'surname' => $row['Person_SurName'],
					'firname' => $row['Person_FirName'],
					'secname' => $row['Person_SecName'],
					'MedPersonal_id' => $row['MedPersonal_id'],
					'orgs' => [ $row['Org_id'] ],
					'groups' => [ 2 ],
					'groupsNames' => [ 'LpuUser' ],
				];

				$user = pmAuthUser::find($userData['login']);

				if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
					continue;
				}

				while ( $user instanceof pmAuthUser ) {
					if ( !isset($usedLogins[$login]) ) {
						$usedLogins[$login] = 0;
					}

					$usedLogins[$login]++;
					$userData['login'] = $login . $usedLogins[$login];
					$user = pmAuthUser::find($userData['login']);

					if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
						break;
					}
				}

				if ( $user instanceof pmAuthUser && $user->medpersonal_id == $row['MedPersonal_id'] ) {
					continue;
				}

				$newUser = pmAuthUser::add(trim($userData['surname'] . " " . $userData['firname']), $userData['login'], $userData['pass'], $userData['surname'], $userData['secname'], $userData['firname']);

				// добавляем новые группы
				foreach ( $userData['groupsNames'] as $group ) {
					$newUser->addGroup($group);
				}

				foreach ( $userData['groups'] as $group ) {
					$this->db->query("
						declare
							@Res bigint,
							@Group_id bigint,
							@User_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);

						set @Res = NULL;
						set @Group_id = :pmUserCacheGroup_id;
						set @User_id = :pmUserCache_id;

						exec p_pmUserCacheGroupLink_ins
							@pmUserCacheGroupLink_id = @Res output,
							@pmUserCacheGroup_id = @Group_id,
							@pmUserCache_id = @User_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @Res as pmUserCacheGroup_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", [
						'pmUserCache_id' => $newUser->pmuser_id,
						'pmUserCacheGroup_id' => $group,
						'pmUser_id' => $sessionParams['pmUser_id'],
					]);
				}

				// добавляем организации
				foreach ( $userData['orgs'] as $org ) {
					$newUser->addOrg($org);
				}

				$newUser->medpersonal_id = $userData['MedPersonal_id'];

				$newUser->password_temp = 1;
				$newUser->password_date = time();
				$newUser->settings = $opt;

				$newUser->insert();

				$this->ReCacheUserData($newUser, $sessionParams);

				// Пишем в лог
				$s = trim($userData['surname'] . ' ' . $userData['firname'] . ' ' . $userData['secname']) . ";"
					. $userData['login'] . ";" . $userData['pass'] . "\r\n";

				file_put_contents($log_file, toAnsi($s, true), FILE_APPEND);

				$doneList[$row['MedPersonal_id']] = $userData;
			}
		}

		return
			'<div>Добавлено пользователей: ' . count($doneList) . '</div>'
			. (count($doneList) > 0 ? '<div>Файл: <a href="' . $log_file . '">ссылка</a></div>' : '');
	}


	public function getNotAdminUsers()
	{
		$query = "
			select
				PMUser_id as pmUser_id
			from
				pmUserCache with (nolock)
			where
				PMUser_Login not like '%swnt%' and pmUser_groups not like '%admin%' and PMUser_Blocked != 1
		";

		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array();
		}
	}

	public function getLpuAdminList()
	{
		$query = "
			select
				PMUser_id as pmUser_id
			from
				pmUserCache with (nolock)
			where
				pmUser_groups like '%LpuAdmin%' and PMUser_Blocked != 1
		";

		$res = $this->db->query($query);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array();
		}
	}

	public function getFirstLpuUnitTypeSysNickByMedStaffFact($data) {

		$result = array();

		if (!empty($data['MedStaffFact_id'])) {
			$result = $this->User_model->getFirstResultFromQuery("
				select top 1
					lut.LpuUnitType_SysNick
				from v_MedStaffFact msf
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_LpuUnitType lut (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
				where msf.MedStaffFact_id = :MedStaffFact_id
			", array('MedStaffFact_id' => $data['MedStaffFact_id']));
		}

		return $result;
	}

	/**
	 * Требуется ли проверка количества неудачных попыток входа текущего пользователя
	 * @return string|null|false
	 */
	public function getCheckFailLoginCounter() {
		return $this->getFirstResultFromQuery("
			select top 1 DS.DataStorage_Value 
			from v_DataStorage DS (nolock)
			where DS.DataStorage_Name = 'check_fail_login' 
			and DS.DataStorage_Value = 1
		", [], true);
	}

	/**
	 * Длительность блокировки учетной записи после неудачных попыток входа текущего пользователя
	 * @return string|null|false
	 */
	public function getBlockTimeFailLogin() {
		return $this->getFirstResultFromQuery("
			select top 1 DS.DataStorage_Value
			from v_DataStorage DS (nolock)
			where DS.DataStorage_Name = 'block_time_fail_login'
		", [], true);
	}

	/**
	 * Количество попыток ввода данных учетной записи до блокировки
	 * @return string|null|false
	 */
	public function getCountBadFailLogin() {
		return $this->getFirstResultFromQuery("
			select top 1 DS.DataStorage_Value
			from v_DataStorage DS (nolock)
			where DS.DataStorage_Name = 'count_bad_fail_login'
		", [], true);
	}

    /**
     * Возвращает check_count_parallel_sessions
     * @return bool|float|int|string
     */
    function getCheckCountParallelSessions(){
        $query = "select top 1 DataStorage_Value from v_DataStorage DS (nolock) where DS.DataStorage_Name = 'check_count_parallel_sessions' and DS.DataStorage_Value = 1";
        return $this->getFirstResultFromQuery($query);
    }

    /**
     * Возвращает count_parallel_sessions
     * @return bool|float|int|string
     */
    function getCountParallelSessions(){
        $query = "select top 1 DataStorage_Value from v_DataStorage DS (nolock) where DS.DataStorage_Name = 'count_parallel_sessions'";
        return $this->getFirstResultFromQuery($query);
    }

}

