<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'controllers/Person.php');

class Samara_Person extends Person {
	/**
	 * samara_Person
	 */ 
    function __construct()
	{
		parent::__construct();
		
		$this->inputRules['saveOrgAndPost'] = array(
						array(
								'default' => null,
								'field' => 'PostNew',
								'label' => 'Должность',
								'rules' => 'trim',
								'type' => 'string'
						),
						array(
								'default' => null,
								'field' => 'Org_id',
								'label' => 'Организация',
								'rules' => 'trim',
								'type' => 'string' 
						)
				);
                
		$this->inputRules['getPostColoredList'] = array(
              array(
                    'field' => 'query',
                    'label' => 'Запрос от комбобокса',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Post_id',
                    'label' => 'Идентификатор должности',
                    'rules' => 'trim',
                    'type' => 'id'
                )
		    );                
     
            
        $this->inputRules['checkPersonDoublesSamara'] = array(
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'default' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'trim|required',
					'type' => 'date'
				)
			);
            
        $this->inputRules['saveFromTFOMS'] = array(
            	array(
					'field' => 'ENP',
					'label' => '',
					'rules' => 'trim',
					'type' => 'string'
			    ),
            	array(
            		'default' => null,
            		'field' => 'Person_id',
            		'label' => '',
            		'rules' => 'trim',
            		'type' => 'id'
            	),            		
            		
            );
            
        $this->inputRules['getPersonSearchGrid'][] = array(
					'field' => 'SearchType',
					'label' => 'Тип поиска',
					'rules' => '',
					'type' => 'string'
				);
        
        
        $this->inputRules['savePersonEditWindow'][] = array(
        		'field' => 'AttachLpu_id',
        		'label' => 'ЛПУ прикрепления',
        		'rules' => 'trim',
        		'type' => 'id'
        );
            
    }
    
    /**
	 * getPersonEditWindow
	 */ 
    function getPersonEditWindow()
	{
		$data = $this->ProcessInputData('getPersonEditWindow', true, false, true);
		if ($data === false) { return true; }

        $this->load->model("Samara_Person_model", "samara_person");
		$info = $this->samara_person->getPersonEditWindow($data);
        $val = array();
        if ( $info != false && count($info) > 0 )
        {
			foreach ($info as $rows)
         	{
				array_walk($rows, 'ConvertFromWin1251ToUTF8');
				array_walk($rows, 'trim');
				$val[] = $rows;
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}
    
	/**
	 * bigintval
	 */ 
	function bigintval($value) {
		$value = trim($value);
		if (ctype_digit($value)) {
			return $value;
		}
		$value = preg_replace("/[^0-9](.*)$/", '', $value);
		if (ctype_digit($value)) {
			return $value;
		}
		return 0;
	}    
    
    /**
    *  TODO:Метод для сохранение данных формы редактирования человека.
    *  Входящие данные: $_POST с данными формы
    *  На выходе: JSON-строка
    *  Используется: форма редактирования человека
    */
    function savePersonEditWindow($toUtf = true)
    {
    
        $data = $this->ProcessInputData('saveOrgAndPost', true, false, true);

        if ($data !== false) { 
			$data['Server_id'] = $_SESSION['server_id'];


			$Org_id = intval($data['Org_id'], 10);
        
			// если пришла строка, то сохраняем её как новую организацию,
			// и используем сохранённый id как место работы
			if ($Org_id === 0){
				$this->load->model("Samara_Org_model", "orgmodel");

				$resp = $this->orgmodel->saveOrg(array(
                    'Server_id' => $data['Server_id'],
                    'Org_Name' => $data['Org_id'], 
                    'Org_Nick' => $data['Org_id'],
                    'OrgType_SysNick' => NULL,
                    'Org_id' => NULL,
                    'Org_Code' => NULL,
                    'Org_Description' => NULL,
                    'Org_rid' => NULL,
                    'Org_begDate' => NULL,
                    'Org_endDate' => NULL,
                    'Okved_id' => NULL,
                    'Okopf_id' => NULL,
                    'Okfs_id' => NULL,
                    'Org_INN' => NULL,
                    'Org_OKATO' => NULL,
                    'Org_KPP' => NULL,
                    'Org_OGRN' => NULL,
                    'Org_Phone' =>NULL,
                    'Org_Email' => NULL,
                    'OrgType_id' => NULL,
                    'UAddress_id' => NULL,
                    'PAddress_id' => NULL,
                    'KLCountry_id' => NULL,
                    'KLRGN_id' => NULL,
                    'KLSubRGN_id' => NULL,
                    'KLCity_id' => NULL,
                    'KLTown_id' => NULL,
                    'pmUser_id' => NULL,
                    'Org_OKPO' => NULL
                ));
				if(isset($resp[0]['Org_id'])){
					$data['Org_id'] = intval($resp[0]['Org_id'],10);
				} else {
					$data['Org_id'] = NULL;
            }
        }
			else {
				$data['Org_id'] = $Org_id;
        }
        
        
			$_POST['Org_id'] = $data['Org_id'];
			// если пришла строка, то сохраняем её как новую профессию,
			// и используем сохранённый id как место работы
        
			if (!empty($data['PostNew'])){
        	
				$this->load->model("Samara_Org_model", "orgmodel");

				$resp = $this->orgmodel->savePost(array(
                    'Server_id' =>  $data['Server_id'],
                    'Post_Name' => $data['PostNew'], 
                    'Post_id' => null,
                    'pmUser_id' => $data['pmUser_id']
                ));

				$_POST['PostNew'] = NULL;

				if(isset($resp[0]['Post_id'])){
					$_POST['Post_id'] = $this->bigintval($resp[0]['Post_id']);
            } 
        }
        }
 		
        parent::savePersonEditWindow();
    }

    
    /**
     * Получение списка должностей по запросу в комбобокс
     * Результат расцвечивается
     */
    function getPostColoredList() {

        $this->load->model("Samara_Person_model", "samara_person");

        $val  = array();

        $data = $this->ProcessInputData('getPostColoredList',true);
        if ($data === false) {return false;}

        $post_data = $this->samara_person->getPostColoredList($data);

        if ( isset($post_data) && is_array($post_data) && count($post_data) > 0 ) {
            foreach ($post_data as $row) {
                $row['Post_ColoredName'] = @preg_replace('/('.$data['query'].')/i','<span style="color:red">\\1</span>',$row['Post_Name']);
                array_walk($row, 'ConvertFromWin1251ToUTF8');
                $val[] = $row;
            }
        }

        $this->ReturnData($val);

        return true;
    }    

    
    /**
	 * searchInTFOMS
	 */ 
    function searchInTFOMS($queryData){ // ihere
         // сбор query
       	$query = "?";
     	foreach($queryData as $key => $value){
			if (empty($value)) continue;
	        $query .= $key . "=" 
				. urlencode(toUTF(''.$value))	
				. "&";
        }
        
		//$url = 'http://11.0.0.8/pers/' . $query;	
		
		$url = 'http://192.168.55.2:3000/pers/' . $query;	

        // @call_user_func_array - используется для того чтобы невыдавало ошибку в выходные данные
        $result = @call_user_func_array('file_get_contents', array($url, false));        
        //$result = @call_user_func_array('file_get_contents', array(, false));

     	if (!$result){
        	return false;
        }
        
        $this->load->helper("Xml"); 
        $dataFromXml = XmlToArray($result);
		
        if (count($dataFromXml['PERSONS']) == 0){
        	return false;
        }
        
        $persons = $dataFromXml['PERSONS']['PERSON'];
		
		if (!empty($persons['ENP'])){
			$temp = $persons;
			$persons = array();
			$persons[] = $temp;
		}
        
        return $persons;
    }
    
    /**
	 * getPersonSearchGrid
	 */ 
    function getPersonSearchGrid()
    { 
        $data = $this->ProcessInputData('getPersonSearchGrid', true);
        if ($data === false) { return true; }
        
        
        if ($data['SearchType'] == 'tfoms'){
            $this->getPersonSearchGridTfoms($data);
            return;
        }
        
		$info = $this->dbmodel->getPersonSearchGrid($data);
		if ( $info != false && count($info) > 0 )
		{
			// идешники людей для проверки был ли человек с сервер ид > 0,
			// чтобы не выводить персонов с нулевым сервером, если для него существует
			// запись с иднтификатором сервера лпу
			$person_ids = array();
			$val = array();
			$val['data'] = array();
			$val['totalCount'] = 0;
			$count = 0;
			
			foreach ($info as $rows)
         	{
				// проверяем, есть ли уже человек с этим идентификатором в контрольном массиве
				if ( isset($rows['__countOfAllRows']) )
				{
					$count = $rows['__countOfAllRows'];
				}
				else
				{
					array_walk($rows, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $rows;
				}				
			}
			$val['totalCount'] = $count;
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
    }
     
	/**
	 * getPersonSearchGrid
	 */ 
    function getPersonSearchGridTfoms($data)
    {
    	// сбор данных для запроса к тфомсу
        $queryData = array();
        if (!empty($data['ENP'])) {
            $queryData['ENP'] = $data['ENP'];
        }
        if (!empty($data['PersonSurName_SurName'])) {
            $queryData['SURNAME'] = strtoupper($data['PersonSurName_SurName']);
        }
        if (!empty($data['PersonFirName_FirName'])){
            $queryData['NAME'] = strtoupper($data['PersonFirName_FirName']);
        }
        if (!empty($data['PersonSecName_SecName'])) {
            $queryData['SECNAME'] = strtoupper($data['PersonSecName_SecName']);
        } 
        if (!empty($data['PersonBirthDay_BirthDay'])) {
            $dob = $data['PersonBirthDay_BirthDay'];
            $queryData['BIRTHDAY'] = substr($dob,8,2) . substr($dob,5,2) . substr($dob,0,4);
        } 
        
        // дата актуальности - сегодняшнее число
        $queryData['DATE_S'] = date('d.m.Y');
        
        // запрос
       	$persons = $this->searchInTFOMS($queryData);
       	
       	// неполучилось или не найдено
		if (!$persons){
			$this->ReturnData(array("data" => array(), "totalCount" => 0));
			return;
   		}
       
   		// сбор данных для таблицы
       	$retPersons = array();
       	foreach($persons as $person) {  
           	$retPersons[] = array(
                "ENP" => $person['ENP'],
                "PersonBirthDay_BirthDay" => substr($person['BIRTHDAY'],8,2) . '.' . substr($person['BIRTHDAY'],5,2) . '.'. substr($person['BIRTHDAY'],0,4) ,
                "PersonFirName_FirName" => $person['NAME'],
                "PersonSecName_SecName" => $person['SECNAME'],
                "PersonSurName_SurName" => $person['SURNAME'],
                "Lpu_Nick" => $person['LPUBASE'],
                "Sex_id" => $person['SEX'],
            	"Person_IsTFOMS" => true
        	);
       	}
        
       	$ret = array(
           	"data" => $retPersons, 
    		"totalCount" => count($retPersons) 
		);
        
		$this->ReturnData($ret); 
	}        
    
    /**
	 * saveFromTFOMS
	 */ 
    function saveFromTFOMS(){
        $data = $this->ProcessInputData('saveFromTFOMS',true);
        if ($data === false) {
        	return false;
        }
        
        $enp = $data['ENP'];
        $Person_id = $data['Person_id'];
        $data = $this->searchInTFOMS($data); 
		/**
		 * first
		 */ 
        function first($arr, $field, $value){
            foreach($arr as $item){
                if ($item[$field] == $value){
                    return $item;                
                }
            }
        }
		/**
		 * getOcatd
		 */ 
        function getOcatd($data){
			/**
			 * zeroify
			 */ 
        	function zeroify($num, $cnt ){
        		return str_pad((int) $num, $cnt,"0",STR_PAD_LEFT);
        	}
        
        	return zeroify ($data['TER'],2) . zeroify ($data['ORGN1'],3) . zeroify ($data['ORGN2'],3) . zeroify ($data['ORGN3'],3);
        }        
		/**
		 * arrConv
		 */ 
        function arrConv($person){
	        $cperson = array();
	        foreach($person as $key => $value){
	        	if (!empty($value)) {
	        		$cperson[$key] = iconv('utf-8', 'windows-1251', $value);
	        	} else{
	        		$cperson[$key] = null;
	        	}
	        }
	        return $cperson;
        }
        
        $rec = first($data, 'ENP', $enp);
        
        $rec = arrConv($rec);
        
        $person = array(
            'Person_id' => $Person_id,
        	//человек
            'Person_BirthDay'	=> $rec['BIRTHDAY'],
            'Person_SurName'	=> $rec['SURNAME'],
            'Person_FirName'	=> $rec['NAME'],
            'Person_SecName' 	=> $rec['SECNAME'],
        	'PersonSex_id' 		=> $rec['SEX'],
        	'Federal_Num'		=> $rec['ENP'],
        	'Lpu_id'			=> $rec['LPUBASE'],	
        		
        	// полис
        	'OmsSprTerr_id' 	=> 92, // hardcode Самарская область
            'PolisType_Code' 	=> $rec['VPOLIS'],
            'Polis_Ser'			=> $rec['SPOLIS'],
            'Polis_Num'			=> $rec['NPOLIS'],
            'Orgsmo_f002smocod' => $rec['INSURER'],
            'Polis_begDate' 	=> $rec['DN'],
            'Polis_endDate' 	=> $rec['DK'],
        		
        	// документ
            'DocumentType_Code' => $rec['DOCTYPE'],
            'Document_Ser' 		=> $rec['SDOC'],
            'Document_Num' 		=> $rec['NDOC'],
	       	
			// адресс
        	'Ocatd'				=> getOcatd($rec),
			'Streetdbf_id'		=> $rec['STREET'],
        	'Address_House'		=> $rec['HOUSE'] == 0 ? NULL : $rec['HOUSE'] . $rec['HOUSELITER'],
        	'Address_Corpus'	=> $rec['CORPUS'] == 0 ? NULL : $rec['CORPUS'],        
        	'Address_Flat'		=> $rec['FLAT'] == 0 ? NULL : $rec['FLAT'].$rec['FLATLITER'],

      		// необходимы для вставки в базу
            'Server_id' 		=> $_SESSION['server_id'],
            'session' 			=> $_SESSION,
            'pmUser_id' 		=> $_SESSION['pmuser_id']
        );

        $this->load->model("Samara_Person_model", "samara_person");
        
		$this->samara_person->savePerson($person);
        $this->samara_person->savePersonPolis($person);
        $this->samara_person->savePersonDocument($person);
        $this->samara_person->savePersonUAddress($person);
        //$this->samara_person->savePersonUAddress($person);
        
        $ret = array();
        
        $ret[] = array(
        	'Person_id' => $person['Person_id'],
        	'PersonEvn_id' => $person['PersonEvn_id'],
        	'Server_id'	=> $person['Server_id']
        );
        
        $this->ReturnData($ret);
    }
    
    /**
	 * checkPersonDoublesSamara
	 */ 
    function checkPersonDoublesSamara(){
    	$data = $this->ProcessInputData('checkPersonDoublesSamara',true);
    	if ($data === false) {return false;}
    	
        $this->load->model("Samara_Person_model", "samara_person");
    	$ret = $this->samara_person->checkPersonDoublesSamara($data);
    	$this->ReturnData($ret);
    }    
    
    // end samara TFOMS
}