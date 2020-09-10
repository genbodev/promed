<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class SearchPersonService_model
 * Модель для работы с soap сервисом поиска пациентов
 */
class SearchPersonService_model extends swModel
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $message
     * Поиск пациента
     */
    public function SearchPersonService($message)
    {
        $this->load->model('Person_model');

        $params = array();

        //Поиск в порядке приоритета параметров
        switch (true) {
            case isset($message->personIdentifiers->insuranceDocument):

                $params['DocumentType_code'] = isset($message->personIdentifiers->insuranceDocument->type) ? $message->personIdentifiers->insuranceDocument->type : null;
                if ($params['DocumentType_code'] == 4) {
                    $params['Polis_EdNum'] = isset($message->personIdentifiers->insuranceDocument->number) ? $message->personIdentifiers->insuranceDocument->number : null;
                } else {
                    $params['Polis_Num'] = isset($message->personIdentifiers->insuranceDocument->number) ? $message->personIdentifiers->insuranceDocument->number : null;

                }
                $params['Polis_Ser'] = isset($message->personIdentifiers->insuranceDocument->serial) ? $message->personIdentifiers->insuranceDocument->serial : null;
                $params['Org_Code'] = isset($message->personIdentifiers->insuranceDocument->organization->externalCode) ? $message->personIdentifiers->insuranceDocument->organization->externalCode : null;
                $params['OrgSmo_id'] = (int)isset($message->personIdentifiers->insuranceDocument->organization->externalID) ? $message->personIdentifiers->insuranceDocument->organization->externalID : null;
                $params['Orgsmo_f002smocod'] = isset($message->personIdentifiers->insuranceDocument->organization->nsiCode) ? $message->personIdentifiers->insuranceDocument->organization->nsiCode : null;

                $Persons = $this->Person_model->findPersonByParams('Polis', $params);

                break;
            case isset($message->personIdentifiers->documents):
                $params['DocumentType_Code'] = isset($message->personIdentifiers->documents->type) ? $message->personIdentifiers->documents->type : null;
                $params['Document_Ser'] = isset($message->personIdentifiers->documents->serial) ? $message->personIdentifiers->documents->serial : null;
                $params['Document_Num'] = isset($message->personIdentifiers->documents->number) ? $message->personIdentifiers->documents->number : null;
                $params['OrgDep_id'] = (int)isset($message->personIdentifiers->documents->organization->systemID) ? $message->personIdentifiers->documents->organization->systemID : null;
                $params['Org_Code'] = isset($message->personIdentifiers->documents->organization->externalCode) ? $message->personIdentifiers->documents->organization->externalCode : null;

                $Persons = $this->Person_model->findPersonByParams('Document', $params);

                break;
            case isset($message->personIdentifiers->snilsDocument):
                $params['Person_Snils'] = isset($message->personIdentifiers->snilsDocument->number) ? $message->personIdentifiers->snilsDocument->number : null;
                $Persons = $this->Person_model->findPersonByParams('Snils', $params);

                break;
            case !empty($message->personIdentifiers->personUniqID):
                $params['Person_id'] = $message->personIdentifiers->personUniqID;
                $Persons = $this->Person_model->getPersonCombo($params);

                break;
            case isset($message->surname) || isset($message->name) || isset($message->patronymic) ||
                isset($message->gender) || isset($message->birthday):

                $params['Person_SurName'] = isset($message->surname) ? $message->surname : null;
                $params['Person_FirName'] = isset($message->name) ? $message->name : null;
                $params['Person_SecName'] = isset($message->patronymic) ? $message->patronymic : null;
                $params['Person_BirthDay'] = isset($message->birthday) ? $message->birthday : null;
                $params['Sex_id'] = (int)isset($message->gender) ? $message->gender : null;

                $Persons = $this->Person_model->findPersonByParams('Fio', $params);

                break;
            default:
                throw new Exception("Не хватает данных для поиска");


        }

        if(is_array($Persons) && count($Persons) > 0){

            //Сбор person_id для фильтра в запросах
            $PersonsList = array();
            foreach($Persons as $person){
                $PersonsList[] = $person['Person_id'];
            }

            //Основные поля пациентов
            $sql = "
            SELECT
                ps.Person_SurName as surname
                ,ps.Person_FirName as name
                ,ps.Person_SecName as patronymic
                ,ps.Sex_id as gender
                ,convert(varchar(10), ps.Person_BirthDay, 120) as birthday
                ,ps.Person_id as personUniqID

                ,dt.DocumentType_Code as d_type
                ,d.Document_Ser as d_serial
                ,d.Document_Num as d_number
                ,CASE WHEN d.Document_endDate is null THEN 'true' ELSE 'false' END as d_isActive
                ,convert(varchar(10), d.Document_begDate, 120) as d_dateBegin
                ,convert(varchar(10), d.Document_endDate, 120) as d_dateEnd
                ,o_od.Org_Code as d_externalCode
                ,od.OrgDep_id as d_externalID
                ,null as d_nsiCode

                ,pt.PolisType_CodeF008 as p_type
                ,p.Polis_Ser as p_serial
                ,p.Polis_Num as p_number
                ,CASE WHEN p.Polis_endDate is null or p.Polis_endDate < dbo.tzGetDate() THEN 'true' ELSE 'false' END  as p_isActive
                ,convert(varchar(10), p.Polis_begDate, 120) as p_dateBegin
                ,convert(varchar(10), p.Polis_endDate, 120) as p_dateEnd
                ,o_os.Org_Code as p_externalCode
                ,os.OrgSmo_id as p_externalID
                ,os.Orgsmo_f002smocod as p_nsiCode

                ,null as s_type
                ,ps.Person_Snils as s_number

                ,pA_rgn.KLRgn_Name as regA_region
                ,pA_rgnArea.KLAdr_Code as regA_regionKladr
                ,CASE WHEN pA_town.KLTown_id is not null THEN pA_town.KLTown_Name ELSE pA_city.KLCity_Name END as regA_locality
                ,CASE WHEN pA_town.KLTown_id is not null THEN pA_townArea.KLAdr_Code ELSE pA_cityArea.KLAdr_Code END regA_localityKladr
                ,pA_country.KLCountry_Name as regA_country
                ,pA_countryArea.KLAdr_Code as regA_countryKladr
                ,pA_street.KLStreet_Name as regA_street
                ,pA_streetArea.KLAdr_Code as regA_streetKladr
                ,pA.Address_Address as regA_geofull
                ,pA.Address_House as regA_house
                ,pA.Address_Corpus as regA_houseBlock
                ,pA.Address_Flat as regA_apartment
                ,pA.Address_Zip as regA_postIndex

                ,uA_rgn.KLRgn_Name as resA_region
                ,uA_rgnArea.KLAdr_Code as resA_regionKladr
                ,CASE WHEN uA_town.KLTown_id is not null THEN uA_town.KLTown_Name ELSE uA_city.KLCity_Name END as resA_locality
                ,CASE WHEN uA_town.KLTown_id is not null THEN uA_townArea.KLAdr_Code ELSE uA_cityArea.KLAdr_Code END resA_localityKladr
                ,uA_country.KLCountry_Name as resA_country
                ,uA_countryArea.KLAdr_Code as resA_countryKladr
                ,uA_street.KLStreet_Name as resA_street
                ,uA_streetArea.KLAdr_Code as resA_streetKladr
                ,uA.Address_Address as resA_geofull
                ,uA.Address_House as resA_house
                ,uA.Address_Corpus as resA_houseBlock
                ,uA.Address_Flat as resA_apartment
                ,uA.Address_Zip as resA_postIndex


            FROM v_PersonState ps (nolock)
                left join v_Document d (nolock) on d.Document_id = ps.Document_id
                left join v_DocumentType dt (nolock) on dt.DocumentType_id = d.DocumentType_id
                left join v_OrgDep od (nolock) on od.OrgDep_id = d.OrgDep_id
                left join v_Org o_od (nolock) on od.Org_id = o_od.Org_id
                left join v_Polis p (nolock) on p.Polis_id = ps.Polis_id
                left join v_PolisType pt (nolock) on p.PolisType_id = pt.PolisType_id
                left join v_OrgSmo os (nolock) on os.OrgSmo_id = p.OrgSmo_id
                left join v_Org o_os (nolock) on os.Org_id = o_os.Org_id

                left join v_Address pA (nolock) on pA.Address_id = ps.PAddress_id
                left join v_KLRgn pA_rgn (nolock) on pA.KLRgn_id = pA_rgn.KLRgn_id
                left join KlArea pA_rgnArea (nolock) on pA_rgn.KLRgn_id = pA_rgnArea.KLArea_id
                left join v_KLCity pA_city (nolock) on pA.KLCity_id = pA_city.KLCity_id
                left join KlArea pA_cityArea (nolock) on pA_city.KLCity_id = pA_cityArea.KLArea_id
                left join v_KLTown pA_town (nolock) on pA.KLTown_id = pA_town.KLTown_id
                left join KlArea pA_townArea (nolock) on pA_town.KLTown_id = pA_townArea.KLArea_id
                left join v_KLCountry pA_country (nolock) on pA.KLCountry_id = pA_country.KLCountry_id
                left join KlArea pA_countryArea (nolock) on pA_country.KLCountry_id = pA_countryArea.KLArea_id
                left join v_KLStreet pA_street (nolock) on pA.KLStreet_id = pA_street.KLStreet_id
                left join KlArea pA_streetArea (nolock) on pA_street.KLStreet_id = pA_streetArea.KLArea_id

                left join v_Address uA (nolock) on uA.Address_id = ps.UAddress_id
                left join v_KLRgn uA_rgn (nolock) on uA.KLRgn_id = uA_rgn.KLRgn_id
                left join KlArea uA_rgnArea (nolock) on uA_rgn.KLRgn_id = uA_rgnArea.KLArea_id
                left join v_KLCity uA_city (nolock) on uA.KLCity_id = uA_city.KLCity_id
                left join KlArea uA_cityArea (nolock) on uA_city.KLCity_id = uA_cityArea.KLArea_id
                left join v_KLTown uA_town (nolock) on uA.KLTown_id = uA_town.KLTown_id
                left join KlArea uA_townArea (nolock) on uA_town.KLTown_id = uA_townArea.KLArea_id
                left join v_KLCountry uA_country (nolock) on uA.KLCountry_id = uA_country.KLCountry_id
                left join KlArea uA_countryArea (nolock) on uA_country.KLCountry_id = uA_countryArea.KLArea_id
                left join v_KLStreet uA_street (nolock) on uA.KLStreet_id = uA_street.KLStreet_id
                left join KlArea uA_streetArea (nolock) on uA_street.KLStreet_id = uA_streetArea.KLArea_id

            WHERE ps.Person_id in (" . implode(',' ,$PersonsList) . ")
            ";

            $res = $this->db->query($sql);
            $arRes = $res->result('array');


            //Лекарственная непереносимость
            $additionalInfoItemDrugSql = "
                SELECT dm.DrugMnn_Name, par.Person_id
                FROM v_PersonAllergicReaction par (nolock)
                left join v_DrugMnn dm (nolock) on par.DrugMnn_id = dm.DrugMnn_id
                WHERE par.Person_id in (" . implode(',' ,$PersonsList) . ")
            ";
            $additionalInfoItemDrugRes = $this->db->query($additionalInfoItemDrugSql);
            $additionalInfoItemDrug = $additionalInfoItemDrugRes->result('array');

            //Аллергические реакции
            $additionalInfoItemPARSql = "
                SELECT PersonAllergicReaction_Kind,Person_id
                FROM v_PersonAllergicReaction par (nolock)
                WHERE par.Person_id in (" . implode(',' ,$PersonsList) . ") and par.DrugMnn_id is null
            ";
            $additionalInfoItemPARRes = $this->db->query($additionalInfoItemPARSql);
            $additionalInfoItemPAR = $additionalInfoItemPARRes->result('array');

            //Уточненные диагнозы
            $additionalInfoItemDiagSql = "
                SELECT D.Diag_Code + ': ' + Diag_Name as Diag, Person_id
                FROM v_EvnDiagSpec eds (nolock)
                left join v_Diag D (nolock) on (eds.Diag_id = D.Diag_id)
                WHERE Person_id in (" . implode(',' ,$PersonsList) . ")
                union
                SELECT D.Diag_Code + ': ' + Diag_Name as Diag, Person_id
                FROM v_EvnSection es (nolock)
                left join v_Diag D (nolock) on (es.Diag_id = D.Diag_id)
                WHERE Person_id in (" . implode(',' ,$PersonsList) . ")
                union
                SELECT D.Diag_Code + ': ' + Diag_Name as Diag, Person_id
                FROM v_EvnDiagPS edp (nolock)
                left join v_Diag D (nolock) on (edp.Diag_id = D.Diag_id)
                WHERE Person_id in (" . implode(',' ,$PersonsList) . ")
                union
                SELECT D.Diag_Code + ': ' + Diag_Name as Diag, Person_id
                FROM v_EvnVizitPL evp (nolock)
                left join v_Diag D (nolock) on (evp.Diag_id = D.Diag_id)
                WHERE Person_id in (" . implode(',' ,$PersonsList) . ")
                union
                SELECT D.Diag_Code + ': ' + Diag_Name as Diag, Person_id
                FROM v_EvnDiagPLSop edps (nolock)
                left join v_Diag D (nolock) on (edps.Diag_id = D.Diag_id)
                WHERE Person_id in (" . implode(',' ,$PersonsList) . ")
            ";

            $additionalInfoItemDiagRes = $this->db->query($additionalInfoItemDiagSql);
            $additionalInfoItemDiag = $additionalInfoItemDiagRes->result('array');

            //Формируем структуру ответа
            $resp = new stdClass();
			$resp->searchedResponseServicesWrap = new stdClass();
			$resp->searchedResponseServicesWrap->searchResponseWrap = new stdClass();
            $resp->searchedResponseServicesWrap->searchResponseWrap->providerName = 'РМИС';

            $resp->searchedResponseServicesWrap->searchResponseWrap->personsData = array();

            //Пройдем по всем пациентам
            foreach($arRes as $res){
                $personsData = new stdClass();
                $personsData->surname = $res['surname'];
                $personsData->name = $res['name'];
                $personsData->patronymic = $res['patronymic'];
                $personsData->gender = $res['gender'];
                $personsData->birthday = $res['birthday'];

                $personIdentifiers = new stdClass();
                $personIdentifiers->personUniqID = $res['personUniqID'];

				$personIdentifiers->documents = new stdClass();
                $personIdentifiers->documents->type = $res['d_type'];
                $personIdentifiers->documents->serial = $res['d_serial'];
                $personIdentifiers->documents->number = $res['d_number'];
                $personIdentifiers->documents->isActive = $res['d_isActive'];
                $personIdentifiers->documents->dateBegin = $res['d_dateBegin'];
                $personIdentifiers->documents->dateEnd = $res['d_dateEnd'];
                $personIdentifiers->documents->externalCode = $res['d_externalCode'];
                $personIdentifiers->documents->externalID = $res['d_externalID'];
                $personIdentifiers->documents->nsiCode = $res['d_nsiCode'];

				$personIdentifiers->insuranceDocument = new stdClass();
                $personIdentifiers->insuranceDocument->type = $res['p_type'];
                $personIdentifiers->insuranceDocument->serial = $res['p_serial'];
                $personIdentifiers->insuranceDocument->number = $res['p_number'];
                $personIdentifiers->insuranceDocument->isActive = $res['p_isActive'];
                $personIdentifiers->insuranceDocument->dateBegin = $res['p_dateBegin'];
                $personIdentifiers->insuranceDocument->dateEnd = $res['p_dateEnd'];
                $personIdentifiers->insuranceDocument->externalCode = $res['p_externalCode'];
                $personIdentifiers->insuranceDocument->externalID = $res['p_externalID'];
                $personIdentifiers->insuranceDocument->nsiCode = $res['p_nsiCode'];

				$personIdentifiers->snilsDocument = new stdClass();
                $personIdentifiers->snilsDocument->type = $res['s_type'];
                $personIdentifiers->snilsDocument->number = $res['s_number'];

                $additionalInfo = new stdClass();
                $additionalInfo->additionalInfoObject = array();

                $additionalInfoItem = new stdClass();
                $additionalInfoItem->title = "Лекарственная непереносимость";
                $additionalInfoItem->textLines = $this->getTextLinesArray($additionalInfoItemDrug, $res['personUniqID'], 'DrugMnn_Name');
                $additionalInfo->additionalInfoObject[] = $additionalInfoItem;

                $additionalInfoItem = new stdClass();
                $additionalInfoItem->title = "Аллергические реакции";
                $additionalInfoItem->textLines = $this->getTextLinesArray($additionalInfoItemPAR, $res['personUniqID'], 'PersonAllergicReaction_Kind');
                $additionalInfo->additionalInfoObject[] = $additionalInfoItem;

                $additionalInfoItem = new stdClass();
                $additionalInfoItem->title = "Уточненные диагнозы";
                $additionalInfoItem->textLines = $this->getTextLinesArray($additionalInfoItemDiag, $res['personUniqID'], 'Diag');
                $additionalInfo->additionalInfoObject[] = $additionalInfoItem;

                $addresses = new stdClass();
				$addresses->registrationAddress = new stdClass();
                $addresses->registrationAddress->region = $res['regA_region'];
                $addresses->registrationAddress->regionKladr = $res['regA_regionKladr'];
                $addresses->registrationAddress->locality = $res['regA_locality'];
                $addresses->registrationAddress->localityKladr = $res['regA_localityKladr'];
                $addresses->registrationAddress->country = $res['regA_country'];
                $addresses->registrationAddress->countryKladr = $res['regA_countryKladr'];
                $addresses->registrationAddress->street = $res['regA_street'];
                $addresses->registrationAddress->streetKladr = $res['regA_streetKladr'];
                $addresses->registrationAddress->geofull = $res['regA_geofull'];
                $addresses->registrationAddress->house = $res['regA_house'];
                $addresses->registrationAddress->houseBlock = $res['regA_houseBlock'];
                $addresses->registrationAddress->apartment = $res['regA_apartment'];
                $addresses->registrationAddress->postIndex = $res['regA_postIndex'];

				$addresses->residentialAddress = new stdClass();
                $addresses->residentialAddress->region = $res['resA_region'];
                $addresses->residentialAddress->regionKladr = $res['resA_regionKladr'];
                $addresses->residentialAddress->locality = $res['resA_locality'];
                $addresses->residentialAddress->localityKladr = $res['resA_localityKladr'];
                $addresses->residentialAddress->country = $res['resA_country'];
                $addresses->residentialAddress->countryKladr = $res['resA_countryKladr'];
                $addresses->residentialAddress->street = $res['resA_street'];
                $addresses->residentialAddress->streetKladr = $res['resA_streetKladr'];
                $addresses->residentialAddress->geofull = $res['resA_geofull'];
                $addresses->residentialAddress->house = $res['resA_house'];
                $addresses->residentialAddress->houseBlock = $res['resA_houseBlock'];
                $addresses->residentialAddress->apartment = $res['resA_apartment'];
                $addresses->residentialAddress->postIndex = $res['resA_postIndex'];


                //Собрали все блоки
                $personsData->personIdentifiers = $personIdentifiers;
                $personsData->additionalInfo = $additionalInfo;
                $personsData->addresses = $addresses;

                //Добавили к общему ответу
                $resp->searchedResponseServicesWrap->searchResponseWrap->personsData[] = $personsData;
            }

            return $resp;
        }
    }

    /**
     * Формирование простого массива $propName по конкретному пациенту
     */
    function getTextLinesArray($array, $person_id, $propName){
        $textLines = array();

        foreach($array as $item){
            if($item['Person_id'] == $person_id && strlen($item[$propName]) > 0){
                $textLines[] = $item[$propName];
            }
        }

        return $textLines;
    }
}
