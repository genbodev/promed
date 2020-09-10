<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ApplicationCVI_model - модель для работы с формой Анкета КВИ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      25.03.2020
 */

require_once('Scenario_model.php');
class ApplicationCVI_model extends Scenario_model {
	var $params = [
		'PlaceArrival_id' => [
			'CVIQuestionType_Code' => 1,
			'AnswerType_Code' => 3
		],
		'KLCountry_id' => [
			'CVIQuestionType_Code' => 2,
			'AnswerType_Code' => 3
		],
		'OMSSprTerr_id' => [
			'CVIQuestionType_Code' => 3,
			'AnswerType_Code' => 3
		],
		'ApplicationCVI_arrivalDate' => [
			'CVIQuestionType_Code' => 4,
			'AnswerType_Code' => 11
		],
		'ApplicationCVI_flightNumber' => [
			'CVIQuestionType_Code' => 5,
			'AnswerType_Code' => 2
		],
		'ApplicationCVI_isContact' => [
			'CVIQuestionType_Code' => 6,
			'AnswerType_Code' => 1
		],
		'ApplicationCVI_isHighTemperature' => [
			'CVIQuestionType_Code' => 7,
			'AnswerType_Code' => 1
		],
		'Cough_id' => [
			'CVIQuestionType_Code' => 8,
			'AnswerType_Code' => 3
		],
		'Dyspnea_id' => [
			'CVIQuestionType_Code' => 9,
			'AnswerType_Code' => 3
		],
		'ApplicationCVI_Other' => [
			'CVIQuestionType_Code' => 10,
			'AnswerType_Code' => 2
		]
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList([ self::SCENARIO_LOAD_EDIT_FORM ]);
	}

	/**
	 * Сохранение
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array|void
	 * @throws Exception
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		return $isAllowTransaction ? $this->doTransaction('_save', $data) : $this->_save($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function doLoadData($data = array())
	{
		$params = [];
		$where = '';

		if( !empty($data['CmpCallCard_id']) ) {
			$where .= " and CQ.CmpCallCard_id = :CmpCallCard_id";
			$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		if( !empty($data['Person_id']) ) {
			$where .= " and CQ.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		if( !empty($data['CVIQuestion_id']) ) {
			$where .= " and CQ.CVIQuestion_id = :CVIQuestion_id";
			$params['CVIQuestion_id'] = $data['CVIQuestion_id'];
		}

		if( !empty($data['HomeVisit_id']) ) {
			$where .= " and CQ.HomeVisit_id = :HomeVisit_id";
			$params['HomeVisit_id'] = $data['HomeVisit_id'];
		}

		// $where = implode(" and ",$where);

		$query = "
			select top 10
				CQA.CVIQuestionAnswer_FreeForm,
				convert(varchar(10), cast(CQA.CVIQuestionAnswer_setDT as datetime), 104) as CVIQuestionAnswer_setDT,
				CQA.CVIQuestionAnswer_refID,
				CQT.CVIQuestionType_id
			from v_CVIQuestion CQ with(nolock)
			left join v_CVIQuestionAnswer CQA with(nolock) on CQA.CVIQuestion_id = CQ.CVIQuestion_id
			left join v_CVIQuestionType CQT with(nolock) on CQT.CVIQuestionType_id = CQA.CVIQuestionType_id
			where 1=1 $where
			order by CQA.CVIQuestion_id asc, CQA.CVIQuestionType_id;
		";

		$questionCodes = [];

		foreach ($this->params as $key => $obj) {
			$questionCodes[$obj['CVIQuestionType_Code']] = $key;
		}

		$questionAsnwers = $this->queryResult($query, $params);

		if(!$questionAsnwers || !count($questionAsnwers)) return [];

		$response = [];

		foreach ($questionAsnwers as $answer) {
			if( empty($answer['CVIQuestionType_id']) ) continue;
			$value = null;
			switch(true) {
				case isset($answer['CVIQuestionAnswer_refID']):
					$value = $answer['CVIQuestionAnswer_refID'];
					break;
				case isset($answer['CVIQuestionAnswer_setDT']):
					$value = $answer['CVIQuestionAnswer_setDT'];
					break;
				case isset($answer['CVIQuestionAnswer_FreeForm']):
					$value = $answer['CVIQuestionAnswer_FreeForm'];
					break;
			}
			$response[$questionCodes[$answer['CVIQuestionType_id']]] = $value;
		}

		return [$response];
	}

	/**
	 * Сохранение
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	protected function _save( $data = array() ) {

		$this->load->model('CVIQuestion_model', 'CVIQuestion_model');
		$session = !empty($data['session']) ? $data['session'] : $this->getSessionParams();
		$questionParams = [
			'session' => $session,
			'CVIQuestion_id' => !empty($data['CVIQuestion_id']) ? $data['CVIQuestion_id'] : null,
			'Person_id' => !empty($data['Person_id']) ? $data['Person_id'] : null,
			'CmpCallCard_id' => !empty($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : null,
			'HomeVisit_id' => !empty($data['HomeVisit_id']) ? $data['HomeVisit_id'] : null
		];
		$resp = $this->CVIQuestion_model->doSave($questionParams);
		if ( !$this->isSuccessful( $resp ) ) {
			return $this->getErrorMessage($resp['Error_Msg'], $resp['Error_Code']);
		}

		$this->load->model('CVIQuestionAnswer_model', 'CVIQuestionAnswer_model');

		foreach ($this->params as $key => $obj) {
			if(!isset($data[$key])) continue;
			$params = [
				'session' => $session,
				'CVIQuestionAnswer_id' => null,
				'CVIQuestion_id' => $resp['CVIQuestion_id'],
				'CVIQuestionType_id' => $obj['CVIQuestionType_Code']
			];

			$dataField = 'CVIQuestionAnswer_FreeForm';
			switch ($obj['AnswerType_Code']) {
				case 3:
				case 1:
					$dataField = 'CVIQuestionAnswer_refId';
					break;
				case 11:
					$dataField = 'CVIQuestionAnswer_setDT';
					break;
			}
			$params[$dataField] = $data[$key];

			$result = $this->CVIQuestionAnswer_model->doSave($params);
			if ( !$this->isSuccessful($result) ) {
				return $this->getErrorMessage($result['Error_Msg'], $result['Error_Code']);
			}
		}

		return [
		    'CVIQuestion_id' => $resp['CVIQuestion_id'],
			'success' => true,
			'Error_Msg' => null,
			'Error_Code' => null
		];
	}
}