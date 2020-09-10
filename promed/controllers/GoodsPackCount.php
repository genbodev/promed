<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Количество товара в потребительской упаковке
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Salakhov R.
 * @version      04.2018
 *
 * @property GoodsPackCount_model GoodsPackCount_model
 */

class GoodsPackCount extends swController {
    /**
     * Конструктор
     */
    function __construct(){
        parent::__construct();
        $this->inputRules = array(
            'save' => array(
                array(
                    'field' => 'DrugComplexMnn_id',
                    'label' => 'Комплексное МНН',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'TRADENAMES_ID',
                    'label' => 'Торговое наименование',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'GoodsPackCount_Count',
                    'label' => 'Количество единиц измерения товара в упаковке',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'GoodsUnit_id',
                    'label' => 'Единица измерения товара',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'Организация',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UserOrg_id',
                    'label' => 'Организация пользователя',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'load' => array(
                array(
                    'field' => 'GoodsPackCount_id',
                    'label' => 'Идентификатор',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Drug_id',
                    'label' => 'Медикамент',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'GoodsUnit_id',
                    'label' => 'Ед. измерения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UserOrg_id',
                    'label' => 'Организация пользователя',
                    'rules' => '',
                    'type' => 'id'
                )
            )
        );
        $this->load->database();
        $this->load->model('GoodsPackCount_model', 'GoodsPackCount_model');
    }

    /**
     * Сохранение
     */
    function save() {
        $data = $this->ProcessInputData('save', false);
        if ($data){
            $response = $this->GoodsPackCount_model->save($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении Количество товара в потребительской упаковке')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка
     */
    function load() {
        $data = $this->ProcessInputData('load', false);
        if ($data){
            $response = $this->GoodsPackCount_model->load($data);
            $this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
            return true;
        } else {
            return false;
        }
    }
}