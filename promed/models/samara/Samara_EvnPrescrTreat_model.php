<?php

require_once(APPPATH.'models/EvnPrescrTreat_model.php');

class Samara_EvnPrescrTreat_model extends EvnPrescrTreat_model {
	/**
	 * description
	 */
    function __construct() {
		parent::__construct();
    }

	/**
	 * _processingDrugListData
	 */
    private function _processingDrugListData($data, $object='EvnPrescrTreatDrug', $isStrongValidate = true) {
		if (empty($data['DrugListData'])) {
			throw new Exception('Не указаны медикаменты', 400);
			}
		ConvertFromWin1251ToUTF8($data['DrugListData']);
		$arr = json_decode($data['DrugListData'], true);
		if (!is_array($arr) || empty($arr)) {
			throw new Exception('Неправильный формат медикаментов', 400);
		}
		
		foreach ($arr as &$row) {
			array_walk($row, 'ConvertFromUTF8ToWin1251');
			continue; // ipavelpetrov
			if (empty($row['MethodInputDrug_id']) || !in_array($row['MethodInputDrug_id'],array(1,2))) {
				throw new Exception('Не указан тип медикамента', 400);
			}
			if ($row['MethodInputDrug_id'] == 1 && empty($row['DrugComplexMnn_id']) && empty($row['actmatters_id'])) {
				throw new Exception('Не указан МНН медикамента', 400);
		}
			if ($row['MethodInputDrug_id'] == 2 && empty($row['Drug_id'])) {
				throw new Exception('Не указано торговое наименование медикамента', 400);
			}
			if ($row['MethodInputDrug_id'] == 1 && empty($row['DrugComplexMnn_id']) && !empty($row['actmatters_id']))
		{
				$trans_result = $this->getDrugComplexMnnByActMatters(array('ActMatters_id'=>$row['actmatters_id']));
				if(empty($trans_result) || !is_array($trans_result) || (empty($trans_result[0]['DrugComplexMnn_id']) && empty($trans_result[0]['Error_Msg']))) {
					throw new Exception('Не найден МНН по действующему веществу', 400);
				} else if(!empty($trans_result[0]['Error_Msg'])) {
					throw new Exception($trans_result[0]['Error_Msg'], 500);
				} else if(!empty($trans_result[0]['DrugComplexMnn_id'])) {
					$row['DrugComplexMnn_id'] = $trans_result[0]['DrugComplexMnn_id'];
				}
				$isStrongValidate = false;//это назначение по стандарту
			}
			if (!empty($row['EdUnits_id'])) {
				$parts = explode('_', $row['EdUnits_id']);
				if (in_array($parts[0], array('CUBICUNITS', 'MASSUNITS')) && !empty($parts[1]) && is_numeric($parts[1])) {
					$row[$parts[0].'_id'] = $parts[1];
					}
				}
			if ($isStrongValidate && empty($row['CUBICUNITS_id']) && empty($row['MASSUNITS_id'])) {
				throw new Exception('Не указана единица измерения', 400);
			}
			if ($isStrongValidate && empty($row['Kolvo'])) {
				throw new Exception('Не указано кол-во ед. измерения', 400);
			}
			if ($isStrongValidate && empty($row['KolvoEd'])) {
				throw new Exception('Не указано кол-во ед. измерения', 400);
			}
			if ($isStrongValidate && empty($row['DoseDay'])) {
				throw new Exception('Не указана дневная доза', 400);
			}
			if ($isStrongValidate && 'EvnCourseTreatDrug'==$object && empty($row['PrescrDose'])) {
				throw new Exception('Не указана курсовая доза', 400);
			}
			if (empty($row['status'])) {
				$row['status'] = 'saved';
			}
		}
		return $arr;
	}
	
	/**
	 * doSaveEvnCourseTreat
	 */
    public function doSaveEvnCourseTreat($data) {
		$isStac = in_array($data['parentEvnClass_SysNick'],array('EvnSection','EvnPS'));
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			if (empty($data['Lpu_id'])) {
				throw new Exception('Неправильный массив параметров', 500);
			}
			$data['DrugListData'] = $this->_processingDrugListData($data, 'EvnCourseTreatDrug');
			$allow_graf_create = false;
			if (
				!empty($data['EvnCourseTreat_setDate']) &&
				!empty($data['EvnCourseTreat_Duration']) &&
				!empty($data['EvnCourseTreat_ContReception']) &&
				!empty($data['DurationType_id']) &&
				!empty($data['DurationType_recid'])
			) {
				$allow_graf_create = true;
			}
			$data['EvnCourseTreat_setDT'] = empty($data['EvnCourseTreat_setDate']) ? NULL : $data['EvnCourseTreat_setDate'];
			$countInDay = empty($data['EvnCourseTreat_CountDay']) ? 1 : $data['EvnCourseTreat_CountDay'];
			$date_list = array($data['EvnCourseTreat_setDT']);
			if ($allow_graf_create) {
				$courseDuration = $data['EvnCourseTreat_Duration']*$this->_getNumDay($data['DurationType_id']);
				$contReception = $data['EvnCourseTreat_ContReception']*$this->_getNumDay($data['DurationType_recid']);
				$interval = empty($data['EvnCourseTreat_Interval']) ? 0 : $data['EvnCourseTreat_Interval'];
				$interval *= $this->_getNumDay($data['DurationType_intid']);
				$day = 1;
				$count_cont = 1;
				$course_begin = strtotime($data['EvnCourseTreat_setDT']);
				for($i=1; $i<$courseDuration; $i++)
				{
					if (empty($interval)) {
						//Непрерывный прием
						$new_date = date('Y-m-d',strtotime('+'.$day.' day', $course_begin));
						$date_list[] = $new_date;
						$day++;
					} else if(!empty($contReception)) {
						//прием с перерывами между числом дней непрерывного приема
						if($contReception == $count_cont)
						{
							$count_cont = 0;
							$day += $interval;
							$i--;
						}
						else
						{
							$new_date = date('Y-m-d',strtotime('+'.$day.' day', $course_begin));
							$date_list[] = $new_date;
							$count_cont++;
							$day++;
						}
					}
				}
				//var_dump($date_list); exit;
				$data['EvnCourseTreat_PrescrCount'] = count($date_list)*$countInDay;
			} else {
				if (empty($data['EvnCourseTreat_Duration'])) {
					$courseDuration = 1;
				} else {
					$courseDuration = $data['EvnCourseTreat_Duration']*$this->_getNumDay($data['DurationType_id']);
				}
				$data['EvnCourseTreat_PrescrCount'] = $courseDuration*$countInDay;
			}

			if (empty($data['EvnCourseTreat_id'])) {
				//нужно создавать курс с назначениями
				$data['EvnCourseTreat_id'] = NULL;
				$data['EvnCourseTreat_FactCount'] = 0;
				$data['EvnCourseTreat_MaxCountDay'] = $countInDay;
				$data['EvnCourseTreat_MinCountDay'] = $countInDay;
				$response = $this->_saveEvnCourseTreat($data);
				$data['EvnCourseTreat_id'] = $response[0]['EvnCourseTreat_id'];

				$data['EvnPrescrTreat_id'] = NULL;
				$data['EvnCourse_id'] = $response[0]['EvnCourseTreat_id'];
				$data['EvnPrescrTreat_PrescrCount'] = $countInDay;
				$data['EvnPrescrTreat_pid'] = $data['EvnCourseTreat_pid'];
				if ($isStac) {
					foreach($date_list as $i => $d)
					{
						//создаем назначения
						$data['EvnPrescrTreat_setDate'] = $d;
						$res = $this->_save($data);
						$response[0]['EvnPrescrTreat_id'.$i] = $res[0]['EvnPrescrTreat_id'];
					}
				} else {
					//создаем назначение
					$data['EvnPrescrTreat_setDate'] = $date_list[0];
					$res = $this->_save($data);
					$response[0]['EvnPrescrTreat_id0'] = $res[0]['EvnPrescrTreat_id'];
				}
			} else {
				//обновление курсa
				$o_data = $this->getAllData($data['EvnCourseTreat_id'], 'EvnCourseTreat');
				if (!empty($o_data['Error_Msg'])) {
					throw new Exception($o_data['Error_Msg'], 500);
				}
				//получаем список назначений
				$queryParams = array(
					'EvnCourse_id' => $data['EvnCourseTreat_id'],
				);
				//@todo если джойнить v_EvnDrug ED то отмененные события списания не дадут отменить назначение
				//поэтому сделал так: EvnDrug ED
				$query = "
					select
						EPT.EvnPrescrTreat_id,
						EPT.PrescriptionStatusType_id,
						EPT.EvnPrescrTreat_setDT,
						EDr.EvnDrug_id,
						EPT.EvnPrescrTreat_FactCount,
						EPT.EvnPrescrTreat_IsExec
					from
						v_EvnPrescrTreat EPT with (nolock)
						outer apply (
							select top 1 ED.EvnDrug_id from v_EvnPrescrTreatDrug EPTD
							inner join EvnDrug ED with (nolock) on ED.EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id
								OR ED.EvnPrescr_id = EPT.EvnPrescrTreat_id
							where EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
						) EDr
					where
						EPT.EvnCourse_id = :EvnCourse_id
				";
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
				}
				$ep_list = $result->result('array');
				foreach ($ep_list as $row) {
					if (is_object($row['EvnPrescrTreat_setDT'])) {
						if ($row['EvnPrescrTreat_setDT'] instanceof DateTime) {
							/**
							 * @var DateTime $var
							 */
							$var = $row['EvnPrescrTreat_setDT'];
							$row['EvnPrescrTreat_setDT'] = $var->format('Y-m-d');
						}
					}
					if (2 == $row['EvnPrescrTreat_IsExec']
						|| 2 == $row['PrescriptionStatusType_id']
						|| !empty($row['EvnDrug_id'])
						|| !empty($row['EvnPrescrTreat_FactCount'])
					) {
						//выполненные назначения оставляем как есть,
						$isAllowCancel = false;
					} else {
						$isAllowCancel = true;
					}
					if ($isAllowCancel) {
						//отменяем назначение
						$this->_destroy(array(
							'object'=>$this->getTableName(),
							'id'=>$row['EvnPrescrTreat_id'],
							'pmUser_id'=>$data['pmUser_id'],
						));
					} else {
						if (in_array($row['EvnPrescrTreat_setDT'], $date_list)) {
							foreach ($date_list as $i => $d) {
								if ($d == $row['EvnPrescrTreat_setDT']) {
									unset($date_list[$i]);
								}
							}
						} else {
							// если они не вписываются в график,
							//то пересчитываем продолжительность, курсовую дозу?
							$data['EvnCourseTreat_PrescrCount']++;
						}
					}
				}

				$data['EvnCourseTreat_MaxCountDay'] = $o_data['EvnCourseTreat_MaxCountDay'];
				$data['EvnCourseTreat_MinCountDay'] = $o_data['EvnCourseTreat_MinCountDay'];
				if ( $data['EvnCourseTreat_MinCountDay'] > $countInDay ) {
					// надо обновить минимальное число приемов в сутки
					$data['EvnCourseTreat_MinCountDay'] = $countInDay;
				}
				if ( $data['EvnCourseTreat_MaxCountDay'] < $countInDay ) {
					// надо обновить максимальное число приемов в сутки
					$data['EvnCourseTreat_MaxCountDay'] = $countInDay;
				}
				$data['EvnCourseTreat_FactCount'] = $o_data['EvnCourseTreat_FactCount'];
				$response = $this->_saveEvnCourseTreat($data);
				$data['EvnCourseTreat_id'] = $response[0]['EvnCourseTreat_id'];


				$data['EvnPrescrTreat_id'] = NULL;
				$data['EvnCourse_id'] = $response[0]['EvnCourseTreat_id'];
				$data['EvnPrescrTreat_PrescrCount'] = $countInDay;
				$data['EvnPrescrTreat_pid'] = $data['EvnCourseTreat_pid'];
				if ($isStac) {
					foreach($date_list as $i => $d)
					{
						//создаем назначения
						$data['EvnPrescrTreat_setDate'] = $d;
						$res = $this->_save($data);
						$response[0]['EvnPrescrTreat_id'.$i] = $res[0]['EvnPrescrTreat_id'];
					}
				} else if (!empty($date_list)) {
					//создаем назначение
					$data['EvnPrescrTreat_setDate'] = $date_list[0];
					$res = $this->_save($data);
					$response[0]['EvnPrescrTreat_id0'] = $res[0]['EvnPrescrTreat_id'];
				}
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		$this->commitTransaction();
		return $response;
	}
	
	/**
	 * Сохранение данных курса 	 
	 */	
	protected function _saveEvnCourseTreat(&$data) {		
		if (empty($data['EvnCourseTreat_pid'])) {
			throw new Exception('Не указано посещение или движение, на котором создан курс', 400);
		}
		if (empty($data['MedPersonal_id'])) {
			throw new Exception('Не указан врач, создавший курс', 400);
		}
		if (empty($data['LpuSection_id'])) {
			throw new Exception('Не указано отделение', 400);
		}
		if (empty($data['Lpu_id'])) {
			throw new Exception('Не указана МО', 400);
		}
		if (empty($data['PersonEvn_id'])) {
			throw new Exception('Не указан человек', 400);
		}
		if (empty($data['EvnCourseTreat_id'])) {
			$action = 'ins';
			$data['EvnCourseTreat_id'] = NULL;
			$data['EvnCourseTreat_FactCount'] = 0;
		} else {
			$action = 'upd';
		}
		$query = "
			declare
			@Res bigint,
			@pmUser_id bigint,
			@ErrCode int,
			@ErrMessage varchar(4000);

			set @Res = :EvnCourseTreat_id;

			exec p_EvnCourseTreat_{$action}
			   @EvnCourseTreat_id = @Res OUTPUT
			  ,@EvnCourseTreat_pid = :EvnCourseTreat_pid --Посещение или движение, на котором создан курс
			  ,@Lpu_id = :Lpu_id
			  ,@Server_id = :Server_id
			  ,@PersonEvn_id = :PersonEvn_id
			  ,@EvnCourseTreat_setDT = :EvnCourseTreat_setDT
			  ,@EvnCourseTreat_disDT = :EvnCourseTreat_disDT
			  ,@Morbus_id = :Morbus_id
			  ,@MedPersonal_id = :MedPersonal_id --Врач, создавший курс
			  ,@LpuSection_id = :LpuSection_id
			  ,@CourseType_id = :CourseType_id
			  ,@EvnCourseTreat_MinCountDay = :EvnCourseTreat_MinCountDay
			  ,@EvnCourseTreat_MaxCountDay = :EvnCourseTreat_MaxCountDay
			  ,@EvnCourseTreat_ContReception = :EvnCourseTreat_ContReception
			  ,@DurationType_recid = :DurationType_recid
			  ,@EvnCourseTreat_Interval = :EvnCourseTreat_Interval
			  ,@DurationType_intid = :DurationType_intid
			  ,@EvnCourseTreat_Duration = :EvnCourseTreat_Duration
			  ,@EvnCourseTreat_PrescrCount = :EvnCourseTreat_PrescrCount
			  ,@DurationType_id = :DurationType_id
			  ,@EvnCourseTreat_FactCount = :EvnCourseTreat_FactCount
			  ,@ResultDesease_id = :ResultDesease_id
			  ,@PerformanceType_id = :PerformanceType_id
			  ,@PrescriptionIntroType_id = :PrescriptionIntroType_id
			  ,@PrescriptionTreatType_id = :PrescriptionTreatType_id
			  ,@PrescriptionTimeType_id = :PrescriptionTimeType_id
			  ,@PrescriptionTreatOrderType_id = :PrescriptionTreatOrderType_id
              ,@EvnCourseTreat_IsPrescrInfusion = :EvnCourseTreat_IsPrescrInfusion
			  ,@pmUser_id = :pmUser_id
			  ,@Error_Code = @ErrCode OUTPUT
			  ,@Error_Message = @ErrMessage OUTPUT;

			select @Res as EvnCourseTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$data['EvnCourseTreat_setDT'] = empty($data['EvnCourseTreat_setDT']) ? NULL : $data['EvnCourseTreat_setDT'];
		$data['EvnCourseTreat_disDT'] = empty($data['EvnCourseTreat_disDT']) ? NULL : $data['EvnCourseTreat_disDT'];
		$data['ResultDesease_id'] = empty($data['ResultDesease_id']) ? NULL : $data['ResultDesease_id'];
		$data['EvnCourseTreat_FactCount'] = empty($data['EvnCourseTreat_FactCount']) ? 0 : $data['EvnCourseTreat_FactCount'];
		$data['DurationType_id'] = empty($data['DurationType_id']) ? NULL : $data['DurationType_id'];
		$data['EvnCourseTreat_Duration'] = empty($data['EvnCourseTreat_Duration']) ? NULL : $data['EvnCourseTreat_Duration'];
		$data['EvnCourseTreat_PrescrCount'] = empty($data['EvnCourseTreat_PrescrCount']) ? NULL : $data['EvnCourseTreat_PrescrCount'];
		$data['DurationType_intid'] = empty($data['DurationType_intid']) ? NULL : $data['DurationType_intid'];
		$data['EvnCourseTreat_Interval'] = empty($data['EvnCourseTreat_Interval']) ? NULL : $data['EvnCourseTreat_Interval'];
		$data['DurationType_recid'] = empty($data['DurationType_recid']) ? NULL : $data['DurationType_recid'];
		$data['EvnCourseTreat_ContReception'] = empty($data['EvnCourseTreat_ContReception']) ? NULL : $data['EvnCourseTreat_ContReception'];
		$data['EvnCourseTreat_MaxCountDay'] = empty($data['EvnCourseTreat_MaxCountDay']) ? NULL : $data['EvnCourseTreat_MaxCountDay'];
		$data['EvnCourseTreat_MinCountDay'] = empty($data['EvnCourseTreat_MinCountDay']) ? NULL : $data['EvnCourseTreat_MinCountDay'];
		$data['Morbus_id'] = empty($data['Morbus_id']) ? NULL : $data['Morbus_id'];
		$data['CourseType_id'] = $this->getCourseTypeId();
		$data['PrescriptionIntroType_id'] = empty($data['PrescriptionIntroType_id']) ? NULL : $data['PrescriptionIntroType_id'];
		$data['PerformanceType_id'] = empty($data['PerformanceType_id']) ? NULL : $data['PerformanceType_id'];
		$data['PrescriptionTreatType_id'] = empty($data['PrescriptionTreatType_id']) ? 2 : $data['PrescriptionTreatType_id'];
		$data['PrescriptionTimeType_id'] = empty($data['PrescriptionTimeType_id']) ? NULL : $data['PrescriptionTimeType_id'];
		$data['PrescriptionTreatOrderType_id'] = empty($data['PrescriptionTreatOrderType_id']) ? NULL : $data['PrescriptionTreatOrderType_id'];
        $data['EvnCourseTreat_IsPrescrInfusion'] = empty($data['EvnCourseTreat_IsPrescrInfusion']) ? NULL : $data['EvnCourseTreat_IsPrescrInfusion'];

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении курса', 500);
		}
		$trans_result = $result->result('array');
		if(!empty($trans_result[0]['Error_Msg'])) {
			throw new Exception($trans_result[0]['Error_Msg'], 500);
		}

		$data['EvnCourseTreat_id'] = $trans_result[0]['EvnCourseTreat_id'];
		if (!empty($data['DrugListData']) && is_array($data['DrugListData'])) {
			foreach ($data['DrugListData'] as $i => $row) {
				switch ($row['status']) {
					case 'saved': // сохранено и не изменено
						$trans_result[0]['EvnCourseTreatDrug_id'.$i.'_nosaved'] = $row['id'];
						$data['DrugListData'][$i]['id'] = null;
						$data['DrugListData'][$i]['status'] = 'new';
						break;
					case 'deleted':
						$this->_destroy(array(
							'object'=>'EvnCourseTreatDrug',
							'id'=>$row['id'],
						));
						$trans_result[0]['EvnCourseTreatDrug_id'.$i.'_deleted'] = $row['id'];
						unset($data['DrugListData'][$i]);
						break;
					default:
						$res = $this->_saveEvnCourseTreatDrug($data, $row);
						if(empty($res)) {
							throw new Exception('Ошибка при сохранении медикамента', 500);
						}
						if (!empty($res[0]['Error_Msg'])) {
							throw new Exception($res[0]['Error_Msg'], 500);
						}
						$trans_result[0]['EvnCourseTreatDrug_id'.$i.'_saved'] = $res[0]['EvnCourseTreatDrug_id'];
						$data['DrugListData'][$i]['id'] = null;
						$data['DrugListData'][$i]['status'] = 'new';
						break;
				}
			}
		}
		return $trans_result;
	}

	/**
	 * Сохранение данных медикамента курса
	 * Функция полностью аналогична прототипу _saveEvnCourseTreatDrug() в базовом классе. Приведена здесь только по причине того, что в базовом классе определена как private
	 */
	private function _saveEvnCourseTreatDrug($data, $drug) {
		if (!empty($drug['id']) && empty($drug['MinDoseDay']) && empty($drug['MaxDoseDay'])) {
			$query = "	
				select
				Drug_id,
				DrugComplexMnn_id,
				CUBICUNITS_id,
				MASSUNITS_id,
				EvnCourseTreatDrug_FactDose,
				EvnCourseTreatDrug_MaxDoseDay,
				EvnCourseTreatDrug_MinDoseDay
				from v_EvnCourseTreatDrug with (nolock) where EvnCourseTreatDrug_id = ?
			";
			$queryParams = array($drug['id']);
			// echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$result = $result->result('array');
			} else {
				return false;
			}
			if (empty($result)) {
				return false;
			}
			if (!empty($result[0]['EvnCourseTreatDrug_FactDose'])) {
				//нельзя обновлять
				return false;
			}
			// Если изменился медикамент или единица измерения
			if (
				(!empty($drug['Drug_id']) && $drug['Drug_id'] != $result[0]['Drug_id'])
				|| (!empty($drug['DrugComplexMnn_id']) && $drug['DrugComplexMnn_id'] != $result[0]['DrugComplexMnn_id'])
				|| (!empty($drug['CUBICUNITS_id']) && $drug['CUBICUNITS_id'] != $result[0]['CUBICUNITS_id'])
				|| (!empty($drug['MASSUNITS_id']) && $drug['MASSUNITS_id'] != $result[0]['MASSUNITS_id'])
			) {
				//так-то в курсе запрещено менять медикамент и ед.измерения, сюда не должно зайти
				$data['EvnCourseTreatDrug_FactDose'] = null;
				$drug['MaxDoseDay'] = $drug['DoseDay'];
				$drug['MinDoseDay'] = $drug['DoseDay'];
			} else {
				// могла изменилась только доза
				$this->_defineMinMaxDoses($drug, $result[0]['EvnCourseTreatDrug_MinDoseDay'], $result[0]['EvnCourseTreatDrug_MaxDoseDay']);
			}
		} else {
			$drug['MaxDoseDay'] = empty($drug['MaxDoseDay'])?$drug['DoseDay']:$drug['MaxDoseDay'];
			$drug['MinDoseDay'] = empty($drug['MinDoseDay'])?$drug['DoseDay']:$drug['MinDoseDay'];
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnCourseTreatDrug_id;

			exec p_EvnCourseTreatDrug_" . ( !empty($drug['id'])? "upd" : "ins" ) . "
				@EvnCourseTreatDrug_id = @Res output,
				@EvnCourseTreat_id = :EvnCourseTreat_id,
				@Drug_id = :Drug_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@EvnCourseTreatDrug_Kolvo = :EvnCourseTreatDrug_Kolvo,
				@EvnCourseTreatDrug_KolvoEd = :EvnCourseTreatDrug_KolvoEd,
				@EvnCourseTreatDrug_FactDose = :EvnCourseTreatDrug_FactDose,
				@CUBICUNITS_id = :CUBICUNITS_id,
				@MASSUNITS_id = :MASSUNITS_id,
				@EvnCourseTreatDrug_MaxDoseDay = :EvnCourseTreatDrug_MaxDoseDay,
				@EvnCourseTreatDrug_MinDoseDay = :EvnCourseTreatDrug_MinDoseDay,
				@EvnCourseTreatDrug_PrescrDose = :EvnCourseTreatDrug_PrescrDose,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnCourseTreatDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnCourseTreatDrug_id' => (empty($drug['id'])? NULL : $drug['id'] ),
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'DrugComplexMnn_id' => (empty($drug['DrugComplexMnn_id'])? NULL : $drug['DrugComplexMnn_id']),
			'CUBICUNITS_id' => (empty($drug['CUBICUNITS_id'])? NULL : $drug['CUBICUNITS_id']),
			'MASSUNITS_id' => (empty($drug['MASSUNITS_id'])? NULL : $drug['MASSUNITS_id']),
			'Drug_id' => (empty($drug['Drug_id'])? NULL : $drug['Drug_id']),
			'EvnCourseTreatDrug_Kolvo' => $drug['Kolvo'],
			'EvnCourseTreatDrug_KolvoEd' => $drug['KolvoEd'],
			'EvnCourseTreatDrug_FactDose' => (empty($data['EvnCourseTreatDrug_FactDose'])? NULL : $data['EvnCourseTreatDrug_FactDose']),
			'EvnCourseTreatDrug_MaxDoseDay' => (empty($drug['MaxDoseDay'])? NULL : $drug['MaxDoseDay']),
			'EvnCourseTreatDrug_MinDoseDay' => (empty($drug['MinDoseDay'])? NULL : $drug['MinDoseDay']),
			'EvnCourseTreatDrug_PrescrDose' => (empty($drug['PrescrDose'])? NULL : $drug['PrescrDose']),
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сравнение доз в одной ед.измерения и определение новых значений макс. и мин. доз
	 * Функция полностью аналогична прототипу _defineMinMaxDoses() в базовом классе. Приведена здесь только по причине того, что в базовом классе определена как private
	 */
	private function _defineMinMaxDoses(&$drug, $minDose, $maxDose) {
		$newDose = $drug['DoseDay'];
		$minDoseStr = $minDose;
		$maxDoseStr = $maxDose;
		$this->_toInt($newDose);
		$this->_toInt($minDose);
		$this->_toInt($maxDose);
		if ($newDose==$minDose && $newDose==$maxDose) {
			//все равны
			$drug['MaxDoseDay'] = $drug['DoseDay'];
			$drug['MinDoseDay'] = $drug['DoseDay'];
			return 0;
		}
		if ($newDose<$minDose) {
			// меньше минимального
			$drug['MaxDoseDay'] = $maxDoseStr;
			$drug['MinDoseDay'] = $drug['DoseDay'];
			return -1;
		}
		if ($newDose>$maxDose) {
			// больше максимального
			$drug['MaxDoseDay'] = $drug['DoseDay'];
			$drug['MinDoseDay'] = $minDoseStr;
			return 1;
		}
		// новая доза входит в диапазон, ничего не меняем
		return 2;
	}
	
	
	/**
	 * @param $dose
	 * Функция полностью аналогична прототипу _toInt() в базовом классе. Приведена здесь только по причине того, что в базовом классе определена как private
	 */
	private function _toInt(&$dose) {
		$parts = explode(' ', $dose);
		if (is_numeric($parts[0])) {
			$dose = $parts[0];
		} else {
			$dose = 0;
		}
	}

	
	/**
	 * Получение данных для формы редактирования курса
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoadEvnCourseTreatEditForm($data) {
		$query = "
			select
				case when ECT.EvnCourseTreat_disDT is null then 'edit' else 'view' end as accessType,

				ECTD.EvnCourseTreatDrug_id as id,
				case when D.Drug_id is not null then 2 else 1 end as MethodInputDrug_id,
				dcm.DrugComplexMnn_id,
				D.Drug_id,
				ECTD.EvnCourseTreatDrug_Kolvo as Kolvo,
				case
					when ECTD.CUBICUNITS_id is not null
						then 'CUBICUNITS_'+ cast(ECTD.CUBICUNITS_ID as varchar)
					when ECTD.MASSUNITS_ID is not null
						then 'MASSUNITS_'+ cast(ECTD.MASSUNITS_ID as varchar)
					else null
				end as EdUnits_id,
				coalesce(D.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name,
				isnull(mu.SHORTNAME, cu.SHORTNAME) as EdUnits_Nick,
				ECTD.EvnCourseTreatDrug_KolvoEd as KolvoEd,
				isnull(df.NAME,D.DrugForm_Name) as DrugForm_Name,
				ECTD.EvnCourseTreatDrug_PrescrDose as PrescrDose,
				ECTD.EvnCourseTreatDrug_FactDose as FactDose,
				ECTD.EvnCourseTreatDrug_MaxDoseDay as MaxDoseDay,
				ECTD.EvnCourseTreatDrug_MinDoseDay as MinDoseDay,
				--null as DoseDay,
				'' as DrugListData,

				ECT.EvnCourseTreat_id,
				ECT.EvnCourseTreat_pid,
				ECT.CourseType_id,
				ECT.Lpu_id,
				ECT.MedPersonal_id,
				ECT.LpuSection_id,
				ECT.Morbus_id,
				convert(varchar(10), ECT.EvnCourseTreat_setDT, 104) as EvnCourseTreat_setDate,
				ECT.EvnCourseTreat_MaxCountDay,
				ECT.EvnCourseTreat_MinCountDay,
				--null as EvnCourseTreat_CountDay,
				ECT.EvnCourseTreat_Duration,
				ECT.EvnCourseTreat_ContReception,
				ECT.EvnCourseTreat_Interval,
				ECT.DurationType_id,
				ECT.DurationType_recid,
				ECT.DurationType_intid,
				ECT.EvnCourseTreat_PrescrCount,
				ECT.EvnCourseTreat_FactCount,
				ECT.ResultDesease_id,
				ECT.PrescriptionTreatType_id,
				ECT.PrescriptionTimeType_id,
				ECT.PrescriptionTreatOrderType_id,
                ECT.EvnCourseTreat_IsPrescrInfusion,
				ECT.PerformanceType_id,
				ECT.PrescriptionIntroType_id,
				case when isnull(EPT.EvnPrescrTreat_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrTreat_IsCito,
				EPT.EvnPrescrTreat_Descr,
				ECT.PersonEvn_id,
				ECT.Server_id
			from
				v_EvnCourseTreat ECT with (nolock)
				inner join v_EvnCourseTreatDrug ECTD with (nolock) on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				outer apply (
					select top 1
						EvnPrescrTreat_IsCito,
						EvnPrescrTreat_Descr
					from v_EvnPrescrTreat with (nolock)
					where EvnCourse_id = ECT.EvnCourseTreat_id
				) EPT
				left join rls.Drug D with (nolock) on D.Drug_id = ECTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = ECTD.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.MASSUNITS mu with (nolock) on ECTD.MASSUNITS_ID = mu.MASSUNITS_ID
				left join rls.CUBICUNITS cu with (nolock) on ECTD.CUBICUNITS_id = cu.CUBICUNITS_id
			where
				ECT.EvnCourseTreat_id = :EvnCourseTreat_id
		";
		$queryParams = array(
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id']
		);
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		$response = array();
		$drugListData = array();
		foreach ($result as $row) {
			$drug = array();
			$drug['id'] = $row['id'];
			$drug['MethodInputDrug_id'] = $row['MethodInputDrug_id'];
			$drug['DrugComplexMnn_id'] = $row['DrugComplexMnn_id'];
			$drug['Drug_id'] = $row['Drug_id'];
			$drug['Drug_Name'] = $row['Drug_Name'];
			$drug['Kolvo'] = $row['Kolvo'];
			$drug['EdUnits_id'] = $row['EdUnits_id'];
			$drug['EdUnits_Nick'] = $row['EdUnits_Nick'];
			$drug['KolvoEd'] = $row['KolvoEd'];
			$drug['DrugForm_Name'] = $row['DrugForm_Name'];
			$drug['PrescrDose'] = $row['PrescrDose'];
			$drug['FactDose'] = $row['FactDose'];
			$drug['DoseDay'] = $row['MaxDoseDay'];//or $row['MinDoseDay'] ?
			$drug['MaxDoseDay'] = $row['MaxDoseDay'];//нужно только для пересчета курса!!!
			$drug['MinDoseDay'] = $row['MinDoseDay'];//нужно только для пересчета курса!!!
			array_walk($drug,'ConvertFromWin1251ToUTF8');
			$drugListData[] = $drug;
	}
		if (!empty($drugListData)) {
			array_walk($result[0],'ConvertFromWin1251ToUTF8');

			unset($result[0]['id']);
			unset($result[0]['MethodInputDrug_id']);
			unset($result[0]['DrugComplexMnn_id']);
			unset($result[0]['Drug_id']);
			unset($result[0]['Drug_Name']);
			unset($result[0]['EdUnits_Nick']);
			unset($result[0]['Kolvo']);
			unset($result[0]['EdUnits_id']);
			unset($result[0]['KolvoEd']);
			unset($result[0]['DrugForm_Name']);
			unset($result[0]['PrescrDose']);
			unset($result[0]['FactDose']);
			unset($result[0]['MaxDoseDay']);
			unset($result[0]['MinDoseDay']);
			$result[0]['DrugListData'] = json_encode($drugListData);

			$result[0]['EvnCourseTreat_CountDay'] = $result[0]['EvnCourseTreat_MaxCountDay'];// or $result[0]['EvnCourseTreat_MinCountDay'] ?
			//unset($result[0]['EvnCourseTreat_MaxCountDay']);
			//unset($result[0]['EvnCourseTreat_MinCountDay']);

			$response[] = $result[0];
		}
		return $response;
	}


}

