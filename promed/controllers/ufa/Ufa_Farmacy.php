<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Farmacy - методы работы для модуля "Аптека"

*/
require_once(APPPATH.'controllers/Farmacy.php');

class Ufa_Farmacy extends Farmacy {
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
	}
	
	
	/**
	 * comment
	 */
	function object_to_array($obj) {
		if (is_object($obj))
			$obj = (array) $obj;

		if (is_array($obj)) {
			$new = array();

			foreach ($obj as $key => $val) {
				if (is_object($obj)) {
					$new[$key] = $this->object_to_array($val);
				} else {
					$new[$key] = $val;
				}
			}
		} else
			$new = $obj;
		return $new;
	}

	/**
     * Загрузка контрактов
     */
    function Contract_importXml()
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", "0");
        ini_set("max_input_time", "0");
        ini_set("post_max_size", "220");
        ini_set("default_socket_timeout", "999");
        ini_set("upload_max_filesize", "220M");
        /**/
        
        $dirMCE        = 'mce';
        $upload_path   = './' . IMPORTPATH_ROOT . $dirMCE;
        
        $allowed_types = explode('|', 'zip');  
        
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }        

        if (!file_exists($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Ошибка загрузки файлов в директорию.')
            )));
        }

        if (!isset($_FILES['Contract_import'])) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100011,
                'Error_Msg' => toUTF('Не выбран файл для импорта!')
            )));
        }
        
        if (!is_uploaded_file($_FILES['Contract_import']['tmp_name'])) {
            $error = (!isset($_FILES['Contract_import']['error'])) ? 4 : $_FILES['Contract_import']['error'];
            switch ($error) {
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
                default:
                    $message = 'При загрузке файла произошла ошибка.';
                    break;
            }
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100012,
                'Error_Msg' => toUTF($message)
            )));
        }
        
        
        // Тип файла разрешен к загрузке?
        $x  = explode('.', $_FILES['Contract_import']['name']);
        $file_data['file_ext'] = end($x);
        if (!in_array(mb_strtolower($file_data['file_ext']), $allowed_types)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100013,
                'Error_Msg' => toUTF('Данный тип файла не разрешен.')
            )));
        }
        
        // Правильно ли указана директория для загрузки?
        if (!@is_dir($upload_path)) {
            mkdir($upload_path);
        }
        
        if (!@is_dir($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100014,
                'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')
            )));
        }
        
        // Имеет ли директория для загрузки права на запись?
        if (!is_writable($upload_path)) {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 100015,
                'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')
            )));
        }
		
        $zip = new ZipArchive;
        
        if ($zip->open($_FILES['Contract_import']['tmp_name']) === TRUE) {
            $zip->extractTo($upload_path);
            
            $zip->close();
            
            $files = scandir($upload_path);
            
            $data = array();
            
            

            $result = array();
            
            $count = glob($upload_path.'/*.xml');

            if (sizeof($count) > 200) {
                foreach ($files as $k => $filename) {
                    $file = iconv('CP866', 'utf-8', $upload_path . '/' . $filename);
                    if (!in_array($filename, array(
                        '.',
                        '..'
                    ))) {
                        @unlink($file);
                    }
                }
                
                exit(json_encode(array(
                    'success' => false,
                    'Error_Code' => 100014,
                    'Error_Msg' => toUTF('Привышен лимит импорта. Максимальный размер архива 200 XML файлов')
                )));
            }
            
            $files = array_reverse($files);
            $count_xml = 0;
            foreach ($files as $k => $filename) {
                $file_info = pathinfo($filename);
                
                if (!in_array($filename, array('.','..')) && isset($file_info['extension']) && $file_info['extension'] == 'xml') {
                    $count_xml++;
                    $griddata = array();
                    $file     = $upload_path . '/' . $filename;

                    if (@file_get_contents($file) === false) {
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Некорректное содержание архива. Ожидаются XML файлы!!!!.')
                        )));
                    } else {
                        $xmlstr = file_get_contents($file);
                    }
                    
                    $xmlstr = strtr($xmlstr, array(
                        'ct:' => ''
                    ));
                    
                    try{
                        $xml = @new SimpleXMLElement($xmlstr);
                        
                    }
                    catch(Exception $e){
                        @unlink($file);
                        exit(json_encode(array(
                            'success' => false,
                            'Error_Code' => 100014,
                            'Error_Msg' => toUTF('Невалидный XML файл: '.$filename),
                            'Error_Txt' => $e->getMessage(),
                        )));                        
                    }

                    $xml_arr = $this->object_to_array($xml);
                    
					$arr = array();
					foreach ($this->object_to_array($xml_arr['Row']) as $j => $row) {   
						$r =  $this->object_to_array($row);
						//$item = array();
						foreach ($r as $key => $v) { 
							$tmp = $this->object_to_array($v[$key]);
							if (is_array($tmp)) {
								//$item[$key] = $tmp[0];
								$arr [$j][$key] = $tmp[0];
					}
							else {
								//$item[$key] = $tmp;
								$arr [$j][$key] = $tmp;
							}
						}						
					}
					
					$response = $this->dbmodel->Contract_importXml($arr);

                    @unlink($file);
					
					echo json_encode(array(
						'success' => true,
						'Cnt' => $response[0]['Cnt'],
						'data' => 'Операция успешно завершена!'
            ));
					
                }
            }
        } 
        else {
            exit(json_encode(array(
                'success' => false,
                'Error_Code' => 10002,
                'Error_Msg' => toUTF('Данный тип сжатия файлов не поддерживается.')
            )));
        }
    }
    
        
        
}
