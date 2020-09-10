<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispScreenOnko_model - модель для работы с талонами скрининговых исследований по онкологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Swan
* @version      07.06.2019
*/

require_once('EvnPLDispAbstract_model.php');

class EvnPLDispScreenOnko_model extends EvnPLDispAbstract_model
{
	/**
	 *	Конструктор
	 */	
    function __construct()
    {
        parent::__construct();

		$this->inputRules = array(
			'addEvnPLDispScreenOnko' => array(
				array('field' => 'PersonEvn_id', 'label' => 'Идентификатор события пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'Evn_pid', 'label' => '', 'rules' => '', 'type' => 'id'),
			),
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispScreenOnko_id',
					'label' => 'Идентификатор талона скрининговых исследований по онкологии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenOnko_setDate',
					'label' => 'Дата осмотра',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispScreenOnko_id',
					'label' => 'Идентификатор карты ПОС',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenOnko_setDate',
					'label' => 'Дата осмотра',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека в событии',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор медперсонала',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispInfoConsentData',
					'label' => 'Данные грида по информир. добр. согласию',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnPLDispScreenOnko' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreenOnko_pid', 'label' => 'Идентификатор посещения', 'rules' => '', 'type' => 'id'),
			),
			'deleteEvnPLDispScreenOnko' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => '', 'type' => 'id'),
				array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id')
			),
			'getProtokolFieldList' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => '', 'type' => 'id'),
				array('field' => 'checkRisk', 'label' => 'Флаг для подсчёта уровня риска', 'rules' => '', 'type' => 'boolean'),
			),
			'saveFormalizedInspection' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'SurveyType_id', 'label' => 'Идентификатор осмотра', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonEvn_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'data', 'label' => '', 'rules' => '', 'type' => 'json_array'),
			),
			'saveResult' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnPLDispScreenOnko_IsSuspectZNO', 'label' => 'Подозрение на ЗНО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Diag_spid', 'label' => 'Подозрение на диагноз', 'rules' => '', 'type' => 'id'),
			),
			'loadEvnPLDispScreenPrescrList' => array(
				array('field' => 'EvnPLDispScreenOnko_id', 'label' => 'Идентификатор карты ПОС', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'UslugaComplexList', 'label' => 'Список услуг для назначений', 'rules' => '', 'type' => 'string'),
				array('field' => 'userLpuSection_id', 'label' => 'Идентификатор отделения пользователя', 'rules' => 'required', 'type' => 'int'),
			),
			'checkEvnPLDispScreenOnkoExists' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			)
		);
	}
	
	/**
	 * Получение входящих параметров
	 */
	function getInputRulesAdv($rule = null) {
		if (empty($rule)) {
			return $this->inputRules;
		} else {
			return $this->inputRules[$rule];
		}
	}
	
	/**
	 * Добавление первичного онкоскрининга
	 */
	function addEvnPLDispScreenOnko($data) {
		$params = array(
			'PersonEvn_id'=>$data['PersonEvn_id'],
			'Evn_pid'=>$data['Evn_pid'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		
		$sql ="
			DECLARE	@return_value int,
					@EvnPLDispScreenOnko_id bigint,
					@EvnPLDispScreenOnko_insDT datetime,
					@EvnPLDispScreenOnko_updDT datetime,
					@EvnPLDispScreenOnko_Index bigint,
					@EvnPLDispScreenOnko_Count bigint,
					@Error_Code int,
					@Error_Message varchar(4000),
					@ThisDate datetime = dbo.tzGetDate()

			EXEC	@return_value = [dbo].[p_EvnPLDispScreenOnko_ins]
					@EvnPLDispScreenOnko_id = @EvnPLDispScreenOnko_id OUTPUT,
					@EvnPLDispScreenOnko_pid = :Evn_pid,
					@Lpu_id = :Lpu_id,
					@EvnPLDispScreenOnko_setDT = @ThisDate,
					@EvnPLDispScreenOnko_insDT = @EvnPLDispScreenOnko_insDT OUTPUT,
					@EvnPLDispScreenOnko_updDT = @EvnPLDispScreenOnko_updDT OUTPUT,
					@EvnPLDispScreenOnko_Index = @EvnPLDispScreenOnko_Index OUTPUT,
					@EvnPLDispScreenOnko_Count = @EvnPLDispScreenOnko_Count OUTPUT,
					@PersonEvn_id = :PersonEvn_id,
					@Server_id = :Server_id,
					@AttachType_id = 4,
					@DispClass_id = 27,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@EvnPLDispScreenOnko_id as 'EvnPLDispScreenOnko_id',
					@EvnPLDispScreenOnko_insDT as 'EvnPLDispScreenOnko_insDT',
					@EvnPLDispScreenOnko_updDT as 'EvnPLDispScreenOnko_updDT',
					@EvnPLDispScreenOnko_Index as 'EvnPLDispScreenOnko_Index',
					@EvnPLDispScreenOnko_Count as 'EvnPLDispScreenOnko_Count',
					@Error_Code as 'Error_Code',
					@Error_Message as 'Error_Msg'

		";
		//~ exit(getDebugSQL($sql, $data));
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				//~ $this->db->trans_rollback();
				return $resp[0];
			}
		}
		return array('success'=>false, 'Error_Msg' => 'Ошибка при создании карты первичного онкологического скрининга');
	}
	
	/**
	 * Загрузка согласий/услуг
	 */
	function loadDopDispInfoConsent($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id'],
			'Person_id' => $data['Person_id'],
			'EvnPLDispScreenOnko_setDate' => $data['EvnPLDispScreenOnko_setDate'],
			'DispClass_id' => 27 //$data['DispClass_id']
		);
		$select = "
			select
				ISNULL(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as DopDispInfoConsent_id,
				MAX(DDIC.EvnPLDisp_id) as EvnPLDispScreenOnko_id,
				MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id,
				ISNULL(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as SurveyTypeLink_IsNeedUsluga,
				ISNULL(MAX(STL.SurveyTypeLink_IsDel), 1) as SurveyTypeLink_IsDel,
				MAX(ST.SurveyType_Code) as SurveyType_Code,
				MAX(ST.SurveyType_IsVizit) as SurveyType_IsVizit,
				MAX(ST.SurveyType_Name) as SurveyType_Name,
				case WHEN MAX(DDIC.DopDispInfoConsent_id) is null or MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
				case WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier,
				--case WHEN ISNULL(MAX(SurveyTypeLink_IsImpossible), 1) = 1 then 'hidden' WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2 then '1' else '0' end as DopDispInfoConsent_IsImpossible,
				case WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2 then '1' else '0' end as DopDispInfoConsent_IsImpossible,
				MAX(STL.SurveyTypeLink_IsUslPack) as SurveyTypeLink_IsUslPack,
				case when (MAX(STL.SurveyTypeLink_IsPrimaryFlow) = 2 and @age not between Isnull(MAX(STL.SurveyTypeLink_From), 0) and  Isnull(MAX(STL.SurveyTypeLink_To), 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect,
				case when MAX(ST.SurveyType_Code) IN (1,48) then 0 else 1 end as sortOrder
			from v_SurveyTypeLink STL (nolock)
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispScreenOnko_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply (
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop with (nolock)
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispScreenOnko_id
						and ISNULL(EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				" . implode(' ', $joinList) . "
			where 
				IsNull(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
				and (IsNull(STL.Sex_id, @sex_id) = @sex_id) -- по полу
				and (@age between Isnull(SurveyTypeLink_From, 0) and  Isnull(SurveyTypeLink_To, 999))
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispScreenOnko_setDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate > :EvnPLDispScreenOnko_setDate)
				and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and ISNULL(STL.SurveyTypeLink_IsEarlier, 1) = 1
				and (STL.SurveyTypeLink_Period is null or STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = @age % STL.SurveyTypeLink_Period)
		";
		
		$selectwith = "
			with consents as (
				{$select}
				group by STL.SurveyType_id, STL.SurveyTypeLink_IsDel
			)
			select
				usluga.EvnUsluga_id, 
				eup.EvnUslugaPar_id,
				usluga.UslugaComplex_id as CompletedUslugaComplex_id, 
				case 
					when STLL.SurveyType_id=2 
					then convert(varchar(10), PJA.PersonOnkoProfile_DtBeg, 104)
					else usluga.EvnUsluga_Date
				end as EvnUsluga_Date,
				case 
					when STLL.SurveyType_id=2 
					then MSF.Person_Fio
					else usluga.MedPersonalFIO
				end as MedPersonalFIO,
				STLL.SurveyTypeLink_Period,
				STLL.SurveyType_id,
				convert(varchar(10), PJA.PersonOnkoProfile_DtBeg, 104) as onkoAnketaDate,
				PJA.PersonOnkoProfile_id,
				consents.DopDispInfoConsent_id,
				consents.EvnPLDispScreenOnko_id,
				consents.SurveyTypeLink_id,
				consents.SurveyTypeLink_IsNeedUsluga,
				consents.SurveyTypeLink_IsDel,
				consents.SurveyType_Code,
				consents.SurveyType_IsVizit,
				consents.SurveyType_Name,
				consents.DopDispInfoConsent_IsAgree,
				consents.DopDispInfoConsent_IsEarlier,
				consents.DopDispInfoConsent_IsImpossible,
				consents.SurveyTypeLink_IsUslPack,
				consents.DopDispInfoConsent_IsAgeCorrect,
				consents.sortOrder,
				STLL.UslugaComplex_id
			from 
				consents
				left join v_SurveyTypeLink STLL with (nolock) on STLL.SurveyTypeLink_id = consents.SurveyTypeLink_id
				outer apply(
					select top 1 
						EU.EvnUsluga_id
						,EU.UslugaComplex_id
						,MP.Person_Fio as MedPersonalFIO
						,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_Date
					from 
						v_SurveyTypeLink STL2 with (nolock)
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL2.SurveyType_id
						inner join v_EvnUsluga EU with (nolock) on EU.UslugaComplex_id = STL2.UslugaComplex_id
						inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EU.MedPersonal_id
					where 
						STL2.SurveyType_id = STLL.SurveyType_id
						and EU.Person_id = :Person_id
						and EU.EvnUsluga_setDate is not null
						and isnull(DATEADD(YEAR, STLL.SurveyTypeLink_Period, EU.EvnUsluga_setDate), dbo.tzGetDate()) >= dbo.tzGetDate()
						and (ST.SurveyType_IsVizit = 1 or EU.EvnUsluga_pid = :EvnPLDispScreenOnko_id)
					order by EU.EvnUsluga_setDate DESC
				) usluga
				outer apply(
					select top 1 
						eup.EvnUslugaPar_id
					from 
						v_EvnUslugaPar eup with (nolock)
					where 
						eup.Person_id = :Person_id and eup.UslugaComplex_id = usluga.UslugaComplex_id
					order by 
						case when eup.EvnUslugaPar_pid = :EvnPLDispScreenOnko_id then 0 else 1 end ASC, 
						eup.EvnUslugaPar_setDate DESC
				) eup
				left join onko.v_ProfileJurnalAct PJA with(nolock) on PJA.Person_id=:Person_id and STLL.SurveyType_id = 2
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = PJA.MedStaffFact_id
		";
		
		$query = "
			declare
				@age int,
				@originalAge int,
				@sex_id bigint;

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age(Person_BirthDay, dbo.tzGetDate() )
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
				
			if ( @age > 99 )
				set @age = 99;

			{$selectwith}
			
			order by consents.sortOrder, consents.SurveyType_Code		
		";
		//~ exit(getDebugSql($query, $params));;;
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранить согласия/услуги
	 */
	function saveDopDispInfoConsent($data) {
		$items = json_decode($data['DopDispInfoConsentData'], true);
		$proc = '';
		//~ $this->db->trans_begin();
		foreach($items as $item) {
			//коррект-е формата значений флагов
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

			if (!empty($item['DopDispInfoConsent_IsImpossible']) && ($item['DopDispInfoConsent_IsImpossible'] == '1' || $item['DopDispInfoConsent_IsImpossible'] === true)) {
				$item['DopDispInfoConsent_IsImpossible'] = 2;
			} else {
				$item['DopDispInfoConsent_IsImpossible'] = 1;
			}
			
			
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$DopDispInfoConsentList[] = $item['DopDispInfoConsent_id'];
				$proc = 'p_DopDispInfoConsent_upd';
				//~ var_dump($item);exit;
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}
			
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispInfoConsent_id;
				
				exec {$proc}
					@DopDispInfoConsent_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDispScreenOnko_id, 
					@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
					@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
					@DopDispInfoConsent_IsImpossible = :DopDispInfoConsent_IsImpossible,
					@SurveyTypeLink_id = :SurveyTypeLink_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$params = array(
				'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Server_id' => $data['Server_id'],
				'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
				'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
				'DopDispInfoConsent_IsImpossible' => $item['DopDispInfoConsent_IsImpossible'],
				'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
				'UslugaComplex_id' => $item['UslugaComplex_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'Lpu_id' => $data['Lpu_id'],
				'EvnPLDispScreenOnko_setDate' => $data['EvnPLDispScreenOnko_setDate'],
				'pmUser_id' => $data['pmUser_id']
			);
			//~ exit(getDebugSQL($query, $params));
			$result = $this->db->query($query, $params);
			
			if ( is_object($result) ) {
				$res = $result->result('array');
				if ( is_array($res) && count($res) > 0 ) {
					if ( !empty($res[0]['Error_Msg']) ) {
						//~ $this->db->trans_rollback();
						return array(
								'success' => false,
								'Error_Msg' => $res[0]['Error_Msg']
							);
					} else $params['DopDispInfoConsent_id'] = $res[0]['DopDispInfoConsent_id'];
				}
			}
			
			//~ var_dump($result);exit();
			
			if($item['DopDispInfoConsent_IsEarlier']==2 && !empty($item['UslugaComplex_id'])) {
				// если указано "пройдено ранее"
				// ищем услугу в EvnUslugaDispDop, если нет то создаём новую, иначе обновляем.
				$query = "
					select top 1 EUDD.EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD
					where EUDD.DopDispInfoConsent_id = :DopDispInfoConsent_id
				";
				//~ exit(getDebugSQL($query, $params));
				//$result = $this->db->query($query, $params);
				$EvnUslugaDispDop_id = $this->getFirstResultFromQuery($query, $params);

				// Сохраняем услугу
				if ( !empty($EvnUslugaDispDop_id) ) {
					$params['EvnUslugaDispDop_id'] = $EvnUslugaDispDop_id;
					$proc = 'p_EvnUslugaDispDop_upd';
				}
				else {
					$params['EvnUslugaDispDop_id'] = null;
					$proc = 'p_EvnUslugaDispDop_ins';
				}

				$params['UslugaComplex_id'] = $item['UslugaComplex_id'];

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
						@EvnUslugaDispDop_pid =:EvnPLDispScreenOnko_id,
						@DopDispInfoConsent_id = :DopDispInfoConsent_id,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@EvnDirection_id = NULL,
						@PersonEvn_id = :PersonEvn_id,
						@PayType_id = @PayType_id,
						@UslugaPlace_id = 1,
						@UslugaComplex_id = :UslugaComplex_id,
						@EvnUslugaDispDop_setDT = :EvnPLDispScreenOnko_setDate,
						@ExaminationPlace_id = NULL,
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
				$EvnUslugaDispDop_id = null;
				
				//~ exit(getDebugSQL($query, $params));
				$result = $this->db->query($query, $params);

				if ( !is_object($result) ) {
					//~ $this->db->trans_rollback();
					return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					//~ $this->db->trans_rollback();
					return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
				}
				else if ( !empty($resp[0]['Error_Msg']) ) {
					//~ $this->db->trans_rollback();
					return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
				} else $EvnUslugaDispDop_id = $resp[0]['EvnUslugaDispDop_id'];
			}
		} //-items
		
		//~ $this->db->trans_commit();
		
		$epds = $this->getFirstRowFromQuery("
			select 
				EvnPLDispScreenOnko_pid,
				Person_id,
				Diag_spid
			from v_EvnPLDispScreenOnko (nolock)
			where EvnPLDispScreenOnko_id = ?
		", [$data['EvnPLDispScreenOnko_id']]);
		
		if ($epds != false && !empty($epds['Diag_spid'])) {
			$this->id = $data['EvnPLDispScreenOnko_id'];
			$this->pid = $epds['EvnPLDispScreenOnko_pid'];
			$this->evnClassId = 203;
			$this->Person_id = $epds['Person_id'];
			$this->Diag_spid = $epds['Diag_spid'];
			$this->sessionParams = $data['session'];
			
			$this->load->model('MorbusOnkoSpecifics_model');
			$this->MorbusOnkoSpecifics_model->checkAndCreateSpecifics($this);
		}

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id']
		);
	}

	/**
	 * Загрузка основных полей формы
	 * @param $data
	 * @return array|false
	 */
	function loadEvnPLDispScreenOnko($data) {
		$where = '';
		$params = [];
		
		if ( !empty($data['EvnPLDispScreenOnko_id']) ) {
			$where = 'where EvnPLDispScreenOnko_id = :EvnPLDispScreenOnko_id';
			$params['EvnPLDispScreenOnko_id'] = $data['EvnPLDispScreenOnko_id'];
		} elseif ( !empty($data['EvnPLDispScreenOnko_pid'])) {
			$where = 'where EvnPLDispScreenOnko_pid = :EvnPLDispScreenOnko_pid';
			$params['EvnPLDispScreenOnko_pid'] = $data['EvnPLDispScreenOnko_pid'];
		} else {
			return [];
		}
		
		return $this->queryResult("
			select 
				isnull(convert(varchar(10), EvnPLDispScreenOnko_setDate, 104), '') as EvnPLDispScreenOnko_setDate
				,EvnPLDispScreenOnko_id
				,EvnPLDispScreenOnko_IsSuspectZNO
				,Diag_spid
			from v_EvnPLDispScreenOnko (nolock)
			{$where}
		", $params);
	}

	function deleteEvnPLDispScreenOnko($data) {

		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			exec p_EvnPLDispScreenOnko_del
				@EvnPLDispScreenOnko_id = :EvnPLDispScreenOnko_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$params = array(
			"EvnPLDispScreenOnko_id" => $data["EvnPLDispScreenOnko_id"],
			"pmUser_id" => $data["pmUser_id"]
		);
		$res = $this->db->query(
		//echo getDebugSQL()
			$sql,
			$params
		);

		if ( is_object($res) ) {
			$result = $res->result('array');
			if ( isset($result[0]['Error_Msg']) ) {
				return array(
					'Error_Msg' => $result[0]['Error_Msg']
				);
			}
		}

		return array(
			'Error_Msg' => ''
		);
	}
	
	/**
	 * Получить список полей раздела Протокол осмотра
	 */
	function getProtokolFieldList($data) {
		$filter = "";
		$join = "";
		$select = "";
		if (!empty($data['checkRisk'])) {
			$filter = "
			and FIP.FormalizedInspectionParams_Directory = 'PathologyType'
			and pt.PathologyType_WRisk is not null
			";
			$join = "inner join v_PathologyType pt (nolock) on pt.PathologyType_id = FI.FormalizedInspection_Result";
			$select = "pt.PathologyType_WRisk,";
		}
		$query = "
			select
				{$select}
				ST.SurveyType_id, 
				ST.SurveyType_Name,
				STL.UslugaComplex_id,
				FIP.FormalizedInspectionParams_id, 
				FIP.FormalizedInspectionParams_Name, 
				FIP.FormalizedInspectionParams_Directory,
				FI.FormalizedInspection_Result,
				FI.FormalizedInspection_DirectoryAnswer_id,
				FI.FormalizedInspection_NResult,
				DDIC.DopDispInfoConsent_id,
				isnull(FI.EvnUslugaDispDop_setDT, '') as EvnUslugaDispDop_setDT,
				isnull(FI.MedPersonal_Fin, '') as MedPersonal_Fin
			from v_FormalizedInspectionParams FIP (nolock)
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = FIP.SurveyType_id
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyType_id = ST.SurveyType_id
				inner join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispScreenOnko_id
				outer apply (
					select 
						FI.FormalizedInspection_Result,
						FI.FormalizedInspection_DirectoryAnswer_id,
						FI.FormalizedInspection_NResult,
						isnull(convert(varchar(10), EUDD.EvnUslugaDispDop_setDT, 104), '') as EvnUslugaDispDop_setDT,
						upper(substring(MSF.Person_SurName,1,1)) + substring(lower(MSF.Person_SurName), 2, len(MSF.Person_SurName))
							+' '+ isnull(substring(MSF.Person_FirName,1,1) +'.', '')
							+' '+ isnull(substring(MSF.Person_SecName,1,1) +'.', '') as MedPersonal_Fin
					from v_FormalizedInspection FI (nolock)
					inner join v_EvnUslugaDispDop EUDD (nolock) on EUDD.EvnUslugaDispDop_id = FI.EvnUslugaDispDop_id
					inner join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = EUDD.MedStaffFact_id
					where 
						FI.FormalizedInspectionParams_id = FIP.FormalizedInspectionParams_id and 
						EvnUslugaDispDop_pid = :EvnPLDispScreenOnko_id
				) FI
				{$join}
			where 
				ST.SurveyType_IsVizit = 2 and 
				(DDIC.DopDispInfoConsent_IsAgree = 2 or DDIC.DopDispInfoConsent_IsEarlier = 2)
				{$filter}
			order by ST.SurveyType_id, FIP.FormalizedInspectionParams_id
		";

		$result = $this->queryResult($query, [
			'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id']
		]);
		if (!empty($data['checkRisk'])) {
			$WRisk = 0;
			foreach ($result as $item) {
				$WRisk += $item['PathologyType_WRisk'];
			}

			return array('WRisk'=>$WRisk);
		}
		$rdata = [];

		foreach($result as $row) {

			if (!isset($rdata[$row['SurveyType_id']])) {
				$rdata[$row['SurveyType_id']] = [
					'SurveyType_id' => $row['SurveyType_id'],
					'SurveyType_Name' => $row['SurveyType_Name'],
					'UslugaComplex_id' => $row['UslugaComplex_id'],
					'DopDispInfoConsent_id' => $row['DopDispInfoConsent_id'],
					'EvnUslugaDispDop_setDT' => $row['EvnUslugaDispDop_setDT'],
					'MedPersonal_Fin' => $row['MedPersonal_Fin'],
					'data' => []
				];
			}

			$row['PathologyType'] = $this->queryResult('select PathologyType_id, PathologyType_Name, PathologyType_IsDefault from v_PathologyType (nolock) where FormalizedInspectionParams_id = ?', [$row['FormalizedInspectionParams_id']]);
			$row['TopographyType'] = $this->queryResult('select TopographyType_id, TopographyType_Name from v_TopographyType (nolock) where SurveyType_id = ?', [$row['SurveyType_id']]);
			foreach($row['TopographyType'] as &$r) {
				$r = [$r['TopographyType_id'], $r['TopographyType_Name']];
			}

			$rdata[$row['SurveyType_id']]['data'][] = $row;
		}

		return $rdata;
	}

	/**
	 * Сохранение раздела Протокола осмотра
	 */
	function saveFormalizedInspection($data) {
		
		$EvnUslugaDispDop_id = $this->getFirstResultFromQuery("
			select top 1 EvnUslugaDispDop_id
			from v_EvnUslugaDispDop
			where 
				EvnUslugaDispDop_pid = :EvnPLDispScreenOnko_id and 
				UslugaComplex_id = :UslugaComplex_id
		", [
			'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		]);
		
		if ($EvnUslugaDispDop_id === false) {
			
			$proc = $EvnUslugaDispDop_id ? 'p_EvnUslugaDispDop_upd' : 'p_EvnUslugaDispDop_ins';
		
			$params = [
				'EvnUslugaDispDop_id' => $EvnUslugaDispDop_id ?: null,
				'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id'],
				'SurveyType_id' => $data['SurveyType_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'pmUser_id' => $data['pmUser_id'],
			];

			$query = "
				declare
					@EvnUslugaDispDop_id bigint,
					@PayType_id bigint,
					@EvnPLDispScreenOnko_setDate datetime = dbo.tzGetDate(),
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @EvnUslugaDispDop_id = :EvnUslugaDispDop_id;
				set @PayType_id = (select top 1 PayType_id from v_PayType (nolock) where PayType_SysNick = 'dopdisp');
				exec {$proc}
					@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
					@EvnUslugaDispDop_pid = :EvnPLDispScreenOnko_id,
					@SurveyType_id = :SurveyType_id,
					@UslugaComplex_id = :UslugaComplex_id,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@PayType_id = @PayType_id,
					@UslugaPlace_id = 1,
					@EvnUslugaDispDop_setDT = @EvnPLDispScreenOnko_setDate,
					@MedPersonal_id = :MedPersonal_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
			$resp = $this->queryResult($query, $params);

			if ( !is_array($resp) || count($resp) == 0 ) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			} else {
				$EvnUslugaDispDop_id = $resp[0]['EvnUslugaDispDop_id'];
			}
		}
		
		foreach($data['data'] as $fip) {
			$FormalizedInspection_id = $this->getFirstResultFromQuery("
				select FormalizedInspection_id 
				from FormalizedInspection (nolock) 
				where 
					EvnUslugaDispDop_id = :EvnUslugaDispDop_id and 
					FormalizedInspectionParams_id = :FormalizedInspectionParams_id 
					
			", [
				'EvnUslugaDispDop_id' => $EvnUslugaDispDop_id,
				'FormalizedInspectionParams_id' => $fip->FormalizedInspectionParams_id
			]);
			
			$proc = $FormalizedInspection_id ? 'upd' : 'ins';
			$params = [
				'FormalizedInspection_id' => $FormalizedInspection_id ?: null,
				'EvnUslugaDispDop_id' => $EvnUslugaDispDop_id,
				'FormalizedInspectionParams_id' => $fip->FormalizedInspectionParams_id,
				'FormalizedInspection_Result' => $fip->FormalizedInspection_Result,
				'FormalizedInspection_DirectoryAnswer_id' => $fip->FormalizedInspection_DirectoryAnswer_id,
				'FormalizedInspection_NResult' => $fip->FormalizedInspection_NResult,
				'pmUser_id' => $data['pmUser_id']
			];
				
			$query = "
				declare
					@FormalizedInspection_id bigint,
					@PayType_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @FormalizedInspection_id = :FormalizedInspection_id;
				exec p_FormalizedInspection_{$proc}
					@FormalizedInspection_id = @FormalizedInspection_id output,
					@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
					@FormalizedInspectionParams_id = :FormalizedInspectionParams_id,
					@FormalizedInspection_Result = :FormalizedInspection_Result,
					@FormalizedInspection_DirectoryAnswer_id = :FormalizedInspection_DirectoryAnswer_id,
					@FormalizedInspection_NResult = :FormalizedInspection_NResult,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @FormalizedInspection_id as FormalizedInspection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
			$resp = $this->queryResult($query, $params);
			
			if ( !is_array($resp) || count($resp) == 0 ) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		}

		return array('success' => true, 'Error_Msg' => '');
	}

	/**
	 * Сохранение раздела Результат
	 */
	function saveResult($data) {
		
		$this->db->query("update EvnPLDisp with (rowlock) set EvnPLDisp_IsSuspectZNO = :EvnPLDisp_IsSuspectZNO, Diag_spid = :Diag_spid where EvnPLDisp_id = :EvnPLDisp_id", [
			'EvnPLDisp_IsSuspectZNO' => $data['EvnPLDispScreenOnko_IsSuspectZNO'],
			'Diag_spid' => $data['Diag_spid'],
			'EvnPLDisp_id' => $data['EvnPLDispScreenOnko_id']
		]);
		
		return array('success' => true, 'Error_Msg' => '');
	}

	/**
	 * Получить список назначений карты ПОС
	 */
	function loadEvnPLDispScreenPrescrList($data) {
		$UslugaComplexList = json_decode($data['UslugaComplexList']);

		// Для тестирования #156667
		if(getRegionNick() == 'perm'){
			//$data['userLpu_id'] = 10010833; // для тестовой Перми сработало
			$data['userLpu_id'] = 101;
			$data['userLpuBuilding_id'] = null;
			$data['userLpuUnit_id'] = null;
		} else {
			$params = array(
				'LpuSection_id' => $data['userLpuSection_id'],
			);
			$sql = "
			Select
				user_ls.Lpu_id,
				user_lu.LpuBuilding_id,
				user_ls.LpuUnit_id
			from v_LpuSection user_ls with (nolock)
			inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			where user_ls.LpuSection_id = :LpuSection_id
		";
			$result = $this->db->query($sql, $params);
			if (is_object($result))
			{
				$rc = $result->result('array');
				if (count($rc)>0 && is_array($rc[0])) {
					$data['userLpu_id'] = $rc[0]['Lpu_id'];
					$data['userLpuBuilding_id'] = $rc[0]['LpuBuilding_id'];
					$data['userLpuUnit_id'] = $rc[0]['LpuUnit_id'];
				}
			}
		}

		$params = array(
			'EvnPrescr_pid' => $data['EvnPLDispScreenOnko_id']
		);

		$query = "
			WITH EvnPrescr
			AS (SELECT 
					COALESCE(EPLD.EvnPrescrLabDiag_id,EPFDU.EvnPrescrFuncDiag_id) AS EvnPrescr_id,
					COALESCE(EPLD.UslugaComplex_id, EPFDU.UslugaComplex_id) AS UslugaComplex_id,
					pt.PrescriptionType_Code,
					isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				FROM v_EvnPrescr EP WITH (NOLOCK)
					LEFT JOIN EvnPrescrLabDiag EPLD WITH (NOLOCK)
						ON EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 11
					LEFT JOIN EvnPrescrFuncDiagUsluga EPFDU WITH (NOLOCK)
						ON EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 12
					LEFT JOIN v_PrescriptionType pt WITH (NOLOCK)
						ON pt.PrescriptionType_id = EP.PrescriptionType_id
				WHERE EP.EvnPrescr_pid = :EvnPrescr_pid 
				--'730023881307390'
					  AND EP.PrescriptionType_id IN ( 11, 12 )
					  AND EP.PrescriptionStatusType_id != 3
			)
			
			SELECT 
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				EP.EvnPrescr_id,
				EP.EvnPrescr_IsExec,
				EvnStatus.EvnStatus_SysNick,
				COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) AS PrescriptionType_Code,
				case	
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 11 then 'EvnPrescrLabDiag'
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 12 then 'EvnPrescrFuncDiag'
					else ''
				end as object,
				case
						when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
						when TTR.TimetableResource_id is not null then isnull(convert(varchar(10), TTR.TimetableResource_begTime, 104),'')+' '+isnull(convert(varchar(5), TTR.TimetableResource_begTime, 108),'')
						when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
					else '' end as RecDate,
				case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'')
					when TTR.TimetableResource_id is not null then isnull(R.Resource_Name,'') +' / '+ isnull(MS.MedService_Name,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end
				else '' end as RecTo,
				ED.*,
				MS.MedService_Nick,
				EUP.EvnUslugaPar_id
			FROM v_UslugaComplex uc with (nolock)
				OUTER APPLY (
					SELECT TOP 1
						   *
					FROM v_UslugaComplex uc11 WITH (NOLOCK)
					WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
				) uc11
				OUTER APPLY (
					SELECT *
					FROM EvnPrescr ep with (nolock)
					WHERE ep.UslugaComplex_id = uc.UslugaComplex_id
				) AS EP
				outer apply (
					Select top 1 
						ED.EvnDirection_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.MedService_id
						,ED.Resource_id
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.LpuSectionProfile_id
						,ED.EvnStatus_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where EP.EvnPrescr_id is not null 
						AND epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) as ED
				-- заказанная услуга для параклиники @TODO костыль!!!
				outer apply (
					Select top 1 EvnUslugaPar_id FROM v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EUP
				--left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ
				-- ресурсы
				outer apply (
						Select top 1 TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
				) TTR
				-- сам ресрс
				left join v_Resource R with (nolock) on R.Resource_id = ED.Resource_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				outer apply(
						select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
						from EvnStatusHistory ESH with(nolock)
						where ESH.Evn_id = ED.EvnDirection_id
							and ESH.EvnStatus_id = ED.EvnStatus_id
						order by ESH.EvnStatusHistory_begDate desc
					) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				OUTER APPLY (
					SELECT TOP 1
					CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'lab' THEN 11
					ELSE CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'func' THEN 12 END
					END AS PrescriptionType_Code
						   
					FROM v_UslugaComplexAttribute t1 WITH (NOLOCK)
						INNER JOIN v_UslugaComplexAttributeType t2 WITH (NOLOCK)
							ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					WHERE t1.UslugaComplex_id = ISNULL(uc11.UslugaComplex_id, uc.UslugaComplex_id)
						  AND t2.UslugaComplexAttributeType_SysNick IN ( 'lab','func' )
				) AS attr
			WHERE uc.UslugaComplex_id IN (" . implode(',', $UslugaComplexList) . ")
			
			--uc.UslugaComplex_id IN ( 4634872, 4426005, 206896, 201667, 200884, 200886, 200885 );
		";
		$result = $this->db->query($query, $params);
		//EvnPLDispScreenOnko_id: 730023881307390
		if ( is_object($result) ) {
			$UslugaList = $result->result('array');
		} else {
			// ошибка - нет назначений (исследований)
			return false;
		}
		
		if ($this->usePostgreLis) {
			$this->load->model('EvnPrescrLabDiag_model');
			$this->load->library('swPrescription');
			$listPostgres = $this->EvnPrescrLabDiag_model->doLoadViewDataPostgres('EvnPrescrPolka', $params['EvnPrescr_pid'], $data['session']);
			if(count($listPostgres)>0){
				foreach ($listPostgres as $key => $value) {
					if(!empty($value['EvnPrescr_id']) && !empty($value['UslugaComplex_id'])){
						$result = array_keys(array_column($UslugaList, 'EvnPrescr_id'), $value['EvnPrescr_id']);
						if(count($result)>0 && $UslugaList[$result[0]]['UslugaComplex_id'] == $value['UslugaComplex_id']){
							$UslugaList[$result[0]] = array_merge($UslugaList[$result[0]], $value);
						}
					}
				}
			}
		}


		$FuncUslList = $LabUslList = $OtherUslList = array();
		foreach ($UslugaList as $key => $usl) {
			if(!empty($usl['EvnDirection_id']) && !empty($usl['EvnStatus_SysNick']) && in_array($usl['EvnStatus_SysNick'], array('Canceled', 'Declined'))) $usl['EvnDirection_id'] = null;
			if(empty($usl['EvnDirection_id'])){
				switch ($usl['object']) {
					case 'EvnPrescrLabDiag':
						$LabUslList[] = $usl['UslugaComplex_id'];
						break;
					case 'EvnPrescrFuncDiag':
						$FuncUslList[] = $usl['UslugaComplex_id'];
						break;
					default;
				}
			}
		}
		$this->load->model('MedService_model');
		$resourceList = $this->MedService_model->getResourceListByFirstTT($data, $FuncUslList);
		$LabAndPZList = $this->MedService_model->getLabAndPZListByFirstTT($data, $LabUslList);

		if(!empty($resourceList))
			foreach($resourceList as $res)
				$resourceList[$res['UslugaComplex_id']] = $res;

		if(!empty($LabAndPZList))
			foreach($LabAndPZList as $lab)
				$LabAndPZList[$lab['UslugaComplex_id']] = $lab;

		if(!empty($resourceList) || !empty($LabAndPZList)){
			foreach($UslugaList as $key => $usl){
				switch ($usl['object']) {
					case 'EvnPrescrLabDiag':
						if(!empty($usl['EvnDirection_id']) && !empty($usl['EvnStatus_SysNick']) && in_array($usl['EvnStatus_SysNick'], array('Canceled', 'Declined'))){
							//отмененное, отклоненное направление
							$UslugaList[$key]['EvnDirection_id'] = null;
							$UslugaList[$key]['EvnDirection_Num'] = null;
							$UslugaList[$key]['EvnDirection_statusDate'] = null;
						}
						if(!empty($LabAndPZList[$usl['UslugaComplex_id']]))
							$UslugaList[$key] = array_merge($UslugaList[$key],$LabAndPZList[$usl['UslugaComplex_id']]);
						break;

					case 'EvnPrescrFuncDiag':
						if(!empty($resourceList[$usl['UslugaComplex_id']]))
							$UslugaList[$key] = array_merge($UslugaList[$key],$resourceList[$usl['UslugaComplex_id']]);
						break;
					default;
				}
			}
		}

		return $UslugaList;
	}

	/**
	 * Проверка наличия ПОС
	 */
	function checkEvnPLDispScreenOnkoExists($data) {

		return $this->getFirstRowFromQuery("
			declare @LastYear date = dateadd(YEAR, -1, dbo.tzGetDate());
			select top 1 EvnPLDispScreenOnko_id
			from v_EvnPLDispScreenOnko (nolock)
			where Person_id = :Person_id and EvnPLDispScreenOnko_setDate >= @LastYear
			order by EvnPLDispScreenOnko_setDate desc
		", $data);
	}
}