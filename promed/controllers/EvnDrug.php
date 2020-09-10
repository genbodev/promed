<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDrug - контроллер персонифицированного учета 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access				public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author				Марков Андрей
* @version			18.04.2010
*/

/**
 * @property EvnDrug_model $dbmodel
 */
class EvnDrug extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnDrug_model', 'dbmodel');

		$this->inputRules = array(
			'deleteEvnDrug' => array(
				array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор случая использования медикаментов',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrTreat_Fact',
					'label' => 'Отменить приемов',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnDrugPanel' => array(
				array(
					'field' => 'EvnDrug_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnDrugEditForm' => array(
				array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор случая использования медикаментов',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnDrugGrid' => array(
				array(
					'field' => 'EvnDrug_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getMolCombo' => array(
				array(
						'field' => 'Mol_id',
						'label' => 'Идентификатор МОЛ',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Contragent_id',
						'label' => 'Идентификатор контрагента',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'LpuSection_id',
						'label' => 'Идентификатор отделения',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Storage_id',
						'label' => 'Идентификатор склада',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'MedService_id',
						'label' => 'Идентификатор службы',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'Org_id',
						'label' => 'Идентификатор организации',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'onDate',
						'label' => 'На дату',
						'rules' => '',
						'type' => 'date'
					)
			),
			'loadDrugPrepList' => array(
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Иденитификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Storage_id',
					'label' => 'Иденитификатор склада',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Иденитификатор фасовки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'date',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
			),
			'loadDrugList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Storage_id',
					'label' => 'Идентификатор склада',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Комбобокс "Медикамент"',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'date',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
			),
			'loadDocumentUcStrList' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Storage_id',
					'label' => 'Идентификатор склада',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'date',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор случая использования медикаментов',
					'rules' => '',
					'type' => 'id'
				)
			),

			'loadEvnVizitPLWOW' => array(
				array(
						'field' => 'EvnDrug_id',
						'label' => 'Идентификатор талона по угл. обсл. ВОВ',
						'rules' => '',
						'type' => 'id'
					)
			),
			'loadEvnDrugView' => array(
				array(
						'field' => 'EvnDrug_id',
						'label' => 'Идентификатор персучета',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'EvnDrug_pid',
						'label' => 'Идентификатор карты',
						'rules' => '',
						'type' => 'id'
					)
			),
			'checkDoublePerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveEvnDrug' => array(
				array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор персучета',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MSF_LpuSection_id',
					'label' => 'MSF_LpuSection_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MSF_MedPersonal_id',
					'label' => 'MSF_MedPersonal_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MSF_MedService_id',
					'label' => 'MSF_MedService_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор Server',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnDrug_pid',
					'label' => 'Идентификатор карты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDrug_rid',
					'label' => 'Идентификатор КВС',
					'rules' => 'trim',
					'type'  => 'id'
				),
				array(
					'field' => 'EvnDrug_setDate',
					'label' => 'Дата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDrug_setTime',
					'label' => 'Время',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека в событии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Документ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_id',
					'label' => '',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'DocumentUcStr_oid',
					'label' => 'Партия',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Storage_id',
					'label' => 'Склад',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mol_id',
					'label' => 'Материально-ответственное лицо',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDrug_Kolvo',
					'label' => 'Количество',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'EvnDrug_KolvoEd',
					'label' => 'Количество единиц',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'EvnDrug_RealKolvo',
					'label' => 'Количество реальное',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'EvnDrug_Price',
					'label' => 'Цена',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'EvnDrug_Sum',
					'label' => 'Сумма',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'EvnCourseTreatDrug_id',
					'label' => 'EvnCourseTreatDrug_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrTreatDrug_id',
					'label' => 'EvnPrescrTreatDrug_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnCourse_id',
					'label' => 'EvnCourse_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'EvnPrescr_id',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescrTreat_Fact',
					'label' => 'Списать приемов',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'GoodsUnit_id',
					'label' => 'Единицы списания',
					'rules' => 'trim',
					'type' => 'id'
				),
                array(
                    'field' => 'GoodsUnit_bid',
                    'label' => 'Единицы учета',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
					'field' => 'arr_time',
					'label' => 'Параметры времени приема',	
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnDrugStreamList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время',
					'rules' => 'required',
					'type' => 'string'
				)				
			),
			'saveEvnUslugaWOW' => array(
				array(
					'field' => 'EvnUslugaWOW_id',
					'label' => 'Идентификатор исследования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор талона по угл. обсл. ВОВ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
						'field' => 'EvnUslugaWOW_setDate',
						'label' => 'Дата исследования',
						'rules' => 'required',
						'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaWOW_didDate',
					'label' => 'Дата результата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'DispWowUslugaType_id',
					'label' => 'Вид исследования',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getExecutedDocumentUcStrForEvnDrug' => array(
                array(
					'field' => 'EvnDrug_id',
					'label' => 'Идентификатор случая использования медикамента',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Удаление случая использования медикаментов
	*  Входящие данные: $_POST['EvnDrug_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function deleteEvnDrug() {
		$data = $this->ProcessInputData('deleteEvnDrug', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvnDrug($data);
		$this->ProcessModelSave($response, true, 'При удалении случая использования медикаментов возникли ошибки')->ReturnData();
		
		return true;
	}

	/**
	*  Получение данных для формы редактирования случая использования медикаментов
	*  Входящие данные: $_POST['EvnDrug_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая использования медикаментов
	*/
	function loadEvnDrugEditForm() {
		$data = $this->ProcessInputData('loadEvnDrugEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDrugEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	*  Получение списка случаев использования медикаментов
	*  Входящие данные: $_POST['EvnDrug_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadEvnDrugGrid() {
		$data = $this->ProcessInputData('loadEvnDrugGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDrugGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	*  Получение списка МОЛ
	*  На выходе: JSON-строка
	*  Используется: форма персучета медикаментов
	*/
	function getMolCombo()
	{
		$data = $this->ProcessInputData('getMolCombo', false);
        $session_data = getSessionParams();

        $data['Lpu_id'] = $session_data['Lpu_id'];

		if ( $data === false ) { return false; }
		
		if ($data['Lpu_id'] == 0) {
			$this->ReturnData(array('success' => false));
			return true;
		}
		
		$response = $this->dbmodel->getMolCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка медикаментов (DrugPrepList), доступных для выбора
	 *  Используется: форма персучета медикаментов
	 */
	function loadDrugPrepList() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadDrugPrepList', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadDrugPrepList($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка доступных партий медикаментов
	 */
	function loadDocumentUcStrList() {
		$data = $this->ProcessInputData('loadDocumentUcStrList', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->loadDocumentUcStrList($data);

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $row ) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');

				if ( isset($row['DocumentUcStr_Price']) ) {
					$row['DocumentUcStr_Price'] = number_format($row['DocumentUcStr_Price'], 2, '.', '');
				}

				if ( isset($row['DocumentUcStr_PriceR']) ) {
					$row['DocumentUcStr_PriceR'] = number_format($row['DocumentUcStr_PriceR'], 2, '.', '');
				}

				if ( isset($row['DocumentUcStr_Count']) ) {
					$row['DocumentUcStr_Count'] = number_format($row['DocumentUcStr_Count'], 4, '.', '');
				}

				$val[] = $row;
			}
		}

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Кривая загрузка списка случаев использования медикаментов
	 * @return bool
	 */
	function loadEvnDrugView()
	{
		$data = $this->ProcessInputData('loadEvnDrugView', true);
		if ( $data === false ) { return false; }

		if ($data['Lpu_id'] == 0) {
			$this->ReturnData(array('success' => false));
			return true;
		}
		
		$response = $this->dbmodel->loadEvnDrugView($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение медикамента персонифицированного учета
	 */
	function saveEvnDrug()
	{
		$jsonPost = file_get_contents('php://input');
		$jsonData = json_decode($jsonPost, true);

		if ( empty($_POST) && is_array($jsonData) )
		{
			$jsonData['EvnDrug_id'] = null; // Ext создает текстовый id для новый записей, сразу убираем
			$_POST = $jsonData;
		}

		//Получаем сессионные переменные
		$data = $this->ProcessInputData('saveEvnDrug', true);
		if ( $data === false ) { return false; }


		if ( ! empty($data['Mol_id']) && ! empty($data['EvnDrug_setDate']))
		{
			$response = $this->dbmodel->MolIsAvailable($data['Mol_id'], $data['EvnDrug_setDate']);
			if( $response == false ) {
				$this->ReturnError('Срок действия МОЛ истек. Сохранение документа учета невозможно');
				return false;
			}
		}

		$response = $this->dbmodel->saveEvnDrug($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

    /**
     * Получение идентификатора первой строки исполненного документа учета, связанной с EvnDrug
     */
    function getExecutedDocumentUcStrForEvnDrug() {
        $data = $this->ProcessInputData('getExecutedDocumentUcStrForEvnDrug', true);
        if ( $data === false ) { return false; }
        $str_id = $this->dbmodel->getExecutedDocumentUcStrForEvnDrug($data['EvnDrug_id']);
        $response = array(
            'DocumentUcStr_id' => $str_id,
            'success' => true
        );
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;
    }
    
	/**
	 *  Получение списка медикаментов для панели использования медикаментов в ЭМК
	 */
	function loadEvnDrugPanel() {
		$data = $this->ProcessInputData('loadEvnDrugPanel', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDrugPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}
?>