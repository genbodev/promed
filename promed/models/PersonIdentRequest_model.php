<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonIdentRequest_model extends swModel {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 *	Проверка необходимости изменения аттрибута человека
	 */
	function checkPersonAttributeChangeIsNecessary($personEvnClassCode, $personSaveData, $evnSetDT = null) {
		$response = array(
			'insDT' => NULL,
			'isNecessary' => false,
			'errorMsg' => '',
			'success' => true
		);

		if ( empty($evnSetDT) ) {
			$evnSetDT = date('Y-m-d H:i:s');
		}

		switch ( $personEvnClassCode ) {
			case 1: // Фамилия
				$query = "
					select top 1 RTRIM(ISNULL(PersonSurName_SurName, '')) as Person_SurName
					from v_PersonSurName with (nolock)
					where PersonSurName_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonSurName_insDT desc
				";
			break;

			case 2: // Имя
				$query = "
					select top 1 RTRIM(ISNULL(PersonFirName_FirName, '')) as Person_FirName
					from v_PersonFirName with (nolock)
					where PersonFirName_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonFirName_insDT desc
				";
			break;

			case 3: // Отчество
				$query = "
					select top 1 RTRIM(ISNULL(PersonSecName_SecName, '')) as Person_SecName
					from v_PersonSecName with (nolock)
					where PersonSecName_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonSecName_insDT desc
				";
			break;

			case 4: // Дата рождения
				$query = "
					select top 1 convert(varchar(10), PersonBirthDay_BirthDay, 120) as Person_BirthDay
					from v_PersonBirthDay with (nolock)
					where PersonBirthDay_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonBirthDay_insDT desc
				";
			break;

			case 5: // Пол
				$query = "
					select top 1 ISNULL(Sex_id, 0) as Sex_id
					from v_PersonSex with (nolock)
					where PersonSex_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonSex_insDT desc
				";
			break;

			case 6: // СНИЛС
				$query = "
					select top 1 RTRIM(ISNULL(PersonSnils_Snils, '')) as Person_Snils
					from v_PersonSnils with (nolock)
					where PersonSnils_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonSnils_insDT desc
				";
			break;

			case 7: // Соц. статус
				$query = "
					select top 1 ISNULL(SocStatus_id, 0) as SocStatus_id
					from v_PersonSocStatus with (nolock)
					where PersonSocStatus_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonSocStatus_insDT desc
				";
			break;

			case 19: // ИНН
				$query = "
					select top 1 RTRIM(ISNULL(PersonInn_Inn, '')) as Person_Inn
					from v_PersonInn with (nolock)
					where PersonInn_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonInn_insDT desc
				";
			break;

			case 8: // Полис
				$query = "
					select top 1
						ISNULL(OrgSmo_id, 0) as OrgSmo_id,
						RTRIM(ISNULL(Polis_Num, '')) as Polis_Num,
						convert(varchar(10), Polis_begDate, 120) as Polis_begDate,
						convert(varchar(10), Polis_endDate, 120) as Polis_endDate
					from v_PersonPolis with (nolock)
					where PersonPolis_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonPolis_insDT desc
				";
			break;

			case 9: // Документ
				$query = "
					select top 1
						ISNULL(DocumentType_id, 0) as DocumentType_id,
						RTRIM(ISNULL(Document_Ser, '')) as Document_Ser,
						RTRIM(ISNULL(Document_Num, '')) as Document_Num
					from v_PersonDocument with (nolock)
					where PersonDocument_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonDocument_insDT desc
				";
			break;

			case 10: // Адрес регистрации
				$query = "
					select top 1
						ISNULL(KLRgn_id, 0) as KLRgn_id,
						ISNULL(KLSubRgn_id, 0) as KLSubRgn_id,
						ISNULL(KLCity_id, 0) as KLCity_id,
						ISNULL(KLTown_id, 0) as KLTown_id,
						ISNULL(KLStreet_id, 0) as KLStreet_id,
						RTRIM(ISNULL(Address_House, '')) as Address_House,
						RTRIM(ISNULL(Address_Corpus, '')) as Address_Corpus,
						RTRIM(ISNULL(Address_Flat, '')) as Address_Flat
					from v_PersonUAddress with (nolock)
					where PersonUAddress_insDT <= cast(:PersonEvn_insDT as datetime)
					order by PersonUAddress_insDT desc
				";
			break;

			default:
				return $response;
			break;
		}

		$queryParams = array('PersonEvn_insDT' => $evnSetDT);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( !is_array($res) ) {
				$response['errorMsg'] = 'Ошибка при проверке факта изменения аттрибута';
				$response['success'] = false;
				return $response;
			}

			switch ( $personEvnClassCode ) {
				case 1: $person_surname = ''; break;
				case 2: $person_firname = ''; break;
				case 3: $person_secname = ''; break;
				case 4: $person_birthday = ''; break;
				case 5: $sex_id = 0; break;
				case 6: $person_snils = ''; break;
				case 7: $socstatus_id = 0; break;
				case 8: $orgmo_id = 0; $polis_num = ''; $polis_begdate = ''; $polis_enddate = ''; break;
				case 9: $documenttype_id = 0; $document_ser = ''; $document_num = ''; break;
				case 10: $klrgn_id = 0; $klsubrgn_id = 0; $klcity_id = 0; $kltown_id = 0; $klstreet_id = 0; $address_house = ''; $address_corpus = ''; $address_flat = ''; break;
				// case 16: $person_ed_num = ''; break;
				case 19: $person_inn = ''; break;
			}

			if ( count($res) > 0 ) {
				switch ( $personEvnClassCode ) {
					case 1: $person_surname = strtolower(trim($res[0]['Person_SurName'])); break;
					case 2: $person_firname = strtolower(trim($res[0]['Person_FirName'])); break;
					case 3: $person_secname = strtolower(trim($res[0]['Person_SecName'])); break;
					case 4: $person_birthday = trim($res[0]['Person_BirthDay']); break;
					case 5: $sex_id = $res[0]['Sex_id']; break;
					case 6: $person_snils = trim($res[0]['Person_Snils']); break;
					case 7: $socstatus_id = $res[0]['SocStatus_id']; break;
					case 8:
						$orgsmo_id = $res[0]['OrgSmo_id'];
						$polis_num = $res[0]['Polis_Num'];
						$polis_begdate = $res[0]['Polis_begDate'];
						$polis_enddate = $res[0]['Polis_endDate'];
					break;
					case 9:
						$documenttype_id = $res[0]['DocumentType_id'];
						$document_ser = $res[0]['Document_Ser'];
						$document_num = $res[0]['Document_Num'];
					break;
					case 10:
						$klrgn_id = $res[0]['KLRgn_id'];
						$klsubrgn_id = $res[0]['KLSubRgn_id'];
						$klcity_id = $res[0]['KLCity_id'];
						$kltown_id = $res[0]['KLTown_id'];
						$klstreet_id = $res[0]['KLStreet_id'];
						$address_house = $res[0]['Address_House'];
						$address_corpus = $res[0]['Address_Corpus'];
						$address_flat = $res[0]['Address_Flat'];
					break;
					// case 16: $person_ed_num = trim($res[0]['Person_EdNum']); break;
					case 19: $person_inn = trim($res[0]['Person_Inn']); break;
				}
			}

			switch ( $personEvnClassCode ) {
				case 1:
					if ( empty($personSaveData['FAM']) ) {
						$personSaveData['FAM'] = '';
					}

					if ( $person_surname != strtolower(trim($personSaveData['FAM'])) ) {
						$response['isNecessary'] = true;
					}
				break;

				case 2:
					if ( empty($personSaveData['NAM']) ) {
						$personSaveData['NAM'] = '';
					}

					if ( $person_firname != strtolower(trim($personSaveData['NAM'])) ) {
						$response['isNecessary'] = true;
					}
				break;

				case 3:
					if ( empty($personSaveData['FNAM']) ) {
						$personSaveData['FNAM'] = '';
					}

					if ( $person_secname != strtolower(trim($personSaveData['FNAM'])) ) {
						$response['isNecessary'] = true;
					}
				break;

				case 4:
					if ( empty($personSaveData['BORN_DATE']) ) {
						$personSaveData['BORN_DATE'] = '';
					}

					if ( $person_birthday != strtolower(trim($personSaveData['BORN_DATE'])) ) {
						$response['isNecessary'] = true;
					}
				break;

				case 5:
					if ( empty($personSaveData['Sex_id']) ) {
						$personSaveData['Sex_id'] = 0;
					}

					if ( $sex_id != $personSaveData['Sex_id'] ) {
						$response['isNecessary'] = true;
					}
				break;

				case 6:
					if ( empty($personSaveData['SNILS']) ) {
						$personSaveData['SNILS'] = '';
					}

					if ( $person_snils != trim($personSaveData['SNILS']) ) {
						$response['isNecessary'] = true;
					}
				break;

				case 7:
					if ( empty($personSaveData['SocStatus_id']) ) {
						$personSaveData['SocStatus_id'] = 0;
					}

					if ( $socstatus_id != $personSaveData['SocStatus_id'] ) {
						$response['isNecessary'] = true;
					}
				break;

				case 8:
					if ( empty($personSaveData['OrgSmo_id']) ) {
						$personSaveData['OrgSmo_id'] = 0;
					}

					if ( empty($personSaveData['BESTBEFORE']) ) {
						$personSaveData['BESTBEFORE'] = '';
					}

					if ( empty($personSaveData['GIV_DATE']) ) {
						$personSaveData['GIV_DATE'] = '';
					}

					if ( empty($personSaveData['POL_NUM_16']) ) {
						$personSaveData['POL_NUM_16'] = '';
					}

					if ( $orgsmo_id != $personSaveData['OrgSmo_id'] || $polis_num != $personSaveData['POL_NUM_16'] || $polis_begdate != $personSaveData['GIV_DATE'] || $polis_enddate != $personSaveData['BESTBEFORE'] ) {
						$response['isNecessary'] = true;
					}
				break;

				case 9:
					if ( empty($personSaveData['DocumentType_id']) ) {
						$personSaveData['DocumentType_id'] = 0;
					}

					if ( empty($personSaveData['DOC_SER']) ) {
						$personSaveData['DOC_SER'] = '';
					}

					if ( empty($personSaveData['DOC_NUM']) ) {
						$personSaveData['DOC_NUM'] = '';
					}

					if ( $documenttype_id != $personSaveData['DocumentType_id'] || $document_ser != $personSaveData['DOC_SER'] || $document_num != $personSaveData['DOC_NUM'] ) {
						$response['isNecessary'] = true;
					}
				break;

				case 10:
					if ( empty($personSaveData['KLRgn_rid']) ) {
						$personSaveData['KLRgn_rid'] = 0;
					}

					if ( empty($personSaveData['KLSubRgn_rid']) ) {
						$personSaveData['KLSubRgn_rid'] = 0;
					}

					if ( empty($personSaveData['KLCity_rid']) ) {
						$personSaveData['KLCity_rid'] = 0;
					}

					if ( empty($personSaveData['KLTown_rid']) ) {
						$personSaveData['KLTown_rid'] = 0;
					}

					if ( empty($personSaveData['KLStreet_rid']) ) {
						$personSaveData['KLStreet_rid'] = 0;
					}

					if ( empty($personSaveData['HOUSE']) ) {
						$personSaveData['HOUSE'] = '';
					}

					if ( empty($personSaveData['CORP']) ) {
						$personSaveData['CORP'] = '';
					}

					if ( empty($personSaveData['FLAT']) ) {
						$personSaveData['FLAT'] = '';
					}

					if ( $klrgn_id != $personSaveData['KLRgn_rid'] || $klsubrgn_id != $personSaveData['KLSubRgn_rid'] ||
						$klcity_id != $personSaveData['KLCity_rid'] || $kltown_id != $personSaveData['KLTown_rid'] ||
						$klstreet_id != $personSaveData['KLStreet_rid'] || $address_house != $personSaveData['HOUSE'] ||
						$address_corpus != $personSaveData['CORP'] || $address_flat != $personSaveData['FLAT']
					) {
						$response['isNecessary'] = true;
					}
				break;
				/*
				case 16:
					if ( empty($personSaveData['POL_NUM_16']) ) {
						$personSaveData['POL_NUM_16'] = '';
					}

					if ( $person_ed_num != trim($personSaveData['POL_NUM_16']) ) {
						$response['isNecessary'] = true;
					}
				break;
				*/
				case 19:
					if ( empty($personSaveData['INN']) ) {
						$personSaveData['INN'] = '';
					}

					if ( $person_inn != trim($personSaveData['INN']) ) {
						$response['isNecessary'] = true;
					}
				break;
			}

			if ( $response['isNecessary'] === true ) {
				switch ( $personEvnClassCode ) {
					case 1: // Фамилия
						$query = "
							select count(PersonSurName_id) as cnt
							from v_PersonSurName with (nolock)
							where PersonSurName_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 2: // Имя
						$query = "
							select count(PersonFirName_id) as cnt
							from v_PersonFirName with (nolock)
							where PersonFirName_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 3: // Отчество
						$query = "
							select count(PersonSecName_id) as cnt
							from v_PersonSecName with (nolock)
							where PersonSecName_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 4: // Дата рождения
						$query = "
							select count(PersonBirthDay_id) as cnt
							from v_PersonBirthDay with (nolock)
							where PersonBirthDay_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 5: // Пол
						$query = "
							select count(PersonSex_id) as cnt
							from v_PersonSex with (nolock)
							where PersonSex_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 6: // СНИЛС
						$query = "
							select count(PersonSnils_id) as cnt
							from v_PersonSnils with (nolock)
							where PersonSnils_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 7: // Соц. статус
						$query = "
							select count(PersonSocStatus_id) as cnt
							from v_PersonSocStatus with (nolock)
							where PersonSocStatus_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 19: // ИНН
						$query = "
							select count(PersonInn_id) as cnt
							from v_PersonInn with (nolock)
							where PersonInn_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 8: // Полис
						$query = "
							select count(PersonPolis_id) as cnt
							from v_PersonPolis with (nolock)
							where PersonPolis_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 9: // Документ
						$query = "
							select count(PersonDocument_id) as cnt
							from v_PersonDocument with (nolock)
							where PersonDocument_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					case 10: // Адрес регистрации
						$query = "
							select count(PersonUAddress_id) as cnt
							from v_PersonUAddress with (nolock)
							where PersonUAddress_insDT <= cast(:PersonEvn_insDT as datetime)
						";
					break;

					default:
						return $response;
					break;
				}

				$queryParams = array('Evn_setDT' => $evnSetDT);

				if ( !empty($personSaveData['GIV_DATE']) ) {
					switch ( $personEvnClassCode ) {
						case 1: $query .= " and PersonSurName_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 2: $query .= " and PersonFirName_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 3: $query .= " and PersonSecName_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 4: $query .= " and PersonBirthDay_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 5: $query .= " and PersonSex_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 6: $query .= " and PersonSnils_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 7: $query .= " and PersonSocStatus_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 8: $query .= " and PersonPolis_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 9: $query .= " and PersonDocument_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 10: $query .= " and PersonUAddress_insDT >= cast(:Polis_begDT as datetime)"; break;
						case 19: $query .= " and PersonInn_insDT >= cast(:Polis_begDT as datetime)"; break;
					}

					$queryParams['Polis_begDT'] = $personSaveData['GIV_DATE'];
				}

				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$res = $result->result('array');

					if ( is_array($res) && count($res) > 0 ) {
						if ( $res[0]['cnt'] == 0 ) {
							$response['insDT'] = $personSaveData['GIV_DATE'];
						}
						else {
							$response['insDT'] = $evnSetDT;
						}
					}
					else {
						$response['errorMsg'] = 'Ошибка при получении количества фактов изменения аттрибута';
						$response['success'] = false;
					}
				}
				else {
					$response['errorMsg'] = 'Ошибка при выполнении запроса к базе данных (получение количества фактов изменения аттрибута)';
					$response['success'] = false;
				}
			}
		}
		else {
			$response['errorMsg'] = 'Ошибка при выполнении запроса к базе данных (проверка факта изменения аттрибута)';
			$response['success'] = false;
		}

		return $response;
	}


	/**
	 *	Получение идентификатора типа документа
	 *	$documentTypeCode int код типа документа
	 */
	function getDocumentTypeId($documentTypeCode) {
		$query = "
			select top 1 DocumentType_id
			from DocumentType with (nolock)
			where DocumentType_Code = :DocumentType_Code
		";

		$queryParams = array('DocumentType_Code' => $documentTypeCode);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение кода КЛАДР
	 */
	function getKladrCode($klarea_id, $klstreet_id) {
		if ( !empty($klstreet_id) ) {
			$query = "
				select top 1 Kladr_Code
				from KLStreet with (nolock)
				where KLStreet_id = :KLStreet_id
			";

			$queryParams = array('KLStreet_id' => $klstreet_id);
		}
		else if ( !empty($klarea_id) ) {
			$query = "
				select top 1 Kladr_Code
				from KLArea with (nolock)
				where KLArea_id = :KLArea_id
			";

			$queryParams = array('KLArea_id' => $klarea_id);
		}
		else {
			return false;
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение кода СМО
	 *	$orgSmoId int идентификатор СМО
	 */
	function getOrgSmoCode($orgSmoId) {
		$query = "
			select top 1
				O.Org_Code as OrgSmo_Code
			from v_OrgSMO OS with (nolock)
				inner join Org O with (nolock) on O.Org_id = OS.Org_id
			where OS.OrgSMO_id = :OrgSMO_id
		";

		$queryParams = array('OrgSMO_id' => $orgSmoId);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идентификатора территории страхования
	 *	$code int код территории страхования
	 */
	function getOMSSprTerrId($code) {
		$query = "
			select top 1 OMSSprTerr_id
			from OMSSprTerr with (nolock)
			where OMSSprTerr_Code = :code
		";

		$queryParams = array('code' => $code);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идентификатора СМО
	 *	$orgSmoCode int код СМО
	 */
	function getOrgSmoId($orgSmoCode) {
		$query = "
			select top 1
				OS.OrgSMO_id as OrgSmo_id
			from v_OrgSMO OS with (nolock)
				inner join Org O with (nolock) on O.Org_id = OS.Org_id
			where O.Org_Code = :Org_Code
		";

		$queryParams = array('Org_Code' => $orgSmoCode);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идентификатора статуса идентификации человека
	 *	$code int код статуса идентификации человека
	 */
	function getPersonIdentStateId($code) {
		$query = "
			select top 1 PersonIdentState_id
			from PersonIdentState with (nolock)
			where PersonIdentState_Code = :PersonIdentState_Code
		";

		$queryParams = array('PersonIdentState_Code' => $code);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идентификатора пола
	 *	$sexCode int код пола
	 */
	function getSexId($sexCode) {
		$query = "
			select top 1 Sex.Sex_id
			from Sex with (nolock)
			where Sex.Sex_Code = :Sex_Code
		";

		$queryParams = array('Sex_Code' => $sexCode);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идентификатора социального статуса
	 *	$socStatusSysNick string системныое наименование социального статуса
	 */
	function getSocStatusId($socStatusSysNick) {
		$query = "
			select top 1 SocStatus_id
			from v_SocStatus with (nolock)
			where SocStatus_SysNick = :SocStatus_SysNick
		";

		$queryParams = array('SocStatus_SysNick' => $socStatusSysNick);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных человека для формирования запроса на идентификацию
	 *	$data @array содержит идентификатор человека или идентификаторы состояния человека
	 */
	function getPersonIdentRequestData($data) {
		$query = "
			select top 1
				ISNULL(PIS.PersonIdentState_Code, 0) as PersonIdentState_Code,
				ISNULL(PS.Person_SurName, '') as Person_Surname,
				ISNULL(PS.Person_FirName, '') as Person_Firname,
				ISNULL(PS.Person_SecName, '') as Person_Secname,
				ISNULL(CONVERT(varchar(20), PS.Person_BirthDay, 120), '') as Person_Birthday,
				ISNULL(S.Sex_id, 0) as Sex_id,
				ISNULL(S.Sex_Code, 0) as Sex_Code,
				ISNULL(DT.DocumentType_Code, 0) as DocumentType_Code,
				ISNULL(DT.DocumentType_id, 0) as DocumentType_id,
				ISNULL(D.Document_Ser, '') as Document_Ser,
				ISNULL(D.Document_Num, '') as Document_Num,
				ISNULL(PS.Person_Snils, '') as Person_Snils,
				ISNULL(PS.Person_Inn, '') as Person_Inn,
				COALESCE(KLS.Kladr_Code, KLA.Kladr_Code, '') as Kladr_Code,
				ISNULL(UAddr.Address_House, '') as Address_House,
				ISNULL(UAddr.Address_Flat, '') as Address_Flat,
				ISNULL(O.Org_Code, '') as Org_Code,
				ISNULL(OS.OrgSmo_id, 0) as OrgSmo_id,
				ISNULL(P.Polis_Num, '') as Polis_Num,
				ISNULL(P.OMSSprTerr_id, 0) as OMSSprTerr_id,
				ISNULL(SS.SocStatus_Code, 0) as SocStatus_Code,
				ISNULL(SS.SocStatus_id, 0) as SocStatus_id,
				ISNULL(PS.Person_id, 1) as Person_id
			from v_PersonState PS with (nolock)
				inner join Person with (nolock) on Person.Person_id = PS.Person_id
				left join PersonIdentState PIS with (nolock) on PIS.PersonIdentState_id = Person.PersonIdentState_id
				left join Sex S with (nolock) on S.Sex_id = PS.Sex_id
				left join Document D with (nolock) on D.Document_id = PS.Document_id
				left join DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join KLStreet KLS with (nolock) on KLS.KLStreet_id = UAddr.KLStreet_id
				left join KLArea KLA with (nolock) on KLA.KLArea_id = COALESCE(UAddr.KLTown_id, UAddr.KLCity_id, UAddr.KLSubRgn_id, UAddr.KLRgn_id)
					and KLS.KLStreet_id is null
				left join Polis P with (nolock) on P.Polis_id = PS.Polis_id
				left join OrgSmo OS with (nolock) on OS.OrgSmo_id = P.OrgSmo_id
				left join Org O with (nolock) on O.Org_id = OS.Org_id
				left join SocStatus SS with (nolock) on SS.SocStatus_id = PS.SocStatus_id
			where PS.Person_id = :Person_id
		";

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при получении данных для идентификации человека', 'success' => false);
		}
	}


	/**
	 *	Установить для человека признак "откуда попал в Промед" :)
	 */
	function setPersonServer($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Person_server
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Установить для человека признак "идентифицирован"
	 */
	function setPersonIdentState($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Person_ident
				@Person_id = :Person_id,
				@Person_identDT = :Person_identDT,
				@PersonIdentState_id = :PersonIdentState_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Person_identDT' => $data['Person_identDT'],
			'PersonIdentState_id' => $data['PersonIdentState_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Перекодировка КЛАДРов по задаче #11630 Ошибка адреса при идентификации из-за обновления КЛАДРа
	 *
	 * @param $oldCode int|string
	 * @return int|string
	 */
	function tmp_Altnames_getNewCode($oldCode){
		$code_len = strlen($oldCode);
		switch ($code_len) {
			case 19:
				$p = substr($oldCode, 0, $code_len-4);//если код 19-значный - значит с точностью до дома, значит надо вырезать последние четыре символа - это дом и актуальность
				break;
			default:
				$p = substr($oldCode, 0, $code_len-2);//для остальных вырезаем только последние два - акутальность
				break;
		}
		
		$where = "OLDCODE = :oldCode";
		
		if (strlen($p) == 15) {
			$where = "OLDCODE like :oldCode + '%'";
		}
		
		$result = $this->getFirstResultFromQuery(
			"SELECT top 1 NEWCODE FROM tmp._Altnames with(nolock) WHERE {$where}",
			array(
				'oldCode' => $p
			)
		);
		if ($result) {
			$count = $this->getFirstResultFromQuery(
				"SELECT count(*) FROM tmp._Altnames with(nolock) WHERE {$where}",
				array(
					'oldCode' => $p
				)
			);
			if ($count != 1) {
				log_message('error','tmp_Altnames_newCode: В таблице перекодировки КЛАДР tmp._Altnames более одного соответствия для кода '.$oldCode);
			}
			return $result;
		} else {
			//log_message('error','tmp_Altnames_newCode: Не удалось перекодировать в новый код: $oldCode = '.$oldCode.'. Произведено обновление СБЗ? Если так, необходимо убрать эту перекодировку.');
			return $oldCode; //возможно, код и так новый
		}
	}

	/**
	 *	Обработка кода КЛАДР и формирование текстовой строки адреса
	 *	$kladrCode @int код КЛАДР
	 *	$house @char дом
	 *	$corpus @char корпус
	 *	$flat @char квартира
	 */
	function parseKladrCode($kladrCode, $house = '', $corpus = '', $flat = '') {
		$query = "
			select
				643 as KLCountry_id,
				KLAdr_Index,
				KLRgn_id,
				KLSubRgn_id,
				KLCity_id,
				KLTown_id,
				PersonSprTerrDop_id,
				KLStreet_id,
				Address_Address
			from dbo.f_parseKladrCode(
				:Kladr_Code,
				:Address_House,
				:Address_Corpus,
				:Address_Flat
			)
		";

		$queryParams = array(
			'Kladr_Code' => $kladrCode,
			'Address_House' => (!empty($house) ? $house : NULL),
			'Address_Corpus' => (!empty($corpus) ? $corpus : NULL),
			'Address_Flat' => (!empty($flat) ? $flat : NULL)
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение даты актуальности сводной базы застрахованных
	 */
	function getActualIdentDT($globalOptions) {
		$date = date('d.m.Y');

		if ( is_array($globalOptions) && array_key_exists('identification_actual_date', $globalOptions) && CheckDateFormat($globalOptions['identification_actual_date']) == 0 ) {
			$date = $globalOptions['identification_actual_date'];
		}

		return $date;
	}


	/**
	 *	Получение даты последней идентификации пациента
	 *	$person_id @int идентификатор пациента
	 */
	function getPersonIdentDT($person_id) {
		$query = "
			select convert(varchar(10), max(Person_identDT), 104) as Person_identDate
			from Person with (nolock)
			where Person_id = :Person_id
		";

		$queryParams = array(
			'Person_id' => $person_id
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( count($response) > 0 && !empty($response[0]['Person_identDate']) ) {
					return $response[0]['Person_identDate'];
				}
				else {
					return '';
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	

	/**
	 *	Выполнение запроса на идентификацию пациента при сохранении учетного документа
	 */
	function doPersonIdentOnEvnSave($data = array(), $evnSetDT = null, $globalOptions = array()) {
		if ( !is_array($globalOptions) || !array_key_exists('globals', $globalOptions) ) {
			$result['errorMsg'] = 'Не найдены глобальные настройки';
			$result['success'] = false;
			return $result;
		}

		$this->load->library('swPersonIdentUfa');

		$result = array(
			'errorMsg' => '',
			'success' => true
		);

		// $f = fopen('ident.txt', 'w');
		// fwrite($f, "Вызов doPersonIdentOnEvnSave\r\n");
		
		// Получение данных человека
		$personIdentRequestData = $this->getPersonIdentRequestData(array('Person_id' => $data['Person_id']));

		if ( is_array($personIdentRequestData) && count($personIdentRequestData) > 0 ) {
			// fwrite($f, "Получены перс. данные\r\n");
			// fwrite($f, "PersonIdentState_Code = " . $personIdentRequestData[0]['PersonIdentState_Code'] . "\r\n");

			if ( $personIdentRequestData[0]['PersonIdentState_Code'] == 1 && empty($evnSetDT) ) {
				// fwrite($f, "Идентификация не требуется\r\n");
				// fclose($f);
				return $result;
			}

			// Получение даты актуальности данных в сводной базе застрахованных
			$actual_ident_date = $this->getActualIdentDT($globalOptions['globals']);

			// fwrite($f, "Дата актуальности СБЗ: " . $actual_ident_date . "\r\n");

			if ( CheckDateFormat($actual_ident_date) == 0 && !empty($evnSetDT) && CheckDateFormat(substr($evnSetDT, 8, 2) . '.' . substr($evnSetDT, 5, 2) . '.' . substr($evnSetDT, 0, 4)) == 0 ) {
				// fwrite($f, "Дата случая: " . substr($evnSetDT, 8, 2) . '.' . substr($evnSetDT, 5, 2) . '.' . substr($evnSetDT, 0, 4) . "\r\n");

				$compareResult = swCompareDates($actual_ident_date, substr($evnSetDT, 8, 2) . '.' . substr($evnSetDT, 5, 2) . '.' . substr($evnSetDT, 0, 4));

				// Дата актуальности СБЗ меньше даты случая и у человека признак "идентифицирован"
				if ( $compareResult[0] == 1 && $personIdentRequestData[0]['PersonIdentState_Code'] == 1 ) {
					// fwrite($f, "Идентификация не требуется, ибо дата актуальности СБЗ меньше даты случая \r\n");
					// fclose($f);
					return $result;
				}
			}

			// fwrite($f, "[" . date('Y-m-d H:i:s') . "] Идентификация таки нужна\r\n");

			$identObject = new swPersonIdentUfa($this->config->item('IDENTIFY_SERVICE_URI'), $this->config->item('IDENTIFY_SERVICE_PORT'));

			// Формирование данных для запроса к сервису БДЗ
			$requestData = array(
				array(
					'FAM' => ucfirst(strtolower($personIdentRequestData[0]['Person_Surname'])),
					'NAM' => ucfirst(strtolower($personIdentRequestData[0]['Person_Firname'])),
					'FNAM' => ucfirst(strtolower($personIdentRequestData[0]['Person_Secname'])),
					'D_BORN' => $personIdentRequestData[0]['Person_Birthday'],
					'SEX' => $personIdentRequestData[0]['Sex_Code'],
					'DOC_TYPE' => $personIdentRequestData[0]['DocumentType_Code'],
					'DOC_SER' => $personIdentRequestData[0]['Document_Ser'],
					'DOC_NUM' => $personIdentRequestData[0]['Document_Num'],
					'INN' => $personIdentRequestData[0]['Person_Inn'],
					'KLADR' => $personIdentRequestData[0]['Kladr_Code'],
					'HOUSE' => $personIdentRequestData[0]['Address_House'],
					'ROOM' => $personIdentRequestData[0]['Address_Flat'],
					'SMO' => $personIdentRequestData[0]['Org_Code'],
					'POL_NUM' => $personIdentRequestData[0]['Polis_Num'],
					'STATUS' => $personIdentRequestData[0]['SocStatus_Code'],
					'ID_REG' => $personIdentRequestData[0]['Person_id']
				)
			);

			if ( !empty($evnSetDT) ) {
				$requestData[0]['DATE_POS'] = $evnSetDT;
			}

			// fwrite($f, "[" . date('Y-m-d H:i:s') . "] Запрос ушел...\r\n");

			$requestResponse = $identObject->doPersonIdentRequest($requestData);

			// fwrite($f, "[" . date('Y-m-d H:i:s') . "] Получен ответ на запрос идентификации\r\n");

			if ( $requestResponse['success'] === false ) {
				$result['errorMsg'] = $requestResponse['errorMsg'];
				$result['success'] = false;
				// fclose($f);
				return $result;
			}

			$personData = $requestResponse['identData'];

			if ( is_array($personData) ) {
				$personSaveData = array(
					'Server_id' => $data['Server_id'],
					'Person_id' => $data['Person_id'],
					'PersonIdentState_Code' => 2,
					'pmUser_id' => $data['pmUser_id']
				);

				if ( count($personData) == 1 && !empty($personData[0]['FAM']) ) {
					// Сохраняем данные по человеку на момент случая
					$personSaveData['PersonIdentState_Code'] = 1;
					$personSaveData['Server_id'] = 0;

					// fwrite($f, "[" . date('Y-m-d H:i:s') . "] " . serialize($personData[0]) . "\r\n");

					foreach ( $personData[0] as $key => $value ) {
						switch ( $key ) {
							case 'CLADR':
								if ( !empty($value) && preg_match('/^\d+$/', $value) && in_array(strlen($value), array(13, 17, 19)) ) {
									$parseKladrCodeResponse = $this->parseKladrCode(
										$value,
										(!empty($personData[0]['HOUSE']) ? $personData[0]['HOUSE'] : ''),
										(!empty($personData[0]['CORP']) ? $personData[0]['CORP'] : ''),
										(!empty($personData[0]['FLAT']) ? $personData[0]['FLAT'] : '')
									);

									if ( is_array($parseKladrCodeResponse) && count($parseKladrCodeResponse) > 0 && !empty($parseKladrCodeResponse[0]['Address_Address'])) {
										if (empty($personSaveData['KLAdr_Index'])) {
											$personSaveData['KLAdr_Index'] = $parseKladrCodeResponse[0]['KLAdr_Index'];
										}
										$personSaveData['KLRgn_rid'] = $parseKladrCodeResponse[0]['KLRgn_id'];
										$personSaveData['KLSubRgn_rid'] = $parseKladrCodeResponse[0]['KLSubRgn_id'];
										$personSaveData['KLCity_rid'] = $parseKladrCodeResponse[0]['KLCity_id'];
										$personSaveData['KLTown_rid'] = $parseKladrCodeResponse[0]['KLTown_id'];
										$personSaveData['KLStreet_rid'] = $parseKladrCodeResponse[0]['KLStreet_id'];
										$personSaveData['PersonSprTerrDop_rid'] = $parseKladrCodeResponse[0]['PersonSprTerrDop_id'];
										$personSaveData['RAddress_Name'] = $parseKladrCodeResponse[0]['Address_Address'];
									}
									else {
										$result['errorMsg'] = 'Не удалось распознать адрес регистрации';
										$result['success'] = false;
										// fclose($f);
										return $result;
									}
								}
							break;

							case 'INDEX_P':
								if (!empty($value)) {
									$personSaveData['KLAdr_Index'] = $value;
								}
							break;
							
							case 'ELIMIN_DATE':
								if ( strlen($value) > 0 ) {
									$personSaveData['PersonIdentState_Code'] = 3;
								}
							case 'BESTBEFORE':
							case 'BORN_DATE':
							case 'GIV_DATE':
								if ( CheckDateFormat(substr($value, 0, 10)) == 0 ) {
									$personSaveData[$key] = ConvertDateFormat(substr($value, 0, 10));
								}
								else {
									$personSaveData[$key] = '';
								}
							break;

							case 'SEX':
								$sex_code = 0;

								if ( strtolower($value) == 'ж' ) {
									$sex_code = 2;
								}
								else if ( strtolower($value) == 'м' ) {
									$sex_code = 1;
								}

								$sexIdResponse = $this->getSexId($sex_code);

								if ( is_array($sexIdResponse) && count($sexIdResponse) > 0 && !empty($sexIdResponse[0]['Sex_id'])) {
									$personSaveData['Sex_id'] = $sexIdResponse[0]['Sex_id'];
								}
								else {
									$result['errorMsg'] = 'Не удалось определить идентификатор пола';
									$result['success'] = false;
									// fclose($f);
									return $result;
								}
							break;

							case 'SMO':
								if ( is_numeric($value) ) {
									$smoIdResponse = $this->getOrgSmoId($value);

									if ( is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
										$personSaveData['OrgSmo_id'] = $smoIdResponse[0]['OrgSmo_id'];
									}
									else {
										$result['errorMsg'] = 'Не удалось определить идентификатор СМО';
										$result['success'] = false;
										// fclose($f);
										return $result;
									}
								}
							break;

							case 'DOC_TYPE':
								$document_type_code = $this->getValidDocumentTypeCode($value);

								$documentTypeIdResponse = $this->getDocumentTypeId($document_type_code);

								if ( is_array($documentTypeIdResponse) && count($documentTypeIdResponse) > 0 && !empty($documentTypeIdResponse[0]['DocumentType_id'])) {
									$personSaveData['DocumentType_id'] = $documentTypeIdResponse[0]['DocumentType_id'];
								}
								else {
									$result['errorMsg'] = 'Не удалось определить идентификатор типа документа';
									$result['success'] = false;
									// fclose($f);
									return $result;
								}
							break;

							case 'SNILS':
								$personSaveData[$key] = str_replace(' ', '', str_replace('-', '', $value));
							break;

							case 'CATEG':
								$soc_status_sys_nick = $this->getValidSocStatusSysNick($value);

								$socStatusIdResponse = $this->getSocStatusId($soc_status_sys_nick);

								if ( is_array($socStatusIdResponse) && count($socStatusIdResponse) > 0 && !empty($socStatusIdResponse[0]['SocStatus_id'])) {
									$personSaveData['SocStatus_id'] = $socStatusIdResponse[0]['SocStatus_id'];
								}
								else {
									$result['errorMsg'] = 'Не удалось определить идентификатор социального статуса';
									$result['success'] = false;
									// fclose($f);
									return $result;
								}
							break;

							default:
								$personSaveData[$key] = $value;
							break;
						}
					}
				}

				if (!empty($personSaveData['KLAdr_Index'])) {
					$personSaveData['RAddress_Name'] = $personSaveData['KLAdr_Index'] . (!empty($personSaveData['RAddress_Name'])?', '.$personSaveData['RAddress_Name']:'');
				}
		
				// fwrite($f, "Сохранение данных\r\n");

				// Сохранение данных
				$response = $this->savePerson($personSaveData, $evnSetDT);

				if ( $response['success'] === false && strlen($response['errorMsg']) > 0 ) {
					$result['errorMsg'] = $response['errorMsg'];
					$result['success'] = false;
					// fclose($f);
					return $result;
				}
			}
			else {
				$result['errorMsg'] = 'Неверный ответ сервиса идентификации';
				$result['success'] = false;
				// fclose($f);
				return $result;
			}
		}
		else {
			$result['errorMsg'] = 'Ошибка при получении данных о пациенте';
			$result['success'] = false;
			// fclose($f);
			return $result;
		}

		// fclose($f);

		return $result;
	}


	/**
	 *	Сохранение изменившихся аттрибутов
	 *	$personSaveData @array данные после идентификации
	 *	$evnSetDT @string дата случая
	 */
	function savePerson($personSaveData, $evnSetDT = null) {
		@ini_set('max_execution_time', 0);

		$result = array(
			'errorMsg' => '',
			'success' => true
		);

		$omsSprTerrResponse = $this->getOMSSprTerrId(61);

		if ( is_array($omsSprTerrResponse) && count($omsSprTerrResponse) > 0 && !empty($omsSprTerrResponse[0]['OMSSprTerr_id'])) {
			$personSaveData['OMSSprTerr_id'] = $omsSprTerrResponse[0]['OMSSprTerr_id'];
		}
		else {
			$result['errorMsg'] = 'Не удалось определить идентификатор территории страхования';
			$result['success'] = false;
			return $result;
		}

		if ( in_array($personSaveData['PersonIdentState_Code'], array(1, 3)) ) {
			if ( empty($evnSetDT) ) {
				$personSaveData['Server_id'] = 0;
			}

			$commonData = array(
				'Server_id' => $personSaveData['Server_id'],
				'Person_id' => $personSaveData['Person_id'],
				'pmUser_id' => $personSaveData['pmUser_id']
			);

			$simplePersonAttributeList = array(
				1 => array('Object_Name' => 'PersonSurName', 'ObjectField_Name' => 'PersonSurName_SurName', 'ObjectField_Value' => (!empty($personSaveData['FAM']) ? $personSaveData['FAM'] : '')),
				2 => array('Object_Name' => 'PersonFirName', 'ObjectField_Name' => 'PersonFirName_FirName', 'ObjectField_Value' => (!empty($personSaveData['NAM']) ? $personSaveData['NAM'] : '')),
				3 => array('Object_Name' => 'PersonSecName', 'ObjectField_Name' => 'PersonSecName_SecName', 'ObjectField_Value' => (!empty($personSaveData['FNAM']) ? $personSaveData['FNAM'] : '')),
				4 => array('Object_Name' => 'PersonBirthDay', 'ObjectField_Name' => 'PersonBirthDay_BirthDay', 'ObjectField_Value' => (!empty($personSaveData['BORN_DATE']) ? $personSaveData['BORN_DATE'] : '')),
				5 => array('Object_Name' => 'PersonSex', 'ObjectField_Name' => 'Sex_id', 'ObjectField_Value' => $personSaveData['Sex_id']),
				6 => array('Object_Name' => 'PersonSnils', 'ObjectField_Name' => 'PersonSnils_Snils', 'ObjectField_Value' => (!empty($personSaveData['SNILS']) ? $personSaveData['SNILS'] : '')),
				7 => array('Object_Name' => 'PersonSocStatus', 'ObjectField_Name' => 'SocStatus_id', 'ObjectField_Value' => $personSaveData['SocStatus_id']),
				8 => array(),
				9 => array(),
				10 => array(),
				// 16 => array('Object_Name' => 'PersonPolisEdNum', 'ObjectField_Name' => 'PersonPolisEdNum_EdNum', 'ObjectField_Value' => (!empty($personSaveData['POL_NUM_16']) ? $personSaveData['POL_NUM_16'] : '')),
				19 => array('Object_Name' => 'PersonInn', 'ObjectField_Name' => 'PersonInn_Inn', 'ObjectField_Value' => (isset($personSaveData['INN']) ? $personSaveData['INN'] : ''))
			);

			// $f = fopen('ident.txt', 'a');
			// fwrite($f, "Сохранение данных\r\n");

			// Проверяем необходимость изменения "простых" аттрибутов
			foreach ( $simplePersonAttributeList as $key => $value ) {
				$response = $this->checkPersonAttributeChangeIsNecessary($key, $personSaveData, $evnSetDT);
				// fwrite($f, "[" . date('Y-m-d H:i:s') . "] Object_id: " . $key . "\r\n");

				if ( strlen($response['errorMsg']) > 0 ) {
					$result['errorMsg'] = $response['errorMsg'];
					$result['success'] = false;
					// fwrite($f, $response['errorMsg'] . "\r\n");
					// fclose($f);
					return $result;
				}

				if ( $response['isNecessary'] === true ) {
					$saveData = $commonData;

					$saveData['PersonEvn_insDT'] = $response['insDT'];

					switch ( $key ) {
						case 8:
							$saveData['OrgSmo_id'] = (!empty($personSaveData['OrgSmo_id']) ? $personSaveData['OrgSmo_id'] : NULL);
							$saveData['OMSSprTerr_id'] = (!empty($personSaveData['OMSSprTerr_id']) ? $personSaveData['OMSSprTerr_id'] : NULL);
							$saveData['POL_NUM_16'] = (!empty($personSaveData['POL_NUM_16']) ? $personSaveData['POL_NUM_16'] : NULL);
							$saveData['GIV_DATE'] = (!empty($personSaveData['GIV_DATE']) ? $personSaveData['GIV_DATE'] : NULL);
							$saveData['BESTBEFORE'] = (!empty($personSaveData['BESTBEFORE']) ? $personSaveData['BESTBEFORE'] : NULL);
							$saveData['PolisType_id'] = NULL;

							if ( !empty($saveData['POL_NUM_16']) ) {
								if ( strlen($saveData['POL_NUM_16']) == 9 ) {
									$saveData['PolisType_id'] = 3;
								}
								else if ( strlen($saveData['POL_NUM_16']) == 16 && substr($saveData['POL_NUM_16'], 3, 6) == substr(str_replace('-', '', $personSaveData['BORN_DATE']), 0, 6) ) {
									$saveData['PolisType_id'] = 1;
								}
								else {
									$saveData['PolisType_id'] = 4;
								}
							}

							$response = $this->processQueryResponse($this->savePersonPolis($saveData));
						break;

						case 9:
							$saveData['DocumentType_id'] = (!empty($personSaveData['DocumentType_id']) ? $personSaveData['DocumentType_id'] : NULL);
							$saveData['DOC_SER'] = (!empty($personSaveData['DOC_SER']) ? $personSaveData['DOC_SER'] : NULL);
							$saveData['DOC_NUM'] = (!empty($personSaveData['DOC_NUM']) ? $personSaveData['DOC_NUM'] : NULL);

							$response = $this->processQueryResponse($this->savePersonDocument($saveData));
						break;

						case 10:
							$saveData['KLRgn_id'] = (!empty($personSaveData['KLRgn_rid']) ? $personSaveData['KLRgn_rid'] : NULL);
							$saveData['KLSubRgn_id'] = (!empty($personSaveData['KLSubRgn_rid']) ? $personSaveData['KLSubRgn_rid'] : NULL);
							$saveData['KLCity_id'] = (!empty($personSaveData['KLCity_rid']) ? $personSaveData['KLCity_rid'] : NULL);
							$saveData['KLTown_id'] = (!empty($personSaveData['KLTown_rid']) ? $personSaveData['KLTown_rid'] : NULL);
							$saveData['KLStreet_id'] = (!empty($personSaveData['KLStreet_rid']) ? $personSaveData['KLStreet_rid'] : NULL);
							$saveData['KLAdr_Index'] = (!empty($personSaveData['KLAdr_Index']) ? $personSaveData['KLAdr_Index'] : NULL);
							$saveData['HOUSE'] = (!empty($personSaveData['HOUSE']) ? $personSaveData['HOUSE'] : NULL);
							$saveData['CORP'] = (!empty($personSaveData['CORP']) ? $personSaveData['CORP'] : NULL);
							$saveData['FLAT'] = (!empty($personSaveData['FLAT']) ? $personSaveData['FLAT'] : NULL);

							$response = $this->processQueryResponse($this->savePersonUAddress($saveData));
						break;

						default:
							$saveData['Object_Name'] = $value['Object_Name'];
							$saveData['ObjectField_Name'] = $value['ObjectField_Name'];
							$saveData['ObjectField_Value'] = $value['ObjectField_Value'];

							$response = $this->processQueryResponse($this->saveSimplePersonAttribute($saveData));
						break;
					}

					// fwrite($f, "done\r\n");

					if ( $response['success'] === false && strlen($response['errorMsg']) > 0 ) {
						$result['errorMsg'] = $response['errorMsg'];
						$result['success'] = false;
						// fclose($f);
						return $result;
					}
				}
			}

			if ( empty($evnSetDT) ) {
				$personIdentStateResponse = $this->getPersonIdentStateId($personSaveData['PersonIdentState_Code']);

				if ( is_array($personIdentStateResponse) && count($personIdentStateResponse) > 0 && !empty($personIdentStateResponse[0]['PersonIdentState_id'])) {
					$personSaveData['PersonIdentState_id'] = $personIdentStateResponse[0]['PersonIdentState_id'];
				}
				else {
					$result['errorMsg'] = 'Не удалось определить идентификатор статуса идентификации';
					$result['success'] = false;
					// fclose($f);
					return $result;
				}

				$personSaveData['Person_identDT'] = date('Y-m-d H:i:s');

				$response = $this->setPersonServer($personSaveData);
				$response = $this->setPersonIdentState($personSaveData);
			}

			// fclose($f);
		}

		return $result;
	}

	/**
	 *
	 * @param type $response
	 * @return boolean 
	 */
	function processQueryResponse($response) {
		$result = array(
			'errorMsg' => '',
			'success' => true
		);

		if ( !isset($response) || !is_array($response) || count($response) == 0 ) {
			$result['errorMsg'] = 'Ошибка при выполнении запроса к базе данных';
			$result['success'] = false;
		}
		else if ( array_key_exists('Error_Msg', $response[0]) && strlen($response[0]['Error_Msg']) > 0 ) {
			$result['errorMsg'] = $response[0]['Error_Msg'];
			$result['success'] = false;
		}

		return $result;
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function saveSimplePersonAttribute($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint;

			exec p_" . $data['Object_Name'] . "_ins
				@Server_id = :Server_id,
				@" . $data['Object_Name'] . "_id = @Res,
				@Person_id = :Person_id,
				@" . $data['Object_Name'] . "_insDT = :PersonEvn_insDT,
				@" . $data['ObjectField_Name'] . " = :ObjectField_Value,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @Res as PersonEvn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_insDT' => $data['PersonEvn_insDT'],
			'ObjectField_Value' => (!empty($data['ObjectField_Value']) ? $data['ObjectField_Value'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function savePersonDocument($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint;

			exec p_PersonDocument_ins
				@Server_id = :Server_id,
				@PersonDocument_id = @Res,
				@Person_id = :Person_id,
				@PersonDocument_insDT = :PersonEvn_insDT,
				@DocumentType_id = :DocumentType_id,
				@Document_Ser = :Document_Ser,
				@Document_Num = :Document_Num,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @Res as PersonEvn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_insDT' => $data['PersonEvn_insDT'],
			'DocumentType_id' => (!empty($data['DocumentType_id']) ? $data['DocumentType_id'] : NULL),
			'Document_Ser' => (!empty($data['DOC_SER']) ? $data['DOC_SER'] : NULL),
			'Document_Num' => (!empty($data['DOC_NUM']) ? $data['DOC_NUM'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function savePersonPolis($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint;

			exec p_PersonPolis_ins
				@Server_id = :Server_id,
				@PersonPolis_id = @Res,
				@Person_id = :Person_id,
				@PersonPolis_insDT = :PersonEvn_insDT,
				@PolisType_id = :PolisType_id,
				@OrgSmo_id = :OrgSmo_id,
				@OmsSprTerr_id = :OMSSprTerr_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Polis_begDate = :Polis_begDate,
				@Polis_endDate = :Polis_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @Res as PersonEvn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_insDT' => $data['PersonEvn_insDT'],
			'PolisType_id' => $data['PolisType_id'],
			'OrgSmo_id' => (!empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : NULL),
			'OMSSprTerr_id' => (!empty($data['OMSSprTerr_id']) ? $data['OMSSprTerr_id'] : NULL),
			'Polis_Ser' => NULL,
			'Polis_Num' => (!empty($data['POL_NUM_16']) ? $data['POL_NUM_16'] : NULL),
			'Polis_begDate' => (!empty($data['GIV_DATE']) ? $data['GIV_DATE'] : NULL),
			'Polis_endDate' => (!empty($data['BESTBEFORE']) ? $data['BESTBEFORE'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		if(!(empty($queryParams['Polis_endDate']) || $queryParams['Polis_endDate'] >= $queryParams['Polis_begDate'])){
			return false;
		}

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function savePersonUAddress($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint;

			exec p_PersonUAddress_ins
				@Server_id = :Server_id,
				@PersonUAddress_id = @Res,
				@Person_id = :Person_id,
				@PersonUAddress_insDT = :PersonEvn_insDT,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_Zip = :Address_Zip,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@Address_Address = :Address_Address,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @Res as PersonEvn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_insDT' => $data['PersonEvn_insDT'],
			'KLRgn_id' => (!empty($data['KLRgn_id']) ? $data['KLRgn_id'] : NULL),
			'KLSubRgn_id' => (!empty($data['KLSubRgn_id']) ? $data['KLSubRgn_id'] : NULL),
			'KLCity_id' => (!empty($data['KLCity_id']) ? $data['KLCity_id'] : NULL),
			'KLTown_id' => (!empty($data['KLTown_id']) ? $data['KLTown_id'] : NULL),
			'KLStreet_id' => (!empty($data['KLStreet_id']) ? $data['KLStreet_id'] : NULL),
			'Address_Zip' => (!empty($data['KLAdr_Index']) ? $data['KLAdr_Index'] : NULL),
			'Address_House' => (!empty($data['HOUSE']) ? $data['HOUSE'] : NULL),
			'Address_Corpus' => (!empty($data['CORP']) ? $data['CORP'] : NULL),
			'Address_Flat' => (!empty($data['FLAT']) ? $data['FLAT'] : NULL),
			'Address_Address' => (!empty($data['RAddress_Name']) ? $data['RAddress_Name'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Возвращает код соц. статуса из справочника SocStatus в Промед, соответствующий коду соц. статуса в ответе из фонда
	 *	$code @int код соц. статуса
	 */
	function getValidSocStatusSysNick($code) {
		$result = 0;

		switch ( $code ) {
			case 1: $result = 'rab'; break; // работающий
			case 2: $result = 'pen'; break; // пенсионер
			case 3: $result = 'nrab'; break; // иждивенец
			case 4: $result = 'nrab'; break; // безработный
			case 5: $result = 'nrab'; break; // неработающий
			case 6: $result = 'study'; break; // учащийся
		}

		return $result;
	}


	/**
	 *	Возвращает код типа документа из справочника DocumentType в Промед, соответствующий коду типа документа в ответе из фонда
	 *	$code @int код типа документа
	 */
	function getValidDocumentTypeCode($code) {
		$result = 0;

		// 2012-02-24 Смена соответствия кодов типов документов
		// https://redmine.swan.perm.ru/issues/8577
		switch ( $code ) {
			case 1: $result = 1; break; // Паспорт гражданина СССР [new]
			case 2: $result = 3; break; // Свидетельство о рождении [3]
			case 3: $result = 14; break; // Паспорт гражданина россии [6]
			case 4: $result = 13; break; // Временное удостоверение личности гражданина РФ [new]
			case 5: $result = 9; break; // Иностранный паспорт [11]
			case 7: $result = 7; break; // Военный билет солдата (матроса, сержанта, старшины) [12]
			case 10: $result = 5; break; // Справка об освобождении из места лишения свободы [new]
			case 12: $result = 11; break; // Вид на жительство [18]
			case 13: $result = 12; break; // Удостоверение беженца РФ [19]
			case 16: $result = 15; break; // Заграничный паспорт [new]
			case 23: $result = 18; break; // Иные документы [21]
			// case 25: $result = 18; break; // Вид на жительство лица без гражданства
			case 99: $result = 18; break; // Свидетельство о рождении иностр. государства [21]
		}

		return $result;
	}


	/**
	 *	Возвращает id СМО по наименованию
	 */
	function getOrgSmoIdByName($OrgSmo_NameTfoms) {
	
		if (empty($OrgSmo_NameTfoms)) {
			return false;
		}
	
		$query = "
			select [OrgSMO_id] 
			from [r10].[v_OrgSmoLink]
			where [OrgSmo_NameTfoms] LIKE :OrgSmo_NameTfoms
		";

		$queryParams = array(
			'OrgSmo_NameTfoms' => $OrgSmo_NameTfoms
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( count($res) > 0 ) {
				return $res[0]['OrgSMO_id'];
			} else {
				return false;
			}
		}
		else {
			return false;
		}		
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function AstraPersonIdentRequest($data){
		
		$identObject = new swPersonIdentAstrahan(
			$this->config->item('IDENTIFY_SERVICE_URI')
		);
		
		$val  = array();
		$identDT = time();

		// Формирование данных для запроса к сервису БДЗ Астрахани (ФИО и ДР)
		$requestData = array(
			'l_f' => mb_ucfirst(mb_strtolower($data['Person_SurName'])),
			'l_i' => mb_ucfirst(mb_strtolower($data['Person_FirName'])),
			'l_o' => mb_ucfirst(mb_strtolower($data['Person_SecName'])),
			'l_dr' => (!empty($data['Person_Birthday']) ?  $data['Person_Birthday'] : '1900-01-01'),
			'l_s_polis' => $data['Polis_Ser'],
			'l_n_polis' => $data['Polis_Num'],
			'polistype' => (isset($data['PolisType_id']))?$data['PolisType_id']:null,
			'l_ss' => $data['Person_SNILS'], 
			'date' => (!empty($data['PersonIdentOnDate']) ? $data['PersonIdentOnDate'] : date('Y-m-d')),
			'actual' => (empty($data['PersonIdentOnDate'])),
			'full'=>(isset($data['full']))?true:false
		);

		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		if ( $requestResponse['success'] === false ) { 
			//echo json_return_errors($requestResponse['errorMsg']);
			$val['Error_Msg'] = $requestResponse['errorMsg'];
			return $val;
			//array_walk($val, 'ConvertFromWin1251ToUTF8');
			//return false;
		}

		// Полученные данные
		$personData = $requestResponse['identData'];

		// Если идентифицирован...
		if ( is_array($personData) ) {
			if ( !empty($personData['sn_pol']) ) { 
				// ... то формируем данные для подстановки на форму редактирования
				$val['Person_identDT'] = $identDT;
				$val['PersonIdentState_id'] = 1;
				$val['Server_id'] = 0;
				// Астрахань -> в Уфимский формат :) 
				$map = array(
					/*
					'FAM' => 'FAM',
					'IM' => 'NAM',
					'OT' => 'FNAM',
					'sex' => 'SEX',
					'birthDate' => 'BORN_DATE',
					*/
					'polis_ser' => 'POL_SER',
					'polis_num' => 'POL_NUM_16',
					'datapp' => 'GIV_DATE', 
					'datape' => 'ELIMIN_DATE'
				);
				
				foreach ( $personData as $key => $value ) {
					switch ($key) {
						case 'datape': // обработка дат
							if ( mb_strlen($value) > 0 && mb_substr($value, 0, 10)!="1899-12-30" ) {
								$val['PersonIdentState_id'] = 3;
								$val['Server_id'] = $data['Server_id'];
							}
						case 'datapp': 
							$d = mb_substr($value, 0, 10);
							if ($d == "1899-12-30") { // сервис вместо пустой даты возвращает такое безобразие
								$val[$map[$key]] = null;
							} else {
								$val[$map[$key]] = ConvertDateEx(mb_substr($value, 0, 10), "-", ".");
							}
						break;

						case 'vid_pol': // тип полиса 
							if ( $value=='Старый' ) {
								$val['PolisType_id'] = 1;
							}
							if ( $value=='Временный' ) {
								$val['PolisType_id'] = 3;
							}
							if ( $value=='Новый' ) {
								$val['PolisType_id'] = 4;
							}
						break;

						case 'sk': // страховая компания
							if ( is_numeric($value) ) {
								// Прямая стыковка, поскольку стыковочной таблицы нет
								// 8000422 МАКС - М
								// 8000018 СОГАЗ - МЕД
								// 68320077752 РОСНО
								if ($value==7) {
									$val['OrgSmo_id'] = 8000018;
								} elseif ($value==15) {
									$val['OrgSmo_id'] = 8000422;
								} else {
									$val['Error_Msg'] = 'По данным Фонда у пациента нет действующего полиса.';
								}
							}
						break;
						case 'lpu':
							$res = $this->db->query("select Lpu_Nick from v_Lpu with (nolock) where Lpu_f003mcod = ?", array($value));
							if ( is_object($res) ){
								$res=$res->result('array');
								$val[$map[$key]] = $res[0]['Lpu_Nick'];
							}
						break;

						/*
						case 'snils': // снилс
							$val[$map[$key]] = str_replace(array(' ', '-'), '', $value);
						break;
						
						case 'sex': // обработка пола 
							if ( mb_strtolower($value) == 'ж' ) {
								$val['Sex_Code'] = 2;
							}
							else if ( mb_strtolower($value) == 'м' ) {
								$val['Sex_Code'] = 1;
							}
						break;
						*/
						default:
							if (isset($map[$key])) {
								$val[$map[$key]] = $value;
							} else {
								$val[$key] = $value;
							}
							
						break;
					}
				}
			} else {
				// такая ошибка может быть очень иногда, когда сервис идентификации вернул данные, а фамилия пустая или ответ есть, а идентифицированных записей нет 
				// (согласно спецификации если ничего не нашли, то ответ сервиса пустой, значит проверка отработает выше, а это нештатная ситуация)
				$val['Error_Msg'] = 'Ошибка сервиса идентификации или по указанным данным человек не идентифицирован: '.var_export($personData, true); 
				$val['PersonIdentState_id'] = 2;
			}
		}
		else {
			// такое вряд ли будет 
			$val['Error_Msg'] = 'Неверный ответ сервиса идентификации: '.var_export($personData, true);
		}
		if (in_array($val['PersonIdentState_id'], array(1,3))) {
			$val['Person_IsInErz'] = 2;
		} else {
			$val['Person_IsInErz'] = 1;
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		return $val;

	}
	
	/**
	 *
	 * @param type $val
	 * @param type $item 
	 */
	function savePersonIdentPolis($val,$item){
		$Polis_begDate = empty($val['GIV_DATE']) ? NULL : date('Y-m-d', strtotime($val['GIV_DATE']));
		$Polis_closeDate = empty($val['GIV_DATE']) ? NULL : date('Y-m-d', strtotime($val['GIV_DATE'] . "-1 days"));
		$Polis_endDate = empty($val['ELIMIN_DATE']) ? NULL : date('Y-m-d', strtotime($val['ELIMIN_DATE']));
		$OmsSprTerr_id = 253;//bad
		$PolisType_id = (empty($val['PolisType_id']) ? NULL : $val['PolisType_id']);
		$OrgSmo_id = (empty($val['OrgSmo_id']) ? NULL : $val['OrgSmo_id']);
		$Polis_Ser = (empty($val['POL_SER']) ? '' : $val['POL_SER']);
		$Polis_Num = (empty($val['POL_NUM_16']) ? '' : $val['POL_NUM_16']);
		$params=array();
		$Federal_Num = NULL;
		if ($PolisType_id == 4) {
			$Federal_Num = $Polis_Num;
		}

		$query = "
			select top 1
				count(PP.Polis_id) as cnt
			from v_PersonPolis PP
			where
				PP.Person_id = :Person_id
				and PP.PolisType_id = :PolisType_id
				and isnull(PP.Polis_Ser,'') = isnull(:Polis_Ser,'')
				and PP.Polis_Num = :Polis_Num
				and PP.Polis_begDate = :Polis_begDate
		";
		$params = array(
			'Person_id' => $item['Person_id'],
			'PolisType_id' => $PolisType_id,
			'Polis_Ser' => $Polis_Ser,
			'Polis_Num' => $Polis_Num,
			'Polis_begDate' => $Polis_begDate
		);
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			return false;
		}
		if ($cnt > 0) {	//Полис уже существует
			return true;
		}

		$queryParams = array(
			'Polis_begDate' => $Polis_begDate,
			'Polis_endDate' => $Polis_endDate,
			'Polis_closeDate' => $Polis_closeDate
		);
		$proc='ins';
		if($item['Polis_id']!=NULL){
			if(
				$item['PolisType_id']==$PolisType_id&&
				($item['Polis_Ser']==$Polis_Ser||(empty($item['Polis_Ser'])&&empty($Polis_Ser)))&&
				($item['Polis_Num']==$Polis_Num||(empty($item['Polis_Num'])&&empty($Polis_Num)))&&
				$item['OrgSmo_id']==$OrgSmo_id
			) {
				$proc = 'upd';
			} else {
				if (!empty($item['Polis_begDate']) && strtotime($item['Polis_begDate'])>=strtotime($Polis_begDate)) {
					$proc = 'upd';	//Обновление текущего полиса
				} else {
					$proc = 'ins';	//Добавление нового полиса

					//Закрытие предыдущего полиса
					$sql = "
						declare @ErrCode int,
							@PersonPolis_id bigint,
							@ErrMsg varchar(400);
						set @PersonPolis_id = :PersonPolis_id;
						exec p_PersonPolis_upd
							@PersonPolis_id = @PersonPolis_id output,
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@OmsSprTerr_id = :OmsSprTerr_id,
							@PolisType_id = :PolisType_id,
							@OrgSmo_id = :OrgSmo_id,
							@Polis_Ser = :Polis_Ser,
							@Polis_Num = :Polis_Num,
							@Polis_begDate = :Polis_begDate,
							@Polis_endDate = :Polis_endDate,
							@PersonPolis_insDT = :Polis_begDate,
							@pmUser_id = 1,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @PersonPolis_id as PersonPolis_id;
					";
					$queryParams = array_merge($item, array(
						'Polis_begDate' => !empty($item['Polis_begDate'])?$item['Polis_begDate']:'2000-01-02',
						'Polis_endDate' => $Polis_closeDate
					));
					if(empty($queryParams['Polis_endDate']) || $queryParams['Polis_endDate'] >= $queryParams['Polis_begDate']){
						$this->queryResult($sql, $queryParams);
					}
				}
			}
		}
		$params = array(
			'PersonEvn_id'=>null,
			'Polis_id'=>$item['Polis_id'],
			'Server_id'=>0,
			'Person_id'=>$item['Person_id'],
			'OmsSprTerr_id'=>$OmsSprTerr_id,
			'PolisType_id'=>$PolisType_id,
			'OrgSmo_id'=>$OrgSmo_id,
			'Polis_Ser'=>$Polis_Ser,
			'Polis_Num'=>$Polis_Num,
			'Polis_begDate'=>$Polis_begDate,
			'Polis_endDate'=>$Polis_endDate
		);
		
		if($Federal_Num>0&&$Federal_Num!=$item['Person_EdNum']){
			$query = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)
				exec p_PersonPolisEdNum_ins
					@Server_id = 0,
					@Person_id = :Person_id,
					@PersonPolisEdNum_insDT = :Polis_begDate,
					@PersonPolisEdNum_EdNum = :Polis_Num,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrMsg as ErrMsg
			";
			$this->db->query($query, $params);
		}
		if(empty($params['Polis_endDate']) || $params['Polis_endDate']>=$params['Polis_begDate']){
			if ($proc=='ins') {
				$query="
					declare @ErrCode int
					declare @ErrMsg varchar(400)
					declare @Res bigint = :PersonEvn_id
					exec p_PersonPolis_ins
						@PersonPolis_id = @Res output,
						@Server_id = :Server_id,
						@Person_id = :Person_id,
						@OmsSprTerr_id = :OmsSprTerr_id,
						@PolisType_id = :PolisType_id,
						@OrgSmo_id = :OrgSmo_id,
						@Polis_Ser = :Polis_Ser,
						@Polis_Num = :Polis_Num,
						@PersonPolis_insDT = :Polis_begDate,
						@Polis_begDate = :Polis_begDate,
						@Polis_endDate = :Polis_endDate,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
					select @Res as PersonEvn_id, @ErrMsg as ErrMsg
				";

				$res = $this->db->query($query, $params);
				if(is_object($res)){
					$res = $res->result('array');
					$sql='exec [dbo].[xp_PersonTransferEvn] 
						@Person_id = :Person_id';
					$this->db->query($sql, array('Person_id'=>$item['Person_id']));
				}
			} else {
				$query = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)

					exec p_Polis_upd
					@Server_id = 0,
					@Polis_id = :Polis_id ,
					@OmsSprTerr_id = :OmsSprTerr_id,
					@PolisType_id = :PolisType_id,
					@OrgSmo_id = :OrgSmo_id,
					@Polis_Ser = :Polis_Ser,
					@Polis_Num = :Polis_Num,
					@Polis_begDate = :Polis_begDate,
					@Polis_endDate = :Polis_endDate,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($query, $params);
				$sql='select PersonPolis_id as PersonEvn_id from v_personPolis with(nolock) where polis_id = :Polis_id';
				$res = $this->db->query($sql, array('Polis_id'=>$item['Polis_id']));
				$result = $res->result('array');
				foreach($result as $val){
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec xp_PersonTransferDate
							@Server_id = 0,
							@PersonEvn_id = ?,
							@PersonEvn_begDT = ?,
							@pmUser_id = 1,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";

					if ($val['PersonEvn_id'] > 0) {
						$res = $this->db->query($sql, array($val['PersonEvn_id'], $Polis_begDate));
					}
				}
				
			}
		}
		return true;
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function PersonIdentPackage(){
		$this->load->library('textlog', array('file' => 'PersonIdentPackage.log'));
		$this->textlog->add('-------------------------------------------------------');
		$this->textlog->add('>>>>>>>>>>>>>>>>>>Запускаем задание<<<<<<<<<<<<<<<<<<<<');
		$this->textlog->add('-------------------------------------------------------');
		$PersonIdentPackage_id=null;
		$query="
			select top 1000 
				Evn_id,Person_id,PersonIdentPackagePos_id
			from PersonIdentPackagePos with(nolock)
			where PersonIdentPackage_id is null
			";
		$PersonArr = array();
		$result = $this->db->query($query, array());
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( count($res) > 0 ) {
				$query = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)
				declare @PersonIdentPackage_id bigint = null
				exec p_PersonIdentPackage_ins
				@PersonIdentPackage_id = @PersonIdentPackage_id output,
				@PersonIdentPackage_Name = 'PersonIdentPackage_Name',
				@PersonIdentPackage_begDate = :PersonIdentPackage_begDate,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @PersonIdentPackage_id as PersonIdentPackage_id, @ErrMsg as ErrMsg
			";
				$result = $this->db->query($query, array('PersonIdentPackage_begDate'=>date('Y-m-d')));
				if ( is_object($result) ) {
					$result = $result->result('array');
					$PersonIdentPackage_id=$result[0]['PersonIdentPackage_id'];
				}else{
					return array(array('errorMsg'=> 'Запрос не вернул объект.','success'=>false));
				}
				foreach($res as $item){
					if($item['Evn_id']==null){
						$sql = "update PersonIdentPackagePos set PersonIdentPackage_id = :PersonIdentPackage_id where PersonIdentPackagePos_id = :PersonIdentPackagePos_id";
						$this->db->query($sql, array('PersonIdentPackagePos_id'=>$item['PersonIdentPackagePos_id'] ,'PersonIdentPackage_id'=>$PersonIdentPackage_id));
						continue;
					}
					if(in_array($item['Person_id'], $PersonArr)){
						$sql = "update PersonIdentPackagePos set PersonIdentPackage_id = :PersonIdentPackage_id where PersonIdentPackagePos_id = :PersonIdentPackagePos_id";
						$this->db->query($sql, array('PersonIdentPackagePos_id'=>$item['PersonIdentPackagePos_id'] ,'PersonIdentPackage_id'=>$PersonIdentPackage_id));
						continue;
					}else{
						$PersonArr[]=$item['Person_id'];
					}
					$query="
						select top 1
							p.Person_SurName,
							p.Person_FirName,
							p.Person_SecName,
							CONVERT(varchar(10),p.Person_Birthday,120) as Person_Birthday,
							pol.Server_id,
							pol.Polis_Ser,
							pol.Polis_Num,
							pol.PolisType_id,
							pol.OmsSprTerr_id,
							p.Person_SNILS,
							pol.OrgSmo_id,
							p.PersonEvn_id,
							p.Person_id,
							e.Evn_id,
							pol.Polis_id,
							pol.PersonPolis_id,
							p.Person_EdNum,
							CONVERT(varchar(10),pol.Polis_begDate, 120) as Polis_begDate,
							CONVERT(varchar(10),pol.Polis_endDate, 120) as Polis_endDate
						from
							v_Person_all p with(nolock)
							inner join v_Evn e with(nolock) on e.PersonEvn_id = p.PersonEvn_id and p.Server_id = e.Server_id
							left join v_PersonPolis pol with (nolock) on pol.Polis_id = p.Polis_id
						where
							e.Evn_id=:Evn_id
							and p.Person_id = :Person_id
					";
					$result = $this->db->query($query, array('Evn_id'=>$item['Evn_id'],'Person_id'=>$item['Person_id']));
					if ( is_object($result) ) {
						$good = true;
						$res = $result->result('array');
						try{
							if(count($res)==1){
								$this->textlog->add('Производим идентификацию '.$res[0]['Person_SurName'].' '.$res[0]['Person_FirName']);
								$val = $this->AstraPersonIdentRequest($res[0]);
								if ( isset($val['Error_Msg']) && strlen($val['Error_Msg']) > 0 ) {
									$this->textlog->add('Ошибка '.$res[0]['Person_SurName'].' '.$res[0]['Person_FirName'].' - '.$val['Error_Msg']);
									$good = false;
								}
							}else{
								$good = false;
							}
						}
						catch(Exception $e){
							$this->textlog->add('Ошибка '.$res[0]['Person_SurName'].' '.$res[0]['Person_FirName'].' - '.$e->getTraceAsString());
							$good = false;
						}
						if($good){
							$this->textlog->add('Сохраняем полис '.$res[0]['Person_SurName'].' '.$res[0]['Person_FirName']);
							$this->savePersonIdentPolis($val,$res[0]);
						}
						if (isset($val['Person_IsInErz']) && isset($res[0]) && !empty($res[0]['Person_id'])) {
							$params = array('Person_IsInErz' => $val['Person_IsInErz'], 'Person_id' => $res[0]['Person_id']);
							$sql = "update Person with(rowlock) set Person_IsInErz = :Person_IsInErz where Person_id = :Person_id";
							$this->db->query($sql, $params);
						}
						$sql = "update PersonIdentPackagePos set PersonIdentPackage_id = :PersonIdentPackage_id where PersonIdentPackagePos_id = :PersonIdentPackagePos_id";
						$this->db->query($sql, array('PersonIdentPackagePos_id'=>$item['PersonIdentPackagePos_id'] ,'PersonIdentPackage_id'=>$PersonIdentPackage_id));
					}
				}
			} else {
				$this->textlog->add('Кол-во элементов в массиве равно нулю');
				return array(array('success'=>true));
			}
		}
		else {
			return array(array('errorMsg'=> 'Запрос не вернул объект.','success'=>false));
		}
		$this->textlog->add('-------------------------------------------------------');
		$this->textlog->add('>>>>>>>>>>>>>>>>>>>>Конец задания<<<<<<<<<<<<<<<<<<<<<<');
		$this->textlog->add('-------------------------------------------------------');
		return array(array('success'=>true));
	}
}
