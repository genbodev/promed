<?php
if ( !defined('BASEPATH') ) {
	die('No direct script access allowed');
}

/**
* Dicom - контроллер общения с DICOM
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access			public
* @author			Демин Дмитрий
* @version			20.02.2013
*/

class Dicom extends swController {
	
	var $inputRules = array(
		'remoteStudy' => array(
			array(
				'field' => 'begDate',
				'label' => 'Дата с',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата по',
				'rules' => 'trim',
				'type' => 'date'
			), 
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			)
		),
		'addStudyToEvnUslugaPar' => array(
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'study_uid',
				'label' => 'Study UID',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'getAssociatedResearches'=>array(
			array('field' => 'EvnUslugaPar_id','label' => 'Идентификатор услуги','rules' => 'required','type' => 'id'),			
		),
		'getStudyView'=>array(
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'LpuEquipmentPacs_id','label' => 'Ид. устройства PACS','rules' => 'required','type' => 'string')
		),
		'getSeriesView'=>array(
			array('field' => 'seriesNum','label' => 'Номер серии','rules' => 'required','type' => 'string'),
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'LpuEquipmentPacs_id','label' => 'Ид. устройства PACS','rules' => 'required','type' => 'string'),
			array('field' => 'EMK','label' => 'Флаг отображения в ЕМК','rules' => '','type' => 'int'),
		),
		'getInstancesForDicomViewer'=>array(
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'LpuEquipmentPacs_id','label' => 'Ид. устройства PACS','rules' => 'required','type' => 'string'),
			array('field' => 'seriesUID','label' => 'Ид серии','rules' => 'required','type' => 'string')					
		),
		'getSeriesForDicomViewer'=>array(
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'MedService_id','label' => 'Ид. службы','rules' => '','type' => 'string'),
			array('field' => 'LpuEquipmentPacs_id','label' => 'Ид. устройства PACS','rules' => 'required','type' => 'string')						
		),
		'saveDicomSvgAnnotation'=>array(
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'seriesUID','label' => 'seriesUID','rules' => 'required','type' => 'string'),
			array('field' => 'sopIUID','label' => 'sopIUID','rules' => 'required','type' => 'string'),
			array('field' => 'attachFrames','label' => 'номера инстансов','rules' => '','type' => 'string'),
			array('field' => 'canvasXmlData','label' => 'xml содержимое аннотации','rules' => 'required','type' => 'string'),
			array('field' => 'DicomStudyNote_id','label' => 'ид аннотации','rules' => '','type' => 'int')			
		),
		'loadDicomSvgAnnotation'=>array(
			array('field' => 'study_uid','label' => 'Study UID','rules' => 'required','type' => 'string'),
			array('field' => 'seriesUID','label' => 'seriesUID','rules' => 'required','type' => 'string'),
			array('field' => 'sopIUID','label' => 'sopIUID','rules' => '','type' => 'string')
		),
		'deleteDicomSvgAnnotation'=>array(
			array('field' => 'DicomStudyNote_id','label' => 'ид аннотации','rules' => '','type' => 'int')
		)

	);
	
	/**
	 * Конструктор
	 * 
	 * @return JSON
	 */
	public function __construct(){
		parent::__construct();
		
		$this->load->database();
		$this->load->model('Dicom_model', 'model');
	}
	
	/**
	 * Возвращает список исследований за день для указанного ЛПУ
	 * 
	 * @return JSON
	 */
	public function remoteStudy(){
		$data = $this->ProcessInputData('remoteStudy', true);
		if ( $data === false ) {
			return false;
		}
		$result = $this->model->remoteStudy( $data );
		$this->ProcessModelList( $result, true, true )->ReturnData();
	}
	
	/**
	 *
	 * 
	 * @return JSON
	 */
	public function getSeriesView() {
		$data = $this->ProcessInputData('getSeriesView', true);
		if ( $data === false ) {
			return false;
		}		
		$response = $this->model->getSeriesView( $data );
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	 *
	 * 
	 * @return JSON
	 */
	public function getStudyView() {
		$data = $this->ProcessInputData('getStudyView', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->getStudyView( $data );
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}

	/**
	 * Возвращает список исследований прикрепленных к определенной услуге
	 * 
	 * @return JSON
	 */
	public function getAssociatedResearches(){
		$data = $this->ProcessInputData('getAssociatedResearches', true);
		if ( $data === false ) {
			return false;
		}
		$data = $this->model->getAssociatedResearches( $data );
		$this->ProcessModelList( $data, true, true )->ReturnData();
	}
	
	/**
	 * Привязка Study UID к параклинической услуге
	 * 
	 * @return boolean
	 */
	public function addStudyToEvnUslugaPar(){
		$data = $this->ProcessInputData('addStudyToEvnUslugaPar', true);
		if ( $data === false ) {
			return false;
		}
		$data = $this->model->addStudyToEvnUslugaPar( $data );
		$this->ProcessModelSave( $data )->ReturnData();
	}
	
	/**
	* Получение серий исследований для просмотровщика dicom
	*
	*/
		
	public function getSeriesForDicomViewer() {
		$data = $this->ProcessInputData('getSeriesForDicomViewer', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->getSeriesForDicomViewer( $data );
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	* Получение изображений серии исследований для просмотровщика dicom
	*
	*/
		
	public function getInstancesForDicomViewer() {
		$data = $this->ProcessInputData('getInstancesForDicomViewer', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->getInstancesForDicomViewer( $data );
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	* Сохранение svg аннотации для dicom изображения
	*
	*/
	
	public function saveDicomSvgAnnotation() {
		$data = $this->ProcessInputData('saveDicomSvgAnnotation', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->saveDicomSvgAnnotation( $data );
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	* Загрузка svg аннотации для dicom изображений
	*
	*/
	
	public function loadDicomSvgAnnotation() {
		$data = $this->ProcessInputData('loadDicomSvgAnnotation', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->loadDicomSvgAnnotation( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		return true;
	}
	
	
	/**
	* Загрузка svg аннотации для dicom изображений
	*
	*/
	
	public function deleteDicomSvgAnnotation() {
		$data = $this->ProcessInputData('deleteDicomSvgAnnotation', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->deleteDicomSvgAnnotation( $data );

		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	* Тестовое изображений изображения для просмотровщика dicom
	*
	*/
		
	public function getImage() {
		$img = ImageCreateFromJPEG($_GET['imageurl']);
		header('Content-type: image/jpeg');
		imagejpeg($img, NULL, 100);
		
		/*$data = $this->ProcessInputData('getInstancesForDicomViewer', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->model->getInstancesForDicomViewer( $data );
		$this->ProcessModelSave( $response )->ReturnData();*/
		return true;
	}
}
