<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Post_model - модель для работы c должностями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.04.2016
 */

class Post_model extends swPgModel {

	public $inputRules = array(
		'PostByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'loadPostByCode' => array(
			array(
				'field' => 'Code',
				'label' => 'Код',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'loadPostByid' => array(
			array(
				'field' => 'Post_id',
				'label' => 'Идентификатор должности',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'createPost' => array(
			array('field' => 'Code', 'label' => 'Код', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Name', 'label' => 'Наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'FRMPPost_id', 'label' => 'Должность ФРМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'PostKind_id', 'label' => 'Вид должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ProfessionalGroup_id', 'label' => 'Профессиональная группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'TarifList', 'label' => 'Тарификационный лист', 'rules' => '', 'type' => 'int'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrimaryHealthCare', 'label' => 'Флаг "Первичное звено"', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'MaxPopulation', 'label' => 'Максимальная численность прикрепления', 'rules' => '', 'type' => 'int'),
			array('field' => 'Post_Nick', 'label' => 'Сокр.наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'begDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date')
		),
		'updatePost' => array(
			array('field' => 'Post_id', 'label' => 'Идентификатор должности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Code', 'label' => 'Код', 'rules' => '', 'type' => 'string'),
			array('field' => 'Name', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'FRMPPost_id', 'label' => 'Должность ФРМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'PostKind_id', 'label' => 'Вид должности', 'rules' => '', 'type' => 'id'),
			array('field' => 'ProfessionalGroup_id', 'label' => 'Профессиональная группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'TarifList', 'label' => 'Тарификационный лист', 'rules' => '', 'type' => 'int'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrimaryHealthCare', 'label' => 'Флаг "Первичное звено"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'MaxPopulation', 'label' => 'Максимальная численность прикрепления', 'rules' => '', 'type' => 'int'),
			array('field' => 'Post_Nick', 'label' => 'Сокр.наименование', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'begDT', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохранение должности
	 */
	function savePost($data) {
		$params = array(
			'Post_id' => !empty($data['Post_id'])?$data['Post_id']:null,
			'Post_Name' => $data['Post_Name'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => !empty($data['Org_id'])?$data['Org_id']:$data['Server_id']
		);

		if (empty($params['Post_id'])) {
			$procedure = 'p_Post_ins';
		} else {
			$procedure = 'p_Post_upd';
		}

		$query = "
			select Post_id as \"Post_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				Post_id := :Post_id,
				Post_Name := :Post_Name,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id);
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка должностей
	 */
	function loadPostGrid($data) {
        $filters = "(1=1)";
        $params = array();
        $top_n = "";

        if ($data['searchMode'] == 'all') {
            $top_n = "limit 100";
        } else {
            $filters .= " and P.Server_id in (0,:Server_id)";
            $params['Server_id'] = !empty($data['Org_id'])?$data['Org_id']:$data['Server_id'];
        }

		if (!empty($data['Post_Name'])) {
			$filters .= " and P.Post_Name iLIKE '%'||:Post_Name||'%'";

			$params['Post_Name'] = $data['Post_Name'];
		}

		$query = "
			select 
				P.Post_id as \"Post_id\",
				P.Post_Name as \"Post_Name\",
				P.Server_id as \"Server_id\"
			from
				v_Post P 

			where
				{$filters}
			order by
				P.Post_id
				{$top_n}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных должности для редактирования
	 */
	function loadPostForm($data) {
		$params = array('Post_id' => $data['Post_id']);
		$query = "
			select 
				P.Post_id as \"Post_id\",
				P.Post_Name as \"Post_Name\"
			from v_Post P 

			where P.Post_id = :Post_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка должностей
	 */
	function loadPostList($data) {
		$filter = "1=1";
		$params = array();
        $top_n = "";

		if (!empty($data['Post_id'])) {
			$filter .= " and P.Post_id = :Post_id";
			$params['Post_id'] = $data['Post_id'];
		} else {
            if ($data['searchMode'] == 'all') {
                $top_n = "limit 100";
            } else {
                $filter .= " and P.Server_id in (0, :Server_id)";
				$params['Server_id'] = !empty($data['Org_id'])?$data['Org_id']:$data['Server_id'];
            }

			if (!empty($data['query'])) {
				$filter .= " and P.Post_Name iLIKE :Post_Name||'%'";

				$params['Post_Name'] = $data['query'];
			}
		}

		$query = "
			select 
				P.Post_id as \"Post_id\",
				P.Post_Name as \"Post_Name\",
				P.Server_id as \"Server_id\"
			from
				v_Post P 

			where
				{$filter}
			{$top_n}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка PostKind
	 */
	function getPostKinds() {
		$params = array();
		
		$query = "
			select
				PK.id as \"id\",
				PK.name as \"name\",
				PK.code as \"code\"
			from persis.v_PostKind PK 

			order by
				PK.name
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получение списка специальностей для МО
	 */
	function getPostsForLpu($data) {
		$resp = $this->queryResult("
			select distinct
				Post_id as \"Post_id\"
			from
				v_MedStaffFact 

			where
				Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		return $resp;
	}

	/**
	 * Получение должности по коду
	 */
	function loadPostForAPI($data) {
		if (!empty($data['Post_id'])) {
			$filter = "id = :Post_id";
		} else if (!empty($data['Code'])) {
			$filter = "code = :Code";
		} else {
			return array();
		}

		$query = "
				select
					code as \"Code\"
					,name as \"Name\"
					,frmpEntry_id as \"FRMPPost_id\"
					,PostKind_id as \"PostKind_id\"
					,ProfessionalGroup_id as \"ProfessionalGroup_id\"
					,TarifList as \"TarifList\"
					,Speciality_id as \"Speciality_id\"
					,PrimaryHealthCare as \"PrimaryHealthCare\"
					,MaxPopulation as \"MaxPopulation\"
				from persis.v_Post 

				where
					{$filter}
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}
	
	/**
	 * Создание должности. Метод для API
	 */
	function createPostForAPI($data){
		$fields = array();
		$setFields = array();
		if(!empty($data['Code'])){
			$fields[] = ' code';
			$setFields[] = ' :Code';
		}
		if(!empty($data['Name'])){
			$fields[] = 'name';
			$setFields[] = ' :Name';
		}
		if(!empty($data['FRMPPost_id'])){
			$fields[] = ' frmpEntry_id';
			$setFields[] = ' :FRMPPost_id';
		}
		if(!empty($data['PostKind_id'])){
			$fields[] = ' PostKind_id';
			$setFields[] = ' :PostKind_id';
		}
		if(!empty($data['ProfessionalGroup_id'])){
			$fields[] = ' ProfessionalGroup_id';
			$setFields[] = ' :ProfessionalGroup_id';
		}
		if(!empty($data['TarifList'])){
			$fields[] = ' TarifList';
			$setFields[] = ' :TarifList';
		}
		if(!empty($data['Speciality_id'])){
			$fields[] = ' Speciality_id';
			$setFields[] = ' :Speciality_id';
		}
		if(isset($data['PrimaryHealthCare'])){
			// PrimaryHealthCare в БД имеют значения 0 и 1
			$data['PrimaryHealthCare'] = $data['PrimaryHealthCare']-1;
			$fields[] = ' PrimaryHealthCare';
			$setFields[] = ' :PrimaryHealthCare';
		}
		if(!empty($data['MaxPopulation'])){
			$fields[] = ' MaxPopulation';
			$setFields[] = ' :MaxPopulation';
		}
		if(!empty($data['Post_Nick'])){
			$fields[] = ' Post_Nick';
			$setFields[] = ' :Post_Nick';
		}
		if(!empty($data['begDT'])){
			$fields[] = ' begDT';
			$setFields[] = ' :begDT';
		}
		if(count($setFields)>0){			
			$setFields = implode(',', $setFields);
			$fields = implode(',', $fields);
		}else{
			return array('success' => false,'Error_Msg' => 'не переданы параметры для добавления записи');
		}
		
		//проверим корректность переданных значений
		$addVer = $this->additionalVerificationAPI($data);		
		if(isset($addVer['Error_Msg'])){
			return array('success' => false,'Error_Msg' => $addVer['Error_Msg']);
		}

		//проверка на уникальность кода
		$count = $this->getFirstResultFromQuery("SELECT COUNT(id) as \"id\" FROM persis.v_Post WHERE code = :Code", $data);
		if(!empty($count)){
			return array(
				'success' => false,
				'Error_Msg' => 'Запись с указанным кодом уже существует'
			);
		}
		try {
			$sp = getSessionParams();
			$query = "INSERT INTO persis.Post (".$fields.", version, insDT, updDT, pmUser_insID, pmUser_updID) VALUES (".$setFields.", 0, dbo.tzGetDate(), dbo.tzGetDate(), ".$sp['pmUser_id'].", ".$sp['pmUser_id'].")";
			//echo getDebugSQL($query, $data);die();
			$res = $this->db->query($query, $data);
			if($res){
				$query = $this->db->query('SELECT currval(\'persis.post_id_seq\') AS "Post_id"');
				$query = $query->row();
				return array('Post_id' => $query->Post_id);
			}else{
				return array(
					'success' => false,
					'Error_Msg' => 'при добавлении записи произошла ошшибка'
				);
			}			
		} catch (Exception $e) {
			return array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
	}
	
	/**
	 * Изменение должности. Метод для API
	 */
	function updatePostForAPI($data){
		$params = array();
		$set = '';
		$setFields = array();
		if(!empty($data['Post_id'])){
			$params['id'] = $data['Post_id'];
		}else{
			return array('success' => false,'Error_Msg' => 'не передан параметр идентификатора записи');
		}
		if(!empty($data['Code'])){
			//проверка на уникальность кода
			$count = $this->getFirstResultFromQuery("SELECT COUNT(id) as \"id\" FROM persis.v_Post WHERE code = :Code and id <> :Post_id", $data);
			if(!empty($count)){
				return array(
					'success' => false,
					'Error_Msg' => 'Запись с указанным кодом уже существует'
				);
			}
			$setFields[] = ' code = :code ';
			$params['code'] = $data['Code'];
		}
		if(!empty($data['Name'])){
			$setFields[] = ' name = :name ';
			$params['name'] = $data['Name'];
		}
		if(!empty($data['FRMPPost_id'])){
			$setFields[] = ' frmpEntry_id = :frmpEntry_id';
			$params['frmpEntry_id'] = $data['FRMPPost_id'];
		}
		if(!empty($data['PostKind_id'])){
			$setFields[] = ' PostKind_id = :PostKind_id';
			$params['PostKind_id'] = $data['PostKind_id'];
		}
		if(!empty($data['ProfessionalGroup_id'])){
			$setFields[] = ' ProfessionalGroup_id = :ProfessionalGroup_id ';
			$params['ProfessionalGroup_id'] = $data['ProfessionalGroup_id'];
		}
		if(!empty($data['TarifList'])){
			$setFields[] = ' TarifList = :TarifList';
			$params['TarifList'] = $data['TarifList'];
		}
		if(!empty($data['Speciality_id'])){
			$setFields[] = ' Speciality_id = :Speciality_id';
			$params['Speciality_id'] = $data['Speciality_id'];
		}
		if(isset($data['PrimaryHealthCare'])){
			// PrimaryHealthCare в БД имеют значения 0 и 1
			$data['PrimaryHealthCare'] = $data['PrimaryHealthCare']-1;
			$setFields[] = ' PrimaryHealthCare = :PrimaryHealthCare ';
			$params['PrimaryHealthCare'] = $data['PrimaryHealthCare'];
		}
		if(!empty($data['MaxPopulation'])){
			$setFields[] = ' MaxPopulation = :MaxPopulation';
			$params['MaxPopulation'] = $data['MaxPopulation'];
		}
		if(!empty($data['Post_Nick'])){
			$setFields[] = ' Post_Nick = :Post_Nick';
			$params['Post_Nick'] = $data['Post_Nick'];
		}
		if(!empty($data['begDT'])){
			$setFields[] = ' begDT = :begDT';
			$params['begDT'] = $data['begDT'];
		}
		
		if(count($setFields)>0){
			$setFields = implode(",", $setFields);
		}else{
			return array('success' => false,'Error_Msg' => 'не переданы параметры для обновления записи');
		}
		
		//проверим корректность переданных значений
		$addVer = $this->additionalVerificationAPI($data);		
		if(isset($addVer['Error_Msg'])){
			return array('success' => false,'Error_Msg' => $addVer['Error_Msg']);
		}
		
		try {
			$sp = getSessionParams();
			$query = "UPDATE persis.Post SET ".$setFields.", updDT=dbo.tzGetDate(), pmUser_updID=".$sp['pmUser_id'].", version=version+1 WHERE id = :id";
			//echo getDebugSQL($query, $params);die();
			$res = $this->db->query($query, $params);
			if($res){
				return $res;
			}else{
				return array(
					'success' => false,
					'Error_Msg' => 'при обновлении записи произошла ошшибка'
				);
			}
		} catch (Exception $e) {
			return array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
	}
	
	/**
	 * Дополнительная проверка данных на корректность заполнения. Метод для API
	 */
	function additionalVerificationAPI($data){
		$errFields = array();
		$requiredFields = ( $_SESSION['region']['nick'] == 'ekb' ) ? array('Post_Nick', 'begDT') : array();
		foreach ($requiredFields as $value) {
			if(empty($data[$value])){
				return array('success' => false,'Error_Msg' => 'Отсутствует обязательный параметр '.$value);
			}
		}
		if(!empty($data['FRMPPost_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select count(*) as \"cnt\" from persis.frmppost where id = :FRMPPost_id
			", $data);		
			if(empty($result['cnt'])){
				//'должность ФРМП';
				$errFields[] = 'FRMPPost_id';
			}
		}
		if(!empty($data['PostKind_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select count(*) as \"cnt\" from persis.v_PostKind where id = :PostKind_id
			", $data);		
			if(empty($result['cnt'])){
				//'вид должности';
				$errFields[] = 'PostKind_id';
			}
		}
		if(!empty($data['ProfessionalGroup_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select count(*) as \"cnt\" from persis.ProfessionalGroup where id = :ProfessionalGroup_id
			", $data);		
			if(empty($result['cnt'])){
				//'профессиональная группа';
				$errFields[] = 'ProfessionalGroup_id';
			}
		}
		if(!empty($data['Speciality_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select count(*) as cnt from persis.Speciality where id = :Speciality_id
			", $data);		
			if(empty($result['cnt'])){
				//'специальность';
				$errFields[] = 'Speciality_id';
			}
		}
		
		if(count($errFields)>0){
			return array('success' => false,'Error_Msg' => 'Не все поля заполнены корректно, проверьте введенные данные '.implode(', ', $errFields));
		}else{
			return false;
		}
	}

}