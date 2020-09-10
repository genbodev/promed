<?php	defined('BASEPATH') or die ('No direct script access allowed');

class Kladr extends swController {
    /**
     * Kladr constructor.
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Kladr_model', 'dbmodel');
        $this->inputRules = array(
                'getJobsRunning'=>array(
                        array(
                                'field' => 'jobName',
                                'label' => 'Имя задания',
                                'rules' => 'required',
                                'type' => 'string'
                        )

                ),
                'startJob'=>array(
                        array(
                                'field' => 'jobName',
                                'label' => 'Имя задания',
                                'rules' => 'required',
                                'type' => 'string'
                        ),
                        array(
                                'field' => 'stepName',
                                'label' => 'Имя шага',
                                'rules' => 'required',
                                'type' => 'string'
                        )
                ),
                'getHistoryByInterval'=>array(
                        array(
                                'field' => 'jobName',
                                'label' => 'Имя задания',
                                'rules' => 'required',
                                'type' => 'string'
                        ),
                        array(
                                'field' => 'Start_DT',
                                'label' => 'Начало интервала',
                                'rules' => 'required',
                                'type' => 'string'
                        ),
                        array(
                                'field' => 'Stop_DT',
                                'label' => 'Конец интервала',
                                'rules' => '',
                                'type' => 'string'
                        )
                )
        );
    }

    /**
     * @return bool
     */
    function getJobsRunning() {
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['getJobsRunning']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->getJobsRunning($data);
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * @return bool
	 */
    function isJobRunning() {
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['getJobsRunning']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->isJobRunning($data);
        if (is_array($response)) {
            $result['data'] = $response[0]['count'];
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * @return bool
	 */
    function getHistoryByInterval(){
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['getHistoryByInterval']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->getHistoryByInterval($data);
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * Некая функция
	 */
    function getJobsList() {
        $response = $this->dbmodel->getJobsList();
        $result = array();
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * @return bool
	 */
    function getStepsList(){
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['getJobsRunning']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->getStepsList($data);
        $result = array();
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * @return bool
	 */
    function startJob(){
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['startJob']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->startJob($data);
        $result = array();
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }

	/**
	 * @return bool
	 */
    function stopJob(){
        $data = array();
        $result = array();
        $err = getInputParams($data, $this->inputRules['getJobsRunning']);
        if ($err != "") {
            echo json_return_errors($err);
            return false;
        }
        $response = $this->dbmodel->stopJob($data);
        $result = array();
        if (is_array($response)) {
            $result['data'] = toUTFR($response);
        } else ajaxErrorReturn();
        echo json_encode($result);
    }
}
