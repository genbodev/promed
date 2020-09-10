<?php

/**
 * TTimetableMedService - расширение абстрактного класса, описывающее бирку службы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      27.12.2011
 */
require_once(APPPATH . "libraries/TTimetable.php");

/**
 * Класс описывающий объект бирки поликлиники
 */
class TTimetableMedService extends TTimetable {

	/**
	 * Идентификатор бирки
	 */
	var $TimetableMedService_id = NULL;

	/**
	 * Тип бирки
	 */
	var $TimetableType_id = NULL;

	/**
	 * Дата изменения бирки
	 */
	var $TimetableMedService_updDT = NULL;

	/**
	 * Время бирки
	 */
	var $TimetableMedService_begTime = NULL;

	/**
	 * День бирки
	 */
	var $TimetableMedService_Day = NULL;

	/**
	 * Признак дополнительной бирки
	 */
	var $TimetableMedService_IsDop = NULL;

	/**
	 * Признак модерации бирки
	 */
	var $TimetableMedService_IsModerated = NULL;

	/**
	 * Примечание по бирке
	 */
	var $TimetableExtend = NULL;

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
	 * Данные по текущей службе
	 */
	var $msData = NULL;

	/**
	 * Идентификатор панели
	 */
	var $PanelID = NULL;

	/**
	 * Назначение для которого грузится расписание
	 */
	var $EvnPrescr_id = NULL;

	/**
	 * Признак загрузки расписания для формы ExtJS 6
	 */
	var $isExt6 = false;

	/**
	 * Как обращаться к ExtJS
	 */
	var $ExtJSFunc = 'Ext';

	/**
	 * Данные о текущем пользователе из сессии
	 */
	var $pmUserdata = NULL;

	/**
	 * Менеджер типов бирок
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

		if ( isset($arFields) ) {
			$this->TimetableMedService_id = $arFields['TimetableMedService_id'];
			$this->TimetableType_id = (isset($arFields['TimetableType_id']) ? $arFields['TimetableType_id'] : 1);
			$this->TimetableMedService_updDT = DateTime::createFromFormat('d.m.Y H:i:s', $arFields['TimetableMedService_updDT']);
			$this->TimetableMedService_begTime = DateTime::createFromFormat('d.m.Y H:i:s', $arFields['TimetableMedService_begTime']);
			$this->TimetableMedService_Day = $arFields['TimetableMedService_Day'];
			$this->TimetableMedService_IsDop = $arFields['TimetableMedService_IsDop'];
			$this->TimetableMedService_IsModerated = (isset($arFields['TimetableMedService_IsModerated']) ? $arFields['TimetableMedService_IsModerated'] : 0);
			if ( isset($arFields['TimetableExtend_Descr']) && $arFields['TimetableExtend_Descr'] ) {
				$this->TimetableExtend = array(
					'Descr' => $arFields['TimetableExtend_Descr'],
					'pmUser_Name' => $arFields['TimetableExtend_pmUser_Name'],
					'setDT' => DateTime::createFromFormat('d.m.Y H:i:s', $arFields['TimetableExtend_updDT'])
				);
			}

			$this->pmUser = array(
				'pmUser_updID' => $arFields['pmUser_updID'],
				'PMUser_Name' => $arFields['PMUser_Name'],
				'Lpu_id' => $arFields['pmUser_Lpu_id']
			);
			if ( isset($data['msData']) ) {
				$this->msData = array(
					'Org_id' => (isset($data['msData']['Org_id'])) ? $data['msData']['Org_id'] : null,
					'LpuUnit_id' => (isset($data['msData']['LpuUnit_id'])) ? $data['msData']['LpuUnit_id'] : null
				);
			}

			if ( isset($arFields['Person_id']) ) {
				$this->Person = array(
					'Person_id' => $arFields['Person_id'],
					'Person_Surname' => $arFields['Person_Surname'],
					'Person_Firname' => $arFields['Person_Firname'],
					'Person_Secname' => $arFields['Person_Secname'],
					'Person_BirthDay' => DateTime::createFromFormat('d.m.Y', $arFields['Person_BirthDay']),
					'Person_Phone' => $arFields['Person_Phone'],
					'Address_Address' => (isset($arFields['Address_Address']) ? $arFields['Address_Address'] : NULL),
					'KLTown_id' => (isset($arFields['KLTown_id']) ? $arFields['KLTown_id'] : NULL),
					'KLStreet_id' => (isset($arFields['KLStreet_id']) ? $arFields['KLStreet_id'] : NULL),
					'Address_House' => (isset($arFields['Address_House']) ? $arFields['Address_House'] : NULL),
					'PrivilegeType_id' => (isset($arFields['PrivilegeType_id']) ? $arFields['PrivilegeType_id'] : NULL),
					'Job_Name' => (isset($arFields['Job_Name']) ? $arFields['Job_Name'] : NULL),
					'Lpu_Nick' => (isset($arFields['Lpu_Nick']) ? $arFields['Lpu_Nick'] : NULL),
					'Person_InetPhone' => (isset($arFields['Person_InetPhone']) ? $arFields['Person_InetPhone'] : NULL)
				);
			}
			$this->Direction = array(
				'EvnDirection_id' => $arFields['EvnDirection_id'],
				'Direction_Num' => $arFields['Direction_Num'],
				'Direction_Date' => $arFields['Direction_Date'],
				'Direction_TalonCode' => $arFields['Direction_TalonCode'],
				'Lpu_Nick' => $arFields['DirLpu_Nick'],
				'Diag_Code' => $arFields['Diag_Code'],
				'QpmUser_Name' => $arFields['QpmUser_Name'],
				'EvnPrescr_id' => $arFields['EvnPrescr_id'],
				'EvnQueue_insDT' => DateTime::createFromFormat('d.m.Y H:i:s', $arFields['EvnQueue_insDT']),
			);

			$this->arReserved = $arReserved;

			$this->pmUserData = $data['pmUserData'];
			if ( empty($data['PanelID']) ) {
				$this->PanelID = 'TTSSchedulePanel';
			} else {
				$this->PanelID = $data['PanelID'];
			}

			if ( !empty($data['isExt6']) ) {
				$this->isExt6 = true;
				$this->ExtJSFunc = 'Ext6';
			} else {
				$this->isExt6 = false;
				$this->ExtJSFunc = 'Ext';
			}

			if ( !empty($data['EvnPrescr_id']) ) {
				$this->EvnPrescr_id = $data['EvnPrescr_id'];
			} else {
				$this->EvnPrescr_id = null;
			}
		}
		
		loadLibrary('TTimetableTypes');
		$this->TimetableType = TTimetableTypes::instance()->getTimetableType($this->TimetableType_id);
	}

	/**
	 * Разрешена ли запись на бирку переданному пользователю
	 */
	function canRecord($direction = false) {
		if ($direction && $this->TimetableType->inSources(6)) {
			return true;
		}
		
		return canRecord(
			array(
				'TimetableMedService_Day' => $this->TimetableMedService_Day,
				'LpuUnit_id' => $this->msData['LpuUnit_id'],
				'Org_id' => $this->msData['Org_id'],
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
		$sClass = "work";
		$sText = "";
		$sEvents = "";

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableMedService_begTime->format('H:i');
		
		if ( !isset($this->Person["Person_id"]) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";

			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
			}
		}

		if ( isset($this->Person) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' && $this->TimetableType_id == 1 ) { // записан из очереди
				$sClass .= " queue";
			}

			if (!empty($this->EvnPrescr_id) && $this->Direction['EvnPrescr_id'] == $this->EvnPrescr_id) {
				$sClass .= " current";
			}

			$sEvents .= $this->GetPersonTip();

			if ($this->isExt6 && !empty($this->EvnPrescr_id) && $this->Direction['EvnPrescr_id'] == $this->EvnPrescr_id) {
				$sEvents .= " onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').clearOwnTime({$this->TimetableMedService_id}, null);\" href='#' ";
			} else {
				if (
					$this->TimetableMedService_begTime->format("Y-m-d H:i") >= date("Y-m-d H:i") && (
						IsCZAdmin() || // администратор ЦЗ
						$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
						IsLpuRegAdmin($this->msData['Org_id']) // Администратор этого ЛПУ
					)) {
					$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedService_id}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ");\" href='#' >X</a>";
				}
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sTalonCode = !empty($this->Direction['Direction_TalonCode'])?', <b>код брони: '.$this->Direction['Direction_TalonCode'].'</b>':'';
				$sText .= " Занято, {$sPerson}{$sTalonCode}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableMedService_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано другим оператором</b>\"";
			} else {
				if ( $this->canRecord(true) ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Person);
					if ( $this->TimetableMedService_begTime->format("Y-m-d H:i") < date("Y-m-d H:i") ) { //  прошедшее время
						If ( IsCZUser() || IsLpuRegAdmin($this->msData['Org_id']) ) {
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) {$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\"";
							}
						} else {
							$sClass .= " locked";
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"{$this->ExtJSFunc}.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
							}
						}
					}else if($this->TimetableType_id==3){
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) {$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\"";
						}
					} else {
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\" href='#' ";
						}
					}
				} else {
					$sClass .= " locked";
				}
			}
			if ( $IsForRow ) {
				$sText .= " Свободно";
				$sEvents .= " style='text-align: left;'";
			}
		}
		
		If ( $this->TimetableMedService_IsDop != "" ) {
			$sClass .= " dop";
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
	}

	/**
	 * Печать одной ячейки в таблице на 2 недели для печатного варианта
	 */
	function PrintCellForPrint() {
		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents = "";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) )
			$sText = "<img src=\"/images/star.gif\"> " . SQLToTimeStr($this->TimetableMedService_begTime);
		else
			$sText = SQLToTimeStr($this->TimetableMedService_begTime);

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
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableMedService_begTime->format('H:i');
		
		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		if ( !isset($this->Person) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
			}
		}

		$sPerson = '';
		If ( !isset($this->Person) ) {
			$sEvents .= " onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').toggleSelection(this, {$this->TimetableMedService_id}, 0); return false;\" oncontextmenu=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableMedService_id}, 0, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . "); return false;\"";
			$sClass .= " free";
		} else {
			$sEvents .= " oncontextmenu=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableMedService_id}, {$this->Person['Person_id']}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . " ); return false;\"";
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sClass .= " person";
			
			$sEvents .= $this->GetPersonTip();

			if ( isset($this->DayID) ) {
				$sText .= " Занято, {$sPerson}";
			}
		}
		
		If ( $this->TimetableMedService_IsDop != "" ) {
			$sClass .= " dop";
		}
		
		// Прошедшие дни
		if ( $this->TimetableMedService_begTime->format("Y-m-d H:i") < date("Y-m-d H:i") ) {
			$sClass .= " old";			
		}

		echo "<td id='TTMS_{$this->TimetableMedService_id}' class='$sClass' $sEvents>$sText</td>";
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
			if ( $this->Person['Person_BirthDay'] != '' )
				$sBirthDate = $this->Person['Person_BirthDay']->format('d.m.Y');
		}

		if ( isset($this->Person['Person_InetPhone']) ) {
			$InetPhone = "<br/>Из интернета: {$this->Person['Person_InetPhone']}";
		} else {
			$InetPhone = "";
		}
		echo "
			<td style=\"text-align: left;\">{$sBirthDate}</td>
			<td style=\"text-align: left;\">" . trim($this->Person['Address_Address']) . "</td>
			<td style=\"text-align: left;\">" . $this->Person['Job_Name'] . "</td>
			<td style=\"text-align: left;\">
				{$this->Person['Person_Phone']}
				{$InetPhone}
			</td>
			<td style=\"text-align: left;\">{$this->Person['Lpu_Nick']}</td>";
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


		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) )
			$sText = "<img src=\"/images/star.gif\"> " . $this->TimetableMedService_begTime->format('H:i');
		else
			$sText = $this->TimetableMedService_begTime->format('H:i');

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
		$Tip .=" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Занято:</b> {$sPerson}<br><b>Оператор:</b> " . Fmt($this->pmUser['PMUser_Name']) . "<br><b>Изменено:</b> " . $this->TimetableMedService_updDT->format("H:i d.m.y");
		If ( $this->Direction['Lpu_Nick'] != '' ) { // есть направляющая ЛПУ, то есть есть направление
			$Tip .="<br/><b>Направление:</b> " . Fmt($this->Direction['Lpu_Nick']) . " №" . $this->Direction['Direction_Num'] . " от " . $this->Direction['Direction_Date'];
		}
		If ( $this->Direction['Diag_Code'] != '' ) { // показываем диагноз если есть
			$Tip .="<br/><b>Диагноз:</b> {$this->Direction['Diag_Code']}";
		}
		If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
			$Tip .="<br/><b>Помещен в очередь:</b> " . Fmt($this->Direction['QpmUser_Name']) . "<br><b>Дата:</b> " . $this->Direction['EvnQueue_insDT']->format("H:i d.m.y");
		}

		if ( isset($this->TimetableExtend) ) {
			$Tip .= "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
		}
		$Tip .="\"";

		return $Tip;
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

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		$sText .= $this->TimetableMedService_begTime->format('H:i');

		if ( !isset($this->Person["Person_id"]) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Person) . ( $this->TimetableMedService_IsDop != ""  ? '<br/>Дополнительная' : '') . "\"";
			}
		}


		if ( isset($this->Person) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
				$sClass .= " queue";
			}

			if (!empty($this->EvnPrescr_id) && $this->Direction['EvnPrescr_id'] == $this->EvnPrescr_id) {
				$sClass .= " current";
			}

			$sEvents .= $this->GetPersonTip();

			if ($this->isExt6 && !empty($this->EvnPrescr_id) && $this->Direction['EvnPrescr_id'] == $this->EvnPrescr_id) {
				$sEvents .= " onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').clearOwnTime({$this->TimetableMedService_id}, null);\" href='#' ";
			} else {
				if (
					$this->TimetableMedService_begTime->format("Y-m-d H:i") >= date("Y-m-d H:i") && (
						IsCZAdmin() || // администратор ЦЗ
						$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
						IsLpuRegAdmin($this->msData['Org_id']) // Администратор этого ЛПУ
					)) {
					If ($this->Direction['Direction_Num'] == '') {// нет выписанного направления
						if (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedService_id});\" href='#'>X</a>";
					} else {
						if (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedService_id}, {$this->Direction['EvnDirection_id']});\" href='#' >X</a>";
					}
				}
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sText .= " Занято, {$sPerson}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableMedService_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents .=" onmouseover=\"this.style.backgroundColor = '#aaaaff'; Tip('<b>Заблокировано другим оператором</b>')\"";
			} else {
				if ( $this->canRecord(true) ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Person);
					if ( $this->TimetableMedService_begTime->format("Y-m-d H:i") < date("Y-m-d H:i") ) { //  прошедшее время
						If ( IsCZUser() || IsLpuRegAdmin($this->msData['Org_id']) ) {
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) {$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\"";
						} else {
							$sClass .= " locked";
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"{$this->ExtJSFunc}.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
						}
					}else if($this->TimetableType_id==3){
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) {$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\"";
					} else {
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"{$this->ExtJSFunc}.getCmp('{$this->PanelID}').recordPerson({$this->TimetableMedService_id}, '{$this->TimetableMedService_begTime->format("d.m.Y")}', '{$this->TimetableMedService_begTime->format("H:i")}');\" href='#' ";
					}
				} else {
					$sClass .= " locked";
				}
			}
			if ( $IsForRow ) {
				$sText .= " Свободно";
				$sEvents .= " style='text-align: left;'";
			}
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
	}

}