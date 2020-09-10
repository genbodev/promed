<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnUslugaDispDop_model - модель для работы с услугами дд
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      11.07.2013
*/

class EvnUslugaDispDop_model extends swPgModel
{
	/**
	 *	Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 *	Сохранение посещения/осмотра/исследования по доп. диспансеризации
	 */
	function saveEvnUslugaDispDop($data) {
		if ( empty($data['EvnUslugaDispDop_setDate']) && empty($data['EvnUslugaDispDop_didDate']) ) {
			return array(array('Error_Msg' => 'Должна быть заполнена хотя бы одна дата'));
		}
		else if ( !empty($data['EvnUslugaDispDop_didDate']) && empty($data['Diag_id']) ) {
			return array(array('Error_Msg' => 'Поле "Диагноз" должно быть заполнено'));
		}

		// После сохранения приема-осмотра врача терапевта с онко диагнозом - открываем специфику - для создания оной
		$openSpecificAfterSave = false;
		// Дублируем didDT в setDT
		// @task https://redmine.swan.perm.ru/issues/104487
		$data['EvnUslugaDispDop_setDate'] = $data['EvnUslugaDispDop_didDate'];
		$data['EvnUslugaDispDop_setTime'] = $data['EvnUslugaDispDop_didTime'];

		if ($data['session']['region']['nick'] == 'ufa') {
			// Проверка разрешения оплаты по ОМС для отделения
			$this->load->model('LpuStructure_model', 'lsmodel');
			$response = $this->lsmodel->getLpuUnitIsOMS(array(
				'LpuSection_id' => $data['LpuSection_id']
			));
			if (!$response[0]['LpuUnit_IsOMS']) {
				return array(array('Error_Msg' => 'Данное отделение не работает по ОМС'));
			}
		}

		$this->db->trans_begin();

		$SurveyType_Code = '';
		$DispClass_id = '';
		
		$SurveyTypeData = $this->getSurveyTypeData($data['DopDispInfoConsent_id']);
		if ($SurveyTypeData !== false) {
			$SurveyType_Code = $SurveyTypeData['SurveyType_Code'];
			$DispClass_id = $SurveyTypeData['DispClass_id'];
		}
		
		if ($DispClass_id == 5 && $SurveyType_Code == 19 && !empty($SurveyTypeData['EvnPLDispProf_IsEndStage']) && $SurveyTypeData['EvnPLDispProf_IsEndStage'] == 2 && empty($data['EvnUslugaDispDop_didDate'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Дата выполнения осмотра врача терапевта обязательна для заполнения'));
		}

		if ($DispClass_id == 10 && $SurveyType_Code == 27 && !empty($SurveyTypeData['EvnPLDisp_setDate']) && !empty($data['EvnUslugaDispDop_didDate']) && strtotime($SurveyTypeData['EvnPLDisp_setDate']) > strtotime($data['EvnUslugaDispDop_didDate'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Дата осмотра врача-терапевта (ВОП) не может быть меньше даты начала диспансеризации'));
		}

		if (is_array($SurveyTypeData) && !empty($SurveyTypeData['SurveyType_id']) && !empty($data['DopDispInfoConsent_id'])) {
			$query = "
				select 
					count(EUDD.EvnUslugaDispDop_id) as \"cnt\"
				from
					v_DopDispInfoConsent DDIC 
					inner join v_SurveyTypeLink STL  on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST  on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaDispDop EUDD  on EUDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
				where
					DDIC.EvnPLDisp_id = :EvnPLDisp_id
					and ST.SurveyType_id = :SurveyType_id
					and DDIC.DopDispInfoConsent_id <> :DopDispInfoConsent_id
					and COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				limit 1
			";
			$params = array(
				'SurveyType_id' => $SurveyTypeData['SurveyType_id'],
				'EvnPLDisp_id' => $data['EvnVizitDispDop_pid'],
				'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
			);
			$count = $this->getFirstResultFromQuery($query, $params);
			if ($count === false) {
				return array(array('Error_Msg' => 'Ошибка при проверке существования осмотра/исследования'));
			}
			if ($count > 0) {
				return array(array('Error_Msg' => "Уже существует осмотр/исследование \"{$SurveyTypeData['SurveyType_Name']}\""));
			}
		}

		/*
		[
				{"DopDispInfoConsent_id":1645204,"SurveyTypeLink_id":85,   "SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":48, "SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":"hidden", "DopDispInfoConsent_IsAgree":1, 	"DopDispInfoConsent_IsImpossible":"hidden"},
				{"DopDispInfoConsent_id":-24689, "SurveyTypeLink_id":24689,"SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":19, "SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":true,     "DopDispInfoConsent_IsAgree":false,	"DopDispInfoConsent_IsImpossible":"hidden"},
				{"DopDispInfoConsent_id":1645206,"SurveyTypeLink_id":23760,"SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":38, "SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":0,	    "DopDispInfoConsent_IsAgree":1,		"DopDispInfoConsent_IsImpossible":"hidden"},
				{"DopDispInfoConsent_id":1645207,"SurveyTypeLink_id":23790,"SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":47, "SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":0,	    "DopDispInfoConsent_IsAgree":1,		"DopDispInfoConsent_IsImpossible":"hidden"},
				{"DopDispInfoConsent_id":1645208,"SurveyTypeLink_id":23788,"SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":101,"SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":0,	    "DopDispInfoConsent_IsAgree":1,		"DopDispInfoConsent_IsImpossible":"hidden"},
				{"DopDispInfoConsent_id":1645209,"SurveyTypeLink_id":23753,"SurveyTypeLink_IsNeedUsluga":1,"SurveyType_Code":155,"SurveyTypeLink_IsDel":1, "SurveyTypeLink_IsUslPack":"", "DopDispInfoConsent_IsAgeCorrect":1, "DopDispInfoConsent_IsEarlier":0,		"DopDispInfoConsent_IsAgree":1,		"DopDispInfoConsent_IsImpossible":"hidden"}
		]
		*/

		if (in_array($SurveyTypeData['DispClass_id'], array(19,26))) {
			$query = "
				select 
					null as \"IsEarlier\",
					EPLD.EvnPLDisp_setDT as \"EvnPLDisp_setDT\",
					EPLD.EvnPLDisp_consDT as \"EvnPLDisp_consDT\",
					null as \"EvnPLDisp_IsNewOrder\"
				from v_DopDispInfoConsent DDIC 
				inner join v_EvnPLDisp EPLD  on EPLD.EvnPLDisp_id = DDIC.EvnPLDisp_id
				where DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
                limit 1
			";
		}
		else {
			$query = "
				select
					IsEarlier.YesNo_Code as \"IsEarlier\",
					EPLD.EvnPLDisp_setDT as \"EvnPLDisp_setDT\",
					EPLD.EvnPLDisp_consDT as \"EvnPLDisp_consDT\",
					EPLD.EvnPLDisp_IsNewOrder as \"EvnPLDisp_IsNewOrder\"
				from 
					v_DopDispInfoConsent DDIC 
					left join v_YesNo IsEarlier  on IsEarlier.YesNo_id = DDIC.DopDispInfoConsent_IsEarlier
					inner join v_EvnPLDisp EPLD  on EPLD.EvnPLDisp_id = DDIC.EvnPLDisp_id
				where 
					DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
				limit 1
			";
		}
		$resp = $this->getFirstRowFromQuery($query, $data);
		if (!$resp) {
			return array(array('Error_Msg' => 'Ошибка при проверке даты выполнения услуги'));
		}


		$EvnPLDisp_consDT = date_create($resp['EvnPLDisp_consDT']);
		$EvnPLDisp_IsNewOrder = $resp['EvnPLDisp_IsNewOrder'];

		$IsEarlier = null;
		if(isset($resp['IsEarlier'])){
			$IsEarlier = $resp['IsEarlier'];
		}


		$check_date = date_create($data['EvnUslugaDispDop_didDate']);



		// $DispClass_id - вид диспансеризации
		// 1 - Дисп-ция взр. населения 1-ый этап
		// 2 - Дисп-ция взр. населения 2-ой этап
		// 5 - Проф.осмотры взр. населения
		// 6 - Периодические осмотры несовершеннолетних
		// 9 - Предварительные осмотры несовершеннолетних 1-ый этап
		// 10 - Профилактические осмотры несовершеннолетних 1-ый этап
		// 19 - Медицинское освидетельствование мигрантов
		// 26 - Медицинское освидетельствование водителей на право управления ТС категории A и B
		if (in_array($DispClass_id, array(1,2,5))) {
			if ( ! $resp['IsEarlier'] && $check_date < $EvnPLDisp_consDT) {
				return array(array('Error_Msg' => 'Дата выполнения осмотра / исследования не должна быть раньше даты подписания Информированного согласия'));
			}
		} else if (in_array($DispClass_id, array(6,9,10))) {
			if ( ! $resp['IsEarlier'] && $check_date < $resp['EvnPLDisp_setDT']) {
				return array(array('Error_Msg' => 'Дата выполнения осмотра / исследования не должна быть раньше даты начала медицинского осмотра'));
			}
		}



		if (getRegionNick() == 'perm' && !empty($data['EvnUslugaDispDop_didDate']) && !empty($data['ExaminationPlace_id']) && $data['ExaminationPlace_id'] != 3) {
			// проверяем что рабочее место врача на дату выполнения услуги открыто.
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact 
				where
					MedStaffFact_id = :MedStaffFact_id
					and LpuSection_id = :LpuSection_id
					and MedPersonal_id = :MedPersonal_id
					and WorkData_begDate <= :EvnUslugaDispDop_didDate
					and (WorkData_endDate >= :EvnUslugaDispDop_didDate OR WorkData_endDate IS NULL)
				limit 1
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'EvnUslugaDispDop_didDate' => $data['EvnUslugaDispDop_didDate']
			));

			if (empty($MedStaffFact_id)) {
				return array(array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги'));
			}
		}


        //Проверка на наличие у врача кода ДЛО и специальности https://redmine.swan.perm.ru/issues/47172
        if (($data['session']['region']['nick'] === 'kareliya') && isset($data['MedPersonal_id'])){

			$dlo_check_filter = '(1=1)';

			if (!empty($data['MedStaffFact_id'])){
				$dlo_check_filter .= ' and MedStaffFact_id = :MedStaffFact_id';
			} else {
				if (!empty($data['MedPersonal_id'])) {
					$dlo_check_filter .= ' and MedPersonal_id = :MedPersonal_id';
				}
				
				if (!empty($data['LpuSection_id'])){
					$dlo_check_filter .= ' and LpuSection_id = :LpuSection_id';
				}
			}

			$queryCheckMedPersonal = "
				select
					COALESCE(MSF.MedPersonal_Code,'') as \"MedPersonal_DloCode\",
					COALESCE(MSF.MedSpecOms_id,0) as \"MedSpecOms_id\",
					COALESCE(MSF.Person_Snils,'') as \"Person_Snils\"
				from
					v_MedStaffFact MSF 
				where
					{$dlo_check_filter}
			";

			//echo getDebugSQL($queryCheckMedPersonal, $data);die;
			$res_MP = $this->db->query($queryCheckMedPersonal, $data);
			if(is_object($res_MP)){
				$result_MP = $res_MP->result('array');
				if(is_array($result_MP)&&count($result_MP)>0){
					if($result_MP[0]['Person_Snils']==''){
						return array(array('Error_Msg' => 'У врача не указан СНИЛС'));
					}
					if(($result_MP[0]['MedSpecOms_id']=='')||($result_MP[0]['MedSpecOms_id']==0)){
						return array(array('Error_Msg' => 'У врача не указана специальность'));
					}
				}
				else{
					return array(array('Error_Msg' => 'У врача не указан СНИЛС или специальность'));
				}
			}
        }
		// проверка на обязательность отделения и врача в случае, если указана "Своя МО". #34624
		$beforeSaveEvnUslugaDispDop = $this->beforeSaveEvnUslugaDispDop($data);

		if ( $beforeSaveEvnUslugaDispDop !== true ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => $beforeSaveEvnUslugaDispDop));
		}

		$this->load->model('EvnUsluga_model', 'eumodel');
		$params = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			//'EvnUsluga_pid' => '',
			'EvnUsluga_setDate' => $data['EvnUslugaDispDop_didDate'],
			'EvnUsluga_setTime' => $data['EvnUslugaDispDop_didTime'],
			'EvnUsluga_disDate' => $data['EvnUslugaDispDop_disDate'],
			'EvnUsluga_disTime' => $data['EvnUslugaDispDop_disTime']
		);
		$checkDate = $this->eumodel->CheckEvnUslugaDate($params);
		if ( !$this->isSuccessful($checkDate) ) {
			return $checkDate;
		}


		// Проверяем сохранён ли осмотр «Индивидуальное углублённое профилактическое консультирование или групповое профилактическое консультирование»
		$isExistSurveyTypeCode47 = (bool) $this->getFirstResultFromQuery("
			select 
				 st.SurveyType_id as \"SurveyType_id\"
			from 
				v_EvnVizitDispDop evdd 
				inner join v_EvnUslugaDispDop eudd  on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic  on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl  on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st  on st.SurveyType_id = stl.SurveyType_id
			where 
				evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
				and st.SurveyType_Code = 47
			limit 1
		", $data);


		// $SurveyType_Code - код типа исследования
		// 19 - Прием (осмотр) врача-терапевта
		// 27 - Прием (осмотр) врача - педиатра
		// 47 - Индивидуальное углубленное профилактическое консультирование или групповое профилактическое консультирование
		if ( in_array($SurveyType_Code, array(19, 27)) ) {
			$sql = "
				select 
					 evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
					,eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
					,stl.DispClass_id as \"DispClass_id\"
				from 
					v_EvnUslugaDispDop eudd 
					inner join v_EvnVizitDispDop evdd  on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent ddic  on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl  on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st  on st.SurveyType_id = stl.SurveyType_id
				where 
					evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
					and st.SurveyType_Code NOT IN (19,27)
					and cast(eudd.EvnUslugaDispDop_didDT as date) > :EvnUslugaDispDop_didDate
                limit 1
			";
			$res = $this->db->query($sql, $data);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( is_array($resp) && count($resp) > 0 ) {
					$this->db->trans_rollback();
					if ($SurveyType_Code == 19) {

						$is_error = true;
						// Регион: Свердловская область
							// Если выполняются условия:
								// В Информированном добровольном согласии осмотр «Приём (осмотр) врача терапевта» отмечен, как пройденный ранее;
								// Сохранён осмотр «Индивидуальное углублённое профилактическое консультирование или групповое профилактическое консультирование»,
							// то при сохранении формы приёма (осмотра) контроль «Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП)» не выполняется.
						if($data['session']['region']['nick'] == 'ekb'){
							if(isset($IsEarlier) && $IsEarlier && $isExistSurveyTypeCode47){
								$is_error = false;
							}
						}

						if($is_error){
							return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП).'));
						}

					} else {
						return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-педиатра (ВОП).'));
					}
				}
			}
		} else {
			$sql = "
				select 
					 evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
					,eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
					,stl.DispClass_id as \"DispClass_id\"
					,st.SurveyType_Code as \"SurveyType_Code\"
				from v_EvnUslugaDispDop eudd 
					inner join v_EvnVizitDispDop evdd  on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent ddic  on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl  on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st  on st.SurveyType_id = stl.SurveyType_id
				where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
					and st.SurveyType_Code IN (19,27)
					and cast(eudd.EvnUslugaDispDop_didDT as date) < :EvnUslugaDispDop_didDate
				limit 1
			";
			$res = $this->db->query($sql, $data);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( is_array($resp) && count($resp) > 0 ) {
					$this->db->trans_rollback();
					if ($resp[0]['SurveyType_Code'] == 19) {
						return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП).'));
					} else {
						return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-педиатра (ВОП).'));
					}
				}
			}
		}

		// Надо проверить что сохраняемая услуга соответствует списку возможных, чтобы пользователи никак не могли сохранить левую услугу.
		// При необходимости можно будет добавить и фильтры (по возрасту, дате и пр.), но пока пусть так, а то терапевту как то сохраняют аж услугу акушера (refs #58948)
		$sql = "
			SELECT 
				stl2.SurveyTypeLink_id as \"SurveyTypeLink_id\"
			FROM
				v_DopDispInfoConsent ddic 
				INNER JOIN v_SurveyTypeLink stl  ON stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				INNER JOIN v_SurveyTypeLink stl2  ON stl2.SurveyType_id = stl.SurveyType_id
			WHERE
				DopDispInfoConsent_id = :DopDispInfoConsent_id
				and stl2.UslugaComplex_id = :UslugaComplex_id
            LIMIT 1
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка возможных услуг)'));
		}

		$resp = $res->result('array');
		if (empty($resp[0]['SurveyTypeLink_id'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Указана некорректная услуга для осмотра/исследования'));
		}
		
		// Получаем EvnVizitDispDop_id по DopDispInfoConsent_id
		$sql = "
			select  
				dd.Diag_id as \"Diag_id\",
				d.Diag_Code as \"Diag_Code\",
				dd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
			from v_EvnVizitDispDop dd 
				LEFT JOIN v_Diag d  ON d.Diag_id = dd.Diag_id
			where dd.DopDispInfoConsent_id = :DopDispInfoConsent_id
			limit 1
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора посещения)'));
		}

		$resp = $res->result('array');
		$this->load->model('EvnVizitDispDop_model', 'EvnVizitDispDop_model');
		if ( is_array($resp) && count($resp) > 0 ) {
			$data['EvnVizitDispDop_id'] = $resp[0]['EvnVizitDispDop_id'];
			$data['saved_Diag_id'] = $resp[0]['Diag_id'];
			$data['saved_Diag_Code'] = $resp[0]['Diag_Code'];
			$procvizit = "p_EvnVizitDispDop_upd";


			// Проверка на существование специфики по онко-диагнозу
			// нужна только для приема-осмотра врача терапевта
			if ($SurveyType_Code == 19 && empty($data['ignoreCheckMorbusOnko'])) {
				$changeDiag = $this->EvnVizitDispDop_model->checkChangeDiag($data);
				if (is_array($changeDiag)) {
					$this->db->trans_rollback();
					return $changeDiag;
				}
				/*$checkSpec = $this->EvnVizitDispDop_model->checkOnkoSpecifics($data);
				if (is_array($checkSpec)) {
					$this->db->trans_rollback();
					return $checkSpec;
				}*/
			}
			// Если сменили диагноз (старый отличается от нового), а также новый - онкологический, значит открываем специфику
			// Но не мешаем сохранять, чтобы данные в специфику подтянулись новые
			if($SurveyType_Code == 19 && !empty($data['isOnkoDiag']) && ($data['saved_Diag_id'] !== $data['Diag_id']))
				$openSpecificAfterSave = true;

			// Получаем EvnUslugaDispDop_id по EvnVizitDispDop_id
			$sql = "
				select eu.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\", eddd.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
				from v_EvnUslugaDispDop eu 
					left join v_EvnDiagDopDisp eddd  on eu.EvnUslugaDispDop_id = eu.EvnUslugaDispDop_id and eddd.Diag_id = eu.Diag_id
				where eu.EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
				limit 1
			";
			$res = $this->db->query($sql, array('EvnUslugaDispDop_pid' => $data['EvnVizitDispDop_id']));

			if ( !is_object($res) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)'));
			}

			$resp = $res->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Дождитесь выполнения предыдущего запроса на сохранение осмотра/исследования'));
			}

			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
			
			//сменили диагноз - удалим и из EvnDiagDopDisp, ниже новый диагноз все равно добавим через addEvnDiagDopDispBefore
			if($data['saved_Diag_id'] !== $data['Diag_id'] && !empty($resp[0]['EvnDiagDopDisp_id'])) {
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
					from p_EvnDiagDopDisp_del(
						EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
						pmUser_id := :pmUser_id);
				";
				$this->db->query($query, array(
					'EvnDiagDopDisp_id' => $resp[0]['EvnDiagDopDisp_id'],
					'pmUser_id' => $data['pmUser_id'])
				);
			}
			$proc = "p_EvnUslugaDispDop_upd";
		}
		else {
			if($SurveyType_Code == 19 && !empty($data['isOnkoDiag']))
				$openSpecificAfterSave = true;

			$data['EvnUslugaDispDop_id'] = null;
			$data['EvnVizitDispDop_id'] = null;

			$proc = "p_EvnUslugaDispDop_ins";
			$procvizit = "p_EvnVizitDispDop_ins";
		}

		if ( !empty($data['EvnUslugaDispDop_setTime']) ) {
			$data['EvnUslugaDispDop_setDate'] .= ' ' . $data['EvnUslugaDispDop_setTime'] . ':00.000';
		}
		
		$data['EvnVizitDispDop_setDate'] = $data['EvnUslugaDispDop_setDate'];
		if (!empty($data['EvnVizitDispDop_didDate'])) {
			$data['EvnVizitDispDop_setDate'] = $data['EvnVizitDispDop_didDate'];
		}

		if ( !empty($data['EvnUslugaDispDop_didTime']) ) {
			$data['EvnUslugaDispDop_didDate'] .= ' ' . $data['EvnUslugaDispDop_didTime'] . ':00.000';
		}

		if ( !empty($data['EvnUslugaDispDop_disDate']) && !empty($data['EvnUslugaDispDop_disTime']) ) {
			$data['EvnUslugaDispDop_disDate'] .= ' ' . $data['EvnUslugaDispDop_disTime'] . ':00.000';
		}
		if ( empty($data['EvnUslugaDispDop_disDate']) ) {
			$data['EvnUslugaDispDop_disDate'] = $data['EvnUslugaDispDop_didDate'];
		}
		if(!in_array($data['session']['region']['nick'], array('perm','buryatiya','kareliya','penza')))
		{
			$data['DeseaseStage'] = null;
		}

		// сначала сохраняем посещение, затем в него услугу, затем к ней сохраняем её результаты %)
		$sql = "
			WITH cte AS (                
					select 
                    	CASE WHEN :EvnVizitDispDop_id is not null
							and :Diag_id is not null
							and not exists (select Diag_id from v_Diag  where Diag_id = :Diag_id and left(Diag_Code, 1) = 'Z')
                        THEN
							DispSurveilType_id ELSE null END as DST_id,
                    	CASE WHEN :EvnVizitDispDop_id is not null
							and :Diag_id is not null
							and not exists (select Diag_id from v_Diag  where Diag_id = :Diag_id and left(Diag_Code, 1) = 'Z')
                        THEN
							EvnVizitDispDop_IsFirstTime ELSE null END  as IsFirstTime,
                    	CASE WHEN :EvnVizitDispDop_id is not null
							and :Diag_id is not null
							and not exists (select Diag_id from v_Diag  where Diag_id = :Diag_id and left(Diag_Code, 1) = 'Z')
                        THEN
							EvnVizitDispDop_IsVMP ELSE null END  as IsVMP
					from v_EvnVizitDispDop 
					where EvnVizitDispDop_id = :EvnVizitDispDop_id
                    limit 1
			)

			select EvnVizitDispDop_id as \"EvnVizitDispDop_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from " . $procvizit . "(
				EvnVizitDispDop_id := :EvnVizitDispDop_id,
				EvnVizitDispDop_pid := :EvnVizitDispDop_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnVizitDispDop_setDT := :EvnVizitDispDop_setDate,
				EvnVizitDispDop_didDT := :EvnUslugaDispDop_didDate,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				EvnVizitDispDop_DeseaseStage := :DeseaseStage,
				LpuSection_id := :LpuSection_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				EvnVizitDispDop_IsFirstTime := (SELECT IsFirstTime FROM cte),
				EvnVizitDispDop_IsVMP := (SELECT IsVMP FROM cte),
				DispSurveilType_id := (SELECT DST_id FROM cte),
				ElectronicService_id := :ElectronicService_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id);
		";
		// echo getDebugSQL($sql, $data);
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении посещения'));
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $resp;
		}

		$data['EvnVizitDispDop_id'] = $resp[0]['EvnVizitDispDop_id'];
		if($SurveyType_Code == 19)
			$this->EvnVizitDispDop_model->onAfterSaveEvn($data, $SurveyType_Code);

		// очищаем поля которые не долнжы сохраняться при выбранном месте оказания "Своя МО".
		if (!empty($data['ExaminationPlace_id']) && $data['ExaminationPlace_id'] != 3) {
			$data['Lpu_uid'] = null;
			$data['LpuSectionProfile_id'] = null;
			$data['MedSpecOms_id'] = null;
		}

		$sql = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
				EvnUslugaDispDop_pid := :EvnVizitDispDop_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				EvnDirection_id := :EvnDirection_id,
				PersonEvn_id := :PersonEvn_id,
				PayType_id := (select PayType_id from v_PayType  where PayType_SysNick = 'dopdisp' limit 1),
				EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDate,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaDispDop_didDT := :EvnUslugaDispDop_didDate,
				EvnUslugaDispDop_disDT := :EvnUslugaDispDop_disDate,
				Lpu_uid := :Lpu_uid,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MedSpecOms_id := :MedSpecOms_id,
				ExaminationPlace_id := :ExaminationPlace_id,
				LpuSection_uid := :LpuSection_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnUslugaDispDop_ExamPlace := :EvnUslugaDispDop_ExamPlace,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				EvnUslugaDispDop_Result := :EvnUslugaDispDop_Result,
				Diag_id := :Diag_id,
				pmUser_id := :pmUser_id);
		";
		// echo getDebugSQL($sql, $data);
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $resp;
		}

		$EvnUsluga_id = $resp[0]['EvnUslugaDispDop_id'];

		// сохраняем сопутствующие диагнозы
		if (!empty($data['EvnDiagDopDispGridData'])) {
			$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
		} else {
			$data['EvnDiagDopDispGridData'] = array();
		}
		foreach($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
			if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
					from p_EvnDiagDopDisp_del(
						EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
						pmUser_id := :pmUser_id);
				";
				$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'pmUser_id' => $data['pmUser_id'])
				);
				if (!is_object($result_eddd))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление сопутствующего диагноза)'));
				}
				$resp_eddd = $result_eddd->result('array');
				if (!is_array($resp_eddd) || count($resp_eddd) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении сопутствующего диагноза'));
				}
				else if (strlen($resp_eddd[0]['Error_Msg']) > 0)
				{
					return $resp_eddd;
				}
			} else {
				if ($EvnDiagDopDisp['Record_Status'] == 0)
				{
					$proc_evdd = 'p_EvnDiagDopDisp_ins';
				}
				else
				{
					$proc_evdd = 'p_EvnDiagDopDisp_upd';
				}

				// проверяем, есть ли уже такой диагноз
				$query = "
					select
						count(*) as cnt
					from
						v_EvnDiagDopDisp 
					where
						EvnDiagDopDisp_pid = ?
						and Diag_id = ?
						and DiagSetClass_id = 3
						and ( EvnDiagDopDisp_id <> COALESCE(CAST(? as bigint), 0) )
				";
				$result_eddd = $this->db->query(
					$query,
					array(
						$EvnUsluga_id,
						$EvnDiagDopDisp['Diag_id'],
						$EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
					)
				);
				if (!is_object($result_eddd))
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
				}
				$resp_eddd = $result_eddd->result('array');
				if (!is_array($resp_eddd) || count($resp_eddd) == 0)
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
				}
				else if ($resp_eddd[0]['cnt'] >= 1)
				{
					return array(array('Error_Msg' => 'Обнаружено дублирование сопутствующих диагнозов, это недопустимо.'));
				}

				$query = "
					select EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                    
					from {$proc_evdd}(
						EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
						EvnDiagDopDisp_setDT := dbo.tzGetDate(),
						EvnDiagDopDisp_pid := :EvnDiagDopDisp_pid,
						Diag_id := :Diag_id,
						DiagSetClass_id := :DiagSetClass_id,
						DeseaseDispType_id := :DeseaseDispType_id,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						pmUser_id := :pmUser_id);
				";
				$result_eddd = $this->db->query($query, array(
					'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
					'EvnDiagDopDisp_pid' => $EvnUsluga_id,
					'Diag_id' => $EvnDiagDopDisp['Diag_id'],
					'DiagSetClass_id' => 3,
					'DeseaseDispType_id' => !empty($EvnDiagDopDisp['DeseaseDispType_id'])?$EvnDiagDopDisp['DeseaseDispType_id']:null,
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!is_object($result_eddd))
				{
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
				}
				$resp_eddd = $result_eddd->result('array');
				if (!is_array($resp_eddd) || count($resp_eddd) == 0)
				{
					return false;
				}
				else if ($resp_eddd[0]['Error_Msg'])
				{
					return $resp_eddd;
				}
			}
		}

		$onDate = $EvnPLDisp_consDT->format('Y-m-d');
		if (!empty($EvnPLDisp_consDT) && !empty($EvnPLDisp_IsNewOrder) && $EvnPLDisp_IsNewOrder == 2) {
			$onDate = $EvnPLDisp_consDT->format('Y') . '-12-31';
		}

		// https://redmine.swan-it.ru/issues/55641
		// https://redmine.swan-it.ru/issues/56445
		// https://redmine.swan-it.ru/issues/174147
		if (
			(
				$DispClass_id == 1
				|| (in_array(getRegionNick(), ['ekb']) && $DispClass_id == 5 && $onDate >= '2019-05-01')
			)
			&& $SurveyType_Code == 20
			&& (
				in_array(getRegionNick(), ['ekb'])
				|| (
					in_array(getRegionNick(), ['astra'])
					&& $onDate < '2018-01-01'
				)
				|| (
					in_array(getRegionNick(), ['perm'])
					&& $onDate < '2019-06-01'
				)
			)
		) {
			// ищем сохранена ли уже услуга
			$CytoEvnUslugaDispDop_id = null;
			$resp_cyto = $this->queryResult("
				select 
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				from
					v_EvnUslugaDispDop 
				where
					EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
				limit 1
			", array(
				'EvnUslugaDispDop_pid' => $EvnUsluga_id
			));
			if (!empty($resp_cyto[0]['EvnUslugaDispDop_id'])) {
				$CytoEvnUslugaDispDop_id = $resp_cyto[0]['EvnUslugaDispDop_id'];
			}

			if ($data['Cyto_IsNotAgree']) {
				$data['DopDispInfoConsent_IsAgree'] = 1;
				$data['DopDispInfoConsent_IsEarlier'] = 1;
				$data['DopDispInfoConsent_IsImpossible'] = 1;
			} else {
				// иначе берём с согласия осмотра фельдшера
				$resp_feldsh = $this->queryResult("
					select 
						ddic.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						ddic.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
						ddic.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
						ddic.DopDispInfoConsent_IsImpossible as \"DopDispInfoConsent_IsImpossible\"
					from
						v_DopDispInfoConsent ddic 
						inner join v_SurveyTypeLink stl  on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where
						ddic.EvnPLDisp_id = :EvnPLDisp_id
						and COALESCE(stl.SurveyTypeLink_ComplexSurvey, 1) = 1
					limit 1
				", array(
					'EvnPLDisp_id' => $data['EvnVizitDispDop_pid']
				));

				if (!empty($resp_feldsh[0]['DopDispInfoConsent_id'])) {
					$data['DopDispInfoConsent_IsAgree'] = $resp_feldsh[0]['DopDispInfoConsent_IsAgree'];
					$data['DopDispInfoConsent_IsEarlier'] = $resp_feldsh[0]['DopDispInfoConsent_IsEarlier'];
					$data['DopDispInfoConsent_IsImpossible'] = $resp_feldsh[0]['DopDispInfoConsent_IsImpossible'];
				} else {
					return array('Error_Msg' => 'Ошибка получения согласия для осмотра фельдшера');
				}
			}

			$this->load->model('EvnPLDispDop13_model');
			$respCytoSave = $this->EvnPLDispDop13_model->saveCytoDopDispInfoConsent(array(
				'EvnPLDisp_id' => $data['EvnVizitDispDop_pid'],
				'DispClass_id' => $DispClass_id,
				'DopDispInfoConsent_IsAgree' => $data['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $data['DopDispInfoConsent_IsEarlier'],
				'DopDispInfoConsent_IsImpossible' => $data['DopDispInfoConsent_IsImpossible'],
				'CytoUslugaComplex_id' => $data['CytoUslugaComplex_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($respCytoSave[0]['Error_Msg'])) {
				return $respCytoSave;
			}

			if ($data['Cyto_IsNotAgree']) {
				// если отказ
				// удаляем услугу цитологического исследования
				if (!empty($CytoEvnUslugaDispDop_id)) {
					$query = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                            
						from p_EvnUslugaDispDop_del(
							EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
							pmUser_id := :pmUser_id);
					";

					$saveResp = $this->queryResult($query, array(
						'EvnUslugaDispDop_id' => $CytoEvnUslugaDispDop_id,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($saveResp[0]['Error_Msg'])) {
						return $saveResp;
					}
				}
			} else {
				// если согласен
				// сохраняем услугу цитологического исследования
				$cytoproc = "p_EvnUslugaDispDop_ins";
				if (!empty($CytoEvnUslugaDispDop_id)) {
					$cytoproc = "p_EvnUslugaDispDop_upd";
				}

				$query = "
					select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$cytoproc}(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						EvnDirection_id := :EvnDirection_id,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := (select PayType_id from v_PayType  where PayType_SysNick = 'dopdisp' limit 1),
						EvnUslugaDispDop_setDT := :EvnUsluga_setDate,
						UslugaComplex_id := :UslugaComplex_id,
						Diag_id := :Diag_id,
						EvnUslugaDispDop_didDT := :EvnUsluga_didDate,
						ExaminationPlace_id := :ExaminationPlace_id,
						Lpu_uid := :Lpu_uid,
						LpuSectionProfile_id := :LpuSectionProfile_id,
						LpuSection_uid := :LpuSection_id,
						MedSpecOms_id := :MedSpecOms_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						EvnPrescrTimetable_id := null,
						EvnPrescr_id := null,
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						pmUser_id := :pmUser_id);
				";

				if ($data['CytoExaminationPlace_id'] == 3) {
					if (
						empty($data['CytoUslugaComplex_id'])
						|| empty($data['CytoEvnUsluga_setDate'])
						|| empty($data['CytoLpu_id'])
						|| (
							$this->getRegionNick() == 'perm'
							&& (
								empty($data['CytoLpuSectionProfile_id'])
								|| empty($data['CytoMedSpecOms_id'])
							)
						)
					) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Не указаны все данные по цитологическому исследованию'));
					}
				} else {
					if (empty($data['CytoUslugaComplex_id']) || empty($data['CytoEvnUsluga_setDate']) || empty($data['CytoLpuSection_id']) || empty($data['CytoMedPersonal_id'])) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Не указаны все данные по цитологическому исследованию'));
					}
				}

				$params = $data;
				$params['EvnUslugaDispDop_id'] = $CytoEvnUslugaDispDop_id;
				$params['EvnUslugaDispDop_pid'] = $EvnUsluga_id;
				$params['EvnUsluga_setDate'] = $data['CytoEvnUsluga_setDate'];
				$params['EvnUsluga_didDate'] = $data['CytoEvnUsluga_setDate'];
				$params['UslugaComplex_id'] = $data['CytoUslugaComplex_id'];
				$params['Diag_id'] = $data['Diag_id'];
				$params['ExaminationPlace_id'] = !empty($data['CytoExaminationPlace_id']) ? $data['CytoExaminationPlace_id'] : null;
				$params['Lpu_uid'] = !empty($data['CytoLpu_id']) ? $data['CytoLpu_id'] : $data['Lpu_uid'];
				$params['LpuSectionProfile_id'] = !empty($data['CytoLpuSectionProfile_id']) ? $data['CytoLpuSectionProfile_id'] : null;
				$params['LpuSection_id'] = !empty($data['CytoLpuSection_id']) ? $data['CytoLpuSection_id'] : null;
				$params['MedSpecOms_id'] = !empty($data['CytoMedSpecOms_id']) ? $data['CytoMedSpecOms_id'] : null;
				$params['MedPersonal_id'] = !empty($data['CytoMedPersonal_id']) ? $data['CytoMedPersonal_id'] : null;
				$params['MedStaffFact_id'] = !empty($data['CytoMedStaffFact_id']) ? $data['CytoMedStaffFact_id'] : null;
				$params['DopDispInfoConsent_id'] = !empty($respCytoSave[0]['DopDispInfoConsent_id']) ? $respCytoSave[0]['DopDispInfoConsent_id'] : null;

				// очищаем поля которые не долнжы сохраняться при выбранном месте оказания "Своя МО".
				if (!empty($params['ExaminationPlace_id']) && $params['ExaminationPlace_id'] != 3) {
					$params['Lpu_uid'] = null;
					$params['LpuSectionProfile_id'] = null;
					$params['MedSpecOms_id'] = null;
				}

				$saveResp = $this->queryResult($query, $params);
				if (!empty($saveResp[0]['Error_Msg'])) {
					return $saveResp;
				}
			}
		}

		$inresults = array();

		if ( !in_array($DispClass_id, array(6, 9, 10, 11, 12)) ) {
			switch ( $SurveyType_Code ) {
				case 3:
					$inresults = array('systolic_blood_pressure','diastolic_blood_pressure');
				break;

				case 4:
					$inresults = array('person_weight','person_height','waist_circumference','body_mass_index');
				break;

				case 5:
					$inresults = array('total_cholesterol');
				break;

				case 6:
					$inresults = array('glucose');
				break;

				case 8:
					$inresults = array('eye_pressure_right','eye_pressure_left'/*, 'eye_pressure_increase'*/);
				break;

				case 9:
				case 10:
					$inresults = array('number_erythrocytes','cln_blood_gem','hematocrit','distribution_width_erythrocytes','volume_erythrocyte','hemoglobin_content','concentration_hemoglobin','cln_blood_trom','cln_blood_leyck','lymphocyte_content','contents_mixture_monocit','contents_mixture_eozinofil','contents_mixture_bazofil','contents_mixture_nezrelklet','granulocytes','number_monocytes','erythrocyte_sedimentation_rate');
				break;

				case 11:
					$inresults = array('amount_urine_s','specific_weight_s','cln_urine_protein_s','cln_urine_sugar_s','urine_acetone_s','urine_bili_s','urine_urobili_s','cln_urine_erit_s','cln_urine_leyck_s','urine_hyal_cylin_s','urine_gran_cylin_s','urine_waxy_cylin_s','urine_epit_s','urine_epit_kidney_s','urine_epit_flat_s','urine_mucus_s','urine_salt_s','urine_bact_s','color','ph','odour','density','transparent');
				break;

				case 12:
					$inresults = array('glucose','cln_urine_protein','albumin','bio_blood_kreatinin','bio_blood_bili','AsAt','AlAt','fibrinogen','potassium','sodium','total_cholesterol');
				break;

				case 13:
					$inresults = array('antigen_blood');
				break;

				case 14:
					$inresults = array('positive_result');
				break;

				case 15:
					$inresults = array('sonographic_signs');
				break;

				case 16:
					$inresults = array('pathology_found');
				break;
				
				case 150:
					$inresults = array('migrant_tub', 'migrant_tub_decr', 'migrant_prev_fg', 'migrant_tub_first_dt', 'migrant_tub_take_dt', 'migrant_tub_group', 'migrant_tub_method', 'migrant_tub_decay', 'migrant_tub_bac', 'migrant_tub_bac_method', 'migrant_tub_morbus', 'migrant_tub_narko');
				break;
				
				case 151:
					$inresults = array('migrant_narko');
				break;
				
				case 154:
					$inresults = array('migrant_syphilis');
				break;
				
				case 152:
					$inresults = array('migrant_HIV_diagn', 'migrant_HIV', 'migrant_HIV_lepr');
				break;
				
				case 142:
					$inresults = array('migrant_HIV_at1');
				break;
				
				case 143:
					$inresults = array('migrant_HIV_at2');
				break;
				
				case 144:
					$inresults = array('migrant_syphilis_ifa');
				break;
				
				case 145:
					$inresults = array('migrant_syphilis_rpga');
				break;
				
				case 146:
					$inresults = array('migrant_syphilis_rmp');
				break;
				
				case 147:
					$inresults = array('migrant_mantu');
				break;
				
				case 148:
					$inresults = array('migrant_allergen');
				break;
				
				case 153:
					$inresults = array('migrant_fluoro');
				break;
				
				case 149:
					$inresults = array('migrant_urine_amphet', 'migrant_urine_marij', 'migrant_urine_morp', 'migrant_urine_cocaine', 'migrant_urine_meth');
				break;
				
				case 155:
				case 156:
				case 157:
				case 158:
					$inresults = array('driver_result');
				break;
				
				case 159:
					$inresults = array('migrant_HIV_at1_at2');
				break;
				
				case 160:
					$inresults = array('migrant_Tub_Probe', 'migrant_tub_size');
				break;
				case 163:
				case 139:
					if($data['ExtVersion']<6) break;
					$inresults = array('indi_prof_consult');
					//сохраняем индивидуальное профилактическое консультирование:
					if (!empty($data['indi_prof_consult'])) {
						$data['indi_prof_consult'] = json_decode($data['indi_prof_consult'], true);
					} else {
						$data['indi_prof_consult'] = array();
					}
					foreach($data['indi_prof_consult'] as $ipconsult) {
						if(!$ipconsult['checked'] /*&& !empty($ipconsult['DispCons_id'])*/) {//снят флаг с имеющейся консультации -> на удаление
							//удаление
							$query = "
								select
									Error_Code as \"Error_Code\", 
									Error_Message as \"Error_Msg\"
								from p_DispCons_del (
									DispCons_id => :DispCons_id,
									IsRemove => 2,
									pmUser_id => :pmUser_id
								)
							";
							$params = array(
								'DispCons_id' => $ipconsult['DispCons_id'],
								'pmUser_id' => $data['pmUser_id']
							);
							$result_ipc = $this->db->query($query, $params);
							if (!is_object($result_ipc))
							{
								return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление индивидуальной профилактической консультации)'));
							}
							$result_ipc = $result_ipc->result('array');
							if (!is_array($result_ipc) || count($result_ipc) == 0)
							{
								return array(0 => array('Error_Msg' => 'Ошибка при удалении индивидуальной профилактической консультации'));
							}
							else if (strlen($result_ipc[0]['Error_Msg']) > 0)
							{
								return $result_ipc;
							}
						} else {
							$proc_ipconsult = 'p_DispCons_upd';
							if($ipconsult['checked'] && empty($ipconsult['DispCons_id'])) {//новая консультация - добавить
								$query = "
									SELECT
										DispCons_id
									FROM
										v_DispCons 
									WHERE
										EvnPLDisp_id = :EvnPLDisp_id AND DispRiskFactorCons_id = :DispRiskFactorCons_id
									limit 1
								";
								$DispCons_id = $this->getFirstResultFromQuery($query, array(
									'EvnPLDisp_id'=>$data['EvnVizitDispDop_pid'],
									'DispRiskFactorCons_id' => $ipconsult['DispRiskFactorCons_id']
									));
								if(empty($DispCons_id)) {
									$proc_ipconsult = 'p_DispCons_ins';
								} else {
									$ipconsult['DispCons_id'] = $DispCons_id;
								}
							}
							$query = "
								select
									DispCons_id as \"DispCons_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from {$proc_ipconsult} (
									DispCons_id => :DispCons_id,
									EvnPLDisp_id => :EvnPLDisp_id,
									DispRiskFactorCons_id => :DispRiskFactorCons_id,
									DispCons_Text => :DispCons_Text,
									pmUser_id => :pmUser_id
								)
							";
							$params = array(
								'DispCons_id' => empty($ipconsult['DispCons_id']) ? null : $ipconsult['DispCons_id'],
								'DispRiskFactorCons_id' => $ipconsult['DispRiskFactorCons_id'],
								'EvnPLDisp_id' => $data['EvnVizitDispDop_pid'],
								'DispCons_Text' => $ipconsult['DispCons_Text'],//из формы уже приходит нужное - дефолтный или правленый текст
								'pmUser_id' => $data['pmUser_id']
							);
							//exit(getDebugSQL($query, $params));
							$result_ipc = $this->db->query($query, $params);
							if (!is_object($result_ipc))
							{
								return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение индивидуальной профилактической консультации)'));
							}
							$result_ipc = $result_ipc->result('array');
							if (!is_array($result_ipc) || count($result_ipc) == 0)
							{
								return array(0 => array('Error_Msg' => 'Ошибка при сохранении индивидуальной профилактической консультации'));
							}
							else if (strlen($result_ipc[0]['Error_Msg']) > 0)
							{
								return $result_ipc;
							}
						}
					}
					break;
			}
		}
		
		foreach ( $inresults as $inresult ) {
			if ( !isset($data[$inresult]) || $data[$inresult] == '' ) {
				$data[$inresult] = NULL;
			}
			
			if($data['ExtVersion']>5) {//сохранение результатов в интерфейсе > ext6
				$inresultdata = $this->getFormalizedInspectionParamsData($inresult, $EvnUsluga_id); // $EvnUsluga_id === EvnUslugaDispDop_id
				
				if ( !empty($inresultdata['FormalizedInspectionParams_id']) ) {
					$queryParams = array(
						'EvnUslugaDispDop_id' => $EvnUsluga_id,
						'FormalizedInspection_id' => $inresultdata['FormalizedInspection_id'],
						'FormalizedInspectionParams_id' => $inresultdata['FormalizedInspectionParams_id'],
						'FormalizedInspection_NResult' => NULL,
						'FormalizedInspection_Result' => NULL,
						'FormalizedInspection_DirectoryAnswer_id' => NULL,
						'pmUser_id' => $data['pmUser_id']
					);

					switch ($inresultdata['RateValueType_SysNick']) {
						case 'int': $queryParams['FormalizedInspection_NResult'] = $data[$inresult]; break;
						case 'float': $queryParams['FormalizedInspection_NResult'] = !empty($data[$inresult]) ? str_replace(',','.',$data[$inresult]) : null; break;
						case 'string': $queryParams['FormalizedInspection_Result'] = $data[$inresult]; break;
						case 'reference': $queryParams['FormalizedInspection_DirectoryAnswer_id'] = $data[$inresult]; break;
						case 'datetime': $queryParams['FormalizedInspection_Result'] = $data[$inresult]; break;
					}
					
					$proc_ext = empty($inresultdata['FormalizedInspection_id']) ? 'ins' : 'upd';
					
					$sql = "
						select
							FormalizedInspection_id as \"FormalizedInspection_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_FormalizedInspection_{$proc_ext} (
							FormalizedInspection_id => :FormalizedInspection_id,
							EvnUslugaDispDop_id => :EvnUslugaDispDop_id,
							FormalizedInspectionParams_id => :FormalizedInspectionParams_id,
							FormalizedInspection_Result => :FormalizedInspection_Result,
							FormalizedInspection_DirectoryAnswer_id => :FormalizedInspection_DirectoryAnswer_id,
							FormalizedInspection_PathologySize => 0,
							FormalizedInspection_NResult => :FormalizedInspection_NResult,
							pmUser_id => :pmUser_id
						)
					";
					//exit(getDebugSql($sql, $queryParams));
					$res = $this->db->query($sql, $queryParams);

					if ( !is_object($res) ) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)'));
					}

					$resprate = $res->result('array');

					if ( !is_array($resprate) || count($resprate) == 0 ) {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Ошибка при сохранении показателя услуги'));
					}
					else if ( !empty($resprate[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return $resprate;
					}
				}
				
			} 
			{//сохранение результатов в ext2 интерфейсе (в новом пока сделаем дублирование)
				// получаем идентификатор EvnUslugaRate и тип сохраняемых данных
				$inresultdata = $this->getRateData($inresult, $EvnUsluga_id);

				if ( !empty($inresultdata['RateType_id']) ) {
					// если такого результата в бд ещё нет, то добавляем
					if ( empty($inresultdata['EvnUslugaRate_id']) ) {
						// сначала p_Rate_ins
						$sql = "
							select Rate_id as \"Rate_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
							from p_Rate_ins(
								Rate_id := :Rate_id,
								RateType_id := :RateType_id,
								Rate_ValueInt := :Rate_ValueInt,
								Rate_ValueFloat := :Rate_ValueFloat,
								Rate_ValueStr := :Rate_ValueStr,
								Rate_ValuesIs := :Rate_ValuesIs,
								Rate_ValueDT := :Rate_ValueDT,
								Server_id := :Server_id,
								pmUser_id := :pmUser_id);
						";
						$queryParams = array(
							'Rate_id' => NULL,
							'RateType_id' => $inresultdata['RateType_id'],
							'Rate_ValueInt' => NULL,
							'Rate_ValueFloat' => NULL,
							'Rate_ValueStr' => NULL,
							'Rate_ValuesIs' => NULL,
							'Rate_ValueDT' => NULL,
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						);

						switch ($inresultdata['RateValueType_SysNick']) {
							case 'int': $queryParams['Rate_ValueInt'] = $data[$inresult]; break;
							case 'float': $queryParams['Rate_ValueFloat'] = !empty($data[$inresult]) ? str_replace(',','.', $data[$inresult]) : null; break;
							case 'string': $queryParams['Rate_ValueStr'] = $data[$inresult]; break;
							case 'reference': $queryParams['Rate_ValuesIs'] = $data[$inresult]; break;
							case 'datetime': $queryParams['Rate_ValueDT'] = $data[$inresult]; break;
						}

						$res = $this->db->query($sql, $queryParams);

						if ( !is_object($res) ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)'));
						}

						$resprate = $res->result('array');

						if ( !is_array($resprate) || count($resprate) == 0 ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при сохранении показателя услуги'));
						}
						else if ( !empty($resprate[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return $resprate;
						}
						
						// затем p_EvnUslugaRate_ins
						$sql = "
							select EvnUslugaRate_id as \"EvnUslugaRate_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
							from p_EvnUslugaRate_ins(
								EvnUslugaRate_id := :EvnUslugaRate_id,
								EvnUsluga_id := :EvnUsluga_id,
								Rate_id := :Rate_id,
								Server_id := :Server_id,
								pmUser_id := :pmUser_id);	
						";

						$queryParams = array(
							'EvnUslugaRate_id' => NULL,
							'EvnUsluga_id' => $EvnUsluga_id,
							'Rate_id' => $resprate[0]['Rate_id'],
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						);
	 
						$res = $this->db->query($sql, $queryParams);

						if ( !is_object($res) ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)'));
						}

						$resp = $res->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при сохранении показателя услуги'));
						}
						else if ( !empty($resp[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return $resp;
						}
					}
					// иначе обновляем тот, что есть
					else {
						// p_Rate_upd
						$sql = "
							select Rate_id as \"Rate_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                        
							from p_Rate_upd(
								Rate_id := :Rate_id,
								RateType_id := :RateType_id,
								Rate_ValueInt := :Rate_ValueInt,
								Rate_ValueFloat := :Rate_ValueFloat,
								Rate_ValueStr := :Rate_ValueStr,
								Rate_ValuesIs := :Rate_ValuesIs,
								Rate_ValueDT := :Rate_ValueDT,
								Server_id := :Server_id,
								pmUser_id := :pmUser_id);
						";
						$queryParams = array(
							'Rate_id' => $inresultdata['Rate_id'],
							'RateType_id' => $inresultdata['RateType_id'],
							'Rate_ValueInt' => NULL,
							'Rate_ValueFloat' => NULL,
							'Rate_ValueStr' => NULL,
							'Rate_ValuesIs' => NULL,
							'Rate_ValueDT' => NULL,
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						);

						switch ($inresultdata['RateValueType_SysNick']) {
							case 'int': $queryParams['Rate_ValueInt'] = $data[$inresult]; break;
							case 'float': $queryParams['Rate_ValueFloat'] = !empty($data[$inresult]) ? str_replace(',','.', $data[$inresult]) : null; break;
							case 'string': $queryParams['Rate_ValueStr'] = $data[$inresult]; break;
							case 'reference': $queryParams['Rate_ValuesIs'] = $data[$inresult]; break;
							case 'datetime': $queryParams['Rate_ValueDT'] = $data[$inresult]; break;
						}

						$res = $this->db->query($sql, $queryParams);

						if ( !is_object($res) ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (обновление показателя услуги)'));
						}

						$resp = $res->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка при обновлении показателя услуги'));
						}
						else if ( !empty($resp[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return $resp;
						}
					}
				}
			}
		}

		// Надо проставить дату начала/окончания в связанной карте
		if (in_array($DispClass_id , array(1,2))) {
			// получаем минимальную/максимальную даты
			$this->load->model('EvnPLDispDop13_model');
			$minmaxdates = $this->EvnPLDispDop13_model->getEvnUslugaDispDopMinMaxDates(array(
				'EvnPLDispDop13_id' => $data['EvnVizitDispDop_pid'],
				'DispClass_id' => $DispClass_id
			));

			if (is_array($minmaxdates)) {
				$setDtUpdate = "";
				if (getRegionNick() == 'pskov') {
					// Для Пскова в качестве даты надо сохранять минимальную дату из услуг
					$setDtUpdate = "Evn_setDT = :Evn_setDT,";
				}
				// обновляем в карте
				$query = "
					update EvnPLDispDop13
					SET
						Evn_disDT = CAST(case when EvnPLDispDop13_isEndStage = 2 then :Evn_disDT 
                        else null end as date),
						{$setDtUpdate}
						Evn_updDT = dbo.tzGetDate(),
						pmUser_updId = :pmUser_id
					where
						EvnPLDispDop13.Evn_id = :EvnVizitDispDop_pid 
				";
				$res = $this->db->query($query, array(
					'Evn_setDT' => $minmaxdates['mindate'],
					'Evn_disDT' => $minmaxdates['maxdate'],
					'EvnVizitDispDop_pid' => $data['EvnVizitDispDop_pid'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ($res !== true) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при обновлении даты окончания карты'));
				}
			}
		}

		if (getRegionNick() == 'pskov' && $DispClass_id == 5) {
			// получаем минимальную/максимальную даты
			$this->load->model('EvnPLDispProf_model');
			$minmaxdates = $this->EvnPLDispProf_model->getEvnUslugaDispDopMinMaxDates(array(
				'EvnPLDispProf_id' => $data['EvnVizitDispDop_pid']
			));

			if (is_array($minmaxdates)) {
				// обновляем в карте
				$query = "
					update Evn 
					SET
						Evn_setDT = :Evn_setDT,
						Evn_updDT = dbo.tzGetDate(),
						pmUser_updId = :pmUser_id
					FROM
						Evn e
						INNER JOIN EvnPLDispProf  ON e.Evn_id = EvnPLDispProf.Evn_id
					where
						e.Evn_id = :EvnVizitDispDop_pid AND e.Evn_id Evn.Evn_id
				";
				$res = $this->db->query($query, array(
					'Evn_setDT' => $minmaxdates['mindate'],
					'Evn_disDT' => $minmaxdates['maxdate'],
					'EvnVizitDispDop_pid' => $data['EvnVizitDispDop_pid'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ($res !== true) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при обновлении даты окончания карты'));
				}
			}
		}

		// Проставить дату окончания профосмотру равной дате осмотра врача педиатра
		if ($DispClass_id == 5 && $SurveyType_Code == 19) {
			$query = "
				update Evn
				SET
					Evn_disDT = :EvnUslugaDispDop_didDate,
					Evn_updDT = dbo.tzGetDate(),
					pmUser_updId = :pmUser_id
				FROM
					Evn e 
					INNER JOIN EvnPLDispProf  ON e.Evn_id = EvnPLDispProf.Evn_id
				where
					e.Evn_id = :EvnVizitDispDop_pid AND
					EvnPLDispProf_isEndStage = 2 AND
					Evn.Evn_id = e.Evn_id 
			";
			$res = $this->db->query($query, $data);
			
			if ( $res !== true ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при обновлении даты окончания карты'));
			}
		}

		if (in_array($DispClass_id, array(1,2,5))) {
			$data['EvnPLDisp_id'] = $data['EvnVizitDispDop_pid'];
			
		// Автоматически создавать поля списка "Впервые выявленные заболевания" по проведенным осмотрам, если в осмотре в поле «"Характер заболевания"»  указано значение «Выявленное во время диспансеризации»
		$this->load->model('EvnDiagDopDisp_model', 'evndiagdopdisp');
		if (!empty($data['DopDispDiagType_id']) && $data['DopDispDiagType_id'] == 2 && !empty($data['Diag_id'])) {
				$data['EvnDiagDopDisp_setDate'] = $data['EvnUslugaDispDop_didDate'];
				$this->evndiagdopdisp->addEvnDiagDopDispFirst($data, $data['Diag_id']);
			}
			
			$isPersonDisp = false;
			// проверяем существование карты дисп учёта
			$query = "
				WITH cte AS (				
					select
						EvnPLDisp_consDT as EvnPLDisp_consDate, 
						Person_id as Person_id
					from
						v_EvnPLDisp 
					where
						EvnPLDisp_id = :EvnPLDisp_id
				)					
				select 
					PersonDisp_id as \"PersonDisp_id\"
				from
					v_PersonDisp 
				where
					Person_id = (SELECT Person_id FROM cte)
					and (PersonDisp_begDate <= (SELECT EvnPLDisp_consDate FROM cte) OR PersonDisp_begDate IS NULL)
					and (PersonDisp_endDate >= (SELECT EvnPLDisp_consDate FROM cte) OR PersonDisp_endDate IS NULL)
                limit 1
			";
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$isPersonDisp = true;
				}
			}
			
			// В Ранее известные имеющиеся заболевания - если в маршрутке указан диагноз отличный от Z и характер заболевании 1. Ранее известное хроническое, при этом нет Д-учета в ЭМК – грузить данные, дата постановки диагноза по умолчанию = дате в маршрутке (refs #26202)
			if (!$isPersonDisp && !empty($data['DopDispDiagType_id']) && $data['DopDispDiagType_id'] == 1 && !empty($data['Diag_id'])) {
				$data['EvnDiagDopDisp_setDate'] = $data['EvnUslugaDispDop_didDate'];
				$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $data['Diag_id']);
			}
		}

		// https://redmine.swan.perm.ru/issues/33554
		// Добавляем повторную проверку на наличие дублей
		$sql = "
			select EvnVizitDispDop_id
			from v_EvnVizitDispDop 
			where DopDispInfoConsent_id = :DopDispInfoConsent_id
				and EvnVizitDispDop_id != :EvnVizitDispDop_id
			limit 1
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих посещений)'));
		}

		$resp = $res->result('array');

		if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnVizitDispDop_id']) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Обнаружены дубли посещений по выбранному исследованию/осмотру. Произведен откат транзакции. Пожалуйста, повторите сохранение.'));
		}

		$this->db->trans_commit();

		$ret_data = array('EvnUslugaDispDop_id' => $EvnUsluga_id, 'Error_Code' => '', 'Error_Msg' => '');

		if ($SurveyType_Code == 19 && empty($data['ignoreCheckMorbusOnko'])) {
			$checkSpec = $this->EvnVizitDispDop_model->checkOnkoSpecifics($data);
			if (is_array($checkSpec) && !empty($checkSpec['Alert_Msg'])) {
				$ret_data['Alert_Msg'] = $checkSpec['Alert_Msg'];
				$openSpecificAfterSave = true;
			}
		}

		if($openSpecificAfterSave)
			$ret_data['openSpecificAfterSave'] = 'addSpecific';

		if (!empty($data['EvnVizitDispDop_id']))  $ret_data['EvnVizitDispDop_id'] = $data['EvnVizitDispDop_id'];
		return array($ret_data);
	}
	
	/**
	 *	Загрузка формы редактирования услуги
	 */
	function loadEvnUslugaDispDop($data) {
		$query = "
			select 
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDD.EvnUslugaDispDop_pid as \"EvnUslugaDispDop_pid\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				EUDD.Server_id as \"Server_id\",
				EUDD.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDD.EvnUslugaDispDop_setDT, 'DD.MM.YYYY') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDD.EvnUslugaDispDop_setTime, 'HH24:MI') as \"EvnUslugaDispDop_setTime\",
				EUDD.UslugaComplex_id as \"UslugaComplex_id\",
				to_char(EUDD.EvnUslugaDispDop_didDT, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				to_char(EUDD.EvnUslugaDispDop_didTime, 'HH24:MI') as \"EvnUslugaDispDop_didTime\",
				to_char(EUDD.EvnUslugaDispDop_disDT, 'DD.MM.YYYY') as \"EvnUslugaDispDop_disDate\",
				to_char(EUDD.EvnUslugaDispDop_disTime, 'HH24:MI') as \"EvnUslugaDispDop_disTime\",
				EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				EUDD.Lpu_uid as \"Lpu_uid\",
				EUDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EUDD.MedSpecOms_id as \"MedSpecOms_id\",
				EUDD.MedStaffFact_id as \"MedStaffFact_id\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				EUDD.EvnDirection_id as \"EvnDirection_id\",
				EVDD.Diag_id as \"Diag_id\",
				EVDD.TumorStage_id as \"TumorStage_id\",
				EVDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				COALESCE(CAST(EVDD.EvnVizitDispDop_DeseaseStage as varchar),'') as \"DeseaseStage\",
				EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				EVDD.EvnVizitDispDop_pid as \"EvnVizitDispDop_pid\",
				EVDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				case when ep.EvnPrescr_id is not null then 1 else 0 end as \"EvnUslugaDispDop_WithDirection\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ed.EvnDirection_insDT, 'DD.MM.YYYY') as \"EvnDirection_insDate\",
				case
					when TTMS.TimetableMedService_id is not null then COALESCE(MS.MedService_Name,'') || ' / '|| COALESCE(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then COALESCE(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then COALESCE(MS.MedService_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then COALESCE(MS.MedService_Name,'') ||' / '|| COALESCE(LSPD.LpuSectionProfile_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
							else COALESCE(LSPD.LpuSectionProfile_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
						end ||' / '|| COALESCE(Lpu.Lpu_Nick,'')
				else '' end as \"EvnDirection_RecTo\"
				,case
					when TTMS.TimetableMedService_id is not null then COALESCE(to_char(TTMS.TimetableMedService_begTime, 'DD.MM.YYYY'),'')||' '||COALESCE(to_char(TTMS.TimetableMedService_begTime, 'HH24:MI'),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '|| COALESCE(to_char(EQ.EvnQueue_setDate, 'DD.MM.YYYY'),'')
				else '' end as \"EvnDirection_RecDate\",
				case when ep.EvnPrescr_id is not null then 'Назначение' else '' end as \"EvnDirection_Type\",
				EUDD.EvnUslugaDispDop_Result as \"EvnUslugaDispDop_Result\"
			from v_EvnUslugaDispDop EUDD 
				inner join v_EvnVizitDispDop EVDD  on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				LEFT JOIN LATERAL (
					Select ep.Evn_id as EvnPrescr_id from EvnPrescr ep  
					inner join Evn  on ep.Evn_id = Evn.Evn_id and Evn.Evn_deleted = 1
					where ep.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
                    limit 1
				) ep ON true
				LEFT JOIN LATERAL (
					Select ED.EvnDirection_id
						,COALESCE(ED.Lpu_sid, ED.Lpu_id) Lpu_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.Lpu_did
						,ED.MedService_id
						,ED.LpuSectionProfile_id
						,ED.EvnDirection_insDT
					from v_EvnPrescrDirection epd 
					inner join v_EvnDirection_all ED  on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when COALESCE(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
                    limit 1
				) ED ON true
				LEFT JOIN LATERAL (
					Select TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS  where TTMS.EvnDirection_id = ED.EvnDirection_id
                    limit 1
				) TTMS ON true
				LEFT JOIN LATERAL (
	                (
					Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ 
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
                    limit 1
                    )
					union
                    (
					Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ 
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
                    limit 1
                    )
				) EQ ON true
				left join v_MedService MS  on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				left join v_LpuSection LS  on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_LpuUnit LU  on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				left join v_LpuSectionProfile LSPD  on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				left join v_Lpu Lpu  on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
			where EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
		";
		$result = $this->db->query($query, array(
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			// нужно получить значения результатов услуги из EvnUslugaRate
			if (isset($resp[0]['EvnUslugaDispDop_id']) /*&& in_array($data['session']['region']['nick'], array('ekb','perm', 'ufa'))*/) {
				// получаем согласие на цитологическое исследование
				$resp[0]['Cyto_IsNotAgree'] = 0;
				$resp_cyto = $this->queryResult("
					select 
						ddic.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						ddic.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
						ddic.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\"
					from
						v_DopDispInfoConsent ddic 
						inner join v_SurveyTypeLink stl  on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where
						ddic.EvnPLDisp_id = :EvnPLDisp_id
						and stl.SurveyTypeLink_ComplexSurvey = 2
					limit 1
				", array(
					'EvnPLDisp_id' => $resp[0]['EvnVizitDispDop_pid']
				));
				if (!empty($resp_cyto[0]['DopDispInfoConsent_id'])) {
					if ($resp_cyto[0]['DopDispInfoConsent_IsAgree'] == 1 && $resp_cyto[0]['DopDispInfoConsent_IsEarlier'] == 1) {
						$resp[0]['Cyto_IsNotAgree'] = 1;
					}
				}

				if ($resp[0]['Cyto_IsNotAgree'] == 0) {
					// получаем данные по цитологическому исследованию
					$query = "
						select
							EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
							EU.Lpu_uid as \"Lpu_id\",
							EU.LpuSectionProfile_id as \"LpuSectionProfile_id\",
							EU.LpuSection_uid as \"LpuSection_id\",
							EU.MedStaffFact_id as \"MedStaffFact_id\",
							EU.MedSpecOms_id as \"MedSpecOms_id\",
							EU.MedPersonal_id as \"MedPersonal_id\",
							to_char(EU.EvnUsluga_setDT, 'DD.MM.YYYY') as \"EvnUsluga_setDate\"
						from
							v_EvnUsluga EU 
							left join EvnUslugaDispDop EUDD  on EUDD.Evn_id = EU.EvnUsluga_id
						where
							EU.EvnUsluga_pid = :EvnUsluga_pid
					";
					$result = $this->db->query($query, array(
						'EvnUsluga_pid' => $resp[0]['EvnUslugaDispDop_id']
					));
					if (is_object($result)) {
						$cytoresp = $result->result('array');
						if (!empty($cytoresp[0]['EvnUsluga_setDate'])) {
							$resp[0]['CytoExaminationPlace_id'] = $cytoresp[0]['ExaminationPlace_id'];
							$resp[0]['CytoLpu_id'] = $cytoresp[0]['Lpu_id'];
							$resp[0]['CytoLpuSectionProfile_id'] = $cytoresp[0]['LpuSectionProfile_id'];
							$resp[0]['CytoLpuSection_id'] = $cytoresp[0]['LpuSection_id'];
							$resp[0]['CytoMedSpecOms_id'] = $cytoresp[0]['MedSpecOms_id'];
							$resp[0]['CytoMedStaffFact_id'] = $cytoresp[0]['MedStaffFact_id'];
							$resp[0]['CytoMedPersonal_id'] = $cytoresp[0]['MedPersonal_id'];
							$resp[0]['CytoEvnUsluga_setDate'] = $cytoresp[0]['EvnUsluga_setDate'];
						}
					}
				}

				if($data['ExtVersion']>5) {//загрузка параметров в новом интерфейсе
					$query = "
						select 
							FI.FormalizedInspectionParams_id as \"FormalizedInspectionParams_id\",
							FIP.FormalizedInspectionParams_SysNick as \"FormalizedInspectionParams_SysNick\",
							RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16)) as varchar)
								WHEN 'float' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16,3)) as varchar)
								WHEN 'string' THEN FI.FormalizedInspection_Result
								WHEN 'template' THEN FI.FormalizedInspection_Result
								WHEN 'reference' THEN cast(FI.FormalizedInspection_DirectoryAnswer_id as varchar)
								WHEN 'datetime' THEN to_char(cast(FI.FormalizedInspection_Result as date), 'DD.MM.YYYY')
							END as \"value\"
						from 
							v_FormalizedInspection FI
							left join v_FormalizedInspectionParams FIP on FIP.FormalizedInspectionParams_id = FI.FormalizedInspectionParams_id
							left join RateValueType RVT on RVT.RateValueType_id = FIP.RateValueType_id
						where 
							FI.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
					";
					$queryParams = array(
						'EvnUslugaDispDop_id' => $resp[0]['EvnUslugaDispDop_id']
					);
					//exit(getDebugSQL($query, $queryParams));
					$result = $this->db->query($query, $queryParams);
					if ( is_object($result) ) {
						$results = $result->result('array');
						foreach($results as $oneresult) {
							$resp[0][$oneresult['FormalizedInspectionParams_SysNick']] = $oneresult['value'];
						}
					}
				} else { //ext2
					$query = "
						select 
							RT.RateType_SysNick as \"nick\",
							RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
								WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
								WHEN 'string' THEN R.Rate_ValueStr
								WHEN 'template' THEN R.Rate_ValueStr
								WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
								WHEN 'datetime' THEN to_char(R.Rate_ValueDT, 'DD.MM.YYYY')
							END as \"value\"
						from 
							v_EvnUslugaRate eur 
							left join v_Rate r  on r.Rate_id = eur.Rate_id 
							left join v_RateType rt  on rt.RateType_id = r.RateType_id
							left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
						where 
							eur.EvnUsluga_id = :EvnUsluga_id
					";
					$result = $this->db->query($query, array(
						'EvnUsluga_id' => $resp[0]['EvnUslugaDispDop_id']
					));
					if ( is_object($result) ) {
						$results = $result->result('array');
						foreach($results as $oneresult) {
							if ($oneresult['RateValueType_SysNick'] == 'float') {
								if ( $oneresult['nick'] == 'bio_blood_kreatinin' ) {
									// Ничего не делаем
								}
								/*
								else if ( in_array($oneresult['nick'], array('AsAt', 'AlAt')) ) {
									// Убираем последнюю цифру в значении
									if (!empty($oneresult['value'])) {
										$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
									}
								}
								else {
									// Убираем последние 2 цифры в значении
									if (!empty($oneresult['value'])) {
										$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
									}
								}
								*/
								else {
									// Убираем последнюю цифру в значении
									// http://redmine.swan.perm.ru/issues/23248
									$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
								}
							}

							$resp[0][$oneresult['nick']] = $oneresult['value'];
						}
					}
				}
			}
		
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDisp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopData($data)
	{		
		$query = "
			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				to_char(EUDD.EvnUslugaDispDop_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				EUDD.DopDispUslugaType_id as \"DopDispUslugaType_id\"
			from v_EvnUslugaDispDop EUDD 			
			where EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id
		";	
		
		$result = $this->db->query($query, array('EvnPLDisp_id' => $data['EvnPLDisp_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	*	Расчёт поля SCORE
	*/
	function loadScoreField($data) {
		/*
			считается по 5 параметрам:
			- давление
			- курение
			- пол
			- возраст
			- холестерин
		*/
		$scorevalue = '';
		
		// 1. читаем необходимые параметры
		$query = "
			select 
				dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) as \"Person_Age\",
				PS.Sex_id as \"Sex_id\",
				case 
					when DDQkur.value = 3 then 1
					when DDQkur.value is null and DD13kur.value13 is null then DP.valueP
					else case when COALESCE(DDQkur.value,0) = 0 then DD13kur.value13 else DDQkur.value end 
				end as \"EvnPLDisp_IsSmoking\",
				USsys.value as \"systolic_blood_pressure\",
				USchol.value as \"total_cholesterol\"
			from
				v_EvnPLDisp EPLD 
				left join v_PersonState PS  on ps.Person_id = EPLD.Person_id
				LEFT JOIN LATERAL(
					select 
						DopDispQuestion_ValuesStr as value 
					from
					 	DopDispQuestion DDQ
					 	left join v_QuestionType QT on QT.QuestionType_id = DDQ.QuestionType_id
					where
						QT.QuestionType_Name='Курите ли Вы? (курение одной и более сигарет в день)'
						and QT.DispClass_id =EPLD.DispClass_id
						and COALESCE(QT.QuestionType_begDate, EPLD.EvnPLDisp_consDT) <= EPLD.EvnPLDisp_consDT
						and COALESCE(QT.QuestionType_endDate, EPLD.EvnPLDisp_consDT) >= EPLD.EvnPLDisp_consDT
						and COALESCE(QT.QuestionType_AgeFrom, Person_Age) <= Person_Age
						and COALESCE(QT.QuestionType_AgeTo, Person_Age) >= Person_Age
					 	and DDQ.EvnPLDisp_id = EPLD.EvnPLDisp_id
                    limit 1
				) DDQkur ON true
				LEFT JOIN LATERAL(
					select DD13.EvnPLDispDop13_IsSmoking as value13 from v_EvnPLDispDop13 DD13  where DD13.EvnPLDispDop13_id = EPLD.EvnPLDisp_id
                    limit 1
				) DD13kur ON true
				LEFT JOIN LATERAL(
					select EvnPLDispProf_IsSmoking as valueP
					from v_EvnPLDispProf
					where EvnPLDispProf_id = EPLD.EvnPLDisp_id		
                    limit 1
				)DP ON true
				LEFT JOIN LATERAL(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from
						v_EvnVizitDispDop evdd 
						left join v_EvnUslugaDispDop eudd  on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_EvnUslugaRate eur  on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
						left join v_Rate r  on r.Rate_id = eur.Rate_id 
						left join v_RateType rt  on rt.RateType_id = r.RateType_id
						left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and RT.RateType_SysNick = 'systolic_blood_pressure'
                    limit 1
				) USsys ON true
				LEFT JOIN LATERAL(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from
						v_EvnVizitDispDop evdd 
						left join v_EvnUslugaDispDop eudd  on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_EvnUslugaRate eur  on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
						left join v_Rate r  on r.Rate_id = eur.Rate_id 
						left join v_RateType rt  on rt.RateType_id = r.RateType_id
						left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and RT.RateType_SysNick = 'total_cholesterol'
                    limit 1
				) USchol ON true
			where
				EPLD.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['systolic_blood_pressure'] = $resp[0]['systolic_blood_pressure'];
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['total_cholesterol'] = $resp[0]['total_cholesterol'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['EvnPLDisp_IsSmoking'] = $resp[0]['EvnPLDisp_IsSmoking'];
			}
		}
		
		// ддя 2 этапа определяем незаданные параметры из первого этапа
		$data['EvnPLDisp_fid'] = $this->getFirstResultFromQuery("SELECT EvnPLDisp_fid  as \"EvnPLDisp_fid\" FROM v_EvnPLDisp  WHERE EvnPLDisp_id = :EvnPLDisp_id", $data);

		if (!empty($data['EvnPLDisp_fid'])) {
			// 1. читаем необходимые параметры
			$query = "
				select 
					dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('YEAR', EPLD.EvnPLDisp_consDT) as varchar) || '-12-31' as timestamp)) as \"Person_Age\",
					PS.Sex_id as \"Sex_id\",
					case when DDQkur.value = 3 then 1 else DDQkur.value end as \"EvnPLDisp_IsSmoking\",
					USsys.value as \"systolic_blood_pressure\",
					USchol.value as \"total_cholesterol\"
				from
					v_EvnPLDisp EPLD 
					left join v_PersonState PS  on ps.Person_id = EPLD.Person_id
					LEFT JOIN LATERAL(
						select DopDispQuestion_ValuesStr as value from DopDispQuestion DDQ  where DDQ.QuestionType_id IN (26,67,120,155,704,730) and DDQ.EvnPLDisp_id = EPLD.EvnPLDisp_id
                        limit 1
					) DDQkur ON true
					LEFT JOIN LATERAL(
						select 
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
								WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
								WHEN 'string' THEN R.Rate_ValueStr
								WHEN 'template' THEN R.Rate_ValueStr
								WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
							END as value
						from
							v_EvnVizitDispDop evdd 
							left join v_EvnUslugaDispDop eudd  on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_EvnUslugaRate eur  on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
							left join v_Rate r  on r.Rate_id = eur.Rate_id 
							left join v_RateType rt  on rt.RateType_id = r.RateType_id
							left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
						where 
							evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and RT.RateType_SysNick = 'systolic_blood_pressure'
                        limit 1
					) USsys ON true
					LEFT JOIN LATERAL(
						select 
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
								WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
								WHEN 'string' THEN R.Rate_ValueStr
								WHEN 'template' THEN R.Rate_ValueStr
								WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
							END as value
						from
							v_EvnVizitDispDop evdd 
							left join v_EvnUslugaDispDop eudd  on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_EvnUslugaRate eur  on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
							left join v_Rate r  on r.Rate_id = eur.Rate_id 
							left join v_RateType rt  on rt.RateType_id = r.RateType_id
							left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
						where 
							evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and RT.RateType_SysNick = 'total_cholesterol'
                        limit 1
					) USchol ON true
				where
					EPLD.EvnPLDisp_id = :EvnPLDisp_fid
                limit 1
			";
			
			$result = $this->db->query($query, $data);
			
			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (empty($data['systolic_blood_pressure']) && !empty($resp[0]['systolic_blood_pressure'])) {
						$data['systolic_blood_pressure'] = $resp[0]['systolic_blood_pressure'];
					}
					if (empty($data['Person_Age']) && !empty($resp[0]['Person_Age'])) {
						$data['Person_Age'] = $resp[0]['Person_Age'];
					}
					if (empty($data['total_cholesterol']) && !empty($resp[0]['total_cholesterol'])) {
						$data['total_cholesterol'] = $resp[0]['total_cholesterol'];
					}
					if (empty($data['Sex_id']) && !empty($resp[0]['Sex_id'])) {
						$data['Sex_id'] = $resp[0]['Sex_id'];
					}
					if (empty($data['EvnPLDisp_IsSmoking']) && !empty($resp[0]['EvnPLDisp_IsSmoking'])) {
						$data['EvnPLDisp_IsSmoking'] = $resp[0]['EvnPLDisp_IsSmoking'];
					}
				}
			}
		}
		
		$errors = array();
		if (empty($data['systolic_blood_pressure']))
		{
			$errors[] = 'давление';
		}
		if (empty($data['Person_Age']))
		{
			$errors[] = 'возраст';
		}
		if (empty($data['total_cholesterol']))
		{
			$errors[] = 'холестерин';
		}
		if (empty($data['Sex_id']))
		{
			$errors[] = 'пол';
		}
		if (empty($data['EvnPLDisp_IsSmoking']))
		{
			$errors[] = 'курение';
		}		
		if (count($errors) > 0) {
			return array('Error_Msg' => 'Не указаны необходимые параметры для расчёта: '.implode($errors,','));
		}
			
		// 2. запрос значения SCORE только при всех заданных параметрах
		if (!empty($data['systolic_blood_pressure']) && !empty($data['Person_Age']) && !empty($data['total_cholesterol']) && !empty($data['Sex_id']) && !empty($data['EvnPLDisp_IsSmoking'])) {
			$query = "
				select
					ScoreValues_Values as \"ScoreValues_Values\"
				from
					v_ScoreValues 
				where
					(cast (:systolic_blood_pressure as decimal) BETWEEN COALESCE(ScoreValues_MinPress,0) and COALESCE(ScoreValues_MaxPress,900)) and
					(:Person_Age BETWEEN COALESCE(ScoreValues_AgeFrom,0) and COALESCE(ScoreValues_AgeTo,900)) and
					(cast (:total_cholesterol as decimal) BETWEEN COALESCE(ScoreValues_MinChol,0) and COALESCE(ScoreValues_MaxChol,900)) and
					:Sex_id = COALESCE(Sex_id, CAST(:Sex_id as bigint)) and
					:EvnPLDisp_IsSmoking = ScoreValues_IsSmoke 
			";
			
			$result = $this->db->query($query, $data);
			
			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$scorevalue = $resp[0]['ScoreValues_Values'];
				}
			}
		}
		
		return array('success' => true, 'SCORE' => $scorevalue);
	}
	
	/**
	 *	Получение EvnVizitDispDop_id для услуги
	 */
	function getEvnVizitDispDopForEvnUsluga($data) {
		$query = "
			select 
				EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
			from
				v_EvnUslugaDispDop EUDD 
				left join v_EvnVizitDispDop EVDD  on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
			where
				EvnUslugaDispDop_id = :EvnUslugaDispDop_id
			limit 1
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnVizitDispDop_id']) ) {
				return $resp[0]['EvnVizitDispDop_id'];
			}
		}
		
		return true;
	}
	
	/**
	 *	Получение сурвейтайп для согласия
	 */
	function getSurveyTypeData($DopDispInfoConsent_id = null) {
		$query = "
			select 
				ST.SurveyType_id as \"SurveyType_id\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				STL.DispClass_id as \"DispClass_id\",
				EPDP.EvnPLDispProf_IsEndStage as \"EvnPLDispProf_IsEndStage\",
				to_char(EPLD.EvnPLDisp_setDT, 'DD.MM.YYYY') as \"EvnPLDisp_setDate\"
			from
				v_DopDispInfoConsent DDIC 
				inner join v_SurveyTypeLink STL  on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST  on ST.SurveyType_id = STL.SurveyType_id
				left join v_EvnPLDisp EPLD  on EPLD.EvnPLDisp_id = DDIC.EvnPLDisp_id
				left join v_EvnPLDispProf EPDP  on EPDP.EvnPLDispProf_id = DDIC.EvnPLDisp_id
			where
				DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
		";
		
		$res = $this->db->query($query, array('DopDispInfoConsent_id' => $DopDispInfoConsent_id));
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				return $resp[0];
			}
		}
		
		return false;
	}
	
	/**
	 *	Получение результатов
	 */
	function getRateData($RateType_SysNick, $EvnUsluga_id) {
		$query = "
			select
				rt.RateType_id as \"RateType_id\",
				rvt.RateValueType_SysNick as \"RateValueType_SysNick\",
				EURData.EvnUslugaRate_id as \"EvnUslugaRate_id\",
				EURData.Rate_id as \"Rate_id\"
			from
				v_RateType rt 
				left join RateValueType rvt  on rvt.RateValueType_id = rt.RateValueType_id
				LEFT JOIN LATERAL(
					select 
						eur.EvnUslugaRate_id, r.Rate_id
					from
						v_EvnUslugaRate eur 
						left join v_Rate r  on r.Rate_id = eur.Rate_id
					where r.RateType_id = rt.RateType_id and eur.EvnUsluga_id = :EvnUsluga_id
					limit 1
				) EURData ON true
			where
				RateType_SysNick = :RateType_SysNick
		";
		
		$res = $this->db->query($query, array(
			'RateType_SysNick' => $RateType_SysNick,
			'EvnUsluga_id' => $EvnUsluga_id
		));
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		
		return array();
	}
	
	/**
	 *	Получение результатов
	 */
	function getFormalizedInspectionParamsData($FormalizedInspectionParams_SysNick, $EvnUslugaDispDop_id) {
		$query = "
			select
				FIP.FormalizedInspectionParams_id as \"FormalizedInspectionParams_id\",
				FIP.FormalizedInspectionParams_SysNick as \"FormalizedInspectionParams_SysNick\",
				RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
				FIDATA.FormalizedInspection_id as \"FormalizedInspection_id\"
			from
				v_FormalizedInspectionParams FIP
				left join RateValueType RVT on RVT.RateValueType_id = FIP.RateValueType_id
				LEFT JOIN LATERAL (
					select
						FI.FormalizedInspection_id
					from
						v_FormalizedInspection FI
					where FI.FormalizedInspectionParams_id = FIP.FormalizedInspectionParams_id and FI.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
					limit 1
				) FIDATA on true
			where
				FIP.FormalizedInspectionParams_SysNick = :FormalizedInspectionParams_SysNick
		";
		$params = array(
			'FormalizedInspectionParams_SysNick' => $FormalizedInspectionParams_SysNick,
			'EvnUslugaDispDop_id' => $EvnUslugaDispDop_id
		);
		//exit(getDebugSQL($query, $params));
		$res = $this->db->query($query, $params);
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		
		return array();
	}
	
	/**
	 *	Получнеие списка профилей
	 */
	function loadLpuSectionProfileList($data) {
		$filter = "";
		
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and UCP.UslugaComplex_id = :UslugaComplex_id";
		}
		
		if (!empty($data['OrpDispSpec_id'])) {
			// услуги для специальностей одинаковые, можно выбрать top 1 по специальность и типу диспансеризации
			$filter .= " and UCP.UslugaComplex_id = (
				select UslugaComplex_id
				from v_SurveyTypeLink stl 
					inner join v_SurveyType st  on st.SurveyType_id = stl.SurveyType_id
				where st.OrpDispSpec_id = :OrpDispSpec_id
					and stl.DispClass_id = :DispClass_id
					and (stl.SurveyTypeLink_endDate >= :onDate or stl.SurveyTypeLink_endDate IS NULL)
					and (stl.SurveyTypeLink_begDate <= :onDate or stl.SurveyTypeLink_begDate IS NULL)
				limit 1
			)";
		}

		// если связок на дату нет, то выдаём все профиля (refs #79926)
		$resp = $this->queryResult("
			select 
				UCP.UslugaComplexProfile_id as \"UslugaComplexProfile_id\"
			from
				v_UslugaComplexProfile UCP 
			where
				UCP.DispClass_id = :DispClass_id
				and (UCP.UslugaComplexProfile_endDate >= :onDate or UCP.UslugaComplexProfile_endDate IS NULL)
				and (UCP.UslugaComplexProfile_begDate <= :onDate or UCP.UslugaComplexProfile_begDate IS NULL)
				{$filter}
			limit 1
		", $data);

		if (!empty($resp[0]['UslugaComplexProfile_id'])) {
			$query = "
				select
					LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
					v_LpuSectionProfile LSP 
					inner join v_UslugaComplexProfile UCP  on UCP.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				where
					UCP.DispClass_id = :DispClass_id
					and (UCP.UslugaComplexProfile_endDate >= :onDate or UCP.UslugaComplexProfile_endDate IS NULL)
					and (UCP.UslugaComplexProfile_begDate <= :onDate or UCP.UslugaComplexProfile_begDate IS NULL)
					{$filter}
			";
		} else {
			$query = "
				select
					LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
					v_LpuSectionProfile LSP 
				where
					(LSP.LpuSectionProfile_endDT >= :onDate or LSP.LpuSectionProfile_endDT IS NULL)
					and (LSP.LpuSectionProfile_begDT <= :onDate or LSP.LpuSectionProfile_begDT IS NULL)
			";
		}
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		
		return false;
	}
	
	/**
	 *	Получение списка специальностей
	 */
	function loadMedSpecOmsList($data) {
		$filter = "";
		
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and UCM.UslugaComplex_id = :UslugaComplex_id";
		}

		if (!empty($data['LpuSectionProfile_id'])) {
			$filter .= " and exists(
				select 
					LpuSectionProfileMedSpec_id
				from
					fed.v_LpuSectionProfileMedSpec lpms 
					inner join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_fedid = lpms.LpuSectionProfile_id
				where
					lpms.MedSpec_id = mso.MedSpec_id
					and lsp.LpuSectionProfile_id = :LpuSectionProfile_id
					and (lpms.LpuSectionProfileMedSpec_endDT >= :onDate or lpms.LpuSectionProfileMedSpec_endDT IS NULL)
					and (lpms.LpuSectionProfileMedSpec_begDT <= :onDate or lpms.LpuSectionProfileMedSpec_begDT IS NULL)
			)";
		}
		
		if (!empty($data['OrpDispSpec_id'])) {
			// услуги для специальностей одинаковые, можно выбрать top 1 по специальность и типу диспансеризации
			$filter .= " and UCM.UslugaComplex_id = (
				select UslugaComplex_id
				from v_SurveyTypeLink stl 
					inner join v_SurveyType st  on st.SurveyType_id = stl.SurveyType_id
				where st.OrpDispSpec_id = :OrpDispSpec_id
					and stl.DispClass_id = :DispClass_id
					and (stl.SurveyTypeLink_endDate >= :onDate or stl.SurveyTypeLink_endDate IS NULL)
					and (stl.SurveyTypeLink_begDate <= :onDate or stl.SurveyTypeLink_begDate IS NULL)
				limit 1
			)";
		}

		// если связок на дату нет, то выдаём все специальности (refs #79926)
		$resp = $this->queryResult("
			select 
				UCM.UslugaComplexMedSpec_id as \"UslugaComplexMedSpec_id\"
			from
				v_MedSpecOms MSO 
				inner join v_UslugaComplexMedSpec UCM  on UCM.MedSpecOms_id = MSO.MedSpecOms_id
			where
				UCM.DispClass_id = :DispClass_id
				and (UCM.UslugaComplexMedSpec_endDate >= :onDate or UCM.UslugaComplexMedSpec_endDate IS NULL)
				and (UCM.UslugaComplexMedSpec_begDate <= :onDate or UCM.UslugaComplexMedSpec_begDate IS NULL)
				{$filter}
			limit 1
		", $data);

		if (!empty($resp[0]['UslugaComplexMedSpec_id'])) {
			$query = "
				select
					MSO.MedSpecOms_id as \"MedSpecOms_id\",
					MSO.MedSpecOms_Code as \"MedSpecOms_Code\",
					MSO.MedSpecOms_Name as \"MedSpecOms_Name\"
				from
					v_MedSpecOms MSO 
					inner join v_UslugaComplexMedSpec UCM  on UCM.MedSpecOms_id = MSO.MedSpecOms_id
				where
					UCM.DispClass_id = :DispClass_id
					and (UCM.UslugaComplexMedSpec_endDate >= :onDate or UCM.UslugaComplexMedSpec_endDate IS NULL)
					and (UCM.UslugaComplexMedSpec_begDate <= :onDate or UCM.UslugaComplexMedSpec_begDate IS NULL)
					{$filter}
			";
		} else {
			$query = "
				select
					MSO.MedSpecOms_id as \"MedSpecOms_id\",
					MSO.MedSpecOms_Code as \"MedSpecOms_Code\",
					MSO.MedSpecOms_Name as \"MedSpecOms_Name\"
				from
					v_MedSpecOms MSO 
			";
		}
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		
		return false;
	}

	/**
	 *	Некоторые проверки, выполняемые до сохранения услуги по доп. диспансеризации
	 *	Возвращает текст ошибки или true, если все корректно
	 */
	function beforeSaveEvnUslugaDispDop($data) {
		if ( empty($data['Lpu_uid']) && !empty($data['EvnUslugaDispDop_didDate']) ) {
			if ( empty($data['MedPersonal_id']) ) {
				return 'Поле "Врач" обязательно для заполнения';
			}

			if ( empty($data['LpuSection_id']) ) {
				return 'Поле "Отделение" обязательно для заполнения';
			}
		}

		return true;
	}
}
?>