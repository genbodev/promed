<?php
class EvnMediaFiles_model extends swModel {
	/**
	 * Конструктор объекта
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Определение Evn_id ТАП, КВС по идентификатору документа EvnXml_id
	 * На выходе: JSON-строка
	 * Используется: формы на которых используется комбобокс выбора файла sw.Promed.SwEvnMediaDataCombo
	 */
	function getEvnByEvnXml($data) {	
		$query = '
			select Evn.Evn_rid as Evn_id from v_EvnXml DOC with (nolock) inner join v_Evn Evn with (nolock) on Evn.Evn_id = DOC.Evn_id where DOC.EvnXml_id = :EvnXml_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение EvnMediaData_id по Evn_id
	 */
	function getEvnMediaByEvn($data) {
		$params = array(
			'Evn_id' => $data['Evn_id'],
		);
		$query = "
			select EvnMediaData_id
			from v_EvnMediaData with(nolock)
			where Evn_id = :Evn_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Функция чтения списка файлов для комбобокса, не больше 100
	 * На выходе: JSON-строка
	 * Используется: формы на которых используется комбобокс выбора файла sw.Promed.SwEvnMediaDataCombo
	 */
	function loadList($data) {
		$filters = '(1 = 1)';
		$params = array();
		
		if(isset($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$filters .= '
				and EMD.Evn_id = :Evn_id';
		}
		
		if(isset($data['EvnXml_id']) && empty($data['Evn_id'])) {
			// Выводим то, что прикреплено к ТАП, КВС для возможности вставки в документ
			$params['EvnXml_id'] = $data['EvnXml_id'];
			$filters .= '
				and EMD.Evn_id = (select Evn.Evn_rid from v_EvnXml DOC with (nolock) inner join v_Evn Evn with (nolock) on Evn.Evn_id = DOC.Evn_id where DOC.EvnXml_id = :EvnXml_id)';
		}
		
		switch ($data['filterType']) {
			case 'image':
				// только картинки jpg|jpe|jpeg|png|bmp|tiff|tif|gif
				$filters .= "
				and (EMD.EvnMediaData_FilePath like '%.jp%' or EMD.EvnMediaData_FilePath like '%png'  or EMD.EvnMediaData_FilePath like '%bmp'  or EMD.EvnMediaData_FilePath like '%gif' or EMD.EvnMediaData_FilePath like '%.tif%')";
				break;
			case 'doc':
				//
				break;
			default:
				// all
		}
		
		$query = '
			select top 100
				EMD.EvnMediaData_id,
				EMD.Evn_id,
				EMD.EvnMediaData_FileName,
				EMD.EvnMediaData_FilePath,
				EMD.EvnMediaData_Comment
			from
				v_EvnMediaData EMD with (nolock)
			where
				'. $filters .'
			order by
				EvnMediaData_insDT
		';
		$result = $this->db->query($query, $params );
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Метод получения файлов для API
	 */
	function getEvnMediaDataForAPI($data) {
		$resp = $this->queryResult("
			select
				EMD.EvnMediaData_id,
				EMD.EvnMediaData_FilePath,
				EMD.EvnMediaData_FileName
			from
				v_EvnMediaData EMD with (nolock)
			where
				EMD.Evn_id = :Evn_id
			order by
				EvnMediaData_insDT
		", array(
			'Evn_id' => $data['Evn_id']
		));

		foreach($resp as &$respone) {
			if (!empty($data['EvnMediaData_IsRequired']) && $data['EvnMediaData_IsRequired'] == 2) {
				if (file_exists(EVNMEDIAPATH . $respone['EvnMediaData_FilePath'])) {
					$respone['File'] = base64_encode(file_get_contents(EVNMEDIAPATH . $respone['EvnMediaData_FilePath']));
				} else {
					$respone['File'] = null;
				}
			} else {
				unset($respone['EvnMediaData_FileName']);
			}
			unset($respone['EvnMediaData_FilePath']);
		}

		return $resp;
	}

	/**
	 * Метод получения файлов для API
	 */
	function addEvnMediaDataFromAPI($data) {
		$file_data = array(
			'Evn_id' => $data['Evn_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id'],
			'methodAPI' => true,
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$file = base64_decode($data['File']);

		$x = explode('.', $data['EvnMediaData_FileName']);
		$file_data['file_ext'] = end($x);

		$allowed_types = explode('|', 'pdf');
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			return array('Error_Msg' => 'Вы пытаетесь загрузить запрещенный тип файла.');
		}

		$file_data['orig_name'] = $data['EvnMediaData_FileName'];
		$file_data['file_name'] = md5($file_data['orig_name'] . time()) . '.' . $file_data['file_ext'];
		$file_data['description'] = '';
		file_put_contents(EVNMEDIAPATH . $file_data['file_name'], $file);

		return $this->saveEvnMediaData($file_data);
	}

	/**
	 * @return string
	 */
	function getUploadPath()
	{
		return './'.EVNMEDIAPATH;
	}

	/**
	 * Функция загрузки файла
	 * @param type $file
	 * @param type $data
	 * @return boolean
	 */
	function uploadFile($file, $data)   {
		
		$upload_path = $this->getUploadPath();
		
		$file_data = array();
		$val  = array();
		
		// Файл загружен?
		if ( !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
		{
			$error = ( ! isset($file['error'])) ? 4 : $file['error'];
			switch($error) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'Вы не выбрали файл для загрузки.';
					break;
			}
			return $this->createError(702,$message);
		}
		
		

		// Тип файла разрешен к загрузке?
		switch ($data['filterType']) {
			case 'image':
				// только картинки
				$allowed_types = explode('|','jpg|jpe|jpeg|png|bmp|tiff|tif|gif');
				break;
			case 'doc':
				//
				$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods|vef');
				break;
			default:
				// all
				$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods|vef|ecg|xml');
		}		
		$x = explode('.', $file['name']);
		$file_data['file_ext'] = end($x);



		if ( ! in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			return $this->createError(702,'Вы пытаетесь загрузить запрещенный тип файла.');
		}
		
		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path)) {
			@mkdir($upload_path);
		}
		if (!@is_dir($upload_path)) {
			return $this->createError(704, 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($upload_path)) {
			return $this->createError(705,'Директория, в которую загружается файл не имеет прав на запись.');
		}

		// Подготовка данных о файле
		$file_data['orig_name'] = $file['name'];
		$file_data['file_size'] = $file['size'];
		$file_data['Evn_id'] = $data['Evn_id'];
		$file_data['fromQueryEvn'] = $data['fromQueryEvn'];
		$file_data['Evn_sid'] = !empty($data['Evn_sid']) ? $data['Evn_sid'] : null;
		$file_data['description'] = !empty($data['FileDescr']) ? $data['FileDescr'] : '';
		$file_data['session'] = $data['session'];
		$file_data['pmUser_id'] = $data['pmUser_id'];
		$file_data['file_name'] = md5($file_data['orig_name'].time()).'.'.$file_data['file_ext'];
		$file_data['upload_dir'] = EVNMEDIAPATH;
		/*
		$http = (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])?$_SERVER['HTTP_X_FORWARDED_PROTO'].'://':'http://');
		$server_path = isset($_SERVER['HTTP_HOST']) ? $http.$_SERVER['HTTP_HOST'] : '';
		$file_data['upload_path'] = $server_path.'/'.IMPORTPATH_ROOT;
		на тестовом Уфы порт не включается в $_SERVER['HTTP_HOST'] и неправильный в $_SERVER['SERVER_PORT'] 
		*/
		
        if ( !empty($data['isForDoc']) ) {
            //Загрузка картинки для вставки в документ
            $filesize = filesize($file['tmp_name']);
            if (!$filesize) {
				return $this->createError(774,'Не удалось определить размер файла');
            }
            //Предельный размер 10 мб.
            if ($filesize > 10485760) {
				return $this->createError(775,'Размер файла превышает максимальный размер файла изображения');
            }
            //Ширина картинки устанавливается в зависимости от исходного размера:
            //Если ширина загружаемой картинки больше 700 px, 
            //то ширина устанавливается равной 100% от ширины документа ~ 90% от ширины белого листа ЭМК,
            //иначе ширина устанавливается равной ширине загружаемой картинки.
            $imageSize = @getimagesize($file['tmp_name']);
            if (!is_array($imageSize)) {
				return $this->createError(776, 'Это неправильное изображение');
            }
            if ($imageSize[0] > 700) {
                $file_data['width'] = '100%';
            } else {
                $file_data['width'] = $imageSize[0] . 'px';
            }
        }
		if ( move_uploaded_file ($file['tmp_name'], $upload_path . $file_data['file_name'])) {
			if (isset($data['saveOnce']) && $data['saveOnce'] == 'true') {
				//сохранение данных о файле в БД
				$saveMedisDataResponse = $this->saveEvnMediaData($file_data);
				//var_dump($saveMedisDataResponse);
				if (!empty($saveMedisDataResponse['Error_Msg'])) {
					return $this->createError(777,toUTF($saveMedisDataResponse['Error_Msg']));
				}
				$file_data['EvnMediaData_id'] = $saveMedisDataResponse['EvnMediaData_id'];
				$file_data['QueryEvn_id'] = $saveMedisDataResponse['QueryEvn_id'];
			}
			array_walk($file_data, 'ConvertFromWin1251ToUTF8');
			unset($file_data['session']);
			$files_data[] = $file_data;
			$val['data'] = json_encode($files_data);
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			return $val;
		} else {
			return $this->createError(706, toUTF('Невозможно скопировать файл в указанное место после его загрузки.'));
		}
		
	}

	/**
	 * Логика проверки из uploadFile вынесена в отдельную функцию для использования с каждым файлом в uploadSeveralFiles
	 *
	 * @param $file
	 * @param $data
	 * @return bool|string
	 */
	function checkFile($file, $data)
	{
		// Файл загружен?
		if ( ! isset($file['tmp_name']) || ! is_uploaded_file($file['tmp_name']))
		{
			$error = ( ! isset($file['error']) ) ? 4 : $file['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'Вы не выбрали файл для загрузки.';
					break;
			}

			return $message;
		}

		// Тип файла разрешен к загрузке?
		switch ($data['filterType'])
		{
			case 'image':
				// только картинки
				$allowed_types = explode('|','jpg|jpe|jpeg|png|bmp|tiff|tif|gif');
				break;
			case 'doc':
				//
				$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods|vef');
				break;
			default:
				// all
				$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods|vef|ecg|xml');
		}

		$name = explode('.', $file['name']);
		$x = end($name);

		if ( ! in_array(strtolower($x), $allowed_types) )
		{
			return 'Вы пытаетесь загрузить запрещенный тип файла: .' . $x;
		}


		if ( !empty($data['isForDoc']) )
		{
			//Загрузка картинки для вставки в документ
			$filesize = filesize($file['tmp_name']);
			if ( ! $filesize)
			{
				return 'Не удалось определить размер файла';
			}
			//Предельный размер 10 мб.
			if ($filesize > 10485760)
			{
				return 'Размер файла превышает максимальный размер файла изображения';
			}

		}

		return true;
	}



	/**
	 * Обработка нескольких файлов при загрузки. Если часть файлов не сохранится, об этом придет сообщение на форму.
	 * Остальные файлы будут сохранены
	 *
	 * @param $data
	 */
	function uploadSeveralFiles($files, $data)
	{
		$upload_path = $this->getUploadPath();
		$uploadedFiles = array();
		$errMsg = array();
		$EvnMediaDataIds = array();


		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path)) {
			@mkdir($upload_path);
		}
		if (!@is_dir($upload_path)) {
			return $this->createError(704, 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($upload_path))
		{
			return $this->createError(705,'Директория, в которую загружается файл не имеет прав на запись.');
		}



		foreach ($files as $key => $file)
		{
			$file_data = array();

			if ( ($msg = $this->checkFile($file, $data)) !== true)
			{
				$errMsg[] = $file['name'] . ': ' . $msg;
				continue;
			}


			if ( ! empty($data['isForDoc']) )
			{
				//Ширина картинки устанавливается в зависимости от исходного размера:
				//Если ширина загружаемой картинки больше 700 px,
				//то ширина устанавливается равной 100% от ширины документа ~ 90% от ширины белого листа ЭМК,
				//иначе ширина устанавливается равной ширине загружаемой картинки.
				$imageSize = @getimagesize($file['tmp_name']);

				if ($imageSize[0] > 700)
				{
					$file_data['width'] = '100%';
				} else
				{
					$file_data['width'] = $imageSize[0] . 'px';
				}
			}

			// Подготовка данных о файле
			$x = explode('.', $file['name']);

			$file_data['file_ext'] = end($x);
			$file_data['orig_name'] = $file['name'];
			$file_data['file_size'] = $file['size'];
			$file_data['Evn_id'] = $data['Evn_id'];
			$file_data['Evn_sid'] = !empty($data['Evn_sid']) ? $data['Evn_sid'] : null;
			$file_data['description'] = !empty($data['FileDescr']) ? $data['FileDescr'] : '';
			$file_data['session'] = $data['session'];
			$file_data['pmUser_id'] = $data['pmUser_id'];
			$file_data['file_name'] = md5($file_data['orig_name'].time()).'.'.$file_data['file_ext'];
			$file_data['upload_dir'] = EVNMEDIAPATH;

			$file_data['EvnMediaData_FileName'] = $file['name'];
			$file_data['EvnMediaData_Comment'] = !empty($data['FileDescr']) ? $data['FileDescr'] : '';
			$file_data['EvnMediaData_FilePath'] = EVNMEDIAPATH . $file_data['file_name'];

			if ( move_uploaded_file($file['tmp_name'], $upload_path . $file_data['file_name']) )
			{
				//$uploadedFiles[$key] = array_walk($file_data, 'ConvertFromWin1251ToUTF8');
			} else
			{
				$errMsg[] = $file_data['orig_name'] . ': ' . toUTF('Невозможно скопировать файл в указанное место после его загрузки.');
				continue;
			}

			$saveMedisDataResponse = $this->saveEvnMediaData($file_data);

			if ( ! empty($saveMedisDataResponse['Error_Msg']) )
			{
				$errMsg[] = $file_data['orig_name'] . ': ' . $saveMedisDataResponse['Error_Msg'];
				//unset($uploadedFiles[$key]);
				continue;
				//return $this->createError(777,toUTF($saveMedisDataResponse['Error_Msg']));
			} else
			{
				$uploadedFiles[$key] = $file_data;
				$uploadedFiles[$key]['EvnMediaData_id'] = $saveMedisDataResponse['EvnMediaData_id'];
				$EvnMediaDataIds[] = $saveMedisDataResponse['EvnMediaData_id'];
			}
		}

		return array('success' => ! ( (bool) count($errMsg) ), 'Error_Msg' => implode('|', $errMsg), 'Error_Code' => null, 'data' => json_encode($uploadedFiles), 'EvnMediaDataIds' => json_encode($EvnMediaDataIds));
	}

	/**
	 * Сохранение записи
	 * @param $data
	 * @return array
	 */
	function saveEvnMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['EvnMediaData_id']) ) {
			$procedure = 'p_EvnMediaData_ins';
		}
		else {
			$procedure = 'p_EvnMediaData_upd';
		}

		$isLisEvn = false;
		$EvnClass_SysNick = $this->getFirstResultFromQuery("
			select top 1 EvnClass_SysNick from v_Evn (nolock) where Evn_id = :Evn_id
		", $data, true);
		if ($EvnClass_SysNick === false) {
			return array('Error_Code' => '', 'Error_Msg' => 'Ошибка при получнии класса события');
		}
		if (empty($EvnClass_SysNick) && $this->usePostgreLis) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('Evn/EvnClassSysNick', $data, 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			if (!empty($res)) {
				$EvnClass_SysNick = $res['EvnClass_SysNick'];
				$isLisEvn = true;
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMediaData_id;
			exec " . $procedure . "
				@EvnMediaData_id = @Res output,				
				@Evn_id = :Evn_id,
				@Evn_sid = :Evn_sid,
				@EvnMediaData_FileName = :EvnMediaData_FileName,
				@EvnMediaData_FilePath = :EvnMediaData_FilePath,
				@EvnMediaData_Comment = :EvnMediaData_Comment,				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg, @Res as EvnMediaData_id;
		";

		$queryParams = array(
			'EvnMediaData_id' => isset($data['EvnMediaData_id']) ? $data['EvnMediaData_id'] : null,
			'Evn_id' => $data['Evn_id'],
			'Evn_sid' => !empty($data['Evn_sid']) ? $data['Evn_sid'] : null,
			'EvnMediaData_FileName' => $data['orig_name'],
			'EvnMediaData_FilePath' => $data['file_name'],
			'EvnMediaData_Comment' => (!empty($data['description'])?$data['description']:''),
			'pmUser_id' => $data['pmUser_id']
		);
		//print getDebugSql($query, $queryParams); die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0])) {
				// если это параклиническая услуга, то нужно проставить дату выполнения
				if ($EvnClass_SysNick == 'EvnUslugaPar') {
					$params = [
						'EvnUslugaParChanged' => array($data['Evn_id']),
						'session' => $data['session'],
						'pmUser_id' => $data['pmUser_id'],
						'MedPersonal_id' => (!empty($data['MedPersonal_id'])) ? $data['MedPersonal_id'] : null,
						'methodAPI' => (!empty($data['methodAPI'])) ? $data['methodAPI'] : null
					];
					if ($isLisEvn && $this->usePostgreLis) {
						$params['EvnUslugaParChanged'] = json_encode($params['EvnUslugaParChanged']);
						$this->load->swapi('lis');
						$this->lis->POST('EvnLabSample/OnChangeApproveResults', $params);
					} else {
						$this->load->model('EvnLabSample_model');
						$this->EvnLabSample_model->onChangeApproveResults($params);
					}
				}
				$res[0]['QueryEvn_id'] = $this->addToQueryEvn($data, $res[0]);
				return $res[0];
			} else {
				return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
			}
		} else {
			return array('Error_Code' => '', 'Error_Msg' => 'Не удалось сохранить информацию о файле.');
		}
	}
	
	/**
	 * Проверяет, есть ли активный запрос по данному случаю, и привязывает файлы при необходимости
	 */
	function addToQueryEvn($data, $res) {
		if (empty($res['EvnMediaData_id'])) return false;
		//$data['pmUser_id'] = 229523552;
		
		$qe = $this->getFirstRowFromQuery("
			select qe.QueryEvn_id, qeu.QueryEvnUser_id
			from QueryEvn qe (nolock) 
			inner join QueryEvnUser qeu (nolock) on 
				qeu.QueryEvn_id = qe.QueryEvn_id and 
				qeu.QueryEvnUserType_id in(2,3) and 
				qeu.QueryEvnUser_endDate is null and 
				qeu.pmUser_rid = :pmUser_id
			where 
				Evn_id = :Evn_id and 
				qe.QueryEvnStatus_id = 1
		", array(
			'Evn_id' => $data['Evn_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if (!$qe) return false;
		
		// загрузка не из формы запроса - не прикрепляем, но спрашиваем
		if (empty($data['fromQueryEvn']) || $data['fromQueryEvn'] != 2) {
			return $qe['QueryEvn_id'];
		}
		
		$msg = $this->getFirstRowFromQuery("
			select top 1
				qem.QueryEvnMessage_id,
				qem.QueryEvnMessage_Text,
				qem.QueryEvnUser_id
			from QueryEvnMessage qem (nolock) 
			where 
				qem.QueryEvn_id = :QueryEvn_id and 
				qem.pmUser_insID = :pmUser_id
		", array(
			'QueryEvn_id' => $qe['QueryEvn_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if (!$msg) {
			$sql = "
				declare
					@QueryEvnMessage_id bigint = null,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_QueryEvnMessage_ins
					@QueryEvnMessage_id = @QueryEvnMessage_id output,
					@QueryEvn_id = :QueryEvn_id,
					@QueryEvnUser_id = :QueryEvnUser_id,
					@QueryEvnMessage_Text = ' ',
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @QueryEvnMessage_id as QueryEvnMessage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
			$tmp = $this->queryResult($sql, array(
				'QueryEvn_id' => $qe['QueryEvn_id'],
				'QueryEvnUser_id' => $qe['QueryEvnUser_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			$msg['QueryEvnMessage_id'] = $tmp[0]['QueryEvnMessage_id'];
		}
		
		$sql = "
			declare
				@QueryEvnMessageFile_id bigint = null,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_QueryEvnMessageFile_ins
				@QueryEvnMessageFile_id = @QueryEvnMessageFile_id output,
				@QueryEvnMessage_id = :QueryEvnMessage_id,
				@EvnMediaData_id = :EvnMediaData_id,
				@EvnXml_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @QueryEvnMessageFile_id as QueryEvnMessageFile_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$this->queryResult($sql, array(
			'QueryEvnMessage_id' => $msg['QueryEvnMessage_id'],
			'EvnMediaData_id' => $res['EvnMediaData_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
			
		return $qe['QueryEvn_id'];
	}

	/**
	 * Связывает файлы, загруженные на форме добавления общей услуги, с услугой, когда она сохраняется
	 *
	 * @param $data
	 * @return array|void
	 * @throws Exception
	 */
	function linkFilesToEvn($data)
	{
		$EvnMediaDataIds = json_decode($data['EvnMediaDataIds'], true);

		if ( ! is_array($EvnMediaDataIds) || count($EvnMediaDataIds) === 0)
		{
			return;
		}

		$results = array();
		$success = 1;

		$query = "
				Declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '',
					@Evn_id bigint = :Evn_id,
					@EvnMediaData_id bigint =  :EvnMediaData_id;

				set nocount on

				begin try
					update dbo.EvnMediaData with (rowlock)
					set
						Evn_id = @Evn_id,
						EvnMediaData_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						EvnMediaData_id = @EvnMediaData_id
				end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
				set nocount off
				Select @EvnMediaData_id as EvnMediaData_id, @Error_Code as Error_Code, @Error_Message as Error_Msg";
		$session_params = $this->getSessionParams();
		foreach ($EvnMediaDataIds as $EvnMediaData_id)
		{
			$results[$EvnMediaData_id] = $this->getFirstRowFromQuery($query, array('EvnMediaData_id' => $EvnMediaData_id, 'Evn_id' => $data['Evn_id'], 'pmUser_id' => $session_params['pmuser_id']));
			$success *= (int) is_numeric($results[$EvnMediaData_id]['EvnMediaData_id']);
		}

		return array(array('success' => (bool) $success, 'results' => json_encode($results)));
	}

	/**
	 * Удаление записи
	 * @param $data
	 * @return array|bool
	 */
	function deleteEvnMediaData($data) {
		$procedure = '';
		
		if ( !isset($data['EvnMediaData_id']) ) {
			return false;
		}
		
		$tmp = $this->queryList("select QueryEvnMessageFile_id from QueryEvnMessageFile (nolock) where EvnMediaData_id = ?", array($data['EvnMediaData_id']));
		foreach($tmp as $qemf) {
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_QueryEvnMessageFile_del
					@QueryEvnMessageFile_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($qemf));
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Evn_id bigint;
			set @Res = :EvnMediaData_id;
			
			select
				@Evn_id = emd.Evn_id
			from
				v_EvnMediaData emd (nolock)
			where
				emd.EvnMediaData_id = @Res;
			
			exec p_EvnMediaData_del 
				@EvnMediaData_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;				
			select @Res as EvnMediaData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg, @Evn_id as Evn_id;
		";

		$queryParams = array(
			'EvnMediaData_id' => isset($data['EvnMediaData_id']) ? $data['EvnMediaData_id'] : null
		);
		//echo getDebugSql($query, $queryParams);exit;
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении файла');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$isLisEvn = false;
		$EvnClass_SysNick = $this->getFirstResultFromQuery("
				select top 1 EvnClass_SysNick from v_Evn (nolock) where Evn_id = :Evn_id
			", $resp[0], true);
		if ($EvnClass_SysNick === false) {
			return array('Error_Code' => '', 'Error_Msg' => 'Ошибка при получнии класса события');
		}
		if (empty($EvnClass_SysNick) && $this->usePostgreLis) {
			$this->load->swapi('lis');
			$res = $this->lis->GET('Evn/EvnClassSysNick', $data, 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			if (!empty($res)) {
				$EvnClass_SysNick = $res['EvnClass_SysNick'];
				$isLisEvn = true;
			}
		}

		if ($EvnClass_SysNick == 'EvnUslugaPar') {
			$params = [
				'EvnUslugaParChanged' => array($resp[0]['Evn_id']),
				'session' => $data['session'],
				'pmUser_id' => $data['session']['pmuser_id']
			];
			if ($isLisEvn && $this->usePostgreLis) {
				$params['EvnUslugaParChanged'] = json_encode($params['EvnUslugaParChanged']);
				$this->load->swapi('lis');
				$this->lis->POST('EvnLabSample/OnChangeApproveResults', $params);
			} else {
				$this->load->model('EvnLabSample_model');
				$this->EvnLabSample_model->onChangeApproveResults($params);
			}
		}
		return $resp;
	}

	/**
	 * Получение данных одной записи
	 * @param $data
	 * @return array|bool
	 */
	function getEvnMediaData($data) {
		$query = "
			select
				EvnMediaData_id,
				Evn_id,
				Evn_sid,
				EvnMediaData_FileName,
				EvnMediaData_FilePath,
				EvnMediaData_Comment
			from
				v_EvnMediaData (nolock)
			where
				EvnMediaData_id = :EvnMediaData_id
			order by
				EvnMediaData_insDT
			";
		$result = $this->db->query($query,  array('EvnMediaData_id' => $data['EvnMediaData_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка для грида
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnMediaFilesListGrid($data) {
		$filters = '(1 = 1)';
		$params = array();
		if((!isset($data['Evn_id'])||empty($data['Evn_id']))&&(!isset($data['EvnXml_id'])||empty($data['EvnXml_id']))){
			return false;
		}
		if(isset($data['Evn_id'])) {
			$params['Evn_id'] = $data['Evn_id'];
			$filters .= '
				and EMD.Evn_id = :Evn_id';
		}
		
		if(isset($data['EvnXml_id']) && empty($data['Evn_id'])) {
			// Выводим то, что прикреплено к ТАП, КВС для возможности вставки в документ
			$params['EvnXml_id'] = $data['EvnXml_id'];
			$filters .= '
				and EMD.Evn_id = (select Evn.Evn_rid from v_EvnXml DOC with (nolock) inner join v_Evn Evn with (nolock) on Evn.Evn_id = DOC.Evn_id where DOC.EvnXml_id = :EvnXml_id)';
		}
		
		switch ($data['filterType']) {
			case 'image':
				// только картинки jpg|jpe|jpeg|png|bmp|tiff|tif|gif
				$filters .= "
				and (EMD.EvnMediaData_FilePath like '%.jp%' or EMD.EvnMediaData_FilePath like '%png'  or EMD.EvnMediaData_FilePath like '%bmp'  or EMD.EvnMediaData_FilePath like '%gif' or EMD.EvnMediaData_FilePath like '%.tif%')";
				break;
			case 'doc':
				//
				break;
			default:
				// all
		}
		
		$query = '
			select
				EMD.EvnMediaData_id,
				EMD.EvnMediaData_FileName,
				EMD.EvnMediaData_FilePath,
				EMD.EvnMediaData_Comment,
				\'saved\' as state
			from
				EvnMediaData EMD with (nolock)
			where
				'.$filters.'
			order by
				EMD.EvnMediaData_insDT
		';
		$result = $this->db->query($query,  $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка для раздела "Файлы" в панели просмотра ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getEvnMediaDataViewData($data) {
		$queryParams = array();
		if (isset($data['EvnMediaData_pid']))
		{
			$filter = 'Evn_id = :Evn_id';
			$queryParams['Evn_id'] = $data['EvnMediaData_pid'];
		}
		else
		{
			$filter = 'EvnMediaData_id = :EvnMediaData_id';
			$queryParams['EvnMediaData_id'] = $data['EvnMediaData_id'];
		}
		$query = "
			select
				EvnMediaData_id,
				Evn_id as EvnMediaData_pid,
				EvnMediaData_FileName,
				EvnMediaData_FilePath,
				'" . EVNMEDIAPATH . "' as EvnMediaData__Dir, -- часть url пути к файлу на сервере без имени файла
				-- если все файлы будут в одной папке, то папку (EvnMediaData__Dir) можно будет прописать в шаблоне eew_file_list_item.php
				-- или если FilePath будет полным путем к файлу, то EvnMediaData__Dir будет не нужна
				EvnMediaData_Comment,
				pmUser_insID,
				0 as Children_Count
			from
				EvnMediaData with (nolock)
			where
				{$filter}
			order by
				EvnMediaData_insDT
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Метода получения записи в EvnMediaData по идентификатору и имени одновременно (для верификации)
	 * @param type $data
	 * @return type
	 */
	public function getEvnMediaDataByNameAndId($data) {
		$rules = array(
			array('field' => 'EvnMediaData_id', 'label' => 'Идентификатор файла', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'fileName', 'label' => 'Имя файла', 'rules' => 'required', 'type' => 'string'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				EMD.EvnMediaData_FileName
			FROM
				v_EvnMediaData EMD with (nolock)
			WHERE
				EMD.EvnMediaData_id = :EvnMediaData_id
				AND EMD.EvnMediaData_FilePath like :fileName
			";
		

		return $this->queryResult($query,$queryParams);
	}

	/**
	 *  Получение списка файлов в ЭМК
	 */
	function loadEvnMediaDataPanel($data) {
		$sql = "
			select
				-- select
				emd.Evn_id,
				emd.EvnMediaData_id,
				emd.EvnMediaData_FileName,
				'" . EVNMEDIAPATH . "' + emd.EvnMediaData_FilePath as EvnMediaData_FilePath,
				emd.EvnMediaData_Comment,
				convert(varchar(10), emd.EvnMediaData_insDT, 104) as EvnMediaData_insDT
				-- end select
			from
				-- from
				v_EvnMediaData emd (nolock),
				v_Evn e (nolock)
				-- end from
			where
				-- where
				e.Person_id = :Person_id
				and
				emd.Evn_id = e.Evn_id
				-- end where
			order by
				-- order by
				emd.EvnMediaData_insDT DESC
				-- end order by
		";
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $data);
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			foreach($response['data'] as $key => $file){
				if(!empty($file['EvnMediaData_FileName'])){
					// Оставлю первый вариант, если вдруг вариант с регуляркой будет медленным/ошибочным
					/*$arrExt = explode(".", $file['EvnMediaData_FileName']);
					if(!empty($arrExt) && count($arrExt)>0)
						$response['data'][$key]['EvnMediaData_Extension'] = $arrExt[count($arrExt)-1];*/
					preg_match('/.+\.(\w+)$/xis', $file['EvnMediaData_FileName'], $arrExt);
					$response['data'][$key]['EvnMediaData_Extension'] = $arrExt[1];
				}

			}
			$response['totalCount'] = count($response['data']) + intval($data['start']);
			return $response;
		} else {
			return false;
		}
	}
}