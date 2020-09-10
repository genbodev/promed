<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * @property Analyzer_model dbmodel
 * @OA\Tag(
 *     name="Analyzer",
 *     description="Анализаторы"
 * )
*/
class Analyzer extends SwREST_Controller {
	protected  $inputRules = array(
		'getAnalyzerRequests' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор анализатора', 'rules' => 'required', 'type' => 'id')
		),
		'saveAnalyzerField' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор анализатора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Analyzer_2wayComm', 'label' => 'Использование двусторонней связи', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_IsNotActive', 'label' => 'Неактивный', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_IsUseAutoReg', 'label' => 'Использование автоматического учёта', 'rules' => '', 'type' => 'int'),
		),
		'saveUslugaFromModelToAnalyzer' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'AnalyzerTest_pid', 'label' => 'Ид Исследования', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'UslugaComplex_id', 'label' => 'Ид Услуги', 'rules' => 'required', 'type' => 'int'),
		),
		'save' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_Name', 'label' => 'Наименование анализатора', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Analyzer_Code', 'label' => 'Код', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
			array('field' => 'equipment_id', 'label' => 'Анализатор ЛИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Test_JSON', 'label' => 'Тесты анализатора ЛИС', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_begDT', 'label' => 'Дата открытия', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Analyzer_endDT', 'label' => 'Дата закрытия', 'rules' => '', 'type' => 'date'),
			array('field' => 'Analyzer_LisClientId', 'label' => 'Id клиента', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisCompany', 'label' => 'Наименование ЛПУ', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisLab', 'label' => 'Наименование лаборатории', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisMachine', 'label' => 'Название машины в ЛИС', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisLogin', 'label' => 'Логин в ЛИС', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisPassword', 'label' => 'Пароль', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisNote', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_2wayComm', 'label' => 'Использование двусторонней связи', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Analyzer_IsUseAutoReg', 'label' => 'Использование автоматического учета', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Analyzer_IsNotActive', 'label' => 'Неактивный', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Analyzer_IsAutoOk', 'label' => 'Автоодобрение', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Analyzer_IsAutoGood','label' => 'Автоодобрение без патологий','rules' => '','type' => 'checkbox'),
			array('field' => 'AutoOkType','label' => 'Тип автоодобрения','rules' => '','type' => 'int')
		),
		'load' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
		),
		'loadList' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_Name', 'label' => 'Наименование анализатора', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_Code', 'label' => 'Код', 'rules' => '', 'type' => 'string'),
			array('field' => 'AnalyzerModel_id', 'label' => 'Модель анализатора', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
			array('field' => 'Analyzer_begDT', 'label' => 'Дата открытия', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'Analyzer_endDT', 'label' => 'Дата закрытия', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'Analyzer_LisClientId', 'label' => 'Id клиента', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisCompany', 'label' => 'Наименование ЛПУ', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisLab', 'label' => 'Наименование лаборатории', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisMachine', 'label' => 'Название машины в ЛИС', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisLogin', 'label' => 'Логин в ЛИС', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisPassword', 'label' => 'Пароль', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_LisNote', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_2wayComm', 'label' => 'Использование двусторонней связи', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_IsUseAutoReg', 'label' => 'Использование автоматического учета', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_IsNotActive', 'label' => 'Признак неактивности', 'rules' => '', 'type' => 'id'),
			array('field' => 'Analyzer_IsAutoOk', 'label' => 'Автоодобрение', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'Analyzer_IsAutoGood','label' => 'Автоодобрение без патологий','rules' => '','type' => 'checkbox'),
			array('field' => 'AutoOkType','label' => 'Тип автоодобрения','rules' => '','type' => 'int'),
			array('field' => 'EvnLabSample_id', 'label' => 'Проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabSamples', 'label' => 'Пробы', 'rules' => '', 'type' => 'string'),
			array('field' => 'hideRuchMetodiki', 'label' => 'Скрыть ручные методики', 'rules' => '', 'type' => 'checkbox'),
		),
		'delete' => array(
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'int'),
		),
		'addUslugaComplexMedServiceFromTest' => array(
			array('field' => 'UslugaComplexMedService_pid', 'label' => 'Идентификатор родительской услуги на службе', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'test_id', 'label' => 'Идентификатор теста', 'rules' => 'required', 'type' => 'id'),
		),
		'getAnalyzerCode' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('Analyzer_model', 'dbmodel');
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Analyzer",
	 *  	tags={"Analyzer"},
	 *	    summary="Сохранение данных анализатора",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_Name",
	 *     					description="Наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_Code",
	 *     					description="Код анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="equipment_id",
	 *     					description="Идентификатор анализатора ЛИС",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Test_JSON",
	 *     					description="Тесты анализатора ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_begDT",
	 *     					description="Дата открытия",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_endDT",
	 *     					description="Дата закрытия",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisClientId",
	 *     					description="ClientId",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisCompany",
	 *     					description="Наименование МО",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisLab",
	 *     					description="Наименование лаборатории",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisMachine",
	 *     					description="Название машины в ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisLogin",
	 *     					description="Логин в ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisPassword",
	 *     					description="Пароль",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisNote",
	 *     					description="Примечание",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_2wayComm",
	 *     					description="Использование двусторонней связи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsUseAutoReg",
	 *     					description="Использование автоматического учета",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsNotActive",
	 *     					description="Неактивный",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsAutoOk",
	 *     					description="Автоодобрение",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsAutoGood",
	 *     					description="Автоодобрение без патологий",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"Analyzer_Name",
	 *     					"Analyzer_Code",
	 *     					"Analyzer_begDT"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_post() {
		$data = $this->ProcessInputData('save', null, true);

		if (!empty($data['equipment_id'])) {
			// проверка наличия связи с другим анализатором
			if ($this->dbmodel->checkAnalyzerHasLinkAllready($data)) {
				$response = array(array(
					'success' => false,
					'Error_Msg' => 'Данный анализатор ЛИС уже связан с другим анализатором в системе промед'
				));
				$this->response(array(
					'error_code' => 0,
					'data' => $response
				));
			}
		}

		if ($data['Analyzer_id'] == 0)
			$data['Analyzer_id'] = null;

		if (empty($data['Analyzer_IsManualTechnic']) && empty($data['AnalyzerModel_id'])) {
			$this->response(array(
				'error_code' => 0,
				'data' => [
					'success' => false,
					'Error_Msg' => 'Не указана модель анализатора'
				]
			));
		}
		if (!empty($data['AnalyzerModel_id'])) {
			$this->load->model('AnalyzerModel_model');
			if ($this->AnalyzerModel_model->AModelHasType($data)) {
				$this->response(array(
					'error_code' => 0,
					'data' => [
						'success' => false,
						'Error_Msg' => 'У выбранной модели анализатора не заполнен тип оборудования! Обратитесь к Администратору системы.'
					]
				));
			}
		}
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($response['Error_Msg'])) {
			$this->response(array('error_code' => 0, 'data' => array($response['Error_Msg'])));
		}
		if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
			$this->response(array('error_code' => 0, 'data' => $response));
		}

		//убираем копирование услуг для анализаторов с автоучетом реактивов:
		if ( !(isset($data['Analyzer_IsUseAutoReg']) && $data['Analyzer_IsUseAutoReg']) ) {
			// добавляем из ЛИС -> копируем услуги (только при первом добавлении анализатора)
			if (empty($data['Analyzer_id']) && !empty($response[0]['Analyzer_id']) && !empty($data['equipment_id']) && !empty($data['MedService_id'])) {
				$data['Test_JSON'] = json_decode($data['Test_JSON'], true);
				if (is_array($data['Test_JSON']) && count($data['Test_JSON']) > 0) {
					// получаем все услуги связанные с анализатором в лис и сохраняем у нас + добавляем на службу
					$this->dbmodel->getAndSaveUslugaCodesForEquipment(array(
						'MedService_id' => $data['MedService_id'],
						'Analyzer_id' => $response[0]['Analyzer_id'],
						'Test_JSON' => $data['Test_JSON'],
						'equipment_id' => $data['equipment_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id'],
						'session' => $data['session']
					));
				}
			}

			// добавляем из Промед -> копируем услуги (только при первом добавлении анализатора)
			if (empty($data['Analyzer_id']) && !empty($response[0]['Analyzer_id']) && empty($data['equipment_id']) && !empty($data['AnalyzerModel_id']) && !empty($data['MedService_id'])) {
				// получаем все услуги связанные с модеью анализатора и сохраняем на анализатор + добавляем на службу
				$this->dbmodel->getAndSaveUslugaCodesForAnalyzerModel(array(
					'MedService_id' => $data['MedService_id'],
					'Analyzer_id' => $response[0]['Analyzer_id'],
					'AnalyzerModel_id' => $data['AnalyzerModel_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id'],
					'session' => $data['session']
				));
			}
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Analyzer",
	 *  	tags={"Analyzer"},
	 *	    summary="Получение данных анализатора",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('load', null, true);
		$response = $this->dbmodel->load($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Analyzer/list",
	 *  	tags={"Analyzer"},
	 *	    summary="Получение списка анализаторов",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_Name",
	 *     		in="query",
	 *     		description="Наименование анализатора",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_Code",
	 *     		in="query",
	 *     		description="Код анализатора",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="AnalyzerModel_id",
	 *     		in="query",
	 *     		description="Идентификатор модели анализатора",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_begDT",
	 *     		in="query",
	 *     		description="Дата открытия",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_endDT",
	 *     		in="query",
	 *     		description="Дата закрытия",
	 *     		@OA\Schema(type="string", format="date")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisClientId",
	 *     		in="query",
	 *     		description="ClientId",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisCompany",
	 *     		in="query",
	 *     		description="Наименование МО",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisLab",
	 *     		in="query",
	 *     		description="Наименование лаборатории",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisMachine",
	 *     		in="query",
	 *     		description="Название машины в ЛИС",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisLogin",
	 *     		in="query",
	 *     		description="Логин в ЛИС",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisPassword",
	 *     		in="query",
	 *     		description="Пароль",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_LisNote",
	 *     		in="query",
	 *     		description="Примечание",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_2wayComm",
	 *     		in="query",
	 *     		description="Использование двусторонней связи",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_IsUseAutoReg",
	 *     		in="query",
	 *     		description="Использование автоматического учета",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_IsNotActive",
	 *     		in="query",
	 *     		description="Признак неактивности",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_IsAutoOk",
	 *     		in="query",
	 *     		description="Автоодобрение",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="Analyzer_IsAutoGood",
	 *     		in="query",
	 *     		description="Автоодобрение без патологий",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="EvnLabSamples",
	 *     		in="query",
	 *     		description="Список идентификаторов проб",
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 *     	@OA\Parameter(
	 *     		name="hideRuchMetodiki",
	 *     		in="query",
	 *     		description="Скрыть ручные методики",
	 *     		@OA\Schema(type="integer")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function list_get() {
		$data = $this->ProcessInputData('loadList', null, true);
		$response = $this->dbmodel->loadList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     	path="/api/Analyzer",
	 *  	tags={"Analyzer"},
	 *	    summary="Удаление данных анализатора",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"Analyzer_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function index_delete() {
		$data = $this->ProcessInputData('delete', null, true);
		$response = $this->dbmodel->delete($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Analyzer/RequestCount",
	 *  	tags={"Analyzer"},
	 *	    summary="Проверка количества заявок для апдейта при изменении неактивности",
	 *     	@OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function RequestCount_get() {
		$data = $this->ProcessInputData('getAnalyzerRequests', null, true);
		$response = $this->dbmodel->getAnalyzerRequests($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     	path="/api/Analyzer/Field",
	 *  	tags={"Analyzer"},
	 *	    summary="Сохранение признака(активности, связи, учёта) анализатора",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_2wayComm",
	 *     					description="Признак использования двухсторонней связи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsNotActive",
	 *     					description="Признак неактивности",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsUseAutoReg",
	 *     					description="Признак Учёта",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"Analyzer_id",
	 *     					("Analyzer_2wayComm" || "Analyzer_IsNotActive" || Analyzer_IsUseAutoReg)
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function Field_patch() {
		$data = $this->ProcessInputData('saveAnalyzerField', null, true);
		$response = $this->dbmodel->saveAnalyzerField($data);
		if (!$response) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$response = array(array('success' => true));
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Analyzer/UslugaFromModel",
	 *  	tags={"Analyzer"},
	 *	    summary="Сохранение услуги на экземпляре анализатора",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerTest_pid",
	 *     					description="Идентификатор исследования",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"Analyzer_id",
	 *     					"AnalyzerModel_id",
	 *     					"UslugaComplex_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function UslugaFromModel_post() {
		$data = $this->ProcessInputData('saveUslugaFromModelToAnalyzer', null, true);
		$this->dbmodel->saveUslugaFromModelToAnalyzer($data);
		$response = array(array('success' => true));
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Analyzer/UslugaComplexMedServiceFromTest",
	 *  	tags={"Analyzer"},
	 *	    summary="Добавление услуги в состав на службу",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="UslugaComplexMedService_pid",
	 *     					description="Идентификатор родительской услуги на службе",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="test_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"UslugaComplexMedService_pid",
	 *     					"test_id"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function UslugaComplexMedServiceFromTest_post() {
		$data = $this->ProcessInputData('addUslugaComplexMedServiceFromTest', null, true);
		$response = $this->dbmodel->addUslugaComplexMedServiceFromTest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     	path="/api/Analyzer/Code/generate",
	 *  	tags={"Analyzer"},
	 *	    summary="Генерирует код анализатора",
	 *     	@OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function genCode_get(){
		$data = $this->ProcessInputData('getAnalyzerCode', null, true);
		$response = $this->dbmodel->getAnalyzerCode($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     	path="/api/Analyzer/saveRecord",
	 *  	tags={"Analyzer"},
	 *	    summary="Альтернативное сохранение без проверки на повтор",
	 *     	@OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_Name",
	 *     					description="Наименование анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_Code",
	 *     					description="Код анализатора",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="AnalyzerModel_id",
	 *     					description="Идентификатор модели анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="equipment_id",
	 *     					description="Идентификатор анализатора ЛИС",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Test_JSON",
	 *     					description="Тесты анализатора ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_begDT",
	 *     					description="Дата открытия",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_endDT",
	 *     					description="Дата закрытия",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisClientId",
	 *     					description="ClientId",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisCompany",
	 *     					description="Наименование МО",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisLab",
	 *     					description="Наименование лаборатории",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisMachine",
	 *     					description="Название машины в ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisLogin",
	 *     					description="Логин в ЛИС",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisPassword",
	 *     					description="Пароль",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_LisNote",
	 *     					description="Примечание",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_2wayComm",
	 *     					description="Использование двусторонней связи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsUseAutoReg",
	 *     					description="Использование автоматического учета",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsNotActive",
	 *     					description="Неактивный",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_IsAutoOk",
	 *     					description="Автоодобрение",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *     					"Analyzer_Name",
	 *     					"Analyzer_Code",
	 *     					"Analyzer_begDT"
	 * 					}
	 * 				)
	 * 			)
	 * 		),
	 *		@OA\Response(
	 *			response="200",
	 *			description="JSON response",
	 *			@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function saveRecord_post() {
		$data = $this->ProcessInputData('save', null, true);

		$data['flag'] = true;
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}
}