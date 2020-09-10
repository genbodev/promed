<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonCard - контроллер API для работы с прикреплениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class PersonCard extends SwREST_Controller {
	protected $inputRules = array(
		'getPersonAttach' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuAttachType_id', 'label' => 'Идентификатор типа прикрепления', 'rules' => '', 'type' => 'id'),
		),
		'getPersonAttachList' => array(
			array('field' => 'bdzID', 'label' => 'Идентификатор человека в ТФОМС', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuAttachType_id', 'label' => 'Тип прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonCardAttach', 'label' => 'Признак наличия заявления о прикреплении', 'rules' => 'zero', 'type' => 'api_flag_nc'),
			array('field' => 'begDate', 'label' => 'Начало периода', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'endDate', 'label' => 'Окончание периода', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'PageNum', 'label' => 'Запрашиваемая страница пакета', 'rules' => 'required', 'type' => 'id')
		),
		'getPersonLpuInfoIsAgree' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'createPersonCard' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Ид участка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuAttachType_id', 'label' => 'Ид типа прикрепления', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonAmbulatCard_id', 'label' => 'Ид амбулаторной карты', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonCard_Code', 'label' => 'Номер амбулаторной карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonCard_begDate', 'label' => 'Дата начала прикрепления', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'PersonCard_endDate', 'label' => 'Дата окончания прикрепления', 'rules' => '', 'type' => 'date'),
				array('field' => 'CardCloseCause_id', 'label' => 'Ид причины закрытия прикрепления', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonCard_IsAttachCondit', 'label' => 'Признак Условное прикрепление', 'rules' => '', 'type' => 'int'),
				array('field' => 'PersonCard_IsAttachAuto', 'label' => 'Признак Автоматическое прикрепление', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonCard_AttachAutoDT', 'label' => 'Дата автоматического прикрепления', 'rules' => '', 'type' => 'int'),
				array('field' => 'PersonCard_DmsPolisNum', 'label' => 'Номер полиса ДМС', 'rules' => '', 'type' => 'string'),
				array('field' => 'PersonCard_DmsBegDate', 'label' => 'Дата начала договора ДМС', 'rules' => '', 'type' => 'date'),
				array('field' => 'PersonCard_DmsEndDate', 'label' => 'Дата окончания договора ДМС', 'rules' => '', 'type' => 'date'),
				array('field' => 'OrgSMO_id', 'label' => 'Ид страховой медицинской организации ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuRegion_fapid', 'label' => 'Ид участка ФАП ', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Ид места работы врача ', 'rules' => '', 'type' => 'id'),
		),
		'updatePersonCard' => array(
			array('field' => 'PersonCard_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonAmbulatCard_id', 'label' => 'Ид амбулаторной карты', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonCard_endDate', 'label' => 'Дата окончания прикрепления', 'rules' => '', 'type' => 'date'),
			array('field' => 'CardCloseCause_id', 'label' => 'Ид причины закрытия прикрепления', 'rules' => '', 'type' => 'id'),
		),
		'deletePersonCard' => array(
			array('field' => 'PersonCard_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
		),
		'getPersonCard' => array(
			array('field' => 'Date_DT', 'label' => 'Дата (для получения списка прикрепелений на дату)', 'rules' => '', 'type' => 'date'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_id', 'label' => 'Ид участка', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_fapid', 'label' => 'Ид участка ФАП ', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuAttachType_id', 'label' => 'Ид типа прикрепления', 'rules' => 'required', 'type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Polka_PersonCard_model', 'dbmodel');
	}

	/**
	 * Получение данных об амбулаторных картах человека
	 */
	public function PersonAttach_get() {
		$data = $this->ProcessInputData('getPersonAttach');

		$resp = $this->dbmodel->getPersonAttach($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка изменений прикреплений пациентов за период
	 */
	public function PersonAttachList_get() {
		$data = $this->ProcessInputData('getPersonAttachList');

		$resp = $this->dbmodel->getPersonAttachList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp['data'],
			'ZAP' => $resp['ZAP']
		));
	}

	/**
	 * Получение согласия на обработку персональных данных
	 */
	public function PersonLpuInfo_IsAgree_get() {
		$data = $this->ProcessInputData('getPersonLpuInfoIsAgree');

		$resp = $this->dbmodel->getPersonLpuInfoIsAgree($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание прикрепления к МО
	 */
	function index_post() {
		$data = $this->ProcessInputData('createPersonCard', false, true);
		
		if(!isset($data['PersonCard_IsAttachCondit'])){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Отсутствует обязательный параметр PersonCard_IsAttachCondit'
			));
		}
		if(isset($data['PersonCard_IsAttachCondit']) && !in_array($data['PersonCard_IsAttachCondit'], array(0,1)) ){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'неверный параметр PersonCard_IsAttachCondit'
			));
		}
		if(isset($data['PersonCard_IsAttachAuto']) && !in_array($data['PersonCard_IsAttachAuto'], array(0,1)) ){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'неверный параметр PersonCard_IsAttachAuto'
			));
		}
		
		//возраст
		$agePerson = $this->dbmodel->getFirstResultFromQuery("
			SELECT top 1 dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as PersonAge
			FROM v_PersonState (nolock)
			WHERE Person_id = :Person_id
		", $data);
		if(!empty($agePerson)){
			$data['PersonAge'] = $agePerson;
		}else{
			$this->response(array(
				'error_code' => 6,
				'error_msg' => "Не удалось определить возраст!"
			));
		}
		//Проверим, существование у человека хотя бы одно активное основное прикрепление
		$resp_check = $this->dbmodel->checkAttachExists($data);
		// Проверка возможности прикрепления
		// тип ЛПУ по возрасту
		$lpuAgeType = $this->dbmodel->getLpuAgeType($data);
		// тип участка
		$lpuRegionType = $this->dbmodel->getLpuRegionType($data);
		if( $lpuAgeType === false || $lpuRegionType === false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => "Не удалось определить тип ЛПУ по возрасту!!"
			));
		}
		/*if($lpuAgeType != $lpuRegionType){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => "идентификатор типа участка не соответствует идентификатору типа участка в ЛПУ"
			));
		}*/
		$data['LpuRegionType_id'] = $lpuRegionType;
		$response = $this->dbmodel->checkAttachPosible($data);
		if ( $response !== true ) {
			$this->response(array(
				'error_code' => $response[0]['Error_Code'],
				'error_msg' => $response[0]['Error_Msg']
			));
		}	
	
		$this->dbmodel->beginTransaction();
		
		// поиск амбулаторной карты 
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);
		$filter = ' AND Person_id = :Person_id AND Lpu_id = :Lpu_id ';
		if(!empty($data['PersonAmbulatCard_id'])){
			$params['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
			$filter .= ' AND PersonAmbulatCard_id = :PersonAmbulatCard_id';
		}
		if(!empty($data['PersonCard_Code'])){
			$params['PersonCard_Code'] = $data['PersonCard_Code'];
			$filter .= ' AND PersonAmbulatCard_Num = :PersonCard_Code';
		}
		$query = "
			select top 1
				PersonAmbulatCard_Num,
				PersonAmbulatCard_id
			from
				v_PersonAmbulatCard with (nolock)
			where (1=1)
				{$filter}
			ORDER BY PersonAmbulatCard_id DESC
		";
		$result = $this->dbmodel->getFirstRowFromQuery($query, $params);
		
		if (is_array($result) && $result['PersonAmbulatCard_Num']) {
			//амбулаторная карта НЕ создается. При создании прикрепления подставляется номер найденной амбулаторпной карты 
			$data['PersonAmbulatCard_Num'] = $result['PersonAmbulatCard_Num'];
			$data['PersonAmbulatCard_id'] = $result['PersonAmbulatCard_id'];
			$data['PersonCard_Code'] = $result['PersonAmbulatCard_Num'];
		}else{
			//то создается новая амбулаторная карта 
			if(!empty($data['PersonCard_Code'])){
				// то Номер амбулаторной карты берется из параметра PersonCard_Code.
				$data['PersonAmbulatCard_Num'] = $data['PersonCard_Code'];
			}else{
				// Если параметр PersonCard_Code  НЕ заполнен, то Номер амбулаторной карты генерируется автоматически
				$data['PersonAmbulatCard_Num'] = $this->dbmodel->getFirstResultFromQuery("
					declare @PersonAmbulatCard_Num bigint = isnull((
						select top 1 max(cast(PersonAmbulatCard_Num as bigint))+1
						from v_PersonAmbulatCard with(nolock)
						where ISNUMERIC(PersonAmbulatCard_Num) = 1
						and Lpu_id = :Lpu_id
					), 1)
					select @PersonAmbulatCard_Num as PersonAmbulatCard_Num
				", array('Lpu_id' => $data['Lpu_id']));
			}
			$this->load->model('PersonAmbulatCard_model', 'PersonAmbulatCard_model');
			$resp = $this->PersonAmbulatCard_model->savePersonAmbulatCard($data);
			if (!empty($resp[0]['Error_Msg'])) {
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $resp[0]['Error_Msg']
				));
			}
			if(!is_array($resp) || empty($resp[0]['PersonAmbulatCard_id'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data['PersonAmbulatCard_id'] = $resp[0]['PersonAmbulatCard_id'];
			$data['PersonCard_Code'] = $data['PersonAmbulatCard_Num'];
		}
		// покончили с поиском амбулаторной карты	
		
		// Если параметр PersonCard_IsAttachCondit (признак «Условное прикрепление») = 0 (Нет), то создается Заявление на прикрепеление
		if(empty($data['PersonCard_IsAttachCondit'])){
			$resp = $this->dbmodel->savePersonCardAttach(array(
				'PersonCardAttach_id' => null
				,'PersonCardAttach_setDate' => $data['PersonCard_begDate']
				,'Lpu_id' => $data['Lpu_id']
				,'Lpu_aid' => $data['Lpu_id']
				,'Person_id' => $data['Person_id']
				,'Address_id' => null
				,'Polis_id' => null
				,'PersonCardAttach_IsSMS' => null
				,'PersonCardAttach_SMS' => null
				,'PersonCardAttach_IsEmail' => null
				,'PersonCardAttach_Email' => null
				,'PersonCardAttach_IsHimself' => null
				,'PersonAmbulatCard_id' => (!empty($data['PersonAmbulatCard_id'])) ? $data['PersonAmbulatCard_id'] : null
				,'pmUser_id' => $data['pmUser_id']
			));
			if( !is_array($resp) || !empty($resp[0]['Error_Msg']) ) {
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => ( !empty($resp[0]['Error_Msg']) ) ? $resp[0]['Error_Msg'] : 'Ошибка! Не удалось сохранить заявление!'
				));
			}
			$data['PersonCardAttach_id'] = $resp[0]['PersonCardAttach_id'];
		}
		// покончили с заявлениями
		
		//-------------- создаем прикрепление
		$data['action'] = 'add';
		if(isset($data['PersonCard_IsAttachAuto'])){
			$data['PersonCard_IsAttachAuto'] = ($data['PersonCard_IsAttachAuto'] == 1) ? 2 : 1;
		}
		if(isset($data['PersonCard_IsAttachCondit'])){
			$data['PersonCard_IsAttachCondit'] = ($data['PersonCard_IsAttachCondit'] == 1) ? 2 : 1;
		}
		if(!empty($data['PersonCard_IsAttachCondit'])) {
			$data['setIsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
		}
		$response = $this->dbmodel->savePersonCard($data, true);
		if( !is_array($response) || ( isset($response[0]) && !empty($response[0]['Error_Msg']) ) ) {
			$this->dbmodel->rollbackTransaction();
			$this->response(array(
				'error_code' => $response[0]['Error_Code'],
				'error_msg' => $response[0]['Error_Msg']
			));
		}else{
			$this->dbmodel->commitTransaction();
			$this->response(array(
				'error_code' => 0,
				'PersonCard_id' => $response[0]['PersonCard_id'],
				'PersonCardAttach_id' => (empty($data['PersonCardAttach_id'])) ? null : $data['PersonCardAttach_id']
			));
		}
	}
	
	/**
	 * Изменение прикрепления к МО
	 */
	function index_put() {
		$data = $this->ProcessInputData('updatePersonCard', false, true);
		$data['action'] = 'edit';
		
		if(empty($data['PersonAmbulatCard_id']) && empty($data['PersonCard_endDate']) && empty($data['CardCloseCause_id']) ){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Отсутствуют параметры для редактирования'
			));
		}
		
		$sql = "
			SELECT top 1
				pc.PersonCard_id,
				pc.Server_id,
				pc.Lpu_id,
                pc.Person_id,
                convert(varchar,cast(pc.PersonCard_begDate as datetime),102) as PersonCard_begDate,
				convert(varchar,cast(pc.PersonCard_endDate as datetime),102) as PersonCard_endDate,
                pc.PersonCard_Code,
				pc.PersonCard_IsAttachCondit,
				pc.LpuRegionType_id,
                pc.LpuRegion_id,
                pc.MedStaffFact_id,
                pc.LpuRegion_Fapid,
				pc.LpuAttachType_id,
                pc.CardCloseCause_id,
				pc.PersonCardAttach_id
			FROM v_PersonCard_all pc with (nolock)
			WHERE
				pc.PersonCard_id = ?;";
		$res = $this->dbmodel->getFirstRowFromQuery($sql, array($data['PersonCard_id']));
		if(empty($res['PersonCard_id'])){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не удалось проверить наличие уже существующего прикрепления',
			));
		}
		
		if(!empty($data['PersonCard_endDate'])){
			$res['PersonCard_endDate'] = $data['PersonCard_endDate'];
		}
		if(!empty($data['CardCloseCause_id'])){
			$res['CardCloseCause_id'] = $data['CardCloseCause_id'];
		}
		if(!empty($data['PersonAmbulatCard_id'])){
			$res['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
		}
		$data = array_merge($data, $res);
		$result = $this->dbmodel->savePersonCard($data, true);
		
		if( isset($result[0]['Error_Code']) ){
			$this->response(array(
				'error_code' => $result[0]['Error_Code'],
				'error_msg' => $result[0]['Error_Msg'],
			));
		}else{
			$this->response(array(
				'error_code' => 0
			));
		}	
	}
	
	/**
	 * Удаление прикрепления к МО
	 */
	function index_delete() {
		$data = $this->ProcessInputData('deletePersonCard');
		$data = array_merge($data, getSessionParams());
		$sql = "
			SELECT top 1
				pc.PersonCardAttach_id,
				pc.PersonCard_endDate
			FROM v_PersonCard_all pc with (nolock)
			WHERE
				pc.PersonCard_id = ?;";
		$resCardAttach = $this->dbmodel->getFirstRowFromQuery($sql, array($data['PersonCard_id']));
		
		if(!empty($resCardAttach['PersonCard_endDate'])){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Закрытое прикрепление не может быть удалено'
			));
		}
		
		$response = $this->dbmodel->deleteAllPersonCardMedicalIntervent($data);
		if( !is_array($response) || !empty($response[0]['Error_Msg']) ) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Ошибка! Не удалось удалить отказ от видов медицинских вмешательств!'
			));
		}
		
		$resDel = $this->dbmodel->deletePersonCard($data, true);
		if(!$resDel){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'произошли ошибки при удалении прикрепления'
			));
		}
		if(isset($resDel[0]['Error_Msg'])){
			$this->response(array(
				'error_code' => $resDel[0]['Error_Code'],
				'error_msg' => $resDel[0]['Error_Msg']
			));
		}
		
		if(!empty($resCardAttach['PersonCardAttach_id'])){
			$resDelAtach = $this->dbmodel->deletePersonCardAttach($resCardAttach);
			if(!$resDelAtach){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'произошли ошибки при удалении записей заявления на прикрепеление'
				));
			}
			if(isset($resDelAtach[0]['Error_Msg'])){
				$this->response(array(
					'error_code' => $resDelAtach[0]['Error_Code'],
					'error_msg' => $resDelAtach[0]['Error_Msg']
				));
			}
		}
		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение списка прикреплений 
	 */
	function index_get() {
		$data = $this->ProcessInputData('getPersonCard');
		
		if(empty($data['Person_id']) && empty($data['Lpu_id']) && empty($data['LpuRegion_id'])){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Хотя бы один из параметров Person_id, Lpu_id, LpuRegion_id должен быть передан'
			));
		}
		
		$result = $this->dbmodel->getPersonCardAPI($data);
		$this->response(array(
			'error_code' => 0,
			'data' => $result
		));
	}
}