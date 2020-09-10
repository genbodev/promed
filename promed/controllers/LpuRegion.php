<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Контроллер для работы с участками, прикреплением
 * @property LpuRegion_model LpuRegion_model
 */
class LpuRegion extends swController {
	public $inputRules = array(
		'getMedPersLpuRegionList' => array(
			array('field' => 'LpuSectionProfile_Code','label' => 'Код профиля врача','rules' => 'trim','type' => 'string'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => 'required','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'МО','rules' => 'required','type' => 'id'),
			array('field' => 'Ignore_Closed','label' => 'Не показывать закрытые','rules' => '','type' => 'id')
		),
		'getAttachData' => array(
			array('field' => 'MedPersonal_id','label' => 'Идентификатор врача','rules' => 'required','type' => 'id'),
			array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => 'required','type' => 'id')			
		),
		'getLpuRegionsByAddress' => array(
			array(
                'field' => 'KLCity_id',
                'label' => 'Идентификатор Улицы',
                'rules' => '',
                'type'  => 'int'
            ),
			array(
                'field' => 'KLStreet_id',
                'label' => 'Идентификатор Улицы',
                'rules' => '',
                'type'  => 'int'
            ),
			array(
                'field' => 'domNum',
                'label' => 'Номер дома',
                'rules' => '',
                'type'  => 'string'
            ),
			array(
                'field' => 'LpuRegionType_Codes',
                'label' => 'Коды типов участков',
                'rules' => '',
                'type'  => 'string'
            )
		),
        'getLpuRegionListFeld' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type'  => 'int'
            ),
            array(
                'field' => 'LpuRegion_date',
                'label' => 'Дата',
                'rules' => '',
                'type'  => 'string'
            ),
            array(
                'field' => 'Org_id',
                'label' => 'Идентификатор организации',
                'rules' => '',
                'type'  => 'int'
            )
        )
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('LpuRegion_model', 'LpuRegion_model');
	}

	/**
	 * Получение данных: "ЛПУ прикрепления", "Тип прикрепления:", "Тип участка:", "Участок:", на котором врач АРМа является врачом на участке
	 *  Входящие данные: $_POST['MedPersonal_id'] $_POST['Lpu_id']
	 *  На выходе: JSON-строка
	 */
	function getAttachData() {
		$data = $this->ProcessInputData('getAttachData', true);
		if ($data) {
			$response = $this->LpuRegion_model->getMedPersLpuRegionList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение участков врача в определенной МО с указанием типа участка и типа прикрепления
	 * Если будет передан идентификатор рабочего места,
	 * то участки будут отфильтрованы по типу прикрепления в зависимости от профиля врача
	 * Входящие данные: $_POST['MedPersonal_id'] $_POST['Lpu_id'] $_POST['LpuSectionProfile_Code'] $_POST['MedStaffFact_id']
	 * На выходе: JSON-строка
	 */
	function getMedPersLpuRegionList() {
		$data = $this->ProcessInputData('getMedPersLpuRegionList', true);
		if ($data) {
			$response = $this->LpuRegion_model->getMedPersLpuRegionList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Получение фельдшердских участков
     */
    function getLpuRegionListFeld() {
        $data = $this->ProcessInputData('getLpuRegionListFeld',true);
        if($data){
            $response = $this->LpuRegion_model->getLpuRegionListFeld($data);
            //var_dump($response);die;
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        }
        else
            return false;
    }
	
    /**
     * Получение фельдшердских участков
     */
    function getLpuRegionsByAddress() {
        $data = $this->ProcessInputData('getLpuRegionsByAddress',true);
        if($data){
            $response = $this->LpuRegion_model->getLpuRegionsByAddress($data);
            //var_dump($response);die;
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        }
        else
            return false;
    }
}