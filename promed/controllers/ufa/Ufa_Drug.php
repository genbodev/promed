<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Drug - операции с медикаментами
* Заодно тут же работа с аптеками
* Вынесено из dlo_ivp.php
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DlO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      23.07.2009
*/
require_once(APPPATH.'controllers/Drug.php');

class Ufa_Drug extends Drug {
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['saveReceptWrong'] = array(
			array('field' => 'EvnRecept_id', 'label' => 'ИД рецепта', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgFarmacy_id', 'label' => 'ИД аптеки', 'rules' => '', 'type' => 'int'),
                        array('field' => 'ReceptWrong_id', 'label' => 'ИД отказа', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'ИД организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReceptWrong_decr', 'label' => 'Причина отказа', 'rules' => '', 'type' => 'string')
		);

		$this->inputRules['loadReceptWrongInfo'] = array(
			array(
				'field' => 'EvnRecept_id',
				'label' => 'EvnRecept_id',
				'rules' => 'trim',
				'type' => 'id'
			)
		);
	}
	
	/**
	* Включение и выключение аптек
	*/
	function vklOrgFarmacy() {
		$data = $this->ProcessInputData('vklOrgFarmacy', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->vklOrgFarmacy($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
        
     /**
     * Сохранение записи о признании рецепта недействительным
     */
    public function saveReceptWrong() {
        $data = $this->ProcessInputData('saveReceptWrong', true);
        if ($data === false) {
            return false;
        }
        
        $val = array();
        $response = $this->dbmodel->saveReceptWrong($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }

		//    Echo '{rows:'.json_encode($val).'}';
        echo json_encode(array('success' => true, 'rows' => $val));

        return true;
    }
    
        /**
     * Загрузка записи о признании рецепта недействительным
     */
    public function loadReceptWrongInfo() {
         $data = $this->ProcessInputData('loadReceptWrongInfo', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->loadReceptWrongInfo($data);
        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
     }
        
        
}
