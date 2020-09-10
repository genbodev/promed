<?php

/**
 * TTimetableStac - расширение абстрактного класса, описывающее бирку стационара
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
 * Класс описывающий объект бирки стационара
 */
class TTimetableStac extends TTimetable {

	/**
	 * Идентификатор бирки
	 */
	var $TimetableStac_id = NULL;

	/**
	 * Тип бирки
	 */
	var $TimetableType_id = NULL;

	/**
	 * Дата изменения бирки
	 */
	var $TimetableStac_updDT = NULL;

	/**
	 * Время бирки
	 */
	var $TimetableStac_setDate = NULL;

	/**
	 * Наименование отделения
	 */
	var $LpuSectionName = NULL;

	/**
	 * День бирки
	 */
	var $TimetableStac_Day = NULL;

	/**
	 * Признак модерации бирки
	 */
	var $TimetableStac_IsModerated = NULL;

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
	 * Тип бирки
	 */
	var $LpuSectionBedType_id = NULL;

	/**
	 * Идентификатор панели
	 */
	var $PanelID = NULL;

	/**
	 * Данные о текущем пользователе из сессии
	 */
	var $pmUserData = NULL;

	/**
	 * Данные по текущему врачу
	 */
	var $lsData = NULL;
	
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
		global $QS;

		$this->TimetableStac_id = $arFields['TimetableStac_id'];
		$this->TimetableType_id = (isset($arFields['TimetableType_id']) ? $arFields['TimetableType_id'] : 1);
		$this->TimetableStac_updDT = $arFields['TimetableStac_updDT'];
		$this->TimetableStac_setDate = $arFields['TimetableStac_setDate'];
		$this->LpuSectionName = !empty($arFields['LpuSectionName']) ? $arFields['LpuSectionName'] : null;
		$this->TimetableStac_Day = $arFields['TimetableStac_Day'];
		$this->TimetableStac_IsModerated = NULL;
		$this->LpuSectionBedType_id = $arFields['LpuSectionBedType_id'];
		if ( isset($arFields['TimetableExtend_Descr']) && $arFields['TimetableExtend_Descr'] ) {
			$this->TimetableExtend = array(
				'Descr' => $arFields['TimetableExtend_Descr'],
				'pmUser_Name' => $arFields['TimetableExtend_pmUser_Name'],
				'setDT' => $arFields['TimetableExtend_updDT']
			);
		}

		$this->pmUser = array(
			'pmUser_updID' => $arFields['pmUser_updID'],
			'PMUser_Name' => $arFields['PMUser_Name'],
			'Lpu_id' => $arFields['pmUser_Lpu_id']
		);
		if ( isset($data['lsData']) ) {
			$this->lsData = array(
				'RecType_id' => (isset($data['lsData']['RecType_id'])) ? $data['lsData']['RecType_id'] : null,
				'Org_id' => (isset($data['lsData']['Org_id'])) ? $data['lsData']['Org_id'] : null,
				'LpuUnit_id' => (isset($data['lsData']['LpuUnit_id'])) ? $data['lsData']['LpuUnit_id'] : null,
			);
		}
		if ( isset($arFields['Person_id']) ) {
			$this->Person = array(
				'Person_id' => $arFields['Person_id'],
				'Person_Surname' => $arFields['Person_Surname'],
				'Person_Firname' => $arFields['Person_Firname'],
				'Person_Secname' => $arFields['Person_Secname'],
				'Person_BirthDay' => $arFields['Person_BirthDay'],
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
			'Lpu_Nick' => $arFields['DirLpu_Nick'],
			'Diag_Code' => $arFields['Diag_Code'],
			'QpmUser_Name' => $arFields['QpmUser_Name'],
			'EvnQueue_insDT' => $arFields['EvnQueue_insDT'],
		);

		$this->arReserved = $arReserved;

		$this->pmUserData = $data['pmUserData'];
		if ( empty($data['PanelID']) ) {
			$this->PanelID = 'TTSSchedulePanel';
		} else {
			$this->PanelID = $data['PanelID'];
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
				'TimetableStac_Day' => $this->TimetableStac_Day,
				'LpuUnit_id' => $this->lsData['LpuUnit_id'],
				'Org_id' => $this->lsData['Org_id'],
				'TimetableType_id' => $this->TimetableType_id
			),
			$this->pmUserData
		);
	}
	
	/**
	 * Возвращает строку для посдказки с типом бирки
	 */
	function getBedTypeTip() {
		if ( $this->LpuSectionBedType_id == "1" ) {
			return "<br/>Мужская койка";
		}
		if ( $this->LpuSectionBedType_id == "2" ) {
			return "<br/>Женская койка";
		}
		if ( $this->LpuSectionBedType_id == "3" ) {
			return "<br/>Общая койка";
		}
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
		
		$formatted = ConvertDateFormat($this->TimetableStac_setDate, 'H:i');
		$sText .= $formatted == '00:00' ? '' : $formatted . ' ';
		$sText .= $this->getLpuSectionBedType();

		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		if ( !isset($this->Person) ) {
			
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";

			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . ConvertDateFormat($this->TimetableExtend['setDT'],'d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "\"";
			}
		}


		if ( isset($this->Person) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' && $this->TimetableType_id == 1 ) { // записан из очереди
				$sClass .= " queue";
			}

			$sEvents .= $this->GetPersonTip();

			if (
				ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") >= date("Y-m-d") && (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					IsLpuRegAdmin($this->lsData['Org_id']) // Администратор этого ЛПУ
			)) {
				If ($this->Direction['Direction_Num'] == '') {// нет выписанного направления
					$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableStac_id});\" href='#'>X</a>";
				} else {
					$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableStac_id}, {$this->Direction['EvnDirection_id']});\" href='#' >X</a>";
				}
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sText .= " Занято, {$sPerson}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableStac_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents .=" onmouseover=\"this.style.backgroundColor = '#aaaaff'; Tip('<b>Заблокировано другим оператором</b>')\"";
			} else {
				$sClass .= " free";

				if ( $this->canRecord() ) {
					if ( ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") < date("Y-m-d") ) { //  прошедший день
						If ( IsCZUser() || IsLpuRegAdmin($this->lsData['Org_id']) ) {
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '" .ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y") ."');\"";
							}
						} else {
							$sClass .= " locked";
							if (!$this->readOnly) {
								$sEvents .= " onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
							}
						}
					}else if($this->TimetableType_id==3){
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '". ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y") . "');\"";
						}
					} else {
						if (!$this->readOnly) {
							$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '" . ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y"). "');\" href='#' ";
						}
					}
				}
			}
			if ( $IsForRow ) {
				$sText .= " Свободно";
				$sEvents .= " style='text-align: left;'";
			}
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
			$sText = "<img src=\"/images/star.gif\"> " . SQLToTimeStr($this->TimetableStac_setDate);
		else
			$sText = SQLToTimeStr($this->TimetableStac_setDate);

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
	 * Возврат символа типа коек в отделении
	 */
	function getLpuSectionBedType() {
		if ( $this->LpuSectionBedType_id == "1" ) {
			return " М";
		}
		if ( $this->LpuSectionBedType_id == "2" ) {
			return " Ж";
		}
		if ( $this->LpuSectionBedType_id == "3" ) {
			return " О";
		}
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

		$formatted = ConvertDateFormat($this->TimetableStac_setDate, 'H:i');
		$sText .= $formatted == '00:00' ? '' : $formatted . ' ';
		$sText .= $this->getLpuSectionBedType();
		
		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		if ( !isset($this->Person) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";

			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . ConvertDateFormat($this->TimetableExtend['setDT'],'d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "\"";
			}
		}


		$sPerson = '';

		If ( !isset($this->Person) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').toggleSelection(this, {$this->TimetableStac_id}); return false;\" oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableStac_id}, 0, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . "); return false;\"";
			$sClass .= " free";
		} else {
			$sEvents .= " oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableStac_id}, {$this->Person['Person_id']}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . "); return false;\"";
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sClass .= " person";
			
			$sEvents .= $this->GetPersonTip();

			if ( isset($this->DayID) ) {
				$sText .= " Занято, {$sPerson}";
			}
		}
		
		// Прошедшие дни
		if ( ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") < date("Y-m-d") ) {
			$sClass .= " old";			
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
	}

	/**
	 * Печать ячейки в суммарной таблице
	 */
	function PrintCellSummary() {
		$sClass = "work";
		$sText = "";
		$sEvents = "";

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";

		if ( in_array($this->Person['PrivilegeType_id'], array(11, 20, 40, 50, 140, 150)) ) {
			$sText .= "<img src=\"/img/icons/star16.png\"> ";
		}
		
		$formatted = ConvertDateFormat($this->TimetableStac_setDate, 'H:i');
		$sText .= $formatted == '00:00' ? '' : $formatted . ' ';
		$sText .= $this->getLpuSectionBedType();
		
		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
		$sEvents .=" ext:qtip=\"<b>Отделение:</b> <br/> ". $this->LpuSectionName ." \"";
		
		// Прошедшие дни
		if ( ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") < date("Y-m-d") ) {
			$sClass .= " old";
		}

		echo "<td class='$sClass' $sEvents>$sText</td>";
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
				$sBirthDate = ConvertDateFormat($this->Person['Person_BirthDay'],'d.m.Y');
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
			$sText = "<img src=\"/img/icons/star16.png\"> " . $this->getLpuSectionBedType();
		else
			$sText = $this->getLpuSectionBedType();

		if ( isset($this->Person) ) {
			$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
			$sText .= " {$sPerson}";
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
		$Tip .=" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Занято:</b> {$sPerson}<br><b>Оператор:</b> " . Fmt($this->pmUser['PMUser_Name']) . "<br><b>Изменено:</b> " . ConvertDateFormat($this->TimetableStac_updDT,"H:i d.m.y");
		If ( $this->Direction['Lpu_Nick'] != '' ) { // есть направляющая ЛПУ, то есть есть направление
			$Tip .="<br/><b>Направление:</b> " . Fmt($this->Direction['Lpu_Nick']) . " №" . $this->Direction['Direction_Num'] . " от " . $this->Direction['Direction_Date'];
		}
		If ( $this->Direction['Diag_Code'] != '' ) { // показываем диагноз если есть
			$Tip .="<br/><b>Диагноз:</b> {$this->Direction['Diag_Code']}";
		}
		If ( $this->Direction['QpmUser_Name'] != '' ) { // записан из очереди
			$Tip .="<br/><b>Помещен в очередь:</b> " . Fmt($this->Direction['QpmUser_Name']) . "<br><b>Дата:</b> " . ConvertDateFormat($this->Direction['EvnQueue_insDT'],"H:i d.m.y");
		}

		if ( isset($this->TimetableExtend) ) {
			$Tip .= "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . ConvertDateFormat($this->TimetableExtend['setDT'],'d.m.Y H:i') . "</i></div>\"";
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
			$sText .= "<img src=\"/img/icons/star16.png\"> " . $this->getLpuSectionBedType();
		}
		$sText .= $this->getLpuSectionBedType();
		
		$sClass .= " " . $this->TimetableType->getClass($this->Person);
		if ( !isset($this->Person) ) {
			
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";

			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " .ConvertDateFormat( $this->TimetableExtend['setDT'],'d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . $this->getBedTypeTip() . "\"";
			}
		}

		if ( isset($this->Person) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Person);

			If ( $this->Direction['QpmUser_Name'] != '' && $this->TimetableType_id == 1 ) { // записан из очереди
				$sClass .= " queue";
			}

			$sEvents .= $this->GetPersonTip();

			if (
				ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") >= date("Y-m-d") && (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					IsLpuRegAdmin($this->lsData['Org_id']) // Администратор этого ЛПУ
			)) {
				If ( $this->Direction['Direction_Num'] == '' ) {// нет выписанного направления
					if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
						$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableStac_id});\" href='#'>X</a>";
				} else {
					if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
						$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableStac_id}, {$this->Direction['EvnDirection_id']});\" href='#' >X</a>";
				}
			}
			if ( $IsForRow ) {
				$sPerson = trim($this->Person['Person_Surname']) . " " . trim($this->Person['Person_Firname']) . " " . trim($this->Person['Person_Secname']);
				$sText .= " Занято, {$sPerson}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableStac_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents .=" onmouseover=\"this.style.backgroundColor = '#aaaaff'; Tip('<b>Заблокировано другим оператором</b>')\"";
			} else {
				$sClass .= " free";

				if ( $this->canRecord(true) ) {
					if ( ConvertDateFormat($this->TimetableStac_setDate,"Y-m-d") < date("Y-m-d") ) { //  прошедший день
						If ( IsCZUser() || IsLpuRegAdmin($this->lsData['Org_id']) ) {
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '". ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y") . "');\"";
						} else {
							$sClass .= " locked";
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
						}
					}else if($this->TimetableType_id==3){
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '". ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y")."');\"";
					} else {
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"Ext.getCmp('{$this->PanelID}').recordPerson({$this->TimetableStac_id}, '" . ConvertDateFormat($this->TimetableStac_setDate,"d.m.Y") . "');\" href='#' ";
					}
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