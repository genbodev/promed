<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispProf_model - модель для работы с профосмотрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version      20.06.2013
*/

require_once('EvnPLDispAbstract_model.php');

class EvnPLDispProf_model extends EvnPLDispAbstract_model
{
	/**
	 *	Конструктор
	 */	
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 *	Удаление аттрибутов
	 */	
	function deleteAttributes($attr, $EvnPLDispProf_id, $pmUser_id) {
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
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsImpossible, 1) = 1
						and ST.SurveyType_Code not in (2, 49)
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
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
						and DDIC.EvnPLDisp_id = :EvnPLDispProf_id
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
					where EvnDiagDopDisp_pid = :EvnPLDispProf_id
				";
			break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispProf_id
						and ST.SurveyType_Code NOT IN (49)
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnPLDisp_id = :EvnPLDispProf_id
				";
			break;
		}

		$result = $this->db->query($query, array('EvnPLDispProf_id' => $EvnPLDispProf_id));

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
	 *	Получение кода услуги
	 */	
	function getUslugaComplexCode($data) {
		$query = "
			select
				UslugaComplex_Code
			from
				v_UslugaComplex (nolock)
			where
				UslugaComplex_id = :UslugaComplex_id
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaComplex_Code'];
			}
		}
		
		return '';
	}

	/**
	 *	Получение идентификатора согласия для сурвейтайпа
	 */	
	function getDopDispInfoConsentForSurveyType($EvnPLDisp_id, $SurveyType_id) {
		$query = "
			select top 1
				DopDispInfoConsent_id
			from
				v_DopDispInfoConsent (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyType_id = :SurveyType_id
		";
		
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $EvnPLDisp_id,
			'SurveyType_id' => $SurveyType_id
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
	 *	Получение идентификатора согласия для сурвейтайплинка
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
	 *	Сохранение согласия
	 */	
	function saveDopDispInfoConsent($data) {
		// Проверки
		$checkResult = $this->checkEvnPLDispProfCanBeSaved($data, 'saveDopDispInfoConsent');

		if ( !empty($checkResult['Error_Msg']) || !empty($checkResult['Alert_Msg'])) {
			return array($checkResult);
		}

		// Стартуем транзакцию
		$this->db->trans_begin();

		$EvnPLDispDopIsNew = false;

		if ($data['EvnPLDispProf_IsMobile']) { $data['EvnPLDispProf_IsMobile'] = 2; } else { $data['EvnPLDispProf_IsMobile'] = 1;	}
		if ($data['EvnPLDispProf_IsOutLpu']) { $data['EvnPLDispProf_IsOutLpu'] = 2; } else { $data['EvnPLDispProf_IsOutLpu'] = 1;	}

		if (empty($data['EvnPLDispProf_id'])) {
			// Проверям наличие карт за выбраный год
			// https://redmine.swan.perm.ru/issues/23095
			$query = "
				select top 1 EvnPLDispProf_id
				from v_EvnPLDispProf with (nolock)
				where Person_id = :Person_id
					and YEAR(EvnPLDispProf_consDT) = YEAR(:EvnPLDispProf_consDT)
					and DispClass_id = :DispClass_id
			";

			$result = $this->db->query($query, array(
				 'DispClass_id' => $data['DispClass_id']
				,'EvnPLDispProf_consDT' => $data['EvnPLDispProf_consDate']
				,'Person_id' => $data['Person_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при проверке наличия карт в указанном году'));
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnPLDispProf_id']) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'У человека уже имеется сохраненная карта проф. осмотра в указанном году.'));
			}
			
			// Проверям наличие карт ДВН за выбраный год
			// https://redmine.swan.perm.ru/issues/61990
			// https://redmine.swan.perm.ru/issues/75980
			$query = "
				select top 1 EvnPLDispDop13_id
				from v_EvnPLDispDop13 with (nolock)
				where Person_id = :Person_id
					and YEAR(EvnPLDispDop13_consDT) = YEAR(:EvnPLDispDop13_consDT)
					and DispClass_id = :DispClass_id
			";

			$result = $this->db->query($query, array(
				'DispClass_id' => 1
				,'EvnPLDispDop13_consDT' => $data['EvnPLDispProf_consDate']
				,'Person_id' => $data['Person_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при проверке наличия карт ДВН в указанном году'));
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnPLDispDop13_id']) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'В указанном году пациент прошел диспансеризацию взрослого населения. Проведение профосмотра невозможно.'));
			}

			$EvnPLDispDopIsNew = true;

			// добавляем новый талон ДД
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
					
				set @Res = :EvnPLDispProf_id;
				
				exec p_EvnPLDispProf_ins
					@EvnPLDispProf_id = @Res output, 
					@MedStaffFact_id = :MedStaffFact_id, 
					@EvnPLDispProf_pid = null, 
					@EvnPLDispProf_rid = null, 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id,
					@EvnPLDispProf_setDT = :EvnPLDispProf_setDate, 
					@EvnPLDispProf_disDT = null, 
					@EvnPLDispProf_didDT = null, 
					@Morbus_id = null, 
					@EvnPLDispProf_IsSigned = null, 
					@pmUser_signID = null, 
					@EvnPLDispProf_signDT = null, 
					@EvnPLDispProf_VizitCount = null, 
					@Person_Age = null, 
					@AttachType_id = 2, 
					@Lpu_aid = null, 
					@EvnPLDispProf_IsStenocard = null, 
					@EvnPLDispProf_IsDoubleScan = null, 
					@EvnPLDispProf_IsTub = null, 
					@EvnPLDispProf_IsEsophag = null, 
					@EvnPLDispProf_IsSmoking = null, 
					@EvnPLDispProf_IsRiskAlco = null, 
					@EvnPLDispProf_IsAlcoDepend = null, 
					@EvnPLDispProf_IsLowActiv = null, 
					@EvnPLDispProf_IsIrrational = null, 
					@Diag_id = null, 
					@EvnPLDispProf_IsDisp = null, 
					@NeedDopCure_id = null, 
					@EvnPLDispProf_IsStac = null, 
					@EvnPLDispProf_IsSanator = null, 
					@EvnPLDispProf_SumRick = null, 
					@RiskType_id = null, 
					@EvnPLDispProf_IsSchool = 1, 
					@EvnPLDispProf_IsProphCons = 1, 
					@EvnPLDispProf_IsHypoten = null, 
					@EvnPLDispProf_IsLipid = null, 
					@EvnPLDispProf_IsHypoglyc = null, 
					@HealthKind_id = null, 
					@EvnPLDispProf_IsEndStage = 1, 
					@EvnPLDispProf_IsFinish = 1, 
					@EvnPLDispProf_consDT = :EvnPLDispProf_consDate,
					@EvnPLDispProf_IsMobile = :EvnPLDispProf_IsMobile,
					@EvnPLDispProf_IsOutLpu = :EvnPLDispProf_IsOutLpu,
					@Lpu_mid = :Lpu_mid,
					@CardioRiskType_id = null, 
					@DispClass_id = :DispClass_id,
					@PayType_id = :PayType_id,
					@EvnPLDispProf_fid = :EvnPLDispProf_fid,
					@EvnPLDispProf_IsNewOrder = :EvnPLDispProf_IsNewOrder,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as EvnPLDispProf_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
				'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnPLDispProf_setDate' => $data['EvnPLDispProf_consDate'],
				'EvnPLDispProf_consDate' => $data['EvnPLDispProf_consDate'],
				'EvnPLDispProf_IsMobile' => $data['EvnPLDispProf_IsMobile'],
				'EvnPLDispProf_IsOutLpu' => $data['EvnPLDispProf_IsOutLpu'],
				'Lpu_mid' => $data['Lpu_mid'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnPLDispProf_fid' => $data['EvnPLDispProf_fid'],
				'EvnPLDispProf_IsNewOrder' => $data['EvnPLDispProf_IsNewOrder'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (isset($resp[0]['EvnPLDispProf_id'])) {
					$data['EvnPLDispProf_id'] = $resp[0]['EvnPLDispProf_id'];
				} else {
					$this->db->trans_rollback();
					return $resp; // иначе выдаем.. там видимо ошибка
				}
			}

			if (getRegionNick() == 'penza') {
				//Отправить человека в очередь на идентификацию
				$this->load->model('Person_model', 'pmodel');
				$this->pmodel->isAllowTransaction = false;
				$resTmp = $this->pmodel->addPersonRequestData(array(
					'Person_id' => $data['Person_id'],
					'Evn_id' => $data['EvnPLDispProf_id'],
					'pmUser_id' => $data['pmUser_id'],
					'PersonRequestSourceType_id' => 3,
				));
				$this->pmodel->isAllowTransaction = true;
				if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
					$this->db->trans_rollback();
					return $resTmp[0];
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
				and (pd.PersonDisp_begDate <= :EvnPLDispProf_consDate OR pd.PersonDisp_begDate IS NULL)
				and (pd.PersonDisp_endDate >= :EvnPLDispProf_consDate OR pd.PersonDisp_endDate IS NULL)
				and pdiag.ProfileDiagGroup_id IS NULL
		";
		$result = $this->db->query($query, array(
			'EvnPLDispProf_consDate' => $data['EvnPLDispProf_consDate'],
			'Person_id' => $data['Person_id']
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Diag_id'])) {
				$data['EvnPLDisp_id'] = $data['EvnPLDispProf_id'];
                foreach ($resp as $item){
					$data['EvnDiagDopDisp_setDate'] = !empty($item['PersonDisp_begDate'])?date('Y-m-d', strtotime($item['PersonDisp_begDate'])):null;
                    $this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['Diag_id']);
                }
			}
		}
		
		// сохраняем данные по информир. добр. согласию для EvnPLDispProf_id = $data['EvnPLDispProf_id']
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
			// Добавил доп. условия, т.к. с клиента может приходить не только 0 и 1, но и true и false
			// https://redmine.swan.perm.ru/issues/22236
			if ( (!empty($item['DopDispInfoConsent_IsEarlier']) && $item['DopDispInfoConsent_IsEarlier'] == '1') || $item['DopDispInfoConsent_IsEarlier'] === true ) {
				$item['DopDispInfoConsent_IsEarlier'] = 2;
			} else {
				$item['DopDispInfoConsent_IsEarlier'] = 1;
			}
			
			if ( (!empty($item['DopDispInfoConsent_IsAgree']) && $item['DopDispInfoConsent_IsAgree'] == '1') || $item['DopDispInfoConsent_IsAgree'] === true ) {
				$item['DopDispInfoConsent_IsAgree'] = 2;
			} else {
				$item['DopDispInfoConsent_IsAgree'] = 1;
			}

			if ( (!empty($item['DopDispInfoConsent_IsImpossible']) && $item['DopDispInfoConsent_IsImpossible'] == '1') || $item['DopDispInfoConsent_IsImpossible'] === true ) {
				$item['DopDispInfoConsent_IsImpossible'] = 2;
			} else {
				$item['DopDispInfoConsent_IsImpossible'] = 1;
			}

			if ( $item['DopDispInfoConsent_IsEarlier'] == 2 || $item['DopDispInfoConsent_IsAgree'] == 2 || $item['DopDispInfoConsent_IsImpossible'] == 2 ) {
				$itemsCount++;
			}
			
			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispProf_id'], $item['SurveyTypeLink_id']);
			
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$DopDispInfoConsentList[] = $item['DopDispInfoConsent_id'];
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}
			
			// если убирают согласие для удалённого SurveyTypeLink, то удаляем его из DopDispInfoConsent. (refs #21573)
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0 && !empty($item['SurveyTypeLink_IsDel']) && $item['SurveyTypeLink_IsDel'] == '2' && $item['DopDispInfoConsent_IsEarlier'] == 1 && $item['DopDispInfoConsent_IsAgree'] == 1 && $item['DopDispInfoConsent_IsImpossible'] == 1) {
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
						@EvnPLDisp_id = :EvnPLDispProf_id, 
						@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
						@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
						@DopDispInfoConsent_IsImpossible = :DopDispInfoConsent_IsImpossible, 
						@SurveyTypeLink_id = :SurveyTypeLink_id, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
	 
					select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
					'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
					'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
					'DopDispInfoConsent_IsImpossible' => $item['DopDispInfoConsent_IsImpossible'],
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
			// Обновляем дату EvnPLDispProf_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select top 1
					 EvnPLDispProf_pid
					,EvnPLDispProf_rid
					,Lpu_id
					,Server_id
					,PersonEvn_id
					,convert(varchar(20), EvnPLDispProf_setDT, 120) as EvnPLDispProf_setDT
					,convert(varchar(20), EvnPLDispProf_disDT, 120) as EvnPLDispProf_disDT
					,convert(varchar(20), EvnPLDispProf_didDT, 120) as EvnPLDispProf_didDT
					,Morbus_id
					,EvnPLDispProf_IsSigned
					,EvnPLDispProf_IsNewOrder
					,EvnPLDispProf_IndexRep
					,EvnPLDispProf_IndexRepInReg
					,EvnDirection_aid
					,pmUser_signID
					,EvnPLDispProf_signDT
					,Person_Age
					,AttachType_id
					,Lpu_aid
					,DispClass_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsStenocard
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsDoubleScan
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsTub
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsEsophag
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsSmoking
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsRiskAlco
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsAlcoDepend
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsLowActiv
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsIrrational
					," . ($itemsCount == 0 ? "null as " : "") . "Diag_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsDisp
					," . ($itemsCount == 0 ? "null as " : "") . "NeedDopCure_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsStac
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsSanator
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_SumRick
					," . ($itemsCount == 0 ? "null as " : "") . "RiskType_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsSchool
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsProphCons
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsHypoten
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsLipid
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsHypoglyc
					," . ($itemsCount == 0 ? "null as " : "") . "HealthKind_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsEndStage
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispProf_IsFinish
					," . ($itemsCount == 0 ? "null as " : "") . "CardioRiskType_id
				from v_EvnPLDispProf with (nolock)
				where EvnPLDispProf_id = :EvnPLDispProf_id
			";
			$result = $this->db->query($query, array(
				'EvnPLDispProf_id' => $data['EvnPLDispProf_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 ) {
					$resp[0]['EvnPLDispProf_consDT'] = $data['EvnPLDispProf_consDate'];
					$resp[0]['EvnPLDispProf_IsNewOrder'] = $data['EvnPLDispProf_IsNewOrder'];
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					$resp[0]['EvnPLDispProf_IsMobile'] = $data['EvnPLDispProf_IsMobile'];
					$resp[0]['EvnPLDispProf_IsOutLpu'] = $data['EvnPLDispProf_IsOutLpu'];
					$resp[0]['Lpu_mid'] = $data['Lpu_mid'];
					$resp[0]['PayType_id'] = $data['PayType_id'];
					$resp[0]['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
							
						set @Res = :EvnPLDispProf_id;
						
						exec p_EvnPLDispProf_upd
							@EvnPLDispProf_id = @Res output, 
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "@" . $key . " = :" . $key . ",";
					}

					$query .= "
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
 
						select @Res as EvnPLDispProf_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

					$resp[0]['EvnPLDispProf_id'] = $data['EvnPLDispProf_id'];

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
				$attrArray[] = 'DopDispInfoConsent';
			}

			foreach ( $attrArray as $attr ) {
				$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispProf_id'], $data['pmUser_id']);

				if ( !empty($deleteResult) ) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
					);
				}
			}
			
			if ( $itemsCount > 0 && count($DopDispInfoConsentToDel) > 0 ) {
				foreach ( $DopDispInfoConsentToDel as $DopDispInfoConsent_id ) {
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec p_DopDispInfoConsent_del
							@DopDispInfoConsent_id = :DopDispInfoConsent_id, 
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
		 
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array(
						'DopDispInfoConsent_id' => $DopDispInfoConsent_id,
						'pmUser_id' => $data['pmUser_id']
					));

					if ( is_object($result) ) {
						$res = $result->result('array');

						if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $res[0]['Error_Msg']
							);
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
		}
		
		// проставляем признак отказа
		if ( $itemsCount == 0 ) {
			$query = "
				update EvnPLDisp with (rowlock) set EvnPLDisp_IsRefusal = 2 where EvnPLDisp_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispProf_id']
			));
		} else {
			$query = "
				update EvnPLDisp with (rowlock) set EvnPLDisp_IsRefusal = 1 where EvnPLDisp_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispProf_id']
			));
		}
		
		// Определяем записи, которые необходимо удалить
		// https://redmine.swan.perm.ru/issues/29017
		if ( count($DopDispInfoConsentList) > 0 ) {
			$query = "
				select
					 DDIC.DopDispInfoConsent_id
					,EVDD.EvnVizitDispDop_id
					,EUDD.EvnUslugaDispDop_id
					,STL.SurveyType_id
				from v_DopDispInfoConsent DDIC with (nolock)
					left join v_EvnVizitDispDop EVDD with (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_EvnUslugaDispDop EUDD with (nolock) on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					left join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				where DDIC.EvnPLDisp_id = :EvnPLDisp_id
					and DDIC.DopDispInfoConsent_id not in (" . implode(', ', $DopDispInfoConsentList) . ")
			";
			$result = $this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispProf_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
				);
			}

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				foreach ( $res as $array ) {
					// Удаляем посещения
					if ( !empty($array['EvnVizitDispDop_id']) ) {
						$resp_ddic = array();
						if (!empty($data['EvnPLDispProf_IsNewOrder']) && $data['EvnPLDispProf_IsNewOrder'] == 2 && !empty($array['SurveyType_id'])) {
							// Попытаемся найти новое согласие для посещения
							$resp_ddic = $this->queryResult("
								select top 1
									DDIC.DopDispInfoConsent_id,
									STL.UslugaComplex_id
								from
									v_DopDispInfoConsent DDIC with (nolock)
									inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
									left join v_EvnVizitDispDop EVDD with (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								where
									DDIC.EvnPLDisp_id = :EvnPLDisp_id
									and STL.SurveyType_id = :SurveyType_id
									and EVDD.EvnVizitDispDop_id is null
							", array(
								'EvnPLDisp_id' => $data['EvnPLDispProf_id'],
								'SurveyType_id' => $array['SurveyType_id']
							));
						}

						if (!empty($resp_ddic[0]['DopDispInfoConsent_id'])) {
							// перевяжем услугу к новому согласию
							$query = "
								declare
									@ErrCode int,
									@ErrMsg varchar(400);
					
								set nocount on;
					
								begin try
								
								update
									EvnVizitDisp with (rowlock)
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									EvnVizitDisp_id = :EvnVizitDispDop_id
									
								update
									EvnVizitDispDop with (rowlock)
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									EvnVizitDispDop_id = :EvnVizitDispDop_id
									
								update
									EvnUslugaDispDop with (rowlock)
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									EvnUslugaDispDop_id = :EvnUslugaDispDop_id
									
								update
									EvnUsluga with (rowlock)
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									EvnUsluga_id = :EvnUslugaDispDop_id
									
								end try
								begin catch
									set @ErrCode = error_number();
									set @ErrMsg = error_message();
								end catch
					
								set nocount off;
					
								select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
							";
							$result = $this->db->query($query, array(
								'EvnVizitDispDop_id' => $array['EvnVizitDispDop_id'],
								'EvnUslugaDispDop_id' => $array['EvnUslugaDispDop_id'],
								'UslugaComplex_id' => $resp_ddic[0]['UslugaComplex_id'],
								'DopDispInfoConsent_id' => $resp_ddic[0]['DopDispInfoConsent_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!is_object($result)) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
								);
							}

							$resTmp = $result->result('array');

							if (is_array($resTmp) && count($resTmp) > 0 && !empty($resTmp[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => $resTmp[0]['Error_Msg']
								);
							}
						} else {
							$query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
	
								exec p_EvnVizitDispDop_del
									@EvnVizitDispDop_id = :EvnVizitDispDop_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
	
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";
							$result = $this->db->query($query, array('EvnVizitDispDop_id' => $array['EvnVizitDispDop_id'], 'pmUser_id' => $data['pmUser_id']));

							if (!is_object($result)) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
								);
							}

							$resTmp = $result->result('array');

							if (is_array($resTmp) && count($resTmp) > 0 && !empty($resTmp[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => $resTmp[0]['Error_Msg']
								);
							}
						}
					}

					// Удаляем записи информированного добровольного согласия
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec p_DopDispInfoConsent_del
							@DopDispInfoConsent_id = :DopDispInfoConsent_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
		 
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array(
						'DopDispInfoConsent_id' => $array['DopDispInfoConsent_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_object($result) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}

					$resTmp = $result->result('array');

					if ( is_array($resTmp) && count($resTmp) > 0 && !empty($resTmp[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => $resTmp[0]['Error_Msg']
						);
					}
				}
			}
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispProf_id' => $data['EvnPLDispProf_id']
		);
	}

	/**
	 *	Получение диагноза по коду
	 */	
	function getDiagIdByCode($diag_code)
	{
		$query = "
			select top 1
				Diag_id
			from v_Diag (nolock)
			where Diag_Code = :Diag_Code
		";
		
		$result = $this->db->query($query, array('Diag_Code' => $diag_code));
	
        if (is_object($result))
        {
            $resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['Diag_id'];
			}
        }
		
		return false;
	}
	
	/**
	 *	Получение данных карты
	 */	
	function getEvnPLDispProfData($data)
	{
		$query = "
			SELECT TOP 1
				EvnPLDispProf_id,
				PersonEvn_id,
				Server_id
			FROM
				v_EvnPLDispProf EPLDD (nolock)
			WHERE
				EPLDD.EvnPLDispProf_id = :EvnPLDisp_id
		";
        $result = $this->db->query($query, $data);

        if (is_object($result))
        {
            $resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
        }
		
		return false;
	}
	
	/**
	 *	Сохранение анкетирования
	 */	
	function saveDopDispQuestionGrid($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();

		// получаем данные о карте ДД
		$dd = $this->getEvnPLDispProfData($data);

		if ( empty($dd) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка получения данных карты диспансеризации');
		}

		$data['PersonEvn_id'] = $dd['PersonEvn_id'];
		$data['Server_id'] = $dd['Server_id'];
		
		$sql = "
			select top 1
				evdd.EvnVizitDispDop_id,
			    eudd.EvnUslugaDispDop_id,
			    stl.DispClass_id,
			    st.SurveyType_Code
			from
				v_EvnUslugaDispDop eudd (nolock)
				inner join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
			where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
			  and st.SurveyType_Code in (19,27)
			  and cast(eudd.EvnUslugaDispDop_didDT as date) < :DopDispQuestion_setDate
		";
		$sqlParams = [
			"EvnVizitDispDop_pid" => $data["EvnPLDisp_id"],
			"DopDispQuestion_setDate" => $data["DopDispQuestion_setDate"]
		];
		/**
		 * @var CI_DB_result $res
		 * @var CI_DB_result $result
		 */
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result_array();
			if (is_array($resp) && count($resp) > 0) {
				$this->db->trans_rollback();
				if ($resp[0]["SurveyType_Code"] == 19) {
					throw new Exception("Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП).");
				} else {
					throw new Exception("Дата любого осмотра / исследования не может быть больше даты осмотра врача-педиатра (ВОП).");
				}
			}
		}

		// Нужно сохранять услугу по анкетированию (refs #20465)
		// Ищем услугу с UslugaComplex_id для SurveyType_Code = 2, если нет то создаём новую, иначе обновляем.
		$query = "
			select top 1
				STL.UslugaComplex_id, -- услуга которую нужно сохранить
				EUDDData.EvnUslugaDispDop_id
			from v_SurveyTypeLink STL (nolock)
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				outer apply(
					select top 1 
						EvnUslugaDispDop_id
					from
						v_EvnUslugaDispDop (nolock) EUDD
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id and EUDD.UslugaComplex_id IN (select UslugaComplex_id from v_SurveyTypeLink (nolock) where SurveyType_id = STL.SurveyType_id)
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where
				ST.SurveyType_Code = 2
				and ddic.EvnPLDisp_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)');
		}

		$resp = $result->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при получении идентификатора услуги');
		}

		// сохраняем услугу
		if ( !empty($resp[0]['EvnUslugaDispDop_id']) ) {
			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
			$proc = 'p_EvnUslugaDispDop_upd';
		}
		else {
			$data['EvnUslugaDispDop_id'] = null;
			$proc = 'p_EvnUslugaDispDop_ins';
		}

		if (empty($data['UslugaComplex_id'])) {
			$data['UslugaComplex_id'] = $resp[0]['UslugaComplex_id'];
		}

		$query = "
			declare
				@EvnUslugaDispDop_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnUslugaDispDop_id = :EvnUslugaDispDop_id;
			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
			exec " . $proc . "
				@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
				@EvnUslugaDispDop_pid = :EvnPLDisp_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@EvnDirection_id = NULL,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = @PayType_id,
				@UslugaPlace_id = 1,
				@EvnUslugaDispDop_setDT = NULL,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaDispDop_didDT = :DopDispQuestion_setDate,
				@ExaminationPlace_id = NULL,
				@Diag_id = :Diag_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@EvnUslugaDispDop_DeseaseStage = :DeseaseStage,
				@LpuSection_uid = :LpuSection_uid,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnUslugaDispDop_ExamPlace = NULL,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)');
		}

		$resp = $result->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
		}

		if (!empty($resp[0]['EvnUslugaDispDop_id'])) {
			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
		} else {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
		}

		ConvertFromWin1251ToUTF8($data['DopDispQuestionData']);
		$items = json_decode($data['DopDispQuestionData'], true);
		
		$this->load->model('EvnDiagDopDisp_model', 'evndiagdopdisp');
		$this->load->model('HeredityDiag_model', 'hereditydiag');
		
		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = array();

		$query = "
			select
				 QuestionType_id
				,DopDispQuestion_id
				,DopDispQuestion_ValuesStr
			from v_DopDispQuestion with (nolock)
			where EvnPLDisp_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка имеющихся данных анкетирования)');
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 ) {
			foreach ( $resp as $dataArray ) {
				if ($dataArray['QuestionType_id'] == 50 && !empty($data['NeedCalculation']) && $data['NeedCalculation'] == 1) {
					$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $dataArray['DopDispQuestion_ValuesStr']);
				}
				$ExistingDopDispQuestionData[$dataArray['QuestionType_id']] = $dataArray['DopDispQuestion_id'];
			}
		}
		
		$data['EvnDiagDopDisp_setDate'] = $data['DopDispQuestion_setDate'];

		foreach($items as $item) {
			if (!empty($data['NeedCalculation']) && $data['NeedCalculation'] == 1) {
				switch($item['QuestionType_id']) {
					/*
						1. Ранее известные имеющиеся заболевания, подраздел
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» на следующие вопросы:
							– №2: I20.9
							– №3: Z03.4
							– №4: I67.9
							– №5: E10.0   upd: E14.9 ибо https://redmine.swan.perm.ru/issues/19459#note-74
							– №6: значение брать из анкеты
							– №7: A16.2
					*/
					case 46:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
						}
					break;
					case 47:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
						}
					break;
					case 48:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
						}
					break;
					case 49:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9'));
						}
					break;
					case 50:
						if ($item['DopDispQuestion_IsTrue'] == 2) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['DopDispQuestion_ValuesStr']);
						}
					break;
					case 51:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
						}
					break;
					/* 
						2. Наследственность по заболеваниям, подраздел		
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» или «не знаю» на следующие вопросы:
						– №8: Z03.4, «Да» - «отягощена», «не знаю» - «не известно»
						– №9: I64., «Да» - «отягощена», «не знаю» - «не известно»
						– №10: C16.9, «Да» - «отягощена», «не знаю» - «не известно»
					*/
					case 52:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('Z03.4'), ($item['DopDispQuestion_ValuesStr']==2)?1:2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('Z03.4'));
						}
					break;
					case 53:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('I64.'), ($item['DopDispQuestion_ValuesStr']==2)?1:2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('I64.'));
						}
					break;
					case 54:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('C16.9'), ($item['DopDispQuestion_ValuesStr']==2)?1:2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('C16.9'));
						}
					break;
					/*
						3. Показания к консультации врача-специалиста, подраздел. // TODO пока непонятно, т.к. ссылку добавили на службу вместо специальности врача: "ну потому что когда мы обсуждали это с Тарасом он сказал что у нас вместо выбора специальности врача, реализованы службы" (c) Света Корнелюк
						Примечание. Автоматически создавать поля списка по результатам анкетирования (при нажатии функциональной кнопки «Рассчитать») – При указании значения «Да» на следующие вопросы:
							– хотя бы на один из вопросов №22,23,24 или хотя бы на один из вопросов №41,42,43: «хирург» - «второй этап диспансеризации»
							– на все вопросы №27,28,29,30 или хотя бы на два из №36-40: «психиатр-нарколог» - «вне программы диспансеризации»
					*/
				}
			}
			
			if ( array_key_exists($item['QuestionType_id'], $ExistingDopDispQuestionData) ) {
				$item['DopDispQuestion_id'] = $ExistingDopDispQuestionData[$item['QuestionType_id']];
			}

			$item['DopDispQuestion_Answer'] = toAnsi($item['DopDispQuestion_Answer']);

			if ( !empty($item['DopDispQuestion_id']) && $item['DopDispQuestion_id'] > 0 ) {
				$proc = 'p_DopDispQuestion_upd';
			}
			else {
				$proc = 'p_DopDispQuestion_ins';
				$item['DopDispQuestion_id'] = null;
			}

			if (empty($item['DopDispQuestion_IsTrue'])) {
				$item['DopDispQuestion_IsTrue'] = null;
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispQuestion_id;
				
				exec {$proc}
					@DopDispQuestion_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDisp_id, 
					@QuestionType_id = :QuestionType_id, 
					@DopDispQuestion_IsTrue = :DopDispQuestion_IsTrue, 
					@DopDispQuestion_Answer = :DopDispQuestion_Answer, 
					@DopDispQuestion_ValuesStr = :DopDispQuestion_ValuesStr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as DopDispQuestion_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'DopDispQuestion_id' => $item['DopDispQuestion_id'],
				'QuestionType_id' => $item['QuestionType_id'],
				'DopDispQuestion_IsTrue' => $item['DopDispQuestion_IsTrue'],
				'DopDispQuestion_Answer' => $item['DopDispQuestion_Answer'],
				'DopDispQuestion_ValuesStr' => $item['DopDispQuestion_ValuesStr'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение ответов на вопросы)');
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении ответов на вопросы');
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		}

		// http://redmine.swan.perm.ru/issues/84088
		// Добавляем повторную проверку на наличие дублей
		$sql = "
			select top 1 EvnUslugaDispDop_id
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_pid = :EvnPLDisp_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnUslugaDispDop_id != :EvnUslugaDispDop_id
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих посещений)'));
		}

		$resp = $res->result('array');

		if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnUslugaDispDop_id']) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Обнаружены дубль по услуге анкетирования. Произведен откат транзакции. Пожалуйста, повторите сохранение.'));
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => ''
		);
	}
	
	/**
	 *	Загрузка согласий
	 */	
	function loadDopDispInfoConsent($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id']
		);

		if (!empty($data['EvnPLDispProf_IsNewOrder']) && $data['EvnPLDispProf_IsNewOrder'] == 2) {
			$data['EvnPLDispProf_consDate'] = date('Y', strtotime($data['EvnPLDispProf_consDate'])) . '-12-31';
		}

		$params['EvnPLDispProf_consDate'] = $data['EvnPLDispProf_consDate'];

		if ( $data['session']['region']['nick'] == 'ufa' ) { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel
			$filter .= " and (ISNULL(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel with (nolock) on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$noFilterByAgeInFirstTime = "";
		if (strtotime($data['EvnPLDispProf_consDate']) >= strtotime('01.04.2015')) {
			$this->load->model('EvnPLDispDop13_model');
			if (!$this->EvnPLDispDop13_model->checkIsPrimaryFlow(array(
				'Person_id' => $data['Person_id'],
				'EvnPLDisp_id' => $data['EvnPLDispProf_id']
			))) {
				$noFilterByAgeInFirstTime = "or STL.SurveyTypeLink_IsPrimaryFlow = 2";
			}
		}

		$query = "
			Declare @sex_id bigint, @age int

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, cast(substring(:EvnPLDispProf_consDate, 1, 4) + '-12-31' as datetime))
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id

			select
				COALESCE(DDIC.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as DopDispInfoConsent_id,
				DDIC.EvnPLDisp_id as EvnPLDispProf_id,
				STL.SurveyTypeLink_id as SurveyTypeLink_id,
				STL.SurveyTypeLink_IsImpossible as SurveyTypeLink_IsImpossible,
				COALESCE(STL.SurveyTypeLink_IsNeedUsluga, 1) as SurveyTypeLink_IsNeedUsluga,
				COALESCE(STL.SurveyTypeLink_IsDel, 1) as SurveyTypeLink_IsDel,
				ST.SurveyType_Code as SurveyType_Code,
				ST.SurveyType_Name as SurveyType_Name,
				case WHEN DDIC.DopDispInfoConsent_id is null or DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier,
				case WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then 1 else 0 end as DopDispInfoConsent_IsImpossible,
				case WHEN STL.SurveyTypeLink_IsImpossible = 2 then '' else 'disabled' end as DopDispInfoConsent_IsImpossible_disabled,
				case when STL.SurveyTypeLink_IsPrimaryFlow = 2 and STL.IsAgeCorrect = 0 then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect
			from
				v_SurveyType ST with(nolock)
				cross apply (
					select top 1
						STL.*,
						IsAgeCorrect.Value as IsAgeCorrect
					from
						v_SurveyTypeLink STL with(nolock)
						outer apply (
							select case when
								@age between COALESCE(STL.SurveyTypeLink_From, 0) and COALESCE(STL.SurveyTypeLink_To, 999)
							then 1 else 0 end as Value
						) IsAgeCorrect
					where
						STL.SurveyType_id = ST.SurveyType_id
						and COALESCE(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
						and (COALESCE(STL.Sex_id, @sex_id) = @sex_id) -- по полу
						and (IsAgeCorrect.Value = 1 {$noFilterByAgeInFirstTime})
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispProf_consDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispProf_consDate)
						and coalesce(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
					order by
						IsAgeCorrect.Value desc,
						abs(STL.SurveyTypeLink_From - @age)
				) STL
				left join v_DopDispInfoConsent DDIC with(nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispProf_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				outer apply (
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop with(nolock)
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispProf_id
						and COALESCE(EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				" . implode(' ', $joinList) . "
			where
				(COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				{$filter}
			order by
				case when ST.SurveyType_Code IN (49) then 0 else 1 end, ST.SurveyType_Code
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
	 *	Удаление карты
	 */	
	function deleteEvnPLDispProf($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispProf_del
				@EvnPLDispProf_id = :EvnPLDispProf_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)');
		}
		
		$attrArray = array(
			'HeredityDiag',
			'ProphConsult',
			'NeedConsult',
			'DopDispInfoConsent'
		);
		foreach ( $attrArray as $attr ) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispProf_id'], $data['pmUser_id']);

			if ( !empty($deleteResult) ) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
				);
			}
		}
	}

	
	/**
	 * Получение талонов ДД для истории лечения человека
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив талонов ДД человека
	 */
	function loadEvnPLDispProfForPerson($data) {
		$query = "
			select
					[EPLDD].[EvnPLDispProf_id] as [EvnPLDispProf_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD].[EvnPLDispProf_VizitCount] as [EvnPLDispProf_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispProf_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispProf_setDate], 104) as [EvnPLDispProf_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispProf_disDate], 104) as [EvnPLDispProf_disDate]
			from
							v_PersonState PS with (nolock)
						inner join [v_EvnPLDispProf] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispProf_IsFinish]
			where
				(1 = 1)
				and [EPLDD].Person_id = :Person_id
			order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение данных для формы просмотра карты
	 */	
	function loadEvnPLDispProfEditForm($data)
	{
		$accessType = '
			case
				when EPLDD13.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDD13.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EPLDD13.EvnPLDispProf_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';
		
		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and ISNULL(EPLDD13.EvnPLDispProf_isPaid, 1) = 1";
		}
		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= "and ISNULL(EPLDD13.EvnPLDispProf_isPaid, 1) = 1
				and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDD13.EvnPLDispProf_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}
		
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLDD13.EvnPLDispProf_id,
				ISNULL(EPLDD13.EvnPLDispProf_IsPaid, 1) as EvnPLDispProf_IsPaid,
				ISNULL(EPLDD13.EvnPLDispProf_IsNewOrder, 1) as EvnPLDispProf_IsNewOrder,
				ISNULL(EPLDD13.EvnPLDispProf_IndexRep, 0) as EvnPLDispProf_IndexRep,
				ISNULL(EPLDD13.EvnPLDispProf_IndexRepInReg, 1) as EvnPLDispProf_IndexRepInReg,
				EPLDD13.Person_id,
				EPLDD13.PersonEvn_id,
				ISNULL(EPLDD13.DispClass_id, 5) as DispClass_id,
				EPLDD13.PayType_id,
				EPLDD13.EvnPLDispProf_pid,
				convert(varchar(10), EPLDD13.EvnPLDispProf_setDate, 104) as EvnPLDispProf_setDate,
				convert(varchar(10), EPLDD13.EvnPLDispProf_disDate, 104) as EvnPLDispProf_disDate,
				convert(varchar(10), EPLDD13.EvnPLDispProf_consDT, 104) as EvnPLDispProf_consDate,
				EPLDD13.Server_id,
				case when EPLDD13.EvnPLDispProf_IsMobile = 2 then 1 else 0 end as EvnPLDispProf_IsMobile,
				case when EPLDD13.EvnPLDispProf_IsOutLpu = 2 then 1 else 0 end as EvnPLDispProf_IsOutLpu,
				EPLDD13.Lpu_mid,
				EPLDD13.EvnPLDispProf_IsStenocard,
				EPLDD13.EvnPLDispProf_IsDoubleScan,
				EPLDD13.EvnPLDispProf_IsTub,
				EPLDD13.EvnPLDispProf_IsEsophag,
				EPLDD13.EvnPLDispProf_IsSmoking,
				EPLDD13.EvnPLDispProf_IsRiskAlco,
				EPLDD13.EvnPLDispProf_IsAlcoDepend,
				EPLDD13.EvnPLDispProf_IsLowActiv,
				EPLDD13.EvnPLDispProf_IsIrrational,
				EPLDD13.Diag_id,
				EPLDD13.EvnPLDispProf_IsDisp,
				EPLDD13.NeedDopCure_id,
				EPLDD13.EvnPLDispProf_IsStac,
				EPLDD13.EvnPLDispProf_IsSanator,
				EPLDD13.EvnPLDispProf_SumRick,
				EPLDD13.RiskType_id,
				EPLDD13.EvnPLDispProf_IsSchool,
				EPLDD13.EvnPLDispProf_IsProphCons,
				EPLDD13.EvnPLDispProf_IsHypoten,
				EPLDD13.EvnPLDispProf_IsLipid,
				EPLDD13.EvnPLDispProf_IsHypoglyc,
				EPLDD13.HealthKind_id,
				EPLDD13.EvnPLDispProf_IsEndStage,
				EPLDD13.EvnPLDispProf_IsFinish,
				EPLDD13.CardioRiskType_id,
				EPLDD13.EvnPLDispProf_IsSuspectZNO,
				EPLDD13.Diag_spid,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint
			FROM
				v_EvnPLDispProf EPLDD13 (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDD13.EvnPLDispProf_id
			WHERE
				(1 = 1)
				and EPLDD13.EvnPLDispProf_id = :EvnPLDispProf_id
		";
		//echo getDebugSQL($query, array( 'EvnPLDispProf_id' => $data['EvnPLDispProf_id'])); exit();
        $result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'EvnPLDispProf_id' => $data['EvnPLDispProf_id']));

        if (is_object($result))
        {
            $resp = $result->result('array');
			// нужно получить значения результатов услуг из EvnUslugaRate
			if (isset($resp[0]['EvnPLDispProf_id'])) {
				$query = "
					select 
						RT.RateType_SysNick as nick,
						RVT.RateValueType_SysNick,
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from v_DopDispInfoConsent DDIC (nolock)
						left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
						outer apply(
							select top 1
								EUDD.EvnUslugaDispDop_id
							from v_EvnUslugaDispDop EUDD (nolock)
								inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							where
								EVDD.EvnVizitDispDop_pid = :EvnPLDispProf_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						) EUDDData
						left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
						left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
						left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
						left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispProf_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code <> 49
						and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
				";
				//echo getDebugSQL($query, array( 'EvnPLDispProf_id' => $resp[0]['EvnPLDispProf_id'])); exit();
				$result = $this->db->query($query, array(
					'EvnPLDispProf_id' => $resp[0]['EvnPLDispProf_id']
				));
				if ( is_object($result) ) {
					$results = $result->result('array');
					foreach($results as $oneresult) {
						if ($oneresult['RateValueType_SysNick'] == 'float') {
							if ( $oneresult['nick'] == 'bio_blood_kreatinin' ) {
								// Ничего не делаем
							}
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
						}

						$resp[0][$oneresult['nick']] = $oneresult['value'];
					}
				}
			}
			
			return $resp;
        }
 	    else
 	    {
            return false;
        }
	}
	
	/**
	 *	Получение полей карты
	 */	
	function getEvnPLDispProfFields($data)
	{
		$query = "
			SELECT TOP 1
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(isnull(lp1.Lpu_Name, '')) as Lpu_AName,
				rtrim(isnull(addr1.Address_Address, '')) as Lpu_AAddress,
				rtrim(lp.Lpu_OGRN) as Lpu_OGRN,
				isnull(pc.PersonCard_Code, '') as PersonCard_Code,
				ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_FIO,
				sx.Sex_Name,
				isnull(osmo.OrgSMO_Nick, '') as OrgSMO_Nick,
				isnull(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as Polis_Ser,
				isnull(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as Polis_Num,
				isnull(osmo.OrgSMO_Name, '') as OrgSMO_Name,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				isnull(addr.Address_Address, '') as Person_Address,
				jborg.Org_Nick,
				case when EPLDD.EvnPLDispProf_IsBud = 2 then 'Да' else 'Нет' end as EvnPLDispProf_IsBud,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispProf_disDate, 104) as EvnPLDispProf_disDate
			FROM
				v_EvnPLDispProf EPLDD (nolock)
				inner join v_Lpu lp (nolock) on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 (nolock) on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 (nolock) on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc (nolock) on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps (nolock) on ps.Person_id = EPLDD.Person_id
				inner join Sex sx (nolock) on sx.Sex_id = ps.Sex_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo (nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr (nolock) on addr.Address_id = ps.PAddress_id
				left join Job jb (nolock) on jb.Job_id = ps.Job_id
				left join Org jborg (nolock) on jborg.Org_id = jb.Org_id
				left join AttachType atype (nolock) on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispProf_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispProf_id'], $data['Lpu_id']));

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
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispProf_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopGrid($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispProf_id']));

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
	 * Получение данных для редактирования посещения врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnVizitDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopEditForm($data)
	{
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		
		$query = "
			select top 1
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as RecordStatus,
				case when EVZDD.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EVZDD.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id and MP.Lpu_id = EVZDD.Lpu_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
		";
		$result = $this->db->query($query, array('EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'], 'Lpu_id' => $data['Lpu_id']));

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
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispProf_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopData($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(isnull(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispProf_id']));

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
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispProf_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$queryunion = "";
		if ($this->regionNick == 'ekb' && !empty($data['isDopUsl'])) {
			// считаем услугу цитологическое исследование
			$queryunion .= "
				union

				select top 1
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
					isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
					case when (STL.SurveyTypeLink_IsPrimaryFlow = 2 and @age not between Isnull(STL.SurveyTypeLink_From, 0) and  Isnull(STL.SurveyTypeLink_To, 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect
				from v_DopDispInfoConsent DDIC (nolock)
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					outer apply (
						Select top 1 * from v_EvnDirection ed (nolock) where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13)
					) ed
					outer apply (
						select top 1
							ep.EvnPrescr_id,
							Evn.Evn_pid as EvnPrescr_pid,
							ep.PrescriptionType_id,
							ed2.EvnDirection_id
						from
							EvnPrescr ep (nolock)
							inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
							outer apply(
								Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock)
								inner join v_EvnDirection_all ed2 (nolock) on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
								where epd.EvnPrescr_id = ep.EvnPrescr_id
							) ed2
						where
							ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					) ep
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
							EUDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
					left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
				where DDIC.EvnPLDisp_id = :EvnPLDispProf_id
					and STL.SurveyTypeLink_ComplexSurvey = 2
					and EUDDData.EvnUslugaDispDop_id is not null
			";
		}

		$query = "
			Declare @age int;

			select top 1
				@age = dbo.Age2(Person_BirthDay, cast(cast(year(epldp.EvnPLDispProf_consDT) as varchar) + '-12-31' as datetime))
			from
				v_EvnPLDispProf epldp (nolock) 
				inner join v_PersonState ps (nolock) on ps.Person_id = epldp.Person_id
			where
				epldp.EvnPLDispProf_id = :EvnPLDispProf_id;
			
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
				ST.OrpDispSpec_id,
				/*
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				*/
				case when (STL.SurveyTypeLink_IsPrimaryFlow = 2 and @age not between Isnull(STL.SurveyTypeLink_From, 0) and  Isnull(STL.SurveyTypeLink_To, 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				/*
				outer apply (
					Select top 1 * from v_EvnDirection ed (nolock) where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13)
				) ed
				outer apply (
					select top 1
						ep.EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep (nolock)
						inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
						outer apply(
							Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock) 
							inner join v_EvnDirection_all ed2 (nolock) on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.EvnPrescr_id
						) ed2
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				*/
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
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispProf_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispProf_id
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
				ST.OrpDispSpec_id,
				/*
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id,
				*/
				case when (STL.SurveyTypeLink_IsPrimaryFlow = 2 and @age not between Isnull(STL.SurveyTypeLink_From, 0) and  Isnull(STL.SurveyTypeLink_To, 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				/*
				outer apply (
					Select top 1 * from v_EvnDirection ed (nolock) where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13)
				) ed
				outer apply (
					select top 1
						ep.EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep (nolock)
						inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
						outer apply(
							Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock) 
							inner join v_EvnDirection_all ed2 (nolock) on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.EvnPrescr_id
						) ed2
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				*/
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
						EVDD.EvnVizitDispDop_pid = :EvnPLDispProf_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispProf_id
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and ST.SurveyType_Code NOT IN (2, 49)
				and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			{$queryunion}
		";
		
		$result = $this->db->query($query, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
	
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
	 *	Список карт для поточного ввода
	 */	
	function loadEvnPLDispProfStreamList($data)
	{
		$filter = '';
		$queryParams = array();

       	$filter .= " and [EPL].[pmUser_insID] = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']) )
		{
        	$filter .= " and [EPL].[EvnPL_insDT] >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

        if ( isset($data['Lpu_id']) )
        {
        	$filter .= " and [EPL].[Lpu_id] = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
        	SELECT DISTINCT TOP 100
				[EPL].[EvnPL_id] as [EvnPL_id],
				[EPL].[Person_id] as [Person_id],
				[EPL].[Server_id] as [Server_id],
				[EPL].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([EPL].[EvnPL_NumCard]) as [EvnPL_NumCard],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				convert(varchar(10), [EPL].[EvnPL_setDate], 104) as [EvnPL_setDate],
				convert(varchar(10), [EPL].[EvnPL_disDate], 104) as [EvnPL_disDate],
				[EPL].[EvnPL_VizitCount] as [EvnPL_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPL_IsFinish]
			FROM [v_EvnPL] [EPL] (nolock)
				inner join [v_PersonState] [PS] (nolock) on [PS].[Person_id] = [EPL].[Person_id]
				left join [YesNo] [IsFinish] (nolock) on [IsFinish].[YesNo_id] = [EPL].[EvnPL_IsFinish]
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY [EPL].[EvnPL_id] desc
    	";
        $result = $this->db->query($query, $queryParams);

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
	 *	Список посещений
	 */	
	function loadEvnVizitPLDispDopGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.ProfGoal_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.EvnVizitPL_Time,
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(PT.PayType_Name) as PayType_Name,
				RTrim(ST.ServiceType_Name) as ServiceType_Name,
				RTrim(VT.VizitType_Name) as VizitType_Name,
				1 as Record_Status
			from v_EvnVizitPL EVPL (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

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
	 *	Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id,
				SocStatus_id,
				ps.UAddress_id as Person_UAddress_id,
				ps.Polis_Ser,
				ps.Polis_Num,
				o.Org_Name,
				o.Org_INN,
				o.Org_OGRN,
				o.UAddress_id as Org_UAddress_id,
				o.Okved_id,
				os.OrgSmo_Name,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
				+ case when month(ps.Person_Birthday) > month(dbo.tzGetDate())
				or (month(ps.Person_Birthday) = month(dbo.tzGetDate()) and day(ps.Person_Birthday) > day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
			from v_PersonState ps (nolock)
			left join v_Job j (nolock) on j.Job_id=ps.Job_id
			left join v_Org o (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where ps.Person_id = ?
		";

		$result = $this->db->query($query, array($data['Person_id']));
		$response = $result->result('array');
		
		if ( !is_array($response) || count($response) == 0 )
			return array(array('Error_Msg' => 'Ошибка при проверке персональных данных человека!'));
		
		$error = Array();
		if (ArrayVal($response[0], 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($response[0], 'SocStatus_id') == '')
			$errors[] = 'Не заполнен Соц. статус';
		if (ArrayVal($response[0], 'Person_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($response[0], 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($response[0], 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($response[0], 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		if (ArrayVal($response[0], 'Org_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес места работы';
		if (ArrayVal($response[0], 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН места работы';
		if (ArrayVal($response[0], 'Org_OGRN') == '')
			$errors[] = 'Не заполнена ОГРН места работы';
		if (ArrayVal($response[0], 'Okved_id') == '')
			$errors[] = 'Не заполнен ОКВЭД места работы';
		
		If (count($error)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array(array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>'.$errstr));
		}
		return array( "Ok", ArrayVal($response[0], 'Sex_id'), ArrayVal($response[0], 'Person_Age'), ArrayVal($response[0], 'Person_Birthday') );
	}

	/**
	 * Проверка атрибута у отделения
	 */
	function checkAttributeforLpuSection($data)
	{
		$query = "
			select 
				EVZDD.EvnVizitDispDop_didDT,
				ASVal.AttributeSign_id,
				ASVal.AttributeSignValue_begDate,
				ASVal.AttributeSignValue_endDate 
			from
				v_EvnVizitDispDop EVZDD with(nolock)
				left join LpuSection LS with(nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				outer apply (
					select 
						[AS].AttributeSign_id,
						ASV.AttributeSignValue_begDate,
						ASV.AttributeSignValue_endDate 
					from
						v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
					where
						[AS].AttributeSign_TableName = 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = EVZDD.LpuSection_id
						and [AS].AttributeSign_Name = 'Передвижные подразделения'
				) ASVal
			where
				EVZDD.EvnVizitDispDop_pid = :EvnPLDispProf_id
		";

		$result = $this->db->query($query, array('EvnPLDispProf_id'=>$data['EvnPLDispProf_id']));
		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {

			$col_lpusection=0;
			foreach($response as $res) {
				if (
					!empty($res['AttributeSign_id'])
					&& (is_null($res['AttributeSignValue_begDate']) || (isset($res['AttributeSignValue_begDate']) && $res['AttributeSignValue_begDate']<=$res['EvnVizitDispDop_didDT']))
					&& (is_null($res['AttributeSignValue_endDate']) || (isset($res['AttributeSignValue_endDate']) && $res['AttributeSignValue_endDate']>=$res['EvnVizitDispDop_didDT']))
				){
					$col_lpusection++;
				}
			}

			if(count($response)==$col_lpusection){
				return 'Все осмотры и исследования карты обслужены мобильной бригадой. Установить флаг "Случай обслужен мобильной бригадой" для всей карты?';
			}
		}

		return array( "Ok");
	}
	
	/**
	 *	Получение минимальной, максимальной дат
	 */
	function getEvnUslugaDispDopMinMaxDates($data)
	{
		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			select
				convert(varchar(10),ISNULL(MIN(EUDDData.EvnUslugaDispDop_didDate), @getdate),120) as mindate,
				convert(varchar(10),ISNULL(MAX(EUDDData.EvnUslugaDispDop_didDate), @getdate),120) as maxdate
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_didDate
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispProf_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where
				DDIC.EvnPLDisp_id = :EvnPLDispProf_id
				and ST.SurveyType_Code <> 49
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
		";

		$result = $this->db->query($query, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}

		return false;
	}
	
	/**
	 *	Получение даты осмотра педиатром
	 */	
	function getEvnUslugaDispDopPedDate($data)
	{
		
		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			select
				convert(varchar(10),ISNULL(MAX(EUDDData.EvnUslugaDispDop_didDate), @getdate),120) as peddate
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_didDate
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispProf_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where DDIC.EvnPLDisp_id = :EvnPLDispProf_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code = 19
		";
		
		$result = $this->db->query($query, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
	
        if (is_object($result))
        {
            $resp = $result->result('array');
			if (is_array($resp) && count($resp) > 0) {
				return $resp[0];
			}
        }
		
		return false;
	}
	
	/**
	 *	Сохранение карты
	 */	
    function saveEvnPLDispProf($data)
    {
		$savedData = array();
		if (!empty($data['EvnPLDispProf_id'])) {
			$savedData = $this->getFirstRowFromQuery("
		  		select top 1 * 
		  		from v_EvnPLDispProf with(nolock) 
		  		where EvnPLDispProf_id = :EvnPLDispProf_id
			", $data, true);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		if (getRegionNick() == 'krasnoyarsk') {
			if (!$data['checkAttributeforLpuSection']) {
				$checkDate = $this->checkAttributeforLpuSection(array(
					'EvnPLDispProf_id' => $data['EvnPLDispProf_id']
				));

				If ($checkDate[0] != "Ok") {
					return array('Error_Msg' => 'YesNo', 'Alert_Msg' => $checkDate, 'Error_Code' => 110);
				}

			}else if($data['checkAttributeforLpuSection']==2) {
				$data['EvnPLDispProf_IsMobile'] = 'on';
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispProf_id']) && !empty($data['EvnPLDispProf_IsEndStage']) && $data['EvnPLDispProf_IsEndStage'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispProf_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session']
			));
		}

		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		// Закомментировал, ибо https://redmine.swan.perm.ru/issues/20289
		/*
		$checkResult = $this->checkPersonData($data);
		
		If ( $checkResult[0]!="Ok" ) {
			return $checkResult;
		}
		*/
    	$proc = '';
    	if ( !isset($data['EvnPLDispProf_id']) ) {
			$proc = 'p_EvnPLDispProf_ins';
	    } else {
			$proc = 'p_EvnPLDispProf_upd';
	    }

		// получаем даты начала и конца услуг внутри диспансеризации.
		$peddate = $this->getEvnUslugaDispDopPedDate($data);
		if (is_array($peddate)) {
			$data['EvnPLDispProf_disDate'] = $peddate['peddate'];
		} else if (!empty($data['EvnPLDispProf_IsEndStage']) && $data['EvnPLDispProf_IsEndStage'] == 2) {
			return array(array('Error_Msg' => 'Дата выполнения осмотра врача терапевта обязательна для заполнения'));
		}

		$data['EvnPLDispProf_setDate'] = $data['EvnPLDispProf_consDate'];
		if (getRegionNick() == 'pskov') {
			// Для Пскова в качестве даты надо сохранять минимальную дату из услуг
			$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
			if (is_array($minmaxdates)) {
				$data['EvnPLDispProf_setDate'] = $minmaxdates['mindate'];
			}
		}
		
		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispProf_IsEndStage']) || $data['EvnPLDispProf_IsEndStage'] == 1) {
			$data['EvnPLDispProf_disDate'] = NULL;
		}
		
		if ($data['EvnPLDispProf_IsMobile']) { $data['EvnPLDispProf_IsMobile'] = 2; } else { $data['EvnPLDispProf_IsMobile'] = 1;	}
		if ($data['EvnPLDispProf_IsOutLpu']) { $data['EvnPLDispProf_IsOutLpu'] = 2; } else { $data['EvnPLDispProf_IsOutLpu'] = 1;	}

		// Проверки на допустимость сохранения карты на указанную дату
		$checkResult = $this->checkEvnPLDispProfCanBeSaved($data, 'saveEvnPLDispProf');

		if ( !empty($checkResult['Error_Msg']) || !empty($checkResult['Alert_Msg'])) {
			return array($checkResult);
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
		
		$this->checkZnoDirection($data, 'EvnPLDispProf');
		
   		$query = "
		    declare
		        @EvnPLDispProf_id bigint,
				@EvnPLDispProf_IsRefusal bigint,
				@EvnDirection_aid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @EvnPLDispProf_id = :EvnPLDispProf_id;

			if ( @EvnPLDispProf_id is not null )
				select top 1
					@EvnPLDispProf_IsRefusal = EvnPLDispProf_IsRefusal,
					@EvnDirection_aid = EvnDirection_aid
				from v_EvnPLDispProf (nolock)
				where EvnPLDispProf_id = :EvnPLDispProf_id;

			exec {$proc}
				@EvnPLDispProf_id = @EvnPLDispProf_id output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispProf_IsNewOrder = :EvnPLDispProf_IsNewOrder,
				@EvnPLDispProf_IndexRep = :EvnPLDispProf_IndexRep,
				@EvnPLDispProf_IndexRepInReg = :EvnPLDispProf_IndexRepInReg,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispProf_setDT = :EvnPLDispProf_setDate,
				@EvnPLDispProf_disDT = :EvnPLDispProf_disDate,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@DispClass_id = :DispClass_id,
				@EvnPLDispProf_fid = :EvnPLDispProf_fid,
				@AttachType_id = 2,
				@EvnPLDispProf_IsStenocard = :EvnPLDispProf_IsStenocard,
				@EvnPLDispProf_IsDoubleScan = :EvnPLDispProf_IsDoubleScan,
				@EvnPLDispProf_IsTub = :EvnPLDispProf_IsTub,
				@EvnPLDispProf_IsEsophag = :EvnPLDispProf_IsEsophag,
				@EvnPLDispProf_IsSmoking = :EvnPLDispProf_IsSmoking,
				@EvnPLDispProf_IsRiskAlco = :EvnPLDispProf_IsRiskAlco,
				@EvnPLDispProf_IsAlcoDepend = :EvnPLDispProf_IsAlcoDepend,
				@EvnPLDispProf_IsLowActiv = :EvnPLDispProf_IsLowActiv,
				@EvnPLDispProf_IsIrrational = :EvnPLDispProf_IsIrrational,
				@Diag_id = :Diag_id,
				@EvnPLDispProf_IsDisp = :EvnPLDispProf_IsDisp,
				@NeedDopCure_id = :NeedDopCure_id,
				@EvnPLDispProf_IsStac = :EvnPLDispProf_IsStac,
				@EvnPLDispProf_IsSanator = :EvnPLDispProf_IsSanator,
				@EvnPLDispProf_SumRick = :EvnPLDispProf_SumRick,
				@RiskType_id = :RiskType_id,
				@EvnPLDispProf_IsSchool = :EvnPLDispProf_IsSchool,
				@EvnPLDispProf_IsProphCons = :EvnPLDispProf_IsProphCons,
				@EvnPLDispProf_IsHypoten = :EvnPLDispProf_IsHypoten,
				@EvnPLDispProf_IsLipid = :EvnPLDispProf_IsLipid,
				@EvnPLDispProf_IsHypoglyc = :EvnPLDispProf_IsHypoglyc,
				@HealthKind_id = :HealthKind_id,
				@EvnPLDispProf_IsEndStage = :EvnPLDispProf_IsEndStage,
				@EvnPLDispProf_IsFinish = :EvnPLDispProf_IsEndStage,
				@EvnPLDispProf_consDT = :EvnPLDispProf_consDate,
				@EvnPLDispProf_IsMobile = :EvnPLDispProf_IsMobile, 
				@EvnPLDispProf_IsOutLpu = :EvnPLDispProf_IsOutLpu, 
				@Lpu_mid = :Lpu_mid,
				@PayType_id = :PayType_id,
				@CardioRiskType_id = :CardioRiskType_id,
				@EvnPLDispProf_IsRefusal = @EvnPLDispProf_IsRefusal,
				@EvnDirection_aid = @EvnDirection_aid,
				@EvnPLDispProf_IsSuspectZNO = :EvnPLDispProf_IsSuspectZNO,
				@Diag_spid = :Diag_spid,
				@EvnPLDispProf_IsKKND = :EvnPLDispProf_IsKKND,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @EvnPLDispProf_id as EvnPLDispProf_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		
        if ( is_object($result) ) {
			$resp = $result->result('array');

			if (!empty($resp[0]['EvnPLDispProf_id'])) {
				$data['EvnPLDispProf_id'] = $resp[0]['EvnPLDispProf_id'];
				$data['EvnPLDispProf_setDT'] = $data['EvnPLDispProf_setDate'];

				// Сохраняем скрытую услугу для Бурятии, если случай закончен
				// @task https://redmine.swan.perm.ru/issues/52175
				// Убрали по https://redmine.swan.perm.ru/issues/61068
				// Вернул https://redmine.swan.perm.ru/issues/88329
				// Добавлен Крым
				// @task https://redmine.swan.perm.ru/issues/88196
				if (
					in_array($data['session']['region']['nick'], array('buryatiya', 'krym')) && !empty($data['EvnPLDispProf_id'])
					&& !empty($data['EvnPLDispProf_IsEndStage']) && $data['EvnPLDispProf_IsEndStage'] == 2
				) {
					// Ищем существующую услугу
					$query = "
						select top 1
							EvnUslugaDispDop_id,
							UslugaComplex_id,
							PayType_id,
							convert(varchar(10), EvnUslugaDispDop_setDT, 104) as EvnUslugaDispDop_setDate
						from v_EvnUslugaDispDop with (nolock)
						where EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
							and EvnUslugaDispDop_IsVizitCode = 2
					";
					$result = $this->db->query($query, array(
						'EvnUslugaDispDop_pid' => $data['EvnPLDispProf_id']
					));

					if (!is_object($result)) {
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
					}

					$response = $result->result('array');

					if (is_array($response) && count($response) > 0) {
						$uslugaData = $response[0];
					} else {
						$uslugaData = array();
					}

					$onDate = $data['EvnPLDispProf_setDT'];
					if (!empty($data['EvnPLDispProf_IsNewOrder']) && $data['EvnPLDispProf_IsNewOrder'] == 2) {
						$onDate = date('Y', strtotime($data['EvnPLDispProf_setDT'])) . '-12-31';
					}

					$ageCalcDate = (!empty($onDate) ? substr($onDate, 0, 4) : date('Y')) . '-12-31';

					// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
					$query = "
						declare
							@sex_id bigint,
							@age int,
							@getdate datetime = dbo.tzGetDate();

						select top 1
							@sex_id = Sex_id,
							@age = dbo.Age2(Person_BirthDay, cast(:ageCalcDate as datetime))
						from v_PersonState ps (nolock)
						where ps.Person_id = :Person_id

						select top 1 USL.UslugaComplex_id
						from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL with (nolock)
						where
							USL.DispClass_id = :DispClass_id
							and ISNULL(USL.Sex_id, @sex_id) = @sex_id
							and @age between ISNULL(USL.UslugaSurveyLink_From, 0) and ISNULL(USL.UslugaSurveyLink_To, 999)
							and ISNULL(USL.UslugaSurveyLink_IsDel, 1) = 1
							and (usl.UslugaSurveyLink_endDate is null or usl.UslugaSurveyLink_endDate >= ISNULL(:EvnPLDispProf_setDT, @getdate))
							and (usl.UslugaSurveyLink_begDate is null or usl.UslugaSurveyLink_begDate <= ISNULL(:EvnPLDispProf_setDT, @getdate))
					";
					$result = $this->db->query($query, array(
						'DispClass_id' => $data['DispClass_id'],
						'EvnPLDispProf_setDT' => $onDate,
						'Person_id' => $data['Person_id'],
						'ageCalcDate' => $ageCalcDate,
					));

					if (!is_object($result)) {
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора)');
					}

					$response = $result->result('array');

					if (is_array($response) && count($response) > 0) {
						$UslugaComplex_id = $response[0]['UslugaComplex_id'];
					} else {
						$UslugaComplex_id = null;
					}

					// Добавляем/обновляем при необходимости
					if (!empty($UslugaComplex_id)) {
						$query = "
							declare
								@id bigint = :EvnUslugaDispDop_id,
								@pt bigint = :PayType_id,
								@ErrCode int,
								@ErrMessage varchar(4000);

							if ( @pt is null )
								set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');

							exec p_EvnUslugaDispDop_" . (!empty($uslugaData['EvnUslugaDispDop_id']) ? "upd" : "ins") . "
								@EvnUslugaDispDop_id = @id output,
								@EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid,
								@UslugaComplex_id = :UslugaComplex_id,
								@EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDT,
								@EvnUslugaDispDop_IsVizitCode = 2,
								@Lpu_id = :Lpu_id,
								@Server_id = :Server_id,
								@PersonEvn_id = :PersonEvn_id,
								@PayType_id = @pt,
								@UslugaPlace_id = 1,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;

							select @id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$result = $this->db->query($query, array(
							'EvnUslugaDispDop_id' => (!empty($uslugaData['EvnUslugaDispDop_id']) ? $uslugaData['EvnUslugaDispDop_id'] : null),
							'EvnUslugaDispDop_pid' => $data['EvnPLDispProf_id'],
							'UslugaComplex_id' => $UslugaComplex_id,
							'EvnUslugaDispDop_setDT' => (!empty($data['EvnPLDispProf_setDT']) ? $data['EvnPLDispProf_setDT'] : null),
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
							'pmUser_id' => $data['pmUser_id']
						));

						if (!is_object($result)) {
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
						}

						$response = $result->result('array');

						if (!is_array($response) || count($response) == 0) {
							return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
						} else if (!empty($response[0]['Error_Msg'])) {
							return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
						}
					} // Удаляем
					else if (!empty($uslugaData['EvnUslugaDispDop_id'])) {
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);

							exec p_EvnUslugaDispDop_del
								@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;

							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$result = $this->db->query($query, array(
							'EvnUslugaDispDop_id' => $uslugaData['EvnUslugaDispDop_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						if (!is_object($result)) {
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
						}

						$response = $result->result('array');

						if (!is_array($response) || count($response) == 0) {
							return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
						} else if (!empty($response[0]['Error_Msg'])) {
							return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
						}
					}
				}
			}

			$justClosed = (
				$data['EvnPLDispProf_IsEndStage'] == 2 && (
					empty($savedData) || $savedData['EvnPLDispProf_IsEndStage'] != 2
				)
			);

			if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
				//Отправить человека в очередь на идентификацию
				$this->load->model('Person_model', 'pmodel');
				$this->pmodel->isAllowTransaction = false;
				$resTmp = $this->pmodel->addPersonRequestData(array(
					'Person_id' => $data['Person_id'],
					'Evn_id' => $data['EvnPLDispProf_id'],
					'pmUser_id' => $data['pmUser_id'],
					'PersonRequestSourceType_id' => 3,
				));
				$this->pmodel->isAllowTransaction = true;
				if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
					return array('Error_Msg' => $resTmp[0]['Error_Msg']);
				}
			}

			return $resp;
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты ДД)'));
		}
    }
	
	
	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispProfYears($data)
    {
  		$sql = "
			select
            -- select
			count(EPLDP.EvnPLDispProf_id) as count,
			year(EPLDP.EvnPLDispProf_setDate) as EvnPLDispProf_Year
			-- end select
			from
			-- from
				v_PersonState PS with (nolock)
					inner join [v_EvnPLDispProf] [EPLDP] with (nolock) on [PS].[Person_id] = [EPLDP].[Person_id] and [EPLDP].Lpu_id = :Lpu_id
			-- end from
			where
			-- where
				exists
					(select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and PC.Lpu_id = :Lpu_id)
				and year(EPLDP.EvnPLDispProf_setDate) >= 2013
				and EPLDP.EvnPLDispProf_setDate is not null
			-- end where
			GROUP BY
				year(EPLDP.EvnPLDispProf_setDate)
			ORDER BY
				year(EPLDP.EvnPLDispProf_setDate)
		";

		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispProfExists($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispProf_id) as count
			FROM
				v_EvnPLDispProf (nolock)
			WHERE
				Person_id = ? and Lpu_id = ? and year(EvnPLDispProf_setDate) = year(dbo.tzGetDate())
		";

		$res = $this->db->query($sql, array($data['Person_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 )
				return array(array('isEvnPLDispProfExists' => false, 'Error_Msg' => ''));
			else
				return array(array('isEvnPLDispProfExists' => true, 'Error_Msg' => ''));
		}
 	    else
 	    	return false;
    }

	/**
	 * Проверка, есть ли талон на этого человека в этом или предыдущем году
	 */
	function checkIfEvnPLDispProfExistsInTwoYear($data)
    {
  		$sql = "
			SELECT top 1
				case
					when year(EvnPLDispProf_setDate) = year(:EvnPLDisp_consDate) then 2
					when year(EvnPLDispProf_setDate) = year(:EvnPLDisp_consDate)-1 then 1
				end as ExistCard
			FROM
				v_EvnPLDispProf (nolock)
			WHERE
				Person_id = :Person_id and Lpu_id = :Lpu_id and (year(EvnPLDispProf_setDate) IN (year(:EvnPLDisp_consDate), year(:EvnPLDisp_consDate)-1))
		";

		$res = $this->db->query($sql, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'EvnPLDisp_consDate' => $data['EvnPLDisp_consDate']
		));

		if ( is_object($res) )
		{
 	    	$resp = $res->result('array');
			if (!empty($resp[0]['ExistCard'])) {
				if ($resp[0]['ExistCard'] == 2) {
					return array('Error_Msg' => '', 'InThisYear' => 1);
				}

				if ($resp[0]['ExistCard'] == 1) {
					return array('Error_Msg' => '', 'InPastYear' => 1);
				}
			}

			return array('Error_Msg' => '');
		}
 	    else
 	    	return false;
    }

	/**
	 *	Перенос карты ДВН в профосмотр
	 */
	function transferEvnPLDispDopToEvnPLDispProf($data)
	{
		// Стартуем транзакцию
		$this->db->trans_begin();

		// 1. Получаем всю необходимую инфу из карты ДВН
		$query = "
			select
				EvnPLDispDop13_id,
				EvnPLDispDop13_IsNewOrder,
				Lpu_id,
				Person_id,
				Server_id,
				PersonEvn_id,
				convert(varchar, EvnPLDispDop13_setDT, 120) as EvnPLDispDop13_setDate,
				convert(varchar, EvnPLDispDop13_consDT, 120) as EvnPLDispDop13_consDate,
				EvnPLDispDop13_IsStenocard,
				EvnPLDispDop13_IsDoubleScan,
				EvnPLDispDop13_IsTub,
				EvnPLDispDop13_IsEsophag,
				EvnPLDispDop13_IsSmoking,
				EvnPLDispDop13_IsRiskAlco,
				EvnPLDispDop13_IsAlcoDepend,
				EvnPLDispDop13_IsLowActiv,
				EvnPLDispDop13_IsIrrational,
				EvnPLDispDop13_IsDisp,
				EvnPLDispDop13_IsStac,
				EvnPLDispDop13_IsSanator,
				EvnPLDispDop13_IsSchool,
				EvnPLDispDop13_IsProphCons,
				EvnPLDispDop13_IsHypoten,
				EvnPLDispDop13_IsLipid,
				EvnPLDispDop13_IsHypoglyc,
				EvnPLDispDop13_IsMobile,
				EvnPLDispDop13_IsOutLpu,
				EvnPLDispDop13_SumRick,
				RiskType_id,
				NeedDopCure_id,
				Lpu_mid,
				PayType_id,
				HealthKind_id,
				Diag_id
			from
				v_EvnPLDispDop13 (nolock)
			where
				EvnPLDispDop13_id = :EvnPLDispDop13_id
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		));
		if (is_object($result)) {
			$resp = $result->result('array');
		}
		if (empty($resp[0]['EvnPLDispDop13_id'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при получении данных по карте доп. диспансеризации'));
		}

		// Группа здоровья (IIIа, IIIб изменить на III)
		if (in_array($data['HealthKind_id'], array(6,7))) {
			$data['HealthKind_id'] = 3;
		}

		// 2. Создаём карту проф. осмотра
		$query = "
			declare
				@EvnPLDispProf_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @EvnPLDispProf_id = null;

			exec p_EvnPLDispProf_ins
				@EvnPLDispProf_id = @EvnPLDispProf_id output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispProf_pid = null,
				@EvnPLDispProf_rid = null,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispProf_setDT = :EvnPLDispProf_setDate,
				@EvnPLDispProf_disDT = null,
				@EvnPLDispProf_didDT = null,
				@Morbus_id = null,
				@EvnPLDispProf_IsSigned = null,
				@pmUser_signID = null,
				@EvnPLDispProf_signDT = null,
				@EvnPLDispProf_VizitCount = null,
				@Person_Age = null,
				@AttachType_id = 2,
				@Lpu_aid = null,
				@EvnPLDispProf_IsStenocard = :EvnPLDispProf_IsStenocard,
				@EvnPLDispProf_IsDoubleScan = :EvnPLDispProf_IsDoubleScan,
				@EvnPLDispProf_IsTub = :EvnPLDispProf_IsTub,
				@EvnPLDispProf_IsEsophag = :EvnPLDispProf_IsEsophag,
				@EvnPLDispProf_IsSmoking = :EvnPLDispProf_IsSmoking,
				@EvnPLDispProf_IsRiskAlco = :EvnPLDispProf_IsRiskAlco,
				@EvnPLDispProf_IsAlcoDepend = :EvnPLDispProf_IsAlcoDepend,
				@EvnPLDispProf_IsLowActiv = :EvnPLDispProf_IsLowActiv,
				@EvnPLDispProf_IsIrrational = :EvnPLDispProf_IsIrrational,
				@Diag_id = :Diag_id,
				@EvnPLDispProf_IsDisp = :EvnPLDispProf_IsDisp,
				@NeedDopCure_id = :NeedDopCure_id,
				@EvnPLDispProf_IsStac = :EvnPLDispProf_IsStac,
				@EvnPLDispProf_IsSanator = :EvnPLDispProf_IsSanator,
				@EvnPLDispProf_SumRick = :EvnPLDispProf_SumRick,
				@RiskType_id = :RiskType_id,
				@EvnPLDispProf_IsSchool = :EvnPLDispProf_IsSchool,
				@EvnPLDispProf_IsProphCons = :EvnPLDispProf_IsProphCons,
				@EvnPLDispProf_IsHypoten = :EvnPLDispProf_IsHypoten,
				@EvnPLDispProf_IsLipid = :EvnPLDispProf_IsLipid,
				@EvnPLDispProf_IsHypoglyc = :EvnPLDispProf_IsHypoglyc,
				@HealthKind_id = :HealthKind_id,
				@EvnPLDispProf_IsEndStage = 1,
				@EvnPLDispProf_IsFinish = 1,
				@EvnPLDispProf_consDT = :EvnPLDispProf_consDate,
				@EvnPLDispProf_IsMobile = :EvnPLDispProf_IsMobile,
				@EvnPLDispProf_IsOutLpu = :EvnPLDispProf_IsOutLpu,
				@Lpu_mid = :Lpu_mid,
				@CardioRiskType_id = null,
				@DispClass_id = 5,
				@PayType_id = :PayType_id,
				@EvnPLDispProf_fid = null,
				@EvnPLDispProf_IsDVN = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output

			select @EvnPLDispProf_id as EvnPLDispProf_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result_epldp = $this->db->query($query, array(
			'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
			'HealthKind_id' => $data['HealthKind_id'],
			'Lpu_id' => $resp[0]['Lpu_id'],
			'Server_id' => $resp[0]['Server_id'],
			'PersonEvn_id' => $resp[0]['PersonEvn_id'],
			'EvnPLDispProf_setDate' => $resp[0]['EvnPLDispDop13_setDate'],
			'EvnPLDispProf_consDate' => $resp[0]['EvnPLDispDop13_consDate'],
			'EvnPLDispProf_IsStenocard' => $data['EvnPLDispDop13_IsStenocard'],
			'EvnPLDispProf_IsDoubleScan' => $data['EvnPLDispDop13_IsDoubleScan'],
			'EvnPLDispProf_IsTub' => $data['EvnPLDispDop13_IsTub'],
			'EvnPLDispProf_IsEsophag' => $data['EvnPLDispDop13_IsEsophag'],
			'EvnPLDispProf_IsSmoking' => $data['EvnPLDispDop13_IsSmoking'],
			'EvnPLDispProf_IsRiskAlco' => $data['EvnPLDispDop13_IsRiskAlco'],
			'EvnPLDispProf_IsAlcoDepend' => $data['EvnPLDispDop13_IsAlcoDepend'],
			'EvnPLDispProf_IsLowActiv' => $data['EvnPLDispDop13_IsLowActiv'],
			'EvnPLDispProf_IsIrrational' => $data['EvnPLDispDop13_IsIrrational'],
			'EvnPLDispProf_IsDisp' => $data['EvnPLDispDop13_IsDisp'],
			'EvnPLDispProf_IsStac' => $data['EvnPLDispDop13_IsStac'],
			'EvnPLDispProf_IsSanator' => $data['EvnPLDispDop13_IsSanator'],
			'EvnPLDispProf_IsSchool' => $data['EvnPLDispDop13_IsSchool'],
			'EvnPLDispProf_IsProphCons' => $data['EvnPLDispDop13_IsProphCons'],
			'EvnPLDispProf_IsHypoten' => $data['EvnPLDispDop13_IsHypoten'],
			'EvnPLDispProf_IsLipid' => $data['EvnPLDispDop13_IsLipid'],
			'EvnPLDispProf_IsHypoglyc' => $data['EvnPLDispDop13_IsHypoglyc'],
			'EvnPLDispProf_IsMobile' => $resp[0]['EvnPLDispDop13_IsMobile'],
			'EvnPLDispProf_IsOutLpu' => $resp[0]['EvnPLDispDop13_IsOutLpu'],
			'EvnPLDispProf_SumRick' => $data['EvnPLDispDop13_SumRick'],
			'RiskType_id' => $data['RiskType_id'],
			'NeedDopCure_id' => $data['NeedDopCure_id'],
			'Diag_id' => $resp[0]['Diag_id'],
			'Lpu_mid' => $resp[0]['Lpu_mid'],
			'PayType_id' => $resp[0]['PayType_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		$resp_epldp = $result_epldp->result('array');
		if (empty($resp_epldp[0]['EvnPLDispProf_id'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении профосмотра'));
		}

		$data['EvnPLDispProf_id'] = $resp_epldp[0]['EvnPLDispProf_id'];

		// 3. Переносим согласия и осмотры
		// 3.1. Грузим осмотры/исследования для переноса
		$query = "
			select
				EUDD.EvnUslugaDispDop_id,
				ST.SurveyType_Code,
				DDIC.DopDispInfoConsent_IsEarlier,
				EVDD.Lpu_id,
				EVDD.Server_id,
				EVDD.PersonEvn_id,
				convert(varchar, EVDD.EvnVizitDispDop_setDT, 120) as EvnVizitDispDop_setDT,
				convert(varchar, EVDD.EvnVizitDispDop_didDT, 120) as EvnVizitDispDop_didDT,
				EVDD.Diag_id,
				EVDD.EvnVizitDispDop_DeseaseStage,
				EVDD.LpuSection_id,
				EVDD.MedPersonal_id,
				EVDD.MedStaffFact_id,
				EVDD.DopDispDiagType_id,
				EUDD.EvnDirection_id,
				EUDD.PayType_id,
				convert(varchar, EUDD.EvnUslugaDispDop_setDT, 120) as EvnUslugaDispDop_setDT,
				EUDD.UslugaComplex_id,
				convert(varchar, EUDD.EvnUslugaDispDop_didDT, 120) as EvnUslugaDispDop_didDT,
				convert(varchar, EUDD.EvnUslugaDispDop_disDT, 120) as EvnUslugaDispDop_disDT,
				EUDD.Lpu_uid,
				EUDD.LpuSectionProfile_id,
				EUDD.MedSpecOms_id,
				EUDD.ExaminationPlace_id,
				EUDD.LpuSection_uid,
				EUDD.EvnUslugaDispDop_ExamPlace
			from
				v_DopDispInfoConsent DDIC (nolock)
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_EvnVizitDispDop EVDD (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				left join v_EvnUslugaDispDop EUDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
			where
				DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and ST.SurveyType_Code IN (3,4,5,6,7,8,9,14,16,17,19,21,31,96,97)

			union all

			select
				EUDD.EvnUslugaDispDop_id,
				ST.SurveyType_Code,
				DDIC.DopDispInfoConsent_IsEarlier,
				EUDD.Lpu_id,
				EUDD.Server_id,
				EUDD.PersonEvn_id,
				null as EvnVizitDispDop_setDT,
				null as EvnVizitDispDop_didDT,
				null as Diag_id,
				null as EvnVizitDispDop_DeseaseStage,
				null as LpuSection_id,
				EUDD.MedPersonal_id,
				EUDD.MedStaffFact_id,
				null as DopDispDiagType_id,
				EUDD.EvnDirection_id,
				EUDD.PayType_id,
				convert(varchar, EUDD.EvnUslugaDispDop_setDT, 120) as EvnUslugaDispDop_setDT,
				EUDD.UslugaComplex_id,
				convert(varchar, EUDD.EvnUslugaDispDop_didDT, 120) as EvnUslugaDispDop_didDT,
				convert(varchar, EUDD.EvnUslugaDispDop_disDT, 120) as EvnUslugaDispDop_disDT,
				EUDD.Lpu_uid,
				EUDD.LpuSectionProfile_id,
				EUDD.MedSpecOms_id,
				EUDD.ExaminationPlace_id,
				EUDD.LpuSection_uid,
				EUDD.EvnUslugaDispDop_ExamPlace
			from
				v_DopDispInfoConsent DDIC (nolock)
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_EvnUslugaDispDop EUDD (nolock) on DDIC.EvnPLDisp_id = EUDD.EvnUslugaDispDop_pid
			where
				DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and ST.SurveyType_Code = 2
		";
		$result_eudd = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		));
		if (!is_object($result_eudd)) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при получении списка осмотров/исследований для переноса в профосмотр'));
		}

		// Формируем удобный массив
		$transferEUDD = array();
		$resp_eudd = $result_eudd->result('array');
		foreach($resp_eudd as $resp_euddone) {
			$transferEUDD[$resp_euddone['SurveyType_Code']] = $resp_euddone;
		}

		// Массив соответствий Проф => Двн
		$SurveyTypeConnect = array(
			2 => array(2),
			3 => array(3),
			4 => array(4),
			5 => array(5),
			6 => array(6),
			7 => array(96,97),
			8 => array(8),
			9 => array(9,10),
			14 => array(14),
			16 => array(16),
			17 => array(17),
			19 => array(19),
			21 => array(21),
			31 => array(31),
			96 => array(96),
			97 => array(97)
		);

		// 3.2. Грузим для свежесозданной карты возможные согласия
		$DopDispInfoConsentArray = $this->loadDopDispInfoConsent(array(
			'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
			'Lpu_id' => $resp[0]['Lpu_id'],
			'Person_id' => $resp[0]['Person_id'],
			'DispClass_id' => 5,
			'EvnPLDispProf_consDate' => $resp[0]['EvnPLDispDop13_consDate'],
			'EvnPLDispProf_IsNewOrder' => $resp[0]['EvnPLDispDop13_IsNewOrder'],
			'session' => $data['session']
		));
		// 3.3. Идём по согласиям и сохраняем
		foreach($DopDispInfoConsentArray as $oneConsent) {
			$query = "
				declare
					@DopDispInfoConsent_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @DopDispInfoConsent_id = null;

				exec p_DopDispInfoConsent_ins
					@DopDispInfoConsent_id = @DopDispInfoConsent_id output,
					@EvnPLDisp_id = :EvnPLDispProf_id,
					@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree,
					@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier,
					@SurveyTypeLink_id = :SurveyTypeLink_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @DopDispInfoConsent_id as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$item = array(
				'SurveyTypeLink_id' => $oneConsent['SurveyTypeLink_id'],
				'DopDispInfoConsent_IsAgree' => 2,
				'DopDispInfoConsent_IsEarlier' => 1
			);

			if (!empty($transferEUDD[$oneConsent['SurveyType_Code']]['DopDispInfoConsent_IsEarlier']) && $transferEUDD[$oneConsent['SurveyType_Code']]['DopDispInfoConsent_IsEarlier'] == 2) {
				$item['DopDispInfoConsent_IsAgree'] = 1;
				$item['DopDispInfoConsent_IsEarlier'] = 2;
			}

			$result_ddic = $this->db->query($query, array(
				'EvnPLDispProf_id' => $data['EvnPLDispProf_id'],
				'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
				'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!is_object($result_ddic)) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка выполнения запроса сохранения согласия'));
			}

			$resp_ddic = $result_ddic->result('array');
			if (empty($resp_ddic[0]['DopDispInfoConsent_id'])) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при сохранении согласия'));
			}

			// Если согласие сохранилась и была в ДВН услуга сохранена то копируем услугу
			if (!empty($SurveyTypeConnect[$oneConsent['SurveyType_Code']])) {
				foreach($SurveyTypeConnect[$oneConsent['SurveyType_Code']] as $SurveyType_Code) {
					if (!empty($transferEUDD[$SurveyType_Code]['EvnUslugaDispDop_id'])) {
						$item = $transferEUDD[$SurveyType_Code];

						// получаем новую услугу из согласия ПОВН.
						$item['UslugaComplex_id'] = $this->getFirstResultFromQuery('
							select
								stl.UslugaComplex_id
							from
								v_SurveyTypeLink stl (nolock)
							where
								stl.SurveyTypeLink_id = :SurveyTypeLink_id
						', array(
							'SurveyTypeLink_id' => $oneConsent['SurveyTypeLink_id']
						));
						if (empty($item['UslugaComplex_id'])) {
							$this->db->trans_rollback();
							return array(array('Error_Msg' => 'Ошибка конвертации услуги'));
						}

						if ($SurveyType_Code == 2) {
							// копируем анкетирование
							$sql = "
								declare
									@EvnUslugaDispDop_id bigint,
									@PayType_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @EvnUslugaDispDop_id = null;
								set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
								exec p_EvnUslugaDispDop_ins
									@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
									@EvnUslugaDispDop_pid = :EvnVizitDispDop_id,
									@Lpu_id = :Lpu_id,
									@Server_id = :Server_id,
									@EvnDirection_id = :EvnDirection_id,
									@PersonEvn_id = :PersonEvn_id,
									@PayType_id = @PayType_id,
									@EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDT,
									@UslugaComplex_id = :UslugaComplex_id,
									@EvnUslugaDispDop_didDT = :EvnUslugaDispDop_didDT,
									@EvnUslugaDispDop_disDT = :EvnUslugaDispDop_disDT,
									@Lpu_uid = :Lpu_uid,
									@LpuSectionProfile_id = :LpuSectionProfile_id,
									@MedSpecOms_id = :MedSpecOms_id,
									@ExaminationPlace_id = :ExaminationPlace_id,
									@LpuSection_uid = :LpuSection_uid,
									@MedPersonal_id = :MedPersonal_id,
									@MedStaffFact_id = :MedStaffFact_id,
									@EvnUslugaDispDop_ExamPlace = :EvnUslugaDispDop_ExamPlace,
									@EvnPrescrTimetable_id = null,
									@EvnPrescr_id = null,
									@Diag_id = :Diag_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";
							// echo getDebugSQL($sql, $data);
							$res = $this->db->query($sql, array(
								'EvnVizitDispDop_id' => $data['EvnPLDispProf_id'],
								'Lpu_id' => $item['Lpu_id'],
								'Server_id' => $item['Server_id'],
								'EvnDirection_id' => $item['EvnDirection_id'],
								'PersonEvn_id' => $item['PersonEvn_id'],
								'PayType_id' => $item['PayType_id'],
								'EvnUslugaDispDop_setDT' => $item['EvnUslugaDispDop_setDT'],
								'UslugaComplex_id' => $item['UslugaComplex_id'],
								'EvnUslugaDispDop_didDT' => $item['EvnUslugaDispDop_didDT'],
								'EvnUslugaDispDop_disDT' => $item['EvnUslugaDispDop_disDT'],
								'Lpu_uid' => $item['Lpu_uid'],
								'LpuSectionProfile_id' => $item['LpuSectionProfile_id'],
								'MedSpecOms_id' => $item['MedSpecOms_id'],
								'ExaminationPlace_id' => $item['ExaminationPlace_id'],
								'LpuSection_uid' => $item['LpuSection_uid'],
								'MedPersonal_id' => $item['MedPersonal_id'],
								'MedStaffFact_id' => $item['MedStaffFact_id'],
								'EvnUslugaDispDop_ExamPlace' => $item['EvnUslugaDispDop_ExamPlace'],
								'Diag_id' => $item['Diag_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
							}

							$resp_eudd = $res->result('array');

							if (!is_array($resp_eudd) || count($resp_eudd) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
							} else if (!empty($resp_eudd[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resp_eudd;
							}

							// К анкетированию нужно ещё перенести его ответы
							// Нельзя перенести, т.к. нет соответствий между вопросами ДВН и вопросами профосмотра.
							/*$query = "
								select
									QuestionType_id,
									DopDispQuestion_IsTrue,
									DopDispQuestion_Answer,
									DopDispQuestion_ValuesStr
								from
									v_DopDispQuestion (nolock)
								where
									EvnPLDisp_id = :EvnPLDisp_id
							";
							$result_eurate = $this->db->query($query, array(
								'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
							));

							if (!is_object($result_eurate)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка выполнения запроса получения ответов'));
							}

							$resp_eurate = $result_eurate->result('array');
							foreach ($resp_eurate as $resp_eurateone) {
								$sql = "
									declare
										@DopDispQuestion_id bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);
									set @DopDispQuestion_id = :DopDispQuestion_id;
									exec p_DopDispQuestion_ins
										@DopDispQuestion_id = @DopDispQuestion_id output,
										@EvnPLDisp_id = :EvnPLDisp_id,
										@QuestionType_id = :QuestionType_id,
										@DopDispQuestion_IsTrue = :DopDispQuestion_IsTrue,
										@DopDispQuestion_Answer = :DopDispQuestion_Answer,
										@DopDispQuestion_ValuesStr = :DopDispQuestion_ValuesStr,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @DopDispQuestion_id as DopDispQuestion_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";

								$queryParams = array(
									'DopDispQuestion_id' => NULL,
									'EvnPLDisp_id' => $data['EvnPLDispProf_id'],
									'QuestionType_id' => $resp_eurateone['QuestionType_id'],
									'DopDispQuestion_IsTrue' => $resp_eurateone['DopDispQuestion_IsTrue'],
									'DopDispQuestion_Answer' => $resp_eurateone['DopDispQuestion_Answer'],
									'DopDispQuestion_ValuesStr' => $resp_eurateone['DopDispQuestion_ValuesStr'],
									'pmUser_id' => $data['pmUser_id']
								);

								$res = $this->db->query($sql, $queryParams);

								if (!is_object($res)) {
									$this->db->trans_rollback();
									return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
								}

								$resp_euratesave = $res->result('array');

								if (!is_array($resp_euratesave) || count($resp_euratesave) == 0) {
									$this->db->trans_rollback();
									return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
								} else if (!empty($resp_euratesave[0]['Error_Msg'])) {
									$this->db->trans_rollback();
									return $resp_euratesave;
								}
							}*/
						} else {
							$sql = "
								declare
									@EvnVizitDispDop_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);

								set @EvnVizitDispDop_id = null;

								exec p_EvnVizitDispDop_ins
									@EvnVizitDispDop_id = @EvnVizitDispDop_id output,
									@EvnVizitDispDop_pid = :EvnVizitDispDop_pid,
									@Lpu_id = :Lpu_id,
									@Server_id = :Server_id,
									@PersonEvn_id = :PersonEvn_id,
									@EvnVizitDispDop_setDT = :EvnVizitDispDop_setDT,
									@EvnVizitDispDop_didDT = :EvnVizitDispDop_didDT,
									@Diag_id = :Diag_id,
									@EvnVizitDispDop_DeseaseStage = :EvnVizitDispDop_DeseaseStage,
									@LpuSection_id = :LpuSection_id,
									@MedPersonal_id = :MedPersonal_id,
									@MedStaffFact_id = :MedStaffFact_id,
									@DopDispDiagType_id = :DopDispDiagType_id,
									@DopDispInfoConsent_id = :DopDispInfoConsent_id,
									@UslugaComplex_id = :UslugaComplex_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;

								select @EvnVizitDispDop_id as EvnVizitDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";
							// echo getDebugSQL($sql, $data);
							$res = $this->db->query($sql, array(
								'EvnVizitDispDop_pid' => $data['EvnPLDispProf_id'],
								'Lpu_id' => $item['Lpu_id'],
								'Server_id' => $item['Server_id'],
								'PersonEvn_id' => $item['PersonEvn_id'],
								'EvnVizitDispDop_setDT' => $item['EvnVizitDispDop_setDT'],
								'EvnVizitDispDop_didDT' => $item['EvnVizitDispDop_didDT'],
								'Diag_id' => $item['Diag_id'],
								'EvnVizitDispDop_DeseaseStage' => $item['EvnVizitDispDop_DeseaseStage'],
								'LpuSection_id' => $item['LpuSection_id'],
								'MedPersonal_id' => $item['MedPersonal_id'],
								'MedStaffFact_id' => $item['MedStaffFact_id'],
								'DopDispDiagType_id' => $item['DopDispDiagType_id'],
								'DopDispInfoConsent_id' => $resp_ddic[0]['DopDispInfoConsent_id'],
								'UslugaComplex_id' => $item['UslugaComplex_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
							}

							$resp_evdd = $res->result('array');

							if (!is_array($resp_evdd) || count($resp_evdd) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при сохранении посещения'));
							} else if (!empty($resp_evdd[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resp_evdd;
							}

							$sql = "
								declare
									@EvnUslugaDispDop_id bigint,
									@PayType_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @EvnUslugaDispDop_id = null;
								set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
								exec p_EvnUslugaDispDop_ins
									@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
									@EvnUslugaDispDop_pid = :EvnVizitDispDop_id,
									@Lpu_id = :Lpu_id,
									@Server_id = :Server_id,
									@EvnDirection_id = :EvnDirection_id,
									@PersonEvn_id = :PersonEvn_id,
									@PayType_id = @PayType_id,
									@EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDT,
									@UslugaComplex_id = :UslugaComplex_id,
									@EvnUslugaDispDop_didDT = :EvnUslugaDispDop_didDT,
									@EvnUslugaDispDop_disDT = :EvnUslugaDispDop_disDT,
									@Lpu_uid = :Lpu_uid,
									@LpuSectionProfile_id = :LpuSectionProfile_id,
									@MedSpecOms_id = :MedSpecOms_id,
									@ExaminationPlace_id = :ExaminationPlace_id,
									@LpuSection_uid = :LpuSection_uid,
									@MedPersonal_id = :MedPersonal_id,
									@MedStaffFact_id = :MedStaffFact_id,
									@EvnUslugaDispDop_ExamPlace = :EvnUslugaDispDop_ExamPlace,
									@EvnPrescrTimetable_id = null,
									@EvnPrescr_id = null,
									@Diag_id = :Diag_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";
							// echo getDebugSQL($sql, $data);
							$res = $this->db->query($sql, array(
								'EvnVizitDispDop_id' => $resp_evdd[0]['EvnVizitDispDop_id'],
								'Lpu_id' => $item['Lpu_id'],
								'Server_id' => $item['Server_id'],
								'EvnDirection_id' => $item['EvnDirection_id'],
								'PersonEvn_id' => $item['PersonEvn_id'],
								'PayType_id' => $item['PayType_id'],
								'EvnUslugaDispDop_setDT' => $item['EvnUslugaDispDop_setDT'],
								'UslugaComplex_id' => $item['UslugaComplex_id'],
								'EvnUslugaDispDop_didDT' => $item['EvnUslugaDispDop_didDT'],
								'EvnUslugaDispDop_disDT' => $item['EvnUslugaDispDop_disDT'],
								'Lpu_uid' => $item['Lpu_uid'],
								'LpuSectionProfile_id' => $item['LpuSectionProfile_id'],
								'MedSpecOms_id' => $item['MedSpecOms_id'],
								'ExaminationPlace_id' => $item['ExaminationPlace_id'],
								'LpuSection_uid' => $item['LpuSection_uid'],
								'MedPersonal_id' => $item['MedPersonal_id'],
								'MedStaffFact_id' => $item['MedStaffFact_id'],
								'EvnUslugaDispDop_ExamPlace' => $item['EvnUslugaDispDop_ExamPlace'],
								'Diag_id' => $item['Diag_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
							}

							$resp_eudd = $res->result('array');

							if (!is_array($resp_eudd) || count($resp_eudd) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
							} else if (!empty($resp_eudd[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resp_eudd;
							}

							// К услуге нужно ещё перенести её результаты
							$query = "
								select
									Rate_id,
									Server_id
								from
									v_EvnUslugaRate (nolock)
								where
									EvnUsluga_id = :EvnUsluga_id
							";
							$result_eurate = $this->db->query($query, array(
								'EvnUsluga_id' => $item['EvnUslugaDispDop_id']
							));

							if (!is_object($result_eurate)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка выполнения запроса получения результатов'));
							}

							$resp_eurate = $result_eurate->result('array');
							foreach ($resp_eurate as $resp_eurateone) {
								$sql = "
									declare
										@EvnUslugaRate_id bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);
									set @EvnUslugaRate_id = :EvnUslugaRate_id;
									exec p_EvnUslugaRate_ins
										@EvnUslugaRate_id = @EvnUslugaRate_id output,
										@EvnUsluga_id = :EvnUsluga_id,
										@Rate_id = :Rate_id,
										@Server_id = :Server_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @EvnUslugaRate_id as EvnUslugaRate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";

								$queryParams = array(
									'EvnUslugaRate_id' => NULL,
									'EvnUsluga_id' => $resp_eudd[0]['EvnUslugaDispDop_id'],
									'Rate_id' => $resp_eurateone['Rate_id'],
									'Server_id' => $resp_eurateone['Server_id'],
									'pmUser_id' => $data['pmUser_id']
								);

								$res = $this->db->query($sql, $queryParams);

								if (!is_object($res)) {
									$this->db->trans_rollback();
									return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
								}

								$resp_euratesave = $res->result('array');

								if (!is_array($resp_euratesave) || count($resp_euratesave) == 0) {
									$this->db->trans_rollback();
									return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
								} else if (!empty($resp_euratesave[0]['Error_Msg'])) {
									$this->db->trans_rollback();
									return $resp_euratesave;
								}
							}
						}
					}
				}
			}
		}

		// переносим данные из списков формы
		$this->load->model('EvnDiagDopDisp_model');
		$resp_eddd = $this->queryResult("
			select
				convert(varchar(10), EvnDiagDopDisp_setDate, 120) as EvnDiagDopDisp_setDate,
				Diag_id,
				DiagSetClass_id,
				DeseaseDispType_id,
				EvnDiagDopDisp_IsSystemDataAdd,
				Lpu_id,
				Server_id,
				PersonEvn_id
			from
				v_EvnDiagDopDisp (nolock)
			where
				EvnDiagDopDisp_pid = :EvnPLDispDop13_id
		", array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		));
		foreach($resp_eddd as $one_eddd) {
			$this->EvnDiagDopDisp_model->saveEvnDiagDopDisp(array(
				'EvnDiagDopDisp_id' => null,
				'EvnDiagDopDisp_setDate' => $one_eddd['EvnDiagDopDisp_setDate'],
				'EvnDiagDopDisp_pid' => $data['EvnPLDispProf_id'],
				'Diag_id' => $one_eddd['Diag_id'],
				'DiagSetClass_id' => $one_eddd['DiagSetClass_id'],
				'DeseaseDispType_id' => $one_eddd['DeseaseDispType_id'],
				'EvnDiagDopDisp_IsSystemDataAdd' => $one_eddd['EvnDiagDopDisp_IsSystemDataAdd'],
				'Lpu_id' => $one_eddd['Lpu_id'],
				'Server_id' => $one_eddd['Server_id'],
				'PersonEvn_id' => $one_eddd['PersonEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}


		// Удаляем карту ДВН
		$this->load->model('EvnPLDispDop13_model');
		$resp_del = $this->EvnPLDispDop13_model->deleteEvnPLDispDop13($data);
		if (!empty($resp_del[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $resp_del;
		}

		$this->db->trans_commit();
		return array('Error_Msg' => '', 'EvnPLDispProf_id' => $data['EvnPLDispProf_id']);
	}

	/**
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispProfViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// – Редактирование карты диспансеризации / профосмотра доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as accessType,";
		if (false && !empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when ISNULL(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as accessType,";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}

		$query = "
			select
				epldp.EvnPLDispProf_id,
				epldp.EvnPLDispProf_pid,
				case
					when epldp.MedStaffFact_id is not null then ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(ls.LpuSection_Name + ' ', '') + ISNULL(msf.Person_Fio, '') 
					else ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(pu.pmUser_Name, '')
				end as AuthorInfo,
				'EvnPLDispProf' as Object,
				ISNULL(epldp.DispClass_id, 5) as DispClass_id,
				epldp.Person_id,
				epldp.PersonEvn_id,
				epldp.Server_id,
				dc.DispClass_Code,
				dc.DispClass_Name,
				{$accessType}
				epldp.PayType_id,
				pt.PayType_Name,
				convert(varchar(10), epldp.EvnPLDispProf_setDT, 104) as EvnPLDispProf_setDate,
				convert(varchar(10), epldp.EvnPLDispProf_disDT, 104) as EvnPLDispProf_disDate,
				convert(varchar(10), epldp.EvnPLDispProf_consDT, 104) as EvnPLDispProf_consDate,
				case when epldp.EvnPLDispProf_IsMobile = 2 then 1 else 0 end as EvnPLDispProf_IsMobile,
				case when epldp.EvnPLDispProf_IsOutLpu = 2 then 1 else 0 end as EvnPLDispProf_IsOutLpu,
				epldp.Lpu_mid,
				epldp.HealthKind_id,
				hk.HealthKind_Name,
				ISNULL(epldp.EvnPLDispProf_IsFinish, 1) as EvnPLDispProf_IsFinish,
				ISNULL(epldp.EvnPLDispProf_IsEndStage, 1) as EvnPLDispProf_IsEndStage,
				epldp.EvnPLDispProf_IsStenocard,
				epldp.EvnPLDispProf_IsDoubleScan,
				epldp.EvnPLDispProf_IsTub,
				epldp.EvnPLDispProf_IsEsophag,
				epldp.EvnPLDispProf_IsSmoking,
				epldp.EvnPLDispProf_IsRiskAlco,
				epldp.EvnPLDispProf_IsAlcoDepend,
				epldp.EvnPLDispProf_IsLowActiv,
				epldp.EvnPLDispProf_IsIrrational,
				epldp.Diag_id,
				d.Diag_FullName as Diag_Name,
				epldp.EvnPLDispProf_IsDisp,
				epldp.NeedDopCure_id,
				ndc.NeedDopCure_Name,
				epldp.EvnPLDispProf_IsStac,
				epldp.EvnPLDispProf_IsSanator,
				epldp.EvnPLDispProf_SumRick,
				epldp.RiskType_id,
				rt.RiskType_Name,
				epldp.EvnPLDispProf_IsSchool,
				epldp.EvnPLDispProf_IsProphCons,
				epldp.EvnPLDispProf_IsHypoten,
				epldp.EvnPLDispProf_IsLipid,
				epldp.EvnPLDispProf_IsHypoglyc,
				epldp.HealthKind_id,
				epldp.CardioRiskType_id,
				crt.CardioRiskType_Name,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint
			from
				v_EvnPLDispProf epldp (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = epldp.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = epldp.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = epldp.pmUser_updID
				left join v_DispClass dc (nolock) on dc.DispClass_id = epldp.DispClass_id
				left join v_PayType pt (nolock) on pt.PayType_id = epldp.PayType_id
				left join v_HealthKind hk (nolock) on hk.HealthKind_id = epldp.HealthKind_id
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = epldp.EvnPLDispProf_id
				left join v_RiskType rt (nolock) on rt.RiskType_id = epldp.RiskType_id
				left join v_CardioRiskType crt (nolock) on crt.CardioRiskType_id = epldp.CardioRiskType_id
				left join v_NeedDopCure ndc (nolock) on ndc.NeedDopCure_id = epldp.NeedDopCure_id
				left join v_Diag d (nolock) on d.Diag_id = epldp.Diag_id
			where
				epldp.EvnPLDispProf_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$resp = $result->result('array');

		// нужно получить значения результатов услуг из EvnUslugaRate
		if ( !empty($resp[0]['EvnPLDispProf_id']) ) {
			$query = "
				select 
					RT.RateType_SysNick as nick,
					RVT.RateValueType_SysNick,
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
					outer apply(
						select top 1
							EUDD.EvnUslugaDispDop_id
						from v_EvnUslugaDispDop EUDD (nolock)
							inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispProf_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
					left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
					left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
					left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
					left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
				where DDIC.EvnPLDisp_id = :EvnPLDispProf_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code <> 49
					and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
			";
			$result = $this->db->query($query, array(
				'EvnPLDispProf_id' => $resp[0]['EvnPLDispProf_id']
			));
			if ( is_object($result) ) {
				$results = $result->result('array');
				foreach($results as $oneresult) {
					if ($oneresult['RateValueType_SysNick'] == 'float') {
						if ( $oneresult['nick'] == 'bio_blood_kreatinin' ) {
							// Ничего не делаем
						}
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
					}

					$resp[0][$oneresult['nick']] = $oneresult['value'];
				}
			}
		}

		return $resp;
	}
	
	/**
	 * Проверка на возможность добавления профосмотра человеку
	 */
	function checkEvnPLDispProfCanBeSaved($data, $mode) {
		$response = array(
			'Alert_Msg' => '',
			'Error_Code' => '',
			'Error_Msg' => ''
		);
		if ( !array_key_exists('EvnPLDispProf_id', $data) ) {
			$data['EvnPLDispProf_id'] = null;
		}

		$data['EvnPLDispProf_YearEndDate'] = substr($data['EvnPLDispProf_consDate'], 0, 4) . '-12-31';

		$data['Person_Age'] = $this->getFirstResultFromQuery("
			SELECT top 1 dbo.Age2(PS.Person_BirthDay, :EvnPLDispProf_YearEndDate) as Person_Age
			FROM v_PersonState PS (nolock)
			WHERE PS.Person_id = :Person_id
		", $data);

		if ( $data['Person_Age'] !== false && $data['Person_Age'] < 18 ) {
			$response['Error_Msg'] = 'Профосмотр проводится для людей в возрасте с 18 лет';
			return $response;
		}

		// Если выбранный персон по полу/возрасту подлежит диспансеризации взрослого населения в выбранном году, то выводить сообщение «В год прохождения диспансеризации профилактический медицинский осмотр не проводится. ОК». Добавление отменить. (Использовать ТЗ по диспансеризации взрослого населения)
		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();

		if (getRegionNick() == 'perm' || (in_array(getRegionNick(), array('kareliya', 'krym')) && $mode == 'saveDopDispInfoConsent' && empty($data['ignoreDVN']))) {
			$DVN = $this->EvnPLDispDop13_model->allowDVN($data['Person_id'], $data['EvnPLDispProf_consDate']);
			if ($DVN == true) {
				if (in_array($this->regionNick, array('kareliya', 'krym'))) {
					$response['Alert_Msg'] = 'В год проведения диспансеризации определенных групп взрослого населения проведение профилактического осмотра не подлежит оплате. Продолжить?';
					$response['Error_Code'] = 101;
				} else {
					$response['Error_Msg'] = 'В год прохождения диспансеризации профилактический медицинский осмотр не проводится';
				}

				return $response;
			}
		}
					
		// Если выбранный персон в предыдущем году (от выбранного года) проходил профосмотр, то выводить сообщение «Профилактический медицинский осмотр проводится 1 раз в два года. ОК». Добавление отменить.
		// upd (task #164891):
		// – Если дата подписания согласия
		//   01.06.2019 Регион: Пермь
		//   06.05.2019 Регион: Пенза, Псков, Крым
		// или позже, то прохождение профосмотра в прошлом году не проверяется. Если выбранный пациент в текущем (выбранном году) проходил
		// профосмотр, то выводиться ошибка «Профилактический медицинский осмотр проводится 1 раз в год. ОК». Добавление отменяется.
		if ( !empty($dateX) && strtotime($data['EvnPLDispProf_consDate']) >= strtotime($dateX) ) {
			$sql = "
				SELECT top 1 EvnPLDispProf_id
				FROM v_EvnPLDispProf (nolock)
				WHERE
					Person_id = :Person_id
					and year(EvnPLDispProf_setDate) = year(:EvnPLDispProf_consDate)
					and EvnPLDispProf_id != ISNULL(:EvnPLDispProf_id, 0)
			";
			$errorMsg = 'Профилактический медицинский осмотр проводится 1 раз в год.';
		}
		else if ( in_array($this->regionNick, array('kareliya', 'perm', 'pskov')) ) {
			$sql = "
				SELECT top 1 EvnPLDispProf_id
				FROM v_EvnPLDispProf (nolock)
				WHERE
					Person_id = :Person_id
					and year(EvnPLDispProf_setDate) in (year(:EvnPLDispProf_consDate), year(:EvnPLDispProf_consDate) - 1)
					and EvnPLDispProf_id != ISNULL(:EvnPLDispProf_id, 0)
			";
			$errorMsg = 'Профилактический медицинский осмотр проводится 1 раз в два года.';
		}

		if ( !empty($sql) ) {
			$checkResult = $this->getFirstResultFromQuery($sql, $data);

			if ($checkResult !== false && !empty($checkResult)) {
				$response['Error_Msg'] = $errorMsg;
				return $response;
			}
		}

		if (getRegionNick() != 'perm' && !empty($dateX) && strtotime($data['EvnPLDispProf_consDate']) >= strtotime($dateX)) {
			// Если дата подписания согласия больше или равна 06.05.2019, то выполняется проверка.
			// Если в выбранном году на человека сохранена карта ДВН 1 этап, то открывается сообщение: «Для пациента в выбранном году уже добавлена карта диспансеризации, добавление карты профилактического осмотра недоступно. Кнопка ОК». При нажатии на кнопку сообщение закрывается, сохранение согласия не выполняется.
			$resp_epldd = $this->queryResult("
				select top 1
					EvnPLDispDop13_id
				from
					v_EvnPLDispDop13 (nolock)
				where
					Person_id = :Person_id
					and year(EvnPLDispDop13_consDT) = year(:EvnPLDispProf_consDate)
					and DispClass_id = 1
			", array(
				'Person_id' => $data['Person_id'],
				'EvnPLDispProf_consDate' => $data['EvnPLDispProf_consDate']
			));
			if (!empty($resp_epldd[0]['EvnPLDispDop13_id'])) {
				$response['Error_Msg'] = 'Для пациента в выбранном году уже добавлена карта диспансеризации, добавление карты профилактического осмотра недоступно.';
				return $response;
			}
		}
		
		if ($mode == 'saveDopDispInfoConsent' || $mode == 'saveEvnPLDispProf') {
			// Если карта отмечена, как «Переходный случай между МО», то реализовать возможность сохранения карты в МО-правопреемнике, если на дату осмотра врача-терапевта (ВОП) основное прикрепление пациента к МО-правопредшественнику.
			$pcardFilter = " and Lpu_id = :Lpu_id";
			if (!empty($data['EvnPLDispProf_id'])) {
				$EvnPLDispProf_IsTransit = $this->getFirstResultFromQuery("
					select top 1
						EvnPLDispProf_IsTransit
					from
						v_EvnPLDispProf (nolock)
					where
						EvnPLDispProf_id = :EvnPLDispProf_id
				", array(
					'EvnPLDispProf_id' => $data['EvnPLDispProf_id']
				));

				if (
					!empty($EvnPLDispProf_IsTransit)
					&& $EvnPLDispProf_IsTransit == 2
					&& array_key_exists('linkedLpuIdList', $data['session'])
				) {
					$pcardFilter = " and Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
				}
			}

			// Если выбранный персон на момент согласия не имеет основного прикрепления к ЛПУ пользователя, то выводить сообщение «Пациент имеет основное прикрепление к другой МО». Добавление отменить.
			$sql = "
				SELECT top 1 PersonCard_id
				FROM v_PersonCard_all with (nolock)
				WHERE
					Person_id = :Person_id
					and LpuAttachType_id = 1
					and cast(PersonCard_begDate as date) <= :EvnPLDispProf_consDate
					and ISNULL(PersonCard_endDate, '2030-01-01') >= :EvnPLDispProf_consDate
					{$pcardFilter}
			";
			$res = $this->db->query($sql, $data);

			if ( !is_object($res) ) {
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')';
				return $response;
			}

			$sel = $res->result('array');

			if ( !is_array($sel) || count($sel) == 0 || empty($sel[0]['PersonCard_id']) ) {
				if(!in_array(getRegionNick(), array('kareliya','krym','buryatiya'))) {
				$response['Error_Msg'] = 'Пациент имеет основное прикрепление к другой МО';
				return $response;
				}else if( in_array(getRegionNick(), array('kareliya','krym','buryatiya')) && empty($data['AttachmentAnswer']) ) {
					$this->db->trans_rollback();
					$response['Alert_Msg'] = 'Пациент не имеет основного прикрепления или прикреплен к другой МО. Продолжить сохранение?';
					$response['tip'] = 'AttachmentAnswer';
					return $response;
			}
			}
		} else if (!empty($data['EvnPLDispProf_disDate'])) {
			// Если карта отмечена, как «Переходный случай между МО», то реализовать возможность сохранения карты в МО-правопреемнике, если на дату осмотра врача-терапевта (ВОП) основное прикрепление пациента к МО-правопредшественнику.
			$pcardFilter = " and Lpu_id = :Lpu_id";
			if (!empty($data['EvnPLDispProf_id'])) {
				$EvnPLDispProf_IsTransit = $this->getFirstResultFromQuery("
					select top 1
						EvnPLDispProf_IsTransit
					from
						v_EvnPLDispProf (nolock)
					where
						EvnPLDispProf_id = :EvnPLDispProf_id
				", array(
					'EvnPLDispProf_id' => $data['EvnPLDispProf_id']
				));

				if (
					!empty($EvnPLDispProf_IsTransit)
					&& $EvnPLDispProf_IsTransit == 2
					&& array_key_exists('linkedLpuIdList', $data['session'])
				) {
					$pcardFilter = " and Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")";
				}
			}

			// Если выбранный персон на дату осмотра терапевта не имеет основного прикрепления к ЛПУ пользователя, то выводить сообщение «Пациент имеет основное прикрепление к другой МО». Сохранение отменить.
			$sql = "
				SELECT
					top 1 PersonCard_id
				FROM v_PersonCard_all with (nolock)
				WHERE
					Person_id = :Person_id
					and LpuAttachType_id = 1
					and cast(PersonCard_begDate as date) <= :EvnPLDispProf_disDate
					and ISNULL(PersonCard_endDate, '2030-01-01') >= :EvnPLDispProf_disDate
					{$pcardFilter}
			";
			$res = $this->db->query($sql, $data);

			if ( !is_object($res) ) {
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')';
				return $response;
			}

			$sel = $res->result('array');

			if ( !is_array($sel) || count($sel) == 0 || empty($sel[0]['PersonCard_id']) ) {
				$response['Error_Msg'] = 'Пациент имеет основное прикрепление к другой МО';
				return $response;
			}
		}

		return $response;
    }


	/**
	 * Данные человека по талону
	 */
	function getEvnPLDispProfPassportFields($data) {
		$dt = array();
		$person_id = 0;
		
  		$sql = "
			SELECT 
				dd.EvnPLDispProf_setDT,
				case when dd.EvnPLDispProf_setDT >= '2015-04-01' then 1 else 0 end as is_new_event,
				dd.Person_id,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_SurName,
				ps.Person_Phone,
				ps.Sex_id,
				datepart(DD,ps.Person_BirthDay) as Person_BirthDay_Day,
				datepart(MM,ps.Person_BirthDay) as Person_BirthDay_Month,
				datepart(YYYY,ps.Person_BirthDay) as Person_BirthDay_Year,
				case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end as Polis_Ser,
				case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end as Polis_Num,
				ua.Address_House,
				ua.Address_Corpus,
				ua.Address_Flat,
				ua.KLStreet_Name,
				kls.KLAreaType_id,
				(
						ua.KLRGN_Name+' '+ua.KLRGN_Socr
						+ISNULL(', '+ua.KLCity_Socr+' '+ua.KLCity_Name,'')
						+ISNULL(', '+ua.KLTown_Socr+' '+ua.KLTown_Name,'')
				) as Address_Info,
				l.Lpu_Name,
				l.Org_Phone,
				l.PAddress_Address as l_address,
				pc.PersonCard_Code,
				dd.EvnPLDispProf_IsSmoking as IsSmoking,
				dd.EvnPLDispProf_IsRiskAlco as IsRiskAlco,
				dd.EvnPLDispProf_IsLowActiv as IsLowActiv,
				dd.EvnPLDispProf_IsIrrational as IsIrrational
			FROM 
				v_EvnPLDispProf dd (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = dd.Person_id
				left join v_Address_all ua (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Lpu_all l (nolock) on l.Lpu_id = dd.Lpu_id
				left join v_PersonCard pc (nolock) on (PC.Person_id = ps.Person_id and pc.LpuAttachType_id = 1)
				left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_KLArea kla with (nolock) on kla.KLArea_id = COALESCE(ua.KLTown_id, ua.KLCity_id, ua.KLSubRgn_id, ua.KLRgn_id)
				left join v_KLSocr kls with (nolock) on kls.KLSocr_id = kla.KLSocr_id
			where
				EvnPLDispProf_id = :EvnPLDispProf_id
		";

		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$dt = array_merge($dt, $res[0]);
			if (isset($res[0]['Person_id']) && $res[0]['Person_id'] != '')
				$person_id = $res[0]['Person_id'];
		}
		//Установленные заболевания
		$query = "
			--Ранее известные и впервые выявленные заболевания
			select
				convert(varchar(10), EDDD.EvnDiagDopDisp_setDate, 104) as Diag_date,
				D.Diag_Name,
				D.Diag_Code
			from
				v_EvnDiagDopDisp EDDD (nolock)
				left join v_DiagSetClass DSC (nolock) on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnPLDispProf_id
				AND D.Diag_Code not like 'Z%'
			union
			--Подозрение на наличие стенокардии
			select
				null as Diag_date,
				'Подозрение на наличие стенокардии напряжения' as Diag_Name,
				null as Diag_Code
			from v_EvnPLDispProf EPLDP with (nolock)
			where EPLDP.EvnPLDispProf_id = :EvnPLDispProf_id
			and EPLDP.EvnPLDispProf_IsStenocard=2
			union
			--Подозрение на наличие туберкулеза
			select
				null as Diag_date,
				'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких' as Diag_Name,
				null as Diag_Code
			from v_EvnPLDispProf EPLDP with (nolock)
			where EPLDP.EvnPLDispProf_id = :EvnPLDispProf_id
			and EPLDP.EvnPLDispProf_IsTub=2
		";
		$res = $this->db->query($query, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		$dt['diags'] = array();
		if(is_object($res)){
			$dt['diags'] = $res->result('array');
		}
		//Основные показатели basic indicators
		$sql = "
				select RT.RateType_SysNick,
					   R.Rate_ValueInt,
				       R.Rate_ValueStr,
				       R.Rate_ValueFloat
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				where EUDD.Person_id = :Person_id
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				and RT.RateType_SysNick in ('person_height','person_weight','total_cholesterol','glucose','person_arterial_pressure','body_mass_index','systolic_blood_pressure','diastolic_blood_pressure')
				and(
				(R.Rate_ValueInt is not null) or (R.Rate_ValueStr is not null) or (R.Rate_ValueFloat is not null)
				)
				and UC.UslugaComplex_Name not like '%мочи%' --В рамках задачи http://redmine.swan.perm.ru/issues/23605 - и для глюкозы в крови, и для глюкозы мочи RateType_SysNick одинаковый
															--Поэтому исключать глюкозу мочи по услуге. А т.к. на разных регионах коды этих услуг разные, пришлось проверять по имени
		";

		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['basic_indicators'] = array();
		if (is_object($res)) {
			$res = $res->result('array');
			$rec = array();
			foreach($res as $row) {
				$rec[$row['RateType_SysNick']][] = array(
					'Rate_ValueInt' => $row['Rate_ValueInt'],
					'Rate_ValueStr' => $row['Rate_ValueStr'],
					'Rate_ValueFloat' => $row['Rate_ValueFloat']
				);
			}
			$dt['basic_indicators'] = $rec;
		}
		//Факторы риска risk_factors
		$sql = "
			select
				RFT.RiskFactorType_Code,
				RFT.RiskFactorType_Name
			from
				v_ProphConsult PC (nolock)
				left join v_RiskFactorType RFT (nolock) on RFT.RiskFactorType_id = PC.RiskFactorType_id
			where
				PC.EvnPLDisp_id = :EvnPLDispProf_id
			order by
				RFT.RiskFactorType_Code
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		$dt['risk_factors'] = array();
		if(is_object($res)) {
			$res = $res->result('array');
			$rec = array();
			foreach($res as $row) {
				$rec[$row['RiskFactorType_Code']][] = array(
					'value' => 'Да'
				);
			}
			$dt['risk_factors'] = $rec;
		}
		//Отдельно - Риск потребления наркотических веществ
		$sql = "
			select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
			from v_DopDispQuestion DDQ (nolock)
			left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
			where EvnPLDisp_id = :EvnPLDispProf_id
			and QT.QuestionType_Code in (36,37,38,39,40)
			and DDQ.DopDispQuestion_IsTrue = 2
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		$dt['risk_narco'] = array();
		if(is_object($res)){
			$res = $res->result('array');
			$dt['risk_narco'] = $res;
		}
		//Отягощенная наследственность
		$sql = "
			select
				D.Diag_Code,
				D.Diag_Name,
				HT.HeredityType_id
			from
				v_HeredityDiag HD (nolock)
				left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
				left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
			where
				HD.EvnPLDisp_id = :EvnPLDispProf_id
				and HT.HeredityType_id = '1'
			order by
				HD.HeredityDiag_id
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		$dt['her_diag'] = array();
		if(is_object($res)){
			$res = $res->result('array');
			$her_diag = '';
			$rec = array();
			foreach($res as $row){
				$her_diag = $her_diag." ".$row['Diag_Code'].";";
			}
			$dt['her_diag'] = $her_diag;
		}
		//Суммарный риск ССЗ %
		$sql = "
				select
				EPLDP.EvnPLDispProf_SumRick,
				RT.RiskType_Name
				from EvnPLDispProf EPLDP (nolock)
				left join RiskType RT (nolock) on RT.RiskType_id=EPLDP.RiskType_id
				where EPLDP.EvnPLDispProf_id = :EvnPLDispProf_id
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		$dt['summ_risk'] = array();
		if(is_object($res)){
			$res = $res->result('array');
			$dt['summ_risk'] = $res;
		}
		//Отдельно получим дату осмотра терапевта
		$sql = "
			select	distinct
				st.SurveyType_Code,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				(mp.Dolgnost_Name + '<br>' + mp.Person_Fio) as Med_Personal
			from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
				left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = evdd.MedPersonal_id
				left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
			where
				EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
				and st.SurveyType_Code = '19'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		if(is_object($res)){
			$res = $res->result('array');
			if(count($res)>0){
				$dt['diddate_19'] = $res[0]['EvnUslugaDispDop_didDate'];
				$dt['Med_Personal'] = $res[0]['Med_Personal'];
			}
		}
		//Получим группу здоровья
		$sql = "
			select HK.HealthKind_Name
			from v_EvnPLDispProf EPLDP with (nolock)
			left join v_HealthKind HK with(nolock) on HK.HealthKind_id=EPLDP.HealthKind_id
			where EvnPLDispProf_id=:EvnPLDispProf_id
		";
		$res = $this->db->query($sql, array('EvnPLDispProf_id' => $data['EvnPLDispProf_id']));
		if(is_object($res)){
			$res = $res->result('array');
			if(count($res)>0){
				$dt['HealthKind_Name'] = $res[0]['HealthKind_Name'];
			}
		}

 	    return $dt;
    }

	/**
	 * @param $RateType_SysNick
	 * @param $EvnUslugaDispDop13_id
	 * @return string
	 */
	function getRiskRateValue($RateType_SysNick, $EvnPLDispProf_id)
	{
		$params = array();
		$params['RateType_SysNick'] = $RateType_SysNick;
		$params['EvnPLDispProf_id'] = $EvnPLDispProf_id;
		$join = '';
		$and = '';
		$rate_value = '';
		if($RateType_SysNick == 'glucose')
		{
			$join = 'left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id';
			$and = "and UC.UslugaComplex_Name not like '%мочи%'";
		}

		$query_riskrate_value = "
		select top 1
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as rate_value
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				{$join}
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
				and RT.RateType_SysNick = :RateType_SysNick
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				{$and}
		";
		$result_riskrate_value = $this->db->query($query_riskrate_value,$params);
		if(is_object($result_riskrate_value)){
			$result_riskrate_value = $result_riskrate_value->result('array');
			if(count($result_riskrate_value)>0)
			{
				$rate_value = trim($result_riskrate_value[0]['rate_value']);
			}
		}
		return $rate_value;
	}

	/**
	 * Получение данных для пункат "Факторы риска" паспорта здоровья
	 */
	function getRiskFactorsForPassport($data)
	{
		$dt = array();
		$params = array();
		$params['Person_id'] = $data['Person_id'];

		$query_epldd = "
			select top 5
				EPLDP.EvnPLDispProf_id,
				convert(varchar(10), EPLDP.EvnPLDispProf_setDate, 104) as dd_date,
				case when EPLDP.EvnPLDispProf_IsSmoking = 2 then 'Да' else 'Нет' end as IsSmoking,
				case when EPLDP.EvnPLDispProf_IsRiskAlco = 2 then 'Да' else 'Нет' end as IsRiskAlco,
				case when EPLDP.EvnPLDispProf_IsLowActiv = 2 then 'Да' else 'Нет' end as IsLowActiv,
				case when EPLDP.EvnPLDispProf_IsIrrational = 2 then 'Да' else 'Нет' end as IsIrrational,
				ISNULL(HK.HealthKind_Name,'') as HealthKind_Name,
				ISNULL(EPLDP.EvnPLDispProf_SumRick,'') as EvnPLDispProf_SumRick,
				ISNULL(RT.RiskType_Name,'') as RiskType_Name

			from v_EvnPLDispProf EPLDP
			left join HealthKind HK (nolock) on HK.HealthKind_id = EPLDP.HealthKind_id
			left join RiskType RT (nolock) on RT.RiskType_id=EPLDP.RiskType_id
			where EPLDP.Person_id = :Person_id
			order by EPLDP.EvnPLDispProf_setDate desc
		";
		$result_epldd = $this->db->query($query_epldd,$params);
		if(is_object($result_epldd))
		{
			$result_epldd = $result_epldd->result('array');

			if(count($result_epldd) > 0){
				for($i=0; $i < count($result_epldd); $i++){
					$dt[$i] = $result_epldd[$i];
					$EvnPLDispProf_id = $result_epldd[$i]['EvnPLDispProf_id'];
					$dt[$i]['systolic_blood_pressure'] 	= (float)$this->getRiskRateValue('systolic_blood_pressure',	$EvnPLDispProf_id);
					$dt[$i]['diastolic_blood_pressure'] = (float)$this->getRiskRateValue('diastolic_blood_pressure',	$EvnPLDispProf_id);
					$dt[$i]['person_pressure'] = '';
					if($dt[$i]['systolic_blood_pressure']!='' && $dt[$i]['diastolic_blood_pressure']!='')
						$dt[$i]['person_pressure'] = $dt[$i]['systolic_blood_pressure'].'/'.$dt[$i]['diastolic_blood_pressure'];
					$dt[$i]['person_weight'] 			= $this->getRiskRateValue('person_weight',				$EvnPLDispProf_id);
					$dt[$i]['person_height'] 			= $this->getRiskRateValue('person_height',				$EvnPLDispProf_id);
					$dt[$i]['body_mass_index'] 			= $this->getRiskRateValue('body_mass_index',			$EvnPLDispProf_id);
					$dt[$i]['glucose'] 					= (float)$this->getRiskRateValue('glucose',				$EvnPLDispProf_id);
					$dt[$i]['total_cholesterol'] 		= (float)$this->getRiskRateValue('total_cholesterol',	$EvnPLDispProf_id);
					$dt[$i]['risk_narco'] = 'Нет';
					$query_risk_narco = "
						select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
						from v_DopDispQuestion DDQ (nolock)
						left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
						where EvnPLDisp_id = :EvnPLDispProf_id
						and QT.QuestionType_Code in (40,41,42,43,44)
						and DDQ.DopDispQuestion_IsTrue = 2
					";
					$result_risk_narco = $this->db->query($query_risk_narco,array('EvnPLDispProf_id' => $EvnPLDispProf_id));
					if(is_object($result_risk_narco)){
						$result_risk_narco = $result_risk_narco->result('array');
						if(count($result_risk_narco)>0) {
							$dt[$i]['risk_narco'] = ($result_risk_narco[0]['DDQ_Count'] > 0)?'Да':'Нет';
						}
					}

					//$dt[$i]['summ_risk'] = '';
					$dt[$i]['summ_risk'] = $result_epldd[$i]['EvnPLDispProf_SumRick'].'; '.$result_epldd[$i]['RiskType_Name'];

					$dt[$i]['dd_medpersonal'] = '';
					$query_dd_medpersonal = "
						select	distinct
							st.SurveyType_Code,
							(ISNULL(ps.PostMed_Name,'') + ' ' + ISNULL(msf.Person_Fio,'')) as Med_Personal
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
							and st.SurveyType_Code = '19'
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					";
					$result_dd_medpersona = $this->db->query($query_dd_medpersonal,array('EvnPLDispProf_id' => $EvnPLDispProf_id));
					if(is_object($result_dd_medpersona)){
						$result_dd_medpersona = $result_dd_medpersona->result('array');
						if(count($result_dd_medpersona)>0){
							$dt[$i]['dd_medpersonal'] = $result_dd_medpersona[0]['SurveyType_Code'] .'-' . $result_dd_medpersona[0]['Med_Personal'];
						}
					}

					//Найдем отдельно данные по отягощенный наследственности
					$dt[$i]['her_diag'] = '';
					$sql = "
						select
							D.Diag_Code,
							D.Diag_Name,
							HT.HeredityType_id
						from
							v_HeredityDiag HD (nolock)
							left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
						where
							HD.EvnPLDisp_id = :EvnPLDispProf_id
							and HT.HeredityType_id = '1'
						order by
							HD.HeredityDiag_id
					";
					$res = $this->db->query($sql, array('EvnPLDispProf_id' => $EvnPLDispProf_id));
					if(is_object($res)){
						$res = $res->result('array');
						$her_diag = '';
						$rec = array();
						foreach($res as $row){
							$her_diag = $her_diag." ".$row['Diag_Code'].";";
						}
						$dt[$i]['her_diag'] = $her_diag;
					}

				}
			}
		}
		return $dt;
	}

	/**
	 * Получение данных для пункат "Факторы риска" паспорта здоровья
	 */
	function getRiskFactorsForPassport_old($data)
	{
		$dt = array();
		$params = array();
		$params['EvnPLDispProf_id'] = $data['EvnPLDispProf_id'];
		$query = "
			select top 5 EPLDP.EvnPLDispProf_id,
				convert(varchar(10), EPLDP.EvnPLDispProf_setDate, 104) as dd_date,
				systolic_blood_pressure.systolic_blood_pressure,
				diastolic_blood_pressure.diastolic_blood_pressure,
				CONVERT(varchar(3),systolic_blood_pressure.systolic_blood_pressure) + '/' + CONVERT(varchar(3),diastolic_blood_pressure.diastolic_blood_pressure) as person_pressure,
				--case when (CAST(systolic_blood_pressure.systolic_blood_pressure as numeric) > 140 or cast(diastolic_blood_pressure.diastolic_blood_pressure as numeric) > 90) then 'Да' else 'Нет' end as risk_high_pressure,
				person_weight.person_weight,
				person_height.person_height,
				body_mass_index.body_mass_index,
				--case when CAST(body_mass_index.body_mass_index as numeric) >= 25 then 'Да' else 'Нет' end as risk_overweight,
				glucose.glucose,
				--case when cast(glucose.glucose as numeric) > 6 then 'Да' else 'Нет' end as risk_gluk,
				total_cholesterol.total_cholesterol,
				--case when cast(total_cholesterol.total_cholesterol as numeric) > 5 then 'Да' else 'Нет' end as risk_dyslipidemia,
				case when EPLDP.EvnPLDispProf_IsSmoking = 2 then 'Да' else 'Нет' end as IsSmoking,
				case when EPLDP.EvnPLDispProf_IsRiskAlco = 2 then 'Да' else 'Нет' end as IsRiskAlco,
				case when EPLDP.EvnPLDispProf_IsLowActiv = 2 then 'Да' else 'Нет' end as IsLowActiv,
				case when EPLDP.EvnPLDispProf_IsIrrational = 2 then 'Да' else 'Нет' end as IsIrrational,
				case when DDQ_Count.DDQ_Count > 1 then 'Да' else 'Нет' end as risk_narco,
				ISNULL(CONVERT(varchar(10), summ_risk.EvnPLDispProf_SumRick),'') + '; ' + ISNULL(summ_risk.RiskType_Name,'') as summ_risk
				 ,m_personal.Med_Personal
				,CAST(m_personal.SurveyType_Code as varchar) + '-' + m_personal.Med_Personal as dd_medpersonal
				,ISNULL(HK.HealthKind_Name,'') as HealthKind_Name
			from v_EvnPLDispProf EPLDP_F (nolock)
			left join v_EvnPLDispProf EPLDP (nolock) on EPLDP.Person_id = EPLDP_F.Person_id
			left join HealthKind HK (nolock) on HK.HealthKind_id = EPLDP_F.HealthKind_id
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as systolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as systolic_blood_pressure
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'systolic_blood_pressure'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) systolic_blood_pressure
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as diastolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as diastolic_blood_pressure
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'diastolic_blood_pressure'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) diastolic_blood_pressure
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_weight
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_weight
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'person_weight'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_weight
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_height
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_height
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'person_height'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_height
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as body_mass_index
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as body_mass_index
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'body_mass_index'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) body_mass_index
			outer apply (
				select-- CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as glucose
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as glucose
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'glucose'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				and UC.UslugaComplex_Name not like '%мочи%'
			) glucose
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as total_cholesterol
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as total_cholesterol
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
				and RT.RateType_SysNick = 'total_cholesterol'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) total_cholesterol
			outer apply(
			select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
						from v_DopDispQuestion DDQ (nolock)
						left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
						where EvnPLDisp_id = EPLDP.EvnPLDispProf_id
						and QT.QuestionType_Code in (40,41,42,43,44)
						and DDQ.DopDispQuestion_IsTrue = 2
			) DDQ_Count
			outer apply(
				select
				CAST(EPLDP2.EvnPLDispProf_SumRick as numeric(10)) as EvnPLDispProf_SumRick,
				RT.RiskType_Name
				from EvnPLDispProf EPLDP2 (nolock)
				left join RiskType RT (nolock) on RT.RiskType_id=EPLDP.RiskType_id
				where EPLDP2.EvnPLDispProf_id = EPLDP.EvnPLDispProf_id
			) summ_risk
			outer apply(
			select	distinct
							st.SurveyType_Code,
							(ps.PostMed_Name + ' ' + msf.Person_Fio) as Med_Personal
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
							and st.SurveyType_Code = '19'
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) m_personal
			where EPLDP_F.EvnPLDispProf_id = :EvnPLDispProf_id-- 1261360--1264267
			and EPLDP.EvnPLDispProf_setDate <= EPLDP_F.EvnPLDispProf_setDate
			and (person_weight.person_weight is not null or person_height.person_height is not null or glucose.glucose is not null)
			order by EPLDP.EvnPLDispProf_setDate desc
		";
		//where EPLDP_F.EvnPLDispDop13_id in(1264267,1261360)-- 1261360--1264267
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result > 0)){
				for($i=0; $i < count($result); $i++)
				{
					$dt[$i] = $result[$i];
					//Найдем отдельно данные по отягощенный наследственности
					$dt[$i]['her_diag'] = '';
					$evnpldispprof_id = $dt[$i]['EvnPLDispProf_id'];
					$sql = "
						select
							D.Diag_Code,
							D.Diag_Name,
							HT.HeredityType_id
						from
							v_HeredityDiag HD (nolock)
							left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
						where
							HD.EvnPLDisp_id = :EvnPLDispProf_id
							and HT.HeredityType_id = '1'
						order by
							HD.HeredityDiag_id
					";
					$res = $this->db->query($sql, array('EvnPLDispProf_id' => $evnpldispprof_id));
					if(is_object($res)){
						$res = $res->result('array');
						$her_diag = '';
						$rec = array();
						foreach($res as $row){
							$her_diag = $her_diag." ".$row['Diag_Code'].";";
						}
						$dt[$i]['her_diag'] = $her_diag;
					}
				}
			}
			//var_dump($dt);die;
		}
		return $dt;
	}

	/**
	 *	Получение идентификатора посещения
	 */
	function getEvnVizitDispDopId($EvnPLDispProf_id = null, $DopDispInfoConsent_id = null) {
		$query = "
			select top 1
				EUDD.EvnUslugaDispDop_pid as EvnVizitDispDop_id
			from
				v_DopDispInfoConsent DDIC with (nolock)
				inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				inner join v_EvnUslugaDispDop EUDD with (nolock) on EUDD.UslugaComplex_id = STL.UslugaComplex_id
			where
				DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and EUDD.EvnUslugaDispDop_rid = :EvnPLDispProf_id
				and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
				and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
				and ST.SurveyType_Code <> 49
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
		";
		
		$result = $this->db->query($query, array(
			 'DopDispInfoConsent_id' => $DopDispInfoConsent_id
			,'EvnPLDispProf_id' => $EvnPLDispProf_id
		));
	
        if ( is_object($result) ) {
            $res = $result->result('array');

			if ( is_array($res) && count($res) > 0 && !empty($res[0]['EvnVizitDispDop_id']) ) {
				return $res[0]['EvnVizitDispDop_id'];
			}
			else {
				return null;
			}
        }
        else {
            return false;
        }
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispProf_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона диспансеризации';
		$arr['isstenocard'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsStenocard',
			'label' => 'Подозрение на наличие стенокардии напряжения',
			'save' => '',
			'type' => 'id'
		);
		$arr['isdoublescan'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsDoubleScan',
			'label' => 'Показания к проведению дуплексного сканирования брахицефальных артерий',
			'save' => '',
			'type' => 'id'
		);
		$arr['istub'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsTub',
			'label' => 'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких',
			'save' => '',
			'type' => 'id'
		);
		$arr['isesophag'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsEsophag',
			'label' => 'Показания к проведению эзофагогастродуоденоскопии',
			'save' => '',
			'type' => 'id'
		);
		$arr['issmoking'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsSmoking',
			'label' => 'Курение',
			'save' => '',
			'type' => 'id'
		);
		$arr['isriskalco'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsRiskAlco',
			'label' => 'Риск пагубного потребления алкоголя',
			'save' => '',
			'type' => 'id'
		);
		$arr['isalcodepend'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsAlcoDepend',
			'label' => 'Подозрение на зависимость от алкоголя',
			'save' => '',
			'type' => 'id'
		);
		$arr['islowactiv'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsLowActiv',
			'label' => 'Низкая физическая активность',
			'save' => '',
			'type' => 'id'
		);
		$arr['isirrational'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsIrrational',
			'label' => 'Нерациональное питание',
			'save' => '',
			'type' => 'id'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Подозрение на хроническое неинфекционное заболевание, требующее дообследования',
			'save' => '',
			'type' => 'id'
		);
		$arr['isdisp'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsDisp',
			'label' => 'Взят на диспансерное наблюдение',
			'save' => '',
			'type' => 'id'
		);
		$arr['isambul'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsAmbul',
			'label' => 'Нуждается в амбулаторном дополнительном лечении (обследовании)',
			'save' => '',
			'type' => 'id'
		);
		$arr['isstac'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsStac',
			'label' => 'Нуждается в стац. спец., в т.ч. высокотехнологичном дополнительном лечении (обследовании)',
			'save' => '',
			'type' => 'id'
		);
		$arr['issanator'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsSanator',
			'label' => 'Нуждается в санаторно-курортном лечении',
			'save' => '',
			'type' => 'id'
		);
		$arr['sumrick'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_SumRick',
			'label' => 'Суммарный сердечно-сосудистый риск',
			'save' => '',
			'type' => 'int'
		);
		$arr['risktype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RiskType_id',
			'label' => 'Тип риска',
			'save' => '',
			'type' => 'id'
		);
		$arr['isschool'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsSchool',
			'label' => 'Школа пациента',
			'save' => '',
			'type' => 'id'
		);
		$arr['isprophcons'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsProphCons',
			'label' => 'Углубленное профилактическое консультирование',
			'save' => '',
			'type' => 'id'
		);
		$arr['healthkind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthKind_id',
			'label' => 'Группа здоровья',
			'save' => '',
			'type' => 'id'
		);
		$arr['isendstage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsEndStage',
			'label' => 'Случай профосмотра закончен',
			'save' => '',
			'type' => 'id'
		);
		$arr['cardiorisktype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CardioRiskType_id',
			'label' => 'Риск сердечно-сосудистых заболеваний',
			'save' => '',
			'type' => 'id'
		);
		$arr['needdopcure_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NeedDopCure_id',
			'label' => 'Дополнительное лечение',
			'save' => '',
			'type' => 'id'
		);
		$arr['ishypoten'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsHypoten',
			'label' => 'Гипотензивная терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['islipid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsLipid',
			'label' => 'Гиполипидемическая терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['ishypoglyc'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsHypoglyc',
			'label' => 'Гипогликемическая терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['isdvn'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispProf_IsDVN',
			'label' => 'Создан из ДВН',
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
		return 103;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispProf';
	}

	/**
	 * Получение списка случаев ПОВН по пациенту
	 */
	function loadEvnPLDispProfList($data)
	{
		return $this->queryResult("
			select
				EPLDP.EvnPLDispProf_id,
				year(EPLDP.EvnPLDispProf_setDate) as EvnPLDispProf_Year,
				convert(varchar(10), EPLDP.EvnPLDispProf_setDate, 104) as EvnPLDispProf_setDate,
				convert(varchar(10), EPLDP.EvnPLDispProf_disDate, 104) as EvnPLDispProf_disDate,
				EPLDP.EvnPLDispProf_IsEndStage,
				case
					when ISNULL(EPLDP.EvnPLDispProf_IsEndStage, 1) = 1 and PS.Lpu_id = :Lpu_id then 'edit'
					else 'view'
				end as accessType,
				case when EPLDP.EvnPLDispProf_IsKKND = 2 then 'Да' else 'Нет' end as EvnPLDispProf_IsKKND,
				L.Lpu_Nick,
				EPLDP.Person_id,
				EPLDP.Server_id
			from
				v_EvnPLDispProf EPLDP with (nolock)
				left join v_Lpu L with (nolock) on L.Lpu_id = EPLDP.Lpu_id
				left join v_PersonState PS with (nolock) on PS.Person_id = EPLDP.Person_id
			where
				EPLDP.Person_id = :Person_id
				and year(EPLDP.EvnPLDispProf_setDate) >= 2019
				and EPLDP.Lpu_id " . getLpuIdFilter($data) . "
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Проверка наличия у пациента карты ДВН или ПОВН в текущем году перед добавлением новой карты ПОВН
	 */
	function checkBeforeAddEvnPLDisp($data)
	{
		$resp = $this->queryResult("
			declare @Year int = year(dbo.tzGetDate());
			
			select top 1
				EvnPLDispDop13_id as id
			from
				v_EvnPLDispDop13 EPLDD with (nolock)
			where
				EPLDD.Person_id = :Person_id
				and year(EPLDD.EvnPLDispDop13_setDate) = @Year
				
			union all
			
			select top 1
				EPLDP.EvnPLDispProf_id as id
			from
				v_EvnPLDispProf EPLDP with (nolock)
			where
				EPLDP.Person_id = :Person_id
				and year(EPLDP.EvnPLDispProf_setDate) = @Year
		", array(
			'Person_id' => $data['Person_id']
		));

		if (!empty($resp[0]['id'])) {
			return array('Error_Msg' => 'Создание карты профилактического осмотра невозможно, так как в этом году пациент уже прошел профилактический осмотр или диспансеризацию взрослого населения');
		} else {
			return array('Error_Msg' => '');
		}
	}
}
?>