<?php
/**
* Контроллер - Электронный паспорт здоровья
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff 
* @version      10.09.2009
*/
class EPH extends swController
{
	public $inputRules = array(
		'getPersonEPHData' => array(
			array(
				'default' => 'common',
				'field' => 'ARMType',
				'label' => 'Тип рабочего места врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'EvnDate_Range',
				'label' => 'Период случаев',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'default' => '',
				'field' => 'level',
				'label' => 'Уровень события',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => '',
				'field' => 'node',
				'label' => 'Родительская нода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'type',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => '',
				'field' => 'object',
				'label' => 'Тип объекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'object_id',
				'label' => 'Идентификатор объекта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'user_MedStaffFact_id',
				'label' => 'Идентификатор диагноза',
				'rules' => 'trim',
				'type' => 'id'
			)
		)
	);
	
	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение детей ноды
	 */
	function GetPersonEPHChild($childrens, $field, $lvl, $dop="")
	{
		$val = array();
		$i = 0;
		if ( $childrens != false && count($childrens) > 0 )
		{
			foreach ($childrens as $rows)
			{
				if (isset($rows['ChildrensCount'])) {
					$field['leaf'] = ($rows['ChildrensCount'] == 0) ? true : false;
				}
				$obj = array(
					'text' => toUTF(trim($rows[$field['name']])),
					'id' => $field['object'].'_'.$lvl.'_'.$rows['Date'].'_'.$dop.'_'.$rows[$field['id']],
					'date' => $rows['Date'],
					'object' => (!isset($rows['object']))?$field['object']:$rows['object'],
					'object_id' => $field['id'],
					'object_value' => $rows[$field['id']],
					'leaf' => $field['leaf'],
					'iconCls' => (!isset($rows['iconCls']))?$field['iconCls']:$rows['iconCls'],
					'cls' => $field['cls']
					);
				/*
				if (isset($rows['LpuRegionType_id']))
					$lrt = array('LpuRegionType_id'=> $rows['LpuRegionType_id']);
				else 
					$lrt = array();
				if (isset($rows['LpuSectionType_id']))
					$lst = array('LpuSectionType_id'=> $rows['LpuSectionType_id']);
				else 
					$lst = array();
				*/
				if ($field['object']=='EvnVizitPL')
				{
					$obj['Diag_id'] = $rows['Diag_id'];
				}
				
				// Если Person_id 
				if (isset($field['person_id']))
				{
					$obj['object_id'] = 'person_id';
					$obj['object'] = $rows['object'];
					$obj['object_value'] = $field['person_id'];
				}
				$val[] = array_merge($obj);
			}
			
		}
		return $val;
	}

	/**
	 * Формирование отображаемого текста ноды 
	 */
	function FormNameNode($lvl, $getdata, $ARMType = 'common', $type = 0)
	{
		$arr = array();
		if ( $getdata != false && count($getdata) > 0 )
		{
			Switch ($lvl)
			{
				case 'GroupByType':
					$i = 0;
					if ($ARMType == 'common') {
						$grouptype = array(
							array('id'=>11123, 'object' =>'EvnPL', 'text' => 'Случаи амбулаторно-поликлинического лечения'), 
							array('id'=>11121, 'object' =>'EvnPLDispDop', 'text' => 'Случаи дополнительной диспансеризации'), 
							array('id'=>11120, 'object' =>'EvnPLDispOrp', 'text' => 'Случаи диспансеризации детей-сирот'), 
							array('id'=>11119, 'object' =>'EvnVizitPL', 'text' => 'Посещения'), 
							array('id'=>11117, 'object' =>'EvnRecept', 'text' => 'Рецепты'), 
							array('id'=>11116, 'object' =>'EvnDirection', 'text' => 'Электронные направления'), 
							array('id'=>11115, 'object' =>'EvnUsluga', 'text' => 'Услуги'), 
							array('id'=>11114, 'object' =>'PersonDisp', 'text' => 'Диспансеризация'), 
							array('id'=>11113, 'object' =>'PersonPrivilege', 'text' => 'Льготы'), 
							array('id'=>11112, 'object' =>'PersonCard', 'text' => 'Прикрепление'), 
							array('id'=>11111, 'object' =>'EvnUslugaPar', 'text' => 'Параклинические услуги'),
							array('id'=>11110, 'object' =>'EvnStick', 'text' => 'Нетрудоспособности')
						);
					}
					if ($ARMType == 'stom') {
						$grouptype = array(
							array('id'=>11123, 'object' =>'EvnPL', 'text' => 'Случаи амбулаторно-поликлинического лечения'), 
							array('id'=>11122, 'object' =>'EvnPLStom', 'text' => 'Случаи стоматологического лечения'), 
							array('id'=>11121, 'object' =>'EvnPLDispDop', 'text' => 'Случаи дополнительной диспансеризации'), 
							array('id'=>11120, 'object' =>'EvnPLDispOrp', 'text' => 'Случаи диспансеризации детей-сирот'), 
							array('id'=>11119, 'object' =>'EvnVizitPL', 'text' => 'Посещения'), 
							array('id'=>11118, 'object' =>'EvnRecept', 'text' => 'Рецепты'), 
							array('id'=>11117, 'object' =>'EvnDirection', 'text' => 'Электронные направления'), 
							array('id'=>11116, 'object' =>'EvnUsluga', 'text' => 'Услуги'), 
							array('id'=>11115, 'object' =>'EvnUslugaStom', 'text' => 'Услуги по стоматологии'), 
							array('id'=>11114, 'object' =>'PersonDisp', 'text' => 'Диспансеризация'), 
							array('id'=>11113, 'object' =>'PersonPrivilege', 'text' => 'Льготы'), 
							array('id'=>11112, 'object' =>'PersonCard', 'text' => 'Прикрепление'), 
							array('id'=>11111, 'object' =>'EvnUslugaPar', 'text' => 'Параклинические услуги'),
							array('id'=>11110, 'object' =>'EvnStick', 'text' => 'Нетрудоспособности')
						);
					}
					if ($ARMType == 'par') {
						$grouptype = array(
							array('id'=>11111, 'object' =>'EvnUslugaPar', 'text' => 'Параклинические услуги')
							);
					}
					$grouptype[] = array('id'=>11109, 'object' =>'PersonDocuments', 'text' => 'Документы');
					foreach ($grouptype as $row)
					{
						// Группа событий
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$grouptype[$i]['Name'] = ''.$row['text'].' ';
						// Дата для сортировки
						$grouptype[$i]['Date'] = $grouptype[$i]['id'];
						$i++;
					}
					return $grouptype;
					break;					
				case 'EvnPL':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// Даты случая 
						if (!empty($row['EvnPL_setDT']))
							$name .= $row['EvnPL_setDT'].'-'.$row['EvnPL_disDT'].' ';
						else 
							$name .= '/ <span style="color: red;"><i>нет дат</i></span> ';
						// Событие
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= '/ '.$row['EvnClass_Name'].' ';
						// Диагноз
						if (!empty($row['Diag_Code']))
							$name = $name.'/ <span style="color: darkblue;">'.$row['Diag_Code'].'.'.$row['Diag_Name'].'</span> ';
						else 
							$name = $name.'/ <span style="color: red;"><i>нет диагноза</i></span> ';
						// Количество посещений
						$name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnVizit_Count'].'</span> ';
						// Результат
						if ($row['EvnPL_IsFinish']==2)
						{
							$name = $name.'результат: '.$row['ResultClass_Name'].' ';
						}
						// ЛПУ
						$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnPL_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnPLStom':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// Даты случая 
						if (!empty($row['EvnPLStom_setDT']))
							$name .= $row['EvnPLStom_setDT'].'-'.$row['EvnPLStom_disDT'].' ';
						else 
							$name .= '/ <span style="color: red;"><i>нет дат</i></span> ';
						// Событие
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= '/ '.$row['EvnClass_Name'].' ';
						// Количество посещений
						$name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnPLStom_VizitCount'].'</span> ';
						// Результат
						if ($row['EvnPLStom_IsFinish']==2)
						{
							$name = $name.'результат: '.$row['ResultClass_Name'].' ';
						}
						// ЛПУ
						$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnPLStom_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnPLDispDop':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// Событие 
						// Даты случая 
						if (!empty($row['EvnPLDispDop_setDT']))
							$name .= $row['EvnPLDispDop_setDT'].'-'.$row['EvnPLDispDop_disDT'].' / ';
						else 
							$name .= '<span style="color: red;"><i>нет дат</i></span> / ';
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// Количество посещений
						$name = $name.'/ число посещений: <span style="color: darkblue;">'.$row['EvnVizitDispDop_Count'].'</span> ';
						// ЛПУ
						$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnPLDispDop_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnVizitPL':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название и дата
						//$name = '<b>'.$row['EvnClass_Name'].'</b> '.$row['EvnVizitPL_setDT'].' ';
						$name = $row['EvnVizitPL_setDT'].' '.$row['EvnClass_Name'].' ';
						// Диагноз
						if (!empty($row['Diag_Code']))
							$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].'.'.$row['Diag_Name'].'</span> ';
						else 
							$name = $name.'/ <span style="color: red;"><i>нет диагноза</i></span> ';
						
						// Подразделение - Отделение - МедПерсонал
						$name = $name. '/ <span style="color: darkblue;"> '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
						// Место обслуживания
						$name = $name. '/ <span style="color: darkblue;"> '.$row['ServiceType_Name'].'</span> ';
						// Цель посещения 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['VizitType_Name'].'</span> ';
						// Вид оплаты 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['PayType_Name'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnVizitPL_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnVizitPLStom':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название и дата
						//$name = '<b>'.$row['EvnClass_Name'].'</b> '.$row['EvnVizitPL_setDT'].' ';
						$name = $row['EvnVizitPLStom_setDT'].' '.$row['EvnClass_Name'].' ';
						
						// Подразделение - Отделение - МедПерсонал
						$name = $name. '/ <span style="color: darkblue;"> '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
						// Место обслуживания
						$name = $name. '/ <span style="color: darkblue;"> '.$row['ServiceType_Name'].'</span> ';
						// Цель посещения 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['VizitType_Name'].'</span> ';
						// Вид оплаты 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['PayType_Name'].'</span> ';
						// Кол-во УЕТ
						$name = $name. '/ <span style="color: darkblue;">УЕТ: '.$row['EvnVizitPLStom_Uet'].'</span> ';
						
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnVizitPLStom_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnDiagPLStom':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// Дата установки
						$name .= '<span style="color: darkblue;"> Дата установки: '.$row['EvnDiagPLStom_setDT'].'</span> / ';
						// Название
						$name .= $row['EvnClass_Name'].' ';
						
						// Диагноз
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
						// Характер заболевания
						$name = $name. '/ <span style="color: darkblue;"> '.$row['DeseaseType_Name'].'</span> ';
						
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnDiagPLStom_setDT'];
						$i++;
					}
					return $getdata;
				break;
				case 'EvnVizitDispDop':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// Название и дата
						$name = $name. '<span style="color: darkblue;"> '.$row['EvnVizitDispDop_setDT'].'</span> / ';
						//$name = '<b>'.$row['EvnClass_Name'].'</b> / '.$row['EvnVizitDispDop_setDT'].' ';
						$name .= $row['EvnClass_Name'].' ';
						
						// Диагноз
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].'.'.$row['Diag_Name'].'</span> ';
						// Подразделение - Отделение 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].'</span> ';
						// Специальность врача - Врач 
						$name = $name. ' / '.$row['DopDispSpec_Name'].' '.$row['MedPersonal_FIO'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnVizitDispDop_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnRecept':
					//[Значок льготности] 04.02.2010 – Таурин-Акос капли глазные 5 мл фл. N 1
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						//$name = $row['EvnClass_Name'].' ';
						// Дата выписки рецепта
						$name = $name. '<span style="color: darkblue;">'.$row['EvnRecept_setDT'].'</span> ';
						// текст «Рецепт» только при линейной группировке
						if (0 == $type)
							$name .= '/ Рецепт ';
						// Серия и номер рецепта
						//$name = $name. '/ <span style="color: darkblue;"> '.$row['EvnRecept_Ser'].' '.$row['EvnRecept_Num'].'</span> ';
						// Медикамент 
						$name = $name. '- <span style="color: darkblue;"> '.$row['Drug_Name'].'</span> ';
						// Кол-во 
						$name = $name. 'N <span style="color: darkblue;"> '.$row['EvnRecept_Kolvo'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnRecept_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnUsluga':
					//[Значок оплаты: ОМС, ДМС и т.д.] 17.12.2009 / 01232233. Диафаноскопия (1) – Доп. диспансеризация 
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата оказания услуги
						$name = $name. '<span style="color: darkblue;">'.$row['EvnUsluga_setDT'].'</span> ';
						// текст «Услуга» только при линейной группировке
						if (0 == $type)
							$name .= '/ Услуга ';
						// код и наименование услуги 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'. '.$row['Usluga_Name'].'</span> ';
						// Кол-во 
						$name = $name. '<span style="color: darkblue;">('.$row['EvnUsluga_Kolvo'].')</span> - ';
						// Название события - тип услуги 
						$name .= $row['EvnClass_Name'];
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Значок iconCls
						$getdata[$i]['iconCls'] = 'pay-'. $row['PayType_SysNick'] .'16';
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnUsluga_setDT'];
						//Для параклинических услуг будет свой тип
						if ($row['EvnClass_Name'] == 'Параклиническая услуга') {
							$getdata[$i]['object'] = 'EvnUslugaPar';
						}
						if ($row['EvnClass_Name'] == 'Стоматологическая услуга') {
							$getdata[$i]['object'] = 'EvnUslugaStom';
						}
						$i++;
					}
					return $getdata;
					break;
				case 'EvnUslugaStom':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата оказания услуги
						$name .= '<span style="color: darkblue;">'.$row['EvnUslugaStom_setDT'].'</span> / ';
						// Название события - тип услуги 
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// код и наименование услуги 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'.'.$row['Usluga_Name'].'</span> ';
						// Кол-во 
						$name = $name. '/ количество: <span style="color: darkblue;"> '.$row['EvnUslugaStom_Kolvo'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnUslugaStom_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnUslugaPar':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата оказания услуги
						$name .= '<span style="color: darkblue;">'.$row['EvnUslugaPar_setDT'].'</span> / ';
						// Название события - тип услуги 
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// код и наименование услуги 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'.'.$row['Usluga_Name'].'</span> ';
						// Кол-во 
						$name = $name. '/ количество: <span style="color: darkblue;"> '.$row['EvnUslugaPar_Kolvo'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnUslugaPar_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnUslugaDispDop':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата оказания услуги
						$name .= '<span style="color: darkblue;">'.$row['EvnUslugaDispDop_setDT'].'</span> / ';
						// Название события - тип услуги 
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// код и наименование услуги 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Usluga_Code'].'.'.$row['Usluga_Name'].'</span> ';
						// Кол-во 
						$name = $name. '/ количество: <span style="color: darkblue;"> '.$row['EvnUslugaDispDop_Kolvo'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnUslugaDispDop_setDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnStick':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата открытия - дата закрытия 
						$name .= '<span style="color: darkblue;">'.$row['EvnStick_begDT'].' - '.$row['EvnStick_endDT'].'</span> / ';
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// Порядок выдачи ЛВН
						$name = $name. '/ <span style="color: darkblue;"> '.$row['StickOrder_Name'].'</span> ';
						// Серия - номер
						$name = $name. '/ <span style="color: darkblue;"> '.$row['EvnStick_Ser'].' '.$row['EvnStick_Num'].'</span> ';
						// Причина выдачи
						$name = $name. '/ <span style="color: red;"> '.$row['StickCause_Name'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnStick_begDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'PersonDocumentsGroup':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name = $row['PersonDocumentsGroup_Name'];							
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = '01.01.1970';
						$i++;
					}
					return $getdata;
					break;
				case 'PersonDocuments':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name = $row['PersonDocuments_Name'];							
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = '01.01.1970';
						$i++;
					}
					return $getdata;
					break;
				case 'DeathSvid':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name = $row['EvnClass_Name'];
						$name .= ' '.$row['DeathSvid_Ser'].' '.$row['DeathSvid_Num'];
						$name .= ' '.$row['DeathSvid_DeathDate'];
						$name .= ' '.$row['DeathCause_Name'];
						$name .= ' '.$row['MedPersonal_FIO'];
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = '01.01.1970';
						$i++;
					}
					return $getdata;
					break;
				case 'BirthSvid':
					$i = 0;
					foreach ($getdata as $row)
					{
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name = $row['EvnClass_Name'];
						$name .= ' '.$row['BirthSvid_Ser'].' '.$row['BirthSvid_Num'];
						$name .= ' '.$row['BirthSvid_GiveDate'];
						$name .= ' '.$row['BirthPlace_Name'];
						$name .= ' '.$row['Lpu_Nick'];
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = '01.01.1970';
						$i++;
					}
					return $getdata;
					break;
				case 'EvnStickDop':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата открытия - дата закрытия 
						$name = $name. '<span style="color: darkblue;">'.$row['EvnStickDop_begDT'].' - '.$row['EvnStickDop_endDT'].'</span> / ';
						// Название события
						//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
						$name .= $row['EvnClass_Name'].' ';
						// Порядок выдачи ЛВН
						$name = $name. '/ <span style="color: darkblue;"> '.$row['StickOrder_Name'].'</span> ';
						// Серия - номер
						$name = $name. '/ <span style="color: darkblue;"> '.$row['EvnStickDop_Ser'].' '.$row['EvnStickDop_Num'].'</span> ';
						// Причина выдачи
						$name = $name. '/ <span style="color: red;"> '.$row['StickCause_Name'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnStickDop_begDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'BegPersonPrivilege':
					//[Значок льготы: фед., рег., мест.] 05.02.2009 / Инвалиды 2-й группы [?Льгота действительна ? недействительна]
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата открытия 
						$name = $name. '<span style="color: darkblue;">'.$row['PersonPrivilege_begDT'].'</span> ';
						// Убрать текст «Льгота: открытие/закрытие» и др., сохранить его только при линейной группировке
						if (0 == $type)
							$name .= '/ Льгота: открытие ';
						// Категория льготы
						$name = $name. '/ <span style="color: darkblue;">'.$row['PrivilegeType_Name'].'</span> ';
						// Льгота действительна ok16/недействительна delete16
						$cur_date = new DateTime(date('d.m.Y'));
						$beg_date = new DateTime($row['PersonPrivilege_begDT']);
						$end_date = (!empty($row['PersonPrivilege_endDT']))?new DateTime($row['PersonPrivilege_endDT']):NULL;
						$real_privelege = ( // Льгота действительна, если 
							$beg_date <= $cur_date
							AND
							( empty($end_date) OR $end_date > $cur_date)
							AND 
							( empty($row['PersonRefuse_IsRefuse']) OR $row['PersonRefuse_IsRefuse'] != 2)
						);
						$ico = '<img src="/img/icons/delete16.png" title="Льгота недействительна" />';
						if ($real_privelege) $ico = '<img src="/img/icons/ok16.png" title="Льгота действительна" />';
						$name .= '<sub>'.$ico.'</sub>';// [beg_date: '.$row['PersonPrivilege_begDT'].' end_date:'.$row['PersonPrivilege_endDT'].' IsRefuse:'.$row['PersonRefuse_IsRefuse'].']';
						// ЛПУ
						//$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
						// Значок iconCls
						$getdata[$i]['iconCls'] = $row['PrivilegeType_Level'];
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonPrivilege_begDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EndPersonPrivilege':
					//[Значок льготы: фед., рег., мест.] 05.02.2009 / Инвалиды 2-й группы [?Льгота действительна ? недействительна]
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата закрытия 
						$name = $name. '<span style="color: darkblue;">'.$row['PersonPrivilege_endDT'].'</span> ';
						// Убрать текст «Льгота: открытие/закрытие» и др., сохранить его только при линейной группировке
						if (0 == $type)
							$name .= '/ Льгота: закрытие ';
						// Категория льготы
						$name = $name. '/ <span style="color: darkblue;">'.$row['PrivilegeType_Name'].'</span> ';
						// Льгота действительна ok16/недействительна delete16
						$cur_date = new DateTime(date('d.m.Y'));
						$beg_date = new DateTime($row['PersonPrivilege_begDT']);
						$end_date = (!empty($row['PersonPrivilege_endDT']))?new DateTime($row['PersonPrivilege_endDT']):NULL;
						$real_privelege = ( // Льгота действительна, если 
							$beg_date <= $cur_date
							AND
							( empty($end_date) OR $end_date > $cur_date)
							AND 
							( empty($row['PersonRefuse_IsRefuse']) OR $row['PersonRefuse_IsRefuse'] != 2)
						);
						$ico = '<img src="/img/icons/delete16.png" title="Льгота недействительна" />';
						if ($real_privelege) $ico = '<img src="/img/icons/ok16.png" title="Льгота действительна" />';
						$name .= '<sub>'.$ico.'</sub>';
						// ЛПУ
						//$name = $name.'/ ЛПУ: '.$row['Lpu_Nick'].' ';
						// Значок iconCls
						$getdata[$i]['iconCls'] = $row['PrivilegeType_Level'];
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonPrivilege_endDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'BegPersonDisp':
					//[Значок дисп.] 05.02.2009 / Взятие на учет / C90.0 Множественная миелома / Пермь ГП2 – кабинет такой-то
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата постановки 
						$name = $name. '<span style="color: darkblue;">'.$row['PersonDisp_begDT'].'</span> / ';
						// текст «Диспансеризация» только при линейной группировке
						if (0 == $type)
							$name .= 'Диспансеризация: ';
						// Название события
						$name .= 'Взятие на учет ';
						// Диагноз
						$name = $name. '/ <span style="color: darkblue;">'.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
						// ЛПУ - кабинет
						$name = $name. '/ <span style="color: darkblue;">'.$row['Lpu_Nick'].'</span>';// - кабинет 
						// ЛПУ - Подразделение - Отделение - МедПерсонал
						//$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].' / '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
						// Дата следующей явки
						//$name = $name. '/ <span style="color: darkblue;">'.$row['PersonDisp_NextDate'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonDisp_begDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EndPersonDisp':
					//[Значок дисп.] 05.02.2009 / Снятие с учета / C90.0 Множественная миелома / Пермь ГП2 – кабинет такой-то
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата снятия 
						$name = $name. '<span style="color: darkblue;">'.$row['PersonDisp_endDT'].'</span> / ';
						// текст «Диспансеризация» только при линейной группировке
						if (0 == $type)
							$name .= 'Диспансеризация: ';
						// Название события
						$name .= 'Снятие с учета ';
						// Диагноз
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Diag_Code'].' '.$row['Diag_Name'].'</span> ';
						// ЛПУ - кабинет 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].'</span> ';// - кабинет
						// ЛПУ - Подразделение - Отделение - МедПерсонал
						//$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].' / '.$row['LpuUnit_Name'].' / '.$row['LpuSection_Name'].' / '.$row['MedPersonal_FIO'].'</span> ';
						// Причина снятия
						//$name = $name. '/ <span style="color: darkblue;">'.$row['DispOutType_Name'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonDisp_endDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'BegPersonCard':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата прикрепления
						$name = $name. '<span style="color: darkblue;">'.$row['PersonCard_begDT'].'</span> / ';
						// Название события
						$name .= 'Прикрепление: ';
						// ЛПУ прикрепления
						$name = $name. '<span style="color: darkblue;">'.$row['Lpu_Nick'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonCard_begDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EndPersonCard':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата прикрепления
						$name = $name. '<span style="color: darkblue;">'.$row['PersonCard_endDT'].'</span> / ';
						// Название события
						$name .= 'Открепление: ';
						// ЛПУ прикрепления
						$name = $name. '<span style="color: darkblue;">'.$row['Lpu_Nick'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['PersonCard_endDT'];
						$i++;
					}
					return $getdata;
					break;
				case 'EvnDirection':
					$i = 0;
					foreach ($getdata as $row)
					{
						$name = '';
						// дата направления
						$name .= '<span style="color: darkblue;">'.$row['EvnDirection_setDT'].'</span> / ';
						// ЭН (номер, тип направления, ЛПУ, подразделение, профиль, дата/время записи)
						// Название события
						$name .= 'Электронное направление: ';
						// номер направления
						$name = $name. '/ <span style="color: darkblue;">'.$row['EvnDirection_Num'].'</span> ';
						// Тип направления 
						$name = $name. '/ <span style="color: darkblue;"> '.$row['DirType_Name'].'</span> ';
						// ЛПУ - Подразделение - Профиль
						$name = $name. '/ <span style="color: darkblue;"> '.$row['Lpu_Nick'].' / '.$row['LpuUnit_Name'].' / '.$row['LpuSectionProfile_Name'].'</span> ';
						// Дата и время записи
						$name = $name. '/ <span style="color: darkblue;">'.$row['RecDate'].'</span> ';
						// Наименование 
						$getdata[$i]['Name'] = $name;
						// Дата для сортировки
						$getdata[$i]['Date'] = $row['EvnDirection_setDT'];
						$i++;
					}
					return $getdata;
					break;

			}
		}
	}

	/**
	 * Сравнение двух дат
	 */
	function cmp($a, $b)
	{
		
		//$d1 = getdate(date($a['date']));
		if (!empty($a['date']))
		{
			$d1 = ConvertDateFormat($a['date']);
			if (empty($d1)) 
				$d1 = $a['date'];
		}
		else 
			{
			$d1 = ConvertDateFormat('01.01.1990');
			}
		if (!empty($b['date']))
		{
			$d2 = ConvertDateFormat($b['date']);
			if (empty($d2)) 
				$d2 = $b['date'];
		}
		else 
			$d2 = ConvertDateFormat('01.01.1990');
		
		//$td1 = mktime(0, 0, 0, $d1, 1, $Y);
		//$d2 = date($b['date']);
		
		if ($d1 == $d2) 
		{
				return 0;
		}
		return ($d1 > $d2) ? -1 : 1;
		//$var_date = mktime(0, 0, 0, $m, 1, $Y);
		//print $d1;
		//return strcmp($a['date'], $b['date']);
	}
		
	/**
	* Получение данных для дерева истории лечения (ЭПЗ)
	* 
	* 
	*/
	function getPersonEPHData()
	{
		$this->load->database();
		$this->load->model('EPH_model', 'EPHmodel');
		$this->load->helper('Text');
		$this->load->helper('Date');
		
		$data = $this->ProcessInputData('getPersonEPHData', false);
		if ($data === false) { return false; }
		
		
		$val = array();
		$val_new = array();
		$arr_1 = array();
		$arr_2 = array();
		$arr_3 = array();
		
		$EvnList = array();
		switch ($data['ARMType']) {
			case 'stom':
				//$EvnList = array_merge($EvnList, array('EvnPLStom'));
			case 'common':
				$EvnList = array_merge($EvnList, array('EvnPL', 'EvnPLStom', 'EvnVizitPL', 'EvnVizitPLStom', 'EvnPLDispDop', 'EvnPLDispDop', 'EvnRecept', 'EvnDirection', 'EvnUsluga', 'EvnUslugaStom', 'PersonDisp', 'PersonPrivilege', 'PersonCard', 'BegPersonPrivilege', 'EndPersonPrivilege', 'BegPersonCard', 'EndPersonCard', 'BegPersonDisp', 'EndPersonDisp', 'EvnUslugaPar', 'EvnStick', 'PersonDocuments'));
			break;
			case 'par':
				$EvnList = array_merge($EvnList, array('EvnUslugaPar'));
			break;
		}
		if ($data['node']=='root') 
		{
			$data['level'] = 0;
		}
		if (!isset($data['type']))
			$data['type'] = 0;
		
		$data['node'] = str_replace(Array('EvnPL', 'EvnVizit', 'LpuUnit', 'Lpu', 'Building'),'',$data['node']);
		
		if (($data['type']==0) || ($data['level']!=1))
			$data[$data['object'].'_id'] = $data['object_id'];
		else 
			$data['Person_id'] = $data['object_id'];
		if (($data['level']==0) && ($data['object_id']==0))
		{
			// Первый запрос при инициализации - возвращаем пустоту
			$this->ReturnData($val);
		}
		else 
		{
		
			// Первый вариант вывода - Группировка по типам
			if ($data['type'] == 1) 
			{
				Switch ($data['level'])
				{
					case 0:
					{
						//Группировка по типам
						$field = Array(
							'object' => "GroupByType",
							'person_id'=>$data['object_id'],
							'id' => "id", 
							'name' => "Name", 
							'iconCls' => 
							'evn-16', 
							'leaf' => false, 
							'cls' => "folder"
							);
						$childrens = $this->FormNameNode('GroupByType', $field, $data['ARMType'],$data['type']);
						$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
						break;
					}
					case 1: 
					{
						if ($data['object'] == 'PersonDocuments')
						{
							// PersonDocuments - Документы человека
							$childrens = array(
								array("PersonDocumentsGroup_Name" => "Медсвидетельстава", "PersonDocumentsGroup_id" => 1),
								array("PersonDocumentsGroup_Name" => "Справки", "PersonDocumentsGroup_id" => 2)
							);
							$childrens = $this->FormNameNode('PersonDocumentsGroup', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDocumentsGroup",'id' => "PersonDocumentsGroup_id", 'name' => "PersonDocumentsGroup_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnPL' && in_array('EvnPL', $EvnList))
						{
							// EvnPL - Случаи амбулаторно-поликлинического лечения 
							$childrens = $this->EPHmodel->GetEvnPLNodeList($data);
							$childrens = $this->FormNameNode('EvnPL', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnPLStom' && in_array('EvnPLStom', $EvnList))
						{
							// EvnPLStom - Случаи стоматологического лечения 
							$childrens = $this->EPHmodel->GetEvnPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPLStom",'id' => "EvnPLStom_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnPLDispDop' && in_array('EvnPLDispDop', $EvnList))
						{
							// EvnPLDispDop - Случай дополнительной диспансеризации
							$childrens = $this->EPHmodel->GetEvnPLDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnPLDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPLDispDop",'id' => "EvnPLDispDop_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnVizitPLStom' && in_array('EvnVizitPLStom', $EvnList))
						{
							// EvnVizitPLStom - 
							$childrens = $this->EPHmodel->GetEvnVizitPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitPLStom",'id' => "EvnVizitPLStom_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if ($data['object'] == 'EvnVizitPL' && in_array('EvnVizitPL', $EvnList))
						{
							// EvnVizit - Посещения (все)
							// EvnVizitPL
							$childrens = $this->EPHmodel->GetEvnVizitPLNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitPL', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitPL", 'id' => "EvnVizitPL_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnVizitDispDop
							$childrens = $this->EPHmodel->GetEvnVizitDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitDispDop", 'id' => "EvnVizitDispDop_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							
							$childrens = $this->EPHmodel->GetEvnVizitPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitPLStom", 'id' => "EvnVizitPLStom_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnRecept' && in_array('EvnRecept', $EvnList))
						{
							// EvnRecept
							$childrens = $this->EPHmodel->GetEvnReceptNodeList($data);
							$childrens = $this->FormNameNode('EvnRecept', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => "Name", 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnDirection' && in_array('EvnDirection', $EvnList))
						{
							// EvnDirection - ЕН
							$childrens = $this->EPHmodel->GetEvnDirectionNodeList($data);
							$childrens = $this->FormNameNode('EvnDirection', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnDirection", 'id' => "EvnDirection_id", 'name' => "Name", 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnUsluga' && in_array('EvnUsluga', $EvnList))
						{
							// EvnUsluga
							$childrens = $this->EPHmodel->GetEvnUslugaNodeList($data);
							$childrens = $this->FormNameNode('EvnUsluga', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnUslugaDispDop
							$childrens = $this->EPHmodel->GetEvnUslugaDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'PersonDisp' && in_array('PersonDisp', $EvnList))
						{
							// PersonDisp - Диспансеризация: постановка
							$childrens = $this->EPHmodel->GetBegPersonDispNodeList($data);
							$childrens = $this->FormNameNode('BegPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							// PersonDisp - Диспансеризация: снятие
							$childrens = $this->EPHmodel->GetEndPersonDispNodeList($data);
							$childrens = $this->FormNameNode('EndPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'PersonPrivilege' && in_array('PersonPrivilege', $EvnList))
						{
							// PersonPrivilege - – Льгота: открытие
							$childrens = $this->EPHmodel->GetBegPersonPrivilegeNodeList($data);
							$childrens = $this->FormNameNode('BegPersonPrivilege', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							// PersonPrivilege – Льгота: закрытие 
							$childrens = $this->EPHmodel->GetEndPersonPrivilegeNodeList($data);
							$childrens = $this->FormNameNode('EndPersonPrivilege', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'PersonCard' && in_array('PersonCard', $EvnList))
						{
							// PersonCard - Прикрепление - (Прикрепление, дата прикрепления, ЛПУ) 
							$childrens = $this->EPHmodel->GetPersonCardBegNodeList($data);
							$childrens = $this->FormNameNode('BegPersonCard', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							// PersonCard - Открепление - (Открепление, дата открепления, ЛПУ) 
							$childrens = $this->EPHmodel->GetPersonCardEndNodeList($data);
							$childrens = $this->FormNameNode('EndPersonCard', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnUslugaPar' && in_array('EvnUslugaPar', $EvnList))
						{
							// EvnUsluga - Параклиническая услуга на человека 
							$childrens = $this->EPHmodel->GetEvnUslugaParNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaPar', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaPar", 'id' => "EvnUslugaPar_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnUslugaStom' && in_array('EvnUslugaStom', $EvnList))
						{
							// EvnUsluga - Стоматологическая услуга на человека 
							$childrens = $this->EPHmodel->GetEvnUslugaStomNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnStick' && in_array('EvnStick', $EvnList))
						{
							// EvnStick
							$childrens = $this->EPHmodel->GetEvnStickNodeList($data);
							$childrens = $this->FormNameNode('EvnStick', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1, $arr_2);
							// EvnStickDop
							$childrens = $this->EPHmodel->GetEvnStickDopNodeList($data);
							$childrens = $this->FormNameNode('EvnStickDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1, $arr_2);
						}
							
						break;
					}
					case 2:
						$data['type'] = 0;
						$data['level'] = 1;
						if (($data['object']=='EvnPL') || ($data['object']=='EvnPLDispDop') || ($data['object']=='PersonDocumentsGroup'))
						{
							$data['level'] = 1;
						}
						else
						{
							$data['level'] = 2;
						}
						break;
					case 3:
						/*
						if ($data['object'] in ('EvnPL', 'EvnPLDispDop'))
						{
							$data['level'] = 1;
						}
						else
						{
							$data['level'] = 2;
						}
						*/
						$data['type'] = 0;
						$data['level'] = 2;
						break;
				}
				
			} // Второй вариант вывода - Группировка по дате
			
			if ($data['type'] == 0) 
			{
				Switch ($data['level'])
				{
					case 0:
						if (in_array('EvnPL', $EvnList)) {
							// EvnPL - Случаи амбулаторно-поликлинического лечения 
							$childrens = $this->EPHmodel->GetEvnPLNodeList($data);
							$childrens = $this->FormNameNode('EvnPL', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if (in_array('EvnPLStom', $EvnList))
						{
							// EvnPLStom - Случаи стоматологического лечения 
							$childrens = $this->EPHmodel->GetEvnPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPLStom",'id' => "EvnPLStom_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if (in_array('EvnPLDispDop', $EvnList)) {
							// EvnPLDispDop - Случай дополнительной диспансеризации
							$childrens = $this->EPHmodel->GetEvnPLDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnPLDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnPLDispDop",'id' => "EvnPLDispDop_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if (in_array('EvnDirection', $EvnList)) {
							// Выписанные электронные направления 
							$childrens = $this->EPHmodel->GetEvnDirectionNodeList($data);
							$childrens = $this->FormNameNode('EvnDirection', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnDirection", 'id' => "EvnDirection_id", 'name' => "Name", 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if (in_array('EvnRecept', $EvnList)) {
							// EvnRecept
							$childrens = $this->EPHmodel->GetEvnReceptWithNoVizitNodeList($data);
							$childrens = $this->FormNameNode('EvnRecept', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => "Name", 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if (in_array('BegPersonPrivilege', $EvnList)) {
							// PersonPrivilege - – Льгота: открытие
							$childrens = $this->EPHmodel->GetBegPersonPrivilegeNodeList($data);
							$childrens = $this->FormNameNode('BegPersonPrivilege', $childrens, $data['ARMType'],$data['type']);
							// To-Do [Значок льготы: фед., рег., мест.]
							$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => "Name", 'iconCls' => 'priv-new16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
							
						if (in_array('EndPersonPrivilege', $EvnList)) {
							// PersonPrivilege – Льгота: закрытие 
							$childrens = $this->EPHmodel->GetEndPersonPrivilegeNodeList($data);
							$childrens = $this->FormNameNode('EndPersonPrivilege', $childrens, $data['ARMType'],$data['type']);
							// To-Do [Значок льготы: фед., рег., мест.]
							$field = Array('object' => "PersonPrivilege",'id' => "PersonPrivilege_id", 'name' => "Name", 'iconCls' => 'pers-priv16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
							
						if (in_array('BegPersonDisp', $EvnList)) {
							// PersonDisp - Диспансеризация: постановка
							$childrens = $this->EPHmodel->GetBegPersonDispNodeList($data);
							$childrens = $this->FormNameNode('BegPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
							
						if (in_array('EndPersonDisp', $EvnList)) {
							// PersonDisp - Диспансеризация: снятие
							$childrens = $this->EPHmodel->GetEndPersonDispNodeList($data);
							$childrens = $this->FormNameNode('EndPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if (in_array('BegPersonCard', $EvnList)) {
							// PersonCard - Прикрепление - (Прикрепление, дата прикрепления, ЛПУ) 
							$childrens = $this->EPHmodel->GetPersonCardBegNodeList($data);
							$childrens = $this->FormNameNode('BegPersonCard', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						if (in_array('EndPersonCard', $EvnList)) {
							// PersonCard - Открепление - (Открепление, дата открепления, ЛПУ) 
							$childrens = $this->EPHmodel->GetPersonCardEndNodeList($data);
							$childrens = $this->FormNameNode('EndPersonCard', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonCard",'id' => "PersonCard_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
							
						if (in_array('EvnUslugaPar', $EvnList)) {
							// EvnUsluga - Параклиническая услуга на человека 
							$childrens = $this->EPHmodel->GetEvnUslugaParNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaPar', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaPar", 'id' => "EvnUslugaPar_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						// на верхнем уровне "Документы" выводим всегда 
						// PersonDocuments - Документы человека
						$childrens = array(
							array("PersonDocuments_Name" => "Документы", "PersonDocuments_id" => 11109)
						);
						$childrens = $this->FormNameNode('PersonDocuments', $childrens, $data['ARMType'],$data['type']);
						$field = Array('object' => "PersonDocuments",'id' => "PersonDocuments_id", 'name' => "PersonDocuments_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
						$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
						$arr_1 = array_merge($arr_1,$arr_2);						
						break;
					case 1:
						if ($data['object'] == 'PersonDocuments')
						{
							// PersonDocuments - Документы человека
							$childrens = array(
								array("PersonDocumentsGroup_Name" => "Медсвидетельстава", "PersonDocumentsGroup_id" => 1),
								array("PersonDocumentsGroup_Name" => "Справки", "PersonDocumentsGroup_id" => 2)
							);
							$childrens = $this->FormNameNode('PersonDocumentsGroup', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDocumentsGroup",'id' => "PersonDocumentsGroup_id", 'name' => "PersonDocumentsGroup_Name", 'iconCls' => 'folder', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						// Список медсвидетельств
						if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 1)
						{
							// свидетельства о рождении
							$childrens = $this->EPHmodel->GetBirthSvidNodeList($data);
							$childrens = $this->FormNameNode('BirthSvid', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "BirthSvid",'id' => "BirthSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							
							// свидетельства о смерти
							$childrens = $this->EPHmodel->GetDeathSvidNodeList($data);
							$childrens = $this->FormNameNode('DeathSvid', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "DeathSvid", 'id' => "DeathSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							
							// свидетельства о смерти детей
							/*$childrens = $this->EPHmodel->GetPntDeathSvidNodeList($data);
							$childrens = $this->FormNameNode('PntDeathSvid', $childrens);
							$field = Array('object' => "PntDeathSvid",'id' => "PntDeathSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);*/
						}
						// Список справок
						if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 2)
						{
							/*$childrens = $this->EPHmodel->GetMedSvidNodeList($data);
							$childrens = $this->FormNameNode('MedSvid', $childrens);
							$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);*/
						}
						if ($data['object'] == 'EvnPL' && in_array('EvnPL', $EvnList))
						{
							// EvnVizitPL
							$childrens = $this->EPHmodel->GetEvnVizitPLNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitPL', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitPL", 'id' => "EvnVizitPL_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnUsluga
							$childrens = $this->EPHmodel->GetEvnUslugaNodeList($data);
							$childrens = $this->FormNameNode('EvnUsluga', $childrens, $data['ARMType'],$data['type']);
							// To-Do [Значок оплаты: ОМС, ДМС и т.д.] 
							$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStick
							$childrens = $this->EPHmodel->GetEvnStickNodeList($data);
							$childrens = $this->FormNameNode('EvnStick', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickDop
							$childrens = $this->EPHmodel->GetEvnStickDopNodeList($data);
							$childrens = $this->FormNameNode('EvnStickDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnPLStom' && in_array('EvnPLStom', $EvnList))
						{
							// EvnVizitPLStom
							$childrens = $this->EPHmodel->GetEvnVizitPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitPLStom", 'id' => "EvnVizitPLStom_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnUsluga
							$childrens = $this->EPHmodel->GetEvnUslugaStomNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStick
							$childrens = $this->EPHmodel->GetEvnStickNodeList($data);
							$childrens = $this->FormNameNode('EvnStick', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStick", 'id' => "EvnStick_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnStickDop
							$childrens = $this->EPHmodel->GetEvnStickDopNodeList($data);
							$childrens = $this->FormNameNode('EvnStickDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnStickDop", 'id' => "EvnStickDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnPLDispDop' && in_array('EvnPLDispDop', $EvnList))
						{
							// EvnVizitDispDop
							$childrens = $this->EPHmodel->GetEvnVizitDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnVizitDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnVizitDispDop", 'id' => "EvnVizitDispDop_id", 'name' => "Name", 'iconCls' => 'evnvizit-16', 'leaf' => false, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnUslugaDispDop
							$childrens = $this->EPHmodel->GetEvnUslugaDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						break;
					case 2:
						// Список медсвидетельств
						if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 1)
						{
							// свидетельства о рождении
							$childrens = $this->EPHmodel->GetBirthSvidNodeList($data);
							$childrens = $this->FormNameNode('BirthSvid', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "BirthSvid",'id' => "BirthSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1, $arr_2);
							
							// свидетельства о смерти
							$childrens = $this->EPHmodel->GetDeathSvidNodeList($data);
							$childrens = $this->FormNameNode('DeathSvid', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "DeathSvid", 'id' => "DeathSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1, $arr_2);
							
							// свидетельства о смерти детей
							/*$childrens = $this->EPHmodel->GetPntDeathSvidNodeList($data);
							$childrens = $this->FormNameNode('PntDeathSvid', $childrens);
							$field = Array('object' => "PntDeathSvid",'id' => "PntDeathSvid_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);*/
						}
						// Список справок
						if ($data['object'] == 'PersonDocumentsGroup' && $data['object_id'] == 2)
						{
							/*$childrens = $this->EPHmodel->GetMedSvidNodeList($data);
							$childrens = $this->FormNameNode('MedSvid', $childrens);
							$field = Array('object' => "EvnPL",'id' => "EvnPL_id", 'name' => "Name", 'iconCls' => 'evnpl-16', 'leaf' => false, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);*/
						}
						if ($data['object'] == 'EvnVizitPL' && in_array('EvnVizitPL', $EvnList))
						{
							// EvnRecept
							$childrens = $this->EPHmodel->GetEvnReceptNodeList($data);
							$childrens = $this->FormNameNode('EvnRecept', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnRecept", 'id' => "EvnRecept_id", 'name' => "Name", 'iconCls' => 'lgot16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							// EvnUsluga
							$childrens = $this->EPHmodel->GetEvnUslugaNodeList($data);
							$childrens = $this->FormNameNode('EvnUsluga', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUsluga", 'id' => "EvnUsluga_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnDirection
							$childrens = $this->EPHmodel->GetEvnDirectionNodeList($data);
							$childrens = $this->FormNameNode('EvnDirection', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnDirection", 'id' => "EvnDirection_id", 'name' => "Name", 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							
							// PersonDisp - Диспансеризация: постановка
							$childrens = $this->EPHmodel->GetBegPersonDispNodeList($data);
							$childrens = $this->FormNameNode('BegPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							$arr_1 = array_merge($arr_1,$arr_2);
							// PersonDisp - Диспансеризация: снятие
							$childrens = $this->EPHmodel->GetEndPersonDispNodeList($data);
							$childrens = $this->FormNameNode('EndPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnVizitPLStom' && in_array('EvnVizitPLStom', $EvnList))
						{
							// EvnUslugaStom
							$childrens = $this->EPHmodel->GetEvnUslugaStomNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaStom", 'id' => "EvnUslugaStom_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnDiagPLStom
							$childrens = $this->EPHmodel->GetEvnDiagPLStomNodeList($data);
							$childrens = $this->FormNameNode('EvnDiagPLStom', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnDiagPLStom", 'id' => "EvnDiagPLStom_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						if ($data['object'] == 'EvnVizitDispDop' && in_array('EvnVizitDispDop', $EvnList))
						{
							// EvnDirection
							$childrens = $this->EPHmodel->GetEvnDirectionNodeList($data);
							$childrens = $this->FormNameNode('EvnDirection', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnDirection", 'id' => "EvnDirection_id", 'name' => "Name", 'iconCls' => 'direction-16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							$arr_1 = array_merge($arr_1,$arr_2);
							// EvnUslugaDispDop
							$childrens = $this->EPHmodel->GetEvnUslugaDispDopNodeList($data);
							$childrens = $this->FormNameNode('EvnUslugaDispDop', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "EvnUslugaDispDop", 'id' => "EvnUslugaDispDop_id", 'name' => "Name", 'iconCls' => 'evnusluga-16', 'leaf' => true, 'cls' => "folder");
							$arr_1 = $this->GetPersonEPHChild($childrens, $field, $data['level']);
							
							// PersonDisp - Диспансеризация: постановка
							$childrens = $this->EPHmodel->GetBegPersonDispNodeList($data);
							$childrens = $this->FormNameNode('BegPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "Beg");
							$arr_1 = array_merge($arr_1,$arr_2);
							// PersonDisp - Диспансеризация: снятие
							$childrens = $this->EPHmodel->GetEndPersonDispNodeList($data);
							$childrens = $this->FormNameNode('EndPersonDisp', $childrens, $data['ARMType'],$data['type']);
							$field = Array('object' => "PersonDisp",'id' => "PersonDisp_id", 'name' => "Name", 'iconCls' => 'disp16', 'leaf' => true, 'cls' => "folder");
							$arr_2 = $this->GetPersonEPHChild($childrens, $field, $data['level'], "End");
							$arr_1 = array_merge($arr_1,$arr_2);
						}
						
						break;
					case 4:
						if (($data['level_two']=='LpuSection') || ($data['level_two']=='All'))
							{
							$childrens = $this->EPHmodel->GetLpuSectionPidNodeList($data);
							$field = Array('object' => "LpuSection",'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-section16', 'leaf' => true, 'cls' => "folder");
							}
					default:
						$childrens = $this->EPHmodel->GetLpuNodeList($data);
						$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu16', 'leaf' => false, 'cls' => "folder");
						break;
				}
			}
			//$val = $this->GetPersonEPHChild($childrens, $field, $data['level']);
			if ( count($arr_1)>0 )
				{
				$val = array_merge($arr_1,$val);
				}
			/*
			if ( count($arr_2)>0 )
				{
				$val = array_merge($val,$arr_2);
				}
			if ( count($arr_3)>0 )
				{
				$val = array_merge($val,$arr_3);
				}
			*/
			usort($val, array($this, 'cmp'));
			//print_r ($val);
			$this->ReturnData($val);
		}
	}

}
?>