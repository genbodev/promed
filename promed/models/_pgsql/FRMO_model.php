<?php	defined('BASEPATH') or die ('No direct script access allowed');
		 /**
		  * FRMO_model - модель для работы с федеральным регистром медицинских организаций
		  *
		  * PromedWeb - The New Generation of Medical Statistic Software
		  * http://swan.perm.ru/PromedWeb
		  *
		  *
		  * @package			Common
		  * @access			public
		  * @copyright		Copyright (c) 2018 Swan Ltd.
		  * @author			Bykov Stanislav (savage@swan.perm.ru)
		  * @version			17.12.2018
		  */

		 class FRMO_model extends SwPgModel {
			 protected $_files_MO = array();
			 protected $_files_MO_Building = array();
			 protected $_files_MO_Depart = array();
			 protected $_files_MO_Staff = array();
			 protected $_MO_Data = array();
			 protected $_MO_Building_Data = array();
			 protected $_MO_Depart_Data = array();
			 protected $_MO_Subdivision_Data = array();
			 protected $_MO_DepartLab_Data = array();
			 protected $_MO_Staff_Data = array();

			 private $_Lpu_id;

			 /**
			  * Конструктор
			  */
			 function __construct() {
				 parent::__construct();
			 }

			 /**
			  * Задание на обработку данных от ФРМО
			  */
			 public function parseFRMOData($data) {
				 try {
					 if ( !defined('FRMO_DATA_PATH') ) {
						 throw new Exception('Не указан путь до XML-файлов (FRMO_DATA_PATH)');
					 }

					 if ( !is_dir(FRMO_DATA_PATH) ) {
						 throw new Exception('Отсутствует папка с XML-файлами (FRMO_DATA_PATH)');
					 }

					 // 1. Ищем файлы для обработки
					 // Маска файла МО: MO<идентификатор>_mo.read.xml
					 $fileList = scandir(FRMO_DATA_PATH);

					 foreach ( $fileList as $file ) {
						 if ( in_array($file, array('.', '..')) ) {
							 continue;
						 }
						 else if ( preg_match("/^MO(\d+)_mo\.read\.xml$/", $file, $matches) ) {
							 $this->_files_MO[$matches[1]] = $file;
						 }
						 else if ( preg_match("/^MO(\d+)_mo_building\.list\.xml$/", $file, $matches) ) {
							 $this->_files_MO_Building[$matches[1]] = $file;
						 }
						 else if ( preg_match("/^MO(\d+)_mo_depart\.list\.xml$/", $file, $matches) ) {
							 $this->_files_MO_Depart[$matches[1]] = $file;
						 }
						 else if ( preg_match("/^MO(\d+)_mo_staff\.list\.xml$/", $file, $matches) ) {
							 $this->_files_MO_Staff[$matches[1]] = $file;
						 }
					 }

					 libxml_use_internal_errors(true);

					 // 2. Запускаем обработку мед. организаций
					 foreach ( $this->_files_MO as $Lpu_id => $xmlfile ) {
						 $this->_Lpu_id = $Lpu_id;

						 $xml = new SimpleXMLElement(file_get_contents(FRMO_DATA_PATH . $xmlfile));

						 foreach ( libxml_get_errors() as $error ) {
							 throw new Exception('Ошибка XML: ' . $error);
						 }

						 libxml_clear_errors();

						 $MO_Data = array(
							 //'XMLData' => file_get_contents(FRMO_DATA_PATH . $xmlfile),
							 'Lpu_id' => $Lpu_id,
							 'oid' => (property_exists($xml, 'oid') ? $xml->oid->__toString() : null),
							 'nameFull' => (property_exists($xml, 'nameFull') ? $xml->nameFull->__toString() : null),
							 'nameShort' => (property_exists($xml, 'nameShort') ? $xml->nameShort->__toString() : null),
							 'inn' => (property_exists($xml, 'inn') ? $xml->inn->__toString() : null),
							 'kpp' => (property_exists($xml, 'kpp') ? $xml->kpp->__toString() : null),
							 'ogrn' => (property_exists($xml, 'ogrn') ? $xml->ogrn->__toString() : null),
							 'organizationType' => (property_exists($xml, 'organizationType') ? $xml->organizationType->__toString() : null),
							 'stateOrganization' => (property_exists($xml, 'stateOrganization') ? ($xml->stateOrganization->__toString() == 'true' ? 1 : 0) : null),
							 'moDeptId' => (property_exists($xml, 'moDeptId') && !empty($xml->moDeptId['id']) ? $xml->moDeptId['id']->__toString() : null),
							 'okopfId' => (property_exists($xml, 'okopfId') && !empty($xml->okopfId['id']) ? $xml->okopfId['id']->__toString() : null),
							 'postIndex' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'postIndex') ? $xml->moAddress->postIndex->__toString() : null),
							 'aoidArea' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'aoidArea') ? $xml->moAddress->address->aoidArea->__toString() : null),
							 'aoidStreet' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'aoidStreet') ? $xml->moAddress->address->aoidStreet->__toString() : null),
							 'houseid' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'houseid') ? $xml->moAddress->address->houseid->__toString() : null),
							 'region' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'region') && !empty($xml->moAddress->address->region['id']) ? $xml->moAddress->address->region['id']->__toString() : null),
							 'areaName' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'areaName') ? $xml->moAddress->address->areaName->__toString() : null),
							 'prefixArea' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'prefixArea') ? $xml->moAddress->address->prefixArea->__toString() : null),
							 'streetName' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'streetName') ? $xml->moAddress->address->streetName->__toString() : null),
							 'prefixStreet' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'prefixStreet') ? $xml->moAddress->address->prefixStreet->__toString() : null),
							 'house' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'address') && property_exists($xml->moAddress->address, 'house') ? $xml->moAddress->address->house->__toString() : null),
							 'cadastralNumber' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'cadastralNumber') ? $xml->moAddress->cadastralNumber->__toString() : null),
							 'latitude' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'latitude') ? $xml->moAddress->latitude->__toString() : null),
							 'longtitude' => (property_exists($xml, 'moAddress') && property_exists($xml->moAddress, 'longtitude') ? $xml->moAddress->longtitude->__toString() : null),
							 'regionId' => (property_exists($xml, 'regionId') && !empty($xml->regionId['id']) ? $xml->regionId['id']->__toString() : null),
							 'medicalSubjectId' => (property_exists($xml, 'medicalSubjectId') && !empty($xml->medicalSubjectId['id']) ? $xml->medicalSubjectId['id']->__toString() : null),
							 'oldOid' => (property_exists($xml, 'oldOid') ? $xml->oldOid->__toString() : null),
							 'createDate' => (property_exists($xml, 'createDate') ? $xml->createDate->__toString() : null),
							 'modifyDate' => (property_exists($xml, 'modifyDate') ? $xml->modifyDate->__toString() : null),
							 'moAgencyKindId' => (property_exists($xml, 'moAgencyKindId') && !empty($xml->moAgencyKindId['id']) ? $xml->moAgencyKindId['id']->__toString() : null),
							 'moAgencyProfileId' => (property_exists($xml, 'moAgencyProfileId') && !empty($xml->moAgencyProfileId['id']) ? $xml->moAgencyProfileId['id']->__toString() : null),
							 'moTerritoryId' => (property_exists($xml, 'moTerritoryId') && !empty($xml->moTerritoryId['id']) ? $xml->moTerritoryId['id']->__toString() : null),
							 'moLevelId' => (property_exists($xml, 'moLevelId') && !empty($xml->moLevelId['id']) ? $xml->moLevelId['id']->__toString() : null),
						 );

						 if ( empty($MO_Data['createDate']) ) $MO_Data['createDate'] = null;
						 if ( empty($MO_Data['modifyDate']) ) $MO_Data['modifyDate'] = null;

						 $fieldsList = array_keys($MO_Data);
						 $updateParamsList = array();

						 foreach ( $fieldsList as $field ) {
							 $updateParamsList[] = $field . " = :" . $field;
						 }

						 $MO_Data['id'] = $this->getFirstResultFromQuery("select id as \"id\"
																 from tmp.FRMOData_MO
																where Lpu_id = :Lpu_id and updDT is null
																limit 1"
																		 , $MO_Data, true);

						 if ( !empty($MO_Data['id']) )
						 {
							 $query = "
									update tmp.FRMOData_MO
									set
										" . implode(", ", $updateParamsList) . ",
										insDT = dbo.tzGetDate()
									where id = :id
									 RETURNING query_id into QueryId;
								";
						 }
						 else
						 {
							 $query = "
									insert into tmp.FRMOData_MO (" . implode(", ", $fieldsList) . ", insDT)
									values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())

									RETURNING query_id into QueryId;
									";
						 }


						 $resp = $this->getFirstRowFromQuery("
									CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
									LANGUAGE 'plpgsql'

									AS $$
									DECLARE

									BEGIN
										{$query}
										exception
											when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
									END;
									$$;

									select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
									from pg_temp.exp_Query();
									", $MO_Data);




						 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
							 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_Data));
						 }
						 else if ( !empty($resp['Error_Msg']) ) {
							 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
						 }

						 $MO_Data['id'] = $resp['id'];

						 $this->_MO_Data[] = $MO_Data;
					 }

					 // 3. Запускаем обработку зданий
					 foreach ( $this->_files_MO_Building as $Lpu_id => $xmlfile ) {
						 $this->_Lpu_id = $Lpu_id;

						 $xml = new SimpleXMLElement(file_get_contents(FRMO_DATA_PATH . $xmlfile));

						 foreach ( libxml_get_errors() as $error ) {
							 throw new Exception('Ошибка XML: ' . $error);
						 }

						 libxml_clear_errors();

						 foreach ( $xml->building as $building ) {
							 $MO_Building_Data = array(
								 //'XMLData' => file_get_contents(FRMO_DATA_PATH . $xmlfile),
								 'Lpu_id' => $this->_Lpu_id,
								 'FRMO_id' => (property_exists($building, 'id') ? $building->id->__toString() : null),
								 'buildName' => (property_exists($building, 'buildName') ? $building->buildName->__toString() : null),
								 'buildYear' => (property_exists($building, 'buildYear') ? $building->buildYear->__toString() : null),
								 'floorCount' => (property_exists($building, 'floorCount') ? $building->floorCount->__toString() : null),
								 'hasTrouble' => (property_exists($building, 'hasTrouble') ? ($building->hasTrouble->__toString() == 'true' ? 1 : 0) : null),
								 'postIndex' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'postIndex') ? $building->buildingAddress->postIndex->__toString() : null),
								 'aoidArea' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'aoidArea') ? $building->buildingAddress->address->aoidArea->__toString() : null),
								 'aoidStreet' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'aoidStreet') ? $building->buildingAddress->address->aoidStreet->__toString() : null),
								 'houseid' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'houseid') ? $building->buildingAddress->address->houseid->__toString() : null),
								 'region' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'region') && !empty($building->buildingAddress->address->region['id']) ? $building->buildingAddress->address->region['id']->__toString() : null),
								 'areaName' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'areaName') ? $building->buildingAddress->address->areaName->__toString() : null),
								 'prefixArea' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'prefixArea') ? $building->buildingAddress->address->prefixArea->__toString() : null),
								 'streetName' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'streetName') ? $building->buildingAddress->address->streetName->__toString() : null),
								 'prefixStreet' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'prefixStreet') ? $building->buildingAddress->address->prefixStreet->__toString() : null),
								 'house' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'address') && property_exists($building->buildingAddress->address, 'house') ? $building->buildingAddress->address->house->__toString() : null),
								 'latitude' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'latitude') ? $building->buildingAddress->latitude->__toString() : null),
								 'longtitude' => (property_exists($building, 'buildingAddress') && property_exists($building->buildingAddress, 'longtitude') ? $building->buildingAddress->longtitude->__toString() : null),
								 'createDate' => (property_exists($building, 'createDate') ? $building->createDate->__toString() : null),
								 'modifyDate' => (property_exists($building, 'modifyDate') ? $building->modifyDate->__toString() : null),
							 );

							 if ( empty($MO_Building_Data['createDate']) ) $MO_Building_Data['createDate'] = null;
							 if ( empty($MO_Building_Data['modifyDate']) ) $MO_Building_Data['modifyDate'] = null;

							 $fieldsList = array_keys($MO_Building_Data);
							 $updateParamsList = array();

							 foreach ( $fieldsList as $field ) {
								 $updateParamsList[] = $field . " = :" . $field;
							 }

							 $MO_Building_Data['id'] = $this->getFirstResultFromQuery("select id as \"id\"
																						from tmp.FRMOData_MO_Building
																						where Lpu_id = :Lpu_id and FRMO_id = :FRMO_id and updDT is null
																						limit 1", $MO_Building_Data, true);

							 if ( !empty($MO_Building_Data['id']) ) {
								 $query = "
										update tmp.FRMOData_MO_Building
										set
											" . implode(", ", $updateParamsList) . ",
											insDT = dbo.tzGetDate()
										where id = :id
										RETURNING query_id into QueryId;
										";
							 }
							 else {
								 $query = "
										insert into tmp.FRMOData_MO_Building (" . implode(", ", $fieldsList) . ", insDT)
										values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())

										RETURNING query_id into QueryId;
										";
							 }

							 $resp = $this->getFirstRowFromQuery("
											   CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
												LANGUAGE 'plpgsql'

												AS $$
												DECLARE

												BEGIN
													{$query}
													exception
														when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
												END;
												$$;

												select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
												from pg_temp.exp_Query();
											", $MO_Building_Data);

							 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
								 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_Building_Data));
							 }
							 else if ( !empty($resp['Error_Msg']) ) {
								 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
							 }

							 $MO_Building_Data['id'] = $resp['id'];

							 $this->_MO_Building_Data[] = $MO_Building_Data;
						 }
					 }

					 // 4. Запускаем обработку групп отделений и отделений
					 foreach ( $this->_files_MO_Depart as $Lpu_id => $xmlfile ) {
						 $this->_Lpu_id = $Lpu_id;

						 $xml = new SimpleXMLElement(file_get_contents(FRMO_DATA_PATH . $xmlfile));

						 foreach ( libxml_get_errors() as $error ) {
							 throw new Exception('Ошибка XML: ' . $error);
						 }

						 libxml_clear_errors();

						 foreach ( $xml->depart as $depart  ) {
							 $MO_Depart_Data = array(
								 //'XMLData' => file_get_contents(FRMO_DATA_PATH . $xmlfile),
								 'Lpu_id' => $this->_Lpu_id,
								 'FRMO_id' => (property_exists($depart, 'id') ? $depart->id->__toString() : null),
								 'departName' => (property_exists($depart, 'departName') ? $depart->departName->__toString() : null),
								 'phones' => (property_exists($depart, 'phones') ? $depart->phones->__toString() : null),
								 'departKindId' => (property_exists($depart, 'departKindId') && !empty($depart->departKindId['id']) ? $depart->departKindId['id']->__toString() : null),
								 'departTypeId' => (property_exists($depart, 'departTypeId') && !empty($depart->departTypeId['id']) ? $depart->departTypeId['id']->__toString() : null),
								 'separateDepart' => (property_exists($depart, 'separateDepart') ? ($depart->separateDepart->__toString() == 'true' ? 1 : 0) : null),
								 'mainBuildingId' => (property_exists($depart, 'mainBuildingId') ? $depart->mainBuildingId->__toString() : null),
								 'ambulance' => (property_exists($depart, 'departHospital') && property_exists($depart->departHospital, 'ambulance') ? ($depart->departHospital->ambulance->__toString() == 'true' ? 1 : 0) : null),
								 'hospitalModeId' => (property_exists($depart, 'departHospital') && property_exists($depart->departHospital, 'hospitalModeId') && !empty($depart->departHospital->hospitalModeId['id']) ? $depart->departHospital->hospitalModeId['id']->__toString() : null),
								 'patientAttached' => (property_exists($depart, 'departReg') && property_exists($depart->departReg, 'patientAttached') ? $depart->departReg->patientAttached->__toString() : null),
								 'childAttached' => (property_exists($depart, 'departReg') && property_exists($depart->departReg, 'childAttached') ? $depart->departReg->childAttached->__toString() : null),
								 'visitPerShift' => (property_exists($depart, 'departReg') && property_exists($depart->departReg, 'visitPerShift') ? $depart->departReg->visitPerShift->__toString() : null),
								 'visitHome' => (property_exists($depart, 'departReg') && property_exists($depart->departReg, 'visitHome') ? ($depart->departReg->visitHome->__toString() == 'true' ? 1 : 0) : null),
								 'createDate' => (property_exists($depart, 'createDate') ? $depart->createDate->__toString() : null),
								 'modifyDate' => (property_exists($depart, 'modifyDate') ? $depart->modifyDate->__toString() : null),
								 'oid' => (property_exists($depart, 'oid') ? $depart->oid->__toString() : null),
							 );

							 $fieldsList = array_keys($MO_Depart_Data);
							 $updateParamsList = array();

							 foreach ( $fieldsList as $field ) {
								 $updateParamsList[] = $field . " = :" . $field;
							 }

							 $MO_Depart_Data['id'] = $this->getFirstResultFromQuery('select top 1 id from tmp.FRMOData_MO_Depart with (nolock) where Lpu_id = :Lpu_id and oid = :oid and updDT is null', $MO_Depart_Data, true);

							 if ( !empty($MO_Depart_Data['id']) )
							 {
								 $query = "
											update tmp.FRMOData_MO_Depart
											set
												" . implode(", ", $updateParamsList) . ",
												insDT = dbo.tzGetDate()
											where id = :id
											RETURNING query_id into QueryId;
										";
							 }
							 else
							 {
								 $query = "
											insert into tmp.FRMOData_MO_Depart (" . implode(", ", $fieldsList) . ", insDT)
											values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())

											RETURNING query_id into QueryId;
										";
							 }

							 $resp = $this->getFirstRowFromQuery("
										  CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
											LANGUAGE 'plpgsql'

											AS $$
											DECLARE

											BEGIN
												{$query}
												exception
													when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
											END;
											$$;

											select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
											from pg_temp.exp_Query();
										", $MO_Depart_Data);

							 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
								 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_Depart_Data));
							 }
							 else if ( !empty($resp['Error_Msg']) ) {
								 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
							 }

							 $MO_Depart_Data['id'] = $resp['id'];

							 $this->_MO_Depart_Data[] = $MO_Depart_Data;

							 if ( property_exists($depart, 'departHospital') && property_exists($depart->departHospital, 'hospitalSubdivisions') ) {
								 foreach ( $depart->departHospital->hospitalSubdivisions->hospitalSubdivision as $hospitalSubdivision ) {
									 $MO_Subdivision_Data = array(
										 'Lpu_id' => $this->_Lpu_id,
										 'Depart_id' => $MO_Depart_Data['id'],
										 'FRMO_id' => (property_exists($hospitalSubdivision, 'id') ? $hospitalSubdivision->id->__toString() : null),
										 'subdivisionName' => (property_exists($hospitalSubdivision, 'subdivisionName') ? $hospitalSubdivision->subdivisionName->__toString() : null),
										 'subdivisionId' => (property_exists($hospitalSubdivision, 'subdivisionId') && !empty($hospitalSubdivision->subdivisionId['id']) ? $hospitalSubdivision->subdivisionId['id']->__toString() : null),
										 'buildingId' => (property_exists($hospitalSubdivision, 'buildingId') ? $hospitalSubdivision->buildingId->__toString() : null),
										 'oid' => (property_exists($hospitalSubdivision, 'oid') ? $hospitalSubdivision->oid->__toString() : null),
									 );

									 $fieldsList = array_keys($MO_Subdivision_Data);
									 $updateParamsList = array();

									 foreach ( $fieldsList as $field ) {
										 $updateParamsList[] = $field . " = :" . $field;
									 }

									 $MO_Subdivision_Data['id'] = $this->getFirstResultFromQuery('select top 1 id from tmp.FRMOData_MO_Subdivision with (nolock) where Lpu_id = :Lpu_id and oid = :oid and updDT is null', $MO_Subdivision_Data, true);

									 if ( !empty($MO_Subdivision_Data['id']) )
									 {
										 $query = "
													update tmp.FRMOData_MO_Subdivision
													set
														" . implode(", ", $updateParamsList) . ",
														insDT = dbo.tzGetDate()
													where id = :id
													RETURNING query_id into QueryId;
												";
									 }
									 else
									 {
										 $query = "
													insert into tmp.FRMOData_MO_Subdivision (" . implode(", ", $fieldsList) . ", insDT)
													values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())

													RETURNING query_id into QueryId;
												";
									 }

									 $resp = $this->getFirstRowFromQuery("
													 CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
														LANGUAGE 'plpgsql'

														AS $$
														DECLARE

														BEGIN
															{$query}
															exception
																when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
														END;
														$$;

														select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
														from pg_temp.exp_Query();
												", $MO_Subdivision_Data);

									 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
										 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_Subdivision_Data));
									 }
									 else if ( !empty($resp['Error_Msg']) ) {
										 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
									 }

									 $MO_Subdivision_Data['id'] = $resp['id'];

									 $this->_MO_Subdivision_Data[] = $MO_Subdivision_Data;

									 $this->db->query("delete
												from tmp.FRMOData_MO_HospitalSubdivisionBed
												where Subdivision_id = :Subdivision_id and updDT is null"
												 , array('Subdivision_id' => $MO_Subdivision_Data['id']));

									 if ( property_exists($hospitalSubdivision, 'hospitalSubdivisionBeds') ) {
										 foreach ( $hospitalSubdivision->hospitalSubdivisionBeds->hospitalSubdivisionBed as $hospitalSubdivisionBed ) {
											 $MO_HospitalSubdivisionBed_Data = array(
												 'Lpu_id' => $this->_Lpu_id,
												 'Subdivision_id' => $MO_Subdivision_Data['id'],
												 'bedProfileId' => (property_exists($hospitalSubdivisionBed, 'bedProfileId') && !empty($hospitalSubdivisionBed->bedProfileId['id']) ? $hospitalSubdivisionBed->bedProfileId['id']->__toString() : null),
												 'bedCount' => (property_exists($hospitalSubdivisionBed, 'bedCount') ? $hospitalSubdivisionBed->bedCount->__toString() : null),
											 );

											 $fieldsList = array_keys($MO_HospitalSubdivisionBed_Data);

											 $query = "
														CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
														LANGUAGE 'plpgsql'

														AS $$
														DECLARE

														BEGIN
																insert into tmp.FRMOData_MO_HospitalSubdivisionBed (" . implode(", ", $fieldsList) . ", insDT)
																values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())
																RETURNING query_id into QueryId;
															exception
																when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
														END;
														$$;

														select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
														from pg_temp.exp_Query();
														";


											 $resp = $this->getFirstRowFromQuery($query, $MO_HospitalSubdivisionBed_Data);

											 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
												 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_HospitalSubdivisionBed_Data));
											 }
											 else if ( !empty($resp['Error_Msg']) ) {
												 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
											 }
										 }
									 }
								 }
							 }

							 if ( property_exists($depart, 'departLabs') ) {
								 foreach ( $depart->departLabs->departLab as $departLab ) {
									 $MO_DepartLab_Data = array(
										 'Lpu_id' => $this->_Lpu_id,
										 'Depart_id' => $MO_Depart_Data['id'],
										 'roomTypeId' => (property_exists($departLab, 'roomTypeId') && !empty($departLab->roomTypeId['id']) ? $departLab->roomTypeId['id']->__toString() : null),
										 'roomCount' => (property_exists($departLab, 'roomCount') ? $departLab->roomCount->__toString() : null),
										 'examPerShift' => (property_exists($departLab, 'examPerShift') ? $departLab->examPerShift->__toString() : null),
										 'buildingId' => (property_exists($departLab, 'buildingId') ? $departLab->buildingId->__toString() : null),
										 'oid' => (property_exists($departLab, 'oid') ? $departLab->oid->__toString() : null),
									 );

									 $fieldsList = array_keys($MO_DepartLab_Data);
									 $updateParamsList = array();

									 foreach ( $fieldsList as $field ) {
										 $updateParamsList[] = $field . " = :" . $field;
									 }

									 $MO_DepartLab_Data['id'] = $this->getFirstResultFromQuery("select id as \"id\"
																						from tmp.FRMOData_MO_DepartLab
																						where Lpu_id = :Lpu_id and oid = :oid and updDT is null
																						limit 1"
																								 , $MO_DepartLab_Data, true);

									 if ( !empty($MO_DepartLab_Data['id']) )
									 {
										 $query = "
												update tmp.FRMOData_MO_DepartLab
												set
													" . implode(", ", $updateParamsList) . ",
													insDT = dbo.tzGetDate()
												where id = :id
												RETURNING query_id into QueryId;
											";
									 }
									 else
									 {
										 $query = "
												insert into tmp.FRMOData_MO_DepartLab (" . implode(", ", $fieldsList) . ", insDT)
												values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())

												RETURNING query_id into QueryId;
											";
									 }

									 $resp = $this->getFirstRowFromQuery("
												CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
													LANGUAGE 'plpgsql'

													AS $$
													DECLARE

													BEGIN
														{$query}
														exception
															when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
													END;
													$$;

													select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
													from pg_temp.exp_Query();
											", $MO_DepartLab_Data);

									 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
										 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_DepartLab_Data));
									 }
									 else if ( !empty($resp['Error_Msg']) ) {
										 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
									 }

									 $MO_DepartLab_Data['id'] = $resp['id'];

									 $this->_MO_DepartLab_Data[] = $MO_DepartLab_Data;
								 }
							 }

							 $this->db->query("delete from tmp.FRMOData_MO_DepartAmbulance where Depart_id = :Depart_id and updDT is null", array('Depart_id' => $MO_Depart_Data['id']));

							 if ( property_exists($depart, 'departAmbulances') ) {
								 foreach ( $depart->departAmbulances->departAmbulance as $departAmbulance ) {
									 $MO_DepartAmbulance_Data = array(
										 'Lpu_id' => $this->_Lpu_id,
										 'Depart_id' => $MO_Depart_Data['id'],
										 'brigadeCount' => (property_exists($departAmbulance, 'brigadeCount') ? $departAmbulance->brigadeCount->__toString() : null),
										 'carCount' => (property_exists($departAmbulance, 'carCount') ? $departAmbulance->carCount->__toString() : null),
										 'departurePerShift' => (property_exists($departAmbulance, 'departurePerShift') ? $departAmbulance->departurePerShift->__toString() : null),
										 'brigadeProfileId' => (property_exists($departAmbulance, 'brigadeProfileId') && !empty($departAmbulance->brigadeProfileId['id']) ? $departAmbulance->brigadeProfileId['id']->__toString() : null),
										 'brigadeTypeId' => (property_exists($departAmbulance, 'brigadeTypeId') && !empty($departAmbulance->brigadeTypeId['id']) ? $departAmbulance->brigadeTypeId['id']->__toString() : null),
										 'buildingId' => (property_exists($departAmbulance, 'buildingId') ? $departAmbulance->buildingId->__toString() : null),
									 );

									 $fieldsList = array_keys($MO_DepartAmbulance_Data);

									 $query = "
											CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
											LANGUAGE 'plpgsql'

											AS $$
											DECLARE

											BEGIN
													insert into tmp.FRMOData_MO_DepartAmbulance (" . implode(", ", $fieldsList) . ", insDT)
													values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())
													RETURNING query_id into QueryId;
												exception
													when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
											END;
											$$;

											select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
											from pg_temp.exp_Query();
											";

									 $resp = $this->getFirstRowFromQuery($query, $MO_DepartAmbulance_Data);

									 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
										 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_DepartAmbulance_Data));
									 }
									 else if ( !empty($resp['Error_Msg']) ) {
										 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
									 }
								 }
							 }

							 $this->db->query("delete from tmp.FRMOData_MO_DepartBuilding where Depart_id = :Depart_id and updDT is null", array('Depart_id' => $MO_Depart_Data['id']));

							 if ( property_exists($depart, 'buildings') && property_exists($depart->buildings, 'buildingId') ) {
								 foreach ( $depart->buildings->buildingId as $buildingId ) {
									 $MO_DepartBuilding_Data = array(
										 'Lpu_id' => $this->_Lpu_id,
										 'Depart_id' => $MO_Depart_Data['id'],
										 'buildingId' => $buildingId->__toString(),
									 );

									 $fieldsList = array_keys($MO_DepartBuilding_Data);

									 $query = "
											CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
											LANGUAGE 'plpgsql'

											AS $$
											DECLARE

											BEGIN
											insert into tmp.FRMOData_MO_DepartBuilding (" . implode(", ", $fieldsList) . ", insDT)
											values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())
													RETURNING query_id into QueryId;
												exception
													when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
											END;
											$$;

											select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
											from pg_temp.exp_Query();
											";


									 $resp = $this->getFirstRowFromQuery($query, $MO_DepartBuilding_Data);

									 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
										 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_DepartBuilding_Data));
									 }
									 else if ( !empty($resp['Error_Msg']) ) {
										 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
									 }
								 }
							 }
						 }
					 }

					 // 5. Запускаем обработку штатного расписания
					 foreach ( $this->_files_MO_Staff as $Lpu_id => $xmlfile ) {
						 $this->_Lpu_id = $Lpu_id;

						 $xml = new SimpleXMLElement(file_get_contents(FRMO_DATA_PATH . $xmlfile));

						 foreach ( libxml_get_errors() as $error ) {
							 throw new Exception('Ошибка XML: ' . $error);
						 }

						 libxml_clear_errors();

						 $this->db->query("delete from tmp.FRMOData_MO_StaffDetail where Lpu_id = :Lpu_id and updDT is null", array('Lpu_id' => $this->_Lpu_id));
						 $this->db->query("delete from tmp.FRMOData_MO_Staff where Lpu_id = :Lpu_id and updDT is null", array('Lpu_id' => $this->_Lpu_id));

						 foreach ( $xml->staff as $staff ) {
							 $MO_Staff_Data = array(
								 //'XMLData' => file_get_contents(FRMO_DATA_PATH . $xmlfile),
								 'Lpu_id' => $this->_Lpu_id,
								 'staffNum' => (property_exists($staff, 'staffNum') ? $staff->staffNum->__toString() : null),
								 'staffCreateDate' => (property_exists($staff, 'staffCreateDate') ? $staff->staffCreateDate->__toString() : null),
								 'beginDate' => (property_exists($staff, 'beginDate') ? $staff->beginDate->__toString() : null),
								 'endDate' => (property_exists($staff, 'endDate') ? $staff->endDate->__toString() : null),
								 'createDate' => (property_exists($staff, 'createDate') ? $staff->createDate->__toString() : null),
							 );

							 if ( empty($MO_Staff_Data['staffCreateDate']) ) $MO_Staff_Data['staffCreateDate'] = null;
							 if ( empty($MO_Staff_Data['beginDate']) ) $MO_Staff_Data['beginDate'] = null;
							 if ( empty($MO_Staff_Data['endDate']) ) $MO_Staff_Data['endDate'] = null;

							 $fieldsList = array_keys($MO_Staff_Data);

							 $query = "
									CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
									LANGUAGE 'plpgsql'

									AS $$
									DECLARE

									BEGIN
											insert into tmp.FRMOData_MO_Staff (" . implode(", ", $fieldsList) . ", insDT)
											values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())
											RETURNING query_id into QueryId;
										exception
											when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
									END;
									$$;

									select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
									from pg_temp.exp_Query();
									";

							 $resp = $this->getFirstRowFromQuery($query, $MO_Staff_Data);

							 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
								 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_Staff_Data));
							 }
							 else if ( !empty($resp['Error_Msg']) ) {
								 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
							 }

							 $MO_Staff_Data['id'] = $resp['id'];

							 $this->_MO_Staff_Data[] = $MO_Staff_Data;

							 if ( property_exists($staff, 'staffDetails') ) {
								 foreach ( $staff->staffDetails->staffDetail as $staffDetail ) {
									 $MO_StaffDetail_Data = array(
										 'Lpu_id' => $this->_Lpu_id,
										 'Staff_id' => $MO_Staff_Data['id'],
										 'totalRate' => (property_exists($staffDetail, 'totalRate') ? $staffDetail->totalRate->__toString() : null),
										 'nrPmuDepartId' => (property_exists($staffDetail, 'nrPmuDepartId') ? $staffDetail->nrPmuDepartId->__toString() : null),
										 'nrPmuDepartHospitalSubdivisionId' => (property_exists($staffDetail, 'nrPmuDepartHospitalSubdivisionId') ? $staffDetail->nrPmuDepartHospitalSubdivisionId->__toString() : null),
										 'postId' => (property_exists($staffDetail, 'postId') && !empty($staffDetail->postId['id']) ? $staffDetail->postId['id']->__toString() : null),
										 'busyRate' => (property_exists($staffDetail, 'busyRate') ? $staffDetail->busyRate->__toString() : null),
										 'externalRate' => (property_exists($staffDetail, 'externalRate') ? $staffDetail->externalRate->__toString() : null),
									 );

									 $fieldsList = array_keys($MO_StaffDetail_Data);
									 $query = "
											CREATE OR REPLACE FUNCTION pg_temp.exp_Query( out QueryId bigint,out Error_Code int,out Error_Message text )
											LANGUAGE 'plpgsql'

											AS $$
											DECLARE

											BEGIN
													insert into tmp.FRMOData_MO_StaffDetail (" . implode(", ", $fieldsList) . ", insDT)
													values (:" . implode(", :", $fieldsList) . ", dbo.tzGetDate())
													RETURNING query_id into QueryId;
												exception
													when others then Error_Code:=SQLSTATE; Error_Message:=SQLERRM;
											END;
											$$;

											select QueryId as \"id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
											from pg_temp.exp_Query();
											";


									 $resp = $this->getFirstRowFromQuery($query, $MO_StaffDetail_Data);

									 if ( $resp == false || !is_array($resp) || count($resp) == 0 ) {
										 throw new Exception('Ошибка запроса к БД: ' . getDebugSQL($query, $MO_StaffDetail_Data));
									 }
									 else if ( !empty($resp['Error_Msg']) ) {
										 throw new Exception('Ошибка запроса к БД: ' . $resp['Error_Msg']);
									 }
								 }
							 }
						 }
					 }

					 //var_dump($this->_MO_Data);
					 //var_dump($this->_MO_Building_Data);
					 //var_dump($this->_MO_Depart_Data);
					 //var_dump($this->_MO_Staff_Data);

					 $response = array('Error_Msg' => '');
				 }
				 catch ( Exception $e ) {
					 $response = array('Error_Msg' => $e->getMessage());

					 if ( !isset($data['fromAPI']) || $data['fromAPI'] === false ) {
						 $this->textlog->add($e->getMessage());
					 }
				 }

				 return $response;
			 }
		 }
