<?php
class PersonCallJournal_model extends SwPgModel
{
    /**
     * Method description
     * @param int $limit
     * @return array|bool
     */
	public function getPersonCallJournal($limit = 500) {
		// https://redmine.swan.perm.ru/issues/37296
		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList();

		// Стартуем транзакцию
		$this->db->trans_begin();


        $filter = "";
        if (count($personPrivilegeCodeList) > 0 ) {
            $filter .= "or
					-- или возраст больше 18 лет и есть действующая льгота определенной категории
					(
					dbo.Age2(PS.Person_BirthDay, cast((select year from cte)::varchar || '-12-31' as date)) >= 18 and exists (
						select pp.PersonPrivilege_id
						from
						    v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where
						    pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
						and
						    pp.Person_id = ps.Person_id
						and
						    pp.PersonPrivilege_begDate <= dbo.tzGetDate()
						and
						    (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate > dbo.tzGetDate())
						limit 1
					))";
        }


        if (in_array($this->regionNick, ['ufa', 'ekb', 'kareliya', 'penza', 'astra'])) {
            $filter .= "or
					(dbo.Age2(PS.Person_BirthDay,  cast((select year from cte)::varchar || '-12-31' as  date)) >= 18 and exists (
						select
						    PersonPrivilegeWOW_id
						from
						    v_PersonPrivilegeWOW
						where
						    Person_id = ps.Person_id
						limit 1
					))";
        }

		$query = "
		    with cte as (
		        select " . date('Y') . " as year
		    )
		    
			select
				 ps.Person_id as \"Person_id\",
				 RTRIM(coalesce(ps.Person_Surname, '')) || ' ' || 
				 RTRIM(coalesce(ps.Person_Firname, '')) || ' ' ||
				 RTRIM(coalesce(ps.Person_Secname, '')) as \"Person_Fio\",
				 replace(replace(coalesce(pi.PersonInfo_InternetPhone, ps.Person_Phone), '-', ''), ' ', '') as \"Person_Phone\",
				 l.Lpu_id as \"Lpu_id\",
				 RTRIM(coalesce(l.Lpu_Name, '')) as \"Lpu_Name\"
			from
				v_PersonState ps
				-- Должно быть актуальное прикрепление
				inner join lateral (
					select
					    Lpu_id
					from
					    v_PersonCard t1
					where
					    t1.Person_id = ps.Person_id
                    and
                        (t1.PersonCard_begDate is null or cast(t1.PersonCard_begDate as timestamp) <= dbo.tzGetDate())
                    and
                        (t1.PersonCard_endDate is null or cast(t1.PersonCard_endDate as timestamp) > dbo.tzGetDate())
					and
					    t1.LpuAttachType_id = 1
					limit 1
				) pc on true
				inner join v_Lpu l on l.Lpu_id = pc.Lpu_id
				-- Телефон
				left join lateral (
					select
					    PersonInfo_InternetPhone
					from
					    v_PersonInfo
					where
					    Person_id = ps.Person_id
                    and
                        length(trim(coalesce(PersonInfo_InternetPhone, ''))) > 0
					limit 1
				) pi on true
			where
				(1 = 1)
				-- Не должно быть карт за текущий год
				and not exists (
					select
					    EvnPLDispDop13_id
					from
					    v_EvnPLDispDop13
					where
					    Person_id = PS.Person_id
                    and
                        coalesce(DispClass_id, 1) = 1
                    and
                        date_part('year', EvnPLDispDop13_setDate) = cast((select year from cte) as integer)
					limit 1
				)
				-- Возраст не больше 99 лет
				and
				    dbo.Age2(PS.Person_BirthDay, cast((select year from cte)::varchar || '-12-31' as date)) <= 99
				and (
					-- Возраст больше 21 года и кратен 3
					(dbo.Age2(PS.Person_BirthDay, cast((select year from cte)::varchar || '-12-31' as date)) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast((select year from cte)::varchar || '-12-31' as date)) - 21) % 3 = 0)
					" . $filter . "
				)
				-- Не должно быть отметки, что человек уже отправлялся в сервис обзвона
				and not exists (
					select
					    PersonCallJournal_id
					from
					    PersonCallJournal
					where
					    PersonCallType_id = 1
                    and
                        Person_id = ps.Person_id
					and
					    date_part('year', PersonCallJournal_insDT) = cast((select year from cte) as integer)
					limit 1
				)
				-- Должен быть указан телефон
				and length(replace(replace(coalesce(pi.PersonInfo_InternetPhone, ps.Person_Phone, ''), '-', ''), ' ', '')) > 0
			limit " . $limit . "
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
                select
                    Error_Code as \"Error_Code\",
                    ErrMessage as \"Error_Msg\"
				from dbo.p_PersonCallJournal_ins
				(
					PersonCallType_id := 1,
					Person_id := :Person_id,
					pmUser_id := 1
				)
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
