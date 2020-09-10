<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonQuarantine_model - модель для работы с таблицей "Данные о нахождении пациента на карантине"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      25.03.2020
 */

require_once('Scenario_model.php');
class PersonQuarantine_model extends Scenario_model
{
	var $table_name = 'PersonQuarantine';
	const GET_LAST_OPENED_QUARANTINE_CARD = "getLastOpenedQuarantineCard";
	var $useCommonEditLoader = true;
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList([ self::SCENARIO_DO_SAVE, self::SCENARIO_LOAD_EDIT_FORM, self::GET_LAST_OPENED_QUARANTINE_CARD, self::SCENARIO_LOAD_GRID ]);
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'alias' => 'PersonQuarantine_id',
				'properties' => [self::PROPERTY_NEED_TABLE_NAME],
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'int'
			),
			'person_id' => array(
				'alias' => 'Person_id',
				'label' => 'Пациент',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => 'required',
				'type' => 'int'
			),
			'begdt' => array(
				'alias' => 'PersonQuarantine_begDT',
				'label' => 'Дата начала карантина',
				'properties' => [ self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NEED_TABLE_NAME ],
				'save' => 'required',
				'type' => 'date'
			),
			'enddt' => array(
				'alias' => 'PersonQuarantine_endDT',
				'label' => 'Дата окончания карантина',
				'properties' => [self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NEED_TABLE_NAME ],
				'save' => '',
				'type' => 'date'
			),
			'personquarantineopenreason_id' => array(
				'alias' => 'PersonQuarantineOpenReason_id',
				'label' => 'Причина открытия',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => 'required',
				'type' => 'int'
			),
			'personquarantineclosereason_id' => array(
				'alias' => 'PersonQuarantineCloseReason_id',
				'label' => 'Причина закрытия',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			),
			'personquarantine_approvedt' => array(
				'alias' => 'PersonQuarantine_approveDT',
				'label' => 'Дата выявления заболевания',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'date'
			),
			'medstafffact_id' => array(
				'alias' => 'MedStaffFact_id',
				'label' => 'Создавший врач',
				'properties' => [self::PROPERTY_IS_SP_PARAM,self::PROPERTY_NOT_SAFE],
				'save' => '',
				'type' => 'int'
			),
			'medstafffact_cid' => array(
				'alias' => 'MedStaffFact_cid',
				'label' => 'Изменивший врач',
				'properties' => [self::PROPERTY_IS_SP_PARAM,self::PROPERTY_NOT_SAFE],
				'save' => '',
				'type' => 'int'
			),
			'medstafffact_zid' => array(
				'alias' => 'MedStaffFact_zid',
				'label' => 'Закрывший врач',
				'properties' => [self::PROPERTY_IS_SP_PARAM,self::PROPERTY_NOT_SAFE],
				'save' => '',
				'type' => 'int'
			),
			'medstafffact_did' => array(
				'alias' => 'MedStaffFact_did',
				'label' => 'Удаливший врач',
				'properties' => [self::PROPERTY_IS_SP_PARAM,self::PROPERTY_NOT_SAFE],
				'save' => '',
				'type' => 'int'
			),
			'repositoryobesrv_contactdate' => array(
				'properties' => [],
				'alias' => 'RepositoryObesrv_contactDate',
				'select' => 'RO.RepositoryObesrv_contactDate',
				'join' => 'LEFT JOIN  LATERAL (select * from v_RepositoryObserv where PersonQuarantine_id = dbo.v_PersonQuarantine.PersonQuarantine_id and COALESCE(RepositoryObesrv_IsFirstRecord,1)=2 LIMIT 1) RO on true ',
				'type' => 'date'
			),
			'repositoryobserv_arrivaldate' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_arrivalDate',
				'select' => 'RO.RepositoryObserv_arrivalDate',
				'type' => 'date'
			),
			'placearrival_id' => array(
				'properties' => [],
				'alias' => 'PlaceArrival_id',
				'select' => 'RO.PlaceArrival_id',
				'type' => 'int'
			),
			'klcountry_id' => array(
				'properties' => [],
				'alias' => 'KLCountry_id',
				'select' => 'RO.KLCountry_id',
				'type' => 'int'
			),
			'klrgn_id' => array(
				'properties' => [],
				'alias' => 'KLRgn_id',
				'select' => 'RO.KLRgn_id',
				'type' => 'int'
			),
			'transportmeans_id' => array(
				'properties' => [],
				'alias' => 'TransportMeans_id',
				'select' => 'RO.TransportMeans_id',
				'type' => 'int'
			),
			'repositoryobserv_transportdesc' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_TransportDesc',
				'select' => 'RO.RepositoryObserv_TransportDesc',
				'type' => 'string'
			),
			'repositoryobserv_transportplace' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_TransportPlace',
				'select' => 'RO.RepositoryObserv_TransportPlace',
				'type' => 'string'
			),
			'repositoryobserv_transportroute' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_TransportRoute',
				'select' => 'RO.RepositoryObserv_TransportRoute',
				'type' => 'string'
			),
			'repositoryobserv_id' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_id',
				'select' => 'RO.RepositoryObserv_id',
				'type' => 'int'
			),
			'repositoryobserv_flightnumber' => array(
				'properties' => [],
				'alias' => 'RepositoryObserv_FlightNumber',
				'select' => 'RO.RepositoryObserv_FlightNumber',
				'type' => 'string'
			)
		);
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
		if (!empty($data)) {
			$this->applyData($data);
		}

		if(empty($data['PersonQuarantine_id'])) {
			$this->setAttribute('medstafffact_id', $data['MedStaffFact_id']);

			$PersonRegister_id = $this->isExistObjectRecord('PersonRegister', ['Person_id' => $data['Person_id'], 'MorbusType_id' => 116]);
			if (!$PersonRegister_id) {
				$PersonRegister = $this->execCommonSP('p_PersonRegister_ins', [
					'PersonRegister_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
					'Person_id' => $data['Person_id'],
					'MorbusType_id' => '116',
					'PersonRegister_setDate' => $data['RepositoryObesrv_contactDate'] ?? $data['RepositoryObserv_arrivalDate'] ?? null,
					'pmUser_id' => $data['pmUser_id']
				], 'array_assoc');
			}
		}
		else
			$this->setAttribute('medstafffact_cid', $data['MedStaffFact_id']);

		if(!empty($data['PersonQuarantine_endDT'])) {
			$this->setAttribute('medstafffact_zid', $data['MedStaffFact_id']);
		}

		$this->validBegEndDates();
	}

	/**
	 * Проверка дат создания/закрытия
	 * @throws Exception
	 */
	private function validBegEndDates() {
		if(empty($this->getAttribute('begdt')))
			throw new Exception('Дата создания контрольной карты не может быть пустым');
		if(empty($this->getAttribute('enddt')))
			return;
		if($this->getAttribute('begdt') > $this->getAttribute('enddt'))
			throw new Exception('Дата создания контрольной карты не может превышать дату закрытия');
	}

	/**
	 * Логика после сохранения
	 * @param array $result
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		$childAttrs = [
			'repositoryobserv_id',
			'repositoryobserv_arrivaldate',
			'repositoryobesrv_contactdate',
			'person_id',
			'placearrival_id',
			'klcountry_id',
			'transportmeans_id',
			'klrgn_id',
			'repositoryobserv_transportdesc',
			'repositoryobserv_transportplace',
			'repositoryobserv_transportroute',
			'repositoryobserv_flightnumber'
		];

		$params = [];
		foreach ($childAttrs as $name) {
			$params[$name] = $this->getAttribute($name);
		}
		$params['pmUser_id'] = $this->getPromedUserId();
		$params['PersonQuarantine_id'] = $this->getAttribute(self::ID_KEY);
		$params['RepositoryObesrv_IsFirstRecord'] = 2;

		$procName = empty($params['repositoryobserv_id']) ? 'p_RepositoryObserv_ins' : 'p_RepositoryObserv_upd';

		$result = $this->execCommonSP($procName, $params );
		if( !$result ) {
			$msg = 'Ошибка при сохранении';
			if(isset($result['Error_Msg']))
				$msg = $result['Error_Msg'];
			throw new Exception($msg);
		}
		
		$this->load->model('PersonPregnancy_model');
		$this->PersonPregnancy_model->checkAndSaveQuarantine([
			'Person_id' => $this->getAttribute('person_id'),
			'pmUser_id' => $this->getPromedUserId()
		]);
	}

	/**
	 * Получение последней карты
	 */
	protected function getLastOpenedQuarantineCard ( $data = [] ) {
		$params = [
			'Person_id' => $data['Person_id']
		];
		$query = "
			select PersonQuarantine_id as \"PersonQuarantine_id\"
			from dbo.v_PersonQuarantine
			where Person_id=:Person_id and PersonQuarantine_endDT is null
			order by PersonQuarantine_begDT desc
			limit 1 
		";
		$id = $this->getFirstResultFromQuery($query,$params);
		if ($id) {
			return $this->doLoadEditForm([ 'PersonQuarantine_id' => $id ]);
		}
		$curDate = $this->getCurrentDT()->format('d.m.Y');
		$params['PersonQuarantine_begDT'] = $curDate;
		return [ $params ];
	}

	/**
	 * Загрузка грида
	 * @return array|void
	 */
	public function doLoadGrid( $data = Array() ) {
		$params = ['Person_id' => $data['Person_id']];
		$query = "
			select 
				PQ.PersonQuarantine_id as \"PersonQuarantine_id\",
				to_char(PQ.PersonQuarantine_begDT, 'dd.mm.yyyy') as \"PersonQuarantine_begDate\",
				PQ.PersonQuarantineOpenReason_id as \"PersonQuarantineOpenReason_id\",
				PQOR.PersonQuarantineOpenReason_Name as \"PersonQuarantineOpenReason_Name\",
				PQCR.PersonQuarantineCloseReason_Name as \"PersonQuarantineCloseReason_Name\",
				to_char(coalesce(RO.RepositoryObserv_arrivalDate,RO.RepositoryObesrv_contactDate), 'dd.mm.yyyy') as \"arrivalOrContactDate\",
				to_char(PQ.PersonQuarantine_approveDT, 'dd.mm.yyyy') as \"PersonQuarantine_approveDT\",
				to_char(PQ.PersonQuarantine_endDT, 'dd.mm.yyyy') as \"PersonQuarantine_endDate\",
				--Подсчет количества дней на карантине
				EXTRACT( DAY FROM COALESCE(PQ.personquarantine_enddt, tzgetdate()) -
						(case
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 1 then RO.RepositoryObserv_arrivalDate
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 2 then RO.RepositoryObesrv_contactDate
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 3 then PQ.PersonQuarantine_approveDT end)
				)+1 as \"QuarantineDays\",
				MSF.Person_SurName as \"FIO\",
				MSF_zid.Person_SurName as \"FIO_zid\"
			from v_PersonQuarantine PQ
			left join v_RepositoryObserv RO on RO.PersonQuarantine_id = PQ.PersonQuarantine_id and COALESCE(RO.RepositoryObesrv_IsFirstRecord,1) = 2
			left join v_PersonQuarantineOpenReason PQOR on PQOR.PersonQuarantineOpenReason_id = PQ.PersonQuarantineOpenReason_id
			left join v_PersonQuarantineCloseReason PQCR on PQCR.PersonQuarantineCloseReason_id = PQ.PersonQuarantineCloseReason_id
			left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PQ.MedStaffFact_id
			left join v_MedStaffFact MSF_zid on MSF_zid.MedStaffFact_id = PQ.MedStaffFact_zid
			-- PROMEDWEB-14537 Происходит задваивание
			-- v_medPersonal не используется + связь выглядит сомнительно, вроде дб MSF.MedPersonal_id
			--left join v_medPersonal MP on MP.MedPersonal_id = MSF.MedStaffFact_id
			where PQ.Person_id = :Person_id
			order by PQ.PersonQuarantine_begDT desc
		";
		return $this->queryResult($query, $params);
	}

	function getPersonQuarantineViewData($data) {
		$params = [
			'Person_id' => $data['Person_id'],
		];

		$query = "
			select
				PQ.PersonQuarantine_id as \"PersonQuarantine_id\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\",
				to_char(PQ.PersonQuarantine_endDT, 'DD.MM.YYYY') as \"PersonQuarantine_endDT\"
			from
				v_PersonQuarantine PQ
			where
				PQ.Person_id = :Person_id
			order by
				PQ.PersonQuarantine_begDT
		";

		return $this->queryResult($query, $params);
	}
}