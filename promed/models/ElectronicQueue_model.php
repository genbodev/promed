<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ElectronicQueue
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

class ElectronicQueue_model extends swModel {
    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Загрузка области данных АРМ
     */
    function loadWorkPlaceGrid($data) {
        $this->load->helper('Reg');

        $queryParams = array(
            'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
            'TimetableMedService_Day' => TimeToDay(strtotime($data['onDate'])),
            'curTimetableMedService_Day' => TimeToDay(time())
        );

        $orderby = "";
        if (!empty($data['session']['CurARM']['ElectronicService_id'])) {
            $orderby .= "case when et.ElectronicService_id = :ElectronicService_id then 0 else 1 end,";
            $queryParams['ElectronicService_id'] = $data['session']['CurARM']['ElectronicService_id'];
        }

        return $this->queryResult("
			select
				ttms.TimetableMedService_id,
				et.ElectronicTalon_Num,
				ISNULL(convert(varchar(5), ttms.TimetableMedService_begTime, 108), 'б/з') as TimetableMedService_begTime,
				rtrim(isnull(ps.Person_SurName,'')) + rtrim(isnull(' ' + ps.Person_FirName,'')) + rtrim(isnull(' ' + ps.Person_SecName, '')) as Person_Fio,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				epdd.EvnPLDispDriver_Num,
				ets.ElectronicTalonStatus_Name,
				ISNULL(cast(es.ElectronicService_Code as varchar) + ' ', '') + es.ElectronicService_Name as ElectronicService_Name,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				et.ElectronicService_id,
				et.ElectronicTalonStatus_id,
				epdd.EvnPLDispDriver_id,
				case when ttms.TimetableMedService_Day = :curTimetableMedService_Day then 1 else 0 end as IsCurrentDate,
				et.ElectronicTalon_id,
				ps.Person_IsUnknown,
				rmt.RecMethodType_Name
			from
				v_TimetableMedService_lite ttms (nolock)
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttms.EvnDirection_id
				left join v_PersonState ps (nolock) on ps.Person_id = ttms.Person_id
				left join v_EvnPLDispDriver epdd (nolock) on epdd.EvnDirection_id = ttms.EvnDirection_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicService es (nolock) on es.ElectronicService_id = et.ElectronicService_id
				left join v_RecMethodType rmt (nolock) on rmt.RecMethodType_id = ttms.RecMethodType_id
			where
				ttms.UslugaComplexMedService_id = :UslugaComplexMedService_id
				and ttms.TimetableMedService_Day = :TimetableMedService_Day
			order by
				{$orderby}
				case when et.ElectronicTalon_Num is not null then 0 else 1 end,
				case when et.ElectronicTalonStatus_id = 4 then 1 else 0 end,
				case when ttms.Person_id is not null then 0 else 1 end,
				TimetableMedService_begTime
		", $queryParams);
    }

    /**
     * Установка статуса электронного талона при неявке пациента
     */
    function setNoPatientTalonStatus($data) {
        $resp_check = $this->queryResult("
			select
				count(*) as cnt
			from
				v_ElectronicTalonHist
			where
				ElectronicTalon_id = :ElectronicTalon_id
				and ElectronicTalonStatus_id = 2
		", array(
            'ElectronicTalon_id' => $data['ElectronicTalon_id']
        ));

        if (!empty($resp_check[0]['cnt']) && $resp_check[0]['cnt'] >= 2) {
            // Если в истории статусов для текущего пункта обслуживания есть 2 записи со статусом «Вызван», то "Отменён"
            // получаем данные по бирке (не отменять если со временем и текущее дата время меньше, чем [дата время бирки + время опоздания при регистрации в очереди (мин.)])
            $resp_noc = $this->queryResult("
				select
					case
						when es.ElectronicService_Num = 1 and ttms.TimetableMedService_begTime is not null and DATEADD(MINUTE, eqi.ElectronicQueueInfo_LateTimeMin, ttms.TimetableMedService_begTime) > dbo.tzGetDate() then 1
						else 0
					end as noCancel
				from
					v_ElectronicTalon et (nolock)
					left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
					left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = et.EvnDirection_id
					left join v_ElectronicService es (nolock) on es.ElectronicService_id = et.ElectronicService_id
				where
					et.ElectronicTalon_id = :ElectronicTalon_id
			", array(
                'ElectronicTalon_id' => $data['ElectronicTalon_id']
            ));

            if (!empty($resp_noc[0]['noCancel'])) {
                $data['ElectronicTalonStatus_id'] = 1; // ожидает
            } else {
                $data['ElectronicTalonStatus_id'] = 5; // отменён
            }
        } else {
            // Иначе "Ожидает"
            $data['ElectronicTalonStatus_id'] = 1;
        }

        $this->load->model('ElectronicTalon_model');
        return $this->ElectronicTalon_model->setElectronicTalonStatus($data);
    }

    /**
     * Проверка активности электронной очереди
     */
    function checkElectronicQueueInfoEnabled($data) {
        $resp = $this->queryResult("
			select
				eqi.ElectronicQueueInfo_id,
				ISNULL(eqi.ElectronicQueueInfo_IsOff, 1) as ElectronicQueueInfo_IsOff
			from
				v_ElectronicQueueInfo eqi (nolock)
				inner join v_ElectronicService es (nolock) on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
			where
				es.ElectronicService_id = :ElectronicService_id
		", array(
            'ElectronicService_id' => $data['ElectronicService_id']
        ));

        if (!empty($resp[0]['ElectronicQueueInfo_id'])) {
            return array('Error_Msg' => '', 'ElectronicQueueInfo_id' => $resp[0]['ElectronicQueueInfo_id'], 'ElectronicQueueInfo_IsOff' => $resp[0]['ElectronicQueueInfo_IsOff']);
        }

        return array('Error_Msg' => 'Ошибка определения активности электронной очереди');
    }

    /**
     * Замена неизвестного человека на известного
     */
    function fixPersonUnknown($data) {
        // убеждаемся, что выбран именно неизвестный человек
        $resp = $this->queryResult("
			select top 1
				pso.Person_id as Person_oldId,
				psn.Person_id as Person_newId,
				psn.Server_id as Server_newId,
				psn.PersonEvn_id as PersonEvn_newId
			from
				v_PersonState pso (nolock)
				left join v_PersonState psn (nolock) on psn.Person_id = :Person_newId
			where
				pso.Person_id = :Person_oldId
				and pso.Person_IsUnknown = 2",
            array(
                'Person_oldId' => $data['Person_oldId'],
                'Person_newId' => $data['Person_newId']
            ));

        if (empty($resp[0]['Person_oldId'])) {
            return array('Error_Msg' => 'Ошибка получения данных по неизвестному человеку');
        }

        if (empty($resp[0]['Person_newId'])) {
            return array('Error_Msg' => 'Ошибка получения данных по человеку');
        }

        $this->beginTransaction();
        // Бирка связывается с идентификатором выбранного человека.
        // Направление связывается с идентификатором выбранного человека.
        // Талон ЭО связывается с идентификатором выбранного человека.
        $resp_upd = $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			set nocount on;

			begin try
				update TimetableMedService with (rowlock) set Person_id = :Person_newId, pmUser_updID = :pmUser_id, TimeTableMedService_updDT = dbo.tzGetDate() where Person_id = :Person_oldId; -- Бирка
				update ElectronicTalon with (rowlock) set Person_id = :Person_newId, pmUser_updID = :pmUser_id, ElectronicTalon_updDT = dbo.tzGetDate() where Person_id = :Person_oldId; -- Талон
				update Evn with (rowlock) set Person_id = :Person_newId, Server_id = :Server_newId, PersonEvn_id = :PersonEvn_newId, pmUser_updID = :pmUser_id, Evn_updDT = dbo.tzGetDate() where Person_id = :Person_oldId and EvnClass_id = 27; -- EvnDirection
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
            'Server_newId' => $resp[0]['Server_newId'],
            'PersonEvn_newId' => $resp[0]['PersonEvn_newId'],
            'Person_newId' => $resp[0]['Person_newId'],
            'Person_oldId' => $resp[0]['Person_oldId'],
            'pmUser_id' => $data['pmUser_id']
        ));
        if (!empty($resp_upd[0]['Error_Msg'])) {
            $this->rollbackTransaction();
            return $resp_upd[0];
        }

        // Человек с признаком «Неизвестный», ранее связанный с биркой, направлением, талоном ЭО, удаляется из БД.
        // Если у него нет других учетных документов
        $this->load->model('Person_model');
        $toDel = $this->Person_model->checkToDelPerson(array(
            'Person_id' => $resp[0]['Person_oldId']
        ));
        if(empty($toDel['Person_id'])) {
            $resp_del = $this->Person_model->deletePerson(array(
                'Person_id' => $resp[0]['Person_oldId'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if (!empty($resp_del[0]['Error_Msg'])) {
                $this->rollbackTransaction();
                return $resp_del[0];
            }
        }

        $this->commitTransaction();
        return array('Error_Msg' => '');
    }

    /**
     * Проверка на занятость текущего сервиса
     */
    function checkIsDigitalServiceBusy($data) {

        $params['ElectronicService_id'] = $data['ElectronicService_id'];
        $ttg_type = 'TimetableGraf';

        if (!empty($data['ttg_type'])) {$ttg_type = $data['ttg_type'];}

        if ($ttg_type == 'TimetableGraf') {

            $query = "

                declare @curDate date = dbo.tzGetdate();

                select top 1
                    et.ElectronicTalon_id,
                    et.ElectronicTalon_Num,
                    ed.EvnDirection_TalonCode,
                    COALESCE(CONVERT(varchar,ttg.TimetableGraf_begTime, 104), CONVERT(varchar,et.ElectronicTalon_insDT, 104)) as day,
					COALESCE(CONVERT(varchar(5),ttg.TimetableGraf_begTime, 108), CONVERT(varchar(5),et.ElectronicTalon_insDT, 108)) as time
                from v_ElectronicTalon et (nolock)
                    left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = et.EvnDirection_id
                    left join v_TimetableGraf_lite ttg (nolock) on ttg.TimetableGraf_id = ed.TimetableGraf_id
                    left join v_Day day (nolock) on day.day_id = (ttg.TimetableGraf_Day - 1)
                where (1=1)
                    and et.ElectronicTalonStatus_id = 3 -- на обслуживании
                    and et.ElectronicService_id = :ElectronicService_id
                    and cast(et.ElectronicTalon_insDT as date) = @curDate
                order by ttg.TimetableGraf_begTime desc

		    ";

        } elseif ($ttg_type == 'TimetableMedService') {

            $query = "
                declare @curDate date = dbo.tzGetdate();

                select top 1
                    et.ElectronicTalon_id,
                    et.ElectronicTalon_Num,
                    ed.EvnDirection_TalonCode,
                    COALESCE(CONVERT(varchar,ttms.TimetableMedService_begTime, 104), CONVERT(varchar,day.day_date, 104)) as day,
                    CONVERT(varchar(5),ttms.TimetableMedService_begTime, 108) as time
                from v_ElectronicTalon et (nolock)
                    left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = et.EvnDirection_id
                    left join v_TimetableMedService_lite ttms (nolock) on ttms.TimeTableMedService_id = ed.TimeTableMedService_id
                    left join v_Day day (nolock) on day.day_id = (ttms.TimeTableMedService_Day - 1)
                where (1=1)
                    and et.ElectronicTalonStatus_id = 3 -- на обслуживании
                    and et.ElectronicService_id = :ElectronicService_id
                    and cast(et.ElectronicTalon_insDT as date) = @curDate
                order by ttms.TimetableMedService_begTime desc

		    ";
        }

        $resp = $this->queryResult($query, $params);

        if (empty($resp[0]['ElectronicTalon_id'])) {

            return array('Error_Msg' => ''); // когда все норм ;-)

        } else {

            return array(
                'Error_Msg' => '',
                'data' => array('ElectronicTalon_id' => $resp[0]['ElectronicTalon_id']),
                'Check_Msg' =>
                    'Перед '
                    .((!empty($data['ServiceAction']) && ($data['ServiceAction'] == 'doCall' || $data['ServiceAction'] == 'call')) ? 'вызовом' : 'приемом')
                    .' нового пациента нужно завершить обслуживание для пациента с талоном №'
                    . $resp[0]['EvnDirection_TalonCode']
                    . ' от '
                    . $resp[0]['day']
                    . (!empty($resp[0]['time']) ? ' ('.$resp[0]['time'].')' : ''),
            );
        }
    }

    /**
     * Завершение приёма
     */
    function finishCall($data) {

        $result = array('Error_Msg' => '');

        // получаем связанные события
        $query = "
			select top 1
			    et.ElectronicTalon_id,
				et.EvnDirection_id,
				et.ElectronicService_id,
				mst.MedServiceType_SysNick
			from v_ElectronicTalon et (nolock)
			left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
			left join v_MedService ms (nolock) on ms.MedService_id = eqi.MedService_id
			left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where et.ElectronicTalon_id = :ElectronicTalon_id
		";

        $talon = $this->getFirstRowFromQuery($query, array('ElectronicTalon_id' => $data['ElectronicTalon_id']));

        if (!empty($talon['ElectronicTalon_id'])) {

        	if ($this->usePostgreLis && !empty($talon['MedServiceType_SysNick']) && in_array($talon['MedServiceType_SysNick'], array('pzm', 'lab'))) {
				$this->load->swapi('lis');
				$dir_data = $this->lis->GET('EvnDirection/getElectronicTalonDirectionData', array(
					'EvnDirection_id' => $talon['EvnDirection_id']
				), 'single');

			} else {
        		$dir_data = $this->getFirstRowFromQuery("
        			select top 1
        				EvnDirection_id,
        				DirType_id
					from v_EvnDirection_all (nolock)
					where EvnDirection_id = :EvnDirection_id
				", array('EvnDirection_id' => $talon['EvnDirection_id'])
				);
			}

           if (!empty($dir_data) && $this->isSuccessful($dir_data)) {

           		if (!empty($talon['ElectronicService_id']) && $talon['ElectronicService_id'] !== $data['ElectronicService_id']) {
					return array('Error_Msg' => 'Нельзя завершить талон электронной очереди обслуживаемый в настоящий момент у другого врача');
				}

				if ($dir_data['DirType_id'] == 25) {

					$this->load->model('ProfService_model');
					$result = $this->ProfService_model->finishCall($data);

				} else {

					$this->load->model('ElectronicTalon_model');
					$this->ElectronicTalon_model->setElectronicTalonStatus(array(
						'ElectronicTalon_id' => $data['ElectronicTalon_id'],
						'ElectronicService_id' => $data['ElectronicService_id'],
						'ElectronicTalonStatus_id' => 4, // завершить прием
						'pmUser_id' => $data['pmUser_id']
					));
				}

			} else {
			   return array('Error_Msg' => 'Не найдено направление связанное с талоном электронной очереди');
		   	}

        } else {
			return array('Error_Msg' => 'Талон электронной очереди не найден');
		}

        return $result;
    }

    /**
     * Приём пациента
     */
    function applyCall($data) {

        $this->load->model('ElectronicTalon_model');
        $this->db->trans_begin();

        $params = array(
            'ElectronicTalon_id' => $data['ElectronicTalon_id'],
            'ElectronicService_id' => (!empty($data['ElectronicService_id']) ? $data['ElectronicService_id'] : null ),
            'ElectronicTalonStatus_id' => 3, // На обслуживании
            'pmUser_id' => $data['pmUser_id']
        );

        $setStatus = $this->ElectronicTalon_model->setElectronicTalonStatus($params);
        if (!empty($setStatus['Error_Msg'])) return array('Error_Msg' => $setStatus['Error_Msg']);

        if (!empty($data['DispClass_id'])) {

            switch ($data['DispClass_id']) {
                case 10:

                    // создаем профосмотр, если не создан
                    if (!empty($data['EvnPLDispTeenInspection_id'])) {

                        $this->db->trans_commit();
                        return array('Error_Msg' => '', 'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']);

                    } else {
                        return $this->createEvnPLDispAndAgreeConsent($data);
                    }
                break;
            }

        } else {
            $this->db->trans_commit();
        }

        return array('Error_Msg' => '');
    }

	/**
	 * Проверка есть ли у связи ПО-ОБЪЕКТ-СОТРУДНИК направления без кода брони
	 */
	function isEnableEvnDirectionsWithEmptyTalonCode($data) {

		$targetField = ""; $entity = "";
		$response['checkResult'] = false;

		if (!empty($data['MedStaffFact_id'])) {
			$entity = 'TimetableGraf';
			$targetField = 'MedStaffFact_id';
		} else if (!empty($data['Resource_id'])) {
			$entity = 'TimetableResource';
			$targetField = 'Resource_id';
		} else if (!empty($data['UslugaComplexMedService_id'])) {
			$entity = 'TimetableMedService';
			$targetField = "UslugaComplexMedService_id";
		} else if (!empty($data['MedService_id'])) {
			$entity = 'TimetableMedService';
			$targetField = "MedService_id";
		}

		if (!empty($targetField)) {

			$params[$targetField] = $data[$targetField];

			$query = "
				select top 1
					tt.TimetableGraf_id
				from v_{$entity}_lite as tt (nolock)
				inner join v_EvnDirection_all as ed (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
				inner join v_MedServiceElectronicQueue as mseq (nolock) on mseq.{$targetField} = tt.{$targetField}
				where
					(1=1)
					and cast({$entity}_begTime as date) > cast(dbo.tzGetdate() as date)
					and ed.EvnDirection_TalonCode is NULL
					and tt.{$targetField} = :{$targetField}
			";

			$result = $this->getFirstResultFromQuery($query, $params);
			if (!empty($result)) $response['checkResult'] = true;
		}

		return $response;
	}

    /**
     * Создаем коды бронирования для записей, у которых нет кода брони после создания очереди
     */
    function generateTalonCodeForExistedRecords($data) {

        $filter = ""; $params = array(); $procedure = ""; $targetField = "";

		if (!empty($data['MedStaffFact_id'])) {
			$entity = 'TimetableGraf';
			$targetField = 'MedStaffFact_id';
			$procedure = "xp_GenTalonCodeGraf";
		} else if (!empty($data['Resource_id'])) {
			$entity = 'TimetableResource';
			$targetField = 'Resource_id';
			$procedure = "xp_GenTalonCodeResource";
		} else if (!empty($data['UslugaComplexMedService_id'])) {
			$entity = 'TimetableMedService';
			$targetField = "UslugaComplexMedService_id";
			$procedure = "xp_GenTalonCodeMedService";
		} else if (!empty($data['MedService_id'])) {
			$entity = 'TimetableMedService';
			$targetField = "MedService_id";
			$procedure = "xp_GenTalonCodeMedService";
		}

        if (!empty($targetField)) {

            if (!empty($data['inList'])) {

                // todo: эта ветка не тестилась!

                $inList = "";
                foreach ($data['inList'] as $id) {$inList .= $id.','; }
                if (!empty($inList)) {

                    $inList = rtrim($inList, ',');
                    $filter = " and tt.{$targetField} in(".$inList.") ";
                }

            } else {
                $filter = "
                    and tt.{$targetField} = :{$targetField}
                ";

                $params[$targetField] = $data[$targetField];
            }

            $query = "
			declare cur cursor read_only FOR

			select
				tt.{$entity}_id,
				ed.Lpu_did,
				ed.EvnDirection_id
			from v_{$entity}_lite as tt (nolock)
			inner join v_EvnDirection_all as ed (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
			inner join v_MedServiceElectronicQueue as mseq (nolock) on mseq.{$targetField} = tt.{$targetField}
			where
				(1=1)
				and cast({$entity}_begTime as date) > cast(dbo.tzGetdate() as date)
				and ed.EvnDirection_TalonCode is NULL
				{$filter}

			declare @{$entity}_id BIGINT
			declare @EvnDirection_id BIGINT
			declare @Lpu_did BIGINT
			declare @EvnDirection_TalonCode VARCHAR(8)
			open cur

			fetch next from cur into @{$entity}_id, @Lpu_did, @EvnDirection_id

			while @@FETCH_STATUS = 0
			begin

				SET @EvnDirection_TalonCode = NULL

				EXEC dbo.{$procedure}
					@{$entity}_id = @{$entity}_id,
					@Lpu_did = @Lpu_did,
					@EvnDirection_TalonCode = @EvnDirection_TalonCode OUTPUT

				UPDATE dbo.EvnDirection WITH (ROWLOCK) SET EvnDirection_TalonCode=@EvnDirection_TalonCode WHERE EvnDirection_id=@EvnDirection_id
				UPDATE dbo.Evn WITH (ROWLOCK) SET pmUser_updID=1 WHERE Evn_id=@EvnDirection_id

				fetch next from cur into @{$entity}_id, @Lpu_did, @EvnDirection_id
			end

			close cur
			deallocate cur
		";

            //print_r(getDebugSQL($query, $params)) ; die();
            $this->db->query($query, $params);
        }

        return array('Error_Msg' => null);
    }

    /**
     * получим тип ЭО линейный или нелинейный
     */
    public function getElectronicQueueType($data) {

        $is_linear_electronic_queue = true; // схема линейная
        $non_linear_list = $this->config->item('NON_LINEAR_ELECTRONIC_QUEUE_LIST');

        if (
            !empty($non_linear_list)
            && in_array($data['ElectronicQueueInfo_id'], $non_linear_list)
        ) $is_linear_electronic_queue = false; // схема нелинейная

        return $is_linear_electronic_queue;
    }
}