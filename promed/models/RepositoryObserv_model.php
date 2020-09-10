<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      03.04.2020
 */

class RepositoryObserv_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'RepositoryObserv';
	}
	
	/**
	 * Возвращает правила проверки данных для метода сохранения
	 */
	function getSaveRules() {
		return [
			[ 'field' => 'RepositoryObserv_id', 'label' => 'Иденнтификатор наблюдения', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'Evn_id', 'label' => 'Родительское событие', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'CmpCallCard_id', 'label' => 'Карта СМП', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'HomeVisit_id', 'label' => 'Вызов врача на дом', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'CVIQuestion_id', 'label' => 'Иденнтификатор анкеты', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'LpuWardType_id', 'label' => 'Тип палаты', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Cough_id', 'label' => 'Кашель', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'CovidType_id', 'label' => 'Коронавирус', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'DiagConfirmType_id', 'label' => 'Диагноз подтвержден рентгенологически', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'DiagSetPhase_id', 'label' => 'Состояние', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Dyspnea_id', 'label' => 'Одышка', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'GenConditFetus_id', 'label' => 'Общее состояние плода', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'IVLRegim_id', 'label' => 'Типы режимов ИВЛ', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'KLCountry_id', 'label' => 'Страна прибытия', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'MedPersonal_Email', 'label' => 'Электронная почта врача', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'MedPersonal_Phone', 'label' => 'Контактный телефон врача', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'PlaceArrival_id', 'label' => 'Место прибытия', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'KLRgn_id', 'label' => 'Регион прибытия', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'TransportMeans_id', 'label' => 'Средство передвижения при въезде в РФ', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_arrivalDate', 'label' => 'Дата прибытия', 'rules' => '', 'type' => 'date' ],
			[ 'field' => 'RepositoryObserv_BreathFrequency', 'label' => 'ЧДД, в минуту', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_BreathPeep', 'label' => 'ПДКВ (PEEP)', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_BreathPressure', 'label' => 'Давление на вдохе (Ppeak)', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_BreathRate', 'label' => 'Частота дыхания (f), в мин', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_BreathVolume', 'label' => 'Дыхательный объем, мл', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_GLU', 'label' => 'Уровень сахара крови', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_Cho', 'label' => 'Общий холестерин', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_CVIQuestionNotReason', 'label' => 'Причина непрохождения опроса', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_Diastolic', 'label' => 'АД диастолическое', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_FiO2', 'label' => 'FiO2', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_FlightNumber', 'label' => 'Номер рейса прибытия', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_Height', 'label' => 'Рос пациента, см', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_Hemoglobin', 'label' => 'гемоглобин', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_IsAntivirus', 'label' => 'Признак «Противовирусное лечение»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsCVIContact', 'label' => 'Признак «Контакт с зараженным КВИ»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsCVIQuestion', 'label' => 'Признак «Прохождение опроса/наблюдения по КВИ»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsEKMO', 'label' => 'Признак «ЭКМО»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsHighTemperature', 'label' => 'Признак «Повышенная температура»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsMyoplegia', 'label' => 'Признак «Миоплегия»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsPronPosition', 'label' => 'Признак «Прон-позиция»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsResuscit', 'label' => 'Признак «Реанимация»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsRunnyNose', 'label' => 'Признак «Насморк»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsSedation', 'label' => 'Признак «Седация»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsSoreThroat', 'label' => 'Признак «Боль в горле»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_IsSputum', 'label' => 'Признак «Мокрота»', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_Leukocytes', 'label' => 'лейкоциты', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_Lymphocytes', 'label' => 'лимфоциты', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_NumberTMK', 'label' => 'Номер ТМК', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_Other', 'label' => 'Иные симптомы', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_PaO2', 'label' => 'PaO2', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_PaO2FiO2', 'label' => 'PaO2/FiO2', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_IVL', 'label' => 'ИВЛ', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_PH', 'label' => 'РН', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_Oxygen', 'label' => 'Кислород', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'RepositoryObserv_Platelets', 'label' => 'тромбоциты', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_PregnancyPeriod', 'label' => 'Беременность, недель', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_Pulse', 'label' => 'ЧСС, в минуту', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_RegimVenting', 'label' => 'Режим вентиляции (аббревиатура)', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_setDate', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date' ],
			[ 'field' => 'RepositoryObserv_setTime', 'label' => 'Время', 'rules' => 'required', 'type' => 'time' ],
			[ 'field' => 'RepositoryObserv_SOE', 'label' => 'СОЭ', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_SpO2', 'label' => 'SpO2', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_SRB', 'label' => 'СРБ', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_Systolic', 'label' => 'АД систолическое', 'rules' => '', 'type' => 'int' ],
			[ 'field' => 'RepositoryObserv_TemperatureFrom', 'label' => 'Температура от', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_TemperatureTo', 'label' => 'Температура до', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'RepositoryObserv_TransportDesc', 'label' => 'Средство передвижения при въезде в РФ (детально)', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_TransportPlace', 'label' => 'Место въезда на территорию РФ', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_TransportRoute', 'label' => 'Маршрут передвижения по РФ', 'rules' => '', 'type' => 'string' ],
			[ 'field' => 'RepositoryObserv_Weight', 'label' => 'Вес пациента, кг', 'rules' => '', 'type' => 'float' ],
			[ 'field' => 'StateDynamic_id', 'label' => 'Динамика', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'PersonQuarantine_id', 'label' => 'ИД пациента на карантине', 'rules' => '', 'type' => 'int'],
			[ 'field' => 'RepositoryObesrv_contactDate', 'label' => 'Дата контакта', 'rules' => '', 'type' => 'date'],
			[ 'field' => 'ignoreRemoteConsultCheck', 'label' => 'Контроль необходимости создания направления', 'rules' => 'trim', 'type' => 'int'],
			[ 'field' => 'createConsult', 'label' => 'Нужно ли создавать необходимость консультации', 'rules' => 'trim', 'type' => 'boolean']
		];
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function delete($data = []) {
		
		$res = $this->queryResult( "
			select CVIConsultRKC_id
			from v_CVIConsultRKC with (nolock)
			where RepositoryObserv_id = :RepositoryObserv_id or RepositoryObserv_sid = :RepositoryObserv_id
		", [
			'RepositoryObserv_id' => $data['RepositoryObserv_id'],
		]);

		if (isset($res[0]['CVIConsultRKC_id'])) {
			foreach ($res as $item) {
				$this->queryResult("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
		
					exec dbo.p_CVIConsultRKC_del
						@CVIConsultRKC_id = :CVIConsultRKC_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
		
					select
						@Error_Code as \"Error_Code\",
						@Error_Message as \"Error_Msg\";
				", ['CVIConsultRKC_id' => $item['CVIConsultRKC_id']]);
			}
		}
		
		return $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec dbo.p_RepositoryObserv_del
				@RepositoryObserv_id = :RepositoryObserv_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@Error_Code as \"Error_Code\",
				@Error_Message as \"Error_Msg\";
		", [
			'RepositoryObserv_id' => $data['RepositoryObserv_id'],
		]);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function getRepositoryObservDefaultData($data = []) {
		$response = $this->getFirstRowFromQuery("
			select top 1
				 PS.Sex_id as \"Sex_id\"
				,RO.RepositoryObserv_Height as \"RepositoryObserv_Height\"
				,RO.RepositoryObserv_Weight as \"RepositoryObserv_Weight\"
				,RO.RepositoryObserv_PregnancyPeriod as \"RepositoryObserv_PregnancyPeriod\"
				,RO.DiagConfirmType_id as \"DiagConfirmType_id\"
				,RO.GenConditFetus_id as \"GenConditFetus_id\"
				,case when ERA13.EvnReanimatAction_id is not null then 2 else null end as \"RepositoryObserv_IsPronPosition\"
				,case when ERA14.EvnReanimatAction_id is not null then 2 else null end as \"RepositoryObserv_IsMyoplegia\"
				,case when ERA15.EvnReanimatAction_id is not null then 2 else null end as \"RepositoryObserv_IsEKMO\"
				,case when ERA13.EvnReanimatAction_id is not null or ERA14.EvnReanimatAction_id is not null or ERA.EvnReanimatAction_id is not null then 3 else null end as \"RepositoryObserv_IVL\"
				,case when ERP.EvnReanimatPeriod_id is not null then 2 else null end as \"RepositoryObserv_IsResuscit\"
				,ERA.IVLRegim_id as \"IVLRegim_id\"
				,ERA.IVLParameter_FrequSet as \"RepositoryObserv_BreathRate\"
				,ERA.IVLParameter_VolE as \"RepositoryObserv_BreathVolume\"
				,ERA.IVLParameter_Peak as \"RepositoryObserv_BreathPressure\"
				,ERA.IVLParameter_PEEP as \"RepositoryObserv_BreathPeep\"
				,ERC.EvnReanimatCondition_OxygenFraction as \"RepositoryObserv_FiO2\"
				,ERC.EvnReanimatCondition_OxygenPressure as \"RepositoryObserv_PaO2\"
				,ERC.EvnReanimatCondition_PaOFiO as \"RepositoryObserv_PaO2FiO2\"
			from dbo.v_PersonState as PS with (nolock)
				outer apply (
					select top 1
						RepositoryObserv_Height,
						RepositoryObserv_Weight,
						RepositoryObserv_PregnancyPeriod,
						DiagConfirmType_id,
						GenConditFetus_id
					from dbo.v_RepositoryObserv with (nolock)
					where Person_id = :Person_id
					order by RepositoryObserv_updDT desc
				) RO
				outer apply (
					select top 1
						t1.EvnReanimatAction_id as \"EvnReanimatAction_id\",
						t2.IVLRegim_id as \"IVLRegim_id\",
						t2.IVLParameter_FrequSet as \"IVLParameter_FrequSet\",
						t2.IVLParameter_VolE as \"IVLParameter_VolE\",
						t2.IVLParameter_Peak as \"IVLParameter_Peak\",
						t2.IVLParameter_PEEP as \"IVLParameter_PEEP\"
					from dbo.v_EvnReanimatAction as t1 with (nolock)
						left join dbo.v_IVLParameter as t2 with (nolock) on t2.EvnReanimatAction_id = t1.EvnReanimatAction_id
					where Person_id = :Person_id
						and t1.EvnReanimatAction_disDT is null
						and t1.ReanimatActionType_id = 1
					order by t1.EvnReanimatAction_setDT desc
				) ERA
				outer apply (
					select top 1
						t1.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
					from dbo.v_EvnReanimatPeriod as t1 with (nolock)
					where Person_id = :Person_id
						and t1.EvnReanimatPeriod_disDT is null
					order by t1.EvnReanimatPeriod_setDT desc
				) ERP
				outer apply (
					select top 1
						t1.EvnReanimatCondition_id,
						t1.EvnReanimatCondition_OxygenFraction,
						t1.EvnReanimatCondition_OxygenPressure,
						t1.EvnReanimatCondition_PaOFiO
					from
						dbo.v_EvnReanimatCondition as t1 with (nolock)
					where
						Person_id = :Person_id
					order by
						t1.EvnReanimatCondition_setDT desc
				) ERC
				outer apply (
					select top 1
						t1.EvnReanimatAction_id as \"EvnReanimatAction_id\"
					from dbo.v_EvnReanimatAction as t1 with (nolock)
					where Person_id = :Person_id
						and t1.EvnReanimatAction_disDT is null
						and t1.ReanimatActionType_id = 13
					order by t1.EvnReanimatAction_setDT desc
				) ERA13
				outer apply (
					select top 1
						t1.EvnReanimatAction_id as \"EvnReanimatAction_id\"
					from dbo.v_EvnReanimatAction as t1 with (nolock)
					where Person_id = :Person_id
						and t1.EvnReanimatAction_disDT is null
						and t1.ReanimatActionType_id = 14
					order by t1.EvnReanimatAction_setDT desc
				) ERA14
				outer apply (
					select top 1
						t1.EvnReanimatAction_id as \"EvnReanimatAction_id\"
					from dbo.v_EvnReanimatAction as t1 with (nolock)
					where Person_id = :Person_id
						and t1.EvnReanimatAction_disDT is null
						and t1.ReanimatActionType_id = 15
					order by t1.EvnReanimatAction_setDT desc
				) ERA15
			where PS.Person_id = :Person_id
		", [
			'Person_id' => $data['Person_id'],
		]);

		if (is_array($response) && count($response) > 0) {
			if (empty($response['RepositoryObserv_Height'])) {
				$tempResponse = $this->getFirstResultFromQuery("
					select top 1 PersonHeight_Height as \"PersonHeight_Height\"
					from dbo.v_PersonHeight with (nolock)
					where Person_id = :Person_id
						and PersonHeight_Height is not null
					order by PersonHeight_setDT desc
				", [
					'Person_id' => $data['Person_id'],
				]);

				if (!empty($tempResponse)) {
					$response['RepositoryObserv_Height'] = $tempResponse;
				}
			}

			if (empty($response['RepositoryObserv_Weight'])) {
				$tempResponse = $this->getFirstRowFromQuery("
					select top 1
						PersonWeight_Weight as \"PersonWeight_Weight\",
						Okei_id as \"Okei_id\"
					from dbo.v_PersonWeight with (nolock)
					where Person_id = :Person_id
						and PersonWeight_Weight is not null
					order by PersonWeight_setDT desc
				", [
					'Person_id' => $data['Person_id'],
				]);

				if ($tempResponse !== false && is_array($tempResponse) && !empty($tempResponse['PersonWeight_Weight'])) {
					if ($tempResponse['Okei_id'] == 36) {
						$tempResponse['PersonWeight_Weight'] = $tempResponse['PersonWeight_Weight'] / 1000;
					}

					$response['RepositoryObserv_Weight'] = $tempResponse['PersonWeight_Weight'];
				}
			}

			if ($response['Sex_id'] == 2) {
				$tempResponse = $this->getFirstResultFromQuery("
					select top 1
						ISNULL(PP.PersonPregnancy_Period, 0)
							+ DATEDIFF(week, PP.PersonPregnancy_dispDate, dbo.tzgetdate())
						as \"PersonPregnancy_Period\" 
					from dbo.v_PersonPregnancy as PP with (nolock)
						inner join dbo.PersonRegister as PR with (nolock) on PR.PersonRegister_id = PP.PersonRegister_id
					where PP.Person_id = :Person_id
						and PP.PersonPregnancy_dispDate is not null
						and PR.PersonRegister_disDate is null
						and PR.PersonRegisterOutCause_id is null
						and PR.PersonRegister_delDT is null
						and PR.PregnancyResult_id is null
				", [
					'Person_id' => $data['Person_id'],
				]);

				if (!empty($tempResponse) && $tempResponse <= 50) {
					$response['RepositoryObserv_PregnancyPeriod'] = $tempResponse;
				}
			}
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function getUseCase($data) {
		$response = ['Error_Msg' => '', 'useCase' => 'evnsection'];
		$resp = $this->queryResult("
			select
				case
					when E.EvnClass_SysNick = 'EvnVizitPL' then 'evnvizitpl'
					when E.EvnClass_SysNick = 'EvnSection' then 'evnsection'
				end as useCase
			from
				v_RepositoryObserv RO (nolock)
				left join v_Evn E (nolock) on E.Evn_id = RO.Evn_id
			where
				RepositoryObserv_id = :RepositoryObserv_id
		", [
			'RepositoryObserv_id' => $data['RepositoryObserv_id']
		]);

		$response['useCase'] = $resp[0]['useCase'] ?? $response['useCase'];

		return $response;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function load($data = []) {
		return $this->queryResult("
			select top 1
				 RepositoryObserv_id
				,PersonQuarantine_id
				,Evn_id
				,CmpCallCard_id
				,HomeVisit_id
				,CVIQuestion_id
				,LpuWardType_id
				,convert(varchar(10), RepositoryObserv_setDT, 104) as RepositoryObserv_setDate
				,convert(varchar(5), RepositoryObserv_setDT, 108) as RepositoryObserv_setTime
				,Lpu_id
				,MedPersonal_id
				,MedStaffFact_id
				,MedPersonal_Phone
				,MedPersonal_Email
				,Person_id
				,RepositoryObserv_Height
				,RepositoryObserv_Weight
				,convert(varchar(10), RepositoryObesrv_contactDate, 104) as RepositoryObesrv_contactDate
				,PlaceArrival_id
				,KLCountry_id
				,KLRgn_id
				,TransportMeans_id
				,convert(varchar(10), RepositoryObserv_arrivalDate, 104) as RepositoryObserv_arrivalDate
				,RepositoryObserv_FlightNumber
				,RepositoryObserv_IsAntivirus
				,RepositoryObserv_IsCVIContact
				,RepositoryObserv_IsCVIQuestion
				,RepositoryObserv_IsEKMO
				,RepositoryObserv_CVIQuestionNotReason
				,RepositoryObserv_IsHighTemperature
				,RepositoryObserv_TemperatureFrom
				,RepositoryObserv_TemperatureTo
				,RepositoryObserv_TransportDesc
				,RepositoryObserv_TransportPlace
				,RepositoryObserv_TransportRoute
				,RepositoryObserv_IsRunnyNose
				,RepositoryObserv_IsSoreThroat
				,RepositoryObserv_Pulse
				,RepositoryObserv_BreathFrequency
				,RepositoryObserv_BreathVolume
				,RepositoryObserv_BreathPressure
				,RepositoryObserv_BreathPeep
				,RepositoryObserv_IsPronPosition
				,RepositoryObserv_IsResuscit
				,RepositoryObserv_IsMyoplegia
				,RepositoryObserv_Systolic
				,RepositoryObserv_Diastolic
				,RepositoryObserv_Cho
				,RepositoryObserv_GLU
				,Dyspnea_id
				,Cough_id
				,CovidType_id
				,DiagConfirmType_id
				,RepositoryObserv_IsSputum
				,RepositoryObserv_NumberTMK
				,RepositoryObserv_Other
				,RepositoryObserv_PregnancyPeriod
				,GenConditFetus_id
				,RepositoryObserv_Hemoglobin
				,RepositoryObserv_Leukocytes
				,RepositoryObserv_Lymphocytes
				,RepositoryObserv_Platelets
				,RepositoryObserv_SOE
				,RepositoryObserv_SRB
				,RepositoryObserv_PH
				,case
					when RepositoryObserv_isOxygen = 2 then 3
					when RepositoryObserv_isNeedOxygen = 2 then 2
					else 1
				end as RepositoryObserv_Oxygen
				,RepositoryObserv_PaO2
				,RepositoryObserv_FiO2
				,RepositoryObserv_PaO2FiO2
				,case
					when RepositoryObserv_isIVL = 2 then 3
					when RepositoryObserv_isNeedIVL = 2 then 2
					else 1
				end as RepositoryObserv_IVL
				,RepositoryObserv_SpO2
				,StateDynamic_id
				,DiagSetPhase_id
				,RepositoryObserv_IsSedation
				,IVLRegim_id
				,RepositoryObserv_RegimVenting
				,RepositoryObserv_BreathRate
			from dbo.v_RepositoryObserv with (nolock)
			where RepositoryObserv_id = :RepositoryObserv_id
		", [
			'RepositoryObserv_id' => $data['RepositoryObserv_id'],
		]);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function loadList($data = []) {
		return $this->queryResult("
			select
				RO.RepositoryObserv_id,
				convert(varchar(10), RO.RepositoryObserv_setDT, 104) + ' ' + convert(varchar(5), RO.RepositoryObserv_setDT, 108) as RepositoryObserv_setDT,
				MSF.Person_Fio as MedPersonal_FIO
			from dbo.v_RepositoryObserv as RO with (nolock)
				inner join v_MedStaffFact as MSF with (nolock) on MSF.MedStaffFact_id = RO.MedStaffFact_id
			where RO.Evn_id = :Evn_id
		", [
			'Evn_id' => $data['Evn_id'],
		]);
	}

	public function loadQuarantineList($data = []) {
		$params = [
			'PersonQuarantine_id' => $data['PersonQuarantine_id']
		];
		$query = "
			select
				RO.RepositoryObserv_id,
				RO.personquarantine_id,
				RO.person_id,
				RO.Cough_id,
				RO.Dyspnea_id,
				RO.RepositoryObserv_IsSputum,
				convert(varchar(10), RO.RepositoryObserv_setDT, 104) + ' ' + convert(varchar(5), RO.RepositoryObserv_setDT, 108) as RepositoryObserv_setDT,
				RO.RepositoryObserv_TemperatureTo,
				RO.RepositoryObserv_TemperatureFrom,
				RO.RepositoryObserv_Systolic,
				RO.RepositoryObserv_Diastolic,
				RO.RepositoryObserv_SpO2,
				RO.RepositoryObserv_BreathFrequency,
				RO.RepositoryObserv_IsRunnyNose,
				RO.RepositoryObserv_IsSoreThroat,
				RO.RepositoryObserv_Pulse,
				RO.RepositoryObserv_GLU,
				RO.RepositoryObserv_Cho,
				RO.RepositoryObserv_Other,
				D.Dyspnea_Name,
				C.Cough_Name
			from RepositoryObserv RO with (nolock)
			left join v_Cough C with(nolock) on C.Cough_id = RO.Cough_id
			left join v_Dyspnea D with(nolock) on D.Dyspnea_id = RO.Dyspnea_id
			where RO.PersonQuarantine_id = :PersonQuarantine_id and isnull(RO.RepositoryObesrv_IsFirstRecord,1) != 2
			ORDER BY RO.RepositoryObserv_setDT desc
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function loadSopDiagList($data = []) {
		$parentEvnData = $this->getFirstRowFromQuery("
			select
				EvnClass_id,
				Evn_pid
			from v_Evn with (nolock)
			where Evn_id = :Evn_id
		", [
			'Evn_id' => $data['Evn_id']
		]);

		if ($parentEvnData !== false && is_array($parentEvnData) && count($parentEvnData) > 0 && $parentEvnData['EvnClass_id'] == 193) {
			$data['Evn_id'] = $parentEvnData['Evn_pid'];
		}

		return $this->queryResult("
			select distinct
				D.Diag_id,
				D.Diag_FullName
			from dbo.v_EvnDiag as ED with (nolock)
				inner join v_Diag as D with (nolock) on D.Diag_id = ED.Diag_id
			where ED.EvnDiag_pid = :Evn_id
				and ED.DiagSetClass_id in (2, 3, 6, 7)
		", [
			'Evn_id' => $data['Evn_id'],
		]);
	}

	public function doSave($data = array(), $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			
			$tmp = $this->save($data);
			
			$this->afterSave($data, $tmp);
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			if ($this->isDebug && $e->getCode() == 500) {
				// только на тестовом и только, если что-то пошло не так
				$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		
		return $this->_saveResponse;
	}
	/**
	 * @param array $data
	 * @return array|false
	 */
	public function save($data = []) {
		$data['RepositoryObserv_setDT'] = $data['RepositoryObserv_setDate'] . ' ' . $data['RepositoryObserv_setTime'];
		
		$data['RepositoryObserv_isNeedIVL'] = $data['RepositoryObserv_IVL'] == 2 ? 2 : 1;
		$data['RepositoryObserv_isIVL'] = $data['RepositoryObserv_IVL'] == 3 ? 2 : 1;
		$data['RepositoryObserv_isNeedOxygen'] = $data['RepositoryObserv_Oxygen'] == 2 ? 2 : 1;
		$data['RepositoryObserv_isOxygen'] = $data['RepositoryObserv_Oxygen'] == 3 ? 2 : 1;

		return $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@RepositoryObserv_id bigint = :RepositoryObserv_id;

			exec dbo.p_RepositoryObserv_" . (!empty($data['RepositoryObserv_id']) ? "upd" : "ins") ."
				@RepositoryObserv_id = @RepositoryObserv_id output,
				@PersonQuarantine_id = :PersonQuarantine_id,
				@Evn_id = :Evn_id,
				@CmpCallCard_id = :CmpCallCard_id,
				@HomeVisit_id = :HomeVisit_id,
				@CVIQuestion_id = :CVIQuestion_id,
				@LpuWardType_id = :LpuWardType_id,
				@RepositoryObserv_setDT = :RepositoryObserv_setDT,
				@Lpu_id = :Lpu_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_Phone = :MedPersonal_Phone,
				@MedPersonal_Email = :MedPersonal_Email,
				@Person_id = :Person_id,
				@RepositoryObserv_Height = :RepositoryObserv_Height,
				@RepositoryObserv_Weight = :RepositoryObserv_Weight,
				@RepositoryObesrv_contactDate = :RepositoryObesrv_contactDate,
				@PlaceArrival_id = :PlaceArrival_id,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@TransportMeans_id = :TransportMeans_id,
				@RepositoryObserv_arrivalDate = :RepositoryObserv_arrivalDate,
				@RepositoryObserv_FlightNumber = :RepositoryObserv_FlightNumber,
				@RepositoryObserv_IsAntivirus = :RepositoryObserv_IsAntivirus,
				@RepositoryObserv_IsCVIContact = :RepositoryObserv_IsCVIContact,
				@RepositoryObserv_IsCVIQuestion = :RepositoryObserv_IsCVIQuestion,
				@RepositoryObserv_IsEKMO = :RepositoryObserv_IsEKMO,
				@RepositoryObserv_CVIQuestionNotReason = :RepositoryObserv_CVIQuestionNotReason,
				@RepositoryObserv_IsHighTemperature = :RepositoryObserv_IsHighTemperature,
				@RepositoryObserv_TemperatureFrom = :RepositoryObserv_TemperatureFrom,
				@RepositoryObserv_TemperatureTo = :RepositoryObserv_TemperatureTo,
				@RepositoryObserv_TransportDesc = :RepositoryObserv_TransportDesc,
				@RepositoryObserv_TransportPlace = :RepositoryObserv_TransportPlace,
				@RepositoryObserv_TransportRoute = :RepositoryObserv_TransportRoute,
				@RepositoryObserv_IsRunnyNose = :RepositoryObserv_IsRunnyNose,
				@RepositoryObserv_IsSoreThroat = :RepositoryObserv_IsSoreThroat,
				@RepositoryObserv_Pulse = :RepositoryObserv_Pulse,
				@RepositoryObserv_BreathFrequency = :RepositoryObserv_BreathFrequency,
				@RepositoryObserv_BreathVolume = :RepositoryObserv_BreathVolume,
				@RepositoryObserv_BreathPressure = :RepositoryObserv_BreathPressure,
				@RepositoryObserv_BreathPeep = :RepositoryObserv_BreathPeep,
				@RepositoryObserv_IsPronPosition = :RepositoryObserv_IsPronPosition,
				@RepositoryObserv_IsResuscit = :RepositoryObserv_IsResuscit,
				@RepositoryObserv_IsMyoplegia = :RepositoryObserv_IsMyoplegia,
				@RepositoryObserv_Systolic = :RepositoryObserv_Systolic,
				@RepositoryObserv_Diastolic = :RepositoryObserv_Diastolic,
				@RepositoryObserv_GLU = :RepositoryObserv_GLU,
				@RepositoryObserv_Cho = :RepositoryObserv_Cho,
				@Dyspnea_id = :Dyspnea_id,
				@Cough_id = :Cough_id,
				@CovidType_id = :CovidType_id,
				@DiagConfirmType_id = :DiagConfirmType_id,
				@RepositoryObserv_IsSputum = :RepositoryObserv_IsSputum,
				@RepositoryObserv_NumberTMK = :RepositoryObserv_NumberTMK,
				@RepositoryObserv_Other = :RepositoryObserv_Other,
				@RepositoryObserv_PregnancyPeriod = :RepositoryObserv_PregnancyPeriod,
				@GenConditFetus_id = :GenConditFetus_id,
				@RepositoryObserv_Hemoglobin = :RepositoryObserv_Hemoglobin,
				@RepositoryObserv_Leukocytes = :RepositoryObserv_Leukocytes,
				@RepositoryObserv_Lymphocytes = :RepositoryObserv_Lymphocytes,
				@RepositoryObserv_Platelets = :RepositoryObserv_Platelets,
				@RepositoryObserv_SOE = :RepositoryObserv_SOE,
				@RepositoryObserv_SRB = :RepositoryObserv_SRB,
				@RepositoryObserv_PH = :RepositoryObserv_PH,
				@RepositoryObserv_isNeedIVL = :RepositoryObserv_isNeedIVL,
				@RepositoryObserv_isIVL = :RepositoryObserv_isIVL,
				@RepositoryObserv_PaO2 = :RepositoryObserv_PaO2,
				@RepositoryObserv_FiO2 = :RepositoryObserv_FiO2,
				@RepositoryObserv_PaO2FiO2 = :RepositoryObserv_PaO2FiO2,
				@RepositoryObserv_isNeedOxygen = :RepositoryObserv_isNeedOxygen,
				@RepositoryObserv_isOxygen = :RepositoryObserv_isOxygen,
				@RepositoryObserv_SpO2 = :RepositoryObserv_SpO2,
				@StateDynamic_id = :StateDynamic_id,
				@DiagSetPhase_id = :DiagSetPhase_id,
				@RepositoryObserv_IsSedation = :RepositoryObserv_IsSedation,
				@IVLRegim_id = :IVLRegim_id,
				@RepositoryObserv_RegimVenting = :RepositoryObserv_RegimVenting,
				@RepositoryObserv_BreathRate = :RepositoryObserv_BreathRate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@RepositoryObserv_id as \"RepositoryObserv_id\",
				@Error_Code as \"Error_Code\",
				@Error_Message as \"Error_Msg\";
		", $data);
	}
	
	protected function afterSave($data, $response) {
		return $this->checkNeedConsultToRKC($data, $response);
	}

	function checkNeedConsultToRKC($data, $response) {
		$data['CVIConsultRKC_setDT'] = $data['RepositoryObserv_setDate'] . ' ' . $data['RepositoryObserv_setTime'];
		// Если выполняется хотя бы одно из условий:
		if (
			$data['RepositoryObserv_BreathFrequency'] > 30 ||
			$data['RepositoryObserv_SpO2'] <= 93 ||
			$data['RepositoryObserv_Systolic'] < 90 ||
			$data['RepositoryObserv_Diastolic'] < 60 ||
			in_array($data['RepositoryObserv_IVL'], [2, 3]) ||
			in_array($data['RepositoryObserv_Oxygen'], [2, 3]) ||
			(!is_null($data['RepositoryObserv_PaO2FiO2']) && $data['RepositoryObserv_PaO2FiO2'] <= 300)
		) {
			// То выполняется проверка на наличие записей по пациенту в таблице для хранения необходимости консультации в РКЦ (см. п.1.5.7):
			$result = $this->getCVIConsultRKC($data);
			
			if ($data['ignoreRemoteConsultCheck'] != 1) {
				$alertMsg = 'С указанными параметрами о состоянии пациента рекомендуется создать направление на удаленную консультацию в региональный консультационный центр. Создать направление?';
				// Если выполняется хотя бы одно следующее условие, то выводим сообщение о необходимости создания направления
				if (empty($result)) {
					$this->setResponse($alertMsg);
					throw new Exception('', 114);
				} elseif (!empty($result) && is_null($result[0]['EvnDirection_id'])) {
					$this->setResponse($alertMsg, false, $result[0]['CVIConsultRKC_id'], $result[0]['RepositoryObserv_id']);
					throw new Exception('', 114);
				} elseif (!empty($result) && $result[0]['EvnDirection_id']) {
					$curDate = new DateTime();
					$diff = $curDate->diff($result[0]['EvnDirection_setDT']);
					if ($diff->d > 3) {
						$this->setResponse($alertMsg);
						throw new Exception('', 114);
					}
				}
			} else {
				// Создаем необходимось консультации если еще не была создана с данными параметрами
				try {
					if ($data['createConsult']) {
						$this->saveCVIConsultRKC(array_replace($data, $response[0]));
					}
				} catch (Exception $e) {
					return ['success' => false, 'error_msg' => $e->getMessage(), 'error_code' => $e->getCode()];
				}
			}
		} else {
			//Иначе выполняется проверка на наличие записей по пациенту в таблице для хранения необходимости консультации в РКЦ (см. п.1.5.7):
			$result = $this->getCVIConsultRKC($data);
			if ($data['ignoreRemoteConsultCheck'] != 1) {
				if (!empty($result) && is_null($result[0]['EvnDirection_id'])) {
					// то при сохранении:
					$alertMsg = $result[0]['CVIConsultRKC_setDT']->format('Y-m-d H:i') . ' по указанным параметрам о состоянии пациента возникла необходимость создания направления на удаленную консультацию в региональный консультационный центр. Создать направление?';
					$this->setResponse($alertMsg, $result[0]['CVIConsultRKC_id'], $result[0]['RepositoryObserv_id']);
					throw new Exception('', 114);

				}
			}
		}
		
		$this->_saveResponse = $response;
		
		return true;
	}
	
	function setResponse($alertMsg, $createConsult = true, $CVIConsultRKC_id = null,  $RepositoryObserv_sid = null) {
		$this->_saveResponse['ignoreParam'] = 'ignoreRemoteConsultCheck';
		$this->_saveResponse['createConsult'] = $createConsult;
		$this->_saveResponse['CVIConsultRKC_id'] = $CVIConsultRKC_id;
		$this->_saveResponse['RepositoryObserv_sid'] = $RepositoryObserv_sid;
		$this->_saveResponse['Alert_Msg'] = $alertMsg;
	}
	
	function getLastCreatedObserve($data) {
		$query = "
			select top 1 
				RO.RepositoryObserv_setDT,
				RO.RepositoryObserv_id
			from v_RepositoryObserv RO with (nolock)
			outer apply (
					select Evn_pid
					from v_RepositoryObserv RO with (nolock)
					where RO.Evn_id = :Evn_id
				) as EvnPS
			where RO.Evn_pid = EvnPS.Evn_pid
			order by RepositoryObserv_setDT DESC
		";
		
		$result = $this->db->query($query, ['Evn_id' => $data]);

		if (!is_object($result)) {
			return false;
		}
		
		return $result->result('array');
	}
	
	function getLastCreatedConsultRKC($data) {
		$query = "
			select top 1 
				CVI.CVIConsultRKC_id
			from v_CVIConsultRKC CVI with (nolock)
			left join v_RepositoryObserv RO with (nolock) on RO.RepositoryObserv_id = CVI.RepositoryObserv_id
			outer apply (
					select Evn_pid
					from v_RepositoryObserv RO with (nolock)
					where RO.Evn_id = :Evn_id
			) as EvnPS
			where RO.Evn_pid = EvnPS.Evn_pid
			order by CVI.CVIConsultRKC_setDT DESC
		";
		
		return $this->getFirstResultFromQuery($query, ['Evn_id' => $data]);
	}
	
	
	function getCVIConsultRKC ($data) {
		
		$query = "
				select top 1
					CVI.CVIConsultRKC_setDT,
					CVI.CVIConsultRKC_id,
					CVI.EvnDirection_id,
					CVI.RepositoryObserv_id,
					ED.EvnDirection_setDT
				from v_CVIConsultRKC CVI with (nolock)
				left join v_RepositoryObserv RO with (nolock) on RO.RepositoryObserv_id = CVI.RepositoryObserv_id
				left join v_EvnDirection ED with (nolock) on ED.EvnDirection_id = CVI.EvnDirection_id
				outer apply (
					select Evn_pid
					from v_RepositoryObserv RO with (nolock)
					where RO.Evn_id = :Evn_id
				) as EvnPS
				where RO.Evn_pid = EvnPS.Evn_pid
			";

		$result = $this->db->query($query, ['Evn_id' => $data['Evn_id']]);

		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
	
	function saveCVIConsultRKC($data) {
		$params = [
			'EvnDirection_id' => $data['EvnDirection_id'] ?? null,
			'RepositoryObserv_id' => $data['RepositoryObserv_id'] ?? null,
			'RepositoryObserv_sid' => $data['RepositoryObserv_sid'] ?? null,
			'CVIConsultRKC_id' => $data['CVIConsultRKC_id'] ?? null,
			'CVIConsultRKC_setDT' => $data['CVIConsultRKC_setDT'] ?? null,
			'pmUser_id' => $data['pmUser_id'] ?? null,
		];
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@CVIConsultRKC_id bigint = :CVIConsultRKC_id;

			exec dbo.p_CVIConsultRKC_" . (!empty($data['CVIConsultRKC_id']) ? "upd" : "ins") ."
				@CVIConsultRKC_id = @CVIConsultRKC_id output,
				@RepositoryObserv_id = :RepositoryObserv_id,
				@RepositoryObserv_sid = :RepositoryObserv_sid,
				@EvnDirection_id = :EvnDirection_id,
				@CVIConsultRKC_setDT = :CVIConsultRKC_setDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@CVIConsultRKC_id as \"CVIConsultRKC_id\",
				@Error_Code as \"Error_Code\",
				@Error_Message as \"Error_Msg\";
		";
		
		$response = $this->queryResult($query, $params);
		
		if (is_object($response)) {
			return $response->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * @param array $data
	 * @return array|false
	 */
	function findByPerson($data) {
		
		// В БД нет явного признака для отличия анкеты от наблюдения, пока будем смотреть по наличию Места прибытия 
		$RepositoryObserv_id = $this->getFirstResultFromQuery("
			select top 1 ro.RepositoryObserv_id 
			from v_RepositoryObserv ro (nolock)
			inner join v_Evn e (nolock) on e.Evn_id = ro.Evn_id
			where e.Person_id = ? and ro.PlaceArrival_id is not null
			order by RepositoryObserv_setDT desc
		", [$data['Person_id']]);
		
		if ($RepositoryObserv_id === false) {
			return [];
		}
		
		return $this->load(['RepositoryObserv_id' => $RepositoryObserv_id]);
	}
}