<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * GeriatricsQuestion_model - гериатрия
 * сделано в режиме совместимости с OnkoCtrl
 */
class GeriatricsQuestion_model extends swModel {
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
			$filter .= " and ps.Person_SurName like :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};
		
		if (isset($data['FirName'])) {
			$filter .= " and ps.Person_FirName like :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};
		
		if (isset($data['SecName'])) {
			$filter .= " and ps.Person_SecName like :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		};

		if (isset($data['BirthDayRange'][0])) {
			$filter .= " and ps.Person_BirthDay  <= :BirthDayRangeBegin";
			$queryParams['BirthDayRangeBegin'] = $data['BirthDayRange'][0];
		}
		
		if (isset($data['BirthDayRange'][1])) {
			$filter .= " and ps.Person_BirthDay  <= :BirthDayRangeEnd";
			$queryParams['BirthDayRangeEnd'] = $data['BirthDayRange'][1];
		}
		
		if (isset($data['BirthDay'])) {
			$filter .= " and ps.Person_BirthDay = :BirthDay";
			$queryParams['BirthDay'] = $data['BirthDay'];
		}
		
		if (isset($data['PeriodRange'][0])) {
			$filter .= " and p.GeriatricsQuestion_setDate  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}
		
		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.GeriatricsQuestion_setDate  <= :PeriodRangeEnd";
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
				p.GeriatricsQuestion_id as id
				,msf.Person_Fin as MedPersonal_fin
				,p.Person_id
				,p.GeriatricsQuestion_id as PersonOnkoProfile_id
				,ps.Person_SurName SurName
				,ps.Person_FirName FirName
				,ps.Person_SecName SecName
				,rtrim(rtrim(isnull(ps.Person_Surname, '')) + ' ' + rtrim(isnull(ps.Person_Firname, '')) + ' ' + rtrim(isnull(ps.Person_Secname, ''))) fio
				,convert(Varchar, ps.Person_BirthDay, 104) as BirthDay
				,ps.Sex_id
				,substring(sex.Sex_Name, 1, 1) sex
				,isnull(uaddr.Address_Address,paddr.Address_Address) as Address
				,lpu.Lpu_id
				,lpu.Lpu_Nick
				,lr.LpuRegionType_Name + ' №' + lr.LpuRegion_Name uch
				,lr.LpuRegion_id
				,convert(Varchar, p.GeriatricsQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,p.MedStaffFact_id
				,anh.AgeNotHindrance_id
				,anh.AgeNotHindrance_Name
				-- end select
			FROM 
				-- from
				GeriatricsQuestion p (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = p.MedStaffFact_id
				inner join v_PersonState ps (nolock) on p.Person_id = ps.Person_id
				left join v_AgeNotHindrance anh (nolock) on anh.AgeNotHindrance_id = p.AgeNotHindrance_id
				left join v_Sex sex (nolock) on sex.Sex_id = ps.Sex_id
				outer apply (
					select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id
					from v_PersonCard pc (nolock)
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
				) as pcard
				left join v_Lpu lpu (nolock) on pcard.Lpu_id = lpu.Lpu_id
				left join v_LpuRegion lr (nolock) on pcard.LpuRegion_id = lr.LpuRegion_id
				left join v_Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
				left join v_Address paddr with (nolock) on ps.PAddress_id = paddr.Address_id
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
				 p.GeriatricsQuestion_id as PersonOnkoProfile_id
				,p.Person_id
				,p.MorbusGeriatrics_id
				,convert(Varchar, p.GeriatricsQuestion_setDate, 104) PersonOnkoProfile_DtBeg
				,msf.Lpu_id
				,l.Lpu_Nick
				,msf.MedStaffFact_id
				,msf.LpuBuilding_id
				,msf.LpuSection_id
				,p.GeriatricsQuestion_Other
				,p.GeriatricsQuestion_CountYes
				,p.AgeNotHindrance_id
			FROM GeriatricsQuestion p WITH (NOLOCK)
				left join v_MedStaffFact msf WITH (NOLOCK) on msf.MedStaffFact_id = p.MedStaffFact_id
				left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = msf.lpu_id
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
				q.GeriatricsQuestionType_id as OnkoQuestions_id,
				q.GeriatricsQuestionType_Name as OnkoQuestions_Name,
				q.GeriatricsQuestionType_Num as Questions_Num,
				isnull(p.AnswerYesNoType_id, 1) as val,
				at.AnswerType_Code,
				ac.AnswerClass_SysNick
			FROM GeriatricsQuestionType q WITH (NOLOCK)
				left join GeriatricsQuestionAnswer p WITH (NOLOCK) on 
					p.GeriatricsQuestionType_id = q.GeriatricsQuestionType_id and 
					p.GeriatricsQuestion_id = :GeriatricsQuestion_id
				left join v_AnswerType at with(nolock) on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac with(nolock) on ac.AnswerClass_id = q.AnswerClass_id
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
			declare
				@GeriatricsQuestion_id bigint = :GeriatricsQuestion_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@GeriatricsQuestion_id = @GeriatricsQuestion_id output,
				@Person_id = :Person_id,
				@GeriatricsQuestion_setDate = :GeriatricsQuestion_setDate,
				@MedStaffFact_id = :MedStaffFact_id,
				@GeriatricsQuestion_Other = :GeriatricsQuestion_Other,
				@GeriatricsQuestion_CountYes = :GeriatricsQuestion_CountYes,
				@AgeNotHindrance_id = :AgeNotHindrance_id,
				@MorbusGeriatrics_id = :MorbusGeriatrics_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @GeriatricsQuestion_id as GeriatricsQuestion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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

			// Пока закомментировал, т.к. может быть несколько анкет, связанных со спецификой, и брать нужно значение из последней анкеты
			/*if ( !empty($queryParams['MorbusGeriatrics_id']) ) {
				$this->db->query("
					update MorbusGeriatrics with (rowlock)
					set AgeNotHindrance_id = :AgeNotHindrance_id
					where MorbusGeriatrics_id = :MorbusGeriatrics_id
				", $queryParams);
			}*/

			// Проводим манипуляции, связанные с извещением
			// Статус пациента: хрупкий, прехрупкий

			if ( $data['AgeNotHindrance_id'] == 1 || $data['AgeNotHindrance_id'] == 2 ) {
				// Проверяем наличие активной записи регистра
				$checkPersonRegisterExists = $this->getFirstResultFromQuery("
					select top 1 PersonRegister_id
					from v_PersonRegister with (nolock)
					where Person_id = :Person_id
						and PersonRegisterType_id = 67
						and PersonRegisterOutCause_id is null
				", $data, true);

				if ( empty($checkPersonRegisterExists) ) {
					$data['Diag_id'] = $this->getFirstResultFromQuery("select top 1 Diag_id from v_Diag with (nolock) where DiagLevel_id = 4 and Diag_Code like 'R54%'", array(), true);

					// Проверяем наличие основного диагноза R54 в случаях пациента
					$checkR54DiagExists = $this->getFirstResultFromQuery("
						select top 1 EvnVizitPL_id as Evn_id from v_EvnVizitPL with (nolock) where Person_id = :Person_id and Diag_id = :Diag_id
						union all
						select top 1 EvnSection_id as Evn_id from v_EvnSection with (nolock) where Person_id = :Person_id and Diag_id = :Diag_id
					", $data, true);

					if ( !empty($checkR54DiagExists) ) {
						// Проверяем наличие извещения
						// Пока пропускаем эту проверку, т.к. все равно запись регистра создается автоматически
						/*$checkEvnNotifyGeriatricsExists = $this->getFirstResultFromQuery("
							select top 1 EvnNotifyGeriatrics_id
							from v_EvnNotifyGeriatrics with (nolock)
							where Person_id = :Person_id
								and NotifyStatus_id in (1, 2)
						", $data, true);*/

						if ( empty($checkEvnNotifyGeriatricsExists) ) {
							if ( !empty($data['MedStaffFact_id']) ) {
								$data['MedPersonal_iid'] = $this->getFirstResultFromQuery("
									select top 1 MedPersonal_id from v_MedStaffFact with (nolock) where MedStaffFact_id = :MedStaffFact_id
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
									declare @MorbusGeriatrics_id bigint = (
										select top 1 mg.MorbusGeriatrics_id
										from v_PersonRegister pr with (nolock)
											inner join v_MorbusGeriatrics mg on mg.Morbus_id = pr.Morbus_id
										where pr.Person_id = :Person_id
											and pr.PersonRegister_id = :PersonRegister_id
											and pr.PersonRegisterType_id = 67
										order by mg.MorbusGeriatrics_id desc
									);

									update GeriatricsQuestion with (rowlock)
									set MorbusGeriatrics_id = @MorbusGeriatrics_id
									where GeriatricsQuestion_id = :GeriatricsQuestion_id

									update MorbusGeriatrics with (rowlock)
									set AgeNotHindrance_id = :AgeNotHindrance_id
									where MorbusGeriatrics_id = @MorbusGeriatrics_id
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
				select GeriatricsQuestionAnswer_id 
				from GeriatricsQuestionAnswer (nolock) 
				where GeriatricsQuestion_id = :GeriatricsQuestion_id
					and GeriatricsQuestionType_id = :GeriatricsQuestionType_id
			", array(
				'GeriatricsQuestion_id' => $data['GeriatricsQuestion_id'],
				'GeriatricsQuestionType_id' => $row[0]
			));

			$procedure = !$GeriatricsQuestionAnswer_id ? 'p_GeriatricsQuestionAnswer_ins' : 'p_GeriatricsQuestionAnswer_upd';
			
			$query = "
				declare
					@GeriatricsQuestionAnswer_id bigint = :GeriatricsQuestionAnswer_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec {$procedure}
					@GeriatricsQuestionAnswer_id = @GeriatricsQuestionAnswer_id output,
					@GeriatricsQuestion_id = :GeriatricsQuestion_id,
					@GeriatricsQuestionType_id = :GeriatricsQuestionType_id,
					@AnswerYesNoType_id = :AnswerYesNoType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @GeriatricsQuestionAnswer_id as GeriatricsQuestionAnswer_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
		$tmp = $this->queryList("select GeriatricsQuestionAnswer_id from GeriatricsQuestionAnswer (nolock) where GeriatricsQuestion_id = ?", array($data['PersonOnkoProfile_id']));
		foreach ( $tmp as $pqa ) {
			$this->db->query("
				declare
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec p_GeriatricsQuestionAnswer_del
					@GeriatricsQuestionAnswer_id = ?,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", array($pqa));
		}
		
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_GeriatricsQuestion_del
				@GeriatricsQuestion_id = :PersonOnkoProfile_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		
		return $this->queryResult($query, $data);
	}
}