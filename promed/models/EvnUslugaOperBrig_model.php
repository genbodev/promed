<?php
class EvnUslugaOperBrig_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function deleteEvnUslugaOperBrig($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnUslugaOperBrig_del
				@EvnUslugaOperBrig_id = :EvnUslugaOperBrig_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array('EvnUslugaOperBrig_id' => $data['EvnUslugaOperBrig_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление операционной бригады)'));
		}
	}

	/**
	 * Получение данных для грида
	 */
	function loadEvnUslugaOperBrigGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				case when EUO.Lpu_id = :Lpu_id " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EUO.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EUOB.EvnUslugaOperBrig_id,
				EUOB.EvnUslugaOper_id as EvnUslugaOperBrig_pid,
				EUO.Person_id,
				EUO.PersonEvn_id,
				EUO.Server_id,
				MSF.MedPersonal_id,
				MSF.MedStaffFact_id,
				ISNULL(ST.SurgType_Code, 0) as SurgType_Code,
				ST.SurgType_id,
				MSF.MedPersonal_TabCode as MedPersonal_Code,
				RTRIM(ISNULL(MSF.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(ST.SurgType_Name, '')) as SurgType_Name
			from v_EvnUslugaOperBrig EUOB with (nolock)
				inner join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EUOB.EvnUslugaOper_id
				left join SurgType ST with (nolock) on ST.SurgType_id = EUOB.SurgType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EUOB.MedStaffFact_id
			where EUOB.EvnUslugaOper_id = :EvnUslugaOperBrig_pid
				and (EUO.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, array(
			'EvnUslugaOperBrig_pid' => $data['EvnUslugaOperBrig_pid'],
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
	 * Функция определяет, существует ли рабочее место на указанную дату
	 */
	function medStaffFactIsOpen($MedStaffFact_id, $date)
	{
		//Проверка что место работы врача не закрыто на дату выполнения услуги
		$response = $this->getFirstResultFromQuery('select top 1 * from v_MedStaffFact with(nolock) where MedStaffFact_id = :MedStaffFact_id and (WorkData_endDate is null OR WorkData_endDate >= :setDT)', array(
			'setDT' => $date,
			'MedStaffFact_id' => $MedStaffFact_id
		));

		return is_numeric($response) ? true : false;
	}

	/**
	 * Сохранение
	 */
	function saveEvnUslugaOperBrig($data) {
		$procedure = '';

		if ( (!isset($data['EvnUslugaOperBrig_id'])) || ($data['EvnUslugaOperBrig_id'] <= 0) ) {
			$procedure = 'p_EvnUslugaOperBrig_ins';
		}
		else {
			$procedure = 'p_EvnUslugaOperBrig_upd';
		}

		$queryParams = array(
			'EvnUslugaOperBrig_id' => $data['EvnUslugaOperBrig_id'],
			'EvnUslugaOperBrig_pid' => $data['EvnUslugaOperBrig_pid'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'SurgType_id' => $data['SurgType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//Проверка что место работы врача не закрыто на дату выполнения услуги
		$isMSFOpen = $this->medStaffFactIsOpen($data['MedStaffFact_id'], $data['EvnUslugaOper_setDate']);


		if ( ! $isMSFOpen ){
			return array(array('Error_Msg' => 'У выбранного врача закрыто рабочее место. Добавление врача невозможно.'));
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUslugaOperBrig_id;
			exec " . $procedure . "
				@EvnUslugaOperBrig_id = @Res output,
				@EvnUslugaOper_id = :EvnUslugaOperBrig_pid,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@SurgType_id = :SurgType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnUslugaOperBrig_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUslugaOperBrig_id' => $data['EvnUslugaOperBrig_id'],
			'EvnUslugaOperBrig_pid' => $data['EvnUslugaOperBrig_pid'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'SurgType_id' => $data['SurgType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
?>