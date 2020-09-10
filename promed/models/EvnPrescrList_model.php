<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 */

/**
 * Модель с методами для
 *  раздела "Назначения" в ЭМК;
 *  формы "Лист назначений";
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property EvnPrescrTreat_model $EvnPrescrTreat_model
 * @property EvnPrescrProc_model $EvnPrescrProc_model
 * @property EvnPrescrOper_model $EvnPrescrOper_model
 * @property EvnPrescrLabDiag_model $EvnPrescrLabDiag_model
 * @property EvnPrescrFuncDiag_model $EvnPrescrFuncDiag_model
 * @property EvnPrescrConsUsluga_model $EvnPrescrConsUsluga_model
 * @property EvnPrescrRegime_model $EvnPrescrRegime_model
 * @property EvnPrescrDiet_model $EvnPrescrDiet_model
 * @property EvnPrescrObserv_model $EvnPrescrObserv_model
 */
class EvnPrescrList_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doloadEvnPrescrDoctorList':
				$rules[] = array(
					'field' => 'Evn_pid',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'DocType_id',
					'label' => 'Тип документа',
					'rules' => '',
					'type' =>  'int'
				);
				break;
			case 'doLoadViewData':
			case 'doLoadEvnPrescrUslugaDataView':
				$rules[] = array(
					'field' => 'parentEvnClass_SysNick',
					'label' => 'Системное имя учетного документа',
					'rules' => '',
					'default' => 'EvnSection',
					'type' => 'string'
				);
				$rules[] = array(
					'field' => 'Evn_pid',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
			case 'doloadEvnPrescrList':
				$rules[] = array(
					'field' => 'Evn_rid',
					'label' => 'Идентификатор случая лечения',
					'rules' => 'required',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'Evn_pid',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
			case 'doLoadEvnPrescrCombo':
				$rules[] = array(
					'field' => 'EvnPrescr_pid',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'PrescriptionType_Code',
					'label' => 'Тип назначения',
					'rules' => '',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => '',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'UslugaComplex_2011id',
					'label' => 'Услуга ГОСТ-2011',
					'rules' => '',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'withoutEvnDirection',
					'label' => 'withoutEvnDirection',
					'rules' => '',
					'type' =>  'id'
				);
				break;
			case 'doMoveInDay':
				$rules[] = array(
					'field' => 'PrescriptionType_id',
					'label' => 'Тип назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				$rules[] = array(
					'field' => 'EvnPrescr_setDate',
					'label' => 'Дата назначения',
					'rules' => 'required',
					'type' =>  'date'
				);
				$rules[] = array(
					'field' => 'whither',
					'label' => 'Тип перемещения (вперед/назад) на 1 день',
					'rules' => 'required',
					'type' =>  'string'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Перенос плановой даты в форме "Лист назначений"
	 */
	function doMoveInDay($data) {
		if (!in_array($data['whither'], array('next','prev'))) {
			return array(array('Error_Msg' => 'Неправильный тип перемещения'));
		}
		$response = array(array('Error_Msg' => 'Неправильный тип назначения'));
		switch ($data['PrescriptionType_id']) {
			case 1: case 2: case 10:
				$response = array(array('Error_Msg' => 'Нельзя разорвать интервал! Нужно редактировать период'));
				break;
			case 5:case 6:
				$response = array(array('Error_Msg' => 'Нельзя нарушить график! Нужно редактировать назначение'));
				break;
			case 7:
				$this->load->model('EvnPrescrOper_model', 'EvnPrescrOper_model');
				$response = $this->EvnPrescrOper_model->doMoveInDay($data);
				break;
			case 11:
				$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
				$response = $this->EvnPrescrLabDiag_model->doMoveInDay($data);
				break;
			case 12:
				$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
				$response = $this->EvnPrescrFuncDiag_model->doMoveInDay($data);
				break;
			case 13:
				$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
				$response = $this->EvnPrescrConsUsluga_model->doMoveInDay($data);
				break;
		}
		return $response;
	}

	/**
	 * Возвращает индекс массива для группировки назначений по типам
	 * в следующем порядке: 1,2,10,5,6,7,11,12,13
	 */
	private function getIndexPrescriptionType($id) {
		$index = 0;
		switch ($id) {
			case 1: $index=0; break;
			case 2: $index=1; break;
			case 10: $index=2; break;
			case 5: $index=3; break;
			case 6: $index=4; break;
			case 7: $index=5; break;
			case 11: $index=6; break;
			case 12: $index=7; break;
			case 13: $index=8; break;
		}
		return $index;
	}
	/**
	 * Возвращает html перерисованного листа назначений + все необходимые данные
	 * @param array $data
	 * @return boolean
	 */
	public function getPrescrPlanView($data) {
		
		//Получаем типы назначений
		$query = "
			select
				PrescriptionType_id,
				PrescriptionType_Code,
				PrescriptionType_Name
			from v_PrescriptionType with (nolock)
			where PrescriptionType_id in (1,2,5,6,7,10,11,12,13)
		";
		$result = $this->db->query($query, array());
		if (!is_object($result)) {
			return false;
		}
		$prescription = array();
		$prescription_type = $result->result('array');
		foreach ($prescription_type as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])] = array(
				'id' => $row['PrescriptionType_id'],
				'code' => $row['PrescriptionType_Code'],
				'title' => $row['PrescriptionType_Name'],
				'items' => array(),
			);
		}
		ksort($prescription);
		unset($prescription_type);

		$data['parentEvnClass_SysNick'] = 'EvnPSAll';
		$options = array(
			'add_where_cause' => 'and EP.PrescriptionType_id in (1,2,5,6,7,10,11,12,13) '
		);
		$rows = $this->_queryLoadViewData($data, $options);
		if ( $rows === false ) {
			return false;
		}
		foreach ($rows as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])]['items'][] = $row;
		}
		unset($rows);
		
		//Генерируем календарь
		
		$cur_date = DateTime::createFromFormat('d.m.Y', date('d.m.Y'));
		$response = array(array(
			'days' => array(
				'EvnPS_id' => $data['Evn_rid'],
				'Evn_pid' => $data['Evn_pid'],
				'EvnPS_setDate'=>$options['EvnPS_setDT']->format('d.m.Y'),
				'Evn_setDate'=>$options['Evn_setDT']->format('d.m.Y'),
				'cur_date'=>$cur_date->format('d.m.Y'),
				'days_diff'=>$options['Evn_setDT']->diff($cur_date)->days,
			),
			$options['key_name']=>'CommonData',
		));
		
		$startCalendarDate = mktime(0, 0, 0, 0, 0, 2037);
		$endCalendarDate = mktime(0, 0, 0, 0, 0, 0);


		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		foreach ($prescription as $prescr_group) {
			$response[] = array(
				'EvnPrescrGroup_Title'=>$prescr_group['title'],
				'PrescriptionType_Code'=>$prescr_group['code'],
				'PrescriptionType_id'=>$prescr_group['id'],
				'isGroupTitle'=>true,
				$options['key_name']=>'EvnPrescrGroup-'.$prescr_group['id'],
			);
			//обрабатываем данные для списка назначений и календаря
			$last_ep = null;
			$last_course = null;
			$index_course = null;
			$first_date = null;
			$is_exe = false;
			$is_sign = false;
			$item = $cnt = 0;
			$days = array();
			$time_arr = array();
			$tmp_arr = array();
			$tmp2_arr = array();
			$drug_cnt = 0;
			$drug_cnt_common = 0;
			$drug_item = null;
			foreach($prescr_group['items'] as $row) {
				$day = array(
					'Day_IsExec' => ($row['EvnPrescr_IsExec'] == 2),
					'EvnPrescr_IsHasEvn' => ($row['EvnPrescr_IsHasEvn'] == 2),
					'Day_IsSign' => ($row['PrescriptionStatusType_id'] == 2),
				);
				$row['isGroupTitle']=false;
				switch($row['PrescriptionType_id'])
				{
					case 1: case 2:
						//режим,диета
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$last_ep = $row['EvnPrescr_id'];
							$item = 0;
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = false;
							$is_sign = false;
							$days = array();
							$time_arr = array();
							$tmp_arr = array();
							$tmp2_arr = array();
						}
						$day['date']=$row['EvnPrescr_setDate'];
						$day['EvnPrescr_id']=$row['EvnPrescr_id'];
						if ($row['PrescriptionType_id']==1) {
							$day['EvnPrescrRegime_id'] = $row['EvnPrescrRegime_id'];
						} else {
							$day['EvnPrescrDiet_id'] = $row['EvnPrescrDiet_id'];
						}
						$days[$row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']] = $day;
						if($is_exe == false)
							$is_exe = false;
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $row['EvnPrescr_cnt'])
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];
							
							$startCalendarDate = ($startCalendarDate<=strtotime($first_date))?$startCalendarDate:strtotime($first_date);
							$endCalendarDate = ($endCalendarDate>strtotime($row['EvnPrescr_setDate']))?$endCalendarDate:strtotime($row['EvnPrescr_setDate']);
							
							$row['EvnPrescrGroup_Title'] = '';
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							if ($row['PrescriptionType_id'] == 1) {
								$row['PrescriptionRegimeType_Name'] = htmlspecialchars($row['PrescriptionRegimeType_Name']);
							} else {
								$row['PrescriptionDietType_Name'] = htmlspecialchars($row['PrescriptionDietType_Name']);
							}
							$row['EvnPrescr_cnt'] = count($days);
							$response[] = $row;
						}
					break;

					case 10:
						//Наблюдения
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$last_ep = $row['EvnPrescr_id'];
							$item = 0;
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = true;
							$is_sign = false;
							$days = array();
							$time_arr = array();
							$tmp_arr = array();
							$tmp2_arr = array();
						}
						if(!array_key_exists(($row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']),$days)) {
							$day['EvnPrescrObserv_id'] = $row['EvnPrescrObserv_id'];
							$day['date']=$row['EvnPrescr_setDate'];
							$day['EvnPrescr_id']=$row['EvnPrescr_id'];
							$days[$row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']] = $day;
						}
						$row['CountInDay'] = 0;
						if(!in_array($row['ObservTimeType_Name'],$time_arr)) {
							$time_arr[] = $row['ObservTimeType_Name'];
							$tmp2_arr[] = $row['ObservTimeType_id'];
							$row['CountInDay']++;
						}
						if(empty($tmp_arr[$row['EvnPrescrObservPos_id']]))
							$tmp_arr[$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
						if($is_exe ==true)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						
						$startCalendarDate = ($startCalendarDate<=strtotime($first_date))?$startCalendarDate:strtotime($first_date);
						$endCalendarDate = ($endCalendarDate>strtotime($row['EvnPrescr_setDate']))?$endCalendarDate:strtotime($row['EvnPrescr_setDate']);
						
						if($item == ($row['EvnPrescr_cnt']*$row['cntParam']))
						{
							if($is_exe){
								$row['EvnPrescr_IsExec'] = 1;
							}	
							else{
								$row['EvnPrescr_IsExec'] = 1;
							}
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['ObservParamType_Names'] = implode(', ',$tmp_arr);
							$row['ObservTimeType_Names'] = implode(', ',$time_arr);
							$row['ObservTimeType_idList'] = implode(',',$tmp2_arr);
							$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];
							$row['EvnPrescrGroup_Title'] = '';
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							$row['EvnPrescr_cnt'] = count($days);
							$response[] = $row;
						}
						break;
					case 5:
						//Лекарственное лечение
						if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$drug_cnt_common = $row['cntDrug'];
							$cnt = $row['PrescrCount']*$row['cntCourseDrug']*$row['cntDrug'];
							$is_exe = false;
							$is_sign = false;
							$days = array();
							$tmp_arr = array();
							$time_arr = array();
						}
						$day_date = $row['EvnPrescr_setDate'];
						$day_key = $last_course.'-'.$day_date;

						if ($last_ep != $row['EvnPrescr_id']) {
							$last_ep = $row['EvnPrescr_id'];
							$time_arr = array();
							$drug_cnt = $row['cntDrug'];
							$drug_item = 0;
							if ($drug_cnt_common > $drug_cnt) {
								$cnt = $cnt - $row['cntCourseDrug']*($drug_cnt_common - $drug_cnt);
							}
						}
						if (empty($tmp_arr[$row['EvnCourseTreatDrug_id']])) {
							$tmp_arr = array(
								'id'=>$row['EvnCourseTreatDrug_id'],
								'Drug_Name'=>$row['CourseDrug_Name'],
								'DrugTorg_Name'=>$row['CourseDrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnCourseTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick($row['CourseDrugForm_Name'], $row['CourseDrug_Name']),
								'Kolvo'=>floatval($row['EvnCourseTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['CourseEdUnits_Nick'],
								'MaxDoseDay'=>$row['EvnCourseTreatDrug_MaxDoseDay'],
								'MinDoseDay'=>$row['EvnCourseTreatDrug_MinDoseDay'],
								'PrescrDose'=>$row['EvnCourseTreatDrug_PrescrDose'],
							);
						}
						if (empty($time_arr[$row['EvnPrescrTreatDrug_id']])) {
							$drug_item++;
							$time_arr = array(
								'id'=>$row['EvnPrescrTreatDrug_id'],
								'Drug_Name'=>$row['Drug_Name'],
								'DrugTorg_Name'=>$row['DrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnPrescrTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], $row['Drug_Name']),
								'Kolvo'=>floatval($row['EvnPrescrTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['EdUnits_Nick'],
								'DoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
								'PrescrCntDay'=>$row['PrescrCntDay'],
								'FactCntDay'=>$row['FactCntDay'],
							);
							if ($drug_cnt==$drug_item) {
								if (empty($days[$day_key])) {
									$day['date']=$day_date;
									$day['cntDrug']=$drug_cnt;
									$day['DrugDataList']=[$time_arr];
									$day['EvnPrescr_id']=$row['EvnPrescr_id'];
									$day['EvnPrescr_Descr']=$row['EvnPrescr_Descr'];
									$days[$day_key] = $day;
								}
							}
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item += 1;
						if($item == $cnt)
						{
							$tmp2_arr = array(
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'isGroupTitle'=>false,
								'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
								'EvnPrescr_IsExec'=>1,
								'PrescriptionStatusType_id'=>1,
								'RecDate'=>null,
								'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
								'PerformanceType_Name'=>$row['PerformanceType_Name'],
								'MaxCountInDay'=>$row['MaxCountInDay'],
								'MinCountInDay'=>$row['MinCountInDay'],
								'Duration'=>$row['Duration'],
								'DurationType_Nick'=>$row['DurationType_Nick'],
								'EvnPrescr_Descr'=>htmlspecialchars($row['EvnPrescr_Descr']),
								$options['key_name']=>'EvnCourseRow-'.$last_course,
							);
							if($is_exe)
								$tmp2_arr['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$tmp2_arr['PrescriptionStatusType_id'] = 2;
							$tmp2_arr['EvnCourse_begDate'] = $first_date;
							$tmp2_arr['EvnCourse_endDate'] = $row['EvnPrescr_setDate'];
							$tmp2_arr['UslugaId_List'] = null;
							$tmp2_arr['Usluga_List'] = null;
							$tmp2_arr['MedServices'] = null;
							$tmp2_arr['DrugDataList'] = [$tmp_arr];
							$tmp2_arr['EvnPrescrGroup_Title'] = '';
							$tmp2_arr['days'] = $days;
							$tmp2_arr['EvnPrescr_cnt'] = $cnt;
							$response[] = $tmp2_arr;
							$startCalendarDate = ($startCalendarDate<=strtotime($first_date)||(is_null($first_date)))?$startCalendarDate:strtotime($first_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
						}
						break;

					case 6:
						//Манипуляции и процедуры
						if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$cnt = $row['PrescrCount'];
							$days = array();
							$tmp_arr = array();
						}
						$day_date = $row['EvnPrescr_setDate'];
						if ('TimetableMedService' == $row['timetable']) {
							$day_date = mb_substr($row['RecDate'],0,10);
						}
						$day_key = $last_course.'-'.$day_date;
						if (empty($days[$day_key])) {
							$day['date']=$day_date;
							$day['CountInDay']=0;
							$day['Day_IsExec']=true;
							$day['EvnPrescr_IsHasEvn']=false;
							$day['Day_IsSign']=true;
							$day['EvnPrescrDataList']=array();
							$days[$day_key] = $day;
						}
						$cell = &$days[$day_key];
						if(empty($cell['EvnPrescrDataList'][$row['EvnPrescr_id']]))
						{
							if ($row['EvnPrescr_IsExec'] != 2) {
								$cell['Day_IsExec']  = false;
							}
							if ($row['PrescriptionStatusType_id'] != 2) {
								$cell['Day_IsSign']  = false;
							}
							if ($row['EvnPrescr_IsHasEvn'] == 2) {
								$cell['EvnPrescr_IsHasEvn']  = true;
							}
							$cell['CountInDay']++;
							$cell['EvnPrescrDataList'][$row['EvnPrescr_id']] = array(
								'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
								'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
								'EvnPrescr_IsHasEvn'=>$row['EvnPrescr_IsHasEvn'],
								'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
								'EvnPrescr_setDate'=>$row['EvnPrescr_setDate'],
								'PrescriptionStatusType_id'=>$row['PrescriptionStatusType_id'],
								'UslugaComplex_id'=>$row['UslugaComplex_id'],
								'UslugaComplex_2011id'=>$row['UslugaComplex_2011id'],
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
								'EvnDirection_id'=>$row['EvnDirection_id'],
								'RecTo'=>$row['RecTo'],
								'RecDate'=>$row['RecDate'],
								'timetable'=>$row['timetable'],
								'timetable_id'=>$row['timetable_id'],
							);
						}
						if(!empty($row['MedService_Name']) && empty($tmp_arr[$row['MedService_id']]))
						{
							$tmp_arr[$row['MedService_id']] = $row['MedService_Name'];
						}

						$item += 1;
						if($item == $cnt)
						{
							$tmp2_arr = array(
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'isGroupTitle'=>false,
								'EvnPrescr_IsCito'=>1,
								'EvnPrescr_IsExec'=>1,
								'PrescriptionStatusType_id'=>1,
								'RecDate'=>null,
								$options['key_name']=>'EvnCourseRow-'.$last_course,
							);
							$tmp2_arr['EvnCourse_begDate'] = $first_date;
							$tmp2_arr['EvnCourse_endDate'] = $row['EvnPrescr_setDate'];
							$tmp2_arr['MedServices'] = implode(', ',$tmp_arr);
							$tmp2_arr['UslugaId_List'] = $row['CourseUslugaComplex_id'];
							if ($this->options['prescription']['enable_show_service_code']) {
								$tmp2_arr['Usluga_List'] = $row['CourseUslugaComplex_Code'].' '.$row['CourseUslugaComplex_Name'];
							} else {
								$tmp2_arr['Usluga_List'] = $row['CourseUslugaComplex_Name'];
							}
							$tmp2_arr['EvnPrescrGroup_Title'] = '';
							$tmp2_arr['days'] = $days;
							$tmp2_arr['EvnPrescr_cnt'] = $cnt;
							$response[] = $tmp2_arr;
							$startCalendarDate = ($startCalendarDate<=strtotime($first_date)||(is_null($first_date)))?$startCalendarDate:strtotime($first_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
						}
						break;

					case 13:
						//Консультационная услуга
					case 11:
						//Лабораторная диагностика
						$last_ep = $row['EvnPrescr_id'];
						$days = array();
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						$row['Usluga_List'] = $row['UslugaComplex_Name'];
					
						$day_date = $row['EvnPrescr_setDate'];
						if ('TimetableMedService' == $row['timetable']) {
							$day_date = mb_substr($row['RecDate'],0,10);
						}
						if ($day_date) {
							//По неясной причине, коазалось, что дата одного из исследований путается с датой записи
							$day['date']=($row['RecDate']!='')?mb_substr($row['RecDate'],0,10):$day_date;
							$day['EvnPrescr_id']=$row['EvnPrescr_id'];
							$days[$row['EvnPrescr_id'].'-'.$day_date] = $day;
						}

						$startCalendarDate = ($startCalendarDate<=strtotime($day_date)||(is_null($day_date)))?$startCalendarDate:strtotime($day_date);
						$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
						$row['days'] = $days;
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						//$row['EvnPrescr_cnt'] = count($days);
						$row['EvnPrescr_cnt'] = 1;
						$response[] = $row;
						break;
					//Оперативное лечение
					case 7:
						//Функциональная диагностика
					case 12:
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$last_ep = $row['EvnPrescr_id'];
							$item = 0;
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = false;
							$is_sign = false;
							$days = array();
							$time_arr = array();
							$tmp_arr = array();
							$tmp2_arr = array();
						}
						if ($first_date == $row['EvnPrescr_setDate']) {
							$cnt = $row['cntUsluga'];
						}
						if( empty($tmp_arr[$row['TableUsluga_id']]) && in_array($row['PrescriptionType_id'],array(7, 12)) )
						{
							$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
							$time_arr[] = $row['UslugaComplex_id'];
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $cnt)
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['UslugaId_List'] = implode(',',$time_arr);
							$row['Usluga_List'] = implode(((in_array($row['PrescriptionType_id'],array(7, 12)))?'<br />':', '),$tmp_arr);
							$row['EvnPrescrGroup_Title'] = '';
							$day_date = $row['EvnPrescr_setDate'];
							if ('TimetableMedService' == $row['timetable']) {
								$day_date = mb_substr($row['RecDate'],0,10);
							}
							if ($day_date) {
								$day['date']=$day_date;
								$day['EvnPrescr_id']=$row['EvnPrescr_id'];
								$days[$row['EvnPrescr_id'].'-'.$day_date] = $day;
							}
							$startCalendarDate = ($startCalendarDate<=strtotime($day_date)||(is_null($day_date)))?$startCalendarDate:strtotime($day_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							//$row['EvnPrescr_cnt'] = count($days);
							$row['EvnPrescr_cnt'] = $row['cntUsluga'];
							$response[] = $row;
						}
						break;
					default:
						break;
				}
			}		
		}

		// var_dump($response); exit();

		//Формируем календарь
		$calendar = array();
		
	
		$startCalendarDate = ($startCalendarDate != mktime(0, 0, 0, 0, 0, 2037))?($startCalendarDate):(($endCalendarDate != mktime(0, 0, 0, 0, 0, 0))?$endCalendarDate:mktime(0,0,0));
		$endCalendarDate = ($endCalendarDate != mktime(0, 0, 0, 0, 0, 0))?($endCalendarDate):(($startCalendarDate != mktime(0, 0, 0, 0, 0, 2037))?$startCalendarDate:mktime(0,0,0));
		
		
		//Показываем минимум 10 дней
		$tenDays = mktime(0, 0, 0, 0, 10, 0)-mktime(0, 0, 0, 0, 0, 0);
		if ($endCalendarDate - $startCalendarDate < $tenDays) {
			$endCalendarDate = $startCalendarDate+$tenDays;
		}
				
		$cur_d = date('j',$startCalendarDate);
		$cur_m = date('n',$startCalendarDate);
		$cur_y = date('Y',$startCalendarDate);
		
		//Названия месяцов в именительном падеже
		$monthNames = array(1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь',);
		//Названия месяцов в родительном падеже
		$monthNamesParPad = array(1=>'Января',2=>'Февраля',3=>'Марта',4=>'Апреля',5=>'Мая',6=>'Июня',7=>'Июля',8=>'Августа',9=>'Сентября',10=>'Октября',11=>'Ноября',12=>'Декабря',);
		//Названия дней недели
		$dayNames = array(1=>'ПН',2=>'ВТ',3=>'СР',4=>'ЧТ',5=>'ПТ',6=>'СБ',7=>'ВС',);
		
		$today = mktime(0,0,0);
		
		while ($endCalendarDate >=  mktime(0, 0, 0, $cur_m, $cur_d, $cur_y)) {
			
			$cur_day = mktime(0,0,0,$cur_m,$cur_d,$cur_y);
			$isToday = ($cur_day == $today);
			
			if (!array_key_exists($cur_y, $calendar)) {
				$calendar[$cur_y] = array();
			}
			
			if (!array_key_exists($cur_m, $calendar[$cur_y])) {
				$calendar[$cur_y][$cur_m] = array(
					'name'=>$monthNames[$cur_m],
					'days'=>array()
				);
			}
			
			$calendar[$cur_y][$cur_m]['days'][$cur_d]=array(
				'isWeekend'=>(in_array(date('N',$cur_day), array(6,7))),
				'dayName'=>$dayNames[date('N',$cur_day)],
				'isToday'=>$isToday
			);
		
			
			if (date('t',mktime(0,0,0,$cur_m,1,$cur_y))==$cur_d) {
				$cur_m++;
				$cur_d = 0;
			}
			if ($cur_m>12) {
				$cur_y++;
				$cur_m = 1;
			}
			$cur_d++;
		}
		
		$calendarHeader = 'График назначений с '.date('j',$startCalendarDate).' '.$monthNamesParPad[date('n',$startCalendarDate)].' '.
				((date('Y',$startCalendarDate)!= date('Y',date($endCalendarDate)))?(date('Y',$startCalendarDate).' '):'').'по '.
				date('j',$endCalendarDate).' '.$monthNamesParPad[date('n',$endCalendarDate)].' '.date('Y',$endCalendarDate);
		
		$headerData = array(
			'calendar'=>$calendar,
			'header'=>$calendarHeader
		);
		
		$dayDiff = ($endCalendarDate - $startCalendarDate)/(mktime(0, 0, 0, 0, 1, 0) - mktime(0, 0, 0, 0, 0, 0))+1;//+1 ибо "включительно"
		
		$this->load->library('parser');
		
		$header = $this->parser->parse('eew_evn_prescrplan_restyled_header', $headerData,true);
		$left = $this->parser->parse('eew_evn_prescrplan_restyled_left', array(
			'count'=>$dayDiff,
			'response'=>$response
		),true);

		$right = $this->parser->parse('eew_evn_prescrplan_restyled_right', array(
			'count'=>$dayDiff,
			'startDate'=>$startCalendarDate,
			'response'=>$response
		),true);
		
		
		$html = $this->parser->parse('eew_evn_prescrplan_restyled', array(
			'header'=>$header,
			'right'=>$right,
			'left'=>$left
		),true);
		
		array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
		$result = array('success'=>true, 'html' => $html,'response'=>$response);
	
		return $result;
	}

	/**
	 * Возвращает данные для листа назначений
	 * @param array $data
	 * @return boolean
	 */
	public function mGetPrescrPlanView($data) {

		//Получаем типы назначений
		$query = "
			select
				PrescriptionType_id,
				PrescriptionType_Code,
				PrescriptionType_Name
			from v_PrescriptionType with (nolock)
			where PrescriptionType_id in (1,2,5,6,7,10,11,12,13)
		";
		$result = $this->db->query($query, array());
		if (!is_object($result)) {
			return false;
		}
		$prescription = array();
		$prescription_type = $result->result('array');
		foreach ($prescription_type as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])] = array(
				'id' => $row['PrescriptionType_id'],
				'code' => $row['PrescriptionType_Code'],
				'title' => $row['PrescriptionType_Name'],
				'items' => array(),
			);
		}
		ksort($prescription);
		unset($prescription_type);

		$data['parentEvnClass_SysNick'] = 'EvnPSAll';
		$options = array(
			'add_where_cause' => 'and EP.PrescriptionType_id in (1,2,5,6,7,10,11,12,13) '
		);
		$rows = $this->_queryLoadViewData($data, $options);
		if ( $rows === false ) {
			return false;
		}
		foreach ($rows as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])]['items'][] = $row;
		}
		unset($rows);

		//Генерируем календарь

		$cur_date = DateTime::createFromFormat('d.m.Y', date('d.m.Y'));

		$startCalendarDate = mktime(0, 0, 0, 0, 0, 2037);
		$endCalendarDate = mktime(0, 0, 0, 0, 0, 0);


		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$response['minCalendarDate'] = "01.01.1000"; // устанавливаем начальную минимальную и максимальную дату
		$response['maxCalendarDate'] = "31.12.1000";
		$response['groups']['regime'] = array('PrescriptionType_id'=>1, 'PrescriptionType_name'=>"Режим");
		$response['groups']['diet'] = array('PrescriptionType_id'=>2, 'PrescriptionType_name'=>"Диета");
		$response['groups']['diag'] = array('PrescriptionType_id'=>3, 'PrescriptionType_name'=>"Диагностика");
		$response['groups']['consul'] = array('PrescriptionType_id'=>4, 'PrescriptionType_name'=>"Консультация");
		$response['groups']['drug'] = array('PrescriptionType_id'=>5, 'PrescriptionType_name'=>"Лекарственное лечение");
		$response['groups']['proc'] = array('PrescriptionType_id'=>6, 'PrescriptionType_name'=>"Манипуляции и процедуры");
		$response['groups']['surgical_treatment'] = array('PrescriptionType_id'=>7, 'PrescriptionType_name'=>"Оперативное лечение");
		$response['groups']['mse'] = array('PrescriptionType_id'=>8, 'PrescriptionType_name'=>"МСЭ");
		$response['groups']['vk'] = array('PrescriptionType_id'=>9, 'PrescriptionType_name'=>"ВК");
		$response['groups']['watch'] = array('PrescriptionType_id'=>10, 'PrescriptionType_name'=>"Наблюдение");
		$response['groups']['labdiag'] = array('PrescriptionType_id'=>11, 'PrescriptionType_name'=>"Лабораторная диагностика");
		$response['groups']['funcdiag'] = array('PrescriptionType_id'=>12, 'PrescriptionType_name'=>"Инструментальная диагностика");
		$response['groups']['consul_diag'] = array('PrescriptionType_id'=>13, 'PrescriptionType_name'=>"Консультационная услуга");


		foreach ($prescription as $prescr_group) {
			//обрабатываем данные для списка назначений и календаря
			$last_ep = null;
			$last_course = null;
			$index_course = null;
			$first_date = null;
			$is_exe = false;
			$is_sign = false;
			$item = $cnt = 0;
			$days = array();
			$time_arr = array();
			$tmp_arr = array();
			$tmp2_arr = array();
			$drug_cnt = 0;
			$drug_cnt_common = 0;
			$drug_item = null;
			foreach($prescr_group['items'] as $row) {
				$day = array(
					'Day_IsExec' => ($row['EvnPrescr_IsExec'] == 2),
					'EvnPrescr_IsHasEvn' => ($row['EvnPrescr_IsHasEvn'] == 2),
					'Day_IsSign' => ($row['PrescriptionStatusType_id'] == 2),
				);
				$row['isGroupTitle']=false;
				switch($row['PrescriptionType_id'])
				{
					case 1: case 2:
					//режим,диета
					if($last_ep != $row['EvnPrescr_id'])
					{
						//это первая итерация с другим назначением
						$last_ep = $row['EvnPrescr_id'];
						$item = 0;
						$first_date = $row['EvnPrescr_setDate'];
						$is_exe = false;
						$is_sign = false;
						$days = array();
						$time_arr = array();
						$tmp_arr = array();
						$tmp2_arr = array();
					}
					$day['date']=$row['EvnPrescr_setDate'];
					$day['EvnPrescr_id']=$row['EvnPrescr_id'];
					if ($row['PrescriptionType_id']==1) {
						$day['EvnPrescrRegime_id'] = $row['EvnPrescrRegime_id'];
					} else {
						$day['EvnPrescrDiet_id'] = $row['EvnPrescrDiet_id'];
					}
					$day['id'] = $row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate'];
					$days[] = $day;

					if($is_exe == false)
						$is_exe = false;
					if($is_sign == false)
						$is_sign = ($row['PrescriptionStatusType_id'] == 2);
					$item = $item + 1;
					if($item == $row['EvnPrescr_cnt'])
					{
						if($is_exe)
							$row['EvnPrescr_IsExec'] = 2;
						if($is_sign)
							$row['PrescriptionStatusType_id'] = 2;
						$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];
						$response['minCalendarDate'] = $first_date;

						$startCalendarDate = ($startCalendarDate<=strtotime($first_date))?$startCalendarDate:strtotime($first_date);
						$endCalendarDate = ($endCalendarDate>strtotime($row['EvnPrescr_setDate']))?$endCalendarDate:strtotime($row['EvnPrescr_setDate']);

						if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
						if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);

						$row['EvnPrescrGroup_Title'] = '';
						$row['days'] = $days;
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						if ($row['PrescriptionType_id'] == 1) {
							$row['PrescriptionRegimeType_Name'] = htmlspecialchars($row['PrescriptionRegimeType_Name']);
						} else {
							$row['PrescriptionDietType_Name'] = htmlspecialchars($row['PrescriptionDietType_Name']);
						}
						$row['EvnPrescr_cnt'] = count($days);

						if ($row['PrescriptionType_id']==1) {
							$response['groups']['regime']['list'][] = $row;
						} else {
							$response['groups']['diet']['list'][] = $row;
						}
					}
					break;

					case 10:
						//Наблюдения
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$last_ep = $row['EvnPrescr_id'];
							$item = 0;
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = true;
							$is_sign = false;
							$days = array();
							$time_arr = array();
							$tmp_arr = array();
							$tmp2_arr = array();
						}
						if(!array_key_exists(($row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']),$days)) {
							$day['EvnPrescrObserv_id'] = $row['EvnPrescrObserv_id'];
							$day['date']=$row['EvnPrescr_setDate'];
							$day['EvnPrescr_id']=$row['EvnPrescr_id'];
							$day['id'] = $row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate'];
							$days[] = $day;
						}
						$row['CountInDay'] = 0;
						if(!in_array($row['ObservTimeType_Name'],$time_arr)) {
							$time_arr[] = $row['ObservTimeType_Name'];
							$tmp2_arr[] = $row['ObservTimeType_id'];
							$row['CountInDay']++;
						}
						if(empty($tmp_arr[$row['EvnPrescrObservPos_id']]))
							$tmp_arr[$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
						if($is_exe ==true)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;

						$startCalendarDate = ($startCalendarDate<=strtotime($first_date))?$startCalendarDate:strtotime($first_date);
						$endCalendarDate = ($endCalendarDate>strtotime($row['EvnPrescr_setDate']))?$endCalendarDate:strtotime($row['EvnPrescr_setDate']);

						if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
						if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);
						if($item == ($row['EvnPrescr_cnt']*$row['cntParam']))
						{
							if($is_exe){
								$row['EvnPrescr_IsExec'] = 1;
							}
							else{
								$row['EvnPrescr_IsExec'] = 1;
							}
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['ObservParamType_Names'] = array_values($tmp_arr);
							$row['ObservTimeType_Names'] = implode(', ',$time_arr);
							$row['ObservTimeType_idList'] = implode(',',$tmp2_arr);
							$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];
							$row['EvnPrescrGroup_Title'] = '';
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							$row['EvnPrescr_cnt'] = count($days);
							$response['groups']['watch']['list'][] = $row;
						}
						break;
					case 5:
						//Лекарственное лечение
						if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$drug_cnt_common = $row['cntDrug'];
							$cnt = $row['PrescrCount']*$row['cntCourseDrug']*$row['cntDrug'];
							$is_exe = false;
							$is_sign = false;
							$days = array();
							$tmp_arr = array();
							$time_arr = array();
						}
						$day_date = $row['EvnPrescr_setDate'];
						$day_key = $last_course.'-'.$day_date;

						if ($last_ep != $row['EvnPrescr_id']) {
							$last_ep = $row['EvnPrescr_id'];
							$time_arr = array();
							$drug_cnt = $row['cntDrug'];
							$drug_item = 0;
							if ($drug_cnt_common > $drug_cnt) {
								$cnt = $cnt - $row['cntCourseDrug']*($drug_cnt_common - $drug_cnt);
							}
						}
						if (empty($tmp_arr[$row['EvnCourseTreatDrug_id']])) {
							$tmp_arr = array(
								'id'=>$row['EvnCourseTreatDrug_id'],
								'Drug_Name'=>$row['CourseDrug_Name'],
								'DrugTorg_Name'=>$row['CourseDrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnCourseTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick($row['CourseDrugForm_Name'], $row['CourseDrug_Name']),
								'Kolvo'=>floatval($row['EvnCourseTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['CourseEdUnits_Nick'],
								'MaxDoseDay'=>$row['EvnCourseTreatDrug_MaxDoseDay'],
								'MinDoseDay'=>$row['EvnCourseTreatDrug_MinDoseDay'],
								'PrescrDose'=>$row['EvnCourseTreatDrug_PrescrDose'],
							);
						}
						if (empty($time_arr[$row['EvnPrescrTreatDrug_id']])) {
							$drug_item++;
							$time_arr = array(
								'id'=> $row['EvnPrescrTreatDrug_id'],
								'Drug_Name'=>$row['Drug_Name'],
								'DrugTorg_Name'=>$row['DrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnPrescrTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], $row['Drug_Name']),
								'Kolvo'=>floatval($row['EvnPrescrTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['EdUnits_Nick'],
								'DoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
								'PrescrCntDay'=>$row['PrescrCntDay'],
								'FactCntDay'=>$row['FactCntDay'],
							);
							if ($drug_cnt==$drug_item) {
								if (empty($days[$day_key])) {
									$day['date']=$day_date;
									$day['cntDrug']=$drug_cnt;
									$day['DrugItem']=$time_arr;
									$day['EvnPrescr_id']=$row['EvnPrescr_id'];
									$day['EvnPrescr_Descr']=$row['EvnPrescr_Descr'];
									$day['id'] = $day_key;
									$days[] = $day;
								}
							}
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item += 1;
						if($item == $cnt)
						{
							$tmp2_arr = array(
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'isGroupTitle'=>false,
								'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
								'EvnPrescr_IsExec'=>1,
								'PrescriptionStatusType_id'=>1,
								'RecDate'=>null,
								'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
								'PerformanceType_Name'=>$row['PerformanceType_Name'],
								'MaxCountInDay'=>$row['MaxCountInDay'],
								'MinCountInDay'=>$row['MinCountInDay'],
								'Duration'=>$row['Duration'],
								'DurationType_Nick'=>$row['DurationType_Nick'],
								'EvnPrescr_Descr'=>htmlspecialchars($row['EvnPrescr_Descr']),
								$options['key_name']=>'EvnCourseRow-'.$last_course,
							);
							if($is_exe)
								$tmp2_arr['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$tmp2_arr['PrescriptionStatusType_id'] = 2;
							$tmp2_arr['EvnCourse_begDate'] = $first_date;
							$tmp2_arr['EvnCourse_endDate'] = $row['EvnPrescr_setDate'];
							$tmp2_arr['UslugaId_List'] = null;
							$tmp2_arr['Usluga_List'] = null;
							$tmp2_arr['MedServices'] = null;
							$tmp2_arr['DrugItemDay'] = $tmp_arr;
							$tmp2_arr['EvnPrescrGroup_Title'] = '';
							$tmp2_arr['days'] = $days;
							$tmp2_arr['EvnPrescr_cnt'] = $cnt;
							$response['groups']['drug']['list'][]  = $tmp2_arr;
							$startCalendarDate = ($startCalendarDate<=strtotime($first_date)||(is_null($first_date)))?$startCalendarDate:strtotime($first_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
							if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
							if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);
						}
						break;

					case 6:
						//Манипуляции и процедуры
						if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$cnt = $row['PrescrCount'];
							$days = array();
							$tmp_arr = array();
						}
						$day_date = $row['EvnPrescr_setDate'];
						if ('TimetableMedService' == $row['timetable']) {
							$day_date = mb_substr($row['RecDate'],0,10);
						}
						$day_key = $last_course.'-'.$day_date;
						if (empty($days[$day_key])) {
							$day['date']=$day_date;
							$day['id'] = $day_key;
							$day['CountInDay']=0;
							$day['Day_IsExec']=true;
							$day['EvnPrescr_IsHasEvn']=false;
							$day['Day_IsSign']=true;
							$day['EvnPrescrDataList']=array();

							$days[$day_key] = $day;
						}
						$cell = &$days[$day_key];
						if(empty($cell['EvnPrescrDataList'][$row['EvnPrescr_id']]))
						{
							if ($row['EvnPrescr_IsExec'] != 2) {
								$cell['Day_IsExec']  = false;
							}
							if ($row['PrescriptionStatusType_id'] != 2) {
								$cell['Day_IsSign']  = false;
							}
							if ($row['EvnPrescr_IsHasEvn'] == 2) {
								$cell['EvnPrescr_IsHasEvn']  = true;
							}
							$cell['CountInDay']++;
							$cell['EvnPrescrDataList'][] = array(
								'id' => $row['EvnPrescr_id'],
								'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
								'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
								'EvnPrescr_IsHasEvn'=>$row['EvnPrescr_IsHasEvn'],
								'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
								'EvnPrescr_setDate'=>$row['EvnPrescr_setDate'],
								'PrescriptionStatusType_id'=>$row['PrescriptionStatusType_id'],
								'UslugaComplex_id'=>$row['UslugaComplex_id'],
								'UslugaComplex_2011id'=>$row['UslugaComplex_2011id'],
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
								'EvnDirection_id'=>$row['EvnDirection_id'],
								'RecTo'=>$row['RecTo'],
								'RecDate'=>$row['RecDate'],
								'timetable'=>$row['timetable'],
								'timetable_id'=>$row['timetable_id'],
							);
						}
						if(!empty($row['MedService_Name']) && empty($tmp_arr[$row['MedService_id']]))
						{
							$tmp_arr[$row['MedService_id']] = $row['MedService_Name'];
						}

						$item += 1;
						if($item == $cnt)
						{
							$tmp2_arr = array(
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'isGroupTitle'=>false,
								'EvnPrescr_IsCito'=>1,
								'EvnPrescr_IsExec'=>1,
								'PrescriptionStatusType_id'=>1,
								'RecDate'=>null,
								$options['key_name']=>'EvnCourseRow-'.$last_course,
							);
							$tmp2_arr['EvnCourse_begDate'] = $first_date;
							$tmp2_arr['EvnCourse_endDate'] = $row['EvnPrescr_setDate'];
							$tmp2_arr['MedServices'] = implode(', ',$tmp_arr);
							$tmp2_arr['UslugaId_List'] = $row['CourseUslugaComplex_id'];
							if ($this->options['prescription']['enable_show_service_code']) {
								$tmp2_arr['Usluga_List'] = $row['CourseUslugaComplex_Code'].' '.$row['CourseUslugaComplex_Name'];
							} else {
								$tmp2_arr['Usluga_List'] = $row['CourseUslugaComplex_Name'];
							}
							$tmp2_arr['EvnPrescrGroup_Title'] = '';
							$tmp2_arr['days'] = array_values($days);
							$tmp2_arr['EvnPrescr_cnt'] = $cnt;
							$response['groups']['proc']['list'][] = $tmp2_arr;
							$startCalendarDate = ($startCalendarDate<=strtotime($first_date)||(is_null($first_date)))?$startCalendarDate:strtotime($first_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
							if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
							if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);
						}
						break;

					case 13:
						//Консультационная услуга
					case 11:
						//Лабораторная диагностика
						$last_ep = $row['EvnPrescr_id'];
						$days = array();
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						$row['Usluga_List'] = $row['UslugaComplex_Name'];

						$day_date = $row['EvnPrescr_setDate'];
						if ('TimetableMedService' == $row['timetable']) {
							$day_date = mb_substr($row['RecDate'],0,10);
						}
						if ($day_date) {
							//По неясной причине, коазалось, что дата одного из исследований путается с датой записи
							$day['date']=($row['RecDate']!='')?mb_substr($row['RecDate'],0,10):$day_date;
							$day['EvnPrescr_id']=$row['EvnPrescr_id'];
							$day['id'] = $row['EvnPrescr_id'].'-'.$day_date;
							$days[] = $day;
						}

						$startCalendarDate = ($startCalendarDate<=strtotime($day_date)||(is_null($day_date)))?$startCalendarDate:strtotime($day_date);
						$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
						if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
						if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);
						$row['days'] = $days;
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						//$row['EvnPrescr_cnt'] = count($days);
						$row['EvnPrescr_cnt'] = 1;
						if ($row['PrescriptionType_id']==13) {
							$response['groups']['consul']['list'][] = $row;
						} else {
							$response['groups']['labdiag']['list'][] = $row;
						}
						break;
					//Оперативное лечение
					case 7:
						//Функциональная диагностика
					case 12:
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$last_ep = $row['EvnPrescr_id'];
							$item = 0;
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = false;
							$is_sign = false;
							$days = array();
							$time_arr = array();
							$tmp_arr = array();
							$tmp2_arr = array();
						}
						if ($first_date == $row['EvnPrescr_setDate']) {
							$cnt = $row['cntUsluga'];
						}
						if( empty($tmp_arr[$row['TableUsluga_id']]) && in_array($row['PrescriptionType_id'],array(7, 12)) )
						{
							$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
							$time_arr[] = $row['UslugaComplex_id'];
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $cnt)
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['UslugaId_List'] = implode(',',$time_arr);
							$row['Usluga_List'] = implode(((in_array($row['PrescriptionType_id'],array(7, 12)))?'<br />':', '),$tmp_arr);
							$row['EvnPrescrGroup_Title'] = '';
							$day_date = $row['EvnPrescr_setDate'];
							if ('TimetableMedService' == $row['timetable']) {
								$day_date = mb_substr($row['RecDate'],0,10);
							}
							if ($day_date) {
								$day['date']=$day_date;
								$day['EvnPrescr_id']=$row['EvnPrescr_id'];
								$day['id'] = $row['EvnPrescr_id'].'-'.$day_date;
								$days[] = $day;
							}
							$startCalendarDate = ($startCalendarDate<=strtotime($day_date)||(is_null($day_date)))?$startCalendarDate:strtotime($day_date);
							$endCalendarDate = ($endCalendarDate>strtotime($day_date)||(is_null($day_date)))?$endCalendarDate:strtotime($day_date);
							if (strtotime($response['minCalendarDate'])>$startCalendarDate) $response['minCalendarDate'] = date('d.m.Y',$startCalendarDate);
							if (strtotime($response['maxCalendarDate'])<$endCalendarDate) $response['maxCalendarDate'] = date('d.m.Y',$endCalendarDate);
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							//$row['EvnPrescr_cnt'] = count($days);
							$row['EvnPrescr_cnt'] = $row['cntUsluga'];
							if ($row['PrescriptionType_id']==7) {
								$response['groups']['surgical_treatment']['list'][] = $row;
							} else {
								$response['groups']['funcdiag']['list'][] = $row;
							}
						}
						break;
					default:
						break;
				}
			}
		}

		// var_dump($response); exit();

		array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
		return $response;
	}

	/**
	 * Получение данных для шаблона печати "Лист врачебных назначений"
	 * Отображается список курсов или назначений и календарь с отметками об исполнении врачом или сестрой
	 * Имя шаблона: print_evnprescr_list
	 */
	function doloadEvnPrescrDoctorList($data) {
		$queryParams = array('Evn_pid'=>$data['Evn_pid']);
		//получить данные по учетному документу ФИО, Название МО, Отделение, лечащий врач.
		//получаем первую дату назначения
		$query = "
			select top 1
				PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_FIO
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,Lpu.Lpu_Name
				,LS.LpuSection_Code
				,LS.LpuSection_Name
				,MP.Person_Fio as MedPersonal_Fio
				,cast(cast(isnull(EP.EvnPrescr_setDT, evn.Evn_setDT) as date) as varchar(10)) as EvnPrescr_date --2014-01-23
				,evn.EvnClass_SysNick
				,PEH.PersonEncrypHIV_Encryp
				,case
					when evn.EvnClass_SysNick = 'EvnVizitPL' OR  evn.EvnClass_SysNick = 'EvnVizitPLStom' then EPLPID.EvnPL_NumCard
					else EPSPID.EvnPS_NumCard
				end as NumCard
			from
				v_Evn evn with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = evn.Person_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = evn.Lpu_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = evn.Evn_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = evn.Evn_id
				left join v_EvnVizitPL EV with (nolock) on EV.EvnVizitPL_id = evn.Evn_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(EPS.LpuSection_pid, ES.LpuSection_id, EV.LpuSection_id)
				left join v_MedPersonal MP with (nolock) on MP.Lpu_id = evn.Lpu_id and MP.MedPersonal_id = coalesce(EPS.MedPersonal_pid, ES.MedPersonal_id, EV.MedPersonal_id)
				outer apply (
					select top 1
						coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescr_setDT
					from v_EvnPrescr EP with (nolock)
					left join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
						and Regime.PrescriptionStatusType_id != 3
					left join v_EvnPrescrDiet Diet with (nolock) on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
						and Diet.PrescriptionStatusType_id != 3
					left join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
						and Obs.PrescriptionStatusType_id != 3
					where EP.EvnPrescr_pid = evn.Evn_id -- and EP.EvnPrescr_setDT is not null
					order by coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)
				) EP
				outer apply(
					SELECT
						EvnPS_NumCard
					FROM v_EvnPS WHERE EvnPS_id = evn.Evn_pid
				) EPSPID
				outer apply(
					SELECT
						EvnPL_NumCard
					FROM v_EvnPL WHERE EvnPL_id = evn.Evn_pid
				) EPLPID
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
			where
				evn.Evn_id = :Evn_pid
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
		} else {
			throw new Exception('Не удалось запросить данные по учетному документу!',400);
		}
		if (empty($response) || empty($response[0]['Person_FIO'])) {
			throw new Exception('Не удалось получить данные по учетному документу!',400);
		}
		if (empty($response[0]['EvnPrescr_date'])) {
			throw new Exception('Не удалось определить дату первого назначения по учетному документу!',400);
		}
		$parse_data = $response[0];

		$isPolka = (in_array($parse_data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom')));
		$regionNick = $data['session']['region']['nick'];

		$addSelect = '';
		$addJoin = '';
		if ($isPolka) {
			// для полки не нужна разбивка по датам и выборка кто выполнил
		} else {
			// для стационара нужна разбивка по датам и выборка кто назначил, кто выполнил #38401
			$queryParams['EvnPrescr_begDate'] = $response[0]['EvnPrescr_date'];
			$addSelect .= "
				,DATEDIFF(DAY, cast(:EvnPrescr_begDate as datetime), coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)) + 1 as DayNum
				,coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				,substring(ISNULL(PMUP.PMUser_Name, ''),1,1) as pmUserPrescr_Name
				,substring(ISNULL(PMUE.PMUser_Name, ''),1,1) as pmUserExec_Name
				,ISNULL(PMUE.PMUser_surName, '') + ' '+ substring(ISNULL(PMUE.PMUser_FirName, ''),1,1) + '.' + substring(ISNULL(PMUE.PMUser_SecName, ''),1,1) + '.' as pmUserExec_FIO";
			/*
			$addJoin .= "
				left join v_pmUser PMUP with (nolock) on PMUP.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
				left join v_pmUser PMUE with (nolock) on PMUE.pmUser_id = coalesce(Regime.pmUser_updID, Diet.pmUser_updID, Obs.pmUser_updID, EP.pmUser_updID)";
			*/
			$addJoin .= "
				left join v_pmUser PMUP with (nolock) on PMUP.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
				left join v_pmUserCache PMUE with (nolock) on PMUE.pmUser_id = coalesce(Regime.pmUser_updID, Diet.pmUser_updID, Obs.pmUser_updID, EP.pmUser_updID)";
			/*
			,substring(ISNULL(MP.Person_SurName, ''),1,1) as pmUserExec_Name
			,case when MP.Dolgnost_Name like '%врач%' then 1 else 0 end as pmUserExec_isDoctor
			,left join v_MedPersonal MP with (nolock) on MP.Medpersonal_id = PMU.pmUser_Medpersonal_id and MP.Lpu_id = PMU.Lpu_id
			 */
		}
		/*
				--,convert(varchar(10), coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT), 104) as EvnPrescrDay_setDate
				--,coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr,'') as EvnPrescrDay_Descr
				--,IsCito.YesNo_Code as IsCito_Code
				--,IsCito.YesNo_Name as IsCito_Name
				--,PST.PrescriptionStatusType_Name
				--,PT.PrescriptionType_Code
				--,case when ED.EvnDirection_id is null then 1 else 2 end as EvnPrescr_IsDir

				--,ISNULL(PRT.PrescriptionRegimeType_id, 0) as PrescriptionRegimeType_id
				--,ISNULL(PRT.PrescriptionRegimeType_Code, 0) as PrescriptionRegimeType_Code

				--,ISNULL(PDT.PrescriptionDietType_id, 0) as PrescriptionDietType_id
				--,ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name

				--,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name-
				--,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
		 */

		if ($regionNick == 'ufa') {
			// выбираем латинские наименования препаратов
			$addSelect .= "
				,coalesce(AM.LATNAME, dcm.DrugComplexMnn_LatName, '') as Drug_Name";
		} else {
			// отображаем торговое наименования препаратов на русском
			$addSelect .= "
				,'' as Drug_Name";

		}


		// получаем данные назначений
		$query = "
			select
			    --общие атрибуты назначения в конкретный день
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id
				,PST.PrescriptionStatusType_id
				--чтобы отобразить в одной строке одно назначение-курс
				,coalesce(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id,EP.EvnPrescr_id) as EvnCoursePrescr_id
				,EP.EvnPrescr_Descr
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Name
				--1
				,ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name
				--2
				,ISNULL(PDT.PrescriptionDietType_Code, '') as PrescriptionDietType_Code
				,ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name
				--5
				,ECTD.EvnCourseTreatDrug_id
				,LTRIM(STR(ECTD.EvnCourseTreatDrug_KolvoEd, 10, 2)) as KolvoEd
				,ISNULL(df.NAME,Drug.DrugForm_Name) as DrugForm_Name
				--,LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 2)) as Kolvo
				,case
					when ECTD.EvnCourseTreatDrug_Kolvo % 1 = 0
						then LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 0))
					when ECTD.EvnCourseTreatDrug_Kolvo * 10 % 1 = 0
						then LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 1))
					else
						LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 2))
				end as Kolvo
				--,isnull(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as Okei_NationSymbol
				, coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME, ep_gu.GoodsUnit_Nick) as Okei_NationSymbol
				,coalesce(dcm.DrugComplexMnn_RusName, Drug.DrugTorg_Name, MnnName.DrugComplexMnnName_Name, '') as DrugTorg_Name
				--5,6 параметры графика
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, '') as CountInDay
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, '') as CourseDuration
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, '') as ContReception
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, '') as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
				--6,7,11,12,13 услуги
				,coalesce(EPPR.EvnPrescrProc_id,EPOU.EvnPrescrOperUsluga_id, EPLDU.EvnPrescrLabDiagUsluga_id, EPFDU.EvnPrescrFuncDiagUsluga_id,EPCU.EvnPrescrConsUsluga_id,0) as TableUsluga_id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,PUC.UslugaComplex_Code as UslugaComplexP_Code
				,PUC.UslugaComplex_Name as UslugaComplexP_Name
				--10
				,ISNULL(OTT.ObservTimeType_id, 0) as ObservTimeType_id
				,ISNULL(OTT.ObservTimeType_Name, '') as ObservTimeType_Name
				--Параметры наблюдения
				,EPOP.EvnPrescrObservPos_id
				,ISNULL(OPT.ObservParamType_Name, '') as ObservParamType_Name
				{$addSelect}
			from
				v_EvnPrescr EP with (nolock)
				inner join v_PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				--1
				left join v_EvnPrescrRegime Regime with (nolock) on EP.PrescriptionType_id = 1
					and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
					and Regime.PrescriptionStatusType_id != 3
				left join v_PrescriptionRegimeType PRT with (nolock) on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				--2
				left join v_EvnPrescrDiet Diet with (nolock) on EP.PrescriptionType_id = 2
					and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
					and Diet.PrescriptionStatusType_id != 3
				left join v_PrescriptionDietType PDT with (nolock) on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				--10
				left join v_EvnPrescrObserv Obs with (nolock) on EP.PrescriptionType_id = 10
					and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
					and Obs.PrescriptionStatusType_id != 3
				--5
				left join v_EvnPrescrTreat Treat with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join rls.MASSUNITS ep_mu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				-- Добавлено https://redmine.swan.perm.ru/issues/136715
				left join rls.ACTUNITS ep_au with (nolock) on ECTD.ACTUNITS_id = ep_au.ACTUNITS_id
				left join GoodsUnit ep_gu with (nolock) on ECTD.GoodsUnit_id = ep_gu.GoodsUnit_id
				
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = ECTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = isnull(ECTD.DrugComplexMnn_id, Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS AM with (nolock) on AM.ACTMATTERS_ID = MnnName.ActMatters_id
				--6
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--6,7,11,12,13
				--left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrOperUsluga with (nolock) where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id
				) EPOU
				--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLD
				--left join v_EvnPrescrLabDiagUsluga EPLDU with (nolock) on EP.PrescriptionType_id = 11 and EPLDU.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiagUsluga with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLDU
				--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				) EPFDU
				--left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrConsUsluga with (nolock) where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				) EPCU
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLDU.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				left join v_UslugaComplex PUC with (nolock) on EP.PrescriptionType_id = 11 and PUC.UslugaComplex_id = EPLD.UslugaComplex_id
				--10
				left join ObservTimeType OTT with (nolock) on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP with (nolock) on EP.PrescriptionType_id = 10 and EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT with (nolock) on EP.PrescriptionType_id = 10 and OPT.ObservParamType_id = EPOP.ObservParamType_id

				left join PrescriptionStatusType PST with (nolock) on PST.PrescriptionStatusType_id = coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id)
				{$addJoin}
			where
				EP.EvnPrescr_pid = :Evn_pid
				and coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,ECTD.EvnCourseTreatDrug_id,Obs.EvnPrescrObserv_id,UC.UslugaComplex_id,PUC.UslugaComplex_id) is not null
			order by
				PT.PrescriptionType_id,
				isnull(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id),
				EP.EvnPrescr_id,
				coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT),
				Obs.EvnPrescrObserv_id
		";
		/*
		убрал, пока не используется, но может позже понадобиться
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join PrescriptionIntroType PIT with (nolock) on EP.PrescriptionType_id = 5 and ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT with (nolock) on  EP.PrescriptionType_id = 5 and ECT.PerformanceType_id = PFT.PerformanceType_id
				left join YesNo IsCito with (nolock) on IsCito.YesNo_id = coalesce(Regime.EvnPrescrRegime_IsCito,Diet.EvnPrescrDiet_IsCito,Obs.EvnPrescrObserv_IsCito,EP.EvnPrescr_IsCito,1)
		 */
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось запросить данные назначений!',400);
		}
		$response = $result->result('array');

		//обработка выборки
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$tmp_arr = array();
		foreach ($response as $row) {
			$type_id = $row['PrescriptionType_id'];
			$row_id = $row['EvnCoursePrescr_id'];
			$day_id = $row['EvnPrescrDay_id'];

			if (empty($tmp_arr[$type_id])) {
				//выбираем данные типа назначения
				$tmp_arr[$type_id] = array(
					'PrescriptionType_Name'=>$row['PrescriptionType_Name'],
					'rows'=>array(),
				);
			}

			if (empty($tmp_arr[$type_id]['rows'][$row_id])) {
				//выбираем данные для отображения в строке
				$tmp_arr[$type_id]['rows'][$row_id] = array(
					'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
					//''=>$row[''],
					'days'=>array(),
				);
			}

			if (!$isPolka && empty($tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id])) {
				//выбираем данные для отображения в ячейке дня
				$tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id] = array(
					'DayNum'=>$row['DayNum'],
					//'EvnPrescrDay_setDate'=>$row['EvnPrescrDay_setDate'],
					'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
					'pmUserExec_Name'=>$row['pmUserExec_Name'],
					'pmUserExec_FIO'=>$row['pmUserExec_FIO'],
					'pmUserPrescr_Name'=>$row['pmUserPrescr_Name'],
					'PrescriptionStatusType_id'=>$row['PrescriptionStatusType_id'],
				);
			}

			switch($type_id) {
				case 1;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionRegimeType_Name'] = $row['PrescriptionRegimeType_Name'];
					break;
				case 2;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Code'] = $row['PrescriptionDietType_Code'];
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Name'] = $row['PrescriptionDietType_Name'];
					break;
				case 10;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList']) ) {
						//Параметры наблюдения
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
					}
					/*
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['TimeTypeList']) ) {
						//Время наблюдения
						$tmp_arr[$type_id]['rows'][$row_id]['TimeTypeList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['TimeTypeList'][$row['ObservTimeType_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['TimeTypeList'][$row['ObservTimeType_id']] = $row['ObservTimeType_Name'];
					}*/
					break;
				case 5;
					$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
					$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList']) ) {
						//медикаменты
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']] = array(
							'Drug_Name'=>$row['Drug_Name'],
							'DrugTorg_Name'=>$row['DrugTorg_Name'],
							'DrugForm_Name'=>$row['DrugForm_Name'],
							'KolvoEd'=>$row['KolvoEd'],
							'Kolvo'=>$row['Kolvo'],
							'Okei_NationSymbol'=>$row['Okei_NationSymbol'],
						);
					}
					break;
				case 6;
				case 7;
				case 11;
				case 12;
				case 13;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList']) ) {
						//услуги(а)
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'] = array();
					}
					if (6 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
						$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					} else {
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					}
					if (11 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Code'] = $row['UslugaComplexP_Code'];
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Name'] = $row['UslugaComplexP_Name'];
					}
					break;
			}
		}
		//var_dump($tmp_arr); exit;

		//дальше собираем данные для отображения
		$parse_data['ep_list'] = array();
		$defRow = array(
			'EvnPrescr_Name'=>null,
			//EvnPrescr_Day{[0-9]+} // содержание ячейки строки сестра
			//EvnPrescr_Day{[0-9]+}S // содержание ячейки строки сестра
		);
		if ($isPolka) {
			$lastRow = array();
		} else {
			$lastRow = array(
				'max_day' => 0,
				'EvnPrescr_begDate' => $queryParams['EvnPrescr_begDate']
			);
		}
		foreach ($tmp_arr as $type_id => $type_data) {
			foreach ($type_data['rows'] as $row_data ) {
				$ep_data = $defRow;

				// заполняем данными ячейки дня строки
				foreach ($row_data['days'] as $day_data ) {
					if($day_data['DayNum'] > $lastRow['max_day'])
					{
						$lastRow['max_day'] = $day_data['DayNum'];
					}
					$caption = 'EvnPrescr_Day'. $day_data['DayNum'];
					$ep_data[$caption] = $day_data['pmUserPrescr_Name'];
					$ep_data[$caption.'S'] = '';
					if($day_data['EvnPrescr_IsExec'] == 2) {
						$ep_data[$caption.'S'] = $day_data['pmUserExec_Name'];
						$ep_data[$caption.'S_FIO'] = $day_data['pmUserExec_FIO'];
					}
				}

				//формируем столбец EvnPrescr_Name
				$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'. $type_data['PrescriptionType_Name'] .'</div>';
				switch($type_id) {
					case 1;
						$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionRegimeType_Name'] .' режим</div>';
						break;
					case 2;
						if ($regionNick == 'ufa') {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionDietType_Name'] .'</div>';
						} else {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">Диета №'.$row_data['PrescriptionDietType_Code'] .'</div>';
						}
						break;
					case 10;
						$ep_data['EvnPrescr_Name'] .= "<div style='text-decoration: underline;'>Параметры наблюдения:</div>";
						$i = 1;
						foreach ( $row_data['ParamTypeList'] as $name ) {
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '<div>';
							}
							$ep_data['EvnPrescr_Name'] .= htmlspecialchars($name) .', ';
							$i++;
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '</div>';
							}
						}
						//$row_data['TimeTypeList']
						break;
					case 5;
						$drug_list = array();
						foreach ( $row_data['DrugList'] as $drug_data ) {
							$name = $drug_data['Drug_Name'];
							if (empty($name)) {
								$name = $drug_data['DrugTorg_Name'];
							}
							$i = '<b>'.$name.'</b>';
							$DrugForm_Nick = $this->EvnPrescrTreat_model->getDrugFormNick($drug_data['DrugForm_Name'], $drug_data['Drug_Name']);
							if ($regionNick != 'ufa') {
								if ( !empty($drug_data['KolvoEd']))
									$i .=  ' По '. htmlspecialchars($drug_data['KolvoEd']) .' '.(empty($DrugForm_Nick)?'ед.дозировки':$DrugForm_Nick);
								if ( !empty($drug_data['Kolvo']) && empty($drug_data['KolvoEd']) )
									$i .=  htmlspecialchars($drug_data['Kolvo']) .' ';
								if ( !empty($drug_data['Okei_NationSymbol']) && empty($drug_data['KolvoEd']) )
									$i .=  htmlspecialchars($drug_data['Okei_NationSymbol']);
							}
							else {
								// Региональные изменения для Уфы https://redmine.swan.perm.ru/issues/136715
								if ( !empty($drug_data['Kolvo']))
									$i .= ' '  .htmlspecialchars($drug_data['Kolvo']) .' ';
								if ( !empty($drug_data['Okei_NationSymbol']))
									$i .=  htmlspecialchars($drug_data['Okei_NationSymbol']);
							}
							
							$drug_list[]=$i;
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode(',<br>',$drug_list);
						if ( !empty($row_data['CountInDay']))
							$ep_data['EvnPrescr_Name'] .=  '<br>'.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
						if ($regionNick != 'ufa') { 
							// Региональные изменения для Уфы https://redmine.swan.perm.ru/issues/136715
							if ( !empty($row_data['ContReception']))
								$ep_data['EvnPrescr_Name'] .=  ', принимать '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
							if ( !empty($row_data['Interval']))
								$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
							if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
								$ep_data['EvnPrescr_Name'] .=  ', в течение '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
							$ep_data['EvnPrescr_Name'] .=  '.';
							$ep_data['EvnPrescr_Name'] .= '</div>';
						}
						break;
					case 6;
					case 7;
					case 11;
					case 12;
					case 13;
						$usluga_list = array();
						if ($this->options['prescription']['enable_show_service_code']) {
							$usluga_tpl = '{UslugaComplex_Code} {UslugaComplex_Name}';
						} else {
							$usluga_tpl = '{UslugaComplex_Name}';
						}
						if (11 == $type_id) {
							$usluga_list[] = strtr($usluga_tpl, array(
								'{UslugaComplex_Code}'=>$row_data['UslugaComplexP_Code'],
								'{UslugaComplex_Name}'=>$row_data['UslugaComplexP_Name'],
							));
						} else {
							//пока состав лаб.услуги не будем отображать, т.к. это не надо было
							foreach ( $row_data['UslugaList'] as $usluga_data ) {
								$usluga_list[] = strtr($usluga_tpl, array(
									'{UslugaComplex_Code}'=>$usluga_data['UslugaComplex_Code'],
									'{UslugaComplex_Name}'=>$usluga_data['UslugaComplex_Name'],
								));
							}
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode('<br />',$usluga_list);
						if (6 == $type_id) {
							if ( !empty($row_data['CountInDay']))
								$ep_data['EvnPrescr_Name'] .=  ' '.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
							if ( !empty($row_data['ContReception']))
								$ep_data['EvnPrescr_Name'] .=  ', повторять непрерывно '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
							if ( !empty($row_data['Interval']))
								$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
							if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
								$ep_data['EvnPrescr_Name'] .=  ', всего '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
						}
						$ep_data['EvnPrescr_Name'] .=  '.';
						$ep_data['EvnPrescr_Name'] .= '</div>';
						break;
				}
				if ( !empty($row_data['EvnPrescr_Descr']) )
				{
					// картинка в pdf не отображается <img src="/img/icons/comment16.png" />&nbsp;
					
					if ($regionNick != 'ufa') {
						$ep_data['EvnPrescr_Name'] .= '<div>' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
					else {
						$ep_data['EvnPrescr_Name'] .= '<div>'. '<font style="text-decoration: underline;">Комментарий:</font> ' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
				}
				$parse_data['ep_list'][] = $ep_data;
			}
		}
		unset($tmp_arr);
		$parse_data['ep_list'][] = $lastRow;
		//var_dump($parse_data); exit;
		return $parse_data;
	}
	
	/**
	 * Получение данных для формы "Лист назначений"
	 *
	 * Пустые разделы, не содержащие назначений, также отображаем.
	 * Под заголовком раздела добавляем пустую строку, разбитую по дням.
	 * При добавлении назначений в раздел, пустую строку все равно отображать внизу раздела.
	 *
	 * В календаре отражаются ВСЕ назначения в рамках случая лечения,
	 * а не только из текущего движения
	 */
	function doloadEvnPrescrList($data) {
		$query = "
			select
				PrescriptionType_id,
				PrescriptionType_Code,
				PrescriptionType_Name
			from v_PrescriptionType with(nolock)
			where PrescriptionType_id in (1,2,5,6,7,10,11,12,13)
		";
		$result = $this->db->query($query, array());
		if (!is_object($result)) {
			return false;
		}
		$prescription = array();
		$prescription_type = $result->result('array');
		foreach ($prescription_type as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])] = array(
				'id' => $row['PrescriptionType_id'],
				'code' => $row['PrescriptionType_Code'],
				'title' => $row['PrescriptionType_Name'],
				'items' => array(),
			);
		}
		ksort($prescription);
		unset($prescription_type);

		$data['parentEvnClass_SysNick'] = 'EvnPSAll';
		$options = array(
			'add_where_cause' => 'and EP.PrescriptionType_id in (1,2,5,6,7,10,11,12,13)',
		);
		$rows = $this->_queryLoadViewData($data, $options);
		if ( $rows === false ) {
			return false;
		}
		foreach ($rows as $row) {
			$prescription[$this->getIndexPrescriptionType($row['PrescriptionType_id'])]['items'][] = $row;
		}
		unset($rows);

		$cur_date = DateTime::createFromFormat('d.m.Y', date('d.m.Y'));
		$response = array(array(
			'days' => array(
				'EvnPS_id' => $data['Evn_rid'],
				'Evn_pid' => $data['Evn_pid'],
				'EvnPS_setDate'=>$options['EvnPS_setDT']->format('d.m.Y'),
				'Evn_setDate'=>$options['Evn_setDT']->format('d.m.Y'),
				'cur_date'=>$cur_date->format('d.m.Y'),
				'days_diff'=>$options['Evn_setDT']->diff($cur_date)->days,
			),
			$options['key_name']=>'CommonData',
		));
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		foreach ($prescription as $prescr_group) {
			$response[] = array(
				'EvnPrescrGroup_Title'=>$prescr_group['title'],
				'PrescriptionType_Code'=>$prescr_group['code'],
				'PrescriptionType_id'=>$prescr_group['id'],
				$options['key_name']=>'EvnPrescrGroup-'.$prescr_group['id'],
			);
			//обрабатываем данные для списка назначений и календаря
			$last_ep = null;
			$first_date = null;
			$is_exe = false;
			$is_sign = false;
			$item = $cnt = 0;
			$days = array();
			$time_arr = array();
			$tmp_arr = array();
			$tmp2_arr = array();
			$DrugForm_Nick = '';
			foreach($prescr_group['items'] as $row) {
				$day = array(
					'Day_IsExec' => ($row['EvnPrescr_IsExec'] == 2),
					'Day_IsSign' => ($row['PrescriptionStatusType_id'] == 2),
				);
				if($last_ep != $row['EvnPrescr_id'])
				{
					//это первая итерация с другим назначением
					$last_ep = $row['EvnPrescr_id'];
					$item = 0;
					$first_date = $row['EvnPrescr_setDate'];
					$is_exe = false;
					$is_sign = false;
					$days = array();
					$time_arr = array();
					$tmp_arr = array();
					$tmp2_arr = array();
				}
				switch($row['PrescriptionType_id'])
				{
					case 1: case 2:
						//режим,диета
						if(!array_key_exists(($row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']),$days)) {
							if ($row['PrescriptionType_id']==1) {
								$day['EvnPrescrRegime_id'] = $row['EvnPrescrRegime_id'];
							} else {
								$day['EvnPrescrDiet_id'] = $row['EvnPrescrDiet_id'];
							}
							$days[$row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']] = $day;
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $row['EvnPrescr_cnt'])
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];
							$row['EvnPrescrGroup_Title'] = '';
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							if ($row['PrescriptionType_id'] == 1) {
								$row['PrescriptionRegimeType_Name'] = htmlspecialchars($row['PrescriptionRegimeType_Name']);
								$row['PrescriptionRegimeType_Image'] = null;
								switch ( $row['PrescriptionRegimeType_id']) {
									case 1: $row['PrescriptionRegimeType_Image'] = 'treatment-common16.png'; break;
									case 2: $row['PrescriptionRegimeType_Image'] = 'treatment-semi-bed16.png'; break;
									case 3: $row['PrescriptionRegimeType_Image'] = 'treatment-bed16.png'; break;
									case 4: $row['PrescriptionRegimeType_Image'] = 'treatment-bed-strict16.png'; break;
								}
							} else {
								$row['PrescriptionDietType_Name'] = htmlspecialchars($row['PrescriptionDietType_Name']);
								switch ( $row['PrescriptionDietType_id']) {
									case 6: $image = 'diet5bn16.png'; break;
									case 12: $image = 'diet10sn16.png'; break;
									default: $image = str_replace('X', $row['PrescriptionDietType_Code'], 'dietXn16.png'); break;
								}
								$row['PrescriptionDietType_Image'] = $image;
							}
							$row['EvnPrescr_cnt'] = count($days);
							$response[] = $row;
						}
					break;
					case 10:
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$is_exe = true;
						}
						//Наблюдения
						if(!array_key_exists(($row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']),$days)) {
							$day['EvnPrescrObserv_id'] = $row['EvnPrescrObserv_id'];
							$days[$row['EvnPrescr_id'].'-'.$row['EvnPrescr_setDate']] = $day;
						}
						if(!in_array($row['ObservTimeType_Name'],$time_arr)) {
							$time_arr[] = $row['ObservTimeType_Name'];
							$tmp2_arr[] = $row['ObservTimeType_id'];
						}
						if(empty($tmp_arr[$row['EvnPrescrObservPos_id']]))
							$tmp_arr[$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
						if($is_exe == true)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == ($row['EvnPrescr_cnt']*$row['cntParam']))
						{
							if($is_exe){
								$row['EvnPrescr_IsExec'] = 2;
							}else{
								$row['EvnPrescr_IsExec'] = 1;
							}
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['ObservParamType_Names'] = implode(', ',$tmp_arr);
							$row['ObservTimeType_Names'] = implode(', ',$time_arr);
							$row['ObservTimeType_idList'] = implode(',',$tmp2_arr);
							$row['EvnPrescr_DateInterval'] = $first_date.'-'.$row['EvnPrescr_setDate'];


							$row['EvnPrescrGroup_Title'] = '';
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							$row['EvnPrescr_cnt'] = count($days);
							$response[] = $row;
						}
						break;
					case 5:

						break;
					case 6:

						break;
					case 13:
						//Консультационная услуга
					case 11:
						//Лабораторная диагностика
						$last_ep = $row['EvnPrescr_id'];
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						$row['Usluga_List'] = $row['UslugaComplex_Name'];
						$day_date = $row['EvnPrescr_setDate'];
						if ('TimetableMedService' == $row['timetable']) {
							$day_date = mb_substr($row['RecDate'],0,10);
						}
						if ($day_date) {
							$days[$row['EvnPrescr_id'].'-'.$day_date] = $day;
						}
						$row['days'] = $days;
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						//$row['EvnPrescr_cnt'] = count($days);
						$row['EvnPrescr_cnt'] = 1;
						$response[] = $row;
						break;
					//Оперативное лечение
					case 7:
						//Функциональная диагностика
					case 12:
						if ($first_date == $row['EvnPrescr_setDate']) {
							$cnt = $row['cntUsluga'];
						}
						if( empty($tmp_arr[$row['TableUsluga_id']]) && in_array($row['PrescriptionType_id'],array(7, 12)) )
						{
							$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
							$time_arr[] = $row['UslugaComplex_id'];
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $cnt)
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['UslugaId_List'] = implode(',',$time_arr);
							$row['Usluga_List'] = implode(((in_array($row['PrescriptionType_id'],array(7, 12)))?'<br />':', '),$tmp_arr);
							$row['EvnPrescrGroup_Title'] = '';
							$day_date = $row['EvnPrescr_setDate'];
							if ('TimetableMedService' == $row['timetable']) {
								$day_date = mb_substr($row['RecDate'],0,10);
							}
							if ($day_date) {
								$days[$row['EvnPrescr_id'].'-'.$day_date] = $day;
							}
							$row['days'] = $days;
							$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
							//$row['EvnPrescr_cnt'] = count($days);
							$row['EvnPrescr_cnt'] = $row['cntUsluga'];
							$response[] = $row;
						}
						break;
				}
			}
			//добавляем пустую строку
			$response[] = array(
				'PrescriptionType_Code'=>$prescr_group['code'],
				'PrescriptionType_id'=>$prescr_group['id'],
				$options['key_name']=>'EmptyRow-'.$prescr_group['id'],
			);
		}
		return $response;
	}

	/**
	 * Возвращает для правой панели формы ввода назначениий услуг данные назначений, созданные в рамках посещения поликлиники или движения в стационаре
	 */
	function doloadEvnPrescrUslugaDataView($data) {
		//swPrescription::$disableNewMode = true;
		$options = array(
			'add_where_cause' => 'and EP.PrescriptionType_id in (1,2,5,6,7,11,10,12,13)',
			'key_name' => 'EvnPrescr_key',
			'disableNewMode' => 1,
		);
		$response = $this->doLoadViewData($data, $options);
		foreach ($response as &$row) {
			if (isset($row['DrugListData']) && is_array($row['DrugListData'])) {
				$drugList = array();
				foreach($row['DrugListData'] as $drug) {
					if (empty($drug['DoseDay']) && !empty($drug['MaxDoseDay']) && !empty($drug['MinDoseDay'])) {
						if ($drug['MaxDoseDay'] == $drug['MinDoseDay']) {
							$drug['DoseDay'] = $drug['MaxDoseDay'];
						} else  {
							$drug['DoseDay'] = $drug['MinDoseDay'].' - '.$drug['MaxDoseDay'];
						}
					}
					if(empty($drug['DoseDay']) && empty($drug['MaxDoseDay']) && empty($drug['MinDoseDay'])){
						$drug['DoseDay'] =0;
					}
					if (empty($drug['FactCntDay'])) {
						$drug['FactCntDay'] = 0;
					}
					$drugList[]=$drug;
				}
				$row['DrugListData'] = $drugList;
			}
		}
		return $response;
	}

	/**
	 * Возвращает данные групп назначений с количеством назначений
	 * для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	function doLoadPrescriptionGroupViewData($section, $evn_pid, $data = array())
	{
		$typeList = array(1,2,5,6,7,11,12,13);
		if ('EvnPrescrPlan' == $section) {
			$typeList[] = 10;
		}
		$filter = '';
		$join = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);
		if (!empty($testFilter)){
			$join .= "inner join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = EPLD.EvnPrescrLabDiag_id
				left join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnLabRequest ELR with (nolock) on ELR.EvnDirection_id = epd.EvnDirection_id
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = ELR.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				outer apply (
					select top 1
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp (nolock)
					inner join v_EvnLabRequestUslugaComplex ELRUC (nolock) on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id and ELRUC.EvnLabRequest_id = ELR.EvnLabRequest_id
					inner join v_EvnLabSample ELS (nolock) on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
				) as UCp
			";

			$filter .= "
					and (
						ED.MedPersonal_id = :MedPersonal_id
						or exists (
							select top 1 Evn_id from v_Evn with(nolock) where Evn_id = :Evn_pid and EvnClass_sysNick = 'EvnSection' and Evn_setDT <= EP.EvnPrescr_setDT and (Evn_disDT is null or Evn_disDT >= EP.EvnPrescr_setDT)
						) or ($testFilter and UCp.UslugaComplex_id is null)
					)";
		}
		$typeList = implode(',', $typeList);
		$query = "
			WITH EP as (
				select p.PrescriptionType_id, p.EvnPrescr_id, p.MedPersonal_sid, p.EvnPrescr_setDT
				from v_EvnPrescr p with (nolock)
				where p.EvnPrescr_pid = :Evn_pid and p.PrescriptionType_id not in (3,4,5,6,8,9)
			),
			EC as (
				select p.EvnClass_id
				from v_EvnCourse p with (nolock)
				where p.EvnCourse_pid = :Evn_pid
			)
			select
			pt.PrescriptionType_id,
			pt.PrescriptionType_Code,
			pt.PrescriptionType_Name,
			case
			when 1 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 1 = EP.PrescriptionType_id)
			when 2 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 2 = EP.PrescriptionType_id)
			when 10 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 10 = EP.PrescriptionType_id)
			when 7 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 7 = EP.PrescriptionType_id)
			when 11 = pt.PrescriptionType_id
			then (
				select
					count(EP.PrescriptionType_id)
				from
					EP
				$join
				where
					11 = EP.PrescriptionType_id
				$filter
			)
			when 12 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 12 = EP.PrescriptionType_id)
			when 13 = pt.PrescriptionType_id
			then (select count(EP.PrescriptionType_id) from EP with(nolock) where 13 = EP.PrescriptionType_id)
			when 5 = pt.PrescriptionType_id
			then (select count(EC.EvnClass_id) from EC with(nolock) where 114 = EC.EvnClass_id)
			when 6 = pt.PrescriptionType_id
			then (select count(EC.EvnClass_id) from EC with(nolock) where 116 = EC.EvnClass_id)
			else 0
			end as PrescriptionType_Cnt
			from v_PrescriptionType pt with (nolock)
			where pt.PrescriptionType_id in ({$typeList})
		";
		$result = $this->db->query($query, array(
			'Evn_pid' => $evn_pid,
			'MedPersonal_id' => $data['medpersonal_id']
		));
		if (!is_object($result)) {
			return array();
		}
		$response = $result->result('array');
		$tmp = array();
		foreach ($response as $row) {
			$i = $this->getIndexPrescriptionType($row['PrescriptionType_id']);
			$sysnick = swPrescription::getEvnClassSysNickByType($row['PrescriptionType_id']);
			if ($sysnick) {
				$tmp[$i] = $row;
				$tmp[$i]['EvnClass_SysNick'] = $sysnick;
				$tmp[$i]['section'] = $section;
				$tmp[$i]['EvnPrescr_pid'] = $evn_pid;
				$tmp[$i][$section.'_id'] = $evn_pid.'-'.$row['PrescriptionType_id'];
			}
		}
		// сортируем по индексу
		ksort($tmp);
		$response = array();
		foreach ($tmp as $row) {
			$response[] = $row;
		}
		return $response;
	}

	/**
	 * Возвращает для панели просмотра ЭМК данные назначений, созданные в рамках посещения поликлиники или движения в стационаре
	 */
	function doLoadViewData($data, $options = array()) {
		$isStac = (in_array($data['parentEvnClass_SysNick'], array('EvnSection', 'EvnPS', 'EvnPSAll')));
		$disableNewMode = false;
		if (!empty($options['disableNewMode'])) {
			$disableNewMode = true;
		}
		$rows = $this->_queryLoadViewData($data, $options);
		if ( $rows === false ) {
			return false;
		}
		$this->load->library('parser');
		//Получаем типы назначений
		$query = "
			select
				PT.PrescriptionType_id,
				PT.PrescriptionType_Code,
				PT.PrescriptionType_Name
			from v_PrescriptionType PT with (nolock)
			where PT.PrescriptionType_id in (1,2,5,6,7,10,11,12,13)
		";
		$result = $this->db->query($query, array(
			 'Evn_pid' => $data['Evn_pid']
		));
		if (!is_object($result)) {
			return false;
		}
		$prescription = array();
		$prescription_type = $result->result('array');

		$cntCourse = array();
		foreach ($prescription_type as $row) {
			if (!$isStac && $row['PrescriptionType_id']==10) {
				continue;
			}
			$i = $this->getIndexPrescriptionType($row['PrescriptionType_id']);
			$prescription[$i] = array(
				'id' => $row['PrescriptionType_id'],
				'code' => $row['PrescriptionType_Code'],
				'title' => $row['PrescriptionType_Name'],
				'items' => array(),
			);
			$cntCourse[$i] = 0;
		}
		ksort($prescription);
		unset($prescription_type);
		$last_course = null;
		foreach ($rows as $row) {
			$i = $this->getIndexPrescriptionType($row['PrescriptionType_id']);
			$prescription[$i]['items'][] = $row;
			$prescription[$i]['EvnPrescr_pid'] = $row['EvnPrescr_pid'];
			$prescription[$i]['count'] = $row['cntInPrescriptionTypeGroup'];
			if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
			{
				//это первая итерация с другим курсом
				$last_course = $row['EvnCourse_id'];
				$cntCourse[$i]++;
			}
		}
		unset($rows);
		/*
		var_dump($prescription);
		var_dump($cntCourse);
		exit;*/
		$response = array();
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		foreach ($prescription as $i => $prescr_group) {

			if ( empty($prescr_group['count']) ) {
				$prescr_group['count'] = 0;
				$EvnPrescrGroup_Count = '';
			} else {
				$EvnPrescrGroup_Count = $prescr_group['count'];
			}

			if ( $cntCourse[$i] > 0 ) {
				if ($prescr_group['id']==5) {
					if ($isStac && $disableNewMode) {
						$EvnPrescrGroup_Count += $cntCourse[$i];
					} else {
						$EvnPrescrGroup_Count = $cntCourse[$i];
					}
				} else {
					$EvnPrescrGroup_Count += $cntCourse[$i];
				}
			}

			$response[] = array(
				'EvnPrescrGroup_Title'=>$prescr_group['title'],
				'EvnCourse_id'=>null,
				'isEvnCourse'=>0,
				'PrescriptionType_id'=>$prescr_group['id'],
				'PrescriptionType_Code'=>$prescr_group['code'],
				'EvnPrescr_pid'=>$data['Evn_pid'],
				'EvnPrescrGroup_Count'=>$EvnPrescrGroup_Count,
				'EvnCourse_Count'=>$cntCourse[$i],
				'cntInPrescriptionTypeGroup'=>$prescr_group['count'],
				$options['key_name']=>$data['Evn_pid'].'-'.$prescr_group['id'],
			);
			
			//обрабатываем данные для списка назначений и календаря
			$is_first_item_type = null;
			$last_type = null;
			$last_ep = null;
			$last_course = null;
			$index_course = null;
			$item = null;
			$first_date = null;
			$is_exe = false;
			$is_sign = false;
			$time_arr = array();
			$tmp_arr = array();
			$graf_arr = array();
			$numCourse = 0;
			$cnt = 0;
			$drug_cnt = 0;
			$drug_cnt_common = 0;
			$drug_item = null;
			foreach($prescr_group['items'] as $row) {
				
				if($last_type != $row['PrescriptionType_id'])
				{
					//создаем строку с заголовком на первой итерации с другим типом назначений
					$last_type = $row['PrescriptionType_id'];
					$is_first_item_type = true;
					$numCourse = 0;
				}
				$row['EvnPrescrGroup_Count']=$EvnPrescrGroup_Count;
				$row['isEvnCourse']=0;
				$row['LastItemOfGroupAdditionalHtml']='';//см. комментарий ниже
				switch($row['PrescriptionType_id'])
				{
					case 1: case 2:
						//режим,диета - может быть только одно назначение
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$item = 0;
							$last_ep = $row['EvnPrescr_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$is_exe = false;
							$is_sign = false;
							$last_course = null;
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $row['EvnPrescr_cnt'])
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['EvnPrescr_setDate'] = $first_date.'&nbsp;—&nbsp;'.$row['EvnPrescr_setDate'];
							$row['EvnPrescrGroup_Title'] = '';
							$response[] = $row;
						}
						break;
					case 10:
						//Наблюдения
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$item = 0;
							$last_ep = $row['EvnPrescr_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$time_arr = array();
							$tmp_arr = array();
							$is_exe = true;
							$is_sign = false;
							$last_course = null;
						}
						if(!in_array($row['ObservTimeType_Name'],$time_arr))
							$time_arr[] = $row['ObservTimeType_Name'];
						if(empty($tmp_arr[$row['EvnPrescrObservPos_id']]))
							$tmp_arr[$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
						if($is_exe == true)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == ($row['EvnPrescr_cnt']*$row['cntParam']))
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							else{
								$row['EvnPrescr_IsExec'] = 1;
							}
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['Params'] = implode(', ',$tmp_arr);
							$row['EvnPrescr_setTime'] = implode(', ',$time_arr);
							$row['EvnPrescr_setDate'] = $first_date.'&nbsp;—&nbsp;'.$row['EvnPrescr_setDate'];
							$row['EvnPrescrGroup_Title'] = '';
							$response[] = $row;
							//print_r($response);
						}
						break;
					case 4:
						//Консультации
						$last_ep = $row['EvnPrescr_id'];
						$row['EvnPrescrGroup_Title'] = '';
						$response[] = $row;
						$last_course = null;
						break;
					case 5:
						//Лекарственное лечение
						/*foreach($row as $dt=>$val){
							$row[$dt]=iconv('', 'utf-8',$val);
						}*/
						if(!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$numCourse++;
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$tmp_arr = array();
							$graf_arr = array();
							if ($isStac && $disableNewMode) {
								$drug_cnt_common = $row['cntDrug'];
								$cnt = $row['PrescrCount']*$row['cntCourseDrug']*$row['cntDrug'];
							} else {
								$cnt = $row['cntCourseDrug']*$row['cntDrug'];
							}
							$index_course = count($response);
							$response[$index_course] = $row;
						}
						if ($last_ep != $row['EvnPrescr_id']) {
							//это первая итерация с другим назначением курса
							$last_ep = $row['EvnPrescr_id'];
							$graf_arr = array();
							$drug_cnt = $row['cntDrug'];
							$drug_item = 0;
							if ($isStac && $disableNewMode && $drug_cnt_common > $drug_cnt) {
								$cnt = $cnt - $row['cntCourseDrug']*($drug_cnt_common - $drug_cnt);
							}
						}
						if (empty($tmp_arr[$row['EvnCourseTreatDrug_id']])) {
							$drug_data = array(
								'Drug_Name'=>$row['CourseDrug_Name'],
								'DrugTorg_Name'=>$row['CourseDrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnCourseTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick($row['CourseDrugForm_Name'], $row['CourseDrug_Name']),
								'Kolvo'=>floatval($row['EvnCourseTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['CourseEdUnits_Nick'],
								'MaxDoseDay'=>$row['EvnCourseTreatDrug_MaxDoseDay'],
								'MinDoseDay'=>$row['EvnCourseTreatDrug_MinDoseDay'],
								'PrescrDose'=>$row['EvnCourseTreatDrug_PrescrDose'],
							);
							//тут это нельзя делать! array_walk($drug_data,'ConvertFromWin1251ToUTF8');
							$tmp_arr[$row['EvnCourseTreatDrug_id']] = $drug_data;
						}
						$item += 1;
						if($item == $cnt)
						{
							$response[$index_course] = array(
								'EvnPrescrGroup_Title'=>'Курс '.$numCourse,
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'isEvnCourse'=>1,
								'DrugListData'=>$tmp_arr,
								'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
								'PerformanceType_Name'=>$row['PerformanceType_Name'],
								'EvnCourse_begDate'=>$first_date,
								'MaxCountInDay'=>$row['MaxCountInDay'],
								'MinCountInDay'=>$row['MinCountInDay'],
								'Duration'=>$row['Duration'],
								'DurationType_Nick'=>$row['DurationType_Nick'],
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'EvnPrescr_pid'=>$row['EvnPrescr_pid'],
								'EvnPrescrGroup_Count'=>$row['PrescrCount'],//($isStac)?$row['PrescrCount']:-1,
								'EvnPrescr_Descr'=>htmlspecialchars($row['EvnPrescr_Descr']),
								'LastItemOfGroupAdditionalHtml'=>'',
								$options['key_name']=>$row['EvnPrescr_pid'].'-'.$row['EvnCourse_id'],
							);
							if (!$isStac) {
								$response[$index_course]['EvnPrescr_id'] = $row['EvnPrescr_id'];
								$response[$index_course]['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
								$response[$index_course]['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
								$response[$index_course]['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
								$response[$index_course]['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
								$response[$index_course]['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
							}
						}
						if ($isStac && empty($graf_arr[$row['EvnPrescrTreatDrug_id']])) {
							$drug_item++;
							$drug_data = array(
								'Drug_Name'=>$row['Drug_Name'],
								'DrugTorg_Name'=>$row['DrugTorg_Name'],
								'KolvoEd'=>floatval($row['EvnPrescrTreatDrug_KolvoEd']),
								'DrugForm_Nick'=>$this->EvnPrescrTreat_model->getDrugFormNick(($row['DrugForm_Name']." "), ($row['Drug_Name'])),
								'Kolvo'=>floatval($row['EvnPrescrTreatDrug_Kolvo']),
								'EdUnits_Nick'=>$row['EdUnits_Nick'],
								'DoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
								'PrescrCntDay'=>$row['PrescrCntDay'],
								'FactCntDay'=>$row['FactCntDay'],
							);
							if (!$disableNewMode) {
								//EvnCourseTreatDrug_id почему-то у всех одинаковый
								//$drug_data['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
								if (!empty($row['Drug_id'])) {
									$drug_data['Drug_key'] = 'Drug'.$row['Drug_id'];
								} else {
									$drug_data['Drug_key'] = 'DrugComplexMnn'.$row['DrugComplexMnn_id'];
								}
							}
							//тут это нельзя делать! array_walk($drug_data,'ConvertFromWin1251ToUTF8');
							$graf_arr[$row['EvnPrescrTreatDrug_id']] = $drug_data;
							if ($drug_cnt==$drug_item) {
								if ($disableNewMode) {
									$row['EvnPrescrGroup_Title'] = '';
									$row['DrugListData'] = $graf_arr;
									$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
									$response[] = $row;
								} else {
									if (!empty($row['EvnPrescr_setDate']) && isset($response[$index_course]) && isset($response[$index_course]['DrugListData'])) {
										//нужно сложить в список назначений курса
										if (empty($response[$index_course]['PrescrListData'])) {
											$response[$index_course]['PrescrListData'] = array();
										}
										$prescr_data = array();
										$prescr_data['PrescriptionType_id'] = $row['PrescriptionType_id'];
										$prescr_data['PrescriptionType_Code'] = $row['PrescriptionType_Code'];
										$prescr_data['EvnPrescr_pid'] = $row['EvnPrescr_pid'];
										$prescr_data['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
										$prescr_data['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
										$prescr_data['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
										$prescr_data['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
										$prescr_data['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
										$prescr_data['DrugListData'] = $graf_arr;
										$response[$index_course]['PrescrListData'][$row['EvnPrescr_id']] = $prescr_data;
									}
								}
							}
						}
						break;
					case 6:
						//Манипуляции и процедуры
						if (!empty($row['EvnCourse_id']) && $last_course != $row['EvnCourse_id'])
						{
							//это первая итерация с другим курсом
							$item = 0;
							$last_course = $row['EvnCourse_id'];
							$first_date = $row['EvnPrescr_setDate'];
							$tmp_arr = array();
							$cnt = $row['PrescrCount'];
							$index_course = count($response);
							$response[$index_course] = $row;
						}
						if(!empty($row['MedService_Name']) && empty($tmp_arr[$row['MedService_id']]))
						{
							$tmp_arr[$row['MedService_id']] = $row['MedService_Name'];
						}
						$item += 1;
						if($item == $cnt)
						{
							$response[$index_course] = array(
								'EvnPrescrGroup_Title'=>$row['CourseUslugaComplex_Code'].' '.$row['CourseUslugaComplex_Name'],
								'EvnCourse_id'=>$row['EvnCourse_id'],
								'isEvnCourse'=>1,
								'EvnCourse_begDate'=>$first_date,
								'EvnCourse_endDate'=>$row['EvnPrescr_setDate'],
								'MaxCountInDay'=>$row['MaxCountInDay'],
								'MinCountInDay'=>$row['MinCountInDay'],
								'MedServices'=>implode(', ',$tmp_arr),
								'PrescriptionType_id'=>$row['PrescriptionType_id'],
								'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
								'EvnPrescr_pid'=>$row['EvnPrescr_pid'],
								'EvnPrescrGroup_Count'=>$row['PrescrCount'],
								'LastItemOfGroupAdditionalHtml'=>'',
								$options['key_name']=>$row['EvnPrescr_pid'].'-'.$row['EvnCourse_id'],
							);
							if (!$this->options['prescription']['enable_show_service_code']) {
								$response[$index_course]['EvnPrescrGroup_Title'] = $row['CourseUslugaComplex_Name'];
							}
						}
						$last_ep = $row['EvnPrescr_id'];
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						if ($this->options['prescription']['enable_show_service_code']) {
							$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
						} else {
							$row['Usluga_List'] = $row['UslugaComplex_Name'];
						}
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						$response[] = $row;
						break;
					case 13:
						//Консультационная услуга
					case 11:
						//Лабораторная диагностика
						$last_course = null;
						$last_ep = $row['EvnPrescr_id'];
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						if ($this->options['prescription']['enable_show_service_code']) {
							$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
						} else {
							$row['Usluga_List'] = $row['UslugaComplex_Name'];
						}
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						$response[] = $row;
						break;
					case 7:
						//Оперативное лечение
						$last_course = null;
						$last_ep = $row['EvnPrescr_id'];
						$row['EvnPrescrGroup_Title'] = '';
						$row['UslugaId_List'] = $row['UslugaComplex_id'];
						if ($this->options['prescription']['enable_show_service_code']) {
							$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
						} else {
							$row['Usluga_List'] = $row['UslugaComplex_Name'];
						}
						$row['EvnPrescr_Descr'] = htmlspecialchars($row['EvnPrescr_Descr']);
						$response[] = $row;
						break;
					case 12:
						//Функциональная диагностика
						if($last_ep != $row['EvnPrescr_id'])
						{
							//это первая итерация с другим назначением
							$item = 0;
							$last_ep = $row['EvnPrescr_id'];
							$last_course = null;
							$first_date = $row['EvnPrescr_setDate'];
							$time_arr = array();
							$graf_arr = array();
							$tmp_arr = array();
							//uslugaList
							$is_exe = false;
							$is_sign = false;
							$cnt = $row['cntUsluga'];
						}
						if( empty($tmp_arr[$row['TableUsluga_id']]) && in_array($row['PrescriptionType_id'],array(7, 12)) )
						{
							$time_arr[] = $row['UslugaComplex_id'];
							if ($this->options['prescription']['enable_show_service_code']) {
								$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
							} else {
								$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
							}
						}
						if($is_exe == false)
							$is_exe = ($row['EvnPrescr_IsExec'] == 2);
						if($is_sign == false)
							$is_sign = ($row['PrescriptionStatusType_id'] == 2);
						$item = $item + 1;
						if($item == $cnt)
						{
							if($is_exe)
								$row['EvnPrescr_IsExec'] = 2;
							if($is_sign)
								$row['PrescriptionStatusType_id'] = 2;
							$row['UslugaId_List'] = implode(',',$time_arr);
							$row['Usluga_List'] = implode(((in_array($row['PrescriptionType_id'],array(7, 12)))?'<br />':', '),$tmp_arr);
							$row['EvnPrescrGroup_Title'] = '';
							$response[] = $row;
						}
						break;
					default:
						$last_ep = null;
						$item = null;
						$first_date = null;
						$is_exe = false;
						$is_sign = false;
						$time_arr = array();
						$graf_arr = array();
						$row['EvnPrescrGroup_Title'] = '';
						//$response[] = $row;
						break;
				}
			}
		}
		$currentItem = 0;
		$sizeOfGroup = 0;
		while ($currentItem<  sizeof($response)) {
			/*
			var_export(array(
				'currentItem'=>$currentItem,
				'EvnPrescrGroup_Title'=>$response["$currentItem"]['EvnPrescrGroup_Title'],
				'PrescriptionType_id'=>$response["$currentItem"]['PrescriptionType_id'],
				'EvnPrescrGroup_Count'=>$response["$currentItem"]['EvnPrescrGroup_Count'],
				'nextItem'=>($currentItem+$response["$currentItem"]['EvnPrescrGroup_Count']+1),
			));
			*/
			$currentItem += $response["$currentItem"]['EvnPrescrGroup_Count'];
			//$response["$currentItem"]['LastItemOfGroupAdditionalHtml'] предполагалось сдалать флагом и довлять по этому флагу во вьюхе
			//html, который будет закрывать группу направлений (eew_evn_prescrplan_list_item.php), однако после первых двух групп
			//$LastItemOfGroupAdditionalHtml автоматически становится true для каждого направления. Необъяснимо, но выяснять сейчас времени нет
			$response["$currentItem"]['LastItemOfGroupAdditionalHtml'] = '</dd></ul>';
			$currentItem++;
		}
		//загружаем документы
		$tmp_arr = array();
		$evnPrescrIdList = array();
		foreach ($response as $key => $row) {
			if (in_array($row['PrescriptionType_Code'], array(11,12))
				&& isset($row['EvnPrescr_IsExec'])
				&& 2 == $row['EvnPrescr_IsExec']
				&& isset($row['EvnPrescr_IsHasEvn'])
				&& 2 == $row['EvnPrescr_IsHasEvn']
			) {
				$response[$key]['EvnXml_id'] = null;
				$id = $row['EvnPrescr_id'];
				$evnPrescrIdList[] = $id;
				$tmp_arr[$id] = $key;
			}
		}
		if (count($evnPrescrIdList) > 0) {
			$evnPrescrIdList = implode(',',$evnPrescrIdList);
			$query = "
			WITH EvnPrescrEvnXml
			as (
				select doc.EvnXml_id, EU.EvnPrescr_id
				from  v_EvnUsluga EU (nolock)
				inner join v_EvnXml doc (nolock) on doc.Evn_id = EU.EvnUsluga_id
				where EU.EvnPrescr_id in ({$evnPrescrIdList})
			)

			select EvnXml_id, EvnPrescr_id from EvnPrescrEvnXml with(nolock)
			order by EvnPrescr_id";
			$result = $this->db->query($query);
			if ( is_object($result) ) {
				$evnPrescrIdList = $result->result('array');
				foreach ($evnPrescrIdList as $row) {
					$id = $row['EvnPrescr_id'];
					if (isset($tmp_arr[$id])) {
						$key = $tmp_arr[$id];
						if (isset($response[$key])) {
							$response[$key]['EvnXml_id'] = $row['EvnXml_id'];
						}
					}
				}
			}
		}
		//exit;
		//var_export($response);exit;
		return $response;
	}

	/**
	 * Возвращает результат запроса данных назначений, созданные в рамках посещения поликлиники или движения в стационаре
	 */
	private function _queryLoadViewData($data, &$options = array()) {
		if(empty($data['parentEvnClass_SysNick']))
		{
			$data['parentEvnClass_SysNick'] = 'EvnVizitPL';
		}
		$add_where_cause = '';
		$ep_where_cause = ' = :Evn_pid';
		$queryParams = array(
			'Evn_pid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id']
		);
		switch($data['parentEvnClass_SysNick']) {
			// назначения в рамках посещения поликлиники
			case 'EvnVizitPL';
				$key_name = 'EvnPrescrPolka_id';
				$edit_conditions = 'EvnVizitPL.Lpu_id = :Lpu_id AND isnull(EvnVizitPL.EvnVizitPL_IsSigned,1) = 1';
				$add_join = 'left join v_EvnVizitPL EvnVizitPL with (nolock) on EvnVizitPL.EvnVizitPL_id = EP.EvnPrescr_pid';
				break;
			// назначения в рамках стоматологического посещения поликлиники
			case 'EvnVizitPLStom';
				$key_name = 'EvnPrescrStom_id';
				$edit_conditions = 'EvnVizitPLStom.Lpu_id = :Lpu_id AND isnull(EvnVizitPLStom.EvnVizitPLStom_IsSigned,1) = 1';
				$add_join = 'left join v_EvnVizitPLStom EvnVizitPLStom with (nolock) on EvnVizitPLStom.EvnVizitPLStom_id = EP.EvnPrescr_pid';
				break;
			// назначения в рамках движения в стационаре
			case 'EvnSection';
				$key_name = 'EvnPrescrPlan_id';
				$edit_conditions = 'EvnSection.Lpu_id = :Lpu_id AND isnull(EvnSection.EvnSection_IsSigned,1) = 1';
				$add_join = 'left join v_EvnSection EvnSection with (nolock) on EvnSection.EvnSection_id = EP.EvnPrescr_pid';
				break;
			// назначения в приемном отделении
			case 'EvnPS';
				$key_name = 'EvnPrescr_key';
				$edit_conditions = 'EvnPS.Lpu_id = :Lpu_id';
				$add_join = 'left join v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EP.EvnPrescr_pid';
				break;
			// назначения в рамках всего случая лечения в стационаре
			case 'EvnPSAll';
				$query = "
					select Evn.Evn_id, Evn.Evn_setDT
					from v_Evn Evn with (nolock)
					where Evn.Evn_rid = :Evn_rid and Evn.EvnClass_id in (30,32)
				";
				$queryParams = array(
					'Evn_rid' => $data['Evn_rid'],
				);
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$tmp = $result->result('array');
				$evn_pid_list = array();
				foreach ($tmp as $row) {
					$evn_pid_list[] = $row['Evn_id'];
					if ($row['Evn_id'] == $data['Evn_rid']) {
						$options['EvnPS_setDT'] = $row['Evn_setDT'];
					}
					if ($row['Evn_id'] == $data['Evn_pid']) {
						//Дата текущего движения в профильном или приемном отделении
						$options['Evn_setDT'] = $row['Evn_setDT'];
					}
				}
				if (empty($evn_pid_list) || empty($options['EvnPS_setDT']) || empty($options['Evn_setDT'])) {
					return false;
				}
				$key_name = 'EvnPrescr_key';
				$edit_conditions = 'Evn.Evn_id = :Evn_pid AND Evn.Lpu_id = :Lpu_id AND isnull(Evn.Evn_IsSigned,1) = 1';
				$add_join = 'left join v_Evn Evn with (nolock) on Evn.Evn_id = EP.EvnPrescr_pid';
				$ep_where_cause = ' in ('. implode(', ', $evn_pid_list) .')';
				$queryParams = array(
					'Evn_pid' => $data['Evn_pid'],
					'Lpu_id' => $data['Lpu_id']
				);
				break;
			default:
				$key_name = 'EvnPrescr_key';
				$edit_conditions = '1=2';
				$add_join = '';
		}

		//сначала проверяем, есть ли вообще назначения
		$query = "
			select COUNT(EvnPrescr_id) as cnt
			from v_EvnPrescr with (nolock)
			where EvnPrescr_pid {$ep_where_cause}
			and isnull(PrescriptionStatusType_id,1) != 3
		";
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		
		if (isset($options['key_name'])) {
			$key_name = $options['key_name'];
		}
		if (isset($options['add_where_cause'])) {
			$add_where_cause .= $options['add_where_cause'];
		}
		$options['key_name'] = $key_name;
		
		$rows = $result->result('array');
		if ($rows[0]['cnt'] == 0) {
			return array();
		}

		$add_select = '';
		if ('EvnPrescr_key' == $options['key_name']) {
			$add_select .= ',EvnXmlDir.EvnXml_id as EvnXmlDir_id
				,EvnXmlDir.XmlType_id as EvnXmlDirType_id';
			$add_join .= "
				outer apply (
					select top 1 EvnXml.EvnXml_id, XmlType.XmlType_id
					from XmlType with (nolock)
					left join EvnXml with (nolock) on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = case
					when exists (
						select top 1 uca.UslugaComplexAttribute_id
						from UslugaComplexAttribute uca (nolock)
						inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						and ucat.UslugaComplexAttributeType_SysNick like 'kt'
						where uca.UslugaComplex_id = UC.UslugaComplex_id
					) then 19
					when exists (
						select top 1 uca.UslugaComplexAttribute_id
						from UslugaComplexAttribute uca (nolock)
						inner join UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						and ucat.UslugaComplexAttributeType_SysNick like 'mrt'
						where uca.UslugaComplex_id = UC.UslugaComplex_id
					) then 18
					else 2 end
				) EvnXmlDir";
		}
		
		$query = "
			select
				case when {$edit_conditions} then 'edit' else 'view' end as accessType,
				convert(varchar,EP.EvnPrescr_id)+
				'-'+convert(varchar,coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EPOU.EvnPrescrOperUsluga_id,EPFDU.EvnPrescrFuncDiagUsluga_id,0)) --EPTD.EvnPrescrTreatDrug_id,EPPRU.EvnUslugaCommon_id,
				as {$key_name},
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,convert(varchar,coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT),104) as EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec --EPTTT.EvnPrescrTreatTimetable_IsExec,EPPRTT.EvnPrescrProcTimetable_IsExec,
				,case when isnull(EU.EvnUsluga_id,EDr.EvnDrug_id) is null then 1 else 2 end as EvnPrescr_IsHasEvn
				-- Если в качестве даты-времени выполнения брать EU.EvnUsluga_setDT, то дата может не отобразиться, если при выполнении не была создана услуга или услуга не связана с назначением
				-- Поэтому решил использовать EP.EvnPrescr_updDT, т.к. после выполнения эта дата не меняется
				,case when (EP.PrescriptionType_id in (7,11,12,13) and 2 = EP.EvnPrescr_IsExec) then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null end as EvnPrescr_execDT
				,case when ED.EvnDirection_id is null then 1 else 2 end as EvnPrescr_IsDir
				,coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id) as PrescriptionStatusType_id
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Code
				,PT.PrescriptionType_Name
				,EPin.cntInPrescriptionTypeGroup
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,case
					when Regime.EvnPrescrRegime_id is not null
					then EPRC.cnt
					when Diet.EvnPrescrDiet_id is not null
					then EPDC.cnt
					when Obs.EvnPrescrObserv_id is not null
					then EPOC.cnt
				else
					null
				end as EvnPrescr_cnt --число дней указаннного назначения
				,IsCito.YesNo_Code as IsCito_Code
				,IsCito.YesNo_Name as IsCito_Name
				,coalesce(EP.EvnPrescr_Descr,Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,'') as EvnPrescr_Descr,
				--1 режим
				Regime.EvnPrescrRegime_id,
				ISNULL(PRT.PrescriptionRegimeType_id, 0) as PrescriptionRegimeType_id,--тип режима
				ISNULL(PRT.PrescriptionRegimeType_Code, 0) as PrescriptionRegimeType_Code,
				ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name,
				--2 диета
				Diet.EvnPrescrDiet_id,
				ISNULL(PDT.PrescriptionDietType_id, 0) as PrescriptionDietType_id,--тип диеты
				ISNULL(PDT.PrescriptionDietType_Code, '') as PrescriptionDietType_Code,
				ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name,
				--10
				Obs.EvnPrescrObserv_id,
				OTT.ObservTimeType_id,
				OTT.ObservTimeType_Name,
				EPOP.EvnPrescrObservPos_id,
				OPT.ObservParamType_Name,
				case
					when Obs.EvnPrescrObserv_id is not null
					then EPOPC.cnt
				else
					null
				end as cntParam
				--5
				,EPTD.EvnPrescrTreatDrug_id
				,Drug.Drug_id
				,dcm.DrugComplexMnn_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_KolvoEd, 10, 2)) as EvnPrescrTreatDrug_KolvoEd
				,ISNULL(df.NAME,Drug.DrugForm_Name) as DrugForm_Name
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_Kolvo, 10, 2)) as EvnPrescrTreatDrug_Kolvo
				,isnull(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as EdUnits_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay
				,EPTD.EvnPrescrTreatDrug_FactCount as FactCntDay
				,EPT.EvnPrescrTreat_PrescrCount as PrescrCntDay
				,EPTDC.cntDrug

				,ec_drug.EvnCourseTreatDrug_id
				,coalesce(CourseDrug.Drug_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrug_Name
				,coalesce(CourseDrug.DrugTorg_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrugTorg_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_KolvoEd, 10, 2)) as EvnCourseTreatDrug_KolvoEd
				,ISNULL(CourseDf.NAME,CourseDrug.DrugForm_Name) as CourseDrugForm_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_Kolvo, 10, 2)) as EvnCourseTreatDrug_Kolvo
				,isnull(ec_mu.SHORTNAME, ec_cu.SHORTNAME) as CourseEdUnits_Nick
				,ec_drug.EvnCourseTreatDrug_MaxDoseDay
				,ec_drug.EvnCourseTreatDrug_MinDoseDay
				,ec_drug.EvnCourseTreatDrug_PrescrDose
				,ec_drug.EvnCourseTreatDrug_FactDose
				,ECTDC.cntCourseDrug

				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
				--6
				,CUC.UslugaComplex_id as CourseUslugaComplex_id
				,CUC.UslugaComplex_2011id as CourseUslugaComplex_2011id
				,CUC.UslugaComplex_Name as CourseUslugaComplex_Name
				,CUC.UslugaComplex_Code as CourseUslugaComplex_Code
				,MS.MedService_id
				,MS.MedService_Name
				--5,6
				,isnull(EPT.EvnCourse_id, EPPR.EvnCourse_id) as EvnCourse_id
				,ISNULL(convert(varchar,ISNULL(EPT.EvnPrescrTreat_setDT,EPPR.EvnPrescrProc_setDT),104), '') as graf_date
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECPR.EvnCourseProc_MaxCountDay, '') as MaxCountInDay
				,coalesce(ECT.EvnCourseTreat_MinCountDay,ECPR.EvnCourseProc_MinCountDay, '') as MinCountInDay
				,coalesce(ECT.EvnCourseTreat_Duration,ECPR.EvnCourseProc_Duration, '') as Duration
				,coalesce(ECT.EvnCourseTreat_ContReception,ECPR.EvnCourseProc_ContReception, '') as ContReception
				,coalesce(ECT.EvnCourseTreat_Interval,ECPR.EvnCourseProc_Interval, '') as Interval
				,case
					when EP.PrescriptionType_id = 5 then EPTC.cnt
					when EP.PrescriptionType_id = 6 then EPPC.cnt
					else 0
				end as PrescrCount
				,DTP.DurationType_Nick
				,DTI.DurationType_Nick as DurationType_IntNick
				,DTN.DurationType_Nick as DurationType_RecNick
				--6,7,11,12,13
				,UC.UslugaComplex_id
				,UC.UslugaComplex_2011id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				--7,12
				,isnull(EPOU.EvnPrescrOperUsluga_id, EPFDU.EvnPrescrFuncDiagUsluga_id) as TableUsluga_id
				,case
					when EP.PrescriptionType_id = 7 then EPOUC.cnt
					when EP.PrescriptionType_id = 12 then EPFDUC.cnt
					else 0
				end as cntUsluga
				--
				,ED.EvnDirection_id
				,EQ.EvnQueue_id
				,case when ED.EvnDirection_Num is null /*or isnull(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
				,case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'') +' / '+ isnull(Lpu.Lpu_Nick,'')
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
				else '' end as RecTo
				,case
					when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
				else '' end as RecDate
				,case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as timetable
				,case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id
				else '' end as timetable_id
				,EP.EvnPrescr_pid as timetable_pid
				,LU.LpuUnitType_SysNick
				,DT.DirType_Code
				{$add_select}
				from v_EvnPrescr EP with (nolock)
				inner join PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				outer apply (select count(EPin.PrescriptionType_id) as cntInPrescriptionTypeGroup from v_EvnPrescr EPin with (nolock) where EPin.EvnPrescr_pid = EP.EvnPrescr_pid and EPin.PrescriptionType_id = EP.PrescriptionType_id group by EPin.PrescriptionType_id) EPin
				outer apply (select count(EvnPrescrRegime_id) as cnt from v_EvnPrescrRegime with (nolock) where EvnPrescrRegime_pid = EP.EvnPrescr_id and PrescriptionStatusType_id != 3) EPRC
				outer apply (select count(EvnPrescrDiet_id) as cnt from v_EvnPrescrDiet with (nolock) where EvnPrescrDiet_pid = EP.EvnPrescr_id and PrescriptionStatusType_id != 3) EPDC
				outer apply (select count(EvnPrescrObserv_id) as cnt from v_EvnPrescrObserv with (nolock) where EvnPrescrObserv_pid = EP.EvnPrescr_id and PrescriptionStatusType_id != 3) EPOC
				outer apply (select count(EvnPrescrObservPos_id) as cnt from v_EvnPrescrObservPos with (nolock) where EvnPrescr_id = EP.EvnPrescr_id) EPOPC
				outer apply (select count(EvnPrescrTreatDrug_id) as cntDrug from v_EvnPrescrTreatDrug with (nolock) where EP.PrescriptionType_id = 5 and EvnPrescrTreat_id = EP.EvnPrescr_id) EPTDC
				outer apply (select count(EvnPrescrOperUsluga_id) as cnt from v_EvnPrescrOperUsluga with (nolock) where EvnPrescrOper_id = EP.EvnPrescr_id) EPOUC
				outer apply (select count(EvnPrescrFuncDiagUsluga_id) as cnt from v_EvnPrescrFuncDiagUsluga with (nolock) where EvnPrescrFuncDiag_id = EP.EvnPrescr_id) EPFDUC
				left join v_EvnPrescrTreat EPT with (nolock) on EP.PrescriptionType_id = 5 and EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
				outer apply (select count(EvnCourseTreatDrug_id) as cntCourseDrug from v_EvnCourseTreatDrug with (nolock) where EP.PrescriptionType_id = 5 and EvnCourseTreat_id = EPT.EvnCourse_id) ECTDC
				left join YesNo IsCito with (nolock) on IsCito.YesNo_id = isnull(EP.EvnPrescr_IsCito,1)
				left join v_EvnPrescrRegime Regime with (nolock) on EP.PrescriptionType_id = 1 and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join PrescriptionRegimeType PRT with (nolock) on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				left join v_EvnPrescrDiet Diet with (nolock) on EP.PrescriptionType_id = 2 and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
				left join PrescriptionDietType PDT with (nolock) on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				left join v_EvnCourseTreat ECT with (nolock) on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				outer apply (select count(EvnPrescrTreat_id) as cnt from v_EvnPrescrTreat with (nolock) where ECT.EvnCourseTreat_id = EvnCourse_id) EPTC
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				left join v_EvnCourseTreatDrug ec_drug with (nolock) on EP.PrescriptionType_id = 5 and ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
				left join rls.MASSUNITS ep_mu with (nolock) on EP.PrescriptionType_id = 5 and EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EP.PrescriptionType_id = 5 and EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.MASSUNITS ec_mu with (nolock) on EP.PrescriptionType_id = 5 and ec_drug.MASSUNITS_ID = ec_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ec_cu with (nolock) on EP.PrescriptionType_id = 5 and ec_drug.CUBICUNITS_id = ec_cu.CUBICUNITS_id
				left join PerformanceType PFT with (nolock) on  EP.PrescriptionType_id = 5 and ECT.PerformanceType_id = PFT.PerformanceType_id
				left join PrescriptionIntroType PIT with (nolock) on EP.PrescriptionType_id = 5 and ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_Drug CourseDrug with (nolock) on EP.PrescriptionType_id = 5 and CourseDrug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn CourseDcm with (nolock) on EP.PrescriptionType_id = 5 and CourseDcm.DrugComplexMnn_id = ec_drug.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS CourseDf with (nolock) on CourseDcm.CLSDRUGFORMS_ID = CourseDf.CLSDRUGFORMS_ID
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECPR with (nolock) on EP.PrescriptionType_id = 6 and ECPR.EvnCourseProc_id = EPPR.EvnCourse_id
				outer apply (select count(EvnPrescrProc_id) as cnt from v_EvnPrescrProc with (nolock) where ECPR.EvnCourseProc_id = EvnCourse_id) EPPC
				left join DurationType DTP with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECPR.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECPR.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECPR.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				left join v_EvnPrescrOper EPO with (nolock) on EP.PrescriptionType_id = 7 and EPO.EvnPrescrOper_id = EP.EvnPrescr_id
				--left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrOperUsluga with (nolock) where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id
				) EPOU
				--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLD
				--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				) EPFDU
				--left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrConsUsluga with (nolock) where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				) EPCU
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id, EPFDU.UslugaComplex_id, EPLD.UslugaComplex_id, EPOU.UslugaComplex_id, EPCU.UslugaComplex_id)
				left join v_UslugaComplex CUC with (nolock) on EP.PrescriptionType_id = 6 and CUC.UslugaComplex_id = ECPR.UslugaComplex_id
				left join v_EvnPrescrObserv Obs with (nolock) on EP.PrescriptionType_id = 10 and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				left join v_ObservTimeType OTT with (nolock) on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP with (nolock) on EP.PrescriptionType_id = 10 and EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT with (nolock) on EP.PrescriptionType_id = 10 and OPT.ObservParamType_id = EPOP.ObservParamType_id

				outer apply (
					Select top 1 
						ED.EvnDirection_id,
						ED.EvnQueue_id, 
						isnull(ED.Lpu_sid, ED.Lpu_id) Lpu_id,
						ED.EvnDirection_Num,
						ED.EvnDirection_IsAuto,
						ED.LpuSection_did,
						ED.LpuUnit_did,
						ED.Lpu_did,
						ED.MedService_id,
						ED.LpuSectionProfile_id,
						DirType_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
				) ED
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate from v_EvnQueue EQ with (nolock) where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					and EQ.EvnQueue_failDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate from v_EvnQueue EQ with (nolock) 
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				--left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)

				outer apply (
					select top 1 EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga with (nolock)
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
				) EU
				outer apply (
					select top 1 EvnDrug_id, EvnDrug_setDT from v_EvnDrug with (nolock)
					where EP.EvnPrescr_IsExec = 2 and EP.PrescriptionType_id = 5 and EvnPrescr_id = EP.EvnPrescr_id
				) EDr

				{$add_join}
			where
				EP.EvnPrescr_pid {$ep_where_cause}
				and coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id) != 3
				and coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT) is not null
				{$add_where_cause}
			order by
				EP.PrescriptionType_id,
				EPT.EvnCourse_id,
				EPPR.EvnCourse_id,
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT,
				Regime.EvnPrescrRegime_setDT,
				Diet.EvnPrescrDiet_setDT,
				Obs.EvnPrescrObserv_setDT
			

		";
		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Получение данных для комбика "Назначение"
	 */
	function doLoadEvnPrescrCombo($data) {
		$params = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
		);
		$add_where_cause = 'EP.EvnPrescr_pid = :EvnPrescr_pid
				and EP.PrescriptionStatusType_id != 3 ';
		if (!empty($data['PrescriptionType_Code']) && in_array($data['PrescriptionType_Code'], array(5,7))) {
			$params['PrescriptionType_id'] = $data['PrescriptionType_Code'];
			$add_where_cause .= 'and EP.PrescriptionType_id = :PrescriptionType_id ';
		} else {
			//только для общих услуг
			$add_where_cause .= 'and EP.PrescriptionType_id in (6,11,12,13) ';
		}

		if (!empty($data['withoutEvnDirection'])) {
			//те назначения, на которые выписано направление, не отображаются
			$add_where_cause .= 'and not exists (select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd with (nolock)
					where EP.EvnPrescr_id = epd.EvnPrescr_id) ';
		}

		$join_uc = 'left join ';
		if (!empty($data['UslugaComplex_2011id'])) {
			// только назначения, совпадающие по ИД услуги или по коду ГОСТ-11
			$join_uc = 'inner join ';
			$params['UslugaComplex_2011id'] = $data['UslugaComplex_2011id'];
			$add_where_cause .= 'and UC.UslugaComplex_2011id = :UslugaComplex_2011id ';
		}
		if (in_array($data['PrescriptionType_Code'], array(6,7,11,12,13))) {
			$select_sp = ",UC.UslugaComplex_2011id
				,UC.UslugaComplex_Name
				";
		} else {
			$select_sp = ",null as UslugaComplex_2011id
				,null as UslugaComplex_Name
				";
		}
		switch ($data['PrescriptionType_Code']) {
			case 5:
				$join_sp = 'inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id';
				$select_sp .= ",EPTD.EvnPrescrTreatDrug_id
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,(select count(EvnPrescrTreatDrug_id) from v_EvnPrescrTreatDrug with (nolock) where EvnPrescrTreat_id = EP.EvnPrescr_id) as cntDrug
				,null as TableUsluga_id
				,0 as cntUsluga";
				break;
			case 6:
				$join_sp = 'inner join v_EvnPrescrProc TableUsluga with (nolock) on TableUsluga.EvnPrescrProc_id = EP.EvnPrescr_id
				' . $join_uc . 'v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = TableUsluga.UslugaComplex_id';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				,TableUsluga.EvnPrescrProc_id as TableUsluga_id
				,1 as cntUsluga";
				break;
			case 7:
				$join_sp = 'inner join v_EvnPrescrOperUsluga TableUsluga with (nolock) on TableUsluga.EvnPrescrOper_id = EP.EvnPrescr_id
				' . $join_uc . 'v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = TableUsluga.UslugaComplex_id';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				--6,7,12
				,TableUsluga.EvnPrescrOperUsluga_id as TableUsluga_id
				,(select count(EvnPrescrOperUsluga_id)
				from v_EvnPrescrOperUsluga with (nolock)
				where EvnPrescrOper_id = EP.EvnPrescr_id) as cntUsluga";
				break;
			case 11:
				$join_sp = 'inner join v_EvnPrescrLabDiag TableUsluga with (nolock) on TableUsluga.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				' . $join_uc . 'v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = TableUsluga.UslugaComplex_id';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				,TableUsluga.EvnPrescrLabDiag_id as TableUsluga_id
				,1 as cntUsluga";
				break;
			case 12:
				$join_sp = 'inner join v_EvnPrescrFuncDiagUsluga TableUsluga with (nolock) on TableUsluga.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				' . $join_uc . 'v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = TableUsluga.UslugaComplex_id';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				,TableUsluga.EvnPrescrFuncDiagUsluga_id as TableUsluga_id
				,(select count(EvnPrescrFuncDiagUsluga_id)
				from v_EvnPrescrFuncDiagUsluga with (nolock)
				where EvnPrescrFuncDiag_id = EP.EvnPrescr_id) as cntUsluga";
				break;
			case 13:
				$join_sp = 'inner join v_EvnPrescrConsUsluga TableUsluga with (nolock) on TableUsluga.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				' . $join_uc . 'v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = TableUsluga.UslugaComplex_id';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				,TableUsluga.EvnPrescrConsUsluga_id as TableUsluga_id
				,1 as cntUsluga";
				break;
			default:
				$join_sp = '';
				$select_sp .= ",null as EvnPrescrTreatDrug_id
				,'' as DrugTorg_Name
				,0 as cntDrug
				--6,7,12
				,null as TableUsluga_id
				,0 as cntUsluga";
				break;
		}
		if (empty($data['PrescriptionType_Code'])) {
			$join_sp = 'left join v_EvnPrescrTreatDrug EPTD with (nolock) on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				--left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrOperUsluga with (nolock) where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id
				) EPOU
				--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLD
				--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				) EPFDU
				--left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrConsUsluga with (nolock) where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				) EPCU
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id, EPLD.UslugaComplex_id, EPOU.UslugaComplex_id, EPCU.UslugaComplex_id)';
			$select_sp = "--5
				,EPTD.EvnPrescrTreatDrug_id
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,(select count(EvnPrescrTreatDrug_id) from v_EvnPrescrTreatDrug with (nolock) where EP.PrescriptionType_id = 5 and EvnPrescrTreat_id = EP.EvnPrescr_id) as cntDrug
				--6,7,11,12,13
				,UC.UslugaComplex_2011id
				,UC.UslugaComplex_Name
				--6,7,12
				,coalesce(EPPR.EvnPrescrProc_id, EPOU.EvnPrescrOperUsluga_id, EPLD.EvnPrescrLabDiag_id, EPFDU.EvnPrescrFuncDiagUsluga_id, EPCU.EvnPrescrConsUsluga_id) as TableUsluga_id
				,case
					when EP.PrescriptionType_id = 7 then (select count(EvnPrescrOperUsluga_id) from v_EvnPrescrOperUsluga with (nolock) where EvnPrescrOper_id = EP.EvnPrescr_id)
					when EP.PrescriptionType_id = 12 then (select count(EvnPrescrFuncDiagUsluga_id) from v_EvnPrescrFuncDiagUsluga with (nolock) where EvnPrescrFuncDiag_id = EP.EvnPrescr_id)
					when EP.PrescriptionType_id in (6,11,13) then 1
					else 0
				end as cntUsluga";
		}

		if (!empty($data['EvnPrescr_id'])) {
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$add_where_cause = 'EP.EvnPrescr_id = :EvnPrescr_id ';
		} else {
			$add_where_cause .= 'and isnull(EP.EvnPrescr_isExec,1) = 1 ';
		}

		$query = "
			select
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.Lpu_id
				,convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate
				,EP.PrescriptionStatusType_id
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Code
				,PT.PrescriptionType_Name
				{$select_sp}
			from v_EvnPrescr EP with (nolock)
				inner join PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				{$join_sp}
			where
				{$add_where_cause}
			order by
				EP.PrescriptionType_id,
				EP.EvnPrescr_id
		";
		//echo getDebugSQL($query,  $params); exit;

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$tmp = $result->result('array');
		$response = array();
		$last_id = 0;
		$cnt = 0;
		foreach($tmp as $row) {
			$record = array(
				'EvnPrescr_id' => $row['EvnPrescr_id'],
				'Lpu_id' => $row['Lpu_id'],
				'PrescriptionType_id' => $row['PrescriptionType_id'],
				'PrescriptionType_Name' => $row['PrescriptionType_Name'],
				'PrescriptionType_Code' => $row['PrescriptionType_Code'],
				'EvnPrescr_pid' => $row['EvnPrescr_pid'],
				'UslugaComplex_2011id' => $row['UslugaComplex_2011id'],
				'EvnPrescr_setDate' => $row['EvnPrescr_setDate'],
				'EvnPrescr_Text' => '',
			);
			
			switch($row['PrescriptionType_id']) {
				case 5:
					if ($row['EvnPrescr_id'] != $last_id) {
						$last_id = $row['EvnPrescr_id'];
						$tmp_arr = array();
						$cnt = 0;
					}
					$cnt++;
					$tmp_arr[$row['EvnPrescrTreatDrug_id']] = $row['DrugTorg_Name'];
					if ($row['cntDrug'] == $cnt) {
						$record['EvnPrescr_Text'] = implode(', ', $tmp_arr);
						$response[] = $record;
					}
					break;
				case 7:
				case 12:
					if(empty($row['TableUsluga_id']))$row['TableUsluga_id']=$row['EvnPrescr_id'];
					if ($row['EvnPrescr_id'] != $last_id) {
						$last_id = $row['EvnPrescr_id'];
						$tmp_arr = array();
						$cnt = 0;
					}
					$cnt++;
					$tmp_arr[$row['TableUsluga_id']] = $row['UslugaComplex_Name'];
					//if ($row['cntUsluga'] == $cnt) {
						$record['EvnPrescr_Text'] = implode(', ', $tmp_arr);
						$response[] = $record;
					//}
					break;
				default:
					$record['EvnPrescr_Text'] = $row['UslugaComplex_Name'];
					$response[] = $record;
					break;
			}
		}
		return $response;
	}

	/**
	 * Получение данных для шаблона печати "Лист врачебных назначений"
	 * Отображается список курсов или назначений и календарь с отметками об исполнении врачом или сестрой
	 * Имя шаблона: print_evnprescr_list
	 */
	/*function doloadEvnPrescrDoctorList($data) {
		$queryParams = array('Evn_pid'=>$data['Evn_pid']);
		//получить данные по учетному документу ФИО, Название МО, Отделение, лечащий врач.
		//получаем первую дату назначения
		$query = "
			select top 1
				PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_FIO
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,Lpu.Lpu_Name
				,LS.LpuSection_Code
				,LS.LpuSection_Name
				,MP.Person_Fio as MedPersonal_Fio
				,cast(cast(isnull(EP.EvnPrescr_setDT, evn.Evn_setDT) as date) as varchar(10)) as EvnPrescr_date --2014-01-23
				,evn.EvnClass_SysNick
				,PEH.PersonEncrypHIV_Encryp
				,case
					when evn.EvnClass_SysNick = 'EvnVizitPL' OR  evn.EvnClass_SysNick = 'EvnVizitPLStom' then EPLPID.EvnPL_NumCard
					else EPSPID.EvnPS_NumCard
				end as NumCard
			from
				v_Evn evn with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = evn.Person_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = evn.Lpu_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = evn.Evn_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = evn.Evn_id
				left join v_EvnVizitPL EV with (nolock) on EV.EvnVizitPL_id = evn.Evn_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(EPS.LpuSection_pid, ES.LpuSection_id, EV.LpuSection_id)
				left join v_MedPersonal MP with (nolock) on MP.Lpu_id = evn.Lpu_id and MP.MedPersonal_id = coalesce(EPS.MedPersonal_pid, ES.MedPersonal_id, EV.MedPersonal_id)
				outer apply (
					select top 1
						coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescr_setDT
					from v_EvnPrescr EP with (nolock)
					left join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
						and Regime.PrescriptionStatusType_id != 3
					left join v_EvnPrescrDiet Diet with (nolock) on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
						and Diet.PrescriptionStatusType_id != 3
					left join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
						and Obs.PrescriptionStatusType_id != 3
					where EP.EvnPrescr_pid = evn.Evn_id -- and EP.EvnPrescr_setDT is not null
					order by coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)
				) EP
				outer apply(
					SELECT
						EvnPS_NumCard
					FROM v_EvnPS WHERE EvnPS_id = evn.Evn_pid
				) EPSPID
				outer apply(
					SELECT
						EvnPL_NumCard
					FROM v_EvnPL WHERE EvnPL_id = evn.Evn_pid
				) EPLPID
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
			where
				evn.Evn_id = :Evn_pid
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
		} else {
			throw new Exception('Не удалось запросить данные по учетному документу!',400);
		}
		if (empty($response) || empty($response[0]['Person_FIO'])) {
			throw new Exception('Не удалось получить данные по учетному документу!',400);
		}
		if (empty($response[0]['EvnPrescr_date'])) {
			throw new Exception('Не удалось определить дату первого назначения по учетному документу!',400);
		}
		$parse_data = $response[0];

		$isPolka = (in_array($parse_data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom')));
		$regionNick = $data['session']['region']['nick'];

		$addSelect = '';
		$addJoin = '';
		if ($isPolka) {
			// для полки не нужна разбивка по датам и выборка кто выполнил
		} else {
			// для стационара нужна разбивка по датам и выборка кто назначил, кто выполнил #38401
			$queryParams['EvnPrescr_begDate'] = $response[0]['EvnPrescr_date'];
			$addSelect .= "
				,DATEDIFF(DAY, cast(:EvnPrescr_begDate as datetime), coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)) + 1 as DayNum
				,coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				,substring(ISNULL(PMUP.PMUser_Name, ''),1,1) as pmUserPrescr_Name
				,substring(ISNULL(PMUE.PMUser_Name, ''),1,1) as pmUserExec_Name
				,ISNULL(PMUE.PMUser_surName, '') + ' '+ substring(ISNULL(PMUE.PMUser_FirName, ''),1,1) + '.' + substring(ISNULL(PMUE.PMUser_SecName, ''),1,1) + '.' as pmUserExec_FIO";

			$addJoin .= "
				left join v_pmUser PMUP with (nolock) on PMUP.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
				left join v_pmUserCache PMUE with (nolock) on PMUE.pmUser_id = coalesce(Regime.pmUser_updID, Diet.pmUser_updID, Obs.pmUser_updID, EP.pmUser_updID)";

		}


		if ($regionNick == 'ufa') {
			// выбираем латинские наименования препаратов
			$addSelect .= "
				,coalesce(AM.LATNAME, dcm.DrugComplexMnn_LatName, '') as Drug_Name";
		} else {
			// отображаем торговое наименования препаратов на русском
			$addSelect .= "
				,'' as Drug_Name";

		}


		// получаем данные назначений
		$query = "
			select
			    --общие атрибуты назначения в конкретный день
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id
				,PST.PrescriptionStatusType_id
				--чтобы отобразить в одной строке одно назначение-курс
				,coalesce(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id,EP.EvnPrescr_id) as EvnCoursePrescr_id
				,EP.EvnPrescr_Descr
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Name
				--1
				,ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name
				--2
				,ISNULL(PDT.PrescriptionDietType_Code, '') as PrescriptionDietType_Code
				,ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name
				--5
				,ECTD.EvnCourseTreatDrug_id
				,LTRIM(STR(ECTD.EvnCourseTreatDrug_KolvoEd, 10, 2)) as KolvoEd
				,ISNULL(df.NAME,Drug.DrugForm_Name) as DrugForm_Name
				,LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 2)) as Kolvo
				,isnull(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as Okei_NationSymbol
				,coalesce(dcm.DrugComplexMnn_RusName, Drug.DrugTorg_Name, MnnName.DrugComplexMnnName_Name, '') as DrugTorg_Name
				--5,6 параметры графика
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, '') as CountInDay
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, '') as CourseDuration
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, '') as ContReception
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, '') as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
				--6,7,11,12,13 услуги
				,coalesce(EPPR.EvnPrescrProc_id,EPOU.EvnPrescrOperUsluga_id, EPLDU.EvnPrescrLabDiagUsluga_id, EPFDU.EvnPrescrFuncDiagUsluga_id,EPCU.EvnPrescrConsUsluga_id,0) as TableUsluga_id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,PUC.UslugaComplex_Code as UslugaComplexP_Code
				,PUC.UslugaComplex_Name as UslugaComplexP_Name
				--10
				,ISNULL(OTT.ObservTimeType_id, 0) as ObservTimeType_id
				,ISNULL(OTT.ObservTimeType_Name, '') as ObservTimeType_Name
				--Параметры наблюдения
				,EPOP.EvnPrescrObservPos_id
				,ISNULL(OPT.ObservParamType_Name, '') as ObservParamType_Name
				{$addSelect}
			from
				v_EvnPrescr EP with (nolock)
				inner join v_PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				--1
				left join v_EvnPrescrRegime Regime with (nolock) on EP.PrescriptionType_id = 1
					and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
					and Regime.PrescriptionStatusType_id != 3
				left join v_PrescriptionRegimeType PRT with (nolock) on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				--2
				left join v_EvnPrescrDiet Diet with (nolock) on EP.PrescriptionType_id = 2
					and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
					and Diet.PrescriptionStatusType_id != 3
				left join v_PrescriptionDietType PDT with (nolock) on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				--10
				left join v_EvnPrescrObserv Obs with (nolock) on EP.PrescriptionType_id = 10
					and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
					and Obs.PrescriptionStatusType_id != 3
				--5
				left join v_EvnPrescrTreat Treat with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join rls.MASSUNITS ep_mu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = ECTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = isnull(ECTD.DrugComplexMnn_id, Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS AM with (nolock) on AM.ACTMATTERS_ID = MnnName.ActMatters_id
				--6
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--6,7,11,12,13
				left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrLabDiagUsluga EPLDU with (nolock) on EP.PrescriptionType_id = 11 and EPLDU.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLDU.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				left join v_UslugaComplex PUC with (nolock) on EP.PrescriptionType_id = 11 and PUC.UslugaComplex_id = EPLD.UslugaComplex_id
				--10
				left join ObservTimeType OTT with (nolock) on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP with (nolock) on EP.PrescriptionType_id = 10 and EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT with (nolock) on EP.PrescriptionType_id = 10 and OPT.ObservParamType_id = EPOP.ObservParamType_id

				left join PrescriptionStatusType PST with (nolock) on PST.PrescriptionStatusType_id = coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id)
				{$addJoin}
			where
				EP.EvnPrescr_pid = :Evn_pid
				and coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,ECTD.EvnCourseTreatDrug_id,Obs.EvnPrescrObserv_id,UC.UslugaComplex_id,PUC.UslugaComplex_id) is not null
			order by
				PT.PrescriptionType_id,
				isnull(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id),
				EP.EvnPrescr_id,
				coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT),
				Obs.EvnPrescrObserv_id
		";

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось запросить данные назначений!',400);
		}
		$response = $result->result('array');
		echo '<pre>';
		print_r($response);
		echo '</pre>';
		//обработка выборки
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$tmp_arr = array();
		foreach ($response as $row) {
			$type_id = $row['PrescriptionType_id'];
			$row_id = $row['EvnCoursePrescr_id'];
			$day_id = $row['EvnPrescrDay_id'];

			if (empty($tmp_arr[$type_id])) {
				//выбираем данные типа назначения
				$tmp_arr[$type_id] = array(
					'PrescriptionType_Name'=>$row['PrescriptionType_Name'],
					'rows'=>array(),
				);
			}

			if (empty($tmp_arr[$type_id]['rows'][$row_id])) {
				//выбираем данные для отображения в строке
				$tmp_arr[$type_id]['rows'][$row_id] = array(
					'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
					//''=>$row[''],
					'days'=>array(),
				);
			}

			if (!$isPolka && empty($tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id])) {
				//выбираем данные для отображения в ячейке дня
				$tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id] = array(
					'DayNum'=>$row['DayNum'],
					//'EvnPrescrDay_setDate'=>$row['EvnPrescrDay_setDate'],
					'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
					'pmUserExec_Name'=>$row['pmUserExec_Name'],
					'pmUserExec_FIO'=>$row['pmUserExec_FIO'],
					'pmUserPrescr_Name'=>$row['pmUserPrescr_Name'],
					'PrescriptionStatusType_id'=>$row['PrescriptionStatusType_id'],
				);
			}

			switch($type_id) {
				case 1;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionRegimeType_Name'] = $row['PrescriptionRegimeType_Name'];
					break;
				case 2;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Code'] = $row['PrescriptionDietType_Code'];
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Name'] = $row['PrescriptionDietType_Name'];
					break;
				case 10;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList']) ) {
						//Параметры наблюдения
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
					}

					break;
				case 5;
					$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
					$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList']) ) {
						//медикаменты
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']] = array(
							'Drug_Name'=>$row['Drug_Name'],
							'DrugTorg_Name'=>$row['DrugTorg_Name'],
							'DrugForm_Name'=>$row['DrugForm_Name'],
							'KolvoEd'=>$row['KolvoEd'],
							'Kolvo'=>$row['Kolvo'],
							'Okei_NationSymbol'=>$row['Okei_NationSymbol'],
						);
					}
					break;
				case 6;
				case 7;
				case 11;
				case 12;
				case 13;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList']) ) {
						//услуги(а)
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'] = array();
					}
					if (6 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
						$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					} else {
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					}
					if (11 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Code'] = $row['UslugaComplexP_Code'];
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Name'] = $row['UslugaComplexP_Name'];
					}
					break;
			}
		}
		//var_dump($tmp_arr); exit;

		//дальше собираем данные для отображения
		$parse_data['ep_list'] = array();
		$defRow = array(
			'EvnPrescr_Name'=>null,
			//EvnPrescr_Day{[0-9]+} // содержание ячейки строки сестра
			//EvnPrescr_Day{[0-9]+}S // содержание ячейки строки сестра
		);
		if ($isPolka) {
			$lastRow = array();
		} else {
			$lastRow = array(
				'max_day' => 0,
				'EvnPrescr_begDate' => $queryParams['EvnPrescr_begDate']
			);
		}
		foreach ($tmp_arr as $type_id => $type_data) {
			foreach ($type_data['rows'] as $row_data ) {
				$ep_data = $defRow;

				// заполняем данными ячейки дня строки
				foreach ($row_data['days'] as $day_data ) {
					if($day_data['DayNum'] > $lastRow['max_day'])
					{
						$lastRow['max_day'] = $day_data['DayNum'];
					}
					$caption = 'EvnPrescr_Day'. $day_data['DayNum'];
					$ep_data[$caption] = $day_data['pmUserPrescr_Name'];
					$ep_data[$caption.'S'] = '';
					if($day_data['EvnPrescr_IsExec'] == 2) {
						$ep_data[$caption.'S'] = $day_data['pmUserExec_Name'];
						$ep_data[$caption.'S_FIO'] = $day_data['pmUserExec_FIO'];
					}
				}

				//формируем столбец EvnPrescr_Name
				$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'. $type_data['PrescriptionType_Name'] .'</div>';
				switch($type_id) {
					case 1;
						$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionRegimeType_Name'] .' режим</div>';
						break;
					case 2;
						if ($regionNick == 'ufa') {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionDietType_Name'] .'</div>';
						} else {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">Диета №'.$row_data['PrescriptionDietType_Code'] .'</div>';
						}
						break;
					case 10;
						$ep_data['EvnPrescr_Name'] .= "<div style='text-decoration: underline;'>Параметры наблюдения:</div>";
						$i = 1;
						foreach ( $row_data['ParamTypeList'] as $name ) {
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '<div>';
							}
							$ep_data['EvnPrescr_Name'] .= htmlspecialchars($name) .', ';
							$i++;
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '</div>';
							}
						}
						//$row_data['TimeTypeList']
						break;
					case 5;
						$drug_list = array();
						foreach ( $row_data['DrugList'] as $drug_data ) {
							$name = $drug_data['Drug_Name'];
							if (empty($name)) {
								$name = $drug_data['DrugTorg_Name'];
							}
							$i = '<b>'.$name.'</b>';
							$DrugForm_Nick = $this->EvnPrescrTreat_model->getDrugFormNick($drug_data['DrugForm_Name'], $drug_data['Drug_Name']);
							if ( !empty($drug_data['KolvoEd']))
								$i .=  ' По '. htmlspecialchars($drug_data['KolvoEd']) .' '.(empty($DrugForm_Nick)?'ед.дозировки':$DrugForm_Nick);
							if ( !empty($drug_data['Kolvo']) && empty($drug_data['KolvoEd']) )
								$i .=  htmlspecialchars($drug_data['Kolvo']) .' ';
							if ( !empty($drug_data['Okei_NationSymbol']) && empty($drug_data['KolvoEd']) )
								$i .=  htmlspecialchars($drug_data['Okei_NationSymbol']);
							$drug_list[]=$i;
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode(',<br>',$drug_list);
						if ( !empty($row_data['CountInDay']))
							$ep_data['EvnPrescr_Name'] .=  '<br>'.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
						if ( !empty($row_data['ContReception']))
							$ep_data['EvnPrescr_Name'] .=  ', принимать '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
						if ( !empty($row_data['Interval']))
							$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
						if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
							$ep_data['EvnPrescr_Name'] .=  ', в течение '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
						$ep_data['EvnPrescr_Name'] .=  '.';
						$ep_data['EvnPrescr_Name'] .= '</div>';
						break;
					case 6;
					case 7;
					case 11;
					case 12;
					case 13;
						$usluga_list = array();
						if ($this->options['prescription']['enable_show_service_code']) {
							$usluga_tpl = '{UslugaComplex_Code} {UslugaComplex_Name}';
						} else {
							$usluga_tpl = '{UslugaComplex_Name}';
						}
						if (11 == $type_id) {
							$usluga_list[] = strtr($usluga_tpl, array(
								'{UslugaComplex_Code}'=>$row_data['UslugaComplexP_Code'],
								'{UslugaComplex_Name}'=>$row_data['UslugaComplexP_Name'],
							));
						} else {
							//пока состав лаб.услуги не будем отображать, т.к. это не надо было
							foreach ( $row_data['UslugaList'] as $usluga_data ) {
								$usluga_list[] = strtr($usluga_tpl, array(
									'{UslugaComplex_Code}'=>$usluga_data['UslugaComplex_Code'],
									'{UslugaComplex_Name}'=>$usluga_data['UslugaComplex_Name'],
								));
							}
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode('<br />',$usluga_list);
						if (6 == $type_id) {
							if ( !empty($row_data['CountInDay']))
								$ep_data['EvnPrescr_Name'] .=  ' '.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
							if ( !empty($row_data['ContReception']))
								$ep_data['EvnPrescr_Name'] .=  ', повторять непрерывно '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
							if ( !empty($row_data['Interval']))
								$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
							if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
								$ep_data['EvnPrescr_Name'] .=  ', всего '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
						}
						$ep_data['EvnPrescr_Name'] .=  '.';
						$ep_data['EvnPrescr_Name'] .= '</div>';
						break;
				}
				if ( !empty($row_data['EvnPrescr_Descr']) )
				{
					// картинка в pdf не отображается <img src="/img/icons/comment16.png" />&nbsp;
					
					if ($regionNick != 'ufa') {
						$ep_data['EvnPrescr_Name'] .= '<div>' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
					else {
						$ep_data['EvnPrescr_Name'] .= '<div>'. '<font style="text-decoration: underline;">Комментарий:</font> ' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
				}
				$parse_data['ep_list'][] = $ep_data;
			}
		}
		unset($tmp_arr);
		$parse_data['ep_list'][] = $lastRow;
		//var_dump($parse_data); exit;
		echo '<pre>';
		print_r($parse_data);
		echo '</pre>'; die();
		return $parse_data;
	}*/


}
