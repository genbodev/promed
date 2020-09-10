<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispScreen_model - модель для работы с профосмотрами
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

class EvnPLDispScreen_model extends swModel
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
	function deleteAttributes($attr, $EvnPLDispScreen_id, $pmUser_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnPLDisp_id = :EvnPLDispScreen_id
				";
			break;
		}

		$result = $this->db->query($query, array('EvnPLDispScreen_id' => $EvnPLDispScreen_id));

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
						" . (in_array($attr, array('EvnUslugaDispDop')) ? "@pmUser_id = :pmUser_id," : "") . "
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
	 *	Проверка возможности добавления новой карты
	 */
	function checkAddAvailability($data) {
		// Если по возрасту (на конец года) пациент не подлежит скрининговым исследованиям взрослых.
		$resp = $this->queryResult("
			select
				ps.Person_id
			from
				v_PersonState ps (nolock)
			where
				ps.Person_id = :Person_id
				and (dbo.Age2(ps.Person_BirthDay, cast(:Year as varchar) + '-12-31') IN (30,35,40,42,44,45,46,48,50,52,54,55,56,58,60,62,64,66,68,70))
		", array(
			'Year' => date('Y'),
			'Person_id' => $data['Person_id']
		));

		if (empty($resp[0]['Person_id'])) {
			return array('Error_Msg' => 'Пациент не подлежит проведению скринингового исследования');
		}

		// Если для расчетной возрастной группы (для взрослых на конец текущего года) на пациента уже создана карта скрининговых исследований
		$resp = $this->queryResult("
			select
				eplds.EvnPLDispScreen_id
			from
				v_PersonState ps (nolock)
				inner join v_AgeGroupDisp agd (nolock) on agd.DispType_id = 5 and agd.AgeGroupDisp_From <= dbo.Age2(ps.Person_BirthDay, cast(:Year as varchar) + '-12-31')  and agd.AgeGroupDisp_To >= dbo.Age2(ps.Person_BirthDay, cast(:Year as varchar) + '-12-31')
				inner join v_EvnPLDispScreen eplds (nolock) on eplds.AgeGroupDisp_id = agd.AgeGroupDisp_id and eplds.Person_id = ps.Person_id
			where
				ps.Person_id = :Person_id
		", array(
			'Year' => date('Y'),
			'Person_id' => $data['Person_id']
		));

		if (!empty($resp[0]['EvnPLDispScreen_id'])) {
			return array('Error_Msg' => 'На пациента уже создана карта скрининговых исследований');
		}

		return array('Error_Msg' => '');
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
	function getEvnPLDispScreenData($data)
	{
		$query = "
			SELECT TOP 1
				EvnPLDispScreen_id,
				PersonEvn_id,
				Server_id
			FROM
				v_EvnPLDispScreen EPLDD (nolock)
			WHERE
				EPLDD.EvnPLDispScreen_id = :EvnPLDisp_id
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
		$dd = $this->getEvnPLDispScreenData($data);

		if ( empty($dd) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка получения данных карты диспансеризации');
		}

		$data['PersonEvn_id'] = $dd['PersonEvn_id'];
		$data['Server_id'] = $dd['Server_id'];
		
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

		$data['UslugaComplex_id'] = $resp[0]['UslugaComplex_id'];

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
				@LpuSection_uid = NULL,
				@MedPersonal_id = NULL,
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

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => ''
		);
	}
	
	/**
	 *	Удаление карты
	 */	
	function deleteEvnPLDispScreen($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispScreen_del
				@EvnPLDispScreen_id = :EvnPLDispScreen_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)');
		}
		
		$attrArray = array(
		);
		foreach ( $attrArray as $attr ) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispScreen_id'], $data['pmUser_id']);

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
	 *	Получение данных для формы просмотра карты
	 */	
	function loadEvnPLDispScreenEditForm($data)
	{
		$accessType = '1=1';

		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLDS.EvnPLDispScreen_id,
				epldsl.ScreenType_id,
				elapp.ScreenEndCause_id,
				EPLDS.Person_id,
				EPLDS.PersonEvn_id,
				EPLDS.Server_id,
				ISNULL(EPLDS.DispClass_id, 13) as DispClass_id,
				EPLDS.EvnPLDispScreen_PersonWaist,
				EPLDS.EvnPLDispScreen_QueteletIndex,
				EPLDS.EvnPLDispScreen_ArteriaSistolPress,
				EPLDS.EvnPLDispScreen_ArteriaDiastolPress,
				EPLDS.HealthKind_id,
				EPLDS.AgeGroupDisp_id,
				EPLDS.EvnPLDispScreen_IsEndStage,
				PH.PersonHeight_Height,
				PW.PersonWeight_Weight,
				EPLDS.AlcoholIngestType_bid,
				EPLDS.AlcoholIngestType_vid,
				EPLDS.AlcoholIngestType_wid,
				EPLDS.EvnPLDispScreen_IsAlco,
				EPLDS.EvnPLDispScreen_IsBleeding,
				EPLDS.EvnPLDispScreen_IsCoronary,
				EPLDS.EvnPLDispScreen_IsHeadache,
				EPLDS.EvnPLDispScreen_IsHeartache,
				EPLDS.EvnPLDispScreen_IsHighPressure,
				EPLDS.EvnPLDispScreen_IsParCoronary,
				EPLDS.EvnPLDispScreen_IsSmoking,
				EPLDS.WaistCircumference_id,
				EPLDS.EvnPLDispScreen_IsBlurVision,
				EPLDS.EvnPLDispScreen_IsDailyPhysAct,
				EPLDS.EvnPLDispScreen_IsDirectedPMSP,
				EPLDS.EvnPLDispScreen_IsGenPredisposed,
				EPLDS.EvnPLDispScreen_IsGlaucoma,
				EPLDS.EvnPLDispScreen_IsHealthy,
				EPLDS.EvnPLDispScreen_IsHighMyopia,
				EPLDS.EvnPLDispScreen_IsHyperglycaemia,
				EPLDS.EvnPLDispScreen_IsHyperlipidemia,
				EPLDS.EvnPLDispScreen_IsHypertension,
				EPLDS.EvnPLDispScreen_IsLowPhysAct,
				EPLDS.EvnPLDispScreen_IsOverweight,
				EPLDS.EvnPLDispScreen_IsVisImpair,
				EPLDS.FecalCasts_id,
				EPLDS.EvnPLDispScreen_IsAlcoholAbuse,
				EPLDS.EvnPLDispScreen_IsDisability,
				EPLDS.EvnPLDispScreen_DisabilityYear,
				EPLDS.EvnPLDispScreen_DisabilityPeriod,
				EPLDS.Diag_disid,
				convert(varchar(10), EPLDS.EvnPLDispScreen_setDate, 104) as EvnPLDispScreen_setDate,
				EPLDS.Lpu_id
			FROM
				v_EvnPLDispScreen EPLDS (nolock)
				left join v_PersonHeight PH (nolock) on PH.Evn_id = EPLDS.EvnPLDispScreen_id
				left join v_PersonWeight PW (nolock) on PW.Evn_id = EPLDS.EvnPLDispScreen_id
				left join r101.v_EvnPLDispScreenLink epldsl (nolock) on epldsl.EvnPLDispScreen_id = EPLDS.EvnPLDispScreen_id
				left join r101.EvnLinkAPP elapp (nolock) on elapp.Evn_id = EPLDS.EvnPLDispScreen_id
			WHERE
				(1 = 1)
				and EPLDS.EvnPLDispScreen_id = :EvnPLDispScreen_id
		";
		//echo getDebugSQL($query, array( 'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id'])); exit();
        $result = $this->db->query($query, array( 'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']));

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
	 *	Получение полей карты
	 */	
	function getEvnPLDispScreenFields($data)
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
				case when EPLDD.EvnPLDispScreen_IsBud = 2 then 'Да' else 'Нет' end as EvnPLDispScreen_IsBud,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispScreen_disDate, 104) as EvnPLDispScreen_disDate
			FROM
				v_EvnPLDispScreen EPLDD (nolock)
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
				and EPLDD.EvnPLDispScreen_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispScreen_id'], $data['Lpu_id']));

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
	 *	Загрузка формы редактирования услуги
	 */
	function loadEvnUslugaDispDop($data) {
		$query = "
			select
				EUDD.EvnUslugaDispDop_id,
				EUDD.EvnUslugaDispDop_pid,
				EUDD.PersonEvn_id,
				EUDD.Server_id,
				CONVERT(varchar(10), EUDD.EvnUslugaDispDop_setDT, 104) as EvnUslugaDispDop_setDate,
				EUDD.EvnUslugaDispDop_setTime,
				EUDD.UslugaComplex_id,
				EUDD.LpuSection_uid as LpuSection_id,
				EUDD.MedPersonal_id,
				EUDD.Diag_id,
				EUDD.SurveyType_id,
				EUDD.DeseaseType_id,
				eu.Lpu_uid,
				EUDD.Lpu_id,
				EUDD.MedStaffFact_id,
				coalesce(eu.EvnUsluga_IsAPP,1) as EvnUsluga_IsAPP
			from 
				v_EvnUslugaDispDop EUDD (nolock)
				inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_id = eudd.EvnUslugaDispDop_id
			where EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
		";
		$result = $this->db->query($query, array(
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			// нужно получить значения результатов услуги из EvnUslugaRate
			if (isset($resp[0]['EvnUslugaDispDop_id'])) {
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
					from
						v_EvnUslugaRate eur (nolock)
						left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
						left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
						left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
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
							// Убираем последнюю цифру в значении
							// http://redmine.swan.perm.ru/issues/23248
							$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
						}

						$resp[0][$oneresult['nick']] = $oneresult['value'];
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
	 * Входящие данные: $data['EvnPLDispScreen_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$setDate = strtotime($data['EvnPLDispScreen_setDate']);
		$dateX = strtotime('01-05-2018');

		$queryBeforeFirstMay = "
			declare
				@Sex_id bigint = (select top 1 Sex_id from v_PersonState (nolock) where Person_id = :Person_id);

			select
				ST.SurveyType_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				STLINK.SurveyTypeLink_id,
				eudd.EvnUslugaDispDop_id,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				0 as noIndication
			from
				v_SurveyType ST (nolock)
				cross apply(
					select top 1
						SurveyTypeLink_id
					from
						v_SurveyTypeLink STL (nolock)
						inner join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = :AgeGroupDisp_id and STL.SurveyTypeLink_From = AGD.AgeGroupDisp_From and STL.SurveyTypeLink_To = AGD.AgeGroupDisp_To
					where
						STL.DispClass_id = 13
						and ISNULL(STL.Sex_id, @Sex_id) = @Sex_id
						and STL.SurveyType_id = ST.SurveyType_id
				) STLINK
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
		";

		$queryAfterFirstMay = "
			declare
				@Sex_id bigint = (select top 1 Sex_id from v_PersonState (nolock) where Person_id = :Person_id),
				@date datetime = dbo.tzGetDate(),
				@age int = (select top 1 dbo.age(Person_BirthDay, dbo.tzGetDate()) from v_PersonState (nolock) where Person_id = :Person_id);

			select
				ST.SurveyType_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				STLINK.SurveyTypeLink_id,
				eudd.EvnUslugaDispDop_id,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				0 as noIndication,
				stlink.UslugaComplex_id,
				eudd.EvnDirection_id,
				eudd.MedPersonal_id,
				eudd.MedStaffFact_id,
				eudd.LpuSection_uid as LpuSection_id,
				eudd.Diag_id
			from
				v_SurveyType ST (nolock)
				cross apply(
					select top 1
						stl.SurveyTypeLink_id,stl.UslugaComplex_id
					from
						v_SurveyTypeLink STL (nolock)
						inner join r101.SurveyTypeScreenLink stsl (nolock) on stsl.SurveyTypeLink_id = STL.SurveyTypeLink_id					
					where
						STL.DispClass_id = 13
						and ISNULL(STL.Sex_id, @Sex_id) = @Sex_id
						and STL.SurveyType_id = ST.SurveyType_id
						and ISNULL(STL.SurveyTypeLink_endDate, @date) >= @date
						and stsl.ScreenType_id = :ScreenType_id
						and @age BETWEEN coalesce(STL.SurveyTypeLink_From, @age) and coalesce(STL.SurveyTypeLink_To, @age)
				) STLINK
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
		";
		
		$queryWithoutAgeGroups = "
			declare
				@Sex_id bigint,
				@ageOnENDYear bigint,
				@birthDay datetime,
				@date datetime = dbo.tzGetDate(),
				@lastDayOfYear datetime;
				set @lastDayOfYear = DATEADD(yy, DATEDIFF(yy, 0, @date) + 1, -1);
				

			select top 1 @Sex_id = Sex_id, @birthDay = Person_BirthDay from v_PersonState (nolock) where Person_id = :Person_id
			set @ageOnENDYear = datediff(yy , @birthDay, @lastDayOfYear)

			select
				ST.SurveyType_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				STLINK.SurveyTypeLink_id,
				CASE 
					when STLINK.SurveyTypeLink_IsEarlier is null then 0
					else STLINK.SurveyTypeLink_IsEarlier
				end as SurveyTypeLink_IsEarlier,
				eudd.EvnUslugaDispDop_id,
				convert(varchar(10), eudd.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				0 as noIndication
			from
				v_SurveyType ST (nolock)
				cross apply(
					select top 1
						SurveyTypeLink_id,
						SurveyTypeLink_IsEarlier,
						SurveyTypeLink_Period
					from
						v_SurveyTypeLink STL (nolock)
					where
						STL.DispClass_id = 13
						and ISNULL(STL.Sex_id, @Sex_id) = @Sex_id
						and STL.SurveyType_id = ST.SurveyType_id
						and @ageOnENDYear between STL.SurveyTypeLink_From and STL.SurveyTypeLink_To
						and ISNULL(STL.SurveyTypeLink_endDate, @date) >= @date
						and STL.SurveyTypeLink_id not in(			--Исключаем если исследование недавно пройдено в другой карте
							SELECT STLD.SurveyTypeLink_id
							from 
								v_SurveyTypeLink STLD
								inner join v_EvnUslugaDispDop ED on ED.SurveyType_id = STLD.SurveyType_id
								left join v_EvnPLDispScreen EPDS on EPDS.EvnPLDispScreen_id = :EvnPLDispScreen_id
							where
								(:EvnPLDispScreen_id is null OR ED.EvnUslugaDispDop_rid <> :EvnPLDispScreen_id)
								and ED.Person_id = :Person_id
								and ED.EvnUslugaDispDop_setDate is not null
								and DATEDIFF(mm, ED.EvnUslugaDispDop_setDate, isnull(EPDS.EvnPLDispScreen_setDate, @date)) < STLD.SurveyTypeLink_Period
								
						)
				) STLINK
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = ST.SurveyType_id
			";

		if($data['withoutAgeGroups'] == 'true') {
			$query = $queryWithoutAgeGroups;
		} else {
			$query = $setDate >= $dateX ? $queryAfterFirstMay : $queryBeforeFirstMay;
		}
		// echo getDebugSQL($query, array(
		// 	'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
		// 	'Person_id' => $data['Person_id'],
		// 	'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
		// ));die;
		$result = $this->db->query($query, array(
			'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
			'Person_id' => $data['Person_id'],
			'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id'],
			'ScreenType_id' => $data['ScreenType_id']
		));
	
        if (is_object($result))
        {
            $resp = $result->result('array');
            foreach($resp as $key => $value) {
            	switch($value['SurveyType_Code']) {
					case '109': // Кольпоскопия
						$noIndication = 1; // нет показаний
						// Результат исследования Цитологическое исследование шейки матки (Рар-тест) один из: ASC-H, HSIL, AIS, рак.
						if (!empty($data['EvnPLDispScreen_id'])) {
							$resp_check = $this->queryResult("
								select top 1
									eudd.EvnUslugaDispDop_id
								from
									v_EvnUslugaDispDop eudd
									inner join v_SurveyType st (nolock) on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 108 -- Цитологическое исследование шейки матки (Рар-тест)
									inner join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
									inner join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
									inner join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
								where
									eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
									and rt.RateType_SysNick = 'uteri_carvix_scrning'
									and r.Rate_ValueStr IN ('5', '7', '8', '9')
							", array(
								'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
							));

							if (!empty($resp_check[0]['EvnUslugaDispDop_id'])) {
								$noIndication = 0;
							}
						}
						$resp[$key]['noIndication'] = $noIndication;
						break;
					case '162': // Колоноскопия
						$noIndication = 1; // нет показаний
						// Результат исследования «Анализ кала на скрытую кровь (гемокульт-тест)» -положительный
						if (!empty($data['EvnPLDispScreen_id'])) {
							$resp_check = $this->queryResult("
								select top 1
									eudd.EvnUslugaDispDop_id
								from
									v_EvnUslugaDispDop eudd
									inner join v_SurveyType st (nolock) on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 112 -- Анализ кала на скрытую кровь (гемокульт-тест)
									inner join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
									inner join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
									inner join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
								where
									eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
									and rt.RateType_SysNick = 'fec_occult_blood'
									and r.Rate_ValueStr = '1'
							", array(
								'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
							));

							if (!empty($resp_check[0]['EvnUslugaDispDop_id'])) {
								$noIndication = 0;
							}
						}
						$resp[$key]['noIndication'] = $noIndication;
						break;
					case '110': // Биопсия
						$noIndication = 1; // нет показаний
						// Результат исследования Цитологическое исследование шейки матки (Рар-тест) один из: ASC-H, HSIL, AIS, рак.
						if (!empty($data['EvnPLDispScreen_id'])) {
							$resp_check = $this->queryResult("
								select top 1
									eudd.EvnUslugaDispDop_id
								from
									v_EvnUslugaDispDop eudd
									inner join v_SurveyType st (nolock) on st.SurveyType_id = eudd.SurveyType_id and st.SurveyType_Code = 108 -- Цитологическое исследование шейки матки (Рар-тест)
									inner join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
									inner join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
									inner join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
								where
									eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
									and rt.RateType_SysNick = 'uteri_carvix_scrning'
									and r.Rate_ValueStr IN ('5', '7', '8', '9')
							", array(
								'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
							));

							if (!empty($resp_check[0]['EvnUslugaDispDop_id'])) {
								$noIndication = 0;
							}
						}
						$resp[$key]['noIndication'] = $noIndication;
						break;
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
	 *	Список карт для поточного ввода
	 */	
	function loadEvnPLDispScreenStreamList($data)
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
	 *	Получение минимальной, максимальной дат
	 */
	function getEvnUslugaDispDopMinMaxDates($data)
	{

		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			select
				convert(varchar(10),ISNULL(MIN(eudd.EvnUslugaDispDop_setDate), @getdate),120) as mindate,
				convert(varchar(10),ISNULL(MAX(eudd.EvnUslugaDispDop_setDate), @getdate),120) as maxdate
			from
				v_EvnUslugaDispDop eudd (nolock)
			where
				eudd.EvnUslugaDispDop_pid = :EvnPLDispScreen_id
		";

		$result = $this->db->query($query, array(
			'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id'])
		);

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
	 *	Получение результатов
	 */
	function getRateData($RateType_SysNick, $EvnUsluga_id) {
		$query = "
			select
				rt.RateType_id,
				rvt.RateValueType_SysNick,
				EURData.EvnUslugaRate_id,
				EURData.Rate_id
			from
				v_RateType rt (nolock)
				left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
				outer apply(
					select top 1
						eur.EvnUslugaRate_id, r.Rate_id
					from
						v_EvnUslugaRate eur (nolock)
						left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
					where r.RateType_id = rt.RateType_id and eur.EvnUsluga_id = :EvnUsluga_id
				) EURData
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
	 *  Удаление посещения/осмотра/исследования по доп. диспансеризации
	 */
	function deleteEvnUslugaDispDop($data) {
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
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id'],
			'pmUser_id' => $data['pmUser_id']
		))->result('array');

		return array(array('Error_Code' => $result[0]['Error_Code'], 'Error_Msg' => $result[0]['Error_Msg']));
	}

	/**
	 *	Сохранение посещения/осмотра/исследования по доп. диспансеризации
	 */
	function saveEvnUslugaDispDop($data) {
		$this->db->trans_begin();

		$SurveyType_Code = $this->getFirstResultFromQuery("select SurveyType_Code from v_SurveyType (nolock) where SurveyType_id = :SurveyType_id", $data);

		//Проверка на наличие у врача кода ДЛО и специальности https://redmine.swan.perm.ru/issues/47172
		/*if (($data['session']['region']['nick'] == 'kareliya')&&isset($data['MedPersonal_id'])){
			$queryCheckMedPersonal = "
				select
					ISNULL(MSF.MedPersonal_Code,'') as MedPersonal_DloCode,
					ISNULL(MSF.MedSpecOms_id,'') as MedSpecOms_id,
					ISNULL(MSF.Person_Snils,'') as Person_Snils
				from v_MedStaffFact MSF with(nolock)
				where MSF.MedPersonal_id = :MedPersonal_id
				and MSF.LpuSection_id = :LpuSection_id
			";
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
		}*/

		if ( !empty($data['EvnUslugaDispDop_id']) ) {
			$proc = "p_EvnUslugaDispDop_upd";
		}
		else {
			$data['EvnUslugaDispDop_id'] = null;
			$proc = "p_EvnUslugaDispDop_ins";
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
			$data['EvnUslugaDispDop_disDate'] .= ' ' . $data['EvnUslugaDispDop_disTime'] . ':00:000';
		}
		if ( empty($data['EvnUslugaDispDop_disDate']) ) {
			$data['EvnUslugaDispDop_disDate'] = $data['EvnUslugaDispDop_setDate'];
		}

		$sql = "
			declare
				@EvnUslugaDispDop_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnUslugaDispDop_id = :EvnUslugaDispDop_id;
			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'transf');
			exec {$proc}
				@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
				@EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid,
				@SurveyType_id = :SurveyType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@EvnDirection_id = :EvnDirection_id,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = @PayType_id,
				@EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDate,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaDispDop_didDT = :EvnUslugaDispDop_didDate,
				@EvnUslugaDispDop_disDT = :EvnUslugaDispDop_disDate,
				@Lpu_uid = :Lpu_uid,
				@Diag_id = :Diag_id,
				@DeseaseType_id = :DeseaseType_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@MedSpecOms_id = :MedSpecOms_id,
				@ExaminationPlace_id = :ExaminationPlace_id,
				@LpuSection_uid = :LpuSection_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnUslugaDispDop_ExamPlace = :EvnUslugaDispDop_ExamPlace,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

		$inresults = array();

		// учтены изменения после 1 мая 2018. Пустые значения пропускаются
		// blood_sugar_lvl intraocular_tens el_cardiography mammography_scrning mammography_scrning_conducted blood_cholest_lvl uteri_carvix_scrning colposcopy_res biopsy_res fec_occult_blood fec_occult_blood_conducted coloscopy_res
		//Скорее всего этот блок никому не нужен, но черт его знает
		switch ( $SurveyType_Code ) {

			case 6:
				$inresults = array('blood_sugar_lvl');
				break;
			case 8:
				$inresults = array('pressure_measure','eye_pressure_left','eye_pressure_right', 'intraocular_tens');
				break;
			case 19:
				$inresults = array('terapevt_vop');
				break;
			case 17:
				$inresults = array('el_cardiography');
				break;
			case 21:
				$inresults = array('mammography_scrning', 'mammography_scrning_conducted');
				break;
			case 103:
				$inresults = array('terapevt_vop');
				break;

			case 104:
				$inresults = array('accoucheur_gynecologist');
				break;

			case 105:
				$inresults = array('electro_cardio_gramm');
				break;

			case 106:
				$inresults = array('blood_cholest_lvl', 'total_cholesterol');
				break;

			case 107:
				$inresults = array('bio_blood_triglycerid', 'glucose');
				break;

			case 108:
				$inresults = array('uteri_carvix_scrning', 'pap_test');
				break;

			case 109:
				$inresults = array('colposcopy_res', 'colposcopy');
				break;

			case 110:
				$inresults = array('biopsy_res', 'biopsy');
				break;

			case 111:
				$inresults = array('pressure_measure','eye_pressure_left','eye_pressure_right');
				break;

			case 112:
				$inresults = array('fec_occult_blood', 'fec_occult_blood_conducted', 'gemokult_test');
				break;

			case 113:
				$inresults = array('rectoromanoscopy');
				break;

			case 114:
				$inresults = array('res_mammo_graph');
				break;

			case 115:
				$inresults = array('in_prostate_cancer');
				break;

			case 116:
				$inresults = array('cancer_stomach');
				break;
			case 162:
				$inresults = array('coloscopy_res');
				break;
		}

		//Скорее всего этот блок никому не нужен, но черт его знает
		foreach ( $inresults as $inresult ) {
			if ( empty($data[$inresult]) ) {
				continue;
				//$data[$inresult] = NULL;
			}

			// получаем идентификатор EvnUslugaRate и тип сохраняемых данных
			$inresultdata = $this->getRateData($inresult, $EvnUsluga_id);

			if ( !empty($inresultdata['RateType_id']) ) {
				// если такого результата в бд ещё нет, то добавляем
				if ( empty($inresultdata['EvnUslugaRate_id']) ) {
					// сначала p_Rate_ins
					$sql = "
						declare
							@Rate_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Rate_id = :Rate_id;
						exec p_Rate_ins
							@Rate_id = @Rate_id output,
							@RateType_id = :RateType_id,
							@Rate_ValueInt = :Rate_ValueInt,
							@Rate_ValueFloat = :Rate_ValueFloat,
							@Rate_ValueStr = :Rate_ValueStr,
							@Rate_ValuesIs = :Rate_ValuesIs,
							@Server_id = :Server_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$queryParams = array(
						'Rate_id' => NULL,
						'RateType_id' => $inresultdata['RateType_id'],
						'Rate_ValueInt' => NULL,
						'Rate_ValueFloat' => NULL,
						'Rate_ValueStr' => NULL,
						'Rate_ValuesIs' => NULL,
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					switch ($inresultdata['RateValueType_SysNick']) {
						case 'int': $queryParams['Rate_ValueInt'] = $data[$inresult]; break;
						case 'float': $queryParams['Rate_ValueFloat'] = $data[$inresult]; break;
						case 'string': $queryParams['Rate_ValueStr'] = $data[$inresult]; break;
						case 'reference': $queryParams['Rate_ValuesIs'] = $data[$inresult]; break;
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
						declare
							@Rate_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Rate_id = :Rate_id;
						exec p_Rate_upd
							@Rate_id = @Rate_id output,
							@RateType_id = :RateType_id,
							@Rate_ValueInt = :Rate_ValueInt,
							@Rate_ValueFloat = :Rate_ValueFloat,
							@Rate_ValueStr = :Rate_ValueStr,
							@Rate_ValuesIs = :Rate_ValuesIs,
							@Server_id = :Server_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$queryParams = array(
						'Rate_id' => $inresultdata['Rate_id'],
						'RateType_id' => $inresultdata['RateType_id'],
						'Rate_ValueInt' => NULL,
						'Rate_ValueFloat' => NULL,
						'Rate_ValueStr' => NULL,
						'Rate_ValuesIs' => NULL,
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					switch ($inresultdata['RateValueType_SysNick']) {
						case 'int': $queryParams['Rate_ValueInt'] = $data[$inresult]; break;
						case 'float': $queryParams['Rate_ValueFloat'] = $data[$inresult]; break;
						case 'string': $queryParams['Rate_ValueStr'] = $data[$inresult]; break;
						case 'reference': $queryParams['Rate_ValuesIs'] = $data[$inresult]; break;
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
		
		$results = empty($data['results'])?[]:json_decode($data['results'], true);
		
		foreach ($results as $res) {

			$checkrecord = $this->getFirstResultFromQuery("
				select ScreenCheckListResult_id from r101.ScreenCheckListResult (nolock) where EvnPLDisp_id = ? and ScreenCheckList_id = ?
			", [$data['EvnUslugaDispDop_pid'], $res['ScreenCheckList_id']]);
			
			$proc = 'r101.p_ScreenCheckListResult_upd';

			if (empty($checkrecord)) $proc = 'r101.p_ScreenCheckListResult_ins';
			
			$params = [
				'ScreenCheckListResult_id' => $checkrecord,
				'EvnPLDisp_id' => $data['EvnUslugaDispDop_pid'],
				'SurveyType_id' => $data['SurveyType_id'],
				'pmUser_id' => $data['pmUser_id'],
				'ScreenCheckList_id' => $res['ScreenCheckList_id'],
				'ScreenValue_id' => $res['ScreenValue_id']
			];

			$query = "
				declare
					@ScreenCheckListResult_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @ScreenCheckListResult_id = :ScreenCheckListResult_id;
				exec {$proc}
					@ScreenCheckListResult_id = @ScreenCheckListResult_id output,
					@EvnPLDisp_id = :EvnPLDisp_id,
					@SurveyType_id = :SurveyType_id,
					@pmUser_id = :pmUser_id,
					
					@ScreenCheckList_id = :ScreenCheckList_id,
					@ScreenValue_id= :ScreenValue_id,

					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				SELECT @ScreenCheckListResult_id as ScreenCheckListResult_id,@ErrCode as ErrorCode, @ErrMessage as Error_Message
			";
			$this->db->query($query, $params);
		}

		$this->db->trans_commit();

		return array(array('EvnUslugaDispDop_id' => $EvnUsluga_id, 'Error_Code' => '', 'Error_Msg' => ''));
	}
	
	/**
	 *	Сохранение карты
	 */	
    function saveEvnPLDispScreen($data)
    {
    	$proc = '';
    	if ( !isset($data['EvnPLDispScreen_id']) ) {
			$proc = 'p_EvnPLDispScreen_ins';
			$data['EvnPLDispScreen_id'] = null;
			$action = 'add';
	    } else {
			$proc = 'p_EvnPLDispScreen_upd';
			$action = null;
	    }
	    if(!isset($data['AgeGroupDisp_id'])) { 
	    	$data['AgeGroupDisp_id'] = null;
	    }

		// получаем даты начала и конца услуг внутри диспансеризации.
		/*
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			$data['EvnPLDispScreen_setDate'] = $minmaxdates['mindate'];
			$data['EvnPLDispScreen_disDate'] = $minmaxdates['maxdate'];
		} else {
			$data['EvnPLDispScreen_setDate'] = date('Y-m-d');
			$data['EvnPLDispScreen_disDate'] = date('Y-m-d');
		}*/
		$data['EvnPLDispScreen_disDate'] = $data['EvnPLDispScreen_setDate'];
		
		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispScreen_IsEndStage']) || $data['EvnPLDispScreen_IsEndStage'] == 1) {
			$data['EvnPLDispScreen_disDate'] = NULL;
		}

		// // проверяем есть ли уже карта на данного пациента
		// $filter = '';
		// if(!isset($data['withoutAgeGroups']) || $data['withoutAgeGroups'] != 'true') {
		// 	$filter .= 'and AgeGroupDisp_id = :AgeGroupDisp_id';
		// }else {
		// 	//$filter .= 'and datediff(mm, EvnPLDispScreen_setDate, getdate()) > 22'
		// }
		// $query = "
		// 	select
		// 		EvnPLDispScreen_id
		// 	from
		// 		v_EvnPLDispScreen (nolock)
		// 	where
		// 		Lpu_id = :Lpu_id
		// 		and Person_id = :Person_id
		// 		and (EvnPLDispScreen_id <> :EvnPLDispScreen_id OR :EvnPLDispScreen_id IS NULL)
		// 		{$filter}
		// ";

		// $result = $this->queryResult($query, $data);
		// if (!is_array($result)) {
		// 	return array('Error_Msg' => 'Ошибка проверки наличия карт скринингового исследования.');
		// }
		// if (!empty($result[0]['EvnPLDispScreen_id'])) {
		// 	return array('Error_Msg' => 'На данного пациента с указанной возрастной группой уже создана карта скринингового исследования.');
		// }

		// Все проверки актуальны только для законченных случаев
		if ($data['EvnPLDispScreen_IsEndStage'] == 2) {
			
			// Выгребаем все диагнозы из осмотров
			$diags = $this->queryList("
				select distinct substring(d.Diag_Code, 1, 3) as Diag_Code
				from v_EvnUslugaDispDop eudd (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = eudd.Diag_id
				where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
			", array(
				'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
			));
			
			$is_a00_t88 = false;
			foreach($diags as $diag) {
				if ($diag >= 'A00' && $diag <= 'T88') {
					$is_a00_t88 = true;
				}
			}
			
			// 1. Проверка значения в поле «Направлен к врачу ПМСП»
			if ($data['EvnPLDispScreen_IsDirectedPMSP'] == 1 && $is_a00_t88) {
				return array('Error_Msg' => 'При диагнозах A00-T88.9 должно быть отмечено «Да» в поле «Направлен к врачу ПМСП');
			}
			
			// 2. Проверка значения в поле «Группа диспансерного наблюдения»
			if (in_array($data['HealthKind_id'], array(8, 9)) && $is_a00_t88) {
				return array('Error_Msg' => 'При диагнозах A00-T88.9 группа диспансерного наблюдения должна быть II, III');
			}
			
			// 3. Проверка результатов исследования Цитологическое исследование шейки матки (Рар-тест)
			if (!array_intersect($diags, array('D26', 'N84', 'N86', 'N87'))) {
				$rar_result = $this->getFirstResultFromQuery("
					select top 1 r.Rate_ValueStr
					from v_EvnUslugaDispDop eudd (nolock)
					left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
					left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id and r.RateType_id = 194
					where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = 108
				", array(
					'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
				));
				if (in_array($rar_result, array(2,3,4,5,6,7,8))) {
					return array('Error_Msg' => 'При указанном результате исследования Цитологическое исследование шейки матки (Рар-тест) требуется указать диагноз  D26, N84, N86, N87');
				}
			}
			
			// 4. Проверка результатов исследования Биопсия
			$biopsy_result = $this->getFirstResultFromQuery("
				select top 1 r.Rate_ValueStr
				from v_EvnUslugaDispDop eudd (nolock)
				left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
				left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id and r.RateType_id = 198
				where eudd.EvnUslugaDispDop_rid = :EvnPLDispScreen_id and eudd.SurveyType_id = 110 
			", array(
				'EvnPLDispScreen_id' => $data['EvnPLDispScreen_id']
			));
			if (!array_intersect($diags, array('D26', 'N84', 'N86', 'N87')) && in_array($biopsy_result, array(1,2,3,4))) {
				return array('Error_Msg' => 'При указанном результате исследования Биопсия требуется указать диагноз  D26, N84, N86, N87');
			}
			
			if (!array_intersect($diags, array('D06')) && $biopsy_result == 5) {
				return array('Error_Msg' => 'При указанном результате исследования Биопсия требуется указать диагноз  D06');
			}
		}

   		$query = "
		    declare
		        @EvnPLDispScreen_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnPLDispScreen_id = :EvnPLDispScreen_id;
			exec {$proc}
				@EvnPLDispScreen_id = @EvnPLDispScreen_id output,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@EvnPLDispScreen_setDT = :EvnPLDispScreen_setDate,
				@EvnPLDispScreen_disDT = :EvnPLDispScreen_disDate,
				@Lpu_id = :Lpu_id,
				@DispClass_id = 13,
				@AttachType_id = 2,
				@AgeGroupDisp_id = :AgeGroupDisp_id,
				@EvnPLDispScreen_PersonWaist = :EvnPLDispScreen_PersonWaist,
				@EvnPLDispScreen_QueteletIndex = :EvnPLDispScreen_QueteletIndex,
				@EvnPLDispScreen_ArteriaSistolPress = :EvnPLDispScreen_ArteriaSistolPress,
				@EvnPLDispScreen_ArteriaDiastolPress = :EvnPLDispScreen_ArteriaDiastolPress,
				@HealthKind_id = :HealthKind_id,
				@EvnPLDispScreen_IsEndStage = :EvnPLDispScreen_IsEndStage,
				@AlcoholIngestType_bid = :AlcoholIngestType_bid,
				@AlcoholIngestType_vid = :AlcoholIngestType_vid,
				@AlcoholIngestType_wid = :AlcoholIngestType_wid,
				@EvnPLDispScreen_IsAlco = :EvnPLDispScreen_IsAlco,
				@EvnPLDispScreen_IsBleeding = :EvnPLDispScreen_IsBleeding,
				@EvnPLDispScreen_IsCoronary = :EvnPLDispScreen_IsCoronary,
				@EvnPLDispScreen_IsHeadache = :EvnPLDispScreen_IsHeadache,
				@EvnPLDispScreen_IsHeartache = :EvnPLDispScreen_IsHeartache,
				@EvnPLDispScreen_IsHighPressure = :EvnPLDispScreen_IsHighPressure,
				@EvnPLDispScreen_IsParCoronary = :EvnPLDispScreen_IsParCoronary,
				@EvnPLDispScreen_IsSmoking = :EvnPLDispScreen_IsSmoking,
				@EvnPLDispScreen_IsBlurVision = :EvnPLDispScreen_IsBlurVision,
				@EvnPLDispScreen_IsDailyPhysAct = :EvnPLDispScreen_IsDailyPhysAct,
				@EvnPLDispScreen_IsDirectedPMSP = :EvnPLDispScreen_IsDirectedPMSP,
				@EvnPLDispScreen_IsGenPredisposed = :EvnPLDispScreen_IsGenPredisposed,
				@EvnPLDispScreen_IsGlaucoma = :EvnPLDispScreen_IsGlaucoma,
				@EvnPLDispScreen_IsHealthy = :EvnPLDispScreen_IsHealthy,
				@EvnPLDispScreen_IsHighMyopia = :EvnPLDispScreen_IsHighMyopia,
				@EvnPLDispScreen_IsHyperglycaemia = :EvnPLDispScreen_IsHyperglycaemia,
				@EvnPLDispScreen_IsHyperlipidemia = :EvnPLDispScreen_IsHyperlipidemia,
				@EvnPLDispScreen_IsHypertension = :EvnPLDispScreen_IsHypertension,
				@EvnPLDispScreen_IsLowPhysAct = :EvnPLDispScreen_IsLowPhysAct,
				@EvnPLDispScreen_IsOverweight = :EvnPLDispScreen_IsOverweight,
				@EvnPLDispScreen_IsVisImpair = :EvnPLDispScreen_IsVisImpair,
				@FecalCasts_id = :FecalCasts_id,
				@EvnPLDispScreen_IsAlcoholAbuse = :EvnPLDispScreen_IsAlcoholAbuse,
				@EvnPLDispScreen_IsDisability = :EvnPLDispScreen_IsDisability,
				@EvnPLDispScreen_DisabilityPeriod = :EvnPLDispScreen_DisabilityPeriod,
				@EvnPLDispScreen_DisabilityYear = :EvnPLDispScreen_DisabilityYear,
				@Diag_disid = :Diag_disid,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @EvnPLDispScreen_id as EvnPLDispScreen_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		

        if ( is_object($result) ) {
			$resp = $result->result('array');

			if (!empty($resp[0]['EvnPLDispScreen_id'])) {

				if($action == 'add' && !empty($data['data']) && $data['withoutAgeGroups']){
					//сохраняем флаги пройденных ранее обследований
					foreach($data['data'] as $value) {
						
						$queryParams = array(
							'checked' => $value->checked,
							'EvnPLDisp_id' => $resp[0]['EvnPLDispScreen_id'],
							'SurveyType_id' => $value->SurveyType_id,
							'pmUser_id' => $data['pmUser_id']
						);
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_DopDispInfoConsent_ins
								@EvnPLDisp_id = :EvnPLDisp_id,
								@SurveyType_id = :SurveyType_id,
								@DopDispInfoConsent_IsEarlier = :checked,
								@pmUser_id = :pmUser_id,

								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							SELECT @ErrCode as ErrorCode, @ErrMessage as Error_Message
						";
						$this->db->query($query, $queryParams);
					}
				}
				// сохраняем рост/вес
				$this->load->model('PersonHeight_model');
				$data['PersonHeight_id'] = $this->getFirstResultFromQuery("
					select PersonHeight_id from v_PersonHeight (nolock) where Evn_id = :Evn_id
				", array(
					'Evn_id' => $resp[0]['EvnPLDispScreen_id']
				));
				if (empty($data['PersonHeight_id'])) {
					$data['PersonHeight_id'] = null;
				}
				$result = $this->PersonHeight_model->savePersonHeight(array(
					'Server_id' => $data['Server_id'],
					'PersonHeight_id' => $data['PersonHeight_id'],
					'Person_id' => $data['Person_id'],
					'PersonHeight_setDate' => $data['EvnPLDispScreen_setDate'],
					'PersonHeight_Height' => $data['PersonHeight_Height'],
					'PersonHeight_IsAbnorm' => NULL,
					'HeightAbnormType_id' => NULL,
					'HeightMeasureType_id' => NULL,
					'Evn_id' => $resp[0]['EvnPLDispScreen_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($result[0]['Error_Msg'])) {
					return array('Error_Msg' => $result[0]['Error_Msg']);
				}
				if (empty($result[0]['PersonHeight_id'])) {
					return array('Error_Msg' => 'Ошибка при сохранении роста');
				}

				$this->load->model('PersonWeight_model');
				$data['PersonWeight_id'] = $this->getFirstResultFromQuery("
					select PersonWeight_id from v_PersonWeight (nolock) where Evn_id = :Evn_id
				", array(
					'Evn_id' => $resp[0]['EvnPLDispScreen_id']
				));
				if (empty($data['PersonWeight_id'])) {
					$data['PersonWeight_id'] = null;
				}
				$result = $this->PersonWeight_model->savePersonWeight(array(
					'Server_id' => $data['Server_id'],
					'PersonWeight_id' => $data['PersonWeight_id'],
					'Person_id' => $data['Person_id'],
					'PersonWeight_setDate' => $data['EvnPLDispScreen_setDate'],
					'PersonWeight_Weight' => $data['PersonWeight_Weight'],
					'PersonWeight_IsAbnorm' => NULL,
					'WeightAbnormType_id' => NULL,
					'WeightMeasureType_id' => NULL,
					'Evn_id' => $resp[0]['EvnPLDispScreen_id'],
					'Okei_id' => 37,//кг
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($result[0]['Error_Msg'])) {
					return array('Error_Msg' => $result[0]['Error_Msg']);
				}
				if (empty($result[0]['PersonWeight_id'])) {
					return array('Error_Msg' => 'Ошибка при сохранении роста');
				}

				if (getRegionNick() == 'kz' && $data['ScreenType_id']) {

					$checkrecord = $this->getFirstRowFromQuery("
						select 
							EvnPLDispScreenLink_id,
							EvnPLDispScreenLink_IsProfBegin,
							EvnPLDispScreenLink_IsProfEnd 
						from 
							r101.v_EvnPLDispScreenLink (nolock) 
						where 
							EvnPLDispScreen_id = ?
					", [$resp[0]['EvnPLDispScreen_id']]);
					
					$kzScreenProc = 'r101.p_EvnPLDispScreenLink_upd';
					
					if (empty($checkrecord)) $kzScreenProc = 'r101.p_EvnPLDispScreenLink_ins';

					$queryParams = array(
						'EvnPLDispScreenLink_id' => $checkrecord['EvnPLDispScreenLink_id'],
						'EvnPLDispScreen_id' => $resp[0]['EvnPLDispScreen_id'],
						'ScreenType_id' => $data['ScreenType_id'],
						'EvnPLDispScreenLink_IsProfBegin' => $checkrecord['EvnPLDispScreenLink_IsProfBegin'],
						'EvnPLDispScreenLink_IsProfEnd' => $checkrecord['EvnPLDispScreenLink_IsProfEnd'],
						'pmUser_id' => $data['pmUser_id']
					);
					
					$query = "
						declare
							@EvnPLDispScreenLink_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @EvnPLDispScreenLink_id = :EvnPLDispScreenLink_id;
						exec {$kzScreenProc}
							@EvnPLDispScreenLink_id = @EvnPLDispScreenLink_id output,
							@EvnPLDispScreen_id = :EvnPLDispScreen_id,
							@EvnPLDispScreenLink_IsProfBegin = :EvnPLDispScreenLink_IsProfBegin,
							@EvnPLDispScreenLink_IsProfEnd = :EvnPLDispScreenLink_IsProfEnd,
							@ScreenType_id = :ScreenType_id,
							@pmUser_id = :pmUser_id,

							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						SELECT @EvnPLDispScreenLink_id as EvnPLDispScreenLink_id,@ErrCode as ErrorCode, @ErrMessage as Error_Message
					";
					$this->db->query($query, $queryParams);
					
					if ($data['ScreenEndCause_id']) {
						$checkrecord = $this->getFirstRowFromQuery("
							select EvnLinkAPP_id,Screening_id from r101.EvnLinkAPP (nolock) where Evn_id = ?
						", [$resp[0]['EvnPLDispScreen_id']]);
			
						$proc = 'r101.p_EvnLinkAPP_upd';

						if (empty($checkrecord)) $proc = 'r101.p_EvnLinkAPP_ins';

						$this->execCommonSP($proc, [
							'EvnLinkAPP_id' => $checkrecord['EvnLinkAPP_id'] ?? null,
							'Evn_id' => $resp[0]['EvnPLDispScreen_id'],
							'Screening_id' => $checkrecord['Screening_id'] ?? null,
							'pmUser_id' => $data['pmUser_id'],
							'ScreenEndCause_id' => $data['ScreenEndCause_id']
						], 'array_assoc');
					}
				}

				// сохраняем опрос по скрининг-тесту
			}

			return $resp;
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты скринингового исследования)');
		}
    }
	
	
	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispScreenYears($data)
    {
  		$sql = "
			select
            -- select
			count(EPLDP.EvnPLDispScreen_id) as count,
			year(EPLDP.EvnPLDispScreen_setDate) as EvnPLDispScreen_Year
			-- end select
			from
			-- from
				v_PersonState PS with (nolock)
					inner join [v_EvnPLDispScreen] [EPLDP] with (nolock) on [PS].[Person_id] = [EPLDP].[Person_id] and [EPLDP].Lpu_id = :Lpu_id
			-- end from
			where
			-- where
				exists
					(select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and PC.Lpu_id = :Lpu_id)
				and year(EPLDP.EvnPLDispScreen_setDate) >= 2013
				and EPLDP.EvnPLDispScreen_setDate is not null
			-- end where
			GROUP BY
				year(EPLDP.EvnPLDispScreen_setDate)
			ORDER BY
				year(EPLDP.EvnPLDispScreen_setDate)
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
	function checkIfEvnPLDispScreenExists($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispScreen_id) as count
			FROM
				v_EvnPLDispScreen (nolock)
			WHERE
				Person_id = ? and Lpu_id = ? and year(EvnPLDispScreen_setDate) = year(dbo.tzGetDate())
		";

		$res = $this->db->query($sql, array($data['Person_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 )
				return array(array('isEvnPLDispScreenExists' => false, 'Error_Msg' => ''));
			else
				return array(array('isEvnPLDispScreenExists' => true, 'Error_Msg' => ''));
		}
 	    else
 	    	return false;
    }

	/**
	 * Проверка, есть ли талон на этого человека в этом или предыдущем году
	 */
	function checkIfEvnPLDispScreenExistsInTwoYear($data)
    {
  		$sql = "
			SELECT top 1
				case
					when year(EvnPLDispScreen_setDate) = year(:EvnPLDisp_consDate) then 2
					when year(EvnPLDispScreen_setDate) = year(:EvnPLDisp_consDate)-1 then 1
				end as ExistCard
			FROM
				v_EvnPLDispScreen (nolock)
			WHERE
				Person_id = :Person_id and Lpu_id = :Lpu_id and (year(EvnPLDispScreen_setDate) IN (year(:EvnPLDisp_consDate), year(:EvnPLDisp_consDate)-1))
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
	 * Проверка на возраст
	 */
	function checkEvnPLDispScreenAge($data, $mode) {
		// Возраст пациента на конец года от 18 лет
		$sql = "
			SELECT top 1 dbo.Age2(PS.Person_BirthDay, :EvnPLDispScreen_consDate) as Person_Age
			FROM v_PersonState PS (nolock)
			WHERE PS.Person_id = :Person_id
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			return 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')';
		}

		$sel = $res->result('array');

		if ( is_array($sel) && count($sel) > 0 ) {
			if ( $sel[0]['Person_Age'] < 18 ) {
				return 'Профосмотр проводится для людей в возрасте с 18 лет';
			}
		}
		else {
			return 'Ошибка при получении возраста пациента';
		}

		return '';
	}

	/**
	 *	Получение идентификатора посещения
	 */
	function getEvnVizitDispDopId($EvnPLDispScreen_id = null, $DopDispInfoConsent_id = null) {
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
				and EUDD.EvnUslugaDispDop_rid = :EvnPLDispScreen_id
				and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
				and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
				and ST.SurveyType_Code <> 49
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
		";
		
		$result = $this->db->query($query, array(
			 'DopDispInfoConsent_id' => $DopDispInfoConsent_id
			,'EvnPLDispScreen_id' => $EvnPLDispScreen_id
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
	 * Получаем список исследование - резудьтаты
	 * @param $data
	 * @return array
	 */
	function getEvnUslugaDispDopResult($data) {
		
		$result = $this->db->query("
			select
				scl.ScreenCheckList_id,
				sv.ScreenValue_id, 
				scl.ScreenCheckList_PunktName,
				sv.ScreenValue_Name,
				sclr.ScreenValue_id as currentValue
			from r101.ScreenCheckList scl (nolock)
				inner join r101.ScreenValue sv (nolock) on sv.ScreenCheckList_PunktCode = scl.ScreenCheckList_PunktCode
				left join r101.ScreenCheckListResult sclr (nolock) on sclr.ScreenCheckList_id = scl.ScreenCheckList_id and sclr.EvnPLDisp_id = ?
			where scl.ScreenType_id = ? and scl.UslugaComplex_id = ?
		", [$data['EvnUslugaDispDop_pid'], $data['ScreenType_id'], $data['UslugaComplex_id']])->result('array');

		$fin = [];
		
		foreach ($result as $res) {
			if (empty($fin[$res['ScreenCheckList_id']])) {
				$fin[$res['ScreenCheckList_id']] = [];
}

			$fin[$res['ScreenCheckList_id']][] = [
				'ScreenCheckList_id' => $res['ScreenCheckList_id'],
				'ScreenValue_id' => $res['ScreenValue_id'],
				'ScreenCheckList_PunktName' => $res['ScreenCheckList_PunktName'],
				'ScreenValue_Name' => $res['ScreenValue_Name'],
				'currentValue' => $res['currentValue']
			];
		}
		
		return $fin;
	}
}
?>