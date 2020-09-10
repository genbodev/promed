<?php
class PMMediaData_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Method description
	 */
	function savepmMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['pmMediaData_id']) ) {
			$procedure = 'p_pmMediaData_ins';
		}
		else {
			$procedure = 'p_pmMediaData_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :pmMediaData_id;
			exec " . $procedure . "
				@pmMediaData_id = @Res output,
				@pmMediaData_ObjectName = :ObjectName,
				@pmMediaData_ObjectID = :ObjectID,
				@pmMediaData_FileName = :pmMediaData_FileName,
				@pmMediaData_FilePath = :pmMediaData_FilePath,
				@pmMediaData_Comment = :pmMediaData_Comment,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as pmMediaData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null,
			'ObjectName' => $data['ObjectName'],
			'ObjectID' => $data['ObjectID'],
			'Person_id' => $data['ObjectName'] == 'PersonPhoto' ? $data['ObjectID'] : null,
			'pmMediaData_FileName' => $data['orig_name'],
			'pmMediaData_FilePath' => $data['file_name'],
			'pmMediaData_Comment' => (!empty($data['description'])?$data['description']:''),
			'pmUser_id' => $data['pmUser_id']
		);
		//print getDebugSql($query, $queryParams); die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0])) {
				return array('Error_Code' => $res[0]['Error_Code'] != '' ? $res[0]['Error_Code'] : '', 'Error_Msg' => $res[0]['Error_Msg'] != '' ? $res[0]['Error_Msg'] : '');
			} else {
				return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
			}
		} else {
			return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
		}
	}
	
	/**
	 * Method description
	 */
	function deletepmMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['pmMediaData_id']) ) {
			return false;
		}
		/*if($_SESSION['region']['nick'] == 'khak'){
			$query_info = "
				select PMD.pmMediaData_ObjectID as pm_id,
				PMD.pmMediaData_FileName as pm_file
				from v_pmMediaData PMD with(nolock)
				where PMD.pmMediaData_id = :pmMediaData_id
				and PMD.pmMediaData_ObjectName = 'PersonCard'
			";
			$result_info = $this->db->query($query_info, array('pmMediaData_id' => $data['pmMediaData_id']));
			if(is_object($result_info)){
				$result_info = $result_info->result('array');
				if(count($result_info) > 0){
					$PersonCard_id = $result_info[0]['pm_id'];
					$File_name = $result_info[0]['pm_file'];
					$Message = $File_name;
					$this->load->library('textlog', array('file'=>'PersonCardFile_del_'.date('Y-m-d').'_'.$PersonCard_id.'.log'),'textlog2');
					$query_info = "
						select ISNULL(P.Person_SurName,'') + ' ' + ISNULL(P.Person_FirName,'') + ' ' + ISNULL(P.Person_SecName,'') as Person_FIO
						from v_PersonCard_all P with(nolock)
						where P.PersonCard_id = :PersonCard_id
					";
					$result_info = $this->db->query($query_info,array('PersonCard_id' => $PersonCard_id));
					if(is_object($result_info)){
						$result_info = $result_info->result('array');
						if(count($result_info) > 0){
							$Message = $Message . ' ' . $result_info[0]['Person_FIO'] . ' ';
						}
					}
					$Message = $Message. 'удален пользователем '.isset($data['pmUser_id'])?$data['pmUser_id']:'null';
					$this->textlog2->add($Message);
				}
			}
		}*/
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :pmMediaData_id;
			exec p_pmMediaData_del 
				@pmMediaData_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;				
			select @Res as pmMediaData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null
		);
		//return getDebugSql($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Method description
	 */
	function getpmMediaData($data) {
		//print_r($data); exit();
		$filter = "1=1";
		$params = array();
		if( isset($data['pmMediaData_id']) && $data['pmMediaData_id'] > 0 ) {
			$params['pmMediaData_id'] = $data['pmMediaData_id'];
			$filter .= " and pmMediaData_id = :pmMediaData_id";
		}
		if( isset($data['ObjectID']) && $data['ObjectID'] > 0 ) {
			$params['pmMediaData_ObjectID'] = $data['ObjectID'];
			$filter .= " and pmMediaData_ObjectID = :pmMediaData_ObjectID";
		}
		if( !empty($data['Person_id']) ) {
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and Person_id = :Person_id";
		}
		if( !empty($data['pmMediaData_ObjectName']) ) {
			$params['pmMediaData_ObjectName'] = $data['pmMediaData_ObjectName'];
			$filter .= " and pmMediaData_ObjectName = :pmMediaData_ObjectName";
		}
		$query = "
			select
				pmMediaData_id,
				pmMediaData_FileName,
				pmMediaData_FilePath,
				pmMediaData_Comment,
				convert(varchar(10),pmMediaData_updDT,104) +' '+ convert(varchar(8),pmMediaData_updDT,108) as updateDateTime
			from
				pmMediaData with (nolock)
			where
				{$filter}
			order by
				pmMediaData_insDT
			";
		//echo getDebugSql($query, $params); exit();
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Method description
	 */
	function loadpmMediaDataListGrid($data) {
		$query = "
			select
				pmMediaData_id,
				pmMediaData_FileName,
				pmMediaData_FilePath,
				pmMediaData_Comment,
				'saved' as state
			from
				pmMediaData with (nolock)
			where
				pmMediaData_ObjectName = :ObjectName
				and pmMediaData_ObjectID = :ObjectID
			order by
				pmMediaData_insDT
			";
		$result = $this->db->query($query,  array(
			'ObjectName' => $data['ObjectName'],
			'ObjectID' => $data['ObjectID']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	// не осили зачем нужна, поэтому убрал
	/*function getpmMediaDataViewData($data) {
		$queryParams = array();
		if (isset($data['pmMediaData_pid']))
		{
			$filter = 'WhsDocumentSupply_id = :WhsDocumentSupply_id';
			$queryParams['WhsDocumentSupply_id'] = $data['pmMediaData_pid'];
		}
		else
		{
			$filter = 'pmMediaData_id = :pmMediaData_id';
			$queryParams['pmMediaData_id'] = $data['pmMediaData_id'];
		}
		$query = "
			select
				pmMediaData_id,
				WhsDocumentSupply_id as pmMediaData_pid,
				pmMediaData_FileName,
				pmMediaData_FilePath,
				'/uploads/' as pmMediaData__Dir, -- часть url пути к файлу на сервере без имени файла
				-- если все файлы будут в одной папке, то папку (pmMediaData__Dir) можно будет прописать в шаблоне eew_file_list_item.php
				-- или если FilePath будет полным путем к файлу, то pmMediaData__Dir будет не нужна
				pmMediaData_Comment,
				0 as Children_Count
			from
				pmMediaData with (nolock)
			where
				{$filter}
			order by
				pmMediaData_insDT
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
	
	///////////////////
	
	private $person_files_url = '/uploads/persons/';
	private $person_thumbs_dir = 'thumbs';

	/**
	 * Method description
	 */
	function getUrlPersons($data) {
		return $this->person_files_url;
	}

	/**
	 * Method description
	 */
	function getUrlPersonFiles($data) {
		return ($this->person_files_url .$data['Person_id']. '/');
	}

	/**
	 * Method description
	 */
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

	/**
	 * Method description
	 */
	function getUrlPersonThumbs($data) {
		return ($this->person_files_url .$data['Person_id'] .'/'. $this->person_thumbs_dir .'/');
	}

	/**
	 * Method description
	 */
	function getPathPersonThumbs($data, $check_dir = false) {
		$url = $this->getUrlPersonThumbs($data);
		$path = '.'.$url;
		if($check_dir && false == file_exists($path))
		{
			@mkdir($path);
		}
		return $path;
	}

	/**
	 * Method description
	 */
	function getPersonPhotoThumbName($data) {
		//имена фотографий начинаются с хэша Person_id
		$result = $this->getpmMediaData($data);
		if(is_array($result) && isset($result[0]) && !empty($result[0]['pmMediaData_FilePath']))
		{
			$name = $this->getUrlPersonThumbs($data).$result[0]['pmMediaData_FilePath'];
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

    /**
     * Копирование информации о файле и самого файла
     * @param array|int $id_list - идентификатор конкретной записи либо массив содержащий ключи
     * @param array $data - для указания идентификатора копируемой записи и данных, которые требуется изменить
     * @return array|bool
     */
    function copypmMediaData($id_list, $data = array()) {
        if (!is_array($id_list)) {
            $id_list = array($id_list);
        }

        $error = array();
        $upload_path = './'.PMMEDIAPATH;

        try {
            // Правильно ли указана директория для загрузки?
            if ( ! @is_dir($upload_path)) {
                $error[] = array('Error_Code' => 704 , 'Error_Msg' => 'Путь для загрузки файлов некорректен');
                throw new Exception();
            }

            // Имеет ли директория для загрузки права на запись?
            if ( ! is_writable($upload_path)) {
                $error[] = array('Error_Code' => 705 , 'Error_Msg' => 'Директория, в которую загружается файл не имеет прав на запись');
                throw new Exception();
            }

            foreach ($id_list as $id) {
                $query = "
                    select
                        pmMediaData_FilePath
                    from
                        v_pmMediaData with (nolock)
                    where
                        pmMediaData_id = :pmMediaData_id;
                ";
                $file_name = $this->getFirstResultFromQuery($query, array(
                    'pmMediaData_id' => $id
                ));
                if (!empty($file_name)) {
                    $f_arr = explode('.', $file_name);
                    $file_ext = count($f_arr) > 1 ? end($f_arr) : '';
                    $new_file_name = md5($file_name.time()).'.'.$file_ext;

                    $copy_res = copy($upload_path.$file_name, $upload_path.$new_file_name);
                    if ($copy_res) {
                        $data['pmMediaData_id'] = $id;
                        $data['pmMediaData_FilePath'] = $new_file_name;

                        $response = $this->copyObject('pmMediaData', $data);
                        if (!empty($response['Error_Msg'])) {
                            $error[] = array('Error_Msg' => $response['Error_Msg']);
                            throw new Exception();
                        }
                    } else {
                        $error[] = array('Error_Msg' => 'Не удалось скопировать файл');
                        throw new Exception();
                    }
                }
            }


        } catch (Exception $e) {
            $result = array(
                'Error_Code' => 0,
                'Error_Msg' => 'Ошибка'
            );

            if (count($error) > 0) {
                if (isset($error[0]['Error_Code'])) {
                    $result['Error_Code'] = $error[0]['Error_Code'];
                }
                $result['Error_Msg'] = $error[0]['Error_Msg'];
            }

            return $result;
        }
    }
}
?>