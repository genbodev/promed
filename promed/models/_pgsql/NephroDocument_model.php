<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * NephroDocument_model - модель "Установка коммиссии МЗ"
 *
 * @package      NephroDocument
 * @access       public
 * @copyright    Copyright (c) Emsis.
 * @author       Salavat Magafurov
 * @version      07.2018
 */

class NephroDocument_model extends swPgModel
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка правил
	 * @param string $name
	 */
	function getInputRules($name) {
		$rules = array();
		switch ($name) {
			case 'uploadFile':
					$rules['MorbusNephro_id'] = array(
						'field' => 'MorbusNephro_id',
						'label' => 'Ид записи в регистре',
						'rules' => 'required',
						'type' => 'int'
					);
					$rules['FileDescr'] = array(
						'field' => 'FileDescr',
						'label' => 'Примечание',
						'rules' => '',
						'type' => 'string'
					);
				break;
			case 'getDocument':
					$rules[self::ID_KEY] = array(
						'field' => 'NephroDocument_id',
						'label' => 'Идентификатор записи',
						'rules' => 'required',
						'type' => 'int'
					);
				break;
			}
		return $rules;
	}

	/**
	 * Загрузка списка наименований файлов
	 * @param array $data
	 */
	function loadViewData($data) {

		$params = array(
			'MorbusNephro_id' => $data['MorbusNephro_id'],
			'MorbusNephro_pid' => $data['MorbusNephro_pid']
		);
		$query = "
			select 
				case when MN.Morbus_disDT is null
					then 'edit'
					else 'view'
				end as \"accessType\",
				ND.NephroDocument_id as \"NephroDocument_id\",
				ND.NephroDocument_filename as \"NephroDocument_filename\",
				ND.NephroDocument_description as \"NephroDocument_description\",
				:MorbusNephro_id as \"MorbusNephro_id\",
				:MorbusNephro_pid as \"MorbusNephro_pid\"
			from r2.v_NephroDocument ND
			left join v_MorbusNephro MN on MN.MorbusNephro_id = ND.MorbusNephro_id
			where ND.MorbusNephro_id = :MorbusNephro_id
			order by 
				ND.NephroDocument_insDT desc
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 * Функция загрузки файла
	 * @param type $file
	 * @param type $data
	 * @return array
	 */
	function uploadFile($file, $data)   {
		$upload_path = './'.IMPORTPATH_ROOT;
		
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
		/*switch ($data['filterType']) {
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
				$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods|vef');
		}		*/
		$x = explode('.', $file['name']);
		$file_data['file_ext'] = end($x);
		//if ( ! in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
		//	return $this->createError(702,'Вы пытаетесь загрузить запрещенный тип файла.');
		//}
		
		// Правильно ли указана директория для загрузки?
		if ( ! @is_dir($upload_path)) {
			return $this->createError(704,'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($upload_path)) {
			return $this->createError(705,'Директория, в которую загружается файл не имеет прав на запись.');
		}
		$filehandle = fopen($file['tmp_name'], "rb") or die( "Can't open file!" );
		$file['content'] = base64_encode(fread($filehandle, filesize($file['tmp_name'])));
		fclose($filehandle);
		unlink($file['tmp_name']);

		if(!empty($data)) {
			$params = array();
			$params['MorbusNephro_id'] = $data['MorbusNephro_id'];
			$params['NephroDocument_content'] = $file['content'];
			$params['NephroDocument_size'] = $file['size'];
			$params['NephroDocument_filename'] = $this->transliterate($file['name']);
			$params['NephroDocument_type'] = $file['type'];
			$params['NephroDocument_description'] = $data['FileDescr'];
			$params['pmUser_id'] = $_SESSION['pmuser_id'];

			return $this->NephroDocument_ins($params);
		} else {
			return $this->createError(706, toUTF('Пустое содержимое файла.'));
		}
		
	}

	/**
	 * Сохранение файла
	 * @param array $param
	 */
	function NephroDocument_ins($params) {
		$query = "
			select
				NephroDocument_id as \"data\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r2.p_NephroDocument_ins(
				MorbusNephro_id := :MorbusNephro_id,
				NephroDocument_content := :NephroDocument_content,
				NephroDocument_type := :NephroDocument_type,
				NephroDocument_size := :NephroDocument_size,
				NephroDocument_filename := :NephroDocument_filename,
				NephroDocument_description := :NephroDocument_description,
				pmUser_id := :pmUser_id
			);
		";

		$result = $this->db->query($query,$params);

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение файла
	 * @param type $data
	 * @return array
	 */
	function getDocument($data){

		$params = array();
		$params['NephroDocument_id'] = $data['NephroDocument_id'];

		$query = "
			select 
				NephroDocument_filename as \"filename\",
				NephroDocument_type as \"type\",
				NephroDocument_size as \"size\",
				NephroDocument_content as \"content\"
			from r2.NephroDocument
			where NephroDocument_id = :NephroDocument_id
		";

		$result = $this->db->query($query,$params);

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Транслитерация из кирилицы в латиницу и наоборот
	 * @param string $textcyr
	 * @param string $textlat
	 * @return string
	 */
	function transliterate($textcyr = null, $textlat = null) {
		$cyr = array(
			 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'щ',  'ш', 'ъ', 'ы', 'ь','э','ю', 'я',
			 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё' , 'Ж','З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Щ',  'Ш', 'Ъ', 'Ы', 'Ь','Э','Ю', 'Я', ' ');
		$lat = array(
			 'a', 'b', 'v', 'g', 'd', 'e', 'yo','zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c','ch','sht','sh', '', 'y', '', 'e','yu','ya',
			 'A', 'B', 'V', 'G', 'D', 'E', 'Yo','Zh', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c','Ch','Sht','Sh', '', 'Y', '', 'e','Yu','Ya','_');
		if($textcyr) return str_replace($cyr, $lat, $textcyr);
		else if($textlat) return str_replace($lat, $cyr, $textlat);
		else return null;
	}
}