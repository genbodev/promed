<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  WorkList_model
 * @author	  Yan Yudin (yudin.yan@gmail.com)
 * @version	  12 2019
 */

 class WorkList_model extends swModel {
	/**
	* Конструктор
	*/
	function __construct() {
	    parent::__construct();
	}

	/**
	 * Получаем список услуг на службе
	 * @return array
	 */
	function getUsluaList($data) {
	   	$result = $this->queryResult("
		   SELECT
			   UCMS.UslugaComplexMedService_id,
			   UCMS.MedService_id,
			   UC.UslugaComplex_id,
			   UC.UslugaComplex_Name,
			   ucat.UslugaCategory_Name,
			   ucat.UslugaCategory_SysNick,
			   uc.UslugaComplex_Code,
			   MPC.MedProductCard_id,
			   MPC1.MedProductClass_Name
		   FROM 
			   v_UslugaComplexMedService UCMS with (NOLOCK)
			   left join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
			   left join v_UslugaCategory ucat with (NOLOCK) on UC.UslugaCategory_id = ucat.UslugaCategory_id
			   left join v_MedProductUslugaComplex MPUC with (nolock) on UCMS.UslugaComplexMedService_id = MPUC.UslugaComplexMedService_id
			   left join passport.v_MedProductCard MPC with (nolock) on MPUC.MedProductCard_id = MPC.MedProductCard_id
			   left join passport.v_MedProductClass MPC1 with (nolock) on MPC.MedProductClass_id = MPC1.MedProductClass_id
		   WHERE
			   UCMS.MedService_id = :MedService_id and  UslugaComplexMedService_endDT is null
			", [
				'MedService_id' => $data['MedService_id']
			]);

		if(!empty($result)) return $result;
		
	    return array(array('success'=>false,'Error_Msg'=>'К данной службе не привязаны услуги'));
	}

	/**
	* Добавляем связь между услугой на службе и МИ
	* @return array 
	*/
	function addMedProductUslugaComplex($data) {
	    try {
	        $response_arr = $this->getMedProductUslugaComplexList();
			$decodeData = json_decode($data['Data'], true)['Data'];
			
	        if(!empty($decodeData)) {
	            foreach ($decodeData as $i => $value) {
	                if(empty($decodeData[$i]['MedProductCard_id'])) {
						for($j = 0; $j < count($response_arr); $j++) {
							if($decodeData[$i]['UslugaComplexMedService_id'] == $response_arr[$j]['UslugaComplexMedService_id']) {
								$delConnect = $this->execCommonSP('p_MedProductUslugaComplex_del', array(
									'MedProductUslugaComplex_id' => $response_arr[$j]['MedProductUslugaComplex_id'],
									'pmUser_id' => $data['pmUser_id'],
									'IsRemove' => '1'
								));
								unset($decodeData[$i]);
								$addRequest[$i] = $delConnect;
								break;
							}
						}
	                } else {
	                    $like = 0;
	                    for($j = 0; $j < count($response_arr); $j++) {
	                        if($decodeData[$i]['UslugaComplexMedService_id'] == $response_arr[$j]['UslugaComplexMedService_id']) {
	                            if($decodeData[$i]['MedProductCard_id'] == $response_arr[$j]['MedProductCard_id']) {
	                                unset($decodeData[$i]);
	                                $like++;
	                                break;
	                            } else {
	                                $delRequest[$i] = $this->execCommonSP('p_MedProductUslugaComplex_del', array(
	                                    'MedProductUslugaComplex_id' => $response_arr[$j]['MedProductUslugaComplex_id'],
	                                    'pmUser_id' => $data['pmUser_id'],
	                                    'IsRemove' => '1' //не удалять запись
	                                )); 
	                            }
	                        }
	                        else continue;
	                    } 
						if($like > 0) continue;
						
	                    $addRequest[$i] = $this->execCommonSP('p_MedProductUslugaComplex_ins', array(
	                        'MedProductCard_id' => $decodeData[$i]['MedProductCard_id'],
	                        'UslugaComplexMedService_id' => $decodeData[$i]['UslugaComplexMedService_id'],
	                        'pmUser_id' => $data['pmUser_id']
	                    ));
	                }
	            }
			}
			if(empty($addRequest)) return [array('Error_Message'=>'Нечего сохранять')];

	        return $addRequest;
	    }
	    catch(Exception $e) {
	        $this->rollbackTransaction();
	        return [array('Error_Message'=>$e->getMessage(), 'Error_Code'=>$e->getCode())];
	    }
	}
	  
	/**
	* Получаем данные о пациенте, МИ, PACS-сервере, направлении и услуге
	* @param array $data входные данные
	* @return array $responseMsg данные о пациенте, МИ, PACS-сервере, направлении и услуге
	*/
	public function getDirectionsData($data) {
		$responseMsg = array();
		$decodeData = json_decode($data['Data'], true);

		for($i = 0; $i < count($decodeData); $i++) {
			$medProductInfo = $this->getInfoToMedProduct($decodeData[$i]['personInfo']['MedProductCard_id']);
			$personInfo = $this->getPersonInfo($decodeData[$i]['personInfo']['Person_id'])[0];
			$uslugaParInfo = $this->getUslugaParInfo($decodeData[$i]['personInfo']['EvnDirection_id']);
			if(empty($medProductInfo) && empty($personInfo) && empty($uslugaParInfo)) {
				$responseMsg[$i] = "";
			}
			$responseMsg[$i] = array_merge($medProductInfo, $personInfo, $uslugaParInfo);
			$responseMsg[$i]['LpuSection_Name'] = $decodeData[$i]['personInfo']['LpuSection_Name'];
			$responseMsg[$i]['WorkListQueue_id'] = $decodeData[$i]['personInfo']['WorkListQueue_id'];
			$responseMsg[$i]['WorkListStatus_Code'] = $decodeData[$i]['personInfo']['WorkListStatus_Code'];
			$responseMsg[$i]['operation'] = $decodeData[$i]['operation'];
		}
		return $responseMsg;
	}

	/**
	* @param integer $lpu_id идентификатор МО
	* Получаем данные о PACS-серверах, связанных с заявками
	*/
	public function getLocalPacs($lpu_id) {
		$response_arr = $this->queryResult("
		SELECT DISTINCT LEP.PACS_ip_vip,
			LEP.PACS_aet,
			LEP.PACS_wado, 
			LEP.PACS_port
		FROM v_WorkListQueue WLQ with (NOLOCK)
			LEFT JOIN LpuEquipmentPacs LEP with (NOLOCK) on WLQ.LpuEquipmentPacs_id = LEP.LpuEquipmentPacs_id
		WHERE LEP.Lpu_id = :Lpu_id
		", [
			'Lpu_id' => $lpu_id
		]);
		if(empty($response_arr)) return false;
		return $response_arr;
	}

	/**
	* Получаем данные о направлении
	* @param integer $EvnDirection_id идентификатор направления
	* @return integer $EvnUslugaPar_id идентификатор параклинической услуги
	*/
	public function getEvnDirectionInfo($EvnDirection_id) {
		$response_arr = $this->queryResult("
		select (PS.PersonSurName_SurName + ' ' +
			PS.PersonFirName_FirName + ' ' +
			PS.PersonSecName_SecName) as Doctor_FIO,
			EUP.EvnUslugaPar_id, 
			UC.UslugaComplex_Code, 
			UC.UslugaComplex_Name,
			TTP.TimeTablePar_begTime
		from EvnDirection ED with (NOLOCK)
		left join persis.MedWorker MD with (NOLOCK) on ED.MedPersonal_id = MD.id
		left join PersonState PS with (NOLOCK) on MD.Person_id = PS.Person_id
		left join EvnUsluga EU with (NOLOCK) on EU.EvnDirection_id = ED.EvnDirection_id
		left join EvnUslugaPar EUP with (NOLOCK) on EUP.EvnUsluga_id = EU.EvnUsluga_id
		left join UslugaComplex UC with (NOLOCK) on EU.UslugaComplex_id = UC.UslugaComplex_id
		left join TimeTablePar TTP with (NOLOCK) on ED.TimeTablePar_id = TTP.TimeTablePar_id
		where ED.EvnDirection_id = :EvnDirection_id
		", [
			'EvnDirection_id' => $EvnDirection_id
		]);

		if(empty($response_arr)) return '';

		return $response_arr[0];
	}

	/**
	* Добавляем запись в WorkListQueue
	* @return array 
	*/
	function addRecordToDB($data) {
		$dataDecode = json_decode($data['Data'], true)['Data'];
		$MedProductCard = $this->getMedProductCardId($dataDecode[0]['EvnUslugaPar_id']);
		if(!empty($MedProductCard['Error_message'])) {
			return [array('Error_Message' => $MedProductCard['Error_message'])];
		}
		$LpuEquipmentPacs_id = $this->getInfoToMedProduct($MedProductCard['MedProductCard_id']);

		if($LpuEquipmentPacs_id === false) return [array('Error_Message'=>"К медицинскому изделию " . $MedProductCard['MedProductClass_Name'] . " не привязан PACS-сервер")];
		$response_arr = $this->queryResult("
			select EvnUslugaPar_id from v_WorkListQueue 
		");

		$check = false;
		for($i = 0; $i < count($response_arr); $i++) {
			if($response_arr[$i]['EvnUslugaPar_id'] == $dataDecode[0]['EvnUslugaPar_id']) {
				$check = true;
				break;
			}
		}
		
		if(!empty($check)) return [array('Error_Message'=>"Заявка уже создана")];
		
		$params = [
			'EvnUslugaPar_id'  => $dataDecode[0]['EvnUslugaPar_id'],
			'WorkListStatus_id' => 1,
			'MedProductCard_id' => $MedProductCard['MedProductCard_id'],
			'LpuEquipmentPacs_id' => $LpuEquipmentPacs_id['LpuEquipmentPacs_id'],
			'pmUser_id' => $data['pmUser_id'],
		];

		$response_arr = $this->execCommonSP("dbo.p_WorkListQueue_ins", $params);
		return [array('message' => "Напрваление добавлено в рабочий список")];
	}

	/**
	* Получаем наименование и идентификатор МИ
	* @param integer $EvnUslugaPar_id идентификатор параклинической услуги
	* @return string MedProductClass_Name наименование медицинского изделия
	* @return integer MedProductCard_id идентификатор медицинского изделия
	*/
	public function getMedProductCardId($EvnUslugaPar_id) {
		$response_arr = $this->queryResult("
		select MPUC.MedProductCard_id,
			MPC1.MedProductClass_Name
		from v_MedProductUslugaComplex MPUC with (NOLOCK)
			left join passport.MedProductCard MPC with (NOLOCK) on MPUC.MedProductCard_id = MPC.MedProductCard_id
			left join passport.MedProductClass MPC1 with (NOLOCK) on MPC.MedProductClass_id = MPC1.MedProductClass_id
			cross apply (select
			ED.MedService_id, EU.UslugaComplex_id
			from EvnUslugaPar EUP with (NOLOCK)
				left join EvnUsluga EU with (NOLOCK) on EUP.Evnusluga_id = EU.Evnusluga_id
				left join EvnDirection ED with (NOLOCK) on EU.EvnDirection_id = ED.EvnDirection_id
			where EvnUslugaPar_id = :EvnUslugaPar_id) x
		where UslugaComplexMedService_id = (
			select UslugaComplexMedService_id 
			from UslugaComplexMedService with (NOLOCK)
			where UslugaComplex_id = x.UslugaComplex_id and MedService_id = x.MedService_id)
		", [
			'EvnUslugaPar_id' => $EvnUslugaPar_id
		]);

		if(empty($response_arr)) {
			return array(
				'Error_message' => 'К данной услеге не привязано мед. изделие, работающее с рабочим списком'
			);
		}

		return $response_arr[0];
	}

	/**
	* Получаем список связей МИ и услуг на службе
	* @param integer UslugaComplexMedService_id  идентификатор услуги на службе
	* @return array
	*/
	public function getMedProductUslugaComplexList($UslugaComplexMedService_id = null) {
		$filter = "(1 = 1)";
		if(!empty($UslugaComplexMedService_id)) $filter .= " AND UslugaComplexMedService_id = :UslugaComplexMedService_id";
		
		$response_arr = $this->queryResult("
			SELECT
				MedProductUslugaComplex_id as \"MedProductUslugaComplex_id\",
				MedProductCard_id as \"MedProductCard_id\",
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			FROM dbo.v_MedProductUslugaComplex with (NOLOCK) WHERE $filter
		", [
			'UslugaComplexMedService_id' => $UslugaComplexMedService_id
		]);

		return $response_arr;
	}

	/**
	* Получаем информацию о медицинском изделии и подключенном к нему PACS-сервере
	* @param integer $MedProductCard_id идентификатор медицинского изделия
	* @return array|bool
	*/
	function getInfoToMedProduct($MedProductCard_id) {
		$response_arr = $this->queryResult("
		select MPC1.MedProductCard_AETitle,
			MPC1.LpuEquipmentPacs_id,
			MPC2.MedProductClass_Name,
			MPC1.MedProductCard_id,
			LEP.PACS_aet,
			LEP.PACS_ip_local,
			LEP.PACS_ip_vip
		from passport.MedProductCard MPC1 with (NOLOCK)
		left join LpuEquipmentPacs LEP with (NOLOCK) on MPC1.LpuEquipmentPacs_id = LEP.LpuEquipmentPacs_id
		left join passport.MedProductClass MPC2 with (NOLOCK) on MPC1.MedProductClass_id = MPC2.MedProductClass_id
		where MPC1.MedProductCard_id = :MedProductCard_id
		",[
			'MedProductCard_id' => $MedProductCard_id
		]);

		if(empty($response_arr)) return false;
		return $response_arr[0];
	}

	/**
	* Получаем ФИО врача направившего пациента на услугу
	* @param integer $EvnDirection_id идентификатор направления
	* @return array
	*/
	public function getUslugaParInfo($EvnDirection_id) {
		$response_arr = $this->queryResult("
		SELECT (PS.PersonSurName_SurName + ' ' +
			PS.PersonFirName_FirName + ' ' +
			PS.PersonSecName_SecName) as Doctor_FIO,
			EUP.EvnUslugaPar_id, 
			UC.UslugaComplex_Code, 
			UC.UslugaComplex_Name,
			TTP.TimeTablePar_begTime,
			ED.EvnDirection_id
		FROM EvnDirection ED with (NOLOCK)
			left join persis.MedWorker MD with (NOLOCK) on ED.MedPersonal_id = MD.id
			left join PersonState PS with (NOLOCK) on MD.Person_id = PS.Person_id
			left join EvnUsluga EU with (NOLOCK) on EU.EvnDirection_id = ED.EvnDirection_id
			left join EvnUslugaPar EUP with (NOLOCK) on EUP.EvnUsluga_id = EU.EvnUsluga_id
			left join UslugaComplex UC with (NOLOCK) on EU.UslugaComplex_id = UC.UslugaComplex_id
			left join TimeTablePar TTP with (NOLOCK) on ED.TimeTablePar_id = TTP.TimeTablePar_id
		WHERE ED.EvnDirection_id = :EvnDirection_id
		", [
			'EvnDirection_id' => $EvnDirection_id
		]);
		if (empty($response_arr)) return false;
		return $response_arr[0];
	}

	/**
	* Получаем алфавиты кириллицы и латиницы
	* @return array|bool массив сопоставлений кириллицы и латиницы
	*/
	public function getAlphabet() {
		$array_translite = $this->queryResult("
			SELECT
				WorkListTrans_id as \"WorkListTrans_id\",
				WorkListTrans_Code as \"WorkListTrans_Code\",
				WorkListTrans_Letter as \"WorkListTrans_Letter\",
				WorkListTrans_Latin as \"WorkListTrans_Latin\"
			FROM dbo.WorkListTrans (NOLOCK)
		");
		if(empty($array_translite)) return false;
		return $array_translite;
	}

	/**
	* Получаем информацию о пациенте
	* @param integer $Person_id идентификатор пациента
	* @return array информация о пациенте
	*/
	public function getPersonInfo($Person_id) {
		$personInfo = $this->queryResult("
			SELECT 
				(PS.PersonSurName_SurName + ' ' + PS.PersonFirName_FirName + ' ' + PS.PersonSecName_SecName) as Person_FIO,
				FORMAT(PS.PersonBirthDay_BirthDay, 'd', 'de-de') as Person_BirthDay,
				PS.Person_id,
				S.Sex_Code,
				A.Address_Address as Address
			FROM PersonState PS with (NOLOCK)
				left join dbo.Address A with (NOLOCK) on PS.PAddress_id = Address_id
				left join Sex S with (NOLOCK) on PS.Sex_id = S.Sex_id
			WHERE PS.Person_id = :Person_id
		", [
			'Person_id' => $Person_id
		]);

		if(empty($personInfo)) {
			return false;
		}

		return $personInfo;
	}

	/**
	* Добавляем связь параклинической услуги и исследования
	*/
	public function addLinkOnStudy($data) {
		$params = [
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Study_uid' => $data['Study_uid'],
			'Study_date' => $data['Study_date'],
			'Patient_Name' => $data['Patient_Name'],
			'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id'],
			'pmUser_id' => $data['pmUser_id']
		];
		$response_arr = $this->execCommonSP('p_EvnUslugaParAssociatedResearches_ins', $params);

		if(isset($response_arr[0]['Error_Message'])) 
			return $response_arr[0]['EvnUslugaParAssociatedResearches_id'];
		return $response_arr;
	}

	/**
	* Обновляем статус заявки в БД Промеда
	* @param array $personInfo информация о направлении пациента
	* @return array|bool
	*/
	public function updRecord($personInfo) {
		if($personInfo['WorkListStatus_id'] == 6) {
			$delRequest = $this->deleteRecordToDB($personInfo);
			if(empty($delRequest)) {
				$personInfo['WorkListStatus_id'] = 8;
			}
		}

		$params = [
			'WorkListQueue_id' => $personInfo['WorkListQueue_id'],
			'EvnUslugaPar_id'  => $personInfo['EvnUslugaPar_id'],
			'WorkListStatus_id' => $personInfo['WorkListStatus_id'],
			'MedProductCard_id' => $personInfo['MedProductCard_id'],
			'LpuEquipmentPacs_id' => $personInfo['LpuEquipmentPacs_id'],
			'pmUser_id' => $personInfo['pmUser_id'],
		];

		$response_arr = $this->execCommonSP("dbo.p_WorkListQueue_upd", $params);
		
		if(isset($response_arr[0]['Error_Message'])) 
			return false;

		return $response_arr;
	}
	/**
	* Удаляем заявку в БД Промеда
	* @param array $paramWL информация о заявке в рабочем списке
	* @return array|bool
	*/
	public function deleteRecordToDB($paramWL) {
		$params = [
			'WorkListQueue_id'  => $paramWL['WorkListQueue_id'],
			'IsRemove' => '1',
			'pmUser_id' => $paramWL['pmUser_id'],
		];

		$response_arr = $this->execCommonSP("dbo.p_WorkListQueue_del", $params);

		if(isset($response_arr[0]['Error_Message'])) 
			return false;

		return $response_arr;
	}

	/**
	* Отменяем заявку в MWL
	* @param array $data данные с формы
	* @return string message|Error_Message сообщение о выполнении операции
	*/
	public function cancelRecordToDB($data) {
		$decodeData = json_decode($data['Data'], true)['Data'][0];
		$LpuEquipmentPacs_id = $this->getInfoToMedProduct($decodeData['MedProductCard_id']);
		$statusWL = $decodeData['WorkListStatus_Code'];

		if($statusWL == 'sent' || $statusWL == 'changes' || $statusWL == 'result' || $statusWL == 'errorChan') {
			$params = array(
				'WorkListQueue_id' => $decodeData['WorkListQueue_id'],
				'EvnUslugaPar_id'  => $decodeData['EvnUslugaPar_id'],
				'WorkListStatus_id' => 2,
				'MedProductCard_id' => $decodeData['MedProductCard_id'],
				'LpuEquipmentPacs_id' => $LpuEquipmentPacs_id['LpuEquipmentPacs_id'],
				'pmUser_id' => $data['pmUser_id'],
			);
			$this->updRecord($params);

			return [array('message' => "Направление скоро будет удалено из рабочего списка")];
		} elseif ($statusWL == 'awaitingDisp' || $statusWL == 'errorDisp' ) {
			$params = array(
				'WorkListQueue_id' => $decodeData['WorkListQueue_id'],
				'pmUser_id' => $data['pmUser_id'],
			);
			$this->deleteRecordToDB($params);

			return [array('message' => "Направление удалено из рабочего списка")];
		} else if($statusWL == 'errorDel' || $statusWL == 'awaitingDel'){
			return [array('message' => "Ожидайте удаления напрвления из очереди, заявка уже создана")];
		} else 
			return [array('Error_Message'=> 'Невозможно удалить направление из очереди')];
	}

	/**
	* Измением МИ в напарвлении
	* @return array сообщение результата выполнения процедуры
	*/
	public function updRecordToDB($data) {
		$decodeData = json_decode($data['Data'], true)['Data'][0];
		
		if(empty($decodeData['MedProductCard_id'])) {
			return [['Error_Message' => "Не выбрано медицинское изделие"]];
		}
		
		$medProductInfo = $this->getInfoToMedProduct($decodeData['MedProductCard_id']);
		$dataForUpdate = array(
			'MedProductCard_id' => $decodeData['MedProductCard_id'],
			'LpuEquipmentPacs_id' => $medProductInfo['LpuEquipmentPacs_id'],
			'WorkListQueue_id' => $decodeData['WorkListQueue_id'],
			'EvnUslugaPar_id' => $decodeData['EvnUslugaPar_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		switch ($decodeData['WorkListStatus_Code']) {
			case 'awaitingDisp':
			case 'errorDisp':
				$dataForUpdate['WorkListStatus_id'] = 1;
				break;
			default:
				$dataForUpdate['WorkListStatus_id'] = 3;
				break;
		}

		$response = $this->updRecord($dataForUpdate);
		if(empty($response))
			return [array('Error_Message' => "Не возможно вынести изменения в заявку")];
		return [array('message' => "Направление успешно изменено")];
	}

	/**
	* Получаем рабочий список
	* @return array рабочий список|ошибка
	*/
	public function getWorkList($data) {
		$decodeData = '';

		if(!empty($data['Data'])) {
			$decodeData = json_decode($data['Data'], true);
		}

		$filter = "(1=1)";
		$params = array();

		if(!empty($decodeData['MedProductCard_id'])) {
			$filter .= " and WL.MedProductCard_id = :MedProductCard_id";
			$params = [
				'MedProductCard_id' => $decodeData['MedProductCard_id']
			];
		} else if(!empty($data['Lpu_id'])) {
			$filter .= " and LEP.Lpu_id = :Lpu_id";	
			$params = [
				'Lpu_id' => $data['Lpu_id']
			];
		}

		if(!empty($decodeData['startDate']) && !empty($decodeData['endDate'])) {
			$filter .= " and WL.WorkListQueue_insDT between :startDate AND :endDate";	
			$params['startDate'] = date('Y-m-d H:i:s', strtotime($decodeData['startDate']));
			$params['endDate'] = date('Y-m-d H:i:s', strtotime($decodeData['endDate'] . " + 23 Hours + 59 Minutes + 59 Seconds"));
		}
			

		$response_arr = $this->queryResult("
		SELECT 
			(PS.PersonSurName_SurName + ' ' + PS.PersonFirName_FirName + ' ' + isnull(PS.PersonSecName_SecName, '')) as Person_FIO,
			E.Person_id,
			convert(varchar(10),PS.PersonBirthDay_BirthDay, 104) as Person_BirthDay,
			ED.EvnDirection_Num as EvnDirection_Num,
			UC.UslugaComplex_Name as UslugaComplex_Name,
			UC.UslugaComplex_Code as UslugaComplex_Code,
			convert(varchar(10),E1.Evn_setDT, 104) as Usluga_setDate,
			isnull(convert(varchar(8), TTP.TimeTablePar_begTime), 'б/з') as Time_begTime,
			WLS.WorkListStatus_Name as WorkListStatus_Name,
			WLS.WorkListStatus_Code as WorkListStatus_Code, 
			LS.LpuSection_Name,
			WL.MedProductCard_id,
			ED.EvnDirection_id,
			WL.WorkListQueue_id, 
			WL.EvnUslugaPar_id, 
			WL.LpuEquipmentPacs_id
		FROM v_WorkListQueue WL with (NOLOCK)
			left join dbo.EvnUslugaPar EUP with (NOLOCK) on WL.EvnUslugaPar_id = EUP.EvnUslugaPar_id
			left join dbo.EvnUsluga EU with (NOLOCK) on EUP.EvnUsluga_id = EU.EvnUsluga_id
			left join dbo.Evn E with (NOLOCK) on EU.Evn_id = E.Evn_id 
			left join dbo.PersonState PS with (NOLOCK) on E.Person_id = PS.Person_id
			left join dbo.EvnDirection ED with (NOLOCK) on EU.EvnDirection_id = ED.EvnDirection_id
			left join dbo.LpuSection LS with (NOLOCK) on ED.LpuSection_id = LS.LpuSection_id
			left join dbo.UslugaComplex UC with (NOLOCK) on EU.UslugaComplex_id = UC.UslugaComplex_id
			left join dbo.TimeTablePar TTP with (NOLOCK) on ED.TimeTablePar_id = TTP.TimeTablePar_id
			left join dbo.LpuEquipmentPacs LEP with (NOLOCK) on WL.LpuEquipmentPacs_id = LEP.LpuEquipmentPacs_id
			left join dbo.WorkListStatus WLS with (NOLOCK) on WL.WorkListStatus_id = WLS.WorkListStatus_id
			left join dbo.Evn E1 with (NOLOCK) on ED.Evn_id = E1.Evn_id
		WHERE WLS.WorkListStatus_Code <> 'result' and $filter
			", $params);

		if(!is_array($response_arr)) return [array('Error_Message'=>$e->getMessage(), 'Error_Code'=>$e->getCode())];
		return $response_arr;
	}

	/**
	* Получаем идентификатор услуги на службе
	* @param integer MedService_id идентификатор медслужбы
	* @param string UslugaComplex_Name наименование услуги
	* @return array ['UslugaComplexMedService_id'] идентификатор услуги на службе
	*/
	public function getUslugaComplexMedService($MedService_id, $UslugaComplex_Name) {
		$response_arr = $this->queryResult("
			SELECT UslugaComplexMedService_id
			FROM UslugaComplexMedService UCMS with (NOLOCK)
			left join UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
			WHERE UCMS.MedService_id = :MedService_id and UC.UslugaComplex_Name = :UslugaComplex_Name
		", [
			'MedService_id' => $MedService_id,
			'UslugaComplex_Name' => $UslugaComplex_Name
		]);
		return $response_arr;
	}

	/**
	* Получаем список не выполненных направлений на текущую дату
	* @param json $data 
	* @return json
	*/
	public function getDirections($data) {
		$decodeData = json_decode($data['Data'], true);
		if(empty($decodeData['MedProductCard_id'])) $decodeData['MedProductCard_id'] = 0;
		$filter = "ed.MedService_id = :MedService_id  and e1.Evn_setDT >= :Date_Todays and mpuc.MedProductCard_id = :MedProductCard_id";
		$response_arr = $this->queryResult("
			select (ps.PersonSurName_SurName + ' ' + ps.PersonFirName_FirName + ' ' + isnull(ps.PersonSecName_SecName, '')) as Person_FIO,
				format(ps.PersonBirthDay_BirthDay, 'dd.MM.yyyy') as Person_BirthDay,
				e.Person_id,
				uc.UslugaComplex_Name,
				ed.EvnDirection_Num,
				format(e1.Evn_setDT, 'dd.MM.yyyy') as EvnDirection_setDT,
				isnull(convert(varchar(8), ttp.TimeTablePar_begTime), 'б/з') as Time_begTime,
				eup.EvnUslugaPar_id,
				(ps1.PersonSurName_SurName + ' ' + ps1.PersonFirName_FirName + ' ' + ps1.PersonSecName_SecName) as Operator,
				ls.LpuSection_Name, l.Lpu_Nick as Lpu_Name
			from EvnDirection ed with (NOLOCK)
				left join Evn e1 with (NOLOCK) on ed.Evn_id = e1.Evn_id
				left join EvnUsluga eu with (NOLOCK) on eu.EvnDirection_id = ed.EvnDirection_id
				left join Evn e with (NOLOCK) on eu.Evn_id = e.Evn_id
				left join PersonState ps with (NOLOCK) on e.Person_id = ps.Person_id 
				left join UslugaComplex uc with (NOLOCK) on eu.UslugaComplex_id = uc.UslugaComplex_id
				left join UslugaComplexMedService ucms with (NOLOCK) on ucms.UslugaComplex_id = uc.UslugaComplex_id
				left join dbo.TimeTablePar ttp with (NOLOCK) on ed.TimeTablePar_id = ttp.TimeTablePar_id
				left join v_MedProductUslugaComplex mpuc with (NOLOCK) on mpuc.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				left join passport.MedProductCard mpc with (NOLOCK) on mpuc.MedProductCard_id = mpc.MedProductCard_id
				left join EvnUslugaPar eup with (NOLOCK) on eup.EvnUsluga_id = eu.EvnUsluga_id
				left join persis.MedWorker mw with (NOLOCK) on ed.MedPersonal_id = mw.id
				left join PersonState ps1 with (NOLOCK) on mw.Person_id = ps1.Person_id
				left join LpuSection ls with (NOLOCK) on ed.LpuSection_id = ls.LpuSection_id
				left join v_Lpu l with (NOLOCK) on ed.Lpu_sid = l.Lpu_id
			where ed.DirFailType_id is null /*and ed.EvnDirection_IsReceive is null */and $filter 
		", [
			'MedService_id' => $decodeData['MedService_id'],
			'Date_Todays' => $data['Todays_date'],
			'MedProductCard_id' => $decodeData['MedProductCard_id']
		]);

		return $response_arr;
	}

	/**
	* Проверяем, есть ли напарвление в очереди РС ПроМеда, если есть то удаляем
	* @param json EvnDirection_id идентификатор направления
	* @return string Сообщение о результатах проверки и статусе удаления из очереди из РС ПроМеда
	*/
	public function checkDirectionInWLQ($data) {
		preg_match('/\d+/', $data['EvnDirection_id'], $EvnDirection_id);
		if(empty($EvnDirection_id[0]))
			return false;
		$checkResult = $this->queryResult("
			SELECT wlq.EvnUslugaPar_id,
				wls.WorkListStatus_Code,
				wlq.LpuEquipmentPacs_id,
				wlq.WorkListQueue_id,
				wlq.MedProductCard_id
			FROM EvnDirection ed with (NOLOCK)
				left join EvnUsluga eu with (NOLOCK) on eu.EvnDirection_id = ed.EvnDirection_id
				left join EvnUslugaPar eup with (NOLOCK) on eup.EvnUsluga_id = eu.EvnUsluga_id
				left join v_WorkListQueue wlq with (NOLOCK) on wlq.EvnUslugaPar_id = eup.EvnUslugaPar_id
				left join v_WorkListStatus wls with (NOLOCK) on wlq.WorkListStatus_id = wls.WorkListStatus_id
			WHERE ed.EvnDirection_id = :EvnDirection_id
		", [
			'EvnDirection_id' => $EvnDirection_id[0]
		]);

		if(!empty($checkResult[0]['EvnUslugaPar_id'])) {
			$params['Data'] = [array(
				'WorkListStatus_Code' => $checkResult[0]['WorkListStatus_Code'],
				'EvnUslugaPar_id' => $checkResult[0]['EvnUslugaPar_id'],
				'LpuEquipmentPacs_id' => $checkResult[0]['LpuEquipmentPacs_id'],
				'MedProductCard_id' => $checkResult[0]['MedProductCard_id'],
				'WorkListQueue_id' => $checkResult[0]['WorkListQueue_id']
			)];
			$data['Data'] = json_encode($params);
			$response = $this->cancelRecordToDB($data);
			return $response;
		}

		return [array('message' => "")];
	}

	/**
	* Получаем список МИ на службе работающих с рабочим списком
	* @param json MedService_id идентификатор службы 
	* @param json MedProductCard_IsWorkList Признак работы с рабочим списком
	* @return boolean наличие МИ на службе с признаком "работа с рабочим списком"
	*/
	public function getMedProductCardIsWL($data) {
		$isHidden = false;
		$addToWorkList = false;
		$MedProductCard_isCombo = !empty($data['MedProductCard_isCombo']);
		$select = '*';
		$filter = ' ';
		$params = array(
			'MedProductCard_IsWorkList' => $data['MedProductCard_IsWorkList']
		);
		
		if(!empty($data['EvnDirection_id']) && empty($data['MedService_id'])) {
			$addToWorkList = true;

			$response_arr = $this->queryResult("
				select eu.UslugaComplex_id, ed.MedService_id, eup.EvnUslugaPar_id
				from EvnUslugaPar eup with (NOLOCK)
					left join EvnUsluga eu with (NOLOCK) on eup.EvnUsluga_id = eu.EvnUsluga_id
					left join EvnDirection ed with (NOLOCK) on eu.EvnDirection_id = ed.EvnDirection_id
				where eu.EvnDirection_id = :EvnDirection_id", 
				[
					'EvnDirection_id' => $data['EvnDirection_id']
				]
			);

			if(empty($response_arr)) {
				return false;
			}

			$data['UslugaComplex_id'] = $response_arr[0]['UslugaComplex_id'];
			$data['MedService_id'] = $response_arr[0]['MedService_id'];
			$data['EvnUslugaPar_id'] = $response_arr[0]['EvnUslugaPar_id'];

			$filter .= 'and ucms.UslugaComplex_id = :UslugaComplex_id';
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if($MedProductCard_isCombo) {
			$select = 'distinct mpc.MedProductCard_id, mpc2.MedProductClass_Name';
		}

		$params['MedService_id'] = $data['MedService_id'];

		$response_arr = $this->queryResult("
			select {$select}
			from v_Resource r  with (NOLOCK)
				left join passport.v_MedProductCardResource mpcr with (NOLOCK) on r.Resource_id = mpcr.Resource_id
				left join passport.MedProductCard mpc with (NOLOCK) on mpcr.MedProductCard_id = mpc.MedProductCard_id
				left join passport.MedProductClass mpc2 with (NOLOCK) on mpc.MedProductClass_id = mpc2.MedProductClass_id
				left join v_MedProductUslugaComplex mpuc with (NOLOCK) on mpuc.MedProductCard_id = mpc.MedProductCard_id
				left join UslugaComplexMedService ucms with (NOLOCK) on mpuc.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			where r.MedService_id = :MedService_id and mpc.MedProductCard_IsWorkList = :MedProductCard_IsWorkList {$filter}", 
			$params
		);
		if($addToWorkList) {
			if(count($response_arr) >= 1) {
				$EvnUslugaPar_id = array(
					'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
				);
				$paramsForAdd['Data'] = array();
				array_push($paramsForAdd['Data'], $EvnUslugaPar_id);
				$data['Data'] = json_encode($paramsForAdd);
				$newRecordWL = $this->addRecordToDB($data);

				return $newRecordWL;
			} else {
				return false;
			}
		} else if($MedProductCard_isCombo){
			if(empty($response_arr)) return [array('Error_Message' => 'К службе не привязано медицинское изделие, работающее с РС')];
			return $response_arr;
		} else {
			if(count($response_arr) >= 1) {
				$isHidden = true;
			}

			return [array('btn_isHidden' => $isHidden)];
		}
	}

	/**
	 * Проверяем EvnUslugaPar в очереди рабочего списка
	 * @param bigint EvnUslugaPar_id идентификатор услуги
	 * @return boolean
	 */
	public function checkIsInWorkListQueue($data) {
	 	$check = $this->queryResult("
			SELECT count(*) as inQueue
			FROM v_WorkListQueue with (NOLOCK)
			WHERE EvnUslugaPar_id = :Evn_id  
		", [
			'Evn_id' => $data['EvnUslugaPar_id']
		]);	
		return $check;
	}
 }		