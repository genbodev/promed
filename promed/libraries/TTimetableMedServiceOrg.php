<?php

/**
 * TTimetableMedServiceOrg - расширение абстрактного класса, описывающее бирку службы
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
class TTimetableMedServiceOrg extends TTimetable {

	/**
	 * Идентификатор бирки
	 */
	var $TimetableMedServiceOrg_id = NULL;

	/**
	 * Тип бирки
	 */
	var $TimetableType_id = NULL;

	/**
	 * Дата изменения бирки
	 */
	var $TimetableMedServiceOrg_updDT = NULL;

	/**
	 * Время бирки
	 */
	var $TimetableMedServiceOrg_begTime = NULL;

	/**
	 * День бирки
	 */
	var $TimetableMedServiceOrg_Day = NULL;


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
	var $Org = NULL;

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
	 * Данные о текущем пользователе из сессии
	 */
	var $pmUserdata = NULL;

	/**
	 * Менеджер типов бирок
	 */
	var $TimetableType = NULL;
	
	/**
	 * Конструктор бирки, принимает данные и заполняет поля объекта
	 */
	function __construct( $arFields = NULL, $arReserved = NULL, $data = NULL ) {

		if ( isset($arFields) ) {
			$this->TimetableMedServiceOrg_id = $arFields['TimetableMedServiceOrg_id'];
			$this->TimetableMedServiceOrg_updDT = $arFields['TimetableMedServiceOrg_updDT'];
			$this->TimetableMedServiceOrg_begTime = $arFields['TimetableMedServiceOrg_begTime'];
			$this->TimetableMedServiceOrg_Day = $arFields['TimetableMedServiceOrg_Day'];
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
			if ( isset($data['msData']) ) {
				$this->msData = array(
					'Org_id' => (isset($data['msData']['Org_id'])) ? $data['msData']['Org_id'] : null,
					'LpuUnit_id' => (isset($data['msData']['LpuUnit_id'])) ? $data['msData']['LpuUnit_id'] : null
				);
			}

			if ( isset($arFields['Org_id']) ) {
				$this->Org = array(
					'Org_id' => $arFields['Org_id'],
					'Org_Nick' => $arFields['Org_Nick'],
					'Org_Phone' => $arFields['Org_Phone'],
					'Address_Address' => $arFields['Address_Address'],
				);
			}
			
			$this->arReserved = $arReserved;

			$this->pmUserData = $data['pmUserData'];
			if ( empty($data['PanelID']) ) {
				$this->PanelID = 'TTSSchedulePanel';
			} else {
				$this->PanelID = $data['PanelID'];
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
				'TimetableMedServiceOrg_Day' => $this->TimetableMedServiceOrg_Day,
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

		
		$sText .= $this->TimetableMedServiceOrg_begTime->format('H:i');
		
		if ( !isset($this->Org["Org_id"]) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";

			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip()  . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip() . "\"";
			}
		}

		if ( isset($this->Org) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Org);

			

			$sEvents .= $this->GetPersonTip();

			if (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					IsLpuRegAdmin($this->msData['Org_id']) // Администратор этого ЛПУ
			) {
				$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedServiceOrg_id}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . ");\" href='#' >X</a>";
			}
			if ( $IsForRow ) {
				$sOrg = trim($this->Org['Org_Nick']);
				$sText .= " Занято, {$sOrg}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableMedServiceOrg_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents = " onmouseout=\"this.style.backgroundColor = '';\" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Заблокировано другим оператором</b>\"";
			} else {
				if ( $this->canRecord() ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Org);
					if ( $this->TimetableMedServiceOrg_begTime->format("Y-m-d H:i") < date("Y-m-d H:i") ) { //  прошедшее время
						If ( IsCZUser() || IsLpuRegAdmin($this->msData['Org_id']) ) {
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\"";
						} else {
							$sClass .= " locked";
							$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
						}
					}else if($this->TimetableType_id==3){
						$sEvents .=" onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\"";
					} else {
						$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\" href='#' ";
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

	/**
	 * Печать одной ячейки в таблице на 2 недели для печатного варианта
	 */
	function PrintCellForPrint() {
		$sClass = "work";
		$sText = "&nbsp;";
		$sEvents = "";

		
		$sText = SQLToTimeStr($this->TimetableMedServiceOrg_begTime);

		if ( isset($this->Org) ) {
			$sOrg = trim($this->Org['Org_Nick']);
			$sText .= " {$sOrg}";
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

		
		$sText .= $this->TimetableMedServiceOrg_begTime->format('H:i');
		
		$sClass .= " " . $this->TimetableType->getClass($this->Org);
		if ( !isset($this->Org) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Org) . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Org) . "\"";
			}
		}

		$sOrg = '';
		If ( !isset($this->Org) ) {
			$sEvents .= " onclick=\"Ext.getCmp('{$this->PanelID}').toggleSelection(this, {$this->TimetableMedServiceOrg_id}, 0); return false;\" oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableMedServiceOrg_id}, 0, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . "); return false;\"";
			$sClass .= " free";
		} else {
			$sEvents .= " oncontextmenu=\"Ext.getCmp('{$this->PanelID}').openContextMenu(this, {$this->TimetableMedServiceOrg_id}, {$this->Org['Org_id']}, " . (!empty($this->Direction['Direction_Num']) ? $this->Direction['EvnDirection_id'] : 'null') . " ); return false;\"";
			$sOrg = trim($this->Org['Org_Nick']);
			$sClass .= " Org";
			
			$sEvents .= $this->GetPersonTip();

			if ( isset($this->DayID) ) {
				$sText .= " Занято, {$sOrg}";
			}
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
		
		$sText = $this->TimetableMedServiceOrg_begTime->format('H:i');
		
		echo "
			<td style=\"text-align: center;\">{$sText}</td>
			<td style=\"text-align: left;\">{$this->Org['Org_Nick']}</td>
			<td style=\"text-align: left;\">{$this->Org['Address_Address']}</td>
			<td style=\"text-align: left;\">{$this->Org['Org_Phone']}</td>
			<td style=\"text-align: left;\">&nbsp;</td>";
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

		$this->PrintDayRow(true);
	}

	/**
	 * Всплывающая подсказка по записанному человеку
	 */
	function GetPersonTip() {
		$sOrg = trim(htmlspecialchars($this->Org['Org_Nick']));

		$Tip = "";
		$Tip .=" onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ext:qtip=\"<b>Занято:</b> {$sOrg}<br><b>Оператор:</b> " . Fmt($this->pmUser['PMUser_Name']) . "<br><b>Изменено:</b> " . $this->TimetableMedServiceOrg_updDT->format("H:i d.m.y");
		
		

		if ( isset($this->TimetableExtend) ) {
			$Tip .= "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
		}
		$Tip .="\"";

		return $Tip;
	}

	/**
	 * Печать одной ячейки в таблице при выписке направлений
	 * $IsForRow boolean Печать ячейки для ряда, с добавлением ФИО 
	 * $Org_id integer Переданный идентификатор записываемого человека, если бирка с таким человеком есть, но без направления, то давать на нее выписать направление (см #4001)
	 */
	function PrintCellForDirection( $IsForRow = false, $Org_id = null ) {
		$sClass = "work";
		$sText = "";
		$sEvents = "";

		$sClass .= " active";
		$sEvents .= " onmouseout=\"this.style.backgroundColor = '';\"";

		
		$sText .= $this->TimetableMedServiceOrg_begTime->format('H:i');

		if ( !isset($this->Org["Org_id"]) ) {
			$sEvents .= " onmouseover=\"this.style.backgroundColor = '#aaaaff';\" ";
			if ( isset($this->TimetableExtend) ) {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Org) . "<br/><br/><div class='ttcomments'>" . nl2br(htmlspecialchars($this->TimetableExtend['Descr'])) . "<br/><i>" . $this->TimetableExtend['pmUser_Name'] . ", " . $this->TimetableExtend['setDT']->format('d.m.Y H:i') . "</i></div>\"";
				$sClass .= " commented";
			} else {
				$sEvents .=" ext:qtip=\"" . $this->TimetableType->getTip($this->Org) . "\"";
			}
		}


		if ( isset($this->Org) ) {
			$sClass .= " " . $this->TimetableType->getClass($this->Org);

			

			$sEvents .= $this->GetPersonTip();

			if (
					IsCZAdmin() || // администратор ЦЗ
					$this->pmUserData['pmUser_id'] == $this->pmUser['pmUser_updID'] || // тот же пользователь, кто последний обновлял бирку
					IsLpuRegAdmin($this->msData['Org_id']) // Администратор этого ЛПУ
			) {
				If ( $this->Direction['Direction_Num'] == '' ) {// нет выписанного направления
					if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
						$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedServiceOrg_id});\" href='#'>X</a>";
				} else {
					if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
						$sText .= " <a style='color: red;font-weight: bold;font-size: 11px;' title='Удалить запись' onclick=\"Ext.getCmp('{$this->PanelID}').clearTime({$this->TimetableMedServiceOrg_id}, {$this->Direction['EvnDirection_id']});\" href='#' >X</a>";
				}
			}
			if ( $IsForRow ) {
				$sOrg = trim($this->Org['Org_Nick']);
				$sText .= " Занято, {$sOrg}";
				$sEvents .= " style='text-align: left;'";
			}
		} else {
			if ( in_array($this->TimetableMedServiceOrg_id, $this->arReserved) ) {
				$sClass .= " locked";
				$sEvents .=" onmouseover=\"this.style.backgroundColor = '#aaaaff'; Tip('<b>Заблокировано другим оператором</b>')\"";
			} else {
				if ( /*$this->canRecord(true)*/true ) {
					$sClass .= " free " . $this->TimetableType->getClass($this->Org);
					if ( $this->TimetableMedServiceOrg_begTime->format("Y-m-d H:i") < date("Y-m-d H:i") ) { //  прошедшее время
						If ( IsCZUser() || IsLpuRegAdmin($this->msData['Org_id']) ) {
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"if (window.confirm('Вы записываете на прошедшее время, продолжить?')) Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\"";
						} else {
							$sClass .= " locked";
							if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
								$sEvents .=" onclick=\"Ext.Msg.alert('Внимание','Выбранное время уже прошло. Пожалуйста, выберите другое время.');\"";
						}
					}else if($this->TimetableType_id==3){
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"if (window.confirm('Вы записываете на платную бирку, продолжить?')) Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\"";
					} else {
						if(!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] != 'spec_mz')
							$sEvents .=" onclick=\"Ext.getCmp('{$this->PanelID}').recordOrg({$this->TimetableMedServiceOrg_id}, '{$this->TimetableMedServiceOrg_begTime->format("d.m.Y")}', '{$this->TimetableMedServiceOrg_begTime->format("H:i")}');\" href='#' ";
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