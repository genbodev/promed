<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnDirection_model.php');

class Lis_EvnDirection_model extends EvnDirection_model {
	function __construct() {
		parent::__construct();

		$this->load->swapi('common');
	}

	/**
	 * Генерация номера для направления
	 * @param array $data
	 * @param int $tryCount
	 * @return array|false
	 */
	function getEvnDirectionNumber($data, $tryCount = 0) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			select
				objectid as \"EvnDirection_Num\"
			from xp_genpmid(
				objectname := 'EvnDirection',
				lpu_id := :Lpu_id
			)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Создание нового направления на время или в очередь
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function saveEvnDirection($data) {
		if(isset($data['session']) && !empty($data['session']['CurArmType']) && ($data['session']['CurArmType']=='smo' || $data['session']['CurArmType']=='tfoms')) {
			if (empty($data['Lpu_id']) && !empty($data['Lpu_did'])) {
				$data['Lpu_id'] = $data['Lpu_did'];
			}
			if (empty($data['Lpu_id'])) {
				return array(array('Error_Msg'=>'Не указана МО назначения!', 'Error_Code'=>400, 'EvnDirection_id'=>!empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null));
			}
		}

		$data['EvnDirection_TalonCode'] = null;
		$isEvnDirectionInsert = (!isset($data['EvnDirection_id'])) || ($data['EvnDirection_id'] <= 0);

		if (empty($data['EvnStatus_id'])) {
			if (!empty($data['TimetableMedService_id'])) {
				$data['EvnStatus_id'] = '17';
			} else if (!empty($data['toQueue'])) {
				$data['EvnStatus_id'] = '10';
			}
		}

		if (!empty($data['EvnPrescr_id'])) {
			$resp = $this->common->GET('EvnPrescr/EvnDirectionIds', array(
				'EvnPrescr_id' => $data['EvnPrescr_id']
			), 'single');
			if (!$this->isSuccessful($resp)) {
				return array($resp);
			}

			if (count($resp['EvnDirection_ids']) > 0) {
				$EvnDirection_ids_str = implode(",", $resp['EvnDirection_ids']);

				$resp_ed = $this->queryResult("
					select
						ed.EvnDirection_id as \"EvnDirection_id\",
						ed.EvnStatus_id as \"EvnStatus_id\"
					from
						v_EvnDirection_all ed
					where
						ed.EvnDirection_id in ({$EvnDirection_ids_str})
						and coalesce(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
					limit 1	
				");
				if (!is_array($resp_ed)) {
					return $this->createError('','Ошибка при поиске направления по назначению');
				}
				if (!empty($resp_ed[0]['EvnDirection_id'])) {
					if ($resp_ed[0]['EvnStatus_id'] == 17) {
						// если с бирки на бирку
						$data['redirectEvnDirection'] = 800;
					} else {
						// если из очереди
						$data['redirectEvnDirection'] = 600;
					}

					$data['EvnDirection_id'] = $resp_ed[0]['EvnDirection_id'];
					if (empty($data['EvnStatus_id'])) {
						$data['EvnStatus_id'] = $resp_ed[0]['EvnStatus_id'];
					}
					$isEvnDirectionInsert = false;
				}
			}
		}

		if ($isEvnDirectionInsert) {
			$procedure = 'p_EvnDirection_ins';
		} else {
			if (!empty($data['EvnDirection_id'])) {
				$data['EvnDirection_TalonCode'] = $this->getFirstResultFromQuery("
					select
						EvnDirection_TalonCode as \"EvnDirection_TalonCode\"
					from v_EvnDirection_all
					where EvnDirection_id = :EvnDirection_id
				", $data);
			}
			$procedure = 'p_EvnDirection_upd';
		}

		if (empty($data['DirType_id'])) {
			return array(array('Error_Msg'=>'Не указан тип направления!', 'Error_Code'=>400, 'EvnDirection_id'=>!empty($data['EvnDirection_id'])?$data['EvnDirection_id']:null));
		}

		if (!empty($data['LpuSection_did'])) {
			$resp_ls = $this->queryResult("
				select
					LpuSection_id as \"LpuSection_id\",
					LpuUnit_id as \"LpuUnit_id\",
					Lpu_id as \"Lpu_id\"
				from
					v_LpuSection
				where
					LpuSection_id = :LpuSection_id
			", array(
				'LpuSection_id' => $data['LpuSection_did']
			));

			if (empty($data['EvnPrescrVK_id']) && !empty($data['LpuUnit_did']) && !empty($resp_ls[0]['LpuUnit_id']) && $resp_ls[0]['LpuUnit_id'] != $data['LpuUnit_did']) {
				return $this->createError('','Отделение, куда направили не соответствует группе отделений, куда направили, проверьте корректность введённых данных');
			}

			if (!empty($data['Lpu_did']) && !empty($resp_ls[0]['Lpu_id']) && $resp_ls[0]['Lpu_id'] != $data['Lpu_did']) {
				return $this->createError('','Некорректно заполнено поле «МО направления» или «Отделение МО». В выбранной МО отсутствует указанное отделение.');
			}
		}
        /*
             * https://redmine.swan-it.ru/issues/169405
             * Если для Организации направления (Org_oid) есть связанная запись в dbo.Lpu, то также производится сохранение связанного значения в Lpu_did
             */
        if (!empty($data['Org_oid']) && empty($data['Lpu_did'])) {
            $linkLpu_did = $this->getFirstResultFromQuery("
				select
				    Lpu_id as \"Lpu_id\"
				from
				    v_Lpu_all
				where
				    Org_id = :Org_oid
                limit 1
			", $data);
            if (!empty($linkLpu_did)) $data['Lpu_did'] = $linkLpu_did;
        }

		// https://redmine.swan.perm.ru/issues/3754
		// вообще не понятно откуда получать эти поля для параклиники и стационара
		// и почему для полки сделано именно таким образом
		$LpuSectionProfile_id = $data['LpuSectionProfile_id'];
		$Lpu_did = $data['Lpu_did'];

		if ( !empty($Lpu_did) ) {
			$Lpu_IsNotForSystem = $this->getFirstResultFromQuery("
				select coalesce(O.Org_IsNotForSystem,1) as \"Lpu_IsNotForSystem\"
				from v_Lpu L
				inner join v_Org O on O.Org_id = L.Org_id
				where Lpu_id = :Lpu_did
				limit 1
			", $data);
			if ($Lpu_IsNotForSystem === false) {
				return $this->createError('','Ошибка при получении свойств МО');
			}
		}

		// профиль должен сохраняться всегда, кроме служб (для служб берется с отделения службы, если оно есть) и кроме патологоанатомич. направлений
		if (empty($LpuSectionProfile_id)) {
			if (!empty($data['MedService_id'])) {
				// получаем профиль с отделения службы
				$query = "
					select
						ls.LpuSectionProfile_id as \"LpuSectionProfile_id\"
					from
						v_MedService ms
						left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
					where
						ms.MedService_id = :MedService_id
					limit 1	
				";

				$queryParams = array(
					'MedService_id' => $data['MedService_id'],
				);
				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (count($res) > 0) {
						$LpuSectionProfile_id = $res[0]['LpuSectionProfile_id'];
					}
				}
			} else if (!in_array(getRegionNick(), array('astra', 'ekb')) && !in_array($data['DirType_id'], array(7,18,23,26))) {
				return $this->createError('','Не указан профиль в направлении, сохранение невозможно');
			}
		}

		// сохраняем должность врача #18572
		$post_id = null;
		$is_recieve = (!empty($data['EvnDirection_IsReceive']) && $data['EvnDirection_IsReceive'] == 2);

		if (!empty($data['Lpu_sid'])) {
			$Lpu_IsNotForSystem = $this->getFirstResultFromQuery("
				select coalesce(O.Org_IsNotForSystem,1) as \"Lpu_IsNotForSystem\"
				from v_Lpu L
				inner join v_Org O on O.Org_id = L.Org_id
				where Lpu_id = :Lpu_sid
				limit 1
			", $data);
			if ($Lpu_IsNotForSystem === false) {
				return $this->createError('','Ошибка при получении свойств МО');
			}
		}

		if (!empty($data['From_MedStaffFact_id']) && $data['From_MedStaffFact_id'] < 0) {
			//Отрицательный идентификатор может прийти при импорте направлений из файла
			$data['From_MedStaffFact_id'] = null;
		} else if (!empty($data['From_MedStaffFact_id'])) {
			$MedStaffFact = $this->common->GET('MedStaffFact', array(
				'MedStaffFact_id' => $data['From_MedStaffFact_id']
			), 'single');
			if (!$this->isSuccessful($MedStaffFact)) {
				return array($MedStaffFact);
			}
			$post_id = $MedStaffFact['Post_id'];
			if (empty($data['MedPersonal_id'])) {
				$data['MedPersonal_id'] = $MedStaffFact['MedPersonal_id'];
			}
			if (empty($data['LpuSection_id']) && !$is_recieve) {
				$data['LpuSection_id'] = $MedStaffFact['LpuSection_id'];
			}
		} else if ($is_recieve && (17 == $data['DirType_id'] || (isset($Lpu_IsNotForSystem) && $Lpu_IsNotForSystem == 2) || getRegionNick() == 'buryatiya')) {
			// должность врача необязательна, т.к. поле врач необязательное поле
		} else {
			// должность врача необязательна только для системных направлений
			if ( empty($data['EvnDirection_IsAuto']) || 2 != $data['EvnDirection_IsAuto'] ) {
				return array(array('Error_Msg'=>'Не указана должность врача!', 'Error_Code'=>400, 'EvnDirection_id'=>$data['EvnDirection_id'],));
			}
		}

		if (empty($data['EvnDirection_setDT'])) {
			if ( empty($data['EvnDirection_setDate']) ) {
				return $this->createError('400','Не указана дата направления!');
			}
			$data['EvnDirection_setDT'] = $data['EvnDirection_setDate'];
		}

		if ( $data['EvnDirection_setDT'] instanceof DateTime ) {
			$data['year'] = $data['EvnDirection_setDT']->format('Y');
		} else {
			$data['year'] = substr($data['EvnDirection_setDT'], 0, 4);
		}

		// Если нет номера направления (автоматическое), то генерим его по текущей ЛПУ
		if (empty($data['EvnDirection_Num'])) {
			$response = $this->getEvnDirectionNumber($data);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				// временно согласно задаче #10647 убираю буквенное обозначение для системных направлений
				//$data['EvnDirection_Num'] = 'A'.$response[0]['EvnDirection_Num'];
				$data['EvnDirection_Num'] = $response[0]['EvnDirection_Num'];
			}
		}

		// берём услугу из заказа.
		if (empty($data['UslugaComplex_id']) && !empty($data['order'])) {
			$orderparams = json_decode(toUTF($data['order']), true);
			if (!empty($orderparams['UslugaComplex_id'])) {
				$data['UslugaComplex_id'] = $orderparams['UslugaComplex_id'];
			}
		}
		if (empty($data['UslugaComplex_id'])) {
			$data['UslugaComplex_id'] = null;
		}


		// перезапись и перенаправление, особая логика перед записью.
		//пока нет api рассписания
		if (!empty($data['redirectEvnDirection'])) {
			$ed_data = $this->getFirstRowFromQuery("
				select
					ED.TimetableMedService_id as \"TimetableMedService_id\"
				from
					v_EvnDirection_all ED 
				where
					ED.EvnDirection_id = :EvnDirection_id
				limit 1
			", $data);

			$resp = $this->common->POST('EvnDirection/beforeRedirectLis', [
				'redirectEvnDirection' => $data['redirectEvnDirection'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'oldTimetableMedService_id' => $ed_data['TimetableMedService_id'],
				'newTimetableMedService_id' => $data['TimetableMedService_id'],
			], 'single');
			if (!$this->isSuccessful($resp)) {
				return [$resp];
			}
		}

		if ($is_recieve) {
			// При сохранении нужно производить проверку, не существует ли направление с такими же параметрами: Лпу направления, тип, номер, дата, пациент. Если есть выдавать сообщение «Направление уже создано направившей стороной».
			if (empty($data['Person_id'])) {
				$resp = $this->common->GET('Person/IdByPersonEvn', array(
					'PersonEvn_id' => $data['PersonEvn_id']
				), 'list');
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				$data['Person_id'] = $resp[0]['Person_id'];
			}

			$query = "
				select
					EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnDirection_all
				where
					EvnDirection_Num = :EvnDirection_Num
					and Lpu_id = :Lpu_id
					and DirType_id = :DirType_id
					and EvnDirection_setDT = :EvnDirection_setDT
					and Person_id = :Person_id
			";
			$params = array(
				'EvnDirection_Num' => $data['EvnDirection_Num'],
				'Lpu_id' => $data['Lpu_id'],
				'DirType_id' => $data['DirType_id'],
				'EvnDirection_setDT' => $data['EvnDirection_setDT'],
				'Person_id' => $data['Person_id']
			);
			if (!empty($data['EvnDirection_id'])) {
				$query .= ' and EvnDirection_id <> :EvnDirection_id ';
				$params['EvnDirection_id'] = $data['EvnDirection_id'];
			}

			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return $this->createError('','Ошибка при проверке наличия дублей направления');
			}
			if (!empty($resp[0]['EvnDirection_id'])) {
				return $this->createError('','Направление уже создано направившей стороной');
			}
		}

		if (empty($data['PayType_id'])) {
			$data['PayType_id'] = null;

			if (!empty($data['EvnPrescr_id'])) {
				$res = $this->common->GET('EvnPrescr/PayTypeFromEvn', array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				), 'single');
				if (!$this->isSuccessful($res)) {
					return $res;
				}
				if (!empty($res['PayType_id'])) {
					$data['PayType_id'] = $res['PayType_id'];
				}
			}
		}

		// если указано родительское событие, то проверяем, чтобы пациент направления соответствовал пациенту события (refs #96733)
		if (!empty($data['EvnDirection_pid'])) {
			$res = $this->common->GET('EvnDirection/EvnFromPersonEvn', [
				'EvnDirection_pid' => $data['EvnDirection_pid'],
				'PersonEvn_id' => $data['PersonEvn_id']
			], 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
		}

		if ($isEvnDirectionInsert && empty($data['isElectronicQueueRedirect'])) {

			$talonCodeData = $this->common->GET('EvnDirection/EvnDirectionTalonCode', array(
				'TimetableMedService_id' => !empty($data['TimetableMedService_id']) ? $data['TimetableMedService_id'] : null,
				'TimetableGraf_id' => !empty($data['TimetableGraf_id']) ? $data['TimetableGraf_id'] : null,
				'TimetableResource_id' => !empty($data['TimetableResource_id']) ? $data['TimetableResource_id'] : null,
				'Lpu_did' => $data['Lpu_did'] ? $data['Lpu_did'] : $data['Lpu_id']
			), 'single');

			if (!$this->isSuccessful($talonCodeData)) { return $talonCodeData; }
			$data['EvnDirection_TalonCode'] = $talonCodeData['EvnDirection_TalonCode'];
		}

		if (empty($data['RecMethodType_id']) && !empty($data['ARMType_id'])) {
			$res = $this->common->GET('EvnDirection/RecMethodType', array(
				'ARMType_id' => $data['ARMType_id']
			), 'single');
			if (!$this->isSuccessful($res)) {
				return $res;
			}
			$data['RecMethodType_id'] = $res['RecMethodType_id'];
		}
		$query = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\",
				:EvnDirection_TalonCode as \"EvnDirection_TalonCode\"
			from {$procedure}(
				ConsultingForm_id := :ConsultingForm_id,
				EvnDirection_id := :EvnDirection_id,
				EvnDirection_pid := :EvnDirection_pid,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnDirection_setDT := :EvnDirection_setDT,
				DirType_id := :DirType_id,
				MedicalCareFormType_id := :MedicalCareFormType_id,
				StudyTarget_id := :StudyTarget_id,
				PayType_id := :PayType_id,
				Diag_id := :Diag_id,
				EvnDirection_Num := :EvnDirection_Num,
				EvnDirection_Descr := :EvnDirection_Descr,
				Lpu_did := :Lpu_did, --куда направлен
				LpuSection_did := :LpuSection_did,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				Lpu_id := :Lpu_id, -- кто направил
				LpuSection_id := :LpuSection_id,
				LpuUnit_did := :LpuUnit_did,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_zid := :MedPersonal_zid,
				MedPersonal_Code := :MedPersonal_Code,
				MedService_id := :MedService_id,
				EvnDirection_desDT := :EvnDirection_desDT,
				EvnDirection_IsAuto := :EvnDirection_IsAuto,
				EvnDirection_IsCito := :EvnDirection_IsCito,
				EvnStatus_id := :EvnStatus_id,
				Post_id := :Post_id,
				Lpu_sid := :Lpu_sid,
				Org_sid := :Org_sid,
				Org_oid := :Org_oid,
				PrehospDirect_id := :PrehospDirect_id,
				TimetableGraf_id := :TimetableGraf_id,
				TimetableStac_id := :TimetableStac_id,
				TimetableResource_id := :TimetableResource_id,
				TimetableMedService_id := :TimetableMedService_id,
				EvnDirection_IsNeedOper := :EvnDirection_IsNeedOper,
				EvnQueue_id := :EvnQueue_id,
				ARMType_id := :ARMType_id,
				EvnDirection_IsReceive := :EvnDirection_IsReceive,
				Resource_id := :Resource_id,
				MedPersonal_did := :MedPersonal_did,
				RemoteConsultCause_id := :RemoteConsultCause_id,
				MedSpec_fid := :MedSpec_fid,
				UslugaComplex_did := :UslugaComplex_did,
				FSIDI_id := :FSIDI_id,
				LpuUnitType_id := :LpuUnitType_id,
				EvnDirection_TalonCode := :EvnDirection_TalonCode,
				RecMethodType_id := :RecMethodType_id,
				ConsultationForm_id := :ConsultationForm_id,
				pmUser_id := :pmUser_id
			)";
		$queryParams = array(
			'ConsultingForm_id' => !isset($data['ConsultingForm_id']) ? NULL : $data['ConsultingForm_id'],
			'EvnDirection_id' => ( !isset($data['EvnDirection_id']) || $data['EvnDirection_id'] <= 0 ? NULL : $data['EvnDirection_id'] ),
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDirection_setDT' => $data['EvnDirection_setDT'],
			'DirType_id' => $data['DirType_id'],
			'MedicalCareFormType_id' => !isset($data['MedicalCareFormType_id']) ? NULL : $data['MedicalCareFormType_id'],
			'StudyTarget_id' => !isset($data['StudyTarget_id']) ? NULL : $data['StudyTarget_id'],
			'PayType_id' => $data['PayType_id'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'EvnDirection_Descr' => $data['EvnDirection_Descr'],
			'LpuSection_did' => $data['LpuSection_did'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedStaffFact_id' => $data['From_MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedPersonal_zid' => $data['MedPersonal_zid'],
			'MedPersonal_Code' => !isset($data['MedPersonal_Code']) ? NULL : $data['MedPersonal_Code'],
			'EvnDirection_IsNeedOper' => (isset($data['EvnDirection_IsNeedOper'])&&$data['EvnDirection_IsNeedOper']>0)? 2 : 1,
			'EvnDirection_desDT'=>!isset($data['EvnDirection_desDT']) ? NULL : $data['EvnDirection_desDT'],
			'TimetableGraf_id' => !isset($data['TimetableGraf_id']) ? NULL : $data['TimetableGraf_id'],
			'TimetableStac_id' => !isset($data['TimetableStac_id']) ? NULL : $data['TimetableStac_id'],
			'TimetablePar_id' => !isset($data['TimetablePar_id']) ? NULL : $data['TimetablePar_id'],
			'TimetableResource_id' => !isset($data['TimetableResource_id']) ? NULL : $data['TimetableResource_id'],
			'MedService_id' => empty($data['MedService_id']) ? NULL : $data['MedService_id'], // служба куда направили
			'MedService_did' => empty($data['MedService_id']) ? NULL : $data['MedService_id'], // служба в которую ставят в очередь равна службе куда направили
			'Resource_id' => empty($data['Resource_id']) ? NULL : $data['Resource_id'],
			'Resource_did' => empty($data['Resource_id']) ? NULL : $data['Resource_id'],
			'RemoteConsultCause_id' => empty($data['RemoteConsultCause_id']) ? NULL : $data['RemoteConsultCause_id'],
			'TimetableMedService_id' => empty($data['TimetableMedService_id']) ? NULL : $data['TimetableMedService_id'],
			'EvnQueue_id' => empty($data['EvnQueue_id']) ? NULL : $data['EvnQueue_id'],
			'EvnDirection_IsAuto' => empty($data['EvnDirection_IsAuto']) ? NULL : $data['EvnDirection_IsAuto'],
			'EvnDirection_IsCito' => empty($data['EvnDirection_IsCito']) ? NULL : $data['EvnDirection_IsCito'],
			'ConsultationForm_id' => empty($data['ConsultationForm_id']) ? NULL : $data['ConsultationForm_id'],
			'EvnStatus_id' => empty($data['EvnStatus_id']) ? NULL : $data['EvnStatus_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuSectionProfile_id' => $LpuSectionProfile_id,
			'Lpu_did' => $Lpu_did,
			'Post_id' => $post_id,
			'Lpu_sid' => empty($data['Lpu_sid']) ? $data['Lpu_id']: $data['Lpu_sid'],
			'Org_sid' => empty($data['Org_sid']) ? NULL : $data['Org_sid'],
			'Org_oid' => empty($data['Org_oid']) ? NULL : $data['Org_oid'],
			'PrehospDirect_id' => empty($data['PrehospDirect_id']) ? NULL : $data['PrehospDirect_id'],
			'EvnUslugaPar_id' => !empty($data['EvnUslugaPar_id']) ? $data['EvnUslugaPar_id'] : null,
			'EvnQueue_pid' => !empty($data['EvnQueue_pid']) ? $data['EvnQueue_pid'] : null,
			'LpuUnit_did' => !empty($data['LpuUnit_did']) ? $data['LpuUnit_did'] : null,
			'LpuSectionProfile_did' => !empty($data['LpuSectionProfile_did']) ? $data['LpuSectionProfile_did'] : null,
			'MedStaffFact_did' => !empty($data['MedStaffFact_did']) ? $data['MedStaffFact_did'] : null,
			'MedPersonal_did' => !empty($data['MedPersonal_did']) ? $data['MedPersonal_did'] : null,
			'UslugaComplex_did' => !empty($data['UslugaComplex_did']) ? $data['UslugaComplex_did'] : (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : null),
            'FSIDI_id' => !empty($data['FSIDI_id']) ? $data['FSIDI_id'] : null,
			'LpuUnitType_id' => !empty($data['LpuUnitType_id']) ? $data['LpuUnitType_id'] : (!empty($data['LpuUnitType_did']) ? $data['LpuUnitType_did'] : null),
			'MedSpec_fid' => !empty($data['MedSpec_fid']) ? $data['MedSpec_fid'] : null,
			'ARMType_id' => !empty($data['ARMType_id']) ? $data['ARMType_id'] : null,
			//параметра @EvnCourse_id нет в p_EvnDirection_insToQueue
			//@EvnCourse_id = :EvnCourse_id,
			//'EvnCourse_id' => !empty($data['EvnCourse_id']) ? $data['EvnCourse_id'] : null,
			'EvnDirection_IsReceive' => !empty($data['EvnDirection_IsReceive']) ? $data['EvnDirection_IsReceive'] : null,
			'EvnDirection_TalonCode' => !empty($data['EvnDirection_TalonCode']) ? $data['EvnDirection_TalonCode'] : null,
			'RecMethodType_id' => !empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : 10 // промед
		);

		if (empty($queryParams['PrehospDirect_id']) && !empty($data['order'])) {
			$order = json_decode($data['order'], true);
			if (isset($order['PrehospDirect_id'])) {
				$queryParams['PrehospDirect_id'] = $order['PrehospDirect_id'];
			}
		}

		//echo '<pre>',print_r($queryParams),'</pre>'; die();

		//echo getDebugSQL($query, $queryParams);die();
		$response = $this->queryResult($query, $queryParams);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении направления');
		}
		if (!$this->isSuccessful($response)) {
			return $response;
		}
		$response[0]['EvnDirection_Num'] = $data['EvnDirection_Num'];
		$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];
		//привязываем направление к бирке(в версиях без лис это в процедуре)
		if ($procedure == 'p_EvnDirection_ins' && !empty($data['TimetableMedService_id'])) {
			$params = [
				'TimetableMedService_id' => $queryParams['TimetableMedService_id'],
				'Person_id' => $data['Person_id'],
				'Evn_id' => $queryParams['EvnDirection_pid'],
				'RecClass_id' => 1,
				'EvnDirection_id' => $response[0]['EvnDirection_id'],
				'EvnDirection_IsAuto' => $queryParams['EvnDirection_IsAuto'],
				'pmUser_id' => $queryParams['pmUser_id'],
			];
			$respTTMS = $this->common->POST('TimetableMedService/recordEvnDirection', $params, 'single');
			if (!$this->isSuccessful($respTTMS)) {
				$this->common->DELETE('TimetableMedService', [
					'cancelType' => 'cancel',
					'TimetableMedService_id' => $data['TimetableMedService_id'],
					'DirFailType_id' => '14',
					'EvnStatusCause_id' => '3',
					'EvnComment_Comment' => ''
				], 'single');
				return $respTTMS;
			}
		}

		collectEditedData(empty($queryParams['EvnDirection_id'])?'ins':'upd', 'EvnDirection', $response[0]['EvnDirection_id']);

		$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];

		if (isset($data['EvnPrescr_id']) && isset($data['PrescriptionType_Code']) && is_array($response) && count($response)==1 &&
			empty($response[0]['Error_Msg']) && !empty($response[0]['EvnDirection_id']) && empty($queryParams['EvnDirection_id']))
		{
			$resp = $this->common->POST('EvnPrescr/checkAndDirectEvnPrescr', $data, 'single');
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		if (!empty($data['EvnUsluga_id'])) {
			$this->load->model('Lis_EvnUsluga_model');
			$this->Lis_EvnUsluga_model->saveEvnDirectionInEvnUsluga($data);
		}

		if (!empty($data['MedService_id'])) {
			// проверяем тип, если лаборатория или пункт забора, значит создаём ещё и заявку.
			$MedServiceType_SysNick = $this->getFirstResultFromQuery("
				select
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
				from
					v_MedService ms
					inner join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where
					ms.MedService_id = :MedService_id
			", $data);

			if (!empty($MedServiceType_SysNick) && in_array($MedServiceType_SysNick, array('lab', 'pzm', 'func', 'microbiolab'))) {
				$resp = $this->makeEvnLabRequest($data);
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				$response[0]['EvnLabRequest_id'] = $resp[0]['EvnLabRequest_id'];
			}
		}

		if (!empty($data['EvnDirection_TalonCode'])) {
			$this->common->POST('ElectronicTalon/sendMessage', $data);
		}
		return $response;
	}

	/**
	 * Удаление направления
	 *
	 * @param $data
	 * @return array
	 */
	function deleteEvnDirection($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			select
				error_message as \"Error_Msg\",
				error_code as \"Error_Code\"
			from p_evndirection_del(
				EvnDirection_id := :EvnDirection_id,
				pmUser_id := :pmUser_id
			)	
		";
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при выполнении запроса к базе данных (удаление направления)');
		}
		return $resp;
	}

	/**
	 * Decline or Cancel direction
	 */
	function execDelDirection($cancelProc, $queryParams) {
		$query = "
			with myvars as (
				select dbo.tzgetdate() as curtime
			)
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from {$cancelProc}(
				EvnDirection_id := :EvnDirection_id,
				DirFailType_id := :DirFailType_id,
				EvnComment_Comment := :EvnComment_Comment,
				EvnStatusCause_id := :EvnStatusCause_id,
				pmUser_id := :pmUser_id,
				Lpu_cid := :Lpu_cid,
				MedStaffFact_fid := :MedStaffFact_fid
			)
		";

		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при отмене направления');
		}

		return $resp;
	}

	/**
	 * Получение идентификатора направления выписанного на бирку
	 * @param array $data
	 * @return int|null
	 */
	function getEvnDirectionIdByRecord($data) {
		if ($data['object'] != 'TimetableMedService') {
			return null;
		}
		$resp = $this->common->GET('TimetableMedService', array(
			'TimetableMedService_id' => $data['TimetableMedService_id']
		), 'single');
		if (empty($resp)) {
			return null;
		}
		return $resp['EvnDirection_id'];
	}

	/**
	 * Проверка может ли направление быть отменено
	 */
	function checkEvnDirectionCanBeCancelled($data) {
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (isset($isEMDEnabled) && ($isEMDEnabled == 1 || $isEMDEnabled === true)) {
			$checkResult = $this->common->GET('EMD/DocumentListByEvn', [
				'Evn_id' => $data['EvnDirection_id'],
				'EvnClass_SysNick' => 'EvnDirection'
			], 'single');
			if(!$this->isSuccessful($checkResult)) {
				return $checkResult['Error_Msg'];
			}
			//echo '<pre>',print_r($checkResult),'</pre>'; die();
			if (!empty($checkResult)) {
				return "Отмена направления невозможна, т.к. оно зарегистрировано в РЭМД";
			}
		}

		// Если направление связано с заявкой, то отменить можно направление, заявка по которому имеет статус "Новая"
		$query = "
			(
				select
					1 as code
				from v_EvnLabRequest elr
				inner join v_EvnStatus es on es.EvnStatus_id = elr.EvnStatus_id
				where elr.EvnDirection_id = :EvnDirection_id and es.EvnStatus_SysNick != 'New'
				limit 1
			) union all (
				select
					2 as code
				from v_EvnDirection_all ed
				where ed.EvnDirection_id = :EvnDirection_id and ed.EvnStatus_id not in (10, 12, 13, 17)
				limit 1
			)
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return 'Ошибка при проверке статуса направления';
		}
		if (count($resp) > 0) {
			if (1 == $resp[0]['code']) {
				return 'Нельзя отменить направление, т.к. заявка по направлению имеет статус не "Новая"';
			} else if (3 == $resp[0]['code']){
				return 'Нельзя отменить направление, т.к. выполнена консультационая услуга';
			} else {
				return 'Можно отменить направление, если направление имеет статус "Записано на бирку" или "В очереди"';
			}
		}

		return '';
	}

	/**
	 * Отмена направления по записи
	 */
	function cancelEvnDirectionbyRecord($data) {
		if (empty($data['EvnDirection_id'])) {
			$data['object'] = 'TimetableMedService';
			$data['EvnDirection_id'] = $this->getEvnDirectionIdByRecord($data);
		}
		if ( !empty($data['EvnDirection_id']) ) {
			$error = $this->checkEvnDirectionCanBeCancelled($data);
			if (!empty($error)) {
				return $error;
			}

			$resp = $this->common->POST('ElectronicTalon/cancelByEvnDirection', array(
				'EvnDirection_id' => $data['EvnDirection_id']
			), 'single');
			if (!$this->isSuccessful($resp)) {
				return $resp['Error_Msg'];
			}

			$data['Lpu_cid'] = $data['session']['lpu_id'];
			$resp = $this->cancelEvnDirection($data);

			if ($resp) {
				if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
					// возвращаем ошибку
					return $resp[0]['Error_Msg'];
				}
				// Если после отмены направление не было удалено, посылаем сообщение
				//todo: check
				/*if ($this->getEvnDirectionIdByRecord($data)) {
					$this->sendCancelEvnDirectionMessage($data);
				}*/
			}
		}

		return '';
	}

	/**
	 * Отмена направления
	 */
	function cancelEvnDirection($data) {
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'DirFailType_id' => !empty($data['DirFailType_id'])?$data['DirFailType_id']:null,
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id'])?$data['EvnStatusCause_id']:null,
			'EvnComment_Comment' => !empty($data['EvnComment_Comment'])?$data['EvnComment_Comment']:null,
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_cid' => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'MedStaffFact_fid' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null
		);

		$cancelProc = "p_EvnDirection_cancel";
		if (!empty($data['cancelType']) && $data['cancelType'] == 'decline') {
			$cancelProc = "p_EvnDirection_decline";
		}

		$resp = $this->execDelDirection($cancelProc, $queryParams);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		// если указан статус очереди, то при отмене шлем пуш и емэйл для пользователя и оповещение в портал
		/*if (!empty($data['EvnQueueStatus_id']) && $data['EvnQueueStatus_id'] == 4) {
			$this->load->model("Queue_model");
			$this->Queue_model->sendRejectNotify($data);
		}*/

		return $resp;
	}

	/**
	 * Создаем заявку на исследование
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function makeEvnLabRequest($data) {
		$response = array(
			'success' => true,
			'EvnLabRequest_id' => null,
		);

		// проверяем что заявка ещё не создана
		$EvnLabRequest_id = $this->getFirstResultFromQuery("
			select
				EvnLabRequest_id as \"EvnLabRequest_id\"
			from
				v_EvnLabRequest elr
			where
				elr.EvnDirection_id = :EvnDirection_id
		", $data);

		if (empty($EvnLabRequest_id)) {

			// если указана бирка, то берем время и место выполнения из неё
			if (!empty($data['TimetableMedService_id'])) {

				$data['EvnLabRequest_prmTime'] = null;

				$pzmRecordData = $this->common->GET('TimetableMedService/getPzmRecordData', array(
					'TimetableMedService_id' => $data['TimetableMedService_id']
				), 'single');

				if (!empty($pzmRecordData['TimetableMedService_id'])) {

					$data['MedService_pzid'] = $pzmRecordData['MedService_pzid'];
					if (!empty($pzmRecordData['EvnLabRequest_prmTime'])) {
						$data['EvnLabRequest_prmTime'] = $pzmRecordData['EvnLabRequest_prmTime'];
					} else {
						$data['EvnLabRequest_prmTime'] = $data['EvnDirection_setDT'];
					}
				}
			}

			if (empty($data['MedService_pzid'])) {

				$data['MedService_pzid'] = null;

				if (!empty($data['order'])) {
					$orderparams = json_decode(toUTF($data['order']), true);
					if (!empty($orderparams['MedService_pzid'])) {
						$data['MedService_pzid'] = $orderparams['MedService_pzid'];
					}
				}
			}

			$data['EvnLabRequest_id'] = null; // создаём заявку
			$query = "
				select
					EvnLabRequest_id as \"EvnLabRequest_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_EvnLabRequest_ins(
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					Lpu_id := :Lpu_id,
					PayType_id := :PayType_id,
					EvnDirection_id := :EvnDirection_id,
					MedService_id := :MedService_id,
					MedService_sid := :MedService_pzid,
					EvnLabRequest_prmTime := :EvnLabRequest_prmTime,
					EvnLabRequest_IsCito := :EvnDirection_IsCito,
					pmUser_id := :pmUser_id
				)
			";

			$params = array(
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Lpu_id' => $data['Lpu_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'MedService_id' => $data['MedService_id'],
				'MedService_pzid' => $data['MedService_pzid'],
				'EvnLabRequest_prmTime' => !empty($data['EvnLabRequest_prmTime']) ? $data['EvnLabRequest_prmTime'] : null,
				'EvnDirection_IsCito' => empty($data['EvnDirection_IsCito']) ? NULL : $data['EvnDirection_IsCito'],
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $params);
			if (is_object($result)) {

				$resp = $result->result('array');
				if (!empty($resp[0]['EvnLabRequest_id'])) {
					$response['EvnLabRequest_id'] = $resp[0]['EvnLabRequest_id'];
					// сразу добавляем пробу и исследование
					$this->load->model('EvnLabRequest_model');

					if (!empty($data['UslugaComplex_id'])) {

						$this->load->model('EvnLabSample_model');
						$resp_labsample = $this->EvnLabSample_model->saveLabSample(array(
							'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
							'RefSample_id' => null,
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'MedService_id' => $data['MedService_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						if (!empty($resp_labsample[0]['EvnLabSample_id'])) {

							if (empty($data['PayType_id'])) {
								$data['PayType_id'] = $this->getFirstResultFromQuery("
									select
										PayType_id as \"PayType_id\"
									from
										v_PayType
									where
										PayType_SysNick = :PayType_SysNick
									limit 1
								", array('PayType_SysNick' => getPayTypeSysNickOMS()));

								if (empty($data['PayType_id'])) {$data['PayType_id'] = null;}
							}

							// исследование
                            $params = $data;
							$params['EvnLabRequest_id'] = $resp[0]['EvnLabRequest_id'];
							$params['EvnLabSample_id'] = $resp_labsample[0]['EvnLabSample_id'];
							$params['RefSample_id'] = null;
                            $orderparams = json_decode(toUTF($data['order']), true);
							$params['researches'][] = $orderparams['UslugaComplexMedService_id'];
                            $checked_tests = json_decode($orderparams['checked']);

							// сохраняем услуги и тесты, разбиваем на пробы по биоматериалу
                            $this->EvnLabSample_model->saveLabSampleResearches($params, $checked_tests);

						}
					}
					$this->updateEvnStatusPostgre(array(
						'Evn_id' => $resp[0]['EvnLabRequest_id'],
						'EvnStatus_SysNick' => 'New',
						'EvnClass_SysNick' => 'EvnLabRequest',
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем количество тестов
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaCount(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем названия услуг
					$this->EvnLabRequest_model->ReCacheLabRequestUslugaName(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// кэшируем статус проб в заявке
					$this->EvnLabRequest_model->ReCacheLabRequestSampleStatusType(array(
						'EvnLabRequest_id' => $resp[0]['EvnLabRequest_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				} else {
					$this->rollbackTransaction();
					throw new Exception('Не удалось сохранить лабораторную заявку', 500);
				}
			}
		} else {
			$this->load->model('EvnLabRequest_model');
			$response['EvnLabRequest_id'] = $EvnLabRequest_id;

			// если направление было связано с EvnLabRequest, нужно перекешировать EvnLabRequest_prmTime - время записи
			$this->EvnLabRequest_model->ReCacheLabRequestPrmTime(array(
				'EvnLabRequest_id' => $EvnLabRequest_id,
				'EvnDirection_id' => $data['EvnDirection_id']
			));
			if (!empty($data['PayType_id'])) { // надо обновить в заявке Вид оплаты

				$query = "
					update
						EvnLabRequest
					set
						PayType_id = :PayType_id
					where
						Evn_id = :EvnLabRequest_id
				";

				$this->db->query($query, array(
					'PayType_id' => $data['PayType_id'],
					'EvnLabRequest_id' => $EvnLabRequest_id
				));
			}
		}

		return array($response);
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	function doLoadView($data) {
		$sysnick = $data['sysnick'];
		$addJoin = '';
		$filter = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		$filterAccessRightsDenied = getAccessRightsTestFilter('UCMPp.UslugaComplex_id', false, true);

		$testFilter = str_replace('isnull', 'coalesce(to_char', $testFilter);
		$testFilter = str_replace(", '')", ", '99999999'), '')", $testFilter);
		$filterAccessRightsDenied = str_replace('isnull', 'coalesce(to_char', $filterAccessRightsDenied);
		$filterAccessRightsDenied = str_replace(", '')", ", '99999999'), '')", $filterAccessRightsDenied);

		$ucpCondition = ' and UCp.UslugaComplex_id is null ';
		if (!$sysnick) $ucpCondition = "";

		if (!empty($testFilter)){
			$filter .= "
				and (
					ED.MedPersonal_id = :MedPersonal_id or
					exists (
						select
							Evn_id
						from
							v_Evn
						where
							Evn_id = :EvnPrescr_pid
							and EvnClass_sysNick = 'EvnSection'
							and Evn_setDT <= :EvnPrescr_setDT
							and (Evn_disDT is null
								or Evn_disDT >= :EvnPrescr_setDT)
						limit 1
					)
					or ({$testFilter} {$ucpCondition})
				)";
		}

		if ($sysnick) {
			$addJoin = "left join lateral(
					select
						UCMPp.UslugaComplex_id
					from
						v_UslugaComplexMedService UCMPp
					inner join v_EvnLabRequestUslugaComplex ELRUC on UCMPp.UslugaComplex_id = ELRUC.UslugaComplex_id
						and ELRUC.EvnLabRequest_id = LR.EvnLabRequest_id
					inner join v_EvnLabSample ELS on ELS.EvnLabSample_id = ELRUC.EvnLabSample_id
						and ELS.LabSampleStatus_id IN(4,6)
					where
						UCMS.UslugaComplexMedService_id = UCMPp.UslugaComplexMedService_pid
						".((!empty($filterAccessRightsDenied))?"and ".$filterAccessRightsDenied:'')."
					limit 1
				) UCp on true
			";
		}

		$UslugaComplex_Code = "UC.UslugaComplex_Code as \"UslugaComplex_Code\"";
		$UslugaComplex_Name = "coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\"";

		if (!empty($this->options['prescription']['enable_grouping_by_gost2011']) || $this->options['prescription']['service_name_show_type'] == 2) {
			$UslugaComplex_Code = 'UC11.UslugaComplex_Code as "UslugaComplex_Code"';
			$UslugaComplex_Name = 'UC11.UslugaComplex_Name as "UslugaComplex_Name"';
		}

		$query = "
			select
				:EvnPrescr_id as \"EvnPrescr_id\",
				case when ED.EvnDirection_id is null OR coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
				case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
				ED.DirFailType_id as \"DirFailType_id\",
				ED.EvnStatus_id as \"EvnStatus_id\",
				null as \"QueueFailCause_id\",
				null as \"EvnQueue_id\",
				case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				case when ED.EvnDirection_Num is null then '' else cast(ED.EvnDirection_Num as varchar) end as \"EvnDirection_Num\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				case
					when :TimetableMedService_id != '1' then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(Lpu.Lpu_Nick,'')
					when ED.EvnStatus_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then coalesce(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							else coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
						end ||' / '|| coalesce(Lpu.Lpu_Nick,'')
				else '' end as \"RecTo\",
				case
					when :TimetableMedService_id != '1' then coalesce(to_char(:TimetableMedService_begTime::timestamp, 'dd.mm.yyyy HH24:MI:SS'),'')
					when ED.EvnStatus_id is not null AND ED.EvnDirection_failDT is null then 'В очереди с '|| coalesce(to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy'),'') --пока так
				else '' end as \"RecDate\",
				case
					when :TimetableMedService_id != '1' then 'TimetableMedService'
					when ED.EvnStatus_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
				case
					when :TimetableMedService_id != '1'  then :TimetableMedService_id -- пока так
				else null end as \"timetable_id\",
				DT.DirType_Code as \"DirType_Code\",
				UCMS.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				null as \"EvnStatusCause_id\",
				null as \"EvnStatusHistory_Cause\",
				case when exists(
					select
						ucms2.UslugaComplexMedService_id
					from
						v_UslugaComplexMedService ucms2
						inner join lis.v_AnalyzerTest at2 on at2.UslugaComplexMedService_id = ucms2.UslugaComplexMedService_id
						inner join lis.v_Analyzer a2 on a2.Analyzer_id = at2.Analyzer_id
					where
						ucms2.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id
						and coalesce(at2.AnalyzerTest_IsNotActive, 1) = 1 and coalesce(a2.Analyzer_IsNotActive, 1) = 1
					limit 1	
				) then 1 else 0 end as \"isComposite\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
				{$UslugaComplex_Code},
				{$UslugaComplex_Name},
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				CASE 
					when Lpu.Lpu_id is not null and Lpu.Lpu_id <> LpuSession.Lpu_id then 2 else 1
				end as \"otherMO\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				case when ES.EvnStatus_Name is null and (ED.DirFailType_id > 0 ) then 'Отменено' else ES.EvnStatus_Name end as \"EvnStatus_Name\",
				DFT.DirFailType_Name as \"EvnStatusCause_Name\",
				to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT), 'dd.mm.yyyy') as \"EvnDirection_statusDate\",
				lr.EvnStatus_id as \"EvnLabRequestStatus\"
			from
				v_EvnDirection_all ED
				left join v_UslugaComplex UC on UC.UslugaComplex_id = :UslugaComplex_id
				left join v_UslugaComplex UC11 on UC11.UslugaComplex_id = UC.UslugaComplex_2011id
				left join v_MedService MS on ms.MedService_id  = ED.MedService_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join lateral(
					select
						EUP.EvnUslugaPar_id
					from
						v_EvnUslugaPar EUP
					where
						EUP.EvnDirection_id = ED.EvnDirection_id
						and EUP.EvnPrescr_id = :EvnPrescr_id
					limit 1	
				) EUP on true
				left join v_LpuUnit LU on LU.LpuUnit_id = coalesce(ED.LpuUnit_did, MS.LpuUnit_id)
				left join v_LpuSectionProfile LSPD on LSPD.LpuSectionProfile_id = coalesce(ED.LpuSectionProfile_id, LS.LpuSectionProfile_id)
			  	-- ЛПУ
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id)
				left join v_Lpu LpuSession on LpuSession.Lpu_id = :Lpu_id
				left join lateral(
					select
						EvnUsluga_id,
						EvnUsluga_setDT
					from
						v_EvnUsluga
					where
						:EvnPrescr_IsExec = 2
						and UC.UslugaComplex_id is not null
						and EvnPrescr_id = :EvnPrescr_id
					limit 1	
				) EU on true
				left join v_EvnLabRequest LR on LR.EvnDirection_id = ED.EvnDirection_id
				-- услуга на службе
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = LR.MedService_id
					and UCMS.UslugaComplex_id = :UslugaComplex_id
					and UCMS.UslugaComplexMedService_pid is null
				left join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				{$addJoin}
			where
				ED.EvnDirection_id = :EvnDirection_id
				and coalesce(ED.EvnStatus_id, 16) not in (12,13)
				{$filter}
		";

		$list = array();
		foreach($data['EvnPrescrList'] as $EvnPrescr) {
			$EvnDirectionData = $this->queryResult($query, $EvnPrescr);
			if (!is_array($EvnDirectionData)) {
				return false;
			}
			if (count($EvnDirectionData) == 0) {
				continue;
			}

			$EvnLabSamplesDefect = $this->getEvnLabSamplesDefect($EvnPrescr);
			if (!is_array($EvnLabSamplesDefect)) {
				return false;
			}
			foreach($EvnDirectionData as &$item) {
				$item['EvnLabSampleDefect'] = $EvnLabSamplesDefect;
			}

			$list = array_merge($list, $EvnDirectionData);
		}

		return $list;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnLabSamplesDefect($data) {
		if (!isset($data['EvnDirection_id']) || empty($data['EvnDirection_id'])) {
			return false;
		}

		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id']
		);

		$query = "
			select
				ELS.EvnLabSample_id as \"EvnLabSample_id\",
				DCT.DefectCauseType_Name as \"DefectCauseType_Name\"
			from 
				v_EvnLabRequest ELR
				inner join v_EvnLabSample ELS on ELS.EvnLabRequest_id = ELR.EvnLabRequest_id
				inner join lis.v_DefectCauseType DCT on DCT.DefectCauseType_id = ELS.DefectCauseType_id
			where 
				ELR.EvnDirection_id = :EvnDirection_id and 
				ELS.LabSampleStatus_id = 5
		";

		//echo getDebugSql($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Проверка возможности удалить направление
	*/
	function checkCanBeCancelled($data) {
		$query = "
			(
				select
					1 as code
				from v_EvnLabRequest elr
				inner join v_EvnStatus es on es.EvnStatus_id = elr.EvnStatus_id
				where elr.EvnDirection_id = :EvnDirection_id and es.EvnStatus_SysNick != 'New'
				limit 1
			) union all (
				select
					2 as code
				from v_EvnDirection_all ed
				where ed.EvnDirection_id = :EvnDirection_id and ed.EvnStatus_id not in (10, 12, 13, 17)
				limit 1
			)
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnDirection($data) {
		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id']
		);
		$query = "
			select 
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.DirType_id as \"DirType_id\",
				ED.EvnStatus_id as \"EvnStatus_id\"
			from 
				v_EvnDirection_all ED
			where 
				ED.EvnDirection_id = :EvnDirection_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение evnlabsample и evnlabrequest по evndirection
	 */
	function getEvnLabSampleAndRequest($data) {
		$res = $this->getFirstRowFromQuery("
			select
				els.EvnLabSample_id as \"EvnLabSample_id\",
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				elr.PersonEvn_id as \"PersonEvn_id\",
				elr.Server_id as \"Server_id\"
			from
				v_EvnDirection_all ed
				inner join v_EvnLabRequest elr on elr.EvnDirection_id = ed.EvnDirection_id
				inner join v_EvnLabSample els on els.EvnLabRequest_id = elr.EvnLabRequest_id
			where
				ed.EvnDirection_id = :EvnDirection_id
			limit 1		
		", $data);

		return $res;
	}

	/**
	 * то же самое, только postgre
	 */
	function updateEvnStatusPostgre($data) {
		$query = "
			select
				error_message as \"Error_Msg\",
				error_code as \"Error_Code\"
			from p_evn_setstatus(
				Evn_id := :Evn_id,
				EvnStatus_id := :EvnStatus_id,
				EvnStatus_SysNick := :EvnStatus_SysNick,
				EvnClass_id := :EvnClass_id,
				EvnClass_SysNick := :EvnClass_SysNick,
				EvnStatusCause_id := :EvnStatusCause_id,
				EvnStatusHistory_Cause := :EvnStatusHistory_Cause,
				MedServiceMedPersonal_id := :MedServiceMedPersonal_id,
				pmUser_id := :pmUser_id
			)	
		";
		$query = $this->queryResult($query, array(
			'Evn_id' => $data['Evn_id'],
			'EvnStatus_id' => !empty($data['EvnStatus_id']) ? $data['EvnStatus_id'] : null,
			'EvnStatus_SysNick' => !empty($data['EvnStatus_SysNick']) ? $data['EvnStatus_SysNick'] : null,
			'EvnClass_id' => !empty($data['EvnClass_id']) ? $data['EvnClass_id'] : null,
			'EvnClass_SysNick' => !empty($data['EvnClass_SysNick']) ? $data['EvnClass_SysNick'] : null,
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnStatusHistory_Cause' => !empty($data['EvnStatusHistory_Cause']) ? $data['EvnStatusHistory_Cause'] : null,
			'MedServiceMedPersonal_id' => !empty($data['MedServiceMedPersonal_id']) ? $data['MedServiceMedPersonal_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		));

		//#44150 : Предшествующий код код не работал при возвращении ошибки!
		if (!$this->isSuccessful($query)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * то же, только postgre
	 */
	function setStatus($params) {
		if (empty($params['EvnStatus_id']) && empty($params['EvnStatus_SysNick'])) {
			throw new Exception('Нужно указать статус', 500);
		}
		if (empty($params['Evn_id'])) {
			$params['Evn_id'] = $this->id;
		}
		if (empty($params['Evn_id'])) {
			throw new Exception('Нужно указать учетный документ', 500);
		}
		if (empty($params['EvnClass_id'])) {
			$params['EvnClass_id'] = $this->evnClassId;
		}
		if (empty($params['EvnClass_id'])) {
			throw new Exception('Нужно указать класс учетного документа', 500);
		}
		if (empty($params['pmUser_id'])) {
			$params['pmUser_id'] = $this->promedUserId;
		}
		if (empty($params['pmUser_id'])) {
			throw new Exception('Нужно указать учетную запись', 500);
		}
		if (empty($params['EvnStatusCause_id'])) {
			$params['EvnStatusCause_id'] = null;
		}
		if (empty($params['EvnStatusHistory_Cause'])) {
			$params['EvnStatusHistory_Cause'] = null;
		} else if (mb_strlen($params['EvnStatusHistory_Cause']) > 200) {
			throw new Exception('Описание не должно быть более 200 символов', 500);
		}
		$tmp = $this->execCommonSPPostgre('p_Evn_setStatus', $params, 'array_assoc');

		if (false == is_array($tmp)) {
			throw new Exception('Не удалось установить статус', 500);
		}
		if (false == empty($tmp['Error_Msg'])) {
			throw new Exception($tmp['Error_Msg'], 500);
		}
	}

	/**
	 *  Получение данных по направлению
	 */
	function loadEvnDirectionEditForm($data) {

		$params = array();
		$filter = "(1=1) ";
		$join = "";
		if ($data['EvnDirection_id'] > 0)
		{
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= "and ED.EvnDirection_id = :EvnDirection_id ";
		}


		if (count($params)>0)
		{
			$selectPersonData = "
					to_char(ps.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
					ps.Person_Surname as \"Person_Surname\",
					ps.Person_Firname as \"Person_Firname\",
					ps.Person_Secname as \"Person_Secname\"";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV peh on peh.Person_id = ED.Person_id";
				$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then to_char(PS.Person_Birthday, 'dd.mm.yyyy') end as \"Person_Birthday\",
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as \"Person_Surname\",
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as \"Person_Firname\",
					case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as \"Person_Secname\"";
			}
			$query = "
				select
					ED.EvnDirection_id as \"EvnDirection_id\",
					ED.EvnDirection_IsReceive as \"EvnDirection_IsReceive\",
					ED.EvnDirection_IsAuto as \"EvnDirection_IsAuto\",
					ED.EvnDirection_Num as \"EvnDirection_Num\",
					ED.ConsultingForm_id as \"ConsultingForm_id\",
					ED.Diag_id as \"Diag_id\",
					Diag.Diag_Name as \"Diag_Name\",
					Diag.Diag_Code as \"Diag_Code\",
					ED.PayType_id as \"PayType_id\",
					ED.DirType_id as \"DirType_id\",
					ED.MedicalCareFormType_id as \"MedicalCareFormType_id\",
					ED.StudyTarget_id as \"StudyTarget_id\",
					coalesce(ED.Lpu_did, DLU.Lpu_id) as \"Lpu_did\",
					ED.Org_oid as \"Org_oid\",
					ED.LpuSection_did as \"LpuSection_did\",
					ED.Lpu_sid as \"Lpu_sid\",
					ED.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ED.EvnDirection_Num as \"EvnDirection_Num\",
					to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
					to_char(ED.EvnDirection_desDT, 'dd.mm.yyyy') as \"EvnDirection_desDT\",
					ED.EvnDirection_Descr as \"EvnDirection_Descr\",
					ED.EvnDirection_IsCito as \"EvnDirection_IsCito\",
					ED.MedStaffFact_id as \"MedStaffFact_id\",
					ED.MedStaffFact_id as \"MedStaffFact_sid\",
					ED.MedStaffFact_id as \"From_MedStaffFact_id\",
					ED.MedPersonal_zid as \"MedStaffFact_zid\",
					ED.MedPersonal_id as \"MedPersonal_id\",
					ED.LpuSection_id as \"LpuSection_id\",
					ED.Post_id as \"Post_id\",
					ED.MedPersonal_zid as \"MedPersonal_zid\",
					case when coalesce(ED.EvnDirection_IsNeedOper,1)= 1 then 'false' else 'true' end as \"EvnDirection_IsNeedOper\",
					LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					ED.LpuUnitType_id as \"LpuUnitType_did\",
					ED.EvnDirection_pid as \"EvnDirection_pid\",
					ED.MedService_id as \"MedService_id\",
					ED.Resource_id as \"Resource_id\",
					ED.RemoteConsultCause_id as \"RemoteConsultCause_id\",
					ED.TimetableMedService_id as \"TimetableMedService_id\",
					ED.TimetableResource_id as \"TimetableResource_id\",
					null as \"PrescriptionType_Code\",
					ED.TimetableGraf_id as \"TimetableGraf_id\",
					ED.TimetablePar_id as \"TimetablePar_id\",
					ED.TimetableStac_id as \"TimetableStac_id\",
					ED.ARMType_id as \"ARMType_id\",
					ED.MedSpec_fid as \"MedSpec_fid\",
					ED.FSIDI_id as \"FSIDI_id\",
					dUC.UslugaComplex_id as \"UslugaComplex_did\",
					dUC.UslugaCategory_id as \"UslugaCategory_did\",
					null as \"EvnXml_id\",
					null as \"EvnDirectionOper_IsAgree\",
					ps.Person_id as \"Person_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ps.Server_id as \"Server_id\",
					null as \"ConsultationForm_id\",
					{$selectPersonData}
				from v_EvnDirection_all ED
					left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_did
					left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_LpuUnit DLU on DLU.LpuUnit_id = ED.LpuUnit_did
					left join v_Lpu DL on DL.Lpu_id = ED.Lpu_did
					left join v_PersonState PS on ED.Person_id = PS.Person_id
					left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
					left join Diag on Diag.Diag_id = ED.Diag_id
					left join v_UslugaComplex dUC on dUC.UslugaComplex_id = ED.UslugaComplex_did
					{$join}
				where {$filter}
			";
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}
		else
			return false;
	}

	/**
	 * Получение данных по направлению для печати
	*/
	function getEvnDirectionForPrint($data) {
		$resp = $this->queryResult("
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				dt.DirType_Code \"DirType_Code\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			from
				v_EvnDirection_all ed
				left join v_DirType dt on dt.DirType_id = ed.DirType_id
				left join v_MedService ms on ed.MedService_id = ms.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (!empty($resp[0]['EvnDirection_id'])) {
			$resp[0]['Error_Msg'] = '';
			return $resp[0];
		}

		return array(
			'Error_Msg' => 'Ошибка получения данных по направлению'
		);
	}

	/**
	 * Получение полей направления для печати
	*/
	function getEvnDirectionFields($data)
	{
		$query = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnDirection_setDate as \"EvnDirection_setDate\",
				DirType_id as \"DirType_id\",
				EvnDirection_Descr as \"EvnDirection_Descr\",
				date_part('day', EvnDirection_setDate) as \"Dir_Day\",
				date_part('month', EvnDirection_setDate) as \"Dir_Month\",
				date_part('year', EvnDirection_setDate) as \"Dir_Year\",
				EvnDirection_setDate as \"EvnDirection_setDate\",
				TimetableGraf_id as \"TimetableGraf_id\",
				TimetableStac_id as \"TimetableStac_id\",
				TimetablePar_id as \"TimetablePar_id\",
				TimetableMedService_id as \"TimetableMedService_id\",
				Lpu_id as \"Lpu_id\",
				LpuSection_id as \"LpuSection_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_oid as \"Org_oid\",
				LpuUnit_did as \"LpuUnit_did\",
				Medpersonal_id as \"Medpersonal_id\",
				MedPersonal_did as \"MedPersonal_did\",
				Medpersonal_zid as \"Medpersonal_zid\",
				MedService_id as \"MedService_id\",
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				Diag_id as \"Diag_id\",
				EvnStatus_id as \"EvnStatus_id\",
				medstafffact_id as \"MedStaffFact_id\",
				Post_id as \"Post_id\",
				MedicalCareFormType_id as \"MedicalCareFormType_id\"
			from v_EvnDirection_all
			where EvnDirection_id = :EvnDirection_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 *  Проверка наличия направления в ту же службу
	 */
	function checkEvnDirectionExists($data) {
		$UslugaList = array();
		// пробуем получить биоматериал по заказываемому исследоваию
		$RefMaterials = $this->queryResult("
			select distinct
				rs.RefMaterial_id as \"RefMaterial_id\"
			from
				v_UslugaComplexMedService ucms
				left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
				inner join v_RefSample rs on rs.RefSample_id = coalesce(ucms_child.RefSample_id, ucms.RefSample_id)
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				and ucms.MedService_id = :MedService_id
				and ucms.UslugaComplexMedService_pid is null
		", array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		));

		if (!empty($RefMaterials)) {
			$RefMats = "";
			foreach($RefMaterials as $RefMaterial) {
				if (!empty($RefMats)) {
					$RefMats .= ",";
				}
				$RefMats .= "'{$RefMaterial['RefMaterial_id']}'";
			}
			$filter = "
				and exists( -- биоматериал в услуге заявки такой же как в заказываемой услуге.
					select
						rs.RefMaterial_id
					from
						v_UslugaComplexMedService ucms
						left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
						inner join v_RefSample rs on rs.RefSample_id = coalesce(ucms_child.RefSample_id, ucms.RefSample_id)
					where
						ucms.UslugaComplex_id = eup.UslugaComplex_id
						and ucms.MedService_id = ed.MedService_id
						and ucms.UslugaComplexMedService_pid is null
						and rs.RefMaterial_id IN ({$RefMats})
					limit 1
				)
			";
		} else {
			$filter = "
				and not exists( -- биоматериал в услуге заявки отсутсвует
					select
						rs.RefMaterial_id
					from
						v_UslugaComplexMedService ucms
						left join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_pid  = ucms.UslugaComplexMedService_id
						inner join v_RefSample rs on rs.RefSample_id = coalesce(ucms_child.RefSample_id, ucms.RefSample_id)
					where
						ucms.UslugaComplex_id = eup.UslugaComplex_id
						and ucms.MedService_id = ed.MedService_id
						and ucms.UslugaComplexMedService_pid is null
						and rs.RefMaterial_id is not null
					limit 1
				)
			";
		}

		$query = "
			select distinct
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ed.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ms.MedService_Nick as \"MedService_Nick\"
			from
				v_EvnDirection_all ed
				inner join v_EvnStatus es on es.EvnStatus_id = ed.EvnStatus_id
				inner join lateral(
					Select
						eup.UslugaComplex_id
					from v_EvnUslugaPar eup
					where eup.EvnDirection_id = ed.EvnDirection_id
					limit 1 -- услуга по заявке
				) eup on true
				inner join v_MedService ms on ms.MedService_id = ed.MedService_id
			where
				ed.Person_id = :Person_id -- тот же пациент
				and ed.EvnDirection_pid = :EvnDirection_pid
				and ed.MedService_id = :MedService_id -- обслуживается той же службой
				and cast(ed.EvnDirection_insDT as date) = cast(dbo.tzGetDate() as date) -- добавлялось в тот же день
				and es.EvnStatus_SysNick in ('Queued', 'DirZap') -- не обслужено
				and eup.UslugaComplex_id != :UslugaComplex_id -- должны быть разные услуги
				{$filter}
		";

		$queryParams = [
			'Person_id' => $data['Person_id'],
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		];

		$resp = $this->queryResult($query, $queryParams);

		if (!empty($resp[0]['EvnDirection_id'])) {
			// Нашли доступное для записи направление
			// Найдем всё, что можно в него записать

			/*if(!empty($data['EvnPrescr_id']) && !empty($data['EvnDirection_pid'])){
				$data['Evn_id'] = $data['EvnDirection_pid'];
				$UslugaList = $this->getUslugaWithoutDirectoryList($data);
			}*/

			return array('Error_Msg' => '', 'EvnDirections' => $resp/*, 'UslugaList' => !empty($UslugaList)?json_encode($UslugaList):null*/); // возвращаем на форму направление в которое можно включить назначение
		}

		return array('Error_Msg' => '');
	}
	/**
	 * Проверка возможности объединения услуг в одно направление
	 */
	function getUslugaWithoutDirectoryList($data) {
		$uslugaFilter = '';
		$MedServiceType = $this->getFirstRowFromQuery("
			SELECT
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			FROM 
				v_MedService ms 
				INNER JOIN v_MedServiceType mst  ON mst.MedServiceType_id = ms.MedServiceType_id
			WHERE
				MedService_id = :MedService_id
		", $data);
		if(empty($MedServiceType)) return false;

		$MedServiceType_SysNick = $MedServiceType['MedServiceType_SysNick'];
		switch ($MedServiceType_SysNick) {
			case 'lab':
				//Для лаборатории нужен список всех оказываемых услуг, для включения в направление таких же из посещения
				$sql = "
					SELECT distinct
						ucms.UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_UslugaComplexMedService ucms  -- услуга на службе
						inner join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaComplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11  on uc11.UslugaComplex_id = uc.UslugaComplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = $usl['UslugaComplex_id'];
					$uslugaFilter = " AND EPLD.UslugaComplex_id IN (".implode(",", $IDs).") ";
				}
				break;
			case 'pzm':
				//Для пункта забора Нужно проверить на способы забора оказываемые пунктом (наличие услуг)
				$sql = "
					SELECT distinct
						UC11.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_UslugaComplexMedService ucms  -- услуга на службе
						inner join v_UslugaComplex uc  on uc.UslugaComplex_id = ucms.UslugaCOmplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11  on uc11.UslugaComplex_id = uc.UslugaCOmplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
						AND uc11.UslugaComplex_Code in ('A11.05.001', 'A11.12.009', 'A11.16.005')
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = "'".$usl['UslugaComplex_Code']."'";
					$uslugaFilter = " AND exists (
						SELECT 
							st.SamplingType_Code
						FROM
							dbo.v_UslugaComplex uc 
							inner join v_UslugaComplex uc11  on uc11.UslugaComplex_id = uc.UslugaComplex_2011id 
							left JOIN UslugaComplexAttribute  ua  ON ua.UslugaComplex_id = uc.UslugaComplex_id
							left JOIN UslugaComplexAttribute  ua2  ON ua2.UslugaComplex_id = uc.UslugaComplex_2011id
							LEFT JOIN SamplingType st  ON (st.SamplingType_id = ua.UslugaComplexAttribute_DBTableID OR st.SamplingType_id = ua2.UslugaComplexAttribute_DBTableID)
						WHERE
							uc.UslugaComplex_id  = EPLD.UslugaComplex_id
							AND (ua.UslugaComplexAttributeType_id = 129 OR ua2.UslugaComplexAttributeType_id = 129)
							AND st.SamplingType_Code IN (".implode(",", $IDs).")
							limit 1
					)";
				}
				break;
			default:
		}

		$params = array(
			'Evn_id' => $data['Evn_id'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		// Запрос на все услуги лабораторной диагн. в данном посещении
		$sql = "
			with cte as (select dbo.tzGetDate() as curdate)
			
			select 
				EPLD.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_pid\",
				CAST(etr.checked as varchar) as checked,
				EP.MedService_id as \"MedService_id\",
				EP.EvnPrescr_id as \"EvnPrescr_id\"
			from v_EvnPrescr EP 
				inner join EvnPrescrLabDiag EPLD  on EPLD.Evn_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left JOIN v_UslugaComplexMedService ucms  on ucms.UslugaComplex_id = EPLD.UslugaComplex_id AND ucms.MedService_id = EP.MedService_id
					 AND ucms.UslugaComplexMedService_pid IS NULL
                     and ucms.UslugaComplexMedService_begDT <= (select curdate from cte)
					and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT > (select curdate from cte))
				left join lateral (
					
						select
							string_agg(coalesce(CAST(UC.UslugaComplex_id as VARCHAR),''), ',') as checked
						from v_UslugaComplexMedService ucmsTemp 
						inner join v_UslugaComplex UC  on ucmsTemp.UslugaComplex_id = UC.UslugaComplex_id
						inner join lateral (
							select
								at_child.AnalyzerTest_SortCode,
								at_child.AnalyzerTest_id,
								coalesce(at_child.AnalyzerTest_SysNick, uc.UslugaComplex_Name) as AnalyzerTest_SysNick
							from
								lis.v_AnalyzerTest at_child 
								inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
								inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
								left join v_UslugaComplex uc on uc.UslugaComplex_id = at_child.UslugaComplex_id
							where
								at_child.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_id
								and at.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_pid
								and coalesce(at_child.AnalyzerTest_IsNotActive, 1) = 1
								and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
								and coalesce(a.Analyzer_IsNotActive, 1) = 1
								and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
								and (uc.UslugaComplex_endDT >= dbo.tzGetDate() or uc.UslugaComplex_endDT is null)
							limit 1
						) ATEST on true -- фильтрация услуг по активности тестов связанных с ними
						where ucmsTemp.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
						group by atest.analyzertest_sortcode
						order by coalesce(ATEST.AnalyzerTest_SortCode, 999999999)
				) etr on true
			where
				EP.EvnPrescr_pid  = :Evn_id
				and EP.EvnPrescr_id != :EvnPrescr_id
				and EP.PrescriptionType_id = 11
				and EP.PrescriptionStatusType_id != 3
				and not exists (
					Select epd.EvnDirection_id
					from EvnPrescrDirection epd 
					--inner join v_EvnDirection_all ED  on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					--and  coalesce(ED.EvnStatus_id, 16) not in (12,13)
					limit 1
				)
				{$uslugaFilter}
		";
		//echo getDebugSQL($sql, $params);die();
		$res = $this->queryResult($sql, $params);

		if (empty($res[0])){
			return false;
		}
		return $res;
	}
	/**
	 * Получение идентификатора мед. службы
	*/
	function getMedServiceFromDirection($data) {
		return $this->getFirstRowFromQuery("
			select
				EvnDirection_id as \"EvnDirection_id\",
				MedService_id as \"MedService_id\"
			from v_EvnDirection_all
			where EvnDirection_id = :EvnDirection_id
		", $data);
	}

	/**
	 * Получение количества направлений
	 */
	function getEvnDirectionCount($data) {
		$filters = array('1=1');

		$params = array(
			'EvnDirection_pid' => $data['EvnDirection_pid']
		);

		if (!empty($data['status']) && $data['status'] == 'active') {
			$filters[] = "EvnDirection_failDT is null";
			$filters[] = "DirFailType_id is null";
			$filters[] = "(EvnStatus_id is null or EvnStatus_id not in (12,13))";
		}

		if (count($filters) > 0) {
			$filters = "and ".implode("\nand ", $filters);
		}

		$query = "
			select count(*) as cnt
			from v_EvnDirection_all
			where EvnDirection_pid = :EvnDirection_pid
			{$filters}
		";
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			return false;
		}

		return array(array(
			'success' => true,
			'Count' => $cnt
		));
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnDirectionNodeList($data) {
		$filter = "(1=1) ";
		$params = array();

		// только не обслуженные, т.е. те направления, назначения по которым не выполнены
		// @todo уточнить определение не обслуженных направлений
		$filterWithoutService = '
		(
			(EvnDirection.EvnStatus_id is null or EvnDirection.EvnStatus_id <> 15)
			/*OR exists (
				select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd
				inner join v_EvnPrescr EP on epd.EvnPrescr_id = EP.EvnPrescr_id
				where EvnDirection.EvnDirection_id = epd.EvnDirection_id
				and (EP.EvnPrescr_IsExec, 1) = 1
			)*/
		)';
		// только не обслуженные электронные направления без признака "создано автоматически".
		$filterOnlyEl = '(coalesce(EvnDirection.EvnDirection_IsAuto, 1) = 1 and '.$filterWithoutService.')';
		// необходимо отображение как направлений, привязанных к случаю - не отмененные, системные и электронные
		// так и непривязанных - только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
		$filterWithParentOrWithout = '(EvnDirection.EvnDirection_pid is not null OR '.$filterOnlyEl.')';
		// основной фильтр
		switch (true) {
			//привязанные к случаю направления - не отмененные, системные и электронные
			case ((isset($data['EvnSection_id'])) && ($data['EvnSection_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnSection_id'];
				break;
			case ((isset($data['EvnVizitPL_id'])) && ($data['EvnVizitPL_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitPL_id'];
				break;
			case ((isset($data['EvnVizitDispDop_id'])) && ($data['EvnVizitDispDop_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitDispDop_id'];
				break;
			case ((isset($data['EvnVizitPLStom_id'])) && ($data['EvnVizitPLStom_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnDirection_pid';
				$params['EvnDirection_pid'] = $data['EvnVizitPLStom_id'];
				break;
			case ((isset($data['EvnPS_id'])) && ($data['EvnPS_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_pid=:EvnPS_id';
				$params['EvnPS_id'] = $data['EvnPS_id'];
				break;
			case ((isset($data['EvnPL_id'])) && ($data['EvnPL_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPL_id'];
				break;
			case ((isset($data['EvnPLStom_id'])) && ($data['EvnPLStom_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLStom_id'];
				break;
			case ((isset($data['EvnPLDispMigrant_id'])) && ($data['EvnPLDispMigrant_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLDispMigrant_id'];
				break;
			case ((isset($data['EvnPLDispDriver_id'])) && ($data['EvnPLDispDriver_id']>0)):
				$filter .= ' and EvnDirection.EvnDirection_rid=:EvnDirection_rid';
				$params['EvnDirection_rid'] = $data['EvnPLDispDriver_id'];
				break;

			case (!empty($data['Person_id']) && $data['Person_id']>0 && !empty($data['DirType_id']) && $data['DirType_id']>0 && !empty($data['type']) && 1==$data['type']):
				// с фильтром по типу направления при отображении дерева в ЭМК в виде "по событиям"
				$filter .= ' and EvnDirection.Person_id = :Person_id and EvnDirection.DirType_id=:DirType_id and '. $filterWithParentOrWithout;
				$params['DirType_id'] = $data['DirType_id'];
				$params['Person_id'] = $data['Person_id'];
				break;

			case (!empty($data['Person_id']) && $data['Person_id']>0 && !empty($data['type']) && 1==$data['type']):
				// При отображении дерева в ЭМК в виде "по событиям" только с фильтром по человеку
				$filter .= ' and EvnDirection.Person_id = :Person_id and '.$filterWithParentOrWithout;
				$params['Person_id'] = $data['Person_id'];
				break;

			default:
				// только с фильтром по человеку
				if (empty($data['Person_id']))
				{
					return array();
				}
				// только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
				$filter .= ' and EvnDirection.Person_id = :Person_id and EvnDirection.EvnDirection_pid is null and '. $filterOnlyEl;
				$params['Person_id'] = $data['Person_id'];
				break;
		}

		// В дереве в принципе не отображать отмененные/отклоненные, не надо перегружать дерево
		$filter .= " and EvnDirection.EvnDirection_failDT is null
		and EvnDirection.DirFailType_id is null
		and (EvnDirection.EvnStatus_id is null or EvnDirection.EvnStatus_id not in (12,13))
		";

		$filter .= " and MST.MedServiceType_SysNick in ('lab','pzm')";

		/*$params['Lpu_id'] = $data['session']['lpu_id'];
		$filter .= "
		and ( Lpu.Lpu_id = :Lpu_id or
			not EXISTS(Select top 1  1 from dbo.VIPPerson vPer with (nolock) where vPer.Person_id = EvnDirection.Person_id and Lpu.Lpu_id =  vPer.lpu_id and vPer.VIPPerson_disDate is null)
		)
		";*/

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and $diagFilter";
			}
		}
		$sql = "
			Select 
				EvnDirection.Lpu_id as \"Lpu_id\",
				EvnDirection.Diag_id as \"Diag_id\",
				EvnDirection.Person_id as \"Person_id\",
				EvnDirection.EvnDirection_pid as \"EvnDirection_pid\",
				EvnDirection.EvnDirection_id as \"EvnDirection_id\",
				EvnDirection.EvnDirection_Num as \"EvnDirection_Num\", 
				DirType.DirType_id as \"DirType_id\",
				Rtrim(DirType.DirType_Name) as \"DirType_Name\",
				coalesce(to_char(EvnDirection.EvnDirection_setDate,'DD.MM.YYYY'),'') as \"EvnDirection_setDT\", 
				RTrim(Lpu.Lpu_Nick) as \"Lpu_Nick\",
				LpuUnit.LpuUnit_id as \"LpuUnit_id\",
				Rtrim(LpuUnit.LpuUnit_Name) as \"LpuUnit_Name\",
				LpuSectionProfile.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				RTrim(LpuSectionProfile.LpuSectionProfile_Code) ||'.'|| RTrim(LpuSectionProfile.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
				case 
					when ttms.TimetableMedService_id is not null
						then to_char(ttms.TimetableMedService_begTime, 'DD.MM.YYYY HH24:MI')
					when EvnDirection.EvnStatus_id = 10
						then case when EUP.EvnUslugaPar_setDT is not null 
							then to_char(EUP.EvnUslugaPar_setDT, 'DD.MM.YYYY HH24:MI') 
							else 'В очереди с ' || coalesce(to_char(EvnDirection.EvnDirection_setDT, 'DD.MM.YYYY'),'') 
						end
					when TTMS.TimetableMedService_id is null
						then 'Направление выписано ' || to_char(EvnDirection.EvnDirection_setDT, 'DD.MM.YYYY')
					else ''
				end as \"RecDate\",
				to_char(EvnDirection.EvnDirection_statusDate, 'DD.MM.YYYY') as \"EvnDirection_statusDate\",
				EvnDirection.EvnStatus_id as \"EvnStatus_id\",
				EvnStatus.EvnStatus_Name as \"EvnStatus_Name\"
			from v_EvnDirection_all EvnDirection
			left join v_Diag Diag on Diag.Diag_id = EvnDirection.Diag_id
			left join v_Lpu Lpu on Lpu.Lpu_id=EvnDirection.Lpu_did
			left join v_LpuUnit LpuUnit on LpuUnit.LpuUnit_id=EvnDirection.LpuUnit_did
			left join v_DirType DirType on DirType.DirType_id=EvnDirection.DirType_id
			left join v_LpuSectionProfile LpuSectionProfile on LpuSectionProfile.LpuSectionProfile_id=EvnDirection.LpuSectionProfile_id
			left join v_MedService MS on MS.MedService_id = EvnDirection.MedService_id
			left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			 -- службы и параклиника
			left join lateral (
				Select TimetableMedService_id, TimetableMedService_begTime 
				from v_TimetableMedService_lite TTMS 
				where TTMS.EvnDirection_id = EvnDirection.EvnDirection_id 
				limit 1
			) TTMS on true
			-- заказанная услуга для параклиники
			left join lateral (
				select EvnUslugaPar_setDT
				from v_EvnUslugaPar EUP
				where EvnDirection_id = EvnDirection.EvnDirection_id
				limit 1
			) EUP on true
			left join v_EvnStatus EvnStatus on EvnStatus.EvnStatus_id = EvnDirection.EvnStatus_id
			where {$filter}
			--order by EvnDirection.EvnDirection_setDate DESC
		";

		return $this->queryResult($sql, $params);
	}

	/**
	 * Данные услуг по направлению для api метода rish api/EvnDirection
	*/
	function getUslugasDataForAPI($data) {
		$resp = $this->queryResult("
			SELECT distinct
				EU.UslugaComplex_id as \"UslugaComplex_id\",
				EU.EvnUsluga_Result as \"EvnUsluga_Result\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			FROM v_EvnUsluga EU
				left join v_EvnUslugaPar EUP on EUP.UslugaComplex_id = EU.UslugaComplex_id and EUP.EvnDirection_id = EU.EvnDirection_id
			WHERE EU.EvnDirection_id = :EvnDirection_id
		", $data);

		if (empty($resp)) {
			$resp = [];
		}

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnDirectionPersonHistory($data) {
		$accessType = 'epl.Lpu_id = :Lpu_id  AND coalesce(epl.EvnDirection_IsSigned,1) != 2';
		//врач может редактировать, удалить, если он работает в поликлинике
		if ( isset($data['userLpuUnitType_SysNick']) && in_array($data['userLpuUnitType_SysNick'], array('polka','stac','dstac','hstac','pstac','parka')) ) {
			$accessType .= " and :userLpuUnitType_SysNick = 'polka'";
		}

		$filter = " (1=1) ";
		$select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter .= " and epl.Person_id in ({$data['person_in']}) ";
			$select = " ,epl.Person_id as \"Person_id\"";
		} else {
			$filter .= " and epl.Person_id = :Person_id ";
		}

		if (empty($data['useArchive'])) {
			// только актуальные
			$filter .= " and coalesce(epl.EvnDirection_IsArchive, 1) = 1";
		} elseif (!empty($data['useArchive']) && $data['useArchive'] == 1) {
			// только архивные
			$filter .= " and coalesce(epl.EvnDirection_IsArchive, 1) = 2";
		} else {
			// все из архивной
			$filter .= "";
		}

		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}

		// только не обслуженные, т.е. те направления, назначения по которым не выполнены
		// @todo уточнить определение не обслуженных направлений
		$filterWithoutService = '
		(
			coalesce(epl.EvnStatus_id,0) not in (15) 
			/*OR exists (
				select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd
				inner join v_EvnPrescr EP on epd.EvnPrescr_id = EP.EvnPrescr_id
				where epl.EvnDirection_id = epd.EvnDirection_id
				and coalesce(EP.EvnPrescr_IsExec, 1) = 1
			)*/
		)';
		// только не обслуженные электронные направления без признака "создано автоматически".
		$filterOnlyEl = '(coalesce(epl.EvnDirection_IsAuto, 1) = 1 and ' . $filterWithoutService . ')';
		// необходимо отображение как направлений, привязанных к случаю - не отмененные, системные и электронные
		// так и непривязанных - только не обслуженные и не отмененные электронные направления без признака "создано автоматически".
		$filterWithParentOrWithout = '(epl.EvnDirection_pid is not null OR ' . $filterOnlyEl . ')';

		if (!empty($data['Person_id']) && $data['Person_id'] > 0 && !empty($data['DirType_id']) && $data['DirType_id'] > 0 && !empty($data['type']) && 1 == $data['type']) {
			// с фильтром по типу направления при отображении дерева в ЭМК в виде "по событиям"
			$filter .= ' and  epl.DirType_id=:DirType_id and ' . $filterWithParentOrWithout;
			$params['DirType_id'] = $data['DirType_id'];
		}

		if (!empty($data['Person_id']) && $data['Person_id'] > 0 && !empty($data['type']) && 1 == $data['type']) {
			// При отображении дерева в ЭМК в виде "по событиям" только с фильтром по человеку
			$filter .= ' and ' . $filterWithParentOrWithout;
		}

		// В дереве в принципе не отображать отмененные/отклоненные, не надо перегружать дерево
		$filter .= " and epl.EvnDirection_failDT is null
		and epl.DirFailType_id is null
		and coalesce(epl.EvnStatus_id,0) not in (12,13)
		";

		$sql = "
			select
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				to_char(epl.EvnDirection_setDate, 'DD.MM.YYYY') as \"objectSetDate\",
				epl.EvnDirection_setTime as \"objectSetTime\",
				to_char(epl.EvnDirection_disDate, 'DD.MM.YYYY') as \"objectDisDate\",
				ec.EvnClass_SysNick as \"object\",
				epl.EvnClass_id as \"EvnClass_id\",
				ec.EvnClass_Name as \"EvnClass_Name\",
				DirType.DirType_id as \"DirType_id\",
				LOWER(RTRIM(DirType.DirType_Name)) as \"DirType_Name\",
				epl.EvnDirection_id as \"Evn_id\",
				epl.EvnDirection_id as \"object_id\",
				null as \"IsFinish\",
				o.Org_Nick as \"Lpu_Nick\",
				l.Lpu_id as \"Lpu_id\",
				null as \"Diag_Code\",
				null as \"Diag_Name\",
				ec.EvnClass_Name as \"EmkTitle\",
				'direction' as \"EvnType\",
				null as \"hide\",
				2 as \"isDoc\",
				epl.EvnDirection_Num AS \"number\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				{$select}
			from
				v_EvnDirection_all epl
				inner join Lpu l on l.Lpu_id = epl.Lpu_id
				inner join Org o on o.Org_id = l.Org_id
				inner join EvnClass ec on ec.EvnClass_id = epl.EvnClass_id
				left join lateral (select Diag_Code from v_Diag d where d.Diag_id = epl.Diag_id limit 1) d on true
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = epl.LpuSectionProfile_id
				left join v_DirType DirType on DirType.DirType_id=epl.DirType_id
				left join v_MedService ms on ms.MedService_id = epl.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				{$filter}
				and mst.MedServiceType_SysNick in ('lab','pzm')
			order by 
				coalesce(epl.EvnDirection_disDate,epl.EvnDirection_setDate) desc
		";

		return $this->queryResult($sql, $data);
	}

	/**
	 * Направления для api метода rish api/EvnDirection
	*/
	function getEvnDirectionsForAPI($data) {

		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and ed.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['Evn_pid'])) {
			$filter .= " and ed.EvnDirection_pid = :Evn_pid";
			$queryParams['Evn_pid'] = $data['Evn_pid'];
		}

		if (!empty($data['EvnDirection_id'])) {
			$filter .= " and ed.EvnDirection_id = :EvnDirection_id";
			$queryParams['EvnDirection_id'] = $data['EvnDirection_id'];
		}

		if (!empty($data['EvnDirection_Num'])) {
			$filter .= " and ed.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		if (!empty($data['EvnDirection_setDate'])) {
			$filter .= " and ed.EvnDirection_setDate = :EvnDirection_setDate";
			$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and ed.Lpu_did = :Lpu_did";
			$queryParams['Lpu_did'] = $data['Lpu_id'];
		}

		if (!empty($data['DirType_id'])) {
			$filter .= " and ed.DirType_id = :DirType_id";
			$queryParams['DirType_id'] = $data['DirType_id'];
		}

		if ((!empty($data['EvnDirection_beg']) || !empty($data['EvnDirection_end'])) && $data['DirType_id'] != 10) {
			return array('error_msg' => 'Запрос направлений за период доступен только для направлений с типом «На исследование». Укажите корректный тип направления или удалите период.');
		}

		if ((!empty($data['EvnDirection_beg']) || !empty($data['EvnDirection_end'])) && $data['DirType_id'] == 10) {
			$period = 'between :EvnDirection_beg and :EvnDirection_end';
			if(empty($data['EvnDirection_beg'])) $period = '<= :EvnDirection_end';
			if(empty($data['EvnDirection_end'])) $period = '>= :EvnDirection_beg';

			$filter .= " and EXISTS (
					SELECT EvnUsluga_id
					FROM v_EvnUsluga EU
					WHERE EU.EvnDirection_id = ed.EvnDirection_id
					AND EU.EvnUsluga_updDT {$period}
					limit 1
				)
			";

			$queryParams['EvnDirection_beg'] = $data['EvnDirection_beg'];
			$queryParams['EvnDirection_end'] = $data['EvnDirection_end'];
		}

		$query = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				EvnDirection_id as \"Evn_id\",
				EvnDirection_pid as \"Evn_pid\",
				Person_id as \"Person_id\",
				EvnDirection_Num as \"EvnDirection_Num\",
				PayType_id as \"PayType_id\",
				DirType_id as \"DirType_id\",
				to_char(EvnDirection_setDate, 'yyyy-mm-dd') as \"EvnDirection_setDate\",
				to_char(EvnDirection_insDT, 'yyyy-mm-dd') as \"EvnDirection_insDT\",
				Diag_id as \"Diag_id\",
				EvnDirection_Descr as \"EvnDirection_Descr\",
				Lpu_sid as \"Lpu_sid\",
				LpuSection_id as \"LpuSection_id\",
				MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedPersonal_zid as \"MedPersonal_zid\",
				Lpu_did as \"Lpu_did\",
				LpuUnit_did as \"LpuUnit_did\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MedPersonal_did as \"MedPersonal_did\",
				TimeTableStac_id as \"TimeTableStac_id\",
				DirFailType_id as \"DirFailType_id\",
				to_char(EvnDirection_failDT, 'yyyy-mm-dd') as \"EvnDirection_failDT\",
				pmUser_failID as \"pmUser_failID\",
				TimeTableGraf_id as \"TimeTableGraf_id\",
				TimeTableStac_id as \"TimeTableStac_id\",
				TimeTableMedService_id as \"TimeTableMedService_id\",
				TimeTableResource_id as \"TimeTableResource_id\",
				EvnQueue_id as \"EvnQueue_id\",
				Resource_id as \"Resource_id\"
			from v_evndirection_all ed
			where (1=1)
				{$filter}
			limit 10001
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получить коды бронирования для ЭО
	 */
	function getEvnDirectionTalonCode($data) {

		$params['Lpu_id'] = $data['Lpu_did'];

		if (!empty($data['EvnDirection_TalonCode'])) {

			$params['EvnDirection_TalonCode'] = $data['EvnDirection_TalonCode'];
			$filter = " and ed.EvnDirection_TalonCode = :EvnDirection_TalonCode ";

		} else if (!empty($data['Person_id'])) {
			$params['Person_id'] = $data['Person_id'];
			$filter = "
                and ed.Person_id = :Person_id
                and ed.EvnDirection_TalonCode is not null
            ";
		} else return array(
			'Error_Msg' => 'Не указаны параметры для поиска талона'
		);

		$query = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				EvnDirection_TalonCode as \"EvnDirection_TalonCode\",
				TimetableMedService_id as \"TimetableMedService_id\",
				Person_id as \"Person_id\"
			from v_evndirection_all ed
			where (1=1)
				and Lpu_did = :Lpu_id
				and cast(EvnDirection_setDT as date) > cast((dbo.tzGetDate()-interval '30 days') as date)
				{$filter}
			
			union 
			
			select
				EvnDirection_id as \"EvnDirection_id\",
				EvnDirection_TalonCode as \"EvnDirection_TalonCode\",
				TimetableMedService_id as \"TimetableMedService_id\",
				Person_id as \"Person_id\"
			from v_evndirection_all ed
			where (1=1)
				and Lpu_sid = :Lpu_id
				and cast(EvnDirection_setDT as date) > cast((dbo.tzGetDate()-interval '30 days') as date)
				{$filter}
				
			order by \"EvnDirection_id\" desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получить информацию по направлению для связанного талона ЭО
	 */
	function getElectronicTalonDirectionData($data){

		$query = "
			select
				EvnDirection_id as \"EvnDirection_id\",
				DirType_id as \"DirType_id\"
			from v_evndirection_all
			where EvnDirection_id = :EvnDirection_id
		";

		return $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
	}

	/**
	 * Получить талоны ЭО для списка направлений
	 */
	function getTalonCodeByEvnDirectionList($data){

		$valid_data = true;
		$result = array();

		// защита от дурака
		$dir_array = explode(',',$data['list']);
		foreach ($dir_array as $EvnDirection_id) {
			if (!is_numeric($EvnDirection_id)) {
				$valid_data = false;
				break;
			}
		}

		if ($valid_data && !empty($data['list'])) {
			$result = $this->queryResult("
				select
					TimetableMedService_id as \"TimetableMedService_id\",
					EvnDirection_id as \"EvnDirection_id\",
					EvnDirection_TalonCode as \"EvnDirection_TalonCode\"
				from v_evndirection_all
				where (1=1) 
					and EvnDirection_id in ({$data['list']})
					and EvnDirection_TalonCode is not null
			", array());
		}

		return $result;
	}

	/**
	 *  Получение списка направлений для панели направлений в ЭМК
	 */
	function loadEvnDirectionPanel($data)
	{
		$filter = ' and dt.DirType_Code = 9 and ms.MedServiceType_id != 8';
		if (!empty($data['DopDispInfoConsent_id'])) {
			$filter .= ' and ed.DopDispInfoConsent_id = :DopDispInfoConsent_id ';
		}

		$resp = $this->queryResult("
			with edd as (
				select * from v_Evn where Evn_pid = :EvnDirection_pid and EvnClass_id = 27
			) 
			select
				'EvnDirectionLabDiag' as \"object\",
				null as  \"EvnDirectionHistologic_id\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_IsSigned as \"IsSigned\",
				'EvnDirection' as \"EMDRegistry_ObjectName\",
				ED.EvnDirection_id as \"EMDRegistry_ObjectID\",
				dt.DirType_Name as \"DirType_Name\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				l.Lpu_Name as \"Lpu_Name\",
				o.Org_Name as \"Org_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				o.Org_Nick as \"Org_Nick\",
				to_char(coalesce(ttms.TimetableMedService_begTime, ed.EvnDirection_setDate), 'DD.MM.YYYY') as \"EvnDirection_setDate\",
				to_char(coalesce(cast(coalesce(ttms.TimetableMedService_begTime) as time), ed.EvnDirection_setTime), 'HH24:MI') as \"EvnDirection_setTime\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				dt.DirType_Code as \"DirType_Code\",
				null as \"TimetableGraf_id\",
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				null as \"TimetableResource_id\",
				null as \"TimetableStac_id\",
				ED.EvnStatus_id as \"EvnStatus_id\",
				ES.EvnStatus_Name as \"EvnStatus_Name\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick,\",
				to_char(ED.EvnDirection_statusDate, 'DD.MM.YYYY') as \"EvnDirection_statusDate\",
				ESC.EvnStatusCause_Name as \"EvnStatusCause_Name\",
				null as \"EvnPrescrMse_id\",
				null as \"EvnDirectionHTM_id\",
				null as \"Lpu_gid\",
				null as \"EvnPrescrVK_id\",
				null as \"EvnStatus_epvkName\",
				null as \"EvnStatus_epvkSysNick\"
			from
				v_EvnDirection_all ed
				left join v_MedService ms on ms.MedService_id = ed.MedService_id
				left join v_DirType dt on dt.DirType_id = ed.DirType_id
				left join v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_did
				left join v_Lpu l on l.Lpu_id = ed.Lpu_did
				left join v_Org o on o.Org_id = ed.Org_oid
				left join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = ed.EvnDirection_id
				left join lateral (
					select
						ESH.EvnStatusCause_id
					from
						v_EvnStatusHistory ESH
					where
						ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by
						ESH.EvnStatusHistory_begDate desc
					limit 1
				) ESH on true
				left join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_EvnStatusCause ESC on ESC.EvnStatusCause_id = ESH.EvnStatusCause_id
			where
				ed.EvnDirection_pid = :EvnDirection_pid
				{$filter}
		", $data);

		$EvnDirectionIds = [];
		foreach($resp as $one) {
			if ($one['EMDRegistry_ObjectName'] == 'EvnDirection' && !empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && !in_array($one['EvnDirection_id'], $EvnDirectionIds)) {
				$EvnDirectionIds[] = $one['EvnDirection_id'];
			}
		}

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($EvnDirectionIds) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$signStatus = $this->EMD_model->getSignStatus([
				'EMDRegistry_ObjectName' => 'EvnDirection',
				'EMDRegistry_ObjectIDs' => $EvnDirectionIds,
				'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'] ?? null
			]);

			foreach($resp as $key => $one) {
				$resp[$key]['EvnDirection_SignCount'] = 0;
				$resp[$key]['EvnDirection_MinSignCount'] = 0;
				if (!empty($one['EvnDirection_id']) && $one['IsSigned'] == 2 && isset($signStatus[$one['EvnDirection_id']])) {
					$resp[$key]['EvnDirection_SignCount'] = $signStatus[$one['EvnDirection_id']]['signcount'];
					$resp[$key]['EvnDirection_MinSignCount'] = $signStatus[$one['EvnDirection_id']]['minsigncount'];
					$resp[$key]['IsSigned'] = $signStatus[$one['EvnDirection_id']]['signed'];
				}
			}
		}

		return $resp;
	}

    /**
     * Включение назначения в существующее направление
     */
    function includeEvnPrescrInDirection($data) {
        //$this->beginTransaction();
        // добавляем связь в EvnPrescrDirection
        $this->load->swapi('lis');
        $resp = $this->common->POST('EvnPrescr/checkAndDirectEvnPrescr', $data, 'single');
        if (!$this->isSuccessful($resp)) {
            return $resp;
        }
        // нам понадобятся EvnLabRequest_id, MedService_id, PersonEvn_id, Server_id
        $resp = $this->lis->GET('EvnDirection/MedServiceFromEvnDirection', [
            'EvnDirection_id' => $data['EvnDirection_id']
        ], 'single');
        if (!$this->isSuccessful($resp)) {
            throw new Exception('Ошибка получения данных по заявке', 500);
        }

        $res = $this->lis->GET('EvnDirection/EvnLabSampleAndRequest', $resp, 'single');
        if (!$this->isSuccessful($resp)) {
            throw new Exception('Ошибка получения данных по заявке', 500);
        }

        $resp = array_merge($resp, $res);
        if (empty($resp['EvnLabRequest_id'])) {
            $this->rollbackTransaction();
            return array('Error_Msg' => 'Ошибка получения данных по заявке');
        } else {
            $data['EvnLabRequest_id'] = $resp['EvnLabRequest_id'];
            $data['MedService_id'] = $resp['MedService_id'];
            $data['PersonEvn_id'] = $resp['PersonEvn_id'];
            $data['Server_id'] = $resp['Server_id'];
            $data['EvnLabSample_id'] = $resp['EvnLabSample_id'];
        }

        // добавляем исследование в заявку
        if (!empty($data['UslugaComplex_id'])) {

            if (!empty($data['EvnLabSample_id'])) {
                $data['PayType_id'] = $this->getFirstResultFromQuery("
					select
						PayType_id as \"PayType_id\"
					from
						v_PayType
					where
						PayType_SysNick = :PayType_SysNick
					limit 1
				", array('PayType_SysNick' => getPayTypeSysNickOMS()));
                if (empty($data['PayType_id'])) {
                    $data['PayType_id'] = null;
                }

                // исследование
                $params = $data;
                $params['RefSample_id'] = null;
                $checked_tests = $data['checked'];
                $orderparams = json_decode(toUTF($data['order']), true);
                $params['researches'][] = $orderparams['UslugaComplexMedService_id'];

                // сохраняем услуги и тесты, разбиваем на пробы по биоматериалу
                $this->load->model('EvnLabSample_model');
                $this->EvnLabSample_model->saveLabSampleResearches($params, $checked_tests);

            }
        }

        // кэшируем количество тестов
        $res = $this->lis->POST('EvnLabRequest/ReCacheLabRequestUslugaCount', [
            'EvnLabRequest_id' => $data['EvnLabRequest_id'],
            'pmUser_id' => $data['pmUser_id']
        ], 'single');
        if (!$this->isSuccessful($res)) {
            throw new Exception($res['Error_Msg'], 500);
        }

        // кэшируем статус проб в заявке
        $res = $this->lis->POST('EvnLabRequest/ReCacheLabRequestSampleStatusType', [
            'EvnLabRequest_id' => $data['EvnLabRequest_id'],
            'pmUser_id' => $data['pmUser_id']
        ], 'single');
        if (!$this->isSuccessful($res)) {
            throw new Exception($res['Error_Msg'], 500);
        }

        return array('Error_Msg' => '');
    }
}
