<?php


class RemoteConsult extends swController
{
	/**
	 * Это Doc-блок
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('RemoteConsult_model', 'dbmodel');

		$this->inputRules = [
			'uploadRemoteProtocol'=>[
				array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
				array('field' => 'Lpu_id','label' => 'МО','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'Отделение МО','rules' => '','type' => 'id')
			],
			'deleteRemoteProtocol'=>[
				array('field' => 'RemoteConsultProtocol_id','label' => 'Идентификатор направления','rules' => '','type' => 'id')
			],
			'downloadRemoteConsult'=>[
				array('field' => 'RemoteConsultProtocol_id','label' => 'Идентификатор направления','rules' => '','type' => 'id')
			]
		];
	}
	
	/**
	 * Метод загрузки протокола удаленной консультации
	 * формирует файл по пути вида:
	 * uploads/orgs/protocols/[lpu_id]/LpuSection/[LpuSection_id_File_name].(doc|pdf)
	 */
	public function uploadRemoteProtocol() {
		$data = $this->ProcessInputData('uploadRemoteProtocol', true);
		$response = $this->dbmodel->uploadRemoteProtocol($data, $_FILES);
		if (is_array($response)) {
			$this->ReturnData($response);
		} else {
			DieWithError('Не удалось загрузить файл!');
			return false;
		}
	}
	
	function deleteRemoteProtocol(){
		$data = $this->ProcessInputData('deleteRemoteProtocol', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deleteRemoteProtocol($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	function downloadRemoteConsult(){
		$data = $this->ProcessInputData('downloadRemoteConsult', true);
		if ($data === false) { return false; }
		
		$filePath = $this->dbmodel->getRemoteProtocolFilePath($data);
		
		if ($filePath != '') {
			$this->dbmodel->downloadRemoteProtocol($filePath);
		}
		
		DieWithError('Не удалось найти файл!');
		return false;
	}
}