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
require_once('EvnPrescrAbstract_model.php');
/**
 * Модель назначения "Лекарственное лечение"
 *
 * Назначения с типом "Лекарственное лечение" хранятся в таблице EvnPrescrTreat
 * В назначении должен быть указан препарат, который хранится в EvnPrescrTreatDrug.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property EvnPrescr_model $EvnPrescr_model
 */
class EvnPrescrTreat_model extends EvnPrescrAbstract_model
{
	/**
	 * @var array
	 */
	private $_dataForRecountEvnCourse = array();

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 5;
	}

	/**
	 * Определение идентификатора типа курса
	 * @return int
	 */
	public function getCourseTypeId() {
		return 1;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrTreat';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doSaveEvnCourseTreat':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),

					array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
					array('field' => 'EvnCourseTreat_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'MedPersonal_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
					array('field' => 'LpuSection_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
					array('field' => 'Morbus_id','label' => 'Идентификатор ','rules' => '','type' => 'id'),
					array('field' => 'EvnCourseTreat_setDate','label' => 'Начать','rules' => 'required','type' => 'date'),
					array('field' => 'EvnCourseTreat_CountDay','label' => 'Приемов в сутки','rules' => 'required','type' => 'int'),
					array('field' => 'EvnCourseTreat_Duration','label' => 'Продолжительность','rules' => 'required','type' => 'int'),
					array('field' => 'DurationType_id','label' => 'Тип продолжительности','rules' => 'required','type' => 'id'),
					array('field' => 'EvnCourseTreat_ContReception','label' => 'Непрерывный прием','rules' => 'required','type' => 'int'),
					array('field' => 'DurationType_recid','label' => 'Тип Непрерывный прием','rules' => 'required','type' => 'id'),
					array('field' => 'EvnCourseTreat_Interval','label' => 'Перерыв','rules' => '','type' => 'int'),
					array('field' => 'DurationType_intid','label' => 'Тип Перерыв','rules' => '','type' => 'id'),
					array('field' => 'ResultDesease_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
					array('field' => 'PerformanceType_id','label' => 'Исполнение','rules' => '','type' => 'id'),
					array('field' => 'PrescriptionIntroType_id','label' => 'Способ применения','rules' => 'required','type' => 'id'),
					array('field' => 'PrescriptionTreatType_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
					array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),
					array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'arr_time','label' => 'Параметры времени приема',	'rules' => '','type' => 'string')
				);
				break;
			case 'doSave':
				$rules = array(
					array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'EvnPrescrTreat_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'EvnCourse_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),

					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),

					array('field' => 'EvnPrescrTreat_PrescrCount','label' => 'Приемов в сутки','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrTreat_setDate','label' => 'Начать','rules' => '','type' => 'date'),
					array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int')
				);
				break;
			case 'doLoadEvnCourseTreatEditForm':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя учетного документа', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
					array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoad':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя учетного документа', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
					array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoadEvnDrugGrid':
				$rules = array(
					array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
			case 'doLoadEvnPrescrTreatDrugCombo':
				$rules = array(
					array('field' => 'EvnPrescrTreat_pid','label' => 'Идентификатор учетного документа', 'rules' => '', 'type' => 'id'),
					array('field' => 'EvnPrescrTreat_id','label' => 'Идентификатор назначения', 'rules' => '', 'type' =>  'id'),
					array('field' => 'EvnPrescrTreatDrug_id','label' => 'Медикамент','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrTreat_setDate','label' => 'Дата','rules' => '','type' => 'date'),
				);
				break;
			case 'doLoadEvnPrescrTreatDrugDataView':
				$rules = array(
					array('field' => 'EvnCourse_id','label' => 'Идентификатор курса', 'rules' => 'required', 'type' =>  'id'),
				);
				break;
		}
		return $rules;
	}

	/**
	 * Вспомогательная функция для создания курса
	 */
	protected function _getNumDay($durationtype_id) {
		$num = 1;
		switch ($durationtype_id) {
			case 2: $num = 7; break;
			case 3: $num = 30; break;
		}
		return $num;
	}

	/**
	 * Сохранение данных курса
	 */
	protected function _saveEvnCourseTreat(&$data,$drug_list = null) {
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
			  ,@pmUser_id = :pmUser_id
			  ,@Error_Code = @ErrCode OUTPUT
			  ,@Error_Message = @ErrMessage OUTPUT;

			select @Res as EvnCourseTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$data['EvnCourseTreat_setDT'] = empty($data['EvnCourseTreat_setDT']) ? NULL : $data['EvnCourseTreat_setDT'];
		$data['EvnCourseTreat_disDT'] = empty($data['EvnCourseTreat_disDT']) ? NULL : $data['EvnCourseTreat_disDT'];
		$data['ResultDesease_id'] = empty($data['ResultDesease_id']) ? NULL : $data['ResultDesease_id'];
		$data['EvnCourseTreat_FactCount'] = NULL;//факт. число приемов учитывается на медикаменте
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

//		 echo getDebugSQL($query, $data); exit();

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
						$data['DrugListData'][$i]['EvnCourseTreatDrug_id'] = $row['id'];
						$data['DrugListData'][$i]['id'] = null;
						$data['DrugListData'][$i]['status'] = 'new';
						break;
					case 'deleted':
						if(is_array($drug_list) && count($drug_list)>0){
							foreach ($drug_list as $drug) {
								if($drug['DrugComplexMnn_id'] == $row['DrugComplexMnn_id']) {
									if (2 == $drug['EvnPrescrTreat_IsExec']
										|| 2 == $drug['PrescriptionStatusType_id']
										|| !empty($drug['EvnDrug_id'])
									) {
										//выполненные назначения оставляем как есть,
										$isAllowCancel = false;
									} else {
										$isAllowCancel = true;
									}
									if ($isAllowCancel) {
										//отменяем назначение
										$this->_destroy(array(
											'object'=>'EvnPrescrTreatDrug',
											'id'=>$drug['EvnPrescrTreatDrug_id']
										));

									} else {
										throw new Exception('Удаление невозможно. Для выбранного медикамента имеется выполненное назначение. Для удаления записи необходимо удалить выполнение назначения.', 500);
									}
								}
							}
						}
						$query1 = "
							select
								EvnReceptGeneralDrugLink_id
							from
								EvnReceptGeneralDrugLink with (nolock)
							where
								EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id
						";
						//echo getDebugSQL($query1, array('EvnCourseTreatDrug_id'=>$row['id']));exit;
						$reslt = $this->db->query($query1, array('EvnCourseTreatDrug_id'=>$row['id']));
						if (!is_object($reslt)) {
							throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
						}
						$erg_list = $reslt->result('array');
						if(count($erg_list)>0) {
							//throw new Exception('Удаление невозможно. Для медикамента выписан рецепт. Для удаления записи необходимо удалить связанный, с медикаментом рецепт.', 500);
							throw new Exception('Удаление медикамента из курса лекарственного лечения невозможно. Для медикамента выписан рецепт. Для удаления записи необходимо удалить медикамент из рецепта.', 500);
							/*foreach ($erg_list as $erg_list_item) {
								$query = "
									delete
									from
										EvnReceptGeneral with (rowlock)
									where
										EvnReceptGeneral_id = :EvnReceptGeneral_id
								";
								//echo getDebugSql($query, $data); die();
								$res = $this->db->query($query, array('EvnReceptGeneral_id'=>$erg_list_item['EvnReceptGeneral_id']));
								if ( $res !== true ) {
									throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
								}
							}*/
						}
						$query2 = "
							select
								EvnPrescrTreatDrug_id
							from
								v_EvnPrescrTreatDrug with (nolock)
							where
								EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id
						";
						//echo getDebugSQL($query, $queryParams);exit;
						$reslt = $this->db->query($query2, array('EvnCourseTreatDrug_id'=>$row['id']));
						if (!is_object($reslt)) {
							throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
						}
						$erg_list = $reslt->result('array');
						if(count($erg_list)>0) {
							foreach ($erg_list as $erg_list_item) {
								$this->_destroy(array(
									'object'=>'EvnPrescrTreatDrug',
									'id'=>$erg_list_item['EvnPrescrTreatDrug_id']
								));
							}
						}
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
						$data['DrugListData'][$i]['EvnCourseTreatDrug_id'] = $res[0]['EvnCourseTreatDrug_id'];
						$data['DrugListData'][$i]['id'] = null;
						$data['DrugListData'][$i]['status'] = 'new';
						break;
				}
			}
		}
		return $trans_result;
	}

	/**
	 * Проверяем есть ли изменения количества медикаментов,
	 * и получаем данные необходимые для пересчета
	 * @param $newDrugList
	 * @param $compareObject
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	private function _isNeedChangeDrug($newDrugList, $compareObject, $id) {
		$hasChange = false;
		$oldDrugList = array();
		switch ($compareObject) {
			case 'EvnCourseTreat':
				$tmp = $this->doLoadEvnCourseTreatEditForm(array('EvnCourseTreat_id'=>$id));
				if(empty($tmp)) {
					throw new Exception('Ошибка при получении данных курса', 500);
				}
				array_walk($tmp[0], 'ConvertFromUTF8ToWin1251');
				$this->_dataForRecountEvnCourse['EvnCourseData'] = $tmp[0];
				$this->_dataForRecountEvnCourse['EvnCourseData']['DrugListData'] = $this->_processingDrugListData($this->_dataForRecountEvnCourse['EvnCourseData'], 'EvnCourseTreatDrug', false);
				$oldDrugList = &$this->_dataForRecountEvnCourse['EvnCourseData']['DrugListData'];
				if ( $tmp[0]['EvnCourseTreat_MinCountDay'] > $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] ) {
					// надо обновить минимальное число приемов в сутки
					$tmp[0]['EvnCourseTreat_MinCountDay'] = $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'];
					$hasChange = true;
				}
				if ( $tmp[0]['EvnCourseTreat_MaxCountDay'] < $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] ) {
					// надо обновить максимальное число приемов в сутки
					$tmp[0]['EvnCourseTreat_MaxCountDay'] = $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'];
					$hasChange = true;
				}
				break;
			case 'EvnPrescrTreat':
				$tmp = $this->doLoad(array('EvnPrescrTreat_id'=>$id));
				if(empty($tmp)) {
					throw new Exception('Ошибка при получении данных назначения', 500);
				}
				array_walk($tmp[0], 'ConvertFromUTF8ToWin1251');
				$oldDrugList = $this->_processingDrugListData($tmp[0], 'EvnPrescrTreatDrug', false);
				if ( $tmp[0]['EvnPrescrTreat_PrescrCount'] != $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] ) {
					$hasChange = true;
				}
				break;
		}
		if (empty($oldDrugList)) {
			throw new Exception('В назначении или в курсе нет медикаментов!', 500);
		}
		// считаем, что в назначении нельзя добавить или изменить медикамент, разве что удалить
		$drugArrs = array();
		foreach ($oldDrugList as $i => $oldDrug) {
			$drugArrs[$i] = null;
			foreach ($newDrugList as $j => $newDrug) {
				if ($oldDrug['DrugComplexMnn_id'] > 0 && $oldDrug['DrugComplexMnn_id']==$newDrug['DrugComplexMnn_id']) {
					$drugArrs[$i] = $j;
				}
				if ($oldDrug['Drug_id'] > 0 && $oldDrug['Drug_id']==$newDrug['Drug_id']) {
					$drugArrs[$i] = $j;
				}
				if ('deleted'==$newDrug['status']) {
					$drugArrs[$i] = null;
				}
			}
			//$row['status']
		}
		//сравниваем
		foreach ($drugArrs as $i => $j) {
			if (!isset($j)) {
				// в назначении был удален медикамент $oldDrugList[$i]
				// Нужно сделать перерасчет курсовой дозы этого медикамента
				$hasChange = true;
				$oldDrugList[$i]['typeChanged'] = '-1d';
			} else if ($oldDrugList[$i]['Kolvo']!=$newDrugList[$j]['Kolvo']) {
				// изменилось количество в ед.измерения
				// надо обновить максимальную или минимальную дневные дозы
				// и сделать перерасчет курсовой дозы
				$hasChange = true;
				$oldDrugList[$i]['typeChanged'] = 'updKolvo';
				$oldDrugList[$i]['newKolvo'] = $newDrugList[$j]['Kolvo'];
			} else if (empty($oldDrugList[$i]['Kolvo']) && $oldDrugList[$i]['KolvoEd']!=$newDrugList[$j]['KolvoEd']) {
				// изменилось количество в ед.дозировки,
				// пересчитывать нужно только если не указано количество в ед.измерения
				// надо обновить максимальную или минимальную дневные дозы
				// и сделать перерасчет курсовой дозы
				$hasChange = true;
				$oldDrugList[$i]['typeChanged'] = 'updKolvoEd';
				$oldDrugList[$i]['newKolvoEd'] = $newDrugList[$j]['KolvoEd'];
			}
		}
		if ($hasChange) {
			switch ($compareObject) {
				case 'EvnCourseTreat':
					//
					break;
				case 'EvnPrescrTreat':
					$this->_dataForRecountEvnCourse['oldEvnPrescrTreatDrugList'] = $oldDrugList;
					$tmp = $this->doLoadEvnCourseTreatEditForm(array('EvnCourseTreat_id'=>$this->_dataForRecountEvnCourse['EvnCourse_id']));
					if(empty($tmp)) {
						throw new Exception('Ошибка при получении данных курса', 500);
					}
					array_walk($tmp[0], 'ConvertFromUTF8ToWin1251');
					if ( $tmp[0]['EvnCourseTreat_MinCountDay'] > $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] ) {
						// надо обновить минимальное число приемов в сутки
						$tmp[0]['EvnCourseTreat_MinCountDay'] = $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'];
					}
					if ( $tmp[0]['EvnCourseTreat_MaxCountDay'] < $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] ) {
						// надо обновить максимальное число приемов в сутки
						$tmp[0]['EvnCourseTreat_MaxCountDay'] = $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'];
					}
					$this->_dataForRecountEvnCourse['EvnCourseData'] = $tmp[0];
					$this->_dataForRecountEvnCourse['EvnCourseData']['DrugListData'] = $this->_processingDrugListData($this->_dataForRecountEvnCourse['EvnCourseData'], 'EvnCourseTreatDrug', false);
					break;
			}
		}
		return $hasChange;
	}


	/**
	 * Определение формы единиц дозировки (поля KolvoEd "Ед. дозировки на 1 прием:")
	 * @param string $DrugForm_Name CLSDRUGFORMS_NameLatinSocr или rls.CLSDRUGFORMS.Name или rls.Drug.DrugForm_Name
	 * @param string $Drug_Name rls.DrugComplexMnn.DrugComplexMnn_RusName или rls.Drug.Drug_Name
	 * @param string $mode Для рецепта - "for_signa". По умолчанию "for_dose_count"
	 * @return string
	 */
	public function getDrugFormNick($DrugForm_Name, $Drug_Name, $mode = 'for_dose_count')
	{
		$str = empty($DrugForm_Name)?$Drug_Name:$DrugForm_Name;
		if (!empty($str)) {
			$str = mb_strtolower(toUTF($str));
		}
		switch (true) {
			case (strpos($str, 'pulv.') !== false && 'for_signa' != $mode):
			case (strpos($str, 'порошок') !== false && 'for_signa' != $mode):
			case (strpos($str, 'пор.') !== false && 'for_signa' != $mode):
				$DrugForm_Nick = 'пор.'; break;
			case (strpos($str, 'sol.') !== false && 'for_signa' != $mode):
			case (strpos($str, 'раствор') !== false && 'for_signa' != $mode):
			case (strpos($str, 'р-р') !== false && 'for_signa' != $mode):
				$DrugForm_Nick = 'раствор'; break;
			case (strpos($str, 'qtt.') !== false && 'for_signa' != $mode):
			case (strpos($str, 'капли') !== false && 'for_signa' != $mode):
				$DrugForm_Nick = 'капли'; break;
			case (strpos($str, 'briketi') !== false):
			case (strpos($str, 'брикеты') !== false):
				$DrugForm_Nick = 'брикеты'; break;
			case (strpos($str, 'granuli') !== false):
			case (strpos($str, 'гран.') !== false):
				$DrugForm_Nick = 'гран.'; break;
			case (strpos($str, 'dragees') !== false):
			case (strpos($str, 'драже') !== false):
				$DrugForm_Nick = 'драже'; break;
			case (strpos($str, 'capsulae') !== false):
			case (strpos($str, 'капс.') !== false):
				$DrugForm_Nick = 'капс.'; break;
			case (strpos($str, 'supp.') !== false):
			case (strpos($str, 'супп.') !== false):
				$DrugForm_Nick = 'супп.'; break;
			case (strpos($str, 'tabl.') !== false):
			case (strpos($str, 'табл.') !== false):
				$DrugForm_Nick = 'табл.'; break;
			case (strpos($str, 'ppl.') !== false):
			case (strpos($str, 'пилюли') !== false):
				$DrugForm_Nick = 'пилюли'; break;
			default: $DrugForm_Nick = ''; break;
		}
		return $DrugForm_Nick;
	}

	/**
	 * Перерасчет данных медикамента курса
	 */
	private function _recountEvnCourseTreatDrug(&$data, $i, $drug) {
		if (!empty($drug['FactDose'])) {
			$data['DrugListData'][$i] = $drug;
			return false;
		}
		// Расчет суточной и курсовой доз
		$typeChanged = empty($drug['typeChanged'])?'':$drug['typeChanged'];
		$kolvo = empty($drug['newKolvo'])?$drug['Kolvo']:$drug['newKolvo'];
		$EdUnits_Nick = $drug['EdUnits_Nick'];
		$kolvoEd = empty($drug['newKolvoEd'])?$drug['KolvoEd']:$drug['newKolvoEd'];
		$DrugForm_Nick = $this->getDrugFormNick($drug['DrugForm_Name'], $drug['Drug_Name']);
		if (isset($this->_dataForRecountEvnCourse['oldEvnPrescrTreatDrugList'])) {
			$drugChanged = false;
			foreach ($this->_dataForRecountEvnCourse['oldEvnPrescrTreatDrugList'] as $ep_drug) {
				if ($drug['DrugComplexMnn_id'] > 0 && $drug['DrugComplexMnn_id']==$ep_drug['DrugComplexMnn_id']) {
					$drugChanged = $ep_drug;
					break;
				}
				if ($drug['Drug_id'] > 0 && $drug['Drug_id']==$ep_drug['Drug_id']) {
					$drugChanged = $ep_drug;
					break;
				}
			}
			if ($drugChanged) {
				$typeChanged = empty($drugChanged['typeChanged'])?'':$drugChanged['typeChanged'];
				$kolvo = empty($drugChanged['newKolvo'])?$drug['Kolvo']:$drugChanged['newKolvo'];
				$EdUnits_Nick = $drugChanged['EdUnits_Nick'];
				$kolvoEd = empty($drugChanged['newKolvoEd'])?$drug['KolvoEd']:$drugChanged['newKolvoEd'];
				$DrugForm_Nick = $this->getDrugFormNick($drugChanged['DrugForm_Name'], $drugChanged['Drug_Name']);
			}
		}
		$cntDay = $data['EvnCourseTreat_MaxCountDay']; // OR $data['EvnCourseTreat_MinCountDay'] ?
		if (isset($this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'])) {
			$cntDay = $this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'];
		}
		$drug['DoseDay']='';
		$drug['PrescrDose']='';
		$prescrCntDay=0;
		if ( $kolvo && $EdUnits_Nick ) {
			// в ед. измерения
			$prescrCntDay = $cntDay*$kolvo;
			$drug['DoseDay'] = round($prescrCntDay, 5) .' '. $EdUnits_Nick;
			$DrugForm_Nick = null;
		}
		if ($kolvoEd && empty($kolvo)) {
			// в ед. дозировки только если не указано в ед.измерения
			$prescrCntDay = $cntDay*$kolvoEd;
			$drug['DoseDay'] = round($prescrCntDay, 5) .' '. $DrugForm_Nick;
			$EdUnits_Nick = null;
		}
		$resCompareDayDoses = $this->_defineMinMaxDoses($drug, $drug['MinDoseDay'], $drug['MaxDoseDay']);
		if ($prescrCntDay > 0 && $data['EvnCourseTreat_Duration']>0 && $data['EvnCourseTreat_ContReception']>0) {
			$duration = $data['EvnCourseTreat_Duration'];
			$cont = $data['EvnCourseTreat_ContReception'];
			$interval = $data['EvnCourseTreat_Interval'];
			switch (true) {
				case ($data['DurationType_id'] == 2): $duration *= 7; break;
				case ($data['DurationType_id'] == 3): $duration *= 30; break;
				case ($data['DurationType_recid'] == 2): $cont *= 7; break;
				case ($data['DurationType_recid'] == 3): $cont *= 30; break;
				case ($interval > 0 && $data['DurationType_intid'] == 2): $interval *= 7; break;
				case ($interval > 0 && $data['DurationType_intid'] == 3): $interval *= 30; break;
			}
			if ('-1d' == $typeChanged) {
				$duration--;
			}
			$ed = (isset($DrugForm_Nick)?$DrugForm_Nick:$EdUnits_Nick);
			$sep = ' - ';
			switch($resCompareDayDoses) {
				case 0: //все равны
					$drug['PrescrDose'] = $this->_cntPrescrDose($prescrCntDay, $duration, $interval, $cont, $ed);
					break;
				case 1: //меньше минимального
					$maxDose = $drug['MaxDoseDay'];
					$this->_toInt($maxDose);
					$drug['PrescrDose'] = $this->_cntPrescrDose($prescrCntDay, $duration, $interval, $cont, $ed);
					$drug['PrescrDose'] .= $sep.$this->_cntPrescrDose($maxDose, $duration, $interval, $cont, $ed);
					break;
				case -1: //больше максимального
					$minDose = $drug['MaxDoseDay'];
					$this->_toInt($minDose);
					$drug['PrescrDose'] = $this->_cntPrescrDose($minDose, $duration, $interval, $cont, $ed);
					$drug['PrescrDose'] .= $sep.$this->_cntPrescrDose($prescrCntDay, $duration, $interval, $cont, $ed);
					break;
				default: // новая доза входит в диапазон
					$minDose = $drug['MaxDoseDay'];
					$this->_toInt($minDose);
					$maxDose = $drug['MaxDoseDay'];
					$this->_toInt($maxDose);
					$drug['PrescrDose'] = $this->_cntPrescrDose($minDose, $duration, $interval, $cont, $ed);
					$drug['PrescrDose'] .= $sep.$this->_cntPrescrDose($maxDose, $duration, $interval, $cont, $ed);
					break;
			}
		}
		$drug['status'] = 'updated';
		$data['DrugListData'][$i] = $drug;
		return true;
	}

	/**
	 * Расчет курсовой дозы
	 */
	private function _cntPrescrDose($prescrCntDay, $duration, $interval, $cont, $ed) {
		if ($interval > 0) {
			$prescrCntCourse = $prescrCntDay*($duration-($interval*floor($duration/($interval+$cont))));
		} else {
			$prescrCntCourse = $prescrCntDay*$duration;
		}
		return (round($prescrCntCourse, 5) .' '. $ed);
	}

	/**
	 * Перерасчет данных курса и медикаментов курса
	 * Вызывается после выполнения метода _isNeedChangeDrug или другого способа получения $this->_dataForRecountEvnCourse['EvnCourseData']
	 * и внесения изменений в БД (обновление, добавление, отмена)
	 */
	private function _recountEvnCourse() {
		$data = &$this->_dataForRecountEvnCourse['EvnCourseData'];
		$data['pmUser_id'] = $this->_dataForRecountEvnCourse['pmUser_id'];
		if (empty($data['EvnCourseTreat_setDate'])) {
			$data['EvnCourseTreat_setDT'] = NULL;
		} else {
			$data['EvnCourseTreat_setDT'] = date('Y-m-d', strtotime($data['EvnCourseTreat_setDate']));
		}
		if (empty($data['EvnCourseTreat_Duration'])) {
			$data['EvnCourseTreat_Duration'] = 1;
		}
		if (empty($data['DurationType_id'])) {
			$data['DurationType_id'] = 1;
		}
		if (empty($data['EvnCourseTreat_ContReception'])) {
			$data['EvnCourseTreat_ContReception'] = 1;
		}
		if (empty($data['DurationType_recid'])) {
			$data['DurationType_recid'] = 1;
		}
		if (empty($data['EvnCourseTreat_Interval'])) {
			$data['EvnCourseTreat_Interval'] = 0;
		}
		if (empty($data['DurationType_intid'])) {
			$data['DurationType_intid'] = 1;
		}
		switch ($this->_dataForRecountEvnCourse['typeChanged']) {
			case 'insEvnPrescrTreat':
				// В курс было добавлено новое назначение,
				// в $data уже актуальное максимальное или минимальное число приемов в сутки
				// перерасчет продолжительности
				$data['EvnCourseTreat_Duration']++;
				// нужно сделать перерасчет данных медикаментов курса
				foreach ($data['DrugListData'] as $i => $drug) {
					$this->_recountEvnCourseTreatDrug($data, $i, $drug);
				}
				break;
			case 'updEvnPrescrTreat':
				// в назначении было изменено число приемов в сутки,
				// в $data уже актуальное максимальное или минимальное число приемов в сутки
				// нужно сделать перерасчет данных медикаментов курса
				foreach ($data['DrugListData'] as $i => $drug) {
					$this->_recountEvnCourseTreatDrug($data, $i, $drug);
				}
				break;
			case 'cancelEvnPrescrTreat':
				// в курсе было отменено назначение,
				// перерасчет продолжительности
				if ( empty($this->_dataForRecountEvnCourse['isCancelEvnCourse']) ) {
					if (empty($data['EvnCourseTreat_Duration']) || $data['EvnCourseTreat_Duration']==1) {
						//если было отменено единственное невыполненое назначение в курсе
						//то нужно удалить курс
						$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
						return $this->EvnPrescr_model->cancelEvnCourse(array(
							'EvnCourse_id' => $data['EvnCourseTreat_id'],
							'pmUser_id' => $data['pmUser_id']
						), true);
					}
				}
				$data['EvnCourseTreat_Duration']--;
				// нужно сделать перерасчет данных медикаментов курса
				foreach ($data['DrugListData'] as $i => $drug) {
					$this->_recountEvnCourseTreatDrug($data, $i, $drug);
				}
				break;
		}
		// сохраняем результат пересчета
		//var_dump($this->_dataForRecountEvnCourse);
		//var_dump($data);
		return $this->_saveEvnCourseTreat($data);
	}

	/**
	 * Сохранение назначения
	 */
	protected function _save($data = array(), $isDoSave = false) {
		if (empty($data['EvnCourse_id'])) {
			throw new Exception('Не указан курс', 400);
		}
		$allowRecountEvnCourse = false;
		$this->_dataForRecountEvnCourse = array();
		$this->_dataForRecountEvnCourse['EvnCourse_id'] = $data['EvnCourse_id'];
		$this->_dataForRecountEvnCourse['pmUser_id'] = $data['pmUser_id'];

		if(empty($data['EvnPrescrTreat_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['EvnPrescrTreat_setDT'] = NULL;
			$data['EvnPrescrTreat_PrescrCount'] = empty($data['EvnPrescrTreat_PrescrCount']) ? 1 : $data['EvnPrescrTreat_PrescrCount'];
			$data['EvnPrescrTreat_id'] = NULL;
			$data['PrescriptionStatusType_id'] = 1;
			if ($isDoSave) {
				// нужно сделать перерасчет продолжительности и курсовой дозы
				// также возможно надо обновить максимальное или минимальное число приемов в сутки,
				// максимальную или минимальную дневные дозы
				$allowRecountEvnCourse = true;
				$this->_dataForRecountEvnCourse['typeChanged'] = 'insEvnPrescrTreat';
				$this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] = $data['EvnPrescrTreat_PrescrCount'];
				$this->_isNeedChangeDrug($data['DrugListData'], 'EvnCourseTreat', $data['EvnCourse_id']);
			}
		}
		else
		{
			$action = 'upd';
			$o_data = $this->getAllData($data['EvnPrescrTreat_id']);
			if (!empty($o_data['Error_Msg'])) {
				throw new Exception($o_data['Error_Msg'], 500);
			}
			$allow_sign = (isset($o_data['PrescriptionStatusType_id']) && $o_data['PrescriptionStatusType_id'] == 1);
			$allowChangeFields = array(
				'EvnPrescrTreat_PrescrCount',
				'EvnPrescrTreat_IsCito',
				'EvnPrescrTreat_Descr'
				//остальное нельзя изменить
			);
			$data['PrescriptionStatusType_id'] = null;
			foreach ($o_data as $key => $value) {
				if (array_key_exists($key, $data) && !in_array($key, $allowChangeFields)) {
					$data[$key] = $value;
				}
			}
			// определяем необходимость пересчета данных курса
			if ($isDoSave) {
				$this->_dataForRecountEvnCourse['typeChanged'] = 'updEvnPrescrTreat';
				$this->_dataForRecountEvnCourse['EvnPrescrTreat_PrescrCount'] = $data['EvnPrescrTreat_PrescrCount'];
				$allowRecountEvnCourse = $this->_isNeedChangeDrug($data['DrugListData'], 'EvnPrescrTreat', $data['EvnPrescrTreat_id']);
			}
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrTreat_id;

			exec p_EvnPrescrTreat_" . $action . "
				@EvnPrescrTreat_id = @Res output,
				@EvnPrescrTreat_pid = :EvnPrescrTreat_pid,
				@EvnCourse_id = :EvnCourse_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPrescrTreat_setDT = :EvnPrescrTreat_setDT,
				@EvnPrescrTreat_PrescrCount = :EvnPrescrTreat_PrescrCount,
				@EvnPrescrTreat_IsCito = :EvnPrescrTreat_IsCito,
				@EvnPrescrTreat_Descr = :EvnPrescrTreat_Descr,
				@pmUser_id = :pmUser_id,
				@EvnPrescrTreat_IsExec = :EvnPrescrTreat_IsExec,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if ( !empty($data['EvnPrescrTreat_setDate']) ) {
			$data['EvnPrescrTreat_setDT'] = $data['EvnPrescrTreat_setDate'];
		}
		if(!empty($data['EvnPrescrTreat_IsCito']) && ($data['EvnPrescrTreat_IsCito'] == 'on' || $data['EvnPrescrTreat_IsCito'] == 2)) {
			$data['EvnPrescrTreat_IsCito'] = 2;
		} else {
			$data['EvnPrescrTreat_IsCito'] = 1;
		}
		$data['EvnCourse_id'] = empty($data['EvnCourse_id']) ? NULL : $data['EvnCourse_id'];
		$data['EvnPrescrTreat_IsExec'] = empty($data['EvnPrescrTreat_IsExec']) ? 1 : $data['EvnPrescrTreat_IsExec'];
		$data['PrescriptionType_id'] = $this->getPrescriptionTypeId();
		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при сохранении назначения', 500);
		}
		$trans_result = $result->result('array');
		if(!empty($trans_result[0]['Error_Msg'])) {
			throw new Exception($trans_result[0]['Error_Msg'], 500);
		}
		
		//  Для Уфы обрабатываем время приема
		if (in_array($this->getRegionNick(),array('ufa','vologda')) && isset($data['arr_time'])) {

			$EvnCourse_id = $data['EvnCourseTreat_id'];
			$prefix = $this->getRegionNick() == 'ufa'
				? 'r2'
				: 'r35';

			$arr_data = json_decode($data['arr_time'], 1);
			if ($arr_data == array()) {
				$query = "
							declare
								@Evn_id bigint = :Evn_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null
								
							exec {$prefix}.p_CourseTimeIntake_del
								@EvnCourse_id = @Evn_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;							

							Select @Error_Code as Error_Code, @Error_Message as Error_Msg;

						 ";
				$params = array();
				$params ['Evn_id'] = $EvnCourse_id;
			} else {
				$xml = '<RD>';
				if (isset($data['pmUser_id']))
					$pmUser = $data['pmUser_id'];
				else
					$pmUser = '';
				$EvnDrug_id = NULL;
				//echo ('$pmUser = ' .$pmUser);
				foreach ($arr_data as $item) {
					$idx = "";
					$time = "";
					if (isset($item['idx']))
						$idx = $item['idx'];
					if (isset($item['time']))
						$time = $item['time'];

					$xml .='<R|*|v1="' . $EvnCourse_id . '" 
							  |*|v2="' . $EvnDrug_id . '" 
							  |*|v3="' . $pmUser . '"
							  |*|v4="' . $idx . '"
							  |*|v5="' . $time . '" ></R>';
				}

				$xml .= '</RD>';
				$xml = strtr($xml, array(PHP_EOL => '', " " => ""));
				$xml = str_replace("|*|", " ", $xml);

				$params = array('xml' => (string) $xml);

				$query = "
							Declare 
							@xml nvarchar(max),
							@Error_Code int,
							@Error_Message varchar(4000)

								Set @xml = :xml;

								 exec {$prefix}.p_CourseTimeIntake_upd @xml, @Error_Code, @Error_Message

								 Select @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
						";
			}

			$result = $this->db->query($query, $params);

			if (is_object($result)) {
				$trans_arrresult = $result->result('array');
				if (!empty($trans_arrresult[0]['Error_Msg'])) {
					throw new Exception($trans_arrresult[0]['Error_Msg'], 500);
				}
			} else {
				throw new Exception('Ошибка при сохранении времени приема');
			}
		}

		$data['EvnPrescrTreat_id'] = $trans_result[0]['EvnPrescrTreat_id'];
		if (!empty($data['DrugListData']) && is_array($data['DrugListData'])) {
			foreach ($data['DrugListData'] as $i => $row) {
				switch ($row['status']) {
					case 'saved': // сохранено и не изменено
						$trans_result[0]['EvnPrescrTreatDrug_id'.$i.'_nosaved'] = $row['id'];
						break;
					case 'deleted':
						$this->_destroy(array(
							'object'=>'EvnPrescrTreatDrug',
							'id'=>$row['id'],
						));
						$trans_result[0]['EvnPrescrTreatDrug_id'.$i.'_deleted'] = $row['id'];
						break;
					default:
						$res = $this->_saveEvnPrescrTreatDrug($data, $row);
						if(empty($res)) {
							throw new Exception('Ошибка при сохранении медикамента', 500);
						}
						if (!empty($res[0]['Error_Msg'])) {
							throw new Exception($res[0]['Error_Msg'], 500);
						}
						$trans_result[0]['EvnPrescrTreatDrug_id'.$i.'_saved'] = $res[0]['EvnPrescrTreatDrug_id'];
						break;
				}
			}
			if ($allowRecountEvnCourse) {
				$this->_recountEvnCourse();
			}
		}
		return $trans_result;
	}

	/**
	 * Проверка и обработка списка медикаментов
	 */
	private function _processingDrugListData($data, $object='EvnPrescrTreatDrug', $isStrongValidate = true) {
		if (empty($data['DrugListData'])) {
			throw new Exception('Не указаны медикаменты', 400);
		}
		$arr = is_array($data['DrugListData'])?$data['DrugListData']:json_decode($data['DrugListData'], true);
		if (!is_array($arr) || empty($arr)) {
			throw new Exception('Неправильный формат медикаментов', 400);
		}
		foreach ($arr as &$row) {
			array_walk($row, 'ConvertFromUTF8ToWin1251');
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
				if (in_array($parts[0], array('CUBICUNITS', 'MASSUNITS', 'ACTUNITS')) && !empty($parts[1]) && is_numeric($parts[1])) {
					$row[$parts[0].'_id'] = $parts[1];
				}
			}
			if (empty($row['DrugComplexMnnDose_Mass'])) {
				$isStrongValidate = false;
			}
			if ($isStrongValidate && empty($row['CUBICUNITS_id']) && empty($row['MASSUNITS_id']) && empty($row['ACTUNITS_id'])) {
				throw new Exception('Не указана единица измерения', 400);
			}
			if ($isStrongValidate && empty($row['Kolvo'])) {
				throw new Exception('Не указано кол-во ед. измерения', 400);
			}
			if ($isStrongValidate && empty($row['KolvoEd']) && !empty($row['DrugForm_Nick'])) {
				throw new Exception('Не указано кол-во ед. дозировки', 400);
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
			if (empty($row['FactCount'])) {
				$row['FactCount'] = 0;
			}
		}
		return $arr;
	}

	/**
	 * Сохранение курса
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

				//Список лекарств для дальнейшей проверки в ходе дестроя удаленных лекарств
				$queryParams = array(
					'EvnCourse_id' => $data['EvnCourseTreat_id']
				);
				$query1 = "
					select
						EPTD.EvnPrescrTreatDrug_id,
						EPTD.DrugComplexMnn_id,
						EPT.PrescriptionStatusType_id,
						EDr.EvnDrug_id,
						EPT.EvnPrescrTreat_IsExec
					from
						v_EvnPrescrTreatDrug EPTD with (nolock)
						left join v_EvnPrescrTreat EPT with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
						outer apply (
							select top 1 ED.EvnDrug_id 
							from EvnDrug ED with (nolock) 
							where ED.EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id OR ED.EvnPrescr_id = EPTD.EvnPrescrTreat_id
						) EDr
					where
						EPT.EvnCourse_id = :EvnCourse_id
				";
				//echo getDebugSQL($query1, $queryParams);exit;
				$reslt = $this->db->query($query1, $queryParams);
				if (!is_object($reslt)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
				}
				$drug_list = $reslt->result('array');

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
						IsNull(EDr.EvnDrug_id,ED.EvnDrug_id) as EvnDrug_id,
						EPT.EvnPrescrTreat_IsExec
					from
						v_EvnPrescrTreat EPT with (nolock)
						outer apply (
							select top 1
								ED2.EvnDrug_id
							from
								EvnDrug ED2 with (nolock)
							where
								ED2.EvnPrescr_id = EPT.EvnPrescrTreat_id
						) ED
						outer apply (
							select top 1 ED1.EvnDrug_id from v_EvnPrescrTreatDrug EPTD with(nolock)
							inner join EvnDrug ED1 with (nolock) on ED1.EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id
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
				if ( $data['EvnCourseTreat_MinCountDay'] != $countInDay ) {
					// надо обновить минимальное число приемов в сутки
					$data['EvnCourseTreat_MinCountDay'] = $countInDay;
				}
				if ( $data['EvnCourseTreat_MaxCountDay'] != $countInDay ) {
					// надо обновить максимальное число приемов в сутки
					$data['EvnCourseTreat_MaxCountDay'] = $countInDay;
				}
				$response = $this->_saveEvnCourseTreat($data,$drug_list);
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
						//echo'TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT';
						$response[0]['EvnPrescrTreat_id'.$i] = $res[0]['EvnPrescrTreat_id'];
					}
				} else if (!empty($date_list) && isset($date_list[0])) {
					//создаем назначение
					$data['EvnPrescrTreat_setDate'] = $date_list[0];
					$res = $this->_save($data);
					$response[0]['EvnPrescrTreat_id0'] = $res[0]['EvnPrescrTreat_id'];
				}
				/*
				//  Для Уфы обрабатываем время приема
				if ($_SESSION['region']['nick'] == 'ufa' && $data['arr_time']) {

					$EvnCourse_id = $data['EvnCourseTreat_id'];


					$arr_data = json_decode($data['arr_time'], 1);
					if ($arr_data == array()) {
						$query = "
							declare
								@Evn_id bigint = :Evn_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null
								
							exec r2.p_EvnCourseTreatTimeEntry_del
								@Evn_id = @Evn_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;							

							Select @Error_Code as Error_Code, @Error_Message as Error_Msg;

						 ";
						$params = array();
						$params ['Evn_id'] = $EvnCourse_id;
					} else {
						$xml = '<RD>';
						if (isset($data['pmuser_id']))
							$pmUser = $data['pmuser_id'];
						else
							$pmUser = '';
						foreach ($arr_data as $item) {
							$idx = "";
							$time = "";
							if (isset($item['idx']))
								$idx = $item['idx'];
							if (isset($item['time']))
								$time = $item['time'];

							$xml .='<R|*|v1="' . $EvnCourse_id . '" 
							  |*|v2="' . $pmUser . '"
							  |*|v3="' . $idx . '"
							  |*|v4="' . $time . '" ></R>';
						}

						$xml .= '</RD>';
						$xml = strtr($xml, array(PHP_EOL => '', " " => ""));
						$xml = str_replace("|*|", " ", $xml);

						$params = array('xml' => (string) $xml);

						$query = "
							Declare 
							@xml nvarchar(max),
							@Error_Code int,
							@Error_Message varchar(4000)

								Set @xml = :xml;

								 exec r2.XP_evnCourseTreatTimeEntry_upd @xml, @Error_Code, @Error_Message

								 Select @Error_Code as Error_Code, @Error_Message as Error_Msg ;  
						";
					}

					$result = $this->db->query($query, $params);

					if (is_object($result)) {
						$trans_result = $result->result('array');
						if (!empty($trans_result[0]['Error_Msg'])) {
							throw new Exception($trans_result[0]['Error_Msg'], 500);
						}
					} else {
						throw new Exception('Ошибка при сохранении времени приема');
					}
				}
				*/
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		
		$this->commitTransaction();
		return $response;
	}

	/**
	 * Сохранение назначения
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			if (empty($data['Lpu_id'])) {
				throw new Exception('Неправильный массив параметров', 500);
			}
			$data['DrugListData'] = $this->_processingDrugListData($data, 'EvnPrescrTreatDrug');
			$response = $this->_save($data, true);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg'=>$e->getMessage(), 'Error_Code'=>$e->getCode()));
		}
		$this->commitTransaction();
		return $response;
	}

	/**
	 * Сохранение данных медикамента дневного назначения
	 */
	private function _saveEvnPrescrTreatDrug($data, $drug) {
		$queryParams = array(
			'EvnPrescrTreatDrug_id' => (empty($drug['id'])? NULL : $drug['id'] ),
			'EvnCourseTreatDrug_id' => (empty($drug['EvnCourseTreatDrug_id'])? NULL : $drug['EvnCourseTreatDrug_id'] ),
			'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id'],
			'DrugComplexMnn_id' => (empty($drug['DrugComplexMnn_id'])? NULL : $drug['DrugComplexMnn_id']),
			'CUBICUNITS_id' => (empty($drug['CUBICUNITS_id'])? NULL : $drug['CUBICUNITS_id']),
			'MASSUNITS_id' => (empty($drug['MASSUNITS_id'])? NULL : $drug['MASSUNITS_id']),
			'ACTUNITS_id' => (empty($drug['ACTUNITS_id'])? NULL : $drug['ACTUNITS_id']),
			'Drug_id' => (empty($drug['Drug_id'])? NULL : $drug['Drug_id']),
			'EvnPrescrTreatDrug_Kolvo' => (empty($drug['Kolvo'])? NULL : $drug['Kolvo']),
			'EvnPrescrTreatDrug_KolvoEd' => (empty($drug['KolvoEd'])? NULL : $drug['KolvoEd']),
			'EvnPrescrTreatDrug_DoseDay' => (empty($drug['DoseDay'])? NULL : $drug['DoseDay']),
			'EvnPrescrTreatDrug_FactCount' => (empty($drug['FactCount'])? 0 : $drug['FactCount']),
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($drug['id'])) {
			$o_data = $this->getAllData($drug['id'], 'EvnPrescrTreatDrug');
			if (!empty($o_data['Error_Msg'])) {
				return array($o_data);
			}
			$allowChangeFields = array(
				'EvnPrescrTreatDrug_Kolvo',
				'EvnPrescrTreatDrug_KolvoEd',
				'EvnPrescrTreatDrug_DoseDay'
				//остальное нельзя изменить
			);
			foreach ($o_data as $key => $value) {
				if (array_key_exists($key, $queryParams) && !in_array($key, $allowChangeFields)) {
					$queryParams[$key] = $value;
				}
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrTreatDrug_id;

			exec p_EvnPrescrTreatDrug_" . ( !empty($drug['id'])? "upd" : "ins" ) . "
				@EvnPrescrTreatDrug_id = @Res output,
				@EvnPrescrTreat_id = :EvnPrescrTreat_id,
				@EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id,
				@Drug_id = :Drug_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@EvnPrescrTreatDrug_Kolvo = :EvnPrescrTreatDrug_Kolvo,
				@EvnPrescrTreatDrug_KolvoEd = :EvnPrescrTreatDrug_KolvoEd,
				@CUBICUNITS_id = :CUBICUNITS_id,
				@MASSUNITS_id = :MASSUNITS_id,
				@ACTUNITS_id = :ACTUNITS_id,
				@EvnPrescrTreatDrug_DoseDay = :EvnPrescrTreatDrug_DoseDay,
				@EvnPrescrTreatDrug_FactCount = :EvnPrescrTreatDrug_FactCount,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrTreatDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $dose
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
	 * Сравнение доз в одной ед.измерения и определение новых значений макс. и мин. доз
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
	 * Сохранение данных медикамента курса
	 */
	private function _saveEvnCourseTreatDrug($data, $drug) {
		if (!empty($drug['id']) && empty($drug['MinDoseDay']) && empty($drug['MaxDoseDay'])) {
			$query = "	
				select
				Drug_id,
				DrugComplexMnn_id,
				CUBICUNITS_id,
				MASSUNITS_id,
				ACTUNITS_id,
				EvnCourseTreatDrug_FactCount,
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
			if (!empty($result[0]['EvnCourseTreatDrug_FactCount'])) {
				//нельзя обновлять
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
				|| (!empty($drug['ACTUNITS_id']) && $drug['ACTUNITS_id'] != $result[0]['ACTUNITS_id'])
			) {
				//так-то в курсе запрещено менять медикамент и ед.измерения, сюда не должно зайти
				$data['EvnCourseTreatDrug_FactCount'] = 0;
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
				@EvnCourseTreatDrug_FactCount = :EvnCourseTreatDrug_FactCount,
				@EvnCourseTreatDrug_FactDose = :EvnCourseTreatDrug_FactDose,
				@CUBICUNITS_id = :CUBICUNITS_id,
				@MASSUNITS_id = :MASSUNITS_id,
				@ACTUNITS_id = :ACTUNITS_id,
				@EvnCourseTreatDrug_MaxDoseDay = :EvnCourseTreatDrug_MaxDoseDay,
				@EvnCourseTreatDrug_MinDoseDay = :EvnCourseTreatDrug_MinDoseDay,
				@EvnCourseTreatDrug_PrescrDose = :EvnCourseTreatDrug_PrescrDose,
				@GoodsUnit_id = :GoodsUnit_id,
				@GoodsUnit_sid = :GoodsUnit_sid,
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
			'ACTUNITS_id' => (empty($drug['ACTUNITS_id'])? NULL : $drug['ACTUNITS_id']),
			'Drug_id' => (empty($drug['Drug_id'])? NULL : $drug['Drug_id']),
			'EvnCourseTreatDrug_Kolvo' => $drug['Kolvo'],
			'EvnCourseTreatDrug_KolvoEd' => $drug['KolvoEd'],
			'EvnCourseTreatDrug_FactCount' => (empty($data['EvnCourseTreatDrug_FactCount'])? 0 : $data['EvnCourseTreatDrug_FactCount']),
			'EvnCourseTreatDrug_FactDose' => (empty($data['EvnCourseTreatDrug_FactDose'])? NULL : $data['EvnCourseTreatDrug_FactDose']),
			'EvnCourseTreatDrug_MaxDoseDay' => (empty($drug['MaxDoseDay'])? NULL : $drug['MaxDoseDay']),
			'EvnCourseTreatDrug_MinDoseDay' => (empty($drug['MinDoseDay'])? NULL : $drug['MinDoseDay']),
			'EvnCourseTreatDrug_PrescrDose' => (empty($drug['PrescrDose'])? NULL : $drug['PrescrDose']),
			'GoodsUnit_id' => (empty($drug['GoodsUnit_id'])? NULL : $drug['GoodsUnit_id']),
			'GoodsUnit_sid' => (empty($drug['GoodsUnit_sid'])? NULL : $drug['GoodsUnit_sid']),
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			$DrugOnOstat = $this->getDrugOnOstat(array(
				'LpuSection_id' => $data['LpuSection_id'],
				'Drug_id' => $drug['Drug_id'],
				'DrugComplexMnn_id' => $drug['DrugComplexMnn_id']
			));
			if (// только ПЕрмь, только ГП2
				//'perm' == $this->regionNick && 10010833 == $data['Lpu_id']
				// только для поликлиники (и стоматки)
				//&&
                isset($data['parentEvnClass_SysNick']) && false == in_array($data['parentEvnClass_SysNick'],array('EvnSection','EvnPS'))
				&& isset($data['EvnCourseTreat_pid'])
				// только при создании
				&& empty($queryParams['EvnCourseTreatDrug_id'])
				&& !empty($response)
				&& empty($response[0]['Error_Msg'])
				&& !empty($response[0]['EvnCourseTreatDrug_id'])
				&& empty($DrugOnOstat)	//не создавать рецепт, если медикамент есть на остатках отделения
			) {
                $queryParams = array(
                    'EvnCourseTreat_pid' => $data['EvnCourseTreat_pid'],
                    'DrugComplexMnn_id' => (empty($drug['DrugComplexMnn_id'])? NULL : $drug['DrugComplexMnn_id']),
                    'Drug_rlsid' => (empty($drug['Drug_id'])? NULL : $drug['Drug_id']),
                );
                $join = '';
                if (!empty($queryParams['Drug_rlsid'])) {
                    $join = 'inner join rls.v_DrugComplexMnn dcm (nolock) on dcm.DrugComplexMnn_id = drug.DrugComplexMnn_id';
                }
                if (!empty($queryParams['DrugComplexMnn_id'])) {
                    $join = 'inner join rls.v_DrugComplexMnn dcm (nolock) on dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
                }
                $query = "
                    select top 1
                    e.Diag_id,
                    drug.drug_id as Drug_rlsid,
                    Drug.Drug_Dose,
                    mnndose.DrugComplexMnnDose_Name,
                    mnndose.DrugComplexMnnDose_Mass,
                    mnndose.MASSUNITS_id,
                    Drug.Drug_Fas,
                    isnull(nomen.DRUGSINPPACK,1) as DRUGSINPPACK,
                    isnull(nomen.PPACKINUPACK,1) as PPACKINUPACK,
                    case when exists (
                        select top 1 actmatters.actmatters_id
                        from rls.drugcomplexmnnname (nolock)
                        inner join rls.actmatters (nolock) on actmatters.actmatters_id = drugcomplexmnnname.actmatters_id
                            and (actmatters.STRONGGROUPID>0 or actmatters.NARCOGROUPID>=3)
                        where drugcomplexmnnname.drugcomplexmnnname_id=dcm.drugcomplexmnnname_id
                    ) then '148-88'
                    else '107' end as ReceptForm_Code,
                    dcm.DrugComplexMnn_id
                    from evnvizitpl e (nolock)
                    left join rls.v_drug drug (nolock) on drug.drug_id = :Drug_rlsid
                    {$join}
					left join rls.v_Nomen nomen with (nolock) on nomen.NOMEN_ID = drug.Drug_id
                    left join rls.v_DrugComplexMnnFas mnnfas WITH (NOLOCK) on mnnfas.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                    left join rls.v_DrugComplexMnnDose mnndose WITH (NOLOCK) on mnndose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                    where e.evnvizitpl_id = :EvnCourseTreat_pid
                ";
                //$tmp = $this->getFirstRowFromQuery($query, $queryParams);
                $result = $this->db->query($query, $queryParams);
                if (is_object($result)) {
                    $tmp = $result->result('array');
                    if (isset($tmp[0])) {
                        $tmp = $tmp[0];
                    } else {
                        throw new Exception('Не удалось получить данные для рецепта', 500);
                    }
                } else {
                    throw new Exception('Не удалось выполнить запрос данных для рецепта', 500);
                }
                /*
                if (empty($tmp)) {
                    throw new Exception('Не удалось получить данные для рецепта '.getDebugSQL($query, $queryParams).var_export($tmp, true), 500);
                }
                */
                $data['ReceptForm_Code'] = $tmp['ReceptForm_Code'];
                $data['Diag_id'] = $tmp['Diag_id'];
                $data['Drug_rlsid'] = $tmp['Drug_rlsid'];
                $data['DrugComplexMnn_id'] = $tmp['DrugComplexMnn_id'];
                $kolvo_in_ed = null;
                $ed_in_fas = null;
                $kolvo = 1;
                $prescrDose = null;
                if (!empty($drug['Kolvo']) && !empty($drug['PrescrDose'])) {
                    $prescrDose = intval($drug['PrescrDose'])+0;
                    //$this->_toInt($drug['PrescrDose']);
                    if (!empty($tmp['Drug_Dose'])) {
                        $kolvo_in_ed = $tmp['Drug_Dose'];
                        $this->_toInt($kolvo_in_ed);
                    }
                    if (!empty($tmp['DrugComplexMnnDose_Name']) && empty($kolvo_in_ed)) {
                        $kolvo_in_ed = $tmp['DrugComplexMnnDose_Name'];
                        $this->_toInt($kolvo_in_ed);
                    }
                    if (!empty($drug['DrugComplexMnnDose_Mass'])
                        && !empty($drug['MASSUNITS_id'])
                        && $drug['MASSUNITS_id'] == $tmp['MASSUNITS_id']
                    ) {
                        $kolvo_in_ed = $tmp['DrugComplexMnnDose_Mass']+0;
                    }
                    $ed_in_fas = $tmp['DRUGSINPPACK']*$tmp['PPACKINUPACK'];
                    if (!empty($tmp['Drug_Fas'])) {
                        $ed_in_fas = $tmp['Drug_Fas']+0;
                    }
                }
                if (!empty($prescrDose) && !empty($kolvo_in_ed) && !empty($ed_in_fas)) {
                    $kolvo = ceil($prescrDose/($kolvo_in_ed*$ed_in_fas));
                }
                if (1 == $kolvo) {
                    //throw new Exception("ceil({$prescrDose}/({$kolvo_in_ed}*{$ed_in_fas}))".var_export($tmp, true));
                }
                if (empty($kolvo)) {
                    //throw new Exception("ceil({$prescrDose}/({$kolvo_in_ed}*{$ed_in_fas}))".var_export($tmp, true));
                    throw new Exception("Неправильный расчет количества");
                }
                $signa = null;
                $drugform_name = '';
                $drug_name = '';
                if(!empty($drug['DrugComplexMnn_id']) && empty($drug['Drug_Name']) && empty($drug['Drug_Name']))
                {
                    $query_getNames = "
                        select
                          ISNULL(DrugForm_Name,'') as DrugForm_Name,
                          ISNULL(Drug_Name,'') as Drug_Name
                        from rls.Drug with(nolock) where DrugComplexMnn_id = :DrugComplexMnn_id
                    ";
                    $params_getNames = array(
                        'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
                    );
                    $res_Names = $this->db->query($query_getNames,$params_getNames);
                    if(is_object($res_Names))
                    {
                        $result_Names = $res_Names->result('array');
                        if(is_array($result_Names) && count($result_Names) > 0)
                        {
                            $drugform_name = $result_Names[0]['DrugForm_Name'];
                            $drug_name = $result_Names[0]['Drug_Name'];
                        }
                    }
                }

                $DrugForm_Nick = $this->getDrugFormNick($drugform_name, $drug_name, 'for_signa');
                //var_dump($drug_name);die;
                if ($DrugForm_Nick && !empty($drug['KolvoEd'])) {
                    $signa = $drug['KolvoEd'] . ' '. $DrugForm_Nick .' на 1 прием';
                } else if (!empty($drug['Kolvo']) && !empty($drug['EdUnits_Nick'])) {
                    $signa = $drug['Kolvo'] . ' '. $drug['EdUnits_Nick'] .' на 1 прием';
                }
                if (!empty($signa)) {
                    if (!empty($data['EvnCourseTreat_MinCountDay']) && !empty($data['EvnCourseTreat_MaxCountDay'])
                        && $data['EvnCourseTreat_MinCountDay'] != $data['EvnCourseTreat_MaxCountDay']
                    ) {
                        $signa .= ', '. $data['EvnCourseTreat_MinCountDay'] .' - '. $data['EvnCourseTreat_MaxCountDay'] . ' приемов в сутки';
                    } else if (!empty($data['EvnCourseTreat_MinCountDay'])) {
                        $signa .= ', '. $data['EvnCourseTreat_MinCountDay'] . ' приемов в сутки';
                    } else if (!empty($data['EvnCourseTreat_MaxCountDay'])) {
                        $signa .= ', '. $data['EvnCourseTreat_MaxCountDay'] . ' приемов в сутки';
                    }
                    if (!empty($data['EvnCourseTreat_Duration']) && !empty($data['DurationType_id']) && empty($data['EvnCourseTreat_Interval'])) {
                        $duration = $data['EvnCourseTreat_Duration']+0;
                        switch (true) {
                            case (1 == $data['DurationType_id'] && 1 == $duration):
                                $signa .= ', 1 день';
                                break;
                            case (1 == $data['DurationType_id'] && in_array($duration,array(2,3,4))):
                                $signa .= ', '. $duration . ' дня';
                                break;
                            case (1 == $data['DurationType_id'] && $duration > 4):
                                $signa .= ', '. $duration . ' дней';
                                break;
                            case (2 == $data['DurationType_id'] && 1 == $duration):
                                $signa .= ', одну неделю';
                                break;
                            case (2 == $data['DurationType_id'] && in_array($duration,array(2,3,4))):
                                $signa .= ', '. $duration . ' недели';
                                break;
                            case (2 == $data['DurationType_id'] && $duration > 4):
                                $signa .= ', '. $duration . ' недель';
                                break;
                            case (3 == $data['DurationType_id'] && 1 == $duration):
                                $signa .= ', 1 месяц';
                                break;
                            case (3 == $data['DurationType_id'] && in_array($duration,array(2,3,4))):
                                $signa .= ', '. $duration . ' месяца';
                                break;
                            case (3 == $data['DurationType_id'] && $duration > 4):
                                $signa .= ', '. $duration . ' месяцев';
                                break;
                        }
                    }
                    if (!empty($data['EvnPrescrTreat_Descr'])) {
                        $signa .= ', '. $data['EvnPrescrTreat_Descr'];
                    }
                }
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка единиц измерения
	 */
	function loadEdUnitsList() {
		$query = "
			select
				'CUBICUNITS_'+ cast(CUBICUNITS_ID as varchar) as EdUnits_id,
				CUBICUNITS_ID as EdUnits_Code,
				SHORTNAME as EdUnits_Name,
				FULLNAME as EdUnits_FullName
			from rls.CUBICUNITS with (nolock)
			where CUBICUNITS_ID > 0
			union all
			select
				'MASSUNITS_'+ cast(MASSUNITS_ID as varchar)  as EdUnits_id,
				MASSUNITS_ID as EdUnits_Code,
				SHORTNAME as EdUnits_Name,
				FULLNAME as EdUnits_FullName
			from rls.MASSUNITS with (nolock)
			where MASSUNITS_ID > 0
			union all
			select
				'ACTUNITS_'+ cast(ACTUNITS_ID as varchar)  as EdUnits_id,
				ACTUNITS_ID as EdUnits_Code,
				SHORTNAME as EdUnits_Name,
				FULLNAME as EdUnits_FullName
			from rls.ACTUNITS with (nolock)
			where ACTUNITS_ID > 0
			order by EdUnits_Code
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
	 * Получение данных для формы редактирования назначения
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select
				case when EPT.PrescriptionStatusType_id = 1 and isnull(EPT.EvnPrescrTreat_IsExec,1) = 1 and ECT.EvnCourseTreat_disDT is null then 'edit' else 'view' end as accessType,

				EPTD.EvnPrescrTreatDrug_id as id,
				case when D.Drug_id is not null then 2 else 1 end as MethodInputDrug_id,
				dcm.DrugComplexMnn_id,
				D.Drug_id,
				EPTD.EvnPrescrTreatDrug_Kolvo as Kolvo,
				case
					when EPTD.CUBICUNITS_id is not null
						then 'CUBICUNITS_'+ cast(EPTD.CUBICUNITS_ID as varchar)
					when EPTD.MASSUNITS_ID is not null
						then 'MASSUNITS_'+ cast(EPTD.MASSUNITS_ID as varchar)
					when EPTD.ACTUNITS_ID is not null
						then 'ACTUNITS_'+ cast(EPTD.ACTUNITS_ID as varchar)
					else null
				end as EdUnits_id,
				coalesce(D.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name,
				coalesce(mu.SHORTNAME, cu.SHORTNAME, au.SHORTNAME) as EdUnits_Nick,
				EPTD.EvnPrescrTreatDrug_KolvoEd as KolvoEd,
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,D.DrugForm_Name,'') as DrugForm_Name,
				EPTD.EvnPrescrTreatDrug_DoseDay as DoseDay,
				EPTD.EvnPrescrTreatDrug_FactCount as FactCount,
				'' as DrugListData,

				EPT.EvnPrescrTreat_id,
				EPT.EvnPrescrTreat_pid,
				EPT.EvnCourse_id,
				EPT.PrescriptionType_id,
				EPT.PrescriptionStatusType_id,
				convert(varchar(10), EPT.EvnPrescrTreat_setDT, 104) as EvnPrescrTreat_setDate,
				EPT.EvnPrescrTreat_PrescrCount,
				case when isnull(EPT.EvnPrescrTreat_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrTreat_IsCito,
				EPT.EvnPrescrTreat_IsExec,
				EPT.EvnPrescrTreat_Descr,
				EPT.PersonEvn_id,
				EPT.Server_id
			from
				v_EvnPrescrTreat EPT with (nolock)
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				inner join v_EvnCourseTreat ECT with (nolock) on EPT.EvnCourse_id = ECT.EvnCourseTreat_id
				left join rls.Drug D with (nolock) on D.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,D.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.MASSUNITS mu with (nolock) on EPTD.MASSUNITS_ID = mu.MASSUNITS_ID
				left join rls.CUBICUNITS cu with (nolock) on EPTD.CUBICUNITS_id = cu.CUBICUNITS_id
				left join rls.ACTUNITS au with (nolock) on EPTD.ACTUNITS_id = au.ACTUNITS_id
				
			where
				EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id
		";

		$queryParams = array(
			'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']
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
			$drug['DoseDay'] = $row['DoseDay'];
			$drug['FactCount'] = $row['FactCount'];
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
			unset($result[0]['DoseDay']);
			unset($result[0]['FactCount']);
			$result[0]['DrugListData'] = json_encode($drugListData);

			$response[] = $result[0];
		}
		return $response;
	}

	/**
	 * Получение данных для формы редактирования курса
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoadEvnCourseTreatEditForm($data) {
		$ch = '"';
		$selectTime = ', null as EvnPrescrTreat_Time ';
		
		if (in_array($this->getRegionNick(),array('ufa','vologda'))) {
			// Для назначения с учетом времени
			$prefix = $this->getRegionNick() == 'ufa'
				? 'r2'
				: 'r35';
			$selectTime = ", '[ ' +
						(SElect '{ idx:' + convert(varchar, CourseTimeIntake_idx) + ',' + ' time:{$ch}' + convert(varchar, CourseTimeIntake_time) + '{$ch} },'
								from {$prefix}.CourseTimeIntake with(nolock)
									where EvnCourse_id = ECT.EvnCourseTreat_id
										and isnull(EvnDrug_id, 0) = 0 
									for xml path(''))  
				+ ']'  EvnPrescrTreat_Time ";
		};
		
		$query = "
			select
				case when ECT.EvnCourseTreat_disDT is null then 'edit' else 'view' end as accessType,

				ECTD.EvnCourseTreatDrug_id as id,
				case when ECTD.Drug_id is not null then 2 else 1 end as MethodInputDrug_id,
				dcm.DrugComplexMnn_id,
				D.Drug_id,
				ECTD.EvnCourseTreatDrug_Kolvo as Kolvo,
				case
					when ECTD.CUBICUNITS_id is not null
						then 'CUBICUNITS_'+ cast(ECTD.CUBICUNITS_ID as varchar)
					when ECTD.MASSUNITS_ID is not null
						then 'MASSUNITS_'+ cast(ECTD.MASSUNITS_ID as varchar)
					when ECTD.ACTUNITS_ID is not null
						then 'ACTUNITS_'+ cast(ECTD.ACTUNITS_ID as varchar)
					else null
				end as EdUnits_id,
				coalesce(D.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name,
				coalesce(mu.SHORTNAME, cu.SHORTNAME, au.SHORTNAME) as EdUnits_Nick,
				ECTD.EvnCourseTreatDrug_KolvoEd as KolvoEd,
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,D.DrugForm_Name,'') as DrugForm_Name,
				DrugComplexMnnDose.DrugComplexMnnDose_Mass,
				ECTD.EvnCourseTreatDrug_PrescrDose as PrescrDose,
				ECTD.EvnCourseTreatDrug_FactDose as FactDose,
				ECTD.EvnCourseTreatDrug_MaxDoseDay as MaxDoseDay,
				ECTD.EvnCourseTreatDrug_MinDoseDay as MinDoseDay,
				ECTD.EvnCourseTreatDrug_FactCount as FactCount,
				ECTD.GoodsUnit_id,
				ECTD.GoodsUnit_sid,
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
				ECT.ResultDesease_id,
				ECT.PrescriptionTreatType_id,
				ECT.PerformanceType_id,
				ECT.PrescriptionIntroType_id,
				case when isnull(EPTG.EvnPrescrTreat_IsCito,1) = 1 then 'off' else 'on' end as EvnPrescrTreat_IsCito,
				EPTG.EvnPrescrTreat_Descr,
				EPTG.EvnPrescrTreatDrug_DoseDay,
				ECT.PersonEvn_id,
				ECT.Server_id,
				COALESCE(ln.NAME,ACT.LATNAME,DrugComplexMnn_LatName,'') as LatName,
				erg.EvnReceptGeneral_id,
				erg.EvnReceptGeneralDrugLink_id
				{$selectTime}
			from
				v_EvnCourseTreat ECT with (nolock)
				inner join v_EvnCourseTreatDrug ECTD with (nolock) on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join rls.Drug D with (nolock) on D.Drug_id = ECTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(ECTD.DrugComplexMnn_id,D.DrugComplexMnn_id)
				outer apply (
					select top 1
						EPT.EvnPrescrTreat_IsCito,
						EPT.EvnPrescrTreat_Descr,
						EPTD.EvnPrescrTreatDrug_DoseDay
					from v_EvnPrescrTreat EPT with (nolock)
					left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
						and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					where EPT.EvnCourse_id = ECT.EvnCourseTreat_id
				) EPTG
				left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.MASSUNITS mu with (nolock) on ECTD.MASSUNITS_ID = mu.MASSUNITS_ID
				left join rls.CUBICUNITS cu with (nolock) on ECTD.CUBICUNITS_id = cu.CUBICUNITS_id
				left join rls.ACTUNITS au with (nolock) on ECTD.ACTUNITS_id = au.ACTUNITS_id
				left join rls.drugcomplexmnnname cmnn  with (nolock) on cmnn.DrugComplexMnnName_id= dcm.DrugComplexMnnName_id
				left join rls.ACTMATTERS ACT with (nolock) on ACT.ACTMATTERS_ID = cmnn.ACTMATTERS_id
				left join rls.PREP p with (nolock) on p.Prep_id = D.DrugPrep_id 
				left join rls.LATINNAMES ln with (nolock) on ln.LATINNAMES_ID = p.LATINNAMEID
				outer apply (
					select top 1
						erg.EvnReceptGeneral_id,
						ergdl.EvnReceptGeneralDrugLink_id
					from v_EvnReceptGeneralDrugLink ergdl (nolock)
					inner join v_EvnReceptGeneral ERG on ERG.EvnReceptGeneral_id = ergdl.EvnReceptGeneral_id
					where ergdl.EvnCourseTreatDrug_id = ECTD.EvnCourseTreatDrug_id
				) erg
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
			$doseDay = '';
			if(!empty($row['EvnCourseTreat_MaxCountDay']) && !empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])){
				$doseDay = ($row['EvnCourseTreat_MaxCountDay']*$row['Kolvo']).' '.$row['EdUnits_Nick'];
			}else if(!empty($row['EvnPrescrTreatDrug_DoseDay'])){
				$doseDay = $row['EvnPrescrTreatDrug_DoseDay'];
			}
			$drug = array();
			$drug['id'] = $row['id'];
			$drug['MethodInputDrug_id'] = $row['MethodInputDrug_id'];
			$drug['DrugComplexMnn_id'] = $row['DrugComplexMnn_id'];
			$drug['Drug_id'] = $row['Drug_id'];
			$drug['Drug_Name'] = $row['Drug_Name'];
			$drug['Kolvo'] = round($row['Kolvo'], 5);
			$drug['EdUnits_id'] = $row['EdUnits_id'];
			$drug['EdUnits_Nick'] = $row['EdUnits_Nick'];
			$drug['KolvoEd'] = round($row['KolvoEd'], 5);
			$drug['DrugForm_Name'] = $row['DrugForm_Name'];
			$drug['DrugComplexMnnDose_Mass'] = round($row['DrugComplexMnnDose_Mass'], 5);
			$drug['PrescrDose'] = $row['PrescrDose'];
			$drug['FactDose'] = $row['FactDose'];
			$drug['DoseDay'] = (!empty($doseDay) ? $doseDay : $row['MaxDoseDay']);//or $row['MinDoseDay'] ?
			$drug['MaxDoseDay'] = $row['MaxDoseDay'];//нужно только для пересчета курса!!!
			$drug['MinDoseDay'] = $row['MinDoseDay'];//нужно только для пересчета курса!!!
			$drug['FactCount'] = $row['FactCount'];
			$drug['GoodsUnit_id'] = $row['GoodsUnit_id'];
			$drug['GoodsUnit_sid'] = $row['GoodsUnit_sid'];
			$drug['LatName'] = $row['LatName'];
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
			unset($result[0]['DrugComplexMnnDose_Mass']);
			unset($result[0]['PrescrDose']);
			unset($result[0]['FactDose']);
			unset($result[0]['MaxDoseDay']);
			unset($result[0]['MinDoseDay']);
			unset($result[0]['FactCount']);
			unset($result[0]['GoodsUnit_id']);
			unset($result[0]['GoodsUnit_sid']);
			$result[0]['DrugListData'] = json_encode($drugListData);

			$result[0]['EvnCourseTreat_CountDay'] = $result[0]['EvnCourseTreat_MaxCountDay'];// $result[0]['EvnCourseTreat_MaxCountDay'];// or $result[0]['EvnCourseTreat_MinCountDay'] ?
			//unset($result[0]['EvnCourseTreat_MaxCountDay']);
			//unset($result[0]['EvnCourseTreat_MinCountDay']);
			
			if (in_array($this->getRegionNick(),array('ufa','vologda'))) {
				// Для назначения с учетом времени
				$result[0]['EvnPrescrTreat_Time'] = str_replace(',]', ']', $result[0]['EvnPrescrTreat_Time']);
			}
			$response[] = $result[0];
		}
		return $response;
	}

	/**
	 * Получение медикамента из остатков в отделении
	 */
	function getOldDrugOnOstat($data) {
		$params = array(
			'LpuSection_id' => $data['LpuSection_id']
		);
		$filters = "";

		if (!empty($data['Drug_id'])) {
			$filters .= " and Drug.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$filters .= " and Drug.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}

		$query = "
			select top 1
				DUO.Drug_id
			from
				rls.v_Drug Drug WITH (NOLOCK)
				inner join v_DocumentUcOst_Lite DUO WITH (NOLOCK) on Drug.Drug_id = DUO.Drug_id
				inner join Contragent C WITH (NOLOCK) on C.Contragent_id = DUO.Contragent_tid
			where
				DUO.DocumentUcStr_Ost > 0
				and C.LpuSection_id = :LpuSection_id
				{$filters}
		";
		$result = $this->getFirstResultFromQuery($query, $params);
		return ($result!==false)?$result:null;
	}

	/**
	 * Получение медикамента из остатков в отделении (по складу отделения)
	 */
	function getDrugOnOstat($data) {
		$params = array(
			'LpuSection_id' => $data['LpuSection_id']
		);
		$filters = "";

		if (!empty($data['Drug_id'])) {
			$filters .= " and Drug.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$filters .= " and Drug.DrugComplexMnn_id = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}

		$query = "
			select distinct
				DOR.Drug_id
			from
				v_DrugOstatRegistry DOR WITH (NOLOCK)
				inner join rls.v_Drug Drug WITH (NOLOCK) on Drug.Drug_id = DOR.Drug_id
				outer apply (
					select top 1 isnull(sum(rED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
					from v_EvnDrug rED with(nolock)
					inner join v_DocumentUcStr rDUS with(nolock) on rDUS.DocumentUcStr_id = rED.DocumentUcStr_id
					inner join v_DocumentUc rDU with(nolock) on rDU.DocumentUc_id = rDUS.DocumentUc_id
					inner join v_DrugShipmentLink oDSL with(nolock) on oDSL.DocumentUcStr_id = rED.DocumentUcStr_oid
					where oDSL.DrugShipment_id = DOR.DrugShipment_id and isnull(rDU.DrugDocumentStatus_id,1) = 1 --новый
				) rl
				outer apply (
					select top 1
						(DOR.DrugOstatRegistry_Kolvo-isnull(rl.EvnDrug_Kolvo,0)) as DocumentUcStr_Ost
				) ost
			where
				ost.DocumentUcStr_Ost > 0
				and DOR.Storage_id in (select Storage_id from v_StorageStructLevel with(nolock) where LpuSection_id = :LpuSection_id)
				{$filters}
		";

		$result = $this->getFirstResultFromQuery($query, $params);
		return ($result!==false)?$result:null;
	}

	/**
	 * Загрузка списка препаратов назначения ЛС для выполнения со списанием (по контрагенту отделения)
	 */
	function doOldLoadEvnDrugGrid($data) {
		$query = "
			select distinct
				 EvnDrug.EvnDrug_id
				,isnull(EvnDrug.Drug_id, DD.Drug_id) as Drug_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,DD.DrugPrepFas_id
				,DD.Drug_Fas
				,EPTD.EvnPrescrTreatDrug_id
				,EPTD.DrugComplexMnn_id
				-- для списания в полке берем общее число приемов в курсе
				,case when EVPL.EvnVizit_id is not null
					then ECT.EvnCourseTreat_PrescrCount
					else EPT.EvnPrescrTreat_PrescrCount
				end as EvnPrescrTreat_PrescrCount
				,case when EVPL.EvnVizit_id is not null
					then isnull(ECTD.EvnCourseTreatDrug_FactCount,0)
					else isnull(EPTD.EvnPrescrTreatDrug_FactCount,0)
				end as EvnPrescrTreatDrug_FactCount
				,EPTD.EvnPrescrTreatDrug_DoseDay
				,EPT.EvnPrescrTreat_Descr -- Комментарий
				,ECTD.EvnCourseTreatDrug_id
				,ECTD.EvnCourseTreatDrug_FactDose
				,ECTD.EvnCourseTreatDrug_FactCount
				,isnull(EvnDrug.EvnCourse_id, EPT.EvnCourse_id) as EvnCourse_id
				,isnull(EvnDrug.EvnDrug_rid, EPT.EvnPrescrTreat_rid) as EvnDrug_rid
				,isnull(EvnDrug.EvnDrug_pid, EPT.EvnPrescrTreat_pid) as EvnDrug_pid
				,isnull(EvnDrug.EvnPrescr_id, EPT.EvnPrescrTreat_id) as EvnPrescr_id
				,isnull(EvnDrug.Person_id, EPT.Person_id) as Person_id
				,isnull(EvnDrug.PersonEvn_id, EPT.PersonEvn_id) as PersonEvn_id
				,isnull(EvnDrug.Server_id, EPT.Server_id) as Server_id
				,convert(varchar(10), isnull(EvnDrug.EvnDrug_setDT, EPT.EvnPrescrTreat_setDT), 104) as EvnDrug_setDate
				,isnull(EvnDrug.EvnDrug_setTime, EPT.EvnPrescrTreat_setTime) as EvnDrug_setTime
				,isnull(cast(EvnDrug.EvnDrug_Kolvo as numeric), (EPTD.EvnPrescrTreatDrug_Kolvo * ISNULL(EPT.EvnPrescrTreat_PrescrCount, 1))) as EvnDrug_Kolvo
				,isnull(EvnDrug.EvnDrug_KolvoEd, (EPTD.EvnPrescrTreatDrug_KolvoEd * ISNULL(EPT.EvnPrescrTreat_PrescrCount, 1))) as EvnDrug_KolvoEd

				,case when EVPL.EvnVizit_id is not null then 1 else 2 end as ParentEvn_IsStac
				,convert(varchar(10), v_Evn.Evn_setDT, 104) as Evn_setDate
				,LS.LpuSection_id
				,ISNULL(LS.LpuSection_Name, '') as LpuSection_Name
				,MP.MedPersonal_id
				,ISNULL(MP.Person_Fio, '') as MedPersonal_FIO

				,DD.DocumentUcStr_id as DocumentUcStr_oid
				,DD.DocumentUcStr_Ost
				,DD.Mol_tid as Mol_id
				,DD.Mol_Name
				,DD.DrugFinance_Name
				,DD.WhsDocumentCostItemType_Name
				,ISNULL(DD.DocumentUcStr_Name, '') as DocumentUcStr_Name
			from
				v_EvnPrescrTreatDrug EPTD with (nolock)
				cross apply (
					Select top 1 * from v_EvnPrescrTreat EPT with (nolock) where EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				) EPT
				outer apply(select top 1 * from v_EvnDrug with (nolock) where EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id) EvnDrug
				left join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on ECTD.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				inner join v_Evn with (nolock) on v_Evn.Evn_id = EPT.EvnPrescrTreat_pid
				left join EvnPS EPS with (nolock) on EPS.EvnPS_id = v_Evn.Evn_id
				left join EvnSection ES with (nolock) on ES.EvnSection_id = v_Evn.Evn_id
				left join EvnVizit EVPL with (nolock) on EVPL.EvnVizit_id = v_Evn.Evn_id
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = COALESCE(ECT.LpuSection_id, ES.LpuSection_id, EVPL.LpuSection_id, EPS.LpuSection_pid)
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = COALESCE(ECT.MedPersonal_id, ES.MedPersonal_id, EVPL.MedPersonal_id, EPS.MedPersonal_pid)
				outer apply (
					select top 1
						/*isnull(DP.DrugPrep_Name, D.DrugTorg_Name) as DrugPrep_Name,*/
						D.DrugPrepFas_id,
						D.Drug_Fas,
						 DU.Mol_tid
						,DUS.Drug_id
						,DUS.DocumentUcStr_id
						,DUS.DocumentUcStr_Ost
						,DUS.DrugFinance_Name
						,DUS.WhsDocumentCostItemType_Name
						,DUS.DocumentUcStr_Ser
						,RTRIM(LTRIM((ISNULL(M.Person_SurName, '') + ' ' + ISNULL(M.Person_FirName, '') + ' ' + ISNULL(M.Person_SecName, '')))) as Mol_Name
						,cast(cast(round(isnull(DUS.DocumentUcStr_Ost, 0), 4) as numeric(16, 4)) as varchar(20)) + ', фин. '
							+ RTRIM(RTRIM(ISNULL(DUS.DrugFinance_Name, 'отсут.'))) + ', серия ' + RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
						as DocumentUcStr_Name
					from dbo.DocumentUcOst_Lite(null) DUS
						inner join rls.v_Drug D with (nolock) on DUS.Drug_id = D.Drug_id
						/*left join rls.DrugPrep DP with (nolock) on DP.DrugPrepFas_id = D.DrugPrepFas_id*/
						inner join v_DocumentUc DU with (nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
						left join v_Mol M with (nolock) on M.Mol_id = DU.Mol_tid
						inner join Contragent C with (nolock) on C.Contragent_id = DUS.Contragent_tid and C.LpuSection_id = LS.LpuSection_id
					where (D.Drug_id = isnull(EvnDrug.Drug_id, EPTD.Drug_id)
						OR D.DrugComplexMnn_id = EPTD.DrugComplexMnn_id) --не всегда работает
						-- and (M.LpuSection_id = LS.LpuSection_id or C.LpuSection_id = LS.LpuSection_id)
				) DD
			where
				EPTD.EvnPrescrTreat_id = :EvnPrescrTreat_id
			order by EPTD.EvnPrescrTreatDrug_id
		";

		$queryParams = array(
			'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']
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
	 * Загрузка списка препаратов назначения ЛС для выполнения со списанием (по складу отделения)
	 */
	function doLoadEvnDrugGrid($data) {
		$where = '';
		if(!empty($data['Storage_id'])) $where .= ' AND DD.Storage_id = :Storage_id ';
		if(!empty($data['DrugPrepFas_id'])) $where .= ' AND DD.DrugPrepFas_id = :DrugPrepFas_id ';
		$outer_apply = "";
		$fields = "";
		if(!empty($data['forAPI'])){
			$fields .= "
				,DD.DocumentUc_id
				,DD.DocumentUcStr_Price
				,DD.DocumentUcStr_Sum
				,GU.GoodsUnit_id
				,DD.GoodsUnit_bid
				,GU.GoodsPackCount_Count AS GoodsPackCount_bCount
			";
			$outer_apply .= "
				outer apply (
					select top 1
						i_gu.GoodsUnit_id,
						coalesce(i_gpc.GoodsPackCount_Count, i_gpc_m.GoodsPackCount_Count, 1) as GoodsPackCount_Count
					from
						v_GoodsUnit i_gu with (nolock)
						left join v_GoodsPackCount i_gpc with (nolock) on i_gpc.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and i_gpc.TRADENAMES_ID = Drug.DrugTorg_id -- ищем по мнн+торговое
						left join v_GoodsPackCount i_gpc_m with (nolock) on i_gpc_m.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc_m.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and i_gpc_m.TRADENAMES_ID is null -- ищем по мнн среди записей без торгового
					where
						(
							i_gu.GoodsUnit_id = ECTD.GoodsUnit_id and (
								i_gpc.GoodsPackCount_Count is not null or i_gpc_m.GoodsPackCount_Count is not null
							)
						) or (
							i_gpc.GoodsPackCount_Count is null and
							i_gpc_m.GoodsPackCount_Count is null and
							i_gu.GoodsUnit_Name = 'упаковка'
						)
					order by
						i_gu.GoodsUnit_id, i_gpc.GoodsPackCount_id, i_gpc_m.GoodsPackCount_id
				) GU
			";
		}
		
		$query = "
			select distinct
				 EvnDrug.EvnDrug_id
				,isnull(EvnDrug.Drug_id, DD.Drug_id) as Drug_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,DD.DrugPrepFas_id
				,DD.Drug_Fas
				,EPTD.EvnPrescrTreatDrug_id
				,EPTD.DrugComplexMnn_id
				-- для списания в полке берем общее число приемов в курсе
				,case when EVPL.EvnVizit_id is not null
					then ECT.EvnCourseTreat_PrescrCount
					else EPT.EvnPrescrTreat_PrescrCount
				end as EvnPrescrTreat_PrescrCount
				,case when EVPL.EvnVizit_id is not null
					then isnull(ECTD.EvnCourseTreatDrug_FactCount,0)
					else isnull(EPTD.EvnPrescrTreatDrug_FactCount,0)
				end as EvnPrescrTreatDrug_FactCount
				,EPTD.EvnPrescrTreatDrug_DoseDay
				,EPT.EvnPrescrTreat_Descr -- Комментарий
				,ECTD.EvnCourseTreatDrug_id
				,ECTD.EvnCourseTreatDrug_FactDose
				,ECTD.EvnCourseTreatDrug_FactCount
				,isnull(EvnDrug.EvnCourse_id, EPT.EvnCourse_id) as EvnCourse_id
				,isnull(EvnDrug.EvnDrug_rid, EPT.EvnPrescrTreat_rid) as EvnDrug_rid
				,isnull(EvnDrug.EvnDrug_pid, EPT.EvnPrescrTreat_pid) as EvnDrug_pid
				,isnull(EvnDrug.EvnPrescr_id, EPT.EvnPrescrTreat_id) as EvnPrescr_id
				,isnull(EvnDrug.Person_id, EPT.Person_id) as Person_id
				,isnull(EvnDrug.PersonEvn_id, EPT.PersonEvn_id) as PersonEvn_id
				,isnull(EvnDrug.Server_id, EPT.Server_id) as Server_id
				,convert(varchar(10), isnull(EvnDrug.EvnDrug_setDT, EPT.EvnPrescrTreat_setDT), 104) as EvnDrug_setDate
				,isnull(EvnDrug.EvnDrug_setTime, EPT.EvnPrescrTreat_setTime) as EvnDrug_setTime
				,isnull(cast(EvnDrug.EvnDrug_Kolvo as numeric), (EPTD.EvnPrescrTreatDrug_Kolvo * ISNULL(EPT.EvnPrescrTreat_PrescrCount, 1))) as EvnDrug_Kolvo
				,isnull(EvnDrug.EvnDrug_KolvoEd, (EPTD.EvnPrescrTreatDrug_KolvoEd * ISNULL(EPT.EvnPrescrTreat_PrescrCount, 1))) as EvnDrug_KolvoEd
				,case when EVPL.EvnVizit_id is not null then 1 else 2 end as ParentEvn_IsStac
				,convert(varchar(10), v_Evn.Evn_setDT, 104) as Evn_setDate
				,LS.Lpu_id
				,LS.LpuSection_id
				,ISNULL(LS.LpuSection_Name, '') as LpuSection_Name
				,MP.MedPersonal_id
				,ISNULL(MP.Person_Fio, '') as MedPersonal_FIO
				,DD.DocumentUcStr_id as DocumentUcStr_oid
				,DD.DocumentUcStr_Ost
				,DD.Storage_tid as Storage_id
				,DD.Mol_id
				,DD.Mol_Name
				,DD.DrugFinance_Name
				,DD.WhsDocumentCostItemType_Name
				,ISNULL(DD.DocumentUcStr_Name, '') as DocumentUcStr_Name
				{$fields}
			from
				v_EvnPrescrTreat EPT with (nolock)
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				outer apply(select top 1 * from v_EvnDrug with (nolock) where EvnPrescrTreatDrug_id = EPTD.EvnPrescrTreatDrug_id) EvnDrug
				left join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on ECTD.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				inner join v_Evn with (nolock) on v_Evn.Evn_id = EPT.EvnPrescrTreat_pid
				inner join v_Lpu with (nolock) on v_Evn.Lpu_id = v_Lpu.Lpu_id 
				left join EvnPS EPS with (nolock) on EPS.EvnPS_id = v_Evn.Evn_id
				left join EvnSection ES with (nolock) on ES.EvnSection_id = v_Evn.Evn_id
				left join EvnVizit EVPL with (nolock) on EVPL.EvnVizit_id = v_Evn.Evn_id
				inner join v_LpuSection_all LS with (nolock) on LS.LpuSection_id = COALESCE(ECT.LpuSection_id, ES.LpuSection_id, EVPL.LpuSection_id, EPS.LpuSection_pid)
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = COALESCE(ECT.MedPersonal_id, ES.MedPersonal_id, EVPL.MedPersonal_id, EPS.MedPersonal_pid)
				outer apply (
					select top 1
						D.DrugPrepFas_id
						,D.Drug_Fas
						,DU.Storage_tid
						,STOR.Storage_id
						,DUS.Drug_id
						,DUS.DocumentUcStr_id
						,isnull(ost.DocumentUcStr_Ost,0) as DocumentUcStr_Ost
						,DF.DrugFinance_Name
						,WDCIT.WhsDocumentCostItemType_Name
						,DUS.DocumentUcStr_Ser
						,M.Mol_id
						,case
							when M.MedPersonal_id is not null then MP.Person_Fio
							else RTRIM(LTRIM((ISNULL(M.Person_SurName, '') + ' ' + ISNULL(M.Person_FirName, '') + ' ' + ISNULL(M.Person_SecName, ''))))
						end as Mol_Name
						,cast(cast(round(isnull(ost.DocumentUcStr_Ost, 0), 4) as numeric(16, 4)) as varchar(20)) + ', фин. '+ RTRIM(RTRIM(ISNULL(DF.DrugFinance_Name, 'отсут.'))) + ', серия ' + RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
						as DocumentUcStr_Name
						,DU.DocumentUc_id
						,DUS.DocumentUcStr_Price
						,DUS.DocumentUcStr_Sum
						,DUS.GoodsUnit_bid
					from
						v_DrugShipmentLink DSL with(nolock)
						inner join v_DrugOstatRegistry DOR with(nolock) on DOR.DrugShipment_id = DSL.DrugShipment_id
						inner join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
						inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = DSL.DocumentUcStr_id
						inner join v_DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
						left join v_Storage STOR with (nolock) on STOR.Storage_id = DOR.Storage_id
						left join v_Mol M with(nolock) on M.Mol_id = DU.Mol_tid and M.Storage_id = DU.Storage_tid
						outer apply (
							select top 1 Person_Fio
							from v_MedPersonal with(nolock)
							where MedPersonal_id = M.MedPersonal_id
						) MP
						left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = DU.DrugFinance_id
						left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = DU.WhsDocumentCostItemType_id
						outer apply (
							select top 1 isnull(sum(rED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
							from v_EvnDrug rED with(nolock)
							inner join v_DocumentUcStr rDUS with(nolock) on rDUS.DocumentUcStr_id = rED.DocumentUcStr_id
							inner join v_DocumentUc rDU with(nolock) on rDU.DocumentUc_id = rDUS.DocumentUc_id
							inner join v_DrugShipmentLink oDSL with(nolock) on oDSL.DocumentUcStr_id = rED.DocumentUcStr_oid
							where oDSL.DrugShipment_id = DSL.DrugShipment_id and isnull(rDU.DrugDocumentStatus_id,1) = 1 --новый
						) rl
						outer apply (
							select top 1
								(DOR.DrugOstatRegistry_Kolvo-isnull(rl.EvnDrug_Kolvo,0)) as DocumentUcStr_Ost
						) ost
					where  1=1
						and (D.Drug_id = isnull(EvnDrug.Drug_id, EPTD.Drug_id)
						OR D.DrugComplexMnn_id = EPTD.DrugComplexMnn_id) --не всегда работает
						and DOR.Org_id = v_Lpu.Org_id
						and STOR.Storage_id in (
			                select
			                    SSL.Storage_id
			                from
			                    v_StorageStructLevel SSL with(nolock)
			                    left join v_MedService MS with(nolock) on MS.MedService_id = SSL.MedService_id
			                where
			                    SSL.LpuSection_id = LS.LpuSection_id or MS.LpuSection_id = LS.LpuSection_id
			            )
				) DD
				{$outer_apply}
			where
				EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id
				{$where}
			order by EPTD.EvnPrescrTreatDrug_id
		";

		$queryParams = array(
			'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']
		);
		if(!empty($data['Storage_id'])) $queryParams['Storage_id'] = $data['Storage_id'];
		if(!empty($data['DrugPrepFas_id'])) $queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];

		//echo getDebugSQL($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Возвращает DrugComplexMnn_id по ActMatters_id (один ActMatters_id может быть для нескольких DrugComplexMnn_id c разными дозировками и формами выпуска)
	 */
	function getDrugComplexMnnByActMatters($data) {
		$query = '
			select top 1 mnn.DrugComplexMnn_id
			from rls.v_DrugComplexMnn mnn with (nolock)
			where exists (
				select top 1 MnnName.DrugComplexMnnName_id from rls.v_DrugComplexMnnName MnnName with (nolock)
				where MnnName.DrugComplexMnnName_id = mnn.DrugComplexMnnName_id
					and MnnName.ActMatters_id = :ActMatters_id
			)
			order by mnn.DrugComplexMnnDose_id desc, mnn.DrugComplexMnnFas_id desc, mnn.DrugComplexMnnGroupType_id desc
		';
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка запроса МНН по действующему веществу'));
		}
	}

	/**
	 * Возвращает данные для шаблона print_evnprescrtreat_list
	 * Спецмаркер #СписокНазначенийЛекарственноеЛечение
	 */
	function getPrintData($data) {
		$query = "
			select distinct
				EP.EvnPrescrTreat_id
				,convert(varchar(10),EP.EvnPrescrTreat_setDate,104) as EvnPrescr_setDate
				,EP.EvnPrescrTreat_Descr as Descr
				,EP.EvnPrescrTreat_IsCito as IsCito
				,ECT.EvnCourseTreat_ContReception as ContReception
				,ECT.EvnCourseTreat_MaxCountDay as CountInDay
				,ECT.EvnCourseTreat_Duration as CourseDuration
				,ECT.EvnCourseTreat_Interval as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
				,EPTD.EvnPrescrTreatDrug_KolvoEd as EvnPrescrTreatDrug_KolvoEd
				,EPTD.EvnPrescrTreatDrug_Kolvo as EvnPrescrTreatDrug_Kolvo
				,ECTD.EvnCourseTreatDrug_MaxDoseDay as EvnCourseTreatDrug_MaxDoseDay
				,ECTD.EvnCourseTreatDrug_PrescrDose as EvnCourseTreatDrug_PrescrDose
				,ECTD.EvnCourseTreatDrug_MinDoseDay as EvnCourseTreatDrug_MinDoseDay
				,ECTD.EvnCourseTreatDrug_FactDose as EvnCourseTreatDrug_FactDose
				,coalesce(CUBICUNITS.SHORTNAME, MASSUNITS.SHORTNAME, ACTUNITS.SHORTNAME, '') as Okei_NationSymbol
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
				,coalesce(dcm.DrugComplexMnn_RusName, Drug.Drug_Name, '') as Drug_Name
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
				,(select count(EvnPrescrTreatDrug_id) from v_EvnPrescrTreatDrug with (nolock) where EvnPrescrTreat_id = EP.EvnPrescrTreat_id) as cntDrug
				,GUS.GoodsUnit_Name as GoodsUnitS_Name
				,ERG.EvnReceptGeneral_Ser
				,ERG.EvnReceptGeneral_Num
				,convert(varchar(10), ERG.EvnReceptGeneral_begDate, 104) as EvnReceptGeneral_begDate
				,RT.ReceptType_Name
			from v_EvnPrescrTreat EP with (nolock)
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EP.EvnPrescrTreat_id
				inner join v_EvnCourseTreat ECT with (nolock) on EP.EvnCourse_id = ECT.EvnCourseTreat_id
				inner join v_EvnCourseTreatDrug ECTD with (nolock) on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join PerformanceType PFT with (nolock) on  ECT.PerformanceType_id = PFT.PerformanceType_id
				left join rls.CUBICUNITS with (nolock) on EPTD.CUBICUNITS_ID = CUBICUNITS.CUBICUNITS_ID
				left join rls.MASSUNITS with (nolock) on EPTD.MASSUNITS_ID = MASSUNITS.MASSUNITS_ID
				left join rls.ACTUNITS with (nolock) on EPTD.ACTUNITS_ID = ACTUNITS.ACTUNITS_ID
				left join PrescriptionIntroType PIT with (nolock) on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join DrugTorg with (nolock) on DrugTorg.DrugTorg_id = Drug.DrugTorg_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join DurationType DTP with (nolock) on ECT.DurationType_id = DTP.DurationType_id
				left join DurationType DTN with (nolock) on ECT.DurationType_recid = DTN.DurationType_id
				left join DurationType DTI with (nolock) on ECT.DurationType_intid = DTI.DurationType_id
				left join v_GoodsUnit GUS with(nolock) on GUS.GoodsUnit_id = ECTD.GoodsUnit_sid
				left join v_EvnReceptGeneralDrugLink ERGDL with(nolock) on ERGDL.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join v_EvnReceptGeneral ERG with(nolock) on ERG.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id
				left join v_ReceptType RT with(nolock) on RT.ReceptType_id = ERG.ReceptType_id
			where
				EP.EvnPrescrTreat_pid = :Evn_pid  and EP.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescrTreat_id
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$tmp = $result->result('array');
			$cnt = 0;
			foreach($tmp as $row) {
				if($cnt == 0)
				{
					$drug_list = array();
				}

				//$drug_list[] = '<span style="font-weight: bold; font-size: 10px;">'.$row['Drug_Name'].'</span>';
				$drug_list[] = $row['Drug_Name'];
				$cnt++;

				if($cnt == $row['cntDrug'])
				{
					$drugform_nick = '';
					if(!empty($row['DrugForm_Name']))
					{
						$row['DrugForm_Name'] = strtolower($row['DrugForm_Name']);
						if(strpos($row['DrugForm_Name'], 'капл') !== false)
						{
							$drugform_nick = 'капли';
						}
						if(strpos($row['DrugForm_Name'], 'капс') !== false)
						{
							$drugform_nick = 'капс.';
						}
						if(strpos($row['DrugForm_Name'], 'супп') !== false)
						{
							$drugform_nick = 'супп.';
						}
						if(strpos($row['DrugForm_Name'], 'табл') !== false)
						{
							$drugform_nick = 'табл.';
						}
					}
					$response[]=array(
						'Drug_Info' => implode(', ',$drug_list)
					,'EvnPrescrTreatDrug_KolvoEd' => round($row['EvnPrescrTreatDrug_KolvoEd'], 5)
					,'EvnPrescrTreatDrug_Kolvo' => round($row['EvnPrescrTreatDrug_Kolvo'], 5)
					,'Okei_NationSymbol' => $row['Okei_NationSymbol']
					,'EvnCourseTreatDrug_MaxDoseDay' => $row['EvnCourseTreatDrug_MaxDoseDay']
					,'EvnCourseTreatDrug_PrescrDose' => $row['EvnCourseTreatDrug_PrescrDose']
					,'EvnCourseTreatDrug_MinDoseDay' => $row['EvnCourseTreatDrug_MinDoseDay']
					,'EvnCourseTreatDrug_FactDose' => $row['EvnCourseTreatDrug_FactDose']
					,'PrescriptionIntroType_Name' => $row['PrescriptionIntroType_Name']
					,'PerformanceType_Name' => $row['PerformanceType_Name']
					,'DrugForm_Nick' => $drugform_nick
					,'EvnPrescr_setDate' => $row['EvnPrescr_setDate']
					,'Descr' => $row['Descr']
					,'IsCito' => $row['IsCito']
					,'ContReception' => $row['ContReception']
					,'CountInDay' => $row['CountInDay']
					,'CourseDuration' => $row['CourseDuration']
					,'Interval' => $row['Interval']
					,'DurationTypeP_Nick' => $row['DurationTypeP_Nick']
					,'DurationTypeN_Nick' => $row['DurationTypeN_Nick']
					,'DurationTypeI_Nick' => $row['DurationTypeI_Nick']
					,'GoodsUnitS_Name' => $row['GoodsUnitS_Name']
					,'EvnReceptGeneral_Ser' => $row['EvnReceptGeneral_Ser']
					,'EvnReceptGeneral_Num' => $row['EvnReceptGeneral_Num']
					,'EvnReceptGeneral_begDate' => $row['EvnReceptGeneral_begDate']
					,'ReceptType_Name' => $row['ReceptType_Name']
					);
					$cnt = 0;
				}
			}
		}
		return $response;
	}

	/**
	 * Обработка после отмены назначения
	 */
	function onAfterCancel($data)
	{
		if (!empty($data['EvnCourse_id'])) {
			$this->_dataForRecountEvnCourse = array();
			$this->_dataForRecountEvnCourse['pmUser_id'] = $data['pmUser_id'];
			$this->_dataForRecountEvnCourse['typeChanged'] = 'cancelEvnPrescrTreat';
			$this->_dataForRecountEvnCourse['isCancelEvnCourse'] = empty($data['isCancelEvnCourse'])?0:1;
			$tmp = $this->doLoadEvnCourseTreatEditForm(array('EvnCourseTreat_id'=>$data['EvnCourse_id']));
			if(empty($tmp)) {
				throw new Exception('Ошибка при получении данных курса', 500);
			}
			array_walk($tmp[0], 'ConvertFromUTF8ToWin1251');
			$this->_dataForRecountEvnCourse['EvnCourseData'] = $tmp[0];
			$this->_dataForRecountEvnCourse['EvnCourseData']['DrugListData'] = $this->_processingDrugListData($this->_dataForRecountEvnCourse['EvnCourseData'], 'EvnCourseTreatDrug', false);
			$this->_recountEvnCourse();
		}
	}

	/**
	 * Обновление данных о фактическом выполнении назначения
	 * @param array $data
	 * @param string $case
	 * @return array
	 * @throws Exception
	 * @todo Сделать обновление курсовой фактической дозы
	 */
	function updateFactData($data, $case = 'upd')
	{
		if (empty($data['EvnPrescrTreat_id'])) {
			throw new Exception('Не указан идетификатор назначения лек.лечения',500);
		}
		if (empty($data['EvnPrescrTreatDrug_id'])) {
			throw new Exception('Не указан идетификатор медикамента назначения',500);
		}
		$isExecEvnPrescr = false;
		//получаем список медикаментов в назначении
		$EvnPrescrData = $this->doLoadEvnDrugGrid($data);
		$EvnPrescrTreatDrug_FactCount = 0;
		$EvnCourseTreatDrug_FactCount = 0;
		$epFactCount = 0;//EvnPrescrTreatDrug_FactCount
		$EvnCourseTreatDrug_FactDose = null;
		$PrescrFactCountDiff = null;
		$cntEvnDrug = 0;
		$cntEvnPrescrTreatDrug = 0;
		$isSetDrugData = false;
		if (is_array($EvnPrescrData)) {
			$cntEvnPrescrTreatDrug = count($EvnPrescrData);
			foreach ($EvnPrescrData as $row) {
				if (!empty($row['EvnDrug_id'])) {
					// подсчитываем число списаний для проверки, что для каждого медикамента есть хотя бы одинслучай списания
					$cntEvnDrug++;
				}
				if (!empty($row['EvnPrescrTreatDrug_FactCount'])) {
					// подсчитываем общее число выполн.приемов в назначении
					$epFactCount += $row['EvnPrescrTreatDrug_FactCount'];
				}
				if ($row['EvnPrescrTreatDrug_id'] != $data['EvnPrescrTreatDrug_id']) {
					continue;
				}
				$EvnPrescrTreatDrug_FactCount = $row['EvnPrescrTreatDrug_FactCount'];
				$EvnCourseTreatDrug_FactCount = $row['EvnCourseTreatDrug_FactCount'];
				$EvnCourseTreatDrug_FactDose = $row['EvnCourseTreatDrug_FactDose'];
				$PrescrFactCountDiff = $row['EvnPrescrTreat_PrescrCount'] - $EvnPrescrTreatDrug_FactCount;
				if (empty($data['EvnCourseTreat_id'])) {
					$data['EvnCourseTreat_id'] = $row['EvnCourse_id'];
				}
				if (empty($data['EvnCourseTreatDrug_id'])) {
					$data['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
				}
				$isSetDrugData = true;
			}
		}
		if (!$isSetDrugData) {
			throw new Exception('Не удалось получить данные медикамента назначения',500);
		}

		switch ($case) {
			case 'upd':
				if ($PrescrFactCountDiff == 0) {
					throw new Exception('Число невыполненных приемов равно нулю, перерасчет не произведен!',500);
				}
				//Если поле «Списать приемов» не больше чем невыполненных приемов в выбранном назначении
				if ($data['EvnPrescrTreat_Fact'] <= $PrescrFactCountDiff) {
					$EvnPrescrTreatDrug_FactCount += $data['EvnPrescrTreat_Fact'];
					$EvnCourseTreatDrug_FactCount += $data['EvnPrescrTreat_Fact'];
					$epFactCount +=  $data['EvnPrescrTreat_Fact'];
				} else {
					throw new Exception('В поле «Списать приемов» указано число больше чем число невыполненных приемов',400);
				}
				if (($epFactCount/$cntEvnPrescrTreatDrug) == $EvnPrescrData[0]['EvnPrescrTreat_PrescrCount']) {
					//это условие должно выполняться у всех медикаментов назначения
					$isExecEvnPrescr = true;
				}
				break;
			case 'cancel':
				$EvnPrescrTreatDrug_FactCount -= $data['EvnPrescrTreat_Fact'];
				$EvnCourseTreatDrug_FactCount -= $data['EvnPrescrTreat_Fact'];
				if ($EvnPrescrTreatDrug_FactCount < 0 || $EvnCourseTreatDrug_FactCount < 0) {
					throw new Exception('В поле «Отменить приемов» указано число больше чем число выполненных приемов в курсе или назначении!',400);
					//$EvnPrescrTreatDrug_FactCount = 0;
					//$EvnCourseTreatDrug_FactCount = 0;
				}
				break;
			default:
				throw new Exception('Неправильный второй аргумент метода EvnPrescrTreat_model::updateFactData',500);
		}
		// сохраняем
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnPrescrTreat_updfact
				@EvnPrescrTreat_id = :EvnPrescrTreat_id,
				@EvnPrescrTreatDrug_id = :EvnPrescrTreatDrug_id,
				@EvnPrescrTreatDrug_FactCount = :EvnPrescrTreatDrug_FactCount,
				@EvnCourseTreat_id = :EvnCourseTreat_id,
				@EvnCourseTreatDrug_id = :EvnCourseTreatDrug_id,
				@EvnCourseTreatDrug_FactCount = :EvnCourseTreatDrug_FactCount,
				@EvnCourseTreatDrug_FactDose = :EvnCourseTreatDrug_FactDose,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id'],
			'EvnPrescrTreatDrug_id' => $data['EvnPrescrTreatDrug_id'],
			'EvnPrescrTreatDrug_FactCount' => $EvnPrescrTreatDrug_FactCount,
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'EvnCourseTreatDrug_id' => $data['EvnCourseTreatDrug_id'],
			'EvnCourseTreatDrug_FactCount' => $EvnCourseTreatDrug_FactCount,
			'EvnCourseTreatDrug_FactDose' => $EvnCourseTreatDrug_FactDose,
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса при обновлении фактических данных', 500);
		}
		$res = $result->result('array');
		if(empty($res)) {
			throw new Exception('Ошибка при сохранении медикамента', 500);
		}
		if (!empty($res[0]['Error_Msg'])) {
			throw new Exception($res[0]['Error_Msg'], 500);
		}
		return array(
			'EvnPrescrTreat_PrescrCount'=>$EvnPrescrData[0]['EvnPrescrTreat_PrescrCount'],
			'epFactCount'=>$epFactCount,
			'cntEvnPrescrTreatDrug'=>$cntEvnPrescrTreatDrug,
			'isExecEvnPrescr'=>$isExecEvnPrescr,
			'EvnPrescrTreatDrug_FactCount'=>$EvnPrescrTreatDrug_FactCount,
			'EvnCourseTreatDrug_FactCount'=>$EvnCourseTreatDrug_FactCount,
			'EvnCourseTreatDrug_FactDose'=>$EvnCourseTreatDrug_FactDose,
		);
	}

	/**
	 * Получение данных для комбика "Назначение" формы списания
	 */
	public function doOldLoadEvnPrescrTreatDrugCombo($data) {
		$queryParams = array();
		$where_clause = '';

		if (!empty($data['EvnPrescrTreat_id'])) {
			$queryParams = array(
				'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']
			);
			$where_clause = 'EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id';// and DD.Drug_id is not null
		}

		if (!empty($data['EvnPrescrTreat_pid'])) {
			$queryParams = array(
				'EvnPrescrTreat_pid' => $data['EvnPrescrTreat_pid']
			);
			$where_clause = 'EPT.EvnPrescrTreat_pid = :EvnPrescrTreat_pid';// and DD.Drug_id is not null
		}

		if (!empty($data['EvnPrescrTreatDrug_id'])) {
			$queryParams = array(
				'EvnPrescrTreatDrug_id' => $data['EvnPrescrTreatDrug_id']
			);
			$where_clause = 'EPTD.EvnPrescrTreatDrug_id = :EvnPrescrTreatDrug_id';
		}

		if (empty($where_clause)) {
			return array();
		}

		$query = "
			select
				EPTD.EvnPrescrTreatDrug_id,
				EPTD.EvnCourseTreatDrug_id,
				EPT.EvnPrescrTreat_id,
				EPT.EvnPrescrTreat_pid,
				EPT.EvnCourse_id,
				convert(varchar(10), EPT.EvnPrescrTreat_setDT, 104) as EvnPrescrTreat_setDate,
				-- для списания в полке берем общее число приемов в курсе
				case when EVPL.EvnVizitPL_id is not null
					then ECT.EvnCourseTreat_PrescrCount
					else EPT.EvnPrescrTreat_PrescrCount
				end as EvnPrescrTreat_PrescrCount,
				case when EVPL.EvnVizitPL_id is not null
					then isnull(ECTD.EvnCourseTreatDrug_FactCount,0)
					else isnull(EPTD.EvnPrescrTreatDrug_FactCount,0)
				end as EvnPrescrTreatDrug_FactCount,

				EPTD.EvnPrescrTreatDrug_DoseDay,
				EPTD.EvnPrescrTreatDrug_KolvoEd,
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name,

				DD.Drug_id,
				coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name,
				DD.DrugPrepFas_id,
				DD.Drug_Fas,

				DD.DocumentUcStr_id as DocumentUcStr_oid,
				DD.DocumentUcStr_Ost,
				DD.Mol_tid as Mol_id,
				DD.Mol_Name,
				DD.DrugFinance_Name,
				DD.WhsDocumentCostItemType_Name,
				ISNULL(DD.DocumentUcStr_Name, '') as DocumentUcStr_Name
			from
				v_EvnPrescrTreat EPT with (nolock)
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				left join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on ECTD.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EPT.EvnPrescrTreat_pid
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EPT.EvnPrescrTreat_pid
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EPT.EvnPrescrTreat_pid

				outer apply (
					select top 1
						/*isnull(DP.DrugPrep_Name, D.DrugTorg_Name) as DrugPrep_Name,*/
						D.DrugPrepFas_id,
						D.Drug_Fas,
						 DU.Mol_tid
						,DUS.Drug_id
						,DUS.DocumentUcStr_id
						,DUS.DocumentUcStr_Ost
						,DUS.DrugFinance_Name
						,DUS.WhsDocumentCostItemType_Name
						,DUS.DocumentUcStr_Ser
						,RTRIM(LTRIM((ISNULL(M.Person_SurName, '') + ' ' + ISNULL(M.Person_FirName, '') + ' ' + ISNULL(M.Person_SecName, '')))) as Mol_Name
						,cast(cast(round(isnull(DUS.DocumentUcStr_Ost, 0), 4) as numeric(16, 4)) as varchar(20)) + ', фин. '
							+ RTRIM(RTRIM(ISNULL(DUS.DrugFinance_Name, 'отсут.'))) + ', серия ' + RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
						as DocumentUcStr_Name
					from dbo.DocumentUcOst_Lite(null) DUS
						inner join rls.v_Drug D with (nolock) on DUS.Drug_id = D.Drug_id
						/*left join rls.DrugPrep DP with (nolock) on DP.DrugPrepFas_id = D.DrugPrepFas_id*/
						inner join v_DocumentUc DU with (nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
						inner join v_Mol M with (nolock) on M.Mol_id = DU.Mol_tid
							and M.LpuSection_id = COALESCE(ES.LpuSection_id, EVPL.LpuSection_id, EPS.LpuSection_pid)
					where
						D.DrugComplexMnn_id = dcm.DrugComplexMnn_id -- поиск остатков реализован только по комплексному МНН в рамках оптимизации по задаче 147834 
				) DD
			where
				{$where_clause}
		";

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		$response = array();
		foreach ($result as $row) {
			$drug = array();
			$drug['EvnPrescrTreatDrug_id'] = $row['EvnPrescrTreatDrug_id'];
			$drug['EvnCourse_id'] = $row['EvnCourse_id'];
			$drug['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
			$drug['EvnPrescrTreat_id'] = $row['EvnPrescrTreat_id'];
			$drug['EvnPrescrTreat_pid'] = $row['EvnPrescrTreat_pid'];
			$drug['EvnPrescrTreat_setDate'] = $row['EvnPrescrTreat_setDate'];
			$drug['EvnPrescrTreat_PrescrCount'] = $row['EvnPrescrTreat_PrescrCount'];
			$drug['EvnPrescrTreatDrug_FactCount'] = $row['EvnPrescrTreatDrug_FactCount'];
			$drug['EvnPrescrTreatDrug_DoseDay'] = $row['EvnPrescrTreatDrug_DoseDay'];
			$drug['PrescrFactCountDiff'] = $row['EvnPrescrTreat_PrescrCount']-$row['EvnPrescrTreatDrug_FactCount'];
			$drug['EvnPrescrTreat_Fact'] = $drug['PrescrFactCountDiff'];
			$drug['Drug_id'] = $row['Drug_id'];
			$drug['Drug_Name'] = $row['Drug_Name'];
			$drug['DrugForm_Name'] = $row['DrugForm_Name'];
			//$drug['DrugUnit_Name'] = $row['DrugUnit_Name'];
			$drug['DrugPrepFas_id'] = $row['DrugPrepFas_id'];
			$drug['DocumentUcStr_oid'] = $row['DocumentUcStr_oid'];
			$drug['DocumentUcStr_Ost'] = $row['DocumentUcStr_Ost'];
			$drug['DocumentUcStr_Name'] = $row['DocumentUcStr_Name'];
			$drug['Mol_id'] = $row['Mol_id'];
			$drug['Mol_Name'] = $row['Mol_Name'];
			$drug['DrugFinance_Name'] = $row['DrugFinance_Name'];
			$drug['WhsDocumentCostItemType_Name'] = $row['WhsDocumentCostItemType_Name'];

			//Назначенное кол-во (ед. доз.)
			$kolvoEd = $row['EvnPrescrTreatDrug_KolvoEd'];

			// кол-во в упаковке
			if (empty($row['Drug_Fas'])) $row['Drug_Fas'] = 1;
			$drug['Drug_Fas'] = $row['Drug_Fas'];

			//Остаток (ед. доз.)
			$edCount = 0;
			if (!empty($row['DocumentUcStr_Ost'])) {
				$edCount = $row['DocumentUcStr_Ost']*$row['Drug_Fas'];
			}
			if ($edCount < $kolvoEd) {
				//если на остатках меньше назначенного кол-ва ед. доз.
				$kolvoEd = $edCount;
			}

			if (empty($kolvoEd)) {
				// Кол-во (ед. доз.)
				$drug['EvnDrug_KolvoEd'] = null;
				// Количество (ед. уч.)
				$drug['EvnDrug_Kolvo'] = null;
			} else {
				// Кол-во (ед. доз.)
				$drug['EvnDrug_KolvoEd'] = $kolvoEd;
				// Количество (ед. уч.)
				$drug['EvnDrug_Kolvo'] = $kolvoEd/$row['Drug_Fas'];
			}
			$response[] = $drug;
		}
		return $response;
	}

	/**
	 * Получение данных для комбика "Назначение" формы списания
	 */
	public function doLoadEvnPrescrTreatDrugCombo($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$queryParams = array();
		$where_clause = '';
		$dor_where = '';
		$outer_off = '';
		$join = '';
		$fldTime = '';
		$ch = '"';
		
		if ($_SESSION['region']['nick'] == 'ufa') {
			//  Для Уфы делаем изменения в запросе
			$outer_off = ' and 1 > 2 '; // // Если Уфа - убираем
			$dor_where = ' and dor.SubAccountType_id = 1';
		}
		
		if (in_array($this->getRegionNick(),array('ufa','vologda'))) {
			$prefix = $this->getRegionNick() == 'ufa'
				? 'r2'
				: 'r35';
			$join = "left join {$prefix}.fn_GoodsPackCount ( EPTD.DrugComplexMnn_id) i_gpc_m  with (nolock) on i_gpc_m.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc_m.DrugComplexMnn_id = EPTD.DrugComplexMnn_id  -- ищем по мнн среди записей без торгового
					";
			$fldTime = ", '[ ' + 
							(SElect '{ idx:' + convert(varchar, CourseTimeIntake_idx) + ',' + ' time:{$ch}' + convert(varchar, CourseTimeIntake_time) + '{$ch} },'	
									from {$prefix}.v_CourseTimeIntake ect with (nolock)
											where EvnCourse_id = EPT.EvnCourse_id 
												and EvnDrug_id is null
												and not exists(SElect 1 from {$prefix}.v_CourseTimeIntake ect2 with(nolock)
													where ect2.evnPrescr_id = EPTD.EvnPrescrTreat_id 
														and ect2.DrugComplexMnn_id =  EPTD.DrugComplexMnn_id
														and ect2.CourseTimeIntake_idx =  ect.CourseTimeIntake_idx)
										for xml path(''))  
					+ ']'  EvnPrescrTreat_Time	
					";
		}
		else {
			$join = "left join v_GoodsPackCount i_gpc_m with (nolock) on i_gpc_m.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc_m.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and i_gpc_m.TRADENAMES_ID is null -- ищем по мнн среди записей без торгового
						";
		}

		if (!empty($data['EvnPrescrTreat_id'])) {
			$queryParams = array(
				'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']
			);
			$where_clause = 'EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id';// and DD.Drug_id is not null
		}

		if (!empty($data['EvnPrescrTreat_pid'])) {
			$queryParams = array(
				'EvnPrescrTreat_pid' => $data['EvnPrescrTreat_pid']
			);
			$where_clause = 'EPT.EvnPrescrTreat_pid = :EvnPrescrTreat_pid';// and DD.Drug_id is not null
		}

		if (!empty($data['EvnPrescrTreatDrug_id'])) {
			$queryParams = array(
				'EvnPrescrTreatDrug_id' => $data['EvnPrescrTreatDrug_id']
			);
			$where_clause = 'EPTD.EvnPrescrTreatDrug_id = :EvnPrescrTreatDrug_id';
		}

		if (empty($where_clause)) {
			return array();
		}

		if (!empty($data['EvnPrescrTreat_setDate'])) {
			$queryParams['EvnPrescrTreat_setDate'] = $data['EvnPrescrTreat_setDate'];
			$where_clause = 'EPT.EvnPrescrTreat_setDT = :EvnPrescrTreat_setDate';
		}

		$queryParams['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();	
		
		$query = "
			select
				EPTD.EvnPrescrTreatDrug_id,
				EPTD.EvnCourseTreatDrug_id,
				EPT.EvnPrescrTreat_id,
				EPT.EvnPrescrTreat_pid,
				EPT.EvnCourse_id,
				convert(varchar(10), EPT.EvnPrescrTreat_setDT, 104) as EvnPrescrTreat_setDate,
				-- для списания в полке берем общее число приемов в курсе
				case when EVPL.EvnVizitPL_id is not null
					then ECT.EvnCourseTreat_PrescrCount
					else EPT.EvnPrescrTreat_PrescrCount
				end as EvnPrescrTreat_PrescrCount,
				case when EVPL.EvnVizitPL_id is not null
					then isnull(ECTD.EvnCourseTreatDrug_FactCount,0)
					else isnull(EPTD.EvnPrescrTreatDrug_FactCount,0)
				end as EvnPrescrTreatDrug_FactCount,
				LS.LpuSection_id,
				EPTD.EvnPrescrTreatDrug_DoseDay,
				EPTD.EvnPrescrTreatDrug_KolvoEd,
				EPTD.EvnPrescrTreatDrug_Kolvo,
				coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name,

				DD.Drug_id,
				coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name,
				DD.DrugPrepFas_id,
				isnull(DD.Drug_Fas, 1) Drug_Fas,
				DD.DocumentUcStr_id as DocumentUcStr_oid,
				DD.DocumentUcStr_Ost,
				DD.Storage_tid as Storage_id,
				DD.Mol_id,
				DD.Mol_Name,
				DD.DrugFinance_Name,
				DD.WhsDocumentCostItemType_Name,
				ISNULL(DD.DocumentUcStr_Name, '') as DocumentUcStr_Name,
				GU.GoodsUnit_id as GoodsUnit_id,
				ECTD.GoodsUnit_sid,
				GU.GoodsPackCount_Count as GoodsPackCount_Count,
				isnull(GU_S.GoodsPackCount_Count, 1) as GoodsPackCount_sCount
				{$fldTime}
			from
				v_EvnPrescrTreat EPT with (nolock)
				inner join v_EvnPrescrTreatDrug EPTD with (nolock) on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				left join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on ECTD.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EPT.EvnPrescrTreat_pid
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EPT.EvnPrescrTreat_pid
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = EPT.EvnPrescrTreat_pid
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = coalesce(ES.LpuSection_id, EVPL.LpuSection_id, EPS.LpuSection_pid)

				outer apply (
					select top 1
						D.DrugPrepFas_id
						,D.Drug_Fas
						,DU.Storage_tid
						,DUS.Drug_id
						,DUS.DocumentUcStr_id
						,isnull(ost.DocumentUcStr_Ost,0) as DocumentUcStr_Ost
						,DF.DrugFinance_Name
						,WDCIT.WhsDocumentCostItemType_Name
						,DUS.DocumentUcStr_Ser
						,M.Mol_id
						,case
							when M.MedPersonal_id is not null then MP.Person_Fio
							else RTRIM(LTRIM((ISNULL(M.Person_SurName, '') + ' ' + ISNULL(M.Person_FirName, '') + ' ' + ISNULL(M.Person_SecName, ''))))
						end as Mol_Name
						,cast(cast(round(isnull(ost.DocumentUcStr_Ost, 0), 4) as numeric(16, 4)) as varchar(20)) + ', фин. '+ RTRIM(RTRIM(ISNULL(DF.DrugFinance_Name, 'отсут.'))) + ', серия ' + RTRIM(ISNULL(DUS.DocumentUcStr_Ser, ''))
						as DocumentUcStr_Name
					from
						v_DrugShipmentLink DSL with(nolock)
						inner join v_DrugOstatRegistry DOR with(nolock) on DOR.DrugShipment_id = DSL.DrugShipment_id
						{$dor_where}
						inner join rls.v_Drug D with(nolock) on D.Drug_id = DOR.Drug_id
						inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = DSL.DocumentUcStr_id
						inner join v_DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
						cross apply (
							select top 1
								SSL.Storage_id,
								SSL.LpuSection_id,
								MST.MedServiceType_SysNick
							from
							    v_StorageStructLevel SSL with(nolock)
                                left join v_MedService MS with(nolock) on MS.MedService_id = SSL.MedService_id
                                left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
							where
							    SSL.Storage_id = DOR.Storage_id
							    and SSL.LpuSection_id = LS.LpuSection_id
						) S
						left join v_Mol M with(nolock) on M.Mol_id = DU.Mol_tid and M.Storage_id = DOR.Storage_id
						outer apply (
							select top 1 Person_Fio
							from v_MedPersonal with(nolock)
							where MedPersonal_id = M.MedPersonal_id
						) MP
						left join v_DrugFinance DF with(nolock) on DF.DrugFinance_id = DU.DrugFinance_id
						left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = DU.WhsDocumentCostItemType_id
						outer apply (
							select top 1 isnull(sum(rED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
							from v_EvnDrug rED with(nolock)
							inner join v_DocumentUcStr rDUS with(nolock) on rDUS.DocumentUcStr_id = rED.DocumentUcStr_id
							inner join v_DocumentUc rDU with(nolock) on rDU.DocumentUc_id = rDUS.DocumentUc_id
							inner join v_DrugShipmentLink oDSL with(nolock) on oDSL.DocumentUcStr_id = rED.DocumentUcStr_oid
							where oDSL.DrugShipment_id = DSL.DrugShipment_id and isnull(rDU.DrugDocumentStatus_id,1) = 1 --новый
							{$outer_off}
						) rl
						outer apply (
							select top 1
								(DOR.DrugOstatRegistry_Kolvo-isnull(rl.EvnDrug_Kolvo,0)) as DocumentUcStr_Ost
						) ost
						outer apply (
							select
								(
									(case when S.MedServiceType_SysNick like 'merch' then 10 else 0 end) +
                                    (case when D.Drug_id = EPTD.Drug_id then 1 else 0 end)

								) as idx
						) ord
					where  1=1
						and (D.Drug_id = EPTD.Drug_id
						OR D.DrugComplexMnn_id = EPTD.DrugComplexMnn_id) --не всегда работает
						and S.Storage_id is not null
						-- #111558
						and DOR.DrugOstatRegistry_Kolvo > 0  
						and DOR.DrugOstatRegistry_Kolvo > isnull(rl.EvnDrug_Kolvo,0)
					order by
						ord.idx desc
				) DD
				outer apply (
					select top 1
						i_gu.GoodsUnit_id,
						coalesce(i_gpc.GoodsPackCount_Count, i_gpc_m.GoodsPackCount_Count, 1) as GoodsPackCount_Count
					from
						v_GoodsUnit i_gu with (nolock)
						left join v_GoodsPackCount i_gpc with (nolock) on i_gpc.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and i_gpc.TRADENAMES_ID = Drug.DrugTorg_id -- ищем по мнн+торговое
						--left join v_GoodsPackCount i_gpc_m with (nolock) on i_gpc_m.GoodsUnit_id = i_gu.GoodsUnit_id and i_gpc_m.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and i_gpc_m.TRADENAMES_ID is null -- ищем по мнн среди записей без торгового
						{$join}
					where
						(
							i_gu.GoodsUnit_id = ECTD.GoodsUnit_id and (
								i_gpc.GoodsPackCount_Count is not null or i_gpc_m.GoodsPackCount_Count is not null
							)
						) or (
							i_gpc.GoodsPackCount_Count is null and
							i_gpc_m.GoodsPackCount_Count is null and
							i_gu.GoodsUnit_Name = 'упаковка'
						)
					order by
						i_gu.GoodsUnit_id, i_gpc.GoodsPackCount_id, i_gpc_m.GoodsPackCount_id
				) GU
				outer apply (
					select top 1
						i_gpc.GoodsPackCount_Count
					from
						v_GoodsPackCount i_gpc with (nolock)
					where
						i_gpc.GoodsUnit_id = ECTD.GoodsUnit_sid and
						i_gpc.GoodsUnit_id <> :DefaultGoodsUnit_id and
						i_gpc.DrugComplexMnn_id = EPTD.DrugComplexMnn_id and
						i_gpc.TRADENAMES_ID = Drug.DrugTorg_id and
						i_gpc.GoodsPackCount_Count is not null
					order by
						i_gpc.TRADENAMES_ID desc, i_gpc.GoodsPackCount_id
				) GU_S
			where
				{$where_clause}
		";
		//echo '<pre>' . print_r($this,1) . '</pre>'; exit();
		//echo '<pre>' . print_r(getDebugSQL($query, $queryParams),1) . '</pre>'; exit();
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return false;
		}

		$response = array();
		foreach ($result as $row) {
			$drug = array();
			$drug['EvnPrescrTreatDrug_id'] = $row['EvnPrescrTreatDrug_id'];
			$drug['EvnCourse_id'] = $row['EvnCourse_id'];
			$drug['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
			$drug['EvnPrescrTreat_id'] = $row['EvnPrescrTreat_id'];
			$drug['EvnPrescrTreat_pid'] = $row['EvnPrescrTreat_pid'];
			$drug['EvnPrescrTreat_setDate'] = $row['EvnPrescrTreat_setDate'];
			$drug['EvnPrescrTreat_PrescrCount'] = $row['EvnPrescrTreat_PrescrCount'];
			$drug['EvnPrescrTreatDrug_FactCount'] = $row['EvnPrescrTreatDrug_FactCount'];
			$drug['EvnPrescrTreatDrug_DoseDay'] = $row['EvnPrescrTreatDrug_DoseDay'];
			$drug['PrescrFactCountDiff'] = $row['EvnPrescrTreat_PrescrCount']-$row['EvnPrescrTreatDrug_FactCount'];
			$drug['EvnPrescrTreat_Fact'] = $drug['PrescrFactCountDiff'];
			$drug['Drug_id'] = $row['Drug_id'];
			$drug['Drug_Name'] = $row['Drug_Name'];
			$drug['DrugForm_Name'] = $row['DrugForm_Name'];
			//$drug['DrugUnit_Name'] = $row['DrugUnit_Name'];
			$drug['DrugPrepFas_id'] = $row['DrugPrepFas_id'];
			$drug['DocumentUcStr_oid'] = $row['DocumentUcStr_oid'];
			$drug['DocumentUcStr_Ost'] = $row['DocumentUcStr_Ost'];
			$drug['DocumentUcStr_Name'] = $row['DocumentUcStr_Name'];
			$drug['LpuSection_id'] = $row['LpuSection_id'];
			$drug['Storage_id'] = $row['Storage_id'];
			$drug['Mol_id'] = $row['Mol_id'];
			$drug['Mol_Name'] = $row['Mol_Name'];
			$drug['DrugFinance_Name'] = $row['DrugFinance_Name'];
			$drug['WhsDocumentCostItemType_Name'] = $row['WhsDocumentCostItemType_Name'];
			$drug['GoodsUnit_id'] = $row['GoodsUnit_id'];
			$drug['GoodsUnit_sid'] = $row['GoodsUnit_sid'];
			$drug['GoodsPackCount_Count'] = $row['GoodsPackCount_Count'];
			$drug['GoodsPackCount_sCount'] = $row['GoodsPackCount_sCount'];
			$drug['EvnPrescrTreatDrug_KolvoEd'] = $row['EvnPrescrTreatDrug_KolvoEd'];  //  #142958
			$drug['EvnPrescrTreatDrug_Kolvo'] = $row['EvnPrescrTreatDrug_Kolvo'];  #142958
			$drug['EvnPrescrTreat_Time'] = isset($row['EvnPrescrTreat_Time']) ? $row['EvnPrescrTreat_Time'] : null;  #160294

			//Назначенное кол-во (ед. доз.)
			$kolvoEd = $row['EvnPrescrTreatDrug_KolvoEd'];

			// кол-во в упаковке
			if (empty($row['Drug_Fas'])) $row['Drug_Fas'] = 1;
			$drug['Drug_Fas'] = $row['Drug_Fas'];

			//Остаток (ед. доз.)
			$edCount = 0;
			if (!empty($row['DocumentUcStr_Ost'])) {
				$edCount = $row['DocumentUcStr_Ost']*$row['Drug_Fas'];
			}
			/* 
			if ($edCount < $kolvoEd) {
				//если на остатках меньше назначенного кол-ва ед. доз.
				$kolvoEd = $edCount;
			}
			*/

			if (empty($kolvoEd)) {
				// Кол-во (ед. доз.)
				$drug['EvnDrug_KolvoEd'] = null;
				// Количество (ед. уч.)
				$drug['EvnDrug_Kolvo'] = null;
			} else {
				// Кол-во (ед. доз.)
				$drug['EvnDrug_KolvoEd'] = $kolvoEd;
				// Количество (ед. уч.)
				$drug['EvnDrug_Kolvo'] = $kolvoEd/$row['Drug_Fas'];
			}

			$response[] = $drug;
		}
		return $response;
	}

	/**
	 * Получение данных списка назначений с типом "Лекарственное лечение"
	 */
	public function doLoadEvnPrescrTreatDrugDataView($data) {
		$queryParams = array();
		$where_clause = '';

		if (!empty($data['EvnCourse_id'])) {
			$queryParams = array(
				'EvnCourse_id' => $data['EvnCourse_id']
			);
			$where_clause = 'EPT.EvnCourse_id = :EvnCourse_id';
		}

		if (empty($where_clause)) {
			return array();
		}

		$query = "
			select
				EPTD.EvnPrescrTreatDrug_id
				,Drug.Drug_id
				,dcm.DrugComplexMnn_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,EPTD.EvnPrescrTreatDrug_KolvoEd as KolvoEd
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
				,EPTD.EvnPrescrTreatDrug_Kolvo as Kolvo
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME) as EdUnits_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay as DoseDay
				,EPTD.EvnPrescrTreatDrug_FactCount as FactCntDay
				,EPT.EvnPrescrTreat_PrescrCount as PrescrCntDay
				,EPT.EvnCourse_id
				,EPT.EvnPrescrTreat_id as EvnPrescr_id
				,EPT.EvnPrescrTreat_pid as EvnPrescr_pid
				,EPT.EvnPrescrTreat_rid as EvnPrescr_rid
				,EPT.PrescrFailureType_id as PrescrFailureType_id
				,convert(varchar(10), EPT.EvnPrescrTreat_setDT, 104) as EvnPrescr_setDate
				,EPT.EvnPrescrTreat_IsExec as EvnPrescr_IsExec
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
				{$where_clause}
		";

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		$response = array();
		foreach ($result as $row) {
			$drug = $row;
			if (!empty($row['Drug_id'])) {
				$drug['Drug_key'] = 'Drug'.$row['Drug_id'];
			} else {
				$drug['Drug_key'] = 'DrugComplexMnn'.$row['DrugComplexMnn_id'];
			}
			$drug['KolvoEd'] = round($row['KolvoEd'], 5);
			$drug['Kolvo'] = round($row['Kolvo'], 5);
			$drug['DrugForm_Nick'] = $this->getDrugFormNick($row['DrugForm_Name'], ($row['Drug_Name']));
			$response[] = $drug;
		}
		return $response;
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		$addSelect = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$isNeedPrintEvnReceptGeneral = false == swPrescription::isStac($section);
		//$isNeedPrintEvnReceptGeneral = true;
		if ($isNeedPrintEvnReceptGeneral) {
			/*$addJoin .= '
				outer apply (
					select top 1
					rf.ReceptForm_Code,
					erg.EvnReceptGeneral_id,
					erg.EvnReceptGeneral_Ser,
					erg.EvnReceptGeneral_Num
					from v_evnreceptgeneral erg (nolock)
					inner join v_ReceptForm rf (nolock) on rf.ReceptForm_id = erg.ReceptForm_id
					where erg.EvnCourseTreatDrug_id = ec_drug.EvnCourseTreatDrug_id
				) erg
			';*/
			//Переделал джойн в соответствие с задачей https://redmine.swan.perm.ru/issues/108295
			$addJoin .= '
				outer apply (
					select top 1
						rf.ReceptForm_Code,
						erg.EvnReceptGeneral_id,
						erg.EvnReceptGeneral_Ser,
						erg.EvnReceptGeneral_Num,
						erg.MedPersonal_id,
						ergdl.EvnReceptGeneralDrugLink_id,
						RT.ReceptType_Code,
						rf.ReceptForm_Name
					from v_EvnReceptGeneralDrugLink ergdl (nolock)
						inner join v_EvnReceptGeneral ERG on ERG.EvnReceptGeneral_id = ergdl.EvnReceptGeneral_id
						left join v_ReceptForm rf (nolock) on rf.ReceptForm_id = erg.ReceptForm_id
						left join v_ReceptType RT with(nolock) on RT.ReceptType_id = ERG.ReceptType_id
					where ergdl.EvnCourseTreatDrug_id = ec_drug.EvnCourseTreatDrug_id
				) erg
			';
			$addSelect .= '
				,erg.EvnReceptGeneral_id
				,erg.ReceptForm_Code
				,erg.EvnReceptGeneral_Ser
				,erg.EvnReceptGeneral_Num
				,erg.MedPersonal_id
				,erg.EvnReceptGeneralDrugLink_id
				,erg.ReceptType_Code
				,erg.ReceptForm_Name
			';
		}
		$query = "
			with prescr as (
			select
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,EP.PrescrFailureType_id
					,convert(varchar(10), EP.EvnPrescr_setDT, 104) as EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				,case when 2 = EP.EvnPrescr_IsExec then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null end as EvnPrescr_execDT
				,1 as EvnPrescr_IsDir
				,EP.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_id as PrescriptionType_Code
				,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
				,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
					,EP.EvnPrescr_setDT
					,EP.EvnPrescr_updDT
				from v_EvnPrescr EP with (nolock)
				where
					EP.EvnPrescr_pid  = :EvnPrescr_pid
					and EP.PrescriptionType_id = 5
					and EP.PrescriptionStatusType_id != 3
			)

			select
				{$accessType},
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,EP.PrescrFailureType_id
				,EP.EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,EP.EvnPrescr_IsExec
				,case when EDr.EvnDrug_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
				,EP.EvnPrescr_execDT
				,EP.EvnPrescr_IsDir
				,EP.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_Code
				,EP.EvnPrescr_IsCito
				,EP.EvnPrescr_Descr
				,EPTD.EvnPrescrTreatDrug_id
				,Drug.Drug_id
				,dcm.DrugComplexMnn_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,EPTD.EvnPrescrTreatDrug_KolvoEd
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
				,EPTD.EvnPrescrTreatDrug_Kolvo
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME) as EdUnits_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay
				,EPTD.EvnPrescrTreatDrug_FactCount as FactCntDay
				,EPT.EvnPrescrTreat_PrescrCount as PrescrCntDay
				,ec_drug.EvnCourseTreatDrug_id
				,coalesce(CourseDrug.Drug_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrug_Name
				,coalesce(CourseDrug.DrugTorg_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrugTorg_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_KolvoEd, 10, 2)) as EvnCourseTreatDrug_KolvoEd
				,coalesce(CourseDf.CLSDRUGFORMS_NameLatinSocr,CourseDf.NAME,CourseDrug.DrugForm_Name,'') as CourseDrugForm_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_Kolvo, 10, 2)) as EvnCourseTreatDrug_Kolvo
				,coalesce(ec_mu.SHORTNAME, ec_cu.SHORTNAME, ec_au.SHORTNAME) as CourseEdUnits_Nick
				,ec_gu.GoodsUnit_Nick as CourseGoodsUnit_Nick
				,ec_drug.EvnCourseTreatDrug_MaxDoseDay
				,ec_drug.EvnCourseTreatDrug_MinDoseDay
				,ec_drug.EvnCourseTreatDrug_PrescrDose
				,ec_drug.EvnCourseTreatDrug_FactDose
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
				,EPT.EvnCourse_id
				,ISNULL(ECT.EvnCourseTreat_MaxCountDay, '') as MaxCountInDay
				,ISNULL(ECT.EvnCourseTreat_MinCountDay, '') as MinCountInDay
				,ISNULL(ECT.EvnCourseTreat_Duration, '') as Duration
				,ISNULL(ECT.EvnCourseTreat_ContReception, '') as ContReception
				,ISNULL(ECT.EvnCourseTreat_Interval, '') as Interval
				,ECT.EvnCourseTreat_PrescrCount
				,DTP.DurationType_Nick
				,DTI.DurationType_Nick as DurationType_IntNick
				,DTN.DurationType_Nick as DurationType_RecNick
				{$addSelect}
			from prescr EP with (nolock)
				inner join v_EvnPrescrTreat EPT with (nolock) on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
				inner join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				--left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				
				left join v_EvnCourseTreatDrug ec_drug with (nolock) on ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
					and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id				
		
				left join rls.MASSUNITS ep_mu with (nolock) on EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.ACTUNITS ep_au with (nolock) on EPTD.ACTUNITS_id = ep_au.ACTUNITS_id
				left join rls.MASSUNITS ec_mu with (nolock) on ec_drug.MASSUNITS_ID = ec_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ec_cu with (nolock) on ec_drug.CUBICUNITS_id = ec_cu.CUBICUNITS_id
				left join rls.ACTUNITS ec_au with (nolock) on ec_drug.ACTUNITS_id = ec_au.ACTUNITS_id
				left join v_GoodsUnit ec_gu  with (nolock) on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id
				left join PerformanceType PFT with (nolock) on  ECT.PerformanceType_id = PFT.PerformanceType_id
				left join PrescriptionIntroType PIT with (nolock) on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				--left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				--left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_Drug CourseDrug with (nolock) on CourseDrug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn CourseDcm with (nolock) on CourseDcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,CourseDrug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS CourseDf with (nolock) on CourseDcm.CLSDRUGFORMS_ID = CourseDf.CLSDRUGFORMS_ID
				left join DurationType DTP with (nolock) on ECT.DurationType_id = DTP.DurationType_id
				left join DurationType DTN with (nolock) on ECT.DurationType_recid = DTN.DurationType_id
				left join DurationType DTI with (nolock) on ECT.DurationType_intid = DTI.DurationType_id
				outer apply (
					select top 1 EvnDrug_id, EvnDrug_setDT from v_EvnDrug with (nolock)
					where EP.EvnPrescr_IsExec = 2 and EvnPrescr_id = EP.EvnPrescr_id
				) EDr
				{$addJoin}
			order by
				EPT.EvnCourse_id,
				EP.EvnPrescr_setDT
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			$last_course = null;
			$last_ep = null;
			$course_drags = array();
			$prescr_drags = array();
			$first_index = 0;
			$index_course = 0;
			$prescr_count = 0;
			$last_index = -1;
			$numCourse = 0;
			foreach ($tmp_arr as $i => $row) {
				if ($last_ep != $row['EvnPrescr_id']) {
					//это первая итерация с другим назначением курса
					$last_ep = $row['EvnPrescr_id'];
					$prescr_drags = array();
					$prescr_count++;
					if (swPrescription::$disableNewMode) {
						$last_index++;
					}
				}
				if ($last_course != $row['EvnCourse_id']) {
					//это первая итерация с другим курсом
					$numCourse++;
					$last_index++;
					$index_course = $last_index;
					$first_index = $i;
					$last_course = $row['EvnCourse_id'];
					$course_drags = array();
					$prescr_count = 1;
					$response[$index_course] = array(
						'EvnCourse_Title'=>'Курс '.$numCourse,
						'EvnCourse_id'=>$row['EvnCourse_id'],
						'isEvnCourse'=>1,
						'DrugListData'=>$course_drags,
						'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
						'PerformanceType_Name'=>$row['PerformanceType_Name'],
						'EvnCourse_begDate'=>$tmp_arr[$first_index]['EvnPrescr_setDate'],
						//'EvnCourse_endDate'=>$row['EvnPrescr_setDate'],
						'MaxCountInDay'=>$row['MaxCountInDay'],
						'MinCountInDay'=>$row['MinCountInDay'],
						'Duration'=>$row['Duration'],
						'DurationType_Nick'=>$row['DurationType_Nick'],
						'PrescriptionType_id'=>$row['PrescriptionType_id'],
						'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
						'EvnPrescr_pid'=>$row['EvnPrescr_pid'],
						'EvnPrescr_Count'=>$prescr_count,
						'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
						'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
						$section . '_id'=>$row['EvnPrescr_pid'].'-'.$row['EvnCourse_id'],
					);
					if (false == swPrescription::isStac($section)) {
						$response[$index_course]['EvnPrescr_id'] = $row['EvnPrescr_id'];
						$response[$index_course]['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
						$response[$index_course]['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
						$response[$index_course]['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
						$response[$index_course]['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
						$response[$index_course]['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
					}
				}
				if (empty($course_drags[$row['EvnCourseTreatDrug_id']])) {
					$drug_data = array(
						'Drug_Name'=>$row['CourseDrug_Name'],
						'DrugTorg_Name'=>$row['CourseDrugTorg_Name'],
						'KolvoEd'=>round($row['EvnCourseTreatDrug_KolvoEd'], 5),
						'DrugForm_Nick'=>$this->getDrugFormNick($row['CourseDrugForm_Name'], $row['CourseDrug_Name']),
						'Kolvo'=>round($row['EvnCourseTreatDrug_Kolvo'], 5),
						'EdUnits_Nick'=>$row['CourseEdUnits_Nick'],
						'GoodsUnit_Nick'=>$row['CourseGoodsUnit_Nick'],
						'MaxDoseDay'=>$row['EvnCourseTreatDrug_MaxDoseDay'],
						'MinDoseDay'=>$row['EvnCourseTreatDrug_MinDoseDay'],
						'PrescrDose'=>$row['EvnCourseTreatDrug_PrescrDose'],
						'DrugMaxCountInDay'=>$row['MaxCountInDay'],
						'PrescrDoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
						'RegionNick'=>$this->getRegionNick()
					);
					if ($isNeedPrintEvnReceptGeneral) {
						$drug_data['EvnReceptGeneral_id'] = $row['EvnReceptGeneral_id'];
						$drug_data['EvnReceptGeneral_Ser'] = $row['EvnReceptGeneral_Ser'];
						$drug_data['EvnReceptGeneral_Num'] = $row['EvnReceptGeneral_Num'];
						$drug_data['ReceptForm_Code'] = $row['ReceptForm_Code'];
						$drug_data['EvnReceptGeneralDrugLink_id'] = $row['EvnReceptGeneralDrugLink_id'];
						$drug_data['MedPersonal_id'] = $row['MedPersonal_id'];
						$drug_data['ReceptType_Code'] = $row['ReceptType_Code'];
						$drug_data['ReceptForm_Name'] = $row['ReceptForm_Name'];
					}
					$course_drags[$row['EvnCourseTreatDrug_id']] = $drug_data;
				}
				if (empty($tmp_arr[$i+1]) || $last_course != $tmp_arr[$i+1]['EvnCourse_id']) {
					$response[$index_course]['DrugListData'] = $course_drags;
					$response[$index_course]['EvnPrescr_Count'] = $prescr_count;
					if (false && false == swPrescription::isStac($section)) {
						$response[$index_course]['EvnPrescr_id'] = $row['EvnPrescr_id'];
						$response[$index_course]['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
						$response[$index_course]['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
						$response[$index_course]['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
						$response[$index_course]['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
						$response[$index_course]['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
					}
				}
				$edr_id = $row['EvnPrescrTreatDrug_id'];
				if (swPrescription::isStac($section)) {
					if (empty($prescr_drags[$edr_id])) {
						$drug_data = array(
							'Drug_Name'=>$row['Drug_Name'],
							'DrugTorg_Name'=>$row['DrugTorg_Name'],
							'KolvoEd'=>round($row['EvnPrescrTreatDrug_KolvoEd'], 5),
							'DrugForm_Nick'=>$this->getDrugFormNick($row['DrugForm_Name'], ($row['Drug_Name'])),
							'Kolvo'=>round($row['EvnPrescrTreatDrug_Kolvo'], 5),
							'EdUnits_Nick'=>$row['EdUnits_Nick'],
							'DoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
							'PrescrCntDay'=>$row['PrescrCntDay'],
							'FactCntDay'=>$row['FactCntDay'],
						);
						if (false == swPrescription::$disableNewMode) {
							//EvnCourseTreatDrug_id почему-то у всех одинаковый
							//$drug_data['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
							if (!empty($row['Drug_id'])) {
								$drug_data['Drug_key'] = 'Drug'.$row['Drug_id'];
							} else {
								$drug_data['Drug_key'] = 'DrugComplexMnn'.$row['DrugComplexMnn_id'];
							}
						}
						$prescr_drags[$edr_id] = $drug_data;
					}
					if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
						if (swPrescription::$disableNewMode) {
							$row['DrugListData'] = $prescr_drags;
							$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
							$response[$last_index] = $row;
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
								$prescr_data['PrescrFailureType_id'] = $row['PrescrFailureType_id'];
								$prescr_data['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
								$prescr_data['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
								$prescr_data['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
								$prescr_data['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
								$prescr_data['DrugListData'] = $prescr_drags;
								$response[$index_course]['PrescrListData'][$row['EvnPrescr_id']] = $prescr_data;
							}
						}
					}
				}
			}
			return $response;
		} else {
			return false;
		}
	}

    /**
     * Получение значения по умолчанию для полей в лекарственном лечении
     */
    function getDrugPackData($data) {
        $query = "";
		$join = "
			outer apply (
                        select top 1 -- лекарственная форма, если не находим в справочнике, то считаем что это таблетка
                            coalesce(i_gu.GoodsUnit_id, i_gu_tab.GoodsUnit_id) as GoodsUnit_id,
							coalesce(i_gu.GoodsUnit_Nick, i_gu_tab.GoodsUnit_Nick) as GoodsUnit_Nick
                        from
                            rls.CLSDRUGFORMS i_cdf with (nolock)
                            left join v_GoodsUnit i_gu with (nolock) on i_gu.GoodsUnit_Name = i_cdf.FULLNAME
							left join v_GoodsUnit i_gu_tab with (nolock) on i_gu_tab.GoodsUnit_Nick = 'таб.'
                        where
                            i_cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                    ) gu
					";
		if ($_SESSION['region']['nick'] == 'ufa') {
			$join = "left join r2.fn_CLSDRUGFORMS () gu on gu.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID";
		};

        if (!empty($data['Drug_id'])) {
            $query = "
                select
                    dcmf.DrugComplexMnnFas_Kol as Fas_Kolvo,
                    (
                       isnull(dcmf.DrugComplexMnnFas_Kol, 1)*isnull(dcmf.DrugComplexMnnFas_KolPrim, 1)*isnull(dcmf.DrugComplexMnnFas_Tert, 1)
                    ) as Fas_NKolvo,
                    (
                        case
                            when dcmf.MASSUNITS_ID is not null
                            then dcmf.DrugComplexMnnFas_MassPrim
                            else dcmf.DrugComplexMnnFas_VolPrim
                        end
                    ) as FasMass_Kolvo,
                    fm_gu.GoodsUnit_id as FasMass_GoodsUnit_id,
                    fm_gu.GoodsUnit_Nick as FasMass_GoodsUnit_Nick,
                    (
                        case
                            when dcmd.MASSUNITS_ID is not null
                            then dcmd.DrugComplexMnnDose_Mass
                            when dcmd.CONCENUNITS_ID is not null
                            then dcmd.DrugComplexMnnDose_Concen
                            else dcmd.DrugComplexMnnDose_KolACT
                        end
                    ) as DoseMass_Kolvo,
                    (
                        case
                            when dcmd.MASSUNITS_ID is not null
                            then 'Mass'
                            when dcmd.CONCENUNITS_ID is not null
                            then 'Concen'
                            else 'KolACT'
                        end
                    ) as DoseMass_Type,
                    dm_gu.GoodsUnit_id as DoseMass_GoodsUnit_id,
                    dm_gu.GoodsUnit_Nick as DoseMass_GoodsUnit_Nick,
                    gu.GoodsUnit_id,
                    gu.GoodsUnit_Nick
                from
                    rls.v_Drug d with (nolock)
                    left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                    left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                    left join rls.MASSUNITS f_mu with (nolock) on f_mu.MASSUNITS_ID = dcmf.MASSUNITS_ID
                    left join rls.CUBICUNITS f_cu with (nolock) on f_cu.CUBICUNITS_ID = dcmf.CUBICUNITS_ID
                    left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                    left join rls.MASSUNITS d_mu with (nolock) on d_mu.MASSUNITS_ID = dcmd.MASSUNITS_ID
                    left join rls.CONCENUNITS d_cu with (nolock) on d_cu.CONCENUNITS_ID = dcmd.CONCENUNITS_ID
                    left join rls.ACTUNITS d_au with (nolock) on d_au.ACTUNITS_ID = dcmd.ACTUNITS_ID
					{$join}
                    outer apply (
                        select top 1
                            i_gu.GoodsUnit_id,
                            i_gu.GoodsUnit_Nick
                        from
                            v_GoodsUnit i_gu with (nolock)
                        where
                            (
                                dcmf.MASSUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = f_mu.FULLNAME
                            ) or (
                                dcmf.CUBICUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = f_cu.FULLNAME
                            )
                    ) fm_gu
                    outer apply (
                        select top 1
                            i_gu.GoodsUnit_id,
                            i_gu.GoodsUnit_Nick
                        from
                            v_GoodsUnit i_gu with (nolock)
                        where
                            (
                                dcmd.MASSUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_mu.FULLNAME
                            ) or (
                                dcmd.CONCENUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_cu.FULLNAME
                            ) or (
                                dcmd.ACTUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_au.FULLNAME
                            )
                    ) dm_gu
                where
                    d.Drug_id = :Drug_id;
            ";
        } else if (!empty($data['DrugComplexMnn_id'])) {
            $query = "
                select
                    dcmf.DrugComplexMnnFas_Kol as Fas_Kolvo,
                    (
                       isnull(dcmf.DrugComplexMnnFas_Kol, 1)*isnull(dcmf.DrugComplexMnnFas_KolPrim, 1)*isnull(dcmf.DrugComplexMnnFas_Tert, 1)
                    ) as Fas_NKolvo,
                    (
                        case
                            when dcmf.MASSUNITS_ID is not null
                            then dcmf.DrugComplexMnnFas_MassPrim
                            else dcmf.DrugComplexMnnFas_VolPrim
                        end
                    ) as FasMass_Kolvo,
                    fm_gu.GoodsUnit_id as FasMass_GoodsUnit_id,
                    fm_gu.GoodsUnit_Nick as FasMass_GoodsUnit_Nick,
                    (
                        case
                            when dcmd.MASSUNITS_ID is not null
                            then dcmd.DrugComplexMnnDose_Mass
                            when dcmd.CONCENUNITS_ID is not null
                            then dcmd.DrugComplexMnnDose_Concen
                            else dcmd.DrugComplexMnnDose_KolACT
                        end
                    ) as DoseMass_Kolvo,
					(
                        case
                            when dcmd.MASSUNITS_ID is not null
                            then 'Mass'
                            when dcmd.CONCENUNITS_ID is not null
                            then 'Concen'
                            else 'KolACT'
                        end
                    ) as DoseMass_Type,
                    dm_gu.GoodsUnit_id as DoseMass_GoodsUnit_id,
                    dm_gu.GoodsUnit_Nick as DoseMass_GoodsUnit_Nick,
                    gu.GoodsUnit_id,
                    gu.GoodsUnit_Nick
                from
                    rls.v_DrugComplexMnn dcm with (nolock)
                    left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                    left join rls.MASSUNITS f_mu with (nolock) on f_mu.MASSUNITS_ID = dcmf.MASSUNITS_ID
                    left join rls.CUBICUNITS f_cu with (nolock) on f_cu.CUBICUNITS_ID = dcmf.CUBICUNITS_ID
                    left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                    left join rls.MASSUNITS d_mu with (nolock) on d_mu.MASSUNITS_ID = dcmd.MASSUNITS_ID
                    left join rls.CONCENUNITS d_cu with (nolock) on d_cu.CONCENUNITS_ID = dcmd.CONCENUNITS_ID
                    left join rls.ACTUNITS d_au with (nolock) on d_au.ACTUNITS_ID = dcmd.ACTUNITS_ID
					{$join}
                    outer apply (
                        select top 1
                            i_gu.GoodsUnit_id,
                            i_gu.GoodsUnit_Nick
                        from
                            v_GoodsUnit i_gu with (nolock)
                        where
                            (
                                dcmf.MASSUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = f_mu.FULLNAME
                            ) or (
                                dcmf.CUBICUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = f_cu.FULLNAME
                            ) or (
                                dcmd.ACTUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_au.FULLNAME
                            )
                    ) fm_gu
                    outer apply (
                        select top 1
                            i_gu.GoodsUnit_id,
                            i_gu.GoodsUnit_Nick
                        from
                            v_GoodsUnit i_gu with (nolock)
                        where
                            (
                                dcmd.MASSUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_mu.FULLNAME
                            ) or (
                                dcmd.CONCENUNITS_ID is not null and
                                i_gu.GoodsUnit_Name = d_cu.FULLNAME
                            )
                    ) dm_gu
                where
                    dcm.DrugComplexMnn_id = :DrugComplexMnn_id;
            ";
        }

        if (!empty($query)) {
            $result = $this->getFirstRowFromQuery($query, $data);
            $result['success'] = true;
            return $result;
        } else {
            return false;
        }
    }

	/**
	 * Получение назначений лекарственных средств
	 */
    function getEvnPrescrTreatForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['EvnPrescr_id'])) {
			$filters[] = "EPT.EvnPrescrTreat_id = :EvnPrescr_id";
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
		}
		if (!empty($data['EvnPrescrTreat_id'])) {
			$filters[] = "EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id";
			$params['EvnPrescrTreat_id'] = $data['EvnPrescrTreat_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filters[] = "EPT.EvnPrescrTreat_pid = :Evn_pid";
			$params['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['Evn_rid'])) {
			$filters[] = "EPT.EvnPrescrTreat_rid = :Evn_rid";
			$params['Evn_rid'] = $data['Evn_rid'];
		}
		if (!empty($data['EvnClass_id'])) {
			$filters[] = "EPT.EvnClass_id = :EvnClass_id";
			$params['EvnClass_id'] = $data['EvnClass_id'];
		}
		if (!empty($data['Evn_setDT'])) {
			$filters[] = "cast(EPT.EvnPrescrTreat_setDT as date) = :Evn_setDT";
			$params['Evn_setDT'] = $data['Evn_setDT'];
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$filters[] = "isnull(ECTD.DrugComplexMnn_id, D.DrugComplexMnn_id) = :DrugComplexMnn_id";
			$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не передан ни один параметр');
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				EPT.EvnPrescrTreat_id as EvnPrescr_id,
				EPT.EvnPrescrTreat_id,
				EPT.EvnPrescrTreat_id as Evn_id,
				EPT.EvnPrescrTreat_pid as Evn_pid,
				EPT.EvnPrescrTreat_rid as Evn_rid,
				EPT.EvnClass_id,
				EPT.Lpu_id,
				convert(varchar(10), EPT.EvnPrescrTreat_setDate, 120) as Evn_setDT,
				EPT.Person_id,
				EPT.PrescriptionType_id,
				IsCito.YesNo_Code as EvnPrescr_IsCito,
				EPT.PrescriptionStatusType_id,
				EPT.EvnPrescrTreat_Descr as EvnPrescr_Descr,
				IsExec.YesNo_Code as EvnPrescr_isExec,
				EPT.EvnPrescrTreat_PrescrCount,
				EPT.EvnCourse_id,
				ECT.EvnCourseTreat_id,
				ECT.CourseType_id,
				ECT.EvnCourseTreat_Duration as EvnCourse_Duration,
				ECT.DurationType_id,
				ECT.EvnCourseTreat_ContReception as EvnCourse_ContReception,
				ECT.DurationType_recid,
				ECT.EvnCourseTreat_Interval as EvnCourse_Interval,
				ECT.DurationType_intid,
				ECT.PrescriptionIntroType_id,
				ECT.PerformanceType_id,
				ECTD.DrugComplexMnn_id,
				ECTD.Drug_id,
				EPTD.EvnPrescrTreatDrug_Kolvo,
				ECTD.GoodsUnit_id,
				EPTD.EvnPrescrTreatDrug_KolvoEd,
				ECTD.GoodsUnit_sid,
				EPTD.EvnPrescrTreatDrug_DoseDay,
				ECTD.EvnCourseTreatDrug_Kolvo,
				ECTD.EvnCourseTreatDrug_KolvoEd,
				ECTD.EvnCourseTreatDrug_PrescrDose
			from 
				v_EvnPrescrTreat EPT with(nolock)
				left join v_EvnPrescrTreatDrug EPTD with(nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				left join v_EvnCourseTreat ECT with(nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join v_EvnCourseTreatDrug ECTD with(nolock) on ECTD.EvnCourseTreat_id = ECT.EvnCourseTreat_id
				left join rls.v_Drug D with(nolock) on D.Drug_id = ECTD.Drug_id
				left join v_YesNo IsCito with(nolock) on IsCito.YesNo_id = EPT.EvnPrescrTreat_IsCito
				left join v_YesNo IsExec with(nolock) on IsExec.YesNo_id = EPT.EvnPrescrTreat_IsExec
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных назначении лекарственных средств
	 */
	function loadEvnPrescrTreatInfoForAPI($data) {
		$params = array('EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']);
		$query = "
			select top 1
				EPT.EvnPrescrTreat_id,
				EPT.EvnPrescrTreat_pid as Evn_pid,
				EPT.PersonEvn_id,
				EPT.Server_id,
				EPT.Lpu_id,
				isnull(EPT.EvnPrescrTreat_isExec, 1) as EvnPrescr_isExec,
				isnull(EPT.EvnPrescrTreat_IsCito, 1) as EvnPrescr_IsCito,
				EPT.EvnPrescrTreat_Descr as EvnPrescr_Descr,
				EPT.EvnPrescrTreat_PrescrCount,
				E.EvnClass_SysNick,
				ECT.EvnCourseTreat_id,
				ECT.MedPersonal_id,
				ECT.LpuSection_id,
				ECT.Morbus_id,
				convert(varchar(10), ECT.EvnCourseTreat_setDate, 120) as Evn_setDT,
				ECT.EvnCourseTreat_Duration as EvnCourse_Duration,
				ECT.DurationType_id,
				ECT.EvnCourseTreat_ContReception as EvnCourse_ContReception,
				ECT.DurationType_recid,
				ECT.EvnCourseTreat_Interval as EvnCourse_Interval,
				ECT.DurationType_intid,
				ECT.ResultDesease_id,
				ECT.PerformanceType_id,
				ECT.PrescriptionIntroType_id,
				ECT.PrescriptionTreatType_id,
				ECTD.EvnCourseTreatDrug_id,
				ECTD.Drug_id,
				ECTD.DrugComplexMnn_id,
				ECTD.EvnCourseTreatDrug_Kolvo as EvnPrescrTreatDrug_Kolvo,
				ECTD.EvnCourseTreatDrug_KolvoEd as EvnPrescrTreatDrug_KolvoEd,
				ECTD.GoodsUnit_id,
				ECTD.GoodsUnit_sid
			from
				v_EvnPrescrTreat EPT with(nolock)
				inner join v_EvnPrescrTreatDrug EPTD with(nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join v_EvnCourseTreat ECT with(nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				inner join v_EvnCourseTreatDrug ECTD with(nolock) on ECTD.EvnCourseTreat_id = ECT.EvnCourseTreat_id
				left join v_Evn E with(nolock) on E.Evn_id = EPT.EvnPrescrTreat_pid
			where
				EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Получение данных назначении лекарственных средств
	 */
	function loadEvnPrescrTreatDataForUpdate($data) {

		$params = array('EvnPrescrTreat_id' => $data['EvnPrescrTreat_id']);

		$query = "
			select top 1
				ECT.EvnCourseTreat_id,
				EPT.EvnPrescrTreat_pid as Evn_id,

				ECTD.DrugComplexMnn_id,

				ECT.PrescriptionIntroType_id,
				convert(varchar(10), ECT.EvnCourseTreat_setDate, 120) as EvnCourseTreat_setDate,
				EPT.EvnPrescrTreat_PrescrCount as EvnCourseTreat_CountDay,
				ECT.EvnCourseTreat_Duration,
				ECT.DurationType_id,
				ECT.PerformanceType_id,
				CASE
					WHEN EPT.EvnPrescrTreat_IsCito = 2 THEN 1
					WHEN EPT.EvnPrescrTreat_IsCito = 1 THEN 0
					ELSE 0
				END as EvnPrescrTreat_IsCito,
				EPT.EvnPrescrTreat_Descr,

				ECT.EvnCourseTreat_ContReception,
				ECT.EvnCourseTreat_Interval,
				ECT.DurationType_recid,
				ECT.DurationType_intid,
				ECT.PrescriptionTreatType_id,
				ECT.ResultDesease_id,
				ECT.Morbus_id,

				ECTD.EvnCourseTreatDrug_id,
				ECTD.Drug_id,

				ECTD.EvnCourseTreatDrug_Kolvo as Kolvo,
				ECTD.EvnCourseTreatDrug_KolvoEd as KolvoEd,
				ECTD.GoodsUnit_id,
				ECTD.GoodsUnit_sid
			from
				v_EvnPrescrTreat EPT with(nolock)
				inner join v_EvnPrescrTreatDrug EPTD (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				inner join v_EvnCourseTreat ECT (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				inner join v_EvnCourseTreatDrug ECTD (nolock) on ECTD.EvnCourseTreat_id = ECT.EvnCourseTreat_id
			where
				EPT.EvnPrescrTreat_id = :EvnPrescrTreat_id
		";

		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewDataGridTree($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		$addSelect = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND isnull({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as accessType";
			$addJoin = "left join v_{$sysnick} {$sysnick} with (nolock) on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$isNeedPrintEvnReceptGeneral = false == swPrescription::isStac($section);
		//$isNeedPrintEvnReceptGeneral = true;
		if ($isNeedPrintEvnReceptGeneral) {
			/*$addJoin .= '
				outer apply (
					select top 1
					rf.ReceptForm_Code,
					erg.EvnReceptGeneral_id,
					erg.EvnReceptGeneral_Ser,
					erg.EvnReceptGeneral_Num
					from v_evnreceptgeneral erg (nolock)
					inner join v_ReceptForm rf (nolock) on rf.ReceptForm_id = erg.ReceptForm_id
					where erg.EvnCourseTreatDrug_id = ec_drug.EvnCourseTreatDrug_id
				) erg
			';*/
			//Переделал джойн в соответствие с задачей https://redmine.swan.perm.ru/issues/108295
			$addJoin .= '
				outer apply (
					select top 1
						rf.ReceptForm_Code,
						erg.EvnReceptGeneral_id,
						erg.EvnReceptGeneral_Ser,
						erg.EvnReceptGeneral_Num,
						erg.MedPersonal_id,
						ergdl.EvnReceptGeneralDrugLink_id
					from v_EvnReceptGeneralDrugLink ergdl (nolock)
					inner join v_EvnReceptGeneral ERG on ERG.EvnReceptGeneral_id = ergdl.EvnReceptGeneral_id
					left join v_ReceptForm rf (nolock) on rf.ReceptForm_id = erg.ReceptForm_id
					where ergdl.EvnCourseTreatDrug_id = ec_drug.EvnCourseTreatDrug_id
				) erg
			';
			$addSelect .= '
				,erg.EvnReceptGeneral_id
				,erg.ReceptForm_Code
				,erg.EvnReceptGeneral_Ser
				,erg.EvnReceptGeneral_Num
				,erg.MedPersonal_id
				,erg.EvnReceptGeneralDrugLink_id
			';
		}
		$query = "
			with prescr as (
				select
					 EP.EvnPrescr_id
					,EP.EvnPrescr_pid
					,EP.EvnPrescr_rid
					,convert(varchar(10), EP.EvnPrescr_setDT, 104) as EvnPrescr_setDate
					,null as EvnPrescr_setTime
					,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
					,case when 2 = EP.EvnPrescr_IsExec then convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null end as EvnPrescr_execDT
					,1 as EvnPrescr_IsDir
					,EP.PrescriptionStatusType_id
					,EP.PrescriptionType_id
					,EP.PrescriptionType_id as PrescriptionType_Code
					,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
					,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
					,EP.EvnPrescr_setDT
					,EP.EvnPrescr_updDT
				from v_EvnPrescr EP with (nolock)
				where
					EP.EvnPrescr_pid  = :EvnPrescr_pid
					and EP.PrescriptionType_id = 5
					and EP.PrescriptionStatusType_id != 3
			)

			select
				{$accessType},
				EP.EvnPrescr_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,EP.EvnPrescr_setDate
				,null as EvnPrescr_setTime
				,EP.EvnPrescr_IsExec
				,case when EDr.EvnDrug_id is null then 1 else 2 end as EvnPrescr_IsHasEvn
				,EP.EvnPrescr_execDT
				,EP.EvnPrescr_IsDir
				,EP.PrescriptionStatusType_id
				,EP.PrescriptionType_id
				,EP.PrescriptionType_Code
				,EP.EvnPrescr_IsCito
				,EP.EvnPrescr_Descr
				,EPTD.EvnPrescrTreatDrug_id
				,Drug.Drug_id
				,dcm.DrugComplexMnn_id
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,EPTD.EvnPrescrTreatDrug_KolvoEd
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
				,EPTD.EvnPrescrTreatDrug_Kolvo
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME) as EdUnits_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay
				,EPTD.EvnPrescrTreatDrug_FactCount as FactCntDay
				,EPT.EvnPrescrTreat_PrescrCount as PrescrCntDay
				,ec_drug.EvnCourseTreatDrug_id
				,coalesce(CourseDrug.Drug_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrug_Name
				,coalesce(CourseDrug.DrugTorg_Name, CourseDcm.DrugComplexMnn_RusName, '') as CourseDrugTorg_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_KolvoEd, 10, 2)) as EvnCourseTreatDrug_KolvoEd
				,coalesce(CourseDf.CLSDRUGFORMS_NameLatinSocr,CourseDf.NAME,CourseDrug.DrugForm_Name,'') as CourseDrugForm_Name
				,LTRIM(STR(ec_drug.EvnCourseTreatDrug_Kolvo, 10, 2)) as EvnCourseTreatDrug_Kolvo
				,coalesce(ec_mu.SHORTNAME, ec_cu.SHORTNAME, ec_au.SHORTNAME) as CourseEdUnits_Nick
				,ec_gu.GoodsUnit_Nick as CourseGoodsUnit_Nick
				,ec_drug.EvnCourseTreatDrug_MaxDoseDay
				,ec_drug.EvnCourseTreatDrug_MinDoseDay
				,ec_drug.EvnCourseTreatDrug_PrescrDose
				,ec_drug.EvnCourseTreatDrug_FactDose
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
				,EPT.EvnCourse_id
				,ISNULL(ECT.EvnCourseTreat_MaxCountDay, '') as MaxCountInDay
				,ISNULL(ECT.EvnCourseTreat_MinCountDay, '') as MinCountInDay
				,ISNULL(ECT.EvnCourseTreat_Duration, '') as Duration
				,ISNULL(ECT.EvnCourseTreat_ContReception, '') as ContReception
				,ISNULL(ECT.EvnCourseTreat_Interval, '') as Interval
				,ECT.EvnCourseTreat_PrescrCount
				,DTP.DurationType_Nick
				,DTI.DurationType_Nick as DurationType_IntNick
				,DTN.DurationType_Nick as DurationType_RecNick
				{$addSelect}
				,case when (dcm.DrugComplexMnn_id is null 
						OR ECT.PrescriptionIntroType_id is null
						OR EP.EvnPrescr_setDate is null
						OR ECT.EvnCourseTreat_Duration is null
						OR ECT.DurationType_id is null
						OR (ECT.EvnCourseTreat_MaxCountDay is null AND ECT.EvnCourseTreat_MaxCountDay is null)
						OR ec_drug.EvnCourseTreatDrug_KolvoEd  is null
						OR ec_drug.GoodsUnit_sid is null) then 1 else 2 end as isValid
			from prescr EP with (nolock)
				inner join v_EvnPrescrTreat EPT with (nolock) on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
				inner join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				--left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
				
				left join v_EvnCourseTreatDrug ec_drug with (nolock) on ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id
					and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id				
		
				left join rls.MASSUNITS ep_mu with (nolock) on EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.ACTUNITS ep_au with (nolock) on EPTD.ACTUNITS_id = ep_au.ACTUNITS_id
				left join rls.MASSUNITS ec_mu with (nolock) on ec_drug.MASSUNITS_ID = ec_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ec_cu with (nolock) on ec_drug.CUBICUNITS_id = ec_cu.CUBICUNITS_id
				left join rls.ACTUNITS ec_au with (nolock) on ec_drug.ACTUNITS_id = ec_au.ACTUNITS_id
				left join v_GoodsUnit ec_gu  with (nolock) on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id
				left join PerformanceType PFT with (nolock) on  ECT.PerformanceType_id = PFT.PerformanceType_id
				left join PrescriptionIntroType PIT with (nolock) on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				--left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = EPTD.Drug_id
				--left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_Drug CourseDrug with (nolock) on CourseDrug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn CourseDcm with (nolock) on CourseDcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,CourseDrug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS CourseDf with (nolock) on CourseDcm.CLSDRUGFORMS_ID = CourseDf.CLSDRUGFORMS_ID
				left join DurationType DTP with (nolock) on ECT.DurationType_id = DTP.DurationType_id
				left join DurationType DTN with (nolock) on ECT.DurationType_recid = DTN.DurationType_id
				left join DurationType DTI with (nolock) on ECT.DurationType_intid = DTI.DurationType_id
				outer apply (
					select top 1 EvnDrug_id, EvnDrug_setDT from v_EvnDrug with (nolock)
					where EP.EvnPrescr_IsExec = 2 and EvnPrescr_id = EP.EvnPrescr_id
				) EDr
				{$addJoin}
			order by
				EPT.EvnCourse_id,
				EP.EvnPrescr_setDT
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			$last_course = null;
			$last_ep = null;
			$course_drags = array();
			$prescr_drags = array();
			$first_index = 0;
			$index_course = 0;
			$prescr_count = 0;
			$last_index = -1;
			$numCourse = 0;
			foreach ($tmp_arr as $i => $row) {
				if ($last_ep != $row['EvnPrescr_id']) {
					//это первая итерация с другим назначением курса
					$last_ep = $row['EvnPrescr_id'];
					$prescr_drags = array();
					$prescr_count++;
					if (swPrescription::$disableNewMode) {
						$last_index++;
					}
				}
				if ($last_course != $row['EvnCourse_id']) {
					//это первая итерация с другим курсом
					$numCourse++;
					$last_index++;
					$index_course = $last_index;
					$first_index = $i;
					$last_course = $row['EvnCourse_id'];
					$course_drags = array();
					$prescr_count = 1;
					$response[$index_course] = array(
						'EvnCourse_Title'=>'Курс '.$numCourse,
						'EvnCourse_id'=>$row['EvnCourse_id'],
						'isEvnCourse'=>1,
						'DrugListData'=>$course_drags,
						//'children'=>$course_drags,
						'Drug_Name' => 'Курс '.$numCourse,
						'expanded' => true,
						'leaf' => false,
						'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
						'PerformanceType_Name'=>$row['PerformanceType_Name'],
						'EvnCourse_begDate'=>$tmp_arr[$first_index]['EvnPrescr_setDate'],
						//'EvnCourse_endDate'=>$row['EvnPrescr_setDate'],
						'MaxCountInDay'=>$row['MaxCountInDay'],
						'MinCountInDay'=>$row['MinCountInDay'],
						'Duration'=>$row['Duration'],
						'DurationType_Nick'=>$row['DurationType_Nick'],
						'PrescriptionType_id'=>$row['PrescriptionType_id'],
						'PrescriptionType_Code'=>$row['PrescriptionType_Code'],
						'EvnPrescr_pid'=>$row['EvnPrescr_pid'],
						'EvnPrescr_Count'=>$prescr_count,
						'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
						'EvnPrescr_IsCito'=>$row['EvnPrescr_IsCito'],
						'isValid'=>$row['isValid'],
						$section . '_id'=>$row['EvnPrescr_pid'].'-'.$row['EvnCourse_id'],
					);
					if (false == swPrescription::isStac($section)) {
						$response[$index_course]['EvnPrescr_id'] = $row['EvnPrescr_id'];
						$response[$index_course]['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
						$response[$index_course]['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
						$response[$index_course]['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
						$response[$index_course]['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
						$response[$index_course]['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
					}
				}
				if (empty($course_drags[$row['EvnCourseTreatDrug_id']])) {
					$drug_data = array(
						'Drug_Name'=>$row['CourseDrug_Name'],
						'leaf' => true,
						'DrugTorg_Name'=>$row['CourseDrugTorg_Name'],
						'KolvoEd'=>round($row['EvnCourseTreatDrug_KolvoEd'], 5),
						'DrugForm_Nick'=>$this->getDrugFormNick($row['CourseDrugForm_Name'], $row['CourseDrug_Name']),
						'Kolvo'=>round($row['EvnCourseTreatDrug_Kolvo'], 5),
						'EdUnits_Nick'=>$row['CourseEdUnits_Nick'],
						'GoodsUnit_Nick'=>$row['CourseGoodsUnit_Nick'],
						'MaxDoseDay'=>$row['EvnCourseTreatDrug_MaxDoseDay'],
						'MinDoseDay'=>$row['EvnCourseTreatDrug_MinDoseDay'],
						'PrescrDose'=>$row['EvnCourseTreatDrug_PrescrDose'],
						'DrugMaxCountInDay'=>$row['MaxCountInDay'],
						'PrescrDoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
						'RegionNick'=>$this->getRegionNick()
					);
					if ($isNeedPrintEvnReceptGeneral) {
						//Для нового Экста
						$response[$index_course]['EvnReceptGeneral_id'] = $row['EvnReceptGeneral_id'];
						$response[$index_course]['EvnReceptGeneral_Ser'] = $row['EvnReceptGeneral_Ser'];
						$response[$index_course]['EvnReceptGeneral_Num'] = $row['EvnReceptGeneral_Num'];
						$response[$index_course]['EvnReceptGeneralDrugLink_id'] = $row['EvnReceptGeneralDrugLink_id'];
						//Для работопспособности старого Экста
						$drug_data['EvnReceptGeneral_id'] = $row['EvnReceptGeneral_id'];
						$drug_data['EvnReceptGeneral_Ser'] = $row['EvnReceptGeneral_Ser'];
						$drug_data['EvnReceptGeneral_Num'] = $row['EvnReceptGeneral_Num'];
						$drug_data['ReceptForm_Code'] = $row['ReceptForm_Code'];
						$drug_data['EvnReceptGeneralDrugLink_id'] = $row['EvnReceptGeneralDrugLink_id'];
						$drug_data['MedPersonal_id'] = $row['MedPersonal_id'];
						if(!empty($row['EvnReceptGeneral_id']))
							$response[$index_course]['haveRecept'] = 2;
					}
					$course_drags[$row['EvnCourseTreatDrug_id']] = $drug_data;
					//$course_drags[] = $drug_data;
				}
				if (empty($tmp_arr[$i+1]) || $last_course != $tmp_arr[$i+1]['EvnCourse_id']) {
					$response[$index_course]['DrugListData'] = $course_drags;
					//$response[$index_course]['children'] = $course_drags;
					$row['expanded'] = true;
					$row['leaf'] = false;
					$response[$index_course]['EvnPrescr_Count'] = $prescr_count;
					if (false && false == swPrescription::isStac($section)) {
						$response[$index_course]['EvnPrescr_id'] = $row['EvnPrescr_id'];
						$response[$index_course]['EvnPrescr_rid'] = $row['EvnPrescr_rid'];
						$response[$index_course]['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
						$response[$index_course]['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
						$response[$index_course]['PrescriptionStatusType_id'] = $row['PrescriptionStatusType_id'];
						$response[$index_course]['EvnPrescr_IsExec'] = $row['EvnPrescr_IsExec'];
					}
				}
				$edr_id = $row['EvnPrescrTreatDrug_id'];
				if (swPrescription::isStac($section)) {
					if (empty($prescr_drags[$edr_id])) {
						$drug_data = array(
							'Drug_Name'=>$row['Drug_Name'],
							'DrugTorg_Name'=>$row['DrugTorg_Name'],
							'KolvoEd'=>round($row['EvnPrescrTreatDrug_KolvoEd'], 5),
							'DrugForm_Nick'=>$this->getDrugFormNick($row['DrugForm_Name'], ($row['Drug_Name'])),
							'Kolvo'=>round($row['EvnPrescrTreatDrug_Kolvo'], 5),
							'EdUnits_Nick'=>$row['EdUnits_Nick'],
							'DoseDay'=>$row['EvnPrescrTreatDrug_DoseDay'],
							'PrescrCntDay'=>$row['PrescrCntDay'],
							'FactCntDay'=>$row['FactCntDay'],
							'leaf' => true
						);
						if (false == swPrescription::$disableNewMode) {
							//EvnCourseTreatDrug_id почему-то у всех одинаковый
							//$drug_data['EvnCourseTreatDrug_id'] = $row['EvnCourseTreatDrug_id'];
							if (!empty($row['Drug_id'])) {
								$drug_data['Drug_key'] = 'Drug'.$row['Drug_id'];
							} else {
								$drug_data['Drug_key'] = 'DrugComplexMnn'.$row['DrugComplexMnn_id'];
							}
						}
						$prescr_drags[$edr_id] = $drug_data;
						//$prescr_drags[] = $drug_data;
					}
					if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
						if (swPrescription::$disableNewMode) {
							$row['DrugListData'] = $prescr_drags;
							//$row['children'] = $prescr_drags;
							$row['expanded'] = true;
							$row['leaf'] = false;
							$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
							$response[$last_index] = $row;
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
								$prescr_data['DrugListData'] = $prescr_drags;
								//$prescr_data['children'] = $prescr_drags;

								$response[$index_course]['PrescrListData'][$row['EvnPrescr_id']] = $prescr_data;
							}
						}
					}
				}
			}

			return $response;
		} else {
			return false;
		}
	}

	/**
	 * yl:5588 Действующие лекарственные наначения для пациента
	 */
	public function checkPersonPrescrTreat($data) {
		$sql = "
			SELECT
				top 6
				coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,convert(varchar(10), EP.EvnPrescr_setDT, 104) as date_start
				,convert(varchar(10), ECT.EvnCourseTreat_Duration, 104) + ' ' + DTP.DurationType_Nick AS duration
				,convert(varchar(10), duration.date_end, 104) as date_end

			FROM v_EvnPrescr EP with (nolock)

				--name
				inner join v_EvnPrescrTreat EPT with (nolock) on EPT.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreatDrug ec_drug with (nolock) on ec_drug.EvnCourseTreat_id = EPT.EvnCourse_id
				left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ec_drug.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = isnull(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)

				--duration
				inner join v_EvnCourseTreat ECT with (nolock) on ECT.EvnCourseTreat_id = EPT.EvnCourse_id
				left join DurationType DTP with (nolock) on ECT.DurationType_id = DTP.DurationType_id

				outer apply (
					select
						case
							when ECT.DurationType_id = 1 then DATEADD(DAY, ECT.EvnCourseTreat_Duration, EP.EvnPrescr_setDT) --день
							when ECT.DurationType_id = 2 then DATEADD(DAY, ECT.EvnCourseTreat_Duration*7, EP.EvnPrescr_setDT) --неделя
							when ECT.DurationType_id = 3 then DATEADD(MONTH, ECT.EvnCourseTreat_Duration, EP.EvnPrescr_setDT) --месяц
						end as date_end
				) duration -- дата окончания назанчения

			WHERE
				EP.Person_id=:Person_id
				and EP.PrescriptionType_id = 5 --лекарство
				and EP.PrescriptionStatusType_id != 3 --рабочее
				and duration.date_end > GETDATE() --просрочка
		";//exit($sql);
		$result = $this->db->query($sql, array(
			"Person_id" => $data["Person_id"],
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response),
			"success" => true
		);
	}

	/**
	 * yl:5588 проверка массива лекарственных наначений для пациента из пакета
	 */
	public function checkPersonPrescrTreatPacket($data, $mnn_arr){
		$res = array(
			"totalCount" => 0,//количество действующих назначений лекарств у пациента
			"allergic" => array(),//аллергия на препараты из пакета
			"reaction" => array(),//пересечение действующих назначений для пациента на препараты из пакете
			"success" => true
		);

		//проверка на количество уже назначенных лекарств для пациента
		if (count($mnn_arr)>0){//если в пакете были лекарства
			$checkPersonPrescrTreat = $this->checkPersonPrescrTreat(array("Person_id" => $data["Person_id"]));
			if (is_array($checkPersonPrescrTreat)) {
				$res["totalCount"] = $checkPersonPrescrTreat["totalCount"];
			};
		} 

		//цикл по лекарствам из пакета
		foreach ($mnn_arr as $DrugComplexMnn_id){
			//проверка на аллергии 
			$this->load->model("PersonAllergicReaction_model", "PersonAllergicReaction_model");
			$allergic=$this->PersonAllergicReaction_model->checkPersonAllergicReaction(array(
				"Person_id" => $data["Person_id"],
				"DrugComplexMnn_id" => $DrugComplexMnn_id
			));
			if($allergic){
				//получим название препарата
				$sql = "
					SELECT TOP 1 
						coalesce(dcm.DrugComplexMnn_RusName, Drug.Drug_Name, '') as Drug_Name
					FROM rls.v_DrugComplexMnn dcm with (nolock)
						left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = :DrugComplexMnn_id
					WHERE 
						dcm.DrugComplexMnn_id = :DrugComplexMnn_id
				";
				$result = $this->db->query($sql, array("DrugComplexMnn_id" => $DrugComplexMnn_id));
				if (!is_object($result)) throw new Exception("Ошибка получения названия препарата $DrugComplexMnn_id в checkPersonPrescrTreatPacket");
				$response = $result->result("array");
				if ( is_array($response) && count($response) > 0) {
					$res["allergic"][]=array(
						"DrugComplexMnn_id"=>$DrugComplexMnn_id,
						"Drug_Name"=>array_values($response)[0]["Drug_Name"]
					);
				};
			};

			//проверка на пересечение 
			$reaction=$this->PersonAllergicReaction_model->checkPersonDrugReaction(array(
				"Evn_id" => $data["Evn_pid"],
				"DrugComplexMnn_id" => $DrugComplexMnn_id
			));
			if(!empty($reaction["LS_LINK_ID"])){
				$res["reaction"][] = $reaction;
			};
		}

		return $res;
	}

}

