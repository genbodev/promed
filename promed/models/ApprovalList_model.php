<?php
/**
 * ApprovalList_model - модель для работы с листами согласования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		ApprovalList
 * @access		public
 * @copyright	Copyright (c) 2010-2019 Swan Ltd.
 * @author		Dmitry Vlasenko
 */
class ApprovalList_model extends swModel {
	private $isEMDEnabled = false;

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->emddb = $this->load->database('emd', true); // своя БД на PostgreSQL
			$this->isEMDEnabled = true;
		}
	}

	/**
	 * Сохранение листа согласования
	 */
	function saveApprovalList($data) {
		if (!$this->isEMDEnabled) {
			return array('Error_Msg' => '');
		}

		if (getRegionNick() != 'msk' && in_array($data['ApprovalList_ObjectName'], ['EvnPS', 'EvnXml', 'EvnDirection'])) {
			// лист согласования не требуется
			return array('Error_Msg' => '');
		}

		$this->load->model('EMD_model');
		$EMDDocumentTypeLocal_id = $this->EMD_model->getEMDDocumentTypeLocal([
			'EMDRegistry_ObjectName' => $data['ApprovalList_ObjectName'],
			'EMDRegistry_ObjectID' => $data['ApprovalList_ObjectId']
		]);

		// Производится поиск объекта по справочнику «Список объектов, для которых требуется лист согласования»
		$resp_aol = $this->queryResult('
			select
				"ApprovalObjectList_id"
			from
				"EMD"."ApprovalObjectList"
			where
				"EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
				and coalesce("Region_id", :Region_id) = :Region_id
				and coalesce("ApprovalObjectList_begDate", :curDate) <= :curDate
				and coalesce("ApprovalObjectList_endDate", :curDate) >= :curDate
		', array(
			'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
			'curDate' => date('Y-m-d'),
			'Region_id' => getRegionNumber()
		), $this->emddb);

		if (empty($resp_aol[0]['ApprovalObjectList_id'])) {
			// лист согласования не требуется
			return array('Error_Msg' => '');
		}

		// Выполняется проверка существования листа согласования для данного объекта по таблице «Лист согласования»
		$resp_al = $this->queryResult('
			select
				"ApprovalList_id"
			from
				"EMD"."ApprovalList"
			where
				"ApprovalList_ObjectName" = :ApprovalList_ObjectName
				and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
		', array(
			'ApprovalList_ObjectName' => $data['ApprovalList_ObjectName'],
			'ApprovalList_ObjectId' => $data['ApprovalList_ObjectId']
		), $this->emddb);

		if (!empty($resp_al[0]['ApprovalList_id'])) {
			if ($data['ApprovalList_ObjectName'] == 'EvnUslugaPar') {
				return array('Error_Msg' => ''); // лист уже создан
			}
			// Если запись найдена, то она удаляется; дополнительно удаляются все связанные записи мед. сотрудников участвующих в подписании;
			$this->deleteApprovalList(array(
				'ApprovalList_ObjectName' => $data['ApprovalList_ObjectName'],
				'ApprovalList_ObjectId' => $data['ApprovalList_ObjectId']
			));
		}

		// Для некоторых событий предусмотрена дополнительная проверка достаточности условий для создания листа согласования:
		switch ($data['ApprovalList_ObjectName']) {
			case 'EvnPrescrMse':
				// Сохранена ссылка на протокол ВК
				// Заполнен список экспертов ВК: у связанного протокола ВК (dbo.EvnVKExpert)
				// Статус направления один из: Новое, Отправлено
				$resp_epm = $this->queryResult("
					select top 1
						EPM.EvnPrescrMse_id,
						convert(varchar(10), EPM.EvnPrescrMse_setDate, 104) as EvnPrescrMse_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin
					from
						v_EvnPrescrMse EPM (nolock)
						inner join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EPM.EvnStatus_id
						inner join v_PersonState ps (nolock) on ps.Person_id = epm.Person_id
					where
						EPM.EvnPrescrMse_id = :EvnPrescrMse_id
						and EPM.EvnVK_id is not null
						and (exists (
							select top 1
								EVKE.EvnVKExpert_id
							from
								v_EvnVKExpert EVKE (nolock)
							where
								EVKE.EvnVK_id = EPM.EvnVK_id
						) or exists (
							select top 1
								vek.VoteExpertVK_id
							from
								v_VoteExpertVK vek (nolock)
								inner join v_VoteListVK VLVK (nolock) on VLVK.VoteListVK_id = vek.VoteListVK_id
								inner join v_EvnVK EVK with(nolock) on EVK.EvnPrescrVK_id = VLVK.EvnPrescrVK_id
							where
								EVK.EvnVK_id = EPM.EvnVK_id
						))
						and ES.EvnStatus_SysNick in ('New', 'Sended')
				", array(
					'EvnPrescrMse_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_epm[0]['EvnPrescrMse_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnDirectionHTM':
				$resp_edhtm = $this->queryResult("
					select top 1
						EDHTM.EvnDirectionHTM_id,
						convert(varchar(10), EDHTM.EvnDirectionHTM_setDate, 104) as EvnDirectionHTM_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin
					from
						v_EvnDirectionHTM EDHTM (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = EDHTM.Person_id
					where
						EDHTM.EvnDirectionHTM_id = :EvnDirectionHTM_id
				", array(
					'EvnDirectionHTM_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_edhtm[0]['EvnDirectionHTM_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnVK':
				$resp_evk = $this->queryResult("
					select top 1
						EVK.EvnVK_id,
						convert(varchar(10), EVK.EvnVK_setDate, 104) as EvnVK_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin
					from
						v_EvnVK EVK (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = EVK.Person_id
					where
						EVK.EvnVK_id = :EvnVK_id
				", array(
					'EvnVK_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_evk[0]['EvnVK_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'BirthSvid':
				// Заполнены поля блок «Получатель»: ФИО, Дата получения свидетельства, Отношение к ребенку
				// Свидетельство не испорчено
				$resp_bs = $this->queryResult("
					select top 1
						BS.BirthSvid_id,
						convert(varchar(10), BS.BirthSvid_GiveDate, 104) as BirthSvid_GiveDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin
					from
						v_BirthSvid BS (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = bs.Person_id
					where
						BS.BirthSvid_id = :BirthSvid_id
						and bs.Person_rid is not null						
						and bs.DeputyKind_id is not null						
						and bs.BirthSvid_RcpDate is not null
						and ISNULL(bs.BirthSvid_IsBad, 1) = 1
				", array(
					'BirthSvid_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_bs[0]['BirthSvid_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'DeathSvid':
				// Заполнены поля блок «Получатель»: ФИО, Дата получения свидетельства
				// Свидетельство не испорчено
				$resp_ds = $this->queryResult("
					select top 1
						DS.DeathSvid_id,
						convert(varchar(10), DS.DeathSvid_GiveDate, 104) as DeathSvid_GiveDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin
					from
						v_DeathSvid DS (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = DS.Person_id
					where
						DS.DeathSvid_id = :DeathSvid_id
						and DS.Person_rid is not null					
						and DS.DeathSvid_RcpDate is not null
						and ISNULL(DS.DeathSvid_IsBad, 1) = 1
				", array(
					'DeathSvid_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_ds[0]['DeathSvid_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnRecept':
				// Форма льготного рецепта =  148-1/у-04(л)
				// Тип рецепта – электронный документ
				// У рецепта заполнено хотя бы одно поле:
				// Специальное назначение
				// Номер протокола ВК
				$resp_er = $this->queryResult("
					select top 1
						ER.EvnRecept_id,
						convert(varchar(10), ER.EvnRecept_setDate, 104) as EvnRecept_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
						ER.EvnRecept_Ser,
						ER.EvnRecept_Num,
						ER.PrescrSpecCause_id,
						ER.EvnRecept_VKProtocolNum,
					    ER.Lpu_id,
						ER.Person_id,
						LS.LpuBuilding_id
					from
						v_EvnRecept ER (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = ER.Person_id
						inner join v_ReceptForm rf (nolock) on rf.ReceptForm_id = er.ReceptForm_id
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = er.LpuSection_id
					where
						ER.EvnRecept_id = :EvnRecept_id
						and RF.ReceptForm_Code = '148'
						and ER.ReceptType_id = 3
						and (
						    ER.PrescrSpecCause_id is not null
						    OR ER.EvnRecept_VKProtocolNum is not null
						)
				", array(
					'EvnRecept_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_er[0]['EvnRecept_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnPS':
				$resp_eps = $this->queryResult("
					select top 1
						EPS.EvnPS_id,
						convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
						eps.Lpu_id,
						es.LpuSection_id
					from
						v_EvnPS EPS (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = EPS.Person_id
						outer apply (
							select top 1
								es.LpuSection_id
							from
								v_EvnSection es (nolock)
								inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = es.MedStaffFact_id
							where
								es.EvnSection_pid = eps.EvnPS_id
						    order by
						    	ES.EvnSection_Index desc
						) es
					where
						EPS.EvnPS_id = :EvnPS_id
				", array(
					'EvnPS_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_eps[0]['EvnPS_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnXml':
				$resp_ex = $this->queryResult("
					select top 1
						EX.EvnXml_id,
						convert(varchar(10), EX.EvnXml_updDT, 104) as EvnXml_updDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
						E.Lpu_id,
					    ISNULL(ES.LpuSection_id, EV.LpuSection_id) as LpuSection_id
					from
						v_EvnXml EX (nolock)
						inner join v_Evn E (nolock) on e.Evn_id = EX.Evn_id
						inner join v_PersonState ps (nolock) on ps.Person_id = E.Person_id
						left join EvnSection ES (nolock) on es.Evn_id = e.Evn_id
						left join EvnVizit EV (nolock) on ev.Evn_id = e.Evn_id
					where
						EX.EvnXml_id = :EvnXml_id
				", array(
					'EvnXml_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_ex[0]['EvnXml_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnDirection':
				$resp_ed = $this->queryResult("
					select top 1
						ED.EvnDirection_id,
						convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
						ed.Lpu_id,
						ed.LpuSection_id
					from
						v_EvnDirection_all ED (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = ED.Person_id
					where
						ED.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_ed[0]['EvnDirection_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnRecept':
				// Форма льготного рецепта =  148-1/у-04(л)
				// Тип рецепта – электронный документ
				// У рецепта заполнено хотя бы одно поле:
				// Специальное назначение
				// Номер протокола ВК
				$resp_er = $this->queryResult("
					select top 1
						ER.EvnRecept_id,
						convert(varchar(10), ER.EvnRecept_setDate, 104) as EvnRecept_setDate,
						RTRIM(ISNULL(ps.Person_Surname, '')) + ' ' + SUBSTRING(ISNULL(ps.Person_Firname, ''),1,1) + ' ' + SUBSTRING(ISNULL(ps.Person_Secname, ''),1,1) as Person_Fin,
						ER.EvnRecept_Ser,
						ER.EvnRecept_Num,
						ER.PrescrSpecCause_id,
						ER.EvnRecept_VKProtocolNum,
					    ER.Lpu_id,
						ER.Person_id,
						LS.LpuBuilding_id
					from
						v_EvnRecept ER (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = ER.Person_id
						inner join v_ReceptForm rf (nolock) on rf.ReceptForm_id = er.ReceptForm_id
						left join v_LpuSection ls (nolock) on ls.LpuSection_id = er.LpuSection_id
					where
						ER.EvnRecept_id = :EvnRecept_id
						and RF.ReceptForm_Code = '148'
						and ER.ReceptType_id = 3
						and (
						    ER.PrescrSpecCause_id is not null
						    OR ER.EvnRecept_VKProtocolNum is not null
						)
				", array(
					'EvnRecept_id' => $data['ApprovalList_ObjectId']
				));

				if (empty($resp_er[0]['EvnRecept_id'])) {
					return array('Error_Msg' => '');
				}
				break;
			case 'EvnUslugaPar':
				if ($this->usePostgreLis) {
					$this->load->swapi('lis');
					$respLis = $this->lis->GET('EvnUsluga/EvnUslugaParInfo', [
						'EvnUslugaPar_id' => $data['ApprovalList_ObjectId']
					]);

					$resp_eup = $respLis['data'] ?? [];
				} else {
					$this->load->model('EvnUsluga_model');
					$resp_eup = $this->EvnUsluga_model->getEvnUslugaParInfo([
						'EvnUslugaPar_id' => $data['ApprovalList_ObjectId']
					]);
				}

				if (empty($resp_eup[0]['EvnUslugaPar_id'])) {
					return array('Error_Msg' => '');
				}
				break;
		}

		$roles = [];
		switch($data['ApprovalList_ObjectName']) {
			case 'EvnVK':
				// 2 варианта заведения экспертов - обычный (EvnVKExpert), необычный (VoteExpertVK)
				$EvnVKExpert_id = $this->getFirstResultFromQuery("select top 1 EvnVKExpert_id from v_EvnVKExpert (nolock) where EvnVK_id = :EvnVK_id", [
					'EvnVK_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($EvnVKExpert_id)) {
					$from = "
						v_EvnVKExpert evke with (nolock)
						inner join v_EvnVK EVK with (nolock) on EVK.EvnVK_id = evke.EvnVK_id
					";
				} else {
					$from = "
						v_VoteExpertVK evke with (nolock)
						inner join v_VoteListVK VLVK with (nolock) on VLVK.VoteListVK_id = evke.VoteListVK_id
						inner join v_EvnVK EVK with (nolock) on EVK.EvnPrescrVK_id = VLVK.EvnPrescrVK_id
					";
				}
				
				$roles = $this->queryResult("
					select
						EVKE.ExpertMedStaffType_id,
						msmp.MedPersonal_id,
						ms.Lpu_id
					from
						{$from}
						inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
						inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
					where
						EVK.EvnVK_id = :EvnVK_id
				", array(
					'EvnVK_id' => $data['ApprovalList_ObjectId']
				));

				foreach($roles as $key => $value) {
					$resp_epr = $this->queryResult('
						select
							"EMDPersonRole_id"
						from
							"EMD"."EMDPersonRoleLink"
						where
							"ExpertMedStaffType_id" = :ExpertMedStaffType_id						
					', array(
						'ExpertMedStaffType_id' => $value['ExpertMedStaffType_id']
					), $this->emddb);

					$EMDPersonRole_id = null;
					if (!empty($resp_epr[0]['EMDPersonRole_id'])) {
						$EMDPersonRole_id = $resp_epr[0]['EMDPersonRole_id'];
					}

					$roles[$key]['EMDPersonRole_id'] = $EMDPersonRole_id;
				}
				break;
			case 'EvnPrescrMse':
				// 2 варианта заведения экспертов - обычный (EvnVKExpert), необычный (VoteExpertVK)
				$EvnVKExpert_id = $this->getFirstResultFromQuery("
					select top 1
						EvnVKExpert_id
					from
						v_EvnVKExpert evke (nolock)
						inner join v_EvnPrescrMse EPM (nolock) on EPM.EvnVK_id = evke.EvnVK_id
					where
						EPM.EvnPrescrMse_id = :EvnPrescrMse_id
				", [
					'EvnPrescrMse_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($EvnVKExpert_id)) {
					$from = "
						v_EvnVKExpert evke with (nolock)
						inner join v_EvnVK EVK with (nolock) on EVK.EvnVK_id = evke.EvnVK_id
					";
				} else {
					$from = "
						v_VoteExpertVK evke with (nolock)
						inner join v_VoteListVK VLVK with (nolock) on VLVK.VoteListVK_id = evke.VoteListVK_id
						inner join v_EvnVK EVK with (nolock) on EVK.EvnPrescrVK_id = VLVK.EvnPrescrVK_id
					";
				}
				
				$roles = $this->queryResult("
					select
						EVKE.ExpertMedStaffType_id,
						msmp.MedPersonal_id,
						ms.Lpu_id
					from
						{$from}
						inner join v_EvnPrescrMse EPM (nolock) on EPM.EvnVK_id = EVK.EvnVK_id
						inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
						inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
					where
						EPM.EvnPrescrMse_id = :EvnPrescrMse_id
				", array(
					'EvnPrescrMse_id' => $data['ApprovalList_ObjectId']
				));

				foreach($roles as $key => $value) {
					$resp_epr = $this->queryResult('
						select
							"EMDPersonRole_id"
						from
							"EMD"."EMDPersonRoleLink"
						where
							"ExpertMedStaffType_id" = :ExpertMedStaffType_id						
					', array(
						'ExpertMedStaffType_id' => $value['ExpertMedStaffType_id']
					), $this->emddb);

					$EMDPersonRole_id = null;
					if (!empty($resp_epr[0]['EMDPersonRole_id'])) {
						$EMDPersonRole_id = $resp_epr[0]['EMDPersonRole_id'];
					}

					$roles[$key]['EMDPersonRole_id'] = $EMDPersonRole_id;
				}
				break;
			case 'EvnDirectionHTM':
				// 2 варианта заведения экспертов - обычный (EvnVKExpert), необычный (VoteExpertVK)
				$EvnVKExpert_id = $this->getFirstResultFromQuery("
					select top 1
						EvnVKExpert_id
					from
						v_EvnVKExpert evke (nolock)
						inner join v_EvnDirectionHTM EDH (nolock) on EDH.EvnDirectionHTM_pid = evke.EvnVK_id
					where
						EDH.EvnDirectionHTM_id = :EvnDirectionHTM_id
				", [
					'EvnDirectionHTM_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($EvnVKExpert_id)) {
					$from = "
						v_EvnVKExpert evke with (nolock)
						inner join v_EvnVK EVK with (nolock) on EVK.EvnVK_id = evke.EvnVK_id
					";
				} else {
					$from = "
						v_VoteExpertVK evke with (nolock)
						inner join v_VoteListVK VLVK with (nolock) on VLVK.VoteListVK_id = evke.VoteListVK_id
						inner join v_EvnVK EVK with (nolock) on EVK.EvnPrescrVK_id = VLVK.EvnPrescrVK_id
					";
				}
				
				$roles = $this->queryResult("
					select
						EVKE.ExpertMedStaffType_id,
						msmp.MedPersonal_id,
						ms.Lpu_id
					from
						{$from}
						inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
						inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
						inner join v_EvnDirectionHTM EDH (nolock) on EDH.EvnDirectionHTM_pid = EVK.EvnVK_id
					where
						EDH.EvnDirectionHTM_id = :EvnDirectionHTM_id
				", array(
					'EvnDirectionHTM_id' => $data['ApprovalList_ObjectId']
				));

				foreach($roles as $key => $value) {
					$resp_epr = $this->queryResult('
						select
							"EMDPersonRole_id"
						from
							"EMD"."EMDPersonRoleLink"
						where
							"ExpertMedStaffType_id" = :ExpertMedStaffType_id						
					', array(
						'ExpertMedStaffType_id' => $value['ExpertMedStaffType_id']
					), $this->emddb);

					$EMDPersonRole_id = null;
					if (!empty($resp_epr[0]['EMDPersonRole_id'])) {
						$EMDPersonRole_id = $resp_epr[0]['EMDPersonRole_id'];
					}

					$roles[$key]['EMDPersonRole_id'] = $EMDPersonRole_id;
				}
				break;
			case 'BirthSvid':
				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_BirthSvid BS (nolock)
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = bs.MedStaffFact_id
					where
						bs.BirthSvid_id = :BirthSvid_id
						
					union all
					
					select
						6 as EMDPersonRole_id, -- главный врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_BirthSvid BS (nolock)
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = bs.MedStaffFact_cid
					where
						bs.BirthSvid_id = :BirthSvid_id
				", array(
					'BirthSvid_id' => $data['ApprovalList_ObjectId']
				));
				break;
			case 'DeathSvid':
				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_DeathSvid DS (nolock)
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = DS.MedStaffFact_id
					where
						DS.DeathSvid_id = :DeathSvid_id
				", array(
					'DeathSvid_id' => $data['ApprovalList_ObjectId']
				));
				break;
			case 'EvnRecept':
				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						ER.MedPersonal_id,
						ER.Lpu_id
					from
						v_EvnRecept ER (nolock)
					where
						ER.EvnRecept_id = :EvnRecept_id
				", array(
					'EvnRecept_id' => $data['ApprovalList_ObjectId']
				));

				if (!empty($resp_er[0]['PrescrSpecCause_id'])) {
					// нужна подпись МО
					$roles[] = [
						'EMDPersonRole_id' => null,
						'MedPersonal_id' => null,
						'Lpu_id' => $resp_er[0]['Lpu_id']
					];
				}

				if (!empty($resp_er[0]['EvnRecept_VKProtocolNum'])) {
					// нужна подпись председателя ВК
					$resp_vk = $this->queryResult("
						select top 1
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
							v_EvnVK evk (nolock)
							inner join v_EvnVKExpert EVKE (nolock) on evke.EvnVK_id = evk.EvnVK_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
							inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
						where
							evk.EvnVK_NumProtocol = :EvnRecept_VKProtocolNum
							and evke.ExpertMedStaffType_id = 1
							and evk.Person_id = :Person_id
					", [
						'Person_id' => $resp_er[0]['Person_id'],
						'EvnRecept_VKProtocolNum' => $resp_er[0]['EvnRecept_VKProtocolNum']
					]);

					if (empty($resp_vk[0]['MedPersonal_id'])) {
						// Если протокол ВК не найден, то осуществляется поиск сотрудника устроенного на службу с типом комиссия ВК в подразделении из которого выписывается рецепт.
						$resp_vk = $this->queryResult("
							declare @curDate date = dbo.tzGetDate();
							
							select top 1
								msmp.MedPersonal_id,
								ms.Lpu_id
							from
								v_MedServiceMedPersonal msmp (nolock)
								inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
								inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							where
								mst.MedServiceType_SysNick = 'vk'
								and ms.LpuBuilding_id = :LpuBuilding_id
								and msmp.MedServiceMedPersonal_begDT <= @curDate
								and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
								and ms.MedService_begDT <= @curDate
								and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
						", [
							'LpuBuilding_id' => $resp_er[0]['LpuBuilding_id']
						]);
					}

					if (!empty($resp_vk[0]['MedPersonal_id'])) {
						$roles[] = [
							'EMDPersonRole_id' => 14, // председатель
							'MedPersonal_id' => $resp_vk[0]['MedPersonal_id'],
							'Lpu_id' => $resp_vk[0]['Lpu_id']
						];
					}
				}

				break;
			case 'EvnPS':
				$listRules = $this->EMD_model->getEMDDocumentSignRules([
					'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
					'Lpu_id' => $resp_eps[0]['Lpu_id'],
					'LpuSection_id' => $resp_eps[0]['LpuSection_id']
				]);

				if (empty($listRules) || empty($listRules['EMDDocumentSign_CountSign'])) {
					return ['Error_Msg' => '']; // нет правил, значит лист согласования не нужен
				}

				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_EvnPS EPS (nolock)
						cross apply (
						    select top 1
						    	msf.MedPersonal_id,
								msf.Lpu_id
						    from
						    	v_EvnSection es (nolock)
						    	inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = es.MedStaffFact_id
						    where
						    	es.EvnSection_pid = eps.EvnPS_id
						    order by
						    	ES.EvnSection_Index desc
						) msf
					where
						EPS.EvnPS_id = :EvnPS_id
				", [
					'EvnPS_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($listRules['roles']['DEP_CHIEF'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msf.MedPersonal_id,
							msf.Lpu_id
						from
							v_MedStaffFact msf (nolock)
							inner join persis.Post p (nolock) on p.id = msf.Post_id
						where
							msf.LpuSection_id = :LpuSection_id
							and p.MedPost_id in (13,15,24,25)
							and msf.WorkData_begDate <= @curDate
							and ISNULL(msf.WorkData_endDate, @curDate) >= @curDate
					", [
						'LpuSection_id' => $resp_eps[0]['LpuSection_id'],
						'EMDPersonRole_id' => $listRules['roles']['DEP_CHIEF']
					]));
				}

				if (!empty($listRules['roles']['HEAD_DOCTOR'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
						    v_MedService ms (nolock)
							inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedService_id = ms.MedService_id
						where
							mst.MedServiceType_SysNick = 'leadermo'
							and ms.Lpu_id = :Lpu_id
							and msmp.MedServiceMedPersonal_begDT <= @curDate
							and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
							and ms.MedService_begDT <= @curDate
							and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
					", [
						'Lpu_id' => $resp_eps[0]['Lpu_id'],
						'EMDPersonRole_id' => $listRules['roles']['HEAD_DOCTOR']
					]));
				}

				break;
			case 'EvnXml':
				$listRules = $this->EMD_model->getEMDDocumentSignRules([
					'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
					'Lpu_id' => $resp_ex[0]['Lpu_id'],
					'LpuSection_id' => $resp_ex[0]['LpuSection_id']
				]);

				if (empty($listRules) || empty($listRules['EMDDocumentSign_CountSign'])) {
					return ['Error_Msg' => '']; // нет правил, значит лист согласования не нужен
				}

				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						puc.MedPersonal_id,
						puc.Lpu_id
					from
						v_EvnXml ex (nolock)
						inner join v_pmUserCache puc (nolock) on puc.pmUser_id = ex.pmUser_updID
					where
						ex.EvnXml_id = :EvnXml_id
				", [
					'EvnXml_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($listRules['roles']['DEP_CHIEF'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msf.MedPersonal_id,
							msf.Lpu_id
						from
							v_MedStaffFact msf (nolock)
							inner join persis.Post p (nolock) on p.id = msf.Post_id
						where
							msf.LpuSection_id = :LpuSection_id
							and p.MedPost_id in (13,15,24,25)
							and msf.WorkData_begDate <= @curDate
							and ISNULL(msf.WorkData_endDate, @curDate) >= @curDate
					", [
						'LpuSection_id' => $resp_ex[0]['LpuSection_id'],
						'EMDPersonRole_id' => $listRules['roles']['DEP_CHIEF']
					]));
				}

				if (!empty($listRules['roles']['HEAD_DOCTOR'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
						    v_MedService ms (nolock)
							inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedService_id = ms.MedService_id
						where
							mst.MedServiceType_SysNick = 'leadermo'
							and ms.Lpu_id = :Lpu_id
							and msmp.MedServiceMedPersonal_begDT <= @curDate
							and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
							and ms.MedService_begDT <= @curDate
							and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
					", [
						'Lpu_id' => $resp_ex[0]['Lpu_id'],
						'EMDPersonRole_id' => $listRules['roles']['HEAD_DOCTOR']
					]));
				}
				break;
			case 'EvnDirection':
				$listRules = $this->EMD_model->getEMDDocumentSignRules([
					'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
					'Lpu_id' => $resp_ed[0]['Lpu_id'],
					'LpuSection_id' => $resp_ed[0]['LpuSection_id']
				]);

				if (empty($listRules) || empty($listRules['EMDDocumentSign_CountSign'])) {
					return ['Error_Msg' => '']; // нет правил, значит лист согласования не нужен
				}

				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_EvnDirection_all ED (nolock)
						inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ED.MedStaffFact_id
					where
						ED.EvnDirection_id = :EvnDirection_id
				", [
					'EvnDirection_id' => $data['ApprovalList_ObjectId']
				]);

				if (!empty($listRules['roles']['DEP_CHIEF'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msf.MedPersonal_id,
							msf.Lpu_id
						from
							v_MedStaffFact msf (nolock)
							inner join persis.Post p (nolock) on p.id = msf.Post_id
						where
							msf.LpuSection_id = :LpuSection_id
							and p.MedPost_id in (13,15,24,25)
							and msf.WorkData_begDate <= @curDate
							and ISNULL(msf.WorkData_endDate, @curDate) >= @curDate
					", [
						'LpuSection_id' => $resp_ed[0]['LpuSection_id'],
						'EMDPersonRole_id' => $listRules['roles']['DEP_CHIEF']
					]));
				}

				if (!empty($listRules['roles']['HEAD_DOCTOR'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
						    v_MedService ms (nolock)
							inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedService_id = ms.MedService_id
							inner join v_MedStaffFact msf (nolock) on msf.MedPersonal_id = msmp.MedPersonal_id
						where
							mst.MedServiceType_SysNick = 'leadermo'
							and ms.Lpu_id = :Lpu_id
							and msmp.MedServiceMedPersonal_begDT <= @curDate
							and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
							and ms.MedService_begDT <= @curDate
							and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
					", [
						'Lpu_id' => $resp_ed[0]['Lpu_id'],
						'EMDPersonRole_id' => $listRules['roles']['HEAD_DOCTOR']
					]));
				}
				break;
			case 'EvnRecept':
				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						ER.MedPersonal_id,
						ER.Lpu_id
					from
						v_EvnRecept ER (nolock)
					where
						ER.EvnRecept_id = :EvnRecept_id
				", array(
					'EvnRecept_id' => $data['ApprovalList_ObjectId']
				));

				if (!empty($resp_er[0]['PrescrSpecCause_id'])) {
					// нужна подпись МО
					$roles[] = [
						'EMDPersonRole_id' => null,
						'MedPersonal_id' => null,
						'Lpu_id' => $resp_er[0]['Lpu_id']
					];
				}

				if (!empty($resp_er[0]['EvnRecept_VKProtocolNum'])) {
					// нужна подпись председателя ВК
					$resp_vk = $this->queryResult("
						select top 1
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
							v_EvnVK evk (nolock)
							inner join v_EvnVKExpert EVKE (nolock) on evke.EvnVK_id = evk.EvnVK_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
							inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
						where
							evk.EvnVK_NumProtocol = :EvnRecept_VKProtocolNum
							and evke.ExpertMedStaffType_id = 1
							and evk.Person_id = :Person_id
					", [
						'Person_id' => $resp_er[0]['Person_id'],
						'EvnRecept_VKProtocolNum' => $resp_er[0]['EvnRecept_VKProtocolNum']
					]);

					if (empty($resp_vk[0]['MedPersonal_id'])) {
						// Если протокол ВК не найден, то осуществляется поиск сотрудника устроенного на службу с типом комиссия ВК в подразделении из которого выписывается рецепт.
						$resp_vk = $this->queryResult("
							declare @curDate date = dbo.tzGetDate();
							
							select top 1
								msmp.MedPersonal_id,
								ms.Lpu_id
							from
								v_MedServiceMedPersonal msmp (nolock)
								inner join v_MedService ms (nolock) on ms.MedService_id = msmp.MedService_id
								inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							where
								mst.MedServiceType_SysNick = 'vk'
								and ms.LpuBuilding_id = :LpuBuilding_id
								and msmp.MedServiceMedPersonal_begDT <= @curDate
								and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
								and ms.MedService_begDT <= @curDate
								and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
						", [
							'LpuBuilding_id' => $resp_er[0]['LpuBuilding_id']
						]);
					}

					if (!empty($resp_vk[0]['MedPersonal_id'])) {
						$roles[] = [
							'EMDPersonRole_id' => 14, // председатель
							'MedPersonal_id' => $resp_vk[0]['MedPersonal_id'],
							'Lpu_id' => $resp_vk[0]['Lpu_id']
						];
					}
				}

				break;
			case 'EvnUslugaPar':
				$listRules = $this->EMD_model->getEMDDocumentSignRules([
					'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id,
					'Lpu_id' => $resp_eup[0]['Lpu_id'],
					'LpuSection_id' => $resp_eup[0]['LpuSection_id']
				]);

				if (empty($listRules) || empty($listRules['EMDDocumentSign_CountSign'])) {
					return ['Error_Msg' => '']; // нет правил, значит лист согласования не нужен
				}

				$roles = $this->queryResult("
					select
						1 as EMDPersonRole_id, -- врач
						msf.MedPersonal_id,
						msf.Lpu_id
					from
						v_MedStaffFact msf (nolock)
					where
						MSF.MedStaffFact_id = :MedStaffFact_id
				", [
					'MedStaffFact_id' => $resp_eup[0]['MedStaffFact_id']
				]);

				if (!empty($listRules['roles']['DEP_CHIEF'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msf.MedPersonal_id,
							msf.Lpu_id
						from
							v_MedServiceMedPersonal msmp (nolock)
							inner join v_MedStaffFact msf (nolock) on msf.MedPersonal_id = msmp.MedPersonal_id
							inner join persis.Post p (nolock) on p.id = msf.Post_id
						where
							msmp.MedService_id = :MedService_id
							and p.MedPost_id in (13,16)
							and msmp.MedServiceMedPersonal_begDT <= @curDate
							and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
							and msf.WorkData_begDate <= @curDate
							and ISNULL(msf.WorkData_endDate, @curDate) >= @curDate
					", [
						'MedService_id' => $resp_eup[0]['MedService_id'],
						'EMDPersonRole_id' => $listRules['roles']['DEP_CHIEF']
					]));
				}

				if (!empty($listRules['roles']['HEAD_DOCTOR'])) {
					$roles = array_merge($roles, $this->queryResult("
						declare @curDate date = dbo.tzGetDate();
						
						select top 1
							:EMDPersonRole_id as EMDPersonRole_id,
							msmp.MedPersonal_id,
							ms.Lpu_id
						from
						    v_MedService ms (nolock)
							inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
							inner join v_MedServiceMedPersonal msmp (nolock) on msmp.MedService_id = ms.MedService_id
							inner join v_MedStaffFact msf (nolock) on msf.MedPersonal_id = msmp.MedPersonal_id
						where
							mst.MedServiceType_SysNick = 'leadermo'
							and ms.Lpu_id = :Lpu_id
							and msmp.MedServiceMedPersonal_begDT <= @curDate
							and ISNULL(msmp.MedServiceMedPersonal_endDT, @curDate) >= @curDate
							and ms.MedService_begDT <= @curDate
							and ISNULL(ms.MedService_endDT, @curDate) >= @curDate
					", [
						'Lpu_id' => $resp_eup[0]['Lpu_id'],
						'EMDPersonRole_id' => $listRules['roles']['HEAD_DOCTOR']
					]));
				}
				break;
		}

		$roleCounts = [];
		foreach($roles as $one_role) {
			if (isset($roleCounts[$one_role['EMDPersonRole_id']])) {
				$roleCounts[$one_role['EMDPersonRole_id']]++;
			} else {
				$roleCounts[$one_role['EMDPersonRole_id']] = 1;
			}
		}

		// Проверка соответствия информации о сотрудниках правилам подписания документа с учетом рациональности:
		// По требованиям ЕГИСЗ (EMD"."EMDSignatureRules);
		$resp_rules = $this->queryResult('
			select
				sr."EMDSignatureRules_MinCount" as mincount,
				sr."EMDSignatureRules_MaxCount" as maxcount,
				sr."EMDPersonRole_id"
			from
				"EMD"."EMDDocumentTypeLocal" tloc
				inner join "EMD"."EMDSignatureRules" sr on sr."EMDDocumentType_id" = tloc."EMDDocumentType_id"
			where
				tloc."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
		', array(
			'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id
		), $this->emddb);

		// Если требования ЕГИСЗ отсутствуют, то по «прочим» требованиям (EMD"."EMDSignatureRulesLocal")
		if (empty($resp_rules)) {
			$resp_rules = $this->queryResult('
				select
					sr."EMDSignatureRulesLocal_MinCount" as mincount,
					sr."EMDSignatureRulesLocal_MaxCount" as maxcount,
					sr."EMDPersonRole_id"
				from
					"EMD"."EMDDocumentTypeLocal" tloc
					inner join "EMD"."EMDSignatureRulesLocal" sr on sr."EMDDocumentTypeLocal_id" = tloc."EMDDocumentTypeLocal_id"
				where
					tloc."EMDDocumentTypeLocal_id" = :EMDDocumentTypeLocal_id
			', array(
				'EMDDocumentTypeLocal_id' => $EMDDocumentTypeLocal_id
			), $this->emddb);
		}

		foreach($resp_rules as $one_rule) {
			$roleCount = $roleCounts[$one_rule['EMDPersonRole_id']] ?? 0;
			if (
				$roleCount < $one_rule['mincount']
				|| $roleCount > $one_rule['maxcount']
			) {
				return array('Error_Msg' => ''); // Список экспертов не соответствует требованиям
			}
		}

		// Создание листа согласования
		$resp_save = $this->queryResult('
			INSERT INTO "EMD"."ApprovalList" (
				"ApprovalList_ObjectName",
				"ApprovalList_ObjectId"
			)
			VALUES (
				:ApprovalList_ObjectName,
				:ApprovalList_ObjectId
			)
			RETURNING "ApprovalList_id"
		', array(
			'ApprovalList_ObjectName' => $data['ApprovalList_ObjectName'],
			'ApprovalList_ObjectId' => $data['ApprovalList_ObjectId']
		), $this->emddb);

		if (empty($resp_save[0]['ApprovalList_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения листа согласования');
		}

		// Создание списка мед Сотрудников, которым требуется выполнить подписание листа
		foreach($roles as $one_role) {
			$this->emddb->query('
				INSERT INTO "EMD"."ApprovalListMedPersonal" (
					"ApprovalList_id",
					"EMDPersonRole_id",
					"MedPersonal_id",
					"Lpu_id",
					"ApprovalListMedPersonal_IsSignature"
				)
				VALUES (
					:ApprovalList_id,
					:EMDPersonRole_id,
					:MedPersonal_id,
					:Lpu_id,
					:ApprovalListMedPersonal_IsSignature
				)
			', array(
				'ApprovalList_id' => $resp_save[0]['ApprovalList_id'],
				'EMDPersonRole_id' => $one_role['EMDPersonRole_id'],
				'MedPersonal_id' => $one_role['MedPersonal_id'],
				'Lpu_id' => $one_role['Lpu_id'],
				'ApprovalListMedPersonal_IsSignature' => empty($one_role['MedPersonal_id']) ? 2 : null
			));

			// Выполняется отправка оповещений о необходимости подписать документ по списку сотрудников для созданного листа согласования
			if (!empty($one_role['MedPersonal_id'])) {
				$docName = 'документа ' . $data['ApprovalList_ObjectName'];
				$setDate = '';
				$Person_Fin = '';
				$AdditionalInfo = '';
				switch ($data['ApprovalList_ObjectName']) {
					case 'EvnPrescrMse':
						$docName = "направления на МСЭ";
						$setDate = $resp_epm[0]['EvnPrescrMse_setDate'];
						$Person_Fin = $resp_epm[0]['Person_Fin'];
						break;
					case 'EvnDirectionHTM':
						$docName = "направления на ВМП";
						$setDate = $resp_edhtm[0]['EvnDirectionHTM_setDate'];
						$Person_Fin = $resp_edhtm[0]['Person_Fin'];
						break;
					case 'EvnVK':
						$docName = "протокола ВК";
						$setDate = $resp_evk[0]['EvnVK_setDate'];
						$Person_Fin = $resp_evk[0]['Person_Fin'];
						break;
					case 'BirthSvid':
						$docName = "свидетельства о рождении";
						$setDate = $resp_bs[0]['BirthSvid_GiveDate'];
						$Person_Fin = $resp_bs[0]['Person_Fin'];
						break;
					case 'DeathSvid':
						$docName = "свидетельства о смерти";
						$setDate = $resp_ds[0]['DeathSvid_GiveDate'];
						$Person_Fin = $resp_ds[0]['Person_Fin'];
						break;
					case 'EvnRecept':
						$docName = "льготного рецепта " . $resp_er[0]['EvnRecept_Ser'] . " №" . $resp_er[0]['EvnRecept_Num'];
						$setDate = $resp_er[0]['EvnRecept_setDate'];
						$Person_Fin = $resp_er[0]['Person_Fin'];
						break;
					case 'EvnPS':
						$docName = "КВС";
						$setDate = $resp_eps[0]['EvnPS_setDate'];
						$Person_Fin = $resp_eps[0]['Person_Fin'];
						break;
					case 'EvnXml':
						$docName = "протокола";
						$setDate = $resp_ex[0]['EvnXml_updDate'];
						$Person_Fin = $resp_ex[0]['Person_Fin'];
						break;
					case 'EvnDirection':
						$docName = "направления";
						$setDate = $resp_ed[0]['EvnDirection_setDate'];
						$Person_Fin = $resp_ed[0]['Person_Fin'];
						break;
					case 'EvnUslugaPar':
						$docName = "протокола лабораторного исследования";
						$setDate = $resp_eup[0]['EvnUslugaPar_setDate'];
						$Person_Fin = $resp_eup[0]['Person_Fin'];
						$AdditionalInfo = ' ' . $resp_eup[0]['UslugaComplex_Name'];
						break;
				}
				$message = "Требуется Ваша подпись {$docName} от {$setDate} {$Person_Fin}{$AdditionalInfo}." . PHP_EOL;
				$message .= "<a href='#' onClick=\"getWnd('swEMDSignWindow').show({ EMDRegistry_ObjectName: '{$data['ApprovalList_ObjectName']}', EMDRegistry_ObjectID: {$data['ApprovalList_ObjectId']} });\">Подписать</a>";
				$noticeData = array(
					'autotype' => 5,
					'pmUser_id' => $data['pmUser_id'],
					'Lpu_rid' => $one_role['Lpu_id'],
					'MedPersonal_rid' => $one_role['MedPersonal_id'],
					'type' => 1,
					'title' => 'Подпись ' . $docName,
					'text' => $message
				);
				$this->load->model('Messages_model');
				$this->Messages_model->autoMessage($noticeData);
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Удаление листа согласования
	 */
	function deleteApprovalList($data) {
		if (!$this->isEMDEnabled) {
			return;
		}

		if (getRegionNick() != 'msk' && in_array($data['ApprovalList_ObjectName'], ['EvnPS', 'EvnXml', 'EvnDirection', 'EvnUslugaPar'])) {
			return;
		}

		$this->emddb->query('
			delete from
				"EMD"."ApprovalListMedPersonal"
			where
				"ApprovalList_id" IN (
					select
						"ApprovalList_id"
					from
						"EMD"."ApprovalList"
					where
						"ApprovalList_ObjectName" = :ApprovalList_ObjectName
						and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
				);
				
			delete from
				"EMD"."ApprovalList"
			where
				"ApprovalList_ObjectName" = :ApprovalList_ObjectName
				and "ApprovalList_ObjectId" = :ApprovalList_ObjectId
		', array(
			'ApprovalList_ObjectName' => $data['ApprovalList_ObjectName'],
			'ApprovalList_ObjectId' => $data['ApprovalList_ObjectId']
		));
	}
}
?>