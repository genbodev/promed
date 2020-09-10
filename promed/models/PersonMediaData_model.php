<?php
class PersonMediaData_model extends CI_Model {
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
				PersonMediaData_id,
				Person_id,
				PersonMediaData_FileName,
				PersonMediaData_FilePath,
				PersonMediaData_Comment
			from
				v_PersonMediaData with (nolock)
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
			$filter = ' and PersonMediaData_FilePath like :Person_id_hash';
		}

		$query = "
			select
				PersonMediaData_id,
				--PersonMediaData_insDT,
				--PersonMediaData_updDT,
				convert(varchar(10),PersonMediaData_updDT,104) +' '+ convert(varchar(8),PersonMediaData_updDT,108) as updateDateTime,
				Person_id,
				PersonMediaData_FileName,
				PersonMediaData_FilePath,
				PersonMediaData_Comment
			from
				v_PersonMediaData with (nolock)
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonMediaData_id;
			exec " . $procedure . "
				@PersonMediaData_id = @Res output,
				@Person_id = :Person_id,
				@PersonMediaData_FileName = :PersonMediaData_FileName,
				@PersonMediaData_FilePath = :PersonMediaData_FilePath,
				@PersonMediaData_Comment = :PersonMediaData_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonMediaData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	/*
	function deletePersonMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['PersonMediaData_id']) ) {
			return false;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonMediaData_id;
			exec p_PersonMediaData_del 
				@PersonMediaData_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;				
			select @Res as PersonMediaData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'PersonMediaData_id' => isset($data['PersonMediaData_id']) ? $data['PersonMediaData_id'] : null
		);
		//return getDebugSql($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	function getPersonMediaData($data) {
		$query = "
			select
				PersonMediaData_id,
				PersonMediaData_FileName,
				PersonMediaData_FilePath,
				PersonMediaData_Comment
			from
				PersonMediaData
			where
				PersonMediaData_id = :PersonMediaData_id
			order by
				PersonMediaData_insDT
			";
		$result = $this->db->query($query,  array('PersonMediaData_id' => $data['PersonMediaData_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	function loadPersonMediaDataListGrid($data) {
		$query = "
			select
				PersonMediaData_id,
				PersonMediaData_FileName,
				PersonMediaData_FilePath,
				PersonMediaData_Comment,
				'saved' as state
			from
				PersonMediaData with (nolock)
			where
				Evn_id = :Evn_id
			order by
				PersonMediaData_insDT
			";
		$result = $this->db->query($query,  array('Evn_id' => $data['Evn_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getPersonMediaDataViewData($data) {
		$queryParams = array();
		if (isset($data['PersonMediaData_pid']))
		{
			$filter = 'Evn_id = :Evn_id';
			$queryParams['Evn_id'] = $data['PersonMediaData_pid'];
		}
		else
		{
			$filter = 'PersonMediaData_id = :PersonMediaData_id';
			$queryParams['PersonMediaData_id'] = $data['PersonMediaData_id'];
		}
		$query = "
			select
				PersonMediaData_id,
				Evn_id as PersonMediaData_pid,
				PersonMediaData_FileName,
				PersonMediaData_FilePath,
				'/uploads/' as PersonMediaData__Dir, -- часть url пути к файлу на сервере без имени файла
				-- если все файлы будут в одной папке, то папку (PersonMediaData__Dir) можно будет прописать в шаблоне eew_file_list_item.php
				-- или если FilePath будет полным путем к файлу, то PersonMediaData__Dir будет не нужна
				PersonMediaData_Comment,
				0 as Children_Count
			from
				PersonMediaData with (nolock)
			where
				{$filter}
			order by
				PersonMediaData_insDT
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	*/
}
?>