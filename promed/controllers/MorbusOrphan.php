<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusOrphan - контроллер для MorbusOrphan
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      10.2012
 *
 * @property MorbusOrphan_model $dbmodel
 * @property EvnNotifyOrphan_model $EvnNotifyOrphan_model
 */

class MorbusOrphan extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		'saveMorbusSpecific' => array(
			array('field' => 'Mode','label' => 'Режим сохранения','rules' => 'trim|required','type' => 'string'),
			array('field' => 'MorbusOrphan_id','label' => 'Идентификатор специфики заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Evn_pid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
			array('field' => 'Diag_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'MorbusBase_id','label' => 'Идентификатор базового заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания','rules' => '','type' => 'id')
		),
		'setParameter' => array(
			array('field' => 'Mode','label' => 'Режим сохранения','rules' => 'trim|required','type' => 'string'),
			array('field' => 'MorbusOrphan_id','label' => 'Идентификатор специфики заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'MorbusBase_id','label' => 'Идентификатор базового заболевания','rules' => 'required','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
		),
        'exportMorbusOrph' => array(
            /*array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type' => 'id'
            ),*/
            array(
                'field' => 'ExportType',
                'label' => 'Тип выгружаемого регистра',
                'rules' => '',
                'type' => 'int'
            )
			/*,
            array(
                'field' => 'ExportDate',
                'label' => 'Дата выгрузки',
                'rules' => '',
                'type' => 'date'
            )
			*/
        )
    );

	/**
	 * Description
	 */
	function __construct () 
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('MorbusOrphan_model', 'dbmodel');
	}
	
	/**
	 *  Сохранение/создание записи
	 *  Используется: пока нигде, сделано на случай сохранения из формы всех параметров специфики
     *
     * @return bool
	 */
	function saveMorbusSpecific() {
		$data = $this->ProcessInputData('saveMorbusSpecific', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра ЛПУ, в которой впервые установлен диагноз орфанного заболевания
	 *  Используется: Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setLpu_oid() {
		$this->inputRules['setParameter'][] = array('field' => 'Lpu_oid','label' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания','rules' => 'required','type' => 'id');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$data['Lpu_id'] = $data['Lpu_oid'];
		$response = $this->dbmodel->saveMorbusSpecific($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}
		
	/**
	 *  Сохранение параметра Diag_id
	 *  Используется: Форма просмотра записи регистра
     *
     * @return bool
	 */
	function setDiag_id() {
		$this->inputRules['setParameter'][] = array('field' => 'Diag_id','label' => 'Диагноз орфанного заболевания','rules' => 'required','type' => 'id');
		$data = $this->ProcessInputData('setParameter', true);
		if ($data === false) { return false; }
		$tmp = $this->dbmodel->saveMorbusSpecific($data);
		if (empty($tmp[0]['Error_Msg'])) {
			// создать Направление на внесение изменений в регистр»
			$this->load->model('EvnNotifyOrphan_model');
			$response = $this->EvnNotifyOrphan_model->createEvnDirectionOrphan($data);
			$response[0] = array_merge($response[0],$tmp[0]);
			$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
			return true;
		} else {
			$this->ProcessModelSave($tmp, true, 'При сохранении возникли ошибки')->ReturnData();
			return true;
		}
	}


    /**
     * Функция возвращает в XML список регионального сегмента регистра по орфанным заболеваниям
     */
    function exportMorbusOrph()
    {
        $data = $this->ProcessInputData('exportMorbusOrph', true);
        if ($data === false) { return false; }

        set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
        $export_orphan = $this->dbmodel->exportMorbusOrph($data);

        if ($export_orphan === false)
        {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
            return false;
        }
        if ( isset($export_orphan['Error_Code']) && ($export_orphan['Error_Code'] == 1) )
        {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по орфанным заболеваниям при указанных параметрах в базе данных отсутствуют.')));
            return false;
        }

        //$this->textlog->add('exportRegistryToXml: Получили все данные из БД ');
        $this->load->library('parser');

        // каталог в котором лежат выгружаемые файлы
        $out_dir = "re_xml_".time()."_"."orphan_register";
        if (!file_exists(EXPORTPATH_ORPHAN))
            mkdir( EXPORTPATH_ORPHAN );
        mkdir( EXPORTPATH_ORPHAN.$out_dir );


        $orph_register_file_name = "ORPH_REGISTER";
        // файл-перс. данные
        $file_re_pers_data_name = EXPORTPATH_ORPHAN.$out_dir."/".$orph_register_file_name.".xml";

        //Для разных регионов
        //$rgn = ($_SESSION['region']['nick']!='perm')?'_'.$_SESSION['region']['nick']:'';
        //$templ = "orph_register".$rgn;

        $templ = "orph_register";
        array_walk_recursive($export_orphan, 'ConvertFromWin1251ToUTF8');
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $export_orphan, true);
        $xml = str_replace('&', '&amp;', $xml);

        file_put_contents($file_re_pers_data_name, $xml);

        $file_zip_sign = $orph_register_file_name;
        $file_zip_name = EXPORTPATH_ORPHAN.$out_dir."/".$file_zip_sign.".zip";
        $zip = new ZipArchive();
        $zip->open($file_zip_name, ZIPARCHIVE::CREATE);
        $zip->AddFile( $file_re_pers_data_name, $orph_register_file_name . ".xml" );
        $zip->close();

        unlink($file_re_pers_data_name);

        if (file_exists($file_zip_name))
        {
            $this->ReturnData(array('success' => true,'Link' => $file_zip_name));
        }
        else {
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
        }
        return true;
    }

}