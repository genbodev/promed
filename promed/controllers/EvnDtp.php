<?php   defined('BASEPATH') or die('No direct script access allowed');
/**
* EvnDtp - извещения о ДТП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Stac
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author       Alexander Arefyev aka Alf (avaref@gmail.com)
* @version			2010
*/

defined('BASEPATH') or die('No direct script access allowed');

class EvnDtp extends swController {

    public $inputRules = array('saveEvnDtpWound' => array(array('field' => 'EvnDtpWound_id',
                'label' => '',
                'rules' => '',
                'type' => 'id'),
            array('field' => 'Lpu_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'id'),
            array('field' => 'Server_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'int'),
            array('field' => 'PersonEvn_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'id'),
            array('field' => 'EvnDtpWound_ObrDate',
                'label' => 'Дата обращения',
                'rules' => 'required',
                'minValue' => 'field: EvnDtpWound_DtpDate',
                'maxValue' => 'now',
                'type' => 'date'),
            array('field' => 'EvnDtpWound_HospDate',
                'label' => 'Дата госпитализации',
                'rules' => '',
                'minValue' => 'field: EvnDtpWound_ObrDate',
                'maxValue' => 'now',
                'type' => 'date'),
            array('field' => 'EvnDtpWound_DtpDate',
                'label' => 'Дата ДТП',
                'rules' => 'required',
                'maxValue' => 'now',
                'type' => 'date'),
            array('field' => 'Diag_pid',
                'label' => 'Диагноз при обращении',
                'rules' => 'required',
                'type' => 'id'),
            array('field' => 'Diag_eid',
                'label' => 'Внешняя причина ДТП',
                'rules' => 'required',
                'type' => 'id'),
            array('field' => 'EvnDtpWound_OtherLpuDate',
                'label' => 'Дата перевода в другое ЛПУ',
                'rules' => '',
                'minValue' => 'field: EvnDtpWound_ObrDate',
                'maxValue' => 'now',
                'type' => 'date'),
            array('field' => 'Lpu_oid',
                'label' => 'ЛПУ, куда переведен раненый',
                'rules' => '',
                'type' => 'id'),
            array('field' => 'Diag_oid',
                'label' => 'Диагноз при переводе',
                'rules' => '',
                'type' => 'id'),
            array('field' => 'MedPersonal_id',
                'label' => 'Врач, составивший извещение',
                'rules' => '',
                'type' => 'id'),
            array('field' => 'EvnDtpWound_setDate',
                'label' => 'Дата составления извещения',
                'rules' => '',
                'minValue' => 'field: EvnDtpWound_ObrDate',
                'maxValue' => 'now',
                'type' => 'date')),
        'loadEvnDtpWoundEditForm' => array(array('field' => 'EvnDtpWound_id',
                'label' => 'Идентификатор извещения о раненом в ДТП',
                'rules' => 'required',
                'type' => 'id')),
        'printEvnDtpWound' => array(array('field' => 'EvnDtpWound_id',
                'label' => 'Идентификатор извещения о раненом в ДТП',
                'rules' => 'required',
                'type' => 'id')),
        'deleteEvnDtpWound' => array(array('field' => 'EvnDtpWound_id',
                'label' => 'Идентификатор извещения о раненом в ДТП',
                'rules' => 'required',
                'type' => 'id')),);

    /**
     * EvnDtp constructor.
     */
    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('EvnDtp_model', 'dbmodel');
    }

    /**
     *  Удаление извещения о раненом в ДТП
     *  Входящие данные: $_POST['EvnDtpWound_id']
     *  На выходе: JSON-строка
     *  Используется: форма поиска извещения о раненом ДТП
     */
    function deleteEvnDtpWound() {
        $data = array();

        $data = $this->ProcessInputData('deleteEvnDtpWound',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->deleteEvnDtpWound($data);
        $this->ProcessModelSave($response,true,'При удалении извещения о раненом в ДТП возникли ошибка')->ReturnData();
        return true;
    }

    /**
     *  Получение данных для формы редактирования извещения о ДТП
     *  Входящие данные: $_POST['EvnDtpWound_id']
     *  На выходе: JSON-строка
     *  Используется: форма редактирования извещения о ДТП
     */
    function loadEvnDtpWoundEditForm() {
        $data = array();

        $data = $this->ProcessInputData('loadEvnDtpWoundEditForm',true);
        if ($data === false) {return false;}

        $res = $this->dbmodel->loadEvnDtpWoundEditForm($data);

        array_walk($res, 'ConvertFromWin1251ToUTF8');
        $res = array('success' => true,
            'data' => $res);

        $this->ReturnData($res);

        return true;
    }

    /**
     *  Сохранение извещения о раненом в ДТП
     *  Входящие данные: ...
     *  На выходе: JSON-строка
     *  Используется: форма редактирования извещения о ДТП
     */
    function saveEvnDtpWound() {
        $this->load->helper('Options');

        $data = array();

        $data = $this->ProcessInputData('saveEvnDtpWound',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->saveEvnDtpWound($data);

        $this->ProcessModelSave($response,true,'Ошибка при сохранении извещения о ранении в ДТП')->ReturnData();

        return true;
    }

    /**
     *  Печать извещения о раненом в ДТП
     *  Входящие данные: $_GET['EvnDtpWound_id']
     *  На выходе: форма для печати извещения о раненом в ДТП
     *  Используется: форма редактирования извещения о раненом в ДТП
     */
    function printEvnDtpWound() {
        $this->load->library('parser');

        $data = array();
        $data = $this->ProcessInputData('printEvnDtpWound',true);
        if ($data === false) {return false;}

        // Получаем данные по талону
        $response = $this->dbmodel->getEvnDtpWoundFields($data);

        if (!is_array($response) || count($response) == 0) {
            echo 'Ошибка при получении данных по КВС';
            return true;
        }

        $template = (getRegionNick() == 'kz') ? 'evn_dtp_wound_template_a4_kz' : 'evn_dtp_wound_template_a4';
		
        $arMonthOf = array(
            1 => "января",
            2 => "февраля",
            3 => "марта",
            4 => "апреля",
            5 => "мая",
            6 => "июня",
            7 => "июля",
            8 => "августа",
            9 => "сентября",
            10 => "октября",
            11 => "ноября",
            12 => "декабря",
        );
        $setDate = strtotime(returnValidHTMLString($response[0]['EvnDtpWound_setDate']));

        $print_data = array(
            'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
            'Lpu_UAddress' => returnValidHTMLString($response[0]['Lpu_UAddress']),
            'Lpu_Phone' => returnValidHTMLString($response[0]['Lpu_Phone']),
            'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
            'Sex_Name_Male' => returnValidHTMLString($response[0]['Sex_Name']) == 'Мужской' ? 'X' : '&nbsp;&nbsp;',
            'Sex_Name_Female' => returnValidHTMLString($response[0]['Sex_Name']) == 'Женский' ? 'X' : '&nbsp;&nbsp;',
            'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
            'EvnDtpWound_ObrDate' => returnValidHTMLString($response[0]['EvnDtpWound_ObrDate']),
            'EvnDtpWound_HospDate' => returnValidHTMLString($response[0]['EvnDtpWound_HospDate']),
            'EvnDtpWound_DtpDate' => returnValidHTMLString($response[0]['EvnDtpWound_DtpDate']),
            'EvnDtpWound_setDate' => date("\"d\" " . $arMonthOf[date("n", $setDate)] . " Y", $setDate),
            'DiagP_Name' => returnValidHTMLString($response[0]['DiagP_Name']),
            'DiagP_Code' => returnValidHTMLString($response[0]['DiagP_Code']),
            'DiagE_Name' => returnValidHTMLString($response[0]['DiagE_Name']),
            'DiagE_Code' => returnValidHTMLString($response[0]['DiagE_Code']),
            'EvnDtpWound_OtherLpuDate' => returnValidHTMLString($response[0]['EvnDtpWound_OtherLpuDate']),
            'OtherLpu_Name' => returnValidHTMLString($response[0]['OtherLpu_Name']),
            'DiagO_Name' => returnValidHTMLString($response[0]['DiagO_Name']),
            'DiagO_Code' => returnValidHTMLString($response[0]['DiagO_Code']),
            'MedPersonal_Dolgnost' => returnValidHTMLString($response[0]['MedPersonal_Dolgnost']),
            'MedPersonal_Fin' => returnValidHTMLString($response[0]['MedPersonal_Fin'])
        );

        return $this->parser->parse($template, $print_data);
    }

}
