<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 *
 * @access		public
 * @copyright	Copyright (c) 2020 Swan Ltd.
 * @version		06.2020
 */

require_once('EvnPrescrAbstract_model.php');

/**
 * Модель назначения "Вакцинация"
 *
 * Назначения с типом "Вакцинация" хранятся в таблицах EvnPrescr, EvnPrescrVaccination
 * В EvnPrescr хранится само назначение, а в EvnPrescrVaccination - календарь назначения и тип вакцины, признак выполнения
 *
 * @package		EvnPrescr
 * 
 */

class EvnPrescrVaccination_model extends EvnPrescrAbstract_model {

    /**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

    public $EvnPrescr_id = null;
    
    /**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 15;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrVaccination';
    }
    
   /**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			// сохранение назначения вакцинации
			case 'doSave':
				$rules = array(
                    array('field' => 'EvnPrescr_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
                    array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
                    array( 'field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'MedPersonal_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'NatCalendar', 'label' => 'Национальный календарь', 'rules' => 'required|trim', 'type' => 'int' ),
					array( 'field' => 'Vaccination_isEpidemic', 'label' => 'Эпидпоказания', 'rules' => 'required|trim', 'type' => 'int' ),
					array( 'field' => 'Prep_ID', 'label' => 'Вакцина', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'Prep_Dose', 'label' => 'Количество доз', 'rules' => 'required|trim', 'type' => 'int' ),
					array( 'field' => 'Storage_id', 'label' => 'Место хранения вакцины', 'rules' => 'required|trim', 'type' => 'int' ),
					array( 'field' => 'vacinationEnable_inGroup', 'label' => 'Json строка списка выбранных прививок', 'rules' => 'required', 'type' => 'string' ),
					array( 'field' => 'EvnDrug_id', 'label' => 'Назначение медикаментов', 'rules' => 'required|trim', 'type' => 'int' ),
				);
				break;
			// удаление назначения вакцинации
			case 'doDelete':
				$rules = array(
					array( 'field' => 'DrugOstatRegistry_id', 'label' => 'Регистр остатков ', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'EvnPrescrVaccination_id', 'label' => 'Назначение с типом Вакцинация (идентификатор)', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'EvnVaccination_id', 'label' => 'Проведение вакцинации (идентификатор)', 'rules' => 'required', 'type' => 'id'),
					// array( 'field' => 'MedService_id', 'label' => 'Идентификатор кабинета вакцинации', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'PersonEvn_id', 'label' => 'Идентификатор события по человеку', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Prep_Dose', 'label' => 'Количество доз', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Prep_id', 'label' => 'Вакцина', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Storage_id', 'label' => 'Склад', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'VaccinationType_id', 'label' => 'Вид прививки', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'VaccionationPermission_id', 'label' => 'Согласие на вакцинацию', 'rules' => '', 'type' => 'id'),
					array( 'field' => 'DocumentUcStr_id', 'label' => 'Строка документа учета', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'DocumentUc_id', 'label' => 'Документ учета', 'rules' => 'required', 'type' => 'id'),
				);
				break;
			// загрузка списка назначений вакцинации
			case 'doLoad':
				$rules = array(
					array( 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ),
                    array( 'field' => 'Org_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'MedService_id', 'label' => 'Идентификатор кабинета вакцинации', 'type' => 'id'),
				);
				break;
			// сохранение согласия на вакцинацию
			case 'savePermission' :
				$rules = array(
					array( 'field' => 'EvnVaccination_id', 'label' => 'Событие вакцинации', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Person_pid', 'label' => 'Законный представитель', 'type' => 'id' ),
					array( 'field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'PersonalStatement', 'label' => 'Личное заявление', 'rules' => 'required', 'type' => 'boolean' ),
				);
				break;
			// сохранение направления на вакцинацию
			case 'saveVacinationDirection' :
				$rules = array(
					array( 'field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id' ),
					array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
					array( 'field' => 'VacinationDirection', 'label' => 'Json строка Списка назначений', 'rules' => 'required', 'type' => 'string' ),
					array( 'field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => 'required', 'type' => 'id'),
					array( 'field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
					
					// куда направили
					array( 'field' => 'Lpu_did', 'label' => 'МО куда направлен', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'LpuSection_did', 'label' => 'Отделение куда направлен', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'LpuUnit_did', 'label' => 'Подотделение куда направили', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'LpuUnitType_id', 'label' => 'Тип подразделения ЛПУ куда направили', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'MedService_id', 'label' => 'Служба куда направили', 'rules' => 'required', 'type' => 'id' ),
				);
				break;
			// удаление согласия на вакцинацию
			case 'deletePermission' :
				$rules = array(
					array( 'field' => 'EvnPrescrVaccination_id', 'label' => 'Назначение', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'EvnVaccination_id', 'label' => 'Событие вакцинации', 'rules' => 'required', 'type' => 'id' ),
					array( 'field' => 'VaccionationPermission_id', 'label' => 'Согласие', 'rules' => 'required', 'type' => 'id' ),
				);
		}
		return $rules;
    }
	

	/**
	 * doLoad - запрос данных для грида формы "Вакцинация"
	 * 
	 */
	public function doLoad($data) {

		$filter = ' and EV.EvnDirection_vid is null';//которые не имеют направления
		$select = '';
		// сортировка по кабинету вакцинации
		if(!empty($data['MedService_id'])){
			$filter .= ' and SSL.MedService_id = :MedService_id';
			$select .= " , SSL.MedService_id as \"MedService_id\" ";
		}


		$query = "
			SELECT DISTINCT
				EPV.EvnPrescrVaccination_id as \"EvnPrescrVaccination_id\"
				, EPVD.EvnVaccination_id as \"EvnVaccination_id\"
				, EV.Prep_id as \"Prep_id\"
				, EV.Prep_Dose as \"Prep_Dose\"
				-- торговое наименование
					, TN.NAME as \"TN_NAME\"
				-- end торговое наименование
				-- наименование типа вакцинации
				, EVDR.VaccinationType_id as \"VaccinationType_id\"
				, VT.VaccinationType_Name as \"VaccinationType_Name\"
				-- наименование типа вакцинации
				-- данные по согласию пациента
				, VACPER.VaccionationPermission_id as \"VaccionationPermission_id\"
				, VACPER.VaccionationPermission_insDT as \"VaccionationPermission_insDT\"
				-- end данные по согласию пациента
				-- регистр остатков
				, DORL.DrugOstatRegistry_id as \"DrugOstatRegistry_id\"
					-- документы учета
					, DUCH.DocumentUcStr_id as \"DocumentUcStr_id\"
				 	, DUCH.DocumentUc_id as \"DocumentUc_id\"
					-- end документы учета
				-- end регистр остатков
				-- склад остатка
				, DOR.Storage_id as \"Storage_id\"
				-- end склад остатка
				-- кабинет вакцинаци
					{$select}
				-- end кабинет вакцинаци
				-- данные по ЛПУ
				, ED.LpuSection_id as \"LpuSection_id\"
				, ED.Mol_id as \"Mol_id\"
				, ED.EvnDrug_id as \"EvnDrug_id\"
				-- end данные по ЛПУ
				, Drug.DrugPrepFas_id as \"DrugPrepFas_id\"
				-- направление
				, EV.EvnDirection_vid as \"EvnDirection_vid\"
				, EV.PersonEvn_id as \"PersonEvn_id\"
				-- end

			FROM dbo.v_EvnPrescrVaccination EPV
				JOIN dbo.v_EvnPrescr EP ON EP.evnprescr_id = EPV.EvnPrescrVaccination_id
				LEFT JOIN dbo.v_EvnPrescrVaccinationDrug EPVD ON EPVD.EvnPrescrVaccination_id = EP.evnprescr_id
				LEFT JOIN dbo.v_EvnVaccination EV  ON EV.EvnVaccination_id = EPVD.EvnVaccination_id
				-- торговое наименование
					LEFT JOIN rls.v_PREP PR ON PR.Prep_id = EV.Prep_id
					-- само наименование
					LEFT JOIN rls.v_TRADENAMES TN ON TN.TRADENAMES_ID = PR.TRADENAMEID
					-- end само наименование
				-- end торговое наименование
				-- наименование типа вакцинации
					LEFT JOIN dbo.v_EvnVaccinationDrugReserve EVDR ON EVDR.EvnVaccination_id = EPVD.EvnVaccination_id
					LEFT JOIN vc.VaccinationType VT ON VT.VaccinationType_id = EVDR.VaccinationType_id
				-- end наименование типа вакцинации
				-- данные по согласию пациента
					LEFT JOIN vc.v_VaccionationPermission VACPER ON VACPER.EvnVaccination_id = EPVD.EvnVaccination_id
				-- end данные по согласию пациента
				-- резерв
					LEFT JOIN dbo.v_DrugOstatRegistryLink DORL ON DORL.DrugOstatRegistryLink_id = EVDR.DrugOstatRegistryLink_id
				-- end резерв
				-- склад резерва
					LEFT JOIN dbo.v_DrugOstatRegistry DOR ON DOR.DrugOstatRegistry_id = DORL.DrugOstatRegistry_id
				-- end склад резерва
				-- кабинет вакцинаци
					LEFT JOIN dbo.v_StorageStructLevel SSL ON SSL.Storage_id = DOR.Storage_id
				-- end кабинет вакцинаци
				-- документы учета
				LEFT JOIN dbo.v_DocumentUcStr DUCH ON DUCH.DocumentUcStr_id = DORL.DrugOstatRegistryLink_TableID
				-- end документы учета
				-- данные по ЛПУ
					LEFT JOIN dbo.v_EvnDrug ED ON ED.DocumentUcStr_id = DORL.DrugOstatRegistryLink_TableID
				-- end данные по ЛПУ
				-- данные по препарату
					INNER JOIN rls.v_Drug Drug on Drug.Drug_id = EV.Prep_id
				-- end данные по препарату
			WHERE
				-- where
					1=1
					and EP.EvnClass_id = (
							SELECT
								EvnClass_id
							FROM
								dbo.EvnClass
							WHERE EvnClass_SysNick = 'EvnPrescrVaccination')
					and EP.Person_id = :Person_id
					and EP.PersonEvn_id = :PersonEvn_id
					-- Проведение вакцинации не пустое 
					and EPVD.EvnVaccination_id IS NOT NULL
					-- end Проведение вакцинации не пустое 
					{$filter}
				-- end where
		";


		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");

	}


	/**
	 * Сохранение назначений вакцинаций пациенту
	 * 
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return type
	 * @throws Exception
	 */


	public function doSaveEvnCourseVaccination($data, $isAllowTransaction = true) {

		
		$this->beginTransaction();
		try {
			if (empty($data['EvnPrescr_pid'])) {
				throw new Exception('Не указан Идентификатор родительского события', 400);
			}
			if (empty($data['PersonEvn_id'])) {
				throw new Exception('Не указан Идентификатор состояния человека', 400);
			}
			if (!isset($data['Server_id'])) {
				throw new Exception('Не указан Идентификатор сервера', 400);
			}

			$vacinationEnable_inGroup_list = json_decode($data['vacinationEnable_inGroup'], true);

			// получение ссылки на зарезервированную, при создании строки документа учета, вакцину
			$Reserved_DrugOstatRegistryLink_id = $this->_getReserved_DrugOstatRegistryLink_id([
				'EvnDrug_id' => $data['EvnDrug_id']
			]);

			$VaccinationTypesPrescr = array(); // записи по каждому типу вакцинации


			// назначение под каждый тип вакцинации
			foreach ($vacinationEnable_inGroup_list as $value) {
				$VaccinationType_id = $value['VaccinationType_id'];

				$EPV_id = $this->_saveEvnPrescrVaccination($data); // запись в таблицу EvnPrescrVaccination
	
				
				// запись в таблицу EvnVaccination
				$EvnVaccination_id = $this->_saveEvnVaccination([
					'Lpu_id' => $data['Lpu_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'Server_id' => $data['Server_id'],
					'Prep_ID' => $data['Prep_ID'],
					'Prep_Dose' => $data['Prep_Dose'],
					'pmUser_id' => $data['pmUser_id']
				]);


				// резервирование Вакцины под вакцинацию
				$EvnVaccination_drugReserve_id = $this->_saveEvnVaccination_drugReserve([
					'EvnVaccination_id' => $EvnVaccination_id,
					'DrugOstatRegistryLink_id' => $Reserved_DrugOstatRegistryLink_id, 
					'VaccinationType_id' => $VaccinationType_id,
					'pmUser_id' => $data['pmUser_id'],
				]);  

				// сзакрепление зарезервированной вакцины за вакцинацией
				$EPVD_id  = $this->_saveEvnPrescrVaccinationDrug([
					'EPV_id' => $EPV_id,
					'pmUser_id' => $data['pmUser_id'],
					'EvnVaccination_id' => $EvnVaccination_id,
				]);

				$VaccinationTypesPrescr[] = [
					'VaccinationType_id' => $VaccinationType_id,
					'EvnVaccination_id' => $EvnVaccination_id,
					'EvnPrescrVaccination_id' => $EPV_id,
					'EvnVaccination_drugReserve_id' => $EvnVaccination_drugReserve_id,
					'EvnPrescrVaccinationDrug_id' => $EPVD_id
				];
			}

		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
					'Error_Msg' => $e->getMessage(),
					'Error_Code' => $e->getCode(),
					));
		}
		$this->commitTransaction();

		return array(array(
				'VaccinationTypesPrescr' => $VaccinationTypesPrescr,
				'Error_Msg' => null,
				'Error_Code' => null,
				));
				
	}


	/**
	 * _getReserved_DrugOstatRegistryLink_id - получение ссылки на зарезервированную вакцину
	 */
	protected function _getReserved_DrugOstatRegistryLink_id($data){
		$query = "
			select
					ED.documentucstr_id as \"DocumentUcStr_id\"
					, DORL.drugostatregistrylink_id as \"Reserved_DrugOstatRegistryLink_id\"
					, DORL.drugostatregistry_id as \"DrugOstatRegistry_id\"
			FROM  dbo.v_evndrug ED
					left join dbo.drugostatregistrylink DORL ON DORL.drugostatregistrylink_tableid = ED.documentucstr_id
			WHERE ED.evndrug_id = :EvnDrug_id
		";
		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['Reserved_DrugOstatRegistryLink_id'];
		} else {
			throw new Exception('Не обнаружена ссылка на зарезервированную вакцину ', 500);
		}
	}

	/**
	 * Получение строки документа учета
	 */

	protected function _getDocumentUcStr_id($data) {
		$query = "
		SELECT
			documentucstr_id as \"DocumentUcStr_id\"
		FROM dbo.v_EvnDrug
		WHERE EvnDrug_id = :EvnDrug_id
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['DocumentUcStr_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}

	/**
	 * Сохранение направления
	 */
	public function saveVacinationDirection ($data) {
		$VacinationDirection = json_decode($data['VacinationDirection'], true);
		$allResults = array();


		$this->beginTransaction();
			try {

				// получение актуального PersonEvn_id 
				$PersonEvn_id = $this->_getLastPersonEvn_id([
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
				]);
				// генерация номера направления
				$this->load->model('EvnDirection_model', 'EvnDirection_model');
				$EvnDirectionNum = $this->EvnDirection_model->getEvnDirectionNumber(array('Lpu_id' => $data['Lpu_id']))[0]['EvnDirection_Num'];

				// создание направления
				$EvnDirection_id = $this->_saveEvnDirection([
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $PersonEvn_id,
					'EvnDirection_Num' => $EvnDirectionNum,
					'pmUser_id' => $data['pmUser_id'],
					'MedPersonal_id' => $data['MedPersonal_id'],
					'Lpu_did' => $data['Lpu_did'],
					'LpuSection_did' => $data['LpuSection_did'],
					'LpuUnit_did' => $data['LpuUnit_did'],
					'LpuUnitType_id' => $data['LpuUnitType_id'],
					'MedService_id' => $data['MedService_id'],
				]);

				// сохранение направления 
				foreach ($VacinationDirection as $value) {
					
					// добавление направления в EvnVaccination
					$updatedEvnVaccination_id = $this->_updateEvnVaccination([
						'EvnVaccination_id' => $value['EvnVaccination_id'],	
						'Lpu_id' => $data['Lpu_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvn_id' => $PersonEvn_id,
						'EvnDirection_vid' => $EvnDirection_id,
						'Prep_id' => $value['Prep_id'],
						'Prep_Dose' => $value['Prep_Dose'],
						'Person_id' => $data['Person_id'],
						'Server_id' => $data['Server_id'],
					]);

					$allResults[] = [
						'EvnVaccination_id' => $value['EvnVaccination_id'],
						'EvnDirection_id' => $EvnDirection_id,
						'updatedEvnVaccination_id' => $updatedEvnVaccination_id
					];
					
				}



			} catch (Exception $e) {

				$this->rollbackTransaction();

				return array(array(
						'Error_Msg' => $e->getMessage(),
						'Error_Code' => $e->getCode(),
						));
			}

		$this->commitTransaction();

		return array(array(
			'allResults' => $allResults,
			'Error_Msg' => null,
			'Error_Code' => null,
			));


	}

	/**
	 * _getLastPersonEvn_id - получение актуального PersonEvn_id
	 * 
	 * @param int Person_id
	 * @param int Server_id
	 * 
	 * @return int PersonEvn_id
	 */
	protected function _getLastPersonEvn_id ($data) {

		$query = "
			SELECT
				personevn_id as \"PersonEvn_id\"
			FROM
				dbo.v_personevn
			WHERE 
				person_id = :Person_id
				and server_id = :Server_id
				-- последняя запись	
				and personevn_upddt = (
										SELECT 
											max(PE.personevn_upddt) 
										FROM dbo.v_personevn PE 
										WHERE 
											PE.person_id = :Person_id
											and PE.server_id = :Server_id
										)
				-- end последняя запись	
		";
		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['PersonEvn_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}


	/**
	 * Сохранение направления в событии вакцинации
	 */
	protected function _updateEvnVaccination ($data) {

		$query = "
			select
				evnvaccination_id as \"EvnVaccination_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_EvnVaccination_upd(
				evnvaccination_id := :EvnVaccination_id,
				evndirection_vid := :EvnDirection_vid,
				personevn_id := :PersonEvn_id,
				prep_id := :Prep_id,
				prep_dose := :Prep_Dose,
				lpu_id := :Lpu_id,
				pmuser_id := :pmUser_id,
				server_id := :Server_id
			);
		";


		$queryParams = array(
			'EvnVaccination_id' => $data['EvnVaccination_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnDirection_vid' => $data['EvnDirection_vid'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Prep_id' => $data['Prep_id'],
			'Prep_Dose' => $data['Prep_Dose'],
			'Server_id' => $data['Server_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response;
		} else {
			throw new Exception('Ошибка при направлении в кабинет вакцинации', 500);
		}
	}


	/**
	 * получение регистра остатков вакцины (не резерв)
	 * 
	 * Prep_ID
	 * Storage_id
	 * SubAccountType_id = 1 параметр установлен по умаолчанию, - записи с типом остатки
	 */
	protected function _getDrugOstatRegistry_id($data) {
		$query = "
			SELECT 
				DROST.drugostatregistry_insdt as \"DrugOstatRegistry_insDT\"
				, DROST.drug_id  as \"Drug_id\"
				, DROST.drugostatregistry_kolvo  as \"DrugOstatRegistry_Kolvo\"
				, DROST.drugostatregistry_id  as \"DrugOstatRegistry_id\"
			
			FROM 
				dbo.v_drugostatregistry DROST
			WHERE
				1=1
				AND DROST.subaccounttype_id = 1  
				AND DROST.drugostatregistry_insdt = (
					SELECT MIN(drugostatregistry_insdt) 
					FROM dbo.v_drugostatregistry 
					WHERE 1=1
					 and subaccounttype_id = 1 
					 and drug_id = :Prep_ID
					 and storage_id = :Storage_id)
				AND DROST.drug_id = :Prep_ID
				AND DROST.storage_id = :Storage_id
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['DrugOstatRegistry_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}

	/**
	 * _saveEvnDirection - сохранение направления
	 * 
	 * @DirType_id - тип направления (В кабинет вакцинации)
	 */
	protected function _saveEvnDirection($data){

		$query = "
			select
				evndirection_id as \"EvnDirection_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evndirection_ins(
				lpu_id := :Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				evndirection_setdt := dbo.tzGetDate(),
				evndirection_num := :EvnDirection_Num,
				medpersonal_id :=:MedPersonal_id,
				dirtype_id := 32,
				lpu_did := :Lpu_did,
				lpusection_did := :LpuSection_did,
				lpuunit_did := :LpuUnit_did,
				lpuunittype_id := :LpuUnitType_id,
				medservice_id := :MedService_id,
				pmuser_id := :pmUser_id
			);
		";

				
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' =>  $data['MedPersonal_id'],
			'Lpu_did' => $data['Lpu_did'],
			'LpuSection_did' => $data['LpuSection_did'],
			'LpuUnit_did' => $data['LpuUnit_did'],
			'LpuUnitType_id' => $data['LpuUnitType_id'],
			'MedService_id' => $data['MedService_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit(); 
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnDirection_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД при сохранении назначения вакцинации', 500);
		}

	}

	/**
	 * _getVaccinationEvnClass - получение id класса события вакцинации
	 */
	protected function _getVaccinationEvnClass_id(){
		$data['EvnClass_SysNick'] = 'EvnPrescrVaccination';
		$query = "
			SELECT
				EvnClass_id
			FROM
				dbo.EvnClass
			WHERE
			-- where
				1=1
				and EvnClass_SysNick = :EvnClass_SysNick
			-- end where
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnClass_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}


	/**
	 * 	_saveVaccineReserv - резервирование вакцины в остатках
	 * DrugOstatRegistry_id
	 * Prep_Dose
	 * pmUser_id
	 */
	protected function _saveVaccineReserv($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');
		$result = $this->DocumentUc_model->moveToReserve(array(
			'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
			'DrugOstatRegistry_Kolvo' => $data['Prep_Dose'], //null,
			'DocumentUcStr_id' => $data['DocumentUcStr_id'],
			'pmUser_id' => $data['pmUser_id'],
			'prescrVaccination' => true, // запрос для возврата результата записи
			// 'debug' => true
		));
		if (!empty($result['Error_Msg'])) {
			throw new Exception($result['Error_Msg'], 500);
		}
		return $result;
	}

	/**
	 * Получение DrugOstatRegistryLink_id после записи
	 */
	protected function _getDrugOstatRegistryLink($data) {
		$query = "
			SELECT
				drugostatregistrylink_id as \"DrugOstatRegistryLink_id\"
			FROM
				dbo.v_drugostatregistrylink
			WHERE
			-- where
				1=1
				and drugostatregistrylink_insdt=(
					SELECT MAX(drugostatregistrylink_insdt) FROM dbo.v_drugostatregistrylink WHERE drugostatregistry_id = :DrugOstatRegistry_id and pmuser_insid = :pmUser_id)
				and drugostatregistry_id = :DrugOstatRegistry_id
				and drugostatregistrylink_tablename = 'DocumentUcStr'
				and drugostatregistrylink_tableid = :DocumentUcStr_id
				and pmuser_insid = :pmUser_id
			-- end where
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['DrugOstatRegistryLink_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}

	/**
	 * _saveEvnVaccination_drugReserve Резервирование вакцины под вакцинацию
	 * @param int EvnVaccination_id
	 * @param int DrugOstatRegistryLink_id
	 * @param int VaccinationType_id
	 * @param int pmUser_id
	 * 
	 * @return int EvnVaccinationDrugReserve_id
	 * 
	 */
	protected function _saveEvnVaccination_drugReserve($data) {

		$query = "
			select
				evnvaccinationdrugreserve_id as \"EvnVaccinationDrugReserve_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnvaccinationdrugreserve_ins(
				evnvaccination_id := :EvnVaccination_id,
				vaccinationtype_id :=:VaccinationType_id,
				drugostatregistrylink_id :=:DrugOstatRegistryLink_id,
				pmuser_id := :pmUser_id
			);
		";
				
		$queryParams = array(
			'EvnVaccination_id' => $data['EvnVaccination_id'],
			'VaccinationType_id' => $data['VaccinationType_id'],
			'DrugOstatRegistryLink_id' => $data['DrugOstatRegistryLink_id'], 
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnVaccinationDrugReserve_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД при сохранении назначения вакцинации', 500);
		}


	}

	/**
	 * Сохранение назначения в EvnPrescrVaccination
	 */
	protected function _saveEvnPrescrVaccination($data) {
		$query = "
			select
				evnprescrvaccination_id as \"EvnPrescrVaccination_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnprescrvaccination_ins(
				prescriptiontype_id := 15,
				lpu_id :=:Lpu_id,
				server_id := :Server_id,
				personevn_id := :PersonEvn_id,
				prescriptionstatustype_id := :PrescriptionStatusType_id,
				evnprescrvaccination_iscito :=1,
				evnprescrvaccination_pid := :EvnPrescr_pid,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PrescriptionStatusType_id' => 1,
			'pmUser_id' => $data['pmUser_id'],
			'EvnPrescr_pid' => $data['EvnPrescr_pid']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnPrescrVaccination_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД при сохранении назначения вакцинации', 500);
		}
		
	}

	/**
	 * _saveEvnVaccination Сохранение вакцинации в списке вакцинаций
	 * 
	 * @param int Lpu_id
	 * @param int PersonEvn_id
	 * @param int Server_id
	 * @param int Prep_ID
	 * @param int Prep_Dose
	 * @param int pmUser_id
	 * @param int EvnDirection_vid
	 * 
	 * @return int EvnVaccination_id
	 */
	protected function _saveEvnVaccination($data) {
		$query = "
			select
				evnvaccination_id as \"EvnVaccination_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnvaccination_ins(
				lpu_id :=:Lpu_id,
				personevn_id := :PersonEvn_id,
				server_id := :Server_id,
				prep_id := :Prep_id,
				prep_dose := :Prep_Dose,
				pmuser_id := :pmUser_id
			);
		";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'Prep_id' => $data['Prep_ID'],
			'Prep_Dose' => $data['Prep_Dose'],
			'pmUser_id' => $data['pmUser_id'],
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnVaccination_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД при сохранении вакцинации', 500);
		}
		
	}


	
	/**
	 * Сохранение Вакцины назначения с типом Вакцинация
	 * @param array $data
	 */
	protected function _saveEvnPrescrVaccinationDrug($data) {

		$query = "
			select
			evnprescrvaccinationdrug_id as \"EvnPrescrVaccinationDrug_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnprescrvaccinationdrug_ins(
				evnvaccination_id := :EvnVaccination_id,
				evnprescrvaccination_id := :EvnPrescrVaccination_id,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnPrescrVaccination_id' => $data['EPV_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnVaccination_id' => $data['EvnVaccination_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['EvnPrescrVaccinationDrug_id'];
		} else {
			throw new Exception('Ошибка при запросе к БД при сохранении Вакцины для вакцинации', 500);
		}
	}


	/**
	 * doSaveEvnPrescrPermission - согласие на проведение вакцинации
	 * 
	 * @param int EvnVaccination_id - событие вакцинации (создано в момент резервирования, вакцинации еще не было)
	 * @param int|null Person_pid - законный представитель (null -если пациент сам подписывает)
	 */
	public function doSaveEvnPrescrPermission($data) {
		$this->beginTransaction();

		try {
			if($this->checkBeforeSave_EvnPrescrPermission($data)) {
				$VaccionationPermission_id = $this->_saveEvnPrescrPermission($data);
			}
				
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
					'Error_Msg' => $e->getMessage(),
					'Error_Code' => $e->getCode(),
					));
		}
		$this->commitTransaction();

		return array(array(
				'VaccionationPermission_id' => $VaccionationPermission_id,
				'Error_Msg' => null,
				'Error_Code' => null,
				));
	}


	/**
	 * doDeleteEvnPrescrPermission - удаление согласия на вакцинацию
	 */
	public function doDeleteEvnPrescrPermission ($data) {
		$this->beginTransaction();
		
		try {
			$deleteEvnPrescrPermission = $this->_deleteEvnPrescrPermission($data);
				
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
					'Error_Msg' => $e->getMessage(),
					'Error_Code' => $e->getCode(),
					));
		}
		$this->commitTransaction();

		return array(array(
				'Error_Msg' => null,
				'Error_Code' => null,
				));
	}

	/**
	 * Сохранение согласия
	 */
	protected function _saveEvnPrescrPermission ($data) {
		$query = "
			select
				vaccionationpermission_id as \"VaccionationPermission_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"

			from vc.p_vaccionationpermission_ins(
				evnvaccination_id := :EvnVaccination_id,
				person_pid := :Person_pid,
				medpersonal_id := :MedPersonal_id,
				pmuser_id := :pmUser_id
			)
		";


		$queryParams = array(
			'EvnVaccination_id' => $data['EvnVaccination_id'],
			'Person_pid' => $data['Person_pid'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['VaccionationPermission_id'];
		} else {
			throw new Exception('Ошибка при Согласия на вакцинацию', 500);
		}
	}

	/**
	 * Удаление согласия
	 */
	protected function _deleteEvnPrescrPermission ($data) {
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"

			from vc.p_vaccionationpermission_del(
					vaccionationpermission_id := :VaccionationPermission_id,
					pmuser_id := :pmUser_id
				)
		";

		$queryParams = array(
			'VaccionationPermission_id' => $data['VaccionationPermission_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при Согласия на вакцинацию', 500);
		}
	}

	/**
	 * checkBeforeSave_EvnPrescrPermission проверка на наличие согласия на вакцинацию
	 */
	public function checkBeforeSave_EvnPrescrPermission($data) {
		$query = "
			SELECT
				VaccionationPermission_id
			FROM
				vc.v_VaccionationPermission
			WHERE
			-- where
				EvnVaccination_id = :EvnVaccination_id
			-- end where
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['VaccionationPermission_id'])) {
				throw new Exception('По данному назначению уже имеется согласие', 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при запросе к БД ', 500);
		}
	}

	/**
	 * deleteEvnPrescrVaccination - удаление назначения
	 * 
	 * 
	 * перед удалением назначения проверяется наличие других связанных назначений,
	 * и в случае их отсутствия удаляется резерв вакцины
	 */
	public function deleteEvnPrescrVaccination ($data, $afterCancel = false){

		$removeVaccineReserv = null;
		$removeDocumentUcStr = null;

		// получение списка всех связанных назначений
		if($afterCancel){ // в случае отмены из панели "назначения и направления"
			$allEvnPrescrVaccinations = $this->_getAllEvnPrescrVaccinations_AfterCancel([
				'EvnVaccination_id'=>$data['EvnVaccination_id']
			]);
		}
		else {
			$allEvnPrescrVaccinations = $this->_getAllEvnPrescrVaccinations([
				'EvnPrescrVaccination_id'=>$data['EvnPrescrVaccination_id']
			]);
		}


		$this->beginTransaction();
		try {

			// удаление согласия на вакцинацию
			$deleteVaccinationPermission = $this->_deleteEvnPrescrPermission($data);

			// удаление в событии вакцинации ссылки на зарезервированную вакцину
			$deleteEvnVaccinationDrugReserve = $this->_deleteEvnVaccinationDrugReserve($data);

			// удаление события вакцинации
			$deleteEvnVaccination = $this->_deleteEvnVaccination($data);
			
			// удаление из назначения "Вакцины назначения с типом Вакцинация"
			$deleteEvnPrescrVaccinationDrug = $this->_delEvnPrescrVaccinationDrug($data);

			// удаление Назначения с типом Вакцинаци
			$deleteEvnPrescrVaccination = $this->_delEvnPrescrVaccination($data);
			
			// удаление назначения из общего списка назначений
			$deleteEvnPrescr = $this->_delEvnPrescr($data);

			// если назначение последнее , то резерв вакцины удаляется
			if(count($allEvnPrescrVaccinations) == 1) {
				// удаление вакцины из резерва
				$removeDocumentUcStr = $this->_removeDocumentUcStr($data);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array(
					'Error_Msg' => $e->getMessage(),
					'Error_Code' => $e->getCode(),
					));
		}
		$this->commitTransaction();

		

		return array(array(
				'data' => $data,
				'removeVaccineReserv' => $removeVaccineReserv,
				'removeDocumentUcStr' => $removeDocumentUcStr,
				'allEvnPrescrVaccinations_count' =>count($allEvnPrescrVaccinations),
				'deleteVaccinationPermission' => $deleteVaccinationPermission,
				'deleteEvnVaccinationDrugReserve' => $deleteEvnVaccinationDrugReserve,
				'deleteEvnVaccination' => $deleteEvnVaccination,
				'deleteEvnPrescrVaccinationDrug' => $deleteEvnPrescrVaccinationDrug,
				'deleteEvnPrescrVaccination' => $deleteEvnPrescrVaccination,
				'deleteEvnPrescr' => $deleteEvnPrescr,
				'Error_Msg' => null,
				'Error_Code' => null,
				));
	
	}



	/**
	 * 	_removeVaccineReserv - резервирование вакцины в остатках
	 * DrugOstatRegistry_id
	 * Prep_Dose
	 * pmUser_id
	 */
	protected function _removeVaccineReserv($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');
		$result = $this->DocumentUc_model->removeReserve(array(
			'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
			'DrugOstatRegistry_Kolvo' => $data['Prep_Dose'],
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DocumentUcStr_id' => $data['DocumentUcStr_id'],
			'pmUser_id' => $data['pmUser_id'],
			// 'debug' => true
		));
		if (!empty($result['Error_Msg'])) {
			throw new Exception($result['Error_Msg'], 500);
		}
		return true;
	}
	/**
	 * 	_removeDocumentUcStr - удаление строки документа учета
	 * 
	 * DocumentUcStr_id
	 * pmUser_id
	 */
	protected function _removeDocumentUcStr($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');
		$result = $this->DocumentUc_model->deleteDocumentUcStr(array(
			'DocumentUcStr_id' => $data['DocumentUcStr_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		if (!empty($result['Error_Msg'])) {
			throw new Exception($result['Error_Msg'], 500);
		}
		return $result;
	}

	protected function _getCurrentDrugOstatRegistryLink($data){
		$query = "
			SELECT DrugOstatRegistryLink_id
				FROM dbo.v_EvnVaccinationDrugReserve
			WHERE EvnVaccination_id = :EvnVaccination_id
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0]['DrugOstatRegistryLink_id'];
		} else {
			throw new Exception('Ошибка при удалении связи зарезервированных вакцин', 500);
		}
	}


	/**
	 * _deleteEvnVaccinationDrugReserve - удаление связи зарезервированных вакцин и проведений вакцинации
	 */
	protected function _deleteEvnVaccinationDrugReserve ($data) {
		//получение EvnVaccinationDrugReserve_id (для удаления) по EvnVaccination_id
		$query = " 
			SELECT
				evnvaccinationdrugreserve_id as \"EvnVaccinationDrugReserve_id\"
			FROM dbo.v_evnvaccinationdrugreserve
			WHERE evnvaccination_id = :EvnVaccination_id
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = $result->result('array');
		$EvnVaccinationDrugReserve_id = $response[0]['EvnVaccinationDrugReserve_id'];

		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evnvaccinationdrugreserve_del(
				evnvaccinationdrugreserve_id := :EvnVaccinationDrugReserve_id
			);
		";

		$queryParams = array(
			'EvnVaccinationDrugReserve_id' => $EvnVaccinationDrugReserve_id,
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при удалении связи зарезервированных вакцин', 500);
		}

	}

	/**
	 * _deleteEvnVaccination - удаление события вакцинации
	 */
	protected function _deleteEvnVaccination($data){
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evnvaccination_del(
				evnvaccination_id := :EvnVaccination_id,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnVaccination_id' => $data['EvnVaccination_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при удалении назначенной вакцинации', 500);
		}
	}

	/**
	 * удаление из назначения "Вакцины назначения с типом Вакцинация"
	 */
	protected function _delEvnPrescrVaccinationDrug($data) {
		$query = " 
			SELECT 
				evnprescrvaccinationdrug_id as \"EvnPrescrVaccinationDrug_id\"
			FROM dbo.v_evnprescrvaccinationdrug
			WHERE evnvaccination_id = :EvnVaccination_id
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = $result->result('array');
		$EvnPrescrVaccinationDrug_id = $response[0]['EvnPrescrVaccinationDrug_id'];

		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evnprescrvaccinationdrug_del(
				evnprescrvaccinationdrug_id := :EvnPrescrVaccinationDrug_id,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnPrescrVaccinationDrug_id' => $EvnPrescrVaccinationDrug_id,
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при удалении из назначения Вакцины назначения с типом Вакцинация', 500);
		}
		
	}

	/**
	 * _delEvnPrescrVaccination - удаление Назначения с типом Вакцинация
	 */
	protected function _delEvnPrescrVaccination($data) {
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evnprescrvaccination_del(
				evnprescrvaccination_id := :EvnPrescrVaccination_id,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnPrescrVaccination_id' => $data['EvnPrescrVaccination_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при удалении Назначения с типом Вакцинация', 500);
		}

	}

	/**
	 * _delEvnPrescr - Удаление назначения из общего списка назначений
	 */
	protected function _delEvnPrescr($data) {

		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from dbo.p_evnprescr_del(
				evnprescr_id := :EvnPrescr_id,
				pmuser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescrVaccination_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return true;
		} else {
			throw new Exception('Ошибка при удалении Назначения из списка назначений', 500);
		}
	}

	/**
	 * _getAllEvnPrescrVaccinations_AfterCancel - получение всех связанных назначений после отмены из панели
	 * 
	 * @param int EvnVaccination_id
	 */
	protected function _getAllEvnPrescrVaccinations_AfterCancel ($data) {
		$query = "
		SELECT
			P_EVDR.drugostatregistrylink_id as \"DrugOstatRegistryLink_id\",
			P_EVDR.evnvaccination_id as \"EvnVaccination_id\",
			S_EPVD.evnprescrvaccination_id as \"EvnPrescrVaccination_id\"
		from dbo.v_evnvaccinationdrugreserve F_EVDR
			LEFT JOIN dbo.v_evnvaccinationdrugreserve P_EVDR ON P_EVDR.drugostatregistrylink_id = F_EVDR.drugostatregistrylink_id
			-- связанные по вакцине
			LEFT JOIN dbo.v_evnprescrvaccinationdrug S_EPVD ON S_EPVD.evnvaccination_id = P_EVDR.evnvaccination_id
			LEFT JOIN dbo.v_evnprescr S_EP ON S_EP.evnprescr_id = S_EPVD.evnprescrvaccination_id
			-- END связанные по вакцине
		where
			F_EVDR.evnvaccination_id = :EvnVaccination_id
		";

		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response;
		} else {
			throw new Exception('Ошибка при удалении назначения вакцинации', 500);
		}

	}



	/**
	 * _getAllEvnPrescrVaccinations - получение всех связанных назначений 
	 * 
	 * @param int EvnPrescrVaccination_id
	 */
	protected function _getAllEvnPrescrVaccinations ($data) {
		$query = "
			SELECT
				P_EVDR.drugostatregistrylink_id as \"DrugOstatRegistryLink_id\",
				P_EVDR.evnvaccination_id as \"EvnVaccination_id\",
				S_EPVD.evnprescrvaccination_id as \"EvnPrescrVaccination_id\"
			FROM dbo.v_evnprescrvaccination F_EPV
					-- исходное
					LEFT JOIN dbo.v_evnprescr F_EP ON F_EP.evnprescr_id = F_EPV.evnprescrvaccination_id
					LEFT JOIN dbo.v_evnprescrvaccinationdrug F_EPVD ON F_EPVD.evnprescrvaccination_id = F_EP.evnprescr_id
					LEFT JOIN dbo.v_evnvaccinationdrugreserve F_EVDR ON F_EVDR.evnvaccination_id = F_EPVD.evnvaccination_id
				-- END исходное
					LEFT JOIN dbo.v_evnvaccinationdrugreserve P_EVDR ON P_EVDR.drugostatregistrylink_id = F_EVDR.drugostatregistrylink_id
				-- связанные по вакцине
					LEFT JOIN dbo.v_evnprescrvaccinationdrug S_EPVD ON S_EPVD.evnvaccination_id = P_EVDR.evnvaccination_id
					LEFT JOIN dbo.v_evnprescr S_EP ON S_EP.evnprescr_id = S_EPVD.evnprescrvaccination_id
				-- END связанные по вакцине
			WHERE F_EPV.evnprescrvaccination_id = :EvnPrescrVaccination_id
		";

		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response;
		} else {
			throw new Exception('Ошибка при удалении назначения вакцинации', 500);
		}


	}

		/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		

		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id AND COALESCE({$sysnick}.{$sysnick}_IsSigned,1) = 1 then 'edit' else 'view' end as \"accessType\"";
			$addJoin = "left join dbo.v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as accessType";
		}
		$query = "
			select
				{$accessType},
				EP.evnprescr_id as \"EvnPrescr_id\"
				,EP.evnprescr_id as \"EvnPrescr_pid\"
				,EP.evnprescr_rid as \"EvnPrescr_rid\"
				,EPV.evnprescrvaccination_setdate as \"EvnPrescr_setDate\"
				, EPV.evnprescrvaccination_isexec as \"EvnPrescr_IsExec\"
				,EPV.prescriptionstatustype_id as \"PrescriptionStatusType_id\"
				,EP.prescriptiontype_id as \"PrescriptionType_id\"
				,EP.prescriptiontype_id as \"PrescriptionType_Code\"
				, EPV.evnprescrvaccination_descr as \"EvnPrescr_Descr\"
				,EP.evnprescr_descr as \"EvnPrescr_MainDescr\"
				, EPV.evnprescrvaccination_id as \"EvnPrescrVaccination_id\"
				, EPV.evnprescrvaccination_count as \"EvnPrescrVaccination_Count\"
				, EV.prep_id as \"Prep_id\"
				, TN.name as \"TN_NAME\"
		   		-- наименование типа вакцинации
				, EVDR.vaccinationtype_id as \"VaccinationType_id\"
				, VT.vaccinationtype_name as \"VaccinationType_Name\"
		   		-- наименование типа вакцинации
				, EV.evndirection_vid as \"EvnDirection_vid\"
		   		-- дата и время исполнения
			   , EPV.evnprescrvaccination_diddt as \"EvnPrescrVaccination_didDT\"
		   		-- end дата и время исполнения

			from dbo.v_evnprescr EP
				inner join dbo.v_evnprescrvaccination EPV on EPV.evnprescrvaccination_id = EP.evnprescr_id
				-- препарат
				LEFT JOIN dbo.v_evnprescrvaccinationdrug EPVD ON EPVD.evnprescrvaccination_id = EP.evnprescr_id
				LEFT JOIN dbo.v_evnvaccination EV ON EV.evnvaccination_id = EPVD.evnvaccination_id
				LEFT JOIN rls.v_prep PR ON PR.prep_id = EV.prep_id
				LEFT JOIN rls.v_tradenames TN ON TN.tradenames_id = PR.tradenameid
				-- end препарат
				-- наименование типа вакцинации
				LEFT JOIN dbo.v_evnvaccinationdrugreserve EVDR ON EVDR.evnvaccination_id = EPVD.evnvaccination_id
				LEFT JOIN vc.vaccinationtype VT ON VT.vaccinationtype_id = EVDR.vaccinationtype_id
				-- end наименование типа вакцинации
				{$addJoin}
 			where
				EP.evnprescr_pid = :EvnPrescr_pid
				and EP.prescriptiontype_id = 15
				and EPV.prescriptionstatustype_id != 3
					-- имеет направление
				and EV.evndirection_vid is not null

			order by
				EP.evnprescr_id
				, EPV.evnprescrvaccination_setdate
		";

		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
		);
		// echo getDebugSql($query, $queryParams);exit;
		
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			$last_ep = null;
			$is_exe = null;
			$is_sign = null;
			$first_index = 0;
			foreach ($tmp_arr as $i => $row) {
				if ($last_ep != $row['EvnPrescr_id']) {
					//это первая итерация с другим назначением
					$first_index = $i;
					$last_ep = $row['EvnPrescr_id'];
					$is_exe = false;
					$is_sign = false;
				}
				if ($is_exe == false) $is_exe = ($row['EvnPrescr_IsExec'] == 2);
				if ($is_sign == false) $is_sign = ($row['PrescriptionStatusType_id'] == 2);
				if (empty($tmp_arr[$i+1]) || $last_ep != $tmp_arr[$i+1]['EvnPrescr_id']) {
					if ($is_exe) $row['EvnPrescr_IsExec'] = 2;
					if ($is_sign) $row['PrescriptionStatusType_id'] = 2;

					if (!empty($section) && $section === 'api') {
						$row['EvnPrescr_setDate'] = $tmp_arr[$first_index]['EvnPrescr_setDate'];
					} else {
						$row['EvnPrescr_setDate'] = $row['EvnPrescr_setDate'];
					}

					$row[$section . '_id'] = $row['EvnPrescr_id'].'-'.$row['EvnPrescrVaccination_id'];
					if ($section === "api" && empty($row['EvnPrescr_Descr']) && !empty($row['EvnPrescr_MainDescr'])) {
						$row['EvnPrescr_Descr'] = $row['EvnPrescr_MainDescr'];
						unset($row['EvnPrescr_MainDescr']);
					}

					$response[] = $row;
				}
			}
			return $response;
		} else {
			return false;
		}

	}

	/**
	 * Обработка после отмены назначения
	 */
	public function onAfterCancel($data)
	{
		$need_params = $this->_getonAfterCancel_Params($data);

		$params = [
			// получение списка всех связанных назначений
			'EvnPrescrVaccination_id' => $data['EvnPrescr_id'], 
			'VaccionationPermission_id' => $need_params['VaccionationPermission_id'],
			'EvnVaccination_id' =>$need_params['EvnVaccination_id'],
			'DocumentUcStr_id' => $need_params['DocumentUcStr_id'],
			'DocumentUc_id' => $need_params['DocumentUc_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id']
		];
		$this->deleteEvnPrescrVaccination($params, true);
	}


	/**
	 * _getonAfterCancel_Params - получение всех параметров назначения, для модели "после отмены"
	 * 
	 * @param int EvnPrescr_id
	 */
	protected function _getonAfterCancel_Params($data){
		$query = "
		select
			EPVD.evnvaccination_id as \"EvnVaccination_id\"
			, VPER.vaccionationpermission_id as \"VaccionationPermission_id\"
			, EVDR.drugostatregistrylink_id as \"DrugOstatRegistryLink_id\"
			-- документы учета
			, DUCH.documentucstr_id as \"DocumentUcStr_id\"
			, DUCH.documentuc_id as \"DocumentUc_id\"
			-- end документы учета
		from dbo.v_evnprescrvaccinationdrug EPVD
				left join vc.v_vaccionationpermission VPER on VPER.evnvaccination_id = EPVD.evnvaccination_id
				left join dbo.v_evnvaccinationdrugreserve EVDR on EVDR.evnvaccination_id = EPVD.evnvaccination_id
				LEFT JOIN dbo.v_drugostatregistrylink DORL ON DORL.drugostatregistrylink_id = EVDR.drugostatregistrylink_id
				-- документы учета
				LEFT JOIN dbo.v_documentucstr DUCH ON DUCH.documentucstr_id = DORL.drugostatregistrylink_tableid
				-- end документы учета
		where EPVD.evnprescrvaccination_id =  :EvnPrescr_id
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);

		// echo getDebugSql($query, $queryParams);exit;
		
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg'], 500);
			}
			return $response[0];
		} else {
			throw new Exception('Ошибка при удалении назначения вакцинации', 500);
		}
	}




}
 
