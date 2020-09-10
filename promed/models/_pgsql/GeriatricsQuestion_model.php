<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * GeriatricsQuestion_model - гериатрия
 * сделано в режиме совместимости с OnkoCtrl
 */
class GeriatricsQuestion_model extends swPgModel {
	/**
	 * Журнал анкет
	 */
	public function GetOnkoCtrlProfileJurnal($data) {
		$filters = array();
		$filter = "(1=1)";
				
		$filter_join = '';
		$join = '';
		$queryParams = array();

		if (isset($data['Empty']) && $data['Empty']==1) {
			return array('data'=>array(),'totalCount'=>0);
		};

		if (isset($data['Lpu_id']))  {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		
		if (isset($data['SurName'])) {
			$filter .= " and ps.Person_SurName ilike :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};
		
		if (isset($data['FirName'])) {
			$filter .= " and ps.Person_FirName ilike :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};
		
		if (isset($data['SecName'])) {
			$filter .= " and ps.Person_SecName ilike :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		};

		if (isset($data['BirthDayRange'][0])) {
			$filter .= " and ps.Person_BirthDay  <= cast(:BirthDayRangeBegin as date)";
			$queryParams['BirthDayRangeBegin'] = $data['BirthDayRange'][0];
		}
		
		if (isset($data['BirthDayRange'][1])) {
			$filter .= " and ps.Person_BirthDay  <= cast(:BirthDayRangeEnd as date)";
			$queryParams['BirthDayRangeEnd'] = $data['BirthDayRange'][1];
		}
		
		if (isset($data['BirthDay'])) {
			$filter .= " and ps.Person_BirthDay = cast(:BirthDay as date)";
			$queryParams['BirthDay'] = $data['BirthDay'];
		}
		
		if (isset($data['PeriodRange'][0])) {
			$filter .= " and p.GeriatricsQuestion_setDate  >= cast(:PeriodRangeBegin as date)";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.GeriatricsQuestion_setDate  <= cast(:PeriodRangeEnd as date)";
			$queryParams['PeriodRangeEnd'] = $data['PeriodRange'][1];
		}

		if (isset($data['Doctor'])) {
			$filter .= " and p.MedStaffFact_id = :Doctor";
			$queryParams['Doctor'] = $data['Doctor'];
		};

		if (isset($data['AgeNotHindrance_id'])) {
			$filter .= " and p.AgeNotHindrance_id = :AgeNotHindrance_id";
			$queryParams['AgeNotHindrance_id'] = $data['AgeNotHindrance_id'];
		};

		if (isset($data['Sex_id'])) {
			$filter .= " and ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		};
		
		if (isset($data['Uch'])) {
			if ($data['Uch'] == '0') {
				$filter .= " and (pcard.LpuRegion_id = '' or lpu.Lpu_id is null)";
			} else {
				$filter .= " and pcard.LpuRegion_id = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};
   
		$sql = "
			SELECT  
				-- select
				 p.GeriatricsQuestion_id as \"id\"
				,msf.Person_Fin as \"MedPersonal_fin\"
				,p.Person_id as \"Person_id\"
				,p.GeriatricsQuestion_id as \"PersonOnkoProfile_id\"
				,ps.Person_SurName as \"SurName\"
				,ps.Person_FirName as \"FirName\"
				,ps.Person_SecName as \"SecName\"
				,rtrim(rtrim(coalesce(ps.Person_Surname, '')) || ' ' || rtrim(coalesce(ps.Person_Firname, '')) || ' ' || rtrim(coalesce(ps.Person_Secname, ''))) as \"fio\"
				,to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\"
				,ps.Sex_id as \"Sex_id\"
				,substring(sex.Sex_Name, 1, 1) as \"sex\"
				,coalesce(uaddr.Address_Address,paddr.Address_Address) as \"Address\"
				,lpu.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
				,lr.LpuRegionType_Name || ' №' || lr.LpuRegion_Name as \"uch\"
				,lr.LpuRegion_id as \"LpuRegion_id\"
				,to_char(p.GeriatricsQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\"
				,p.MedStaffFact_id as \"MedStaffFact_id\"
				,anh.AgeNotHindrance_id as \"AgeNotHindrance_id\"
				,anh.AgeNotHindrance_Name as \"AgeNotHindrance_Name\"
				-- end select
			FROM 
				-- from
				GeriatricsQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				inner join v_PersonState ps on p.Person_id = ps.Person_id
				left join v_AgeNotHindrance anh on anh.AgeNotHindrance_id = p.AgeNotHindrance_id
				left join v_Sex sex on sex.Sex_id = ps.Sex_id
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id
					from v_PersonCard pc
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard on true
				left join v_Lpu lpu on pcard.Lpu_id = lpu.Lpu_id
				left join v_LpuRegion lr on pcard.LpuRegion_id = lr.LpuRegion_id
				left join v_Address uaddr on ps.UAddress_id = uaddr.Address_id
				left join v_Address paddr on ps.PAddress_id = paddr.Address_id
				-- end from  
			WHERE 
				-- where
				{$filter}
				-- end where        
			order by 
				-- order by
				p.GeriatricsQuestion_setDate desc
				-- end order by  
		";
					
		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка доп инфы для формы анкетирования
	 */
	public function loadOnkoContrProfileFormInfo($data) {
		$queryParams = array();
		$filter = '';

		if (!empty($data['PersonOnkoProfile_id'])) {
			$filter = ' and GeriatricsQuestion_id = :PersonOnkoProfile_id ';
		}
		
		$query = "
			SELECT 
				 p.GeriatricsQuestion_id as \"PersonOnkoProfile_id\"
				,p.Person_id as \"Person_id\"
				,p.MorbusGeriatrics_id as \"MorbusGeriatrics_id\"
				,to_char(p.GeriatricsQuestion_setDate, 'dd.mm.yyyy') as \"PersonOnkoProfile_DtBeg\"
				,msf.Lpu_id as \"Lpu_id\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,msf.MedStaffFact_id as \"MedStaffFact_id\"
				,msf.LpuBuilding_id as \"LpuBuilding_id\"
				,msf.LpuSection_id as \"LpuSection_id\"
				,p.GeriatricsQuestion_Other as \"GeriatricsQuestion_Other\"
				,p.GeriatricsQuestion_CountYes as \"GeriatricsQuestion_CountYes\"
				,p.AgeNotHindrance_id as \"AgeNotHindrance_id\"
			FROM GeriatricsQuestion p
				left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l on l.Lpu_id = msf.lpu_id
				where p.Person_id = :Person_id
			{$filter} 
		";

		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['PersonOnkoProfile_id'] = $data['PersonOnkoProfile_id'];
		
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка вопросов для анкеты
	 */
	public function getOnkoQuestions($data) {
		$sql = "
			SELECT 
				q.GeriatricsQuestionType_id as \"OnkoQuestions_id\",
				q.GeriatricsQuestionType_Name as \"OnkoQuestions_Name\",
				q.GeriatricsQuestionType_Num as \"Questions_Num\",
				coalesce(p.AnswerYesNoType_id, 1) as \"val\",
				at.AnswerType_Code as \"AnswerType_Code\",
				ac.AnswerClass_SysNick as \"AnswerClass_SysNick\"
			FROM GeriatricsQuestionType q
				left join GeriatricsQuestionAnswer p on 
					p.GeriatricsQuestionType_id = q.GeriatricsQuestionType_id and 
					p.GeriatricsQuestion_id = :GeriatricsQuestion_id
				left join v_AnswerType at on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac on ac.AnswerClass_id = q.AnswerClass_id
			order by q.GeriatricsQuestionType_Code
		";

		$queryParams['GeriatricsQuestion_id'] = $data['PersonOnkoProfile_id'];
		
		return $this->queryResult($sql, $queryParams);
	}

	/**
	 * Сохранение информации об анкетировании пациента
	 */
	public function savePersonOnkoProfile($data) {
		$procedure = empty($data['PersonOnkoProfile_id']) ? 'p_GeriatricsQuestion_ins' : 'p_GeriatricsQuestion_upd';
		
		$GeriatricsQuestion_CountYes = 0;
		
		foreach ( $data['QuestionAnswer'] as $row ) {
			if ( $row[1] == 1 ) {
				$GeriatricsQuestion_CountYes++;
			}
		}

		if ( !empty($data['GeriatricsQuestion_Other']) ) {
			$GeriatricsQuestion_CountYes++;
		}

		switch ( $GeriatricsQuestion_CountYes ) {
			case 0:
				$data['AgeNotHindrance_id'] = 3;
				break;

			case 1:
			case 2:
				$data['AgeNotHindrance_id'] = 2;
				break;

			default:
				$data['AgeNotHindrance_id'] = 1;
				break;
		}

		$queryParams = array();
		$query = "
			select
				GeriatricsQuestion_id as \"GeriatricsQuestion_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				GeriatricsQuestion_id := :GeriatricsQuestion_id,
				Person_id := :Person_id,
				GeriatricsQuestion_setDate := :GeriatricsQuestion_setDate,
				MedStaffFact_id := :MedStaffFact_id,
				GeriatricsQuestion_Other := :GeriatricsQuestion_Other,
				GeriatricsQuestion_CountYes := :GeriatricsQuestion_CountYes,
				AgeNotHindrance_id := :AgeNotHindrance_id,
				MorbusGeriatrics_id := :MorbusGeriatrics_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams['GeriatricsQuestion_id'] = $data['PersonOnkoProfile_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['GeriatricsQuestion_setDate'] = $data['Profile_Date'];
		$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$queryParams['GeriatricsQuestion_Other'] = null/*$data['GeriatricsQuestion_Other']*/;
		$queryParams['GeriatricsQuestion_CountYes'] = $GeriatricsQuestion_CountYes;
		$queryParams['AgeNotHindrance_id'] = $data['AgeNotHindrance_id'];
		$queryParams['MorbusGeriatrics_id'] = !empty($data['MorbusGeriatrics_id']) ? $data['MorbusGeriatrics_id'] : null;
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		$result = $this->queryResult($query, $queryParams);
		
		if (count($result) && isset($result[0]['GeriatricsQuestion_id'])) {
			$data['GeriatricsQuestion_id'] = $result[0]['GeriatricsQuestion_id'];
			$result[0]['PersonOnkoProfile_id'] = $result[0]['GeriatricsQuestion_id'];
			$result[0]['AgeNotHindrance_id'] = $data['AgeNotHindrance_id'];
			$this->saveGeriatricsQuestionAnswer($data);

			// Проводим манипуляции, связанные с извещением
			// Статус пациента: хрупкий, прехрупкий

			if ( $data['AgeNotHindrance_id'] == 1 || $data['AgeNotHindrance_id'] == 2 ) {
				// Проверяем наличие активной записи регистра
				$checkPersonRegisterExists = $this->getFirstResultFromQuery("
					select
						PersonRegister_id as \"PersonRegister_id\"
					from v_PersonRegister
					where Person_id = :Person_id
						and PersonRegisterType_id = 67
						and PersonRegisterOutCause_id is null
				", $data, true);

				if ( empty($checkPersonRegisterExists) ) {
					$data['Diag_id'] = $this->getFirstResultFromQuery("
						select
							Diag_id as \"Diag_id\"
						from v_Diag
						where DiagLevel_id = 4
							and Diag_Code ilike 'R54%'
					", array(), true);

					// Проверяем наличие основного диагноза R54 в случаях пациента
					$checkR54DiagExists = $this->getFirstResultFromQuery("
						(select
							EvnVizitPL_id as \"Evn_id\"
						from v_EvnVizitPL
						where Person_id = :Person_id
							and Diag_id = :Diag_id
						limit 1)
						union all
						(select
							EvnSection_id as \"Evn_id\"
						from v_EvnSection
						where Person_id = :Person_id
							and Diag_id = :Diag_id
						limit 1)
					", $data, true);

					if ( !empty($checkR54DiagExists) ) {
						// Проверяем наличие извещения
						// Пока пропускаем эту проверку, т.к. все равно запись регистра создается автоматически

						if ( empty($checkEvnNotifyGeriatricsExists) ) {
							if ( !empty($data['MedStaffFact_id']) ) {
								$data['MedPersonal_iid'] = $this->getFirstResultFromQuery("
									select
										MedPersonal_id as \"MedPersonal_id\"
									from v_MedStaffFact
									where MedStaffFact_id = :MedStaffFact_id
									limit 1
								", $data, true);
							}

							$this->load->model('PersonRegister_model', 'PersonRegister_model');

							$this->PersonRegister_model->setPerson_id($data['Person_id']);
							$this->PersonRegister_model->setPersonRegisterType_id(67);
							$this->PersonRegister_model->setPersonRegisterType_SysNick('geriatrics');
							$this->PersonRegister_model->setMorbusType_SysNick('geriatrics');
							$this->PersonRegister_model->setDiag_id($data['Diag_id']);
							$this->PersonRegister_model->setPersonRegister_setDate($data['Profile_Date']);
							$this->PersonRegister_model->setLpu_iid($data['Lpu_id']);
							//$this->PersonRegister_model->setEvnNotifyBase_id($data['EvnNotifyBase_id']);

							if ( !empty($data['MedPersonal_iid']) ) {
								$this->PersonRegister_model->setMedPersonal_iid($data['MedPersonal_iid']);
							}

							$this->PersonRegister_model->setSessionParams($data['session']);

							$response = $this->PersonRegister_model->save();

							if ( !empty($response[0]['Error_Msg']) ) {
								$result[0]['Alert_Msg'] = '<div>Внимание! Ошибка при добавлении пациента в регистр по гериатрии! Причина:</div><div>' . $response[0]['Error_Msg'] . '</div>';
							}
							else if ( !empty($response[0]['PersonRegister_id']) ) {
								$this->db->query("
									with mv as (
										select
											mg.MorbusGeriatrics_id
										from v_PersonRegister pr
											inner join v_MorbusGeriatrics mg on mg.Morbus_id = pr.Morbus_id
										where pr.Person_id = :Person_id
											and pr.PersonRegister_id = :PersonRegister_id
											and pr.PersonRegisterType_id = 67
										order by mg.MorbusGeriatrics_id desc
										limit 1
									)

									update GeriatricsQuestion
									set MorbusGeriatrics_id = (select MorbusGeriatrics_id as mv)
									where GeriatricsQuestion_id = :GeriatricsQuestion_id;

									update MorbusGeriatrics
									set AgeNotHindrance_id = :AgeNotHindrance_id
									where MorbusGeriatrics_id = (select MorbusGeriatrics_id as mv);
								", array(
									'Person_id' => $data['Person_id'],
									'AgeNotHindrance_id' => $data['AgeNotHindrance_id'],
									'PersonRegister_id' => $response[0]['PersonRegister_id'],
									'GeriatricsQuestion_id' => $result[0]['GeriatricsQuestion_id'],
								));
							}
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * сохранение ответов
	 */
	public function saveGeriatricsQuestionAnswer($data) {
		foreach ( $data['QuestionAnswer'] as $row ) {
			$GeriatricsQuestionAnswer_id = $this->getFirstResultFromQuery("
				select GeriatricsQuestionAnswer_id as \"GeriatricsQuestionAnswer_id\" 
				from GeriatricsQuestionAnswer 
				where GeriatricsQuestion_id = :GeriatricsQuestion_id
					and GeriatricsQuestionType_id = :GeriatricsQuestionType_id
			", array(
				'GeriatricsQuestion_id' => $data['GeriatricsQuestion_id'],
				'GeriatricsQuestionType_id' => $row[0]
			));

			$procedure = !$GeriatricsQuestionAnswer_id ? 'p_GeriatricsQuestionAnswer_ins' : 'p_GeriatricsQuestionAnswer_upd';
			
			$query = "
				select
					GeriatricsQuestionAnswer_id as \"GeriatricsQuestionAnswer_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$procedure}(
					GeriatricsQuestionAnswer_id := :GeriatricsQuestionAnswer_id,
					GeriatricsQuestion_id := :GeriatricsQuestion_id,
					GeriatricsQuestionType_id := :GeriatricsQuestionType_id,
					AnswerYesNoType_id := :AnswerYesNoType_id,
					pmUser_id := :pmUser_id
				)
			";
			
			$this->queryResult($query, array(
				'GeriatricsQuestionAnswer_id' => $GeriatricsQuestionAnswer_id ? $GeriatricsQuestionAnswer_id : null,
				'GeriatricsQuestion_id' => $data['GeriatricsQuestion_id'],
				'GeriatricsQuestionType_id' => $row[0],
				'AnswerYesNoType_id' => $row[1] == 1 ? 2 : 1,
				'pmUser_id' => $data['pmUser_id']
			));
		}
	}

	/**
	 * Удаление анкеты
	 */
	public function deleteOnkoProfile($data) {
		$tmp = $this->queryList("
			select
				GeriatricsQuestionAnswer_id as \"GeriatricsQuestionAnswer_id\"
			from GeriatricsQuestionAnswer
			where GeriatricsQuestion_id = ?
		", array($data['PersonOnkoProfile_id']));
		foreach ( $tmp as $pqa ) {
			$this->db->query("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_GeriatricsQuestionAnswer_del(
					GeriatricsQuestionAnswer_id := ?
				)
			", array($pqa));
		}
		
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_GeriatricsQuestion_del(
				GeriatricsQuestion_id := :PersonOnkoProfile_id
			)
		";
		
		return $this->queryResult($query, $data);
	}
}