<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispMigrant_model - модель для работы с талонами по диспансеризации мигрантов
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

class EvnPLDispMigrant_model extends EvnPLDispAbstract_model
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
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispMigran_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона освидетельствования';
		$arr['evnpldispmigran_rfbegdate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_RFbegDate',
			'label' => 'Планируемый период пребывания в РФ',
			'save' => '',
			'type' => 'date'
		);
		$arr['evnpldispmigran_rfenddate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_RFendDate',
			'label' => 'Планируемый период пребывания в РФ',
			'save' => '',
			'type' => 'date'
		);
		$arr['evnpldispmigrant_isfinish'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigrant_IsFinish',
			'label' => 'Медицинское обследование закончено',
			'save' => '',
			'type' => 'id'
		);
		$arr['resultdispmigrant_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDispMigrant_id',
			'label' => 'Результат',
			'save' => '',
			'type' => 'id'
		);
		$arr['evnpldispmigran_serthivnumber'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertHIVNumber',
			'label' => 'Сертификат об обследовании на ВИЧ',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['evnpldispmigran_serthivdate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertHIVDate',
			'label' => 'Сертификат об обследовании на ВИЧ',
			'save' => '',
			'type' => 'date'
		);
		$arr['evnpldispmigran_sertinfectnumber'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertInfectNumber',
			'label' => 'Мед. заключение об инфекционных заболеваниях',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['evnpldispmigran_sertinfectdate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertInfectDate',
			'label' => 'Мед. заключение об инфекционных заболеваниях',
			'save' => '',
			'type' => 'date'
		);
		$arr['evnpldispmigran_sertnarconumber'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertNarcoNumber',
			'label' => 'Мед. заключение о наркомании',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['evnpldispmigran_sertnarcodate'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispMigran_SertNarcoDate',
			'label' => 'Мед. заключение о наркомании',
			'save' => '',
			'type' => 'date'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 189;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispMigrant';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRFDateRange($id, $value = null)
	{
		$value = explode(' - ', $value);
		if (count($value) == 2) {
			$this->_updateAttribute($id, 'evnpldispmigran_rfbegdate', $value[0]);
			return $this->_updateAttribute($id, 'evnpldispmigran_rfenddate', $value[1]);
		}
		else {
			$this->_updateAttribute($id, 'evnpldispmigran_rfbegdate', null);
			return $this->_updateAttribute($id, 'evnpldispmigran_rfenddate', null);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnPLDispMigrantIsFinish($id, $value = null) {
		if ($value == 2) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as [count]
				from v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispMigrant_id and
					R.Rate_ValuesIs is not null	and (
						(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
						(ST.SurveyType_Code = 151 and R.RateType_id = 169) or --нарколог
						(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
						(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
					)
			', array(
				'EvnPLDispMigrant_id' => $id
			));
			if (empty($test) || $test < 5) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования мигранта не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей');
			}
		}
		return $this->_updateAttribute($id, 'evnpldispmigrant_isfinish', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateResultDispMigrantid($id, $value = null) {
		if ($value == 1) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as [count]
				from v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispMigrant_id and
					R.Rate_ValuesIs = 2 and (
						(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
						(ST.SurveyType_Code = 151 and R.RateType_id = 169) or --нарколог
						(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
						(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
					)
			', array(
				'EvnPLDispMigrant_id' => $id
			));
			if (!empty($test) && $test > 0) {
				return array('Error_Msg' => 'Результат «Отсутствие заболеваний, опасных для окружающих» не может быть выбран, если хотя бы один из осмотров врачей выявил наличие заболевания');
			}
		}
		return $this->_updateAttribute($id, 'resultdispmigrant_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertHIVNumber($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_serthivnumber', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertHIVDate($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_serthivdate', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertInfectNumber($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_sertinfectnumber', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertInfectDate($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_sertinfectdate', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertNarcoNumber($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_sertnarconumber', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSertNarcoDate($id, $value = null) {
		return $this->_updateAttribute($id, 'evnpldispmigran_sertnarcodate', $value);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPLDispMigrant($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispMigrant_del
				@EvnPLDispMigrant_id = :EvnPLDispMigrant_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'],
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
		// Проверки
		/*$checkResult = $this->checkEvnPLDispMigrantCanBeSaved($data, 'saveDopDispInfoConsent');

		if ( !empty($checkResult['Error_Msg']) || !empty($checkResult['Alert_Msg'])) {
			return array($checkResult);
		}*/

		// Стартуем транзакцию
		$this->db->trans_begin();

		$EvnPLDispDopIsNew = false;

		if ($data['EvnPLDispMigrant_IsMobile']) { $data['EvnPLDispMigrant_IsMobile'] = 2; } else { $data['EvnPLDispMigrant_IsMobile'] = 1;	}
		
		if (empty($data['EvnPLDispMigrant_id'])) {

			$EvnPLDispDopIsNew = true;
		
			if ( isset($data['EvnPLDispMigran_RFDateRange']) && count($data['EvnPLDispMigran_RFDateRange']) == 2 && !empty($data['EvnPLDispMigran_RFDateRange'][0]) ) {
				$data['EvnPLDispMigran_RFbegDate'] = $data['EvnPLDispMigran_RFDateRange'][0];
				$data['EvnPLDispMigran_RFendDate'] = $data['EvnPLDispMigran_RFDateRange'][1];
			}
			else {
				$data['EvnPLDispMigran_RFbegDate'] = null;
				$data['EvnPLDispMigran_RFendDate'] = null;
			}

			// добавляем новый талон ДД
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
					
				set @Res = :EvnPLDispMigrant_id;
				
				exec p_EvnPLDispMigrant_ins
					@EvnPLDispMigrant_id = @Res output, 
					@MedStaffFact_id = :MedStaffFact_id, 
					@EvnPLDispMigrant_pid = null, 
					@EvnPLDispMigrant_rid = null, 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id,
					@EvnPLDispMigrant_setDT = :EvnPLDispMigrant_setDate, 
					@EvnPLDispMigrant_disDT = null, 
					@EvnPLDispMigrant_didDT = null, 
					@Morbus_id = null, 
					@EvnPLDispMigrant_IsSigned = null, 
					@pmUser_signID = null, 
					@EvnPLDispMigrant_signDT = null, 
					@EvnPLDispMigrant_VizitCount = null, 
					@EvnPLDispMigrant_IsFinish = 1, 
					@Person_Age = null, 
					@AttachType_id = 2, 
					@Lpu_aid = null, 
					@EvnPLDispMigrant_consDT = :EvnPLDispMigrant_consDate,
					@EvnPLDispMigrant_IsMobile = :EvnPLDispMigrant_IsMobile,
					@Lpu_mid = :Lpu_mid,
					@DispClass_id = :DispClass_id,
					@PayType_id = :PayType_id,
					@EvnPLDispMigrant_fid = :EvnPLDispMigrant_fid,
					@EvnPLDispMigrant_Num = :EvnPLDispMigrant_Num,
					@EvnPLDispMigran_RFbegDate = :EvnPLDispMigran_RFbegDate,
					@EvnPLDispMigran_RFendDate = :EvnPLDispMigran_RFendDate,
					@ResultDispMigrant_id = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as EvnPLDispMigrant_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'],
				'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnPLDispMigrant_setDate' => $data['EvnPLDispMigrant_consDate'],
				'EvnPLDispMigrant_consDate' => $data['EvnPLDispMigrant_consDate'],
				'EvnPLDispMigrant_IsMobile' => $data['EvnPLDispMigrant_IsMobile'],
				'Lpu_mid' => $data['Lpu_mid'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnPLDispMigrant_fid' => $data['EvnPLDispMigrant_fid'],
				'EvnPLDispMigrant_Num' => $this->getEvnPLDispMigrantNumber($data),
				'EvnPLDispMigran_RFbegDate' => $data['EvnPLDispMigran_RFbegDate'],
				'EvnPLDispMigran_RFendDate' => $data['EvnPLDispMigran_RFendDate'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (isset($resp[0]['EvnPLDispMigrant_id'])) {
					$data['EvnPLDispMigrant_id'] = $resp[0]['EvnPLDispMigrant_id'];
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
				pd.Diag_id,
				convert(varchar(10), pd.PersonDisp_begDate, 104) as PersonDisp_begDate
			from
				v_PersonDisp pd (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
				left join v_ProfileDiag pdiag (nolock) on pdiag.Diag_id = d.Diag_pid
			where
				pd.Person_id = :Person_id
				and (pd.PersonDisp_begDate <= :EvnPLDispMigrant_consDate OR pd.PersonDisp_begDate IS NULL)
				and (pd.PersonDisp_endDate >= :EvnPLDispMigrant_consDate OR pd.PersonDisp_endDate IS NULL)
				and pdiag.ProfileDiagGroup_id IS NULL
		";
		$result = $this->db->query($query, array(
			'EvnPLDispMigrant_consDate' => $data['EvnPLDispMigrant_consDate'],
			'Person_id' => $data['Person_id']
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Diag_id'])) {
				$data['EvnPLDisp_id'] = $data['EvnPLDispMigrant_id'];
                foreach ($resp as $item){
					$data['EvnDiagDopDisp_setDate'] = !empty($item['PersonDisp_begDate'])?date('Y-m-d', strtotime($item['PersonDisp_begDate'])):null;
                    $this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['Diag_id']);
                }
			}
		}
		
		// сохраняем данные по информир. добр. согласию для EvnPLDispMigrant_id = $data['EvnPLDispMigrant_id']
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
			
			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispMigrant_id'], $item['SurveyTypeLink_id']);
			
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
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :DopDispInfoConsent_id;
					
					exec {$proc}
						@DopDispInfoConsent_id = @Res output, 
						@EvnPLDisp_id = :EvnPLDispMigrant_id, 
						@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
						@DopDispInfoConsent_IsEarlier = null, 
						@SurveyTypeLink_id = :SurveyTypeLink_id, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
	 
					select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'],
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
					'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
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
			// Обновляем дату EvnPLDispMigrant_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select top 1
					 EvnPLDispMigrant_pid
					,EvnPLDispMigrant_rid
					,Lpu_id
					,Server_id
					,PersonEvn_id
					,convert(varchar(20), EvnPLDispMigrant_setDT, 120) as EvnPLDispMigrant_setDT
					,convert(varchar(20), EvnPLDispMigrant_disDT, 120) as EvnPLDispMigrant_disDT
					,convert(varchar(20), EvnPLDispMigrant_didDT, 120) as EvnPLDispMigrant_didDT
					,Morbus_id
					,EvnPLDispMigrant_IsSigned
					,EvnPLDispMigrant_IndexRep
					,EvnPLDispMigrant_IndexRepInReg
					,pmUser_signID
					,EvnPLDispMigrant_signDT
					,EvnPLDispMigrant_IsFinish
					,Person_Age
					,AttachType_id
					,Lpu_aid
					,DispClass_id
					,EvnPLDispMigrant_Num
					,convert(varchar(20), EvnPLDispMigran_RFbegDate, 120) as EvnPLDispMigran_RFbegDate
					,convert(varchar(20), EvnPLDispMigran_RFendDate, 120) as EvnPLDispMigran_RFendDate
					,EvnPLDispMigran_SertHIVNumber
					,EvnPLDispMigran_SertHIVDate
					,EvnPLDispMigran_SertInfectNumber
					,EvnPLDispMigran_SertInfectDate
					,EvnPLDispMigran_SertNarcoNumber
					,EvnPLDispMigran_SertNarcoDate
					,ResultDispMigrant_id
				from v_EvnPLDispMigrant with (nolock)
				where EvnPLDispMigrant_id = :EvnPLDispMigrant_id
			";
			$result = $this->db->query($query, array(
				'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 ) {
					$resp[0]['EvnPLDispMigrant_consDT'] = $data['EvnPLDispMigrant_consDate'];
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					$resp[0]['EvnPLDispMigrant_IsMobile'] = $data['EvnPLDispMigrant_IsMobile'];
					$resp[0]['Lpu_mid'] = $data['Lpu_mid'];
					$resp[0]['PayType_id'] = $data['PayType_id'];
					$resp[0]['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
					
					if ( count($data['EvnPLDispMigran_RFDateRange']) == 2 && !empty($data['EvnPLDispMigran_RFDateRange'][0]) ) {
						$resp[0]['EvnPLDispMigran_RFbegDate'] = $data['EvnPLDispMigran_RFDateRange'][0];
						$resp[0]['EvnPLDispMigran_RFendDate'] = $data['EvnPLDispMigran_RFDateRange'][1];
					}
					else {
						$resp[0]['EvnPLDispMigran_RFbegDate'] = null;
						$resp[0]['EvnPLDispMigran_RFendDate'] = null;
					}

					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
							
						set @Res = :EvnPLDispMigrant_id;
						
						exec p_EvnPLDispMigrant_upd
							@EvnPLDispMigrant_id = @Res output, 
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "@" . $key . " = :" . $key . ",";
					}

					$query .= "
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
 
						select @Res as EvnPLDispMigrant_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

					$resp[0]['EvnPLDispMigrant_id'] = $data['EvnPLDispMigrant_id'];

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
				$attrArray[] = 'ProphConsult'; // Показания к углубленному профилактическому консультированию
				$attrArray[] = 'NeedConsult'; // Показания к консультации врача-специалиста
			}

			foreach ( $attrArray as $attr ) {
				$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispMigrant_id'], $data['pmUser_id']);

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
			'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']
		);
	}
	
	/**
	 * Обновление данных по информир. добр. согласию (штучно)
	 */
	function updateDopDispInfoConsent($data) {
		
		if ($data['DopDispInfoConsent_id'] > 0) {
			$SurveyTypeLink_id = $this->getFirstResultFromQuery('
				select SurveyTypeLink_id 
				from v_DopDispInfoConsent (nolock) 
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
				select DopDispInfoConsent_id 
				from v_DopDispInfoConsent (nolock) 
				where SurveyTypeLink_id = :SurveyTypeLink_id and EvnPLDisp_id = :EvnPLDisp_id
			', array(
				'SurveyTypeLink_id' => $SurveyTypeLink_id,
				'EvnPLDisp_id' => $data['EvnPLDispMigrant_id']
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DopDispInfoConsent_id;
			
			exec {$proc}
				@DopDispInfoConsent_id = @Res output, 
				@EvnPLDisp_id = :EvnPLDispMigrant_id, 
				@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
				@DopDispInfoConsent_IsEarlier = null, 
				@SurveyTypeLink_id = :SurveyTypeLink_id, 
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output

			select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'],
			'DopDispInfoConsent_id' => $DopDispInfoConsent_id,
			'DopDispInfoConsent_IsAgree' => $data['DopDispInfoConsent_IsAgree'],
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
			select top 1
				DopDispInfoConsent_id
			from
				v_DopDispInfoConsent (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyTypeLink_id = :SurveyTypeLink_id
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
	function getEvnPLDispMigrantViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// Редактирование карты доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as accessType,";
		$accessType = "'edit' as accessType,";
		/*if (!empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when ISNULL(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as accessType,";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}*/

		$query = "
			select
				EPLDM.EvnPLDispMigrant_id,
				case
					when EPLDM.MedStaffFact_id is not null then ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(ls.LpuSection_Name + ' ', '') + ISNULL(msf.Person_Fio, '') 
					else ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(pu.pmUser_Name, '')
				end as AuthorInfo,
				'EvnPLDispMigrant' as Object,
				EPLDM.DispClass_id,
				EPLDM.Person_id,
				EPLDM.PersonEvn_id,
				EPLDM.Server_id,
				dc.DispClass_Code,
				dc.DispClass_Name,
				l.Lpu_Nick,
				{$accessType}
				EPLDM.PayType_id,
				pt.PayType_Name,
				convert(varchar(10), EPLDM.EvnPLDispMigrant_setDT, 104) as EvnPLDispMigrant_setDate,
				convert(varchar(10), EPLDM.EvnPLDispMigrant_consDT, 104) as EvnPLDispMigrant_consDate,
				convert(varchar(10), EPLDM.EvnPLDispMigran_RFbegDate, 104) + ' - ' + convert(varchar(10), EPLDM.EvnPLDispMigran_RFendDate, 104) as EvnPLDispMigrant_RFDateRange,
				EPLDM.EvnPLDispMigrant_IsFinish,
				IsFinish.YesNo_Name as EvnPLDispMigrant_IsFinish_Name,
				EPLDM.EvnPLDispMigrant_Num,
				EPLDM.ResultDispMigrant_id,
				ResultDispMigrant.ResultDispMigrant_Name,
				EPLDM.EvnPLDispMigran_SertHIVNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertHIVDate, 104) as EvnPLDispMigran_SertHIVDate,
				EPLDM.EvnPLDispMigran_SertInfectNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertInfectDate, 104) as EvnPLDispMigran_SertInfectDate,
				EPLDM.EvnPLDispMigran_SertNarcoNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertNarcoDate, 104) as EvnPLDispMigran_SertNarcoDate,
				IsInfected.count as IsInfected,
				IsSert.IsHiv,
				IsSert.IsInfect,
				IsSert.IsNarco,
				IsHivAgree.count as IsHivAgree
			from
				v_EvnPLDispMigrant EPLDM (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = EPLDM.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = EPLDM.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = EPLDM.pmUser_updID
				left join v_DispClass dc (nolock) on dc.DispClass_id = EPLDM.DispClass_id
				left join v_PayType pt (nolock) on pt.PayType_id = EPLDM.PayType_id
				left join v_YesNo IsFinish (nolock) on IsFinish.YesNo_id = EPLDM.EvnPLDispMigrant_IsFinish
				left join v_ResultDispMigrant ResultDispMigrant (nolock) on ResultDispMigrant.ResultDispMigrant_id = EPLDM.ResultDispMigrant_id
				outer apply (
					select 
						count(*) as [count]
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
						inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
					where
						EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id and
						R.Rate_ValuesIs = 2 and (
							(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
							(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
							(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
						)
				) as IsInfected
				outer apply (
					select 
						case when COUNT(case when (R.RateType_id = 171 and R.Rate_ValuesIs = 1) then 1 end) = 1 then null else 1 end as [IsHiv],
						case when COUNT(case when (R.RateType_id IN(156,168,171,172)) then 1 end) = 4 then null else 1 end as [IsInfect],
						case when COUNT(case when (R.RateType_id = 169) then 1 end) = 1 then null else 1 end as [IsNarco]
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
						inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
					where
						EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id and
						R.Rate_ValuesIs is not null and (
							(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
							(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
							(ST.SurveyType_Code = 154 and R.RateType_id = 168) or --дерматовенеролог, сифилис
							(ST.SurveyType_Code = 151 and R.RateType_id = 169) --нарколог
						)
				) as IsSert
				outer apply (
					select 
						count (*) [count]
					from v_SurveyTypeLink STL (nolock)
						left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						DDIC.EvnPLDisp_id = EPLDM.EvnPLDispMigrant_id and 
						STL.SurveyType_id in (142,143,159) and 
						ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 and
						DDIC.DopDispInfoConsent_IsAgree = 2
				) as IsHivAgree
			where
				EPLDM.EvnPLDispMigrant_id = :EvnPLDisp_id
		";

		return $this->queryResult($query, $queryParams);
	}
	
	
	/**
	 *	Получение данных по инфекциям
	 */
	function getInfectData($data){

		$query = "
			select
				EPLDM.EvnPLDispMigrant_id,
				IsInfected.count as IsInfected,
				IsSert.IsHiv,
				IsSert.IsInfect,
				IsSert.IsNarco
			from
				v_EvnPLDispMigrant EPLDM (nolock)
				outer apply (
					select 
						count(*) as [count]
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
						inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
					where
						EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id and
						R.Rate_ValuesIs = 2 and (
							(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
							(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
							(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
						)
				) as IsInfected
				outer apply (
					select 
						case when COUNT(case when (R.RateType_id = 171 and R.Rate_ValuesIs = 1) then 1 end) = 1 then null else 1 end as [IsHiv],
						case when COUNT(case when (R.RateType_id IN(156,168,171,172)) then 1 end) = 4 then null else 1 end as [IsInfect],
						case when COUNT(case when (R.RateType_id = 169) then 1 end) = 1 then null else 1 end as [IsNarco]
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
						left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
					where
						EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id and
						R.Rate_ValuesIs is not null and (
							(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
							(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
							(ST.SurveyType_Code = 154 and R.RateType_id = 168) or --дерматовенеролог, сифилис
							(ST.SurveyType_Code = 151 and R.RateType_id = 169) --нарколог
						)
				) as IsSert
			where
				EPLDM.EvnPLDispMigrant_id = :EvnPLDispMigrant_id
		";

		return $this->queryResult($query, $data);
	}
	
	
	/**
	 *	Получение данных для формы редактирования карты
	 */
	function loadEvnPLDispMigrantEditForm($data)
	{
		$accessType = '
			case
				when EPLDM.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDM.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EPLDM.EvnPLDispMigrant_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';
		
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLDM.EvnPLDispMigrant_id,
				ISNULL(EPLDM.EvnPLDispMigrant_IsPaid, 1) as EvnPLDispMigrant_IsPaid,
				ISNULL(EPLDM.EvnPLDispMigrant_IndexRep, 0) as EvnPLDispMigrant_IndexRep,
				EPLDM.EvnPLDispMigrant_fid,
				EPLDM.Person_id,
				EPLDM.PersonEvn_id,
				ISNULL(EPLDM.DispClass_id, 6) as DispClass_id,
				EPLDM.PayType_id,
				EPLDM.EvnPLDispMigrant_pid,
				convert(varchar(10), EPLDM.EvnPLDispMigrant_setDate, 104) as EvnPLDispMigrant_setDate,
				convert(varchar(10), EPLDM.EvnPLDispMigrant_disDate, 104) as EvnPLDispMigrant_disDate,
				convert(varchar(10), EPLDM.EvnPLDispMigrant_consDT, 104) as EvnPLDispMigrant_consDate,
				EPLDM.Server_id,
				case when EPLDM.EvnPLDispMigrant_IsMobile = 2 then 1 else 0 end as EvnPLDispMigrant_IsMobile,
				EPLDM.Lpu_mid,
				EPLDM.EvnPLDispMigrant_IsFinish,
				EPLDM.EvnPLDispMigrant_Num,
				EPLDM.ResultDispMigrant_id,
				convert(varchar(10), EPLDM.EvnPLDispMigran_RFbegDate, 104) + ' - ' + convert(varchar(10), EPLDM.EvnPLDispMigran_RFendDate, 104) as EvnPLDispMigran_RFDateRange,
				EPLDM.EvnPLDispMigran_SertHIVNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertHIVDate, 104) as EvnPLDispMigran_SertHIVDate,
				EPLDM.EvnPLDispMigran_SertInfectNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertInfectDate, 104) as EvnPLDispMigran_SertInfectDate,
				EPLDM.EvnPLDispMigran_SertNarcoNumber,
				convert(varchar(10), EPLDM.EvnPLDispMigran_SertNarcoDate, 104) as EvnPLDispMigran_SertNarcoDate
			FROM
				v_EvnPLDispMigrant EPLDM (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDM.EvnPLDispMigrant_id
			WHERE
				(1 = 1)
				and EPLDM.EvnPLDispMigrant_id = :EvnPLDispMigrant_id
		";
		
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']));
		
        if (is_object($result))
        {
			$resp = $result->result('array');
			return $resp;
		}
		else
		{
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
			'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispMigrant_consDate' => $data['EvnPLDispMigrant_consDate']
		);

		$query = "
			Declare @sex_id bigint, @age int

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, cast(substring(:EvnPLDispMigrant_consDate, 1, 4) + '-12-31' as datetime))
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id

			select
				ISNULL(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as DopDispInfoConsent_id,
				MAX(DDIC.EvnPLDisp_id) as EvnPLDispMigrant_id,
				MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id,
				ISNULL(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as SurveyTypeLink_IsNeedUsluga,
				ISNULL(MAX(STL.SurveyTypeLink_IsDel), 1) as SurveyTypeLink_IsDel,
				MAX(ST.SurveyType_Code) as SurveyType_Code,
				MAX(ST.SurveyType_Name) as SurveyType_Name,
				case WHEN MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
				case when MAX(ST.SurveyType_Code) IN (49) then 0 else 1 end
			from v_SurveyTypeLink STL (nolock)
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispMigrant_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply (
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop with (nolock)
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispMigrant_id
						and ISNULL(EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				" . implode(' ', $joinList) . "
			where 
				IsNull(STL.DispClass_id, :DispClass_id) = :DispClass_id -- тип
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispMigrant_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispMigrant_consDate)
				" . $filter . "
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
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
	 * Входящие данные: $data['EvnPLDispMigrant_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$query = "
			select
				DDIC.DopDispInfoConsent_id,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				case when el.Evn_lid is not null then 'true' else 'false' end as EvnUslugaDispDop_WithDirection,
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				ST.OrpDispSpec_id,
				isnull(epd.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				EvnXmlData.EvnXml_id
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from
						v_EvnPrescr (nolock)
					where
						DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				left join v_EvnDirection ed (nolock) on ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13)
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from
						v_EvnUslugaDispDop EUDD (nolock)
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispMigrant_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				outer apply(
					select top 1
						ex.EvnXml_id
					from
						v_EvnXml ex (nolock)
						inner join v_EvnUslugaPar eup (nolock) on ex.Evn_id = eup.EvnUslugaPar_id
					where
						eup.EvnPrescr_id = ep.EvnPrescr_id
				) EvnXmlData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispMigrant_id
				and ST.SurveyType_Code = 2
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			union

			select
				DDIC.DopDispInfoConsent_id,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				case when el.Evn_lid is not null then 'true' else 'false' end as EvnUslugaDispDop_WithDirection,
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				ST.OrpDispSpec_id,
				isnull(epd.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				EvnXmlData.EvnXml_id
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						EvnPrescr_id,
						EvnPrescr_pid,
						PrescriptionType_id
					from
						v_EvnPrescr (nolock)
					where
						DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				left join v_EvnDirection ed (nolock) on ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13)
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from v_EvnUslugaDispDop EUDD (nolock)
						left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispMigrant_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				outer apply(
					select top 1
						ex.EvnXml_id
					from
						v_EvnXml ex (nolock)
						inner join v_EvnUslugaPar eup (nolock) on ex.Evn_id = eup.EvnUslugaPar_id
					where
						eup.EvnPrescr_id = ep.EvnPrescr_id
				) EvnXmlData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispMigrant_id
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and ST.SurveyType_Code NOT IN (2, 49)
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";
		
		//echo getDebugSql($query, array('EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id'])); die();
		$result = $this->db->query($query, array('EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']));
	
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
	 * Получение списка контактных лиц
	 * Входящие данные: $data['EvnPLDispMigrant_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadMigrantContactGrid($data)
	{
		$query = "
			select
				MC.MigrantContact_id,
				MC.EvnPLDispMigrant_id,
				MC.EvnPLDispMigrant_id as pid,
				1 as RecordStatus_Code,
				MC.Person_cid,
				PS.Person_SurName as Person_Surname,
				PS.Person_FirName as Person_Firname,
				PS.Person_SecName as Person_Secname,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + PS.Person_SecName + ' ' + convert(varchar(10), PS.Person_BirthDay, 104) as MigrantContact_Name
			from v_MigrantContact MC (nolock)
				inner join v_PersonState PS (nolock) on MC.Person_cid = PS.Person_id
			where 
				MC.EvnPLDispMigrant_id = :EvnPLDispMigrant_id
		";
		
		$result = $this->db->query($query, array('EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']));
	
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
	 * Получение списка контактных лиц
	 * Входящие данные: $data['EvnPLDispMigrant_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function getMigrantContactViewData($data) {
		if (!empty($data['parent_object_value'])) {
			$data['EvnPLDispMigrant_id'] = $data['parent_object_value'];
			return $this->loadMigrantContactGrid($data);
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Удаление аттрибутов
	 */	
	function deleteAttributes($attr, $EvnPLDispMigrant_id, $pmUser_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnVizitDispDop':
				$query = "
					select
						EVDD.EvnVizitDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_EvnVizitDispDop EVDD with (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispMigrant_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code not in (1, 2, 48)
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			// Специально для удаления анкетирования
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispMigrant_id
						and DDIC.EvnPLDisp_id = :EvnPLDispMigrant_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code = 2
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			case 'EvnDiagDopDisp':
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnDiagDopDisp_pid = :EvnPLDispMigrant_id
				";
			break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispMigrant_id
						and ST.SurveyType_Code NOT IN (1,48)
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnPLDisp_id = :EvnPLDispMigrant_id
				";
			break;
		}

		$result = $this->db->query($query, array('EvnPLDispMigrant_id' => $EvnPLDispMigrant_id));

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $array ) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_" . $attr . "_del
						@" . $attr . "_id = :id,
						" . (in_array($attr, array('EvnDiagDopDisp', 'EvnUslugaDispDop', 'EvnVizitDispDop')) ? "@pmUser_id = :pmUser_id," : "") . "
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
    function saveEvnPLDispMigrant($data) {
		
		if ($data['EvnPLDispMigrant_IsFinish'] == 2) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as [count]
				from v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispMigrant_id and
					R.Rate_ValuesIs is not null	and (
						(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
						(ST.SurveyType_Code = 151 and R.RateType_id = 169) or --нарколог
						(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
						(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
					)
			', array(
				'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']
			));
			if (empty($test) || $test < 3) {
				return array('Error_Msg' => 'Случай медицинского освидетельствования мигранта не может быть закончен, если не заполнен хотя бы один из результатов осмотров врачей');
			}
		}
		
		if ($data['ResultDispMigrant_id'] == 1) {
			$test = $this->getFirstResultFromQuery('
				select 
					count(*) as [count]
				from v_EvnUslugaDispDop EUDD (nolock)
					inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					inner join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					inner join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
					inner join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispMigrant_id and
					R.Rate_ValuesIs = 2 and (
						(ST.SurveyType_Code = 150 and R.RateType_id = 156) or --туберкулез, фтизиатр
						(ST.SurveyType_Code = 151 and R.RateType_id = 169) or --нарколог
						(ST.SurveyType_Code = 152 and R.RateType_id IN(171,172)) or --инфекционист, вич, лепра
						(ST.SurveyType_Code = 154 and R.RateType_id = 168) --дерматовенеролог, сифилис
					)
			', array(
				'EvnPLDispMigrant_id' => $data['EvnPLDispMigrant_id']
			));
			if (!empty($test) && $test > 0) {
				return array('Error_Msg' => 'Результат «Отсутствие заболеваний, опасных для окружающих» не может быть выбран, если хотя бы один из осмотров врачей выявил наличие заболевания');
			}
		}

    	$proc = '';
    	if ( !isset($data['EvnPLDispMigrant_id']) ) {
			$proc = 'p_EvnPLDispMigrant_ins';
	    } else {
			$proc = 'p_EvnPLDispMigrant_upd';
	    }

		$data['EvnPLDispMigrant_setDate'] = $data['EvnPLDispMigrant_consDate'];
		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
		
		if ( count($data['EvnPLDispMigran_RFDateRange']) == 2 && !empty($data['EvnPLDispMigran_RFDateRange'][0]) ) {
			$data['EvnPLDispMigran_RFbegDate'] = $data['EvnPLDispMigran_RFDateRange'][0];
			$data['EvnPLDispMigran_RFendDate'] = $data['EvnPLDispMigran_RFDateRange'][1];
		}
		else {
			$data['EvnPLDispMigran_RFbegDate'] = null;
			$data['EvnPLDispMigran_RFendDate'] = null;
		}
		
   		$query = "
		    declare
		        @EvnPLDispMigrant_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @EvnPLDispMigrant_id = :EvnPLDispMigrant_id;

			exec {$proc}
				@EvnPLDispMigrant_id = @EvnPLDispMigrant_id output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispMigrant_setDT = :EvnPLDispMigrant_setDate,
				@EvnPLDispMigrant_IsFinish = :EvnPLDispMigrant_IsFinish,
				@EvnPLDispMigrant_consDT = :EvnPLDispMigrant_consDate,
				@DispClass_id = :DispClass_id,
				@EvnPLDispMigrant_IndexRep = :EvnPLDispMigrant_IndexRep,
				@EvnPLDispMigrant_IndexRepInReg = :EvnPLDispMigrant_IndexRepInReg,
				@PayType_id = :PayType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispMigran_RFbegDate = :EvnPLDispMigran_RFbegDate,
				@EvnPLDispMigran_RFendDate = :EvnPLDispMigran_RFendDate,
				@EvnPLDispMigran_SertHIVNumber = :EvnPLDispMigran_SertHIVNumber,
				@EvnPLDispMigran_SertHIVDate = :EvnPLDispMigran_SertHIVDate,
				@EvnPLDispMigran_SertInfectNumber = :EvnPLDispMigran_SertInfectNumber,
				@EvnPLDispMigran_SertInfectDate = :EvnPLDispMigran_SertInfectDate,
				@EvnPLDispMigran_SertNarcoNumber = :EvnPLDispMigran_SertNarcoNumber,
				@EvnPLDispMigran_SertNarcoDate = :EvnPLDispMigran_SertNarcoDate,
				@EvnPLDispMigrant_Num = :EvnPLDispMigrant_Num,
				@ResultDispMigrant_id = :ResultDispMigrant_id,
				@AttachType_id = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @EvnPLDispMigrant_id as EvnPLDispMigrant_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		
        if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) && !empty($resp[0]['EvnPLDispMigrant_id'])) {
				$data['EvnPLDispMigrant_id'] = $resp[0]['EvnPLDispMigrant_id'];
				$this->saveMigrantContactData($data);
			}
			return $resp;
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты)'));
		}
    }
	
	/**
	 *	Сохранение списка контактных лиц
	 */	
	function saveMigrantContactData($data) {
		
		$MigrantContactData = $data['MigrantContactJSON'];
		
		if ( !is_array($MigrantContactData) || !count($MigrantContactData) ) {
			return false;
		}
			
		foreach($MigrantContactData as $MigrantContact) {
			$MigrantContact = (array)$MigrantContact;
			$MigrantContact['pmUser_id'] = $data['pmUser_id'];
			$MigrantContact['EvnPLDispMigrant_id'] = $data['EvnPLDispMigrant_id'];
			switch ( $MigrantContact['RecordStatus_Code'] ) {
				case 0:
				case 2:
					$queryResponse = $this->saveMigrantContact($MigrantContact);
					break;

				case 3:
					$queryResponse = $this->deleteMigrantContact($MigrantContact);
					break;
			}
		}
	}
	
	/**
	 *	Сохранение контактного лица
	 */	
	function saveMigrantContact($data) {
		$params = $data;

		if ($params['MigrantContact_id'] > 0) {
			$procedure = 'p_MigrantContact_upd';
		} else {
			$params['MigrantContact_id'] = null;
			$procedure = 'p_MigrantContact_ins';
		}

		$query = "
			declare
				@MigrantContact_id bigint = :MigrantContact_id,
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec {$procedure}
				@MigrantContact_id = @MigrantContact_id output,
				@EvnPLDispMigrant_id = :EvnPLDispMigrant_id,
				@Person_cid = :Person_cid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MigrantContact_id as MigrantContact_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
		
	}
	
	/**
	 *	Удаление контактного лица
	 */	
	function deleteMigrantContact($data) {
		$params = array('MigrantContact_id' => $data['MigrantContact_id']);

		$query = "
			declare
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec p_MigrantContact_del
				@MigrantContact_id = :MigrantContact_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Генерация номера
	 */
	function getEvnPLDispMigrantNumber($data) {
		$query = "
			declare @EvnPLDispMigrant_Num bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnPLDispMigrant',
				@Lpu_id = :Lpu_id,
				@ObjectID = @EvnPLDispMigrant_Num output;
			select @EvnPLDispMigrant_Num as EvnPLDispMigrant_Num, '' as Error_Msg;
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			$result = $result->result('array');
			if (count($result) && $result[0]['EvnPLDispMigrant_Num']) {
				return $result[0]['EvnPLDispMigrant_Num'];
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
			declare
				@EvnPrescr_id bigint,
				@Lpu_id bigint;

			select top 1
				@EvnPrescr_id = EvnPrescr_id,
				@Lpu_id = Lpu_id
			from
				v_EvnPrescr (nolock)
			where
				DopDispInfoConsent_id = :DopDispInfoConsent_id;

			select 
				convert(varchar(10), eup.EvnUslugaPar_setDate, 104) as EvnUslugaDispDop_didDate,
				eup.EvnUslugaPar_setTime as EvnUslugaDispDop_didTime,
				convert(varchar(10), eup.EvnUslugaPar_setDate, 104) as EvnUslugaDispDop_disDate,
				eup.EvnUslugaPar_setTime as EvnUslugaDispDop_disTime,
				case when eup.Lpu_id = @Lpu_id then 1 else 3 end as ExaminationPlace_id,
				eup.Lpu_id as Lpu_uid,
				ls.LpuSectionProfile_id as LpuSectionProfile_id,
				msf.MedSpecOms_id,
				ls.LpuSection_id as LpuSection_id,
				eup.MedStaffFact_id as MedStaffFact_id,
				msf.MedPersonal_id,
				null as Diag_id
			from v_EvnPrescrDirection epd (nolock)
				inner join v_EvnUslugaPar eup (nolock) on eup.EvnDirection_id = epd.EvnDirection_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = eup.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			where
				epd.EvnPrescr_id = @EvnPrescr_id
				
			union all

			select 
				convert(varchar(10), epl.EvnVizitPL_setDate, 104) as EvnUslugaDispDop_didDate,
				epl.EvnVizitPL_setTime as EvnUslugaDispDop_didTime,
				convert(varchar(10), epl.EvnVizitPL_setDate, 104) as EvnUslugaDispDop_disDate,
				epl.EvnVizitPL_setTime as EvnUslugaDispDop_disTime,
				case when epl.Lpu_id = isnull(@Lpu_id,ed.Lpu_id) then 1 else 3 end as ExaminationPlace_id,
				epl.Lpu_id as Lpu_uid,
				ls.LpuSectionProfile_id as LpuSectionProfile_id,
				msf.MedSpecOms_id,
				ls.LpuSection_id as LpuSection_id,
				epl.MedStaffFact_id as MedStaffFact_id,
				msf.MedPersonal_id,
				epl.Diag_id
			from v_EvnDirection ed (nolock)
				left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = @EvnPrescr_id
				inner join v_EvnVizitPL epl (nolock) on epl.EvnDirection_id = isnull(epd.EvnDirection_id, ed.EvnDirection_id)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = epl.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			where
				ed.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and ed.EvnStatus_id not in (12,13)
		";
		
		return $this->queryResult($query, array(
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id']
		));
	}
	
}