<?php defined('BASEPATH') or die ('No direct script access allowed');

class MzDrugRequest_model extends swModel {
	private $Server_id;//источник данных
	private $DrugRequest_id;//идентификатор
	private $DrugRequestPeriod_id;//идентификатор справочника медикаментов: период заявки
	private $DrugRequestStatus_id;//идентификатор справочника медикаментов: статус заявки
	private $DrugRequest_Name;//наименование
	private $Lpu_id;//идентификатор справочника ЛПУ
	private $LpuSection_id;//идентификатор справочника отделений ЛПУ
	private $MedPersonal_id;//идентификатор справочника медицинских работников
	private $DrugRequest_Summa;//сумма по строке заявки
	private $DrugRequest_YoungChildCount;//количество прикрепленных детей по заявке
	private $PersonRegisterType_id;//Тип регистра
	private $DrugRequest_IsSigned;//Признак подписания документа
	private $pmUser_signID;//Пользователь, подписавший документ
	private $DrugRequest_signDT;//Дата подписания
	private $DrugRequest_Version;//Версия документа
	private $DrugRequestKind_id;//Вид заявки
	private $DrugRequestProperty_id;//Спиок медикаментов
	private $DrugRequestPropertyFed_id;//Спиок медикаментов
	private $DrugRequestPropertyReg_id;//Спиок медикаментов
	private $DrugGroup_id;//Группа медикаментов
	private $pmUser_id;//Идентификатор пользователя системы Промед
	private $DrugRequestQuota_Person;//Лимит финансирования на одного льготника
	private $DrugRequestQuota_PersonFed;//Лимит финансирования на одного льготника
	private $DrugRequestQuota_PersonReg;//Лимит финансирования на одного льготника
	private $DrugRequestQuota_Total;//Лимит финансирования по заявка в целом
	private $DrugRequestQuota_TotalFed;//Лимит финансирования по заявка в целом
	private $DrugRequestQuota_TotalReg;//Лимит финансирования по заявка в целом
	private $DrugRequestQuota_IsPersonalOrderObligatory;//флаг "Персональная разнарядка обязательна"

	/**
	 * Получение параметра
	 */
	public function getServer_id() { return $this->Server_id;}

	/**
	 * Установка параметра
	 */
	public function setServer_id($value) { $this->Server_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_id() { return $this->DrugRequest_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_id($value) { $this->DrugRequest_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestPeriod_id() { return $this->DrugRequestPeriod_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestPeriod_id($value) { $this->DrugRequestPeriod_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestStatus_id() { return $this->DrugRequestStatus_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestStatus_id($value) { $this->DrugRequestStatus_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_Name() { return $this->DrugRequest_Name;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_Name($value) { $this->DrugRequest_Name = $value; }

	/**
	 * Получение параметра
	 */
	public function getLpu_id() { return $this->Lpu_id;}

	/**
	 * Установка параметра
	 */
	public function setLpu_id($value) { $this->Lpu_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getLpuSection_id() { return $this->LpuSection_id;}

	/**
	 * Установка параметра
	 */
	public function setLpuSection_id($value) { $this->LpuSection_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getMedPersonal_id() { return $this->MedPersonal_id;}

	/**
	 * Установка параметра
	 */
	public function setMedPersonal_id($value) { $this->MedPersonal_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_Summa() { return $this->DrugRequest_Summa;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_Summa($value) { $this->DrugRequest_Summa = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_YoungChildCount() { return $this->DrugRequest_YoungChildCount;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_YoungChildCount($value) { $this->DrugRequest_YoungChildCount = $value; }

	/**
	 * Получение параметра
	 */
	public function getPersonRegisterType_id() { return $this->PersonRegisterType_id;}

	/**
	 * Установка параметра
	 */
	public function setPersonRegisterType_id($value) { $this->PersonRegisterType_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_IsSigned() { return $this->DrugRequest_IsSigned;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_IsSigned($value) { $this->DrugRequest_IsSigned = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_signID() { return $this->pmUser_signID;}

	/**
	 * Установка параметра
	 */
	public function setpmUser_signID($value) { $this->pmUser_signID = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_signDT() { return $this->DrugRequest_signDT;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_signDT($value) { $this->DrugRequest_signDT = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequest_Version() { return $this->DrugRequest_Version;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequest_Version($value) { $this->DrugRequest_Version = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestKind_id() { return $this->DrugRequestKind_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestKind_id($value) { $this->DrugRequestKind_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestProperty_id() { return $this->DrugRequestProperty_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestProperty_id($value) { $this->DrugRequestProperty_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestPropertyFed_id() { return $this->DrugRequestPropertyFed_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestPropertyFed_id($value) { $this->DrugRequestPropertyFed_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestPropertyReg_id() { return $this->DrugRequestPropertyReg_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestPropertyReg_id($value) { $this->DrugRequestPropertyReg_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugGroup_id() { return $this->DrugGroup_id;}

	/**
	 * Установка параметра
	 */
	public function setDrugGroup_id($value) { $this->DrugGroup_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка параметра
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_Person() { return $this->DrugRequestQuota_Person;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_Person($value) { $this->DrugRequestQuota_Person = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_PersonFed() { return $this->DrugRequestQuota_PersonFed;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_PersonFed($value) { $this->DrugRequestQuota_PersonFed = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_PersonReg() { return $this->DrugRequestQuota_PersonReg;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_PersonReg($value) { $this->DrugRequestQuota_PersonReg = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_Total() { return $this->DrugRequestQuota_Total;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_Total($value) { $this->DrugRequestQuota_Total = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_TotalFed() { return $this->DrugRequestQuota_TotalFed;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_TotalFed($value) { $this->DrugRequestQuota_TotalFed = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_TotalReg() { return $this->DrugRequestQuota_TotalReg;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_TotalReg($value) { $this->DrugRequestQuota_TotalReg = $value; }

	/**
	 * Получение параметра
	 */
	public function getDrugRequestQuota_IsPersonalOrderObligatory() { return $this->DrugRequestQuota_IsPersonalOrderObligatory;}

	/**
	 * Установка параметра
	 */
	public function setDrugRequestQuota_IsPersonalOrderObligatory($value) { $this->DrugRequestQuota_IsPersonalOrderObligatory = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Функция добавления пациентов имеющих прикрепление к участку, но отстутсвующих в участковой заявке
	 */
	function addDrugRequestPersonOrderMissingPerson($data) {
		$result = array();

		try {
			$this->beginTransaction();

			//получение данных заявки
			$query = "
                select
                    dr.DrugRequest_id,
                    dr.LpuRegion_id,
                    dr.MedPersonal_id,
                    drs.DrugRequestStatus_Code
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                where
                    dr.DrugRequest_id = :DrugRequest_id;
            ";
			$dr_data = $this->getFirstRowFromQuery($query, array(
				'DrugRequest_id' => $data['DrugRequest_id']
			));
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("При получении данных заявки произошла ошибка");
			}

			//проверяем статус заявки
			if ($dr_data['DrugRequestStatus_Code'] != '1') { //1 - Начальная
				throw new Exception("Недопустимый статус заявки");
			}

			//проверяем статус заявки
			if (empty($dr_data['LpuRegion_id'])) { //1 - Начальная
				throw new Exception("Операция доступна только для участковых заявок");
			}

			//получение списка пациентов для добавления
			$person_list = $this->getDrugRequestPersonOrderMissingPerson($dr_data['DrugRequest_id']);
			if (is_array($person_list)) {
				foreach($person_list as $person_data) {
					$save_result = $this->saveObject('DrugRequestPersonOrder', array(
						'DrugRequest_id' => $dr_data['DrugRequest_id'],
						'MedPersonal_id' => $dr_data['MedPersonal_id'],
						'Person_id' => $person_data['Person_id']
					));
					if (empty($save_result['DrugRequestPersonOrder_id'])) {
						throw new Exception(!empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "При сохранении строки разнарядки произошла ошибка");
					}
				}
			}

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['successs'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				Server_id,
				DrugRequest_id,
				dr.DrugRequestPeriod_id,
				dr.DrugRequest_Name, dr.Lpu_id, dr.LpuSection_id, dr.MedPersonal_id,
				dr.DrugRequest_Summa,
				dr.DrugRequest_YoungChildCount,
				dr.DrugRequestKind_id,
				dr.DrugRequestProperty_id,
				dr.DrugRequestPropertyFed_id,
				dr.DrugRequestPropertyReg_id,
				dr.DrugGroup_id,
				dr.PersonRegisterType_id, dr.DrugRequest_IsSigned, dr.pmUser_signID, dr.DrugRequest_signDT, dr.DrugRequest_Version,
				drq.DrugRequestQuota_Person,
				drq.DrugRequestQuota_Total,
				drqf.DrugRequestQuota_Person as DrugRequestQuota_PersonFed,
				drqf.DrugRequestQuota_Total as DrugRequestQuota_TotalFed,
				drqr.DrugRequestQuota_Person as DrugRequestQuota_PersonReg,
				drqr.DrugRequestQuota_Total as DrugRequestQuota_TotalReg,
				is_obl.YesNo_Code as DrugRequestQuota_IsPersonalOrderObligatory,
				datediff(month, period.DrugRequestPeriod_begDate, dateadd(day, 1, period.DrugRequestPeriod_endDate)) as DrugRequestPeriod_MonthCount,
				status.DrugRequestStatus_id,
				status.DrugRequestStatus_Name,
				status.DrugRequestStatus_Code
			from
				dbo.v_DrugRequest dr with(nolock)
				left join dbo.DrugRequestQuota drq with (nolock) on
					drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
					isnull(drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
					isnull(drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
					isnull(drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
					drq.DrugFinance_id is null
				left join dbo.DrugRequestQuota drqf with (nolock) on
					drqf.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
					isnull(drqf.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
					isnull(drqf.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
					isnull(drqf.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
					drqf.DrugFinance_id = 3
				left join dbo.DrugRequestQuota drqr with (nolock) on
					drqr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
					isnull(drqr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
					isnull(drqr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
					isnull(drqr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
					drqr.DrugFinance_id = 27
				left join dbo.DrugRequestPeriod period with (nolock) on period.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join dbo.DrugRequestStatus status with (nolock) on status.DrugRequestStatus_id = dr.DrugRequestStatus_id
				outer apply (
					select (case
						when dr.PersonRegisterType_id = 1 then isnull(drqf.DrugRequestQuota_IsPersonalOrderObligatory, drqr.DrugRequestQuota_IsPersonalOrderObligatory)
						else drq.DrugRequestQuota_IsPersonalOrderObligatory
					end) as DrugRequestQuota_IsPersonalOrderObligatory					
				) drq_ipoo
				left join v_YesNo is_obl on is_obl.YesNo_id = isnull(drq_ipoo.DrugRequestQuota_IsPersonalOrderObligatory, 1)
			where
				DrugRequest_id = :DrugRequest_id
		";
		$r = $this->db->query($q, array('DrugRequest_id' => $this->DrugRequest_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->Server_id = $r[0]['Server_id'];
				$this->DrugRequest_id = $r[0]['DrugRequest_id'];
				$this->DrugRequestPeriod_id = $r[0]['DrugRequestPeriod_id'];
				$this->DrugRequestStatus_id = $r[0]['DrugRequestStatus_id'];
				$this->DrugRequest_Name = $r[0]['DrugRequest_Name'];
				$this->Lpu_id = $r[0]['Lpu_id'];
				$this->LpuSection_id = $r[0]['LpuSection_id'];
				$this->MedPersonal_id = $r[0]['MedPersonal_id'];
				$this->DrugRequest_Summa = $r[0]['DrugRequest_Summa'];
				$this->DrugRequest_YoungChildCount = $r[0]['DrugRequest_YoungChildCount'];
				$this->PersonRegisterType_id = $r[0]['PersonRegisterType_id'];
				$this->DrugRequest_IsSigned = $r[0]['DrugRequest_IsSigned'];
				$this->pmUser_signID = $r[0]['pmUser_signID'];
				$this->DrugRequest_signDT = $r[0]['DrugRequest_signDT'];
				$this->DrugRequest_Version = $r[0]['DrugRequest_Version'];
				$this->DrugRequestProperty_id = $r[0]['DrugRequestProperty_id'];
				$this->DrugRequestPropertyFed_id = $r[0]['DrugRequestPropertyFed_id'];
				$this->DrugRequestPropertyReg_id = $r[0]['DrugRequestPropertyReg_id'];
				$this->DrugRequestKind_id = $r[0]['DrugRequestKind_id'];
				$this->DrugGroup_id = $r[0]['DrugGroup_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function loadConsolidatedDrugRequest() {
		$q = "
			select
				dr.DrugRequest_id,
				limit.sum_total as DrugRequestQuota_SumTotal,
				region_request.DrugRequest_id as RegionDrugRequest_id,
                region_request.DrugRequestQuota_Total
			from
				dbo.DrugRequest dr with (nolock)
				outer apply (
					select
						sum(drq.DrugRequestQuota_Total) as sum_total
					from
						dbo.DrugRequestPurchase drp with(nolock)
						left join dbo.DrugRequest dr2 with (nolock) on dr2.DrugRequest_id = drp.DrugRequest_lid
						left join dbo.DrugRequestQuota drq with (nolock) on
						    drq.DrugFinance_id is null and
                            drq.DrugRequestPeriod_id = dr2.DrugRequestPeriod_id and
                            isnull(drq.PersonRegisterType_id, 0) = isnull(dr2.PersonRegisterType_id, 0) and
                            isnull(drq.DrugGroup_id, 0) = isnull(dr2.DrugGroup_id, 0) and
                            isnull(drq.DrugRequestKind_id, 0) = isnull(dr2.DrugRequestKind_id, 0)
					where
						drp.DrugRequest_id = dr.DrugRequest_id
				) limit
				outer apply (
					select top 1
                        dr2.DrugRequest_id,
                        drq.DrugRequestQuota_Total
					from
						dbo.DrugRequestPurchase drp with(nolock)
						left join dbo.DrugRequest dr2 with (nolock) on dr2.DrugRequest_id = drp.DrugRequest_lid
						left join dbo.DrugRequestQuota drq with (nolock) on
						    drq.DrugFinance_id is null and
                            drq.DrugRequestPeriod_id = dr2.DrugRequestPeriod_id and
                            isnull(drq.PersonRegisterType_id, 0) = isnull(dr2.PersonRegisterType_id, 0) and
                            isnull(drq.DrugGroup_id, 0) = isnull(dr2.DrugGroup_id, 0) and
                            isnull(drq.DrugRequestKind_id, 0) = isnull(dr2.DrugRequestKind_id, 0)
					where
						drp.DrugRequest_id = dr.DrugRequest_id
			        order by
			            drp.DrugRequestPurchase_id
				) region_request
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
		$r = $this->db->query($q, array('DrugRequest_id' => $this->DrugRequest_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для формы редактирования участка заявки
	 */
	function loadDrugRequestLpuRegion() {
		$query = "
			select
				dr.DrugRequest_id,
				dr.Lpu_id,
				dr.LpuRegion_id,
				(
					isnull('уч. № '+lr.LpuRegion_Name+isnull(' (' + lrt.LpuRegionType_Name + ')', '')+', ', '') +					
					isnull(mp.Person_Fio, '')+
					isnull(', '+mp.Dolgnost_Name, '')+
					isnull(', '+ls.LpuSection_Name, '')+
					isnull(', '+lu.LpuUnit_Name, '')
				) as DrugRequest_Name,
				msr.LpuRegion_id as DefaultLpuRegion_id
			from
				v_DrugRequest dr with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = dr.Lpu_id
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = dr.LpuRegion_id
				left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = dr.LpuSection_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = dr.LpuUnit_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = dr.MedPersonal_id
				outer apply (
					select top 1
						i_msr.LpuRegion_id
					from
						v_MedStaffRegion i_msr with (nolock)
					where
						i_msr.Lpu_id = mp.Lpu_id and
						i_msr.MedPersonal_id = mp.MedPersonal_id and
						isnull(i_msr.MedStaffRegion_isMain, 1) = 2 -- основной врач на участке
				) msr
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
		$lr_data = $this->queryResult($query, array(
			'DrugRequest_id' => $this->DrugRequest_id
		));
		return is_array($lr_data) && count($lr_data) > 0 ? $lr_data : false;
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$declare = array();
		$set = array();
		$select = array();
		$join = array();
		$where = array();
		$p = array();
		$list_type = isset($filter['list_type']) ? $filter['list_type'] : 'region';
        $region =  $filter['session']['region']['nick'];

		if (isset($filter['Year']) && $filter['Year']) {
			$where[] = 'DATEPART(year,DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) = :Year';
			$p['Year'] = $filter['Year'];
		}

		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id'] > 0) {
			$where[] = 'v_DrugRequest.DrugRequestPeriod_id = :DrugRequestPeriod_id';
		}

		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id'] != -1) {
			$where[] = 'isnull(v_DrugRequest.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0)';
		}

		if (isset($filter['DrugRequestKind_id']) && $filter['DrugRequestKind_id'] != -1) {
			$where[] = 'isnull(v_DrugRequest.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0)';
		}

		if (isset($filter['DrugGroup_id']) && $filter['DrugGroup_id'] != -1) {
			$where[] = 'isnull(v_DrugRequest.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)';
		}

        if (isset($filter['DrugRequest_Version']) && $filter['DrugRequest_Version'] != -1) {
            $where[] = 'isnull(v_DrugRequest.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0)';
        } else {
            if($list_type == 'region') {
                $where[] = '(v_DrugRequest.DrugRequest_Version is null or v_DrugRequest.DrugRequest_Version = 1)';
            } else {
                $where[] = 'v_DrugRequest.DrugRequest_Version is null';
            }
        }

		if (isset($filter['OrgServiceTerr_Org_id']) && $filter['OrgServiceTerr_Org_id'] > 0) {
			$where[] = '
				exists(
					select
						ost2.OrgServiceTerr_id
					from
						OrgServiceTerr ost with(nolock)
						left join OrgServiceTerr ost2 with(nolock) on
							ost2.KLCountry_id = ost.KLCountry_id and
							ost2.KLRgn_id = ost.KLRgn_id and
							(ost.KLSubRgn_id is null or isnull(ost2.KLSubRgn_id, 0) = isnull(ost.KLSubRgn_id, 0)) and
							(ost.KLCity_id is null or isnull(ost2.KLCity_id, 0) = isnull(ost.KLCity_id, 0)) and
							(ost.KLTown_id is null or isnull(ost2.KLTown_id, 0) = isnull(ost.KLTown_id, 0))
					where
						ost.Org_id = :OrgServiceTerr_Org_id and
						ost2.Org_id = Lpu_id_ref.Org_id
				)
			';
		}

        //получаем идентификатор заявочной кампании
        if (in_array($list_type, array('lpu', 'medpersonal'))) {
            $reg_dr_data = $this->getRegionDrugRequestByParams($filter);
            $filter['RegionDrugRequest_id'] = !empty($reg_dr_data['DrugRequest_id']) ? $reg_dr_data['DrugRequest_id'] : null;
        }

		switch($list_type) {
			case 'region':
				$select[] = "SvodDrugRequest.DrugRequest_id as SvodDrugRequest_id";
				$select[] = "SvodDrugRequest.DrugRequest_Name as SvodDrugRequest_Name";
				$select[] = "DrugRequestRegionFirstCopy.DrugRequest_id as DrugRequestRegionFirstCopy_id";
				$select[] = "MoDrugRequest.Count as MoDrugRequest_Count";
				$select[] = "cast(case when DrugRequestSum.Summa > 0 then DrugRequestSum.Summa else null end as decimal(14,2)) as DrugRequest_Summa";
				$select[] = "cast(case when DrugRequestSumFed.Summa > 0 then DrugRequestSumFed.Summa else null end as decimal(14,2)) as DrugRequest_SummaFed";
				$select[] = "cast(case when DrugRequestSumReg.Summa > 0 then DrugRequestSumReg.Summa else null end as decimal(14,2)) as DrugRequest_SummaReg";
				$select[] = "DrugRequestPropertyOrg.Org_Name as DrugRequestProperty_OrgName";
				$select[] = "DrugGroup.DrugGroup_Name";
                if ($region == 'ufa') {
                    $select[] = "
                        (
                            case
                                when v_DrugRequest.DrugRequest_Version = 1 then 'Прогноз регионального лекарственного обеспечения'
                                when DrugRequestRegionFirstCopy.DrugRequest_id is not null then 'Лимит.потр.'
                                else ''
                            end
                        ) as FirstCopy_Inf
                    ";
                } else {
                    $select[] = "
                        (
                            case
                                when v_DrugRequest.DrugRequest_Version = 1 then 'Копия 1'
                                else ''
                            end
                        ) as FirstCopy_Inf
                    ";
                }

				$join[] = "
					outer apply	(
						select top 1
							sdr.DrugRequest_id,
							sdr.DrugRequest_Name
						from
							v_DrugRequestPurchase drp with (nolock)
							left join v_DrugRequest sdr with (nolock) on sdr.DrugRequest_id = drp.DrugRequest_id
						where
							drp.DrugRequest_lid = v_DrugRequest.DrugRequest_id
					) SvodDrugRequest
				";

				$join[] = "
					outer apply	(
						select top 1
							count(DrugRequest_id) as Count
						from
							v_DrugRequest mdr with(nolock)
						where
							mdr.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(mdr.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
							isnull(mdr.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(mdr.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							mdr.DrugRequest_Version is null and
							mdr.Lpu_id is not null and
							mdr.MedPersonal_id is null
					) MoDrugRequest
				";

				$join[] = "
					outer apply (
						select
							sum(drr2.DrugRequestRow_Summa) as Summa
						from
							v_DrugRequest dr2 with (nolock)
							left join v_DrugRequestRow drr2 with (nolock) on drr2.DrugRequest_id = dr2.DrugRequest_id
						where
							dr2.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(dr2.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(dr2.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(dr2.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
							isnull(dr2.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							dr2.MedPersonal_id is not null and
							drr2.DrugRequestRow_id is not null 
					) DrugRequestSum
				";

				$join[] = "
					outer apply (
						select
							sum(drr2.DrugRequestRow_Summa) as Summa
						from
							v_DrugRequest dr2 with (nolock)
							left join v_DrugRequestRow drr2 with (nolock) on drr2.DrugRequest_id = dr2.DrugRequest_id
						where
							dr2.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(dr2.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(dr2.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(dr2.PersonRegisterType_id, 0) = 1 and
							isnull(dr2.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							dr2.MedPersonal_id is not null and
							drr2.DrugRequestRow_id is not null and 
							drr2.DrugFinance_id = 3
					) DrugRequestSumFed
				";

				$join[] = "
					outer apply (
						select
							sum(drr2.DrugRequestRow_Summa) as Summa
						from
							v_DrugRequest dr2 with (nolock)
							left join v_DrugRequestRow drr2 with (nolock) on drr2.DrugRequest_id = dr2.DrugRequest_id
						where
							dr2.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(dr2.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(dr2.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(dr2.PersonRegisterType_id, 0) = 1 and
							isnull(dr2.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							dr2.MedPersonal_id is not null and
							drr2.DrugRequestRow_id is not null and
							drr2.DrugFinance_id = 27
					) DrugRequestSumReg
				";
                
				$join[] = "
					outer apply (
						select top 1
							fc_dr.DrugRequest_id
						from
							v_DrugRequest fc_dr with(nolock)
							left join v_DrugRequestCategory fc_drc with(nolock) on fc_drc.DrugRequestCategory_id = fc_dr.DrugRequestCategory_id
						where
							fc_dr.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(fc_dr.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
							isnull(fc_dr.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(fc_dr.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							fc_dr.DrugRequest_Version = 1 and
							fc_drc.DrugRequestCategory_SysNick = 'region' and
							fc_dr.DrugRequest_id <> v_DrugRequest.DrugRequest_id
					) DrugRequestRegionFirstCopy
				";

                $join[] = "
				    outer apply (
                        select top 1
                            i_dr_plan.DrugRequestPlan_FedKolvo,
                            i_dr_plan.DrugRequestPlan_RegKolvo,
                            i_dr_plan.DrugRequestPlan_Kolvo,
                            i_dr_plan.DrugRequestPlan_FedSumma,
                            i_dr_plan.DrugRequestPlan_RegSumma,
                            i_dr_plan.DrugRequestPlan_Summa,
                            i_dr_plan.DrugRequestPlan_CountFed,
                            i_dr_plan.DrugRequestPlan_CountReg,
                            i_dr_plan.DrugRequestPlan_CountReq
                        from
                            v_DrugRequestPlan i_dr_plan with (nolock)
                        where
                            i_dr_plan.DrugRequest_id = v_DrugRequest.DrugRequest_id and
                            i_dr_plan.Lpu_id is null and
                            i_dr_plan.LpuRegion_id is null
				    ) DrugRequestPlanParams
				";

				$join[] = "left join v_DrugRequestProperty DrugRequestProperty with (nolock) on DrugRequestProperty.DrugRequestProperty_id = v_DrugRequest.DrugRequestProperty_id";
				$join[] = "left join v_DrugRequestProperty DrugRequestPropertyFed with (nolock) on DrugRequestPropertyFed.DrugRequestProperty_id = v_DrugRequest.DrugRequestPropertyFed_id";
				$join[] = "left join v_DrugRequestProperty DrugRequestPropertyReg with (nolock) on DrugRequestPropertyReg.DrugRequestProperty_id = v_DrugRequest.DrugRequestPropertyReg_id";
				$join[] = "left join v_Org DrugRequestPropertyOrg with (nolock) on DrugRequestPropertyOrg.Org_id = coalesce(DrugRequestProperty.Org_id, DrugRequestPropertyFed.Org_id, DrugRequestPropertyReg.Org_id)";
				$join[] = "left join v_DrugGroup DrugGroup with (nolock) on DrugGroup.DrugGroup_id = v_DrugRequest.DrugGroup_id";

				if(!empty($filter['fromLpuPharmacyHead']) && !empty($filter['DrugRequestProperty_Org_id'])){
					$select[] = "case when v_DrugRequest.DrugRequestStatus_id = 1 then '01.01.1900' else convert(varchar,v_DrugRequest.DrugRequest_insDT,104) end as DrugRequest_insDT";
					$select[] = "MoDrugRequestCur.Count as MoDrugRequestCur_Count";
					$join[] = "
						outer apply	(
							select top 1
								count(DrugRequest_id) as Count
							from
								v_DrugRequest mdr with(nolock)
							where
								mdr.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
								isnull(mdr.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
								isnull(mdr.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
								isnull(mdr.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
								isnull(mdr.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
								mdr.Lpu_id = :Lpu_id and
								mdr.MedPersonal_id is null
						) MoDrugRequestCur
					";
					$where[] = "((DrugRequestCategory_ref.DrugRequestCategory_SysNick = 'region' and DrugRequestProperty.Org_id = :DrugRequestProperty_Org_id) or (MoDrugRequestCur.Count > 0))";
					
					if (!empty($filter['DrugRequestStatus_id'])) {
						$where[] = 'v_DrugRequest.DrugRequestStatus_id = :DrugRequestStatus_id';
					}

					if (!empty($filter['Coordinator_id'])) {
						if($filter['Coordinator_id'] == 1){
							$where[] = 'isnull(DrugRequestProperty.Org_id, 0) = isnull(:DrugRequestProperty_Org_id, 0)';
						} else if($filter['Coordinator_id'] == 2){
							$where[] = 'isnull(DrugRequestProperty.Org_id, 0) <> isnull(:DrugRequestProperty_Org_id, 0)';
						}
					}

					if (!empty($filter['DrugRequest_Summa1'])) {
						$where[] = 'DrugRequestSum.Summa >= :DrugRequest_Summa1';
					}

					if (!empty($filter['DrugRequest_Summa2'])) {
						$where[] = 'DrugRequestSum.Summa <= :DrugRequest_Summa2';
					}

				} else {
					$where[] = "DrugRequestCategory_ref.DrugRequestCategory_SysNick = 'region'";

	                if (!empty($filter['DrugRequestProperty_Org_id'])) {
	                    $where[] = 'DrugRequestProperty.Org_id = :DrugRequestProperty_Org_id';
	                }
				}
				
				break;
			case 'lpu':
				$select[] = "DrugRequestRowStats.Summa as DrugRequestRow_Summa";
				$select[] = "DrugRequestRowStatsFed.Summa as DrugRequestRow_SummaFed";
				$select[] = "DrugRequestRowStatsReg.Summa as DrugRequestRow_SummaReg";

				$form_filter = "1=1";
				if (!empty($filter['DrugRequestStatus_id'])) {
					$form_filter .= " and v_DrugRequest.DrugRequestStatus_id = :DrugRequestStatus_id";
				}
				if (!empty($filter['KLAreaStat_id'])) {
					$join[] = "
						outer apply (
							select top 1
								OST.OrgServiceTerr_id
							from
								v_OrgServiceTerr OST (nolock)
								inner join v_KLAreaStat KAS (nolock) on
									ISNULL(OST.KLRgn_id, 0) = ISNULL(KAS.KLRgn_id, 0)
									and ISNULL(OST.KLCity_id, 0) = ISNULL(KAS.KLCity_id, 0)
									and ISNULL(OST.KLTown_id, 0) = ISNULL(KAS.KLTown_id, 0)
							where
								OST.Org_id = Lpu_id_ref.Org_id and KAS.KLAreaStat_id = :KLAreaStat_id
						) OST
					";
					$form_filter .= " and OST.OrgServiceTerr_id IS NOT NULL";
				}
				$select[] = "case when ({$form_filter}) then 1 else 0 end as Filtered";

				$join[] = "
					outer apply	(
						select
							sum(DrugRequestRow.DrugRequestRow_Summa) as Summa
						from
							DrugRequest with (nolock)
							left join v_DrugRequestRow DrugRequestRow with (nolock) on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
						where
							DrugRequest.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(DrugRequest.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
							isnull(DrugRequest.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(DrugRequest.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(DrugRequest.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							DrugRequest.Lpu_id = v_DrugRequest.Lpu_id and
							DrugRequest.MedPersonal_id is not null and
							DrugRequestRow.DrugRequestRow_id is not null
					) DrugRequestRowStats
				";

				$join[] = "
					outer apply	(
						select
							sum(DrugRequestRow.DrugRequestRow_Summa) as Summa
						from
							DrugRequest with (nolock)
							left join v_DrugRequestRow DrugRequestRow with (nolock) on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
						where
							DrugRequest.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(DrugRequest.PersonRegisterType_id, 0) = 1 and
							isnull(DrugRequest.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(DrugRequest.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(DrugRequest.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							DrugRequest.Lpu_id = v_DrugRequest.Lpu_id and
							DrugRequest.MedPersonal_id is not null and
							DrugRequestRow.DrugRequestRow_id is not null and
							DrugRequestRow.DrugFinance_id = 3
					) DrugRequestRowStatsFed
				";

				$join[] = "
					outer apply	(
						select
							sum(DrugRequestRow.DrugRequestRow_Summa) as Summa
						from
							DrugRequest with (nolock)
							left join v_DrugRequestRow DrugRequestRow with (nolock) on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
						where
							DrugRequest.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
							isnull(DrugRequest.PersonRegisterType_id, 0) = 1 and
							isnull(DrugRequest.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
							isnull(DrugRequest.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
							isnull(DrugRequest.DrugRequest_Version, 0) = isnull(v_DrugRequest.DrugRequest_Version, 0) and
							DrugRequest.Lpu_id = v_DrugRequest.Lpu_id and
							DrugRequest.MedPersonal_id is not null and
							DrugRequestRow.DrugRequestRow_id is not null and
							DrugRequestRow.DrugFinance_id = 27
					) DrugRequestRowStatsReg
				";

                $join[] = "
				    outer apply (
                        select top 1
                            i_dr_plan.DrugRequestPlan_FedKolvo,
                            i_dr_plan.DrugRequestPlan_RegKolvo,
                            i_dr_plan.DrugRequestPlan_Kolvo,
                            i_dr_plan.DrugRequestPlan_FedSumma,
                            i_dr_plan.DrugRequestPlan_RegSumma,
                            i_dr_plan.DrugRequestPlan_Summa,
                            i_dr_plan.DrugRequestPlan_CountFed,
                            i_dr_plan.DrugRequestPlan_CountReg,
                            i_dr_plan.DrugRequestPlan_CountReq
                        from
                            v_DrugRequestPlan i_dr_plan with (nolock)
                        where
                            i_dr_plan.DrugRequest_id = :RegionDrugRequest_id and
                            i_dr_plan.Lpu_id = v_DrugRequest.Lpu_id and
                            i_dr_plan.LpuRegion_id is null
				    ) DrugRequestPlanParams
				";

				$where[] = 'v_DrugRequest.Lpu_id is not null';
				$where[] = 'v_DrugRequest.LpuSection_id is null';
				$where[] = 'v_DrugRequest.MedPersonal_id is null';
				break;
			case 'medpersonal':
				$select[] = "rtrim(LpuUnit_id_ref.LpuUnit_Name) as LpuUnit_Name";
				$select[] = "rtrim(LpuSection_id_ref.LpuSection_Name) as LpuSection_Name";
				$select[] = "rtrim(LpuRegion_id_ref.LpuRegion_Name) as LpuRegion_Name";
				$select[] = "(
				    rtrim(MedPersonal_id_ref.Person_FIO)+
                    (case
                        when is_hms.HeadMedSpecType_Name is not null then isnull(' ('+is_hms.HeadMedSpecType_Name+')', '')
                        else ''
                    end)
                ) as MedPersonal_FIO";
				$select[] = "convert(varchar(10), v_DrugRequest.DrugRequest_insDT, 104) as DrugRequest_insDT";
				$select[] = "convert(varchar(10), v_DrugRequest.DrugRequest_updDT, 104) as DrugRequest_updDT";
				$select[] = "request_summa.Summa as DrugRequest_Summa";
				$select[] = "request_summa_fed.Summa_f as DrugRequest_Summa_Fed";
				$select[] = "request_summa_reg.Summa_r as DrugRequest_Summa_Reg";

				$join[] = "
					outer apply (
						select
							sum(fs_drr.DrugRequestRow_Summa) as Summa
						from
							v_DrugRequest fs_dr with(nolock)
							left join v_DrugRequestRow fs_drr with(nolock) on fs_drr.DrugRequest_id = fs_dr.DrugRequest_id
						where
							fs_drr.Person_id is null and
							fs_dr.DrugRequest_id = v_DrugRequest.DrugRequest_id
					) request_summa
				";

				$join[] = "
					outer apply (
						select
							sum(fs_drr_f.DrugRequestRow_Summa) as Summa_f
						from
							v_DrugRequest fs_dr_f with(nolock)
							left join v_DrugRequestRow fs_drr_f with(nolock) on fs_drr_f.DrugRequest_id = fs_dr_f.DrugRequest_id
							left join v_DrugFinance df_f with (nolock) on df_f.DrugFinance_id = fs_drr_f.DrugFinance_id
						where
							fs_drr_f.Person_id is null and
							fs_dr_f.DrugRequest_id = v_DrugRequest.DrugRequest_id and
							df_f.DrugFinance_SysNick = 'fed'
					) request_summa_fed
				";

				$join[] = "
					outer apply (
						select
							sum(fs_drr_r.DrugRequestRow_Summa) as Summa_r
						from
							v_DrugRequest fs_dr_r with(nolock)
							left join v_DrugRequestRow fs_drr_r with(nolock) on fs_drr_r.DrugRequest_id = fs_dr_r.DrugRequest_id
							left join v_DrugFinance df_r with (nolock) on df_r.DrugFinance_id = fs_drr_r.DrugFinance_id
						where
							fs_drr_r.Person_id is null and
							fs_dr_r.DrugRequest_id = v_DrugRequest.DrugRequest_id and
							df_r.DrugFinance_SysNick = 'reg'
					) request_summa_reg
				";

				$join[] = "
                    outer apply ( -- проверка на включение врача заявки в перечень главных внештатных специалистов
                        select top 1
                            i_hms.HeadMedSpec_id,
                            i_hmst.HeadMedSpecType_Name
                        from
                            persis.v_MedWorker i_mw with (nolock)
                            inner join v_HeadMedSpec i_hms with (nolock) on i_hms.MedWorker_id = i_mw.MedWorker_id
                            left join v_HeadMedSpecType i_hmst with (nolock) on i_hmst.HeadMedSpecType_id = i_hms.HeadMedSpecType_id
                        where
                            i_mw.Person_id = MedPersonal_id_ref.Person_id and
                            DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
                    ) is_hms
				";

                $join[] = "
				    outer apply (
                        select top 1
                            i_dr_plan.DrugRequestPlan_FedKolvo,
                            i_dr_plan.DrugRequestPlan_RegKolvo,
                            i_dr_plan.DrugRequestPlan_Kolvo,
                            i_dr_plan.DrugRequestPlan_FedSumma,
                            i_dr_plan.DrugRequestPlan_RegSumma,
                            i_dr_plan.DrugRequestPlan_Summa,
                            i_dr_plan.DrugRequestPlan_CountFed,
                            i_dr_plan.DrugRequestPlan_CountReg,
                            i_dr_plan.DrugRequestPlan_CountReq
                        from
                            v_DrugRequestPlan i_dr_plan with (nolock)
                        where
                            i_dr_plan.DrugRequest_id = :RegionDrugRequest_id and
                            i_dr_plan.Lpu_id = v_DrugRequest.Lpu_id and
                            i_dr_plan.LpuRegion_id = v_DrugRequest.LpuRegion_id
				    ) DrugRequestPlanParams
				";

				$where[] = 'v_DrugRequest.Lpu_id = :Lpu_id';

				if (!empty($filter['LpuUnit_id']) && $filter['LpuUnit_id'] > 0) {
					$where[] = "v_DrugRequest.LpuUnit_id = :LpuUnit_id";
				}

				if (!empty($filter['LpuSection_id']) && $filter['LpuSection_id'] > 0) {
					$where[] = "v_DrugRequest.LpuSection_id = :LpuSection_id";
				}

				if (!empty($filter['MedPersonal_id']) && $filter['MedPersonal_id'] > 0) {
					$where[] = "v_DrugRequest.MedPersonal_id = :MedPersonal_id";
				} else {
					$where[] = "v_DrugRequest.MedPersonal_id is not null";
				}

				if (!empty($filter['DrugRequestStatus_id'])) {
					$where[] = "v_DrugRequest.DrugRequestStatus_id = :DrugRequestStatus_id";
				}

				if (!empty($filter['DrugFinance_id'])) {
					if($filter['DrugFinance_id'] == 3){
						$where[] = "isnull(request_summa_fed.Summa_f,0) > 0";
					} else if($filter['DrugFinance_id'] == 27) {
						$where[] = "isnull(request_summa_reg.Summa_r,0) > 0";
					}
				}

				break;
		}

		$declare_clause = count($declare) > 0 ? ', '.implode(', ', $declare) : '';
		$set_clause = implode(' ', $set);
		$select_clause = implode(', ', $select);
		if (strlen($select_clause)) {
			$select_clause = ', '.$select_clause;
		}
		$join_clause = implode(' ', $join);
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}

		$q = "
			declare
				@FedReceptFinance_id bigint,
				@RegReceptFinance_id bigint
				$declare_clause;

			set @FedReceptFinance_id = (select top 1 ReceptFinance_id from ReceptFinance with(nolock) where ReceptFinance_Code = 1);
			set @RegReceptFinance_id = (select top 1 ReceptFinance_id from ReceptFinance with(nolock) where ReceptFinance_Code = 2);
			$set_clause

			SELECT TOP 1000
				'true' as DrugRequest_isActual,
				v_DrugRequest.Server_id,
				v_DrugRequest.DrugRequest_id,
				v_DrugRequest.DrugRequestPeriod_id,
				v_DrugRequest.DrugRequestStatus_id,
				DrugRequestStatus_id_ref.DrugRequestStatus_Code,
				DrugRequestStatus_id_ref.DrugRequestStatus_Name,
				v_DrugRequest.DrugRequest_Name,
				v_DrugRequest.Lpu_id,
				v_DrugRequest.LpuRegion_id,
				v_DrugRequest.LpuSection_id,
				v_DrugRequest.MedPersonal_id,
				v_DrugRequest.DrugRequest_Summa,
				v_DrugRequest.DrugRequest_YoungChildCount,
				v_DrugRequest.PersonRegisterType_id,
				v_DrugRequest.DrugRequestKind_id,
				v_DrugRequest.DrugGroup_id,
				v_DrugRequest.DrugRequest_IsSigned,
				v_DrugRequest.pmUser_signID,
				v_DrugRequest.DrugRequest_signDT,
				v_DrugRequest.DrugRequest_Version,
				DrugRequestPeriod_id_ref.DrugRequestPeriod_Name as DrugRequestPeriod_Name,
				Lpu_id_ref.Lpu_Name Lpu_Name,
				LpuSection_id_ref.LpuSection_Name LpuSection_Name,
				PersonRegisterType_id_ref.PersonRegisterType_Name as PersonRegisterType_Name,
				DrugRequest_IsSigned_ref.YesNo_Name DrugRequest_IsSigned_Name,
				DrugRequestKind_ref.DrugRequestKind_Name as DrugRequestKind_Name,
				null as FedDrugRequestQuota_Total,
				null as RegDrugRequestQuota_Total,
				null as DrugRequestQuota_Total,
				DrugRequestPlanParams.DrugRequestPlan_FedKolvo,
				DrugRequestPlanParams.DrugRequestPlan_RegKolvo,
				DrugRequestPlanParams.DrugRequestPlan_Kolvo,
				DrugRequestPlanParams.DrugRequestPlan_FedSumma,
				DrugRequestPlanParams.DrugRequestPlan_RegSumma,
				DrugRequestPlanParams.DrugRequestPlan_Summa
				$select_clause
			FROM
				dbo.v_DrugRequest WITH (NOLOCK)
				left join dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref with (nolock) on DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id
				left join dbo.v_DrugRequestStatus DrugRequestStatus_id_ref with (nolock) on DrugRequestStatus_id_ref.DrugRequestStatus_id = v_DrugRequest.DrugRequestStatus_id
				left join dbo.v_Lpu Lpu_id_ref with (nolock) on Lpu_id_ref.Lpu_id = v_DrugRequest.Lpu_id
				left join dbo.v_LpuUnit LpuUnit_id_ref with (nolock) on LpuUnit_id_ref.LpuUnit_id = v_DrugRequest.LpuUnit_id
				left join dbo.v_LpuSection LpuSection_id_ref with (nolock) on LpuSection_id_ref.LpuSection_id = v_DrugRequest.LpuSection_id
				left join dbo.v_LpuRegion LpuRegion_id_ref with (nolock) on LpuRegion_id_ref.LpuRegion_id = v_DrugRequest.LpuRegion_id
				left join dbo.v_PersonRegisterType PersonRegisterType_id_ref with (nolock) on PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugRequest.PersonRegisterType_id
				left join dbo.v_MedPersonal MedPersonal_id_ref with (nolock) on MedPersonal_id_ref.MedPersonal_id = v_DrugRequest.MedPersonal_id and MedPersonal_id_ref.Lpu_id = v_DrugRequest.Lpu_id
				left join dbo.v_YesNo DrugRequest_IsSigned_ref with (nolock) on DrugRequest_IsSigned_ref.YesNo_id = v_DrugRequest.DrugRequest_IsSigned
				left join dbo.DrugRequestQuota fed with (nolock) on fed.PersonRegisterType_id = v_DrugRequest.PersonRegisterType_id and fed.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and fed.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'fed')
				left join dbo.DrugRequestQuota reg with (nolock) on reg.PersonRegisterType_id = v_DrugRequest.PersonRegisterType_id and reg.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and reg.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'reg')
				left join dbo.DrugRequestCategory DrugRequestCategory_ref with (nolock) on DrugRequestCategory_ref.DrugRequestCategory_id = v_DrugRequest.DrugRequestCategory_id
				left join dbo.DrugRequestKind DrugRequestKind_ref with (nolock) on DrugRequestKind_ref.DrugRequestKind_id = v_DrugRequest.DrugRequestKind_id
				$join_clause
			$where_clause
		";
		//print getDebugSQL($q, $filter); die;
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение региональной заявки
	 */
	function saveDrugRequestRegion() {
		$procedure = 'p_DrugRequest_ins';
		if ( $this->DrugRequest_id > 0 ) {
			$procedure = 'p_DrugRequest_upd';
		} else if ($this->Lpu_id <= 0 && $this->MedPersonal_id <= 0) {
			$q = "
				select
					count(dr.DrugRequest_id) as cnt
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestCategory drc with(nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					drc.DrugRequestCategory_SysNick = 'region';
			";
			$p = array(
				'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
				'PersonRegisterType_id' => $this->PersonRegisterType_id,
				'DrugRequestKind_id' => $this->DrugRequestKind_id,
				'DrugGroup_id' => $this->DrugGroup_id
			);
			$r = $this->db->query($q, $p);
			if ( is_object($r) ) {
				$result = $r->result('array');
				if (isset($result[0]) && isset($result[0]['cnt']) && $result[0]['cnt'] > 0)
					return array(array('Error_Msg' => 'Сохранение невозможно. Региональная заявка c заданными параметрами уже существует.'));
			}
		}

		//Определяем категорию заявки
		$category = '';
		if ($this->Lpu_id <= 0 && $this->MedPersonal_id <= 0) { //заявка региона
			$category = 'region';
		} else if ($this->Lpu_id > 0 && $this->MedPersonal_id <= 0) { //заявка МО
			$category = 'MO';
		} else if ($this->MedPersonal_id > 0) { //заявка врача
			$category = 'vrach';
		}

		$q = "
			declare
				@DrugRequest_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@DrugRequestStatus_id bigint,
				@DrugRequestCategory_id bigint;
				
			set @DrugRequest_id = :DrugRequest_id;
			set @DrugRequestStatus_id = isnull(:DrugRequestStatus_id, (select top 1 DrugRequestStatus_id from DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 4));
			set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = :DrugRequestCategory_SysNick);
			
			exec dbo." . $procedure . "
				@Server_id = :Server_id,
				@DrugRequest_id = @DrugRequest_id output,
				@DrugRequestPeriod_id = :DrugRequestPeriod_id,
				@DrugRequestStatus_id = @DrugRequestStatus_id,
				@DrugRequest_Name = :DrugRequest_Name,
				@Lpu_id = :Lpu_id,
				@LpuSection_id = :LpuSection_id,
				@MedPersonal_id = :MedPersonal_id,
				@DrugRequest_Summa = :DrugRequest_Summa,
				@DrugRequest_YoungChildCount = :DrugRequest_YoungChildCount,
				@PersonRegisterType_id = :PersonRegisterType_id,
				@DrugRequest_IsSigned = :DrugRequest_IsSigned,
				@pmUser_signID = :pmUser_signID,
				@DrugRequest_signDT = :DrugRequest_signDT,
				@DrugRequest_Version = :DrugRequest_Version,
				@DrugRequestKind_id = :DrugRequestKind_id,
				@DrugRequestProperty_id = :DrugRequestProperty_id,
				@DrugRequestPropertyFed_id = :DrugRequestPropertyFed_id,
				@DrugRequestPropertyReg_id = :DrugRequestPropertyReg_id,
				@DrugGroup_id = :DrugGroup_id,
				@pmUser_id = :pmUser_id,
				@DrugRequestCategory_id = @DrugRequestCategory_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugRequest_id as DrugRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'Server_id' => $this->Server_id,
			'DrugRequest_id' => $this->DrugRequest_id,
			'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
			'DrugRequestStatus_id' => $this->DrugRequestStatus_id,
			'DrugRequest_Name' => $this->DrugRequest_Name,
			'Lpu_id' => $this->Lpu_id,
			'LpuSection_id' => $this->LpuSection_id,
			'MedPersonal_id' => $this->MedPersonal_id,
			'DrugRequest_Summa' => $this->DrugRequest_Summa,
			'DrugRequest_YoungChildCount' => $this->DrugRequest_YoungChildCount,
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
			'DrugRequest_IsSigned' => $this->DrugRequest_IsSigned,
			'pmUser_signID' => $this->pmUser_signID,
			'DrugRequest_signDT' => $this->DrugRequest_signDT,
			'DrugRequest_Version' => $this->DrugRequest_Version,
			'DrugRequestKind_id' => $this->DrugRequestKind_id,
			'DrugRequestProperty_id' => !empty($this->DrugRequestProperty_id)?$this->DrugRequestProperty_id:null,
			'DrugRequestPropertyFed_id' => !empty($this->DrugRequestPropertyFed_id)?$this->DrugRequestPropertyFed_id:null,
			'DrugRequestPropertyReg_id' => !empty($this->DrugRequestPropertyReg_id)?$this->DrugRequestPropertyReg_id:null,
			'DrugGroup_id' => $this->DrugGroup_id,
			'pmUser_id' => $this->pmUser_id,
			'DrugRequestCategory_SysNick' => $category
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->DrugRequest_id = $result[0]['DrugRequest_id'];

		    if($this->PersonRegisterType_id == 1){
		    	$this->saveDrugRequestQuota(array(
					'DrugFinance_id' => 3,
					'PersonRegisterType_id' => $this->PersonRegisterType_id,
					'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
					'DrugRequestKind_id' => $this->DrugRequestKind_id,
					'DrugGroup_id' => $this->DrugGroup_id,
					'DrugRequestQuota_Person' => $this->DrugRequestQuota_PersonFed,
					'DrugRequestQuota_Total' => $this->DrugRequestQuota_TotalFed,
					'DrugRequestQuota_IsPersonalOrderObligatory' => $this->DrugRequestQuota_IsPersonalOrderObligatory
				));
				$this->saveDrugRequestQuota(array(
					'DrugFinance_id' => 27,
					'PersonRegisterType_id' => $this->PersonRegisterType_id,
					'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
					'DrugRequestKind_id' => $this->DrugRequestKind_id,
					'DrugGroup_id' => $this->DrugGroup_id,
					'DrugRequestQuota_Person' => $this->DrugRequestQuota_PersonReg,
					'DrugRequestQuota_Total' => $this->DrugRequestQuota_TotalReg,
					'DrugRequestQuota_IsPersonalOrderObligatory' => $this->DrugRequestQuota_IsPersonalOrderObligatory
				));
		    } else {
		    	$this->saveDrugRequestQuota(array(
					'DrugFinance_SysNick' => null,
					'PersonRegisterType_id' => $this->PersonRegisterType_id,
					'DrugRequestPeriod_id' => $this->DrugRequestPeriod_id,
					'DrugRequestKind_id' => $this->DrugRequestKind_id,
					'DrugGroup_id' => $this->DrugGroup_id,
					'DrugRequestQuota_Person' => $this->DrugRequestQuota_Person,
					'DrugRequestQuota_Total' => $this->DrugRequestQuota_Total,
					'DrugRequestQuota_IsPersonalOrderObligatory' => $this->DrugRequestQuota_IsPersonalOrderObligatory
				));
		    }
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Сохранение лимитов
	 */
	function saveDrugRequestQuota($data) {
		$id = 0;
		
		//если не указан идентификатор финансирования, пытаемся получить идентификатор по нику
		if (!isset($data['DrugFinance_id']) && isset($data['DrugFinance_SysNick'])) {
			$q = "
				select
					DrugFinance_id
				from 
					DrugFinance with(nolock)
				where
					DrugFinance_SysNick = :DrugFinance_SysNick
			";
			$r = $this->db->query($q, $data);
			if (is_object($r)) {
				 $result = $r->result('array');
				 if (isset($result[0]) && isset($result[0]['DrugFinance_id']))
					$data['DrugFinance_id'] = $result[0]['DrugFinance_id'];
			}
		}

		if (!isset($data['DrugFinance_id'])) {
			$data['DrugFinance_id'] = null;
		}
		if (!isset($data['DrugRequestQuota_Reserve'])) {
			$data['DrugRequestQuota_Reserve'] = null;
		}
		
		//ищем запись с нужными параметрами
		$q = "
			select
				DrugRequestQuota_id
			from 
				DrugRequestQuota
			where
				isnull(DrugFinance_id, 0) = isnull(:DrugFinance_id, 0) and
				DrugRequestPeriod_id = :DrugRequestPeriod_id and
				isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
				isnull(DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
				isnull(DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			 $result = $r->result('array');
			 if (isset($result[0]) && isset($result[0]['DrugRequestQuota_id']))
				$id = $result[0]['DrugRequestQuota_id'];
		}
		
		$data['pmUser_id'] = $this->pmUser_id;
		$data['DrugRequestQuota_id'] = $id > 0 ? $id : null;		
		
		if (!empty($data['DrugRequestQuota_Person']) || !empty($data['DrugRequestQuota_Total']) || !empty($data['DrugRequestQuota_Reserve']) || $data['DrugRequestQuota_IsPersonalOrderObligatory'] == '2') {
			$procedure = $id > 0 ? 'p_DrugRequestQuota_upd' : 'p_DrugRequestQuota_ins';
			
			$q = "
				declare
					@DrugRequestQuota_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @DrugRequestQuota_id = :DrugRequestQuota_id;
				exec dbo." . $procedure . "
					@DrugRequestQuota_id = @DrugRequestQuota_id output,
					@DrugRequestPeriod_id = :DrugRequestPeriod_id,
					@DrugFinance_id = :DrugFinance_id,
					@PersonRegisterType_id = :PersonRegisterType_id,
					@DrugRequestQuota_Person = :DrugRequestQuota_Person,
					@DrugRequestQuota_Total = :DrugRequestQuota_Total,
					@DrugRequestQuota_Reserve = :DrugRequestQuota_Reserve,
					@DrugRequestKind_id = :DrugRequestKind_id,
					@DrugGroup_id = :DrugGroup_id,
					@DrugRequestQuota_IsPersonalOrderObligatory = :DrugRequestQuota_IsPersonalOrderObligatory,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @DrugRequestQuota_id as DrugRequestQuota_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$r = $this->db->query($q, $data);
		} else if ($id > 0) { //удаляем запись о квотах финансирования
			$q = "
				declare
					@DrugRequestQuota_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @DrugRequestQuota_id = :DrugRequestQuota_id;
				exec dbo.p_DrugRequestQuota_del
					@DrugRequestQuota_id = @DrugRequestQuota_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @DrugRequestQuota_id as DrugRequestQuota_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$r = $this->db->query($q, $data);
		}
	}

	/**
	 * Сохранение общего лимита по заявке
	 */
	function saveDrugRequestQuotaTotal($data) {
		$error = array();

        $query = "
			select
			    dr.DrugRequest_id,
                dr.DrugRequestPeriod_id,
                dr.PersonRegisterType_id,
                dr.DrugRequestKind_id,
                dr.DrugGroup_id,
                quota.DrugRequestQuota_id
			from
			    v_DrugRequest dr with (nolock)
			    outer apply (
                    select top 1
                        i_drq.DrugRequestQuota_id
                    from
                        v_DrugRequestQuota i_drq with (nolock)
                    where
                        i_drq.DrugFinance_id is null and
                        i_drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                        isnull(i_drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                        isnull(i_drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                        isnull(i_drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0)
                 ) quota
			where
			     dr.DrugRequest_id = :DrugRequest_id;
		";
        $request_data = $this->getFirstRowFromQuery($query, $data);
        if (!is_array($request_data) || empty($request_data['DrugRequest_id'])) {
            $error[] = "Не удалось получить данные заявки.";
		}

        if (count($error) < 1) {
            if (!empty($request_data['DrugRequestQuota_id'])) { //редактирование данных о лимите
                $data['DrugRequestQuota_id'] = $request_data['DrugRequestQuota_id'];
            } else { //добавление данных о лимите
                $data['DrugRequestQuota_id'] = null;
                $data['DrugFinance_id'] = null;
                $data['DrugRequestPeriod_id'] = $request_data['DrugRequestPeriod_id'];
                $data['PersonRegisterType_id'] = $request_data['PersonRegisterType_id'];
                $data['DrugRequestKind_id'] = $request_data['DrugRequestKind_id'];
                $data['DrugGroup_id'] = $request_data['DrugGroup_id'];
            }

            $response = $this->saveObject('DrugRequestQuota', $data);
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            } else {
                if (!empty($response['DrugRequestQuota_id'])) {
                    $data['DrugRequestQuota_id'] = $response['DrugRequestQuota_id'];
                } else {
                    $error[] = 'Не удалось сохранить данные об объемах финансировани';
                }
            }
        }

        $result = array();

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        if (!empty($data['DrugRequestQuota_id'])) {
            $result['DrugRequestQuota_id'] = $data['DrugRequestQuota_id'];
            $result['success'] = true;
        }

        return $result;
	}

	/**
	 * Удаление
	 */
	function delete($data = array()) {
        $request_id = !empty($data['DrugRequest_id']) ? $data['DrugRequest_id'] : $this->DrugRequest_id;
		$error = array();
		$request_data = array();

		//получаем данные о заявке
		$query = "
			select
				dr.DrugRequest_id,
				dr.PersonRegisterType_id,
				dr.DrugRequestPeriod_id,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id,
				dr.Lpu_id,
				dr.MedPersonal_id,
				drs.DrugRequestStatus_Code,
				drc.DrugRequestCategory_SysNick
			from
			 	v_DrugRequest dr with (nolock)
			 	left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			 	left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
			where
				DrugRequest_id = :DrugRequest_id
		";
        $request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $request_id
		));
		if (empty($request_data['DrugRequest_id'])) {
            $error[] = 'Не удалось получить информацию о заявке.';
        }

		//проверяем статус заявки удалять можно только заявки со статусом "Начальная" или "Нулевая"
		if ($request_data['DrugRequestStatus_Code'] != 1 && $request_data['DrugRequestStatus_Code'] != 4) { //1 - Начальная; 4 - Нулевая;
			$error[] = 'Удаление невозможно. Для удаления статус заявки должен соответствовать значению "Нулевая" или "Начальная".';
        }

        $this->beginTransaction();

        //для заявок МО проверяем заявки врачей на наличие введеных медикаментов
        if (count($error) <= 0 && $request_data['DrugRequestCategory_SysNick'] == 'MO') {
            $params = array(
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id'],
                'Lpu_id' => $request_data['Lpu_id'],
                'DrugRequestCategory_id' => $this->getObjectIdByCode('DrugRequestCategory', 1) //1 - Заявка врача
            );

            $query = "
                select top 1
                    drr.DrugRequestRow_id
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestRow drr with (nolock) on drr.DrugRequest_id = dr.DrugRequest_id
                where
                    dr.DrugRequest_Version is null and
                    isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                    dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                    isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                    dr.Lpu_id = :Lpu_id and
                    dr.DrugRequestCategory_id = :DrugRequestCategory_id and
                    drr.DrugRequestRow_Kolvo > 0;
            ";
            $result = $this->getFirstRowFromQuery($query, $params);
            if (!empty($result['DrugRequestRow_id'])) {
                $error[] = 'Удаление невозможно. Заявка содержит заявки врачей с ненулевым количеством медикаментов.';
            }

            if (count($error) <= 0) {
                //получение списка дочерних заявок
                $query = "
                    select
                        dr.DrugRequest_id
                    from
                        v_DrugRequest dr with (nolock)
                    where
                        dr.DrugRequest_Version is null and
                        isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                        isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        dr.Lpu_id = :Lpu_id and
                        dr.DrugRequestCategory_id = :DrugRequestCategory_id;
                ";
                $result = $this->db->query($query, $params);
                if (is_object($result)) {
                    $request_array = $result->result('array');

                    //запрещаем транзакции в рекурсивно вызванной функции
                    $this->isAllowTransaction = false;

                    //удаление заявок врачей
                    foreach($request_array as $req) {
                        $response = $this->delete(array(
                            'DrugRequest_id' => $req['DrugRequest_id'],
                            'isAllowAutoStatus' => false
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'].'1';
                            break;
                        }
                    }

                    //вновь разрешаем транзакции
                    $this->isAllowTransaction = true;
                }
            }
        }

        //для заявок врачей удаляем разнарядки
        if (count($error) <= 0 && $request_data['DrugRequestCategory_SysNick'] == 'vrach') {
            $query = "
                delete from
                    DrugRequestPersonOrder
                where
                    DrugRequest_id = :DrugRequest_id;
            ";
            $result = $this->db->query($query, array(
                'DrugRequest_id' => $request_id
            ));
        }

        //удаление сопутствующих данных
        if (count($error) <= 0) {
            $query = "
                declare
                    @DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
                    @PersonRegisterType_id bigint = :PersonRegisterType_id,
                    @Lpu_id bigint = :Lpu_id,
                    @MedPersonal_id bigint = :MedPersonal_id;

                delete from
                    DrugRequestPlan
                where
                    DrugRequest_id = :DrugRequest_id;

                if (@Lpu_id is null and @MedPersonal_id is null)
                begin
                    delete from DrugRequestLpuGroup
                    where
                        PersonRegisterType_id = @PersonRegisterType_id and
                        DrugRequestPeriod_id = @DrugRequestPeriod_id and
                        MedPersonal_id is not null

                    delete from DrugRequestQuota
                    where
                        PersonRegisterType_id = @PersonRegisterType_id and
                        DrugRequestPeriod_id = @DrugRequestPeriod_id
                end;
            ";
            $result = $this->db->query($query, $request_data);
        }

        //непосредственное удаление заявки
        if (count($error) <= 0) {
            $result = $this->deleteObject('DrugRequest', $request_data);
            if (!empty($result['Error_Msg'])) {
                $error[] = $result['Error_Msg'];
            }
        }

        //автоматическое изменение статуса, если разрешено
        if (count($error) <= 0 && (!isset($data['isAllowAutoStatus']) || $data['isAllowAutoStatus']) && $request_data['DrugRequestStatus_Code'] != 4) { //4 - Нулевая; Если заявка имеет статус нулевая, то автостатус не имеет смысла, так как заявка не может быть сформирована.
            $this->setAutoDrugRequestStatus(array(
                'category' => $request_data['DrugRequestCategory_SysNick'],
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id'],
                'Lpu_id' => $request_data['Lpu_id']
            ));
        }

        $result = array(
            'Error_Code' => null,
            'Error_Msg' => null
        );

        if (count($error) <= 0) {
            $this->commitTransaction();
        } else {
            $this->rollbackTransaction();
            $result['Error_Msg'] = $error[0];
        }

        return $result;
	}

	/**
	 * Удаление сводной заявки
     * $disable_trans - признак отключения внутренних транзакций
	 */
	function deleteConsolidatedDrugRequest($data, $disable_trans = false) {
        $error = array();

        if (empty($data['DrugRequest_id'])) {
            $data['DrugRequest_id'] = $this->DrugRequest_id;
        }

        if (!$disable_trans) {
            //старт транзакции
            $this->beginTransaction();
        }

		//проверяем статус заявки удалять можно только заявки со статусом "Начальная"
		$query = "
			select
				drs.DrugRequestStatus_Code
			from
				DrugRequest dr with (nolock)
				left join DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$status_code = $this->getFirstResultFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));
		if (!empty($status_code) && $status_code != '4') { //4 - Начальная
			$error[] = 'Удаление невозможно';
		}

        //проверка связей строк спецификации со внешними объектами
        if (count($error) == 0) {
            $query = "
                select
                    count(drps.DrugRequestPurchaseSpec_id) as drps_cnt,
                    count(dre.DrugRequestExec_id) as dre_cnt,
                    count(wdcod.WhsDocumentCommercialOfferDrug_id) as wdcod_cnt,
                    count(wdppl.WhsDocumentProcurementPriceLink_id) as wdppl_cnt,
                    count(wdprs.WhsDocumentProcurementRequestSpec_id) aswdprs_cnt,
                    count(wdpss.WhsDocumentProcurementSupplySpec_id) as wdpss_cnt
                from
                    v_DrugRequestPurchaseSpec drps with (nolock)
                    left join v_DrugRequestExec dre with (nolock) on dre.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                    left join v_WhsDocumentCommercialOfferDrug wdcod with (nolock) on wdcod.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                    left join v_WhsDocumentProcurementPriceLink wdppl with (nolock) on wdppl.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                    left join v_WhsDocumentProcurementRequestSpec wdprs with (nolock) on wdprs.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                    left join v_WhsDocumentProcurementSupplySpec wdpss with (nolock) on wdpss.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                where
                    drps.DrugRequest_id = :DrugRequest_id;
            ";
            $obj_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
            if (!empty($obj_data['drps_cnt'])) {
                $obj_cnt = 0;
                $obj_cnt += $obj_data['dre_cnt'];
                $obj_cnt += $obj_data['wdcod_cnt'];
                $obj_cnt += $obj_data['wdppl_cnt'];
                $obj_cnt += $obj_data['aswdprs_cnt'];
                $obj_cnt += $obj_data['wdpss_cnt'];

                if ($obj_cnt > 0) {
                    $error[] = "Удаление заявки невозможно, так как она уже используется";
                }
            }
        }

		//удаляем связи с заявками ЛЛО
        if (count($error) == 0) {
            $query = "
                delete from DrugRequestPurchaseSpec
                where
                    DrugRequest_id = :DrugRequest_id;

                delete from DrugRequestPurchase
                where
                    DrugRequest_id = :DrugRequest_id;
            ";
            $response = $this->getFirstResultFromQuery($query, array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
        }

        //непосредственное удаление заявки
        if (count($error) == 0) {
            $response = $this->deleteObject('DrugRequest', array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        $result = array();

        if (count($error) > 0) {
            if (!$disable_trans) {
                //откат транзакции
                $this->rollbackTransaction();
            }
            $result['Error_Msg'] = $error[0];
        } else {
            if (!$disable_trans) {
                //коммит транзакции
                $this->commitTransaction();
            }
            $result['success'] = true;
        }

        return $result;
	}

	/**
	 * Удаление строки разнарядки
	 */
	function deleteDrugRequestPersonOrder($data) {
        $result = array();
        $drpo_data = array();
        $status_code = null;

        try {
            //получение данных строки ранарядки
            $query = "
                select
                    drpo.DrugRequestPersonOrder_id,
                    drpo.DrugRequest_id,
                    drpo.Person_id,
                    drpo.DrugComplexMnn_id,
                    drpo.Tradenames_id,
                    drpo.DrugRequestPersonOrder_OrdKolvo,
                    drs.DrugRequestStatus_Code
                from
                    v_DrugRequestPersonOrder drpo with (nolock)
                    left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drpo.DrugRequest_id
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                where
                    drpo.DrugRequestPersonOrder_id = :DrugRequestPersonOrder_id;
            ";
            $drpo_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequestPersonOrder_id' => $data['id']
            ));
            if (empty($drpo_data['DrugRequestPersonOrder_id'])) {
                throw new Exception("При получении данных строки разнарядки произошла ошибка");
            }

            if (empty($drpo_data['DrugComplexMnn_id']) && empty($drpo_data['Tradenames_id'])) { //для строк без медикментов
                //проверка наличия дочерних строк разнарядки
                $query = "
                    select
                        count(DrugRequestPersonOrder_id) as cnt
                    from
                        v_DrugRequestPersonOrder with (nolock)
                    where
                        DrugRequest_id = :DrugRequest_id and
                        Person_id = :Person_id and
                        (
                            DrugComplexMnn_id is not null or
                            Tradenames_id is not null
                        );
                ";
                $cnt = $this->getFirstResultFromQuery($query, array(
                    'DrugRequest_id' => $drpo_data['DrugRequest_id'],
                    'Person_id' => $drpo_data['Person_id']
                ));
                if ($cnt > 0) {
                    throw new Exception("Для данного пациента имеются строки в разнарядке. Удаление невозможно.");
                }
            } else { //для строк с медикаментами
                if ($drpo_data['DrugRequestStatus_Code'] == '1') { //1 - Начальная
                    //для заявок со статусом начальная, при удалении строк разнарядки корректируется количество медикамента в заявке

					//получение данных заявочной кампании
					$reg_dr_data = $this->getRegionDrugRequestByParams(array(
						'DrugRequest_id' => $drpo_data['DrugRequest_id']
					));
                    
                    //получение данных соответсвующей строки заявки
                    $query = "
                        select
                            drr.DrugRequestRow_id,
                            drr.DrugRequestRow_Kolvo,
                            dlr.Price
                        from
                            v_DrugRequestRow drr with (nolock)
                            outer apply (
                                select top 1
                                    (case
                                        when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
                                        else i_dlr.DrugListRequest_Price
                                    end) as Price
                                from
                                    v_DrugListRequest i_dlr with (nolock)
                                    left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
                                where
                                    i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id
                                     and (
                                        (:DrugRequestProperty_id is not null and i_dlr.DrugRequestProperty_id  = :DrugRequestProperty_id) or
                                        (drr.DrugFinance_id = :DrugFinanceFed_id and i_dlr.DrugRequestProperty_id  = :DrugRequestPropertyFed_id) or
                                        (drr.DrugFinance_id = :DrugFinanceReg_id and i_dlr.DrugRequestProperty_id  = :DrugRequestPropertyReg_id)
                                    )
                                order by
                                    DrugListRequest_insDT desc
                            ) dlr
                        where
                            drr.DrugRequest_id = :DrugRequest_id and
                            isnull(drr.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
                            isnull(drr.TRADENAMES_id, 0) = isnull(:Tradenames_id, 0);
                    ";
                    $drr_data = $this->getFirstRowFromQuery($query, array(
                        'DrugRequest_id' => $drpo_data['DrugRequest_id'],
                        'DrugComplexMnn_id' => $drpo_data['DrugComplexMnn_id'],
                        'Tradenames_id' => $drpo_data['Tradenames_id'],
                        'DrugFinanceFed_id' => 3,
                        'DrugFinanceReg_id' => 27,
						'DrugRequestProperty_id' => $reg_dr_data['DrugRequestProperty_id'],
						'DrugRequestPropertyFed_id' => $reg_dr_data['DrugRequestPropertyFed_id'],
						'DrugRequestPropertyReg_id' => $reg_dr_data['DrugRequestPropertyReg_id']
                    ));

                    if (!empty($drpo_data['DrugRequestPersonOrder_OrdKolvo']) && !empty($drr_data['DrugRequestRow_Kolvo'])) {
                        $new_kolvo = $drr_data['DrugRequestRow_Kolvo']*1 - $drpo_data['DrugRequestPersonOrder_OrdKolvo']*1;
                        if ($new_kolvo > 0) { //редактирование строки заявки
                            $res = $this->saveObject('DrugRequestRow', array(
                                'DrugRequestRow_id' => $drr_data['DrugRequestRow_id'],
                                'DrugRequestRow_Kolvo' => $new_kolvo,
                                'DrugRequestRow_Summa' => $drr_data['Price'] > 0 ? $new_kolvo*$drr_data['Price'] : null,
                            ));
                            if (!empty($res['Error_Msg'])) {
                                throw new Exception($res['Error_Msg']);
                            }
                        } else { //удаление строки заявки
                            $res = $this->deleteObject('DrugRequestRow', array(
                                'DrugRequestRow_id' => $drr_data['DrugRequestRow_id']
                            ));
                            if (!empty($res['Error_Msg'])) {
                                throw new Exception($res['Error_Msg']);
                            }
                        }
                    }
                }
            }

            //удаление строки разнарядки
            $result = $this->deleteObject('DrugRequestPersonOrder', array(
                'DrugRequestPersonOrder_id' => $data['id']
            ));
            if (!empty($result['Error_Msg'])) {
                throw new Exception($result['Error_Msg']);
            }
        }  catch (Exception $e) {
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
	}

	/**
	 * Загрузка списка МО
	 */
	function loadLpuList($filter) {
		$where = array();
		
		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id']) {
			$where[] = 'drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id';
		}
		
		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id']) {
			$where[] = 'isnull(drlg.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0)';
		}

		if (isset($filter['DrugRequestKind_id']) && $filter['DrugRequestKind_id']) {
			$where[] = 'isnull(drlg.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0)';
		}

		if (isset($filter['DrugGroup_id']) && $filter['DrugGroup_id']) {
			$where[] = 'isnull(drlg.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)';
		}
		
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}

        $filter['MpDrugRequestCategory_id'] = $this->getObjectIdByCode('DrugRequestCategory', 1); //1 - Заявка врача

        $query = "
			select
				drlg.DrugRequestLpuGroup_id,
				mp.MedPersonal_id,
				mp.Person_Fio,
				l.Lpu_id,
				l.Lpu_Nick as Lpu_Name,
				mp.Dolgnost_Name as Post_Name,
				replace(replace((
					select distinct LpuSectionProfile.LpuSectionProfile_Name+',' as 'data()'
					from v_MedStaffFact with(nolock)
						left join LpuSection with (nolock) on LpuSection.LpuSection_id = v_MedStaffFact.LpuSection_id
						left join LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id	
					where
						MedPersonal_id = mp.MedPersonal_id						
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuSectionProfile_Name,
				mp.MedPersonal_Code as CodeDLO,
				replace(replace((
					select LpuRegion_Name+',' as 'data()'
					from v_MedStaffRegion MedStaffRegion with(nolock) 
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = MedStaffRegion.LpuRegion_id
					where
						MedPersonal_id = mp.MedPersonal_id						
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuRegion_Name,
				dr_mp.cnt as DrugRequestMp_Count
			from
				DrugRequestLpuGroup drlg with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = drlg.Lpu_id
				outer apply (
			        select top 1
                        i_pmuc.MedPersonal_id
                    from
                        v_pmUserCache i_pmuc with (nolock)
                        left join pmUserCacheGroupLink pmLink with (nolock) on pmLink.pmUserCache_id = i_pmuc.pmUser_id
                        left join pmUserCacheGroup pmGroup with (nolock) on pmLink.pmUserCacheGroup_id = pmGroup.pmUserCacheGroup_id
                    where
                        i_pmuc.Lpu_id = l.Lpu_id and
                        (
                        	(pmGroup.pmUserCacheGroup_SysNick='102' or pmGroup.pmUserCacheGroup_SysNick ='ChiefLLO')
                        	or (i_pmuc.pmUser_groups like '%\"102\"%' or i_pmuc.pmUser_groups like '%\"ChiefLLO\"%')
                        )
                    order by
                        i_pmuc.MedPersonal_id
			    ) pmu
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = pmu.MedPersonal_id
				outer apply (
				    select
				        count(DrugRequest_id) as cnt
				    from
				        v_DrugRequest i_dr with (nolock)
				    where
				        i_dr.DrugRequest_Version is null and
				        i_dr.DrugRequestCategory_id = :MpDrugRequestCategory_id and
				        i_dr.Lpu_id = drlg.Lpu_id and
				        i_dr.DrugRequestPeriod_id = drlg.DrugRequestPeriod_id and
				        isnull(i_dr.PersonRegisterType_id, 0) = isnull(drlg.PersonRegisterType_id, 0) and
				        isnull(i_dr.DrugRequestKind_id, 0) = isnull(drlg.DrugRequestKind_id, 0) and
				        isnull(i_dr.DrugGroup_id, 0) = isnull(drlg.DrugGroup_id, 0)
				) dr_mp
			$where_clause
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей
	 */
	function loadMedPersonalList($filter) {
		$where = array();

		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id']) {
			$where[] = 'drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id';
		}

		if (isset($filter['PersonRegisterType_id']) && $filter['PersonRegisterType_id']) {
			$where[] = 'isnull(drlg.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0)';
		}

		if (isset($filter['DrugRequestKind_id']) && $filter['DrugRequestKind_id']) {
			$where[] = 'isnull(drlg.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0)';
		}

		if (isset($filter['DrugGroup_id']) && $filter['DrugGroup_id']) {
			$where[] = 'isnull(drlg.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)';
		}

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}

		$q = "
			select
				drlg.DrugRequestLpuGroup_id,
				mp.MedPersonal_id,
				mp.Person_Fio,
				l.Lpu_id,
				l.Lpu_Nick as Lpu_Name,
				mp.Dolgnost_Name as Post_Name,
				replace(replace((
					select distinct LpuSectionProfile.LpuSectionProfile_Name+',' as 'data()'
					from v_MedStaffFact with(nolock)
						left join LpuSection with (nolock) on LpuSection.LpuSection_id = v_MedStaffFact.LpuSection_id
						left join LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
					where
						MedPersonal_id = mp.MedPersonal_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuSectionProfile_Name,
				mp.MedPersonal_Code as CodeDLO,
				replace(replace((
					select LpuRegion_Name+',' as 'data()'
					from v_MedStaffRegion MedStaffRegion with(nolock)
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = MedStaffRegion.LpuRegion_id
					where
						MedPersonal_id = mp.MedPersonal_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuRegion_Name
			from
				DrugRequestLpuGroup drlg with (nolock)
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drlg.MedPersonal_id
				left join v_Lpu l with (nolock) on l.Lpu_id = mp.Lpu_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МО для выбора
	 */
	function loadLpuSelectList($filter) {
		$where = array();

		if (isset($filter['Person_SurName']) && $filter['Person_SurName']) {
			$where[] = 'mp.Person_SurName like :Person_SurName';
			$filter['Person_SurName'] = '%'.$filter['Person_SurName'].'%';
		}
		if (isset($filter['Person_FirName']) && $filter['Person_FirName']) {
			$where[] = 'mp.Person_FirName like :Person_FirName';
			$filter['Person_FirName'] = '%'.$filter['Person_FirName'].'%';
		}
		if (isset($filter['Person_SecName']) && $filter['Person_SecName']) {
			$where[] = 'mp.Person_SecName like :Person_SecName';
			$filter['Person_SecName'] = '%'.$filter['Person_SecName'].'%';
		}
		if (isset($filter['Lpu_id']) && $filter['Lpu_id']) {
			$where[] = 'l.Lpu_id = :Lpu_id';
		}
		if (isset($filter['LpuSectionProfile_id']) && $filter['LpuSectionProfile_id']) {
			$where[] = '
				mp.MedPersonal_id in (
					select
					    i_msf.MedPersonal_id
					from
					    v_MedStaffFact i_msf with (nolock)
						left join LpuSection i_ls with (nolock) on i_ls.LpuSection_id = i_msf.LpuSection_id
					where
						i_msf.LpuSectionProfile_id = :LpuSectionProfile_id
				)
			';
		}
		if (isset($filter['PostMed_id']) && $filter['PostMed_id']) {
			$where[] = 'mp.Dolgnost_id = :PostMed_id';
		}
        if (isset($filter['WorkData_IsResponsible']) && $filter['WorkData_IsResponsible'] > 0) {
            $where[] = "mp.MedPersonal_id is not null";
        }
		if (isset($filter['begDate']) && !empty($filter['begDate']) && isset($filter['endDate']) && !empty($filter['endDate'])) {
			$where[] = 'mp.WorkData_begDate <= :endDate and (mp.WorkData_endDate is null or mp.WorkData_endDate >= :begDate)';
		}
		
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		
		$query = "
        	select top 500
				mp.MedPersonal_id,
				mp.Person_Fio,
				l.Lpu_id,
				l.Lpu_Nick as Lpu_Name,
				mp.Dolgnost_Name as Post_Name,
				replace(replace((
					select distinct LpuSectionProfile.LpuSectionProfile_Name+',' as 'data()'
					from v_MedStaffFact with(nolock)
						left join LpuSection with (nolock) on LpuSection.LpuSection_id = v_MedStaffFact.LpuSection_id
						left join LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id	
					where
						MedPersonal_id = mp.MedPersonal_id						
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuSectionProfile_Name,
				mp.MedPersonal_Code as CodeDLO,
				replace(replace((
					select LpuRegion_Name+',' as 'data()'
					from v_MedStaffRegion MedStaffRegion with(nolock) 
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = MedStaffRegion.LpuRegion_id
					where
						MedPersonal_id = mp.MedPersonal_id						
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuRegion_Name
			from
			    v_Lpu l with (nolock)
			    outer apply (
			        select top 1
                        i_pmuc.MedPersonal_id
                    from
                        v_pmUserCache i_pmuc with (nolock)
                        left join pmUserCacheGroupLink pmLink with (nolock) on pmLink.pmUserCache_id = i_pmuc.pmUser_id
                        left join pmUserCacheGroup pmGroup with (nolock) on pmLink.pmUserCacheGroup_id = pmGroup.pmUserCacheGroup_id
                    where
                        i_pmuc.Lpu_id = l.Lpu_id and
                        (
                        	(pmGroup.pmUserCacheGroup_SysNick='102' or pmGroup.pmUserCacheGroup_SysNick ='ChiefLLO')
                        	or (i_pmuc.pmUser_groups like '%\"102\"%' or i_pmuc.pmUser_groups like '%\"ChiefLLO\"%')
                        ) and
                        i_pmuc.MedPersonal_id is not null
                    order by
                        i_pmuc.MedPersonal_id
			    ) pmu
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = pmu.MedPersonal_id
			$where_clause
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей для выбора
	 */
	function loadMedPersonalSelectList($filter) {
		$where = array();
		$p = array();

		if (isset($filter['Person_SurName']) && $filter['Person_SurName']) {
			$where[] = 'mp.Person_SurName like :Person_SurName';
			$filter['Person_SurName'] = '%'.$filter['Person_SurName'].'%';
		}
		if (isset($filter['Person_FirName']) && $filter['Person_FirName']) {
			$where[] = 'mp.Person_FirName like :Person_FirName';
			$filter['Person_FirName'] = '%'.$filter['Person_FirName'].'%';
		}
		if (isset($filter['Person_SecName']) && $filter['Person_SecName']) {
			$where[] = 'mp.Person_SecName like :Person_SecName';
			$filter['Person_SecName'] = '%'.$filter['Person_SecName'].'%';
		}
		if (isset($filter['Lpu_id']) && $filter['Lpu_id']) {
			$where[] = 'mp.Lpu_id = :Lpu_id';
		}
		if (isset($filter['LpuSectionProfile_id']) && $filter['LpuSectionProfile_id']) {
			$where[] = '
				mp.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffFact with(nolock)
						left join LpuSection with (nolock) on LpuSection.LpuSection_id = v_MedStaffFact.LpuSection_id
					where
						LpuSectionProfile_id = :LpuSectionProfile_id
				)
			';
		}
		if (isset($filter['PostMed_id']) && $filter['PostMed_id']) {
			$where[] = 'mp.Dolgnost_id = :PostMed_id';
		}
		if (isset($filter['LpuRegionType_id']) && $filter['LpuRegionType_id']) {
			$where[] = '
				mp.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffRegion MedStaffRegion with(nolock)
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = MedStaffRegion.LpuRegion_id
					where
						LpuRegionType_id = :LpuRegionType_id
				)
			';
		}
		if (isset($filter['WorkData_IsDlo']) && $filter['WorkData_IsDlo'] > 0) {
			$where[] = "mp.MedPersonal_Code is not null and mp.MedPersonal_Code <> '0'";
		}
		if (isset($filter['begDate']) && !empty($filter['begDate']) && isset($filter['endDate']) && !empty($filter['endDate'])) {
			$where[] = 'mp.WorkData_begDate <= :endDate and (mp.WorkData_endDate is null or mp.WorkData_endDate >= :begDate)';
		}

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}

		$q = "
			select top 500
				mp.MedPersonal_id,
				mp.Person_Fio,
				l.Lpu_id,
				l.Lpu_Nick as Lpu_Name,
				mp.Dolgnost_Name as Post_Name,
				replace(replace((
					select distinct LpuSectionProfile.LpuSectionProfile_Name+',' as 'data()'
					from v_MedStaffFact with(nolock)
						left join LpuSection with (nolock) on LpuSection.LpuSection_id = v_MedStaffFact.LpuSection_id
						left join LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
					where
						MedPersonal_id = mp.MedPersonal_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuSectionProfile_Name,
				mp.MedPersonal_Code as CodeDLO,
				replace(replace((
					select LpuRegion_Name+',' as 'data()'
					from v_MedStaffRegion MedStaffRegion with(nolock)
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = MedStaffRegion.LpuRegion_id
					where
						MedPersonal_id = mp.MedPersonal_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as LpuRegion_Name
			from
				v_MedPersonal mp with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = mp.Lpu_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение списка врачей из JSON
	 */
	function saveMedPersonalListFromJSON($data) {
		if (!empty($data['json_str'])) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) if ($record->MedPersonal_id > 0) {
				$params = $data;
				$params['MedPersonal_id'] = $record->MedPersonal_id;
				$q = "";
				switch($record->state) {
					case 'add':
						$q = "
							insert into DrugRequestLpuGroup (
								MedPersonal_id,
								DrugRequestPeriod_id,
								pmUser_insID,
								pmUser_updID,
								DrugRequestLpuGroup_insDT,
								DrugRequestLpuGroup_updDT,
								PersonRegisterType_id,
								DrugGroup_id,
								DrugRequestKind_id
							) values (
								:MedPersonal_id,
								:DrugRequestPeriod_id,
								:pmUser_id,
								:pmUser_id,
								dbo.tzGetDate(),
								dbo.tzGetDate(),
								:PersonRegisterType_id,
								:DrugGroup_id,
								:DrugRequestKind_id
							);
						";
					break;
					case 'delete':
						if ($record->DrugRequestLpuGroup_id > 0) {
							$q = "
								delete from
									DrugRequestLpuGroup
								where
									DrugRequestLpuGroup_id = :DrugRequestLpuGroup_id;
							";
							$params['DrugRequestLpuGroup_id'] = $record->DrugRequestLpuGroup_id;
						}
					break;						
				}
				if (!empty($q)) {
					//print getDebugSql($q, $params);
					$result = $this->db->query($q, $params);
				}
			}
		}
		return true;
	}

	/**
	 * Сохранение списка МО из JSON
	 */
	function saveLpuListFromJSON($data) {
		if (!empty($data['json_str'])) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) if ($record->Lpu_id > 0) {
				$params = $data;
				$params['Lpu_id'] = $record->Lpu_id;
				$q = "";
				switch($record->state) {
					case 'add':
						$q = "
							insert into DrugRequestLpuGroup (
								Lpu_id,
								DrugRequestPeriod_id,
								pmUser_insID,
								pmUser_updID,
								DrugRequestLpuGroup_insDT,
								DrugRequestLpuGroup_updDT,
								PersonRegisterType_id,
								DrugGroup_id,
								DrugRequestKind_id
							) values (
								:Lpu_id,
								:DrugRequestPeriod_id,
								:pmUser_id,
								:pmUser_id,
								dbo.tzGetDate(),
								dbo.tzGetDate(),
								:PersonRegisterType_id,
								:DrugGroup_id,
								:DrugRequestKind_id
							);
						";
					break;
					case 'delete':
						if ($record->DrugRequestLpuGroup_id > 0) {
							$q = "
								delete from
									DrugRequestLpuGroup
								where
									DrugRequestLpuGroup_id = :DrugRequestLpuGroup_id;
							";
							$params['DrugRequestLpuGroup_id'] = $record->DrugRequestLpuGroup_id;
						}
					break;
				}
				if (!empty($q)) {
					//print getDebugSql($q, $params);
					$result = $this->db->query($q, $params);
				}
			}
		}
		return true;
	}

	/**
	 * Смена статуса заявки
	 */
	function changeDrugRequestStatus($data) {
		$result = array();
		$err_msg = '';
		$request_data = array();
        $region = $_SESSION['region']['nick'];

		$this->setServer_id($data['Server_id']);
		$this->setpmUser_id($data['pmUser_id']);

		//Получаем данные о заявке
		$q = "
			select
				dr.PersonRegisterType_id,
				dr.DrugRequestPeriod_id,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id,
				dr.Lpu_id,
				drs.DrugRequestStatus_Code,
				drc.DrugRequestCategory_SysNick
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$r = $this->db->query($q, array('DrugRequest_id' => $data['DrugRequest_id']));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$request_data = $r[0];
			}
		}

		if (!isset($request_data['DrugRequestStatus_Code'])) {
			$err_msg = 'Не удалось получить данные о заявке.';
		}

		if (isset($request_data['DrugRequestCategory_SysNick'])) {
			$request_data['DrugRequestCategory_SysNick'] = mb_strtolower($request_data['DrugRequestCategory_SysNick']);
		}

		//Проверяем допустимость смены статуса
		if (empty($err_msg)) {
			//Утверждение заявки МО. Статус до утверждения должен быть "Сформированная"
			if ($request_data['DrugRequestCategory_SysNick'] == 'mo' && $data['DrugRequestStatus_Code'] == 3 && $request_data['DrugRequestStatus_Code'] != 2) { //3 - Утвержденная; 2 - Сформированная
				if ($request_data['DrugRequestStatus_Code'] == 3)
					$err_msg = 'Данная заявка уже утверждена.';
				else
					$err_msg = 'Утвердить можно только сформированную заявку МО.';
			}

			//Отмена статуса утверждена или возврат на редактирование заявки МО. Доступно, только если заявка региона не включена в сводную заявку.
			if ($request_data['DrugRequestCategory_SysNick'] == 'mo' && $request_data['DrugRequestStatus_Code'] == 3 && $data['DrugRequestStatus_Code'] != 3) { //3 - Утвержденная;
				$cnt = 1;
				$q = "
					select
						count(DrugRequest_id) as cnt
					from
						v_DrugRequest with(nolock)
					where
						DrugRequest_Version is null and
						isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
						DrugRequestPeriod_id = :DrugRequestPeriod_id and
						isnull(DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
						isnull(DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
						DrugRequest_id in ( select DrugRequest_lid from DrugRequestPurchase with(nolock) );
				";
				$p = array(
					'DrugRequestStatus_Code' => $data['DrugRequestStatus_Code'],
					'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
					'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
					'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
					'DrugGroup_id' => $request_data['DrugGroup_id']
				);
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$r = $r->result('array');
					if (isset($r[0])) {
						$cnt = $r[0]['cnt'];
					}
				}
				if ($cnt > 0)
					$err_msg = 'Данная заявка МО включена в сводную заявку. Смена статуса невозможна.';
			}
		}

		//проверка содержимого заявки МО при её утверждении
		if (empty($err_msg) && $request_data['DrugRequestCategory_SysNick'] == 'mo' && $data['DrugRequestStatus_Code'] == 3) { //3 - Утвержденная
			$check_data = $this->checkDrugRequestMoMissingAndUnattachedPerson($data);
			if (!empty($check_data['Error_Msg'])) {
				if (isset($check_data['MissingPerson_List']) || isset($check_data['UnattachedPerson_List'])) {
					$err_msg = 'Заявка не может быть утверждена, так как найдены ошибки в заявках врачей';
					$result['Error_Type'] = 'drugrequest_mo_confirmation_missing_and_unattached';
					$result['Error_Data'] = $check_data;
				}
			}
		}

		//Производим смену статуса
		if (empty($err_msg)) {
			$this->updateDrugRequestStatus(
				null,
				$data['DrugRequestStatus_Code'],
				array(
					'DrugRequest_id' => $data['DrugRequest_id']
				)
			);
		}

		//Производим сопутствующие смены статусов, если требуется
		if (empty($err_msg)) {
			//При открытии региональной заявки все подчиненые заявки получают статус 1 - Начальная
			if ($request_data['DrugRequestCategory_SysNick'] == 'region' && $request_data['DrugRequestStatus_Code'] == 4 && $data['DrugRequestStatus_Code'] == 1) {
				$this->updateDrugRequestStatus(
					array('mo', 'vrach'),
					$data['DrugRequestStatus_Code'],
					array(
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
						'DrugGroup_id' => $request_data['DrugGroup_id']
					)
				);
			}

			//При возвращении заявки МО на редактирование все подчиненые заявки получают статус 1 - Начальная
			if ($request_data['DrugRequestCategory_SysNick'] == 'mo' && $request_data['DrugRequestStatus_Code'] != 4 && $data['DrugRequestStatus_Code'] == 1) {
				$this->updateDrugRequestStatus(
					'vrach',
					$data['DrugRequestStatus_Code'],
					array(
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
						'DrugGroup_id' => $request_data['DrugGroup_id'],
						'Lpu_id' => $request_data['Lpu_id']
					)
				);
			}

			//При утверждении заявки МО. Если все остальные заявки МО в пределах региональной заявки утверждены. Утверждается заявка региона. Помимо этого, в любом случае утверждаются заявки врача.
			//При отмене статуса "Утвержденная" для заявки МО. Отмена утверждения для заявки региона. Присвоением заявкам врача входящим в данную заявку МО - статуса "Сформированная".
			if (
				$request_data['DrugRequestCategory_SysNick'] == 'mo' &&
				(
					($request_data['DrugRequestStatus_Code'] == 2 && $data['DrugRequestStatus_Code'] == 3) ||
					($request_data['DrugRequestStatus_Code'] == 3 && $data['DrugRequestStatus_Code'] == 2) ||
					($request_data['DrugRequestStatus_Code'] == 3 && $data['DrugRequestStatus_Code'] == 6) ||
					($request_data['DrugRequestStatus_Code'] == 6 && $data['DrugRequestStatus_Code'] == 7)
				)
			) {//2 - Сформированная; 3 - Утвержденная; 6 - Согласована; 7 - Утверждена МЗ
				//Автостатус для заявки региона
				$this->setAutoDrugRequestStatus(array(
					'DrugRequest_id' => $data['DrugRequest_id']
				));

				//Установка статуса для заявок врачей
                if (in_array($data['DrugRequestStatus_Code'], array(2, 3))) {
                    $this->updateDrugRequestStatus(
                        'vrach',
                        $data['DrugRequestStatus_Code'],
                        array(
                            'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                            'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                            'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                            'DrugGroup_id' => $request_data['DrugGroup_id'],
                            'Lpu_id' => $request_data['Lpu_id']
                        )
                    );
                }
			}

			//Отмена статуса утверждена или возврат на редактирование заявки МО. Автоматическая смена статуса заявки региона с "Утвержденная" на "Начальная"
			//Отмена статуса согласована. Автоматическая смена статуса заявки региона с "Утвержденная" на "Начальная"
			//Отмена статуса утверждена МЗ. Автоматическая смена статуса заявки региона с "Утвержденная" на "Начальная"
			if (
                $request_data['DrugRequestCategory_SysNick'] == 'mo' &&
                (
                    (!in_array($region, array('saratov', 'ufa')) && $request_data['DrugRequestStatus_Code'] == 3 && $data['DrugRequestStatus_Code'] != 3) ||
                    (in_array($region, array('ufa')) && $request_data['DrugRequestStatus_Code'] == 6 && $data['DrugRequestStatus_Code'] != 6) ||
                    (in_array($region, array('saratov')) && $request_data['DrugRequestStatus_Code'] == 7 && $data['DrugRequestStatus_Code'] != 7)
                )
            ) { //3 - Утвержденная; 6 - Cогласована; 7 - Утверждена МЗ
				$this->updateDrugRequestStatus(
					'region',
					1,
					array(
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
						'DrugGroup_id' => $request_data['DrugGroup_id']
					)
				);
			}
		}

		$result['DrugRequest_id'] = $data['DrugRequest_id'];
		$result['Error_Msg'] = $err_msg;
		$this->DrugRequest_id = $data['DrugRequest_id'];
		
		return $result;
	}

	/**
	 * Внесение автоматических коррективов в статусы заявок
	 * $data - данные заявки-инициатора
	 * $data['category'] - категория заявки-инициатора
	 */
	function setAutoDrugRequestStatus($data) {
		$err_msg = '';
		$request_data = array();
        $region = $_SESSION['region']['nick'];

		//Получаем данные о заявке
		if (isset($data['DrugRequest_id'])) {
			$q = "
				select
					dr.PersonRegisterType_id,
					dr.DrugRequestPeriod_id,
					dr.DrugRequestKind_id,
					dr.DrugGroup_id,
					dr.Lpu_id,
					drs.DrugRequestStatus_Code,
					drc.DrugRequestCategory_SysNick
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
					left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					dr.DrugRequest_id = :DrugRequest_id;
			";
			$r = $this->db->query($q, array('DrugRequest_id' => $data['DrugRequest_id']));
			if (is_object($r)) {
				$r = $r->result('array');
				if (isset($r[0])) {
					$category = $r[0]['DrugRequestCategory_SysNick'];
					$request_data = $r[0];
				}
			}
		} else {
			$category =  isset($data['category']) ? $data['category'] : null;
			$request_data['PersonRegisterType_id'] =  isset($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : null;
			$request_data['DrugRequestPeriod_id'] = isset($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : null;
			$request_data['DrugRequestKind_id'] = isset($data['DrugRequestKind_id']) ? $data['DrugRequestKind_id'] : null;
			$request_data['DrugGroup_id'] = isset($data['DrugGroup_id']) ? $data['DrugGroup_id'] : null;
			$request_data['Lpu_id'] = isset($data['Lpu_id']) ? $data['Lpu_id'] : null;
		}

		$category = mb_strtolower($category);

		//проверки и автоматические изменения связанные с изменением статуса заявки врача
		if (empty($err_msg) && ($category == 'vrach' || $category == 'all')) {
			//если все заявки врача в пределах одной заявки МО сформированны, заявке МО также присваивается статус "сформированная", в противном случае заявке мо присваивается статус "начальная"
			$new_status = 0;
			$total_count = 0;
			$formed_count = 0;

			//Считаем все заявки, и все несформированные заявки
			$q = "
				declare
					@status_form_id bigint,
					@status_app_id bigint;

				set @status_form_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 2);
				set @status_app_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 3);

				select
					count(DrugRequest_id) as total_count,
					sum(case when DrugRequestStatus_id in (@status_form_id, @status_app_id) then 1 else 0 end) as formed_count
				from
					v_DrugRequest with(nolock)
				where
					DrugRequest_Version is null and
					DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					Lpu_id = :Lpu_id and
					MedPersonal_id is not null and
					DrugRequestCategory_id in (
						select
							DrugRequestCategory_id
						from
							v_DrugRequestCategory with(nolock)
						where
							DrugRequestCategory_SysNick = 'vrach'
					)
			";
			$r = $this->db->query($q, $request_data);
			if (is_object($r)) {
				$r = $r->result('array');
				if (isset($r[0])) {
					$total_count = $r[0]['total_count'];
					$formed_count = $r[0]['formed_count'];
				}
			}

			if ($total_count > 0 && $formed_count == $total_count) {
				$new_status = 2; //2 - Сформированная
			} else {
				$new_status = 1; //1 - Начальная
			}

			//Если необходимо, утанавливаем родительской заявке новый статус
			if ($new_status > 0) {
				$this->updateDrugRequestStatus(
					'mo',
					$new_status,
					array(
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
						'DrugGroup_id' => $request_data['DrugGroup_id'],
						'Lpu_id' => $request_data['Lpu_id']
					)
				);
			}
		}

		//проверки и автоматические изменения связанные с изменением статуса заявки МО (кроме Саратова и Уфы)
		if (empty($err_msg) && ($category == 'mo' || $category == 'all')) {
			//если все заявки МО в пределах одной заявки региона утвержджены (Уфа или Саратов: солгласованы), заявке региона также присваивается статус "Утвержденная", в противном случае заявке региона присваивается статус "Начальная"
			$new_status = 0;
			$total_count = 0;
			$approved_count = 0;
			$conformed_count = 0;
			$approved_mz_count = 0;

			$q = "
				declare
					@status3_id bigint,
					@status6_id bigint,
					@status7_id bigint;

				set @status3_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 3);
				set @status6_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 6);
				set @status7_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 7);

				select
					count(DrugRequest_id) as total_count,
					sum(case when DrugRequestStatus_id = @status3_id then 1 else 0 end) as approved_count,
					sum(case when DrugRequestStatus_id = @status6_id then 1 else 0 end) as conformed_count,
					sum(case when DrugRequestStatus_id = @status7_id then 1 else 0 end) as approved_mz_count
				from
					v_DrugRequest with (nolock)
				where
					DrugRequest_Version is null and
					DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					DrugRequestCategory_id in (
						select
							DrugRequestCategory_id
						from
							v_DrugRequestCategory with(nolock)
						where
							DrugRequestCategory_SysNick = 'mo'
					)
				";
			$p = array(
				'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
				'DrugGroup_id' => $request_data['DrugGroup_id']
			);
			$r = $this->db->query($q, $request_data);
			if (is_object($r)) {
				$r = $r->result('array');
				if (isset($r[0])) {
					$total_count = $r[0]['total_count'];
					$approved_count = $r[0]['approved_count'];
					$conformed_count = $r[0]['conformed_count'];
                    $approved_mz_count = $r[0]['approved_mz_count'];
				}
			}

            if (in_array($region, array('saratov', 'ufa'))) {
                if (
                    $total_count > 0 &&
                    (
                        ($region == 'ufa' && $conformed_count == $total_count) ||
                        ($region == 'saratov' && $approved_mz_count == $total_count)
                    )
                ) {
                    $new_status = 3; //3 - Утвержденная
                } else {
                    $new_status = 1; //1 - Начальная
                }
            } else {
                if ($total_count > 0 && $approved_count == $total_count) {
                    $new_status = 3; //3 - Утвержденная
                } else {
                    $new_status = 1; //1 - Начальная
                }
            }


			//Если необходимо, утанавливаем родительской заявке новый статус
			if ($new_status > 0) {
				$this->updateDrugRequestStatus(
					'region',
					$new_status,
					array(
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
						'DrugGroup_id' => $request_data['DrugGroup_id']
					)
				);
			}
		}

		return $err_msg;
	}

	/**
	 * Вспомогательная функция для смены статуса группе заявок
	 * category - текстовый код (массив кодов) заявок
	 * status - код нового статуса
	 * request_data - общие признаки обновляемых заявок
	 * возвращает результат выполнения запроса
	 */
	function updateDrugRequestStatus($category, $status, $request_data) {
		$where = array();

		if (isset($request_data['DrugRequest_id']) && $request_data['DrugRequest_id'] > 0) {
			$where[] = "DrugRequest_id = :DrugRequest_id";
		} else {
			if (!isset($request_data['DrugRequestPeriod_id'])) { //чтобы случайно не испортить данные в БД
				return false;
			}

			$where[] = "DrugRequest_Version is null";

			if(is_array($category)) {
				foreach($category as $key => $value) {
					$category[$key] = "DrugRequestCategory_SysNick = '{$value}'";
				}
				$where_category = join(' or ', $category);
			} else {
				$where_category = "DrugRequestCategory_SysNick = '{$category}'";
			}

			$where[] = "DrugRequestCategory_id in (
				select
					DrugRequestCategory_id
				from
					v_DrugRequestCategory with(nolock)
				where
					{$where_category}
			)";

			foreach($request_data as $key => $value) {
				if ($key == 'PersonRegisterType_id' || $key == 'DrugRequestKind_id' || $key == 'DrugGroup_id') {
					$where[] = "isnull({$key}, 0) = isnull(:{$key}, 0)";
				} else {
					$where[] = "{$key} = :{$key}";
				}
			}
		}

		$where = join(' and ', $where);

		$q = "
			update
				DrugRequest
			set
				Server_id = :Server_id,
				DrugRequestStatus_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = {$status}),
				pmUser_updID = :pmUser_id,
				DrugRequest_updDT = dbo.tzGetDate()
			where
				{$where}
			";

		$request_data['Server_id'] = $this->getServer_id();
		$request_data['pmUser_id'] = $this->getpmUser_id();

		//print getDebugSQL($q, $request_data); die;
		return $this->db->query($q, $request_data);
	}

	/**
	 * Оценка правомерности редактирования состава или характеристик заявки
	 */
	function checkAllowedDrugRequestEdit($data) {
		$err_msg = null;
		$request_data = array();

		if (isset($data['DrugRequest_id'])) {
			//Получаем данные о заявке
			$q = "
				select
					dr.DrugRequest_id,
					dr.PersonRegisterType_id,
					dr.DrugRequestPeriod_id,
					dr.DrugRequestKind_id,
					dr.DrugGroup_id
				from
					v_DrugRequest dr with (nolock)
				where
					dr.DrugRequest_id = :DrugRequest_id;
			";
			$r = $this->db->query($q, array('DrugRequest_id' => $data['DrugRequest_id']));
			if (is_object($r)) {
				$r = $r->result('array');
				if (isset($r[0])) {
					$request_data = $r[0];
				}
			}
			if (!isset($request_data['DrugRequest_id'])) {
				$err_msg = 'Не удалось получить данные о заявке.';
			}
		} else {
			$request_data['DrugRequestPeriod_id'] = isset($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : null;
			$request_data['PersonRegisterType_id'] = isset($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : null;
			$request_data['DrugRequestKind_id'] = isset($data['DrugRequestKind_id']) ? $data['DrugRequestKind_id'] : null;
			$request_data['DrugGroup_id'] = isset($data['DrugGroup_id']) ? $data['DrugGroup_id'] : null;
		}

		//Проверяем наличие родительской региональной заявки в составе сводной.
		if (empty($err_msg)) {
			$q = "
				select
					count(DrugRequest_id) as cnt
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					drc.DrugRequestCategory_SysNick = 'region' and
					dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					dr.DrugRequest_id in (
						select
							Drugrequest_lid
						from
							DrugRequestPurchase with(nolock)
					)
			";
			$r = $this->db->query($q, $request_data);
			if (is_object($r)) {
				$r = $r->result('array');
				if (isset($r[0]) && isset($r[0]['cnt']) && $r[0]['cnt'] > 0) {
					$err_msg = 'Данная заявка входит в состав сводной заявки. Редактирование недоступно.';
				}
			}
		}

		return $err_msg;
	}

	/**
	 * Создание полной копии заявочной кампании, включая все дочерние заявки и разнарядки (копирование потребности)
	 */
	function createDrugRequestRegionFirstCopy($data) {
        $result = array();
        $version_num = 1; //номер копии фиксирован
		$pmuser_id = $this->getPromedUserId();

        try {
            $this->beginTransaction();

            //получаем данные заявочой кампании
            $query = "
                select
                    dr.DrugRequest_id,
                    dr.PersonRegisterType_id,
                    dr.DrugRequestPeriod_id,
                    dr.DrugRequestKind_id,
                    dr.DrugGroup_id,
                    drs.DrugRequestStatus_Code,
                    drp.DrugRequestPurchase_id
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    outer apply (
                        select top 1
                            DrugRequestPurchase_id
                        from
                            v_DrugRequestPurchase i_drp with (nolock)
                        where
                            i_drp.Drugrequest_lid = dr.DrugRequest_id
                    ) drp
                where
                    dr.DrugRequest_id = :DrugRequest_id
            ";
            $request_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
            if (empty($request_data['DrugRequest_id'])) {
                throw new Exception("Не удалось получить данные заявки");
            }

            //проверка статуса
            if ($data['check_status'] === true && $request_data['DrugRequestStatus_Code'] <> '3') { //3 - Утвержденная
                throw new Exception("Заявочная кампания не имеет статуса \"Утвержденная\". Продолжение выполнения операции невозможно.");
            }

            //проверка на наличие сводной заявки. в которую включена данная заявочная кампания
            if ($data['check_consolidated_request'] === true && !empty($request_data['DrugRequestPurchase_id'])) {
                throw new Exception("Заявочная кампания включена в сводную заявку. Продолжение выполнения операции невозможно.");
            }

            //проверяем наличие "первой" копии
            $query = "
                select
                    dr.DrugRequest_id
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
                where
                    dr.DrugRequest_Version = :DrugRequest_Version and
                    drc.DrugRequestCategory_SysNick = 'region' and
					dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)
            ";
            $res = $this->getFirstRowFromQuery($query, array(
                'DrugRequest_Version' => $version_num,
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id']
            ));
            if (!empty($res['DrugRequest_id'])) {
                throw new Exception("Копия заявки уже создана");
            }

            //получаем список копируемых заявок
            $query = "
                select
                    dr.DrugRequest_id,
                    drc.DrugRequestCategory_SysNick
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
                where
                    dr.DrugRequest_Version is null and
                    drc.DrugRequestCategory_SysNick in ('vrach', 'mo', 'region', 'glavMZ', 'building', 'section') and
					dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)
            ";
            $request_list = $this->queryResult($query, array(
                'DrugRequest_Version' => $version_num,
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id']
            ));
            if (!is_array($request_list)) {
                throw new Exception("При получении списка заявок произошла ошибка");
            }

            //формирование запроса для копирования плановых параметров
			$select_array = array();
			$fields_array = array_keys($this->getFirstRowFromQuery("select top 1 * from v_DrugRequestPlan"));
			unset($fields_array[0]);

			foreach($fields_array as $field) {
				switch($field) {
					case 'DrugRequest_id':
						$select_array[] = ':NewDrugRequest_id';
						break;
					case 'pmUser_insID':
					case 'pmUser_updID':
						$select_array[] = $pmuser_id;
						break;
					case 'DrugRequestPlan_insDT':
					case 'DrugRequestPlan_updDT':
						$select_array[] = '@datetime';
						break;
					default:
						$select_array[] = $field;
						break;
				}
			}

			$drp_copy_query = "
				declare
					@datetime datetime = dbo.tzGetDate(),
					@Error_Code int,
					@Error_Message varchar(4000)
				set nocount on
				begin try
					insert into
						DrugRequestPlan(".join(',', $fields_array).")
					select
						".join(',', $select_array)."
					from
						v_DrugRequestPlan with (nolock)
					where
						DrugRequest_id = :DrugRequest_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			//формирование запроса для копирования строк заявки
			$select_array = array();
			$fields_array = array_keys($this->getFirstRowFromQuery("select top 1 * from v_DrugRequestRow"));
            unset($fields_array[0]);

			foreach($fields_array as $field) {
				switch($field) {
					case 'DrugRequest_id':
						$select_array[] = ':NewDrugRequest_id';
						break;
					case 'pmUser_insID':
					case 'pmUser_updID':
						$select_array[] = $pmuser_id;
						break;
					case 'DrugRequestRow_insDT':
					case 'DrugRequestRow_updDT':
						$select_array[] = '@datetime';
						break;
					default:
						$select_array[] = $field;
						break;
				}
			}

			$drr_copy_query = "
				declare
					@datetime datetime = dbo.tzGetDate(),
					@Error_Code int,
					@Error_Message varchar(4000)
				set nocount on
				begin try
					insert into
						DrugRequestRow(".join(',', $fields_array).")
					select
						".join(',', $select_array)."
					from
						v_DrugRequestRow with (nolock)
					where
						DrugRequest_id = :DrugRequest_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			//формирование запроса для копирования строк заявки
			$select_array = array();
			$fields_array = array_keys($this->getFirstRowFromQuery("select top 1 * from v_DrugRequestPersonOrder"));
			unset($fields_array[0]);

			foreach($fields_array as $field) {
				switch($field) {
					case 'DrugRequest_id':
						$select_array[] = ':NewDrugRequest_id';
						break;
					case 'DrugRequestPersonOrder_Copy':
						$select_array[] =  $version_num;
						break;
					case 'pmUser_insID':
					case 'pmUser_updID':
						$select_array[] = $pmuser_id;
						break;
					case 'DrugRequestPersonOrder_insDT':
					case 'DrugRequestPersonOrder_updDT':
						$select_array[] = '@datetime';
						break;
					default:
						$select_array[] = $field;
						break;
				}
			}

			$drpo_copy_query = "
				declare
					@datetime datetime = dbo.tzGetDate(),
					@Error_Code int,
					@Error_Message varchar(4000)
				set nocount on
				begin try
					insert into
						DrugRequestPersonOrder(".join(',', $fields_array).")
					select
						".join(',', $select_array)."
					from
						v_DrugRequestPersonOrder with (nolock)
					where
						DrugRequest_id = :DrugRequest_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

            //копируем заявки и связанные данные
            for($i = 0; $i < count($request_list); $i++) {
                //непосредственное копирование заявки
                $save_result = $this->copyObject('DrugRequest', array(
                    'DrugRequest_id' => $request_list[$i]['DrugRequest_id'],
                    'DrugRequest_Version' => $version_num
                ));
                if (!empty($save_result['DrugRequest_id'])) {
                    $request_list[$i]['NewDrugRequest_id'] = $save_result['DrugRequest_id'];
                } else {
                    $error[] = !empty($save_result['Error_Msg']) ? $save_result['Error_Msg'] : "Не удалось сохранить копию заявки";
                }

                //копирование плановых параметров
                if ($request_list[$i]['DrugRequestCategory_SysNick'] == 'region') {
					$res = $this->getFirstRowFromQuery($drp_copy_query, array(
						'DrugRequest_id' => $request_list[$i]['DrugRequest_id'],
						'NewDrugRequest_id' => $request_list[$i]['NewDrugRequest_id']
					));
					if (!is_array($res) || !empty($res['Error_Msg'])) {
						throw new Exception("При копировании плановых параметров произошла ошибка");
					}
				}

                if (!empty($request_list[$i]['NewDrugRequest_id']) && !in_array($request_list[$i]['DrugRequestCategory_SysNick'], array('mo', 'region'/*, 'building', 'section'*/))) {
					//копирование строк заявки
					$res = $this->getFirstRowFromQuery($drr_copy_query, array(
						'DrugRequest_id' => $request_list[$i]['DrugRequest_id'],
						'NewDrugRequest_id' => $request_list[$i]['NewDrugRequest_id']
					));
					if (!is_array($res) || !empty($res['Error_Msg'])) {
						throw new Exception("При копировании строк заявки произошла ошибка");
					}

					//копирование строк разнарядки
					$res = $this->getFirstRowFromQuery($drpo_copy_query, array(
						'DrugRequest_id' => $request_list[$i]['DrugRequest_id'],
						'NewDrugRequest_id' => $request_list[$i]['NewDrugRequest_id']
					));
					if (!is_array($res) || !empty($res['Error_Msg'])) {
						throw new Exception("При копировании строк разнарядки заявки произошла ошибка");
					}
                }
            }

            $this->commitTransaction();
            $result['success'] = true;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

	/**
	 * Удаление полной копии заявочной кампании, включая все дочерние заявки и разнарядки (функция для разработчика)
	 */
	function deleteDrugRequestRegionFirstCopy($data) {
        $result = array();
        $version_num = 1; //номер копии фиксирован

        try {
            $this->beginTransaction();

            //получаем данные заявочой кампании
            $query = "
                select
                    dr.DrugRequest_id,
                    dr.PersonRegisterType_id,
                    dr.DrugRequestPeriod_id,
                    dr.DrugRequestKind_id,
                    dr.DrugGroup_id,
                    drs.DrugRequestStatus_Code,
                    drp.DrugRequestPurchase_id
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    outer apply (
                        select top 1
                            DrugRequestPurchase_id
                        from
                            v_DrugRequestPurchase i_drp with (nolock)
                        where
                            i_drp.Drugrequest_lid = dr.DrugRequest_id
                    ) drp
                where
                    dr.DrugRequest_id = :DrugRequest_id
            ";
            $request_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
            if (empty($request_data['DrugRequest_id'])) {
                throw new Exception("Не удалось получить данные заявки");
            }

            //получаем список удаляемых заявок
            $query = "
                select
                    dr.DrugRequest_id,
                    drc.DrugRequestCategory_SysNick
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
                where
                    dr.DrugRequest_Version = :DrugRequest_Version and
                    drc.DrugRequestCategory_SysNick in ('vrach', 'mo', 'region', 'glavMZ', 'building', 'section') and
					dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)
            ";
            $request_list = $this->queryResult($query, array(
                'DrugRequest_Version' => $version_num,
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id']
            ));
            if (!is_array($request_list)) {
                throw new Exception("При получении списка заявок произошла ошибка");
            }

            //удаляем плановые параметры
			$query = "
				delete from DrugRequestPlan where DrugRequest_id = :DrugRequest_id;
			";
			$res = $this->getFirstResultFromQuery($query, array(
				'DrugRequest_id' => $data['DrugRequest_id']
			));

            //удаляем заявки и связанные данные
            for($i = 0; $i < count($request_list); $i++) {
                if (!empty($request_list[$i]['DrugRequest_id']) && !in_array($request_list[$i]['DrugRequestCategory_SysNick'], array('mo', 'region'/*, 'building', 'section'*/))) {
                    //получение списка строк заявки
                    $query = "
                        select
                            drr.DrugRequestRow_id
                        from
                            v_DrugRequestRow drr with (nolock)
                        where
                            drr.DrugRequest_id = :DrugRequest_id;
                    ";
                    $row_list = $this->queryList($query, array(
                        'DrugRequest_id' => $request_list[$i]['DrugRequest_id']
                    ));

                    //удаление строк заявки
                    foreach($row_list as $row_id) {
                        /*$res = $this->deleteObject('DrugRequestRow', array(
                            'DrugRequestRow_id' => $row_id
                        ));
                        if (!empty($res['Error_Msg'])) {
                            throw new Exception("При удалении строки заявки произошла ошибка");
                        }*/
                        //стандартная хранимка тут не подходит, так как строки заявок не удаляются насовсем
                        $query = "
                            delete from DrugRequestRow where DrugRequestRow_id = :DrugRequestRow_id;
                        ";
                        $res = $this->getFirstResultFromQuery($query, array(
                            'DrugRequestRow_id' => $row_id
                        ));
                    }

                    //получение списка строк разнарядки
                    $query = "
                        select
                            drpo.DrugRequestPersonOrder_id
                        from
                            v_DrugRequestPersonOrder drpo with (nolock)
                        where
                            drpo.DrugRequest_id = :DrugRequest_id;
                    ";
                    $drpo_list = $this->queryList($query, array(
                        'DrugRequest_id' => $request_list[$i]['DrugRequest_id']
                    ));

                    //удаление строк разнарядки
                    foreach($drpo_list as $drpo_id) {
                        $res = $this->deleteObject('DrugRequestPersonOrder', array(
                            'DrugRequestPersonOrder_id' => $drpo_id
                        ));
                        if (!empty($res['Error_Msg'])) {
                            throw new Exception("При удалении строки разнарядки произошла ошибка");
                        }
                    }
                }

                //непосредственное удаление заявки
                $del_result = $this->deleteObject('DrugRequest', array(
                    'DrugRequest_id' => $request_list[$i]['DrugRequest_id']
                ));
                if (!empty($del_result['Error_Msg'])) {
                    $error[] = !empty($del_result['Error_Msg']) ? $del_result['Error_Msg'] : "Не удалось удалить копию заявки";
                }
            }

            $this->commitTransaction();
            $result['success'] = true;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

	/**
	 * Создание архивной копии заявки
	 */
	function createDrugRequestArchiveCopy($data) {
		$err_msg = '';
		$request_list = array();
		$version_num = 0;

		//получаем параметры заявок для копирования, если указан идентификатор заявки
		if(!empty($data['DrugRequest_id'])) {
			$query = "
				select
					DrugRequestPeriod_id,
					PersonRegisterType_id,
					Lpu_id,
					MedPersonal_id
				from
					DrugRequest
				where
					DrugRequest_id = :DrugRequest_id and
					DrugRequest_Version is null;
			";
			$result = $this->getFirstRowFromQuery($query, $data);
			if (!empty($result['DrugRequestPeriod_id'])) {
				$data['DrugRequestPeriod_id'] = $result['DrugRequestPeriod_id'];
				$data['PersonRegisterType_id'] = $result['PersonRegisterType_id'];
				$data['Lpu_id'] = $result['Lpu_id'];
				$data['MedPersonal_id'] = $result['MedPersonal_id'];
			}
		}

		if (empty($data['DrugRequestPeriod_id'])) {
			$err_msg = 'Для создания архивной копии необходимо указатьрабочий период заявки.';
		}

		//получаем список заявок для копирования
		if (empty($err_msg)) {
			$query = "
				select
					DrugRequest_id
				from
					DrugRequest
				where
					DrugRequest_Version is null and
					isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					DrugRequestPeriod_id = :DrugRequestPeriod_id and
					Lpu_id is not null and
					(:Lpu_id is null or Lpu_id = :Lpu_id) and
					MedPersonal_id is not null and
					(:MedPersonal_id is null or MedPersonal_id = :MedPersonal_id);
			";
			$result = $this->db->query($query, array(
				'PersonRegisterType_id' => isset($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : null,
				'DrugRequestPeriod_id' => isset($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : null,
				'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
				'MedPersonal_id' => isset($data['MedPersonal_id']) ? $data['MedPersonal_id'] : null
			));
			if (is_object($result)) {
				$request_list = $result->result('array');
			}
			if (count($request_list) <= 0) {
				$err_msg = 'Список заявок пуст.';
			}
		}

		//получаем номер версии
		if (empty($err_msg)) { //нумерацию начинаяем с версии 2 так как версия с номером 1 зарезервирована под механизмы копирования потребности
			$query = "
				select
					isnull(MAX(DrugRequest_Version), 1)+1 as version
				from
					DrugRequest;
			";
			$result = $this->getFirstResultFromQuery($query);
			if ($result > 0) {
				$version_num = $result;
			} else {
				$err_msg = 'Не удалось получить номер версии.';
			}
		}

		//сборка запросов для копирования заявок
		if (empty($err_msg)) {
			$ins_dr_params = $this->getStoredProcedureParamsList('p_DrugRequest_ins', 'dbo');
			$ins_drr_params = $this->getStoredProcedureParamsList('p_DrugRequestRow_ins', 'dbo');

			$query_get_dr_part = join(", ", $ins_dr_params);
			$query_get_drr_part = join(", ", $ins_drr_params);
			$query_ins_dr_part = "";
			$query_ins_drr_part = "";

			foreach($ins_dr_params as $param) {
				if ($param != 'DrugRequest_id') {
					$query_ins_dr_part .= "@{$param} = :{$param}, ";
				}
			}
			foreach($ins_drr_params as $param) {
				if ($param != 'DrugRequestRow_id') {
					$query_ins_drr_part .= "@{$param} = :{$param}, ";
				}
			}

			$query_get_dr = "
				select
					{$query_get_dr_part}
				from
					DrugRequest
				where
					DrugRequest_id = :DrugRequest_id;
			";

			$query_get_drr = "
				select
					{$query_get_drr_part}
				from
					DrugRequestRow
				where
					DrugRequest_id = :DrugRequest_id;
			";

			$query_ins_dr = "
				declare
					@DrugRequest_id bigint = null,
					@Error_Code int,
					@Error_Message varchar(4000);

				execute dbo.p_DrugRequest_ins
					@DrugRequest_id = @DrugRequest_id output,
					{$query_ins_dr_part}
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @DrugRequest_id as DrugRequest_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$query_ins_drr = "
				declare
					@DrugRequestRow_id bigint = null,
					@Error_Code int,
					@Error_Message varchar(4000);

				execute dbo.p_DrugRequestRow_ins
					@DrugRequestRow_id = @DrugRequestRow_id output,
					{$query_ins_drr_part}
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @DrugRequestRow_id as DrugRequestRow_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
		}

		//копирование заявок и строк заявок
		if (empty($err_msg)) {
			$this->db->trans_begin();

			foreach($request_list as $request) {
				//получение данных копируемой заявки
				$ins_dr_data = $this->getFirstRowFromQuery($query_get_dr, $request);
				$ins_dr_data['DrugRequest_Version'] = $version_num;
				$ins_dr_data['pmUser_id'] = $this->pmUser_id;

				//копирование заявки
				$result = $this->getFirstRowFromQuery($query_ins_dr, $ins_dr_data);
				if (!empty($result['DrugRequest_id'])) {
					$request_id = $result['DrugRequest_id'];

					//получение данных строк заявки
					$result = $this->db->query($query_get_drr, $request);
					if (is_object($result)) {
						$ins_drr_data = $result->result('array');

						//копирование строк заявки
						foreach($ins_drr_data as $request_row) {
							$request_row['DrugRequest_id'] = $request_id;
							$request_row['pmUser_id'] = $this->pmUser_id;
							$result = $this->getFirstResultFromQuery($query_ins_drr, $request_row);
						}
					}
				}
			}

			$this->db->trans_commit();
		}

		return array(array('Error_Code' => '', 'Error_Msg' => $err_msg));
	}

	/**
	 * Получение списка различий меж текущей и архивной заявкой (возможно не используется)
	 */
	function getArchiveCopyDifferencesProtocol() {
		$err = 'Сравнение заявок в данный момент не доступно';
		$protocol = '';
		$q = "
			select
				DrugRequestPeriod_id,
				PersonRegisterType_id,
				Lpu_id,
				MedPersonal_id
			from
				DrugRequest
			where
				DrugRequest_id = :DrugRequest_id and
				DrugRequest_Version is null;
		";
		$result = $this->db->query($q, array('DrugRequest_id' => $this->DrugRequest_id));
		if ( is_object($result) ) {
			$data = $result->result('array');
			$data = $data[0];
			
			/*if (isset($data['DrugRequestPeriod_id']) && isset($data['PersonRegisterType_id'])) {
				$q = "
					select
						DR.DrugRequest_Summa as summ,
						ADR.DrugRequest_Summa as archive_summ
					from
						DrugRequest DR with (nolock)
						left join DrugRequest ADR with (nolock) on ADR.PersonRegisterType_id = DR.PersonRegisterType_id and ADR.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and ADR.Lpu_id = DR.Lpu_id and ADR.MedPersonal_id = DR.MedPersonal_id and ADR.DrugRequest_Version = 6
					where		
						DR.PersonRegisterType_id = :PersonRegisterType_id and
						DR.DrugRequestPeriod_id = :DrugRequestPeriod_id and
						DR.DrugRequest_Version is NULL
				";
				$q .= " and DR.Lpu_id is not null";
				//$q .= (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) ? " and DR.Lpu_id = :Lpu_id" : " and DR.Lpu_id is not null";
				$q .= (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) ? " and DR.MedPersonal_id = :MedPersonal_id" : " and DR.MedPersonal_id is not null";
				$result = $this->db->query($q, $data);
				if ( is_object($result) ) {
					$requests = $result->result('array');
					$version = 0;
					//получаем номер версии последней копии
					if (count($requests) > 0) {
						$q = "
							select isnull(MAX(DrugRequest_Version), 0) as version
							from
								DrugRequest
							where						
								PersonRegisterType_id = :PersonRegisterType_id and
								DrugRequestPeriod_id = :DrugRequestPeriod_id
						";
						$result = $this->db->query($q, $data);
						if (is_object($result)) {
							$res = $result->result('array');
							if (isset($res[0]) && isset($res[0]['version']) && $res[0]['version'] > 0)
								$version = $res[0]['version'];
						}
					} else {
						$err = 'Список заявок пуст.';
					}
					
					if ($version > 0) {
						foreach($requests as $request) {
							$q = "
								select
									DrugRequest_id
								from
									DrugRequest
								where
									DrugRequest_id = :DrugRequest_id
							";
							$result = $this->db->query($q, array(
								'DrugRequest_id' => $request['DrugRequest_id'],
								'version' => $version
							));
							if ( is_object($result) ) {
								$res = $result->result('array');
								//$protocol .= $res[0]['DrugRequest_id'].'<br/>';
							}
						}
					} else {
						$err = 'Архивная копия отсутствует.';
					}
				}
			}*/
		}
		
		
		$protocol .= '';
		
		return array(array('Error_Code' => '', 'Error_Msg' => $err, 'Protocol' => $protocol));
	}

	/**
	 * По идентификатору региональной заявки создает недостающие заявки МО, а также удаляет лишние заявки МО (нет в списке и не содержат заявок врачей)
	 */
	function createMoDrugRequst() {
		$query = "
		set nocount on;
			declare
				@DrugRequestPeriod_id bigint,
				@PersonRegisterType_id bigint,
				@DrugRequestKind_id bigint,
				@DrugGroup_id bigint,
				@DrugRequestPeriod_begDate datetime,
				@DrugRequestPeriod_endDate datetime,
				@DrugRequestStatus_id bigint,
				@DrugRequestStatus_Code bigint,
				@MoDrugRequestCategory_id bigint,
				@MpDrugRequestCategory_id bigint;
				
			select
				@DrugRequestPeriod_id = dr.DrugRequestPeriod_id,
				@PersonRegisterType_id = dr.PersonRegisterType_id,
				@DrugRequestKind_id = dr.DrugRequestKind_id,
				@DrugGroup_id = dr.DrugGroup_id,
				@DrugRequestPeriod_begDate = drp.DrugRequestPeriod_begDate,
				@DrugRequestPeriod_endDate = drp.DrugRequestPeriod_endDate,
				@DrugRequestStatus_id = dr.DrugRequestStatus_id,
				@DrugRequestStatus_Code = drs.DrugRequestStatus_Code
			from
				DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
			where
				DrugRequest_id = :DrugRequest_id;

			set @DrugRequestStatus_id = (
				case
					when @DrugRequestStatus_Code = 4 then @DrugRequestStatus_id
					else (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 1)
				end
			);

			select
				@MoDrugRequestCategory_id = DrugRequestCategory_id
			from
				DrugRequestCategory
			where
				DrugRequestCategory_SysNick = 'mo';

			select
				@MpDrugRequestCategory_id = DrugRequestCategory_id
			from
				DrugRequestCategory
			where
				DrugRequestCategory_SysNick = 'vrach';

			insert into DrugRequest
				 (
					   Server_id,
					   DrugRequestPeriod_id,
					   DrugRequestStatus_id,
					   DrugRequest_Name,
					   Lpu_id,
					   pmUser_insID,
					   pmUser_updID,
					   DrugRequest_insDT,
					   DrugRequest_updDT,
					   PersonRegisterType_id,
					   DrugRequestCategory_id,
					   DrugRequestKind_id,
					   DrugGroup_id
				 )
			select distinct
				:Server_id,
				@DrugRequestPeriod_id,
				@DrugRequestStatus_id,
				'Заявка МО '+l.Lpu_Nick,
				l.Lpu_id,
				:pmUser_id,
				:pmUser_id,
				dbo.tzGetDate(),
				dbo.tzGetDate(),
				@PersonRegisterType_id,
				@MoDrugRequestCategory_id,
				@DrugRequestKind_id,
				@DrugGroup_id
            from
                v_DrugRequestLpuGroup drlg with (nolock)
                left join v_Lpu l with (nolock) on l.Lpu_id = drlg.Lpu_id
            where
                l.Lpu_id is not null and
                drlg.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                isnull(drlg.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                isnull(drlg.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                isnull(drlg.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                l.Lpu_id not in (
                    select
                        i_dr.Lpu_id
                    from
                        v_DrugRequest i_dr with (nolock)
                    where
                        i_dr.DrugRequest_Version is null and
                        i_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                        isnull(i_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                        isnull(i_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                        isnull(i_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                        i_dr.DrugRequestCategory_id = @MoDrugRequestCategory_id
                );

            delete from
                DrugRequest
            where
                DrugRequest_Version is null and
                DrugRequestPeriod_id = @DrugRequestPeriod_id and
                isnull(PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                isnull(DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                isnull(DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                DrugRequestCategory_id = @MoDrugRequestCategory_id and
				Lpu_id not in (
					select
						i_drlg.Lpu_id
					from
						v_DrugRequestLpuGroup i_drlg with (nolock)
					where
						i_drlg.DrugRequestPeriod_id = DrugRequest.DrugRequestPeriod_id and
				        isnull(i_drlg.PersonRegisterType_id, 0) = isnull(DrugRequest.PersonRegisterType_id, 0) and
				        isnull(i_drlg.DrugRequestKind_id, 0) = isnull(DrugRequest.DrugRequestKind_id, 0) and
				        isnull(i_drlg.DrugGroup_id, 0) = isnull(DrugRequest.DrugGroup_id, 0)
				) and
				Lpu_id not in (
					select
				       i_dr.Lpu_id
				    from
				        v_DrugRequest i_dr with (nolock)
				    where
				        i_dr.DrugRequest_Version is null and
				        i_dr.DrugRequestCategory_id = @MpDrugRequestCategory_id and
						i_dr.Lpu_id = DrugRequest.Lpu_id and
				        i_dr.DrugRequestPeriod_id = DrugRequest.DrugRequestPeriod_id and
				        isnull(i_dr.PersonRegisterType_id, 0) = isnull(DrugRequest.PersonRegisterType_id, 0) and
				        isnull(i_dr.DrugRequestKind_id, 0) = isnull(DrugRequest.DrugRequestKind_id, 0) and
				        isnull(i_dr.DrugGroup_id, 0) = isnull(DrugRequest.DrugGroup_id, 0)
				);
			set nocount off;
		";

		$r = $this->db->query($query, array(
			'DrugRequest_id' => $this->DrugRequest_id,
			'Server_id' => $this->Server_id,
			'pmUser_id' => $this->pmUser_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * По идентификатору региональной заявки возвращает список льготников
	 */
	function getPrivilegePersonList() {
		$q = "
			select
				*
			from
				PersonPrivilege priv with(nolock)
			where
				priv.Person_id in (
					select
						pc.Person_id
					from
						MedStaffRegion msr with (nolock)
						left join PersonCard pc with (nolock) on pc.LpuRegion_id = msr.LpuRegion_id
					where
						msr.MedPersonal_id in (5077, 11678, 33731/*, 41*/)
				) and
				PersonPrivilege_endDate >= @DrugRequestPeriod_begDate and
				PersonPrivilege_begDate <= @DrugRequestPeriod_begDate
		";
		$r = $this->db->query($q, array(
			'DrugRequest_id' => $this->DrugRequest_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение параметров
	 */
	function saveDrugRequestPurchaseSpecParams($data) {

		if (isset($data['DrugRequestPurchaseSpec_pKolvo'])) {
			$price = 0;
			
			$q = "select top 1 DrugRequestPurchaseSpec_Price from DrugRequestPurchaseSpec with(nolock) where DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id";
			$r = $this->db->query($q, $data);
			if (is_object($r)) {
				$result = $r->result('array');
				if (isset($result[0]) && $result[0]['DrugRequestPurchaseSpec_Price'] > 0)
					$price = $result[0]['DrugRequestPurchaseSpec_Price'];
			}
			
			$data['DrugRequestPurchaseSpec_pSum'] = $data['DrugRequestPurchaseSpec_pKolvo'] * $price;
			
			$q = "
				update
					DrugRequestPurchaseSpec
				set
					DrugRequestPurchaseSpec_pKolvo = :DrugRequestPurchaseSpec_pKolvo,
					DrugRequestPurchaseSpec_pSum = :DrugRequestPurchaseSpec_pSum
				where
					DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id;
			";		
			$r = $this->db->query($q, $data);
		}

		$result = array(array('Error_Msg' => ''));

		return $result;
	}

	/**
	 * Получение параметров для заявки МО
	 */
	function getDrugRequestPlanParams($data) {
        //получение данных заявочной кампании
        $reg_dr_data = $this->getRegionDrugRequestByParams(array(
            'DrugRequest_id' => $data['DrugRequest_id']
        ));

        //получение данных заявки
        $query = "
            select
                Lpu_id,
                LpuRegion_id
            from
                v_DrugRequest with (nolock)
            where
                DrugRequest_id = :DrugRequest_id;
        ";
        $dr_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $data['DrugRequest_id']
        ));

        //поиск существующей записи в бд
        $query = "
		    select top 1
		        DrugRequestPlan_id,
		        DrugRequestPlan_Kolvo,
		        DrugRequestPlan_Summa,
		        DrugRequestPlan_CountReq,
		        DrugRequestPlan_FedKolvo,
		        DrugRequestPlan_FedSumma,
		        DrugRequestPlan_CountFed,
		        DrugRequestPlan_RegKolvo,
		        DrugRequestPlan_RegSumma,
		        DrugRequestPlan_CountReg
		    from
		        v_DrugRequestPlan with(nolock)
		    where
		        DrugRequest_id = :RegionDrugRequest_id and
		        isnull(Lpu_id, 0) = isnull(:Lpu_id, 0) and
		        isnull(LpuRegion_id, 0) = isnull(:LpuRegion_id, 0)
		    order by
		        DrugRequestPlan_id;
		";
		$response = $this->getFirstRowFromQuery($query, array(
            'RegionDrugRequest_id' => $reg_dr_data['DrugRequest_id'],
            'Lpu_id' => $dr_data['Lpu_id'],
            'LpuRegion_id' => $dr_data['LpuRegion_id']
        ));

		return $response;
	}

	/**
	 * Сохранение параметров
	 */
	function saveDrugRequestPlanParams($data) {
        //получение данных заявочной кампании
        $reg_dr_data = $this->getRegionDrugRequestByParams(array(
            'DrugRequest_id' => $data['DrugRequest_id']
        ));

        //получение данных заявки
        $query = "
            select
                Lpu_id,
                LpuRegion_id
            from
                v_DrugRequest with (nolock)
            where
                DrugRequest_id = :DrugRequest_id;
        ";
        $dr_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $data['DrugRequest_id']
        ));

        //поиск существующей записи в бд
		$query = "
		    select top 1
		        DrugRequestPlan_id
		    from
		        v_DrugRequestPlan with(nolock)
		    where
		        DrugRequest_id = :RegionDrugRequest_id and
		        isnull(Lpu_id, 0) = isnull(:Lpu_id, 0) and
		        isnull(LpuRegion_id, 0) = isnull(:LpuRegion_id, 0)
		    order by
		        DrugRequestPlan_id;
		";
		$id = $this->getFirstResultFromQuery($query, array(
           'RegionDrugRequest_id' => $reg_dr_data['DrugRequest_id'],
           'Lpu_id' => $dr_data['Lpu_id'],
           'LpuRegion_id' => $dr_data['LpuRegion_id']
        ));
		$data['DrugRequestPlan_id'] = !empty($id) ? $id : null;
		$data['DrugRequest_id'] = $reg_dr_data['DrugRequest_id'];
		$data['Lpu_id'] = !empty($dr_data['Lpu_id']) ? $dr_data['Lpu_id'] : null;
		$data['LpuRegion_id'] = !empty($dr_data['LpuRegion_id']) ? $dr_data['LpuRegion_id'] : null;

        $result = $this->saveObject('DrugRequestPlan', $data);

		return $result;
	}

	/**
     * Вычисление пареметров для заявки МО
	 */
	/*function calculateDrugRequestPlanParams($data) {
        $id_array = array();
        $error = array();

        if (!empty($data['DrugRequest_list'])) {
            $id_array = explode(',', $data['DrugRequest_list']);
        }

        //очистка списка идентификаторов
        for ($i = 0; $i < count($id_array); $i++) {
            if (!is_numeric($id_array[$i]) || empty($id_array[$i])) {
                unset($id_array[$i]);
            }
        }
        
        $this->beginTransaction();

        //расчет количества льготников
        if ($data['object'] == 'Kolvo') {

            foreach($id_array as $id) {
                //получение данных заявки
                $query = "
                    select
                        dr.Lpu_id,
                        dr.PersonRegisterType_id,
                        prt.PersonRegisterType_SysNick,
                        drs.DrugRequestStatus_Code
                    from
                        v_DrugRequest dr with (nolock)
                        left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                        left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    where
                        dr.DrugRequest_id = :DrugRequest_id;
                ";
                $request_data = $this->getFirstRowFromQuery($query, array(
                    'DrugRequest_id' => $id
                ));

                if (!empty($request_data['DrugRequestStatus_Code']) && in_array($request_data['DrugRequestStatus_Code'], array('3', '7'))) { // 3 - Утвержденная; 7 - Утвержденная МЗ.
                    $error[] = 'Недопустимый статус заявки';
                }

                //расчет количества льготников
                if (count($error) == 0) {
                    $query = "
                        declare
                            @cur_date date,
                            @Lpu_id bigint = :Lpu_id,
                            @PersonRegisterType_id bigint = :PersonRegisterType_id,
                            @PersonRegisterType_SysNick varchar(50) = :PersonRegisterType_SysNick,
                            @cnt bigint,
                            @fed_cnt bigint,
                            @reg_cnt bigint;

                        set @cur_date = dbo.tzGetDate();

                        if (substring(isnull(@PersonRegisterType_SysNick, ''), 0, 7) = 'common')
                            begin
                                select
                                    @fed_cnt = sum(case when p.ReceptFinance_id = 1 then 1 else 0 end), -- 1 - Федеральный бюджет
                                    @reg_cnt = sum(case when p.ReceptFinance_id = 2 then 1 else 0 end) -- 2 - Субъект РФ
                                from (
                                    select
                                        pc.Person_id,
                                        pt.ReceptFinance_id
                                    from
                                        v_PersonCard pc with (nolock)
                                        left join v_LpuAttachType lat with (nolock) on lat.LpuAttachType_id = pc.LpuAttachType_id
                                        left join v_PersonPrivilege pp with (nolock) on pp.Person_id = pc.Person_id
                                        left join v_PrivilegeType pt with (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
                                    where
                                        pc.Lpu_id = @Lpu_id and
                                        (pc.PersonCard_begDate is null or pc.PersonCard_begDate <= @cur_date) and
                                        (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= @cur_date) and
                                        lat.LpuAttachType_SysNick = 'main' and --основной тип прикрепления
                                        (pp.PersonPrivilege_begDate is null or pp.PersonPrivilege_begDate <= @cur_date) and
                                        (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= @cur_date) and
                                        isnull(pp.PersonRefuse_IsRefuse, 1) = 1 and -- нет отказа от льготы
                                        pt.ReceptFinance_id is not null
                                    group by
                                        pc.Person_id,
                                            pt.ReceptFinance_id
                                ) p
                            end
                        else
                            begin
                                select
                                    @cnt = count(p.Person_id)
                                from (
                                    select
                                        pc.Person_id
                                    from
                                        v_PersonCard pc with (nolock)
                                        left join v_LpuAttachType lat with (nolock) on lat.LpuAttachType_id = pc.LpuAttachType_id
                                        left join v_PersonRegister pr with (nolock) on pr.Person_id = pc.Person_id
                                    where
                                        pc.Lpu_id = @Lpu_id and
                                        (pc.PersonCard_begDate is null or pc.PersonCard_begDate <= @cur_date) and
                                        (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= @cur_date) and
                                        lat.LpuAttachType_SysNick = 'main' and --основной тип прикрепления
                                        pr.PersonRegisterType_id = @PersonRegisterType_id and
                                        (pr.PersonRegister_setDate is null or pr.PersonRegister_setDate <= @cur_date) and
                                        (pr.PersonRegister_disDate is null or pr.PersonRegister_disDate >= @cur_date)
                                    group by
                                    pc.Person_id
                                ) p
                            end

                        select @cnt as cnt, @fed_cnt as fed_cnt, @reg_cnt as reg_cnt, @PersonRegisterType_SysNick as type_nick;
                    ";
                    $cnt_data = $this->getFirstRowFromQuery($query, $request_data);
                    if (count($cnt_data) > 0) {
                        if ($cnt_data['type_nick'] == 'common_fl') {
                            $cnt_data['cnt'] = $cnt_data['fed_cnt'];
                        }
                        if ($cnt_data['type_nick'] == 'common_rl') {
                            $cnt_data['cnt'] = $cnt_data['reg_cnt'];
                        }

                        $response = $this->saveDrugRequestPlanParams(array(
                            'DrugRequest_id' => $id,
                            'DrugRequestPlan_Kolvo' => $cnt_data['cnt'] > 0 ? $cnt_data['cnt'] : null,
                            'DrugRequestPlan_FedKolvo' => $cnt_data['fed_cnt'] > 0 ? $cnt_data['fed_cnt'] : null,
                            'DrugRequestPlan_RegKolvo' => $cnt_data['reg_cnt'] > 0 ? $cnt_data['reg_cnt'] : null
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $error[] = $response['Error_Msg'];
                        }
                    } else {
                        $error[] = "При расчете количества льготников произошла ошибка";
                    }
                }

                if (count($error) > 0) {
                    break;
                }
            }
        }

        //расчет лимитов
        if ($data['object'] == 'Summa') {
            foreach($id_array as $id) {
                //получение данных заявки, лимитов и количества льготников
                $query = "
                    select
                        prt.PersonRegisterType_SysNick,
                        drs.DrugRequestStatus_Code,
                        drq.DrugRequestQuota_Person as limit,
                        drqf.DrugRequestQuota_Person as fed_limit,
                        drqr.DrugRequestQuota_Person as reg_limit,
                        drp.DrugRequestPlan_Kolvo as cnt,
                        drp.DrugRequestPlan_FedKolvo as fed_cnt,
                        drp.DrugRequestPlan_RegKolvo as reg_cnt
                    from
                        v_DrugRequest dr with (nolock)
                        left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                        left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                        left join v_DrugRequestQuota drq with (nolock) on
                            drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            isnull(drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            drq.DrugFinance_id is null
                        left join v_DrugRequestQuota drqf with (nolock) on
                            drqf.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(drqf.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(drqf.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            isnull(drqf.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            drqf.DrugFinance_id = 3
                        left join v_DrugRequestQuota drqr with (nolock) on
                            drqr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(drqr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(drqr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            isnull(drqr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            drqr.DrugFinance_id = 27
                        outer apply (
                            select top 1
                                i_drp.DrugRequestPlan_Kolvo,
                                i_drp.DrugRequestPlan_FedKolvo,
                                i_drp.DrugRequestPlan_RegKolvo
                            from
                                v_DrugRequestPlan i_drp with (nolock)
                            where
                                i_drp.DrugRequest_id = dr.DrugRequest_id
                            order by
                                i_drp.DrugRequestPlan_id
                        ) drp
                    where
                        dr.DrugRequest_id = :DrugRequest_id;
                ";
                $request_data = $this->getFirstRowFromQuery($query, array(
                    'DrugRequest_id' => $id
                ));

                if (!empty($request_data['DrugRequestStatus_Code']) && in_array($request_data['DrugRequestStatus_Code'], array('3', '7'))) { // 3 - Утвержденная; 7 - Утвержденная МЗ.
                    $error[] = 'Недопустимый статус заявки';
                }

                //расчет количества льготников
                if (count($error) == 0 && count($request_data) > 0) {
                    $saved_data = array();
                    $saved_data['DrugRequest_id'] = $id;

                    if ($request_data['PersonRegisterType_SysNick'] == 'common') {
                        $limit = $request_data['fed_cnt'] > 0 ? $request_data['fed_cnt'] : 0; 
                        $limit = $request_data['fed_limit'] > 0 ? $limit*$request_data['fed_limit'] : 0; 
                        $saved_data['DrugRequestPlan_FedSumma'] = $limit > 0 ? $limit : null;

                        $limit = $request_data['reg_cnt'] > 0 ? $request_data['reg_cnt'] : 0;
                        $limit = $request_data['reg_limit'] > 0 ? $limit*$request_data['reg_limit'] : 0;
                        $saved_data['DrugRequestPlan_RegSumma'] = $limit > 0 ? $limit : null;
                    } else {
                        $limit = $request_data['cnt'] > 0 ? $request_data['cnt'] : 0;
                        $limit = $request_data['limit'] > 0 ? $limit*$request_data['limit'] : 0;
                        $saved_data['DrugRequestPlan_Summa'] = $limit > 0 ? $limit : null;
                    }

                    $response = $this->saveDrugRequestPlanParams($saved_data);
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }
                }

                if (count($error) > 0) {
                    break;
                }
            }
        }

        $result = array();
        if (count($error) > 0) {
            //откат изменений при наличии ошибок
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }
        return $result;
	}*/

    /**
     * Вычисление режима расчета плановых показателей по нику регистра заявки
     */
    function getCalculatingModeByPersonRegisterType($type_nick) {
        $calc_mode = 'undefined'; //режим подсчета, один из вариантов: 'fed_reg', 'fd_and_rg' - по наличию фед. льготы и/или рег. льготы (fd_and_rg не предусматривает раздельного подсчета по каждой льготе), 'reg' - по наличию рег. льготы, 'fed' - по наличию фед. льготы, 'register' - по регистру заболеваний, 'undefined' - метод не определен
        $region = $_SESSION['region']['nick'];

        switch ($type_nick) {
            case 'common':
                if ($region != 'kz') {
                    $calc_mode = 'fed_reg';
                }
                break;
            case 'common_fl':
                if ($region != 'kz') {
                    $calc_mode = 'fed';
                }
                break;
            case 'common_rl':
                if ($region != 'kz') {
                    $calc_mode = 'reg';
                }
                break;
            case 'diab_fl':
                if ($region == 'ufa') {
                    $calc_mode = 'fed';
                }
                break;
            case 'diab_rl':
                if ($region == 'ufa') {
                    $calc_mode = 'reg';
                }
                break;
            case 'orphan':
            case 'nolos':
                if ($region == 'ufa') {
                    $calc_mode = 'fd_and_rg';
                }
                break;
            default:
                if ($region != 'ufa' && $region != 'kz') {
                    $calc_mode = 'register';
                }
                break;
        }

        return $calc_mode;
    }

	/**
     * Вычисление пареметров для заявочной кампании
	 */
	function calculateDrugRequestPlanRegionParams($data) {
        $mode = !empty($data['mode']) ? $data['mode'] : 'all'; //all - расчет и количества льготников и объемов финансирования; sum - расчет только обьемов финансирования (на основе уже существующих данных о количестве льготников)
        $calc_mode = 'undefined';
        $region = $_SESSION['region']['nick'];
        $result = array();

        try {
            $this->beginTransaction();

            //получение данных заявочной кампании
            $query = "
                select
                    dr.DrugRequest_id,
                    dr.DrugRequestPeriod_id,
                    dr.PersonRegisterType_id,
                    dr.DrugRequestKind_id,
                    dr.DrugGroup_id,
                    dr.DrugRequestCategory_id,
                    dr.DrugRequest_Version,
                    drp.DrugRequestPlan_id,
                    prt.PersonRegisterType_SysNick,
                    drs.DrugRequestStatus_Code
                from
                    v_DrugRequest dr with (nolock)
                    left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    outer apply (
                        select top 1
                            i_drp.DrugRequestPlan_id
                        from
                            v_DrugRequestPlan i_drp with (nolock)
                        where
                            i_drp.DrugRequest_id = dr.DrugRequest_id and
                            i_drp.Lpu_id is null and
                            i_drp.LpuRegion_id is null
                        order by
                            i_drp.DrugRequestPlan_id
                    ) drp
                where
                    dr.DrugRequest_id = :RegionDrugRequest_id;
            ";
            $dr_data = $this->getFirstRowFromQuery($query, array(
                'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
            ));
            if (empty($dr_data['DrugRequest_id'])) {
                throw new Exception("При получении данных заявочной кампании произошла ошибка");
            }

            //прверка статуса, подсчеты доступны только для заявочных кампаний со статусом "Начальная"
            if ($dr_data['DrugRequestStatus_Code'] != '1') { //1 = Начальная
                throw new Exception("Расчет возможен только для заявочных кампаний со статусом\"Начальная\"");
            }

            //определние режима подсчета
            if (!empty($dr_data['PersonRegisterType_SysNick'])) {
                $calc_mode = $this->getCalculatingModeByPersonRegisterType($dr_data['PersonRegisterType_SysNick']);
            }
            if ($calc_mode == 'undefined') {
                throw new Exception("Для указанного регистра метод расчета не определен");
            }

            //полученеи нормативов заявочной кампании
            $query = "
                select
                    isnull(max(p.limit), 0) as limit,
                    isnull(max(p.fed_limit), 0) as fed_limit,
                    isnull(max(p.reg_limit), 0) as reg_limit
                from (
                    select
                        (case
                            when drq.DrugFinance_id is null then drq.DrugRequestQuota_Person
                            else null
                        end) as limit,
                        (case
                            when drq.DrugFinance_id = 3 then drq.DrugRequestQuota_Person
                            else null
                        end) as fed_limit,
                        (case
                            when drq.DrugFinance_id = 27 then drq.DrugRequestQuota_Person
                            else null
                        end) as reg_limit
                    from
                        v_DrugRequestQuota drq with (nolock)
                    where
                        drq.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(drq.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        isnull(drq.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        isnull(drq.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0)
                ) p
            ";
            $quota_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
                'DrugGroup_id' => $dr_data['DrugGroup_id'],
                'DrugRequestKind_id' => $dr_data['DrugRequestKind_id']
            ));
            if (!isset($quota_data['limit'])) {
                throw new Exception("При получении данных о нормативах произошла ошибка");
            }

            if ($mode == 'all') { //режим одновременного подсчета и количества и объемов финансирвоания
                //получение списка МО и участков, а также подсчет льготников по ним
                $select = "";
                $join = "";

                switch($calc_mode) {
                    case 'fd_and_rg':
                        $select .= ", lgot_cnt.cnt as lgot_cnt";
                        $join .= "
                            outer apply (
                                select
                                    count(i_p.Person_id) as cnt
                                from (
                                    select
                                        i_pc.Person_id
                                    from
                                        v_PersonCard_all i_pc with (nolock)
                                        left join v_LpuAttachType i_lat with (nolock) on i_lat.LpuAttachType_id = i_pc.LpuAttachType_id
                                        left join v_PersonPrivilege i_pp with (nolock) on i_pp.Person_id = i_pc.Person_id
                                        left join v_PrivilegeType i_pt with (nolock) on i_pt.PrivilegeType_id = i_pp.PrivilegeType_id
                                    where
                                        i_pc.Lpu_id = lr.Lpu_id and
                                        i_pc.LpuRegion_id = lr.LpuRegion_id and
                                        (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @cur_date) and
                                        (i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @cur_date) and
                                        i_lat.LpuAttachType_SysNick = 'main' and --основной тип прикрепления
                                        (i_pp.PersonPrivilege_begDate is null or i_pp.PersonPrivilege_begDate <= @cur_date) and
                                        (i_pp.PersonPrivilege_endDate is null or i_pp.PersonPrivilege_endDate >= @cur_date) and
                                        isnull(i_pp.PersonRefuse_IsRefuse, 1) = 1 and -- нет отказа от льготы
                                        i_pt.ReceptFinance_id is not null
                                    group by
                                        i_pc.Person_id
                                ) i_p
                            ) lgot_cnt
                        ";
                        break;
                    case 'fed_reg':
                    case 'fed':
                    case 'reg':
                        if ($calc_mode == 'fed') {
                            $select  .= ", lgot_cnt.fed_cnt as lgot_cnt";
                        }
                        if ($calc_mode == 'reg') {
                            $select  .= ", lgot_cnt.reg_cnt as lgot_cnt";
                        }
                        if ($calc_mode == 'fed_reg') {
                            $select  .= ", lgot_cnt.fed_cnt as fed_lgot_cnt";
                            $select  .= ", lgot_cnt.reg_cnt as reg_lgot_cnt";
                        }

                        $join_where = "";
                        if ($dr_data['PersonRegisterType_SysNick'] == 'common_rl' && $region == 'ufa') { //для регистра "РЛО: общетерапевтическая группа" в Уфе, мы должны исключить некоторые категории льготников
							$join_where = " and (i_pt.PrivilegeType_SysNick is null or i_pt.PrivilegeType_SysNick not in ('shizof_epileps', 'pregnancy', 'breastfeeding'))";
						}

                        $join .= "
                            outer apply (
                                select
                                    isnull(sum(case when i_p.ReceptFinance_id = 1 then 1 else 0 end), 0) as fed_cnt, -- 1 - Федеральный бюджет
                                    isnull(sum(case when i_p.ReceptFinance_id = 2 then 1 else 0 end), 0) as reg_cnt -- 2 - Субъект РФ
                                from (
                                    select
                                        i_pc.Person_id,
                                        i_pt.ReceptFinance_id
                                    from
                                        v_PersonCard_all i_pc with (nolock)
                                        left join v_LpuAttachType i_lat with (nolock) on i_lat.LpuAttachType_id = i_pc.LpuAttachType_id
                                        left join v_PersonPrivilege i_pp with (nolock) on i_pp.Person_id = i_pc.Person_id
                                        left join v_PrivilegeType i_pt with (nolock) on i_pt.PrivilegeType_id = i_pp.PrivilegeType_id
                                    where
                                        i_pc.Lpu_id = lr.Lpu_id and
                                        i_pc.LpuRegion_id = lr.LpuRegion_id and
                                        (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @cur_date) and
                                        (i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @cur_date) and
                                        i_lat.LpuAttachType_SysNick = 'main' and --основной тип прикрепления
                                        (i_pp.PersonPrivilege_begDate is null or i_pp.PersonPrivilege_begDate <= @cur_date) and
                                        (i_pp.PersonPrivilege_endDate is null or i_pp.PersonPrivilege_endDate >= @cur_date) and
                                        isnull(i_pp.PersonRefuse_IsRefuse, 1) = 1 and -- нет отказа от льготы
                                        i_pt.ReceptFinance_id is not null
                                        $join_where
                                    group by
                                        i_pc.Person_id,
                                        i_pt.ReceptFinance_id
                                ) i_p
                            ) lgot_cnt
                        ";
                        break;
                    case 'register':
                        $select  .= ", lgot_cnt.cnt as lgot_cnt";
                        $join .= "
                            outer apply (
                                select
                                    count(i_p.Person_id) as cnt
                                from (
                                    select
                                        i_pc.Person_id
                                    from
                                        v_PersonCard_all i_pc with (nolock)
                                        left join v_LpuAttachType i_lat with (nolock) on i_lat.LpuAttachType_id = i_pc.LpuAttachType_id
                                        left join v_PersonRegister i_pr with (nolock) on i_pr.Person_id = i_pc.Person_id
                                    where
                                        i_pc.Lpu_id = lr.Lpu_id and
                                        i_pc.LpuRegion_id = lr.LpuRegion_id and
                                        (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @cur_date) and
                                        (i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @cur_date) and
                                        i_lat.LpuAttachType_SysNick = 'main' and --основной тип прикрепления
                                        i_pr.PersonRegisterType_id = drlg.PersonRegisterType_id and
                                        (i_pr.PersonRegister_setDate is null or i_pr.PersonRegister_setDate <= @cur_date) and
                                        (i_pr.PersonRegister_disDate is null or i_pr.PersonRegister_disDate >= @cur_date)
                                    group by
                                        i_pc.Person_id
                                ) i_p
                            ) lgot_cnt
                        ";
                        break;
                }

                $query = "
                    declare
                        @cur_date date;

                    set @cur_date = dbo.tzGetDate();

                    select
                        lr.Lpu_id,
                        lr.LpuRegion_id,
                        drp_l.DrugRequestPlan_id as DrugRequestPlanLpu_id,
                        drp_lr.DrugRequestPlan_id as DrugRequestPlanLpuRegion_id
                        {$select}
                    from
                        v_DrugRequestLpuGroup drlg with (nolock)
                        inner join v_LpuRegion lr with (nolock) on lr.Lpu_id = drlg.Lpu_id
                        outer apply (
                            select top 1
                                i_drp_l.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_l with (nolock)
                            where
                                i_drp_l.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_l.Lpu_id = lr.Lpu_id and
                                i_drp_l.LpuRegion_id is null
                            order by
                                i_drp_l.DrugRequestPlan_id
                        ) drp_l
                        outer apply (
                            select top 1
                                i_drp_lr.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_lr with (nolock)
                            where
                                i_drp_lr.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_lr.LpuRegion_id = lr.LpuRegion_id
                            order by
                                i_drp_lr.DrugRequestPlan_id
                        ) drp_lr
                        {$join}
                    where
                        drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(drlg.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        isnull(drlg.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        isnull(drlg.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0);
                ";
                $lr_array = $this->queryResult($query, array(
                    'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
                    'DrugGroup_id' => $dr_data['DrugGroup_id'],
                    'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
                    'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
                ));
            } else if ($mode == 'sum') { //режим подсчета только объемов финансирования
                //получение списка МО и участков, а также подсчет льготников по ним
                $select = "";
                $join = "";

                switch($calc_mode) {
                    case 'fed_reg':
                        $select  .= ", drp_l.fed_cnt as l_fed_lgot_cnt";
                        $select  .= ", drp_lr.fed_cnt as fed_lgot_cnt";
                        $select  .= ", drp_l.reg_cnt as l_reg_lgot_cnt";
                        $select  .= ", drp_lr.reg_cnt as reg_lgot_cnt";
                        break;
                    case 'fed':
                    case 'reg':
                    case 'fd_and_rg':
                    case 'register':
                        $select  .= ", drp_l.cnt as l_lgot_cnt";
                        $select  .= ", drp_lr.cnt as lgot_cnt";
                        break;
                }

                $query = "
                    select
                        lr.Lpu_id,
                        lr.LpuRegion_id,
                        drp_l.DrugRequestPlan_id as DrugRequestPlanLpu_id,
                        drp_lr.DrugRequestPlan_id as DrugRequestPlanLpuRegion_id
                        {$select}
                    from
                        v_DrugRequestLpuGroup drlg with (nolock)
                        left join v_LpuRegion lr with (nolock) on lr.Lpu_id = drlg.Lpu_id
                        outer apply (
                            select top 1
                                i_drp_l.DrugRequestPlan_id,
                                i_drp_l.DrugRequestPlan_Kolvo as cnt,
                                i_drp_l.DrugRequestPlan_FedKolvo as fed_cnt,
                                i_drp_l.DrugRequestPlan_RegKolvo as reg_cnt
                            from
                                v_DrugRequestPlan i_drp_l with (nolock)
                            where
                                i_drp_l.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_l.Lpu_id = lr.Lpu_id and
                                i_drp_l.LpuRegion_id is null
                            order by
                                i_drp_l.DrugRequestPlan_id
                        ) drp_l
                        outer apply (
                            select top 1
                                i_drp_lr.DrugRequestPlan_id,
                                i_drp_lr.DrugRequestPlan_Kolvo as cnt,
                                i_drp_lr.DrugRequestPlan_FedKolvo as fed_cnt,
                                i_drp_lr.DrugRequestPlan_RegKolvo as reg_cnt,
                                i_drp_lr.DrugRequestPlan_KolvoDT
                            from
                                v_DrugRequestPlan i_drp_lr with (nolock)
                            where
                                i_drp_lr.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_lr.LpuRegion_id = lr.LpuRegion_id
                            order by
                                i_drp_lr.DrugRequestPlan_id
                        ) drp_lr
                    where
                        drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(drlg.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        isnull(drlg.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        isnull(drlg.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                        drp_lr.DrugRequestPlan_id is not null and
                        drp_lr.DrugRequestPlan_KolvoDT is not null;
                ";
                $lr_array = $this->queryResult($query, array(
                    'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
                    'DrugGroup_id' => $dr_data['DrugGroup_id'],
                    'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
                    'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
                ));
            }

            if (count($lr_array) == 0) {
                throw new Exception("Список заявок для расчета пуст");
            }

            //обработка и сохранение показателей по участкам
            $l_array = array();
            foreach($lr_array as $lr_data) {
                $lpu_id = $lr_data['Lpu_id'];

                if (!isset($l_array[$lpu_id])) {
                    $l_array[$lpu_id] = array(
                        'DrugRequestPlan_id' => $lr_data['DrugRequestPlanLpu_id'],
                        'DrugRequestPlan_FedKolvo' => 0,
                        'DrugRequestPlan_FedSumma' => 0,
                        'DrugRequestPlan_RegKolvo' => 0,
                        'DrugRequestPlan_RegSumma' => 0,
                        'DrugRequestPlan_Kolvo' => 0,
                        'DrugRequestPlan_Summa' => 0
                    );
                    if ($mode == 'sum') {
                        if ($calc_mode == 'fed_reg') {
                            $l_array[$lpu_id]['DrugRequestPlan_FedSumma'] = $lr_data['l_fed_lgot_cnt']*1*$quota_data['fed_limit'];
                            $l_array[$lpu_id]['DrugRequestPlan_RegSumma'] = $lr_data['l_reg_lgot_cnt']*1*$quota_data['reg_limit'];
                        }
                        if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
                            $l_array[$lpu_id]['DrugRequestPlan_Summa'] = $lr_data['l_lgot_cnt']*1*$quota_data['limit'];
                        }
                    }
                }

                $save_data = array(
                    'DrugRequestPlan_id' => $lr_data['DrugRequestPlanLpuRegion_id']
                );
                if (empty($save_data['DrugRequestPlan_id'])) {
                    $save_data['DrugRequest_id'] = $data['RegionDrugRequest_id'];
                    $save_data['Lpu_id'] = $lr_data['Lpu_id'];
                    $save_data['LpuRegion_id'] = $lr_data['LpuRegion_id'];
                }

                if ($calc_mode == 'fed_reg') {
                    $save_data['DrugRequestPlan_FedSumma'] = $lr_data['fed_lgot_cnt']*1*$quota_data['fed_limit'];
                    $save_data['DrugRequestPlan_RegSumma'] = $lr_data['reg_lgot_cnt']*1*$quota_data['reg_limit'];
                    if ($mode == 'all') {
                        $save_data['DrugRequestPlan_FedKolvo'] = $lr_data['fed_lgot_cnt']*1;
                        $save_data['DrugRequestPlan_RegKolvo'] = $lr_data['reg_lgot_cnt']*1;
                        $l_array[$lpu_id]['DrugRequestPlan_FedKolvo'] += $save_data['DrugRequestPlan_FedKolvo'];
                        $l_array[$lpu_id]['DrugRequestPlan_RegKolvo'] += $save_data['DrugRequestPlan_RegKolvo'];
                        $l_array[$lpu_id]['DrugRequestPlan_FedSumma'] += $save_data['DrugRequestPlan_FedSumma'];
                        $l_array[$lpu_id]['DrugRequestPlan_RegSumma'] += $save_data['DrugRequestPlan_RegSumma'];
                    }
                }
                if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
                    $save_data['DrugRequestPlan_Summa'] = $lr_data['lgot_cnt']*1*$quota_data['limit'];
                    if ($mode == 'all') {
                        $save_data['DrugRequestPlan_Kolvo'] = $lr_data['lgot_cnt']*1;
                        $l_array[$lpu_id]['DrugRequestPlan_Kolvo'] += $save_data['DrugRequestPlan_Kolvo'];
                        $l_array[$lpu_id]['DrugRequestPlan_Summa'] += $save_data['DrugRequestPlan_Summa'];
                    }
                }
                if ($mode == 'all') {
                    $save_data['DrugRequestPlan_KolvoDT'] = date('Y-m-d H:i:s');
                }

                $res = $this->saveObject('DrugRequestPlan', $save_data);
                if (!empty($res['Error_Msg'])) {
                    throw new Exception($res['Error_Msg']);
                }
            }

            //сохраненеие показателй по МО
            foreach($l_array as $lpu_id => $l_data) {
                $save_data = array(
                    'DrugRequestPlan_id' => $l_data['DrugRequestPlan_id']
                );
                if (empty($save_data['DrugRequestPlan_id'])) {
                    $save_data['DrugRequest_id'] = $data['RegionDrugRequest_id'];
                    $save_data['Lpu_id'] = $lpu_id;
                }

                if ($calc_mode == 'fed_reg') {
                    if ($mode == 'all') {
                        $save_data['DrugRequestPlan_FedKolvo'] = $l_data['DrugRequestPlan_FedKolvo'];
                        $save_data['DrugRequestPlan_RegKolvo'] = $l_data['DrugRequestPlan_RegKolvo'];
                    }
                    $save_data['DrugRequestPlan_FedSumma'] = $l_data['DrugRequestPlan_FedSumma'];
                    $save_data['DrugRequestPlan_RegSumma'] = $l_data['DrugRequestPlan_RegSumma'];
                }
                if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
                    if ($mode == 'all') {
                        $save_data['DrugRequestPlan_Kolvo'] = $l_data['DrugRequestPlan_Kolvo'];
                    }
                    $save_data['DrugRequestPlan_Summa'] = $l_data['DrugRequestPlan_Summa'];
                }
                if ($mode == 'all') {
                    $save_data['DrugRequestPlan_KolvoDT'] = date('Y-m-d H:i:s');
                }

                $res = $this->saveObject('DrugRequestPlan', $save_data);
                if (!empty($res['Error_Msg'])) {
                    throw new Exception($res['Error_Msg']);
                }
            }

            $this->commitTransaction();
            $result['success'] = true;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
	}

    /**
     * Вычисление параметров для заявки участка (либо заявки МО), возможно вычисление для всех заявок в составе заявочной кампании
     */
    function calculateDrugRequestPlanLpuRegionParams($data) {
		if (!empty($data['background_mode_enabled'])) {
			ignore_user_abort(true); // игнорирует отключение пользователя и позволяет скрипту быть запущенным постоянно
			set_time_limit(7200); // это может выполняться весьма и весьма долго

			ob_start();
			echo json_encode(array("success" => "true"));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}

        $region = $_SESSION['region']['nick'];
        $mode = null;
        $calc_mode = 'undefined';
        $result = array();
		$transaction_disabled = (isset($data['transaction_disabled']) && $data['transaction_disabled']);

        try {
        	if (!$transaction_disabled) {
				$this->beginTransaction();
			}

            //настройка режима метода
            if (!empty($data['LpuRegionDrugRequest_id'])) {
                $mode = 'lpu_region'; //расчет данных по участку
                $data['DrugRequest_id'] = $data['LpuRegionDrugRequest_id'];
            } else if (!empty($data['RegionDrugRequest_id'])) {
                $mode = 'region'; //расчет данных по всем заявкам завочной кампании
                $data['DrugRequest_id'] = $data['RegionDrugRequest_id'];
            } else {
                $mode = 'lpu'; //рассчет данных по МО и всем участкам включенным в МО
            }

            //получение данных заявочной кампании
            $reg_dr_data = $this->getRegionDrugRequestByParams($data);
            if (empty($reg_dr_data['DrugRequest_id'])) {
                throw new Exception("При получении данных заявочной кампании произошла ошибка");
            }

            //получение данных заявки
            if ($mode == 'lpu_region') {
                $query = "
                    select
                        dr.DrugRequest_id,
                        dr.Lpu_id,
                        dr.LpuRegion_id,
                        drp_l.DrugRequestPlan_id as DrugRequestPlanLpu_id,
                        drp_lr.DrugRequestPlan_id as DrugRequestPlanLpuRegion_id,
                        prt.PersonRegisterType_SysNick,
                        drs.DrugRequestStatus_Code
                    from
                        v_DrugRequest dr with (nolock)
                        left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                        left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                        outer apply (
                            select top 1
                                i_drp_l.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_l with (nolock)
                            where
                                i_drp_l.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_l.Lpu_id = dr.Lpu_id and
                                i_drp_l.LpuRegion_id is null
                            order by
                                i_drp_l.DrugRequestPlan_id
                        ) drp_l
                        outer apply (
                            select top 1
                                i_drp_lr.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_lr with (nolock)
                            where
                                i_drp_lr.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_lr.Lpu_id = dr.Lpu_id and
                                i_drp_lr.LpuRegion_id = dr.LpuRegion_id
                            order by
                                i_drp_lr.DrugRequestPlan_id
                        ) drp_lr
                    where
                        dr.DrugRequest_id = :LpuRegionDrugRequest_id;
                ";
                $dr_data = $this->getFirstRowFromQuery($query, array(
                    'LpuRegionDrugRequest_id' => $data['LpuRegionDrugRequest_id'],
                    'RegionDrugRequest_id' => !empty($reg_dr_data['DrugRequest_id']) ? $reg_dr_data['DrugRequest_id'] : null
                ));

                if (empty($dr_data['LpuRegion_id'])) { //1 - Начальная
                    throw new Exception("В заявке не указан уасток");
                }
            } else if ($mode == 'lpu') {
                $query = "
                    select
                        dr.DrugRequest_id,
                        dr.Lpu_id,
                        prt.PersonRegisterType_SysNick,
                        drs.DrugRequestStatus_Code
                    from
                        v_DrugRequest dr with (nolock)
                        left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                        left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    where
                        dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                        isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and
                        dr.Lpu_id = :Lpu_id;
                ";
                $dr_data = $this->getFirstRowFromQuery($query, array(
                    'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $data['PersonRegisterType_id'],
                    'DrugRequestKind_id' => $data['DrugRequestKind_id'],
                    'DrugGroup_id' => $data['DrugGroup_id'],
                    'DrugRequest_Version' => isset($data['DrugRequest_Version']) ? $data['DrugRequest_Version'] : null,
                    'Lpu_id' => $data['Lpu_id']
                ));
            } else if ($mode == 'region') {
                $query = "
                    select
                        dr.DrugRequest_id,
                        dr.Lpu_id,
                        prt.PersonRegisterType_SysNick,
                        drs.DrugRequestStatus_Code
                    from
                        v_DrugRequest dr with (nolock)
                        left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                        left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    where
                        dr.DrugRequest_id = :DrugRequest_id;
                ";
                $dr_data = $this->getFirstRowFromQuery($query, array(
                    'DrugRequest_id' => $data['DrugRequest_id']
                ));
            }

            if (empty($dr_data['DrugRequest_id'])) {
                throw new Exception("При получении данных заявки произошла ошибка");
            }

            //прверка статуса, подсчеты доступны только для заявочных кампаний со статусом "Начальная"
            if ($dr_data['DrugRequestStatus_Code'] != '1' && empty($data['status_check_disabled'])) { //1 - Начальная
                throw new Exception("Расчет возможен только для заявочных кампаний со статусом\"Начальная\"");
            }

            //определние режима подсчета
            if (!empty($dr_data['PersonRegisterType_SysNick'])) {
                $calc_mode = $this->getCalculatingModeByPersonRegisterType($dr_data['PersonRegisterType_SysNick']);
            }
            if ($calc_mode == 'undefined') {
                throw new Exception("Для указанного регистра метод расчета не определен");
            }

            //расчет объема по заявке участка
            $select = "";
            $where = "";

            switch($calc_mode) {
                case 'fed_reg':
                    $select = "
                        sum(case when p.DrugFinance_id = @DrugFinanceFed_id then isnull(p.Kolvo*p.Price, 0) else 0 end) as fed_sum,
                        sum(case when p.DrugFinance_id = @DrugFinanceReg_id then isnull(p.Kolvo*p.Price, 0) else 0 end) as reg_sum
                    ";
                    break;
                case 'fed':
                case 'reg':
                case 'fd_and_rg':
                case 'register':
                    $select = "sum(isnull(Kolvo*Price, 0)) as req_sum";
                    break;
            }

            if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
                $df_sub_query = "
                    select
                        null as DrugFinance_id
                ";
            } else {
                $df_sub_query = "
                    select top 1
                        ii_drr.DrugFinance_id
                    from
                        v_DrugRequestRow ii_drr with (nolock)
                    where
                        ii_drr.DrugRequest_id = i_dr.DrugRequest_id and
                        isnull(ii_drr.DrugComplexMnn_id, 0) = isnull(i_drpo.DrugComplexMnn_id, 0) and
                        isnull(ii_drr.TRADENAMES_id, 0) = isnull(i_drpo.Tradenames_id, 0)
                    order by
                        ii_drr.DrugFinance_id desc, ii_drr.DrugRequestRow_id
                ";
            }

            //получение списка заявок участков
            $lr_array = array();
            if ($mode == 'lpu_region') {
                $lr_array[] = array(
                    'Lpu_id' => $dr_data['Lpu_id'],
                    'LpuRegion_id' => $dr_data['LpuRegion_id'],
                    'DrugRequestPlanLpu_id' => $dr_data['DrugRequestPlanLpu_id'],
                    'DrugRequestPlanLpuRegion_id' => $dr_data['DrugRequestPlanLpuRegion_id'],
                    'LpuRegionDrugRequest_id' => $data['LpuRegionDrugRequest_id']
                );
            } else if ($mode == 'lpu' || $mode == 'region') {
                $query = "
                    select
                        lr.Lpu_id,
                        lr.LpuRegion_id,
                        drp_l.DrugRequestPlan_id as DrugRequestPlanLpu_id,
                        drp_lr.DrugRequestPlan_id as DrugRequestPlanLpuRegion_id,
                        dr.DrugRequest_id as LpuRegionDrugRequest_id
                    from
                        v_DrugRequestLpuGroup drlg with (nolock)
                        left join v_LpuRegion lr with (nolock) on lr.Lpu_id = drlg.Lpu_id
                        outer apply (
                            select top 1
                                DrugRequest_id
                            from
                                v_DrugRequest i_dr with (nolock)
                            where
                                i_dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                                isnull(i_dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                                isnull(i_dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                                isnull(i_dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                                isnull(i_dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and
                                i_dr.Lpu_id = lr.Lpu_id and
                                i_dr.LpuRegion_id = lr.LpuRegion_id
                        ) dr
                        outer apply (
                            select top 1
                                i_drp_l.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_l with (nolock)
                            where
                                i_drp_l.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_l.Lpu_id = lr.Lpu_id and
                                i_drp_l.LpuRegion_id is null
                            order by
                                i_drp_l.DrugRequestPlan_id
                        ) drp_l
                        outer apply (
                            select top 1
                                i_drp_lr.DrugRequestPlan_id
                            from
                                v_DrugRequestPlan i_drp_lr with (nolock)
                            where
                                i_drp_lr.DrugRequest_id = :RegionDrugRequest_id and
                                i_drp_lr.LpuRegion_id = lr.LpuRegion_id
                            order by
                                i_drp_lr.DrugRequestPlan_id
                        ) drp_lr
                    where
                        drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                        isnull(drlg.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
                        isnull(drlg.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
                        isnull(drlg.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
                        (:Lpu_id is null or drlg.Lpu_id = :Lpu_id) and
                        dr.DrugRequest_id is not null;
                ";
                $lr_array = $this->queryResult($query, array(
                    'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
                    'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
                    'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
                    'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version'],
                    'Lpu_id' => $dr_data['Lpu_id'],
                    'RegionDrugRequest_id' => $reg_dr_data['DrugRequest_id']
                ));
            }

            $lpu_sum_data = array();

            foreach($lr_array as $lr_data) {
            	//получение количества медикаментов за переделами участковой заявки
                $query = "
                    declare
                        @LpuRegionDrugRequest_id bigint = :LpuRegionDrugRequest_id,
                        @Lpu_id bigint = :Lpu_id,
                        @DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
                        @PersonRegisterType_id bigint = :PersonRegisterType_id,
                        @DrugRequestKind_id bigint = :DrugRequestKind_id,
                        @DrugGroup_id bigint = :DrugGroup_id,
                        @DrugRequest_Version bigint = :DrugRequest_Version,
                        @DrugRequestProperty_id bigint = :DrugRequestProperty_id,
                        @DrugRequestPropertyFed_id bigint = :DrugRequestPropertyFed_id,
                        @DrugRequestPropertyReg_id bigint = :DrugRequestPropertyReg_id,
                        @DrugFinanceFed_id bigint = 3,
                        @DrugFinanceReg_id bigint = 27;

                    select
                        {$select}
                    from (
                        select
                            drug_list.DrugComplexMnn_id,
                            drug_list.Tradenames_id,
                            drug_list.DrugFinance_id,
                            drug_list.Kolvo,
                            dlr.Price
                        from
                            v_DrugRequestPersonOrder drpo with (nolock)
                            outer apply (
                                select
                                    i_drpo.DrugComplexMnn_id,
                                    i_drpo.Tradenames_id,
                                    i_drr.DrugFinance_id,
                                    sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo
                                from
                                    v_DrugRequest i_dr with (nolock)
                                    inner join v_DrugRequestPersonOrder i_drpo with (nolock) on i_drpo.DrugRequest_id = i_dr.DrugRequest_id
                                    outer apply (
                                        {$df_sub_query}
                                    ) i_drr
                                where
                                	i_dr.DrugRequest_id <> @LpuRegionDrugRequest_id and -- исключаем собственную разанрядку
                                    i_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                                    isnull(i_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                                    isnull(i_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                                    isnull(i_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                                    isnull(i_dr.DrugRequest_Version, 0) = isnull(@DrugRequest_Version, 0) and
                                    (i_dr.LpuRegion_id is not null or i_dr.MedPersonal_id is not null) and
                                    i_drpo.Person_id = drpo.Person_id and
                                    i_drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
                                    (
                                        i_drpo.DrugComplexMnn_id is not null or
                                        i_drpo.Tradenames_id is not null
                                    ) and
									i_dr.Lpu_id = @Lpu_id
                                group by
                                    i_drpo.DrugComplexMnn_id,
                                    i_drpo.Tradenames_id,
                                    i_drr.DrugFinance_id
                            ) drug_list
                            outer apply (
                                select top 1
                                    (case
                                        when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
                                        else i_dlr.DrugListRequest_Price
                                    end) as Price
                                from
                                    v_DrugListRequest i_dlr with (nolock)
                                    left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drug_list.Tradenames_id
                                where
                                    i_dlr.DrugComplexMnn_id = drug_list.DrugComplexMnn_id
                                     and (
                                        (@DrugRequestProperty_id is not null and i_dlr.DrugRequestProperty_id  = @DrugRequestProperty_id) or
                                        (drug_list.DrugFinance_id = @DrugFinanceFed_id and i_dlr.DrugRequestProperty_id  = @DrugRequestPropertyFed_id) or
                                        (drug_list.DrugFinance_id = @DrugFinanceReg_id and i_dlr.DrugRequestProperty_id  = @DrugRequestPropertyReg_id)
                                    )
                                order by
                                    DrugListRequest_insDT desc
                            ) dlr
                        where
                            drpo.DrugRequest_id = @LpuRegionDrugRequest_id and
                            drpo.DrugComplexMnn_id is null and
                            drpo.Tradenames_id is null
                    ) p
                ";
                $sum_data = $this->getFirstRowFromQuery($query, array(
                    'LpuRegionDrugRequest_id' => $lr_data['LpuRegionDrugRequest_id'],
                    'Lpu_id' => $lr_data['Lpu_id'],
                    'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
                    'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
                    'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
                    'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version'],
                    'DrugRequestProperty_id' => $reg_dr_data['DrugRequestProperty_id'],
                    'DrugRequestPropertyFed_id' => $reg_dr_data['DrugRequestPropertyFed_id'],
                    'DrugRequestPropertyReg_id' => $reg_dr_data['DrugRequestPropertyReg_id']
                ));
                if (!is_array($sum_data) || count($sum_data) == 0) {
                    throw new Exception("При расчете объема заявок участков произошла ошибка");
                }

                //добавляем в полученную сумму собственную разнарядку + резерв (т.е. просто стоимость всех позиций в заявке)
				$query = "
                    declare
                        @LpuRegionDrugRequest_id bigint = :LpuRegionDrugRequest_id,
                        @DrugRequestProperty_id bigint = :DrugRequestProperty_id,
                        @DrugRequestPropertyFed_id bigint = :DrugRequestPropertyFed_id,
                        @DrugRequestPropertyReg_id bigint = :DrugRequestPropertyReg_id,
                        @DrugFinanceFed_id bigint = 3,
                        @DrugFinanceReg_id bigint = 27;

                    select
                        {$select}
                    from (
                        select
                            drr.DrugComplexMnn_id,
                            drr.TRADENAMES_id as Tradenames_id,
                            drr.DrugFinance_id,
                            drr.DrugRequestRow_Kolvo as Kolvo,
                            dlr.Price
                        from
                            v_DrugRequestRow drr with (nolock)
                            outer apply (
                                select top 1
                                    (case
                                        when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
                                        else i_dlr.DrugListRequest_Price
                                    end) as Price
                                from
                                    v_DrugListRequest i_dlr with (nolock)
                                    left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
                                where
                                    i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id
                                     and (
                                        (@DrugRequestProperty_id is not null and i_dlr.DrugRequestProperty_id  = @DrugRequestProperty_id) or
                                        (drr.DrugFinance_id = @DrugFinanceFed_id and i_dlr.DrugRequestProperty_id  = @DrugRequestPropertyFed_id) or
                                        (drr.DrugFinance_id = @DrugFinanceReg_id and i_dlr.DrugRequestProperty_id  = @DrugRequestPropertyReg_id)
                                    )
                                order by
                                    DrugListRequest_insDT desc
                            ) dlr
                        where
                            drr.DrugRequest_id = @LpuRegionDrugRequest_id
                    ) p
                ";
				$lr_sum_data = $this->getFirstRowFromQuery($query, array(
					'LpuRegionDrugRequest_id' => $lr_data['LpuRegionDrugRequest_id'],
					'DrugRequestProperty_id' => $reg_dr_data['DrugRequestProperty_id'],
					'DrugRequestPropertyFed_id' => $reg_dr_data['DrugRequestPropertyFed_id'],
					'DrugRequestPropertyReg_id' => $reg_dr_data['DrugRequestPropertyReg_id']
				));
				if (!is_array($lr_sum_data) || count($lr_sum_data) == 0) {
					throw new Exception("При расчете объема заявок участков произошла ошибка");
				} else {
					if ($calc_mode == 'fed_reg') {
						$sum_data['fed_sum'] += !empty ($lr_sum_data['fed_sum']) ? $lr_sum_data['fed_sum']*1 : 0;
						$sum_data['reg_sum'] += !empty ($lr_sum_data['reg_sum']) ? $lr_sum_data['reg_sum']*1 : 0;
					}
					if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
						$sum_data['req_sum'] += !empty ($lr_sum_data['req_sum']) ? $lr_sum_data['req_sum']*1 : 0;
					}
				}

                //сохраненеи показателей
                $save_data = array(
                    'DrugRequestPlan_id' => $lr_data['DrugRequestPlanLpuRegion_id']
                );
                if (empty($save_data['DrugRequestPlan_id'])) {
                    $save_data['DrugRequest_id'] = $reg_dr_data['DrugRequest_id']; //идентификатор заявочной кампани
                    $save_data['Lpu_id'] = $lr_data['Lpu_id'];
                    $save_data['LpuRegion_id'] = $lr_data['LpuRegion_id'];
                }

                if ($calc_mode == 'fed_reg') {
                    $save_data['DrugRequestPlan_CountFed'] = $sum_data['fed_sum'];
                    $save_data['DrugRequestPlan_CountReg'] = $sum_data['reg_sum'];
                    if ($mode == 'lpu' || $mode == 'region') {
                    	if (empty($lpu_sum_data[$lr_data['Lpu_id']])) {
							$lpu_sum_data[$lr_data['Lpu_id']] = array(
								'DrugRequestPlan_id' => $lr_data['DrugRequestPlanLpu_id'],
								'fed_sum' => 0,
								'reg_sum' => 0
							);
						}
                        $lpu_sum_data[$lr_data['Lpu_id']]['fed_sum'] += $sum_data['fed_sum']*1;
                        $lpu_sum_data[$lr_data['Lpu_id']]['reg_sum'] += $sum_data['reg_sum']*1;
                    }
                }
                if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
                    $save_data['DrugRequestPlan_CountReq'] = $sum_data['req_sum'];
                    if ($mode == 'lpu' || $mode == 'region') {
						if (empty($lpu_sum_data[$lr_data['Lpu_id']])) {
							$lpu_sum_data[$lr_data['Lpu_id']] = array(
								'DrugRequestPlan_id' => $lr_data['DrugRequestPlanLpu_id'],
								'req_sum' => 0
							);
						}
                        $lpu_sum_data[$lr_data['Lpu_id']]['req_sum'] += $sum_data['req_sum']*1;
                    }
                }

                $res = $this->saveObject('DrugRequestPlan', $save_data);
                if (!empty($res['Error_Msg'])) {
                    throw new Exception($res['Error_Msg']);
                }
            }

            //сохранение параметров для заявки МО
            if ($mode == 'lpu' || $mode == 'region') {
            	foreach ($lpu_sum_data as $lpu_id => $sum_data) {
					$save_data = array(
						'DrugRequestPlan_id' => $sum_data['DrugRequestPlan_id']
					);
					if (empty($save_data['DrugRequestPlan_id'])) {
						$save_data['DrugRequest_id'] = $reg_dr_data['DrugRequest_id']; //идентификатор заявочной кампани
						$save_data['Lpu_id'] = $lpu_id;
					}

					if ($calc_mode == 'fed_reg') {
						$save_data['DrugRequestPlan_CountFed'] = $sum_data['fed_sum'];
						$save_data['DrugRequestPlan_CountReg'] = $sum_data['reg_sum'];
					}
					if ($calc_mode == 'fd_and_rg' || $calc_mode == 'fed' || $calc_mode == 'reg' || $calc_mode == 'register') {
						$save_data['DrugRequestPlan_CountReq'] = $sum_data['req_sum'];
					}

					$res = $this->saveObject('DrugRequestPlan', $save_data);
					if (!empty($res['Error_Msg'])) {
						throw new Exception($res['Error_Msg']);
					}
				}
            }
			if (!$transaction_disabled) {
				$this->commitTransaction();
			}
            $result['success'] = true;
        } catch (Exception $e) {
			if (!$transaction_disabled) {
				$this->rollbackTransaction();
			}
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

	/**
	 * Рассчет сумм для заявок
	 */
	function calculateDrugRequestSum($data) {
		$fed_df_id = 3;
		$reg_df_id = 27;
		$pmuser_id = $this->getPromedUserId();

        try {
			$this->beginTransaction();

            //получение данных заявочной кампании
            $reg_dr_data = $this->getRegionDrugRequestByParams($data);
            if (empty($reg_dr_data['DrugRequest_id'])) {
                throw new Exception("При получении данных заявочной кампании произошла ошибка");
            }

            //получение данных заявки
			$query = "
				select
					dr.DrugRequest_id,
					dr.Lpu_id,
					dr.LpuRegion_id,
					dr.MedPersonal_id,
					drc.DrugRequestCategory_SysNick
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					dr.DrugRequest_id = :DrugRequest_id;
			";
			$dr_data = $this->getFirstRowFromQuery($query, array(
				'DrugRequest_id' => $data['DrugRequest_id']
			));
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("При получении данных заявки произошла ошибка");
			}

			//получение списка заявок для обработки
			$params = array(
				'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version']
			);
			$where_sql = "";
			switch($dr_data['DrugRequestCategory_SysNick']) {
				case 'vrach':
					$where_sql = "
						and dr.Lpu_id = :Lpu_id
						and isnull(dr.LpuRegion_id, 0) = isnull(:LpuRegion_id, 0)
						and dr.MedPersonal_id = :MedPersonal_id
					";
					$params['Lpu_id'] = $dr_data['Lpu_id'];
					$params['LpuRegion_id'] = $dr_data['LpuRegion_id'];
					$params['MedPersonal_id'] = $dr_data['MedPersonal_id'];
					break;
				case 'MO':
					$where_sql = "
						and dr.Lpu_id = :Lpu_id
					";
					$params['Lpu_id'] = $dr_data['Lpu_id'];
					break;
			}
			$query = "
				declare
					@DrugRequestCategory_id bigint = null;
								
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
					
				select
					dr.DrugRequest_id					
				from
					v_DrugRequest dr with (nolock)
				where
					isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and					
					dr.DrugRequestCategory_id = @DrugRequestCategory_id
					{$where_sql}
			";
			$dr_list = $this->queryResult($query, $params);

			foreach($dr_list as $request) {
				//пересчет списка медикаментов
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@Error_Code int,
						@Error_Message varchar(4000)
					set nocount on
					begin try
						update
							DrugRequestRow
						set
							DrugRequestRow_Summa = cast(isnull(dlr.Price*drr.DrugRequestRow_Kolvo, 0) as money),
							DrugRequestRow_updDT = @datetime,
							pmUser_updID = :pmUser_id
						from
							DrugRequestRow drr with (nolock)
							outer apply (
								select (case
									when :PersonRegisterType_id <> 1 then :DrugRequestProperty_id
									when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceFed_id then :DrugRequestPropertyFed_id
									when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceReg_id then :DrugRequestPropertyReg_id
									else null
								end) as DrugRequestProperty_id
							) drp
							outer apply (
								select top 1
									(case
										when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
										else i_dlr.DrugListRequest_Price
									end) as Price
								from
									v_DrugListRequest i_dlr with (nolock)
									left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
								where
									i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id and
									i_dlr.DrugRequestProperty_id = drp.DrugRequestProperty_id
								order by
									DrugListRequest_insDT desc
							) dlr
						where
							drr.DrugRequest_id = :DrugRequest_id
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'DrugRequest_id' => $request['DrugRequest_id'],
					'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
					'DrugRequestProperty_id' => $reg_dr_data['DrugRequestProperty_id'],
					'DrugRequestPropertyFed_id' => $reg_dr_data['DrugRequestPropertyFed_id'],
					'DrugRequestPropertyReg_id' => $reg_dr_data['DrugRequestPropertyReg_id'],
					'DrugFinanceFed_id' => $fed_df_id,
					'DrugFinanceReg_id' => $reg_df_id,
					'pmUser_id' => $pmuser_id
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При пересчете списка медикаментов произошла ошибка");
				}
			}

			//пересчет сумм заявок врачей
			$params = array(
				'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version'],
				'pmUser_id' => $pmuser_id
			);
			$where_sql = "";
			switch($dr_data['DrugRequestCategory_SysNick']) {
				case 'vrach':
					$where_sql = "
						and dr.Lpu_id = :Lpu_id
						and isnull(dr.LpuRegion_id, 0) = isnull(:LpuRegion_id, 0)
						and dr.MedPersonal_id = :MedPersonal_id
					";
					$params['Lpu_id'] = $dr_data['Lpu_id'];
					$params['LpuRegion_id'] = $dr_data['LpuRegion_id'];
					$params['MedPersonal_id'] = $dr_data['MedPersonal_id'];
					break;
				case 'MO':
					$where_sql = "
						and dr.Lpu_id = :Lpu_id
					";
					$params['Lpu_id'] = $dr_data['Lpu_id'];
					break;
				default:
					$where_sql = "
						and dr.Lpu_id is not null
					";
					break;
			}
			$query = "
				declare
					@datetime datetime = dbo.tzGetDate(),
					@DrugRequestCategory_id bigint = null,
					@Error_Code int,
					@Error_Message varchar(4000)
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach')
				set nocount on
				begin try
					update
						DrugRequest
					set
						DrugRequest_Summa = drr_s.DrugRequest_Summa,
						DrugRequest_updDT = @datetime,
						pmUser_updID = :pmUser_id
					from
						DrugRequest dr with (nolock)
						outer apply (
							select
								sum(cast(isnull(drr.DrugRequestRow_Summa, 0) as money)) as DrugRequest_Summa
							from
								v_DrugRequestRow drr with (nolock)
							where
								drr.DrugRequest_id = dr.DrugRequest_id
						) drr_s
					where
						isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
						isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
						isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and					
						dr.DrugRequestCategory_id = @DrugRequestCategory_id
						{$where_sql}
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$res = $this->getFirstRowFromQuery($query, $params);
			if (!is_array($res) || !empty($res['Error_Msg'])) {
				throw new Exception("При сохранении сумм заявок врачей произошла ошибка");
			}

			//пересчет сумм заявок МО
			$params = array(
				'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version'],
				'pmUser_id' => $pmuser_id
			);
			$where_sql = "";
			switch($dr_data['DrugRequestCategory_SysNick']) {
				case 'vrach':
				case 'MO':
					$where_sql = "
						and dr.Lpu_id = :Lpu_id
					";
					$params['Lpu_id'] = $dr_data['Lpu_id'];
					break;
				default:
					$where_sql = "
						and dr.Lpu_id is not null
					";
					break;
			}
			$query = "
				declare
					@datetime datetime = dbo.tzGetDate(),
					@VrachDrugRequestCategory_id bigint = null,								
					@MoDrugRequestCategory_id bigint = null,
					@Error_Code int,
					@Error_Message varchar(4000)						
				set @VrachDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach')
				set @MoDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'MO')
				set nocount on
				begin try
					update
						DrugRequest
					set
						DrugRequest_Summa = dr_s.DrugRequest_Summa,
						DrugRequest_updDT = @datetime,
						pmUser_updID = :pmUser_id 
					from
						DrugRequest dr with (nolock)
						outer apply (
							select
								sum(isnull(i_dr.DrugRequest_Summa, 0)) as DrugRequest_Summa
							from
								v_DrugRequest i_dr with (nolock)
							where
								isnull(i_dr.DrugRequestPeriod_id, 0) = isnull(dr.DrugRequestPeriod_id, 0) and
								isnull(i_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
								isnull(i_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
								isnull(i_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
								isnull(i_dr.DrugRequest_Version, 0) = isnull(dr.DrugRequest_Version, 0) and					
								i_dr.DrugRequestCategory_id = @VrachDrugRequestCategory_id and
								i_dr.Lpu_id = dr.Lpu_id
						) dr_s
					where
						isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
						isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
						isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and
						dr.DrugRequestCategory_id = @MoDrugRequestCategory_id
						{$where_sql}
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$res = $this->getFirstRowFromQuery($query, $params);
			if (!is_array($res) || !empty($res['Error_Msg'])) {
				throw new Exception("При сохранении сумм заявок МО произошла ошибка");
			}

			//вычисление и сохранение суммы заявочной кампании
			$query = "
				select
					sum(isnull(dr.DrugRequest_Summa, 0)) as DrugRequest_Summa
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
					isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and				
					drc.DrugRequestCategory_SysNick = 'MO'
			";
			$res = $this->getFirstRowFromQuery($query, array(
				'DrugRequestPeriod_id' => $reg_dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $reg_dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $reg_dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $reg_dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $reg_dr_data['DrugRequest_Version']
			));
			if (isset($res['DrugRequest_Summa'])) {
				$save_result = $this->saveObject('DrugRequest', array(
					'DrugRequest_id' => $reg_dr_data['DrugRequest_id'],
					'DrugRequest_Summa' => $res['DrugRequest_Summa']
				));
				if (empty($save_result['DrugRequest_id'])) {
					throw new Exception("При сохранении суммы заявочной кампании произошла ошибка");
				}
			} else {
				throw new Exception("При вычичлении суммы заявочной кампании произошла ошибка");
			}

            $this->commitTransaction();
            $result['success'] = true;
        } catch (Exception $e) {
			$this->rollbackTransaction();
            $result['success'] = false;
            $result['Error_Msg'] = $e->getMessage();
        }

        return $result;
    }

	/**
	 * Загрузка списка
	 */
	function loadConsolidatedDrugRequestList($filter) {
		$where = array();
		$p = array();
		
		$where[] = 'dr.Lpu_id is null';
		$where[] = 'dr.MedPersonal_id is null';
		$where[] = "drc.DrugRequestCategory_SysNick = 'svod'";
		
		if (isset($filter['Year']) && $filter['Year']) {			
			$where[] = 'datepart(year, drp.DrugRequestPeriod_begDate) = :Year';
			$p['Year'] = $filter['Year'];
		}
		
		//пересечение периода поиска с периодом любой из заявок входящих в сводную
		if (!empty($filter['ConsolidatedDrugRequest_begDate']) && !empty($filter['ConsolidatedDrugRequest_endDate'])) {
			$where[] = "drp.DrugRequestPeriod_begDate <= :ConsolidatedDrugRequest_endDate";
            $where[] = "drp.DrugRequestPeriod_endDate >= :ConsolidatedDrugRequest_begDate";
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
			    where
			        -- where
			        {$where_clause}
			        -- end where
			";
		}
		
		$query = "
			select top 1000
			    -- select
				dr.DrugRequest_id,
				dr.DrugRequestStatus_id,
				drs.DrugRequestStatus_Code,
				drs.DrugRequestStatus_Name,
				dr.DrugRequest_Name,
				convert(varchar(10), cast(dr.DrugRequest_updDT as datetime), 104) + ' ' + convert(varchar(5), cast(dr.DrugRequest_updDT as datetime), 108) as DrugRequest_updDT,
				isnull(RequestSum.Fed, 0) as Sum_Fed,
				isnull(RequestSum.Reg, 0) as Sum_Reg,
				isnull(RequestSum.pTotal, 0) as Sum_pTotal,
				isnull(sumS.sumR, 0) as Sum_Total,
				datepart(year, drp.DrugRequestPeriod_begDate) as FinYear
				-- end select
			from
			    -- from
				dbo.v_DrugRequest dr with (nolock)
				left join dbo.v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join dbo.v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				left join dbo.v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				outer apply (
					select
						sum(case when df.DrugFinance_SysNick = 'fed' then drps.DrugRequestPurchaseSpec_Sum else 0 end) as Fed,
						sum(case when df.DrugFinance_SysNick = 'reg' then drps.DrugRequestPurchaseSpec_Sum else 0 end) as Reg,
						sum(drps.DrugRequestPurchaseSpec_Sum) as Total,
						sum(drps.DrugRequestPurchaseSpec_Sum) as pTotal
					from
						DrugRequestPurchaseSpec drps with(nolock)
						left join v_DrugFinance df with(nolock) on df.DrugFinance_id = drps.DrugFinance_id
					where
						drps.DrugRequest_id = dr.DrugRequest_id
				) RequestSum
				outer apply (
					select 
						sum(sumA.Summa) as sumR
					from v_DrugRequestPurchase drpoa with (nolock)
					left join v_DrugRequest droa with (nolock) on droa.DrugRequest_id = drpoa.DrugRequest_lid
					outer apply (
						select
							sum(i_drr.DrugRequestRow_Summa) as Summa
						from
							v_DrugRequest i_dr with (nolock)
							left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
						where
							i_dr.DrugRequest_Version is null and
							i_dr.DrugRequestPeriod_id = droa.DrugRequestPeriod_id and
							isnull(i_dr.PersonRegisterType_id, 0) = isnull(droa.PersonRegisterType_id, 0) and
							isnull(i_dr.DrugRequestKind_id, 0) = isnull(droa.DrugRequestKind_id, 0) and
							isnull(i_dr.DrugGroup_id, 0) = isnull(droa.DrugGroup_id, 0) and
							i_drr.Person_id is null
					) sumA
					where drpoa.DrugRequest_id = dr.DrugRequest_id
				) sumS
				-- end from
			$where_clause
			order by
			    -- order by
			    dr.DrugRequest_insDT desc
			    -- end order by
		";

        if (!empty($filter['limit'])) {
            $result = $this->db->query(getLimitSQLPH($query, $filter['start'], $filter['limit']), $filter);
            $count = $this->getFirstResultFromQuery(getCountSQLPH($query), $filter);
            if (is_object($result) && $count !== false) {
                return array(
                    'data' => $result->result('array'),
                    'totalCount' => $count
                );
            } else {
                return false;
            }
        } else {
            $result = $this->db->query($query, $filter);
            if ( is_object($result) ) {
                return $result->result('array');
            } else {
                return false;
            }
        }
	}

	/**
	 * Загрузка списка строк заявки, соотвествующих конкретной позиции сводной заявки
	 */
	function loadConsolidatedDrugRequestRowList($filter) {
		$q = "
			select
				drr.DrugRequestRow_id,
				l.Lpu_id,
				l.Lpu_Name,
				dr_mp.MedPersonal_id,
				mp.Person_Fio as MedPersonal_Fio,
				drr.Person_id,
				(case
					when
						drr.Person_id is not null
					then
						isnull(ps.Person_SurName+' ','')+isnull(ps.Person_FirName+' ','')+isnull(ps.Person_SecName,'')
					else
						'Резерв'
				end) as Person_Fio,
				drr.DrugRequestRow_Kolvo,
				drr.DrugRequestRow_Summa,
				drr.DrugRequestRow_KolDrugBuy,
				drr.DrugRequestRow_SumBuy,
				','+(
					select distinct
						cast(kas.KLAreaStat_id as varchar)+','
					from
						v_OrgServiceTerr ost (nolock)
						inner join v_KLAreaStat kas (nolock) on
							ISNULL(OST.KLRgn_id, 0) = ISNULL(KAS.KLRgn_id, 0)
							and ISNULL(OST.KLCity_id, 0) = ISNULL(KAS.KLCity_id, 0)
							and ISNULL(OST.KLTown_id, 0) = ISNULL(KAS.KLTown_id, 0)
					where
						ost.Org_id = l.Org_id
					for xml path('')
				) as KLAreaStat_List
			from
				v_DrugRequestPurchaseSpec drps with(nolock)
				left join v_DrugRequestPurchase drp with(nolock) on drp.DrugRequest_id = drps.DrugRequest_id
				left join v_DrugRequest dr_region with(nolock) on dr_region.DrugRequest_id = drp.DrugRequest_lid
				left join v_DrugRequest dr_mp with(nolock) on
				    dr_mp.DrugRequestPeriod_id = dr_region.DrugRequestPeriod_id and
				    isnull(dr_mp.PersonRegisterType_id, 0) = isnull(dr_region.PersonRegisterType_id, 0) and
				    isnull(dr_mp.DrugRequestKind_id, 0) = isnull(dr_region.DrugRequestKind_id, 0) and
				    isnull(dr_mp.DrugGroup_id, 0) = isnull(dr_region.DrugGroup_id, 0) and
				    dr_mp.DrugRequest_Version is null and
				    dr_mp.MedPersonal_id is not null
				inner join v_DrugRequestRow drr with(nolock) on
					drr.DrugRequest_id = dr_mp.DrugRequest_id and
					drr.DrugComplexMnn_id = drps.DrugComplexMnn_id and
					isnull(drr.DrugFinance_id, 0) = isnull(drps.DrugFinance_id, 0) and
					isnull(drr.TRADENAMES_id, 0) = isnull(drps.TRADENAMES_id, 0)
				left join v_Lpu l with(nolock) on l.Lpu_id = dr_mp.Lpu_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = dr_mp.MedPersonal_id
				left join v_PersonState ps with(nolock) on ps.Person_id = drr.Person_id
			where
				drps.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
			order by
				isnull(l.Lpu_Name, 'ЯЯЯ'), isnull(mp.Person_Fio, 'ЯЯЯ'), isnull(ps.Person_FirName, 'ЯЯЯ'), Person_Fio;
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных заявок, доступных для формирования сводной заявки
	 */
	function loadConsolidatedDrugRequestSourceList() {
		$where = array();
		$p = array();
		
		$where[] = 'v_DrugRequest.Lpu_id is null';
		$where[] = 'v_DrugRequest.MedPersonal_id is null';
		$where[] = "DrugRequestCategory_id_ref.DrugRequestCategory_SysNick = 'region'";
		$where[] = "v_DrugRequest.DrugRequest_id not in (select DrugRequest_lid from DrugRequestPurchase with(nolock))";
		$where[] = "DrugRequestStatus_id_ref.DrugRequestStatus_Code = 3"; //3 - Утвержденная

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		
		$q = "
			SELECT TOP 1000				
				v_DrugRequest.DrugRequest_id,
				v_DrugRequest.DrugRequestPeriod_id,
				v_DrugRequest.DrugRequestStatus_id,
				v_DrugRequest.DrugGroup_id,
				DrugRequestStatus_id_ref.DrugRequestStatus_Code,
				DrugRequestStatus_id_ref.DrugRequestStatus_Name,
				v_DrugRequest.DrugRequest_Name,
				v_DrugRequest.Lpu_id,
				v_DrugRequest.LpuSection_id,
				v_DrugRequest.MedPersonal_id,
				DrugRequestSumma.Summa as DrugRequest_Summa,
				DrugFinanceList.DrugFinance_List,
				v_DrugRequest.DrugRequest_YoungChildCount,
				v_DrugRequest.PersonRegisterType_id,
				v_DrugRequest.DrugRequest_IsSigned,
				v_DrugRequest.pmUser_signID,
				v_DrugRequest.DrugRequest_signDT,
				v_DrugRequest.DrugRequest_Version,
				DrugRequestPeriod_id_ref.DrugRequestPeriod_Name as DrugRequestPeriod_Name,
				PersonRegisterType_id_ref.PersonRegisterType_Name as PersonRegisterType_Name,
				DrugGroup_id_ref.DrugGroup_Name as DrugGroup_Name,
				datepart(year, DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) as FinYear
			FROM
				dbo.v_DrugRequest WITH (NOLOCK)
				LEFT JOIN dbo.v_DrugRequestCategory DrugRequestCategory_id_ref WITH (NOLOCK) ON DrugRequestCategory_id_ref.DrugRequestCategory_id = v_DrugRequest.DrugRequestCategory_id
				LEFT JOIN dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref WITH (NOLOCK) ON DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id
				LEFT JOIN dbo.v_DrugRequestStatus DrugRequestStatus_id_ref WITH (NOLOCK) ON DrugRequestStatus_id_ref.DrugRequestStatus_id = v_DrugRequest.DrugRequestStatus_id
				LEFT JOIN dbo.v_PersonRegisterType PersonRegisterType_id_ref WITH (NOLOCK) ON PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugRequest.PersonRegisterType_id
				LEFT JOIN dbo.v_DrugGroup DrugGroup_id_ref WITH (NOLOCK) ON DrugGroup_id_ref.DrugGroup_id = v_DrugRequest.DrugGroup_id
				outer apply (
					select
						sum(i_drr.DrugRequestRow_Summa) as Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
						i_dr.DrugRequest_Version is null and
						i_dr.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
						isnull(i_dr.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
						isnull(i_dr.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
						isnull(i_dr.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
						i_drr.Person_id is null
				)  DrugRequestSumma
				outer apply (
                    select
                        replace(replace((
                            select distinct
                                cast(i_drr.DrugFinance_id as varchar)+',' as 'data()'
                            from
                                v_DrugRequest i_dr with (nolock)
                                left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
                            where
                                i_dr.DrugRequest_Version is null and
                                i_dr.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id and
                                isnull(i_dr.PersonRegisterType_id, 0) = isnull(v_DrugRequest.PersonRegisterType_id, 0) and
                                isnull(i_dr.DrugRequestKind_id, 0) = isnull(v_DrugRequest.DrugRequestKind_id, 0) and
                                isnull(i_dr.DrugGroup_id, 0) = isnull(v_DrugRequest.DrugGroup_id, 0) and
                                i_drr.Person_id is null
                            for xml path('')
                        )+',,', ',,,', ''), ',,', '') as DrugFinance_List
                ) DrugFinanceList
			$where_clause
		";
		$result = $this->db->query($q, array());
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных заявок включенных в список сводной заявки
	 */
	function loadConsolidatedRegionDrugRequestList($data) {
		$where = array();
		$params = array();

        if (!empty($data['DrugRequest_id'])) {
            $where[] = "drp.DrugRequest_id = :DrugRequest_id";
            $params['DrugRequest_id'] = $data['DrugRequest_id'];
        } else {
            return false;
        }

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query = "
			select
				dr.DrugRequest_id,
				isnull(o.Org_Nick,o.Org_Name) as Org_Name,
				drpr.DrugRequestPeriod_Name,
				dr.DrugRequest_Name,
				prt.PersonRegisterType_Name,
				dg.DrugGroup_Name,
				sum.Summa as DrugRequest_Sum
			from
			    v_DrugRequestPurchase drp with (nolock)
				left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drp.DrugRequest_lid
				left join v_DrugRequestPeriod drpr with (nolock) on drpr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
				left join v_DrugGroup dg with (nolock) on dg.DrugGroup_id = dr.DrugGroup_id
                left join v_DrugRequestProperty drprp with (nolock) on drprp.DrugRequestProperty_id = coalesce(dr.DrugRequestProperty_id,dr.DrugRequestPropertyFed_id,dr.DrugRequestPropertyReg_id)
                left join v_Org o with (nolock) on o.Org_id = drprp.Org_id
				outer apply (
					select
						sum(i_drr.DrugRequestRow_Summa) as Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
						i_dr.DrugRequest_Version is null and
						i_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						isnull(i_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						i_drr.Person_id is null
				) sum
			$where_clause
		";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка сгруппированой спецификации сводной заявки
	 */
	function loadDrugRequestPurchaseSpecSumList($filter) {
		$with = array();
		$select = array();
		$join = array();
        $where = array();

        if (!isset($filter['DrugRequest_id'])) {
            $filter['DrugRequest_id'] = null;
        }

        //фильтры
        if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
            $where[] = 'drpc.DrugFinance_id = :DrugFinance_id';
        }

        if (isset($filter['RlsClsatc_id']) && $filter['RlsClsatc_id'] > 0) {
            $where[] = 'atc.Atc_id = :RlsClsatc_id';
        }

        if (isset($filter['RlsClsPhGrLimp_id']) && $filter['RlsClsPhGrLimp_id'] > 0) {
            $where[] = '(PhGr.ID = :RlsClsPhGrLimp_id or PhGr.PARENTID = :RlsClsPhGrLimp_id)';
        }

        if (!empty($filter['Drug_Name'])) {
            $where[] = '(dcm.DrugComplexMnn_RusName like :Drug_Name or tn.NAME like :Drug_Name)';
            $filter['Drug_Name'] = "%{$filter['Drug_Name']}%";
        }

		$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';
		$select_clause = count($select) > 0 ? ', '.implode(', ', $select) : '';
        $join_clause = implode(' ', $join);
        $where_clause = count($where) > 0 ? 'where '.implode(' and ', $where) : '';

        $query = "
            select top 1000
                drpc.DrugRequestPurchaseSpec_id,
                drpc.DrugComplexMnn_id,
                drpc.TRADENAMES_id,
                drpc.Evn_id,
                g_drpc.DrugRequestPurchaseSpec_lKolvo,
                case 
                	when g_drpc.DrugRequestPurchaseSpec_Price > 0 then g_drpc.DrugRequestPurchaseSpec_Price
                	when drr.DrugRequestRow_Kolvo > 0 then CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo))
                	else 0
                end as DrugRequestPurchaseSpec_Price,
                case 
                	when g_drpc.DrugRequestPurchaseSpec_Sum > 0 then g_drpc.DrugRequestPurchaseSpec_Sum
                	when drr.DrugRequestRow_Kolvo > 0 then (CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo)) * g_drpc.DrugRequestPurchaseSpec_lKolvo)
                	else 0
                end as DrugRequestPurchaseSpec_Sum,
                g_drpc.DrugRequestPurchaseSpec_pKolvo,
                case 
                	when g_drpc.DrugRequestPurchaseSpec_pSum > 0 then g_drpc.DrugRequestPurchaseSpec_pSum
                	when drr.DrugRequestRow_Kolvo > 0 then (CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo)) * g_drpc.DrugRequestPurchaseSpec_lKolvo)
                	else 0
                end as DrugRequestPurchaseSpec_pSum,
                g_drpc.DrugRequestPurchaseSpec_RefuseCount,
                g_drpc.DrugRequestPurchaseSpec_RestCount,
                atc.Atc_id,
                atc.Atc_Name,
                tn.NAME as TRADENAMES_Name,
                dcmn.DrugComplexMnnName_Name,
                cdf.NAME as ClsDrugForms_Name,
                dcmd.DrugComplexMnnDose_Name,
                dcmf.DrugComplexMnnFas_Name,
                g_drpc.DrugRequestExec_Count,
                nomen.DrugComplexMnnCode_Code,
                (case when PhGr.ACTMATTERID is not null then 1 else 0 end) as InJnvlp,
                PhGr.Name as PhGr_Name,
                drpc.Evn_id,
                (isnull(' '+convert(varchar(10), evk.EvnVK_ConclusionDate, 104), '') + isnull(' '+evk.EvnVK_NumProtocol, '')) as Evn_Name
            from
                (
                    select
                        min(i_drpc.DrugRequestPurchaseSpec_id) as DrugRequestPurchaseSpec_id,
                        max(i_drpc.DrugRequestPurchaseSpec_Price) as DrugRequestPurchaseSpec_Price,
                        sum(i_drpc.DrugRequestPurchaseSpec_lKolvo) as DrugRequestPurchaseSpec_lKolvo,
                        sum(i_drpc.DrugRequestPurchaseSpec_Sum) as DrugRequestPurchaseSpec_Sum,
                        sum(i_drpc.DrugRequestPurchaseSpec_pKolvo) as DrugRequestPurchaseSpec_pKolvo,
                        sum(i_drpc.DrugRequestPurchaseSpec_pSum) as DrugRequestPurchaseSpec_pSum,
                        sum(i_drpc.DrugRequestPurchaseSpec_RefuseCount) as DrugRequestPurchaseSpec_RefuseCount,
                        sum(i_drpc.DrugRequestPurchaseSpec_RestCount) as DrugRequestPurchaseSpec_RestCount,
                        sum(i_exec_data.DrugRequestExec_Count) as DrugRequestExec_Count
                    from
                        dbo.v_DrugRequestPurchaseSpec i_drpc with (nolock)
                        outer apply (
                            select
                                sum(i_dre.DrugRequestExec_Count) as DrugRequestExec_Count
                            from
                                v_DrugRequestExec i_dre with (nolock)
                            where
                                i_dre.DrugRequestPurchaseSpec_id = i_drpc.DrugRequestPurchaseSpec_id
                        ) i_exec_data
                    where
                        i_drpc.DrugRequest_id = :DrugRequest_id
                    group by
                        i_drpc.DrugComplexMnn_id, i_drpc.TRADENAMES_id, i_drpc.Evn_id
                ) g_drpc
                left join dbo.v_DrugRequestPurchaseSpec drpc with (nolock) on drpc.DrugRequestPurchaseSpec_id = g_drpc.DrugRequestPurchaseSpec_id
                left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drpc.DrugComplexMnn_id
                left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                left join rls.CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = drpc.TRADENAMES_id
                left join EvnVK evk with (nolock) on evk.Evn_id = drpc.Evn_id
                outer apply (
                    select top 1
                        catc.CLSATC_ID as Atc_id,
                        catc.NAME as Atc_Name
                    from
                        rls.DrugComplexMnn dcm2 with(nolock)
                        left join rls.DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
                        left join rls.PREP_ACTMATTERS pact with(nolock) on pact.MATTERID = dcmn2.ActMatters_id
                        right join rls.PREP p with(nolock) on p.Prep_id = pact.PREPID and p.DRUGFORMID = dcm2.CLSDRUGFORMS_ID
                        left join rls.PREP_ATC patc with(nolock) on patc.PREPID = p.Prep_id
                        left join rls.CLSATC catc with(nolock) on catc.CLSATC_ID = patc.UNIQID
                    where
                        DrugComplexMnn_id = drpc.DrugComplexMnn_id
                ) atc
                outer apply (
                    select top 1
                        cpl1.CLS_PHGR_LIMP_ID as ID,
                        cpl1.PARENTID,
                        isnull(cpl2.NAME+' ','')+cpl1.NAME as Name,
                        adl.ACTMATTERID,
                        adl.DRUGFORMID
                    from
                        rls.AM_DF_LIMP adl with(nolock)
                        left join rls.CLS_PHGR_LIMP cpl1 with(nolock) on cpl1.CLS_PHGR_LIMP_ID = adl.LIMP_PHGR_ID
                        left join rls.CLS_PHGR_LIMP cpl2 with(nolock) on cpl1.PARENTID <> 0 and cpl2.CLS_PHGR_LIMP_ID = cpl1.PARENTID
                    where
                        adl.ACTMATTERID = dcmn.ActMatters_id and
                        adl.DRUGFORMID = dcm.CLSDRUGFORMS_ID
                ) PhGr
                outer apply (
                    select top 1
                        i_dcmc.DrugComplexMnnCode_Code
                    from
                        rls.v_DrugComplexMnnCode i_dcmc with (nolock)
                    where
                        i_dcmc.DrugComplexMnn_id = drpc.DrugComplexMnn_id
                    order by
                        i_dcmc.DrugComplexMnnCode_id
                ) nomen
				outer apply (
					select top 1 dr.DrugRequestPeriod_id from dbo.v_DrugRequest dr with (nolock)
					where dr.DrugRequest_id = :DrugRequest_id
				) dr_orig
				outer apply (
					select
						sum(i_drr.DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
						sum(i_drr.DrugRequestRow_Summa) as DrugRequestRow_Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
					    i_dr.DrugRequest_Version is null and
						i_dr.DrugRequestPeriod_id = dr_orig.DrugRequestPeriod_id and
						i_drr.Person_id is null and
						(
							i_drr.DrugComplexMnn_id is not null or
							i_drr.TRADENAMES_ID is not null
						) and
						i_drr.DrugComplexMnn_id = drpc.DrugComplexMnn_id and
						isnull(i_drr.TRADENAMES_id, 0) = isnull(drpc.TRADENAMES_id, 0) and
						(:Lpu_id is null or i_dr.Lpu_id = :Lpu_id)
					group by
						i_drr.DrugComplexMnn_id, i_drr.TRADENAMES_ID
				) drr
                {$join_clause}
            {$where_clause}
        ";

		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка спецификации сводной заявки
	 */
	function loadDrugRequestPurchaseSpecList($filter) {
		$with = array();
		$select = array();
		$join = array();
        $where = array();
		$p = array();

        $region = $_SESSION['region']['nick'];

        //столбец "Заказано (пред.год)"
        $with[] = "
            request_data as (
                select
                    drr.DrugComplexMnn_id,
                    drr.TRADENAMES_id,
                    sum(drr.DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
                    sum(drr.DrugRequestRow_KolDrugBuy) as DrugRequestRow_KolDrugBuy
                from
                    v_DrugRequest con_dr with (nolock)
                    left join v_DrugRequestPurchase drp with (nolock) on drp.DrugRequest_id = con_dr.DrugRequest_id
                    left join v_DrugRequest reg_dr with (nolock) on reg_dr.DrugRequest_id = drp.DrugRequest_lid
                    outer apply (
                        select
                            i_mp_dr.DrugRequest_id
                        from
                            v_DrugRequest i_mp_dr with (nolock)
                            left join /*v_*/DrugRequestPeriod i_drp on i_drp.DrugRequestPeriod_id = i_mp_dr.DrugRequestPeriod_id
                        where
                            i_mp_dr.DrugRequestCategory_id = @MpDrugRequestCategory_id and
                            i_mp_dr.DrugRequest_Version is null and
                            isnull(i_mp_dr.PersonRegisterType_id, 0) = isnull(reg_dr.PersonRegisterType_id, 0) and
                            isnull(i_mp_dr.DrugGroup_id, 0) = isnull(reg_dr.DrugGroup_id, 0) and
                            datepart(year, i_drp.DrugRequestPeriod_begDate) = @LastYear
                    ) mp_dr
                    left join v_DrugRequestRow drr with (nolock) on drr.DrugRequest_id = mp_dr.DrugRequest_id
                where
                    con_dr.DrugRequest_id = @ConsolidatedDrugRequest_id and
                    drr.DrugComplexMnn_id is not null and
                    drr.DrugRequestRow_Kolvo > 0
                group by
                    drr.DrugComplexMnn_id, drr.TRADENAMES_id
            )
        ";
        $join[] = "left join request_data on request_data.DrugComplexMnn_id = drpc.DrugComplexMnn_id and request_data.TRADENAMES_id = drpc.TRADENAMES_id";

        if ($region == 'saratov') {
            $select[] = "request_data.DrugRequestRow_Kolvo";
        }

        //столбец "Отпущено (пред.год)"
        if ($region == 'saratov') {
            $with[] = "
                dus_data as (
                    select
                        d.DrugComplexMnn_id,
                        sum(dus.DocumentUcStr_Count) as DocumentUcStr_Count
                    from
                        v_DocumentUc du with (nolock)
                        left join DocumentUcStr dus with (nolock) on dus.DocumentUc_id = du.DocumentUc_id
                        left join DocumentUcStr p_dus with (nolock) on p_dus.DocumentUcStr_id = dus.DocumentUcStr_oid
                        left join DrugShipmentLink dsl with (nolock) on dsl.DocumentUcStr_id = p_dus.DocumentUcStr_id
                        left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                        left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = d.DrugTorg_id
                    where
                        DrugDocumentType_id = @DocRealDrugDocumentType_id and
                        dsl.DrugShipment_id in (
                            select
                                ds.DrugShipment_id
                            from
                                v_DrugRequest con_dr with (nolock)
                                left join v_DrugRequestPurchase drp with (nolock) on drp.DrugRequest_id = con_dr.DrugRequest_id
                                left join v_DrugRequest reg_dr with (nolock) on reg_dr.DrugRequest_id = drp.DrugRequest_lid
                                outer apply (
                                    select
                                        i_reg_dr.DrugRequest_id,
                                        i_reg_dr.DrugRequest_Name
                                    from
                                        v_DrugRequest i_reg_dr with (nolock)
                                        left join /*v_*/DrugRequestPeriod i_drp on i_drp.DrugRequestPeriod_id = i_reg_dr.DrugRequestPeriod_id
                                    where
                                        i_reg_dr.DrugRequestCategory_id = @RegionDrugRequestCategory_id and
                                        i_reg_dr.DrugRequest_Version is null and
                                        isnull(i_reg_dr.PersonRegisterType_id, 0) = isnull(reg_dr.PersonRegisterType_id, 0) and
                                        isnull(i_reg_dr.DrugGroup_id, 0) = isnull(reg_dr.DrugGroup_id, 0) and
                                        datepart(year, i_drp.DrugRequestPeriod_begDate) = @LastYear
                                ) ly_reg_dr
                                left join v_DrugRequestPurchase ly_drp with (nolock) on ly_drp.DrugRequest_lid = ly_reg_dr.DrugRequest_id
                                left join DrugRequestPurchaseSpec ly_drps with (nolock) on ly_drps.DrugRequest_id = ly_drp.DrugRequest_id
                                left join DrugRequestExec dre with (nolock) on dre.DrugRequestPurchaseSpec_id = ly_drps.DrugRequestPurchaseSpec_id
                                left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = dre.WhsDocumentSupplySpec_id
                                left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
                            where
                                con_dr.DrugRequest_id = @ConsolidatedDrugRequest_id and
                                ly_drp.DrugRequest_id is not null
                        )
                    group by
                        d.DrugComplexMnn_id
                )
            ";
            $select[] = "dus_data.DocumentUcStr_Count";
            $join[] = "left join dus_data on dus_data.DrugComplexMnn_id = drpc.DrugComplexMnn_id";
        }

        if (!isset($filter['DrugRequest_id'])) {
            $filter['DrugRequest_id'] = null;
        }

        if (!isset($filter['DrugRequestPurchaseSpec_id'])) {
            $filter['DrugRequestPurchaseSpec_id'] = null;
        }

        //поле Отказ
        if ($region == 'saratov') {
            $select[] = "drpc.DrugRequestPurchaseSpec_RefuseCount";
        } else {
            $select[] = "isnull(drpc.DrugRequestPurchaseSpec_RefuseCount, case
                when isnull(request_data.DrugRequestRow_Kolvo, 0) > isnull(request_data.DrugRequestRow_KolDrugBuy, 0)
                then isnull(request_data.DrugRequestRow_Kolvo, 0) - isnull(request_data.DrugRequestRow_KolDrugBuy, 0)
                else null
            end) as DrugRequestPurchaseSpec_RefuseCount";
        }


        //фильтры
        if (!empty($filter['DrugRequestPurchaseSpec_id'])) {
            $where[] = 'drpc.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id';
        } else {
            if (!empty($filter['DrugRequest_id'])) {
                $where[] = 'drpc.DrugRequest_id = :DrugRequest_id';
            }

            if (!empty($filter['DrugComplexMnn_id'])) {
                $where[] = 'drpc.DrugComplexMnn_id = :DrugComplexMnn_id';
            }

            if (isset($filter['TRADENAMES_id'])) {
                $where[] = 'isnull(drpc.TRADENAMES_id, 0) = isnull(:TRADENAMES_id, 0)';
            }

            if (isset($filter['Evn_id'])) {
                $where[] = 'isnull(drpc.Evn_id, 0) = isnull(:Evn_id, 0)';
            }

            if (!isset($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
                $where[] = 'drpc.DrugFinance_id = :DrugFinance_id';
            }
        }

		$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';
		$select_clause = count($select) > 0 ? ', '.implode(', ', $select) : '';
        $join_clause = implode(' ', $join);
        $where_clause = count($where) > 0 ? 'where '.implode(' and ', $where) : '';

		$query = "
            declare
                @ConsolidatedDrugRequest_id bigint = :DrugRequest_id,
                @LastYear int,
                @MpDrugRequestCategory_id bigint,
                @RegionDrugRequestCategory_id bigint,
                @DocRealDrugDocumentType_id bigint;

            if (:DrugRequest_id is null and :DrugRequestPurchaseSpec_id is not null)
            begin
                set @ConsolidatedDrugRequest_id = (select top 1 DrugRequest_id from v_DrugRequestPurchaseSpec where DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id);
            end;

            set @MpDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from v_DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'vrach'); --Заявка врача
            set @RegionDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from v_DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'region'); --Заявочная кампания
            set @DocRealDrugDocumentType_id = (select top 1 DrugDocumentType_id from v_DrugDocumentType where DrugDocumentType_SysNick = 'DocReal'); --Реализация
            set @LastYear = (
                select top 1
                    (datepart(year, drp.DrugRequestPeriod_begDate)-1) as LastYear
                from
                    v_DrugRequest dr with (nolock)
                    left join DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                where
                    dr.DrugRequest_id = @ConsolidatedDrugRequest_id
            );

            {$with_clause}
            select top 1000
                opn.YesNo_Code isOpen_Code,
                (case when opn.YesNo_Code = 1 then 'Открыта' else 'Закрыта' end) isOpen_Name,
                drpc.DrugRequestPurchaseSpec_id,
                dcm.DrugComplexMnn_RusName,
                drpc.DrugRequestPurchaseSpec_lKolvo,
                drpc.DrugRequestPurchaseSpec_lSum,
                case 
                	when drpc.DrugRequestPurchaseSpec_Price > 0 then drpc.DrugRequestPurchaseSpec_Price
                	when drr.DrugRequestRow_Kolvo > 0 then CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo))
                	else 0
                end as DrugRequestPurchaseSpec_Price,
                drpc.DrugRequestPurchaseSpec_Kolvo,
                case 
                	when drpc.DrugRequestPurchaseSpec_Sum > 0 then drpc.DrugRequestPurchaseSpec_Sum
                	when drr.DrugRequestRow_Kolvo > 0 then (CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo)) * drpc.DrugRequestPurchaseSpec_lKolvo)
                	else 0
                end as DrugRequestPurchaseSpec_Sum,
                drpc.DrugRequestPurchaseSpec_pKolvo,
                case 
                	when drpc.DrugRequestPurchaseSpec_pSum > 0 then drpc.DrugRequestPurchaseSpec_pSum
                	when drr.DrugRequestRow_Kolvo > 0 then (CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo)) * drpc.DrugRequestPurchaseSpec_lKolvo)
                	else 0
                end as DrugRequestPurchaseSpec_pSum,
                drpc.DrugRequestPurchaseSpec_RestCount,
                df.DrugFinance_id,
                df.DrugFinance_Name,
                atc.Atc_id,
                atc.Atc_Name,
                tn.NAME as TRADENAMES_Name,
                dcmn.DrugComplexMnnName_Name,
                cdf.NAME as ClsDrugForms_Name,
                dcmd.DrugComplexMnnDose_Name,
                dcmf.DrugComplexMnnFas_Name,
                exec_data.DrugRequestExec_Count,
                purchase_data.WhsDocumentProcurementRequestSpec_Kolvo,
                purchase_data.WhsDocumentProcurementRequestSpec_Sum,
                nomen.DrugComplexMnnCode_Code,
                (case when PhGr.ACTMATTERID is not null then 1 else 0 end) as InJnvlp,
                PhGr.Name as PhGr_Name,
                o.Org_Name,
                o.Org_Nick,
                dg.DrugGroup_Name,
                prt.PersonRegisterType_Name
                {$select_clause}
            from
                dbo.v_DrugRequestPurchaseSpec drpc with (nolock)
                left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drpc.DrugComplexMnn_id
                left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                left join rls.CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = drpc.TRADENAMES_id
                left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drpc.DrugFinance_id
                left join YesNo opn with (nolock) on opn.YesNo_id = drpc.DrugRequestPurchaseSpec_isOpen
                left join v_Org o with (nolock) on o.Org_id = drpc.Org_id
                left join v_DrugGroup dg with (nolock) on dg.DrugGroup_id = drpc.DrugGroup_id
                left join v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = drpc.PersonRegisterType_id
                outer apply (
                    select top 1
                        catc.CLSATC_ID as Atc_id,
                        catc.NAME as Atc_Name
                    from
                        rls.DrugComplexMnn dcm2 with(nolock)
                        left join rls.DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
                        left join rls.PREP_ACTMATTERS pact with(nolock) on pact.MATTERID = dcmn2.ActMatters_id
                        right join rls.PREP p with(nolock) on p.Prep_id = pact.PREPID and p.DRUGFORMID = dcm2.CLSDRUGFORMS_ID
                        left join rls.PREP_ATC patc with(nolock) on patc.PREPID = p.Prep_id
                        left join rls.CLSATC catc with(nolock) on catc.CLSATC_ID = patc.UNIQID
                    where
                        DrugComplexMnn_id = drpc.DrugComplexMnn_id
                ) atc
                outer apply (
                    select top 1
                        cpl1.CLS_PHGR_LIMP_ID as ID,
                        cpl1.PARENTID,
                        isnull(cpl2.NAME+' ','')+cpl1.NAME as Name,
                        adl.ACTMATTERID,
                        adl.DRUGFORMID
                    from
                        rls.AM_DF_LIMP adl with(nolock)
                        left join rls.CLS_PHGR_LIMP cpl1 with(nolock) on cpl1.CLS_PHGR_LIMP_ID = adl.LIMP_PHGR_ID
                        left join rls.CLS_PHGR_LIMP cpl2 with(nolock) on cpl1.PARENTID <> 0 and cpl2.CLS_PHGR_LIMP_ID = cpl1.PARENTID
                    where
                        adl.ACTMATTERID = dcmn.ActMatters_id and
                        adl.DRUGFORMID = dcm.CLSDRUGFORMS_ID
                ) PhGr
                outer apply (
                    select
                        sum(dre.DrugRequestExec_Count) as DrugRequestExec_Count
                    from
                        v_DrugRequestExec dre with (nolock)
                    where
                        dre.DrugRequestPurchaseSpec_id = drpc.DrugRequestPurchaseSpec_id
                ) exec_data
                outer apply (
                    select
                        sum(wdprs.WhsDocumentProcurementRequestSpec_Kolvo) as WhsDocumentProcurementRequestSpec_Kolvo,
                        sum(wdprs.WhsDocumentProcurementRequestSpec_Kolvo * wdprs.WhsDocumentProcurementRequestSpec_PriceMax) as WhsDocumentProcurementRequestSpec_Sum
                    from
                       v_WhsDocumentProcurementRequestSpec wdprs with (nolock)
                    where
                        wdprs.DrugRequestPurchaseSpec_id = drpc.DrugRequestPurchaseSpec_id
                ) purchase_data
                outer apply (
                    select top 1
                        i_dcmc.DrugComplexMnnCode_Code
                    from
                        rls.v_DrugComplexMnnCode i_dcmc with (nolock)
                    where
                        i_dcmc.DrugComplexMnn_id = drpc.DrugComplexMnn_id
                    order by
                        i_dcmc.DrugComplexMnnCode_id
                ) nomen
				outer apply (
					select top 1 dr.DrugRequestPeriod_id from dbo.v_DrugRequest dr with (nolock)
					where dr.DrugRequest_id = @ConsolidatedDrugRequest_id
				) dr_orig
				outer apply (
					select
						sum(i_drr.DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
						sum(i_drr.DrugRequestRow_Summa) as DrugRequestRow_Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
					    i_dr.DrugRequest_Version is null and
						i_dr.DrugRequestPeriod_id = dr_orig.DrugRequestPeriod_id and
						i_drr.Person_id is null and
						(
							i_drr.DrugComplexMnn_id is not null or
							i_drr.TRADENAMES_ID is not null
						) and
						i_drr.DrugComplexMnn_id = drpc.DrugComplexMnn_id and
						isnull(i_drr.TRADENAMES_id, 0) = isnull(drpc.TRADENAMES_id, 0) and
						(:Lpu_id is null or i_dr.Lpu_id = :Lpu_id)
					group by
						i_drr.DrugComplexMnn_id, i_drr.TRADENAMES_ID
				) drr
                {$join_clause}
            {$where_clause}
        ";

		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных спецификации сводной заявки для экспорта
	 */
	function loadDrugRequestPurchaseSpecListForExport($filter) {
		$with = array();
		$select = array();
		$join = array();
        $where = array();

        //фильтры
        if (isset($filter['DrugRequest_id']) && $filter['DrugRequest_id'] > 0) {
            $where[] = 'drpc.DrugRequest_id = :DrugRequest_id';
        } else {
            $filter['DrugRequest_id'] = null;
        }

        if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
            $where[] = 'drpc.DrugFinance_id = :DrugFinance_id';
        }

        if (isset($filter['RlsClsatc_id']) && $filter['RlsClsatc_id'] > 0) {
            $where[] = 'atc.Atc_id = :RlsClsatc_id';
            $join[] = "
                outer apply (
                    select top 1
                        catc.CLSATC_ID as Atc_id,
                        catc.NAME as Atc_Name
                    from
                        rls.DrugComplexMnn dcm2 with(nolock)
                        left join rls.DrugComplexMnnName dcmn2 with(nolock) on dcmn2.DrugComplexMnnName_id = dcm2.DrugComplexMnnName_id
                        left join rls.PREP_ACTMATTERS pact with(nolock) on pact.MATTERID = dcmn2.ActMatters_id
                        right join rls.PREP p with(nolock) on p.Prep_id = pact.PREPID and p.DRUGFORMID = dcm2.CLSDRUGFORMS_ID
                        left join rls.PREP_ATC patc with(nolock) on patc.PREPID = p.Prep_id
                        left join rls.CLSATC catc with(nolock) on catc.CLSATC_ID = patc.UNIQID
                    where
                        DrugComplexMnn_id = drpc.DrugComplexMnn_id
                ) atc
            ";
        }

        if (isset($filter['RlsClsPhGrLimp_id']) && $filter['RlsClsPhGrLimp_id'] > 0) {
            $where[] = '(PhGr.ID = :RlsClsPhGrLimp_id or PhGr.PARENTID = :RlsClsPhGrLimp_id)';
            $join[] = "
                outer apply (
                    select top 1
                        cpl1.CLS_PHGR_LIMP_ID as ID,
                        cpl1.PARENTID,
                        isnull(cpl2.NAME+' ','')+cpl1.NAME as Name
                    from
                        rls.AM_DF_LIMP adl with(nolock)
                        left join rls.CLS_PHGR_LIMP cpl1 with(nolock) on cpl1.CLS_PHGR_LIMP_ID = adl.LIMP_PHGR_ID
                        left join rls.CLS_PHGR_LIMP cpl2 with(nolock) on cpl1.PARENTID <> 0 and cpl2.CLS_PHGR_LIMP_ID = cpl1.PARENTID
                    where
                        adl.ACTMATTERID = dcmn.ActMatters_id and
                        adl.DRUGFORMID = dcm.CLSDRUGFORMS_ID
                ) PhGr
            ";
        }

        if (isset($filter['DrugComplexMnn_Name']) && !empty($filter['DrugComplexMnn_Name'])) {
            $where[] = 'dcm.DrugComplexMnn_RusName like :DrugComplexMnn_Name';
            $filter['DrugComplexMnn_Name'] = "%{$filter['DrugComplexMnn_Name']}%";
        }

		$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';
		$select_clause = count($select) > 0 ? ', '.implode(', ', $select) : '';
        $join_clause = implode(' ', $join);
        $where_clause = count($where) > 0 ? 'where '.implode(' and ', $where) : '';

        $query = "
            select top 1000
                drpc.DrugRequest_id, --Идентификатор заявки
                o.Org_Name, --Организация
                dg.DrugGroup_Name, --Группа медикаментов
                df.DrugFinance_Name, --Тип финансирования
                prt.PersonRegisterType_Name, --Тип регистра
                dcmn.DrugComplexMnnName_Name as DrugComplexMnn_Name, --Медикамент в заявке
                tn.NAME as Tradenames_Name, --Торговое наименование
                cdf.NAME as DrugForm_Name, --Форма выпуска
                dcmd.DrugComplexMnnDose_Name as Dose_Name, --Дозировка
                dcmf.DrugComplexMnnFas_Name as Fas_Name, --Фасовка
                drpc.Evn_id, --Протокол ВК
                drpc.DrugRequestPurchaseSpec_Price, --Цена
                cpt.CalculatPriceType_Name, --Тип расчета цены
                convert(varchar(10), drpc.DrugRequestPurchaseSpec_priceDate, 104) as DrugRequestPurchaseSpec_priceDate, --Дата расчета цены
                drpc.DrugRequestPurchaseSpec_lKolvo, --Количество по заявкам МО
                drpc.DrugRequestPurchaseSpec_lSum, --Стоимость заявленного МО
                drpc.DrugRequestPurchaseSpec_RefuseCount, --Количество упаковок: отказ
                drpc.DrugRequestPurchaseSpec_RestCount, --Количество упаковок: из остатков
                drpc.DrugRequestPurchaseSpec_Kolvo, --Количество «к закупу для МО»
                drpc.DrugRequestPurchaseSpec_Sum, --Стоимость закупаемого для МО
                drpc.DrugRequestPurchaseSpec_pKolvo, --Количество в заявке на закуп
                drpc.DrugRequestPurchaseSpec_pSum, --Стоимость в заявке на закуп
                wdu.WhsDocumentUc_Num --№ лота
            from
                dbo.v_DrugRequestPurchaseSpec drpc with (nolock)
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drpc.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
                left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
                left join rls.ActMatters am with (nolock) on am.ACTMATTERS_ID = dcmn.ActMatters_id
                left join rls.CLSDRUGFORMS cdf with (nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join dbo.v_Org o with (nolock) on o.Org_id = drpc.Org_id
				left join dbo.v_DrugGroup dg with (nolock) on dg.DrugGroup_id = drpc.DrugGroup_id
				left join dbo.v_DrugFinance df  with (nolock) on df.DrugFinance_id = drpc.DrugFinance_id
 				left join dbo.v_PersonRegisterType prt with (nolock) on prt.PersonRegisterType_id = drpc.PersonRegisterType_id
				left join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = drpc.TRADENAMES_id
				left join dbo.v_CalculatPriceType cpt with (nolock) on cpt.CalculatPriceType_id = drpc.CalculatPriceType_id
				left join dbo.v_WhsDocumentUc wdu with (nolock) on wdu.WhsDocumentUc_id = drpc.WhsDocumentUc_id
                $join_clause
            $where_clause
        ";


		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Создание сводной заявки
     * $disable_trans - признак отключения внутренних транзакций
	 */
	function createConsolidatedDrugRequest($data, $disable_trans = false) {
		$id = 0;
		$req_list = $data['SelectedRequest_List'];
        $error = array();

        if (!$disable_trans) {
            //старт транзакции
            $this->beginTransaction();
        }

        if (!empty($data['FinYear'])) {
            //поиск подходящего рабочего периода для заданного финансового года
            $query = "
                select top 1
                    drp.DrugRequestPeriod_id
                from
                    v_DrugRequestPeriod drp with (nolock)
                where
                    drp.DrugRequestPeriod_begDate = :DrugRequestPeriod_begDate and
                    drp.DrugRequestPeriod_endDate = :DrugRequestPeriod_endDate
                order by
                    len(drp.DrugRequestPeriod_Name), drp.DrugRequestPeriod_id;
            ";
            $result = $this->getFirstResultFromQuery($query, array(
                'DrugRequestPeriod_begDate' => $data['FinYear'].'-01-01',
                'DrugRequestPeriod_endDate' => $data['FinYear'].'-12-31'
            ));
            if ($result > 0) {
                $data['DrugRequestPeriod_id'] = $result;
            } else {
                //добавляем в бд новый рабочий период для выбранного рабочего года
                $response = $this->saveObject('DrugRequestPeriod', array(
                    'DrugRequestPeriod_Name' => $data['FinYear'].' год',
                    'DrugRequestPeriod_begDate' => $data['FinYear'].'-01-01',
                    'DrugRequestPeriod_endDate' => $data['FinYear'].'-12-31'
                ));
                if (!empty($response['DrugRequestPeriod_id'])) {
                    $data['DrugRequestPeriod_id'] = $response['DrugRequestPeriod_id'];
                } else if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        //добавление заявки
        if (count($error) == 0) {
            $query = "
                declare
                    @DrugRequest_id bigint,
                    @DrugRequestCategory_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000),
                    @DrugRequestStatus_id bigint;

                set @DrugRequestStatus_id = (select top 1 DrugRequestStatus_id from DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 4);
                set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'svod');

                exec dbo.p_DrugRequest_ins
                    @DrugRequest_id = @DrugRequest_id output,
                    @Server_id = :Server_id,
                    @DrugRequestStatus_id = @DrugRequestStatus_id,
                    @DrugRequest_Name = :DrugRequest_Name,
                    @DrugRequestPeriod_id = :DrugRequestPeriod_id,
                    @PersonRegisterType_id = :PersonRegisterType_id,
                    @DrugGroup_id = :DrugGroup_id,
                    @pmUser_id = :pmUser_id,
                    @DrugRequestCategory_id = @DrugRequestCategory_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @DrugRequest_id as DrugRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $params = array(
                'Server_id' => $data['Server_id'],
                'DrugRequest_Name' => $data['DrugRequest_Name'],
                'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
                'PersonRegisterType_id' => $data['PersonRegisterType_id'],
                'DrugGroup_id' => $data['DrugGroup_id'],
                'pmUser_id' => $data['pmUser_id']
            );

            $response = $this->getFirstRowFromQuery($query, $params);
            if (!empty($response['DrugRequest_id'])) {
                $id = $response['DrugRequest_id'];
            } else {
                $error[] = 'Ошибка при сохранении заявки';
            }
        }

		
		if (count($error) == 0 && $id > 0 && !empty($req_list)) {
			$query = "
				insert into DrugRequestPurchase (
					DrugRequest_id,
					DrugRequest_lid,
					pmUser_insID,
					pmUser_updID,
					DrugRequestPurchase_insDT,
					DrugRequestPurchase_updDT
				)
				select
					:id,
					DrugRequest_id,
					:pmUser_id,
					:pmUser_id,
					getdate(),
					getdate()
				from
					DrugRequest
				where
					DrugRequest_id in ($req_list);
					
				declare
				    @Yes_id bigint,
                    @RegionDrugRequestCategory_id bigint,
                    @CommonPersonRegisterType_id bigint;

                set @Yes_id = (select top 1 YesNo_id from YesNo with(nolock) where YesNo_Code = 1);
                set @RegionDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'region' order by DrugRequestCategory_id);
                set @CommonPersonRegisterType_id = (select top 1 PersonRegisterType_id from PersonRegisterType where PersonRegisterType_SysNick = 'common' order by PersonRegisterType_id);

				insert into DrugRequestPurchaseSpec (
					DrugRequestPurchaseSpec_isOpen,
					DrugRequestPurchaseSpec_Price,
					DrugRequestPurchaseSpec_lKolvo,
					DrugRequestPurchaseSpec_lSum,
					DrugRequestPurchaseSpec_Kolvo,
					DrugRequestPurchaseSpec_Sum,
					DrugRequestPurchaseSpec_pKolvo,
					DrugRequestPurchaseSpec_pSum,
					pmUser_insID,
					pmUser_updID,
					DrugRequestPurchaseSpec_insDT,
					DrugRequestPurchaseSpec_updDT,
					DrugRequest_id,
					DrugComplexMnn_id,
					TRADENAMES_id,
					DrugFinance_id,
					Evn_id,
					Org_id,
					DrugGroup_id,
					PersonRegisterType_id
				)
				select
					@Yes_id,					
					p.DrugRequestRow_Price as DrugRequestPurchaseSpec_Price,
					p.DrugRequestRow_Kolvo as DrugRequestPurchaseSpec_lKolvo,
					(isnull(p.DrugRequestRow_Price, 0) * isnull(p.DrugRequestRow_Kolvo, 0)) as DrugRequestPurchaseSpec_lSum,
					p.DrugRequestRow_Kolvo as DrugRequestPurchaseSpec_Kolvo,
					(isnull(p.DrugRequestRow_Price, 0) * isnull(p.DrugRequestRow_Kolvo, 0)) as DrugRequestPurchaseSpec_Sum,
					p.DrugRequestRow_Kolvo as DrugRequestPurchaseSpec_pKolvo,
					(isnull(p.DrugRequestRow_Price, 0) * isnull(p.DrugRequestRow_Kolvo, 0)) as DrugRequestPurchaseSpec_pSum,
					:pmUser_id,
					:pmUser_id,
					getdate(),
					getdate(),
					:id,
					p.DrugComplexMnn_id,
					p.TRADENAMES_id,
					p.DrugFinance_id,
					p.Evn_id,
					p.Org_id,
					p.DrugGroup_id,
					p.PersonRegisterType_id
				from (
					select
						sum(DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
                        max(DrugRequestRow_Price) as DrugRequestRow_Price,
                        Org_id,
                        DrugGroup_id,
                        PersonRegisterType_id,
                        DrugFinance_id,
                        DrugComplexMnn_id,
                        TRADENAMES_id,
                        Evn_id
					from (
						select
							DrugRequestRow.DrugRequestRow_Kolvo,
                            DrugRequestRow_Price.DrugListRequest_Price as DrugRequestRow_Price,
                            (
                                case
                                    when
                                        RegionDrugRequest.PersonRegisterType_id is not null
                                    then
                                        RegionDrugRequest.Org_id
                                    else
                                        Lpu.Org_id
                                end
                            ) as Org_id,
                            RegionDrugRequest.DrugGroup_id,
                            RegionDrugRequest.PersonRegisterType_id,
                            DrugRequestRow.DrugFinance_id,
                            DrugRequestRow.DrugComplexMnn_id,
                            DrugRequestRow.TRADENAMES_id,
                            DrugRequestRow.Evn_id
						from
							v_DrugRequestRow DrugRequestRow with (nolock)
                            left join DrugRequest with (nolock) on DrugRequest.DrugRequest_id = DrugRequestRow.DrugRequest_id
                            left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = DrugRequest.Lpu_id
                            outer apply(
                                select top 1
                                    drp.DrugRequestProperty_id,
                                    dr.DrugGroup_id,
                                    dr.PersonRegisterType_id,
                                    drp.Org_id
                                from
                                    DrugRequest dr with (nolock)
                                    outer apply (
                                        select (
                                            case
                                                when isnull(dr.PersonRegisterType_id, 0) <> @CommonPersonRegisterType_id then dr.DrugRequestProperty_id
                                                when dr.PersonRegisterType_id = @CommonPersonRegisterType_id and DrugRequestRow.DrugFinance_id = 3 then dr.DrugRequestPropertyFed_id
                                                when dr.PersonRegisterType_id = @CommonPersonRegisterType_id and DrugRequestRow.DrugFinance_id = 27 then dr.DrugRequestPropertyReg_id
                                                else null
                                            end
                                        ) as DrugRequestProperty_id
                                    ) drp_id
                                    left join v_DrugRequestProperty drp with (nolock) on drp.DrugRequestProperty_id = drp_id.DrugRequestProperty_id
                                where
                                    dr.DrugRequest_Version is null and
                                    dr.DrugRequestCategory_id = @RegionDrugRequestCategory_id and
                                    dr.DrugRequestPeriod_id = DrugRequest.DrugRequestPeriod_id and
                                    isnull(dr.PersonRegisterType_id, 0) = isnull(DrugRequest.PersonRegisterType_id, 0) and
                                    isnull(dr.DrugRequestKind_id, 0) = isnull(DrugRequest.DrugRequestKind_id, 0) and
                                    isnull(dr.DrugGroup_id, 0) = isnull(DrugRequest.DrugGroup_id, 0)
                            ) RegionDrugRequest
                            outer apply (
                                select top 1
                                    (case
                                        when dlrt.DrugRequest_Price > 0 then dlrt.DrugRequest_Price
                                        else dlr.DrugListRequest_Price
                                    end) as DrugListRequest_Price
                                from
                                    v_DrugListRequest dlr with (nolock)
                                    left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = DrugRequestRow.TRADENAMES_id
                                where
                                    dlr.DrugComplexMnn_id = DrugRequestRow.DrugComplexMnn_id
                                    and dlr.DrugRequestProperty_id = RegionDrugRequest.DrugRequestProperty_id
                            ) DrugRequestRow_Price
						where
							DrugRequestRow.DrugComplexMnn_id is not null and
							DrugRequestRow.DrugRequest_id in (
								select
									dr2.DrugRequest_id
								from
									DrugRequest dr with(nolock)
									left join DrugRequest dr2 with (nolock) on
										dr2.DrugRequest_Version is null and
										dr2.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
										isnull(dr2.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
										isnull(dr2.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
										isnull(dr2.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
										dr2.Lpu_id is not null and
										dr2.MedPersonal_id is not null
								where
									dr.DrugRequest_id in ($req_list)
							)
					) pp
					group by
						Org_id, DrugGroup_id, PersonRegisterType_id,  DrugFinance_id, DrugComplexMnn_id, TRADENAMES_id, Evn_id
				) p;
			";			
			$params = array(
				'id' => $id,
				'pmUser_id' => $data['pmUser_id']
			);
			$response = $this->db->query($query, $params);
		}

        $result = array();

        if (count($error) > 0) {
            if (!$disable_trans) {
                //откат транзакции
                $this->rollbackTransaction();
            }
            $result['Error_Msg'] = $error[0];
        } else {
            if (!$disable_trans) {
                //коммит транзакции
                $this->commitTransaction();
            }
            $result['DrugRequest_id'] = $id;
            $result['success'] = true;
        }

        return $result;
	}

	/**
	 * Расчет лимитированной заявки
	 */
	function recalculateDrugRequestByFin($data) {
		$result = array();

		if (!empty($data['background_mode_enabled'])) {
			ignore_user_abort(true); // игнорирует отключение пользователя и позволяет скрипту быть запущенным постоянно
			set_time_limit(7200); // это может выполняться весьма и весьма долго

			ob_start();
			echo json_encode(array("success" => "true"));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}

		try {
			$this->beginTransaction();

			//проверка и очистка входящих параметров
			if (!empty($data['Lpu_List'])) {
				$data['Lpu_List'] = preg_replace('/[^0-9 ,]/', '', $data['Lpu_List']);
			}

			//проверка текущего статуса и наличия первой копии заявки
			$query = "
				 select
                    dr.DrugRequest_id,
                    dr.DrugRequestPeriod_id,
					dr.PersonRegisterType_id,
					dr.DrugRequestKind_id,
					dr.DrugGroup_id,
                    dr.DrugRequestStatus_id,
                    dr.DrugRequestProperty_id,
					dr.DrugRequestPropertyFed_id,
					dr.DrugRequestPropertyReg_id,
                    drs.DrugRequestStatus_Code,
                    fc.DrugRequest_id as RegionDrugRequestFirstCopy_id
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    outer apply (
                        select top 1
                            fc_dr.DrugRequest_id
                        from
                            v_DrugRequest fc_dr with(nolock)
                            left join v_DrugRequestCategory fc_drc with(nolock) on fc_drc.DrugRequestCategory_id = fc_dr.DrugRequestCategory_id
                        where
                            fc_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(fc_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(fc_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            isnull(fc_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            fc_dr.DrugRequest_Version = 1 and
                            fc_drc.DrugRequestCategory_SysNick = 'region' and
                            fc_dr.DrugRequest_id <> dr.DrugRequest_id
                    ) fc
                where
                    dr.DrugRequest_id = :RegionDrugRequest_id;
			";
			$dr_data = $this->getFirstRowFromQuery($query, array(
				'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
			));
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("При получении данных заявочной кампании произошла ошибка");
			}
			if ($dr_data['DrugRequestStatus_Code'] != '1') { //1 - Начальная
				throw new Exception("Недопустимый статус заявочной кампании");
			}
			if (empty($dr_data['RegionDrugRequestFirstCopy_id'])) {
				throw new Exception("Отсутствуют данные о реальной потребности для заявочной кампании");
			}

			//смена статуса заявочной кампании на время расчета
			if (empty($data['status_change_disabled'])) {
				$save_result = $this->saveObject('DrugRequest', array(
					'DrugRequest_id' => $dr_data['DrugRequest_id'],
					'DrugRequestStatus_id' => $this->getObjectIdByCode('DrugRequestStatus', 8) //8 - Выполняется операция обработки
				));
				if (empty($save_result['DrugRequest_id'])) {
					throw new Exception("При сохранении статуса заявочной кампании произошла ошибка");
				}
			}

			//перед расчетом необходимо произвести расчет объемов участковых заявок в рамках первой копии заявочной кампании (отключено по причине больших затрат на выполнение, плановые параметры предполагается копировать из оригинала)
			/*$response = $this->calculateDrugRequestPlanLpuRegionParams(array(
				'RegionDrugRequest_id' => $dr_data['RegionDrugRequestFirstCopy_id'],
				'transaction_disabled' => true,
				'status_check_disabled' => true
			));
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}*/

			//получение списка заявок для обработки а также информации об объеме финансирования и объеме заявки участка первой копии
			$lpu_filter = !empty($data['Lpu_List']) ? "dr.Lpu_id in ({$data['Lpu_List']}) and " : "";
			$query = "
				declare
					@DrugRequestCategory_id bigint = null;
								
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
					
				select
					dr.DrugRequest_id,
					dr.Lpu_id,
					fc.DrugRequest_id as DrugRequestFirstCopy_id,
					drp.DrugRequestPlan_Summa,
					drp.DrugRequestPlan_FedSumma,
					drp.DrugRequestPlan_RegSumma,
					drp_fc.DrugRequestPlan_CountReq,
					drp_fc.DrugRequestPlan_CountFed,
					drp_fc.DrugRequestPlan_CountReg,
					(case
						when drp_fc.DrugRequestPlan_CountReq > 0 then isnull(drp.DrugRequestPlan_Summa, 0)/cast(drp_fc.DrugRequestPlan_CountReq as float)
						else 0
					end) as koef,
					(case
						when drp_fc.DrugRequestPlan_CountFed > 0 then isnull(drp.DrugRequestPlan_FedSumma, 0)/cast(drp_fc.DrugRequestPlan_CountFed as float)
						else 0
					end) as koef_fed,
					(case
						when drp_fc.DrugRequestPlan_CountReg > 0 then isnull(drp.DrugRequestPlan_RegSumma, 0)/cast(drp_fc.DrugRequestPlan_CountReg as float)
						else 0
					end) as koef_reg
				from
					v_DrugRequest dr with (nolock)
					outer apply (
						select top 1
							fc_dr.DrugRequest_id
						from
							v_DrugRequest fc_dr with(nolock)
						where
							fc_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
							isnull(fc_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
							isnull(fc_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
							isnull(fc_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
							isnull(fc_dr.Lpu_id, 0) = isnull(dr.Lpu_id, 0) and
							isnull(fc_dr.LpuSection_id, 0) = isnull(dr.LpuSection_id, 0) and
							isnull(fc_dr.LpuUnit_id, 0) = isnull(dr.LpuUnit_id, 0) and
							isnull(fc_dr.LpuRegion_id, 0) = isnull(dr.LpuRegion_id, 0) and
							isnull(fc_dr.MedPersonal_id, 0) = isnull(dr.MedPersonal_id, 0) and
							fc_dr.DrugRequest_Version = 1 and
							fc_dr.DrugRequestCategory_id = dr.DrugRequestCategory_id
					) fc
					outer apply (
						select top 1
							i_drp.DrugRequestPlan_Summa,
							i_drp.DrugRequestPlan_FedSumma,
							i_drp.DrugRequestPlan_RegSumma
						from
							v_DrugRequestPlan i_drp with (nolock)
						where
							i_drp.DrugRequest_id = :RegionDrugRequest_id and
							i_drp.Lpu_id = dr.Lpu_id and
							i_drp.LpuRegion_id = dr.LpuRegion_id
						order by
							i_drp.DrugRequestPlan_id
					) drp				
					outer apply (
						select top 1
							i_drp_fc.DrugRequestPlan_CountReq,
							i_drp_fc.DrugRequestPlan_CountFed,
							i_drp_fc.DrugRequestPlan_CountReg
						from
							v_DrugRequestPlan i_drp_fc with (nolock)
						where
							i_drp_fc.DrugRequest_id = :RegionDrugRequestFirstCopy_id and
							i_drp_fc.Lpu_id = dr.Lpu_id and
							i_drp_fc.LpuRegion_id = dr.LpuRegion_id
						order by
							i_drp_fc.DrugRequestPlan_id
					) drp_fc
				where
					isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
					dr.DrugRequestCategory_id = @DrugRequestCategory_id and
					dr.DrugRequest_Version is null and
					fc.DrugRequest_id is not null and
					{$lpu_filter}
					dr.LpuRegion_id is not null and (
						isnull(drp_fc.DrugRequestPlan_CountReq, 0) > 0 or
						isnull(drp_fc.DrugRequestPlan_CountFed, 0) > 0 or
						isnull(drp_fc.DrugRequestPlan_CountReg, 0) > 0
					);
			";
			$dr_list = $this->queryResult($query, array(
				'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $dr_data['DrugGroup_id'],
				'RegionDrugRequest_id' => $data['RegionDrugRequest_id'],
				'RegionDrugRequestFirstCopy_id' => $dr_data['RegionDrugRequestFirstCopy_id']
			));

			//проверяем для всех ли заявок указан обьем финансирования объем участковой заявки первой копии
			/*$err_msg_fin = "Выполнение расчета невозможно, т.к. не для всех участковых заявок МО определены объемы финансирования. Рассчитайте объемы финансирования заявок и повторите расчет по уменьшению лимитированной заявки.";
			$err_msg_fc = "Выполнение расчета невозможно, т.к. не для всех участковых заявок МО определены объемы участковых заявок в реальной потребности";
			if ($dr_data['PersonRegisterType_id'] == 1) {
				foreach($dr_list as $request) {
					if (empty($request['DrugRequestPlan_FedSumma']) && empty($request['DrugRequestPlan_FedSumma'])) {
						throw new Exception($err_msg_fin);
					}
					if (empty($request['DrugRequestPlan_CountFed']) && empty($request['DrugRequestPlan_CountReg'])) {
						throw new Exception($err_msg_fc);
					}
				}
			} else {
				foreach($dr_list as $request) {
					if (empty($request['DrugRequestPlan_Summa'])) {
						throw new Exception($err_msg_fin);
					}
					if (empty($request['DrugRequestPlan_CountReq'])) {
						throw new Exception($err_msg_fc);
					}
				}
			}*/

			//перерасчет персональнаой разнарядки
			$fed_df_id = 3;
            $reg_df_id = 27;
			$pmuser_id = $this->getPromedUserId();

			//создание временной таблицы
			$tmp_tbl_name = "#drpo_tmp_tbl".time();
			$query = "
				declare
					@Error_Code int,
					@Error_Message varchar(4000)
				set nocount on
				begin try
					if object_id(N'tempdb..{$tmp_tbl_name}', N'U') is not null
						drop table {$tmp_tbl_name};	
					create table {$tmp_tbl_name} (
						DrugRequestPersonOrder_id bigint,
						DrugRequestPersonOrderFirstCopy_id bigint,
						DrugRequestPersonOrder_OrdKolvo float,
						DrugRequestRow_id bigint,
						DrugRequest_id bigint,
						DrugFinance_id bigint,
						koef float
					)
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$res = $this->getFirstRowFromQuery($query);
			if (!is_array($res) || !empty($res['Error_Msg'])) {
				throw new Exception("При создании временной таблицы произошла ошибка");
			}

			foreach($dr_list as $request) {
				//если коэфицент больше 1 либо нет данных для расчета, заявку можно пропустить
				if ($dr_data['PersonRegisterType_id'] == 1) {
					if (
						(empty($request['DrugRequestPlan_FedSumma']) && empty($request['DrugRequestPlan_RegSumma'])) ||
						(empty($request['DrugRequestPlan_CountFed']) && empty($request['DrugRequestPlan_CountReg'])) ||
						($request['koef_fed'] >= 1 && $request['koef_reg'] >= 1)
					) {
						continue;
					}
				} else {
					if (
						empty($request['DrugRequestPlan_Summa']) ||
						empty($request['DrugRequestPlan_CountReq']) ||
						$request['koef'] >= 1
					) {
						continue;
					}
				}

				//очистка временной таблицы
				$res = $this->getFirstRowFromQuery("
					declare
						@Error_Code int,
						@Error_Message varchar(4000)
					set nocount on
					begin try
						delete from {$tmp_tbl_name}
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				");
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При очистке временной таблицы произошла ошибка");
				}

				if ($dr_data['PersonRegisterType_id'] == 1) { //общетерапевтический регистр
					$koef_sql = "(case when drr.DrugFinance_id = {$fed_df_id} then {$request['koef_fed']} when drr.DrugFinance_id = {$reg_df_id} then {$request['koef_reg']} else 0 end)";
				} else {
					$koef_sql = $request['koef'];
				}

				//заносим список позиций персонльной разнарядки первой копии во временную таблицу
				$query = "
					declare
                        @LpuRegionDrugRequest_id bigint = :LpuRegionDrugRequest_id,
                        @Lpu_id bigint = :Lpu_id,
                        @DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
                        @PersonRegisterType_id bigint = :PersonRegisterType_id,
                        @DrugRequestKind_id bigint = :DrugRequestKind_id,
                        @DrugGroup_id bigint = :DrugGroup_id,
                        @DrugRequestCategory_id bigint = null,
						@Error_Code int,
						@Error_Message varchar(4000)
					set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach')
					set nocount on
					begin try
						with dr_list(DrugRequest_id) as (
							select
								DrugRequest_id
							from
								v_DrugRequest dr with(nolock)
								outer apply (
									select top 1
										i_aro.AccessRightsName_id
									from
										v_Lpu i_l with (nolock)
										left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
									where
										i_l.Lpu_id = dr.Lpu_id
								) arl
							where
								dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
								isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
								isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
								isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
								dr.DrugRequestCategory_id = @DrugRequestCategory_id and
								dr.DrugRequest_Version is null and
								(
									dr.Lpu_id = @Lpu_id or
									arl.AccessRightsName_id is null
								)
						),
						fc_dr_list(DrugRequest_id) as (
							select
								DrugRequest_id
							from
								v_DrugRequest dr with(nolock)
								outer apply (
									select top 1
										i_aro.AccessRightsName_id
									from
										v_Lpu i_l with (nolock)
										left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
									where
										i_l.Lpu_id = dr.Lpu_id
								) arl
							where
								dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
								isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
								isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
								isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
								dr.DrugRequestCategory_id = @DrugRequestCategory_id and
								dr.DrugRequest_Version = 1 and
								(
									dr.Lpu_id = @Lpu_id or
									arl.AccessRightsName_id is null
								)
						)
						insert into {$tmp_tbl_name}
						select
							drpo.DrugRequestPersonOrder_id,
							drpo_fc.DrugRequestPersonOrder_id as DrugRequestPersonOrderFirstCopy_id,
							drpo_fc.DrugRequestPersonOrder_OrdKolvo,
							drr.DrugRequestRow_id,
							drr.DrugRequest_id,
							drr.DrugFinance_id,
							k.koef
						from
							fc_dr_list dr_fc
							left join v_DrugRequestPersonOrder drpo_fc with (nolock) on drpo_fc.DrugRequest_id = dr_fc.DrugRequest_id
							outer apply (
								select top 1
									i_drpo.DrugRequestPersonOrder_id,
									i_drpo.DrugRequest_id,
									i_drpo.DrugComplexMnn_id,
									i_drpo.Tradenames_id,
									i_drpo.DrugRequestPersonOrder_OrdKolvo
								from
									dr_list i_dr
									left join v_DrugRequestPersonOrder i_drpo with (nolock) on i_drpo.DrugRequest_id = i_dr.DrugRequest_id
								where
									i_drpo.Person_id = drpo_fc.Person_id and
									isnull(i_drpo.DrugComplexMnn_id, 0) = isnull(drpo_fc.DrugComplexMnn_id, 0) and
									isnull(i_drpo.Tradenames_id, 0) = isnull(drpo_fc.Tradenames_id, 0)
								order by
									i_drpo.DrugRequestPersonOrder_id
							) drpo
							outer apply (
								select top 1
									i_drr.DrugRequest_id,
									i_drr.DrugRequestRow_id,
									i_drr.DrugFinance_id								
								from
									v_DrugRequestRow i_drr with (nolock)
								where
									i_drr.DrugRequest_id = drpo.DrugRequest_id and
									isnull(i_drr.DrugComplexMnn_id, 0) = isnull(drpo.DrugComplexMnn_id, 0) and
									isnull(i_drr.TRADENAMES_id, 0) = isnull(drpo.Tradenames_id, 0)
								order by
									i_drr.DrugFinance_id desc, i_drr.DrugRequestRow_id
							) drr
							outer apply (
								select
									{$koef_sql} as koef
							) k
						where
							drpo_fc.Person_id in (
								select
									ii_drpo.Person_id
								from
									v_DrugRequestPersonOrder ii_drpo with (nolock)
								where
									ii_drpo.DrugRequest_id = @LpuRegionDrugRequest_id
							) and
							(
								drpo_fc.DrugComplexMnn_id is not null or
								drpo_fc.Tradenames_id is not null
							) and
							drpo.DrugRequestPersonOrder_id is not null and
							drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
							drpo_fc.DrugRequestPersonOrder_OrdKolvo > 0 and
							k.koef < 1
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'LpuRegionDrugRequest_id' => $request['DrugRequest_id'],
					'Lpu_id' => $request['Lpu_id'],
					'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
                    'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
                    'DrugGroup_id' => $dr_data['DrugGroup_id']
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При формировании данных для пересчета произошла ошибка");
				}

				//пересчет количества
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@Error_Code int,
						@Error_Message varchar(4000)
					set nocount on
					begin try
						update
							DrugRequestPersonOrder
						set
							DrugRequestPersonOrder_OrdKolvo = (case when kolvo.kolvo > 0 then kolvo.kolvo else 1 end),
							DrugRequestPersonOrder_updDT = @datetime,
							pmUser_updID = {$pmuser_id}
						from
							DrugRequestPersonOrder drpo with (nolock)
							inner join {$tmp_tbl_name} k_data on k_data.DrugRequestPersonOrder_id = drpo.DrugRequestPersonOrder_id
							outer apply (
								select
									floor(k_data.DrugRequestPersonOrder_OrdKolvo * k_data.koef) as kolvo
							) kolvo
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query);
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При пересчете количества в разнарядках произошла ошибка");
				}

				//пересчет списка медикаментов
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@Error_Code int,
						@Error_Message varchar(4000)
					set nocount on
					begin try
						update
							DrugRequestRow
						set
							DrugRequestRow_Kolvo = drpo.kolvo,
							DrugRequestRow_Summa = cast(isnull(dlr.Price*drpo.kolvo, 0) as money),
							DrugRequestRow_updDT = @datetime,
							pmUser_updID = {$pmuser_id} 
						from
							DrugRequestRow drr with (nolock)
							outer apply (
								select
									sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as kolvo
								from
									v_DrugRequestPersonOrder i_drpo with (nolock)
								where
									i_drpo.DrugRequest_id = drr.DrugRequest_id and
									i_drpo.Person_id is not null and
									isnull(i_drpo.DrugComplexMnn_id, 0) = isnull(drr.DrugComplexMnn_id, 0) and
									isnull(i_drpo.Tradenames_id, 0) = isnull(drr.TRADENAMES_id, 0)
							) drpo
							left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
							outer apply (
								select (case
									when :PersonRegisterType_id <> 1 then :DrugRequestProperty_id
									when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceFed_id then :DrugRequestPropertyFed_id
									when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceReg_id then :DrugRequestPropertyReg_id
									else null
								end) as DrugRequestProperty_id
							) drp
							outer apply (
								select top 1
									(case
										when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
										else i_dlr.DrugListRequest_Price
									end) as Price
								from
									v_DrugListRequest i_dlr with (nolock)
									left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
								where
									i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id and
									i_dlr.DrugRequestProperty_id = drp.DrugRequestProperty_id
								order by
									DrugListRequest_insDT desc
							) dlr
						where
							drr.DrugRequestRow_id in (select DrugRequestRow_id from {$tmp_tbl_name})
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'PersonRegisterType_id' => $dr_data['DrugRequest_id'],
					'DrugRequestProperty_id' => $dr_data['DrugRequestProperty_id'],
					'DrugRequestPropertyFed_id' => $dr_data['DrugRequestPropertyFed_id'],
					'DrugRequestPropertyReg_id' => $dr_data['DrugRequestPropertyReg_id'],
					'DrugFinanceFed_id' => $fed_df_id,
					'DrugFinanceReg_id' => $reg_df_id
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При пересчете списка медикаментов произошла ошибка");
				}
			}

			//пересчет сумм заявок врачей
			if (empty($data['dr_sum_recalculate_disabled'])) {
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@DrugRequestCategory_id bigint = null,
						@Error_Code int,
						@Error_Message varchar(4000)
					set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach')
					set nocount on
					begin try
						update
							DrugRequest
						set
							DrugRequest_Summa = drr_s.DrugRequest_Summa,
							DrugRequest_updDT = @datetime,
							pmUser_updID = {$pmuser_id} 
						from
							DrugRequest dr with (nolock)
							outer apply (
								select
									sum(cast(isnull(drr.DrugRequestRow_Summa, 0) as money)) as DrugRequest_Summa
								from
									v_DrugRequestRow drr with (nolock)
								where
									drr.DrugRequest_id = dr.DrugRequest_id
							) drr_s
						where
							isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
							isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
							isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
							isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
							dr.DrugRequestCategory_id = @DrugRequestCategory_id and
							dr.DrugRequest_Version is null and
							dr.Lpu_id is not null
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
					'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
					'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
					'DrugGroup_id' => $dr_data['DrugGroup_id']
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При сохранении сумм заявок врачей произошла ошибка");
				}
			}

			//пересчет сумм заявок МО
			if (empty($data['dr_sum_recalculate_disabled'])) {
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@VrachDrugRequestCategory_id bigint = null,								
						@MoDrugRequestCategory_id bigint = null,
						@Error_Code int,
						@Error_Message varchar(4000)						
					set @VrachDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach')
					set @MoDrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'MO')
					set nocount on
					begin try
						update
							DrugRequest
						set
							DrugRequest_Summa = dr_s.DrugRequest_Summa,
							DrugRequest_updDT = @datetime,
							pmUser_updID = {$pmuser_id} 
						from
							DrugRequest dr with (nolock)
							outer apply (
								select
									sum(isnull(i_dr.DrugRequest_Summa, 0)) as DrugRequest_Summa
								from
									v_DrugRequest i_dr with (nolock)
								where
									isnull(i_dr.DrugRequestPeriod_id, 0) = isnull(dr.DrugRequestPeriod_id, 0) and
									isnull(i_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
									isnull(i_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
									isnull(i_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and					
									i_dr.DrugRequestCategory_id = @VrachDrugRequestCategory_id and
									i_dr.DrugRequest_Version is null and
									i_dr.Lpu_id is not null
							) dr_s
						where
							isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
							isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
							isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
							isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
							dr.DrugRequestCategory_id = @MoDrugRequestCategory_id and
							dr.DrugRequest_Version is null and
							dr.Lpu_id is not null
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
					'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
					'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
					'DrugGroup_id' => $dr_data['DrugGroup_id']
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При сохранении сумм заявок МО произошла ошибка");
				}
			}

			//вычисление и сохранение суммы заявочной кампании
			if (empty($data['dr_sum_recalculate_disabled'])) {
				$query = "
					select
						sum(isnull(dr.DrugRequest_Summa, 0)) as DrugRequest_Summa
					from
						v_DrugRequest dr with (nolock)
						left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
					where
						isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
						isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and					
						drc.DrugRequestCategory_SysNick = 'MO' and
						dr.DrugRequest_Version is null;
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
					'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
					'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
					'DrugGroup_id' => $dr_data['DrugGroup_id']
				));
				if (isset($res['DrugRequest_Summa'])) {
					$save_result = $this->saveObject('DrugRequest', array(
						'DrugRequest_id' => $dr_data['DrugRequest_id'],
						'DrugRequest_Summa' => $res['DrugRequest_Summa']
					));
					if (empty($save_result['DrugRequest_id'])) {
						throw new Exception("При сохранении суммы заявочной кампании произошла ошибка");
					}
				} else {
					throw new Exception("При вычичлении суммы заявочной кампании произошла ошибка");
				}
			}

			//смена статуса заявочной кампании обратно на оригинальный
			if (empty($data['status_change_disabled'])) {
				$save_result = $this->saveObject('DrugRequest', array(
					'DrugRequest_id' => $dr_data['DrugRequest_id'],
					'DrugRequestStatus_id' => $dr_data['DrugRequestStatus_id']
				));
				if (empty($save_result['DrugRequest_id'])) {
					throw new Exception("При сохранении статуса заявочной кампании произошла ошибка");
				}
			}

			//после расчета необходимо произвести расчет объемов участковых заявок в рамках заявочной кампании (отключено по причине больших затрат на выполнение, плановые параметры предполагается обновлять из интерфейса либо при ручном вызове функции пересчетаcopyDrugRequestPlanToFirstCopy)
			/*$response = $this->calculateDrugRequestPlanLpuRegionParams(array(
				'RegionDrugRequest_id' => $data['RegionDrugRequest_id'],
				'transaction_disabled' => true,
				'status_check_disabled' => true
			));
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}*/

			$this->commitTransaction();
			$result['success'] = true;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Пересчет сумм и количеств в заявочной компании, по содержимому персональной разнарядки
	 */
	function recalculateDrugRequestByPersonOrderKolvo($data) {
		$result = array();

		try {
			$this->beginTransaction();

			//проверка текущего статуса и наличия первой копии заявки
			$query = "
				 select
                    dr.DrugRequest_id,
                    dr.DrugRequestPeriod_id,
					dr.PersonRegisterType_id,
					dr.DrugRequestKind_id,
					dr.DrugGroup_id,
					dr.DrugRequest_Version,
                    dr.DrugRequestProperty_id,
					dr.DrugRequestPropertyFed_id,
					dr.DrugRequestPropertyReg_id
                from
                    v_DrugRequest dr with (nolock)
                where
                    dr.DrugRequest_id = :RegionDrugRequest_id;
			";
			$dr_data = $this->getFirstRowFromQuery($query, array(
				'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
			));
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("При получении данных заявочной кампании произошла ошибка");
			}

			//получение списка заявок для обработки а также информации об объеме финансирования и объеме заявки участка первой копии
			$query = "
				declare
					@DrugRequestCategory_id bigint = null;
								
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
					
				select
					dr.DrugRequest_id
				from
					v_DrugRequest dr with (nolock)
				where
					isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and					
					dr.DrugRequestCategory_id = @DrugRequestCategory_id;
			";
			$dr_list = $this->queryResult($query, array(
				'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $dr_data['DrugRequest_Version']
			));

			//перерасчет персональнаой разнарядки
			$fed_df_id = 3;
			$reg_df_id = 27;
			foreach($dr_list as $request) {
				//получаем список позиций персонльной разнарядки
				$query = "
					select
						p.DrugRequestRow_id,
						p.Kolvo as DrugRequestRow_Kolvo,
						cast(isnull(dlr.Price*p.Kolvo, 0) as money) as DrugRequestRow_Summa
					from
						(
							select
								i_drr.DrugRequestRow_id,
								sum(drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo
							from
								v_DrugRequestPersonOrder drpo with (nolock)
								outer apply (
									select top 1
										ii_drr.DrugRequestRow_id							
									from
										v_DrugRequestRow ii_drr with (nolock)
									where
										ii_drr.DrugRequest_id = drpo.DrugRequest_id and
										isnull(ii_drr.DrugComplexMnn_id, 0) = isnull(drpo.DrugComplexMnn_id, 0) and
										isnull(ii_drr.TRADENAMES_id, 0) = isnull(drpo.Tradenames_id, 0)
									order by
										ii_drr.DrugFinance_id desc, ii_drr.DrugRequestRow_id
								) i_drr
							where
								drpo.DrugRequest_id = :DrugRequest_id and
								drpo.Person_id is not null and
								(
									drpo.DrugComplexMnn_id is not null or
									drpo.Tradenames_id is not null
								) and
								drpo.DrugRequestPersonOrder_id is not null and
								drpo.DrugRequestPersonOrder_OrdKolvo > 0 and 
								i_drr.DrugRequestRow_id is not null
							group by
								i_drr.DrugRequestRow_id
						) p
						left join v_DrugRequestRow drr with (nolock) on drr.DrugRequestRow_id = p.DrugRequestRow_id
						outer apply (
							select (case
								when :PersonRegisterType_id <> 1 then :DrugRequestProperty_id
								when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceFed_id then :DrugRequestPropertyFed_id
								when :PersonRegisterType_id = 1 and drr.DrugFinance_id = :DrugFinanceReg_id then :DrugRequestPropertyReg_id
								else null
							end) as DrugRequestProperty_id
						) drp
						outer apply (
							select top 1
								(case
									when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
									else i_dlr.DrugListRequest_Price
								end) as Price
							from
								v_DrugListRequest i_dlr with (nolock)
								left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
							where
								i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id and
								i_dlr.DrugRequestProperty_id = drp.DrugRequestProperty_id
							order by
								DrugListRequest_insDT desc
						) dlr
				";
				$row_array = $this->queryResult($query, array(
					'DrugRequest_id' => $request['DrugRequest_id'],
					'PersonRegisterType_id' => $dr_data['DrugRequest_id'],
					'DrugRequestProperty_id' => $dr_data['DrugRequestProperty_id'],
					'DrugRequestPropertyFed_id' => $dr_data['DrugRequestPropertyFed_id'],
					'DrugRequestPropertyReg_id' => $dr_data['DrugRequestPropertyReg_id'],
					'DrugFinanceFed_id' => $fed_df_id,
					'DrugFinanceReg_id' => $reg_df_id
				));

				//пересчет списка медикаментов
				foreach ($row_array as $row_data) {
					//обновление количество медикамента в строке заявки
					$save_result = $this->saveObject('DrugRequestRow', array(
						'DrugRequestRow_id' => $row_data['DrugRequestRow_id'],
						'DrugRequestRow_Kolvo' => $row_data['DrugRequestRow_Kolvo'],
						'DrugRequestRow_Summa' => $row_data['DrugRequestRow_Summa']
					));
					if (empty($save_result['DrugRequestRow_id'])) {
						throw new Exception("При сохранении строки заявки произошла ошибка");
					}
				}

				//пересчет суммы заявки
				$query = "
					select
						sum(cast(isnull(drr.DrugRequestRow_Summa, 0) as money)) as DrugRequest_Summa
					from
						v_DrugRequestRow drr with (nolock)
					where
						drr.DrugRequest_id = :DrugRequest_id;
				";
				$sum_data = $this->getFirstRowFromQuery($query, array(
					'DrugRequest_id' => $request['DrugRequest_id']
				));

				//сохранение суммы заявки
				$save_result = $this->saveObject('DrugRequest', array(
					'DrugRequest_id' => $request['DrugRequest_id'],
					'DrugRequest_Summa' => !empty($sum_data['DrugRequest_Summa']) ? $sum_data['DrugRequest_Summa'] : 0
				));
				if (empty($save_result['DrugRequest_id'])) {
					throw new Exception("При сохранении суммы заявочной кампании произошла ошибка");
				}
			}

			//пересчет сумм заявок МО
			$lpu_sum = array();
			$query = "
				select
					dr.DrugRequest_id,
					dr.Lpu_id,
					isnull(dr.DrugRequest_Summa, 0) as DrugRequest_Summa,
					drc.DrugRequestCategory_SysNick
				from
					v_DrugRequest dr with (nolock)
					left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				where
					isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
					isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and					
					drc.DrugRequestCategory_SysNick in ('vrach', 'MO', 'glavMZ', 'building', 'section') and
					dr.Lpu_id is not null;
			";
			$req_list = $this->queryResult($query, array(
				'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
				'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
				'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
				'DrugGroup_id' => $dr_data['DrugGroup_id'],
				'DrugRequest_Version' => $dr_data['DrugRequest_Version']
			));
			foreach($req_list as $request) {
				if (!isset($lpu_sum[$request['Lpu_id']])) {
					$lpu_sum[$request['Lpu_id']] = array(
						'DrugRequest_id' => null,
						'sum' => 0
					);
				}
				if ($request['DrugRequestCategory_SysNick'] == 'MO') {
					$lpu_sum[$request['Lpu_id']]['DrugRequest_id'] = $request['DrugRequest_id'];
				} else {
					$lpu_sum[$request['Lpu_id']]['sum'] += $request['DrugRequest_Summa']*1;
				}
			}

			//сохранение сумм заявок МО и рассчет суммы заявочной кампании
			$total_sum = 0;
			foreach($lpu_sum as $lpu_id => $sum_data) {
				if (!empty($sum_data['DrugRequest_id'])) {
					$total_sum += $sum_data['sum'];

					//сохранение суммы заявки МО
					$save_result = $this->saveObject('DrugRequest', array(
						'DrugRequest_id' => $sum_data['DrugRequest_id'],
						'DrugRequest_Summa' => $sum_data['sum']
					));
					if (empty($save_result['DrugRequest_id'])) {
						throw new Exception("При сохранении суммы заявки произошла ошибка");
					}
				}
			}

			//сохранение суммы заявочной кампании
			$save_result = $this->saveObject('DrugRequest', array(
				'DrugRequest_id' => $dr_data['DrugRequest_id'],
				'DrugRequest_Summa' => $total_sum
			));
			if (empty($save_result['DrugRequest_id'])) {
				throw new Exception("При сохранении суммы заявочной кампании произошла ошибка");
			}

			$this->commitTransaction();
			$result['success'] = true;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Пересоздание сводной заявки
	 */
	function reCreateConsolidatedDrugRequest() {
		$data = array();
		$error = array();

        //старт транзакции
        $this->beginTransaction();

        //получение данных пересоздаваемой заявки
		$query = "
			select
				DrugRequest_Name,
				replace(replace((
					select cast(DrugRequest_lid as varchar)+',' as 'data()'
					from DrugRequestPurchase with(nolock)
					where DrugRequest_id = DrugRequest.drugrequest_id
					for xml path('')
				)+',,', ',,,', ''), ',,', '') as SelectedRequest_List,
				DrugRequestPeriod_id,
                PersonRegisterType_id,
                DrugGroup_id
			from
				DrugRequest with(nolock)
			where
				DrugRequest_id = :DrugRequest_id;
		";
		$data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $this->DrugRequest_id
        ));
		if (is_array($data)) {
            $data['Server_id'] = $this->Server_id;
            $data['pmUser_id'] = $this->pmUser_id;
		} else {
            $error[] = 'Не удалось получить данные заявки';
        }

		if (count($error) == 0 && isset($data['DrugRequest_Name']) && isset($data['SelectedRequest_List'])) {
			$response = $this->createConsolidatedDrugRequest($data, true);
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        if (count($error) == 0) {
            $response = $this->deleteConsolidatedDrugRequest(array(
                'DrugRequest_id' => $this->DrugRequest_id
            ), true);
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        $result = array();

        if (count($error) > 0) {
            //откат транзакции
            $this->rollbackTransaction();
            $result['Error_Msg'] = $error[0];
        } else {
            //коммит транзакции
            $this->commitTransaction();
            $result['success'] = true;
        }

        return $result;
	}

	/**
	 * Запрет редактирования для группы строк спецификации сводной заявки
	 */
	function closeDrugRequestPurchaseSpec($data) {
		$id_list = preg_replace('/[^0-9,]/', '', $data['Id_List']);
		if (count($id_list)>0) {
			$q = "
				update
					DrugRequestPurchaseSpec
				set
					DrugRequestPurchaseSpec_isOpen = (select top 1 YesNo_id from YesNo with(nolock) where YesNo_Code = 0)
				where
					DrugRequestPurchaseSpec_id in ({$id_list});
			";		
			$r = $this->db->query($q, array());
		}
		$result = array(array('Error_Msg' => ''));

		return $result;
	}

	/**
	 * Снятие запрета редактирования для группы строк спецификации сводной заявки
	 */
	function openDrugRequestPurchaseSpec($data) {
		$id_list = preg_replace('/[^0-9,]/', '', $data['Id_List']);
		if (count($id_list)>0) {
			$q = "
				update
					DrugRequestPurchaseSpec
				set
					DrugRequestPurchaseSpec_isOpen = (select top 1 YesNo_id from YesNo with(nolock) where YesNo_Code = 1)
				where
					DrugRequestPurchaseSpec_id in ({$id_list});
			";
			$r = $this->db->query($q, array());
		}
		$result = array(array('Error_Msg' => ''));

		return $result;
	}

	/**
	 * Загрузка списка МНН для комбо
	 */
	function loadDrugComplexMnnCombo($filter) {
		$where = '';

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $filter['DrugRequest_id']));
		if(!empty($drugRequestProperty['DrugRequestPropertyFed_id']) && !empty($drugRequestProperty['DrugRequestPropertyReg_id'])){
			$drp_where = " (dlr.DrugRequestProperty_id = :DrugRequestPropertyFed_id or dlr.DrugRequestProperty_id = :DrugRequestPropertyReg_id) ";
			$filter['DrugRequestPropertyFed_id'] = $drugRequestProperty['DrugRequestPropertyFed_id'];
			$filter['DrugRequestPropertyReg_id'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
		} else {
			$drp_where = " dlr.DrugRequestProperty_id = :DrugRequestProperty_id ";
			$filter['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		}

		if (!empty($filter['DrugComplexMnn_id'])) {
			$where .= " and dcm.DrugComplexMnn_id = :DrugComplexMnn_id";
		} else {
			if (!empty($filter['query'])) {
				$where .= " and dcmn.DrugComplexMnnName_Name like '%'+:DrugComplexMnnName_Name+'%'";
				$filter['DrugComplexMnnName_Name'] = $filter['query'];
			}
		}
		if(!empty($filter['DrugFinance_id'])){
			$where .= " and drp.DrugFinance_id = :DrugFinance_id";
		}

		$q = "
			select
				dcm.DrugComplexMnn_id,
				cast(cast(isnull(dlr.DrugListRequest_Price, 0) as decimal(12,2)) as varchar(max))  as DrugComplexMnn_Price,
				isnull(isProblem.YesNo_Code, 0) as DrugListRequest_IsProblem,
				dlr.DrugListRequest_Comment,
				dcm.DrugComplexMnn_RusName,
				dcmn.DrugComplexMnnName_Name,
				cdf.NAME as ClsDrugForms_Name,
				dcmd.DrugComplexMnnDose_Name,
				dcmf.DrugComplexMnnFas_Name,
				drp.DrugFinance_id,
				replace(replace((
					select distinct
						ca.NAME+', '
					from
						rls.PREP_ACTMATTERS pam with (nolock)
						left join rls.PREP_ATC pa with (nolock) on pa.PREPID = pam.PREPID
						inner join rls.CLSATC ca with (nolock) on ca.CLSATC_ID = pa.UNIQID
					where
						pam.MATTERID = dcmn.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code
			from
				DrugListRequest dlr with (nolock)
				left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = dlr.DrugComplexMnn_id
				left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
				left join DrugRequestProperty drp with (nolock) on drp.DrugRequestProperty_id = dlr.DrugRequestProperty_id
			where
				{$drp_where}
				{$where};
		";

		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка торговых наименований для комбо
	 */
	function loadTradenamesCombo($filter) {
		$where = '';

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $filter['DrugRequest_id']));
		if(!empty($drugRequestProperty['DrugRequestPropertyFed_id']) && !empty($drugRequestProperty['DrugRequestPropertyReg_id'])){
			$drp_where = " (dlr.DrugRequestProperty_id = :DrugRequestPropertyFed_id or dlr.DrugRequestProperty_id = :DrugRequestPropertyReg_id) ";
			$filter['DrugRequestPropertyFed_id'] = $drugRequestProperty['DrugRequestPropertyFed_id'];
			$filter['DrugRequestPropertyReg_id'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
		} else {
			$drp_where = " dlr.DrugRequestProperty_id = :DrugRequestProperty_id ";
			$filter['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		}

		if (!empty($filter['Tradenames_id'])) {
			$where .= " and tn.TRADENAMES_ID = :Tradenames_id";
		} else {
			if (!empty($filter['query'])) {
				$where .= " and tn.NAME like '%'+:NAME+'%'";
				$filter['NAME'] = $filter['query'];
			}
			if (!empty($filter['DrugComplexMnn_id']) && $filter['DrugComplexMnn_id'] > 0) {
				$where .= " and dlr.DrugComplexMnn_id = :DrugComplexMnn_id";
			}
			if (!empty($filter['DrugRequest_id']) && !empty($filter['fromPersonOrder'])){
				if (!empty($filter['DrugComplexMnn_id']) && $filter['DrugComplexMnn_id'] > 0){
					$where .= "
						and tn.TRADENAMES_ID in 
						(select drr.TRADENAMES_id from v_DrugRequestRow drr with (nolock) where drr.DrugRequest_id = :DrugRequest_id and drr.DrugComplexMnn_id = :DrugComplexMnn_id)
					";
				} else {
					$where .= "
						and tn.TRADENAMES_ID in 
						(select drr.TRADENAMES_id from v_DrugRequestRow drr with (nolock) where drr.DrugRequest_id = :DrugRequest_id)
					";
				}
			}
		}

		$q = "select
				tn.TRADENAMES_ID,
				tn.NAME,
				isnull(isProblem.YesNo_Code, 0) as DrugListRequestTorg_IsProblem,
				dlrt.DrugRequest_Price as Tradenames_Price
			from
				v_DrugListRequest dlr with (nolock)
				left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id
				inner join rls.v_TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = dlrt.TRADENAMES_id
				left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlrt.DrugListRequestTorg_IsProblem
			where
				{$drp_where}
				{$where};
		";

		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка протоколов ВК для комбо
	 */
	function loadProtokolVKCombo($filter) {
		$where = 'vk.Person_id = @Person_id ';

		if (!empty($filter['CauseTreatmentType_id'])) {
			$where .= " and vk.CauseTreatmentType_id = :CauseTreatmentType_id";
		}

		$sql = "
			Declare
				@Person_id bigint = :Person_id;
				
			Select EvnVK_id, Person_id, EvnVK_NumProtocol, convert(varchar, EvnVK_setDate, 104) EvnVK_setDate, 
				'№' + isnull(convert(varchar, EvnVK_NumProtocol), '_') + ' от ' + isnull(convert(varchar, EvnVK_setDate, 104), '') protokolVK_name,
				CauseTreatmentType_id
			from v_EvnVK VK  with (nolock)
			where
				{$where}
			order by EvnVK_setDate desc
				";

		$result = $this->db->query($sql, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МНН для комбо (используется при редактировании спецификации ГК)
	 */
	function loadDrugComplexMnnComboForSupply($filter) {
		$where = '1=1';
		$from = '';
		$select = '';
		if (!empty($filter['query'])) {
			$where .= " and dcm.DrugComplexMnn_RusName like '%'+:DrugComplexMnn_RusName+'%'";
			$filter['DrugComplexMnn_RusName'] = $filter['query'];
		}

		if ($this->getRegionNick() != 'ufa' || !empty($filter['WhsDocumentProcurementRequest_id'])) {
			$select .= "
				wdprs.Drug_id,
				wdprs.Okei_id,
				wdprs.WhsDocumentProcurementRequestSpec_Kolvo as Kolvo,
				wdprs.WhsDocumentProcurementRequestSpec_PriceMax as PriceMax,
				wdprs.Tradenames_id as Tradenames_id,
				tn.NAME as Tradenames_Name,
			";
			$from = "WhsDocumentProcurementRequestSpec wdprs with (nolock)
				left join rls.DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = wdprs.DrugComplexMnn_id
				left join rls.TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = wdprs.Tradenames_id
			";
			$where .= " and wdprs.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id";
		} else {
			if (empty($filter['DrugComplexMnn_RusName'])) {
				return false;
			}
			$select .= "
				null as Drug_id,
				null as Okei_id,
				null as Kolvo,
				null as PriceMax,
				null as Tradenames_id,
				null as Tradenames_Name,
			";
			$from = "rls.DrugComplexMnn dcm with (nolock)";
		}

		$q = "
			select
				{$select}
				dcm.DrugComplexMnn_id,
				dcm.DrugComplexMnn_RusName
			from
				{$from}
			where
				{$where}
			order by
				dcm.DrugComplexMnn_RusName;
		";

		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации ГК)
	 */
	function loadRlsDrugComboForSupply($filter) {
		$where = array();

		if ($filter['Drug_id'] > 0) {
			$where[] = 'Drug.Drug_id = :Drug_id';
		} else {
			//$where[] = '(Drug.Drug_begDate is null or Drug.Drug_begDate <= @date)';
			//$where[] = '(Drug.Drug_endDate is null or Drug.Drug_endDate >= @date)';

			if ($filter['DrugComplexMnn_id'] > 0) {
				$where[] = 'Drug.DrugComplexMnn_id = :DrugComplexMnn_id';
			}
			if ($filter['Tradenames_id'] > 0) {
				$where[] = 'Prep.TRADENAMEID = :Tradenames_id';
			}
			if ($filter['WhsDocumentProcurementRequest_id'] > 0) {
				$query = "
					select
						count(Tradenames_id) as tn_cnt,
						sum(case when Tradenames_id is null then 1 else 0 end) as null_cnt
					from
						v_WhsDocumentProcurementRequestSpec with (nolock)
					where
						WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id;
				";
				$result = $this->getFirstRowFromQuery($query, $filter);
				if ($result['tn_cnt'] > 0 && $result['null_cnt'] == 0) {
					$where[] = 'Prep.TRADENAMEID in (
						select
							wdprs.Tradenames_id
						from
							v_WhsDocumentProcurementRequestSpec wdprs with (nolock)
						where
							wdprs.WhsDocumentProcurementRequest_id = :WhsDocumentProcurementRequest_id
					)';
				}
			}
			if (strlen($filter['query']) > 0) {
				$filter['query'] = '%'.preg_replace('/ /', '%', $filter['query']).'%';
				$where[] = 'Drug.Drug_Name LIKE :query';
			}
		}

		if (count($where) > 0) {
			$query = "
				declare
					@date date;

				set @date = cast(dbo.tzGetDate() as date);

				select top 1000
					Drug.Drug_id,
					Drug.Drug_Name,
					DrugNomen.DrugNomen_Code,
					DrugComplexMnn.DrugComplexMnn_RusName
				from
					rls.v_Drug Drug with (nolock)
					inner join rls.v_DrugPrep DrugPrep with (nolock) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
					inner join rls.v_DrugComplexMnn DrugComplexMnn with (nolock) on DrugComplexMnn.DrugComplexMnn_id = Drug.DrugComplexMnn_id
					left join rls.PREP Prep with (nolock) on Prep.Prep_id = Drug.DrugPrep_id
					outer apply (
						select top 1
							v_DrugNomen.DrugNomen_Code
						from
							rls.v_DrugNomen
						where
							v_DrugNomen.Drug_id = Drug.Drug_id
						order by
							DrugNomen_id
					) DrugNomen
				where
					".join($where, ' and ')."
				order by
					Drug.Drug_Name;
			";

			$result = $this->db->query($query, $filter);
			if ( is_object($result) ) {
				return $result->result('array');
			}
		}

		return false;
	}

	/**
	 * Получение данных о лимитах
	 */
	function getLimitDataForRequestSelectWindow($data) {
		$q = "
			declare
				@FedDrugRequestQuota_Person decimal(10,2) = null,
				@RegDrugRequestQuota_Person decimal(10,2) = null,
				@FedDrugRequestQuota_Reserve decimal(10,2) = null,
				@RegDrugRequestQuota_Reserve decimal(10,2) = null,
				@DrugRequestPlan_FedSumma money = null,
				@DrugRequestPlan_RegSumma money = null;

			select
				@FedDrugRequestQuota_Person = fed.DrugRequestQuota_Person,
				@RegDrugRequestQuota_Person = reg.DrugRequestQuota_Person,
				@FedDrugRequestQuota_Reserve = fed.DrugRequestQuota_Reserve,
				@RegDrugRequestQuota_Reserve = reg.DrugRequestQuota_Reserve
			from
				dbo.v_DrugRequest dr with(nolock)
				left join dbo.DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				left join dbo.DrugRequestQuota fed with (nolock) on fed.PersonRegisterType_id = dr.PersonRegisterType_id and fed.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and fed.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'fed')
				left join dbo.DrugRequestQuota reg with (nolock) on reg.PersonRegisterType_id = dr.PersonRegisterType_id and reg.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and reg.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'reg')
			where
				dr.DrugRequest_Version is null
				and drc.DrugRequestCategory_SysNick = 'region'
				and dr.PersonRegisterType_id = :PersonRegisterType_id
				and dr.DrugRequestPeriod_id = :DrugRequestPeriod_id;

			select top 1
				@DrugRequestPlan_FedSumma = DrugRequestPlan_FedSumma,
				@DrugRequestPlan_RegSumma = DrugRequestPlan_RegSumma
			from
				DrugRequestPlan with(nolock)
			where
				DrugRequest_id in (
					select
						DrugRequest_id
					from
						DrugRequest
					where
						PersonRegisterType_id = :PersonRegisterType_id
						and DrugRequestPeriod_id = :DrugRequestPeriod_id
						and Lpu_id = :Lpu_id
						and MedPersonal_id is null
				)
				and (DrugRequestPlan_FedSumma is not null or DrugRequestPlan_RegSumma is not null)

			select
				@FedDrugRequestQuota_Person as FedDrugRequestQuota_Person,
				@RegDrugRequestQuota_Person as RegDrugRequestQuota_Person,
				@FedDrugRequestQuota_Reserve as FedDrugRequestQuota_Reserve,
				@RegDrugRequestQuota_Reserve as RegDrugRequestQuota_Reserve,
				@DrugRequestPlan_FedSumma as DrugRequestPlan_FedSumma,
				@DrugRequestPlan_RegSumma as DrugRequestPlan_RegSumma;
		";
		$r = $this->db->query($q, array(
				'Lpu_id' => $data['Lpu_id'],
				'PersonRegisterType_id' => $data['PersonRegisterType_id'],
				'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']
		));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получение шаблона для отправки сообщения при манипуляциях с заявками
	 */
	function getTemplateForNotice($event) {
		$res = array(
			'header' => '',
			'text' => ''
		);

		switch ($event) {
			case 'request_set_edit': //Присвоение заявке региона и заявкам МО статуса «Начальная»
				$res['header'] = 'Открыта для редактирования  {DrugRequest_Name}';
				$res['text'] = "Уважаемые пользователи,  открыта для редактирования {DrugRequest_Name}.

					Установлены следующие лимиты по заявке на одного льготника:
					федеральный бюджет - {FedDrugRequestQuota_Person} руб.
					региональный бюджет - {RegDrugRequestQuota_Person} руб.
					Дата закрытия заявки - ________ .
				";
				break;
			case 'mo_request_return_edit': //Возврат заявки МО на редактирование
				$res['header'] = '{Lpu_Name}: возврат на редактирование {DrugRequest_Name}';
				$res['text'] = "Здравствуйте!   {DrugRequest_Name} по {Lpu_Name} возвращена на редактирование по причине:
					_________________
					В срок до ________ прошу внести изменения в вашу заявку ЛЛО в соответствии со
					следующими условиями:
					_________________
				";
				break;
			case 'mo_request_set_formed': //Присвоение заявке МО статуса "Сформированная"
				$res['header'] = '{Lpu_Name}: сформирована {DrugRequest_Name}';
				$res['text'] = "
					Здравствуйте!  Заявка {DrugRequest_Name} по {Lpu_Name} сформирована.

					Сумма заявки:
					федеральный бюджет - {FedSumm_Total} руб.
					региональный бюжет - {RegSumm_Total} руб.
				";
				break;
			case 'mo_request_set_confirmed': //Присвоение заявке МО статуса "Утвержденная"
				$res['header'] = '{Lpu_Name}: Утверждена {DrugRequest_Name} от {Lpu_Name}';
				$res['text'] = "Здравствуйте!  Заявка {DrugRequest_Name} по {Lpu_Name} Утверждена.
				";
				break;
			case 'mp_request_return_edit': //Возврат заявки врача на редактирование
				$res['header'] = '{MedPersonal_Name}: возврат на редактирование {DrugRequest_Name}';
				$res['text'] = "Здравствуйте!

					Ваша {DrugRequest_Name} возвращена на редактирование по причине:
					_________________
					В срок до ________ прошу внести изменения в Вашу заявку в соответствии со следующими условиями:
					_________________
				";
				break;
		}

		// Информация о специалисте
		$res['text'] .= "
			С уважением,
			{User_Name}

			тел. {User_Phone}
			e-mail {User_Email}
		";

		$res['text'] = nl2br($res['text']);

		return $res;
	}

	/**
	 * Получение списка получателей для отправки сообщения
	 */
	function getRecipientForNotice($data) {
		$recipient = array();
		$request_data = array();
		$mp_array = array();

		//Получаем данные о заявке
		$q = "
			select
				PersonRegisterType_id,
				DrugRequestPeriod_id,
				Lpu_id,
				MedPersonal_id
			from
			 	v_DrugRequest with (nolock)
			where
				DrugRequest_id = :DrugRequest_id
		";
		$r = $this->db->query($q, $data);
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$request_data = $r[0];
			}
		}

		//Получаем список врачей
		$mp_query = '';
		switch($data['event']) {
			case 'request_set_edit':
				$mp_query = "
					-- Врачи заяввки ЛЛО
					select distinct
						mp.MedPersonal_id
					from
						DrugRequestLpuGroup drlg with (nolock)
						left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drlg.MedPersonal_id
					where
						drlg.DrugRequestPeriod_id = :DrugRequestPeriod_id and
						drlg.PersonRegisterType_id = :PersonRegisterType_id and
						(:Lpu_id is null or mp.Lpu_id = :Lpu_id) and
						mp.MedPersonal_id is not null
					-- Специалисты ТОУЗ
					union
					select distinct
						MedPersonal_id
					from
						v_MedService ms with (nolock)
						left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
						left join v_MedServiceMedPersonal msmp with (nolock) on msmp.MedService_id = ms.MedService_id
					where
						mst.MedServiceType_SysNick in ('touz', 'leadermo') and
						(msmp.MedServiceMedPersonal_endDT is null or msmp.MedServiceMedPersonal_endDT > dbo.tzGetDate()) and
						msmp.MedPersonal_id is not null
				";
				break;
			//case 'mo_request_return_edit':
			case 'mo_request_set_confirmed':
				$mp_query = "
					-- Врачи заяввки ЛЛО
					select distinct
						MedPersonal_id
					from
						v_DrugRequest with(nolock)
					where
						DrugRequest_Version is null and
						PersonRegisterType_id = :PersonRegisterType_id and
						DrugRequestPeriod_id = :DrugRequestPeriod_id and
						(:Lpu_id is null or Lpu_id = :Lpu_id) and
						MedPersonal_id is not null
					-- Специалисты ТОУЗ
					union
					select distinct
						MedPersonal_id
					from
						v_MedService ms with (nolock)
						left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
						left join v_MedServiceMedPersonal msmp with (nolock) on msmp.MedService_id = ms.MedService_id
					where
						mst.MedServiceType_SysNick in ('touz', 'leadermo') and
						(msmp.MedServiceMedPersonal_endDT is null or msmp.MedServiceMedPersonal_endDT > dbo.tzGetDate()) and
						msmp.MedPersonal_id is not null
				";
				break;
			case 'mo_request_set_formed':
				$mp_query = "
					-- Специалисты ТОУЗ и ЛЛО МЗ
					select distinct
						MedPersonal_id
					from
						v_MedService ms with (nolock)
						left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
						left join v_MedServiceMedPersonal msmp with (nolock) on msmp.MedService_id = ms.MedService_id
					where
						mst.MedServiceType_SysNick in ('touz', 'minzdravdlo') and
						(msmp.MedServiceMedPersonal_endDT is null or msmp.MedServiceMedPersonal_endDT > dbo.tzGetDate()) and
						msmp.MedPersonal_id is not null
				";
				break;
			case 'mp_request_return_edit':
				//Получаем врача заявки
				$mp_array[] = $request_data['MedPersonal_id'];
				break;
		}

		if ($data['event'] == 'mo_request_return_edit') { //возврат заявки МО на редактирование
			$query = "
				select distinct
					pmuc.PMUser_id
				from
					v_pmUserCache pmuc with (nolock)
					left join pmUserCacheGroupLink pmucgl with (nolock) on pmucgl.pmUserCache_id = pmuc.pmUser_id
					left join pmUserCacheGroup pmucg with (nolock) on pmucgl.pmUserCacheGroup_id = pmucg.pmUserCacheGroup_id
				where
					isnull(pmuc.PMUser_Blocked, 0) = 0 and
					(:Lpu_id is null or pmuc.Lpu_id = :Lpu_id) and
					pmuc.MedPersonal_id is not null and 
					(
						pmuc.MedPersonal_id in (
							select
								msmp.MedPersonal_id
							from
								v_MedService ms with (nolock)
								left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
								left join v_MedServiceMedPersonal msmp with (nolock) on msmp.MedService_id = ms.MedService_id
							where
								mst.MedServiceType_SysNick in ('leadermo') and
								(msmp.MedServiceMedPersonal_endDT is null or msmp.MedServiceMedPersonal_endDT > dbo.tzGetDate()) and
								msmp.MedPersonal_id is not null
						) or
						pmucg.pmUserCacheGroup_SysNick = '102' or
						pmucg.pmUserCacheGroup_SysNick = 'ChiefLLO' or
                        pmuc.pmUser_groups like '%\"102\"%' or
                        pmuc.pmUser_groups like '%\"ChiefLLO\"%'
					);					
			";
			$recipient = $this->queryList($query, array(
				'Lpu_id' => $request_data['Lpu_id']
			));
		}

		if (!empty($mp_query)) {
			$r = $this->db->query($mp_query, $request_data);
			if (is_object($r)) {
				$r = $r->result('array');
				for($i = 0; $i < count($r); $i++) {
					$mp_array[] = $r[$i]['MedPersonal_id'];
				}
			}
			$mp_array = array_unique($mp_array);
		}

		//По списку врачей получаем список пользователей
		if (count($mp_array) > 0) {
			$q = "
				select
					PMUser_id
				from
					pmUserCache with (nolock)
				where
					IsNull(PMUser_Blocked,0) = 0 and
					MedPersonal_id in (".join(',', $mp_array).") and
					(:Lpu_id is null or Lpu_id = :Lpu_id)
			";
			$r = $this->db->query($q, array(
				'Lpu_id' => $request_data['Lpu_id']
			));
			if (is_object($r)) {
				$r = $r->result('array');
				for($i = 0; $i < count($r); $i++) {
					$recipient[] = $r[$i]['PMUser_id'];
				}
			}
		}

		return $recipient;
	}

	/**
	 * Получение данных для заполнения шаблона сообщения
	 */
	function getDataForNotice($data) {
		$select = '';
		$join = '';

		switch ($data['event']) {
			case 'request_set_edit':
				$select .= "fed.DrugRequestQuota_Person as FedDrugRequestQuota_Person,";
				$select .= "reg.DrugRequestQuota_Person as RegDrugRequestQuota_Person,";
				$join .= " left join dbo.DrugRequestQuota fed with (nolock) on fed.PersonRegisterType_id = dr.PersonRegisterType_id and fed.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and fed.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'fed') ";
				$join .= " left join dbo.DrugRequestQuota reg with (nolock) on reg.PersonRegisterType_id = dr.PersonRegisterType_id and reg.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and reg.DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with(nolock) where DrugFinance_SysNick = 'reg') ";
				break;
			case 'mo_request_set_formed':
				$select .= "convert(numeric(19,2), drrs.FedSumm_Total) as FedSumm_Total,";
				$select .= "convert(numeric(19,2), drrs.RegSumm_Total) as RegSumm_Total,";
				$join .= " outer apply (
					select
						sum(case when DrugRequestType_Code = 1 then DrugRequestRow_Summa else 0 end) as FedSumm_Total,
						sum(case when DrugRequestType_Code = 2 then DrugRequestRow_Summa else 0 end) as RegSumm_Total
					from
						DrugRequest with (nolock)
						left join v_DrugRequestRow DrugRequestRow with (nolock) on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
						left join DrugRequestType with (nolock) on DrugRequestType.DrugRequestType_id = DrugRequestRow.DrugRequestType_id
					where
						DrugRequest.DrugRequest_Version is null	and
							DrugRequest.PersonRegisterType_id = dr.PersonRegisterType_id and
								DrugRequest.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
									DrugRequest.Lpu_id = dr.Lpu_id and
										DrugRequest.MedPersonal_id is not null and
							DrugRequestRow.DrugRequestRow_id is not null
				) drrs ";
			case 'mo_request_return_edit':
			case 'mo_request_set_confirmed':
				$select .= "lpu.Lpu_Name,";
				$join .= " left join v_Lpu lpu with (nolock) on lpu.Lpu_id = dr.Lpu_id ";
				break;
			case 'mp_request_return_edit':
				$select .= "mp.Person_Fio as MedPersonal_Name,";
				$join .= " left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = dr.MedPersonal_id ";
				break;
		}

		$q = "
			select
				{$select}
				DrugRequest_Name
			from
				dbo.v_DrugRequest dr with(nolock)
				{$join}
			where
				DrugRequest_id = :DrugRequest_id
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных заявок для комбобокса
	 */
	function loadRegionDrugRequestCombo($filter) {
        $region = $_SESSION['region']['nick'];
		$mode = isset($filter['mode']) ? $filter['mode'] : null;
		$select = "";
		$join = "";
		$where = "";

		if ($mode == "with_mo" || ($mode == "with_user_mo" && isset($filter['Lpu_id']) && $filter['Lpu_id'] > 0)) {
			$join .= "
				outer apply (
					select
						count(DrugRequest_id) as cnt
					from
						v_DrugRequest dr2 with (nolock)
					where
						dr2.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(dr2.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(dr2.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						isnull(dr2.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						dr2.Lpu_id is not null and
						".($mode == "with_user_mo" ? "dr2.Lpu_id = :Lpu_id and" : "")."
						dr2.DrugRequest_Version is null and
						dr2.MedPersonal_id is null and
						dr2.DrugRequestStatus_id <> @NullDrugRequestStatus_id
				) as mo_req
			";
			$where .= " and mo_req.cnt > 0";
		}

        if ($filter['show_first_copy']) {
            $select .= "
                ,FirstCopyInf.Inf as FirstCopy_Inf
            ";
            $select .= "
                ,(
                    isnull(FirstCopyInf.Inf+' ', '')+
                    isnull(drs.DrugRequestStatus_Name+' ', '') +
                    isnull(dr.DrugRequest_Name, '')
                ) as DrugRequest_FullName
            ";

            if ($region == 'ufa') {
                $join .= "
                    outer apply (
                        select top 1
                            fc_dr.DrugRequest_id
                        from
                            v_DrugRequest fc_dr with(nolock)
                            left join v_DrugRequestCategory fc_drc with(nolock) on fc_drc.DrugRequestCategory_id = fc_dr.DrugRequestCategory_id
                        where
                            fc_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(fc_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(fc_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            isnull(fc_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            fc_dr.DrugRequest_Version = 1 and
                            fc_drc.DrugRequestCategory_SysNick = 'region' and
                            fc_dr.DrugRequest_id <> dr.DrugRequest_id
                    ) DrugRequestRegionFirstCopy
                ";
                $join .= "
                    outer apply (
                        select
                            (case
                                when dr.DrugRequest_Version = 1 then 'Прогноз ЛО'
                                when DrugRequestRegionFirstCopy.DrugRequest_id is not null then 'Лимит.потр.'
                                else null
                            end) as Inf
                    ) FirstCopyInf
                ";
            } else {
                $join .= "
                    outer apply (
                        select
                            (case
                                when dr.DrugRequest_Version = 1 then 'Копия 1'
                                else null
                            end) as Inf
                    ) FirstCopyInf
                ";
            }
            $where .= " and (dr.DrugRequest_Version = 1 or dr.DrugRequest_Version is null)";
        } else {
            $select .= ", dr.DrugRequest_Name as DrugRequest_FullName";
            $where .= " and dr.DrugRequest_Version is null";
        }

		$query = "
			declare
				@NullDrugRequestStatus_id bigint;

			set @NullDrugRequestStatus_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 4);

			select
				dr.DrugRequest_id,
				dr.DrugRequest_Name,
				dr.DrugRequestPeriod_id,
				drp.DrugRequestPeriod_Name,
				dr.PersonRegisterType_id,
				mt.PersonRegisterType_Name,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id,
				dr.DrugRequest_Version,
				drs.DrugRequestStatus_Code
		        {$select}
			from
				v_DrugRequest dr with (nolock)
				left join v_PersonRegisterType mt with(nolock) on mt.PersonRegisterType_id = dr.PersonRegisterType_id
				inner join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				{$join}
			where
				DrugRequestCategory_id = (
					select top 1
						DrugRequestCategory_id
					from
						v_DrugRequestCategory with(nolock)
					where
						DrugRequestCategory_SysNick = 'region'
				)
				and dr.DrugRequestStatus_id != @NullDrugRequestStatus_id --Статус заявки: не Нулевая
				{$where}
			order by
				dr.DrugRequest_id desc
		";

		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Загрузка списка заявок МО для комбобокса
	 */
	function loadMoDrugRequestCombo($data) {
		$filters = array();
		$params = array();
		$where = "";


		$filters[] = "dr.DrugRequestCategory_id = @DrugRequestCategory_id";
		$filters[] = "dr.DrugRequestStatus_id != @NullDrugRequestStatus_id"; // Статус заявки: не Нулевая

		if (!empty($data['RegionDrugRequest_id']) && $data['RegionDrugRequest_id'] > 0) {
			$query = "
				select
					DrugRequestPeriod_id,
					PersonRegisterType_id,
					DrugRequestKind_id,
					DrugGroup_id
				from
					v_DrugRequest with (nolock)
				where
					DrugRequest_id = :DrugRequest_id;
			";
			$result = $this->getFirstRowFromQuery($query, array('DrugRequest_id' => $data['RegionDrugRequest_id']));

			if (is_array($result)) {
				$filters[] = "dr.DrugRequestPeriod_id = :DrugRequestPeriod_id";
				$filters[] = "isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0)";
				$filters[] = "isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0)";
				$filters[] = "isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0)";

				$params['DrugRequestPeriod_id'] = $result['DrugRequestPeriod_id'];
				$params['PersonRegisterType_id'] = $result['PersonRegisterType_id'];
				$params['DrugRequestKind_id'] = $result['DrugRequestKind_id'];
				$params['DrugGroup_id'] = $result['DrugGroup_id'];
			}
		}

		if (!empty($data['query'])) {
			$filters[] = "dr.DrugRequest_Name like :query";
			$params['query'] = "%".$data['query']."%";
		}

		if (!empty($data['Lpu_id'])) {
			$filters[] = "dr.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
			declare
				@DrugRequestCategory_id bigint,
				@NullDrugRequestStatus_id bigint;

			set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from v_DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'mo');
			set @NullDrugRequestStatus_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 4);

			select
				dr.DrugRequest_id,
				dr.DrugRequest_Name,
				dr.DrugRequestPeriod_id,
				drp.DrugRequestPeriod_Name,
				dr.PersonRegisterType_id,
				mt.PersonRegisterType_Name,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id,
				drs.DrugRequestStatus_Code,
				dr.Lpu_id
			from
				v_DrugRequest dr with (nolock)
				left join v_PersonRegisterType mt with(nolock) on mt.PersonRegisterType_id = dr.PersonRegisterType_id
				left join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			{$where}
			order by
				dr.DrugRequest_id desc
		";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение информации о количестве и сумме "К закупу"
	 */
	function saveDrugRequestRowBuyDataFromJSON($data) {
		$result = $result = array(array('Error_Msg' => null));;

		$dt = (array) json_decode($data['JsonData']);
		$kolvo = 0;
		$sum = 0;
		foreach($dt as $record) {
			if ($record->DrugRequestRow_id > 0) {
				if ($record->state == 'edit') {
					$q = "
						update
							DrugRequestRow
						set
							DrugRequestRow_KolDrugBuy = :DrugRequestRow_KolDrugBuy,
							DrugRequestRow_SumBuy = :DrugRequestRow_SumBuy,
							pmUser_updID = :pmUser_id,
							DrugRequestRow_updDT = dbo.tzGetDate()
						where
							DrugRequestRow_id = :DrugRequestRow_id;
					";
					$r = $this->db->query($q, array(
						'DrugRequestRow_KolDrugBuy' => $record->DrugRequestRow_KolDrugBuy,
						'DrugRequestRow_SumBuy' => $record->DrugRequestRow_SumBuy,
						'pmUser_id' => $this->pmUser_id,
						'DrugRequestRow_id' => $record->DrugRequestRow_id
					));
				}

				$kolvo += $record->DrugRequestRow_KolDrugBuy;
				$sum += $record->DrugRequestRow_SumBuy;
			}
		}

		if ($data['DrugRequestPurchaseSpec_id'] > 0) {
			$q = "
				update
					DrugRequestPurchaseSpec
				set
					DrugRequestPurchaseSpec_Kolvo = :Kolvo,
					DrugRequestPurchaseSpec_Sum = :Sum,
					DrugRequestPurchaseSpec_pKolvo = :Kolvo,
					DrugRequestPurchaseSpec_pSum = :Sum,
					pmUser_updID = :pmUser_id,
					DrugRequestPurchaseSpec_updDT = dbo.tzGetDate()
				where
					DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id;
			";
			$r = $this->db->query($q, array(
				'Kolvo' => $kolvo,
				'Sum' => $sum,
				'pmUser_id' => $this->pmUser_id,
				'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
			));
		}

		return $result;
	}

	/**
	 * Подсчет количества актуальных строк в заявке
	 */
	function getDrugRequestRowCount($filter) {
		$q = "
			select
				count(DrugRequestRow_id) as cnt
			from
				v_DrugRequestRow with(nolock)
			where
				DrugRequest_id = :DrugRequest_id;
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Создание копии заявки
	 */
	function createDrugRequestCopy($data) {
		//отменяем транзакции внутри промежуточных функций
		$data['no_trans'] = true;

		//старт транзакции
		$this->db->trans_begin();

		//создаем список льготников
		$result = $this->createDrugRequestPersonList($data);
		if (!$result || !empty($result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $result;
		}

		//копируем медикаменты
		$result = $this->createDrugRequestDrugCopy($data);
		if (!$result || !empty($result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $result;
		}

		//коммит транзакции
		$this->db->trans_commit();

		return array(array('Error_Msg' => null));
	}

	/**
	 * Создание списка пациентов для заявки
	 */
	function createDrugRequestPersonList($data) {
		//получение данных заявки
		$query= "
			select
				MedPersonal_id,
				PersonRegisterType_id,
				Lpu_id,
				DrugRequestPeriod_id
			from
				v_DrugRequest with (nolock)
			where
				DrugRequest_id = :DrugRequest_id;
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $this->getDrugRequest_id()
		));
		if (count($request_data) == 0) {
			return array(array('Error_Msg' => 'Не удалось получить данные заявки'));
		}

		//получение списка необходимых льготников, спика льготников в строках заявки, списка льготников в заявке, а также получение информации о необходимых действиях над этими списками
		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@MedPersonal_id bigint = :MedPersonal_id,
				@PersonRegisterType_id bigint = :PersonRegisterType_id,
				@PersonRegisterType_SysNick varchar(30),
				@Lpu_id bigint = :Lpu_id,
				@DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
				@DrugRequestPeriod_begDate date,
				@DrugRequestPeriod_endDate date,
				@Current_Date date,
				@Current_Year int;

			set @Current_Date = dbo.tzGetDate();
			set @Current_Year = datepart(year, @Current_Date);
			set @PersonRegisterType_SysNick = (select top 1 PersonRegisterType_SysNick from v_PersonRegisterType with(nolock) where PersonRegisterType_id = @PersonRegisterType_id);
			set @DrugRequestPeriod_begDate = (select top 1 DrugRequestPeriod_begDate from v_DrugRequestPeriod with(nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);
			set @DrugRequestPeriod_endDate = (select top 1 DrugRequestPeriod_endDate from v_DrugRequestPeriod with(nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);

			with request_person as (
				select
					replace(replace((
						select
							cast(DrugRequestRow_id as varchar(max))+','
						from
							DrugRequestRow with(nolock)
						where
							DrugRequest_id = @DrugRequest_id and
							Person_id = pp.Person_id
						for xml path('')
					)+',,', ',,,', ''), ',,', '') as DrugRequestRow_List,
					pp.Person_id
				from
				(
					select distinct
						Person_id
					from
						DrugRequestRow with(nolock)
					where
						DrugRequest_id = @DrugRequest_id
				) pp
			),
			list_person as (
				select
					DrugRequestPerson_id,
					Person_id
				from
					DrugRequestPerson
				where
					PersonRegisterType_id = @PersonRegisterType_id and
					DrugRequestPeriod_id = @DrugRequestPeriod_id and
					Lpu_id = @Lpu_id and
					MedPersonal_id = @MedPersonal_id
			)
			select
				coalesce(p.Person_id, rp.Person_id, lp.Person_id) as Person_id,
				rp.DrugRequestRow_List,
				lp.DrugRequestPerson_id,
				(
					case
						when p.Person_id is null and rp.Person_id is not null then 'delete'
						else null
					end
				) as req_action,
				(
					case
						when p.Person_id is null and lp.Person_id is not null then 'delete'
						when p.Person_id is not null and lp.Person_id is null then 'add'
						else null
					end
				) as list_action
			from (
					select distinct
						priv.Person_id
					from
						PersonPrivilege priv with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = priv.PrivilegeType_id
						left join v_ReceptFinance rf  with (nolock) on rf.ReceptFinance_id = pt.ReceptFinance_id
						outer apply (
							select
								count(EvnRecept_id) as cnt
							from
								v_EvnRecept er with (nolock)
								left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
							where
								er.Person_id = priv.Person_id and
								datediff(day,er.EvnRecept_setDate, @Current_Date) <= 90 and
								wdcit.PersonRegisterType_id = @PersonRegisterType_id and
								wdcit.DrugFinance_id = pt.DrugFinance_id
						) recept
						outer apply (
							select
								count(PersonRefuse_id) as cnt
							from
								v_PersonRefuse pr
								left join v_YesNo yn with(nolock) on yn.YesNo_id = pr.PersonRefuse_IsRefuse
							where
								pr.Person_id = priv.Person_id and
								yn.YesNo_Code = 1 and
								pr.PersonRefuse_Year = @Current_Year
						) is_refuse
					where
						(PersonPrivilege_endDate is null or PersonPrivilege_endDate >= @DrugRequestPeriod_begDate) and
						(PersonPrivilege_begDate is null or PersonPrivilege_begDate <= @DrugRequestPeriod_begDate) and
						(
							:SourceDrugRequest_id is not null or
							priv.Person_id in (
								select
									Person_id
								from
									PersonCard with (nolock)
								where
									LpuRegion_id in (
										select
											msr.LpuRegion_id
										from
											MedStaffRegion msr with (nolock)
											left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
										where
											(
												msr.MedPersonal_id = @MedPersonal_id or
												(
													@MedPersonal_id is null and
													msr.MedPersonal_id in (
														select drlg.MedPersonal_id
														from DrugRequestLpuGroup drlg with(nolock)
															left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drlg.MedPersonal_id
														where
															DrugRequestPeriod_id = @DrugRequestPeriod_id and
															PersonRegisterType_id = @PersonRegisterType_id and
															drlg.MedPersonal_id is not null and
															(@Lpu_id is null or mp.Lpu_id = @Lpu_id)
													)
												)
											) and
											lr.LpuRegion_begDate <= @DrugRequestPeriod_begDate and
											(
												lr.LpuRegion_endDate is null or
												lr.LpuRegion_endDate >= @DrugRequestPeriod_endDate
											)
									)
							)
						) and
						(
							@PersonRegisterType_SysNick = 'common' or
							(@PersonRegisterType_SysNick = 'common_fl' and rf.ReceptFinance_Code = 1 and is_refuse.cnt = 0 ) or
							(@PersonRegisterType_SysNick = 'common_rl' and rf.ReceptFinance_Code = 2) or
							priv.Person_id in (
								select
									Person_id
								from
									v_PersonRegister with (nolock)
								where (
									PersonRegister_disDate is null or
									PersonRegister_disDate > @DrugRequestPeriod_begDate
								)
								and PersonRegisterType_id = @PersonRegisterType_id
							)
						) and
						recept.cnt > 0 and
						(
							:SourceDrugRequest_id is null or
							priv.Person_id in (
								select
									drp1.Person_id
								from
									v_DrugRequest dr1 with (nolock)
									left join v_DrugRequestPerson drp1 with (nolock) on
										drp1.PersonRegisterType_id = dr1.PersonRegisterType_id and
										drp1.DrugRequestPeriod_id = dr1.DrugRequestPeriod_id and
										drp1.Lpu_id = dr1.Lpu_id and
										drp1.MedPersonal_id = dr1.MedPersonal_id
								where
									dr1.DrugRequest_id = :SourceDrugRequest_id
							)
						)
				) p
				full outer join request_person rp with(nolock) on rp.Person_id = p.Person_id
				full outer join list_person lp with(nolock) on lp.Person_id = p.Person_id or lp.Person_id = rp.Person_id
			where
				p.Person_id is not null or
				rp.Person_id is not null or
				lp.Person_id is not null;
		";
		$result = $this->db->query($query, array(
			'DrugRequest_id' => $this->getDrugRequest_id(),
			'MedPersonal_id' => $request_data['MedPersonal_id'],
			'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
			'Lpu_id' => $request_data['Lpu_id'],
			'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
			'SourceDrugRequest_id' => isset($data['SourceDrugRequest_id']) && $data['SourceDrugRequest_id'] > 0 ? $data['SourceDrugRequest_id'] : null
		));
		if ( is_object($result) ) {
			$person_arr = $result->result('array');

			//старт транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_begin();
			}
			foreach($person_arr as $person) {
				//обработка списка пациентов
				if ($person['list_action'] == 'add') {
					$query = "
						declare
							@DrugRequestPerson_id bigint,
							@Error_Code bigint,
							@Error_Message varchar(4000);

						exec dbo.p_DrugRequestPerson_ins
							@Server_id = :Server_id,
							@DrugRequestPerson_id = @DrugRequestPerson_id output,
							@DrugRequestPeriod_id = :DrugRequestPeriod_id,
							@Person_id = :Person_id,
							@Lpu_id = :Lpu_id,
							@MedPersonal_id = :MedPersonal_id,
							@PersonRegisterType_id = :PersonRegisterType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";
					$result = $this->getFirstRowFromQuery($query, array(
						'Server_id' => $data['Server_id'],
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'Person_id' => $person['Person_id'],
						'Lpu_id' => $request_data['Lpu_id'],
						'MedPersonal_id' => $request_data['MedPersonal_id'],
						'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($result['Error_Msg'])) {
						if (!isset($data['no_trans'])) {
							$this->db->trans_rollback();
						}
						return array($result);
					}
				}
				if ($person['list_action'] == 'delete' && $person['DrugRequestPerson_id'] > 0) {
					$query = "
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000);

						exec dbo.p_DrugRequestPerson_del
							@DrugRequestPerson_id = :DrugRequestPerson_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";
					$result = $this->getFirstRowFromQuery($query, array(
						'DrugRequestPerson_id' => $person['DrugRequestPerson_id']
					));
					if (!empty($result['Error_Msg'])) {
						if (!isset($data['no_trans'])) {
							$this->db->trans_rollback();
						}
						return array($result);
					}
				}

				//удаление лишних строк заявки
				if ($person['req_action'] == 'delete') {
					$row_arr = explode(',', $person['DrugRequestRow_List']);
					foreach($row_arr as $row_id) {
						$query = "
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000);

							exec dbo.p_DrugRequestRow_del
								@DrugRequestRow_id = :DrugRequestRow_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						$result = $this->getFirstRowFromQuery($query, array(
							'DrugRequestRow_id' => $row_id
						));
						if (!empty($result['Error_Msg'])) {
							if (!isset($data['no_trans'])) {
								$this->db->trans_rollback();
							}
							return array($result);
						}
					}
				}
			}

			//коммит транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_commit();
			}
		}
		return array(array('Error_Msg' => null));
	}

	/**
	 * Создание списка пациентов для заявки (новая версия заявки с разнарядками по пациентам)
	 */
	function createMzDrugRequestPersonList($data) {
		//получение данных заявки
		$query= "
			select
				MedPersonal_id,
				PersonRegisterType_id,
				Lpu_id,
				DrugRequestPeriod_id
			from
				v_DrugRequest with (nolock)
			where
				DrugRequest_id = :DrugRequest_id;
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $this->getDrugRequest_id()
		));
		if (count($request_data) == 0) {
			return array(array('Error_Msg' => 'Не удалось получить данные заявки'));
		}

		//получение списка необходимых льготников, спика льготников в строках заявки, списка льготников в заявке, а также получение информации о необходимых действиях над этими списками
		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@MedPersonal_id bigint = :MedPersonal_id,
				@PersonRegisterType_id bigint = :PersonRegisterType_id,
				@PersonRegisterType_SysNick varchar(30),
				@Lpu_id bigint = :Lpu_id,
				@DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
				@DrugRequestPeriod_begDate date,
				@DrugRequestPeriod_endDate date,
				@Current_Date date,
				@Current_Year int;

			set @Current_Date = dbo.tzGetDate();
			set @Current_Year = datepart(year, @Current_Date);
			set @PersonRegisterType_SysNick = (select top 1 PersonRegisterType_SysNick from v_PersonRegisterType with(nolock) where PersonRegisterType_id = @PersonRegisterType_id);
			set @DrugRequestPeriod_begDate = (select top 1 DrugRequestPeriod_begDate from v_DrugRequestPeriod with(nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);
			set @DrugRequestPeriod_endDate = (select top 1 DrugRequestPeriod_endDate from v_DrugRequestPeriod with(nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);

			with order_person as (
                select
                    drpo.Person_id
                from
                    v_DrugRequestPersonOrder drpo with (nolock)
                where
                    drpo.DrugRequest_id = @DrugRequest_id and
                    drpo.DrugComplexMnn_id is null and
                    drpo.Tradenames_id is null
            )
            select
                p.Person_id as Person_id
            from (
                select distinct
                    priv.Person_id
                from
                    PersonPrivilege priv with (nolock)
                    left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = priv.PrivilegeType_id
                    left join v_ReceptFinance rf  with (nolock) on rf.ReceptFinance_id = pt.ReceptFinance_id
                    outer apply (
                        select
                            count(EvnRecept_id) as cnt
                        from
                            v_EvnRecept er with (nolock)
                            left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
                        where
                            er.Person_id = priv.Person_id and
                            datediff(day,er.EvnRecept_setDate, @Current_Date) <= 365 and
                            wdcit.PersonRegisterType_id = @PersonRegisterType_id and
                            wdcit.DrugFinance_id = pt.DrugFinance_id
                    ) recept
                    outer apply (
                        select
                            count(PersonRefuse_id) as cnt
                        from
                            v_PersonRefuse pr
                            left join v_YesNo yn with(nolock) on yn.YesNo_id = pr.PersonRefuse_IsRefuse
                        where
                            pr.Person_id = priv.Person_id and
                            yn.YesNo_Code = 1 and
                            pr.PersonRefuse_Year = @Current_Year
                    ) is_refuse
                where
                    (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= @DrugRequestPeriod_begDate) and
                    (PersonPrivilege_begDate is null or PersonPrivilege_begDate <= @DrugRequestPeriod_begDate) and
                    (
                        priv.Person_id in (
                            select
                                Person_id
                            from
                                PersonCard with (nolock)
                            where
                                LpuRegion_id in (
                                    select
                                        msr.LpuRegion_id
                                    from
                                        MedStaffRegion msr with (nolock)
                                        left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
                                    where
                                        (
                                            msr.MedPersonal_id = @MedPersonal_id or
                                            (
                                                @MedPersonal_id is null and
                                                msr.MedPersonal_id in (
                                                    select drlg.MedPersonal_id
                                                    from DrugRequestLpuGroup drlg with(nolock)
                                                        left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drlg.MedPersonal_id
                                                    where
                                                        DrugRequestPeriod_id = @DrugRequestPeriod_id and
                                                        PersonRegisterType_id = @PersonRegisterType_id and
                                                        drlg.MedPersonal_id is not null and
                                                        (@Lpu_id is null or mp.Lpu_id = @Lpu_id)
                                                )
                                            )
                                        ) and
                                        lr.LpuRegion_begDate <= @DrugRequestPeriod_begDate and
                                        (
                                            lr.LpuRegion_endDate is null or
                                            lr.LpuRegion_endDate >= @DrugRequestPeriod_endDate
                                        )
                                )
                        )
                    ) and
                    (
                        @PersonRegisterType_SysNick = 'common' or
                        (@PersonRegisterType_SysNick = 'common_fl' and rf.ReceptFinance_Code = 1 and is_refuse.cnt = 0 ) or
                        (@PersonRegisterType_SysNick = 'common_rl' and rf.ReceptFinance_Code = 2) or
                        priv.Person_id in (
                            select
                                Person_id
                            from
                                v_PersonRegister with (nolock)
                            where (
                                PersonRegister_disDate is null or
                                PersonRegister_disDate > @DrugRequestPeriod_begDate
                            )
                            and PersonRegisterType_id = @PersonRegisterType_id
                        )
                    ) and
                    recept.cnt > 0
                ) p
                left join order_person op on op.Person_id = p.Person_id
            where
                p.Person_id is not null and
                op.Person_id is null;
		";
		$result = $this->db->query($query, array(
			'DrugRequest_id' => $this->getDrugRequest_id(),
			'MedPersonal_id' => $request_data['MedPersonal_id'],
			'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
			'Lpu_id' => $request_data['Lpu_id'],
			'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id']
        ));

		if ( is_object($result) ) {
			$person_arr = $result->result('array');

			//старт транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_begin();
			}

            //обработка списка пациентов
			foreach($person_arr as $person) {
                $query = "
                    declare
                        @DrugRequestPersonOrder_id bigint,
                        @Error_Code bigint,
                        @Error_Message varchar(4000);

                    exec dbo.p_DrugRequestPersonOrder_ins
                        @DrugRequestPersonOrder_id = @DrugRequestPersonOrder_id output,
                        @DrugRequest_id = :DrugRequest_id,
                        @Person_id = :Person_id,
                        @MedPersonal_id = :MedPersonal_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @Error_Code output,
                        @Error_Message = @Error_Message output;

                    select @Error_Code as Error_Code, @Error_Message as Error_Msg;
                ";
                $result = $this->getFirstRowFromQuery($query, array(
                    'DrugRequest_id' => $this->getDrugRequest_id(),
                    'Person_id' => $person['Person_id'],
                    'MedPersonal_id' => $request_data['MedPersonal_id'],
                    'pmUser_id' => $data['pmUser_id'],
                ));
                if (!empty($result['Error_Msg'])) {
                    if (!isset($data['no_trans'])) {
                        $this->db->trans_rollback();
                    }
                    return array($result);
                }
			}

			//коммит транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_commit();
			}
		}
		return array(array('Error_Msg' => null));
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую
	 */
	function createDrugRequestDrugCopy($data) {
		$row_arr = array();
		$query = "
			declare
				@PersonRegisterType_id bigint,
				@DrugRequestPeriod_id bigint,
				@Lpu_id bigint,
				@MedPersonal_id bigint,
				@DrugRequestProperty_id bigint,
				@Current_Date date;

			set @Current_Date = dbo.tzGetDate();

			select
				@PersonRegisterType_id = dr.PersonRegisterType_id,
				@DrugRequestPeriod_id = dr.DrugRequestPeriod_id,
				@Lpu_id = dr.Lpu_id,
				@MedPersonal_id = dr.MedPersonal_id,
				@DrugRequestProperty_id = dr_region.DrugRequestProperty_id
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequest dr_region with(nolock) on
					dr_region.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
					isnull(dr_region.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
					isnull(dr_region.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
					isnull(dr_region.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
					dr_region.DrugRequest_Version is null and
					dr_region.DrugRequestCategory_id = (select DrugRequestCategory_id from DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'region')
			where
				dr.DrugRequest_id = :DrugRequest_id;


			with person_list as (
				select
					Person_id
				from
					v_DrugRequestPerson with(nolock)
				where
					PersonRegisterType_id = @PersonRegisterType_id and
					DrugRequestPeriod_id = @DrugRequestPeriod_id and
					Lpu_id = @Lpu_id and
					MedPersonal_id = @MedPersonal_id
			)
			select
				drr.DrugRequestRow_id,
				dlr.Price,
				'copy' as action
			from
				v_DrugRequestRow drr with (nolock)
				left join v_DrugRequestRow current_drr with (nolock) on
					current_drr.DrugRequest_id = :DrugRequest_id and
					isnull(current_drr.Person_id, 0) = isnull(drr.Person_id, 0) and
					current_drr.DrugComplexMnn_id = drr.DrugComplexMnn_id
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
				outer apply (
					select top 1
						DrugListRequest_Price as Price
					from
						v_DrugListRequest
					where
						DrugComplexMnn_id = drr.DrugComplexMnn_id and
						DrugRequestProperty_id = @DrugRequestProperty_id
					order by
						DrugListRequest_insDT desc
				) dlr
				outer apply (
					select
						count(EvnRecept_id) as cnt
					from
						v_EvnRecept er with (nolock)
						left join v_WhsDocumentCostItemType wdcit with(nolock) on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
					where
						er.Person_id = drr.Person_id and
						datediff(day,er.EvnRecept_setDate, @Current_Date) <= 90 and
						wdcit.PersonRegisterType_id = @PersonRegisterType_id and
						wdcit.DrugFinance_id = drr.DrugFinance_id
				) recept
			where
				current_drr.DrugRequestRow_id is null and
				dlr.Price is not null and
				(
					drr.Person_id is null or
					(
						drr.Person_id in (select Person_id from person_list with(nolock)) and
						recept.cnt > 0
					)
				) and
				drr.DrugRequest_id = :SourceDrugRequest_id;
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$row_arr = $result->result('array');
		} else {
			return array(array('Error_Msg' => 'При получении списка строк заявки произошла ошибка.'));
		}

		//старт транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_begin();
		}

		foreach($row_arr as $row) {
			$query = "";
			if ($row['action'] == 'delete') {
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);

					exec dbo.p_DrugRequestRow_del
						@DrugRequestRow_id = :DrugRequestRow_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			}
			if ($row['action'] == 'copy') {
				$query = "
					declare
						@DrugRequestRow_id bigint,
						@DrugRequestType_id bigint,
						@Person_id bigint,
						@DrugProtoMnn_id bigint,
						@DrugRequestRow_Kolvo float,
						@DrugRequestRow_Summa money,
						@Drug_id bigint,
						@DrugRequestRow_KolvoUe numeric(18,6),
						@DrugRequestRow_RashUe numeric(18,6),
						@ACTMATTERS_ID bigint,
						@DrugRequestRow_DoseOnce varchar(30),
						@Okei_oid bigint,
						@DrugRequestRow_DoseDay varchar(30),
						@Okei_did bigint,
						@DrugRequestRow_DoseCource varchar(30),
						@Okei_cid bigint,
						@DrugComplexMnn_id bigint,
						@TRADENAMES_id int,
						@DrugFinance_id bigint,
						@DrugRequestRow_KolDrugBuy numeric(18,2),
						@DrugRequestRow_SumBuy money,
						@Error_Code int,
						@Error_Message varchar(4000),
						@Price float = :Price;

					select
						@DrugRequestType_id = DrugRequestType_id,
						@Person_id = Person_id,
						@DrugProtoMnn_id = DrugProtoMnn_id,
						@DrugRequestRow_Kolvo = DrugRequestRow_Kolvo,
						@DrugRequestRow_Summa = isnull(DrugRequestRow_Kolvo*@Price,0),
						@Drug_id = Drug_id,
						@DrugRequestRow_KolvoUe = DrugRequestRow_KolvoUe,
						@DrugRequestRow_RashUe = DrugRequestRow_RashUe,
						@ACTMATTERS_ID = ACTMATTERS_ID,
						@DrugRequestRow_DoseOnce = DrugRequestRow_DoseOnce,
						@Okei_oid = Okei_oid,
						@DrugRequestRow_DoseDay = DrugRequestRow_DoseDay,
						@Okei_did = Okei_did,
						@DrugRequestRow_DoseCource = DrugRequestRow_DoseCource,
						@Okei_cid = Okei_cid,
						@DrugComplexMnn_id = DrugComplexMnn_id,
						@TRADENAMES_id = TRADENAMES_id,
						@DrugFinance_id = DrugFinance_id
					from
						v_DrugRequestRow with(nolock)
					where
						DrugRequestRow_id = :DrugRequestRow_id;

					execute dbo.p_DrugRequestRow_ins
						@DrugRequestRow_id = @DrugRequestRow_id output,
						@DrugRequest_id = :DrugRequest_id,
						@DrugRequestType_id = @DrugRequestType_id,
						@Person_id = @Person_id,
						@DrugProtoMnn_id = @DrugProtoMnn_id,
						@DrugRequestRow_Kolvo = @DrugRequestRow_Kolvo,
						@DrugRequestRow_Summa = @DrugRequestRow_Summa,
						@Drug_id = @Drug_id,
						@DrugRequestRow_KolvoUe = @DrugRequestRow_KolvoUe,
						@DrugRequestRow_RashUe = @DrugRequestRow_RashUe,
						@ACTMATTERS_ID = @ACTMATTERS_ID,
						@DrugRequestRow_DoseOnce = @DrugRequestRow_DoseOnce,
						@Okei_oid = @Okei_oid,
						@DrugRequestRow_DoseDay = @DrugRequestRow_DoseDay,
						@Okei_did = @Okei_did,
						@DrugRequestRow_DoseCource = @DrugRequestRow_DoseCource,
						@Okei_cid = @Okei_cid,
						@DrugComplexMnn_id = @DrugComplexMnn_id,
						@TRADENAMES_id = @TRADENAMES_id,
						@DrugFinance_id = @DrugFinance_id,
						@DrugRequestRow_KolDrugBuy = @DrugRequestRow_Kolvo,
						@DrugRequestRow_SumBuy = @DrugRequestRow_Summa,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @DrugRequestRow_id as DrugRequestRow_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			}

			$result = $this->getFirstRowFromQuery($query, array(
				'DrugRequestRow_id' => $row['DrugRequestRow_id'],
				'DrugRequest_id' => $data['DrugRequest_id'],
				'Price' => $row['Price'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!empty($result['Error_Msg'])) {
				if (!isset($data['no_trans'])) {
					$this->db->trans_rollback();
				}
				return array($result);
			}
		}

		//коммит транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_commit();
		}

		return array(array('Error_Msg' => null));
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую (новая версия заявки с разнарядками по пациентам)
	 */
	function createMzDrugRequestDrugCopy($data) {
		$row_arr = array();
		$query = "
			declare
				@PersonRegisterType_id bigint,
				@DrugRequestPeriod_id bigint,
				@Lpu_id bigint,
				@MedPersonal_id bigint,
				@DrugRequestProperty_id bigint,
				@Current_Date date;

			set @Current_Date = dbo.tzGetDate();

			select
				@PersonRegisterType_id = dr.PersonRegisterType_id,
				@DrugRequestPeriod_id = dr.DrugRequestPeriod_id,
				@Lpu_id = dr.Lpu_id,
				@MedPersonal_id = dr.MedPersonal_id,
				@DrugRequestProperty_id = dr_region.DrugRequestProperty_id
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequest dr_region on
					dr_region.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
					isnull(dr_region.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
					isnull(dr_region.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
					isnull(dr_region.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
					dr_region.DrugRequest_Version is null and
					dr_region.DrugRequestCategory_id = (select DrugRequestCategory_id from DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'region')
			where
				dr.DrugRequest_id = :DrugRequest_id;

			select
				(case
				    when current_drr.DrugRequestRow_id is not null then current_drr.DrugRequestRow_id
				    else drr.DrugRequestRow_id
				end) as DrugRequestRow_id,
				(case
				    when current_drr.DrugRequestRow_id is not null then isnull(drr.DrugRequestRow_Kolvo, 0) + isnull(current_drr.DrugRequestRow_Kolvo, 0)
				    else drr.DrugRequestRow_Kolvo
				end) as DrugRequestRow_Kolvo,
				dlr.Price,
				(case
				    when current_drr.DrugRequestRow_id is not null then 'edit'
				    else 'copy'
				end) as action
			from
				v_DrugRequestRow drr with (nolock)
				left join v_DrugRequestRow current_drr with (nolock) on
					current_drr.DrugRequest_id = :DrugRequest_id and
					drr.Person_id is null and
					current_drr.DrugComplexMnn_id = drr.DrugComplexMnn_id and
					isnull(current_drr.TRADENAMES_id, 0) = isnull(drr.TRADENAMES_id, 0)
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
				outer apply (
					select top 1
						(case
							when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
							else i_dlr.DrugListRequest_Price
						end) as Price
					from
						v_DrugListRequest i_dlr with (nolock)
						left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drr.TRADENAMES_id
					where
						i_dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id and
						i_dlr.DrugRequestProperty_id = @DrugRequestProperty_id
					order by
						DrugListRequest_insDT desc
				) dlr
			where
				dlr.Price is not null and
				drr.Person_id is null and
				drr.DrugRequest_id = :SourceDrugRequest_id;
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$row_arr = $result->result('array');
		} else {
			return array(array('Error_Msg' => 'При получении списка строк заявки произошла ошибка.'));
		}

		//старт транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_begin();
		}

		foreach($row_arr as $row) {
			if ($row['action'] == 'edit') {
                $result = $this->saveObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $row['DrugRequestRow_id'],
                    'DrugRequestRow_Kolvo' => $row['DrugRequestRow_Kolvo'],
                    'DrugRequestRow_Summa' => $row['Price'] > 0 ? $row['DrugRequestRow_Kolvo']*$row['Price'] : null,
                    'pmUser_id' => $data['pmUser_id'],
                ));
			}

			if ($row['action'] == 'copy') {
                $result = $this->copyObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $row['DrugRequestRow_id'],
                    'DrugRequest_id' => $data['DrugRequest_id'],
                    'DrugRequestRow_Kolvo' => $row['DrugRequestRow_Kolvo'],
                    'DrugRequestRow_Summa' => $row['Price'] > 0 ? $row['DrugRequestRow_Kolvo']*$row['Price'] : null,
                    'pmUser_id' => $data['pmUser_id'],
                ));
			}

            if (!empty($result['Error_Msg'])) {
                if (!isset($data['no_trans'])) {
                    $this->db->trans_rollback();
                }
                return array($result);
            }
		}

		//коммит транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_commit();
		}

		return array(array('Error_Msg' => null));
	}

	/**
	 * Загрузка списка заявок для комбобокса (копирование заявок)
	 */
	function loadSourceDrugRequestCombo($filter) {
		$query = "
			declare
				@PersonRegisterType_id bigint,
				@DrugRequestCategory_id bigint,
				@DrugGroup_id bigint,
				@DrugRequestPeriod_begDate date,
				@MedPersonal_id bigint,
				@Lpu_id bigint,
				@Year int;

			select
				@PersonRegisterType_id = dr.PersonRegisterType_id,
				@DrugRequestCategory_id = dr.DrugRequestCategory_id,
				@DrugGroup_id = dr.DrugGroup_id,
				@DrugRequestPeriod_begDate = drp.DrugRequestPeriod_begDate,
				@MedPersonal_id = MedPersonal_id,
				@Lpu_id = Lpu_id
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
			where
				DrugRequest_id = :DrugRequest_id;

			select
				dr.DrugRequest_id,
				(dr.DrugRequest_Name + isnull(' по ' + mt.PersonRegisterType_Name, '') + ' на ' + drp.DrugRequestPeriod_Name) as DrugRequest_Name,
				summ.total as DrugRequest_Sum,
				drs.DrugRequestStatus_Name
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				left join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_PersonRegisterType mt with(nolock) on mt.PersonRegisterType_id = dr.PersonRegisterType_id
				outer apply (
					select
						sum(DrugRequestRow_Summa) as total
					from
						v_DrugRequestRow with (nolock)
					where
						DrugRequest_id = dr.DrugRequest_id
				) summ
			where
				dr.DrugRequest_id <> :DrugRequest_id and
				isnull(dr.DrugRequestCategory_id, 0) = isnull(@DrugRequestCategory_id, 0) and
				isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
				isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
				drp.DrugRequestPeriod_begDate <= @DrugRequestPeriod_begDate and
				datediff(year, DrugRequestPeriod_begDate, @DrugRequestPeriod_begDate) <= 2 and
				dr.MedPersonal_id = @MedPersonal_id and
				dr.Lpu_id = @Lpu_id and
				DrugRequest_Version is null and
				summ.total > 0
			order by
				summ.total desc
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение заявки МО по заявке врача
	 */
	function getMoRequestByMpRequest() {
		$category_id = $this->getObjectIdByCode('DrugRequestCategory', 2); // 2 - Заявка МО

		$query= "
			select top 1
				modr.DrugRequest_id,
				drs.DrugRequestStatus_Code
			from
				v_DrugRequest mpdr with (nolock)
				left join v_DrugRequest modr with (nolock) on
					modr.DrugRequestPeriod_id = mpdr.DrugRequestPeriod_id and
					isnull(modr.PersonRegisterType_id, 0) = isnull(mpdr.PersonRegisterType_id, 0) and
					isnull(modr.DrugRequestKind_id, 0) = isnull(mpdr.DrugRequestKind_id, 0) and
					isnull(modr.DrugGroup_id, 0) = isnull(mpdr.DrugGroup_id, 0) and
					modr.Lpu_id = mpdr.Lpu_id and
					modr.DrugRequest_Version is null and
					modr.DrugRequestCategory_id = :DrugRequestCategory_id
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = modr.DrugRequestStatus_id
			where
				mpdr.DrugRequest_id = :DrugRequest_id;
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $this->getDrugRequest_id(),
			'DrugRequestCategory_id' => $category_id
		));
		if (count($request_data) == 0 || !isset($request_data['DrugRequest_id'])) {
			return array('Error_Msg' => 'Не удалось получить данные заявки');
		} else {
			return $request_data;
		}
	}

	/**
	 * Получение статуса заявки МО по параметрам
	 */
	function getMoRequestStatusByParams($data) {
		$query = "
			select top 1
				dr.DrugRequest_id,
				drs.DrugRequestStatus_Code,
				drs.DrugRequestStatus_Name
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			where
				dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
				isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
				isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
				isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
				dr.Lpu_id = :Lpu_id and
				isnull(dr.DrugRequest_Version, 0) = isnull(:DrugRequest_Version, 0) and
				dr.DrugRequestCategory_id in (
					select
						DrugRequestCategory_id
					from
						v_DrugRequestCategory with(nolock)
					where
						DrugRequestCategory_SysNick = 'mo'
				);
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
			'PersonRegisterType_id' => $data['PersonRegisterType_id'],
			'DrugRequestKind_id' => $data['DrugRequestKind_id'],
			'DrugRequest_Version' => $data['DrugRequest_Version'],
			'DrugGroup_id' => $data['DrugGroup_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		return array($request_data);
	}

	/**
	 * Сохранение количества в строке заявки
	 */
	function saveDrugRequestRowKolvo($data) {
		//ищем существующую строку
		$query = "
			select top 1
				DrugRequestRow_id
			from
				v_DrugRequestRow with(nolock)
			where
				DrugRequest_id = :DrugRequest_id and
				Person_id is null and
				DrugComplexMnn_id = :DrugComplexMnn_id and
				isnull(TRADENAMES_id, 0) = isnull(:TRADENAMES_id, 0)
			order by
				DrugRequestRow_id;
		";
		$row_id = $this->getFirstResultFromQuery($query, $data);

		if ($row_id > 0) {
			$data['DrugRequestRow_id'] = $row_id;
		}

		//сохраняем или удаляем данные
		if ($data['DrugRequestRow_Kolvo'] > 0) {
			$data['DrugRequestRow_Summa'] = $data['DrugRequestRow_Price'] > 0 ? $data['DrugRequestRow_Kolvo']*$data['DrugRequestRow_Price'] : null;
			$result = $this->saveObject('DrugRequestRow', $data);
			if (is_array($result) && !empty($result['DrugRequestRow_id'])) {
				$row_id = $result['DrugRequestRow_id'];
			}
		} else if ($row_id > 0) {
			$result = $this->deleteObject('DrugRequestRow', array(
				'DrugRequestRow_id' => $row_id
			));
			$row_id = null;
		}

		$kolvo = null;
		if ($row_id > 0) {
			$query = "
				select
					DrugRequestRow_Kolvo
				from
					v_DrugRequestRow with(nolock)
				where
					DrugRequestRow_id = :DrugRequestRow_id;
			";
			$kolvo = $this->getFirstResultFromQuery($query, array(
				'DrugRequestRow_id' => $row_id
			));
		}

		$result = array(array(
			'DrugRequestRow_id' => $row_id > 0 ? $row_id : null,
			'DrugRequestRow_Kolvo' => $kolvo > 0 ? $kolvo : null,
			'Error_Code' => null,
			'Error_Msg' => null
		));
		return $result;
	}

	/**
	 * Сохранение дозировок
	 */
	function saveDrugRequestRowDose($data) {
		$query = "
			update
				DrugRequestRow
			set
				Okei_oid = :Okei_oid,
				DrugRequestRow_DoseOnce = :DrugRequestRow_DoseOnce,
				DrugRequestRow_DoseDay = :DrugRequestRow_DoseDay,
				DrugRequestRow_DoseCource = :DrugRequestRow_DoseCource
			where
				DrugRequestRow_id = :DrugRequestRow_id;

			select :DrugRequestRow_id as DrugRequestRow_id, null as Error_Code, null as Error_Msg;
		";
		$r = $this->db->query($query, $data);
		$result = array(array(
			'DrugRequestRow_id' => $data['DrugRequestRow_id'],
			'Error_Code' => null,
			'Error_Msg' => null
		));
		return $result;
	}

	/**
	 * Сохранение строки персональной разнарядки
	 */
	function saveDrugRequestPersonOrder($data) {
        $result = array(
            'success' => false,
            'DrugRequestPersonOrder_id' => null,
            'Error_Code' => null,
            'Error_Msg' => null
        );

        try {
            $this->beginTransaction();

            //получение данных заявки
            $query = "
                select
                    dr.DrugRequest_id,
                    dr.DrugRequestPeriod_id,
                    dr.PersonRegisterType_id,
                    dr.DrugRequestKind_id,
                    dr.DrugGroup_id,
                    dr.DrugRequestCategory_id,
                    drs.DrugRequestStatus_Code,
                    dr.Lpu_id,
                    lr.LpuRegion_id,
                    lrt.LpuRegionType_id,
                    lrt.LpuRegionType_SysNick
                from
                    v_DrugRequest dr with (nolock)
                    left join v_DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                    left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = dr.LpuRegion_id
                    left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
                where
                    dr.DrugRequest_id = :DrugRequest_id;
            ";
            $dr_data = $this->getFirstRowFromQuery($query, array(
                'DrugRequest_id' => $data['DrugRequest_id']
            ));
            if (empty($dr_data['DrugRequest_id'])) {
                throw new Exception("При получении данных заявки произошла ошибка");
            }

			//для заявок участков необходимо проверить уникальность пациента в рамках участковых заявок
            if (!empty($dr_data['LpuRegion_id']) && $dr_data['DrugRequestStatus_Code'] == '1') { //1 - Начальная
				$main_type_list = array('ter', 'ped', 'vop', 'op'); //список системных ников типов участков с основным типом прикрепления: Терапевтический, Педиатрический, Врач общей практики, Общей практики

				if (in_array($dr_data['LpuRegionType_SysNick'], $main_type_list)) { //участок с основным типом прикрепления
					$query = "					
						declare
							@cur_date date;
	
						set @cur_date = dbo.tzGetDate();
						
						select top 1
							dr.DrugRequest_id,
							l.Lpu_Name,
							lr.LpuRegion_Name,
							mp.Person_Fio as MedPersonal_Fio
						from
							v_DrugRequest dr with (nolock)
							left join v_DrugRequestPersonOrder drpo with (nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
							left join v_Lpu l with (nolock) on l.Lpu_id = dr.Lpu_id
							left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = dr.LpuRegion_id
							left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
							left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = dr.MedPersonal_id and mp.Lpu_id = dr.Lpu_id
						where
							dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
							isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
							isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
							isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
							dr.DrugRequestCategory_id = :DrugRequestCategory_id and
							dr.DrugRequest_Version is null and 
							dr.DrugRequest_id <> :DrugRequest_id and
							dr.LpuRegion_id is not null and 
							drpo.Person_id = :Person_id and
							lrt.LpuRegionType_SysNick in ('".join("', '", $main_type_list)."')
						order by
							drpo.DrugRequestPersonOrder_id;
					";
				} else { //заявки с другими типами участка
					$query = "
						declare
							@cur_date date;
	
						set @cur_date = dbo.tzGetDate();
						
						select top 1
							dr.DrugRequest_id,
							l.Lpu_Name,
							lr.LpuRegion_Name,
							mp.Person_Fio as MedPersonal_Fio
						from
							v_DrugRequest dr with (nolock)
							left join v_DrugRequestPersonOrder drpo with (nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
							left join v_Lpu l with (nolock) on l.Lpu_id = dr.Lpu_id
							left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = dr.LpuRegion_id
							left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
							left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = dr.MedPersonal_id and mp.Lpu_id = dr.Lpu_id
						where
							dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
							isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
							isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
							isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
							dr.DrugRequestCategory_id = :DrugRequestCategory_id and
							dr.DrugRequest_Version is null and 
							dr.DrugRequest_id <> :DrugRequest_id and
							dr.Lpu_id = :Lpu_id and 
							dr.LpuRegion_id is not null and 
							drpo.Person_id = :Person_id and
							lrt.LpuRegionType_id = :LpuRegionType_id
						order by
							drpo.DrugRequestPersonOrder_id;
					";
				}
				$check_data = $this->getFirstRowFromQuery($query, array(
					'DrugRequest_id' => $data['DrugRequest_id'],
					'Person_id' => $data['Person_id'],
					'DrugRequestPeriod_id' => $dr_data['DrugRequestPeriod_id'],
					'PersonRegisterType_id' => $dr_data['PersonRegisterType_id'],
					'DrugRequestKind_id' => $dr_data['DrugRequestKind_id'],
					'DrugGroup_id' => $dr_data['DrugGroup_id'],
					'DrugRequestCategory_id' => $dr_data['DrugRequestCategory_id'],
					'Lpu_id' => $dr_data['Lpu_id'],
					'LpuRegionType_id' => $dr_data['LpuRegionType_id']
				));
				if (!empty($check_data['DrugRequest_id'])) {
					$err_msg = "Пациент не может добавлен, т.к. он  уже включен в разнарядку по заявке  участка {$check_data['LpuRegion_Name']}, {$check_data['MedPersonal_Fio']}, {$check_data['Lpu_Name']}. Чтобы включить пациента в свою разнарядку, исключите пациента из разнарядки заявки этого участкового врача.";
					throw new Exception($err_msg);
				}
			}

            if (!empty($data['DrugRequestFirstCopy_id']) || $dr_data['DrugRequestStatus_Code'] != '1') { //1 - Начальная
                //проверка на превышение количества медикамента в заявке
                $check_data = $this->checkDrugAmount(array(
                    'DrugRequestPersonOrder_id' => $data['DrugRequestPersonOrder_id'],
                    'DrugRequest_id' => $data['DrugRequest_id'],
                    'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
                    'Tradenames_id' => $data['Tradenames_id'],
                    'DrugRequestPersonOrder_OrdKolvo' => $data['DrugRequestPersonOrder_OrdKolvo']
                ));
                if (empty($check_data['Error_Msg']) && isset($check_data['distinction'])) {
                    if ($check_data['distinction'] < 0) {
                        throw new Exception('В разнарядку включено медикаментов больше, чем есть в резерве врача заявки. Уменьшите количество ЛС в разнарядке');
                    }
                } else {
                    throw new Exception(!empty($check_data['Error_Msg']) ? $check_data['Error_Msg'] : 'При проверке количества медикамента поизошла ошибка');
                }

                //проверка на превышение количества медикамента в реальной потребности (дублирует проверку на форме, так что нужно вызывать только тогда когда нет проверки на форме)
                if (isset($data['need_check_kolvo_in_first_copy'])) {
                    $check_data = $this->checkExistPersonDrugInRegionFirstCopy(array(
                        'DrugRequestFirstCopy_id' => $data['DrugRequestFirstCopy_id'],
                        'Person_id' => $data['Person_id'],
                        'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
                        'Tradenames_id' => $data['Tradenames_id']
                    ));
                    if (empty($check_data['Error_Msg']) && isset($check_data['drpo_kolvo'])) {
                        if ($data['DrugRequestPersonOrder_OrdKolvo']*1 > $check_data['drpo_kolvo']*1) {
                            throw new Exception('В разнарядку включено медикаментов больше, чем есть в резерве врача заявки. Уменьшите количество ЛС в разнарядке');
                        }
                    } else {
                        throw new Exception(!empty($check_data['Error_Msg']) ? $check_data['Error_Msg'] : 'При проверке данных о реальной потребности произошла ошибка');
                    }
                }
            }

            //проверка на дублирование
            $query = "
                declare
                    @DrugRequestPeriod_id bigint = null,
                    @PersonRegisterType_id bigint = null,
                    @DrugRequestKind_id bigint = null,
                    @DrugGroup_id bigint = null,
                    @DrugRequestCategory_id bigint = null,
                    @DrugRequest_Version bigint = null;

                select
                    @DrugRequestPeriod_id = DrugRequestPeriod_id,
                    @PersonRegisterType_id = PersonRegisterType_id,
                    @DrugRequestKind_id = DrugRequestKind_id,
                    @DrugGroup_id = DrugGroup_id,
                    @DrugRequestCategory_id = DrugRequestCategory_id,
                    @DrugRequest_Version = DrugRequest_Version
                from
                    v_DrugRequest with (nolock)
                where
                    DrugRequest_id = :DrugRequest_id;

                select
                    drpo.DrugRequestPersonOrder_id,
                    drpo.DrugRequest_id
                from
                    v_DrugRequest dr with(nolock)
                    left join v_DrugRequestPersonOrder drpo with(nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
                where
                    dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                    isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                    isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                    isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                    dr.DrugRequestCategory_id = @DrugRequestCategory_id and
                    isnull(dr.DrugRequest_Version, 0) = isnull(@DrugRequest_Version, 0) and
                    drpo.Person_id = :Person_id and
                    isnull(drpo.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
                    isnull(drpo.Tradenames_id, 0) = isnull(:Tradenames_id, 0) and
                    (
                        :DrugRequestPersonOrder_id is null or
                        drpo.DrugRequestPersonOrder_id != :DrugRequestPersonOrder_id
                    ) and
                    (
                        dr.DrugRequest_id = :DrugRequest_id or
                        :DrugComplexMnn_id is not null or
                        :Tradenames_id is not null
                    );
            ";
            $double_data = $this->getFirstRowFromQuery($query, $data);

            if (!empty($double_data['DrugRequestPersonOrder_id'])) {
                if ($double_data['DrugRequest_id'] == $data['DrugRequest_id']) {
                    $result['success'] = false;
                    $result['DrugRequestPersonOrder_id'] = $double_data['DrugRequestPersonOrder_id'];
                    throw new Exception('Данная запись уже есть в разнарядке');
                } else {
                    throw new Exception('Медикамент уже включен в персональную разнарядку пациента текущей заявочной кампании');
                }
            } else {
                $result = $this->saveObject('DrugRequestPersonOrder', $data);
                if (!empty($result['DrugRequestPersonOrder_id'])) {
                    if (!empty($data['DrugRequestFirstCopy_id'])) { //если сохранение успешно и передан идентификатор "первой копии" то дублируем туда данные, проверка дублирования в рамках заявочной кампании производится снаружи
                        $res = $this->copyDrugRequestPersonOrderToFirstCopy(array(
                            'DrugRequestPersonOrder_id' => $result['DrugRequestPersonOrder_id'],
                            'DrugRequestFirstCopy_id' => $data['DrugRequestFirstCopy_id']
                        ));
                        if (!empty($res['Error_Msg'])) {
                            throw new Exception($res['Error_Msg']);
                        }
                    }
                } else {
                    throw new Exception(!empty($result['Error_Msg']) ? $result['Error_Msg'] : 'При сохранении строки разнарядки произошла ошибка');
                }

                //проверяем есть ли запись в списке пациентов, если нет - добавляем
                if (!empty($data['DrugComplexMnn_id']) || !empty($data['Tradenames_id'])) {
                    $query = "
                        select
                            DrugRequestPersonOrder_id
                        from
                            v_DrugRequestPersonOrder with(nolock)
                        where
                            DrugRequest_id = :DrugRequest_id and
                            Person_id = :Person_id and
                            DrugComplexMnn_id is null and
                            Tradenames_id is null;
                    ";
                    $id = $this->getFirstResultFromQuery($query, $data);

                    if (empty($id) || $id <= 0) {
                    	$save_data = $data;
						$save_data['DrugComplexMnn_id'] = null;
						$save_data['Tradenames_id'] = null;
                        $res = $this->saveObject('DrugRequestPersonOrder', $save_data);
                        if (!empty($res['Error_Msg'])) {
                            throw new Exception($res['Error_Msg']);
                        }

                        if (!empty($res['DrugRequestPersonOrder_id']) && !empty($data['DrugRequestFirstCopy_id'])) { //если сохранение успешно и передан идентификатор "первой копии" то дублируем туда данные
                            $res = $this->copyDrugRequestPersonOrderToFirstCopy(array(
                                'DrugRequestPersonOrder_id' => $res['DrugRequestPersonOrder_id'],
                                'DrugRequestFirstCopy_id' => $data['DrugRequestFirstCopy_id']
                            ));
                            if (!empty($res['Error_Msg'])) {
                                throw new Exception($res['Error_Msg']);
                            }
                        }
                    }
                }
            }

            //если строка разнарядки с медикаментом, а так же если статус заявки - начальная и не передан идентификатор "первой копии" (считаем чэто признаком отсутствия "первой копии") редактиуем связанную строку заявки, если такой строки нет, то добавляем её
            if ((!empty($data['DrugComplexMnn_id']) || !empty($data['Tradenames_id'])) && $dr_data['DrugRequestStatus_Code'] == '1' && empty($data['DrugRequestFirstCopy_id'])) { //1 - Начальная
                //считаем суммарное количество по медикаменту среди разнарядок (используем существущий метод, изначально предназначенй для проверки)
                $drug_kolvo = 0;
                $res = $this->checkExistsDrugRequestPersonOrderForDrugRequestRow(array(
                    'DrugRequest_id' => $data['DrugRequest_id'],
                    'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
                    'TRADENAMES_id' => $data['Tradenames_id']
                ));
                if (!empty($res['drpo_kolvo'])) {
                    $drug_kolvo = $res['drpo_kolvo'];
                } else {
                    throw new Exception('При подсчете количества медикамента произошла ошибка');
                }

                //ищем подходящую строку заявки
                $query = "
                    select
                        drr.DrugRequestRow_id
                    from
                        v_DrugRequestRow drr with (nolock)
                    where
                        drr.DrugRequest_id = :DrugRequest_id and
                        isnull(drr.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
                        isnull(drr.TRADENAMES_id, 0) = isnull(:Tradenames_id, 0);
                ";
                $row_id = $this->getFirstResultFromQuery($query, array(
                    'DrugRequest_id' => $data['DrugRequest_id'],
                    'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
                    'Tradenames_id' => $data['Tradenames_id']
                ));

                $save_params = array();
                $save_params['DrugRequestRow_Kolvo'] = $drug_kolvo;

                if (!empty($row_id)) {
                    $save_params['DrugRequestRow_id'] = $row_id;
                } else {
                    $save_params['DrugRequest_id'] = $data['DrugRequest_id'];
                    if (!empty($data['DrugComplexMnn_id'])) {
                        $save_params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
                    }
                    if (!empty($data['Tradenames_id'])) {
                        $save_params['TRADENAMES_id'] = $data['Tradenames_id'];
                    }
					if (!empty($data['EvnVK_id'])) {
                        $save_params['EvnVK_id'] = $data['EvnVK_id'];
                    }
                    if (!empty($data['DrugFinance_id'])) {
                        $save_params['DrugFinance_id'] = $data['DrugFinance_id'];
                    } else {
						throw new Exception('Не указан источник финансирования, сохранение прервано');
					}
                }

                //определение цены медикамента
                $property_data = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $data['DrugRequest_id']));
                if (isset($property_data['DrugRequestProperty_id'])) {
                    $query = "
                        select top 1
                            (case
                                when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
                                else i_dlr.DrugListRequest_Price
                            end) as Price
                        from
                            v_DrugListRequest i_dlr with (nolock)
                            left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = :TRADENAMES_id
                        where
                            i_dlr.DrugComplexMnn_id = :DrugComplexMnn_id and
                            (
                                i_dlr.DrugRequestProperty_id = :DrugRequestProperty_id or
                                (i_dlr.DrugRequestProperty_id = :DrugRequestPropertyFed_id and :DrugFinance_id = 3) or
                                (i_dlr.DrugRequestProperty_id = :DrugRequestPropertyReg_id and :DrugFinance_id = 27)
                            )
                    ";
                    $price = $this->getFirstResultFromQuery($query, array(
                        'DrugComplexMnn_id' => !empty($data['DrugComplexMnn_id']) ? $data['DrugComplexMnn_id'] : null,
                        'TRADENAMES_id' => !empty($data['Tradenames_id']) ? $data['Tradenames_id'] : null,
                        'DrugFinance_id' => !empty($data['DrugFinance_id']) ? $data['DrugFinance_id'] : null,
                        'DrugRequestProperty_id' => $property_data['DrugRequestProperty_id'],
                        'DrugRequestPropertyFed_id' => $property_data['DrugRequestPropertyFed_id'],
                        'DrugRequestPropertyReg_id' => $property_data['DrugRequestPropertyReg_id']
                    ));;
                    $save_params['DrugRequestRow_Summa'] = !empty($price) ? ($drug_kolvo*1)*($price*1) : 0;
                } else {
                    throw new Exception('При получении информации о списке медикаментов произошла ошибка');
                }

                $res = $this->saveObject('DrugRequestRow', $save_params);
                if (empty($res['DrugRequestRow_id'])) {
                    if (!empty($res['Error_Msg'])) {
                        throw new Exception($res['Error_Msg']);
                    } else {
                        throw new Exception('При '.($row_id > 0 ? 'редактировании' : 'добавлении').' строки произошла ошибка');
                    }
                }
            }

            $this->commitTransaction();
            $result['success'] = true;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            $result['Error_Msg'] = $e->getMessage();
        }

		return $result;
	}

	/**
	 * Получение идентификатора произвольного обьекта по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$query = "
			select top 1
				{$object_name}_id
			from
				v_{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Возвращает первую запись из таблицы $object_name со свойствами переданными в $data
	 */
	function checkObjectDoubles($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$where = "";
		foreach ($data as $key => $value) {
			if (empty($where)) {
				$where .= "where ";
			} else {
				$where .= " and ";
			}
			if ($key == "{$object_name}_id") {
				$where .= "(:{$key} is null or {$key} <> :{$key})";
			} else {
				if ($value == null) {
					$where .= "{$key} is null";
				} else {
					$where .= "{$key} = :{$key}";
				}
			}
		}

		$query = "
			select top 1
				*
			from
				{$schema}.v_{$object_name} with (nolock)
			{$where}
		";
		return $this->getFirstRowFromQuery($query, $data);
	}

	/**
	 * Загрузка данных конкртеного рабочего периода
	 */
	function loadDrugRequestPeriod($data) {
		$query = "
			select
				drp.DrugRequestPeriod_id,
				drp.DrugRequestPeriod_begDate,
				drp.DrugRequestPeriod_endDate,
				drp.DrugRequestPeriod_Name,
				drpp.DrugRequestPlanPeriod_UsedCount
			from
				dbo.v_DrugRequestPeriod drp with (nolock)
				outer apply (
					select
						count(i_drpd.DrugRequestPlanDelivery_id) as DrugRequestPlanPeriod_UsedCount
					from
						v_DrugRequestPlanPeriod i_drpp with (nolock)
						left join v_DrugRequestPlanDelivery i_drpd with (nolock) on i_drpd.DrugRequestPlanPeriod_id = i_drpp.DrugRequestPlanPeriod_id
					where
						i_drpp.DrugRequestPeriod_id = drp.DrugRequestPeriod_id
				) drpp
			where
				drp.DrugRequestPeriod_id = :DrugRequestPeriod_id
		";
		$result = $this->db->query($query, array(
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']
		));
		if (is_object($result)) {
			$result = $result->result('array');
			if (isset($result[0])) {
				return $result;
			}
		}

		return false;
	}

	/**
	 * Сохранение информации о планово-отчетных периодах
	 */
	function saveDrugRequestPlanPeriodFromJSON($data) {
		$result = array();
		$error = array();
		$this->beginTransaction();

		if (!empty($data['json_str']) && $data['DrugRequestPeriod_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) {
				//проверка на возможность редактирования
				$response = $this->checkDrugRequestPlanPeriodEdit(array(
					'DrugRequestPlanPeriod_id' => $record->DrugRequestPlanPeriod_id
				));
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
					continue;
				}

				switch($record->state) {
					case 'add':
					case 'edit':
						//сохранение
						$save_data = array(
							'DrugRequestPlanPeriod_id' => $record->state == 'edit' ? $record->DrugRequestPlanPeriod_id : null,
							'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
							'DrugRequestPlanPeriod_Name' => $record->DrugRequestPlanPeriod_Name,
							'DrugRequestPlanPeriod_begDate' => $record->DrugRequestPlanPeriod_begDate,
							'DrugRequestPlanPeriod_endDate' => $record->DrugRequestPlanPeriod_endDate,
							'pmUser_id' => $data['pmUser_id']
						);
						$response = $this->saveObject('DrugRequestPlanPeriod', $save_data);
						if (!empty($response['Error_Msg'])) {
							$error[] = $response['Error_Msg'];
						}
						break;
					case 'delete':
						//удаление
						$response = $this->deleteObject('DrugRequestPlanPeriod', array(
							'DrugRequestPlanPeriod_id' => $record->DrugRequestPlanPeriod_id
						));
						if (!empty($response['Error_Msg'])) {
							$error[] = $response['Error_Msg'];
						}
						break;
				}
			}
		}

		if (count($error) <= 0) {
			$this->commitTransaction();
		} else {
			$this->rollbackTransaction();
			$result['Error_Msg'] = $error[0];
		}

		return $result;
	}

	/**
	 * Загрузка списка планово-отчетных периодов
	 */
	function loadDrugRequestPlanPeriodList($data) {
		$query = "
			select
				DrugRequestPlanPeriod_id,
				DrugRequestPlanPeriod_Name,
				convert(varchar(10), DrugRequestPlanPeriod_begDate, 104) as DrugRequestPlanPeriod_begDate,
				convert(varchar(10), DrugRequestPlanPeriod_endDate, 104) as DrugRequestPlanPeriod_endDate
			from
				v_DrugRequestPlanPeriod with(nolock)
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ЛПУ для комбобокса
	 */
	function loadLpuCombo($data) {
		$filters = array();
		$params = array();
		$where = "";

		if (!empty($data['Lpu_id'])) {
			$filters[] = "l.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else {
			if (!empty($data['query'])) {
				$filters[] = "l.Lpu_Name like :query";
				$params['query'] = "%".$data['query']."%";
			}
            if (!empty($data['Date'])) {
				$filters[] = "(l.Lpu_begDate is null or l.Lpu_begDate <= :Date)";
				$filters[] = "(l.Lpu_endDate is null or l.Lpu_endDate >= :Date)";
				$params['Date'] = $data['Date'];
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

        $query = "
        	select top 500
        		l.Lpu_id,
        		l.Lpu_Name
			from v_Lpu l with (nolock)
				{$where}
			order by
				l.Lpu_Name
    	";
		
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка отделений для комбобокса
	 */
	function loadLpuSectionCombo($data) {
		$filters = array();
		$params = array();
		$where = "";

		if (!empty($data['LpuSection_id'])) {
			$filters[] = "ls.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		} else {
			if (!empty($data['Lpu_id'])) {
				$filters[] = "lu.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}

			if (!empty($data['LpuUnit_id'])) {
				$filters[] = "ls.LpuUnit_id = :LpuUnit_id";
				$params['LpuUnit_id'] = $data['LpuUnit_id'];
			}

			if (!empty($data['query'])) {
				$filters[] = "ls.LpuSection_Name like :query";
				$params['query'] = "%".$data['query']."%";
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

        $query = "
        	select top 500
        		ls.LpuSection_id,
        		ls.LpuSection_Name
			from v_LpuSection ls with (nolock)
			 	left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				{$where}
			order by
				ls.LpuSection_Name
    	";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка участков для комбобокса
	 */
	function loadLpuRegionCombo($data) {
		$filters = array();
		$params = array();
		$where = "";

		if (!empty($data['LpuRegion_id'])) {
			$filters[] = "lr.LpuRegion_id = :LpuRegion_id";
			$params['LpuRegion_id'] = $data['LpuRegion_id'];
		} else {
			if (!empty($data['Lpu_id'])) {
				$filters[] = "lr.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}

			if (!empty($data['LpuSection_id'])) {
				$filters[] = "(lr.LpuSection_id is null or lr.LpuSection_id = :LpuSection_id)";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
        	select top 500
        		lr.LpuRegion_id,
        		(lr.LpuRegion_Name + isnull(' (' + lrt.LpuRegionType_Name + ')', '')) as LpuRegion_Name
			from
			    v_LpuRegion lr with (nolock)
			    left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
				{$where}
			order by
				lr.LpuRegion_Name
    	";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей для комбобокса
	 */
	function loadMedPersonalCombo($data) {
		$filters = array();
		$params = array();
		$where = "";

		if (!empty($data['MedPersonal_id'])) {
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		} else {
			$filters[] = "msf.WorkData_begDate is not null and msf.WorkData_begDate <= dbo.tzGetDate()";

			if ($data['session']['region']['nick'] != 'ufa') {
				$filters[] = "msf.WorkData_dlobegDate is not null and msf.WorkData_dlobegDate <= dbo.tzGetDate()";
				$filters[] = "(msf.WorkData_dloendDate is null or msf.WorkData_dloendDate > dbo.tzGetDate())";
				$filters[] = "isnull(msf.MedPersonal_Code, '0') != '0'";
			} else {
				$filters[] = "isnull(msf.MedPersonal_TabCode, '0') != '0'";
			}

			if (!empty($data['Lpu_id']) && !isFarmacy()) {
				$filters[] = "msf.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}

			if (!empty($data['LpuSection_id'])) {
				$filters[] = "msf.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			} else if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
				$filters[] = "msf.LpuSection_id in (select LpuSection_id from Contragent with(nolock) where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
				$params['OrgFarmacy_id'] = $data['session']['OrgFarmacy_id'];
			}

			if (!empty($data['LpuUnit_id'])) {
				$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
				$params['LpuUnit_id'] = $data['LpuUnit_id'];
			}
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

        $query = "
        	select distinct
        		msf.MedPersonal_id,
				ltrim(rtrim(isnull(msf.MedPersonal_TabCode,0))) as MedPersonal_Code,
				ltrim(rtrim(isnull(msf.Person_FIO,''))) as MedPersonal_Fio,
				convert(varchar(10), msf.WorkData_endDate, 104) as WorkData_endDate,
				msf.LpuSection_id
			from v_MedStaffFact msf with (nolock)
				{$where}
			order by
				ltrim(rtrim(isnull(msf.Person_FIO,'')))
    	";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение идентификатора заявки по списку параметров
	 */
	function getDrugRequestIdByParams($data) {
		$query = "
			select
				DrugRequest_id
			from
				v_DrugRequest dr with (nolock)
			where
				isnull(dr.DrugRequestPeriod_id, 0) = isnull(:DrugRequestPeriod_id, 0) and
				isnull(dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
				isnull(dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
				isnull(dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
				isnull(dr.Lpu_id, 0) = isnull(:Lpu_id, 0) and
				isnull(dr.LpuUnit_id, 0) = isnull(:LpuUnit_id, 0) and
				isnull(dr.LpuSection_id, 0) = isnull(:LpuSection_id, 0) and
				isnull(dr.LpuRegion_id, 0) = isnull(:LpuRegion_id, 0) and
				isnull(dr.MedPersonal_id, 0) = isnull(:MedPersonal_id, 0) and
				dr.DrugRequest_Version is null;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'DrugRequestPeriod_id' => !empty($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : null,
			'PersonRegisterType_id' => !empty($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : null,
			'DrugRequestKind_id' => !empty($data['DrugRequestKind_id']) ? $data['DrugRequestKind_id'] : null,
			'DrugGroup_id' => !empty($data['DrugGroup_id']) ? $data['DrugGroup_id'] : null,
			'Lpu_id' => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'LpuUnit_id' => !empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null,
			'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
			'LpuRegion_id' => !empty($data['LpuRegion_id']) ? $data['LpuRegion_id'] : null,
			'MedPersonal_id' => !empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : null
		));

		return $result > 0 ? $result : null;
	}

	/**
	 * Получение данных о категории заявки
	 */
	function getDrugRequestCategory($request_id) {
		$query = "
			select
				dr.DrugRequest_id,
				dr.DrugRequestCategory_id,
				drc.DrugRequestCategory_Name,
				drc.DrugRequestCategory_Code,
				drc.DrugRequestCategory_SysNick
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestCategory drc with(nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
		$result = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $request_id
		));
		return $result;
	}

	/**
	 * Получение данных заявки (используется на форме редактирования заявки)
	 */
	function getDrugRequestData($request_id) {
        $region = $_SESSION['region']['nick'];
		$finance_id = null;
        $join = array();

		$property = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $request_id));
		$property_id = $property['DrugRequestProperty_id'];
		if ($property_id > 0) {
			$finance_id = $this->getFirstResultFromQuery("
				select
					DrugFinance_id
				from
					v_DrugRequestProperty with(nolock)
				where
					DrugRequestProperty_id = :DrugRequestProperty_id
			", array('DrugRequestProperty_id' => $property_id));
		}

        if ($region == 'ufa') {
            $join[] = "
                outer apply (
                    select
                        (case
                            when dr.DrugRequest_Version = 1 then 'Прогноз регионального лекарственного обеспечения'
                            when DrugRequestFirstCopy.DrugRequest_id is not null then 'Лимит.потр.'
                            else null
                        end) as Inf
                ) FirstCopyInf
            ";
        } else {
            $join[] = "
                outer apply (
                    select
                        (case
                            when dr.DrugRequest_Version = 1 then 'Копия 1'
                            else null
                        end) as Inf
                ) FirstCopyInf
            ";
        }

        $join_clause = implode(' ', $join);

		$query = "
			select top 1
				dr.DrugRequest_id,
				dr.PersonRegisterType_id,
				prt.PersonRegisterType_Name,
				drp.DrugRequestPeriod_Name,
				dr.DrugRequestStatus_id,
				drs.DrugRequestStatus_Name,
				drs.DrugRequestStatus_Code,
				drc.DrugRequestCategory_SysNick,
				l.Lpu_id,
				l.Lpu_Nick,
				l.Lpu_Name,
				ls.LpuSection_Name,
				lr.LpuRegion_id,
				(lr.LpuRegion_Name + isnull(' (' + lrt.LpuRegionType_Name + ')', '')) as LpuRegion_Name,
				dr.MedPersonal_id,
				mp.Person_Fio as MedPersonal_Fio,
				mp.Dolgnost_Name,
				(convert(varchar(10), prot.Protection_Date, 104) + ' ' + convert(char(5), prot.Protection_Date, 108)) as Protection_Date,
				datediff(day, dbo.tzGetDate(), prot.Protection_Date) as Protection_RemainedDays,
				(case
				    when is_hms.HeadMedSpec_id is not null then 1
				    else 0
				end) as MedPersonal_isMainSpec,
				DrugRequestFirstCopy.DrugRequest_id as DrugRequestFirstCopy_id,
				FirstCopyInf.Inf as FirstCopy_Inf
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
				left join v_DrugRequestCategory drc with(nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
				left join v_Lpu l with(nolock) on l.Lpu_id = dr.Lpu_id
				left join v_LpuSection ls with(nolock) on ls.LpuSection_id = dr.LpuSection_id
				left join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = dr.LpuRegion_id
				left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = dr.MedPersonal_id
				left join v_PersonRegisterType prt with(nolock) on prt.PersonRegisterType_id = dr.PersonRegisterType_id
				left join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				outer apply (
					select top 1
						i_ttmso.TimetableMedServiceOrg_begTime as Protection_Date
					from
						v_TimetableMedServiceOrg i_ttmso with (nolock)
						inner join v_Lpu i_l with (nolock) on i_l.Org_id = i_ttmso.Org_id
					where
						i_l.Lpu_id = l.Lpu_id
					order by
						i_ttmso.TimetableMedServiceOrg_begTime
				) prot
                outer apply ( -- проверка на включение врача заявки в перечень главных внештатных специалистов
                    select top 1
                        i_hms.HeadMedSpec_id
                    from
                        persis.v_MedWorker i_mw with (nolock)
                        inner join v_HeadMedSpec i_hms with (nolock) on i_hms.MedWorker_id = i_mw.MedWorker_id
                    where
                        i_mw.Person_id = mp.Person_id and
                        drp.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
                ) is_hms
                outer apply (
                    select top 1
                        fc_dr.DrugRequest_id
                    from
                        v_DrugRequest fc_dr with(nolock)
                    where
                        fc_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                        isnull(fc_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                        isnull(fc_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                        isnull(fc_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                        isnull(fc_dr.Lpu_id, 0) = isnull(dr.Lpu_id, 0) and
                        isnull(fc_dr.LpuSection_id, 0) = isnull(dr.LpuSection_id, 0) and
                        isnull(fc_dr.LpuUnit_id, 0) = isnull(dr.LpuUnit_id, 0) and
                        isnull(fc_dr.LpuRegion_id, 0) = isnull(dr.LpuRegion_id, 0) and
                        isnull(fc_dr.MedPersonal_id, 0) = isnull(dr.MedPersonal_id, 0) and
                        fc_dr.DrugRequest_Version = 1 and
                        fc_dr.DrugRequestCategory_id = dr.DrugRequestCategory_id and
                        fc_dr.DrugRequest_id <> dr.DrugRequest_id
                ) DrugRequestFirstCopy
                {$join_clause}
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
		$r = $this->db->query($query, array('DrugRequest_id' => $request_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$r[0]['DrugFinance_id'] = $finance_id;
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    /**
     * Получение данных заявки (используется на форме редактирования заявки)
     */
    function getDrugRequestSumData($request_id) {
        $reg_dr_data = $this->getRegionDrugRequestByParams(array('DrugRequest_id' => $request_id));

        $sum_dr_data = array(
             'DrugRequest_RowSumma' => null,
             'DrugRequest_FedRowSumma' => null,
             'DrugRequest_RegRowSumma' => null,
             'DrugRequestPersonOrder_Summa' => null,
             'DrugRequestPersonOrder_FedSumma' => null,
             'DrugRequestPersonOrder_RegSumma' => null,
             'DrugRequestPersonOrder_Kolvo' => null,
             'DrugRequestPersonOrder_FedKolvo' => null,
             'DrugRequestPersonOrder_RegKolvo' => null
        );

        $params = array(
            'DrugRequest_id' => $request_id,
            'DrugRequestProperty_id' => $reg_dr_data['DrugRequestProperty_id'],
            'DrugRequestPropertyFed_id' => $reg_dr_data['DrugRequestPropertyFed_id'],
            'DrugRequestPropertyReg_id' => $reg_dr_data['DrugRequestPropertyReg_id'],
            'DrugFinanceFed_id' => '3',
            'DrugFinanceReg_id' => '27',
        );

        $query_array = array();

        $query_array[] = "
            select
                sum(i_drr.DrugRequestRow_Summa) as DrugRequest_RowSumma
            from
                v_DrugRequestRow i_drr with (nolock)
            where
                i_drr.DrugRequest_id = :DrugRequest_id;
        ";

        $query_array[] = "
            select
                sum(i_drr_f.DrugRequestRow_Summa) as DrugRequest_FedRowSumma
            from
                v_DrugRequestRow i_drr_f with (nolock)
            where
                i_drr_f.DrugRequest_id = :DrugRequest_id and i_drr_f.DrugFinance_id = :DrugFinanceFed_id;
        ";

        $query_array[] = "
            select
                sum(i_drr_r.DrugRequestRow_Summa) as DrugRequest_RegRowSumma
            from
                v_DrugRequestRow i_drr_r with (nolock)
            where
                i_drr_r.DrugRequest_id = :DrugRequest_id and i_drr_r.DrugFinance_id = :DrugFinanceReg_id;
        ";

        $query_array[] = "
            select
                sum(isnull(p.Kolvo*p.Price, 0)) as DrugRequestPersonOrder_Summa,
                sum(case when p.DrugFinance_id = :DrugFinanceFed_id then isnull(p.Kolvo*p.Price, 0) else 0 end) as DrugRequestPersonOrder_FedSumma,
                sum(case when p.DrugFinance_id = :DrugFinanceReg_id then isnull(p.Kolvo*p.Price, 0) else 0 end) as DrugRequestPersonOrder_RegSumma
            from (
                select
                    drug_list.DrugComplexMnn_id,
                    drug_list.Tradenames_id,
                    drug_list.DrugFinance_id,
                    drug_list.Kolvo,
                    dlr.Price
                from
                    v_DrugRequestPersonOrder drpo with (nolock)
                    outer apply (
                        select
                            i_drpo.DrugComplexMnn_id,
                            i_drpo.Tradenames_id,
                            i_drr.DrugFinance_id,
                            sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo
                        from
                            v_DrugRequestPersonOrder i_drpo with (nolock)
                            outer apply (
                                select top 1
                                    ii_drr.DrugFinance_id
                                from
                                    v_DrugRequestRow ii_drr with (nolock)
                                where
                                    ii_drr.DrugRequest_id = :DrugRequest_id and
                                    isnull(ii_drr.DrugComplexMnn_id, 0) = isnull(i_drpo.DrugComplexMnn_id, 0) and
                                    isnull(ii_drr.TRADENAMES_id, 0) = isnull(i_drpo.Tradenames_id, 0)
                                order by
                                    ii_drr.DrugFinance_id desc, ii_drr.DrugRequestRow_id
                            ) i_drr
                        where
                            i_drpo.DrugRequest_id = :DrugRequest_id and
                            i_drpo.Person_id = drpo.Person_id and
                            i_drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
                            (
                                i_drpo.DrugComplexMnn_id is not null or
                                i_drpo.Tradenames_id is not null
                            )
                        group by
                            i_drpo.DrugComplexMnn_id,
                            i_drpo.Tradenames_id,
                            i_drr.DrugFinance_id
                    ) drug_list
                    outer apply (
                        select top 1
                            (case
                                when i_dlrt.DrugRequest_Price > 0 then i_dlrt.DrugRequest_Price
                                else i_dlr.DrugListRequest_Price
                            end) as Price
                        from
                            v_DrugListRequest i_dlr with (nolock)
                            left join v_DrugListRequestTorg i_dlrt with (nolock) on i_dlrt.DrugListRequest_id = i_dlr.DrugListRequest_id and i_dlrt.TRADENAMES_id = drug_list.Tradenames_id
                        where
                            i_dlr.DrugComplexMnn_id = drug_list.DrugComplexMnn_id
                                and (
                                (:DrugRequestProperty_id is not null and i_dlr.DrugRequestProperty_id  = :DrugRequestProperty_id) or
                                (drug_list.DrugFinance_id = :DrugFinanceFed_id and i_dlr.DrugRequestProperty_id  = :DrugRequestPropertyFed_id) or
                                (drug_list.DrugFinance_id = :DrugFinanceReg_id and i_dlr.DrugRequestProperty_id  = :DrugRequestPropertyReg_id)
                            )
                        order by
                            DrugListRequest_insDT desc
                    ) dlr
                where
                    drpo.DrugRequest_id = :DrugRequest_id and
                    drpo.DrugComplexMnn_id is null and
                    drpo.Tradenames_id is null
            ) p;
        ";

        $query_array[] = "
            select
                count(p.Person_id) as DrugRequestPersonOrder_Kolvo
            from (
                select
                    i_drpo.Person_id
                from
                    v_DrugRequestPersonOrder i_drpo with (nolock)
                where
                    i_drpo.DrugRequest_id = :DrugRequest_id and
                    i_drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
                    (
                        i_drpo.DrugComplexMnn_id is not null or
                        i_drpo.Tradenames_id is not null
                    )
                group by
                    i_drpo.Person_id
            ) p;
        ";

        $query_array[] = "
            select
                sum(case when p.DrugFinance_id = :DrugFinanceFed_id then 1 else 0 end) as DrugRequestPersonOrder_FedKolvo,
                sum(case when p.DrugFinance_id = :DrugFinanceReg_id then 1 else 0 end) as DrugRequestPersonOrder_RegKolvo
            from (
                select
                    i_drpo.Person_id,
                    i_drr.DrugFinance_id
                from
                    v_DrugRequestPersonOrder i_drpo with (nolock)
                    outer apply (
                        select top 1
                            ii_drr.DrugFinance_id
                        from
                            v_DrugRequestRow ii_drr with (nolock)
                        where
                            ii_drr.DrugRequest_id = :DrugRequest_id and
                            isnull(ii_drr.DrugComplexMnn_id, 0) = isnull(i_drpo.DrugComplexMnn_id, 0) and
                            isnull(ii_drr.TRADENAMES_id, 0) = isnull(i_drpo.Tradenames_id, 0)
                        order by
                            ii_drr.DrugFinance_id desc, ii_drr.DrugRequestRow_id
                    ) i_drr
                where
                    i_drpo.DrugRequest_id = :DrugRequest_id and
                    i_drpo.DrugRequestPersonOrder_OrdKolvo > 0 and
                    (
                        i_drpo.DrugComplexMnn_id is not null or
                        i_drpo.Tradenames_id is not null
                    )
                group by
                    i_drpo.Person_id,
                    i_drr.DrugFinance_id
            ) p;
        ";

        foreach($query_array as $query) {
            $data = $this->getFirstRowFromQuery($query, $params);
            if (is_array($data)) {
                foreach(array_keys($data) as $key) {
                    $sum_dr_data[$key] = $data[$key];
                }
            }
        }

        return $sum_dr_data;
    }

	/**
	 * Получение данных о статусе заявки
	 */
	function getDrugRequestStatus($request_id) {
		$query = "
			select
				dr.DrugRequest_id,
				dr.DrugRequestStatus_id,
				drs.DrugRequestStatus_Name,
				drs.DrugRequestStatus_Code
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
		$result = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $request_id
        ));
		return $result;
	}

	/**
	 * Загрузка
	 */
	function loadDrugRequestRow($data) {
		$q = "
			select
				*
			from
				dbo.DrugRequestRow drr with(nolock)
			where
				drr.DrugRequestRow_id = :DrugRequestRow_id
		";

		$result = $this->db->query($q, array('DrugRequestRow_id' => $data['DrugRequestRow_id']));

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Функция возвращает идентификатор списка медикаментов
	 */
	function getDrugRequestPropertyId($data) {
		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@DrugGroup_id bigint = :DrugGroup_id,
				@DrugRequestKind_id bigint = :DrugRequestKind_id,
				@DefaultDrugRequestKind_id bigint = NULL,
				@PersonRegisterType_id bigint = :PersonRegisterType_id,
				@DrugRequestPeriod_id bigint = :DrugRequestPeriod_id;

			set @DefaultDrugRequestKind_id = (select DrugRequestKind_id from DrugRequestKind with(nolock) where DrugRequestKind_Code = 1); -- 1 - Плановая;

			if (@DrugRequest_id is not null and @DrugRequest_id > 0)
			begin
				select
					@DrugGroup_id = DrugGroup_id,
					@DrugRequestKind_id = DrugRequestKind_id,
					@PersonRegisterType_id = PersonRegisterType_id,
					@DrugRequestPeriod_id = DrugRequestPeriod_id
				from
					DrugRequest with (nolock) where DrugRequest_id = @DrugRequest_id;
			end;

			select
				DrugRequestProperty_id,
				DrugRequestPropertyFed_id,
				DrugRequestPropertyReg_id
			from
				DrugRequest with (nolock)
			where
				DrugRequest_Version is null
				and DrugRequestCategory_id = (select DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'region')
				and isnull(DrugGroup_id, 0) = isnull(@DrugGroup_id, 0)
				and isnull(DrugRequestKind_id, @DefaultDrugRequestKind_id) = isnull(@DrugRequestKind_id, @DefaultDrugRequestKind_id)
				and isnull(DrugRequestPeriod_id, 0) = isnull(@DrugRequestPeriod_id, 0)
				and isnull(PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0);
		";

		$result = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => !empty($data['DrugRequest_id']) ? $data['DrugRequest_id'] : null,
			'DrugGroup_id' => !empty($data['DrugGroup_id']) ? $data['DrugGroup_id'] : null,
			'DrugRequestKind_id' => !empty($data['DrugRequestKind_id']) ? $data['DrugRequestKind_id'] : null,
			'DrugRequestPeriod_id' => !empty($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : null,
			'PersonRegisterType_id' => !empty($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : null
		));
		if ($result !== false) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка аналитики для строки заявки
	 */
	function loadDrugRequestRowFactorList($data) {
		$factor_arr = array();

		$factor_arr[] = array(
			'Factor_Name' => 'Заявлено за 3 предыдущих периода (в среднем)',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Заявлено  в предыдущем периоде',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Израсходовано в предыдущем периоде',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Не обеспечено по выписанным рецептам',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Остатки в ПО',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Свободные остатки ПО',
			'Factor_Value' => ''
		);
		$factor_arr[] = array(
			'Factor_Name' => 'Включить в заявку',
			'Factor_Value' => ''
		);

		for($i = 0; $i < count($factor_arr); $i++) {
			$factor_arr[$i]['Factor_id'] = $i+1;
		}

		return $factor_arr;
	}

	/**
	 * Загрузка строки разнарядки по пациентам
	 */
	function loadDrugRequestPersonOrder($data) {
		$q = "
			select
				drpo.DrugRequestPersonOrder_id,
				drpo.DrugRequest_id,
				drpo.Person_id,
				drpo.MedPersonal_id,
				drpo.DrugComplexMnn_id,
				drpo.Tradenames_id,
				drpo.DrugRequestPersonOrder_Kolvo,
				drpo.DrugRequestPersonOrder_OrdKolvo,
				convert(varchar(10), drpo.DrugRequestPersonOrder_begDate, 104) as DrugRequestPersonOrder_begDate,
				convert(varchar(10), drpo.DrugRequestPersonOrder_endDate, 104) as DrugRequestPersonOrder_endDate,
				drpo.DrugRequestExceptionType_id,
				dr_row.DrugFinance_id,
				EvnVK_id
			from
				dbo.v_DrugRequestPersonOrder drpo with (nolock)
				outer apply (
					select top 1 
						drr.DrugFinance_id
					from
						v_DrugRequestRow drr with (nolock)
					where
						drr.DrugRequest_id = drpo.DrugRequest_id and
						(drr.DrugComplexMnn_id = drpo.DrugComplexMnn_id or drr.TRADENAMES_id = drpo.Tradenames_id)
				) dr_row
			where
				drpo.DrugRequestPersonOrder_id = :DrugRequestPersonOrder_id
		";

		$result = $this->db->query($q, array('DrugRequestPersonOrder_id' => $data['DrugRequestPersonOrder_id']));

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк заявки
	 */
	function loadMzDrugRequestMoDrugGrid($data) {
		$filters = array();
		$where = "";

		$drugRequestProperty = $this->getDrugRequestPropertyId(array(
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
			'PersonRegisterType_id' => $data['PersonRegisterType_id'],
			'DrugRequestKind_id' => $data['DrugRequestKind_id'],
			'DrugGroup_id' => $data['DrugGroup_id']
		));
		$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		if (empty($data['DrugRequestProperty_id'])) {
			$data['DrugRequestProperty_id'] = null;
		}

		if (!empty($data['Tradenames_Name'])) {
			$filters[] = "tn.NAME like :Tradenames_Name";
			$data['Tradenames_Name'] = '%'.$data['Tradenames_Name'].'%';
		}
		if (!empty($data['ClsDrugForms_Name'])) {
			$filters[] = "cdf.NAME like :ClsDrugForms_Name";
			$data['ClsDrugForms_Name'] = '%'.$data['ClsDrugForms_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnName_Name'])) {
			$filters[] = "dcmn.DrugComplexMnnName_Name like :DrugComplexMnnName_Name";
			$data['DrugComplexMnnName_Name'] = '%'.$data['DrugComplexMnnName_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnDose_Name'])) {
			$filters[] = "dcmd.DrugComplexMnnDose_Name like :DrugComplexMnnDose_Name";
			$data['DrugComplexMnnDose_Name'] = '%'.$data['DrugComplexMnnDose_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnFas_Name'])) {
			$filters[] = "dcmf.DrugComplexMnnFas_Name like :DrugComplexMnnFas_Name";
			$data['DrugComplexMnnFas_Name'] = '%'.$data['DrugComplexMnnFas_Name'].'%';
		}
		if (!empty($data['DrugFinance_id'])) {
			$filters[] = "df2.DrugFinance_id = :DrugFinance_id";
			$data['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
        	select
        		-- select
        		drr.DrugRequestRow_id,
        		drr.DrugComplexMnn_id,
        		dcmn.DrugComplexMnnName_Name,
        		drr.TRADENAMES_id,
        		tn.NAME as Tradenames_Name,
        		cdf.NAME as ClsDrugForms_Name,
        		dcmd.DrugComplexMnnDose_Name,
        		dcmf.DrugComplexMnnFas_Name,
        		df2.DrugFinance_Name,
        		drr.DrugRequestRow_Kolvo,
				case 
					when DrugListRequest_Data.Price > 0 then DrugListRequest_Data.Price 
					else CONVERT(money, (drr.DrugRequestRow_Summa / drr.DrugRequestRow_Kolvo))
				end as DrugRequestRow_Price,
				drr.DrugRequestRow_Summa,
			    DrugListRequest_Data.DrugListRequest_Comment,
				DrugListRequest_Data.isProblem,
				DrugListRequest_Data.isProblemTorg,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				NTFR_Data.NAME as NTFR_Name
				-- end select
			from
				-- from
				(
					select
						max(i_drr.DrugRequestRow_id) as DrugRequestRow_id,
						i_drr.DrugComplexMnn_id,
						i_drr.TRADENAMES_ID,
						sum(i_drr.DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
						sum(i_drr.DrugRequestRow_Summa) as DrugRequestRow_Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
					    i_dr.DrugRequest_Version is null and
						i_dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
						isnull(i_dr.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
						isnull(i_dr.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
						isnull(i_dr.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
						i_drr.Person_id is null and
						(
							i_drr.DrugComplexMnn_id is not null or
							i_drr.TRADENAMES_ID is not null
						) and
						(:Lpu_id is null or i_dr.Lpu_id = :Lpu_id)
					group by
						i_drr.DrugComplexMnn_id, i_drr.TRADENAMES_ID
				) drr
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = drr.TRADENAMES_ID
				outer apply (
					select top 1 
						df.DrugFinance_Name
						,i_drr2.DrugFinance_id
					from v_DrugRequest i_dr2 with (nolock)
					left join v_DrugRequestRow i_drr2 with (nolock) on i_drr2.DrugRequest_id = i_dr2.DrugRequest_id
					left join v_DrugFinance df  with(nolock) on df.DrugFinance_id = i_drr2.DrugFinance_id
					where --i_drr2.DrugRequest_id = drr.DrugRequestRow_id
					i_dr2.DrugRequest_Version is null and
					i_dr2.DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(i_dr2.PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(i_dr2.DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(i_dr2.DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					i_drr2.Person_id is null and
					(
						i_drr2.DrugComplexMnn_id = drr.DrugComplexMnn_id or
						i_drr2.TRADENAMES_ID = drr.TRADENAMES_ID
					) and
					(:Lpu_id is null or i_dr2.Lpu_id = :Lpu_id) and 
					df.DrugFinance_Name is not null
				) df2
				outer apply (
					select top 1
						(case
							when dlrt.DrugRequest_Price > 0 then dlrt.DrugRequest_Price
							else dlr.DrugListRequest_Price
						end) as Price,
			            dlr.DrugListRequest_Comment,
						isnull(isProblem.YesNo_Code, 0) as isProblem,
						isnull(isProblemTorg.YesNo_Code, 0) as isProblemTorg
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = drr.TRADENAMES_id
						left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
						left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = dlrt.DrugListRequestTorg_IsProblem
					where
						dlr.DrugRequestProperty_id = :DrugRequestProperty_id and
						dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id
				) as DrugListRequest_Data
				outer apply (
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Data
				-- end from
			{$where}
			order by
				-- order by
				dcmn.DrugComplexMnnName_Name
				-- end order by
    	";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $data);
		if (is_object($result) && $count !== false) {
			return array(
				'data' => $result->result('array'),
				'totalCount' => $count
			);
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк заявки
	 */
	function loadMzDrugRequestDrugGrid($data) {
		$filters = array();
		$join = "";
		$where = "";

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $data['DrugRequest_id']));
		if(!empty($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 1){
			$whereDrugRequestProperty = " (dlr.DrugRequestProperty_id = :DrugRequestPropertyFed_id or dlr.DrugRequestProperty_id = :DrugRequestPropertyReg_id) ";
			$data['DrugRequestPropertyFed_id'] = $drugRequestProperty['DrugRequestPropertyFed_id'];
			$data['DrugRequestPropertyReg_id'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
		} else {
			$whereDrugRequestProperty = " dlr.DrugRequestProperty_id = :DrugRequestProperty_id ";
			$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		}
		

		$filters[] = "drr.DrugRequest_id = :DrugRequest_id";
		$filters[] = "drr.Person_id is null";

		if (is_array($data['DrugRequestRow_updDateRange']) && !empty($data['DrugRequestRow_updDateRange'][0]) && !empty($data['DrugRequestRow_updDateRange'][1])) {

			$filters[] = "cast(drr.DrugRequestRow_insDT as date) between :DrugRequestRow_updDateRange_1 and :DrugRequestRow_updDateRange_2";
			$data['DrugRequestRow_updDateRange_1'] = $data['DrugRequestRow_updDateRange'][0];
			$data['DrugRequestRow_updDateRange_2'] = $data['DrugRequestRow_updDateRange'][1];
		}
		if (!empty($data['Tradenames_Name'])) {
			$filters[] = "tn.NAME like :Tradenames_Name";
			$data['Tradenames_Name'] = '%'.$data['Tradenames_Name'].'%';
		}
		if (!empty($data['ClsDrugForms_Name'])) {
			$filters[] = "cdf.NAME like :ClsDrugForms_Name";
			$data['ClsDrugForms_Name'] = '%'.$data['ClsDrugForms_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnName_Name'])) {
			$filters[] = "dcmn.DrugComplexMnnName_Name like :DrugComplexMnnName_Name";
			$data['DrugComplexMnnName_Name'] = '%'.$data['DrugComplexMnnName_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnDose_Name'])) {
			$filters[] = "dcmd.DrugComplexMnnDose_Name like :DrugComplexMnnDose_Name";
			$data['DrugComplexMnnDose_Name'] = '%'.$data['DrugComplexMnnDose_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnFas_Name'])) {
			$filters[] = "dcmf.DrugComplexMnnFas_Name like :DrugComplexMnnFas_Name";
			$data['DrugComplexMnnFas_Name'] = '%'.$data['DrugComplexMnnFas_Name'].'%';
		}
		if (empty($data['ShowDeleted']) || $data['ShowDeleted'] != 1) {
			$filters[] = "drr.DrugRequestRow_delDT is null";
		}
		if (!empty($data['ShowWithoutPerson'])) {
			$filters[] = "drpo.cnt = 0";$filters[] = "drpo.cnt = 0";
			$join .= "
				outer apply (
					select
						count(i_drpo.DrugRequestPersonOrder_id) as cnt
					from
						v_DrugRequestPersonOrder i_drpo with (nolock)
					where
						i_drpo.DrugRequest_id = drr.DrugRequest_id and
						i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id and
						isnull(i_drpo.Tradenames_id, 0) = isnull(drr.TRADENAMES_id, 0)													
				) drpo
			";
			$filters[] = "drr.DrugRequestRow_delDT is null";
		}
		if (!empty($data['DrugFinance_id'])) {
			$filters[] = "df.DrugFinance_id = :DrugFinance_id";
			$data['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
        	select
        		-- select
        		drr.DrugRequestRow_id,
        		drr.DrugComplexMnn_id,
        		dcmn.DrugComplexMnnName_Name,
        		drr.TRADENAMES_id,
        		tn.NAME as Tradenames_Name,
        		cdf.NAME as ClsDrugForms_Name,
        		dcmd.DrugComplexMnnDose_Name,
        		dcmf.DrugComplexMnnFas_Name,
        		case when df.DrugFinance_Name is not null then df.DrugFinance_Name else DrugListRequest_Data.DrugFinance_Name end as DrugFinance_Name,
        		drr.DrugRequestRow_Kolvo,
				DrugListRequest_Data.Price as DrugRequestRow_Price,
				drr.DrugRequestRow_Summa,
				convert(varchar(10), drr.DrugRequestRow_insDT, 104) as DrugRequestRow_insDT,
				convert(varchar(10), drr.DrugRequestRow_updDT, 104) as DrugRequestRow_updDT,
				convert(varchar(10), drr.DrugRequestRow_delDT, 104) as DrugRequestRow_delDT,
			    DrugListRequest_Data.DrugListRequest_Comment,
				DrugListRequest_Data.isProblem,
				DrugListRequest_Data.isProblemTorg,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				NTFR_Data.NAME as NTFR_Name
				-- end select
			from
				-- from
				DrugRequestRow drr with (nolock)
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = drr.TRADENAMES_ID
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
				outer apply (
					select top 1
						(case
							when dlrt.DrugRequest_Price > 0 then dlrt.DrugRequest_Price
							else dlr.DrugListRequest_Price
						end) as Price,
			            dlr.DrugListRequest_Comment,
						isnull(isProblem.YesNo_Code, 0) as isProblem,
						isnull(isProblemTorg.YesNo_Code, 0) as isProblemTorg,
						df2.DrugFinance_Name
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = drr.TRADENAMES_id
						left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
						left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = dlrt.DrugListRequestTorg_IsProblem
						left join v_DrugRequestProperty drp with (nolock) on drp.DrugRequestProperty_id = dlr.DrugRequestProperty_id
						left join v_DrugFinance df2 with (nolock) on df2.DrugFinance_id = drp.DrugFinance_id
					where
						".$whereDrugRequestProperty." and
						dlr.DrugComplexMnn_id = drr.DrugComplexMnn_id
				) as DrugListRequest_Data
				outer apply (
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Data
				{$join}
				-- end from
			{$where}
			order by
				-- order by
				dcmn.DrugComplexMnnName_Name
				-- end order by
    	";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $data);
		if (is_object($result) && $count !== false) {
			return array(
				'data' => $result->result('array'),
				'totalCount' => $count
			);
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного медикамента
	 */
	function loadMzDrugRequestDrugPersonGrid($data) {
		$filters = array();
		$where = "";

		$filters[] = "drpo.DrugRequest_id = :DrugRequest_id";
		$filters[] = "(drpo.DrugComplexMnn_id is not null or drpo.Tradenames_id is not null)";

		if (!empty($data['DrugComplexMnn_id'])) {
			$filters[] = "drpo.DrugComplexMnn_id = :DrugComplexMnn_id";
			$filters[] = "isnull(drpo.Tradenames_id, 0) = isnull(:Tradenames_id, 0)";
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
			select
				drpo.DrugRequestPersonOrder_id,
				drpo.Person_id,
				isnull(ps.Person_SurName+' ', '') + isnull(ps.Person_FirName+' ', '') + isnull(ps.Person_SecName, '') as Person_Fio,
				isnull(rtrim(l.Lpu_Nick)+' ', '') + isnull(rtrim(pcard.LpuRegion_Name), '') as Lpu_Information,
				drpo.DrugRequestPersonOrder_OrdKolvo,
				mp.Person_Fio as MedPersonal_Name,
				isnull(convert(varchar(10), DrugRequestPersonOrder_begDate, 104), '') + ' - ' + isnull(convert(varchar(10), DrugRequestPersonOrder_endDate, 104), '') as DrugRequestPersonOrder_Period
			from
				v_DrugRequestPersonOrder drpo with (nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = drpo.Person_id
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drpo.MedPersonal_id
				outer apply (
					select top 1
						pc.Lpu_id,
						pc.LpuRegion_id,
						pc.LpuRegion_Name
					from
						v_PersonCard pc with (nolock)
					where
						pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
					order by
						PersonCard_begDate desc
				) as pcard
				left join v_Lpu l with (nolock) on pcard.Lpu_id = l.Lpu_id
			{$where}
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком медикаментов для заявки
	 */
	function loadMzDrugRequestDrugListGrid($data) {
		$filters = array();
		$where = "";
		$double = false;
		$data['drp_fed'] = 0;
		$data['drp_reg'] = 0;

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $data['DrugRequest_id']));
		$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		if (
			empty($data['DrugRequestProperty_id']) 
			&& empty($drugRequestProperty['DrugRequestPropertyFed_id'])
			&& empty($drugRequestProperty['DrugRequestPropertyReg_id'])
		) {
			return false;
		}

		if (empty($data['DrugRequestProperty_id'])) {
			// Сперва возьмем по федеральному бюджету
			$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestPropertyFed_id'];
			$double = true;
			$data['drp_fed'] = $drugRequestProperty['DrugRequestPropertyFed_id'];
		}

		if (!empty($data['Tradenames_Name'])) {
			$filters[] = "tn.NAME like :Tradenames_Name";
			$data['Tradenames_Name'] = '%'.$data['Tradenames_Name'].'%';
		}
		if (!empty($data['ClsDrugForms_Name'])) {
			$filters[] = "cdf.NAME like :ClsDrugForms_Name";
			$data['ClsDrugForms_Name'] = '%'.$data['ClsDrugForms_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnName_Name'])) {
			$filters[] = "dcmn.DrugComplexMnnName_Name like :DrugComplexMnnName_Name";
			$data['DrugComplexMnnName_Name'] = '%'.$data['DrugComplexMnnName_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnDose_Name'])) {
			$filters[] = "dcmd.DrugComplexMnnDose_Name like :DrugComplexMnnDose_Name";
			$data['DrugComplexMnnDose_Name'] = '%'.$data['DrugComplexMnnDose_Name'].'%';
		}
		if (!empty($data['DrugComplexMnnFas_Name'])) {
			$filters[] = "dcmf.DrugComplexMnnFas_Name like :DrugComplexMnnFas_Name";
			$data['DrugComplexMnnFas_Name'] = '%'.$data['DrugComplexMnnFas_Name'].'%';
		}
		if (!empty($data['DrugFinance_id'])) {
			if($data['DrugFinance_id'] == 3){
				// Просто отключим запрос по региональному списку
				$double = false;
			} else if ($data['DrugFinance_id'] == 27){
				$double = false;
				$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
				$data['drp_fed'] = 0;
				$data['drp_reg'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
			}
		}

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
        	select
        		-- select
        		dlr.DrugListRequest_id,
        		dlr.DrugComplexMnn_id,
        		dcmn.DrugComplexMnnName_Name,
        		dlr.TRADENAMES_id,
        		tn.NAME as Tradenames_Name,
        		cdf.NAME as ClsDrugForms_Name,
        		dcmd.DrugComplexMnnDose_Name,
        		dcmf.DrugComplexMnnFas_Name,
        		DrugRequestRow_Data.DrugRequestRow_Kolvo,
        		case 
					when DrugRequestRow_Data.DrugFinance_Name is not null then DrugRequestRow_Data.DrugFinance_Name
					when dlr.DrugFinance_Name is not null then dlr.DrugFinance_Name
					else case 
						when :drp_fed = :DrugRequestProperty_id then 'Федеральный бюджет'
						when :drp_reg = :DrugRequestProperty_id then 'Региональный бюджет'
						else drProp_Data.DrugFinance_Name
					end
				end as DrugFinance_Name,
				case 
					when DrugRequestRow_Data.DrugFinance_Name is not null then DrugRequestRow_Data.DrugFinance_id
					when dlr.DrugFinance_id is not null then dlr.DrugFinance_id
					else case 
						when :drp_fed = :DrugRequestProperty_id then 3
						when :drp_reg = :DrugRequestProperty_id then 27
						else drProp_Data.DrugFinance_id
					end
				end as DrugFinance_id,
        		(case
					when dlr.DrugRequest_Price > 0 then dlr.DrugRequest_Price
					else dlr.DrugListRequest_Price
				end) as DrugRequestRow_Price,
				dlr.DrugListRequest_Comment,
				isnull(isProblem.YesNo_Code, 0) as isProblem,
				isnull(isProblemTorg.YesNo_Code, 0) as isProblemTorg,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				NTFR_Data.NAME as NTFR_Name
				-- end select
			from
				-- from
				(
					select
						(cast(dlr.DrugListRequest_id as varchar) + isnull('_'+cast(dlrt.DrugListRequestTorg_id as varchar), '')) as DrugListRequest_id,
						dlr.DrugComplexMnn_id,
						dlrt.TRADENAMES_id,
						dlr.DrugListRequest_Comment,
						dlr.DrugListRequest_IsProblem,
						dlrt.DrugListRequestTorg_IsProblem,
						dlr.DrugListRequest_Price,
						dlrt.DrugRequest_Price,
						innerDrugRequestRow_Data.DrugFinance_id,
						innerDrugRequestRow_Data.DrugFinance_Name
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id is not null
						outer apply (
							select top 1
								idrr.DrugFinance_id,
								idf.DrugFinance_Name
							from
								v_DrugRequestRow idrr with (nolock)
								left join v_DrugFinance idf with (nolock) on idf.DrugFinance_id = idrr.DrugFinance_id
							where
								idrr.person_id is null and
								idrr.DrugRequest_id = :DrugRequest_id and
								idrr.DrugComplexMnn_id = dlr.DrugComplexMnn_id 
								/*and isnull(idrr.TRADENAMES_id, 0) = isnull(dlrt.TRADENAMES_id, 0)*/
						) as innerDrugRequestRow_Data
					where
						 dlr.DrugRequestProperty_id = :DrugRequestProperty_id
					union select
						cast(dlr.DrugListRequest_id as varchar) as DrugListRequest_id,
						dlr.DrugComplexMnn_id,
						null as TRADENAMES_id,
						dlr.DrugListRequest_Comment,
						dlr.DrugListRequest_IsProblem,
						null as DrugListRequestTorg_IsProblem,
						dlr.DrugListRequest_Price,
						null as DrugRequest_Price,
						innerDrugRequestRow_Data.DrugFinance_id,
						innerDrugRequestRow_Data.DrugFinance_Name
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id is not null
						outer apply (
							select top 1
								idrr.DrugFinance_id,
								idf.DrugFinance_Name
							from
								v_DrugRequestRow idrr with (nolock)
								left join v_DrugFinance idf with (nolock) on idf.DrugFinance_id = idrr.DrugFinance_id
							where
								idrr.person_id is null and
								idrr.DrugRequest_id = :DrugRequest_id and
								idrr.DrugComplexMnn_id = dlr.DrugComplexMnn_id 
								/*and isnull(idrr.TRADENAMES_id, 0) = isnull(dlrt.TRADENAMES_id, 0)*/
						) as innerDrugRequestRow_Data
					where
						dlr.DrugRequestProperty_id = :DrugRequestProperty_id and
						dlrt.DrugListRequestTorg_id is not null
				) dlr
				left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
				left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = dlr.DrugListRequestTorg_IsProblem
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = dlr.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = dlr.TRADENAMES_ID
				outer apply (
					select top 1
						drr.DrugRequestRow_Kolvo,
						drr.DrugFinance_id,
						df.DrugFinance_Name
					from
						v_DrugRequestRow drr with (nolock)
						left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
					where
						drr.person_id is null and
						drr.DrugRequest_id = :DrugRequest_id and
						drr.DrugComplexMnn_id = dlr.DrugComplexMnn_id and
						isnull(drr.TRADENAMES_id, 0) = isnull(dlr.TRADENAMES_id, 0)
				) as DrugRequestRow_Data
				outer apply (
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Data
				outer apply (
					select top 1
						drprop.DrugFinance_id,
						dfprop.DrugFinance_Name
					from
						v_DrugRequestProperty drprop with(nolock)
						left join v_DrugFinance dfprop with (nolock) on dfprop.DrugFinance_id = drprop.DrugFinance_id
					where
						drprop.DrugRequestProperty_id = :DrugRequestProperty_id
				) as drProp_Data
				-- end from
				{$where}
				order by
					-- order by
					dcmn.DrugComplexMnnName_Name
					-- end order by
    	";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $data);
		if($double) {
			if (is_object($result)) {
				$result1 = array();
				$result2 = array();
				$totalCount = 0;
				if($count !== false){
					$result1 = $result->result('array');
					$totalCount = $count;
				}
				// Теперь возьмем по региональному бюджету
				$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
				$data['drp_fed'] = 0;
				$data['drp_reg'] = $drugRequestProperty['DrugRequestPropertyReg_id'];
				$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
				$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $data);
				if (is_object($result)) {
					if($count !== false){
						$result2 = $result->result('array');
						$totalCount += $count;
					}
				} else {
					return false;
				}
				$res = array_merge($result1,$result2);
				return array(
					'data' => $res,
					'totalCount' => $totalCount
				);
			} else {
				return false;
			}
		} else {
			if (is_object($result) && $count !== false) {
				return array(
					'data' => $result->result('array'),
					'totalCount' => $count
				);
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка грида со списком пациентов
	 */
	function loadMzDrugRequestPersonGrid($data) {
		$region = $_SESSION['region']['nick'];
        $with_arr = array();
        $join_arr = array();
		$filters = array();
        $with = "";
		$join = "";
		$where = "";

		$filters[] = "drpo.DrugRequest_id = :DrugRequest_id";
		$filters[] = "drpo.DrugComplexMnn_id is null";
		$filters[] = "drpo.Tradenames_id is null";

		if (!empty($data['Person_SurName'])) {
			$filters[] = "ps.Person_SurName like :Person_SurName";
			$data['Person_SurName'] = $data['Person_SurName'].'%';
		}
		if (!empty($data['Person_FirName'])) {
			$filters[] = "ps.Person_FirName like :Person_FirName";
			$data['Person_FirName'] = $data['Person_FirName'].'%';
		}
		if (!empty($data['Person_SecName'])) {
			$filters[] = "ps.Person_SecName like :Person_SecName";
			$data['Person_SecName'] = $data['Person_SecName'].'%';
		}
		if (!empty($data['ShowPersonOnlyWthoutDrug'])) {
			$dr_join = "";
			$dr_where = "";

			if ($region == 'ufa') { //для уфы отображаем медикаменты из разнарядок только внутри своей ЛПУ
				$dr_where .= " and dr2.Lpu_id = dr.Lpu_id";
			} else { //для остальных регионов, отображаем медикаменты по пациенту из своей МО и других МО заявочной кампании (кроме "закрытых")
				$dr_join .= "
					outer apply (
						select top 1
							i_aro.AccessRightsName_id
						from
							v_Lpu i_l with (nolock)
							left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
						where
							i_l.Lpu_id = dr2.Lpu_id
					) arl
				";
				$dr_where .= " and (dr2.Lpu_id = dr.Lpu_id or arl.AccessRightsName_id is null)";
			}

            $with_arr[] = "
                dr_list(DrugRequest_id) as (
                    select
                        dr2.DrugRequest_id
                    from
                        v_DrugRequest dr with(nolock)
                        left join v_DrugRequest dr2 with (nolock) on
                            dr2.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(dr2.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(dr2.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            isnull(dr2.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            isnull(dr2.DrugRequest_Version, 0) = isnull(dr.DrugRequest_Version, 0) and 
                            dr2.DrugRequestCategory_id = dr.DrugRequestCategory_id
                    	{$dr_join}
                    where
                        dr.DrugRequest_id = :DrugRequest_id
                        {$dr_where}
                )
            ";
            $join_arr[] = "
                outer apply (
                    select top 1
                        ii_drpo.DrugRequestPersonOrder_id
                    from
                        v_DrugRequestPersonOrder ii_drpo with(nolock)
                        inner join dr_list ii_dl on ii_dl.DrugRequest_id = ii_drpo.DrugRequest_id
                    where
                        ii_drpo.Person_id = drpo.Person_id and
                        (
                            ii_drpo.DrugComplexMnn_id is not null or
                            ii_drpo.Tradenames_id is not null
                        )
                ) drpo_wd
            ";
			$filters[] = "drpo_wd.DrugRequestPersonOrder_id is null";
		}

		if (count($with_arr) > 0) {
            $with = "
				-- addit with
				with
					".join(", ", $with_arr)."
				-- end addit with
			";
		}

		if (count($join_arr) > 0) {
            $join = join(" ", $join_arr);
		}

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
		    {$with}
			select
				-- select
				drpo.DrugRequestPersonOrder_id,
				drpo.Person_id,
				rtrim(ps.Person_SurName) as Person_SurName,
				rtrim(ps.Person_FirName) as Person_FirName,
				rtrim(ps.Person_SecName) as Person_SecName,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				rtrim(l.Lpu_Nick) as Lpu_Nick,
				rtrim(pcard.LpuRegion_Name) as LpuRegion_Name,
				(case when PRYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuse,
				(case when PRNextYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuseNext,
				(case when PRPeriodYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuseCurr,
				(case when fedl.Person_id is not null then 'true' else 'false' end) as Person_IsFedLgot,
				(case when fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null then 'true' else 'false' end) as Person_IsFedLgotCurr,
				(case when regl.OwnLpu = 1 then 'true' else (case when regl.OwnLpu is not null then 'gray' else 'false' end) end) as Person_IsRegLgot,
				(case when reg2.OwnLpu = 1 and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) then 'true' else (case when reg2.OwnLpu is not null and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) then 'gray' else 'false' end) end) as Person_IsRegLgotCurr,
				(case when disp.OwnLpu = 1 then 'true' else (case when disp.OwnLpu is not null then 'gray' else 'false' end) end) as Person_Is7Noz,
				(case when ps.Server_pid = 0 then 'true' else 'false' end) as Person_IsBDZ,
				convert(varchar(10), DrugRequestPersonOrder_insDT, 104) as DrugRequestPersonOrder_insDT,
				convert(varchar(10), DrugRequestPersonOrder_updDT, 104) as DrugRequestPersonOrder_updDT,
				drpo_cnt.cnt as DrugRequestPersonOrder_Count
				-- end select
			from
				-- from
				v_DrugRequestPersonOrder drpo with (nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = drpo.Person_id
				left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drpo.DrugRequest_id
				left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				outer apply (
					select top 1
						pc.Lpu_id,
						pc.LpuRegion_id,
						pc.LpuRegion_Name
					from
						v_PersonCard pc with (nolock)
					where
						pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
					order by
						PersonCard_begDate desc
				) as pcard
				left join v_Lpu l with (nolock) on pcard.Lpu_id = l.Lpu_id
				left join v_PersonRefuse PRYear with (nolock) on PRYear.Person_id = ps.Person_id and PRYear.PersonRefuse_IsRefuse = 2 and PRYear.PersonRefuse_Year = year(dbo.tzGetDate())
				left join v_PersonRefuse PRNextYear with (nolock) on PRNextYear.Person_id = ps.Person_id and PRNextYear.PersonRefuse_IsRefuse = 2 and PRNextYear.PersonRefuse_Year = (year(dbo.tzGetDate())+1)
				left join v_PersonRefuse PRPeriodYear with (nolock) on PRPeriodYear.Person_id = ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse = 2 and PRPeriodYear.PersonRefuse_Year = year(drp.DrugRequestPeriod_begDate)
				outer apply (
					select
						count(DrugRequestPersonOrder_id) as cnt
					from
						v_DrugRequestPersonOrder i_drpo with (nolock)
					where
						i_drpo.DrugRequest_id = drpo.DrugRequest_id and
						i_drpo.Person_id = drpo.Person_id and
						i_drpo.Person_id = drpo.Person_id and
						(
							i_drpo.DrugComplexMnn_id is not null or
							i_drpo.Tradenames_id is not null
						)
				) drpo_cnt
				outer apply (
					select top
						1 Person_id
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 1 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as fedl
				outer apply (
					select top 1
						Person_id
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 1 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as fed2
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 2 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as regl
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 2 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as reg2
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						v_PersonDisp with (nolock)
					where
						Person_id = ps.Person_id and
						(
							PersonDisp_endDate is null or
							PersonDisp_endDate > dbo.tzGetDate()
						) and
						Sickness_id is not null
				) as disp
				{$join}
				-- end from
			{$where}
			order by
				-- order by
				ps.Person_SurName, ps.Person_FirName, ps.Person_SecName
				-- end order by
		";

		$count_query = "
		    {$with}
			select
				count(drpo.DrugRequestPersonOrder_id) as cnt
			from
				v_DrugRequestPersonOrder drpo with (nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = drpo.Person_id
				{$join}
			{$where}
		";

		$result = $this->db->query($query, array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName']
		));

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$count = $this->getFirstResultFromQuery($count_query, $data);
		if (is_object($result) && $count !== false) {
			return array(
				'data' => $result->result('array'),
				'totalCount' => $count
			);
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком пациентов (аналитика персональной потребности)
	 */
	function loadMzDrugRequestFirstCopyGrid($data) {
		$region = $_SESSION['region']['nick'];
        $with_arr = array();
        $join_arr = array();
		$filters = array();
        $with = "";
		$join = "";
		$where = "";

		$filters[] = "drpo.DrugRequest_id = :DrugRequestFirstCopy_id"; //берем "первую копию" за освнову, так как в ней не могут отстутсвовать пациенты из заявки врача (в теории) т.е. она более полная
		$filters[] = "drpo.DrugComplexMnn_id is null";
		$filters[] = "drpo.Tradenames_id is null";

		if (!empty($data['Person_SurName'])) {
			$filters[] = "ps.Person_SurName like :Person_SurName";
			$data['Person_SurName'] = $data['Person_SurName'].'%';
		}
		if (!empty($data['Person_FirName'])) {
			$filters[] = "ps.Person_FirName like :Person_FirName";
			$data['Person_FirName'] = $data['Person_FirName'].'%';
		}
		if (!empty($data['Person_SecName'])) {
			$filters[] = "ps.Person_SecName like :Person_SecName";
			$data['Person_SecName'] = $data['Person_SecName'].'%';
		}
		if (!empty($data['ShowPersonOnlyWthoutDrug'])) {
			$dr_join = "";
			$dr_where = "";

			if ($region == 'ufa') { //для уфы отображаем медикаменты из разнарядок только внутри своей ЛПУ
				$dr_where .= " and dr2.Lpu_id = dr.Lpu_id";
			} else { //для остальных регионов, отображаем медикаменты по пациенту из своей МО и других МО заявочной кампании (кроме "закрытых")
				$dr_join .= "
					outer apply (
						select top 1
							i_aro.AccessRightsName_id
						from
							v_Lpu i_l with (nolock)
							left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
						where
							i_l.Lpu_id = dr2.Lpu_id
					) arl
				";
				$dr_where .= " and (dr2.Lpu_id = dr.Lpu_id or arl.AccessRightsName_id is null)";
			}

            $with_arr[] = "
                dr_list(DrugRequest_id) as (
                    select
                        dr2.DrugRequest_id
                    from
                        v_DrugRequest dr with(nolock)
                        left join v_DrugRequest dr2 with (nolock) on
                            dr2.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
                            isnull(dr2.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
                            isnull(dr2.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
                            isnull(dr2.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
                            isnull(dr2.DrugRequest_Version, 0) = isnull(dr.DrugRequest_Version, 0) and
                            dr2.DrugRequestCategory_id = dr.DrugRequestCategory_id
                    	{$dr_join}
                    where
                        dr.DrugRequest_id = :DrugRequest_id
                        {$dr_where}
                )
            ";
            $join_arr[] = "
                outer apply (
                    select top 1
                        ii_drpo.DrugRequestPersonOrder_id
                    from
                        v_DrugRequestPersonOrder ii_drpo with(nolock)
                        inner join dr_list ii_dl on ii_dl.DrugRequest_id = ii_drpo.DrugRequest_id
                    where
                        ii_drpo.Person_id = drpo.Person_id and
                        (
                            ii_drpo.DrugComplexMnn_id is not null or
                            ii_drpo.Tradenames_id is not null
                        )
                ) drpo_wd
            ";
			$filters[] = "drpo_wd.DrugRequestPersonOrder_id is null";
		}

		if (count($with_arr) > 0) {
            $with = "
				-- addit with
				with
					".join(", ", $with_arr)."
				-- end addit with
			";
		}

		if (count($join_arr) > 0) {
            $join = join(" ", $join_arr);
		}

		if (count($filters) > 0) {
			$where = "
				where
					-- where
					".join(" and ", $filters)."
					-- end where
			";
		}

		$query = "
		    {$with}
			select
				-- select
				'true' as RP,
				(case when drpo_mp.DrugRequestPersonOrder_id is not null then 'true' else 'false' end) as LP,
				drpo.DrugRequestPersonOrder_id,
				drpo.Person_id,
				rtrim(ps.Person_SurName) as Person_SurName,
				rtrim(ps.Person_FirName) as Person_FirName,
				rtrim(ps.Person_SecName) as Person_SecName,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				rtrim(l.Lpu_Nick) as Lpu_Nick,
				rtrim(pcard.LpuRegion_Name) as LpuRegion_Name,
				(case when PRYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuse,
				(case when PRNextYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuseNext,
				(case when PRPeriodYear.PersonRefuse_id is not null then 'true' else 'false' end) as Person_IsRefuseCurr,
				(case when fedl.Person_id is not null then 'true' else 'false' end) as Person_IsFedLgot,
				(case when fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null then 'true' else 'false' end) as Person_IsFedLgotCurr,
				(case when regl.OwnLpu = 1 then 'true' else (case when regl.OwnLpu is not null then 'gray' else 'false' end) end) as Person_IsRegLgot,
				(case when reg2.OwnLpu = 1 and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) then 'true' else (case when reg2.OwnLpu is not null and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) then 'gray' else 'false' end) end) as Person_IsRegLgotCurr,
				(case when disp.OwnLpu = 1 then 'true' else (case when disp.OwnLpu is not null then 'gray' else 'false' end) end) as Person_Is7Noz,
				(case when ps.Server_pid = 0 then 'true' else 'false' end) as Person_IsBDZ,
				convert(varchar(10), DrugRequestPersonOrder_insDT, 104) as DrugRequestPersonOrder_insDT,
				convert(varchar(10), DrugRequestPersonOrder_updDT, 104) as DrugRequestPersonOrder_updDT,
				drpo_cnt.cnt as DrugRequestPersonOrder_Count
				-- end select
			from
				-- from
				v_DrugRequestPersonOrder drpo with (nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = drpo.Person_id
				left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drpo.DrugRequest_id
				left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				outer apply (
					select top 1
						pc.Lpu_id,
						pc.LpuRegion_id,
						pc.LpuRegion_Name
					from
						v_PersonCard pc with (nolock)
					where
						pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
					order by
						PersonCard_begDate desc
				) as pcard
				left join v_Lpu l with (nolock) on pcard.Lpu_id = l.Lpu_id
				left join v_PersonRefuse PRYear with (nolock) on PRYear.Person_id = ps.Person_id and PRYear.PersonRefuse_IsRefuse = 2 and PRYear.PersonRefuse_Year = year(dbo.tzGetDate())
				left join v_PersonRefuse PRNextYear with (nolock) on PRNextYear.Person_id = ps.Person_id and PRNextYear.PersonRefuse_IsRefuse = 2 and PRNextYear.PersonRefuse_Year = (year(dbo.tzGetDate())+1)
				left join v_PersonRefuse PRPeriodYear with (nolock) on PRPeriodYear.Person_id = ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse = 2 and PRPeriodYear.PersonRefuse_Year = year(drp.DrugRequestPeriod_begDate)
				outer apply (
					select
						count(DrugRequestPersonOrder_id) as cnt
					from
						v_DrugRequestPersonOrder i_drpo with (nolock)
					where
						i_drpo.DrugRequest_id = drpo.DrugRequest_id and
						i_drpo.Person_id = drpo.Person_id and
						i_drpo.Person_id = drpo.Person_id and
						(
							i_drpo.DrugComplexMnn_id is not null or
							i_drpo.Tradenames_id is not null
						)
				) drpo_cnt
				outer apply (
					select top
						1 Person_id
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 1 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as fedl
				outer apply (
					select top 1
						Person_id
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 1 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as fed2
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 2 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as regl
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						PersonPrivilege reg with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
						reg.Person_id = ps.Person_id and
						pt.ReceptFinance_id = 2 and
						reg.PersonPrivilege_begDate <= dbo.tzGetDate() and
						(
							reg.PersonPrivilege_endDate is null or
							reg.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
						)
				) as reg2
				outer apply (
					select
						max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from
						v_PersonDisp with (nolock)
					where
						Person_id = ps.Person_id and
						(
							PersonDisp_endDate is null or
							PersonDisp_endDate > dbo.tzGetDate()
						) and
						Sickness_id is not null
				) as disp
				outer apply (
				    select top 1
				        i_drpo.DrugRequestPersonOrder_id
				    from
				        v_DrugRequestPersonOrder i_drpo with (nolock)
				    where
				        i_drpo.DrugRequest_id = :DrugRequest_id and
				        i_drpo.Person_id = drpo.Person_id
				) drpo_mp -- разнарядка из заявки врача
				{$join}
				-- end from
			{$where}
			order by
				-- order by
				ps.Person_SurName, ps.Person_FirName, ps.Person_SecName
				-- end order by
		";

		$count_query = "
		    {$with}
			select
				count(drpo.DrugRequestPersonOrder_id) as cnt
			from
				v_DrugRequestPersonOrder drpo with (nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = drpo.Person_id
				{$join}
			{$where}
		";

		$result = $this->db->query($query, array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'DrugRequestFirstCopy_id' => $data['DrugRequestFirstCopy_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName']
		));

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$count = $this->getFirstResultFromQuery($count_query, $data);
		if (is_object($result) && $count !== false) {
			return array(
				'data' => $result->result('array'),
				'totalCount' => $count
			);
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного пациента
	 */
	function loadMzDrugRequestPersonDrugGrid($data) {
		$region = $_SESSION['region']['nick'];
		$filters = array();
		$join = "";
		$where = "";

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $data['DrugRequest_id']));
		$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		if (empty($data['DrugRequestProperty_id'])) {
			$data['DrugRequestProperty_id'] = null;
		}

        $filters[] = "dr.DrugRequestPeriod_id = @DrugRequestPeriod_id";
		$filters[] = "isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0)";
		$filters[] = "isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0)";
		$filters[] = "isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0)";
		$filters[] = "dr.DrugRequestCategory_id = @DrugRequestCategory_id";
		$filters[] = "(drpo.DrugComplexMnn_id is not null or drpo.Tradenames_id is not null)";
		$filters[] = "isnull(dr.DrugRequest_Version, 0) = isnull(@DrugRequest_Version, 0)";

		if ($region == 'ufa') { //для уфы отображаем медикаменты из разнарядок только внутри своей ЛПУ
			$filters[] = "dr.Lpu_id = @Lpu_id";
		} else { //для остальных регионов, отображаем медикаменты по пациенту из своей МО и других МО заявочной кампании (кроме "закрытых")
			$join .= "
				outer apply (
					select top 1
						i_aro.AccessRightsName_id
					from
						v_Lpu i_l with (nolock)
						left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
					where
						i_l.Lpu_id = dr.Lpu_id
				) arl
			";
			$filters[] = "(dr.Lpu_id = @Lpu_id or arl.AccessRightsName_id is null)";
		}

		if (!empty($data['Person_id'])) {
			$filters[] = "drpo.Person_id = :Person_id";
		} else {
            return false;
        }

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
		    declare
                @DrugRequestPeriod_id bigint = null,
                @PersonRegisterType_id bigint = null,
                @DrugRequestKind_id bigint = null,
                @DrugGroup_id bigint = null,
                @DrugRequestCategory_id bigint = null,
                @DrugRequest_Version int = null,
                @Lpu_id bigint = null;

            select
                @DrugRequestPeriod_id = DrugRequestPeriod_id,
                @PersonRegisterType_id = PersonRegisterType_id,
                @DrugRequestKind_id = DrugRequestKind_id,
                @DrugGroup_id = DrugGroup_id,
                @DrugRequestCategory_id = DrugRequestCategory_id,
                @DrugRequest_Version = DrugRequest_Version,
                @Lpu_id = Lpu_id
            from
                v_DrugRequest with (nolock)
            where
                DrugRequest_id = :DrugRequest_id;

			select
        		drpo.DrugRequestPersonOrder_id,
        		drpo.DrugRequest_id,
        		drpo.DrugComplexMnn_id,
        		dcmn.DrugComplexMnnName_Name,
        		drpo.Tradenames_id,
        		tn.NAME as Tradenames_Name,
				EvnVK.EvnVK_id,
				case 
					when EvnVK.EvnVK_id is not null
						then '№' + isnull(convert(varchar, EvnVK_NumProtocol), '_') + ' от ' + isnull(convert(varchar, EvnVK_setDate, 104), '')
					else null
				end as protokolVK_name,
        		cdf.NAME as ClsDrugForms_Name,
        		dcmd.DrugComplexMnnDose_Name,
        		dcmf.DrugComplexMnnFas_Name,
        		DrugRequestRow_Data.DrugFinance_Name,
        		drpo.DrugRequestPersonOrder_OrdKolvo,
        		drpo.DrugRequestPersonOrder_Kolvo,
        		mp.Person_Fio as MedPersonal_FIO,
        		(
        		    isnull(mp.Person_Fin, '')+
        		    isnull(' '+mp.Dolgnost_Name, '')
        		) as MedPersonal_FullInf,
				convert(varchar(10), drpo.DrugRequestPersonOrder_begDate, 104) as DrugRequestPersonOrder_begDate,
				convert(varchar(10), drpo.DrugRequestPersonOrder_endDate, 104) as DrugRequestPersonOrder_endDate,
				dret.DrugRequestExceptionType_Name,
			    DrugListRequest_Data.DrugListRequest_Comment,
				DrugListRequest_Data.isProblem,
				DrugListRequest_Data.isProblemTorg,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				(
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Name
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestPersonOrder drpo with (nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
				left join v_DrugRequestExceptionType dret with (nolock) on dret.DrugRequestExceptionType_id = drpo.DrugRequestExceptionType_id
				outer apply (
				    select top 1
				        *
				    from
				        v_MedPersonal i_mp with (nolock)
				    where
				        i_mp.MedPersonal_id = drpo.MedPersonal_id
				    order by
				        i_mp.MedPersonal_id
				) mp
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drpo.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = drpo.Tradenames_id
				outer apply (
					select top 1
			            dlr.DrugListRequest_Comment,
						isnull(isProblem.YesNo_Code, 0) as isProblem,
						isnull(isProblemTorg.YesNo_Code, 0) as isProblemTorg
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = drpo.Tradenames_id
						left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
						left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = dlrt.DrugListRequestTorg_IsProblem
					where
						dlr.DrugRequestProperty_id = :DrugRequestProperty_id and
						dlr.DrugComplexMnn_id = drpo.DrugComplexMnn_id
				) as DrugListRequest_Data
				outer apply (
					select top 1
						df.DrugFinance_Name
					from 
						v_DrugRequestRow drr with (nolock)
						left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id 
					where
						drr.DrugRequest_id = drpo.DrugRequest_id and
						(drr.DrugComplexMnn_id = drpo.DrugComplexMnn_id or drr.TRADENAMES_id = drpo.Tradenames_id)
				) as DrugRequestRow_Data
				{$join}
				left join v_EvnVK EvnVK on EvnVK.EvnVK_id = drpo.EvnVK_id
				{$where}
			order by
					dcmn.DrugComplexMnnName_Name
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного пациента (аналитика персональной потребности)
	 */
	function loadMzDrugRequestFirstCopyDrugGrid($data) {
		$region = $_SESSION['region']['nick'];
		$filters = array();
		$where = "";
		$join = "";

		$drugRequestProperty = $this->getDrugRequestPropertyId(array('DrugRequest_id' => $data['DrugRequest_id']));
		$data['DrugRequestProperty_id'] = $drugRequestProperty['DrugRequestProperty_id'];
		if (empty($data['DrugRequestProperty_id'])) {
			$data['DrugRequestProperty_id'] = null;
		}

        //$filters[] = "dr.DrugRequest_id = :DrugRequestFirstCopy_id"; //берем "первую копию" за освнову, так как в ней не могут отстутсвовать пациенты из заявки врача (в теории) т.е. она более полная
        $filters[] = "dr.DrugRequestPeriod_id = @DrugRequestPeriod_id";
		$filters[] = "isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0)";
		$filters[] = "isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0)";
		$filters[] = "isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0)";
		$filters[] = "dr.DrugRequestCategory_id = @DrugRequestCategory_id";
		$filters[] = "dr.DrugRequest_Version = '1'";
		$filters[] = "(drpo.DrugComplexMnn_id is not null or drpo.Tradenames_id is not null)";

		if ($region == 'ufa') { //для уфы отображаем медикаменты из разнарядок только внутри своей ЛПУ
			$filters[] = "dr.Lpu_id = @Lpu_id";
		} else { //для остальных регионов, отображаем медикаменты по пациенту из своей МО и других МО заявочной кампании (кроме "закрытых")
			$join .= "
				outer apply (
					select top 1
						i_aro.AccessRightsName_id
					from
						v_Lpu i_l with (nolock)
						left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
					where
						i_l.Lpu_id = dr.Lpu_id
				) arl
			";
			$filters[] = "(dr.Lpu_id = @Lpu_id or arl.AccessRightsName_id is null)";
		}

		if (!empty($data['Person_id'])) {
			$filters[] = "drpo.Person_id = :Person_id";
		}

		if (count($filters) > 0) {
			$where = "where ".join(" and ", $filters);
		}

		$query = "
		    declare
                @DrugRequestPeriod_id bigint = null,
                @PersonRegisterType_id bigint = null,
                @DrugRequestKind_id bigint = null,
                @DrugGroup_id bigint = null,
                @DrugRequestCategory_id bigint = null,
                @Lpu_id bigint = null;

            select
                @DrugRequestPeriod_id = DrugRequestPeriod_id,
                @PersonRegisterType_id = PersonRegisterType_id,
                @DrugRequestKind_id = DrugRequestKind_id,
                @DrugGroup_id = DrugGroup_id,
                @DrugRequestCategory_id = DrugRequestCategory_id,
                @Lpu_id = Lpu_id
            from
                v_DrugRequest with (nolock)
            where
                DrugRequest_id = :DrugRequestFirstCopy_id;

            with mp_request_list (DrugRequest_id) as (
                select
                    DrugRequest_id
                from
                    v_DrugRequest with (nolock)
                where
                    DrugRequestPeriod_id = @DrugRequestPeriod_id and
                    isnull(PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                    isnull(DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                    isnull(DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                    DrugRequestCategory_id = @DrugRequestCategory_id and
                    DrugRequest_Version is null
            )
			select
        		drpo.DrugRequestPersonOrder_id,
        		drpo.DrugRequest_id,
        		drpo.DrugComplexMnn_id,
        		dcmn.DrugComplexMnnName_Name,
        		drpo.Tradenames_id,
        		tn.NAME as Tradenames_Name,
        		--cdf.NAME as ClsDrugForms_Name,
        		--dcmd.DrugComplexMnnDose_Name,
        		--dcmf.DrugComplexMnnFas_Name,
        		DrugRequestRow_Data.DrugFinance_Name,
        		drpo.DrugRequestPersonOrder_OrdKolvo as RP_Kolvo,
        		drpo_mp.DrugRequestPersonOrder_OrdKolvo as LP_Kolvo,
        		mp.Person_Fio as MedPersonal_FIO,
				--convert(varchar(10), drpo.DrugRequestPersonOrder_begDate, 104) as DrugRequestPersonOrder_begDate,
				--convert(varchar(10), drpo.DrugRequestPersonOrder_endDate, 104) as DrugRequestPersonOrder_endDate,
				--dret.DrugRequestExceptionType_Name,
			    DrugListRequest_Data.DrugListRequest_Comment,
				DrugListRequest_Data.isProblem,
				DrugListRequest_Data.isProblemTorg,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				(
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Name
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestPersonOrder drpo with (nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
				--left join v_DrugRequestExceptionType dret with (nolock) on dret.DrugRequestExceptionType_id = drpo.DrugRequestExceptionType_id
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = drpo.MedPersonal_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drpo.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				--left join rls.v_DrugComplexMnnDose dcmd with (nolock) on dcmd.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
				--left join rls.v_DrugComplexMnnFas dcmf with (nolock) on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				--left join rls.v_CLSDRUGFORMS cdf  with(nolock) on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = drpo.Tradenames_id
				outer apply (
					select top 1
			            dlr.DrugListRequest_Comment,
						isnull(isProblem.YesNo_Code, 0) as isProblem,
						isnull(isProblemTorg.YesNo_Code, 0) as isProblemTorg
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = drpo.Tradenames_id
						left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = dlr.DrugListRequest_IsProblem
						left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = dlrt.DrugListRequestTorg_IsProblem
					where
						dlr.DrugRequestProperty_id = :DrugRequestProperty_id and
						dlr.DrugComplexMnn_id = drpo.DrugComplexMnn_id
				) as DrugListRequest_Data
				outer apply (
					select top 1
						df.DrugFinance_Name
					from
						v_DrugRequestRow drr with (nolock)
						left join v_DrugFinance df with (nolock) on df.DrugFinance_id = drr.DrugFinance_id
					where
						drr.DrugRequest_id = drpo.DrugRequest_id and
						(drr.DrugComplexMnn_id = drpo.DrugComplexMnn_id or drr.TRADENAMES_id = drpo.Tradenames_id)
				) as DrugRequestRow_Data
				outer apply (
				    select top 1
				        i_drpo.DrugRequestPersonOrder_id,
				        i_drpo.DrugRequestPersonOrder_OrdKolvo
				    from
				        v_DrugRequestPersonOrder i_drpo with (nolock)
				        inner join mp_request_list i_mrl on i_mrl.DrugRequest_id = i_drpo.DrugRequest_id
				    where
				        i_drpo.Person_id = drpo.Person_id and
				        isnull(i_drpo.DrugComplexMnn_id, 0) = isnull(drpo.DrugComplexMnn_id, 0) and
				        isnull(i_drpo.Tradenames_id, 0) = isnull(drpo.Tradenames_id, 0)
				) drpo_mp -- разнарядка из заявок врачей
				{$join}	
				{$where}
			order by
				dcmn.DrugComplexMnnName_Name
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о количестве медикамента в строке заявки и в разнарядке
	 */
	function getDrugRequestRowKolvoData($data) {
		$query = "
			select
				drr.DrugRequestRow_Kolvo,
				drpo.DrugRequestPersonOrder_SumOrdKolvo
			from
				v_DrugRequestRow drr with (nolock)
				outer apply (
					select
						isnull(sum(i_drpo.DrugRequestPersonOrder_OrdKolvo), 0) as DrugRequestPersonOrder_SumOrdKolvo
					from
						v_DrugRequestPersonOrder i_drpo with (nolock)
					where
						i_drpo.DrugRequest_id = drr.DrugRequest_id and
						isnull(i_drpo.DrugComplexMnn_id, 0) = isnull(drr.DrugComplexMnn_id, 0) and
						isnull(i_drpo.Tradenames_id, 0) = isnull(drr.TRADENAMES_id, 0)
				) drpo
			where
				drr.DrugRequestRow_id = :DrugRequestRow_id;
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение дополнительных данных для строки разнарядки
	 */
	function getDrugRequestPersonOrderContext($data) {
		$query = "
			select
				(
					select top 1
						isnull(drp.DrugRequestPeriod_Name, '') +
						isnull(', '+drk.DrugRequestKind_Name, '') +
						isnull(', '+ltrim(rtrim(lr.LpuRegion_Name)), '') +
						isnull(', '+mp.Person_Fio, '')
					from
						v_DrugRequest dr with (nolock)
						left join v_DrugRequestPeriod drp with(nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
						left join v_DrugRequestKind drk with(nolock) on drk.DrugRequestKind_id = dr.DrugRequestKind_id
						left join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = dr.LpuRegion_id
						left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = dr.MedPersonal_id
					where
						DrugRequest_id = :DrugRequest_id
				) as DrugRequest_Information,
				(
					select top 1
						isnull(rtrim(l.Lpu_Nick)+' ', '') +
						isnull(rtrim(pcard.LpuRegion_Name), '')
					from
						v_PersonState ps with(nolock)
						outer apply (
							select top 1
								pc.Lpu_id,
								pc.LpuRegion_id,
								pc.LpuRegion_Name
							from
								v_PersonCard pc with (nolock)
							where
								pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
							order by
								PersonCard_begDate desc
						) as pcard
						left join v_Lpu l with (nolock) on pcard.Lpu_id = l.Lpu_id
					where
						Person_id = :Person_id
				) as Lpu_Information
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Функция получения списка пациентов из заявок врачей, имеющих прикрепление к участку но отстутсвующих в участковой заявке
	 */
	function getDrugRequestPersonOrderMissingPerson($request_id) {
		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@DrugRequestPeriod_id bigint = null,
				@PersonRegisterType_id bigint = null,
				@DrugRequestKind_id bigint = null,
				@DrugGroup_id bigint = null,
				@LpuRegion_id bigint = null,
				@Lpu_id bigint = null,
				@DrugRequestCategory_id bigint = null,
				@cur_date datetime;
											
			set @cur_date = dbo.tzGetDate();
			set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
							
			select
				@DrugRequestPeriod_id = DrugRequestPeriod_id,
				@PersonRegisterType_id = PersonRegisterType_id,
				@DrugRequestKind_id = DrugRequestKind_id,
				@DrugGroup_id = DrugGroup_id,
				@LpuRegion_id = LpuRegion_id,
				@Lpu_id = Lpu_id
			from
				v_DrugRequest with (nolock)
			where
				DrugRequest_id = @DrugRequest_id;
									
			select
				ps.Person_id,
				isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,'') as Person_Fio,
				isnull(convert(varchar(10), ps.Person_BirthDay, 104), '') as Person_BirthDay				
			from (
					select
						drpo.Person_id
					from
						v_DrugRequest dr with (nolock)
						left join v_DrugRequestPersonOrder drpo with(nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
						outer apply (
							select top 1
								i_lr_drpo.DrugRequestPersonOrder_id
							from
								v_DrugRequestPersonOrder i_lr_drpo with(nolock)
							where
								i_lr_drpo.DrugRequest_id = @DrugRequest_id and
								i_lr_drpo.Person_id = drpo.Person_id
						) lr_drpo
						outer apply (
							select top 1
								pc.PersonCard_id
							from
								v_PersonCard_all pc with (nolock)
							where
								pc.LpuRegion_id = @LpuRegion_id and
								pc.Person_id = drpo.Person_id and
								pc.LpuAttachType_id in (1, 4) and -- основное или служебное
								(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= @cur_date) and
								(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= @cur_date)
							order by
								pc.LpuAttachType_id
						) att
					where
						dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
						isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
						dr.DrugRequestCategory_id = @DrugRequestCategory_id and
						dr.DrugRequest_Version is null and
						dr.Lpu_id = @Lpu_id and
						dr.LpuRegion_id is null and
						dr.MedPersonal_id is not null and
						att.PersonCard_id is not null and
						lr_drpo.DrugRequestPersonOrder_id is null
					group by
						drpo.Person_id
				) p
				left join v_PersonState ps with (nolock) on ps.Person_id = p.Person_id;
		";
		$person_list = $this->queryResult($query, array(
			'DrugRequest_id' => $request_id
		));

		return $person_list;
	}

	/**
	 * Функция получения списка пациентов участковой заявки без прикрепления к участку
	 */
	function getDrugRequestPersonOrderUnattachedPerson($request_id, $lpuregion_id) {
		$query = "
			declare
				@cur_date datetime;
			
			set @cur_date = dbo.tzGetDate();
			
							
			select
				ps.Person_id,
				isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,'') as Person_Fio,
				isnull(convert(varchar(10), ps.Person_BirthDay, 104), '') as Person_BirthDay				
			from (
				select
					drpo.Person_id
				from
					v_DrugRequestPersonOrder drpo with(nolock)
					outer apply (
						select top 1
							i_pc.PersonCard_id
						from
							v_PersonCard_all i_pc with (nolock)
						where
							i_pc.LpuRegion_id = :LpuRegion_id and
							i_pc.Person_id = drpo.Person_id and
							i_pc.LpuAttachType_id in (1, 4) and -- основное или служебное
							(i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @cur_date) and
							(i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @cur_date)
					) att
				where
					drpo.DrugRequest_id = :DrugRequest_id and
					att.PersonCard_id is null
				group by
					drpo.Person_id
			) p
			left join v_PersonState ps with (nolock) on ps.Person_id = p.Person_id;
		";
		$person_list = $this->queryResult($query, array(
			'DrugRequest_id' => $request_id,
			'LpuRegion_id' => $lpuregion_id
		));

		return $person_list;
	}

	/**
	 * Получение суммарного количества медикаментов в выписанных рецептах
	 */
	function getEvnReceptSumKolvoByParams($data) {
		$query = "
			declare
				@DrugRequestPeriod_id bigint,
				@DrugRequestPeriod_begDate date,
				@DrugRequestPeriod_endDate date;

			select
				@DrugRequestPeriod_id = DrugRequestPeriod_id
			from
				v_DrugRequest with(nolock)
			where
				DrugRequest_id = :DrugRequest_id

			select
				@DrugRequestPeriod_begDate = DrugRequestPeriod_begDate,
				@DrugRequestPeriod_endDate = DrugRequestPeriod_endDate
			from
				v_DrugRequestPeriod with(nolock)
			where
				DrugRequestPeriod_id = @DrugRequestPeriod_id;

			select
				isnull(sum(er.EvnRecept_Kolvo), 0) as EvnRecept_SumKolvo
			from
				v_EvnRecept er with (nolock)
				inner join rls.v_Drug d with(nolock) on d.Drug_id = er.Drug_rlsid
				left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
			where
				er.Person_id = :Person_id and
				er.ReceptDelayType_id is null and
				er.EvnRecept_setDate between @DrugRequestPeriod_begDate and @DrugRequestPeriod_endDate and
				(:DrugComplexMnn_id is null or d.DrugComplexMnn_id = :DrugComplexMnn_id) and
				(:Tradenames_id is null or p.TRADENAMEID = :Tradenames_id);
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида с информацией о потребности
	 */
	function loadDrugRequestPlanDeliveryGrid($data) {
		$where_clause = "";
		$where = array();
		$period_id_arr = explode(',', $data['PeriodId_List']);

		$where[] = "dr.DrugRequest_id = :DrugRequest_id";

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query_select = "";
		$query_where = "";

		foreach($period_id_arr as $period_id) {
			$query_select .= "plan_{$period_id}.DrugRequestPlanDelivery_Kolvo as plan_{$period_id},";
			$query_where .= "
				left join drpd plan_{$period_id} on plan_{$period_id}.DrugRequestPlanPeriod_id = '{$period_id}' and plan_{$period_id}.DrugComplexMnn_id = drr.DrugComplexMnn_id and isnull(plan_{$period_id}.Tradenames_id, 0) = isnull(drr.TRADENAMES_ID, 0)
			";
		}

		$query = "
			with drpd as (
				select
					max(DrugRequestPlanDelivery_Kolvo) as DrugRequestPlanDelivery_Kolvo,
					DrugRequestPlanPeriod_id,
					DrugComplexMnn_id,
					Tradenames_id
				from
					v_DrugRequestPlanDelivery
				where
					DrugRequest_id = :DrugRequest_id
				group by
					DrugRequestPlanPeriod_id, DrugComplexMnn_id, Tradenames_id
			)
			select
		   		drr.DrugRequestRow_id,
        		drr.DrugComplexMnn_id,
        		dcm.DrugComplexMnn_RusName,
        		drr.TRADENAMES_id as Tradenames_id,
        		tn.NAME as Tradenames_Name,
        		{$query_select}
        		drr.DrugRequestRow_Kolvo
			from
				v_DrugRequest dr with (nolock)
				outer apply (
					select
						max(i_drr.DrugRequestRow_id) as DrugRequestRow_id,
						i_drr.DrugComplexMnn_id,
						i_drr.TRADENAMES_ID,
						sum(i_drr.DrugRequestRow_Kolvo) as DrugRequestRow_Kolvo,
						sum(i_drr.DrugRequestRow_Summa) as DrugRequestRow_Summa
					from
						v_DrugRequest i_dr with (nolock)
						left join v_DrugRequestRow i_drr with (nolock) on i_drr.DrugRequest_id = i_dr.DrugRequest_id
					where
						i_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						isnull(i_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						i_drr.Person_id is null and
						(
							i_drr.DrugComplexMnn_id is not null or
							i_drr.TRADENAMES_ID is not null
						) and
						i_dr.Lpu_id = dr.Lpu_id
					group by
						i_drr.DrugComplexMnn_id, i_drr.TRADENAMES_ID
				) drr
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
				left join rls.v_TRADENAMES tn  with(nolock) on tn.TRADENAMES_ID = drr.TRADENAMES_ID
				{$query_where}
			{$where_clause}
			order by
				dcm.DrugComplexMnn_RusName, tn.NAME
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение количества в плане потребности
	 */
	function saveDrugRequestPlanDeliveryKolvo($data) {
		//ищем существующую строку
		$query = "
			select top 1
				DrugRequestPlanDelivery_id
			from
				v_DrugRequestPlanDelivery with(nolock)
			where
				DrugRequest_id = :DrugRequest_id and
				DrugRequestPlanPeriod_id = :DrugRequestPlanPeriod_id and
				DrugComplexMnn_id = :DrugComplexMnn_id and
				(:Tradenames_id is null or Tradenames_id = :Tradenames_id)
			order by
				DrugRequestPlanDelivery_id;
		";
		$record_id = $this->getFirstResultFromQuery($query, $data);

		if ($record_id > 0) {
			$data['DrugRequestPlanDelivery_id'] = $record_id;
		}

		//сохраняем или удаляем данные
		if ($data['DrugRequestPlanDelivery_Kolvo'] > 0) {
			$result = $this->saveObject('DrugRequestPlanDelivery', $data);
			if (is_array($result) && !empty($result['DrugRequestPlanDelivery_id'])) {
				$record_id = $result['DrugRequestPlanDelivery_id'];
			}
		} else if ($record_id > 0) {
			$result = $this->deleteObject('DrugRequestPlanDelivery', array(
				'DrugRequestPlanDelivery_id' => $record_id
			));
			$record_id = null;
		}

		$kolvo = null;
		if ($record_id > 0) {
			$query = "
				select
					DrugRequestPlanDelivery_Kolvo
				from
					v_DrugRequestPlanDelivery with(nolock)
				where
					DrugRequestPlanDelivery_id = :DrugRequestPlanDelivery_id;
			";
			$kolvo = $this->getFirstResultFromQuery($query, array(
				'DrugRequestPlanDelivery_id' => $record_id
			));
		}

		$result = array(array(
			'DrugRequestPlanDelivery_id' => $record_id > 0 ? $record_id : null,
			'DrugRequestPlanDelivery_Kolvo' => $kolvo > 0 ? $kolvo : null,
			'Error_Code' => null,
			'Error_Msg' => null
		));
		return $result;
	}

	/**
	 * Проверка категории заявки на соотвествие данным
	 */
	function checkDrugRequestCategory($data) {
		$error = null;
		$category_code = null;

		if (!empty($data['DrugRequestCategory_Code'])) {
			$category_code = $data['DrugRequestCategory_Code'];
		} else if (!empty($data['DrugRequestCategory_id'])) {
			$category_code = $this->getFirstResultFromQuery("
				select
					DrugRequestCategory_Code
				from
					v_DrugRequestCategory with(nolock)
				where
					DrugRequestCategory_id = :DrugRequestCategory_id;
			", array('DrugRequestCategory_id' => $data['DrugRequestCategory_id']));
		}

		switch($category_code) {
			case 1: //заявка врача
				if (empty($data['MedPersonal_id'])) {
					$error = 'Для заявки врача должен быть указан врач.';
				}
				break;
		}

		return array('Error_Msg' => $error);
	}


	/**
	 * Проверка категории заявки на соотвествие данным
	 */
	function checkDrugRequestLpuRegionExist($data) {
		$result = array();

		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@LpuRegion_id bigint = :LpuRegion_id,
				@DrugRequestPeriod_id bigint = null,
				@PersonRegisterType_id bigint = null,
				@DrugRequestKind_id bigint = null,
				@DrugGroup_id bigint = null;

			select
				@DrugRequestPeriod_id = DrugRequestPeriod_id,
				@PersonRegisterType_id = PersonRegisterType_id,
				@DrugRequestKind_id = DrugRequestKind_id,
				@DrugGroup_id = DrugGroup_id
			from
				v_DrugRequest with (nolock)
			where
				DrugRequest_id = @DrugRequest_id;

			select top 1
				dr.DrugRequest_id,
				mp.MedPersonal_id,
				isnull(mp.Person_Fio, '') as MedPersonal_Fio,
				isnull(ls.LpuSection_Name, '') as LpuSection_Name
			from
				v_DrugRequest dr with (nolock)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = dr.LpuSection_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = dr.MedPersonal_id
			where
				dr.DrugRequest_id <> @DrugRequest_id and
				dr.LpuRegion_id = @LpuRegion_id and
				isnull(dr.DrugRequestPeriod_id, 0) = isnull(@DrugRequestPeriod_id, 0) and
				isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
				isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
				isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
				dr.DrugRequest_Version is null;
		";
		$check_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'LpuRegion_id' => $data['LpuRegion_id']
		));
		if (!empty($check_data['DrugRequest_id'])) {
			if (!empty($check_data['MedPersonal_id'])) {
				$result['Error_Msg'] = "Изменения не могут быть сохранены, т.к. для этого участка уже создана заявка врачом {$check_data['MedPersonal_Fio']}";
			} else {
				$result['Error_Msg'] = "Изменения не могут быть сохранены, т.к. для этого участка уже создана заявка отделения {$check_data['LpuSection_Name']}";
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия в заявке участка пациентов без прикрепления к данному участку и пациентов отсутсвующих в заявках участка (при этом присутствующие в заявках врачей)
	 */
	function checkDrugRequestMoMissingAndUnattachedPerson($data) {
		$result = array();

		//получаем данные заявки
		$query = "
			select
				dr.DrugRequest_id,
				dr.Lpu_id,
				is_obl.YesNo_Code as IsPersonalOrderObligatory
			from
				v_DrugRequest dr with (nolock)				
				outer apply (
					select top 1
						i_drq.DrugRequestQuota_IsPersonalOrderObligatory
					from
						dbo.DrugRequestQuota i_drq with (nolock)
					where
						i_drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						isnull(i_drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						(
							(dr.PersonRegisterType_id = 1 and i_drq.DrugFinance_id is not null) or
							(dr.PersonRegisterType_id <> 1 and i_drq.DrugFinance_id is null)
						)
					order by
						i_drq.DrugRequestQuota_id				
				) drq
				left join v_YesNo is_obl on is_obl.YesNo_id = isnull(drq.DrugRequestQuota_IsPersonalOrderObligatory, 1)
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$dr_data = $this-> getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));

		//определяем необходимость проведения проверки
		$need_check = (!empty($dr_data['IsPersonalOrderObligatory']));

		if (!empty($dr_data['DrugRequest_id']) && !empty($dr_data['Lpu_id']) && $need_check) {
			//поиск пациентов не прикрепленных к участку заявки в разнарядке которой они содержатся
			$query = "
				declare
					@DrugRequest_id bigint = :DrugRequest_id,
					@DrugRequestPeriod_id bigint = null,
					@PersonRegisterType_id bigint = null,
					@DrugRequestKind_id bigint = null,
					@DrugGroup_id bigint = null,
					@Lpu_id bigint = null,
					@DrugRequestCategory_id bigint = null,
					@cur_date datetime;
												
				set @cur_date = dbo.tzGetDate();
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
								
				select
					@DrugRequestPeriod_id = DrugRequestPeriod_id,
					@PersonRegisterType_id = PersonRegisterType_id,
					@DrugRequestKind_id = DrugRequestKind_id,
					@DrugGroup_id = DrugGroup_id,
					@Lpu_id = Lpu_id
				from
					v_DrugRequest with (nolock)
				where
					DrugRequest_id = @DrugRequest_id;
											
															
				select
					ps.Person_id,
					isnull(mp.Person_Fio, '') as DrugRequest_Name,
					isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,'') as Person_Fio,
					isnull(convert(varchar(10), ps.Person_BirthDay, 104), '') as Person_BirthDay,
					isnull(convert(varchar(10), ps.Person_deadDT, 104), '') as Person_deadDT,
					isnull(lr.LpuRegion_Name + isnull(' (' + lrt.LpuRegionType_Name + ')', ''), '') as LpuRegion_Name
				from
					(
						select
							i_dr.DrugRequest_id,
							i_drpo.Person_id
						from
							v_DrugRequest i_dr with (nolock)
							left join v_DrugRequestPersonOrder i_drpo with(nolock) on i_drpo.DrugRequest_id = i_dr.DrugRequest_id
							outer apply (
								select top 1
									i_lr_drpo.DrugRequestPersonOrder_id
								from
									v_DrugRequest i_lr_dr with (nolock)
									left join v_DrugRequestPersonOrder i_lr_drpo with(nolock) on i_lr_drpo.DrugRequest_id = i_lr_dr.DrugRequest_id
								where
									i_lr_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
									isnull(i_lr_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
									isnull(i_lr_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
									isnull(i_lr_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
									i_lr_dr.DrugRequestCategory_id = @DrugRequestCategory_id and
									i_lr_dr.DrugRequest_Version is null and
									i_lr_dr.Lpu_id = @Lpu_id and
									i_lr_dr.LpuRegion_id is not null and
									i_lr_drpo.Person_id = i_drpo.Person_id
							) lr_drpo
						where
							i_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
							isnull(i_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
							isnull(i_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
							isnull(i_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
							i_dr.DrugRequestCategory_id = @DrugRequestCategory_id and
							i_dr.DrugRequest_Version is null and
							i_dr.Lpu_id = @Lpu_id and
							i_dr.LpuRegion_id is null and
							i_dr.MedPersonal_id is not null and
							lr_drpo.DrugRequestPersonOrder_id is null
						group by
							i_dr.DrugRequest_id, i_drpo.Person_id
					) p
					left join v_PersonState ps with (nolock) on ps.Person_id = p.Person_id
					left join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = p.DrugRequest_id
					outer apply (
						select top 1
							pc.LpuRegion_id
						from
							v_PersonCard_all pc with (nolock)
						where
							pc.Lpu_id = @Lpu_id and
							pc.Person_id = p.Person_id and
							pc.LpuAttachType_id in (1, 4) and -- основное или служебное
							(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= @cur_date) and
							(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= @cur_date) and
							pc.LpuRegion_id is not null
						order by
							pc.LpuAttachType_id
					) att
					left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = att.LpuRegion_id
					left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
					outer apply (
						select top 1
							i_mp.Person_Fio
						from
							v_MedPersonal i_mp with (nolock)
						where
							i_mp.MedPersonal_id = dr.MedPersonal_id and
							i_mp.Lpu_id = dr.Lpu_id
						order by
							i_mp.MedPersonal_id
					) mp
				where
					p.Person_id is not null					
				order by
					mp.Person_Fio, (isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''));
			";
			$person_list = $this->queryResult($query, array(
				'DrugRequest_id' => $dr_data['DrugRequest_id']
			));
			if ($person_list !== false) {
				if (is_array($person_list) && count($person_list) > 0) {
					$result['Error_Msg'] = "В заявках врачей есть пациенты, остутствующие в участковых заявках";
					$result['MissingPerson_List'] = $person_list;
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}

			//поиск пациентов не прикрепленных к участку заявки в разнарядке которой они содержатся
			$query = "
				declare
					@DrugRequest_id bigint = :DrugRequest_id,
					@DrugRequestPeriod_id bigint = null,
					@PersonRegisterType_id bigint = null,
					@DrugRequestKind_id bigint = null,
					@DrugGroup_id bigint = null,
					@Lpu_id bigint = null,
					@DrugRequestCategory_id bigint = null,
					@cur_date datetime;
								
				set @cur_date = dbo.tzGetDate();
				set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'vrach');
				
				select
					@DrugRequestPeriod_id = DrugRequestPeriod_id,
					@PersonRegisterType_id = PersonRegisterType_id,
					@DrugRequestKind_id = DrugRequestKind_id,
					@DrugGroup_id = DrugGroup_id,
					@Lpu_id = Lpu_id
				from
					v_DrugRequest with (nolock)
				where
					DrugRequest_id = @DrugRequest_id;
							
											
				select
					ps.Person_id,
					isnull('Участок № ' + dr_lr.LpuRegion_Name + isnull(' (' + dr_lrt.LpuRegionType_Name + ')', ''), '') as DrugRequest_Name,
					isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,'') as Person_Fio,
					isnull(convert(varchar(10), ps.Person_BirthDay, 104), '') as Person_BirthDay,
					isnull(convert(varchar(10), ps.Person_deadDT, 104), '') as Person_deadDT,
					isnull(lr.LpuRegion_Name + isnull(' (' + lrt.LpuRegionType_Name + ')', ''), '') as LpuRegion_Name
				from
					(
						select
							i_dr.DrugRequest_id,
							i_drpo.Person_id
						from
							v_DrugRequest i_dr with (nolock)
							left join v_DrugRequestPersonOrder i_drpo with(nolock) on i_drpo.DrugRequest_id = i_dr.DrugRequest_id
							outer apply (
								select top 1
									i_pc.PersonCard_id
								from
									v_PersonCard_all i_pc with (nolock)
								where
									i_pc.LpuRegion_id = i_dr.LpuRegion_id and
									i_pc.Person_id = i_drpo.Person_id and
									i_pc.LpuAttachType_id in (1, 4) and -- основное или служебное
									(i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @cur_date) and
									(i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @cur_date)
							) i_att
						where
							i_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
							isnull(i_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
							isnull(i_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
							isnull(i_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
							i_dr.DrugRequestCategory_id = @DrugRequestCategory_id and
							i_dr.DrugRequest_Version is null and
							i_dr.Lpu_id = @Lpu_id and
							i_dr.LpuRegion_id is not null and
							i_att.PersonCard_id is null
						group by
							i_dr.DrugRequest_id, i_drpo.Person_id
					) p
					left join v_PersonState ps with (nolock) on ps.Person_id = p.Person_id
					left join v_DrugRequest dr with(nolock) on dr.DrugRequest_id = p.DrugRequest_id
					outer apply (
						select top 1
							pc.LpuRegion_id
						from
							v_PersonCard_all pc with (nolock)
						where
							pc.Lpu_id = @Lpu_id and
							pc.Person_id = p.Person_id and
							pc.LpuAttachType_id in (1, 4) and -- основное или служебное
							(pc.PersonCard_begDate is null or pc.PersonCard_begDate <= @cur_date) and
							(pc.PersonCard_endDate is null or pc.PersonCard_endDate >= @cur_date) and
							pc.LpuRegion_id is not null
						order by
							pc.LpuAttachType_id
					) att
					left join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = att.LpuRegion_id
					left join v_LpuRegion dr_lr with(nolock) on dr_lr.LpuRegion_id = dr.LpuRegion_id
					left join v_LpuRegionType lrt with(nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
					left join v_LpuRegionType dr_lrt with(nolock) on dr_lrt.LpuRegionType_id = dr_lr.LpuRegionType_id
				where
					p.Person_id is not null
				order by
					dr_lr.LpuRegion_Name, (isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,''));
			";
			$person_list = $this->queryResult($query, array(
				'DrugRequest_id' => $dr_data['DrugRequest_id']
			));
			if ($person_list !== false) {
				if (is_array($person_list) && count($person_list) > 0) {
					$result['Error_Msg'] = "В заявках есть пациенты, не имеющие прикрепления к участковой заявке";
					$result['UnattachedPerson_List'] = $person_list;
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки возможности редактирования планово-отчетного периода.
	 */
	function checkDrugRequestPlanPeriodEdit($data) {
		$result = array();

		if (!empty($data['DrugRequestPlanPeriod_id'])) {
			$query = "
				select
					count(DrugRequestPlanDelivery_id) as cnt
				from
					v_DrugRequestPlanDelivery with (nolock)
				where
					DrugRequestPlanPeriod_id = :DrugRequestPlanPeriod_id;
			";
			$cnt = $this->getFirstResultFromQuery($query, $data);

			if ($cnt !== false) {
				if ($cnt > 0) {
					$result['Error_Msg'] = 'Планово-отчетный период уже используются. Редактирование не возможно.';
				}
			} else {
				$result['Error_Msg'] = 'При проверке данных планово-отчетного периода, произошла ошибка.';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия в разнарядке заявки пациентов без прикрепления к заданному учатку
	 */
	function checkDrugRequestPersonOrderByLpuRegion($data) {
		$result = array();

		//получаем данные заявки
		$query = "
			select
				dr.DrugRequest_id,
				is_obl.YesNo_Code as IsPersonalOrderObligatory
			from
				v_DrugRequest dr with (nolock)				
				outer apply (
					select top 1
						i_drq.DrugRequestQuota_IsPersonalOrderObligatory
					from
						dbo.DrugRequestQuota i_drq with (nolock)
					where
						i_drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						isnull(i_drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						(
							(dr.PersonRegisterType_id = 1 and i_drq.DrugFinance_id is not null) or
							(dr.PersonRegisterType_id <> 1 and i_drq.DrugFinance_id is null)
						)
					order by
						i_drq.DrugRequestQuota_id				
				) drq
				left join v_YesNo is_obl on is_obl.YesNo_id = isnull(drq.DrugRequestQuota_IsPersonalOrderObligatory, 1)
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$dr_data = $this-> getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));

		//определяем необходимость проведения проверки
		$need_check = !empty($dr_data['IsPersonalOrderObligatory']);

		if (!empty($data['DrugRequest_id']) && !empty($data['LpuRegion_id']) && $need_check) {
			$person_list = $this->getDrugRequestPersonOrderUnattachedPerson($data['DrugRequest_id'], $data['LpuRegion_id']);
			if ($person_list !== false) {
				if (is_array($person_list) && count($person_list) > 0) {
					$result['Error_Msg'] = "Участок заявки не может быть изменен, так как в разнарядке есть пациенты с прикреплением к другим участкам. Удалите этих пациентов из разнарядки и повторите попытку.";
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия пациентов имеющих прикрепление к участку но отстутсвующих в участковой заявке
	 */
	function checkDrugRequestPersonOrderMissingPerson($data) {
		$result = array();

		//получаем данные заявки
		$query = "
			select
				dr.DrugRequest_id,
				dr.LpuRegion_id,
				is_obl.YesNo_Code as IsPersonalOrderObligatory
			from
				v_DrugRequest dr with (nolock)				
				outer apply (
					select top 1
						i_drq.DrugRequestQuota_IsPersonalOrderObligatory
					from
						dbo.DrugRequestQuota i_drq with (nolock)
					where
						i_drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						isnull(i_drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						(
							(dr.PersonRegisterType_id = 1 and i_drq.DrugFinance_id is not null) or
							(dr.PersonRegisterType_id <> 1 and i_drq.DrugFinance_id is null)
						)
					order by
						i_drq.DrugRequestQuota_id				
				) drq
				left join v_YesNo is_obl on is_obl.YesNo_id = isnull(drq.DrugRequestQuota_IsPersonalOrderObligatory, 1)
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$dr_data = $this-> getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));

		//определяем необходимость проведения проверки
		$need_check = (!empty($dr_data['IsPersonalOrderObligatory']) && !empty($dr_data['LpuRegion_id']));

		if (!empty($dr_data['DrugRequest_id']) && !empty($dr_data['LpuRegion_id']) && $need_check) {
			$person_list = $this->getDrugRequestPersonOrderMissingPerson($dr_data['DrugRequest_id']);
			if ($person_list !== false) {
				if (is_array($person_list) && count($person_list) > 0) {
					$result['Error_Msg'] = "В заявке отсутствуют некоторые пациенты имеющие прикрепления к участку заявки";
					$result['Person_List'] = $person_list;
					$result['DrugRequest_id'] = $data['DrugRequest_id'];
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия в заявке участка пациентов без прикрепления к данному участку
	 */
	function checkDrugRequestPersonOrderUnattachedPerson($data) {
		$result = array();

		//получаем данные заявки
		$query = "
			select
				dr.DrugRequest_id,
				dr.LpuRegion_id,
				is_obl.YesNo_Code as IsPersonalOrderObligatory
			from
				v_DrugRequest dr with (nolock)				
				outer apply (
					select top 1
						i_drq.DrugRequestQuota_IsPersonalOrderObligatory
					from
						dbo.DrugRequestQuota i_drq with (nolock)
					where
						i_drq.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
						isnull(i_drq.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
						isnull(i_drq.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
						isnull(i_drq.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
						(
							(dr.PersonRegisterType_id = 1 and i_drq.DrugFinance_id is not null) or
							(dr.PersonRegisterType_id <> 1 and i_drq.DrugFinance_id is null)
						)
					order by
						i_drq.DrugRequestQuota_id				
				) drq
				left join v_YesNo is_obl on is_obl.YesNo_id = isnull(drq.DrugRequestQuota_IsPersonalOrderObligatory, 1)
			where
				dr.DrugRequest_id = :DrugRequest_id;
		";
		$dr_data = $this-> getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));

		//определяем необходимость проведения проверки
		$need_check = (!empty($dr_data['IsPersonalOrderObligatory']) && !empty($dr_data['LpuRegion_id']));

		if (!empty($dr_data['DrugRequest_id']) && !empty($dr_data['LpuRegion_id']) && $need_check) {
			$person_list = $this->getDrugRequestPersonOrderUnattachedPerson($dr_data['DrugRequest_id'], $dr_data['LpuRegion_id']);
			if ($person_list !== false) {
				if (is_array($person_list) && count($person_list) > 0) {
					$result['Error_Msg'] = "В заявке есть пациенты, не имеющие прикрепления к участковой заявке";
					$result['Person_List'] = $person_list;
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия в заявке пациента без медикаментов в рамках всей заявочной кампании
	 */
	function checkDrugRequestPersonOrderEmptyPerson($data) {
		$result = array();

		if (!empty($data['DrugRequest_id'])) {
			$query = "
				 declare
                    @DrugRequest_id bigint = :DrugRequest_id,
                    @DrugRequestPeriod_id bigint = null,
                    @PersonRegisterType_id bigint = null,
                    @DrugRequestKind_id bigint = null,
                    @DrugGroup_id bigint = null,
                    @DrugRequestCategory_id bigint = null,
                    @Lpu_id bigint = null,
                    @DrugRequest_Version int = null;

                select
                    @DrugRequestPeriod_id = DrugRequestPeriod_id,
                    @PersonRegisterType_id = PersonRegisterType_id,
                    @DrugRequestKind_id = DrugRequestKind_id,
                    @DrugGroup_id = DrugGroup_id,
                    @DrugRequestCategory_id = DrugRequestCategory_id,
                    @Lpu_id = Lpu_id,
                    @DrugRequest_Version = DrugRequest_Version
                from
                    v_DrugRequest with (nolock)
                where
                    DrugRequest_id = @DrugRequest_id;

                with dr_list(DrugRequest_id) as (
                    select
                        DrugRequest_id
                    from
                        v_DrugRequest dr with(nolock)
						outer apply (
							select top 1
								i_aro.AccessRightsName_id
							from
								v_Lpu i_l with (nolock)
								left join v_AccessRightsOrg i_aro with (nolock) on i_aro.Org_id = i_l.Org_id
							where
								i_l.Lpu_id = dr.Lpu_id
						) arl
                    where
                        dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                        isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                        isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                        isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                        dr.DrugRequestCategory_id = @DrugRequestCategory_id and
                        isnull(dr.DrugRequest_Version, 0) = isnull(@DrugRequest_Version, 0) and
                        (
                        	dr.Lpu_id = @Lpu_id or
                        	arl.AccessRightsName_id is null
                        )
                )
                select
                    count(drpo.DrugRequestPersonOrder_id) as cnt
                from
                    v_DrugRequestPersonOrder drpo with(nolock)
                    outer apply ( -- ищем записи по пациенту в других разнарядках в рамках заявочной кампании
                        select top 1
                            i_drpo.DrugRequestPersonOrder_id
                        from
                            v_DrugRequestPersonOrder i_drpo with(nolock)
                            inner join dr_list i_dl on i_dl.DrugRequest_id = i_drpo.DrugRequest_id
                        where
                            i_drpo.Person_id = drpo.Person_id and
                            (
                                i_drpo.DrugComplexMnn_id is not null or
                                i_drpo.Tradenames_id is not null
                            )
                    ) p
                where
                    drpo.DrugRequest_id = @DrugRequest_id and
                    drpo.DrugComplexMnn_id is null and
                    drpo.Tradenames_id is null and
                    p.DrugRequestPersonOrder_id is null;
			";
			$cnt = $this->getFirstResultFromQuery($query, $data);

			if ($cnt !== false) {
				if ($cnt > 0) {
                    $new_status_name = $data['DrugRequestStatus_Code'] == '2' ? "сформированная" : "утвержденная";
					$result['Error_Msg'] = "Изменить статус заявки врача на «{$new_status_name}» невозможно, т.к. в персональной разнарядке есть пациенты, у которых не указаны медикаменты";
				}
			} else {
				$result['Error_Msg'] = 'При проверке разнарядки, произошла ошибка';
			}
		}

		return $result;
	}

	/**
	 * Функция проверки наличия медикаментов без распределения по пациентам
	 */
	function checkDrugRequestRowWithoutPerson($data) {
		$result = array();

		if (!empty($data['DrugRequest_id'])) {
			$query = "
				select
                    count(drr.DrugRequestRow_id) as cnt
                from
                    v_DrugRequestRow drr with(nolock)
                    outer apply (
						select
							count(i_drpo.DrugRequestPersonOrder_id) as cnt
						from
							v_DrugRequestPersonOrder i_drpo with (nolock)
						where
							i_drpo.DrugRequest_id = drr.DrugRequest_id and
							i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id and
							isnull(i_drpo.Tradenames_id, 0) = isnull(drr.TRADENAMES_id, 0)													
					) drpo
                where
                    drr.DrugRequest_id = :DrugRequest_id and
                    drpo.cnt = 0;
			";
			$cnt = $this->getFirstResultFromQuery($query, $data);

			if ($cnt !== false) {
				if ($cnt > 0) {
					$result['Error_Msg'] = "В заявке врача есть медикаменты, для которых не сформирована персональная разнарядка. Проверьте данные заявки. Для просмотра таких медикаментов в заявке врача установите на список медикаментов фильтр «Не распределены между пациентами».";
				}
			} else {
				$result['Error_Msg'] = 'При проверке списка медикаментов, произошла ошибка';
			}
		}

		return $result;
	}

    /**
     * Утверждение всех заявок МО в заявочной кампании
     */
    function approveAllDrugRequestMo($data) {
        $error = array();
        $status_error = false;

        //получаем данные заявки региона
        $query = "
			select
				dr.DrugRequest_id,
				dr.PersonRegisterType_id,
				dr.DrugRequestPeriod_id,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id
			from
			 	v_DrugRequest dr with (nolock)
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
        $request_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $data['RegionDrugRequest_id']
        ));
        if (empty($request_data['DrugRequest_id'])) {
            $error[] = "Не удалось получить данные заявки.";
        }

        //проверка статусов заявок
        if (count($error) <= 0 && $data['check_status'] === true) {
            $total_count = 0;
            $formed_count = 0;

            //Считаем все заявки, и все несформированные заявки
            $query = "
				declare
					@status_form_id bigint,
					@status_app_id bigint,
					@category_id bigint;

				set @status_form_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 2);
				set @status_app_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 3);
				set @category_id = (select top 1 DrugRequestCategory_id from v_DrugRequestCategory with(nolock) where DrugRequestCategory_SysNick = 'mo');

				select
					count(DrugRequest_id) as total_count,
					sum(case when DrugRequestStatus_id in (@status_form_id, @status_app_id) then 1 else 0 end) as formed_count
				from
					v_DrugRequest with(nolock)
				where
					DrugRequest_Version is null and
					DrugRequestPeriod_id = :DrugRequestPeriod_id and
					isnull(PersonRegisterType_id, 0) = isnull(:PersonRegisterType_id, 0) and
					isnull(DrugRequestKind_id, 0) = isnull(:DrugRequestKind_id, 0) and
					isnull(DrugGroup_id, 0) = isnull(:DrugGroup_id, 0) and
					DrugRequestCategory_id = @category_id
			";
            $result = $this->getFirstRowFromQuery($query, $request_data);
            if (!empty($result['total_count'])) {
                $total_count = $result['total_count'];
                $formed_count = $result['formed_count'];
            }

            if ($total_count <= 0) {
                $error[] = "В заявочной кампании отсутствуют заявки МО.";
            } else if ($formed_count < $total_count) {
                $error[] = "Не все заявки МО сформированы.";
                $status_error = true;
            }
        }

        //обновляем дочерние заявки (мо и врачей)
        $type_array = array('mo', 'vrach', 'building', 'section');

        if (!$data['set_auto_status']) { //если авто статус не предусмотрен, то меняем статус еще и заявке региона (в противном случае об этом должен позаботиться авто статус)
            $type_array[] = 'region';
        }

        if (count($error) <= 0) {
            $this->updateDrugRequestStatus(
                $type_array,
                3, //3 - Утвержденная
                array(
                    'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                    'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                    'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                    'DrugGroup_id' => $request_data['DrugGroup_id']
                )
            );
        }

        //автостатус для заявки региона
        if (count($error) <= 0 && $data['set_auto_status']) {
            $this->setAutoDrugRequestStatus(array(
                'category' => 'mo',
                'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                'DrugGroup_id' => $request_data['DrugGroup_id']
            ));
        }

        $result = array(
            'Error_Code' => null,
            'Error_Msg' => null
        );

        if (count($error) > 0) {
            $result[$status_error ? 'Status_Msg' : 'Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Отмена утверждения всех заявок МО в заявочной кампании
     */
    function unapproveAllDrugRequestMo($data) {
        $error = array();
        $status_error = false;

        //получаем данные заявки региона
        $query = "
			select
				dr.DrugRequest_id,
				dr.PersonRegisterType_id,
				dr.DrugRequestPeriod_id,
				dr.DrugRequestKind_id,
				dr.DrugGroup_id
			from
			 	v_DrugRequest dr with (nolock)
			where
				dr.DrugRequest_id = :DrugRequest_id
		";
        $request_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => $data['RegionDrugRequest_id']
        ));
        if (empty($request_data['DrugRequest_id'])) {
            $error[] = "Не удалось получить данные заявки.";
        }

        //проверка наличия сводой заявки в состав в которой включена данная заявка региона
        if (count($error) <= 0 && $data['check_consolidated_request'] === true) {
            $query = "
				select
					count(drp.DrugRequestPurchase_id) as cnt
				from
					v_DrugRequestPurchase drp with(nolock)
				where
					drp.DrugRequest_lid = :DrugRequest_id
			";
            $cnt = $this->getFirstResultFromQuery($query, array(
                'DrugRequest_id' => $data['RegionDrugRequest_id']
            ));

            if ($cnt > 0) {
                $error[] = "Заявочная кампания включена в сводную заявку. Смена статуса невозможна.";
            }
        }

        //обновляем дочерние заявки (мо и врачей)
        $type_array = array('region', 'mo', 'vrach', 'building', 'section');
        if (count($error) <= 0) {
            $this->updateDrugRequestStatus(
                $type_array,
                1, //1 - Начальная
                array(
                    'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                    'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                    'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                    'DrugGroup_id' => $request_data['DrugGroup_id']
                )
            );
        }

        $result = array(
            'Error_Code' => null,
            'Error_Msg' => null
        );

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Получение списка данных о исполнении сводной заявки
     */
    function loadDrugRequestExecList($data) {
        $where = array();

        if (!empty($data['DrugRequestExec_id'])) {
            $where[] = "dre.DrugRequestExec_id = :DrugRequestExec_id";
        } else {
            $where[] = "drps.DrugRequest_id = :DrugRequest_id";
            $where[] = "dre.DrugRequestExec_id is not null";
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
            select
                dre.DrugRequestExec_id,
                dre.DrugRequestPurchaseSpec_id,
                dre.WhsDocumentSupplySpec_id,
                dre.DrugRequestExec_PurchCount,
                (
                    isnull('№ ' + wds.WhsDocumentUc_Num, '') +
                    isnull(' от ' + convert(varchar(10), wds.WhsDocumentUc_Date, 104), '') +
                    isnull(', ' + s_org.Org_Name, '')
                ) as WhsDocumentSupply_Name,
                coalesce(d.Drug_Name, dcm.DrugComplexMnn_RusName) as Drug_Name,
                wdss.WhsDocumentSupplySpec_PriceNDS,
                dre.DrugRequestExec_SupplyCount,
                dre.DrugRequestExec_Count
            from
                v_DrugRequestPurchaseSpec drps with (nolock)
                left join v_DrugRequestExec dre with (nolock) on dre.DrugRequestPurchaseSpec_id = drps.DrugRequestPurchaseSpec_id
                left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = dre.WhsDocumentSupplySpec_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = wdss.DrugComplexMnn_id
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
                left join v_Org s_org with (nolock) on s_org.Org_id = wds.Org_sid
            {$where_clause}
		";
        $result = $this->db->query($query, $data);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение данных закупа при исполнении сводной заявки
     */
    function loadDrugRequestExecPurchaseList($data) {
        $query = "
            select top 100
                wdss.WhsDocumentSupplySpec_id,
                (
                    isnull('№ '+wdpr.WhsDocumentUc_Num, '') +
                    isnull(' от '+convert(varchar(10), wdpr.WhsDocumentUc_Date, 104),  '')
                ) as WhsDocumentProcurementRequest_Name,
                wdprs.WhsDocumentProcurementRequestSpec_Kolvo,
                cast(wdprs.WhsDocumentProcurementRequestSpec_Kolvo * wdprs.WhsDocumentProcurementRequestSpec_PriceMax as decimal(12,2)) as WhsDocumentProcurementRequestSpec_Sum,
                (
                    isnull('№ ' + wds.WhsDocumentUc_Num, '') +
                    isnull(' от ' + convert(varchar(10), wds.WhsDocumentUc_Date, 104), '') +
                    isnull(', ' + s_org.Org_Name, '')
                ) as WhsDocumentSupply_Name,
                coalesce(d.Drug_Name, dcm.DrugComplexMnn_RusName) as Drug_Name,
                wdss.WhsDocumentSupplySpec_KolvoUnit,
                wdss.WhsDocumentSupplySpec_PriceNDS,
                wdss.WhsDocumentSupplySpec_SumNDS
            from
                v_WhsDocumentProcurementRequestSpec wdprs with (nolock)
                left join v_WhsDocumentProcurementRequest wdpr with (nolock) on wdpr.WhsDocumentProcurementRequest_id = wdprs.WhsDocumentProcurementRequest_id
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_pid = wdpr.WhsDocumentUc_id
                left join v_Org s_org with (nolock) on s_org.Org_id = wds.Org_sid
                left join v_WhsDocumentSupplySpec wdss with (nolock) on
                    wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id and (
                        wdss.Drug_id = wdprs.Drug_id or
                        wdss.DrugComplexMnn_id = wdprs.DrugComplexMnn_id
                    )
                left join rls.v_Drug d with (nolock) on d.Drug_id = wdprs.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = wdprs.DrugComplexMnn_id
            where
                wdprs.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
		";
        $result = $this->db->query($query, $data);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение списка остатков для исполнения сводной заявки
     */
    function loadDrugRequestExecSourceList($data) {
        if (empty($data['Org_id'])) {
            $query = "
                select top 1
                    drpr.Org_id
                from
                    DrugRequestPurchase drp with (nolock)
                    left join DrugRequest dr with (nolock) on dr.DrugRequest_id = drp.DrugRequest_lid
                    left join DrugRequestProperty drpr with (nolock) on drpr.DrugRequestProperty_id = dr.DrugRequestProperty_id
                where
                    drp.DrugRequest_id = :DrugRequest_id
                order by
                     dr.DrugRequest_id;
            ";
            $data['Org_id'] = $this->getFirstResultFromQuery($query, $data);
        }

        $query = "
            with dprs_data as (
                select
                    i_drps.DrugRequestPurchaseSpec_id,
                    i_drps.DrugComplexMnn_id,
                    i_drps.DrugRequestPurchaseSpec_Price,
                    i_drps.DrugRequestPurchaseSpec_Kolvo,
                    i_drps.DrugFinance_id,
                    i_dr.PersonRegisterType_id
                from
                    v_DrugRequestPurchaseSpec i_drps with (nolock)
                    left join v_DrugRequest i_dr with (nolock) on i_dr.DrugRequest_id = i_drps.DrugRequest_id
                where
                    i_drps.DrugRequest_id = :DrugRequest_id
            )
            select
                dor.DrugOstatRegistry_id,
                dor.DrugOstatRegistry_Kolvo,
                dor.DrugOstatRegistry_Cost,
                sat.SubAccountType_Name,
                wdss.WhsDocumentSupplySpec_id,
                dd.DrugRequestPurchaseSpec_id,
                dd.DrugRequestPurchaseSpec_Kolvo,
                d.Drug_Name,
                datepart(year, wds.WhsDocumentUc_Date) as WhsDocumentSupply_Year,
                (
                    isnull('№ ' + wds.WhsDocumentUc_Num, '') +
                    isnull(' от ' + convert(varchar(10), wds.WhsDocumentUc_Date, 104), '') +
                    isnull(', ' + s_org.Org_Name, '')
                ) as WhsDocumentSupply_Name
            from
                v_DrugOstatRegistry dor with (nolock)
                left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
                left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                left join v_Org s_org with (nolock) on s_org.Org_id = wds.Org_sid
                left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
                left join dprs_data dd on dd.DrugComplexMnn_id = d.DrugComplexMnn_id and dd.DrugFinance_id = dor.DrugFinance_id and dd.PersonRegisterType_id = wdcit.PersonRegisterType_id
                outer apply (
                    select top 1
                        i_wdss.WhsDocumentSupplySpec_id
                    from
                        v_WhsDocumentSupplySpec i_wdss with (nolock)
                    where
                        i_wdss.Drug_id = dor.Drug_id and
                        i_wdss.WhsDocumentSupplySpec_PriceNDS = dor.DrugOstatRegistry_Cost
                    order by
                        i_wdss.WhsDocumentSupplySpec_id
                ) wdss
            where
                dor.Org_id = :Org_id and
                dor.DrugOstatRegistry_Kolvo > 0 and
                sat.SubAccountType_Code in (1, 2) and -- 1 - Доступно; 2 - Зарезервировано
                wdss.WhsDocumentSupplySpec_id is not null and
	            dd.DrugRequestPurchaseSpec_id is not null
		";
        $result = $this->db->query($query, $data);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Сохранение данных о исполнении сводной заявки из JSON
     */
    function saveDrugRequestExecFromJSON($data) {
        $error = array();

        $this->beginTransaction();

        if (!empty($data['json_str']) && $data['DrugRequest_id'] > 0) {
            ConvertFromWin1251ToUTF8($data['json_str']);
            $dt = (array) json_decode($data['json_str']);

            //получение массива текущих данных о исполнении разнарядки
            $spec_array = array();
            $query = "
                select
                    WhsDocumentSupplySpec_id
                from
                    v_DrugRequestExec dre with (nolock)
                    left join v_DrugRequestPurchaseSpec drps with (nolock) on drps.DrugRequestPurchaseSpec_id = dre.DrugRequestPurchaseSpec_id
                where
                    drps.DrugRequest_id = :DrugRequest_id;
            ";
            $result = $this->db->query($query, array('DrugRequest_id' => $data['DrugRequest_id']));
            if (is_object($result)) {
                $result = $result->result('array');
                foreach($result as $spec) {
                    $spec_array[] = $spec['WhsDocumentSupplySpec_id'];
                }
            }

            $purchase_spec_array = array();

            foreach($dt as $record) {
                $record = (array) $record;

                if (!in_array($record['WhsDocumentSupplySpec_id'], $spec_array)) {
                    $spec_array[] = $record['WhsDocumentSupplySpec_id'];

                    $response = $this->saveObject('DrugRequestExec', array(
                        'DrugRequestPurchaseSpec_id' => $record['DrugRequestPurchaseSpec_id'],
                        'WhsDocumentSupplySpec_id' => $record['WhsDocumentSupplySpec_id'],
                        'DrugRequestExec_PurchCount' => $record['DrugRequestPurchaseSpec_Kolvo'],
                        'DrugRequestExec_SupplyCount' => $record['DrugOstatRegistry_Kolvo'],
                        'pmUser_id' => $data['pmUser_id']
                    ));

                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }

                    if (!in_array($record['DrugRequestPurchaseSpec_id'], $purchase_spec_array)) {
                        $purchase_spec_array[] = $record['DrugRequestPurchaseSpec_id'];
                    }
                }
            }

            foreach($purchase_spec_array as $purchase_spec_id) {
                $response = $this->recalculateDrugRequestPurchaseSpecData(array(
                    'DrugRequestPurchaseSpec_id' => $purchase_spec_id,
                    'pmUser_id' => $data['pmUser_id']
                ));

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        $result = array(
            'Error_Code' => null,
            'Error_Msg' => null
        );

        if (count($error) <= 0) {
            $this->commitTransaction();
        } else {
            $this->rollbackTransaction();
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Пересчет количественных данных для позиции спецификации сводной заявки
     */
    function recalculateDrugRequestPurchaseSpecData($data) {
        $query = "
            select
                sum(dre.DrugRequestExec_SupplyCount) as DrugRequestExec_SupplyCount,
                sum(dre.DrugRequestExec_Count) as DrugRequestExec_Count
            from
                v_DrugRequestExec dre with (nolock)
            where
                dre.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id;
        ";
        $exec_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
        ));

        $query = "
            select
                drps.DrugRequestPurchaseSpec_lKolvo,
                drps.DrugRequestPurchaseSpec_Price,
                drps.DrugRequestPurchaseSpec_RefuseCount
            from
                v_DrugRequestPurchaseSpec drps with (nolock)
            where
                drps.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id;
        ";
        $spec_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
        ));

        $rest_kolvo = !empty($exec_data['DrugRequestExec_SupplyCount']) ? $exec_data['DrugRequestExec_SupplyCount'] : 0;
        $exec_kolvo = !empty($exec_data['DrugRequestExec_Count']) ? $exec_data['DrugRequestExec_Count'] : 0;

        $l_kolvo = !empty($spec_data['DrugRequestPurchaseSpec_lKolvo']) ? $spec_data['DrugRequestPurchaseSpec_lKolvo'] : 0;
        $price = !empty($spec_data['DrugRequestPurchaseSpec_Price']) ? $spec_data['DrugRequestPurchaseSpec_Price'] : 0;
        $refuse_kolvo = !empty($spec_data['DrugRequestPurchaseSpec_RefuseCount']) ? $spec_data['DrugRequestPurchaseSpec_RefuseCount'] : 0;

        $p_kolvo = $l_kolvo - $refuse_kolvo - $rest_kolvo + $exec_kolvo;
        $p_sum = $price * $p_kolvo;

        $response = $this->saveObject('DrugRequestPurchaseSpec', array(
            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
            'DrugRequestPurchaseSpec_RestCount' => $rest_kolvo > 0 ? $rest_kolvo : null,
            'DrugRequestPurchaseSpec_pKolvo' => $p_kolvo > 0 ? $p_kolvo : null,
            'DrugRequestPurchaseSpec_pSum' => $p_sum > 0 ? $p_sum : null,
            'pmUser_id' => $data['pmUser_id']
        ));
        return $response;
    }

    /**
     * Проверка возможности удаления строки информации о исполнении сводной заявки
     */
    function checkDrugRequestExecDelete($data) {
        $result = array();

        if (empty($data['DrugRequest_id']) || empty($data['WhsDocumentSupplySpec_id'])) {
            $query = "
                select
                    drps.DrugRequest_id,
                    dre.WhsDocumentSupplySpec_id
                from
                    v_DrugRequestExec dre with (nolock)
                    left join v_DrugRequestPurchaseSpec drps with (nolock) on drps.DrugRequestPurchaseSpec_id = dre.DrugRequestPurchaseSpec_id
                where
                    dre.DrugRequestExec_id = :DrugRequestExec_id;
            ";
            $exec_data = $this->getFirstRowFromQuery($query, $data);
            $data['DrugRequest_id'] = $exec_data['DrugRequest_id'];
            $data['WhsDocumentSupplySpec_id'] = $exec_data['WhsDocumentSupplySpec_id'];
        }

        $query = "
            select
                count(wdoad.WhsDocumentOrderAllocationDrug_id) as cnt
            from
                v_WhsDocumentOrderAllocation wdoa with (nolock)
                left join v_WhsDocumentOrderAllocationDrug wdoad with (nolock) on wdoad.WhsDocumentOrderAllocation_id = wdoa.WhsDocumentOrderAllocation_id
            where
                wdoa.DrugRequest_id = :DrugRequest_id and
                wdoad.Drug_id in (
                    select
                        i_wdss.Drug_id
                    from
                        v_WhsDocumentSupplySpec i_wdss with (nolock)
                    where
                        i_wdss.WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id
                )
        ";
        $response = $this->getFirstRowFromQuery($query, $data);
        if (isset($response['cnt']) && $response['cnt'] > 0) {
            $result['Error_Msg'] = "Удаление данных об исполнении сводной заявки за счет указанного контракта не возможно, т.к. медикамент уже включен в разнарядку";
        }

        return $result;
    }

    /**
     * Вспомагательная функция для получения информации о медикаменте по идентификатору позиции сводной заявки или лота
     */
    function getWhsDocumentProcurementPriceDrugData($data) {
        $drug_data = array();

        if (!empty($data['DrugRequestPurchaseSpec_id'])) {
            $query = "
                select
                    drps.DrugComplexMnn_id,
                    drps.TRADENAMES_id as Tradenames_id,
                    o.Org_id
                from
                    v_DrugRequestPurchaseSpec drps with (nolock)
                    outer apply (
                        select top 1
                            i_drpr.Org_id
                        from
                            v_DrugRequestPurchase i_drp with (nolock)
                            left join v_DrugRequest i_dr with (nolock) on i_dr.DrugRequest_id = i_drp.DrugRequest_lid
                            left join v_DrugRequestProperty i_drpr with (nolock) on i_drpr.DrugRequestProperty_id = i_dr.DrugRequestProperty_id
                        where
                            i_drp.DrugRequest_id = drps.DrugRequest_id and
                            i_drpr.Org_id is not null
                        order by
                            i_drp.DrugRequestPurchase_id
	                ) o
                where
                    drps.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
            ";
            $drug_data = $this->getFirstRowFromQuery($query, $data);
        } else if (!empty($data['WhsDocumentProcurementRequestSpec_id'])) {
            $query = "
                select
                    wdpprs.DrugComplexMnn_id,
                    wdpprs.Tradenames_id,
                    o.Org_id
                from
                    v_WhsDocumentProcurementRequestSpec wdpprs with (nolock)
                    outer apply (
                        select top 1
                            i_drpr.Org_id
                        from
                            v_DrugRequestPurchaseSpec i_drps with (nolock)
                            left join v_DrugRequestPurchase i_drp with (nolock) on i_drp.DrugRequest_id = i_drps.DrugRequest_id
                            left join v_DrugRequest i_dr with (nolock) on i_dr.DrugRequest_id = i_drp.DrugRequest_lid
                            left join v_DrugRequestProperty i_drpr with (nolock) on i_drpr.DrugRequestProperty_id = i_dr.DrugRequestProperty_id
                        where
                            i_drps.DrugRequestPurchaseSpec_id = wdpprs.DrugRequestPurchaseSpec_id and
                            i_drpr.Org_id is not null
                        order by
                            i_drp.DrugRequestPurchase_id
                    ) o
                where
                    wdpprs.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id
            ";
            $drug_data = $this->getFirstRowFromQuery($query, $data);
        }

        return $drug_data;
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с ценами на медикаменты
     */
    function loadWhsDocumentProcurementPriceLinkList($data) {
        $where = array();

        if (!empty($data['DrugRequestPurchaseSpec_id'])) {
            $where[] = 'wdppl.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id';
        }

        if (!empty($data['WhsDocumentProcurementRequestSpec_id'])) {
            $where[] = 'wdppl.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id';
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
            declare
                @current_date date;

            set @current_date = dbo.tzGetDate();

            select
                wdppl.WhsDocumentProcurementPriceLink_id,
                wdppl.NOMEN_ID as Nomen_id,
                dpf.DrugPrep_Name as DrugPrepFas_Name,
                convert(varchar(10), wdppl.WhsDocumentProcurementPriceLink_PriceDate, 104) as WhsDocumentProcurementPriceLink_PriceDate, --Дата рег.цены
                wdppl.WhsDocumentProcurementPriceLink_PriceRub, --Зарег.цена произв. (руб.)
                isnull(DrugMarkup.Wholesale, 0) as Wholesale,
                isnull(DrugMarkup.Retail, 0) as Retail
            from
                v_WhsDocumentProcurementPriceLink wdppl with (nolock)
                left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = wdppl.Nomen_id
                inner join rls.Prep p with (nolock) on p.Prep_id = n.PREPID
                inner join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
                left join rls.v_Drug d with(nolock) on d.Drug_id = n.NOMEN_ID
                left join rls.v_DrugPrep dpf with(nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
                left join rls.AM_DF_LIMP adl with (nolock) on adl.ACTMATTERID = pa.MATTERID and adl.DRUGFORMID = p.DRUGFORMID
                left join rls.TN_DF_LIMP tdl with (nolock) on tdl.TRADENAMEID = p.TRADENAMEID and tdl.DRUGFORMID = p.DRUGFORMID
                left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = pa.MATTERID
                outer apply (
                    select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
                ) IsNarko
                outer apply (
                    select top 1
                        i_dm.DrugMarkup_id,
                        i_dm.DrugMarkup_Wholesale as Wholesale,
                        i_dm.DrugMarkup_Retail as Retail,
                        i_dm.DrugMarkup_begDT
                    from
                        v_DrugMarkup i_dm
                        left join v_YesNo is_narko on is_narko.YesNo_id = i_dm.DrugMarkup_IsNarkoDrug
                    where
                        n.PRICEINRUB between i_dm.DrugMarkup_MinPrice and i_dm.DrugMarkup_MaxPrice and
                        isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
                        (
                            n.PRICEDATE is null or (
                                i_dm.DrugMarkup_begDT <= n.PRICEDATE and
                                (
                                    i_dm.DrugMarkup_endDT is null or
                                    i_dm.DrugMarkup_endDT >= n.PRICEDATE
                                )
                            )
                        ) and
                        i_dm.DrugMarkup_begDT <= @current_date
                    order by
                        i_dm.DrugMarkup_begDT
                ) DrugMarkup
			$where_clause
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка цен на медикаменты для формы добавления
     */
    function loadWhsDocumentProcurementPriceLinkSourceList($data) {
        $drug_data = $this->getWhsDocumentProcurementPriceDrugData($data);

        $data['DrugComplexMnn_id'] = !empty($drug_data['DrugComplexMnn_id']) ? $drug_data['DrugComplexMnn_id'] : null;
        $data['Tradenames_id'] = !empty($drug_data['Tradenames_id']) ? $drug_data['Tradenames_id'] : null;
        $data['Org_id'] = !empty($drug_data['Org_id']) ? $drug_data['Org_id'] : null;

        $query = "
            declare
                @current_date date;

            set @current_date = dbo.tzGetDate();

            select
                n.NOMEN_ID as Nomen_id,
                dpf.DrugPrep_Name as DrugPrepFas_Name,
                convert(varchar(10), n.PRICEDATE, 104) as WhsDocumentProcurementPriceLink_PriceDate, --Дата рег.цены
                n.PRICEINRUB as WhsDocumentProcurementPriceLink_PriceRub, --Зарег.цена произв. (руб.)
                isnull(DrugMarkup.Wholesale, 0) as Wholesale,
                isnull(DrugMarkup.Retail, 0) as Retail
            from
                rls.v_Nomen n with (nolock)
                inner join rls.Prep p with (nolock) on p.Prep_id = n.PREPID
                inner join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
                left join rls.v_Drug d with(nolock) on d.Drug_id = n.NOMEN_ID
                left join rls.v_DrugPrep dpf with(nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
                left join rls.AM_DF_LIMP adl with (nolock) on adl.ACTMATTERID = pa.MATTERID and adl.DRUGFORMID = p.DRUGFORMID
                left join rls.TN_DF_LIMP tdl with (nolock) on tdl.TRADENAMEID = p.TRADENAMEID and tdl.DRUGFORMID = p.DRUGFORMID
                left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = pa.MATTERID
                outer apply (
                    select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
                ) IsNarko
                outer apply (
                    select top 1
                        i_dm.DrugMarkup_id,
                        i_dm.DrugMarkup_Wholesale as Wholesale,
                        i_dm.DrugMarkup_Retail as Retail,
                        i_dm.DrugMarkup_begDT
                    from
                        v_DrugMarkup i_dm
                        left join v_YesNo is_narko on is_narko.YesNo_id = i_dm.DrugMarkup_IsNarkoDrug
                    where
                        n.PRICEINRUB between i_dm.DrugMarkup_MinPrice and i_dm.DrugMarkup_MaxPrice and
                        isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
                        (
                            n.PRICEDATE is null or (
                                i_dm.DrugMarkup_begDT <= n.PRICEDATE and
                                (
                                    i_dm.DrugMarkup_endDT is null or
                                    i_dm.DrugMarkup_endDT >= n.PRICEDATE
                                )
                            )
                        ) and
                        i_dm.DrugMarkup_begDT <= @current_date
                    order by
                        i_dm.DrugMarkup_begDT
                ) DrugMarkup
            where
                (
                    adl.ACTMATTERID is not null or
                    tdl.DRUGFORMID is not null
                ) and
                n.PRICEINRUB is not null and
                (
                    d.DrugComplexMnn_id = :DrugComplexMnn_id and
                    (
                        :Tradenames_id is null or d.DrugTorg_id = :Tradenames_id
                    )
                )
            order by
                dpf.DrugPrep_Name desc

		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с коммерческими предложениями
     */
    function loadWhsDocumentCommercialOfferDrugList($data) {
        $where = array();

        if (!empty($data['DrugRequestPurchaseSpec_id'])) {
            $where[] = 'wdcod.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id';
        }

        if (!empty($data['WhsDocumentProcurementRequestSpec_id'])) {
            $where[] = 'wdcod.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id';
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
            select
                wdcod.WhsDocumentCommercialOfferDrug_id,
                wdcod.CommercialOfferDrug_id,
                cod.CommercialOfferDrug_Price,
                co.CommercialOffer_begDT,
                o_s.Org_Name as Supplier_Name,
                dpfc.DrugPrepFasCode_Code,
                dpf.DrugPrep_Name as DrugPrepFas_Name
            from
                v_WhsDocumentCommercialOfferDrug wdcod with (nolock)
                left join v_CommercialOfferDrug cod with (nolock)  on cod.CommercialOfferDrug_id = wdcod.CommercialOfferDrug_id
                left join v_CommercialOffer co with (nolock)  on co.CommercialOffer_id = cod.CommercialOffer_id
                left join v_Org o_s with (nolock) on o_s.Org_id = co.Org_id
                left join rls.v_DrugPrep dpf with (nolock) on dpf.DrugPrepFas_id = cod.DrugPrepFas_id
                outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                        i_dpfc.DrugPrepFas_id = dpf.DrugPrepFas_id and (
                            :UserOrg_id is null or
                            i_dpfc.Org_id = :UserOrg_id
                        )
                    order by
                        i_dpfc.DrugPrepFasCode_id
                ) dpfc
			$where_clause
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка коммерческих предложений для формы добавления
     */
    function loadWhsDocumentCommercialOfferDrugSourceList($data) {
        $drug_data = $this->getWhsDocumentProcurementPriceDrugData($data);

        $data['DrugComplexMnn_id'] = !empty($drug_data['DrugComplexMnn_id']) ? $drug_data['DrugComplexMnn_id'] : null;
        $data['Tradenames_id'] = !empty($drug_data['Tradenames_id']) ? $drug_data['Tradenames_id'] : null;
        $data['Org_id'] = !empty($drug_data['Org_id']) ? $drug_data['Org_id'] : null;

        $query = "
			with drug_list as (
                select
                    i_d.Drug_id,
                    i_d.DrugPrepFas_id,
                    i_d.DrugComplexMnn_id
                from
                    rls.v_Drug i_d
                where
                    i_d.DrugComplexMnn_id = :DrugComplexMnn_id and
                    (
                        :Tradenames_id is null or i_d.DrugTorg_id = :Tradenames_id
                    )
            )
            select
                cod.CommercialOfferDrug_id,
                cod.CommercialOffer_id,
                convert(varchar(10), co.CommercialOffer_begDT, 104) as CommercialOffer_begDT,
                o_s.Org_Name as Supplier_Name,
                o_d.Org_Name as Org_D_Name,
                cod.CommercialOfferDrug_Price,
                dcmn.DrugComplexMnnName_Name,
                dpfc.DrugPrepFasCode_Code,
                dpf.DrugPrep_Name as DrugPrepFas_Name
            from
                (
                    select
                        i_co.CommercialOffer_id,
                        i_co.Org_id,
                        i_co.Org_did,
                        i_co.CommercialOffer_begDT
                    from
                        v_CommercialOffer i_co with (nolock)
                    where
                        i_co.CommercialOffer_id in (
                            select
                                i_cod.CommercialOffer_id
                            from
                                v_CommercialOfferDrug i_cod with (nolock)
                                inner join drug_list on drug_list.DrugPrepFas_id = i_cod.DrugPrepFas_id
                        )
                ) co
                left join v_CommercialOfferDrug cod with (nolock) on cod.CommercialOffer_id = co.CommercialOffer_id
                left join v_Org o_s with (nolock) on o_s.Org_id = co.Org_id
                left join v_Org o_d with (nolock) on o_d.Org_id = co.Org_did
                inner join drug_list d with (nolock) on d.DrugPrepFas_id = cod.DrugPrepFas_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_DrugPrep dpf with (nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
                outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                        i_dpfc.DrugPrepFas_id = dpf.DrugPrepFas_id and (
                            :UserOrg_id is null or
                            i_dpfc.Org_id = :UserOrg_id
                        )
                    order by
                        i_dpfc.DrugPrepFasCode_id
                ) dpfc
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с позициями ГК
     */
    function loadWhsDocumentProcurementSupplySpecList($data) {
        $where = array();

        if (!empty($data['DrugRequestPurchaseSpec_id'])) {
            $where[] = 'wdpss.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id';
        }

        if (!empty($data['WhsDocumentProcurementRequestSpec_id'])) {
            $where[] = 'wdpss.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id';
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
			select
                wdpss.WhsDocumentProcurementSupplySpec_id,
                wdpss.WhsDocumentSupplySpec_id,
                wdss.WhsDocumentSupplySpec_Price,
                convert(varchar(10), wds.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
                wds.WhsDocumentUc_Num,
                o_s.Org_Name as Supplier_Name,
                dpfc.DrugPrepFasCode_Code,
                dpf.DrugPrep_Name as DrugPrepFas_Name
			from
				v_WhsDocumentProcurementSupplySpec wdpss with (nolock)
				left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupplySpec_id = wdpss.WhsDocumentSupplySpec_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdss.WhsDocumentSupply_id
				left join v_Org o_s with (nolock) on o_s.Org_id = wds.Org_sid
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdss.Drug_id
				left join rls.v_DrugPrep dpf with (nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
				outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                        i_dpfc.DrugPrepFas_id = dpf.DrugPrepFas_id and (
                            :UserOrg_id is null or
                            i_dpfc.Org_id = :UserOrg_id
                        )
                    order by
                        i_dpfc.DrugPrepFasCode_id
				) dpfc
			$where_clause
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка позиций ГК для формы добавления
     */
    function loadWhsDocumentProcurementSupplySpecSourceList($data) {
        $drug_data = $this->getWhsDocumentProcurementPriceDrugData($data);

        $data['DrugComplexMnn_id'] = !empty($drug_data['DrugComplexMnn_id']) ? $drug_data['DrugComplexMnn_id'] : null;
        $data['Tradenames_id'] = !empty($drug_data['Tradenames_id']) ? $drug_data['Tradenames_id'] : null;
        $data['Org_id'] = !empty($drug_data['Org_id']) ? $drug_data['Org_id'] : null;

        $query = "
			with drug_list as (
                select
                    i_d.Drug_id,
                    i_d.DrugPrepFas_id,
                    i_d.DrugComplexMnn_id
                from
                    rls.v_Drug i_d
                where
                    i_d.DrugComplexMnn_id = :DrugComplexMnn_id and
                    (
                        :Tradenames_id is null or i_d.DrugTorg_id = :Tradenames_id
                    )
            )
            select
                wdss.WhsDocumentSupplySpec_id,
                convert(varchar(10), wds.WhsDocumentUc_Date, 104) as WhsDocumentUc_Date,
                wds.WhsDocumentUc_Num,
                o_s.Org_Name as Supplier_Name,
                wdss.WhsDocumentSupplySpec_Price,
                dcmn.DrugComplexMnnName_Name,
                dpfc.DrugPrepFasCode_Code,
                dpf.DrugPrep_Name as DrugPrepFas_Name
            from
                (
                    select top 10 -- последние 10 контрактов по дате
                        i_wds.WhsDocumentSupply_id,
                        i_wds.Org_sid,
                        i_wds.WhsDocumentUc_Date,
                        i_wds.WhsDocumentUc_Num
                    from
                        v_WhsDocumentSupply i_wds with (nolock)
                        left join v_WhsDocumentStatusType i_wdst on i_wdst.WhsDocumentStatusType_id = i_wds.WhsDocumentStatusType_id
                        left join v_WhsDocumentType i_wdt on i_wdt.WhsDocumentType_id = i_wds.WhsDocumentType_id
                    where
                        (
                            i_wdt.WhsDocumentType_Code = 18 or ( -- 18 - Контракт ввода остатков
                                WhsDocumentType_Code in (3, 6) and -- 3 - Контракт на поставку; 6 - Контракт на поставку и отпуск
                                i_wdst.WhsDocumentStatusType_Code = 2 -- 2 - Действующий
                            )
                        ) and
                        i_wds.Org_pid = :Org_id and -- Плательщик по ГК соответствует координатору списка медикаментов
                        i_wds.WhsDocumentSupply_id in (
                            select
                                i_wdss.WhsDocumentSupply_id
                            from
                                v_WhsDocumentSupplySpec i_wdss with (nolock)
                                inner join drug_list on drug_list.Drug_id = i_wdss.Drug_id
                        )
                    order by
                        i_wds.WhsDocumentUc_Date desc
                ) wds
                left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                left join v_Org o_s with (nolock) on o_s.Org_id = wds.Org_sid
                inner join drug_list d with (nolock) on d.Drug_id = wdss.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_DrugPrep dpf with (nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
                outer apply (
                    select top 1
                        i_dpfc.DrugPrepFasCode_Code
                    from
                        rls.v_DrugPrepFasCode i_dpfc with (nolock)
                    where
                        i_dpfc.DrugPrepFas_id = dpf.DrugPrepFas_id and (
                            :UserOrg_id is null or
                            i_dpfc.Org_id = :UserOrg_id
                        )
                    order by
                        i_dpfc.DrugPrepFasCode_id
                ) dpfc
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Сохранение строк из сереализованного массива
     */
    function saveWhsDocumentProcurementPriceLinkFromJSON($data) {
        $result = array();
        if (!empty($data['json_str']) && ($data['DrugRequestPurchaseSpec_id'] > 0 || $data['WhsDocumentProcurementRequestSpec_id'] > 0)) {
            ConvertFromWin1251ToUTF8($data['json_str']);
            $dt = (array) json_decode($data['json_str']);
            foreach($dt as $record) {
                switch($record->state) {
                    case 'add':
                    case 'edit':
                        $save_data = array(
                            'WhsDocumentProcurementPriceLink_id' => $record->state == 'edit' ? $record->WhsDocumentProcurementPriceLink_id : null,
                            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                            'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                            'Nomen_id' => !empty($record->Nomen_id) ? $record->Nomen_id : null,
                            'WhsDocumentProcurementPriceLink_PriceRub' => !empty($record->WhsDocumentProcurementPriceLink_PriceRub) ? $record->WhsDocumentProcurementPriceLink_PriceRub : null,
                            'WhsDocumentProcurementPriceLink_PriceDate' => !empty($record->WhsDocumentProcurementPriceLink_PriceDate) ? $record->WhsDocumentProcurementPriceLink_PriceDate : null,
                            'pmUser_id' => $data['pmUser_id']
                        );

                        //сохранение строки
                        $response = $this->saveObject('WhsDocumentProcurementPriceLink', $save_data);
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                    case 'delete':
                        //удаление строки
                        $response = $this->deleteObject('WhsDocumentProcurementPriceLink', array(
                            'WhsDocumentProcurementPriceLink_id' => $record->WhsDocumentProcurementPriceLink_id
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                }
                if (!empty($result['Error_Msg'])) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Сохранение строк из сереализованного массива
     */
    function saveWhsDocumentCommercialOfferDrugFromJSON($data) {
        $result = array();
        if (!empty($data['json_str']) && ($data['DrugRequestPurchaseSpec_id'] > 0 || $data['WhsDocumentProcurementRequestSpec_id'] > 0)) {
            ConvertFromWin1251ToUTF8($data['json_str']);
            $dt = (array) json_decode($data['json_str']);
            foreach($dt as $record) {
                switch($record->state) {
                    case 'add':
                    case 'edit':
                        $save_data = array(
                            'WhsDocumentCommercialOfferDrug_id' => $record->state == 'edit' ? $record->WhsDocumentCommercialOfferDrug_id : null,
                            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                            'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                            'CommercialOfferDrug_id' => !empty($record->CommercialOfferDrug_id) ? $record->CommercialOfferDrug_id : null,
                            'pmUser_id' => $data['pmUser_id']
                        );

                        //сохранение строки
                        $response = $this->saveObject('WhsDocumentCommercialOfferDrug', $save_data);
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                    case 'delete':
                        //удаление строки
                        $response = $this->deleteObject('WhsDocumentCommercialOfferDrug', array(
                            'WhsDocumentCommercialOfferDrug_id' => $record->WhsDocumentCommercialOfferDrug_id
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                }
                if (!empty($result['Error_Msg'])) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Сохранение строк из сереализованного массива
     */
    function saveWhsDocumentProcurementSupplySpecFromJSON($data) {
        $result = array();
        if (!empty($data['json_str']) && ($data['DrugRequestPurchaseSpec_id'] > 0 || $data['WhsDocumentProcurementRequestSpec_id'] > 0)) {
            ConvertFromWin1251ToUTF8($data['json_str']);
            $dt = (array) json_decode($data['json_str']);
            foreach($dt as $record) {
                switch($record->state) {
                    case 'add':
                    case 'edit':
                        $save_data = array(
                            'WhsDocumentProcurementSupplySpec_id' => $record->state == 'edit' ? $record->WhsDocumentProcurementSupplySpec_id : null,
                            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                            'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                            'WhsDocumentSupplySpec_id' => !empty($record->WhsDocumentSupplySpec_id) ? $record->WhsDocumentSupplySpec_id : null,
                            'pmUser_id' => $data['pmUser_id']
                        );

                        //сохранение строки
                        $response = $this->saveObject('WhsDocumentProcurementSupplySpec', $save_data);
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                    case 'delete':
                        //удаление строки
                        $response = $this->deleteObject('WhsDocumentProcurementSupplySpec', array(
                            'WhsDocumentProcurementSupplySpec_id' => $record->WhsDocumentProcurementSupplySpec_id
                        ));
                        if (!empty($response['Error_Msg'])) {
                            $result['Error_Msg'] = $response['Error_Msg'];
                        }
                        break;
                }
                if (!empty($result['Error_Msg'])) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Загрузка данных для рассчета цен
     */
    function loadWhsDocumentProcurementPrice($data) {
        $result = array();

        if (!empty($data['DrugRequestPurchaseSpec_id'])) {
            $query = "
                select
                    drps.DrugRequestPurchaseSpec_lKolvo as TotalKolvo,
                    cast(drps.DrugRequestPurchaseSpec_Price as decimal(12,2)) as TotalPrice,
                    convert(varchar(10), drps.DrugRequestPurchaseSpec_priceDate, 104) as CalculationDate,
                    CalculatPriceType_id
                from
                    v_DrugRequestPurchaseSpec drps with (nolock)
                where
                    drps.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
            ";
            $result = $this->getFirstRowFromQuery($query, $data);
        } else if (!empty($data['WhsDocumentProcurementRequestSpec_id'])) {
            $query = "
                select
                    wdpprs.WhsDocumentProcurementRequestSpec_Kolvo as TotalKolvo,
                    wdpprs.WhsDocumentProcurementRequestSpec_PriceMax as TotalPrice,
                    wdpprs.WhsDocumentProcurementRequestSpec_CalcPriceDate as CalculationDate,
	                o.Okei_Name,
	                gpc.GoodsPackCount_Count,
                    CalculatPriceType_id
                from
                    v_WhsDocumentProcurementRequestSpec wdpprs with (nolock)
                    left join v_Okei o on o.Okei_id = wdpprs.Okei_id
                    outer apply (
                        select top 1
                            i_gpc.GoodsPackCount_Count
                        from
                            v_GoodsPackCount i_gpc with (nolock)
                        where
                            i_gpc.GoodsUnit_id = wdpprs.GoodsUnit_id and
                            i_gpc.DrugComplexMnn_id = wdpprs.DrugComplexMnn_id and
                            (
                                wdpprs.Tradenames_id is null or i_gpc.TRADENAMES_ID = wdpprs.Tradenames_id
                            )
                        order by
                            i_gpc.GoodsPackCount_id
                    ) gpc
                where
                    wdpprs.WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id
            ";
            $result = $this->getFirstRowFromQuery($query, $data);
        }

        $result = $this->getFirstRowFromQuery($query, $data);

        if (is_array($result)) {
            return array($result);
        } else {
            return false;
        }
    }

    /**
     * Функция обновления данных о цене и информации о расчете цены для строк сводной заявки
     *
     * Данные сохраняются для конкретной строки, а после дублируются в строки той же заявки с анологичными DrugComplexMnn_id, TRADENAMES_id и Evn_id
     */
    function saveWhsDocumentProcurementPriceDataInRequestSpec($data) {
        $error = array();
        $result = array();
        $spec_id_list = array();

        $query = "
            select
                drps.DrugRequestPurchaseSpec_id,
                drps.DrugRequestPurchaseSpec_Kolvo,
                drps.DrugRequestPurchaseSpec_lKolvo,
                drps.DrugRequestPurchaseSpec_pKolvo
            from
                v_DrugRequestPurchaseSpec own_drps with (nolock)
                left join v_DrugRequestPurchaseSpec drps with (nolock) on
                    drps.DrugRequest_id = own_drps.DrugRequest_id and
                    drps.DrugComplexMnn_id = own_drps.DrugComplexMnn_id and
                    isnull(drps.TRADENAMES_id, 0) = isnull(own_drps.TRADENAMES_id, 0) and
                    isnull(drps.Evn_id, 0) = isnull(own_drps.Evn_id, 0)
            where
                own_drps.DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
        ";
        $spec_list = $this->queryResult($query, array(
            'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
        ));

        for ($i = 0; $i < count($spec_list); $i++) {
            $price = !empty($data['TotalPrice']) ? $data['TotalPrice'] : 0;
            $kolvo = !empty($spec_list[$i]['DrugRequestPurchaseSpec_Kolvo']) ? $spec_list[$i]['DrugRequestPurchaseSpec_Kolvo'] : 0;
            $l_kolvo = !empty($spec_list[$i]['DrugRequestPurchaseSpec_lKolvo']) ? $spec_list[$i]['DrugRequestPurchaseSpec_lKolvo'] : 0;
            $p_kolvo = !empty($spec_list[$i]['DrugRequestPurchaseSpec_pKolvo']) ? $spec_list[$i]['DrugRequestPurchaseSpec_pKolvo'] : 0;
            $sum = $price * $kolvo;
            $l_sum = $price * $l_kolvo;
            $p_sum = $price * $p_kolvo;

            $response = $this->MzDrugRequest_model->saveObject('DrugRequestPurchaseSpec', array(
                'DrugRequestPurchaseSpec_id' => $spec_list[$i]['DrugRequestPurchaseSpec_id'],
                'DrugRequestPurchaseSpec_Price' => $price > 0 ? $price : null,
                'DrugRequestPurchaseSpec_priceDate' => $data['CalculationDate'],
                'DrugRequestPurchaseSpec_Sum' => $sum > 0 ? $sum : null,
                'DrugRequestPurchaseSpec_lSum' => $l_sum > 0 ? $l_sum : null,
                'DrugRequestPurchaseSpec_pSum' => $p_sum > 0 ? $p_sum : null,
                'CalculatPriceType_id' => $data['CalculatPriceType_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }

            //сбор идентификаторов строк
            if (count($error) == 0 && $spec_list[$i]['DrugRequestPurchaseSpec_id'] != $data['DrugRequestPurchaseSpec_id']) {
                $spec_id_list[] = $spec_list[$i]['DrugRequestPurchaseSpec_id'];
            }

            if (count($error) > 0) {
                break;
            }
        }

        //удаление информации о прежних расчетах
        if (count($error) == 0 && count($spec_id_list) > 0) {
            $spec_id_str = join(',', $spec_id_list);

            $query = "
                delete from
                    WhsDocumentProcurementPriceLink
                where
                    DrugRequestPurchaseSpec_id in ({$spec_id_str});

                delete from
                    WhsDocumentCommercialOfferDrug
                where
                    DrugRequestPurchaseSpec_id in ({$spec_id_str});

                delete from
                    WhsDocumentProcurementSupplySpec
                where
                    DrugRequestPurchaseSpec_id in ({$spec_id_str});
            ";
            $response = $this->db->query($query);
        }

        //дублирование информации о расчете цены из главной строки
        if (count($error) == 0 && count($spec_id_list) > 0) {
            $obj_array = array('WhsDocumentProcurementPriceLink', 'WhsDocumentCommercialOfferDrug', 'WhsDocumentProcurementSupplySpec');

            foreach($obj_array as $obj) {
                $query = "
                    select
                        {$obj}_id
                    from
                        v_{$obj} with (nolock)
                    where
                        DrugRequestPurchaseSpec_id = :DrugRequestPurchaseSpec_id
                ";
                $item_array = $this->queryResult($query, array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
                ));
                if (is_array($item_array)) {
                    foreach($item_array as $item) {
                        foreach($spec_id_list as $spec_id) {
                            $params = array();
                            $params[$obj.'_id'] = $item[$obj.'_id'];
                            $params['DrugRequestPurchaseSpec_id'] = $spec_id;
                            $params['pmUser_id'] = $data['pmUser_id'];
                            $response = $this->copyObject($obj, $params);
                            if (!empty($response['Error_Msg'])) {
                                $error[] = $response['Error_Msg'];
                                break;
                            }
                        }
                        if (count($error) > 0) {
                            break;
                        }
                    }
                }
                if (count($error) > 0) {
                    break;
                }
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
	* Получение списка льгот человека
	*/
	function getPersonPrivilegeData($data) {
		if(empty($data['Person_id'])){
			return array();
		}
		$query = "
			select
				PT.DrugFinance_id
			from v_PersonPrivilege PP with (nolock)
				inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
			where 
				PP.Person_id = ISNULL(:Person_id, 0) and 
				PP.PersonPrivilege_begDate <= dbo.tzGetDate() and
				(
					PP.PersonPrivilege_endDate is null or
					PP.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)
				)
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Проверка количества медикамента
	*/
	function checkDrugAmount($data) {
        $result = array(
            'successs' => false,
            'distinction' => null,
            'Error_Msg' => null
        );

		try {
			if (!empty($data['DrugComplexMnn_id']) || !empty($data['Tradenames_id'])) {
				$query = "
					select
						isnull(sum(drpo.DrugRequestPersonOrder_OrdKolvo), 0) as drpo_kolvo
					from
						v_DrugRequestPersonOrder drpo with (nolock)
					where
						drpo.DrugRequest_id = :DrugRequest_id and
						isnull(drpo.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
						isnull(drpo.Tradenames_id, 0) = isnull(:Tradenames_id, 0) and
						(:DrugRequestPersonOrder_id is null or drpo.DrugRequestPersonOrder_id <> :DrugRequestPersonOrder_id)
				";
				$drpo_data = $this->getFirstRowFromQuery($query, $data);
				if (!isset($drpo_data['drpo_kolvo'])) {
					throw new Exception('При получении данных о количестве медикамента в разнарядке произошла ошибка');
				}

				$query = "
					select
						isnull(sum(drr.DrugRequestRow_Kolvo), 0) as drr_kolvo
					from
						v_DrugRequestRow drr with (nolock)
					where
						drr.DrugRequest_id = :DrugRequest_id and
						isnull(drr.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
						isnull(drr.TRADENAMES_id, 0) = isnull(:Tradenames_id, 0)
				";
				$drr_data = $this->getFirstRowFromQuery($query, $data);
				if (!isset($drr_data['drr_kolvo'])) {
					throw new Exception('При получении данных о количестве медикамента в заявке произошла ошибка');
				}

				$result['distinction'] = $drr_data['drr_kolvo'] * 1 - $drpo_data['drpo_kolvo'] * 1 - $data['DrugRequestPersonOrder_OrdKolvo'] * 1;
			} else {
				$result['distinction'] = 0;
			}
			$result['successs'] = true;
		} catch (Exception $e) {
			$result['successs'] = false;
			$result['Error_Msg'] = $e->getMessage();
		}

		return $result;
	}

	/**
	* Проверка наличия пациента в разнарядке "первой копии" заявки
	*/
	function checkExistPersonInFirstCopy($data) {
        $result = array(
            'drpo_cnt' => 0
        );

		$query = "
			select
				count(drpo.DrugRequestPersonOrder_id) as drpo_cnt
			from
			    v_DrugRequestPersonOrder drpo with (nolock)
			where
				drpo.DrugRequest_id = :DrugRequestFirstCopy_id and
				drpo.Person_id = :Person_id and
				drpo.DrugComplexMnn_id is null and
				drpo.Tradenames_id is null
		";
        $res = $this->getFirstRowFromQuery($query, $data);
        if (isset($res['drpo_cnt'])) {
            $result['drpo_cnt'] = $res['drpo_cnt'];
        }

		return $result;
	}

	/**
	* Проверка наличия медикамента в разнарядках "первой копии" заявочной кампании
	*/
	function checkExistPersonDrugInRegionFirstCopy($data) {
        $result = array(
            'drpo_cnt' => 0,
            'drpo_kolvo' => 0
        );

		$query = "
		    declare
                @DrugRequestPeriod_id bigint = null,
                @PersonRegisterType_id bigint = null,
                @DrugRequestKind_id bigint = null,
                @DrugGroup_id bigint = null,
                @DrugRequestCategory_id bigint = null;

            select
                @DrugRequestPeriod_id = DrugRequestPeriod_id,
                @PersonRegisterType_id = PersonRegisterType_id,
                @DrugRequestKind_id = DrugRequestKind_id,
                @DrugGroup_id = DrugGroup_id,
                @DrugRequestCategory_id = DrugRequestCategory_id
            from
                v_DrugRequest with (nolock)
            where
                DrugRequest_id = :DrugRequestFirstCopy_id;

			select top 1
				1 as drpo_cnt,
				drpo.DrugRequestPersonOrder_OrdKolvo as drpo_kolvo
			from
				v_DrugRequest dr with(nolock)
				left join v_DrugRequestPersonOrder drpo with(nolock) on drpo.DrugRequest_id = dr.DrugRequest_id
			where
				dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                isnull(dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                isnull(dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
		        isnull(dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
		        dr.DrugRequestCategory_id = @DrugRequestCategory_id and
		        dr.DrugRequest_Version = 1 and
				drpo.Person_id = :Person_id and
				isnull(drpo.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
				isnull(drpo.Tradenames_id, 0) = isnull(:Tradenames_id, 0);
		";
        $res = $this->getFirstRowFromQuery($query, $data);
        if (isset($res['drpo_cnt'])) {
            $result['drpo_cnt'] = $res['drpo_cnt'];
            $result['drpo_kolvo'] = $res['drpo_kolvo'];
        }

		return $result;
	}

    /**
     * Проверка наличия медикамента в разнарядках заявки
     */
    function checkExistsDrugRequestPersonOrderForDrugRequestRow($data) {
        $result = array(
            'drpo_cnt' => 0,
            'drpo_kolvo' => 0
        );

        if (!empty($data['DrugRequestRow_id'])) {
            $query = "
                select
                    count(DrugRequestPersonOrder_id) as drpo_cnt,
                    sum(drpo.DrugRequestPersonOrder_OrdKolvo) as drpo_kolvo
                from
                    v_DrugRequestRow drr with(nolock)
                    left join v_DrugRequestPersonOrder drpo with(nolock) on drpo.DrugRequest_id = drr.DrugRequest_id
                where
                    drr.DrugRequestRow_id = :DrugRequestRow_id and
                    isnull(drpo.DrugComplexMnn_id, 0) = isnull(drr.DrugComplexMnn_id, 0) and
                    isnull(drpo.Tradenames_id, 0) = isnull(drr.TRADENAMES_id, 0);
            ";
        } else {
            $query = "
                select
                    count(DrugRequestPersonOrder_id) as drpo_cnt,
                    sum(drpo.DrugRequestPersonOrder_OrdKolvo) as drpo_kolvo
                from
                    v_DrugRequestPersonOrder drpo with(nolock)
                where
                    drpo.DrugRequest_id = :DrugRequest_id and
                    isnull(drpo.DrugComplexMnn_id, 0) = isnull(:DrugComplexMnn_id, 0) and
                    isnull(drpo.Tradenames_id, 0) = isnull(:TRADENAMES_id, 0);
            ";
        }

        $res = $this->getFirstRowFromQuery($query, $data);
        if (isset($res['drpo_cnt'])) {
            $result['drpo_cnt'] = $res['drpo_cnt'];
            $result['drpo_kolvo'] = $res['drpo_kolvo'];
        }

        return $result;
    }

    /**
     * Дублирование данных cтрок разнарядки в "первую копию заявки", с проверкой на уникальность
     */
    function copyDrugRequestPersonOrderToFirstCopy($data) {
        $result = array(
            'success' => false,
            'DrugRequestPersonOrder_id' => null,
            'Error_Code' => null,
            'Error_Msg' => null
        );

        if (!empty($data['DrugRequestPersonOrder_id']) && !empty($data['DrugRequestFirstCopy_id'])) {
            //проверка на уникальность в рамках "первой копии"
            $query = "
                declare
                    @Person_id bigint,
                    @DrugComplexMnn_id bigint,
                    @Tradenames_id bigint;

                select
                    @Person_id = Person_id,
                    @DrugComplexMnn_id = DrugComplexMnn_id,
                    @Tradenames_id = Tradenames_id
                from
                    v_DrugRequestPersonOrder with(nolock)
                where
                    DrugRequestPersonOrder_id = :DrugRequestPersonOrder_id;

                select
                    DrugRequestPersonOrder_id
                from
                    v_DrugRequestPersonOrder with(nolock)
                where
                    DrugRequest_id = :DrugRequestFirstCopy_id and
                    Person_id = @Person_id and
                    isnull(DrugComplexMnn_id, 0) = isnull(@DrugComplexMnn_id, 0) and
                    isnull(Tradenames_id, 0) = isnull(@Tradenames_id, 0) and
                    DrugRequestPersonOrder_Copy = '1';
            ";
            $id = $this->getFirstResultFromQuery($query, $data);
            if (empty($id)) {
                $res = $this->copyObject('DrugRequestPersonOrder', array(
                    'DrugRequestPersonOrder_id' => $data['DrugRequestPersonOrder_id'],
                    'DrugRequest_id' => $data['DrugRequestFirstCopy_id'],
                    'DrugRequestPersonOrder_Copy' => 1
                ));
                if (!empty($res['DrugRequestPersonOrder_id'])) {
                    $result['success'] = true;
                    $result['DrugRequestPersonOrder_id'] = $res['DrugRequestPersonOrder_id'];
                } else {
                    $result['Error_Msg'] = !empty($res['Error_Msg'])  ? $res['Error_Msg'] : 'При копириовании строки разнарядки произош1ла ошибка.';
                }
            }
        } else {
            $result['Error_Msg'] = 'Не переданы обязательные параметры. Сохранение копии строки разнарядки невозможно.';
        }

        return $result;
    }

    /**
     * Копирование плановых параметров в "первую копию заявки", с удалением существующих
     */
	function copyDrugRequestPlanToFirstCopy($data) {
        $result = array(
            'success' => false,
            'Error_Code' => null,
            'Error_Msg' => null
        );

        try {
        	$this->beginTransaction();

			//получение данных заявочой кампании
			$query = "
				select
					dr.DrugRequest_id,
					dr.PersonRegisterType_id,
					dr.DrugRequestPeriod_id,
					dr.DrugRequestKind_id,
					dr.DrugGroup_id,
					fc.DrugRequest_id as RegionDrugRequestFirstCopy_id
				from
					v_DrugRequest dr with (nolock)
					outer apply (
						select top 1
							fc_dr.DrugRequest_id
						from
							v_DrugRequest fc_dr with(nolock)
							left join v_DrugRequestCategory fc_drc with(nolock) on fc_drc.DrugRequestCategory_id = fc_dr.DrugRequestCategory_id
						where
							fc_dr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id and
							isnull(fc_dr.PersonRegisterType_id, 0) = isnull(dr.PersonRegisterType_id, 0) and
							isnull(fc_dr.DrugRequestKind_id, 0) = isnull(dr.DrugRequestKind_id, 0) and
							isnull(fc_dr.DrugGroup_id, 0) = isnull(dr.DrugGroup_id, 0) and
							fc_dr.DrugRequest_Version = 1 and
							fc_drc.DrugRequestCategory_SysNick = 'region'
					) fc
				where
					dr.DrugRequest_id = :RegionDrugRequest_id and
					dr.DrugRequest_Version is null
			";
			$dr_data = $this->getFirstRowFromQuery($query, array(
				'RegionDrugRequest_id' => $data['RegionDrugRequest_id']
			));
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("Не удалось получить данные заявочной кампании");
			}
			if (empty($dr_data['DrugRequest_id'])) {
				throw new Exception("Не найдена первая копия заявочной кампании");
			}

			//удаление существующих плановых параметров первой копии
			$query = "
				declare
					@Error_Code int,
					@Error_Message varchar(4000)
				set nocount on
				begin try
					delete from
						DrugRequestPlan
					where
						DrugRequest_id = :RegionDrugRequestFirstCopy_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$res = $this->getFirstRowFromQuery($query, array(
				'RegionDrugRequestFirstCopy_id' => $dr_data['RegionDrugRequestFirstCopy_id']
			));
			if (!is_array($res) || !empty($res['Error_Msg'])) {
				throw new Exception("При удалении плановых параметров первой копии произошла ошибка".$res['Error_Msg']);
			}

			//копирование плановых параметров из оригинала
			$pmuser_id = $this->getPromedUserId();
			$select_array = array();
			$fields_array = array_keys($this->getFirstRowFromQuery("select top 1 * from v_DrugRequestPlan"));
			unset($fields_array[0]);

			foreach($fields_array as $field) {
				switch($field) {
					case 'DrugRequest_id':
						$select_array[] = ':RegionDrugRequestFirstCopy_id';
						break;
					case 'pmUser_insID':
					case 'pmUser_updID':
						$select_array[] = $pmuser_id;
						break;
					case 'DrugRequestPlan_insDT':
					case 'DrugRequestPlan_updDT':
						$select_array[] = '@datetime';
						break;
					default:
						$select_array[] = $field;
						break;
				}
			}

			if (count($fields_array) > 0) {
				$query = "
					declare
						@datetime datetime = dbo.tzGetDate(),
						@Error_Code int,
						@Error_Message varchar(4000)
					set nocount on
					begin try
						insert into
							DrugRequestPlan(".join(',', $fields_array).")
						select
							".join(',', $select_array)."
						from
							v_DrugRequestPlan with (nolock)
						where
							DrugRequest_id = :RegionDrugRequest_id
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";
				$res = $this->getFirstRowFromQuery($query, array(
					'RegionDrugRequest_id' => $data['RegionDrugRequest_id'],
					'RegionDrugRequestFirstCopy_id' => $dr_data['RegionDrugRequestFirstCopy_id']
				));
				if (!is_array($res) || !empty($res['Error_Msg'])) {
					throw new Exception("При удалении плановых параметров первой копии произошла ошибка");
				}
			}

			$result['success'] = true;
			$this->commitTransaction();
		} catch (Exception $e) {
			$result['successs'] = false;
			$result['Error_Msg'] = $e->getMessage();
			$this->rollbackTransaction();
		}

		return $result;
    }

    /**
     * Функция возвращает заявочной кампании по идентификатору включеннной в эту кампанию заявки или по набору параметров
     */
    function getRegionDrugRequestByParams($data) {
        $query = "
			declare
                @DrugRequest_id bigint = :DrugRequest_id,
                @DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
                @PersonRegisterType_id bigint = :PersonRegisterType_id,
                @DrugGroup_id bigint = :DrugGroup_id,
                @DrugRequestKind_id bigint = :DrugRequestKind_id,
                @DrugRequest_Version int = :DrugRequest_Version;

            if (@DrugRequest_id is not null and @DrugRequest_id > 0)
			begin
                select
                    @DrugRequestPeriod_id = DrugRequestPeriod_id,
                    @PersonRegisterType_id = PersonRegisterType_id,
                    @DrugGroup_id = DrugGroup_id,
                    @DrugRequestKind_id = DrugRequestKind_id,
                    @DrugRequest_Version = DrugRequest_Version
                from
                    v_DrugRequest with (nolock) where DrugRequest_id = :DrugRequest_id;
            end;

			select top 1
                reg_dr.DrugRequest_id,
                reg_dr.DrugRequestPeriod_id,
                reg_dr.PersonRegisterType_id,
                reg_dr.DrugGroup_id,
                reg_dr.DrugRequestKind_id,
                reg_dr.DrugRequest_Version,
                reg_dr.DrugRequestProperty_id,
                reg_dr.DrugRequestPropertyFed_id,
                reg_dr.DrugRequestPropertyReg_id
			from			    
				v_DrugRequest reg_dr with (nolock)
				left join v_DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = reg_dr.DrugRequestCategory_id
			where
                reg_dr.DrugRequestPeriod_id = @DrugRequestPeriod_id and
                isnull(reg_dr.PersonRegisterType_id, 0) = isnull(@PersonRegisterType_id, 0) and
                isnull(reg_dr.DrugGroup_id, 0) = isnull(@DrugGroup_id, 0) and
                isnull(reg_dr.DrugRequestKind_id, 0) = isnull(@DrugRequestKind_id, 0) and
                isnull(reg_dr.DrugRequest_Version, 0) = isnull(@DrugRequest_Version, 0) and
                drc.DrugRequestCategory_SysNick  = 'region'
            order by
                reg_dr.DrugRequest_id;
		";
        $reg_dr_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequest_id' => isset($data['DrugRequest_id']) && $data['DrugRequest_id'] > 0 ? $data['DrugRequest_id'] : null,
            'DrugRequestPeriod_id' => isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id'] > 0 ? $data['DrugRequestPeriod_id'] : null,
            'PersonRegisterType_id' => isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] > 0 ? $data['PersonRegisterType_id'] : null,
            'DrugGroup_id' => isset($data['DrugGroup_id']) && $data['DrugGroup_id'] > 0 ? $data['DrugGroup_id'] : null,
            'DrugRequestKind_id' => isset($data['DrugRequestKind_id']) && $data['DrugRequestKind_id'] > 0 ? $data['DrugRequestKind_id'] : null,
            'DrugRequest_Version' => isset($data['DrugRequest_Version']) && $data['DrugRequest_Version'] > 0 ? $data['DrugRequest_Version'] : null
        ));

        return $reg_dr_data;
    }
}
