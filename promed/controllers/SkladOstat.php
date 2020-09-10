<?php
/**
 * 
 */
class SkladOstat extends swController {
	/**
	 * 
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'importOst' => array(
				array('field' => 'typeAction','label' => 'Тип импорта','rules' => '','type' => 'string'),
			),
			'loadOstatGrid' => array(
				array('field' => 'SkladOstat_Sklad','label' => 'Название Склада','rules' => '','type' => 'string'),
				array('field' => 'SkladOstat_Gdmd','label' => 'Наименование медикамента','rules' => '','type' => 'string')
			)
		);
		$this->load->database();
		$this->load->model('SkladOstat_model', 'SkladOstat_model');
	}
	/**
	 *
	 * @return type 
	 */
	function loadOstatGrid(){
		$data = $this->ProcessInputData('loadOstatGrid', true);
        if ($data)
        {
			$response = $this->SkladOstat_model->loadOstatGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		
		}else{
			return false;
		}
	}
	/**
	 *
	 * @return type 
	 */
	function importOst(){
		$fl = null;
		$data = $this->ProcessInputData('importOst', true);
        if ($data)
        {
			$tmp_folder = tempnam(sys_get_temp_dir(), 'imp'); //создаю временный файл
			$file="";
			if ($tmp_folder) {
				unlink($tmp_folder); //удаляю его и использую как имя для временного каталога
				if (mkdir($tmp_folder)) {
					//перемещаю все файлы во временный каталог
					if (!empty($_FILES["sourcefiles"])) {
						$fl = substr($_FILES["sourcefiles"]["name"],0,3);
						if ($_FILES["sourcefiles"]["error"]==0) {
							move_uploaded_file($_FILES["sourcefiles"]["tmp_name"], $tmp_folder . '/' . $_FILES["sourcefiles"]["name"]);
							$file = $tmp_folder . '/' . $_FILES["sourcefiles"]["name"];
						} else {
							@unlink($tmp_folder);
							throw new Exception('Не удалось загрузить все файлы');
						}
						if(($fl=='INV'&&$data['typeAction']=='LpuSectionOTD')||($fl=='RST'&&$data['typeAction']=='SkladOst')){
							
						}else {
							@unlink($tmp_folder);
							throw new Exception('Выбранный файл не соответствует данной загрузке');
						}
					}
				}
			}
			$data['file']=$file;
			$tmp = getSessionParams();
			$res = $this->SkladOstat_model->run($data);
			$this->ProcessModelSave($res)->ReturnData();
		}else{
            return false;
        }

	}
}
?>
