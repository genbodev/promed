<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * OkeiType - контроллер для работы с мерами измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class OkeiType extends swController {

    protected  $inputRules = array(
        'loadOkeiTypeGrid' => array(
            array(
                'field' => 'OkeiType_id',
                'label' => 'Идентификатор типа ОКЕИ',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'OkeiType_Code',
                'label' => 'Код типа ОКЕИ',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'OkeiType_Name',
                'label' => 'Наименование типа ОКЕИ',
                'rules' => '',
                'type' => 'string'
            )
        )
    );

	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('OkeiType_model', 'dbmodel');
    }

	/**
	 * Возвращает список мер измерения
	 * @return bool
	 */
	function loadOkeiTypeGrid()
    {
        $data = $this->ProcessInputData('loadOkeiTypeGrid',true);
        if ($data === false) {return false;}

        $okei_type_data = $this->dbmodel->loadOkeiTypeGrid($data);
        $this->ProcessModelList($okei_type_data,true,true)->ReturnData();
        return true;
    }
}