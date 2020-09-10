<?php

class PMMediaData_model extends SwPgModel
{
    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Method description
     * @param $data
     * @return array
     */
    function savepmMediaData($data)
    {
        $procedure = '';

        if (!isset($data['pmMediaData_id'])) {
            $procedure = 'p_pmMediaData_ins';
        } else {
            $procedure = 'p_pmMediaData_upd';
        }

        $query = "select
                        pmMediaData_id as \"pmMediaData_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from " . $procedure . " (
                        pmMediaData_id := :pmMediaData_id,
                        pmMediaData_ObjectName := :ObjectName,
                        pmMediaData_ObjectID := :ObjectID,
                        pmMediaData_FileName := :pmMediaData_FileName,
                        pmMediaData_FilePath := :pmMediaData_FilePath,
                        pmMediaData_Comment := :pmMediaData_Comment,
                        Person_id := :Person_id,
                        pmUser_id := :pmUser_id
                    );";


        $queryParams = array(
            'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null,
            'ObjectName' => $data['ObjectName'],
            'ObjectID' => $data['ObjectID'],
            'Person_id' => $data['ObjectName'] == 'PersonPhoto' ? $data['ObjectID'] : null,
            'pmMediaData_FileName' => $data['orig_name'],
            'pmMediaData_FilePath' => $data['file_name'],
            'pmMediaData_Comment' => !empty($data['description']) ? $data['description'] : '',
            'pmUser_id' => $data['pmUser_id']
        );
        //print getDebugSql($query, $queryParams); die;
        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
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
     * @param $data
     * @return bool
     */
    function deletepmMediaData($data)
    {
        $procedure = '';

        if (!isset($data['pmMediaData_id'])) {
            return false;
        }

        $query = "select
                    CAST(:pmMediaData_id as bigint) as \"pmMediaData_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_pmMediaData_del (
                    pmMediaData_id := :pmMediaData_id
                );
            ";

        $queryParams = array(
            'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null
        );
        //return getDebugSql($query, $queryParams);
        $result = $this->db->query($query, $queryParams);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Method description
     * @param $data
     * @return bool
     */
    function getpmMediaData($data)
    {
        //print_r($data); exit();
        $filter = "1=1";
        $params = array();
        if (isset($data['pmMediaData_id']) && $data['pmMediaData_id'] > 0) {
            $params['pmMediaData_id'] = $data['pmMediaData_id'];
            $filter .= " and pmMediaData_id = :pmMediaData_id";
        }
        if (isset($data['ObjectID']) && $data['ObjectID'] > 0) {
            $params['pmMediaData_ObjectID'] = $data['ObjectID'];
            $filter .= " and pmMediaData_ObjectID = :pmMediaData_ObjectID";
        }
        if (!empty($data['Person_id'])) {
            $params['Person_id'] = $data['Person_id'];
            $filter .= " and Person_id = :Person_id";
        }
        if (!empty($data['pmMediaData_ObjectName'])) {
            $params['pmMediaData_ObjectName'] = $data['pmMediaData_ObjectName'];
            $filter .= " and pmMediaData_ObjectName = :pmMediaData_ObjectName";
        }
        $query = "
			select
				pmMediaData_id as \"pmMediaData_id\",
				pmMediaData_FileName as \"pmMediaData_FileName\",
				pmMediaData_FilePath as \"pmMediaData_FilePath\",
				pmMediaData_Comment as \"pmMediaData_Comment\",
				to_char(pmMediaData_updDT,'dd.mm.yyyy') ||' '|| to_char(pmMediaData_updDT,'HH24:MI:SS') as \"updateDateTime\"
			from
				v_pmMediaData
			where
				{$filter}
			order by
				pmMediaData_insDT
			";
        //echo getDebugSql($query, $params); exit();
        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Method description
     * @param $data
     * @return bool
     */
    function loadpmMediaDataListGrid($data)
    {
        $query = "
			select
				pmMediaData_id as \"pmMediaData_id\",
				pmMediaData_FileName as \"pmMediaData_FileName\",
				pmMediaData_FilePath as \"pmMediaData_FilePath\",
				pmMediaData_Comment as \"pmMediaData_Comment\",
				'saved' as \"state\"
			from
				pmMediaData
			where
				pmMediaData_ObjectName = :ObjectName
				and pmMediaData_ObjectID = :ObjectID
			order by
				pmMediaData_insDT
			";
        $result = $this->db->query($query, array(
            'ObjectName' => $data['ObjectName'],
            'ObjectID' => $data['ObjectID']
        ));
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }


    private $person_files_url = '/uploads/persons/';
    private $person_thumbs_dir = 'thumbs';

    /**
     * Method description
     * @return string
     */
    function getUrlPersons()
    {
        return $this->person_files_url;
    }

    /**
     * Method description
     * @param $data
     * @return string
     */
    function getUrlPersonFiles($data)
    {
        return ($this->person_files_url . $data['Person_id'] . '/');
    }

    /**
     * Method description
     * @param $data
     * @param bool $check_dir
     * @return string
     */
    function getPathPersonFiles($data, $check_dir = false)
    {
        $url = $this->getUrlPersonFiles($data);
        $path = '.' . $url;
        if ($check_dir && false == file_exists($path)) {
            $path_persons = '.' . $this->person_files_url;
            if (file_exists($path_persons)) {
                @mkdir($path);
            } else {
                @mkdir($path_persons);
                @mkdir($path);
            }
        }
        return $path;
    }

    /**
     * Method description
     */
    function getUrlPersonThumbs($data)
    {
        return ($this->person_files_url . $data['Person_id'] . '/' . $this->person_thumbs_dir . '/');
    }

    /**
     * Method description
     * @param $data
     * @param bool $check_dir
     * @return string
     */
    function getPathPersonThumbs($data, $check_dir = false)
    {
        $url = $this->getUrlPersonThumbs($data);
        $path = '.' . $url;
        if ($check_dir && false == file_exists($path)) {
            @mkdir($path);
        }
        return $path;
    }

    /**
     * Method description
     */
    function getPersonPhotoThumbName($data)
    {
        //имена фотографий начинаются с хэша Person_id
        $result = $this->getpmMediaData($data);
        if (is_array($result) && isset($result[0]) && !empty($result[0]['pmMediaData_FilePath'])) {
            $name = $this->getUrlPersonThumbs($data) . $result[0]['pmMediaData_FilePath'];
            if (file_exists('.' . $name)) {
                $name .= '?' . md5($result[0]['updateDateTime']);
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

    /**
     * Копирование информации о файле и самого файла
     * @param array|int $id_list - идентификатор конкретной записи либо массив содержащий ключи
     * @param array $data - для указания идентификатора копируемой записи и данных, которые требуется изменить
     * @return array|bool
     */
    function copypmMediaData($id_list, $data = array())
    {
        if (!is_array($id_list)) {
            $id_list = array($id_list);
        }

        $error = array();
        $upload_path = './' . PMMEDIAPATH;

        try {
            // Правильно ли указана директория для загрузки?
            if (!@is_dir($upload_path)) {
                $error[] = array('Error_Code' => 704, 'Error_Msg' => 'Путь для загрузки файлов некорректен');
                throw new Exception();
            }

            // Имеет ли директория для загрузки права на запись?
            if (!is_writable($upload_path)) {
                $error[] = array('Error_Code' => 705, 'Error_Msg' => 'Директория, в которую загружается файл не имеет прав на запись');
                throw new Exception();
            }

            foreach ($id_list as $id) {
                $query = "
                    select
                        pmMediaData_FilePath as \"pmMediaData_FilePath\"
                    from
                        v_pmMediaData
                    where
                        pmMediaData_id = :pmMediaData_id;
                ";
                $file_name = $this->getFirstResultFromQuery($query, array(
                    'pmMediaData_id' => $id
                ));
                if (!empty($file_name)) {
                    $f_arr = explode(".", $file_name);
                    $file_ext = count($f_arr) > 1 ? end($f_arr) : '';
                    $new_file_name = md5($file_name . time()) . '.' . $file_ext;

                    $copy_res = copy($upload_path . $file_name, $upload_path . $new_file_name);
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
