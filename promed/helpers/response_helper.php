<?php
/**
* Response_helper - хелпер, для возврата стандартного ответа на запрос
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcin Ivan aka IVP (ipshon@rambler.ru)
* @version      24.08.2009
*/
// константы
define ("ERROR_NO_ERROR_CODE", -1);
define ("ERROR_NO_ERROR_MSG", "");

define ("ERROR_FALSE_CODE", 0);
define ("ERROR_FALSE_MSG", "Во время выполнения операции произошла  ошибка.");

/**
 * функция, принимающая данные для возврата клиенту, обрабатывающая их и возвращающая результат в JSON-формате
 * входные параметры:
 * $responseData: данные, для передачи, может быть массив, содержащий success, Error_Code, Error_Msg, возвращаемые данные или быть true или false или пустой строкой.
 * $cancelErrorHandle - если, true, то отменяет проверку ошибок на клиенте перед передачей в callback вызывающей функции, по умолчанию false.
 */
function returnResponseToClient($responseData, $start = NULL, $limit = NULL, $cancelErrorHandle = false)
{
	// формат возвращаемого ответа:
	//					 array("success"=>[true/false],
	//					       "Error_Code"=>[код ошибки, или -1 если ошибки нет],
	//                         "Error_Msg"=>[сообщение об ошибке, или пусто],
	//                         "Response_Data"=>[собственно данные, которые возвращаются]
	//                   )
	// массив, содержащий информацию об успехе, код ошибки, сообщение и данные,
	// которые будет отправляться клиенту

	$response_array = array();
	// сначала проверяем, возвращается ли просто булево значение
	if ( $responseData === true )
	{
		$response_array["success"] = true;
		$response_array["Error_Code"] = ERROR_NO_ERROR_CODE;
		$response_array["Error_Msg"] = ERROR_NO_ERROR_MSG;
		$response_array["Response_Data"] = true;
		$response_array["Cancel_Error_Handle"] = $cancelErrorHandle;
	}

	if ( $responseData === false )
	{
		$response_array["success"] = false;
		$response_array["Error_Code"] = ERROR_FALSE_CODE;
		$response_array["Error_Msg"] = ERROR_FALSE_MSG;
		$response_array["Response_Data"] = false;
		$response_array["Cancel_Error_Handle"] = $cancelErrorHandle;
	}

	// массив
	if ( is_array($responseData) )
	{
		// проверяем, передается ли уже Success, ErrorCode, ErrorMsg
		if ( array_key_exists("success", $responseData) )
			$response_array["success"] = $responseData["success"];
		else
		    $response_array["success"] = true;

		if ( array_key_exists("Error_Code", $responseData) )
			$response_array["Error_Code"] = $responseData["Error_Code"];
		else
			$response_array["Error_Code"] = ERROR_NO_ERROR_CODE;

		if ( array_key_exists("Error_Msg", $responseData) )
			$response_array["Error_Msg"] = $responseData["Error_Msg"];
		else
			$response_array["Error_Code"] = ERROR_NO_ERROR_MSG;
		if ( !isset($responseData['data']) )
		{
			if ($start == null && $limit == null) {
				$response_array["Response_Data"]["data"] = $responseData;
			}
			else {
				$response_array["Response_Data"]["data"] = array_slice($responseData, $start, $limit);
			}
			$response_array["Response_Data"]["count"] = count($responseData);
		}
		else
		{
			$response_array["Response_Data"]["data"] = $responseData["data"];
			if ( isset($responseData["count"]) )
				$response_array["Response_Data"]["count"] = $responseData["count"];
		}
		$response_array["Cancel_Error_Handle"] = $cancelErrorHandle;
	}

	// если пустая строка
	if ( $responseData == "" )
	{
		$response_array["success"] = true;
		$response_array["Error_Code"] = ERROR_NO_ERROR_CODE;
		$response_array["Error_Msg"] = ERROR_NO_ERROR_MSG;
		$response_array["Response_Data"] = "";
		$response_array["Cancel_Error_Handle"] = $cancelErrorHandle;
	}
	echo iconv("windows-1251", "UTF-8", json_encode($response_array));
}

/**
 * Класс хелпер для отдачи json - данных клиенту.
 * Почти все методы возвращают this и можно строить цепочку вызовов
 * например ->clear()->set('total',100)->set('rows',$data)->utf()->json()
 */
class JsonResponce {

    private $content = array();

	/**
	 * Description
	 */
    function  __construct($content = array()) {
        $this->content = $content;
    }

	/**
	 * Description
	 */
    public function set($key,$value){
        $this->content[$key] = $value;
        return $this;
    }

	/**
	 * Description
	 */
    public function get(){
        return $this->content;
    }

	/**
	 * Description
	 */
    public function clear(){
        $this->content = array();
        return $this;
    }

	/**
	 * Description
	 */
    public function success($value){
        $this->set('success',$value);
        return $this;
    }

	/**
	 * Description
	 */
    public function toStore($array,$totalName = 'total',$itemsName = 'items'){
        $this->set($totalName,count($array));
        $this->set($itemsName,$array);
        return $this;
    }
    /**
     * Хелпер функция для ТreeLoader
     * @param <type> $array - массив с данными из БД
     * @param <type> $idPrefix - префикс для автоматического добавления к id ноды
     * @param <type> $idColumn - имя колонки с id
     * @param <type> $titleColumn - имя колонки для text
     * @param <type> $defaults - хэш для добавления в атрибуты ноды
     */
    public function toTree($array,$idPrefix ='',$idColumn = 'id',$titleColumn = 'text',$defaults = null){
        foreach($array as $value){
            $temp = $value;
            $temp['id'] = $idPrefix.$value[$idColumn];
            $temp['text'] = $value[$titleColumn];
            if($defaults){
                foreach($defaults as $dkey=>$dvalue){
                	if (empty($temp[$dkey])) {
                		$temp[$dkey] = $dvalue;
                	}
                    
                }
            }
            $this->content[] = $temp;
        }
        return $this;
    }

	/**
	 * Description
	 */
    public function error($message){
        $this->set('success',false);
        $this->set('msg',$message);
        return $this;
    }

	/**
	 * Description
	 */
    public static function toUTFR($var){
		if(is_string($var)) {
			return toUTF($var);
		} else if(is_array($var)){
			$temp = array();
			foreach($var as $key=>$value){
				$temp[toUTF($key)] = self::toUTFR($value);
			}
			return $temp;
		} else return $var;
    }


	/**
	 * Description
	 */
    public function utf(){
        $this->content = self::toUTFR($this->content);
        return $this;
    }
	/**
	 * Description
	 */
    public function utf_ru_out(){
        $this->content = php2js($this->content);
        return $this->content;
    }
    
	/**
	 * Description
	 */
    public function json(){
        return @json_encode($this->content);
    }
}


?>