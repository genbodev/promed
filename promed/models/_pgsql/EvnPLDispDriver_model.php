<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispDriver_model - модель для работы с талонами по диспансеризации водителей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 - 2016 Swan Ltd.
*/

require_once('EvnPLDispAbstract_model.php');

class EvnPLDispDriver_model extends EvnPLDispAbstract_model
{
	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispDriver_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона освидетельствования';
		$arr['evnpldispdriver_num'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDriver_Num',
			'label' => 'Номер',
			'save' => '',
			'type' => 'id'
		);
		$arr['evnpldispdriver_isfinish'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDriver_IsFinish',
			'label' => 'Медицинское обследование закончено',
			'save' => '',
			'type' => 'id'
		);
		$arr['resultdispdriver_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDispDriver_id',
			'label' => 'Результат',
			'save' => '',
			'type' => 'id'
		);
		$arr['evnpldispdriver_medser'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDriver_MedSer',
			'label' => 'Заключение - серия',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['evnpldispdriver_mednum'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDriver_MedNum',
			'label' => 'Заключение - номер',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['evnpldispdriver_meddate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDriver_MedDate',
			'label' => 'Заключение - дата',
			'save' => '',
			'type' => 'date'
		);
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_id',
			'label' => 'Направление',
			'save' => '',
			'type' => 'id'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 190;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispDriver';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLDispDriverIsFinish($id, $value = null) {
		if ($value == 2) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as "count"
				from v_EvnUslugaDispDop EUDD 
					inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id and
					R.Rate_ValuesIs is not null and 
					ST.SurveyType_Code in(155,156,157,158)
			', array(
				'EvnPLDispDriver_id' => $id
			));
			if (empty($test) || $test < 4) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования водителя не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей');
			}
		}
		if ($value != 2) {
			$this->_updateAttribute($id, 'evnpldispdriver_medser', null);
			$this->_updateAttribute($id, 'evnpldispdriver_mednum', null);
			$this->_updateAttribute($id, 'evnpldispdriver_meddate', null);
			$this->_updateAttribute($id, 'resultdispdriver_id', null);
		}
		return $this->_updateAttribute($id, 'evnpldispdriver_isfinish', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateResultDispDriverid($id, $value = null) {
		if ($value == 1) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as "count"
				from v_EvnUslugaDispDop EUDD 
					inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id and
					R.Rate_ValuesIs = 1 and 
					ST.SurveyType_Code in(155,156,157,158)
			', array(
				'EvnPLDispDriver_id' => $id
			));
			if (empty($test) || $test < 4) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования водителя не может быть закончен с результатом «Отсутствие медицинских противопоказаний к управлению ТС», если результат осмотра хотя бы одного врача не заполнен или выявил противопоказания к управлению ТС');
			}
		}
		return $this->_updateAttribute($id, 'resultdispdriver_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedSer($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispdriver_medser', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedNum($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispdriver_mednum', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedDate($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispdriver_meddate', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateConsDT($id, $value = null)
	{
		$this->_updateAttribute($id, 'setdt', $value);
		return $this->_updateAttribute($id, 'consdt', $value);
	}
		
	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPLDispDriver($data) {
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
			from p_EvnPLDispDriver_del(
				EvnPLDispDriver_id := :EvnPLDispDriver_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}
	
	/**
	 *	Сохранение согласия
	 */	
	function saveDopDispInfoConsent($data) {

		// Стартуем транзакцию
		$this->db->trans_begin();

		$EvnPLDispDopIsNew = false;

		if ($data['EvnPLDispDriver_IsMobile']) { $data['EvnPLDispDriver_IsMobile'] = 2; } else { $data['EvnPLDispDriver_IsMobile'] = 1;	}
		
		$personage = $this->getFirstResultFromQuery("
			select COALESCE(dbo.Age2(Person_Birthday, dbo.tzGetDate()),0) as \"Person_Age\"
			from v_PersonState 
			where Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id']
		));
		
		if ($personage < 15) {
			return array('Error_Msg' => 'Возраст пациента меньше 15 лет. Создание случая медицинского освидетельствования водителя невозможно');
		}
		
		if (empty($data['EvnPLDispDriver_id'])) {

			$EvnPLDispDopIsNew = true;

			// добавляем новый талон ДД
			$query = "
				select 
					EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\"
				from p_EvnPLDispDriver_ins(
					EvnPLDispDriver_id := :EvnPLDispDriver_id, 
					MedStaffFact_id := :MedStaffFact_id, 
					EvnPLDispDriver_pid := null, 
					EvnPLDispDriver_rid := null, 
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id,
					EvnPLDispDriver_setDT := :EvnPLDispDriver_setDate, 
					EvnPLDispDriver_disDT := null, 
					EvnPLDispDriver_didDT := null, 
					Morbus_id := null, 
					EvnPLDispDriver_IsSigned := null, 
					pmUser_signID := null, 
					EvnPLDispDriver_signDT := null, 
					EvnPLDispDriver_VizitCount := null, 
					EvnPLDispDriver_IsFinish := 1, 
					Person_Age := null, 
					AttachType_id := 2, 
					Lpu_aid := null, 
					EvnPLDispDriver_consDT := :EvnPLDispDriver_consDate,
					EvnPLDispDriver_IsMobile := :EvnPLDispDriver_IsMobile,
					Lpu_mid := :Lpu_mid,
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					EvnPLDispDriver_fid := :EvnPLDispDriver_fid,
					EvnPLDispDriver_Num := :EvnPLDispDriver_Num,
					ResultDispDriver_id := null,
					EvnPLDispDriver_MedSer := null,
					EvnPLDispDriver_MedNum := null,
					EvnPLDispDriver_MedDate := null,
					EvnDirection_id := :EvnDirection_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
				'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnPLDispDriver_setDate' => $data['EvnPLDispDriver_consDate'],
				'EvnPLDispDriver_consDate' => $data['EvnPLDispDriver_consDate'],
				'EvnPLDispDriver_IsMobile' => $data['EvnPLDispDriver_IsMobile'],
				'Lpu_mid' => $data['Lpu_mid'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnPLDispDriver_fid' => $data['EvnPLDispDriver_fid'],
				'EvnPLDispDriver_Num' => $this->getEvnPLDispDriverNumber($data),
				'EvnDirection_id' => !empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null,
				'pmUser_id' => $data['pmUser_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (isset($resp[0]['EvnPLDispDriver_id'])) {
					$data['EvnPLDispDriver_id'] = $resp[0]['EvnPLDispDriver_id'];
				} else {
					$this->db->trans_rollback();
					return $resp; // иначе выдаем.. там видимо ошибка
				}
			}
		}
		
		$this->load->model('EvnDiagDopDisp_model', 'evndiagdopdisp');
		
		// При наличии карты дисп. учета пациента с периодом действия включающим создаваемую карту ДВН/ПОВН (по дате инф. согласия) добавить диагноз с карты дисп. учета. (refs #22327)
		$query = "
			select
				pd.Diag_id as \"Diag_id\",
				to_char(pd.PersonDisp_begDate, 'DD.MM.YYYY') as \"PersonDisp_begDate\"
			from
				v_PersonDisp pd 
				inner join v_Diag d on d.Diag_id = pd.Diag_id
				left join v_ProfileDiag pdiag on pdiag.Diag_id = d.Diag_pid
			where
				pd.Person_id = :Person_id
				and (pd.PersonDisp_begDate <= :EvnPLDispDriver_consDate OR pd.PersonDisp_begDate IS NULL)
				and (pd.PersonDisp_endDate >= :EvnPLDispDriver_consDate OR pd.PersonDisp_endDate IS NULL)
				and pdiag.ProfileDiagGroup_id IS NULL
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDriver_consDate' => $data['EvnPLDispDriver_consDate'],
			'Person_id' => $data['Person_id']
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Diag_id'])) {
				$data['EvnPLDisp_id'] = $data['EvnPLDispDriver_id'];
                foreach ($resp as $item){
					$data['EvnDiagDopDisp_setDate'] = !empty($item['PersonDisp_begDate'])?date('Y-m-d', strtotime($item['PersonDisp_begDate'])):null;
                    $this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['Diag_id']);
                }
			}
		}
		
		// сохраняем данные по информир. добр. согласию для EvnPLDispDriver_id = $data['EvnPLDispDriver_id']
		ConvertFromWin1251ToUTF8($data['DopDispInfoConsentData']);
		$items = json_decode($data['DopDispInfoConsentData'], true);
		$itemsCount = 0;
		
		// Массив идентификаторов DopDispInfoConsent_id, которые надо удалить
		// Выполняться должно после удаления посещений, т.к. в посещениях сейчас есть ссылка на DopDispInfoConsent
		$DopDispInfoConsentToDel = array();

		// Список идентификаторов DopDispInfoConsent_id, которые 
		// https://redmine.swan.perm.ru/issues/29017
		$DopDispInfoConsentList = array();
		
		foreach($items as $item) {			
			if ( (!empty($item['DopDispInfoConsent_IsAgree']) && $item['DopDispInfoConsent_IsAgree'] == '1') || $item['DopDispInfoConsent_IsAgree'] === true ) {
				$item['DopDispInfoConsent_IsAgree'] = 2;
			} else {
				$item['DopDispInfoConsent_IsAgree'] = 1;
			}
			
			if ( (!empty($item['DopDispInfoConsent_IsEarlier']) && $item['DopDispInfoConsent_IsEarlier'] == '1') || $item['DopDispInfoConsent_IsEarlier'] === true ) {
				$item['DopDispInfoConsent_IsEarlier'] = 2;
			} else {
				$item['DopDispInfoConsent_IsEarlier'] = 1;
			}
			
			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispDriver_id'], $item['SurveyTypeLink_id']);
			
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$DopDispInfoConsentList[] = $item['DopDispInfoConsent_id'];
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}
			
			// если убирают согласие для удалённого SurveyTypeLink, то удаляем его из DopDispInfoConsent. (refs #21573)
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0 && !empty($item['SurveyTypeLink_IsDel']) && $item['SurveyTypeLink_IsDel'] == '2' && $item['DopDispInfoConsent_IsAgree'] == 1) {
				// Удаление перенесено 
				$DopDispInfoConsentToDel[] = $item['DopDispInfoConsent_id'];
			}
			else {
				if (empty($item['SurveyTypeLink_id'])) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => 'Ошибка при сохранении информированного добровольного согласия (отсутсвует ссылка на SurveyTypeLink)'
					);
				}
				
				$query = "
					select 
                    	DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Message\"
                    from {$proc}(
						DopDispInfoConsent_id := :DopDispInfoConsent_id, 
						EvnPLDisp_id := :EvnPLDispDriver_id, 
						DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
						DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
						SurveyTypeLink_id := :SurveyTypeLink_id, 
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
					'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
					'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
					'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');

					if ( is_array($res) && count($res) > 0 ) {
						if ( !empty($res[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $res[0]['Error_Msg']
							);
						}

						if ( !in_array($res[0]['DopDispInfoConsent_id'], $DopDispInfoConsentList) ) {
							$DopDispInfoConsentList[] = $res[0]['DopDispInfoConsent_id'];
						}
					}
				}
				else {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
					);
				}
			}
		}

		if ( $EvnPLDispDopIsNew === false )  {
			// Обновляем дату EvnPLDispDriver_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select
					 EvnPLDispDriver_pid as \"EvnPLDispDriver_pid\"
					-- ,EvnPLDispDriver_rid as \"EvnPLDispDriver_rid\"
					,Lpu_id as \"Lpu_id\"
					,Server_id as \"Server_id\"
					,PersonEvn_id as \"PersonEvn_id\"
					,to_char(EvnPLDispDriver_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDriver_setDT\"
					,to_char(EvnPLDispDriver_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDriver_disDT\"
					,to_char(EvnPLDispDriver_didDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDriver_didDT\"
					,Morbus_id as \"Morbus_id\"
					,EvnPLDispDriver_IsSigned as \"EvnPLDispDriver_IsSigned\"
					,EvnPLDispDriver_IndexRep as \"EvnPLDispDriver_IndexRep\"
					,EvnPLDispDriver_IndexRepInReg as \"EvnPLDispDriver_IndexRepInReg\"
					,pmUser_signID as \"pmUser_signID\"
					,EvnPLDispDriver_signDT as \"EvnPLDispDriver_signDT\"
					,EvnPLDispDriver_IsFinish as \"EvnPLDispDriver_IsFinish\"
					,Person_Age as \"Person_Age\"
					,AttachType_id as \"AttachType_id\"
					,Lpu_aid as \"Lpu_aid\"
					,DispClass_id as \"DispClass_id\"
					,EvnPLDispDriver_Num as \"EvnPLDispDriver_Num\"
					,ResultDispDriver_id as \"ResultDispDriver_id\"
					,EvnPLDispDriver_MedSer as \"EvnPLDispDriver_MedSer\"
					,EvnPLDispDriver_MedNum as \"EvnPLDispDriver_MedNum\"
					,EvnPLDispDriver_MedDate as \"EvnPLDispDriver_MedDate\"
					,EvnDirection_id as \"EvnDirection_id\"
				from v_EvnPLDispDriver 
				where EvnPLDispDriver_id = :EvnPLDispDriver_id
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 ) {
					$resp[0]['EvnPLDispDriver_setDT'] = $data['EvnPLDispDriver_consDate'];
					$resp[0]['EvnPLDispDriver_consDT'] = $data['EvnPLDispDriver_consDate'];
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					$resp[0]['EvnPLDispDriver_IsMobile'] = $data['EvnPLDispDriver_IsMobile'];
					$resp[0]['Lpu_mid'] = $data['Lpu_mid'];
					$resp[0]['PayType_id'] = $data['PayType_id'];
					$resp[0]['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

					$query = "
						select 
							EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Message\"
						from p_EvnPLDispDriver_upd(
							EvnPLDispDriver_id := :EvnPLDispDriver_id
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "," . $key . " := :" . $key ." \n" ;
					}

					$query .= "
						)
					";

					$resp[0]['EvnPLDispDriver_id'] = $data['EvnPLDispDriver_id'];

					$result = $this->db->query($query, $resp[0]);
					
					if ( is_object($result) ) {
						$resp = $result->result('array');

						if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return $resp;
						}
					}
				}
			}

			// Чистим атрибуты и услуги
			$attrArray = array(
				 'EvnVizitDispDop' // Услуги с отказом и посещения
			);

			if ( $itemsCount == 0 ) {
				$attrArray[] = 'EvnDiagDopDisp'; // Ранее известные имеющиеся заболевания, впервые выявленные заболевания
				$attrArray[] = 'HeredityDiag'; // Наследственность по заболеваниям
			}

			foreach ( $attrArray as $attr ) {
				$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispDriver_id'], $data['pmUser_id']);

				if ( !empty($deleteResult) ) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
					);
				}
			}
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		);
	}
	
	/**
	 * выбрать всё (согласие)
	 */
	function checkAllDopDispInfoConsent($data) {
		
		$query = "
			select 
				COALESCE(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as \"DopDispInfoConsent_id\",
				DDIC.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
				DDIC.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\"
			from v_SurveyTypeLink  STL
			left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDisp_id
			where 
				STL.DispClass_id = :DispClass_id and 
				COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1
			group by STL.SurveyType_id, STL.SurveyTypeLink_id, DDIC.DopDispInfoConsent_IsAgree, DDIC.DopDispInfoConsent_IsEarlier
			
		";
		$res = $this->queryResult($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispDriver_id'],
			'DispClass_id' => 26
		));
		
		foreach ($res as $row) {
			if (!empty($row['DopDispInfoConsent_id']) && $row['DopDispInfoConsent_id'] > 0) {
				$DopDispInfoConsent_id = $row['DopDispInfoConsent_id'];
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$DopDispInfoConsent_id = null;
				$proc = 'p_DopDispInfoConsent_ins';
			}
			
			$query = "
				select 
					DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\"
				from {$proc}(
					DopDispInfoConsent_id := :DopDispInfoConsent_id, 
					EvnPLDisp_id := :EvnPLDispDriver_id, 
					DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
					DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
					SurveyTypeLink_id := :SurveyTypeLink_id, 
					pmUser_id := :pmUser_id
				)
			";
			$this->db->query($query, array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
				'DopDispInfoConsent_id' => $DopDispInfoConsent_id,
				'DopDispInfoConsent_IsAgree' => $data['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $data['DopDispInfoConsent_IsEarlier'],
				'SurveyTypeLink_id' => $row['SurveyTypeLink_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		);
	}
	
	/**
	 * Обновление данных по информир. добр. согласию (штучно)
	 */
	function updateDopDispInfoConsent($data) {
		
		if ($data['DopDispInfoConsent_id'] > 0) {
			$SurveyTypeLink_id = $this->getFirstResultFromQuery('
				select SurveyTypeLink_id as "SurveyTypeLink_id"
				from v_DopDispInfoConsent  
				where DopDispInfoConsent_id = :DopDispInfoConsent_id
			', array(
				'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id']
			));
		
			if (!$SurveyTypeLink_id) {
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при выполнении обновления данных'
				);
			}
		}
		else {
			$SurveyTypeLink_id = -$data['DopDispInfoConsent_id'];
			// проверим, не сохраняли ли ранее
			$ddic = $this->getFirstResultFromQuery('
				select DopDispInfoConsent_id as "DopDispInfoConsent_id"
				from v_DopDispInfoConsent  
				where SurveyTypeLink_id = :SurveyTypeLink_id and EvnPLDisp_id = :EvnPLDisp_id
			', array(
				'SurveyTypeLink_id' => $SurveyTypeLink_id,
				'EvnPLDisp_id' => $data['EvnPLDispDriver_id']
			));
			if ($ddic && $ddic > 0) {
				$data['DopDispInfoConsent_id'] = $ddic;
			}
		}
		
		if (!empty($data['DopDispInfoConsent_id']) && $data['DopDispInfoConsent_id'] > 0) {
			$DopDispInfoConsent_id = $data['DopDispInfoConsent_id'];
			$proc = 'p_DopDispInfoConsent_upd';
		} else {
			$DopDispInfoConsent_id = null;
			$proc = 'p_DopDispInfoConsent_ins';
		}
		
		$query = "
			select 
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
			from {$proc}(
				DopDispInfoConsent_id := :DopDispInfoConsent_id, 
				EvnPLDisp_id := :EvnPLDispDriver_id, 
				DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
				DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
				SurveyTypeLink_id := :SurveyTypeLink_id, 
				pmUser_id := :pmUser_id,
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
			'DopDispInfoConsent_id' => $DopDispInfoConsent_id,
			'DopDispInfoConsent_IsAgree' => $data['DopDispInfoConsent_IsAgree'],
			'DopDispInfoConsent_IsEarlier' => $data['DopDispInfoConsent_IsEarlier'],
			'SurveyTypeLink_id' => $SurveyTypeLink_id,
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'
			);
		}
	}
	
	/**
	 *	Получение идентификатора из списка добровольного информированного согласия по $SurveyTypeLink_id
	 */	
	function getDopDispInfoConsentForSurveyTypeLink($EvnPLDisp_id, $SurveyTypeLink_id) {
		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_DopDispInfoConsent 
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyTypeLink_id = :SurveyTypeLink_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $EvnPLDisp_id,
			'SurveyTypeLink_id' => $SurveyTypeLink_id
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['DopDispInfoConsent_id'];
			}
		}
		
		return null;
	}

	/**
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispDriverViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id'],
			'EvnPLDispDriver_id' => $data['EvnPLDisp_id']
		);
		$accessType = "'edit' as \"accessType\",";

		$query = "
			select
				EPLDD.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				case
					when EPLDD.MedStaffFact_id is not null then COALESCE(l.Lpu_Nick || ' ', '') || COALESCE(ls.LpuSection_Name || ' ', '') || COALESCE(msf.Person_Fio, '') 
					else COALESCE(l.Lpu_Nick || ' ', '') || COALESCE(pu.pmUser_Name, '')
				end as \"AuthorInfo\",
				'EvnPLDispDriver' as \"Object\",
				EPLDD.DispClass_id as \"DispClass_id\",
				EPLDD.Person_id as \"Person_id\",
				EPLDD.EvnPLDispDriver_IsSigned as \"EvnPLDispDriver_IsSigned\",
				EPLDD.PersonEvn_id as \"PersonEvn_id\",
				EPLDD.Server_id as \"Server_id\",
				dc.DispClass_Code as \"DispClass_Code\",
				dc.DispClass_Name as \"DispClass_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				{$accessType}
				EPLDD.PayType_id as \"PayType_id\",
				pt.PayType_Name as \"PayType_Name\",
				to_char(EPLDD.EvnPLDispDriver_setDT, 'DD.MM.YYYY') as \"EvnPLDispDriver_setDate\",
				to_char(EPLDD.EvnPLDispDriver_consDT, 'DD.MM.YYYY') as \"EvnPLDispDriver_consDate\",
				EPLDD.EvnPLDispDriver_IsFinish as \"EvnPLDispDriver_IsFinish\",
				IsFinish.YesNo_Name as \"EvnPLDispDriver_IsFinish_Name\",
				EPLDD.EvnPLDispDriver_Num as \"EvnPLDispDriver_Num\",
				EPLDD.ResultDispDriver_id as \"ResultDispDriver_id\",
				RDD.ResultDispDriver_Name as \"ResultDispDriver_Name\",
				EPLDD.EvnPLDispDriver_MedSer as \"EvnPLDispDriver_MedSer\",
				EPLDD.EvnPLDispDriver_MedNum as \"EvnPLDispDriver_MedNum\",
				to_char(EPLDD.EvnPLDispDriver_MedDate, 'DD.MM.YYYY') as \"EvnPLDispDriver_MedDate\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				eqi.ElectronicQueueInfo_IsOff as \"ElectronicQueueInfo_IsOff\",
				case when eqil.ElectronicService_id = et.ElectronicService_id then 2 else 1 end as \"ElectronicQueueInfo_IsLast\",
				case when ddic.SurveyTypeLink_id is null then 'checked' else '' end as \"EvnPLDispDriver_allChecked\"
			from
				v_EvnPLDispDriver EPLDD 
				left join v_Lpu l on l.Lpu_id = EPLDD.Lpu_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = EPLDD.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu on pu.pmUser_id = EPLDD.pmUser_updID
				left join v_DispClass dc on dc.DispClass_id = EPLDD.DispClass_id
				left join v_PayType pt on pt.PayType_id = EPLDD.PayType_id
				left join v_YesNo IsFinish on IsFinish.YesNo_id = EPLDD.EvnPLDispDriver_IsFinish
				left join v_ResultDispDriver RDD on RDD.ResultDispDriver_id = EPLDD.ResultDispDriver_id
				left join v_ElectronicTalon et on et.EvnDirection_id = epldd.EvnDirection_id
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
				left join v_MedServiceElectronicQueue mseq on et.ElectronicService_id = mseq.ElectronicService_id
				left join v_MedServiceMedPersonal msp on msp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
				LEFT JOIN LATERAL (
					select STL.SurveyTypeLink_id
					from v_SurveyTypeLink  STL
					left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = EPLDD.EvnPLDispDriver_id
					where 
						STL.DispClass_id = EPLDD.DispClass_id and 
						COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 and
						(DDIC.DopDispInfoConsent_id is null or DDIC.DopDispInfoConsent_IsAgree != 2)
					limit 1
				) ddic ON true
				LEFT JOIN LATERAL (
					select
						es.ElectronicService_id
					from
						v_MedServiceElectronicQueue mseq 
						inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
						inner join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
					where
						msmp.MedService_id = msp.MedService_id
					order by 
						es.ElectronicService_Num desc
					limit 1
				) eqil ON true
			where
				EPLDD.EvnPLDispDriver_id = :EvnPLDisp_id
		";
		
		$result = $this->db->query($query, $queryParams);
		
        if (is_object($result)) {
			$resp = $result->result('array');
			// данные для чекбоксов
			$resp[0]['DriverCategory'] = $this->getDriverCategory($queryParams);
			$resp[0]['DriverMedicalClose'] = $this->getDriverMedicalClose($queryParams);
			$resp[0]['DriverMedicalIndication'] = $this->getDriverMedicalIndication($queryParams);
			return $resp;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 *	Получение данных для формы редактирования карты
	 */
	function loadEvnPLDispDriverEditForm($data)
	{
		$accessType = '
			case
				when EPLDD.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDD.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and COALESCE(EPLDD.EvnPLDispDriver_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';
		
		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EPLDD.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				EPLDD.EvnPLDispDriver_IsSigned as \"EvnPLDispDriver_IsSigned\",
				COALESCE(EPLDD.EvnPLDispDriver_IsPaid, 1) as \"EvnPLDispDriver_IsPaid\",
				COALESCE(EPLDD.EvnPLDispDriver_IndexRep, 0) as \"EvnPLDispDriver_IndexRep\",
				EPLDD.EvnPLDispDriver_fid as \"EvnPLDispDriver_fid\",
				EPLDD.Person_id as \"Person_id\",
				EPLDD.PersonEvn_id as \"PersonEvn_id\",
				EPLDD.EvnDirection_id as \"EvnDirection_id\",
				COALESCE(EPLDD.DispClass_id, 26) as \"DispClass_id\",
				EPLDD.PayType_id as \"PayType_id\",
				EPLDD.EvnPLDispDriver_pid as \"EvnPLDispDriver_pid\",
				to_char(EPLDD.EvnPLDispDriver_setDate, 'DD.MM.YYYY') as \"EvnPLDispDriver_setDate\",
				to_char(EPLDD.EvnPLDispDriver_disDate, 'DD.MM.YYYY') as \"EvnPLDispDriver_disDate\",
				to_char(EPLDD.EvnPLDispDriver_consDT, 'DD.MM.YYYY') as \"EvnPLDispDriver_consDate\",
				EPLDD.Server_id as \"Server_id\",
				case when EPLDD.EvnPLDispDriver_IsMobile = 2 then 1 else 0 end as \"EvnPLDispDriver_IsMobile\",
				EPLDD.Lpu_mid as \"Lpu_mid\",
				EPLDD.EvnPLDispDriver_IsFinish as \"EvnPLDispDriver_IsFinish\",
				EPLDD.EvnPLDispDriver_Num as \"EvnPLDispDriver_Num\",
				EPLDD.ResultDispDriver_id as \"ResultDispDriver_id\",
				EPLDD.EvnPLDispDriver_MedSer as \"EvnPLDispDriver_MedSer\",
				EPLDD.EvnPLDispDriver_MedNum as \"EvnPLDispDriver_MedNum\",
				to_char(EPLDD.EvnPLDispDriver_MedDate, 'DD.MM.YYYY') as \"EvnPLDispDriver_MedDate\"
			FROM
				v_EvnPLDispDriver EPLDD 
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispDriver_id = :EvnPLDispDriver_id
			limit 1
		";
		
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']));
		
        if (is_object($result)) {
			$resp = $result->result('array');
			// данные для чекбоксов
			$resp[0]['DriverCategory'] = $this->getDriverCategory($data);
			$resp[0]['DriverMedicalClose'] = $this->getDriverMedicalClose($data);
			$resp[0]['DriverMedicalIndication'] = $this->getDriverMedicalIndication($data);
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Загрузка согласий
	 */	
	function loadDopDispInfoConsent($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispDriver_consDate' => $data['EvnPLDispDriver_consDate']
		);
		
		if (!empty($data['EvnPLDispDriver_consDate'])) {
			$filter .= "				
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDriver_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDriver_consDate)
			";
		}

		$query = "
			select
				COALESCE(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as \"DopDispInfoConsent_id\",
				MAX(DDIC.EvnPLDisp_id) as \"EvnPLDispDriver_id\",
				MAX(STL.SurveyTypeLink_id) as \"SurveyTypeLink_id\",
				COALESCE(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as \"SurveyTypeLink_IsNeedUsluga\",
				COALESCE(MAX(STL.SurveyTypeLink_IsDel), 1) as \"SurveyTypeLink_IsDel\",
				MAX(ST.SurveyType_Code) as \"SurveyType_Code\",
				MAX(ST.SurveyType_Name) as \"SurveyType_Name\",
				case WHEN MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\",
				case WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\",
				case when MAX(ST.SurveyType_Code) IN (49) then 0 else 1 end,
				STL.DispClass_id as \"DispClass_id\"
			from v_SurveyTypeLink STL 
				left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					select EvnUslugaDispDop_id
					from v_EvnUslugaDispDop 
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispDriver_id
						/*and COALESCE(EvnUslugaDispDop_IsVizitCode, 1) = 1*/
					limit 1
				) EUDD ON true
				" . implode(' ', $joinList) . "
			where 
				COALESCE(STL.DispClass_id, :DispClass_id) = :DispClass_id -- тип
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				" . $filter . "
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel, STL.DispClass_id
			/*order by case when MAX(ST.SurveyType_Code) IN (49) then 0 else 1 end, MAX(ST.SurveyType_Code)*/
			
		";
		// echo getDebugSql($query, $params); die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка исследований в талоне
	 * Входящие данные: $data['EvnPLDispDriver_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$query = "
			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				DDIC.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
				DDIC.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDT, 'DD.MM.YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				COALESCE(ep.EvnPrescr_pid, ed.EvnDirection_pid) as \"EvnPrescr_pid\",
				ep.PrescriptionType_id as \"PrescriptionType_id\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\",
				COALESCE(epd.EvnDirection_id, ed.EvnDirection_id) as \"EvnDirection_id\",
				EvnXmlData.EvnXml_id as \"EvnXml_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				STL.DispClass_id as \"DispClass_id\",
				COALESCE(ep.EvnPrescr_Count, 0) + COALESCE(ed.EvnDirection_Count,0) as \"DispDopDirections_Count\",
				ep.EvnPrescr_Count as \"EvnPrescr_Count\",
				ed.EvnDirection_Count as \"EvnDirection_Count\"
			from v_DopDispInfoConsent DDIC 
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL(
					select
						COUNT(*) OVER() as EvnPrescr_Count,
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from v_EvnPrescr 
					where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					limit 1
				) ep ON true
				LEFT JOIN LATERAL(
					select
						COUNT(*) OVER() as EvnDirection_Count,
						EvnDirection_id,
						EvnDirection_pid
					from v_EvnDirection edoa 
					where edoa.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and edoa.EvnStatus_id not in (12,13)
					limit 1
				) ed ON true
				left join v_EvnPrescrDirection epd on epd.EvnPrescr_id = ep.EvnPrescr_id
				LEFT JOIN LATERAL(
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setDT,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from
						v_EvnUslugaDispDop EUDD 
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispDriver_id
						/*and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1*/
					limit 1
				) EUDDData ON true
				LEFT JOIN LATERAL(
					select
						ex.EvnXml_id
					from
						v_EvnXml ex 
						inner join v_EvnUslugaPar eup on ex.Evn_id = eup.EvnUslugaPar_id
					where
						eup.EvnPrescr_id = ep.EvnPrescr_id
					limit 1
				) EvnXmlData ON true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
				and ST.SurveyType_Code = 2
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			union

			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				DDIC.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
				DDIC.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDT, 'DD.MM.YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				COALESCE(ep.EvnPrescr_pid, ed.EvnDirection_pid) as \"EvnPrescr_pid\",
				ep.PrescriptionType_id as \"PrescriptionType_id\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\",
				COALESCE(epd.EvnDirection_id, ed.EvnDirection_id) as \"EvnDirection_id\",
				EvnXmlData.EvnXml_id as \"EvnXml_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				STL.DispClass_id as \"DispClass_id\",
				COALESCE(ep.EvnPrescr_Count, 0) + COALESCE(ed.EvnDirection_Count,0) as \"DispDopDirections_Count\",
				ep.EvnPrescr_Count as \"EvnPrescr_Count\",
				ed.EvnDirection_Count as \"EvnDirection_Count\"
			from v_DopDispInfoConsent DDIC 
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL(
					select
						COUNT(*) OVER() as EvnPrescr_Count,
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from v_EvnPrescr 
					where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					limit 1
				) ep ON true
				LEFT JOIN LATERAL(
					select
						COUNT(*) OVER() as EvnDirection_Count,
						EvnDirection_id,
						EvnDirection_pid
					from v_EvnDirection edoa 
					where edoa.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and edoa.EvnStatus_id not in (12,13)
					limit 1
				) ed ON true
				left join v_EvnPrescrDirection epd on epd.EvnPrescr_id = ep.EvnPrescr_id
				LEFT JOIN LATERAL(
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setDT,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from v_EvnUslugaDispDop EUDD 
						left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						/*and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1*/
					limit 1
				) EUDDData ON true
				LEFT JOIN LATERAL(
					select
						ex.EvnXml_id
					from
						v_EvnXml ex 
						inner join v_EvnUslugaPar eup on ex.Evn_id = eup.EvnUslugaPar_id
					where
						eup.EvnPrescr_id = ep.EvnPrescr_id
					limit 1
				) EvnXmlData ON true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and ST.SurveyType_Code NOT IN (2, 49)
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";
		
		//echo getDebugSql($query, array('EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'])); die();
		$result = $this->db->query($query, array('EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']));
	
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
	 *	Удаление аттрибутов
	 */	
	function deleteAttributes($attr, $EvnPLDispDriver_id, $pmUser_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnVizitDispDop':
				$query = "
					select
						EVDD.EvnVizitDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD 
						inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDriver_id
						and COALESCE(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code not in (1, 2, 48)
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			// Специально для удаления анкетирования
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD 
						inner join v_SurveyTypeLink STL on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDriver_id
						and DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
						and COALESCE(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code = 2
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			case 'EvnDiagDopDisp':
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " 
					where EvnDiagDopDisp_pid = :EvnPLDispDriver_id
				";
			break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC 
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDriver_id
						and ST.SurveyType_Code NOT IN (1,48)
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " 
					where EvnPLDisp_id = :EvnPLDispDriver_id
				";
			break;
		}

		$result = $this->db->query($query, array('EvnPLDispDriver_id' => $EvnPLDispDriver_id));

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $array ) {
				$query = "
					select 
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Message\"
					from p_" . $attr . "_del
						@" . $attr . "_id = :id,
						" . (in_array($attr, array('EvnDiagDopDisp', 'EvnUslugaDispDop', 'EvnVizitDispDop')) ? "pmUser_id := :pmUser_id," : "") . "
				";
				$result = $this->db->query($query, array('id' => $array['id'], 'pmUser_id' => $pmUser_id));

				if ( !is_object($result) ) {
					return 'Ошибка при выполнении запроса к базе данных';
				}

				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
					return $res[0]['Error_Msg'];
				}
			}
		}

		return '';
	}
	
	
	
	/**
	 *	Сохранение карты
	 */	
    function saveEvnPLDispDriver($data) {
		
		if ($data['EvnPLDispDriver_IsFinish'] == 2) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as "count"
				from v_EvnUslugaDispDop EUDD 
					inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id and
					R.Rate_ValuesIs is not null and 
					ST.SurveyType_Code in(155,156,157,158)
			', array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
			));
			if (empty($test) || $test < 4) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования водителя не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей');
			}
		}
		
		if ($data['EvnPLDispDriver_IsFinish'] == 2 && $data['ResultDispDriver_id'] == 1) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as "count"
				from v_EvnUslugaDispDop EUDD 
					inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispDriver_id and
					R.Rate_ValuesIs = 1 and 
					ST.SurveyType_Code in(155,156,157,158)
			', array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
			));
			if (empty($test) || $test < 4) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования водителя не может быть закончен с результатом «Отсутствие медицинских противопоказаний к управлению ТС», если результат осмотра хотя бы одного врача не заполнен или выявил противопоказания к управлению ТС');
			}
		}

    	$proc = '';
    	if ( !isset($data['EvnPLDispDriver_id']) ) {
			$proc = 'p_EvnPLDispDriver_ins';
	    } else {
			$proc = 'p_EvnPLDispDriver_upd';
	    }

		$data['EvnPLDispDriver_setDate'] = $data['EvnPLDispDriver_consDate'];
		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
		
   		$query = "
			with cte as (
				select
					case when EvnPLDispDriver_IsSigned = 2 then 1 else EvnPLDispDriver_IsSigned end as EvnPLDispDriver_IsSigned,
					pmUser_signID,
					EvnPLDispDriver_signDT
				from
					v_EvnPLDispDriver
				where
					EvnPLDispDriver_id = :EvnPLDispDriver_id
			)
			select 
				EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnPLDispDriver_id := :EvnPLDispDriver_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnDirection_id := :EvnDirection_id,
				EvnPLDispDriver_setDT := :EvnPLDispDriver_setDate,
				EvnPLDispDriver_IsFinish := :EvnPLDispDriver_IsFinish,
				EvnPLDispDriver_consDT := :EvnPLDispDriver_consDate,
				DispClass_id := :DispClass_id,
				EvnPLDispDriver_IndexRep := :EvnPLDispDriver_IndexRep,
				EvnPLDispDriver_IndexRepInReg := :EvnPLDispDriver_IndexRepInReg,
				PayType_id := :PayType_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispDriver_Num := :EvnPLDispDriver_Num,
				ResultDispDriver_id := :ResultDispDriver_id,
				EvnPLDispDriver_MedSer := :EvnPLDispDriver_MedSer,
				EvnPLDispDriver_MedNum := :EvnPLDispDriver_MedNum,
				EvnPLDispDriver_MedDate := :EvnPLDispDriver_MedDate,
				AttachType_id := 2,
				EvnPLDispDriver_IsSigned := (select EvnPLDispDriver_IsSigned from cte),
				pmUser_signID := (select pmUser_signID from cte),
				EvnPLDispDriver_signDT := (select EvnPLDispDriver_signDT from cte),
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		
        if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) && !empty($resp[0]['EvnPLDispDriver_id'])) {
				$data['EvnPLDispDriver_id'] = $resp[0]['EvnPLDispDriver_id'];
				$this->saveCB($data);
			}
			return $resp;
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты)'));
		}
    } // saveEvnPLDispDriver()
	
	/**
	 * Сохранение чекбоксов
	 */
	function saveCB($data) {
		
		// --- DriverCategory ---
		$this->saveCBgr($data, 'DriverCategory');
		
		// --- DriverMedicalClose ---
		$this->saveCBgr($data, 'DriverMedicalClose');
		
		// --- DriverMedicalIndication ---
		$this->saveCBgr($data, 'DriverMedicalIndication');
	}
	
	/**
	 * сохранение флагов (из эмк)
	 */
	function saveCBemk($data) {
		$data[$data['type']] = $data['data'];
		$this->saveCBgr($data, $data['type']);
		return array('success' => true);
	}
	
	/**
	 * Сохранение чекбоксов
	 */
	function saveCBgr($data, $type) {
		$DriverData = explode(',', $data[$type]);
		$filter = empty($data[$type]) ? '' : "DCL.{$type}_id not in(".implode(',',$DriverData).") and ";
		$query = "
			do $$
				declare
					rec record;
				begin
					for rec in select {$type}Link_id from {$type}Link where EvnPLDispDriver_id = :EvnPLDispDriver_id loop
						perform p_{$type}Link_del(
							{$type}Link_id := rec.{$type}Link_id
						);
					end loop;
				end
			$$;
		";
		$res = $this->db->query($query,array('EvnPLDispDriver_id'=>$data['EvnPLDispDriver_id']));
		foreach($DriverData as $val) {
			$query="
				do $$
					begin
					if not exists (select {$type}Link_id from {$type}Link  where {$type}_id = :{$type}_id and EvnPLDispDriver_id = :EvnPLDispDriver_id) then
						perform p_{$type}Link_ins(
							{$type}Link_id := null,
							{$type}_id := :{$type}_id,
							EvnPLDispDriver_id := :EvnPLDispDriver_id,
							pmUser_id := :pmUser_id
						);
					end if;
					end
				$$;
			";
			$res = $this->db->query($query, array(
				'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id'],
				"{$type}_id" => !empty($val) ? $val : null,
				'pmUser_id'=>$data['pmUser_id']
			));
		}
		
	}
	
	/**
	 * Генерация номера
	 */
	function getEvnPLDispDriverNumber($data) {
		$query = "
			select 
				objectid as \"EvnPLDispDriver_Num\",
				'' as \"Error_Msg\"
			from xp_GenpmID(
				ObjectName := 'EvnPLDispDriver',
				Lpu_id := :Lpu_id,
				ObjectID := null
			)
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			$result = $result->result('array');
			if (count($result) && $result[0]['EvnPLDispDriver_Num']) {
				return $result[0]['EvnPLDispDriver_Num'];
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
	 *	Получение данных по инфекциям
	 */
	function getUslugaResult($data){
		
		$query = "			
			with tmp as (
				select
					EvnPrescr_id as \"EvnPrescr_id\",
					Lpu_id as \"Lpu_id\"
				from
					v_EvnPrescr 
				where
					DopDispInfoConsent_id = :DopDispInfoConsent_id
				limit 1
            )

			select 
				to_char(eup.EvnUslugaPar_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				eup.EvnUslugaPar_setTime as \"EvnUslugaDispDop_didTime\",
				to_char(eup.EvnUslugaPar_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_disDate\",
				eup.EvnUslugaPar_setTime as \"EvnUslugaDispDop_disTime\",
				case when eup.Lpu_id = tmp.Lpu_id then 1 else 3 end as \"ExaminationPlace_id\",
				eup.Lpu_id as \"Lpu_uid\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				msf.MedSpecOms_id as \"MedSpecOms_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				eup.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				null as \"Diag_id\"
			from v_EvnPrescrDirection epd 
				inner join v_EvnUslugaPar eup on eup.EvnDirection_id = epd.EvnDirection_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = eup.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			where
				epd.EvnPrescr_id = tmp.EvnPrescr_id
				
			union all

			select 
				to_char(epl.EvnVizitPL_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				epl.EvnVizitPL_setTime as \"EvnUslugaDispDop_didTime\",
				to_char(epl.EvnVizitPL_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_disDate\",
				epl.EvnVizitPL_setTime as \"EvnUslugaDispDop_disTime\",
				case when epl.Lpu_id = COALESCE(@Lpu_id,ed.Lpu_id) then 1 else 3 end as \"ExaminationPlace_id\",
				epl.Lpu_id as \"Lpu_uid\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				msf.MedSpecOms_id as \"MedSpecOms_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				epl.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				epl.Diag_id as \"Diag_id\"
			from v_EvnDirection ed 
				left join v_EvnPrescrDirection epd on epd.EvnPrescr_id = @EvnPrescr_id
				inner join v_EvnVizitPL epl on epl.EvnDirection_id = COALESCE(epd.EvnDirection_id, ed.EvnDirection_id)
				left join v_MedStaffFact msf on msf.MedStaffFact_id = epl.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			where
				ed.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and ed.EvnStatus_id not in (12,13)
		";
		
		return $this->queryResult($query, array(
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id']
		));
	}	
	
	/**
	 * Получение списка категорий ТС
	 */
	function getDriverCategory($data) {

		$query = "
			SELECT
				DC.DriverCategory_id as \"value\",
				DC.DriverCategory_Name as \"boxLabel\",
				case when (COALESCE(DriverCategoryLink_id,0)=0) then 'false' else 'true' end as \"checked\"
			FROM
				v_DriverCategory DC 
				left join v_DriverCategoryLink DCL  on	DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id
					AND DCL.DriverCategory_id = DC.DriverCategory_id
			ORDER BY
				value
			";
		
		return $this->queryResult($query, array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		));
	}
	
	/**
	 * Медицинские ограничения к управлению ТС
	 */
	function getDriverMedicalClose($data) {

		$query = "
			SELECT
				DC.DriverMedicalClose_id as \"value\",
				DC.DriverMedicalClose_Name as \"boxLabel\",
				case when (COALESCE(DriverMedicalCloseLink_id,0)=0) then 'false' else 'true' end as \"checked\"
			FROM
				DriverMedicalClose DC 
				left join v_DriverMedicalCloseLink DCL  on	DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id
					AND DCL.DriverMedicalClose_id = DC.DriverMedicalClose_id
			";

		return $this->queryResult($query, array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		));
	}
	
	/**
	 * Медицинские показания к управлению ТС
	 */
	function getDriverMedicalIndication($data) {

		$query = "
			SELECT
				DC.DriverMedicalIndication_id as \"value\",
				DC.DriverMedicalIndication_Name as \"boxLabel\",
				case when (COALESCE(DriverMedicalIndicationLink_id,0)=0) then 'false' else 'true' end as \"checked\"
			FROM
				DriverMedicalIndication DC 
				left join v_DriverMedicalIndicationLink DCL  on	DCL.EvnPLDispDriver_id = :EvnPLDispDriver_id
					AND DCL.DriverMedicalIndication_id = DC.DriverMedicalIndication_id
			";

		return $this->queryResult($query, array(
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		));
	}
	
	/**
	 * данные из регистров
	 */
	function getRegistryInfo($data) {

		$query = "
			select
				MT.MorbusType_SysNick as \"MorbusType_SysNick\",
				COALESCE(Diag.Diag_FullName, PRDiag.Diag_FullName) as \"Diag_Name\"
			from v_PersonRegister PR 
			inner join v_MorbusType MT on PR.MorbusType_id = MT.MorbusType_id
			left join v_EvnNotifyCrazy EN on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
			left join v_MorbusCrazy MO on MO.Morbus_id = COALESCE(EN.Morbus_id,PR.Morbus_id)
			LEFT JOIN LATERAL (
				select CD.Diag_id 
				from v_MorbusCrazyDiag MCD 
				left join v_CrazyDiag CD on CD.CrazyDiag_id=MCD.CrazyDiag_id 
				where MCD.MorbusCrazy_id=MO.MorbusCrazy_id
				order by MCD.MorbusCrazyDiag_setDT desc
				limit 1
			) CDiag ON true
			left join v_Diag Diag on Diag.Diag_id = CDiag.Diag_id
			left join v_Diag PRDiag on PRDiag.Diag_id = PR.Diag_id
			where 
				PR.Person_id = :Person_id and
				PR.PersonRegister_disDate is null and 
				MT.MorbusType_SysNick in('crazy', 'narc')
			limit 1
			";
			
		$res = $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));
		
		if (count($res)) {
			return array('success' => true, 'message' => "Пациент состоит в Регистре по ".($res[0]['Diag_Name']=='crazy' ? 'психиатрии' : 'наркологии')." с диагнозом {$res[0]['Diag_Name']}");
		}
		
		return array('success' => true, 'message' => '');
	}
	
	/**
	 * Печать направления на МСЭ в формате HL7
	 */
	function printEvnPLDispDriverHL7($data)
	{
		$resp = $this->queryResult("
			select
				Evn.EvnPLDispDriver_id as \"EvnPLDispDriver_id\", /*идентификатор bigint*/
				to_char(Evn.EvnPLDispDriver_setDT, 'YYYY-MM-DD') as \"EvnPLDispDriver_setDT\", /*идентификатор bigint*/
				LpuOID.PassportToken_tid as \"PassportToken_tid\",
				Evn.Person_id as \"Person_id\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				s.Sex_Code as \"Sex_Code\",
				s.Sex_Name as \"Sex_Name\",
				ua.Address_Address as \"UAddress_Address\",
				ua.KLRgn_id as \"UKLRgn_id\",
				pa.Address_Address as \"PAddress_Address\",
				pa.KLRgn_id as \"PKLRgn_id\",
				ps.Person_Phone as \"Person_Phone\",
				VPI.PersonInfo_Email as \"PersonInfo_Email\",
				L.Lpu_Name as \"Lpu_Name\",
				L.Lpu_Phone as \"Lpu_Phone\",
				L.UAddress_id as \"LUAddress_id\",
				lua.Address_Address as \"LUAddress_Address\",
				lua.Address_Zip as \"LUAddress_Zip\",
				luasr.KLSubRgn_Name as \"LUKLSubRgn_Name\",
				luat.KLTown_Name as \"LUKLTown_Name\",
				luac.KLCity_Name as \"LUKLCity_Name\",
				luas.KLStreet_Name as \"LUKLStreet_Name\",
				lua.Address_Corpus as \"LUAddress_Corpus\",
				lua.Address_House as \"LUAddress_House\",
				lua.KLRgn_id as \"LUKLRgn_id\",
				L.PAddress_id as \"LPAddress_id\",
				lpa.Address_Address as \"LPAddress_Address\",
				lpa.Address_Zip as \"LPAddress_Zip\",
				lpasr.KLSubRgn_Name as \"LPKLSubRgn_Name\",
				lpat.KLTown_Name as \"LPKLTown_Name\",
				lpac.KLCity_Name as \"LPKLCity_Name\",
				lpas.KLStreet_Name as \"LPKLStreet_Name\",
				lpa.Address_Corpus as \"LPAddress_Corpus\",
				lpa.Address_House as \"LPAddress_House\",
				lpa.KLRgn_id as \"LPKLRgn_id\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Person_SurName as \"MedPersonal_SurName\",
				msf.Person_FirName as \"MedPersonal_FirName\",
				msf.Person_SecName as \"MedPersonal_SecName\",
				Evn.EvnPLDispDriver_MedSer as \"EvnPLDispDriver_MedSer\",
				Evn.EvnPLDispDriver_MedNum as \"EvnPLDispDriver_MedNum\",
				d.Document_Num as \"Document_Num\",
				d.Document_Ser as \"Document_Ser\",
				to_char(d.Document_begDate, 'DD.MM.YYYY') as \"Document_begDate\",
				o.Org_Name as \"DocOrg_Name\",
				Evn.ResultDispDriver_id as \"ResultDispDriver_id\"
			from
				dbo.v_EvnPLDispDriver Evn
				left join lateral (
					select
						evdd.MedStaffFact_id
					from
						v_EvnVizitDispDop evdd
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where
						EvnVizitDispDop_pid = Evn.EvnPLDispDriver_id				
						and stl.SurveyType_id = 158 -- прием (осмотр) терапевтом
					limit 1		
				) evdd on true
				left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id
				left join fed.v_PassportToken LpuOID withon LpuOID.Lpu_id = Evn.Lpu_id
				left join v_PersonState ps on ps.Person_id = Evn.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_Org o on o.Org_id = d.OrgDep_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_Address_all pa on pa.Address_id = ps.PAddress_id
				left join v_PersonInfo VPI on VPI.Person_id = PS.Person_id
				left join v_Lpu l on l.Lpu_id = Evn.Lpu_id
				left join v_Address_all lua on lua.Address_id = l.UAddress_id
				left join v_KLSubRgn luasr on luasr.KLSubRgn_id = lua.KLSubRgn_id
				left join v_KLTown luat on luat.KLTown_id = lua.KLTown_id
				left join v_KLCity luac on luac.KLCity_id = lua.KLCity_id
				left join v_KLStreet luas on luas.KLStreet_id = lua.KLStreet_id
				left join v_Address_all lpa on lpa.Address_id = l.PAddress_id
				left join v_KLSubRgn lpasr on lpasr.KLSubRgn_id = lpa.KLSubRgn_id
				left join v_KLTown lpat on lpat.KLTown_id = lpa.KLTown_id
				left join v_KLCity lpac on lpac.KLCity_id = lpa.KLCity_id
				left join v_KLStreet lpas on lpas.KLStreet_id = lpa.KLStreet_id
			where
				Evn.EvnPLDispDriver_id = :EvnPLDispDriver_id
			limit 1
		", [
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		]);

		if (empty($resp[0]['EvnPLDispDriver_id'])) {
			throw new Exception('Ошибка получения данных по направлению на МСЭ', 500);
		}

		// достаем категории
		$resp[0]['DriverCategorys'] = [];
		$resp_dcl = $this->queryResult("
			select
				dc.DriverCategory_Code as \"DriverCategory_Code\",
				dc.DriverCategory_Name as \"DriverCategory_Name\"
			from
				v_DriverCategoryLink dcl
				inner join v_DriverCategory dc on dc.DriverCategory_id = dcl.DriverCategory_id
			where
				dcl.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", [
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]['DriverCategorys'][] = $one_dcl['DriverCategory_Name'];
		}

		// достаем ограничения
		$resp[0]['DriverMedicalCloses'] = [];
		$resp_dcl = $this->queryResult("
			select
				dmcl.DriverMedicalClose_id as \"DriverMedicalClose_id\"
			from
				v_DriverMedicalCloseLink dmcl
			where
				dmcl.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", [
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]['DriverMedicalCloses'][] = $one_dcl['DriverMedicalClose_id'];
		}

		// достаем показания
		$resp[0]['DriverMedicalIndications'] = [];
		$resp_dcl = $this->queryResult("
			select
				dmil.DriverMedicalIndication_id as \"DriverMedicalIndication_id\"
			from
				v_DriverMedicalIndicationLink dmil
			where
				dmil.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", [
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		]);
		foreach($resp_dcl as $one_dcl) {
			$resp[0]['DriverMedicalIndications'][] = $one_dcl['DriverMedicalIndication_id'];
		}

		if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] == 643 && $resp[0]['NationalityStatus_IsTwoNation'] == 1) {
			$resp[0]['personNationCode'] = '1';
			$resp[0]['personNationName'] = 'Гражданин Российской Федерации';
		} else if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] == 643 && $resp[0]['NationalityStatus_IsTwoNation'] == 2) {
			$resp[0]['personNationCode'] = '2';
			$resp[0]['personNationName'] = 'Гражданин Российской Федерации и иностранного государства (двойное гражданство)';
		} else if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] != 643) {
			$resp[0]['personNationCode'] = '3';
			$resp[0]['personNationName'] = 'Иностранный гражданин';
		} else {
			$resp[0]['personNationCode'] = '4';
			$resp[0]['personNationName'] = 'Лицо без гражданства';
		}

		$resp[0]['assignedTime'] = date('Y-m-d');
		$resp[0]['isAssigned'] = 'S';

		$this->load->library('parser');

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/xsl/MBC.xsl"?>';
		$xml .= $this->parser->parse('print_evnpldispdriver_hl7', $resp[0], true);

		return array('xml' => $xml);
	}

	/**
	 * Проверка прав на подписание карты мед освидетельствования водителя
	 */
	function checkSignAccess($data) {
		// Права на подписание карты имеет врач Проводивший прием (осмотр) врачом-терапевтом.
		// В поле «Медицинское освидетельствование закончено» установлено значение «Да»
		$resp_epldd = $this->queryResult("
			select
				epldd.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				epldd.EvnPLDispDriver_IsFinish as \"EvnPLDispDriver_IsFinish\"
			from
				v_EvnPLDispDriver EPLDD
				left join lateral (
					select
						evdd.MedStaffFact_id
					from
						v_EvnVizitDispDop evdd
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					where
						EvnVizitDispDop_pid = EPLDD.EvnPLDispDriver_id				
						and stl.SurveyType_id = 158 -- прием (осмотр) терапевтом
					limit 1		
				) evdd on true
				left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id 
			where
				EPLDD.EvnPLDispDriver_id = :EvnPLDispDriver_id
		", [
			'EvnPLDispDriver_id' => $data['EvnPLDispDriver_id']
		]);

		if (empty($resp_epldd[0]['EvnPLDispDriver_id'])) {
			throw new Exception('Подписание невозможно, т.к. не найдено подписываемое медицинское освидетельствование водителя');
		}

		if (empty($resp_epldd[0]['MedPersonal_id'])) {
			throw new Exception('Подписание невозможно, т.к. не указан врач в приеме (осмотре) врачом-терапевтом');
		}

		if (empty($data['session']['medpersonal_id']) || $resp_epldd[0]['MedPersonal_id'] != $data['session']['medpersonal_id']) {
			throw new Exception('Подписание невозможно, права на подписание карты имеет врач проводивший прием (осмотр) врачом-терапевтом.');
		}

		if ($resp_epldd[0]['EvnPLDispDriver_IsFinish'] != 2) {
			throw new Exception('Подписание невозможно, т.к. медицинское освидетельствование не закончено');
		}

		return true;
	}
}