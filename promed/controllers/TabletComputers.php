<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Interview - контроллер для работы с формой опроса пользователей
 */
class TabletComputers extends swController
{
    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('TabletComputers_model', 'dbmodel');

        $this->inputRules = array(
            'deleteTabletComputer' => array(
                array('field' => 'CMPTabletPC_id',
                    'label' => 'id',
                    'rules' => 'required',
                    'type' => 'id'
                ),
            ),
            'saveTabletComputer' => array(
                array('field' => 'CMPTabletPC_id',
                    'label' => 'id',
                    'rules' => '',
                    'type' => 'id'
                ), array('field' => 'LpuBuilding_id',
                    'label' => 'Номер базовой подстанции',
                    'rules' => 'required',
                    'type' => 'id'
                ), array('field' => 'CMPTabletPC_Code',
                    'label' => 'Код',
                    'rules' => 'required',
                    'type' => 'string'
                ), array('field' => 'CMPTabletPC_Name',
                    'label' => 'Наименование',
                    'rules' => '',
                    'type' => 'string'
                ), array('field' => 'CMPTabletPC_SIM',
                    'label' => 'Номер SIM карты',
                    'rules' => '',
                    'type' => 'string'
                ),
            ),
            'loadTabletComputer' => array(
                array('field' => 'CMPTabletPC_id',
                    'label' => 'id',
                    'rules' => 'required',
                    'type' => 'id'
                )
            )
        );
    }

    /**
     * Сохранение Планшетного компьютера
     */
    public function saveTabletComputer() {
        $data = $this->ProcessInputData('saveTabletComputer', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveTabletComputer($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;
    }

    /**
     * Удаление Планшетного компьютера
     */
    public function deleteTabletComputer() {
        $data = $this->ProcessInputData('deleteTabletComputer', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteTabletComputer($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;
    }

    /**
     * Получение списка планшетных компьютеров
     */
    public function loadTabletComputersList()
    {
        $response = $this->dbmodel->loadTabletComputersList();
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Получение планшетного компьютера по id
     */
    public function loadTabletComputer()
    {
        $data = $this->ProcessInputData('loadTabletComputer', true);

        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadTabletComputer($data);
        $this->ProcessModelList($response, true)->ReturnData();

    }

}
