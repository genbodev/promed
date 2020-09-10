<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'controllers/Search.php');

class Samara_Search extends Search {
	/**
	 * function
	 */
    function __construct()
	{
        // Добавляем свое поле 'MedStaffFact_id' (Фильтр по врачам текущего отделения)
        // в формах поиска EvnPS и EvnSection //Oplachko
        $this->inputRules['EvnPS'][] = array(
            'field' => 'MedStaffFact_id',
            'label' => 'Идентификатор врача выписавший пациента',
            'rules' => '',
            'type' => 'id'
        );
        parent::__construct();
        $this->load->model('Samara_Search_model', 'samara_dbmodel');            
    }
    /**
	 * function
	 */
    function searchData() {
        if (empty($this->samara_dbmodel)) { return false; } // не прошли конструктор
        
        $val  = array();

        $response = $this->samara_dbmodel->searchData($this->inputData, false, false);

        if ( is_array($response) ) {
            if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
                $val = $response;
                array_walk($val, 'ConvertFromWin1251ToUTF8');
            }
            else if ( isset($response['data']) ) {
                $val['data'] = array();
                foreach ( $response['data'] as $row ) {
                    array_walk($row, 'ConvertFromWin1251ToUTF8');
                    $val['data'][] = $row;
                }

                $val['totalCount'] = $response['totalCount'];
            }
        }

        $this->ReturnData($val);

        return true;
    }
}