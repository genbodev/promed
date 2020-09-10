<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonRegisterEndo - регистр по эндо
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */
class PersonRegisterEndo extends swController
{

    public $inputRules = array(
        'savePersonRegisterEndo' => array(
            array(
                'field' => 'PersonRegisterEndo_id',
                'label' => 'Идентификатор записи в регистре эндо',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonRegister_id',
                'label' => 'Идентификатор записи в регистре',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonRegister_Code',
                'label' => 'Номер',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'Diag_id',
                'label' => 'Диагноз',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'CategoryLifeDegreeType_id',
                'label' => 'Степень',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ProsthesType_id',
                'label' => 'Тип протезирования',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_iid',
                'label' => 'МО постановки на учет',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal_iid',
                'label' => 'Врач',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonRegisterEndo_obrDate',
                'label' => 'Дата обращения',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'PersonRegister_setDate',
                'label' => 'Дата постановки',
                'rules' => 'required',
                'type' => 'date'
            ),
            array(
                'field' => 'PersonRegisterEndo_callDate',
                'label' => 'Дата вызова на операцию',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'PersonRegisterEndo_hospDate',
                'label' => 'Дата госпитализации в стационар',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'PersonRegisterEndo_operDate',
                'label' => 'Дата операции',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'PersonRegisterEndo_Contacts',
                'label' => 'Адрес и телефон',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'PersonRegisterEndo_Comment',
                'label' => 'Примечание',
                'rules' => '',
                'type' => 'string'
            )
        ),
		'loadPersonRegisterEndoEditForm' => array(
			array(
				'field' => 'PersonRegisterEndo_id',
				'label' => 'Идентификатор записи в регистре эндо',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deletePersonRegisterEndo' => array(
			array(
				'field' => 'PersonRegisterEndo_id',
				'label' => 'Идентификатор записи в регистре эндо',
				'rules' => 'required',
				'type' => 'id'
			)
		)
    );

	/**
	 * Description
	 */
    function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('PersonRegisterEndo_model', 'dbmodel');
    }

    /**
     * Сохранение
     */
    function savePersonRegisterEndo()
    {
        $data = $this->ProcessInputData('savePersonRegisterEndo',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->savePersonRegisterEndo($data);
        $this->ProcessModelSave($response, true)->ReturnData();
    }

    /**
     * Загрузка
     */
    function loadPersonRegisterEndoEditForm()
    {
        $data = $this->ProcessInputData('loadPersonRegisterEndoEditForm',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->loadPersonRegisterEndoEditForm($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Удаление
     */
    function deletePersonRegisterEndo()
    {
        $data = $this->ProcessInputData('deletePersonRegisterEndo',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->deletePersonRegisterEndo($data);
        $this->ProcessModelSave($response, true)->ReturnData();
    }
}
