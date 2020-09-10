<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * BarCode - контроллер для работы со штрих-кодом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      23.04.2012
 */
class BarCode extends swController {
	var $NeedCheckLogin = false;
	public $inputRules = array();
	/**
	*  __construct
	*/
	function __construct(){
		parent::__construct();

		$this->load->database();
		$this->load->model('Barcode_model','dbmodel');
		$this->inputRules = array(
			'GetBarcode' => array(
				array(
					'field' => 'EvnRecept_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'GetBarcodeAmbulatCard' => array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'decodeBarCode' => array(
				array(
					'field' => 'code',
					'label' => 'считанный код',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getQRCode' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}
	/**
	*  Index
	*/
	function Index() {
		return false;
	}
	
	/**
	*  получение штрих-кода
	*/
	function GetBarcode() {
		$this->load->helper('Barcode');
		$this->load->helper('Options');
		$data = $this->ProcessInputData('GetBarcode', false);
		if ($data === false) {return false;}
		//var_dump($data);
		/*if ( (0 == $data['EvnRecept_id']) || (0 == $data['Lpu_id']) ) {
			echo 'Неверно заданы параметры';
			return true;
		}*/

		$response = $this->dbmodel->getBarcodeFields($data);

		if ( !is_array($response) || count($response) == 0 )
		{
			echo 'Ошибка при получении данных по рецепту';
			return true;
		} else {
			//$response = toAnsiR($response, true);
			// Если передаем параметр LINK - отображаем саму ссылку, а не картинку
			if (isset($_GET['link'])) {
				print getPromedUrl().'/barcode.php?s='. urlencode($this->dbmodel->getBinaryString($response));
			} else {
				$this->dbmodel->genBarcodeImage($this->dbmodel->getBinaryString($response));
			}
		}
	}

	/**
	*  получение бинарной строки штрих-кода
	*/
	function GetBarcodeBinaryString() {
		$this->load->helper('Barcode');
		$this->load->helper('Options');
		$data = $this->ProcessInputData('GetBarcode', false);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getBarcodeFields($data);
		$response['binary_string'] = 1;

		echo "/*NO PARSE JSON*/";

		if ( !is_array($response) || count($response) == 0 )
		{
			echo 'Ошибка при получении данных по рецепту';
			return true;
		} else {
			print $this->dbmodel->getBinaryString($response);
		}
	}
	/**
	*  woohoo
	*/
	function woohoo(){
		include 'barcode/barcode_v501/barcode.php';
	}
	
	/**
	 * GetBarcodeAmbulatCard
	 */
	function GetBarcodeAmbulatCard(){
		$this->load->helper('Barcode');
		$this->load->helper('Options');
		$data = $this->ProcessInputData('GetBarcodeAmbulatCard', false);
		if ($data === false) {return false;}
		//var_dump($data);
		/*if ( (0 == $data['EvnRecept_id']) || (0 == $data['Lpu_id']) ) {
			echo 'Неверно заданы параметры';
			return true;
		}*/

		$response = $this->dbmodel->GetBarcodeAmbulatCard($data);
		if(!$response){
			echo 'Ошибка при получении данных по амбулаторной карте';
		}

		/*if ( !is_array($response) || count($response) == 0 )
		{
			echo 'Ошибка при получении данных по амбулаторной карте';
			return true;
		} else {
			//$response = toAnsiR($response, true);
			// Если передаем параметр LINK - отображаем саму ссылку, а не картинку 
			if (isset($_GET['link'])) {
				print getPromedUrl().'/barcode.php?s='. urlencode($this->dbmodel->getBinaryString($response));
			} else {
				$this->dbmodel->genBarcodeImage($this->dbmodel->getBinaryString($response));
			}
		}*/
	}
	
	/**
	 * Декодирование строки
	 */
	function decodeBarCode(){
		$data = $this->ProcessInputData('decodeBarCode', false);
		$obj = $this->dbmodel->decodeBarCode($data);
		if($obj){
			$this->ReturnData(array("success"=>true, "obj" => $obj));
		}else{
			$this->ReturnData(array("success"=>false));
		}
	}

	/**
	 * Получение QR-кода
	 */
	function getQRCode(){
		$data = $this->ProcessInputData('getQRCode', false);
		if ($data === false) { return false; }

		$this->dbmodel->getQRCode($data);
	}
}