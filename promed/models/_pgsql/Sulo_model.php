<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Sulo_model - модель для работы с сервисом СУЛО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Sulo
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Valery Bondarev
 * @version      12.2019
 */
class Sulo_model extends swPgModel
{
    /**
     *    Конструктор
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Проверка наличия ID в DR_Register
     */
    function check_DR_Register($DR_Register_id)
    {
        $params_check_DR_Register = array(
            'ID' => $DR_Register_id
        );
        $query_check_DR_Register = "
    		select count(ID) as \"ctnID\"
    		from r101.DR_Register
    		where ID = :ID
    	";
        $result_check_DR_Register = $this->db->query($query_check_DR_Register, $params_check_DR_Register);
        $result_check_DR_Register = $result_check_DR_Register->result('array');
        if ($result_check_DR_Register[0]['ctnID'] == 0)
            return false;
        else
            return true;
    }

    /**
     * Добавление информации в DR_Register
     */
    function Add_DR_Register($DR_Register, $ObjectSynchronLog_id)
    {
        $RegDate = new DateTime($DR_Register['createdAt']);
        $ExpirationDate = new DateTime($DR_Register['deadLine']);
        //$ObjectSynchronLog_id = $this->AddRegisterToSyncronLog($DR_Register['id']);
        $params_ins = array(
            'ID' => $DR_Register['id'],
            'RegType_id' => null,
            'RegAction_id' => null,
            'RegNumber_Ru' => $DR_Register['numberRu'],        //Регистрационный номер на русском языке
            'RegNumber_Kz' => $DR_Register['numberKz'],        //Регистрационный номер на казахском языке
            'RegDate' => $RegDate->format('Y-m-d'),        //Дата регистрации
            'RegTerm' => $DR_Register['lifeTime'],        //Срок регистрации
            'ExpirationDate' => $ExpirationDate->format('Y-m-d'),//Дата истечения
            'Name_ru' => $DR_Register['nameRu'],            //Торговое наименование на русском языке
            'Name_kz' => $DR_Register['nameKz'],            //Торговое наименование на государственном языке
            '_producer_name_kz' => '',
            '_producer_name_ru' => '',
            '_country_name_ru' => '',
            '_country_name_kz' => '',
            'GmpSign' => 0,
            'TrademarkSign' => 0,
            'PatentSign' => 0,
            'StorageTerm' => $DR_Register['storageLifeTime'],    //Срок хранения
            'StorageMeasure_id' => null,
            'sulo_id' => null,
            'StatusId' => null,
            'CertificateDataId' => null,
            'NeedSign' => null,
            'ParentRegisterId' => null,
            'REGCERT_ID' => null,
            'ObjectSynchronLog_id' => $ObjectSynchronLog_id
        );
        $query_ins = "
			insert into r101.DR_Register(
				ID,
				RegType_id,
				RegAction_id,
				RegNumber_Ru,
				RegNumber_Kz,
				RegDate,
				RegTerm,
				ExpirationDate,
				Name_ru,
				Name_kz,
				_producer_name_kz,
				_producer_name_ru,
				_country_name_ru,
				_country_name_kz,
				GmpSign,
				TrademarkSign,
				PatentSign,
				StorageTerm,
				StorageMeasure_id,
				sulo_id,
				StatusId,
				CertificateDataId,
				NeedSign,
				ParentRegisterId,
				REGCERT_ID,
				ObjectSynchronLog_id
			)
			values(
				:ID,
				:RegType_id,
				:RegAction_id,
				:RegNumber_Ru,
				:RegNumber_Kz,
				:RegDate,
				:RegTerm,
				:ExpirationDate,
				:Name_ru,
				:Name_kz,
				:_producer_name_kz,
				:_producer_name_ru,
				:_country_name_ru,
				:_country_name_kz,
				:GmpSign,
				:TrademarkSign,
				:PatentSign,
				:StorageTerm,
				:StorageMeasure_id,
				:sulo_id,
				:StatusId,
				:CertificateDataId,
				:NeedSign,
				:ParentRegisterId,
				:REGCERT_ID,
				:ObjectSynchronLog_id
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_ins, $params_ins);die;
        $res = $this->queryResult($query_ins, $params_ins);
        if ($res === false)
            return 0;
        else
            return $DR_Register['id'];
    }

    /**
     *    Добавление информации о производителях (из тега manufactires)
     */
    function Add_manufactures($manufactures, $DR_Register_id)
    {
        //Сначала добавляем данные в DR_DicProducerTypes (предварительно проверим, а есть ли уже такая запись)
        $producerType = $manufactures['producerType'];
        $params_check_producerType = array(
            'ID' => $producerType['id']
        );
        $query_check_producerType = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicProducerTypes
			where ID = :ID
		";

        $result_check_producerType = $this->db->query($query_check_producerType, $params_check_producerType);
        $result_check_producerType = $result_check_producerType->result('array');
        if ($result_check_producerType[0]['ctnID'] == 0) //Если еще нет, то добавим
        {
            $params_add_producerType = array(
                'ID' => $producerType['id'],
                'Name_Ru' => $producerType['nameRu'],
                'Name_Kz' => $producerType['nameKz']
            );
            $query_add_producerType = "
				insert into r101.DR_DicProducerTypes(
					ID,
					Name_Ru,
					Name_Kz
				)
				values (
					:ID,
					:Name_Ru,
					:Name_Kz
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_producerType = $this->queryResult($query_add_producerType, $params_add_producerType);
        }

        //Теперь добавляем данные в MZ_DicState (тег country - страна производителя); снова предварительно проверим, а есть уже такая запись.
        $country = $manufactures['country'];
        $params_check_MZ_DicState = array(
            'ID' => $country['id']
        );
        $query_check_MZ_DicState = "
			select count(ID) as \"ctnID\"
			from r101.MZ_DicState
			where ID = :ID
		";
        $result_check_MZ_DicState = $this->db->query($query_check_MZ_DicState, $params_check_MZ_DicState);
        $result_check_MZ_DicState = $result_check_MZ_DicState->result('array');
        if ($result_check_MZ_DicState[0]['ctnID'] == 0) //Если еще нет, то добавим
        {
            $params_add_MZ_DicState = array(
                'ID' => $country['id'],
                'Dbeg' => '1900-01-01',
                'Dend' => '2099-01-01',
                'NameRu' => $country['nameRu'],
                'PublCod' => NULL,
                'NameKZ' => $country['nameKz'],
                'Dversion' => NULL,
                'FullNameRu' => $country['fullNameRu'],
                'FullNameKz' => $country['fullNameKz'],
                'NameEn' => $country['nameEn'],
                'Code' => $country['code'],
                'COUNTRIES_ID' => NULL
            );
            $query_add_MZ_DicState = "
				insert into r101.MZ_DicState (
					ID,
					Dbeg,
					Dend,
					NameRu,
					PublCod,
					NameKZ,
					Dversion,
					FullNameRu,
					FullNameKz,
					NameEn,
					Code,
					COUNTRIES_ID
				)
				values (
					:ID,
					:Dbeg,
					:Dend,
					:NameRu,
					:PublCod,
					:NameKZ,
					:Dversion,
					:FullNameRu,
					:FullNameKz,
					:NameEn,
					:Code,
					:COUNTRIES_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_MZ_DicState = $this->queryResult($query_add_MZ_DicState, $params_add_MZ_DicState);
        }

        //Добавляем данные в MZ_DicOrgPravForm (тег administrationForm)
        $producer = $manufactures['producer'];
        //var_dump($producer);die;
        if (is_array($producer['administrationForm']) && count($producer['administrationForm']) > 0) {
            $administrationForm = $producer['administrationForm'];
            $FormType_id = $administrationForm['id'];
            //Проверим, а а есть уже такая запись.
            $params_check_MZ_DicOrgPravForm = array(
                'ID' => $administrationForm['id']
            );
            $query_check_MZ_DicOrgPravForm = "
				select count(ID) as \"ctnID\"
				from r101.MZ_DicOrgPravForm
				where ID = :ID
			";
            $result_check_MZ_DicOrgPravForm = $this->db->query($query_check_MZ_DicOrgPravForm, $params_check_MZ_DicOrgPravForm);
            $result_check_MZ_DicOrgPravForm = $result_check_MZ_DicOrgPravForm->result('array');
            //var_dump($result_check_MZ_DicOrgPravForm);die;
            if ($result_check_MZ_DicOrgPravForm[0]['ctnID'] == 0) { //Если нет, то добавим
                $params_add_MZ_DicOrgPravForm = array(
                    'ID' => $administrationForm['id'],
                    'Dbeg' => '1900-01-01',
                    'Dend' => '2099-01-01',
                    'NameRu' => $administrationForm['nameRu'],
                    'Publcod' => NULL,
                    'ShortNameRu' => $administrationForm['shortNameRu'],
                    'ShortNameKz' => $administrationForm['shortNameKz'],
                    'NameKz' => $administrationForm['nameKz'],
                    'Dversion' => NULL
                );
                $query_add_MZ_DicOrgPravForm = "
					insert into r101.MZ_DicOrgPravForm (
						ID,
						Dbeg,
						Dend,
						NameRu,
						Publcod,
						ShortNameRu,
						ShortNameKz,
						NameKz,
						Dversion
					)
					values (
						:ID,
						:Dbeg,
						:Dend,
						:NameRu,
						:Publcod,
						:ShortNameRu,
						:ShortNameKz,
						:NameKz,
						:Dversion
					)
					returning null as \"Error_Code\", null as \"Error_Msg\"
				";
                //echo getDebugSQL($query_add_MZ_DicOrgPravForm, $params_add_MZ_DicOrgPravForm);die;
                $result_add_MZ_DicOrgPravForm = $this->queryResult($query_add_MZ_DicOrgPravForm, $params_add_MZ_DicOrgPravForm);
            }
        } else {
            $FormType_id = null;
        }

        //Добавляем данные в DR_DicProducers (тег producer). Предварительно проверим, а есть уже такая запись
        $params_check_DR_DicProducers = array(
            'ID' => $producer['id']
        );
        $query_check_DR_DicProducers = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicProducers
			where ID = :ID
		";
        $result_check_DR_DicProducers = $this->db->query($query_check_DR_DicProducers, $params_check_DR_DicProducers);
        $result_check_DR_DicProducers = $result_check_DR_DicProducers->result('array');
        if ($result_check_DR_DicProducers[0]['ctnID'] == 0) {
            $params_add_DR_DicProducers = array(
                'ID' => $producer['id'],
                'FormType_id' => $FormType_id,
                'Name_Ru' => $producer['nameRu'],
                'Name_Kz' => $producer['nameKz'],
                'Name_Eng' => $producer['nameEn'],
                'Rnn' => $producer['rnn'],
                'Iin' => $producer['iin'],
                'Bin' => $producer['bin'],
                'FIRMS_ID' => null
            );
            $query_add_DR_DicProducers = "
				insert into r101.DR_DicProducers (
					ID,
					FormType_id,
					Name_Ru,
					Name_Kz,
					Name_Eng,
					Rnn,
					Iin,
					Bin,
					FIRMS_ID
				)
				values (
					:ID,
					:FormType_id,
					:Name_Ru,
					:Name_Kz,
					:Name_Eng,
					:Rnn,
					:Iin,
					:Bin,
					:FIRMS_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            //echo getDebugSQL($query_add_DR_DicProducers, $params_add_DR_DicProducers);die;
            $result_add_DR_DicProducers = $this->queryResult($query_add_DR_DicProducers, $params_add_DR_DicProducers);
        }

        //Добавляем данные в DR_RegisterProducers
        $params_add_DR_RegisterProducers = array(
            'ID' => $manufactures['id'],
            'Register_id' => $DR_Register_id,
            'Producer_id' => $producer['id'],
            'ProducerType_id' => $producerType['id'],
            'Country_id' => $country['id'],
            'FIRMS_ID' => null
        );
        $query_add_DR_RegisterProducers = "
			insert into r101.DR_RegisterProducers (
				ID,
				Register_id,
				Producer_id,
				ProducerType_id,
				Country_id,
				FIRMS_ID
			)
			values (
				:ID,
				:Register_id,
				:Producer_id,
				:ProducerType_id,
				:Country_id,
				:FIRMS_ID
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_add_DR_RegisterProducers, $params_add_DR_RegisterProducers);die;
        $result_add_DR_RegisterProducers = $this->queryResult($query_add_DR_RegisterProducers, $params_add_DR_RegisterProducers);
        if ($result_add_DR_RegisterProducers === false)
            return false;
        else
            return true;
    }

    /**
     *    Добавление единиц измерения
     */
    function add_MZ_DicDoseType($mz_dic_dodetype)
    {
        //Проверим, есть ли уже такой в базе
        $params_check_MZ_DicDoseType = array(
            'ID' => $mz_dic_dodetype['id']
        );
        $query_check_MZ_DicDoseType = "
			select count(ID) as \"ctnID\"
			from r101.MZ_DicDoseType
			where ID = :ID
		";
        $result_check_MZ_DicDoseType = $this->db->query($query_check_MZ_DicDoseType, $params_check_MZ_DicDoseType);
        $result_check_MZ_DicDoseType = $result_check_MZ_DicDoseType->result('array');
        if ($result_check_MZ_DicDoseType[0]['ctnID'] == 0) {
            $params_add_MZ_DicDoseType = array(
                'ID' => $mz_dic_dodetype['id'],
                'Dversion' => null,
                'Dbeg' => '1900-01-01',
                'DEnd' => '2099-01-01',
                'NameRu' => $mz_dic_dodetype['nameRu'],
                'PublCod' => null,
                'ShortNameRu' => $mz_dic_dodetype['shortNameRu'],
                'ShortNameKz' => $mz_dic_dodetype['shortNameKz'],
                'NameKz' => $mz_dic_dodetype['nameKz'],
                'Parent' => null,
                'Numeral_name_ru' => null,
                'More_numeral_name_ru' => null,
                'MASSUNITS_ID' => null
            );
            $query_add_MZ_DicDoseType = "
				insert into r101.MZ_DicDoseType (
					ID,
					Dversion,
					Dbeg,
					DEnd,
					NameRu,
					PublCod,
					ShortNameRu,
					ShortNameKz,
					NameKz,
					Parent,
					Numeral_name_ru,
					More_numeral_name_ru,
					MASSUNITS_ID
				)
				values (
					:ID,
					:Dversion,
					:Dbeg,
					:DEnd,
					:NameRu,
					:PublCod,
					:ShortNameRu,
					:ShortNameKz,
					:NameKz,
					:Parent,
					:Numeral_name_ru,
					:More_numeral_name_ru,
					:MASSUNITS_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_MZ_DicDoseType = $this->queryResult($query_add_MZ_DicDoseType, $params_add_MZ_DicDoseType);
        }

    }

    /**
     *    Добавление информации в MZ_DicDoseType
     */
    function addStorageMeasure($storageMeasure, $DR_Register_id)
    {
        $this->add_MZ_DicDoseType($storageMeasure);

        //Теперь обновим запись в DR_Register
        $params_update_DR_Register = array(
            'ID' => $DR_Register_id,
            'StorageMeasure_id' => $storageMeasure['id']
        );
        $query_update_DR_Register = "
				update r101.DR_Register
				set StorageMeasure_id = :StorageMeasure_id
				where ID = :ID
				returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        $result_update_DR_Register = $this->queryResult($query_update_DR_Register, $params_update_DR_Register);
        if ($result_update_DR_Register === false)
            return false;
        else
            return true;
    }

    /**
     *    Добавление информации об упаковках (тег Boxes)
     */
    function addBoxes($boxes, $DR_Register_id)
    {
        //Добавим данные в DR_DicBoxes (тег boxType). Сначала проверим, есть ли.
        $params_check_DR_DicBoxex = array(
            'ID' => $boxes['boxType']['id']
        );
        $query_check_DR_DicBoxes = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicBoxes
			where ID = :ID
		";
        $result_check_DR_DicBoxes = $this->db->query($query_check_DR_DicBoxes, $params_check_DR_DicBoxex);
        $result_check_DR_DicBoxes = $result_check_DR_DicBoxes->result('array');
        if ($result_check_DR_DicBoxes[0]['ctnID'] == 0) {
            $params_add_DR_DicBoxes = array(
                'ID' => $boxes['boxType']['id'],
                'Name_Ru' => $boxes['boxType']['nameRu'],
                'Name_Kz' => $boxes['boxType']['nameKz'],
                'Parent_Id' => null,
                'sulo_id' => null,
                'DRUGPACK_ID' => null
            );
            $query_add_DR_DicBoxes = "
				insert into r101.DR_DicBoxes (
					ID,
					Name_Ru,
					Name_Kz,
					Parent_Id,
					sulo_id,
					DRUGPACK_ID
				)
				values (
					:ID,
					:Name_Ru,
					:Name_Kz,
					:Parent_Id,
					:sulo_id,
					:DRUGPACK_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_DR_DicBoxes = $this->queryResult($query_add_DR_DicBoxes, $params_add_DR_DicBoxes);
        }

        //Добавляем данные в MZ_DicDoseType. Сначала проверим, есть ли.
        if (is_array($boxes['volumeMeasure']) && count($boxes['volumeMeasure']) > 0) {
            $volumeMeasure = $boxes['volumeMeasure'];
            $this->add_MZ_DicDoseType($volumeMeasure);
        }
        //Добавим данные в DR_RegisterBoxRkLs. Сначала проверим, есть ли.
        if (is_array($boxes['history']) && count($boxes['history']) > 0) {
            $history = $boxes['history'];
            $params_check_DR_RegisterBoxRkLs = array(
                'ID' => $history['id']
            );
            $query_check_DR_RegisterBoxRkLs = "
				select count(ID) as \"ctnID\"
				from r101.DR_RegisterBoxRkLs
				where ID = :ID
			";
            $result_check_DR_RegisterBoxRkLs = $this->db->query($query_check_DR_RegisterBoxRkLs, $params_check_DR_RegisterBoxRkLs);
            $result_check_DR_RegisterBoxRkLs = $result_check_DR_RegisterBoxRkLs->result('array');
            if ($result_check_DR_RegisterBoxRkLs[0]['ctnID'] == 0) {
                $StateDate = new DateTime($history['date']);
                $params_add_DR_RegisterBoxRkLs = array(
                    'ID' => $history['id'],
                    'StateDate' => $StateDate->format('Y-m-d'),
                    'StateSign' => ($history['isHistorical'] == true) ? 2 : 1
                );
                $query_add_DR_RegisterBoxRkls = "
					insert into r101.DR_RegisterBoxRkLs (
						ID,
						StateDate,
						StateSign
					)
					values (
						:ID,
						:StateDate,
						:StateSign
					)
					returning null as \"Error_Code\", null as \"Error_Msg\"
				";
                //echo getDebugSQL($query_add_DR_RegisterBoxRkls, $params_add_DR_RegisterBoxRkLs);die;
                $result_add_DR_RegisterBoxRkls = $this->queryResult($query_add_DR_RegisterBoxRkls, $params_add_DR_RegisterBoxRkLs);
            }
        }
        //Теперь добавляем данные в DR_RegisterBoxes
        $params_add_DR_RegisterBoxes = array(
            'ID' => $boxes['id'],
            'Register_id' => $DR_Register_id,
            'Box_Id' => $boxes['boxType']['id'],
            'InnerSign' => ($boxes['isInner'] == true) ? 2 : 1,
            'Volume' => $boxes['volume'],
            'Volume_Measure_Id' => (is_array($boxes['volumeMeasure']) && count($boxes['volumeMeasure']) > 0) ? $volumeMeasure['id'] : null,
            'Unit_Count' => $boxes['unitCount'],
            'BoxSize' => $boxes['size'],
            'Description' => $boxes['description'],
            'sulo_id' => null
        );
        $query_add_DR_RegisterBoxes = "
			insert into r101.DR_RegisterBoxes (
				ID,
				Register_id,
				Box_Id,
				InnerSign,
				Volume,
				Volume_Measure_Id,
				Unit_Count,
				BoxSize,
				Description,
				sulo_id
			)
			values (
				:ID,
				:Register_id,
				:Box_Id,
				:InnerSign,
				:Volume,
				:Volume_Measure_Id,
				:Unit_Count,
				:BoxSize,
				:Description,
				:sulo_id
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_add_DR_RegisterBoxes, $params_add_DR_RegisterBoxes);die;
        $result_add_DR_RegisterBoxes = $this->queryResult($query_add_DR_RegisterBoxes, $params_add_DR_RegisterBoxes);
        if ($result_add_DR_RegisterBoxes === false)
            return false;
        else
            return true;
    }

    /**
     *    Обработка регистрационных досье ЛС (тег detailsDrug)
     */
    function addDetailsDrug($detailsDrug, $DR_Register_id)
    {
        //Добавляем данные в MZ_DicDoseType (тег dosageMeasure). Сначала проверим, есть ли.
        $DosageMeasure_Id = null;
        if (is_array($detailsDrug['dosageMeasure']) && count($detailsDrug['dosageMeasure']) > 0) {
            $dosageMeasure = $detailsDrug['dosageMeasure'];
            $DosageMeasure_Id = $dosageMeasure['id'];
            $this->add_MZ_DicDoseType($dosageMeasure);
        }

        //Добавляем данные в DR_DicDrugTypes. Сначала проверим, есть ли.
        $DR_DicDrugTypes = $detailsDrug['drugType'];
        $DrugType_Id = $DR_DicDrugTypes['id'];
        $params_check_DR_DicDrugTypes = array(
            'ID' => $DR_DicDrugTypes['id']
        );
        $query_check_DR_DicDrugTypes = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicDrugTypes
			where ID = :ID
		";
        $result_check_DR_DicDrugTypes = $this->db->query($query_check_DR_DicDrugTypes, $params_check_DR_DicDrugTypes);
        $result_check_DR_DicDrugTypes = $result_check_DR_DicDrugTypes->result('array');
        if ($result_check_DR_DicDrugTypes[0]['ctnID'] == 0) {
            $params_add_DR_DicDrugTypes = array(
                'ID' => $DR_DicDrugTypes['id'],
                'Name_Ru' => $DR_DicDrugTypes['nameRu'],
                'Name_Kz' => $DR_DicDrugTypes['nameKz'],
            );
            $query_add_DR_DicDrugTypes = "
				insert into r101.DR_DicDrugTypes (
					ID,
					Name_Ru,
					Name_Kz
				)
				values (
					:ID,
					:Name_Ru,
					:Name_Kz
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_DR_DicDrugTypes = $this->queryResult($query_add_DR_DicDrugTypes, $params_add_DR_DicDrugTypes);
        }

        //Добавляем данные в DR_DicInternationalNames. Сначала проверим, есть ли.
        $DR_DicInternationalNames = $detailsDrug['internationalName'];
        $IntName_Id = $DR_DicInternationalNames['id'];
        $params_check_DR_DicInternationalNames = array(
            'ID' => $IntName_Id
        );
        $query_check_DR_DicInternationalNames = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicInternationalNames
			where ID = :ID
		";
        $result_check_DR_DicInternationalNames = $this->db->query($query_check_DR_DicInternationalNames, $params_check_DR_DicInternationalNames);
        $result_check_DR_DicInternationalNames = $result_check_DR_DicInternationalNames->result('array');
        if ($result_check_DR_DicInternationalNames[0]['ctnID'] == 0) {
            $params_add_DR_DicInternationalNames = array(
                'ID' => $IntName_Id,
                'Name_Ru' => $DR_DicInternationalNames['nameRu'],
                'Name_Eng' => $DR_DicInternationalNames['nameRu'],
                'Name_Kz' => $DR_DicInternationalNames['nameKz'],
                'sulo_id' => null,
                'ACTMATTERS_ID' => null
            );
            $query_add_DR_DicInternationalNames = "
				insert into r101.DR_DicInternationalNames (
					ID,
					Name_Ru,
					Name_Eng,
					Name_Kz,
					sulo_id,
					ACTMATTERS_ID
				)
				values (
					:ID,
					:Name_Ru,
					:Name_Eng,
					:Name_Kz,
					:sulo_id,
					:ACTMATTERS_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_DR_DicInternationalNames = $this->queryResult($query_add_DR_DicInternationalNames, $params_add_DR_DicInternationalNames);
        }

        //Добавляем способы ввода.
        if (is_array($detailsDrug['usageMethods']) && count($detailsDrug['usageMethods']) > 0) {
            for ($u = 0; $u < count($detailsDrug['usageMethods']); $u++) {
                $usageMethods = $detailsDrug['usageMethods'][$u];
                //Добавляем данные в DR_DicUseMethods. Сначала проверим, есть ли.
                $method = $usageMethods['method'];
                $params_check_DR_DicUseMethods = array(
                    'ID' => $method['id']
                );
                $query_check_DR_DicUseMethods = "
					select count(ID) as \"ctnID\"
					from r101.DR_DicUseMethods
					where ID = :ID
				";
                $result_check_DR_DicUseMethods = $this->db->query($query_check_DR_DicUseMethods, $params_check_DR_DicUseMethods);
                $result_check_DR_DicUseMethods = $result_check_DR_DicUseMethods->result('array');
                if ($result_check_DR_DicUseMethods[0]['ctnID'] == 0) {
                    $params_add_DR_DicUseMethods = array(
                        'ID' => $method['id'],
                        'Name_Ru' => $method['nameRu'],
                        'Name_Kz' => $method['nameKz']
                    );
                    $query_add_DR_DicUseMethods = "
						insert into r101.DR_DicUseMethods (
							ID,
							Name_Ru,
							Name_Kz
						)
						values (
							:ID,
							:Name_Ru,
							:Name_Kz
						)
						returning null as \"Error_Code\", null as \"Error_Msg\"
					";
                    $result_add_DR_DicUseMethods = $this->queryResult($query_add_DR_DicUseMethods, $params_add_DR_DicUseMethods);
                }
                //Добавляем данные в DR_RegisterUseMethods
                $paramd_add_DR_RegisterUseMethods = array(
                    'ID' => $usageMethods['id'],
                    'Register_Id' => $DR_Register_id,
                    'Use_method_id' => $method['id']
                );
                $query_add_DR_RegisterUseMethods = "
					insert into r101.DR_RegisterUseMethods (
						ID,
						Register_Id,
						Use_method_id
					)
					values (
						:ID,
						:Register_Id,
						:Use_method_id
					)
					returning null as \"Error_Code\", null as \"Error_Msg\"
				";
                $result_add_DR_RegisterUseMethods = $this->queryResult($query_add_DR_RegisterUseMethods, $paramd_add_DR_RegisterUseMethods);
            }
        }

        //Добавляем фармакологические действия (pharmActions)
        if (is_array($detailsDrug['pharmActions']) && count($detailsDrug['pharmActions']) > 0) {
            for ($p = 0; $p < count($detailsDrug['pharmActions']); $p++) {
                $pharmActions = $detailsDrug['pharmActions'][$p];
                //Добавляем данные в DR_DicPharmActions. Сначала проверим, есть ли.
                $action = $pharmActions['action'];
                $params_check_DR_DicPharmActions = array(
                    'ID' => $action['id']
                );
                $query_check_DR_DicPharmActions = "
					select count(ID) as \"ctnID\"
					from r101.DR_DicPharmActions
					where ID = :ID
				";
                $result_check_DR_DicPharmActions = $this->db->query($query_check_DR_DicPharmActions, $params_check_DR_DicPharmActions);
                $result_check_DR_DicPharmActions = $result_check_DR_DicPharmActions->result('array');
                if ($result_check_DR_DicPharmActions[0]['ctnID'] == 0) {
                    $params_add_DR_DicPharmActions = array(
                        'ID' => $action['id'],
                        'Name_Ru' => $action['nameRu'],
                        'Name_Kz' => $action['nameKz'],
                        'Parent_Id' => null,
                        'CLSPHARMAGROUP_ID' => null
                    );
                    $query_add_DR_DicPharmActions = "
						insert into r101.DR_DicPharmActions (
							ID,
							Name_Ru,
							Name_Kz,
							Parent_Id,
							CLSPHARMAGROUP_ID
						)
						values (
							:ID,
							:Name_Ru,
							:Name_Kz,
							:Parent_Id,
							:CLSPHARMAGROUP_ID
						)
						returning null as \"Error_Code\", null as \"Error_Msg\"
					";
                    $result_add_DR_DicPharmActions = $this->queryResult($query_add_DR_DicPharmActions, $params_add_DR_DicPharmActions);
                }
                //Добавляем данные в DR_RegisterPharmActions
                $params_add_DR_RegisterPharmActions = array(
                    'ID' => $pharmActions['id'],
                    'Register_id' => $DR_Register_id,
                    'PharmAction_Id' => $action['id']
                );
                $query_add_DR_RegisterPharmActions = "
					insert into r101.DR_RegisterPharmActions (
						ID,
						Register_id,
						PharmAction_Id
					)
					values (
						:ID,
						:Register_id,
						:PharmAction_Id
					)
					returning null as \"Error_Code\", null as \"Error_Msg\"
				";
                $result_add_DR_RegisterPharmActions = $this->queryResult($query_add_DR_RegisterPharmActions, $params_add_DR_RegisterPharmActions);
            }
        }

        //Добавляем информацию по АТХ(atc), таблица DR_DicAtcCodes. Сначала проверим, есть ли.
        $atc = $detailsDrug['atc'];
        $Atc_Id = $atc['id'];
        $params_check_DR_DicAtcCodes = array(
            'ID' => $atc['id']
        );
        $query_check_DR_DicAtcCodes = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicAtcCodes
			where ID = :ID
		";
        $result_check_DR_DicAtcCodes = $this->db->query($query_check_DR_DicAtcCodes, $params_check_DR_DicAtcCodes);
        $result_check_DR_DicAtcCodes = $result_check_DR_DicAtcCodes->result('array');
        if ($result_check_DR_DicAtcCodes[0]['ctnID'] == 0) {
            $params_add_DR_DicAtcCodes = array(
                'ID' => $atc['id'],
                'PubCode' => $atc['code'],
                'Name_Ru' => $atc['nameRu'],
                'Name_Kz' => $atc['nameKz'],
                'Parent_Id' => null,
                'sulo_id' => null,
                'CLSATC_ID' => null
            );
            $query_add_DR_DicAtcCodes = "
				insert into r101.DR_DicAtcCodes (
					ID,
					PubCode,
					Name_Ru,
					Name_Kz,
					Parent_Id,
					sulo_id,
					CLSATC_ID
				)
				values (
					:ID,
					:PubCode,
					:Name_Ru,
					:Name_Kz,
					:Parent_Id,
					:sulo_id,
					:CLSATC_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_DR_DicAtcCodes = $this->queryResult($query_add_DR_DicAtcCodes, $params_add_DR_DicAtcCodes);
        }

        //Добавляем информацию по лекарственной форме, таблица DR_DicDosageForms. Сначала проверим, есть ли.
        $dosageForm = $detailsDrug['dosageForm'];
        $DosageForm_Id = $dosageForm['id'];
        $params_check_DR_DicDosageForms = array(
            'ID' => $DosageForm_Id
        );
        $query_check_DR_DicDosageForms = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicDosageForms
			where ID = :ID
		";
        $result_check_DR_DicDosageForms = $this->db->query($query_check_DR_DicDosageForms, $params_check_DR_DicDosageForms);
        $result_check_DR_DicDosageForms = $result_check_DR_DicDosageForms->result('array');
        if ($result_check_DR_DicDosageForms[0]['ctnID'] == 0) {
            $params_add_DR_DicDosageForms = array(
                'ID' => $DosageForm_Id,
                'Name_Ru' => $dosageForm['nameRu'],
                'Name_Kz' => $dosageForm['nameKz'],
                'Parent_Id' => null,
                'Concentration' => null,
                'Volume' => null,
                'sulo_id' => null,
                'CLSDRUGFORMS_ID' => null
            );
            $query_add_DR_DicDosageForms = "
				insert into r101.DR_DicDosageForms (
					ID,
					Name_Ru,
					Name_Kz,
					Parent_Id,
					Concentration,
					Volume,
					sulo_id,
					CLSDRUGFORMS_ID
				)
				values (
					:ID,
					:Name_Ru,
					:Name_Kz,
					:Parent_Id,
					:Concentration,
					:Volume,
					:sulo_id,
					:CLSDRUGFORMS_ID
				)
				returning null as \"Error_Code\", null as \"Error_Msg\"
			";
            $result_add_DR_DicDosageForms = $this->queryResult($query_add_DR_DicDosageForms, $params_add_DR_DicDosageForms);
        }

        //Добавляем информацию о составе (тег substanceItems);
        if (is_array($detailsDrug['substanceItems']) && count($detailsDrug['substanceItems']) > 0) {
            for ($s = 0; $s < count($detailsDrug['substanceItems']); $s++) {
                $substanceItems = $detailsDrug['substanceItems'][$s];
                //Добавим данные в DR_DicSubstanceTypes. Сначала проверим, есть ли.
                $substanceType = $substanceItems['substanceType'];
                $SubstanceType_Id = $substanceType['id'];
                $params_check_DR_DicSubstanceTypes = array(
                    'ID' => $SubstanceType_Id
                );
                $query_check_DR_DicSubstanceTypes = "
					select count(ID) as \"ctnID\"
					from r101.DR_DicSubstanceTypes
					where ID = :ID
				";
                $result_check_DR_DicSubstanceTypes = $this->db->query($query_check_DR_DicSubstanceTypes, $params_check_DR_DicSubstanceTypes);
                $result_check_DR_DicSubstanceTypes = $result_check_DR_DicSubstanceTypes->result('array');
                if ($result_check_DR_DicSubstanceTypes[0]['ctnID'] == 0) {
                    $params_add_DR_DicSubstanceTypes = array(
                        'ID' => $SubstanceType_Id,
                        'Name_Ru' => $substanceType['nameRu'],
                        'Name_Kz' => $substanceType['nameKz'],
                    );
                    $query_add_DR_DicSubstanceTypes = "
						insert into r101.DR_DicSubstanceTypes (
							ID,
							Name_Ru,
							Name_Kz
						)
						values (
							:ID,
							:Name_Ru,
							:Name_Kz
						)
						returning null as \"Error_Code\", null as \"Error_Msg\"
					";
                    $result_add_DR_DicSubstanceTypes = $this->queryResult($query_add_DR_DicSubstanceTypes, $params_add_DR_DicSubstanceTypes);
                }

                //Добавляем данные в MZ_DicDoseType (тег dosageMeasure). Сначала проверим, есть ли.
                $Measure_Id = null;
                if (is_array($substanceItems['dosageMeasure']) && count($substanceItems['dosageMeasure']) > 0) {
                    $dosageMeasure = $substanceItems['dosageMeasure'];
                    $Measure_Id = $dosageMeasure['id'];
                    $this->add_MZ_DicDoseType($dosageMeasure);
                }

                $Country_Id = null;
                if (is_array($substanceItems['country']) && count($substanceItems['country']) > 0) {
                    $country = $substanceItems['country'];
                    $Country_Id = $country['id'];
                    $params_check_MZ_DicState = array(
                        'ID' => $country['id']
                    );
                    $query_check_MZ_DicState = "
						select count(ID) as \"ctnID\"
						from r101.MZ_DicState
						where ID = :ID
					";
                    $result_check_MZ_DicState = $this->db->query($query_check_MZ_DicState, $params_check_MZ_DicState);
                    $result_check_MZ_DicState = $result_check_MZ_DicState->result('array');
                    if ($result_check_MZ_DicState[0]['ctnID'] == 0) //Если еще нет, то добавим
                    {
                        $params_add_MZ_DicState = array(
                            'ID' => $country['id'],
                            'Dbeg' => '1900-01-01',
                            'Dend' => '2099-01-01',
                            'NameRu' => $country['nameRu'],
                            'PublCod' => NULL,
                            'NameKZ' => $country['nameKz'],
                            'Dversion' => NULL,
                            'FullNameRu' => $country['fullNameRu'],
                            'FullNameKz' => $country['fullNameKz'],
                            'NameEn' => $country['nameEn'],
                            'Code' => $country['code'],
                            'COUNTRIES_ID' => NULL
                        );
                        $query_add_MZ_DicState = "
							insert into r101.MZ_DicState (
								ID,
								Dbeg,
								Dend,
								NameRu,
								PublCod,
								NameKZ,
								Dversion,
								FullNameRu,
								FullNameKz,
								NameEn,
								Code,
								COUNTRIES_ID
							)
							values (
								:ID,
								:Dbeg,
								:Dend,
								:NameRu,
								:PublCod,
								:NameKZ,
								:Dversion,
								:FullNameRu,
								:FullNameKz,
								:NameEn,
								:Code,
								:COUNTRIES_ID
							)
							returning null as \"Error_Code\", null as \"Error_Msg\"
						";
                        $result_add_MZ_DicState = $this->queryResult($query_add_MZ_DicState, $params_add_MZ_DicState);
                    }

                }

                $Producer_Id = null;
                if (is_array($substanceItems['producer']) && count($substanceItems['producer']) > 0) {
                    //Добавляем данные в MZ_DicOrgPravForm (тег administrationForm)
                    $producer = $substanceItems['producer'];
                    $Producer_Id = $producer['id'];
                    //var_dump($producer);die;
                    if (is_array($producer['administrationForm']) && count($producer['administrationForm']) > 0) {
                        $administrationForm = $producer['administrationForm'];
                        $FormType_id = $administrationForm['id'];
                        //Проверим, а а есть уже такая запись.
                        $params_check_MZ_DicOrgPravForm = array(
                            'ID' => $administrationForm['id']
                        );
                        $query_check_MZ_DicOrgPravForm = "
							select count(ID) as \"ctnID\"
							from r101.MZ_DicOrgPravForm
							where ID = :ID
						";
                        $result_check_MZ_DicOrgPravForm = $this->db->query($query_check_MZ_DicOrgPravForm, $params_check_MZ_DicOrgPravForm);
                        $result_check_MZ_DicOrgPravForm = $result_check_MZ_DicOrgPravForm->result('array');
                        //var_dump($result_check_MZ_DicOrgPravForm);die;
                        if ($result_check_MZ_DicOrgPravForm[0]['ctnID'] == 0) { //Если нет, то добавим
                            $params_add_MZ_DicOrgPravForm = array(
                                'ID' => $administrationForm['id'],
                                'Dbeg' => '1900-01-01',
                                'Dend' => '2099-01-01',
                                'NameRu' => $administrationForm['nameRu'],
                                'Publcod' => NULL,
                                'ShortNameRu' => $administrationForm['shortNameRu'],
                                'ShortNameKz' => $administrationForm['shortNameKz'],
                                'NameKz' => $administrationForm['nameKz'],
                                'Dversion' => NULL
                            );
                            $query_add_MZ_DicOrgPravForm = "
								insert into r101.MZ_DicOrgPravForm (
									ID,
									Dbeg,
									Dend,
									NameRu,
									Publcod,
									ShortNameRu,
									ShortNameKz,
									NameKz,
									Dversion
								)
								values (
									:ID,
									:Dbeg,
									:Dend,
									:NameRu,
									:Publcod,
									:ShortNameRu,
									:ShortNameKz,
									:NameKz,
									:Dversion
								)
								returning null as \"Error_Code\", null as \"Error_Msg\"
							";
                            //echo getDebugSQL($query_add_MZ_DicOrgPravForm, $params_add_MZ_DicOrgPravForm);die;
                            $result_add_MZ_DicOrgPravForm = $this->queryResult($query_add_MZ_DicOrgPravForm, $params_add_MZ_DicOrgPravForm);
                        }
                    } else {
                        $FormType_id = null;
                    }

                    //Добавляем данные в DR_DicProducers (тег producer). Предварительно проверим, а есть уже такая запись
                    $params_check_DR_DicProducers = array(
                        'ID' => $producer['id']
                    );
                    $query_check_DR_DicProducers = "
						select count(ID) as \"ctnID\"
						from r101.DR_DicProducers
						where ID = :ID
					";
                    $result_check_DR_DicProducers = $this->db->query($query_check_DR_DicProducers, $params_check_DR_DicProducers);
                    $result_check_DR_DicProducers = $result_check_DR_DicProducers->result('array');
                    if ($result_check_DR_DicProducers[0]['ctnID'] == 0) {
                        $params_add_DR_DicProducers = array(
                            'ID' => $producer['id'],
                            'FormType_id' => $FormType_id,
                            'Name_Ru' => $producer['nameRu'],
                            'Name_Kz' => $producer['nameKz'],
                            'Name_Eng' => $producer['nameEn'],
                            'Rnn' => $producer['rnn'],
                            'Iin' => $producer['iin'],
                            'Bin' => $producer['bin'],
                            'FIRMS_ID' => null
                        );
                        $query_add_DR_DicProducers = "
							insert into r101.DR_DicProducers (
								ID,
								FormType_id,
								Name_Ru,
								Name_Kz,
								Name_Eng,
								Rnn,
								Iin,
								Bin,
								FIRMS_ID
							)
							values (
								:ID,
								:FormType_id,
								:Name_Ru,
								:Name_Kz,
								:Name_Eng,
								:Rnn,
								:Iin,
								:Bin,
								:FIRMS_ID
							)
							returning null as \"Error_Code\", null as \"Error_Msg\"
						";
                        //echo getDebugSQL($query_add_DR_DicProducers, $params_add_DR_DicProducers);die;
                        $result_add_DR_DicProducers = $this->queryResult($query_add_DR_DicProducers, $params_add_DR_DicProducers);
                    }
                }

                //Добавляем данные в DR_DicCategories. Сначала проверим, есть ли.
                $substance = $substanceItems['substance'];
                $Category_Id = null;
                if (is_array($substance['category']) && count($substance['category']) > 0) {
                    $category = $substance['category'];
                    $Category_Id = $category['id'];
                    $params_check_DR_DicCategories = array(
                        'ID' => $category['id']
                    );
                    $query_check_DR_DicCategories = "
						select count(ID) as \"ctnID\"
						from r101.DR_DicCategories
						where ID = :ID
					";
                    $result_check_DR_DicCategories = $this->db->query($query_check_DR_DicCategories, $params_check_DR_DicCategories);
                    $result_check_DR_DicCategories = $result_check_DR_DicCategories->result('array');
                    if ($result_check_DR_DicCategories[0]['ctnID'] == 0) {
                        $params_add_DR_DicCategories = array(
                            'ID' => $category['id'],
                            'Name_Ru' => $category['nameRu'],
                            'Name_Kz' => $category['nameKz'],
                            'NARCOGROUPS_ID' => null,
                            'STRONGGROUPS_ID' => null
                        );
                        $query_add_DR_DicCategories = "
							insert into r101.DR_DicCategories (
								ID,
								Name_Ru,
								Name_Kz,
								NARCOGROUPS_ID,
								STRONGGROUPS_ID
							)
							values (
								:ID,
								:Name_Ru,
								:Name_Kz,
								:NARCOGROUPS_ID,
								:STRONGGROUPS_ID
							)
							returning null as \"Error_Code\", null as \"Error_Msg\"
						";
                        $result_add_DR_DicCategories = $this->queryResult($query_add_DR_DicCategories, $params_add_DR_DicCategories);
                    }
                }

                //Добавляем инфу в DR_DicSubstances. Сначала проверим, есть ли.
                $Substance_Id = $substance['id'];
                $params_check_DR_DicSubstances = array(
                    'ID' => $Substance_Id
                );
                $query_check_DR_DicSubstances = "
					select count(ID) as \"ctnID\"
					from r101.DR_DicSubstances
					where ID = :ID
				";
                $result_check_DR_DicSubstances = $this->db->query($query_check_DR_DicSubstances, $params_check_DR_DicSubstances);
                $result_check_DR_DicSubstances = $result_check_DR_DicSubstances->result('array');
                if ($result_check_DR_DicSubstances[0]['ctnID'] == 0) {
                    $params_add_DR_DicSubstances = array(
                        'ID' => $Substance_Id,
                        'Name_Ru' => $substance['nameRu'],
                        'Name_Kz' => $substance['nameKz'],
                        'Name_Eng' => $substance['nameEn'],
                        'AnimalSign' => ($substance['isAnimal'] == true) ? 2 : 1,
                        'Category_Id' => $Category_Id,
                        'CategoryPos' => null,
                        'sulo_id' => null
                    );
                    $query_add_DR_DicSubstances = "
						insert into r101.DR_DicSubstances (
							ID,
							Name_Ru,
							Name_Kz,
							Name_Eng,
							AnimalSign,
							Category_Id,
							CategoryPos,
							sulo_id
						)
						values (
							:ID,
							:Name_Ru,
							:Name_Kz,
							:Name_Eng,
							:AnimalSign,
							:Category_Id,
							:CategoryPos,
							:sulo_id
						)
						returning null as \"Error_Code\", null as \"Error_Msg\"
					";
                    $result_add_DR_DicSubstances = $this->queryResult($query_add_DR_DicSubstances, $params_add_DR_DicSubstances);
                }

                //Добавим инфу в DR_DicNdTypes (Тип НД - Национальный доход). Сначала проверим, есть ли.
                $NdType_Id = null;
                if (is_array($substanceItems['ndType']) && count($substanceItems['ndType']) > 0) {
                    $ndType = $substanceItems['ndType'];
                    $NdType_Id = $ndType['id'];
                    $params_check_DR_DicNdTypes = array(
                        'ID' => $NdType_Id
                    );
                    $query_check_DR_DicNdTypes = "
						select count(ID) as \"ctnID\"
						from r101.DR_DicNdTypes
						where ID = :ID
					";
                    $result_check_DR_DicNdTypes = $this->db->query($query_check_DR_DicNdTypes, $params_check_DR_DicNdTypes);
                    $result_check_DR_DicNdTypes = $result_check_DR_DicNdTypes->result('array');
                    if ($result_check_DR_DicNdTypes[0]['ctnID'] == 0) {
                        $params_add_DR_DicNdTypes = array(
                            'ID' => $NdType_Id,
                            'Name_Ru' => $ndType['nameRu'],
                            'Name_Kz' => $ndType['nameKz'],
                            'ShortName_Ru' => $ndType['shortNameRu'],
                            'ShortName_Kz' => $ndType['shortNameKz']
                        );
                        $query_add_DR_DicNdTypes = "
							insert into r101.DR_DicNdTypes (
								ID,
								Name_Ru,
								Name_Kz,
								ShortName_Ru,
								ShortName_Kz
							)
							values (
								:ID,
								:Name_Ru,
								:Name_Kz,
								:ShortName_Ru,
								:ShortName_Kz
							)
							returning null as \"Error_Code\", null as \"Error_Msg\"
						";
                        $result_add_DR_DicNdTypes = $this->queryResult($query_add_DR_DicNdTypes, $params_add_DR_DicNdTypes);
                    }
                }

                //Добавляем данные в DR_RegisterSubstances
                $params_add_DR_RegisterSubstances = array(
                    'ID' => $substanceItems['id'],
                    'Register_id' => $DR_Register_id,
                    'SubstanceType_Id' => $SubstanceType_Id,
                    'Substance_Id' => $Substance_Id,
                    'SubstanceCount' => $substanceItems['count'],
                    'Measure_Id' => $Measure_Id,
                    'Producer_Id' => $Producer_Id,
                    'Country_Id' => $Country_Id,
                    'NdType_Id' => $NdType_Id,
                    'comment' => $substanceItems['comment']
                );
                $query_add_DR_RegisterSubstances = "
					insert into r101.DR_RegisterSubstances (
						ID,
						Register_id,
						SubstanceType_Id,
						Substance_Id,
						SubstanceCount,
						Measure_Id,
						Producer_Id,
						Country_Id,
						NdType_Id,
						comment
					)
					values (
						:ID,
						:Register_id,
						:SubstanceType_Id,
						:Substance_Id,
						:SubstanceCount,
						:Measure_Id,
						:Producer_Id,
						:Country_Id,
						:NdType_Id,
						:comment
					)
					returning null as \"Error_Code\", null as \"Error_Msg\"
				";
                $result_add_DR_RegisterSubstances = $this->queryResult($query_add_DR_RegisterSubstances, $params_add_DR_RegisterSubstances);
            }
        }
        //Добавляем данные в DR_RegisterDrugs
        $params_add_DR_RegisterDrugs = array(
            'ID' => $detailsDrug['id'],
            'DrugType_Id' => $DrugType_Id,
            'IntName_Id' => $IntName_Id,
            'Atc_Id' => $Atc_Id,
            'DosageForm_Id' => $DosageForm_Id,
            'DosageComment_Ru' => $detailsDrug['dosageCommentRu'],
            'DosageComment_Kz' => $detailsDrug['dosageCommentKz'],
            'DosageValue' => $detailsDrug['dosageValue'],
            'DosageMeasure_Id' => $DosageMeasure_Id,
            'Concentration_Ru' => $detailsDrug['concentrationRu'],
            'Concentration_Kz' => $detailsDrug['concentrationKz'],
            'RecipeSign' => ($detailsDrug['isRecipe'] == true) ? 2 : 1,
            'GenericSign' => ($detailsDrug['isGeneric'] == true) ? 2 : 1,
            'LifeType_id' => 0,
            'Category_Id' => null,
            'CategoryPos' => null,
            'NdName_id' => null,
            'NdName' => null,
            'NdNumber' => null,
            'NdComment' => null,
            'Substance' => null,
            'BiosimilarSign' => 0,
            'AutoGenericSign' => 0,
            'OrphanSign' => 0,
            'Prep_id' => null
        );
        $query_add_DR_RegisterDrugs = "
			insert into r101.DR_RegisterDrugs (
				ID,
				DrugType_Id,
				IntName_Id,
				Atc_Id,
				DosageForm_Id,
				DosageComment_Ru,
				DosageComment_Kz,
				DosageValue,
				DosageMeasure_Id,
				Concentration_Ru,
				Concentration_Kz,
				RecipeSign,
				GenericSign,
				LifeType_id,
				Category_Id,
				CategoryPos,
				NdName_id,
				NdName,
				NdNumber,
				NdComment,
				Substance,
				BiosimilarSign,
				AutoGenericSign,
				OrphanSign,
				Prep_id
			)
			values (
				:ID,
				:DrugType_Id,
				:IntName_Id,
				:Atc_Id,
				:DosageForm_Id,
				:DosageComment_Ru,
				:DosageComment_Kz,
				:DosageValue,
				:DosageMeasure_Id,
				:Concentration_Ru,
				:Concentration_Kz,
				:RecipeSign,
				:GenericSign,
				:LifeType_id,
				:Category_Id,
				:CategoryPos,
				:NdName_id,
				:NdName,
				:NdNumber,
				:NdComment,
				:Substance,
				:BiosimilarSign,
				:AutoGenericSign,
				:OrphanSign,
				:Prep_id
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_add_DR_RegisterDrugs, $params_add_DR_RegisterDrugs);die;
        $result_add_DR_RegisterSubstances = $this->queryResult($query_add_DR_RegisterDrugs, $params_add_DR_RegisterDrugs);
        return true;

    }

    /**
     *    Добавление регистрационного досье ИМН и МТ (тег detailsTechnic)
     */
    function addDetailsTechnic($detailsTechnic, $DR_Register_id)
    {
        //var_dump($detailsTechnic['description']);die;
        $MtCategory_Id = null;
        $DegreeRisk_Id = null;
        $RiskDetail_Id = null;
        $query_check_DR_DicMtCategories = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicMtCategories
			where ID = :ID
		";
        $query_check_DR_DicDegreeRisks = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicDegreeRisks
			where ID = :ID
		";
        $query_check_DR_DicDegreeRiskDetails = "
			select count(ID) as \"ctnID\"
			from r101.DR_DicDegreeRiskDetails
			where ID = :ID
		";
        $result_check_DR_DicMtCategories = $this->db->query($query_check_DR_DicMtCategories, array('ID' => $detailsTechnic['mtcategoryid']));
        $result_check_DR_DicMtCategories = $result_check_DR_DicMtCategories->result('array');
        if ($result_check_DR_DicMtCategories[0]['ctnID'] > 0)
            $MtCategory_Id = $detailsTechnic['mtcategoryid'];

        $result_check_DR_DicDegreeRisks = $this->db->query($query_check_DR_DicDegreeRisks, array('ID' => $detailsTechnic['degreeriskid']));
        $result_check_DR_DicDegreeRisks = $result_check_DR_DicDegreeRisks->result('array');
        if ($result_check_DR_DicDegreeRisks[0]['ctnID'] > 0)
            $DegreeRisk_Id = $detailsTechnic['degreeriskid'];

        $result_check_DR_DicDegreeRiskDetails = $this->db->query($query_check_DR_DicDegreeRiskDetails, array('ID' => $detailsTechnic['riskdetailid']));
        $result_check_DR_DicDegreeRiskDetails = $result_check_DR_DicDegreeRiskDetails->result('array');
        if ($result_check_DR_DicDegreeRiskDetails[0]['ctnID'] > 0)
            $RiskDetail_Id = $detailsTechnic['riskdetailid'];

        $params_add_DR_RegisterMt = array(
            'ID' => $DR_Register_id,
            'Description' => mb_substr($detailsTechnic['description'], 0, 2000),
            'Purpose' => $detailsTechnic['purpose'],
            'UseArea' => $detailsTechnic['usageArea'],
            'MtCategory_Id' => $MtCategory_Id,//$detailsTechnic['mtcategoryid'],
            'DegreeRisk_Id' => $DegreeRisk_Id,//$detailsTechnic['degreeriskid'],
            'RiskDetail_Id' => $RiskDetail_Id,//$detailsTechnic['riskdetailid'],
            'MtSign' => ($detailsTechnic['mtsign'] == true) ? 2 : 1,
            'SterilitySign' => ($detailsTechnic['sterilitysign'] == true) ? 2 : 1,
            'MeasurementSign' => ($detailsTechnic['measurementsign'] == true) ? 2 : 1,
            'BalkSign' => ($detailsTechnic['balksign'] == true) ? 2 : 1,
            'Prep_id' => null
        );
        $query_add_DR_RegisterMt = "
			insert into r101.DR_RegisterMt (
				ID,
				Description,
				Purpose,
				UseArea,
				MtCategory_Id,
				DegreeRisk_Id,
				RiskDetail_Id,
				MtSign,
				SterilitySign,
				MeasurementSign,
				BalkSign,
				Prep_id
			)
			values (
				:ID,
				:Description,
				:Purpose,
				:UseArea,
				:MtCategory_Id,
				:DegreeRisk_Id,
				:RiskDetail_Id,
				:MtSign,
				:SterilitySign,
				:MeasurementSign,
				:BalkSign,
				:Prep_id
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_add_DR_RegisterMt, $params_add_DR_RegisterMt);die;
        $result_add_DR_RegisterMt = $this->queryResult($query_add_DR_RegisterMt, $params_add_DR_RegisterMt);
        return true;
    }

    /**
     * Добавление информации о комплектации ИМН и МТ (тег registermtparts)
     */
    function addRegistermtparts($registermtparts, $DR_Register_id)
    {
        $params_add_DR_RegisterMtParts = array(
            'ID' => $registermtparts['id'],
            'Register_Id' => $DR_Register_id,
            'Name_Ru' => mb_substr($registermtparts['nameru'], 0, 2000),
            'Name_Kz' => mb_substr($registermtparts['namekz'], 0, 2000),
            'PartNumber' => $registermtparts['partnumber'],
            'Model' => $registermtparts['model'],
            'Specification_Ru' => $registermtparts['specificationru'],
            'Specification_Kz' => $registermtparts['specificationkz'],
            'ProducerName_Ru' => $registermtparts['producernameru'],
            'ProducerName_Kz' => $registermtparts['producernamekz'],
            'CountryName_Ru' => $registermtparts['countrynameru'],
            'CountryName_Kz' => $registermtparts['countrynamekz'],
            'sulo_id' => null
        );
        $query_add_DR_RegisterMtParts = "
			insert into r101.DR_RegisterMtParts (
				ID,
				Register_Id,
				Name_Ru,
				Name_Kz,
				PartNumber,
				Model,
				Specification_Ru,
				Specification_Kz,
				ProducerName_Ru,
				ProducerName_Kz,
				CountryName_Ru,
				CountryName_Kz,
				sulo_id
			)
			values (
				:ID,
				:Register_Id,
				:Name_Ru,
				:Name_Kz,
				:PartNumber,
				:Model,
				:Specification_Ru,
				:Specification_Kz,
				:ProducerName_Ru,
				:ProducerName_Kz,
				:CountryName_Ru,
				:CountryName_Kz,
				:sulo_id
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        $result_add_DR_RegisterMtParts = $this->queryResult($query_add_DR_RegisterMtParts, $params_add_DR_RegisterMtParts);
        return true;
    }

    /**
     * Добавление информации о сертификатах (тег Certificate)
     */
    function addCertificate($certificate, $DR_Register_id)
    {
        $regdate = new DateTime($certificate['regdate']);
        $enddate = new DateTime($certificate['enddate']);
        $dateprolongation = new DateTime($certificate['dateprolongation']);
        $annulmentdate = new DateTime($certificate['annulmentdate']);
        $refusedate = new DateTime($certificate['refusedate']);

        $params_add_DR_Certifications = array(
            'ID' => $certificate['id'],
            'Register_Id' => $DR_Register_id,
            'Reg_Date' => (isset($certificate['regdate'])) ? $regdate->format('Y-m-d') : '1900-01-01',
            'End_Date' => (isset($certificate['enddate'])) ? $enddate->format('Y-m-d') : '2099-01-01',
            'Reg_number' => $certificate['regnumber'],
            'Blank_Number' => $certificate['blanknumber'],
            'Product_name' => $certificate['productname'],
            'Series' => $certificate['series'],
            'Date_prolongation' => (isset($certificate['dateprolongation'])) ? $dateprolongation->format('Y-m-d') : null,        //$certificate['dateprolongation'],
            'Annulment_sign' => ($certificate['annulmentsign'] == true) ? 2 : 1,
            'Annulment_date' => (isset($certificate['annulmentdate'])) ? $annulmentdate->format('Y-m-d') : null,        //$certificate['annulmentdate'],
            'Annulment_reason' => $certificate['annulmentreason'],
            'Dublicate_sign' => ($certificate['dublicatesign'] == true) ? 2 : 1,
            'Refuse_sign' => ($certificate['refusesign'] == true) ? 2 : 1,
            'Refuse_date' => (isset($certificate['refusedate'])) ? $refusedate->format('Y-m-d') : null,         //$certificate['refusedate'],
            'Refuse_reason' => $certificate['refusereason'],
            'Argument' => $certificate['argument'],
            'Dept_name' => $certificate['deptname'],
            'Register_number' => $certificate['registernumber'],
            'Application_type_name' => $certificate['applicationtypename']
        );
        $query_add_DR_Certifications = "
			insert into r101.DR_Certifications (
				ID,
				Register_Id,
				Reg_Date,
				End_Date,
				Reg_number,
				Blank_Number,
				Product_name,
				Series,
				Date_prolongation,
				Annulment_sign,
				Annulment_date,
				Annulment_reason,
				Dublicate_sign,
				Refuse_sign,
				Refuse_date,
				Refuse_reason,
				Argument,
				Dept_name,
				Register_number,
				Application_type_name
			)
			values (
				:ID,
				:Register_Id,
				:Reg_Date,
				:End_Date,
				:Reg_number,
				:Blank_Number,
				:Product_name,
				:Series,
				:Date_prolongation,
				:Annulment_sign,
				:Annulment_date,
				:Annulment_reason,
				:Dublicate_sign,
				:Refuse_sign,
				:Refuse_date,
				:Refuse_reason,
				:Argument,
				:Dept_name,
				:Register_number,
				:Application_type_name
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        //echo getDebugSQL($query_add_DR_Certifications, $params_add_DR_Certifications);die;
        $result_add_DR_Certifications = $this->queryResult($query_add_DR_Certifications, $params_add_DR_Certifications);
        return true;
    }

    /**
     * Добавление информации по ИМИРК (тег registerNmirk)
     */
    function addRegisterNmirk($RegisterNmirk, $DR_Register_id)
    {
        $params_add_Register_nmirk = array(
            'ID' => $RegisterNmirk['id'],
            'Register_Id' => $DR_Register_id,
            'Code' => $RegisterNmirk['code'],
            'Comments' => isset($RegisterNmirk['comments']) ? $RegisterNmirk['comments'] : ''
        );
        $query_add_Register_nmirk = "
			insert into r101.Register_nmirk (
				ID,
				Register_Id,
				Code,
				Comments
			)
			values (
				:ID,
				:Register_Id,
				:Code,
				:Comments
			)
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
        $result_add_Register_nmirk = $this->queryResult($query_add_Register_nmirk, $params_add_Register_nmirk);
        return true;
    }

    /**
     *    Добавление информации о DR_Register в ObjectSynchronLog
     */
    function AddRegisterToSyncronLog($id)
    {
        $query = "
			select 
			    ObjectSynchronLog_id as \"ObjectSynchronLog_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_ObjectSynchronLog_ins (
				ObjectSynchronLog_id := null,
				Object_Name := 'DR_Register',
				Object_setDT := dbo.tzGetDate(),
				Object_id := {$id},
				ObjectSynchronLogService_id := 5,
				pmUser_id := 1
			);
		";
        $resp = $this->queryResult($query, array());
        if (is_array($resp)) {
            if (isset($resp[0]['ObjectSynchronLog_id']))
                return $resp[0]['ObjectSynchronLog_id'];
            else
                return 0;
        } else
            return 0;
    }

    /**
     * Выполнение скриптов по обновлегию рлс-ных таблиц
     */
    function execDBUpdate()
    {
        $query = "
    		select 
    		    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from r101.xp_RLSUpdate ();
    	";
        $result = $this->db->query($query, array());
        if (is_object($result)) {
            $result = $result->result('array');
            if (is_array($result))
                return $result;
            else {
                $result = array(
                    'success' => false,
                    'errorMsg' => 'Ошибка 1'
                );
                return $result;
            }
        } else {
            $result = array(
                'success' => false,
                'errorMsg' => 'Ошибка 2'
            );
            return $result;
        }
    }
}

?>