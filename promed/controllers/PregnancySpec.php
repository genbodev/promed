<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PregnancySpec - специфика беременности и родов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Created by JetBrains PhpStorm.
 * User: IGabdushev
 * Date: 24.10.11
 * Time: 10:10
 */
/**
 * @property PregnancySpec_model $dbmodel
 * @property CI_Loader $load
 * @property EvnUsluga_model $usluga_dbmodel
 * */
class PregnancySpec extends swController
{

    public $inputRules = array(
        'load' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики о беременности и родах в ДУ',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadAnotherLpuList' => array(
            array(
                'field' => 'PersonDisp_id',
                'label' => 'Идентификатор карты ДУ',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadEvnUslugaPregnancySpecGrid' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики о беременности и родах в ДУ',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadEvnUslugaPregnancySpecForm' => array(
            array(
                'field' => 'id',
                'label' => 'Идентификатор услуги',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadPregnancySpecComplication' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики по беременности и родам в карте ДУ',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'loadPregnancySpecExtragenitalDisease' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики по беременности и родам в карте ДУ',
                'rules' => 'required',
                'type' => 'id'
            ),
        ),
        'saveEvnUslugaPregnancySpec' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики по беременности и родам в карте ДУ',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnUslugaPregnancySpec_id',
                'label' => 'Идентификатор общей услуги',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnUslugaCommon_Kolvo',
                'label' => 'Количество',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'EvnUslugaPregnancySpec_rid',
                'label' => 'Идентификатор родительского события',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'EvnUslugaCommon_setDate',
                'label' => 'Дата оказания услуги',
                'rules' => 'trim|required',
                'type' => 'date'
            ),
            array(
                'field' => 'EvnUslugaCommon_disDate',
                'label' => 'Дата окончания услуги',
                'rules' => 'trim|required',
                'type' => 'date'
            ),
            array(
                'field' => 'EvnUslugaCommon_setTime',
                'label' => 'Время оказания услуги',
                'rules' => 'trim',
                'type' => 'time'
            ),
            array(
                'field' => 'EvnUslugaCommon_disTime',
                'label' => 'Время окончания услуги',
                'rules' => 'trim',
                'type' => 'time'
            ),
            array(
                'field' => 'Lpu_uid',
                'label' => 'ЛПУ',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'Org_uid',
                'label' => 'Другая организация',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSection_uid',
                'label' => 'Отделение',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSectionProfile_id',
                'label' => 'Профиль',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal_id',
                'label' => 'Врач',
                'rules' => 'trim',
                'type' => 'id'
            ),
            array(
                'field' => 'PayType_id',
                'label' => 'Вид оплаты',
                'rules' => 'trim|required',
                'type' => 'id'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonEvn_id',
                'label' => 'Идентификатор состояния человека',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Server_id',
                'label' => 'Идентификатор сервера',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'UslugaComplex_id',
                'label' => 'Услуга',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'UslugaPlace_id',
                'label' => 'Место оказывания услуги',
                'rules' => 'required',
                'type' => 'id'
            ),
            // для сохранения анамнеза
            array(
                'field' => 'AnamnezData',
                'label' => 'Данные специфики',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'XmlTemplate_id',
                'label' => 'Идентификатор шаблона',
                'rules' => 'trim',
                'type' => 'id'
            )
        ),
        'save' => array(
            array(
                'field' => 'PregnancySpec_id',
                'label' => 'Идентификатор специфики по беременности и родам в карте ДУ',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_aid',
                'label' => 'Наблюдалась в другом ЛПУ',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'PregnancySpec_Period',
                'label' => ' Срок беременности при взятии на учет (нед)',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'PregnancySpec_Count',
                'label' => 'Которая беременность',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'PregnancySpec_CountBirth',
                'label' => 'Из них закончились родами',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'PregnancySpec_CountAbort',
                'label' => 'Из них закончились абортами',
                'rules' => 'required',
                'type' => 'int'
            ),
            array(
                'field' => 'PregnancySpec_BirthDT',
                'label' => 'Предполагаемая дата',
                'rules' => 'required|trim',
                'type' => 'date'
            ),
            array(
                'field' => 'PregnancySpec_IsHIVtest',
                'label' => 'Обследована на ВИЧ',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'BirthResult_id',
                'label' => 'Исход беременности',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'PregnancySpec_OutcomDT',
                'label' => 'Дата исхода',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'PregnancySpec_OutcomPeriod',
                'label' => 'Срок исхода',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'BirthSpec_id',
                'label' => 'Особенности родов',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonDisp_id',
                'label' => 'Идентификатор карты ДУ пациента',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'PregnancySpec_IsHIV',
                'label' => 'Наличие ВИЧ-инфекции',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'PregnancySpecComplication',
                'label' => 'Осложнения беременности',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'PregnancySpecExtragenitalDisease',
                'label' => 'Экстрагенитальные заболевания',
                'rules' => '',
                'type' => 'string'
            )
        ),
        'loadPregnancySpecList' => array(
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
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
        $this->load->model('PregnancySpec_model', 'dbmodel');
    }


    /**
     * Загрузка данных специфики
     */
    function load()
    {
        $data = array();
        $val = array();

        $data = $this->ProcessInputData('load',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->load($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
    }

    /**
     *  Сохранение общей услуги
     *  Входящие данные: ...
     *  На выходе: JSON-строка
     *  Используется: форма редактирования общей услуги
     * @return boolean
     */
    function saveEvnUslugaPregnancySpec()
    {
        $data = array();
        //$val = array();
        $data = $this->ProcessInputData('saveEvnUslugaPregnancySpec',true);
        if ($data === false) {return false;}
        // Проверка на дубли
        $this->load->model('EvnUsluga_model', 'usluga_dbmodel');
        $data['EvnUslugaCommon_id'] = $data['EvnUslugaPregnancySpec_id'];
        $response = $this->usluga_dbmodel->checkEvnUslugaDoubles($data, 'common');

        if ($response == -1) {
            $val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении проверки услуг на дубли');
            array_walk($val, 'ConvertFromWin1251ToUTF8');
            $this->ReturnData($val);
            return false;
        }
        else if ($response > 0) {
            $val = array('success' => false, 'Error_Msg' => 'Сохранение отменено, т.к. данная услуга уже заведена в талоне/КВС. Если было выполнено несколько услуг, то измените количество в ранее заведенной услуге');
            array_walk($val, 'ConvertFromWin1251ToUTF8');
            $this->ReturnData($val);
            return false;
        }

        $response = $this->dbmodel->saveEvnUslugaPregnancySpec($data);

        if (is_array($response) && count($response) > 0) {
            $val = $response[0];
            if (strlen($val['Error_Msg']) == 0) {
                $val['success'] = true;
            }
            else {
                $val['success'] = false;
            }
        }
        else {
            $val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении общей услуги');
        }

        array_walk($val, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData($val);
        return true;
    }

    /**
     * Загрузка данных грида лабораторных обследований
     */
    function loadEvnUslugaPregnancySpecGrid()
    {
        $data = $this->ProcessInputData('loadEvnUslugaPregnancySpecGrid', true, true);

        if (is_array($data) && count($data) > 0) {
            $response = $this->dbmodel->loadEvnUslugaPregnancySpecGrid($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Загрузка данных комбобокса других ЛПУ, гдк наблюдалась роженица
     */
    function loadAnotherLpuList()
    {
        $data = $this->ProcessInputData('loadAnotherLpuList', true, true);
        if ($data) {
            $response = $this->dbmodel->loadAnotherLpuList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение специфики
     */
    function save()
    {
        $data = array();
        //$val = array();
        // Получаем сессионные переменные

        $data = $this->ProcessInputData('save',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->save($data);
        if (is_array($response) && count($response) > 0) {
            $val = $response[0];
            if (array_key_exists('Error_Msg', $val) && empty($val['Error_Msg'])) {
                $val['success'] = true;
                $val['PregnancySpec_id'] = $response[0]['PregnancySpec_id'];
                if ( !empty($data['PregnancySpecComplication']) ) {
                    // Сохранение Осложнений беременности
                    $tmpstr = $data['PregnancySpecComplication'];
                    ConvertFromWin1251ToUTF8($tmpstr);
                    $personHeightData = json_decode($tmpstr, true);
                    if ( is_array($personHeightData) ) {
                        for ( $i = 0; $i < count($personHeightData); $i++ ) {
                            $personHeight = array('pmUser_id' => $data['pmUser_id'], 'Server_id' => $_SESSION['server_id']);
                            if(( empty($personHeightData[$i]['Diag_id']) || !is_numeric($personHeightData[$i]['Diag_id']) )
                                ||( empty($personHeightData[$i]['PregnancySpecComplication_id']) || !is_numeric($personHeightData[$i]['PregnancySpecComplication_id']) )
                                ||( empty($personHeightData[$i]['PSC_setDT']) || CheckDateFormat($personHeightData[$i]['PSC_setDT']) > 0 )
                                ||( !isset($personHeightData[$i]['RecordStatus_Code']) || !is_numeric($personHeightData[$i]['RecordStatus_Code']) || !in_array($personHeightData[$i]['RecordStatus_Code'], array(0, 2, 3)) )){
                                continue;
                            }
                            $personHeight['Diag_id'] = $personHeightData[$i]['Diag_id'];
                            $personHeight['PregnancySpec_id'] = $val['PregnancySpec_id'];
                            $personHeight['PregnancySpecComplication_id'] = $personHeightData[$i]['PregnancySpecComplication_id'];
                            $personHeight['PSC_setDT'] = ConvertDateFormat($personHeightData[$i]['PSC_setDT']);
                            $personHeight['RecordStatus_Code'] = $personHeightData[$i]['RecordStatus_Code'];
                            switch ( $personHeight['RecordStatus_Code'] ) {
                                case 0:
                                case 2:
                                    /*$response = */$this->dbmodel->savePregnancySpecComplication($personHeight);
                                break;
                                case 3:
                                    /*$response = */$this->dbmodel->deletePregnancySpecComplication($personHeight);
                                break;
                            }
                        }
                    }
                }
                if ( !empty($data['PregnancySpecExtragenitalDisease']) ) {
                    // Сохранение Осложнений беременности
                    $tmpstr = $data['PregnancySpecExtragenitalDisease'];
                    ConvertFromWin1251ToUTF8($tmpstr);
                    $personHeightData = json_decode($tmpstr, true);
                    if ( is_array($personHeightData) ) {
                        for ( $i = 0; $i < count($personHeightData); $i++ ) {
                            $personHeight = array('pmUser_id' => $data['pmUser_id'], 'Server_id' => $_SESSION['server_id']);
                            if(( empty($personHeightData[$i]['Diag_id']) || !is_numeric($personHeightData[$i]['Diag_id']) )
                                ||( empty($personHeightData[$i]['PSED_id']) || !is_numeric($personHeightData[$i]['PSED_id']) )
                                ||( empty($personHeightData[$i]['PSED_setDT']) || CheckDateFormat($personHeightData[$i]['PSED_setDT']) > 0 )
                                ||( !isset($personHeightData[$i]['RecordStatus_Code']) || !is_numeric($personHeightData[$i]['RecordStatus_Code']) || !in_array($personHeightData[$i]['RecordStatus_Code'], array(0, 2, 3)) )){
                                continue;
                            }
                            $personHeight['Diag_id'] = $personHeightData[$i]['Diag_id'];
                            $personHeight['PregnancySpec_id'] = $val['PregnancySpec_id'];
                            $personHeight['PSED_id'] = $personHeightData[$i]['PSED_id'];
                            $personHeight['PSED_setDT'] = ConvertDateFormat($personHeightData[$i]['PSED_setDT']);
                            $personHeight['RecordStatus_Code'] = $personHeightData[$i]['RecordStatus_Code'];
                            switch ( $personHeight['RecordStatus_Code'] ) {
                                case 0:
                                case 2:
                                    /*$response = */$this->dbmodel->savePregnancySpecExtragenitalDisease($personHeight);
                                break;
                                case 3:
                                    /*$response = */$this->dbmodel->deletePregnancySpecExtragenitalDisease($personHeight);
                                break;
                            }
                        }
                    }
                }
            }
            else {
                $val['success'] = false;
            }
        }
        else {
            $val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении измерения массы пациента');
        }

        array_walk($val, 'ConvertFromWin1251ToUTF8');

        $this->ReturnData($val);

        return true;
    }

    /**
     * Загрузка формы специфики беременности
     */
    function loadEvnUslugaPregnancySpecForm()
    {
        $data = array();
        $val = array();

        // Получаем сессионные переменные
        $data = $this->ProcessInputData('loadEvnUslugaPregnancySpecForm',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->loadEvnUslugaPregnancySpecForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

        return true;
    }

    /**
     * Загрузка грида осложнения беременности
     */
    function loadPregnancySpecComplication()
    {
        $data = array();
        $val = array();

        $data = $this->ProcessInputData('loadPregnancySpecComplication',true);
        if ($data === false) {return false;}
        $response = $this->dbmodel->loadPregnancySpecComplication($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

        return false;
    }



    /**
     * Загрузка грида Экстрагенитальные заболевания
     */
    function loadPregnancySpecExtragenitalDisease()
    {
        $data = array();
        $val = array();
        $data = $this->ProcessInputData('loadPregnancySpecExtragenitalDisease',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->loadPregnancySpecExtragenitalDisease($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

        return false;
    }


    /**
     * Загрузка списка ДУ по беременности
     */
    function loadPregnancySpecList()
    {
        $result = false;
        $data = $this->ProcessInputData('loadPregnancySpecList', true, true);
        if ($data) {
            $response = $this->dbmodel->loadPregnancySpecList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            $result = true;
        }
        return $result;
    }

}
