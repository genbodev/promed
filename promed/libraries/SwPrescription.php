<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Prescription
 * @access		private
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @author		Alexander Permyakov
 * @version		12.2014
 */

/**
 * Вспомогательная библиотека для работы с назначениями
 */
class SwPrescription
{
	/**
	 * Признак, что, если курс лечения надо отображать развернутым,
	 * то данные назначений не надо складывать в данные курса
	 * @var bool
	 */
	static $disableNewMode = false;
	/**
	 * @param int $id PrescriptionType_id
	 * @return bool|string
	 */
	static function getEvnClassSysNickByType($id)
	{
		switch (true) {
			case (1 == $id): $sysnick = 'EvnPrescrRegime'; break;
			case (2 == $id): $sysnick = 'EvnPrescrDiet'; break;
			case (10 == $id): $sysnick = 'EvnPrescrObserv'; break;
			case (5 == $id): $sysnick = 'EvnCourseTreat'; break;
			case (6 == $id): $sysnick = 'EvnCourseProc'; break;
			case (7 == $id): $sysnick = 'EvnPrescrOperBlock'; break;
			case (11 == $id): $sysnick = 'EvnPrescrLabDiag'; break;
			case (12 == $id): $sysnick = 'EvnPrescrFuncDiag'; break;
			case (13 == $id): $sysnick = 'EvnPrescrConsUsluga'; break;
			default: $sysnick = false; break;
		}
		return $sysnick;
	}

	/**
	 * @param string $name
	 * @return bool|string
	 */
	static function getParentEvnClassSysNickBySectionName($name)
	{
		switch (true) {
			case ('EvnPrescrStom' == $name): $sysnick = 'EvnVizitPLStom'; break;
			case ('EvnPrescrPolka' == $name): $sysnick = 'EvnVizitPL'; break;
			case ('EvnPrescrPlan' == $name): $sysnick = 'EvnSection'; break;
			default: $sysnick = false; break;
		}
		return $sysnick;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	static function isStac($name)
	{
		return in_array($name, array(
			'EvnPrescrPlan',
			'EvnSection'
		));
	}
}

/**
 * Базовый класс представления назначения
 */
abstract class swPrescriptionView
{
	protected $_section = '';
	protected $_items = array();
	/**
	 * construct
	 */
	function __construct($section, $items = array())
	{
		$this->_section = $section;
		$this->_items = $items;
	}
	/**
	 * @return string
	 */
	abstract function getView();
}

/**
 * Класс представления группы назначений
 */
class swPrescriptionGroupView extends swPrescriptionView
{
	/**
	 * Конструктор
	 */
	function __construct($section, $items = array(), $forRightPanel = 0)
	{
		if ($forRightPanel) {
			$section .= "_rightPanel";
		}
		parent::__construct($section, $items);
		$this->forRightPanel = $forRightPanel;
	}

	/**
	 * @return string
	 */
	function getView()
	{
		if ($this->forRightPanel) {
			$tpl = '
			<div id="{section}List_{pid}" style="min-width:400px;">
				<div class="clear">
					<div class="data-table EvnPrescrPlanList">
						<dl id="{section}Table_{pid}" style="display: block;">
							{item_arr}
							<dt class="collapsed" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}">
								<span id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_loadEvnPrescr">
									<span>{PrescriptionType_Name}
										<span class="count" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_cnt">{PrescriptionType_Cnt}</span>
									</span>
								</span>
								<a href="#" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_addPrescription"><img src="/img/EvnPrescrPlan/add.png" title="Добавить"> Добавить</a>
							</dt>
							<dd id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_items"></dd>
							{/item_arr}
						</dl>
					</div>
				</div>
			</div>';
		} else {
			$tpl = '
			<div id="{section}List_{pid}">
				<div class="clear">
					<div class="data-table EvnPrescrPlanList">
						<div class="caption">
							<div class="PrescrHeader">
								<h2><span id="{section}List_{pid}_toggleDisplay" class="collapsible">Назначения </span></h2>' .
								(
									$this->_section == 'EvnPrescrPlan' ? 
										'<a id="{section}List_{pid}_addPacketPrescr" class="button icon icon-add16" title="Добавить пакетное назначение" style="width: auto; top: 2px; padding: 3px 4px;"><span></span></a>'.
										'<a id="{section}List_{pid}_savePacketPrescr" class="button icon icon-save16" title="Сохранить как пакет назначений" style="width: auto; top: 2px; padding: 3px 4px;"><span></span></a>'
									: ''
								) .
								'<span class="button" id="{section}List_{pid}_openPrescrListActionMenu"></span>
							</div>
						</div>
						<dl id="{section}Table_{pid}" style="display: block;">
							{item_arr}
							<dt class="collapsed" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}">
								<span id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_loadEvnPrescr">
									<span>{PrescriptionType_Name}
										<span class="count" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_cnt">{PrescriptionType_Cnt}</span>
									</span>
								</span>
								<a href="#" id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_addPrescription"><img src="/img/EvnPrescrPlan/add.png" title="Добавить"> Добавить</a>
							</dt>
							<dd id="{section}_{EvnPrescr_pid}-{PrescriptionType_id}_items"></dd>
							{/item_arr}
						</dl>
					</div>
				</div>
			</div>';
		}
		return strtr($tpl, array(
			'{section}' => $this->_section,
		));
	}
}

/**
* Класс представления назначения
*/
class swPrescriptionGroupItemView extends swPrescriptionView
{
	public $itemKey = '';
	public $name = '';
	public $courseItemAttributes = '';
	public $buttons = '';
	public $directioninfo = '';
	public $directionbuttons = '';
	public $description = array();
	public $prescriptions = array();
	public $afterDescription = array();
	/**
	 * construct
	 */
	function __construct($section, $rows, $forRightPanel = 0)
	{
		parent::__construct($section);

		$this->_section_id = $this->_section;
		if ($forRightPanel) {
			$this->_section_id = $this->_section."_rightPanel";
		}

		$this->forRightPanel = $forRightPanel;

		foreach($rows as $data) {
			$prescription = new stdClass();

			$prescription->buttons = '';
			$prescription->header = '<div class="prescriptioninfo" id="{section}_{itemKey}"{courseItemAttributes}>{buttons}{name}</div>';

			$this->itemKey = !empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null;

			$this->courseItemAttributes = '';
			if (!empty($data['EvnCourse_id']) && empty($data['isEvnCourse'])) {
				$prescription->header = '<div class="EvnCourse' . $data['EvnCourse_id'] . '" style="display: none;" id="{section}_{itemKey}">{buttons}{name}</div>';
			}

			switch (true) {
				case (1 == $data['PrescriptionType_id'])://<период действия>  <тип режима> <комментарий>
					$prescription->itemKey = $data['EvnPrescr_id'] . '-' . $data['EvnPrescrRegime_id'];// === $data[$this->_section . '_id']
					$prescription->name = '<span style="font-weight: bold">' . $data['PrescriptionRegimeType_Name'] . '</span>';
					$this->description[] = array('name' => 'Период', 'value' => $data['EvnPrescr_setDate']);
					break;
				case (2 == $data['PrescriptionType_id'])://<период действия>  <тип диеты> <комментарий>
					$prescription->itemKey = $data['EvnPrescr_id'] . '-' . $data['EvnPrescrDiet_id'];// === $data[$this->_section . '_id']
					$prescription->name = '<span style="font-weight: bold">' . $data['PrescriptionDietType_Name'] . '</span>';
					$this->description[] = array('name' => 'Период', 'value' => $data['EvnPrescr_setDate']);
					break;
				case (4 == $data['PrescriptionType_id'])://<дата и время> <профиль> <комментарий>
					$prescription->itemKey = $data['EvnPrescr_id'] . '-' . $data['EvnPrescrCons_id'];// === $data[$this->_section . '_id']
					$prescription->name = '<span style="font-weight: bold">' . $data['LpuSectionProfile_Name'] . '</span>';
					if (empty($data['EvnPrescr_setTime'])) {
						$this->description[] = array('name' => 'Плановая дата', 'value' => $data['EvnPrescr_setDate']);
					} else {
						$this->description[] = array('name' => 'Записан', 'value' => $data['EvnPrescr_setDate'] . '&nbsp;' . $data['EvnPrescr_setTime']);
					}
					break;
				case (10 == $data['PrescriptionType_id'])://<период действия> <Утро, День, Вечер> <параметры через запятую>, комментарий
					$prescription->itemKey = $data['EvnPrescr_id'] . '-' . $data['EvnPrescrObserv_id'];// === $data[$this->_section . '_id']
					$prescription->name = '<span style="font-weight: bold">' . $data['Params'] . '</span>';
					$this->description[] = array('name' => 'Период', 'value' => $data['EvnPrescr_setDate']);
					if (!empty($data['EvnPrescr_setTime'])) {
						$this->description[] = array('name' => 'Время наблюдения', 'value' => $data['EvnPrescr_setTime']);
					}
					break;
				case (5 == $data['PrescriptionType_id'] && !empty($data['isEvnCourse'])):
					$prescription->itemKey = $data['EvnPrescr_pid'] . '-' . $data['EvnCourse_id'];// === $data[$this->_section . '_id']
					$this->courseItemAttributes = ' style="background: none;"';
					$prescription->name = '<ul>
					<li class="EvnCourseTreatHead" style="padding: 6px 0; cursor: pointer;">
					<div>
						<span class="EvnCourseTreatHeadBtn" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openPrescrActionMenu"></span>
						<span>' . $data['EvnCourse_Title'] . '</span>
					</div>
					</li>';
					if (!empty($data['DrugListData']) && is_array($data['DrugListData'])) {
						$prescription->name .= '<li style="background: none"><span style="font-weight: bold;">Препараты:</span></li>';
						foreach ($data['DrugListData'] as $id => $row) {
							$drugName = '<span style="font-weight: bold; font-size:16px;">' . ($row['DrugTorg_Name']) . '</span>';
							if (!empty($row['EvnReceptGeneral_id']) && !empty($row['ReceptForm_Code'])) {
								$drugName .= '<span style="display:block;float:right;">';
								$drugName .= ' <span class="ExpandEvnPrescrTreatLink editEvnReceptGeneral" id="' .
									$row['EvnReceptGeneral_id'] . '_' . $row['ReceptForm_Code'] .
									'" style="display:block;height:16px;float:left;">Рецепт ';
								if(!empty($row['ReceptForm_Name'])){
									$drugName .= $row['ReceptForm_Name']. ' ';
								}
								if(!empty($row['EvnReceptGeneral_Ser'])){
									$drugName .= $row['EvnReceptGeneral_Ser'].' ';
								}
								if(!empty($row['EvnReceptGeneral_Num'])){
									$drugName .= $row['EvnReceptGeneral_Num'];
								}
								$drugName .= '</span>';
								if($row['MedPersonal_id']  == $_SESSION['medpersonal_id'])
								{
									if((!empty($row['ReceptType_Code']) && ($row['ReceptType_Code'] == 2 || $row['ReceptType_Code'] == 3)) && (empty($row['RegionNick']) || $row['RegionNick'] != 'kz')){
										$drugName .= ' <span class="ExpandEvnPrescrTreatLink printEvnReceptGeneral print16" id="' .
											$row['EvnReceptGeneral_id'] . '_' . $row['ReceptForm_Code'] .
											'" style="display:block;width:16px;height:16px;float:left;margin:0 5px;"></span>';
									}else{
										$drugName .= '<span style="display:block;width:16px;height:16px;float:left;margin:0 5px;">&nbsp;</span>';
									}
									$drugName .= ' <span class="ExpandEvnPrescrTreatLink deleteEvnReceptGeneral" id="' .
										$row['EvnReceptGeneral_id'] . '_' . $row['ReceptForm_Code'] . '_' . $row['EvnReceptGeneralDrugLink_id'] .
										'" style="display:block;height:16px;float:left;">&nbsp; Исключить из рецепта</span>';
									$drugName .= '</span>';
								}
								else
								{
									$drugName .= '</span>';
								}
							} else {
								$drugName .= ' <span class="ExpandEvnPrescrTreatLink createEvnReceptGeneral" id="' .$id.'" style="display:block;float:right;">Включить в рецепт</span>';
							}
							if (isset($data['EvnPrescr_IsCito']) && $data['EvnPrescr_IsCito'] == 2) {
								$drugName .= '&nbsp;<span class="cito">Cito!</span>';
							}
							if (empty($row['DoseDay']) && !empty($row['MaxDoseDay']) && !empty($row['MinDoseDay'])) {
								if ($row['MaxDoseDay'] == $row['MinDoseDay']) {
									$row['DoseDay'] = $row['MaxDoseDay'];
									if(!empty($row['DrugMaxCountInDay']) && !empty($row['EdUnits_Nick']) && !empty($row['Kolvo'])){
										$row['DoseDay'] = ($row['Kolvo']*$row['DrugMaxCountInDay']) . ' ' . ($row['GoodsUnit_Nick']);
									} else if(!empty($row['PrescrDoseDay'])){
										$row['DoseDay'] = $row['PrescrDoseDay'];
									}
								} else {
									$row['DoseDay'] = $row['MinDoseDay'] . ' - ' . $row['MaxDoseDay'];
								}
							}
							$dose = '';
							$doseOne = '';
							if (!empty($row['Kolvo']) && !empty($row['GoodsUnit_Nick'])) {
								$doseOne .= ' разовая: ' . $row['Kolvo'] . ' ' . ($row['GoodsUnit_Nick']);
							} else if (!empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])) {
								$doseOne .= ' разовая: ' . $row['Kolvo'] . ' ' . ($row['EdUnits_Nick']);
							} else if (!empty($row['KolvoEd']) && !empty($row['DrugForm_Nick'])) {
								$doseOne .= ' разовая: ' . $row['KolvoEd'] . ' ' . ($row['DrugForm_Nick']);
							}
							if (!empty($doseOne)) {
								$dose = '<br><span style="font-weight: bold;">Доза:</span> ' . $doseOne;
								if (!empty($row['DoseDay'])) {
									$dose .= ', дневная: ' . ($row['DoseDay']);
								} else {
									$dose .= '.';
								}
								if (!empty($row['PrescrDose'])) {
									$dose .= ', курсовая: ' . ($row['PrescrDose']) . '.';
								} else {
									$dose .= '.';
								}
							}
							$prescription->name .= '<li style="background: none; margin: 10px 0 10px 50px;">' . $drugName . $dose . '</li>';
						}
					}
					if (!empty($data['PrescriptionIntroType_Name'])) {
						$this->description[] = array('name' => 'Метод введения', 'value' => htmlspecialchars($data['PrescriptionIntroType_Name']));
					}
					if (!empty($data['EvnCourse_begDate'])) {
						$this->description[] = array('name' => 'Период', 'value' => 'с ' . $data['EvnCourse_begDate']);
					}
					if (!empty($data['Duration']) && !empty($data['DurationType_Nick'])) {
						$this->description[] = array('name' => 'Продолжительность', 'value' => $data['Duration'] . ' ' . $data['DurationType_Nick']);
					}
					if (!empty($data['PerformanceType_Name'])) {
						$this->description[] = array('name' => 'Исполнение', 'value' => htmlspecialchars($data['PerformanceType_Name']));
					}
					if (!empty($data['EvnPrescr_Descr'])) {
						$this->description[] = array('name' => 'Комментарий', 'value' => htmlspecialchars($data['EvnPrescr_Descr']));
					}
					foreach ($this->description as $row) {
						$prescription->name .= strtr('<li style="background: none"><strong>{name}: </strong> {value}</li>', array(
							'{name}' => $row['name'],
							'{value}' => $row['value'],
						));
					}
					if (!empty($data['PrescrListData']) && is_array($data['PrescrListData'])) {
						// Если есть дневные назначения, то показываем ссылку "Развернуть"
						$prescription->name .= '
						<li style="background: none">
							<span class="ExpandEvnPrescrTreatLink EvnPrescrTreatCollapsed" id="' . $this->_section_id . '_' . $prescription->itemKey . '_toogleEvnCourseTreat">Развернуть</span>
						</li>
						<li style="background: none; display: none;" id="' . $this->_section_id . '_' . $prescription->itemKey . '_EvnCourseTreatItems">
							<div id="' . $this->_section_id . '_' . $prescription->itemKey . '_EvnCourseTreatViewData"></div>
						</li>
						';
					}
					$this->description = array();
					$data['EvnPrescr_Descr'] = '';
					$prescription->name .= '</ul>';
					break;
				case (5 == $data['PrescriptionType_id'] && empty($data['isEvnCourse'])):
					$prescription->itemKey = $data['EvnPrescr_id'] . '-0';// === $data[$this->_section . '_id']
					$prescription->buttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openPrescrActionMenu"></span>';
					$DrugData = array();
					if (!empty($data['DrugListData']) && is_array($data['DrugListData'])) {
						foreach ($data['DrugListData'] as $id => $row) {
							$str = '<span title="' . ($row['Drug_Name']) . '">' . ($row['DrugTorg_Name']) . '</span>';
							/*if (!empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])) {
								$str .= ' Доза разовая – '.$row['Kolvo'].' '.$row['EdUnits_Nick'];
							} else if (!empty($row['KolvoEd']) && !empty($row['DrugForm_Nick'])) {
								$str .= ' Доза разовая – '.$row['KolvoEd'].' '.$row['DrugForm_Nick'];
							} else {
								$str .= '.';
							}*/
							if (!empty($row['DoseDay'])) {
								$str .= ', дневная доза – ' . ($row['DoseDay']);
							} else {
								$str .= '.';
							}
							if (!empty($data['PrescrCntDay'])) {
								$str .= ', ' . (empty($row['FactCntDay']) ? 0 : $row['FactCntDay']) . '/{PrescrCntDay}.';
							} else {
								$str .= '.';
							}
							$DrugData[] = $str;
						}
					}
					$prescription->name = '<span style="float: left;">' . implode('<br />', $DrugData) . '</span>';
					if ($data['EvnPrescr_IsExec'] == 2) {
						$prescription->name = $prescription->name . '<span title="Назначение выполнено" class="EvnPrescr_icon EvnPrescr_icon_type4"></span>';
					}
					break;
				case (6 == $data['PrescriptionType_id'] && !empty($data['isEvnCourse'])):
					$prescription->itemKey = $data['EvnPrescr_pid'] . '-' . $data['EvnCourse_id'];// === $data[$this->_section . '_id']
					$this->courseItemAttributes = '';
					$prescription->name = '<span style="font-weight: bold; float: left" class="link" id="' . $this->_section_id . '_' . $prescription->itemKey . '_toogleEvnCourse">' . $data['EvnCourse_Title'] . '</span>';
					if (isset($data['EvnPrescr_IsCito']) && $data['EvnPrescr_IsCito'] == 2) {
						$prescription->name .= '&nbsp;<span class="cito">Cito!</span>';
					}
					if ($data['EvnPrescr_IsExec'] == 2) {
						$prescription->name = $prescription->name . '<span title="Назначение выполнено" class="EvnPrescr_icon EvnPrescr_icon_type4"></span>';
					}
					$prescription->name .= '<br><br><ul>
					<li>' . $data['EvnCourse_begDate'] . ' - ' . $data['EvnCourse_endDate']
						. '. Общее кол-во: ' . $data['EvnPrescr_Count'] . '. Кратность: ';
					if ($data['MinCountInDay'] == $data['MaxCountInDay']) {
						$prescription->name .= $data['MinCountInDay'];
					} else {
						$prescription->name .= $data['MinCountInDay'] . ' - ' . $data['MaxCountInDay'];
					}
					$prescription->name .= ' в день.</li>';
					if (!empty($data['MedServices'])) {
						$prescription->name .= '<li><span style="font-weight: bold">Место выполнения:</span> ' . $data['MedServices'] . '.</li>';
					}
					$prescription->name .= '</ul>';
					break;
				case (6 == $data['PrescriptionType_id'] && empty($data['isEvnCourse'])):
					$prescription->itemKey = $data['EvnPrescr_id'] . '-0';// === $data[$this->_section . '_id']
					$prescription->name = '<span style="float: left;">' . $data['Usluga_List'] . '.&nbsp;' . $data['EvnPrescr_setDate'] . '</span>';
					if ($data['EvnPrescr_IsDir'] == 2) {

						$EvnPrescr_IsExec = ($data['EvnPrescr_IsExec'] == 2);
						$EvnPrescr_IsExecEl = '';
						if($EvnPrescr_IsExec){
							$EvnPrescr_IsExecEl = "<span title='Процедура выполнена' class='EvnPrescr_icon EvnPrescr_icon_type4'></span>";
						}

						$this->directioninfo = "
							<ul class='dirinfo'>
								<li>
									<div class='dirinfoinner'>
										<img src='/img/icons/napr_icon.png' />
										&nbsp;<span id='" . $this->_section_id . "_" . $prescription->itemKey . "_viewdir' class='link' title='Просмотр направления'>Направление {$data['EvnDirection_Num']}</span>
									</div>
									<div class='dirinfoinner'>
										<img src='/img/icons/place_icon.png' />&nbsp;{$data['RecTo']}
									</div>
									<div class='dirinfoinner'>
										<img src='/img/icons/time_icon.png' />&nbsp;{$data['RecDate']}
									</div>
									{$EvnPrescr_IsExecEl}
								</li>
							</ul>
						";
						//$this->directionbuttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openDirActionMenu"></span>';
						$prescription->buttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openPrescrActionMenu"></span>';
						/*$this->description[] = array(
							'name'=>'<span id="' . $this->_section_id . '_' . $prescription->itemKey . '_viewdir" class="link" title="Просмотр направления">Запись</span>',
							'value'=>$data['RecTo'] . ' ' . $data['RecDate'] . ' ' . $data['EvnDirection_Num']
						);*/
					} else {
						if (// направление было отменено/отклонено
							((isset($data['EvnStatus_id']) && in_array($data['EvnStatus_id'], array(12, 13)) && array_key_exists('EvnStatusCause_id', $data))
								|| isset($data['DirFailType_id'])
								|| isset($data['QueueFailCause_id'])
							) //Направления, отмененные с причиной “Неверный ввод”, не отображать на форме
							&& $data['EvnStatusCause_id'] != 4
							&& $data['DirFailType_id'] != 14
							&& $data['QueueFailCause_id'] != 5
						) {
							$statusinfo = "<img src='/img/icons/time_icon.png' />&nbsp;{$data['EvnDirection_statusDate']}&nbsp;<span class='cito'>{$data['EvnStatus_Name']}</span>";
							if (!empty($data['EvnStatusCause_Name'])) {
								$statusinfo .= "&nbsp;по&nbsp;причине&nbsp;{$data['EvnStatusCause_Name']} ";
							}
							if (!empty($data['EvnStatusHistory_Cause'])) {
								$statusinfo .= "(".htmlspecialchars($data['EvnStatusHistory_Cause'], ENT_IGNORE, 'UTF-8').")";
							}
							$this->directioninfo = "<ul class='dirinfo'><li>
							<div class='dirinfoinner'><img src='/img/icons/napr_icon.png' />&nbsp;<span id='" . $this->_section_id . "_" . $prescription->itemKey . "_viewdir' class='link' title='Просмотр направления'>Направление {$data['EvnDirection_Num']}</span></div>
							<div class='dirinfoinner'><img src='/img/icons/place_icon.png' />&nbsp;{$data['RecTo']}</div>
							<div class='dirinfoinner'>{$statusinfo}</div>
							</li></ul>";
						} else {
							$this->description[] = array('name' => 'Запись', 'value' => '<span class="cito">Требуется запись</span>');
						}
					}
					if ($data['EvnPrescr_IsExec'] == 2) {
						$this->description = array();
						$prescription->name = $prescription->name . '<span title="Назначение выполнено" class="EvnPrescr_icon EvnPrescr_icon_type4"></span>';
					}
					break;
				case (7 == $data['PrescriptionType_id'])://также как 12
				case (11 == $data['PrescriptionType_id'])://также как 12
				case (13 == $data['PrescriptionType_id'])://также как 12
				case (14 == $data['PrescriptionType_id'])://также как 12
				case (12 == $data['PrescriptionType_id']):
					$prescription->itemKey = $data[$this->_section . '_id'];// == $data['EvnPrescr_id'] . '-' . $data['TableUsluga_id'] for 7,12
					$prescription->name = '<span style="float: left; font-weight: bold">' . $data['Usluga_List'] . '</span>';
					// если есть бракованные пробы, выделяем назначение красным
					if (11 == $data['PrescriptionType_id'] && isset($data['EvnLabSampleDefect']) && count($data['EvnLabSampleDefect'])) {
						$prescription->name = '<span style="float: left; font-weight: bold; color: #FF0004;">' . $data['Usluga_List'] . '</span>';
					}
					if ($data['EvnPrescr_IsDir'] == 2) {
						$this->directioninfo = "<ul class='dirinfo'><li><div class='dirinfoinner napr'><img src='/img/icons/napr_icon.png' />&nbsp;<span id='" . $this->_section_id . "_" . $prescription->itemKey . "_viewdir' class='link' title='Просмотр направления'>Направление {$data['EvnDirection_Num']}</span></div><div class='dirinfoinner place'><img src='/img/icons/place_icon.png' />&nbsp;{$data['RecTo']}</div><div class='dirinfoinner time'><img src='/img/icons/time_icon.png' />&nbsp;{$data['RecDate']}</div></li></ul>";
						$this->directionbuttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openDirActionMenu"></span>';
						$prescription->buttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openPrescrActionMenu"></span>';
						/*$this->description[] = array(
							'name'=>'<span id="' . $this->_section_id . '_' . $prescription->itemKey . '_viewdir" class="link" title="Просмотр направления">Запись</span>',
							'value'=>$data['RecTo'] . ' ' . $data['RecDate'] . ' ' . $data['EvnDirection_Num']
						);*/
					} else {
						if (// направление было отменено/отклонено
							((isset($data['EvnStatus_id']) && in_array($data['EvnStatus_id'], array(12, 13)) && array_key_exists('EvnStatusCause_id', $data))
								|| isset($data['DirFailType_id'])
								|| isset($data['QueueFailCause_id'])
							) //Направления, отмененные с причиной “Неверный ввод”, не отображать на форме
							&& $data['EvnStatusCause_id'] != 4
							&& $data['DirFailType_id'] != 14
							&& $data['QueueFailCause_id'] != 5
						) {
							$statusinfo = "<img src='/img/icons/time_icon.png' />&nbsp;{$data['EvnDirection_statusDate']}&nbsp;<span class='cito'>{$data['EvnStatus_Name']}</span>";
							if (!empty($data['EvnStatusCause_Name'])) {
								$statusinfo .= "&nbsp;по&nbsp;причине&nbsp;{$data['EvnStatusCause_Name']} ";
							}
							if (!empty($data['EvnStatusHistory_Cause'])) {
								$statusinfo .= "(".htmlspecialchars($data['EvnStatusHistory_Cause'], ENT_IGNORE, 'UTF-8').")";
							}
							$this->directioninfo = "<ul class='dirinfo'><li>
							<div class='dirinfoinner'><img src='/img/icons/napr_icon.png' />&nbsp;<span id='" . $this->_section_id . "_" . $prescription->itemKey . "_viewdir' class='link' title='Просмотр направления'>Направление {$data['EvnDirection_Num']}</span></div>
							<div class='dirinfoinner'><img src='/img/icons/place_icon.png' />&nbsp;{$data['RecTo']}</div>
							<div class='dirinfoinner'>{$statusinfo}</div>
							</li></ul>";
						} else {
							$this->description[] = array('name' => 'Запись', 'value' => '<span class="cito">Требуется запись</span>');
						}
					}
					if ($data['EvnPrescr_IsExec'] == 2) {
						$this->description = array();
						$prescription->name = $prescription->name . '<span title="Назначение выполнено" class="EvnPrescr_icon EvnPrescr_icon_type4"></span>';
					}
					if (isset($data['EvnXml_id'])) {
						$prescription->name .= '&nbsp;<span class="dashedlink" id="' . $this->_section_id . '_' . $prescription->itemKey . '_xml">Результаты...</span>';
					}
					// пробы только для лаб
					if (11 == $data['PrescriptionType_id'] && isset($data['EvnLabSampleDefect']) && count($data['EvnLabSampleDefect'])) {
						foreach ($data['EvnLabSampleDefect'] as $EvnLabSampleDefectRow) {
							$this->afterDescription[] = "<span class=\"cito\">Брак пробы ({$EvnLabSampleDefectRow['DefectCauseType_Name']})</span>";
						}
					}
					break;
			}
			if (isset($data['EvnPrescr_IsCito']) && $data['EvnPrescr_IsCito'] == 2 && !(6 == $data['PrescriptionType_id'] && !empty($data['isEvnCourse'])) && !(5 == $data['PrescriptionType_id'] && !empty($data['isEvnCourse']))) {
				$prescription->name .= '&nbsp;<span class="cito">Cito!</span>';
			}
			if (isset($data['EvnPrescr_IsDir']) && $data['EvnPrescr_IsDir'] == 2 && array_key_exists('EvnXmlDir_id', $data)) {
				if (empty($data['EvnXmlDir_id'])) {
					$this->afterDescription[] = '<span id="' . $this->_section_id . '_' . $prescription->itemKey . '_addBlankDir" class="link" title="Открыть форму Бланк направления: Добавление">Заполнить бланк</span>';
				} else {
					$this->afterDescription[] = '<span id="' . $this->_section_id . '_' . $prescription->itemKey . '_editBlankDir" class="link" title="Открыть форму Бланк направления: Просмотр/редактирование">Просмотр/редактирование бланка</span>';
				}
			}
			if (!empty($data['EvnPrescr_Descr'])) {
				$this->description[] = array('name' => 'Комментарий', 'value' => htmlspecialchars($data['EvnPrescr_Descr']));
			}
			if (empty($prescription->buttons) && 5 != $data['PrescriptionType_id']) {
				$prescription->buttons = '<span class="button" id="' . $this->_section_id . '_' . $prescription->itemKey . '_openPrescrActionMenu"></span>';
			}

			$this->prescriptions[] = $prescription;
		}
	}

	/**
	 * @return string
	 */
	function getView()
	{
		$prescrtpl = "";
		$description = '';
		if (count($this->description) > 0 || count($this->afterDescription) > 0) {
			$description = '<ul>';
			foreach ($this->description as $row) {
				$description .= strtr('<li><strong>{name}: </strong> {value}</li>', array(
					'{name}' => $row['name'],
					'{value}' => $row['value'],
				));
			}
			foreach ($this->afterDescription as $value) {
				$description .= strtr('<li>{value}</li>', array(
					'{value}' => $value,
				));
			}
			$description .= '</ul>';
		}

		foreach($this->prescriptions as $prescription) {
			$prescrtpl .= strtr($prescription->header, array(
				'{section}' => $this->_section_id,
				'{itemKey}' => $prescription->itemKey,
				'{buttons}' => $prescription->buttons,
				'{name}' => $prescription->name
			));
		}

		$tpl = '
						<li>
							'.$prescrtpl.'
							<div class="directioninfo" id="{section}_{itemKey}" onmouseover="Ext.get(this).parent().query(\'.prescriptioninfo\').forEach(function(el) {Ext.get(el).addClass(\'hover\');Ext.get(el).query(\'li.EvnCourseTreatHead div .EvnCourseTreatHeadBtn\').forEach(function(el) {Ext.get(el).addClass(\'EvnCourseTreatHeadBtnShow\')})});" onmouseout="Ext.get(this).parent().query(\'.prescriptioninfo\').forEach(function(el) {Ext.get(el).removeClass(\'hover\');Ext.get(el).query(\'li.EvnCourseTreatHead div .EvnCourseTreatHeadBtn\').forEach(function(el) {Ext.get(el).removeClass(\'EvnCourseTreatHeadBtnShow\')})});">{directionbuttons}{directioninfo}{description}</div>
						</li>';
		return strtr($tpl, array(
			'{section}' => $this->_section_id,
			'{itemKey}' => $this->itemKey,
			'{directionbuttons}' => $this->directionbuttons,
			'{directioninfo}' => $this->directioninfo,
			'{description}' => $description,
			'{courseItemAttributes}' => $this->courseItemAttributes
		));
	}
}

/**
 * Класс представления назначений
 */
class swPrescriptionItemsView extends swPrescriptionView
{
	/**
	 * Конструктор
	 */
	function __construct($section, $items = array(), $forRightPanel = 0)
	{
		parent::__construct($section, $items);
		$this->forRightPanel = $forRightPanel;
	}

	/**
	 * @return string
	 */
	function getView()
	{
		// надо сгруппировать назначения с одинаковыми направлениями.
		$grouppeditems = array();
		foreach ($this->_items as $row) {
			if (!empty($row['EvnDirection_id'])) {
				$grouppeditems[$row['EvnDirection_id']][] = $row;
			} else {
				$grouppeditems[][] = $row;
			}
		}

		$items = '';
		foreach ($grouppeditems as $key => $rowgroup) {
			$item = new swPrescriptionGroupItemView($this->_section, $rowgroup, $this->forRightPanel);
			$items .= $item->getView();
		}
		$tpl = '
			<ul>
		    {items}
			</ul>';
		return strtr($tpl, array(
			'{items}' => $items,
		));
	}
}


