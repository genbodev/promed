<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Okei - контроллер для работы с единицами измерения
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

class Okei extends swController {

    protected  $inputRules = array(
        'loadOkeiGrid' => array(
            array(
                'field' => 'Okei_id',
                'label' => 'Идентификатор единици измерения ОКЕИ',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'Okei_Code',
                'label' => 'Код единицы измерения ОКЕИ',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'Okei_Name',
                'label' => 'Наименование единицы измерения ОКЕИ',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'OkeiType_id',
                'label' => 'Идентификатор типа ОКЕИ',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'default' => 0,
                'field' => 'start',
                'label' => 'Номер стартовой записи',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'default' => 100,
                'field' => 'limit',
                'label' => 'Количество записей',
                'rules' => '',
                'type' => 'id'
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
        $this->load->model('Okei_model', 'dbmodel');
    }

	/**
	 * Возвращает список единиц измерения
	 * @return bool
	 */
	function loadOkeiGrid()
    {
        $data = $this->ProcessInputData('loadOkeiGrid',true);
        if ($data === false) {return false;}

        $okei_data = $this->dbmodel->loadOkeiGrid($data);

        $this->ProcessModelMultiList($okei_data,true,true)->ReturnData();
        return true;
    }
}