<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ImmuneProphylaxis - модель для работы со журналами прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 *
 */
class ImmuneProphylaxis_model extends swModel
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Добавление новой записи в журнал вакцинации
	 */
	function addTo_VacJournalAccount($data)
	{
		$insertInto = '';
		$values = '';
		
		$fields = [
			'Person_id',
			'Vaccine_id',
			'vacJournalAccount_DatePurpose',
			'vacJournalAccount_Purpose_MedPersonal_id',
			'vacJournalAccount_Seria',
			'vacJournalAccount_Period',
			'vacJournalAccount_Dose',
			'VaccineWay_id',
			'VaccinePlace_id',
			'vacJournalAccount_Lpu_id',
			'vacJournalAccount_Vac_MedPersonal_id',
			'vacJournalAccount_StatusType_id',
			'vacJournalAccount_DateVac'
		];
		
		foreach ($fields as $field) {
			$insertInto .= "{$field}, ";
			$values .= " :{$field}, ";
		}
		
		$insertInto = mb_substr($insertInto, 0, -2);
		$values = mb_substr($values, 0, -2);

		$query = "
			insert into vac.vac_JournalAccount
			(" . $insertInto . ")
			values (" . $values . " )";

		//echo getDebugSQL($query, $data); die();
		$this->db->query($query, $data);
		$it =$this->db->affected_rows();
		if ($it < 1) return null;
		$query = "select top 1 vacJournalAccount_id
		from vac.vac_JournalAccount
		where Person_id = :Person_id and vacJournalAccount_DateVac = :vacJournalAccount_DateVac and Vaccine_id = :Vaccine_id
		order by vacJournalAccount_id desc";

		return $this->queryResult($query, $data);
	}

	/**
	 * Редактирование записи в журнале вакцинации
	 */
	function update_VacJournalAccount($data)
	{
		$setParams = [];
		$where = 'vacJournalAccount_id = :vacJournalAccount_id';
		
		$fields = [
			'Person_id',
			'vacJournalAccount_DatePurpose',
			'vacJournalAccount_Purpose_MedPersonal_id',
			'Vaccine_id',
			'vacJournalAccount_Seria',
			'vacJournalAccount_Period',
			'vacJournalAccount_Dose',
			'VaccineWay_id',
			'VaccinePlace_id',
			'vacJournalAccount_DateVac',
			'vacJournalAccount_Lpu_id',
			'vacJournalAccount_Vac_MedPersonal_id'
		];
		
		foreach ($fields as $field) {
			$setParams[] = "{$field} = :{$field}";
		}

		if (empty($setParams))
			return 1;
		$query = "
			update vac.vac_JournalAccount with(rowlock)
			set	" . implode(',
			', $setParams) . '
			where ' . $where;

		$this->db->query($query, $data);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Получение записи из журнала вакцинации
	 */
	function selectFrom_VacJournalAccount($data)
	{
		$where = [];
		if (isset($data['vacJournalAccount_id'])) {
			$where[] = 'vacJournalAccount_id = :vacJournalAccount_id';
		}
		if (isset($data['Person_id'])) {
			$where[] = 'Person_id = :Person_id';
		}
		if (empty($where))
			throw new Exception('Не указан ни один обязательный параметр');

		$query = "
		select
			vacJournalAccount_id,
			Person_id,
			vacJournalAccount_Purpose_MedPersonal_id,
			Vaccine_id,
			vacJournalAccount_Seria,
			vacJournalAccount_Period,
			vacJournalAccount_Dose,
			VaccineWay_id,
			VaccinePlace_id,
			vacJournalAccount_DateVac,
			vacJournalAccount_Lpu_id,
			vacJournalAccount_Vac_MedPersonal_id
		from vac.vac_JournalAccount
			where " . implode(' and ', $where);

		$res = $this->db->query($query, $data);

		if (is_object($res)) {
			$res = $res->result('array');

			return $res;
		} else return null;
	}

	/**
	 * Добавление новой записи в журнале прививок Манту
	 */
	function addTo_Vac_JournalMantu($data)
	{
		$insertInto = '';
		$values = '';
		
		$fields = [
			'Person_id',
			'TubDiagnosisType_id',
			'VaccineWay_id',
			'VaccinePlace_id',
			'MantuReactionType_id',
			'JournalMantu_Dose',
			'JournalMantu_Lpu_id',
			'JournalMantu_vacMedPersonal_id',
			'DiaskinTestReactionType_id',
			'JournalMantu_StatusType_id',
			'JournalMantu_ReactDescription',
			'JournalMantu_DateVac',
			'JournalMantu_Seria',
			'JournalMantu_Period'
		];
		
		foreach ($fields as $field) {
			$insertInto .= "{$field}, ";
			$values .= " :{$field}, ";
		}
		
		$insertInto = mb_substr($insertInto, 0, -2);
		$values = mb_substr($values, 0, -2);

		$query = "
		insert into vac.vac_JournalMantu
		(" . $insertInto . ")
		values (" . $values . " )";
		$this->db->query($query, $data);

		$it =$this->db->affected_rows();
		if ($it < 1) return null;
		$query = "select top 1 JournalMantu_id
		from vac.vac_JournalMantu
		where Person_id = :Person_id and JournalMantu_DateVac = :JournalMantu_DateVac and TubDiagnosisType_id = :TubDiagnosisType_id
		order by JournalMantu_id desc";

		return $this->db->query($query, $data)->result('array');
	}

	/**
	 * Редактирование записи в журнале прививок Манту
	 */
	function update_Vac_JournalMantu($data)
	{
		$setParams = [];
		$where = 'JournalMantu_id = :JournalMantu_id';
		
		$fields = [
			'Person_id',		
			'VaccineWay_id',		
			'VaccinePlace_id',		
			'JournalMantu_Dose',		
			'MantuReactionType_id',		
			'JournalMantu_DateVac',		
			'JournalMantu_Lpu_id',		
			'JournalMantu_vacMedPersonal_id',		
			'TubDiagnosisType_id',		
			'DiaskinTestReactionType_id',		
			'JournalMantu_ReactDescription'
		];
		
		foreach ($fields as $field) {
			$setParams[] = "{$field} = :{$field}";
		}

		if (empty($setParams))
			return true;
		$query = "
			update vac.vac_JournalMantu with(rowlock)
			set	" . implode(',
			', $setParams) . '
			where ' . $where;

		$this->db->query($query, $data);
		return ($this->db->affected_rows() > 0);
	}

	/**
	 * Получение записи из журнала прививок Манту
	 */
	function selectFrom_Vac_JournalMantu($data)
	{
		$where = [];
		if (isset($data['JournalMantu_id'])) {
			$where[] = 'JournalMantu_id = :JournalMantu_id';
		}
		if (isset($data['Person_id'])) {
			$where[] = 'Person_id = :Person_id';
		}
		if (empty($where))
			throw new Exception('Не указан ни один обязательный параметр');

		$query = "
		select
			JournalMantu_id,
			Person_id,
			JournalMantu_Dose,
			VaccineWay_id,
			VaccinePlace_id,
			MantuReactionType_id,
			JournalMantu_DateVac,
			JournalMantu_Lpu_id,
			JournalMantu_vacMedPersonal_id,
			TubDiagnosisType_id,
			DiaskinTestReactionType_id
		from vac.vac_JournalMantu
			where " . implode(' and ', $where);

		$res = $this->db->query($query, $data);

		if (is_object($res)) {
			$res = $res->result('array');

			return $res;
		} else return null;
	}
}
