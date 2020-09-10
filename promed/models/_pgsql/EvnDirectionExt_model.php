<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnDirectionExt
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      11 2014
 */
class EvnDirectionExt_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm120 = "'yyyy-mm-dd hh:mi:ss'";

    /**
     * Получение списка
     * @param $data
     * @return bool
     */
    public function loadList($data)
    {
        $filters = "";
        if (!empty($data['notIdentOnly'])) {
            $filters .= " and ed.EvnDirection_id is null";
        }
        if (!empty($data['Person_SurName'])) {
            $filters .= " and case when ed.EvnDirection_id is not null then ps.Person_SurName else ede.Person_SurName end ilike :Person_SurName || '%'";
        }
        if (!empty($data['Person_FirName'])) {
            $filters .= " and case when ed.EvnDirection_id is not null then ps.Person_FirName else ede.Person_FirName end ilike :Person_FirName || '%'";
        }
        if (!empty($data['Person_SecName'])) {
            $filters .= " and case when ed.EvnDirection_id is not null then ps.Person_SecName else ede.Person_SecName end ilike :Person_SecName || '%'";
        }
        if (!empty($data['Person_BirthDay'])) {
            $filters .= " and case when ed.EvnDirection_id is not null then ps.Person_BirthDay else ede.Person_BirthDay end = :Person_BirthDay";
        }
        if (!empty($data['EvnDirectionExt_setDT_From'])) {
            $filters .= " and ede.EvnDirectionExt_setDT >= :EvnDirectionExt_setDT_From";
        }
        if (!empty($data['EvnDirectionExt_setDT_To'])) {
            $filters .= " and ede.EvnDirectionExt_setDT <= :EvnDirectionExt_setDT_To";
        }
        if (!empty($data['NaprLpu_id'])) {
            $filters .= " and l.Lpu_id = :NaprLpu_id";
        }
        if (!empty($data['EvnDirectionExt_IsIdent'])) {
            if ($data['EvnDirectionExt_IsIdent'] == 2) {
                $filters .= " and ede.EvnDirection_id is not null";
            } else {
                $filters .= " and ede.EvnDirection_id is null";
            }
        }
        $query = "
			select
				-- select
				ede.EvnDirectionExt_id as \"EvnDirectionExt_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ed.Person_id as \"Person_id\",
				case when ed.EvnDirection_id is not null then ps.Person_SurName else ede.Person_SurName end as \"Person_SurName\",
				case when ed.EvnDirection_id is not null then ps.Person_FirName else ede.Person_FirName end as \"Person_FirName\",
				case when ed.EvnDirection_id is not null then ps.Person_SecName else ede.Person_SecName end as \"Person_SecName\",
				to_char(case when ed.EvnDirection_id is not null then ps.Person_BirthDay else ede.Person_BirthDay end, {$this->dateTimeForm104}) as \"Person_BirthDay\",
				case when ed.EvnDirection_id is not null then s2.Sex_Name else s.Sex_Name end as \"Sex_Name\",
				case when ed.EvnDirection_id is not null then ps.Polis_Ser else ede.Polis_Ser end as \"Polis_Ser\",
				case when ed.EvnDirection_id is not null then ps.Polis_Num else ede.Polis_Num end as \"Polis_Num\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ede.EvnDirectionExt_NPRID as \"EvnDirectionExt_NPRID\",
				pt.PrehospType_Name as \"PrehospType_Name\",
				d.Diag_Name as \"Diag_Name\",
				to_char(ede.EvnDirectionExt_setDT, {$this->dateTimeForm104}) as \"EvnDirectionExt_setDT\",
				case when ede.EvnDirection_id is not null then 2 else 1 end as \"EvnDirectionExt_IsIdent\",
				l.Org_id as \"Org_id\",
				ede.Diag_id as \"Diag_id\",
				l.Lpu_id as \"Lpu_id\"
				-- end select
			from
				-- from
				v_EvnDirectionExt ede
				left join v_Lpu l on l.Lpu_f003mcod = ede.Lpu_f003mcod
				left join v_EvnDirection_all ed on ed.EvnDirection_id = ede.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = ed.Person_id
				left join v_Sex s2 on s2.Sex_id = ede.Sex_id
				left join v_Sex s on s.Sex_id = ede.Sex_id
				left join nsi.v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ede.LpuSectionProfile_id
				left join v_PrehospType pt on pt.PrehospType_id = ede.PrehospType_id
				left join v_Diag d on d.Diag_id = ede.Diag_id
				-- end from
			where
				-- where
				ede.Lpu_id = :Lpu_id
            and
                l.Lpu_id is not null
            and
                not exists (
                    select
                        OrgWorkPeriod_id
                    from
                        v_OrgWorkPeriod owp
                    where
                        owp.Org_id = l.Org_id
                    and
                        owp.OrgWorkPeriod_begDate <= ede.EvnDirectionExt_setDT
                    and
                        (owp.OrgWorkPeriod_endDate IS NULL or owp.OrgWorkPeriod_endDate >= ede.EvnDirectionExt_setDT) -- не работает в Промеде
                    limit 1
                )
				{$filters}
				-- end where
			order by
				-- order by
				ede.EvnDirectionExt_id
				-- end order by
		";

        $limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
        $result = $this->db->query($limit_query, $data);

        if (!is_object($result))
            return false;

        $res = $result->result('array');


        if ($data['start'] == 0 && count($res) < $data['limit']) {
            $response['data'] = $res;
            $response['totalCount'] = count($res);
            return $response;
        } else {
            $response['data'] = $res;
            $get_count_query = getCountSQLPH($query);
            $get_count_result = $this->db->query($get_count_query, $data);

            if (is_object($get_count_result)) {
                $response['totalCount'] = $get_count_result->result('array');
                $response['totalCount'] = $response['totalCount'][0]['cnt'];
                return $response;
            }
            return false;
        }
    }

    /**
     * Повторная идентификация направления
     * @param $data
     * @return array
     */
    public function reidentEvnDirectionExt($data)
    {
        // получаем необходимые данные
        $query = "
			select
				ede.EvnDirectionExt_id as \"EvnDirectionExt_id\",
				ede.Person_SurName as \"Person_SurName\",
				ede.Person_FirName as \"Person_FirName\",
				ede.Person_SecName as \"Person_SecName\",
				to_char(ede.Person_BirthDay, {$this->dateTimeForm120}) as \"Person_BirthDay\"
			from
				v_EvnDirectionExt ede
			where
				ede.EvnDirection_id = :EvnDirection_id
            and
				not exists(select EvnPS_id from v_EvnPS where EvnDirection_id = ede.EvnDirection_id limit 1) -- переидентифицируем только если не используется.
		";

        $result = $this->db->query($query, $data);
        if (!is_object($result))
            return ['Error_Msg' => ''];


        $resp = $result->result('array');
        if (!empty($resp[0]['EvnDirectionExt_id'])) {
            // Проводим идентификацию
            $result_ident = $this->db->query("
					select
					    dbo.getPersonByFIOPolis
					    (
					        :Person_SurName,
					        :Person_FirName,
					        :Person_SecName,
					        :Person_BirthDay,
					        null,
					        null
					    ) as \"Person_id\"
				", $resp[0]);
            if (!is_object($result))
                return ['Error_Msg' => ''];

            $resp_idented = $result_ident->result('array');
            if (empty($resp_idented[0]['Person_id'])) {
                // При перевыборе направления в КВС, если до этого было выбрано Внешнее направление, то для такого Внешнего направления заново проводить автоматическую идентификацию.
                // Если при автоматической идентификации Внешнее направление не удалось идентифицировать, то удалять связанное с ним Электронное направление.
                $query = "
                    update
                        EvnDirectionExt
                    set
                        EvnDirection_id = null,
                        Person_id = null
                    where
                        EvnDirectionExt_id = :EvnDirectionExt_id
				";
                $this->db->query($query, $resp[0]);

                $this->load->model('EvnDirection_model');
                $this->EvnDirection_model->deleteEvnDirection([
                    'EvnDirection_id' => $data['EvnDirection_id'],
                    'pmUser_id' => $data['pmUser_id']
                ]);
            }
        }
        return ['Error_Msg' => ''];
    }

    /**
     * Сохранение внешнего направления
     * @param $data
     * @return array|false
     */
    public function saveEvnDirectionExt($data)
    {
        $response = [
            'EvnDirectionExt_id' => null,
            'EvnDirection_id' => null,
            'Error_Code' => null,
            'Error_Msg' => null
        ];

        if (!empty($data['EvnDirectionExt_id'])) {
            $response['EvnDirectionExt_id'] = $data['EvnDirectionExt_id'];
        }
        if (!empty($data['EvnDirection_id'])) {
            $response['EvnDirection_id'] = $data['EvnDirection_id'];
        }

        // Сначала проверяем, может уже есть такое направление.
        if (empty($response['EvnDirectionExt_id']) && !empty($response['EvnDirection_id'])) {
            $response['EvnDirectionExt_id'] = $this->getFirstResultFromQuery("
				select
				    EvnDirectionExt_id as \"EvnDirectionExt_id\"
				from
				    v_EvnDirectionExt
				where
				    EvnDirection_id = :EvnDirection_id
				limit 1
			", $response, true);
            if ($response['EvnDirectionExt_id'] === false) {
                return $this->createError('', 'Ошибка при поиске внешнего направления');
            }
        }

        if (empty($response['EvnDirectionExt_id'])) {
            $data['EvnDirectionExt_id'] = null;
            $proc = "p_EvnDirectionExt_ins";
        } else {
            $data['EvnDirectionExt_id'] = $response['EvnDirectionExt_id'];
            $proc = "p_EvnDirectionExt_upd";
        }

        // Если ещё не было, проводим идентификацию, используется функция [dbo].[GetPersonByFioPolis]
        if (empty($data['Person_id'])) {
            $result = $this->db->query("
				select
				    dbo.getPersonByFIOPolis
				(
				    :Person_SurName,
				    :Person_FirName,
				    :Person_SecName,
				    :Person_BirthDay,
				    null,
				    null
				) as \"Person_id\"
			", $data);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['Person_id'])) {
                    $data['Person_id'] = $resp[0]['Person_id'];
                }
            }
        }

        $query = "
			select
			    EvnDirectionExt_id as \"EvnDirectionExt_id\",
			    Error_Code as \"Error_Code\",
			    Error_Msg as \"Error_Msg\"
			from {$proc}
			(
				EvnDirectionExt_id := :EvnDirectionExt_id,
				EvnDirectionExt_NPRID := :EvnDirectionExt_NPRID,
				EvnDirection_id := :EvnDirection_id,
				EvnDirectionExt_setDT := :EvnDirectionExt_setDT,
				PrehospType_id := :PrehospType_id,
				Lpu_id := :Lpu_id,
				Lpu_Name := :Lpu_Name,
				Lpu_f003mcod := :Lpu_f003mcod,
				Person_id := :Person_id,
				Person_SurName := :Person_SurName,
				Person_FirName := :Person_FirName,
				Person_SecName := :Person_SecName,
				Person_BirthDay := :Person_BirthDay,
				Person_Phone := :Person_Phone,
				Sex_id := :Sex_id,
				PolisType_id := :PolisType_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Diag_id := :Diag_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				LpuSection_Code := :LpuSection_Code,
				MedPersonal_SNILS := :MedPersonal_SNILS,
				EvnDirectionExt_planDate := :EvnDirectionExt_planDate,
				pmUser_id := :pmUser_id
			)
		";

        $result = $this->queryResult($query, $data);
        if (!is_array($result)) {
            return $this->createError('', 'Ошибка при сохранении внешнего направления');
        }
        if (!$this->isSuccessful($result)) {
            return $result;
        }
        $response['EvnDirectionExt_id'] = $result[0]['EvnDirectionExt_id'];
        if (empty($response['EvnDirection_id']) && !empty($response['EvnDirectionExt_id']) && !empty($data['Person_id'])) {
            // Если пациент  успешно прошел идентификацию, то необходимо создавать новый объект «Электронное направление», сохраняя в нем данные из Внешнего направления.
            $data['EvnDirectionExt_id'] = $response['EvnDirectionExt_id'];
            $result = $this->identEvnDirectionExt($data);
            if (!empty($result['EvnDirection_id'])) {
                $response['EvnDirection_id'] = $result['EvnDirection_id'];
            }
        }

        return [$response];
    }

    /**
     * Идентификация / смена пациента для внешнего направления
     * @param $data
     * @return array
     * @throws Exception
     */
    public function identEvnDirectionExt($data)
    {
        // 1. проверяем идентифицировано ли данное направление уже
        $dirdata = null;

        $query = "
			select
                ede.EvnDirection_id as \"EvnDirection_id\",
                ps.Server_id as \"Server_id\",
                ps.PersonEvn_id as \"PersonEvn_id\",
                ede.EvnDirectionExt_NPRID as \"EvnDirectionExt_NPRID\",
                to_char(ede.EvnDirectionExt_setDT, {$this->dateTimeForm120}) as \"EvnDirectionExt_setDT\",
                ede.Lpu_id as \"Lpu_id\",
                ede.Diag_id as \"Diag_id\",
                l.Lpu_id as \"Lpu_sid\",
                l.Org_id as \"Org_sid\",
                lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                case when ede.PrehospType_id =  2 then 1 else 5 end as \"DirType_id\"
			from
			 	v_EvnDirectionExt ede
			 	left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ede.LpuSectionProfile_id
			 	left join v_PersonState ps on ps.Person_id = :Person_id
			 	left join v_Lpu l on l.Lpu_f003mcod = ede.Lpu_f003mcod
            where
			 	EvnDirectionExt_id = :EvnDirectionExt_id
			limit 1
		";
        $result = $this->db->query($query, $data);
        if (is_object($result)) {
            $resp = $result->result('array');
            if (!empty($resp[0])) {
                $dirdata = $resp[0];
            }
        }

        if (empty($dirdata)) {
           throw new Exception('Ошибка получения данных по внешнему направлению');
        }

        if (empty($dirdata['EvnDirection_id'])) {
            $query = "
				select
					EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnDirection
				where
					EvnDirection_Num = :EvnDirection_Num
                and
                    Person_id = :Person_id
			";
            $result = $this->db->query($query, [
                'EvnDirection_Num' => $dirdata['EvnDirectionExt_NPRID'],
                'Person_id' => $data['Person_id']
            ]);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['EvnDirection_id'])) {
                    // return array('Error_Msg' => 'Направление с указанным номером во внешнем нарпавлении для пациента уже создано');
                    $dirdata['EvnDirection_id'] = $resp[0]['EvnDirection_id'];
                    // обновляем ссылку на направление во внешнем направлении
                    $query = "
						update
							EvnDirectionExt
						set
							EvnDirection_id = :EvnDirection_id,
							Person_id = :Person_id
						where
							EvnDirectionExt_id = :EvnDirectionExt_id
					";

                    $this->db->query($query, [
                        'EvnDirection_id' => $resp[0]['EvnDirection_id'],
                        'EvnDirectionExt_id' => $data['EvnDirectionExt_id'],
                        'Person_id' => $data['Person_id']
                    ]);
                }
            }
        }

        // 2. если нет, то просто создаём новое направления
        if (empty($dirdata['EvnDirection_id'])) {
            $this->load->model('EvnDirection_model');
            $result = $this->EvnDirection_model->saveEvnDirection([
                'EvnDirection_id' => $dirdata['EvnDirection_id'],
                'Server_id' => $dirdata['Server_id'],
                'PersonEvn_id' => $dirdata['PersonEvn_id'],
                'EvnDirection_Num' => $dirdata['EvnDirectionExt_NPRID'],
                'PrehospDirect_id' => ($dirdata['Lpu_sid'] != $dirdata['Lpu_id']) ? 2 : 1,
                'From_MedStaffFact_id' => -1,
                'EvnDirection_pid' => null,
                'Diag_id' => $dirdata['Diag_id'],
                'EvnDirection_Descr' => null,
                'LpuSection_did' => null,
                'MedPersonal_zid' => null,
                'onlySaveDirection' => 1,
                'EvnDirection_setDT' => $dirdata['EvnDirectionExt_setDT'],
                'LpuSectionProfile_id' => $dirdata['LpuSectionProfile_id'],
                'Lpu_id' => $dirdata['Lpu_sid'], // МО, создавшее направление
                'Lpu_did' => $dirdata['Lpu_id'],// МО, куда был направлен пациент
                'DirType_id' => $dirdata['DirType_id'], // тип направления
                'EvnDirection_IsAuto' => 1,
                'EvnDirection_IsReceive' => 2,
                'LpuSection_id' => null, // Направившее отделение
                'MedPersonal_id' => null, // Направивший врач
                'Lpu_sid' => $dirdata['Lpu_sid'], // Направившее ЛПУ
                'Org_sid' => $dirdata['Org_sid'], // Направившая организация
                'session' => $data['session'],
                'pmUser_id' => $data['pmUser_id'],
                'toQueue' => true
            ]);

            if (!empty($result[0]['Error_Msg'])) {
                return array('Error_Msg' => $result[0]['Error_Msg']);
            }

            if (!empty($result[0]['EvnDirection_id'])) {
                $dirdata['EvnDirection_id'] = $result[0]['EvnDirection_id'];
                // обновляем ссылку на направление во внешнем направлении
                $query = "
					update
						EvnDirectionExt
					set
						EvnDirection_id = :EvnDirection_id,
						Person_id = :Person_id
					where
						EvnDirectionExt_id = :EvnDirectionExt_id
				";

                $this->db->query($query,[
                    'EvnDirection_id' => $result[0]['EvnDirection_id'],
                    'EvnDirectionExt_id' => $data['EvnDirectionExt_id'],
                    'Person_id' => $data['Person_id']
                ]);
            }
        } else {
            // если уже есть направление, то проверяем а не используется ли направление в КВС и не отменено ли оно
            $query = "
				select
					EvnDirection_failDT as \"EvnDirection_failDT\",
					evns.EvnDirection_UsedCount as \"EvnDirection_UsedCount\"
				from
					v_EvnDirection ed
					left join lateral (
						select
							count(e.EvnPS_id) as EvnDirection_UsedCount
						from
							v_EvnPS e
						where
							e.EvnDirection_id = ed.EvnDirection_id
							and e.Person_id != :Person_id
					) evns on true
				where
					ed.EvnDirection_id = :EvnDirection_id
			";
            $result = $this->db->query($query, [
                'EvnDirection_id' => $dirdata['EvnDirection_id'],
                'Person_id' => $data['Person_id']
            ]);
            if (is_object($result)) {
                $resp = $result->result('array');
                if (!empty($resp[0]['EvnDirection_failDT'])) {
                    throw new Exception('Данное направление было отменено. Смена пациента невозможна.');
                }

                if (!empty($resp[0]['EvnDirection_UsedCount']) && $resp[0]['EvnDirection_UsedCount'] > 0) {
                    throw new Exception('Данное направление выбрано в карте выбывшего из стационара. Смена пациента невозможна.');
                }
            }

            // если нет, от обновляем ссылку на чела в эл. направлении и во внешнем
            $query = "
				update
					Evn
				set
					Person_id = :Person_id,
					PersonEvn_id = :PersonEvn_id,
					Server_id = :Server_id
				where
					Evn_id = :EvnDirection_id
			";

            $this->db->query($query, [
                'EvnDirection_id' => $dirdata['EvnDirection_id'],
                'Person_id' => $data['Person_id'],
                'PersonEvn_id' => $dirdata['PersonEvn_id'],
                'Server_id' => $dirdata['Server_id']
            ]);
            // обновляем ссылку на направление во внешнем направлении
            $query = "
				update
					EvnDirectionExt
				set
					Person_id = :Person_id
				where
					EvnDirectionExt_id = :EvnDirectionExt_id
			";

            $this->db->query($query, [
                'EvnDirectionExt_id' => $data['EvnDirectionExt_id'],
                'Person_id' => $data['Person_id']
            ]);
        }

        $response = [
            'Error_Msg' => '',
            'EvnDirection_id' => $dirdata['EvnDirection_id'],
            'EvnDirection_Num' => '',
            'Diag_id' => null,
            'Org_id' => null,
            'Lpu_id' => null
        ];

        // получаем необходимые данные по направлению: EvnDirection_Num
        $query = "
			select
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				ed.Lpu_id as \"Lpu_id\",
				l.Org_id as \"Org_id\",
				ed.Diag_id as \"Diag_id\"
			from
				v_EvnDirection ed
				left join v_Lpu l on l.Lpu_id = ed.Lpu_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		";
        $result = $this->db->query($query, [
            'EvnDirection_id' => $dirdata['EvnDirection_id']
        ]);
        if (is_object($result)) {
            $resp = $result->result('array');
            if (!empty($resp[0])) {
                $response['EvnDirection_Num'] = $resp[0]['EvnDirection_Num'];
                $response['Lpu_id'] = $resp[0]['Lpu_id'];
                $response['Org_id'] = $resp[0]['Org_id'];
                $response['Diag_id'] = $resp[0]['Diag_id'];
            }
        }

        return $response;
    }
}