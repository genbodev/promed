<?php	defined('BASEPATH') or die ('No direct script access allowed');
class CardioRiskCalc extends swController {
    public $inputRules = array(
        'deleteCardioRiskCalc' => array(
            array(
                'field' => 'CardioRiskCalc_id',
                'label' => 'Идентификатор определения группы крови',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadCardioRiskCalcPanel' => array(
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadCardioRiskCalcEditForm' => array(
            array(
                'field' => 'CardioRiskCalc_id',
                'label' => 'Идентификатор определения группы крови',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'saveCardioRiskCalc' => array(
            array(
                'field' => 'CardioRiskCalc_setDT',
                'label' => 'Дата измерения',
                'rules' => 'trim|required',
                'type' => 'date'
            ),
            array(
                'field' => 'CardioRiskCalc_id',
                'label' => 'Идентификатор риска',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'CardioRiskCalc_SistolPress',
                'label' => 'Систолическое давление',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'CardioRiskCalc_IsSmoke',
                'label' => 'Курение',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'CardioRiskCalc_Chol',
                'label' => 'Общий холестерин',
                'rules' => '',
                'type' => 'float'
            ),
            array(
                'field' => 'CardioRiskCalc_Percent',
                'label' => 'Процент',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'RiskType_id',
                'label' => 'Тип риска',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Server_id',
                'label' => 'Идентификатор сервера',
                'rules' => 'required',
                'type' => 'int'
            )
        ),
        'calcCargioRiskPercent' => array(
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'CardioRiskCalc_SistolPress',
                'label' => 'Систолическое давление',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'CardioRiskCalc_IsSmoke',
                'label' => 'Курение',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'CardioRiskCalc_Chol',
                'label' => 'Общий холестерин',
                'rules' => 'required',
                'type' => 'float'
            )
        )
    );

    /**
     * CardioRiskCalc constructor.
     */
    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('CardioRiskCalc_model', 'dbmodel');
    }


    /**
     *  Удаление данных о сердечно-сосудистом риске
     *  Входящие данные: $_POST['CardioRiskCalc_id']
     *  На выходе: JSON-строка
     *  Используется: -
     */
    function deleteCardioRiskCalc() {
        $data = array();
        $val  = array();

        // Получаем сессионные переменные
        $data = array_merge($data, getSessionParams());

        $err = getInputParams($data, $this->inputRules['deleteCardioRiskCalc']);

        if ( strlen($err) > 0 ) {
            echo json_return_errors($err);
            return false;
        }

        $response = $this->dbmodel->deleteCardioRiskCalc($data);

        if ( (is_array($response)) && (count($response) > 0) ) {
            if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
                $val['success'] = true;
            }
            else {
                $val = $response[0];
                $val['success'] = false;
            }
        }
        else {
            $val['Error_Msg'] = 'При удалении определения группы крови возникли ошибки';
            $val['success'] = false;
        }

        array_walk($val, 'ConvertFromWin1251ToUTF8');

        $this->ReturnData($val);

        return true;
    }


    /**
     *  Получение данных для формы расчета сердечно-сосудистого риска
     *  Входящие данные: $_POST['CardioRiskCalc_id']
     *  На выходе: JSON-строка
     *  Используется: форма редактирования сердечно-сосудистого риска
     */
    function loadCardioRiskCalcEditForm() {
        $data = array();
        $val  = array();

        // Получаем сессионные переменные
        $data = array_merge($data, getSessionParams());

        $err = getInputParams($data, $this->inputRules['loadCardioRiskCalcEditForm']);

        if ( strlen($err) > 0 ) {
            echo json_return_errors($err);
            return false;
        }

        $response = $this->dbmodel->loadCardioRiskCalcEditForm($data);

        if ( is_array($response) && count($response) > 0 ) {
            $val = $response;
            array_walk($val[0], 'ConvertFromWin1251ToUTF8');
        }

        $this->ReturnData($val);

        return true;
    }


    /**
     *  Сохранение определения группы крови
     *  Входящие данные: <поля формы>
     *  На выходе: JSON-строка
     *  Используется: форма редактирования определения группы крови
     */
    function saveCardioRiskCalc() {
        $data = array();
        $val  = array();

        // Получаем сессионные переменные
        $data = array_merge($data, getSessionParams());

        $err = getInputParams($data, $this->inputRules['saveCardioRiskCalc']);

        if ( strlen($err) > 0 ) {
            echo json_return_errors($err);
            return false;
        }

        $response = $this->dbmodel->saveCardioRiskCalc($data);

        if ( is_array($response) && count($response) > 0 ) {
            $val = $response[0];

            if ( array_key_exists('Error_Msg', $val) && empty($val['Error_Msg']) ) {
                $val['success'] = true;
                $val['CardioRiskCalc_id'] = $response[0]['CardioRiskCalc_id'];
            }
            else {
                $val['success'] = false;
            }
        }
        else {
            $val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении определения группы крови');
        }

        array_walk($val, 'ConvertFromWin1251ToUTF8');

        $this->ReturnData($val);

        return true;
    }

    /**
     * Расчет процента сердечно-сосудистого риска риска
     */
    function calcCargioRiskPercent() {
        $data = $this->ProcessInputData('calcCargioRiskPercent', true);
        if ($data === false) return false;

        $response = $this->dbmodel->calcCargioRiskPercent($data);
        $this->ReturnData($response);
        return true;
    }

    /**
     * Получение списка рисков для ЭМК
     */
    function loadCardioRiskCalcPanel() {
        $data = $this->ProcessInputData('loadCardioRiskCalcPanel', true, true, true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadCardioRiskCalcPanel($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return false;
    }
}
