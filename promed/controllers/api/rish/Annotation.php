<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Annotation extends SwREST_Controller {
	protected  $inputRules = array(
		'createAnnotation' => array(
			array('field' => 'AnnotationType_id', 'label' => 'Идентификатор типа примечания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Annotation_Comment','label' => 'Текст примечания','rules' => 'required','type' => 'string'),
			array('field' => 'Annotation_begDate','label' => 'Дата начала действия примечания','rules' => 'required','type' => 'date'),
			array('field' => 'Annotation_begTime','label' => 'Время начала действия примечания','rules' => 'required','type' => 'time'),
			array('field' => 'Annotation_endDate','label' => 'Дата окончания действия примечания','rules' => 'required','type' => 'date'),
			array('field' => 'Annotation_endTime','label' => 'Время окончания действия примечания','rules' => 'required','type' => 'time'),
			array('field' => 'AnnotationVison_id','label' => 'Идентификатор видимости примечания','rules' => 'required','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Место работы врача','rules' => 'required','type' => 'id')
		),
		'updateAnnotation' => array(
			array('field' => 'Annotation_id', 'label' => 'Идентификатор примечания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AnnotationType_id', 'label' => 'Идентификатор типа примечания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Annotation_Comment','label' => 'Текст примечания','rules' => '','type' => 'string'),
			array('field' => 'Annotation_begDate','label' => 'Дата начала действия примечания','rules' => '','type' => 'date'),
			array('field' => 'Annotation_begTime','label' => 'Время начала действия примечания','rules' => '','type' => 'time'),
			array('field' => 'Annotation_endDate','label' => 'Дата окончания действия примечания','rules' => '','type' => 'date'),
			array('field' => 'Annotation_endTime','label' => 'Время окончания действия примечания','rules' => '','type' => 'time'),
			array('field' => 'AnnotationVison_id','label' => 'Идентификатор видимости примечания','rules' => '','type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Annotation_model', 'dbmodel');
	}

	/**
	 * Добавление примечания к расписанию
	 */
	function Annotation_post() {
		$data = $this->ProcessInputData('createAnnotation', null, true);

		$data['MedService_id'] = null;
		$data['Resource_id'] = null;
		$data['ignore_doubles'] = true;
		$resp = $this->dbmodel->save($data);
		if (!empty($resp[0]['Annotation_id'])) {
			$this->response(array(
				'error_code' => 0,
				'data' => array(
					'Annotation_id' => $resp[0]['Annotation_id']
				)
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Изменение примечания к расписанию
	 */
	function Annotation_put() {
		$data = $this->ProcessInputData('updateAnnotation', null, true);

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createAnnotation');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->load($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$data['MedService_id'] = null;
		$data['Resource_id'] = null;
		$data['ignore_doubles'] = true;
		$resp = $this->dbmodel->save($data);
		if (!empty($resp[0]['Annotation_id'])) {
			$this->response(array(
				'error_code' => 0
			));
		} else {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}