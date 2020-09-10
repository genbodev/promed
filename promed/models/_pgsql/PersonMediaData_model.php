<?php
class PersonMediaData_model extends SwPgModel {
	function __construct() {
		parent::__construct();
	}

	private $person_files_url = '/uploads/persons/';
	private $person_thumbs_dir = 'thumbs';

	function getUrlPersons($data) {
		return $this->person_files_url;
	}

	function getUrlPersonFiles($data) {
		return ($this->person_files_url .$data['Person_id']. '/');
	}

	function getPathPersonFiles($data, $check_dir = false) {
		$url = $this->getUrlPersonFiles($data);
		$path = '.'.$url;
		if($check_dir && false == file_exists($path))
		{
			$path_persons = '.'.$this->person_files_url;
			if(file_exists($path_persons))
			{
				@mkdir($path);
			}
			else
			{
				@mkdir($path_persons);
				@mkdir($path);
			}
		}
		return $path;
	}

	function getUrlPersonThumbs($data) {
		return ($this->person_files_url .$data['Person_id'] .'/'. $this->person_thumbs_dir .'/');
	}

	function getPathPersonThumbs($data, $check_dir = false) {
		$url = $this->getUrlPersonThumbs($data);
		$path = '.'.$url;
		if($check_dir && false == file_exists($path))
		{
			@mkdir($path);
		}
		return $path;
	}

	function getPersonPhotoThumbName($data) {
		//имена фотографий начинаются с хэша Person_id
		$result = $this->getPersonPhoto($data);
		if(is_array($result) && isset($result[0]) && !empty($result[0]['PersonMediaData_FilePath']))
		{
			$name = $this->getUrlPersonThumbs($data).$result[0]['PersonMediaData_FilePath'];
			if(file_exists('.'.$name))
			{
				$name .= '?'.md5($result[0]['updateDateTime']);
				return $name;
			}
		}
		/*
		при отсутствии фотографии тут можно вывести урл картинки М или Ж
		if (isset($data['Sex_id']))
		{
		}
		*/
		return '/img/men.jpg';
	}
	
	function getMediaData($data) {
		$queryParams = array(
			'PersonMediaData_id' => $data['PersonMediaData_id']
		);
		$query = "
			select
				PersonMediaData_id as \"PersonMediaData_id\",
				Person_id as \"Person_id\",
				PersonMediaData_FileName as \"PersonMediaData_FileName\",
				PersonMediaData_FilePath as \"PersonMediaData_FilePath\",
				PersonMediaData_Comment as \"PersonMediaData_Comment\"
			from
				v_PersonMediaData
			where
				PersonMediaData_id = :PersonMediaData_id
			";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getPersonPhoto($data) {
		//имена фотографий начинаются с Person_id
		$data['Person_id_hash'] = $data['Person_id'];
		return $this->getPersonMediaData($data);
	}
	
	function getPersonMediaData($data) {
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		$filter = '';

		if(isset($data['Person_id_hash']))
		{
			$queryParams['Person_id_hash'] = $data['Person_id_hash'].'%';
			$filter = ' and PersonMediaData_FilePath ilike :Person_id_hash';
		}

		$query = "
			select
				PersonMediaData_id as \"PersonMediaData_id\",
				to_char(PersonMediaData_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"updateDateTime\",
				Person_id as \"Person_id\",
				PersonMediaData_FileName as \"PersonMediaData_FileName\",
				PersonMediaData_FilePath as \"PersonMediaData_FilePath\",
				PersonMediaData_Comment as \"PersonMediaData_Comment\"
			from
				v_PersonMediaData
			where
				Person_id = :Person_id
				{$filter}
			order by
				PersonMediaData_insDT
			";
		$result = $this->db->query($query,  $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	function savePersonMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['PersonMediaData_id']) ) {
			$procedure = 'p_PersonMediaData_ins';
		}
		else {
			$procedure = 'p_PersonMediaData_upd';
		}

		$query = "
			select
				PersonMediaData_id as \"PersonMediaData_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				PersonMediaData_id := :PersonMediaData_id,
				Person_id := :Person_id,
				PersonMediaData_FileName := :PersonMediaData_FileName,
				PersonMediaData_FilePath := :PersonMediaData_FilePath,
				PersonMediaData_Comment := :PersonMediaData_Comment,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'PersonMediaData_id' => isset($data['PersonMediaData_id']) ? $data['PersonMediaData_id'] : null,
			'Person_id' => $data['Person_id'],
			'PersonMediaData_FileName' => $data['PersonMediaData_FileName'],
			'PersonMediaData_FilePath' => $data['PersonMediaData_FilePath'],
			'PersonMediaData_Comment' => (!empty($data['PersonMediaData_Comment'])?$data['PersonMediaData_Comment']:''),
			'pmUser_id' => $data['pmUser_id']
		);
		//print getDebugSql($query, $queryParams); die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0])) {
				return array('Error_Code' => (!empty($res[0]['Error_Code']))?$res[0]['Error_Code']:'', 'Error_Msg' => (!empty($res[0]['Error_Msg']))?$res[0]['Error_Msg']:'');
			} else {
				return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
			}
		} else {
			return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
		}
	}
}
