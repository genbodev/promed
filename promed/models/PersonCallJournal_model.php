<?php
class PersonCallJournal_model extends swModel {
	/**
	 * Method description
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Method description
	 */
	function getPersonCallJournal($limit = 500) {
		// https://redmine.swan.perm.ru/issues/37296
		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList();

		// Стартуем транзакцию
		$this->db->trans_begin();

		$query = "
			declare @year varchar(4);
			set @year = '" . date('Y') . "';

			select top " . $limit . "
				 ps.Person_id
				,RTRIM(ISNULL(ps.Person_Surname, ''))
					+ ' ' + RTRIM(ISNULL(ps.Person_Firname, ''))
					+ ' ' + RTRIM(ISNULL(ps.Person_Secname, '')) as Person_Fio
				,replace(replace(ISNULL([pi].PersonInfo_InternetPhone, ps.Person_Phone), '-', ''), ' ', '') as Person_Phone
				,l.Lpu_id
				,RTRIM(ISNULL(l.Lpu_Name, '')) as Lpu_Name
			from
				v_PersonState ps with (nolock)
				-- Должно быть актуальное прикрепление
				cross apply (
					select top 1 Lpu_id
					from v_PersonCard t1 with (nolock)
					where t1.Person_id = ps.Person_id
						and (t1.PersonCard_begDate is null or cast(t1.PersonCard_begDate as datetime) <= dbo.tzGetDate())
						and (t1.PersonCard_endDate is null or cast(t1.PersonCard_endDate as datetime) > dbo.tzGetDate())
						and t1.LpuAttachType_id = 1
				) pc
				inner join v_Lpu l with (nolock) on l.Lpu_id = pc.Lpu_id
				-- Телефон
				outer apply (
					select top 1 PersonInfo_InternetPhone
					from v_PersonInfo with (nolock)
					where Person_id = ps.Person_id
						and LEN(LTRIM(RTRIM(ISNULL(PersonInfo_InternetPhone, '')))) > 0
				) [pi]
			where
				(1 = 1)
				-- Не должно быть карт за текущий год
				and not exists (
					select top 1 EvnPLDispDop13_id
					from v_EvnPLDispDop13 with (nolock)
					where Person_id = PS.Person_id
						and ISNULL(DispClass_id,1) = 1
						and YEAR(EvnPLDispDop13_setDate) = @year
				)
				-- Возраст не больше 99 лет
				and dbo.Age2(PS.Person_BirthDay, @year + '-12-31') <= 99
				and (
					-- Возраст больше 21 года и кратен 3
					(dbo.Age2(PS.Person_BirthDay, @year + '-12-31') - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, @year + '-12-31') - 21)%3 = 0)
					" . (count($personPrivilegeCodeList) > 0 ? "or
					-- или возраст больше 18 лет и есть действующая льгота определенной категории
					(dbo.Age2(PS.Person_BirthDay, @year + '-12-31') >= 18 and exists (
						select top 1 pp.PersonPrivilege_id
						from v_PersonPrivilege pp (nolock)
							inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = ps.Person_id
							and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
							and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > dbo.tzGetDate())
					))" : "") . "
					" . (in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'penza', 'astra')) ? "or
					(dbo.Age2(PS.Person_BirthDay, @year + '-12-31') >= 18 and exists (
						select top 1 PersonPrivilegeWOW_id
						from v_PersonPrivilegeWOW (nolock)
						where Person_id = ps.Person_id
					))" : "") . "
				)
				-- Не должно быть отметки, что человек уже отправлялся в сервис обзвона
				and not exists (
					select top 1 PersonCallJournal_id
					from PersonCallJournal with (nolock)
					where PersonCallType_id = 1
						and Person_id = ps.Person_id
						and YEAR(PersonCallJournal_insDT) = @year
				)
				-- Должен быть указан телефон
				and len(replace(replace(coalesce([pi].PersonInfo_InternetPhone, ps.Person_Phone, ''), '-', ''), ' ', '')) > 0
		";
		$result = $this->db->query($query);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			$this->textlog->add('Ошибка при выполнении запроса к базе данных (получение списка пациентов)');
			return false;
		}

		$array = $result->result('array');
		$response = array();

		foreach ( $array as $record ) {
			$record['Person_Phone'] = $this->getFormalizedPhoneNum($record['Person_Phone']);

			if ( !empty($record['Person_Phone']) ) {
				array_walk($record, 'ConvertFromWin1251ToUTF8');

				$response[] = array(
					'id' => $record['Person_id'],
					'created_at' => date('d.m.Y H:i:s'),
					'visit_at' => null,
					'patient' => array(
						'id' => $record['Person_id'],
						'fio' => $record['Person_Fio'],
						'phone' => $record['Person_Phone']
					),
					'mo' => array(
						'id' => $record['Lpu_id'],
						'name' => $record['Lpu_Name']
					)
				);
			}

			// Добавляем запись в журнал
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec dbo.p_PersonCallJournal_ins
					@PersonCallType_id = 1,
					@Person_id = :Person_id,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, $record);

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				$this->textlog->add('Ошибка при выполнении запроса к базе данных (добавление записи в журнал)');
				return false;
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				$this->db->trans_rollback();
				$this->textlog->add('Ошибка при добавлении записи в журнал');
				return false;
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				$this->db->trans_rollback();
				$this->textlog->add($resp[0]['Error_Msg']);
				return false;
			}
		}

		$this->db->trans_commit();

		return $response;
	}

	/**
	 *	Попытка получения формализованного номера телефона из произвольного текстового поля
	 */

	function getFormalizedPhoneNum($phone = '') {
		$phone = trim($phone);

		if ( preg_match('/^([0-9\-]+)/', $phone, $match) ) {
			$phone = $match[1];

			// Обычный сотовый номер
			if ( preg_match('/^89(\d{9})$/', $phone, $match) ) {
				return $phone;
			}

			// Полный городской номер
			if ( preg_match('/^8(\d{10})$/', $phone, $match) ) { 
				return $phone;
			}

			// Cотовый номер без первой цифры
			if ( preg_match('/^9(\d{9})$/', $phone, $match) ) { 
				return '8' . $phone;
			}

			// Городской номер
			if ( preg_match('/^2(\d{6})$/', $phone, $match) ) { 
				return '8347' . $phone;
			}

			// Сотовые номера по маскам 8-9##-###-##-## или 89##-###-##-##
			if ( preg_match('/^8\-(\d{3})\-(\d{3})\-(\d{2})\-(\d{2})$/', $phone, $match) || preg_match('/^8(\d{3})\-(\d{3})\-(\d{2})\-(\d{2})$/', $phone, $match) ) { 
				return '8' . $match[1] . $match[2] . $match[3] . $match[4];
			}

			// Сотовые номера по маскам 8-9##-###-##-## или 89##-###-##-##
			if ( preg_match('/^2(\d{2})\-(\d{2})\-(\d{2})$/', $phone, $match) ) { 
				return '2' . $match[1] . $match[2] . $match[3];
			}
		}

		return null;
	}
}
