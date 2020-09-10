<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Description of EvnReanimatPeriod_model
 *
 * @author Muskat Boris 
 * @version			25.05.2017
 */

//ФОРМА ВВОДА РЕАНИМАЦИОННОГО ПЕРИОДА****************************************************************************************************************************************
//ERPEW_NSI() --  формирование справочников для формы редактирования реанимационного периода
//getParamsERPWindow($arg) -- Формирование параметров для окна редактирования реанимационного периода
//EvnReanimatPeriod_Save($arg) - СОхранение изменений реанимационного периода
//loadEvnReanimatPeriodViewData($data) - индикация реанимационных периодов на форме "движения" в рамках формы ЭМК
//loudEvnReanimatPeriodGrid_PS() - загрузка таблици реанимационных периодов в окно КВС
//ШКАЛЫ****************************************************************************************************************************************************************
//getapache_TreeData($node) - формирование дерева корректирующих параметров шкалы APACHE
//loudEvnScaleGrid($data) - загрузка таблици результатов расчётов (исследований) по шкалам
//getEvnScaleContent($data) - Получение из БД данных конкретного расчёта (исследования) по шкале - 
//getEvnScaleContentEMK($data)- Получение из БД данных конкретного расчёта (исследования) по шкале для ЭМК 
// EvnScales_Del() - удаление записи шкалы
// EvnScale_Save($arg) - Сохранение в БД данных конкретного расчёта по шкале - 
//РЕАНИМАЦИОННЫЕ МЕРОПРИЯТИЯ*************************************************************************************************************************************
//loudEvnReanimatActionGrid($data) - загрузка таблици реанимационных мероприятий
//loudEvnReanimatActionEMK($data) - загрузка данных реанимационных мероприятий для ЭМК
//EvnReanimatAction_Del() - удаление записи мероприятия
//EvnReanimatAction_Save($arg) - Сохранение в БД данных конкретного реанимационного мероприятия
//GetParamIVL($data) - Извлечение данных параметров ИВЛ
//GetReanimatActionRate($data)	- Извлечение данных периодических измерений, проводимых в рамках реанимационных мероприятий
//GetCardPulm($data) - Извлечение данных Сердечно-лёгочной реанимации
//РЕГУЛЯРНЫЕ НАБЛЮДЕНИЯ*****************************************************************************************************************************************
//loudEvnReanimatConditionGrid($data) - загрузка таблици регулярного наблюдения состояния
//loudEvnReanimatConditionGridEMK($data) - загрузка регулярного наблюдения состояния для ЭМК
//getDataToNewCondition() - получение данных шкал и мероприятий для нового наблюдения
//EvnReanimatCondition_Del() - удаление записи регулярного наблюдения состояния
//EvnReanimatCondition_Save($arg) - Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
//// getDataToNotePrint($data) - извлечение данных для печати дневника/поступления
//getAntropometrData($data)	- Возвращает антропометрические данные конкретного пациента за определённый период
//GetBreathAuscultative() - Возвращает данные о дыхании аускультативно
//ПЕРЕВОД В РЕАНИМАЦИЮ И ИЗ******************************************************************************************************************************************
//getReanimSectionPatientList($data) - Формирование списка пациентов в отделениях, относящихся к реанимационной службе
//moveToReanimation($data) - Перевод пациента в реанимацию из АРМ-ов стационара и реаниматора
//getToReanimationFromFewPS($data) - Индикация нескольких карт выбывшего из стационара для выбора для перевода в реанимацию
//moveToReanimationFromPriem($data) - Перевод пациента в реанимацию из АРМ приёмного отделения
//moveToReanimationOutPriem() - Перевод пациента в реанимацию минуя приёмное отделене
//getProfilSectionId() - озвращает Id первого попавшегося отделения обслуживаемого данной службой реанимации
//endReanimatReriod($data) - Завершение реанимационного периода проверка - а есть ли подготовка данных для окна
//checkEvnSectionByRPClose() - Проверка завершения реанимационных периодов и исхода последнего РП при завершении движения
//checkBeforeLeave() - Проверка завершения реанимационных периодов и исхода последнего РП при попытке выписки
//checkBeforeDelEvn() -	Проверка завершения реанимационных периодов при попытке удаления КВС или движения
//deleteEvnReanimatPeriod()	- Удаление реанимационного периода  из ЭМК
//delReanimatPeriod()	- Удаление реанимационного периода из АРМ-ов стационара и реаниматолога
//changeReanimatPeriodCheck() - проверка можно ли переводить из одной реанимации в другую
//changeReanimatPeriod() - перевод из одной реанимации в другую
//printPatientList($data) - Печать списка пациентов
//ОТОБРАЖЕНИЕ НА АРМе**********************************************************************************************************************************************
//getReanimationPatientList($data) - список пациентов переведённых в реанимацию для отображения в дереве на АРМ реаниматолога
//НАЗНАЧЕНИЯ*******************************************************************************************************************************************************
//loudEvnPrescrGrid($data) загрузка таблици назначений
//ReanimatPeriodPrescrLink_Save($data) создание прикрепления назначения к РП
//loudEvnDirectionGrid($data) - загрузка таблици направлений
//getEvnDirectionViewData($data) - загрузка списка направлений для проссмотра
//ReanimatPeriodDirectLink_Save($data) -- создание прикрепления направлеения к РП
//getDirectionLinkedDocs($data) - загрузка таблици дополнительных документов прикреплённых к направлению
//loudEvnDrugCourseGrid(data) - загрузка таблици курсов лекарственных средств
//loudEvnPrescrTreatDrugGrid($data) - загрузка таблици назначений / лекарственных средств
//ReanimatPeriodDrugCourse_Save(data) - создание прикрепления курса лекарств к РП

//ЧУЖИЕ******************************************************************************************************************************
//getReanimationServices($data) - Получение списка реанимационных служб по МО
//mMoveToReanimation($data) - Перевод пациента в реанимацию из АРМа мобильного стационара
//sendCallReanimateTeamMessage($data)Отправка сообщения всему персоналу на службе реанимации


require_once('EvnAbstract_model.php');



class EvnReanimatPeriod_model extends EvnAbstract_model {
	//put your code here
	
	//ФОРМА ВВОДА РЕАНИМАЦИОННОГО ПЕРИОДА  /  ЭМК*****************************************************************************************************************************
	/**
     * BOB - 24.05.2017
     * формирование справочников для формы редактирования реанимационного периода
	 */
	function ERPEW_NSI() {
		
		//Параметры шкалы glasgow   //BOB - 07.02.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'glasgow' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);

		$EvnScaleglasgowSrc = $result4->result('array');
		$EvnScaleglasgowDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScaleglasgowSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScaleglasgowDst[$ParameterType_SysNick] = array();
			}
			$EvnScaleglasgowDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы glasgow_ch   //BOB - 07.02.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'glasgow_ch' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);

		$EvnScaleglasgow_chSrc = $result4->result('array');
		$EvnScaleglasgow_chDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScaleglasgow_chSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScaleglasgow_chDst[$ParameterType_SysNick] = array();
			}
			$EvnScaleglasgow_chDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы sofa   //BOB - 07.02.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'sofa' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalesofaSrc = $result4->result('array');
		$EvnScalesofaDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalesofaSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalesofaDst[$ParameterType_SysNick] = array();
			}
			$EvnScalesofaDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы apache   //BOB - 07.02.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'apache' 
				   and SC.ScaleParameterType_id <= 28
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScaleapacheSrc = $result4->result('array');
		$EvnScaleapacheDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScaleapacheSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScaleapacheDst[$ParameterType_SysNick] = array();
			}
			$EvnScaleapacheDst[$ParameterType_SysNick][] = $row;
		}
		
		
		//Параметры шкалы waterlow   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'waterlow' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalewaterlowSrc = $result4->result('array');
		$EvnScalewaterlowDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalewaterlowSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalewaterlowDst[$ParameterType_SysNick] = array();
			}
			$EvnScalewaterlowDst[$ParameterType_SysNick][] = $row;
		}
		
		
		//Параметры шкалы rass   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'rass' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalerassSrc = $result4->result('array');
		$EvnScalerassDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalerassSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalerassDst[$ParameterType_SysNick] = array();
			}
			$EvnScalerassDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы hunt_hess   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'hunt_hess' 
				   and SC.ScaleParameterResult_Name not like 'Дополнительно%'  -- BOB - 29.03.2019
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalehunt_hessSrc = $result4->result('array');
		$EvnScalehunt_hessDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalehunt_hessSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalehunt_hessDst[$ParameterType_SysNick] = array();
			}
			$EvnScalehunt_hessDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы four   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'four' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalefourSrc = $result4->result('array');
		$EvnScalefourDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalefourSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalefourDst[$ParameterType_SysNick] = array();
			}
			$EvnScalefourDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы mrc   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'mrc' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalemrcSrc = $result4->result('array');
		$EvnScalemrcDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalemrcSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalemrcDst[$ParameterType_SysNick] = array();
			}
			$EvnScalemrcDst[$ParameterType_SysNick][] = $row;
		}
		
		
		//Параметры шкалы VAScale   //BOB - 23.11.2018
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'VAScale' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScaleVAScaleSrc = $result4->result('array');
		$EvnScaleVAScaleDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScaleVAScaleSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScaleVAScaleDst[$ParameterType_SysNick] = array();
			}
			$EvnScaleVAScaleDst[$ParameterType_SysNick][] = $row;
		}
		
		//Параметры шкалы nihss   //BOB - 23.02.2019
		$query = "		
				select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
					CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				  from dbo.v_Scale SC with (nolock)
				 where SC.ScaleType_SysNick = 'nihss' 
				 order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
	        ";
        $result4 = $this->db->query($query);
		$EvnScalenihssSrc = $result4->result('array');
		$EvnScalenihssDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalenihssSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalenihssDst[$ParameterType_SysNick] = array();
			}
			$EvnScalenihssDst[$ParameterType_SysNick][] = $row;
		}

		
		//Параметры шкалы glasgow_neonat   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'glasgow_neonat' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScaleglasgow_neonatSrc = $result4->result('array');
		$EvnScaleglasgow_neonatDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScaleglasgow_neonatSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScaleglasgow_neonatDst[$ParameterType_SysNick] = array();
			}
			$EvnScaleglasgow_neonatDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы psofa   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'psofa' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalepsofaSrc = $result4->result('array');
		$EvnScalepsofaDst = array();	
		$ParameterType_SysNick = '';
		$EvnScalepsofaDst['cardiovascular'] = array();
		$EvnScalepsofaDst['renal'] = array();
		foreach($EvnScalepsofaSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalepsofaDst[$ParameterType_SysNick] = array();
			}
			$EvnScalepsofaDst[$ParameterType_SysNick][] = $row;

			if (strpos($row['ScaleParameterType_SysNick'], 'cardiovascular') === 0)
				$EvnScalepsofaDst['cardiovascular'][] = $row;
			if (strpos($row['ScaleParameterType_SysNick'], 'renal') === 0)
				$EvnScalepsofaDst['renal'][] = $row;
		}

		//Параметры шкалы psas   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'psas' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalepsasSrc = $result4->result('array');
		$EvnScalepsasDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalepsasSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalepsasDst[$ParameterType_SysNick] = array();
			}
			$EvnScalepsasDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы pelod   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'pelod' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalepelodSrc = $result4->result('array');
		$EvnScalepelodDst = array();	
		$ParameterType_SysNick = '';
		$EvnScalepelodDst['pressure'] = array();
		$EvnScalepelodDst['renal'] = array();
		foreach($EvnScalepelodSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalepelodDst[$ParameterType_SysNick] = array();
			}
			$EvnScalepelodDst[$ParameterType_SysNick][] = $row;

			if (strpos($row['ScaleParameterType_SysNick'], 'pressure') === 0)
				$EvnScalepelodDst['pressure'][] = $row;
			if (strpos($row['ScaleParameterType_SysNick'], 'renal') === 0)
				$EvnScalepelodDst['renal'][] = $row;
		}

		//Параметры шкалы npass   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'npass' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalenpassSrc = $result4->result('array');
		$EvnScalenpassDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalenpassSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalenpassDst[$ParameterType_SysNick] = array();
			}
			$EvnScalenpassDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы comfort   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'comfort' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalecomfortSrc = $result4->result('array');
		$EvnScalecomfortDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalecomfortSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalecomfortDst[$ParameterType_SysNick] = array();
			}
			$EvnScalecomfortDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы pipp   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'pipp' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalepippSrc = $result4->result('array');
		$EvnScalepippDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalepippSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalepippDst[$ParameterType_SysNick] = array();
			}
			$EvnScalepippDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы bind   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'bind' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalebindSrc = $result4->result('array');
		$EvnScalebindDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalebindSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalebindDst[$ParameterType_SysNick] = array();
			}
			$EvnScalebindDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы nips   //BOB - 20.02.2020
		$query = "		
			select SC.ScaleParameterType_SysNick,  SC.ScaleParameterResult_Name, SC.ScaleParameterResult_id, 
				CONVERT(int,SC.ScaleParameterResult_Value) as ScaleParameterResult_Value, SC.ScaleParameterType_id 
				from dbo.v_Scale SC with (nolock)
				where SC.ScaleType_SysNick = 'nips' 
				order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
        $result4 = $this->db->query($query);

		$EvnScalenipsSrc = $result4->result('array');
		$EvnScalenipsDst = array();	
		$ParameterType_SysNick = '';
		foreach($EvnScalenipsSrc as $row){
			if ($row['ScaleParameterType_SysNick'] != $ParameterType_SysNick){
				$ParameterType_SysNick = $row['ScaleParameterType_SysNick'];				
				$EvnScalenipsDst[$ParameterType_SysNick] = array();
			}
			$EvnScalenipsDst[$ParameterType_SysNick][] = $row;
		}


				
		/*регулярное наблюдение состояния**********************************************************************************************************************************/		
		
		//Стороны
		$query = "		
			  select SideType_id, SideType_Name, SideType_SysNick
				from dbo.SideType  with (nolock)
			   order by SideType_id
	        ";
		$ReanimConditParam_SideType = $this->db->query($query)->result('array');


		/*реанимационные мероприятия**********************************************************************************************************************************/		
		//Лекарственные Средства  //BOB - 05.03.2020
		$query = "		
			select ReanimDrugType_id,	ReanimDrugType_Name
			 from dbo.v_ReanimDrugType
			order by ReanimDrugType_id
		";
		$ReanimDrugType = $this->db->query($query)->result('array');

		$ReturnObject = array(		
								'EvnScaleglasgow' => $EvnScaleglasgowDst,
								'EvnScaleglasgow_ch' => $EvnScaleglasgow_chDst,
								'EvnScalesofa' => $EvnScalesofaDst,
 								'EvnScaleapache' => $EvnScaleapacheDst,
								'EvnScalewaterlow' => $EvnScalewaterlowDst,
								'EvnScalerass' => $EvnScalerassDst,
								'EvnScalehunt_hess' => $EvnScalehunt_hessDst,
								'EvnScalefour' => $EvnScalefourDst,	
								'EvnScalemrc' => $EvnScalemrcDst,	
								'EvnScaleVAScale' => $EvnScaleVAScaleDst,	
								'EvnScalenihss' => $EvnScalenihssDst,
								'EvnScaleglasgow_neonat' => $EvnScaleglasgow_neonatDst,
								'EvnScalepsofa' => $EvnScalepsofaDst,
								'EvnScalepsas' => $EvnScalepsasDst,
								'EvnScalepelod' => $EvnScalepelodDst,
								'EvnScalenpass' => $EvnScalenpassDst,
								'EvnScalecomfort' => $EvnScalecomfortDst,
								'EvnScalepipp' => $EvnScalepippDst,
								'EvnScalebind' => $EvnScalebindDst,
								'EvnScalenips' => $EvnScalenipsDst,

								'SideType' => $ReanimConditParam_SideType,

								'ReanimDrugType' => $ReanimDrugType,

                               'Message' => '');

		//	echo '<pre>' . print_r($ReturnObject, 1) . '</pre>'; //BOB - 25.01.2017
		
		return $ReturnObject;   //BOB - 21.03.2019

	}

	/**
     * BOB - 24.01.2019
     * формирование справочников для формы редактирования реанимационного периода
	 */
	function loadReanimatSyndromeType() {
		$query = "
			select ReanimatSyndromeType_id, ReanimatSyndromeType_Name 
			  from dbo.ReanimatSyndromeType  with (nolock)
			 order by ReanimatSyndromeType_id
        ";
        $result = $this->db->query($query);
		
		if (is_object($result))
            return $result->result('array');
 	    else
            return false;
 	}



	 /**
     *  Формирование параметров для окна редактирования реанимационного периода
	 * BOB - 25.11.2017
     */
    function getParamsERPWindow($arg)
    {
		$query = "
			select  ERP.EvnReanimatPeriod_id,
					ERP.EvnReanimatPeriod_pid,
					convert(varchar(10), ERP.EvnReanimatPeriod_setDate  ,104) as EvnReanimatPeriod_setDate,
					ERP.EvnReanimatPeriod_setTime,
					convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,104) as EvnReanimatPeriod_disDate,
					ERP.EvnReanimatPeriod_disTime,
					ERP.ReanimReasonType_id,   --//BOB - 07.02.2018
					ERP.ReanimResultType_id,	--//BOB - 07.02.2018
					ERP.LpuSectionBedProfile_id, --//BOB - 25.10.2018
					ERP.ReanimatAgeGroup_id, 		--//BOB - 23.01.2020
					LS.LpuSection_id,
					LS.LpuSection_Name,
					LU.LpuUnitType_id,
					MS.MedService_id,
					MS.MedService_Name,
					convert(varchar(10), ES.EvnSection_setDate  ,104) as EvnSection_setDate,
					ES.EvnSection_setTime,
					ES.Diag_id, 
					D.Diag_Code, 
					D.Diag_Name,
					EPS.EvnPS_NumCard,
					EPS.Lpu_id,
					EPS.EvnPS_id as EvnReanimatPeriod_rid,
					EPS.Diag_pid as Diag_id_PS,
					case
						when dp.Diag_Code IN ('U07.1', 'U07.2') then 3
						when dd.Diag_Code IN ('U07.1', 'U07.2') then 3
						when d.Diag_Code IN ('U07.1', 'U07.2') then 3
						when exists(
							select top 1
								edps.EvnDiagPS_id
							from
								v_EvnDiagPS edps (nolock)
								inner join v_Diag d (nolock) on d.Diag_id = edps.Diag_id 
							where
								edps.EvnDiagPS_rid = EPS.EvnPS_id
								and edps.DiagSetType_id in (1, 2, 3)
								and d.Diag_Code IN ('U07.1', 'U07.2')
						) then 3
						else RepositoryObserv.CovidType_id
					end as CovidType_id
			  from  dbo.v_EvnReanimatPeriod ERP with(nolock)
					inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = ERP.EvnReanimatPeriod_pid
					inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ERP.LpuSection_id
					left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_MedService MS with(nolock) on MS.MedService_id = ERP.MedService_id
					left join dbo.Diag D with(nolock) on D.Diag_id = isnull(ES.Diag_id, EPS.Diag_pid)
					left join v_Diag DD with (nolock) on EPS.Diag_did = DD.Diag_id
					left join v_Diag DP with (nolock) on EPS.Diag_pid = DP.Diag_id
					outer apply (
						select top 1 CovidType_id
						from v_RepositoryObserv with (nolock)
						where Evn_id = EPS.EvnPS_id
					) RepositoryObserv
			  where ERP.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
		";
        $result = $this->db->query($query, array('EvnReanimatPeriod_id' => $arg['EvnReanimatPeriod_id']));
		
		if ( !is_object($result) ) return false;

		$erp_data = $result->result('array');

		$query = "
			 select ERP.Person_id, 
					ERP.PersonEvn_id, 
					ERP.Server_id, 
					PS.PersonSurName_SurName as Person_Surname,
					PS.PersonFirName_FirName as Person_Firname,
					PS.PersonSecName_SecName as Person_Secname,
					PS.PersonBirthDay_BirthDay as Person_Birthday,
					PS.Sex_id
			   from dbo.v_EvnReanimatPeriod ERP with(nolock) 
			   inner join PersonState PS with(nolock) on ERP.Person_id = PS.Person_id
			   where EvnReanimatPeriod_id = :EvnReanimatPeriod_id
		";
        $result = $this->db->query($query, array('EvnReanimatPeriod_id' => $arg['EvnReanimatPeriod_id']));
		
		if ( !is_object($result) ) return false;

		$pers_data = $result->result('array');
		
		//BOB - 27.09.2019
		$query = "
			select MSM.MedPersonal_id,  MPC.Person_id, LEFT(PS.PersonSurName_SurName, 1) + LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) + ' ' + SUBSTRING(PS.PersonFirName_FirName, 1, 1) + '.' + SUBSTRING(PS.PersonSecName_SecName, 1, 1)  + '.' as EvnReanimatCondition_Doctor, PS.PersonSurName_SurName, PS.PersonFirName_FirName, PS.PersonSecName_SecName
			  from  v_EvnReanimatPeriod ERP
			  inner join MedServiceMedPersonal MSM on ERP.MedService_id = MSM.MedService_id
			  inner join MedPersonalCache MPC on MSM.MedPersonal_id = MPC.MedPersonal_id
			  inner join PersonState PS on MPC.Person_id = PS.Person_id
			 where ERP.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			   and MSM.MedServiceMedPersonal_endDT is NULL
			 group by MSM.MedPersonal_id,  MPC.Person_id, PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
			 order by PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
		";
        $result = $this->db->query($query, array('EvnReanimatPeriod_id' => $arg['EvnReanimatPeriod_id']));
		
		if ( !is_object($result) ) return false;

		$MS_doctors = $result->result('array');
		//BOB - 27.09.2019
		
		$ReturnObject = array(
			'erp_data' => $erp_data,
			'pers_data' => $pers_data,
			'MS_doctors' => $MS_doctors
		);	
		//		echo '<pre>' . print_r($ReturnObject, 1) . '</pre>'; //BOB - 20.10.2017
		return $ReturnObject;
	}



	 /**
     *  СОхранение изменений реанимационного периода
	 * BOB - 13.11.2017
     */	
	function EvnReanimatPeriod_Save($arg)
	{
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$arg['EvnReanimatPeriod_setDate'] .= ' '.$arg['EvnReanimatPeriod_setTime'].':00';
		if(($arg['EvnReanimatPeriod_disDate'] == '') || $arg['EvnReanimatPeriod_disTime'] == ''){
			$arg['EvnReanimatPeriod_disDate'] = null;
		}
		else {
			$arg['EvnReanimatPeriod_disDate'] .=" ".$arg['EvnReanimatPeriod_disTime'].":00";
		}
		
		//BOB - 17.05.2018
		$params = array(
						'EvnReanimatPeriod_id' => $arg['EvnReanimatPeriod_id'],
						'EvnReanimatPeriod_pid' => $arg['EvnReanimatPeriod_pid'],
						'EvnReanimatPeriod_setDT' => $arg['EvnReanimatPeriod_setDate'],
						'EvnReanimatPeriod_disDT' => $arg['EvnReanimatPeriod_disDate']
						);
		
		$query = "
				declare
					@EvnReanimatPeriod_id bigint  = :EvnReanimatPeriod_id,
					@EvnReanimatPeriod_pid bigint = :EvnReanimatPeriod_pid,
					@EvnReanimatPeriod_setDT datetime = :EvnReanimatPeriod_setDT, 
					@EvnReanimatPeriod_disDT datetime = :EvnReanimatPeriod_disDT, 

					@EvnSection_setDT datetime,
					@EvnSection_disDT datetime,
					@EvnSection_IsInReg bigint,
					@Person_id bigint,

					@EvnReanimatPeriod_id_other bigint,
					@EvnReanimatPeriod_setDT_other datetime,
					@EvnReanimatPeriod_disDT_other datetime,

					@Child_setDT_Min  datetime,
					@Child_setDT_Max  datetime,
					--@Child_disDT_Min  datetime,
					@Child_disDT_Max  datetime,

					@err_status varchar(10) = 'norm',
					@err_message varchar(1000) = '';

					if ((@EvnReanimatPeriod_disDT is not null) and (@EvnReanimatPeriod_setDT > @EvnReanimatPeriod_disDT))begin
						set @err_status = 'err'
						set @err_message = '~Дата начало РП превышает дату окончания РП!'
					end
					else begin
					
						-- нахожу родительское движение
						select @EvnSection_setDT = EvnSection_setDT, @EvnSection_disDT = EvnSection_disDT, @EvnSection_IsInReg = EvnSection_IsInReg, @Person_id = Person_id 
						 from dbo.v_EvnSection with (nolock)
						where EvnSection_id = @EvnReanimatPeriod_pid

						--select @EvnSection_setDT as EvnSection_setDT, @EvnSection_disDT as EvnSection_disDT, @EvnSection_IsInReg as EvnSection_IsInReg, @Person_id as Person_id 

						-- ЕСЛИ движение в реестре ОМП
						if (isnull(@EvnSection_IsInReg, 1) <> 1 ) begin
							set @err_status = 'err'
							set @err_message = '~Случай лечения уже в реестре ОМП, изменения РП невозможны!'
						end
						else begin
							--дата начала РП должна быть в пределах дат «движения»
							if not ((@EvnReanimatPeriod_setDT >= @EvnSection_setDT) and ((@EvnReanimatPeriod_setDT < @EvnSection_disDT) or (@EvnSection_disDT is null))) begin	
								set @err_status = 'err'
								set @err_message = @err_message + '~Начало РП вне периода Движения'
							end

							--дата начала РП должна быть строго больше окончания предыдущего РП, если он имеется
							set @EvnReanimatPeriod_id_other = null
							set @EvnReanimatPeriod_disDT_other = null
							--нахожу предыдущий РП
							select top 1 @EvnReanimatPeriod_id_other = EvnReanimatPeriod_id, @EvnReanimatPeriod_disDT_other = EvnReanimatPeriod_disDT 
							from dbo.v_EvnReanimatPeriod with (nolock)
							where Person_id = @Person_id
							  and EvnReanimatPeriod_setDT < @EvnReanimatPeriod_setDT
							  and EvnReanimatPeriod_id <> @EvnReanimatPeriod_id
							order by EvnReanimatPeriod_setDT desc

							--select @EvnReanimatPeriod_id_other as EvnReanimatPeriod_id, @EvnReanimatPeriod_disDT_other as EvnReanimatPeriod_disDT , @EvnReanimatPeriod_setDT as EvnReanimatPeriod_setDT

							if ((@EvnReanimatPeriod_id_other is not null) and (@EvnReanimatPeriod_setDT <= @EvnReanimatPeriod_disDT_other)) begin	
								set @err_status = 'err'
								set @err_message = @err_message + '~Начало РП раньше или равно окончанию предыдущего РП'
							end

							-- нахожу минимальную дату начала дочерних сущностей
							select top 1 @Child_setDT_Min = Evn_setDT
							from v_Evn with (nolock)
							where Evn_pid = @EvnReanimatPeriod_id
							order by Evn_setDT

							--дата начала РП не должна быть больше начала дочерних событий
							if ((@Child_setDT_Min is not null) and  (@EvnReanimatPeriod_setDT > @Child_setDT_Min)) begin	
								set @err_status = 'err'
								set @err_message = @err_message + '~Начало РП позже начала дочернего события'
							end
							
							--нахожу следующий РП
							set @EvnReanimatPeriod_id_other = null
							set @EvnReanimatPeriod_setDT_other = null

							select top 1 @EvnReanimatPeriod_id_other = EvnReanimatPeriod_id, @EvnReanimatPeriod_setDT_other = EvnReanimatPeriod_setDT 
							from dbo.v_EvnReanimatPeriod with (nolock)
							where Person_id = @Person_id
							  and EvnReanimatPeriod_setDT > @EvnReanimatPeriod_setDT
							  and EvnReanimatPeriod_id <> @EvnReanimatPeriod_id
							order by EvnReanimatPeriod_setDT asc

							--select @EvnReanimatPeriod_id_other as EvnReanimatPeriod_id, @EvnReanimatPeriod_setDT_other as EvnReanimatPeriod_setDT,  @EvnReanimatPeriod_disDT as EvnReanimatPeriod_disDT


							-- если дата окончания РП пустая 
							if (@EvnReanimatPeriod_disDT is null) begin
							
								-- дата окончания РП обязательно должна быть если есть дата окончания «движения»  //BOB - 09.07.2019
								if (@EvnSection_disDT is not null) begin	
									set @err_status = 'err'
									set @err_message = @err_message + '~Окончание РП обязательно должно быть если есть дата окончания Движения'
								end

								-- не должно быть следующего РП
								if (@EvnReanimatPeriod_id_other is not null) begin
									set @err_status = 'err'
									set @err_message = @err_message + '~Окончание РП отсутствует при наличии следующего РП'
								end
							end
							else begin -- если непустая:

								--select @EvnReanimatPeriod_disDT as EvnReanimatPeriod_disDT, @EvnSection_setDT as EvnSection_setDT, @EvnSection_disDT as EvnSection_disDT

								-- дата окончания РП должна быть в пределах дат «движения»
								if not ((@EvnReanimatPeriod_disDT >= @EvnSection_setDT) and ((@EvnReanimatPeriod_disDT < @EvnSection_disDT) or (@EvnSection_disDT is null))) begin	
									set @err_status = 'err'
									set @err_message = @err_message + '~Окончание РП вне периода Движения'
								end

								-- дата окончания РП должна быть строго меньше начала следующего РП, если он есть
								if ((@EvnReanimatPeriod_id_other is not null) and (@EvnReanimatPeriod_disDT >= @EvnReanimatPeriod_setDT_other)) begin	
									set @err_status = 'err'
									set @err_message = @err_message + '~Окончание РП позже или равно началу следующего РП'
								end


								-- нахожу максимальную дату начала дочерних сущностей
								select top 1 @Child_setDT_Max = Evn_setDT
								from v_Evn with (nolock)
								where Evn_pid = @EvnReanimatPeriod_id
								order by Evn_setDT desc

								-- нахожу максимальную дату окончания дочерних сущностей
								select top 1 @Child_disDT_Max = Evn_disDT
								from v_Evn with (nolock)
								where Evn_pid = @EvnReanimatPeriod_id
								order by Evn_disDT desc

								--select @Child_setDT_Min as Child_setDT_Min, @Child_setDT_Max as Child_setDT_Max, @Child_disDT_Max as Child_disDT_Max


								-- дата окончания РП не должна быть меньше окончаний дочерних событий, а поскольку имеются без окончани, то и начал
								if ((@Child_disDT_Max is not null) and (@EvnReanimatPeriod_disDT < @Child_disDT_Max) or ((@Child_setDT_Max is not null) and (@EvnReanimatPeriod_disDT < @Child_setDT_Max))) begin	
									set @err_status = 'err'
									set @err_message = @err_message + '~Окончание РП раньше окончания или начала дочернего события'
								end
							end
						end
					end
					select @err_status as err_status, @err_message as err_message, @EvnReanimatPeriod_setDT_other as EvnReanimatPeriod_setDT_other

		";
		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));		
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $result = '.print_r($result, 1)); //BOB - 17.05.2018
		
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $resultArray = '.print_r($resultArray, 1)); //BOB - 17.05.2018
		if ($resultArray[0]['err_status'] == 'err'){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $resultArray[0]['err_message'];
			return $Response;
		}
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $resultArray = '.print_r($resultArray, 1)); //BOB - 17.05.2018

		
		//BOB - 08.10.2019
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save isset($resultArray[0][EvnReanimatPeriod_setDT_other]) = '.print_r(isset($resultArray[0]['EvnReanimatPeriod_setDT_other']), 1)); //BOB - 17.05.2018
		$LastRP = isset($resultArray[0]['EvnReanimatPeriod_setDT_other']) ? 0 : 1; //BOB - 08.10.2019 - true - если сохраняемая запись РП последняя, т.е. при контроле не была найдена болоее поздняя
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $LastRP_2 = '.print_r($LastRP, 1));
		//BOB - 08.10.2019

		//BOB - 17.05.2018
		$params = array(
						'EvnReanimatPeriod_id' => $arg['EvnReanimatPeriod_id'],
						'EvnReanimatPeriod_pid' => $arg['EvnReanimatPeriod_pid'],
						'EvnReanimatPeriod_setDT' => $arg['EvnReanimatPeriod_setDate'],
						'EvnReanimatPeriod_disDT' => $arg['EvnReanimatPeriod_disDate'],
						'ReanimReasonType_id' => $arg['ReanimReasonType_id'],
						'ReanimResultType_id' => $arg['ReanimResultType_id'],				
						'LpuSectionBedProfile_id' => $arg['LpuSectionBedProfile_id'],		//BOB - 25.10.2018
						'ReanimatAgeGroup_id' => $arg['ReanimatAgeGroup_id'],		//BOB - 23.01.2020
						'Lpu_id' => $arg['Lpu_id'],
						'Server_id' => $arg['Server_id'],
						'PersonEvn_id' => $arg['PersonEvn_id'],
			
						'pmUser_id' => $arg['pmUser_id']
						);
				//echo '<pre>' . print_r($params, 1) . '</pre>'; //BOB - 20.10.2017
		
		
		$query = "
			declare
			  @EvnReanimatPeriod_id bigint = :EvnReanimatPeriod_id ,

			  @Error_Code int = null,
			  @Error_Message varchar(4000) = null;


			exec p_EvnReanimatPeriod_upd  					
			  @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
			  @EvnReanimatPeriod_pid = :EvnReanimatPeriod_pid,
			  @EvnReanimatPeriod_setDT = :EvnReanimatPeriod_setDT, 
			  @EvnReanimatPeriod_disDT = :EvnReanimatPeriod_disDT, 
			  @ReanimReasonType_id = :ReanimReasonType_id,
			  @ReanimResultType_id = :ReanimResultType_id,
			  @LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
			  @ReanimatAgeGroup_id = :ReanimatAgeGroup_id,
			  @Lpu_id = :Lpu_id, 
			  @Server_id = :Server_id, 
			  @PersonEvn_id = :PersonEvn_id, 

			 @pmUser_id = :pmUser_id,
			 @Error_Code = @Error_Code output,
			 @Error_Message = @Error_Message output;


			select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
		";
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		if ((empty($resultArray[0]['EvnReanimatPeriod_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			//!!!!здесь д.б. return $Response;
		}
		
			//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
			
		$arg['ReanimatRegister_IsPeriodNow'] = 2;	//BOB - 08.10.2019
		
		
		//Если закрытие РП
		if ($arg['EvnReanimatPeriod_disDate'] != null){

			$params = array(
						'EvnReanimatAction_pid' => $arg['EvnReanimatPeriod_id']
						);
			
			$query = "select * from dbo.v_EvnReanimatAction  with (nolock)
					   where EvnReanimatAction_pid = :EvnReanimatAction_pid
						 and EvnReanimatAction_disDT is null";
			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) return false;
			
			//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
			
			$Response = $result->result('array');
			
			//echo '<pre>' . print_r($Response, 1) . '</pre>'; //BOB - 20.10.2017
			
			foreach($Response as &$row ){
				
				$params = array(
						'EvnReanimatAction_id' => $row['EvnReanimatAction_id'],
						'EvnReanimatAction_pid' => $arg['EvnReanimatPeriod_id'],
						'EvnReanimatAction_disDT' => $arg['EvnReanimatPeriod_disDate'],
					
					
						'ReanimatActionType_id' => $row['ReanimatActionType_id'],
						'UslugaComplex_id' => $row['UslugaComplex_id'],
						'EvnUsluga_id' => $row['EvnUsluga_id'],
						'ReanimDrugType_id' => $row['ReanimDrugType_id'],
						'EvnReanimatAction_DrugDose' => $row['EvnReanimatAction_DrugDose'],
						'EvnDrug_id' => $row['EvnDrug_id'],
						'EvnReanimatAction_MethodCode' => $row['EvnReanimatAction_MethodCode'],
						'EvnReanimatAction_ObservValue' => $row['EvnReanimatAction_ObservValue'],
						'ReanimatCathetVeins_id' => $row['ReanimatCathetVeins_id'],
						'CathetFixType_id' => $row['CathetFixType_id'],
						'EvnReanimatAction_CathetNaborName' => $row['EvnReanimatAction_CathetNaborName'],
						'NutritiousType_id' => $row['NutritiousType_id'],
						'EvnReanimatAction_DrugUnit' => $row['EvnReanimatAction_DrugUnit'],
						'EvnReanimatAction_MethodTxt' => $row['EvnReanimatAction_MethodTxt'],
						'EvnReanimatAction_NutritVol' => $row['EvnReanimatAction_NutritVol'],
						'EvnReanimatAction_NutritEnerg' => $row['EvnReanimatAction_NutritEnerg'],
						'MilkMix_id' => $row['MilkMix_id'],    //BOB - 15.04.2020
						
						'EvnReanimatAction_setDT' => $row['EvnReanimatAction_setDT'],
					
						'Lpu_id' => $arg['Lpu_id'],
						'Server_id' => $arg['Server_id'],
						'PersonEvn_id' => $arg['PersonEvn_id'],
						'pmUser_id' => $arg['pmUser_id']
						);
				//echo '<pre>' . print_r($params, 1) . '</pre>'; //BOB - 20.10.2017
				$query = "
					declare
						@EvnReanimatAction_id bigint = :EvnReanimatAction_id, 
						@Error_Code int = null,
						@Error_Message varchar(4000) = null; 

					exec   dbo.p_EvnReanimatAction_upd
						@EvnReanimatAction_id  = @EvnReanimatAction_id,
						@EvnReanimatAction_pid  = :EvnReanimatAction_pid  , 
						@EvnReanimatAction_disDT = :EvnReanimatAction_disDT,
						@Lpu_id = :Lpu_id, 
						@Server_id = :Server_id, 
						@PersonEvn_id = :PersonEvn_id, 
							
						 @ReanimatActionType_id = :ReanimatActionType_id,
						 @UslugaComplex_id = :UslugaComplex_id,
						 @EvnUsluga_id = :EvnUsluga_id,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @EvnReanimatAction_DrugDose = :EvnReanimatAction_DrugDose,
						 @EvnDrug_id = :EvnDrug_id,
						 @EvnReanimatAction_MethodCode = :EvnReanimatAction_MethodCode,
						 @EvnReanimatAction_ObservValue = :EvnReanimatAction_ObservValue,
						 @ReanimatCathetVeins_id = :ReanimatCathetVeins_id,
						 @CathetFixType_id = :CathetFixType_id,
						 @EvnReanimatAction_CathetNaborName = :EvnReanimatAction_CathetNaborName,
						 @NutritiousType_id = :NutritiousType_id,
						 @EvnReanimatAction_DrugUnit = :EvnReanimatAction_DrugUnit,
						 @EvnReanimatAction_MethodTxt = :EvnReanimatAction_MethodTxt,
						 @EvnReanimatAction_NutritVol = :EvnReanimatAction_NutritVol,
						 @EvnReanimatAction_NutritEnerg = :EvnReanimatAction_NutritEnerg,
						 @MilkMix_id = :MilkMix_id,

						 @EvnReanimatAction_setDT = :EvnReanimatAction_setDT,

						@pmUser_id = :pmUser_id,
						@Error_Code  = @Error_Code output,
						@Error_Message = @Error_Message output ;

					select @EvnReanimatAction_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
				";

				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
				if ( !is_object($result) )
					return false;

			}

			
			
			// снятие помеоки "в РП сейчас"
			$arg['ReanimatRegister_IsPeriodNow'] = 1;	//BOB - 08.10.2019
//			$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');			//BOB - 08.10.2019 - закомментил
//			$Response = $this->ReanimatRegister_model->ReanimatRegisterEndRP($arg);
		
		}
		//BOB - 08.10.2019 если сохраняемая запись последняя, только тогда лезем корёжить регистр
		if ($LastRP) {
			$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');			//BOB - 08.10.2019
			$Response = $this->ReanimatRegister_model->ReanimatRegisterEndRP($arg);			
		}
		
		return $Response;
	}


	/**
	 * BOB - 18.04.2017
	 * индикация реанимационных периодов на форме "движения"
	 * в рамках формы ЭМК
	 */
	function loadEvnReanimatPeriodViewData($data){
		
		$Response = array ();

		//BOB - 07.02.2018
		$query = "
				select  ERP.EvnReanimatPeriod_id,
						ERP.EvnReanimatPeriod_pid,
						convert(varchar(10), ERP.EvnReanimatPeriod_setDate  ,104) as EvnReanimatPeriod_setDate,
						ERP.EvnReanimatPeriod_setTime,
						convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,104) as EvnReanimatPeriod_disDate,
						ERP.EvnReanimatPeriod_disTime,
						ERP.ReanimReasonType_id as ReanimReasonType,
						Rea.ReanimReasonType_Name,
						ERP.ReanimResultType_id as ReanimResultType,
						Res.ReanimResultType_Name,
						LS.LpuSection_id,
						LS.LpuSection_Name,
						MS.MedService_id,
						MS.MedService_Name,
						case when exists(
							select * 
							from v_EvnSection ES with(nolock)
							inner join v_DiagFinance DF with(nolock) on DF.Diag_id = ES.Diag_id
							where ES.EvnSection_pid = EPS.EvnPS_id
							and DF.DiagFinance_IsRankin = 2
						) then 2 else 1 end as DiagFinance_IsRankin
				  from  dbo.v_EvnReanimatPeriod ERP with(nolock)
						left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ERP.LpuSection_id
						left join v_MedService MS with(nolock) on MS.MedService_id = ERP.MedService_id
						left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ERP.EvnReanimatPeriod_rid
						left join ReanimReasonType Rea with(nolock) on Rea.ReanimReasonType_id =  ERP.ReanimReasonType_id
						left join ReanimResultType Res with(nolock) on Res.ReanimResultType_id =  ERP.ReanimResultType_id
				 where  ERP.EvnReanimatPeriod_pid = :EvnReanimatPeriod_pid
				 order by ERP.EvnReanimatPeriod_setDT desc
		";	
		$result = $this->db->query($query, array('EvnReanimatPeriod_pid' => $data['EvnReanimatPeriod_pid']));
		
		if(!is_object($result))
			return false;

		$Response = $result->result('array');
		
		
		foreach($Response as &$row ){
			$data['EvnReanimatCondition_pid'] = $row['EvnReanimatPeriod_id'];
			$row['EvnReanimatCondition'] = $this->loudEvnReanimatConditionGridEMK($data);
			$data['EvnReanimatAction_pid'] = $row['EvnReanimatPeriod_id'];			
			$row['EvnReanimatAction'] = $this->loudEvnReanimatActionEMK($data);	
			$data['EvnScale_pid'] = $row['EvnReanimatPeriod_id'];			
			$row['EvnScale'] = $this->loudEvnScaleGrid($data);
			foreach($row['EvnScale'] as &$OneScale){
				$data['EvnScale_id'] = $OneScale['EvnScale_id'];
				$OneScale['ScaleParam'] = $this->getEvnScaleContentEMK($data);
			}
			
		}
		
		
		
		//	log_message('debug', 'EvnReanimatPeriod_model=>loadEvnReanimatPeriodViewData $Response = '.print_r($Response, 1)); //BOB - 01.12.2017
		return $Response;
	}

	/**
	 * BOB - 04.09.2018
	 * загрузка таблици реанимационных периодов в окно КВС
	 */
	function loudEvnReanimatPeriodGrid_PS($data) {
		$Response = array ();

		//BOB - 07.02.2018
		$query = "
				select  ERP.EvnReanimatPeriod_id,
						ERP.EvnReanimatPeriod_pid,
						convert(varchar(10), ERP.EvnReanimatPeriod_setDate  ,104) + ' ' + cast(ERP.EvnReanimatPeriod_setTime as varchar) as EvnReanimatPeriod_setDT,
						convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,104) + ' ' + cast(ERP.EvnReanimatPeriod_disTime as varchar) as EvnReanimatPeriod_disDT,
						Rea.ReanimReasonType_Name,
						Res.ReanimResultType_Name,
						LS.LpuSection_id,
						LS.LpuSection_Name,
						MS.MedService_id,
						MS.MedService_Name
				  from  dbo.v_EvnReanimatPeriod ERP with(nolock)
						left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ERP.LpuSection_id
						left join v_MedService MS with(nolock) on MS.MedService_id = ERP.MedService_id
						left join ReanimReasonType Rea with(nolock) on Rea.ReanimReasonType_id =  ERP.ReanimReasonType_id
						left join ReanimResultType Res with(nolock) on Res.ReanimResultType_id =  ERP.ReanimResultType_id
				 where  ERP.EvnReanimatPeriod_rid = :EvnReanimatPeriod_rid
				 order by ERP.EvnReanimatPeriod_setDT
		";	
		$result = $this->db->query($query, array('EvnReanimatPeriod_rid' => $data['EvnPS_id']));
		
		if(!is_object($result))
			return false;

		$Response = $result->result('array');
		
		
		//log_message('debug', 'EvnReanimatPeriod_model=>loudEvnReanimatPeriodGrid_PS $Response = '.print_r($Response, 1)); //BOB - 01.12.2017
		return $Response;	
		
	}


	//ШКАЛЫ********************************************************************************************************************************************************
	/**
	 *BOB - 17.06.2017
	 * формирование дерева корректирующих параметров шкалы APACHE
	 */
	function getapache_TreeData($node) {
	
		$params = array(
			'node' => isset($node) ? $node : null
			);
		
		switch ($node) {
			case 'root':  //возвращает 2 строки:  Неоперированные пациенты, Послеоперационные пациенты
				$return[] = array(
					'text' => 'Неоперированные пациенты',
					'id' => 'no_oper',
					'leaf' => false,
				);		
				$return[] = array(
					'text' => 'Послеоперационные пациенты',
					'id' => 'oper',
					'leaf' => false,
				);		
				break;
			case 'no_oper': //возвращает названия разделов по Неоперированным
				$query = "
					select SC.ScaleParameterType_id as num_id, SC.ScaleParameterType_SysNick as id, SC.ScaleParameterType_Name as text, 0 as leaf 
					  from dbo.v_Scale SC with (nolock) 
					 where SC.ScaleType_id = 6
					   and SC.ScaleParameterType_id in (29,30,31,32,33,34)
					 group by SC.ScaleParameterType_id, SC.ScaleParameterType_SysNick, SC.ScaleParameterType_Name 
					 order by SC.ScaleParameterType_id 
				";
				$result = $this->db->query($query);
				$return = $result->result('array');
				break;
			case 'breath_insufficiency':  //возвращает содержимое разделов по Неоперированным 
			case 'heart_insufficiency':
			case 'trauma':
			case 'neurology':
			case 'other':
			case 'organ_system':
			case 'after_operation_plan_organ_system':////возвращает содержимое разделов 'Если ничего не подходит - основная органная система' по Послеоперационным
			case 'after_operation_extra_organ_system':
				$query = "
					select SC.ScaleParameterType_SysNick + '_' + convert(varchar,SC.ScaleParameterResult_id) as id, SC.ScaleParameterType_SysNick,
					SC.ScaleParameterResult_Name + '&nbsp;&nbsp;&nbsp;<span style=\"color: darkblue;\">' + convert(varchar,convert(float,  ScaleParameterResult_Value)) +'</span>' as text, 
					convert(varchar,convert(float,  SC.ScaleParameterResult_Value)) as ScaleParameterResult_Value, 1 as leaf, ScaleParameterType_id,ScaleParameterResult_id
					  from dbo.v_Scale SC with (nolock) 
					 where SC.ScaleParameterType_SysNick = :node
					 order by SC.ScaleParameterResult_id				
					 ";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'p_EvnScale_ins exec query: ', getDebugSql($query, $params));		
				$return = $result->result('array');
				break;
			case 'oper':  //возвращает названия разделов по Послеоперационным
				$query = "
					select SC.ScaleParameterType_id as num_id, SC.ScaleParameterType_SysNick as id, SC.ScaleParameterType_Name as text, 0 as leaf 
					  from dbo.v_Scale SC with (nolock) 
					 where SC.ScaleType_id = 6
					   and SC.ScaleParameterType_id in (35,37)
					 group by SC.ScaleParameterType_id, SC.ScaleParameterType_SysNick, SC.ScaleParameterType_Name 
					 order by SC.ScaleParameterType_id 
				";
				$result = $this->db->query($query);
				$return = $result->result('array');
				break;
			case 'after_operation_plan':  //возвращает содержимое разделов по Послеоперационным 
			case 'after_operation_extra':
				$query = "
					select SC.ScaleParameterType_SysNick + '_' + convert(varchar,SC.ScaleParameterResult_id) as id, SC.ScaleParameterType_SysNick,
					SC.ScaleParameterResult_Name + '&nbsp;&nbsp;&nbsp;<span style=\"color: darkblue;\">' + convert(varchar,convert(float,  ScaleParameterResult_Value)) +'</span>' as text, 
					convert(varchar,convert(float,  SC.ScaleParameterResult_Value)) as ScaleParameterResult_Value, 1 as leaf, ScaleParameterType_id,ScaleParameterResult_id
					  from dbo.v_Scale SC with (nolock) 
					 where SC.ScaleParameterType_SysNick = :node
					 order by SC.ScaleParameterResult_id				
					 ";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'p_EvnScale_ins exec query: ', getDebugSql($query, $params));		
				$return = $result->result('array');				
				$return[] = array(
					'id' => $node.'_organ_system',
					'text' => 'Если ничего не подходит - основная органная система',
					'ScaleParameterResult_Value' => '0',
					'leaf' => false
				);		

				break;
			default:
				$return[] = array(
					'text' => 'кое чё',
					'id' => 'koe_cho',
					'leaf' => true,
				);		
		}
			
		return 	$return;			
	}
	
	
	
	
	/**
	 * BOB - 29.05.2017
	 * загрузка таблици результатов расчётов (исследований) по шкалам
	 */	
	function loudEvnScaleGrid($data) {	
		//BOB - 05.02.2018
		$query = "
			select	ESC.EvnScale_id, 
					ESC.EvnScale_pid, 
					ESC.Person_id, 
					ESC.PersonEvn_id, 
					ESC.Server_id,    
					convert(varchar(10), ESC.EvnScale_setDate  ,104) as EvnScale_setDate,
					ESC.EvnScale_setTime,
					ESC.ScaleType_id, 
					ESC.ScaleType_Name, 
					ESC.ScaleType_SysNick,
					ESC.EvnScale_Result,
					ESC.EvnScale_ResultTradic,
					ESC.EvnScale_AgeMonth
			from dbo.v_EvnScale ESC with (nolock)
			where ESC.EvnScale_pid = :EvnScale_pid
			order by ESC.EvnScale_setDT desc
		";		
		$result = $this->db->query($query, array('EvnScale_pid' => $data['EvnScale_pid']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	
	}
	
	/**
	 * BOB - 29.05.2017
	 * Получение из БД данных конкретного расчёта (исследования) по шкале - 
	 */
	function getEvnScaleContent($data) {
		//BOB - 14.02.2018
		$query = "
  			select	SP.ScaleParameter_id, 
					SP.ScaleParameterType_id, 
					SP.ScaleParameterResult_id, 
				--	ES.ScaleParameterType_SysNick,
					case
						when CHARINDEX('~',ES.ScaleParameterType_SysNick) > 0 then
							SUBSTRING(ES.ScaleParameterType_SysNick, 1 , CHARINDEX('~',ES.ScaleParameterType_SysNick) - 1)
						else ES.ScaleParameterType_SysNick
					end  as ScaleParameterType_SysNick,
					ES.ScaleType_SysNick
			   from dbo.ScaleParameter SP with (nolock)
			  inner join dbo.v_Scale ES  with (nolock) on SP.ScaleParameterType_id = ES.ScaleParameterType_id and SP.ScaleParameterResult_id = ES.ScaleParameterResult_id
			  where SP.EvnScale_id = :EvnScale_id
			  ";
		$result = $this->db->query($query, array('EvnScale_id' => $data['EvnScale_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * BOB - 29.05.2017
	 * Получение из БД данных конкретного расчёта (исследования) по шкале для ЭМК 
	 */
	function getEvnScaleContentEMK($data) {
		//BOB - 14.02.2018
		$query = "
			select	--SP.ScaleParameter_id, 
					--SP.ScaleParameterType_id, 
					--SP.ScaleParameterResult_id, 
					--S.ScaleParameterType_SysNick,
					--S.ScaleType_SysNick,
				S.ScaleParameterType_Name,
					case
						when (S.ScaleType_SysNick = 'apache' and S.ScaleParameterType_id >= 29) then        
							cast(cast(S.ScaleParameterResult_Value as numeric(20,3)) as varchar)
						else 
							cast(cast(S.ScaleParameterResult_Value as numeric(20,0)) as varchar)
					end as ScaleParameterResult_Value,
					S.ScaleParameterResult_Name
					
			   from dbo.ScaleParameter SP  with (nolock)  
			  inner join dbo.v_Scale S with (nolock) on  S.ScaleParameterType_id = SP.ScaleParameterType_id 
													  and S.ScaleParameterResult_id = SP.ScaleParameterResult_id
			  where SP.EvnScale_id = :EvnScale_id
				order by SP.ScaleParameterType_id 			
				";
		$result = $this->db->query($query, array('EvnScale_id' => $data['EvnScale_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * BOB - 13.06.2017
	 * Сохранение в БД данных конкретного расчёта по шкале - 
	 * !!!!!Осталась дырка: @ScaleType_Version bigint = 1 надо бы сделать правильно
	 */
	function EvnScale_Save($data) {
		
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$data['EvnScale_setDate'] .= ' '.$data['EvnScale_setTime'].':00';
		
		$params = array(
			'EvnScale_pid' => $data['EvnScale_pid'],
			'EvnScale_rid' => $data['EvnScale_rid'],
			'EvnScale_setDT' => isset($data['EvnScale_setDate']) ? $data['EvnScale_setDate'] : null,
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'Person_id' => isset($data['Person_id']) ? $data['Person_id'] : null,
			'PersonEvn_id' => isset($data['PersonEvn_id']) ? $data['PersonEvn_id'] : null,
			'Server_id' => isset($data['Server_id']) ? $data['Server_id'] : null,
			'ScaleType_id' => isset($data['ScaleType_id']) ? $data['ScaleType_id'] : null,
			'EvnScale_Result' => isset($data['EvnScale_Result']) ? $data['EvnScale_Result'] : null,
			'EvnScale_ResultTradic' => isset($data['EvnScale_ResultTradic']) ? $data['EvnScale_ResultTradic'] : null,
			'pmUser_id' => isset($data['pmUser_id']) ? $data['pmUser_id'] : null,
			'ScaleParameter' => isset($data['ScaleParameter']) ? json_decode($data['ScaleParameter'], true)	: null,
			'EvnScale_AgeMonth' => 	isset($data['EvnScale_AgeMonth']) ? $data['EvnScale_AgeMonth'] : null
		);
		
		$query = "
			declare 
				@EvnScale_id bigint = null, 
				@Error_Code int = null, 
				@Error_Message varchar(4000) = null; 

			exec dbo.p_EvnScale_ins 
				@EvnScale_id = @EvnScale_id output, 
				@EvnScale_pid = :EvnScale_pid , 
				@EvnScale_rid = :EvnScale_rid , 
				@Lpu_id = :Lpu_id , 
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 

				@ScaleType_id = :ScaleType_id, 
				@EvnScale_Result = :EvnScale_Result, 
				@EvnScale_ResultTradic = :EvnScale_ResultTradic, 
				@EvnScale_AgeMonth = :EvnScale_AgeMonth,
				@EvnScale_setDT = :EvnScale_setDT, 
				@EvnScale_disDT = null, 

				@pmUser_id = :pmUser_id, 
				@Error_Code = @Error_Code output, 
				@Error_Message  = @Error_Message output; 

			select @EvnScale_id as EvnScale_id, @Error_Code as Error_Code, @Error_Message as Error_Message; 
		";
		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'p_EvnScale_ins exec query: ', getDebugSql($query, $params));		
		
		if ( !is_object($result) )
			return false;
		
		$EvnScaleResult = $result->result('array');
		
		if (($EvnScaleResult[0]['EvnScale_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null)){
			
			//BOB - 23.07.2018
			//если SOFA
			if($params['ScaleType_id'] == 5){
				$query = "
					update EvnSection set
					EvnSection_SofaScalePoints = :EvnScale_Result
					where EvnSection_id = (select Evn_pid from Evn  with (nolock) where Evn_id = :EvnScale_pid ) 

					update Evn set
					Evn_updDT = GetDate()
					where Evn_id = (select Evn_pid from Evn  with (nolock) where Evn_id = :EvnScale_pid ) 
				";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'update EvnSection exec query: ', getDebugSql($query, $params));		
			}
			
			
			//BOB - 12.09.2018
			$params['EvnScale_id'] = $EvnScaleResult[0]['EvnScale_id'];
			
			//BOB - 26.11.2018
			//если ВАШ
			if($params['ScaleType_id'] == 19){
				$query = "
					select ScaleParameterType_id, ScaleParameterResult_id from v_Scale S  with (nolock)
					where S.ScaleType_id = :ScaleType_id
					  and S.ScaleParameterResult_Value = :EvnScale_Result
				";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'p_EvnScale_ins exec query: ', getDebugSql($query, $params));		

				if ( !is_object($result) )
					return false;

				$EvnScaleResult = $result->result('array');
				//log_message('debug', 'BOB_0'.print_r($EvnScaleResult, 1));
				$params['ScaleParameter'][0]['ScaleParameterType_id'] = $EvnScaleResult[0]['ScaleParameterType_id'];
				$params['ScaleParameter'][0]['ScaleParameterResult_id'] = $EvnScaleResult[0]['ScaleParameterResult_id'];
				//log_message('debug', 'BOB_1'.print_r($params, 1));
			}
			//BOB - 26.11.2018
			
			
			foreach($params['ScaleParameter'] as $ScaleParameter){
				$params['ScaleParameterType_id'] = $ScaleParameter['ScaleParameterType_id'];
				$params['ScaleParameterResult_id'] = $ScaleParameter['ScaleParameterResult_id'];
				
				$query = "
					declare
						@ScaleParameter_id bigint = null,
						@EvnScale_id bigint = :EvnScale_id,
						@ScaleParameterType_id bigint = :ScaleParameterType_id,
						@ScaleParameterResult_id bigint = :ScaleParameterResult_id,

						@pmUser_id bigint = :pmUser_id,
						@Error_Code int = null,
						@Error_Message varchar(4000) = null; 

					exec   dbo.p_ScaleParameter_ins
						@ScaleParameter_id output,
						@EvnScale_id,
						@ScaleParameterType_id,
						@ScaleParameterResult_id,
						@pmUser_id,
						@Error_Code output,
						@Error_Message output;

					select @ScaleParameter_id as ScaleParameter_id, @Error_Code as Error_Code, @Error_Message as Error_Message;			
				";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'p_ScaleParameter_ins exec query: ', getDebugSql($query, $params));		
				$ScaleParameterResult = $result->result('array');
				
				if (($ScaleParameterResult[0]['Error_Code'] != null) || ($ScaleParameterResult[0]['Error_Message'] != null)){
					$Response['success'] = 'false';
					$Response['Error_Msg'] = $ScaleParameterResult[0]['Error_Code'].' '.$ScaleParameterResult[0]['Error_Message'];
					break;
				}
				//echo '<pre>' . print_r($ScaleParameterResult, 1) . '</pre>'; 

			}
		}
		else {
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
			
		// подумать что возвращать может сообщения просто
		if ( is_object($result) ) {
			return $Response;
		}
		else {
			return false;
		}
		
		return true;
		
	}

		/**
	 * BOB - 21.05.2018
	 * удаление записи шкалы
	 */
	function EvnScales_Del($data) {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$queryParams = array('EvnScale_id' => $data['EvnScale_id'],
							  'pmUser_id' => $pmUser_id);
		$query = "
			declare

			 @EvnScale_id bigint = :EvnScale_id,
			 @pmUser_id bigint = :pmUser_id,
			 @Error_Code int = null,
			 @Error_Message varchar(4000) = null; 

			exec dbo.p_EvnScale_del
			 @EvnScale_id,
			 @pmUser_id,
			 @Error_Code output,
			 @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;		
		";
		
		
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $queryParams));
		
		if ( !is_object($result) )
			return false;

		$EvnScaleResult = $result->result('array');

		if (($EvnScaleResult[0]['Error_Code'] != null) || ($EvnScaleResult[0]['Error_Message'] != null)){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
		return $Response;
	}

	
	//РЕАНИМАЦИОННЫЕ МЕРОПРИЯТИЯ******************************************************************************************************************************
	/**
	 * BOB - 03.07.2017
	 * загрузка таблици реанимационных мероприятий
	 */	
	function loudEvnReanimatActionGrid($data) {	
		$query = "
			select	ERA.EvnReanimatAction_id, 
					ERA.EvnReanimatAction_pid, 
					ERA.Person_id, 
					ERA.PersonEvn_id, 
					ERA.Server_id,    
					convert(varchar(10), ERA.EvnReanimatAction_setDate  ,104) as EvnReanimatAction_setDate,
					ERA.EvnReanimatAction_setTime,
					convert(varchar(10), ERA.EvnReanimatAction_disDate  ,104) as EvnReanimatAction_disDate,
					ERA.EvnReanimatAction_disTime,
					ERA.ReanimatActionType_id,  
					ERA.ReanimatActionType_SysNick,
					ERA.ReanimatActionType_Name, 
					case
						when ERA.ReanimatActionType_id = 3 then ERA.NutritiousType_id
						else ERA.UslugaComplex_id
					end as UslugaComplex_id,
					ERA.EvnUsluga_id,
					ERA.ReanimDrugType_id, 
					ERA.EvnReanimatAction_DrugDose,
					ERA.EvnReanimatAction_DrugUnit,
					ERA.EvnDrug_id,
					ERA.EvnReanimatAction_MethodCode,
					case 
						when ERA.ReanimatActionType_SysNick = 'observation_saturation' then
							cast(ERA.EvnReanimatAction_ObservValue as int)
						else null
					end  as EvnReanimatAction_ObservValue,
					ERA.ReanimatCathetVeins_id, 
					ERA.CathetFixType_id, 
					ERA.EvnReanimatAction_CathetNaborName,
					case  
						when ERA.ReanimatActionType_SysNick = 'nutrition' then
							(select  NutritiousType_Name  from dbo.NutritiousType RNT  with (nolock) where RNT.NutritiousType_id = ERA.NutritiousType_id)
						when ERA.ReanimatActionType_SysNick in ('lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins') then
							(select UslugaComplex_Name from UslugaComplex with (nolock) where UslugaComplex_id = ERA.UslugaComplex_id)
						else null
					end  as EvnReanimatAction_MethodName,
					case
						when ERA.ReanimatActionType_SysNick in ('nutrition', 'lung_ventilation','hemodialysis','endocranial_sensor')  then
							null
						when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation')  then
							RD.ReanimDrugType_Name
						else ''
					end as  EvnReanimatAction_Medicoment,
					EU.PayType_id,
					ERA.EvnReanimatAction_MethodTxt,
					ERA.EvnReanimatAction_NutritVol,
					ERA.EvnReanimatAction_NutritEnerg,
					ERA.MilkMix_id
			from dbo.v_EvnReanimatAction ERA with (nolock)
			left join  dbo.EvnUsluga EU  with (nolock) on EU.EvnUsluga_id = ERA.EvnUsluga_id
			left join dbo.ReanimDrugType RD  with (nolock) on RD.ReanimDrugType_id = ERA.ReanimDrugType_id
				where ERA.EvnReanimatAction_pid = :EvnReanimatAction_pid
			order by ERA.EvnReanimatAction_id desc			";
		$result = $this->db->query($query, array('EvnReanimatAction_pid' => $data['EvnReanimatAction_pid']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	
	}

	/**
	 * BOB - 03.07.2017
	 * загрузка данных реанимационных мероприятий для ЭМК
	 */	
	function loudEvnReanimatActionEMK($data) {	
		//BOB - 07.02.2018
		$query = "
			select	ERA.EvnReanimatAction_id, 
					ERA.EvnReanimatAction_pid, 
					convert(varchar(10), ERA.EvnReanimatAction_setDate  ,104) as EvnReanimatAction_setDate,
					ERA.EvnReanimatAction_setTime,
					convert(varchar(10), ERA.EvnReanimatAction_disDate  ,104) as EvnReanimatAction_disDate,
					ERA.EvnReanimatAction_disTime,
				--	ERA.ReanimatActionType_id, 
				--	ERA.ReanimatActionType_SysNick,
					ERA.ReanimatActionType_Name, 
				--	ERA.UslugaComplex_id,
				--	ERA.EvnUsluga_id,
				--	ERA.ReanimDrugType_id,
				--	ERA.EvnReanimatAction_DrugDose,
				--	ERA.EvnDrug_id,
				--	ERA.EvnReanimatAction_MethodCode,
				--	case 
				--		when ERA.ReanimatActionType_SysNick = 'observation_saturation' then
				--			cast(cast(ERA.EvnReanimatAction_ObservValue as int) as varchar) + '&nbsp%&nbsp'
				--		else null
				--	end  as EvnReanimatAction_ObservValue,
					case
						when ERA.ReanimatActionType_SysNick = 'observation_saturation' then
							(select top 1  cast(cast(RateSPO2_Value as int) as varchar) + '&nbsp%&nbsp' from dbo.v_RateSPO2 with(nolock)
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateSPO2_setDT desc, RateSPO2_id desc)
						when ERA.ReanimatActionType_SysNick = 'invasive_hemodynamics' then
							(select top 1  cast(cast(RateHemodynam_Value as int) as varchar) from dbo.v_RateHemodynam with(nolock)
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateHemodynam_setDT desc, RateHemodynam_id desc)
						when ERA.ReanimatActionType_SysNick = 'endocranial_sensor' then
							(select top 1  cast(cast(RateVCHD_Value as int) as varchar) from dbo.v_RateVCHD with(nolock)
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateVCHD_setDT desc, RateVCHD_id desc)
					end as EvnReanimatAction_ObservValue,
				--	ERA.ReanimatCathetVeins_id,
				--	ERA.CathetFixType_id,
				--	ERA.EvnReanimatAction_CathetNaborName,
					case  
						when ERA.ReanimatActionType_SysNick = 'nutrition' then
							(select  NutritiousType_Name  from dbo.NutritiousType RNT  with (nolock) where RNT.NutritiousType_id = ERA.NutritiousType_id) +
							isnull(': ' + ERA.EvnReanimatAction_MethodTxt, '') + ' ' +
							isnull(' объём - ' + cast(ERA.EvnReanimatAction_NutritVol as varchar(10)), '') +
							isnull(' энергетическая ценность - ' + cast(ERA.EvnReanimatAction_NutritEnerg as varchar(10)), '') + '<br>'
						when ERA.ReanimatActionType_SysNick in ('lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins') then
							(select UslugaComplex_Name from UslugaComplex with (nolock) where UslugaComplex_id = ERA.UslugaComplex_id) + '<br>'
						else null
					end  as EvnReanimatAction_MethodName,
					case
						when ERA.ReanimatActionType_SysNick in ('nutrition','lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins') then
							'Метод &nbsp'
						else
							''
					end as EvnReanimatActionMethod_Field,
					case
						when ERA.ReanimatActionType_SysNick in ('nutrition', 'lung_ventilation','hemodialysis','endocranial_sensor')  then
							null
						when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation')  then
							RD.ReanimDrugType_Name
						else ''
					end as  EvnReanimatAction_Medicoment,
					case
						when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation')  then
							'Медикамент &nbsp'
						else ''
					end as  EvnReanimatAction_MedicomentField,
					case
						when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation')  then
							'&nbsp Дозировка &nbsp'
						else ''
					end as  EvnReanimatAction_DrugDoseField,
					case
						when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation')  then
							cast(cast(ERA.EvnReanimatAction_DrugDose as int) as varchar) + ' ' + ERA.EvnReanimatAction_DrugUnit  + '<br>'
						else ''
					end as  EvnReanimatAction_DrugDose,
				--	EU.PayType_id,
					case
						when ERA.ReanimatActionType_SysNick in ('catheterization_veins','epidural_analgesia','endocranial_sensor','hemodialysis','lung_ventilation') then
							(select top 1 PT.PayType_Name from dbo.PayType PT with (nolock)  where PT.Region_id is null and PT.PayType_id = EU.PayType_id) + '<br>'
						else 
							null
					end as PayType_name,
					case
						when ERA.ReanimatActionType_SysNick in ('catheterization_veins','epidural_analgesia','endocranial_sensor','hemodialysis','lung_ventilation') then
							'Тип оплаты &nbsp'
						else 
							null
					end as PayType_Field,
					case
						when ERA.ReanimatActionType_SysNick = 'catheterization_veins' then
						'<strong>Вена &nbsp</strong> ' + (select ReanimatCathetVeins_NameI from dbo.ReanimatCathetVeins RCV with (nolock)  where ReanimatCathetVeins_id = ERA.ReanimatCathetVeins_id ) + 
						'<strong>&nbsp фиксация &nbsp</strong> ' + (select CathetFixType_Name from CathetFixType with (nolock)  where CathetFixType_id = ERA.CathetFixType_id ) + 
						'<strong>&nbsp набор &nbsp</strong> ' + ERA.EvnReanimatAction_CathetNaborName + '<br>'
						else
							null
					end as ReanimatCathetVeins,
					case
						when ERA.ReanimatActionType_SysNick in ('invasive_hemodynamics','observation_saturation','card_pulm') then
							null
						else 
							'<span class=\"link\" id=\"EvnReanimatAction_' + cast(EvnReanimatAction_id as varchar) +'_toggleDisplay\">Показать</span> '
					end as Pokazat

					
			from dbo.v_EvnReanimatAction ERA with (nolock)
			left join  dbo.EvnUsluga EU  with (nolock) on EU.EvnUsluga_id = ERA.EvnUsluga_id
			left join dbo.ReanimDrugType RD  with (nolock) on RD.ReanimDrugType_id = ERA.ReanimDrugType_id
				where ERA.EvnReanimatAction_pid = :EvnReanimatAction_pid
			order by ERA.EvnReanimatAction_setDT desc			
		";
		$result = $this->db->query($query, array('EvnReanimatAction_pid' => $data['EvnReanimatAction_pid']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	
	}
	
	/**
	 * BOB - 21.05.2018
	 * удаление записи мероприятия
	 */
	function EvnReanimatAction_Del($data) {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$queryParams = array('EvnReanimatAction_id' => $data['EvnReanimatAction_id'],
							  'pmUser_id' => $pmUser_id);
		
		
		$query = "		
			select EvnUsluga_id, EvnDrug_id from v_EvnReanimatAction with (nolock)
			 where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $queryParams));
		
		if ( !is_object($result) )
			return false;

		$EvnScaleResult = $result->result('array');
		
		//если не найдена запись мероприятия
		if (count($EvnScaleResult) == 0){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = 'Запись реанимационного мероприятия не найдена';
			return $Response;
		}
		
		//если код услуги непустой, то - удаление услуги
		if ($EvnScaleResult[0]['EvnUsluga_id'] != null){
			$queryParams['EvnUsluga_id'] = $EvnScaleResult[0]['EvnUsluga_id'];
			$query = "		
				declare

				 @EvnUsluga_id bigint = :EvnUsluga_id,
				 @pmUser_id bigint = :pmUser_id,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null; 

				exec dbo.p_EvnUsluga_del
				 @EvnUsluga_id,
				 @pmUser_id,
				 @Error_Code output,
				 @Error_Message output;
 
				select @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			$result = $this->db->query($query, $queryParams);
			//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $queryParams));			
		}
		
		// //если код медикамента непустой, то - удаление медикамента
		// if ($EvnScaleResult[0]['EvnDrug_id'] != null){
		// 	$queryParams['EvnDrug_id'] = $EvnScaleResult[0]['EvnDrug_id'];
		// 	$query = "		
		// 		declare

		// 		 @EvnDrug_id bigint = :EvnDrug_id,
		// 		 @pmUser_id bigint = :pmUser_id,
		// 		 @Error_Code int = null,
		// 		 @Error_Message varchar(4000) = null; 

		// 		exec dbo.p_EvnDrug_del
		// 		 @EvnDrug_id,
		// 		 @pmUser_id,
		// 		 @Error_Code output,
		// 		 @Error_Message output;
 
		// 		select @Error_Code as Error_Code, @Error_Message as Error_Message;		
		// 	";
		// 	$result = $this->db->query($query, $queryParams);
		// 	//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $queryParams));			
		// }

		//удаление записей ЛС //BOB - 05.03.2020
		$query = "		
			declare @RateCur CURSOR;
			declare 
				@ReanimDrug_id bigint,
				@EvnDrug_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set @RateCur = cursor scroll for
			select ReanimDrug_id, EvnDrug_id from dbo.v_ReanimDrug  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id;

			open @RateCur;
			fetch next from @RateCur into @ReanimDrug_id, @EvnDrug_id;

			while @@FETCH_STATUS = 0 begin

				exec dbo.p_ReanimDrug_del
					@ReanimDrug_id =  @ReanimDrug_id,
					@pmUser_id = :pmUser_id,
					@IsRemove = 1,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				exec dbo.p_EvnDrug_del
					@EvnDrug_id = @EvnDrug_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				fetch next from @RateCur into @ReanimDrug_id, @EvnDrug_id;
			end
			close @RateCur;
			
			select @Error_Code as Error_Code, @Error_Message as Error_Message  ;
		";
		$result = $this->db->query($query, $queryParams);

		
		//BOB - 03.11.2018
		//удаление параметров ИВЛ
		$query = "		
			declare 
			 @IVLParameter_id bigint = null,
			 @Error_Code int = null,
			 @Error_Message varchar(4000) = null;


			select @IVLParameter_id = IVLParameter_id from IVLParameter  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id

			exec dbo.p_IVLParameter_del
			 @IVLParameter_id = @IVLParameter_id,
			 @pmUser_id = :pmUser_id ,
			 @IsRemove = 1,
			 @Error_Code = @Error_Code output,
			 @Error_Message = @Error_Message output;

			 select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model p_IVLParameter_del query: ', getDebugSql($query, $queryParams));	
		
		//BOB - 03.11.2018
		//удаление параметров сердечно-лёгочная реанимация
		$query = "		
			declare 
			 @ReanimatCardPulm_id bigint = null,
			 @Error_Code int = null,
			 @Error_Message varchar(4000) = null;


			select @ReanimatCardPulm_id = ReanimatCardPulm_id from ReanimatCardPulm  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id

			exec dbo.p_ReanimatCardPulm_del
			 @ReanimatCardPulm_id = @ReanimatCardPulm_id,
			 @pmUser_id = :pmUser_id ,
			 @IsRemove = 1,
			 @Error_Code = @Error_Code output,
			 @Error_Message = @Error_Message output;

			 select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model p_ReanimatCardPulm_del query: ', getDebugSql($query, $queryParams));	
		
		if ( !is_object($result) )
			return false;
		$EvnScaleResult = $result->result('array');
		
		//удаление измерений
		$query = "		
			declare @RateCur CURSOR;
			declare 
				@RateVCHD_id bigint,
				@RateSPO2_id bigint,
				@RateHemodynam_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set @RateCur = cursor scroll for
			select RateVCHD_id from dbo.v_RateVCHD  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id;

			open @RateCur;
			fetch next from @RateCur into @RateVCHD_id;

			while @@FETCH_STATUS = 0 begin
				exec dbo.p_RateVCHD_del
				 @RateVCHD_id = @RateVCHD_id,
				 @pmUser_id = :pmUser_id,
				 @IsRemove = 1,
				 @Error_Code  = @Error_Code output,
				 @Error_Message = @Error_Message output;

				 fetch next from @RateCur into @RateVCHD_id;
			end
			close @RateCur;
			
			set @RateCur = cursor scroll for
			select RateSPO2_id from dbo.v_RateSPO2  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id;

			open @RateCur;
			fetch next from @RateCur into @RateSPO2_id;

			while @@FETCH_STATUS = 0 begin
				exec dbo.p_RateSPO2_del
				 @RateSPO2_id = @RateSPO2_id,
				 @pmUser_id = :pmUser_id,
				 @IsRemove = 1,
				 @Error_Code  = @Error_Code output,
				 @Error_Message = @Error_Message output;

				 fetch next from @RateCur into @RateSPO2_id;
			end
			close @RateCur;

			set @RateCur = cursor scroll for
			select RateHemodynam_id from dbo.v_RateHemodynam  with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id;

			open @RateCur;
			fetch next from @RateCur into @RateHemodynam_id;

			while @@FETCH_STATUS = 0 begin
				exec dbo.p_RateHemodynam_del
				 @RateHemodynam_id = @RateHemodynam_id,
				 @pmUser_id = :pmUser_id,
				 @IsRemove = 1,
				 @Error_Code  = @Error_Code output,
				 @Error_Message = @Error_Message output;

				 fetch next from @RateCur into @RateHemodynam_id;
			end
			close @RateCur;


			select @Error_Code as Error_Code, @Error_Message as Error_Message  ;
		";
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model p_IVLParameter_del query: ', getDebugSql($query, $queryParams));	

		if (($EvnScaleResult[0]['Error_Code'] != null) || ($EvnScaleResult[0]['Error_Message'] != null)){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
			//BOB - 03.11.2018
		} else {
			//удаление мероприятия
			$query = "
				declare

				 @EvnReanimatAction_id bigint = :EvnReanimatAction_id,
				 @pmUser_id bigint = :pmUser_id,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null; 

				exec dbo.p_EvnReanimatAction_del
				 @EvnReanimatAction_id,
				 @pmUser_id,
				 @Error_Code output,
				 @Error_Message output;

				select @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";

			$result = $this->db->query($query, $queryParams);
			//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $queryParams));

			if ( !is_object($result) )
				return false;

			$EvnScaleResult = $result->result('array');

			if (($EvnScaleResult[0]['Error_Code'] != null) || ($EvnScaleResult[0]['Error_Message'] != null)){
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
			}
		}
		return $Response;
	}

	
	
	/**
	 * BOB - 10.07.2017    	 * 
	 * Сохранение в БД данных конкретного реанимационного мероприятия - 
	 * BOB - 04.07.2019  - добавляю возможность редактирования
	 */
	function EvnReanimatAction_Save($data) {
		
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$EvnUslugaCommonResult = null;  // массив - результат получаемый при создании записи услуги
		$EvnUsluga_id = null;  // id услуги извлекаемый из $EvnUslugaCommonResult
		$DrugNameResult = null;  // массив - результат получаемый при создании записи медикамента
		$EvnDrug_id = null;  // id медикамента извлекаемый из $DrugNameResult
		
		$EvnReanimatAction_id_Rate = null; //BOB - 03.11.2018 код записи мероприятия для работы с измерениями
		
		//BOB - 12.09.2018
		$data['EvnReanimatAction_setDate'] .= ' '.$data['EvnReanimatAction_setTime'].':00';
		if(($data['EvnReanimatAction_disDate'] == '') || $data['EvnReanimatAction_disTime'] == ''){
			$data['EvnReanimatAction_disDate'] = null;
		}
		else {
			$data['EvnReanimatAction_disDate'] =$data['EvnReanimatAction_disDate']." ".$data['EvnReanimatAction_disTime'].":00";
		}

		$params = array(
						'EvnReanimatAction_id' => $data['EvnReanimatAction_id'],
						'ReanimatActionType_SysNick' => $data['ReanimatActionType_SysNick'],
						'EvnReanimatAction_pid' => isset($data['EvnReanimatAction_pid']) ? $data['EvnReanimatAction_pid'] : null,
						'EvnSection_id' => isset($data['EvnSection_id']) ? $data['EvnSection_id'] : null,   //BOB - 02.09.2018
						'EvnReanimatAction_setDT' => isset($data['EvnReanimatAction_setDate']) ? $data['EvnReanimatAction_setDate'] : null,
						'EvnReanimatAction_disDT' => isset($data['EvnReanimatAction_disDate']) ? $data['EvnReanimatAction_disDate'] : null,
						'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null, 
					    'Server_id' => isset($data['Server_id']) ? $data['Server_id'] : null,
						'PersonEvn_id' => isset($data['PersonEvn_id']) ? $data['PersonEvn_id'] : null,
						'pmUser_id' => isset($data['pmUser_id']) ? $data['pmUser_id'] : null,
					    'ReanimatActionType_id' => isset($data['ReanimatActionType_id']) ? $data['ReanimatActionType_id'] : null,
					    'UslugaComplex_id' => $data['ReanimatActionType_id'] != 3 ? (isset($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null) : null,
					    'NutritiousType_id' => $data['ReanimatActionType_id'] == 3 ? (isset($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null) : null,
					    'ReanimDrugType_id' => isset($data['ReanimDrugType_id']) ? $data['ReanimDrugType_id'] : null,
						'EvnReanimatAction_DrugDose' => isset($data['EvnReanimatAction_DrugDose']) ? $data['EvnReanimatAction_DrugDose'] : null,
					    'EvnReanimatAction_DrugUnit' => isset($data['EvnReanimatAction_DrugUnit']) ? $data['EvnReanimatAction_DrugUnit'] : null,
						'ReanimDrug' => isset($data['ReanimDrug']) ? json_decode($data['ReanimDrug'], true)	: null, //BOB - 05.03.2020
					    'EvnReanimatAction_MethodCode' => isset($data['EvnReanimatAction_MethodCode']) ? $data['EvnReanimatAction_MethodCode'] : null,
					    'EvnReanimatAction_ObservValue' => isset($data['EvnReanimatAction_ObservValue']) ? $data['EvnReanimatAction_ObservValue'] : null,
					    'ReanimatCathetVeins_id' => isset($data['ReanimatCathetVeins_id']) ? $data['ReanimatCathetVeins_id'] : null,
					    'CathetFixType_id' => isset($data['CathetFixType_id']) ? $data['CathetFixType_id'] : null,
					    'EvnReanimatAction_CathetNaborName' => isset($data['EvnReanimatAction_CathetNaborName']) ? $data['EvnReanimatAction_CathetNaborName'] : null,
						'LpuSection_id' => isset($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
						'IVLParameter' => isset($data['IVLParameter']) ? json_decode($data['IVLParameter'], true)	: null,
						'CardPulm' => isset($data['CardPulm']) ? json_decode($data['CardPulm'], true)	: null,
						'Rate_List' => isset($data['Rate_List']) ? json_decode($data['Rate_List'], true)	: null,
						'EvnReanimatAction_MethodTxt' => isset($data['EvnReanimatAction_MethodTxt']) ? $data['EvnReanimatAction_MethodTxt']	: null,  //BOB - 03.11.2018  метод - вариант пользователя
						'EvnReanimatAction_NutritVol' => isset($data['EvnReanimatAction_NutritVol']) ? $data['EvnReanimatAction_NutritVol']	: null,  //BOB - 03.11.2018  объём питания
						'EvnReanimatAction_NutritEnerg' => isset($data['EvnReanimatAction_NutritEnerg']) ? $data['EvnReanimatAction_NutritEnerg']	: null, //BOB - 03.11.2018  энеогия питания
						'EvnUsluga_id' => isset($data['EvnUsluga_id']) ? $data['EvnUsluga_id']	: null, //BOB - 04.07.2019  
						'EvnDrug_id' => isset($data['EvnDrug_id']) ? $data['EvnDrug_id']	: null, //BOB - 04.07.2019 
						'MilkMix_id' => isset($data['MilkMix_id']) ? $data['MilkMix_id']	: null, //BOB - 15.04.2020 

						
						);
		//BOB - 12.09.2018
		//log_message('debug', 'BOB_$params1 = '.print_r($params, 1));
			//echo '<pre>' . print_r($params, 1) . '</pre>';  
	
		//если новая запись!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!	
		$NewEvnReanimatAction = $params['EvnReanimatAction_id'] == 'New_GUID_Id';
		//log_message('debug', 'BOB_$NewEvnReanimatAction = '.print_r($NewEvnReanimatAction, 1));
		
		// если типы мероприятий, содержащие услуги
		if (in_array($params['ReanimatActionType_SysNick'],array('lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins'))) {				

			//BOB - 12.09.2018
			$params['MedPersonal_id'] = isset($data['MedPersonal_id']) ? $data['MedPersonal_id'] : null;
			$params['MedStaffFact_id'] = isset($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null;
			$params['PayType_id'] = isset($data['PayType_id']) ? $data['PayType_id'] : null;     //BOB - 21.03.2018
			$params['Diag_id'] = isset($data['Diag_id']) ? $data['Diag_id'] : null;
			
			
			//BOB - 02.09.2018
			$params['EvnUslugaCommon_disDT'] = isset($params['EvnReanimatAction_disDT']) ? $params['EvnReanimatAction_disDT'] : null;
			if (in_array($params['ReanimatActionType_SysNick'],array('epidural_analgesia','catheterization_veins')))
					$params['EvnUslugaCommon_disDT'] = $params['EvnReanimatAction_setDT'];

			//BOB - 18.01.2020
			$query = "
				select case when count(*) = 0 then 'Common' else 'Oper' end  UslType
				 from UslugaComplexAttribute UCA   with (nolock)
				inner join UslugaComplexAttributeType UCAT   with (nolock) on UCA.UslugaComplexAttributeType_id = UCAT.UslugaComplexAttributeType_id
				 where UCA.UslugaComplex_id = :UslugaComplex_id
				   and UslugaComplexAttributeType_SysNick = 'oper'
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $params));
			if ( !is_object($result) )
				return false;

			$UslTypeResult = $result->result('array');
			$UslType = $UslTypeResult[0]['UslType'];

			//если старая запись - сначалап удаляю EvnUslugaCommon
			if (!$NewEvnReanimatAction) {
				$query = "		
					declare

					 @EvnUsluga_id bigint = :EvnUsluga_id,
					 @pmUser_id bigint = :pmUser_id,
					 @Error_Code int = null,
					 @Error_Message varchar(4000) = null; 

					exec dbo.p_EvnUsluga".$UslType."_del
					 @EvnUsluga_id,
					 @pmUser_id,
					 @Error_Code output,
					 @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Message;		
				";
				$result = $this->db->query($query, $params);
				//sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $params));
				if ( !is_object($result) )
					return false;
			}
			
			$query = "
				declare
					@Res bigint  = null ,
					@UslugaComplexTariff_id bigint = null,
					@LpuSection_id bigint = null,
					@LpuSectionProfile_id bigint = null,
					@ErrCode int,
					@ErrMessage varchar(4000);

				select @UslugaComplexTariff_id =   uct.UslugaComplexTariff_id 
				  from dbo.UslugaComplexTariff UCT   with (nolock)
				 where UCT.UslugaComplex_id = :UslugaComplex_id
				   and UCT.UslugaComplexTariff_begDate <= :EvnReanimatAction_setDT
				   and (UCT.UslugaComplexTariff_endDate >=  :EvnReanimatAction_setDT or  UCT.UslugaComplexTariff_endDate is null)

				select @LpuSection_id = isnull(MS.LpuSection_id, :LpuSection_id), @LpuSectionProfile_id = LS.LpuSectionProfile_id
				   from EvnReanimatPeriod ERP   with (nolock)
				   inner join MedService MS   with (nolock) on MS.MedService_id = ERP.MedService_id
				   inner join LpuSection LS with (nolock) on LS.LpuSection_id = isnull(MS.LpuSection_id, :LpuSection_id)
				 where EvnReanimatPeriod_id = :EvnReanimatAction_pid

				exec p_EvnUsluga".$UslType."_ins
					@EvnUsluga".$UslType."_id = @Res output,
					@EvnUsluga".$UslType."_pid = :EvnSection_id,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnUsluga".$UslType."_setDT = :EvnReanimatAction_setDT,
					@EvnUsluga".$UslType."_disDT = :EvnUslugaCommon_disDT,

					@MedPersonal_id = :MedPersonal_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@LpuSection_uid = @LpuSection_id,
					@LpuSectionProfile_id = @LpuSectionProfile_id,

					@UslugaComplex_id = :UslugaComplex_id,
					@UslugaPlace_id = 1,
					@UslugaComplexTariff_id = @UslugaComplexTariff_id,
					@PayType_id = :PayType_id,
					@EvnUsluga".$UslType."_Kolvo = 1,

					@DiagSetClass_id = 1,
					@Diag_id = :Diag_id,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;


				select @Res as EvnUsluga".$UslType."_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnUsluga'.$UslType.'_ins exec query: ', getDebugSql($query, $params));

			if ( !is_object($result) )
				return false;

			$EvnUslugaCommonResult = $result->result('array');
			//log_message('debug', '$EvnUsluga'.$UslType.'Result:  '.print_r($EvnUslugaCommonResult, 1));

			if (($EvnUslugaCommonResult[0]['EvnUsluga'.$UslType.'_id'] == null) || ($EvnUslugaCommonResult[0]['Error_Code'])|| ($EvnUslugaCommonResult[0]['Error_Msg'])){
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $EvnUslugaCommonResult[0]['Error_Code'].' '.$EvnUslugaCommonResult[0]['Error_Msg'];
				return $Response;			
			}
			$EvnUsluga_id = ($EvnUslugaCommonResult != null) ? $EvnUslugaCommonResult[0]['EvnUsluga'.$UslType.'_id'] : null;
			//BOB - 18.01.2020
		}

		//BOB - 12.09.2018
		$params['EvnUsluga_id'] = isset($EvnUsluga_id) ? $EvnUsluga_id : null;
		$params['EvnDrug_id'] =  isset($EvnDrug_id) ? $EvnDrug_id : null;  //BOB - 05.03.2020 !!!!!!!!!!!!!!!!!!!!!!!!!!
		//	echo '<pre>' .'-2- '. print_r($params, 1) . '</pre>'; 

		
		//если новая запись
		if ($NewEvnReanimatAction) {

			$query = "
					declare
						 @EvnReanimatAction_id bigint = null,
						 @Error_Code int = null,
						 @Error_Message varchar(4000) = null;

					exec   dbo.p_EvnReanimatAction_ins
						 @EvnReanimatAction_id  = @EvnReanimatAction_id output,
						 @EvnReanimatAction_pid = :EvnReanimatAction_pid  , 
						 @Lpu_id = :Lpu_id, 
						 @Server_id = :Server_id, 

						 @ReanimatActionType_id = :ReanimatActionType_id,
						 @UslugaComplex_id = :UslugaComplex_id,
						 @EvnUsluga_id = :EvnUsluga_id,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @EvnReanimatAction_DrugDose = :EvnReanimatAction_DrugDose,
						 @EvnDrug_id = :EvnDrug_id,
						 @EvnReanimatAction_MethodCode = :EvnReanimatAction_MethodCode,
						 @EvnReanimatAction_ObservValue = :EvnReanimatAction_ObservValue,
						 @ReanimatCathetVeins_id = :ReanimatCathetVeins_id,
						 @CathetFixType_id = :CathetFixType_id,
						 @EvnReanimatAction_CathetNaborName = :EvnReanimatAction_CathetNaborName,
						 @NutritiousType_id = :NutritiousType_id,
						 @EvnReanimatAction_DrugUnit = :EvnReanimatAction_DrugUnit,						 
						 @EvnReanimatAction_MethodTxt = :EvnReanimatAction_MethodTxt,
						 @EvnReanimatAction_NutritVol = :EvnReanimatAction_NutritVol,  
						 @EvnReanimatAction_NutritEnerg = :EvnReanimatAction_NutritEnerg,
						 @MilkMix_id = :MilkMix_id,

						 @PersonEvn_id = :PersonEvn_id, 
						 @EvnReanimatAction_setDT = :EvnReanimatAction_setDT, 
						 @EvnReanimatAction_disDT = :EvnReanimatAction_disDT,

						 @pmUser_id = :pmUser_id,
						 @Error_Code = @Error_Code  output,
						 @Error_Message = @Error_Message output;


					select @EvnReanimatAction_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
				";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnReanimatAction_ins exec query: ', getDebugSql($query, $params));

			if ( !is_object($result) )
				return false;

			$EvnScaleResult = $result->result('array');
			//log_message('debug', 'BOB_0'.print_r($EvnScaleResult, 1));
		} else {    // существующая запись - для сохранения даты окончания
			
			$query = "
				declare

					@EvnReanimatAction_id bigint = :EvnReanimatAction_id, 
					@Error_Code int = null,
					@Error_Message varchar(4000) = null; 

				exec   dbo.p_EvnReanimatAction_upd
					@EvnReanimatAction_id  = @EvnReanimatAction_id,
					@EvnReanimatAction_pid  = :EvnReanimatAction_pid  , 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id, 
					
						 @ReanimatActionType_id = :ReanimatActionType_id,
						 @UslugaComplex_id = :UslugaComplex_id,
						 @EvnUsluga_id = :EvnUsluga_id,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @EvnReanimatAction_DrugDose = :EvnReanimatAction_DrugDose,
						 @EvnDrug_id = :EvnDrug_id,
						 @EvnReanimatAction_MethodCode = :EvnReanimatAction_MethodCode,
						 @EvnReanimatAction_ObservValue = :EvnReanimatAction_ObservValue,
						 @ReanimatCathetVeins_id = :ReanimatCathetVeins_id,
						 @CathetFixType_id = :CathetFixType_id,
						 @EvnReanimatAction_CathetNaborName = :EvnReanimatAction_CathetNaborName,
						 @NutritiousType_id = :NutritiousType_id,
						 @EvnReanimatAction_DrugUnit = :EvnReanimatAction_DrugUnit,						 
						 @EvnReanimatAction_MethodTxt = :EvnReanimatAction_MethodTxt,
						 @EvnReanimatAction_NutritVol = :EvnReanimatAction_NutritVol,  
						 @EvnReanimatAction_NutritEnerg = :EvnReanimatAction_NutritEnerg,
						 @MilkMix_id = :MilkMix_id,

						 @EvnReanimatAction_setDT = :EvnReanimatAction_setDT, 
					@EvnReanimatAction_disDT = :EvnReanimatAction_disDT,
					
					@pmUser_id = :pmUser_id, 
					@Error_Code  = @Error_Code output,
					@Error_Message = @Error_Message output ;

				select @EvnReanimatAction_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $params));

			if ( !is_object($result) )
				return false;

			$EvnScaleResult = $result->result('array');
			
		}
		
		if (($EvnScaleResult[0]['EvnReanimatAction_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null)){	

			$EvnReanimatAction_id_Rate = $EvnScaleResult[0]['EvnReanimatAction_id']; 

			//если нутритивная поддержка - закрываем предыдущую нутритивную поддержку
			if (in_array($params['ReanimatActionType_SysNick'],array('nutrition'))) {	

				//BOB - 12.09.2018
				$params['EvnReanimatAction_id_Prev'] = $EvnScaleResult[0]['EvnReanimatAction_id'];
				$query = "
					select ERA.EvnReanimatAction_id, ERA.EvnReanimatAction_setDT, ERA.EvnReanimatAction_disDT as ReanimatNutritionType_Name  from 
					v_EvnReanimatAction ERA with (nolock)
					 where EvnReanimatAction_pid = :EvnReanimatAction_pid
					   and EvnReanimatAction_id <> :EvnReanimatAction_id_Prev
					   and ReanimatActionType_SysNick = :ReanimatActionType_SysNick
					   and ERA.EvnReanimatAction_setDT <= :EvnReanimatAction_setDT
					   and ERA.EvnReanimatAction_disDT is null
					 order by EvnReanimatAction_setDT desc	
				";
				$result = $this->db->query($query, $params);
				sql_log_message('error', 'from v_EvnReanimatAction exec query: ', getDebugSql($query, $params));
				//BOB - 12.09.2018


				if ( !is_object($result) )
					return false;

				$EvnScaleResult = $result->result('array');
				//log_message('debug', 'BOB_1'.print_r($EvnScaleResult, 1));
				if (sizeof($EvnScaleResult) > 0) {
					$params['EvnReanimatAction_id_Prev'] = $EvnScaleResult[0]['EvnReanimatAction_id'];					
					//BOB - 12.09.2019
					$query = "
							update Evn set
							Evn_disDT = :EvnReanimatAction_setDT,
							Evn_updDT = GetDate(),
							pmUser_updID = :pmUser_id
							where Evn_id = :EvnReanimatAction_id_Prev;
					";
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $params));
					// запрос для формирования $EvnScaleResult - для единообразия алгоритмов - используется в конце метода
					$query = "
							declare
								@EvnReanimatAction_id bigint = :EvnReanimatAction_id_Prev,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;

							select @EvnReanimatAction_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
					";
					
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $params));

					if ( !is_object($result) )
						return false;

					$EvnScaleResult = $result->result('array');
					//log_message('debug', 'BOB_2'.print_r($EvnScaleResult, 1));
				}
			} //если нутритивная поддержка - закрываем предыдущую нутритивную поддержку

			//если ИВЛ - сохраняю параметры
			if (in_array($params['ReanimatActionType_SysNick'],array('lung_ventilation')) && (isset($params['IVLParameter']))  ) {	

				$params['IVLParameter_id'] = isset($params['IVLParameter']['IVLParameter_id']) ? $params['IVLParameter']['IVLParameter_id'] : null;
				$params['EvnReanimatAction_id'] = $EvnReanimatAction_id_Rate;
				//$params['EvnReanimatAction_id'] = $EvnScaleResult[0]['EvnReanimatAction_id'];

				$params['IVLParameter_Apparat'] = $params['IVLParameter']['IVLParameter_Apparat'];
				$params['IVLRegim_id'] = $params['IVLParameter']['IVLRegim_id'];
				$params['IVLParameter_TubeDiam'] = $params['IVLParameter']['IVLParameter_TubeDiam'];
				$params['IVLParameter_FiO2'] = $params['IVLParameter']['IVLParameter_FiO2'];
				$params['IVLParameter_PcentMinVol'] = $params['IVLParameter']['IVLParameter_PcentMinVol'];
				$params['IVLParameter_TwoASVMax'] = $params['IVLParameter']['IVLParameter_TwoASVMax'];
				$params['IVLParameter_FrequSet'] = $params['IVLParameter']['IVLParameter_FrequSet'];
				$params['IVLParameter_VolInsp'] = $params['IVLParameter']['IVLParameter_VolInsp'];
				$params['IVLParameter_PressInsp'] = $params['IVLParameter']['IVLParameter_PressInsp'];
				$params['IVLParameter_PressSupp'] = $params['IVLParameter']['IVLParameter_PressSupp'];
				$params['IVLParameter_FrequTotal'] = $params['IVLParameter']['IVLParameter_FrequTotal'];
				$params['IVLParameter_VolTe'] = $params['IVLParameter']['IVLParameter_VolTe'];
				$params['IVLParameter_VolE'] = $params['IVLParameter']['IVLParameter_VolE'];
				$params['IVLParameter_TinTet'] = $params['IVLParameter']['IVLParameter_TinTet'];
				$params['IVLParameter_VolTrig'] = $params['IVLParameter']['IVLParameter_VolTrig'];
				$params['IVLParameter_PressTrig'] = $params['IVLParameter']['IVLParameter_PressTrig'];
				$params['IVLParameter_PEEP'] = $params['IVLParameter']['IVLParameter_PEEP'];
				$params['IVLParameter_VolTi'] = $params['IVLParameter']['IVLParameter_VolTi']; //BOB - 29.02.2020	
				$params['IVLParameter_Peak'] = $params['IVLParameter']['IVLParameter_Peak']; //BOB - 29.02.2020	
				$params['IVLParameter_MAP'] = $params['IVLParameter']['IVLParameter_MAP']; //BOB - 29.02.2020	
				$params['IVLParameter_Tins'] = $params['IVLParameter']['IVLParameter_Tins']; //BOB - 29.02.2020	
				$params['IVLParameter_FlowMax'] = $params['IVLParameter']['IVLParameter_FlowMax']; //BOB - 29.02.2020	
				$params['IVLParameter_FlowMin'] = $params['IVLParameter']['IVLParameter_FlowMin']; //BOB - 29.02.2020	
				$params['IVLParameter_deltaP'] = $params['IVLParameter']['IVLParameter_deltaP']; //BOB - 29.02.2020	
				$params['IVLParameter_Other'] = $params['IVLParameter']['IVLParameter_Other']; //BOB - 29.02.2020	


				//log_message('debug', 'BOB_params_IVLParameter = '.print_r($params, 1));
				if($NewEvnReanimatAction){

					$query = "
						declare
						 @IVLParameter_id bigint = null,
						 @Error_Code int = null,
						 @Error_Message varchar(4000) = null

						exec   
						dbo.p_IVLParameter_ins
						 @IVLParameter_id = @IVLParameter_id output,
						 @EvnReanimatAction_id = :EvnReanimatAction_id,
						 @IVLParameter_Apparat = :IVLParameter_Apparat,
						 @IVLRegim_id = :IVLRegim_id,
						 @IVLParameter_TubeDiam = :IVLParameter_TubeDiam,
						 @IVLParameter_FiO2 = :IVLParameter_FiO2,
						 @IVLParameter_FrequSet = :IVLParameter_FrequSet,
						 @IVLParameter_VolInsp = :IVLParameter_VolInsp,
						 @IVLParameter_PressInsp = :IVLParameter_PressInsp,
						 @IVLParameter_PressSupp = :IVLParameter_PressSupp,
						 @IVLParameter_FrequTotal = :IVLParameter_FrequTotal,
						 @IVLParameter_VolTe = :IVLParameter_VolTe,
						 @IVLParameter_VolE = :IVLParameter_VolE,
						 @IVLParameter_TinTet = :IVLParameter_TinTet,
						 @IVLParameter_VolTrig = :IVLParameter_VolTrig,
						 @IVLParameter_PressTrig = :IVLParameter_PressTrig,
						 @IVLParameter_PEEP = :IVLParameter_PEEP,
						 @IVLParameter_PcentMinVol = :IVLParameter_PcentMinVol,
						 @IVLParameter_TwoASVMax = :IVLParameter_TwoASVMax,
						 @IVLParameter_VolTi = :IVLParameter_VolTi,
						 @IVLParameter_Peak = :IVLParameter_Peak,
						 @IVLParameter_MAP = :IVLParameter_MAP,
						 @IVLParameter_Tins = :IVLParameter_Tins,
						 @IVLParameter_FlowMax = :IVLParameter_FlowMax,
						 @IVLParameter_FlowMin = :IVLParameter_FlowMin,
						 @IVLParameter_deltaP = :IVLParameter_deltaP,
						 @IVLParameter_Other = :IVLParameter_Other,
	 
						 @pmUser_id = :pmUser_id,
						 @Error_Code = @Error_Code output,
						 @Error_Message = @Error_Message output;

						select @IVLParameter_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_IVLParameter_ins exec query: ', getDebugSql($query, $params));

					if ( !is_object($result) )
						return false;
				} else {
					$query = "
						declare
						 @IVLParameter_id bigint = :IVLParameter_id,
						 @Error_Code int = null,
						 @Error_Message varchar(4000) = null

						exec   
						dbo.p_IVLParameter_upd
						 @IVLParameter_id = @IVLParameter_id output,
						 @EvnReanimatAction_id = :EvnReanimatAction_id,
						 @IVLParameter_Apparat = :IVLParameter_Apparat,
						 @IVLRegim_id = :IVLRegim_id,
						 @IVLParameter_TubeDiam = :IVLParameter_TubeDiam,
						 @IVLParameter_FiO2 = :IVLParameter_FiO2,
						 @IVLParameter_FrequSet = :IVLParameter_FrequSet,
						 @IVLParameter_VolInsp = :IVLParameter_VolInsp,
						 @IVLParameter_PressInsp = :IVLParameter_PressInsp,
						 @IVLParameter_PressSupp = :IVLParameter_PressSupp,
						 @IVLParameter_FrequTotal = :IVLParameter_FrequTotal,
						 @IVLParameter_VolTe = :IVLParameter_VolTe,
						 @IVLParameter_VolE = :IVLParameter_VolE,
						 @IVLParameter_TinTet = :IVLParameter_TinTet,
						 @IVLParameter_VolTrig = :IVLParameter_VolTrig,
						 @IVLParameter_PressTrig = :IVLParameter_PressTrig,
						 @IVLParameter_PEEP = :IVLParameter_PEEP,
						 @IVLParameter_PcentMinVol = :IVLParameter_PcentMinVol,
						 @IVLParameter_TwoASVMax = :IVLParameter_TwoASVMax,
						 @IVLParameter_VolTi = :IVLParameter_VolTi,
						 @IVLParameter_Peak = :IVLParameter_Peak,
						 @IVLParameter_MAP = :IVLParameter_MAP,
						 @IVLParameter_Tins = :IVLParameter_Tins,
						 @IVLParameter_FlowMax = :IVLParameter_FlowMax,
						 @IVLParameter_FlowMin = :IVLParameter_FlowMin,
						 @IVLParameter_deltaP = :IVLParameter_deltaP,
						 @IVLParameter_Other = :IVLParameter_Other,

						 @pmUser_id = :pmUser_id,
						 @Error_Code = @Error_Code output,
						 @Error_Message = @Error_Message output;

						select @IVLParameter_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_IVLParameter_ins exec query: ', getDebugSql($query, $params));

					if ( !is_object($result) )
						return false;
					
				}

				$EvnScaleResult = $result->result('array');

			}


			//если сердечно-лёгочная реанимация - сохраняю параметры
			if (in_array($params['ReanimatActionType_SysNick'],array('card_pulm')) && (isset($params['CardPulm'])) ) {	

				$params['ReanimatCardPulm_id'] = isset($params['CardPulm']['ReanimatCardPulm_id']) ? $params['CardPulm']['ReanimatCardPulm_id'] : null;
				$params['EvnReanimatAction_id'] = $EvnReanimatAction_id_Rate;
				//$params['EvnReanimatAction_id'] = $EvnScaleResult[0]['EvnReanimatAction_id'];


				$params['ReanimatCardPulm_ClinicalDeath'] = substr($params['CardPulm']['ReanimatCardPulm_ClinicalDeathDate'],0,10).' '.$params['CardPulm']['ReanimatCardPulm_ClinicalDeathTime'].':00';
				if(!isset($params['CardPulm']['ReanimatCardPulm_BiologDeathDate']) || !isset($params['CardPulm']['ReanimatCardPulm_BiologDeathTime'])){
					$params['ReanimatCardPulm_BiologDeath'] = null;
				}
				else {
					$params['ReanimatCardPulm_BiologDeath'] = substr($params['CardPulm']['ReanimatCardPulm_BiologDeathDate'],0,10)." ".$params['CardPulm']['ReanimatCardPulm_BiologDeathTime'].":00";
				}


				$params['ReanimatCardPulm_IsPupilDilat'] = $params['CardPulm']['ReanimatCardPulm_IsPupilDilat'];
				$params['ReanimatCardPulm_IsCardMonitor'] = $params['CardPulm']['ReanimatCardPulm_IsCardMonitor'];
				$params['ReanimatCardPulm_StopCardActType'] = $params['CardPulm']['ReanimatCardPulm_StopCardActType'];
				$params['IVLRegim_id'] = isset($params['CardPulm']['IVLRegim_id']) ? $params['CardPulm']['IVLRegim_id'] : null;
				$params['ReanimatCardPulm_FiO2'] = isset($params['CardPulm']['ReanimatCardPulm_FiO2']) ? $params['CardPulm']['ReanimatCardPulm_FiO2'] : null;
				$params['ReanimatCardPulm_IsCardTonics'] = $params['CardPulm']['ReanimatCardPulm_IsCardTonics'];
				$params['ReanimatCardPulm_CardTonicDose'] = isset($params['CardPulm']['ReanimatCardPulm_CardTonicDose']) ? $params['CardPulm']['ReanimatCardPulm_CardTonicDose'] : null;
				$params['ReanimatCardPulm_CathetVein'] = $params['CardPulm']['ReanimatCardPulm_CathetVein'];
				$params['ReanimatCardPulm_TrachIntub'] = isset($params['CardPulm']['ReanimatCardPulm_TrachIntub']) ? $params['CardPulm']['ReanimatCardPulm_TrachIntub'] : null;
				$params['ReanimatCardPulm_Auscultatory'] = $params['CardPulm']['ReanimatCardPulm_Auscultatory'];
				$params['ReanimatCardPulm_AuscultatoryTxt'] = isset($params['CardPulm']['ReanimatCardPulm_AuscultatoryTxt']) ? $params['CardPulm']['ReanimatCardPulm_AuscultatoryTxt'] : null;
				$params['ReanimatCardPulm_CardMassage'] = isset($params['CardPulm']['ReanimatCardPulm_CardMassage']) ? $params['CardPulm']['ReanimatCardPulm_CardMassage'] : null;
				$params['ReanimatCardPulm_DefibrilCount'] = isset($params['CardPulm']['ReanimatCardPulm_DefibrilCount']) ? $params['CardPulm']['ReanimatCardPulm_DefibrilCount'] : null;
				$params['ReanimatCardPulm_DefibrilMin'] = isset($params['CardPulm']['ReanimatCardPulm_DefibrilMin']) ? $params['CardPulm']['ReanimatCardPulm_DefibrilMin'] : null;
				$params['ReanimatCardPulm_DefibrilMax'] = isset($params['CardPulm']['ReanimatCardPulm_DefibrilMax']) ? $params['CardPulm']['ReanimatCardPulm_DefibrilMax'] : null;
				$params['ReanimDrugType_id'] = isset($params['CardPulm']['ReanimDrugType_id']) ? $params['CardPulm']['ReanimDrugType_id'] : null;
				$params['ReanimatCardPulm_DrugDose'] = isset($params['CardPulm']['ReanimatCardPulm_DrugDose']) ? $params['CardPulm']['ReanimatCardPulm_DrugDose'] : null;
				$params['ReanimatCardPulm_DrugSposob'] = isset($params['CardPulm']['ReanimatCardPulm_DrugSposob']) ? $params['CardPulm']['ReanimatCardPulm_DrugSposob'] : null;
				$params['ReanimDrugType_did'] = isset($params['CardPulm']['ReanimDrugType_did']) ? $params['CardPulm']['ReanimDrugType_did'] : null;
				$params['ReanimatCardPulm_dDrugDose'] = isset($params['CardPulm']['ReanimatCardPulm_dDrugDose']) ? $params['CardPulm']['ReanimatCardPulm_dDrugDose'] : null;
				$params['ReanimatCardPulm_dDrugSposob'] = isset($params['CardPulm']['ReanimatCardPulm_dDrugSposob']) ? $params['CardPulm']['ReanimatCardPulm_dDrugSposob'] : null;
				$params['ReanimatCardPulm_DrugTxt'] = isset($params['CardPulm']['ReanimatCardPulm_DrugTxt']) ? $params['CardPulm']['ReanimatCardPulm_DrugTxt'] : null;
				$params['ReanimatCardPulm_IsEffective'] = $params['CardPulm']['ReanimatCardPulm_IsEffective'];
				$params['ReanimatCardPulm_Time'] = $params['CardPulm']['ReanimatCardPulm_Time'];
				$params['ReanimatCardPulm_DoctorTxt'] = $params['CardPulm']['ReanimatCardPulm_DoctorTxt']; 



				//log_message('debug', 'BOB_params_CardPulm = '.print_r($params, 1));
				if($NewEvnReanimatAction){

					$query = "
						declare
						 @ReanimatCardPulm_id bigint = null,
						 @Error_Code int = null,
						 @Error_Message varchar(4000) = null

						exec   
						p_ReanimatCardPulm_ins
						 @ReanimatCardPulm_id = @ReanimatCardPulm_id output,
						 @EvnReanimatAction_id = :EvnReanimatAction_id,

						 @ReanimatCardPulm_ClinicalDeath = :ReanimatCardPulm_ClinicalDeath,
						 @ReanimatCardPulm_IsPupilDilat = :ReanimatCardPulm_IsPupilDilat,
						 @ReanimatCardPulm_IsCardMonitor = :ReanimatCardPulm_IsCardMonitor,
						 @ReanimatCardPulm_StopCardActType = :ReanimatCardPulm_StopCardActType,
						 @IVLRegim_id = :IVLRegim_id,
						 @ReanimatCardPulm_FiO2 = :ReanimatCardPulm_FiO2,
						 @ReanimatCardPulm_IsCardTonics = :ReanimatCardPulm_IsCardTonics,
						 @ReanimatCardPulm_CardTonicDose = :ReanimatCardPulm_CardTonicDose,
						 @ReanimatCardPulm_CathetVein = :ReanimatCardPulm_CathetVein,
						 @ReanimatCardPulm_TrachIntub = :ReanimatCardPulm_TrachIntub,
						 @ReanimatCardPulm_Auscultatory = :ReanimatCardPulm_Auscultatory,
						 @ReanimatCardPulm_AuscultatoryTxt = :ReanimatCardPulm_AuscultatoryTxt,
						 @ReanimatCardPulm_CardMassage = :ReanimatCardPulm_CardMassage,
						 @ReanimatCardPulm_DefibrilCount = :ReanimatCardPulm_DefibrilCount,
						 @ReanimatCardPulm_DefibrilMin = :ReanimatCardPulm_DefibrilMin,
						 @ReanimatCardPulm_DefibrilMax = :ReanimatCardPulm_DefibrilMax,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @ReanimatCardPulm_DrugDose = :ReanimatCardPulm_DrugDose,
						 @ReanimatCardPulm_DrugSposob = :ReanimatCardPulm_DrugSposob,
						 @ReanimDrugType_did = :ReanimDrugType_did,
						 @ReanimatCardPulm_dDrugDose = :ReanimatCardPulm_dDrugDose,
						 @ReanimatCardPulm_dDrugSposob = :ReanimatCardPulm_dDrugSposob,
						 @ReanimatCardPulm_DrugTxt = :ReanimatCardPulm_DrugTxt,
						 @ReanimatCardPulm_IsEffective = :ReanimatCardPulm_IsEffective,
						 @ReanimatCardPulm_Time = :ReanimatCardPulm_Time,
						 @ReanimatCardPulm_BiologDeath = :ReanimatCardPulm_BiologDeath,
						 @ReanimatCardPulm_DoctorTxt = :ReanimatCardPulm_DoctorTxt,

						 @pmUser_id = :pmUser_id,
						 @Error_Code = @Error_Code output,
						 @Error_Message = @Error_Message output;

						select @ReanimatCardPulm_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_ReanimatCardPulm_ins exec query: ', getDebugSql($query, $params));

					if ( !is_object($result) )
						return false;


				} else {
					$query = "
						declare
						 @ReanimatCardPulm_id bigint = :ReanimatCardPulm_id,
						 @Error_Code int = null,
						 @Error_Message varchar(4000) = null

						exec   
						p_ReanimatCardPulm_upd
						 @ReanimatCardPulm_id = @ReanimatCardPulm_id output,
						 @EvnReanimatAction_id = :EvnReanimatAction_id,

						 @ReanimatCardPulm_ClinicalDeath = :ReanimatCardPulm_ClinicalDeath,
						 @ReanimatCardPulm_IsPupilDilat = :ReanimatCardPulm_IsPupilDilat,
						 @ReanimatCardPulm_IsCardMonitor = :ReanimatCardPulm_IsCardMonitor,
						 @ReanimatCardPulm_StopCardActType = :ReanimatCardPulm_StopCardActType,
						 @IVLRegim_id = :IVLRegim_id,
						 @ReanimatCardPulm_FiO2 = :ReanimatCardPulm_FiO2,
						 @ReanimatCardPulm_IsCardTonics = :ReanimatCardPulm_IsCardTonics,
						 @ReanimatCardPulm_CardTonicDose = :ReanimatCardPulm_CardTonicDose,
						 @ReanimatCardPulm_CathetVein = :ReanimatCardPulm_CathetVein,
						 @ReanimatCardPulm_TrachIntub = :ReanimatCardPulm_TrachIntub,
						 @ReanimatCardPulm_Auscultatory = :ReanimatCardPulm_Auscultatory,
						 @ReanimatCardPulm_AuscultatoryTxt = :ReanimatCardPulm_AuscultatoryTxt,
						 @ReanimatCardPulm_CardMassage = :ReanimatCardPulm_CardMassage,
						 @ReanimatCardPulm_DefibrilCount = :ReanimatCardPulm_DefibrilCount,
						 @ReanimatCardPulm_DefibrilMin = :ReanimatCardPulm_DefibrilMin,
						 @ReanimatCardPulm_DefibrilMax = :ReanimatCardPulm_DefibrilMax,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @ReanimatCardPulm_DrugDose = :ReanimatCardPulm_DrugDose,
						 @ReanimatCardPulm_DrugSposob = :ReanimatCardPulm_DrugSposob,
						 @ReanimDrugType_did = :ReanimDrugType_did,
						 @ReanimatCardPulm_dDrugDose = :ReanimatCardPulm_dDrugDose,
						 @ReanimatCardPulm_dDrugSposob = :ReanimatCardPulm_dDrugSposob,
						 @ReanimatCardPulm_DrugTxt = :ReanimatCardPulm_DrugTxt,
						 @ReanimatCardPulm_IsEffective = :ReanimatCardPulm_IsEffective,
						 @ReanimatCardPulm_Time = :ReanimatCardPulm_Time,
						 @ReanimatCardPulm_BiologDeath = :ReanimatCardPulm_BiologDeath,
						 @ReanimatCardPulm_DoctorTxt = :ReanimatCardPulm_DoctorTxt,

						 @pmUser_id = :pmUser_id,
						 @Error_Code = @Error_Code output,
						 @Error_Message = @Error_Message output;

						select @ReanimatCardPulm_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
					$result = $this->db->query($query, $params);
					//sql_log_message('error', 'p_ReanimatCardPulm_ins exec query: ', getDebugSql($query, $params));

					if ( !is_object($result) )
						return false;
					
				}
				$EvnScaleResult = $result->result('array');
			}

			//BOB - 03.11.2018
			//СОХРАНЕНИЕ ИЗМЕРЕНИЙ
			if (isset($EvnReanimatAction_id_Rate) && isset($params['Rate_List'])){

				$RateType = '';
				$StepsToChange = '';
				if($params['ReanimatActionType_SysNick'] == 'endocranial_sensor'){
					$RateType = 'VCHD';
					$StepsToChange = "@RateVCHD_StepsToChange = :Rate_StepsToChange,";
				}
				elseif	($params['ReanimatActionType_SysNick'] == 'invasive_hemodynamics') 
					$RateType = 'Hemodynam';
				else 
					$RateType = 'SPO2';
				//log_message('debug', 'BOB_$StepsToChange_1'.print_r($StepsToChange, 1));


				foreach ($params['Rate_List'] as $Rate) {
					$Rate['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
					$Rate['EvnReanimatAction_id'] = $EvnReanimatAction_id_Rate;
					$Rate['Rate_setDT'] = substr($Rate['Rate_setDate'],6,4).'-'.substr($Rate['Rate_setDate'],3,2).'-'.substr($Rate['Rate_setDate'],0,2).' '.$Rate['Rate_setTime'].':00';				
					//	echo '<pre>' . print_r($Rate, 1) . '</pre>'; 
					//log_message('debug', 'BOB_$Rate_1'.print_r($Rate, 1));



					switch ($Rate['Rate_RecordStatus']) {
						case 0:
							//добавление нового измерения
							$query = "
								declare
								 @Rate_id bigint = null,
								 @Error_Code int = null,
								 @Error_Message varchar(4000) = null;
								exec dbo.p_Rate".$RateType."_ins
								 @Rate".$RateType."_id = @Rate_id output,
								 @EvnReanimatAction_id = :EvnReanimatAction_id,
								 @Rate".$RateType."_Value = :Rate_Value,".$StepsToChange.
								 "@Rate".$RateType."_setDT = :Rate_setDT,
								 @pmUser_id = :pmUser_id,
								 @Error_Code = @Error_Code output,
								 @Error_Message  = @Error_Message output;

								select @Rate_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
							";
							$result = $this->db->query($query, $Rate);
							//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $Rate));
							break;
						case 2:
							//изменение измерения
							$query = "
								declare
								 @Rate_id bigint = :Rate_id,
								 @Error_Code int = null,
								 @Error_Message varchar(4000) = null;
								exec   dbo.p_Rate".$RateType."_upd
								 @Rate".$RateType."_id = @Rate_id,
								 @Rate".$RateType."_Value = :Rate_Value,".$StepsToChange.
								 "@Rate".$RateType."_setDT = :Rate_setDT,
								 @pmUser_id = :pmUser_id,
								 @Error_Code = @Error_Code output,
								 @Error_Message = @Error_Message output;

								select @Rate_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
							";
							//log_message('debug', 'BOB_$query_1'.print_r($query, 1));
							$result = $this->db->query($query, $Rate);
							//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $Rate));
							break;
						case 3:
							//удалениен измерения
							$query = "
								declare
								 @Rate_id bigint = :Rate_id,
								 @Error_Code int = null,
								 @Error_Message varchar(4000) = null;
								exec dbo.p_Rate".$RateType."_del
								 @Rate".$RateType."_id = @Rate_id,
								 @pmUser_id = :pmUser_id,
								 @IsRemove = 1,
								 @Error_Code = @Error_Code output,
								 @Error_Message = @Error_Message output;

								select @Rate_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
							";
							$result = $this->db->query($query, $Rate);
							//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $Rate));
							break;				
					}
				}
			}
				
				//BOB - 05.03.2020
			// если типы мероприятий, содержащие медикамент  и имеется параметр 'ReanimDrug'
			if (in_array($params['ReanimatActionType_SysNick'],array('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics','sedation'))  && isset($params['ReanimDrug'])  ) {

				
				foreach ($params['ReanimDrug'] as $ReanimDrug) {
					//echo '<pre>' .'$ReanimDrug='. print_r($ReanimDrug, 1) . '</pre>';
				
					$ReanimDrug['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
					$ReanimDrug['EvnReanimatAction_id'] = $EvnReanimatAction_id_Rate;

					//если старая запись - сначалап удаляю EvnDrug 
					//echo '<pre>' .'$ReanimDrug[ReanimDrug_Status]='. print_r($ReanimDrug['ReanimDrug_Status'], 1) . '</pre>';
					if ($ReanimDrug['ReanimDrug_Status'] != 0) {
						$query = "		
							declare

							@EvnDrug_id bigint = :EvnDrug_id,
							@pmUser_id bigint = :pmUser_id,
							@Error_Code int = null,
							@Error_Message varchar(4000) = null; 

							exec dbo.p_EvnDrug_del
							@EvnDrug_id,
							@pmUser_id,
							@Error_Code output,
							@Error_Message output;

							select @Error_Code as Error_Code, @Error_Message as Error_Message;		
						";
						$result = $this->db->query($query, $ReanimDrug);
						sql_log_message('error', 'EvnReanimatPeriod_model EvnScales_Del query: ', getDebugSql($query, $ReanimDrug));
						if ( !is_object($result) )
							return false;
					}
					//если не "Под наркозом" и не удаляется запись ЛС
					if(($ReanimDrug['ReanimDrugType_id'] != 12) && ($ReanimDrug['ReanimDrug_Status'] != 3)) {
					
						//определяю наименование по коду
						$query = "		
							select ReanimDrugType_name from dbo.ReanimDrugType  with (nolock)
							where ReanimDrugType_id = :ReanimDrugType_id" ;		
						$result = $this->db->query($query, $ReanimDrug);
						//sql_log_message('error', 'from dbo.ReanimDrugType exec query: ', getDebugSql($query, $ReanimDrug));		

						if ( !is_object($result) )
							return false;

						$DrugNameResult = $result->result('array');

						//выбор Drug_id для выбранного медикамента
						// минимального, т.к. без разницы какой, лишь бы было что сохранить в EvnDrug
						$BaseWhere = "";
						switch ($DrugNameResult[0]['ReanimDrugType_name']){
							case 'Адреналин':
							case 'Мезатон':
							case 'Норадреналин':
							case 'Новокаин':
							case 'Лидокаин':
							case 'Дофамин':
							case 'Левосимендан':   //BOB - 04.03.2020
							case 'Фентанил':   //BOB - 04.03.2020
							case 'Морфин':   //BOB - 04.03.2020
							case 'Тиопентал натрия':   //BOB - 04.03.2020
							case 'Дормикум':   //BOB - 04.03.2020
							case 'Фенобарбитал':   //BOB - 04.03.2020
								$BaseWhere = " and D.DrugTorg_Name = '".$DrugNameResult[0]['ReanimDrugType_name']."'  ";
								break;
							case 'Добутамин':
								$BaseWhere = " and D.DrugTorg_Name like '%".$DrugNameResult[0]['ReanimDrugType_name']."%'  ";
								break;
							case 'Ропивакаин':
							case 'Каспофунгин':
							case 'Микафунгин':
							case 'Анидулафунгин':

								$BaseWhere = " and  D.DrugComplexMnn_id in (SELECT DrugComplexMnn_id FROM rls.DrugComplexMnn DCM with (nolock)
																			where DCM.DrugComplexMnn_RusName like '%".$DrugNameResult[0]['ReanimDrugType_name']."%')";
								break;
						}

						$query = "
							select min(Drug_id) as Drug_id
							from rls.Drug D with (nolock)
							inner join rls.DrugPrep DP with (nolock) on DP.DrugPrepFas_id = D.DrugPrepFas_id
							where (1=1) ".$BaseWhere."  
							and D.Drug_begDate <=  GETDATE()
							and (D.Drug_endDate >=   GETDATE() or  D.Drug_endDate is null)
							and not (D.Drug_Dose is null and Drug_Volume is null and D.Drug_Mass is null and Drug_Fas is null and DP.Drug_Size is null) ";
						$result = $this->db->query($query);
						sql_log_message('error', 'from dbo.ReanimDrugType exec query: ', getDebugSql($query));		
						if ( !is_object($result) )
							return false;

						$DrugNameResult = $result->result('array');
						$params['Drug_id'] = isset($DrugNameResult[0]['Drug_id']) ? $DrugNameResult[0]['Drug_id'] : null;


						$query = "
						declare
							@Res bigint  = null ,
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_EvnDrug_ins       --_Socr
							@EvnDrug_id = @Res output, 
							@EvnDrug_pid = :EvnReanimatAction_pid,
							@EvnDrug_setDT = :EvnReanimatAction_setDT,
							@Lpu_id = :Lpu_id, 
							@Server_id = :Server_id, 
							@PersonEvn_id = :PersonEvn_id, 
							@LpuSection_id = :LpuSection_id, 
							@Drug_id = :Drug_id, 
							@pmUser_id = :pmUser_id, 
							@Error_Code = @ErrCode output, 
							@Error_Message = @ErrMessage output;
						select @Res as EvnDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Message; ";
						$result = $this->db->query($query, $params);
						sql_log_message('error', 'p_EvnDrug_ins_Socr exec query: ', getDebugSql($query, $params));
						//BOB - 12.09.2018


						if ( !is_object($result) )
							return false;

						$DrugNameResult = $result->result('array');

						if (($DrugNameResult[0]['EvnDrug_id'] == null) || ($DrugNameResult[0]['Error_Code'])|| ($DrugNameResult[0]['Error_Message'])){
							$Response['success'] = 'false';
							$Response['Error_Msg'] = $DrugNameResult[0]['Error_Code'].' '.$DrugNameResult[0]['Error_Message'];
							return $Response;			
						}
						//$EvnDrug_id = ($DrugNameResult != null) ? $DrugNameResult[0]['EvnDrug_id'] : null ; //BOB - 12.09.2018
						$ReanimDrug['EvnDrug_id'] = ($DrugNameResult != null) ? $DrugNameResult[0]['EvnDrug_id'] : null ;
					}


					switch ($ReanimDrug['ReanimDrug_Status']) {
						case 0:
							//добавление нового ЛС
							$query = "
								declare
									@ReanimDrug_id bigint = null,
									@Error_Code int = null,
									@Error_Message varchar(4000) = null;
							   	exec dbo.p_ReanimDrug_ins
									@ReanimDrug_id = @ReanimDrug_id output,
									@EvnReanimatAction_id = :EvnReanimatAction_id,								
									@ReanimDrugType_id = :ReanimDrugType_id,
									@EvnDrug_id = :EvnDrug_id,
									@ReanimDrug_Dose = :ReanimDrug_Dose,
									@ReanimDrug_Unit = :ReanimDrug_Unit,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
								select @ReanimDrug_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		

							";
							$result = $this->db->query($query, $ReanimDrug);
							sql_log_message('error', 'p_ReanimDrug_ins exec query: ', getDebugSql($query, $ReanimDrug));
							break;
						case 2:
							//изменение ЛС
							$query = "
								declare
									@ReanimDrug_id bigint = :ReanimDrug_id,
									@Error_Code int = null,
									@Error_Message varchar(4000) = null;
								exec dbo.p_ReanimDrug_upd
									@ReanimDrug_id = @ReanimDrug_id output,
									@EvnReanimatAction_id = :EvnReanimatAction_id,								
									@ReanimDrugType_id = :ReanimDrugType_id,
									@EvnDrug_id = :EvnDrug_id,
									@ReanimDrug_Dose = :ReanimDrug_Dose,
									@ReanimDrug_Unit = :ReanimDrug_Unit,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
								select @ReanimDrug_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
							";
							//log_message('debug', 'BOB_$query_1'.print_r($query, 1));
							$result = $this->db->query($query, $ReanimDrug);
							sql_log_message('error', 'p_ReanimDrug_upd exec query: ', getDebugSql($query, $ReanimDrug));
							break;
						case 3:
							//удалениен ЛС

							$query = "
								declare
									@ReanimDrug_id bigint = :ReanimDrug_id,
									@Error_Code int = null,
									@Error_Message varchar(4000) = null;								
								exec dbo.p_ReanimDrug_del
									@ReanimDrug_id =  @ReanimDrug_id,
									@pmUser_id = :pmUser_id,
									@IsRemove = 1,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
								select @ReanimDrug_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
							";
							$result = $this->db->query($query, $ReanimDrug);
							sql_log_message('error', 'p_ReanimDrug_del exec query: ', getDebugSql($query, $ReanimDrug));
							break;				
					}
				} // foreach
			}				


			//BOB - 05.03.2020
		}
		
		if (sizeof($EvnScaleResult) > 0) {
			if (($EvnScaleResult[0]['EvnReanimatAction_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null)){
				//ничего не надо

			}		
			else {
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
			}			
		}
			
		// подумать что возвращать: ссылки на привязанные записи , а может просто сообщение
		if ( is_object($result) ) {
			return $Response;
		}
		else {
			return false;
		}
		
		return true;
		
	}
	
	
	
	
	/**
	 * BOB - 03.11.2018
	 * Извлечение данных параметров ИВЛ
	 */
	function GetParamIVL($data) {
		$query = "
			select top 1 IVLParameter_id,IVLParameter_Apparat,IVLP.IVLRegim_id,IVLR.IVLRegim_SysNick, IVLParameter_TubeDiam,IVLParameter_FiO2,IVLParameter_FrequSet,IVLParameter_VolInsp,IVLParameter_PressInsp,IVLParameter_PressSupp,
				  IVLParameter_FrequTotal,IVLParameter_VolTe,IVLParameter_VolE,IVLParameter_TinTet,IVLParameter_VolTrig,IVLParameter_PressTrig,IVLParameter_PEEP,IVLParameter_PcentMinVol,IVLParameter_TwoASVMax,
				  IVLParameter_VolTi,IVLParameter_Peak,IVLParameter_MAP,IVLParameter_Tins,IVLParameter_FlowMax,IVLParameter_FlowMin,IVLParameter_Other,IVLParameter_deltaP
 
			from v_IVLParameter IVLP with (nolock)
			inner join IVLRegim IVLR  with (nolock) on IVLP.IVLRegim_id = IVLR.IVLRegim_id
			where EvnReanimatAction_id = :EvnReanimatAction_id		";
		$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	
	/**
	 * BOB - 03.11.2018
	 * Извлечение данных периодических измерений, проводимых в рамках реанимационных мероприятий
	 */
	function GetReanimatActionRate($data) {
		//!!!!//расчёт величины Rate_PerCent для рисования графика, это процеты поэтому *100, деление на 200 потому что принял 200 за максимум
		switch ($data['ReanimatActionType_SysNick']) {
			case 'endocranial_sensor':  //использование датчика ВЧД
				$query = "
					select RateVCHD_id as Rate_id, RateVCHD_Value as Rate_Value, CAST(CAST(RateVCHD_Value AS float)/200*100 as int) as Rate_PerCent,  RateVCHD_StepsToChange as Rate_StepsToChange, 
						   CONVERT(varchar(10),  RateVCHD_setDate, 104) as Rate_setDate, RateVCHD_setTime  as Rate_setTime,
						   :EvnReanimatAction_id as EvnReanimatAction_id, 1 as Rate_RecordStatus, RateVCHD_setDT as  Rate_setDT
					  from dbo.v_RateVCHD  with (nolock)
					 where EvnReanimatAction_id = :EvnReanimatAction_id
					 order by RateVCHD_id --//BOB - 23.10.2019
					";
				$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
				break;
			case 'invasive_hemodynamics':  //инвазивная гемодинамика - внутривенное измерение давления
				$query = "
					select RateHemodynam_id as Rate_id, RateHemodynam_Value as Rate_Value, CAST(CAST(RateHemodynam_Value AS float)/200*100 as int) as Rate_PerCent, '' as Rate_StepsToChange, 
						   CONVERT(varchar(10),  RateHemodynam_setDate, 104) as Rate_setDate, RateHemodynam_setTime  as Rate_setTime,
						   :EvnReanimatAction_id as EvnReanimatAction_id, 1 as Rate_RecordStatus, RateHemodynam_setDT as  Rate_setDT
					  from dbo.v_RateHemodynam  with (nolock)
					 where EvnReanimatAction_id = :EvnReanimatAction_id
					 order by RateHemodynam_id --//BOB - 23.10.2019
					";
				$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
				break;
			case 'observation_saturation':  //Наблюдение сатурации гемоглобина
				$query = "
					select RateSPO2_id as Rate_id, RateSPO2_Value as Rate_Value, RateSPO2_Value as Rate_PerCent, '' as Rate_StepsToChange, 
						   CONVERT(varchar(10),  RateSPO2_setDate, 104) as Rate_setDate, RateSPO2_setTime  as Rate_setTime,
						   :EvnReanimatAction_id as EvnReanimatAction_id, 1 as Rate_RecordStatus, RateSPO2_setDT as  Rate_setDT
					  from dbo.v_RateSPO2  with (nolock)
					 where EvnReanimatAction_id = :EvnReanimatAction_id
					 order by RateSPO2_id  --//BOB - 23.10.2019
					";
				$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
				break;
			case 'new_rate':  //получение пустой записи для заполнения пустого грида измерений
				$query = "					
					select -1 as Rate_id, 0 as Rate_Value, 0 as Rate_PerCent, '' as Rate_StepsToChange, 
						CONVERT(varchar(10),  GetDate(), 104) as Rate_setDate, left(cast(GetDate() as time),5) as Rate_setTime,
						case when :EvnReanimatAction_id is null then 'New_GUID_Id' else cast(:EvnReanimatAction_id as varchar(20)) end as EvnReanimatAction_id, 0 as Rate_RecordStatus
					";
				$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
				break;
		}
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	
	/**
	 * BOB - 22.02.2019
	 * Извлечение данных Сердечно-лёгочной реанимации
	 */
	function GetCardPulm($data) {
		$query = "
			select 	ReanimatCardPulm_id,
					EvnReanimatAction_id,

					convert(varchar(10), ReanimatCardPulm_ClinicalDeathDate  ,104) as ReanimatCardPulm_ClinicalDeathDate,
					ReanimatCardPulm_ClinicalDeathTime,
					ReanimatCardPulm_IsPupilDilat,
					ReanimatCardPulm_IsCardMonitor,
					ReanimatCardPulm_StopCardActType,
					IVLRegim_id,
					ReanimatCardPulm_FiO2,
					ReanimatCardPulm_IsCardTonics,
					ReanimatCardPulm_CardTonicDose,
					ReanimatCardPulm_CathetVein,
					ReanimatCardPulm_TrachIntub,
					ReanimatCardPulm_Auscultatory,
					ReanimatCardPulm_AuscultatoryTxt,
					ReanimatCardPulm_CardMassage,
					ReanimatCardPulm_DefibrilCount,
					ReanimatCardPulm_DefibrilMin,
					ReanimatCardPulm_DefibrilMax,
					ReanimDrugType_id,
					ReanimatCardPulm_DrugDose,
					ReanimatCardPulm_DrugSposob,
					ReanimDrugType_did,
					ReanimatCardPulm_dDrugDose,
					ReanimatCardPulm_dDrugSposob,
					ReanimatCardPulm_DrugTxt,
					ReanimatCardPulm_IsEffective,
					ReanimatCardPulm_Time,
					convert(varchar(10), ReanimatCardPulm_BiologDeathDate  ,104) as ReanimatCardPulm_BiologDeathDate,
					ReanimatCardPulm_BiologDeathTime,
					ReanimatCardPulm_DoctorTxt 
				  from dbo.v_ReanimatCardPulm  with (nolock)
				where EvnReanimatAction_id = :EvnReanimatAction_id		";
		$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	
	/**
	 * BOB - 05.3.2020
	 * Извлечение данных Лекарственных средств
	 */
	function GetReanimDrug($data) {
		$query = "
			select	ReanimDrug_id,	
				EvnReanimatAction_id,	
				ReanimDrugType_id,	
				EvnDrug_id,	
				ReanimDrug_Dose,	
				ReanimDrug_Unit, 
				2 as ReanimDrug_Status
			from v_ReanimDrug   with (nolock)
			where EvnReanimatAction_id = :EvnReanimatAction_id
			order by ReanimDrug_insDT		
		";
		$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
		
		if ( is_object($result) ) {
			$src_resp = $result->result('array');
			$dst_resp = array();
			if (count($src_resp) == 0) {//вариант для старых записей: нет записей ЛС, привязанных к мероприятию, буду брать из записи мероприятия
				$query = "
					select 	-1 as ReanimDrug_id,	
						EvnReanimatAction_id,	
						ReanimDrugType_id,	
						EvnDrug_id,	
						EvnReanimatAction_DrugDose as ReanimDrug_Dose,	
						EvnReanimatAction_DrugUnit as ReanimDrug_Unit, 
						0 as ReanimDrug_Status
					from EvnReanimatAction
					where EvnReanimatAction_id = :EvnReanimatAction_id
				";
				$result = $this->db->query($query, array('EvnReanimatAction_id' => $data['EvnReanimatAction_id']));
				if ( !is_object($result) ) return false;

				$src_resp = $result->result('array');
			}

			foreach ($src_resp as $Raw) {
				$dst_resp[$Raw['ReanimDrug_id']] = $Raw;
			}
			return $dst_resp;
		}
		else {
			return false;
		}	
	}
	
	
	//РЕГУЛЯРНЫЕ НАБЛЮДЕНИЯ***********************************************************************************************************************************************
	/**
	 * BOB - 20.09.2017
	 * загрузка таблици регулярного наблюдения состояния
	 */	
	function loudEvnReanimatConditionGrid($data) {	
		$query = "
			declare 
				@EvnReanimatCondition_pid bigint = :EvnReanimatCondition_pid, 
				@ReanimatAgeGroup_id bigint = null;
	
			select @ReanimatAgeGroup_id = ReanimatAgeGroup_id from v_EvnReanimatPeriod where EvnReanimatPeriod_id = @EvnReanimatCondition_pid;
	
			if (@ReanimatAgeGroup_id in (1,2))
				begin
		
					select	ENS.EvnNeonatalSurvey_id as EvnReanimatCondition_id, 
							ENS.EvnNeonatalSurvey_pid as EvnReanimatCondition_pid, 
							ENS.Person_id, 
							ENS.PersonEvn_id, 
							ENS.Server_id,    
							convert(varchar(10), ENS.EvnNeonatalSurvey_setDate  ,104) as EvnReanimatCondition_setDate,
							ENS.EvnNeonatalSurvey_setTime as EvnReanimatCondition_setTime,
							convert(varchar(10), ENS.EvnNeonatalSurvey_disDate  ,104) as EvnReanimatCondition_disDate,
							ENS.EvnNeonatalSurvey_disTime as EvnReanimatCondition_disTime,
							ENS.ReanimStageType_id, 
							case 
								when RCP1.ReanimStageType_id = 1 then 'Первичный осмотр' 
								else RCP1.ReanimStageType_Name
							end as Stage_Name, 
							ENS.ReanimConditionType_id,
							RCP2.ReanimConditionType_Name as Condition_Name,
							ENS.EvnNeonatalSurvey_Doctor as EvnReanimatCondition_Doctor
					from dbo.v_EvnNeonatalSurvey ENS with (nolock)
					left join dbo.ReanimStageType RCP1 with (nolock) on ENS.ReanimStageType_id = RCP1.ReanimStageType_id
					left join dbo.ReanimConditionType RCP2 with (nolock) on ENS.ReanimConditionType_id = RCP2.ReanimConditionType_id
					where ENS.EvnNeonatalSurvey_pid = @EvnReanimatCondition_pid
					order by ENS.EvnNeonatalSurvey_setDT desc
		
				end
			else
				begin 

					select	ERC.EvnReanimatCondition_id, 
						ERC.EvnReanimatCondition_pid, 
						ERC.Person_id, 
						ERC.PersonEvn_id, 
						ERC.Server_id,    
						convert(varchar(10), ERC.EvnReanimatCondition_setDate  ,104) as EvnReanimatCondition_setDate,
						ERC.EvnReanimatCondition_setTime,
						convert(varchar(10), ERC.EvnReanimatCondition_disDate  ,104) as EvnReanimatCondition_disDate,
						ERC.EvnReanimatCondition_disTime,
						ERC.ReanimStageType_id, 
						RCP1.ReanimStageType_Name as Stage_Name, 
						ERC.ReanimConditionType_id,
						RCP2.ReanimConditionType_Name as Condition_Name,
						ERC.EvnReanimatCondition_Complaint,
						ERC.SkinType_id,
						ERC.EvnReanimatCondition_SkinTxt,
						ERC.ConsciousType_id,
						ERC.BreathingType_id,
						ERC.EvnReanimatCondition_IVLapparatus,
						ERC.EvnReanimatCondition_IVLparameter,
						ERC.EvnReanimatCondition_Auscultatory,
						ERC.HeartTonesType_id,
						ERC.HemodynamicsType_id,
						ERC.EvnReanimatCondition_Pressure,
						ERC.EvnReanimatCondition_HeartFrequency,
						ERC.EvnReanimatCondition_StatusLocalis,
						ERC.AnalgesiaType_id,
						ERC.EvnReanimatCondition_Diuresis,
						ERC.UrineType_id,
						ERC.EvnReanimatCondition_UrineTxt,
						ERC.EvnReanimatCondition_Conclusion,
						ERC.EvnReanimatCondition_AnalgesiaTxt,
						ERC.ReanimArriveFromType_id,
						ERC.EvnReanimatCondition_HemodynamicsTxt,
						ERC.EvnReanimatCondition_NeurologicStatus,
						ERC.EvnReanimatCondition_sofa,
						ERC.EvnReanimatCondition_apache,
						ERC.EvnReanimatCondition_Saturation,
						--ERC.NutritiousType_id,					--BOB - 23.09.2019 - закомментарено
						--ERC.EvnReanimatCondition_NutritiousTxt,	--BOB - 23.09.2019 - закомментарено
						ERC.EvnReanimatCondition_Temperature,
						ERC.EvnReanimatCondition_InfusionVolume,
						ERC.EvnReanimatCondition_DiuresisVolume,
						ERC.EvnReanimatCondition_CollectiveSurvey,
						--BOB - 21.12.2018
						ERC.EvnReanimatCondition_SyndromeType,
						ERC.EvnReanimatCondition_ConsTxt,
						ERC.SpeechDisorderType_id,
						ERC.EvnReanimatCondition_rass,
						ERC.EvnReanimatCondition_Eyes,
						ERC.EvnReanimatCondition_WetTurgor,
						ERC.EvnReanimatCondition_waterlow,
						ERC.SkinType_mid,
						ERC.EvnReanimatCondition_MucusTxt,
						ERC.EvnReanimatCondition_IsMicrocDist,
						ERC.EvnReanimatCondition_IsPeriphEdem,
						ERC.EvnReanimatCondition_Reflexes,
						ERC.EvnReanimatCondition_BreathFrequency,
						ERC.EvnReanimatCondition_HeartTones,
						ERC.EvnReanimatCondition_IsHemodStab,
						ERC.EvnReanimatCondition_Tongue,
						ERC.EvnReanimatCondition_Paunch,
						ERC.EvnReanimatCondition_PaunchTxt,
						ERC.PeristalsisType_id,
						ERC.EvnReanimatCondition_VBD,
						ERC.EvnReanimatCondition_Defecation,
						ERC.EvnReanimatCondition_DefecationTxt,
						ERC.LimbImmobilityType_id,
						ERC.EvnReanimatCondition_MonopLoc,
						ERC.EvnReanimatCondition_mrc,
						ERC.EvnReanimatCondition_MeningSign,
						ERC.EvnReanimatCondition_MeningSignTxt,
						ERC.EvnReanimatCondition_glasgow,
						ERC.EvnReanimatCondition_four,
						ERC.EvnReanimatCondition_SyndromeTxt,
						ERC.EvnReanimatCondition_Doctor
						--BOB - 16.09.2019
					from dbo.v_EvnReanimatCondition ERC with (nolock)
					left join dbo.ReanimStageType RCP1 with (nolock) on ERC.ReanimStageType_id = RCP1.ReanimStageType_id
					left join dbo.ReanimConditionType RCP2 with (nolock) on ERC.ReanimConditionType_id = RCP2.ReanimConditionType_id
					where ERC.EvnReanimatCondition_pid = @EvnReanimatCondition_pid
					order by ERC.EvnReanimatCondition_setDT desc

				end	
		";
		$result = $this->db->query($query, array('EvnReanimatCondition_pid' => $data['EvnReanimatCondition_pid']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}


	/**
	 * BOB - 20.09.2017
	 * загрузка регулярного наблюдения состояния для ЭМК
	 */	
	function loudEvnReanimatConditionGridEMK($data) {	
		//BOB - 06.02.2018
		$query = "
			select	ERC.EvnReanimatCondition_id, 
					ERC.EvnReanimatCondition_pid, 
					ERC.Person_id, 
					ERC.PersonEvn_id, 
					ERC.Server_id,    
					--ERC.EvnReanimatCondition_setDT,   
					convert(varchar(10), ERC.EvnReanimatCondition_setDate  ,104) as EvnReanimatCondition_setDate,
					ERC.EvnReanimatCondition_setTime,
					convert(varchar(10), ERC.EvnReanimatCondition_disDate  ,104) as EvnReanimatCondition_disDate,
					ERC.EvnReanimatCondition_disTime,
					ERC.ReanimStageType_id, 
					(select top 1 ReanimStageType_Name from dbo.ReanimStageType with (nolock) where ReanimStageType_id = ERC.ReanimStageType_id) as Stage_Name, 
					ERC.ReanimConditionType_id,
					(select top 1 ReanimConditionType_Name from dbo.ReanimConditionType with (nolock) where ReanimConditionType_id = ERC.ReanimConditionType_id) as Condition_Name,
					ERC.EvnReanimatCondition_Complaint as Complaint,
					case
						when ERC.SkinType_id = 5 then ERC.EvnReanimatCondition_SkinTxt
						when ERC.SkinType_id in (1,2,3,4) then (select SkinType_Name from SkinType ST where ST.SkinType_id = ERC.SkinType_id) + case when ERC.EvnReanimatCondition_SkinTxt is not null then ', ' + ERC.EvnReanimatCondition_SkinTxt else '' end
						else case when ERC.EvnReanimatCondition_SkinTxt is not null then ERC.EvnReanimatCondition_SkinTxt else '' end
					end as SkinTxt,
					(select top 1 ConsciousType_Name from dbo.ConsciousType with (nolock) where ConsciousType_id = ERC.ConsciousType_id) as Conscious,
					(select top 1 BreathingType_Name from dbo.BreathingType with (nolock) where BreathingType_id = ERC.BreathingType_id) as Breathing,
					ERC.EvnReanimatCondition_IVLapparatus as IVLapparatus,
					ERC.EvnReanimatCondition_IVLparameter as IVLparameter,
					--	ISNULL((select top 1 AuscultatoryType_Name from dbo.AuscultatoryType with (nolock) where AuscultatoryType_id = cast(SUBSTRING(ERC.EvnReanimatCondition_Auscultatory,1,1) as integer)),'')+ ' ' +
					--	ISNULL((select top 1 AuscultatoryType_Name from dbo.AuscultatoryType with (nolock) where AuscultatoryType_id = case  when cast(SUBSTRING(ERC.EvnReanimatCondition_Auscultatory,2,1) as integer) = 0 then 0 else cast(SUBSTRING(ERC.EvnReanimatCondition_Auscultatory,2,1) as integer) + 2 end ),'')+ ', ' +
					--	ISNULL((select top 1 AuscultatoryType_Name from dbo.AuscultatoryType with (nolock) where AuscultatoryType_id =  case  when cast(SUBSTRING(ERC.EvnReanimatCondition_Auscultatory,3,1) as integer) = 0 then 0 else cast(SUBSTRING(ERC.EvnReanimatCondition_Auscultatory,3,1) as integer) + 5 end ),'') 
					dbo.getReanimatBreathAuscultative(EvnReanimatCondition_id) as Auscultatory,
					(case substring(ERC.EvnReanimatCondition_HeartTones,1,1) when '1' then 'ритмичные' when '2' then 'аритмичные'  else '' end + ' ' +
                    case substring(ERC.EvnReanimatCondition_HeartTones,2,1) when '1' then 'ясные' when '2' then 'приглушенные'  when '3' then 'глухие'  else '' end) as Heart_tones,
					(select top 1 HemodynamicsType_Name from dbo.HemodynamicsType with (nolock) where HemodynamicsType_id = ERC.HemodynamicsType_id) +  case when ERC.EvnReanimatCondition_HemodynamicsTxt is null or ERC.EvnReanimatCondition_HemodynamicsTxt = '' then '' else ', параметры: ' end + IsNull(ERC.EvnReanimatCondition_HemodynamicsTxt,'')
					as Hemodynamics,
					ERC.EvnReanimatCondition_Pressure as Pressure,
					case when ERC.EvnReanimatCondition_HeartFrequency > 0 then cast(ERC.EvnReanimatCondition_HeartFrequency as varchar) + ' / мин' else '' end as Heart_frequency,
					ERC.EvnReanimatCondition_StatusLocalis as Status_localis,
					case
						when ERC.AnalgesiaType_id = 3 then
							ERC.EvnReanimatCondition_AnalgesiaTxt
						else 
							(select top 1 AnalgesiaType_Name from dbo.AnalgesiaType with (nolock) where AnalgesiaType_id = ERC.AnalgesiaType_id)
					end as Analgesia,
					--ISNULL((select top 1 DiuresisType_Name from dbo.DiuresisType with (nolock) where DiuresisType_id = cast(SUBSTRING(ERC.EvnReanimatCondition_Diuresis,1,1) as integer)),'')+ ' ' +
					--ISNULL((select top 1 DiuresisType_Name from dbo.DiuresisType with (nolock) where DiuresisType_id = case  when cast(SUBSTRING(ERC.EvnReanimatCondition_Diuresis,2,1) as integer) = 0 then 0 else cast(SUBSTRING(ERC.EvnReanimatCondition_Diuresis,2,1) as integer) + 4 end ),'')
					case substring(EvnReanimatCondition_Diuresis,1,1) when '1' then 'адекватный' when '2' then 'снижен'  when '3' then 'олигурия'  when '4' then 'анурия'  when '5' then 'полиурия'  else '' end + ' ' +
					case substring(EvnReanimatCondition_Diuresis,2,1) when '1' then 'самостоятельно' when '2' then 'по уретральному катетеру'  else '' end + ' ' +
					case substring(EvnReanimatCondition_Diuresis,3,1) when '1' then 'на фоне стимуляции' when '2' then 'без стимуляции' else '' end + ' ' +
					case when EvnReanimatCondition_DiuresisVolume is not null then 'объём - ' + cast(cast(EvnReanimatCondition_DiuresisVolume as int) as varchar(10)) + ' мл' else '' end
					as Diuresis,
					case
						when ERC.UrineType_id = 4 then
							ERC.EvnReanimatCondition_UrineTxt
						else 
							(select top 1 UrineType_Name from dbo.UrineType with (nolock) where UrineType_id = ERC.UrineType_id)
					end as Urine,
					ERC.EvnReanimatCondition_Conclusion as Conclusion,
					ERC.EvnReanimatCondition_NeurologicStatus as Neurologic_Status,
					case 
						when ERC.ReanimStageType_id = 1 then
							' из ' + (select top 1 ReanimArriveFromType_Name from dbo.ReanimArriveFromType with (nolock) where ReanimArriveFromType_id = ERC.ReanimArriveFromType_id)
						else ''
					end  as ArriveFromTxt,
					case ERC.ReanimStageType_id when 3 then 'Дополнительная информация' else 'Неврологический статус' end as NevroField,
					case ERC.ReanimStageType_id when 3 then 'Проведено' else 'Заключение' end as ConclusionField,
					ERC.EvnReanimatCondition_sofa as sofa,
					ERC.EvnReanimatCondition_apache as apache,
					case when ERC.EvnReanimatCondition_Saturation is not null then cast(ERC.EvnReanimatCondition_Saturation as varchar) + ' %' else '' end   as SpO2,
					--case												--BOB - 23.09.2019 - закомментарено
					--	when ERC.NutritiousType_id = 4 then
					--		ERC.EvnReanimatCondition_NutritiousTxt
					--	else
					--		REPLACE((select top 1 RNT.NutritiousType_Name from dbo.NutritiousType RNT with (nolock) where RNT.NutritiousType_id = ERC.NutritiousType_id), 'ое', 'ая')
					--end as Nutritious,
					case when ERC.EvnReanimatCondition_Temperature is not null then cast(cast(ERC.EvnReanimatCondition_Temperature as numeric(5, 1)) as varchar) + ' °C' else '' end  as Temperature,
					case when ERC.EvnReanimatCondition_InfusionVolume is not null then cast(ERC.EvnReanimatCondition_InfusionVolume as varchar) + ' мл' else '' end  as InfusionVolume,
					case when ERC.EvnReanimatCondition_DiuresisVolume is not null then cast(ERC.EvnReanimatCondition_DiuresisVolume as varchar) + ' мл' else '' end  as DiuresisVolume,
					ERC.EvnReanimatCondition_CollectiveSurvey as CollectiveSurvey
                        
			from dbo.v_EvnReanimatCondition ERC with (nolock)
			where ERC.EvnReanimatCondition_pid = :EvnReanimatCondition_pid
			order by ERC.EvnReanimatCondition_setDT desc
		";		
		$result = $this->db->query($query, array('EvnReanimatCondition_pid' => $data['EvnReanimatCondition_pid']));
				
		if (is_object($result)){
			$Response = $result->result('array');
			
			
			//log_message('debug', 'EvnReanimatPeriod_model=>loudEvnReanimatConditionGridEMK $Response = '.print_r($Response, 1)); //BOB - 4.12.2017
			return $Response;
			
		}
		else	
			return false;
		
	}

	/**
	 * BOB - 23.04.2018
	 * получение данных шкал и мероприятий для нового наблюдения
	 */
	function getDataToNewCondition($data) {
		$queryParams = array('EvnReanimatCondition_pid' => $data['EvnReanimatCondition_pid']);

		$query = "
				select top 1
				case  ReanimStageType_id
					when 2 then convert(varchar(10), ERC.EvnReanimatCondition_disDate  ,104)
					else convert(varchar(10), ERC.EvnReanimatCondition_setDate  ,104)
				end as EvnReanimatCondition_disDate,
				case  ReanimStageType_id
					when 2 then ERC.EvnReanimatCondition_disTime
					else ERC.EvnReanimatCondition_setTime
				end as EvnReanimatCondition_disTime,
				case  ReanimStageType_id
					when 2 then convert(varchar(20), ERC.EvnReanimatCondition_disDT, 120)
					else convert(varchar(20), ERC.EvnReanimatCondition_setDT, 120)
				end as EvnReanimatCondition_disDT,
				ReanimStageType_id
			from v_EvnReanimatCondition ERC  with (nolock) 
			where EvnReanimatCondition_pid = :EvnReanimatCondition_pid
			order by EvnReanimatCondition_setDT desc		
		";	
		$LastCondit = $this->db->query($query, $queryParams);
		
		if ( !is_object($LastCondit) )
			return false;

		$LastConditArr = $LastCondit->result('array');
		$queryParams['EvnReanimatCondition_disDT'] = isset($LastConditArr[0]['EvnReanimatCondition_disDT']) ? $LastConditArr[0]['EvnReanimatCondition_disDT'] : '3999-01-01 00:00:00';

		//значени сатурации гемоглобина
		$query = "
			select top 1 RateSPO2_Value as EvnReanimatAction_ObservValue
			  from dbo.v_EvnReanimatAction ERA with (nolock)
			 inner join dbo.RateSPO2 SPO with (nolock) on ERA.EvnReanimatAction_id = SPO.EvnReanimatAction_id
			 where ERA.EvnReanimatAction_pid = :EvnReanimatCondition_pid
			   and ERA.ReanimatActionType_id = 9
			   and SPO.RateSPO2_Deleted = 1
			   and (RateSPO2_setDT >= :EvnReanimatCondition_disDT or RateSPO2_setDT is null  )
			 order by RateSPO2_id desc
		";
		$SpO2 = $this->db->query($query, $queryParams);
		
		if ( !is_object($SpO2) )
			return false;
		
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
		
		//BOB - 23.09.2019 - закомментарено
		//		//питание
		//		$query = "
		//			select top 1 ERA.EvnReanimatAction_id, ERA.EvnReanimatAction_setDT, ERA.EvnReanimatAction_disDT, RNT.NutritiousType_id, RNT.NutritiousType_Name as ReanimatNutritionType_Name
		//			from v_EvnReanimatAction ERA with (nolock)
		//			inner join dbo.NutritiousType RNT with (nolock) on RNT.NutritiousType_id = ERA.NutritiousType_id
		//			 where ERA.EvnReanimatAction_pid = :EvnReanimatCondition_pid
		//			   and ERA.ReanimatActionType_SysNick = 'nutrition'
		//			   and ERA.EvnReanimatAction_disDT is null
		//			 order by ERA.EvnReanimatAction_setDT desc	";
		//		$Nutrition = $this->db->query($query, $queryParams);

		
		//if ( !is_object($Nutrition) )
		//	return false;
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));



	
		//параметры ИВЛ
		$query = "
			select top 1 IVLParameter_id,IVLParameter_Apparat,IVLP.IVLRegim_id,IVLR.IVLRegim_SysNick, IVLR.IVLRegim_Name, IVLParameter_TubeDiam,IVLParameter_FiO2,IVLParameter_FrequSet,IVLParameter_VolInsp,IVLParameter_PressInsp,IVLParameter_PressSupp,
				  IVLParameter_FrequTotal,IVLParameter_VolTe,IVLParameter_VolE,IVLParameter_TinTet,IVLParameter_VolTrig,IVLParameter_PressTrig,IVLParameter_PEEP,IVLParameter_PcentMinVol,IVLParameter_TwoASVMax 
			from v_EvnReanimatAction ERA with (nolock)
			inner join v_IVLParameter IVLP with (nolock) on ERA.EvnReanimatAction_id = IVLP.EvnReanimatAction_id
			inner join IVLRegim IVLR  with (nolock) on IVLP.IVLRegim_id = IVLR.IVLRegim_id
			 where EvnReanimatAction_pid = :EvnReanimatCondition_pid 
			   and ReanimatActionType_SysNick = 'lung_ventilation'
			   and (EvnReanimatAction_disDT >= :EvnReanimatCondition_disDT or EvnReanimatAction_disDT is null  )
			 order by ERA.EvnReanimatAction_id desc			";	
		$IVLParameter = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'lung_ventilation exec _______: ', getDebugSql($query, $queryParams));
		
		if ( !is_object($IVLParameter) )
			return false;
		
		//SOFA
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
			  from dbo.v_EvnScale ES with (nolock) 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'sofa'
			 order by ES.EvnScale_setDT desc	";	
		$Sofa = $this->db->query($query, $queryParams);
		
		if ( !is_object($Sofa) )
			return false;
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
		//APACHE
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
			  from dbo.v_EvnScale ES with (nolock) 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'apache'
			 order by ES.EvnScale_setDT desc	";	
		$Apache = $this->db->query($query, $queryParams);
		
		if ( !is_object($Apache) )
			return false;
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
		//BOB - 24.01.2019
		//RASS
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
			  from dbo.v_EvnScale ES with (nolock) 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'rass'
			 order by ES.EvnScale_setDT desc	";	
		$rass = $this->db->query($query, $queryParams);
		
		if ( !is_object($rass) )
			return false;

		//BOB - 24.01.2019
		//WATERLOW
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
			  from dbo.v_EvnScale ES with (nolock) 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'waterlow'
			 order by ES.EvnScale_setDT desc	";	
		$waterlow = $this->db->query($query, $queryParams);
		
		if ( !is_object($waterlow) )
			return false;

		//BOB - 24.01.2019
		//MRC
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
			  from dbo.v_EvnScale ES with (nolock) 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'mrc'
			 order by ES.EvnScale_setDT desc	";	
		$mrc = $this->db->query($query, $queryParams);
		
		if ( !is_object($mrc) )
			return false;

		
		//BOB - 16.09.2019
		//Glasgow
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick
			  from dbo.v_EvnScale ES with (nolock)
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick in ('glasgow','glasgow_ch','glasgow_neonat')
			 order by ES.EvnScale_setDT desc	";
		$Glasgow = $this->db->query($query, $queryParams);
		
		if ( !is_object($mrc) )
			return false;

		
		//BOB - 16.09.2019
		//FOUR
		$query = "
			select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick
			  from dbo.v_EvnScale ES with (nolock)
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'four'
			 order by ES.EvnScale_setDT desc	";
		$FOUR = $this->db->query($query, $queryParams);
		
		if ( !is_object($mrc) )
			return false;
			
		$ReturnObject = array(	'SpO2' => $SpO2->result('array'),
								//'Nutritious' => $Nutrition->result('array'),  //BOB - 23.09.2019 - закомментарено
								'IVLParameter' => $IVLParameter->result('array'),
								'Sofa' => $Sofa->result('array'),
								'Apache' => $Apache->result('array'),
								'rass' => $rass->result('array'),
								'waterlow' => $waterlow->result('array'),
								'LastCondit' => $LastConditArr,
								'mrc' => $mrc->result('array'),
								'Glasgow' => $Glasgow->result('array'),
								'FOUR' => $FOUR->result('array'),
                               'Message' => '');

		
		//log_message('debug', 'BOB_0'.print_r($ReturnObject, 1));
		return $ReturnObject;
	}
	
	
	
	/**
	 * BOB - 27.09.2017
	 * Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
	 */
	function EvnReanimatCondition_Save($data) {
		
				//echo '<pre>'.'_1' . print_r($data, 1) . '</pre>'; 
				
				//$sessionParams = $this->sessionParams['region']['number']; //текущий пользователь
				//$sessionParams = $this->sessionParams; //текущий пользователь
				//echo '<pre>'.'   $sessionParams_2'  . print_r($sessionParams, 1) . '</pre>'; 
		
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		
		$result = null;
		
		//BOB - 12.09.2018
		$data['EvnReanimatCondition_setDate'] .= ' '.$data['EvnReanimatCondition_setTime'].':00';
		if(($data['EvnReanimatCondition_disDate'] == '') || $data['EvnReanimatCondition_disTime'] == ''){
			$data['EvnReanimatCondition_disDate'] = null;
		}
		else {
			$data['EvnReanimatCondition_disDate'] =$data['EvnReanimatCondition_disDate']." ".$data['EvnReanimatCondition_disTime'].":00";
		}
		
		
		
		$params = array(
			'EvnReanimatCondition_id' => $data['EvnReanimatCondition_id'],						
			'EvnReanimatCondition_pid' => $data['EvnReanimatCondition_pid'],
			'EvnReanimatCondition_rid' => $data['EvnReanimatCondition_rid'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => isset($data['Server_id']) ? $data['Server_id'] : null,

			'EvnReanimatCondition_setDT' => isset($data['EvnReanimatCondition_setDate']) ? $data['EvnReanimatCondition_setDate'] : null,
			'EvnReanimatCondition_disDT' => isset($data['EvnReanimatCondition_disDate']) ? $data['EvnReanimatCondition_disDate'] : null,

			'ReanimStageType_id' => $data['ReanimStageType_id'],
			'ReanimConditionType_id' => isset($data['ReanimConditionType_id']) ? $data['ReanimConditionType_id'] : null,
			'EvnReanimatCondition_Complaint' => isset($data['EvnReanimatCondition_Complaint']) ? $data['EvnReanimatCondition_Complaint'] : null,
			'SkinType_id' => isset($data['SkinType_id']) ? $data['SkinType_id'] : null,
			'EvnReanimatCondition_SkinTxt' => isset($data['EvnReanimatCondition_SkinTxt']) ? $data['EvnReanimatCondition_SkinTxt'] : null,
			'ConsciousType_id' => isset($data['ConsciousType_id']) ? $data['ConsciousType_id'] : null,
			'BreathingType_id' => isset($data['BreathingType_id']) ? $data['BreathingType_id'] : null,
			'EvnReanimatCondition_IVLapparatus' => isset($data['EvnReanimatCondition_IVLapparatus']) ? $data['EvnReanimatCondition_IVLapparatus'] : null,
			'EvnReanimatCondition_IVLparameter' => isset($data['EvnReanimatCondition_IVLparameter']) ? $data['EvnReanimatCondition_IVLparameter'] : null,
			'EvnReanimatCondition_Auscultatory' => isset($data['EvnReanimatCondition_Auscultatory']) ? $data['EvnReanimatCondition_Auscultatory'] : null,
			'HeartTonesType_id' => isset($data['HeartTonesType_id']) ? $data['HeartTonesType_id'] : null,
			'HemodynamicsType_id' => isset($data['HemodynamicsType_id']) ? $data['HemodynamicsType_id'] : null,
			'EvnReanimatCondition_Pressure' => isset($data['EvnReanimatCondition_Pressure']) ? $data['EvnReanimatCondition_Pressure'] : null,
			'EvnReanimatCondition_HeartFrequency' => isset($data['EvnReanimatCondition_HeartFrequency']) ? $data['EvnReanimatCondition_HeartFrequency'] : null,
			'EvnReanimatCondition_StatusLocalis' => isset($data['EvnReanimatCondition_StatusLocalis']) ? $data['EvnReanimatCondition_StatusLocalis'] : null,
			'AnalgesiaType_id' => isset($data['AnalgesiaType_id']) ? $data['AnalgesiaType_id'] : null,
			'EvnReanimatCondition_AnalgesiaTxt' => isset($data['EvnReanimatCondition_AnalgesiaTxt']) ? $data['EvnReanimatCondition_AnalgesiaTxt'] : null,
			'EvnReanimatCondition_Diuresis' => isset($data['EvnReanimatCondition_Diuresis']) ? $data['EvnReanimatCondition_Diuresis'] : null,
			'UrineType_id' => isset($data['UrineType_id']) ? $data['UrineType_id'] : null ,
			'EvnReanimatCondition_UrineTxt' => isset($data['EvnReanimatCondition_UrineTxt']) ? $data['EvnReanimatCondition_UrineTxt'] : null,
			'EvnReanimatCondition_Conclusion' => isset($data['EvnReanimatCondition_Conclusion']) ? $data['EvnReanimatCondition_Conclusion'] : null,
			'ReanimArriveFromType_id' => isset($data['ReanimArriveFromType_id']) ? $data['ReanimArriveFromType_id'] : null,
			'EvnReanimatCondition_HemodynamicsTxt' => isset($data['EvnReanimatCondition_HemodynamicsTxt']) ? $data['EvnReanimatCondition_HemodynamicsTxt'] : null,
			'EvnReanimatCondition_NeurologicStatus' => isset($data['EvnReanimatCondition_NeurologicStatus']) ? $data['EvnReanimatCondition_NeurologicStatus'] : null,
			'EvnReanimatCondition_sofa' => isset($data['EvnReanimatCondition_sofa']) ? $data['EvnReanimatCondition_sofa'] : null ,								//BOB - 23.04.2018
			'EvnReanimatCondition_apache' =>  isset($data['EvnReanimatCondition_apache']) ? 	$data['EvnReanimatCondition_apache'] : 	null ,						//BOB - 23.04.2018
			'EvnReanimatCondition_Saturation' => isset($data['EvnReanimatCondition_Saturation']) ?  $data['EvnReanimatCondition_Saturation'] : 	null ,					//BOB - 23.04.2018
			'EvnReanimatCondition_OxygenFraction' => isset($data['EvnReanimatCondition_OxygenFraction']) ?  $data['EvnReanimatCondition_OxygenFraction'] : 	null ,
			'EvnReanimatCondition_OxygenPressure' => isset($data['EvnReanimatCondition_OxygenPressure']) ?  $data['EvnReanimatCondition_OxygenPressure'] : 	null ,
			'EvnReanimatCondition_PaOFiO' => isset($data['EvnReanimatCondition_PaOFiO']) ?  $data['EvnReanimatCondition_PaOFiO'] : 	null ,
			//'NutritiousType_id' =>  isset($data['NutritiousType_id']) ? $data['NutritiousType_id'] : null ,													//BOB - 23.04.2018						//BOB - 23.09.2019 - закомментарено
			//'EvnReanimatCondition_NutritiousTxt' =>  isset($data['EvnReanimatCondition_NutritiousTxt']) ? $data['EvnReanimatCondition_NutritiousTxt'] : null,					//BOB - 28.08.2018		//BOB - 23.09.2019 - закомментарено
			'EvnReanimatCondition_Temperature' => isset($data['EvnReanimatCondition_Temperature']) ? $data['EvnReanimatCondition_Temperature'] : null,							//BOB - 28.08.2018
			'EvnReanimatCondition_InfusionVolume' => isset($data['EvnReanimatCondition_InfusionVolume']) ? $data['EvnReanimatCondition_InfusionVolume'] : null,				//BOB - 28.08.2018
			'EvnReanimatCondition_DiuresisVolume' => isset($data['EvnReanimatCondition_DiuresisVolume']) ? $data['EvnReanimatCondition_DiuresisVolume'] : null,				//BOB - 28.08.2018
			'EvnReanimatCondition_CollectiveSurvey' => isset($data['EvnReanimatCondition_CollectiveSurvey']) ? $data['EvnReanimatCondition_CollectiveSurvey'] : null,		//BOB - 28.08.2018
			'EvnReanimatCondition_SyndromeType' => isset($data['EvnReanimatCondition_SyndromeType']) ? $data['EvnReanimatCondition_SyndromeType'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_ConsTxt' => isset($data['EvnReanimatCondition_ConsTxt']) ? $data['EvnReanimatCondition_ConsTxt'] : null,					//BOB - 24.01.2019
			'SpeechDisorderType_id' => isset($data['SpeechDisorderType_id']) ? $data['SpeechDisorderType_id'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_rass' => isset($data['EvnReanimatCondition_rass']) ? $data['EvnReanimatCondition_rass'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_Eyes' => isset($data['EvnReanimatCondition_Eyes']) ? $data['EvnReanimatCondition_Eyes'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_WetTurgor' => isset($data['EvnReanimatCondition_WetTurgor']) ? $data['EvnReanimatCondition_WetTurgor'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_waterlow' => isset($data['EvnReanimatCondition_waterlow']) ? $data['EvnReanimatCondition_waterlow'] : null,					//BOB - 24.01.2019
			'SkinType_mid' => isset($data['SkinType_mid']) ? $data['SkinType_mid'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_MucusTxt' => isset($data['EvnReanimatCondition_MucusTxt']) ? $data['EvnReanimatCondition_MucusTxt'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_IsMicrocDist' => isset($data['EvnReanimatCondition_IsMicrocDist']) ? $data['EvnReanimatCondition_IsMicrocDist'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_IsPeriphEdem' => isset($data['EvnReanimatCondition_IsPeriphEdem']) ? $data['EvnReanimatCondition_IsPeriphEdem'] : null,					//BOB - 24.01.2019
			'EvnReanimatCondition_Reflexes' => isset($data['EvnReanimatCondition_Reflexes']) ? $data['EvnReanimatCondition_Reflexes'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_BreathFrequency' => isset($data['EvnReanimatCondition_BreathFrequency']) ? $data['EvnReanimatCondition_BreathFrequency'] : null,			//BOB - 24.01.2019
			'BreathAuscult_List' => isset($data['BreathAuscult_List']) ? json_decode($data['BreathAuscult_List'], true)	: null,												//BOB - 24.01.2019
			'EvnReanimatCondition_HeartTones' => isset($data['EvnReanimatCondition_HeartTones']) ? $data['EvnReanimatCondition_HeartTones'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_IsHemodStab' => isset($data['EvnReanimatCondition_IsHemodStab']) ? $data['EvnReanimatCondition_IsHemodStab'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_Tongue' => isset($data['EvnReanimatCondition_Tongue']) ? $data['EvnReanimatCondition_Tongue'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_Paunch' => isset($data['EvnReanimatCondition_Paunch']) ? $data['EvnReanimatCondition_Paunch'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_PaunchTxt' => isset($data['EvnReanimatCondition_PaunchTxt']) ? $data['EvnReanimatCondition_PaunchTxt'] : null,			//BOB - 24.01.2019
			'PeristalsisType_id' => isset($data['PeristalsisType_id']) ? $data['PeristalsisType_id'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_VBD' => isset($data['EvnReanimatCondition_VBD']) ? $data['EvnReanimatCondition_VBD'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_Defecation' => isset($data['EvnReanimatCondition_Defecation']) ? $data['EvnReanimatCondition_Defecation'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_DefecationTxt' => isset($data['EvnReanimatCondition_DefecationTxt']) ? $data['EvnReanimatCondition_DefecationTxt'] : null,			//BOB - 24.01.2019
			'LimbImmobilityType_id' => isset($data['LimbImmobilityType_id']) ? $data['LimbImmobilityType_id'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_MonopLoc' => isset($data['EvnReanimatCondition_MonopLoc']) ? $data['EvnReanimatCondition_MonopLoc'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_mrc' => isset($data['EvnReanimatCondition_mrc']) ? $data['EvnReanimatCondition_mrc'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_MeningSign' => isset($data['EvnReanimatCondition_MeningSign']) ? $data['EvnReanimatCondition_MeningSign'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_MeningSignTxt' => isset($data['EvnReanimatCondition_MeningSignTxt']) ? $data['EvnReanimatCondition_MeningSignTxt'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_glasgow' => isset($data['EvnReanimatCondition_glasgow']) ? $data['EvnReanimatCondition_glasgow'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_four' => isset($data['EvnReanimatCondition_four']) ? $data['EvnReanimatCondition_four'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_SyndromeTxt' => isset($data['EvnReanimatCondition_SyndromeTxt']) ? $data['EvnReanimatCondition_SyndromeTxt'] : null,			//BOB - 24.01.2019
			'EvnReanimatCondition_Doctor' => isset($data['EvnReanimatCondition_Doctor']) ? $data['EvnReanimatCondition_Doctor'] : null,			//BOB - 24.01.2019
			'pmUser_id' => $data['pmUser_id']

		);
		//BOB - 12.09.2018 
		
		
		
		
		
		if($params['EvnReanimatCondition_id'] == null) {

			$query = "
				declare
				 @EvnReanimatCondition_id bigint = null,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null; 

				exec   dbo.p_EvnReanimatCondition_ins
				 @EvnReanimatCondition_id = @EvnReanimatCondition_id output,
				 @EvnReanimatCondition_pid = :EvnReanimatCondition_pid , 
				 @Lpu_id = :Lpu_id,
				 @Server_id = :Server_id, 

				 @ReanimStageType_id = :ReanimStageType_id,
				 @EvnReanimatCondition_Complaint = :EvnReanimatCondition_Complaint,
				 @ReanimConditionType_id = :ReanimConditionType_id,
				 @SkinType_id  = :SkinType_id,
				 @EvnReanimatCondition_SkinTxt = :EvnReanimatCondition_SkinTxt,
				 @ConsciousType_id = :ConsciousType_id,
				 @BreathingType_id = :BreathingType_id,
				 @EvnReanimatCondition_IVLapparatus = :EvnReanimatCondition_IVLapparatus,
				 @EvnReanimatCondition_IVLparameter = :EvnReanimatCondition_IVLparameter,
				 @EvnReanimatCondition_Auscultatory = :EvnReanimatCondition_Auscultatory,
				 @HeartTonesType_id = :HeartTonesType_id,
				 @HemodynamicsType_id = :HemodynamicsType_id,
				 @EvnReanimatCondition_Pressure = :EvnReanimatCondition_Pressure,
				 @EvnReanimatCondition_HeartFrequency = :EvnReanimatCondition_HeartFrequency,
				 @EvnReanimatCondition_StatusLocalis = :EvnReanimatCondition_StatusLocalis,
				 @AnalgesiaType_id = :AnalgesiaType_id,
				 @EvnReanimatCondition_AnalgesiaTxt = :EvnReanimatCondition_AnalgesiaTxt,
				 @EvnReanimatCondition_Diuresis = :EvnReanimatCondition_Diuresis,
				 @UrineType_id = :UrineType_id,
				 @EvnReanimatCondition_UrineTxt = :EvnReanimatCondition_UrineTxt,
				 @EvnReanimatCondition_Conclusion = :EvnReanimatCondition_Conclusion,
				 @ReanimArriveFromType_id = :ReanimArriveFromType_id,
				 @EvnReanimatCondition_HemodynamicsTxt = :EvnReanimatCondition_HemodynamicsTxt,
				 @EvnReanimatCondition_NeurologicStatus = :EvnReanimatCondition_NeurologicStatus,
				 @EvnReanimatCondition_sofa = :EvnReanimatCondition_sofa,								
				 @EvnReanimatCondition_apache = :EvnReanimatCondition_apache,							
				 @EvnReanimatCondition_Saturation = :EvnReanimatCondition_Saturation,
				 @EvnReanimatCondition_OxygenFraction = :EvnReanimatCondition_OxygenFraction,
				 @EvnReanimatCondition_OxygenPressure = :EvnReanimatCondition_OxygenPressure,
				 @EvnReanimatCondition_PaOFiO = :EvnReanimatCondition_PaOFiO,
				 @EvnReanimatCondition_Temperature = :EvnReanimatCondition_Temperature,
				 @EvnReanimatCondition_InfusionVolume = :EvnReanimatCondition_InfusionVolume,
				 @EvnReanimatCondition_DiuresisVolume = :EvnReanimatCondition_DiuresisVolume,
				 @EvnReanimatCondition_CollectiveSurvey = :EvnReanimatCondition_CollectiveSurvey,
				 
				 @EvnReanimatCondition_SyndromeType = :EvnReanimatCondition_SyndromeType,
				 @EvnReanimatCondition_ConsTxt = :EvnReanimatCondition_ConsTxt,
				 @SpeechDisorderType_id = :SpeechDisorderType_id,
				 @EvnReanimatCondition_rass = :EvnReanimatCondition_rass,
				 @EvnReanimatCondition_Eyes = :EvnReanimatCondition_Eyes,
				 @EvnReanimatCondition_WetTurgor = :EvnReanimatCondition_WetTurgor,
				 @EvnReanimatCondition_waterlow = :EvnReanimatCondition_waterlow,
				 @SkinType_mid = :SkinType_mid,
				 @EvnReanimatCondition_MucusTxt = :EvnReanimatCondition_MucusTxt,
				 @EvnReanimatCondition_IsMicrocDist = :EvnReanimatCondition_IsMicrocDist,
				 @EvnReanimatCondition_IsPeriphEdem = :EvnReanimatCondition_IsPeriphEdem,
				 @EvnReanimatCondition_Reflexes = :EvnReanimatCondition_Reflexes,
				 @EvnReanimatCondition_BreathFrequency = :EvnReanimatCondition_BreathFrequency,
				 @EvnReanimatCondition_HeartTones = :EvnReanimatCondition_HeartTones,
				 @EvnReanimatCondition_IsHemodStab = :EvnReanimatCondition_IsHemodStab,
				 @EvnReanimatCondition_Tongue = :EvnReanimatCondition_Tongue,
				 @EvnReanimatCondition_Paunch = :EvnReanimatCondition_Paunch,
				 @EvnReanimatCondition_PaunchTxt = :EvnReanimatCondition_PaunchTxt,
				 @PeristalsisType_id = :PeristalsisType_id,
				 @EvnReanimatCondition_VBD = :EvnReanimatCondition_VBD,
				 @EvnReanimatCondition_Defecation = :EvnReanimatCondition_Defecation,
				 @EvnReanimatCondition_DefecationTxt = :EvnReanimatCondition_DefecationTxt,
				 @LimbImmobilityType_id = :LimbImmobilityType_id,
				 @EvnReanimatCondition_MonopLoc = :EvnReanimatCondition_MonopLoc,
				 @EvnReanimatCondition_mrc = :EvnReanimatCondition_mrc,
				 @EvnReanimatCondition_MeningSign = :EvnReanimatCondition_MeningSign,
				 @EvnReanimatCondition_MeningSignTxt = :EvnReanimatCondition_MeningSignTxt,
				 
				 @EvnReanimatCondition_glasgow = :EvnReanimatCondition_glasgow,
				 @EvnReanimatCondition_four = :EvnReanimatCondition_four,
				 @EvnReanimatCondition_SyndromeTxt = :EvnReanimatCondition_SyndromeTxt,
				 @EvnReanimatCondition_Doctor = :EvnReanimatCondition_Doctor,


				 @PersonEvn_id =:PersonEvn_id,
				 @EvnReanimatCondition_setDT =:EvnReanimatCondition_setDT, 
				 @EvnReanimatCondition_disDT =:EvnReanimatCondition_disDT, 

				 @pmUser_id = :pmUser_id,
				 @Error_Code = @Error_Code output,
				 @Error_Message = @Error_Message output;

				select @EvnReanimatCondition_id as EvnReanimatCondition_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnReanimatCondition_ins exec query: ', getDebugSql($query, $params));
			
			//	 --@NutritiousType_id = :NutritiousType_id,									  --BOB - 23.09.2019 - убрано из вызова процедуры
			//	 --@EvnReanimatCondition_NutritiousTxt = :EvnReanimatCondition_NutritiousTxt, --BOB - 23.09.2019 - убрано из вызова процедуры
			

			//echo '<pre>' . print_r($query, 1) . '</pre>'; 
			//echo '<pre>' . print_r($result, 1) . '</pre>'; 

			if ( !is_object($result) )
				return false;
			}
		else {
						$query = "
				declare
				 @EvnReanimatCondition_id bigint = :EvnReanimatCondition_id,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null; 

				exec   dbo.p_EvnReanimatCondition_upd
				 @EvnReanimatCondition_id = @EvnReanimatCondition_id output,
				 @EvnReanimatCondition_pid = :EvnReanimatCondition_pid , 
				 @Lpu_id = :Lpu_id,
				 @Server_id = :Server_id, 

				 @ReanimStageType_id = :ReanimStageType_id,
				 @EvnReanimatCondition_Complaint = :EvnReanimatCondition_Complaint,
				 @ReanimConditionType_id = :ReanimConditionType_id,
				 @SkinType_id  = :SkinType_id,
				 @EvnReanimatCondition_SkinTxt = :EvnReanimatCondition_SkinTxt,
				 @ConsciousType_id = :ConsciousType_id,
				 @BreathingType_id = :BreathingType_id,
				 @EvnReanimatCondition_IVLapparatus = :EvnReanimatCondition_IVLapparatus,
				 @EvnReanimatCondition_IVLparameter = :EvnReanimatCondition_IVLparameter,
				 @EvnReanimatCondition_Auscultatory = :EvnReanimatCondition_Auscultatory,
				 @HeartTonesType_id = :HeartTonesType_id,
				 @HemodynamicsType_id = :HemodynamicsType_id,
				 @EvnReanimatCondition_Pressure = :EvnReanimatCondition_Pressure,
				 @EvnReanimatCondition_HeartFrequency = :EvnReanimatCondition_HeartFrequency,
				 @EvnReanimatCondition_StatusLocalis = :EvnReanimatCondition_StatusLocalis,
				 @AnalgesiaType_id = :AnalgesiaType_id,
				 @EvnReanimatCondition_AnalgesiaTxt = :EvnReanimatCondition_AnalgesiaTxt,
				 @EvnReanimatCondition_Diuresis = :EvnReanimatCondition_Diuresis,
				 @UrineType_id = :UrineType_id,
				 @EvnReanimatCondition_UrineTxt = :EvnReanimatCondition_UrineTxt,
				 @EvnReanimatCondition_Conclusion = :EvnReanimatCondition_Conclusion,
				 @ReanimArriveFromType_id = :ReanimArriveFromType_id,
				 @EvnReanimatCondition_HemodynamicsTxt = :EvnReanimatCondition_HemodynamicsTxt,
				 @EvnReanimatCondition_NeurologicStatus = :EvnReanimatCondition_NeurologicStatus,
				 @EvnReanimatCondition_sofa = :EvnReanimatCondition_sofa,								
				 @EvnReanimatCondition_apache = :EvnReanimatCondition_apache,							
				 @EvnReanimatCondition_Saturation = :EvnReanimatCondition_Saturation,
				 @EvnReanimatCondition_OxygenFraction = :EvnReanimatCondition_OxygenFraction,
				 @EvnReanimatCondition_OxygenPressure = :EvnReanimatCondition_OxygenPressure,
				 @EvnReanimatCondition_PaOFiO = :EvnReanimatCondition_PaOFiO,
				 @EvnReanimatCondition_Temperature = :EvnReanimatCondition_Temperature,
				 @EvnReanimatCondition_InfusionVolume = :EvnReanimatCondition_InfusionVolume,
				 @EvnReanimatCondition_DiuresisVolume = :EvnReanimatCondition_DiuresisVolume,
				 @EvnReanimatCondition_CollectiveSurvey = :EvnReanimatCondition_CollectiveSurvey,

				 @EvnReanimatCondition_SyndromeType = :EvnReanimatCondition_SyndromeType,
				 @EvnReanimatCondition_ConsTxt = :EvnReanimatCondition_ConsTxt,
				 @SpeechDisorderType_id = :SpeechDisorderType_id,
				 @EvnReanimatCondition_rass = :EvnReanimatCondition_rass,
				 @EvnReanimatCondition_Eyes = :EvnReanimatCondition_Eyes,
				 @EvnReanimatCondition_WetTurgor = :EvnReanimatCondition_WetTurgor,
				 @EvnReanimatCondition_waterlow = :EvnReanimatCondition_waterlow,
				 @SkinType_mid = :SkinType_mid,
				 @EvnReanimatCondition_MucusTxt = :EvnReanimatCondition_MucusTxt,
				 @EvnReanimatCondition_IsMicrocDist = :EvnReanimatCondition_IsMicrocDist,
				 @EvnReanimatCondition_IsPeriphEdem = :EvnReanimatCondition_IsPeriphEdem,
				 @EvnReanimatCondition_Reflexes = :EvnReanimatCondition_Reflexes,
				 @EvnReanimatCondition_BreathFrequency = :EvnReanimatCondition_BreathFrequency,
				 @EvnReanimatCondition_HeartTones = :EvnReanimatCondition_HeartTones,
				 @EvnReanimatCondition_IsHemodStab = :EvnReanimatCondition_IsHemodStab,
				 @EvnReanimatCondition_Tongue = :EvnReanimatCondition_Tongue,
				 @EvnReanimatCondition_Paunch = :EvnReanimatCondition_Paunch,
				 @EvnReanimatCondition_PaunchTxt = :EvnReanimatCondition_PaunchTxt,
				 @PeristalsisType_id = :PeristalsisType_id,
				 @EvnReanimatCondition_VBD = :EvnReanimatCondition_VBD,
				 @EvnReanimatCondition_Defecation = :EvnReanimatCondition_Defecation,
				 @EvnReanimatCondition_DefecationTxt = :EvnReanimatCondition_DefecationTxt,
				 @LimbImmobilityType_id = :LimbImmobilityType_id,
				 @EvnReanimatCondition_MonopLoc = :EvnReanimatCondition_MonopLoc,
				 @EvnReanimatCondition_mrc = :EvnReanimatCondition_mrc,
				 @EvnReanimatCondition_MeningSign = :EvnReanimatCondition_MeningSign,
				 @EvnReanimatCondition_MeningSignTxt = :EvnReanimatCondition_MeningSignTxt,

				 @EvnReanimatCondition_glasgow = :EvnReanimatCondition_glasgow,
				 @EvnReanimatCondition_four = :EvnReanimatCondition_four,
				 @EvnReanimatCondition_SyndromeTxt = :EvnReanimatCondition_SyndromeTxt,
				 @EvnReanimatCondition_Doctor = :EvnReanimatCondition_Doctor,

				 @PersonEvn_id =:PersonEvn_id,
				 @EvnReanimatCondition_setDT = :EvnReanimatCondition_setDT, 
				 @EvnReanimatCondition_disDT = :EvnReanimatCondition_disDT, 

				 @pmUser_id = :pmUser_id,
				 @Error_Code = @Error_Code output,
				 @Error_Message = @Error_Message output;

				select @EvnReanimatCondition_id as EvnReanimatCondition_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnReanimatCondition_upd exec query: ', getDebugSql($query, $params));		
			
			if ( !is_object($result) )
				return false;
		}		
		
		$EvnScaleResult = $result->result('array');

		//		 --@NutritiousType_id = :NutritiousType_id,										--BOB - 23.09.2019 - убрано из вызова процедуры
		//		 --@EvnReanimatCondition_NutritiousTxt = :EvnReanimatCondition_NutritiousTxt,	--BOB - 23.09.2019 - убрано из вызова процедуры
		
		
		if (!(($EvnScaleResult[0]['EvnReanimatCondition_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null))){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
			return $Response;
		}
		
		//echo '<pre>' . print_r($EvnScaleResult, 1) . '</pre>'; 
		//log_message('debug', 'BOB_0'.print_r($EvnScaleResult, 1));
		
		//BOB - 03.11.2018
		//СОХРАНЕНИЕ АУСКУЛЬТАТИВНОГО
		if (isset($params['BreathAuscult_List'])){
			
			foreach ($params['BreathAuscult_List'] as $BreathAuscult) {

				$BreathAuscult['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
				$BreathAuscult['EvnReanimatCondition_id'] =  $EvnScaleResult[0]['EvnReanimatCondition_id'];
				
				//log_message('debug', 'BOB_0'.print_r($BreathAuscult, 1));
			
				switch ($BreathAuscult['BA_RecordStatus']) {
					case 0:
						//добавление нового 
						$query = "
							declare
								@BreathAuscultative_id bigint = null,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec dbo.p_BreathAuscultative_ins
								@BreathAuscultative_id = @BreathAuscultative_id output,
								@EvnReanimatCondition_id = :EvnReanimatCondition_id,
								@SideType_id = :SideType_id,
								@BreathAuscultative_Auscult = :BreathAuscultative_Auscult,
								@BreathAuscultative_AuscultTxt = :BreathAuscultative_AuscultTxt,
								@BreathAuscultative_Rale = :BreathAuscultative_Rale,
								@BreathAuscultative_RaleTxt = :BreathAuscultative_RaleTxt,
								@BreathAuscultative_IsPleuDrain = :BreathAuscultative_IsPleuDrain,
								@BreathAuscultative_PleuDrainTxt = :BreathAuscultative_PleuDrainTxt,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @BreathAuscultative_id as BreathAuscultative_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $BreathAuscult);
						sql_log_message('error', 'p_BreathAuscultative_ins exec query: ', getDebugSql($query, $BreathAuscult));
						break;
					case 2:
						//изменение 
						$query = "
							declare
								@BreathAuscultative_id bigint = :BreathAuscultative_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec   dbo.p_BreathAuscultative_upd
								@BreathAuscultative_id = @BreathAuscultative_id output,
								@EvnReanimatCondition_id = :EvnReanimatCondition_id,
								@SideType_id = :SideType_id,
								@BreathAuscultative_Auscult = :BreathAuscultative_Auscult,
								@BreathAuscultative_AuscultTxt = :BreathAuscultative_AuscultTxt,
								@BreathAuscultative_Rale = :BreathAuscultative_Rale,
								@BreathAuscultative_RaleTxt = :BreathAuscultative_RaleTxt,
								@BreathAuscultative_IsPleuDrain = :BreathAuscultative_IsPleuDrain,
								@BreathAuscultative_PleuDrainTxt = :BreathAuscultative_PleuDrainTxt,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							select @BreathAuscultative_id as BreathAuscultative_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $BreathAuscult);
						sql_log_message('error', 'p_BreathAuscultative_upd exec query: ', getDebugSql($query, $BreathAuscult));
						
						break;
				}
				$BreathAuscultResult = $result->result('array');
				//log_message('debug', 'BOB_0'.print_r($BreathAuscultResult, 1));
				//log_message('debug', 'BOB_0'.print_r((!(($BreathAuscultResult[0]['BreathAuscultative_id']) && ($BreathAuscultResult[0]['Error_Code'] == null) && ($BreathAuscultResult[0]['Error_Message'] == null))), 1));


				if (!(($BreathAuscultResult[0]['BreathAuscultative_id']) && ($BreathAuscultResult[0]['Error_Code'] == null) && ($BreathAuscultResult[0]['Error_Message'] == null))){
					$Response['success'] = 'false';
					$Response['Error_Msg'] = $BreathAuscultResult[0]['Error_Code'].' '.$BreathAuscultResult[0]['Error_Message'];
					return $Response;
				}
				
			}
		
		}
		
		
		
		return $Response;
		
		
	}

	/**
	 * BOB - 21.05.2018
	 * удаление записи регулярного наблюдения состояния
	 */
	function EvnReanimatCondition_Del($data) {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$queryParams = array('EvnReanimatCondition_id' => $data['EvnReanimatCondition_id'],
							  'pmUser_id' => $pmUser_id);
		
		$query = "
			select BreathAuscultative_id from BreathAuscultative
			where EvnReanimatCondition_id = :EvnReanimatCondition_id
		";
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) )
			return false;

		$BreathAuscultative = $result->result('array');
		
		foreach ($BreathAuscultative as $BreathAuscult) {
			$queryParams['BreathAuscultative_id'] = $BreathAuscult['BreathAuscultative_id'];
			
			$query = "
				declare

				 @BreathAuscultative_id bigint = :BreathAuscultative_id,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null;

				exec dbo.p_BreathAuscultative_del
				 @BreathAuscultative_id = @BreathAuscultative_id,
				 @pmUser_id = :pmUser_id,
				 @Error_Code = @Error_Code output,
				 @Error_Message  = @Error_Message output;

				select @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			
			$result = $this->db->query($query, $queryParams);
			//sql_log_message('error', 'EvnReanimatPeriod_model EvnReanimatCondition_Del query: ', getDebugSql($query, $queryParams));
			if ( !is_object($result) )
				return false;
		}
		
		$query = "
			declare

			 @EvnReanimatCondition_id bigint = :EvnReanimatCondition_id,
			 @pmUser_id bigint = :pmUser_id,
			 @Error_Code int = null,
			 @Error_Message varchar(4000) = null; 

			exec dbo.p_EvnReanimatCondition_del
			 @EvnReanimatCondition_id,
			 @pmUser_id,
			 @Error_Code output,
			 @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;		
		";
		
		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model EvnReanimatCondition_Del query: ', getDebugSql($query, $queryParams));
		
		if ( !is_object($result) )
			return false;

		$EvnScaleResult = $result->result('array');

		if (($EvnScaleResult[0]['Error_Code'] != null) || ($EvnScaleResult[0]['Error_Message'] != null)){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
		return $Response;
	}


	//	/**
	//	 * BOB - 02.10.2017
	//	 * извлечение данных для печати дневника/поступления
	//	 */
	//	function getDataToNotePrint($data) {
	//
	//		$queryParams = array('EvnReanimatAction_pid' => $data['EvnReanimatAction_pid'], 
	//						   	 'EvnReanimatAction_setDT' => $data['EvnReanimatCondition_setDT']);
	//		
	//		$query = "
	//			select cast(EvnReanimatAction_ObservValue as int) as EvnReanimatAction_ObservValue from v_EvnReanimatAction with (nolock)
	//			 where EvnReanimatAction_pid = :EvnReanimatAction_pid
	//			   and ReanimatActionType_SysNick = 'observation_saturation'
	//			   and EvnReanimatAction_setDT <= :EvnReanimatAction_setDT
	//			 order by EvnReanimatAction_setDT desc		";
	//		$NoteSpO2 = $this->db->query($query, $queryParams);
	//		
	//		if ( !is_object($NoteSpO2) )
	//			return false;
	//		
	//		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
	//
	//
	//		$query = "
	//			select ERA.EvnReanimatAction_id, ERA.EvnReanimatAction_setDT, ERA.EvnReanimatAction_disDT, RNT.NutritiousType_id, RNT.NutritiousType_Name as ReanimatNutritionType_Name  from 
	//			v_EvnReanimatAction ERA with (nolock)
	//			inner join dbo.NutritiousType RNT with (nolock) on RNT.NutritiousType_id = ERA.NutritiousType_id
	//			 where ERA.EvnReanimatAction_pid = :EvnReanimatAction_pid
	//			   and ERA.ReanimatActionType_SysNick = 'nutrition'
	//			   and ERA.EvnReanimatAction_setDT <= :EvnReanimatAction_setDT
	//			   and (ERA.EvnReanimatAction_disDT > :EvnReanimatAction_setDT or ERA.EvnReanimatAction_disDT is null)
	//			 order by ERA.EvnReanimatAction_setDT desc	";	
	//		$Nutrition = $this->db->query($query, $queryParams);
	//
	//		
	//		if ( !is_object($Nutrition) )
	//			return false;
	//		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));
	//	
	//		
	//
	//		$ReturnObject = array(	'NoteSpO2' => $NoteSpO2->result('array'),
	//								'NoteNutritious' => $Nutrition->result('array'),
	//                               'Message' => '');
	//
	//		
	//		//log_message('debug', 'BOB_0'.print_r($ReturnObject, 1));
	//		return $ReturnObject;
	//		
	//		
	//	}
	/**
	* Возвращает антропометрические данные конкретного пациента за определённый период
	* BOB - 24.01.2019
	*/
	function getAntropometrData($data)
	{

		//$data['Evn_setDate'] .= ' '.$data['Evn_setTime'].':00';
		if(($data['Evn_disDate'] == '') || $data['Evn_disTime'] == ''){
			$data['Evn_disDate'] = null;
		}
		else {
			$data['Evn_disDate'] =$data['Evn_disDate']." ".$data['Evn_disTime'].":00";
		}
		
		//echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; 
		$params = array(
			'Person_id' => $data['Person_id'],
			//'Evn_setDT' => isset($data['Evn_setDate']) ? $data['Evn_setDate'] : null,
			'Evn_disDT' => isset($data['Evn_disDate']) ? $data['Evn_disDate'] : null
		);
		
		//echo '<pre>'.'  $params  ' . print_r($params, 1) . '</pre>'; 
		
		$disDT = isset($params['Evn_disDT']) ? ':Evn_disDT' : 'GetDate()';
		//echo '<pre>'.'  $disDT  ' . print_r($disDT, 1) . '</pre>'; 

		//Поиск роста
		$query = "
			select top 1 
				   convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate,
				   cast(PH.PersonHeight_Height as float) as PersonHeight_Height
			  from v_PersonHeight PH with (nolock)
			where PH.Person_id = :Person_id
			  and PH.PersonHeight_setDT < ".$disDT."
			order by PH.PersonHeight_setDT desc, PH.PersonHeight_id desc
			";
		$PersonHeight = $this->db->query($query, $params)->result('array');

		//Поиск веса и Индекс массы тела
		$query = "
			select top 1
				convert(varchar(10), PW.PersonWeight_setDT, 104) as PersonWeight_setDate,
				case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonWeight_Weight,
				case 
					when ISNULL(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null
				then
					convert(varchar(10),ROUND(cast(
						case when pw.Okei_id = 36 then
							cast(pw.PersonWeight_Weight as float) / 1000
						else
							pw.PersonWeight_Weight
						end
					as float)/POWER(0.01*cast(PH.PersonHeight_Height as float),2),2))
				else
					''
				end as Weight_Index 
			  from v_PersonWeight PW with (nolock)
					outer apply (
						select top 1 PersonHeight_Height
						from v_PersonHeight with (nolock)
						where Person_id = :Person_id
							and HeightMeasureType_id is not null
							and PersonHeight_setDT < ".$disDT." -- PW.PersonWeight_setDT
						order by PersonHeight_setDT desc, PersonHeight_id desc
					) PH
			 where PW.Person_id = :Person_id
			   and PW.PersonWeight_setDT < ".$disDT."    
			 order by PW.PersonWeight_setDT desc, PW.PersonWeight_id desc
			";
		$PersonWeight = $this->db->query($query, $params)->result('array');


		$ReturnObject = array(	
			'PersonHeight' => $PersonHeight,
			'PersonWeight' => $PersonWeight
		);

		return $ReturnObject;

	}

	 /**
     * Возвращает данные о дыхании аускультативно
	 * BOB - 24.01.2019
     */
    function GetBreathAuscultative($data)
    {
		$query = "
			select BreathAuscultative_id
					,EvnReanimatCondition_id
					,BA.SideType_id
					,ST.SideType_SysNick
					,BreathAuscultative_Auscult
					,BreathAuscultative_AuscultTxt
					,BreathAuscultative_Rale
					,BreathAuscultative_RaleTxt
					,BreathAuscultative_IsPleuDrain
					,BreathAuscultative_PleuDrainTxt
					,2 as BA_RecordStatus 
			  from v_BreathAuscultative BA with(nolock)
			  inner join SideType ST  with(nolock) on BA.SideType_id = ST.SideType_id
			  where EvnReanimatCondition_id = :EvnReanimatCondition_id
			  order by SideType_id
			";
		$result = $this->db->query($query, array('EvnReanimatCondition_id' => $data['EvnReanimatCondition_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
    }


	//ПЕРЕВОД В РЕАНИМАЦИЮ**********************************************************************************************************************************************

	/**
     * BOB - 21.03.2017
	 * Формирование списка пациентов в отделениях, относящихся к реанимационной службе
	 */
    function getReanimSectionPatientList($data) {
         
		//BOB - 12.09.2018
		$params = array(
			'MedService_id' => $data['MedService_id'],						
			'Lpu_id' => $data['Lpu_id'],
		);
		//Выборка отделений привязанных к службе реанимации
		$query = "
				select LS.LpuSection_id 
				  from dbo.v_MedServiceSection MSS  with(nolock)
						inner join dbo.v_LpuSection LS  with(nolock) on MSS.LpuSection_id = LS.LpuSection_id
				 where MSS.MedService_id = :MedService_id"
		;    
		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		//sql_log_message('error', 'from dbo.v_MedServiceSection exec query: ', getDebugSql($query, $params));		
                
		if (is_object($result)) {
			$resp = $result->result('array');
			//если есть отделения прикреплённые к службе реанимации
			if (count($resp) > 0) {
				//Выборка не повторяющихся id персон, прикреплённым к отделениям, прикреплённым к службе реанимации
				$query = "
						select distinct ES.Person_id 
						  from dbo.v_MedServiceSection MSS  with(nolock) 
								inner join dbo.v_LpuSection LS  with(nolock) on MSS.LpuSection_id = LS.LpuSection_id
								inner join dbo.v_EvnSection ES  with(nolock) on LS.LpuSection_id = ES.LpuSection_id
						 where MSS.MedService_id = :MedService_id
						   and ES.EvnSection_setDT <= SYSDATETIME() 
						   and (ES.EvnSection_disDT is null or ES.EvnSection_disDT > SYSDATETIME())"
						;                            
				$result = $this->db->query($query, $params); //BOB - 12.09.2018
				//sql_log_message('error', 'from dbo.v_MedServiceSection 2 exec query: ', getDebugSql($query, $params));		
			} else {  //нет отделений прикреплённые к службе реанимации
				//Выборка не повторяющихся id персон, прикреплённым к отделениям данного стационара
				$query = "
						SELECT  distinct ES.Person_id 
						  FROM v_LpuSection LS with (nolock)
								inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
								inner join dbo.v_EvnSection ES with (nolock) on LS.LpuSection_id = ES.LpuSection_id
						 WHERE LS.Lpu_id = :Lpu_id
						   and LU.LpuUnitType_id = 1
						   and not exists (select top 1 1 from dbo.v_MedServiceSection MSS with (nolock) where MSS.LpuSection_id = LS.LpuSection_id)
						   and ES.EvnSection_setDT <= SYSDATETIME() 
						   and (ES.EvnSection_disDT is null or ES.EvnSection_disDT > SYSDATETIME())"
						;  

				//$result = $this->db->query($query);
				$result = $this->db->query($query, $params); //BOB - 12.09.2018
				//sql_log_message('error', 'from v_LpuSection exec query: ', getDebugSql($query, $params));		
			}

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					//собираю $Person_id в строку с разделителями - запятыми
					$Person_ids = '';
					foreach ($resp as $rows) {
						$Person_ids .= $rows['Person_id'].',';
					}

					$Person_ids = substr($Person_ids, 0, strlen($Person_ids) - 1);
					return $Person_ids;
				}
				else return 'null';
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
    
	
	/**
     * BOB - 22.03.2017
	 * Перевод пациента в реанимацию из АРМ-ов стационара и реаниматора
     * проверка не находится ли пациент уже в реанимации
     * формирование реанимационного периода
	 */
    function moveToReanimation($data) {
		//echo '<pre> $data1 '. print_r($data , 1) . '</pre>'; //BOB - 14.03.2017 

		$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');
		
		//возвращаемый объект
		$ReturnObject = array(
			'Status' => '',
			'Message' => '',
			'EvnReanimatPeriod_id' => '',
			'fork' => 0
		);

		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$LpuSection_id = 0; //код отделения
		$EvnPS_id = 0;      //код карты выбывшего из стационара (ПС)
		$EvnSection_id = 0; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
		$MedService_id = 0; //код службы реанимации

		//BOB - 12.09.2018
		$params = array(
			'Person_id' => isset($data['Person_id']) ? $data['Person_id'] : null,
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null
		);
		
		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$query = "
			select *
			from v_EvnReanimatPeriod ERP  with (nolock) 
			where
				ERP.Person_id = :Person_id
				and ERP.EvnReanimatPeriod_setDate <= SYSDATETIME()
				and ERP.EvnReanimatPeriod_disDate is null
		";    
		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		//sql_log_message('error', 'from v_EvnReanimatPeriod exec query: ', getDebugSql($query, $params));		


		if (!is_object($result)) {
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
			return $ReturnObject;
		}
                
		$resp = $result->result('array');

		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject['Status'] = 'AlreadyInReanimation';
			$ReturnObject['Message'] = 'Данный пациент уже находится в реанимации';
			return $ReturnObject;                            
		} 
                                
		// из АРМ стационара: 
		if ($data['ARMType'] == 'stac') {
			// поиск реанимационной службы, она не передана в параметрах   
			
			//BOB - 19.06.2019
			$query = "
				select
					MS.MedService_id 
				from dbo.v_MedService MS with (nolock) 
					inner join dbo.MedServiceType MST  with (nolock) on MS.MedServiceType_id = MST.MedServiceType_id
				where
					MS.Lpu_id = :Lpu_id  
					and MS.MedService_endDT is null
					and MST.MedServiceType_SysNick = 'reanimation'			
			";

			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'from v_EvnReanimatPeriod exec query: ', getDebugSql($query, $params));		

			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
				return $ReturnObject;
			}

			$resp = $result->result('array');

			//BOB - 19.06.2019
			//если не найдены службы реанимации у данной МО
			if (count($resp) == 0) {
				$ReturnObject['Status'] = 'NoReanimatMedService';
				$ReturnObject['Message'] = 'В данной МО нет ни одной службы с типом Реанимация!';
				return $ReturnObject;
			}
			else if (count($resp) > 1) {
				$ReturnObject['Status'] = 'ManyReanimatMedService';
				$ReturnObject['Message'] = 'В МО больше одной служб реанимации';
				$ReturnObject['Server_id'] = $data['Server_id'];
				$ReturnObject['Person_id'] = $data['Person_id'];
				$ReturnObject['PersonEvn_id'] = $data['PersonEvn_id'];
				$ReturnObject['EvnPS_id'] = $data['EvnPS_id'];
				$ReturnObject['EvnSection_id'] = $data['EvnSection_id'];
				$ReturnObject['LpuSection_id'] = $data['LpuSection_id'];
				return $ReturnObject;
			}

			$LpuSection_id = $data['LpuSection_id']; //код отделения
			$EvnPS_id = $data['EvnPS_id'];      //код карты выбывшего из стационара (ПС)
			$EvnSection_id = $data['EvnSection_id']; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
			$MedService_id = $resp[0]['MedService_id']; //код службы реанимации
		} 
                        
                // из АРМ реаниматолога: 
		if ($data['ARMType'] == 'reanimation') {
			//echo '<pre> $data '. print_r($data['ARMType'].'~LpuSection_id='.$LpuSection_id.'~EvnPS_id='.$EvnPS_id.'~EvnSection_id='.$EvnSection_id.'~MedService_id='.$MedService_id , 1) . '</pre>'; //BOB - 14.03.2017 

			//поиск карты выбывшего из стационара  //BOB - 19.04.2017   -  добавил
                                         
			$query = "
					select EPS.EvnPS_NumCard, EPS.EvnPS_id, EPS.EvnPS_setDT, EPS.LpuSection_id, ES.EvnSection_id  
					  from dbo.v_EvnPS EPS with(nolock) 
					 inner join dbo.v_EvnSection ES with(nolock) on ES.EvnSection_pid = EPS.EvnPS_id
					 where EPS.Lpu_id = :Lpu_id
				       and EPS.Person_id = :Person_id
				       and EPS.EvnPS_setDate <= GETDATE()
					   and EPS.EvnPS_disDate is null
					   and EPS.LpuSection_id is not null
					   and ES.EvnSection_setDate <= GETDATE()
					   and ES.EvnSection_disDate is null
					 order by EPS.EvnPS_setDate desc, ES.EvnSection_setDate desc"
					;    
			$result = $this->db->query($query, $params); //BOB - 12.09.2018
			//sql_log_message('error', 'from v_EvnPS exec query: ', getDebugSql($query, $params));		

			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
				return $ReturnObject;
			}

			$resp = $result->result('array');

			//если найдена больше чем одна карта выбывшего из стационара
			if (count($resp) > 1) {
				$ReturnObject['Status'] = 'ManyEvnPS';
				$ReturnObject['Message'] = 'У пациента несколько карт выбывшего из стационара';
				$ReturnObject['Server_id'] = $data['Server_id'];
				$ReturnObject['Person_id'] = $data['Person_id'];
				$ReturnObject['PersonEvn_id'] = $data['PersonEvn_id'];

				return $ReturnObject;
			}                                            

			$LpuSection_id = $resp[0]['LpuSection_id']; //код отделения
			$EvnPS_id = $resp[0]['EvnPS_id'];      //код карты выбывшего из стационара (ПС)
			$EvnSection_id = $resp[0]['EvnSection_id']; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
			$MedService_id = $data['MedService_id']; //код службы реанимации
		}                 
                
		// после выбора карты ВС или реанимационных медслужб если их было много:
		if ($data['ARMType'] == 'FromManyEvnPS') {

			$LpuSection_id = $data['LpuSection_id']; //код отделения
			$EvnPS_id = $data['EvnPS_id'];      //код карты выбывшего из стационара (ПС)
			$EvnSection_id = $data['EvnSection_id']; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
			$MedService_id = $data['MedService_id']; //код службы реанимации

		}
			
		// формирование реанимационного периода
		$params['LpuSection_id'] = isset($LpuSection_id) ? $LpuSection_id : null; 
		$params['EvnPS_id'] = isset($EvnPS_id) ? $EvnPS_id : null;
		$params['EvnSection_id'] = isset($EvnSection_id) ? $EvnSection_id : null; 
		$params['MedService_id'] = isset($MedService_id) ? $MedService_id : null; 
		
		$params['Server_id'] = isset($data['Server_id']) ? $data['Server_id'] : null;
		$params['PersonEvn_id'] = isset($data['PersonEvn_id']) ? $data['PersonEvn_id'] : null;
		$params['pmUser_id'] = isset($pmUser_id) ? $pmUser_id : null;

		$query = "
				declare

					 @EvnReanimatPeriod_id bigint,
					 @EvnReanimatPeriod_insDT  datetime = GETDATE(),
					 @Error_Code int,
					 @Error_Message varchar(4000), 
					 @ReanimatAgeGroup_id bigint = null,
					 @getDT datetime = cast(cast(GetDate() as date) as datetime);
								  
					 select top 1 
					 @ReanimatAgeGroup_id = case 
						 when dateadd(DAY, -29, @getDT)  < PS.Person_BirthDay then 1
						 when dateadd(DAY, -29, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -1, @getDT) < PS.Person_BirthDay then 2
						 when dateadd(YEAR, -1, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -4, @getDT) < PS.Person_BirthDay then 3
						 when dateadd(YEAR, -4, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -18, @getDT) < PS.Person_BirthDay then 4
						 else 5
					 end
					 from dbo.v_PersonState PS  with (nolock)
					 inner join PersonEvn PE with (nolock) on PE.Person_id = PS.Person_id
					 where PE.PersonEvn_id=:PersonEvn_id;

				exec   dbo.p_EvnReanimatPeriod_ins
					 @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
					 @EvnReanimatPeriod_pid = :EvnSection_id, 
					 @EvnReanimatPeriod_rid = :EvnPS_id, 
					 @Lpu_id = :Lpu_id, 
					 @Server_id = :Server_id, 
					 @MedService_id = :MedService_id,
					 @LpuSection_id = :LpuSection_id,
					 @ReanimResultType_id  = null,
					 @ReanimReasonType_id  = 1,
					 @LpuSectionBedProfile_id = null, 
					 @ReanimatAgeGroup_id = @ReanimatAgeGroup_id,
					 @PersonEvn_id = :PersonEvn_id, 
					 @EvnReanimatPeriod_setDT = @EvnReanimatPeriod_insDT, 
					 @EvnReanimatPeriod_disDT = null, 
					 @EvnReanimatPeriod_didDT = null, 

					 @EvnReanimatPeriod_insDT = null,
					 @EvnReanimatPeriod_updDT = null,
					 @EvnReanimatPeriod_Index = null, 
					 @EvnReanimatPeriod_Count = null, 
					 @Morbus_id = null, 
					 @EvnReanimatPeriod_IsSigned = null, 
					 @pmUser_signID = null, 
					 @EvnReanimatPeriod_signDT = null, 
					 @EvnStatus_id = null, 
					 @EvnReanimatPeriod_statusDate = null,
					 @isReloadCount = null,
					 @pmUser_id = :pmUser_id,
					 @Error_Code = @Error_Code output,
					 @Error_Message = @Error_Message output;

				select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
				";

		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		//sql_log_message('error', 'p_EvnReanimatPeriod_ins exec query: ', getDebugSql($query, $params));
		
		//$queryParams = array();

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnReanimatPeriod_id']) ) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
				$ReturnObject['fork'] = 1;
			}
			else if ( !empty($response[0]['Error_Message']) ) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = $response[0]['Error_Message'];
				$ReturnObject['fork'] = 2;
			} 
			else {
				//	$arg = array('Person_id' => $data['Person_id']);
				$data['EvnSection_id'] = $EvnSection_id;
				$data['EvnReanimatPeriod_id'] = $response[0]['EvnReanimatPeriod_id'];
				$dyrdyn = $this->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
				if (!$dyrdyn) {
					$ReturnObject['Status'] = 'Oshibka';
					$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании записи регистра реанимации';
					$ReturnObject['fork'] = 3;
				}
				else {
					$ReturnObject['Status'] = $dyrdyn['Status'];
					$ReturnObject['Message'] = 'Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>'. $dyrdyn['Message'];// 'Пациент переведён в реанимацию';
					$ReturnObject['EvnReanimatPeriod_id'] = $data['EvnReanimatPeriod_id'];
					$ReturnObject['fork'] = 3;						
				}
			}
		} else { // ошибка работы с БД при запуске хранимой процедуры 
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
			$ReturnObject['fork'] = 0;
			return $ReturnObject;
		}


		return $ReturnObject;
	}
	
	
	
	/**
     * BOB - 24.03.2017
	 * Индикация нескольких карт выбывшего из стационара
     * для выбора для перевода в реанимацию
	 */
	function getToReanimationFromFewPS($data) {
		//BOB - 12.09.2018
		$params = array(
			'Person_id' => isset($data['Person_id']) ? $data['Person_id'] : null,
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'MedService_id' => isset($data['MedService_id']) ? $data['MedService_id'] : null
		);

		//BOB - 19.06.2019
		if($data['Status'] == 'ManyEvnPS'){
			$query = "
					select EPS.EvnPS_NumCard, EPS.EvnPS_id, convert(char ,EPS.EvnPS_setDate, 104) EvnPS_setDate ,  EPS.LpuSection_id, LS.LpuSection_FullName, PA.Person_Fio, ES.EvnSection_id
					  from dbo.v_EvnPS EPS  with (nolock) 
					 inner join dbo.v_EvnSection ES with(nolock) on ES.EvnSection_pid = EPS.EvnPS_id
					 inner join dbo.v_LpuSection LS with(nolock)  on EPS.LpuSection_id  = LS.LpuSection_id
					 LEFT JOIN v_Person_all PA with (nolock) on PA.Server_id = EPS.Server_id and
							PA.Person_id = EPS.Person_id and PA.PersonEvn_id = EPS.PersonEvn_id
							 where EPS.Lpu_id = :Lpu_id
							   and EPS.Person_id = :Person_id
							   and EPS.EvnPS_setDate <= GETDATE()
							   and EPS.EvnPS_disDate is null
							   and EPS.LpuSection_id is not null
							 order by EPS.EvnPS_setDate desc
			";
		} else { //$data['Status'] == 'ManyReanimatMedService'
			$query = "
					select MS.MedService_id as EvnPS_id,  ISNULL(LB.LpuBuilding_Name, '') + '/' + ISNULL(LU.LpuUnit_Name, '') + '/' + ISNULL(LS.LpuSection_Name, '') + '/' +  MS.MedService_Name as LpuSection_FullName,     
					MedService_id as EvnPS_NumCard, null as EvnPS_setDate ,  MedService_id as LpuSection_id, MedService_id as EvnSection_id  
					--,MS.MedService_Name, LS.LpuSection_Name,  LU.LpuUnit_Name, LB.LpuBuilding_Name
					 from dbo.v_MedService MS with (nolock) 
					inner join dbo.MedServiceType MST  with (nolock) on MS.MedServiceType_id = MST.MedServiceType_id
					left join v_LpuSection LS with (nolock) on MS.LpuSection_id = LS.LpuSection_id
					left join v_LpuUnit LU with (nolock) on MS.LpuUnit_id = LU.LpuUnit_id
					left join v_LpuBuilding LB with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
					where MS.Lpu_id = :Lpu_id 
					  and MS.MedService_endDT is null    -- BOB - 18.09.2019
					  and MST.MedServiceType_SysNick = 'reanimation'
			".(isset($params['MedService_id']) ? "and MS.MedService_id <> :MedService_id" : "")       ;  //BOB - 02.10.2019

		}

		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		sql_log_message('error', 'from dbo.v_EvnPS exec query: ', getDebugSql($query, $params));
		//$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
            
    } 

	/**
     * BOB - 22.03.2017
	 * Перевод пациента в реанимацию из АРМ приёмного отделения
     * проверка не находится ли пациент уже в реанимации
     * формирование реанимационного периода
	 * запись в регистр реанимации
	 * сбор реквизитов для открытия окна реанимационного периода
	 */
	function moveToReanimationFromPriem($data) {

		$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');
		
		//возвращаемый объект
		$ReturnObject = array(	
			'EvnReanimatPeriod_id' => '',
			'Status' => '',
			'Message' => '',
			'fork' => 0
		);

		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$LpuSection_id = 0; //код отделения
		$EvnSection_id = 0; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
		$MedService_id = 0; //код службы реанимации
		$EvnPS_id = $data['EvnPS_id'];      //код карты выбывшего из стационара (ПС)  //BOB - 19.06.2019

		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = array(
				'Person_id' => $data['Person_id']
				);		
                //BOB - 19.03.2018 - убираю из поиска PersonEvn_id и Server_id - поиск делается по пациенту вне зависимости от его состояния
		$query = "
			select *
			from v_EvnReanimatPeriod ERP  with (nolock) 
			where
				ERP.Person_id = :Person_id
				and ERP.EvnReanimatPeriod_setDate <= SYSDATETIME()
				and ERP.EvnReanimatPeriod_disDate is null
		";    
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
			return $ReturnObject;
		}
        //sql_log_message('error', 'moveToReanimationFromPriem: ', getDebugSql($query, $params));        
		$resp = $result->result('array');

		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject['Status'] = 'AlreadyInReanimation';
			$ReturnObject['Message'] = 'Данный пациент уже находится в реанимации';
			return $ReturnObject;                            
		} 
		
		//из АРМ приёмника  //BOB - 19.09.2019
		if ($data['ARMType'] == 'priem') {

			//находим движение в профильном отделении и код самого отделения
			$params = array('EvnPS_id' => $data['EvnPS_id']);
			$query = "
				select
					Child.EvnSection_id,
					Child.LpuSection_id
				from v_EvnPS EvnPS  with (nolock) 
					outer apply (
						select top 1
							ES.EvnSection_id, ES.LpuSection_id
						from v_EvnSection ES with (nolock)
						where
							ES.EvnSection_pid = EvnPS.EvnPS_id
							and ISNULL(ES.EvnSection_IsPriem, 1) = 1
					) Child
				where EvnPS_id = :EvnPS_id";
			
			$result = $this->db->query($query, $params);
			if (!is_object($result)) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
				return $ReturnObject;
			}

			$resp = $result->result('array');

			//если выборка пустая
			if (count($resp) == 0) {
				$ReturnObject['Status'] = 'AlreadyInReanimation';
				$ReturnObject['Message'] = 'Не найдена запись движения в профильном отделении';
				return $ReturnObject;
			}

			$LpuSection_id = $resp[0]['LpuSection_id']; //код отделения
			$EvnSection_id = $resp[0]['EvnSection_id']; //код движения пациента в отделении

			// поиск реанимационной службы, она не передана в параметрах
			$params = array('Lpu_id' => $data['Lpu_id']);

			//BOB - 19.06.2019
			$query = "
				select
					MS.MedService_id 
				from dbo.v_MedService MS with (nolock) 
					inner join dbo.MedServiceType MST  with (nolock) on MS.MedServiceType_id = MST.MedServiceType_id
				where 
					MS.Lpu_id = :Lpu_id  
					and MS.MedService_endDT is null
					and MST.MedServiceType_SysNick = 'reanimation'			
			";

			$result = $this->db->query($query, $params);

			// ошибка работы с БД при поиске карт выбывшего из стационара
			if (!is_object($result)) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
				return $ReturnObject;
			}

			$resp = $result->result('array');

			//BOB - 19.06.2019
			//если не найдены службы реанимации у данной МО
			if (count($resp) == 0) {
				$ReturnObject['Status'] = 'NoReanimatMedService';
				$ReturnObject['Message'] = 'В данной МО нет ни одной службы с типом Реанимация!';
				return $ReturnObject;
			}
			else if (count($resp) > 1) {
				$ReturnObject['Status'] = 'ManyReanimatMedService';
				$ReturnObject['Message'] = 'В МО больше одной служб реанимации';
				$ReturnObject['Server_id'] = $data['Server_id'];
				$ReturnObject['Person_id'] = $data['Person_id'];
				$ReturnObject['PersonEvn_id'] = $data['PersonEvn_id'];
				$ReturnObject['EvnPS_id'] = $data['EvnPS_id'];
				$ReturnObject['EvnSection_id'] = $EvnSection_id;
				$ReturnObject['LpuSection_id'] = $LpuSection_id;
				return $ReturnObject;
			}

			$MedService_id = $resp[0]['MedService_id']; //код службы реанимации

		}

		// после выбора реанимационных медслужб если их было много: //BOB - 19.06.2019
		if ($data['ARMType'] == 'FromManyEvnPS') {

			$LpuSection_id = $data['LpuSection_id']; //код отделения
			$EvnPS_id = $data['EvnPS_id'];      //код карты выбывшего из стационара (ПС)
			$EvnSection_id = $data['EvnSection_id']; //код движения пациента в отделении //BOB - 19.04.2017   -  добавил
			$MedService_id = $data['MedService_id']; //код службы реанимации

		}





		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				// формирование реанимационного периода
			//SQL вызова хранимой процедуры формирующей реанимационный период //BOB - 19.04.2017   -  добавил   $EvnSection_id
		$params = array(
				'EvnSection_id' => $EvnSection_id, 
				'EvnPS_id' => $EvnPS_id, //   $data['EvnPS_id'],
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'LpuSection_id' => $LpuSection_id,
				'MedService_id' => $MedService_id,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id'	=> $pmUser_id
				);		
		$query = "
				declare

					 @EvnReanimatPeriod_id bigint,
					 @EvnReanimatPeriod_insDT  datetime = GETDATE(),
					 @Error_Code int,
                     @Error_Message varchar(4000), 
                     @ReanimatAgeGroup_id bigint = null,
                     @getDT datetime = cast(cast(GetDate() as date) as datetime);
                                  
                     select top 1 
                     @ReanimatAgeGroup_id = case 
                         when dateadd(DAY, -29, @getDT)  < PS.Person_BirthDay then 1
                         when dateadd(DAY, -29, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -1, @getDT) < PS.Person_BirthDay then 2
                         when dateadd(YEAR, -1, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -4, @getDT) < PS.Person_BirthDay then 3
                         when dateadd(YEAR, -4, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -18, @getDT) < PS.Person_BirthDay then 4
                         else 5
                     end
                     from dbo.v_PersonState PS  with (nolock)
                     inner join PersonEvn PE with (nolock) on PE.Person_id = PS.Person_id
                     where PE.PersonEvn_id=:PersonEvn_id;

				exec   dbo.p_EvnReanimatPeriod_ins
					 @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
					 @EvnReanimatPeriod_pid = :EvnSection_id, 
					 @EvnReanimatPeriod_rid = :EvnPS_id, 
					 @Lpu_id = :Lpu_id, 
					 @Server_id = :Server_id, 
					 @MedService_id = :MedService_id,
					 @LpuSection_id = :LpuSection_id,
					 @ReanimResultType_id  = null,
					 @ReanimReasonType_id  = 1,
					 @LpuSectionBedProfile_id = null, 
                     @ReanimatAgeGroup_id = @ReanimatAgeGroup_id,					 

					 @PersonEvn_id = :PersonEvn_id, 
					 @EvnReanimatPeriod_setDT = @EvnReanimatPeriod_insDT, 
					 @pmUser_id = :pmUser_id,
					 @Error_Code = @Error_Code output,
					 @Error_Message = @Error_Message output;

				select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

		$result = $this->db->query($query, $params);
		
		//$queryParams = array();
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));

	
		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnReanimatPeriod_id']) ) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
				$ReturnObject['fork'] = 1;
			}
			else if (( !empty($response[0]['Error_Code']) ) || ( !empty($response[0]['Error_Msg']) )) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = $response[0]['Error_Code'].' '.$response[0]['Error_Msg'];
				$ReturnObject['fork'] = 2;
			} 
			else {
				$data['EvnSection_id'] = $EvnSection_id;
				$data['EvnReanimatPeriod_id'] = $response[0]['EvnReanimatPeriod_id'];
				$dyrdyn = $this->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
				if (!$dyrdyn) {
					$ReturnObject['Status'] = 'Oshibka';
					$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании записи регистра реанимации';
					$ReturnObject['fork'] = 3;
				}
				else {
					
					$ReturnObject['Status'] = 'DoneSuccessfully';
					$ReturnObject['Message'] = 'Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>';// 'Пациент переведён в реанимацию';
					$ReturnObject['EvnReanimatPeriod_id'] = $data['EvnReanimatPeriod_id'];
					$ReturnObject['fork'] = 4;						
				}
			}
		} else { // ошибка работы с БД при запуске хранимой процедуры 
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
			$ReturnObject['fork'] = 0;
			return $ReturnObject;
		}
		
		return($ReturnObject);
				
	}

	
	/**
     * BOB - 29.05.2018  
	 * Перевод пациента в реанимацию минуя приёмное отделене
     * проверка не находится ли пациент уже в реанимации
	 * нахождение Движения в переданнной КВС
     * формирование реанимационного периода
	 * запись в регистр реанимации
	 * сбор реквизитов для открытия окна реанимационного периода
	 * !!! пока не используется вызов спрятан
	 */
	function moveToReanimationOutPriem($data) {

		$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');
		
		//возвращаемый объект
		$ReturnObject = array(	'EvnReanimatPeriod_id' => '',
								'Status' => '',
								'Message' => '',
								'fork' => 0);

		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$LpuSection_id = 0; //код отделения
		$EvnSection_id = 0; //код движения пациента в отделении
		$MedService_id = $data['MedService_id']; //код службы реанимации 
		
		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = array(
				'Person_id' => $data['Person_id']
				);		
                //BOB - 19.03.2018 - убираю из поиска PersonEvn_id и Server_id - поиск делается по пациенту вне зависимости от его состояния
		$query = "
				select * from v_EvnReanimatPeriod ERP  with (nolock) 
				 where ERP.Person_id = :Person_id
			      and ERP.EvnReanimatPeriod_setDate <= SYSDATETIME()
				   and ERP.EvnReanimatPeriod_disDate is null"
		;    
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
			return $ReturnObject;
		}
                
		$resp = $result->result('array');

		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (count($resp) > 0) {
			$ReturnObject['Status'] = 'AlreadyInReanimation';
			$ReturnObject['Message'] = 'Данный пациент уже находится в реанимации';
			return $ReturnObject;                            
		} 
		
		//находим движение в профильном отделении и код самого отделения
		$params = array('EvnPS_id' => $data['EvnPS_id']);		
		$query = "
			select Child.EvnSection_id, Child.LpuSection_id
			  from v_EvnPS EvnPS  with (nolock) 
				   outer apply (
								select top 1
									ES.EvnSection_id, ES.LpuSection_id
								from
									v_EvnSection ES with (nolock)
								where
									ES.EvnSection_pid = EvnPS.EvnPS_id
									and ISNULL(ES.EvnSection_IsPriem, 1) = 1
							) Child
			where EvnPS_id = :EvnPS_id "
		;    
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
			return $ReturnObject;
		}
                
		$resp = $result->result('array');
			//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));

		//если выборка пустая
		if (count($resp) == 0) {
			$ReturnObject['Status'] = 'AlreadyInReanimation';
			$ReturnObject['Message'] = 'Не найдена запись движения в профильном отделении';
			return $ReturnObject;                            
		} 
		
		$LpuSection_id = $resp[0]['LpuSection_id']; //код отделения
		$EvnSection_id = $resp[0]['EvnSection_id']; //код движения пациента в отделении 
		
		
				// формирование реанимационного периода
			//SQL вызова хранимой процедуры формирующей реанимационный период 
		$params = array(
				'EvnSection_id' => $EvnSection_id, 
				'EvnPS_id' => $data['EvnPS_id'],
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'LpuSection_id' => $LpuSection_id,
				'MedService_id' => $MedService_id,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id'	=> $pmUser_id
				);		
		$query = "
				declare

					 @EvnReanimatPeriod_id bigint,
					 @EvnReanimatPeriod_insDT  datetime = GETDATE(),
					 @Error_Code int,
                     @Error_Message varchar(4000), 
                     @ReanimatAgeGroup_id bigint = null,
                     @getDT datetime = cast(cast(GetDate() as date) as datetime);
                                  
                     select top 1 
                     @ReanimatAgeGroup_id = case 
                         when dateadd(DAY, -29, @getDT)  < PS.Person_BirthDay then 1
                         when dateadd(DAY, -29, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -1, @getDT) < PS.Person_BirthDay then 2
                         when dateadd(YEAR, -1, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -4, @getDT) < PS.Person_BirthDay then 3
                         when dateadd(YEAR, -4, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -18, @getDT) < PS.Person_BirthDay then 4
                         else 5
                     end
                     from dbo.v_PersonState PS  with (nolock)
                     inner join PersonEvn PE with (nolock) on PE.Person_id = PS.Person_id
                     where PE.PersonEvn_id=:PersonEvn_id;

				exec   dbo.p_EvnReanimatPeriod_ins
					 @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
					 @EvnReanimatPeriod_pid = :EvnSection_id, 
					 @EvnReanimatPeriod_rid = :EvnPS_id, 
					 @Lpu_id = :Lpu_id, 
					 @Server_id = :Server_id, 
					 @MedService_id = :MedService_id,
					 @LpuSection_id = :LpuSection_id,
					 @ReanimResultType_id  = null,
					 @ReanimReasonType_id  = 1,
					 @LpuSectionBedProfile_id = null, 
                     @ReanimatAgeGroup_id = @ReanimatAgeGroup_id,

					 @PersonEvn_id = :PersonEvn_id, 
					 @EvnReanimatPeriod_setDT = @EvnReanimatPeriod_insDT, 
					 @pmUser_id = :pmUser_id,
					 @Error_Code = @Error_Code output,
					 @Error_Message = @Error_Message output;

				select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

		$result = $this->db->query($query, $params);
		
		//$queryParams = array();
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));

	
		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 || empty($response[0]['EvnReanimatPeriod_id']) ) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
				$ReturnObject['fork'] = 1;
			}
			else if (( !empty($response[0]['Error_Code']) ) || ( !empty($response[0]['Error_Msg']) )) {
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = $response[0]['Error_Code'].' '.$response[0]['Error_Msg'];
				$ReturnObject['fork'] = 2;
			} 
			else {
				$data['EvnSection_id'] = $EvnSection_id;
				$data['EvnReanimatPeriod_id'] = $response[0]['EvnReanimatPeriod_id'];
				$dyrdyn = $this->ReanimatRegister_model->ReanimatRegisterSet($data, 1);
				if (!$dyrdyn) {
					$ReturnObject['Status'] = 'Oshibka';
					$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании записи регистра реанимации';
					$ReturnObject['fork'] = 3;
				}
				else {
					
					$ReturnObject['Status'] = 'DoneSuccessfully';
					$ReturnObject['Message'] = 'Пациент перевендён в реанимацию. <br> Запись в реестр реанимации: <br>';// 'Пациент переведён в реанимацию';
					$ReturnObject['EvnReanimatPeriod_id'] = $data['EvnReanimatPeriod_id'];
					$ReturnObject['fork'] = 4;						
				}
			}
		} else { // ошибка работы с БД при запуске хранимой процедуры 
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка обращения к БД при формировании реанимационного периода';
			$ReturnObject['fork'] = 0;
			return $ReturnObject;
		}
		
		return($ReturnObject);
	}
	
	/**
     * BOB - 29.05.2018  
	 * Возвращает Id первого попавшегося отделения обслуживаемого данной службой реанимации
	 */
	function getProfilSectionId($data) {
		//Выборка отделений привязанных к службе реанимации
		$params = array(
				'MedService_id' => $data['MedService_id']
				);		
		$query = "
				select top 1 LS.LpuSection_id 
				  from dbo.v_MedServiceSection MSS with (nolock)
						inner join dbo.v_LpuSection LS  with (nolock) on MSS.LpuSection_id = LS.LpuSection_id
				 where MSS.MedService_id = :MedService_id
				   and LpuSectionProfile_SysNick = 'profil'
				   ";    
		$result = $this->db->query($query, $params)->result('array');

		if(count($result) == 0){
			$params = array(
					'Lpu_id' => $data['Lpu_id']
					);		
			$query = "
					select top 1 LpuSection_id
					 from v_LpuSection LS with (nolock)
					 inner join v_LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id  
					 where LS.LpuSectionProfile_SysNick = 'profil'
					   and LS.Lpu_id = :Lpu_id
					   and LU.LpuUnitType_SysNick = 'stac'					   ";    
			$result = $this->db->query($query, $params)->result('array');			
		}
		
		$response = '';
		if(count($result) != 0){
			$response = $result[0]['LpuSection_id'];
		}
		return $response;
		
	}
	
	
	
	
	
	
	
	/**
     * BOB - 22.03.2017
	 * Завершение реанимационного периода
	 * проверка - а есть ли
	 * подготовка данных для окна
	 */
	function endReanimatReriod($data) {
		
		//возвращаемый объект
		$ReturnObject = array(	'EvnReanimatPeriod_id' => '',
								'Status' => '',
								'Message' => '');

		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = array(
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id']
				);		
		$query = "
				select EvnReanimatPeriod_id from v_EvnReanimatPeriod ERP  with (nolock) 
				 where ERP.Person_id = :Person_id
			      and ERP.EvnReanimatPeriod_setDate <= SYSDATETIME()
				   and ERP.EvnReanimatPeriod_disDate is null"
		;    
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject['Status'] = 'Oshibka';
			$ReturnObject['Message'] = 'Ошибка при выполнении запроса к базе данных';
			return $ReturnObject;
		}
        //sql_log_message('error', 'endReanimatReriod: ', getDebugSql($query, $params));        
		$resp = $result->result('array');

		//если нет реанимационного периода
		if (count($resp) == 0) {
			$ReturnObject['Status'] = 'NotInReanimation';
			$ReturnObject['Message'] = 'Данный пациент не находится в реанимации';
			return $ReturnObject;                            
		} 

		$ReturnObject['Status'] = 'DoneSuccessfully';
		$ReturnObject['Message'] = 'Нашли голубчика';
		$ReturnObject['EvnReanimatPeriod_id'] = $resp[0]['EvnReanimatPeriod_id'];

		return($ReturnObject);
		
		
	}


	/**
     * BOB - 28.04.2018
	 * Проверка завершения реанимационных периодов
	 * и исхода последнего РП
	 * при завершении движения
	 */
	function checkEvnSectionByRPClose($data) {

        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'Status' => 'norm',
								'Message' => '');
		
		// Ищу РП отсортировав их по дате начала
		//$data['EvnSection_id'] = 111;
		$params = array(
				'EvnSection_id' => $data['EvnSection_id']
				);		
		$query = "
				select EvnReanimatPeriod_disDT, convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,120) as EvnReanimatPeriod_disDate, EvnReanimatPeriod_disTime, ERP.ReanimResultType_id, RRT.ReanimResultType_Name 
				  from dbo.v_EvnReanimatPeriod ERP with (nolock) 
				  left join dbo.ReanimResultType RRT  with (nolock) on ERP.ReanimResultType_id  = RRT.ReanimResultType_id 
				 where EvnReanimatPeriod_pid = :EvnSection_id
				 order by EvnReanimatPeriod_setDT desc
				 ";    
		$result = $this->db->query($query, $params)->result('array');
		//echo '<pre>'.'  $data  ' . print_r($result, 1) . '</pre>'; //BOB - 14.03.2017
		
		//ЕСЛИ нет ни одного РП
		if (count($result) == 0){
			//	сообщение НОРМ			
			$ReturnObject['Status'] = "norm";
			$ReturnObject['Message'] = "Реанимационные периоды отсутствуют.";
			return $ReturnObject;
		}
		
		//ЕСЛИ поздняя запись незавершённая
		if ($result[0]['EvnReanimatPeriod_disDT'] == null){
			//	сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject['Status'] = "stop";
			$ReturnObject['Message'] = "Реанимационный период не закрыт!";
			return $ReturnObject;
		}
		
		
		//Сравнение дат-времён закрытия РП и Движения
		$hour = (int)substr($data['EvnSection_disTime'], 0, 2);
		$minute = (int)substr($data['EvnSection_disTime'], 3, 2);
		$month = (int)substr($data['EvnSection_disDate'], 5, 2);
		$day = (int)substr($data['EvnSection_disDate'], 8, 2);
		$year = (int)substr($data['EvnSection_disDate'], 0, 4);		
		$EvnSection_disDT = mktime($hour, $minute, 0, $month, $day, $year);
        //echo '<pre>'.'  $data  ' . print_r(date('F jS, Y g:i:s a',   $EvnSection_disDT), 1) . '</pre>'; //BOB - 14.03.2017
		
		$hour = (int)substr($result[0]['EvnReanimatPeriod_disTime'], 0, 2);
		$minute = (int)substr($result[0]['EvnReanimatPeriod_disTime'], 3, 2);
		$month = (int)substr($result[0]['EvnReanimatPeriod_disDate'], 5, 2);
		$day = (int)substr($result[0]['EvnReanimatPeriod_disDate'], 8, 2);
		$year = (int)substr($result[0]['EvnReanimatPeriod_disDate'], 0, 4);		
		$EvnReanimatPeriod_disDT = mktime($hour, $minute, 0, $month, $day, $year);
        //echo '<pre>'.'  $data  ' . print_r(date('F jS, Y g:i:s a',   $EvnReanimatPeriod_disDT), 1) . '</pre>'; //BOB - 14.03.2017
		
		//ЕСЛИ дата закрытия РП > даты закрытия движения
		if($EvnReanimatPeriod_disDT > $EvnSection_disDT){
			//сообщение НЕ НОРМ - дата закрытия РП > даты закрытия движения
			$ReturnObject['Status'] = "ask";
			$ReturnObject['Message'] = "Закрытие Реанимационного периода (".str_pad($day,2,'0', STR_PAD_LEFT).".".str_pad($month,2,'0', STR_PAD_LEFT).".".$year." ".str_pad($hour,2,'0', STR_PAD_LEFT).":".str_pad($minute,2,'0', STR_PAD_LEFT).") позднее закрытия Движения!";
			return $ReturnObject;
		}
			
		//СРавнение исходов РП и ДВижения
		// Код исхода движения перевожу в SysNick
		$params = array(
				'LeaveType_id' => $data['LeaveType_id']
				);		
		$query = "
				select LeaveType_SysNick from LeaveType  with (nolock) 
				 where LeaveType_id = :LeaveType_id
			";
		$LeaveType = $this->db->query($query, $params)->result('array');
		if(
			in_array($result[0]['ReanimResultType_id'],[2,3])
			&& !in_array($LeaveType[0]['LeaveType_SysNick'],['die','ksdie','ksdiepp','diepp','dsdie','dsdiepp','kslet','ksletitar'])
			|| !in_array($result[0]['ReanimResultType_id'],[2,3])
			&& in_array($LeaveType[0]['LeaveType_SysNick'],['die','ksdie','ksdiepp','diepp','dsdie','dsdiepp','kslet','ksletitar'])
		){
			$ReturnObject['Status'] = "ask";
			$ReturnObject['Message'] = "Исходы Реанимационного периода (".$result[0]['ReanimResultType_Name'].") и Движения не соответствуют друг другу!";
			return $ReturnObject;			
		}	
		sql_log_message('error', 'from LeaveType: ', getDebugSql($query, $params)); 	
			
		return $ReturnObject;
	}

	/**
     * BOB - 14.06.2018
	 * Проверка завершения реанимационных периодов
	 * и исхода последнего РП
	 * при попытке выписки
	 */
	function checkBeforeLeave($data) {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//log_message('debug', 'checkBeforeLeave   '.print_r($data, 1));
		//возвращаемый объект
		$ReturnObject = [
			'success' => true,
			'Error_Msg' => ''
		];
		
		// Ищу РП отсортировав их по дате начала
		//$data['EvnSection_id'] = 111;
		$params = [
			'EvnSection_id' => $data['EvnSection_id']
		];
		$query = "
			select EvnReanimatPeriod_disDT, convert(varchar(10), ERP.EvnReanimatPeriod_disDT  ,120) as EvnReanimatPeriod_disDate, EvnReanimatPeriod_disTime, ERP.ReanimResultType_id, RRT.ReanimResultType_Name 
			from dbo.v_EvnReanimatPeriod ERP with (nolock) 
				left join dbo.ReanimResultType RRT  with (nolock) on ERP.ReanimResultType_id  = RRT.ReanimResultType_id 
			where EvnReanimatPeriod_pid = :EvnSection_id
			order by EvnReanimatPeriod_setDT desc
		";
		$result = $this->db->query($query, $params)->result('array');
		//echo '<pre>'.'  $data  ' . print_r($result, 1) . '</pre>'; //BOB - 14.03.2017
		
		//ЕСЛИ нет ни одного РП
		if (count($result) == 0){
			//	сообщение НОРМ
			$ReturnObject['success'] = true;
			$ReturnObject['Error_Msg'] = "";
			return $ReturnObject;
		}
		
		//ЕСЛИ поздняя запись незавершённая
		if (empty($result[0]['EvnReanimatPeriod_disDT'])){
			//	сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = "Реанимационный период не закрыт!";
			return $ReturnObject;
		}

		// Сравнение исходов РП и ДВижения
		// Код исхода движения перевожу в SysNick
		$LeaveType_SysNick = $this->getFirstResultFromQuery("
			select LeaveType_SysNick
			from LeaveType with (nolock)
			where LeaveType_id = :LeaveType_id
		", [
			'LeaveType_id' => $data['LeaveType_id']
		]);

		if ($LeaveType_SysNick !== false && !empty($LeaveType_SysNick) ) {
			$deathLeaveTypes = [ 'die', 'dsdie', 'ksdie' ];

			// ЕСЛИ исходы РП и движения не соответствуют  (код исхода движения перевести в SysNick и уже оперировать с ним)
			if (
				($result[0]['ReanimResultType_id'] > 1 && !in_array($LeaveType_SysNick, $deathLeaveTypes))
				|| ($result[0]['ReanimResultType_id'] == 1 && in_array($LeaveType_SysNick, $deathLeaveTypes))
			) {
				//сообщение НЕ НОРМ - исходы РП и движения не соответствуют
				$ReturnObject['success'] = false;
				$ReturnObject['Error_Msg'] = "Исход Реанимационного периода (".$result[0]['ReanimResultType_Name'].") и вид выписки не соответствуют друг другу!";
				return $ReturnObject;
			}
		}

		return $ReturnObject;
	}
	
	/**
     * BOB - 21.01.2019
	 * Проверка завершения реанимационных периодов
	 * при попытке удаления КВС или движения
	 */
	function checkBeforeDelEvn($data) {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//log_message('debug', 'checkBeforeLeave   '.print_r($data, 1));
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '');
		
		$EvnReanimatPeriod_ = '';
		if ($data['Object'] == 'EvnPS')
			$EvnReanimatPeriod_ =  'EvnReanimatPeriod_rid' ;
		elseif ($data['Object'] == 'EvnSection') 
			$EvnReanimatPeriod_ =  'EvnReanimatPeriod_pid' ;
		else 
			return false;
		
		
		// Ищу РП
		$params = array(
				'Object_id' => $data['Object_id']
				);
		$query = "
				select EvnReanimatPeriod_id
				  from dbo.v_EvnReanimatPeriod ERP with (nolock) 
				 where ".$EvnReanimatPeriod_." = :Object_id
				--   and EvnReanimatPeriod_disDT is null
				   ";    
		$result = $this->db->query($query, $params)->result('array');
		//echo '<pre>'.'  $data  ' . print_r($result, 1) . '</pre>'; //BOB - 14.03.2017
		
		//ЕСЛИ нет ни одного открытого РП
		if (count($result) == 0){
			//	сообщение НОРМ			
			$ReturnObject['success'] = true;
			$ReturnObject['Error_Msg'] = "";
		}
		else {//ЕСЛИ поздняя запись незавершённая
			//	сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = "Имеется Реанимационный период!";
		}
					
		return $ReturnObject;
	}
	
	
	 /**
     * Удаление реанимационного периода из ЭМК
	 * BOB - 12.05.2018
     */
    function deleteEvnReanimatPeriod($data)
    {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '');
		
		$params = array(
				'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
				);		
		$query = "
				select count(*) Evn_Count from Evn  with (nolock) 
				 where Evn_pid = :EvnReanimatPeriod_id
				   and Evn_deleted < 2
			";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));

		if($Evn[0]['Evn_Count'] > 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'Реанимационный период содержит дочерние объекты. <br> Удаление невозможно.';
			return $ReturnObject;			
		}	
		//BOB - 12.07.2019
		$query = "
 			select count(*) Evn_Count from (
				select EvnReanimatPeriod_id 
				  from dbo.ReanimatPeriodPrescrLink RPPL  with (nolock)
					inner join dbo.v_EvnPrescr EP  with (nolock) on EP.EvnPrescr_id = RPPL.EvnPrescr_id
				 where EvnReanimatPeriod_id=:EvnReanimatPeriod_id
				union all
				select EvnReanimatPeriod_id
				  from dbo.ReanimatPeriodDirectLink  RPDL
				  left join dbo.v_EvnDirection_All ED  with (nolock) on ED.EvnDirection_id = RPDL.EvnDirection_id
				 where EvnReanimatPeriod_id=:EvnReanimatPeriod_id
			) as children";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));

		if($Evn[0]['Evn_Count'] > 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'Реанимационный период содержит прикреплённые назначения и/или направления. <br> Удаление невозможно.';
			return $ReturnObject;			
		}	
		//BOB - 12.07.2019

		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$params = array(
				'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
				'pmUser_id'	=> $pmUser_id
				);		
		$query = "
		
				declare

					@EvnReanimatPeriod_id bigint = :EvnReanimatPeriod_id,
					@pmUser_id bigint = :pmUser_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null; 

				   exec dbo.p_EvnReanimatPeriod_del
					@EvnReanimatPeriod_id,
					@pmUser_id,
					@Error_Code output,
					@Error_Message output;


					select @Error_Code as Error_Code, @Error_Message as Error_Message;
		
				";

		$result = $this->db->query($query, $params)->result('array');

		if (( !empty($response[0]['Error_Code']) ) || ( !empty($response[0]['Error_Msg']) )) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = "Ошибка при удалении Реанимационного периода: <br>". (!empty($response[0]['Error_Code']) ? $response[0]['Error_Code'] : "").  (!empty($response[0]['Error_Msg']) ? $response[0]['Error_Msg'] : "");
			return $ReturnObject;			
		}	
		
		//поиск неудалённых РП
		$params = array(
				'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
				);		
		$query = "
				declare
					@Person_id bigint,				--2586392
					@EvnReanimatPeriod_id bigint,   --1357388
					@ReanimatRegister_id bigint;	--61

				select @Person_id = Person_id from Evn  with (nolock) 
				where Evn_id = :EvnReanimatPeriod_id

				select top 1 @EvnReanimatPeriod_id = EvnReanimatPeriod_id from v_EvnReanimatPeriod ERP  with (nolock) 
				where ERP.Person_id = @Person_id
				order by ERP.EvnReanimatPeriod_setDT desc

				select @ReanimatRegister_id = ReanimatRegister_id from dbo.ReanimatRegister RR  with (nolock) 
				where RR.Person_id = @Person_id


				select @Person_id as Person_id, @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @ReanimatRegister_id as ReanimatRegister_id			";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));
		
		//ЕСЛИ отсутствуют 
		if ( empty($Evn[0]['EvnReanimatPeriod_id']) ) {
			//	удаление записи регистра реанимации
			if ( !empty($Evn[0]['ReanimatRegister_id']) ) {
				$params = array(
							'ReanimatRegister_id' => $Evn[0]['ReanimatRegister_id'],
							'pmUser_id'	=> $pmUser_id
						);		
				$query = "
						declare
							@ReanimatRegister_id bigint = :ReanimatRegister_id,
							@pmUser_id bigint = :pmUser_id,
							@IsRemove bigint = 2,
							@Error_Code int = null,
							@Error_Message varchar(4000) = null;

						   exec dbo.p_ReanimatRegister_del
							@ReanimatRegister_id,
							@pmUser_id,
							@IsRemove,
							@Error_Code output,
							@Error_Message output;

							select @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
				$result = $this->db->query($query, $params)->result('array');
				//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));
			}
		}
		else {  //ИНАЧЕ - присутствуют
			//	установить предыдущий код РП и снять пометку о РП в данный момент
			if ( !empty($Evn[0]['ReanimatRegister_id']) ) {
				$params = array(
							'EvnReanimatPeriod_id' => $Evn[0]['EvnReanimatPeriod_id'],
							'ReanimatRegister_id' => $Evn[0]['ReanimatRegister_id'],
							'pmUser_id'	=> $pmUser_id
						);		
				$query = "
					declare
						@ReanimatRegister_id bigint = :ReanimatRegister_id,
						@Error_Code int = null,
						@Error_Message varchar(4000) = null;

					exec dbo.p_ReanimatRegister_upd
						@ReanimatRegister_id = @ReanimatRegister_id output,
						@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
						@ReanimatRegister_IsPeriodNow = 1, -- нет
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
				$result = $this->db->query($query, $params)->result('array');
				//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));
			}

		}
		
		
		
		
		return $ReturnObject;
		
		
    }
	
	 /**
     * Удаление реанимационного периода из АРМ-ов стационара и реаниматолога
	 * BOB - 12.05.2018
     */
    function delReanimatPeriod($data)
    {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '');
		
		$params = array(
				'Person_id' => $data['Person_id']
				);		
		$query = "
				select ERP.EvnReanimatPeriod_id from dbo.v_EvnReanimatPeriod ERP with (nolock)
				 where ERP.Person_id = :Person_id
				 order by ERP.EvnReanimatPeriod_setDT desc
			";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>deleteEvnReanimatPeriod exec query: ', getDebugSql($query, $params));

		if(count($Evn) === 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'У данного пациента отсутствует Реанимационный период.';
			return $ReturnObject;			
		}	
		
		$data['EvnReanimatPeriod_id'] = $Evn[0]['EvnReanimatPeriod_id'];
		
		$ReturnObject = $this->deleteEvnReanimatPeriod($data);
		return $ReturnObject;
		
	}
	
	 /**
     * проверка можно ли переводить из одной реанимации в другую
	 * BOB - 02.10.2019
     */
    function changeReanimatPeriodCheck($data)
    {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '',
								'EvnReanimatPeriod_id' => '',
								'MedService_id' => '',
								'EvnPS_id' => '',
								'EvnSection_id' => '',
								'LpuSection_id' => ''
			);
		
		$params = array(
				'EvnReanimatPeriod_rid' => $data['EvnPS_id'],
				'Person_id' => $data['Person_id']
				);
		$query = "
					select EvnReanimatPeriod_id, EvnReanimatPeriod_pid, EvnReanimatPeriod_rid, LpuSection_id, MedService_id, Lpu_id
					  from v_EvnReanimatPeriod with (nolock)
					 where Person_id = :Person_id
					   and EvnReanimatPeriod_rid = :EvnReanimatPeriod_rid
					   and EvnReanimatPeriod_disDT is null
				";
		$Evn = $this->db->query($query, $params)->result('array');
		sql_log_message('error', 'EvnReanimatPeriod_model=>changeReanimatPeriodCheck exec query: ', getDebugSql($query, $params));

		if(count($Evn) === 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'У данного пациента по данному КВС отсутствует Реанимационный период.';
			return $ReturnObject;
		}
		
		$ReturnObject['EvnReanimatPeriod_id'] = $Evn[0]['EvnReanimatPeriod_id'];
		$ReturnObject['MedService_id'] = $Evn[0]['MedService_id'];
		$ReturnObject['EvnPS_id'] = $Evn[0]['EvnReanimatPeriod_rid'];
		$ReturnObject['EvnSection_id'] = $Evn[0]['EvnReanimatPeriod_pid'];
		$ReturnObject['LpuSection_id'] = $Evn[0]['LpuSection_id'];
		
		$params['Lpu_id'] = $Evn[0]['Lpu_id'];
		
		$query = "
					select * from v_MedService with (nolock)
					 where Lpu_id = :Lpu_id
					   and MedServiceType_id = 67
					   and MedService_endDT is NULL
				";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>changeReanimatPeriodCheck exec query: ', getDebugSql($query, $params));
		
		if(count($Evn) < 2) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'В данной МО одна служба реанимации - переводить некуда.';
			return $ReturnObject;
		}
		return $ReturnObject;
	}

	 /**
     * перевод из одной реанимации в другую
	 * BOB - 02.10.2019
     */
    function changeReanimatPeriod($data)
    {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '',
								'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
								'MedService_id' => $data['MedService_id']
			);
		
		$params = array(
						'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
						'MedService_id' => $data['MedService_id']
				);
		
		$query = "
			declare
				@EvnReanimatPeriod_disDT datetime = GetDate(),
				@EvnReanimatPeriod_setDT datetime,
				@EvnReanimatPeriod_id bigint  = :EvnReanimatPeriod_id,
				@EvnReanimatPeriod_pid bigint,
				@EvnReanimatPeriod_rid bigint,

				@ReanimReasonType_id bigint,
				@LpuSectionBedProfile_id bigint,
				@LpuSection_id bigint,
				@Lpu_id bigint,
				@Server_id bigint,
				@PersonEvn_id bigint,
				@Person_id bigint,

				@EvnSection_setDT datetime,
				@EvnSection_disDT datetime,

				@Child_setDT_Max  datetime,
				@Child_disDT_Max  datetime,

				@err_status varchar(10) = 'norm',
				@err_message varchar(1000) = '';


			select @EvnReanimatPeriod_setDT = EvnReanimatPeriod_setDT, @EvnReanimatPeriod_pid = EvnReanimatPeriod_pid, @EvnReanimatPeriod_rid = EvnReanimatPeriod_rid,
					@ReanimReasonType_id = ReanimReasonType_id, @LpuSectionBedProfile_id = LpuSectionBedProfile_id, @LpuSection_id = LpuSection_id,
					@Lpu_id = Lpu_id, @Server_id = Server_id, @PersonEvn_id = PersonEvn_id, @Person_id = Person_id
			   from v_EvnReanimatPeriod with (nolock)
			where EvnReanimatPeriod_id = @EvnReanimatPeriod_id;

			if (@EvnReanimatPeriod_setDT > @EvnReanimatPeriod_disDT) begin
				set @err_status = 'err'
				set @err_message = '~Дата начало исходного РП превышает дату окончания - текущую дату!'
			end
			else begin

				-- нахожу родительское движение
				select @EvnSection_setDT = EvnSection_setDT, @EvnSection_disDT = EvnSection_disDT
					from dbo.v_EvnSection with (nolock)
				where EvnSection_id = @EvnReanimatPeriod_pid;

				-- дата окончания РП должна быть в пределах дат «движения»
				if not ((@EvnReanimatPeriod_disDT >= @EvnSection_setDT) and ((@EvnReanimatPeriod_disDT < @EvnSection_disDT) or (@EvnSection_disDT is null))) begin
					set @err_status = 'err'
					set @err_message = @err_message + '~Окончание РП - текущая дата вне периода Движения'
				end

				-- нахожу максимальную дату начала дочерних сущностей
				select top 1 @Child_setDT_Max = Evn_setDT
				from v_Evn with (nolock)
				where Evn_pid = @EvnReanimatPeriod_id
				order by Evn_setDT desc

				-- нахожу максимальную дату окончания дочерних сущностей
				select top 1 @Child_disDT_Max = Evn_disDT
				from v_Evn with (nolock)
				where Evn_pid = @EvnReanimatPeriod_id
				order by Evn_disDT desc

				-- дата окончания РП не должна быть меньше окончаний дочерних событий, а поскольку имеются без окончани, то и начал
				if ((@Child_disDT_Max is not null) and (@EvnReanimatPeriod_disDT < @Child_disDT_Max) or ((@Child_setDT_Max is not null) and (@EvnReanimatPeriod_disDT < @Child_setDT_Max))) begin
					set @err_status = 'err'
					set @err_message = @err_message + '~Окончание РП - текущая дата раньше окончания или начала дочернего события'
				end
			end
			
			select @err_status as err_status, @err_message as err_message,
					@EvnReanimatPeriod_id as EvnReanimatPeriod_id, @EvnReanimatPeriod_pid as EvnReanimatPeriod_pid,  @EvnReanimatPeriod_rid as EvnReanimatPeriod_rid,
					@EvnReanimatPeriod_setDT as EvnReanimatPeriod_setDT, @EvnReanimatPeriod_disDT as EvnReanimatPeriod_disDT,
					@ReanimReasonType_id as ReanimReasonType_id, @LpuSectionBedProfile_id as LpuSectionBedProfile_id,
					@Lpu_id as Lpu_id, @Server_id as Server_id, @PersonEvn_id as PersonEvn_id, @Person_id as Person_id,
					@LpuSection_id as LpuSection_id
		";
		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $result = '.print_r($result, 1)); //BOB - 17.05.2018
		
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		//log_message('debug', 'EvnReanimatPeriod_model=>EvnReanimatPeriod_Save $resultArray = '.print_r($resultArray, 1)); //BOB - 17.05.2018
		if ($resultArray[0]['err_status'] == 'err'){
			$ReturnObject['success'] = 'false';
			$ReturnObject['Error_Msg'] = $resultArray[0]['err_message'];
			return $ReturnObject;
		}
		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь
		
		
		//   Закрытие РП с исходом – «перевод в другую службу реанимации»
		$params = array(
						'EvnReanimatPeriod_id' => $resultArray[0]['EvnReanimatPeriod_id'],
						'EvnReanimatPeriod_pid' => $resultArray[0]['EvnReanimatPeriod_pid'],
						'EvnReanimatPeriod_rid' => $resultArray[0]['EvnReanimatPeriod_rid'],
						'EvnReanimatPeriod_setDT' => $resultArray[0]['EvnReanimatPeriod_setDT'],
						'EvnReanimatPeriod_disDT' => $resultArray[0]['EvnReanimatPeriod_disDT'],
						'ReanimReasonType_id' => $resultArray[0]['ReanimReasonType_id'],
						'ReanimResultType_id' => 4,
						'LpuSectionBedProfile_id' => $resultArray[0]['LpuSectionBedProfile_id'],
						'LpuSection_id' => $resultArray[0]['LpuSection_id'],
						'MedService_id' => $data['MedService_id'],
						'Lpu_id' => $resultArray[0]['Lpu_id'],
						'Server_id' => $resultArray[0]['Server_id'],
						'PersonEvn_id' => $resultArray[0]['PersonEvn_id'],
						'Person_id'  => $resultArray[0]['Person_id'],
			
						'pmUser_id' => $pmUser_id
						);
		//echo '<pre>' . print_r($params, 1) . '</pre>'; //BOB - 20.10.2017
		
		$query = "
			declare
			  @EvnReanimatPeriod_id bigint = :EvnReanimatPeriod_id ,

			  @Error_Code int = null,
			  @Error_Message varchar(4000) = null;


			exec p_EvnReanimatPeriod_upd
			  @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
			  @EvnReanimatPeriod_pid = :EvnReanimatPeriod_pid,
			  @EvnReanimatPeriod_setDT = :EvnReanimatPeriod_setDT,
			  @EvnReanimatPeriod_disDT = :EvnReanimatPeriod_disDT,
			  @ReanimReasonType_id = :ReanimReasonType_id,
			  @ReanimResultType_id = :ReanimResultType_id,
			  @LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
			  @Lpu_id = :Lpu_id,
			  @Server_id = :Server_id,
			  @PersonEvn_id = :PersonEvn_id,

			 @pmUser_id = :pmUser_id,
			 @Error_Code = @Error_Code output,
			 @Error_Message = @Error_Message output;


			select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
		";
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		if ((empty($resultArray[0]['EvnReanimatPeriod_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
			$ReturnObject['success'] = 'false';
			$ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			return $ReturnObject;
		}
		
		// Закрытие всех незакрытых дочерних наблюдений и реанимационных мероприятий, может ещё и измерений.
		$query = "select * from dbo.v_EvnReanimatAction  with (nolock)
				   where EvnReanimatAction_pid = :EvnReanimatPeriod_id
					 and EvnReanimatAction_disDT is null";
		$result = $this->db->query($query, $params);
		//sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $params));
		if ( !is_object($result) ) return false;
		
		$Response = $result->result('array');

		foreach($Response as &$row ){

			$params['EvnReanimatAction_id'] = $row['EvnReanimatAction_id'];
			$params['ReanimatActionType_id'] = $row['ReanimatActionType_id'];
			$params['UslugaComplex_id'] = $row['UslugaComplex_id'];
			$params['EvnUsluga_id'] = $row['EvnUsluga_id'];
			$params['ReanimDrugType_id'] = $row['ReanimDrugType_id'];
			$params['EvnReanimatAction_DrugDose'] = $row['EvnReanimatAction_DrugDose'];
			$params['EvnDrug_id'] = $row['EvnDrug_id'];
			$params['EvnReanimatAction_MethodCode'] = $row['EvnReanimatAction_MethodCode'];
			$params['EvnReanimatAction_ObservValue'] = $row['EvnReanimatAction_ObservValue'];
			$params['ReanimatCathetVeins_id'] = $row['ReanimatCathetVeins_id'];
			$params['CathetFixType_id'] = $row['CathetFixType_id'];
			$params['EvnReanimatAction_CathetNaborName'] = $row['EvnReanimatAction_CathetNaborName'];
			$params['NutritiousType_id'] = $row['NutritiousType_id'];
			$params['EvnReanimatAction_DrugUnit'] = $row['EvnReanimatAction_DrugUnit'];
			$params['EvnReanimatAction_MethodTxt'] = $row['EvnReanimatAction_MethodTxt'];
			$params['EvnReanimatAction_NutritVol'] = $row['EvnReanimatAction_NutritVol'];
			$params['EvnReanimatAction_NutritEnerg'] = $row['EvnReanimatAction_NutritEnerg'];
			$params['MilkMix_id'] = $row['MilkMix_id'];    //BOB - 15.04.2020
			

			$params['EvnReanimatAction_setDT'] = $row['EvnReanimatAction_setDT'];
			
			//echo '<pre>' . print_r($params, 1) . '</pre>'; //BOB - 20.10.2017
			$query = "
				declare
					@EvnReanimatAction_id bigint = :EvnReanimatAction_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;

				exec   dbo.p_EvnReanimatAction_upd
					@EvnReanimatAction_id  = @EvnReanimatAction_id,
					@EvnReanimatAction_pid  = :EvnReanimatPeriod_id,
					@EvnReanimatAction_disDT = :EvnReanimatPeriod_disDT,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					
					
						 @ReanimatActionType_id = :ReanimatActionType_id,
						 @UslugaComplex_id = :UslugaComplex_id,
						 @EvnUsluga_id = :EvnUsluga_id,
						 @ReanimDrugType_id = :ReanimDrugType_id,
						 @EvnReanimatAction_DrugDose = :EvnReanimatAction_DrugDose,
						 @EvnDrug_id = :EvnDrug_id,
						 @EvnReanimatAction_MethodCode = :EvnReanimatAction_MethodCode,
						 @EvnReanimatAction_ObservValue = :EvnReanimatAction_ObservValue,
						 @ReanimatCathetVeins_id = :ReanimatCathetVeins_id,
						 @CathetFixType_id = :CathetFixType_id,
						 @EvnReanimatAction_CathetNaborName = :EvnReanimatAction_CathetNaborName,
						 @NutritiousType_id = :NutritiousType_id,
						 @EvnReanimatAction_DrugUnit = :EvnReanimatAction_DrugUnit,
						 @EvnReanimatAction_MethodTxt = :EvnReanimatAction_MethodTxt,
						 @EvnReanimatAction_NutritVol = :EvnReanimatAction_NutritVol,
						 @EvnReanimatAction_NutritEnerg = :EvnReanimatAction_NutritEnerg,
						 @MilkMix_id = :MilkMix_id,

						 @EvnReanimatAction_setDT = :EvnReanimatAction_setDT,


					@pmUser_id = :pmUser_id,
					@Error_Code  = @Error_Code output,
					@Error_Message = @Error_Message output ;

				select @EvnReanimatAction_id as EvnReanimatAction_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
			";

			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $params));
			if ( !is_object($result) )
				return false;

		}

		
		//   Формирование нового РП,
		$query = "
				declare

					 @EvnReanimatPeriod_id bigint,
					 @EvnReanimatPeriod_insDT  datetime = dateadd(mi, 1, GETDATE()),
					 @Error_Code int,
                     @Error_Message varchar(4000), 
                     @ReanimatAgeGroup_id bigint = null,
                     @getDT datetime = cast(cast(GetDate() as date) as datetime);
                                  
                     select top 1 
                     @ReanimatAgeGroup_id = case 
                         when dateadd(DAY, -29, @getDT)  < PS.Person_BirthDay then 1
                         when dateadd(DAY, -29, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -1, @getDT) < PS.Person_BirthDay then 2
                         when dateadd(YEAR, -1, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -4, @getDT) < PS.Person_BirthDay then 3
                         when dateadd(YEAR, -4, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -18, @getDT) < PS.Person_BirthDay then 4
                         else 5
                     end
                     from dbo.v_PersonState PS  with (nolock)
                     inner join PersonEvn PE with (nolock) on PE.Person_id = PS.Person_id
                     where PE.PersonEvn_id=:PersonEvn_id;
					 
				exec   dbo.p_EvnReanimatPeriod_ins
					 @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
					 @EvnReanimatPeriod_pid = :EvnReanimatPeriod_pid,
					 @EvnReanimatPeriod_rid = :EvnReanimatPeriod_rid,
					 @Lpu_id = :Lpu_id,
					 @Server_id = :Server_id,
					 @MedService_id = :MedService_id,
					 @LpuSection_id = :LpuSection_id,
					 @ReanimResultType_id  = null,
					 @ReanimReasonType_id  = 1,
					 @LpuSectionBedProfile_id = null, 
                     @ReanimatAgeGroup_id = @ReanimatAgeGroup_id,

					 @PersonEvn_id = :PersonEvn_id,
					 @EvnReanimatPeriod_setDT = @EvnReanimatPeriod_insDT,
					 @EvnReanimatPeriod_disDT = null,
					 @EvnReanimatPeriod_didDT = null,

					 @EvnReanimatPeriod_insDT = null,
					 @EvnReanimatPeriod_updDT = null,
					 @EvnReanimatPeriod_Index = null,
					 @EvnReanimatPeriod_Count = null,
					 @Morbus_id = null,
					 @EvnReanimatPeriod_IsSigned = null,
					 @pmUser_signID = null,
					 @EvnReanimatPeriod_signDT = null,
					 @EvnStatus_id = null,
					 @EvnReanimatPeriod_statusDate = null,
					 @isReloadCount = null,
					 @pmUser_id = :pmUser_id,
					 @Error_Code = @Error_Code output,
					 @Error_Message = @Error_Message output;

				select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
				";

		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		//sql_log_message('error', 'p_EvnReanimatPeriod_ins exec query: ', getDebugSql($query, $params));
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		if ((empty($resultArray[0]['EvnReanimatPeriod_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
			$ReturnObject['success'] = 'false';
			$ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			return $ReturnObject;
		}
		
		$params['EvnReanimatPeriod_id'] = $resultArray[0]['EvnReanimatPeriod_id'];
		$ReturnObject['EvnReanimatPeriod_id'] = $resultArray[0]['EvnReanimatPeriod_id'];
		//   Изменение кода РП в записи регистра реанимации
		
		$query = "
				declare
					@ReanimatRegister_id bigint,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;

				select @ReanimatRegister_id = ReanimatRegister_id from dbo.ReanimatRegister RR  with (nolock)
				where RR.Person_id = :Person_id

				exec dbo.p_ReanimatRegister_upd
					@ReanimatRegister_id = @ReanimatRegister_id output,
					@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
					@ReanimatRegister_IsPeriodNow = 2, -- да
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @Error_Code as Error_Code, @Error_Message as Error_Mess;
				";

		$result = $this->db->query($query, $params); //BOB - 12.09.2018
		//sql_log_message('error', 'p_ReanimatRegister_upd exec query: ', getDebugSql($query, $params));
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		if ((!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
			$ReturnObject['success'] = 'false';
			$ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			return $ReturnObject;
		}
		
		
		return $ReturnObject;
	}
	
	/**
	 * Печать списка пациентов
	 * BOB - 24/12/2019
	 * @return bool
	 */
	function printPatientList($data)
	{
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id']
		);

		$query = "
			select 	 ROW_NUMBER() OVER(ORDER BY EvnReanimatPeriod_setDT desc) AS Record_Num,
			EvnPS.EvnPS_NumCard as EvnPS_NumCard,
			Person_all.Person_FirName as Person_Firname,
						Person_all.Person_SecName as Person_Secname,
						Person_all.Person_SurName as Person_Surname,
						ISNULL(convert(varchar(10), Person_all.Person_BirthDay, 104), '') as Person_Birthday,
						ISNULL(convert(varchar(10), EvnReanimatPeriod.EvnReanimatPeriod_setDate, 104), '') as EvnPS_setDate,
						ISNULL(convert(varchar(10), EvnReanimatPeriod.EvnReanimatPeriod_disDate, 104), '') as EvnPS_disDate,
						DATEDIFF(DAY, EvnReanimatPeriod.EvnReanimatPeriod_setDate, cast(SYSDATETIME() as DATE)) as EvnPS_KoikoDni,
						'-' as LpuSectionWard_name,
						ISNULL(PT.PayType_Name, '') as PayType_Name
			
			from v_EvnReanimatPeriod EvnReanimatPeriod  with (nolock)
				LEFT JOIN v_Person_all Person_all with (nolock) on Person_all.Server_id = EvnReanimatPeriod.Server_id and
							Person_all.Person_id = EvnReanimatPeriod.Person_id and Person_all.PersonEvn_id = EvnReanimatPeriod.PersonEvn_id
				inner JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnReanimatPeriod.EvnReanimatPeriod_rid
				inner JOIN v_EvnSection EvnS with(nolock) on EvnS.EvnSection_id = EvnReanimatPeriod.EvnReanimatPeriod_pid
				LEFT JOIN v_PayType PT with (nolock) on PT.PayType_id = EvnS.PayType_id
			
			WHERE EvnReanimatPeriod.MedService_id = :MedService_id
				and EvnReanimatPeriod.Lpu_id = :Lpu_id
				and cast(EvnReanimatPeriod.EvnReanimatPeriod_setDate as DATE) <= cast(SYSDATETIME() as DATE)
				and EvnReanimatPeriod.EvnReanimatPeriod_disDate is null
		";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}


	//ОТОБРАЖЕНИЕ НА АРМе**********************************************************************************************************************************************

	/**
     * BOB - 14.03.2017
	 * список пациентов переведённых в реанимацию для отображения в дереве на АРМ реаниматолога
	 * куча полей неизвестного назначения, оставил их чтобы не нарушить полноту данных в узле дерева, запрос делал по образцу
	 */
	function getReanimationPatientList($data) {
            
        $filters = '';
        
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionWard_id' => $data['object_value'],
			'MedService_id' => $data['MedService_id'],
			'date' => $data['date']
		);
		
		
		if (!empty($data['filter_Person_F'])) {
			$filters .= " and Person_all.Person_SurName LIKE :Person_F";
			$queryParams['Person_F'] = $data['filter_Person_F'] . '%';
		}
		if (!empty($data['filter_Person_I'])) {
			$filters .= ' and Person_all.Person_FirName LIKE :Person_I';
			$queryParams['Person_I'] = $data['filter_Person_I'] . '%';
		}
		if (!empty($data['filter_Person_O'])) {
			$filters .= ' and Person_all.Person_SecName LIKE :Person_O';
			$queryParams['Person_O'] = $data['filter_Person_O'] . '%';
		}
		if (!empty($data['filter_Person_BirthDay'])) {
			$filters .= ' and cast(Person_all.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['filter_Person_BirthDay'];
		}
		
        //log_message('debug', 'EvnReanimatPeriod_model=>getReanimationPatientList  $queryParams   '.print_r($queryParams, 1)); //BOB - 04.12.2017      
        $query = "
                SELECT distinct
				EvnReanimatPeriod.EvnReanimatPeriod_setDT,
				EvnReanimatPeriod.EvnReanimatPeriod_id as EvnReanimatPeriod_id,
				EvnReanimatPeriod.EvnReanimatPeriod_rid as EvnReanimatPeriod_rid,
				EvnReanimatPeriod.LpuSection_id as LpuSection_id,
				EvnReanimatPeriod.MedService_id as MedService_id,
				Person_all.Sex_id as Sex_id,
				case when 0=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
				case when 0=1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp else Person_all.Person_Fio
				end as Person_Fio,
				convert(varchar(10), Person_all.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(Person_all.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				dbo.Age_newborn(Person_all.Person_BirthDay, dbo.tzGetDate()) as Person_AgeMonth,
				Diag.Diag_Code as Diag_Code,
				Diag.Diag_Name as Diag_Name,
				ISNULL(convert(varchar(10), EvnReanimatPeriod.EvnReanimatPeriod_setDate, 104), '') as EvnReanimatPeriod_setDate, 
				ISNULL(convert(varchar(10), EvnReanimatPeriod.EvnReanimatPeriod_disDate, 104), '') as EvnReanimatPeriod_disDate,
				Person_all.Person_id as Person_id,
				Person_all.Server_id as Server_id,
				Person_all.PersonEvn_id as PersonEvn_id,
				case when exists(
					select *
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = Person_all.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				EvnPS.EvnPS_NumCard as EvnPS_NumCard,
				null as Mes_id,   --ISNULL(Mes.Mes_id, '') as Mes_id,
				null as Mes_Code,  --ISNULL(Mes.Mes_Code, '') as Mes_Code,
				null as KoikoDni,  --ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
				EvnPS.EvnPS_id as EvnPS_id,
				null as LpuSectionWard_id,   --isnull(EvnSection.LpuSectionWard_id, '') as LpuSectionWard_id, 
				null  as MedPersonal_id,      --isnull(EvnReanimatPeriod.MedPersonal_id, '') as MedPersonal_id,   
				null  as MedPersonal_Fin, --MedStaffFact.Person_Fin as MedPersonal_Fin,
				datediff(\"d\", EvnReanimatPeriod.EvnReanimatPeriod_setDate, case when (EvnReanimatPeriod.EvnReanimatPeriod_disDate > dbo.tzGetDate()) then :date else isnull(EvnReanimatPeriod.EvnReanimatPeriod_disDate, :date) end) as EvnSecdni,
				EvnS.EvnSection_id,
				LS.LpuSection_Name
			FROM
				v_EvnReanimatPeriod EvnReanimatPeriod with (nolock)
				LEFT JOIN v_Person_all Person_all with (nolock) on Person_all.Server_id = EvnReanimatPeriod.Server_id and
				Person_all.Person_id = EvnReanimatPeriod.Person_id and Person_all.PersonEvn_id = EvnReanimatPeriod.PersonEvn_id
				inner JOIN v_EvnSection EvnS with(nolock) on EvnS.EvnSection_id = EvnReanimatPeriod.EvnReanimatPeriod_pid
				inner JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnS.EvnSection_pid
				LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = isnull(EvnS.Diag_id, EvnPS.Diag_pid)
				--LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
				--LEFT JOIN v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.LpuSection_id = EvnSection.LpuSection_id
				--and MedStaffFact.MedPersonal_id = EvnSection.MedPersonal_id
				LEFT JOIN v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = Person_all.Person_id
				left join v_LpuSection as LS with(nolock) on LS.LpuSection_id = EvnS.LpuSection_id
			WHERE
				EvnReanimatPeriod.MedService_id = :MedService_id
			   --	AND EvnPS.EvnPS_id is not null
				-- and EvnSection.LpuSectionWard_id is null
						and cast(EvnReanimatPeriod.EvnReanimatPeriod_setDate as DATE) <= cast(:date as DATE)
						and EvnReanimatPeriod.EvnReanimatPeriod_disDate is null
				{$filters}
				
			ORDER BY 
				EvnReanimatPeriod_setDT desc
		";

		$result = $this->db->query($query, $queryParams);
		//sql_log_message('error', 'EvnReanimatPeriod_model=>getReanimationPatientList exec query: ', getDebugSql($query, $queryParams));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
    //BOB - 14.03.2017


	//НАЗНАЧЕНИЯ*******************************************************************************************************************************************************
	
	/**
	 * BOB - 22.04.2019
	 * загрузка таблици назначений
	 */	
	function loudEvnPrescrGrid($data) {	
		
		
		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnSection_id'],
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
		);
		
		
		$query = "
			select
				EP.EvnPrescr_id,													-- id назначения								
				EP.EvnPrescr_pid,													-- pid назначения								
				EP.EvnPrescr_rid,													-- rid назначения								
				convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate,		-- дата назначения, похоже время везде 0 								
				EP.PrescriptionType_id,												-- id типа назначения
				PT.PrescriptionType_Name,											-- наименование назначения
				isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec,					-- ++ назначение  выполнено - 2, нет - 1
				isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito,												-- ++ срочность: 2 - да, 1 - нет
				case 
					when ED.EvnDirection_id is null then 'Отменено'					--//BOB - 08.07.2019
					when ED.DirFailType_id > 0 OR EQ.QueueFailCause_id  > 0 then		--//BOB - 08.07.2019
						case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end + ' - ' +
						coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name)
					when TTMS.TimetableMedService_id is null and TTR.TimetableResource_id is null and EQ.EvnQueue_id is null then 'Отменено'					--//BOB - 08.07.2019
					when isnull(EP.EvnPrescr_IsExec,1) = 2 then 'Выполнено' 
					when isnull(EP.EvnPrescr_IsCito,1) = 2 then 'Cito!' 
					else '' 
				end as EvnPrescr_StatusTxt,	-- ++ дополнительно текстово
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_id
				   when 12 then UC12.UslugaComplex_id
				   when 13 then UC13.UslugaComplex_id
				   else ''
				end as UslugaComplex_id,											-- ++ id услуги
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_Code
				   when 12 then UC12.UslugaComplex_Code
				   when 13 then UC13.UslugaComplex_Code
				   else ''
				end as UslugaComplex_Code,											-- ++ код услуги
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_Name
				   when 12 then UC12.UslugaComplex_Name
				   when 13 then UC13.UslugaComplex_Name
				   else ''
				end as	UslugaComplex_Name,   -- ++ название услуги
				ED.EvnDirection_id,			-- ++ код направления
				case when ED.EvnDirection_Num is null then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num,   -- ++ номер направления
				case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when TTR.TimetableResource_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(R.Resource_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end +' / '+ isnull(Lpu.Lpu_Nick,'')
				else '' end as RecTo,  --++ наименование службы куда было направление: TTMS - по бирке, EQ - в очереди, TTR - ресурс
				case
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when TTR.TimetableResource_id is not null then isnull(convert(varchar(10), TTR.TimetableResource_begTime, 104),'')+' '+isnull(convert(varchar(5), TTR.TimetableResource_begTime, 108),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate,  -- ++ время бирки / постановки в очередь
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when TTR.TimetableResource_id is not null then 'TimetableResource'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable,   --++ какой-то NickName:  бирка - TimetableMedService, очередь EvnQueue, ресурс TimetableResource
				case 
					when 2 = EP.EvnPrescr_IsExec then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null
				end as EvnPrescr_execDT,		-- ++  дата ~выполнения~ услуги из даты изменения, так же как сделано в запросах для ЭМК
				EQ.EvnQueue_id,				-- ++ код постановки в очередь
				MS.MedService_id,
				LS.LpuSection_id,
				LU.LpuUnit_id,
				Lpu.Lpu_id,
				case EP.PrescriptionType_id
				   when 12 then (select top 1 EvnPrescrFuncDiagUsluga_id from EvnPrescrFuncDiagUsluga with (nolock) where EvnPrescrFuncDiag_id = EP.EvnPrescr_id )
				   else 0
				end as	TableUsluga_id   -- ++ id EvnPrescrFuncDiagUsluga

			from dbo.v_EvnPrescr EP with (nolock)
				inner join dbo.PrescriptionType PT with (nolock) on EP.PrescriptionType_id = PT.PrescriptionType_id
				inner join dbo.ReanimatPeriodPrescrLink RPPL  with (nolock) on EP.EvnPrescr_id = RPPL.EvnPrescr_id
				left join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC11 with (nolock) on UC11.UslugaComplex_id = EPLD.UslugaComplex_id
				left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC12 with (nolock) on UC12.UslugaComplex_id = EPFDU.UslugaComplex_id
				left join EvnPrescrConsUsluga EPCU with (nolock) on EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC13 with (nolock) on UC13.UslugaComplex_id = EPCU.UslugaComplex_id
				outer apply (
					Select top 1 ED.EvnDirection_id
						,isnull(ED.Lpu_sid, ED.Lpu_id) Lpu_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.EvnDirection_IsAuto
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.Lpu_did
						,ED.MedService_id
						,ED.Resource_id
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
						,ED.MedPersonal_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) ED
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ				
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH																											--//BOB - 08.07.2019
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id									--//BOB - 08.07.2019
				left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id				--//BOB - 08.07.2019
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id								--//BOB - 08.07.2019
				left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id						--//BOB - 08.07.2019
				outer apply (
					Select top 1 TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
				) TTR
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- сам ресрс
				left join v_Resource R with (nolock) on R.Resource_id = ED.Resource_id
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				--left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)

			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			and RPPL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			and EP.PrescriptionType_id in (11, 12, 13)	

			order by  EP.EvnPrescr_setDT desc, EP.EvnPrescr_id desc

		";
		$result = $this->db->query($query, $queryParams);
		sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));	

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}


	/**
	 * BOB - 22.04.2019
	 * создание прикрепления назначения к РП
	 */
	function ReanimatPeriodPrescrLink_Save($data) {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$params = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],						
			'pmUser_id' => $this->sessionParams['pmuser_id'], //текущий пользователь,
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
		);	
		
		$query = "
			declare 
				@ReanimatPeriodPrescrLink_id bigint = null, 
				@Error_Code int = null, 
				@Error_Message varchar(4000) = null; 

			exec dbo.p_ReanimatPeriodPrescrLink_ins 
				@ReanimatPeriodPrescrLink_id = @ReanimatPeriodPrescrLink_id output, 
				@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
				@EvnPrescr_id = :EvnPrescr_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output, 
				@Error_Message  = @Error_Message output; 

			select @ReanimatPeriodPrescrLink_id as ReanimatPeriodPrescrLink_id, @Error_Code as Error_Code, @Error_Message as Error_Message; 
		";
		$result = $this->db->query($query, $params);
		sql_log_message('error', 'p_ReanimatPeriodPrescrLink_ins exec query: ', getDebugSql($query, $params));		
		
		
		if ( !is_object($result) )
			return false;
				
		
		$EvnScaleResult = $result->result('array');

		if (!(($EvnScaleResult[0]['ReanimatPeriodPrescrLink_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null))){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
		
		return $Response;
	}

	/**
	 * BOB - 22.04.2019
	 * загрузка таблици направлений
	 */	
	function loudEvnDirectionGrid($data) {
		$queryParams = array(
			'EvnDirection_pid' => $data['EvnSection_id'],
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$this->load->model('EvnDirection_model', 'EvnDirection_model');			
		$goAll = $this->getGlobalOptions();
		$go = $goAll['globals'];
		
		$query = "
			declare
				@curDate datetime = dbo.tzGetDate();
				
			SELECT
				ED.EvnDirection_id as EvnDirection_id,    --  ++ id напрправления
				convert(varchar,ED.EvnDirection_setDT,104) as EvnDirection_setDate,		-- дата направления, похоже время везде 0 								
				ED.Person_id,       --++ id пациента
				ED.PersonEvn_id as PersonEvn_id,
				ED.Server_id as Server_id,
				ED.Lpu_id as Lpu_id,											-- id больнички из Evn
				ED.Diag_id as Diag_id,										    -- id диагноза из EvnDirection,  для фильтра по ГУЗам
				ED.EvnDirection_IsSigned,										-- подписано ли направление, из Evn
				cast(ED.EvnDirection_Num as varchar) as EvnDirection_Num,		-- номер направления
				case
					when ED.Org_oid is not null then OO.Org_Nick
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end +' / '+ isnull(Lpu.Lpu_Nick,'')
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then isnull(LSP.LpuSectionProfile_Name,'') +' / '+ isnull(LS.LpuSection_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
					else '' 
				end as RecTo,                 -- ++ подразделение
				case
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'') else convert(varchar(10), EUP.EvnUslugaPar_setDT, 104)+' '+convert(varchar(5), EUP.EvnUslugaPar_setDT, 108) end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then 'Направление выписано ' + convert(varchar(10), ED.EvnDirection_setDT, 104)
					else '' 
				end as RecDate,  -- ++ ~В очереди с~  + дата/дата бирки если есть
				case 
					when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 
					else ED.EvnStatus_id 
				end as EvnStatus_id,  -- id статуса (состояния) направления
				case 
					when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' 
					else EvnStatus.EvnStatus_Name 
				end as EvnStatus_Name,      -- текст статуса (состояния) направления
				convert(varchar(10), coalesce( ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 104) as EvnDirection_statusDate,  -- дата изменения состояния
				CASE
					WHEN (ED.EvnStatus_id in (12,13,15)) THEN 0
					WHEN TT.recDate > @curDate and ( ED.ARMType_id =24 OR TT.pmUser_updID BETWEEN 1000000 AND 5000000) THEN 0
					WHEN ED.EvnDirection_IsAuto = 2 AND " . ($go['disallow_canceling_el_dir_for_elapsed_time'] ? '1' : '0') . " = 1 AND TT.recDate <= @curDate THEN 0
					WHEN ISNULL(ED.EvnDirection_IsAuto, 1) = 1 AND " . ($go['disallow_canceling_el_dir_for_elapsed_time'] ? '1' : '0') . " = 0 and TT.recDate <= @curDate THEN 0
					WHEN DF.DirectionFrom = 'incoming' THEN CASE WHEN {$this->EvnDirection_model->getDirectionCancelConditionsForIncoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'outcoming' THEN CASE WHEN {$this->EvnDirection_model->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'both' THEN CASE WHEN {$this->EvnDirection_model->getDirectionCancelConditionsForIncoming()} OR {$this->EvnDirection_model->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
					ELSE 1 
				END as allowCancel,										-- разрешение на отмену

				DT.DirType_Code,	-- код типа направления / на всякий случай,вдруг придётся расширять список типов направлений
				case
					when TTMS.TimetableMedService_id is not null then 'На исследование: ' + isnull(UC.UslugaComplex_Name,'')
					when EQ.EvnQueue_id is not null then
						case 
							when EUP.EvnUslugaPar_setDT is null then isnull(DT.DirType_Name,'Очередь') 
							else 'На исследование: ' + isnull(UC.UslugaComplex_Name,'') 
						end
					-- Пытаемся получить инфрмацию из самого направления
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null and ED.DirType_id is not null then DT.DirType_Name +':'
					else '' 
				end as RecWhat,     -- наименование типа направления (и иногда услуги)/ на всякий случай,вдруг придётся расширять список типов направлений
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'EvnQueue' else 'EvnUslugaPar' end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then 'EvnDirection'
					else '' 
				end as timetable,      -- сущность (таблица) в которой хранится (ведётся) время 
				case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then EQ.EvnQueue_id else EQ.EvnUslugaPar_id end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then ED.EvnDirection_id
					else '' 
				end as timetable_id,       -- id сущности (таблици) в которой хранится (ведётся) время 
				coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as EvnStatusCause_Name,  -- наименование причины состояния
				fLpu.Lpu_Nick as StatusFromLpu, -- название больнички пользователя изменившего статус
				fMP.Person_Fio as StatusFromMP, -- ФИО врача - пользователя изменившего статус
				LU.LpuUnitType_SysNick,			-- SysNick модуля больницы
				 EvnXmlDir.EvnXml_id as EvnXmlDir_id	-- код прикреплённого бланка  - XML-документа
				,EvnXmlDir.XmlType_id as EvnXmlDirType_id	-- тип прикреплённого бланка  - XML-документа
			FROM
				v_EvnDirection_all ED with (nolock)
				 -- TTMS - Расписание службы    (службы и параклиника)
				inner join ReanimatPeriodDirectLink RPDL with (nolock) on RPDL.EvnDirection_id  = ED.EvnDirection_id
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				 -- EQ - очередь
				left join v_EvnQueue EQ with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
				-- MS - сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_UslugaComplex UC with (NOLOCK) on EUP.UslugaComplex_id = UC.UslugaComplex_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id,LS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSP with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSP.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Org OO with (nolock) on OO.Org_id = ED.Org_oid
				-- ESH - история статусов направления	
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				-- EvnStatus - справочник статусов / по идентификатору статуса события
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				-- EvnStatusCause  по причине установки статуса
				left join EvnStatusCause with(nolock) on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				-- DFT - Справочник причин отмены направления
				left join v_DirFailType DFT with(nolock) on DFT.DirFailType_id = ED.DirFailType_id
				-- QFC - Причина изменения порядка очереди
				left join v_QueueFailCause QFC with(nolock) on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				-- fUser - пользователь изменивший статус
				left join v_pmUserCache fUser with(nolock) on fUser.PMUser_id = coalesce(ED.pmUser_failID,EQ.pmUser_failID,ESH.pmUser_insID)
				-- fLpu - больничка пользователя изменившего статус
				left join v_Lpu fLpu with(nolock) on fLpu.Lpu_id = fUser.Lpu_id
				-- fMP - медперсонал пользователя изменившего статус
				outer apply(
					select top 1 MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = fUser.MedPersonal_id and MP.Lpu_id = fUser.Lpu_id and MP.WorkType_id = 1
				) fMP
				-- EvnXmlDir - Xmlдокумент направления, похоже только для удалённой консультации
				outer apply (
					select top 1 EvnXml.EvnXml_id, XmlType.XmlType_id
					from XmlType with (nolock)
					left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = case
					when 13 = DT.DirType_Code then 20
					else null end
				) EvnXmlDir
				-- пользователь и дата установки	
				outer apply (
					select top 1 * from (
						select top 1
							Timetable.pmUser_updID,
							Timetable.TimetableMedService_begTime as recDate
						from 
							v_TimetableMedService_lite Timetable (nolock)
						where
							ED.DirType_id in (2,3,10,11,15,25)
							and Timetable.EvnDirection_id = ED.EvnDirection_id
						UNION ALL
						select top 1
							EQ.pmUser_updID,
							null as recDate
						from
							v_EvnQueue EQ (nolock)
						where 
							EQ.EvnDirection_id = ED.EvnDirection_id
							and (EQ.EvnQueue_recDT is null or EQ.pmUser_recID = 1)
							and EQ.EvnQueue_failDT is null
							and EQ.EvnQueue_IsArchived is null
						UNION ALL
						select
							ED.pmUser_updID,
							null as recDate
						where isnull(ED.EvnDirection_IsAuto,1) = 1
					) tt
				) TT
				-- хитро сравниваются всякие id Lpu в направлении и делаются какие-то выыводы, что-то вроде направленность перемещения
				outer apply (
					SELECT
						CASE
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = ISNULL(ED.Lpu_sid, ED.Lpu_id) THEN 'both'
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = :Lpu_id THEN 'incoming'
							ELSE 'outcoming' END
						as DirectionFrom
				) DF
			WHERE ED.EvnDirection_pid = :EvnDirection_pid
			  and DT.DirType_Code = 13 -- отбор направлений на удалённуюконсультацию
			  and RPDL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			  -- исключаем из списка направления, связанные с назначениями
			  and not exists (select top 1 epd.EvnPrescr_id from v_EvnPrescrDirection epd with (nolock) where epd.EvnDirection_id = ED.EvnDirection_id)
			  -- исключаем из списка направления на МСЭ кроме статусов Новое и Отказ
			  and isnull(ED.DirFailType_id, 0) != 14
			  and isnull(EQ.QueueFailCause_id, 0) != 5
			  and isnull(ESH.EvnStatusCause_id, 0) != 4 
			order by ED.EvnDirection_setDT desc, ED.EvnDirection_id desc


		";
		$result = $this->db->query($query, $queryParams);
		sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));	

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}	
	}
	
	/**
	 * BOB - 22.04.2019
	 * создание прикрепления направления к РП
	 */
	function ReanimatPeriodDirectLink_Save($data) {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id'],						
			'pmUser_id' => $this->sessionParams['pmuser_id'], //текущий пользователь,
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
		);	
		
		$query = "
			declare 
				@ReanimatPeriodDirectLink_id bigint = null, 
				@Error_Code int = null, 
				@Error_Message varchar(4000) = null; 

			exec dbo.p_ReanimatPeriodDirectLink_ins 
				@ReanimatPeriodDirectLink_id = @ReanimatPeriodDirectLink_id output, 
				@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
				@EvnDirection_id = :EvnDirection_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output, 
				@Error_Message  = @Error_Message output; 

			select @ReanimatPeriodDirectLink_id as ReanimatPeriodDirectLink_id, @Error_Code as Error_Code, @Error_Message as Error_Message; 
			
		";
		$result = $this->db->query($query, $params);
		sql_log_message('error', 'p_ReanimatPeriodDirectLink_ins exec query: ', getDebugSql($query, $params));		
		
		
		if ( !is_object($result) )
			return false;
				
		
		$EvnScaleResult = $result->result('array');

		if (!(($EvnScaleResult[0]['ReanimatPeriodDirectLink_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null))){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
		
		return $Response;
	}
	
	
	/**
	 * BOB - 22.04.2019
	 * загрузка таблици дополнительных документов прикреплённых к направлению
	 */
	function getDirectionLinkedDocs($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id']
		);


		$query = "
			select
				EXDL.EvnXmlDirectionLink_id,
				ED.EvnDirection_id,
				ED.EvnDirection_pid,
				EvnXml.EvnXml_id,
				EvnXml.Evn_id, -- as EvnXml_pid,
				Evn.Evn_pid,
				Evn.Evn_rid,
				Evn.EvnClass_id,
				Evn.EvnClass_SysNick,
				EvnXml.XmlType_id,
				EvnXml.EvnXml_Name,
				EvnXml.EvnXml_Data,
				xts.XmlTemplateSettings_Settings as XmlTemplate_Settings,
				xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate,
				xtd.XmlTemplateData_Data as XmlTemplate_Data,
				convert(varchar, EvnXml.EvnXml_insDT, 104) as EvnXml_Date,
				EvnXml.pmUser_insID,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name,
				0 as frame,
				1 as readOnly
			from
				v_EvnXmlDirectionLink EXDL with (nolock)
			INNER JOIN v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EXDL.EvnDirection_id
			inner join v_EvnXml EvnXml with (NOLOCK) on EvnXml.EvnXml_id = EXDL.EvnXml_id 
			inner join v_Evn Evn with (NOLOCK) on Evn.Evn_id = EvnXml.Evn_id
			left join pmUserCache with (NOLOCK) on pmUserCache.pmUser_id = EvnXml.pmUser_insID
			left join XmlTemplateData xtd with (NOLOCK) on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
			left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
			left join XmlTemplateSettings xts with (NOLOCK) on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
			
			where EXDL.EvnDirection_id = :EvnDirection_id
			order by EvnXml.EvnXml_insDT desc
		";
		$result = $this->db->query($query, $queryParams);
		sql_log_message('error', 'getDirectionLinkedDocs: ', getDebugSql($query, $queryParams));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * BOB - 07.11.2019
	 * загрузка таблици курсов лекарственных средств
	 */
	function loudEvnDrugCourseGrid($data) {
		$queryParams = array(
			'EvnCourseTreat_pid' => $data['EvnSection_id'],
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		// $goAll = $this->getGlobalOptions();
		// $go = $goAll['globals'];
		
		$query = "
			select
				ECT.EvnCourseTreat_id
				,ECT.EvnCourseTreat_pid
				,ECT.EvnCourseTreat_rid
				,convert(varchar, ECT.EvnCourseTreat_setDate, 104) as EvnCourseTreat_setDate			-- дата установки курса
				,EPT.EvnPrescrTreat_id
				,convert(varchar, EPT.EvnPrescrTreat_setDate, 104) as EvnPrescrTreat_setDate			-- дата начала курса
				,EPT.EvnPrescrTreat_Descr			--комментарий
				,case when EPT.EvnPrescrTreat_IsCito = 2 then 'Cito!' else '' end EvnPrescrTreat_IsCito			    -- Признак срочности (Да/Нет)
				,Drug.Drug_id						-- id медикамента
				,dcm.DrugComplexMnn_id				-- id Справочника комплексных МНН
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name            -- наименование медикамента: вместо CourseDrug_Name -- наименование медикамента на курс - DrugForm_Nick
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name		 -- торговое наименование медикамента
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name -- наименование формы медикамента: вместо CourseDrugForm_Name -- наименование формы медикамента на курс - DrugForm_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay													-- дневная доза - DoseDay, PrescrDoseDay
				,ec_drug.EvnCourseTreatDrug_id														-- id Медикаментов курса лекарственных средств
				,ec_drug.EvnCourseTreatDrug_MaxDoseDay												--максимальная дневная доза - MaxDoseDay
				,ec_drug.EvnCourseTreatDrug_MinDoseDay												--минимальная дневная доза - MinDoseDay
				,ec_drug.EvnCourseTreatDrug_PrescrDose												--назначенная курсовая доза - PrescrDose
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_KolvoEd, 10, 0)) as EvnCourseTreatDrug_KolvoEd --количество на один прием в единицах дозировки - KolvoEd
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_Kolvo, 10, 0)) as EvnCourseTreatDrug_Kolvo	--количество на один прием в единицах измерения	- Kolvo
				,ISNULL(ECT.EvnCourseTreat_MaxCountDay, '') as MaxCountInDay						--максимальное количество раз в сутки - DrugMaxCountInDay
				,ISNULL(ECT.EvnCourseTreat_Duration, '') as Duration								-- продолжительность курса
				,DTP.DurationType_Nick																--тип продолжительности
				,coalesce(ec_mu.SHORTNAME, ec_cu.SHORTNAME, ec_au.SHORTNAME) as EdUnits_Nick	-- Краткое название единицы измерения  - EdUnits_Nick
				,ec_gu.GoodsUnit_Nick as GoodsUnit_Nick										-- Краткое название единицы измерения по региональному справочнику - GoodsUnit_Nick
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name  -- наименование метода введения
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name	-- наименование типа исполнения
			
			from
				v_EvnCourseTreat ECT with (nolock)
				inner join v_EvnPrescrTreat EPT with (nolock) on EPT.EvnCourse_id = ECT.EvnCourseTreat_id   -- назначение лекарственных средств (v_EvnPrescrTreat)
				inner join dbo.ReanimatPeriodPrescrLink RPPL  with (nolock) on EPT.EvnPrescrTreat_id = RPPL.EvnPrescr_id
				left join v_EvnCourseTreatDrug ec_drug with (nolock) on ec_drug.EvnCourseTreat_id = ECT.EvnCourseTreat_id  ---- Медикаменты курса лекарственных средств
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ec_drug.Drug_id    -- Региональный справочник Медикаменты курса
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)  -- Справочник комплексных МНН
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id     --Медикаменты назначения с типом лекарственное лечение
								and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				left join rls.MASSUNITS ec_mu with (nolock) on ec_drug.MASSUNITS_ID = ec_mu.MASSUNITS_ID	 --Названия единиц массы курса
				left join rls.CUBICUNITS ec_cu with (nolock) on ec_drug.CUBICUNITS_id = ec_cu.CUBICUNITS_id  --Единицы объема упаковок курса
				left join rls.ACTUNITS ec_au with (nolock) on ec_drug.ACTUNITS_id = ec_au.ACTUNITS_id		--Названия единиц действия препаратов курса
				left join v_GoodsUnit ec_gu  with (nolock) on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		--Региональный справочник единиц измерения курса
				left join PerformanceType PFT with (nolock) on  ECT.PerformanceType_id = PFT.PerformanceType_id  --Тип исполнения с курса
				left join PrescriptionIntroType PIT with (nolock) on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id  --Метод введения с курса  с курса
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID    --Классификация лекарственных форм препаратов со справочника МНН
				left join DurationType DTP with (nolock) on ECT.DurationType_id = DTP.DurationType_id    -- тип продолжительности
			where EvnCourseTreat_pid = :EvnCourseTreat_pid
			  and RPPL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			order by ECT.EvnCourseTreat_id, EPT.EvnPrescrTreat_setDt
		";
		$result = $this->db->query($query, $queryParams);
		sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));

		if ( is_object($result) ) {
			$QueryResult = $result->result('array');

			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');

			$EvnCourseTreat_id = '';
			$EvnPrescrTreat_id = '';
			$EvnCourseTreatDrug_id = '';
			$numCourse = 0;
			$DrugCourse = array();

			foreach($QueryResult as $row){
				
				$DrugCourseRow = array(
					'EvnCourseTreat_id' => '',
					'EvnCourse_Title' => '',
					'EvnCourseTreat_setDate' => '',
					'EvnPrescrTreat_IsCito' => '',
					'DrugTorg_Name' => '',
					'EvnPrescrTreat_setDate' => '',
					'DoseOne' => '',
					'DoseDay' => '',
					'DoseCourse' => '',
					'Duration' => '',
					'PrescriptionIntroType_Name' => '',
					'PerformanceType_Name' => ''
				);

				//если первая запись курса
				if ($row['EvnCourseTreat_id'] != $EvnCourseTreat_id){
					$EvnCourseTreat_id = $row['EvnCourseTreat_id'];
					$EvnPrescrTreat_id = $row['EvnPrescrTreat_id'];
					$EvnCourseTreatDrug_id = $row['EvnCourseTreatDrug_id'];
					$numCourse++;
					
					$DrugCourseRow['EvnCourseTreat_id'] = $row['EvnCourseTreat_id'];
					$DrugCourseRow['EvnCourse_Title'] = $numCourse;										// Курс №
					$DrugCourseRow['EvnCourseTreat_setDate'] = $row['EvnCourseTreat_setDate'];			// дата создания курса
					$DrugCourseRow['EvnPrescrTreat_IsCito'] = $row['EvnPrescrTreat_IsCito'];			// Cito

					//Продолжительность
					if (!empty($row['Duration']) && !empty($row['DurationType_Nick']))
						$DrugCourseRow['Duration'] = $row['Duration'] . ' ' . $row['DurationType_Nick'];
					//Метод введения
					if (!empty($row['PrescriptionIntroType_Name']))
						$DrugCourseRow['PrescriptionIntroType_Name'] = $row['PrescriptionIntroType_Name'];
						//$this->description[] = array('name' => 'Метод введения', 'value' => htmlspecialchars($data['PrescriptionIntroType_Name']));
					//Исполнение
					if (!empty($row['PerformanceType_Name']))
						$DrugCourseRow['PerformanceType_Name'] = $row['PerformanceType_Name'];
				}
				$DrugCourseRow['EvnCourse_id'] = $row['EvnCourseTreat_id'];

				//если записи первого назначения (дня) курса
				if ($row['EvnPrescrTreat_id'] == $EvnPrescrTreat_id){
					$DrugCourseRow['DrugTorg_Name'] = $row['DrugTorg_Name'];  // Препараты - торг наименование

					//Дозы:
					//!!! алгоритмы взял из promed\libraries\SwPrescription.php 293-327 и promed\models\EvnPrescrTreat_model.php 3593-3733
					//Разовая
					$row['DrugForm_Nick'] = $this->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], $row['Drug_Name']);
					if (!empty($row['EvnCourseTreatDrug_Kolvo']) && !empty($row['GoodsUnit_Nick'])) {
						$DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_Kolvo'] . ' ' . ($row['GoodsUnit_Nick']);
					} else if (!empty($row['EvnCourseTreatDrug_Kolvo']) && !empty($row['EdUnits_Nick'])) {
						$DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_Kolvo'] . ' ' . ($row['EdUnits_Nick']);
					} else if (!empty($row['EvnCourseTreatDrug_KolvoEd']) && !empty($row['DrugForm_Nick'])) {
						$DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_KolvoEd'] . ' ' . ($row['DrugForm_Nick']);
					}
					//дневная
					if (!empty($row['EvnCourseTreatDrug_MaxDoseDay']) && !empty($row['EvnCourseTreatDrug_MinDoseDay'])) {
						if ($row['EvnCourseTreatDrug_MaxDoseDay'] == $row['EvnCourseTreatDrug_MinDoseDay']) {
							$DrugCourseRow['DoseDay'] = $row['EvnCourseTreatDrug_MaxDoseDay'];
							if(!empty($row['MaxCountInDay']) && !empty($row['GoodsUnit_Nick']) && !empty($row['EvnCourseTreatDrug_Kolvo'])){
								$DrugCourseRow['DoseDay'] = ($row['EvnCourseTreatDrug_Kolvo']*$row['MaxCountInDay']) . ' ' . ($row['GoodsUnit_Nick']);
							} else if(!empty($row['EvnPrescrTreatDrug_DoseDay'])){
								$DrugCourseRow['DoseDay'] = $row['EvnPrescrTreatDrug_DoseDay'];
							}
						} else {
							$DrugCourseRow['DoseDay'] = $row['EvnCourseTreatDrug_MinDoseDay'] . ' - ' . $row['EvnCourseTreatDrug_MaxDoseDay'];
						}
					}
					//курсовая
					if (!empty($row['EvnCourseTreatDrug_PrescrDose']))
						$DrugCourseRow['DoseCourse'] = $row['EvnCourseTreatDrug_PrescrDose'];

					//если запись первого лекарства в первом назначении
					if ($EvnCourseTreatDrug_id == $row['EvnCourseTreatDrug_id']) {
						$DrugCourseRow['EvnPrescrTreat_setDate'] = $row['EvnPrescrTreat_setDate'];		//Период: с ...
					}

					$DrugCourse[] = $DrugCourseRow;
				}


	
			}

			return $DrugCourse;
		}
		else {
			return false;
		}
	}


	/**
	 * BOB - 07.11.2019
	 * загрузка таблици назначений / лекарственных средств
	 */
	function loudEvnPrescrTreatDrugGrid($data) {
		$queryParams = array();

		$queryParams = array(
			'EvnCourse_id' => $data['EvnCourse_id']
		);

		$query = "
			select
				EPTD.EvnPrescrTreatDrug_id
				,EPT.EvnPrescrTreat_setDt
				,case when EPT.EvnPrescrTreat_setDate < cast(cast(GetDate() as date) as datetime) then 1 else 0 end as prosroch  --просрочено - 1 / в работе  - 0
				,Drug.Drug_id
				,dcm.DrugComplexMnn_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name				-- Разовая доза
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name     -- Медикамент
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_KolvoEd, 10, 0)) as KolvoEd											-- Разовая доза
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name -- Разовая доза
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_Kolvo, 10, 0)) as Kolvo												-- Разовая доза
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME) as EdUnits_Nick		-- Разовая доза
				,EPTD.EvnPrescrTreatDrug_DoseDay as DoseDay                                         -- Суточная доза
				,EPTD.EvnPrescrTreatDrug_FactCount as FactCntDay									-- количество исполненных приемов на дату
				,EPT.EvnPrescrTreat_PrescrCount as PrescrCntDay										-- количество назначенных приемов на дату
				,EPT.EvnCourse_id
				,EPT.EvnPrescrTreat_id
				,EPT.EvnPrescrTreat_pid
				,EPT.EvnPrescrTreat_rid
				,convert(varchar(10), EPT.EvnPrescrTreat_setDT, 104) as EvnPrescrTreat_setDate			--Выполнение
				,EPT.EvnPrescrTreat_IsExec as EvnPrescr_IsExec										--Выполнение
				,EPT.PrescriptionStatusType_id
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Code
				,case when EDr.EvnDrug_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
		from
				v_EvnPrescrTreat EPT with (nolock)
				inner join PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EPT.PrescriptionType_id
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.MASSUNITS ep_mu with (nolock) on EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.ACTUNITS ep_au with (nolock) on EPTD.ACTUNITS_ID = ep_au.ACTUNITS_ID
				outer apply (
					select top 1 EvnDrug_id, EvnDrug_setDT from v_EvnDrug with (nolock)
					where EPT.EvnPrescrTreat_IsExec = 2 and EvnPrescr_id = EPT.EvnPrescrTreat_id
				) EDr
			where
				EPT.EvnCourse_id = :EvnCourse_id
			order by EPT.EvnPrescrTreat_setDt, EPTD.EvnPrescrTreatDrug_id
		";

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}

		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');

		$result = $result->result('array');
		$response = array();
		$dayNum = 0;
		$EvnPrescrTreat_id = '';

		foreach ($result as $row) {
			$drug = $row;
			//день №
			if ($EvnPrescrTreat_id != $row['EvnPrescrTreat_id']) {
				$dayNum++;
				$EvnPrescrTreat_id = $row['EvnPrescrTreat_id'];
			}
			$drug['dayNum'] = $dayNum;
			//Разовая доза   // алгоритм взят из jscore\libs\swComponentLibPanels.js – 7887
			$drug['DoseOne'] = '-';
			$row['DrugForm_Nick'] = $this->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], ($row['Drug_Name']));
			if (!empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])) {
				$drug['DoseOne'] = $row['Kolvo'] . ' ' . ($row['EdUnits_Nick']);
			} else if (!empty($row['KolvoEd']) && !empty($row['DrugForm_Nick'])) {
				$drug['DoseOne'] = $row['KolvoEd'] . ' ' . ($row['DrugForm_Nick']);
			}
			//Приемов в день   // алгоритм взят из jscore\libs\swComponentLibPanels.js - 7892
			$drug['CntDay'] = '0';
			if (!empty($row['FactCntDay']) && !empty($row['PrescrCntDay'])) {
				$drug['CntDay'] = $row['FactCntDay'] . ' / ' . ($row['PrescrCntDay']);
			} else if (empty($row['FactCntDay']) && !empty($row['PrescrCntDay'])) {
				$drug['CntDay'] = '0 / ' . ($row['PrescrCntDay']);
			}
			//Выполнение       // алгоритм взят из jscore\libs\swComponentLibPanels.js - 7898
			$iconPositionTpl = '0';  // в работе
			if ($row['EvnPrescr_IsExec'] == 2)
				$iconPositionTpl = '-105px';    //выполнено
			else if ($row['prosroch'] == 1)
				$iconPositionTpl = '-22px';     // просрочено

			$drug['ExecDay'] = '<span style="width:16px; height:16px; background:url(/img/EvnPrescrPlan/icon.png) no-repeat left top; background-position:0 '.$iconPositionTpl.'; display: block; position: relative; top: 0; left: 20px;"></span>';
	
			$response[] = $drug;
		}
		return $response;
	}

	/**
	 * BOB - 07.11.2019
	 * создание прикрепления курса лекарств к РП
	 */
	function ReanimatPeriodDrugCourse_Save($data) {
		$Response = array (
			'success' => 'true',
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'Error_Msg' => '');

		//$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		
		$params = array(
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'pmUser_id' => $this->sessionParams['pmuser_id'], //текущий пользователь,
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
		);

		$query = "
			select EvnPrescrTreat_id from v_EvnPrescrTreat
			where EvnCourse_id = :EvnCourseTreat_id
		";
		$result = $this->db->query($query, $params);
		sql_log_message('error', 'select EvnPrescrTreat_id exec query: ', getDebugSql($query, $params));
		
		if ( !is_object($result) )
			return false;

		$EvnPrescrTreatResult = $result->result('array');

		foreach ($EvnPrescrTreatResult as $row) {
			$params['EvnPrescr_id'] = $row['EvnPrescrTreat_id'];
			
			$query = "
				declare
					@ReanimatPeriodPrescrLink_id bigint = null,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;

				exec dbo.p_ReanimatPeriodPrescrLink_ins
					@ReanimatPeriodPrescrLink_id = @ReanimatPeriodPrescrLink_id output,
					@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
					@EvnPrescr_id = :EvnPrescr_id,

					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message  = @Error_Message output;

				select @ReanimatPeriodPrescrLink_id as ReanimatPeriodPrescrLink_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
			";
			$result = $this->db->query($query, $params);
			sql_log_message('error', 'p_ReanimatPeriodDirectLink_ins exec query: ', getDebugSql($query, $params));
			
			
			if ( !is_object($result) )
				return false;
			
			$EvnScaleResult = $result->result('array');

			if (!(($EvnScaleResult[0]['ReanimatPeriodPrescrLink_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null))){
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
				return $Response;
			}
		}

		return $Response;
	}







	/**
	 * Получение списка реанимационных служб по МО
	 */
	function getReanimationServices($data) {

		$params['Lpu_id'] = $data['Lpu_id'];

		$result = $this->queryResult("
			select
				ms.MedService_id,
				ms.MedService_Nick
			from v_MedService ms (nolock)
			inner join dbo.MedServiceType mst (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
			where (1=1)
				and ms.Lpu_id = :Lpu_id
				and mst.MedServiceType_SysNick = 'reanimation'
				and ms.MedService_endDT is null
		", $params);

		return $result;
	}


	/**
	 * Перевод пациента в реанимацию из АРМа мобильного стационара
	 */
	function mMoveToReanimation($data) {

		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$reanimated = $this->getFirstResultFromQuery("
			select top 1
				EvnReanimatPeriod_id
			from v_EvnReanimatPeriod ERP (nolock)
			where (1=1)
				and ERP.Person_id = :Person_id
				and ERP.EvnReanimatPeriod_setDate <= dbo.tzGetDate()
				and ERP.EvnReanimatPeriod_disDate is null
		", array('Person_id' => $data['Person_id']));

		//если есть открытый реанимационный период пациента, т.е. он уже в реанимации
		if (!empty($reanimated)) {
			return array('Error_Msg' => 'Данный пациент уже находится в реанимации');
		}

		// загрузим так же периодику пациента
		$periodic = $this->getFirstRowFromQuery("
			select top 1
				PersonEvn_id,
				Server_id
			from v_PersonEvn (nolock)
			where Person_id = :Person_id
			order by PersonEvn_id desc
		", array('Person_id' => $data['Person_id']));

		// и данные по движению
		$EvnPS_id = $this->getFirstResultFromQuery("
			select top 1
				EvnSection_pid as EvnPS_id
			from v_EvnSection (nolock)
			where EvnSection_id = :EvnSection_id
		", array('EvnSection_id' => $data['EvnSection_id']));

		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'MedPersonal_id' =>  !empty($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : null,
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnPS_id' => !empty($EvnPS_id) ? $EvnPS_id : null,
			'EvnSection_id' => $data['EvnSection_id'],
			'Server_id' => $periodic['Server_id'],
			'PersonEvn_id' => $periodic['PersonEvn_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// формирование реанимационного периода
		$query = "
				declare
					 @EvnReanimatPeriod_id bigint,
					 @EvnReanimatPeriod_insDT datetime = dbo.tzGetDate(),
					 @Error_Code int,
                     @Error_Message varchar(4000), 
                     @ReanimatAgeGroup_id bigint = null,
                     @getDT datetime = cast(cast(GetDate() as date) as datetime);
                                  
                     select top 1 
                     @ReanimatAgeGroup_id = case 
                         when dateadd(DAY, -29, @getDT)  < PS.Person_BirthDay then 1
                         when dateadd(DAY, -29, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -1, @getDT) < PS.Person_BirthDay then 2
                         when dateadd(YEAR, -1, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -4, @getDT) < PS.Person_BirthDay then 3
                         when dateadd(YEAR, -4, @getDT)  >= PS.Person_BirthDay and dateadd(YEAR, -18, @getDT) < PS.Person_BirthDay then 4
                         else 5
                     end
                     from dbo.v_PersonState PS  with (nolock)
                     inner join PersonEvn PE with (nolock) on PE.Person_id = PS.Person_id
                     where PE.PersonEvn_id=:PersonEvn_id;
					 
				exec p_EvnReanimatPeriod_ins
					 @EvnReanimatPeriod_id = @EvnReanimatPeriod_id output,
					 @EvnReanimatPeriod_pid = :EvnSection_id,
					 @EvnReanimatPeriod_rid = :EvnPS_id,
					 @Lpu_id = :Lpu_id,
					 @Server_id = :Server_id,
					 @MedService_id = :MedService_id,
					 @LpuSection_id = :LpuSection_id,
					 @ReanimResultType_id  = null,
					 @ReanimReasonType_id  = 1,
					 @LpuSectionBedProfile_id = null,
                     @ReanimatAgeGroup_id = @ReanimatAgeGroup_id,

					 @PersonEvn_id = :PersonEvn_id,
					 @EvnReanimatPeriod_setDT = @EvnReanimatPeriod_insDT,
					 @EvnReanimatPeriod_disDT = null,
					 @EvnReanimatPeriod_didDT = null,

					 @EvnReanimatPeriod_insDT = null,
					 @EvnReanimatPeriod_updDT = null,
					 @EvnReanimatPeriod_Index = null,
					 @EvnReanimatPeriod_Count = null,
					 @Morbus_id = null,
					 @EvnReanimatPeriod_IsSigned = null,
					 @pmUser_signID = null,
					 @EvnReanimatPeriod_signDT = null,
					 @EvnStatus_id = null,
					 @EvnReanimatPeriod_statusDate = null,
					 @isReloadCount = null,
					 @pmUser_id = :pmUser_id,
					 @Error_Code = @Error_Code output,
					 @Error_Message = @Error_Message output;

				select @EvnReanimatPeriod_id as EvnReanimatPeriod_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

		$this->beginTransaction();
		$saveResult = $this->getFirstRowFromQuery($query, $params);

		if (empty($saveResult['EvnReanimatPeriod_id'])) {

			$this->rollbackTransaction();
			$error = !empty($saveResult['Error_Msg']) ? ': '.$saveResult['Error_Msg'] : '';
			$result['Error_Msg'] = 'Ошибка при сохранении реанимационного периода'.$error;

		} else {

			$params['EvnReanimatPeriod_id'] = $saveResult['EvnReanimatPeriod_id'];

			$this->load->model('ReanimatRegister_model', 'ReanimatRegister_model');
			$registerResult = $this->ReanimatRegister_model->mSaveReanimatRegister($params);

			if (!empty($registerResult['ReanimatRegister_id'])) {
				$result = array(
					'ReanimatRegister_id' => $registerResult['ReanimatRegister_id'],
					'EvnReanimatPeriod_id' => $saveResult['EvnReanimatPeriod_id']
				);

				$this->sendCallReanimateTeamMessage(
					array(
						'MedService_id' => $data['MedService_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Person_id' => $data['Person_id'],
						'LpuSection_id' => $data['LpuSection_id']
					)
				);

			} else {
				$this->rollbackTransaction();
				$rr_error = !empty($registerResult['Error_Msg']) ? ': '.$registerResult['Error_Msg'] : '';
				$result['Error_Msg'] = 'Ошибка при сохранении реанимационного регистра'.$rr_error;
			}
		}

		$this->commitTransaction();
		return $result;
	}

	/**
	 * Отправка сообщения всему персоналу на службе реанимации
	 */
	function sendCallReanimateTeamMessage($data) {

		$recepients = $this->queryResult("
			select
				pmUser_id
			from
				v_pmUserCache puc (nolock)
				inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedPersonal_id = puc.MedPersonal_id
			where (1=1)
				and msmp.MedService_id = :MedService_id
				and ISNULL(puc.pmUser_deleted, 1) = 1
		", array('MedService_id' => $data['MedService_id']));

		if (!empty($recepients)) {

			$LpuSection_Name = $this->getFirstResultFromQuery("
				select top 1 LpuSection_Name from v_LpuSection (nolock)
				where LpuSection_id = :LpuSection_id
			", array('LpuSection_id' => $data['LpuSection_id']));

			$Person_FullName = $this->getFirstResultFromQuery("
				select top 1
					(Person_SurName + ' ' + Person_FirName + ' ' + Person_SecName) as Person_FullName
				from v_PersonState (nolock)
				where Person_id = :Person_id
			", array('Person_id' => $data['Person_id']));

			$message = "Отделение '{$LpuSection_Name}' запрашивает реанимационную группу для пациента {$Person_FullName}";
			$noticeData = array(
				'autotype' => 5,
				'pmUser_id' => $data['pmUser_id'],
				'type' => 1,
				'title' => 'Запрос реанимационной группы',
				'text' => $message
			);

			foreach($recepients as $medpersonal) {

				$noticeData['User_rid'] = $medpersonal['pmUser_id'];
				//echo '<pre>',print_r($noticeData),'</pre>'; die();

				$this->load->model('Messages_model');
				$this->Messages_model->autoMessage($noticeData);
			}
		}
	}




	
}

