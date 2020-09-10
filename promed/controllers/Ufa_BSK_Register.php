<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ufa_BSK_Register - контроллер для БСК (Башкирия)
 *  административная часть
 *
 *
 * @package			BSK
 * @author			Васинский Игорь 
 * @version			20.08.2014
 */

class Ufa_BSK_Register extends swController
{
    var $model = "Ufa_BSK_Register_model";
    
    
    /**
     * comment
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');
        
        $this->inputRules = array(
            'listRecomendation' => array(
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservRecomendation_text',
                    'label' => 'текст рекомендации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'searchRecomendation_text',
                    'label' => 'фильтр',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'deleteEditRecomendation' => array(
                array(
                    'field' => 'BSKObservRecomendation_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'saveAfterEditRecomendation' => array(
                array(
                    'field' => 'BSKObservRecomendation_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservRecomendation_text',
                    'label' => 'текст рекомендации',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getRecomendations' => array(
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElementValues_id',
                    'label' => 'идентификатор значения типа',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'searchRecomendation_text',
                    'label' => 'фильтр',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'searchRecomendations' => array(
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElementValues_id',
                    'label' => 'идентификатор значения типа',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'saveRecomendation' => array(
                array(
                    'field' => 'BSKObservElementValues_id',
                    'label' => 'идентификатор значения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'jsonSetRecomendation',
                    'label' => 'идентификаторы рекомендаций в Json',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'тип рекомендации',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'addRecomendation' => array(
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'идентификатор типа рекомендации',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservRecomendation_text',
                    'label' => 'текст рекомендации',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getListObjects' => array(
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'getListGroupTypes' => array(
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'getFormatAndUnit' => array(
                array(
                    'field' => 'Type_id',
                    'label' => 'идентификатор типа сведений',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getListTypes' => array(
                array(
                    'field' => 'BSKObservElementGroup_id',
                    'label' => 'идентификатор группы типов сведений',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getListValues' => array(
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'идентификатор типа сведений',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'addObject' => array(
                array(
                    'field' => 'BSKObject_name',
                    'label' => 'Наименование предмета наблюдения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'идентификатор типа заболевания',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'addGroupType' => array(
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'идентификатор группы сведений',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElementGroup_name',
                    'label' => 'Наименование группы сведений',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'addType' => array(
                array(
                    'field' => 'BSKObservElementGroup_id',
                    'label' => 'идентификатор группы сведений',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElement_name',
                    'label' => 'наименование типа сведений',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'formatText',
                    'label' => 'текстовое значение формата типа сведений',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_symbol',
                    'label' => 'буквенное обозначение типа сведения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_formula',
                    'label' => 'текст формулы типа сведения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_stage',
                    'label' => 'этап типа сведений',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_Sex_id',
                    'label' => 'пол пациента',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_IsRequire',
                    'label' => 'признак обязательного ответа',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_minAge',
                    'label' => 'мин. значение возраста',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_maxAge',
                    'label' => 'макс. значение возраста',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_Anketa',
                    'label' => 'содержание вопроса для печатного варината',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'addValue' => array(
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'идентификатор типа сведений',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElementValues_data',
                    'label' => 'значение типа сведений',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElementValues_min',
                    'label' => 'минимально допустимое значение типа сведений',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_max',
                    'label' => 'максимально допустимое значение типа сведений',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_points',
                    'label' => 'кол-во баллов',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_sign',
                    'label' => 'признак',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'addUnit' => array(
                array(
                    'field' => 'nameUnit',
                    'label' => 'Наименование единицы измерения',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'editObject' => array(
                array(
                    'field' => 'object_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'идентификатор типа заболевания',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'nameObject',
                    'label' => 'Наименование предмета наблюдения',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'editGroupType' => array(
                array(
                    'field' => 'BSKObservElementGroup_id',
                    'label' => 'идентификатор группы сведений предмета наблюдения',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElementGroup_name',
                    'label' => 'Наименование группы сведений предмета наблюдения',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'editType' => array(
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'идентификатор группы сведений',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElement_name',
                    'label' => 'наименование типа сведений',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'formatText',
                    'label' => 'текстовое значение формата типа сведений',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_symbol',
                    'label' => 'буквенное обозначение типа сведения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_formula',
                    'label' => 'текст формулы типа сведения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_stage',
                    'label' => 'этап типа сведений',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_Sex_id',
                    'label' => 'пол пациента',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_IsRequire',
                    'label' => 'признак обязательного ответа',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_minAge',
                    'label' => 'мин. значение возраста',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_maxAge',
                    'label' => 'макс. значение возраста',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'BSKObservElement_Anketa',
                    'label' => 'текст вопроса для анкеты',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'editValue' => array(
                array(
                    'field' => 'BSKObservElementValues_id',
                    'label' => 'идентификатор значения типа сведений',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElementValues_data',
                    'label' => 'значение типа сведений',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElementValues_min',
                    'label' => 'минимально допустимое значение типа сведений',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_max',
                    'label' => 'максимально допустимое значение типа сведений',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_points',
                    'label' => 'кол-во балов',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'BSKObservElementValues_sign',
                    'label' => 'признак',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'editUnit' => array(
                array(
                    'field' => 'editUnit',
                    'label' => 'Наименование единицы измерения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'unit_id',
                    'label' => 'идентификатор единицы измерения',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'deleteObject' => array(
                array(
                    'field' => 'object_id',
                    'label' => 'идентификатор предмета наблюдения',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'deleteGroupType' => array(
                array(
                    'field' => 'BSKObservElementGroup_id',
                    'label' => 'идентификатор группы сведений',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'deleteType' => array(
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'идентификатор типа сведений',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'deleteValue' => array(
                array(
                    'field' => 'BSKObservElementValues_id',
                    'label' => 'идентификатор значения сведений',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'deleteUnit' => array(
                array(
                    'field' => 'unit_id',
                    'label' => 'идентификатор единицы измерения',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'checkSymbol' => array(
                array(
                    'field' => 'symbol',
                    'label' => 'краткое буквенное описание типа сведений (для парсера формул)',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'идентификатор типа сведений',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'checkUnitBeforeDelete' => array(
                array(
                    'field' => 'unit_id',
                    'label' => 'идентификатор единицы измерения',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'manageLinks' => array(
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'предмет наблюдения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObservElement_id',
                    'label' => 'тип сведений',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'action',
                    'label' => 'тип действия',
                    'rules' => '',
                    'type' => 'string'
                )
            )
            
        );
        
    }
    
    /**
     *  Получение списка рекомендаций для вкладки "Управление рекомендациями"
     */
    public function listRecomendation()
    {
        $data = $this->ProcessInputData('listRecomendation', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->listRecomendation($data);
        
        return $this->ReturnData($list);
    }
    
    
    /**
     *  Получение рекомендаций
     */
    function getRecomendations()
    {
        $data = $this->ProcessInputData('getRecomendations', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getRecomendations($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     * Удаление рекомендации
     */
    function deleteEditRecomendation()
    {
        $data = $this->ProcessInputData('deleteEditRecomendation', true);
        
        
        if ($data === false) {
            return false;
        }
        
        //$list = $this->dbmodel->preSaveRecomendation($data);
        
        $response = $this->dbmodel->deleteEditRecomendation($data);
        
        return $this->ReturnData(array(
            'success' => true
        ));
        //$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Сохранение рекомендаций
     */
    function saveAfterEditRecomendation()
    {
        $data = $this->ProcessInputData('saveAfterEditRecomendation', true);

        
        if ($data === false) {
            return false;
        }
        
        //$list = $this->dbmodel->preSaveRecomendation($data);
        
        $response = $this->dbmodel->saveAfterEditRecomendation($data);
        
        return $this->ReturnData(array(
            'success' => true
        ));
        //$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Сохранение рекомендаций после редактирования
     */
    function saveRecomendation()
    {
        $data = $this->ProcessInputData('saveRecomendation', true);
        
        if ($data === false) {
            return false;
        }
        
        //$list = $this->dbmodel->preSaveRecomendation($data);
        
        $response = $this->dbmodel->saveRecomendation($data);
        
        return $this->ReturnData(array(
            'success' => true
        ));
        //$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Создание рекомендации
     */
    function addRecomendation()
    {
        $data = $this->ProcessInputData('addRecomendation', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addRecomendation($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Получение общих вопросов
     */
    function getLinks()
    {
        $list = $this->dbmodel->getLinks();
        
        return $this->ReturnData($list);
    }
    
    /**
     * Управление общими вопросами
     */
    function manageLinks()
    {
        $data = $this->ProcessInputData('manageLinks', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->manageLinks($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     *  Получить все предметы наблюдения
     */
    function getListObjects()
    {
        $data = $this->ProcessInputData('getListObjects', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListObjects($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     *  Получить все группы сведений предмета наблюдения
     */
    function getListGroupTypes()
    {
        $data = $this->ProcessInputData('getListGroupTypes', true);
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListGroupTypes($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     *  Получить все сведения предмета наблюдения
     */
    function getListTypes()
    {
        $data = $this->ProcessInputData('getListTypes', true);
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListTypes($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     *  Получить все значения сведений
     */
    function getListValues()
    {
        $data = $this->ProcessInputData('getListValues', true);
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListValues($data);
        
        return $this->ReturnData($list);
    }
    
    
    /**
     *  Добавление нового предмета наблюдения
     */
    function addObject()
    {
        $data = $this->ProcessInputData('addObject', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addObject($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Добавление значения для группы сведениЙ
     */
    function addGroupType()
    {
        $data = $this->ProcessInputData('addGroupType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addGroupType($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Добавление нового типа сведений предмета наблюдения
     */ 
    function addType()
    {
        
        $data = $this->ProcessInputData('addType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addType($data);
        
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Добавление значения для сведения
     */
    function addValue()
    {
        $data = $this->ProcessInputData('addValue', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addValue($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Редактирование предмета наблюдения
     */
    function editObject()
    {
        $data = $this->ProcessInputData('editObject', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->editObject($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Редактирование группы сведений
     */
    function editGroupType()
    {
        $data = $this->ProcessInputData('editGroupType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->editGroupType($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Редактирование типа сведений
     */
    function editType()
    {
        $data = $this->ProcessInputData('editType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->editType($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Редактирование значения типа сведений  
     */
    function editValue()
    {
        $data = $this->ProcessInputData('editValue', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->editValue($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Удаление предмета наблюдения
     */
    function deleteObject()
    {
        $data = $this->ProcessInputData('deleteObject', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->deleteObject('BSK_GenObjObsn', $data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Удаление группы сведений
     */
    function deleteGroupType()
    {
        $data = $this->ProcessInputData('deleteGroupType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->deleteGroupType($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Удаление типа сведений
     */
    function deleteType()
    {
        $data = $this->ProcessInputData('deleteType', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->deleteType($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Удаление значения типа сведений
     */
    function deleteValue()
    {
        $data = $this->ProcessInputData('deleteValue', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->deleteValue($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     *  Построение древа предметов наблюдения
     */
    function getTreeObjects()
    {
        $tmp = $this->dbmodel->getTreeObjects();
        
        /**
        $tree = '<ul>';
        
        if(is_array($tmp)){
        foreach($tmp as $k=>$nameObj){
        $tree .= '<li><b onclick="alert(this.innerHTML)">' . $nameObj['BSK_GenObjObsn_name'] . '</b></li><br/>';
        }
        }
        
        $tree .= '</ul>';
        */
        $tree = '';
        //echo '<pre>' . print_r($tmp, 1) . '</pre>';
        if (is_array($tmp)) {
            foreach ($tmp as $k => $v) {
                
                $v['TypeName']                                                      = $v['TypeName'] . ($v['unit'] == '' ? '' : ', <small style="color:gray">[' . $v['unit'] . ']</small>');
                //$res[] = $v['ObjectName'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['format']   = $v['format'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['unit']     = $v['unit'] == '' ? '-' : $v['unit'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['symbol']   = $v['symbol'] == '' ? '-' : $v['symbol'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['min']      = $v['min'] == '' ? '-' : $v['min'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['max']      = $v['max'] == '' ? '-' : $v['max'];
                $res[$v['ObjectName']][$v['GroupType']][$v['TypeName']]['values'][] = $v['Data'];
                //$res[$v['ObjectName']][$v['GroupType']][] = $v['TypeName'];
            }
        }
        //echo '<pre>' . print_r($res, 1) . '</pre>';
        
        $tree .= '<ul>';
        
        $formats_no_data = array(
            'textfield (вводится в ручную)',
            'datefield (поле для ввода даты)',
            'formula (автоматический расчёт)'
        );
        
        $style_more_info = 'background-color:#FFFFEB; width:400px; padding:10px; border-radius:5px;margin:3px;border:1px solid #D0D0D0';
        
        foreach ($res as $obj => $obj_data) {
            $tree .= '<li style="cursor:pointer" ><h1>' . $obj . '</h1><ul style="">';
            
            foreach ($obj_data as $group => $group_data) {
                
                if ($group != '') {
                    
                    $tree .= '<li  onclick="toggle(this)"  style="cursor:pointer;color:#770CB4;padding-left:30px;"><h3> ' . $group . '</h3><ul style="display:none;padding-left:30px;">';
                    
                    foreach ($group_data as $type => $type_data) {
                        //echo '<pre>' . print_r($type, 1) . '</pre>';
                        if ($type != '') {
                            
                            $tree .= '<li  style="color:#0E36C6;cursor:pointer;"><h4> ' . $type;
                            
                            
                            $tree .= '<div style="' . $style_more_info . '"><small style="color:grey; font-weight:normal; font-style:italic;"><b>Формат: </b>' . $type_data['format'] . ',
                                      <!--<br/><b>Ед. измерения: </b>' . $type_data['unit'] . ',-->
                                      <br/><b>Символ: </b>' . $type_data['symbol'] . ',
                                      <br/><b>min: </b>' . $type_data['min'] . ',
                                      <br/><b>max: </b>' . $type_data['max'] . '</small></div>';
                            
                            $tree .= '<ul style="padding-left:30px; font-weight:normal">';
                            
                            
                            if (isset($type_data['values'])) {
                                foreach ($type_data['values'] as $k => $value) {
                                    
                                    $tree .= '<li  style="color:#487628;">' . ($value != '' ? $value : '<span style="color:red">Пусто</span>') . '</li>';
                                }
                            }
                            
                            $tree .= '</ul></li><br/>';
                        }
                    }
                    $tree .= '</ul></li>';
                }
            }
            $tree .= '</ul></li><br/>';
        }
        
        
        
        $tree .= '</ul>';
        
        
        
        $tree .= '<script language="JavaScript">
                  function toggle(el){
                    var chld = el.childNodes;
                    console.log("AFTER: ", chld[1].style.display);
                    
                    for(k in chld){
                        if(k>0){
                            console.log("CHLD:", chld[k].innerHTML);
                            chld[k].style.display =  (chld[1].style.display == "none") ? "block" : "none";
                            
                        }
                    }
                    
                    console.log("BEFORE: ", chld[1].style.display);
                  }
                  </script>
                ';
        
        $rendertree = iconv('utf-8', 'windows-1251', $tree);
        
        //$renderTree = $tree != '' ? $tree : 'Список объектов пуст';
        
        return $this->ReturnData($tree);
        
    }
    
    /**
     *  Получение списка возможных вариантов типов сведений
     */
    function getTypesFormat()
    {
        $formats = $this->dbmodel->getTypesFormat();
        
        return iconv('utf-8', 'windows-1251', $this->ReturnData($formats));
    }
    
    /**
     *  Получение списка единиц измерения
     */
    function getUnits()
    {
        $units = $this->dbmodel->getUnits();
        
        return iconv('utf-8', 'windows-1251', $this->ReturnData($units));
    }
    
    /**
     * Получение формата и ед. измерения для конкретного типа сведений
     */ 
    function getFormatAndUnit()
    {
        $data = $this->ProcessInputData('getFormatAndUnit', true);
        if ($data === false) {
            return false;
        }
        
        $FU = $this->dbmodel->getFormatAndUnit($data);
        
        return $this->ReturnData($FU);
    }
    
    /**
     *  Проверка условного символьного обозначения
     */ 
    function checkSymbol()
    {
        $data = $this->ProcessInputData('checkSymbol', true);
        if ($data === false) {
            return false;
        }
        
        $check = $this->dbmodel->checkSymbol($data);
        
        return $this->ReturnData($check);
    }
    
    /**
     * Добавление новой единицы измерения
     */ 
    function addUnit()
    {
        $data = $this->ProcessInputData('addUnit', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->addUnit($data);
        
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Добавление новой единицы измерения
     */ 
    function editUnit()
    {
        $data = $this->ProcessInputData('editUnit', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->editUnit($data);
        
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Проверка используемости единицы измерения (перед удалением)
     */
    function checkUnitBeforeDelete()
    {
        $data = $this->ProcessInputData('checkUnitBeforeDelete', true);
        if ($data === false) {
            return false;
        }
        
        $check = $this->dbmodel->checkUnitBeforeDelete($data);
        
        return $this->ReturnData($check);
    }
    
    /**
     * Удаление единицы измерения
     */
    function deleteUnit()
    {
        $data = $this->ProcessInputData('deleteUnit', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->deleteUnit($data);
        
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
}
?>