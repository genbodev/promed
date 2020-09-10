<?php

/**
 * TimetableQuote - модель для работы с квотами на прием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      12.12.2013
 */
class TimetableQuote_model extends swModel {

	private $debug = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		if($this->debug)$this->load->library('textlog', array('file' => 'TimetableQuote_model_ref140196.log'));
	}

	/**
	 * Получение списка квот
	 */
	function getQuotesList( $data ) {

		$queryParams = array();

		$filters = array();

		// Квоты только для своей ЛПУ
		$filters[] = ' ttq.Lpu_id = :Lpu_id';
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( isset($data['MedStaffFact_id']) ) {
			$filters[] = ' ttq.MedStaffFact_id = :MedStaffFact_id';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if ( isset($data['LpuSection_id']) ) {
			$filters[] = ' (ttq.LpuSection_id = :LpuSection_id or msf.LpuSection_id = :LpuSection_id)';
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( isset($data['LpuSectionProfile_id']) ) {
			$filters[] = ' (ttq.LpuSectionProfile_id = :LpuSectionProfile_id or msf_ls.LpuSectionProfile_id = :LpuSectionProfile_id or ls.LpuSectionProfile_id= :LpuSectionProfile_id )';
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( isset($data['LpuUnit_id']) ) {
			$filters[] = ' ttq.LpuUnit_id = :LpuUnit_id';
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( isset($data['MedService_id']) ) {
			$filters[] = ' ttq.MedService_id = :MedService_id';
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if ( isset($data['Resource_id']) ) {
			$filters[] = ' ttq.Resource_id = :Resource_id';
			$queryParams['Resource_id'] = $data['Resource_id'];
		}

		$queryParams['TimetableQuoteRule_Date'] = $data['TimetableQuoteRule_Date'];
		$filters[] = ' :TimetableQuoteRule_Date between ttq.TimetableQuoteRule_begDT and ttq.TimetableQuoteRule_endDT';

		if ( isset($data['TimetableQuoteType_id']) ) {
			$filters[] = ' ttq.TimetableQuoteType_id = :TimetableQuoteType_id';
			$queryParams['TimetableQuoteType_id'] = $data['TimetableQuoteType_id'];
		}
		$sql = "
			select 
				ttq.TimetableQuoteRule_id,
				ttq.TimetableQuoteType_id,
				ttq.LpuUnit_id,
				lu.LpuUnit_Name,
				ttq.LpuSectionProfile_id,
				ttq.LpuSection_id,
				ttq.MedStaffFact_id,
				ttqt.TimetableQuoteType_Name,
				case 
					when ttq.LpuSectionProfile_id is not null then 'Профиль: ' + lsp.LpuSectionProfile_Name
					when ttq.MedStaffFact_id is not null then 'Врач: ' + msf.Person_FIO + isnull(' [' + cast(msf_ls.LpuSection_Code as varchar) + '. ' + msf_ls.LpuSection_Name + ']', '')
					when ttq.LpuSection_id is not null then 'Отделение: ' + ls.LpuSection_Name
					when ttq.Resource_id is not null and ttq.MedService_id is not null then 'Ресурс: ' + res.Resource_Name + isnull(' [' + ms.MedService_Nick + ']', '')
					when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 'Служба: ' + isnull(ms2.MedService_Name, '') + isnull(' [' + uc.UslugaComplex_Name + ']', '')
				end
				as TimetableQuote_Object,
				convert(varchar, ttq.TimetableQuoteRule_begDT, 104) as TimetableQuoteRule_begDT,
				convert(varchar, ttq.TimetableQuoteRule_endDT, 104) as TimetableQuoteRule_endDT,
				substring(ttqs.Subject, 0, len(ttqs.Subject)) as TimetableQuoteSubjects
			from v_TimetableQuoteRule ttq with (nolock)
			left join v_LpuUnit lu with (nolock) on ttq.LpuUnit_id = lu.LpuUnit_id
			left join v_LpuSectionProfile lsp with (nolock) on ttq.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuSection ls with (nolock) on ttq.LpuSection_id = ls.LpuSection_id
			left join v_MedStaffFact msf with (nolock) on ttq.MedStaffFact_id = msf.MedStaffFact_id
			left join v_LpuSection msf_ls with (nolock) on msf.LpuSection_id = msf_ls.LpuSection_id
			left join v_Resource res with (nolock) on res.Resource_id = ttq.Resource_id
			left join v_MedService ms with (nolock) on ms.MedService_id = res.MedService_id
			left join v_MedService ms2 with (nolock) on ms2.MedService_id = ttq.MedService_id
			left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ttq.UslugaComplex_id
			outer apply (
				select
					(
						select
							coalesce(l.Lpu_Nick, et.ERTerr_Name, msf.Person_FIO, lr.LpuRegion_Name, ls.LpuSection_Name) + ' - ' + cast(ttqs2.TimetableQuote_Amount as varchar) + '|' as 'data()' 
						from v_TimetableQuoteRuleSubject ttqs2 with (nolock)
						left join v_Lpu l with (nolock) on ttqs2.Lpu_id = l.Lpu_id
						left join v_ERTerr et with (nolock) on ttqs2.ERTerr_id = et.ERTerr_id
						left join v_MedStaffFact msf with (nolock) on ttqs2.MedStaffFact_id = msf.MedStaffFact_id
						left join v_LpuSection ls with (nolock) on ttqs2.LpuSection_id = ls.LpuSection_id
						left join v_LpuRegion lr with (nolock) on ttqs2.LpuRegion_id = lr.LpuRegion_id
						where
							ttqs2.TimetableQuoteRule_id = ttqs1.TimetableQuoteRule_id
						order by l.Lpu_Nick
						for xml path('') 
					) as Subject
				from v_TimetableQuoteRuleSubject ttqs1 with (nolock)
				where ttqs1.TimetableQuoteRule_id = ttq.TimetableQuoteRule_id
			) ttqs
			left join TimetableQuoteType ttqt with (nolock) on ttq.TimetableQuoteType_id = ttqt.TimetableQuoteType_id " .
				ImplodeWhere($filters);

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка профилей для ЛПУ с фильтрами по структуре
	 */
	public function getLpuSectionProfileList( $data ) {

		$queryParams = array();

		if ( isset($data['LpuUnit_id']) ) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "ls.LpuUnit_id = :LpuUnit_id";
		}
		if ( isset($data['LpuSection_id']) ) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "ls.LpuSection_id = :LpuSection_id";
		}

		if ( isset($data['LpuSectionPid_id']) ) {
			$queryParams['LpuSectionPid_id'] = $data['LpuSectionPid_id'];
			$filters[] = "ls.LpuSection_id = :LpuSectionPid_id";
		}

		$filters[] = "ls.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$sql = "
			SELECT
				distinct
					ls.LpuSectionProfile_id,
					lsp.LpuSectionProfile_Code,
					rtrim(lsp.LpuSectionProfile_Name) as LpuSectionProfile_Name
			FROM v_LpuSection ls with (nolock)
			left join v_LpuSectionProfile lsp with (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
		
			" . ImplodeWhere($filters) . "
			ORDER BY rtrim(lsp.LpuSectionProfile_Name), ls.LpuSectionProfile_id
		";
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	} //end getLpuSectionProfileList()

	/**
	 * Проверки при сохранении
	 */
	public function checkSaveTimetableQuoteRuleSubject( $data ) {

		$subjects = json_decode($data['rule_subjects']);
		// Проверяем, что $subjects не пустой 
		if ( count($subjects) == 0 ) {
			return 'Не задано субъектов квоты';
		}

		// Проверяем что уже нет правила квоты с этими же данными, пересекающихся с сохраняемым по срокам
		$filters = array();

		if ( isset($data['TimetableQuoteRule_id']) ) {
			$queryParams['TimetableQuoteRule_id'] = $data['TimetableQuoteRule_id'];
			$filters[] = " ttq.TimetableQuoteRule_id != :TimetableQuoteRule_id ";
		}

		if ( !empty($data['Lpu_id']) ) {
			$filters[] = " ttq.Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filters[] = " ttq.LpuUnit_id = :LpuUnit_id ";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filters[] = " ttq.LpuSectionProfile_id = :LpuSectionProfile_id ";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filters[] = " ttq.LpuSection_id = :LpuSection_id ";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['MedStaffFact_id']) ) {
			$filters[] = " ttq.MedStaffFact_id = :MedStaffFact_id ";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if ( !empty($data['MedService_id']) ) {
			$filters[] = " ttq.MedService_id = :MedService_id ";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if ( !empty($data['Resource_id']) ) {
			$filters[] = " ttq.Resource_id = :Resource_id ";
			$queryParams['Resource_id'] = $data['Resource_id'];
		}

		if ( !empty($data['UslugaComplex_id']) ) {
			$filters[] = " ttq.UslugaComplex_id = :UslugaComplex_id ";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if ( $data['TimetableQuoteType_id'] < 3 ) {
			$filters[] = " ttq.TimetableQuoteType_id in (1, 2) ";
		} else {
			$filters[] = " ttq.TimetableQuoteType_id in (3, 4) ";
		}

		$filters[] = " (:TimetableQuoteRule_begDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT or :TimetableQuoteRule_endDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT)";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		$sql = "
			select
				count(ttq.TimetableQuoteRule_id) as cnt
			from v_TimetableQuoteRule ttq with (nolock)
			" .
				ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams);
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( $res[0]['cnt'] > 0 ) {
				return 'Уже есть правило квоты с этими же данными, пересекающееся с сохраняемым по срокам';
			}
		}

		if ( $data['TimetableQuoteType_id'] < 3 ) {
			// Проверки для внешних квот
			$subject_list = array();
			foreach ( $subjects as $subject ) {
				// Проверяем, что все субъекты это либо ЛПУ либо территория
				if ( empty($subject->Lpu_id) && empty($subject->ERTerr_id) ) {
					return 'В записи не задан субъект квоты';
				}

				// Проверяем, что у всех субъектов есть числовое значение квоты
				if ( !isset($subject->TimetableQuote_Amount) ) {
					return 'Не задано количество квот у субъекта';
				}
				// Проверяем, что нет повторяющихся субъектов квоты
				$subject_item = serialize(array($subject->Lpu_id, $subject->ERTerr_id, $subject->PayType_id));
				if ( in_array($subject_item, $subject_list) ) {
					return 'Нельзя указать два раза один и тот же субъект';
				}

				if (!empty($data['Resource_id'])) {
					if (!empty($subject->Lpu_id)) {
						if ($this->getTTRRecordsCountByLpu($subject->Lpu_id, $data) > $subject->TimetableQuote_Amount) {
							return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject->Lpu_Nick);
						}
					}
					if (!empty($subject->ERTerr_id)) {
						if ($this->getTTRRecordsCountByTerr($subject->ERTerr_id, $data) > $subject->TimetableQuote_Amount) {
							return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject->ERTerr_Name);
						}
					}
				} else {
					if (!empty($subject->Lpu_id)) {
						if ($this->getTTGRecordsCountByLpu($subject->Lpu_id, $data) > $subject->TimetableQuote_Amount) {
							return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject->Lpu_Nick);
						}
					}
					if (!empty($subject->ERTerr_id)) {
						if ($this->getTTGRecordsCountByTerr($subject->ERTerr_id, $data) > $subject->TimetableQuote_Amount) {
							return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject->ERTerr_Name);
						}
					}
				}
				$subject_list[] = $subject_item;
			}
		} else {
			// Проверки для внутренних квот
			$subject_list = array();

			foreach ( $subjects as $subject ) {
				// Проверяем, что все субъекты не пустые
				if (
					empty($subject->LpuSection_id) ||
					($subject->SubjectType_id == 2 && empty($subject->MedStaffFact_id)) ||
					($subject->SubjectType_id == 3 && empty($subject->LpuRegion_id))
				) {
					return 'В записи не задан субъект квоты';
				}

				// Проверяем, что у всех субъектов есть числовое значение квоты
				if ( !isset($subject->TimetableQuote_Amount) ) {
					return 'Не задано количество квот у субъекта';
				}
				// Проверяем, что нет повторяющихся субъектов квоты
				// т.к. сейчас (пока на Уфе) несколько полей, повтором считать будем только полное совпадение по всем полям
				// для простоты рассчёта переводим в строку
				
				$subject_item = serialize(array($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id, $subject->PayType_id));
				if ( in_array($subject_item, $subject_list) ) {
					return 'Нельзя указать два раза один и тот же субъект';
				}
				
				$subject_name = 
					!empty($subject->MedStaffFact_id) ? $subject->MedStaffFact_FIO :
					(!empty($subject->LpuRegion_id) ? $subject->LpuRegion_Name : 
					$subject->LpuSection_Name);

				if ( ! empty($data['Resource_id'])) {
					if ( $this->getTTRRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id, 
							$this->getRegionNick()=='kz' ? $data : array_merge($data, array('PayType_id'=>$subject->PayType_id))
						) > $subject->TimetableQuote_Amount ) {
						return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject_name);
					}
				} else if( ! empty($data['UslugaComplex_id'])) {
					if ( $this->getTTMSRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id, 
							$this->getRegionNick()=='kz' ? $data : array_merge($data, array('PayType_id'=>$subject->PayType_id))
						) > $subject->TimetableQuote_Amount ) {
						return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject_name);
					}
				} else {
					if ($this->getTTGRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id, 
							$this->getRegionNick()=='kz' ? $data : array_merge($data, array('PayType_id'=>$subject->PayType_id))
					) > $subject->TimetableQuote_Amount) {
						return 'Невозможно создать правило, лимит квоты уже превышен для субъекта ' . toAnsi($subject_name);
					}
				}
				$subject_list[] = $subject_item;
			}
		}

		/*
		  // Проверяем что нет правил с такими же субъектами, пересекающихся с сохраняемым по срокам
		  $filters = array();

		  if (isset($data['TimetableQuoteRule_id'])) {
		  $queryParams['TimetableQuoteRule_id'] = $data['TimetableQuoteRule_id'];
		  $filters[] = " ttqs.TimetableQuoteRule_id != :TimetableQuoteRule_id ";
		  }
		  // Для пустых массивов добавляем пустые значения чтобы implode не ломался
		  if (count($lpus) == 0) { $lpus[] = 0;}
		  if (count($terrs) == 0) { $terrs[] = 0; }
		  $filters[] = " (:TimetableQuoteRule_begDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT or :TimetableQuoteRule_endDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT) and (ttqs.Lpu_id in (".implode(',', $lpus).") or ttqs.ERTerr_id in (".implode(',', $terrs)."))";
		  $queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		  $queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		  if ( !empty($data['Lpu_id']) ) {
		  $filters[] = " ttq.Lpu_id = :Lpu_id ";
		  $queryParams['Lpu_id'] = $data['Lpu_id'];
		  }

		  if ( !empty($data['LpuUnit_id']) ) {
		  $filters[] = " ttq.LpuUnit_id = :LpuUnit_id ";
		  $queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		  }

		  if ( !empty($data['LpuSectionProfile_id']) ) {
		  $filters[] = " ttq.LpuSectionProfile_id = :LpuSectionProfile_id ";
		  $queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		  }

		  if ( !empty($data['LpuSection_id']) ) {
		  $filters[] = " ttq.LpuSection_id = :LpuSection_id ";
		  $queryParams['LpuSection_id'] = $data['LpuSection_id'];
		  }

		  if ( !empty($data['MedStaffFact_id']) ) {
		  $filters[] = " ttq.MedStaffFact_id = :MedStaffFact_id ";
		  $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		  }

		  $sql = "
		  select
		  count(ttq.TimetableQuoteRule_id) as cnt
		  from v_TimetableQuoteRuleSubject ttqs with (nolock)
		  left join v_TimetableQuoteRule ttq with (nolock) on ttqs.TimetableQuoteRule_id = ttq.TimetableQuoteRule_id
		  ".
		  ImplodeWhere($filters);
		  //echo getDebugSQL($sql, $queryParams);
		  $result = $this->db->query($sql, $queryParams);
		  if (is_object($result)) {
		  $res = $result->result('array');
		  if ($res[0]['cnt'] > 0) {
		  return 'Уже есть квота с такими же параметрами для одного из субъектов на заданные даты';
		  }
		  } */

		return;
	}

	/**
	 * Подсчет количества внешних бирок, занятых из заданного ЛПУ
	 */
	function getTTGRecordsCountByLpu( $Lpu_id, $data ) {
		$filter = " select TimetableGraf_id from v_EvnDirection_all with (nolock) where Lpu_id = {$Lpu_id}";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых из заданного ЛПУ
	 */
	function getTTRRecordsCountByLpu( $Lpu_id, $data ) {
		$filter = " select TimetableResource_id from v_EvnDirection_all with (nolock) where Lpu_id = {$Lpu_id}";
		return $this->getTTRRecordsCount($data, $filter);
	}

	/**
	 * @param $Lpu_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByLpu( $Lpu_id, $data ) {
		$filter = " select TimeTableMedService_id from v_EvnDirection_all with (nolock) where Lpu_id = {$Lpu_id}";
		return $this->getTTMSRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых с заданной территории(списка территорий)
	 */
	function getTTGRecordsCountByTerr( $ERTerr_id, $data ) {
		if ( is_array($ERTerr_id) ) {
			if ( count($ERTerr_id) == 0 ) { // такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}


		$filter = " select TimetableGraf_id from v_EvnDirection with (nolock) where Lpu_id in (
			select Lpu_id
			from v_LpuUnit_ER lu with (nolock)
			left join Address a with(nolock) on a.Address_id = lu.Address_id
			inner join v_ERTerr Terr with (nolock) on
			(
				((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
				((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
				((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
				((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
				((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
			) and {$terr_cond}
		)";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых с заданной территории(списка территорий)
	 */
	function getTTRRecordsCountByTerr( $ERTerr_id, $data ) {
		if ( is_array($ERTerr_id) ) {
			if ( count($ERTerr_id) == 0 ) { // такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}


		$filter = " select TimetableResource_id from v_EvnDirection with (nolock) where Lpu_id in (
			select Lpu_id
			from v_LpuUnit_ER lu with (nolock)
			left join Address a with(nolock) on a.Address_id = lu.Address_id
			inner join v_ERTerr Terr with (nolock) on
			(
				((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
				((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
				((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
				((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
				((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
			) and {$terr_cond}
		)";
		return $this->getTTGRecordsCount($data, $filter);
	}


	/**
	 * @param $ERTerr_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByTerr( $ERTerr_id, $data ) {
		if ( is_array($ERTerr_id) ) {
			if ( count($ERTerr_id) == 0 ) { // такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}


		$filter = " select TimeTableMedService_id from v_EvnDirection with (nolock) where Lpu_id in (
			select Lpu_id
			from v_LpuUnit_ER lu with (nolock)
			left join Address a with(nolock) on a.Address_id = lu.Address_id
			inner join v_ERTerr Terr with (nolock) on
			(
				((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
				((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
				((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
				((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
				((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
			) and {$terr_cond}
		)";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества занятых внешних бирок
	 */
	function getTTGRecordsCount( $data, $inbox_filter ) {
		$filters = array(" ttg.Person_id is not null and TimetableType_id = 5 "); // только внешние бирки
		$queryParams = array();

		if ( !empty($data['Lpu_id']) ) {
			$filters[] = " msf.Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filters[] = " ls.LpuUnit_id = :LpuUnit_id ";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filters[] = " ls.LpuSectionProfile_id = :LpuSectionProfile_id ";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filters[] = " msf.LpuSection_id = :LpuSection_id ";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['MedStaffFact_id']) ) {
			$filters[] = " ttg.MedStaffFact_id = :MedStaffFact_id ";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		
		if( !empty($data['PayType_id']) ) {
			$filters[] = " ed.PayType_id = :PayType_id ";
			$queryParams['PayType_id'] = $data['PayType_id'];
		}

		$filters[] = " cast(ttg.TimetableGraf_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		$filters[] = " ttg.TimetableGraf_id in ( {$inbox_filter} ) ";

		// При подсчете записей не считаем записи врача к самому себе
		$filters[] = " msf.MedPersonal_id != (select top 1 pu.pmUser_MedPersonal_id from v_pmUser pu with (nolock) where ttg.pmUser_updId = pu.pmUser_id ) ";

		$sql = "
			select
				count(*) as cnt
			from v_TimeTableGraf_lite ttg with (nolock)
			left join v_EvnDirection_all ed with (nolock) on ttg.EvnDirection_id = ed.EvnDirection_id
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
			left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
			" .
				ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams); die();
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0]['cnt'];
		} else {
			return 0;
		}
	}

	/**
	 * Подсчет количества занятых внешних бирок
	 */
	function getTTRRecordsCount( $data, $inbox_filter ) {
		$filters = array(" ttr.Person_id is not null and TimetableType_id = 5 "); // только внешние бирки
		$queryParams = array();

		if ( !empty($data['Resource_id']) ) {
			$filters[] = " ttr.Resource_id = :Resource_id ";
			$queryParams['Resource_id'] = $data['Resource_id'];
		} else {
			return 0;
		}

		$filters[] = " cast(ttr.TimetableResource_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		$filters[] = " ttr.TimetableResource_id in ( {$inbox_filter} ) ";

		$sql = "
			select
				count(*) as cnt
			from v_TimetableResource_lite ttr with (nolock)
			" .
				ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams);
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0]['cnt'];
		} else {
			return 0;
		}
	}


	/**
	 * @param $data
	 * @param $inbox_filter
	 * @return int
	 */
	function getTTMSRecordsCount( $data, $inbox_filter ) {
		$filters = array(" ttms.Person_id is not null and TimetableType_id = 5 "); // только внешние бирки
		$queryParams = array();

		if ( ! empty($data['MedService_id']) ) {
			$filters[] = " ttms.MedService_id = :MedService_id ";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if ( ! empty($data['UslugaComplex_id']) ) {
			$filters[] = " ttms.UslugaComplexMedService_id = :UslugaComplex_id ";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		$filters[] = " cast(ttms.TimetableMedService_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		$filters[] = " ttms.TimeTableMedService_id in ( {$inbox_filter} ) ";

		$sql = "
			select
				count(*) as cnt
			from v_TimetableResource_lite ttms with (nolock)
			" .
			ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams);
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0]['cnt'];
		} else {
			return 0;
		}
	}

	/**
	 * Подсчет количества занятых врачом бирок для внутреннего квотирования
	 */
	function getTTGRecordsCountByMedStaffFact( $MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data ) {
		$filters = array(" ttg.Person_id is not null and ttg.TimetableType_id = 5 "); // считаем все бирки
		$queryParams = array();

		if ( !empty($data['Lpu_id']) ) {
			$filters[] = " msf.Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filters[] = " ls.LpuUnit_id = :LpuUnit_id ";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$filters[] = " ls.LpuSectionProfile_id = :LpuSectionProfile_id ";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['LpuSection_id']) ) {
			$filters[] = " msf.LpuSection_id = :LpuSection_id ";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['MedStaffFact_id']) ) {
			$filters[] = " ttg.MedStaffFact_id = :MedStaffFact_id ";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		$filters[] = " cast(ttg.TimetableGraf_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		if (!empty($MedStaffFact_id)) {
			// Запись от этого врача
			if ( empty($data['From_MedStaffFact_id']) ) { 
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid ) ";
				$filters[] = " ed.MedStaffFact_id = :MedStaffFact_sid "; //#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
			} else { 
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid or msf.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2) ) ";
				$filters[] = " (ed.MedStaffFact_id = :MedStaffFact_sid or ed.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2)) ";//#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
				$queryParams['MedStaffFact_sid2'] = $data['From_MedStaffFact_id'];
			}
		} elseif (!empty($LpuRegion_id)) {
			$filters[] = " ed.MedPersonal_id in (
				select MedPersonal_id
				from v_MedStaffRegion msr with (nolock) 
				where msr.LpuRegion_id = :LpuRegion_sid 
				and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= @curDate)
				and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > @curDate)
			) ";
			$queryParams['LpuRegion_sid'] = $LpuRegion_id;
		} elseif (!empty($LpuSection_id)) {
			$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.LpuSection_id = :LpuSection_sid ) ";
			$queryParams['LpuSection_sid'] = $LpuSection_id;
		}
		
		if( !empty($data['PayType_id']) ) {
			$filters[] = " ed.PayType_id = :PayType_id ";
			$queryParams['PayType_id'] = $data['PayType_id'];
		}

		// При подсчете записей не считаем записи врача к самому себе
		//$filters[] = " msf.MedPersonal_id != ed.MedPersonal_id "; //#117075

		$sql = "
			declare
				@curDate datetime = dbo.tzGetDate();
				
			select
				count(*) as cnt
			from 
				v_TimeTableGraf_lite ttg with (nolock)
				left join v_EvnDirection_all ed with (nolock) on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
			" .
				ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams);

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0]['cnt'];
		} else {
			return 0;
		}
	}

	/**
	 * Подсчет количества занятых врачом бирок для внутреннего квотирования
	 */
	function getTTRRecordsCountByMedStaffFact( $MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data ) {

//		if($this->debug)$this->textlog->add('start getTTRRecordsCountByMedStaffFact');

		$filters = array(" ttr.Person_id is not null and ttr.TimetableType_id = 5 "); // считаем все бирки
		$queryParams = array();

		if ( !empty($data['Resource_id']) ) {
//			if($this->debug)$this->textlog->add(' ! empty Resource_id');
			$filters[] = " ttr.Resource_id = :Resource_id ";
			$queryParams['Resource_id'] = $data['Resource_id'];
		} else {
//			if($this->debug)$this->textlog->add('empty Resource_id');

//			if($this->debug)$this->textlog->add('stop1 getTTRRecordsCountByMedStaffFact');
			return 0;
		}

		$filters[] = " cast(ttr.TimetableResource_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		if ( ! empty($MedStaffFact_id)) {
//			if($this->debug)$this->textlog->add(' ! empty $MedStaffFact_id');

			// Запись от этого врача
			if (empty($data['From_MedStaffFact_id']) ) {
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid ) ";
				$filters[] = " ed.MedStaffFact_id = :MedStaffFact_sid ";//#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
			} else { 
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid or msf.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2) ) ";
				$filters[] = " (ed.MedStaffFact_id = :MedStaffFact_sid or ed.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2)) ";//#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
				$queryParams['MedStaffFact_sid2'] = $data['From_MedStaffFact_id'];
			}
		} elseif ( ! empty($LpuRegion_id)) {
//			if($this->debug)$this->textlog->add(' ! empty $LpuRegion_id');

			$filters[] = " ed.MedPersonal_id in (
				select MedPersonal_id
				from v_MedStaffRegion msr with (nolock) 
				where msr.LpuRegion_id = :LpuRegion_sid 
				and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= @curDate)
				and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > @curDate)
			) ";
			$queryParams['LpuRegion_sid'] = $LpuRegion_id;
		} elseif ( ! empty($LpuSection_id)) {

//			if($this->debug)$this->textlog->add(' ! empty $LpuSection_id');

			$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.LpuSection_id = :LpuSection_sid ) ";
			$queryParams['LpuSection_sid'] = $LpuSection_id;
		}
		
		if( !empty($data['PayType_id']) ) {
			$filters[] = " ed.PayType_id = :PayType_id ";
			$queryParams['PayType_id'] = $data['PayType_id'];
		}


//		if($this->debug)$this->textlog->add('$filters = '.serialize($filters));

		$sql = "
			declare
				@curDate datetime = dbo.tzGetDate();
			select
				count(*) as cnt
			from v_TimetableResource_lite ttr with (nolock)
			left join v_EvnDirection_all ed with (nolock) on ttr.EvnDirection_id = ed.EvnDirection_id
			" .
				ImplodeWhere($filters);
		//echo getDebugSQL($sql, $queryParams);

//		if($this->debug)$this->textlog->add('sql = '.getDebugSQL($sql, $queryParams));

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {

//			if($this->debug)$this->textlog->add('is_object $result');

			$res = $result->result('array');

//			if($this->debug)$this->textlog->add('$res = '.serialize($res));

//			if($this->debug)$this->textlog->add('stop2 getTTRRecordsCountByMedStaffFact');

			return $res[0]['cnt'];
		} else {
//			if($this->debug)$this->textlog->add(' ! is_object $result');

//			if($this->debug)$this->textlog->add('stop3 getTTRRecordsCountByMedStaffFact');
			return 0;
		}
	}

	/**
	 * @param $MedStaffFact_id
	 * @param $LpuSection_id
	 * @param $LpuRegion_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByMedStaffFact($MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data) {
		$filters = array(" ttms.Person_id is not null and ttms.TimetableType_id = 5 "); // считаем все бирки
		$queryParams = array();

		if ( ! empty($data['MedService_id']) && empty($data['UslugaComplex_id'])) {
			$filters[] = " ttms.MedService_id = :MedService_id ";
			//$filters[] = " ed.MedService_id = :MedService_id ";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if ( ! empty($data['UslugaComplex_id']) && empty($data['MedService_id'])) {
			$filters[] = " ttms.UslugaComplexMedService_id = :UslugaComplex_id ";
			//$filters[] = " ed.UslugaComplex_did = :UslugaComplex_id ";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		if(!empty($data['UslugaComplex_id']) && !empty($data['MedService_id'])){
			$filters[] = "CASE WHEN ttms.MedService_id is not null THEN ttms.MedService_id
					ELSE ed.MedService_id
				END = :MedService_id
				AND
				CASE WHEN ttms.MedService_id is not null THEN ttms.UslugaComplexMedService_id
					ELSE ed.UslugaComplex_did
				END = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		$filters[] = " cast(ttms.TimeTableMedService_begTime as date) between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT ";
		$queryParams['TimetableQuoteRule_begDT'] = $data['TimetableQuoteRule_begDT'];
		$queryParams['TimetableQuoteRule_endDT'] = $data['TimetableQuoteRule_endDT'];

		if (!empty($MedStaffFact_id)) {
			// Запись от этого врача
			if ( empty($data['From_MedStaffFact_id']) ) {
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid ) ";
				$filters[] = " ed.MedStaffFact_id = :MedStaffFact_sid ";//#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
			} else {
				//$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.MedStaffFact_id = :MedStaffFact_sid or msf.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2) ) ";
				$filters[] = " (ed.MedStaffFact_id = :MedStaffFact_sid or ed.MedStaffFact_id in (select MedStaffFact_sid from MedStaffFactLink (nolock) where MedStaffFact_id = :MedStaffFact_sid2)) ";//#117075
				$queryParams['MedStaffFact_sid'] = $MedStaffFact_id;
				$queryParams['MedStaffFact_sid2'] = $data['From_MedStaffFact_id'];
			}
		} elseif (!empty($LpuRegion_id)) {
			$filters[] = " ed.MedPersonal_id in (
				select MedPersonal_id
				from v_MedStaffRegion msr with (nolock) 
				where msr.LpuRegion_id = :LpuRegion_sid 
				and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= @curDate)
				and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > @curDate)
			) ";
			$queryParams['LpuRegion_sid'] = $LpuRegion_id;
		} elseif (!empty($LpuSection_id)) {
			$filters[] = " ed.MedPersonal_id in ( select MedPersonal_id from v_MedStaffFact msf with (nolock) where msf.LpuSection_id = :LpuSection_sid ) ";
			$queryParams['LpuSection_sid'] = $LpuSection_id;
		}
		
		if(!empty($data['PayType_id'])) {
			$filters[] = " ed.PayType_id = :PayType_id ";
			$queryParams['PayType_id'] = $data['PayType_id'];
		}

		$sql = "
			declare
				@curDate datetime = dbo.tzGetDate();
				
			select
				count(*) as cnt
			from 
				v_TimeTableMedService_lite ttms with (nolock)
				left join v_EvnDirection_all ed with (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
			" .
			ImplodeWhere($filters);

		//echo getDebugSQL($sql, $queryParams);die();
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			return $res[0]['cnt'];
		} else {
			return 0;
		}
	}

	/**
	 * Сохранение правила квоты
	 */
	public function saveQuoteRule( $data ) {

		$err = $this->checkSaveTimetableQuoteRuleSubject($data);
		if ( isset($err) ) {
			return array(
				'Error_Msg' => $err
			);
		}


		if ( isset($data['TimetableQuoteRule_id']) ) {
			$proc = "p_TimetableQuoteRule_upd";
		} else {
			$proc = "p_TimetableQuoteRule_ins";
		}

		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@TimetableQuoteRule_id bigint = :TimetableQuoteRule_id;
		
			exec {$proc}
				@TimetableQuoteRule_id = @TimetableQuoteRule_id output,
				@TimetableQuoteType_id = :TimetableQuoteType_id,
				@Lpu_id = :Lpu_id,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSection_id = :LpuSection_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedService_id = :MedService_id,
				@Resource_id = :Resource_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@TimetableQuoteRule_begDT = :TimetableQuoteRule_begDT,
				@TimetableQuoteRule_endDT = :TimetableQuoteRule_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @TimetableQuoteRule_id as TimetableQuoteRule_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
		//echo getDebugSQL(
			$sql, array(
				'TimetableQuoteRule_id' => $data['TimetableQuoteRule_id'],
				'TimetableQuoteType_id' => $data['TimetableQuoteType_id'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuUnit_id' => $data['LpuUnit_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'MedService_id' => $data['MedService_id'],
				'Resource_id' => $data['Resource_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'TimetableQuoteRule_begDT' => $data['TimetableQuoteRule_begDT'],
				'TimetableQuoteRule_endDT' => $data['TimetableQuoteRule_endDT'],
				'pmUser_id' => $data['pmUser_id']
			)
		);

		if ( is_object($res) ) {
			$result = $res->result('array');

			if ( !isset($result[0]['Error_Msg']) && isset($result[0]['TimetableQuoteRule_id']) ) {
				// Сохраняем субъектов квоты
				if ( isset($data['TimetableQuoteRule_id']) ) {
					//если редактируем существующее правило, то удаляем старых субъектов
					$err = $this->deleteTimetableQuoteRuleSubject($data['TimetableQuoteRule_id']);

					if ( isset($err) ) {
						return array(
							'Error_Msg' => $err
						);
					}
					$err = $this->addTimetableQuoteRuleSubject($data['TimetableQuoteRule_id'], json_decode($data['rule_subjects']), $data['pmUser_id']);
					return array(
						'Error_Msg' => $err
					);
				} else {
					$err = $this->addTimetableQuoteRuleSubject($result[0]['TimetableQuoteRule_id'], json_decode($data['rule_subjects']), $data['pmUser_id']);
					return array(
						'Error_Msg' => $err
					);
				}
			} else {
				return array(
					'Error_Msg' => $result[0]['Error_Msg']
				);
			}
		}
	} //end saveQuoteRule()

	/**
	 * Удаление субъектов у заданного правила
	 */
	function deleteTimetableQuoteRuleSubject( $TimetableQuoteRule_id ) {

		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);
		
			exec p_TimetableQuoteRule_delSubjects
				@TimetableQuoteRule_id = :TimetableQuoteRule_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'TimetableQuoteRule_id' => $TimetableQuoteRule_id
				)
		);

		if ( is_object($res) ) {
			$result = $res->result('array');
			return $result[0]['Error_Msg'];
		}
	}

	/**
	 * Добавление субъектов заданному правилу
	 */
	function addTimetableQuoteRuleSubject( $TimetableQuoteRule_id, $subjects, $pmuser_id ) {
		foreach ( $subjects as $subject ) {
			$sql = "
				declare
					@ErrCode bigint,
					@ErrMsg varchar(4000),
					@TimetableQuoteRuleSubject_id bigint;
			
				exec p_TimetableQuoteRuleSubject_ins
					@TimetableQuoteRuleSubject_id = @TimetableQuoteRuleSubject_id output,
					@TimetableQuoteRule_id = :TimetableQuoteRule_id,
					@ERTerr_id = :ERTerr_id,
					@Lpu_id = :Lpu_id,
					@LpuSection_id = :LpuSection_id,
					@LpuRegion_id = :LpuRegion_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@TimetableQuote_Amount = :TimetableQuote_Amount,
					@PayType_id = :PayType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$res = $this->db->query(
					$sql, array(
				'TimetableQuoteRule_id' => $TimetableQuoteRule_id,
				'ERTerr_id' => !empty($subject->ERTerr_id) ? $subject->ERTerr_id : null,
				'Lpu_id' => !empty($subject->Lpu_id) ? $subject->Lpu_id : null,
				'LpuSection_id' => !empty($subject->LpuSection_id) ? $subject->LpuSection_id : null,
				'LpuRegion_id' => !empty($subject->LpuRegion_id) ? $subject->LpuRegion_id : null,
				'MedStaffFact_id' => !empty($subject->MedStaffFact_id) ? $subject->MedStaffFact_id : null,
				'PayType_id' => !empty($subject->PayType_id) ? $subject->PayType_id : null,
				'TimetableQuote_Amount' => $subject->TimetableQuote_Amount,
				'pmUser_id' => $pmuser_id
					)
			);

			if ( is_object($res) ) {
				$result = $res->result('array');
				if ( isset($result[0]['Error_Msg']) ) {
					return $result[0]['Error_Msg'];
				}
			} else {
				return false;
			}
		}
	} // end addTimetableQuoteRuleSubject()

	/**
	 * Загрузка правила квоты
	 */
	function getQuoteRule( $data ) {

		$sql = "
			select TOP 1
				ttq.TimetableQuoteRule_id,
				ttq.TimetableQuoteType_id,
				ttq.LpuUnit_id,
				ttq.LpuSectionProfile_id,
				ttq.LpuSection_id,
				ttq.MedStaffFact_id,
				ttq.MedService_id,
				ttq.Resource_id,
				ttq.UslugaComplex_id,
				ttqt.TimetableQuoteType_Name,
				ttq.TimetableQuoteRule_begDT,
				ttq.TimetableQuoteRule_endDT,
				case
					when ttq.Resource_id is not null and ttq.MedService_id is not null then 4
					when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 5
					when ttq.MedStaffFact_id is not null then 3
					when ttq.LpuSection_id is not null then 2
					else 1
				end as QuoteType_id
			from v_TimetableQuoteRule ttq with (nolock)
			left join v_LpuSectionProfile lsp with (nolock) on ttq.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuSection ls with (nolock) on ttq.LpuSection_id = ls.LpuSection_id
			left join v_MedStaffFact msf with (nolock) on ttq.MedStaffFact_id = msf.MedStaffFact_id
			left join v_TimetableQuoteType ttqt with (nolock) on ttq.TimetableQuoteType_id = ttqt.TimetableQuoteType_id 
			where TimetableQuoteRule_id = :TimetableQuoteRule_id";

		$queryParams = array(
			'TimetableQuoteRule_id' => $data['TimetableQuoteRule_id']
		);

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	} // end getQuoteRule()

	/**
	 * Загрузка субъектов квоты
	 */
	function getQuoteRuleSubjects( $data ) {

		$sql = "
			select
				tqrs.ERTerr_id, 
				tqrs.Lpu_id,
				tqrs.MedStaffFact_id,
				tqrs.PayType_id,
				PT.PayType_Name,
				TimetableQuote_Amount,
				et.ERTerr_Name,
				l.Lpu_Nick,
				msf.Person_FIO as MedStaffFact_FIO,
				case
					when tqrs.ERTerr_id is not null then 2
					when tqrs.MedStaffFact_id is not null then 2
					when tqrs.LpuRegion_id is not null then 3
					else 1
				end as SubjectType_id,
				ls.LpuSection_id,
				ls.LpuSection_Name,
				lr.LpuRegion_id,
				lr.LpuRegion_Name
			from v_TimetableQuoteRuleSubject tqrs with (nolock)
			left join v_ERTerr et with (nolock) on et.ERTerr_id = tqrs.ERTerr_id
			left join v_Lpu l with (nolock) on l.Lpu_id = tqrs.Lpu_id
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = tqrs.MedStaffFact_id
			left join v_LpuSection ls with (nolock) on ls.LpuSection_id = tqrs.LpuSection_id
			left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = tqrs.LpuRegion_id
			left join v_PayType PT with (nolock) on PT.PayType_id=tqrs.PayType_id
			where
				TimetableQuoteRule_id = :TimetableQuoteRule_id
		";

		$queryParams = array(
			'TimetableQuoteRule_id' => $data['TimetableQuoteRule_id']
		);

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	} // end getQuoteRuleSubjects()

	/**
	 * Удаление правила квоты
	 */
	public function deleteQuoteRule( $data ) {

		$err = $this->deleteTimetableQuoteRuleSubject($data['TimetableQuoteRule_id']);

		if ( isset($err) ) {
			return array(
				'Error_Msg' => $err
			);
		}

		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@TimetableQuoteRule_id bigint = :TimetableQuoteRule_id;
		
			exec p_TimetableQuoteRule_del
				@TimetableQuoteRule_id = @TimetableQuoteRule_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'TimetableQuoteRule_id' => $data['TimetableQuoteRule_id']
				)
		);

		if ( is_object($res) ) {
			$result = $res->result('array');
			return array(
				'Error_Msg' => $result[0]['Error_Msg']
			);
		} else {
			return false;
		}
	} //end deleteQuoteRule()

	/**
	 * Проверка, что запись на текущую бирку не нарушит никаких правил квоты
	 */
	function checkTimetableQuote( $data, $record_data, $mode = 'ttg' ) {

//		if($this->debug)$this->textlog->add('start checkTimetableQuote');
//		if($this->debug)$this->textlog->add('$data = '.serialize($data));
//		if($this->debug)$this->textlog->add('$record_data = '.serialize($record_data));
//		if($this->debug)$this->textlog->add('$mode = '.serialize($mode));

		$filters = array();
		$queryParams = array();
		$rows = array();
		
		$PayType_Name = '';//наименование вида оплаты (для сообщения)

		//для Карелии запись на бирку 'Для самозаписи' доступна Интернет-пользователям и через инфомат
		//для Повторной бирки Врач, которому принадлежит бирка или Пользователь группы «Повторный прием»
		if ($this->getRegionNick()=='kareliya') {
			$userGroups = array();
			if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) {
				$userGroups = explode('|', $_SESSION['groups']);
			}
			$allowRepeatedReceptionAccess = in_array('RepeatedReception', $userGroups); //группа прав Повторный приём
			$isRegistratorCall = (!empty($data['session']['ARMList']) && in_array('regpol', $data['session']['ARMList']) || in_array('callcenter', $data['session']['ARMList']));
			if($record_data['TimetableType_id'] == 15){ // Повторная бирка
				if( !$isRegistratorCall && ($data['MedStaffFact_id'] == $record_data['MedStaffFact_id'] || $allowRepeatedReceptionAccess)){
					return true;
				} else{
					return 'Запись невозможна. Бирка доступна для Группы пользователей - Повторный прием.';
				}
			}
			if($record_data['TimetableType_id'] == 17){ // Для самозаписи
				if(isInetUser($data['pmUser_id']) || $data['pmUser_id'] == 999901){
					return true;
				} else{
					return 'Запись невозможна. Бирка доступна только Интернет-пользователям';
				}
			}
		}
		
		if ( ! empty($data['EvnDirection_IsCito']) && $data['EvnDirection_IsCito'] == 2) {
			if($this->getRegionNick()!='kareliya') {		 //#183266
//				if($this->debug)$this->textlog->add('EvnDirection_IsCito == 2');
//				if($this->debug)$this->textlog->add('stop1 checkTimetableQuote');
				return true;
			} else {
				if ( $record_data['TimetableType_id'] != 5){ //#183266
//					if($this->debug)$this->textlog->add('EvnDirection_IsCito == 2');
//					if($this->debug)$this->textlog->add('stop1 checkTimetableQuote');
					return true;
				}
			}
		}

		if ($data['Lpu_id'] == $record_data['Lpu_id']) {
//			if($this->debug)$this->textlog->add('$data Lpu_id == $record_data Lpu_id');

			//первичная бирка доступна только для Регистраторов своей МО
			if($this->getRegionNick()=='kareliya' && $record_data['TimetableType_id'] == 16) {
				$isPolkaRegistrator = (!empty($data['session']['ARMList']) && in_array('regpol', $data['session']['ARMList']));
				if($isPolkaRegistrator){
					return true;
				} else {
					return 'Запись невозможна. Бирка доступна только регистраторами своей МО';
				}
			}
			// Если запись происходит в свою МО, то интересны только внутренние квоты
			$filters[] = ' ttqr.TimetableQuoteType_id in (3,4)';

			// Пользователь не имеет доступ к АРМ регистратора или Call-центра, если имеет, то проверка завершается удачно.
			// (Записи регистраторов и операторов Call-центра не квотируются в своей МО)
			if (
				! empty($data['session']['ARMList']) &&
				is_array($data['session']['ARMList']) &&
				(
					in_array('regpol', $data['session']['ARMList']) ||
					in_array('callcenter', $data['session']['ARMList'])
				)
			) {
				if($this->getRegionNick()!='kareliya') { //#183266
//					if($this->debug)$this->textlog->add('ARMList == regpol or ARMList == callcenter');
//					if($this->debug)$this->textlog->add('stop2 checkTimetableQuote');
					return true;
				} else {
					if($record_data['TimetableType_id'] != 5){ //#183266
//						if($this->debug)$this->textlog->add('ARMList == regpol or ARMList == callcenter');
//						if($this->debug)$this->textlog->add('stop2 checkTimetableQuote');
						return true;
					}
				}
			}
		} else {
//			if($this->debug)$this->textlog->add('$data Lpu_id != $record_data Lpu_id');
			//первичная бирка доступна только для Регистраторов своей МО
			if($this->getRegionNick()=='kareliya' && $record_data['TimetableType_id'] == 16) {
				return 'Запись невозможна. Бирка доступна только регистраторами своей МО';
			}
			// Если запись происходит в чужую МО, то интересны только внешние квоты
			$filters[] = ' ttqr.TimetableQuoteType_id in (1,2)';
		}

		if ( ! empty($record_data['LpuUnit_id'])){
//			if($this->debug)$this->textlog->add(' ! empty LpuUnit_id');

			$filters[] = ' isnull(ttqr.LpuUnit_id, :LpuUnit_id) = :LpuUnit_id';
			$queryParams['LpuUnit_id'] = $record_data['LpuUnit_id'];
		}

		if ( ! empty($record_data['Lpu_id'])){
//			if($this->debug)$this->textlog->add(' ! empty Lpu_id');

			$filters[] = ' isnull(ttqr.Lpu_id, :Lpu_id) = :Lpu_id';
			$queryParams['Lpu_id'] = $record_data['Lpu_id'];
		}

		$queryParams['Timetable_Date'] = $record_data['Timetable_Date'];
		$filters[] = ' cast(:Timetable_Date as date) between ttqr.TimetableQuoteRule_begDT and ttqr.TimetableQuoteRule_endDT';

//		if($this->debug)$this->textlog->add('$filters = '.serialize($filters));

		$base_sql = "
			select
				ttqr.TimetableQuoteType_id,
				ttqr.TimetableQuoteRule_begDT,
				ttqr.TimetableQuoteRule_endDT,
				ttqr.UslugaComplex_id,
				ttqrs.Lpu_id,
				ttqrs.ERTerr_id,
				ttqrs.TimetableQuote_Amount,
				ttqrs.LpuSection_id,
				ttqrs.LpuRegion_id,
				ttqrs.MedStaffFact_id,
				ttqrs.PayType_id,
				pt.PayType_Name,
				case
					when ttqr.UslugaComplex_id is not null then 5
					when ttqr.Resource_id is not null then 4
					when ttqr.MedStaffFact_id is not null then 3
					when ttqr.LpuSection_id is not null then 2
					else 1
				end as QuoteType_id
			from v_TimetableQuoteRule ttqr with (nolock)
			left join v_TimetableQuoteRuleSubject ttqrs with (nolock) on ttqrs.TimetableQuoteRule_id = ttqr.TimetableQuoteRule_id 
			left join v_PayType pt with (nolock) on pt.PayType_id = ttqrs.PayType_id
			" .
			ImplodeWhere($filters);

//		if($this->debug)$this->textlog->add('$mode = '.$mode);

		// TimetableResource
		if ($mode == 'ttr'){
//			if($this->debug)$this->textlog->add('$mode = ttr');

			// Начинаем искать одну квоту, самую детальную, по ресурсу
			$sql = $base_sql . " and ttqr.Resource_id = :Resource_id";
			$queryParams['Resource_id'] = $record_data['Resource_id'];

//			if($this->debug)$this->textlog->add(getDebugSQL($sql, $queryParams));

			$result = $this->db->query($sql, $queryParams);
			if (is_object($result)) {
				$rows = $result->result('array');
//				if($this->debug)$this->textlog->add('is_object $result. $rows = '.serialize($rows));

			} else {
//				if($this->debug)$this->textlog->add(' ! is_object $result');
//				if($this->debug)$this->textlog->add('stop3 checkTimetableQuote');
				return false;
			}
			// закоментировал, т.к. блокирует всю службу, а не конкретный ресурс #192584
			/*if (count($rows) == 0 && !empty($record_data['MedService_id'])) {
				// тогда ищем по службе (ресурс тоже относится к ней)
				$sql = $base_sql . " and ttqr.MedService_id = :MedService_id";
				$queryParams['MedService_id'] = $record_data['MedService_id'];
				$result = $this->db->query($sql, $queryParams);

				if (is_object($result)) {
					$rows = $result->result('array');
				} else {
					return false;
				}
			}*/
		}
		else if($mode == 'ttms'){ // TimetableMedService


			// так как поле "Услуга" в форме НЕ обязательное
			if(isset($record_data['UslugaComplex_id']) && ! empty($record_data['UslugaComplex_id'])){
				// ищем по услуге
				$sql = $base_sql . " and ttqr.UslugaComplex_id = :UslugaComplex_id";
				$queryParams['UslugaComplex_id'] = $record_data['UslugaComplex_id'];
				$result = $this->db->query($sql, $queryParams);

				if (is_object($result)) {
					$rows = $result->result('array');
				} else {
					return false;
				}
			}

			if (count($rows) == 0) {
				// ищем по службе
				$whereUsluga = '';
				if (isset($data['UslugaComplex_id']) && ! empty($data['UslugaComplex_id'])) { 
					$whereUsluga = ' and isnull(ttqr.UslugaComplex_id, :UslugaComplex_id)=:UslugaComplex_id ';
					$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
				}
				$sql = $base_sql . $whereUsluga. " 
					and (
						ttqr.MedService_id = :MedService_id 
						OR
						--связанная слубжа
						ttqr.MedService_id in (
								SELECT 
									msl.MedService_lid
								FROM 
									dbo.v_MedServiceLink msl WITH ( NOLOCK )
									left join v_MedService ms (nolock) on ms.MedService_id = msl.MedService_id
									left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
								WHERE msl.MedService_id = :MedService_id
									and mst.MedServiceType_Code in (1) -- тип Пункт забора биоматериала
							)
					)";
				$queryParams['MedService_id'] = $record_data['MedService_id'];
				//echo getDebugSQL($sql, $queryParams); die;
				$result = $this->db->query($sql, $queryParams);

				if (is_object($result)) {
					$rows = $result->result('array');
				} else {
					return false;
				}
			}

		}
		else { // TimetableGraf

			// Начинаем искать одну квоту, самую детальную, по врачу
			$sql = $base_sql . " and ttqr.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $record_data['MedStaffFact_id'];
			//echo getDebugSQL($sql, $queryParams);die();
			$result = $this->db->query($sql, $queryParams);
			if (is_object($result)) {
				$rows = $result->result('array');
			} else {
				return false;
			}

			if (count($rows) == 0) {
				// ищем дальше, теперь по отделению
				$sql = $base_sql . " and ttqr.LpuSection_id = :LpuSection_id";
				$queryParams['LpuSection_id'] = $record_data['LpuSection_id'];
				//echo getDebugSQL($sql, $queryParams);die();
				$result = $this->db->query($sql, $queryParams);
				if (is_object($result)) {
					$rows = $result->result('array');
				} else {
					return false;
				}

				if (count($rows) == 0) {
					// если и по отделению не нашлось, ищем последнюю, по профилю
					$sql = $base_sql . " and ttqr.LpuSectionProfile_id = :LpuSectionProfile_id";
					$queryParams['LpuSectionProfile_id'] = $record_data['LpuSectionProfile_id'];
					//echo getDebugSQL($sql, $queryParams); die();
					$result = $this->db->query($sql, $queryParams);
					if (is_object($result)) {
						$rows = $result->result('array');
					} else {
						return false;
					}
				}
			}

		}



		if ( count($rows) == 0 ) {
//			if($this->debug)$this->textlog->add('count($rows) == 0');
//			if($this->debug)$this->textlog->add('stop4 checkTimetableQuote');
			// если не существует квота на дату записи (по профилю, по отделению, врачу), то проверка завершается удачно.
			// кроме карелии. Если тип бирки по направлению и нет квот то вывоводим сообщение что запись запрщена
			if($this->getRegionNick()=='kareliya' && $record_data['TimetableType_id'] == 5) { //#183266
				return 'Запись невозможна. На данную бирку нет наличия квот';
			} else {
				return true;
			}

		}
		
		// квотируются только бирки по направлению
		if ( $record_data['TimetableType_id'] != 5 ) {
//			if($this->debug)$this->textlog->add('$record_data TimetableType_id != 5');
//			if($this->debug)$this->textlog->add('stop5 checkTimetableQuote');
			return true;
		}

		$record_data['TimetableQuoteRule_begDT'] = $rows[0]['TimetableQuoteRule_begDT']->format('Y-m-d');
		$record_data['TimetableQuoteRule_endDT'] = $rows[0]['TimetableQuoteRule_endDT']->format('Y-m-d');

		$allow = (in_array($rows[0]['TimetableQuoteType_id'], array(1,3))); // Для разрещающей квоты по умолчанию считаем, что запись разрешена, для запрещающей наоборот

//		if($this->debug)$this->textlog->add('$allow = '.serialize($allow));

		if ( $data['Lpu_id'] == $record_data['Lpu_id'] ) {
//			if($this->debug)$this->textlog->add('$data Lpu_id == $record_data Lpu_id');
			// Если запись происходит в свою МО, то работает следующий алгоритм:
			// Если квота «разрешающая внутренняя» и в ней нет врача, к которому привязан пользователь, то проверка завершается удачно
			// Если квота «запрещающая внутренняя» и в ней нет врача, к которому привязан пользователь, то проверка завершается сообщением вида, запись в запрещена
			$this->load->model("User_model", "User_model");
			$medstafffacts = $this->User_model->getMedStaffFactsBypmUser($data['pmUser_id']); // места работы пользователя

//			if($this->debug)$this->textlog->add('$medstafffacts count = '.sizeof($medstafffacts).'. '.serialize($medstafffacts));

			$hasMedStaffFact = false;
			if ( count($medstafffacts) > 0 ) {

//				if($this->debug)$this->textlog->add('count($medstafffacts) > 0');

				$inner_allow = true;

				foreach ($rows as $row) {

					// если это правило по месту работы, которое есть у врача, привязанного к текущему записывающему пользователю
					//if ($this->checkTimetableQuoteUser($row, $data['pmUser_id'])) {
//					if($this->debug)$this->textlog->add('$data MedStaffFact_id = '.serialize($data['MedStaffFact_id']));
//					if($this->debug)$this->textlog->add('$row MedStaffFact_id = '.serialize($row['MedStaffFact_id']));
					//#117075, #144950
					//если в квоте указан врач, иначе по месту работы
					if ($data['MedStaffFact_id'] == $row['MedStaffFact_id'] || (!$row['MedStaffFact_id'] && $this->checkTimetableQuoteUser($row, $data['pmUser_id'])) ) {

//						if($this->debug)$this->textlog->add('$data MedStaffFact_id = $row MedStaffFact_id');

						$hasMedStaffFact = true;

						// Число записей за период указанный в этой квоте, сделанных всеми пользователями привязанными
						// к врачу, к которому привязан текущий пользователь, удовлетворяющих атрибутам квоты, меньше или
						// равно числу записей указанных в квоте, проверка завершается удачно.

						$record_data_params = $record_data;
						
						if(!empty($row['PayType_id'])) {
							if($row['PayType_id'] != $data['PayType_id']) continue;
							$record_data_params['PayType_id'] = $data['PayType_id'];
						}
						
						if ($mode == 'ttr') {
//							if($this->debug)$this->textlog->add('$mode == ttr');
							$inner_allow = ($this->getTTRRecordsCountByMedStaffFact($row['MedStaffFact_id'], $row['LpuSection_id'], $row['LpuRegion_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						} else if($mode == 'ttms') {
//							if($this->debug)$this->textlog->add('$mode == ttms');
							$inner_allow = ($this->getTTMSRecordsCountByMedStaffFact($row['MedStaffFact_id'], $row['LpuSection_id'], $row['LpuRegion_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						} else {
//							if($this->debug)$this->textlog->add('$mode else');
							$inner_allow = ($this->getTTGRecordsCountByMedStaffFact($row['MedStaffFact_id'], $row['LpuSection_id'], $row['LpuRegion_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						}
						if(!$inner_allow and !empty($row['PayType_id'])) {
							$PayType_Name = $row['PayType_Name'];
						}
//						if($this->debug)$this->textlog->add('$inner_allow = '.serialize($inner_allow));

						$limit = $row['TimetableQuote_Amount'];
//						if($this->debug)$this->textlog->add('TimetableQuote_Amount = '.serialize($limit));
					}


					if ($inner_allow === false) {
						break;
					}
				}

				if ($inner_allow === false) {

//					if($this->debug)$this->textlog->add('$inner_allow === false');
//					if($this->debug)$this->textlog->add('stop6 checkTimetableQuote');
					// Иначе возвращается сообщение, о том, что запись невозможна из-за превышения квоты.
					return 'Запись невозможна. Превышен лимит '.(empty($PayType_Name) ?
						($this->getRegionNick()=='kz' ? 'квоты' : 'общей квоты')
						:'квоты по виду оплаты '.$PayType_Name
					).' для врача в ' . $limit . ' записи(ей) на интервале ' . $rows[0]['TimetableQuoteRule_begDT']->format('d.m.Y') . ' - ' . $rows[0]['TimetableQuoteRule_endDT']->format('d.m.Y').'.';
				}
			}

			$allow = $allow || $hasMedStaffFact; // если есть врач в запрещающей квоте, значит разрешаем запись.
		}
		else {
//			if($this->debug)$this->textlog->add('$data Lpu_id != $record_data Lpu_id');
			// Если запись происходит в чужую МО, то работает следующий алгоритм:

			$inQuoteFound = false;
			// Если квота «разрешающая внешняя» и в ней нет ЛПУ (территории), где работает пользователь, то проверка завершается удачно.
			// Если квота «запрещающая внешняя» и в ней нет ЛПУ (территории), где работает пользователь, то проверка завершается сообщением вида, запись в ЛПУ запрещена
			$ERTerrs = $this->getTerrsByLpu($data['Lpu_id']);
			foreach ( $rows as $row ) {
				if ( $row['TimetableQuoteType_id'] < 3 ) {
					$arrRecData = $record_data;
					if( !empty($record_data['MedStaffFact_id']) && !empty($row['QuoteType_id']) && $row['QuoteType_id'] == 1 ) {
						// если учитываем по профилю целиком, то исключим MedStaffFact_id
						unset($arrRecData['MedStaffFact_id']);
					}
					$record_data_params = $arrRecData;
					if(!empty($row['PayType_id'])) {
						if($data['PayType_id'] != $row['PayType_id']) continue;
						$record_data_params['PayType_id'] = $row['PayType_id'];
					}
					// Если в квоте есть ЛПУ (территория), где работает пользователь, считается количество записей (на период, в котором лежит записываемая бирка, по свойствам правила квоты (подразделение, профиль, отделение, врач)) через направления от ЛПУ, где работает текущий пользователь, если оно меньше чем квота, то проверка завершается удачно;
					if ( $row['Lpu_id'] == $data['Lpu_id'] ) {
						$inQuoteFound = true;
						if ($mode == 'ttr') {
							$allow = ($this->getTTRRecordsCountByLpu($row['Lpu_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						} else if($mode == 'ttms') {
							$allow = ($this->getTTMSRecordsCountByLpu($row['Lpu_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						} else {
							$allow = ($this->getTTGRecordsCountByLpu($row['Lpu_id'], $record_data_params) < $row['TimetableQuote_Amount']);
						}
						if(!$allow) $PayType_Name = $row['PayType_Name'];
						break; // самое детальное правило уже нашлось, можно выходить
					}
					if ( in_array($row['ERTerr_id'], $ERTerrs) ) {
						$inQuoteFound = true;
						if ($mode == 'ttr') {
							$allow = ($this->getTTRRecordsCountByTerr($ERTerrs, $record_data_params) < $row['TimetableQuote_Amount']);
						} else if($mode == 'ttms') {
							$allow = ($this->getTTMSRecordsCountByTerr($ERTerrs, $record_data_params) < $row['TimetableQuote_Amount']);
						} else {
							$allow = ($this->getTTGRecordsCountByTerr($ERTerrs, $record_data_params) < $row['TimetableQuote_Amount']);
						}
						if(!$allow) $PayType_Name = $row['PayType_id'];
						// а вот тут рано останавливаться, может найтись более детальное правило, по конкретной ЛПУ
					}
					
				}
			}//foreach
			if (!$inQuoteFound && !$allow) { // если запрещающая квота и нет субъекта в квоте, то "Запись в МО запрещена"
				if($this->getRegionNick()=='kz') {
					return "Запись невозможна. В МО установлена запрещающая внешняя квота.";
				} else {
					return "Запись невозможна из-за превышения ".
						(
							empty($PayType_Name) ? "внешней общей квоты" : "внешней квоты по виду оплаты ".$PayType_Name
						);
				}
			}
		}


//		if($this->debug)$this->textlog->add('end checkTimetableQuote. Result = '.serialize($allow));
		return $allow;
	}

	/**
	 * Проверка, попадает ли пользователь под квоту 
	 */
	function checkTimetableQuoteUser( $data, $pmUser_id ) {

		$queryParams = array();
		$queryParams['pmUser_id'] = $pmUser_id;
		
		if (!empty($data['MedStaffFact_id'])) {
			$filters = "and msf.MedStaffFact_id = :MedStaffFact_id ";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		} elseif (!empty($data['LpuRegion_id'])) {
			$filters = " and msf.MedStaffFact_id in (
				select MedStaffFact_id
				from v_MedStaffRegion msr with (nolock) 
				where msr.LpuRegion_id = :LpuRegion_id 
				and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= @curDate)
				and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > @curDate)
			) ";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		} elseif (!empty($data['LpuSection_id'])) {
			$filters = " and msf.LpuSection_id = :LpuSection_id ";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}
		
		$sql = "
			declare
				@curDate datetime = dbo.tzGetDate();
			SELECT 
				msf.MedStaffFact_id
			from v_MedStaffFact msf with(nolock) 
			inner join v_pmUser pu with (nolock) on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
			where 
			(
				pu.pmUser_id = :pmUser_id or 
				msf.MedPersonal_id in (
					SELECT
						msf1.MedPersonal_id
					FROM
						v_MedStaffFact msf with (nolock)
					inner join MedStaffFactLink msfl with(nolock) on msf.MedStaffFact_id = msfl.MedStaffFact_sid
					inner join v_MedStaffFact msf1 with (nolock) on msf1.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_pmUser pu with (nolock) on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
					WHERE
						pu.pmUser_id = :pmUser_id
				)
			)
			{$filters}
		";
		
		$res = $this->queryResult($sql, $queryParams);
		return count($res) > 0;		
	}	

	/**
	 * Получение списка территорий обслуживаемых ЛПУ
	 */
	function getTerrsByLpu( $Lpu_id ) {
		$sql = "select
				distinct ERTErr_id
			from v_LpuUnit lu with (nolock)
			left outer join Address lua with (nolock) on lu.Address_id = lua.Address_id
			inner join ERTerr Terr with (nolock) on
			(
				((lua.KLCountry_id = Terr.KLCountry_id) or coalesce(lua.KLCountry_id, Terr.KLCountry_id) is null) and
				((lua.KLRGN_id = Terr.KLRGN_id) or coalesce(lua.KLRGN_id, Terr.KLRGN_id) is null) and
				((lua.KLSubRGN_id = Terr.KLSubRGN_id) or coalesce(lua.KLSubRGN_id, Terr.KLSubRGN_id) is null) and
				((lua.KLCity_id = Terr.KLCity_id) or coalesce(lua.KLCity_id, Terr.KLCity_id) is null) and
				((lua.KLTown_id = Terr.KLTown_id) or coalesce(lua.KLTown_id, Terr.KLTown_id) is null)
			) and lua.KLCountry_id is not null and lua.KLRGN_id is not null
			where lu.Lpu_id = :Lpu_id";
		$queryParams = array(
			'Lpu_id' => $Lpu_id
		);
		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$rows = $result->result('array');
			$res = array();
			foreach ( $rows as $row ) {
				$res[] = $row['ERTErr_id'];
			}
			return $res;
		} else {
			return false;
		}
	} //end getTerrsByLpu()
	
	/**
	 * Автоматические системные уведомления по остаткам квот
	 */
	function QuoteNoticeSend() {
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime",86400);
		ini_set("session.cookie_lifetime",86400);

		$this->load->library('textlog', array('file'=>'QuoteNoticeSend_'.date('Y-m-d').'.log'));
		$this->textlog->add('Запуск рассылки уведомлений регистраторам МО по остаткам квот');
		
		$this->load->model('Messages_model', 'Messages_model');
		$sql = "
			DECLARE	@getdate datetime = dbo.tzGetDate();

			SELECT DISTINCT ttqr.Lpu_id
			FROM v_TimetableQuoteRule ttqr with (nolock) 
			WHERE @getdate between ttqr.TimetableQuoteRule_begDT and ttqr.TimetableQuoteRule_endDT+1
		"; //получаем список МО где есть действующие квоты
		$resp = $this->db->query($sql, array() );
		if(!is_object($resp)) {
			$this->textlog->add('Ошибка при определении списка МО с действующими квотами.');
			return false;
		}
		
		$lpulist = $resp->result('array');
		$this->textlog->add('Найдено '.count($lpulist).' МО где есть действующие квоты');
		foreach($lpulist as $lpu) {//для каждого МО собираем информацию об остатках квот
			$this->textlog->add('Читаем квоты для Lpu_id = '.$lpu['Lpu_id']);
			//по типам квот (разреш.внеш, запрещ.внеш, разреш.внутр, запрещ.внутр)
			$Qdata = array('1'=>array(), '2'=>array(), '3'=>array(), '4'=>array());
			$params = array('Lpu_id'=>$lpu['Lpu_id']);
			//Читаем список действующих квот в МО:
			$sql = "
				DECLARE	@getdate datetime = dbo.tzGetDate();
				SELECT
					ttq.TimeTableQuoteRule_id,
					ttq.TimeTableQuoteType_id,
					convert(varchar(10), ttq.TimetableQuoteRule_begDT, 120) as begDT120,
					convert(varchar(10), ttq.TimetableQuoteRule_endDT, 120) as endDT120,
					convert(varchar(10), ttq.TimetableQuoteRule_begDT, 104) as begDT104,
					convert(varchar(10), ttq.TimetableQuoteRule_endDT, 104) as endDT104,
					ttqs.TimeTableQuote_Amount,
					coalesce(L.Lpu_Nick, ER.ERTerr_Name, msf.Person_FIO, subjLR.LpuRegion_Name, LS.LpuSection_Name) as subjName,
					
					ttq.Lpu_id,
					ttqs.Lpu_id as SubjLpu_id,
					ttq.LpuUnit_id,
					ttq.MedService_id,
					ttq.Resource_id,
					ttq.UslugaComplex_id,

					ttq.MedStaffFact_id,
					ttq.LpuSection_id,
					ttq.LpuSectionProfile_id,
					LS.LpuSectionProfile_id as LpuSectionProfile_id2,
					
					LU.LpuUnit_Name,
										
					ttqs.MedStaffFact_id as subj_MedStaffFact_id,
					ttqs.LpuSection_id as subj_LpuSection_id,
					ttqs.LpuRegion_id as subj_LpuRegion_id,
					ttqs.PayType_id as subj_PayType_id,
					subjPT.PayType_Name,
					ER.ERTerr_id,
					case 
						when ttq.LpuSectionProfile_id is not null then 'Профиль: ' + LSP.LpuSectionProfile_Name
						when ttq.MedStaffFact_id is not null then 'Врач: ' + msf.Person_FIO + isnull(' [' + cast(msf_ls.LpuSection_Code as varchar) + '. ' + msf_ls.LpuSection_Name + ']', '')
						when ttq.LpuSection_id is not null then 'Отделение: ' + LS.LpuSection_Name
						when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 'Служба: ' + isnull(ms2.MedService_Name, '') + isnull(' [' + UC.UslugaComplex_Name + ']', '')
						when ttq.Resource_id is not null and ttq.MedService_id is not null then 'Ресурс: ' + res.Resource_Name + isnull(' [' + ms.MedService_Nick + ']', '')
					end as TimetableQuote_Object
					
				FROM v_TimetableQuoteRule ttq with (nolock)
					left join v_TimetableQuoteRuleSubject ttqs with (nolock) on ttqs.TimetableQuoteRule_id = ttq.TimetableQuoteRule_id
					left join v_PayType subjPT with(nolock) on subjPT.PayType_id = ttqs.PayType_id
					left join v_LpuSection subjLS with(nolock) on subjLS.LpuSection_id = ttqs.LpuSection_id
					left join v_LpuRegion subjLR with (nolock) on subjLR.LpuRegion_id = ttqs.LpuRegion_id
					left join v_Resource res with(nolock) on res.Resource_id = ttq.Resource_id
					left join v_MedService ms with (nolock) on ms.MedService_id = res.MedService_id
					left join v_MedService ms2 with (nolock) on ms2.MedService_id = ttq.MedService_id
					left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = ttq.UslugaComplex_id
					left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = ttq.LpuUnit_id
					left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = ttq.LpuSectionProfile_id
					left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ttq.LpuSection_id
					left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = ttq.MedStaffFact_id
					left join v_LpuSection msf_ls with (nolock) on msf.LpuSection_id = msf_ls.LpuSection_id
					left join v_ERTerr ER on ER.ERTerr_id = ttqs.ERTerr_id
					left join v_Lpu L on L.Lpu_id = ttqs.Lpu_id
				WHERE ttq.Lpu_id = :Lpu_id and cast(@getdate as date) between ttq.TimeTableQuoteRule_begDT and ttq.TimeTableQuoteRule_endDT
			";
			//~ echo getDebugSQL($sql, $params);exit;

			$resp = $this->db->query($sql, $params );
			if(!is_object($resp)) {
				$this->textlog->add('Пропускаем Lpu_id = '.$lpu['Lpu_id'].' : ошибка запроса '.getDebugSQL($sql, $params));
				continue;
			}
			$quotes = $resp->result('array');
			foreach($quotes as $q) {
				$filters[] = " ttr.Person_id is not null and ttr.TimetableType_id = 5 ";
				$queryParams = array();
				$records_count = 0;
				$object_name = $q['TimetableQuote_Object'];
				$subject_name = $q['subjName'];
				$limit = $q['TimeTableQuote_Amount'];
				
				$this->textlog->add("TimeTableQuoteRule_id=".$q['TimeTableQuoteRule_id']." (".($q['TimeTableQuoteType_id']<3 ? 'Внешняя квота ':'Внутренняя квота').") Лимит $limit. Начало квоты: ".$q['begDT104'].". Окончание: ".$q['endDT104'].". Объект квотирования: $object_name. Субъект: $subject_name.");
				
				$data = array(
							'TimetableQuoteRule_begDT' => $q['begDT120'],
							'TimetableQuoteRule_endDT' => $q['endDT120'],
							'Lpu_id' => $lpu['Lpu_id'],
							'LpuUnit_id' => $q['LpuUnit_id'],
							'LpuSectionProfile_id' => !empty($q['LpuSectionProfile_id']) ? $q['LpuSectionProfile_id'] : $q['LpuSectionProfile_id2'],
							'LpuSection_id' => $q['LpuSection_id'],
							'MedStaffFact_id' => $q['MedStaffFact_id'],
							'MedService_id' => $q['MedService_id'],
							'Resource_id' => $q['Resource_id'],
							'UslugaComplex_id' => $q['UslugaComplex_id'],
							'PayType_id'=>$q['subj_PayType_id']
						);
				//$this->textlog->add('Quote data = '.var_export($data,1));
				if($q['TimeTableQuoteType_id']<3) {
					//внешние квоты
					//Если МО объекта квоты == МО субъекта, то квоту не включаем в отчет, т.к. в этих случаях бирки фактически внутренние, а не внешние, т.е. не лимитируются
					if (!empty($q['Resource_id'])) {
						if (!empty($q['ERTerr_id'])) {
							$records_count = $this->getTTRRecordsCountByTerr($q['ERTerr_id'], $data);
							$this->textlog->add('... Resource_id = '.$q['Resource_id'].' ; getTTRRecordsCountByTerr = '.$records_count);
						} else if (!empty($q['Lpu_id'])) {
							if($q['Lpu_id']==$q['SubjLpu_id']) continue;
							$records_count = $this->getTTRRecordsCountByLpu($q['SubjLpu_id'], $data);
							$this->textlog->add('... Resource_id = '.$q['Resource_id'].' ; getTTRRecordsCountByLpu = '.$records_count);
						}
					} else {
						if (!empty($q['ERTerr_id'])) {
							$records_count = $this->getTTGRecordsCountByTerr($q['ERTerr_id'], $data);
							$this->textlog->add('... getTTGRecordsCountByTerr = '.$records_count);
						} else if (!empty($q['Lpu_id'])) {
							if($q['Lpu_id']==$q['SubjLpu_id']) continue;
							$records_count = $this->getTTGRecordsCountByLpu($q['SubjLpu_id'], $data);
							$this->textlog->add('... getTTGRecordsCountByLpu = '.$records_count);
						}
					}
				} else {
					//внутренние квоты
					$countTTR = $this->getTTRRecordsCountByMedStaffFact($q['subj_MedStaffFact_id'], $q['subj_LpuSection_id'], $q['subj_LpuRegion_id'], $data);
					$countTTMS = $this->getTTMSRecordsCountByMedStaffFact($q['subj_MedStaffFact_id'], $q['subj_LpuSection_id'], $q['subj_LpuRegion_id'], $data);
					$countTTG = $this->getTTGRecordsCountByMedStaffFact($q['subj_MedStaffFact_id'], $q['subj_LpuSection_id'], $q['subj_LpuRegion_id'], $data);
					
					$records_count = $countTTR+$countTTMS+$countTTG;
					$this->textlog->add("Бирок: $records_count (ttr=$countTTR; ttms=$countTTMS;ttg=$countTTG) Остаток=".($limit - $records_count)." ; Resource_id=".$q['Resource_id'].' ; UslugaComplex_id='.$q['UslugaComplex_id'].' ;');
				}
				
				$Qdata[$q['TimeTableQuoteType_id']][] = array(
					'value'=> ($limit - $records_count),//остаток
					'LpuUnit_Name'=>$q['LpuUnit_Name'],//подразделение
					'Object_Name'=>$object_name,//наименование объекта квотирования
					'begDT'=>$q['begDT104'],//начало действия квоты
					'endDT'=>$q['endDT104'],//окончание действия квоты
					'Subject_Name'=>$subject_name, //наименование субъекта
					'PayType_Name'=>$q['PayType_Name'] //вид оплаты
				);
			}
			$msg = '';
			foreach($Qdata as $key => $QuotesByType) {
				if(count($QuotesByType)>0) {
					switch($key) {
						case '1': $msg.='<b>Разрешающая внешняя</b>';break;
						case '2': $msg.='<b>Запрещающая внешняя</b>';break;
						case '3': $msg.='<b>Разрешающая внутренняя</b>';break;
						case '4': $msg.='<b>Запрещающая внутренняя</b>';break;
					}
					$msg.="<table cellspacing='0' border='1' class='table-in-message'><tr>
						<td>№</td>
						<td>Подразделение</td>
						<td>Объект квотирования</td>
						<td>Начало действия квоты</td>
						<td>Окончание действия квоты</td>
						<td>".($key<3?"МО<br>Территория":"
							Отделение<br>
							Отделение/Участок<br>
							Отделение/Врач
						")."</td>
						<td>Вид оплаты</td>
						<td>Остаток квоты</td>
						</tr>";
					$i=0;
					foreach($QuotesByType as $quote) {
						$i+=1;
						$msg.="<tr>";
						$msg.="<td>$i</td><td>".$quote['LpuUnit_Name']."</td><td>".$quote['Object_Name']."</td><td>".$quote['begDT']."</td><td>".$quote['endDT']."</td><td>".$quote['Subject_Name']."</td><td>".$quote['PayType_Name']."</td><td>".$quote['value']."</td>";
						$msg.="</tr>";
					}
					$msg.="</table><br>";
				}
			}
			if(empty($msg)) {
				$this->textlog->add('Пропускаем Lpu_id = '.$lpu['Lpu_id'].' : нет информации по квотам.');
				continue;
			}
			$noticeData = array(
				'autotype' => 1 //обычное сообщение (msg.NoticeType)
				,'type' => 1 //Информационное (обычное)
				,'pmUser_id' => 1
				,'title' => 'Остатки по квотам'
				,'text' => $msg
				,'MedServiceType_SysNick' => 'regpol'
				,'Lpu_id' => $lpu['Lpu_id']
			);
			$msg_result = $this->Messages_model->autoMessage($noticeData);//отправляется сообщение
			
			if(!empty($msg_result['Error_Msg'])) {
				$this->textlog->add('Ошибка: '.$msg_result['Error_Msg'].' . Не удалось отправить сообщение '.$msg);
			} else if(!empty($msg_result['Message_id'])) {
				$this->textlog->add('Отправлено сообщение Message_id = '.$msg_result['Message_id']);
			}
		}
		return true;
	}
}

?>