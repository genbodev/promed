<?php   defined('BASEPATH') or die('No direct script access allowed');
/**
* EvnDtp - извещения о скончавшемся в ДТП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Stac
* @access			public
* @copyright		Copyright (c) 2016 Swan Ltd.
* @author           Alexander Kurakin (a.kurakin@swan.perm.ru)
* @version			2016
*/

defined('BASEPATH') or die('No direct script access allowed');

class EvnDtpDeath extends swController {

    public $inputRules = array(
        'saveEvnDtpDeath' => array(
            array(
                'field' => 'EvnDtpDeath_id',
                'label' => '',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Server_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'PersonEvn_id',
                'label' => '',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnDtpDeath_DeathDate',
                'label' => 'Дата смерти',
                'rules' => 'required',
                'minValue' => 'field: EvnDtpDeath_DtpDate',
                'maxValue' => 'now',
                'type' => 'date'
            ),
            array(
                'field' => 'EvnDtpDeath_HospDate',
                'label' => 'Дата госпитализации',
                'rules' => '',
                'minValue' => 'field: EvnDtpDeath_DtpDate',
                'maxValue' => 'now',
                'type' => 'date'
            ),
            array(
                'field' => 'EvnDtpDeath_DtpDate',
                'label' => 'Дата ДТП',
                'rules' => 'required',
                'maxValue' => 'now',
                'type' => 'date'
            ),
            array(
                'field' => 'Diag_pid',
                'label' => 'Диагноз при поступлении',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'Diag_eid',
                'label' => 'Внешняя причина смерти',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Diag_iid',
                'label' => 'Непосредственная причина смерти',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Diag_mid',
                'label' => 'Основная причина смерти',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'DtpDeathPlace_id',
                'label' => 'Место наступления смерти',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'DtpDeathTime_id',
                'label' => 'Срок наступления смерти от момента ДТП',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Врач, составивший извещение',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnDtpDeath_setDate',
                'label' => 'Дата составления извещения',
                'rules' => '',
                'minValue' => 'field: EvnDtpDeath_DtpDate',
                'maxValue' => 'now',
                'type' => 'date'
            )
        ),
        'loadEvnDtpDeathEditForm' => array(
            array(
                'field' => 'EvnDtpDeath_id',
                'label' => 'Идентификатор извещения о скончавшемся в ДТП',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'printEvnDtpDeath' => array(
            array(
                'field' => 'EvnDtpDeath_id',
                'label' => 'Идентификатор извещения о скончавшемся в ДТП',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'deleteEvnDtpDeath' => array(
            array(
                'field' => 'EvnDtpDeath_id',
                'label' => 'Идентификатор извещения о скончавшемся в ДТП',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
    );

    /**
     *  Comment
     */
    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('EvnDtpDeath_model', 'dbmodel');
    }

    /**
     *  Удаление извещения о скончавшемся в ДТП
     *  Входящие данные: $_POST['EvnDtpDeath_id']
     *  На выходе: JSON-строка
     *  Используется: форма поиска извещения о скончавшемся ДТП
     */
    function deleteEvnDtpDeath() {
        $data = array();

        $data = $this->ProcessInputData('deleteEvnDtpDeath',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->deleteEvnDtpDeath($data);
        $this->ProcessModelSave($response,true,'При удалении извещения о скончавшемся в ДТП возникли ошибка')->ReturnData();
        return true;
    }

    /**
     *  Получение данных для формы редактирования извещения о скончавшемся в ДТП
     *  Входящие данные: $_POST['EvnDtpDeath_id']
     *  На выходе: JSON-строка
     *  Используется: форма редактирования извещения о скончавшемся в ДТП
     */
    function loadEvnDtpDeathEditForm() {
        $data = array();

        $data = $this->ProcessInputData('loadEvnDtpDeathEditForm',true);
        if ($data === false) {return false;}

        $res = $this->dbmodel->loadEvnDtpDeathEditForm($data);

        array_walk($res, 'ConvertFromWin1251ToUTF8');
        $res = array('success' => true,
            'data' => $res);

        $this->ReturnData($res);

        return true;
    }

    /**
     *  Сохранение извещения о скончавшемся в ДТП
     *  Входящие данные: ...
     *  На выходе: JSON-строка
     *  Используется: форма редактирования извещения о скончавшемся в ДТП
     */
    function saveEvnDtpDeath() {
        $this->load->helper('Options');

        $data = array();

        $data = $this->ProcessInputData('saveEvnDtpDeath',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->saveEvnDtpDeath($data);

        $this->ProcessModelSave($response,true,'Ошибка при сохранении извещения о скончавшемся в ДТП')->ReturnData();

        return true;
    }

    /**
     *  Печать извещения о скончавшемся в ДТП
     *  Входящие данные: $_GET['EvnDtpDeath_id']
     *  На выходе: форма для печати извещения о скончавшемся в ДТП
     *  Используется: форма редактирования извещения о скончавшемся в ДТП
     */
    function printEvnDtpDeath() {
        $this->load->library('parser');

        $data = array();
        $data = $this->ProcessInputData('printEvnDtpDeath',true);
        if ($data === false) {return false;}

        // Получаем данные по талону
        $response = $this->dbmodel->getEvnDtpDeathFields($data);

        if (!is_array($response) || count($response) == 0) {
            echo 'Ошибка при получении данных по КВС';
            return true;
        }
		
		$template = (getRegionNick() == 'kz') ? 'evn_dtp_death_template_a4_kz' : 'evn_dtp_death_template_a4';
		
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
        if(isset($response[0]['EvnDtpDeath_setDate']))
            $setDate = strtotime(returnValidHTMLString($response[0]['EvnDtpDeath_setDate']));
        if(
            (isset($response[0]['DeathPlace'])&&($response[0]['DeathPlace'] == 2)) && (isset($response[0]['DeathTime'])&&($response[0]['DeathTime'] == 1))
          ) {
            $inStac7 = 'X';
        } else {
            $inStac7 = '&nbsp;&nbsp;';
        }

        if(
            (isset($response[0]['DeathPlace'])&&($response[0]['DeathPlace'] == 3)) && (isset($response[0]['DeathTime'])&&($response[0]['DeathTime'] == 1))
          ) {
            $atHome7 = 'X';
        } else {
            $atHome7 = '&nbsp;&nbsp;';
        }

        $print_data = array(
            'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
            'Lpu_UAddress' => returnValidHTMLString($response[0]['Lpu_UAddress']),
            'Lpu_Phone' => returnValidHTMLString($response[0]['Lpu_Phone']),
            'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
            'Sex_Name_Male' => returnValidHTMLString($response[0]['Sex_Name']) == 'Мужской' ? 'X' : '&nbsp;&nbsp;',
            'Sex_Name_Female' => returnValidHTMLString($response[0]['Sex_Name']) == 'Женский' ? 'X' : '&nbsp;&nbsp;',
            'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
            'EvnDtpDeath_DeathDate' => returnValidHTMLString($response[0]['EvnDtpDeath_DeathDate']),
            'EvnDtpDeath_HospDate' => returnValidHTMLString($response[0]['EvnDtpDeath_HospDate']),
            'EvnDtpDeath_DtpDate' => returnValidHTMLString($response[0]['EvnDtpDeath_DtpDate']),
            'EvnDtpDeath_setDate' => date("\"d\" " . $arMonthOf[date("n", $setDate)] . " Y", $setDate),
            'DiagP_Name' => returnValidHTMLString($response[0]['DiagP_Name']),
            'DiagP_Code' => returnValidHTMLString($response[0]['DiagP_Code']),
            'DiagI_Name' => returnValidHTMLString($response[0]['DiagI_Name']),
            'DiagI_Code' => returnValidHTMLString($response[0]['DiagI_Code']),
            'DiagE_Name' => returnValidHTMLString($response[0]['DiagE_Name']),
            'DiagE_Code' => returnValidHTMLString($response[0]['DiagE_Code']),
            'DiagM_Name' => returnValidHTMLString($response[0]['DiagM_Name']),
            'DiagM_Code' => returnValidHTMLString($response[0]['DiagM_Code']),
            'MedPersonal_Dolgnost' => returnValidHTMLString($response[0]['MedPersonal_Dolgnost']),
            'MedPersonal_Fin' => returnValidHTMLString($response[0]['MedPersonal_Fin']),
            'inSMP' => (isset($response[0]['DeathPlace'])&&($response[0]['DeathPlace'] == 1)) ? 'X' : '&nbsp;&nbsp;',
            'inStac30' =>  (isset($response[0]['DeathPlace'])&&($response[0]['DeathPlace'] == 2)) ? 'X' : '&nbsp;&nbsp;',
            'inStac7' =>  $inStac7,
            'atHome30' =>  (isset($response[0]['DeathPlace'])&&($response[0]['DeathPlace'] == 3)) ? 'X' : '&nbsp;&nbsp;',
            'atHome7' =>  $atHome7
        );

        return $this->parser->parse($template, $print_data);
    }

}
