<?php	defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'controllers/PersonCard.php');

class Buryatiya_PersonCard extends PersonCard {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Печать заявления на прикрепление (новый метод)
	 * На выходе: форма для печати заявления на прикрепление
	 * Используется: форма редактирования карты пациента
	 */
	function printPersonCardAttach() {
		$data = $this->ProcessInputData('printPersonCardAttach', true);
		if ($data === false) { return false; }

		//print_r($data); die();
		$this->load->library('parser');
		$this->load->model('Common_model', 'dbmodel');
		$template = "PersonCardAttach_template_buryatiya";

		$response = $this->dbmodel->loadPersonDataForPrintPersonCard($data);
		$parseData = array();
		$parseData['Perm_Head'] = "";

		$parseData['Person_FIO'] = $response[0]['Person_Surname']. " " .$response[0]['Person_Firname']. " " .$response[0]['Person_Secname'];
		$parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Polis_Ser'] = $response[0]['Polis_Ser'];
		$parseData['Polis_Num'] = $response[0]['Polis_Num'];
		$parseData['OrgSmo_Name'] = $response[0]['OrgSmo_Name'];
		$parseData['Polis_begDate'] = $response[0]['Polis_begDate'];
		$parseData['date'] = date('d.m.Y');
		$parseData['statement_template_title'] = "Заявление на прикрепление: " . $parseData['Person_FIO'];
		$parseData['Person_RAddress'] = $response[0]['Person_RAddress'];
		$parseData['Person_PAddress'] = $response[0]['Person_PAddress'];
		$parseData['Lpu_Nick'] = $response[0]['Lpu_Nick'];
		$parseData['Person_BirthYear'] = mb_substr($response[0]['Person_Birthday'], 6, strlen($response[0]['Person_Birthday']));

		//Кусок для печати заявления о выборе МО для Бурятии https://redmine.swan.perm.ru/issues/47331
		$parseData['Sex_Code'] = $response[0]['Sex_Code'];
		$parseData['Person_Birthday'] = $response[0]['Person_Birthday'];
		$parseData['Person_Surname'] = $response[0]['Person_Surname'];
		$parseData['Person_Firname'] = $response[0]['Person_Firname'];
		$parseData['Person_Secname'] = $response[0]['Person_Secname'];
		$parseData['Person_BAddress'] = $response[0]['Person_BAddress'];
		$parseData['Nationality'] = $response[0]['Nationality'];
		$parseData['DocumentType_Name'] = $response[0]['DocumentType_Name'];
		$parseData['Document_Ser'] = $response[0]['Document_Ser'];
		$parseData['Document_Num'] = $response[0]['Document_Num'];
		$parseData['OrgDep_Name'] = $response[0]['OrgDep_Name'];
		$parseData['Document_begDate'] = $response[0]['Document_begDate'];
		$parseData['URgn_Name'] = $response[0]['URgn_Name'];
		$parseData['USubRgn_Name'] = $response[0]['USubRgn_Name'];
		$parseData['UCity_Name'] = $response[0]['UCity_Name'];
		$parseData['UTown_Name'] = $response[0]['UTown_Name'];
		$parseData['UStreet_Name'] = $response[0]['UStreet_Name'];
		$parseData['UAddress_House'] = $response[0]['UAddress_House'];
		$parseData['UAddress_Corpus'] = $response[0]['UAddress_Corpus'];
		$parseData['UAddress_Flat'] = $response[0]['UAddress_Flat'];
		$parseData['PSubRgn_Name'] = $response[0]['PSubRgn_Name'];
		$parseData['PCity_Name'] = $response[0]['PCity_Name'];
		$parseData['PTown_Name'] = $response[0]['PTown_Name'];
		$parseData['PStreet_Name'] = $response[0]['PStreet_Name'];
		$parseData['PAddress_House'] = $response[0]['PAddress_House'];
		$parseData['PAddress_Corpus'] = $response[0]['PAddress_Corpus'];
		$parseData['PAddress_Flat'] = $response[0]['PAddress_Flat'];

		if($data['PersonCardAttach_IsHimself'] == 2)
		{
			$parseData['DPerson_Surname'] = "";
			$parseData['DPerson_Firname'] = "";
			$parseData['DPerson_Secname'] = "";
			$parseData['DeputyKind_Name'] = "";
			$parseData['DDocumentType_Name'] = "";
			$parseData['DDocument_Ser'] = "";
			$parseData['DDocument_Num'] = "";
			$parseData['DOrgDep_Name'] = "";
			$parseData['DDocument_begDate'] = "";
		}
		else{
			$parseData['DPerson_Surname'] = $response[0]['DPerson_Surname'];
			$parseData['DPerson_Firname'] = $response[0]['DPerson_Firname'];
			$parseData['DPerson_Secname'] = $response[0]['DPerson_Secname'];
			$parseData['DeputyKind_Name'] = $response[0]['DeputyKind_Name'];
			$parseData['DDocumentType_Name'] = $response[0]['DocumentType_Name'];
			$parseData['DDocument_Ser'] = $response[0]['DDocument_Ser'];
			$parseData['DDocument_Num'] = $response[0]['DDocument_Num'];
			$parseData['DOrgDep_Name'] = $response[0]['DOrgDep_Name'];
			$parseData['DDocument_begDate'] = $response[0]['DDocument_begDate'];
		}
		$parseData['Phone'] = $response[0]['Phone'];
		//Конец куска для печати для Бурятиии

		//print_r($response); die();
		$this->load->model('User_model', 'umodel');
		// Получаем данные по ЛПУ
		$response = $this->umodel->getCurrentLpuName($data);
		$parseData['Lpu_Name'] = $response[0]['Lpu_Name'];
		$parseData['Lpu_Address'] = $response[0]['Lpu_Address'];

		// если выбрана Форма заявления о выборе МО представителем пациента, то находим представителя
		if( $data['PersonCardAttach_IsHimself'] == 1 ) {
			$this->load->model('Mse_model', 'Mse_model');
			$deputyData = $this->Mse_model->getDeputyKind(array('Person_id' => $data['Person_id']));
			if(is_array($deputyData) && count($deputyData) > 0 ) {
				$parseData['Deputy_Fio'] = $deputyData[0]['Person_Fio'];
			} else {
				DieWithError("У пациента нет законного представителя!");
				return;
			}
			//print_r($deputyData); die();
		}

		$res = $this->parser->parse($template, $parseData);
	}
}
?>