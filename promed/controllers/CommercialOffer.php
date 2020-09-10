<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов "Коммерческие предложения"
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       ModelGenerator
 * @version
 * @property CommercialOffer_model CommercialOffer_model
 */
class CommercialOffer extends swController
{
	/**
	 *  Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'CommercialOffer_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'CommercialOffer_begDT',
					'label' => 'дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Org_id',
					'label' => 'поставщик',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'CommercialOffer_Comment',
					'label' => 'примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugListJSON',
					'label' => 'Список медикаментов',
					'rules' => '',
					'type' => 'string'
				),
                array(
                    'field' => 'Org_did',
                    'label' => 'Организация',
                    'rules' => '',
                    'type' => 'id'
                )
			),
			'load' => array(
				array(
					'field' => 'CommercialOffer_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_did',
					'label' => 'Организация для которой предназначено предложение',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadCommercialOfferDrugList' => array(
				array(
					'field' => 'CommercialOffer_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'id_list',
					'label' => 'Список идентификаторов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'check_list',
					'label' => 'Список дополнительных проверок',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRlsDrugCombo' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Drug_Name',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Reg_Num',
					'label' => '№ РУ',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRlsDrugPrepFasCombo' => array(
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				)
			),
			'importFromXls' => array(
				array(
					'field' => 'CommercialOffer_id',
					'label' => 'Идентификатор коммерческого предложения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getCommercialOfferDrugContext' => array(
				array(
					'field' => 'DrugPrepFas_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Идентификатор медикамента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadOrgDidCombo' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				),
                array(
					'field' => 'UserOrg_id',
					'label' => 'Идентификатор организации пользователя',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getCommercialOfferDrugDetail' => array(
					array(
						'field'	=> 'CommercialOfferDrug_PriceDetail',
						'label'	=> 'Код СКП',
						'rules'	=> 'required',
						'type'	=> 'id'
					)
				)
		);
		$this->load->database();
		$this->load->model('CommercialOffer_model', 'CommercialOffer_model');
		$this->load->model('PMMediaData_model', 'PMMediaData_model');
	}

	/**
	 *  Сохранение коммерческого предложения
	 */
	function save() {
        $error = array();
		$data = $this->ProcessInputData('save', true);

		if ($data){
			if (isset($data['CommercialOffer_id'])) {
				$this->CommercialOffer_model->setCommercialOffer_id($data['CommercialOffer_id']);
			}
			if (isset($data['CommercialOffer_begDT'])) {
				$this->CommercialOffer_model->setCommercialOffer_begDT($data['CommercialOffer_begDT']);
			}
			if (isset($data['Org_id'])) {
				$this->CommercialOffer_model->setOrg_id($data['Org_id']);
			}
			if (isset($data['CommercialOffer_Comment'])) {
				$this->CommercialOffer_model->setCommercialOffer_Comment($data['CommercialOffer_Comment']);
			}
            if (isset($data['Org_did'])) {
                $this->CommercialOffer_model->setOrg_did($data['Org_did']);
            }

            //старт транзакции
            $this->CommercialOffer_model->beginTransaction();

            if (count($error) == 0 && getRegionNick() == 'kz') { //для Казахстана; проверка уникальности действующего прайса для СК Фармации
                $response = $this->CommercialOffer_model->checkSkFarmCommercialOfferUnique($data);
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }

            //сохранение данных коммерческого предложения
            if (count($error) == 0) {
                $response = $this->CommercialOffer_model->save();
                $this->ProcessModelSave($response, true, 'Ошибка при сохранении Коммерческие предложения');

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }

			if (count($error) == 0 && !empty($this->OutData['CommercialOffer_id'])) {
				//сохранение списка медикаментов
				$response = $this->CommercialOffer_model->saveCommercialOfferDrugFromJSON(array(
					'CommercialOffer_id' => $this->OutData['CommercialOffer_id'],
					'json_str' => $data['DrugListJSON']
				));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
			}

            if (count($error) > 0) {
                //откат транзакции
                $this->CommercialOffer_model->rollbackTransaction();
                $this->ReturnError($error[0]);
            } else {
                //коммит транзакции
                $this->CommercialOffer_model->commitTransaction();
                $this->ReturnData();
            }
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка коммерческого предложения
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->CommercialOffer_model->setCommercialOffer_id($data['CommercialOffer_id']);
			$response = $this->CommercialOffer_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка коммерческих предложений
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->CommercialOffer_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка спецификации коммерческого предложения
	 */
	function loadCommercialOfferDrugList() {
		$data = $this->ProcessInputData('loadCommercialOfferDrugList', true);
		if ($data) {
			$filter = $data;
			$response = $this->CommercialOffer_model->loadCommercialOfferDrugList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Удаление коммерческого предложения
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
            //дополнительные проверки перед удалением
            if (!empty($data['check_list'])) {
                $response = $this->CommercialOffer_model->checkBeforeDelete($data);
                if (!empty($response['Error_Msg'])) {
                    return $this->ReturnError($response['Error_Msg']);
                }
            }

			$response = $this->CommercialOffer_model->delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка данных для комбобокса
	 */
	function loadRlsDrugCombo() {
		$data = $this->ProcessInputData('loadRlsDrugCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->CommercialOffer_model->loadRlsDrugCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка данных для комбобокса
	 */
	function loadRlsDrugPrepFasCombo() {
		$data = $this->ProcessInputData('loadRlsDrugPrepFasCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->CommercialOffer_model->loadRlsDrugPrepFasCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Импорт спецификации коммерческого предложения из xls файла.
	 */
	function importFromXls() {
		$data = $this->ProcessInputData('importFromXls', true);

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}
		if( $ext != 'xls' ) {
			return $this->ReturnError('Необходим файл с расширением xls.');
		}

		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		if ($data){
			$response = $this->CommercialOffer_model->importFromXls($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			unlink($fileFullName);
			return true;
		} else {
			unlink($fileFullName);
			return false;
		}
	}

	/**
	 * Получение дполнительных данных
	 */
	function getCommercialOfferDrugContext() {
		$data = $this->ProcessInputData('getCommercialOfferDrugContext', true);
		if ($data){
			$response = $this->CommercialOffer_model->getCommercialOfferDrugContext($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     *  Загрузка данных для комбобокса
     */
    function loadOrgDidCombo() {
        $data = $this->ProcessInputData('loadOrgDidCombo', false);
        if ($data) {
            $filter = $data;
            $response = $this->CommercialOffer_model->loadOrgDidCombo($filter);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
    * Получение списка прайсов
    */
    function loadCommercialOfferList() {
    	$response = $this->CommercialOffer_model->loadCommercialOfferList();
    	//var_dump($response);die;
    	$this->ProcessModelList($response, true, true)->ReturnData();
            return true;
    }

    /**
    * Получение медикамента из прайса по коду СКП
    */
    function getCommercialOfferDrugDetail() {
    	$data = $this->ProcessInputData('getCommercialOfferDrugDetail',false);
    	if($data) {
    		$response = $this->CommercialOffer_model->getCommercialOfferDrugDetail($data);
    		$this->ProcessModelList($response,true,true)->ReturnData();
    		return true;
    	}
    	return false;
    }
}