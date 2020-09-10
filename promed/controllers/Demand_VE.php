<?php	defined('BASEPATH') or die ('No direct script access allowed');

include_once($_SERVER['DOCUMENT_ROOT']."/promed/controllers/RegistryUfaVE.php");



class Demand_VE extends swController {
	
	/**
	*  Task#18011
	*  Используется при построении грида со списком Уфимских СМО
	*  модификация оригинального Demand.php для групповой постановке реестров на очередь формирования Task#18011
	*  Окно: swRegistryEditWindowVE
	*  /?c=Demand_VE&m=getOrgSmoUfaList
	*/
	function __construct() {
		parent::__construct();
	}
	/**
	 * Получение списка СМО региона
	 */
	function getOrgSmoUfaList() {

		$this->load->database();
		$this->load->model('Demand_model_VE', 'dbmodel');
		$val  = array();
		
        
        
		$state_data = $this->dbmodel->getOrgSmoUfaList();
		
		if ( isset($state_data) && is_array($state_data) && count($state_data) > 0 ) {
			foreach ($state_data as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		
		$this->inputRules = array(array('field'=>'Org_id'));
		$this->ReturnData($val);

		return true;
	}
	/**
	 *Обработка данных после работы с формой
	 */
	function PrepareData(){
		$RegistryUfaVE = new RegistryUfaVE;
		
		if(!isset($_POST['data'])){
			echo 'Не получена ожидаемая JSON строка c с данными отделениям и списком СМО для предварительной подготовки данных';
			return false; 
		}   
		
		$dec_data = json_decode($_POST['data'], 1);
		$Registry_IsNew = null;
		if (!empty($_POST['Registry_IsNew'])) {
			$Registry_IsNew = (int)$_POST['Registry_IsNew'];
		}

		unset($_POST['data']);
		unset($_POST['Registry_IsNew']);
		
		$strip_symbols = array('"'=>"", "'"=>"", "`"=>"");

        //echo '<pre>' . print_r($dec_data,1) . '</pre>'; exit;

		// предварительные
		if($dec_data['Smo'] == 'null') {
			foreach($dec_data['LpuUnitSet'] as $lus){
				$_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
				$_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
				$_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
				$_POST['Registry_IsNew'][] = $Registry_IsNew;
				$_POST['Registry_id'][] = NULL;
				$_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
				$_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
				$_POST['Registry_IsActive'][] = 2;
				$_POST['Registry_begDate'][] = $lus['datestart'];
				$_POST['Registry_endDate'][] = $lus['dateend'];
				$_POST['OrgSmo_id'][] = NULL;//$smo['Smo_id'];
				$_POST['LpuUnitSet_id'][] = $lus['id'];
				$_POST['Registry_Num'][] = (string)$lus['number'];
				//Не используется
				$_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$lus['datestart'].'" Registry_endDate="'.$lus['dateend'].'" OrgSmo_id="NULL" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"></Registry>'.PHP_EOL;                       
			}
		} else 

		//Большие реестры (xml формируется в RegistryUfa_modelVE.php m=saveRegistryQueue_array))
		if(empty($dec_data['Smo'])){
			//ФОрмирование массивов для /?c=RegistryUfa&m=saveRegistry
			foreach($dec_data['LpuUnitSet'] as $lus){
				
				$_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
				$_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
				$_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
				$_POST['Registry_IsNew'][] = $Registry_IsNew;
				$_POST['Registry_id'][] = 0;
				$_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
				$_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
				$_POST['Registry_IsActive'][]= 2;
				$_POST['Registry_begDate'][] = $lus['datestart'];
				$_POST['Registry_endDate'][] = $lus['dateend'];              
				$_POST['OrgSmo_id'][] = "";
				$_POST['LpuUnitSet_id'][] = $lus['id'];
				$_POST['Registry_Num'][] = (string)$lus['number']; 
				//Не используется 
				$_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2"  Registry_begDate="'.$lus['datestart'].'"  Registry_endDate="'.$lus['dateend'].'" OrgSmo_id="" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"></Registry>'.PHP_EOL; 
            }   
		}
		//Реестры по указанным СМО и выбранным подразделениям
		else{
			foreach($dec_data['Smo'] as $smo){
				//Для инотеров - даты инотеров
				if($smo['Smo_id'] != 8){
					
					foreach($dec_data['LpuUnitSet'] as $lus){
						$_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
						$_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
						$_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
						$_POST['Registry_IsNew'][] = $Registry_IsNew;
						$_POST['Registry_id'][] = NULL;
						$_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
						$_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
						$_POST['Registry_IsActive'][] = 2;
						$_POST['Registry_begDate'][] = $lus['datestart'];
						$_POST['Registry_endDate'][] = $lus['dateend'];
						$_POST['OrgSmo_id'][] = $smo['Smo_id'];
						$_POST['LpuUnitSet_id'][] = $lus['id'];
						$_POST['Registry_Num'][] = (string)$lus['number'];
						//Не используется
						$_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$lus['datestart'].'" Registry_endDate="'.$lus['dateend'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"></Registry>'.PHP_EOL;                         
					}
				}
				//Для инотеров - даты инотеров
				else{
                    foreach($dec_data['LpuUnitSet'] as $lus){
                        //https://redmine.swan.perm.ru/issues/90544
                        if($dec_data['period_inoter']['begDate'] != null){
                                $_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
                                $_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
                                $_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
                                $_POST['Registry_IsNew'][] = $Registry_IsNew;
                                $_POST['Registry_id'][] = 0;
                                $_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
                                $_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
                                $_POST['Registry_IsActive'][] = 2;
                                $_POST['Registry_begDate'][] = $dec_data['period_inoter']['begDate'];
                                $_POST['Registry_endDate'][] = $dec_data['period_inoter']['endDate'];
                                $_POST['OrgSmo_id'][] = $smo['Smo_id'];
                                $_POST['LpuUnitSet_id'][] = $lus['id'];
                                $_POST['Registry_Num'][] = (string)$lus['number'];                        
                                //Не используется
                                $_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$dec_data['period_inoter']['begDate'].'" Registry_endDate="'.$dec_data['period_inoter']['endDate'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"></Registry>'.PHP_EOL;                                       
                        }
                        else{
                            $_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
                            $_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
                            $_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
                            $_POST['Registry_IsNew'][] = $Registry_IsNew;
                            $_POST['Registry_id'][] = 0;
                            $_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
                            $_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
                            $_POST['Registry_IsActive'][] = 2;
                            $_POST['Registry_begDate'][] = $lus['datestart'];
                            $_POST['Registry_endDate'][] = $lus['dateend'];
                            $_POST['OrgSmo_id'][] = $smo['Smo_id'];
                            $_POST['LpuUnitSet_id'][] = $lus['id'];
                            $_POST['Registry_Num'][] = (string)$lus['number'];                        
                            //Не используется
                            $_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$dec_data['period_inoter']['begDate'].'" Registry_endDate="'.$dec_data['period_inoter']['endDate'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"></Registry>'.PHP_EOL;                                       

                        }

                    }
				}
			}
		}

		//echo "<pre>".print_r($_POST,1)."</pre>"; exit;

		$RegistryUfaVE->saveRegistry();

	}
        
	/**
	 *Обработка данных после работы с формой
         * https://redmine.swan.perm.ru/issues/90544
	 */
	function PrepareDataSMO(){
		$RegistryUfaVE = new RegistryUfaVE;
		
		if(!isset($_POST['data'])){
			echo 'Не получена ожидаемая JSON строка c с данными отделениям и списком СМО для предварительной подготовки данных';
			return false; 
		}   
		
		$dec_data = json_decode($_POST['data'], 1);
		$Registry_IsNew = null;
		if (!empty($_POST['Registry_IsNew'])) {
			$Registry_IsNew = (int)$_POST['Registry_IsNew'];
		}
		
		unset($_POST['data']);
		unset($_POST['Registry_IsNew']);

		$strip_symbols = array('"'=>"", "'"=>"", "`"=>"");

        //echo '<pre>' . print_r($_POST,1) . '</pre>';

		//Большие реестры (xml формируется в RegistryUfa_modelVE.php m=saveRegistryQueue_array))
		if(empty($dec_data['Smo'])){
			//ФОрмирование массивов для /?c=RegistryUfa&m=saveRegistry
			foreach($dec_data['LpuUnitSet'] as $lus){
				$_POST['Registry_id'][] = 0;
                $_POST['Registry_IsNotInsur'][] = $dec_data['global']['Registry_IsNotInsur'];
                $_POST['Registry_IsZNO'][] = $dec_data['global']['Registry_IsZNO'];
                $_POST['Registry_Comment'][] = $dec_data['global']['Registry_Comment'];
				$_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
				$_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
				$_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
				$_POST['Registry_IsNew'][] = $Registry_IsNew;
				$_POST['Registry_id'][] = 0;
				$_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
				$_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
				$_POST['PayType_SysNick'][] = $dec_data['global']['PayType_SysNick'];
				$_POST['Registry_IsActive'][]= 2;
				$_POST['Registry_begDate'][] = $lus['datestart'];
				$_POST['Registry_endDate'][] = $lus['dateend'];              
				$_POST['OrgSmo_id'][] = "";
				$_POST['LpuUnitSet_id'][] = $lus['id'];
				$_POST['Registry_Num'][] = $lus['number'];  
				//Не используется 
				$_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2"  Registry_begDate="'.$lus['datestart'].'"  Registry_endDate="'.$lus['dateend'].'" OrgSmo_id="" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'" Registry_IsNotInsur="'.$dec_data['global']['Registry_IsNotInsur'].'" Registry_IsZNO="'.$dec_data['global']['Registry_IsZNO'].'" Registry_Comment="'.$dec_data['global']['Registry_Comment'].'"></Registry>'.PHP_EOL;
            }   
		}
		//Реестры по указанным СМО и выбранным подразделениям
		else{
			foreach($dec_data['Smo'] as $smo){
				//Для инотеров - даты инотеров
				if($smo['Smo_id'] != 8){
					
					foreach($dec_data['LpuUnitSet'] as $lus){
	                    $_POST['Registry_IsNotInsur'][] = $dec_data['global']['Registry_IsNotInsur'];
	                    $_POST['Registry_IsZNO'][] = $dec_data['global']['Registry_IsZNO'];
	                    $_POST['Registry_Comment'][] = $dec_data['global']['Registry_Comment'];
	                    $_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
	                    $_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
						$_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
						$_POST['Registry_IsNew'][] = $Registry_IsNew;
						$_POST['Registry_id'][] = NULL;
						$_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
						$_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
						$_POST['PayType_SysNick'][] = $dec_data['global']['PayType_SysNick'];
						$_POST['Registry_IsActive'][] = 2;
						$_POST['Registry_begDate'][] = $lus['datestart'];
						$_POST['Registry_endDate'][] = $lus['dateend'];
						$_POST['OrgSmo_id'][] = $smo['Smo_id'];
						$_POST['LpuUnitSet_id'][] = $lus['id'];
						$_POST['Registry_Num'][] = (string)$lus['number'];
						//Не используется
						$_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$lus['datestart'].'" Registry_endDate="'.$lus['dateend'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"  Registry_IsNotInsur="'.$dec_data['global']['Registry_IsNotInsur'].'" Registry_IsZNO="'.$dec_data['global']['Registry_IsZNO'].'" Registry_Comment="'.$dec_data['global']['Registry_Comment'].'"></Registry>'.PHP_EOL;
					}
				}
				//Для инотеров - даты инотеров
				else{
                    foreach($dec_data['LpuUnitSet'] as $lus){
                        //https://redmine.swan.perm.ru/issues/90544
                        if($dec_data['period_inoter']['begDate'] != null){
                                $_POST['Registry_IsNotInsur'][] = $dec_data['global']['Registry_IsNotInsur'];
                                $_POST['Registry_IsZNO'][] = $dec_data['global']['Registry_IsZNO'];
                                $_POST['Registry_Comment'][] = $dec_data['global']['Registry_Comment'];
                                $_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
                                $_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
                                $_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
                                $_POST['Registry_IsNew'][] = $Registry_IsNew;
                                $_POST['Registry_id'][] = 0;
                                $_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
                                $_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
								$_POST['PayType_SysNick'][] = $dec_data['global']['PayType_SysNick'];
                                $_POST['Registry_IsActive'][] = 2;
                                $_POST['Registry_begDate'][] = $dec_data['period_inoter']['begDate'];
                                $_POST['Registry_endDate'][] = $dec_data['period_inoter']['endDate'];
                                $_POST['OrgSmo_id'][] = $smo['Smo_id'];
                                $_POST['LpuUnitSet_id'][] = $lus['id'];
                                $_POST['Registry_Num'][] = (string)$lus['number'];                    
                                //Не используется
                                $_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$dec_data['period_inoter']['begDate'].'" Registry_endDate="'.$dec_data['period_inoter']['endDate'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"  Registry_IsNotInsur="'.$dec_data['global']['Registry_IsNotInsur'].'" Registry_IsZNO="'.$dec_data['global']['Registry_IsZNO'].'" Registry_Comment="'.$dec_data['global']['Registry_Comment'].'"></Registry>'.PHP_EOL;
                        }
                        else{
                            $_POST['Registry_IsNotInsur'][] = $dec_data['global']['Registry_IsNotInsur'];
                            $_POST['Registry_IsZNO'][] = $dec_data['global']['Registry_IsZNO'];
                            $_POST['Registry_Comment'][] = $dec_data['global']['Registry_Comment'];
                            $_POST['RegistryType_id'][] = $dec_data['global']['RegistryType_id'];
                            $_POST['RegistrySubType'][] = $dec_data['global']['RegistrySubType'];
                            $_POST['Registry_accDate'][] = $dec_data['global']['Curr_date'];
                            $_POST['Registry_IsNew'][] = $Registry_IsNew;
                            $_POST['Registry_id'][] = 0;
                            $_POST['Lpu_id'][] = $dec_data['global']['lpu_id'];
                            $_POST['RegistryStatus_id'][] = $dec_data['global']['RegistryStatus_id'];
							$_POST['PayType_SysNick'][] = $dec_data['global']['PayType_SysNick'];
                            $_POST['Registry_IsActive'][] = 2;
                            $_POST['Registry_begDate'][] = $lus['datestart'];
                            $_POST['Registry_endDate'][] = $lus['dateend'];
                            $_POST['OrgSmo_id'][] = $smo['Smo_id'];
                            $_POST['LpuUnitSet_id'][] = $lus['id'];
                            $_POST['Registry_Num'][] = (string)$lus['number'];                    
                            //Не используется
                            $_POST['xml'][] = '<Registry RegistryType_id="'.$dec_data['global']['RegistryType_id'].'"  Registry_accDate="'.$dec_data['global']['Curr_date'].'" Registry_id="0" Lpu_id="'.$dec_data['global']['lpu_id'].'" RegistryStatus_id="'.$dec_data['global']['RegistryStatus_id'].'" Registry_IsActive="2" Registry_begDate="'.$dec_data['period_inoter']['begDate'].'" Registry_endDate="'.$dec_data['period_inoter']['endDate'].'" OrgSmo_id="'.$smo['Smo_id'].'" LpuUnitSet_id="'.$lus['id'].'" Registry_Num="'.$lus['number'].'"  Registry_IsNotInsur="'.$dec_data['global']['Registry_IsNotInsur'].'" Registry_IsZNO="'.$dec_data['global']['Registry_IsZNO'].'" Registry_Comment="'.$dec_data['global']['Registry_Comment'].'"></Registry>'.PHP_EOL;

                        }

                    }
				}
			}
		} 

		$RegistryUfaVE->saveUnionRegistry();

	}        
}
?>