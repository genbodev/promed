<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * WorkGraphMiddle - контроллер для с работы с графиком дежурств среднего медперсонала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access    public
 * @copyright Copyright (c) 2019
 * @author
 * @version
 *
 * @property WorkGraphMiddle_model dbmodel
 */

class WorkGraphMiddle extends swController
{
    protected $inputRules = array(
        'selWorkGraphMiddle' => array(
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор места работы',
                'rules' => '',
                'type' => 'id'
            ) ,
            array(
                'field' => 'fromDate',
                'label' => 'Дата от',
                'rules' => '',
                'type' => 'date'
            ) ,
            array(
                'field' => 'toDate',
                'label' => 'Дата до',
                'rules' => '',
                'type' => 'date'
            )
        ) ,

        'addWorkGraphMiddle' => array(
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор места работы',
                'rules' => 'required',
                'type' => 'id'
            ) ,
            array(
                'field' => 'WorkGraphMiddle_begDate',
                'label' => 'Время начала дежурства',
                'rules' => 'required',
                'type' => 'datetime'
            ) ,
            array(
                'field' => 'WorkGraphMiddle_endDate',
                'label' => 'Время окончания дежурства',
                'rules' => 'required',
                'type' => 'datetime'
            ) ,
            array(
                'field' => 'pmUser_id',
                'label' => 'Пользователь',
                'rules' => 'required',
                'type' => 'id'
            )
        ) ,

        'updWorkGraphMiddle' => array(
            array(
                'field' => 'WorkGraphMiddle_id',
                'label' => 'Идентификатор дежурства',
                'rules' => 'required',
                'type' => 'id'
            ) ,
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор места работы',
                'rules' => '',
                'type' => 'id'
            ) ,
            array(
                'field' => 'WorkGraphMiddle_begDate',
                'label' => 'Время начала дежурства',
                'rules' => '',
                'type' => 'datetime'
            ) ,
            array(
                'field' => 'WorkGraphMiddle_endDate',
                'label' => 'Время окончания дежурства',
                'rules' => '',
                'type' => 'datetime'
            ) ,
            array(
                'field' => 'pmUser_id',
                'label' => 'Пользователь',
                'rules' => 'required',
                'type' => 'id'
            )
        ) ,

        'delWorkGraphMiddle' => array(
            array(
                'field' => 'WorkGraphMiddle_id',
                'label' => 'Идентификатор дежурства',
                'rules' => 'required',
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
        $this
            ->load
            ->database();
        $this
            ->load
            ->model('WorkGraphMiddle_model', 'dbmodel');
    }

    /**
     * Получение списка дежурств
     */
    function selWorkGraphMiddle()
    {
        $data = $this->ProcessInputData('selWorkGraphMiddle', true);

        if ($data === false) return false;

        $response = $this
            ->dbmodel
            ->selWorkGraphMiddle($data);

        $this->ProcessModelMultiList($response, true, true)->ReturnData();
    }

    /**
     * Добавление дежурства
     */
    function addWorkGraphMiddle()
    {
        $data = $this->ProcessInputData('addWorkGraphMiddle', true);

        if ($data === false) return false;

        $response = $this
            ->dbmodel
            ->addWorkGraphMiddle($data);

        $this->ProcessModelSave($response, true)->ReturnData();

        return true;
    }

    /**
     * Изменение дежурства
     */
    function updWorkGraphMiddle()
    {
        $data = $this->ProcessInputData('updWorkGraphMiddle', true);

        if ($data === false) return false;

        $response = $this
            ->dbmodel
            ->updWorkGraphMiddle($data);

        $this->ProcessModelSave($response, true)->ReturnData();

        return true;
    }

    /**
     * Удаление дежурства
     */
    function delWorkGraphMiddle()
    {
        $data = $this->ProcessInputData('delWorkGraphMiddle', true);

        if ($data === false) return false;

        $response = $this
            ->dbmodel
            ->delWorkGraphMiddle($data);

        $this->ProcessModelSave($response, true)->ReturnData();

        return true;
    }
}

