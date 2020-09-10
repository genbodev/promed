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
 *
 * @property CI_DB_driver $db
 * @property User_model $User_model
 * @property Messages_model $Messages_model
 */
class TimetableQuote_model extends swPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	private $debug = false;

	function __construct()
	{
		parent::__construct();
		if ($this->debug) {
			$this->load->library("textlog", ["file" => "TimetableQuote_model_ref140196.log"]);
		}
	}

	/**
	 * @param string $value
	 */
	function addLog($value)
	{
		if ($this->debug) {
			$this->textlog->add($value);
		}
	}

	/**
	 * Получение списка квот
	 * @param $data
	 * @return array|bool
	 */
	function getQuotesList($data)
	{
		$queryParams = [];
		$filters = [];
		// Квоты только для своей ЛПУ
		$filters[] = "ttq.Lpu_id = :Lpu_id";
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["MedStaffFact_id"])) {
			$filters[] = "ttq.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if (isset($data["LpuSection_id"])) {
			$filters[] = "(ttq.LpuSection_id = :LpuSection_id or msf.LpuSection_id = :LpuSection_id)";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}

		if (isset($data["LpuSectionProfile_id"])) {
			$filters[] = "(ttq.LpuSectionProfile_id = :LpuSectionProfile_id or msf_ls.LpuSectionProfile_id = :LpuSectionProfile_id or ls.LpuSectionProfile_id= :LpuSectionProfile_id)";
			$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}

		if (isset($data["LpuUnit_id"])) {
			$filters[] = "ttq.LpuUnit_id = :LpuUnit_id";
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
		}

		if (isset($data["MedService_id"])) {
			$filters[] = "ttq.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $data["MedService_id"];
		}

		if (isset($data["Resource_id"])) {
			$filters[] = "ttq.Resource_id = :Resource_id";
			$queryParams["Resource_id"] = $data["Resource_id"];
		}

		$queryParams["TimetableQuoteRule_Date"] = $data["TimetableQuoteRule_Date"];
		$filters[] = ":TimetableQuoteRule_Date between ttq.TimetableQuoteRule_begDT and ttq.TimetableQuoteRule_endDT";

		if (isset($data["TimetableQuoteType_id"])) {
			$filters[] = "ttq.TimetableQuoteType_id = :TimetableQuoteType_id";
			$queryParams["TimetableQuoteType_id"] = $data["TimetableQuoteType_id"];
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select 
				ttq.TimetableQuoteRule_id as \"TimetableQuoteRule_id\",
				ttq.TimetableQuoteType_id as \"TimetableQuoteType_id\",
				ttq.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				ttq.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ttq.LpuSection_id as \"LpuSection_id\",
				ttq.MedStaffFact_id as \"MedStaffFact_id\",
				ttqt.TimetableQuoteType_Name as \"TimetableQuoteType_Name\",
				case 
					when ttq.LpuSectionProfile_id is not null then 'Профиль: '||lsp.LpuSectionProfile_Name
					when ttq.MedStaffFact_id is not null then 'Врач: '||msf.Person_FIO||coalesce(' ['||msf_ls.LpuSection_Code::varchar||'. '||msf_ls.LpuSection_Name||']', '')
					when ttq.LpuSection_id is not null then 'Отделение: '||ls.LpuSection_Name
					when ttq.Resource_id is not null and ttq.MedService_id is not null then 'Ресурс: '||res.Resource_Name||coalesce(' ['||ms.MedService_Nick||']', '')
					when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 'Служба: '||coalesce(ms2.MedService_Name, '')||coalesce(' ['||uc.UslugaComplex_Name||']', '')
				end
				 as \"TimetableQuote_Object\",
				to_char(ttq.TimetableQuoteRule_begDT, '{$this->dateTimeForm104}') as \"TimetableQuoteRule_begDT\",
				to_char(ttq.TimetableQuoteRule_endDT, '{$this->dateTimeForm104}') as \"TimetableQuoteRule_endDT\",
				substring(ttqs.Subject, 0, length(ttqs.Subject)) as \"TimetableQuoteSubjects\"
			from
			    v_TimetableQuoteRule ttq
				left join v_LpuUnit lu on ttq.LpuUnit_id = lu.LpuUnit_id
				left join v_LpuSectionProfile lsp on ttq.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				left join v_LpuSection ls on ttq.LpuSection_id = ls.LpuSection_id
				left join v_MedStaffFact msf on ttq.MedStaffFact_id = msf.MedStaffFact_id
				left join v_LpuSection msf_ls on msf.LpuSection_id = msf_ls.LpuSection_id
				left join v_Resource res on res.Resource_id = ttq.Resource_id
				left join v_MedService ms on ms.MedService_id = res.MedService_id
				left join v_MedService ms2 on ms2.MedService_id = ttq.MedService_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ttq.UslugaComplex_id
				left join lateral (
					select
						(
							select string_agg(coalesce(l.Lpu_Nick, et.ERTerr_Name, msf.Person_FIO, lr.LpuRegion_Name, ls.LpuSection_Name)||' - '||ttqs2.TimetableQuote_Amount::varchar||'|', '') 
							from
							    v_TimetableQuoteRuleSubject ttqs2
								left join v_Lpu l on ttqs2.Lpu_id = l.Lpu_id
								left join v_ERTerr et on ttqs2.ERTerr_id = et.ERTerr_id
								left join v_MedStaffFact msf on ttqs2.MedStaffFact_id = msf.MedStaffFact_id
								left join v_LpuSection ls on ttqs2.LpuSection_id = ls.LpuSection_id
								left join v_LpuRegion lr on ttqs2.LpuRegion_id = lr.LpuRegion_id
							where ttqs2.TimetableQuoteRule_id = ttqs1.TimetableQuoteRule_id
						) as Subject
					from v_TimetableQuoteRuleSubject ttqs1
					where ttqs1.TimetableQuoteRule_id = ttq.TimetableQuoteRule_id
				) as ttqs on true
				left join TimetableQuoteType ttqt on ttq.TimetableQuoteType_id = ttqt.TimetableQuoteType_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка профилей для ЛПУ с фильтрами по структуре
	 * @param $data
	 * @return array|bool
	 */
	public function getLpuSectionProfileList($data)
	{
		$queryParams = [];
		if (isset($data["LpuUnit_id"])) {
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
			$filters[] = "ls.LpuUnit_id = :LpuUnit_id";
		}
		if (isset($data["LpuSection_id"])) {
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
			$filters[] = "ls.LpuSection_id = :LpuSection_id";
		}
		if (isset($data["LpuSectionPid_id"])) {
			$queryParams["LpuSectionPid_id"] = $data["LpuSectionPid_id"];
			$filters[] = "ls.LpuSection_id = :LpuSectionPid_id";
		}
		$filters[] = "ls.Lpu_id = :Lpu_id";
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select distinct
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				rtrim(lsp.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\"
			FROM
				v_LpuSection ls
				left join v_LpuSectionProfile lsp on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			{$whereString}
			order by
				rtrim(lsp.LpuSectionProfile_Name),
				ls.LpuSectionProfile_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверки при сохранении
	 * @param $data
	 * @return string
	 */
	public function checkSaveTimetableQuoteRuleSubject($data)
	{
		$subjects = json_decode($data["rule_subjects"]);
		// Проверяем, что $subjects не пустой 
		if (count($subjects) == 0) {
			return "Не задано субъектов квоты";
		}
		// Проверяем что уже нет правила квоты с этими же данными, пересекающихся с сохраняемым по срокам
		$filters = [];
		if (isset($data["TimetableQuoteRule_id"])) {
			$queryParams["TimetableQuoteRule_id"] = $data["TimetableQuoteRule_id"];
			$filters[] = "ttq.TimetableQuoteRule_id != :TimetableQuoteRule_id";
		}
		if (!empty($data["Lpu_id"])) {
			$filters[] = "ttq.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filters[] = "ttq.LpuUnit_id = :LpuUnit_id";
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (!empty($data["LpuSectionProfile_id"])) {
			$filters[] = "ttq.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "ttq.LpuSection_id = :LpuSection_id";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$filters[] = "ttq.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if (!empty($data["MedService_id"])) {
			$filters[] = "ttq.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["Resource_id"])) {
			$filters[] = "ttq.Resource_id = :Resource_id";
			$queryParams["Resource_id"] = $data["Resource_id"];
		}
		if (!empty($data["UslugaComplex_id"])) {
			$filters[] = "ttq.UslugaComplex_id = :UslugaComplex_id";
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		if ($data["TimetableQuoteType_id"] < 3) {
			$filters[] = "ttq.TimetableQuoteType_id in (1, 2)";
		} else {
			$filters[] = "ttq.TimetableQuoteType_id in (3, 4)";
		}
		$filters[] = "(:TimetableQuoteRule_begDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT or :TimetableQuoteRule_endDT between ttq.TimetableQuoteRule_begDT and TimetableQuoteRule_endDT)";
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(ttq.TimetableQuoteRule_id) as \"cnt\"
			from v_TimetableQuoteRule ttq
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$res = $result->result("array");
			if ($res[0]["cnt"] > 0) {
				return "Уже есть правило квоты с этими же данными, пересекающееся с сохраняемым по срокам";
			}
		}
		if ($data["TimetableQuoteType_id"] < 3) {
			// Проверки для внешних квот
			$subject_list = [];
			foreach ($subjects as $subject) {
                $subject->TimetableQuote_Amount = (int)$subject->TimetableQuote_Amount;
				// Проверяем, что все субъекты это либо ЛПУ либо территория
				if (empty($subject->Lpu_id) && empty($subject->ERTerr_id)) {
					return "В записи не задан субъект квоты";
				}
				// Проверяем, что у всех субъектов есть числовое значение квоты
				if (!isset($subject->TimetableQuote_Amount)) {
					return "Не задано количество квот у субъекта";
				}
				// Проверяем, что нет повторяющихся субъектов квоты
				$subject_item = serialize(array($subject->Lpu_id, $subject->ERTerr_id, $subject->PayType_id));
				if (in_array($subject_item, $subject_list)) {
					return "Нельзя указать два раза один и тот же субъект";
				}
				if (!empty($data["Resource_id"])) {
					if (!empty($subject->Lpu_id)) {
						if ($this->getTTRRecordsCountByLpu($subject->Lpu_id, $data) > $subject->TimetableQuote_Amount) {
							return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject->Lpu_Nick);
						}
					}
					if (!empty($subject->ERTerr_id)) {
						if ($this->getTTRRecordsCountByTerr($subject->ERTerr_id, $data) > $subject->TimetableQuote_Amount) {
							return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject->ERTerr_Name);
						}
					}
				} else {
					if (!empty($subject->Lpu_id)) {
						if ($this->getTTGRecordsCountByLpu($subject->Lpu_id, $data) > $subject->TimetableQuote_Amount) {
							return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject->Lpu_Nick);
						}
					}
					if (!empty($subject->ERTerr_id)) {
						if ($this->getTTGRecordsCountByTerr($subject->ERTerr_id, $data) > $subject->TimetableQuote_Amount) {
							return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject->ERTerr_Name);
						}
					}
				}
				$subject_list[] = $subject_item;
			}
		} else {
			// Проверки для внутренних квот
			$subject_list = [];
			foreach ($subjects as $subject) {
                $subject->TimetableQuote_Amount = (int)$subject->TimetableQuote_Amount;
				// Проверяем, что все субъекты не пустые
				if (
					empty($subject->LpuSection_id) ||
					($subject->SubjectType_id == 2 && empty($subject->MedStaffFact_id)) ||
					($subject->SubjectType_id == 3 && empty($subject->LpuRegion_id))
				) {
					return "В записи не задан субъект квоты";
				}
				// Проверяем, что у всех субъектов есть числовое значение квоты
				if (!isset($subject->TimetableQuote_Amount)) {
					return "Не задано количество квот у субъекта";
				}
				// Проверяем, что нет повторяющихся субъектов квоты
				// т.к. сейчас (пока на Уфе) несколько полей, повтором считать будем только полное совпадение по всем полям
				// для простоты рассчёта переводим в строку
				$subject_item = serialize(array($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id, $subject->PayType_id));
				if (in_array($subject_item, $subject_list)) {
					return "Нельзя указать два раза один и тот же субъект";
				}
				$subject_name =
					!empty($subject->MedStaffFact_id) ? $subject->MedStaffFact_FIO :
						(!empty($subject->LpuRegion_id) ? $subject->LpuRegion_Name :
							$subject->LpuSection_Name);

				if (!empty($data["Resource_id"])) {
					if ($this->getTTRRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id,
							$this->getRegionNick() == "kz" ? $data : array_merge($data, array("PayType_id" => $subject->PayType_id))
						) > $subject->TimetableQuote_Amount) {
						return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject_name);
					}
				} else if (!empty($data["UslugaComplex_id"])) {
					if ($this->getTTMSRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id,
							$this->getRegionNick() == "kz" ? $data : array_merge($data, array("PayType_id" => $subject->PayType_id))
						) > $subject->TimetableQuote_Amount) {
						return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject_name);
					}
				} else {
					if ($this->getTTGRecordsCountByMedStaffFact($subject->MedStaffFact_id, $subject->LpuSection_id, $subject->LpuRegion_id,
							$this->getRegionNick() == "kz" ? $data : array_merge($data, array("PayType_id" => $subject->PayType_id))
						) > $subject->TimetableQuote_Amount) {
						return "Невозможно создать правило, лимит квоты уже превышен для субъекта " . toAnsi($subject_name);
					}
				}
				$subject_list[] = $subject_item;
			}
		}
		return null;
	}

	/**
	 * Подсчет количества внешних бирок, занятых из заданного ЛПУ
	 * @param $Lpu_id
	 * @param $data
	 * @return int
	 */
	function getTTGRecordsCountByLpu($Lpu_id, $data)
	{
		$filter = "
			select TimetableGraf_id
			from v_EvnDirection_all
			where Lpu_id = {$Lpu_id}
		";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых из заданного ЛПУ
	 * @param $Lpu_id
	 * @param $data
	 * @return int
	 */
	function getTTRRecordsCountByLpu($Lpu_id, $data)
	{
		$filter = "
			select TimetableResource_id
			from v_EvnDirection_all
			where Lpu_id = {$Lpu_id}
		";
		return $this->getTTRRecordsCount($data, $filter);
	}

	/**
	 * @param $Lpu_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByLpu($Lpu_id, $data)
	{
		$filter = "
			select TimeTableMedService_id
			from v_EvnDirection_all
			where Lpu_id = {$Lpu_id}
		";
		return $this->getTTMSRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых с заданной территории(списка территорий)
	 * @param $ERTerr_id
	 * @param $data
	 * @return int
	 */
	function getTTGRecordsCountByTerr($ERTerr_id, $data)
	{
		if (is_array($ERTerr_id)) {
			if (count($ERTerr_id) == 0) {
				// такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}
		$filter = "
			select TimetableGraf_id
			from v_EvnDirection
			where Lpu_id in (
				select Lpu_id
				from
				    v_LpuUnit_ER lu
					left join Address a on a.Address_id = lu.Address_id
					inner join v_ERTerr Terr on (
						((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
						((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
						((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
						((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
						((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
					) and {$terr_cond}
			)
		";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества внешних бирок, занятых с заданной территории(списка территорий)
	 * @param $ERTerr_id
	 * @param $data
	 * @return int
	 */
	function getTTRRecordsCountByTerr($ERTerr_id, $data)
	{
		if (is_array($ERTerr_id)) {
			if (count($ERTerr_id) == 0) {
				// такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}
		$filter = "
			select TimetableResource_id
			from v_EvnDirection
			where Lpu_id in (
				select Lpu_id
				from
					v_LpuUnit_ER lu
					left join Address a on a.Address_id = lu.Address_id
					inner join v_ERTerr Terr on (
						((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
						((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
						((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
						((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
						((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
					) and {$terr_cond}
			)
		";
		return $this->getTTGRecordsCount($data, $filter);
	}


	/**
	 * @param $ERTerr_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByTerr($ERTerr_id, $data)
	{
		if (is_array($ERTerr_id)) {
			if (count($ERTerr_id) == 0) {
				// такого быть не должно
				return 0;
			}
			$terr_cond = " Terr.ERTerr_id in (" . implode(', ', $ERTerr_id) . ") ";
		} else {
			$terr_cond = " Terr.ERTerr_id = {$ERTerr_id} ";
		}
		$filter = "
			select TimeTableMedService_id
			from v_EvnDirection
			where Lpu_id in (
				select Lpu_id
				from
				    v_LpuUnit_ER lu
					left join Address a on a.Address_id = lu.Address_id
					inner join v_ERTerr Terr on (
						((a.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
						((a.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
						((a.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
						((a.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
						((a.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
					) and {$terr_cond}
			)
		";
		return $this->getTTGRecordsCount($data, $filter);
	}

	/**
	 * Подсчет количества занятых внешних бирок
	 * @param $data
	 * @param $inbox_filter
	 * @return int
	 */
	function getTTGRecordsCount($data, $inbox_filter)
	{
		$filters = ["ttg.Person_id is not null and TimetableType_id = 5"]; // только внешние бирки
		$queryParams = [];
		if (!empty($data["Lpu_id"])) {
			$filters[] = "msf.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filters[] = "ls.LpuUnit_id = :LpuUnit_id";
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (!empty($data["LpuSectionProfile_id"])) {
			$filters[] = "ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$filters[] = "ttg.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if (!empty($data["PayType_id"])) {
			$filters[] = "ed.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		$filters[] = "ttg.TimetableGraf_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$filters[] = "ttg.TimetableGraf_id in ({$inbox_filter})";
		// При подсчете записей не считаем записи врача к самому себе
		$filters[] = "
			msf.MedPersonal_id != (
				select pu.pmUser_MedPersonal_id
				from v_pmUser pu
				where ttg.pmUser_updId = pu.pmUser_id
				limit 1
			)
		";
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(1) as \"cnt\"
			from
				v_TimeTableGraf_lite ttg
				left join v_EvnDirection_all ed on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result("array");
		return $res[0]["cnt"];
	}

	/**
	 * Подсчет количества занятых внешних бирок
	 * @param $data
	 * @param $inbox_filter
	 * @return int
	 */
	function getTTRRecordsCount($data, $inbox_filter)
	{
		$filters = ["ttr.Person_id is not null and TimetableType_id = 5"]; // только внешние бирки
		$queryParams = [];
		if (empty($data["Resource_id"])) {
			return 0;
		}
		$filters[] = "ttr.Resource_id = :Resource_id";
		$filters[] = "ttr.TimetableResource_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$filters[] = "ttr.TimetableResource_id in ({$inbox_filter})";
		$queryParams["Resource_id"] = $data["Resource_id"];
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(1) as \"cnt\"
			from v_TimetableResource_lite ttr
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result("array");
		return $res[0]["cnt"];
	}

	/**
	 * @param $data
	 * @param $inbox_filter
	 * @return int
	 */
	function getTTMSRecordsCount($data, $inbox_filter)
	{
		$filters = ["ttms.Person_id is not null and TimetableType_id = 5"]; // только внешние бирки
		$queryParams = [];
		if (!empty($data["MedService_id"])) {
			$filters[] = "ttms.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["UslugaComplex_id"])) {
			$filters[] = "ttms.UslugaComplexMedService_id = :UslugaComplex_id";
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		$filters[] = "ttms.TimetableMedService_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$filters[] = "ttms.TimeTableMedService_id in ({$inbox_filter})";
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(1) as \"cnt\"
			from v_TimetableResource_lite ttms
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result("array");
		return $res[0]["cnt"];
	}

	/**
	 * Подсчет количества занятых врачом бирок для внутреннего квотирования
	 * @param $MedStaffFact_id
	 * @param $LpuSection_id
	 * @param $LpuRegion_id
	 * @param $data
	 * @return int
	 */
	function getTTGRecordsCountByMedStaffFact($MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data)
	{
		$filters = ["ttg.Person_id is not null and ttg.TimetableType_id = 5"]; // считаем все бирки
		$queryParams = [];
		if (!empty($data["Lpu_id"])) {
			$filters[] = "msf.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filters[] = "ls.LpuUnit_id = :LpuUnit_id";
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (!empty($data["LpuSectionProfile_id"])) {
			$filters[] = "ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["MedStaffFact_id"])) {
			$filters[] = "ttg.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		$filters[] = "ttg.TimetableGraf_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		if (!empty($MedStaffFact_id)) {
			// Запись от этого врача
			if (empty($data["From_MedStaffFact_id"])) {
				$filters[] = "ed.MedStaffFact_id = :MedStaffFact_sid";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
			} else {
				$filters[] = "
					(
						ed.MedStaffFact_id = :MedStaffFact_sid or
						ed.MedStaffFact_id in (
							select MedStaffFact_sid
							from MedStaffFactLink
							where MedStaffFact_id = :MedStaffFact_sid2
						)
					)
				";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
				$queryParams["MedStaffFact_sid2"] = $data["From_MedStaffFact_id"];
			}
		} elseif (!empty($LpuRegion_id)) {
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffRegion msr 
					where msr.LpuRegion_id = :LpuRegion_sid 
					  and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= tzgetdate())
					  and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > tzgetdate())
				)
			";
			$queryParams["LpuRegion_sid"] = $LpuRegion_id;
		} elseif (!empty($LpuSection_id)) {
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffFact msf
					where msf.LpuSection_id = :LpuSection_sid
				)
			";
			$queryParams["LpuSection_sid"] = $LpuSection_id;
		}
		if (!empty($data["PayType_id"])) {
			$filters[] = "ed.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(1) as \"cnt\"
			from 
				v_TimeTableGraf_lite ttg
				left join v_EvnDirection_all ed on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result("array");
		return $res[0]["cnt"];
	}

	/**
	 * Подсчет количества занятых врачом бирок для внутреннего квотирования
	 * @param $MedStaffFact_id
	 * @param $LpuSection_id
	 * @param $LpuRegion_id
	 * @param $data
	 * @return int
	 */
	function getTTRRecordsCountByMedStaffFact($MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data)
	{
//		$this->addLog("start getTTRRecordsCountByMedStaffFact");
		$filters = ["ttr.Person_id is not null and ttr.TimetableType_id = 5"]; // считаем все бирки
		$queryParams = [];
		if (!empty($data["Resource_id"])) {
//			$this->addLog(" ! empty Resource_id");
			$filters[] = "ttr.Resource_id = :Resource_id";
			$queryParams["Resource_id"] = $data["Resource_id"];
		} else {
//			$this->addLog("empty Resource_id");
//			$this->addLog("stop1 getTTRRecordsCountByMedStaffFact");
			return 0;
		}
		$filters[] = "ttr.TimetableResource_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		if (!empty($MedStaffFact_id)) {
//			$this->addLog(" ! empty $MedStaffFact_id");
			// Запись от этого врача
			if (empty($data["From_MedStaffFact_id"])) {
				$filters[] = "ed.MedStaffFact_id = :MedStaffFact_sid";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
			} else {
				$filters[] = "
					(
						ed.MedStaffFact_id = :MedStaffFact_sid or
						ed.MedStaffFact_id in (
							select MedStaffFact_sid
							from MedStaffFactLink
							where MedStaffFact_id = :MedStaffFact_sid2
						)
					)
				";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
				$queryParams["MedStaffFact_sid2"] = $data["From_MedStaffFact_id"];
			}
		} elseif (!empty($LpuRegion_id)) {
//			$this->addLog(" ! empty $LpuRegion_id");
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffRegion msr 
					where msr.LpuRegion_id = :LpuRegion_sid 
					  and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= tzgetdate())
					  and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > tzgetdate())
				)
			";
			$queryParams["LpuRegion_sid"] = $LpuRegion_id;
		} elseif (!empty($LpuSection_id)) {
//			$this->addLog(" ! empty $LpuSection_id");
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffFact msf
					where msf.LpuSection_id = :LpuSection_sid
				)
			";
			$queryParams["LpuSection_sid"] = $LpuSection_id;
		}
		if (!empty($data["PayType_id"])) {
			$filters[] = "ed.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
//		$this->addLog("$filters = " . serialize($filters));
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$fromString = "
			v_TimetableResource_lite ttr
			left join v_EvnDirection_all ed on ttr.EvnDirection_id = ed.EvnDirection_id
		";
		$sql = "
			select count(1) as \"cnt\"
			from {$fromString}
			{$whereString}
		";
//		$this->addLog("sql = " . getDebugSQL($sql, $queryParams));
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
//			$this->addLog(" ! is_object $result");
//			$this->addLog("stop3 getTTRRecordsCountByMedStaffFact");
			return 0;
		}
//		$this->addLog("is_object $result");
		$res = $result->result("array");
//		$this->addLog("$res = " . serialize($res));
//		$this->addLog("stop2 getTTRRecordsCountByMedStaffFact");
		return $res[0]["cnt"];
	}

	/**
	 * @param $MedStaffFact_id
	 * @param $LpuSection_id
	 * @param $LpuRegion_id
	 * @param $data
	 * @return int
	 */
	function getTTMSRecordsCountByMedStaffFact($MedStaffFact_id, $LpuSection_id, $LpuRegion_id, $data)
	{
		$filters = ["ttms.Person_id is not null and ttms.TimetableType_id = 5"]; // считаем все бирки
		$queryParams = [];
		if (!empty($data["MedService_id"]) && empty($data["UslugaComplex_id"])) {
			$filters[] = "ttms.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["UslugaComplex_id"]) && empty($data["MedService_id"])) {
			$filters[] = "ttms.UslugaComplexMedService_id = :UslugaComplex_id";
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		if (!empty($data["UslugaComplex_id"]) && !empty($data["MedService_id"])) {
			$filters[] = "
				CASE WHEN ttms.MedService_id is not null
					THEN ttms.MedService_id
					ELSE ed.MedService_id
				END = :MedService_id
				AND
				CASE WHEN ttms.MedService_id is not null
					THEN ttms.UslugaComplexMedService_id
					ELSE ed.UslugaComplex_did
				END = :UslugaComplex_id
			";
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			$queryParams["MedService_id"] = $data["MedService_id"];
		}
		$filters[] = "ttms.TimeTableMedService_begTime::date between :TimetableQuoteRule_begDT and :TimetableQuoteRule_endDT";
		$queryParams["TimetableQuoteRule_begDT"] = $data["TimetableQuoteRule_begDT"];
		$queryParams["TimetableQuoteRule_endDT"] = $data["TimetableQuoteRule_endDT"];
		if (!empty($MedStaffFact_id)) {
			// Запись от этого врача
			if (empty($data["From_MedStaffFact_id"])) {
				$filters[] = "ed.MedStaffFact_id = :MedStaffFact_sid";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
			} else {
				$filters[] = "
					(
						ed.MedStaffFact_id = :MedStaffFact_sid or
						ed.MedStaffFact_id in (
							select MedStaffFact_sid
							from MedStaffFactLink
							where MedStaffFact_id = :MedStaffFact_sid2
						)
					)
				";
				$queryParams["MedStaffFact_sid"] = $MedStaffFact_id;
				$queryParams["MedStaffFact_sid2"] = $data["From_MedStaffFact_id"];
			}
		} elseif (!empty($LpuRegion_id)) {
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffRegion msr 
					where msr.LpuRegion_id = :LpuRegion_sid 
					  and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= tzgetdate())
					  and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > tzgetdate())
				)
			";
			$queryParams["LpuRegion_sid"] = $LpuRegion_id;
		} elseif (!empty($LpuSection_id)) {
			$filters[] = "
				ed.MedPersonal_id in (
					select MedPersonal_id
					from v_MedStaffFact msf
					where msf.LpuSection_id = :LpuSection_sid
				)
			";
			$queryParams["LpuSection_sid"] = $LpuSection_id;
		}
		if (!empty($data["PayType_id"])) {
			$filters[] = "ed.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		$fromString = "
			v_TimeTableMedService_lite ttms
			left join v_EvnDirection_all ed on ttms.EvnDirection_id = ed.EvnDirection_id
		";
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			select count(1) as \"cnt\"
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return 0;
		}
		$res = $result->result("array");
		return $res[0]["cnt"];
	}

	/**
	 * Сохранение правила квоты
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function saveQuoteRule($data)
	{
		$err = $this->checkSaveTimetableQuoteRuleSubject($data);
        if ( isset($err) ) {
            return array(
                'Error_Msg' => $err
            );
        }
		$proc = (isset($data["TimetableQuoteRule_id"])) ? "p_TimetableQuoteRule_upd" : "p_TimetableQuoteRule_ins";
		$selectString = "
			timetablequoterule_id as \"TimetableQuoteRule_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$proc}(
			    timetablequoterule_id := :TimetableQuoteRule_id,
			    timetablequotetype_id := :TimetableQuoteType_id,
			    lpusectionprofile_id := :LpuSectionProfile_id,
			    lpusection_id := :LpuSection_id,
			    medstafffact_id := :MedStaffFact_id,
			    timetablequoterule_begdt := :TimetableQuoteRule_begDT,
			    timetablequoterule_enddt := :TimetableQuoteRule_endDT,
			    lpuunit_id := :LpuUnit_id,
			    lpu_id := :Lpu_id,
			    medservice_id := :MedService_id,
			    resource_id := :Resource_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"TimetableQuoteRule_id" => $data["TimetableQuoteRule_id"],
			"TimetableQuoteType_id" => $data["TimetableQuoteType_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuUnit_id" => $data["LpuUnit_id"],
			"LpuSectionProfile_id" => $data["LpuSectionProfile_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"MedService_id" => $data["MedService_id"],
			"Resource_id" => $data["Resource_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"TimetableQuoteRule_begDT" => $data["TimetableQuoteRule_begDT"],
			"TimetableQuoteRule_endDT" => $data["TimetableQuoteRule_endDT"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД.");
		}
		$result = $result->result("array");
		if (isset($result[0]["Error_Msg"]) || !isset($result[0]["TimetableQuoteRule_id"])) {
			throw new Exception($result[0]["Error_Msg"]);
		}
		// Сохраняем субъектов квоты
		if (isset($data["TimetableQuoteRule_id"])) {
			//если редактируем существующее правило, то удаляем старых субъектов
			$err = $this->deleteTimetableQuoteRuleSubject($data["TimetableQuoteRule_id"]);
			if (isset($err)) {
				throw new Exception($err);
			}
			$err = $this->addTimetableQuoteRuleSubject($data["TimetableQuoteRule_id"], json_decode($data["rule_subjects"]), $data["pmUser_id"]);
			return ["Error_Msg" => $err];
		} else {
			$err = $this->addTimetableQuoteRuleSubject($result[0]["TimetableQuoteRule_id"], json_decode($data["rule_subjects"]), $data["pmUser_id"]);
			return ["Error_Msg" => $err];
		}
	}

	/**
	 * Удаление субъектов у заданного правила
	 * @param $TimetableQuoteRule_id
	 * @return bool
	 */
	function deleteTimetableQuoteRuleSubject($TimetableQuoteRule_id)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablequoterule_delsubjects(timetablequoterule_id := :TimetableQuoteRule_id);
		";
		$queryParams = ["TimetableQuoteRule_id" => $TimetableQuoteRule_id];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return $result[0]["Error_Msg"];
	}

	/**
	 * Добавление субъектов заданному правилу
	 * @param $TimetableQuoteRule_id
	 * @param $subjects
	 * @param $pmuser_id
	 * @return bool
	 */
	function addTimetableQuoteRuleSubject($TimetableQuoteRule_id, $subjects, $pmuser_id)
	{
		foreach ($subjects as $subject) {
			$sql = "
				select
					timetablequoterulesubject_id as \"TimetableQuoteRuleSubject_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Message\"
				from p_timetablequoterulesubject_ins(
				    timetablequoterule_id := :TimetableQuoteRule_id,
				    erterr_id := :ERTerr_id,
				    lpu_id := :Lpu_id,
				    timetablequote_amount := :TimetableQuote_Amount,
				    medstafffact_id := :MedStaffFact_id,
				    lpusection_id := :LpuSection_id,
				    lpuregion_id := :LpuRegion_id,
				    paytype_id := :PayType_id,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"TimetableQuoteRule_id" => $TimetableQuoteRule_id,
				"ERTerr_id" => !empty($subject->ERTerr_id) ? $subject->ERTerr_id : null,
				"Lpu_id" => !empty($subject->Lpu_id) ? $subject->Lpu_id : null,
				"LpuSection_id" => !empty($subject->LpuSection_id) ? $subject->LpuSection_id : null,
				"LpuRegion_id" => !empty($subject->LpuRegion_id) ? $subject->LpuRegion_id : null,
				"MedStaffFact_id" => !empty($subject->MedStaffFact_id) ? $subject->MedStaffFact_id : null,
				"PayType_id" => !empty($subject->PayType_id) ? $subject->PayType_id : null,
				"TimetableQuote_Amount" => (int) $subject->TimetableQuote_Amount,
				"pmUser_id" => $pmuser_id
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $sqlParams);
			if (!is_object($result)) {
				return false;
			}
			$result = $result->result("array");
			if (isset($result[0]["Error_Msg"])) {
				return $result[0]["Error_Msg"];
			}
		}
		return;
	}

	/**
	 * Загрузка правила квоты
	 * @param $data
	 * @return array|bool
	 */
	function getQuoteRule($data)
	{
		$sql = "
			select
				ttq.TimetableQuoteRule_id as \"TimetableQuoteRule_id\",
				ttq.TimetableQuoteType_id as \"TimetableQuoteType_id\",
				ttq.LpuUnit_id as \"LpuUnit_id\",
				ttq.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ttq.LpuSection_id as \"LpuSection_id\",
				ttq.MedStaffFact_id as \"MedStaffFact_id\",
				ttq.MedService_id as \"MedService_id\",
				ttq.Resource_id as \"Resource_id\",
				ttq.UslugaComplex_id as \"UslugaComplex_id\",
				ttqt.TimetableQuoteType_Name as \"TimetableQuoteType_Name\",
				to_char(ttq.TimetableQuoteRule_begDT, 'dd.mm.yyyy') as \"TimetableQuoteRule_begDT\",
				to_char(ttq.TimetableQuoteRule_endDT, 'dd.mm.yyyy') as \"TimetableQuoteRule_endDT\",
				case
					when ttq.Resource_id is not null and ttq.MedService_id is not null then 4
					when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 5
					when ttq.MedStaffFact_id is not null then 3
					when ttq.LpuSection_id is not null then 2
					else 1
				end as \"QuoteType_id\"
			from
				v_TimetableQuoteRule ttq
				left join v_LpuSectionProfile lsp on ttq.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				left join v_LpuSection ls on ttq.LpuSection_id = ls.LpuSection_id
				left join v_MedStaffFact msf on ttq.MedStaffFact_id = msf.MedStaffFact_id
				left join v_TimetableQuoteType ttqt on ttq.TimetableQuoteType_id = ttqt.TimetableQuoteType_id 
			where TimetableQuoteRule_id = :TimetableQuoteRule_id
			limit 1
		";
		$queryParams = ["TimetableQuoteRule_id" => $data["TimetableQuoteRule_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка субъектов квоты
	 * @param $data
	 * @return array|bool
	 */
	function getQuoteRuleSubjects($data)
	{
		$sql = "
			select
				tqrs.ERTerr_id as \"ERTerr_id\",
				tqrs.Lpu_id as \"Lpu_id\",
				tqrs.MedStaffFact_id as \"MedStaffFact_id\",
				tqrs.PayType_id as \"PayType_id\",
				PT.PayType_Name as \"PayType_Name\",
				TimetableQuote_Amount as \"TimetableQuote_Amount\",
				et.ERTerr_Name as \"ERTerr_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				msf.Person_FIO as \"MedStaffFact_FIO\",
				case
					when tqrs.ERTerr_id is not null then 2
					when tqrs.MedStaffFact_id is not null then 2
					when tqrs.LpuRegion_id is not null then 3
					else 1
				end as \"SubjectType_id\",
				ls.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				lr.LpuRegion_id as \"LpuRegion_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\"
			from
				v_TimetableQuoteRuleSubject tqrs
				left join v_ERTerr et on et.ERTerr_id = tqrs.ERTerr_id
				left join v_Lpu l on l.Lpu_id = tqrs.Lpu_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = tqrs.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = tqrs.LpuSection_id
				left join v_LpuRegion lr on lr.LpuRegion_id = tqrs.LpuRegion_id
				left join v_PayType PT on PT.PayType_id=tqrs.PayType_id
			where TimetableQuoteRule_id = :TimetableQuoteRule_id
		";
		$queryParams = ["TimetableQuoteRule_id" => $data["TimetableQuoteRule_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Удаление правила квоты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function deleteQuoteRule($data)
	{
		$err = $this->deleteTimetableQuoteRuleSubject($data["TimetableQuoteRule_id"]);
		if (isset($err)) {
			throw new Exception($err);
		}
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablequoterule_del(timetablequoterule_id := :TimetableQuoteRule_id);
		";
		$sqlParams = ["TimetableQuoteRule_id" => $data["TimetableQuoteRule_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$result = $res->result("array");
		return ["Error_Msg" => $result[0]["Error_Msg"]];
	}

	/**
	 * Проверка, что запись на текущую бирку не нарушит никаких правил квоты
	 * @param $data
	 * @param $record_data
	 * @param string $mode
	 * @return bool|string
	 */
	function checkTimetableQuote($data, $record_data, $mode = 'ttg')
	{
//		$this->addLog("start checkTimetableQuote");
//		$this->addLog("data = " . serialize($data));
//		$this->addLog("record_data = " . serialize($record_data));
//		$this->addLog("mode = " . serialize($mode));

		$record_dataString = json_encode($record_data);
		$limit = "";
		$filters = [];
		$queryParams = [];
		$rows = [];
		$PayType_Name = "";//наименование вида оплаты (для сообщения)

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

		if (!empty($data["EvnDirection_IsCito"]) && $data["EvnDirection_IsCito"] == 2) {
			if($this->getRegionNick()!='kareliya') { //#183266
				$this->addLog("EvnDirection_IsCito == 2");
				$this->addLog("stop1 checkTimetableQuote");
				return true;
			} else {
				if ( $record_data['TimetableType_id'] != 5){ //#183266
					$this->addLog("EvnDirection_IsCito == 2");
					$this->addLog("stop1 checkTimetableQuote");
					return true;
				}
			}
		}
		if ($data["Lpu_id"] == $record_data["Lpu_id"]) {
//			$this->addLog("data Lpu_id == {$record_dataString} Lpu_id");
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
			$filters[] = "ttqr.TimetableQuoteType_id in (3,4)";
			// Пользователь не имеет доступ к АРМ регистратора или Call-центра, если имеет, то проверка завершается удачно.
			// (Записи регистраторов и операторов Call-центра не квотируются в своей МО)
			if (
				!empty($data["session"]["ARMList"]) &&
				is_array($data["session"]["ARMList"]) &&
				(
					in_array("regpol", $data["session"]["ARMList"]) ||
					in_array("callcenter", $data["session"]["ARMList"])
				)
			) {
				if($this->getRegionNick()!='kareliya') { //#183266
					$this->addLog("ARMList == regpol or ARMList == callcenter");
					$this->addLog("stop2 checkTimetableQuote");
					return true;
				} else {
					if($record_data['TimetableType_id'] != 5){ //#183266
						$this->addLog("ARMList == regpol or ARMList == callcenter");
						$this->addLog("stop2 checkTimetableQuote");
						return true;
					}
				}
			}
		} else {
//			$this->addLog("data Lpu_id != {$record_dataString} Lpu_id");
			//первичная бирка доступна только для Регистраторов своей МО
			if($this->getRegionNick()=='kareliya' && $record_data['TimetableType_id'] == 16) {
				return 'Запись невозможна. Бирка доступна только регистраторами своей МО';
			}
			// Если запись происходит в чужую МО, то интересны только внешние квоты
			$filters[] = "ttqr.TimetableQuoteType_id in (1,2)";
		}
		if (!empty($record_data["LpuUnit_id"])) {
//			$this->addLog(" ! empty LpuUnit_id");
			$filters[] = "coalesce(ttqr.LpuUnit_id, :LpuUnit_id) = :LpuUnit_id";
			$queryParams["LpuUnit_id"] = $record_data["LpuUnit_id"];
		}
		if (!empty($record_data["Lpu_id"])) {
//			$this->addLog(" ! empty Lpu_id");
			$filters[] = "coalesce(ttqr.Lpu_id, :Lpu_id) = :Lpu_id";
			$queryParams["Lpu_id"] = $record_data["Lpu_id"];
		}
		$queryParams["Timetable_Date"] = $record_data["Timetable_Date"];
		$filters[] = ":Timetable_Date::date between ttqr.TimetableQuoteRule_begDT and ttqr.TimetableQuoteRule_endDT";
//		$this->addLog("filters = " . serialize($filters));
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$base_sql = "
			select
				ttqr.TimetableQuoteType_id as \"TimetableQuoteType_id\",
				to_char(ttqr.TimetableQuoteRule_begDT, 'dd.mm.yyyy') as \"TimetableQuoteRule_begDT\",
				to_char(ttqr.TimetableQuoteRule_endDT, 'dd.mm.yyyy') as \"TimetableQuoteRule_endDT\",
				ttqr.UslugaComplex_id as \"UslugaComplex_id\",
				ttqrs.Lpu_id as \"Lpu_id\",
				ttqrs.ERTerr_id as \"ERTerr_id\",
				ttqrs.TimetableQuote_Amount as \"TimetableQuote_Amount\",
				ttqrs.LpuSection_id as \"LpuSection_id\",
				ttqrs.LpuRegion_id as \"LpuRegion_id\",
				ttqrs.MedStaffFact_id as \"MedStaffFact_id\",
				ttqrs.PayType_id as \"PayType_id\",
				pt.PayType_Name as \"PayType_Name\",
				case
					when ttqr.UslugaComplex_id is not null then 5
					when ttqr.Resource_id is not null then 4
					when ttqr.MedStaffFact_id is not null then 3
					when ttqr.LpuSection_id is not null then 2
					else 1
				end as \"QuoteType_id\"
			from
				v_TimetableQuoteRule ttqr
				left join v_TimetableQuoteRuleSubject ttqrs on ttqrs.TimetableQuoteRule_id = ttqr.TimetableQuoteRule_id 
				left join v_PayType pt on pt.PayType_id = ttqrs.PayType_id
			{$whereString}
		";
//		$this->addLog("mode = " . $mode);
		if ($mode == "ttr") {
			// TimetableResource
//			$this->addLog("mode = ttr");
			// Начинаем искать одну квоту, самую детальную, по ресурсу
			$sql = $base_sql . " and ttqr.Resource_id = :Resource_id";
			$queryParams["Resource_id"] = $record_data["Resource_id"];
//			$this->addLog(getDebugSQL($sql, $queryParams));
			/**@var CI_DB_result $result */
			$result = $this->db->query($sql, $queryParams);
			if (!is_object($result)) {
//				$this->addLog(" ! is_object $result");
//				$this->addLog("stop3 checkTimetableQuote");
				return false;
			}
			$rows = $result->result("array");
//			$this->addLog("is_object result. rows = " . serialize($rows));
            // закоментировал, т.к. блокирует всю службу, а не конкретный ресурс #192584
			/*if (count($rows) == 0 && !empty($record_data["MedService_id"])) {
				// тогда ищем по службе (ресурс тоже относится к ней)
				$sql = $base_sql . " and ttqr.MedService_id = :MedService_id";
				$queryParams["MedService_id"] = $record_data["MedService_id"];
				$result = $this->db->query($sql, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$rows = $result->result("array");
			}*/
		} elseif ($mode == "ttms") {
			// TimetableMedService
			// так как поле "Услуга" в форме НЕ обязательное
			if (isset($record_data["UslugaComplex_id"]) && !empty($record_data["UslugaComplex_id"])) {
				// ищем по услуге
				$sql = $base_sql . " and ttqr.UslugaComplex_id = :UslugaComplex_id";
				$queryParams["UslugaComplex_id"] = $record_data["UslugaComplex_id"];
				$result = $this->db->query($sql, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$rows = $result->result("array");
			}
			if (count($rows) == 0) {
				// ищем по службе
				$whereUsluga = "";
				if (isset($data["UslugaComplex_id"]) && !empty($data["UslugaComplex_id"])) {
					$whereUsluga = " and coalesce(ttqr.UslugaComplex_id, :UslugaComplex_id) = :UslugaComplex_id ";
					$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
				}
				$sql = $base_sql . $whereUsluga . " 
					and (
						ttqr.MedService_id = :MedService_id or
						ttqr.MedService_id in (
							select msl.MedService_lid
							from
								v_MedServiceLink msl
								left join v_MedService ms on ms.MedService_id = msl.MedService_id
								left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
							where msl.MedService_id = :MedService_id
							  and mst.MedServiceType_Code in (1)
						)
					)
				";
				$queryParams["MedService_id"] = $record_data["MedService_id"];
				$result = $this->db->query($sql, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$rows = $result->result("array");
			}
		} else {
			// TimetableGraf
			// Начинаем искать одну квоту, самую детальную, по врачу
			$sql = $base_sql . " and ttqr.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $record_data["MedStaffFact_id"];
			$result = $this->db->query($sql, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			$rows = $result->result("array");
			if (count($rows) == 0) {
				// ищем дальше, теперь по отделению
				$sql = $base_sql . " and ttqr.LpuSection_id = :LpuSection_id";
				$queryParams["LpuSection_id"] = $record_data["LpuSection_id"];
				$result = $this->db->query($sql, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$rows = $result->result("array");
				if (count($rows) == 0) {
					// если и по отделению не нашлось, ищем последнюю, по профилю
					$sql = $base_sql . " and ttqr.LpuSectionProfile_id = :LpuSectionProfile_id";
					$queryParams["LpuSectionProfile_id"] = $record_data["LpuSectionProfile_id"];
					$result = $this->db->query($sql, $queryParams);
					if (!is_object($result)) {
						return false;
					}
					$rows = $result->result("array");
				}
			}
		}
		if (count($rows) == 0) {
//			$this->addLog("count(rows) == 0");
//			$this->addLog("stop4 checkTimetableQuote");
			// если не существует квота на дату записи (по профилю, по отделению, врачу), то проверка завершается удачно.
			if($this->getRegionNick()=='kareliya' && $record_data['TimetableType_id'] == 5) { //#183266
				return 'Запись невозможна. На данную бирку нет наличия квот';
			} else {
				return true;
			}
		}
		// квотируются только бирки по направлению
		if ($record_data["TimetableType_id"] != 5) {
//			$this->addLog("record_data TimetableType_id != 5");
//			$this->addLog("stop5 checkTimetableQuote");
			return true;
		}
		/**
		 * @var DateTime $TimetableQuoteRule_begDT
		 * @var DateTime $TimetableQuoteRule_endDT
		 */
		$TimetableQuoteRule_begDT = ConvertDateFormat($rows[0]["TimetableQuoteRule_begDT"], 'd.m.Y');
		$TimetableQuoteRule_endDT = ConvertDateFormat($rows[0]["TimetableQuoteRule_endDT"], 'd.m.Y');
		$record_data["TimetableQuoteRule_begDT"] = $TimetableQuoteRule_begDT->format("Y-m-d");
		$record_data["TimetableQuoteRule_endDT"] = $TimetableQuoteRule_endDT->format("Y-m-d");
		$allow = (in_array($rows[0]["TimetableQuoteType_id"], [1, 3])); // Для разрещающей квоты по умолчанию считаем, что запись разрешена, для запрещающей наоборот
//		$this->addLog("allow = " . serialize($allow));
		if ($data["Lpu_id"] == $record_data["Lpu_id"]) {
//			$this->addLog("data Lpu_id == record_data Lpu_id");
			// Если запись происходит в свою МО, то работает следующий алгоритм:
			// Если квота «разрешающая внутренняя» и в ней нет врача, к которому привязан пользователь, то проверка завершается удачно
			// Если квота «запрещающая внутренняя» и в ней нет врача, к которому привязан пользователь, то проверка завершается сообщением вида, запись в запрещена
			$this->load->model("User_model", "User_model");
			$medstafffacts = $this->User_model->getMedStaffFactsBypmUser($data["pmUser_id"]); // места работы пользователя
//			$this->addLog("medstafffacts count = " . sizeof($medstafffacts) . ". " . serialize($medstafffacts));
			$hasMedStaffFact = false;
			if (count($medstafffacts) > 0) {
//				$this->addLog("count(medstafffacts) > 0");
				$inner_allow = true;
				foreach ($rows as $row) {
					// если это правило по месту работы, которое есть у врача, привязанного к текущему записывающему пользователю
//					$this->addLog("data MedStaffFact_id = " . serialize($data["MedStaffFact_id"]));
//					$this->addLog("row MedStaffFact_id = " . serialize($row["MedStaffFact_id"]));
					//#117075, #144950
					//если в квоте указан врач, иначе по месту работы
					if ($data["MedStaffFact_id"] == $row["MedStaffFact_id"] || (!$row["MedStaffFact_id"] && $this->checkTimetableQuoteUser($row, $data["pmUser_id"]))) {
//						$this->addLog("data MedStaffFact_id = row MedStaffFact_id");
						$hasMedStaffFact = true;
						// Число записей за период указанный в этой квоте, сделанных всеми пользователями привязанными
						// к врачу, к которому привязан текущий пользователь, удовлетворяющих атрибутам квоты, меньше или
						// равно числу записей указанных в квоте, проверка завершается удачно.
						$record_data_params = $record_data;
						if (!empty($row["PayType_id"])) {
							if ($row["PayType_id"] != $data["PayType_id"]) continue;
							$record_data_params["PayType_id"] = $data["PayType_id"];
						}
						if ($mode == "ttr") {
//							$this->addLog("mode == ttr");
							$inner_allow = ($this->getTTRRecordsCountByMedStaffFact($row["MedStaffFact_id"], $row["LpuSection_id"], $row["LpuRegion_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						} else if ($mode == "ttms") {
//							$this->addLog("mode == ttms");
							$inner_allow = ($this->getTTMSRecordsCountByMedStaffFact($row["MedStaffFact_id"], $row["LpuSection_id"], $row["LpuRegion_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						} else {
//							$this->addLog("mode else");
							$inner_allow = ($this->getTTGRecordsCountByMedStaffFact($row["MedStaffFact_id"], $row["LpuSection_id"], $row["LpuRegion_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						}
						if (!$inner_allow and !empty($row["PayType_id"])) {
							$PayType_Name = $row["PayType_Name"];
						}
//						$this->addLog("inner_allow = " . serialize($inner_allow));
						$limit = $row["TimetableQuote_Amount"];
//						$this->addLog("TimetableQuote_Amount = " . serialize($limit));
					}
					if ($inner_allow === false) {
						break;
					}
				}
				if ($inner_allow === false) {
//					$this->addLog('inner_allow === false');
//					$this->addLog('stop6 checkTimetableQuote');
					// Иначе возвращается сообщение, о том, что запись невозможна из-за превышения квоты.
					$TimetableQuoteRule_begDT = ConvertDateFormat($rows[0]["TimetableQuoteRule_begDT"], 'd.m.Y');
					$TimetableQuoteRule_endDT = ConvertDateFormat($rows[0]["TimetableQuoteRule_endDT"], 'd.m.Y');
					return
						"Запись невозможна. Превышен лимит " .
						(empty($PayType_Name) ? ($this->getRegionNick() == "kz" ? "квоты" : "общей квоты") : "квоты по виду оплаты " . $PayType_Name) .
						" для врача в {$limit} записи(ей) на интервале " . $TimetableQuoteRule_begDT->format("d.m.Y") . " - " . $TimetableQuoteRule_endDT->format("d.m.Y") . ".";
				}
			}
			$allow = $allow || $hasMedStaffFact; // если есть врач в запрещающей квоте, значит разрешаем запись.
		} else {
//			$this->addLog("data Lpu_id != record_data Lpu_id");
			// Если запись происходит в чужую МО, то работает следующий алгоритм:
			$inQuoteFound = false;
			// Если квота «разрешающая внешняя» и в ней нет ЛПУ (территории), где работает пользователь, то проверка завершается удачно.
			// Если квота «запрещающая внешняя» и в ней нет ЛПУ (территории), где работает пользователь, то проверка завершается сообщением вида, запись в ЛПУ запрещена
			$ERTerrs = $this->getTerrsByLpu($data["Lpu_id"]);
			foreach ($rows as $row) {
				if ($row["TimetableQuoteType_id"] < 3) {
					$arrRecData = $record_data;
					if (!empty($record_data["MedStaffFact_id"]) && !empty($row["QuoteType_id"]) && $row["QuoteType_id"] == 1) {
						// если учитываем по профилю целиком, то исключим MedStaffFact_id
						unset($arrRecData["MedStaffFact_id"]);
					}
					$record_data_params = $arrRecData;
					if (!empty($row["PayType_id"])) {
						if ($data["PayType_id"] != $row["PayType_id"]) continue;
						$record_data_params["PayType_id"] = $row["PayType_id"];
					}
					// Если в квоте есть ЛПУ (территория), где работает пользователь, считается количество записей (на период, в котором лежит записываемая бирка, по свойствам правила квоты (подразделение, профиль, отделение, врач)) через направления от ЛПУ, где работает текущий пользователь, если оно меньше чем квота, то проверка завершается удачно;
					if ($row["Lpu_id"] == $data["Lpu_id"]) {
						$inQuoteFound = true;
						if ($mode == "ttr") {
							$allow = ($this->getTTRRecordsCountByLpu($row["Lpu_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						} else if ($mode == "ttms") {
							$allow = ($this->getTTMSRecordsCountByLpu($row["Lpu_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						} else {
							$allow = ($this->getTTGRecordsCountByLpu($row["Lpu_id"], $record_data_params) < $row["TimetableQuote_Amount"]);
						}
						if (!$allow) $PayType_Name = $row["PayType_Name"];
						break; // самое детальное правило уже нашлось, можно выходить
					}
					if (in_array($row["ERTerr_id"], $ERTerrs)) {
						$inQuoteFound = true;
						if ($mode == "ttr") {
							$allow = ($this->getTTRRecordsCountByTerr($ERTerrs, $record_data_params) < $row["TimetableQuote_Amount"]);
						} else if ($mode == "ttms") {
							$allow = ($this->getTTMSRecordsCountByTerr($ERTerrs, $record_data_params) < $row["TimetableQuote_Amount"]);
						} else {
							$allow = ($this->getTTGRecordsCountByTerr($ERTerrs, $record_data_params) < $row["TimetableQuote_Amount"]);
						}
						if (!$allow) $PayType_Name = $row["PayType_id"];
						// а вот тут рано останавливаться, может найтись более детальное правило, по конкретной ЛПУ
					}

				}
			}
			if (!$inQuoteFound && !$allow) {
				// если запрещающая квота и нет субъекта в квоте, то "Запись в МО запрещена"
				if ($this->getRegionNick() == "kz") {
					return "Запись невозможна. В МО установлена запрещающая внешняя квота.";
				}
				return "Запись невозможна из-за превышения " . (empty($PayType_Name) ? "внешней общей квоты" : "внешней квоты по виду оплаты " . $PayType_Name);
			}
		}
//		$this->addLog("end checkTimetableQuote. Result = " . serialize($allow));
		return $allow;
	}

	/**
	 * Проверка, попадает ли пользователь под квоту
	 * @param $data
	 * @param $pmUser_id
	 * @return bool
	 */
	function checkTimetableQuoteUser($data, $pmUser_id)
	{
		$filters = [];
		$queryParams = [];
		$queryParams["pmUser_id"] = $pmUser_id;
		if (!empty($data["MedStaffFact_id"])) {
			$filters[] = "msf.MedStaffFact_id = :MedStaffFact_id";
			$queryParams["MedStaffFact_id"] = $data["MedStaffFact_id"];
		} elseif (!empty($data["LpuRegion_id"])) {
			$filters[] = "
				msf.MedStaffFact_id in (
					select MedStaffFact_id
					from v_MedStaffRegion msr 
					where msr.LpuRegion_id = :LpuRegion_id 
					  and (MedStaffRegion_begDate is null or MedStaffRegion_begDate <= tzgetdate())
					  and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > tzgetdate())
				)
			";
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
		} elseif (!empty($data["LpuSection_id"])) {
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		$whereString = (count($filters) != 0) ? " and " . implode(" and ", $filters) : "";
		$sql = "
			select msf.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFact msf 
				inner join v_pmUser pu on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
			where (
				pu.pmUser_id = :pmUser_id or 
				msf.MedPersonal_id in (
					select msf1.MedPersonal_id
					from
						v_MedStaffFact msf
						inner join MedStaffFactLink msfl on msf.MedStaffFact_id = msfl.MedStaffFact_sid
						inner join v_MedStaffFact msf1 on msf1.MedStaffFact_id = msfl.MedStaffFact_id
						inner join v_pmUser pu on pu.pmUser_MedPersonal_id = msf.MedPersonal_id
					where pu.pmUser_id = :pmUser_id
				)
			)
			{$whereString}
		";
		$res = $this->queryResult($sql, $queryParams);
		return count($res) > 0;
	}

	/**
	 * Получение списка территорий обслуживаемых ЛПУ
	 * @param $Lpu_id
	 * @return array|bool
	 */
	function getTerrsByLpu($Lpu_id)
	{
		$sql = "
			select distinct
				ERTErr_id as \"ERTErr_id\"
			from
				v_LpuUnit lu
				left outer join Address lua on lu.Address_id = lua.Address_id
				inner join ERTerr Terr on (
					((lua.KLCountry_id = Terr.KLCountry_id) or coalesce(lua.KLCountry_id, Terr.KLCountry_id) is null) and
					((lua.KLRGN_id = Terr.KLRGN_id) or coalesce(lua.KLRGN_id, Terr.KLRGN_id) is null) and
					((lua.KLSubRGN_id = Terr.KLSubRGN_id) or coalesce(lua.KLSubRGN_id, Terr.KLSubRGN_id) is null) and
					((lua.KLCity_id = Terr.KLCity_id) or coalesce(lua.KLCity_id, Terr.KLCity_id) is null) and
					((lua.KLTown_id = Terr.KLTown_id) or coalesce(lua.KLTown_id, Terr.KLTown_id) is null)
				) and lua.KLCountry_id is not null and lua.KLRGN_id is not null
			where lu.Lpu_id = :Lpu_id
		";
		$queryParams = ["Lpu_id" => $Lpu_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$rows = $result->result("array");
		$res = [];
		foreach ($rows as $row) {
			$res[] = $row["ERTErr_id"];
		}
		return $res;
	}

	/**
	 * Автоматические системные уведомления по остаткам квот
	 * @return bool
	 */
	function QuoteNoticeSend()
	{
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime", 86400);
		ini_set("session.cookie_lifetime", 86400);

		$this->load->library("textlog", array("file" => "QuoteNoticeSend_" . date("Y-m-d") . ".log"));
//		$this->addLog("Запуск рассылки уведомлений регистраторам МО по остаткам квот");
		$this->load->model("Messages_model", "Messages_model");
		$sql = "
			select distinct
				ttqr.Lpu_id as \"Lpu_id\"
			from v_TimetableQuoteRule ttqr 
			where tzgetdate() between ttqr.TimetableQuoteRule_begDT and (ttqr.TimetableQuoteRule_endDT + interval '1 day')
		";
		//получаем список МО где есть действующие квоты
		$resp = $this->db->query($sql);
		if (!is_object($resp)) {
//			$this->addLog("Ошибка при определении списка МО с действующими квотами.");
			return false;
		}
		$lpulist = $resp->result("array");
//		$this->addLog("Найдено " . count($lpulist) . " МО где есть действующие квоты");
		foreach ($lpulist as $lpu) {
			//для каждого МО собираем информацию об остатках квот
//			$this->addLog("Читаем квоты для Lpu_id = " . $lpu["Lpu_id"]);
			//по типам квот (разреш.внеш, запрещ.внеш, разреш.внутр, запрещ.внутр)
			$Qdata = ["1" => [], "2" => [], "3" => [], "4" => []];
			$params = ["Lpu_id" => $lpu["Lpu_id"]];
			//Читаем список действующих квот в МО:
			$sql = "
				select
					ttq.TimeTableQuoteRule_id as \"TimeTableQuoteRule_id\",
					ttq.TimeTableQuoteType_id as \"TimeTableQuoteType_id\",
					to_char(ttq.TimetableQuoteRule_begDT, '{$this->dateTimeForm120}') as \"begDT120\",
					to_char(ttq.TimetableQuoteRule_endDT, '{$this->dateTimeForm120}') as \"endDT120\",
					to_char(ttq.TimetableQuoteRule_begDT, '{$this->dateTimeForm104}') as \"begDT104\",
					to_char(ttq.TimetableQuoteRule_endDT, '{$this->dateTimeForm104}') as \"endDT104\",
					ttqs.TimeTableQuote_Amount as \"TimeTableQuote_Amount\",
					coalesce(L.Lpu_Nick, ER.ERTerr_Name, msf.Person_FIO, subjLR.LpuRegion_Name, LS.LpuSection_Name) as \"subjName\",
					ttq.Lpu_id as \"Lpu_id\",
					ttqs.Lpu_id as \"SubjLpu_id\",
					ttq.LpuUnit_id as \"LpuUnit_id\",
					ttq.MedService_id as \"MedService_id\",
					ttq.Resource_id as \"Resource_id\",
					ttq.UslugaComplex_id as \"UslugaComplex_id\",
					ttq.MedStaffFact_id as \"MedStaffFact_id\",
					ttq.LpuSection_id as \"LpuSection_id\",
					ttq.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LS.LpuSectionProfile_id as \"LpuSectionProfile_id2\",
					LU.LpuUnit_Name as \"LpuUnit_Name\",
					ttqs.MedStaffFact_id as \"subj_MedStaffFact_id\",
					ttqs.LpuSection_id as \"subj_LpuSection_id\",
					ttqs.LpuRegion_id as \"subj_LpuRegion_id\",
					ttqs.PayType_id as \"subj_PayType_id\",
					subjPT.PayType_Name as \"PayType_Name\",
					ER.ERTerr_id as \"ERTerr_id\",
					case 
						when ttq.LpuSectionProfile_id is not null then 'Профиль: '||LSP.LpuSectionProfile_Name
						when ttq.MedStaffFact_id is not null then 'Врач: '||msf.Person_FIO||coalesce(' ['||msf_ls.LpuSection_Code::varchar||'. '||msf_ls.LpuSection_Name||']', '')
						when ttq.LpuSection_id is not null then 'Отделение: '||LS.LpuSection_Name
						when ttq.UslugaComplex_id is not null or ttq.MedService_id is not null then 'Служба: '||coalesce(ms2.MedService_Name, '')||coalesce(' ['||UC.UslugaComplex_Name||']', '')
						when ttq.Resource_id is not null and ttq.MedService_id is not null then 'Ресурс: '||res.Resource_Name||coalesce(' ['||ms.MedService_Nick||']', '')
					end as \"TimetableQuote_Object\"
				from v_TimetableQuoteRule ttq
					left join v_TimetableQuoteRuleSubject ttqs on ttqs.TimetableQuoteRule_id = ttq.TimetableQuoteRule_id
					left join v_PayType subjPT on subjPT.PayType_id = ttqs.PayType_id
					left join v_LpuSection subjLS on subjLS.LpuSection_id = ttqs.LpuSection_id
					left join v_LpuRegion subjLR on subjLR.LpuRegion_id = ttqs.LpuRegion_id
					left join v_Resource res on res.Resource_id = ttq.Resource_id
					left join v_MedService ms on ms.MedService_id = res.MedService_id
					left join v_MedService ms2 on ms2.MedService_id = ttq.MedService_id
					left join v_UslugaComplex UC on UC.UslugaComplex_id = ttq.UslugaComplex_id
					left join v_LpuUnit LU on LU.LpuUnit_id = ttq.LpuUnit_id
					left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ttq.LpuSectionProfile_id
					left join v_LpuSection LS on LS.LpuSection_id = ttq.LpuSection_id
					left join v_MedStaffFact msf on msf.MedStaffFact_id = ttq.MedStaffFact_id
					left join v_LpuSection msf_ls on msf.LpuSection_id = msf_ls.LpuSection_id
					left join v_ERTerr ER on ER.ERTerr_id = ttqs.ERTerr_id
					left join v_Lpu L on L.Lpu_id = ttqs.Lpu_id
				where ttq.Lpu_id = :Lpu_id and tzgetdate() between ttq.TimeTableQuoteRule_begDT and ttq.TimeTableQuoteRule_endDT
			";
			$resp = $this->db->query($sql, $params);
			if (!is_object($resp)) {
//				$this->addLog("Пропускаем Lpu_id = " . $lpu["Lpu_id"] . " : ошибка запроса " . getDebugSQL($sql, $params));
				continue;
			}
			$quotes = $resp->result("array");
			foreach ($quotes as $q) {
				$filters[] = "ttr.Person_id is not null and ttr.TimetableType_id = 5";
				$records_count = 0;
				$object_name = $q["TimetableQuote_Object"];
				$subject_name = $q["subjName"];
				$limit = $q["TimeTableQuote_Amount"];
//				$this->addLog("TimeTableQuoteRule_id=" . $q["TimeTableQuoteRule_id"] . " (" . ($q["TimeTableQuoteType_id"] < 3 ? "Внешняя квота " : "Внутренняя квота") . ") Лимит $limit. Начало квоты: " . $q["begDT104"] . ". Окончание: " . $q["endDT104"] . ". Объект квотирования: $object_name. Субъект: $subject_name.");
				$data = [
					"TimetableQuoteRule_begDT" => $q["begDT120"],
					"TimetableQuoteRule_endDT" => $q["endDT120"],
					"Lpu_id" => $lpu["Lpu_id"],
					"LpuUnit_id" => $q["LpuUnit_id"],
					"LpuSectionProfile_id" => !empty($q["LpuSectionProfile_id"]) ? $q["LpuSectionProfile_id"] : $q["LpuSectionProfile_id2"],
					"LpuSection_id" => $q["LpuSection_id"],
					"MedStaffFact_id" => $q["MedStaffFact_id"],
					"MedService_id" => $q["MedService_id"],
					"Resource_id" => $q["Resource_id"],
					"UslugaComplex_id" => $q["UslugaComplex_id"],
					"PayType_id" => $q["subj_PayType_id"]
				];
				if ($q["TimeTableQuoteType_id"] < 3) {
					//внешние квоты
					//Если МО объекта квоты == МО субъекта, то квоту не включаем в отчет, т.к. в этих случаях бирки фактически внутренние, а не внешние, т.е. не лимитируются
					if (!empty($q["Resource_id"])) {
						if (!empty($q["ERTerr_id"])) {
							$records_count = $this->getTTRRecordsCountByTerr($q["ERTerr_id"], $data);
//							$this->addLog("... Resource_id = " . $q["Resource_id"] . " ; getTTRRecordsCountByTerr = " . $records_count);
						} else if (!empty($q["Lpu_id"])) {
							if ($q["Lpu_id"] == $q["SubjLpu_id"]) continue;
							$records_count = $this->getTTRRecordsCountByLpu($q["SubjLpu_id"], $data);
//							$this->addLog("... Resource_id = " . $q["Resource_id"] . " ; getTTRRecordsCountByLpu = " . $records_count);
						}
					} else {
						if (!empty($q["ERTerr_id"])) {
							$records_count = $this->getTTGRecordsCountByTerr($q["ERTerr_id"], $data);
//							$this->addLog("... getTTGRecordsCountByTerr = " . $records_count);
						} else if (!empty($q["Lpu_id"])) {
							if ($q["Lpu_id"] == $q["SubjLpu_id"]) continue;
							$records_count = $this->getTTGRecordsCountByLpu($q["SubjLpu_id"], $data);
//							$this->addLog("... getTTGRecordsCountByLpu = " . $records_count);
						}
					}
				} else {
					//внутренние квоты
					$countTTR = $this->getTTRRecordsCountByMedStaffFact($q["subj_MedStaffFact_id"], $q["subj_LpuSection_id"], $q["subj_LpuRegion_id"], $data);
					$countTTMS = $this->getTTMSRecordsCountByMedStaffFact($q["subj_MedStaffFact_id"], $q["subj_LpuSection_id"], $q["subj_LpuRegion_id"], $data);
					$countTTG = $this->getTTGRecordsCountByMedStaffFact($q["subj_MedStaffFact_id"], $q["subj_LpuSection_id"], $q["subj_LpuRegion_id"], $data);
					$records_count = $countTTR + $countTTMS + $countTTG;
//					$this->addLog("Бирок: {$records_count} (ttr={$countTTR}; ttms={$countTTMS};ttg={$countTTG}) Остаток=" . ($limit - $records_count) . " ; Resource_id=" . $q["Resource_id"] . " ; UslugaComplex_id=" . $q["UslugaComplex_id"] . " ;");
				}
				$Qdata[$q["TimeTableQuoteType_id"]][] = [
					"value" => ($limit - $records_count),//остаток
					"LpuUnit_Name" => $q["LpuUnit_Name"],//подразделение
					"Object_Name" => $object_name,//наименование объекта квотирования
					"begDT" => $q["begDT104"],//начало действия квоты
					"endDT" => $q["endDT104"],//окончание действия квоты
					"Subject_Name" => $subject_name, //наименование субъекта
					"PayType_Name" => $q["PayType_Name"] //вид оплаты
				];
			}
			$msg = "";
			foreach ($Qdata as $key => $QuotesByType) {
				if (count($QuotesByType) > 0) {
					switch ($key) {
						case "1":
							$msg .= "<b>Разрешающая внешняя</b>";
							break;
						case "2":
							$msg .= "<b>Запрещающая внешняя</b>";
							break;
						case "3":
							$msg .= "<b>Разрешающая внутренняя</b>";
							break;
						case "4":
							$msg .= "<b>Запрещающая внутренняя</b>";
							break;
					}
					$msg .= "
						<table cellspacing='0' border='1' class='table-in-message'>
							<tr>
								<td>№</td>
								<td>Подразделение</td>
								<td>Объект квотирования</td>
								<td>Начало действия квоты</td>
								<td>Окончание действия квоты</td>
								<td>" . ($key < 3 ? "МО<br>Территория" : "
									Отделение<br>
									Отделение/Участок<br>
									Отделение/Врач
								") . "</td>
								<td>Вид оплаты</td>
								<td>Остаток квоты</td>
							</tr>
					";
					$i = 0;
					foreach ($QuotesByType as $quote) {
						$i += 1;
						$msg .= "<tr>";
						$msg .= "<td>$i</td><td>" . $quote['LpuUnit_Name'] . "</td><td>" . $quote['Object_Name'] . "</td><td>" . $quote['begDT'] . "</td><td>" . $quote['endDT'] . "</td><td>" . $quote['Subject_Name'] . "</td><td>" . $quote['PayType_Name'] . "</td><td>" . $quote['value'] . "</td>";
						$msg .= "</tr>";
					}
					$msg .= "</table><br>";
				}
			}
			if (empty($msg)) {
//				$this->addLog("Пропускаем Lpu_id = " . $lpu["Lpu_id"] . " : нет информации по квотам.");
				continue;
			}
			$noticeData = [
				"autotype" => 1, //обычное сообщение (msg.NoticeType)
				"type" => 1, //Информационное (обычное)
				"pmUser_id" => 1,
				"title" => "Остатки по квотам",
				"text" => $msg,
				"MedServiceType_SysNick" => "regpol",
				"Lpu_id" => $lpu["Lpu_id"]
			];
			$msg_result = $this->Messages_model->autoMessage($noticeData);//отправляется сообщение
			if (!empty($msg_result["Error_Msg"])) {
//				$this->addLog("Ошибка: " . $msg_result["Error_Msg"] . " . Не удалось отправить сообщение " . $msg);
			} else if (!empty($msg_result["Message_id"])) {
//				$this->addLog("Отправлено сообщение Message_id = " . $msg_result["Message_id"]);
			}
		}
		return true;
	}
}