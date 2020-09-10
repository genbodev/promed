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
			'pmUser_id' => $data['pmUser_id']
		);
		
		$sql ="
			SELECT
				EvnPLDispScreenOnko_id AS \"EvnPLDispScreenOnko_id\",
				EvnPLDispScreenOnko_insDT AS \"EvnPLDispScreenOnko_insDT\",
				EvnPLDispScreenOnko_updDT AS \"EvnPLDispScreenOnko_updDT\",
				EvnPLDispScreenOnko_Index AS \"EvnPLDispScreenOnko_Index\",
				EvnPLDispScreenOnko_Count AS \"EvnPLDispScreenOnko_Count\",
				Error_Code AS \"Error_Code\",
				Error_Message AS \"Error_Msg\"
			FROM dbo.p_EvnPLDispScreenOnko_ins (
				EvnPLDispScreenOnko_pid := :Evn_pid,
				Lpu_id := :Lpu_id,
				EvnPLDispScreenOnko_setDT := dbo.tzGetDate(),
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				AttachType_id := 4,
				DispClass_id := 27,
				pmUser_id := :pmUser_id
			)
		";

		//~ exit(getDebugSQL($sql, $data));
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				//~ $this->db->trans_rollback();
				return $response[0];
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
			SELECT
				COALESCE(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) AS DopDispInfoConsent_id,
				MAX(DDIC.EvnPLDisp_id) AS EvnPLDispScreenOnko_id,
				MAX(STL.SurveyTypeLink_id) AS SurveyTypeLink_id,
				COALESCE(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) AS SurveyTypeLink_IsNeedUsluga,
				COALESCE(MAX(STL.SurveyTypeLink_IsDel), 1) AS SurveyTypeLink_IsDel,
				MAX(ST.SurveyType_Code) AS SurveyType_Code,
				MAX(ST.SurveyType_IsVizit) AS SurveyType_IsVizit,
				MAX(ST.SurveyType_Name) AS SurveyType_Name,
				CASE WHEN MAX(DDIC.DopDispInfoConsent_id) IS NULL OR MAX(DDIC.DopDispInfoConsent_IsAgree) = 2
					THEN 1
					ELSE 0
				END AS DopDispInfoConsent_IsAgree,
				CASE WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2
					THEN 1
					ELSE 0
				END AS DopDispInfoConsent_IsEarlier,
				/*CASE
					WHEN COALESCE(MAX(SurveyTypeLink_IsImpossible), 1) = 1
						THEN 'hidden'
					WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2
						THEN '1'
						ELSE '0'
				END AS DopDispInfoConsent_IsImpossible,*/
				case WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2 then '1' else '0' end as DopDispInfoConsent_IsImpossible,
				MAX(STL.SurveyTypeLink_IsUslPack) AS SurveyTypeLink_IsUslPack,
				CASE WHEN (MAX(STL.SurveyTypeLink_IsPrimaryFlow) = 2
					AND :Age NOT BETWEEN COALESCE(MAX(STL.SurveyTypeLink_From), 0)
					AND COALESCE(MAX(STL.SurveyTypeLink_To), 999)
				)
					THEN 0
					ELSE 1
				END AS DopDispInfoConsent_IsAgeCorrect,
				CASE WHEN MAX(ST.SurveyType_Code) IN (1,48)
					THEN 0
					ELSE 1
				END AS sortOrder
			FROM v_SurveyTypeLink STL
				LEFT JOIN v_DopDispInfoConsent DDIC ON DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					AND DDIC.EvnPLDisp_id = :EvnPLDispScreenOnko_id
				LEFT JOIN v_UslugaComplex UC ON UC.UslugaComplex_id = STL.UslugaComplex_id
				LEFT JOIN v_SurveyType ST ON ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					SELECT EvnUslugaDispDop_id
					FROM v_EvnUslugaDispDop
					WHERE UslugaComplex_id = UC.UslugaComplex_id
						AND EvnUslugaDispDop_rid = :EvnPLDispScreenOnko_id
						AND COALESCE(EvnUslugaDispDop_IsVizitCode, 1) = 1
					LIMIT 1
				) EUDD ON TRUE
				" . implode(' ', $joinList) . "
			WHERE 
				COALESCE(STL.DispClass_id, CAST(:DispClass_id as bigint)) = :DispClass_id -- этап
				AND (COALESCE(STL.Sex_id, CAST(:Sex_id as bigint)) = :Sex_id) -- по полу
				AND (:Age between COALESCE(SurveyTypeLink_From, 0) AND  COALESCE(SurveyTypeLink_To, 999))
				AND (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 OR (EUDD.EvnUslugaDispDop_id IS NOT NULL AND DDIC.DopDispInfoConsent_id IS NOT NULL))
				AND (STL.SurveyTypeLink_begDate IS NULL OR STL.SurveyTypeLink_begDate <= :EvnPLDispScreenOnko_setDate)
				AND (STL.SurveyTypeLink_endDate IS NULL OR STL.SurveyTypeLink_endDate > :EvnPLDispScreenOnko_setDate)
				AND COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				AND COALESCE(STL.SurveyTypeLink_IsEarlier, 1) = 1
				AND (STL.SurveyTypeLink_Period IS NULL OR STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = :Age % STL.SurveyTypeLink_Period)
		";
		
		$query = "
			WITH consents AS (
				{$select}
				GROUP BY STL.SurveyType_id, STL.SurveyTypeLink_IsDel
			)
			SELECT
				usluga.EvnUsluga_id AS \"EvnUsluga_id\", 
				eup.EvnUslugaPar_id AS \"EvnUslugaPar_id\",
				usluga.UslugaComplex_id AS \"CompletedUslugaComplex_id\", 
				CASE 
					WHEN STLL.SurveyType_id=2 
					THEN TO_CHAR(PJA.PersonOnkoProfile_DtBeg, 'dd.mm.yyyy')
					ELSE usluga.EvnUsluga_Date
				END AS \"EvnUsluga_Date\",
				CASE 
					WHEN STLL.SurveyType_id=2 
					THEN MSF.Person_Fio
					ELSE usluga.MedPersonalFIO
				END AS \"MedPersonalFIO\",
				STLL.SurveyTypeLink_Period AS \"SurveyTypeLink_Period\",
				STLL.SurveyType_id AS \"SurveyType_id\",
				TO_CHAR(PJA.PersonOnkoProfile_DtBeg, 'dd.mm.yyyy') AS \"onkoAnketaDate\",
				PJA.PersonOnkoProfile_id AS \"PersonOnkoProfile_id\",
				consents.DopDispInfoConsent_id AS \"DopDispInfoConsent_id\",
				consents.EvnPLDispScreenOnko_id AS \"EvnPLDispScreenOnko_id\",
				consents.SurveyTypeLink_id AS \"SurveyTypeLink_id\",
				consents.SurveyTypeLink_IsNeedUsluga AS \"SurveyTypeLink_IsNeedUsluga\",
				consents.SurveyTypeLink_IsDel AS \"SurveyTypeLink_IsDel\",
				consents.SurveyType_Code AS \"SurveyType_Code\",
				consents.SurveyType_IsVizit AS \"SurveyType_IsVizit\",
				consents.SurveyType_Name AS \"SurveyType_Name\",
				consents.DopDispInfoConsent_IsAgree AS \"DopDispInfoConsent_IsAgree\",
				consents.DopDispInfoConsent_IsEarlier AS \"DopDispInfoConsent_IsEarlier\",
				consents.DopDispInfoConsent_IsImpossible AS \"DopDispInfoConsent_IsImpossible\",
				consents.SurveyTypeLink_IsUslPack AS \"SurveyTypeLink_IsUslPack\",
				consents.DopDispInfoConsent_IsAgeCorrect AS \"DopDispInfoConsent_IsAgeCorrect\",
				consents.sortOrder AS \"sortOrder\",
				STLL.UslugaComplex_id AS \"UslugaComplex_id\"
			FROM 
				consents
				LEFT JOIN v_SurveyTypeLink STLL ON STLL.SurveyTypeLink_id = consents.SurveyTypeLink_id
				LEFT JOIN LATERAL (
					SELECT  EU.EvnUsluga_id
						,EU.UslugaComplex_id
						,MP.Person_Fio AS MedPersonalFIO
						,TO_CHAR(EU.EvnUsluga_setDate, 'dd.mm.yyyy') AS EvnUsluga_Date
					FROM 
						v_SurveyTypeLink STL2
						JOIN v_SurveyType ST ON ST.SurveyType_id = STL2.SurveyType_id
						JOIN v_EvnUsluga EU ON EU.UslugaComplex_id = STL2.UslugaComplex_id
						JOIN v_MedPersonal MP ON MP.MedPersonal_id = EU.MedPersonal_id
					WHERE 
						STL2.SurveyType_id = STLL.SurveyType_id
						AND EU.Person_id = :Person_id
						AND EU.EvnUsluga_setDate IS NOT NULL
						AND coalesce(DATEADD('year', STLL.SurveyTypeLink_Period, EU.EvnUsluga_setDate), dbo.tzGetDate()) >= dbo.tzGetDate()
						AND (ST.SurveyType_IsVizit = 1 OR EU.EvnUsluga_pid = :EvnPLDispScreenOnko_id)
					ORDER BY EU.EvnUsluga_setDate DESC
					LIMIT 1
				) usluga ON TRUE
				LEFT JOIN LATERAL (
					SELECT  eup.EvnUslugaPar_id
					FROM    v_EvnUslugaPar eup
					WHERE   eup.Person_id = :Person_id AND eup.UslugaComplex_id = usluga.UslugaComplex_id
					ORDER BY CASE WHEN eup.EvnUslugaPar_pid = :EvnPLDispScreenOnko_id THEN 0 ELSE 1 END ASC, 
						 eup.EvnUslugaPar_setDate DESC
					LIMIT 1
				) eup ON TRUE
				LEFT JOIN onko.v_ProfileJurnalAct PJA ON PJA.Person_id=:Person_id AND STLL.SurveyType_id = 2
				LEFT JOIN v_MedStaffFact MSF ON MSF.MedStaffFact_id = PJA.MedStaffFact_id
			ORDER BY consents.sortOrder, consents.SurveyType_Code		
		";
		
		$result = $this->db->query("
			SELECT
				COALESCE(Sex_id, 3) AS \"Sex_id\",
				dbo.Age(Person_BirthDay, dbo.tzGetDate() ) AS \"Age\"
			FROM v_PersonState ps
			WHERE ps.Person_id = :Person_id", 
			$params);
		
		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 ) {
				$params['Sex_id'] = $response[0]['Sex_id'];
				$params['Age'] = $response[0]['Age'];
				if ( $params['Age'] > 99 ) $params['Age'] = 99;
			}
		}

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
				SELECT
					DopDispInfoConsent_id AS \"DopDispInfoConsent_id\",
					Error_Code AS \"Error_Code\",
					Error_Message AS \"Error_Msg\"
				FROM {$proc}(
					DopDispInfoConsent_id := :DopDispInfoConsent_id,
					EvnPLDisp_id := :EvnPLDispScreenOnko_id, 
					DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
					DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
					DopDispInfoConsent_IsImpossible := :DopDispInfoConsent_IsImpossible,
					SurveyTypeLink_id := :SurveyTypeLink_id,
					pmUser_id := :pmUser_id
				)
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
				$response = $result->result('array');
				if ( is_array($response) && count($response) > 0 ) {
					if ( !empty($response[0]['Error_Msg']) ) {
						//~ $this->db->trans_rollback();
						return array(
								'success' => false,
								'Error_Msg' => $response[0]['Error_Msg']
							);
					} else $params['DopDispInfoConsent_id'] = $response[0]['DopDispInfoConsent_id'];
				}
			}
			
			//~ var_dump($result);exit();
			
			if($item['DopDispInfoConsent_IsEarlier']==2 && !empty($item['UslugaComplex_id'])) {
				// если указано "пройдено ранее"
				// ищем услугу в EvnUslugaDispDop, если нет то создаём новую, иначе обновляем.
				$query = "
					SELECT
						EUDD.EvnUslugaDispDop_id AS \"EvnUslugaDispDop_id\"
					FROM v_EvnUslugaDispDop EUDD
					WHERE EUDD.DopDispInfoConsent_id = :DopDispInfoConsent_id
					LIMIT 1
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

				$params['PayType_id'] = $this->getFirstResultFromQuery("
					SELECT
						PayType_id AS \"PayType_id\"
					FROM v_PayType
					WHERE PayType_SysNick = 'dopdisp'
					LIMIT 1
				");

				$query = "
					SELECT
						EvnUslugaDispDop_id AS \"EvnUslugaDispDop_id\",
						Error_Code \"Error_Code\",
						Error_Message AS \"Error_Msg\"
					FROM {$proc}(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						EvnUslugaDispDop_pid := :EvnPLDispScreenOnko_id,
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						EvnDirection_id := NULL,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := :PayType_id,
						UslugaPlace_id := 1,
						UslugaComplex_id := :UslugaComplex_id,
						EvnUslugaDispDop_setDT := :EvnPLDispScreenOnko_setDate,
						ExaminationPlace_id := NULL,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						EvnUslugaDispDop_ExamPlace := NULL,
						EvnPrescrTimetable_id := NULL,
						EvnPrescr_id := NULL,
						pmUser_id := :pmUser_id
					)
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
				EvnPLDispScreenOnko_pid as \"EvnPLDispScreenOnko_pid\",
				Person_id as \"Person_id\",
				Diag_spid as \"Diag_spid\"
			from v_EvnPLDispScreenOnko
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
				coalesce(to_char(EvnPLDispScreenOnko_setDate, 'dd.mm.yyyy'), '') as \"EvnPLDispScreenOnko_setDate\",
				EvnPLDispScreenOnko_id as \"EvnPLDispScreenOnko_id\",
				EvnPLDispScreenOnko_IsSuspectZNO as \"EvnPLDispScreenOnko_IsSuspectZNO\",
				Diag_spid as \"Diag_spid\"
 			from
 			    v_EvnPLDispScreenOnko
			{$where}
		", $params);
	}

    function deleteEvnPLDispScreenOnko($data) {

        $sql = "
		    select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from p_EvnPLDispScreenOnko_del
			(
				EvnPLDispScreenOnko_id := :EvnPLDispScreenOnko_id,
				pmUser_id := :pmUser_id
			)
		";
        $params = [
            "EvnPLDispScreenOnko_id" => $data["EvnPLDispScreenOnko_id"],
            "pmUser_id" => $data["pmUser_id"]
        ];
        $res = $this->db->query(
        //echo getDebugSQL()
            $sql,
            $params
        );

        if ( is_object($res) ) {
            $result = $res->result('array');
            if ( isset($result[0]['Error_Msg']) ) {
                return [
                    'Error_Msg' => $result[0]['Error_Msg']
                ];
            }
        }

        return [
            'Error_Msg' => ''
        ];
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
            $join = "inner join v_PathologyType pt on pt.PathologyType_id = CAST(FI.FormalizedInspection_Result as bigint)";
            $select = "pt.PathologyType_WRisk as \"PathologyType_WRisk\",";
        }
		$query = "
			SELECT
			    {$select}
				ST.SurveyType_id AS \"SurveyType_id\", 
				ST.SurveyType_Name AS \"SurveyType_Name\",
				STL.UslugaComplex_id AS \"UslugaComplex_id\",
				FIP.FormalizedInspectionParams_id AS \"FormalizedInspectionParams_id\", 
				FIP.FormalizedInspectionParams_Name AS \"FormalizedInspectionParams_Name\", 
				FIP.FormalizedInspectionParams_Directory AS \"FormalizedInspectionParams_Directory\",
				FI.FormalizedInspection_Result AS \"FormalizedInspection_Result\",
				FI.FormalizedInspection_DirectoryAnswer_id \"FormalizedInspection_DirectoryAnswer_id\",
				FI.FormalizedInspection_NResult AS \"FormalizedInspection_NResult\",
				DDIC.DopDispInfoConsent_id AS \"DopDispInfoConsent_id\",
				COALESCE(FI.EvnUslugaDispDop_setDT, '') AS \"EvnUslugaDispDop_setDT\",
				COALESCE(FI.MedPersonal_Fin, '') AS \"MedPersonal_Fin\"
			FROM v_FormalizedInspectionParams FIP
				INNER JOIN v_SurveyType ST ON ST.SurveyType_id = FIP.SurveyType_id
				INNER JOIN v_SurveyTypeLink STL ON STL.SurveyType_id = ST.SurveyType_id
				INNER JOIN v_DopDispInfoConsent DDIC ON DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					AND DDIC.EvnPLDisp_id = :EvnPLDispScreenOnko_id
				LEFT JOIN LATERAL (
					SELECT 
						FI.FormalizedInspection_Result,
						FI.FormalizedInspection_DirectoryAnswer_id,
						FI.FormalizedInspection_NResult,
						COALESCE(TO_CHAR(EUDD.EvnUslugaDispDop_setDT, 'dd.mm.yyyy'), '') AS EvnUslugaDispDop_setDT,
						UPPER(SUBSTRING(MSF.Person_SurName FROM 1 FOR 1)) || SUBSTRING(LOWER(MSF.Person_SurName) FROM 2 FOR LENGTH(MSF.Person_SurName))
							|| ' ' || COALESCE(SUBSTRING(MSF.Person_FirName FROM 1 FOR 1) || '.', '')
							|| ' ' || COALESCE(SUBSTRING(MSF.Person_SecName FROM 1 FOR 1) || '.', '') AS MedPersonal_Fin
					FROM v_FormalizedInspection FI
					INNER JOIN v_EvnUslugaDispDop EUDD ON EUDD.EvnUslugaDispDop_id = FI.EvnUslugaDispDop_id
					INNER JOIN v_MedStaffFact MSF ON MSF.MedStaffFact_id = EUDD.MedStaffFact_id
					WHERE 
						FI.FormalizedInspectionParams_id = FIP.FormalizedInspectionParams_id AND 
						EvnUslugaDispDop_pid = :EvnPLDispScreenOnko_id
				) FI ON TRUE
				{$join}
			WHERE
                ST.SurveyType_IsVizit = 2
            AND
                (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
                {$filter}
			ORDER BY ST.SurveyType_id, FIP.FormalizedInspectionParams_id
		";
		
		$result = $this->queryResult($query, [
			'EvnPLDispScreenOnko_id' => $data['EvnPLDispScreenOnko_id']
		]);

        if (!empty($data['checkRisk'])) {
            $WRisk = 0;
            foreach ($result as $item) {
                $WRisk += $item['PathologyType_WRisk'];
            }

            return ['WRisk'=>$WRisk];
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
			
			$row['PathologyType'] = $this->queryResult("
				SELECT
					PathologyType_id AS \"PathologyType_id\",
					PathologyType_Name AS \"PathologyType_Name\",
					PathologyType_IsDefault AS \"PathologyType_IsDefault\"
				FROM v_PathologyType
				WHERE FormalizedInspectionParams_id = :FormalizedInspectionParams_id
			", ['FormalizedInspectionParams_id' => $row['FormalizedInspectionParams_id']]);
			$row['TopographyType'] = $this->queryResult("
				SELECT
					TopographyType_id AS \"TopographyType_id\",
					TopographyType_Name AS \"TopographyType_Name\"
				FROM v_TopographyType
				WHERE SurveyType_id = :SurveyType_id
			", ['SurveyType_id' => $row['SurveyType_id']]);
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
			SELECT
				EvnUslugaDispDop_id AS \"EvnUslugaDispDop_id\"
			FROM v_EvnUslugaDispDop
			WHERE 
				EvnUslugaDispDop_pid = :EvnPLDispScreenOnko_id AND 
				UslugaComplex_id = :UslugaComplex_id
			LIMIT 1
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

			$params['PayType_id'] = $this->getFirstResultFromQuery("
				SELECT
					PayType_id AS \"PayType_id\"
				FROM v_PayType
				WHERE PayType_SysNick = 'dopdisp'
				LIMIT 1
			");

			$query = "
				SELECT	EvnUslugaDispDop_id AS \"EvnUslugaDispDop_id\",
					Error_Code AS \"Error_Code\",
					Error_Message AS \"Error_Msg\"
				FROM {$proc}(
					EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
					EvnUslugaDispDop_pid := :EvnPLDispScreenOnko_id,
					SurveyType_id := :SurveyType_id,
					UslugaComplex_id := :UslugaComplex_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					PayType_id := :PayType_id,
					UslugaPlace_id := 1,
					EvnUslugaDispDop_setDT := dbo.tzGetDate(),
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					pmUser_id := :pmUser_id
				)
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
				SELECT
					FormalizedInspection_id AS \"FormalizedInspection_id\"
				FROM FormalizedInspection
				WHERE 
					EvnUslugaDispDop_id = :EvnUslugaDispDop_id AND 
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
				SELECT
					FormalizedInspection_id AS \"FormalizedInspection_id\",
					Error_Code AS \"Error_Code\",
					Error_Message AS \"Error_Msg\"
				FROM p_FormalizedInspection_{$proc} (
					FormalizedInspection_id := :FormalizedInspection_id,
					EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
					FormalizedInspectionParams_id := :FormalizedInspectionParams_id,
					FormalizedInspection_Result := :FormalizedInspection_Result,
					FormalizedInspection_DirectoryAnswer_id := :FormalizedInspection_DirectoryAnswer_id,
					FormalizedInspection_NResult := :FormalizedInspection_NResult,
					pmUser_id := :pmUser_id
				)
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
		
		$this->db->query("
			update EvnPLDisp
			set
				EvnPLDisp_IsSuspectZNO = :EvnPLDisp_IsSuspectZNO,
				Diag_spid = :Diag_spid
			WHERE Evn_id = :EvnPLDisp_id
		", [
			'EvnPLDisp_IsSuspectZNO' => $data['EvnPLDispScreenOnko_IsSuspectZNO'],
			'Diag_spid' => $data['Diag_spid'],
			'EvnPLDisp_id' => $data['EvnPLDispScreenOnko_id']
		]);
		
		return array('success' => true, 'Error_Msg' => '');
	}

	/**
	 * Получить список полей раздела Протокол осмотра
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
			SELECT
				user_ls.Lpu_id AS \"Lpu_id\",
				user_lu.LpuBuilding_id AS \"LpuBuilding_id\",
				user_ls.LpuUnit_id As \"LpuUnit_id\"
			FROM v_LpuSection user_ls
			INNER JOIN v_LpuUnit user_lu ON user_lu.LpuUnit_id = user_ls.LpuUnit_id
			WHERE user_ls.LpuSection_id = :LpuSection_id
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
			WITH EvnPrescr AS (
				SELECT 
					COALESCE(EPLD.Evn_id,EPFDU.EvnPrescrFuncDiag_id) AS EvnPrescr_id,
					COALESCE(EPLD.UslugaComplex_id, EPFDU.UslugaComplex_id) AS UslugaComplex_id,
					pt.PrescriptionType_Code,
					COALESCE(EP.EvnPrescr_IsExec,1) AS EvnPrescr_IsExec
				FROM v_EvnPrescr EP
					LEFT JOIN EvnPrescrLabDiag EPLD
						ON EPLD.Evn_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 11
					LEFT JOIN EvnPrescrFuncDiagUsluga EPFDU
						ON EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 12
					LEFT JOIN v_PrescriptionType pt
						ON pt.PrescriptionType_id = EP.PrescriptionType_id
				WHERE EP.EvnPrescr_pid = :EvnPrescr_pid 
				--'730023881307390'
					  AND EP.PrescriptionType_id IN ( 11, 12 )
					  AND EP.PrescriptionStatusType_id != 3
			)
			
			SELECT 
				uc.UslugaComplex_id AS \"UslugaComplex_id\",
				uc.UslugaComplex_Code AS \"UslugaComplex_Code\",
				uc.UslugaComplex_Name \"UslugaComplex_Name\",
				EP.EvnPrescr_id AS \"EvnPrescr_id\",
				EP.EvnPrescr_IsExec AS \"EvnPrescr_IsExec\",
				EvnStatus.EvnStatus_SysNick AS \"EvnStatus_SysNick\",
				COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) AS \"PrescriptionType_Code\",
				CASE	
					WHEN COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 11 THEN 'EvnPrescrLabDiag'
					WHEN COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 12 THEN 'EvnPrescrFuncDiag'
					ELSE ''
				END AS \"object\",
				CASE
						WHEN TTMS.TimetableMedService_id IS NOT NULL THEN COALESCE(TO_CHAR(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy hh24:mi'), '')
						WHEN TTR.TimetableResource_id IS NOT NULL THEN COALESCE(TO_CHAR(TTR.TimetableResource_begTime, 'dd.mm.yyyy hh24:mi'), '')
						WHEN EQ.EvnQueue_id IS NOT NULL THEN 'В очереди с ' || COALESCE(TO_CHAR(EQ.EvnQueue_setDate, 'dd.mm.yyyy'), '')
					ELSE '' END AS \"RecDate\",
				CASE
					WHEN TTMS.TimetableMedService_id IS NOT NULL THEN COALESCE(MS.MedService_Name,'')
					WHEN TTR.TimetableResource_id IS NOT NULL THEN COALESCE(R.Resource_Name,'') || ' / ' || COALESCE(MS.MedService_Name,'')
					WHEN EQ.EvnQueue_id IS NOT NULL THEN
						CASE
							WHEN MS.MedService_id IS NOT NULL AND  MS.LpuSection_id IS NULL AND MS.LpuUnit_id IS NULL
							THEN COALESCE(MS.MedService_Name,'')
							WHEN MS.MedService_id IS NOT NULL AND  MS.LpuSection_id IS NULL AND MS.LpuUnit_id IS NOT NULL
							THEN COALESCE(MS.MedService_Name,'') || ' / ' || COALESCE(LU.LpuUnit_Name,'')
							WHEN MS.MedService_id IS NOT NULL AND  MS.LpuSection_id IS NOT NULL AND MS.LpuUnit_id IS NOT NULL
							THEN COALESCE(MS.MedService_Name,'') || ' / ' || COALESCE(LSPD.LpuSectionProfile_Name,'') || ' / ' || COALESCE(LU.LpuUnit_Name,'')
							ELSE COALESCE(LSPD.LpuSectionProfile_Name,'') || ' / ' || COALESCE(LU.LpuUnit_Name,'')
						END
				ELSE '' END AS \"RecTo\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnQueue_id as \"EvnQueue_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				ED.MedService_id as \"MedService_id\",
				ED.Resource_id as \"Resource_id\",
				ED.LpuSection_did as \"LpuSection_did\",
				ED.LpuUnit_did as \"LpuUnit_did\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ED.EvnStatus_id as \"EvnStatus_id\",
				MS.MedService_Nick AS \"MedService_Nick\",
				EUP.EvnUslugaPar_id AS \"EvnUslugaPar_id\"
			FROM v_UslugaComplex uc
				LEFT JOIN LATERAL (
					SELECT *
					FROM v_UslugaComplex uc11
					WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
					LIMIT 1
				) uc11 ON TRUE
				LEFT JOIN LATERAL (
					SELECT *
					FROM EvnPrescr ep
					WHERE ep.UslugaComplex_id = uc.UslugaComplex_id
				) EP ON TRUE
				LEFT JOIN LATERAL (
					SELECT
						 ED.EvnDirection_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.MedService_id
						,ED.Resource_id
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.LpuSectionProfile_id
						,ED.EvnStatus_id
					FROM v_EvnPrescrDirection epd
					INNER JOIN v_EvnDirection_all ED ON epd.EvnDirection_id = ED.EvnDirection_id
					WHERE EP.EvnPrescr_id IS NOT NULL 
						AND epd.EvnPrescr_id = EP.EvnPrescr_id
					ORDER BY 
						CASE WHEN COALESCE(ED.EvnStatus_id, 16) in (12,13) THEN 2 ELSE 1 END /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT DESC
					LIMIT 1
				) ED ON TRUE
				-- заказанная услуга для параклиники @TODO костыль!!!
				LEFT JOIN LATERAL (
					SELECT EvnUslugaPar_id FROM v_EvnUslugaPar WHERE EvnDirection_id = ED.EvnDirection_id LIMIT 1
				) EUP ON TRUE
				--LEFT JOIN v_EvnUslugaPar EUP ON EUP.EvnDirection_id = ED.EvnDirection_id
				-- службы и параклиника
				LEFT JOIN LATERAL (
					SELECT TimetableMedService_id, TimetableMedService_begTime FROM v_TimetableMedService_lite TTMS WHERE TTMS.EvnDirection_id = ED.EvnDirection_id LIMIT 1
				) TTMS ON TRUE
				-- очередь
				LEFT JOIN LATERAL (
					(
					SELECT EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					FROM v_EvnQueue EQ
					WHERE EQ.EvnDirection_id = ED.EvnDirection_id
					AND EQ.EvnQueue_recDT IS NULL
					LIMIT 1
					)
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					UNION
					(
					SELECT EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					FROM v_EvnQueue EQ
					WHERE (EQ.EvnQueue_id = ED.EvnQueue_id)
					AND (EQ.EvnQueue_recDT IS NULL OR TTMS.TimetableMedService_id IS NULL)
					AND EQ.EvnQueue_failDT IS NULL
					LIMIT 1
					)
				) EQ ON TRUE
				-- ресурсы
				LEFT JOIN LATERAL (
						SELECT TimetableResource_id, TimetableResource_begTime FROM v_TimetableResource_lite TTR WHERE TTR.EvnDirection_id = ED.EvnDirection_id LIMIT 1
				) TTR ON TRUE
				-- сам ресрс
				LEFT JOIN v_Resource R ON R.Resource_id = ED.Resource_id
				LEFT JOIN v_MedService MS ON MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				LEFT JOIN v_LpuSection LS ON LS.LpuSection_id = COALESCE(ED.LpuSection_did, MS.LpuSection_id)
				-- подразделение для очереди и служб
				LEFT JOIN v_LpuUnit LU ON COALESCE(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				LEFT JOIN v_LpuSectionProfile LSPD ON COALESCE(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				LEFT JOIN LATERAL (
						SELECT ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
						FROM EvnStatusHistory ESH
						WHERE ESH.Evn_id = ED.EvnDirection_id
							AND ESH.EvnStatus_id = ED.EvnStatus_id
						ORDER BY ESH.EvnStatusHistory_begDate DESC
						LIMIT 1
					) ESH ON TRUE
				LEFT JOIN EvnStatus ON EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				LEFT JOIN LATERAL (
					SELECT
					CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'lab' THEN 11
					ELSE CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'func' THEN 12 END
					END AS PrescriptionType_Code
						   
					FROM v_UslugaComplexAttribute t1
						INNER JOIN v_UslugaComplexAttributeType t2
							ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					WHERE t1.UslugaComplex_id = COALESCE(uc11.UslugaComplex_id, uc.UslugaComplex_id)
						  AND t2.UslugaComplexAttributeType_SysNick IN ( 'lab','func' )
					LIMIT 1
				) AS attr ON TRUE
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
            if (count($listPostgres) > 0) {
                foreach ($listPostgres as $key => $value) {
                    if (!empty($value['EvnPrescr_id']) && !empty($value['UslugaComplex_id'])) {
                        $result = array_keys(array_column($UslugaList, 'EvnPrescr_id'), $value['EvnPrescr_id']);
                        if (count($result) > 0 && $UslugaList[$result[0]]['UslugaComplex_id'] == $value['UslugaComplex_id']) {
                            $UslugaList[$result[0]] = array_merge($UslugaList[$result[0]], $value);
                        }
                    }
                }
            }
        }
        
		$FuncUslList = $LabUslList = $OtherUslList = array();
		foreach ($UslugaList as $key => $usl) {
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
			SELECT
				EvnPLDispScreenOnko_id AS \"EvnPLDispScreenOnko_id\"
			FROM v_EvnPLDispScreenOnko
			WHERE Person_id = :Person_id AND EvnPLDispScreenOnko_setDate >= dbo.tzGetDate() - INTERVAL '1 YEAR'
			ORDER BY EvnPLDispScreenOnko_setDate DESC
			LIMIT 1
		", $data);
	}
}