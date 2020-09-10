<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Vaccine extends swController {

    /**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */
    /*
    public function getVaccineGridDetail() {
                $this->load->database();
		$this->load->model("Vaccine_model", "dbmodel");
		$data = $_REQUEST;
		$info = $this->dbmodel->getVaccineGridDetail($data);
		if ( $info != false && count($info) > 0 ) {
			foreach ($info as $rows) {
				$val[] = array(
					'Vaccine_id'=>toUTF(trim($rows['Vaccine_id'])),
					'Name'=>toUTF(trim($rows['Name'])),
					'SignComb'=>toUTF(trim($rows['SignComb'])),
					'CodeInf'=>toUTF(trim($rows['CodeInf'])),
					'NameVac'=>toUTF(trim($rows['NameVac'])),
					'GRID_NAME_VAC'=>toUTF(trim($rows['GRID_NAME_VAC'])),
					'NAME_TYPE_VAC'=>toUTF(trim($rows['NAME_TYPE_VAC'])),
					'AgeRange'=>toUTF(trim($rows['AgeRange'])),
					'AgeRange2Sim'=>toUTF(trim($rows['AgeRange2Sim'])),
					'SignWayPlace'=>toUTF(trim($rows['SignWayPlace'])),
                                        'WayPlace_id'=>toUTF(trim($rows['WayPlace_id'])),
                                        'WAY_PLACE'=>toUTF(trim($rows['WAY_PLACE'])),
                                        'SignDoza'=>toUTF(trim($rows['SignDoza'])),
                                        'Doza'=>toUTF(trim($rows['Doza'])),
                                        'VACCINE_DOZA'=>toUTF(trim($rows['VACCINE_DOZA'])),
				);
			}
			echo json_encode($val);
		}
		else
			echo json_encode(array());
	}  //end getVaccineGridDetail()
	*/
	
 	/**
	 * Description
	 */
	public function GetVaccineTypePeriod() {
		//		  echo 'This My Model!';



		$data = array();
		$val  = array();
		$this->load->database();
		//                echo 'jiowefoiefjioi';
		//                return true;
		// Получаем сессионные переменные
		//		$data = array_merge($data, getSessionParams());

		//$err = getInputParams($data, $this->inputRules['loadbacname']);
		$this->load->model('Vaccine_model', 'dbmodel');

		$response = $this->dbmodel->GetVaccineTypePeriod($data);
		//   $response = $this->dbmodel->loadEvnAggEditForm($data);

		//		if ( is_array($response) && count($response) > 0 ) {
		//			$val = $response;
		//			array_walk($val[0], 'ConvertFromWin1251ToUTF8');
		//		}
		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:'.json_encode($val).'}';

		return true;
	}

	/**
	 * Description
	 */
	public function getVaccineWay(){
		$data = array();
		$val  = array();
		$this->load->database();
		$this->load->model('Vaccine_model', 'dbmodel');
		$response = $this->dbmodel->GetVaccineWay($data);

		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:'.json_encode($val).'}';

		return true;
	}

	/**
	 * Description
	 */
	public function GetVaccinePlace(){

		//                $data = array();
		$data = $_REQUEST;
		$val  = array();
		$this->load->database();
		$this->load->model('Vaccine_model', 'dbmodel');
		$response = $this->dbmodel->GetVaccinePlace($data);

		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:'.json_encode($val).'}';

		return true;
	}


	/**
	 * Description
	 */
	public function GetSprInoculation(){
		$data = array();
		$val  = array();
		$this->load->database();
		$this->load->model('Vaccine_model', 'dbmodel');
		$response = $this->dbmodel->GetSprInoculation($data);

		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:'.json_encode($val).'}';

		return true;
	}


	/**
	 * Description
	 */
	public function getVaccine4Combo2() {
		$data = array();
		$val  = array();
		$this->load->database();
		$this->load->model('Vaccine_model', 'dbmodel');
		$response = $this->dbmodel->getVaccine4Combo2($data);

		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:'.json_encode($val).'}';

		return true;
	}
}
?>