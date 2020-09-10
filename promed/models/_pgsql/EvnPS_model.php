<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');
/**
 * EvnPS_model - Модель КВС
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * Госпитализация
 * @property-read int $IsCont	Продолжение случая (Переведен) EvnPS_IsCont
 * @property-read string $NumCard номер карты EvnPS_NumCard
 * @property DateTime $setDT Дата и время поступления
 * @property string $setDate Дата поступления в формате Y-m-d
 * @property string $setTime Время поступления в формате H:i
 * @property-read int $PayType_id Вид оплаты
 *
 * Кем направлен
 * @property-read int $IsWithoutDirection Без направления EvnPS_IsWithoutDirection
 * @property-read int $EvnDirection_id	Направление
 * @property-read int $EvnQueue_id Очередь
 * @property-read string $EvnDirection_Num	varchar	номер направления
 * @property-read DateTime $EvnDirection_setDT дата направления
 * @property-read int $PrehospDirect_id	Кем направлен
 * @property-read int $Lpu_did	Направившая МО
 * @property-read int $Org_did	Направившая организация
 * @property-read int $LpuSection_did	Направившее отделение
 * @property-read int $OrgMilitary_did	Направивший военкомат
 * @property-read int $Diag_did	Основной диагноз направившего учреждения
 * @property-read int $DiagSetPhase_did	Стадия/Фаза заболевания для диагнозов направившего (Diag_did)
 * @property-read string $PhaseDescr_did Описание фазы для диагнозов направившего (Diag_did) EvnPS_PhaseDescr_did
 *
 * Кем доставлен
 * @property-read int $PrehospArrive_id	Кем доставлен
 * @property-read int $CmpCallCard_id	Талон вызова
 * @property-read string $CodeConv Код EvnPS_CodeConv
 * @property-read string $NumConv Номер наряда EvnPS_NumConv
 * @property-read int $IsPLAmbulance Талон передан на ССМП (Да/Нет) EvnPS_IsPLAmbulance
 *
 * Дефекты догоспитального этапа
 * @property-read int $IsImperHosp Несвоевременность госпитализации EvnPS_IsImperHosp
 * @property-read int $IsShortVolume Недостаточный обьем оперативной помощи EvnPS_IsShortVolume
 * @property-read int $IsWrongCure Неправильная тактика лечения EvnPS_IsWrongCure
 * @property-read int $IsDiagMismatch Несовпадение диагноза EvnPS_IsDiagMismatch
 *
 * ВМП
 * @property string $HTMBegDate Дата выдачи талона на ВМП в формате Y-m-d
 * @property string $HTMHospDate Дата планируемой госпитализации (ВМП) в формате Y-m-d
 * @property string $HTMTicketNum Номер талона на ВМП
 *
 * Приемное
 * @property-read int $PrehospType_id	Тип госпитализации
 * @property-read int $HospCount Количество госпитализаций EvnPS_HospCount
 * @property-read float $TimeDesease Время с начала заболевания EvnPS_TimeDesease
 * @property-read int $Okei_id	Единица измерения времени с начала заболевания
 * @property-read int $IsNeglectedCase Случай запущен EvnPS_IsNeglectedCase
 * @property-read int $PrehospTrauma_id	Травма
 * @property-read int $IsUnlaw Травма противоправная EvnPS_IsUnlaw
 * @property-read int $IsUnport Нетранспортабельность EvnPS_IsUnport
 * @property-read int $EntranceModeType_id Способ передвижения
 * @property-read int $LpuSection_pid	Приемное отделение
 * @property-read int $MedPersonal_pid	Врач приемного отделения
 * @property-read int $MedStaffFact_pid	Рабочее место врача приемного отделения
 * @property-read int $LpuSectionWard_id Палата приемного отделения
 * @property-read int $PrehospToxic_id	Вид отравления (Состояние опьянения)
 * @property-read int $LpuSectionTransType_id Вид транспортировки
 * @property-read int $Diag_pid	Основной диагноз приемного отделения
 * @property-read int $DiagSetPhase_pid	Стадия/Фаза заболевания для диагнозов приемного (Diag_pid)
 * @property-read string $PhaseDescr_pid Описание фазы для диагнозов приемного (Diag_pid) EvnPS_PhaseDescr_pid
 * @property-read int $IsActive Дееспособен EvnPS_IsActive
 * @property-read int $DeseaseType_id Характер заболевания
 * @property-read int $TumorStage_id Стадия выявленного ЗНО
 * @property-read int $IsZNO Подозрение на ЗНО
 * @property-read DateTime $FamilyContact_msgDT Дата/время сообщения родственнику FamilyContact_msgDT
 * @property-read string $FamilyContact_msgDate Дата сообщения родственнику в формате Y-m-d FamilyContact_msgDate
 * @property-read string $FamilyContact_msgTime Время сообщения родственнику в формате H:i FamilyContact_msgTime
 * @property-read string $FamilyContact_FIO ФИО родственника FamilyContact_FIO
 * @property-read string $FamilyContact_Phone Телефон родственника FamilyContact_Phone
 * @property-read Datetime $CmpTltDT Время выполнения тлт в СМП
 *
 * Исход пребывания в приемном
 * @property-read DateTime $outcomeDT Дата и время исхода из приемного отделения EvnPS_OutcomeDT
 * @property-read string $outcomeDate Дата исхода из приемного отделения в формате Y-m-d
 * @property-read string $outcomeTime Время исхода из приемного отделения в формате H:i
 * @property-read int $LeaveType_prmid Исход пребывания в приемном отделении
 * @property-read int $LpuSection_eid Отделение, в которое госпитализирован
 * @property-read int $PrehospWaifRefuseCause_id Причина отказа от госпитализации
 * @property-read int $LpuSectionBedProfileLink_id Пофиль коек приемного отделения
 * @property-read int $ResultClass_id Исход
 * @property-read int $ResultDeseaseType_id Результат обращения
 * @property-read int $IsTransfCall	 Передан активный вызов (да/нет) EvnPS_IsTransfCall
 * @property-read int $PrehospStatus_id	Статус записи АРМа приемного отделения
 *
 * Беспризорный
 * @property-read int $IsWaif Беспризорный (Да/Нет) EvnPS_IsWaif
 * @property-read int $PrehospWaifArrive_id	Кем доставлен (Беспризорный)
 * @property-read int $PrehospWaifReason_id	Причина помещения в ЛПУ (Беспризорный)
 * @property-read DateTime $PrehospWaifRefuseDT	Дата отказа от госпитализации EvnPS_PrehospWaifRefuseDT
 *
 * Отказ в подтверждении госпитализации
 * @property-read int $IsPrehospAcceptRefuse Отказ в подтверждении госпитализации EvnPS_IsPrehospAcceptRefuse
 * @property-read DateTime $PrehospAcceptRefuseDT Дата отказа в потверждении госпитализации EvnPS_PrehospAcceptRefuseDT
 *
 * @property DateTime $disDT Дата и время закрытия КВС
 * @property string $disDate Дата закрытия КВС в формате Y-m-d
 * @property string $disTime Время закрытия КВС в формате H:i
 *
 * Поля для кэширования и только для чтения
 * @property-read int $IsInReg Признак вхождения в реестр EvnPS_IsInReg
 * @property-read int $Person_Age Возраст пациента
 *
 * @property-read int $TimetableStac_id
 * @property-read int $EmergencyData_id
 *
 * Данные последнего движения для кэширования
 * @property-read int $Diag_id	Основной диагноз
 * @property-read int $Mes_id	МЭС по основному диагнозу
 * @property-read int $LeaveType_id	Тип выписки
 * @property-read int $Diag_aid	Основной паталогоанатомический диагноз
 * @property-read int $LpuSection_id Отделение последнего движения в рамках КВС
 *
 * Непонятные или устаревшие поля
 * @ property-read string $Mes_OldCode
 * @ property-read string $DrugActions вид отравления EvnPS_DrugActions
 * @ property-read int $HospType_id Тип госпитализации
 * @ property-read int $DeputyKind_id Тип попечителя
 * @ property-read string $DeputyFIO	ФИО Представителя EvnPS_DeputyFIO
 * @ property-read string $DeputyContact	Контакты представителя EvnPS_DeputyContact
 * @ property-read int $TimeDeseaseType_id Тип предварительной госпитализации
 *
 * @property-read string $leaveTypeSysNick
 * @property-read int $leaveTypeCode
 * @property-read string $payTypeSysNick
 * @property-read string $prehospTypeSysNick
 * @property-read string $prehospDirectSysNick
 * @property-read array $listEvnSectionData
 * @property-read array $listEvnUslugaData
 * @property-read int $evnSectionPriemId
 * @property-read int $evnSectionFirstId
 * @property-read int $evnSectionNoChild
 * @property-read int $evnSectionLastId
 * @property-read array $listRegionNickWithEvnSectionPriem
 * @property-read int $personNewBornId
 *
 * @property EvnSection_model $EvnSection_model
 * @property EvnSection_model $evnSectionPriem
 * @property EvnSection_model $evnSectionFirst
 * @property EvnSection_model $evnSectionLast
 * @property Org_model $Org_model
 * @property EvnDirection_model $EvnDirection_model
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 * @property Messages_model $Messages_model
 * @property Stick_model $Stick_model
 * @property CureStandart_model $CureStandart_model
 * @property HomeVisit_model $HomeVisit_model
 * @property Numerator_model $Numerator_model
 */
class EvnPS_model extends EvnAbstract_model
{
	private $_listEvnDiagPS = array();
	private $_listEvnUslugaData = null;
	protected $_listEvnSectionData = null;
	private $_leaveTypeCode = null;
	private $_leaveTypeSysNick = null;
	private $_payTypeSysNick = null;
	private $_prehospTypeSysNick = null;
	private $_prehospDirectSysNick = null;
	private $_isLoadedEvnSectionPriem = false;
	private $_isLoadedEvnSectionFirst = false;
	private $_isLoadedEvnSectionLast = false;
	private $_evnSectionFirst = null;
	private $_evnSectionLast = null;
	private $_evnSectionGotChild = null;
	private $_personNewBorn_id = null;
	protected $_useJsonParams = true;

	/**
	 * Сброс данных объекта
	 */
	function reset()
	{
		parent::reset();
		$this->_listEvnDiagPS = array();
		$this->_listEvnUslugaData = null;
		$this->_listEvnSectionData = null;
		$this->_leaveTypeCode = null;
		$this->_leaveTypeSysNick = null;
		$this->_payTypeSysNick = null;
		$this->_prehospTypeSysNick = null;
		$this->_prehospDirectSysNick = null;
		$this->_evnSectionFirst = null;
		$this->_evnSectionLast = null;
		$this->_evnSectionGotChild = null;
	}

	/**
	 * Список регионов, в которых данные приемного отделения хранятся также в EvnSection
	 */
	function getListRegionNickWithEvnSectionPriem()
	{
		return [ 'buryatiya', 'ekb', 'kareliya', 'krym', 'penza', 'perm', 'pskov', 'msk', 'vologda', 'adygeya' ];
	}

	/**
	 * Получение данных по всем движениям в рамках КВС
	 */
	function getListEvnSectionData()
	{
		if (empty($this->id)) {
			$this->_listEvnSectionData = array();
		} else if (!is_array($this->_listEvnSectionData)) {
			$add_select = '';
			$add_join = '';
			if ( $this->regionNick == 'perm' ) {
				$add_join .= '
				left join v_DiagSSZ ssz  on ssz.Diag_id = es.Diag_id';
				$add_select .= '
				,ssz.Diag_id as "ssz_Diag_id"';
			}
			$result = $this->db->query("
				SELECT
					es.EvnSection_id as \"EvnSection_id\",
					es.EvnSection_setDT as \"EvnSection_setDT\",
					es.EvnSection_disDT as \"EvnSection_disDT\",
					es.LpuSection_id as \"LpuSection_id\",
					es.Diag_id as \"Diag_id\",
					es.EvnSection_IsPriem as \"EvnSection_IsPriem\",
					es.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
					pt.PayType_SysNick as \"PayType_SysNick\"
					{$add_select}
				FROM v_EvnSection es 
				left join v_PayType pt  on pt.PayType_id = es.PayType_id
				{$add_join}
				WHERE es.EvnSection_pid = :id
				order by es.EvnSection_setDT
			", array(
				'id' => $this->id
			));
			if (false === is_object($result)) {
				throw new Exception('Не удалось получить данные по всем движениям в рамках КВС');
			}
			$tmp = $result->result('array');
			$this->_listEvnSectionData = array();
			foreach ($tmp as $row) {
				$id = $row['EvnSection_id'];
				$this->_listEvnSectionData[$id] = array(
					'ispriem' => (2 == $row['EvnSection_IsPriem']),
					'setdt' => $row['EvnSection_setDT'],
					'disdt' => $row['EvnSection_disDT'],
					'LpuSection_id' => $row['LpuSection_id'],
					'HTMedicalCareClass_id' => $row['HTMedicalCareClass_id'],
					'Diag_id' => $row['Diag_id'],
					'PayType_SysNick' => $row['PayType_SysNick'],
				);
				if ( $this->regionNick == 'perm' ) {
					$this->_listEvnSectionData[$id]['ssz_Diag_id'] = $row['ssz_Diag_id'];
				}
			}
		}
		return $this->_listEvnSectionData;
	}

	/**
	 * Диагноз из списка ССЗ
	 */
	function loadSszDiagId($Diag_id)
	{
		$res = $this->getFirstResultFromQuery('
			select Diag_id as "Diag_id"
			from v_DiagSSZ 
			where Diag_id = :id
			limit 1
		', array('id' => $Diag_id));
		if (false == $res) {
			$res = null;
		}
		return $res;
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkIsOMS($data) {
		$response = array('Error_Msg' => '');

		$query = "
			select
				df.DiagFinance_IsOms as \"DiagFinance_IsOms\"
			from
				v_DiagFinance df 
			where
				df.Diag_id = :Diag_id
			limit 1
		";
		$queryParams = array(
			'Diag_id' => $data['Diag_id']
		);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array('Error_Msg' => "Ошибка при выполнении запроса к базе данных");
		}

		$Oms = $result->result('array');
		if ($Oms[0]['DiagFinance_IsOms'] == 1  ) {
			return false;
		}
		return true;
	}
	/**
	 * Обновление данных в памяти по всем движениям в рамках КВС
	 */
	protected function _setEvnSectionData(EvnSection_model $object, $action)
	{
		$id = $object->id;
		if ('del' == $action) {
			if (isset($this->_listEvnSectionData[$id])) {
				unset($this->_listEvnSectionData[$id]);
			}
		} else {
			if (empty($object->setdt)) {
				throw new Exception('Попытка установить движение без даты поступления', 500);
			}
			$this->_listEvnSectionData[$id] = array();
			$this->_listEvnSectionData[$id]['ispriem'] = (2 == $object->IsPriem);
			$this->_listEvnSectionData[$id]['setdt'] = $object->setdt;
			$this->_listEvnSectionData[$id]['disdt'] = $object->disdt;
			$this->_listEvnSectionData[$id]['LpuSection_id'] = $object->LpuSection_id;
			$this->_listEvnSectionData[$id]['Diag_id'] = $object->Diag_id;
			$this->_listEvnSectionData[$id]['PayType_SysNick'] = $object->payTypeSysNick;
			if ( $this->regionNick == 'perm' ) {
				$this->_listEvnSectionData[$id]['ssz_Diag_id'] = null;
				if ($object->Diag_id) {
					$this->_listEvnSectionData[$id]['ssz_Diag_id'] = $this->loadSszDiagId($object->Diag_id);
				}
			}
		}
		$tmp = array();
		foreach ($this->_listEvnSectionData as $id => $data) {
			if (empty($data['setdt'])) {
				throw new Exception('В рамках КВС как-то оказалось движение без даты поступления', 500);
			}
			$dt = ConvertDateFormat($data['setdt'], 'Y-m-d H:i:s');
			$data['id'] = $id;
			$tmp[$dt] = $data;
		}
		ksort($tmp);
		$this->_listEvnSectionData = array();
		foreach ($tmp as $data) {
			$id = $data['id'];
			unset($data['id']);
			$this->_listEvnSectionData[$id] = $data;
		}
		if (empty($this->evnSectionFirstId)) {
			$this->_evnSectionFirst = null;
		} else if ($this->evnSectionFirstId == $object->id) {
			$this->_evnSectionFirst = $object;
		}
		if (empty($this->evnSectionLastId)) {
			$this->_evnSectionLast = null;
		} else if ($this->evnSectionLastId == $object->id) {
			$this->_evnSectionLast = $object;
		}
		if (isset($this->_evnSectionFirst) && $this->evnSectionFirstId != $this->_evnSectionFirst->id) {
			$this->_evnSectionFirst = null;
		}
		if (isset($this->_evnSectionLast) && $this->evnSectionLastId != $this->_evnSectionLast->id) {
			$this->_evnSectionLast = null;
		}
	}

	/**
	 * Получение идентификатора движения в приемном отделении
	 */
	function getEvnSectionPriemId()
	{
		$sid = null;
		foreach ($this->listEvnSectionData as $id => $data) {
			if ($data['ispriem']) {
				$sid = $id;
			}
		}
		if (empty($sid) && !in_array($this->regionNick, $this->listRegionNickWithEvnSectionPriem)) {
			// для всех регионов, кроме некоторых, данные движения в приемном хранятся только в КВС
			$sid = $this->id;
		}
		return $sid;
	}

	/**
	 * Получение признака, что у движения нет дочерних объектов
	 */
	function getEvnSectionNoChild()
	{
		$id = $this->getEvnSectionFirstId();
		if (!empty($id)) {
			$result = $this->getFirstRowFromQuery('
				select
					Evn_id as "Evn_id"
				from
					v_Evn E 
					inner join v_EvnSection ES  on E.Evn_pid = ES.EvnSection_id
				where
					ES.EvnSection_id = :EvnSection_id
				limit 1
			', array(
				'EvnSection_id' => $id
			));

			if (!empty($result['Evn_id'])) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	/**
	 * Получение идентификатора первого движения в профильном отделении
	 */
	function getEvnSectionFirstId()
	{
		$sid = null;
		foreach ($this->listEvnSectionData as $id => $data) {
			if ($data['ispriem']) {
				continue;
			}
			$sid = $id;
			break;
		}
		return $sid;
	}

	/**
	 * Получение идентификатора последнего движения в профильном отделении
	 */
	function getEvnSectionLastId()
	{
		$sid = null;
		foreach ($this->listEvnSectionData as $id => $data) {
			if ($data['ispriem']) {
				continue;
			}
			$sid = $id;
		}
		return $sid;
	}

	/**
	 * Получение объекта движения в приемном отделении
	function getEvnSectionPriem()
	{
	$id = $this->getEvnSectionPriemId();
	if ($id == $this->id) {
	$this->_isLoadedEvnSectionPriem = true;
	return $this;
	}
	if (false == $this->_isLoadedEvnSectionPriem) {
	$this->load->model('EvnSection_model', 'evnSectionPriem');
	$this->evnSectionPriem->applyData(array(
	'EvnSection_id' => $id,
	'session' => $this->sessionParams,
	));
	$this->_isLoadedEvnSectionPriem = true;
	}
	return $this->evnSectionPriem;
	}
	 */

	/**
	 * Получение объекта первого движения в профильном отделении
	 *
	 * До вызова метода должно быть определено отделение, в которое госпитализирован
	 *
	 * Если движение не создано и не должно быть создано или должно быть удалено, то возвращается null,
	 * если движение должно быть создано, то возвращается объект без данных
	 * в остальных случаях возвращается объект с данными из БД
	 */
	function getEvnSectionFirst()
	{
		$id = $this->getEvnSectionFirstId();
		if (empty($id) && empty($this->LpuSection_eid)) {
			// движение не создано и не должно быть создано
			$this->_evnSectionFirst = null;
			return $this->_evnSectionFirst;
		}
		if (isset($id) && empty($this->LpuSection_eid) && isset($this->LpuSection_pid)) {
			// движение должно быть удалено
			$this->_evnSectionFirst = null;
			return $this->_evnSectionFirst;
		}
		if (empty($id) && isset($this->LpuSection_eid) && isset($this->LpuSection_pid)) {
			//надо будет создать движение на данное отделение с текущими датой/временем поступления.
			$this->load->model('EvnSection_model', 'evnSectionFirst1');
			$this->_evnSectionFirst = $this->evnSectionFirst1;
			$this->_evnSectionFirst->applyData(array(
				'EvnSection_pid' => $this->id,
				'EvnSection_setDate' => $this->outcomeDate,
				'EvnSection_setTime' => $this->outcomeTime,
				'Lpu_id' => $this->Lpu_id,
				'Server_id' => $this->Server_id,
				'Person_id' => $this->Person_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'LpuSection_id' => $this->LpuSection_eid,
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
			));
			$this->_evnSectionFirst->setParent($this);
			$this->_evnSectionLast = $this->_evnSectionFirst;
			return $this->_evnSectionFirst;
		}
		if (isset($id) && empty($this->_evnSectionFirst)) {
			//если отделение перевыбрано, то надо будет менять отделение
			$this->load->model('EvnSection_model', 'evnSectionFirst2');
			$this->_evnSectionFirst = $this->evnSectionFirst2;
			$this->_evnSectionFirst->applyData(array(
				'EvnSection_id' => $id,
				'session' => $this->sessionParams,
			));
			$this->_evnSectionFirst->setParent($this);
			if ($id == $this->getEvnSectionLastId()) {
				$this->_evnSectionLast = $this->_evnSectionFirst;
			}
		}
		return $this->_evnSectionFirst;
	}

	/**
	 * Получение объекта последнего движения в профильном отделении
	 */
	function getEvnSectionLast()
	{
		$id = $this->getEvnSectionLastId();
		if (empty($id) && empty($this->LpuSection_eid)) {
			$this->_evnSectionLast = null;
			return $this->_evnSectionLast;
		}
		if (empty($id) && isset($this->LpuSection_eid)) {
			return $this->getEvnSectionFirst();
		}
		if ($id == $this->getEvnSectionFirstId()) {
			return $this->getEvnSectionFirst();
		}
		if (empty($this->_evnSectionLast)) {
			$this->load->model('EvnSection_model', 'evnSectionLast2');
			$this->_evnSectionLast = $this->evnSectionLast2;
			$this->_evnSectionLast->applyData(array(
				'EvnSection_id' => $id,
				'session' => $this->sessionParams,
			));
			$this->_evnSectionLast->setParent($this);
		}
		return $this->_evnSectionLast;
	}

	/**
	 * Получение даты последней флюорографии
	 */
	function getLastFluorographyDate($data) {
		$query = "
			select
				to_char(F.fDate, 'DD.MM.YYYY') as \"FluorographyDate\"
			from (
				select 
					RO.RepositoryObserv_FluorographyDate as \"fdate\"
				from 
					v_RepositoryObserv RO
				where
					RO.Person_id = :Person_id
					and RO.RepositoryObserv_FluorographyDate is not null

				union all

				select
					EU.EvnUsluga_setDate as \"fdate\"
				from
					v_EvnUsluga EU
					inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
					left join v_EvnPS EPS on EPS.EvnPS_id = EU.EvnUsluga_rid
					left join v_EvnPL EPL on EPL.EvnPL_id = EU.EvnUsluga_rid
				where
					UC.UslugaComplex_Code in ('A06.09.006', 'A06.09.006.001')
					and (
						EPS.Person_id = :Person_id
						or EPL.Person_id = :Person_id
					)
			) as F
			order by
				F.fDate desc
			limit 1
		";

		$resp = $this->getFirstRowFromQuery($query, $data);

		return array($resp);
	}

	/**
	 * Получение системного наименования Кем направлен
	 */
	function getPrehospDirectSysNick()
	{
		if (empty($this->PrehospDirect_id)) {
			$this->_prehospDirectSysNick = null;
		} else if (empty($this->_prehospDirectSysNick)) {
			$result = $this->getFirstRowFromQuery('
				select PrehospDirect_SysNick as "PrehospDirect_SysNick"
				FROM v_PrehospDirect 
				WHERE PrehospDirect_id = :id
				limit 1
			', array(
				'id' => $this->PrehospDirect_id
			));
			if (false === is_array($result)) {
				throw new Exception('Не удалось получить данные Кем направлен');
			}
			$this->_prehospDirectSysNick = $result['PrehospDirect_SysNick'];
		}
		return $this->_prehospDirectSysNick;
	}

	/**
	 * Получение системного наименования типа госпитализации
	 */
	function getPrehospTypeSysNick()
	{
		if (empty($this->PrehospType_id)) {
			$this->_prehospTypeSysNick = null;
		} else if (empty($this->_prehospTypeSysNick)) {
			$result = $this->getFirstRowFromQuery('
				SELECT PrehospType_SysNick as "PrehospType_SysNick"
				FROM v_PrehospType 
				WHERE PrehospType_id = :id
				limit 1
			', array(
				'id' => $this->PrehospType_id
			));
			if (false === is_array($result)) {
				throw new Exception('Не удалось получить данные типа госпитализации');
			}
			$this->_prehospTypeSysNick = $result['PrehospType_SysNick'];
		}
		return $this->_prehospTypeSysNick;
	}

	/**
	 * Получение вида оплаты КВС
	 */
	function getEvnPSPayTypeSysNick($data) {
		$resp = $this->queryResult("
			select
				PT.PayType_SysNick as \"PayType_SysNick\",
				'' as \"Error_Msg\"
			from
				v_EvnPS eps 
				inner join v_PayType pt  on pt.PayType_id = eps.PayType_id
			where
				eps.EvnPS_id = :EvnPS_id
		", array(
			'EvnPS_id' => $data['EvnPS_id']
		));

		if (!empty($resp[0])) {
			return $resp[0];
		}

		return false;
	}

	/**
	 * Определение кода типа оплаты
	 * @param array $data
	 * @return string
	 * @throws Exception
	 */
	function getPayTypeSysNick($data = array())
	{
		$id = null;
		$allowApply = true;
		if (is_array($data) && isset($data['PayType_id'])) {
			$id = $data['PayType_id'];
			$allowApply = false;
		} else if (isset($this->PayType_id)) {
			$id = $this->PayType_id;
		}
		$result = null;
		if (($allowApply || empty($this->_payTypeSysNick)) && !empty($id)) {
			$result = $this->getFirstResultFromQuery('
				select PayType_SysNick as "PayType_SysNick"
				from v_PayType 
				where PayType_id = :PayType_id
			', array('PayType_id' => $id));
			if (empty($result)) {
				throw new Exception('Ошибка при получении кода типа оплаты', 500);
			}
		}
		if ($allowApply) {
			$this->_payTypeSysNick = $result;
			return $this->_payTypeSysNick;
		} else {
			return $result;
		}
	}

	/**
	 * Получение системного наименования исхода госпитализации
	 */
	function getLeaveTypeSysNick()
	{
		if (empty($this->LeaveType_id)) {
			$this->_leaveTypeCode = null;
			$this->_leaveTypeSysNick = null;
		} else if (empty($this->_leaveTypeSysNick) && empty($this->_leaveTypeCode)) {
			$result = $this->getFirstRowFromQuery('
				SELECT
					LeaveType_Code as "LeaveType_Code",
					LeaveType_SysNick as "LeaveType_SysNick"
				FROM v_LeaveType 
				WHERE LeaveType_id = :LeaveType_id
				limit 1
			', array(
				'LeaveType_id' => $this->LeaveType_id
			));
			if (false === is_array($result)) {
				throw new Exception('Не удалось получить данные типа исхода госпитализации');
			}
			$this->_leaveTypeCode = $result['LeaveType_Code'] + 0;
			$this->_leaveTypeSysNick = $result['LeaveType_SysNick'];
		}
		return $this->_leaveTypeSysNick;
	}

	/**
	 * @return int
	 */
	function getLeaveTypeCode()
	{
		if (empty($this->leaveTypeSysNick)) {
			$this->_leaveTypeCode = null;
		}
		return $this->_leaveTypeCode;
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPS_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор карты выбывшего из стационара';
		$arr['pid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		// Госпитализация
		$arr['setdate']['label'] = 'Дата поступления';
		$arr['setdate']['alias'] = 'EvnPS_setDate';
		$arr['settime']['label'] = 'Время поступления';
		$arr['settime']['alias'] = 'EvnPS_setTime';
		$arr['diddt']['alias'] = 'EvnPS_didDT';
		$arr['iscont'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsCont',
			'label' => 'Переведен',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['numcard'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_NumCard',
			'label' => 'Номер карты',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['paytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayType_id',
			'label' => 'Вид оплаты',
			'save' => getRegionNick() != 'kz' ? 'trim|required' : 'trim',
			'type' => 'id'
		);
		// Кем направлен
		$arr['iswithoutdirection'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsWithoutDirection',
			'label' => 'Без электронного направления',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_id',
			'label' => 'Идентификатор электронного направления',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evndirectionext_id'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnDirectionExt_id',
			'label' => 'Идентификатор внешнего направления',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnqueue_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnQueue_id',
			'label' => 'Идентификатор записи о постановке в очередь',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evndirection_setdt'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnDirection_setDate',
			'label' => 'Дата направления',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['evndirection_num'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_Num',
			'label' => 'Номер направления',
			'save' => 'trim|max_length[16]',
			'type' => 'string'
		);
		$arr['prehospdirect_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospDirect_id',
			'label' => 'Кем направлен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_did',
			'label' => 'Отделение ("Госпитализация")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['org_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_did',
			'label' => 'Организация ("Госпитализация")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medstafffact_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_did',
			'label' => 'Направивший врач',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medpersonal_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_did',
			'label' => 'Направивший врач',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medstafffact_tfomscode'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_TFOMSCode',
			'label' => 'Код направившего врача',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['orgmilitary_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'OrgMilitary_did',
			'label' => 'Военкомат ("Госпитализация")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_did',
			'label' => 'ЛПУ ("Госпитализация")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_did',
			'label' => 'Диагноз направившего учреждения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_eid',
			'label' => 'Внешняя причина',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_did',
			'label' => 'Состояние пациента при направлении',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['phasedescr_did'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_PhaseDescr_did',
			'label' => 'Расшифровка',
			'save' => 'trim',
			'type' => 'string'
		);
		// Кем доставлен
		$arr['prehosparrive_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospArrive_id',
			'label' => 'Кем доставлен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['cmpcallcard_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CmpCallCard_id',
			'label' => 'Талон вызова',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['codeconv'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_CodeConv',
			'label' => 'Код',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['numconv'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_NumConv',
			'label' => 'Номер наряда',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['isplambulance'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsPLAmbulance',
			'label' => 'Талон передан на ССМП',
			'save' => 'trim',
			'type' => 'id'
		);
		// Дефекты догоспитального этапа
		$arr['isimperhosp'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsImperHosp',
			'label' => 'Несвоевременность госпитализации',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isshortvolume'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsShortVolume',
			'label' => 'Недостаточный объем клинико-диагностического обследования',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['iswrongcure'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsWrongCure',
			'label' => 'Неправильная тактика лечения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isdiagmismatch'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsDiagMismatch',
			'label' => 'Несовпадение диагноза',
			'save' => 'trim',
			'type' => 'id'
		);
		// Поля ВМП
		$arr['htmbegdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_HTMBegDate',
			'label' => 'Дата выдачи талона на ВМП',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['htmhospdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_HTMHospDate',
			'label' => 'Дата планируемой госпитализации (ВМП)',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['htmticketnum'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_HTMTicketNum',
			'label' => 'Номер талона на ВМП',
			'save' => 'trim',
			'type' => 'string'
		);
		// Приемное
		$arr['prehosptype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospType_id',
			'label' => 'Тип госпитализации',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['hospcount'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_HospCount',
			'label' => 'Количество госпитализаций',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['timedesease'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_TimeDesease',
			'label' => 'Время с начала заболевания',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['okei_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Okei_id',
			'label' => 'Единица измерения времени с начала заболевания',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isneglectedcase'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsNeglectedCase',
			'label' => 'Случай запущен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['RepositoryObserv_BreathRate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_BreathRate',
			'label' => 'Частота дыхания',
			'save' => '',
			'type' => 'int'
		);
		$arr['RepositoryObserv_Systolic'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_Systolic',
			'label' => 'Систолическое АД',
			'save' => '',
			'type' => 'int'
		);
		$arr['RepositoryObserv_Diastolic'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_Diastolic',
			'label' => 'Диастолическое АД',
			'save' => '',
			'type' => 'int'
		);
		$arr['RepositoryObserv_Height'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_Height',
			'label' => 'Рост, см',
			'save' => '',
			'type' => 'float'
		);
		$arr['RepositoryObserv_Weight'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_Weight',
			'label' => 'Вес, кг',
			'save' => '',
			'type' => 'float'
		);
		$arr['RepositoryObserv_TemperatureFrom'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_TemperatureFrom',
			'label' => 'Температура тела',
			'save' => '',
			'type' => 'float'
		);
		$arr['RepositoryObserv_FluorographyDate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_FluorographyDate',
			'label' => 'Флюорография',
			'save' => '',
			'type' => 'date'
		);
		$arr['RepositoryObserv_SpO2'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'RepositoryObserv_SpO2',
			'label' => 'Сатурация кислорода (%)',
			'save' => '',
			'type' => 'int'
		);
		$arr['covidtype_id'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'CovidType_id',
			'label' => 'Коронавирус',
			'save' => '',
			'type' => 'id'
		);
		$arr['diagconfirmtype_id'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'DiagConfirmType_id',
			'label' => 'Диагноз подтвержден рентгенологически',
			'save' => '',
			'type' => 'id'
		);
		$arr['prehosptrauma_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospTrauma_id',
			'label' => 'Травма',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isunlaw'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsUnlaw',
			'label' => 'Противоправная',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isunport'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsUnport',
			'label' => 'Нетранспортабельность',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['entrancemodetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EntranceModeType_id',
			'label' => 'Вид транспортировки',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_pid',
			'label' => 'Приемное отделение ("Приемное")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['getbed_id'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'GetBed_id',
			'label' => 'Профиль койки',
			'save' => 'trim',
			'type' => 'id'
		);
        $arr['diag_cid'] = array(
            'properties' => array(
                self::PROPERTY_NOT_LOAD,
                self::PROPERTY_NOT_SAFE,
            ),
            'alias' => 'Diag_cid',
            'label' => 'Уточняющий диагноз',
            'save' => 'trim',
            'type' => 'id'
        );
        $arr['purposehospital_id'] = array(
            'properties' => array(
                self::PROPERTY_NOT_LOAD,
                self::PROPERTY_NOT_SAFE,
            ),
            'alias' => 'PurposeHospital_id',
            'label' => 'Цель госпитализации',
            'save' => 'trim',
            'type' => 'id'
        );
		$arr['medpersonal_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_pid',
			'label' => 'Врач приемного отделения ("Приемное")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medstafffact_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_pid',
			'label' => 'Рабочее место врача приемного отделения ("Приемное")',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectionward_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionWard_id',
			'label' => 'Палата приемного отделения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectionbedprofilelink_id'] = array(
			'properties' => array(
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD
			),
			'alias' => 'LpuSectionBedProfileLink_id',
			'label' => 'Профиль коек приемного отделения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehosptoxic_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospToxic_id',
			'label' => 'Состояние опьянения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectiontranstype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionTransType_id',
			'label' => 'Состояние транспортировки',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_pid',
			'label' => 'Основной диагноз приемного отделения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_pid',
			'label' => 'Состояние пациента при поступлении',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_aid',
			'label' => 'Состояние пациента при выписке',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['phasedescr_pid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_PhaseDescr_pid',
			'label' => 'Расшифровка',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['isactive'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsActive',
			'label' => 'Дееспособен',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['deseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeseaseType_id',
			'label' => 'Характер',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['tumorstage_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TumorStage_id',
			'label' => 'Стадия выявленного ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['iszno'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsZNO',
			'label' => 'Подозрение на ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isznoremove'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsZNORemove',
			'label' => 'Снятие признака подозрения на ЗНО',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['biopsydate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_BiopsyDate',
			'label' => 'Дата взятия биопсии',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['diag_spid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_spid',
			'label' => 'Подозрение на диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
        $arr['familycontact_msgdt'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_READ_ONLY,
                self::PROPERTY_NOT_SAFE,
                self::PROPERTY_NOT_LOAD,
                self::PROPERTY_DATE_TIME
            ),
            'alias' => 'FamilyContact_msgDT',
            'applyMethod'=>'_applyFamilyContact_msgDT',
        );
        $arr['familycontact_msgdate'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_READ_ONLY,
                self::PROPERTY_NOT_SAFE,
                self::PROPERTY_NOT_LOAD,
            ),
            // только для извлечения из POST и обработки методом _applyFamilyContact_msgDT
            'alias' => 'FamilyContact_msgDate',
            'label' => 'Дата сообщения родственнику',
            'save' => 'trim',
            'type' => 'date'
        );
        $arr['familycontact_msgtime'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_READ_ONLY,
                self::PROPERTY_NOT_SAFE,
                self::PROPERTY_NOT_LOAD,
            ),
            // только для извлечения из POST и обработки методом _applyFamilyContact_msgDT
            'alias' => 'FamilyContact_msgTime',
            'label' => 'Время сообщения родственнику',
            'save' => 'trim',
            'type' => 'time'
        );
        $arr['familycontact_fio'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_READ_ONLY,
                self::PROPERTY_NOT_SAFE,
                self::PROPERTY_NOT_LOAD,
            ),
            'alias' => 'FamilyContact_FIO',
            'label' => 'ФИО родственника',
            'save' => 'trim',
            'type' => 'string'
        );
        $arr['familycontact_phone'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_READ_ONLY,
                self::PROPERTY_NOT_SAFE,
                self::PROPERTY_NOT_LOAD,
            ),
            'alias' => 'FamilyContact_Phone',
            'label' => 'Телефон родственника',
            'save' => 'trim',
            'type' => 'string'
        );
		if ( getRegionNick() == 'vologda' ){
			$arr['familycontactperson_id'] = array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD,
				),
				'alias' => 'FamilyContactPerson_id',
				'label' => 'Идентификатор представителя',
				'save' => 'trim',
				'type' => 'id'
			);
		};
		// Исход пребывания в приемном
		$arr['outcomedt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPS_OutcomeDT',
			'applyMethod'=>'_applyOutcomeDT',
		);
		$arr['outcomedate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyOutcomeDT
			'alias' => 'EvnPS_OutcomeDate',
			'label' => 'Дата исхода из приемного отделения',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['outcometime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyOutcomeDT
			'alias' => 'EvnPS_OutcomeTime',
			'label' => 'Время исхода из приемного отделения',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['lpusection_eid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_eid',
			'label' => 'Отделение, в которое госпитализирован',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospwaifrefusecause_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospWaifRefuseCause_id',
			'label' => 'Причина отказа от госпитализации',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medicalcareformtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedicalCareFormType_id',
			'label' => 'Форма помощи',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['resultclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultClass_id',
			'label' => 'Исход',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['resultdeseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDeseaseType_id',
			'label' => 'Результат обращения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['istransfcall'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsTransfCall',
			'label' => 'Передан активный вызов',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospstatus_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'PrehospStatus_id',
			'label' => 'Статус записи АРМа приемного отделения',
			'save' => 'trim',
			'type' => 'id'
		);
		// Беспризорный
		$arr['iswaif'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsWaif',
			'label' => 'Беспризорный',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospwaifarrive_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospWaifArrive_id',
			'label' => 'Кем доставлен беспризорный',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospwaifreason_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospWaifReason_id',
			'label' => 'Причина помещения в ЛПУ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospwaifrefusedt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_DATE_TIME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' =>  'EvnPS_PrehospWaifRefuseDT',
			'label' => 'Дата отказа приёма',
			'save' => 'trim',
			'type' => 'date'
		);

		// Отказ в подтверждении госпитализации
		$arr['isprehospacceptrefuse'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IsPrehospAcceptRefuse',
			// нужно брать из входящих параметров только при сценариях setEvnPSPrehospAcceptRefuse
			/*'label' => 'Отказ в подтверждении госпитализации',
			'save' => 'trim',
			'type' => 'id'*/
		);
		$arr['prehospacceptrefusedt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_DATE_TIME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPS_PrehospAcceptRefuseDT',
			/*'label' => 'Дата отказа в подтверждении госпитализации',
			'save' => 'trim',
			'type' => 'date'*/
		);

		$arr['disdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPS_disDT',
			'applyMethod'=>'_applyDisDT',
		);
		$arr['disdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnPS_disDate',
			'label' => 'Дата закрытия КВС',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['distime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnPS_disTime',
			'label' => 'Время закрытия КВС',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPS_IsInReg',
		);
		$arr['person_age'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Person_Age',
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Diag_id',
		);
		$arr['mes_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Mes_id',
		);
		$arr['diag_aid'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'Diag_aid',
		);
		$arr['leavetype_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'LeaveType_id',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
			),
			'alias' => 'LpuSection_id',
			'label' => 'Отделение',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['leavetype_prmid'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'LeaveType_prmid',
			'label' => 'Исход пребывания',
			'select' => 'v_EvnSection.LeaveType_prmid',
			'join' => 'left outer join v_EvnSection on {ViewName}.EvnPS_id = v_EvnSection.EvnSection_pid and v_EvnSection.EvnSection_Index = 0',
		);
		$arr['timetablestac_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'TimetableStac_id',
			'select' => 'v_EvnDirection_all.TimetableStac_id',
			'join' => 'left join v_EvnDirection_all on v_EvnDirection_all.EvnDirection_id = {ViewName}.EvnDirection_id',
			/*'external_query' => '
				select TimetableStac_id from v_EvnDirection_all  where EvnDirection_id = :EvnDirection_id limit 1
			',
			'external_query_params' => array(
				'EvnDirection_id',
			),*/
		);
		$arr['emergencydata_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EmergencyData_id',
			'select' => 'COALESCE(ED1.EmergencyData_id, ED2.EmergencyData_id) as "EmergencyData_id"',
			'join' => '
				LEFT JOIN LATERAL (
					select EmergencyData_id
					from v_TimetableStac_lite 
					where TimetableStac_id = v_EvnDirection_all.TimetableStac_id
					limit 1
				) ED1 ON true
				LEFT JOIN LATERAL (
					select EmergencyData_id
					from v_TimetableStac_lite 
					where Evn_id = {ViewName}.{PrimaryKey}
						and EmergencyData_id is not null
					limit 1
				) ED2 ON true
			',
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IndexRep',
			'label' => 'Признак повторной подачи',
			'save' => 'trim',
			'type' => 'int',
		);
		$arr['indexrepinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_IndexRepInReg',
		);
		$arr['notificationdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPS_NotificationDT',
			'applyMethod'=>'_applyNotificationDT',
		);
		$arr['notificationdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'EvnPS_NotificationDate',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['notificationtime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			'alias' => 'EvnPS_NotificationTime',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
		);
		$arr['policeman'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_Policeman',
		);
		$arr['rfid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'type' => 'id',
			'alias' => 'EvnPS_RFID'
		);
        if(getRegionNick() != 'kz') {
			$arr['cmptltdt'] = array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
				),
				'alias' => 'EvnPS_CmpTltDT',
				'applyMethod'=>'_applyCmpTltDT',
			);
			$arr['cmptltdate'] = array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD,
				),
				'alias' => 'EvnPS_CmpTltDate',
				'save' => 'trim',
				'type' => 'date'
			);
			$arr['cmptlttime'] = array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD,
				),
				'alias' => 'EvnPS_CmpTltTime',
				'save' => 'trim',
				'type' => 'time'
			);
		}
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 30;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPS';
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DELETE,
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
		));
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules['id'] = array(
					'field' => 'EvnPS_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
			case 'checkBeforeLeaveFromForm':
			case 'checkBeforeLeave':
				$rules = array();
				$rules[] = array(
					'field' => 'EvnSection_pid', 'label' => 'Идентификатор КВС',
					'rules' => 'trim|required', 'type' => 'id'
				);
				$rules[] = array(
					'field' => 'EvnSection_id', 'label' => 'Идентификатор движения в профильном отделении',
					'rules' => 'trim' . ('checkBeforeLeave' == $name ? '|required' : ''),
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'LpuSection_id', 'label' => 'Идентификатор профильного отделения',
					'rules' => 'trim' . ('checkBeforeLeaveFromForm' == $name ? '|required' : ''),
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'MedPersonal_id', 'label' => 'Лечащий врач',
					'rules' => 'trim' . ('checkBeforeLeaveFromForm' == $name ? '|required' : ''),
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'MedStaffFact_id', 'label' => 'Рабочее место лечащего врача',
					'rules' => 'trim' . ('checkBeforeLeaveFromForm' == $name ? '|required' : ''),
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'UslugaComplex_id', 'label' => 'Услуга лечения',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'HTMedicalCareClass_id', 'label' => 'Метод ВМП',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'EvnSection_setDate', 'label' => 'Дата поступления',
					'rules' => 'trim' . ('checkBeforeLeaveFromForm' == $name ? '|required' : ''),
					'type' => 'date'
				);
				$rules[] = array(
					'field' => 'EvnSection_setTime', 'label' => 'Время поступления',
					'rules' => 'trim' . ('checkBeforeLeaveFromForm' == $name ? '|required' : ''),
					'type' => 'time'
				);
				$rules[] = array(
					'field' => 'EvnSection_IsZNO',
					'label' => 'Подозрение на ЗНО',
					'rules' => '',
					'type' => 'checkbox'
				);
				$rules[] = array(
					'field' => 'childPS', 'label' => 'childPS',
					'rules' => '',
					'type' => 'checkbox'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['from'] = array(
			'field' => 'from',
			'label' => 'from',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['checkEvnPSPersonNewbornBirthSpecStacConnect'] = array(
            'field' => 'checkEvnPSPersonNewbornBirthSpecStacConnect',
            'label' => 'checkEvnPSPersonNewbornBirthSpecStacConnect',
            'rules' => '',
            'type' => 'string'
        );
		$all['LeaveType_prmid'] = array(
			'field' => 'LeaveType_prmid',
			'label' => 'LeaveType_prmid',
			'rules' => '',
			'type' => 'id'
		);
		$all['UslugaComplex_id'] = array(
			'field' => 'UslugaComplex_id',
			'label' => 'UslugaComplex_id',
			'rules' => '',
			'type' => 'id'
		);
		$all['LeaveType_fedid'] = array(
			'field' => 'LeaveType_fedid',
			'label' => 'LeaveType_fedid',
			'rules' => '',
			'type' => 'id'
		);
		$all['ResultDeseaseType_fedid'] = array(
			'field' => 'ResultDeseaseType_fedid',
			'label' => 'ResultDeseaseType_fedid',
			'rules' => '',
			'type' => 'id'
		);
		$all['LpuSectionProfile_id'] = array(
			'field' => 'LpuSectionProfile_id',
			'label' => 'LpuSectionProfile_id',
			'rules' => '',
			'type' => 'id'
		);
		$all['addEvnSection'] = array(
			'field' => 'addEvnSection',
			'label' => 'Флаг добавления движения',
			'rules' => '',
			'type'	=> 'string'
		);
		$all['MedPersonal_id'] = array(
			'field' => 'MedPersonal_id',
			'label' => 'Идентификатор врача', // для добавления из ЭМК
			'rules' => '',
			'type' => 'id'
		);
		$all['MedStaffFact_id'] = array(
			'field' => 'MedStaffFact_id',
			'label' => 'Идентификатор места работы врача', // для добавления из ЭМК
			'rules' => '',
			'type' => 'id'
		);
		$all['TimetableStac_id'] = array(
			'field' => 'TimetableStac_id', // Для АРМ приемного
			'label' => 'Бирка',
			'rules' => '',
			'type' => 'id'
		);
		$all['EvnCostPrint_setDT'] = array(
			'field' => 'EvnCostPrint_setDT',
			'label' => 'Дата выдачи справки/отказа',
			'rules' => '',
			'type' => 'date'
		);
		$all['EvnCostPrint_IsNoPrint'] = array(
			'field' => 'EvnCostPrint_IsNoPrint',
			'label' => 'Отказ',
			'rules' => '',
			'type' => 'id'
		);
		$all['childPS'] = array(
			'field' => 'childPS',
			'label' => 'childPS',
			'rules' => '',
			'type' => 'checkbox'
		);
		$all['ignoreUslugaComplexTariffCountCheck'] = array(
			'field' => 'ignoreUslugaComplexTariffCountCheck',
			'label' => 'Признак игнорирования проверки количества тарифов на услуге',
			'rules' => '',
			'type' => 'int'
		);
		$all['vizit_direction_control_check'] = array(
			'field' => 'vizit_direction_control_check',
			'label' => 'Признак игнорирования проверки пересечения ТАП',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnPSDoublesCheck'] = array(
			'field' => 'ignoreEvnPSDoublesCheck',
			'label' => 'Признак игнорирования проверки пересечения КВС',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnPSTimeDeseaseCheck'] = array(
			'field' => 'ignoreEvnPSTimeDeseaseCheck',
			'label' => 'Проверять заполнения поля «Время с начала заболевания»',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnPSHemoDouble'] = array(
			'field' => 'ignoreEvnPSHemoDouble',
			'label' => 'Флаг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnPSHemoLong'] = array(
			'field' => 'ignoreEvnPSHemoLong',
			'label' => 'Флаг',
			'rules' => '',
			'type' => 'int'
		);
		$all['EvnPS_NotificationDate'] = array(
			'field' => 'EvnPS_NotificationDate',
			'label' => 'Дата направления Извещения',
			'rules' => '',
			'type' => 'date'
		);
		$all['EvnPS_NotificationTime'] = array(
			'field' => 'EvnPS_NotificationTime',
			'label' => 'Время направления Извещения',
			'rules' => '',
			'type' => 'time'
		);
		$all['MedStaffFact_id'] = array(
			'field' => 'MedStaffFact_id',
			'label' => 'Сотрудник МО, передавший телефонограмму',
			'rules' => '',
			'type' => 'id'
		);
		$all['GetBed_id'] = array(
			'field' => 'GetBed_id',
			'label' => 'Профиль койки',
			'rules' => '',
			'type' => 'id'
		);
        $all['Diag_cid'] = array(
            'field' => 'Diag_cid',
            'label' => 'Уточняющий диагноз',
            'rules' => '',
            'type' => 'id'
        );
        $all['PurposeHospital_id'] = array(
            'field' => 'PurposeHospital_id',
            'label' => 'Цель госпитализации',
            'rules' => '',
            'type' => 'id'
        );
		$all['EvnPS_Policeman'] = array(
			'field' => 'EvnPS_Policeman',
			'label' => 'Сотрудник, принявший информацию',
			'rules' => '',
			'type' => 'string'
		);
		$all['ignoreCheckMorbusOnko'] = array(
			'field' => 'ignoreCheckMorbusOnko',
			'label' => 'Признак игнорирования проверки перед удалением специфики',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreMorbusOnkoDrugCheck'] = array(
			'field' => 'ignoreMorbusOnkoDrugCheck',
			'label' => 'Признак игнорирования проверки препаратов в онко заболевании',
			'rules' => '',
			'type' => 'int'
		);
        if(getRegionNick() != 'kz') {
			$all['EvnPS_CmpTltDate'] = array(
				'field' => 'EvnPS_CmpTltDate',
				'label' => 'Дата проведения тлт в СМП',
				'rules' => '',
				'type' => 'date'
			);
			$all['EvnPS_CmpTltTime'] = array(
				'field' => 'EvnPS_CmpTltTime',
				'label' => 'Время проведения тлт в СМП',
				'rules' => '',
				'type' => 'time'
			);
		}
		$all['TraumaCircumEvnPS_Name'] = array(
			'field' => 'TraumaCircumEvnPS_Name',
			'label' => 'Обстоятельства получения травмы',
			'rules' => '',
			'type' => 'string'
		);
		$all['TraumaCircumEvnPS_setDTDate'] = array(
			'field' => 'TraumaCircumEvnPS_setDTDate',
			'label' => 'Дата, время получения травмы',
			'rules' => '',
			'type' => 'date'
		);
		$all['TraumaCircumEvnPS_setDTTime'] = array(
			'field' => 'TraumaCircumEvnPS_setDTTime',
			'label' => 'Дата, время получения травмы',
			'rules' => '',
			'type' => 'time'
		);
        $all['Pediculos_id'] = array(
            'field' => 'Pediculos_id',
            'label' => 'идентифкатор',
            'rules' => '',
            'type' => 'int'
        );
        $all['PediculosDiag_id'] = array(
            'field' => 'PediculosDiag_id',
            'label' => 'педикулёз идентифкатор диагноза',
            'rules' => '',
            'type' => 'int'
        );
		$all['ScabiesDiag_id'] = array(
			'field' => 'ScabiesDiag_id',
			'label' => 'чесотка идентифкатор диагноза',
			'rules' => '',
			'type' => 'int'
		);
        $all['Pediculos_isPrint'] = array(
            'field' => 'Pediculos_isPrint',
            'label' => 'признак печати уведомления',
            'rules' => '',
            'default' => 1,
            'type' => 'int'
        );
        $all['Pediculos_Sanitation_setDate'] = array(
            'field' => 'Pediculos_Sanitation_setDate',
            'label' => 'время санитарной обработки',
            'rules' => '',
            'type' => 'date'
        );
        $all['Pediculos_Sanitation_setTime'] = array(
            'field' => 'Pediculos_Sanitation_setTime',
            'label' => 'время санитарной обработки',
            'rules' => '',
            'type' => 'time'
        );
        $all['Pediculos_isSanitation'] = array(
            'field' => 'Pediculos_isSanitation',
            'label' => 'Санитарная обработка',
            'rules' => '',
            'type' => 'checkbox'
        );
        $all['isPediculos'] = array(
            'field' => 'isPediculos',
            'label' => 'педикулёз',
            'rules' => '',
            'type' => 'checkbox'
        );
		$all['isScabies'] = array(
			'field' => 'isScabies',
			'label' => 'чесотка',
			'rules' => '',
			'type' => 'checkbox'
		);
		$all['RepositoryObservData'] = [
			'field' => 'RepositoryObservData',
			'label' => 'Анкета',
			'rules' => '',
			'type' => 'json_array',
			'assoc' => true
		];
		return $all;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params['EvnPS_IndexRep'] = empty($data['EvnPS_IndexRep']) ? null : $data['EvnPS_IndexRep'];
		$this->_params['EvnPS_IndexRepInReg'] = empty($data['EvnPS_IndexRepInReg']) ? null : $data['EvnPS_IndexRepInReg'];
		$this->_params['ignore_sex'] = empty($data['ignore_sex']) ? null : $data['ignore_sex'];
		$this->_params['TimetableStac_id'] = empty($data['TimetableStac_id']) ? null : $data['TimetableStac_id'];
		$this->_params['LeaveType_prmid'] = empty($data['LeaveType_prmid']) ? null : $data['LeaveType_prmid'];
		$this->_params['UslugaComplex_id'] = empty($data['UslugaComplex_id']) ? null : $data['UslugaComplex_id'];
		$this->_params['LeaveType_fedid'] = empty($data['LeaveType_fedid']) ? null : $data['LeaveType_fedid'];
		$this->_params['ResultDeseaseType_fedid'] = empty($data['ResultDeseaseType_fedid']) ? null : $data['ResultDeseaseType_fedid'];
		$this->_params['LpuSectionProfile_id'] = empty($data['LpuSectionProfile_id']) ? null : $data['LpuSectionProfile_id'];
		$this->_params['LpuSectionBedProfileLink_id'] = empty($data['LpuSectionBedProfileLink_id']) ? null : $data['LpuSectionBedProfileLink_id'];
        $this->_params['LpuSectionBedProfile_id'] = empty($data['LpuSectionBedProfile_id']) ? null : $data['LpuSectionBedProfile_id'];
        $this->_params['addEvnSection'] = !isset($data['addEvnSection']) ? null : $data['addEvnSection'];
		$this->_params['MedPersonal_id'] = empty($data['MedPersonal_id']) ? null : $data['MedPersonal_id'];
		$this->_params['MedStaffFact_id'] = empty($data['MedStaffFact_id']) ? null : $data['MedStaffFact_id'];
		$this->_params['GetBed_id'] = empty($data['GetBed_id']) ? null : $data['GetBed_id'];
        $this->_params['Diag_cid'] = empty($data['Diag_cid']) ? null : $data['Diag_cid'];
        $this->_params['PurposeHospital_id'] = empty($data['PurposeHospital_id']) ? null : $data['PurposeHospital_id'];
        $this->_params['from'] = empty($data['from']) ? null : $data['from'];
		$this->_params['ignoreUslugaComplexTariffCountCheck'] = empty($data['ignoreUslugaComplexTariffCountCheck']) ? false : true;
		$this->_params['childPS'] = empty($data['childPS']) ? false : true;
		$this->_params['start'] = empty($data['start']) ? 0 : $data['start'];
		$this->_params['limit'] = empty($data['limit']) ? 100 : $data['limit'];
		$this->_params['vizit_direction_control_check'] = empty($data['vizit_direction_control_check']) ? 0 : $data['vizit_direction_control_check'];
		$this->_params['ignoreEvnPSDoublesCheck'] = empty($data['ignoreEvnPSDoublesCheck']) ? 0 : $data['ignoreEvnPSDoublesCheck'];
		$this->_params['ignoreEvnPSTimeDeseaseCheck'] = empty($data['ignoreEvnPSTimeDeseaseCheck']) ? 0 : $data['ignoreEvnPSTimeDeseaseCheck'];
		$this->_params['ignoreEvnPSHemoDouble'] = empty($data['ignoreEvnPSHemoDouble']) ? 0 : $data['ignoreEvnPSHemoDouble'];
		$this->_params['ignoreEvnPSHemoLong'] = empty($data['ignoreEvnPSHemoLong']) ? 0 : $data['ignoreEvnPSHemoLong'];
		$this->_params['ignoreCheckMorbusOnko'] = empty($data['ignoreCheckMorbusOnko']) ? 0 : $data['ignoreCheckMorbusOnko'];
		$this->_params['ignoreMorbusOnkoDrugCheck'] = empty($data['ignoreMorbusOnkoDrugCheck']) ? 0 : $data['ignoreMorbusOnkoDrugCheck'];
		$this->_params['checkEvnPSPersonNewbornBirthSpecStacConnect'] = empty($data['checkEvnPSPersonNewbornBirthSpecStacConnect']) ? null : $data['checkEvnPSPersonNewbornBirthSpecStacConnect'];
        $this->_params['FamilyContact_msgDate'] = empty($data['FamilyContact_msgDate']) ? null : $data['FamilyContact_msgDate'];
        $this->_params['FamilyContact_msgTime'] = empty($data['FamilyContact_msgTime']) ? null : $data['FamilyContact_msgTime'];
        $this->_params['FamilyContact_FIO'] = empty($data['FamilyContact_FIO']) ? null : $data['FamilyContact_FIO'];
        $this->_params['FamilyContact_Phone'] = empty($data['FamilyContact_Phone']) ? null : $data['FamilyContact_Phone'];
		if (array_key_exists('CovidType_id', $data)) {
			$this->_params['RepositoryObserv_BreathRate'] = $data['RepositoryObserv_BreathRate'] ?? null;
			$this->_params['RepositoryObserv_Systolic'] = $data['RepositoryObserv_Systolic'] ?? null;
			$this->_params['RepositoryObserv_Diastolic'] = $data['RepositoryObserv_Diastolic'] ?? null;
			$this->_params['RepositoryObserv_Height'] = $data['RepositoryObserv_Height'] ?? null;
			$this->_params['RepositoryObserv_Weight'] = $data['RepositoryObserv_Weight'] ?? null;
			$this->_params['RepositoryObserv_TemperatureFrom'] = $data['RepositoryObserv_TemperatureFrom'] ?? null;
			$this->_params['RepositoryObserv_SpO2'] = $data['RepositoryObserv_SpO2'] ?? null;
			$this->_params['CovidType_id'] = $data['CovidType_id'] ?? null;
			$this->_params['DiagConfirmType_id'] = $data['DiagConfirmType_id'] ?? null;
		}
		$this->_params['RepositoryObserv_FluorographyDate'] = $data['RepositoryObserv_FluorographyDate'] ?? null;
		$this->_params['RepositoryObservData'] = $data['RepositoryObservData'] ?? null;
		if (getRegionNick() == 'vologda') {
			$this->_params['FamilyContactPerson_id'] = $data['FamilyContactPerson_id'] ?? null;
		}
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		$this->_processingDtValue($column, $value, 'dis');
		$this->_processingDtValue($column, $value, 'outcome');
		$this->_processingDtValue($column, $value, 'notification');
        if(getRegionNick() != 'kz')
			$this->_processingDtValue($column, $value, 'cmptlt');
		return parent::_processingSavedValue($column, $value);
	}


	/**
	 * Извлечение даты и времени события из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyOutcomeDT($data)
	{
		return $this->_applyDT($data, 'outcome');
	}

	/**
	 * Извлечение даты и времени события из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyNotificationDT($data)
	{
		return $this->_applyDT($data, 'notification');
	}

	/**
	 * Извлечение даты и времени события из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyCmpTltDT($data)
	{
		return $this->_applyDT($data, 'cmptlt');
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePrehospTraumaId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'prehosptrauma_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedPersonalPid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medpersonal_pid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedStaffFactPid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medstafffact_pid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionWardId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusectionward_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionProfileId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusectionprofile_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionBedProfileId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusectionbedprofile_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateIsTransfCall($id, $value = null)
	{
		return $this->_updateAttribute($id, 'istransfcall', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateIsPLAmbulance($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isplambulance', $value);
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'lpusectionward_id':
				$data = array();
				if (empty($this->_params['ignore_sex'])) {
					$data['ignore_sex'] = 0;
					$data['Sex_id'] = $this->person_Sex_id;
				} else {
					$data['ignore_sex'] = 1;
				}
				$data['EvnPS_id'] = $this->id;
				$data['LpuSection_id'] = $this->LpuSection_pid;
				$data['LpuSectionWard_id'] = $this->LpuSectionWard_id;
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->checkChangeLpuSectionWardId($data);
				break;
			case 'istransfcall':
				if ($this->IsTransfCall == 2 && empty($this->PrehospWaifRefuseCause_id)) {
					throw new Exception('');
				}
				break;
			case 'isplambulance':
				if ($this->IsPLAmbulance == 2 && $this->PrehospArrive_id != 2) {
					throw new Exception('');
				}
				break;
		}
	}


	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if (in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE, self::SCENARIO_DO_SAVE))) {
		
		    if ( $this->_params['checkEvnPSPersonNewbornBirthSpecStacConnect'] ) {
		        $res = $this->queryResult("select * from v_EvnPS EPS
	                inner join v_PersonNewborn PN on EPS.EvnPS_id = PN.EvnPS_id
	                inner join v_BirthSpecStac BSS on PN.BirthSpecStac_id = BSS.BirthSpecStac_id
                    where EPS.EvnPS_id = :EvnPS_id", array ( 'EvnPS_id' => $this->id ) );

		        if ( empty( $res[0] ) ){
                    throw new Exception('В случае, если госпитализация плановая, должны быть заполнены данные о направлении.');
                }
            }
            
			if (empty($this->setDate) || empty($this->setDT)) {
				throw new Exception('Некорректная дата поступления');
			}

			if (getRegionNick() == 'perm' && !empty($this->id) && !empty($this->setDT) && ConvertDateFormat($this->setDT, 'Y') >= 2016) {
				// если ДНЛ >= 01.01.2016 проверяем является ли случаем гемодиализа и тянем дату исхода
				$resp_eu = $this->queryResult("
					select
						eu.EvnUsluga_id as \"EvnUsluga_id\",
						LASTES.EvnSection_disDT as \"EvnSection_disDT\"
					from
						v_EvnUsluga eu 
						inner join v_UslugaComplex uc  on uc.UslugaComplex_id = eu.UslugaComplex_id
						inner join v_EvnSection es  on es.EvnSection_id = eu.EvnUsluga_pid
						INNER JOIN LATERAL (
							select
								EvnSection_disDT
							from
								v_EvnSection 
							where
								EvnSection_pid = es.EvnSection_pid
							order by
								EvnSection_disDT
							desc
							limit 1
						) LASTES ON true
					where
						es.EvnSection_pid = :EvnPS_id
						and uc.UslugaComplex_Code in ('A18.05.002', 'A18.30.001')
					limit 1
				", array(
					'EvnPS_id' => $this->id
				));

				if (!empty($resp_eu[0]['EvnUsluga_id']) && !empty($resp_eu[0]['EvnSection_disDT']) && ConvertDateFormat($resp_eu[0]['EvnSection_disDT'], 'Y') >= 2016) {
					// если случай гемодиализа и ДКЛ >= 01.01.2016
					if (empty($this->_params['ignoreEvnPSHemoDouble'])) {
						// проверяем есть ли ещё одна КВС
						$resp_hemo = $this->queryResult("
							select
								eps.EvnPS_id as \"EvnPS_id\"
							from
								v_EvnPS eps 
								inner join v_EvnSection es  on es.EvnSection_pid = eps.EvnPS_id
								INNER JOIN LATERAL (
									select
										es2.LeaveType_fedid
									from
										v_EvnSection es2 
									where
										es2.EvnSection_pid = eps.EvnPS_id
									order by
										es2.EvnSection_disDT
									desc
									limit 1
								) LASTES ON true
								inner join fed.v_LeaveType lt  on lt.LeaveType_id = LASTES.LeaveType_fedid
								inner join v_EvnUsluga eu  on eu.EvnUsluga_pid = es.EvnSection_id
								inner join v_UslugaComplex uc  on uc.UslugaComplex_id = eu.UslugaComplex_id
								inner join v_LpuSection ls  on ls.LpuSection_id = es.LpuSection_id
								inner join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
								inner join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
								inner join v_PayType pt  on es.PayType_id = pt.PayType_id
							where
								eps.Lpu_id = :Lpu_id
								and eps.EvnPS_id <> :EvnPS_id
								and eps.Person_id = :Person_id
								and uc.UslugaComplex_Code in ('A18.05.002', 'A18.30.001')
								and pt.PayType_SysNick = 'oms'
								and lt.LeaveType_Code IS NOT NULL
								and lt.LeaveType_Code NOT IN ('205', '206', '207', '208')
								and lut.LpuUnitType_SysNick in ('dstac','hstac','pstac')
								and date_part('month', EPS.EvnPS_disDT) = date_part('month', :EvnSection_disDT)
								and date_part('year', EPS.EvnPS_disDT) = date_part('year', :EvnSection_disDT)
							limit 1
						", array(
							'EvnPS_id' => $this->id,
							'Lpu_id' => $this->Lpu_id,
							'Person_id' => $this->Person_id,
							'EvnSection_disDT' => ConvertDateFormat($resp_eu[0]['EvnSection_disDT'], 'Y-m-d H:i:s')
						));
						if (!empty($resp_hemo[0]['EvnPS_id'])) {
							// если есть ещё одна КВС с услугой А18.05.002 или А18.30.001 в этой же МО в ОМС движении ДС в течение календарного месяца даты конца лечения и фед. результат в этой КВС <> 205-208
							$this->_setAlertMsg('Для выбранного пациента в дневном стационаре текущей МО оплачивается только один случай гемодиализа в отчетный месяц. Ограничение нарушено. Продолжить сохранение?');
							$this->_saveResponse['data'] = array();
							throw new Exception('YesNo', 115);
						}
					}

					if (empty($this->_params['ignoreEvnPSHemoLong'])) {
						$diff = $this->setDT->diff($resp_eu[0]['EvnSection_disDT']);
						if ($diff->days + 1 > 30) {
							// если ДКЛ – ДНЛ > 30 дней
							$this->_setAlertMsg('Продолжительность случая гемодиализа превышает 30 дней. Продолжить сохранение?');
							$this->_saveResponse['data'] = array();
							throw new Exception('YesNo', 116);
						}
					}
				}
			}

			if (getRegionNick() == 'perm' && !empty($this->disDate)) {
				$usluga_complex_code_list = $this->queryList("
					with mv as (
						select
							cast(:EvnPS_disDate as date) as dt
					)

					select distinct
						UC.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_EvnUsluga EU 
						inner join v_Evn EP  on EP.Evn_id = EU.EvnUsluga_pid
						left join v_UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
					where
					
						EU.EvnUsluga_rid = :EvnPS_id
						and (select dt from mv) not between UC.UslugaComplex_begDT
							and COALESCE(UC.UslugaComplex_endDT, (select dt from mv)
						)
						and EU.EvnUsluga_setDT is not null
						and ep.EvnClass_SysNick <> 'EvnUslugaPar'
				", array(
					'EvnPS_id' => $this->id,
					'EvnPS_disDate' => $this->disDate,
				));
				if (!is_array($usluga_complex_code_list)) {
					throw new Exception('Ошибка при получении не действующих услуг на дату окончания случая лечения');
				}
				if (count($usluga_complex_code_list) > 0) {
					$usluga_complex_code_list_str = implode(", ", $usluga_complex_code_list);
					throw new Exception("Услуги должны быть действующими на дату окончания случая лечения. Проверьте актуальность услуг(и): {$usluga_complex_code_list_str}");
				}
			}

			if (getRegionNick() == 'buryatiya' && !empty($this->id) && $this->PrehospType_id == 2) {
				$resp_check = $this->queryResult("
					select
						es.EvnSection_id as \"EvnSection_id\"
					from
						v_EvnSection es
						left join v_Diag d on d.Diag_id = es.Diag_id
						left join v_Diag d2 on d2.Diag_id = d.Diag_pid
						left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
					where
						es.EvnSection_pid = :EvnSection_pid
						and coalesce(lsp.LpuSectionProfile_Code, '') <> '158'
						and d.Diag_Code <> 'I20.8'
						and d2.Diag_Code in ('I60', 'I61', 'I62', 'I63', 'I64', 'I20', 'I21', 'I22', 'I23', 'I24', 'G45')
					limit 1
				", [
					'EvnSection_pid' => $this->id
				]);
				if (!empty($resp_check[0]['EvnSection_id'])) {
					throw new Exception("Указанные диагноз и профиль подразумевают экстренное лечение. Для корректного формирования реестров заполните данные об экстренной госпитализации.");
				}
			}
			
			$this->_checkMorbusOnkoLeave();
		}

		// Проверка КВС на пересечение по дате госпитализации с другими стационарными случаями
		// Проверка должна также работать и при автоматическом сохранении КВС, поэтому вынес ее из-под условия if (self::SCENARIO_DO_SAVE == $this->scenario)
		// @task https://redmine.swan.perm.ru/issues/66979
		// Завернул все это безобразие в условие, т.к. с пересечениями можно сохранять КВС
		$kvs_intersection_control = 1;
		if (array_key_exists('kvs_intersection_control', $this->globalOptions['globals'])) {
			$kvs_intersection_control = $this->globalOptions['globals']['kvs_intersection_control'];
		}
		$control_paytype = 0;
		if (array_key_exists('kvs_intersection_control_paytype', $this->globalOptions['globals'])) {
			$control_paytype = $this->globalOptions['globals']['kvs_intersection_control_paytype'];
		}
		if (
			($kvs_intersection_control == 3 || ($kvs_intersection_control == 2 && empty($this->_params['ignoreEvnPSDoublesCheck'])))
			&& !in_array($this->scenario, array(self::SCENARIO_DELETE)) // Проверка не должна работать при удалении КВС @task https://redmine.swan.perm.ru/issues/69971
		) {
			$LpuUnitType = $this->getLpuUnitTypeFromFirstEvnSection(array(
				'EvnPS_id' => $this->id,
				'Lpu_id' => $this->Lpu_id,
			));

			$EvnPS_setDate = $this->setDate;
			if (!empty($this->setTime)) {
				$EvnPS_setDate .= ' '.$this->setTime;
			}

			$EvnPS_disDate = $this->outcomeDT;
			foreach ( $this->listEvnSectionData as $data ) {
				if ($data['ispriem']) {
					continue;
				}
				$EvnPS_disDate = $data['disdt'];
			}

			$response = $this->checkEvnPSDoubles(array(
				'EvnPS_id' => $this->id,
				'EvnPS_setDate' => $EvnPS_setDate,
				'EvnPS_disDate' => $EvnPS_disDate,
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id,
				'PayType_id' => $control_paytype?$this->PayType_id:null,
				'LpuUnitType_SysNick' => is_array($LpuUnitType)?$LpuUnitType['LpuUnitType_SysNick']:null,
			));
			if ( !is_array($response) ) {
				throw new Exception('Ошибка при проверке дублирования случаев пребывания пациента в стационаре');
			}
			if ( count($response) > 0 ) {
				$ext_count = 0;
				$int_count = 0;
				foreach ( $response as $double ) {
					if ( $double['intersect_type'] === 'outer' ) {
						$ext_count += 1;
					}
					if ( $double['intersect_type'] === 'inner' ) {
						$int_count += 1;
					}
				}

				$msg = 'Внимание! <br/>';
				$msg .= 'Имеется пересечение по дате госпитализации с другими стационарными случаями. <br/>';
				$msg .= 'Случаев пересечения внутри ЛПУ: ' . $int_count . ' <br/>';
				$msg .= 'Случаев пересечения с другими ЛПУ: ' . $ext_count . ' <br/>';


				if ($kvs_intersection_control == 3){
					//Запрет сохранения
					throw new Exception($msg." Сохранение запрещено.", 113);
				} else if (empty($this->_params['ignoreEvnPSDoublesCheck'])) {
					//предупреждение
					$this->_saveResponse['Alert_Msg'] = $msg." Продолжить сохранение?";
					$this->_saveResponse['data'] = $response;
					throw new Exception('YesNo', 113);
				}
			}
		}

		if (self::SCENARIO_DO_SAVE == $this->scenario) {
			// https://redmine.swan.perm.ru/issues/4614
			// Проверка заполнения хотя бы одного из диагнозов - направившего учреждения или приемного отделения
			if ( isset($this->PrehospDirect_id) ) {
				if ( empty($this->Diag_did) && 'perm' == $this->regionNick && $this->person_OmsSprTerr_Code > 100 ) {
					throw new Exception('При заполненном поле "Кем направлен" диагноз направившего учреждения обязателен для заполнения');
				}
				if ( in_array($this->PrehospDirect_id, array(1,2)) ) {
					if (isset($this->Org_did)) {
						$this->load->model('Org_model');
						$response = $this->Org_model->getLpuData(array('Org_id'=>$this->Org_did));
						if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
							$this->setAttribute('lpu_did', $response[0]['Lpu_id']);
						}
					}
				}
			}

			$this->_checkChangeUslugaComplex();

			$this->_updatePrehospStatus();
			$this->_checkChangeDiagPid();
			$this->_checkPrehospDirect();
             
			//Контроль типа госпитализации
			$this->_checkPrehospType();

			// Если не совпадают дата выписки в одном из движений и дата госпитализации в последующем движении, то сохранение отменять и
			// выводить сообщение
			$lastDisDT = $this->outcomeDT;
			foreach ( $this->listEvnSectionData as $data ) {
				if ($data['ispriem']) {
					continue;
				}
				if (isset($lastDisDT) && ConvertDateFormat($lastDisDT, 'd.m.Y') != ConvertDateFormat($data['setdt'], 'd.m.Y')) {
					throw new Exception('Сохранение отменено, т.к. не совпадают дата выписки в одном из движений и дата госпитализации в последующем движении.');
				}
				$lastDisDT = $data['disdt'];
			}

			// Проверка заполнения времени начала заболевания при экстренной госпитализации
			// https://redmine.swan.perm.ru/issues/78173
			$this->load->model('Options_model');
			$eps_control = $this->Options_model->getOptionsGlobals($this->_params, 'eps_control');
			if ($eps_control == 2) {
				if ( in_array($this->PrehospType_id, array(1, 3)) && (empty($this->timedesease) || empty($this->okei_id)) && empty($this->_params['ignoreEvnPSTimeDeseaseCheck']) ) {
					$this->_setAlertMsg('Не указано время с начала заболевания. Сохранить?');
					$this->_saveResponse['data'] = array();
					throw new Exception('YesNo', 114);
				}
			} elseif ($eps_control == 3) {
				if ( in_array($this->PrehospType_id, array(1, 3)) && (empty($this->timedesease) || empty($this->okei_id)) ) {
					throw new Exception('Не указано время с начала заболевания');
				}
			}

			// Проверка соответствия типов оплаты в движениях
			// https://jira.is-mis.ru/browse/PROMEDWEB-5116
			if (!empty($this->id) && in_array(getRegionNick(), ['adygeya', 'perm']) && $this->hasEvnSectionWithOtherPayType($this->id, $this->PayType_id)) {
				$payTypes = $this->getPayTypesFromSections($this->id);
				$payTypes = implode(', ', array_column($payTypes,'PayType_Name'));
				throw new Exception( 'В КВС указаны разные виды оплаты: '.$payTypes.'. Укажите один вид оплаты для всех движений.' );
			}
		}

		// Вынес из-под условия self::SCENARIO_DO_SAVE == $this->scenario
		// @task https://redmine.swan.perm.ru/issues/83882
		// Добавил реализацию проверки при сценариях self::SCENARIO_AUTO_CREATE и self::SCENARIO_DO_SAVE
		// @task https://redmine.swan.perm.ru/issues/90572
		// Добавил реализацию проверки при сценарии self::SCENARIO_SET_ATTRIBUTE
		if ( in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE, self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE)) ) {
			// Проверка КВС на дубли по номеру
			$response = $this->checkEvnPSDoublesByNum(array(
				'EvnPS_id' => $this->id,
				'EvnPS_IsCont' => $this->IsCont,
				'EvnPS_NumCard' => $this->NumCard,
				'EvnPS_setDT' => $this->setDT,
				'Lpu_id' => $this->Lpu_id
			));
			if ( !is_array($response) ) {
				throw new Exception('Ошибка при проверке дублей карты по номеру');
			}
			if ( count($response) > 0 ) {
				throw new Exception('Указанный номер карты уже используется');
			}
		}

		$lastEvnSectionDisDT = $this->_evnSectionLast?$this->_evnSectionLast->disDT:null;
		$this->checkHtmDates($lastEvnSectionDisDT);

		if (!empty($this->id) && $this->scenario == self::SCENARIO_DO_SAVE) {
			$cnt = $this->getFirstResultFromQuery("
			select 
				(select count(*) from v_EvnDiagPS  where EvnDiagPS_pid = :id and Diag_id = :Diag_did and DiagSetType_id = 1) + 
				(select count(*) from v_EvnDiagPS  where EvnDiagPS_pid = :id and Diag_id = :Diag_pid and DiagSetType_id = 2)
			as \"cnt\"
			", [
				'id' => $this->id,
				'Diag_did' => $this->Diag_did,
				'Diag_pid' => $this->Diag_pid
			]);
			if ($cnt > 0) {
				throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
			}
		}

		if (
			getRegionNick() == 'kz' &&
			!empty($this->id) &&
			$this->scenario == self::SCENARIO_DO_SAVE &&
			in_array($this->PayType_id, [150, 151])
		) {
			$chk = $this->queryList("
				select 
					ls.LpuSection_Name as \"LpuSection_Name\"
				from v_EvnSection es
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					left join r101.GetBedEvnLink gbel on gbel.Evn_id = es.EvnSection_id
				where
					es.EvnSection_pid = :id and 
					gbel.GetBed_id is null
			", ['id' => $this->id]);
			if (count($chk)) {
				throw new Exception('В движении '.join(', ', $chk).' не заполнена информация о койке');
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		// если было направление и выбрали другое, то старое надо освободить (вернуть в прежний статус)
		$this->load->model('EvnDirectionAll_model');
		$response = $this->EvnDirectionAll_model->onBeforeSetAnotherDirectionEvnPS($this);
		if ( !empty($response['Error_Msg']) ) {
			throw new Exception($response['Error_Msg'], $response['Error_Code']);
		}

		if ($this->regionNick == 'adygeya' && $this->scenario == self::SCENARIO_DO_SAVE && isset($data['EvnPS_HTMBegDate'])) {
			if ($this->setDate < $data['EvnPS_HTMBegDate'])
				throw new Exception('Дата выдачи талона на ВМП должна быть раньше даты госпитализации. Укажите верную дату выдачи талона.');
			if (isset($data['EvnPS_HTMHospDate']) && $data['EvnPS_HTMHospDate'] < $data['EvnPS_HTMBegDate'])
				throw new Exception('Дата планируемой госпитализации должна быть позже даты выдачи талона на ВМП. Укажите верную дату планируемой госпитализации.');
		}

		if ( $this->regionNick == 'penza') {
			$needHTMCheck = false;
			// Если в КВС выбрано направление типа «направление на ВМП» или заполнены поля «Номер талона на ВМП» и «Дата выдачи талона на ВМП»
			if (!empty($this->HTMTicketNum) || !empty($this->HTMTicketNum)) {
				$needHTMCheck = true;
			}
			if (!empty($this->EvnDirection_id) && !$needHTMCheck) {
				$resp_ed = $this->queryResult("
					select
						EvnDirectionHTM_id as \"EvnDirectionHTM_id\"
					from v_EvnDirectionHTM
					where EvnDirectionHTM_id = :EvnDirection_id
					limit 1
				", array(
					'EvnDirection_id' => $this->EvnDirection_id
				));
				if (!empty($resp_ed[0]['EvnDirectionHTM_id'])) {
					$needHTMCheck = true;
				}
			}

			if ($needHTMCheck) {
				// Если у отделения в движении (проверка всех профильных движений в рамках КВС) снят флаг «Выполнение высокотехнологичной медицинской помощи»,
				// сообщение об ошибке: «В отделении <Краткое наименование МО> <Код отделения> <Наименование отделения> не предусмотрено выполнение высокотехнологичной помощи. Необходимо выбрать другое направление или изменить параметры отделения. Ок. Отмена.
				$resp = $this->queryResult("
					select
						LS.LpuSection_Code as \"LpuSection_Code\",
						LS.LpuSection_Name as \"LpuSection_Name\",
						L.Lpu_Nick as \"Lpu_Nick\"
					from
						v_EvnPS eps 
						inner join v_EvnSection es  on es.EvnSection_pid = eps.EvnPS_Id
						inner join v_LpuSection ls  on ls.LpuSection_id = es.LpuSection_id
						left join v_Lpu l  on l.Lpu_id = ls.Lpu_id
					where
						eps.EvnPS_id = :EvnPS_id
						and COALESCE(ls.LpuSection_IsHTMedicalCare, 1) = 1
						and COALESCE(es.EvnSection_IsPriem, 1) = 1
					limit 1
				", array(
					'EvnPS_id' => $this->id
				));

				if (!empty($resp[0])) {
					throw new Exception('В отделении ' . $resp[0]['Lpu_Nick'] . ' ' . $resp[0]['LpuSection_Code'] . ' ' . $resp[0]['LpuSection_Name'] . ' не предусмотрено выполнение высокотехнологичной помощи. Необходимо выбрать другое направление или изменить параметры отделения.');
				}

				// Если ни в одном движении в рамках КВС не заполнено поле «Метод высокотехнологичной медицинской помощи»,
				$resp = $this->queryResult("
					select
						ES.HTMedicalCareClass_id as \"HTMedicalCareClass_id\"
					from
						v_EvnPS eps 
						inner join v_EvnSection es  on es.EvnSection_pid = eps.EvnPS_Id
					where
						eps.EvnPS_id = :EvnPS_id
						and COALESCE(es.EvnSection_IsPriem, 1) = 1
					order by
						ES.HTMedicalCareClass_id desc
					limit 1
				", array(
					'EvnPS_id' => $this->id
				));

				if (isset($resp[0]) && empty($resp[0]['HTMedicalCareClass_id'])) {
					throw new Exception('Необходимо заполнить поле "Метод высокотехнологичной медицинской помощи" хотя бы в одном движении в рамках текущей КВС');
				}
			}
		}

		if ( $this->regionNick == 'perm') {

			$EvnPS_isFinish = $this->getFirstResultFromQuery('
				select
					EvnSection_id as "EvnSection_id"
				from v_EvnSection ES 
					left join v_CureResult CR  on CR.CureResult_id = ES.CureResult_id
				where ES.EvnSection_rid = :EvnPS_id
					and CR.CureResult_Code = 1
				limit 1
			', $data);

			//Если случай лечения закончен проверяем услуги на вхождение в период КВС
			if (!empty($EvnPS_isFinish)) {
				$checkDate = $this->CheckEvnUslugasDate($data['EvnPS_id'], !empty($data['ignoreParentEvnDateCheck'])?$data['ignoreParentEvnDateCheck']:null);
				if ( !$this->isSuccessful($checkDate) ) {
					throw new Exception($checkDate[0]['Error_Msg'], (int)$checkDate[0]['Error_Code']);
				}

				if (!empty($data['EvnPS_id'])) {
					$PayTypeMBTSZZ = $this->getFirstRowFromQuery("
						select
							es.EvnSection_id
						from
							v_EvnPS eps 
							inner join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
							left join v_PayType pt on pt.PayType_id = es.PayType_id
						where
							eps.EvnPS_id = :EvnPS_id
							and pt.PayType_SysNick = 'mbudtrans_mbud'
							and coalesce(es.EvnSection_IsPriem,1)=1
						limit 1	
					", $data);

					if (!empty($PayTypeMBTSZZ)) {
						$this->checkPayTypeMBT(array(
							'EvnPS_id' => $data['EvnPS_id']
						));
					}
				}
			}

			// https://redmine.swan.perm.ru/issues/76559 - тут сделано
			// https://redmine.swan.perm.ru/issues/78033 - тут закомментировано
			/*if ( !empty($this->_params['LeaveType_fedid']) && $this->getPayTypeSysNick() == 'oms' ) {
				$LeaveType_Code = $this->getFirstResultFromQuery(
					'select LeaveType_Code from fed.v_LeaveType  where LeaveType_id = :LeaveType_fedid limit 1',
					array('LeaveType_fedid' => $this->_params['LeaveType_fedid'])
				);

				if ( $LeaveType_Code == 313 ) {
					throw new Exception('Случаи с результатом "313 Констатация факта смерти" не подлежат оплате по ОМС');
				}
			}*/
		}

		if (false && getRegionNick() == 'krym') { // убрали проверку #113905
			if (
				!empty($this->id) &&
				!empty($data['EvnPS_disDate']) // если случай закрыт
			) {
				// проверяем заполненность КПГ наличие услуг A11.20.027.1; A11.20.027.2; A11.20.027.3; A11.20.027.4
				$resp_es = $this->queryResult("
					select
						es.EvnSection_id as \"EvnSection_id\"
					from
						v_EvnSection es 
					where
						es.EvnSection_pid = :EvnPS_id
						and (
							Mes_kid is not null
							or exists (
								select
									eu.EvnUsluga_id
								from
									v_EvnUsluga eu 
									inner join v_UslugaComplex uc  on uc.UslugaComplex_id = eu.UslugaComplex_id
								where
									eu.EvnUsluga_pid = es.EvnSection_id
									and eu.PayType_id = (Select PayType_id from v_PayType pt  where pt.PayType_SysNick = 'oms' limit 1)
									and uc.UslugaComplex_Code in ('A11.20.027.1', 'A11.20.027.2', 'A11.20.027.3', 'A11.20.027.4')
								limit 1
							)
						)
					limit 1
				", array(
					'EvnPS_id' => $this->id
				));

				if (empty($resp_es[0]['EvnSection_id'])) {
					// если таких услуг нет
					throw new Exception('КПГ не заполнен, уточните профиль', 102);
				}
			}
		}

		if ($this->regionNick == 'kareliya'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE))
			&& !empty($this->evnSectionLast)
		) {
			$this->evnSectionLast->checkEvnUslugaV001();
		}

		if(in_array($this->regionNick, ['adygeya', 'pskov', 'khak'])){ // #161605

			if($this->payTypeSysNick == 'oms' && $this->PrehospType_id != 2){
				$LpuUnitType = $this->getLpuUnitTypeFromFirstEvnSection(array(
					'EvnPS_id' => $this->id,
					'Lpu_id' => $this->Lpu_id,
				));
				if(in_array($LpuUnitType['LpuUnitType_SysNick'], array('dstac', 'hstac', 'pstac'))){
					throw new Exception('В отделение дневного стационара пациент может быть госпитализирован только планово: проверьте, корректно ли указано отделение в первом движении КВС или измените значение поля «Тип госпитализации» на «Планово»');
				}

			}

		}

		if ( !empty($data['EvnPS_OutcomeDate']) && !empty($data['LpuSection_eid']) && !empty($data['Person_id'])) {
			//Проверяем пересечение с ТАП если выставлена соответствующая настройка
			$this->checkIntersectEvnSectionWithVizit(array(
				'LpuSection_id' => $data['LpuSection_eid'],
				'EvnSection_disDate' => $data['EvnPS_OutcomeDate'],
				'EvnSection_disTime' => $data['EvnPS_OutcomeTime'],
				'EvnSection_setDate' => $data['EvnPS_setDate'],
				'EvnSection_setTime' => $data['EvnPS_setTime'],
				'PayType_id' => $data['PayType_id'],
				'Person_id' => $data['Person_id'],
				'vizit_direction_control_check' => $data['vizit_direction_control_check'],
				'session' => $data['session']
			));
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPS_id']) && !empty($data['EvnPS_disDate']) && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPS_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id'],
				'Lpu_id' => $data['Lpu_id']
			));
		}

		if ($this->regionNick == 'astra') {
			if (!empty($data['EvnDirectionExt_id']) && empty($data['EvnDirection_id'])) {
				// выполняем идентификацию.
				$this->load->model('EvnDirectionExt_model');
				$resp_ed = $this->EvnDirectionExt_model->identEvnDirectionExt(array(
					'EvnDirectionExt_id' => $data['EvnDirectionExt_id'],
					'Person_id' => $data['Person_id'],
					'session' => $data['session'],
					'pmUser_id' => $this->promedUserId
				));
				if ( !empty($resp_ed['Error_Msg']) ) {
					throw new Exception($resp_ed['Error_Msg']);
				}

				if (!empty($resp_ed['EvnDirection_id'])) {
					// если проидентифицировалось то сохраняем ссылку на новое направление
					$this->setAttribute('evndirection_id', $resp_ed['EvnDirection_id']);
					$this->setAttribute('evndirection_num', $resp_ed['EvnDirection_Num']);
				}
			}
		}

		//Начинаем отслеживать статусы события EvnPS
		$this->personNoticeEvn->setEvnClassSysNick('EvnPS');
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotFirst();
	}

	/**
	 * Проверки при попытке добавить исход госпитализации из ЭМК, АРМа стационара,
	 * в форме редактирования движения
	 */
	function checkBeforeLeave($data)
	{
		try {
			if (empty($data['EvnSection_pid'])) {
				throw new Exception('Не указан идентификатор КВС');
			}
			if ('checkBeforeLeaveFromForm' != $this->scenario) {
				if (empty($data['EvnSection_id'])) {
					throw new Exception('Не указано движение');
				}
				unset($data['LpuSection_id']);
				unset($data['MedPersonal_id']);
				unset($data['MedStaffFact_id']);
				unset($data['EvnSection_setDate']);
				unset($data['EvnSection_setTime']);
			}
			$this->reset();
			$this->applyData(array(
				'session' => $data['session'],
				'EvnPS_id' => $data['EvnSection_pid'],
				'childPS' => $data['childPS']
			));
			$this->load->model('EvnSection_model');
			$this->EvnSection_model->reset();

			if ( 'checkBeforeLeaveFromForm' != $this->scenario ) {
				$applyData = array(
					'session' => $data['session'],
					'EvnSection_id' => $data['EvnSection_id'],
				);
			}
			else {
				$applyData = $data;
			}

			$this->EvnSection_model->applyData($applyData);
			$this->EvnSection_model->setParent($this);
			$this->EvnSection_model->setScenario(self::SCENARIO_DO_SAVE);

			//Контроль заполнения данных движения
			if (empty($this->EvnSection_model->MedPersonal_id) || empty($this->EvnSection_model->MedStaffFact_id)) {
				throw new Exception('Не указан лечащий врач');
			}
			/*if (
				$this->regionNick == 'pskov' && empty($this->EvnSection_model->UslugaComplex_id) && empty($this->EvnSection_model->HTMedicalCareClass_id)
				&& empty($data['UslugaComplex_id']) && empty($data['HTMedicalCareClass_id'])
				&& $this->EvnSection_model->getLpuUnitTypeSysNick() != 'stac'

			) {
				throw new Exception('Не указана услуга лечения');
			}*/
			if (false == $this->EvnSection_model->isNewRecord) {
				$this->EvnSection_model->checkEvnSectionNarrowBed();
			}

			// @task https://redmine.swan.perm.ru/issues/139189
			if (getRegionNick() != 'kz') {

				$params = array(
					'Evn_id' => $data['EvnSection_id']
				);
				if (isset($_POST['EvnSection_IsZNO'])) {
					$params['EvnSection_IsZNO'] = $data['EvnSection_IsZNO'];
				}
				$this->load->model('MorbusOnkoSpecifics_model', 'MorbusOnkoSpecifics');
				$eu_check = $this->MorbusOnkoSpecifics->checkMorbusOnkoSpecificsUsluga($params);
				if ($eu_check !== false && is_array($eu_check)) {
					throw new Exception('В движении необходимо заполнить обязательные поля в специфике по онкологии в разделе ' . $eu_check['error_section']);
				}
			}

		} catch (Exception $e) {
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
		}
		return $this->_saveResponse;
	}

	/**
	 * Контроль поля "Кем направлен"
	 * @throws Exception
	 */
	protected function _checkPrehospDirect() {
		if (!empty($this->PrehospDirect_id) && getRegionNick() == 'kareliya') {
			$Lpu_did = null;
			if ($this->prehospDirectSysNick == 'lpusection') {
				$Lpu_did = $this->Lpu_id;
			} else if ($this->prehospDirectSysNick == 'lpu') {
				$Lpu_did = $this->Lpu_did;
			} else if (!empty($this->Org_did)) {
				$Lpu_did = $this->getFirstResultFromQuery("
					select
						L.Lpu_id as \"Lpu_id\"
					from v_Lpu_all L
					where L.Org_id = :Org_id
					limit 1
				", array('Org_id' => $this->Org_did), true);
				if ($Lpu_did === false) {
					throw new Exception('Ошибка при проверке направившей организации');
				}
			}
			if (!empty($Lpu_did) && (empty($this->EvnDirection_Num) || empty($this->EvnDirection_setDT))) {
				throw new Exception("При направлении из медицинской организации поля <Номер направления> и <Дата направления> - обязательны к заполнению");
			}
		}
	}

	/**
	 * Контроль типа госпитализации
	 * @throws Exception
	 */
	protected function _checkPrehospType()
	{
		/*$debug = array(
			$this->prehospTypeSysNick,
			$object->lpuUnitTypeSysNick,
			$object->setDate,
			$this->payTypeSysNick,
			$this->IsCont,
			$this->prehospDirectSysNick,
			$this->EvnDirection_Num,
			$this->EvnDirection_setDT
		);
		throw new Exception(var_export($debug, true));*/
		//print_r($this->evnSectionFirstId);
		$omsDirectSysNicks = array('lpu', 'lpusection');
		if (getRegionNick() == 'perm' && $this->prehospDirectSysNick == 'lpu' && !$this->hasLpuPeriodOMS()) {
			//Для других МО, не имеющих периода ОМС, номер и дата направления не обязательны
			$omsDirectSysNicks = array('lpusection');
		}
		if (getRegionNick() == 'astra') {
			$omsDirectSysNicks = array('lpu', 'lpusection', 'rvk');
		}

		$LpuSection_id = $this->LpuSection_id;
		if (empty($LpuSection_id)) {
			$EvnSectionLast = $this->getEvnSectionLast();
			if (is_object($EvnSectionLast)) {
				$LpuSection_id = $EvnSectionLast->LpuSection_id;
			}
		}
		$lpuUnitTypeSysNick = "";
		if (!empty($LpuSection_id)) {
			$lpuUnitTypeSysNick = $this->getFirstResultFromQuery("
				select
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				from v_LpuSection LS
				inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				where LS.LpuSection_id = :LpuSection_id",
				array('LpuSection_id' => $LpuSection_id), true);
		}
		
		if ($this->prehospTypeSysNick == 'plan' // При плановой госпитализации
			&& !in_array($this->regionNick, array('penza', 'ufa','kz','by','perm','kareliya','ekb', 'khak', 'adygeya')) //https://redmine.swan.perm.ru/issues/57210 22 комент
			&& isset($lpuUnitTypeSysNick) && $lpuUnitTypeSysNick == 'stac' // в круглосуточный стационар
			&& $this->setDate > '2012-03-31' // начиная с 01.04.2012
			&& $this->payTypeSysNick == $this->payTypeSysNickOMS // с видом оплаты ОМС
			&& $this->IsCont != 2 // без перевода
			&& (// поля <Номер направления> и <Дата направления> - обязательны к заполнению
				empty($this->EvnDirection_Num)
					|| empty($this->EvnDirection_setDT)
					// поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"
					|| !in_array($this->prehospDirectSysNick, $omsDirectSysNicks)
			)
		) {
			/*if ($this->regionNick == 'kz') {
				$pay_type = "Республиканский (Пол-ка,стац,СКПН)";
			} else {
				$pay_type = "ОМС";
			}*/
			$pay_type = "ОМС";
			throw new Exception('При плановой госпитализации в круглосуточный стационар
			с видом оплаты '.$pay_type.' и без перевода, начиная с 01.04.2012
			поля <Номер направления> и <Дата направления> - обязательны к заполнению,
			поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"');
		}

		// https://jira.is-mis.ru/browse/PROMEDWEB-5116 Для Хакасии и Адыгеи
		if ($this->prehospTypeSysNick == 'plan' // При плановой госпитализации
			&& in_array($this->regionNick, array('khak', 'adygeya'))
			&& isset($lpuUnitTypeSysNick) && substr($lpuUnitTypeSysNick, -4) == 'stac' // в круглосуточный или дневной стационар
			&& $this->payTypeSysNick == $this->payTypeSysNickOMS // с видом оплаты ОМС
			&& $this->IsCont != 2 // без перевода
			&& (// поля <Номер направления> и <Дата направления> - обязательны к заполнению
				empty($this->EvnDirection_Num)
					|| empty($this->EvnDirection_setDT)
					// поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"
					|| !in_array($this->prehospDirectSysNick, $omsDirectSysNicks)
			)
		) {
			$pay_type = "ОМС";
			throw new Exception('При плановой госпитализации в круглосуточный или дневной стационар
			с видом оплаты '.$pay_type.' и без перевода,
			поля <Номер направления> и <Дата направления> - обязательны к заполнению,
			поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"');
		}

		//https://redmine.swan.perm.ru/issues/85667#note-11 Для Перми
		if ($this->prehospTypeSysNick == 'plan' // При плановой госпитализации
			&& in_array($this->regionNick, array('perm'))
			&& isset($lpuUnitTypeSysNick) && ($lpuUnitTypeSysNick == 'stac' || $lpuUnitTypeSysNick == 'dstac') // в круглосуточный стационар или в дневной стационар
			//&& $object->setDate > '2012-03-31' // начиная с 01.04.2012
			&& in_array($this->payTypeSysNick, array($this->payTypeSysNickOMS)) // с видом оплаты ОМС
			&& $this->IsCont != 2 // без перевода
			&& (empty($this->PrehospDirect_id)
				|| in_array($this->prehospDirectSysNick, $omsDirectSysNicks) && (empty($this->EvnDirection_Num) || empty($this->EvnDirection_setDT))
			)
		) {
			throw new Exception('При плановой госпитализации в круглосуточный или дневной стационар
			и без перевода поля <Номер направления>, <Дата направления>
			и <Кем направлен> - обязательны к заполнению');
		}
		// https://redmine.swan.perm.ru/issues/117645 для Пензы
		if ($this->prehospTypeSysNick == 'plan' // При плановой госпитализации
			&& in_array($this->regionNick, array('penza'))
			&& $this->payTypeSysNick == $this->payTypeSysNickOMS // с видом оплаты ОМС
			&& (empty($this->PrehospDirect_id) || empty($this->EvnDirection_Num) || empty($this->EvnDirection_setDT))
		) {
			// Если в КВС указана плановая госпитализация и не заполнены данные о направлении, то выводится сообщение об ошибке, процесс сохранения КВС останавливается.
			$pay_type = "ОМС";
			throw new Exception('При плановой госпитализации с видом оплаты '.$pay_type.' поля <Номер направления>, <Дата направления> и <Кем направлен> - обязательны к заполнению');
		}
		/* перенесено из checkPrehospType при сохранении движения
		if (!in_array($this->regionNick, array('astra', 'ufa')) // кроме Самары и Уфы, и Астрахани https://redmine.swan.perm.ru/issues/42663
			&& isset($this->disDate) // проверяем при закрытии случая
			&& self::SCENARIO_DO_SAVE == $this->scenario // при сохранении из формы
			&& $this->id == $this->parent->evnSectionLastId // при сохранении последнего движения
			&& $this->parent->prehospTypeSysNick == 'plan' // При плановой госпитализации
			&& $this->lpuUnitTypeSysNick == 'stac' // в круглосуточный стационар
			&& $this->parent->payTypeSysNick == $this->payTypeSysNickOMS // с видом оплаты ОМС
			&& $this->parent->IsCont != 2 // без перевода
			&& $this->disDate > '2012-03-31' // начиная с 01.04.2012
			&& (// поля <Номер направления> и <Дата направления> - обязательны к заполнению
				empty($this->parent->EvnDirection_Num)
					|| empty($this->parent->EvnDirection_setDT)
					// поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"
					|| !in_array($this->parent->prehospDirectSysNick, array('lpu', 'lpusection'))
			)
		) {
			throw new Exception('При плановой госпитализации в круглосуточный стационар
			с видом оплаты ОМС и без перевода, начиная с 01.04.2012
			поля <Номер направления> и <Дата направления> - обязательны к заполнению,
			поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"');
		}*/
	}

	/**
	 * Проверка диагноза приемного отделения
	 * @throws Exception
	 */
	protected function _checkChangeDiagPid()
	{
		if (empty($this->LpuSection_pid)) {
			$this->setAttribute('diag_pid', null);
		}
		if (isset($this->Diag_pid) && isset($this->id)
			&& 'ekb' == $this->regionNick
			&& $this->payTypeSysNick == 'bud'
			&& $this->_params['UslugaComplex_id']==4568436
			&& count($this->listEvnSectionData) > 0
		) {
			$leav  = $this->checkIsOMS(array('Diag_id'=>$this->Diag_pid));
			if(!$leav){
				throw new Exception('Диагноз не оплачивается по ОМС');
			}
		}
		if (false == $this->_isAttributeChanged('diag_pid') || empty($this->LpuSection_pid)) {
			return true;
		}

		/* Контроль ввода диагноза в приемном отделении по ССЗ #35215 для ПК
		 *
		 * При сохранении КВС при выполнении следующих условий:
		 * Включена настройка «Обязательность ввода диагноза в приемном отделении»
		 * Диагноз приемного отделения из списка ССЗ
		 * Введено хотя бы одного движения в КВС по ОМС
		 * Диагноз этого движения из списка ССЗ
		 * необходимо выдать сообщение «».
		 * Сохранение запретить.
		 */
		$this->load->model('Options_model');
		$check_priemdiag_allow = $this->Options_model->getOptionsGlobals($this->_params, 'check_priemdiag_allow');

		if (isset($this->Diag_pid) && isset($this->id)
			&& 'perm' == $this->regionNick
			&& count($this->listEvnSectionData) > 0
			&& $check_priemdiag_allow == 1
		) {
			$hasCC3 = $this->loadSszDiagId($this->Diag_pid);
			if ($hasCC3) {
				$hasCC3 = false;
				foreach ($this->listEvnSectionData as $row) {
					if ($row['ispriem']) {
						continue;
					}
					if ($row['ssz_Diag_id'] && $row['PayType_SysNick'] == $this->payTypeSysNickOMS) {
						$hasCC3 = true;
						break;
					}
				}
			}
			if ($hasCC3) {
				throw new Exception('Диагноз приемного отделения не может быть из списка ССЗ для оплаты по ОМС');
			}
		}



		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function _checkChangeUslugaComplex() {
		if (empty($this->_params['UslugaComplex_id'])) {
			return true;
		}

		if ($this->regionNick == 'perm' && $this->payTypeSysNick == 'oms') {
			$query = "
				select
					count(UCT.UslugaComplexTariff_id) as \"Count\"
				from v_UslugaComplexTariff UCT 
				where
					UCT.UslugaComplex_id = :UslugaComplex_id
					and UCT.PayType_id = :PayType_id
					and UCT.UslugaComplexTariff_begDate <= :EvnPS_setDate
					and (UCT.UslugaComplexTariff_endDate > :EvnPS_setDate or UCT.UslugaComplexTariff_endDate is null)
				limit 1
			";
			$tariff_count = $this->getFirstResultFromQuery($query, array(
				'EvnPS_setDate' => $this->setDate,
				'UslugaComplex_id' => $this->_params['UslugaComplex_id'],
				'PayType_id' => $this->PayType_id
			));
			if ($tariff_count === false) {
				throw new Exception('Ошибка при проверке наличия тарифов.', 500);
			}
			if ($tariff_count == 0) {
				$warningFrom = ($this->_params['from']=='workplacepriem')?'Приемное отделение':'КВС';
				$this->addWarningMsg($warningFrom.': На данную услугу нет тарифа!');
			}
		}
		return true;
	}

	/**
	 * Проверка заполненности дат для ВМП
	 * @param DateTime $disDT Дата закрытия КВС
	 * @throws Exception
	 */
	public function checkHtmDates($disDT, $ignoreList = array()) {
		if (empty($disDT)) {
			$disDT = null;
		}
		else if (!$disDT instanceof DateTime) {
			$disDT = date_create($disDT);
		}
		$needHTM = false;
		foreach($this->listEvnSectionData as $EvnSection_id => $EvnSection) {
			if (!empty($EvnSection['HTMedicalCareClass_id']) && !in_array($EvnSection_id, $ignoreList)) {
				$needHTM = true;break;
			}
		}

		switch (getRegionNick()) {
			case 'ekb':
				$DateX = date_create('2018-06-01');
				break;
			case 'perm':
			case 'pskov':
				$DateX = date_create('2018-04-01');
				break;
			case 'kareliya':
			case 'vologda':
			case 'adygeya':
				$DateX = date_create('2019-01-01');
				break;
			default:
				$DateX = date_create('2016-10-01');
		}
				
		if (array_key_exists('check_htm_dates', $this->globalOptions['globals'])
			&& $this->globalOptions['globals']['check_htm_dates'] && $needHTM
			// Поменял проверку на проверку даты выписки из последнего движения,
			// т.к. при заполненной дате исхода из приемного $this->disDT оказывался пустой
			// @task https://redmine.swan.perm.ru/issues/102724
			//&& !empty($this->disDT) && $this->disDT >= date_create('2016-10-01')
			&& !empty($disDT) && $disDT >= $DateX
			&& (empty($this->HTMBegDate) || empty($this->HTMHospDate) ||
				(in_array(getRegionNick(), array('adygeya', 'krasnoyarsk', 'penza', 'perm', 'ufa', 'krym', 'pskov', 'ekb', 'kareliya', 'vologda')) && empty($this->HTMTicketNum))
			)
		) {
			if (in_array(getRegionNick(), array('adygeya', 'krasnoyarsk', 'penza', 'perm', 'ufa', 'krym', 'pskov', 'ekb', 'kareliya', 'vologda'))) {
				$msg = "При выполнении ВМП должны быть указаны дата выдачи, номер талона на ВМП и дата планируемой госпитализации";
			} else {
				$msg = "При выполнении ВМП должны быть указаны дата выдачи талона на ВМП и дата планируемой госпитализации";
			}
			throw new Exception($msg);
		}
	}

	/**
	 * Проверка данных приемного отделения
	 * Изменение статуса записи АРМа приемного отделения
	 * @throws Exception
	 */
	protected function _updatePrehospStatus()
	{
		if ( empty($this->LpuSection_pid) ) {
			$this->setAttribute('lpusection_eid', null);
			$this->setAttribute('prehospwaifrefusecause_id', null);
			$this->setAttribute('resultclass_id', null);
			$this->setAttribute('resultdeseasetype_id', null);
			$this->setAttribute('istransfcall', null);
			$this->setAttribute('prehospwaifrefusedt', null);
			$this->setAttribute('outcomedt', null);
			$this->setAttribute('outcomedate', null);
			$this->setAttribute('outcometime', null);
			$this->setAttribute('prehospstatus_id', null);
			if (!empty($this->_params['addEvnSection']) && isset($this->LpuSection_id)) {
				$this->setAttribute('lpusection_eid', $this->LpuSection_id);
			}
		} else {
			if (isset($this->evnSectionFirstId)
				&& empty($this->LpuSection_eid)
				&& $this->_isAttributeChanged('lpusection_eid')
				&& $this->evnSectionFirstId != $this->evnSectionLastId
			) {
				throw new Exception('Отмена госпитализации невозможна: у пациента более одного движения');
			}
			// https://redmine.swan.perm.ru/issues/42421
			if ( !in_array($this->regionNick, array('buryatiya', 'pskov')) && isset($this->outcomeDate) && empty($this->PrehospWaifRefuseCause_id) && empty($this->LpuSection_eid) ) {
				throw new Exception('При заполненной дате исхода из приемного отделения должен быть заполнен исход пребывания в приемном отделении (отказ) или отделение, куда пациент госпитализирован');
			}
			// https://redmine.swan.perm.ru/issues/51353
			if ( in_array($this->regionNick, array('kareliya', 'krym')) && !empty($this->PrehospWaifRefuseCause_id) && (empty($this->ResultClass_id) || empty($this->ResultDeseaseType_id)) ) {
				throw new Exception('При заполненной причине отказа в приемном отделения поля "Результат обращения" и "Исход" обязательны для заполнения');
			}
			// https://redmine.swan.perm.ru/issues/60195
			if ( $this->regionNick == 'kareliya'
				&& !empty($this->PrehospWaifRefuseCause_id)
				&& $this->payTypeSysNick == $this->payTypeSysNickOMS
				&& empty($this->listEvnUslugaData)
			) {
				throw new Exception('При заполненной причине отказа в приемном отделения должна быть заведена хотя бы одна услуга');
			}

			if (isset($this->LpuSection_eid) ) {
				$this->setAttribute('prehospwaifrefusecause_id', null);
				$this->setAttribute('resultclass_id', null);
				$this->setAttribute('resultdeseasetype_id', null);
				// проверка на госпитализацию
				if ($this->_isAttributeChanged('lpusection_eid') && isset($this->evnSectionFirstId) && $this->evnSectionFirstId != $this->evnSectionLastId) {
					throw new Exception('Изменение отделения первого движения невозможно, поскольку в рамках данного случая <br/>уже имеется несколько движений.');
				}
			}
			if (isset($this->PrehospWaifRefuseCause_id)) {
				$this->setAttribute('lpusection_eid', null);
				$this->setAttribute('rfid', null);
			}

			// проставляем дату исхода из приемного отделения
			//EvnSection_setDT or EvnPS_OutcomeDT https://redmine.swan.perm.ru/issues/36141
			$evnSectionFirstId = $this->evnSectionFirstId;
			$outcomedt = null;

			if (isset($this->evnSectionFirst)) {
				// первого движения нет в БД, но оно должно быть добавлено
				$outcomedt = $this->outcomeDT ? $this->outcomeDT : $this->currentDT;
			}
			if (isset($this->PrehospWaifRefuseCause_id) || isset($this->_params['LeaveType_prmid'])) {
				// есть отказ от госпитализации
				$outcomedt = $this->outcomeDT ? $this->outcomeDT : $this->currentDT;
			}
			if (isset($outcomedt)) {
				$this->setAttribute('outcomedt', $outcomedt);
				$this->setAttribute('outcomedate', ConvertDateFormat($outcomedt, 'Y-m-d'));
				$this->setAttribute('outcometime', ConvertDateFormat($outcomedt, 'H:i'));
			} else {
				$this->setAttribute('outcomedt', null);
				$this->setAttribute('outcomedate', null);
				$this->setAttribute('outcometime', null);
			}

			// проставляем статус записи АРМа приемного отделения
			$this->setAttribute('prehospstatus_id', null);
			if (isset($this->LpuSection_eid)) {
				$this->setAttribute('prehospstatus_id', 4);
			}
			if (isset($this->PrehospWaifRefuseCause_id)) {
				$this->setAttribute('prehospstatus_id', 5);
				// проставляем дату отказа
				$this->setAttribute('prehospwaifrefusedt', $outcomedt);
				$this->setAttribute('disdt', $outcomedt);
				$this->setAttribute('disdate', ConvertDateFormat($outcomedt, 'Y-m-d'));
				$this->setAttribute('distime', ConvertDateFormat($outcomedt, 'H:i'));
			} else {
				$this->setAttribute('prehospwaifrefusedt', null);
				$this->setAttribute('disdt', null);
				$this->setAttribute('disdate', null);
				$this->setAttribute('distime', null);
				$this->setAttribute('istransfcall', null);
			}
			if (empty($this->PrehospStatus_id)) {
				$this->setAttribute('prehospstatus_id', 3);
			}
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotSecond();
		$this->personNoticeEvn->processStatusChange();
		if ($this->_isExistsEvnDirection()) {
			$this->load->model('EvnDirectionAll_model');
			switch ($this->PrehospWaifRefuseCause_id) {
				case 1: $EvnStatusCause_id = 7; break; // Отсутствие мест - Нет мест для госпитализации
				case 2: $EvnStatusCause_id = 1; break; // Отказ больного - Отказ пациента
				case 3: $EvnStatusCause_id = 6; break; // Нет экстренных показаний для госпитализации - Нет показаний для госпитализации
				case 4: $EvnStatusCause_id = 3; break; // Направление не обосновано - Ошибочное направление
				case 5: $EvnStatusCause_id = 12; break; // Направление не по профилю - Диагноз не соответствует профилю стационара
				case 6: $EvnStatusCause_id = 13; break; // Карантин в отделении - Эпидпоказания
				case 7: $EvnStatusCause_id = 13; break; // Больной контактный по инфекционному заболеванию - Эпидпоказания
				case 8: $EvnStatusCause_id = 18; break; // Уход пациента - Неявка пациента
				case 9: $EvnStatusCause_id = 22; break; // Непредоставление необходимого пакета документов - Непредоставление необходимого пакета документов
				case 10: $EvnStatusCause_id = 5; break; // Констатация факта смерти
				default: $EvnStatusCause_id = null; break;
			}
			$needSetStatus = null;
			if ($this->isNewRecord) {
				$response = $this->EvnDirectionAll_model->onCreateEvnPS($this);
				if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg']);
				}
				if ($this->LpuSection_eid > 0) {
					$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
				} else if ($EvnStatusCause_id > 0) {
					$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_REJECTED;
				}else{
					$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
				}
			} else {
				// При смене исхода пребывания в приемном с госпитализации на отказ и наоборот, должен меняться статус направления (Отклонено/Обслужено).
				$this->EvnDirectionAll_model->setParams(array(
					'session' => $this->sessionParams,
				));
				$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
				if ($this->_isAttributeChanged('prehospwaifrefusecause_id')) {
					if ($EvnStatusCause_id > 0) {
						// установлен отказ
						if ($this->EvnDirectionAll_model->EvnStatus_id != 13) {
							$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_REJECTED;
						}
					} else {
						// отменен отказ
						if (13 == $this->EvnDirectionAll_model->EvnStatus_id && !$this->_isAttributeChanged('lpusection_eid')) {
							$needSetStatus = $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE;
						}
					}
				}
				if ($this->_isAttributeChanged('lpusection_eid')) {
					if ($this->LpuSection_eid > 0 || (!empty($this->_evnSectionLast) && $this->_evnSectionLast->IsPriem != 2)) {
						// установлена госпитализация
						if ($this->EvnDirectionAll_model->EvnStatus_id != 15) {
							$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
						}
					} else {
						// отменена госпитализация
						if (15 == $this->EvnDirectionAll_model->EvnStatus_id && !$this->_isAttributeChanged('prehospwaifrefusecause_id')) {
							$needSetStatus = $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE;
						}
					}
				}
			}
			if (!$needSetStatus && $this->EvnDirectionAll_model->EvnStatus_id == 10) {
				$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
			}
			if ($needSetStatus) {
				$this->EvnDirectionAll_model->setStatus(array(
					'Evn_id' => $this->EvnDirection_id,
					'EvnStatusCause_id' => $EvnStatusCause_id,
					'EvnStatusHistory_Cause' => null,
					'EvnStatus_SysNick' => $needSetStatus,
					'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
					'pmUser_id' => $this->promedUserId,
				));
			}
		}

		if ($this->_params['from'] == 'workplacepriem' && empty($this->EvnDirection_id) && !empty($this->_params['TimetableStac_id'])) {
			//принятие пациента в приемное отделение, при госпитализации из мобильной версии СМП
			$query = "
				update TimetableStac
				set Evn_id = :Evn_id 
				where TimetableStac_id = :TimetableStac_id
			";
			$params = array(
				'Evn_id' => $this->id,
				'TimetableStac_id' => $this->_params['TimetableStac_id'],
			);
			$this->db->query($query, $params);
		}

		if ($this->regionNick == 'astra') {
			// выполняем переидентификацию.
			if (!empty($this->_savedData['evndirection_id'])) {
				$this->load->model('EvnDirectionExt_model');
				$response = $this->EvnDirectionExt_model->reidentEvnDirectionExt(array(
					'EvnDirection_id' => $this->_savedData['evndirection_id'],
					'pmUser_id' => $this->promedUserId
				));
				if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg']);
				}
			}
		}

		if ($this->regionNick == 'kz') {

			$getbedevnlink_id = $this->getFirstResultFromQuery("select GetBedEvnLink_id as \"GetBedEvnLink_id\" from r101.GetBedEvnLink where Evn_id = ?", [$this->id]);
			$proc = !$getbedevnlink_id ? 'r101.p_GetBedEvnLink_ins' : 'r101.p_GetBedEvnLink_upd';

			if ($this->_params['GetBed_id'] != null) {
				$this->execCommonSP($proc, [
					'GetBedEvnLink_id' => $getbedevnlink_id ? $getbedevnlink_id : null,
					'Evn_id' => $this->id,
					'GetBed_id' => $this->_params['GetBed_id'],
					'pmUser_id' => $this->promedUserId
				], 'array_assoc');
			} elseif ($getbedevnlink_id != false) {
				return $this->execCommonSP('r101.p_GetBedEvnLink_del', [
					'GetBedEvnLink_id' => $getbedevnlink_id
				], 'array_assoc');
			}

            $EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP where Evn_id = ?", [$this->id]);
            $proc = !$EvnLinkAPP_id ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';

            if (!empty($this->_params['Diag_cid']) || !empty($this->_params['PurposeHospital_id'])) {
                $this->execCommonSP($proc, [
                    'EvnLinkAPP_id' => $EvnLinkAPP_id ? $EvnLinkAPP_id : null,
                    'Evn_id' => $this->id,
                    'PurposeHospital_id' => $this->_params['PurposeHospital_id'],
                    'Diag_cid' => $this->_params['Diag_cid'],
                    'pmUser_id' => $this->promedUserId
                ], 'array_assoc');
            } elseif ($EvnLinkAPP_id != false) {
                return $this->execCommonSP('r101.p_EvnLinkAPP_del', [
                    'EvnLinkAPP_id' => $EvnLinkAPP_id
                ], 'array_assoc');
            }
		}

        // сообщение родственнику
        if (!empty($this->_params['FamilyContact_FIO']) || !empty($this->_params['FamilyContact_Phone'])) {
            $replace_symbols = ["-", "(", ")", " "];

			if (getRegionNick() == 'vologda' && !empty($this->_params['FamilyContactPerson_id'])) {
				$this->_params['FamilyContact_FIO'] = null;
			}
			
            $count=$this->getFirstResultFromQuery('SELECT COUNT(EvnPS_id) FROM FamilyContact WHERE EvnPS_id = :EvnPS_id', ['EvnPS_id' => $this->id])+0;
            if($count>0)
                $query = "
                        UPDATE FamilyContact 
                        SET FamilyContact_msgDT = :FamilyContact_msgDT,
                            FamilyContact_FIO = :FamilyContact_FIO, 
                            FamilyContact_Phone = :FamilyContact_Phone,
                            Person_id = :Person_id,
                            pmUser_updID = :currUser,
                            FamilyContact_updDT = dbo.tzGetDate()   
                        WHERE EvnPS_id = :EvnPS_id
                    ";
            else
                $query = "
                        INSERT INTO FamilyContact 
                        (EvnPS_id, FamilyContact_msgDT, FamilyContact_FIO, FamilyContact_Phone, Person_id, pmUser_insID, pmUser_updID, FamilyContact_insDT, FamilyContact_updDT)
                        VALUES (:EvnPS_id, :FamilyContact_msgDT, :FamilyContact_FIO, :FamilyContact_Phone, :Person_id, :currUser, :currUser, dbo.tzGetDate(), dbo.tzGetDate())
                    ";

            $params = [
                'EvnPS_id' => $this->id,
                'FamilyContact_msgDT' => $this->_params['FamilyContact_msgDate']." ".$this->_params['FamilyContact_msgTime'],
                'FamilyContact_FIO' => $this->_params['FamilyContact_FIO'],
				"Person_id" => $this->_params['FamilyContactPerson_id'] ?? null,
                'FamilyContact_Phone' => str_replace($replace_symbols, "", trim((string) $this->_params['FamilyContact_Phone'])),
                'currUser' =>  $this->promedUserId
            ];

            $this->db->query($query, $params);
        }

		$this->_saveRepositoryObserv();
		$this->_saveEvnSectionPriem($result);
		$this->_changeEvnSectionFirst($result);
		$this->_saveHomeVisit($result);
		$this->_checkConformityPayType();

		if (!empty($this->_params['RepositoryObservData'])) {
			$this->load->model('RepositoryObserv_model');
			$err = getInputParams(
				$this->_params['RepositoryObservData'], 
				$this->RepositoryObserv_model->getSaveRules(), 
				true, 
				$this->_params['RepositoryObservData']
			);
			if (empty($err)) {
				$this->_params['RepositoryObservData']['Evn_id'] = $this->id;
				$this->_params['RepositoryObservData']['Lpu_id'] = $this->Lpu_id;
				$this->_params['RepositoryObservData']['pmUser_id'] = $this->promedUserId;
				$this->RepositoryObserv_model->save($this->_params['RepositoryObservData']);
			}
		}
        
		$this->load->model('ApprovalList_model');
        $this->ApprovalList_model->saveApprovalList(array(
            'ApprovalList_ObjectName' => 'EvnPS',
            'ApprovalList_ObjectId' => $this->id,
            'pmUser_id' => $this->promedUserId
        ));
	}

	/**
	 * Логика после успешного сохранения объекта в БД со всеми составными частями
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onSave()
	{
		if (self::SCENARIO_DO_SAVE == $this->scenario && in_array(getRegionNick(), ['adygeya', 'perm']) && $this->hasEvnSectionWithOtherPayType($this->id, $this->PayType_id)) {
			$payTypes = $this->getPayTypesFromSections($this->id);
			$payTypes = implode(', ', array_column($payTypes,'PayType_Name'));
			$this->_setAlertMsg("<div>В КВС указаны разные виды оплаты: </div><div>{$payTypes}</div><div>Укажите один вид оплаты для всех движений.</div>");
		}
		try {
			// @todo переместить в _afterSave, если нужно, чтобы отменялась транзакция
			// при изменении диагноза в приемном отделении для Перми нужно пересчитать КСГ для всех движений, где диагноз ССЗ #43472
			if ( $this->regionNick == 'perm'
				&& $this->_isAttributeChanged('diag_pid')
			) {
				$this->load->model('EvnSection_model');
				foreach($this->listEvnSectionData as $id => $row) {
					if (isset($row['ssz_Diag_id'])) {
						$this->EvnSection_model->reset();
						$this->EvnSection_model->setParent($this);
						$this->EvnSection_model->recalcKSGKPGKOEF($id, $this->sessionParams);
					}
				}
			}
		} catch (Exception $e) {
			$this->_setAlertMsg("<div>При перерасчете КСГ/КПГ произошла ошибка</div><div>{$e->getMessage()}</div>");
		}
		// уведомления направившему МО
		if ($this->EvnDirection_id > 0) {
			/*echo " - ".$this->EvnDirection_id;
			exit();*/
			// Получим необходимые данные для уведомления
			$this->load->model('EvnDirection_model');
			$ndata = $this->EvnDirection_model->getDirectionDataForNotice(array(
				'EvnPS_id' => $this->id,
				'EvnDirection_id' => $this->EvnDirection_id,
			));
			$this->load->model('Messages_model');

			// Уведомление о госпитализации отсылать нужно только когда указано отделение в исходе
			if ( $this->_isAttributeChanged('lpusection_eid') && $this->LpuSection_eid > 0 ) {
				/*$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->setStatus(array(
					'Evn_id' => $this->EvnDirection_id,
					'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					'EvnClass_id' => 27,
					'pmUser_id' => $this->promedUserId,
				));*/

				if ( is_array($ndata) ) {
					$text = 'Направленный вами пациент ' .$ndata['Person_Fio']. ' в ' .$ndata['Lpu_Nick']. ' по профилю ' .$ndata['LpuSectionProfile_Name'];
					$text .= ' госпитализирован ' .$ndata['EvnPS_setDT']. ' в ' . $ndata['Lpu_H_Nick'] . ' ' .$ndata['LpuSection_H_FullName'];
					$noticeData = array(
						'autotype' => 1,
						'Lpu_rid' => $this->Lpu_id,
						'pmUser_id' => $this->promedUserId,
						'MedPersonal_rid' => $ndata['MedPersonal_id'],
						'type' => 1,
						'title' => 'Госпитализация по направлению',
						'text' => $text
					);
					$this->Messages_model->autoMessage($noticeData);
				}
			}
			// Уведомление об отказе отсылать нужно только когда отказано в госпитализации
			if ( $this->_isAttributeChanged('prehospwaifrefusecause_id') && $this->PrehospWaifRefuseCause_id > 0 ) {

				if ( is_array($ndata) ) {
					$text = 'Пациенту ' .$ndata['Person_Fio']. ', направленному вами в ' .$ndata['Lpu_Nick']. ' по профилю ' .$ndata['LpuSectionProfile_Name'];
					$text .= ' отказано в госпитализации с основанием ' .$ndata['PrehospWaifRefuseCause_Name'];
					$noticeData = array(
						'autotype' => 1,
						'Lpu_rid' => $this->Lpu_id,
						'pmUser_id' => $this->promedUserId,
						'MedPersonal_rid' => $ndata['MedPersonal_id'],
						'type' => 1,
						'title' => 'Отказ в госпитализации',
						'text' => $text
					);
					$this->load->model('Messages_model');
					$this->Messages_model->autoMessage($noticeData);
				}
			}
		}
	}

	/**
	 * Сохранение данных по COVID19
	 */
	function _saveRepositoryObserv() {
		if (getRegionNick() == 'msk' && !empty($this->id) && array_key_exists('CovidType_id', $this->_params)) {
			$resp_ceps = $this->queryResult("
				select
					ceps.RepositoryObserv_id as \"RepositoryObserv_id\"
				from
					v_RepositoryObserv ceps
				where
					ceps.Evn_id = :EvnPS_id
				limit 1	
			", [
				'EvnPS_id' => $this->id
			]);
			$RepositoryObserv_id = $resp_ceps[0]['RepositoryObserv_id'] ?? null;
			$proc = "p_RepositoryObserv_ins";
			if (!empty($RepositoryObserv_id)) {
				$proc = "p_RepositoryObserv_upd";
			}

			$resp_save = $this->queryResult("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$proc} (
					RepositoryObserv_id := :RepositoryObserv_id,
					Evn_id := :Evn_id,
					CovidType_id := :CovidType_id,
					DiagConfirmType_id := :DiagConfirmType_id,
					Person_id := :Person_id,
					LpuSection_id := :LpuSection_id,
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					RepositoryObserv_BreathRate := :RepositoryObserv_BreathRate,
					RepositoryObserv_Systolic := :RepositoryObserv_Systolic,
					RepositoryObserv_Diastolic := :RepositoryObserv_Diastolic,
					RepositoryObserv_Height := :RepositoryObserv_Height,
					RepositoryObserv_Weight := :RepositoryObserv_Weight,
					RepositoryObserv_TemperatureFrom := :RepositoryObserv_TemperatureFrom,
					RepositoryObserv_FluorographyDate := :RepositoryObserv_FluorographyDate,
					RepositoryObserv_SpO2 := :RepositoryObserv_SpO2,
					pmUser_id := :pmUser_id
				)
			", [
				'RepositoryObserv_id' => $RepositoryObserv_id,
				'Evn_id' => $this->id,
				'CovidType_id' => $this->_params['CovidType_id'] ?? null,
				'DiagConfirmType_id' => $this->_params['DiagConfirmType_id'] ?? null,
				'Person_id' => $this->Person_id,
				'LpuSection_id' => $this->LpuSection_pid,
				'MedStaffFact_id' => $this->MedStaffFact_pid,
				'MedPersonal_id' => $this->MedPersonal_pid,
				'RepositoryObserv_BreathRate' => $this->_params['RepositoryObserv_BreathRate'] ?? null,
				'RepositoryObserv_Systolic' => $this->_params['RepositoryObserv_Systolic'] ?? null,
				'RepositoryObserv_Diastolic' => $this->_params['RepositoryObserv_Diastolic'] ?? null,
				'RepositoryObserv_Height' => $this->_params['RepositoryObserv_Height'] ?? null,
				'RepositoryObserv_Weight' => $this->_params['RepositoryObserv_Weight'] ?? null,
				'RepositoryObserv_TemperatureFrom' => $this->_params['RepositoryObserv_TemperatureFrom'] ?? null,
				'RepositoryObserv_FluorographyDate' => $this->_params['RepositoryObserv_FluorographyDate'] ?? null,
				'RepositoryObserv_SpO2' => $this->_params['RepositoryObserv_SpO2'] ?? null,
				'pmUser_id' => $this->promedUserId
			]);

			if (!empty($resp_save[0]['Error_Msg'])) {
				throw new Exception($resp_save[0]['Error_Msg']);
			}
		}
	}

	/**
	 * Сохранение данных движения в приемном отделении
	 */
	protected function _saveEvnSectionPriem($result)
	{
		if (in_array($this->regionNick, $this->listRegionNickWithEvnSectionPriem)) {
			// 1. Движение в приемное создается в БД для каждого случая лечения, в случае фактического отсутствия приемного отделения создается пустым.
			// Для существующих КВС не добавлять движение в приёмном
			$this->load->model('EvnSection_model');
			$this->EvnSection_model->reset();
			$this->EvnSection_model->setParent($this);

			if ( empty($this->setDate) ) {
				throw new Exception('Пустая дата поступления в приемное отделение', 500);
			}

			if ( $this->regionNick == 'perm' && !empty($this->PrehospWaifRefuseCause_id) && !empty($this->outcomeDate) && strtotime($this->outcomeDate) >= strtotime('01.01.2015') ) {
				if ( empty($this->_params['LeaveType_fedid']) ) {
					throw new Exception('Поле "Фед. результат" обязательно для заполнения', 500);
				}

				if ( empty($this->_params['ResultDeseaseType_fedid']) ) {
					throw new Exception('Поле "Фед. исход" обязательно для заполнения', 500);
				}
			}

			// Если в КВС в поле “Вид оплаты” выбрано “Местный бюджет”, то при отказе в приемном отделении:
			// в поле “Код посещение” должны быть доступны только услуги связанные с группой 350
			// при добавлении услуг должны быть доступны только услуги связанные с группой 351
			if ( $this->regionNick == 'ekb' && !empty($this->PrehospWaifRefuseCause_id) && $this->payTypeSysNick == 'bud' ) {
				if (!empty($this->_params['UslugaComplex_id'])) {
					// проверяем поле "Код посещения"
					$resp_uc = $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
							v_UslugaComplex uc 
						where
							uc.UslugaComplex_id = :UslugaComplex_id
							and not exists (
								select
									ucpl.UslugaComplexPartitionLink_id
								from
									r66.UslugaComplexPartitionLink ucpl 
									inner join r66.UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
								where
									ucp.UslugaComplexPartition_Code = '350'
									and ucpl.UslugaComplex_id = uc.UslugaComplex_id
								limit 1
							)
						limit 1
					", array(
						'UslugaComplex_id' => $this->_params['UslugaComplex_id']
					));

					if (!empty($resp_uc[0]['UslugaComplex_id'])) {
						throw new Exception('При виде оплаты "Местный бюджет" в поле "Код посещения" может быть указана только услуга из группы 350.', 0);
					}
				}
				if (!empty($this->id)) {
					// проверяем услуги
					$resp_uc = $this->queryResult("
						select
							uc.UslugaComplex_id as \"UslugaComplex_id\"
						from
							v_EvnUsluga eu 
							inner join v_UslugaComplex uc  on uc.UslugaComplex_id = eu.UslugaComplex_id
						where
							eu.EvnUsluga_rid = :EvnSection_pid
							and COALESCE(eu.EvnUsluga_IsVizitCode, 1) = 1
							and not exists (
								select
									ucpl.UslugaComplexPartitionLink_id
								from
									r66.UslugaComplexPartitionLink ucpl 
									inner join r66.UslugaComplexPartition ucp  on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
								where
									ucp.UslugaComplexPartition_Code = '351'
									and ucpl.UslugaComplex_id = uc.UslugaComplex_id
								limit 1
							)
						limit 1
					", array(
						'EvnSection_pid' => $this->id
					));

					if (!empty($resp_uc[0]['UslugaComplex_id'])) {
						throw new Exception('При виде оплаты "Местный бюджет" для отказов в приёмном могут быть указаны только услуги из группы 351.', 0);
					}
				}
			}

			if (!empty($this->evnSectionPriemId) && $this->_isAttributeChanged('diag_pid')) {
				$this->load->library('swMorbus');
				$this->ignoreCheckMorbusOnko = $this->_params['ignoreCheckMorbusOnko'];
				$tmp = swMorbus::onBeforeChangeDiag($this);
				if ($tmp !== true && isset($tmp['Alert_Msg'])) {
					$this->_saveResponse['ignoreParam'] = $tmp['ignoreParam'];
					$this->_saveResponse['Alert_Msg'] = $tmp['Alert_Msg'];
					throw new Exception('YesNo', 289);
				}
			}

			$data = array(
				'session' => $this->sessionParams,
				'EvnSection_id' => $this->evnSectionPriemId,
				'EvnSection_pid' => $this->id,
				'EvnSection_setDate' => $this->setDate,
				'EvnSection_setTime' => $this->setTime,
				'Lpu_id' => $this->Lpu_id,
				'MedStaffFact_id'=>$this->MedStaffFact_pid,
				'Server_id' => $this->Server_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'LpuSection_id' => $this->LpuSection_pid,
				'Diag_id' => $this->Diag_pid,
				'DeseaseType_id' => $this->DeseaseType_id,
				'TumorStage_id' => $this->TumorStage_id,
				'EvnSection_IsZNO' => $this->IsZNO,
				'Diag_spid' => $this->diag_spid,
				'PayType_id' => $this->PayType_id,
				'MedPersonal_id' => $this->MedPersonal_pid,
				'LeaveType_prmid' => $this->_params['LeaveType_prmid'],
				'UslugaComplex_id' => $this->_params['UslugaComplex_id'],
				'LeaveType_fedid' => $this->_params['LeaveType_fedid'],
				'ResultDeseaseType_fedid' => $this->_params['ResultDeseaseType_fedid'],
				'LpuSectionProfile_id' => $this->_params['LpuSectionProfile_id'],
				'LpuSectionBedProfile_id' => $this->_params['LpuSectionBedProfile_id'],
				'EvnSection_IndexRep' => $this->_params['EvnPS_IndexRep'],
				'EvnSection_IndexRepInReg' => $this->_params['EvnPS_IndexRepInReg'],
			);
			if (isset($this->outcomeDate)) {
				$data['EvnSection_disDate'] = $this->outcomeDate;
				$data['EvnSection_disTime'] = $this->outcomeTime;
			} else  {
				$data['EvnSection_disDate'] = null;
				$data['EvnSection_disTime'] = null;
			}
			$resp = $this->EvnSection_model->saveEvnSectionInPriem($data, false == $this->isNewRecord && !empty($this->listEvnSectionData));

			$this->_saveResponse['EvnSectionPriem_id'] = $resp[0]['EvnSection_id'];
		} else {
			// в последнюю очередь обновляем заболевание у приемного отделения
			parent::_afterSave($result);
		}
	}

	/**
	 * Создание/обновление/удаление движения в первом профильном отделении
	 */
	protected function _changeEvnSectionFirst($result)
	{
		switch (true) {
			case (!empty($this->_params['addEvnSection'])
				&& isset($this->LpuSection_id)
				//&& empty($this->LpuSection_pid)
			):
				/*
				* Создание пустого движения в том случае, если приходит флаг "addEvnSection"
				* Когда КВС добавляется из ЭМК по нажатию кнопки "добавить новый случай"
				* Когда КВС добавляется из АРМа стационара по нажатию кнопки "добавить пациента" (swEvnPSEditWindow.form_mode == 'arm_stac_add_patient')
				* Для Уфы когда КВС добавляется из Журнала госпитализаций (swEvnPSEditWindow.form_mode == 'dj_hosp')
				*/
				$this->load->model('EvnSection_model', 'evnSectionFirst3');
				$response = $this->evnSectionFirst3->doSave(array(
					'EvnSection_pid' => $this->id,
					'EvnSection_setDate' => $this->setDate,
					'EvnSection_setTime' => $this->setTime,
					'Lpu_id' => $this->Lpu_id,
					'Server_id' => $this->Server_id,
					'Person_id' => $this->Person_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'LpuSection_id' => $this->LpuSection_id,
					'MedPersonal_id' => $this->_params['MedPersonal_id'],
					'MedStaffFact_id' => (!empty($this->_params['MedStaffFact_id']) ? $this->_params['MedStaffFact_id'] : $this->MedStaffFact_pid),
					'LpuSectionProfile_id' => $this->_params['LpuSectionProfile_id'],
					'LpuSectionBedProfile_id' => $this->_params['LpuSectionBedProfile_id'],
					'UslugaComplex_id' => $this->_params['UslugaComplex_id'],
					'EvnSection_IsAdultEscort' => 1,
					'vizit_direction_control_check' => empty($this->_params['vizit_direction_control_check'])?0:$this->_params['vizit_direction_control_check'],
					'session' => $this->sessionParams,
					'scenario' => self::SCENARIO_AUTO_CREATE,
				), false);
				if ( !empty($response['Error_Msg']) ) {
					if( !empty($response['Alert_Msg']) ) {
						$this->_saveResponse['Alert_Msg'] = $response['Alert_Msg'];
					}
					throw new Exception($response['Error_Msg'], (!empty($response['Error_Code']) ? $response['Error_Code'] : 0));
				}
				$this->_setEvnSectionData($this->evnSectionFirst3, 'add');
				break;
			case (empty($this->evnSectionFirst) && isset($this->evnSectionFirstId)
				&& isset($this->LpuSection_pid) && $this->evnSectionNoChild
			):
				//если отделение в Исходе пребывания в приемном отделении установлено на пустое значение, то удалять движение.
				$this->load->model('EvnSection_model', 'EvnSection_model');
				$response = $this->EvnSection_model->doDelete(array(
					'EvnSection_id' => $this->evnSectionFirstId,
					'isExecCommonChecksOnDelete' => true,
					'session' => $this->sessionParams,
				), false);
				if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg'], (!empty($response['Error_Code']) ? $response['Error_Code'] : 0));
				}
				$this->_setEvnSectionData($this->EvnSection_model, 'del');
				break;
			case (isset($this->evnSectionFirst) && $this->evnSectionFirst->isNewRecord
				&& isset($this->LpuSection_pid) && isset($this->LpuSection_eid) && $this->evnSectionNoChild
			):
				//если выбрано отделение в Исходе пребывания в приемном отделении, то создавать движение на данное отделение
				$this->evnSectionFirst->setParent($this);
				$this->evnSectionFirst->setAttribute('LpuSectionWard_id', $this->LpuSectionWard_id);
				$this->evnSectionFirst->setAttribute('LpuSectionBedProfileLink_fedid', $this->_params['LpuSectionBedProfileLink_id']);
				
				$response = $this->evnSectionFirst->doSave(array(
					'vizit_direction_control_check' => empty($this->_params['vizit_direction_control_check'])?0:$this->_params['vizit_direction_control_check'],
					'session' => $this->sessionParams,
					'scenario' => self::SCENARIO_AUTO_CREATE,
				), false);
				if ( !empty($response['Error_Msg']) ) {
					if ( !empty($response['Alert_Msg']) ) {
						$this->_saveResponse['Alert_Msg'] = $response['Alert_Msg'];
					}
					if ( !empty($response['Error_Code']) ) {
						throw new Exception($response['Error_Msg'], $response['Error_Code']);
					}
					throw new Exception($response['Error_Msg']);
				}
				$this->_setEvnSectionData($this->evnSectionFirst, 'add');
				break;
			case (isset($this->evnSectionFirst) && isset($this->LpuSection_eid)
				&& false == $this->evnSectionFirst->isNewRecord && $this->evnSectionNoChild
			):
				$this->evnSectionFirst->setAttributes(array(
					// изменение времени движения при изменение его в Исходе пребывания в приемном отделении (refs #19567)
					'EvnSection_setDate' => $this->outcomeDate,
					'EvnSection_setTime' => $this->outcomeTime,
					// если отделение перевыбрано, то менять отделение
					'LpuSection_id' => $this->LpuSection_eid,
					'LpuSectionWard_id' => $this->LpuSectionWard_id,
					'LpuSectionBedProfileLink_fedid' => $this->_params['LpuSectionBedProfileLink_id']
				));
				$this->evnSectionFirst->setScenario(self::SCENARIO_SET_ATTRIBUTE);
				$response = $this->evnSectionFirst->doSave(array(), false);
				if ( !empty($response['Error_Msg']) ) {
					if( !empty($response['Alert_Msg']) ) {
						$this->_saveResponse['Alert_Msg'] = $response['Alert_Msg'];
					}
					throw new Exception($response['Error_Msg'], (!empty($response['Error_Code']) ? $response['Error_Code'] : 0));
				}
				$this->_setEvnSectionData($this->evnSectionFirst, 'upd');
				break;
		}
	}

	/**
	 * Создание вызова врача по патронажу при выписке новорожденного
	 */
	function _saveHomeVisit($result) {
		if ($this->regionNick == 'kz' &&
			$this->_params['childPS'] &&
			$this->leaveTypeSysNick == 'leave'
		) {
			$this->load->model('HomeVisit_model');

			$count = $this->getFirstResultFromQuery("
				select count(*) as \"cnt\"
				from v_HomeVisit 
				where Person_id = :Person_id 
				and HomeVisitCallType_id = 4 
				and COALESCE(HomeVisitStatus_id,1) in (1,3,4,6)
			", array('Person_id' => $this->Person_id));
			if ($count === false) {
				throw new Exception('Ошибка при проверке существования вызова по патронажу');
			}
			if ($count == 0) {
				$setDate = date_create($this->disDate)->modify('+1 day');
				$now = date_create(date('Y-m-d'));
				switch($setDate->format('D')) {
					case 'Sat': $setDate->modify('+2 day');break;
					case 'Sun': $setDate->modify('+1 day');break;
				}
				if ($setDate < $now) {
					$setDate = $now;
				}

				$query = "
					with mv as (
						select
							'2017-03-31' as dt
					)

					select
						Mother.Person_Phone as \"Person_Phone\",
						A.Address_Address as \"Address_Address\",
						A.KLRgn_id as \"KLRgn_id\",
						A.KLSubRgn_id as \"KLSubRgn_id\",
						A.KLCity_id as \"KLCity_id\",
						A.KLTown_id as \"KLTown_id\",
						A.KLStreet_id as \"KLStreet_id\",
						A.Address_House as \"Address_House\",
						A.Address_Flat as \"Address_Flat\",
						MotherAttach.Lpu_id as \"LpuAttach_id\",
						COALESCE(MotherAttach.LpuRegion_id, 1) as \"LpuRegion_id\",
						MSF.MedPersonal_id as \"MedPersonal_id\",
						MSF.MedStaffFact_id as \"MedStaffFact_id\",
						L.Lpu_Nick as \"Lpu_Nick\",
						COALESCE(L.Lpu_Phone, 'не указан') as \"Lpu_Phone\"
					from 
						v_PersonNewBorn PNB 
						left join v_BirthSpecStac BSS  on BSS.BirthSpecStac_id = PNB.BirthSpecStac_id
						left join v_PersonRegister PR  on PR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState Mother  on Mother.Person_id = PR.Person_id
						left join v_Address A  on A.Address_id = COALESCE(Mother.PAddress_id, Mother.UAddress_id)
						LEFT JOIN LATERAL(
							select
								PC.PersonCard_id,
								PC.Lpu_id,
								PC.LpuRegion_id
							from v_PersonCard PC 
							inner join v_Lpu L  on L.Lpu_id = PC.Lpu_id
							inner join v_LpuRegionType LRT  on LRT.LpuRegionType_id = PC.LpuRegionType_id
							where PC.LpuAttachType_id = 1
							and lower(LRT.LpuRegionType_SysNick) like lower('op')
							and PC.Person_id = Mother.Person_id
							and (select dt from mv) between PC.PersonCard_begDate and COALESCE(PC.PersonCard_endDate, (select dt from mv))
							limit 1
						) MotherAttach ON true
						LEFT JOIN LATERAL(
							select
								MSR.MedPersonal_id,
								MSR.MedStaffFact_id
							from v_MedStaffRegion MSR 
							where MSR.LpuRegion_id = MotherAttach.LpuRegion_id
							order by MSR.MedStaffRegion_isMain desc
							limit 1
						) MSF ON true
						left join v_Lpu L  on L.Lpu_id = :Lpu_id
					where 
						PNB.Person_id = :Person_id
						and MotherAttach.PersonCard_id is not null
					limit 1
				";
				$info = $this->getFirstRowFromQuery($query, array(
					'Person_id' => $this->Person_id,
					'Lpu_id' => $this->Lpu_id,
					'date' => $setDate
				), true);
				if ($info === false) {
					throw new Exception('Ошибка при получении данных для создания вызова по патронажу');
				}
				if (!empty($info)) {
					$HomeVisit_Num = null;
					$resp = $this->HomeVisit_model->getHomeVisitNum(array(
						'Lpu_id' => $info['LpuAttach_id'],
						'pmUser_id' => $this->promedUserId,
						'onDate' => $setDate,
						'Numerator_id' => null
					));
					if (!is_array($resp)) {
						throw new Exception('Ошибка при получении номера посещения по патронажу');
					}
					if (isset($resp['Error_Code']) && $resp['Error_Code'] != 'numerator404') {
						$HomeVisit_Num = null;
					} else if (!empty($resp['Error_Msg'])) {
						throw new Exception($resp['Error_Msg']);
					} else {
						$HomeVisit_Num = $resp['Numerator_Num'];
					}

					$HomeVisit_Comment = "МО передающая актив: {$info['Lpu_Nick']}, Телефон: {$info['Lpu_Phone']}.";

					$days_after_leave = date_diff($setDate, $this->disDT)->days;
					if ($days_after_leave > 3) {
						$period = new DatePeriod($setDate, new DateInterval('P1D'), $this->disDT);
						foreach($period as $dt) {
							$weekday = $dt->format('D');
							if ($weekday == 'Sat' && $weekday == 'Sun') {
								$days_after_leave--;
							}
						}
					}
					if ($days_after_leave > 3) {
						$HomeVisit_Comment .= " После выписки новорожденного прошло более 3-х рабочих дней";
					}

					$resp = $this->HomeVisit_model->addHomeVisit(array(
						'HomeVisit_id' => null,
						'CallProfType_id' => 1,
						'HomeVisitCallType_id' => 4,
						'Address_Address' => $info['Address_Address'],
						'KLRgn_id' => $info['KLRgn_id'],
						'KLSubRgn_id' => $info['KLSubRgn_id'],
						'KLCity_id' => $info['KLCity_id'],
						'KLTown_id' => $info['KLTown_id'],
						'KLStreet_id' => $info['KLStreet_id'],
						'Address_House' => $info['Address_House'],
						'Address_Flat' => $info['Address_Flat'],
						'Person_id' => $this->Person_id,
						'HomeVisit_setDate' => $setDate->format('Y-m-d'),
						'HomeVisit_setTime' => date('H:i:00'),
						'HomeVisit_Num' => $HomeVisit_Num,
						'Lpu_id' => $info['LpuAttach_id'],
						'LpuRegion_cid' => $info['LpuRegion_id'],
						'MedPersonal_id' => $info['MedPersonal_id'],
						'MedStaffFact_id' => $info['MedStaffFact_id'],
						'HomeVisit_Phone' => $info['Person_Phone'],
						'HomeVisitWhoCall_id' => 4,
						'HomeVisitStatus_id' => 1,
						'HomeVisit_Comment' => $HomeVisit_Comment,
						'pmUser_id' => $this->promedUserId,
					));
					if (!is_array($resp)) {
						throw new Exception('Ошибка при создании выпзова по патронажу');
					}
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$this->_saveResponse['HomeVisit_id'] = $resp[0]['HomeVisit_id'];
				}
			}
		}
	}

	/**
	 * Функция отказа/отмены отказа в госпитализации
	 */
	function saveEvnPSWithPrehospWaifRefuseCause($data)
	{
		try {
			$this->applyData(array(
				'session' => $data['session'],
				'scenario' => self::SCENARIO_SET_ATTRIBUTE,
				'EvnPS_id' => $data['EvnPS_id'],
				'PrehospWaifRefuseCause_id' => $data['PrehospWaifRefuseCause_id'],
				'EvnPS_IsTransfCall' => $data['EvnPS_IsTransfCall'],
				'LeaveType_prmid' => $data['LeaveType_prmid'],
				'ResultClass_id' => $data['ResultClass_id'],
				'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'LeaveType_fedid' => $data['LeaveType_fedid'],
				'ResultDeseaseType_fedid' => $data['ResultDeseaseType_fedid'],
			));
			if (empty($this->PrehospWaifRefuseCause_id)) {
				$this->setAttribute('lpusection_eid', null);
				$this->setAttribute('prehospwaifrefusecause_id', null);
				$this->setAttribute('resultclass_id', null);
				$this->setAttribute('resultdeseasetype_id', null);
				$this->setAttribute('istransfcall', null);
				$this->setAttribute('prehospwaifrefusedt', null);
				$this->setAttribute('outcomedt', null);
				$this->setAttribute('outcomedate', null);
				$this->setAttribute('outcometime', null);
				$this->setAttribute('disdt', null);
				$this->setAttribute('disdate', null);
				$this->setAttribute('distime', null);
				$this->setAttribute('deseasetype_id', null);//#157736
				$this->setAttribute('prehospstatus_id', 3);
			}
			$this->beginTransaction();
			$this->_updatePrehospStatus();
			$result = $this->_save();
			$this->_saveEvnSectionPriem($result);

			if ($this->EvnDirection_id) {
				$this->load->model('EvnDirectionAll_model');
				if (empty($this->PrehospWaifRefuseCause_id)) {
					// откатываем статус “Отклонено”
					$this->EvnDirectionAll_model->setParams(array(
						'session' => $this->sessionParams,
					));
					$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
					if (13 == $this->EvnDirectionAll_model->EvnStatus_id) {
						$this->EvnDirectionAll_model->setStatus(array(
							'Evn_id' => $this->EvnDirection_id,
							'EvnStatus_SysNick' => $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE,
							'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
							'pmUser_id' => $this->promedUserId,
						));
					}
				} else {
					// переводим в статус “Отклонено”
					switch ($this->PrehospWaifRefuseCause_id) {
						case 1: $EvnStatusCause_id = 7; break; // Отсутствие мест - Нет мест для госпитализации
						case 2: $EvnStatusCause_id = 1; break; // Отказ больного - Отказ пациента
						case 3: $EvnStatusCause_id = 6; break; // Нет экстренных показаний для госпитализации - Нет показаний для госпитализации
						case 4: $EvnStatusCause_id = 3; break; // Направление не обосновано - Ошибочное направление
						case 5: $EvnStatusCause_id = 12; break; // Направление не по профилю - Диагноз не соответствует профилю стационара
						case 6: $EvnStatusCause_id = 13; break; // Карантин в отделении - Эпидпоказания
						case 7: $EvnStatusCause_id = 13; break; // Больной контактный по инфекционному заболеванию - Эпидпоказания
						case 8: $EvnStatusCause_id = 18; break; // Уход пациента - Неявка пациента
						case 9: $EvnStatusCause_id = 22; break; // Непредоставление необходимого пакета документов - Непредоставление необходимого пакета документов
						case 10: $EvnStatusCause_id = 5; break; // Констатация факта смерти
						default: $EvnStatusCause_id = null; break;
					}
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $this->EvnDirection_id,
						'EvnStatusCause_id' => $EvnStatusCause_id,
						'EvnStatusHistory_Cause' => null,
						'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_REJECTED,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $this->promedUserId,
					));
				}
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return $this->_saveResponse;
		}
		// уведомления направившему МО
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * Проверка возможности госпитализации по направлению
	 */
	function checkDirHospitalize($data)
	{
		$ed_data = $this->getFirstRowFromQuery('
			select
				EvnDirection_IsConfirmed as "EvnDirection_IsConfirmed",
				v_EvnPs.EvnPs_id as "EvnPs_id"
			from v_EvnDirection 
				left join v_EvnPs  on v_EvnPs.EvnDirection_id = v_EvnDirection.EvnDirection_id
			where v_EvnDirection.EvnDirection_id = :EvnDirection_id', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
		));
		if (empty($ed_data)) {
			$this->_saveResponse['Error_Msg'] = 'Госпитализировать можно только по электронному направлению!';
			return $this->_saveResponse;
		}
		if (2 != $ed_data['EvnDirection_IsConfirmed']) {
			$this->_saveResponse['Error_Msg'] = 'Госпитализировать по направлению можно, если госпитализация подтверждена врачом отделения по профилю направления или руководителем ЛПУ.';
			return $this->_saveResponse;
		}
		if (!empty($ed_data['EvnPs_id'])) {
			$this->_saveResponse['Error_Msg'] = 'Пациент уже госпитализирован по этому направлению!';
			return $this->_saveResponse;
		}
		return $this->_saveResponse;
	}

	/**
	 * Функция сохранения исхода пребывания в приемном отделении
	 */
	function saveEvnPSWithLeavePriem($data)
	{
		try {
			$this->applyData(array(
				'session' => $data['session'],
				'scenario' => self::SCENARIO_SET_ATTRIBUTE,
				'EvnPS_id' => $data['EvnPS_id'],
				'LeaveType_prmid' => $data['LeaveType_prmid'],
				'LpuSection_eid' => $data['LpuSection_id'],
				'PrehospWaifRefuseCause_id' => $data['PrehospWaifRefuseCause_id'],
				'MedicalCareFormType_id' => $data['MedicalCareFormType_id'],
				'EvnPS_IsTransfCall' => $data['EvnPS_IsTransfCall'],
				'ResultClass_id' => $data['ResultClass_id'],
				'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'LeaveType_fedid' => $data['LeaveType_fedid'],
				'ResultDeseaseType_fedid' => $data['ResultDeseaseType_fedid'],
				'DeseaseType_id' => $data['DeseaseType_id'],//#157736
				/*'Diag_pid' => $data['Diag_id'],*/
			));
			$this->beginTransaction();
			$this->_updatePrehospStatus();
			$this->_checkChangeDiagPid();
			$result = $this->_save();
			$this->_changeEvnSectionFirst($result);
			$this->_saveEvnSectionPriem($result);

			if (!empty($this->EvnDirection_id)) {
				$this->load->model('EvnDirectionAll_model');
				switch ($this->PrehospWaifRefuseCause_id) {
					case 1: $EvnStatusCause_id = 7; break; // Отсутствие мест - Нет мест для госпитализации
					case 2: $EvnStatusCause_id = 1; break; // Отказ больного - Отказ пациента
					case 3: $EvnStatusCause_id = 6; break; // Нет экстренных показаний для госпитализации - Нет показаний для госпитализации
					case 4: $EvnStatusCause_id = 3; break; // Направление не обосновано - Ошибочное направление
					case 5: $EvnStatusCause_id = 12; break; // Направление не по профилю - Диагноз не соответствует профилю стационара
					case 6: $EvnStatusCause_id = 13; break; // Карантин в отделении - Эпидпоказания
					case 7: $EvnStatusCause_id = 13; break; // Больной контактный по инфекционному заболеванию - Эпидпоказания
					case 8: $EvnStatusCause_id = 18; break; // Уход пациента - Неявка пациента
					case 9: $EvnStatusCause_id = 22; break; // Непредоставление необходимого пакета документов - Непредоставление необходимого пакета документов
					case 10: $EvnStatusCause_id = 5; break; // Констатация факта смерти
					default: $EvnStatusCause_id = null; break;
				}
				$needSetStatus = null;
				// При смене исхода пребывания в приемном с госпитализации на отказ и наоборот, должен меняться статус направления (Отклонено/Обслужено).

				$this->EvnDirectionAll_model->setParams(array(
					'session' => $this->sessionParams,
				));
				$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
				if ($this->_isAttributeChanged('prehospwaifrefusecause_id')) {
					if ($EvnStatusCause_id > 0) {
						// установлен отказ
						if ($this->EvnDirectionAll_model->EvnStatus_id != 13) {
							$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_REJECTED;
						}
					} else {
						// отменен отказ
						if (13 == $this->EvnDirectionAll_model->EvnStatus_id && !$this->_isAttributeChanged('lpusection_eid')) {
							$needSetStatus = $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE;
						}
					}
				}
				if ($this->_isAttributeChanged('lpusection_eid')) {
					if ($this->LpuSection_eid > 0 || (!empty($this->_evnSectionLast) && $this->_evnSectionLast->IsPriem != 2)) {
						// установлена госпитализация
						if ($this->EvnDirectionAll_model->EvnStatus_id != 15) {
							$needSetStatus = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
						}
					} else {
						// отменена госпитализация
						if (15 == $this->EvnDirectionAll_model->EvnStatus_id && !$this->_isAttributeChanged('prehospwaifrefusecause_id')) {
							$needSetStatus = $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE;
						}
					}
				}
				if ($needSetStatus) {
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $this->EvnDirection_id,
						'EvnStatusCause_id' => $EvnStatusCause_id,
						'EvnStatusHistory_Cause' => null,
						'EvnStatus_SysNick' => $needSetStatus,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $this->promedUserId,
					));
				}
			}

			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return $this->_saveResponse;
		}
		// уведомления направившему МО
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * Получает список записей для справочного стола стационара
	 * Фильтры: ЛПУ, ФИО, ДР, номер КВС, отделение, период
	 */
	function loadWorkPlaceSprst($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$eps_filter = 'EvnPS.Lpu_id = :Lpu_id';

		if (!empty($data['EvnPS_NumCard']))
		{
			$eps_filter .= " AND EvnPS.EvnPS_NumCard = :EvnPS_NumCard";
			$params['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
		}
		if (!empty($data['LpuSection_id']))
		{
			$eps_filter .= " AND COALESCE(EvnPS.LpuSection_id,EvnPS.LpuSection_pid) = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (empty($data['beg_date']))
		{
			$data['beg_date'] = date('Y-m-d');
		}
		$params['beg_date'] = $data['beg_date'];
		$oth_filter = '((EPS.EvnSection_setDate = :beg_date) or ( EPS.EvnSection_setDate < :beg_date and (EPS.EvnPS_disDate is null or EPS.EvnPS_disDate = :beg_date) ))';

		if (!empty($data['end_date']) && $data['end_date'] == $data['beg_date'])
		{
			$data['end_date'] = null;
		}
		if (!empty($data['end_date']))
		{
			$params['end_date'] = $data['end_date'];
			$oth_filter = '((EPS.EvnSection_setDate between :beg_date and :end_date) or ( EPS.EvnSection_setDate < :beg_date and (EPS.EvnPS_disDate is null or EPS.EvnPS_disDate = :beg_date) ))';
		}

		if (!empty($data['Person_Surname']))
		{
			$oth_filter .= ' AND lower(PS.Person_Surname) LIKE lower(:Person_SurName)';
			$params['Person_SurName'] = rtrim($data['Person_Surname']).'%';
		}
		if (!empty($data['Person_Firname']))
		{
			$oth_filter .= ' AND lower(PS.Person_Firname) LIKE lower(:Person_FirName)';
			$params['Person_FirName'] = rtrim($data['Person_Firname']).'%';
		}
		if (!empty($data['Person_Secname']))
		{
			$oth_filter .= ' AND lower(PS.Person_Secname) LIKE lower(:Person_SecName)';
			$params['Person_SecName'] = rtrim($data['Person_Secname']).'%';
		}
		if (!empty($data['Person_Birthday']))
		{
			$oth_filter .= ' AND PS.Person_BirthDay = cast(:Person_BirthDay as timestamp)';
			$params['Person_BirthDay'] = $data['Person_Birthday'];
		}

		$sql = "
			select
				-- select
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Person_id as \"Person_id\",
				EPS.PersonEvn_id as \"PersonEvn_id\",
				EPS.Server_id as \"Server_id\",
				RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\",
				RTRIM(PS.Person_SurName) as \"Person_Surname\",
				RTRIM(PS.Person_FirName) as \"Person_Firname\",
				RTRIM(PS.Person_SecName) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				to_char(EPS.EvnSection_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				COALESCE(LS.LpuSection_Name, '') as \"LpuSection_Name\",
				case when LpuUnitType.LpuUnitType_SysNick = 'stac' 
					then datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные 
					else (datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные 
				end as \"EvnPS_KoikoDni\", 
				CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				dbfpayt.PayType_Name as \"PayType_Name\", --Вид оплаты
				CASE 
					WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
					WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN 'Отказ: ' ||  pwrc.PrehospWaifRefuseCause_Name
					ELSE ''
				END as \"LeaveType_Name\",
				MPRec.Person_Fio as \"MP_Fio\"	
			-- end select
			from
				-- from
				(
					select
						EvnPS.EvnPS_id
						,EvnPS.Person_id
						,EvnPS.PersonEvn_id
						,EvnPS.Server_id
						,EvnPS.EvnPS_NumCard
						,EvnPS.EvnPS_setDate
						,EvnPS.LpuSection_id
						,EvnPS.LpuSection_pid
						,EvnPS.PayType_id
						,EvnPS.PrehospWaifRefuseCause_id
						,CASE
							WHEN EvnPS.PrehospWaifRefuseCause_id > 0 
							THEN EvnPS.EvnPS_setDate
							WHEN LastES.EvnSection_disDate is not null
							THEN LastES.EvnSection_disDate
							ELSE EvnPS.EvnPS_disDate
						END as EvnPS_disDate
						,CASE WHEN LastES.EvnSection_setDate is not null
							THEN LastES.EvnSection_setDate
							ELSE EvnPS.EvnPS_setDate
						END as EvnSection_setDate
						,LastES.LeaveType_id
						,LastES.MedPersonal_id
					from v_EvnPS EvnPS 
						left join v_EvnSection LastES  on LastES.EvnSection_pid = EvnPS.EvnPS_id and LastES.EvnSection_Index = LastES.EvnSection_Count-1 and LastES.Lpu_id = :Lpu_id
					where {$eps_filter}
				) EPS
				inner join v_PersonState PS  on EPS.Person_id = PS.Person_id
				left join LpuSection LS  on LS.LpuSection_id = COALESCE(EPS.LpuSection_id,EPS.LpuSection_pid)
				left join PayType dbfpayt  on dbfpayt.PayType_id = EPS.PayType_id 
				left join LeaveType LT  on LT.LeaveType_id = EPS.LeaveType_id
				left join v_PrehospWaifRefuseCause pwrc  on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
				left join LpuUnit  on LpuUnit.LpuUnit_id = LS.LpuUnit_id 
				left join LpuUnitType  on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id
				left join v_MedPersonal MPRec
					on MPRec.MedPersonal_id = EPS.MedPersonal_id and MPRec.Lpu_id = :Lpu_id
				-- end from
			where
				-- where
				{$oth_filter}
				-- end where
			order by
				-- order by
				EPS.EvnSection_setDate
				-- end order by
		";
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$result = $this->db->query($sql,$params);
		$response = array();
		if ( is_object($result) ) {
			$res = $result->result('array');
			$response['data'] = $res;
			$response['totalCount'] = count($res);
			return $response;
		}
		else
			return false;
	}

	/**
	*
	* Получает список записей для АРМа приемного:
	* Только для Башкирии #191293
	* - электронные направления не из очереди, с записью на койку или без, в т.ч. экстренные
	* - записи на койку (стац.бирки) в т.ч. экстренные бирки по СММП и бирки без эл.направлений
	* - КВС самостоятельно обратившихся (принятых не по направлению, не по бирке) и принятых из очереди
	* - записи из очереди с эл. направлением или без
	* Группы Не поступал, Находится в приемном, Госпитализирован, Отказ формировать на установленную дату
	* Не поступал - "план госпитализаций", пациенты ожидающие госпитализации на установленную дату
	* Находится в приемном - история болезни создана установленным днем, но исход из приемного другим днем.
	* Госпитализирован - исход из приемного - Госпитализирован на установленную дату
	* Отказ - исход из приемного - Отказ на установленную дату.
	* Очередь и план госпитализаций показывать на установленную дату
	* #191293 - переделка на временные таблицы, в рамках оптимизации
	*/
	function loadWorkPlacePriemUfa($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['date'] = $data['date'];// установленная дата
		//$params['begDT'] = date('Y-m-d H:m',(time()-86400)).':00.000';// сутки от текущего времени
		//$params['begDT2'] = date('Y-m-d H:m',(time()-172800)).':00.000';// 2 суток от текущего времени
		$params['LpuSection_id'] = $data['LpuSection_id']; // приемное отделение
		// фильтр на направления с бирками со статусом не поступал
		$filter_dir = array();
		// фильтр на направления из очереди со статусом не поступал
		$filter_eq = '';
		// фильтр на не экстренные бирки без эл.направлений со статусом не поступал
		$filter_tts = '';
		$filter = '';
		$join = '';

		$isSearchByEncryp = false;
		$select_person_data_tab1 = "
						convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						isnull(PS.Person_SurName,'НЕИЗВЕСТЕН') +' '+ isnull(PS.Person_FirName,'НЕИЗВЕСТЕН') +' '+ isnull(PS.Person_SecName,'НЕИЗВЕСТЕН') as Person_Fio,";

		$select_person_data_tab2 = "
						PS.Person_Birthday as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						PS.Person_Fio";

		$select_person_data_eq = "
						convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						PS.Person_Fio,";

		$select_person_data_evnps = "
						convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						PS.Person_Fio,";
		$select_person_encryp_data = "null as PersonEncrypHIV_Encryp,";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$join .= " left join v_PersonEncrypHIV peh  on peh.Person_id = ps.Person_id";
				$select_person_encryp_data = "peh.PersonEncrypHIV_Encryp,";
				$selectPersonData = "
					,case when peh.PersonEncrypHIV_Encryp is null 
						then ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') 
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio
					,null as Person_BirthDay
					,null as Person_Phone
					,null as Address_Address";
				$select_person_data_tab1 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else convert(varchar(10), PS.Person_Birthday, 104) end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then isnull(PS.Person_SurName,'Не идентифицирован') +' '+ isnull(PS.Person_FirName,'') +' '+ isnull(PS.Person_SecName,'') else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";

				$select_person_data_tab2 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else PS.Person_Birthday end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio";

				$select_person_data_eq = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else convert(varchar(10), PS.Person_Birthday, 104) end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";

				$select_person_data_evnps = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else convert(varchar(10), PS.Person_Birthday, 104) end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then convert(varchar,dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))+''
						else convert(varchar,dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))+' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filter .= " and not exists(
					select peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh 
					inner join v_EncrypHIVTerr eht  on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
					limit 1
				)";
			}
		}

		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and peh.PersonEncrypHIV_Encryp like :Person_SurName";
			} else {
				$filter .= " AND PS.Person_Surname LIKE :Person_SurName";
			}
			$params['Person_SurName'] = rtrim($data['Person_SurName']).'%';
		}
		
		if (!empty($data['Person_FirName'])) 
		{
			$filter .= " AND PS.Person_Firname LIKE :Person_FirName";
			$params['Person_FirName'] = rtrim($data['Person_FirName']).'%';
		}
		if (!empty($data['Person_SecName'])) 
		{
			$filter .= " AND PS.Person_Secname LIKE :Person_SecName";
			$params['Person_SecName'] = rtrim($data['Person_SecName']).'%';
		}
		if (!empty($data['Person_BirthDay'])) 
		{
			$filter .= " AND cast(PS.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		if (!empty($data['PrehospStatus_id'])) 
		{
			$filter .= " AND EST.PrehospStatus_id = :PrehospStatus_id";
			$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
		}
		
		if (empty($data['EvnDirectionShow_id'])) 
		{
			// На установленную дату
			$filter_dir [] = " AND TTS.TimetableStac_setDate = :date";
			//$filter_dir [] = " AND ED.EvnDirection_setDT = :date";
			$filter_tts .= " AND cast(TimetableStac_setDate as date) = :date";
		}
		// иначе Все направления (отображать направления с бирками без признака отмены и без связки с КВС, но не зависимо от даты бирки)
		
		$filter_isConfirmed = '';
		if (!empty($data['EvnDirection_isConfirmed'])) {
			$filter_isConfirmed = " AND ISNULL(ED.EvnDirection_isConfirmed, 1) = :EvnDirection_isConfirmed";
			$params['EvnDirection_isConfirmed'] = $data['EvnDirection_isConfirmed'];
		}
		// иначе отобразить все

		$union_queue="
		union all
		select
			'EvnQueue_'+ convert(varchar, EQ.EvnQueue_id) as keyNote,
			convert(varchar(8), EQ.EvnQueue_setDT, 112) as sortDate,
			EST.PrehospStatus_id as groupField,
			EQ.EvnDirection_id as EvnDirection_id,
			v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
			ED.EvnDirection_IsConfirmed,
			ED.EvnDirection_Num as EvnDirection_Num,
			convert(varchar(10),Evn.Evn_setDT,104) as EvnDirection_setDate,
			ED.Diag_id as Diag_did,
			ED.LpuSection_id as LpuSection_did,
			L.Lpu_id as Lpu_did,
			L.Org_id as Org_did,
			ED.DirType_id as DirType_id,
			isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(Diag.Diag_Code,'')+'.'+ isnull(Diag.Diag_Name,'') as Direction_exists,
			null as TimetableStac_setDate,
			null as SMMP_exists,
			null as EvnPS_CodeConv,
			null as EvnPS_NumConv,
			null as TimetableStac_insDT,
			--convert(varchar(10), EQ.EvnQueue_setDT, 104) as EvnQueue_setDate,
			EQ.EvnQueue_setDT as EvnQueue_setDate,
			EQ.EvnQueue_id,
			1 as IsHospitalized,
			null as EvnPS_id,
			'' as EvnPS_setDT,
			null as EvnPS_NumCard,
			EQ.TimetableStac_id as TimetableStac_id,
			LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
			EQ.LpuSectionProfile_did as LpuSectionProfile_did,
			EST.PrehospStatus_id as PrehospStatus_id,
			EST.PrehospStatus_Name as PrehospStatus_Name,
			Diag.Diag_id,
			RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
			puc.PMUser_Name as pmUser_Name,
			1 as IsRefusal,
			null as PrehospWaifRefuseCause_id,
			null as MedStaffFact_pid,
			1 as IsCall,
			1 as IsSmmp,
			null as PrehospArrive_id,
			null as PrehospArrive_SysNick,
			null as PrehospType_id,
			null as PrehospType_SysNick,
			{$select_person_data_eq}
			{$select_person_encryp_data}
			EQ.Person_id,
			EQ.PersonEvn_id,
			EQ.Server_id,
			null as childElement,
			LS.LpuSection_Name,
			null as EvnPL_id,
			null as EvnPL_NumCard,
			EPS.CmpCallCard_id,
			null as EvnSection_id,
			case when exists(
				select *
				from v_PersonQuarantine PQ
				where PQ.Person_id = EQ.Person_id
				and PQ.PersonQuarantine_endDT is null
			) then 2 else 1 end as PersonQuarantine_IsOn,
			{$this->getScaleFields()}
			'' as EvnPS_OutcomeDT
		from EvnDirection ED 
		inner join Evn on Evn.Evn_id = ED.EvnDirection_id and Evn.Evn_deleted = 1
			cross apply (
				Select * from v_EvnQueue EQ  where EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_id = ED.EvnQueue_id limit 1 -- по идее последнее условие необязательно но делает стоимость плана меньше 
				) EQ
			left join v_LpuUnit LU  on EQ.LpuUnit_did = LU.LpuUnit_id
			left join v_Person_all PS  on EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id
			left join v_DirType DT  on ED.DirType_id = DT.DirType_id
			left join v_LpuSectionProfile LSP  on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
			left join v_Diag Diag  on isnull(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
			--left join v_PrehospStatus EST  on 1 = EST.PrehospStatus_id
			left join v_PrehospStatus EST  on EST.PrehospStatus_id =  CASE WHEN ED.EvnDirection_failDT IS NOT NULL AND Evn.EvnStatus_id = 13 THEN 5 ELSE 1 END
			left join pmUserCache puc  on EQ.pmUser_insID = puc.pmUser_id
			left join v_Lpu L  on L.Lpu_id = isnull(ED.Lpu_sid,Evn.Lpu_id)
			outer apply (
				Select EvnPS_id, CmpCallCard_id from v_EvnPS EPS with  where EPS.EvnDirection_id = EQ.EvnDirection_id limit 1 --and EPS.Lpu_id = L.Lpu_id
			) EPS
			left join v_LpuSection LS  on LS.LpuSection_id = ED.LpuSection_did
			-- нет смысла брать согласие с КВС, при условии в фильтрах AND EPS.EvnPS_id is null
			left join v_PersonLpuInfo  on Evn.Lpu_id = v_PersonLpuInfo.Lpu_id and Evn.Person_id = v_PersonLpuInfo.Person_id
			{$this->getScaleJoins('EPS')}
			{$join}
		where
			(
				EQ.EvnQueue_failDT is NULL 
				OR 
				Evn.EvnStatus_id = 13 AND @curdate = CAST(ED.EvnDirection_failDT AS DATE) --показывать в группе ОТКАЗ со статусом отклонено
			)
			AND EQ.EvnQueue_recDT is null
			AND Evn.EvnStatus_id not in (8, 15, 17)
			AND LU.Lpu_id = :Lpu_id
			AND ED.Lpu_did = :Lpu_id -- по идее очередь куда поставили и направление куда направили, должны быть в одну МО
			AND LU.LpuUnitType_id in (1,9,6)
			AND (EQ.EvnDirection_id is null OR EQ.EvnQueue_id is not null)
			AND ED.DirType_id IN (1,2,5,4,6)
			AND EPS.EvnPS_id is null
			{$filter}
			{$filter_isConfirmed}
			".$this->genLpuSectionServiceFilter('EvnQueue', $data['isAll']);

		if (empty($data['EvnQueueShow_id'])) 
		{
			// не показывать очередь
			$union_queue="";
		}
		else if($data['EvnQueueShow_id'] == 1)
		{
			// показать очередь, кроме записй из архива
			$union_queue.= "AND isnull(EQ.EvnQueue_IsArchived,1) = 1";
		}

		$pre_tab2 = "
			select
				'EvnDirection_'+ convert(varchar, ED.EvnDirection_id) as keyNote,
				TTS.TimetableStac_setDate, 
				ES.EvnStatus_SysNick,
				ED.EvnDirection_setDT,
				ED.EvnDirection_id as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				ED.EvnDirection_IsConfirmed,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_did,
				ED.DirType_id as DirType_id,
				case when TTS.TimetableType_id = 6 then
						isnull(TTSLS.LpuSectionProfile_Name,'')
						+', '+ isnull(TtsDiag.Diag_Code,'') +'.'+ isnull(TtsDiag.Diag_Name,'')
						+', Бригада №'+ isnull(ET.EmergencyTeam_Num,'')
					else
						null
					end as SMMP_exists,
				null as EvnPS_CodeConv,
				null as EvnPS_NumConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				null as EvnQueue_id,
				ED.TimetableStac_id as TimetableStac_id,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				null as childElement, 
				ED.LpuSectionProfile_id,
				ED.Diag_id,
				TTS.LpuSection_id,
				TTS.pmUser_updId,
				ED.pmUser_insID,
				isnull(ED.Lpu_sid,ED.Lpu_id) as Lpu_id,
				CCCTT.CmpCallCard_id,
				null as EvnSection_id,
				{$select_person_encryp_data}
				{$select_person_data_tab2}
			from
				v_EvnDirection_all ED 
				left join v_EvnStatus ES with  on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_TimetableStac_lite TTS  on ED.TimetableStac_id = TTS.TimetableStac_id
				left join v_LpuSection TTSLS  on TTS.LpuSection_id = TTSLS.LpuSection_id
				left join v_CmpCloseCardTimeTable CCCTT  on CCCTT.TimetableStac_id = TTS.TimetableStac_id
				left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_Diag TtsDiag  on TtsDiag.Diag_id = CCC.Diag_gid
				left join v_Person_all PS  on ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id
				left join v_PrehospStatus EST  on 1 = EST.PrehospStatus_id
				left join v_LpuUnit LU  on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_PersonLpuInfo  on ED.Lpu_id = v_PersonLpuInfo.Lpu_id and PS.Person_id = v_PersonLpuInfo.Person_id
				{$join}
			where
				ED.Lpu_did = :Lpu_id
				AND ED.EvnQueue_id is null
				AND ( ED.DirType_id is not null AND ED.DirType_id in (1, 2, 4, 5, 6) )
				AND isnull(LU.LpuUnitType_SysNick,'stac')!='polka'				
				AND (
					(ISNULL(ES.EvnStatus_SysNick, '') not in ('Canceled', 'Serviced', 'Declined') AND ED.DirFailType_id is NULL)
					OR
					(ISNULL(ES.EvnStatus_SysNick, '') in ('Declined') AND ED.DirFailType_id is NOT NULL)
				)
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnDirection', $data['isAll'])."
		";

		$tab2 = "";
		if (!empty($filter_dir)) {
			foreach($filter_dir as $filter_dir1) {
				if (!empty($tab2)) {
					$tab2 .= "
						union all
					";
				}
				$tab2 .= $pre_tab2 . " " . $filter_dir1;
			}
		} else {
			$tab2 = $pre_tab2;
		}

		$tab1="
			SELECT
				'TimetableStac_'+ convert(varchar, TTS.TimetableStac_id) as keyNote,
				isnull(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate) as sortDate,
				EST.PrehospStatus_id as groupField,
				null as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				null as EvnDirection_IsConfirmed,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				EvnPS.EvnDirection_setDT as EvnDirection_setDate,
				coalesce(EvnPS.Diag_did,CCC.Diag_gid) as Diag_did,
				EvnPS.LpuSection_did as LpuSection_did,
				EvnPS.Lpu_did,
				EvnPS.Org_did,
				null as DirType_id,
				null as Direction_exists,
				TTS.TimetableStac_setDate,
				case when TTS.TimetableType_id = 6 then
					isnull(TTSLS.LpuSectionProfile_Name,'')
					+', '+ isnull(TtsDiag.Diag_Code,'') +'.'+ isnull(TtsDiag.Diag_Name,'')
					+', Бригада №'+ isnull(ET.EmergencyTeam_Num,'')
				else
					null
				end as SMMP_exists,
				isnull(CCC.CmpCallCard_Ngod,'') as EvnPS_CodeConv,
				isnull(ET.EmergencyTeam_Num,'') as EvnPS_NumConv,
				TTS.TimetableStac_updDT as TimetableStac_insDT,
				null as EvnQueue_setDate,
				null as EvnQueue_id,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				EvnPS.EvnPS_setDT,
				EvnPS.EvnPS_NumCard,
				TTS.TimetableStac_id as TimetableStac_id,
				TTSLS.LpuSectionProfile_Name as LpuSectionProfile_Name,
				TTSLS.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				Diag.Diag_id,
				RTRIM(coalesce(Diag.Diag_Code,TtsDiag.Diag_Code)) + '. ' + RTRIM(coalesce(Diag.Diag_Name,TtsDiag.Diag_Name)) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall,
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp,
				PA.PrehospArrive_id,
				PA.PrehospArrive_SysNick,
				PT.PrehospType_id,
				PT.PrehospType_SysNick,
				{$select_person_data_tab1}
				{$select_person_encryp_data}
				PS.Person_id,
				isnull(EvnPS.PersonEvn_id,PS.PersonEvn_id) as PersonEvn_id,
				isnull(EvnPS.Server_id,PS.Server_id) as Server_id,
				TTSLS.LpuSection_Name,
				TTS.TimetableType_id,
				null as EmergencyDataStatus_id,
				TTS.Person_id as Person_ttsid,
				EvnPS.LpuSection_pid,
				EvnPS.MedStaffFact_pid,
				EvnPS.EvnPS_OutcomeDT,
				EvnPS.LpuSection_eid,
				CCC.CmpCallCard_id,
				null as EvnSection_id,
				{$this->getScaleFields()}
				EPL.EvnPL_id,
				EPL.EvnPL_NumCard
			from v_TimetableStac_lite TTS 
				outer apply (
					select 1 as EvnDirection_IsConfirmed
				) ED
				left join v_EvnPS EvnPS  on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection TTSLS  on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
				left join v_CmpCloseCardTimeTable CCCTT  on CCCTT.TimetableStac_id = TTS.TimetableStac_id
				left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_PersonState PS  on TTS.Person_id = PS.Person_id
				left join v_Diag Diag  on EvnPS.Diag_pid = Diag.Diag_id
				left join v_Diag TtsDiag  on TtsDiag.Diag_id = CCC.Diag_gid
				left join v_PrehospStatus EST  on isnull(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
				left join pmUserCache puc  on TTS.pmUser_updId = puc.pmUser_id					
				left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
				left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id
				left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id		
				left join v_EvnDirection EDr  on EDr.EvnDirection_id = TTS.EvnDirection_id
				{$this->getScaleJoins('CCC')}
				{$join}
			where
				TTSLS.Lpu_id = :Lpu_id
				AND TTS.Person_id is not null -- убрал некорректное отображение пустых бирок
				AND PS.Person_IsUnknown = 2
				AND EDr.EvnDirection_id is null -- здесь только без направлений
				AND EST.PrehospStatus_id = 1 -- со статусом не поступал
				{$filter_tts} -- если такой фильтр есть, то запрос можно сразу ограничить
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('TimetableStac', $data['isAll']);
		
		$tab1_declare="
			CREATE TABLE #tab1 
			(
				tmp_tab1_id bigint NOT NULL IDENTITY(1, 1),
				keyNote varchar (44) COLLATE Cyrillic_General_CI_AS NULL,
				sortDate datetime NULL,
				groupField varchar (200) COLLATE Cyrillic_General_CI_AS NULL,
				EvnDirection_id bigint,
				PersonLpuInfo_IsAgree bigint NULL,
				EvnDirection_IsConfirmed bigint,
				EvnDirection_Num varchar (20) COLLATE Cyrillic_General_CI_AS NULL,
				EvnDirection_setDate datetime NULL,
				Diag_did bigint NULL,
				LpuSection_did bigint NULL,
				Lpu_did bigint NULL,
				Org_did bigint NULL,
				DirType_id bigint,
				Direction_exists varchar (200) COLLATE Cyrillic_General_CI_AS NULL,
				TimetableStac_setDate datetime NULL,
				SMMP_exists varchar (840) COLLATE Cyrillic_General_CI_AS NULL,
				EvnPS_CodeConv varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
				EvnPS_NumConv varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
				TimetableStac_insDT datetime NULL,
				EvnQueue_setDate datetime,
				EvnQueue_id bigint,
				IsHospitalized bigint,
				EvnPS_id bigint NULL,
				EvnPS_setDT datetime NULL,
				EvnPS_NumCard varchar (50) COLLATE Cyrillic_General_CI_AS NULL,
				TimetableStac_id bigint NULL,
				LpuSectionProfile_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				LpuSectionProfile_did bigint NULL,
				PrehospStatus_id bigint NULL,
				PrehospStatus_Name varchar (50) COLLATE Cyrillic_General_CI_AS NULL,
				Diag_id bigint NULL,
				Diag_CodeName varchar (312) COLLATE Cyrillic_General_CI_AS NULL,
				pmUser_Name varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
				IsRefusal bigint,
				PrehospWaifRefuseCause_id bigint NULL,
				IsCall bigint NULL,
				IsSmmp bigint NULL,
				PrehospArrive_id bigint NULL,
				PrehospArrive_SysNick varchar (20) COLLATE Cyrillic_General_CI_AS NULL,
				PrehospType_id bigint NULL,
				PrehospType_SysNick varchar (20) COLLATE Cyrillic_General_CI_AS NULL,
				Person_BirthDay varchar (10) COLLATE Cyrillic_General_CI_AS NULL,
				Person_age varchar (35) COLLATE Cyrillic_General_CI_AS NULL,
				Person_Fio nvarchar (92) COLLATE Cyrillic_General_CI_AS NULL,
				PersonEncrypHIV_Encryp varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
				Person_id bigint NULL,
				PersonEvn_id bigint NULL,
				Server_id bigint NULL,
				LpuSection_Name varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
				TimetableType_id bigint NULL,
				EmergencyDataStatus_id bigint,
				Person_ttsid bigint NULL,
				LpuSection_pid bigint NULL,
				MedStaffFact_pid bigint NULL,
				EvnPS_OutcomeDT datetime NULL,
				LpuSection_eid bigint NULL,
				CmpCallCard_id bigint NULL,
				EvnSection_id bigint,
				FaceAsymetry_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				HandHold_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				SqueezingBrush_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				ScaleLams_id bigint NULL,
				ScaleLams_Value bigint NULL,
				PainResponse_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				ExternalRespirationType_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				SystolicBloodPressure_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				InternalBleedingSigns_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				LimbsSeparation_Name varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				PrehospTraumaScale_id bigint NULL,
				PrehospTraumaScale_Value varchar (200) COLLATE Cyrillic_General_CI_AS NULL,
				PainDT varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				ECGDT varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				ResultECG varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				TLTDT varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				FailTLT varchar (500) COLLATE Cyrillic_General_CI_AS NULL,
				EvnPL_id bigint NULL,
				EvnPL_NumCard nvarchar (30) COLLATE Cyrillic_General_CI_AS NULL
			);
			
			ALTER TABLE #tab1 ADD PRIMARY KEY ([tmp_tab1_id]) ON [PRIMARY];
			
			INSERT INTO #tab1 {$tab1};";

		$tab2_declare="
		CREATE TABLE #tab2(
			tmp_tab2_id bigint NOT NULL IDENTITY(1, 1),
			keyNote varchar (43) COLLATE Cyrillic_General_CI_AS NULL,
			TimetableStac_setDate datetime NULL,
			EvnStatus_SysNick varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
			EvnDirection_setDT datetime NULL,
			EvnDirection_id bigint NULL,
			PersonLpuInfo_IsAgree bigint NULL,
			EvnDirection_IsConfirmed bigint NULL,
			EvnDirection_Num varchar (20) COLLATE Cyrillic_General_CI_AS NULL,
			EvnDirection_setDate varchar (10) COLLATE Cyrillic_General_CI_AS NULL,
			Diag_did bigint NULL,
			LpuSection_did bigint NULL,
			Lpu_did bigint NULL,
			DirType_id bigint NULL,
			SMMP_exists varchar (840) COLLATE Cyrillic_General_CI_AS NULL,
			EvnPS_CodeConv varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
			EvnPS_NumConv varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
			TimetableStac_insDT datetime,
			EvnQueue_setDate datetime,
			EvnQueue_id bigint,
			TimetableStac_id bigint NULL,
			LpuSectionProfile_did bigint NULL,
			Person_id bigint NULL,
			PersonEvn_id bigint NULL,
			Server_id bigint NULL,
			childElement varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
			LpuSectionProfile_id bigint NULL,
			Diag_id bigint NULL,
			LpuSection_id bigint NULL,
			pmUser_updId bigint NULL,
			pmUser_insID bigint NULL,
			Lpu_id bigint NULL,
			CmpCallCard_id bigint NULL,
			EvnSection_id bigint,
			PersonEncrypHIV_Encryp varchar (100) COLLATE Cyrillic_General_CI_AS NULL,
			Person_BirthDay datetime NULL,
			Person_age varchar (35) COLLATE Cyrillic_General_CI_AS NULL,
			Person_Fio nvarchar (92) COLLATE Cyrillic_General_CI_AS NULL
		);
	
		ALTER TABLE #tab2 ADD PRIMARY KEY ([tmp_tab2_id]) ON [PRIMARY];
	
		INSERT INTO #tab2 {$tab2};";
		// https://redmine.swan.perm.ru/issues/39660
		// Запрос оптимизирован - выборка по TimetableStac вынесена в with, далее вместо 5 OR реализовано в виде 5 union all
		$sql = "
			Declare
				@curdate datetime = :date;
			
			begin

			SET NOCOUNT ON; 
			IF OBJECT_ID(N'tempdb..#tab1', N'U') IS NOT NULL
				DROP TABLE #tab1;
			IF OBJECT_ID(N'tempdb..#tab2', N'U') IS NOT NULL
				DROP TABLE #tab2;
			{$tab1_declare}
			{$tab2_declare}

			SET NOCOUNT OFF;

			select
				'EvnDirection_'+ convert(varchar, ED.EvnDirection_id) as keyNote,
				convert(varchar(8), coalesce (EvnPS.EvnPS_setDate, ED.TimetableStac_setDate, ED.EvnDirection_setDT), 112) as sortDate,
				--1 as groupField,
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as groupField,
				ED.EvnDirection_id as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				ED.EvnDirection_IsConfirmed,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_did,
				L.Lpu_id as Lpu_did,
				L.Org_id as Org_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(DiagD.Diag_Code,'')+'.'+ isnull(DiagD.Diag_Name,'') as Direction_exists,
				convert(varchar(10), ED.TimetableStac_setDate, 104) as TimetableStac_setDate,
				SMMP_exists,
				null as EvnPS_CodeConv,
				null as EvnPS_NumConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				null as EvnQueue_id,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				EvnPS.EvnPS_NumCard,
				ED.TimetableStac_id as TimetableStac_id,
				LSP.LpuSectionProfile_Name,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				--1 as PrehospStatus_id,
				--'Не поступал' as PrehospStatus_Name,
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as PrehospStatus_id,
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 'Отказ' ELSE 'Не поступал' END as PrehospStatus_Name,
				Diag.Diag_id,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name, --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				EvnPS.MedStaffFact_pid,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				PA.PrehospArrive_id,
				PA.PrehospArrive_SysNick,
				PT.PrehospType_id,
				PT.PrehospType_SysNick,
				convert(varchar(10), ED.Person_Birthday, 104) as Person_BirthDay,
				ED.Person_age,
				ED.Person_Fio,
				ED.PersonEncrypHIV_Encryp,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				null as childElement,
				LS.LpuSection_Name,
				EPL.EvnPL_id,
				EPL.EvnPL_NumCard,
				ED.CmpCallCard_id,
				null as EvnSection_id,
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = ED.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				{$this->getScaleFields()}
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) + ' ' + convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeDT
			from
				#tab2 ED 
				left join v_EvnPS EvnPS  on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT  on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP  on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD  on ED.Diag_id= DiagD.Diag_id
				left join v_LpuSection LS  on COALESCE(EvnPS.LpuSection_eid,ED.LpuSection_id,ED.LpuSection_did) = LS.LpuSection_id
				left join v_Diag Diag  on isnull(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join pmUserCache puc  on isnull (ED.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_Lpu L  on L.Lpu_id = ED.Lpu_id				
				left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
				left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id
				left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				{$this->getScaleJoins('ED')}
			where
				(1=1) and IsNull(EvnPS.PrehospStatus_id,1) in (1)
				{$filter_isConfirmed}
				
			{$union_queue}

			union all

			select
				keyNote,
				convert(varchar(8), sortDate, 112) as sortDate,
				groupField,
				EvnDirection_id,
				PersonLpuInfo_IsAgree,
				EvnDirection_IsConfirmed,
				EvnDirection_Num,
				convert(varchar(10),EvnDirection_setDate,104) as EvnDirection_setDate,
				Diag_did,
				LpuSection_did,
				Lpu_did,
				Org_did,
				DirType_id,
				Direction_exists,
				convert(varchar(10), TimetableStac_setDate, 104) as TimetableStac_setDate,
				SMMP_exists,
				EvnPS_CodeConv,
				EvnPS_NumConv,
				convert(varchar(10), TimetableStac_insDT, 104) + ' ' + convert(varchar(5), TimetableStac_insDT, 108) as TimetableStac_insDT,
				EvnQueue_setDate,
				null as EvnQueue_id,
				IsHospitalized,
				EvnPS_id,
				 convert(varchar(10), EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS_setDT, 108) as EvnPS_setDT,
				EvnPS_NumCard,
				TimetableStac_id,
				LpuSectionProfile_Name,
				LpuSectionProfile_did,
				PrehospStatus_id,
				PrehospStatus_Name,
				Diag_id,
				Diag_CodeName,
				pmUser_Name,
				IsRefusal,
				PrehospWaifRefuseCause_id,
				MedStaffFact_pid,
				IsCall,
				IsSmmp,
				PrehospArrive_id,
				PrehospArrive_SysNick,
				PrehospType_id,
				PrehospType_SysNick,
				convert(varchar(10), Person_Birthday, 104) as Person_BirthDay,
				Person_age,
				Person_Fio,
				PersonEncrypHIV_Encryp,
				Person_id,
				PersonEvn_id,
				Server_id,
				null as childElement,
				LpuSection_Name,
				EvnPL_id,
				EvnPL_NumCard,
				CmpCallCard_id,
				null as EvnSection_id,
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				{$this->getScaleFields(false)}
				convert(varchar(10), EvnPS_OutcomeDT, 104) + ' ' + convert(varchar(5), EvnPS_OutcomeDT, 108) as EvnPS_OutcomeDT
			from #tab1 
			where
				-- экстр. бирки только с EmergencyDataStatus Койка забронирована на установленную дату
				-- /*AND EmergencyDataStatus_id = 1 */ - описание в комите к #51610
				(TimetableType_id = 6 AND cast(TimetableStac_setDate as date) = @curdate /*AND EmergencyDataStatus_id = 1 */)

			union all

			select
				keyNote,
				convert(varchar(8), sortDate, 112) as sortDate,
				groupField,
				EvnDirection_id,
				PersonLpuInfo_IsAgree,
				EvnDirection_IsConfirmed,
				EvnDirection_Num,
				convert(varchar(10),EvnDirection_setDate,104) as EvnDirection_setDate,
				Diag_did,
				LpuSection_did,
				Lpu_did,
				Org_did,
				DirType_id,
				Direction_exists,
				convert(varchar(10), TimetableStac_setDate, 104) as TimetableStac_setDate,
				SMMP_exists,
				EvnPS_CodeConv,
				EvnPS_NumConv,
				convert(varchar(10), TimetableStac_insDT, 104) + ' ' + convert(varchar(5), TimetableStac_insDT, 108) as TimetableStac_insDT,
				EvnQueue_setDate,
				EvnQueue_id,
				IsHospitalized,
				EvnPS_id,
				 convert(varchar(10), EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS_setDT, 108) as EvnPS_setDT,
				EvnPS_NumCard,
				TimetableStac_id,
				LpuSectionProfile_Name,
				LpuSectionProfile_did,
				PrehospStatus_id,
				PrehospStatus_Name,
				Diag_id,
				Diag_CodeName,
				pmUser_Name,
				IsRefusal,
				PrehospWaifRefuseCause_id,
				MedStaffFact_pid,
				IsCall,
				IsSmmp,
				PrehospArrive_id,
				PrehospArrive_SysNick,
				PrehospType_id,
				PrehospType_SysNick,
				convert(varchar(10), Person_Birthday, 104) as Person_BirthDay,
				Person_age,
				Person_Fio,
				PersonEncrypHIV_Encryp,
				Person_id,
				PersonEvn_id,
				Server_id,
				null as childElement,
				LpuSection_Name,
				EvnPL_id,
				EvnPL_NumCard,
				CmpCallCard_id,
				null as EvnSection_id,
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				{$this->getScaleFields(false)}
				convert(varchar(10), EvnPS_OutcomeDT, 104) + ' ' + convert(varchar(5), EvnPS_OutcomeDT, 108) as EvnPS_OutcomeDT
			from #tab1 
			where
				-- в зависимости от фильтра: все или на установленную дату
				(TimetableType_id != 6 AND Person_ttsid is not null {$filter_tts})

			union all

			select
				'EvnPS_'+ convert(varchar, EvnPS.EvnPS_id) as keyNote,
				convert(varchar(8), EvnPS.EvnPS_setDate, 112) as sortDate,
				case 
					when EvnPS.EvnPS_setDate <= @curdate and datediff(day, EvnPS.EvnPS_setDate, @curdate) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, dateadd(day, 1, @curdate)) > @curdate then 3
					when (cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND EvnPS.LpuSection_eid IS NOT NULL) then 4
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND lt.LeaveType_Code = '603' then 2" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 5
					when ED.EvnStatus_id = 13 AND ED.EvnDirection_failDT IS NOT NULL AND @curdate = CAST(ED.EvnDirection_failDT AS DATE) then 5
				end as groupField,
				ED.EvnDirection_id as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				ED.EvnDirection_IsConfirmed,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),EvnPS.EvnDirection_setDT,104) as EvnDirection_setDate,
				EvnPS.Diag_did,
				EvnPS.LpuSection_did,
				EvnPS.Lpu_did,
				EvnPS.Org_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(DiagD.Diag_Code,'')+'.'+ isnull(DiagD.Diag_Name,'') as Direction_exists,
				convert(varchar(10), TTS.TimetableStac_setDate, 104) as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as EvnPS_NumConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				null as EvnQueue_id,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				EvnPS_NumCard,
				null as TimetableStac_id,
				'' as LpuSectionProfile_Name,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				case 
					when EvnPS.EvnPS_setDate <= @curdate and datediff(day, EvnPS.EvnPS_setDate, @curdate) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, dateadd(day, 1, @curdate)) > @curdate then 'Находится в приемном'
					when cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND EvnPS.LpuSection_eid IS NOT NULL then 'Госпитализирован'
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND lt.LeaveType_Code = '603' then 'Принят'" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 'Отказ'
				end as PrehospStatus_Name,
				Diag.Diag_id,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,--Диагноз приемного; код, наименование
				puc.PMUser_Name as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? " and lt.LeaveType_Code = '602'" : "") . " then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				EvnPS.MedStaffFact_pid,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				PA.PrehospArrive_id,
				PA.PrehospArrive_SysNick,
				PT.PrehospType_id,
				PT.PrehospType_SysNick,
				{$select_person_data_evnps}
				{$select_person_encryp_data}
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				Child.ChildEvn_id as childElement,
				LS.LpuSection_Name,
				EPL.EvnPL_id,
				EPL.EvnPL_NumCard,
				EvnPS.CmpCallCard_id,
				EvnSection.EvnSection_id,
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = EvnPS.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				{$this->getScaleFields()}
				convert(varchar(10), EvnPS.EvnPS_OutcomeDT, 104) + ' ' + convert(varchar(5), EvnPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeDT
			from v_EvnPS EvnPS 
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT  on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP  on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD  on ED.Diag_id= DiagD.Diag_id
				left join v_Person_all PS  on EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id
				left join v_LpuSection LS  on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag  on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST  on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				--left join v_TimetableStac_lite TTS  on EvnPS.EvnPS_id = TTS.Evn_id
				outer apply (
					Select TimetableStac_setDate 
					from v_TimetableStac_lite TTS  
					where EvnPS.EvnPS_id = TTS.Evn_id and EvnPS.EvnDirection_id = TTS.EvnDirection_id
					limit 1
				) TTS
				left join pmUserCache puc  on EvnPS.pmUser_updID = puc.pmUser_id
				outer apply (
					select
						ES.EvnSection_id as ChildEvn_id,
						ES.EvnSection_setDate
						limit 1
					from
						v_EvnSection ES with 
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
						and ISNULL(ES.EvnSection_IsPriem, 1) = 1
				) Child
				outer apply (
					select
						ES.EvnSection_id
					from
						v_EvnSection ES with 
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
					limit 1
				) EvnSection
				" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "outer apply (
					select LeaveType_prmid
					from v_EvnSection with 
					where EvnSection_pid = EvnPS.EvnPS_id
						and EvnSection_IsPriem = 2
					limit 1
				) ESPriem
				left join v_LeaveType lt with  on lt.LeaveType_id = ESPriem.LeaveType_prmid" : "") . "				
				--left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
				--left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id	
				outer apply (
					Select EvnPL_id, EvnPL_NumCard from v_EvnLink EL  
					left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id	
					where EL.Evn_lid = EvnPS.EvnPS_id
					limit 1
				) EPL
				left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				{$this->getScaleJoins('EvnPS')}
				{$join}
			where
				EvnPS.Lpu_id = :Lpu_id
				AND EvnPS.LpuSection_pid = :LpuSection_id
				AND EvnPS.EvnPS_setDate between @curdate-2 and @curdate and isnull(EvnPS.EvnPS_OutcomeDT,@curdate)>=@CURDATE
				/*AND ((
					--со статусом Находится в приемном - история болезни создана установленным днем и дата исхода пустая или позже
					-- #57215
					EvnPS.EvnPS_setDate <= @curdate and datediff(day, EvnPS.EvnPS_setDate, @curdate) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, dateadd(day, 1, @curdate)) > @curdate
				)
				OR (
					--со статусом Отказ на установленную дату или со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = @curdate AND COALESCE(" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "ESPriem.LeaveType_prmid, " : "") . "EvnPS.PrehospWaifRefuseCause_id,  EvnPS.LpuSection_eid) IS NOT NULL)
				)
				OR (
					--со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = -date AND EvnPS.LpuSection_eid IS NOT NULL)
				)
				)*/
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnPS', $data['isAll'])."
			order by
				sortDate DESC
			
			end
			";

		//echo getDebugSql($sql, $params);exit;
		
		//print_r($sql);
		$res = $this->db->query($sql,$params);

		if ( is_object($res) ) {
			$res_array = $res->result('array');
			return $res_array;
		}
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkPrehospAcceptRefuseChangeAbility($data) {
		$outputData = array();

		$query = "
			select
				 COALESCE(EPS.Person_id, 0) as \"Person_id\" -- Идентификатор пациента
				,COALESCE(EPS.PrehospType_id, 0) as \"PrehospType_id\" -- Тип госпитализации 'Планово'
				,DATEDIFF('DAY', " . (!empty($data['EvnSection_id']) ? "ES.EvnSection_setDT" : "EPS.EvnPS_setDT") . ", dbo.tzGetDate()) as \"DaysDiff\" -- В течение 5 дней с даты госпитализации
				,COALESCE(EPS.Lpu_id, 0) as \"Lpu_id\" -- ЛПУ госпитализации
				,COALESCE(ED.EvnDirection_id, 0) as \"EvnDirection_id\" -- Идентификатор направления
				,COALESCE(ED.Lpu_sid,ED.Lpu_id) as \"Lpu_did\" -- ЛПУ направления
				,COALESCE(ED.MedPersonal_id, 0) as \"MedPersonal_did\" -- Направивший специалист
				,COALESCE(ZMP.MedPersonal_id, 0) as \"MedPersonal_zdid\" -- Заведующий отделением, в котором работает направивший специалист
			from
				v_EvnPS EPS 
				" . (!empty($data['EvnSection_id']) ? "inner join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_id = :EvnSection_id" : "") . "
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EPS.EvnDirection_id
				LEFT JOIN LATERAL (
					select MSF.MedPersonal_id
					from dbo.v_MedStaffFact MSF 
						inner join persis.Post P  on P.id = MSF.Post_id
					where MSF.LpuSection_id = ED.LpuSection_id
						and (COALESCE(MSF.WorkData_begDate, dbo.tzGetDate()) <= dbo.tzGetDate())
						and (COALESCE(MSF.WorkData_endDate, dbo.tzGetDate() + interval '1 day') > dbo.tzGetDate())
						and P.code = 6
					limit 1
				) ZMP ON true
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		$queryParams = array(
			'EvnPS_id' => $data['EvnPS_id']
		,'EvnSection_id' => $data['EvnSection_id']
		);

		// echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		$outputData = $response[0];

		// Получаем участковых врачей для пациента
		// Тип прикрепления: основное (???)
		$query = "
			select
				 MSR.MedPersonal_id as \"MedPersonal_id\"
				,PCA.Lpu_id as \"Lpu_id\"
			from
				v_PersonCard PCA 
				left join v_MedStaffRegion MSR  on MSR.LpuRegion_id = PCA.LpuRegion_id
			where
				PCA.Person_id = :Person_id
				and MSR.MedPersonal_id is not null
				and PCA.LpuAttachType_id = 1
		";

		$queryParams = array(
			'Person_id' => $outputData['Person_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) ) {
			return false;
		}

		$outputData['LpuRegionMedPersonalList'] = $response;

		return $outputData;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getMessageDataOnPrehospAcceptRefuse($data) {
		$query = "
			select
				 COALESCE(PS.Person_Surname, '') as \"Person_Surname\"
				,COALESCE(PS.Person_Firname, '') as \"Person_Firname\"
				,COALESCE(PS.Person_Secname, '') as \"Person_Secname\"
				,COALESCE(L.Lpu_Nick, '') as \"Lpu_Name\"
				,COALESCE(EPS.Lpu_id, 0) as \"Lpu_id\"
				,COALESCE(ES.MedPersonal_id, 0) as \"MedPersonal_id\"
			from
				v_EvnPS EPS 
				inner join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id
					and ES.EvnSection_Index = ES.EvnSection_Count - 1
				inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
				inner join v_Lpu L  on L.Lpu_id = :Lpu_id
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		$queryParams = array(
			'EvnPS_id' => $data['EvnPS_id']
		,'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Получение данных КВС для использования в ТАП (при заведении ТАП из отказа в приёмном)
	 */
	function getEvnPSInfoForEvnPL($data)
	{
		$resp = $this->queryResult("
			select
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Diag_pid as \"Diag_pid\",
				EPS.PrehospTrauma_id as \"PrehospTrauma_id\",
				EPS.Diag_eid as \"Diag_eid\",
				EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\",
				EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\",
				EPS.LpuSection_pid as \"LpuSection_pid\",
				EPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				ES.UslugaComplex_id as \"UslugaComplex_id\",
				'' as \"Error_Msg\"
			from
				v_EvnPS EPS 
				left join v_EvnSection ES  on EPS.EvnPS_id = ES.EvnSection_pid and ES.EvnSection_Index = 0
			where
				EPS.EvnPS_id = :EvnPS_id
		", array(
			'EvnPS_id' => $data['EvnPS_id']
		));

		if (!empty($resp[0]['EvnPS_id'])) {
			return $resp[0];
		}

		return array('Error_Msg' => 'Ошибка получения данных КВС');
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSViewData($data)
	{
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$accessType = 'EvnPS.Lpu_id = :Lpu_id';

		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and COALESCE(EvnPS.EvnPS_IsPaid, 1) = 1";
		}

		$withMedStaffFact_from = '';
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= " AND LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')";
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU  on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		} else {
			//если нет рабочего места врача, то доступ только на чтение
			$accessType .= ' AND 1 = 2';
		}

		$this->load->model('CureStandart_model');
		$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('D', 'PS.Person_BirthDay', 'COALESCE(EvnPS.EvnPS_setDT,dbo.tzGetDate())');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('D');

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				case when {$accessType} then 1 else 0 end as \"allowUnsign\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				EvnPS.Diag_id as \"Diag_id\",
				EvnPS.EvnPS_IsSigned as \"EvnPS_IsSigned\",
				COALESCE(D.Diag_Code,'') as \"Diag_Code\", -- основной диагноз последнего движения или приемного отделения
				COALESCE(D.Diag_Name,'') as \"Diag_Name\",
				EvnPS.Diag_did as \"Diag_did\",
				COALESCE(DD.Diag_Code,'') as \"Diag_d_Code\",
				COALESCE(DD.Diag_Name,'') as \"Diag_d_Name\",
				FM.CureStandart_Count as \"CureStandart_Count\",
				DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\",
				COALESCE(to_char(EvnPS.EvnPS_setDT, 'DD.MM.YYYY'),'') as \"EvnPS_setDate\",
				COALESCE(to_char(EvnPS.EvnPS_setDT, 'HH24:MI'),'') as \"EvnPS_setTime\",
				to_char(COALESCE(EL.EvnLeave_setDT,COALESCE(EvnPS.EvnPS_disDT,dbo.tzGetDate())), 'DD.MM.YYY') as \"EvnPS_disDate\",--дата и время  выписки из стационара, или текущие  дата и время, если данных о выписке нет
				to_char(COALESCE(EL.EvnLeave_setDT,COALESCE(EvnPS.EvnPS_disDT,dbo.tzGetDate())), 'HH24:MI') as \"EvnPS_disTime\",
				RTRIM(EvnPS.EvnPS_NumCard) as \"EvnPS_NumCard\",
				EvnPS.EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EvnPS.EvnPS_NumConv as \"EvnPS_NumConv\",
				EvnPS.EvnPS_IsCont as \"EvnPS_IsCont\",-- переведен
				coalesce(OP.Org_name,LSP.LpuSection_name,'') as \"Lpu_p_Name\",-- откуда переведен
				EvnPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\",
				EvnPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\",
				EvnPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\",
				EvnPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\",
				COALESCE(CAST(EvnPS.EvnDirection_Num AS varchar),'') as \"EvnDirection_Num\",
				COALESCE(to_char(EvnPS.EvnDirection_setDT, 'DD.MM.YYYY'),'') as \"EvnDirection_setDate\",
				coalesce(OD.Org_name,LSD.LpuSection_name,'') as \"Lpu_d_Name\", --кем выдано:  ЛПУ, Отделение Организация
				LT.LeaveType_Code as \"LeaveType_Code\",
				LT.LeaveType_Name as \"LeaveType_Name\",
				PT.PrehospType_Name as \"PrehospType_Name\",
				PT.PrehospType_SysNick as \"PrehospType_SysNick\",
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_Name as \"PrehospArrive_Name\",
				PA.PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				--EvnPS.LpuSection_id,--последнее отделение
				EvnPS.Person_id as \"Person_id\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				Child.ChildEvn_id as \"ChildEvn_id\",
				to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				(ecp.EvnCostPrint_Cost::numeric(10,2))::text as \"CostPrint\",
				ESLAST.EvnSection_id as \"EvnSection_id\",
				case
					when dp.Diag_Code IN ('U07.1', 'U07.2') then 3
					when dd.Diag_Code IN ('U07.1', 'U07.2') then 3
					when exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id 
						where
							edps.EvnDiagPS_rid = EvnPS.EvnPS_id
							and edps.DiagSetType_id in (1, 2, 3)
							and d.Diag_Code IN ('U07.1', 'U07.2')
						limit 1
					) then 3
					else RepositoryObserv.CovidType_id
				end as \"CovidType_id\"
			FROM
				v_EvnPS EvnPS 
				left join v_EvnCostPrint ecp  on ecp.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection LS  on LS.LpuSection_id = EvnPS.LpuSection_id
				left join v_LpuUnit LUT  on LUT.LpuUnit_id = LS.LpuUnit_id
				left join v_Diag DD  on EvnPS.Diag_did = DD.Diag_id
				left join v_Diag DP  on EvnPS.Diag_pid = DP.Diag_id
				left join v_LeaveType LT  on EvnPS.LeaveType_id = LT.LeaveType_id
				left join v_PrehospType PT  on EvnPS.PrehospType_id = PT.PrehospType_id
				left join v_PrehospArrive PA  on EvnPS.PrehospArrive_id = PA.PrehospArrive_id
				left join v_EvnLeave EL  on EvnPS.EvnPS_id = EL.EvnLeave_pid
				left join v_EvnSection ESLAST  on EvnPS.EvnPS_id = ESLAST.EvnSection_pid and EvnPS.LpuSection_id = ESLAST.LpuSection_id
				left join v_Diag D  on COALESCE(ESLAST.Diag_id,EvnPS.Diag_pid) = D.Diag_id
				left join v_Org OP  on EvnPS.EvnPS_IsCont = 2 AND EvnPS.Org_did = OP.Org_id
				left join v_LpuSection LSP  on EvnPS.EvnPS_IsCont = 2 AND EvnPS.LpuSection_did = LSP.LpuSection_id
				left join Org OD  on EvnPS.Org_did = OD.Org_id
				left join v_LpuSection LSD  on EvnPS.EvnDirection_id is not null AND EvnPS.LpuSection_did = LSD.LpuSection_id
				left join v_PersonState PS  on EvnPS.Person_id = PS.Person_id
				LEFT JOIN LATERAL (
					{$cureStandartCountQuery}
				) FM ON true
				LEFT JOIN LATERAL (
					{$diagFedMesFileNameQuery}
				) DFM ON true
				LEFT JOIN LATERAL (
					select
						Evn_id as ChildEvn_id
					from
						v_Evn E 
						inner join v_EvnSection ES  on E.Evn_pid = ES.EvnSection_id
					where
						ES.EvnSection_pid = :EvnPS_id
					limit 1
				) Child ON true
				LEFT JOIN LATERAL (
					select CovidType_id
					from v_RepositoryObserv
					where Evn_id = :EvnPS_id
					order by RepositoryObserv_updDT DESC
					limit 1
				) RepositoryObserv ON true
				{$withMedStaffFact_from}
			WHERE
				EvnPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		//echo getDebugSql($query, $params);exit;

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
            $resp = $result->result('array');

			$isEMDEnabled = $this->config->item('EMD_ENABLE');
            if (!empty($resp[0]['EvnPS_id']) && !empty($isEMDEnabled)) {
                $this->load->model('EMD_model');
                $signStatus = $this->EMD_model->getSignStatus([
                    'EMDRegistry_ObjectName' => 'EvnPS',
                    'EMDRegistry_ObjectIDs' => [$resp[0]['EvnPS_id']],
                    'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
                ]);

                $resp[0]['EvnPS_SignCount'] = 0;
                $resp[0]['EvnPS_MinSignCount'] = 0;
                if (!empty($resp[0]['EvnPS_id']) && $resp[0]['EvnPS_IsSigned'] == 2 && isset($signStatus[$resp[0]['EvnPS_id']])) {
                    $resp[0]['EvnPS_SignCount'] = $signStatus[$resp[0]['EvnPS_id']]['signcount'];
                    $resp[0]['EvnPS_MinSignCount'] = $signStatus[$resp[0]['EvnPS_id']]['minsigncount'];
                    $resp[0]['EvnPS_IsSigned'] = $signStatus[$resp[0]['EvnPS_id']]['signed'];
                }
            }

            if($this->getRegionNick() == 'khak') {
				if($resp[0]['LeaveType_Code'] == 1){
					$query_leave = "
								select
									ED.ResultDesease_id as \"ResultDesease_id\",
									RD.ResultDesease_Name as \"ResultDesease_Name\"
								from
									v_EvnDie ED 
									left join v_ResultDesease RD  on RD.ResultDesease_id = ED.ResultDesease_id
								where
									ED.EvnDie_pid = :EvnSection_id
								limit 1
							";
					$result = $this->db->query($query_leave, array(
						'EvnSection_id' => $resp[0]['EvnSection_id']
					));
					if (is_object($result)) {
						$resp_leave = $result->result('array');
						if (!empty($resp_leave[0]) && $resp_leave[0]['ResultDesease_id'] == 52) {
							$resp[0]['ResultDesease_id'] = $resp_leave[0]['ResultDesease_id'];
							$resp[0]['ResultDesease_Name'] = $resp_leave[0]['ResultDesease_Name'];
							$resp[0]['LeaveType_Code'] = 3;
							$resp[0]['LeaveType_Name'] = 'Смерть';
						}
					}
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Создание фильтров по обслуживаемым отделеням приемным
	 */
	function genLpuSectionServiceFilter($type, $isAll) {
		$filter = '';

		// #193920 убрать фильтр на несуществующие "LpuUnit_did"
		$filter_lpu_id = '';
		if(getRegionNick() == 'msk'){
			$filter_lpu_id = ' AND LU.Lpu_id = :Lpu_id AND LU.LpuUnitType_id in (1,9,6) ';
		}

		if (isset($isAll) && !$isAll) {
			switch ($type) {
				case 'TimetableStac':
					$filter = " AND TTSLS.LpuSection_id in (select LSS.LpuSection_did from v_LpuSectionService LSS  where LSS.LpuSection_id = :LpuSection_id)";
					break;
				case 'EvnDirection':
					$filter = " AND ED.LpuSection_did  in (select LSS.LpuSection_did from v_LpuSectionService LSS  where LSS.LpuSection_id = :LpuSection_id)";
					break;
				case 'EvnPS':
					// убрал этот фильтр, т.к. из-за него в АРМе приемного отображаются не все пациенты принятые врачом приемного
					// EvnPS.LpuSection_id - это отделение последнего движения,
					// по нему вообще неправильно фильтровать, если есть отказ или если находится в приемном
					//$filter = " AND EvnPS.LpuSection_id  in (select LSS.LpuSection_did from v_LpuSectionService LSS  where LSS.LpuSection_id = :LpuSection_id)";
					break;
				case 'EvnQueue':
					$filter = $filter_lpu_id."
						AND LSP.LpuSectionProfile_id in (
						select LpuSection.LpuSectionProfile_id as \"LpuSectionProfile_id\"
						from v_LpuSectionService LSS
						inner join v_LpuSection LpuSection on LpuSection.LpuSection_id = LSS.LpuSection_did
						where LSS.LpuSection_id = :LpuSection_id
					)";
					break;
			}
		}
		return $filter;
	}

	/**
	 * Получает список столбцов для шкал
	 */
	function getScaleFields($alias = true) {
		if(getRegionNick() != 'ufa') return '';

		/*return
			($alias?"SL.":"")."FaceAsymetry_Name as \"FaceAsymetry_Name\",".
			($alias?"SL.":"")."HandHold_Name as \"HandHold_Name\",".
			($alias?"SL.":"")."SqueezingBrush_Name as \"SqueezingBrush_Name\",".
			($alias?"SL.":"")."ScaleLams_id as \"ScaleLams_id\",".
			($alias?"SL.":"")."ScaleLams_Value as \"ScaleLams_Value\",".
			($alias?"PTS.":"")."PainResponse_Name as \"PainResponse_Name\",".
			($alias?"PTS.":"")."ExternalRespirationType_Name as \"ExternalRespirationType_Name\",".
			($alias?"PTS.":"")."SystolicBloodPressure_Name as \"SystolicBloodPressure_Name\",".
			($alias?"PTS.":"")."InternalBleedingSigns_Name as \"InternalBleedingSigns_Name\",".
			($alias?"PTS.":"")."LimbsSeparation_Name as \"LimbsSeparation_Name\",".
			($alias?"PTS.":"")."PrehospTraumaScale_id as \"PrehospTraumaScale_id\",".
			($alias?"PTS.":"")."PrehospTraumaScale_Value as \"PrehospTraumaScale_Value\",".
			($alias?"BRDO.":"")."PainDT as \"PainDT\",".
			($alias?"BRDO.":"")."ECGDT as \"ECGDT\",".
			($alias?"BRDO.":"")."ResultECG as \"ResultECG\",".
			($alias?"BRDO.":"")."TLTDT as \"TLTDT\",".
			($alias?"BRDO.":"")."FailTLT as \"FailTLT\",";
		*/
		return
			($alias?"SL.":"")."FaceAsymetry_Name as \"faceasymetry_name\",".
			($alias?"SL.":"")."HandHold_Name as \"handhold_name\",".
			($alias?"SL.":"")."SqueezingBrush_Name as \"squeezingbrush_name\",".
			($alias?"SL.":"")."ScaleLams_id as \"scalelams_id\",".
			($alias?"SL.":"")."ScaleLams_Value as \"scalelams_value\",".
			($alias?"PTS.":"")."PainResponse_Name as \"painresponse_name\",".
			($alias?"PTS.":"")."ExternalRespirationType_Name as \"externalrespirationtype_name\",".
			($alias?"PTS.":"")."SystolicBloodPressure_Name as \"systolicbloodpressure_name\",".
			($alias?"PTS.":"")."InternalBleedingSigns_Name as \"internalbleedingsigns_name\",".
			($alias?"PTS.":"")."LimbsSeparation_Name as \"limbsseparation_name\",".
			($alias?"PTS.":"")."PrehospTraumaScale_id as \"prehosptraumascale_id\",".
			($alias?"PTS.":"")."PrehospTraumaScale_Value as \"prehosptraumascale_value\",".
			($alias?"BRDO.":"")."PainDT as \"paindt\",".
			($alias?"BRDO.":"")."ECGDT as \"ecgdt\",".
			($alias?"BRDO.":"")."ResultECG as \"resultecg\",".
			($alias?"BRDO.":"")."TLTDT as \"tltdt\",".
			($alias?"BRDO.":"")."FailTLT as \"failtlt\",";
	}

	/**
	 * Получает строку для дополнения запроса в разделе join (шкалы)
	 */
	function getScaleJoins($alias) {
		if( getRegionNick() != 'ufa') return '';

		if(!empty($alias)) $alias = $alias.".";

		return "left join v_ScaleLams SL  on SL.CmpCallCard_id = {$alias}CmpCallCard_id
				left join v_PrehospTraumaScale PTS  on PTS.CmpCallCard_id = {$alias}CmpCallCard_id
				left join dbo.v_BSKRegistry BR on BR.CmpCallCard_id = {$alias}CmpCallCard_id
				left join v_BskRegistryDataOks BRDO  on BRDO.BskRegistry_id = BR.BskRegistry_id";
	}

	/**
	 * Загрузка данных по шкалам по ид карте вызова
	 */
	public function loadScalesByCmpCallCardId($data)
	{
		$params = [];
		$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		$query = "
			select
				{$this->getScaleFields()}
			from v_CmpCallCard CCC
			{$this->getScaleJoins('CCC')}
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		return $this->queryResult($query,$params);
	}

	/**
	 *
	 * Получает список записей для АРМа приемного:
	 * - электронные направления не из очереди, с записью на койку или без, в т.ч. экстренные
	 * - записи на койку (стац.бирки) в т.ч. экстренные бирки по СММП и бирки без эл.направлений
	 * - КВС самостоятельно обратившихся (принятых не по направлению, не по бирке) и принятых из очереди
	 * - записи из очереди с эл. направлением или без
	 * Группы Не поступал, Находится в приемном, Госпитализирован, Отказ формировать на установленную дату
	 * Не поступал - "план госпитализаций", пациенты ожидающие госпитализации на установленную дату
	 * Находится в приемном - история болезни создана установленным днем, но исход из приемного другим днем.
	 * Госпитализирован - исход из приемного - Госпитализирован на установленную дату
	 * Отказ - исход из приемного - Отказ на установленную дату.
	 * Очередь и план госпитализаций показывать на установленную дату
	 */
	function loadWorkPlacePriem($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['date'] = $data['date'];// установленная дата
		//$params['begDT'] = date('Y-m-d H:m',(time()-86400)).':00.000';// сутки от текущего времени
		//$params['begDT2'] = date('Y-m-d H:m',(time()-172800)).':00.000';// 2 суток от текущего времени
		$params['LpuSection_id'] = $data['LpuSection_id']; // приемное отделение
		// фильтр на направления с бирками со статусом не поступал
		$filter_dir = array();
		// фильтр на направления из очереди со статусом не поступал
		$filter_eq = '';
		// фильтр на не экстренные бирки без эл.направлений со статусом не поступал
		$filter_tts = '';
		$filter = '';
		$join = '';

		$isSearchByEncryp = false;
		$select_person_data_tab1 = "
						to_char(PS.Person_Birthday, 'DD.MM.YYYY') as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as Person_age,
						COALESCE(PS.Person_SurName,'НЕИЗВЕСТЕН')  || ' ' ||  COALESCE(PS.Person_FirName,'НЕИЗВЕСТЕН')  || ' ' ||  COALESCE(PS.Person_SecName,'НЕИЗВЕСТЕН') as Person_Fio,";

		$select_person_data_tab2 = "
						PS.Person_Birthday as Person_BirthDay,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as Person_age,
						PS.Person_Fio as Person_Fio";

		$select_person_data_eq = "
						to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_BirthDay\",
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as \"Person_age\",
						PS.Person_Fio as \"Person_Fio\",";

		$select_person_data_evnps = "
						to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_BirthDay\",
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as \"Person_age\",
						PS.Person_Fio as \"Person_Fio\",";
		$select_person_encryp_data = "null as \"PersonEncrypHIV_Encryp\",";
		$select_person_encryp_data_no_quote = "null as PersonEncrypHIV_Encryp,";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$join .= " left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id";
				$select_person_encryp_data = "peh.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",";
				$select_person_encryp_data_no_quote = "peh.PersonEncrypHIV_Encryp,";
				$select_person_data_tab1 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'DD.MM.YYYY') end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then COALESCE(PS.Person_SurName,'Не идентифицирован')  || ' ' ||  COALESCE(PS.Person_FirName,'')  || ' ' ||  COALESCE(PS.Person_SecName,'') else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";

				$select_person_data_tab2 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else PS.Person_Birthday end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio";

				$select_person_data_eq = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'DD.MM.YYYY') end as \"Person_BirthDay\",
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as \"Person_age\",
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Fio\",";

				$select_person_data_evnps = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'DD.MM.YYYY') end as \"Person_BirthDay\",
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then (dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()))::text || ''
						else (dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()))::text || ' мес.' end as \"Person_age\",
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Fio\",";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filter .= " and not exists(
					select peh.PersonEncrypHIV_Encryp 
					from v_PersonEncrypHIV peh 
					inner join v_EncrypHIVTerr eht  on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and COALESCE(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
					limit 1
				)";
			}
		}

		// пока используется только на Казахстане и Уфе, для других регионов заглушка, чтобы не сломались запросы
		$isBDZ = 'null as Person_IsBDZ,';
		if (in_array(getRegionNick(), ['kz', 'ufa'])) {
			$isBDZ = "case
				when PS.Person_IsInFOMS = 1 then 'orange'
				when PS.Person_IsInFOMS = 2 then 'true'
				else 'false'
			end as Person_IsBDZ,";
		}

		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and lower(peh.PersonEncrypHIV_Encryp) like lower(:Person_SurName)";
			} else {
				$filter .= " AND lower(PS.Person_Surname) LIKE lower(:Person_SurName)";
			}
			$params['Person_SurName'] = rtrim($data['Person_SurName']).'%';
		}

		if (!empty($data['Person_FirName']))
		{
			$filter .= " AND lower(PS.Person_Firname) LIKE lower(:Person_FirName)";
			$params['Person_FirName'] = rtrim($data['Person_FirName']).'%';
		}
		if (!empty($data['Person_SecName']))
		{
			$filter .= " AND lower(PS.Person_Secname) LIKE lower(:Person_SecName)";
			$params['Person_SecName'] = rtrim($data['Person_SecName']).'%';
		}
		if (!empty($data['Person_BirthDay']))
		{
			$filter .= " AND cast(PS.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if (!empty($data['PrehospStatus_id']))
		{
			$filter .= " AND EST.PrehospStatus_id = :PrehospStatus_id";
			$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
		}

		if (empty($data['EvnDirectionShow_id']))
		{
			// На установленную дату
			$filter_dir [] = " AND cast(TTS.TimetableStac_setDate as date) = :date";
			//$filter_dir [] = " AND ED.EvnDirection_setDT = :date";
			$filter_tts .= " AND cast(TimetableStac_setDate as date) = :date";
		}
		
		
		if (getRegionNick()=='msk' && !empty($data['PSNumCard']))
		{
			$filter .= " AND EvnPS.EvnPS_NumCard = :PSNumCard";
			$params['PSNumCard'] = $data['PSNumCard'];
		}
		
		// иначе Все направления (отображать направления с бирками без признака отмены и без связки с КВС, но не зависимо от даты бирки)

		if (empty($data['EvnQueueShow_id']))
		{
			// не показывать очередь
			$filter_eq .= "(1=2) AND ";
		}
		else if($data['EvnQueueShow_id'] == 1)
		{
			// показать очередь, кроме записй из архива
			$filter_eq .= "COALESCE(EQ.EvnQueue_IsArchived,1) = 1 AND";
		}

		$filter_isConfirmed = '';
		if (!empty($data['EvnDirection_isConfirmed'])) {
			$filter_isConfirmed = " AND COALESCE(ED.EvnDirection_isConfirmed, 1) = :EvnDirection_isConfirmed";
			$params['EvnDirection_isConfirmed'] = $data['EvnDirection_isConfirmed'];
		}

		// #193920 убрать фильтр на несуществующие "LpuUnit_did"
		$filter_lpu_id = '';
		if(getRegionNick() != 'msk'){
			$filter_lpu_id = ' AND LU.Lpu_id = :Lpu_id AND LU.LpuUnitType_id in (1,9,6) ';
		}
		// иначе отобразить все


		$pre_tab2 = "
			select
				'EvnDirection_' ||  ED.EvnDirection_id::text as keyNote,
				TTS.TimetableStac_setDate as TimetableStac_setDate,
				ES.EvnStatus_SysNick as EvnStatus_SysNick,
				ED.EvnDirection_setDT as EvnDirection_setDT,
				ED.EvnDirection_id as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				ED.EvnDirection_IsConfirmed as EvnDirection_IsConfirmed,
				ED.EvnDirection_Num as EvnDirection_Num,
				to_char(cast(ED.EvnDirection_setDate as date), 'DD.MM.YYYY') as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				TTSLSd.LpuSection_Name as LpuSection_dName,
				ED.Lpu_did,
				LPUd.Lpu_Name as Lpu_dName,
				ED.DirType_id as DirType_id,
				case when TTS.TimetableType_id = 6 then
						COALESCE(TTSLS.LpuSectionProfile_Name,'')
						 || ', ' ||  COALESCE(TtsDiag.Diag_Code,'')  || '.' ||  COALESCE(TtsDiag.Diag_Name,'')
						 || ', Бригада №' ||  COALESCE(ET.EmergencyTeam_Num,'')
					else
						null
					end as SMMP_exists,
				null as EvnPS_CodeConv,
				null as EvnPS_NumConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				null as EvnQueue_id,
				ED.TimetableStac_id as TimetableStac_id,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				ED.Person_id as Person_id,
				ED.PersonEvn_id as PersonEvn_id,
				ED.Server_id as Server_id,
				null as childElement, 
				ED.LpuSectionProfile_id as LpuSectionProfile_id,
				ED.Diag_id as Diag_id,
				TTS.LpuSection_id as LpuSection_id,
				TTS.pmUser_updId as pmUser_updId,
				ED.pmUser_insID as pmUser_insID,
				COALESCE(ED.Lpu_sid,ED.Lpu_id) as Lpu_id,
				CCCTT.CmpCallCard_id as CmpCallCard_id,
				null as EvnSection_id,
				ED.PayType_id as PayType_id,
				{$isBDZ}
				{$select_person_encryp_data_no_quote}
				{$select_person_data_tab2}
			from
				v_EvnDirection_all ED 
				left join v_EvnStatus ES  on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_TimetableStac_lite TTS  on ED.TimetableStac_id = TTS.TimetableStac_id
				left join v_LpuSection TTSLS  on TTS.LpuSection_id = TTSLS.LpuSection_id
				left join v_CmpCloseCardTimeTable CCCTT  on CCCTT.TimetableStac_id = TTS.TimetableStac_id
				left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_Diag TtsDiag  on TtsDiag.Diag_id = CCC.Diag_gid
				left join lateral (select * from v_Person_all PS where ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id limit 1) as PS on true
				left join v_PrehospStatus EST  on 1 = EST.PrehospStatus_id
				left join v_LpuUnit LU  on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_PersonLpuInfo  on ED.Lpu_id = v_PersonLpuInfo.Lpu_id and PS.Person_id = v_PersonLpuInfo.Person_id
				left join v_EvnPS EvnPS on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection TTSLSd on ED.LpuSection_id = TTSLSd.LpuSection_id
				left join v_Lpu LPUd on ED.Lpu_did = LPUd.Lpu_id
				{$join}
			where
				ED.Lpu_did = :Lpu_id
				AND ED.EvnQueue_id is null
				AND ( ED.DirType_id is not null AND ED.DirType_id in (1, 2, 4, 5, 6) )
				AND COALESCE(LU.LpuUnitType_SysNick,'stac')!='polka'
				/*
				AND ED.DirFailType_id is null --если не отменено
				AND COALESCE(ES.EvnStatus_SysNick, '') not in ('Canceled', 'Declined', 'Serviced')
				*/
				AND (
					(COALESCE(ES.EvnStatus_SysNick, '') not in ('Canceled', 'Serviced', 'Declined') AND ED.DirFailType_id is NULL)
					OR
					(COALESCE(ES.EvnStatus_SysNick, '') in ('Declined') AND ED.DirFailType_id is NOT NULL)
				)
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnDirection', $data['isAll'])."
		";

		$tab2 = "";
		if (!empty($filter_dir)) {
			foreach($filter_dir as $filter_dir1) {
				if (!empty($tab2)) {
					$tab2 .= "
						union all
					";
				}
				$tab2 .= $pre_tab2 . " " . $filter_dir1;
			}
		} else {
			$tab2 = $pre_tab2;
		}

		// https://redmine.swan.perm.ru/issues/39660
		// Запрос оптимизирован - выборка по TimetableStac вынесена в with, далее вместо 5 OR реализовано в виде 5 union all
		$sql = "
			with tab1 as (
				select
					'TimetableStac_' ||  TTS.TimetableStac_id::text as keyNote,
					COALESCE(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate) as sortDate,
					EST.PrehospStatus_id as groupField,
					null as EvnDirection_id,
					v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
					null as EvnDirection_IsConfirmed,
					EvnPS.EvnDirection_Num as EvnDirection_Num,
					EvnPS.EvnDirection_setDT as EvnDirection_setDate,
					coalesce(EvnPS.Diag_did,CCC.Diag_gid) as Diag_did,
					EvnPS.LpuSection_did as LpuSection_did,
					TTSLSd.LpuSection_Name as LpuSection_dName,
					EvnPS.Lpu_did,
					LPUd.Lpu_Name as Lpu_dName,
					EvnPS.Org_did,
					ORGd.Org_Name as Org_dName,
					null as DirType_id,
					null as Direction_exists,
					TTS.TimetableStac_setDate as TimetableStac_setDate,
					case when TTS.TimetableType_id = 6 then
						COALESCE(TTSLS.LpuSectionProfile_Name,'')
						 || ', ' ||  COALESCE(TtsDiag.Diag_Code,'')  || '.' ||  COALESCE(TtsDiag.Diag_Name,'')
						 || ', Бригада №' ||  COALESCE(ET.EmergencyTeam_Num,'')
					else
						null
					end as SMMP_exists,
					COALESCE(CCC.CmpCallCard_Ngod::text,'') as EvnPS_CodeConv,
					COALESCE(ET.EmergencyTeam_Num,'') as EvnPS_NumConv,
					TTS.TimetableStac_updDT as TimetableStac_insDT,
					null as EvnQueue_setDate,
					null as EvnQueue_id,
					case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
					EvnPS.EvnPS_id as EvnPS_id,
					EvnPS.EvnPS_setDT as EvnPS_setDT,
					datediff('minute', dbo.tzGetDate(), EvnPS.EvnPS_setDT) as EvnPS_setDT_Diff,
					EvnPS.EvnPS_NumCard as EvnPS_NumCard,
					TTS.TimetableStac_id as TimetableStac_id,
					TTSLS.LpuSectionProfile_Name as LpuSectionProfile_Name,
					TTSLS.LpuSectionProfile_id as LpuSectionProfile_did,
					EST.PrehospStatus_id as PrehospStatus_id,
					EST.PrehospStatus_Name as PrehospStatus_Name,
					Diag.Diag_id as Diag_id,
					RTRIM(coalesce(Diag.Diag_Code,TtsDiag.Diag_Code)) || '. ' || RTRIM(coalesce(Diag.Diag_Name,TtsDiag.Diag_Name)) as Diag_CodeName,
					puc.PMUser_Name as pmUser_Name,
					case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,
					EvnPS.PrehospWaifRefuseCause_id as PrehospWaifRefuseCause_id,
					COALESCE(EvnPS.EvnPS_IsTransfCall,1) as IsCall,
					COALESCE(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp,
					PA.PrehospArrive_id as PrehospArrive_id,
					PA.PrehospArrive_SysNick as PrehospArrive_SysNick,
					PT.PrehospType_id as PrehospType_id,
					PT.PrehospType_SysNick as PrehospType_SysNick,
					CASE
						WHEN EvnPS.PrehospDirect_id = 1 
						THEN TTSLSd.LpuSection_Name
						ELSE ORGd.Org_Name
					END as PrehospDirection_Name,
					{$select_person_data_tab1}
					{$select_person_encryp_data_no_quote}
					PS.Person_id as Person_id,
					COALESCE(EvnPS.PersonEvn_id,PS.PersonEvn_id) as PersonEvn_id,
					COALESCE(EvnPS.Server_id,PS.Server_id) as Server_id,
					TTSLS.LpuSection_Name as LpuSection_Name,
					TTS.TimetableType_id as TimetableType_id,
					null as EmergencyDataStatus_id,
					TTS.Person_id as Person_ttsid,
					EvnPS.LpuSection_pid as LpuSection_pid,
					EvnPS.MedStaffFact_pid as MedStaffFact_pid,
					EvnPS.EvnPS_OutcomeDT as EvnPS_OutcomeDT,
					EvnPS.LpuSection_eid as LpuSection_eid,
					CCC.CmpCallCard_id as CmpCallCard_id,
					null as EvnSection_id,
					null as PayType_id,
					{$isBDZ}
					{$this->getScaleFields()}
					EPL.EvnPL_id as EvnPL_id,
					EPL.EvnPL_NumCard as EvnPL_NumCard
				from v_TimetableStac_lite TTS 
					LEFT JOIN LATERAL (
						select 1 as EvnDirection_IsConfirmed
					) ED ON true
					left join v_EvnPS EvnPS  on TTS.Evn_id = EvnPS.EvnPS_id
					left join v_LpuSection TTSLS  on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
					left join v_CmpCloseCardTimeTable CCCTT  on CCCTT.TimetableStac_id = TTS.TimetableStac_id
					left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
					left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
					left join v_PersonState PS  on TTS.Person_id = PS.Person_id

					left join v_Diag Diag  on EvnPS.Diag_pid = Diag.Diag_id
					left join lateral (
						select *
						from v_Diag TtsDiag
						where TtsDiag.Diag_id = CCC.Diag_gid
						limit 1
					) TtsDiag on true
					left join v_PrehospStatus EST  on COALESCE(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
					left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
					left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
					left join pmUserCache puc  on TTS.pmUser_updId = puc.pmUser_id					
					left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
					left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id
					left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id		
					left join v_EvnDirection EDr  on EDr.EvnDirection_id = TTS.EvnDirection_id
					
					left join v_LpuSection TTSLSd on EvnPS.LpuSection_did = TTSLSd.LpuSection_id
					left join v_Lpu LPUd on EvnPS.Lpu_did = LPUd.Lpu_id
					left join v_Org ORGd on EvnPS.Org_did = ORGd.Org_id
					{$this->getScaleJoins('CCC')}
					{$join}
				where
					TTSLS.Lpu_id = :Lpu_id
					AND TTS.Person_id is not null -- убрал некорректное отображение пустых бирок
					AND PS.Person_IsUnknown = 2
					AND EDr.EvnDirection_id is null -- здесь только без направлений
					AND EST.PrehospStatus_id = 1 -- со статусом не поступал
					{$filter_tts} -- если такой фильтр есть, то запрос можно сразу ограничить
					{$filter}
					{$filter_isConfirmed}
					".$this->genLpuSectionServiceFilter('TimetableStac', $data['isAll'])."
			),
			tab2 as (
				{$tab2}
			), mv as (
				select
					cast(:date as timestamp) as dt
			)
			select
				'EvnDirection_' ||  ED.EvnDirection_id::text as \"keyNote\",
				to_char( coalesce (EvnPS.EvnPS_setDate, ED.TimetableStac_setDate, ED.EvnDirection_setDT), 'yyyymmdd') as \"sortDate\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(cast(ED.EvnDirection_setDate as date), 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_did as \"LpuSection_did\",
				TTSLSd.LpuSection_Name as \"LpuSection_dName\",
				L.Lpu_id as \"Lpu_did\",
				LPUd.Lpu_Name as \"Lpu_dName\",
				L.Org_id as \"Org_did\",
				ORGd.Org_Name as \"Org_dName\",	
				ED.DirType_id as \"DirType_id\",
				COALESCE(DT.DirType_Name,'')  || ', ' ||  COALESCE(LSP.LpuSectionProfile_Name,'')  || ', ' ||  COALESCE(DiagD.Diag_Code,'') || '.' ||  COALESCE(DiagD.Diag_Name,'') as \"Direction_exists\",
				to_char(ED.TimetableStac_setDate, 'DD.MM.YYYY') || ' ' || to_char(ED.TimetableStac_setDate, 'HH24:MI') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				null as \"EvnQueue_id\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				datediff('minute', dbo.tzGetDate(), EvnPS.EvnPS_setDT) as \"EvnPS_setDT_Diff\",
				EvnPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ED.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as \"PrehospStatus_id\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 'Отказ' ELSE 'Не поступал' END as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\", --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EvnPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				COALESCE(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				COALESCE(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PT.PrehospType_id as \"PrehospType_id\",
				PT.PrehospType_SysNick as \"PrehospType_SysNick\",
				CASE
					WHEN EvnPS.PrehospDirect_id = 1
					THEN TTSLSd.LpuSection_Name
					ELSE ORGd.Org_Name
				END as \"PrehospDirection_Name\",
				to_char(ED.Person_Birthday, 'DD.MM.YYYY') as \"Person_BirthDay\",
				ED.Person_age as \"Person_age\",
				ED.Person_Fio as \"Person_Fio\",
				ED.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				null as \"childElement\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.EvnPL_NumCard as \"EvnPL_NumCard\",
				ED.CmpCallCard_id as \"CmpCallCard_id\",
				cast(null as bigint) as \"EvnSection_id\",
				ED.PayType_id as \"PayType_id\",
				Person_IsBDZ as \"Person_IsBDZ\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = ED.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$this->getScaleFields()}
				to_char(EvnPS.EvnPS_OutcomeDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_OutcomeDT\"
			from
				tab2 ED 
				left join v_EvnPS EvnPS  on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT  on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP  on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD  on ED.Diag_id= DiagD.Diag_id
				left join v_LpuSection LS  on COALESCE(EvnPS.LpuSection_eid,ED.LpuSection_id,ED.LpuSection_did) = LS.LpuSection_id
				left join v_Diag Diag  on COALESCE(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join pmUserCache puc  on COALESCE (ED.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_Lpu L  on L.Lpu_id = ED.Lpu_id				
				left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
				left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id
				left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				left join v_LpuSection TTSLSd on ED.LpuSection_did = TTSLSd.LpuSection_id
				left join v_Lpu LPUd on L.Lpu_id = LPUd.Lpu_id
				left join v_Org ORGd on L.Org_id = ORGd.Org_id
				{$this->getScaleJoins('ED')}
			where
				(1=1) and COALESCE(EvnPS.PrehospStatus_id,1) in (1)
				{$filter_isConfirmed}
				
			union all
			select
				'EvnQueue_' || EQ.EvnQueue_id::text as \"keyNote\",
				to_char(EQ.EvnQueue_setDT, 'YYYYMMDD') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				EQ.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				TTSLSd.LpuSection_Name as \"LpuSection_dName\",
				L.Lpu_id as \"Lpu_did\",
				LPUd.Lpu_Name as \"Lpu_dName\",
				L.Org_id as \"Org_did\",
				ORGd.Org_Name as \"Org_dName\",
				ED.DirType_id as \"DirType_id\",
				COALESCE(DT.DirType_Name,'')  || ', ' ||  COALESCE(LSP.LpuSectionProfile_Name,'')  || ', ' ||  COALESCE(Diag.Diag_Code,'') || '.' ||  COALESCE(Diag.Diag_Name,'') as \"Direction_exists\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				to_char(EQ.EvnQueue_setDT, 'DD.MM.YYYY') as \"EvnQueue_setDate\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				1 as \"IsHospitalized\",
				null as \"EvnPS_id\",
				'' as \"EvnPS_setDT\",
				0 as \"EvnPS_setDT_Diff\",
				null as \"EvnPS_NumCard\",
				EQ.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				EQ.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\",
				1 as \"IsRefusal\",
				null as \"PrehospWaifRefuseCause_id\",
				null as \"MedStaffFact_pid\",
				1 as \"IsCall\",
				1 as \"IsSmmp\",
				null as \"PrehospArrive_id\",
				null as \"PrehospArrive_SysNick\",
				null as \"PrehospType_id\",
				null as \"PrehospType_SysNick\",
				CASE
					WHEN EvnPS.PrehospDirect_id = 1
					THEN TTSLSd.LpuSection_Name
					ELSE ORGd.Org_Name
				END as \"PrehospDirection_Name\",
				{$select_person_data_eq}
				{$select_person_encryp_data}
				EQ.Person_id as \"Person_id\",
				EQ.PersonEvn_id as \"PersonEvn_id\",
				EQ.Server_id as \"Server_id\",
				null as \"childElement\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				null as \"EvnPL_id\",
				null as \"EvnPL_NumCard\",
				EvnPS.CmpCallCard_id as \"CmpCallCard_id\",
				cast(null as bigint) as \"EvnSection_id\",
				ED.PayType_id as \"PayType_id\",
				{$isBDZ}
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = EQ.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$this->getScaleFields()}
				'' as \"EvnPS_OutcomeDT\"
			from v_EvnDirection_all ED 
                inner join  v_EvnQueue EQ  on EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_id = ED.EvnQueue_id
				left join v_LpuUnit LU  on EQ.LpuUnit_did = LU.LpuUnit_id
				left join lateral (select * from v_Person_all PS where EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id limit 1) as PS on true
				left join v_DirType DT  on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP  on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join lateral(
					select *
					from v_Diag Diag
					where COALESCE(EQ.Diag_id, ED.Diag_id) = Diag.Diag_id
					limit 1
				) Diag on true
				left join v_PrehospStatus EST  on EST.PrehospStatus_id =  CASE WHEN coalesce(ED.EvnDirection_failDT, ED.EvnDirection_statusDate) IS NOT NULL AND ED.EvnStatus_id = 13 THEN 5 ELSE 1 END
				left join pmUserCache puc  on EQ.pmUser_insID = puc.pmUser_id
				left join v_Lpu L  on L.Lpu_id = COALESCE(ED.Lpu_sid,ED.Lpu_id)
                left join v_EvnPS EvnPS  on EvnPS.EvnDirection_id = ED.EvnDirection_id
				left join v_LpuSection LS  on LS.LpuSection_id = ED.LpuSection_did
				-- нет смысла брать согласие с КВС, при условии в фильтрах AND EvnPS.EvnPS_id is null
                left join v_PersonLpuInfo  on ED.Lpu_id = v_PersonLpuInfo.Lpu_id and ED.Person_id = v_PersonLpuInfo.Person_id
				left join v_LpuSection TTSLSd on ED.LpuSection_did = TTSLSd.LpuSection_id
				left join v_Lpu LPUd on L.Lpu_id = LPUd.Lpu_id
				left join v_Org ORGd on L.Org_id = ORGd.Org_id
				{$this->getScaleJoins('EvnPS')}
				{$join}
			where
				{$filter_eq}
				--EQ.EvnQueue_failDT is null
                (EQ.EvnQueue_failDT is NULL OR ED.EvnStatus_id = 13 AND (select dt
                                                                         from mv) = CAST(ED.EvnDirection_failDT AS DATE) --показывать в группе ОТКАЗ со статусом отклонено
                )

				AND EQ.EvnQueue_recDT is null
				AND ED.EvnStatus_id not in (8, 15, 17)
				{$filter_lpu_id}
				AND ED.Lpu_did = :Lpu_id -- по идее очередь куда поставили и направление куда направили, должны быть в одну МО
				AND (EQ.EvnDirection_id is null OR EQ.EvnQueue_id is not null)
				AND ED.DirType_id IN (1,2,5,4,6)
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnQueue', $data['isAll'])."

			union all

			select
				keyNote as \"keyNote\",
				to_char(sortDate, 'YYYYMMDD') as \"sortDate\",
				groupField as \"groupField\",
				cast(EvnDirection_id as bigint) as \"EvnDirection_id\",
				PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				cast(EvnDirection_IsConfirmed as bigint) as \"EvnDirection_IsConfirmed\",
				EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnDirection_setDate, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				Diag_did as \"Diag_did\",
				LpuSection_did as \"LpuSection_did\",
				LpuSection_dName as \"LpuSection_dName\",
				Lpu_did as \"Lpu_did\",
				Lpu_dName as \"Lpu_dName\",
				Org_did as \"Org_did\",
				Org_dName as \"Org_dName\",
				cast(DirType_id as bigint) as \"DirType_id\",
				Direction_exists as \"Direction_exists\",
				to_char(TimetableStac_setDate, 'DD.MM.YYYY') || ' ' || to_char(TimetableStac_setDate, 'HH24:MI') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EvnPS_NumConv as \"EvnPS_NumConv\",
				to_char(TimetableStac_insDT, 'DD.MM.YYYY HH24:MI') as \"TimetableStac_insDT\",
				EvnQueue_setDate as \"EvnQueue_setDate\",
				null as \"EvnQueue_id\",
				IsHospitalized as \"IsHospitalized\",
				EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_setDT\",
				datediff('minute', dbo.tzGetDate(), EvnPS_setDT) as \"EvnPS_setDT_Diff\",
				EvnPS_NumCard as \"EvnPS_NumCard\",
				TimetableStac_id as \"TimetableStac_id\",
				LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LpuSectionProfile_did as \"LpuSectionProfile_did\",
				PrehospStatus_id as \"PrehospStatus_id\",
				PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag_id as \"Diag_id\",
				Diag_CodeName as \"Diag_CodeName\",
				pmUser_Name as \"pmUser_Name\",
				IsRefusal as \"IsRefusal\",
				PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				MedStaffFact_pid as \"MedStaffFact_pid\",
				IsCall as \"IsCall\",
				IsSmmp as \"IsSmmp\",
				PrehospArrive_id as \"PrehospArrive_id\",
				PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PrehospType_id as \"PrehospType_id\",
				PrehospType_SysNick as \"PrehospType_SysNick\",
				PrehospDirection_Name as \"PrehospDirection_Name\",
				to_char(cast(Person_Birthday as date), 'DD.MM.YYYY') as \"Person_BirthDay\",
				Person_age as \"Person_age\",
				Person_Fio as \"Person_Fio\",
				PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				null as \"childElement\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnPL_id as \"EvnPL_id\",
				EvnPL_NumCard as \"EvnPL_NumCard\",
				CmpCallCard_id as \"CmpCallCard_id\",
				cast(null as bigint) as \"EvnSection_id\",
				cast(PayType_id as bigint) as \"PayType_id\",
				Person_IsBDZ as \"Person_IsBDZ\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$this->getScaleFields(false)}
				to_char(EvnPS_OutcomeDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_OutcomeDT\"
			from tab1 
			where
				-- экстр. бирки только с EmergencyDataStatus Койка забронирована на установленную дату
				-- /*AND EmergencyDataStatus_id = 1 */ - описание в комите к #51610
				(TimetableType_id = 6 AND cast(TimetableStac_setDate as date) = (select dt from mv) /*AND EmergencyDataStatus_id = 1 */)

			union all

			select
				keyNote as \"keyNote\",
				to_char(sortDate, 'YYYYMMDD') as \"sortDate\",
				groupField as \"groupField\",
				cast(EvnDirection_id as bigint) as \"EvnDirection_id\",
				PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				cast(EvnDirection_IsConfirmed as bigint) as \"EvnDirection_IsConfirmed\",
				EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnDirection_setDate, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				Diag_did as \"Diag_did\",
				LpuSection_did as \"LpuSection_did\",
				LpuSection_dName as \"LpuSection_dName\",
				Lpu_did as \"Lpu_did\",
				Lpu_dName as \"Lpu_dName\",
				Org_did as \"Org_did\",
				Org_dName as \"Org_dName\",
				cast(DirType_id as bigint) as \"DirType_id\",
				Direction_exists as \"Direction_exists\",
				to_char(TimetableStac_setDate, 'DD.MM.YYYY') || ' ' || to_char(TimetableStac_setDate, 'HH24:MI') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EvnPS_NumConv as \"EvnPS_NumConv\",
				to_char(TimetableStac_insDT, 'DD.MM.YYYY HH24:MI') as \"TimetableStac_insDT\",
				EvnQueue_setDate as \"EvnQueue_setDate\",
				cast(EvnQueue_id as bigint) as \"EvnQueue_id\",
				IsHospitalized as \"IsHospitalized\",
				EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_setDT\",
				datediff('minute', dbo.tzGetDate(), EvnPS_setDT) as \"EvnPS_setDT_Diff\",
				EvnPS_NumCard as \"EvnPS_NumCard\",
				TimetableStac_id as \"TimetableStac_id\",
				LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LpuSectionProfile_did as \"LpuSectionProfile_did\",
				PrehospStatus_id as \"PrehospStatus_id\",
				PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag_id as \"Diag_id\",
				Diag_CodeName as \"Diag_CodeName\",
				pmUser_Name as \"pmUser_Name\",
				IsRefusal as \"IsRefusal\",
				PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				MedStaffFact_pid as \"MedStaffFact_pid\",
				IsCall as \"IsCall\",
				IsSmmp as \"IsSmmp\",
				PrehospArrive_id as \"PrehospArrive_id\",
				PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PrehospType_id as \"PrehospType_id\",
				PrehospType_SysNick as \"PrehospType_SysNick\",
				PrehospDirection_Name as \"PrehospDirection_Name\",
				to_char(cast(Person_Birthday as date), 'DD.MM.YYYY') as \"Person_BirthDay\",
				Person_age as \"Person_age\",
				Person_Fio as \"Person_Fio\",
				PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				null as \"childElement\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnPL_id as \"EvnPL_id\",
				EvnPL_NumCard as \"EvnPL_NumCard\",
				CmpCallCard_id as \"CmpCallCard_id\",
				cast(null as bigint) as \"EvnSection_id\",
				cast(PayType_id as bigint) as \"PayType_id\",
				Person_IsBDZ as \"Person_IsBDZ\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$this->getScaleFields(false)}
				to_char(EvnPS_OutcomeDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_OutcomeDT\"
			from tab1 
			where
				-- в зависимости от фильтра: все или на установленную дату
				(TimetableType_id != 6 AND Person_ttsid is not null 
                {$filter_tts}
                )

			union all

			select
				'EvnPS_' || EvnPS.EvnPS_id::text as \"keyNote\",
				to_char(EvnPS.EvnPS_setDate, 'YYYYMMDD') as \"sortDate\",
				case 
					when EvnPS.EvnPS_setDate <= (select dt from mv) and datediff('day', EvnPS.EvnPS_setDate, (select dt from mv)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, ((select dt from mv) + interval '1 day')) > (select dt from mv) then 3
					when (cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND EvnPS.LpuSection_eid IS NOT NULL) then 4
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND lt.LeaveType_Code = '603' then 2" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 5
					when ED.EvnStatus_id = 13 AND ED.EvnDirection_failDT IS NOT NULL AND (select dt from mv) = CAST(ED.EvnDirection_failDT AS DATE) then 5
				end as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				EvnPS.Diag_did as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				TTSLSd.LpuSection_Name as \"LpuSection_dName\",
				EvnPS.Lpu_did as \"Lpu_did\",
				LPUd.Lpu_Name as \"Lpu_dName\",
				EvnPS.Org_did as \"Org_did\",
				ORGd.Org_Name as \"Org_dName\",
				ED.DirType_id as DirType_id,
				COALESCE(DT.DirType_Name,'')  || ', ' ||  COALESCE(LSP.LpuSectionProfile_Name,'')  || ', ' ||  COALESCE(DiagD.Diag_Code,'') || '.' ||  COALESCE(DiagD.Diag_Name,'') as \"Direction_exists\",
				to_char(TTS.TimetableStac_setDate, 'DD.MM.YYYY') || ' ' || to_char(TTS.TimetableStac_setDate, 'HH24:MI') as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				null as \"EvnQueue_id\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				datediff('minute', dbo.tzGetDate(), EvnPS.EvnPS_setDT) as \"EvnPS_setDT_Diff\",
				EvnPS_NumCard as \"EvnPS_NumCard\",
				null as \"TimetableStac_id\",
				'' as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				case 
					when EvnPS.EvnPS_setDate <= (select dt from mv) and datediff('day', EvnPS.EvnPS_setDate, (select dt from mv)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, ((select dt from mv) + interval '1 day')) > (select dt from mv) then 'Находится в приемном'
					when cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND EvnPS.LpuSection_eid IS NOT NULL then 'Госпитализирован'
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND lt.LeaveType_Code = '603' then 'Принят'" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 'Отказ'
				end as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",--Диагноз приемного; код, наименование
				puc.PMUser_Name as \"pmUser_Name\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? " and lt.LeaveType_Code = '602'" : "") . " then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EvnPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				COALESCE(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				COALESCE(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PT.PrehospType_id as \"PrehospType_id\",
				PT.PrehospType_SysNick as \"PrehospType_SysNick\",
				CASE
					WHEN EvnPS.PrehospDirect_id = 1
					THEN TTSLSd.LpuSection_Name
					ELSE ORGd.Org_Name
				END as \"PrehospDirection_Name\",
				{$select_person_data_evnps}
				{$select_person_encryp_data}
				EvnPS.Person_id as \"Person_id\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				Child.ChildEvn_id::text as childElement,
				LS.LpuSection_Name as \"LpuSection_Name\",
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.EvnPL_NumCard as \"EvnPL_NumCard\",
				EvnPS.CmpCallCard_id as \"CmpCallCard_id\",
				EvnSection.EvnSection_id as \"EvnSection_id\",
				cast(null as bigint) as \"PayType_id\",
				{$isBDZ}
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = EvnPS.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$this->getScaleFields()}
				to_char(EvnPS.EvnPS_OutcomeDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_OutcomeDT\"
			from v_EvnPS EvnPS 
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT  on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP  on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join lateral(
					select *
					from v_Diag DiagD
					where ED.Diag_id = DiagD.Diag_id
					limit 1
				) DiagD on true
				left join lateral (select * from v_Person_all PS where EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id limit 1) as PS on true
				left join v_LpuSection LS  on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join lateral(
					select *
					from v_Diag Diag
					where EvnPS.Diag_pid = Diag.Diag_id
					limit 1
				) Diag on true
				left join v_PrehospStatus EST  on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				--left join v_TimetableStac_lite TTS  on EvnPS.EvnPS_id = TTS.Evn_id
				LEFT JOIN LATERAL (
					Select TimetableStac_setDate from v_TimetableStac_lite TTS  where EvnPS.EvnPS_id = TTS.Evn_id and EvnPS.EvnDirection_id = TTS.EvnDirection_id limit 1
				) TTS ON true
				left join pmUserCache puc  on EvnPS.pmUser_updID = puc.pmUser_id
				LEFT JOIN LATERAL (
					select
						ES.EvnSection_id as ChildEvn_id,
						ES.EvnSection_setDate
					from
						v_EvnSection ES 
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
						and COALESCE(ES.EvnSection_IsPriem, 1) = 1
					limit 1
				) Child ON true
				left join lateral (
					select
						ES.EvnSection_id
					from
						v_EvnSection ES
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
					limit 1
				) EvnSection ON true
				" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "LEFT JOIN LATERAL (
					select LeaveType_prmid
					from v_EvnSection 
					where EvnSection_pid = EvnPS.EvnPS_id
						and EvnSection_IsPriem = 2
					limit 1
				) ESPriem ON true
				left join v_LeaveType lt  on lt.LeaveType_id = ESPriem.LeaveType_prmid" : "") . "				
				--left join v_EvnLink EL  on EL.Evn_lid = EvnPS.EvnPS_id
				--left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id	
				LEFT JOIN LATERAL (
					Select EvnPL_id, EvnPL_NumCard from v_EvnLink EL  
					left join v_EvnPL EPL  on EPL.EvnPL_id = EL.Evn_id	
					where EL.Evn_lid = EvnPS.EvnPS_id
					limit 1
				) EPL ON true
				left join v_PrehospArrive PA  on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT  on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo  on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				left join v_LpuSection TTSLSd on EvnPS.LpuSection_did = TTSLSd.LpuSection_id
				left join v_Lpu LPUd on EvnPS.Lpu_did = LPUd.Lpu_id
				left join v_Org ORGd on ORGd.Org_id = EvnPS.Org_did
				{$this->getScaleJoins('EvnPS')}
				{$join}
			where
				EvnPS.Lpu_id = 
                :Lpu_id
				AND EvnPS.LpuSection_pid = 
                :LpuSection_id
				AND EvnPS.EvnPS_setDate between (select dt from mv)-interval '2 days' and (select dt from mv)
				and COALESCE(cast(EvnPS.EvnPS_OutcomeDT as date),(select dt from mv))>=(select dt from mv)
				/*AND ((
					--со статусом Находится в приемном - история болезни создана установленным днем и дата исхода пустая или позже
					-- #57215
					EvnPS.EvnPS_setDate <= (select dt from mv) and datediff('day', EvnPS.EvnPS_setDate, (select dt from mv)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, ((select dt from mv) + interval '1 day')) > (select dt from mv)
				)
				OR (
					--со статусом Отказ на установленную дату или со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = (select dt from mv) AND COALESCE(" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "ESPriem.LeaveType_prmid, " : "") . "EvnPS.PrehospWaifRefuseCause_id,  EvnPS.LpuSection_eid) IS NOT NULL)
				)
				OR (
					--со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = -date AND EvnPS.LpuSection_eid IS NOT NULL)
				)
				)*/
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnPS', $data['isAll'])."

			order by
				\"sortDate\" DESC
		";

		//echo getDebugSql($sql, $params);exit;

		$res = $this->db->query($sql,$params);

		if ( is_object($res) ) {
			$res_array = $res->result('array');
			return $res_array;
		}
		else
			return false;
	}

	/**
	 *
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * * Получает список записей для АРМа приемного:
	 * - электронные направления не из очереди, с записью на койку или без, в т.ч. экстренные
	 * - записи на койку (стац.бирки) в т.ч. экстренные бирки по СММП и бирки без эл.направлений
	 * - КВС самостоятельно обратившихся (принятых не по направлению, не по бирке) и принятых из очереди
	 * - записи из очереди с эл. направлением или без
	 * Группы Не поступал, Находится в приемном, Госпитализирован, Отказ формировать на установленную дату
	 * Не поступал - "план госпитализаций", пациенты ожидающие госпитализации на установленную дату
	 * Находится в приемном - история болезни создана установленным днем, но исход из приемного другим днем.
	 * Госпитализирован - исход из приемного - Госпитализирован на установленную дату
	 * Отказ - исход из приемного - Отказ на установленную дату.
	 * Очередь и план госпитализаций показывать на установленную дату
	 */
	function mLoadWorkPlacePriem($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['date'] = $data['date'];// установленная дата
		//$params['begDT'] = date('Y-m-d H:m',(time()-86400)).':00.000';// сутки от текущего времени
		//$params['begDT2'] = date('Y-m-d H:m',(time()-172800)).':00.000';// 2 суток от текущего времени
		$params['LpuSection_id'] = $data['LpuSection_id']; // приемное отделение
		// фильтр на направления с бирками со статусом не поступал
		$filter_dir = array();
		// фильтр на направления из очереди со статусом не поступал
		$filter_eq = '';
		// фильтр на не экстренные бирки без эл.направлений со статусом не поступал
		$filter_tts = '';
		$filter = '';
		$join = '';

		$isSearchByEncryp = false;
		$select_person_data_tab1 = "
						PS.Person_Birthday,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.' end as Person_age,
						coalesce(PS.Person_SurName,'НЕИЗВЕСТЕН') ||' '|| coalesce(PS.Person_FirName,'НЕИЗВЕСТЕН') ||' '|| coalesce(PS.Person_SecName,'НЕИЗВЕСТЕН') as Person_Fio,";

		$select_person_data_tab2 = "
						PS.Person_Birthday,
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.' end as Person_age,
						PS.Person_Fio";

		$select_person_data_eq = "
						to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) || ' мес.' end as \"Person_age\",
						PS.Person_Fio as \"Person_Fio\",";

		$select_person_data_evnps = "
						to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
						case when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate())||' мес.' end as \"Person_age\",
						PS.Person_Fio as \"Person_Fio\",";
		$select_person_encryp_data = "null as \"PersonEncrypHIV_Encryp\",";
		$select_person_encryp_data_no_quote = "null as PersonEncrypHIV_Encryp,";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
				$join .= " left join v_PersonEncrypHIV peh on peh.Person_id = ps.Person_id";
				$select_person_encryp_data = "peh.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",";
				$select_person_encryp_data_no_quote = "peh.PersonEncrypHIV_Encryp,";
				$selectPersonData = "
					,case when peh.PersonEncrypHIV_Encryp is null 
						then ps.Person_SurName || coalesce(' ' || ps.Person_FirName,'') || coalesce(' ' || ps.Person_SecName,'') 
						else rtrim(peh.PersonEncrypHIV_Encryp) end 
					as Person_Fio
					,null as Person_BirthDay
					,null as Person_Phone
					,null as Address_Address";
				$select_person_data_tab1 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'dd.mm.yyyy') end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then coalesce(PS.Person_SurName,'Не идентифицирован') ||' '|| coalesce(PS.Person_FirName,'') ||' '|| coalesce(PS.Person_SecName,'') else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";

				$select_person_data_tab2 = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else PS.Person_Birthday end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio";

				$select_person_data_eq = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'dd.mm.yyyy') end as Person_BirthDay,
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
						then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
						else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.' end as Person_age,
						case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Fio else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Fio,";

				$select_person_data_evnps = "
						case when peh.PersonEncrypHIV_Encryp is NOT null then NULL else to_char(PS.Person_Birthday, 'dd.mm.yyyy') end as \"Person_BirthDay\",
						case when peh.PersonEncrypHIV_Encryp is NOT null
							then NULL
						when dbo.Age2(PS.Person_Birthday,dbo.tzGetDate())>0 
							then dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) ||''
							else dbo.Age_newborn(PS.Person_Birthday,dbo.tzGetDate()) ||' мес.'
						end as \"Person_age\",
						case when peh.PersonEncrypHIV_Encryp is null
							then PS.Person_Fio
							else rtrim(peh.PersonEncrypHIV_Encryp)
						end as \"Person_Fio\",";
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filter .= " and not exists(
					select peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh
					inner join v_EncrypHIVTerr eht on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and coalesce(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
					limit 1
				)";
			}
		}

		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and lower(peh.PersonEncrypHIV_Encryp) like lower(:Person_SurName)";
			} else {
				$filter .= " AND lower(PS.Person_Surname) LIKE lower(:Person_SurName)";
			}
			$params['Person_SurName'] = rtrim($data['Person_SurName']).'%';
		}

		if (!empty($data['Person_FirName']))
		{
			$filter .= " AND lower(PS.Person_Firname) LIKE lower(:Person_FirName)";
			$params['Person_FirName'] = rtrim($data['Person_FirName']).'%';
		}
		if (!empty($data['Person_SecName']))
		{
			$filter .= " AND lower(PS.Person_Secname) LIKE lower(:Person_SecName)";
			$params['Person_SecName'] = rtrim($data['Person_SecName']).'%';
		}
		if (!empty($data['Person_BirthDay']))
		{
			$filter .= " AND cast(PS.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if (!empty($data['PrehospStatus_id']))
		{
			$filter .= " AND EST.PrehospStatus_id = :PrehospStatus_id";
			$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
		}

		if (empty($data['EvnDirectionShow_id']))
		{
			// На установленную дату
			$filter_dir [] = " AND TTS.TimetableStac_setDate = cast(:date as date)";
			//$filter_dir [] = " AND ED.EvnDirection_setDT = cast(:date as date)";
			$filter_tts .= " AND cast(TimetableStac_setDate as date) = cast(:date as date)";
		}
		// иначе Все направления (отображать направления с бирками без признака отмены и без связки с КВС, но не зависимо от даты бирки)

		if (empty($data['EvnQueueShow_id']))
		{
			// не показывать очередь
			$filter_eq .= "(1=2) AND ";
		}
		else if($data['EvnQueueShow_id'] == 1)
		{
			// показать очередь, кроме записй из архива
			$filter_eq .= "coalesce(EQ.EvnQueue_IsArchived,1) = 1 AND";
		}

		$filter_isConfirmed = '';
		if (!empty($data['EvnDirection_isConfirmed'])) {
			$filter_isConfirmed = " AND coalesce(ED.EvnDirection_isConfirmed, 1) = :EvnDirection_isConfirmed";
			$params['EvnDirection_isConfirmed'] = $data['EvnDirection_isConfirmed'];
		}
		// иначе отобразить все


		$pre_tab2 = "
			select
				TTS.TimetableStac_setDate, 
				ES.EvnStatus_SysNick,
				ED.EvnDirection_setDT,
				ED.EvnDirection_insDT,
				ED.EvnDirection_id as EvnDirection_id,
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
				ED.EvnDirection_IsConfirmed,
				ED.EvnDirection_Num as EvnDirection_Num,
				ED.EvnDirection_setDate as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_did,
				ED.DirType_id as DirType_id,
				case when TTS.TimetableType_id = 6 then
						coalesce(TTSLS.LpuSectionProfile_Name,'')
						||', '|| coalesce(TtsDiag.Diag_Code,'') ||'.'|| coalesce(TtsDiag.Diag_Name,'')
						||', Бригада №'|| coalesce(ET.EmergencyTeam_Num,'')
					else
						null
					end as SMMP_exists,
				null as EvnPS_CodeConv,
				null as EvnPS_NumConv,
				cast(null as timestamp) as TimetableStac_insDT,
				cast(null as timestamp) as EvnQueue_setDate,
				cast(null as bigint) as EvnQueue_id,
				ED.TimetableStac_id as TimetableStac_id,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				ED.Person_id,
				PS.Person_IsUnknown,
				ED.PersonEvn_id,
				ED.Server_id,
				ED.LpuSectionProfile_id,
				ED.Diag_id,
				TTS.LpuSection_id,
				TTS.pmUser_updId,
				ED.pmUser_insID,
				coalesce(ED.Lpu_sid,ED.Lpu_id) as Lpu_id,
				CCCTT.CmpCallCard_id,
				{$select_person_encryp_data_no_quote}
				{$select_person_data_tab2}
			from
				v_EvnDirection_all ED
				left join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_TimetableStac_lite TTS on ED.TimetableStac_id = TTS.TimetableStac_id
				left join v_LpuSection TTSLS on TTS.LpuSection_id = TTSLS.LpuSection_id
				left join v_CmpCloseCardTimeTable CCCTT on CCCTT.TimetableStac_id = TTS.TimetableStac_id
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
				left join v_EmergencyTeam ET on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_Diag TtsDiag on TtsDiag.Diag_id = CCC.Diag_gid
				left join lateral (select * from v_Person_all PS where ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id limit 1) as PS on true
				left join v_PrehospStatus EST on 1 = EST.PrehospStatus_id
				left join v_LpuUnit LU on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_PersonLpuInfo on ED.Lpu_id = v_PersonLpuInfo.Lpu_id and PS.Person_id = v_PersonLpuInfo.Person_id
				{$join}
			where
				ED.Lpu_did = :Lpu_id
				AND ED.EvnQueue_id is null
				AND ( ED.DirType_id is not null AND ED.DirType_id in (1, 2, 4, 5, 6) )
				AND coalesce(LU.LpuUnitType_SysNick,'stac')!='polka'
				/*
				AND ED.DirFailType_id is null --если не отменено
				AND coalesce(ES.EvnStatus_SysNick, '') not in ('Canceled', 'Declined', 'Serviced')
				*/
				AND (
					(coalesce(ES.EvnStatus_SysNick, '') not in ('Canceled', 'Serviced', 'Declined') AND ED.DirFailType_id is NULL)
					OR
					(coalesce(ES.EvnStatus_SysNick, '') in ('Declined') AND ED.DirFailType_id is NOT NULL)
				)
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnDirection', 0)."
		";

		$tab2 = "";
		if (!empty($filter_dir)) {
			foreach($filter_dir as $filter_dir1) {
				if (!empty($tab2)) {
					$tab2 .= "
						union all
					";
				}
				$tab2 .= $pre_tab2 . " " . $filter_dir1;
			}
		} else {
			$tab2 = $pre_tab2;
		}

		// https://redmine.swan.perm.ru/issues/39660
		// Запрос оптимизирован - выборка по TimetableStac вынесена в with, далее вместо 5 OR реализовано в виде 5 union all
		$sql = "
			with tab1 as (
				select
					coalesce(EvnPS.EvnPS_insDT,TTS.TimetableStac_insDT) as insertDT,
					EST.PrehospStatus_id as groupField,
					cast(null as bigint) as EvnDirection_id,
					v_PersonLpuInfo.PersonLpuInfo_IsAgree as PersonLpuInfo_IsAgree,
					cast(null as bigint) as EvnDirection_IsConfirmed,
					EvnPS.EvnDirection_Num as EvnDirection_Num,
					EvnPS.EvnDirection_setDT as EvnDirection_setDate,
					coalesce(EvnPS.Diag_did,CCC.Diag_gid) as Diag_did,
					EvnPS.LpuSection_did as LpuSection_did,
					EvnPS.Lpu_did,
					EvnPS.Org_did,
					cast(null as bigint) as DirType_id,
					TTS.TimetableStac_setDate,
					case when TTS.TimetableType_id = 6 then
						coalesce(TTSLS.LpuSectionProfile_Name,'')
						||', '|| coalesce(TtsDiag.Diag_Code,'') ||'.'|| coalesce(TtsDiag.Diag_Name,'')
						||', Бригада №'|| coalesce(ET.EmergencyTeam_Num,'')
					else
						null
					end as SMMP_exists,
					coalesce(cast(CCC.CmpCallCard_Ngod as varchar), '') as EvnPS_CodeConv,
					coalesce(cast(ET.EmergencyTeam_Num as varchar), '') as EvnPS_NumConv,
					TTS.TimetableStac_updDT as TimetableStac_insDT,
					cast(null as timestamp) as EvnQueue_setDate,
					cast(null as bigint) as EvnQueue_id,
					case when EvnPS.LpuSection_eid is not null then 1 else 0 end as IsHospitalized,
					EvnPS.EvnPS_id as EvnPS_id,
					EvnPS.EvnPS_setDT,
					EvnPS.EvnPS_NumCard,
					TTS.TimetableStac_id as TimetableStac_id,
					TTSLS.LpuSectionProfile_Name as LpuSectionProfile_Name,
					TTSLS.LpuSectionProfile_id as LpuSectionProfile_did,
					EST.PrehospStatus_id as PrehospStatus_id,
					EST.PrehospStatus_Name as PrehospStatus_Name,
					Diag.Diag_id,
					RTRIM(coalesce(Diag.Diag_Code,TtsDiag.Diag_Code)) as Diag_Code,
					RTRIM(coalesce(Diag.Diag_Name,TtsDiag.Diag_Name)) as Diag_Name,
					puc.PMUser_Name as MedPersonal_Fin,
					case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,
					EvnPS.PrehospWaifRefuseCause_id,
					coalesce(EvnPS.EvnPS_IsTransfCall,1) as IsCall,
					coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp,
					PA.PrehospArrive_id,
					PA.PrehospArrive_SysNick,
					PT.PrehospType_id,
					PT.PrehospType_SysNick,
					{$select_person_data_tab1}
					{$select_person_encryp_data_no_quote}
					PS.Person_id as Person_id,
					PS.Person_IsUnknown as Person_IsUnknown,
					coalesce(EvnPS.PersonEvn_id,PS.PersonEvn_id) as PersonEvn_id,
					coalesce(EvnPS.Server_id,PS.Server_id) as Server_id,
					TTSLS.LpuSection_Name,
					TTS.TimetableType_id,
					cast(null as bigint) as EmergencyDataStatus_id,
					TTS.Person_id as Person_ttsid,
					EvnPS.LpuSection_pid,
					EvnPS.MedStaffFact_pid,
					EvnPS.EvnPS_OutcomeDT,
					EvnPS.LpuSection_eid,
					CCC.CmpCallCard_id,
					EvnPS.EvnPS_RFID,
					{$this->getScaleFields()}
					EPL.EvnPL_id
				from v_TimetableStac_lite TTS
					left join lateral (
						select 1 as EvnDirection_IsConfirmed
					) ED on true
					left join v_EvnPS EvnPS on TTS.Evn_id = EvnPS.EvnPS_id
					left join v_LpuSection TTSLS on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
					left join v_CmpCloseCardTimeTable CCCTT on CCCTT.TimetableStac_id = TTS.TimetableStac_id
					left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCTT.CmpCallCard_id
					left join v_EmergencyTeam ET on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
					left join v_PersonState PS on TTS.Person_id = PS.Person_id

					left join v_Diag Diag on EvnPS.Diag_pid = Diag.Diag_id
					left join v_Diag TtsDiag on TtsDiag.Diag_id = CCC.Diag_gid
					left join v_PrehospStatus EST on coalesce(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
					left join v_PrehospArrive PA on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
					left join v_PrehospType PT on PT.PrehospType_id = EvnPS.PrehospType_id
					left join pmUserCache puc on TTS.pmUser_updId = puc.pmUser_id					
					left join v_EvnLink EL on EL.Evn_lid = EvnPS.EvnPS_id
					left join v_EvnPL EPL on EPL.EvnPL_id = EL.Evn_id
					left join v_PersonLpuInfo on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id		
					left join v_EvnDirection EDr on EDr.EvnDirection_id = TTS.EvnDirection_id
					{$this->getScaleJoins('CCC')}
					{$join}
				where
					TTSLS.Lpu_id = :Lpu_id
					AND TTS.Person_id is not null -- убрал некорректное отображение пустых бирок
					AND PS.Person_IsUnknown = 2
					AND EDr.EvnDirection_id is null -- здесь только без направлений
					AND EST.PrehospStatus_id = 1 -- со статусом не поступал
					{$filter_tts} -- если такой фильтр есть, то запрос можно сразу ограничить
					{$filter}
					{$filter_isConfirmed}
					".$this->genLpuSectionServiceFilter('TimetableStac', 0)."
			),
			tab2 as (
				{$tab2}
			)
			select
				to_char(coalesce(EvnPS.EvnPS_insDT, ED.TimetableStac_insDT, ED.EvnDirection_insDT), 'dd.mm.yyyy HH24:MI') as \"insertDT\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_did as \"LpuSection_did\",
				L.Lpu_id as \"Lpu_did\",
				L.Org_id as \"Org_did\",
				ED.DirType_id as \"DirType_id\",
				to_char(ED.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				cast(null as bigint) as \"EvnQueue_id\",
				case when EvnPS.LpuSection_eid is not null then 1 else 0 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				EvnPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ED.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 5 ELSE 1 END as \"PrehospStatus_id\",
				CASE WHEN ED.EvnStatus_SysNick = 'Declined' THEN 'Отказ' ELSE 'Не поступал' END as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				RTRIM(Diag.Diag_Name) as \"Diag_Name\",
				puc.PMUser_Name as \"MedPersonal_Fin\", --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EvnPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PT.PrehospType_id as \"PrehospType_id\",
				PT.PrehospType_SysNick as \"PrehospType_SysNick\",
				to_char(ED.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				ED.Person_age as \"Person_age\",
				ED.Person_Fio as \"Person_Fio\",
				ED.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				ED.Person_id as \"Person_id\",
				ED.Person_IsUnknown as \"Person_IsUnknown\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				EPL.EvnPL_id as \"EvnPL_id\",
				ED.CmpCallCard_id as \"CmpCallCard_id\",
				EvnPS.EvnPS_RFID as \"EvnPS_RFID\",
				{$this->getScaleFields()}
				to_char(EvnPS.EvnPS_OutcomeDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_OutcomeDT\"
			from
				tab2 ED
				left join v_EvnPS EvnPS on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD on ED.Diag_id= DiagD.Diag_id
				left join v_LpuSection LS on COALESCE(EvnPS.LpuSection_eid,ED.LpuSection_id,ED.LpuSection_did) = LS.LpuSection_id
				left join v_Diag Diag on coalesce(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join pmUserCache puc on coalesce(ED.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_Lpu L on L.Lpu_id = ED.Lpu_id				
				left join v_EvnLink EL on EL.Evn_lid = EvnPS.EvnPS_id
				left join v_EvnPL EPL on EPL.EvnPL_id = EL.Evn_id
				left join v_PrehospArrive PA on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				{$this->getScaleJoins('ED')}
			where
				(1=1)
				and (EvnPS.PrehospStatus_id is null or EvnPS.PrehospStatus_id = 1)
				{$filter_isConfirmed}
				
			union all
			select
				to_char( EQ.EvnQueue_insDT, 'dd.mm.yyyy HH24:MI') as \"insertDT\",
				EST.PrehospStatus_id as \"groupField\",
				EQ.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(Evn.Evn_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				L.Lpu_id as \"Lpu_did\",
				L.Org_id as \"Org_did\",
				ED.DirType_id as \"DirType_id\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				to_char(EQ.EvnQueue_setDT, 'dd.mm.yyyy') as \"EvnQueue_setDate\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				0 as \"IsHospitalized\",
				null as \"EvnPS_id\",
				'' as \"EvnPS_setDT\",
				null as \"EvnPS_NumCard\",
				EQ.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				EQ.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				RTRIM(Diag.Diag_Name) as \"Diag_Name\",
				puc.PMUser_Name as \"MedPersonal_Fin\",
				1 as \"IsRefusal\",
				null as \"PrehospWaifRefuseCause_id\",
				null as \"MedStaffFact_pid\",
				1 as \"IsCall\",
				1 as \"IsSmmp\",
				null as \"PrehospArrive_id\",
				null as \"PrehospArrive_SysNick\",
				null as \"PrehospType_id\",
				null as \"PrehospType_SysNick\",
				{$select_person_data_eq}
				{$select_person_encryp_data}
				EQ.Person_id as \"Person_id\",
				PS.Person_IsUnknown as \"Person_IsUnknown\",
				EQ.PersonEvn_id as \"PersonEvn_id\",
				EQ.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				null as \"EvnPL_id\",
				EPS.CmpCallCard_id as \"CmpCallCard_id\",
				EPS.EvnPS_RFID as \"EvnPS_RFID\",
				{$this->getScaleFields()}
				'' as \"EvnPS_OutcomeDT\"
			from v_EvnDirection ED
			inner join Evn on Evn.Evn_id = ED.EvnDirection_id and Evn.Evn_deleted = 1
				inner join lateral (
					Select * from v_EvnQueue EQ where EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_id = ED.EvnQueue_id limit 1 -- по идее последнее условие необязательно но делает стоимость плана меньше 
					) EQ on true
				left join v_LpuUnit LU on EQ.LpuUnit_did = LU.LpuUnit_id
				left join lateral (select * from v_Person_all PS where EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id limit 1) PS on true
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Diag Diag on coalesce(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
				--left join v_PrehospStatus EST on 1 = EST.PrehospStatus_id
				left join v_PrehospStatus EST on EST.PrehospStatus_id =  CASE WHEN ED.EvnDirection_failDT IS NOT NULL AND Evn.EvnStatus_id = 13 THEN 5 ELSE 1 END
				left join pmUserCache puc on EQ.pmUser_insID = puc.pmUser_id
				left join v_Lpu L on L.Lpu_id = coalesce(ED.Lpu_sid,Evn.Lpu_id)
				left join lateral (
					Select EvnPS_id, CmpCallCard_id, EvnPS_RFID from v_EvnPS EPS where EPS.EvnDirection_id = EQ.EvnDirection_id limit 1--and EPS.Lpu_id = L.Lpu_id
				) EPS on true
				left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_did
				-- нет смысла брать согласие с КВС, при условии в фильтрах AND EPS.EvnPS_id is null
				left join v_PersonLpuInfo on Evn.Lpu_id = v_PersonLpuInfo.Lpu_id and Evn.Person_id = v_PersonLpuInfo.Person_id
				{$this->getScaleJoins('EPS')}
				{$join}
			where
				{$filter_eq}
				--EQ.EvnQueue_failDT is null
				(
					EQ.EvnQueue_failDT is NULL 
					OR 
					Evn.EvnStatus_id = 13 AND cast(:date as date) = CAST(ED.EvnDirection_failDT AS DATE) --показывать в группе ОТКАЗ со статусом отклонено
				)
				AND EQ.EvnQueue_recDT is null
				AND Evn.EvnStatus_id not in (8, 15, 17)
				AND LU.Lpu_id = :Lpu_id
				AND ED.Lpu_did = :Lpu_id -- по идее очередь куда поставили и направление куда направили, должны быть в одну МО
				AND LU.LpuUnitType_id in (1,9,6)
				AND (EQ.EvnDirection_id is null OR EQ.EvnQueue_id is not null)
				AND ED.DirType_id IN (1,2,5,4,6)
				AND EPS.EvnPS_id is null
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnQueue', 0)."

			union all

			select
				to_char( insertDT, 'dd.mm.yyyy') as \"insertDT\",
				groupField as \"groupField\",
				EvnDirection_id as \"EvnDirection_id\",
				PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				Diag_did as \"Diag_did\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				DirType_id as \"DirType_id\",
				to_char(TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EvnPS_NumConv as \"EvnPS_NumConv\",
				to_char(TimetableStac_insDT, 'dd.mm.yyyy HH24:MI') as \"TimetableStac_insDT\",
				to_char(EvnQueue_setDate, 'dd.mm.yyyy') as \"EvnQueue_setDate\",
				null as \"EvnQueue_id\",
				IsHospitalized as \"IsHospitalized\",
				EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS_setDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_setDT\",
				EvnPS_NumCard as \"EvnPS_NumCard\",
				TimetableStac_id as \"TimetableStac_id\",
				LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LpuSectionProfile_did as \"LpuSectionProfile_did\",
				PrehospStatus_id as \"PrehospStatus_id\",
				PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag_id as \"Diag_id\",
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\",
				MedPersonal_Fin as \"MedPersonal_Fin\",
				IsRefusal as \"IsRefusal\",
				PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				MedStaffFact_pid as \"MedStaffFact_pid\",
				IsCall as \"IsCall\",
				IsSmmp as \"IsSmmp\",
				PrehospArrive_id as \"PrehospArrive_id\",
				PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PrehospType_id as \"PrehospType_id\",
				PrehospType_SysNick as \"PrehospType_SysNick\",
				to_char(Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				Person_age as \"Person_age\",
				Person_Fio as \"Person_Fio\",
				PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				Person_id as \"Person_id\",
				Person_IsUnknown as \"Person_IsUnknown\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnPL_id as \"EvnPL_id\",
				CmpCallCard_id as \"CmpCallCard_id\",
				EvnPS_RFID as \"EvnPS_RFID\",
				{$this->getScaleFields(false)}
				to_char(EvnPS_OutcomeDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_OutcomeDT\"
			from tab1
			where
				-- экстр. бирки только с EmergencyDataStatus Койка забронирована на установленную дату
				-- /*AND EmergencyDataStatus_id = 1 */ - описание в комите к #51610
				(TimetableType_id = 6 AND cast(TimetableStac_setDate as date) = cast(:date as date) /*AND EmergencyDataStatus_id = 1 */)

			union all

			select
				to_char( insertDT, 'dd.mm.yyyy') as \"insertDT\",
				groupField as \"groupField\",
				EvnDirection_id as \"EvnDirection_id\",
				PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				Diag_did as \"Diag_did\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				DirType_id as \"DirType_id\",
				to_char(TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				SMMP_exists as \"SMMP_exists\",
				EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EvnPS_NumConv as \"EvnPS_NumConv\",
				to_char(TimetableStac_insDT, 'dd.mm.yyyy HH24:MI') as \"TimetableStac_insDT\",
				to_char(EvnQueue_setDate, 'dd.mm.yyyy') as \"EvnQueue_setDate\",
				EvnQueue_id as \"EvnQueue_id\",
				IsHospitalized as \"IsHospitalized\",
				EvnPS_id as \"EvnPS_id\",
				 to_char(EvnPS_setDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_setDT\",
				EvnPS_NumCard as \"EvnPS_NumCard\",
				TimetableStac_id as \"TimetableStac_id\",
				LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LpuSectionProfile_did as \"LpuSectionProfile_did\",
				PrehospStatus_id as \"PrehospStatus_id\",
				PrehospStatus_Name as \"PrehospStatus_Name\",
				Diag_id as \"Diag_id\",
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\",
				MedPersonal_Fin as \"MedPersonal_Fin\",
				IsRefusal as \"IsRefusal\",
				PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				MedStaffFact_pid as \"MedStaffFact_pid\",
				IsCall as \"IsCall\",
				IsSmmp as \"IsSmmp\",
				PrehospArrive_id as \"PrehospArrive_id\",
				PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PrehospType_id as \"PrehospType_id\",
				PrehospType_SysNick as \"PrehospType_SysNick\",
				to_char(Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				Person_age as \"Person_age\",
				Person_Fio as \"Person_Fio\",
				PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				Person_id as \"Person_id\",
				Person_IsUnknown as \"Person_IsUnknown\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnPL_id as \"EvnPL_id\",
				CmpCallCard_id as \"CmpCallCard_id\",
				EvnPS_RFID as \"EvnPS_RFID\",
				{$this->getScaleFields(false)}
				to_char(EvnPS_OutcomeDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_OutcomeDT\"
			from tab1
			where
				-- в зависимости от фильтра: все или на установленную дату
				(TimetableType_id != 6 AND Person_ttsid is not null {$filter_tts})

			union all

			select
				to_char( EvnPS.EvnPS_insDT, 'dd.mm.yyyy HH24:MI')  as \"insertDT\",
				case 
					when EvnPS.EvnPS_setDate <= cast(:date as date) and datediff('day', EvnPS.EvnPS_setDate, cast(:date as date)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, cast(:date as date) + interval '1 day') > cast(:date as date) then 3
					when (cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND EvnPS.LpuSection_eid IS NOT NULL) then 4
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND lt.LeaveType_Code = '603' then 2" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 5
					when ED.EvnStatus_id = 13 AND ED.EvnDirection_failDT IS NOT NULL AND cast(:date as date) = CAST(ED.EvnDirection_failDT AS DATE) then 5
				end as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				v_PersonLpuInfo.PersonLpuInfo_IsAgree as \"PersonLpuInfo_IsAgree\",
				ED.EvnDirection_IsConfirmed as \"EvnDirection_IsConfirmed\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				EvnPS.Diag_did as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				EvnPS.Lpu_did as \"Lpu_did\",
				EvnPS.Org_did as \"Org_did\",
				ED.DirType_id as \"DirType_id\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"EvnPS_NumConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				cast(null as bigint) as \"EvnQueue_id\",
				case when EvnPS.LpuSection_eid is not null then 1 else 0 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				EvnPS_NumCard as \"EvnPS_NumCard\",
				null as \"TimetableStac_id\",
				'' as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				case 
					when EvnPS.EvnPS_setDate <= cast(:date as date) and datediff('day', EvnPS.EvnPS_setDate, cast(:date as date)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, cast(:date as date) + interval '1 day') > cast(:date as date) then 'Находится в приемном'
					when cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND EvnPS.LpuSection_eid IS NOT NULL then 'Госпитализирован'
					" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "when cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND lt.LeaveType_Code = '603' then 'Принят'" : "") . "
					when cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND EvnPS.PrehospWaifRefuseCause_id IS NOT NULL then 'Отказ'
				end as \"PrehospStatus_Name\",
				Diag.Diag_id as \"Diag_id\",
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				RTRIM(Diag.Diag_Name) as \"Diag_Name\",--Диагноз приемного; код, наименование
				puc.PMUser_Name as \"MedPersonal_Fin\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? " and lt.LeaveType_Code = '602'" : "") . " then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EvnPS.MedStaffFact_pid as \"MedStaffFact_pid\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_SysNick as \"PrehospArrive_SysNick\",
				PT.PrehospType_id as \"PrehospType_id\",
				PT.PrehospType_SysNick as \"PrehospType_SysNick\",
				{$select_person_data_evnps}
				{$select_person_encryp_data}
				EvnPS.Person_id as \"Person_id\",
				PS.Person_IsUnknown as \"Person_IsUnknown\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				EPL.EvnPL_id as \"EvnPL_id\",
				EvnPS.CmpCallCard_id as \"CmpCallCard_id\",
				EvnPS.EvnPS_RFID as \"EvnPS_RFID\",
				{$this->getScaleFields()}
				to_char(EvnPS.EvnPS_OutcomeDT, 'dd.mm.yyyy HH24:MI') as \"EvnPS_OutcomeDT\"
			from v_EvnPS EvnPS
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD on ED.Diag_id= DiagD.Diag_id
				left join lateral (select * from v_Person_all PS where EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id limit 1) PS on true
				left join v_LpuSection LS on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				--left join v_TimetableStac_lite TTS on EvnPS.EvnPS_id = TTS.Evn_id
				left join lateral(
					Select TimetableStac_setDate from v_TimetableStac_lite TTS where EvnPS.EvnPS_id = TTS.Evn_id and EvnPS.EvnDirection_id = TTS.EvnDirection_id limit 1
				) TTS on true
				left join pmUserCache puc on EvnPS.pmUser_updID = puc.pmUser_id
				inner join lateral(
					select
						ES.EvnSection_id as ChildEvn_id,
						ES.EvnSection_setDate
					from
						v_EvnSection ES
					where
						ES.EvnSection_pid = EvnPS.EvnPS_id
						and coalesce(ES.EvnSection_IsPriem, 1) = 1
					limit 1
				) Child on true
				" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "left join lateral (
					select LeaveType_prmid
					from v_EvnSection
					where EvnSection_pid = EvnPS.EvnPS_id
						and EvnSection_IsPriem = 2
					limit 1
				) ESPriem on true
				left join v_LeaveType lt on lt.LeaveType_id = ESPriem.LeaveType_prmid" : "") . "				
				--left join v_EvnLink EL on EL.Evn_lid = EvnPS.EvnPS_id
				--left join v_EvnPL EPL on EPL.EvnPL_id = EL.Evn_id	
				left join lateral(
					Select EvnPL_id from v_EvnLink EL 
					left join v_EvnPL EPL on EPL.EvnPL_id = EL.Evn_id	
					where EL.Evn_lid = EvnPS.EvnPS_id
					limit 1
				) EPL on true
				left join v_PrehospArrive PA on PA.PrehospArrive_id = EvnPS.PrehospArrive_id
				left join v_PrehospType PT on PT.PrehospType_id = EvnPS.PrehospType_id
				left join v_PersonLpuInfo on EvnPS.Lpu_id = v_PersonLpuInfo.Lpu_id and EvnPS.Person_id = v_PersonLpuInfo.Person_id
				{$this->getScaleJoins('EvnPS')}
				{$join}
			where
				EvnPS.Lpu_id = :Lpu_id
				AND EvnPS.LpuSection_pid = :LpuSection_id
				AND EvnPS.EvnPS_setDate between cast(:date as date) - interval '2 days' and cast(:date as date) and coalesce(EvnPS.EvnPS_OutcomeDT,cast(:date as date))>=cast(:date as date)
				/*AND ((
					--со статусом Находится в приемном - история болезни создана установленным днем и дата исхода пустая или позже
					-- #57215
					EvnPS.EvnPS_setDate <= cast(:date as date) and datediff('day', EvnPS.EvnPS_setDate, cast(:date as date)) < 3 and coalesce(cast(EvnPS.EvnPS_OutcomeDT as date), Child.EvnSection_setDate, cast(:date as date) + interval '1 day') > cast(:date as date)
				)
				OR (
					--со статусом Отказ на установленную дату или со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = cast(:date as date) AND COALESCE(" . (in_array($this->regionNick, array('buryatiya', 'pskov')) ? "ESPriem.LeaveType_prmid, " : "") . "EvnPS.PrehospWaifRefuseCause_id,  EvnPS.LpuSection_eid) IS NOT NULL)
				)
				OR (
					--со статусом Госпитализирован на установленную дату
					(cast(EvnPS.EvnPS_OutcomeDT as date) = -date AND EvnPS.LpuSection_eid IS NOT NULL)
				)
				)*/
				{$filter}
				{$filter_isConfirmed}
				".$this->genLpuSectionServiceFilter('EvnPS', 0)."

			order by
				\"insertDT\" DESC
		";

		//echo getDebugSql($sql, $params);exit;
		$res = $this->queryResult($sql, $params);
		if (!empty($res)) {
			$grouptypes = $this->queryResult("
				select PrehospStatus_id as \"PrehospStatus_id\", PrehospStatus_Name as \"PrehospStatus_Name\" from v_PrehospStatus
			", array());

			$result = array();
			foreach ($grouptypes as $group) {
				$result[$group['PrehospStatus_id']] = array(
					'group_id' => $group['PrehospStatus_id'],
					'group_title' => $group['PrehospStatus_Name'],
					'group_data' => (object)array(),
					'patients' => array()
				);
			}

			foreach ($res as $item) {
				$result[$item['PrehospStatus_id']]['patients'][] = $item;
			}
			return array_values($result);
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkEvnPSDoubles($data) {
		$where = '';

		//На Астрахани если первое движение в дневном стационаре, то не учитывать время
		if (getRegionNick() == 'astra' && in_array($data['LpuUnitType_SysNick'], array('dstac','pstac'))) {
			$where .= "
				and EPS.EvnPS_setDate <= :EvnPS_setDate
				and (EPS.EvnPS_disDate is null or EPS.EvnPS_disDate > :EvnPS_setDate)
			";
		} else {
			$where .= "
				and (
					(
						EPS.EvnPS_setDT <= CAST(:EvnPS_setDate as timestamp) -- дата начала до текущего
						and (
							EPS.EvnPS_disDT is null -- и дата конца не задана
							or EPS.EvnPS_disDT > CAST(:EvnPS_setDate as timestamp) -- либо дата конца после начала текущего
						)
					)
					or
					(
						EPS.EvnPS_setDT >= CAST(:EvnPS_setDate as timestamp) -- дата начала до
						and (
							:EvnPS_disDate is null -- и дата конца не задана
							or CAST(:EvnPS_disDate as timestamp) > EPS.EvnPS_setDT -- либо дата конца после начала
						)
					)
				)
			";
			if ( !empty($data['EvnPS_setDate']) && !empty($data['EvnPS_setTime']) ) {
				$data['EvnPS_setDate'] .= ' ' . $data['EvnPS_setTime'];
			}
			if ( !empty($data['EvnPS_disDate']) && !empty($data['EvnPS_disTime']) ) {
				$data['EvnPS_disDate'] .= ' ' . $data['EvnPS_disTime'];
			}
		}

		if (!empty($data['PayType_id'])) {
			$where .= "
				and EPS.PayType_id = :PayType_id
			";
		}

		$query = "
			select
				to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				case
					when EPS.Lpu_id = :Lpu_id then 'inner'
					when EPS.Lpu_id <> :Lpu_id then 'outer'
				end as \"intersect_type\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Nick\"
			from
				v_EvnPS EPS 
				inner join v_Lpu Lpu  on Lpu.Lpu_id = EPS.Lpu_id
				left join v_EvnSection ES  on ES.EvnSection_rid = EPS.EvnPS_id
				left join v_LpuSectionProfile LSP  on ES.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			where
				EPS.EvnPS_id <> coalesce(:EvnPS_id::bigint, 0)
				{$where}
				and EPS.Person_id = :Person_id
				and EPS.PrehospWaifRefuseCause_id is null
				and COALESCE(ES.EvnSection_IsPriem, 1) = 1
		";

		$queryParams = array(
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'EvnPS_setDate' => $data['EvnPS_setDate'],
			'EvnPS_disDate' => $data['EvnPS_disDate'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'PayType_id' => !empty($data['PayType_id'])?$data['PayType_id']:null,
		);

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkEvnPSDoublesByNum($data) {
		$queryParams = array(
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'EvnPS_IsCont' => $data['EvnPS_IsCont'],
			'EvnPS_NumCard' => $data['EvnPS_NumCard'],
			'Lpu_id' => $data['Lpu_id']
		);
		$filter = "";
		if (in_array($this->regionNick, array('ekb','kareliya','krym','perm','astra','kz'))) {
			$filter .= " and date_part('year', EvnPS_setDT) = date_part('year', CAST(:EvnPS_setDT as date))";
			$queryParams['EvnPS_setDT'] = $data['EvnPS_setDT'];
		}

		$query = "
			select 
				EPS.EvnPS_id as \"EvnPS_id\"
			from
				v_EvnPS EPS 
			where
				EPS.Lpu_id = :Lpu_id 
				and EPS.EvnPS_IsCont = 1 -- ищем среди тех, где продолжение случая = НЕТ
				and 1 = :EvnPS_IsCont -- и добавляемый тоже не должен быть продолжением случая
				and EPS.EvnPS_id <> :EvnPS_id
				and EPS.EvnPS_NumCard = :EvnPS_NumCard
				{$filter}
			limit 1
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkEvnPSCrossEvnPL($data) {
		$query = "
			select
				COUNT(case when EVPL.Lpu_id = :Lpu_id then EVPL.EvnVizitPL_id else null end) as \"int_count\",
				COUNT(case when EVPL.Lpu_id <> :Lpu_id then EVPL.EvnVizitPL_id else null end) as \"ext_count\"
			from
				v_EvnVizitPL EVPL 
			where
				EVPL.EvnVizitPL_setDate = CAST(:EvnPS_setDate as timestamp)
				and EPS.Person_id = :Person_id
		";

		$queryParams = array(
			'EvnPS_setDate' => $data['EvnPS_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkEvnSectionDates($data) {
		$query = "
			select
				case when ESNext.EvnSection_setDT is not null and ES.EvnSection_disDT != ESNext.EvnSection_setDT then 1 else 0 end as \"disDateIsIncorrect\"
			from
				v_EvnSection ES 
				LEFT JOIN LATERAL (
					select EvnSection_setDT
					from v_EvnSection 
					where EvnSection_pid = :EvnSection_pid
						and EvnSection_setDT > ES.EvnSection_setDT
						and COALESCE(EvnSection_IsPriem, 1) = 1
					order by EvnSection_setDT
					limit 1
				) ESNext ON true
			where ES.EvnSection_pid = :EvnSection_pid
				and COALESCE(ES.EvnSection_IsPriem, 1) = 1
		";

		$queryParams = array(
			'EvnSection_pid' => $data['EvnPS_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// Проверки возможности удалить КВС
		if ( !isSuperAdmin() && !isLpuAdmin($this->Lpu_id) ) {
			if ( $this->sessionParams['isMedStatUser'] == false ) {
				if ($this->promedUserId != $this->pmUser_insID) {
					throw new Exception('Вы не можете удалить КВС, которая добавлена другим пользователем');
				}
				if ( isset($this->sessionParams['CurMedStaffFact_id']) ) {
					$sys_nick = $this->getFirstResultFromQuery('
						select
							LU.LpuUnitType_SysNick as "LpuUnitType_SysNick"
						from v_MedStaffFact MSF 
						left join v_LpuUnit LU  on MSF.LpuUnit_id = LU.LpuUnit_id
						where MSF.MedStaffFact_id = :MedStaffFact_id
						limit 1
					', array('MedStaffFact_id' => $this->sessionParams['CurMedStaffFact_id']));
					if (!in_array($sys_nick, array('stac','dstac','hstac','pstac','priem'))) {
						throw new Exception('Удалить КВС может только врач стационара (или приемного покоя)');
					}
				}
			}
		}
		// Проверка использования ЛВН в КВС
		// https://redmine.swan.perm.ru/issues/5992
		// Пункт 5
		$this->load->model('Stick_model');
		$response = $this->Stick_model->checkEvnDeleteAbility(array('Evn_id' => $this->id));
		if ( is_array($response) && count($response) > 0 ) {
			$error = '<div>Удаление КВС невозможно, документ содержит ЛВН ';
			$first = true;
			foreach ( $response as $array ) {
				if(!$first) { $error .= ", "; }
				$error .= "№". $array['EvnStick_Ser'] . " " . $array['EvnStick_Num'] . " дата "	. $array['EvnStick_setDate'];
				$first = false;
			}
			$error .= "</div>";
			throw new Exception($error);
		}
		// при удалении КВС пациента принятого из очереди поправить запись о постановке в очередь
		$this->load->model('EvnDirectionAll_model');
		$response = $this->EvnDirectionAll_model->onBeforeDeleteEvnPS($this);
		if ( !empty($response['Error_Msg']) ) {
			throw new Exception($response['Error_Msg'], $response['Error_Code']);
		}

		// Проверка использования медикаментов в КВС
		if (empty($data['ignoreEvnDrug'])) {
			$this->load->model('EvnDrug_model');
			$response = $this->EvnDrug_model->loadEvnDrugGrid(array('EvnDrug_pid' => $this->id));
			if ( is_array($response) && count($response) > 0 ) {
				$this->_saveResponse['Alert_Msg'] = 'Случай лечения содержит документы использования медикаментов. При удалении случая лечения данные по медикаментам  удалятся.  Продолжить удаление?';
				throw new Exception('YesNo', 702);
			}
		}
		if ($this->personNewBornId) {
			//Уберает из специфики новорожденного ссылку на КВС
			$this->load->model('PersonNewBorn_model');
			$resp = $this->PersonNewBorn_model->setPersonNewBornEvnPS(array(
				'PersonNewBorn_id' => $this->personNewBornId,
				'EvnPS_id' => null
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		}

		$BirthSpecStacList = $this->queryResult("
			select BSS.BirthSpecStac_id as \"BirthSpecStac_id\"
			from v_EvnSection ES
			inner join v_BirthSpecStac BSS on BSS.EvnSection_id = ES.EvnSection_id
			where ES.EvnSection_pid = :EvnSection_pid
		", array(
			'EvnSection_pid' => $this->id
		), true);
		if (!is_array($BirthSpecStacList)) {
			throw new Exception('Ошибка при получении исхода беременности');
		}
		foreach($BirthSpecStacList as $BirthSpecStac) {
			$this->load->model('PersonPregnancy_model');
			$resp = $this->PersonPregnancy_model->deleteBirthSpecStac(array(
				'BirthSpecStac_id' => $BirthSpecStac['BirthSpecStac_id'],
				'pmUser_id' => $this->promedUserId,
				'session' => $this->sessionParams
			), false);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}
	}

	/**
	 * Получение специфики новорожденного, связанной с КВС, если такая есть
	 */
	function getPersonNewBornId() {
		if (empty($this->_personNewBorn_id) && !empty($this->id)) {
			$result = $this->getFirstResultFromQuery("
				select PersonNewBorn_id as \"PersonNewBorn_id\" from PersonNewBorn  where EvnPS_id = :EvnPS_id limit 1
			", array('EvnPS_id' => $this->id), true);
			if ($result === false) {
				throw new Exception('Ошибка при получении специфики новорожденного, связанной с КВС');
			}
			$this->_personNewBorn_id = $result;
		}
		return $this->_personNewBorn_id;
	}

	/**
	 * После удаления
	 */
	protected function _afterDelete($result) {
        parent::_afterDelete($result);

        $this->load->model('ApprovalList_model');
        $this->ApprovalList_model->deleteApprovalList(array(
            'ApprovalList_ObjectName' => 'EvnPS',
            'ApprovalList_ObjectId' => $this->id
        ));
        
		if ($this->regionNick == 'astra') {
			// выполняем переидентификацию.
			if (!empty($this->evndirection_id)) {
				$this->load->model('EvnDirectionExt_model');
				$response = $this->EvnDirectionExt_model->reidentEvnDirectionExt(array(
					'EvnDirection_id' => $this->evndirection_id,
					'pmUser_id' => $this->promedUserId
				));
				if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg']);
				}
			}
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPS($data, $isAllowTransaction = true)
	{
		return array($this->doDelete($data, $isAllowTransaction));
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDiagPSList($data) {
		$query = "
			select
				EDPS.EvnDiagPS_pid as \"EvnDiagPS_pid\",
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				RTRIM(Diag.Diag_Name) as \"Diag_Name\",
				DSC.DiagSetClass_Code as \"DiagSetClass_Code\",
				DST.DiagSetType_Code as \"DiagSetType_Code\",
				RTRIM(DSC.DiagSetClass_Name) as \"DiagSetClass_Name\"
			from v_EvnDiagPS EDPS 
				inner join Diag  on Diag.Diag_id = EDPS.Diag_id
				inner join DiagSetClass DSC  on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
					and DSC.DiagSetClass_Code in (2, 3)
				inner join DiagSetType DST  on DST.DiagSetType_id = EDPS.DiagSetType_id
			where EDPS.EvnDiagPS_rid = :EvnPS_id
				and EDPS.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка движений в КВС с подсчетом количества услуг, оказанных в рамках движения
	 *	Реализовано в рамках задачи https://redmine.swan.perm.ru/issues/12361
	 */
	function checkEvnUslugaConformity($data) {
		$query = "
			select
				 to_char(ES.EvnSection_setDT, 'DD.MM.YYYY') as \"EvnSection_setDate\"
				,LS.LpuSection_Name as \"LpuSection_Name\"
				,EUC.cnt as \"evnUslugaCount\"
			from v_EvnSection ES 
				inner join LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
				LEFT JOIN LATERAL (
					select count(EvnUsluga_id) as cnt
					from v_EvnUsluga 
					where EvnUsluga_pid = ES.EvnSection_id
				) EUC ON true
			where
				ES.EvnSection_pid = :EvnPS_id
		";
		$result = $this->db->query($query, array('EvnPS_id' => $data['EvnPS_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных для создания копии КВС
	 */
	function getEvnPSCopyData($data) {
		$query = "
			select
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				EPS.EvnPS_IsCont as \"EvnPS_IsCont\",
				EPS.Diag_aid as \"Diag_aid\",
				EPS.Diag_pid as \"Diag_pid\",
				EPS.Diag_did as \"Diag_did\",
				EPS.EvnDirection_id as \"EvnDirection_id\",
				EPS.PrehospArrive_id as \"PrehospArrive_id\",
				EPS.PrehospDirect_id as \"PrehospDirect_id\",
				EPS.PrehospToxic_id as \"PrehospToxic_id\",
				EPS.LpuSectionTransType_id as \"LpuSectionTransType_id\",
				EPS.PayType_id as \"PayType_id\",
				EPS.PrehospTrauma_id as \"PrehospTrauma_id\",
				EPS.PrehospType_id as \"PrehospType_id\",
				EPS.Lpu_did as \"Lpu_did\",
				EPS.Org_did as \"Org_did\",
				EPS.LpuSection_did as \"LpuSection_did\",
				EPS.OrgMilitary_did as \"OrgMilitary_did\",
				EPS.LpuSection_pid as \"LpuSection_pid\",
				EPS.MedPersonal_pid as \"MedPersonal_pid\",
				EPS.EvnPS_setTime as \"EvnPS_setTime\",
				EPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EPS.EvnDirection_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnDirection_setDate\",
				EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EPS.EvnPS_NumConv as \"EvnPS_NumConv\",
				EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				EPS.Okei_id as \"Okei_id\",
				EPS.EvnPS_HospCount as \"EvnPS_HospCount\",
				EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\",
				EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\",
				EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\",
				EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\",
				EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\",
				EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\",
				COALESCE(LT.LeaveType_Code, 0) as \"LeaveType_Code\",
				EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\",
				EPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\",
				COALESCE(EPS.EvnPS_IsWaif, 1) as \"EvnPS_IsWaif\",
				-- EPS.LpuSection_id as \"LpuSection_id\",
				EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EPS.ResultClass_id as \"ResultClass_id\",
				EPS.ResultDeseaseType_id as \"ResultDeseaseType_id\",
				EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\",
				EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\"
			from v_EvnPS EPS 
				inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
				left join LeaveType LT  on LT.LeaveType_id = EPS.LeaveType_id
			where EPS.EvnPS_id = :EvnPS_id
				and EPS.Lpu_id = :Lpu_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( count($response) > 0 ) {
				return $response[0];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool]
	 */
	function getEvnPSFields($data) {

		if (empty($data['EvnPS_id'])){
			//var_dump($data);
			//return false;
			$where = ' and EPS.EvnPS_id = (select EvnSection_pid from v_EvnSection  where EvnSection_id = :EvnSection_id)';
		}
		else{
			$where = ' and EPS.EvnPS_id = :EvnPS_id';
		}
		if(!isTFOMSUser() && !isOuzSpec() && empty($data['session']['medpersonal_id'])){
			$where.=' and EPS.Lpu_id = :Lpu_id';
		}
		$query = "
			select
				RTRIM(COALESCE(AnatomWhere.AnatomWhere_Name, '')) as \"AnatomWhere_Name\",
				RTRIM(COALESCE(DiagA.Diag_Code, '')) as \"DiagA_Code\",
				RTRIM(COALESCE(DiagA.Diag_Name, '')) as \"DiagA_Name\",
				RTRIM(COALESCE(DiagH.Diag_Code, '')) as \"DiagH_Code\",
				RTRIM(COALESCE(DiagH.Diag_Name, '')) as \"DiagH_Name\",
				RTRIM(COALESCE(DiagP.Diag_Code, '')) as \"DiagP_Code\",
				RTRIM(COALESCE(DiagP.Diag_Name, '')) as \"DiagP_Name\",
				COALESCE(to_char(Document.Document_begDate, 'DD.MM.YYYY'), '') as \"Document_begDate\",
				RTRIM(COALESCE(Document.Document_Num, '')) as \"Document_Num\",
				RTRIM(COALESCE(Document.Document_Ser, '')) as \"Document_Ser\",
				RTRIM(COALESCE(DocumentType.DocumentType_Name, '')) as \"DocumentType_Name\",
				EPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EPS.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				to_char(EPS.EvnPS_disTime, 'HH24:MI') as \"EvnPS_disTime\",
				EPS.EvnPS_HospCount as \"EvnPS_HospCount\",
				to_char(ED.EvnDie_expDate, 'DD.MM.YYYY') as \"EvnDie_expDate\",
				to_char(ED.EvnDie_expTime, 'HH24:MI') as \"EvnDie_expTime\",
				COALESCE(IsAmbul.YesNo_Code, 0) as \"EvnLeave_IsAmbul\",
				COALESCE(IsWait.YesNo_Code, 0) as \"EvnDie_IsWait\",
				COALESCE(IsAnatom.YesNo_Code, 0) as \"EvnDie_IsAnatom\",
				COALESCE(IsDiagMismatch.YesNo_Code, 0) as \"EvnPS_IsDiagMismatch\",
				COALESCE(IsImperHosp.YesNo_Code, 0) as \"EvnPS_IsImperHosp\",
				COALESCE(IsShortVolume.YesNo_Code, 0) as \"EvnPS_IsShortVolume\",
				COALESCE(IsUnlaw.YesNo_Code, 0) as \"EvnPS_IsUnlaw\",
				COALESCE(IsUnport.YesNo_Code, 0) as \"EvnPS_IsUnport\",
				COALESCE(IsWrongCure.YesNo_Code, 0) as \"EvnPS_IsWrongCure\",
				COALESCE(EPS.EvnPS_CodeConv, '') as \"EvnPS_CodeConv\",
				COALESCE(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\",
				COALESCE(EPS.EvnPS_NumConv, '') as \"EvnPS_NumConv\",
				to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_setTime, 'HH24:MI') as \"EvnPS_setTime\",
				EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				Okei.Okei_NationSymbol as \"Okei_NationSymbol\",
				COALESCE(EL.EvnLeave_UKL, ED.EvnDie_UKL, EOL.EvnOtherLpu_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL, EOST.EvnOtherStac_UKL) as \"EvnLeave_UKL\",
				RTRIM(COALESCE(L.Lpu_Name, '')) as \"Lpu_Name\",
				RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name, '')) as \"PrehospOrg_Name\",
				RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				RTRIM(COALESCE(OSTLS.LpuSection_Name, '')) as \"OtherStac_Name\",
				RTRIM(COALESCE(OSTLUT.LpuUnitType_Name, '')) as \"OtherStacType_Name\",
				RTRIM(COALESCE(LpuRegion.LpuRegion_Name, '')) as \"LpuRegion_Name\",
				RTRIM(COALESCE(OD.Org_Name, '')) as \"OrgDep_Name\",
				RTRIM(COALESCE(OJ.Org_Name, '')) as \"OrgJob_Name\",
				RTRIM(COALESCE(OS.Org_Name, '')) as \"OrgSmo_Name\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\",
				RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",
				RTRIM(COALESCE(PAddr.Address_Address, '')) as \"PAddress_Name\",
				RTRIM(COALESCE(UAddr.Address_Address, '')) as \"UAddress_Name\",
				RTRIM(COALESCE(KLAreaType.KLAreaType_Name, '')) as \"KLAreaType_Name\",
				RTRIM(COALESCE(LeaveCause.LeaveCause_Name, OLC.LeaveCause_Name, OSC.LeaveCause_Name, OSTC.LeaveCause_Name)) as \"LeaveCause_Name\",
				RTRIM(COALESCE(LeaveType.LeaveType_Name, '')) as \"LeaveType_Name\",
				RTRIM(COALESCE(MPRec.Person_Fio, '')) as \"PreHospMedPersonal_Fio\",
				RTRIM(COALESCE(EDAMP.MedPersonal_TabCode, '')) as \"AnatomMedPersonal_Code\",
				RTRIM(COALESCE(EDAMP.Person_Fio, '')) as \"AnatomMedPersonal_Fio\",
				RTRIM(COALESCE(EDMP.MedPersonal_TabCode, '')) as \"EvnDieMedPersonal_Code\",
				RTRIM(COALESCE(EDMP.Person_Fin, '')) as \"EvnDieMedPersonal_Fin\",
				RTRIM(COALESCE(OLC.LeaveCause_Name, '')) as \"OtherLpuCause_Name\",
				RTRIM(COALESCE(OSC.LeaveCause_Name, '')) as \"OtherSectionCause_Name\",
				RTRIM(COALESCE(OSCBP.LeaveCause_Name, '')) as \"OtherSectionBPCause_Name\",
				RTRIM(COALESCE(OSTC.LeaveCause_Name, '')) as \"OtherStacCause_Name\",
				RTRIM(COALESCE(OtherLpu.Org_Name, '')) as \"OtherLpu_Name\",
				to_char(Polis.Polis_begDate, 'DD.MM.YYYY') as \"Polis_begDate\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(COALESCE(Polis.Polis_Ser, '')) END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then COALESCE(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(COALESCE(Polis.Polis_Num, '')) END as \"Polis_Num\",
				RTRIM(COALESCE(PolisType.PolisType_Name, '')) as \"PolisType_Name\",
				RTRIM(COALESCE(Post.Post_Name, '')) as \"Post_Name\",
				RTRIM(COALESCE(PayType.PayType_Name, '')) as \"PayType_Name\",
				RTRIM(COALESCE(PHT.PrehospTrauma_Name, '')) as \"PrehospTrauma_Name\",
				RTRIM(COALESCE(PrehospArrive.PrehospArrive_Name, '')) as \"PrehospArrive_Name\",
				RTRIM(COALESCE(PrehospDirect.PrehospDirect_Name, '')) as \"PrehospDirect_Name\",
				RTRIM(COALESCE(PrehospToxic.PrehospToxic_Name, '')) as \"PrehospToxic_Name\",
				RTRIM(COALESCE(LpuSectionTransType.LpuSectionTransType_Name, '')) as \"LpuSectionTransType_Name\",
				RTRIM(COALESCE(PrehospType.PrehospType_Name, '')) as \"PrehospType_Name\",
				RTRIM(COALESCE(ResultDesease.ResultDesease_Name, '')) as \"ResultDesease_Name\",
				RTRIM(COALESCE(Sex.Sex_Name, '')) as \"Sex_Name\",
				RTRIM(COALESCE(SocStatus.SocStatus_Name, '')) as \"SocStatus_Name\",
				RTRIM(COALESCE(InvalidType.InvalidType_begDate, '')) as \"InvalidType_begDate\",
				COALESCE(InvalidType.InvalidType_Code, 0) as \"InvalidType_Code\",
				RTRIM(COALESCE(PersonPrivilege.PrivilegeType_Name, '')) as \"PrivilegeType_Name\",
				to_char(COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOSBP.EvnOtherSectionBedProfile_setDate, EOST.EvnOtherStac_setDate), 'DD.MM.YYYY') as \"EvnPS_disDate\",
				COALESCE(EL.EvnLeave_setTime, ED.EvnDie_setTime, EOL.EvnOtherLpu_setTime, EOS.EvnOtherSection_setTime, EOSBP.EvnOtherSectionBedProfile_setTime, EOST.EvnOtherStac_setTime) as \"EvnPS_disTime\",
				RTRIM(COALESCE(EvnUdost.EvnUdost_Ser, '') || ' ' || COALESCE(EvnUdost.EvnUdost_Num, '')) as \"EvnUdost_SerNum\",
				RTRIM(COALESCE(AnatomLpu.Lpu_Name, AnatomLS.LpuSection_Name, AnatomOrg.OrgAnatom_Name, '')) as \"EvnAnatomPlace\",
				COALESCE(EvnSection.LpuUnitType_Code, 0) as \"LpuUnitType_Code\",
				EPS.EntranceModeType_id as \"EntranceModeType_id\",
				COALESCE(OST.OMSSprTerr_Code, 0) as \"OmsSprTerr_Code\",
				RTRIM(COALESCE(OST.OMSSprTerr_Name, '')) as \"OmsSprTerr_Name\",
				PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\"
			from v_EvnPS EPS 
				inner join v_Lpu L  on L.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
					-- PS.Server_id = EPS.Server_id and PS.PersonEvn_id = EPS.PersonEvn_id
				left join v_EvnSection ESLast  on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join Address UAddr  on UAddr.Address_id = PS.UAddress_id
				left join Address PAddr  on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType  on KLAreaType.KLAreaType_id = PAddr.KLAreaType_id
				left join Document  on Document.Document_id = PS.Document_id
				left join DocumentType  on DocumentType.DocumentType_id = Document.DocumentType_id
				left join LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_pid
				left join LpuSection PHLS  on PHLS.LpuSection_id = EPS.LpuSection_did
				left join OrgDep  on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OD  on OD.Org_id = OrgDep.Org_id
				left join Org PHO  on PHO.Org_id = EPS.Org_did
				left join v_OrgMilitary PHOM  on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join Job  on Job.Job_id = PS.Job_id
				left join Org OJ  on OJ.Org_id = Job.Org_id
				left join v_PersonCard PC  on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (COALESCE(PC.PersonCard_endDate, EPS.EvnPS_insDT+interval '1 day') > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join LpuRegion  on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Post  on Post.Post_id = Job.Post_id
				left join Polis  on Polis.Polis_id = PS.Polis_id
				left join PolisType  on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OmsSprTerr OST  on OST.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join OrgSmo  on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS  on OS.Org_id = OrgSmo.Org_id
				left join v_EvnLeave EL  on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED  on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL  on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherSection EOS  on EOS.EvnOtherSection_pid = ESLast.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP  on EOSBP.EvnOtherSectionBedProfile_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST  on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join Diag DiagH  on DiagH.Diag_id = EPS.Diag_did
				left join Diag DiagP  on DiagP.Diag_id = EPS.Diag_pid
				left join Diag DiagA  on DiagA.Diag_id = ED.Diag_aid
				left join AnatomWhere  on AnatomWhere.AnatomWhere_id = ED.AnatomWhere_id
				left join LeaveCause  on LeaveCause.LeaveCause_id = EL.LeaveCause_id
				left join LeaveCause OSC  on OSC.LeaveCause_id = EOS.LeaveCause_id
				left join LeaveCause OSCBP  on OSCBP.LeaveCause_id = EOSBP.LeaveCause_id
				left join LeaveCause OSTC  on OSTC.LeaveCause_id = EOST.LeaveCause_id
				left join LeaveCause OLC  on OLC.LeaveCause_id = EOL.LeaveCause_id
				left join LeaveType  on LeaveType.LeaveType_id = EPS.LeaveType_id
				left join v_Org OtherLpu  on OtherLpu.Org_id = EOL.Org_oid
				left join v_Lpu PreHospLpu  on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_MedPersonal MPRec  on MPRec.MedPersonal_id = EPS.MedPersonal_pid
					and MPRec.Lpu_id = EPS.Lpu_id
				left join v_MedPersonal EDMP  on EDMP.MedPersonal_id = ED.MedPersonal_id
					and EDMP.Lpu_id = ED.Lpu_id
				left join v_MedPersonal EDAMP  on EDAMP.MedPersonal_id = ED.MedPersonal_aid
					and EDAMP.Lpu_id = ED.Lpu_id
				left join v_Lpu AnatomLpu  on AnatomLpu.Lpu_id = ED.Lpu_aid
				left join v_OrgAnatom AnatomOrg  on AnatomOrg.OrgAnatom_id = ED.OrgAnatom_id
				left join LpuSection AnatomLS  on AnatomLS.LpuSection_id = ED.LpuSection_aid
				left join LpuUnitType OSTLUT  on OSTLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join LpuSection OSTLS  on OSTLS.LpuSection_id = EOST.LpuSection_oid
				left join PayType  on PayType.PayType_id = EPS.PayType_id
				left join PrehospArrive  on PrehospArrive.PrehospArrive_id = EPS.PrehospArrive_id
				left join PrehospDirect  on PrehospDirect.PrehospDirect_id = EPS.PrehospDirect_id
				left join PrehospToxic  on PrehospToxic.PrehospToxic_id = EPS.PrehospToxic_id
				left join LpuSectionTransType  on LpuSectionTransType.LpuSectionTransType_id = EPS.LpuSectionTransType_id
				left join v_PrehospTrauma PHT  on PHT.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join PrehospType  on PrehospType.PrehospType_id = EPS.PrehospType_id
				left join ResultDesease  on ResultDesease.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id)
				left join Sex  on Sex.Sex_id = PS.Sex_id
				left join SocStatus  on SocStatus.SocStatus_id = PS.SocStatus_id
				left join YesNo IsAmbul  on IsAmbul.YesNo_id = EL.EvnLeave_IsAmbul
				left join YesNo IsWait  on IsWait.YesNo_id = ED.EvnDie_IsWait
				left join YesNo IsAnatom  on IsAnatom.YesNo_id = ED.EvnDie_IsAnatom
				left join YesNo IsDiagMismatch  on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
				left join YesNo IsImperHosp  on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
				left join YesNo IsShortVolume  on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
				left join YesNo IsUnlaw  on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
				left join YesNo IsUnport  on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
				left join YesNo IsWrongCure  on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
				left join v_Okei Okei  on Okei.Okei_id = EPS.Okei_id
				left join v_PersonEncrypHIV PEH  on PEH.Person_id = PS.Person_id
				LEFT JOIN LATERAL (
					select
						PrivilegeType_Code as InvalidType_Code,
						to_char(PersonPrivilege_begDate, 'DD.MM.YYYY') as InvalidType_begDate
					from
						v_PersonPrivilege 
					where PrivilegeType_Code in ('81', '82', '83')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) InvalidType ON true
				LEFT JOIN LATERAL (
					select
						PrivilegeType_Name
					from
						v_PersonPrivilege 
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) PersonPrivilege ON true
				LEFT JOIN LATERAL (
					select
						EvnUdost_Num,
						EvnUdost_Ser
					from
						v_EvnUdost 
					where EvnUdost_setDate <= dbo.tzGetDate()
						and Person_id = PS.Person_id
					order by EvnUdost_setDate desc
					limit 1
				) EvnUdost ON true
				LEFT JOIN LATERAL (
					select
						LUT2.LpuUnitType_Code
					from
						v_EvnSection ES2 
						inner join LpuSection LS2  on LS2.LpuSection_id = ES2.LpuSection_id
						inner join LpuUnit LU2  on LU2.LpuUnit_id = LS2.LpuUnit_id
						inner join LpuUnitType LUT2  on LUT2.LpuUnitType_id = LU2.LpuUnitType_id
							and LUT2.LpuUnitType_Code in (2, 3, 4, 5)
					where ES2.EvnSection_pid = EPS.EvnPS_id
					order by ES2.EvnSection_setDate desc
					limit 1
				) EvnSection ON true
			where
				(1=1) 
			".$where."
			limit 1
		";
		//EPS.EvnPS_id = :EvnPS_id
		//";
		//echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); exit();

		if (is_null($data['EvnPS_id'])){
			$result = $this->db->query($query, array(
				'EvnSection_id' => $data['EvnSection_id'],
				'Lpu_id' => $data['Lpu_id']
			));
		}
		else{
			$result = $this->db->query($query, array(
				'EvnPS_id' => $data['EvnPS_id'],
				'Lpu_id' => $data['Lpu_id']
			));
		}
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function Check_OtherDep ($data) {  //Проверка, был ли перевод в другое отделение в предыдущем движении
		$query = "SELECT
					   to_char(ES.EvnSection_setDate, 'DD.MM.YYYY') as \"EvnSection_setDate\"
					   ,to_char(ES.EvnSection_setTime, 'HH24:MI') as \"EvnSection_setTime\"
					   ,LS.LpuSection_Name as \"LpuSection_Name\"
					   ,M.Post_Name as \"Post_Name\"
				FROM v_EvnSection ES 
				LEFT JOIN  EvnPS EP  ON EP.EvnPS_id = ES.EvnSection_pid
				LEFT JOIN v_EvnSection ES_Prev  ON ( ES_Prev.EvnSection_pid = EP.EvnPS_id AND ES_Prev.EvnSection_Index = ES.EvnSection_Index-1)
				LEFT JOIN LpuSection LS  ON LS.LpuSection_id = ES_Prev.LpuSection_id
				LEFT JOIN MedPersonalCache M  ON M.MedPersonal_id = ES.MedPersonal_id
				WHERE ES.EvnSection_id = :EvnSection_id
				AND   ES_Prev.LeaveType_id = '5'
				limit 1
				";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id']
		));
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function GetHosp_Result($data) {
		//var_dump($data);
		//return false;
		if (($data['KVS_Type'] == 'VG') || ($data['KVS_Type'] == 'V') || ($data['KVS_Type'] == 'G'))
			$where = 'ES.EvnSection_id = :EvnSection_id';
		else
			$where = 'ES.EvnSection_pid = :EvnPS_id
				and ES.EvnSection_Index = ES.EvnSection_Count - 1';

		$query = "select
				COALESCE(LT.LeaveType_Name, '') as \"LeaveType_Name\"
				,to_char(ES.EvnSection_disDate, 'DD.MM.YYYY') as \"EvnSection_disDate\"
				,to_char(ES.EvnSection_disTime, 'HH24:MI') as \"EvnSection_disTime\"
				,COALESCE(EL.EvnLeave_UKL, ED.EvnDie_UKL, EOL.EvnOtherLpu_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL, EOST.EvnOtherStac_UKL) as \"EvnLeave_UKL\"
				,RTRIM(COALESCE(ResultDesease.ResultDesease_Name, '')) as \"ResultDesease_Name\"
				,RTRIM(COALESCE(LeaveCause.LeaveCause_Name, OLC.LeaveCause_Name, OSC.LeaveCause_Name, OSTC.LeaveCause_Name)) as \"LeaveCause_Name\"
				,COALESCE(IsAmbul.YesNo_Code, 0) as \"EvnLeave_IsAmbul\"
				,COALESCE(IsWait.YesNo_Code, 0) as \"EvnDie_IsWait\"
				,COALESCE(IsAnatom.YesNo_Code, 0) as \"EvnDie_IsAnatom\"
				,RTRIM(COALESCE(OtherLpu.Org_Name, '')) as \"OtherLpu_Name\"
				,RTRIM(COALESCE(OSTLS.LpuSection_Name, '')) as \"OtherStac_Name\"
				,RTRIM(COALESCE(OSTLUT.LpuUnitType_Name, '')) as \"OtherStacType_Name\"
				,RTRIM(COALESCE(EDMP.MedPersonal_TabCode, '')) as \"EvnDieMedPersonal_Code\"
				,RTRIM(COALESCE(EDMP.Person_Fin, '')) as \"EvnDieMedPersonal_Fin\"
				,to_char(ED.EvnDie_expDate, 'DD.MM.YYYY') as \"EvnDie_expDate\"
				,to_char(ED.EvnDie_expTime, 'HH24:MI') as \"EvnDie_expTime\"
				,RTRIM(COALESCE(AnatomWhere.AnatomWhere_Name, '')) as \"AnatomWhere_Name\"
				,RTRIM(COALESCE(AnatomLpu.Lpu_Name, AnatomLS.LpuSection_Name, AnatomOrg.OrgAnatom_Name, '')) as \"EvnAnatomPlace\"
				,RTRIM(COALESCE(EDAMP.MedPersonal_TabCode, '')) as \"AnatomMedPersonal_Code\"
				,RTRIM(COALESCE(EDAMP.Person_Fio, '')) as \"AnatomMedPersonal_Fio\"
			from v_EvnSection ES 
				left join LeaveType LT  on LT.LeaveType_id = ES.LeaveType_id
				LEFT JOIN LATERAL (
					Select * from v_EvnLeave EL  where EL.EvnLeave_pid = ES.EvnSection_id limit 1
				) EL ON true
				left join v_EvnDie ED  on ED.EvnDie_pid = ES.EvnSection_id
				left join v_EvnOtherLpu EOL  on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				left join v_EvnOtherSection EOS  on EOS.EvnOtherSection_pid = ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP  on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				left join v_EvnOtherStac EOST  on EOST.EvnOtherStac_pid = ES.EvnSection_id
				left join ResultDesease  on ResultDesease.ResultDesease_id = COALESCE(EL.ResultDesease_id, COALESCE(EOL.ResultDesease_id, COALESCE(EOS.ResultDesease_id, EOST.ResultDesease_id)))
				left join LeaveCause  on LeaveCause.LeaveCause_id = EL.LeaveCause_id
				left join LeaveCause OSC  on OSC.LeaveCause_id = EOS.LeaveCause_id
				left join LeaveCause OSTC  on OSTC.LeaveCause_id = EOST.LeaveCause_id
				left join LeaveCause OLC  on OLC.LeaveCause_id = EOL.LeaveCause_id
				left join YesNo IsAmbul  on IsAmbul.YesNo_id = EL.EvnLeave_IsAmbul
				left join YesNo IsWait  on IsWait.YesNo_id = ED.EvnDie_IsWait
				left join YesNo IsAnatom  on IsAnatom.YesNo_id = ED.EvnDie_IsAnatom
				left join v_Org OtherLpu  on OtherLpu.Org_id = EOL.Org_oid
				left join LpuUnitType OSTLUT  on OSTLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join LpuSection OSTLS  on OSTLS.LpuSection_id = EOST.LpuSection_oid
				left join v_MedPersonal EDMP  on EDMP.MedPersonal_id = ED.MedPersonal_id and EDMP.Lpu_id = ED.Lpu_id
				left join AnatomWhere  on AnatomWhere.AnatomWhere_id = ED.AnatomWhere_id
				left join v_Lpu AnatomLpu  on AnatomLpu.Lpu_id = ED.Lpu_aid
				left join v_OrgAnatom AnatomOrg  on AnatomOrg.OrgAnatom_id = ED.OrgAnatom_id
				left join LpuSection AnatomLS  on AnatomLS.LpuSection_id = ED.LpuSection_aid
				left join v_MedPersonal EDAMP  on EDAMP.MedPersonal_id = ED.MedPersonal_aid and EDAMP.Lpu_id = ED.Lpu_id
			where ".$where;
		if (($data['KVS_Type'] == 'VG') || ($data['KVS_Type'] == 'V') || ($data['KVS_Type'] == 'G'))
			$result = $this->db->query($query,array(
				'EvnSection_id' => $data['EvnSection_id']
			));
		else
			$result = $this->db->query($query,array(
				'EvnPS_id' => $data['EvnPS_id']
			));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка услуг в рамках КВС
	 * @return array
	 * @throws Exception
	 */
	function getListEvnUslugaData()
	{
		if ( is_array($this->_listEvnUslugaData) ) {
			return $this->_listEvnUslugaData;
		}
		$result = $this->db->query("
			select
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				EU.EvnUsluga_pid as \"EvnUsluga_pid\"
			from v_EvnUsluga EU 
			where EU.EvnUsluga_rid = :EvnPS_id
		", array(
			'EvnPS_id'=>$this->id,
		));
		if ( is_object($result) ) {
			$tmp = $result->result('array');
			$this->_listEvnUslugaData = array();
			foreach ($tmp as $row) {
				$this->_listEvnUslugaData[$row['EvnUsluga_id']] = $row;
			}
		} else {
			throw new Exception('Не удалось загрузить список услуг в рамках КВС');
		}
		return $this->_listEvnUslugaData;
	}

	/**
	 * Получение услуг для печати ТАП отказа в госпитализации
	 */
	function getEvnUslugaData($data) {
		$query = "
			select
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				sum(EU.EvnUsluga_Kolvo) as \"EvnUsluga_Kolvo\"
			from v_EvnUsluga EU 
			left join UslugaComplex UC  on UC.UslugaComplex_id = EU.UslugaComplex_id
			where EU.EvnUsluga_rid = :EvnPS_id
			group by UC.UslugaComplex_Code
		";
		$result = $this->db->query($query,array('EvnPS_id'=>$data['EvnPS_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных диагноза для печати ТАП отказа в госпитализации
	 */
	function getEvnDiagData($data) {
		//Основной диагноз
		$query = "
			select --Основные
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				'' as \"DeseaseType_Code\",
				1 as \"diagType\",
				CASE WHEN (PD.PersonDisp_endDate is null or to_char(dbo.tzGetDate(), 'DD.MM.YYYY') = to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY')) THEN (CASE WHEN (PD.PersonDisp_id is null) then 'Нет' else 'Да' end) ELSE '' END as \"IsDisp\",
				CASE WHEN (PD.PersonDisp_endDate is null or to_char(dbo.tzGetDate(), 'DD.MM.YYYY') = to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY')) THEN COALESCE(to_char(PD.PersonDisp_begDate, 'DD.MM.YYYY'),'') ELSE '' end as \"Disp_Date\",
				CASE WHEN DOT.DispOutType_SysNick = 'zdorov' and PD.PersonDisp_endDate is not null and to_char(dbo.tzGetDate(), 'DD.MM.YYYY') = to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') then to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') else '' end as \"DOT_Zdorov\",
				CASE WHEN DOT.DispOutType_SysNick != 'zdorov' and PD.PersonDisp_endDate is not null and to_char(dbo.tzGetDate(), 'DD.MM.YYYY') = to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') then to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') else '' end as \"DOT_Other\"
			from v_EvnSection ES 
				left join LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = ES.MedPersonal_id
					and MP.Lpu_id = ES.Lpu_id
				inner join Diag on Diag.Diag_id = ES.Diag_id
				--left join DeseaseType DT  on DT.DeseaseType_id = ES.DeseaseType_id
				left join v_PersonDisp PD  on PD.Person_id = ES.Person_id and PD.Diag_id = Diag.Diag_id
				left join v_DispOutType DOT  on DOT.DispOutType_id = PD.DispOutType_id
			where ES.EvnSection_pid = :EvnPS_id
			order by ES.EvnSection_Index desc
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));
		if ( !is_object($result) ) {
			return false;
		}
		$main = $result->result('array');

		return $main;
	}

	/**
	 * Получение данных для печати ТАП отказа в госпитализации
	 */
	function getEvnPSForPrintEvnPLRefuse($data) {
		$params = array('EvnPS_id' => $data['EvnPS_id']);

		$query = "
			select
				RTRIM(COALESCE(OJ.Org_Name, '')) as \"OrgJob_Name\",
				RTRIM(COALESCE(OrgUnion.OrgUnion_Name, '')) as \"OrgUnion_Name\",
				RTRIM(COALESCE(Post.Post_Name, '')) as \"Post_Name\",
				RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",
				'' as \"Person_INN\",
				PS.Sex_id as \"Sex_id\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_Name as \"Sex_Name\",
				PS.Person_Snils as \"Person_Snils\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				PAddr.KlareaType_id as \"KlareaType_id\",
				Lpu.Lpu_Name as \"Lpu_Name\",
				Lpu.PAddress_Address as \"LpuAddress\",
				Lpu.Lpu_OGRN as \"Lpu_OGRN\",
				RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\",
				RTRIM(COALESCE(OS.Org_Name, '')) as \"OrgSmo_Name\",
				to_char(Polis.Polis_begDate, 'DD.MM.YYYY') as \"Polis_begDate\",
				to_char(Polis.Polis_endDate, 'DD.MM.YYYY') as \"Polis_endDate\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(COALESCE(Polis.Polis_Ser, '')) END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then COALESCE(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(COALESCE(Polis.Polis_Num, '')) END as \"Polis_Num\",
				COALESCE(SocStatus.SocStatus_Code, '') as \"SocStatus_Code\",
				COALESCE(SocStatus.SocStatus_Name, '') as \"SocStatus_Name\",
				RTRIM(COALESCE(PAddr.Address_Address, '')) as \"PAddress_Name\",
				RTRIM(COALESCE(UAddr.Address_Address, '')) as \"UAddress_Name\",
				COALESCE(KLAreaType.KLAreaType_Code, 0) as \"KLAreaType_Code\",
				RTRIM(COALESCE(KLAreaType.KLAreaType_Name, '')) as \"KLAreaType_Name\",
				RTRIM(COALESCE(LpuRegion.LpuRegion_Name, '')) as \"LpuRegion_Name\",
				COALESCE(to_char(Document.Document_begDate, 'DD.MM.YYYY'), '') as \"Document_begDate\",
				RTRIM(COALESCE(Document.Document_Num, '')) as \"Document_Num\",
				RTRIM(COALESCE(Document.Document_Ser, '')) as \"Document_Ser\",
				COALESCE(DocumentType.DocumentType_Code, 0) as \"DocumentType_Code\",
				RTRIM(COALESCE(DocumentType.DocumentType_Name, '')) as \"DocumentType_Name\",
				COALESCE(PHT.PrehospTrauma_Code, 0) as \"PrehospTrauma_Code\",
				RTRIM(COALESCE(ResultClass.ResultClass_SysNick, '')) as \"ResultClass_SysNick\",
				COALESCE(ResultClass.ResultClass_Code, 0) as \"ResultClass_Code\",
				COALESCE(ResultDeseaseType.ResultDeseaseType_Code, 0) as \"ResultDeseaseType_Code\",
				RTRIM(COALESCE(PT.PayType_Name, '')) as \"PayType_Name\",
				COALESCE(PT.PayType_Code, 0) as \"PayType_Code\",
				RTRIM(COALESCE(PT.PayType_SysNick,'')) as \"PayType_SysNick\",
				'41' as \"VizitType_Code\",
				RTRIM(COALESCE(EvnSection.MedPersonal_Fio, '')) as \"MedPersonal_Fio\",
				RTRIM(COALESCE(EvnSection.LpuSectionProfile_Code, '')) as \"LpuSectionProfile_Code\",
				RTRIM(COALESCE(EvnSection.LpuSectionProfile_Name, '')) as \"LpuSectionProfile_Name\",
				RTRIM(coalesce(EvnSection.LpuSectionBedProfile_Code, '')) as \"LpuSectionBedProfile_Code\",
				RTRIM(coalesce(EvnSection.LpuSectionBedProfile_Name, '')) as \"LpuSectionBedProfile_Name\",
				RTRIM(COALESCE(EvnSection.MedPersonal_Code, '')) as \"MedPersonal_Code\",
				RTRIM(COALESCE(EvnSection.MedPersonal_Code, '')) as \"MedPersonal_Code_Last\",
				EvnSection.EvnSection_setDate as \"EvnSection_setDate\",
				EvnSection.Days_Count as \"Days_Count\",
				RTRIM(COALESCE(PersonPrivilege.PersonPrivilege_begDate, '')) as \"PersonPrivilege_begDate\",
				RTRIM(COALESCE(PersonPrivilege.PrivilegeType_Name, '')) as \"PrivilegeType_Name\",
				PersonPrivilege.PrivilegeType_Code as \"PrivilegeType_Code\"
			from
				v_EvnPS EPS 
				left join v_Lpu Lpu  on Lpu.Lpu_id = EPS.Lpu_id
				left join v_Person_all PS  on PS.PersonEvn_id = EPS.PersonEvn_id and PS.Server_id = EPS.Server_id
				left join Address UAddr  on UAddr.Address_id = PS.UAddress_id
				left join Address PAddr  on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType  on KLAreaType.KLAreaType_id = UAddr.KLAreaType_id
				left join Document  on Document.Document_id = PS.Document_id
				left join DocumentType  on DocumentType.DocumentType_id = Document.DocumentType_id
				left join Job  on Job.Job_id = PS.Job_id
				left join Org OJ  on OJ.Org_id = Job.Org_id
				left join OrgUnion  on OrgUnion.OrgUnion_id = Job.OrgUnion_id
				left join Post  on Post.Post_id = Job.Post_id
				left join Diag  on Diag.Diag_id = EPS.Diag_pid
				left join v_PayType PT  on PT.PayType_id = EPS.PayType_id
				LEFT JOIN LATERAL(
					select 
							PC.PersonCard_Code,
							PC.LpuRegion_Name,
							PC.LpuRegion_id
					from  v_PersonCard_all PC 
					where PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.LpuAttachType_id = 1
					order by PC.PersonCard_begDate desc
					limit 1
				) PC ON true
				LEFT JOIN LATERAL (
					select
						to_char(ES.EvnSection_setDate, 'DD.MM.YYYY') as EvnSection_setDate,
						DATEDIFF('day',ES.EvnSection_setDate,ES.EvnSection_disDate) as Days_Count,
						LSP.LpuSectionProfile_Code,
						LSP.LpuSectionProfile_Name,
						LSBP.LpuSectionBedProfile_Code,
						LSBP.LpuSectionBedProfile_Name,
						MP.MedPersonal_Code,
						MP.Person_Fio as MedPersonal_Fio
					from v_EvnSection ES 
						left join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = ES.LpuSectionProfile_id
                        left join v_LpuSectionBedProfile LSBP on LSBP.LpuSectionBedProfile_id = ES.LpuSectionBedProfile_id
                        left join v_MedPersonal MP  on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id
					where ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_IsPriem = 2
					limit 1
				) EvnSection ON true
				left join ResultClass  on ResultClass.ResultClass_id = EPS.ResultClass_id
				left join ResultDeseaseType  on ResultDeseaseType.ResultDeseaseType_id = EPS.ResultDeseaseType_id
				left join v_LpuRegion LpuRegion  on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join PersonState PState  on PState.Person_id = PS.Person_id
				left join Polis  on Polis.Polis_id = PState.Polis_id
				left join PolisType  on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo  on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org OS  on OS.Org_id = OrgSmo.Org_id
				--left join DirectType  on DirectType.DirectType_id = EPS.DirectType_id
				left join v_PrehospTrauma PHT  on PHT.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join SocStatus  on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Sex  on Sex.Sex_id = PS.Sex_id
				LEFT JOIN LATERAL (
					select
						PrivilegeType_Name,
						COALESCE(PrivilegeType_Code, 0) as PrivilegeType_Code,
						to_char(PersonPrivilege_begDate, 'DD.MM.YYYY') as PersonPrivilege_begDate
					from
						v_PersonPrivilege 
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) PersonPrivilege ON true
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$result = $result->result('array');

			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * @comment Для Карелии метод вынесен в региональную модель
	 */
	function getEvnPSNumber($data) {
		$query = "
			select
				ObjectID as \"EvnPS_NumCard\"
			from xp_GenpmID(
				ObjectName := 'EvnPS',
				Lpu_id := :Lpu_id
			)
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return int
	 * Проверка, выбрано ли первое по хронологии движение
	 */
	function checkEvnSection($data)
	{

		$params = array(
			'Lpu_id' => $data["Lpu_id"],
			'EvnSection_pid' => $data['EvnPS_id'],
			'EvnSection_id' => $data['EvnSection_id']
		);
		//var_dump($params);
		//return false;
		$query = "
		SELECT
			ES.EvnClass_id as \"EvnClass_id\",
			ES.EvnClass_Name as \"EvnClass_Name\",
			ES.EvnSection_id as \"EvnSection_id\",
			ES.EvnSection_setDate as \"EvnSection_setDate\",
			ES.EvnSection_setTime as \"EvnSection_setTime\",
			ES.EvnSection_didDate as \"EvnSection_didDate\",
			ES.EvnSection_didTime as \"EvnSection_didTime\",
			ES.EvnSection_disDate as \"EvnSection_disDate\",
			ES.EvnSection_disTime as \"EvnSection_disTime\",
			ES.EvnSection_pid as \"EvnSection_pid\",
			ES.EvnSection_rid as \"EvnSection_rid\",
			ES.Lpu_id as \"Lpu_id\",
			ES.Server_id as \"Server_id\",
			ES.PersonEvn_id as \"PersonEvn_id\",
			ES.EvnSection_setDT as \"EvnSection_setDT\",
			ES.EvnSection_disDT as \"EvnSection_disDT\",
			ES.EvnSection_didDT as \"EvnSection_didDT\",
			ES.EvnSection_insDT as \"EvnSection_insDT\",
			ES.EvnSection_updDT as \"EvnSection_updDT\",
			ES.EvnSection_Index as \"EvnSection_Index\",
			ES.EvnSection_Count as \"EvnSection_Count\",
			ES.pmUser_insID as \"pmUser_insID\",
			ES.pmUser_updID as \"pmUser_updID\",
			ES.Person_id as \"Person_id\",
			ES.Morbus_id as \"Morbus_id\",
			ES.EvnSection_IsSigned as \"EvnSection_IsSigned\",
			ES.pmUser_signID as \"pmUser_signID\",
			ES.EvnSection_signDT as \"EvnSection_signDT\",
			ES.EvnSection_IsArchive as \"EvnSection_IsArchive\",
			ES.EvnSection_Guid as \"EvnSection_Guid\",
			ES.EvnSection_IndexMinusOne as \"EvnSection_IndexMinusOne\",
			ES.EvnStatus_id as \"EvnStatus_id\",
			ES.EvnSection_statusDate as \"EvnSection_statusDate\",
			ES.EvnSection_IsTransit as \"EvnSection_IsTransit\",
			ES.LpuSection_id as \"LpuSection_id\",
			ES.Diag_id as \"Diag_id\",
			ES.Mes_id as \"Mes_id\",
			ES.PayType_id as \"PayType_id\",
			ES.TariffClass_id as \"TariffClass_id\",
			ES.MedPersonal_id as \"MedPersonal_id\",
			ES.EvnSection_IsInReg as \"EvnSection_IsInReg\",
			ES.Mes_OldCode as \"Mes_OldCode\",
			ES.LpuSectionWard_id as \"LpuSectionWard_id\",
			ES.DiagSetPhase_id as \"DiagSetPhase_id\",
			ES.EvnSection_PhaseDescr as \"EvnSection_PhaseDescr\",
			ES.LeaveType_id as \"LeaveType_id\",
			ES.EvnSection_IsAdultEscort as \"EvnSection_IsAdultEscort\",
			ES.Mes2_id as \"Mes2_id\",
			ES.UslugaComplex_id as \"UslugaComplex_id\",
			ES.EvnSection_IsMeal as \"EvnSection_IsMeal\",
			ES.EvnSection_IsPaid as \"EvnSection_IsPaid\",
			ES.Mes_tid as \"Mes_tid\",
			ES.Mes_sid as \"Mes_sid\",
			ES.MesTariff_id as \"MesTariff_id\",
			ES.Mes_kid as \"Mes_kid\",
			ES.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
			ES.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			ES.EvnSection_IsPriem as \"EvnSection_IsPriem\",
			ES.LpuSectionTransType_id as \"LpuSectionTransType_id\",
			ES.EvnSection_InsideNumCard as \"EvnSection_InsideNumCard\",
			ES.EvnSection_IndexRep as \"EvnSection_IndexRep\",
			ES.EvnSection_IndexRepInReg as \"EvnSection_IndexRepInReg\",
			ES.LeaveType_fedid as \"LeaveType_fedid\",
			ES.ResultDeseasetype_fedid as \"ResultDeseasetype_fedid\",
			ES.EvnSection_CoeffCTP as \"EvnSection_CoeffCTP\",
			ES.LeaveType_prmid as \"LeaveType_prmid\",
			ES.EvnSection_IsFinish as \"EvnSection_IsFinish\",
			ES.MedStaffFact_id as \"MedStaffFact_id\",
			ES.EvnSection_IsRehab as \"EvnSection_IsRehab\",
			ES.RankinScale_id as \"RankinScale_id\",
			ES.RankinScale_sid as \"RankinScale_sid\",
			ES.CureResult_id as \"CureResult_id\",
			ES.EvnSection_IsWillPaid as \"EvnSection_IsWillPaid\",
			ES.EvnSection_IndexNum as \"EvnSection_IndexNum\",
			ES.UslugaComplex_sid as \"UslugaComplex_sid\",
			ES.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
			ES.Diag_ksgid as \"Diag_ksgid\",
			ES.Diag_kskpid as \"Diag_kskpid\",
			ES.EvnSection_InsultScale as \"EvnSection_InsultScale\",
			ES.MesOldUslugaComplex_id as \"MesOldUslugaComplex_id\",
			ES.EvnSection_IsMedReason as \"EvnSection_IsMedReason\",
			ES.EvnSection_IsCardShock as \"EvnSection_IsCardShock\",
			ES.EvnSection_StartPainHour as \"EvnSection_StartPainHour\",
			ES.EvnSection_StartPainMin as \"EvnSection_StartPainMin\",
			ES.EvnSection_GraceScalePoints as \"EvnSection_GraceScalePoints\",
			ES.EvnSection_TreatmentDiff as \"EvnSection_TreatmentDiff\",
			ES.Diag_eid as \"Diag_eid\",
			ES.EvnSection_IsTerm as \"EvnSection_IsTerm\",
			ES.EvnSection_IsST as \"EvnSection_IsST\",
			ES.DeseaseBegTimeType_id as \"DeseaseBegTimeType_id\",
			ES.DeseaseType_id as \"DeseaseType_id\",
			ES.TumorStage_id as \"TumorStage_id\",
			ES.EvnSection_isPartialPay as \"EvnSection_isPartialPay\",
			ES.PainIntensity_id as \"PainIntensity_id\",
			ES.RehabScale_id as \"RehabScale_id\",
			ES.EvnSection_SofaScalePoints as \"EvnSection_SofaScalePoints\",
			ES.EvnSection_BarthelIdx as \"EvnSection_BarthelIdx\",
			ES.EvnSection_IsManualIdxNum as \"EvnSection_IsManualIdxNum\",
			ES.RehabScale_vid as \"RehabScale_vid\",
			ES.EvnSection_IsZNO as \"EvnSection_IsZNO\",
			ES.LpuSectionBedProfileLink_fedid as \"LpuSectionBedProfileLink_fedid\",
			ES.Diag_spid as \"Diag_spid\",
			ES.EvnSection_IndexNum2 as \"EvnSection_IndexNum2\",
			ES.EvnSection_IsWillPaid2 as \"EvnSection_IsWillPaid2\",
			ES.MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
			ES.EvnSection_IsInRegZNO as \"EvnSection_IsInRegZNO\",
			ES.Registry_sid as \"Registry_sid\",
			ES.EvnSection_IsZNORemove as \"EvnSection_IsZNORemove\",
			ES.EvnSection_BiopsyDate as \"EvnSection_BiopsyDate\",
			ES.EvnSection_IsMultiKSG as \"EvnSection_IsMultiKSG\",
			ES.EvnSection_NIHSSAfterTLT as \"EvnSection_NIHSSAfterTLT\",
			ES.EvnSection_NIHSSLeave as \"EvnSection_NIHSSLeave\",
			ES.EvnSection_Absence as \"EvnSection_Absence\",
			ES.EvnSection_TotalFract as \"EvnSection_TotalFract\",
			ES.DiagSetPhase_aid as \"DiagSetPhase_aid\",
			ES.PayTypeERSB_id as \"PayTypeERSB_id\",
			ES.PrivilegeType_id as \"PrivilegeType_id\",
			ES.MesTariff_sid as \"MesTariff_sid\"
		FROM v_EvnSection ES 
		WHERE ES.EvnSection_pid = :EvnSection_pid
		AND ES.Lpu_id = :Lpu_id
		AND ES.EvnSection_setDate = (
										SELECT MIN(ES.EvnSection_setDate)
										FROM v_EvnSection ES 
										WHERE ES.EvnSection_pid = :EvnSection_pid
										AND ES.Lpu_id = :Lpu_id
									)
		AND ES.EvnSection_id = :EvnSection_id
		";

		$result = $this->db->query($query, $params);

		return count($result->result('array'));

	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnSectionData($data, $allowPriem = false) {
		$filterList = array();
		$params = array();

		if ($data['KVS_Type']=='VG'){
			$KVS_Type = '';
			if ($this->checkEvnSection($data)=='0')
				$KVS_Type = 'G';
			else
				$KVS_Type = 'V';
			//if(($data['KVS_Type']=='V')||($data['KVS_Type']=='G'))
			if (($KVS_Type == 'V') || ($KVS_Type == 'G'))
			{
				$filterList[] = "ES.EvnSection_id = :EvnSection_id";
				$params['EvnSection_id'] = $data['EvnSection_id'];
			}
		}
		$filterList[] = "ES.Lpu_id = :Lpu_id";
		$params['Lpu_id'] = $data['Lpu_id'];

		if ( !empty($data['EvnPS_id']) ) {
			$filterList[] = "ES.EvnSection_pid = :EvnSection_pid";
			$params['EvnSection_pid'] = $data['EvnPS_id'];
		}

		if ( $allowPriem === false ) {
			$filterList[] = "COALESCE(ES.EvnSection_IsPriem, 1) = 1";
		}

		$query = "
			select
				ES.EvnSection_id as \"EvnSection_id\",
				to_char(ES.EvnSection_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnSection_setDT\",
				to_char(ES.EvnSection_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnSection_disDT\",
				RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				RIGHT(LS.LpuSection_Code,2) || '. ' || RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_CodeName\",
				MP.MedPersonal_TabCode as \"MedPersonal_Code\",
				MP.MedPersonal_Code as \"MPCode\",
				MP.Person_Fio as \"MedPersonal_FIO\",
				RTRIM(COALESCE(PT.PayType_Name, '')) as \"PayType_Name\",
				'Основной' as \"EvnSectionDiagSetClassOsn_Name\",
				RTRIM(COALESCE(Diag.Diag_Code, '')) as \"EvnSectionDiagOsn_Code\",
				RTRIM(COALESCE(Diag.Diag_Name, '')) as \"EvnSectionDiagOsn_Name\",
				RTRIM(COALESCE(Mes.Mes_Code, '')) as \"EvnSectionMesOsn_Code\",
				RTRIM(COALESCE(Mes.Mes_Code, '')) as \"EvnSectionKsg_Code\",
				COALESCE(ksgkpg.Mes_Code, '') || ' ' ||  COALESCE(ksgkpg.Mes_Name, '') as \"EvnSection_KSG\",
				ELB.EvnLeaveBase_UKL as \"EvnSection_UKL\",
				COALESCE(ES.EvnSection_IsAdultEscort,'1') as \"EvnSection_IsAdultEscort\",
				LSBP.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LSBP.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_Code\",
				LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\",
				COALESCE(NB.LpuSectionProfile_Name,'') as \"LpuSectionNarrowBedProfile_Name\"
			from v_EvnSection ES 
				inner join v_LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
				INNER JOIN LATERAL (
					select
						 MedPersonal_TabCode
						,MedPersonal_Code
						,Person_Fio
					from v_MedPersonal 
					where MedPersonal_id = ES.MedPersonal_id
						and Lpu_id = :Lpu_id
					limit 1
				) MP ON true
				inner join v_PayType PT  on PT.PayType_id = ES.PayType_id
				left join v_Diag Diag  on Diag.Diag_id = ES.Diag_id
				left join v_LpuSectionBedProfile LSBP   on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				left join v_MesOld Mes  on Mes.Mes_id = ES.Mes_id
				left join v_MesTariff spmt  on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg  on spmt.Mes_id = ksgkpg.Mes_id
				LEFT JOIN LATERAL (
					select EvnLeaveBase_UKL
					from v_EvnLeaveBase 
					where EvnLeaveBase_pid = ES.EvnSection_id
					limit 1
				) ELB ON true
				LEFT JOIN LATERAL (
					select 
						RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name
					from v_EvnSectionNarrowBed ESNB 
					left join LpuSection LS2  on LS2.LpuSection_id = ESNB.LpuSection_id
					left join LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS2.LpuSectionProfile_id
					where ESNB.EvnSectionNarrowBed_pid = ES.EvnSection_id
					limit 1
				) NB ON true
			where " . implode(' and ', $filterList) . "
			order by ES.EvnSection_setDT
		";

		$result = $this->db->query($query,$params);

		if ( is_object($result) ) {
			$result = $result->result('array');

			if(getRegionNick() == 'perm' || getRegionNick() == 'kareliya') {//#144263 Заменяем профиль койки на профиль койки из движения КВС
				foreach ($result as $key => $item) {
					$queryBedProfile = "
					select sbp.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\"
					from 
						v_EvnSection ES
						inner join LpuSectionBedProfile SBP on SBP.LpuSectionBedProfile_id = ES.LpuSectionBedProfile_id
					where 
						EvnSection_id = :EvnSection_id
					limit 1
					";
					$bedProfile =  $this->queryResult($queryBedProfile, array('EvnSection_id' => $item['EvnSection_id']));
					if(!empty($bedProfile)) {
						$result[$key]['LpuSectionBedProfile_Name'] = $bedProfile[0]['LpuSectionBedProfile_Name'];
					}
				}
			}

			//$str_narrow_bed_setDT = '';
			//$str_narrow_bed_disDT = '';
			for ($i=0; $i<count($result); $i++){
				$str_narrow_bed = '';
				$EvnSection_id = $result[$i]['EvnSection_id'];
				$query_narrow = "
					select
						RTRIM(LSP.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
						to_char(ESNB.EvnSectionNarrowBed_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnSectionNarrowBed_setDT\",
						to_char(ESNB.EvnSectionNarrowBed_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnSectionNarrowBed_disDT\"
					from v_EvnSectionNarrowBed ESNB 
					left join LpuSection LS2  on LS2.LpuSection_id = ESNB.LpuSection_id
					left join LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS2.LpuSectionProfile_id
					where ESNB.EvnSectionNarrowBed_pid = :EvnSection_id
				";

				$result_narrow = $this->db->query($query_narrow,array('EvnSection_id' => $EvnSection_id));

				if(is_object($result_narrow)){
					$result_narrow = $result_narrow->result('array');
					for($j=0; $j<count($result_narrow); $j++){
						$str_narrow_bed .=  $result_narrow[$j]['LpuSectionProfile_Name'].'<br>';
						//$str_narrow_bed_setDT .=  $result_narrow[$j]['EvnSectionNarrowBed_setDT'].'<br>';
						//$str_narrow_bed_disDT .=  $result_narrow[$j]['EvnSectionNarrowBed_disDT'].'<br>';
					}

					if($str_narrow_bed) $result[$i]['LpuSectionBedProfile_Name'] = $str_narrow_bed;
					/*if(!empty($str_narrow_bed_setDT)){
						$result[$i]['EvnSection_setDT'] = $str_narrow_bed_setDT;
					}
					if(!empty($str_narrow_bed_disDT)){
						$result[$i]['EvnSection_disDT'] = $str_narrow_bed_disDT;
					}*/
				}
			}
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных по узким койкам для печати
	 */
	function getEvnSectionNarrowBedData($data) {
		$params = array('EvnSectionNarrowBed_rid' => $data['EvnPS_id']);

		$query = "
			select
				ESNB.EvnSectionNarrowBed_pid as \"EvnSection_id\",
				LSBP.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_Code\",
				--LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\",
				--LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				COALESCE(LSBP.LpuSectionBedProfile_Name,LSP.LpuSectionProfile_Name) as \"LpuSectionBedProfile_Name\",
				to_char(ESNB.EvnSectionNarrowBed_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnSection_setDT\",
				to_char(ESNB.EvnSectionNarrowBed_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnSection_disDT\"
			from
				v_EvnSectionNarrowBed ESNB 
				inner join v_LpuSection LS  on LS.LpuSection_id = ESNB.LpuSection_id
				left join v_LpuSectionBedProfile LSBP  on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				left join LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
			where ESNB.EvnSectionNarrowBed_pid = :EvnSectionNarrowBed_rid
		";

		//echo getDebugSQL($query, $params);die;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных по льготам человека для печати
	 */
	function getPersonPrivilegeData($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$filter = '(1=1)';

		if (!empty($data['PersonPrivilege_begDate'])){
			$params['PersonPrivilege_begDate'] = $data['PersonPrivilege_begDate'];
			$filter .= " and PP.PersonPrivilege_begDate < :PersonPrivilege_begDate ";
		}

		if (!empty($data['PersonPrivilege_endDate'])){
			$params['PersonPrivilege_endDate'] = $data['PersonPrivilege_endDate'];
			$filter .= " and PP.PersonPrivilege_endDate >= :PersonPrivilege_endDate ";
		}

		$query = "
			select distinct
				case
					when PP.PrivilegeType_Code = '11' then 1	--инвалид ВОВ
					when PP.PrivilegeType_Code in('20', '140', '150') then 2	--участник ВОВ
					when PP.PrivilegeType_Code in ('110','111','112','240','91') then 4	--лицо, подвергшееся радиационному облучению
					when PP.PrivilegeType_Code = '92' then 5	--в т.ч. в Чернобыле
					when PP.PrivilegeType_Code = '81' then 6	--инв. I гр.
					when PP.PrivilegeType_Code = '82' then 7	--инв. II гр.
					when PP.PrivilegeType_Code = '83' then 8	--инв. III гр.
					when PP.PrivilegeType_Code = '84' then 9	--ребенок-инвалид
					else 11		--прочие
				end as \"PrivilegeType_Code\"
			from v_PersonPrivilege PP 
			where
				PP.Person_id = :Person_id
				and {$filter}
		";

		//echo getDebugSQL($query, $params);die;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных по льготам человека для печати на Хакасии
	 */
	function getPersonPrivilegeDataHakas($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$filter = '(1=1)';

		if (!empty($data['PersonPrivilege_begDate'])){
			$params['PersonPrivilege_begDate'] = $data['PersonPrivilege_begDate'];
			$filter .= " and PP.PersonPrivilege_begDate < :PersonPrivilege_begDate ";
		}

		if (!empty($data['PersonPrivilege_endDate'])){
			$params['PersonPrivilege_endDate'] = $data['PersonPrivilege_endDate'];
			$filter .= " and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= :PersonPrivilege_endDate) ";
		}

		$query = "
			select distinct
				case
					when PP.PrivilegeType_Code in ('11','50') then 1	--инвалид ВОВ
					when PP.PrivilegeType_Code in ('10','20','140','150') then 2	--участник ВОВ
					when PP.PrivilegeType_Code = '30' then 3	--Воин-интернационалист
					when PP.PrivilegeType_Code in ('110','111','112','240','91','93','94','123','124','125','128') then 4	--лицо, подвергшееся радиационному облучению
					when PP.PrivilegeType_Code = '92' then 5	--в т.ч. в Чернобыле
					when PP.PrivilegeType_Code = '81' then 6	--инв. I гр.
					when PP.PrivilegeType_Code = '82' then 7	--инв. II гр.
					when PP.PrivilegeType_Code = '83' then 8	--инв. III гр.
					when PP.PrivilegeType_Code = '84' then 9	--ребенок-инвалид
					else 11		--прочие
				end as \"PrivilegeType_Code\"
			from v_PersonPrivilege PP 
			where
				PP.Person_id = :Person_id
				and {$filter}
		";

		//echo getDebugSQL($query, $params);die;
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return null
	 */
	function getLpuUnitTypeFromFirstEvnSection($data) {
		$query = "
			select
				LUT.LpuUnitType_id as \"LpuUnitType_id\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			from v_EvnSection ES 
				inner join LpuSection LS  on LS.LpuSection_id = ES.LpuSection_id
				inner join LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				inner join LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
			where ES.EvnSection_pid = :EvnSection_pid
				and ES.Lpu_id = :Lpu_id
				and COALESCE(ES.EvnSection_IsPriem, 1) = 1
			order by ES.EvnSection_setDate
			limit 1
		";
		return $this->getFirstRowFromQuery($query, array(
			'EvnSection_pid' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * @param $data
	 * @return null
	 */
	function getDataFromLastEvnSection($data)
	{
		$this->applyData($data);
		if ( is_object($this->evnSectionLast) ) {
			return array(
				'LpuUnitType_id' => $this->evnSectionLast->lpuUnitTypeId,
				'EvnSection_DisDate' => $this->evnSectionLast->disDT ? ConvertDateFormat($this->evnSectionLast, 'd.m.Y') : null,
			);
		} else {
			return null;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnStickData($data) {
		$query = "
			select
				to_char(COALESCE(eswr.EvnStickWorkRelease_begDT, ES.EvnStick_begDate, ES.EvnStick_setDT), 'DD.MM.YYYY') as \"EvnStick_begDate\",
				case when ES.StickLeaveType_id is not null then to_char(COALESCE(eswr.EvnStickWorkRelease_endDT, ES.EvnStick_endDate, ES.EvnStick_disDT), 'DD.MM.YYYY') else '' end as \"EvnStick_endDate\",
				RTRIM(SO.StickOrder_Name) as \"StickOrder_Name\",
				RTRIM(ES.EvnStick_Ser) as \"EvnStick_Ser\",
				RTRIM(ES.EvnStick_Num) as \"EvnStick_Num\",
				RTRIM(SC.StickCause_Name) as \"StickCause_Name\",
				RTRIM(Sex.Sex_Name) as \"Sex_Name\",
				ES.EvnStick_Age as \"EvnStick_Age\"
			from v_EvnStick ES 
				left join StickOrder SO  on SO.StickOrder_id = ES.StickOrder_id
				left join StickCause SC  on SC.StickCause_id = ES.StickCause_id
				left join v_PersonState PS  on PS.Person_id = ES.Person_sid
				left join Sex  on Sex.Sex_id = PS.Sex_id
				LEFT JOIN LATERAL (
					select
						min(EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDT,
						max(EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease 
					where EvnStickBase_id = ES.EvnStick_id
				) eswr ON true
			where ES.EvnStick_pid = :EvnStick_pid
		";
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnPS_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaOperMedDataKarelya($data) {
		$query = "
			select to_char(EUO.EvnUslugaOper_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnUslugaOper_setDT\",
					MP1.MedPersonal_Code as \"OperSurgeon_Code\",
					MP1.Person_Fio as \"OperSurgeon_Name\",
					MP2.MedPersonal_Code as \"OperAnesthetist_Code\",
					MP2.Person_Fio as \"OperAnesthetist_Name\",
					MP3.MedPersonal_Code as \"Oper1Assistant_Code\",
					MP3.Person_Fio as \"Oper1Assistant_Name\",
					MP4.MedPersonal_Code as \"Oper2Assistant_Code\",
					MP4.Person_Fio as \"Oper2Assistant_Name\"
			from v_EvnUslugaOper EUO 
			left join v_EvnUslugaOperBrig EUOB1  on EUO.EvnUslugaOper_id = EUOB1.EvnUslugaOper_id and EUOB1.SurgType_id = (select SurgType_id from v_SurgType  where SurgType_Code = 1)
			left join v_MedPersonal MP1  on MP1.MedPersonal_id = EUOB1.MedPersonal_id and MP1.Lpu_id = EUO.Lpu_id
			left join v_EvnUslugaOperBrig EUOB2  on EUO.EvnUslugaOper_id = EUOB2.EvnUslugaOper_id and EUOB2.SurgType_id = (select SurgType_id from v_SurgType  where SurgType_Code = 6)
			left join v_MedPersonal MP2  on MP2.MedPersonal_id = EUOB2.MedPersonal_id and MP2.Lpu_id = EUO.Lpu_id
			left join v_EvnUslugaOperBrig EUOB3  on EUO.EvnUslugaOper_id = EUOB3.EvnUslugaOper_id and EUOB3.SurgType_id = (select SurgType_id from v_SurgType  where SurgType_Code = 2)
			left join v_MedPersonal MP3  on MP3.MedPersonal_id = EUOB3.MedPersonal_id and MP3.Lpu_id = EUO.Lpu_id
			left join v_EvnUslugaOperBrig EUOB4  on EUO.EvnUslugaOper_id = EUOB4.EvnUslugaOper_id and EUOB4.SurgType_id = (select SurgType_id from v_SurgType  where SurgType_Code = 3)
			left join v_MedPersonal MP4  on MP4.MedPersonal_id = EUOB4.MedPersonal_id and MP4.Lpu_id = EUO.Lpu_id
			where EUO.Lpu_id = :Lpu_id
			and EUO.EvnUslugaOper_rid = :EvnPS_id
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnUslugaOperData($data) {
		$query = "
			select
				to_char(EUO.EvnUslugaOper_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnUslugaOper_setDT\",
				LS.LpuSection_Code as \"LpuSection_Code\",
				MP.MedPersonal_TabCode as \"MedPersonal_Code\",
				RTRIM(COALESCE(PT.PayType_Name, '')) as \"PayType_Name\",
				RTRIM(COALESCE(UC.UslugaComplex_Code, U.Usluga_Code)) as \"Usluga_Code\",
				RTRIM(COALESCE(UC.UslugaComplex_Name, U.Usluga_Name)) as \"Usluga_Name\",
				RTRIM(COALESCE(Anest.AnesthesiaClass_Name, '')) as \"AnesthesiaClass_Name\",
				COALESCE(EUOIE.YesNo_Code, 0) as \"EvnUslugaOper_IsEndoskop\",
				COALESCE(EUOIL.YesNo_Code, 0) as \"EvnUslugaOper_IsLazer\",
				COALESCE(EUOIK.YesNo_Code, 0) as \"EvnUslugaOper_IsKriogen\",
				COALESCE(EUOIRG.YesNo_Code, 0) as \"EvnUslugaOper_IsRadGraf\",
				COALESCE(EUOIMS.YesNo_Code, 0) as \"EvnUslugaOper_IsMicrSurg\",
				RTRIM(COALESCE(EvnAgg.AggType_Name, '')) as \"AggType_Name\",
				EvnAgg.AggType_Code as \"AggType_Code\",
				COALESCE(AT_1.AggType_Name,'') as \"AggType_Name_1\",
				COALESCE(AT_2.AggType_Name,'') as \"AggType_Name_2\"
			from v_EvnUslugaOper EUO 
				left join v_EvnAgg EA_1  on EA_1.EvnAgg_pid = EUO.EvnUslugaOper_id and EA_1.AggWhen_id = 1 --Осложнение во время операции
				left join v_AggType AT_1  on AT_1.AggType_id = EA_1.AggType_id
				left join v_EvnAgg EA_2  on EA_2.EvnAgg_pid = EUO.EvnUslugaOper_id and EA_2.AggWhen_id = 2 --Осложнение после операции
				left join v_AggType AT_2  on AT_2.AggType_id = EA_2.AggType_id
				inner join LpuSection LS  on LS.LpuSection_id = EUO.LpuSection_uid
				inner join v_MedPersonal MP  on MP.MedPersonal_id = EUO.MedPersonal_id
					and MP.Lpu_id = EUO.Lpu_id
				inner join PayType PT  on PT.PayType_id = EUO.PayType_id
				left join Usluga U  on U.Usluga_id = EUO.Usluga_id
				left join UslugaComplex UC  on UC.UslugaComplex_id = EUO.UslugaComplex_id
				left join YesNo EUOIE  on EUOIE.YesNo_id = COALESCE(EUO.EvnUslugaOper_IsEndoskop, 1)
				left join YesNo EUOIL  on EUOIL.YesNo_id = COALESCE(EUO.EvnUslugaOper_IsLazer, 1)
				left join YesNo EUOIK  on EUOIK.YesNo_id = COALESCE(EUO.EvnUslugaOper_IsKriogen, 1)
				left join YesNo EUOIRG  on EUOIRG.YesNo_id = COALESCE(EUO.EvnUslugaOper_IsRadGraf, 1)
				left join YesNo EUOIMS  on EUOIMS.YesNo_id = COALESCE(EUO.EvnUslugaOper_IsMicrSurg, 1)
				LEFT JOIN LATERAL (
					select
						AC.AnesthesiaClass_Name
					from v_EvnUslugaOperAnest EUOA 
						inner join AnesthesiaClass AC  on AC.AnesthesiaClass_id = EUOA.AnesthesiaClass_id
					where EUOA.EvnUslugaOper_id = EUO.EvnUslugaOper_id
					limit 1
				) Anest ON true
				LEFT JOIN LATERAL (
					select AT.AggType_Name, AT.AggType_Code
					from v_EvnAgg EA 
					left join v_AggType AT  on AT.AggType_id = EA.AggType_id
					where EA.EvnAgg_pid = EUO.EvnUslugaOper_id
					order by EA.EvnAgg_setDate asc
					limit 1
				) EvnAgg ON true
			where EUO.EvnUslugaOper_rid = :EvnPS_id
				and EUO.Lpu_id = :Lpu_id
		";
		// echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); exit();
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение части запроса для определения прав доступа к форме редатирования события
	 */
	function getAccessTypeQueryPart($data, &$params) {
		$EvnClass = !empty($data['EvnClass'])?$data['EvnClass']:$this->evnClassSysNick;
		$EvnAlias = !empty($data['EvnAlias'])?$data['EvnAlias']:$this->evnClassSysNick;
		$session = $data['session'];

		$linkLpuIdList = isset($session['linkedLpuIdList'])?$session['linkedLpuIdList']:array();
		$linkLpuIdList_str = count($linkLpuIdList)>0?implode(',', $linkLpuIdList):'0';

		$queryPart = "
			case
				when {$EvnAlias}.Lpu_id = :Lpu_id then 1
				when {$EvnAlias}.Lpu_id in ({$linkLpuIdList_str}) and COALESCE({$EvnAlias}.{$EvnClass}_IsTransit, 1) = 2 then 1
				when (cast(:isMedStatUser as int) = 1 or cast(:withoutMedPersonal as int) = 1) and {$EvnAlias}.Lpu_id = :Lpu_id then 1
				when cast(:isSuperAdmin as int) = 1 then 1
				else 0
			end = 1
		";

		$params['isMedStatUser'] = isMstatArm($data);
		$params['isSuperAdmin'] = isSuperadmin();
		$params['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		if ( $session['region']['nick'] == 'ekb' ) {
			$queryPart .= " and COALESCE({$EvnAlias}.{$EvnClass}_IsPaid, 1) = 1";
		}

		if ($this->regionNick == 'pskov') {
			$queryPart .= "and COALESCE({$EvnAlias}.{$EvnClass}_IsPaid, 1) = 1
			 	and not exists(
					select RD.Registry_id as \"Registry_id\"
					from r60.v_RegistryData RD 
						inner join v_Registry R  on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS  on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = {$EvnAlias}.{$EvnClass}_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		if ( !isSuperAdmin() && $session['isMedStatUser'] == false ) {
			$this->load->helper('MedStaffFactLink');
			$med_personal_list = getMedPersonalListWithLinks();

			if ( count($med_personal_list)>0 ) {
				$queryPart .= "and (exists (
					select t1.EvnSection_id
					from v_EvnSection t1 
						inner join v_MedStaffFact t2  on t2.LpuSection_id = t1.LpuSection_id and (
							t2.MedPersonal_id in (".implode(',',$med_personal_list).")
						)
					where
						t1.EvnSection_pid = {$EvnAlias}.{$EvnClass}_id
						and t2.WorkData_begDate <= COALESCE({$EvnAlias}.{$EvnClass}_disDate, dbo.tzGetDate())
						and (t2.WorkData_endDate is null or t2.WorkData_endDate >= COALESCE({$EvnAlias}.{$EvnClass}_disDate, {$EvnAlias}.{$EvnClass}_setDate))
					limit 1
				)
				or exists (
					select t1.EvnPS_id
					from v_EvnPS t1 
						inner join v_MedStaffFact t2  on t2.LpuSection_id = t1.LpuSection_pid and (
							t2.MedPersonal_id in (".implode(',',$med_personal_list).")
						)
					where
						t1.EvnPS_id = {$EvnAlias}.{$EvnClass}_id
						and t2.WorkData_begDate <= COALESCE({$EvnAlias}.{$EvnClass}_disDate, dbo.tzGetDate())
						and (t2.WorkData_endDate is null or t2.WorkData_endDate >= COALESCE({$EvnAlias}.{$EvnClass}_disDate, {$EvnAlias}.{$EvnClass}_setDate))
					limit 1
				)
				or exists(
					select WG.WorkGraph_id
					from v_WorkGraph WG
					inner join v_MedStaffFact MSF on (MSF.MedStaffFact_id = WG.MedStaffFact_id and MSF.MedPersonal_id in (".implode(',',$med_personal_list)."))
					where (
						CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
						and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
					)
					limit 1
				)
				)";
			}
		}

		if($this->getRegionNick() == 'ufa' && isSuperAdmin()) {
			return " 1 = 1 ";
		}

		return $queryPart;
	}

	function RunReportsInjuryJournal($data){

		switch($data['kindJournal_id']){
			case '1': $where = " pt.TraumaClass_id = 3"; break;
			case '2': $where = " pt.TraumaType_id = 1 and pt.TraumaClass_id < 6"; break;
			case '3': $where = " eps.EvnPS_IsUnlaw = 2"; break;
		};

		$select = '';
		if (getRegionNick() != 'kz') {
			$select = "
				to_char(tce.TraumaCircumEvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"TraumaCircumEvnPS_setDT\",
				tce.TraumaCircumEvnPS_Name as \"TraumaCircumEvnPS_Name\",
			";
		}
		
		$query = "
				select
					eps.EvnPS_NumCard as \"EvnPS_NumCard\",
					coalesce(ps.Person_SurName,'') || ' ' || coalesce(ps.Person_FirName,'') || ' ' || coalesce(ps.Person_SecName,'') as \"Person_FIO\",
					ps.Polis_Num as \"Polis_Num\",
					to_char(ps.Person_BirthDay,'DD.MM.YYYY') as \"Person_BirthDay\",
					pd.PrehospDirect_Name as \"PrehospDirect_Name\",
					pa.PrehospArrive_Name as \"PrehospArrive_Name\",
				    {$select}
					to_char(eps.EvnPS_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
					d.Diag_Code || ' ' || d.Diag_Name as \"Diag\",
					de.Diag_Code || ' ' || de.Diag_Name as \"Diag_eid\",
					case
						when eps.PrehospWaifRefuseCause_id = '10' then to_char(eps.EvnPS_OutcomeDT,'DD.MM.YYYY')
						when lt.LeaveType_SysNick ilike '%die%' then to_char(es.EvnSection_disDate,'DD.MM.YYYY')
						else ' '
					end as \"EvnPS_OutcomeDT\",
					dp.DeathPlace_Name as \"DeathPlace\"
				from v_PrehospTrauma pt
					left join v_EvnPS eps on eps.PrehospTrauma_id = pt.PrehospTrauma_id
					left join v_PersonState ps on eps.Person_id = ps.Person_id
					left join v_PrehospDirect pd on pd.PrehospDirect_id = eps.PrehospDirect_id
					left join v_PrehospArrive pa on PA.PrehospArrive_id = eps.PrehospArrive_id
					left join v_TraumaCircumEvnPS tce on tce.EvnPS_id = eps.EvnPS_id
					left join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id and es.EvnSection_Index = es.EvnSection_Count - 1
					left join v_Diag d on d.Diag_id =
						(
						case
							when eps.Diag_pid 	is not null then eps.Diag_pid
							when es.Diag_id 	is not null then es.Diag_id
							else eps.Diag_id
						end
						)
					left join v_Diag de on de.Diag_id = eps.Diag_eid
					left join v_LeaveType lt on es.LeaveType_id = lt.LeaveType_id
					left join v_DeathSvid ds on eps.Person_id = ds.Person_id
					left join v_DeathPlace dp on dp.DeathPlace_id = ds.DeathPlace_id
				where
					eps.Lpu_id = :Lpu_id and
					EvnPS_setDate >= :begDate and
					EvnPS_setDate <= :endDate and
					{$where}
				";
		$result = $this->db->query($query, array('begDate' => $data['begDate'],'endDate' => $data['endDate'],'Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPSEditForm($data) {
		$params = array(
			'EvnPS_id' => $data['EvnPS_id']
			,'Lpu_id' => $data['Lpu_id']
		);

		$accessType = $this->getAccessTypeQueryPart(array(
			'EvnAlias' => 'EPS',
			'session' => $data['session']
		), $params);

		$selectEvnDirectionData = "
			,EPS.PrehospDirect_id as \"PrehospDirect_id\"
			,EPS.EvnDirection_Num as \"EvnDirection_Num\"
			,to_char(EPS.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\"
			,coalesce(EPS.Org_did, LPU_DID.Org_id, EPS.OrgMilitary_did, ED.Org_sid) as \"Org_did\" /* Направившая организация */
			,EPS.MedStaffFact_did as \"MedStaffFact_did\"
			,EPS.MedPersonal_did as \"MedPersonal_did\"
			,EPS.MedStaffFact_TFOMSCode as \"MedStaffFact_TFOMSCode\"
			,EPS.LpuSection_did as \"LpuSection_did\"
			,coalesce(EPS.Lpu_did, ED.Lpu_sid, ED.Lpu_id) as \"Lpu_did\" /* Направившая МО */
			,COALESCE(EPS.Diag_did, ED.Diag_id) as \"Diag_did\"
			,EPS.EvnDirection_id as \"EvnDirection_id\"
			,EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\"
			,ED.DirType_id as \"DirType_id\"
			,COALESCE(ED.EvnDirection_IsAuto,1) as \"EvnDirection_IsAuto\"
			,COALESCE(ED.EvnDirection_IsReceive,1) as \"EvnDirection_IsReceive\"
		";

		$joins = '';
		$fields = '';
        if(getRegionNick() != 'kz') {

			$fields .= "
				,{$this->getScaleFields()}
				to_char(EPS.EvnPS_CmpTltDT, 'DD.MM.YYYY') as \"EvnPS_CmpTltDate\"
				,to_char(EPS.EvnPS_CmpTltDT, 'HH24:MI') as \"EvnPS_CmpTltTime\"
				,BRDO.ResultECG as \"ResultECG\"
				,to_char(FC.FamilyContact_msgDT, 'DD.MM.YYYY') as \"FamilyContact_msgDate\"
				,to_char(FC.FamilyContact_msgDT, 'HH24:MI') as \"FamilyContact_msgTime\"				
				,case
					when length(FC.FamilyContact_Phone) = 10 
					then '(' || left(FC.FamilyContact_Phone, 3) || ')-' || substring(FC.FamilyContact_Phone, 4, 3) || '-' ||
					    substring(FC.FamilyContact_Phone, 7, 2) || '-' || right(FC.FamilyContact_Phone, 2)
					else ''
				end as
			";
			$joins .= "
				left join dbo.v_BSKRegistry BR  on BR.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_BSKRegistryDataOks BRDO  on BRDO.BskRegistry_id = BR.BskRegistry_id
				left join v_ScaleLams SL  on SL.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_PrehospTraumaScale PTS  on PTS.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_FamilyContact FC on FC.EvnPS_id = EPS.EvnPS_id
			";

			if (getRegionNick() == 'vologda') {
				$fields .= "
					\"VologdaFamilyContact_Phone\"
					,CASE 
						WHEN FC.FamilyContact_FIO IS NULL AND FC.Person_id IS NOT NULL THEN P.Person_Fio
						WHEN FC.FamilyContact_FIO IS NOT NULL AND FC.Person_id IS NULL THEN FC.FamilyContact_FIO
					    ELSE ''
					END AS \"VologdaFamilyContact_FIO\"
					,FC.Person_id as \"FamilyContactPerson_id\"
				";

				$joins .= "
					LEFT JOIN v_Person_all P ON P.Person_id = FC.Person_id
				";
			} else {
				$fields .= "
					\"FamilyContact_Phone\"
					,FC.FamilyContact_FIO as \"FamilyContact_FIO\"
				";
			}
		}

		if(getRegionNick() == 'ekb') {
			$fields .= "
				,EPS.EvnPS_IsZNORemove as \"EvnPS_IsZNORemove\"
				,to_char(EPS.EvnPS_BiopsyDate, 'DD.MM.YYYY') as \"EvnPS_BiopsyDate\"
			";
		}

		if(getRegionNick() == 'kz') {
			$fields .= "
				,ebel.GetBed_id as \"GetBed_id\"
				,edla.PurposeHospital_id as \"PurposeHospital_id\"
				,edla.Diag_cid as \"Diag_cid\"
			";
			$joins .= "
				left join r101.GetBedEvnLink ebel on ebel.Evn_id = EPS.EvnPS_id
                left join r101.EvnLinkAPP edla on edla.Evn_id = EPS.EvnPS_id
			";
		}
		
        if(in_array(getRegionNick(), array('vologda','msk'))){
            $joins .= "
				left join lateral(
					SELECT * 
					FROM v_Pediculos P
					WHERE P.Evn_id = EPS.EvnPS_id
					ORDER BY P.Pediculos_insDT DESC
					limit 1
				) Pediculos on true
			";
            $fields .= "
				,Pediculos.Pediculos_id as \"Pediculos_id\"
				,to_char(Pediculos.Pediculos_SanitationDT, 'dd.mm.yyyy') as \"Pediculos_Sanitation_setDate\"
				,to_char(Pediculos.Pediculos_SanitationDT, 'hh24:mi') as \"Pediculos_Sanitation_setTime\"
				,CASE WHEN Pediculos.Diag_id IS NOT NULL THEN 1 ELSE NULL END AS \"isPediculos\"
				,CASE WHEN Pediculos.Diag_sid IS NOT NULL THEN 1 ELSE NULL END AS \"isScabies\"
				,coalesce(Pediculos.Diag_id,0) AS \"PediculosDiag_id\"
				,coalesce(Pediculos.Diag_sid,0) AS \"ScabiesDiag_id\"
				,Pediculos.Pediculos_isSanitation as \"Pediculos_isSanitation\"
				,coalesce(Pediculos.Pediculos_isPrint,1) AS \"Pediculos_isPrint\"
				,CASE WHEN
					Pediculos.Pediculos_id IS NOT NULL AND (Pediculos.Diag_id IS NOT null or Pediculos.Diag_sid IS NOT null) THEN 1
					ELSE 0
				END AS \"buttonPrint058\"
			";
        }else{
            $fields .= "
				,NULL as \"Pediculos_id\"
				,'' as \"Pediculos_Sanitation_setDate\"
				,'' as \"Pediculos_Sanitation_setDate\"
				,NULL as \"isPediculos\"
				,NULL as \"isScabies\"
				,'' as \"PediculosDiag_id\"
				,'' as \"ScabiesDiag_id\"
				,NULL as \"Pediculos_isSanitation\"
				,NULL as \"buttonPrint058\"
				,NULL as \"Pediculos_isPrint\"
			";
        }

		if (getRegionNick() == 'msk') {
			$joins .= "
				left join lateral(
					select
						ceps.CovidType_id,
						ceps.DiagConfirmType_id,
						ceps.RepositoryObserv_BreathRate,
						ceps.RepositoryObserv_Systolic,
						ceps.RepositoryObserv_Diastolic,
						ceps.RepositoryObserv_Height,
						ceps.RepositoryObserv_Weight,
						ceps.RepositoryObserv_TemperatureFrom,
						ceps.RepositoryObserv_FluorographyDate,
						ceps.RepositoryObserv_SpO2
					from
						v_RepositoryObserv ceps
					where
						ceps.Evn_id = EPS.EvnPS_id
					ORDER BY
						ceps.RepositoryObserv_updDT DESC
					limit 1
				) ceps on true
			";

			$fields .= "
				, ceps.CovidType_id as \"CovidType_id\"
				, ceps.DiagConfirmType_id as \"DiagConfirmType_id\"
				, ceps.RepositoryObserv_BreathRate as \"RepositoryObserv_BreathRate\"
				, ceps.RepositoryObserv_Systolic as \"RepositoryObserv_Systolic\"
				, ceps.RepositoryObserv_Diastolic as \"RepositoryObserv_Diastolic\"
				, ceps.RepositoryObserv_Height as \"RepositoryObserv_Height\"
				, ceps.RepositoryObserv_Weight as \"RepositoryObserv_Weight\"
				, ceps.RepositoryObserv_TemperatureFrom as \"RepositoryObserv_TemperatureFrom\"
				, to_char(ceps.RepositoryObserv_FluorographyDate, 'DD.MM.YYYY') as \"RepositoryObserv_FluorographyDate\"
				, ceps.RepositoryObserv_SpO2 as \"RepositoryObserv_SpO2\"
			";
		}
        
		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\"
				,EPS.EvnPS_id as \"EvnPS_id\"
				,EPS.EvnPS_IsSigned as \"EvnPS_IsSigned\"
				,EPS.Lpu_id as \"Lpu_id\"
				,COALESCE(EPS.EvnPS_IsTransit, 1) as \"EvnPS_IsTransit\"
				,EPS.EvnPS_IsCont as \"EvnPS_IsCont\"
				,EPS.DiagSetPhase_did as \"DiagSetPhase_did\"
				,EPS.EvnPS_PhaseDescr_did as \"EvnPS_PhaseDescr_did\"
				,EPS.Diag_pid as \"Diag_pid\"
				,EPS.Diag_eid as \"Diag_eid\"
				,TCEPS.TraumaCircumEvnPS_Name as \"TraumaCircumEvnPS_Name\"
			    ,to_char(TCEPS.TraumaCircumEvnPS_setDT, 'dd.mm.yyyy hh24:mi:ss') as \"TraumaCircumEvnPS_setDT\"
				,EPS.DiagSetPhase_pid as \"DiagSetPhase_pid\"
				,EPS.DiagSetPhase_aid as \"DiagSetPhase_aid\"
				,EPS.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\"
				,RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\"
				,EPS.LeaveType_id as \"LeaveType_id\"
				,EPS.PayType_id as \"PayType_id\"
				,to_char(EPS.EvnPS_setDT, 'DD.MM.YYYY') as \"EvnPS_setDate\"
				,to_char(EPS.EvnPS_setDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_setDT\"
				,to_char(EPS.EvnPS_disDT, 'DD.MM.YYYY HH24:MI') as \"EvnPS_disDT\"
				,to_char(EPS.EvnPS_setTime, 'HH24:MI') as \"EvnPS_setTime\"
				,to_char(EPS.EvnPS_OutcomeDT, 'DD.MM.YYYY') as \"EvnPS_OutcomeDate\"
				,to_char(EPS.EvnPS_OutcomeDT, 'HH24:MI') as \"EvnPS_OutcomeTime\"
				{$selectEvnDirectionData}
				,EPS.LpuSection_pid as \"LpuSection_pid\"
				,EPS.MedStaffFact_pid as \"MedStaffFact_pid\"
				,EPS.PrehospArrive_id as \"PrehospArrive_id\"
				,EPS.CmpCallCard_id as \"CmpCallCard_id\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,EPS.PrehospToxic_id as \"PrehospToxic_id\"
				,EPS.LpuSectionTransType_id as \"LpuSectionTransType_id\"
				,EPS.PrehospType_id as \"PrehospType_id\"
				,EPS.EvnPS_HospCount as \"EvnPS_HospCount\"
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.Okei_id as \"Okei_id\"
				,EPS.PrehospTrauma_id as \"PrehospTrauma_id\"
				,EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\"
				,EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\"
				,to_char(EPS.EvnPS_NotificationDT, 'DD.MM.YYYY') as \"EvnPS_NotificationDate\"
				,to_char(EPS.EvnPS_NotificationDT, 'HH24:MI') as \"EvnPS_NotificationTime\"
				,EPS.MedStaffFact_id as \"MedStaffFact_id\"
				,EPS.EvnPS_Policeman as \"EvnPS_Policeman\"
				,EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\"
				,EPS.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\"
				,EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\"
				,EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\"
				,EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\"
				,COALESCE(EPS.EvnPS_IsWaif, 1) as \"EvnPS_IsWaif\"
				,EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\"
				,EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\"
				,EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\"
				,ES.LpuSection_id as \"LpuSection_id\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,EPS.MedicalCareFormType_id as \"MedicalCareFormType_id\"
				,EPS.ResultClass_id as \"ResultClass_id\"
				,EPS.ResultDeseaseType_id as \"ResultDeseaseType_id\"
				,EPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\"
				,EPS.Person_id as \"Person_id\"
				,EPS.PersonEvn_id as \"PersonEvn_id\"
				,EPS.Server_id as \"Server_id\"
				,EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\"
				,EPS.EvnQueue_id as \"EvnQueue_id\"
				,EPS.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\"
				,to_char(EPS.EvnPS_PrehospAcceptRefuseDT, 'DD.MM.YYYY') as \"EvnPS_PrehospAcceptRefuseDT\"
				,to_char(EPS.EvnPS_PrehospWaifRefuseDT, 'DD.MM.YYYY') as \"EvnPS_PrehospWaifRefuseDT\"
				,EPS.LpuSection_eid as \"LpuSection_eid\"
				,ES.LpuSectionWard_id as \"LpuSectionWard_id\"
				,Child.LpuSectionWard_id as \"LpuSectionWard_id\"
				,Child.LpuSectionBedProfileLink_id as \"LpuSectionBedProfileLink_id\"
				,EPS.PrehospStatus_id as \"PrehospStatus_id\"
				,to_char(EPS.EvnPS_HTMBegDate, 'DD.MM.YYYY') as \"EvnPS_HTMBegDate\"
				,to_char(EPS.EvnPS_HTMHospDate, 'DD.MM.YYYY') as \"EvnPS_HTMHospDate\"
				,EPS.EvnPS_HTMTicketNum as \"EvnPS_HTMTicketNum\"
				,ES.UslugaComplex_id as \"UslugaComplex_id\"
				,ES.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,EPS.EntranceModeType_id as \"EntranceModeType_id\"
				,EPS.EvnPS_IsActive as \"EvnPS_IsActive\"
				,EPS.DeseaseType_id as \"DeseaseType_id\"
				,EPS.TumorStage_id as \"TumorStage_id\"
				,EPS.EvnPS_IsZNO as \"EvnPS_IsZNO\"
				,EPS.Diag_spid as \"Diag_spid\"
				,Child.ChildLpuSection_id as \"ChildLpuSection_id\"
				,to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\"
				,ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\"
				,ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\"
				,ES.LeaveType_prmid as \"LeaveType_prmid\"
				,PRMLT.LeaveType_SysNick as \"LeaveType_prmSysNick\"
				,ES.LeaveType_fedid as \"LeaveType_fedid\"
				,ES.ResultDeseaseType_fedid as \"ResultDeseaseType_fedid\"
				,COALESCE(ES.EvnSection_IsPaid, 1) as \"EvnSection_IsPaid\"
				,COALESCE(ES.EvnSection_IndexRep, 0) as \"EvnPS_IndexRep\"
				,COALESCE(ES.EvnSection_IndexRepInReg, 1) as \"EvnPS_IndexRepInReg\"
				,case when PNB.BirthSpecStac_id is not null then 1 else 0 end as \"childPS\"
				,EPS.EvnPS_isMseDirected as \"EvnPS_isMseDirected\"
				,ES.EvnSection_id as \"EvnSection_id\"
				{$fields}
			FROM
				v_EvnPS EPS 
				left join v_EvnCostPrint ecp  on ecp.Evn_id = EPS.EvnPS_id
				left join v_TraumaCircumEvnPS tceps on tceps.EvnPS_id = EPS.EvnPS_id
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_EvnDirectionHTM EDH  on EDH.EvnDirectionHTM_id = EPS.EvnDirection_id
				left join v_Lpu LPU_DID  on LPU_DID.Lpu_id = EPS.Lpu_did
				left join v_EvnSection ES  on EPS.EvnPS_id = ES.EvnSection_pid and ES.EvnSection_Index = 0
				left join v_LeaveType PRMLT on PRMLT.LeaveType_id = ES.LeaveType_prmid
				LEFT JOIN LATERAL (
					select
						LpuSection_id as ChildLpuSection_id,
						LpuSectionWard_id as LpuSectionWard_id,
						LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_id
					from
						v_EvnSection 
					where
						EvnSection_pid = :EvnPS_id
						and (EvnSection_isPriem is null or EvnSection_isPriem = 1)
					order by EvnSection_index
					limit 1
				) Child ON true
				left join v_PersonNewBorn PNB  on PNB.EvnPS_id = EPS.EvnPS_id
				$joins
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
			limit 1
		";
		//echo getDebugSQL($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPSEditFormForDelDocs($data) {
		$params = ['EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id']];

		$selectEvnDirectionData = "
				,EPS.PrehospDirect_id as \"PrehospDirect_id\"
				,EPS.EvnDirection_Num as \"EvnDirection_Num\"
				,to_char(EPS.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\"
				,coalesce(EPS.Org_did, LPU_DID.Org_id, EPS.OrgMilitary_did, ED.Org_sid) as \"Org_did\" 
				,EPS.MedStaffFact_did as \"MedStaffFact_did\"
				,EPS.MedPersonal_did as \"MedPersonal_did\"
				,EPS.MedStaffFact_TFOMSCode as \"MedStaffFact_TFOMSCode\"
				,EPS.LpuSection_did as \"LpuSection_did\"
				,coalesce(EPS.Lpu_did, ED.Lpu_sid, ED.Lpu_id) as \"Lpu_did\" 
				,coalesce(EPS.Diag_did, ED.Diag_id) as \"Diag_did\"
				,EPS.EvnDirection_id as \"EvnDirection_id\"
				,EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\"
				,ED.DirType_id as \"DirType_id\"
				,coalesce(ED.EvnDirection_IsAuto,1) as \"EvnDirection_IsAuto\"
				,coalesce(ED.EvnDirection_IsReceive,1) as \"EvnDirection_IsReceive\"
		";

		$joins = '';
		$fields = '';
		if(getRegionNick() != 'kz') {

			$fields .= "
				,{$this->getScaleFields()}
				to_char(EPS.EvnPS_CmpTltDT, 'DD.MM.YYYY') as \"EvnPS_CmpTltDate\"
				,to_char(EPS.EvnPS_CmpTltDT, 'HH24:MI:SS') as \"EvnPS_CmpTltTime\"
				,BRDO.ResultECG as \"ResultECG\"
				,to_char(FC.FamilyContact_msgDT, 'DD.MM.YYYY') as \"FamilyContact_msgDate\"
				,to_char(FC.FamilyContact_msgDT, 'HH24:MI:SS') as \"FamilyContact_msgTime\"				
				,case
					when length(FC.FamilyContact_Phone) = 10 
					then '(' || left(FC.FamilyContact_Phone, 3) || ')-' || substring(FC.FamilyContact_Phone, 4, 3) || '-' ||
					    substring(FC.FamilyContact_Phone, 7, 2) || '-' || right(FC.FamilyContact_Phone, 2)
					else ''
				end as \"FamilyContact_Phone\"
				,FC.FamilyContact_FIO as \"FamilyContact_FIO\"
			";
			$joins .= "
				left join dbo.v_BSKRegistry BR on BR.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_BSKRegistryDataOks BRDO on BRDO.BskRegistry_id = BR.BskRegistry_id
				left join v_ScaleLams SL on SL.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_PrehospTraumaScale PTS on PTS.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_FamilyContact FC on FC.EvnPS_id = EPS.Evn_id
			";
		};

		if(getRegionNick() == 'ekb') {
			$fields .= "
				,EPS.EvnPS_IsZNORemove as \"EvnPS_IsZNORemove\"
				,to_char(EPS.EvnPS_BiopsyDate, 'DD.MM.YYYY') as \"EvnPS_BiopsyDate\"
			";
		}

		if(getRegionNick() == 'kz') {
			$fields .= "
				,ebel.GetBed_id as \"GetBed_id\"
				,edla.PurposeHospital_id as \"PurposeHospital_id\"
				,edla.Diag_cid as \"Diag_cid\"
			";
			$joins .= "
				left join r101.GetBedEvnLink ebel on ebel.Evn_id = EPS.Evn_id
				left join r101.EvnLinkAPP edla on edla.Evn_id = EPS.Evn_id
			";
		}

		if(in_array(getRegionNick(), array('vologda','msk','ufa'))){
			$joins .= "
				left join lateral(
					select
						* 
					from v_Pediculos P
					where 
						P.Evn_id = EPS.Evn_id
					order by P.Pediculos_insDT desc
				) Pediculos on true
			";
			$fields .= "
				,Pediculos.Pediculos_id as \"Pediculos_id\"
				,to_char(Pediculos.Pediculos_SanitationDT, 'DD.MM.YYYY') as \"Pediculos_Sanitation_setDate\"
				,to_char(Pediculos.Pediculos_SanitationDT, 'HH24:MI:SS') as \"Pediculos_Sanitation_setTime\"
				,case when Pediculos.Diag_id is not null then 1 else null end as \"isPediculos\"
				,case when Pediculos.Diag_sid is not null then 1 else null end as \"isScabies\"
				,Pediculos.Diag_id as \"PediculosDiag_id\"
				,Pediculos.Diag_sid as \"ScabiesDiag_id\"
				,Pediculos.Pediculos_isSanitation as \"Pediculos_isSanitation\"
				,coalesce(Pediculos.Pediculos_isPrint,1) as \"Pediculos_isPrint\"
				,case 
					when 
						Pediculos.Pediculos_id is not null 
						and (Pediculos.Diag_id is not null or Pediculos.Diag_sid is not null) 
					then 1
					else 0
				end as \"buttonPrint058\"
			";
		}else{
			$fields .= "
				,null as \"Pediculos_id\"
				,'' as \"Pediculos_Sanitation_setDate\"
				,'' as \"Pediculos_Sanitation_setDate\"
				,null as \"isPediculos\"
				,null as \"isScabies\"
				,'' as \"PediculosDiag_id\"
				,'' as \"ScabiesDiag_id\"
				,null as \"Pediculos_isSanitation\"
				,null as \"buttonPrint058\"
				,null as \"Pediculos_isPrint\"
			";
		}

		if (getRegionNick() == 'msk') {
			$joins .= "
				left join lateral(
					select
						ceps.CovidType_id as \"CovidType_id\",
						ceps.DiagConfirmType_id as \"DiagConfirmType_id\",
						ceps.RepositoryObserv_BreathRate as \"RepositoryObserv_BreathRate\",
						ceps.RepositoryObserv_Systolic as \"RepositoryObserv_Systolic\",
						ceps.RepositoryObserv_Diastolic as \"RepositoryObserv_Diastolic\",
						ceps.RepositoryObserv_Height as \"RepositoryObserv_Height\",
						ceps.RepositoryObserv_Weight as \"RepositoryObserv_Weight\",
						ceps.RepositoryObserv_TemperatureFrom as \"RepositoryObserv_TemperatureFrom\",
						ceps.RepositoryObserv_FluorographyDate as \"RepositoryObserv_FluorographyDate\",
						ceps.RepositoryObserv_SpO2 as \"RepositoryObserv_SpO2\"
					from v_RepositoryObserv ceps
					where
						ceps.Evn_id = EPS.Evn_id
					order by
						ceps.RepositoryObserv_updDT desc
					limit 1
				) ceps
			";

			$fields .= "
				, ceps.CovidType_id as \"CovidType_id\"
				, ceps.DiagConfirmType_id as \"DiagConfirmType_id\"
				, ceps.RepositoryObserv_BreathRate as \"RepositoryObserv_BreathRate\"
				, ceps.RepositoryObserv_Systolic as \"RepositoryObserv_Systolic\"
				, ceps.RepositoryObserv_Diastolic as \"RepositoryObserv_Diastolic\"
				, ceps.RepositoryObserv_Height as \"RepositoryObserv_Height\"
				, ceps.RepositoryObserv_Weight as \"RepositoryObserv_Weight\"
				, ceps.RepositoryObserv_TemperatureFrom as \"RepositoryObserv_TemperatureFrom\"
				, to_char(ceps.RepositoryObserv_FluorographyDate, 'DD.MM.YYYY') as \"RepositoryObserv_FluorographyDate\"
				, ceps.RepositoryObserv_SpO2 as \"RepositoryObserv_SpO2\"
			";
		}

		$view = $data['delDocsView'] == 1 ? '' : 'v_';

		$query = "
			select
				'view' as \"accessType\"
				,EPS.Evn_id as \"EvnPS_id\"
				,Evn.Evn_IsSigned as \"Evn_IsSigned\"
				,Evn.Lpu_id as \"Lpu_id\"
				,coalesce(Evn.Evn_IsTransit, 1) as \"EvnPS_IsTransit\"
				,EPS.EvnPS_IsCont as \"EvnPS_IsCont\"
				,EPS.DiagSetPhase_did as \"DiagSetPhase_did\"
				,EPS.EvnPS_PhaseDescr_did as \"EvnPS_PhaseDescr_did\"
				,EPS.Diag_pid as \"Diag_pid\"
				,EPS.Diag_eid as \"Diag_eid\"
				,TCEPS.TraumaCircumEvnPS_Name as \"TraumaCircumEvnPS_Name\"
				,EPS.DiagSetPhase_pid as \"DiagSetPhase_pid\"
				,EPS.DiagSetPhase_aid as \"DiagSetPhase_aid\"
				,EPS.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\"
				,rtrim(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\"
				,EPS.LeaveType_id as \"LeaveType_id\"
				,EPS.PayType_id as \"PayType_id\"
				,to_char(Evn.Evn_setDT, 'DD.MM.YYYY') as \"EvnPS_setDate\"
				,to_char(Evn.Evn_setDT, 'DD.MM.YYYY') || ' ' || to_char(Evn.Evn_setDT, 'HH24:MI:SS') as \"EvnPS_setDT\"
				,to_char(Evn.Evn_disDT, 'DD.MM.YYYY') || ' ' || to_char(Evn.Evn_disDT, 'HH24:MI:SS') as \"EvnPS_disDT\"
				,to_char(Evn.Evn_setDT, 'HH24:MI:SS') as \"EvnPS_setTime\"
				,to_char(EPS.EvnPS_OutcomeDT, 'DD.MM.YYYY') as \"EvnPS_OutcomeDate\"
				,to_char(EPS.EvnPS_OutcomeDT, 'HH24:MI:SS') as \"EvnPS_OutcomeTime\"
				{$selectEvnDirectionData}
				,EPS.LpuSection_pid as \"LpuSection_pid\"
				,EPS.MedStaffFact_pid as \"MedStaffFact_pid\"
				,EPS.PrehospArrive_id as \"PrehospArrive_id\"
				,EPS.CmpCallCard_id as \"CmpCallCard_id\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,EPS.PrehospToxic_id as \"PrehospToxic_id\"
				,EPS.LpuSectionTransType_id as \"LpuSectionTransType_id\"
				,EPS.PrehospType_id as \"PrehospType_id\"
				,EPS.EvnPS_HospCount as \"EvnPS_HospCount\"
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.Okei_id as \"Okei_id\"
				,EPS.PrehospTrauma_id as \"PrehospTrauma_id\"
				,EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\"
				,EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\"
				,to_char(EPS.EvnPS_NotificationDT, 'DD.MM.YYYY') as \"EvnPS_NotificationDate\"
				,to_char(EPS.EvnPS_NotificationDT, 'HH24:MI:SS') as \"EvnPS_NotificationTime\"
				,EPS.MedStaffFact_id as \"MedStaffFact_id\"
				,EPS.EvnPS_Policeman as \"EvnPS_Policeman\"
				,EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\"
				,EPS.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\"
				,EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\"
				,EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\"
				,EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\"
				,coalesce(EPS.EvnPS_IsWaif, 1) as \"EvnPS_IsWaif\"
				,EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\"
				,EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\"
				,EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\"
				,ES.LpuSection_id as \"LpuSection_id\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,EPS.MedicalCareFormType_id as \"MedicalCareFormType_id\"
				,EPS.ResultClass_id as \"ResultClass_id\"
				,EPS.ResultDeseaseType_id as \"ResultDeseaseType_id\"
				,EPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\"
				,Evn.Person_id as \"Person_id\"
				,Evn.PersonEvn_id as \"PersonEvn_id\"
				,Evn.Server_id as \"Server_id\"
				,EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\"
				,EPS.EvnQueue_id as \"EvnQueue_id\"
				,EPS.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\"
				,to_char(EPS.EvnPS_PrehospAcceptRefuseDT, 'DD.MM.YYYY') as \"EvnPS_PrehospAcceptRefuseDT\"
				,to_char(EPS.EvnPS_PrehospWaifRefuseDT, 'DD.MM.YYYY') as \"EvnPS_PrehospWaifRefuseDT\"
				,EPS.LpuSection_eid as \"LpuSection_eid\"
				,ES.LpuSectionWard_id as \"LpuSectionWard_id\"
				,EPS.PrehospStatus_id as \"PrehospStatus_id\"
				,to_char(EPS.EvnPS_HTMBegDate, 'DD.MM.YYYY') as \"EvnPS_HTMBegDate\"
				,to_char(EPS.EvnPS_HTMHospDate, 'DD.MM.YYYY') as \"EvnPS_HTMHospDate\"
				,EPS.EvnPS_HTMTicketNum as \"EvnPS_HTMTicketNum\"
				,ES.UslugaComplex_id as \"UslugaComplex_id\"
				,ES.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,EPS.EntranceModeType_id as \"EntranceModeType_id\"
				,EPS.EvnPS_IsActive as \"EvnPS_IsActive\"
				,EPS.DeseaseType_id as \"DeseaseType_id\"
				,EPS.TumorStage_id as \"TumorStage_id\"
				,EPS.EvnPS_IsZNO as \"EvnPS_IsZNO\"
				,EPS.Diag_spid as \"Diag_spid\"
				,Child.ChildLpuSection_id as \"ChildLpuSection_id\"
				,to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\"
				,ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\"
				,ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\"
				,ES.LeaveType_prmid as \"LeaveType_prmid\"
				,ES.LeaveType_fedid as \"LeaveType_fedid\"
				,ES.ResultDeseaseType_fedid as \"ResultDeseaseType_fedid\"
				,coalesce(ES.EvnSection_IsPaid, 1) as \"EvnSection_IsPaid\"
				,coalesce(ES.EvnSection_IndexRep, 0) as \"EvnPS_IndexRep\"
				,coalesce(ES.EvnSection_IndexRepInReg, 1) as \"EvnPS_IndexRepInReg\"
				,case when PNB.BirthSpecStac_id is not null then 1 else 0 end as \"childPS\"
				,EPS.EvnPS_isMseDirected as \"EvnPS_isMseDirected\"
				,ES.EvnSection_id as \"EvnSection_id\"
				$fields
			from EvnPS EPS
				inner join Evn on EPS.Evn_id = Evn.Evn_id and Evn.EvnClass_id in (30)
				left join v_EvnCostPrint ecp on ecp.Evn_id = EPS.Evn_id
				left join v_TraumaCircumEvnPS tceps on tceps.EvnPS_id = EPS.Evn_id
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = EPS.EvnDirection_id
				left join v_Lpu LPU_DID on LPU_DID.Lpu_id = EPS.Lpu_did
				left join v_EvnSection ES on EPS.Evn_id = ES.EvnSection_pid and ES.EvnSection_Index = 0
				left join lateral (
					select
						LpuSection_id as ChildLpuSection_id
					from
						v_EvnSection
					where
						EvnSection_pid = :EvnPS_id
						and (EvnSection_isPriem is null or EvnSection_isPriem = 1)
					order by EvnSection_index
					limit 1
				) Child on true
				left join v_PersonNewBorn PNB on PNB.EvnPS_id = EPS.Evn_id
				$joins
			where (1 = 1)
				and EPS.Evn_id = :EvnPS_id
			limit 1
		";

		$result = $this->db->query($query, $params);

		return is_object($result) ?  $result->result('array') : false;
	}


	/**
	 * метод для МАРМ
	 */
	function mLoadEvnPSEditForm($data) {
		$joins = '';
		$fields = '';

		$params = array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$accessType = $this->getAccessTypeQueryPart(array(
			'EvnAlias' => 'EPS',
			'session' => $data['session']
		), $params);

		if(getRegionNick() != 'kz') {

			$fields .= "
				,{$this->getScaleFields()}
				to_char(EPS.EvnPS_CmpTltDT, 'dd.mm.yyyy') as \"EvnPS_CmpTltDate\"
				,to_char(EPS.EvnPS_CmpTltDT, 'hh24:mi') as \"EvnPS_CmpTltTime\"
				,BRDO.ResultECG as \"ResultECG\"
			";

			$joins = "
				left join dbo.v_BSKRegistry BR on BR.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_BSKRegistryDataOks BRDO on BRDO.BskRegistry_id = BR.BskRegistry_id
				left join v_ScaleLams SL on SL.CmpCallCard_id = EPS.CmpCallCard_id
				left join v_PrehospTraumaScale PTS on PTS.CmpCallCard_id = EPS.CmpCallCard_id
			";
		};

		if(getRegionNick() == 'ekb') {
			$fields = "
				,EPS.EvnPS_IsZNORemove as \"EvnPS_IsZNORemove\"
				,to_char(EPS.EvnPS_BiopsyDate, 'dd.mm.yyyy') as \"EvnPS_BiopsyDate\"
			";
		}

		if(getRegionNick() == 'kz') {
			$fields = "
				,ebel.GetBed_id as \"GetBed_id\"
			";
			$joins = "
				left join r101.GetBedEvnLink ebel on ebel.Evn_id = EPS.EvnPS_id
			";
		}

		$query = "
			SELECT
				case when {$accessType}
					then 'edit'
					else 'view'
				end as \"accessType\"
				,EPS.EvnPS_id as \"EvnPS_id\"
				,EPS.EvnPS_IsSigned as \"EvnPS_IsSigned\"
				,EPS.Lpu_id as \"Lpu_id\"
				,coalesce(EPS.EvnPS_IsTransit, 1) as \"EvnPS_IsTransit\"
				,EPS.EvnPS_IsCont as \"EvnPS_IsCont\"
				,EPS.DiagSetPhase_did as \"DiagSetPhase_did\"
				,EPS.EvnPS_PhaseDescr_did as \"EvnPS_PhaseDescr_did\"
				,EPS.Diag_pid as \"Diag_pid\"
				,EPS.Diag_eid as \"Diag_eid\"
				,EPS.DiagSetPhase_pid as \"DiagSetPhase_pid\"
				,EPS.DiagSetPhase_aid as \"DiagSetPhase_aid\"
				,EPS.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\"
				,RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\"
				,EPS.LeaveType_id as \"LeaveType_id\"
				,EPS.PayType_id as \"PayType_id\"
				,to_char(EPS.EvnPS_setDT, 'dd.mm.yyyy') as \"EvnPS_setDate\"
				,EPS.EvnPS_setTime as \"EvnPS_setTime\"
				,to_char(EPS.EvnPS_OutcomeDT, 'dd.mm.yyyy') as \"EvnPS_OutcomeDate\"
				,to_char(EPS.EvnPS_OutcomeDT, 'HH24:MI') as \"EvnPS_OutcomeTime\"
				,EPS.PrehospDirect_id as \"PrehospDirect_id\"
				,EPS.EvnDirection_Num as \"EvnDirection_Num\"
				,to_char(EPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\"
				,coalesce(EPS.Org_did, LPU_DID.Org_id, EPS.OrgMilitary_did, ED.Org_sid) as \"Org_did\" /* Направившая организация */
				,EPS.MedStaffFact_did as \"MedStaffFact_did\"
				,EPS.MedPersonal_did as \"MedPersonal_did\"
				,EPS.MedStaffFact_TFOMSCode as \"MedStaffFact_TFOMSCode\"
				,EPS.LpuSection_did as \"LpuSection_did\"
				,coalesce(EPS.Lpu_did, ED.Lpu_sid, ED.Lpu_id) as \"Lpu_did\" /* Направившая МО */
				,coalesce(EPS.Diag_did, ED.Diag_id) as \"Diag_did\"
				,EPS.EvnDirection_id as \"EvnDirection_id\"
				,EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\"
				,ED.DirType_id as \"DirType_id\"
				,coalesce(ED.EvnDirection_IsAuto,1) as \"EvnDirection_IsAuto\"
				,coalesce(ED.EvnDirection_IsReceive,1) as \"EvnDirection_IsReceive\"
				,EPS.LpuSection_pid as \"LpuSection_pid\"
				,EPS.MedStaffFact_pid as \"MedStaffFact_pid\"
				,EPS.PrehospArrive_id as \"PrehospArrive_id\"
				,EPS.CmpCallCard_id as \"CmpCallCard_id\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,EPS.PrehospToxic_id as \"PrehospToxic_id\"
				,EPS.LpuSectionTransType_id as \"LpuSectionTransType_id\"
				,EPS.PrehospType_id as \"PrehospType_id\"
				,EPS.EvnPS_HospCount as \"EvnPS_HospCount\"
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.Okei_id as \"Okei_id\"
				,EPS.PrehospTrauma_id as \"PrehospTrauma_id\"
				,EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\"
				,EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\"
				,to_char(EPS.EvnPS_NotificationDT, 'dd.mm.yyyy') as \"EvnPS_NotificationDate\"
				,to_char(EPS.EvnPS_NotificationDT, 'HH24:MI') as \"EvnPS_NotificationTime\"
				,EPS.MedStaffFact_id as \"MedStaffFact_id\"
				,EPS.EvnPS_Policeman as \"EvnPS_Policeman\"
				,EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\"
				,EPS.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\"
				,EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\"
				,EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\"
				,EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\"
				,coalesce(EPS.EvnPS_IsWaif, 1) as \"EvnPS_IsWaif\"
				,EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\"
				,EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\"
				,EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\"
				,ES.LpuSection_id as \"LpuSection_id\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,EPS.MedicalCareFormType_id as \"MedicalCareFormType_id\"
				,EPS.ResultClass_id as \"ResultClass_id\"
				,EPS.ResultDeseaseType_id as \"ResultDeseaseType_id\"
				,EPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\"
				,EPS.Person_id as \"Person_id\"
				,EPS.PersonEvn_id as \"PersonEvn_id\"
				,EPS.Server_id as \"Server_id\"
				,EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\"
				,EPS.EvnQueue_id as \"EvnQueue_id\"
				,EPS.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\"
				,to_char(EPS.EvnPS_PrehospAcceptRefuseDT, 'dd.mm.yyyy') as \"EvnPS_PrehospAcceptRefuseDT\"
				,to_char(EPS.EvnPS_PrehospWaifRefuseDT, 'dd.mm.yyyy') as \"EvnPS_PrehospWaifRefuseDT\"
				,EPS.LpuSection_eid as \"LpuSection_eid\"
				,EPS.PrehospStatus_id as \"PrehospStatus_id\"
				,to_char(EPS.EvnPS_HTMBegDate, 'dd.mm.yyyy') as \"EvnPS_HTMBegDate\"
				,to_char(EPS.EvnPS_HTMHospDate, 'dd.mm.yyyy') as \"EvnPS_HTMHospDate\"
				,EPS.EvnPS_HTMTicketNum as \"EvnPS_HTMTicketNum\"
				,ES.UslugaComplex_id as \"UslugaComplex_id\"
				,ES.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,EPS.EntranceModeType_id as \"EntranceModeType_id\"
				,EPS.DeseaseType_id as \"DeseaseType_id\"
				,EPS.TumorStage_id as \"TumorStage_id\"
				,EPS.EvnPS_IsZNO as \"EvnPS_IsZNO\"
				,EPS.Diag_spid as \"Diag_spid\"
				,Child.ChildLpuSection_id as \"ChildLpuSection_id\"
				,to_char(ecp.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"EvnCostPrint_setDT\"
				,ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\"
				,ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\"
				,ES.LeaveType_prmid as \"LeaveType_prmid\"
				,ES.LeaveType_fedid as \"LeaveType_fedid\"
				,ES.ResultDeseaseType_fedid as \"ResultDeseaseType_fedid\"
				,coalesce(ES.EvnSection_IsPaid, 1) as \"EvnSection_IsPaid\"
				,coalesce(ES.EvnSection_IndexRep, 0) as \"EvnPS_IndexRep\"
				,coalesce(ES.EvnSection_IndexRepInReg, 1) as \"EvnPS_IndexRepInReg\"
				,case when PNB.BirthSpecStac_id is not null then 1 else 0 end as \"childPS\"
				,EPS.EvnPS_isMseDirected as \"EvnPS_isMseDirected\"
				,dpid.Diag_Name as \"pid_DiagName\"
				,deid.Diag_Name as \"eid_DiagName\"
				,ddid.Diag_Name as \"did_DiagName\"
				,dspid.Diag_Name as \"spid_DiagName\"
				,dpid.Diag_Code as \"pid_Diag_Code\"
				,deid.Diag_Code as \"eid_Diag_Code\"
				,ddid.Diag_Code as \"did_Diag_Code\"
				,dspid.Diag_Code as \"spid_Diag_Code\"
				{$fields}
			FROM
				v_EvnPS EPS
				left join v_EvnCostPrint ecp on ecp.Evn_id = EPS.EvnPS_id
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = EPS.EvnDirection_id
				left join v_Lpu LPU_DID on LPU_DID.Lpu_id = EPS.Lpu_did
				left join v_EvnSection ES on EPS.EvnPS_id = ES.EvnSection_pid
					and ES.EvnSection_Index = 0
				left join v_Diag dpid on dpid.Diag_id = EPS.Diag_pid
				left join v_Diag deid on deid.Diag_id = EPS.Diag_eid
				left join v_Diag ddid on ddid.Diag_id = coalesce(EPS.Diag_did, ED.Diag_id)
				left join v_Diag dspid on dspid.Diag_id = EPS.Diag_spid
				left join lateral(
					select
						LpuSection_id as ChildLpuSection_id
					from
						v_EvnSection
					where
						EvnSection_pid = :EvnPS_id
						and (EvnSection_isPriem is null or EvnSection_isPriem = 1)
					order by EvnSection_index
					limit 1
				) Child on true
				left join v_PersonNewBorn PNB on PNB.EvnPS_id = EPS.EvnPS_id
				$joins
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
			limit 1
		";

		//echo getDebugSQL($query, $params);die();
		$result = $this->getFirstRowFromQuery($query, $params);

		/*if (!empty($result)) {
			$diagClasses = array('EvnDiagPSHosp', 'EvnDiagPSRecep', 'EvnDiagPSDie', 'EvnDiagPSSect');
			$this->load->model('EvnDiag_model');

			foreach ($diagClasses as $cls) {
				$result[$cls] = $this->EvnDiag_model->loadEvnDiagPSGrid(array(
					'class' => $cls,
					'EvnDiagPS_rid' => $data['EvnPS_id'],
					'Lpu_id' => $data['Lpu_id']
				));
			}
		}*/

		return $result;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPSList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EPS.Person_id = :Person_id";
		$queryParams['Person_id'] = $data['Person_id'];

		if ( !empty($data['EvnPS_id']) ) {
			$filter .= " and EPS.EvnPS_id = :EvnPS_id";
			$queryParams['EvnPS_id'] = $data['EvnPS_id'];
		}

		$query = "
			select
				 EPS.EvnPS_id as \"EvnPS_id\"
				,RTRIM(COALESCE(EPS.EvnPS_NumCard, '')) as \"EvnPS_NumCard\"
				,EPS.PrehospType_id as \"PrehospType_id\"
				,to_char(ED.EvnDie_setDT, 'DD.MM.YYYY') as \"EvnPS_deathDate\"
				,to_char(ED.EvnDie_setDT, 'HH24:MI') as \"EvnPS_deathTime\"
				,to_char(EPS.EvnPS_setDT, 'DD.MM.YYYY') as \"EvnPS_setDate\"
				,to_char(ES.EvnPS_disDT, 'DD.MM.YYYY') as \"EvnPS_disDate\"
			from
				v_EvnPS EPS 
				inner join v_LeaveType LT  on LT.LeaveType_id = EPS.LeaveType_id
					and LT.LeaveType_SysNick in ('cmpdieavt','cmpdiebrig','cmpdiedo','die','diepp','dsdie','dsdiepp','ksdie','ksdiepp')
				LEFT JOIN LATERAL (
					select
						max(EvnSection_disDate) as EvnPS_disDT
					from v_EvnSection 
					where EvnSection_pid = EPS.EvnPS_id
					limit 1
				) ES ON true
				LEFT JOIN LATERAL (
					select
						EvnDie_setDT
					from v_EvnDie 
					where EvnDie_pid = EPS.EvnPS_id
					limit 1
				) ED ON true
			where " . $filter . "
			order by
				EPS.EvnPS_setDT
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response)==0)  {
				$query = "
					select
						 EPS.EvnPS_id as \"EvnPS_id\"
						,RTRIM(COALESCE(EPS.EvnPS_NumCard, '')) as \"EvnPS_NumCard\"
						,EPS.PrehospType_id as \"PrehospType_id\"
						,to_char(ED.EvnDie_setDT, 'DD.MM.YYYY') as \"EvnPS_deathDate\"
						,to_char(ED.EvnDie_setDT, 'HH24:MI') as \"EvnPS_deathTime\"
						,to_char(EPS.EvnPS_setDT, 'DD.MM.YYYY') as \"EvnPS_setDate\"
						,to_char(ES.EvnPS_disDT, 'DD.MM.YYYY') as \"EvnPS_disDate\"
					from
						v_EvnPS EPS 
						LEFT JOIN LATERAL (
							select
								max(EvnSection_disDate) as EvnPS_disDT
							from v_EvnSection 
							where EvnSection_pid = EPS.EvnPS_id
							limit 1
						) ES ON true
						LEFT JOIN LATERAL (
							select
								EvnDie_setDT
							from v_EvnDie 
							where EvnDie_pid = EPS.EvnPS_id
							limit 1
						) ED ON true
					where " . $filter . "
						and EvnPS_disDate is null
						and EPS.PrehospWaifRefuseCause_id is null
					order by
						EPS.EvnPS_setDT desc
					limit 1
				";
				$result = $this->db->query($query, $queryParams);
				$response = $result->result('array');
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPSStreamList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EPS.pmUser_insID = :pmUser_id";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		$filter .= " and EPS.EvnPS_insDT >= :EvnPS_insDT";

		if ( (isset($data['begDate'])) && (isset($data['begTime'])) ) {
			$queryParams['EvnPS_insDT'] = $data['begDate'] . " " . $data['begTime'];
		}
		else {
			$queryParams['EvnPS_insDT'] = date('Y-m-d');
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EPS.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Person_id as \"Person_id\",
				EPS.Server_id as \"Server_id\",
				EPS.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				EPSKD.EvnPS_KoikoDni as \"EvnPS_KoikoDni\",
				to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\",
				case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
			FROM v_EvnPS EPS 
				inner join v_PersonState PS  on PS.Person_id = EPS.Person_id
				left join v_EvnCostPrint ecp  on ecp.Evn_id = EPS.EvnPS_id
				LEFT JOIN LATERAL (
					select
						DATEDIFF('DAY', min(EvnSection_setDate), max(EvnSection_disDate)) as EvnPS_KoikoDni
					from v_EvnSection 
					where EvnSection_pid = EPS.EvnPS_id and EvnSection_disDate is not null and EvnSection_setDate is not null 
					/*
					having
						max(EvnSection_disDate) is not null
						and min(EvnSection_setDate) is not null
					*/
				) EPSKD ON true
			WHERE " . $filter . "
			ORDER BY EPS.EvnPS_id desc
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadLeaveInfoGrid($data) {
		if ( isset($data['EvnLeave_id']) ) {
			$query = "
				select
					RTRIM(LC.LeaveCause_Name) as \"LeaveCause_Name\",
					RTRIM(RD.ResultDesease_Name) as \"ResultDesease_Name\",
					cast(EL.EvnLeave_UKL as numeric(10, 2)) as \"UKL\",
					to_char(EL.EvnLeave_setDate, 'DD.MM.YYYY') as \"setDate\",
					COALESCE(EL.EvnLeave_setTime, '') as \"setTime\",
					RTRIM(COALESCE(YesNo.YesNo_Name, '')) as \"IsAmbul\"
				from
					v_EvnLeave EL 
					inner join v_LeaveCause LC  on LC.LeaveCause_id = EL.LeaveCause_id
					inner join v_ResultDesease RD  on RD.ResultDesease_id = EL.ResultDesease_id
					left join v_YesNo  on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
				where
					EL.EvnLeave_id = :EvnLeave_id
				limit 1
			";
			$queryParams = array('EvnLeave_id' => $data['EvnLeave_id']);
		}
		else if ( isset($data['EvnOtherLpu_id']) ) {
			$query = "
				select
					RTRIM(LC.LeaveCause_Name) as \"OtherLeaveCause_Name\",
					RTRIM(RD.ResultDesease_Name) as \"ResultDesease_Name\",
					EOL.EvnOtherLpu_UKL as \"UKL\",
					to_char(EOL.EvnOtherLpu_setDate, 'DD.MM.YYYY') as \"setDateOther\",
					COALESCE(EOL.EvnOtherLpu_setTime, '') as \"setTimeOther\",
					RTRIM(COALESCE(Org.Org_Name, '')) as \"Lpu_Name\"
				from
					v_EvnOtherLpu EOL 
					inner join v_LeaveCause LC  on LC.LeaveCause_id = EOL.LeaveCause_id
					inner join v_ResultDesease RD  on RD.ResultDesease_id = EOL.ResultDesease_id
					left join v_Org Org  on Org.Org_id = EOL.Org_oid
				where
					EOL.EvnOtherLpu_id = :EvnOtherLpu_id
				limit 1
			";
			$queryParams = array('EvnOtherLpu_id' => $data['EvnOtherLpu_id']);
		}
		else if ( isset($data['EvnDie_id']) ) {
			$query = "
				select
					ED.EvnDie_UKL as \"UKL\",
					to_char(ED.EvnDie_setDate, 'DD.MM.YYYY') as \"deathDate\",
					COALESCE(ED.EvnDie_setTime, '') as \"deathTime\",
					RTRIM(COALESCE(MP.Person_Fio, '')) as \"MP_Anatom_Fio\",
					RTRIM(COALESCE(yn1.YesNo_Name, '')) as \"IsWait\",
					RTRIM(COALESCE(YesNo.YesNo_Name, '')) as \"IsAnatom\",
					RTRIM(COALESCE(Diag.Diag_Name, '')) as \"Diag_Anatom_Name\"
				from
					v_EvnDie ED 
					INNER JOIN LATERAL (
						select Person_Fio
						from v_MedPersonal 
						where MedPersonal_id = ED.MedPersonal_id
							and Lpu_id = ED.Lpu_id
						limit 1
					) MP ON true
					left join v_Diag  on Diag.Diag_id = ED.Diag_aid
					left join v_YesNo yn1  on yn1.YesNo_id = ED.EvnDie_IsWait
					left join v_YesNo  on YesNo.YesNo_id = ED.EvnDie_IsAnatom
				where
					ED.EvnDie_id = :EvnDie_id
				limit 1
			";
			$queryParams = array('EvnDie_id' => $data['EvnDie_id']);
		}
		else if ( isset($data['EvnOtherStac_id']) ) {
			$query = "
				select
					RTRIM(LC.LeaveCause_Name) as \"OtherLeaveCause_Name\",
					RTRIM(RD.ResultDesease_Name) as \"ResultDesease_Name\",
					EOS.EvnOtherStac_UKL as \"UKL\",
					to_char(EOS.EvnOtherStac_setDate, 'DD.MM.YYYY') as \"setDateOther\",
					COALESCE(EOS.EvnOtherStac_setTime, '') as \"setTimeOther\",
					RTRIM(COALESCE(LUT.LpuUnitType_Name, '')) as \"LpuUnitType_Name\",
					RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\"
				from
					v_EvnOtherStac EOS 
					inner join v_LeaveCause LC  on LC.LeaveCause_id = EOS.LeaveCause_id
					inner join v_ResultDesease RD  on RD.ResultDesease_id = EOS.ResultDesease_id
					inner join v_LpuUnitType LUT  on LUT.LpuUnitType_id = EOS.LpuUnitType_oid
					inner join v_LpuSection LS  on LS.LpuSection_id = EOS.LpuSection_oid
				where
					EOS.EvnOtherStac_id = :EvnOtherStac_id
				limit 1
			";
			$queryParams = array('EvnOtherStac_id' => $data['EvnOtherStac_id']);
		}
		else if ( isset($data['EvnOtherSection_id']) ) {
			$query = "
				select
					RTRIM(LC.LeaveCause_Name) as \"OtherLeaveCause_Name\",
					RTRIM(RD.ResultDesease_Name) as \"ResultDesease_Name\",
					EOS.EvnOtherSection_UKL as \"UKL\",
					to_char(EOS.EvnOtherSection_setDate, 'DD.MM.YYYY') as \"setDateOther\",
					COALESCE(EOS.EvnOtherSection_setTime, '') as \"setTimeOther\",
					RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\"
				from
					v_EvnOtherSection EOS 
					inner join v_LeaveCause LC  on LC.LeaveCause_id = EOS.LeaveCause_id
					inner join v_ResultDesease RD  on RD.ResultDesease_id = EOS.ResultDesease_id
					inner join v_LpuSection LS  on LS.LpuSection_id = EOS.LpuSection_oid
				where
					EOS.EvnOtherSection_id = :EvnOtherSection_id
				limit 1
			";
			$queryParams = array('EvnOtherSection_id' => $data['EvnOtherSection_id']);
		}
		else if ( isset($data['EvnOtherSectionBedProfile_id']) ) {
			$query = "
				select
					RTRIM(LC.LeaveCause_Name) as \"OtherLeaveCause_Name\",
					RTRIM(RD.ResultDesease_Name) as \"ResultDesease_Name\",
					EOSBP.EvnOtherSectionBedProfile_UKL as \"UKL\",
					to_char(EOSBP.EvnOtherSectionBedProfile_setDate, 'DD.MM.YYYY') as \"setDateOther\",
					COALESCE(EOSBP.EvnOtherSectionBedProfile_setTime, '') as \"setTimeOther\",
					RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\"
				from
					v_EvnOtherSectionBedProfile EOSBP 
					inner join v_LeaveCause LC  on LC.LeaveCause_id = EOSBP.LeaveCause_id
					inner join v_ResultDesease RD  on RD.ResultDesease_id = EOSBP.ResultDesease_id
					inner join v_LpuSection LS  on LS.LpuSection_id = EOSBP.LpuSection_oid
				where
					EOSBP.EvnOtherSectionBedProfile_id = :EvnOtherSectionBedProfile_id
				limit 1
			";
			$queryParams = array('EvnOtherSectionBedProfile_id' => $data['EvnOtherSectionBedProfile_id']);
		}
		else {
			return false;
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Определяем, есть ли движения с иным типом оплаты
	 * @param int $EvnPS_id
	 * @param int $PayType_id
	 * @return bool
	 */
	function hasEvnSectionWithOtherPayType($EvnPS_id, $PayType_id) {
		$query = "
			select EvnSection_id as \"EvnSection_id\" from v_EvnSection  where EvnSection_pid = :EvnPS_id and PayType_id != :PayType_id and COALESCE(EvnSection_IsPriem, 1) = 1
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $EvnPS_id,
			'PayType_id' => $PayType_id,
		));
		if ( is_object($result) ) {
			return count($result->result('array')) > 0;
		}
		else {
			return false;
		}
	}

	function getPayTypesFromSections($EvnPS_id)
	{
		return $this->queryResult("
			select
				pt.PayType_Name as \"PayType_Name\"
			from v_EvnSection es
				left join v_PayType pt on es.PayType_id = pt.PayType_id
			where es.EvnSection_pid = :EvnPS_id
		", [
			'EvnPS_id' => $EvnPS_id
		]);
	}

	/**
	 * Имеется ли у МО из направления период ОМС
	 */
	function hasLpuPeriodOMS() {
		if (empty($this->Org_did)) {
			return false;
		}
		$this->load->model('LpuPassport_model');
		$resp = $this->LpuPassport_model->hasLpuPeriodOMS(array(
			'Org_oid' => $this->Org_did,
			'Date' => $this->EvnDirection_setDT
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Message'], $resp[0]['Error_Code']);
		}
		return $resp[0]['hasLpuPeriodOMS'];
	}

	/**
	 * Сохранение КВС
	 * @param $data
	 * @return array
	 */
	function saveEvnPS($data)
	{
		return array($this->doSave($data));
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadHospitalizationsGrid($data) {

		// без фильтра по ЛПУ и участкам выводим карты ВС всех пациентов со всех участков данного врача
		if (isSuperAdmin())
		{
			$data['Lpu_aid'] = ($data['Lpu_aid']>0)?$data['Lpu_aid']:$data['session']['lpu_id'];
		}
		else
		{
			$data['Lpu_aid'] = $data['session']['lpu_id'];
		}
		$filters = "";
		$signal_filters = "";


		$query = "Select count(*) as \"rec\" from MedStaffRegion  where MedPersonal_id = :MedPersonal_id";
		$params = array('MedPersonal_id' => $data['session']['medpersonal_id']);
		$result = $this->db->query($query, $params);
		$gvrach = false;
		if (is_object($result)) {
			$res =  $result->result('array');
			if ((count($res) && $res[0]['rec']==0) && (($data['MedPersonal_id']==$data['session']['medpersonal_id']) || empty($data['MedPersonal_id']))) // НЕ участковый
			{
				//$filter = "Lpu_id = :Lpu_aid";
				$gvrach = true;
			}
		}
		$join_medstaffregion = '';
		if (isset($data['MedPersonal_id']) && ($data['MedPersonal_id'] > 0) && (!$gvrach))
		{
			// для участковых врачей показываем только пациентов с его участка
			// для сигнальной информации #137508 только для основных врачей на участке
			$join_medstaffregion = 'inner join v_MedStaffRegion MedStaffRegion  on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id and MedStaffRegion.MedPersonal_id = :MedPersonal_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
				LEFT JOIN persis.Post p  on p.id = msf.Post_id
				';
		}

		if (isset($data['LpuRegion_id']) && $data['LpuRegion_id'] > 0)
		{
			$filters .= "and PC.LpuRegion_id = :LpuRegion_id ";
		}
		if (isset($data['Person_Surname']) && $data['Person_Surname'] !="")
		{
			$filters .= "and lower(PC.Person_Surname) like lower(:Person_Surname) ";

		}
		if (isset($data['Person_Firname']) && $data['Person_Firname'] !="")
		{
			$filters .= "and lower(PC.Person_Firname) like lower(:Person_Firname) ";

		}
		if (isset($data['Person_Secname']) && $data['Person_Secname'] !="")
		{
			$filters .= "and lower(PC.Person_Secname) like lower(:Person_Secname) ";

		}
		if (isset($data['Person_Birthday']) && $data['Person_Birthday'] !="")
		{
			$filters .= "and PC.Person_Birthday = :Person_Birthday ";

		}
		if (isset($data['Person_Birthday_Range_0']) && $data['Person_Birthday_Range_0'] !="")
		{
			$filters .= "and PC.Person_Birthday >= :Person_Birthday_Range_0 ";

		}
		if (isset($data['Person_Birthday_Range_1']) && $data['Person_Birthday_Range_1'] !="")
		{
			$filters .= "and PC.Person_Birthday <= :Person_Birthday_Range_1 ";

		}
		/*
		if (isset($data['Lpu_aid']) && $data['Lpu_aid'] > 0 && empty($data['LpuRegion_id']) && (isSuperAdmin()))
			$filter = "LpuAtt.Lpu_id = :Lpu_aid";
		*/

		$filters_esdate = "";
		if (!empty($data['EvnPS_setDateTime_Start']))
			$filters_esdate .= " AND es.EvnSection_setDate >= '".$data['EvnPS_setDateTime_Start']."'";

		if (!empty($data['EvnPS_setDateTime_End']))
			$filters_esdate .= " AND es.EvnSection_setDate <= '".$data['EvnPS_setDateTime_End']."'";

		if (isset($data['isEvnDirection']) && $data['isEvnDirection'] == 2)
			$filters .= " AND EPS.EvnDirection_id is not null ";
		if (isset($data['isEvnDirection']) && $data['isEvnDirection'] == 1)
			$filters .= " AND EPS.EvnDirection_id is null ";

		if ( isset($data['EvnPS_IsNeglectedCase']) && $data['EvnPS_IsNeglectedCase'] > 0 )
			$filters .= " AND COALESCE(EPS.EvnPS_IsNeglectedCase, 1) = :EvnPS_IsNeglectedCase ";

		if (isset($data['PrehospType_id']) && $data['PrehospType_id'] > 0)
			$filters .= " AND EPS.PrehospType_id = :PrehospType_id";

		if (isset($data['PrehospArrive_id']) && $data['PrehospArrive_id'] > 0)
			$filters .= " AND EPS.PrehospArrive_id = :PrehospArrive_id";

		if (isset($data['Org_oid']) && $data['Org_oid'] > 0)
			$filters .= " AND EPS.Lpu_id = :Org_oid";

		if (isset($data['NotLeave']) && $data['NotLeave'] == 'true')
		{
			$filters .= " AND EPS.LeaveType_id is null";
			$data['LeaveType_id'] = 0;
		}

		if (isset($data['LeaveType_id']) && $data['LeaveType_id'] > 0)
			$filters .= " AND EPS.LeaveType_id = :LeaveType_id";

		if (isset($data['ResultDesease_id']) && $data['ResultDesease_id'] > 0)
			$filters .= " AND EPS.EvnPS_id in (select EvnLeave_pid from v_EvnLeave  where EvnLeave_pid is not null AND ResultDesease_id = :ResultDesease_id
			UNION ALL
			select EvnOtherLpu_pid from v_EvnOtherLpu  where EvnOtherLpu_pid is not null  AND ResultDesease_id = :ResultDesease_id
			UNION ALL
			select EvnOtherSection_pid from v_EvnOtherSection  where EvnOtherSection_pid  is not null  AND ResultDesease_id = :ResultDesease_id
			UNION ALL
			select EvnOtherStac_pid from v_EvnOtherStac  where EvnOtherStac_pid is not null  AND ResultDesease_id = :ResultDesease_id)";

		if (isset($data['LeaveCause_id']) && $data['LeaveCause_id'] > 0)
			$filters .= " AND EPS.EvnPS_id in (select EvnLeave_pid from v_EvnLeave  where EvnLeave_pid is not null AND LeaveCause_id = :LeaveCause_id
			UNION ALL
			select EvnOtherLpu_pid from v_EvnOtherLpu  where EvnOtherLpu_pid is not null  AND LeaveCause_id = :LeaveCause_id
			UNION ALL
			select EvnOtherSection_pid from v_EvnOtherSection  where EvnOtherSection_pid  is not null  AND LeaveCause_id = :LeaveCause_id
			UNION ALL
			select EvnOtherStac_pid from v_EvnOtherStac  where EvnOtherStac_pid is not null  AND LeaveCause_id = :LeaveCause_id)";

		/*
		if (isset($data['EvnPS_disDateTime_Start']) && strlen($data['EvnPS_disDateTime_Start']) > 0)
			$filters .= " AND cast(EPS.EvnPS_disDate as date) >= '".substr($data['EvnPS_disDateTime_Start'], 0, strpos($data['EvnPS_disDateTime_Start'],"T"))."'";
		if (isset($data['EvnPS_disDate_End']) && strlen($data['EvnPS_disDateTime_End']) > 0)
			$filters .= " AND cast(EPS.EvnPS_disDateTime as date) <= '".substr($data['EvnPS_disDateTime_End'], 0, strpos($data['EvnPS_disDateTime_End'],"T"))."'";
		*/

		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('EPS.Lpu_id');
		if (!empty($lpuFilter)) {
			$filters .= " and $lpuFilter";
		}

		if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
			$signal_filters = "and PC.LpuAttachType_id in (1)

			and (cast(ES.EvnSection_PlanDisDT as date) = cast(DATEADD ('day', 1, dbo.tzGetDate()) as date) or
				(EPS.EvnPS_disDate >= '".$data['EvnPS_disDateTime_Start']."' AND EPS.EvnPS_disDate <= '".$data['EvnPS_disDateTime_End']."' and LT.LeaveType_Code = 1))
			and MedStaffRegion.MedStaffRegion_isMain = 2 -- основной врач на участке
			and p.code in (74,47,40,117,111) -- c должностями на участке 
				";
		} else {
			$signal_filters = 'and PC.LpuAttachType_id in (1,2,3,4)
			and (PC.LpuAttachType_id != 4 or PC.Lpu_id = ES.Lpu_id)';
		}

		$addit_query = "
			select distinct
				PC.LpuAttachType_id as LpuAttachType_id,
				ES.EvnSection_id as EvnSection_id,
				EPS.EvnPS_id as EvnPS_id,
				EPS.Person_id as Person_id,
				EPS.PersonEvn_id as PersonEvn_id,
				EPS.Server_id as Server_id,
				ED.Lpu_did as Lpu_did,
				ED.MedPersonal_id as MedPersonal_did, -- Направивший врач
				-- MSR.MedPersonal_id as MedPersonal_aid, -- Участковый врач
				ZMPD.MedPersonal_id as MedPersonal_zdid, -- Заведующий отделением направившего врача
				EPS.PrehospType_id as PrehospType_id,
				--Поступил - косяк должна быть дата поступления в отделение!
				--COALESCE(to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY'), '') || ' ' || COALESCE(EPS.EvnPS_setTime, '') as \"EvnPS_setDateTime\",
				COALESCE(
					to_char(ES.EvnSection_setDate, 'DD.MM.YYYY') ||  ' ' || COALESCE(ES.EvnSection_setTime::varchar, ''),
					to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY') ||  ' ' || COALESCE(EPS.EvnPS_setTime::varchar, '')
				) as EvnPS_setDateTime,
				COALESCE(ES.EvnSection_setDate, EPS.EvnPS_setDate) as EvnPS_setDateSort,
				COALESCE(ES.EvnSection_setTime, EPS.EvnPS_setTime) as EvnPS_setTimeSort,

				RTRIM(COALESCE(PC.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PC.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PC.Person_Secname, '')) as Person_Fio,
				to_char(PC.Person_Birthday, 'DD.MM.YYYY') as Person_Birthday,
				dbo.Age2(PC.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				--ЛПУ, куда госпитализирован - Lpu_id
				EPS.Lpu_id as Lpu_id,
				RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as Lpu_Name,
				RTRIM(COALESCE(LpuDir.Lpu_Nick, '')) as LpuDir_Name,
				-- Отделение (показывать последнее активное отделение)
				RTRIM(COALESCE(lss.LpuSection_Name,ls.LpuSection_Name)) as LpuSections_Name,
				--Тип госпитализации
				RTRIM(COALESCE(PHT.PrehospType_Name, '')) as PrehospType_Name,
				-- Количество дней с момента госпитализации
				DATEDIFF('DAY', ES.EvnSection_setDT, dbo.tzGetDate()) as DaysDiff,

				COALESCE(ES.EvnSection_Count, 0) as EvnSection_Count,

				-- Кем направлен (Если направлен ЛПУ, то выводить LPU_Nick; если другое отделение, то название отделения)
				case
					when EPS.Lpu_did = :Lpu_wid then RTRIM(MPED.Person_Fin)
					when EPS.Lpu_did is not null then RTRIM(COALESCE((SELECT Lpu_Nick FROM v_Lpu  WHERE Lpu_id = EPS.Lpu_did), ''))
					when EPS.Lpu_id != :Lpu_wid and EPS.PrehospDirect_id = 1 then RTRIM(COALESCE((SELECT Lpu_Nick FROM v_Lpu  WHERE Lpu_id = EPS.Lpu_id), ''))
					when EPS.LpuSection_did is not null then RTRIM(COALESCE((SELECT LpuSection_Name FROM LpuSection  WHERE LpuSection_id = EPS.LpuSection_did), ''))
					else RTRIM(COALESCE(PHD.PrehospDirect_Name, ''))
				end as PrehospDirect_Name,
				--Кем доставлен
				RTRIM(COALESCE(PHA.PrehospArrive_Name, '')) as PrehospArrive_Name,
				--Направление
				EPS.EvnDirection_id as EvnDirection_id,
				ED.EvnDirection_Num as EvnDirection_Num,
				null as ActualCost,
				null as PlannedCost,
				--окончательный Основной диагноз МКБ+наименование
				d.Diag_FullName as Diag_Name,
				d.Diag_id as Diag_id,
				--Дата(время) выписки (перевода, смерти)
				COALESCE(to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY'), '') || ' ' || COALESCE(EPS.EvnPS_disTime::varchar, '') as EvnPS_disDateTime,
				--Исход госпитализации
				RTRIM(COALESCE(LT.LeaveType_Name, '')) as LeaveType_Name,
				--Результат госпитализации
				--
				-- RTRIM(COALESCE(RD.ResultDesease_Name, '')) as ResultDesease_Name,
				-- ЛПУ прикрепления на дату поступления
				RTRIM(COALESCE(LpuAtt.Lpu_Nick, '')) as LpuAtt_Name,
				-- Участок на дату поступления
				RTRIM(COALESCE(LR.LpuRegion_Name, '')) as LpuRegion_Name,
				case when COALESCE(EvnPS_IsNeglectedCase, 1) = 2 then 'true' else 'false' end as EvnPS_IsNeglectedCase,
				case when COALESCE(EPS.EvnPS_IsPrehospAcceptRefuse, 1) = 2 then 'true' else 'false' end as EvnPS_IsPrehospAcceptRefuse_Name,
				EPS.EvnPS_IsPrehospAcceptRefuse as EvnPS_IsPrehospAcceptRefuse,
				COALESCE(ES.EvnSection_setDT, EPS.EvnPS_setDT) as orderByDT
				-- end select
			FROM
				v_EvnSection ES 
				inner join v_PersonCard PC  on ES.Person_id = PC.Person_id
				--inner join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid and EPS.Lpu_id = ES.Lpu_id
				INNER JOIN LATERAL (
					Select EPS.*
					from v_EvnPS EPS  
					where EPS.EvnPS_id = ES.EvnSection_pid and EPS.Lpu_id = ES.Lpu_id
					limit 1
				) EPS ON true
				{$join_medstaffregion} --для фильтра по участкам

				left join v_LpuSection LS  on LS.LpuSection_id = EPS.LpuSection_pid --для получения имени приемного отделения
				left join v_PrehospDirect PHD  on PHD.PrehospDirect_id = EPS.PrehospDirect_id --Кем направлен
				left join v_PrehospType PHT  on PHT.PrehospType_id = EPS.PrehospType_id--Тип госпитализации
				left join v_PrehospArrive PHA  on PHA.PrehospArrive_id = EPS.PrehospArrive_id --Кем доставлен
				left join v_Lpu Lpu  on Lpu.Lpu_id = EPS.Lpu_id --ЛПУ куда госпитализирован
				left join v_Lpu LpuAtt  on LpuAtt.Lpu_id = PC.Lpu_id --лпу прикрепления на дату поступления
				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id --участок прикрепления на дату поступления
				left join v_LeaveType LT  on LT.LeaveType_id = EPS.LeaveType_id --Исход госпитализации
				left join v_EvnDirection_all ED  on ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_Lpu LpuDir  on LpuDir.Lpu_id = ED.Lpu_did --ЛПУ направления
				left join v_MedPersonal MPED  on MPED.MedPersonal_id = ED.MedPersonal_id and MPED.Lpu_id = ED.Lpu_sid
				left join v_Diag dp  on EPS.Diag_pid = dp.Diag_id
				LEFT JOIN LATERAL (
					select MSF.MedPersonal_id
					from dbo.v_MedStaffFact MSF 
						inner join persis.Post P  on P.id = MSF.Post_id
					where MSF.LpuSection_id = ED.LpuSection_id
						and (COALESCE(MSF.WorkData_begDate, dbo.tzGetDate()) <= dbo.tzGetDate())
						and (COALESCE(MSF.WorkData_endDate, dbo.tzGetDate()+interval '1 day') > dbo.tzGetDate())
						and P.code = 6
					limit 1
				) ZMPD ON true
				--Последнее активное отделение
				left join v_LpuSection lss  on lss.LpuSection_id = ES.LpuSection_id
				left join v_Diag d  on COALESCE(ES.Diag_id,EPS.Diag_pid) = d.Diag_id
				--данные для расчета предполагаемой стоимости лечения в отделении в котором пациент сейчас находится
				--LpuSectionTariffMes_Tariff последнего активного отделения

			where
				PC.Lpu_id = :Lpu_aid -- фильтр по МО не может быть нуловым
				{$signal_filters}
				and COALESCE(ES.LpuSection_id, EPS.LpuSection_pid) is not null
				and PC.PersonCard_begDate <= ES.EvnSection_setDate
				and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ES.EvnSection_setDate)
				and ES.EvnSection_Index = (ES.EvnSection_Count - 1)
				{$filters}
				{$filters_esdate}
		";

		$query = "
			-- addit with
			with list as (
				{$addit_query}
			)
			-- end addit with
			select
				-- select
				t1.LpuAttachType_id as \"LpuAttachType_id\",
				t1.EvnSection_id as \"EvnSection_id\",
				t1.EvnPS_id as \"EvnPS_id\",
				t1.Person_id as \"Person_id\",
				t1.PersonEvn_id as \"PersonEvn_id\",
				t1.Server_id as \"Server_id\",
				t1.Lpu_did as \"Lpu_did\",
				t1.MedPersonal_did as \"MedPersonal_did\",
				t1.MedPersonal_zdid as \"MedPersonal_zdid\",
				t1.PrehospType_id as \"PrehospType_id\",
				t1.EvnPS_setDateTime as \"EvnPS_setDateTime\",
				t1.EvnPS_setDateSort as \"EvnPS_setDateSort\",
				t1.EvnPS_setTimeSort as \"EvnPS_setTimeSort\",
				t1.Person_Fio as \"Person_Fio\",
				t1.Person_Birthday as \"Person_Birthday\",
				t1.Person_Age as \"Person_Age\",
				t1.Lpu_id as \"Lpu_id\",
				t1.Lpu_Name as \"Lpu_Name\",
				t1.LpuDir_Name as \"LpuDir_Name\",
				t1.LpuSections_Name as \"LpuSections_Name\",
				t1.PrehospType_Name as \"PrehospType_Name\",
				t1.DaysDiff as \"DaysDiff\",
				t1.EvnSection_Count as \"EvnSection_Count\",
				t1.PrehospDirect_Name as \"PrehospDirect_Name\",
				t1.PrehospArrive_Name as \"PrehospArrive_Name\",
				t1.EvnDirection_id as \"EvnDirection_id\",
				t1.EvnDirection_Num as \"EvnDirection_Num\",
				t1.ActualCost as \"ActualCost\",
				t1.PlannedCost as \"PlannedCost\",
				t1.Diag_Name as \"Diag_Name\",
				t1.Diag_id as \"Diag_id\",
				t1.EvnPS_disDateTime as \"EvnPS_disDateTime\",
				t1.LeaveType_Name as \"LeaveType_Name\",
				t1.LpuAtt_Name as \"LpuAtt_Name\",
				t1.LpuRegion_Name as \"LpuRegion_Name\",
				t1.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\",
				t1.EvnPS_IsPrehospAcceptRefuse_Name as \"EvnPS_IsPrehospAcceptRefuse_Name\",
				t1.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\",
				t1.orderByDT as \"orderByDT\"
				-- end select
			from
				-- from
				list t1
				-- end from
			where
				-- where
				t1.LpuAttachType_id = (
					select t2.LpuAttachType_id
					from list t2 where t1.EvnSection_id = t2.EvnSection_id
					order by LpuAttachType_id
					limit 1
				)
				-- end where
			order by
				-- order by
				t1.orderByDT desc
				-- end order by
		";

		$params = array(
			'PrehospType_id' => $data['PrehospType_id'],
			'PrehospArrive_id' => $data['PrehospArrive_id'],
			'Org_oid' => $data['Org_oid'],
			'LeaveType_id' => $data['LeaveType_id'],
			'ResultDesease_id' => $data['ResultDesease_id'],
			'Lpu_aid' => !empty($data['Lpu_aid'])?$data['Lpu_aid']:null,
			'Lpu_wid' => $data['session']['lpu_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'Person_Surname'=>$data['Person_Surname']."%",
			'Person_Firname'=>$data['Person_Firname']."%",
			'Person_Secname'=>$data['Person_Secname']."%",
			'Person_Birthday'=>$data['Person_Birthday'],
			'Person_Birthday_Range_0'=>$data['Person_Birthday_Range_0'],
			'Person_Birthday_Range_1'=>$data['Person_Birthday_Range_1'],
			'LeaveCause_id' => $data['LeaveCause_id'],
			'MedPersonal_id' => (isset($data['MedPersonal_id']))?$data['MedPersonal_id']:$data['session']['medpersonal_id'],
			'EvnPS_IsNeglectedCase' => isset($data['EvnPS_IsNeglectedCase']) ? $data['EvnPS_IsNeglectedCase'] : null
		);
		//print ((isset($data['MedPersonal_id']))?$data['MedPersonal_id']:$data['session']['medpersonal_id']);
		//echo getDebugSQL($query, $params); exit();
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		exit;
		*/

		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);

		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as &$row)
			{
				$row['IsEvnDirection'] = false;
				if (isset($row['EvnDirection_id']))
				{
					$row['IsEvnDirection'] = true;
					$row['EvnDirection_Num'] = '<span class="fake-link">'.$row['EvnDirection_Num'].'</span>';
				}
			}
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 * Функция проверки совпадения (Для КВС, в которой уже добавлен ЛВН с исходом ЛВН "Смерть" требуется, чтобы дата исхода госпитализации равнялась дате смерти и исход госпитализации был равен Смерть.)
	 */
	function CheckEvnPSDie($data) {
		if (!empty($data['EvnPS_id'])) {
			// если нет ЛВН то проверку не производить
			$query = "SELECT COUNT(EvnStick_id) as \"CNT\" FROM v_EvnStick  WHERE EvnStick_pid = :EvnStick_pid";

			$result = $this->db->query($query, array(
				'EvnStick_pid' => $data['EvnPS_id']
			));

			$response = $result->result('array');
			if ($response[0]['CNT'] == 0){
				return true;
			}

			// выбираем дату из ЛВН с причиной закрытия смерть
			$query = "SELECT to_char(EvnStick_disDate, 'DD.MM.YYYY') as \"EvnStick_disDate\" FROM v_EvnStick  WHERE EvnStick_pid = :EvnStick_pid AND StickLeaveType_id = 4 ORDER BY EvnStick_disDate DESC";

			$result = $this->db->query($query, array(
				'EvnStick_pid' => $data['EvnPS_id']
			));

			if ( is_object($result) ) {
				$response = $result->result('array');
				if ( is_array($response) && count($response) > 0 ) {

					$date1 = date('d.m.Y',strtotime($data['EvnPS_disDate']));
					$date2 = $response[0]['EvnStick_disDate'];

					if (($data['LeaveType_id']==3)&&($date1==$date2)) {
						return true;
					} else {
						return false;
					}
					// если не найдено то при исходе смерть выводим предупреждение
				} else {
					if ($data['LeaveType_id']==3) { return false; }
				}
			}
		}
		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 * Функция проверки наличия у человека направления или самостоятельного обращения на текущие статистические сутки
	 */
	function checkSelfTreatment($data) {
		$query = "
			select
				'EvnDirection' as \"name\",
				null as \"EvnPS_id\",
				COALESCE(ED.EvnDirection_Num::text,'') as \"num\",
				COALESCE(to_char(TTS.TimetableStac_setDate, 'DD.MM.YYYY'),'') as \"recdate\",
				COALESCE(LS.LpuSection_Name,'') as \"LpuSection_Name\",
				COALESCE(Diag.Diag_Name,'') as \"Diag_Name\",
				null as \"EvnQueue_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				to_char(ED.EvnDirection_setDate, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Diag_id as \"Diag_did\",
				ED.DirType_id as \"DirType_id\",
				L.Lpu_id as \"Lpu_did\",
				L.Org_id as \"Org_did\",
				null as \"EvnPS_CodeConv\",
				TTS.TimetableStac_id as \"TimetableStac_id\"
			from 
				v_EvnDirection_all ED 
				left join v_EvnPS EvnPS  on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_TimetableStac_lite TTS  on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSection LS  on COALESCE(ED.LpuSection_did,ED.LpuSection_id) = LS.LpuSection_id
				left join v_Diag Diag  on ED.Diag_id = Diag.Diag_id
				left join v_Lpu L  on L.Lpu_id = COALESCE(ED.Lpu_sid,ED.Lpu_id)
			where
				ED.Lpu_did = :Lpu_id and
				ED.Person_id = :Person_id and
				ED.EvnQueue_id is null AND 
				ED.DirType_id in (1,5,6) AND 
				ED.DirFailType_id is null and
				ED.EvnDirection_failDT is null and
				EvnPS.EvnPS_id is null
			union all 
			select
				'TimetableStac' as \"name\",
				null as \"EvnPS_id\",
				COALESCE(EmD.EmergencyData_CallNum,'') as \"num\",
				COALESCE(to_char(TTS.TimetableStac_setDate, 'DD.MM.YYYY'),'')  as \"recdate\",
				COALESCE(LS.LpuSection_Name,'') as \"LpuSection_Name\",
				COALESCE(Diag.Diag_Name,'') as \"Diag_Name\",
				null as \"EvnQueue_id\",
				null as \"EvnDirection_id\",
				null as \"EvnDirection_setDate\",
				null as \"LpuSection_did\",
				EmD.Diag_id as \"Diag_did\",
				null as \"DirType_id\",
				null as \"Lpu_did\",
				null as \"Org_did\",
				EmD.EmergencyData_CallNum as \"EvnPS_CodeConv\",
				TTS.TimetableStac_id as \"TimetableStac_id\"
			from 
				v_TimetableStac_lite TTS 
				left join v_EvnPS EvnPS  on TTS.Evn_id = EvnPS.EvnPS_id
				left join EmergencyData EmD  on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join v_LpuSection LS  on TTS.LpuSection_id = LS.LpuSection_id
				left join v_Diag Diag  on EmD.Diag_id = Diag.Diag_id
				left join v_EvnDirection_all ED  on TTS.EvnDirection_id = ED.EvnDirection_id
			where
				LS.Lpu_id = :Lpu_id and
				TTS.Person_id = :Person_id and
				ED.TimetableStac_id is null and
				EvnPS.EvnPS_id is null
			union all 
			select
				'EvnQueue' as \"name\",
				null as \"EvnPS_id\",
				COALESCE(ED.EvnDirection_Num::text,'')  as \"num\",
				'' as \"recdate\",
				COALESCE(LS.LpuSection_Name,'') as \"LpuSection_Name\",
				COALESCE(Diag.Diag_Name,'') as \"Diag_Name\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				to_char(ED.EvnDirection_setDate, 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Diag_id as \"Diag_did\",
				ED.DirType_id as \"DirType_id\",
				L.Lpu_id as \"Lpu_did\",
				L.Org_id as \"Org_did\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_id\"
			from 
				v_EvnQueue EQ 
				left join v_LpuUnit LU  on EQ.LpuUnit_did = LU.LpuUnit_id
				left join v_EvnDirection_all ED  on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_LpuSection LS  on COALESCE(ED.LpuSection_did,EQ.LpuSection_did) = LS.LpuSection_id
				left join v_Diag Diag  on ED.Diag_id = Diag.Diag_id
				left join v_Lpu L  on L.Lpu_id = COALESCE(ED.Lpu_sid,ED.Lpu_id)
			where
				EQ.Person_id = :Person_id 
				AND EQ.EvnQueue_failDT is null
				AND LU.Lpu_id = :Lpu_id
				AND LU.LpuUnitType_id in (1,9,6)
				AND (EQ.EvnDirection_id is null OR ED.EvnQueue_id is not null)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Функция выбора данных для печати справки об отказе от госпитализации
	 */
	function printEvnPSEditForm($data) {
		$query = "
			SELECT
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				Addr.Address_Address as \"Person_PAddress\",-- дом.адрес или место регистрации
				COALESCE(orgjob.Org_Name,'______________________________') as \"Person_Job\",
				COALESCE(post.Post_Name,'______________________________') as \"Person_JobPost\",
				to_char(EPS.EvnPS_setDT, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_setTime, 'HH24:MI') as \"EvnPS_setTime\",
				to_char(EPS.EvnPS_disDT, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				to_char(EPS.EvnPS_disTime, 'HH24:MI') as \"EvnPS_disTime\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				Lpu.Lpu_Name as \"Lpu_Name\",
				Lpu.Lpu_Phone as \"Lpu_Phone\",
				Lpu.UAddress_Address as \"UAddress_Address\",
				(RTrim(Diag.Diag_Code) || ' ' || RTrim(Diag.Diag_Name)) as \"Diag_Name\",
				PWRC.PrehospWaifRefuseCause_Name as \"PrehospWaifRefuseCause_Name\",
				MP.Person_Fin as \"MedPersonal_Fio\",
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.Person_id as \"Person_id\",
				EPS.PersonEvn_id as \"PersonEvn_id\",
				EPS.Server_id as \"Server_id\"
			FROM
				v_EvnPS EPS 
				left join v_PersonState PS  on PS.Person_id = EPS.Person_id
				left join v_Address Addr  on Addr.Address_id = COALESCE(PS.PAddress_id,PS.UAddress_id)
				left join v_Job job  on job.Job_id = PS.Job_id
				left join v_Org orgjob  on job.Org_id = orgjob.Org_id
				left join v_Post post  on post.post_id = job.post_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = EPS.Lpu_id
				left join v_Diag Diag  on Diag.Diag_id = EPS.Diag_pid
				left join v_PrehospWaifRefuseCause PWRC  on PWRC.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = EPS.MedPersonal_pid and MP.Lpu_id = EPS.Lpu_id
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Функция выбора данных для печати справки об отказе пациента от госпитализации
	 */
	function printPatientRefuse($data) {
		$query = "
			SELECT
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				Lpu.Lpu_Nick as \"Lpu_Nick\"
			FROM
				v_EvnPS EPS 
				left join v_PersonState PS  on PS.Person_id = EPS.Person_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = EPS.Lpu_id
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array
	 * Функция сохранения признака "Передан активный вызов"
	 */
	function setActiveCall($data)
	{
		$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
		$this->setParams($data);
		return array($this->updateIsTransfCall($data['EvnPS_id'], 2));
	}

	/**
	 * @param $data
	 * @return array
	 * Функция сохранения признака "Талон передан на ССМП"
	 */
	function setTransmitAmbulance($data)
	{
		$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
		$this->setParams($data);
		return array($this->updateIsPLAmbulance($data['EvnPS_id'], 2));
	}

	/**
	 * @param $data
	 * @return bool
	 * Проверка наличия поступления пациента в приемное отделения за последние 24 часа
	 */
	function checkReceptionTime($data) {
		$query = "
			select
				EvnPS.EvnPS_id as \"EvnPS_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\"
			from 
				v_EvnPS EvnPS 
				left join v_Lpu Lpu  on EvnPS.Lpu_id = Lpu.Lpu_id
			where
				EvnPS.Person_id = :Person_id
				and EvnPS.EvnPS_setDT between (dbo.tzgetdate() - interval '1 day') and dbo.tzGetDate()
			limit 1
		";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @param $query
	 * @return bool
	 */
	function exportToDbfBedFond($data, $query)
	{
		$data['time1'] = '09:00';
		$data['time2'] = '00:01';
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			log_message('error', 'query fails:', getDebugSql($query, $data));
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setEvnPSPrehospAcceptRefuse($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPS_setIsPrehospAcceptRefuse(
				EvnPS_id := :EvnPS_id,
				EvnPS_IsPrehospAcceptRefuse := :EvnPS_IsPrehospAcceptRefuse,
				EvnPS_PrehospAcceptRefuseDT := :EvnPS_PrehospAcceptRefuseDT,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnPS_id' => $data['EvnPS_id'],
			'EvnPS_IsPrehospAcceptRefuse' => $data['EvnPS_IsPrehospAcceptRefuse'],
			'EvnPS_PrehospAcceptRefuseDT' => ($data['EvnPS_IsPrehospAcceptRefuse'] == 2 ? date('Y-m-d H:i:s') : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция получения данных для проверок перед открытием ЭМК
	 * 1) Есть ли у пациента открытые КВС в данном ЛПУ
	 */
	function beforeOpenEmk($data) {
		// достаточно 1ой открытой КВС
		$query = '
			select 1 as "countOpenEvnPS"
			from v_EvnPS EvnPS 
			where EvnPS.Person_id = :Person_id
				and EvnPS.Lpu_id = :Lpu_id
				and EvnPS.EvnPS_disDT is null
				and EvnPS.PrehospWaifRefuseCause_id is null
			limit 1
		';
		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 || !array_key_exists('countOpenEvnPS', $response[0]) ) {
			$response = array(array('countOpenEvnPS' => 0));
		}

		return $response;
	}

	/**
	 * checkEvnPSBirth
	 */
	function checkEvnPSChild($data){
		$queryParams = array();
		$queryParams['Person_id'] = $data['Person_id'];
		$query="select PC.PersonChild_id as \"PersonChild_id\"
                from v_PersonChild PC 
				where PC.Person_id = :Person_id
				limit 1";
		//echo getDebugSQL($query,$queryParams);die;
		$result = $this->db->query($query,$queryParams);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		else
			return false;
	}

	/**
	 * checkEvnPSSectionAndDateEqual
	 */
	function checkEvnPSSectionAndDateEqual($data){
		$query="
			select
				case when LpuSection_id <> :LpuSection_eid then 1 else 0 end as \"LpuSection_NotEqual\",
				case when cast(EvnSection_setDate as date) <> :EvnPS_OutcomeDate then 1 else 0 end as \"OutcomeDate_NotEqual\",
				case when EvnSection_setTime <> :EvnPS_OutcomeTime then 1 else 0 end as \"OutcomeTime_NotEqual\"
			from v_EvnSection 
			where
				EvnSection_pid = :EvnPS_id
				and COALESCE(EvnSection_IsPriem, 1) = 1
			order by EvnSection_Index
			limit 1
		";
		//echo getDebugSQL($query,$data);die;
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			$response[0]['ignoreOutcomeAndAction'] = (
				$response[0]['LpuSection_NotEqual'] == 1
				|| $response[0]['OutcomeDate_NotEqual'] == 1
				|| $response[0]['OutcomeTime_NotEqual'] == 1
			);
		}
		else {
			$response = array(array('ignoreOutcomeAndAction' => false));
		}

		return $response;
	}

	/**
	 * checkEvnSectionSectionAndDateEqual
	 */
	function checkEvnSectionSectionAndDateEqual($data){

		$query="
			select
				COUNT(*) as \"count\"
			from v_EvnSection ES 
				inner join v_EvnPS EPS  on EvnPS_id = EvnSection_pid
			where
				ES.EvnSection_id = :EvnSection_id
				and ES.EvnSection_Index = 0
				and EPS.LpuSection_eid is not null
				and EPS.EvnPS_OutcomeDT  is not null
				and ( EPS.LpuSection_eid <> :LpuSection_id
					or cast(EPS.EvnPS_OutcomeDT as date) <> :EvnSection_setDate
					or substring(to_char(EvnPS_OutcomeDT, 'HH24:MI') from 1 for 6) <> :EvnSection_setTime
				)
		";
		//echo getDebugSQL($query,$data);die;
		$result = $this->db->query($query,$data);
		if(is_object($result)){
			$response = $result->result('array');

			if (is_array($response) && !empty($response[0]['count']) && $response[0]['count'] > 0) {
				$response[0]['ignoreOutcomeAndAction'] = true;
			}

			return $response;
		}
		else
			return false;
	}

	/**
	 * checkEvnPSBirth
	 */
	function checkEvnPSBirth($data){
		$queryParams = array();
		$queryParams['EvnPS_id'] = $data['EvnPS_id'];
		$query="select
				 BSS.BirthSpecStac_id as \"BirthSpecStac_id\"
            from v_EvnPS EPS 
            left join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id
            inner join v_BirthSpecStac BSS  on BSS.EvnSection_id = ES.EvnSection_id
            where (1=1)
                and EPS.EvnPS_id =:EvnPS_id
			limit 1";
		//echo getDebugSQL($query,$queryParams);die;
		$result = $this->db->query($query,$queryParams);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		else
			return false;
	}

	/**
	 * Получение Morbus_id по психиатрии/наркологии https://redmine.swan.perm.ru/issues/36513
	 */
	function getMorbusCrazy($data){
		$queryParams = array();
		$queryParams['EvnPS_id'] = $data['EvnPS_id'];
		$query = "
            select
                M.Morbus_id as \"Morbus_id\",
                ES.EvnSection_id as \"EvnSection_id\"
            from v_EvnPS EPS 
            left join v_EvnSection ES  on ES.EvnSection_pid = EPS.EvnPS_id
            left join v_Evn Evn  on Evn.Evn_id = ES.EvnSection_id
            left join v_Morbus M1  on M1.Morbus_id = Evn.Morbus_id
			left join v_Morbus M2  on M2.Evn_pid = Evn.Evn_id
			left join v_Morbus M  on M.Morbus_id = COALESCE(M1.Morbus_id, M2.Morbus_id)
            left join MorbusType MT  on MT.MorbusType_id = M.MorbusType_id
            where (1=1)
                and M.Morbus_id is not null
                and MT.MorbusType_SysNick in ('crazy','narc')
                and EPS.EvnPS_id = :EvnPS_id
			limit 1
        ";
		//echo getDebugSQL($query,$queryParams);die;
		$result = $this->db->query($query,$queryParams);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		else
			return false;
	}

	/**
	 * Сохранение движения без дополнительных данных ("на госпитализацию" из АРМ врача приемного и при заполненнии поля "Госпитализирован в" формы поступления)
	 * @param $data
	 * @return array
	 */
	function saveEvnSectionInHosp($data)
	{

		try {
			$this->applyData($data);
			if ( !empty($data['EvnPS_OutcomeDate']) && !empty($data['LpuSection_eid']) && !empty($data['Person_id'])) {
				//Проверяем пересечение с ТАП если выставлена соответствующая настройка
				$this->checkIntersectEvnSectionWithVizit(array(
					'LpuSection_id' => $data['LpuSection_eid'],
					'EvnSection_disDate' => $data['EvnPS_OutcomeDate'],
					'EvnSection_disTime' => $data['EvnPS_OutcomeTime'],
					'EvnSection_setDate' => $this->setDate,
					'EvnSection_setTime' => $this->setTime,
					'PayType_id' => $this->PayType_id,
					'Person_id' => $data['Person_id'],
					'vizit_direction_control_check' => $data['vizit_direction_control_check'],
					'session' => $data['session']
				));
			}
			if (getRegionNick() == 'msk') {
				// проверяем заполнение обязательных полей COVID19
				$resp_ceps = $this->queryResult("
					select
						ceps.CovidType_id as \"CovidType_id\",
						ceps.DiagConfirmType_id as \"DiagConfirmType_id\",
						ceps.RepositoryObserv_BreathRate as \"RepositoryObserv_BreathRate\",
						ceps.RepositoryObserv_Systolic as \"RepositoryObserv_Systolic\",
						ceps.RepositoryObserv_Diastolic as \"RepositoryObserv_Diastolic\",
						ceps.RepositoryObserv_Height as \"RepositoryObserv_Height\",
						ceps.RepositoryObserv_Weight as \"RepositoryObserv_Weight\",
						ceps.RepositoryObserv_TemperatureFrom as \"RepositoryObserv_TemperatureFrom\",
						ceps.RepositoryObserv_SpO2 as \"RepositoryObserv_SpO2\",
						d.Diag_Code as \"Diag_Code\"
					from
						v_RepositoryObserv ceps
						left join v_EvnPS eps on eps.EvnPS_id = :EvnPS_id
						left join v_Diag d on d.Diag_id = eps.Diag_pid
					where
						ceps.Evn_id = :EvnPS_id
					ORDER BY
						ceps.RepositoryObserv_updDT DESC
					limit 1
				", [
					'EvnPS_id' => $this->id
				]);

				$fields = [];
				if (!isset($resp_ceps[0]['RepositoryObserv_BreathRate'])) {
					$fields[] = 'Частота дыхания';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_Systolic'])) {
					$fields[] = 'Систолическое АД';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_Diastolic'])) {
					$fields[] = 'Диастолическое АД';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_Height'])) {
					$fields[] = 'Рост, см';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_Weight'])) {
					$fields[] = 'Вес, кг';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_TemperatureFrom'])) {
					$fields[] = 'Температура тела';
				}
				if (!isset($resp_ceps[0]['RepositoryObserv_SpO2'])
					&& !empty($resp_ceps[0]['Diag_Code'])
					&& (
						in_array($resp_ceps[0]['Diag_Code'], ['B34.2', 'B33.8', 'Z03.8', 'Z22.8', 'Z20.8', 'U07.1', 'U07.2'])
						|| (mb_substr($resp_ceps[0]['Diag_Code'], 0, 3) >= 'J00' && mb_substr($resp_ceps[0]['Diag_Code'], 0, 3) <= 'J99')
					)
				) {
					$fields[] = 'Сатурация кислорода (%)';
				}
				if (!isset($resp_ceps[0]['CovidType_id'])) {
					$fields[] = 'Коронавирус';
				}
				if (!isset($resp_ceps[0]['DiagConfirmType_id'])) {
					$fields[] = 'Диагноз подтвержден рентгенологически';
				}

				if (!empty($fields)) {
					throw new Exception('Не заполнены обязательные для госпитализации поля на форме "Поступление пациента в приемное отделение": ' . implode(', ', $fields) . '. Заполните поля.', 101);
				}
			}
			$this->beginTransaction();

			$this->_updatePrehospStatus();
			$result = $this->_save();
			$result['session'] = $data['session'];
			$this->_changeEvnSectionFirst($result);
			if ($this->EvnDirection_id) {
				$this->load->model('EvnDirectionAll_model');
				// переводим в статус “Обслужено”
				$this->EvnDirectionAll_model->setStatus(array(
					'Evn_id' => $this->EvnDirection_id,
					'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
					'pmUser_id' => $this->promedUserId,
				));
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return $this->_saveResponse;
		}
		// уведомления направившему МО
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * Отмена госпитализации из АРМа приемного отделения
	 * @param array $data
	 * @return array
	 */
	function deleteEvnSectionInHosp($data)
	{
		try {
			$inputData = array('session' => $data['session']);
			if (empty($data['EvnPS_id'])) {
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->applyData($data);
				$inputData['EvnPS_id'] = $this->EvnSection_model->pid;
			} else {
				$inputData['EvnPS_id'] = $data['EvnPS_id'];
			}
			$inputData['EvnPS_OutcomeDate'] = null;
			$inputData['EvnPS_OutcomeTime'] = null;
			$inputData['LpuSection_eid'] = null;
			$inputData['LpuSectionWard_id'] = null;
			$inputData['scenario'] = self::SCENARIO_DO_SAVE;
			$this->applyData($inputData);
			$this->beginTransaction();
			$this->_updatePrehospStatus();
			$result = $this->_save();
			$this->_changeEvnSectionFirst($result);
			if ($this->EvnDirection_id>0) {
				$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->setParams(array(
					'session' => $this->sessionParams,
				));
				$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
				if (15 == $this->EvnDirectionAll_model->EvnStatus_id) {
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $this->EvnDirection_id,
						'EvnStatus_SysNick' => $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $this->promedUserId,
					));
				}
			}

			$dt = array(
				'session' => $this->sessionParams,
				'EvnSection_id' => $this->evnSectionPriemId,
				'EvnSection_pid' => $this->id,
				'EvnSection_setDate' => $this->setDate,
				'EvnSection_setTime' => $this->setTime,
				'Lpu_id' => $this->Lpu_id,
				'MedStaffFact_id'=>$this->MedStaffFact_pid,
				'Server_id' => $this->Server_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'LpuSection_id' => $this->LpuSection_pid,
				'Diag_id' => $this->Diag_pid,
				'DeseaseType_id' => $this->DeseaseType_id,
				'TumorStage_id' => $this->TumorStage_id,
				'EvnSection_IsZNO' => $this->IsZNO,
				'Diag_spid' => $this->diag_spid,
				'PayType_id' => $this->PayType_id,
				'MedPersonal_id' => $this->MedPersonal_pid,
				'LeaveType_prmid' => null,
				'UslugaComplex_id' => $this->_params['UslugaComplex_id'],
				'LeaveType_fedid' => $this->_params['LeaveType_fedid'],
				'ResultDeseaseType_fedid' => $this->_params['ResultDeseaseType_fedid'],
				'LpuSectionProfile_id' => $this->_params['LpuSectionProfile_id'],
				'EvnSection_IndexRep' => $this->_params['EvnPS_IndexRep'],
				'EvnSection_IndexRepInReg' => $this->_params['EvnPS_IndexRepInReg'],
			);
			if (isset($this->outcomeDate)) {
				$dt['EvnSection_disDate'] = $this->outcomeDate;
				$dt['EvnSection_disTime'] = $this->outcomeTime;
			} else  {
				$dt['EvnSection_disDate'] = null;
				$dt['EvnSection_disTime'] = null;
			}
			$this->EvnSection_model->saveEvnSectionInPriem($dt, true);

			$this->commitTransaction();
			return array($this->_saveResponse);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return array($this->_saveResponse);
		}
	}

	/**
	 * Отмена госпитализации из АРМа приемного отделения: МАРМ
	 * @param array $data
	 * @return array
	 */
	function mDeleteEvnSectionInHosp($data)
	{
		try {

			$inputData = array('session' => $data['session']);

			if (!empty($data['EvnPS_id'])) {

				$inputData['EvnPS_id'] = $data['EvnPS_id'];

			} else {

				if (!empty($data['EvnSection_id'])) {
					$EvnSection_data = $this->getFirstRowFromQuery("
						SELECT 
							EvnSection_id as \"EvnSection_id\",
							EvnSection_pid as \"EvnSection_pid\"
						FROM v_EvnSection 
						where EvnSection_id = :EvnSection_id",
						array('EvnSection_id'=>$data['EvnSection_id'])
					);

					if (empty($EvnSection_data['EvnSection_id'])) {
						throw new Exception('В БД не найден указанный EvnSection_id', 777);
					}

					if (empty($EvnSection_data['EvnSection_pid'])) {
						throw new Exception('В БД не найден КВС по указанному движению', 777);
					}

					$inputData['EvnPS_id'] = $EvnSection_data['EvnSection_pid'];
				} else {
					throw new Exception('Необходимо указать идентификатор КВС или движение', 777);
				}
			}

			$inputData['EvnPS_OutcomeDate'] = null;
			$inputData['EvnPS_OutcomeTime'] = null;
			$inputData['LpuSection_eid'] = null;
			$inputData['LpuSectionWard_id'] = null;
			$inputData['scenario'] = self::SCENARIO_DO_SAVE;

			$this->applyData($inputData);
			$this->beginTransaction();

			$this->_updatePrehospStatus();
			$result = $this->_save();
			$this->_changeEvnSectionFirst($result);

			if ($this->EvnDirection_id>0) {

				$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->setParams(array(
					'session' => $this->sessionParams,
				));

				$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $this->EvnDirection_id));
				if ($this->EvnDirectionAll_model->EvnStatus_id == 15) {
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $this->EvnDirection_id,
						'EvnStatus_SysNick' => $this->EvnDirectionAll_model->TimetableStac_id ? EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED : EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $this->promedUserId,
					));
				}
			}

			$this->commitTransaction();
			return array($this->_saveResponse);

		} catch (Exception $e) {

			$this->rollbackTransaction();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return array($this->_saveResponse);
		}
	}

	/**
	 * getLastEvnPS
	 */
	function getLastEvnPS($data) {
		$filters = "";
		$params = array();

		if (!empty($data['LpuSection_id'])) {
			$filters .= " and EPS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['Person_id'])) {
			$filters .= " and EPS.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$query = "
			select EPS.EvnPS_id as \"EvnPS_id\"
			from v_EvnPS EPS 
			where 1=1 {$filters}
			order by EvnPS_setDT desc
			limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение направления из КВС
	 * @param $data
	 * @return int|null|false
	 */
	function getEvnDirectionFromEvnPS($data) {
		$params = array('EvnPS_id' => $data['EvnPS_id']);
		$query = "
			select EvnDirection_id as \"EvnDirection_id\"
			from v_EvnPS 
			where EvnPS_id = :EvnPS_id
			limit 1
		";
		return $this->getFirstResultFromQuery($query, $params, true);
	}

	/**
	 * Получение данных КВС. Метод для API
	 */
	function getEvnPSForAPI($data) {
		$params = array();
		$filter = "";

		if (!empty($data['EvnPS_id'])) {
			$params['EvnPS_id'] = $data['EvnPS_id'];
			$filter .= " and EPS.EvnPS_id = :EvnPS_id";
		}

		if (!empty($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$filter .= " and EPS.EvnPS_id = :Evn_id";
		}

		if (!empty($data['Person_id'])) {
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and EPS.Person_id = :Person_id";
		}

		if (!empty($data['EvnPS_NumCard'])) {
			$params['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
			$filter .= " and EPS.EvnPS_NumCard = :EvnPS_NumCard";
		}

		if (!empty($data['EvnPS_setDT'])) {
			$params['EvnPS_setDT'] = $data['EvnPS_setDT'];
			$filter .= " and EPS.EvnPS_setDT = :EvnPS_setDT";
		}

		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and EPS.Lpu_id = :Lpu_id";
		}

		if (empty($filter)) {
			return array();
		}

		$query = "
			select
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.EvnPS_id as \"Evn_id\",
				EPS.Person_id as \"Person_id\",
				EPS.Lpu_id as \"Lpu_id\",
				EPS.EvnPS_IsCont as \"EvnPS_IsCont\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				EPS.PayType_id as \"PayType_id\",
				to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD') as \"EvnPS_setDT\",
				EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\",
				EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\",
				EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\",
				EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\",
				EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\",
				EPS.PrehospType_id as \"PrehospType_id\",
				EPS.PrehospDirect_id as \"PrehospDirect_id\",
				EPS.LpuSection_did as \"LpuSection_did\",
				EPS.Lpu_did as \"Lpu_did\",
				EPS.Org_did as \"Org_did\",
				EPS.OrgMilitary_did as \"OrgMilitary_did\",
				EPS.PrehospArrive_id as \"PrehospArrive_id\",
				EPS.CmpCallCard_id as \"CmpCallCard_id\",
				EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\",
				EPS.EvnPS_NumConv as \"EvnPS_NumConv\",
				EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\",
				EPS.EvnPS_IsWaif as \"EvnPS_IsWaif\",
				EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\",
				EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\",
				EPS.PrehospToxic_id as \"PrehospToxic_id\",
				EPS.LpuSectionTransType_id as \"LpuSectionTransType_id\",
				EPS.EvnPS_HospCount as \"EvnPS_HospCount\",
				EPS.Okei_id as \"Okei_id\",
				EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				EPS.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\",
				EPS.PrehospTrauma_id as \"PrehospTrauma_id\",
				EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\",
				EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\",
				EPS.LpuSection_pid as \"LpuSection_pid\",
				EPS.MedPersonal_pid as \"MedPersonal_pid\",
				EPS.Diag_pid as \"Diag_pid\",
				EPS.DiagSetPhase_pid as \"DiagSetPhase_pid\",
				EPS.DiagSetPhase_aid as \"DiagSetPhase_aid\",
				EPS.Diag_id as \"Diag_id\",
				EPS.Diag_eid as \"Diag_eid\",
				EPS.Diag_aid as \"Diag_aid\",
				EPS.Diag_did as \"Diag_did\",
				EPS.Diag_pid as \"Diag_pid\",
				EPS.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\",
				EPS.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\",
				to_char(EPS.EvnPS_OutcomeDT, 'YYYY-MM-DD') as \"EvnPS_OutcomeDT\",
				EPS.LpuSection_eid as \"LpuSection_eid\",
				LastES.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LastES.UslugaComplex_id as \"UslugaComplex_id\",
				EPS.EvnDirection_id as \"EvnDirection_id\",
				EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				EPS.DeseaseType_id as \"DeseaseType_id\"
			from
				v_EvnPS EPS 
				LEFT JOIN LATERAL(
					select *
					from v_EvnSection ES 
					where ES.EvnSection_pid = EPS.EvnPS_id
					order by ES.EvnSection_setDT desc
					limit 1
				) LastES On true
			where
				(1=1)
				{$filter}
			limit 1
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Получение списка движений в КВС. Метод для API
	 */
	function getEvnSectionListForAPI($data) {
		$params = array('EvnPS_id' => $data['EvnPS_id']);
		$filter = '';
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter = " and ES.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				ES.EvnSection_id as \"EvnSection_id\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ES.Lpu_id as \"Lpu_id\"
			from
				v_EvnSection ES 
				inner join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid
			where
				ES.EvnSection_pid = :EvnPS_id
		".$filter;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных движения. Метод для API
	 */
	function getEvnSectionForAPI($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filter = "";

		if (!empty($data['EvnPS_id'])) {
			$params['EvnPS_id'] = $data['EvnPS_id'];
			$filter .= " and ES.EvnSection_pid = :EvnPS_id";
		}

		if (!empty($data['EvnSection_id'])) {
			$params['EvnSection_id'] = $data['EvnSection_id'];
			$filter .= " and ES.EvnSection_id = :EvnSection_id";
		}

		if (!empty($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$filter .= " and ES.EvnSection_id = :Evn_id";
		}

		if (!empty($data['EvnSection_setDate'])) {
			$params['EvnSection_setDate'] = $data['EvnSection_setDate'];
			$filter .= " and cast(ES.EvnSection_setDT as date) = :EvnSection_setDate";
		}

		if (!empty($data['EvnSection_IsPriem'])) {
			$params['EvnSection_IsPriem'] = $data['EvnSection_IsPriem'];
			$filter .= " and COALESCE(ES.EvnSection_IsPriem,1) = :EvnSection_IsPriem";
		}

		if (!empty($data['Date_DT'])) {
			$params['Date_DT'] = $data['Date_DT'];
			$filter .= " and (
				(cast(ES.EvnSection_setDT as date) <= :Date_DT and ES.EvnSection_disDT is null)
				or (cast(ES.EvnSection_setDT as date) <= :Date_DT and cast(ES.EvnSection_disDT as date) >= :Date_DT)
			)";
		}

		if (empty($filter)) {
			return array();
		}


		$query = "
			select
				ES.EvnSection_id as \"EvnSection_id\",
				ES.EvnSection_id as \"Evn_id\",
				ES.EvnSection_pid as \"EvnPS_id\",
				to_char(ES.EvnSection_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnSection_setDT\",
				to_char(ES.EvnSection_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnSection_disDT\",
				ES.PayType_id as \"PayType_id\",
				ES.TariffClass_id as \"TariffClass_id\",
				ES.LpuSection_id as \"LpuSection_id\",
				ES.LpuSectionWard_id as \"LpuSectionWard_id\",
				ES.MedStaffFact_id as \"MedStaffFact_id\",
				ES.Diag_id as \"Diag_id\",
				case when ES.EvnSection_IsPriem = 2 
					then EPS.Diag_pid else ES.Diag_id 
				end as \"Diag_id\",
				case when ES.EvnSection_IsPriem = 2 
					then EPS.DiagSetPhase_pid else ES.DiagSetPhase_id 
				end as \"DiagSetPhase_id\",
				EPS.DiagSetPhase_aid as \"DiagSetPhase_aid\",
				case when ES.EvnSection_IsPriem = 2 
					then EPS.EvnPS_PhaseDescr_pid else ES.EvnSection_PhaseDescr 
				end as \"EvnSection_PhaseDescr\",
				ES.Mes_id as \"Mes_id\",
				COALESCE(ES.Mes_sid, MT.Mes_id) as \"Mes_sid\",
				ES.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LT.LeaveType_id as \"LeaveType_id\",
				COALESCE(EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, ED.EvnDie_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL, EOST.EvnOtherStac_UKL) as \"EvnLeave_UKL\",
				COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id) as \"ResultDesease_id\",
				COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOS.LeaveCause_id, EOSBP.LeaveCause_id, EOST.LeaveCause_id) as \"LeaveCause_id\",
				EL.EvnLeave_IsAmbul as \"EvnLeave_IsAmbul\",
				EOL.Org_oid as \"Org_oid\",
				dMSF.MedStaffFact_id as \"MedStaffFact_did\",
				dMSF.MedPersonal_id as \"MedPersonal_did\",
				ED.EvnDie_IsAnatom as \"EvnDie_IsAnatom\",
				IsAnatom.YesNo_Code as \"EvnDie_IsAnatom\",
				EOST.LpuUnitType_oid as \"LpuUnitType_oid\",
				COALESCE(EOS.LpuSection_oid, EOSBP.LpuSection_oid, EOST.LpuSection_oid) as \"LpuSection_oid\",
				EOSBP.LpuSectionBedProfile_oid as \"LpuSectionBedProfile_oid\",
				IsPriem.YesNo_Code as \"EvnSection_IsPriem\",
				ES.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				ES.DeseaseType_id as \"DeseaseType_id\"
			from
				v_EvnSection ES 
				left join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_LeaveType LT  on LT.LeaveType_id = ES.LeaveType_id
				left join v_EvnLeave EL  on EL.EvnLeave_pid = ES.EvnSection_id
				left join v_EvnDie ED  on ED.EvnDie_pid = ES.EvnSection_id
				left join v_EvnOtherLpu EOL  on EOL.EvnOtherLpu_pid = ES.EvnSection_id
				left join v_EvnOtherSection EOS  on EOS.EvnOtherSection_pid = ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile EOSBP  on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
				left join v_EvnOtherStac EOST  on EOST.EvnOtherStac_pid = ES.EvnSection_id
				LEFT JOIN LATERAL(
					select *
					from v_MedStaffFact MSF 
					where MSF.MedPersonal_id = ED.MedPersonal_id
					limit 1
				) as dMSF On true
				left join v_MesTariff MT  on MT.MesTariff_id = ES.MesTariff_id
				left join v_YesNo IsAnatom  on IsAnatom.YesNo_id = ED.EvnDie_IsAnatom
				left join v_YesNo IsPriem  on IsPriem.YesNo_id = COALESCE(ES.EvnSection_IsPriem,1)
			where
				ES.Lpu_id = :Lpu_id
				{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка сопутствующих диагнозов или осложнений в движении. Метод для API
	 */
	function getEvnDiagPSListForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['EvnSection_id'])) {
			$info = $this->getFirstRowFromQuery("
				select
					EvnSection_pid as \"EvnSection_pid\",
					COALESCE(EvnSection_IsPriem, 1) as \"EvnSection_IsPriem\"
				from v_EvnSection 
				where EvnSection_id = :EvnSection_id
				limit 1
			", $data);

			if ($info['EvnSection_IsPriem'] == 2) {
				//Сопутствующие диагнозы приемного отделения сохраняются на КВС
				$filters[] = "EDPS.EvnDiagPS_pid = :EvnDiagPS_pid";
				$filters[] = "EDPS.DiagSetType_id = 2";		//Предварительный
				$params['EvnDiagPS_pid'] = $info['EvnSection_pid'];
			} else {
				$filters[] = "EDPS.EvnDiagPS_pid = :EvnDiagPS_pid";
				$params['EvnDiagPS_pid'] = $data['EvnSection_id'];
			}
		}
		if (!empty($data['EvnDiagPS_id'])) {
			$filters[] = "EDPS.EvnDiagPS_id = :EvnDiagPS_id";
			$params['EvnDiagPS_id'] = $data['EvnDiagPS_id'];
		}
		if (!empty($data['DiagSetClass_id'])) {
			$filters[] = "EDPS.DiagSetClass_id = :DiagSetClass_id";
			$params['DiagSetClass_id'] = $data['DiagSetClass_id'];
		}
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "EDPS.Lpu_id = :Lpu_id";
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				EDPS.EvnDiagPS_id as \"EvnDiagPS_id\",
				to_char(EDPS.EvnDiagPS_setDT, 'YYYY-MM-DD') as \"EvnDiagPS_setDate\",
				to_char(EDPS.EvnDiagPS_setDT, 'HH24:MI') as \"EvnDiagPS_setTime\",
				EDPS.DiagSetClass_id as \"DiagSetClass_id\",
				EDPS.DiagSetType_id as \"DiagSetType_id\",
				EDPS.Diag_id as \"Diag_id\",
				EDPS.DiagSetPhase_id as \"DiagSetPhase_id\",
				EDPS.EvnDiagPS_PhaseDescr as \"EvnDiagPS_PhaseDescr\"
			from
				v_EvnDiagPS EDPS 
			where
				{$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * получить назначения с типом Диета
	 */
	function getEvnPrescrDietForAPI($data) {
		$params = array();
		$where = ' AND EP.PrescriptionType_id = 2 ';
		if(empty($data['EvnPrescr_id']) && empty($data['Evn_pid'])){
			return array('Error_Msg' => "Отсутствует один из параметров: EvnPrescr_id или Evn_pid");
		}
		if (!empty($data['EvnPrescr_id'])) {
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$where .= " AND EvnPrescrDiet_pid = :EvnPrescr_id";
		}else{
			$params['EvnPrescr_pid'] = $data['Evn_pid'];
			$where .= " AND EvnPrescr_pid = :EvnPrescr_pid";
		}
		if (!empty($data['Lpu_id'])) {
			$where .= " and EP.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\" 
				,EP.EvnPrescr_pid as \"Evn_pid\"
				,EP.PrescriptionType_id as \"PrescriptionType_id\"
				,EPP.PrescriptionDietType_id as \"PrescriptionDietType_id\" 
				--,EPP.EvnPrescrDiet_setDT as \"Evn_setDT\"
				,to_char(EPP.EvnPrescrDiet_setDT, 'YYYY-MM-DD') as \"Evn_setDT\"
				--,EP.EvnPrescr_Count as \"Evn_Count\"
				,EP.EvnPrescr_Descr as \"EvnPrescr_Descr\"
				,(SELECT COUNT(*) FROM v_EvnPrescrDiet WHERE EvnPrescrDiet_pid=EP.EvnPrescr_id) as \"Evn_Count\"
			FROM
				v_EvnPrescr EP 
				inner join v_EvnPrescrDiet EPP  on EPP.EvnPrescrDiet_pid = EP.EvnPrescr_id
			WHERE (1=1)
			limit 1
		".$where;
		return $this->queryResult($query, $params);
	}

	/**
	 * получить назначения с типом режим
	 */
	function getEvnPrescrRegimeForAPI($data) {
		$params = array();
		$where = ' AND EP.PrescriptionType_id = 1 ';
		if(empty($data['EvnPrescr_id']) && empty($data['Evn_pid'])){
			return array('Error_Msg' => "Отсутствует один из параметров: EvnPrescr_id или Evn_pid");
		}
		if (!empty($data['EvnPrescr_id'])) {
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$where .= " AND EvnPrescrRegime_pid = :EvnPrescr_id";
		}else{
			$params['EvnPrescr_pid'] = $data['Evn_pid'];
			$where .= " AND EvnPrescr_pid = :EvnPrescr_pid";
		}

		if (!empty($data['Lpu_id'])) {
			$where .= " and EP.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\" 
				,EP.EvnPrescr_pid as \"Evn_pid\"
				,EP.PrescriptionType_id as \"PrescriptionType_id\"
				,EPP.PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\" 
				--,EPP.EvnPrescrRegime_setDT as \"Evn_setDT\"
				,to_char(EPP.EvnPrescrRegime_setDT, 'YYYY-MM-DD') as \"Evn_setDT\"
				--,EP.EvnPrescr_Count as \"Evn_Count\"
				,EP.EvnPrescr_Descr as \"EvnPrescr_Descr\"
				,(SELECT COUNT(*) FROM v_EvnPrescrRegime WHERE EvnPrescrRegime_pid=EP.EvnPrescr_id) as \"Evn_Count\"
			FROM
				v_EvnPrescr EP 
				inner join v_EvnPrescrRegime EPP  on EPP.EvnPrescrRegime_pid = EP.EvnPrescr_id
			WHERE (1=1)
			limit 1
		".$where;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получить назначения  КВС,ТАП. Метод для API
	 */
	function getEvnPrescrForAPI($data) {
		$resDiet = array();
		$resRegime = array();

		if (!empty($data['PrescriptionType_id'])) {
			if($data['PrescriptionType_id'] == 1){
				$resRegime = $this->getEvnPrescrRegimeForAPI($data);
			}elseif($data['PrescriptionType_id'] == 2){
				$resDiet = $this->getEvnPrescrDietForAPI($data);
			}else{
				return array(
					'Error_Msg' => 'Для передачи доступны значения: «1 Режим» и «2 Диета»'
				);
			}
		}else{
			$resDiet = $this->getEvnPrescrDietForAPI($data);
			$resRegime = $this->getEvnPrescrRegimeForAPI($data);
		}

		$result = array_merge($resDiet, $resRegime);
		return $result;
	}

	/**
	 * сохранение назначения (режим). Метод для API
	 */
	function savePrescriptionRegimeForAPI($data){
		$this->load->model('EvnPrescrRegime_model', 'EvnPrescrRegime_model');
		if ($data === false) {
			return false;
		}

		$response = $this->EvnPrescrRegime_model->doSave($data);
		return $response;
	}

	/**
	 * Cохранение Обстоятельства получения травмы
	 * @param $data
	 * @return bool|int
	 */
	function saveTraumaCircumEvnPS($data)
	{
		/**@var CI_DB_result $result */
		$sql = "select TraumaCircumEvnPS_id as \"TraumaCircumEvnPS_id\" from v_TraumaCircumEvnPS TC where EvnPS_id = :EvnPS_id limit 1";
		$result = $this->db->query($sql, array('EvnPS_id' => $data['EvnPS_id']));
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result_array();
		$exec = (!empty($res[0]['TraumaCircumEvnPS_id'])) ? 'upd' : 'ins';
		$query = "
			select
				TraumaCircumEvnPS_id as \"TraumaCircumEvnPS_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_TraumaCircumEvnPS_{$exec} (
				TraumaCircumEvnPS_id := :TraumaCircumEvnPS_id,
				TraumaCircumEvnPS_Name := :TraumaCircumEvnPS_Name,
			    TraumaCircumEvnPS_setDT := :TraumaCircumEvnPS_setDT,
				EvnPS_id := :EvnPS_id,
				pmUser_id := :pmUser_id
			)
		";
		$setDT = null;
		if (isset($data['TraumaCircumEvnPS_setDTDate'])) {
			$setDT = $data['TraumaCircumEvnPS_setDTDate'] . " " . $data['TraumaCircumEvnPS_setDTTime'] . ":00";
		}
		$params = [
			'TraumaCircumEvnPS_id' => (!empty($res[0]['TraumaCircumEvnPS_id'])) ? $res[0]['TraumaCircumEvnPS_id'] : null,
			'TraumaCircumEvnPS_Name' => $data['TraumaCircumEvnPS_Name'],
			'TraumaCircumEvnPS_setDT' => $setDT,
			'EvnPS_id' => $data['EvnPS_id'],
			'pmUser_id' => $data['pmUser_id']
		];
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? true : false;
	}

	/**
	 * сохранение назначения (диета). Метод для API
	 */
	function savePrescriptionDietForAPI($data){
		$this->load->model('EvnPrescrDiet_model', 'EvnPrescrDiet_model');
		if ($data === false) return false;

		$response = $this->EvnPrescrDiet_model->doSave($data);
		return $response;
	}

	/**
	 * Сохранить назначение
	 */
	function saveEvnPrescrForAPI($data) {
		$sql = "
			SELECT 
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				Lpu_id as \"Lpu_id\"
			FROM 
				v_Evn 
			WHERE Evn_id = :EvnPrescr_pid
		";
		$resEvn = $this->getFirstRowFromQuery($sql, array('EvnPrescr_pid' => $data['Evn_pid']));
		if (!is_array($resEvn)) {
			return false;
		}
		if(isset($resEvn['Lpu_id']) && $resEvn['Lpu_id'] != $data['Lpu_id']){
			return array(
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			);
		}

		$data = array_merge($data, $resEvn);
		$data['EvnPrescr_dayNum'] = ( !empty($data['Evn_Count']) ) ? $data['Evn_Count'] : 1;
		if( !empty($data['Evn_setDT']) ) $data['EvnPrescr_setDate'] = $data['Evn_setDT'];

		if($data['PrescriptionType_id'] == 1){
			if(empty($data['EvnPrescrRegime_setDT'])) $data['EvnPrescrRegime_setDT'] = date_create();
			$result = $this->savePrescriptionRegimeForAPI($data);
		}elseif($data['PrescriptionType_id'] == 2){
			if(empty($data['EvnPrescrDiet_setDT'])) $data['EvnPrescrDiet_setDT'] = date_create();
			$result = $this->savePrescriptionDietForAPI($data);
		}else{
			return array(
				'Error_Msg' => 'Для передачи доступны значения: «1 Режим» и «2 Диета»'
			);
		}
		if (!$this->isSuccessful($result)) {
			throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
		}

		if (is_array($result)) {
			return $result[0];
		}

		return false;
	}

	/**
	 * Редактировать назначение
	 */
	function updateEvnPrescrForAPI($data) {
		$sql = "
			SELECT 
				case when COALESCE(PrescriptionStatusType_id, 1) = 1 then 'edit' else 'view' end as \"accessType\", 
				EvnPrescr_pid as \"EvnPrescr_pid\",
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				Lpu_id as \"Lpu_id\",
				PrescriptionType_id as \"PrescriptionType_id\",
				Server_id as \"Server_id\",
				EvnPrescr_pid as \"EvnPrescr_pid\"
			FROM 
				v_EvnPrescr 
			WHERE EvnPrescr_id = :EvnPrescr_id
		";
		$resEvn = $this->getFirstRowFromQuery($sql, array('EvnPrescr_id' => $data['EvnPrescr_id']));
		if (!is_array($resEvn)) {
			return false;
		}
		if(isset($resEvn['Lpu_id']) && $resEvn['Lpu_id'] != $data['Lpu_id']){
			return array(
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			);
		}

		$data = array_merge($data, $resEvn);
		$data['EvnPrescr_dayNum'] = ( !empty($data['Evn_Count']) ) ? $data['Evn_Count'] : 1;
		if( !empty($data['Evn_setDT']) ) $data['EvnPrescr_setDate'] = $data['Evn_setDT'];

		switch ($resEvn['PrescriptionType_id']) {
			case 1:
				$data['PrescriptionType_id'] = 1;
				if (empty($data['PrescriptionRegimeType_id'])) {
					$resRegime = $this->getEvnPrescrRegimeForAPI($data);
					$data['PrescriptionRegimeType_id'] = $resRegime[0]['PrescriptionRegimeType_id'];
				}
				$result = $this->savePrescriptionRegimeForAPI($data);
				break;
			case 2:
				$data['PrescriptionType_id'] = 2;
				if (empty($data['PrescriptionDietType_id'])) {
					$resDiet = $this->getEvnPrescrDietForAPI($data);
					$data['PrescriptionDietType_id'] = $resDiet[0]['PrescriptionDietType_id'];
				}
				$result = $this->savePrescriptionDietForAPI($data);
				break;
			default:
				return array(
					'Error_Msg' => 'Назначение не относится к типам «1 Режим» или «2 Диета»'
				);
				break;
		}

		if (!$this->isSuccessful($result)) {
			throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
		}

		if (is_array($result)) {
			return $result[0];
		}

		return false;
	}

	/**
	 * Получение информации о КВС из ЭРСБ
	 * Используется: swInfoKVSfromERSB
	 */
	function getInfoKVSfromERSB($data) {
		$sql = "
			SELECT 
				eps.Hospitalization_id as \"Hosp_id\",
				to_char(eps.EvnPSLink_insDT, 'DD.MM.YYYY HH24:MI') as \"Hosp_date\"
			FROM 
				r101.EvnPSLink eps
			WHERE eps.EvnPS_id = :EvnPS_id
		";
		$resEvn = $this->getFirstRowFromQuery($sql, array('EvnPS_id' => $data['EvnPS_id']));
		if (!is_array($resEvn)) {
			return false;
		}
		return $resEvn;
	}

	/**
	 * Метод возвращает информацию о КВС, переданном в БГ
	 *
	 * @param $data
	 * @return array|bool
	 */
	function getInfoEvnPSfromBg($data) {
		$query = "
			SELECT 
				Hospitalization_id as \"id\",
				to_char(EvnPSLink_insDT, 'DD.MM.YYYY HH24:MI') as \"insDate\"
			FROM
				r101.EvnPSLink
			WHERE Hospitalization_id = :Hospitalization_id
		";

		$result = $this->getFirstRowFromQuery($query, array('Hospitalization_id' => $data['id']));


		if (!is_array($result)) {
			return false;
		}
		return $result;
	}

	/**
	 *  Метод возвращает информацию о КВС для контроля выбора в формах "Выбор отделения ЛПУ", "Исход пребывания в приемном отделении"
	 *
	 * @param $data
	 * @return array|bool
	 */
	function controlSavingForm_DepartmentSelectionLPU($data) {
		$query = "
			SELECT 
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\",
				EPS.MedicalCareFormType_id as \"MedicalCareFormType_id\",
				EPS.EvnDirection_Num as \"EvnDirection_Num\",
				EPS.LpuSection_eid as \"LpuSection_eid\",
				to_char(EPS.EvnDirection_setDT, 'DD.MM.YYYY') as \"EvnDirection_setDate\"
			FROM
				v_EvnPS EPS
			WHERE EvnPS_id = :EvnPS_id
		";

		$result = $this->getFirstRowFromQuery($query, array('EvnPS_id' => $data['EvnPS_id']));

		if (!is_array($result)) {
			return false;
		}
		return $result;
	}

	/**
	 * Получение результата услуги ЭКГ
	 */
	function getEcgResult($data) {
		$params = array(
			'EvnUsluga_id' => $data['EvnUsluga_id']
		);

		$query="SELECT ER.ECGResult_Name as \"ECGResult_Name\",
					ER.ECGResult_Code as \"ECGResult_Code\"
				FROM EvnUslugaCommon EUC 
				left join AttributeSignValue ASV  on ASV.AttributeSignValue_TablePKey = EUC.EvnUslugaCommon_id
				left join AttributeValue AV  on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
				left join Attribute A  on A.Attribute_id = AV.AttributeValue_id and A.Attribute_SysNick = 'EKGResult'
				left join ECGResult ER  on ER.ECGResult_id = AV.AttributeValue_ValueIdent
				WHERE EUC.EvnUsluga_id = :EvnUsluga_id";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getMedicalCareBudgType($data) {
		$MedicalCareBudgType_id = null;

		$params = array(
			'EvnPS_setDate' => $data['EvnPS_setDate'],
			'EvnPS_disDate' => $data['EvnPS_disDate'],
			'LeaveType_SysNick' => $data['LeaveType_SysNick'],
			'PayType_SysNick' => $data['PayType_SysNick'],
			'LpuUnitType_SysNick' => $data['LpuUnitType_SysNick'],
			'HTMedicalCareClass_id' => $data['HTMedicalCareClass_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'Diag_id' => $data['Diag_id'],
			'Person_id' => $data['Person_id'],
		);

		if (in_array(getRegionNick(), array('perm','astra','ufa','kareliya','krym','pskov'))) {
			if (in_array($params['PayType_SysNick'], array('bud', 'fbud', 'subrf', 'mbudtrans_mbud')) &&
				(
					!empty($params['EvnPS_disDate']) &&
					(!empty($params['LeaveType_SysNick']) || !empty($params['HTMedicalCareClass_id']))
				)
			) {
				$DocumentUcType = ($params['LpuUnitType_SysNick'] == 'stac')?2:3;
				//$DocumentUcType = !empty($params['HTMedicalCareClass_id']) ? 5 : $DocumentUcType;
				$diff = date_diff(date_create($params['EvnPS_setDate']), date_create($params['EvnPS_disDate']));

				if(!empty($params['HTMedicalCareClass_id'])){
					$groupCode = $this->getFirstResultFromQuery("select HTMCC.HTMedicalCareClass_BudgGroupCode as \"HTMedicalCareClass_BudgGroupCode\" from dbo.HTMedicalCareClass HTMCC where HTMCC.HTMedicalCareClass_id = :HTMedicalCareClass_id limit 1", $params);

					if(!empty($groupCode)){
						$MedicalCareBudgType_id = $this->getFirstResultFromQuery("select MCTL.MedicalCareBudgType_id as \"MedicalCareBudgType_id\" from v_MedicalCareBudgType MCTL where MCTL.MedicalCareBudgType_Code = :MedicalCareBudgType_Code limit 1", array('MedicalCareBudgType_Code' => intval($groupCode) + 100 ));
					}

				}else{
					$this->load->model('MedicalCareBudgType_model');
					$resp = $this->MedicalCareBudgType_model->getMedicalCareBudgTypeId(array(
						'MedicalCareBudgTypeLink_DocumentUcType' => $DocumentUcType,
						'Lpu_id' => $params['Lpu_id'],
						'LpuSection_id' => $params['LpuSection_id'],
						'LpuSectionProfile_id' => $params['LpuSectionProfile_id'],
						'Diag_id' => $params['Diag_id'],
						'MedicalCareBudgTypeLink_Dlit' => $diff->days,
						'Person_id' => $params['Person_id'],
						'begDate' => $params['EvnPS_setDate'],
						'endDate' => $params['EvnPS_disDate'],
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
					$MedicalCareBudgType_id = $resp[0]['MedicalCareBudgType_id'];
				}


			}
		}

		return array(array(
			'success' => true,
			'MedicalCareBudgType_id' => $MedicalCareBudgType_id
		));
	}

	/**
	 * @throws Exception
	 */
	protected function _checkMorbusOnkoLeave() {

		if (getRegionNick() == 'perm' && !empty($this->PrehospWaifRefuseCause_id) && self::SCENARIO_AUTO_CREATE != $this->scenario) {
			// если КВС/движение не сохранялось, значит и специфики точно нет, проверяем только диагноз
			if (empty($this->id)) {
				$mo_chk = $this->getFirstResultFromQuery("
					select Diag.Diag_id as \"Diag_id\"
					from v_Diag Diag 
					where 
						Diag.Diag_id = :Diag_id
						and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
					limit 1
				", array('Diag_id' => $this->Diag_pid));
				if(!empty($mo_chk)) {
                    throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
                }
			} else {
				if (in_array($this->regionNick, ['kareliya', 'adygeya'])) {
					$OnkoConsultField = 'OC.OnkoConsult_id as "OnkoConsult_id"';
					$OnkoConsultJoin = "
						LEFT JOIN LATERAL (
							select OnkoConsult_id
							from v_OnkoConsult 
							where MorbusOnkoLeave_id = mol.MorbusOnkoLeave_id
							limit 1
						) OC On true
					";
				}
				else {
					$OnkoConsultField = 'null as "OnkoConsult_id"';
					$OnkoConsultJoin = "";
				}

				$query = "
					select
						es.EvnSection_id as \"EvnSection_id\",
						COALESCE(es.EvnSection_disDate, es.EvnSection_setDate) as \"filterDate\",
						Diag.Diag_id as \"Diag_id\",
						mol.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
    					mol.EvnSection_id as \"EvnSection_id\",
    					mol.OnkoDiag_id as \"OnkoDiag_id\",
    					mol.MorbusOnkoLeave_MorfoDiag as \"MorbusOnkoLeave_MorfoDiag\",
    					mol.OnkoT_id as \"OnkoT_id\",
    					mol.OnkoN_id as \"OnkoN_id\",
    					mol.OnkoM_id as \"OnkoM_id\",
    					mol.TumorStage_id as \"TumorStage_id\",
    					mol.MorbusOnkoLeave_IsTumorDepoUnknown as \"MorbusOnkoLeave_IsTumorDepoUnknown\",
    					mol.MorbusOnkoLeave_IsTumorDepoLympha as \"MorbusOnkoLeave_IsTumorDepoLympha\",
    					mol.MorbusOnkoLeave_IsTumorDepoBones as \"MorbusOnkoLeave_IsTumorDepoBones\",
    					mol.MorbusOnkoLeave_IsTumorDepoLiver as \"MorbusOnkoLeave_IsTumorDepoLiver\",
    					mol.MorbusOnkoLeave_IsTumorDepoLungs as \"MorbusOnkoLeave_IsTumorDepoLungs\",
    					mol.MorbusOnkoLeave_IsTumorDepoBrain as \"MorbusOnkoLeave_IsTumorDepoBrain\",
    					mol.MorbusOnkoLeave_IsTumorDepoSkin as \"MorbusOnkoLeave_IsTumorDepoSkin\",
    					mol.MorbusOnkoLeave_IsTumorDepoKidney as \"MorbusOnkoLeave_IsTumorDepoKidney\",
    					mol.MorbusOnkoLeave_IsTumorDepoOvary as \"MorbusOnkoLeave_IsTumorDepoOvary\",
    					mol.MorbusOnkoLeave_IsTumorDepoPerito as \"MorbusOnkoLeave_IsTumorDepoPerito\",
    					mol.MorbusOnkoLeave_IsTumorDepoMarrow as \"MorbusOnkoLeave_IsTumorDepoMarrow\",
    					mol.MorbusOnkoLeave_IsTumorDepoOther as \"MorbusOnkoLeave_IsTumorDepoOther\",
    					mol.MorbusOnkoLeave_IsTumorDepoMulti as \"MorbusOnkoLeave_IsTumorDepoMulti\",
    					mol.TumorPrimaryTreatType_id as \"TumorPrimaryTreatType_id\",
    					mol.TumorRadicalTreatIncomplType_id as \"TumorRadicalTreatIncomplType_id\",
    					mol.pmUser_insID as \"pmUser_insID\",
    					mol.pmUser_updID as \"pmUser_updID\",
    					mol.MorbusOnkoLeave_insDT as \"MorbusOnkoLeave_insDT\",
    					mol.MorbusOnkoLeave_updDT as \"MorbusOnkoLeave_updDT\",
    					mol.Diag_id as \"Diag_id\",
    					mol.OnkoTumorStatusType_id as \"OnkoTumorStatusType_id\",
    					mol.OnkoDiagConfType_id as \"OnkoDiagConfType_id\",
    					mol.OnkoLateComplTreatType_id as \"OnkoLateComplTreatType_id\",
    					mol.OnkoCombiTreatType_id as \"OnkoCombiTreatType_id\",
    					mol.MorbusOnkoLeave_NumTumor as \"MorbusOnkoLeave_NumTumor\",
    					mol.OnkoLesionSide_id as \"OnkoLesionSide_id\",
    					mol.MorbusOnkoLeave_NumHisto as \"MorbusOnkoLeave_NumHisto\",
    					mol.TumorCircumIdentType_id as \"TumorCircumIdentType_id\",
    					mol.OnkoLateDiagCause_id as \"OnkoLateDiagCause_id\",
    					mol.TumorAutopsyResultType_id as \"TumorAutopsyResultType_id\",
    					mol.MorbusOnkoLeave_specSetDT as \"MorbusOnkoLeave_specSetDT\",
    					mol.MorbusOnkoLeave_specDisDT as \"MorbusOnkoLeave_specDisDT\",
    					mol.MorbusOnkoLeave_IsMainTumor as \"MorbusOnkoLeave_IsMainTumor\",
    					mol.MorbusOnkoLeave_setDiagDT as \"MorbusOnkoLeave_setDiagDT\",
    					mol.OnkoDiag_mid as \"OnkoDiag_mid\",
    					mol.OnkoPostType_id as \"OnkoPostType_id\",
    					mol.DiagAttribType_id as \"DiagAttribType_id\",
    					mol.DiagAttribDict_id as \"DiagAttribDict_id\",
    					mol.DiagResult_id as \"DiagResult_id\",
    					mol.DiagAttribDict_fid as \"DiagAttribDict_fid\",
    					mol.DiagResult_fid as \"DiagResult_fid\",
    					mol.OnkoHealType_id as \"OnkoHealType_id\",
    					mol.OnkoConsultResult_id as \"OnkoConsultResult_id\",
    					mol.OnkoTreatment_id as \"OnkoTreatment_id\",
    					mol.EvnDiagPLSop_id as \"EvnDiagPLSop_id\",
    					mol.MorbusOnkoLeave_FirstSignDT as \"MorbusOnkoLeave_FirstSignDT\",
    					mol.TumorPrimaryMultipleType_id as \"TumorPrimaryMultipleType_id\",
    					mol.MorbusOnkoLeave_takeDT as \"MorbusOnkoLeave_takeDT\",
    					mol.HistologicReasonType_id as \"HistologicReasonType_id\",
    					mol.MorbusOnkoLeave_histDT as \"MorbusOnkoLeave_histDT\",
    					mol.OnkoT_fid as \"OnkoT_fid\",
    					mol.OnkoN_fid as \"OnkoN_fid\",
    					mol.OnkoM_fid as \"OnkoM_fid\",
    					mol.TumorStage_fid as \"TumorStage_fid\",
    					mol.EvnDiag_id as \"EvnDiag_id\",
    					mol.OnkoStatusYearEndType_id as \"OnkoStatusYearEndType_id\",
						to_char(mol.MorbusOnkoLeave_takeDT, 'DD.MM.YYYY') as \"MorbusOnko_takeDT\",
						OT.OnkoTreatment_id as \"OnkoTreatment_id\",
						OT.OnkoTreatment_Code as \"OnkoTreatment_Code\",
						dbo.Age2(PS.Person_Birthday, COALESCE(es.EvnSection_disDate, es.EvnSection_setDate)) as \"Person_Age\",
						MorbusOnkoLink.MorbusOnkoLink_id as \"MorbusOnkoLink_id\",
						{$OnkoConsultField}
					from 
						v_EvnSection es 
						inner join v_Diag Diag  on Diag.Diag_id = COALESCE(:Diag_id, es.Diag_id)
						inner join v_Person_all PS  on PS.PersonEvn_id = es.PersonEvn_id and PS.Server_id = es.Server_id
						left join v_MorbusOnkoLeave mol  on mol.EvnSection_id = es.EvnSection_id
						left join v_OnkoTreatment OT  on OT.OnkoTreatment_id = mol.OnkoTreatment_id
						LEFT JOIN LATERAL(
								SELECT 
									MorbusOnkoLink_id
								FROM
									v_MorbusOnkoLink
								WHERE
									MorbusOnkoLeave_id = mol.MorbusOnkoLeave_id
							    limit 1
						) as MorbusOnkoLink ON true
						{$OnkoConsultJoin}
					where 
						es.EvnSection_pid = :EvnPS_id 
						and es.EvnSection_IsPriem = 2
						and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
					limit 1
				";
				$mo_chk = $this->getFirstRowFromQuery($query, array(
					'EvnPS_id' => $this->id,
					'Diag_id' => $this->Diag_pid
				));
				if (!empty($mo_chk)) {
                    if ( in_array($this->regionNick, ['kareliya', 'adygeya']) && empty($mo_chk['OnkoConsult_id']) ) {
                        throw new Exception('В специфике по онкологии заполните раздел "Сведения о проведении консилиума".');
                    }

                    if (
                        $this->regionNick == 'ufa' && !empty($mo_chk['OnkoTreatment_id']) && ($mo_chk['OnkoTreatment_Code'] == 1 || $mo_chk['OnkoTreatment_Code'] == 2)
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoUnknown']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLympha'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoBones']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLiver'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoLungs']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoBrain'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoSkin']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoKidney'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoOvary']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoPerito'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoMarrow']) && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoOther'])
                        && empty($mo_chk['MorbusOnkoLeave_IsTumorDepoMulti'])
                    ) {
                        throw new Exception('В специфике по онкологии необходимо заполнить раздел "Локализация отдаленных метастазов", обязательный при поводе обращения "1. Лечение при рецидиве" или "2. Лечение при прогрессировании".');
                    }
                    
                    if (
                        empty($mo_chk['OnkoTreatment_id'])
                        /*|| (
                            empty($mo_chk['MorbusOnkoLink_id']) && empty($mo_chk['HistologicReasonType_id'])
                        )*/
                        || (
                            empty($mo_chk['TumorStage_fid']) && !empty($mo_chk['OnkoTreatment_id']) && $mo_chk['OnkoTreatment_Code'] != 5 && $mo_chk['OnkoTreatment_Code'] != 6
                        )
                        || (
                        empty($mo_chk['TumorStage_id'])
                        )
                    ) {
                        throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
                    }

                    $onkoFields = array('OnkoT', 'OnkoN', 'OnkoM');
                    foreach ( $onkoFields as $field ) {
                        if ( empty($mo_chk[$field . '_id']) ) {
                            throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
                        }
                    }

					$onkoFields = array();

					if ( $mo_chk['OnkoTreatment_Code'] === 0 && $mo_chk['Person_Age'] >= 18 ) {
						$onkoFields[] = 'OnkoT';
						$onkoFields[] = 'OnkoN';
						$onkoFields[] = 'OnkoM';
					}

					foreach ( $onkoFields as $field ) {
						if ( !empty($mo_chk[$field . '_fid']) ) {
							continue;
						}

						$param1 = false; // Есть связка с диагнозом и OnkoT_id is not null
						$param2 = false; // Есть связка с диагнозом и OnkoT_id is null
						$param3 = false; // Нет связки с диагнозом и есть записи с Diag_id is null

						$LinkData = $this->queryResult("
							(select
							Diag_id as \"Diag_id\",
							{$field}_fid as \"{$field}_fid\",
							{$field}Link_begDate as \"{$field}Link_begDate\",
							{$field}Link_endDate as \"{$field}Link_endDate\"
							from dbo.v_{$field}Link 
							where Diag_id = :Diag_id)
							union all
							(select
							Diag_id as \"Diag_id\",
							{$field}_fid as \"{$field}_fid\",
							{$field}Link_begDate as \"{$field}Link_begDate\",
							{$field}Link_endDate as \"{$field}Link_endDate\"
							from dbo.v_{$field}Link 
							where Diag_id is null)
						", array('Diag_id' => $mo_chk['Diag_id']));

						if ( $LinkData !== false ) {
							foreach ( $LinkData as $row ) {
								if (
									(empty($row[$field . 'Link_begDate']) || $row[$field . 'Link_begDate'] <= $mo_chk['filterDate'])
									&& (empty($row[$field . 'Link_endDate']) || $row[$field . 'Link_endDate'] >= $mo_chk['filterDate'])
								) {
									if ( !empty($row['Diag_id']) && $row['Diag_id'] == $mo_chk['Diag_id'] ) {
										if ( !empty($row[$field . '_fid']) ) {
											$param1 = true;
										}
										else {
											$param2 = true;
										}
									}
									else if ( empty($row['Diag_id']) ) {
										$param3 = true;
									}
								}
							}
						}

						if ( $param1 == true || ($param3 == true && $param2 == false) ) {
                            throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
						}
					}
				}

				if (empty($this->_params['ignoreMorbusOnkoDrugCheck'])) {
					$rslt = $this->getFirstResultFromQuery("
						select MOD.MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\"
						from v_MorbusOnkoDrug MOD 
							inner join v_EvnSection ES  on ES.EvnSection_id = MOD.Evn_id
						where ES.EvnSection_pid = :EvnPS_id
							and ES.EvnSection_IsPriem = 2
						limit 1
					", array('EvnPS_id' => $this->id), true);
					if ( !empty($rslt) ) {
						$this->_saveResponse['ignoreParam'] = "ignoreMorbusOnkoDrugCheck";
						$this->_saveResponse['Alert_Msg'] = "В разделе «Данные о препаратах» остались препараты, не связанные с лечением. Продолжить сохранение?";
						throw new Exception('YesNo', 106);
					}
				}
			}
		}
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function _isExistsEvnDirection() {
		//print_r(array('$this->EvnDirection_id' => $this->EvnDirection_id));exit;
		if (empty($this->EvnDirection_id)) {
			return false;
		}
		$params = array(
			'EvnDirection_id' => $this->EvnDirection_id
		);
		$query = "
			select count(*) as \"cnt\" 
			from v_EvnDirection_all  
			where EvnDirection_id = :EvnDirection_id
			limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->getFirstResultFromQuery($query, $params);
		if ($resp === false) {
			throw new Exception('Ошибка при проверке направлению');
		}
		return $resp > 0;
	}

	/**
	 * Печать КВС
	 */
	function printEvnPS($data) {
		$this->load->library('parser');

		if (empty($data['KVS_Type'])) {
			$data['KVS_Type'] = '';
		}
		if (empty($data['Parent_Code'])) {
			$data['Parent_Code'] = '3';
		}

		$response = $this->getEvnPSFields($data);

		if (!is_array($response) || count($response) == 0) {
			echo 'Ошибка при получении данных по КВС';
			return true;
		}

		return $this->_printEvnPS($data, $response);
	}

	/**
	 * @param $data
	 * @param $response
	 * @return string
	 */
	protected function _printEvnPS($data, $response) {
		$evn_diag_ps_admit_data = array();
		$evn_diag_ps_anatom_data = array();
		$evn_diag_ps_hosp_data = array();
		$evn_diag_ps_section_data = array();
		$evn_section_data = array();
		$evn_stick_data = array();
		$evn_usluga_oper_data = array();

		if ( strlen($response[0]['DiagP_Name']) > 0 ) {
			$evn_diag_ps_admit_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagP_Code'], 'Diag_Name' => $response[0]['DiagP_Name']);
		}

		if ( strlen($response[0]['DiagA_Name']) > 0 ) {
			$evn_diag_ps_anatom_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagA_Code'], 'Diag_Name' => $response[0]['DiagA_Name']);
		}

		if ( strlen($response[0]['DiagH_Name']) > 0 ) {
			$evn_diag_ps_hosp_data[] = array('DiagSetClass_Name' => 'Основной', 'Diag_Code' => $response[0]['DiagH_Code'], 'Diag_Name' => $response[0]['DiagH_Name']);
		}

		$response_temp = $this->getEvnDiagPSList($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				switch ( $response_temp[$i]['DiagSetType_Code'] ) {
					case 1:
						$evn_diag_ps_hosp_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
						break;

					case 2:
						$evn_diag_ps_admit_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
						break;

					case 3:
						$evn_diag_ps_section_data[] = array(
							'EvnDiagPS_pid' => $response_temp[$i]['EvnDiagPS_pid'],
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
						break;

					case 5:
						$evn_diag_ps_anatom_data[] = array(
							'DiagSetClass_Name' => $response_temp[$i]['DiagSetClass_Name'],
							'Diag_Code' => $response_temp[$i]['Diag_Code'],
							'Diag_Name' => $response_temp[$i]['Diag_Name']
						);
						break;
				}
			}
		}

		//-------------------------Печать разных вариантов КВС----------------------------------
        $KVS_Type = $data['KVS_Type'];
        $template = 'evn_ps_template_list_a4';
        $response_temp = $this->getEvnSectionData($data);

        if (($data['Parent_Code'] == '1')||($data['Parent_Code']=='3')||($data['Parent_Code']=='4'))
        {

            if((is_array($response_temp))&&(count($response_temp)==0)) //В истории болезни нет ни одного движения
            {
                $template = 'evn_ps_template_list_a4_first';
            }
        }

        if ($data['Parent_Code'] == '6')
        {
            $template = 'evn_ps_template_list_a4_first';
        }

        if (($data['Parent_Code'] == '2')||($data['Parent_Code']=='5')||($data['Parent_Code']=='7'))
        {

            if($data['KVS_Type'] == 'AB') //Здесь означает, что список был составлен по КВС
            {
                if((is_array($response_temp))&&(count($response_temp)==0)) //В истории болезни нет ни одного движения
                {
                    $template = 'evn_ps_template_list_a4_first';
                }
            }
            if($data['KVS_Type'] == 'VG') //Здесь означает, что список был составлен по движениям
            {
                //проверка на то, какое по хронологии движение было выбрано. Тут уже поможет только SQL-запрос
                $response_section = $this->checkEvnSection($data);

                if ($response_section == '0')
                {
                    //Не первое по хронологии;
                    $KVS_Type = 'G';
                }
                else
                {
                    //Первое по хронологии;
                    $KVS_Type = 'V';
                }
                //echo json_return_errors($KVS_Type);
                //return false;
            }
        }

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;

			if ( count($evn_diag_ps_hosp_data) < 3 ) {
				for ( $j = count($evn_diag_ps_hosp_data); $j < 3; $j++ ) {
					$evn_diag_ps_hosp_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			if ( count($evn_diag_ps_admit_data) < 3 ) {
				for ( $j = count($evn_diag_ps_admit_data); $j < 3; $j++ ) {
					$evn_diag_ps_admit_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			if ( count($evn_diag_ps_anatom_data) < 2 ) {
				for ( $j = count($evn_diag_ps_anatom_data); $j < 2; $j++ ) {
					$evn_diag_ps_anatom_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
			}

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'EvnSection_id' => 0,
						'EvnSection_setDT' => '&nbsp;<br />&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'PayType_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagSetClassOsn_Name' => '&nbsp;',
						'EvnSectionDiagOsn_Name' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
					);
				}

				$evn_section_data[$i]['EvnSectionDiagData'] = array();

				if ( $i < count($evn_section_data) ) {
					foreach ( $evn_diag_ps_section_data as $key => $value ) {
						if ( $value['EvnDiagPS_pid'] == $evn_section_data[$i]['EvnSection_id'] ) {
							$evn_section_data[$i]['EvnSectionDiagData'][] = array(
								'EvnSectionDiagSetClass_Name' => $value['DiagSetClass_Name'],
								'EvnSectionDiag_Code' => $value['Diag_Code'],
								'EvnSectionDiag_Name' => $value['Diag_Name'],
								'EvnSectionMes_Code' => '&nbsp;'
							);
						}
					}
				}

				if ( count($evn_section_data[$i]['EvnSectionDiagData']) < 2 ) {
					for ( $j = count($evn_section_data[$i]['EvnSectionDiagData']); $j < 2; $j++ ) {
						$evn_section_data[$i]['EvnSectionDiagData'][$j] = array(
							'EvnSectionDiagSetClass_Name' => '&nbsp;<br />&nbsp;',
							'EvnSectionDiag_Code' => '&nbsp;',
							'EvnSectionDiag_Name' => '&nbsp;',
							'EvnSectionMes_Code' => '&nbsp;'
						);
					}
				}
			}
		}

		$response_temp = $this->getEvnStickData($data);
		if ( is_array($response_temp) ) {
			$evn_stick_data = $response_temp;

			if ( count($evn_stick_data) < 2 ) {
				for ( $i = count($evn_stick_data); $i < 2; $i++ ) {
					$evn_stick_data[$i] = array(
						'EvnStick_begDate' => '&nbsp;',
						'EvnStick_endDate' => '&nbsp;',
						'StickOrder_Name' => '&nbsp;',
						'EvnStick_Ser' => '&nbsp;',
						'EvnStick_Num' => '&nbsp;',
						'StickCause_Name' => '&nbsp;',
						'Sex_Name' => '&nbsp;',
						'EvnStick_Age' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'LpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'MedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'PayType_Name' => $response_temp[$i]['PayType_Name'],
					'Usluga_Name' => ($data['session']['region']['nick'] == 'krym' ? ($response_temp[$i]['Usluga_Code']) : '') .' '.$response_temp[$i]['Usluga_Name'],
					'AnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => $response_temp[$i]['EvnUslugaOper_IsRadGraf'] == 1 ? 'X' : '&nbsp;',
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'LpuSection_Code' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'MedPersonal_Code' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'PayType_Name' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'Usluga_Name' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'AnesthesiaClass_Name' => '&nbsp;<br />&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => '&nbsp;',
				);
			}
		}

		$invalid_type_name = '';
		$lpu_unit_type_name = '';

		switch ( $response[0]['InvalidType_Code'] ) {
			case 81:
				$invalid_type_name = "3-я группа";
				break;

			case 82:
				$invalid_type_name = "2-я группа";
				break;

			case 83:
				$invalid_type_name = "1-я группа";
				break;
		}

		switch ( $response[0]['LpuUnitType_Code'] ) {
			case 2:
				$lpu_unit_type_name = "круглосуточного стационара";
				break;

			case 3:
				$lpu_unit_type_name = "дневного стационара при стационаре";
				break;

			case 4:
				$lpu_unit_type_name = "стационара на дому";
				break;

			case 5:
				$lpu_unit_type_name = "дневного стационара при поликлинике";
				break;
		}

		if (($KVS_Type == 'V')||($KVS_Type == 'G'))
		{
			$hosp_result = $this->GetHosp_Result($data);

			if($KVS_Type == 'G')
			{

				for ( $j = count($evn_diag_ps_admit_data); $j < 3; $j++ ) {
					$evn_diag_ps_admit_data[] = array(
						'DiagSetClass_Name' => '&nbsp;<br />&nbsp;',
						'Diag_Code' => '&nbsp;',
						'Diag_Name' => '&nbsp;'
					);
				}
				//Проверям исход предыдущего движения (был ли перевод в другое отделение)
				$prev_result = $this->Check_OtherDep($data);

				if ($prev_result) {
					//var_dump($prev_result);
					//exit;
					$response[0]['EvnPS_setDate'] = $prev_result[0]['EvnSection_setDate'];
					$response[0]['EvnPS_setTime'] = $prev_result[0]['EvnSection_setTime'];
					$response[0]['PrehospDirect_Name'] = $prev_result[0]['LpuSection_Name'];
					$response[0]['PrehospOrg_Name'] = $prev_result[0]['Post_Name'];
				}
				$print_data = array(
					'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
					'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
					'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
					'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
					'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
					'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
					'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
					'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
					'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
					'EvnDie_IsWait' => $hosp_result[0]['EvnDie_IsWait'] == 1 ? 'X' : '&nbsp;',
					'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
					'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
					'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
					'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
					'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
					'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
					'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
					'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
					'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
					'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
					'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
					'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnSection_disDate']),
					'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnSection_disTime']),
					'EvnPS_HospCount' => '',
					'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnlaw' => '',
					'EvnPS_IsUnport' => '',
					'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
					'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
					'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
					'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
					'EvnPS_TimeDesease' => '',
					'Okei_NationSymbol' => '',
					'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
					'EvnSectionData' => $evn_section_data,
					'EvnStickData' => $evn_stick_data,
					'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
					'EvnUslugaOperData' => $evn_usluga_oper_data,
					'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
					'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
					'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
					'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
					'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
					'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
					'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
					'LpuSection_Name' => '',
					'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
					'OmsSprTerr_Code' => returnValidHTMLString($response[0]['OmsSprTerr_Code']),
					'OmsSprTerr_Name' => returnValidHTMLString($response[0]['OmsSprTerr_Name']),
					'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
					'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
					'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
					'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
					'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
					'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
					'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
					'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
					'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
					'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
					'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
					'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
					'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
					'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
					'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
					'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
					'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
					'PreHospMedPersonal_Fio' => '',
					'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
					'PrehospToxic_Name' => '',
					'LpuSectionTransType_Name' => '',
					'PrehospTrauma_Name' => '',
					'PrehospType_Name' => '',
					'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
					'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
					'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
					'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
					'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
				);
			}
			else{
				$print_data = array(
					'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
					'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
					'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
					'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
					'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
					'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
					'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
					'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
					'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
					'EvnDie_IsWait' => $hosp_result[0]['EvnDie_IsWait'] == 1 ? 'X' : '&nbsp;',
					'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
					'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
					'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
					'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
					'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
					'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
					'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
					'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
					'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
					'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
					'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
					'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnSection_disDate']),
					'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnSection_disTime']),
					'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
					'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
					'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
					'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
					'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
					'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
					'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
					'Okei_NationSymbol' => returnValidHTMLString($response[0]['Okei_NationSymbol']),
					'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
					'EvnSectionData' => $evn_section_data,
					'EvnStickData' => $evn_stick_data,
					'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
					'EvnUslugaOperData' => $evn_usluga_oper_data,
					'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
					'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
					'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
					'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
					'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
					'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
					'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
					'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
					'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
					'OmsSprTerr_Code' => returnValidHTMLString($response[0]['OmsSprTerr_Code']),
					'OmsSprTerr_Name' => returnValidHTMLString($response[0]['OmsSprTerr_Name']),
					'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
					'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
					'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
					'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
					'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
					'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
					'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
					'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
					'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
					'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
					'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
					'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
					'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
					'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
					'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
					'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
					'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
					'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
					'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
					'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
					'LpuSectionTransType_Name' => returnValidHTMLString($response[0]['LpuSectionTransType_Name']),
					'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
					'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
					'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
					'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
					'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
					'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
					'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
				);
			}
		}
		else if ($KVS_Type == 'A')
		{
			$hosp_result = $this->GetHosp_Result($data);
			$print_data = array(
				'AnatomMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Code']),
				'AnatomMedPersonal_Fio' => returnValidHTMLString($hosp_result[0]['AnatomMedPersonal_Fio']),
				'AnatomWhere_Name' => returnValidHTMLString($hosp_result[0]['AnatomWhere_Name']),
				'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
				'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
				'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
				'EvnAnatomPlace' => returnValidHTMLString($hosp_result[0]['EvnAnatomPlace']),
				'EvnDie_expDate' => returnValidHTMLString($hosp_result[0]['EvnDie_expDate']),
				'EvnDie_expTime' => returnValidHTMLString($hosp_result[0]['EvnDie_expTime']),
				'EvnDie_IsWait' => $hosp_result[0]['EvnDie_IsWait'] == 1 ? 'X' : '&nbsp;',
				'EvnDie_IsAnatom' => $hosp_result[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
				'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
				'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
				'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
				'EvnDieMedPersonal_Code' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Code']),
				'EvnDieMedPersonal_Fin' => returnValidHTMLString($hosp_result[0]['EvnDieMedPersonal_Fin']),
				'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
				'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
				'EvnLeave_IsAmbul' => $hosp_result[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
				'EvnLeave_UKL' => returnValidHTMLString($hosp_result[0]['EvnLeave_UKL']),
				'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
				'EvnPS_disDate' => returnValidHTMLString($hosp_result[0]['EvnPS_disDate']),
				'EvnPS_disTime' => returnValidHTMLString($hosp_result[0]['EvnPS_disTime']),
				'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
				'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
				'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
				'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
				'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
				'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
				'Okei_NationSymbol' => returnValidHTMLString($response[0]['Okei_NationSymbol']),
				'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
				'EvnSectionData' => $evn_section_data,
				'EvnStickData' => $evn_stick_data,
				'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
				'EvnUslugaOperData' => $evn_usluga_oper_data,
				'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
				'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
				'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
				'LeaveCause_Name' => returnValidHTMLString($hosp_result[0]['LeaveCause_Name']),
				'LeaveType_Name' => returnValidHTMLString($hosp_result[0]['LeaveType_Name']),
				'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
				'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
				'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
				'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
				'OmsSprTerr_Code' => returnValidHTMLString($response[0]['OmsSprTerr_Code']),
				'OmsSprTerr_Name' => returnValidHTMLString($response[0]['OmsSprTerr_Name']),
				'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
				'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
				'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
				'OtherLpu_Name' => returnValidHTMLString($hosp_result[0]['OtherLpu_Name']),
				'OtherStac_Name' => returnValidHTMLString($hosp_result[0]['OtherStac_Name']),
				'OtherStacType_Name' => returnValidHTMLString($hosp_result[0]['OtherStacType_Name']),
				'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
				'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
				'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
				'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
				'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
				'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
				'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
				'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
				'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
				'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
				'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
				'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
				'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
				'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
				'LpuSectionTransType_Name' => returnValidHTMLString($response[0]['LpuSectionTransType_Name']),
				'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
				'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
				'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
				'ResultDesease_Name' => returnValidHTMLString($hosp_result[0]['ResultDesease_Name']),
				'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
				'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
				'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			);
		}
		else{ //Вариант Б
			$print_data = array(
				'AnatomMedPersonal_Code' => returnValidHTMLString($response[0]['AnatomMedPersonal_Code']),
				'AnatomMedPersonal_Fio' => returnValidHTMLString($response[0]['AnatomMedPersonal_Fio']),
				'AnatomWhere_Name' => returnValidHTMLString($response[0]['AnatomWhere_Name']),
				'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
				'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
				'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
				'EvnAnatomPlace' => returnValidHTMLString($response[0]['EvnAnatomPlace']),
				'EvnDie_expDate' => returnValidHTMLString($response[0]['EvnDie_expDate']),
				'EvnDie_expTime' => returnValidHTMLString($response[0]['EvnDie_expTime']),
				'EvnDie_IsWait' => $response[0]['EvnDie_IsWait'] == 1 ? 'X' : '&nbsp;',
				'EvnDie_IsAnatom' => $response[0]['EvnDie_IsAnatom'] == 1 ? 'X' : '&nbsp;',
				'EvnDiagPSAdmitData' => $evn_diag_ps_admit_data,
				'EvnDiagPSAnatomData' => $evn_diag_ps_anatom_data,
				'EvnDiagPSHospData' => $evn_diag_ps_hosp_data,
				'EvnDieMedPersonal_Code' => returnValidHTMLString($response[0]['EvnDieMedPersonal_Code']),
				'EvnDieMedPersonal_Fin' => returnValidHTMLString($response[0]['EvnDieMedPersonal_Fin']),
				'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num']),
				'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate']),
				'EvnLeave_IsAmbul' => $response[0]['EvnLeave_IsAmbul'] == 1 ? 'X' : '&nbsp;',
				'EvnLeave_UKL' => returnValidHTMLString($response[0]['EvnLeave_UKL']),
				'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv']),
				'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate']),
				'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime']),
				'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount']),
				'EvnPS_IsDiagMismatch' => $response[0]['EvnPS_IsDiagMismatch'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsImperHosp' => $response[0]['EvnPS_IsImperHosp'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsShortVolume' => $response[0]['EvnPS_IsShortVolume'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnlaw' => $response[0]['EvnPS_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsUnport' => $response[0]['EvnPS_IsUnport'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_IsWrongCure' => $response[0]['EvnPS_IsWrongCure'] == 1 ? 'X' : '&nbsp;',
				'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
				'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv']),
				'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
				'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime']),
				'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
				'Okei_NationSymbol' => returnValidHTMLString($response[0]['Okei_NationSymbol']),
				'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара',
				'EvnSectionData' => $evn_section_data,
				'EvnStickData' => $evn_stick_data,
				'EvnUdost_SerNum' => returnValidHTMLString($response[0]['EvnUdost_SerNum']),
				'EvnUslugaOperData' => $evn_usluga_oper_data,
				'InvalidType_begDate' => returnValidHTMLString($response[0]['InvalidType_begDate']),
				'InvalidType_Name' => returnValidHTMLString($invalid_type_name),
				'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
				'LeaveCause_Name' => returnValidHTMLString($response[0]['LeaveCause_Name']),
				'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name']),
				'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
				'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
				'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
				'LpuUnitType_Name' => returnValidHTMLString($lpu_unit_type_name),
				'OmsSprTerr_Code' => returnValidHTMLString($response[0]['OmsSprTerr_Code']),
				'OmsSprTerr_Name' => returnValidHTMLString($response[0]['OmsSprTerr_Name']),
				'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
				'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
				'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
				'OtherLpu_Name' => returnValidHTMLString($response[0]['OtherLpu_Name']),
				'OtherStac_Name' => returnValidHTMLString($response[0]['OtherStac_Name']),
				'OtherStacType_Name' => returnValidHTMLString($response[0]['OtherStacType_Name']),
				'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
				'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
				'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
				'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
				'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
				'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
				'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
				'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
				'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
				'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
				'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
				'PreHospMedPersonal_Fio' => returnValidHTMLString($response[0]['PreHospMedPersonal_Fio']),
				'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
				'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name']),
				'LpuSectionTransType_Name' => returnValidHTMLString($response[0]['LpuSectionTransType_Name']),
				'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
				'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name']),
				'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
				'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name']),
				'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
				'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
				'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			);
		}

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = returnValidHTMLString($response[0]['PersonEncrypHIV_Encryp']);

			$person_fields = array('PolisType_Name', 'Polis_Num', 'Polis_Ser', 'OMSSprTerr_Code', 'OrgSmo_Name',
				'Person_OKATO', 'Sex_Code', 'Sex_Name', 'Person_Birthday', 'Person_Age', 'DocumentType_Name', 'Document_Ser',
				'Document_Num', 'KLAreaType_Name', 'KLAreaType_id', 'Person_Phone', 'PAddress_Name', 'UAddress_Name', 'SocStatus_Code',
				'InvalidType_Name', 'PersonCard_Code', 'PrivilegeType_Code'
			);

			foreach($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}

	/**
	 * Список профилей коек
	 */
	function getBedList($data) {
		$select = "
			gb.GetBed_id as \"GetBed_id\",
			gb.BedProfile as \"BedProfile\",
			gb.BedProfileRu as \"BedProfileRu\",
			gb.TypeSrcFinRu as \"TypeSrcFinRu\",
			gb.StacTypeRu as \"StacTypeRu\",
			gb.BedProfileRu || ' (' || gb.TypeSrcFinRu || '/' || gb.StacTypeRu || ')' as \"BedProfileRuFull\"
		";

		if (!empty($data['GetBed_id'])) {
			return $this->queryResult("select {$select} from r101.GetBed gb where gb.GetBed_id = :GetBed_id", $data);
		}

		$pers_data = $this->getFirstRowFromQuery("select dbo.Age2(Person_Birthday, dbo.tzGetDate()) as \"age\", Sex_id as \"Sex_id\" from v_PersonState where Person_id = :Person_id", $data);
		$data['fpid'] = $this->getFirstResultFromQuery("select FPID as \"FPID\" from r101.LpuSectionFPIDLink where LpuSection_id = :LpuSection_id", $data);
		$data['Sex_id'] = strtr($pers_data['Sex_id'], "123", "325");

		$basequery = "select 
				gb.BedProfile as BedProfile,
				gb.TypeSrcFinRu as TypeSrcFinRu,
				gb.StacTypeRu as StacTypeRu
			from r101.GetBed gb 
			inner join r101.GetRoom gr on gr.ID = gb.RoomID
			inner join r101.GetFP fp on fp.FPID = gr.FPID
			inner join r101.GetMO mo on mo.ID = fp.MOID
			where mo.Lpu_id = :Lpu_id
				and gb.LastAction = 1
				and gr.Sex in (1,4,to_number(:Sex_id))
		";

		/*if ($pers_data['age'] >= 18) {
			$basequery .= " and gr.Child is null ";
		}*/

		if ($data['fpid']) {
			$query = $basequery;
			$query .=" and fp.FPID = :fpid ";
			$res = $this->queryResult("
				select	
					{$select}
				from (
					{$query}
					group by 
						gb.BedProfile,
						gb.TypeSrcFinRu,
						gb.StacTypeRu
				) t
				left join lateral(
					select * 
					from r101.GetBed gb
					where t.BedProfile = gb.BedProfile and t.TypeSrcFinRu = gb.TypeSrcFinRu and t.StacTypeRu = gb.StacTypeRu
					limit 1
				) gb on true
			", $data);
			if (count($res)) return $res;
		}

		return $this->queryResult("
			select
				{$select}
			from (
				{$basequery}
				group by 
					gb.BedProfile,
					gb.TypeSrcFinRu,
					gb.StacTypeRu
			) t
			left join lateral(
				select * 
				from r101.GetBed gb
				where t.BedProfile = gb.BedProfile and t.TypeSrcFinRu = gb.TypeSrcFinRu and t.StacTypeRu = gb.StacTypeRu
				limit 1
			) gb on true
		", $data);
	}

	/**
	 * Загрузка формы КВС в МАРМ
	 */
	function mGetEvnPSInfo($data) {

		$access_filter = '1 = 2'; $access_join = '';
		$queryParams = array('EvnPS_id' => $data['EvnPS_id']);

		if (isset($data['session']['CurMedStaffFact_id'])) {

			$access_filter = "
				eps.Lpu_id = :Lpu_id 
				AND LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')
			";

			$access_join = '
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU on MSF.LpuUnit_id = LU.LpuUnit_id
			';

			$queryParams['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}

		$query = "
			select
				case when {$access_filter} 
					then 'edit' 
					else 'view'
				end as \"accessType\",
				eps.EvnPS_id as \"EvnPS_id\",
				eps.EvnPS_IsSigned as \"EvnPS_IsSigned\",
				eps.Person_id as \"Person_id\",
				eps.PersonEvn_id as \"PersonEvn_id\",
				eps.Server_id as \"Server_id\",
				eps.MedPersonal_pid as \"MedPersonal_id\",
				RTRIM(eps.EvnPS_NumCard) as \"EvnPS_NumCard\",
				d.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				LT.LeaveType_Code as \"LeaveType_Code\",
				LT.LeaveType_Name as \"LeaveType_Name\",
				PT.PrehospType_id as \"PrehospType_id\",
				PT.PrehospType_Name as \"PrehospType_Name\",
				PA.PrehospArrive_id as \"PrehospArrive_id\",
				PA.PrehospArrive_Name as \"PrehospArrive_Name\",
				eps.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\",
				eps.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\",
				eps.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\",
				eps.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\",
				coalesce(cast(eps.EvnDirection_Num as varchar),'') as \"EvnDirection_Num\",
				coalesce(to_char(eps.EvnDirection_setDT, 'dd.mm.yyyy'),'') as \"EvnDirection_setDate\",
				coalesce(to_char(eps.EvnPS_setDT, 'dd.mm.yyyy'),'') as \"EvnPS_setDate\",
				coalesce(to_char(eps.EvnPS_setDT, 'HH24:MI'),'') as \"EvnPS_setTime\",
				--дата и время  выписки из стационара, или текущие  дата и время, если данных о выписке нет
				to_char(coalesce(EL.EvnLeave_setDT,coalesce(eps.EvnPS_disDT,dbo.tzGetDate())), 'dd.mm.yyyy') as \"EvnPS_disDate\",
				to_char(coalesce(EL.EvnLeave_setDT,coalesce(eps.EvnPS_disDT,dbo.tzGetDate())), 'HH24:MI') as \"EvnPS_disTime\",
				EUP.cnt as \"EvnUslugaParCount\",
				ES.cnt + ES2.cnt as \"EvnStickCount\",
				ER.cnt as \"EvnReceptCount\",
				EMD.cnt as \"EvnMediaDataCount\"
			from
				v_EvnPS eps
				left join v_LpuSection LS on LS.LpuSection_id = eps.LpuSection_id
				left join v_LeaveType LT on eps.LeaveType_id = LT.LeaveType_id
				left join v_PrehospType PT on eps.PrehospType_id = PT.PrehospType_id
				left join v_PrehospArrive PA on eps.PrehospArrive_id = PA.PrehospArrive_id
				left join v_Diag d on d.Diag_id = eps.Diag_id
				left join v_EvnLeave EL on eps.EvnPS_id = EL.EvnLeave_pid
				{$access_join}
				left join lateral(
					select
						count(EUP.EvnUslugaPar_id) as cnt
					from
						v_EvnUslugaPar EUP
						left join v_Evn EvnUP on EvnUP.Evn_id = eup.EvnUslugaPar_pid
					where
						EUP.EvnUslugaPar_rid =  eps.EvnPS_id
						and EUP.EvnUslugaPar_setDT is not null
						and coalesce(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
				) EUP on true
				left join lateral(
					select
						count(ER.EvnRecept_id) as cnt
					from
						v_EvnRecept er
					where
						er.EvnRecept_pid =  eps.EvnPS_id
						and er.Lpu_id = eps.Lpu_id
				) ER on true
				left join lateral(
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es
					where
						es.EvnStickBase_mid = eps.EvnPS_id
				) ES on true
				left join lateral(
					select
						count(ES.EvnStickBase_id) as cnt
					from
						v_EvnStickBase es
					where
						exists (select EvnLink_id from v_EvnLink 
						where Evn_id = eps.EvnPS_id and Evn_lid = es.EvnStickBase_id)
				) ES2 on true
				left join lateral(
					select
						count(EMD.EvnMediaData_id) as cnt
					from
						v_EvnMediaData EMD
					where
						EMD.Evn_id = eps.EvnPS_id
				) EMD on true
			where eps.EvnPS_id = :EvnPS_id
			limit 1
		";

		$result = $this->getFirstRowFromQuery($query, $queryParams);

		// получаем список движений
		if (!empty($result)) {

			$filter = '';

			$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
			if (!empty($diagFilter)) $filter .= " and $diagFilter";

			$result['EvnSection'] = $this->queryResult("
				select
					es.EvnSection_id as \"EvnSection_id\",
					to_char(es.EvnSection_setDT, 'dd.mm.yyyy') as \"EvnSection_setDate\",
					ls.LpuSection_Name as \"LpuSection_Name\"
				from v_EvnSection es
				left join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				where 
					es.EvnSection_pid = :EvnPS_id 
					and es.LpuSection_id is not null
					{$filter}					
				order by es.EvnSection_setDT desc
			", array('EvnPS_id' => $data['EvnPS_id']));
		}

		return $result;
	}

	/**
	 * method
	 */
	function mBindRFID($data) {

		$lpu_id = $this->getFirstResultFromQuery("
			select
				Lpu_id as \"Lpu_id\"
			from v_EvnPS
			where EvnPS_id = :EvnPS_id
			limit 1
		", $data);
		if (empty($lpu_id)) {
			throw new Exception('Данная КВС не существует');
		}

		if ($lpu_id != $data['session']['lpu_id']) {
			throw new Exception('Данная КВС создана не в Вашей МО');
		}

		$OtherEvnPS_id = $this->getFirstResultFromQuery("
			select
				EvnPS_id as \"EvnPS_id\"
			from v_EvnPS
			where 
				EvnPS_RFID = :RFID_id
				and EvnPS_id != :EvnPS_id
				and Lpu_id = :Lpu_id
			limit 1
		", $data);
		if (!empty($OtherEvnPS_id) && empty($data['ignoreOtherRelationsForRFID'])) {
			throw new Exception('Метка уже связана с другой картой. Привязать метку к выбранной карте и удалить связь с другой картой?', 1);
		}

		$EvnPS_id = $this->getFirstResultFromQuery("
			select
				EvnPS_id as \"EvnPS_id\"
			from v_EvnPS
			where 
				EvnPS_id = :EvnPS_id
				and EvnPS_RFID != :RFID_id
				and Lpu_id = :Lpu_id
			limit 1
		", $data);
		if (!empty($EvnPS_id) && empty($data['ignoreOtherRelationsForEvnPS'])) {
			throw new Exception('Карта пациента уже связана с другой меткой. Привязать карту к новой метке?', 2);
		}

		// зануляем РФИД другой КВС
		if (!empty($OtherEvnPS_id)) {
			$unbind_result = $this->updateRfidLabel($data);
		}

		$bind_result = $this->updateRfidLabel($data);
		return $bind_result;
	}

	/**
	 * method
	 */
	function updateRfidLabel($data) {

		if (!empty($data['EvnPS_id'])) {

			$response = $this->getFirstRowFromQuery("
				update EvnPS
				set EvnPS_RFID = :RFID_id
				where Evn_id = :EvnPS_id
				returning '' as \"Error_Msg\", 0 as \"Error_Code\";
			", array(
					'EvnPS_id' => $data['EvnPS_id'],
					'RFID_id' => $data['RFID_id']
				)
			);


			if (!empty($response['Error_Msg'])) {
				throw new Exception('Не удалось обновить метку КВС');
			}
		}

		return $response;
	}

	/**
	 * method
	 */
	function mGetEvnPSByRFID($data) {

		$response = $this->getFirstRowFromQuery("
			select
				eps.EvnPS_id as \"EvnPS_id\",
				eps.Person_id as \"Person_id\",
				coalesce(PS.Person_SurName,'НЕИЗВЕСТЕН') ||' '|| coalesce(PS.Person_FirName,'НЕИЗВЕСТЕН') ||' '|| coalesce(PS.Person_SecName,'НЕИЗВЕСТЕН') as \"Person_Fio\"
			from v_EvnPS eps
			left join v_PersonState PS on ps.Person_id = eps.Person_id 
			where 
				eps.EvnPS_RFID = :RFID_id
				and eps.Lpu_id = :Lpu_id
			limit 1
		", array(
				'RFID_id' => $data['RFID_id'],
				'Lpu_id' => $data['session']['lpu_id']
			)
		);

		if (empty($response['EvnPS_id'])) {
			$response['EvnPS_id'] = null;
		}

		return $response;
	}

	/**
	 * method
	 */
	function mUnbindRFID($data) {

		$lpu_id = $this->getFirstResultFromQuery("
			select
				Lpu_id as \"Lpu_id\"
			from v_EvnPS
			where EvnPS_id = :EvnPS_id
			limit 1
		", array('EvnPS_id' => $data['EvnPS_id']));

		if (empty($lpu_id)) {
			throw new Exception('Данная КВС не существует');
		}

		if ($lpu_id != $data['session']['lpu_id']) {
			throw new Exception('Данная КВС создана не в Вашей МО');
		}

		$unbind_result = $this->updateRfidLabel(array(
			'EvnPS_id' => $data['EvnPS_id'],
			'RFID_id' => null
		));

		return $unbind_result;
	}


	/**
	 * Контроль на соответствие параметров КВС и движений КВС виду оплаты
	 */
	function _checkConformityPayType(){
		
		if($this->regionNick != 'perm'){
			return true;
		}
		
		$query = "
			select
				es.EvnSection_id as \"EvnSection_id\",
				eps.Lpu_id as \"Lpu_id\",
				PrehospType.PrehospType_SysNick as \"PrehospType_SysNick\",
				ksgkpg.Mes_id as \"Mes_id\"
			from
				v_EvnPS eps
				inner join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
				inner join v_CureResult cr on cr.CureResult_id = es.CureResult_id
				left join v_PayType pt on pt.PayType_id = es.PayType_id
				left join PrehospType on PrehospType.PrehospType_id = eps.PrehospType_id
				left join v_MesTariff spmt on ES.MesTariff_id = spmt.MesTariff_id
				left join v_MesOld as ksgkpg on spmt.Mes_id = ksgkpg.Mes_id
			where
				eps.EvnPS_id = :Evn_id
				and pt.PayType_SysNick = 'mbudtrans'
				and cr.CureResult_Code = 1
			limit 1
		";
		
		$result = $this->db->query($query, array(
			'Evn_id' => $this->id
		));
		
		if (is_object($result)) {
			$resp = $result->result('array');
			if(!empty($resp)){
				
				// достаём дату последнего движения
				$query = "
					SELECT
						es.EvnSection_id as \"EvnSection_id\",
						to_char(coalesce(es.EvnSection_disDate, es.EvnSection_setDate), 'YYYY-MM-DD') as \"EvnSection_disDate\"
					FROM
						v_EvnSection es
					WHERE
						es.EvnSection_pid = :Evn_id
					order by
						es.EvnSection_Index desc
				  	limit 1
				";
				
				$lastEvnSection = $this->queryResult($query, array(
					'Evn_id' => $this->id
				));
				
				$volumeMBTStac = $this->getFirstRowFromQuery("
					select
						av.AttributeValue_id as \"AttributeValue_id\"
					from
						v_AttributeVision avis
						inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						inner join lateral (
							select
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								a2.Attribute_TableName = 'dbo.Lpu'
								and coalesce(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
							limit 1
						) MOFILTER on true
					where
						vt.VolumeType_Code = 'МБТ-Стац'
						and avis.AttributeVision_TableName = 'dbo.VolumeType'
						and av.AttributeValue_ValueIdent = :Mes_id
						and coalesce(av.AttributeValue_begDate, :EvnSection_disDate) <= :EvnSection_disDate
						and coalesce(av.AttributeValue_endDate, :EvnSection_disDate) >= :EvnSection_disDate
					limit 1
				", array(
					'Mes_id' => $resp[0]['Mes_id'],
					'Lpu_id' => $resp[0]['Lpu_id'],
					'EvnSection_disDate' => $lastEvnSection[0]['EvnSection_disDate']
				));
				
				if(
					in_array($resp[0]['PrehospType_SysNick'], array('plan'))
					|| !$volumeMBTStac
				){
					throw new Exception("Параметры КВС и/или движения КВС не соответствуют условиям оплаты в рамках межбюджетного трансферта. Проверьте корректность указания следующих данных в КВС и в движении: вид оплаты, тип госпитализации, КСГ");
				}
			}
		}
	}

	/**
	 * Контроль на наличие в КВС с видом оплаты «МБТ (СЗЗ)» услуг МБТ
	 */
	function checkPayTypeMBT($data){

		if($this->regionNick != 'perm'){
			return true;
		}

		$resp_es = $this->queryResult("
			select
				es.EvnSection_IndexNum as \"EvnSection_IndexNum\",
				es.EvnSection_id as \"EvnSection_id\",
				pt.PayType_SysNick as \"PayType_SysNick\",
				es.EvnSection_setDate as \"EvnSection_setDate\",
				ISNULL(es.EvnSection_disDate, es.EvnSection_setDate) as \"EvnSection_disDate\",
				es.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				EU.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\",
				EU.EvnUslugaCommon_setDT as \"EvnUslugaCommon_setDT\",
				EU.EvnUslugaCommon_disDT as \"EvnUslugaCommon_disDT\",
				es.EvnSection_IsPriem as \"EvnSection_IsPriem\"
			from
				v_EvnSection es
				inner join v_PayType pt on pt.PayType_id = es.PayType_id
				outer apply (
					select 
						ucat.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\",
						euc.EvnUslugaCommon_setDT as \"EvnUslugaCommon_setDT\",
						euc.EvnUslugaCommon_disDT as \"EvnUslugaCommon_disDT\"
					from
						v_EvnUslugaCommon euc 
						inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = euc.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						euc.EvnUslugaCommon_pid = es.EvnSection_id
						and ucat.UslugaComplexAttributeType_SysNick='mbtransf'
					order by
						euc.EvnUslugaCommon_setDT desc
					limit 1	
				) EU
			where
				es.EvnSection_pid = :EvnPS_id
			order by
				es.EvnSection_IndexNum,es.EvnSection_setDT DESC
		", array(
			'EvnPS_id' => $data['EvnPS_id']
		));

		$groupped = array();
		foreach($resp_es as $respone) {

			//за исключением движения в приёмном отделении и движений с методом ВМП
			if (!empty($respone['HTMedicalCareClass_id']) || $respone['EvnSection_IsPriem']==2) {
				continue;
			}

			$key = $respone['EvnSection_IndexNum'];

			if (empty($key)) {
				$key = 'id_' . $respone['EvnSection_id']; // в отдельную группу
			}

			// вид оплаты последнего (хронологически) движения группы 
			if (empty($groupped[$key]['PayType_Last_Visit'])){
				$groupped[$key]['PayType_Last_Visit'] = $respone['PayType_SysNick'];
			}

			//дата выписки в последнем движении 
			if (empty($groupped[$key]['Date_Last_Visit'])){
				$groupped[$key]['Date_Last_Visit'] = $respone['EvnSection_disDate'];
			}

			$groupped[$key]['EvnSections'][$respone['EvnSection_id']] = $respone;
		}

		foreach($groupped as $group) {
			$col_usl=0;
			foreach($group['EvnSections'] as $es) {
				if (
					!empty($es['UslugaComplexAttributeType_SysNick'])
					&& (isset($es['EvnUslugaCommon_setDT']) || (!isset($es['EvnUslugaCommon_setDT']) && $es['EvnUslugaCommon_setDT']<=$group['Date_Last_Visit']))
					&& (isset($es['EvnUslugaCommon_disDT']) || (!isset($es['EvnUslugaCommon_disDT']) && $es['EvnUslugaCommon_disDT']>=$group['Date_Last_Visit']))
				){
					$col_usl++;
				}
			}

			if(
				$group['PayType_Last_Visit']=='mbudtrans_mbud'
				&& $col_usl==0
			){
				throw new Exception('Для случаев с видом оплаты «МБТ (СЗЗ)» обязательно указание услуги по межбюджетному трансферту. Услуга должна быть указана для каждой группы движений с данным видом оплаты.');
			}
		}
	}
	
	/**
	 * Проставляем признак удаления проверки на педикулёз и санобработку
	 */
	function deletePediculos($data){
		if (empty($data['Pediculos_id'])) return false;
		$query = "
			update dbo.Pediculos
			set 
				Pediculos_deleted = 2,
				Pediculos_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where Pediculos_id = :Pediculos_id
		";
		$result = $this->db->query($query, $data);
		return $result;
	}

	/**
	 * Сохранение на педикулёз и санобработку
	 */
	function savePediculos($data){
		$response = array('success' => false);
		if (empty($data['Pediculos_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select
					Pediculos_id as \"Pediculos_id\"
				from v_Pediculos
				where Evn_id = :Evn_id
				ORDER BY Pediculos_insDT DESC
				limit 1
			", $data);
			if(isset($result['Pediculos_id'])) $data['Pediculos_id'] = $result['Pediculos_id'];
		}

		if (empty($data['Pediculos_id'])) {
			$procedure = 'p_Pediculos_ins';
		} else {
			$procedure = 'p_Pediculos_upd';
		}
		$query = "
			select
				Pediculos_id as \"Pediculos_id\",
				Error_Message as \"Error_Msg\",
				Error_Code as \"Error_Code\"
			from {$procedure}(
				Pediculos_id := :Pediculos_id,
				Evn_id := :Evn_id,
				Diag_id := :Diag_id,
				Diag_sid := :Diag_sid,
				Pediculos_DiagDT := :Pediculos_DiagDT,
				Pediculos_isPrint := :Pediculos_isPrint,
				Pediculos_isSanitation := :Pediculos_isSanitation,
				Pediculos_SanitationDT := :Pediculos_SanitationDT,
				pmUser_id := :pmUser_id
			)
		";
		$params = array(
			'Pediculos_id' => (!empty($data['Pediculos_id'])) ? $data['Pediculos_id'] : $data['Pediculos_id'],
			'Evn_id' => $data['Evn_id'],
			'Diag_id' => (!empty($data['PediculosDiag_id'])) ? $data['PediculosDiag_id'] : null,
			'Diag_sid' => (!empty($data['ScabiesDiag_id'])) ? $data['ScabiesDiag_id'] : null,
			'Pediculos_DiagDT' => $data['Pediculos_SanitationDT'],
			'Pediculos_isPrint' => (!empty($data['Pediculos_isPrint'])) ? $data['Pediculos_isPrint'] : null,
			'Pediculos_isSanitation' => (!empty($data['Pediculos_SanitationDT'])) ? 1 : null,
			'Pediculos_SanitationDT' => $data['Pediculos_SanitationDT'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'Pediculos_id' => $arr[0]['Pediculos_id']);
	}

	/**
	 * получение дополнительной информации в блок информации о пациенте
	 */
	function getInfoPanelAdditionalInformation($data){
		if(empty($data['EvnPS_id']) || empty($data['fields']) || !is_array($data['fields'])) return false;
		$arrFields = $data['fields'];
		$join = '';
		$select = '';
		$where = '';
		if(count($arrFields) == 0) return false;

		if(in_array('accompanied_by_an_adult', $arrFields)){
			//--Сопровождается взрослым
			$join .= "
				left join lateral(
					--Сопровождается взрослым
					SELECT
						ES.EvnSection_id,
						ES.EvnSection_IsAdultEscort,
						YN.YesNo_Name AS EvnSection_IsAdultEscort_Name
					FROM v_EvnSection ES
						LEFT JOIN dbo.v_YesNo YN ON YN.YesNo_id = coalesce(ES.EvnSection_IsAdultEscort,1)
					WHERE ES.EvnSection_pid = EPS.EvnPS_id
					ORDER BY ES.EvnSection_insDT DESC
					limit 1
				) EvnSection on true
			";
			$select .= " 
				,EvnSection.EvnSection_id as \"EvnSection_id\"
				,EvnSection.EvnSection_IsAdultEscort_Name AS \"accompanied_by_an_adult\"
			";
		}
		if(in_array('pediculosis_check_and_sanitation', $arrFields)){
			//--Проверка на педикулёз и санобработка
			$join .= "
				left join lateral(
					--Проверка на педикулёз и санобработка
					SELECT
						P.Pediculos_id,
						P.Pediculos_isSanitation,
						P.Pediculos_SanitationDT
					FROM v_Pediculos P
					WHERE P.Evn_id = EPS.EvnPS_id
					ORDER BY P.Pediculos_insDT DESC
					limit 1
				) Pediculos on true
			";
			$select .= " 
				,Pediculos.Pediculos_id as \"Pediculos_id\"
				,CASE WHEN Pediculos.Pediculos_id IS NOT NULL
						AND Pediculos.Pediculos_isSanitation = 1
						AND Pediculos.Pediculos_SanitationDT IS NOT NULL 
					THEN 'Да' 
					ELSE 'Нет' 
				END as \"pediculosis_check_and_sanitation\"
			";
		}
		if(in_array('things_and_valuables_in_storage', $arrFields)){
			//--Вещи и ценности на хранении
			$join .= "
				left join lateral(
					--Вещи и ценности на хранении
					SELECT
						EvnXml.EvnXml_id
					FROM v_EvnXml EvnXml
						LEFT JOIN v_EvnSection ES ON ES.EvnSection_id = EvnXml.Evn_id
						LEFT JOIN XmlTemplate XT ON XT.XmlTemplate_id = EvnXml.XmlTemplate_id
					WHERE ES.EvnSection_pid = EPS.EvnPS_id --AND EvnXml.XmlType_id = 21
						AND XT.XmlType_id = 21
					limit 1
				) EvnXml on true
			";
			$select .= " 
				,EvnXml.EvnXml_id as \"EvnXml_id\"
				,CASE WHEN EvnXml.EvnXml_id IS NOT NULL THEN 'Да' ELSE 'Нет' END as \"things_and_valuables_in_storage\"
			";
		}
		$query = "
            SELECT
				EPS.EvnPS_id as \"EvnPS_id\"
				{$select}
			FROM v_EvnPS EPS
			{$join}
			WHERE EPS.EvnPS_id = :EvnPS_id
			{$where}
			limit 1
        ";
		//echo getDebugSQL($query,$queryParams);die;
		$result = $this->db->query($query,$data);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		else
			return false;
	}

	public function getAllDiagByPS($params) {
		$query = "
			select edps_diag.Diag_Code as \"Diag_Code\"
			from v_EvnDiagPS edps
			inner join Diag edps_diag on edps_diag.Diag_id = edps.Diag_id
			where edps.EvnDiagPS_rid = :EvnPS_id and DiagSetType_id = 3
		";
		return $this->queryList($query, $params);
	}


	function getEvnPSDisDT($data) {
		$query = "
			select 
			       EvnPS_disDT as \"EvnPS_disDT\"
			from 
			     v_EvnPS 
			where 
			      EvnPS_id = :Evn_id
			LIMIT 1
		";
		
		return $this->getFirstResultFromQuery($query, ['Evn_id' => $data['Evn_id']]);
	}

	/**
	 * Возвращает id первого профильного движения
	 * @param $data
	 */
	public function getFirstProfileEvnSectionId($data){
		$filters = "";
		$params = array();

		if ( empty($data['EvnPS_id']) ) {
			return false;
		}

		$query = "
			SELECT
				EvnSection_id as \"EvnSection_id\"
			FROM v_EvnSection
			WHERE EvnSection_pid = :EvnPS_id
			AND EvnSection_IsPriem IS NULL
			ORDER BY EvnSection_id
			LIMIT 1
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, ['EvnPS_id' => $data['EvnPS_id']]);
	}
}
