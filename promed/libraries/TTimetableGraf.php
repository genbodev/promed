<?php

/**
 * TTimetableGraf - расширение абстрактного класса, описывающее бирку поликлиники
 * Унаследовано от ЭР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      05.10.2011
 */
require_once(APPPATH . "libraries/TTimetable.php");

/**
 * Класс описывающий объект бирки поликлиники
 */
class TTimetableGraf extends TTimetable {

	private static $ci_instance = null;

	/**
	 * Идентификатор бирки
	 */
	var $TimetableGraf_id = NULL;

	/**
	 * Тип бирки
	 */
	var $TimetableType_id = NULL;

	/**
	 * Дата изменения бирки
	 */
	var $TimetableGraf_updDT = NULL;

	/**
	 * Время бирки
	 */
	var $TimetableGraf_begTime = NULL;

	/**
	 * День бирки
	 */
	var $TimetableGraf_Day = NULL;

	/**
	 * Признак дополнительной бирки
	 */
	var $TimetableGraf_IsDop = NULL;
	/**
	 * количество записанных на групповую бирку в данный момент
	 */
	var $TimeTableGraf_countRec = NULL;
	/**
	 * максимально допустимое количество записанных на групповую бирку
	 */
	var $TimeTableGraf_PersRecLim = NULL;

	/**
	 * Групповая бирка
	 */
	var $TimetableGrafRecList_id = NULL;
	/**
	 * Признак модерации бирки
	 */
	var $TimetableGraf_IsModerated = NULL;

	/**
	 * Данные пользователя последним изменившего бирку в ассоциативном массиве
	 */
	var $pmUser = NULL;

	/**
	 * Данные человека, записанного на бирку в ассоциативном массиве
	 */
	var $Person = NULL;

	/**
	 * Данные направления, выписанного на бирку в ассоциативном массиве, 
	 * включая данные по очереди, если направление в ней стояло
	 */
	var $Direction = NULL;

	/**
	 * Строка запроса
	 */
	var $sURLParams = NULL;

	/**
	 * Массив заблокированных бирок
	 */
	var $arReserved = NULL;

	/**
	 * Данные по текущему врачу
	 */
	var $mpData = NULL;

	/**
	 * Список участков, на которых может обслуживаться человек
	 */
	var $Regions = NULL;

	/**
	 * Идентификатор панели
	 */
	var $PanelID = NULL;

	/**
	 * Данные о текущем пользователе из сессии
	 */
	var $pmUserData = NULL;
	
	/**
	 * Объект типа бирок
	 */
	var $TimetableType = NULL;

	/**
	 * Не выводить экшены, только просмотр
	 */
	var $readOnly = false;

	/**
	 * Конструктор бирки, принимает данные и заполняет поля объекта
	 */
	function __construct( $arFields = NULL, $arReserved = NULL, $data = NULL ) {
		$this->regionNick = self::getCiInstance()->load->getRegionNick();
		if ( isset($arFields) ) {
			$this->TimetableGraf_id = $arFields['TimetableGraf_id'];
			$this->TimetableType_id = (isset($arFields['TimetableType_id']) ? $arFields['TimetableType_id'] : 1);
			$this->TimetableGraf_updDT = $arFields['TimetableGraf_updDT'];
			$this->TimetableGraf_begTime = $arFields['TimetableGraf_begTime'];
			$this->TimetableGraf_Day = $arFields['TimetableGraf_Day'];
			$this->TimetableGraf_IsDop = $arFields['TimetableGraf_IsDop'];
			$this->TimeTableGraf_countRec = (!empty($arFields['TimeTableGraf_countRec']))?$arFields['TimeTableGraf_countRec']:null;
			$this->TimeTableGraf_PersRecLim = (!empty($arFields['TimeTableGraf_PersRecLim']))?$arFields['TimeTableGraf_PersRecLim']:null;
			$this->TimetableGrafRecList_id = (!empty($arFields['TimetableGrafRecList_id']))?$arFields['TimetableGrafRecList_id']:null;

			$this->TimetableGraf_IsModerated = (isset($arFields['TimetableGraf_IsModerated']) ? $arFields['TimetableGraf_IsModerated'] : 0);
			if ( isset($arFields['annotation']) && count($arFields['annotation']) ) {
				$raw_annot = array();
				foreach ($arFields['annotation'] as $annotation) {				
					$this->annotation[] = "<div class='ttcomments'>" . nl2br(htmlspecialchars($annotation['Annotation_Comment'])) . "<br/><i>" . $annotation['pmUser_Name'] . ", " . $annotation['Annotation_updDT']->format('d.m.Y H:i') . "</i></div>";
					$raw_annot[] = trim($annotation['Annotation_Comment']);
				}
				$this->aHash = hash('crc32', trim(join('', $raw_annot)));
			}

			if ( isset($arFields['Regions']) ) {
				$this->Regions = $arFields['Regions'];
			}

			$this->pmUser = array(
				'pmUser_updID' => $arFields['pmUser_updID'],
				'PMUser_Name' => $arFields['PMUser_Name'],
				'Lpu_id' => $arFields['pmUser_Lpu_id']
			);
			if ( isset($data['mpdata']) ) {
				$this->mpData = array(
					'RecType_id' => (isset($data['mpdata']['RecType_id'])) ? $data['mpdata']['RecType_id'] : null,
					'Org_id' => (isset($data['mpdata']['Org_id'])) ? $data['mpdata']['Org_id'] : null,
					'Lpu_id' => (isset($data['mpdata']['Lpu_id'])) ? $data['mpdata']['Lpu_id'] : null,
					'MedStaffFact_IsDirRec' => (isset($data['mpdata']['MedStaffFact_IsDirRec'])) ? $data['mpdata']['MedStaffFact_IsDirRec'] : null,
					'MedPersonal_id' => (isset($data['mpdata']['MedPersonal_id'])) ? $data['mpdata']['MedPersonal_id'] : null,
					'LpuUnit_id' => (isset($data['mpdata']['LpuUnit_id'])) ? $data['mpdata']['LpuUnit_id'] : null,
				);
			}

			if ( isset($arFields['Person_id']) ) {
				$this->Person = array(
					'Person_id' => $arFields['Person_id'],
					'Person_Surname' => $arFields['Person_Surname'],
					'Person_Firname' => $arFields['Person_Firname'],
					'Person_Secname' => $arFields['Person_Secname'],
					'Person_BirthDay' => $arFields['Person_BirthDay'],
					'PersonCard_id' => (isset($arFields['PersonCard_id']) ? $arFields['PersonCard_id'] : NULL),
					'PersonCard_Code' => (isset($arFields['PersonCard_Code']) ? $arFields['PersonCard_Code'] : NULL),
					'Person_Phone' => $arFields['Person_Phone'],
					'Address_Address' => (isset($arFields['Address_Address']) ? $arFields['Address_Address'] : NULL),
					'KLTown_id' => (isset($arFields['KLTown_id']) ? $arFields['KLTown_id'] : NULL),
					'KLStreet_id' => (isset($arFields['KLStreet_id']) ? $arFields['KLStreet_id'] : NULL),
					'Address_House' => (isset($arFields['Address_House']) ? $arFields['Address_House'] : NULL),
					'PrivilegeType_id' => (isset($arFields['PrivilegeType_id']) ? $arFields['PrivilegeType_id'] : NULL),
					'Job_Name' => (isset($arFields['Job_Name']) ? $arFields['Job_Name'] : NULL),
					'Lpu_Nick' => (isset($arFields['Lpu_Nick']) ? $arFields['Lpu_Nick'] : NULL),
					'Person_InetPhone' => (isset($arFields['Person_InetPhone']) ? $arFields['Person_InetPhone'] : NULL),
					'Person_IsPriem' => (isset($arFields['Person_IsPriem']) ? $arFields['Person_IsPriem'] : NULL),
					'Polis_Ser' => (isset($arFields['Polis_Ser']) ? $arFields['Polis_Ser'] : NULL),
					'Polis_Num' => (isset($arFields['Polis_Num']) ? $arFields['Polis_Num'] : NULL),
					'Person_Filter' => (isset($arFields['Person_Filter']) ? $arFields['Person_Filter'] : NULL)
				);
			}

			$this->Direction = array(
				'EvnDirection_id' => $arFields['EvnDirection_id'],
				'EvnDirection_tid' => isset($arFields['EvnDirection_tid']) ? $arFields['EvnDirection_tid'] : null,
				'Direction_Num' => $arFields['Direction_Num'],
				'Direction_Date' => $arFields['Direction_Date'],
				'Direction_TalonCode' => $arFields['Direction_TalonCode'],
				'Lpu_Nick' => $arFields['DirLpu_Nick'],
				'Diag_Code' => $arFields['Diag_Code'],
				'QpmUser_Name' => $arFields['QpmUser_Name'],
				'EvnQueue_insDT' => $arFields['EvnQueue_insDT'],
			);
			$this->arReserved = $arReserved;

			$this->pmUserData = $data['pmUserData'];
			$this->curDT = $data['curDT'];
			
			if ( empty($data['PanelID']) ) {
				$this->PanelID = 'TTGSchedulePanel';
			} else {
				$this->PanelID = $data['PanelID'];
			}

			$this->timetable_blocked = !empty($data['timetable_blocked']);
		}
		
		loadLibrary('TTimetableTypes');
		$this->TimetableType = TTimetableTypes::instance()->getTimetableType($this->TimetableType_id);
	}

	private static function getCiInstance()
	{
		if (isset(self::$ci_instance)) {
			return self::$ci_instance;
		}
		self::$ci_instance =& get_instance();
		return self::$ci_instance;
	}
	
	/**
	 * Разрешена ли запись на бирку переданному пользователю
	 */
	function canRecord($direction = false) {
		if ($direction && $this->TimetableType->inSources(6) && $this->mpData['MedStaffFact_IsDirRec'] != 1) {
			return true;
		}
		
		return canRecord(
			array(
				'TimetableGraf_Day' => $this->TimetableGraf_Day,
				'LpuUnit_id' => $this->mpData['LpuUnit_id'],
				'MedPersonal_id' => $this->mpData['MedPersonal_id'],
				'MedStaffFact_IsDirRec' => $this->mpData['MedStaffFact_IsDirRec'],
				'Org_id' => $this->mpData['Org_id'],
				'TimetableType_id' => $this->TimetableType_id
			),
			$this->pmUserData
		);
	}

	/**
	 * Печать одной ячейки в таблице на 2 недели
	 * $IsForRow boolean Печать ячейки для ряда, с добавлением ФИО 
	 */
	function PrintCell( $IsForRow = false ) {

		//echo '<pre>',print_r($this),'</pre>'; die();

		$sClass = "work";
		$sText = "";
		$sEvents = "";
		$addSpan = "";
		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = ''; Ext.select('td.commented').removeClass('commented-active');\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableGraf_begTime->format('H:i');
		
		// Добавляю контекстное меню #125232 для групповых бирок
		if(!empty($this->TimetableType_id) && $this->TimetableType_id == 14){
			$strDT = '';
			if (!empty($this->TimetableGraf_begTime))
				$strDT .= ", '".$this->TimetableGraf_begTime->format("d.m.Y") . "', '" . $this->TimetableGraf_begTime->format("H:i")."'";
			else
				$strDT .= ', null, null';
			$sEvents .= " oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableGraf_id}, " . (!empty($this->Person['Person_id']) ? $this->Person['Person_id'] : 'null') . ", " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ", " . var_export(IsInetUser($this->pmUser['pmUser_updID']), true) . "{$strDT}); return false;\"";
		}

		if ( !isset($this->Person["Person_id"]) ) {
			if ( isset($this->annotation) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableGraf_IsDop != ""  ? '<br/>Дополнительная' : '') . "<br/><br/>" . join('', $this->annotation) . "\"";
				$sClass .= " commented";
				$sClass .= " tt-annot-{$this->aHash}"; 
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff'; Ext.select('td.tt-annot-{$this->aHash}').addClass('commented-active');\" ";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableGraf_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			}
		}

		if ( isset($this->Person) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
				$sClass .= " queue";
			}

			$sEvents .= $this->GetPersonTip();

			if (
				$this->TimetableGraf_begTime->format("Y-m-d H:i") >= $this->curDT->format("Y-m-d H:i") && (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					($this->pmUserData['Lpu_id'] == $this->pmUser['Lpu_id'] && $this->regionNick == 'pskov') || // пользователь того же ЛПУ, кто последний обновлял бирку
					IsLpuRegAdmin($this->mpData['Org_id']) // Администратор этого ЛПУ
			)) {
				$evnDir = !empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null';
				$recList = !empty($this->TimetableGrafRecList_id) ? $this->TimetableGrafRecList_id : 'null';
				$pmUser = var_export(IsInetUser($this->pmUser['pmUser_updID']), true);
				$pers = (!empty($this->Person) && !empty($this->Person['Person_id'])) ? $this->Person['Person_id'] : 'null';
				$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' 
 							onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableGraf_id}, ".$evnDir.", ".$pmUser.", ".$pers.", ".$recList.");\" href='#'>X</a>";
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sTalonCode = !empty($this->Direction['Direction_TalonCode'])?', <b>код брони: '.$this->Direction['Direction_TalonCode'].'</b>':'';
				$sText .= " Занято, {$sPerson}{$sTalonCode}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableGraf_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано другим оператором</b>\"";
			} else if ( $this->mpData["RecType_id"] == 3 ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Прием ведется только по 'живой очереди'</b>\"";
			} else if ($this->timetable_blocked && !empty($this->TimetableType_id) && in_array($this->TimetableType_id, array(1,11))) {
				$sClass .= " " . $this->TimetableType->getClass(1);
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано очередью</b>\"";
			} else {
				
				if ( $this->canRecord() ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Person);
					if ( $this->TimetableGraf_begTime->format("Y-m-d H:i") < $this->curDT->format("Y-m-d H:i") ) { //  прошедший день или время
						If (
							(IsCZUser() || IsLpuRegAdmin($this->mpData['Org_id']))
							&& isset($_SESSION['setting']) && is_array($_SESSION['setting'])
							&& isset($_SESSION['setting']['server']) && is_array($_SESSION['setting']['server'])
							&& $_SESSION['setting']['server']['disallow_recording_for_elapsed_time'] != true
						) {
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}');\"";
							}
						} else {
							$sClass .= " locked";
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
							}
						}
					} else if($this->TimetableType_id==3){
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}', '{$this->TimetableGraf_begTime->format("H:i")}');\"";
						}
					}else {
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}', '{$this->TimetableGraf_begTime->format("H:i")}');\" href='#' ";
						}
					}
				} else {
					$sClass .= " locked";
				}
			}
			if ( $IsForRow  ) {
				$sText .= " Свободно";
				$sEvents .= " style='text-align: left;'";
			}
		}
		
		If ( $this->TimetableGraf_IsDop != "" ) {
			$sClass .= " dop";
		}
		//При записи 1 и более человек на бирку с типом "групповой прием" добавляем иконку
		if(!empty($this->TimeTableGraf_countRec) && $this->TimeTableGraf_countRec>0){
			$addSpan = "<span class='timetable-type-group-cell'></span>";
		}
		echo "<td class='$sClass' $sEvents>$sText $addSpan</td>";
	}

	/**
	 * Печать одной ячейки в таблице на 2 недели для печатного варианта
	 */
	function PrintCellForPrint() {
		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents = "";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText = "<img src=\"/images/star.gif\"> " . SQLToTimeStr($this->TimetableGraf_begTime);
		} else {
			$sText = SQLToTimeStr($this->TimetableGraf_begTime);
		}

		if ( isset($this->Person) ) {
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sText .= " {$sPerson}";
			$sEvents .= " style='text-align: left;'";
		} else {
			if ( isset($this->DayID) ) {
				$sText .= " ";
				$sEvents .= " style='text-align: left;'";
			}
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
	}

	/**
	 * Печать одной ячейки в таблице на 2 недели для редактирования
	 */
	function PrintCellForEdit() {
		$sClass = "work";
		$sText = "";
		$sEvents = "";

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = ''; Ext.select('td.commented').removeClass('commented-active');\"";
		
		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableGraf_begTime->format('H:i');
		
		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		if ( !isset($this->Person) ) {
			if ( isset($this->annotation) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableGraf_IsDop != "" ? '<br/>Дополнительная' : '') . "<br/><br/>" . join('', $this->annotation) . "\"";
				$sClass .= " commented";
				$sClass .= " tt-annot-{$this->aHash}"; 
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff'; Ext.select('td.tt-annot-{$this->aHash}').addClass('commented-active');\" ";
			} else {
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableGraf_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
			}
		}

		$sPerson = '';
		If ( !isset($this->Person) ) {
			$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').toggleSelection(this, {$this->TimetableGraf_id}); return false;\" oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableGraf_id}, 0, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ", " . var_export(IsInetUser($this->pmUser['pmUser_updID']), true) . "); return false;\"";
			$sClass .= " free";
		} else {
			$sEvents .= " oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableGraf_id}, {$this->Person['Person_id']}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ", " . var_export(IsInetUser($this->pmUser['pmUser_updID']), true) . "); return false;\"";
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sClass .= " person";

			$sEvents .= $this->GetPersonTip();

			if ( isset($this->DayID) ) {
				$sText .= " Занято, {$sPerson}";
			}
		}
		
		If ( $this->TimetableGraf_IsDop != "" ) {
			$sClass .= " dop";
		}
		
		// Прошедшие дни
		if ( $this->TimetableGraf_begTime->format("Y-m-d H:i") < $this->curDT->format("Y-m-d H:i") ) {
			$sClass .= " old";			
		}
		
		echo "<td id='TTG_{$this->TimetableGraf_id}' class='$sClass' $sEvents>$sText</td>";
	}

	/**
	 * Печать одной строки в списке на день
	 */
	function PrintDayRow( $IsPrint = false ) {
		global $Connection;

		if ( !$IsPrint ) {
			$this->PrintCell(true);
		}

		$sBirthDate = "";
		if ( isset($this->Person) ) {
			if ( $this->Person['Person_BirthDay'] != '' ) {
				$sBirthDate = $this->Person['Person_BirthDay']->format('d.m.Y');
			}
		}

		if ( count($this->Regions) > 0 ) {
			$ShowRegionTxt = "<td style=\"text-align: left;\">" . implode(", ", $this->Regions) . "</td>";
		} else {
			$ShowRegionTxt = "<td style=\"text-align: left;\"></td>";
		}

		if ( $this->TimetableGraf_IsModerated == 2 ) {
			$ShowAlerted = "<br/><font color='red'><b>Предупрежден</b></font>";
		} else {
			$ShowAlerted = "";
		}
		if ( isset($this->Person['Person_InetPhone']) ) {
			$InetPhone = "<br/>Из интернета: {$this->Person['Person_InetPhone']}";
		} else {
			$InetPhone = "";
		}
		if ( isset($this->Person) ) {
			$pmUser_Name = $this->pmUser['PMUser_Name'];
		} else {
			$pmUser_Name = "";
		}
		if ( isset($this->Person['Person_IsPriem']) && $this->Person['Person_IsPriem']==2 ) {
			$Person_IsPriem = "<img src='/img/icons/checked16.png' border=0>";
		} else {
			$Person_IsPriem = "";
		}
		if (isset($this->Person['PersonCard_id'])) {
			$PersonCard_Code = "<a href=# onclick=\"getWnd('swPersonCardEditWindow').show({'action':'edit','Person_id':{$this->Person['Person_id']},'PersonCard_id':{$this->Person['PersonCard_id']} }); return false;\">{$this->Person['PersonCard_Code']}</a>";
		} else {
			$PersonCard_Code = "";
		}
		// Для Астрахани выводится информация о полисе
		if ($this->regionNick == "astra") {
			if (isset($this->Person['Polis_Num'])) {
				if ($this->Person['Polis_Ser'] == '') {
					$PolisInfo = "№ " . $this->Person['Polis_Num'];
				} else {
					$PolisInfo = $this->Person['Polis_Ser'] . " № " . $this->Person['Polis_Num'];
				}
			} else {
				$PolisInfo = "";
			}
		}
		
		echo "
			<td style=\"text-align: left;\">{$PersonCard_Code}</td>
			<td style=\"text-align: left;\">{$sBirthDate}</td>
			<td style=\"text-align: left;\">" . trim($this->Person['Address_Address']) . "</td>
			<td style=\"text-align: left;\">" . $this->Person['Job_Name'] . "</td>";
		echo ($this->pmUserData['Lpu_id'] == 81 && $this->regionNick == "ufa") ? "" : "{$ShowRegionTxt}";
		echo "
			<td style=\"text-align: left;\">
				{$this->Person['Person_Phone']}
				{$InetPhone}
				{$ShowAlerted}
			</td>";
		echo ($this->pmUserData['Lpu_id'] == 81 && $this->regionNick == "ufa") ? "" : "<td style=\"text-align: left;\">{$this->Person['Lpu_Nick']}</td>";
		echo ($this->pmUserData['Lpu_id'] == 81 && $this->regionNick == "ufa") ? "" : "<td style=\"text-align: center;\">{$Person_IsPriem}</td>";						

		// Для Астрахани выводится информация о полисе
		if ($this->regionNick == "astra") {
			echo "<td style=\"text-align: left;\">{$PolisInfo}</td>";
		}
		echo "<td style=\"text-align: left;\">{$pmUser_Name}</td>";
		If ( !isset($_GET['Print']) && !$IsPrint ) {
			If ( isset($this->Person) && $this->Person['Person_Filter'] != 1 ) {
				$this->ShowPrintOption();
			} else {
				echo "<td><br></td>";
			}
		}
	}

	/**
	 * Печать одной строки в списке на день для печатного варианта
	 */
	function PrintDayRowForPrint() {
		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents = "";

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";


		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText = "<img src=\"/images/star.gif\"> " . $this->TimetableGraf_begTime->format('H:i');
		} else {
			$sText = $this->TimetableGraf_begTime->format('H:i');
		}

		if ( isset($this->Person) ) {
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sTalonCode = !empty($this->Direction['Direction_TalonCode'])?', <b>код брони: '.$this->Direction['Direction_TalonCode'].'</b>':'';
			$sText .= " {$sPerson}{$sTalonCode}";
			$sEvents .= " style='text-align: left;'";
		} else {
			$sText .= " ";
			$sEvents .= " style='text-align: left;'";
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
		$this->PrintDayRow(true);
	}

	/**
	 * Всплывающая подсказка по записанному человеку
	 */
	function GetPersonTip() {
		$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);

		$Tip = "";
		$Tip .=" onmouseover=\"this.style.backgroundColor = '#aaaaff';\"";
		$Tip .= " ext:qtip=\"<b>Занято:</b> {$sPerson}<br><b>Оператор:</b> " . Fmt($this->pmUser['PMUser_Name']) . "<br><b>Изменено:</b> " . $this->TimetableGraf_updDT->format("H:i d.m.y");

		If ( $this->Direction['Lpu_Nick'] != '' ) { // есть направляющая ЛПУ, то есть есть направление
			$Tip .="<br/><b>Направление:</b> " . Fmt($this->Direction['Lpu_Nick']) . " №" . $this->Direction['Direction_Num'] . " от " . $this->Direction['Direction_Date'];
		}
		If ( $this->Direction['Diag_Code'] != '' ) { // показываем диагноз если есть
			$Tip .="<br/><b>Диагноз:</b> {$this->Direction['Diag_Code']}";
		}
		If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
			$Tip .="<br/><b>Помещен в очередь:</b> " . Fmt($this->Direction['QpmUser_Name']) . "<br><b>Дата:</b> " . $this->Direction['EvnQueue_insDT']->format("H:i d.m.y");
		}

		if ( isset($this->annotation) ) {
			$Tip .= join('', $this->annotation);
		}
		$Tip .="\"";

		return $Tip;
	}

	/**
	 * Вывод списка ссылок печати для строки записи
	 */
	function ShowPrintOption() {
		echo "<td><a style='color: red;font-weight: bold;font-size: 11px;' title='Печать талона амбулаторного пациента' onclick=\"Ext.getCmp('{$this->PanelID}').printMenu(this, {$this->Person['Person_id']}, {$this->TimetableGraf_id}, {$this->Direction['EvnDirection_tid']}); \" href='#' ><img src='/img/icons/print16.png' border=0 title='Печать талона амбулаторного пациента'></a></td>";
	}

	/**
	 * Печать одной ячейки в таблице при выписке направлений
	 * $IsForRow boolean Печать ячейки для ряда, с добавлением ФИО 
	 * $Person_id integer Переданный идентификатор записываемого человека, если бирка с таким человеком есть, но без направления, то давать на нее выписать направление (см #4001)
	 */
	function PrintCellForDirection( $IsForRow = false, $Person_id = null ) {

		$sClass = "work";
		$sText = "";
		$sEvents = "";
		$addSpan = "";
		$isNotCpecMZ = (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz');
		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = ''; Ext.select('td.commented').removeClass('commented-active');\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableGraf_begTime->format('H:i');

		if ( !isset($this->Person["Person_id"]) ) {

			if ( isset($this->annotation) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableGraf_IsDop != ""  ? '<br/>Дополнительная' : '') . "<br/><br/>" . join('', $this->annotation) . "\"";
				$sClass .= " commented";
				$sClass .= " tt-annot-{$this->aHash}"; 
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff'; Ext.select('td.tt-annot-{$this->aHash}').addClass('commented-active');\" ";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableGraf_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
				$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			}
		}
		// Добавляю контекстное меню #125232 для групповых бирок
		if(!empty($this->TimetableType_id) && $this->TimetableType_id == 14){
			$strDT = '';
			if (!empty($this->TimetableGraf_begTime))
				$strDT .= ", '".$this->TimetableGraf_begTime->format("d.m.Y") . "', '" . $this->TimetableGraf_begTime->format("H:i")."'";
			else
				$strDT .= ', null, null';
			$sEvents .= " oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableGraf_id}, " . (!empty($this->Person['Person_id']) ? $this->Person['Person_id'] : 'null') . ", " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ", " . var_export(IsInetUser($this->pmUser['pmUser_updID']), true) . "{$strDT}); return false;\"";
		}

		if ( isset($this->Person) /*|| (!empty($this->TimeTableGraf_countRec) && $this->TimeTableGraf_countRec>=$this->TimeTableGraf_PersRecLim)*/ ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
				$sClass .= " queue";
			}

			$sEvents .= $this->GetPersonTip();

			if (
				$this->TimetableGraf_begTime->format("Y-m-d H:i") >= $this->curDT->format("Y-m-d H:i") && (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					($this->pmUserData['Lpu_id'] == $this->pmUser['Lpu_id'] && $this->regionNick == 'pskov') || // пользователь того же ЛПУ, кто последний обновлял бирку
					IsLpuRegAdmin($this->mpData['Org_id']) // Администратор этого ЛПУ
			)) {
				$dir = (!empty($this->Direction['EvnDirection_id'])) ? ','.$this->Direction['EvnDirection_id'] : '';
				if($isNotCpecMZ)
					$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableGraf_id}{$dir});\" href='#'>X</a>";
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sText .= " Занято, {$sPerson}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {

			if ( in_array($this->TimetableGraf_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано другим оператором</b>\"";
			} else if ( $this->mpData["RecType_id"] == 3 ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Прием ведется только по 'живой очереди'</b>\"";
			} else if ($this->timetable_blocked && !empty($this->TimetableType_id) && in_array($this->TimetableType_id, array(1,11))) {
				$sClass .= " " . $this->TimetableType->getClass(1);
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано очередью</b>\"";
			} else {

				$current_day = new Datetime(date('Y-m-d'));
				$day_diff = $current_day->diff($this->TimetableGraf_begTime)->days;
				if ( $this->canRecord(true) ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Person);
					if ( getRegionNick() == 'perm' && !IsCZUser() && IsOtherLpuRegUser($this->mpData['Org_id']) && !(
							($day_diff > 1) || //запись на послезавтра или в пределах ближайших max_day дней
							($day_diff == 1 && date("H:i") < getCloseNextDayRecordTime())//запись на завтра, но до getCloseNextDayRecordTime() часов
							) ) {
						$sClass .= " locked";
						if($isNotCpecMZ)
							$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Можно выписывать направления в другую МО начиная с завтрашнего дня. Пожалуйста, выберите другое время.');\"";
					} elseif ( $this->TimetableGraf_begTime->format("Y-m-d H:i") < $this->curDT->format("Y-m-d H:i") ) { //  прошедший день или время
						If (
							(IsCZUser() || IsLpuRegAdmin($this->mpData['Org_id']))
							&& isset($_SESSION['setting']) && is_array($_SESSION['setting'])
							&& isset($_SESSION['setting']['server']) && is_array($_SESSION['setting']['server'])
							&& $_SESSION['setting']['server']['disallow_recording_for_elapsed_time'] != true
						) {
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}', '{$this->TimetableGraf_begTime->format("H:i")}');\"";
						} else {
							// Изменена формулировка предупреждения #16939
							$sClass .= " locked";
							if($isNotCpecMZ)
							
								$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
						}
					} else if($this->TimetableType_id==3){
						if($isNotCpecMZ)
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}', '{$this->TimetableGraf_begTime->format("H:i")}');\"";
					}else {
						if($isNotCpecMZ)
							$sEvents .=" onclick=\"Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableGraf_id}, '{$this->TimetableGraf_begTime->format("d.m.Y")}', '{$this->TimetableGraf_begTime->format("H:i")}');\" href='#' ";
					}
				} else if ( IsOtherLpuRegUser($this->mpData['Org_id']) && $this->mpData['MedStaffFact_IsDirRec'] == 1 ) {
					$sClass .= " locked";
					if($isNotCpecMZ)
						$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Выписка направлений к врачу запрещена, можно выписать направления только на внешние бирки.');\"";
				} else {
					$sClass .= " locked";
				}
			}
			if ( $IsForRow ) {
				$sText .= " Свободно";
				$sEvents .= " style='text-align: left;'";
			}
		}
		If ( $this->TimetableGraf_IsDop != "" ) {
			$sClass .= " dop";
		}
		//При записи 1 и более человек на бирку с типом "групповой прием" добавляем иконку
		if(!empty($this->TimeTableGraf_countRec) && $this->TimeTableGraf_countRec>0){
			$addSpan = "<span class='timetable-type-group-cell'></span>";
		}

		echo "<td class='$sClass' $sEvents>$sText $addSpan</td>";
	}

}