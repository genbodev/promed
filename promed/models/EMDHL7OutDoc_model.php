<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
* модель обрабатывает исходящий документ в формате HL7
* Является диспетчером для подчиненных, в которых обрабатывает специфично каждый из документов
*
* @package      EMD
* @access       public
* @copyright    Copyright (c) 2020 Swan Ltd.
* @author       
* @version      17.07.2020
*/

/*
	Как работать:
		

*/


class EMDHL7OutDoc_model extends swModel
{
	/**
	* код документа 1.2.643.5.1.13.13.99.2.195
	* @param $code int
	*/
	protected $code=0;
	
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	* установить код документа
	* @param $code int
	* @return void
	*/
	public function setCode($code)
	{
		$this->code=(int)$code;
	}
	
	/**
	* загрузка адаптер, соответсвующего коду
	* используется внутренними вызовами
	* @param $code int код документа, если 0, то грузится то что указали раннее
	* @return void
	*/
	protected function loadAdapter($code=0)
	{
		if (!empty($code)){
			$this->setCode($code);
		}
		//загружаем нужный обработчик (по коду документа)
		try{
			$this->load->model("HL7/out/Doc{$this->code}",'EMDHL7OutDocAdapter');
		} catch (Exception $e){
			throw new Exception("Для документов с кодом {$this->code} нет обработчика! (федеральный справочник 1.2.643.5.1.13.13.99.2.195)");
		}
	}
	
	/**
	* собственно создает файл формата HL7
	* @param $data array
	* @param $code int - код документа мз справочника 1.2.643.5.1.13.13.99.2.195
	*/
	public function PrintHL7(array $data,$code=0)
	{
		//если указали код, грузим адаптер
		if ($code>0){
			$this->loadAdapter($code);
		}
		//получаем специфичные для документа данные
		$info=$this->EMDHL7OutDocAdapter->getInfo($data);
		
		//собственно создаем xml
		$xml=$this->EMDHL7OutDocAdapter->createHL7($info);
		
		//проверяем документ на соотвествие схемы
		$this->EMDHL7OutDocAdapter->ValidateDoc($xml);
		return ['xml' => $xml];
	}
	
	/**
	* производит обращение к адаптерам используя текущий объект как прокси
	* возвращает результат работы соотвествующего метода в адаптере
	* если метода не существует - исключение
	* @param string $name
	* @param $var array
	* @return mixed
	* @throws Exception
	*/
	public function __call ($name,  $var)
	{
		//если адаптер еще не грузили, загружаем
		if (empty($this->EMDHL7OutDocAdapter)){
			$this->loadAdapter();
		}
		if (!method_exists($this->EMDHL7OutDocAdapter,$name) ){
			throw new Exception("Не верное обращение к обработчику HL7, метода {$name} не существует");
		}
		return call_user_func_array ([$this->EMDHL7OutDocAdapter,$name],$var);
	 }
}
