<?php
/**
 * Модель СМП (Казахстан)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Sergey Popkov
 * @version      2015
*/

require_once(APPPATH.'models/_pgsql/CmpCallCard_model.php');

class Kz_CmpCallCard_model extends CmpCallCard_model {
	
	/**
	 * default desc 
	 */
	function loadCmpCloseCardEditForm($data) {
		
		$queryParams = array();
		$filter = '(1 = 1)';
		$filter .=" and CCC.CmpCallCard_id = :CmpCallCard_id";
		
		$query = "
			select 
				'' as \"accessType\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				--RTRIM(PMC.PMUser_Name) as Feldsher,				
				CCC.CmpCallCard_IsAlco as \"isAlco\",
				CCC.CmpCallType_id as \"CallType_id\",
				CCC.CmpReason_id as \"Reason_id\",
				CCC.Sex_id as \"Sex_id\",
				CCC.KLSubRgn_id as \"Area_id\",
				CCC.KLCity_id as \"City_id\",
				CCC.KLTown_id as \"Town_id\",
				CCC.KLStreet_id as \"Street_id\",
				CCC.CmpCallCard_Dom as \"House\",
				CCC.CmpCallCard_Kvar as \"Office\",
				to_char(COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay, null), 'DD.MM.YYYY') as \"Birthday\",
				org1.Org_Name as \"Work\",
				case
					when CCC.Person_Age > 0 then CCC.Person_Age
					else null
				end as \"Age\",
				to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY')||' '||to_char(CCC.CmpCallCard_prmDT, 'HH24:MI') as \"AcceptTime\",
				to_char(CCCStatusData.TransTime, 'DD.MM.YYYY')||' '||to_char(CCCStatusData.TransTime, 'HH24:MI') as \"TransTime\",
				COALESCE(PS.Person_Surname, case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end) as \"Fam\",
				COALESCE(PS.Person_Firname, case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end) as \"Name\",
				COALESCE(PS.Person_Secname, case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end) as \"Middle\",
				CCC.Person_id as \"Person_id\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"KLSubRgn_id\",
				CCC.KLCity_id as \"KLCity_id\",
				CCC.KLTown_id as \"KLTown_id\",
				CCC.KLStreet_id as \"KLStreet_id\",
				PMCins.MedPersonal_id as \"FeldsherAccept\",				
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				EMT.EmergencyTeam_Num as \"EmergencyTeamNum\",
				HSMP.Person_Fin as \"Doctor\",
				HSsecondMP.Person_Fin as \"Feldsher\",
				DRMP.Person_Fin as \"Driver\",
				to_char(CCC.CmpCallCard_TEnd, 'DD.MM.YYYY') || ' ' || SUBSTRING( to_char(CCC.CmpCallCard_TEnd, 'HH24:MI:SS'), 1, 5 ) as \"EndTime\"
			from
				v_CmpCallCard CCC 
				left join r101.v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_PersonState PS  on PS.Person_id = CCC.Person_id
				left join v_Job as job1  ON PS.Job_id=job1.Job_id
				left join v_Org as org1  ON job1.Org_id=org1.Org_id
				left join SocStatus as dbfss  on dbfss.SocStatus_id = PS.SocStatus_id
				LEFT JOIN v_Lpu L  on L.Lpu_id = CCC.Lpu_id
				LEFT join v_pmUserCache PMC  on PMC.PMUser_id = CCC.pmUser_updID
				LEFT join v_pmUserCache PMCins  on PMCins.PMUser_id = CCC.pmUser_insID		
				LEFT JOIN v_EmergencyTeam EMT  on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
				LEFT JOIN v_MedPersonal as HSMP  ON( HSMP.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal as HSsecondMP  ON( HSsecondMP.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal as DRMP  ON( DRMP.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN LATERAL (
					select 
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS  
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
                    limit 1
				) as CCCStatusData ON true
			where
			{$filter}	
            limit 1			
		";
		
		//LEFT JOIN v_pmUser P  on P.PMUser_id = CCC.pmUser_updID

			
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']		
		));

		if ( is_object($result) ) {
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * default desc 
	 */
	function loadCmpCloseCardViewForm($data) {
	
		$filter = "FALSE";
		$queryParams = array();
		
		if (!empty($data['CmpCallCard_id'])) {
			$filter = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} elseif (!empty($data['CmpCloseCard_id'])) {
			$filter = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$queryParams['CmpCloseCard_id'] = $data['CmpCloseCard_id'];
		}
		
		$query = "
			select 
				CClC.CardNum as \"CardNum\"
				,CClC.EmergencyTeamNum as \"EmergencyTeamNum\"
				--,CCLC.EmergencyTeam_id as EmergencyTeam_id
				,CCC.Person_id as \"Person_id\"
				,CClC.Area_id as \"Area_id\"
				,CClC.Town_id as \"Town_id\"
				,CClC.City_id as \"City_id\"
				,CClC.Street_id as \"Street_id\"
				,CClC.House as \"House\"
				,CClC.Office as \"Office\"
				,CClC.CallType_id as \"CallType_id\"
				,CClC.CmpCallCard_id as \"CmpCallCard_id\"
				,CClC.CmpCloseCard_id as \"CmpCloseCard_id\"
				,to_char(CClC.AcceptTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.AcceptTime as timestamp), 'HH24:MI') as \"AcceptTime\"
				,to_char(CClC.TransTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.TransTime as timestamp), 'HH24:MI') as \"TransTime\"
				,to_char(CClC.GoTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.GoTime as timestamp), 'HH24:MI') as \"GoTime\"				
				,to_char(CClC.ArriveTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.ArriveTime as timestamp), 'HH24:MI') as \"ArriveTime\"				
				,to_char(CClC.ToHospitalTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.ToHospitalTime as timestamp), 'HH24:MI') as \"ToHospitalTime\"
				,to_char(CClC.EndTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.EndTime as timestamp), 'HH24:MI') as \"EndTime\"
				,to_char(CClC.NextTime, 'DD.MM.YYYY')||' '||to_char(cast(CClC.NextTime as timestamp), 'HH24:MI') as \"NextTime\"
				,CClC.Fam as \"Fam\"
                ,CClC.Name as \"Name\"
                ,CClC.Middle as \"Middle\"
				,CClC.Sex_id as \"Sex_id\"
				,CClC.Age as \"Age\"
				,to_char(CClC.Birthday, 'DD.MM.YYYY') as \"Birthday\"
				,CClC.Work as \"Work\"
				,to_char(CClC.ServiceDT, 'DD.MM.YYYY')||' '||to_char(cast(CClC.ServiceDT as timestamp), 'HH24:MI') as \"ServiceDT\"
				,CClC.Feldsher as \"Feldsher\"
				,CClC.Doctor as \"Doctor\"
				,CClC.Driver as \"Driver\"
				,CClC.Reason_id as \"Reason_id\"
				,CASE WHEN COALESCE(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as \"isAlco\"
				,CClC.Lpu_tid as \"Lpu_tid\"
				,to_char(cast(CClC.DeliveryTime as timestamp), 'HH24:MI') as \"DeliveryTime\"
				,CClC.isHosp as \"isHosp\"
				,CClC.Kilo as \"Kilo\"
				,CCC.KLRgn_id as \"KLRgn_id\"
				,CClC.Area_id as \"Area_id\"
				,CClC.City_id as \"City_id\"
				,CClC.Town_id as \"Town_id\"
				,CClC.Street_id as \"Street_id\"
				,CClC.pmUser_insID as \"pmUser_insID\"
				,CClC.ExpEtap as \"ExpEtap\"
				,CClC.ExpDoctor as \"ExpDoctor\"
				,CClC.ExpZav as \"ExpZav\"
				,CClC.ExpGlav as \"ExpGlav\"
				,CClC.Complaints as \"Complaints\"
				,CClC.Anamnez as \"Anamnez\"
				,CClC.AnamnezLife as \"AnamnezLife\"
				,CClC.LocalStatus as \"LocalStatus\"
				,CClC.Shit as \"Shit\"
				,CClC.WorkAD as \"WorkAD\"
				,CClC.AD as \"AD\",
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as \"Pulse\",
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as \"Chss\",
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as \"Chd\",
				CClC.Temperature as \"Temperature\"
				,CClC.SaO as \"SaO\"
				,CClC.Gluck as \"Gluck\"
				,CClC.AfterShit as \"AfterShit\"
				,CClC.AfterWorkAD as \"AfterWorkAD\"
				,CClC.AfterAD as \"AfterAD\"
				,CClC.AfterPulse as \"AfterPulse\"
				,CClC.AfterChss as \"AfterChss\"
				,CClC.AfterChd as \"AfterChd\"
				,CClC.AfterTemperature as \"AfterTemperature\"
				,CClC.AfterSaO as \"AfterSaO\"
				,CClC.AfterGluck as \"AfterGluck\"
				,CClC.Diag_id as \"Diag_id\"
				,CClC.Instrument as \"Instrument\"
				,CClC.Lecheb as \"Lecheb\"
				,CClC.Rashod as \"Rashod\"
				,CCLC.FeldsherAccept as \"FeldsherAccept\"
				,UCA.PMUser_Name as \"pmUser_insName\" 
			from
				r101.v_CmpCloseCard CClC 
				left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS  on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L  on L.Lpu_id = CClC.Lpu_id	
				LEFT JOIN v_EmergencyTeam EMT  on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
				LEFT JOIN v_pmUserCache UCA  on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT  on UCT.PMUser_id = CClC.FeldsherTrans
				LEFT JOIN LATERAL (
					select 
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS  
					where
						CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
                    limit 1
				) as CCCStatusData ON true
			where
				{$filter}
            limit 1
		";
		
		//LEFT JOIN v_pmUser P  on P.PMUser_id = CCC.pmUser_updID

			
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {			
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	
	
	/**
	 * Все комбики
	 */
	/*
	function getComboxAll() {
		$query = "						
			select
				CMBJ.CmpCloseCardCombo_id,
				CMBJ.ComboName,
				CMBJ.ComboAdd,
				CMB.ComboSys,
				CMBJ.Parent_id,
				CMB.isLoc as isLocCombo,
				CMBJ.isLoc
			from
				r101.CmpCloseCardCombo CMB 

			join
				r101.CmpCloseCardCombo CMBJ  ON CMBJ.Parent_id = CMB.CmpCloseCardCombo_id

			where 
				CMB.Parent_id = '0'			
		";	
		
		$result = $this->db->query($query);		
		if ( !is_object($result) ) return false;		
		$res = $result->result('array');
		$ret = array();		
		foreach ($res as $r2) {			
			if ($r2['isLoc'] == '1') {
				if ($r2['isLocCombo'] == '2') {
					$ret[$r2['ComboSys']][] = array("boxLabel"=>$r2['ComboName'].' '.$r2['ComboAdd'], "id"=>"CMPCLOSE_CB_{$r2['CmpCloseCardCombo_id']}", "name"=>$r2['ComboSys'], "inputValue"=>$r2['CmpCloseCardCombo_id']);
				} else {
					$ret[$r2['ComboSys']][] = array("boxLabel"=>$r2['ComboName'].' '.$r2['ComboAdd'], "id"=>"CMPCLOSE_CB_{$r2['CmpCloseCardCombo_id']}", "name"=>$r2['ComboSys'].'[]', "inputValue"=>$r2['CmpCloseCardCombo_id']);
				}
			} else {
				$wid = strlen($r2['ComboName'].' '.$r2['ComboAdd']);
				if ($wid < 10) $wl = 50;
				if ($wid >= 10) $wl = 120;
				if ($wid > 20) $wl = 400;
				if ($r2['isLocCombo'] == '2') {
					$add = ($r2['ComboAdd'] != '')?', <i>'.$r2['ComboAdd'].'</i>':'';
					$ret[$r2['ComboSys']][] = array(
						"boxLabel"	=>	$r2['ComboName'].$add,
						"id"		=>	"CMPCLOSE_CB_{$r2['CmpCloseCardCombo_id']}",
						"name"		=>	$r2['ComboSys'],
						"value"		=>	'2'
					);
				}
				$ret[$r2['ComboSys']][] = array(
					"labelWidth" => $wl,
					"labelAlign" => "left",
					"name"=>'ComboValue['.$r2['CmpCloseCardCombo_id'].']',
					"xtype" => 'textfield',
					"ctCls" => "left",
					"id"=>"CMPCLOSE_ComboValue_{$r2['CmpCloseCardCombo_id']}",
					"style" => "text-align: left",
					"fieldLabel" =>  ($r2['isLocCombo'] != '2')?($r2['ComboName'].' '.$r2['ComboAdd']):''
				);							
			}						
		}	
		return $ret;		
	}
	*/
	
	
	
		
	/**
	 * @desc Сохранение формы 110у
	 * @param array $data
	 * @return boolean 
	 */
	/*
	function saveCmpCloseCard110($data) {
		
		$action = null;
		
		$rules = array(
			array( 'field' => 'Kilo' , 'label' => 'Километраж' , 'type' => 'float', 'maxValue' => '1000' ),
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !empty( $err ) )
			return $err ;
		
		if ((isset($data['CmpCloseCard_id']))&&($data['CmpCloseCard_id'] != null)) {
			$action = 'edit';
			$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';			
			$procedure = 'r101.p_CmpCloseCard_upd';
			$relProcedure = 'r101.p_CmpCloseCardRel_ins';
		} else {
			$query = "select
						CLC.CmpCloseCard_id
					from
						r101.v_CmpCloseCard CLC 

					where
						CLC.CmpCallCard_id = :CmpCallCard_id
			";
			$queryParams = array('CmpCallCard_id' => $data['CmpCallCard_id']); 
			$result = $this->db->query($query, $queryParams);
			$retrun = $result->result('array');
			if ( is_object($result) && count($retrun) > 0) {
				$data['CmpCloseCard_id'] = $retrun[0]['CmpCloseCard_id'];
				$action = 'edit';
				$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';
				$procedure = 'r101.p_CmpCloseCard_upd';
				$relProcedure = 'r101.p_CmpCloseCardRel_ins';				
			} else {			
				$action = 'add';
				$closeCard = '';
				$procedure = 'r101.p_CmpCloseCard_ins';
				$relProcedure = 'r101.p_CmpCloseCardRel_ins';
			}
		}
		
		$UnicNums = ';';
				
		//Приводим данные в полях с типом datetime к виду, в котором их принимает БД
		$timeFiledsNames = array(
			'AcceptTime',
			'TransTime',
			'GoTime',
			'ArriveTime',
			'ServiceDT',
			'ToHospitalTime',
			'NextTime',
			'EndTime'
		);
		
		foreach ($timeFiledsNames as $key => $timeFieldName) {
			if (!empty($data[$timeFieldName])) {
				if (isset($data[$timeFieldName])&&$data[$timeFieldName] != '') $data[$timeFieldName] = substr($data[$timeFieldName],3,3).substr($data[$timeFieldName],0,3).substr($data[$timeFieldName],6,10);
			}
		}
			
		
		$birthday = null;
		if ($data['Birthday'] != '') {
			$birthday = explode(' ',$data['Birthday']);
			$birthday = explode('.',$birthday[0]);
			$birthday = $birthday[1].'.'.$birthday[0].'.'.$birthday[2].' 00:00';
		}
		
		$queryParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			
			'CardNum' => $data['CardNum'],
			
			'Feldsher' => $data['Feldsher'],
			'Doctor' => $data['Doctor'],
			'Driver' => $data['Driver'],
						
			'EmergencyTeamNum' => $data['EmergencyTeamNum'],
			//'EmergencyTeam_id' => $data['EmergencyTeam_id'],
			'AcceptTime' => ($data['AcceptTime'] != '') ? $data['AcceptTime'] : null,
			'TransTime' => ($data['TransTime'] != '') ? $data['TransTime'] : null,
			'GoTime' => ($data['GoTime'] != '') ? $data['GoTime'] : null,
			'ArriveTime' => ($data['ArriveTime'] != '') ? $data['ArriveTime'] : null,			
			'ToHospitalTime' => ($data['ToHospitalTime'] != '') ? $data['ToHospitalTime'] : null,
			'EndTime' => ($data['EndTime'] != '') ? $data['EndTime'] : null,
			'NextTime' => ($data['NextTime'] != '') ? $data['NextTime'] : null,			
			'ServiceDT' => ($data['ServiceDT'] != '') ? $data['ServiceDT'] : null,
			
			'Area_id' => (int)$data['Area_id'] ? (int)$data['Area_id'] : null,
			'City_id' =>  (int)$data['City_id'] ? (int)$data['City_id'] : null,
			'Town_id' => (int)$data['Town_id'] ? (int)$data['Town_id'] : null,
			'Street_id' => (int)$data['Street_id'] ? (int)$data['Street_id'] : null,
			'House' => $data['House'],			
			'Office' => $data['Office'],
			
			'Fam' => $data['Fam'],
			'Name' => $data['Name'],
			'Middle' => $data['Middle'],
			'Age' => $data['Age'],
			'Birthday' => $birthday,
			'Sex_id' => $data['Sex_id'],
			'Work' => $data['Work'],
			
			'CallType_id' => $data['CallType_id'],
			'Reason_id' => $data['Reason_id'],
			
			'isAlco' => (($data['isAlco'] > 0)?$data['isAlco']:null),
			
			'Lpu_tid' => $data['Lpu_tid'],			
			'DeliveryTime' => $data['DeliveryTime'],		
			'isHosp' => $data['isHosp'],		
			'Kilo' => $data['Kilo'],
					
			'ExpEtap' => $data['ExpEtap'],		
			'ExpDoctor' => $data['ExpDoctor'],		
			'ExpZav' => $data['ExpZav'],		
			'ExpGlav' => $data['ExpGlav'],
					
			'Complaints' => $data['Complaints'],			
			'Anamnez' => $data['Anamnez'],
			'AnamnezLife' => $data['AnamnezLife'],
			'CmpCloseCard_StatusLocalis' => $data['CmpCloseCard_StatusLocalis'],
			
			'Shit' => $data['Shit'],			
			'WorkAD' => $data['WorkAD'],
			'AD' => $data['AD'],
			'Chss' => $data['Chss'],
			'Pulse' => $data['Pulse'],
			'Temperature' => $data['Temperature'],
			'Chd' => $data['Chd'],
			'SaO' => $data['SaO'],			
			'Gluck' => $data['Gluck'],
			
			'AfterShit' => $data['AfterShit'],			
			'AfterWorkAD' => $data['AfterWorkAD'],
			'AfterAD' => $data['AfterAD'],
			'AfterChss' => $data['AfterChss'],
			'AfterPulse' => $data['AfterPulse'],
			'AfterTemperature' => $data['AfterTemperature'],
			'AfterChd' => $data['AfterChd'],
			'AfterSaO' => $data['AfterSaO'],			
			'AfterGluck' => $data['AfterGluck'],
			
			'Diag_id' => (isset($data['Diag_id']) && $data['Diag_id'] != '') ? $data['Diag_id'] : null,			
			'Instrument' => $data['Instrument'],
			'Lecheb' => $data['Lecheb'],
			'Rashod' => $data['Rashod'],
									
			'pmUser_id' => $data['pmUser_id']
		);	
		
		$txt = "";
		foreach ($queryParams as $q=>$p) {
			$txt .= "@".$q." = :".$q.",\r\n";
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
				".$UnicNums."
			set @Res = 0;
			exec " . $procedure . "
				@CmpCloseCard_id = @Res output,				
				".$txt."				
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;


		";
		
		if ( $action == 'edit' ) {
			$NewCmpCloseCard_id = null;
			//Если админ смп , то делаем копию исходной записи, а измененную копию сохраняем на место старой 	
			//1 - выбираем старую запись
			$squery = "
				SELECT * 
				FROM r101.v_CmpCloseCard CLC 

				WHERE CLC.CmpCloseCard_id = " . $data['CmpCloseCard_id'] . ";
			";
			
			$result = $this->db->query($squery, $data);

			if ( !is_object($result) ) {
				return false;
			}
			$oldresult = $result->result('array');
			$oldresult = $oldresult[0];

			
			//2 - сохраняем страую запись в новую

			$squeryParams = array(

				'CmpCallCard_id' => $oldresult['CmpCallCard_id'],
			
				'CardNum' => $oldresult['CardNum'],

				'Feldsher' => $oldresult['Feldsher'],
				'Doctor' => $oldresult['Doctor'],
				'Driver' => $oldresult['Driver'],

				'EmergencyTeamNum' => $oldresult['EmergencyTeamNum'],
				//'EmergencyTeam_id' => $oldresult['EmergencyTeam_id'],
				'AcceptTime' => ($oldresult['AcceptTime'] != '') ? $oldresult['AcceptTime'] : null,
				'TransTime' => ($oldresult['TransTime'] != '') ? $oldresult['TransTime'] : null,
				'GoTime' => ($oldresult['GoTime'] != '') ? $oldresult['GoTime'] : null,
				'ArriveTime' => ($oldresult['ArriveTime'] != '') ? $oldresult['ArriveTime'] : null,			
				'ToHospitalTime' => ($oldresult['ToHospitalTime'] != '') ? $oldresult['ToHospitalTime'] : null,
				'EndTime' => ($oldresult['EndTime'] != '') ? $oldresult['EndTime'] : null,
				'NextTime' => ($oldresult['NextTime'] != '') ? $oldresult['NextTime'] : null,
				'ServiceDT' => ($oldresult['ServiceDT'] != '') ? $oldresult['ServiceDT'] : null,

				'Area_id' => (int)$oldresult['Area_id'] ? (int)$oldresult['Area_id'] : null,
				'City_id' =>  (int)$oldresult['City_id'] ? (int)$oldresult['City_id'] : null,
				'Town_id' => (int)$oldresult['Town_id'] ? (int)$oldresult['Town_id'] : null,
				'Street_id' => (int)$oldresult['Street_id'] ? (int)$oldresult['Street_id'] : null,
				'House' => $oldresult['House'],			
				'Office' => $oldresult['Office'],

				'Fam' => $oldresult['Fam'],
				'Name' => $oldresult['Name'],
				'Middle' => $oldresult['Middle'],
				'Age' => $oldresult['Age'],
				'Birthday' => ($oldresult['Birthday'] != '') ? $oldresult['Birthday'] : null,
				'Sex_id' => $oldresult['Sex_id'],
				'Work' => $oldresult['Work'],

				'CallType_id' => $oldresult['CallType_id'],
				'Reason_id' => $oldresult['Reason_id'],

				'isAlco' => (($oldresult['isAlco'] > 0)?$oldresult['isAlco']:null),

				'Lpu_tid' => $oldresult['Lpu_tid'],				
				'DeliveryTime' => $oldresult['DeliveryTime'],		
				'isHosp' => $oldresult['isHosp'],		
				'Kilo' => $oldresult['Kilo'],

				'ExpEtap' => $oldresult['ExpEtap'],		
				'ExpDoctor' => $oldresult['ExpDoctor'],		
				'ExpZav' => $oldresult['ExpZav'],		
				'ExpGlav' => $oldresult['ExpGlav'],

				'Complaints' => $oldresult['Complaints'],			
				'Anamnez' => $oldresult['Anamnez'],
				'AnamnezLife' => $oldresult['AnamnezLife'],
				'CmpCloseCard_StatusLocalis' => $oldresult['CmpCloseCard_StatusLocalis'],

				'Shit' => $oldresult['Shit'],			
				'WorkAD' => $oldresult['WorkAD'],
				'AD' => $oldresult['AD'],
				'Chss' => $oldresult['Chss'],
				'Pulse' => $oldresult['Pulse'],
				'Temperature' => $oldresult['Temperature'],
				'Chd' => $oldresult['Chd'],
				'SaO' => $oldresult['SaO'],			
				'Gluck' => $oldresult['Gluck'],

				'AfterShit' => $oldresult['AfterShit'],			
				'AfterWorkAD' => $oldresult['AfterWorkAD'],
				'AfterAD' => $oldresult['AfterAD'],
				'AfterChss' => $oldresult['AfterChss'],
				'AfterPulse' => $oldresult['AfterPulse'],
				'AfterTemperature' => $oldresult['AfterTemperature'],
				'AfterChd' => $oldresult['AfterChd'],
				'AfterSaO' => $oldresult['AfterSaO'],			
				'AfterGluck' => $oldresult['AfterGluck'],

				'Diag_id' => (isset($oldresult['Diag_id']) && $oldresult['Diag_id'] != '') ? $oldresult['Diag_id'] : null,			
				'Instrument' => $oldresult['Instrument'],
				'Lecheb' => $oldresult['Lecheb'],
				'Rashod' => $oldresult['Rashod'],

				'pmUser_id' => $oldresult['pmUser_insID'],

				'CmpCloseCard_firstVersion' => $oldresult['CmpCloseCard_firstVersion'],
			);
		
			$txt = "";
			foreach ($squeryParams as $q=>$p) {
				$txt .= "@".$q." = :".$q.",\r\n";
			}
			
			$squery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = 0;

				exec r101.p_CmpCloseCard_ins						
					".$txt."
					@CmpCloseCard_id = @Res output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;


			";
		
			$result = $this->db->query($squery, $squeryParams);

			if ( !is_object($result) ) {
				return false;
			}
			
			$result = $result->result('array');
			$result = $result[0];
			
			$NewCmpCloseCard_id = $result['CmpCloseCard_id'];

			// 3 - заменяем старую запись текущими изменениями
		
			$newParams = $queryParams;	
			$newParams['CmpCloseCard_id']  =  $oldresult['CmpCloseCard_id'];
			
			if ( (!isset($newParams['CmpCloseCard_id']))||($newParams['CmpCloseCard_id'] == null ) )
			{
				$newParams['CmpCallCard_id'] = $oldresult['CmpCallCard_id'];
			}
			
			$txt = "";
			foreach ($newParams as $q=>$p) {
				$txt .= "@".$q." = :".$q.",\r\n";
			}
		
			$squery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :CmpCloseCard_id;

				exec r101.p_CmpCloseCard_upd
				".$txt."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;


			";	
		
			$result = $this->db->query($squery, $newParams);
			
			if ( !is_object($result) ) {
				return false;
			}
			$resArray = $result->result('array');

			// 4 - устанавливаем значение старого id в перезаписанной записи 		
			$squery = "
				exec r101.p_CmpCloseCard_setFirstVersion 
				@CmpCloseCard_id = " . $oldresult['CmpCloseCard_id'] . ",
				@CmpCloseCard_firstVersion = " . $NewCmpCloseCard_id . ",
				@pmUser_id = " . $data['pmUser_id'] . ";							
			";
		
			$res = $this->db->query($squery);
					
		} else {	// add
			$result = $this->db->query($query, $queryParams);
			$resArray = $result->result('array');	
		}
		
		
		
		// Комбо которые нам нужны для сохранения
		$comboFields = array(
			'Condition_id', 
			'Cons_id',
			'Behavior_id',
			'Pupil_id',
			'Light_id',
			'Aniz_id',
			'Kozha_id', 
			'Heart_id', 
			'Noise_id', 
			'Pulse_id', 
			'Exkurs_id', 
			'Hale_id', 
			'Rattle_id', 
			'Shortwind_id', 
			'Nev_id', 
			'Menen_id', 
			'Eye_id',
			'Chmn_id', 
			'Reflex_id',
			'Move_id', 
			'Bol_id', 
			'Afaz_id', 
			'Sbabin_id', 
			'Soppen_id', 
			'Zev_id', 
			'Mindal_id', 
			'Lang_id', 
			'Gaste_id',
			'Sympt_id',
			'Liver_id',
			'Selez_id',
			'Moch_id',
			'Menst_id',
			'Per_id',
			'Result_id',
			'ResultV_id',
			'Travm_id'
		);
		
		$relComboFields = array();
		foreach ($comboFields as $cfield) {
			if (isset($data[$cfield])) {
				//Если это чекбокс, собираем значения отмеченных
				if (is_array($data[$cfield])) {
					foreach ($data[$cfield] as $dataField) {
						$relComboFields[] = $dataField;
					}
				}
				if (((int)$data[$cfield] == $data[$cfield]) && ($data[$cfield] > 0)) {
					//Если это радиобаттон берем его значение
					$relComboFields[] = $data[$cfield];
				}
			}
		}
		
	
		$queryRelParams = array(
			'CmpCloseCard_id' => $resArray[0]['CmpCloseCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		if ($action == 'add') {
			$relResult = array();
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
					set @Res = 0;
					exec " . $relProcedure . "
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = :CmpCloseCard_id,
						@CmpCloseCardCombo_id = :relComboField,				
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";	


				//var_dump(getDebugSQL($query, $queryRelParams)); exit;
				$relResult[] = $this->db->query($query, $queryRelParams);			
			}
		
			//Если у нас тип - это поле ввода
			if (is_array($data['ComboValue'])) foreach ($data['ComboValue'] as $cKey => $cValue) {
				if ($cValue != '') {
					$queryRelParams['cKey'] = $cKey;
					$queryRelParams['cValue'] = $cValue;
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000)
							".$UnicNums."
						set @Res = 0;
						exec " . $relProcedure . "
							@CmpCloseCardRel_id = @Res output,
							@CmpCloseCard_id = :CmpCloseCard_id,
							@CmpCloseCardCombo_id = :cKey,
							@Localize = :cValue,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";


					$relResult[] = $this->db->query($query, $queryRelParams);
				}		
			}

			if ( is_object($result) ) {			
				return $resArray;
			}
			else {
				return false;
			}		
		} else {// action edit
			$relResult = array();
			//заменяем id комбобоксов на свежий
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				//$query = "
				//	declare
				//		@pmUser_id bigint,
				//		@Error_Code int,
				//		@Error_Message varchar(4000);
				//	exec p_CmpCloseCardRel_updVersion
				//		@CmpCloseCard_oldId = ".$oldresult['CmpCloseCard_id'].",
				//		@CmpCloseCard_newId = ".$NewCmpCloseCard_id.",
				//	@pmUser_id = " . $data['pmUser_id'] . "				
				//	";
				$query = "					
				update 
					r101.CmpCloseCardRel with (ROWLOCK) 
				set
					CmpCloseCard_id = ".$NewCmpCloseCard_id.",
					pmUser_updID = ".$data['pmUser_id'].",
					CmpCloseCardRel_updDT = dbo.tzGetDate()
				where 
					CmpCloseCard_id = ".$oldresult['CmpCloseCard_id'];
				//$relResult[] = $this->db->query($query, $queryRelParams);	
				$relResult[] = $this->db->query($query, array());
			}	
			
			//записываем новые значения комбиков в стрый id
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
					set @Res = 0;
					exec " . $relProcedure . "
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = ".$oldresult['CmpCloseCard_id'].",
						@CmpCloseCardCombo_id = :relComboField,				
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";			


				$relResult[] = $this->db->query($query, $queryRelParams);				
			}		
		
			//Если у нас тип - это поле ввода
			if (is_array($data['ComboValue'])) foreach ($data['ComboValue'] as $cKey => $cValue) {
				if ($cValue != '') {
					$queryRelParams['cKey'] = $cKey;
					$queryRelParams['cValue'] = $cValue;
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000)
							".$UnicNums."
						set @Res = 0;
						exec " . $relProcedure . "
							@CmpCloseCardRel_id = @Res output,
							@CmpCloseCard_id = ".$oldresult['CmpCloseCard_id'].",
							@CmpCloseCardCombo_id = :cKey,
							@Localize = :cValue,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as CmpCloseCard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";


					$relResult[] = $this->db->query($query, $queryRelParams);
				}		
			}
	
			return $resArray;
		}
	}
	*/

	/**
	 * для ЭМК
	 * @param type $data
	 * @return array
	 */
	function printCmpCloseCardEMK($data) {
		$query = "
			select
				CLC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,to_char(CLC.CmpCloseCard_insDT, 'DD.MM.YYYY') as \"CardDate\"
				,to_char(CC.CmpCallCard_insDT, 'DD.MM.YYYY') as \"CallCardDate\"
				,CLC.Day_num as \"Day_num\"
				,CLC.Year_num as \"Year_num\"
				--,CLC.Feldsher_id
				--,CLC.StationNum
				--,CLC.EmergencyTeamNum
				,to_char(CLC.AcceptTime, 'DD.MM.YYYY')||' '||to_char(CLC.AcceptTime, 'HH24:MI:SS') as \"AcceptDateTime\"
				,SX.Sex_name as \"Sex_name\"
				--,CLC.SummTime
				,CLC.Fam as \"Fam\"
				,CLC.Name as \"Name\"
				,CLC.Middle as \"Middle\"
				--,CLC.Age
				,DIAG.Diag_FullName as \"Diag\"
				,UCA.PMUser_Name as \"FeldsherAcceptName\"
				--,UCT.PMUser_Name as \"FeldsherTransName\"
				,to_char(ccp.CmpCallCardCostPrint_setDT, 'DD.MM.YYYY') as \"CmpCallCardCostPrint_setDT\"
				,ccp.CmpCallCardCostPrint_IsNoPrint as \"CmpCallCardCostPrint_IsNoPrint\"
				,CAST(CAST(ccp.CmpCallCardCostPrint_Cost as numeric(19,2)) as varchar) as \"CostPrint\"
			from
				{$this->schema}.v_CmpCloseCard CLC 
				LEFT JOIN v_CmpCallCard CC  on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_CmpCallCardCostPrint ccp  on ccp.CmpCallCard_id = cc.CmpCallCard_id
				LEFT JOIN Sex SX  on SX.Sex_id = CLC.Sex_id
				left join v_Diag DIAG  on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_pmUserCache UCA  on UCA.PMUser_id = CLC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT  on UCT.PMUser_id = CLC.FeldsherTrans
			where
				CLC.CmpCloseCard_id = :CmpCloseCard_id
            limit 1
		";


		$result = $this->db->query($query, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id']
		));


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * default desc 
	 */
	function getComboRel($CmpCloseCard, $SysName) {
		
		$query = "						
			select
				CMB.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\"
				, CMB.ComboName as \"ComboName\"
			from
				r101.CmpCloseCardCombo CMB 
			where 
				Parent_id = '0'
				AND ComboSys = :combo_id
		";
		
		$queryParams = array('combo_id' => $SysName);
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			$res = $result->result('array');	
			
			$comboid = $res[0]['CmpCloseCardCombo_id'];
		
			$query = "						
				select 
					--CCombo.ComboSys,
					Ccombo.CmpCloseCardCombo_id as \"CmpCloseCardCombo_id\",
					CCombo.ComboName as \"ComboName\",
					RL.Localize as \"Localize\"
					,CASE WHEN COALESCE(RL.CmpCloseCardRel_id,0) = 0 THEN 0 ELSE 1 END as \"flag\"
				from
					r101.CmpCloseCardCombo CCombo 
				LEFT JOIN r101.CmpCloseCardRel RL  on RL.CmpCloseCard_id = :CmpCloseCard_id
					and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id
				where
					CCombo.Parent_id = :ComboId
			";

			$queryParams = array(
				'CmpCloseCard_id' => $CmpCloseCard,
				'ComboId' => $comboid
			);
			
			$result = $this->db->query($query, $queryParams);
			
			if ( is_object($result) ) {
				$result = $result->result('array');					
				$new = array();
				foreach ($result as $res) $new[$res['CmpCloseCardCombo_id']] = $res;
				return $new;
			} else {
				return false;
			}
		}
	}
	
	
	
	/**
	 * default desc 
	 */
	function printCmpCloseCard110($data) {
		$query = "
			select 
				CLC.CardNum as \"CardNum\"
				,CLC.EmergencyTeamNum as \"EmergencyTeamNum\"				
				,MPh1.Person_Fin as \"EmergencyTeam_HeadShiftFIO\"
				,CLC.Area_id as \"Area_id\"
				,KL_AR.KLArea_Name as \"Area\"
				,CLC.City_id as \"City_id\"
				,KL_CITY.KLArea_Name as \"City\"
				,CLC.Town_id as \"Town_id\"
				,KL_TOWN.KLArea_Name as \"Town\"
				,CLC.Street_id as \"Street_id\"
				,KL_ST.KLStreet_Name as \"Street\"					
				,CLC.House as \"House\"
				,CLC.Office as \"Office\"
				,COALESCE(KL_AR.KLArea_Name, '') || ' ' || COALESCE(KL_CITY.KLArea_Name, '') || ' ' || COALESCE(KL_TOWN.KLArea_Name,'') || ' ' || COALESCE(KL_ST.KLStreet_Name,'') || ' ' || CLC.House || ', ' || CLC.Office as \"Adress\"
				,CLC.CallType_id as \"CallType_id\"
				,CLC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				,to_char(CLC.AcceptTime, 'HH24:MI') as \"AcceptTime\"
				,to_char(CLC.AcceptTime, 'DD.MM.YYYY') as \"AcceptDate\"
				,to_char(CLC.TransTime, 'HH24:MI') as \"TransTime\"
				,to_char(CLC.GoTime, 'HH24:MI') as \"GoTime\"
				,to_char(CLC.ArriveTime, 'HH24:MI') as \"ArriveTime\"				
				,to_char(CLC.ToHospitalTime, 'HH24:MI') as \"ToHospitalTime\"
				,to_char(CLC.EndTime, 'HH24:MI') as \"EndTime\"
				,to_char(CLC.NextTime, 'HH24:MI') as \"NextTime\"
				,to_char(CLC.ServiceDT, 'DD.MM.YYYY') as \"ServiceDT\"
				,CLC.Fam as \"Fam\"
                ,CLC.Name as \"Name\"
                ,CLC.Middle as \"Middle\"
				,CLC.Sex_id as \"Sex_id\"
				,CLC.Age as \"Age\"
				,to_char(CLC.Birthday, 'DD.MM.YYYY') as \"Birthday\"
				,CLC.Work as \"Work\"
				,to_char(CLC.ServiceDT, 'DD.MM.YYYY')||' '||to_char(cast(CLC.ServiceDT as timestamp), 'HH24:MI') as \"ServiceDT\"
				,CLC.Feldsher as \"Feldsher\"
				,CLC.Doctor as \"Doctor\"
				,CLC.Driver as \"Driver\"
				,CLC.Reason_id as \"Reason_id\"
				,CASE WHEN COALESCE(CLC.isAlco,0) = 0 THEN NULL ELSE CLC.isAlco END as \"isAlco\"
				,CLC.Lpu_tid as \"Lpu_tid\"
				,to_char(cast(CLC.DeliveryTime as timestamp), 'HH24:MI') as \"DeliveryTime\"
				,CLC.isHosp as \"isHosp\"
				,CLC.Kilo as \"Kilo\"
				,CLC.pmUser_insID as \"pmUser_insID\"
				,CLC.ExpEtap as \"ExpEtap\"
				,CLC.ExpDoctor as \"ExpDoctor\"
				,CLC.ExpZav as \"ExpZav\"
				,CLC.ExpGlav as \"ExpGlav\"
				,CLC.Complaints as \"Complaints\"
				,CLC.Anamnez as \"Anamnez\"
				,CLC.AnamnezLife as \"AnamnezLife\"
				,CLC.LocalStatus as \"LocalStatus\"
				,CLC.Shit as \"Shit\"
				,CLC.WorkAD as \"WorkAD\"
				,CLC.AD as \"AD\"
				,CLC.Pulse as \"Pulse\"
				,CLC.Chss as \"Chss\"
				,CLC.Chd as \"Chd\"
				,CLC.Temperature as \"Temperature\"
				,CLC.SaO as \"SaO\"
				,CLC.Gluck as \"Gluck\"
				,CLC.AfterShit as \"AfterShit\"
				,CLC.AfterWorkAD as \"AfterWorkAD\"
				,CLC.AfterAD as \"AfterAD\"
				,CLC.AfterPulse as \"AfterPulse\"
				,CLC.AfterChss as \"AfterChss\"
				,CLC.AfterChd as \"AfterChd\"
				,CLC.AfterTemperature as \"AfterTemperature\"
				,CLC.AfterSaO as \"AfterSaO\"
				,CLC.AfterGluck as \"AfterGluck\"
				,CLC.Diag_id as \"Diag_id\"
				,CLC.Instrument as \"Instrument\"
				,CLC.Lecheb as \"Lecheb\"
				,CLC.Rashod as \"Rashod\"
				,DIAG.Diag_FullName as \"Diag\"
				,DIAG.Diag_Code as \"CodeDiag\"		
				,isAlco as \"isAlco\"
				,CASE WHEN COALESCE(CLC.isHosp,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isHosp\"
				,CCT.CmpCallType_Name as \"CallType\"
				,SX.Sex_name as \"Sex_name\"
				,RS.CmpReason_Name as \"Reason\"
				,Lpu.Lpu_name as \"Lpu_name\"
				--,CASE WHEN COALESCE(CC.CmpLpu_id,0) =0 THEN Lpu.Lpu_name ELSE CmpLpu.Lpu_name END as Lpu_name
			from
				r101.v_CmpCloseCard CLC 
				LEFT JOIN Sex SX  on SX.Sex_id = CLC.Sex_id
				LEFT join v_pmUserCache PMCA  on PMCA.PMUser_id = CLC.FeldsherAccept
				LEFT join v_pmUserCache PMCT  on PMCT.PMUser_id = CLC.FeldsherTrans
				LEFT JOIN v_CmpReason RS  on RS.CmpReason_id = CLC.Reason_id
				LEFT JOIN KLStreet KL_ST  on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN KLArea KL_AR  on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY  on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN  on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CLC.CallType_id
				left join v_Diag DIAG  on DIAG.Diag_id = CLC.Diag_id
				left join v_CmpCallCard CC  on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = CC.Lpu_id
				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id=CC.EmergencyTeam_id
				left join v_MedPersonal MPh1  on MPh1.MedPersonal_id=ET.EmergencyTeam_HeadShift
				--left join v_Lpu CmpLpu  on CmpLpu.Lpu_id = CC.CmpLpu_id
			where
				CLC.CmpCallCard_id = :CmpCallCard_id
            limit 1
		";
		
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']		
		));
		

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * default desc 
	 */
	 /*
	function loadCmpCloseCardComboboxesViewForm($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}
		
		$query = "
			SELECT
				--CCC.CmpCloseCard_id,
				CCCR.CmpCloseCardCombo_id,
				CCCR.Localize
			FROM
				r101.v_CmpCloseCard CCC 

				LEFT JOIN r101.v_CmpCloseCardRel CCCR  on CCCR.CmpCloseCard_id = CCC.CmpCloseCard_id

			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
			";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$retrun = $result->result('array');			
			return $retrun;
		} else {
			return false;
		}
	}
	*/
	
}
