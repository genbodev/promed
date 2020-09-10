<?php
/**
* Vologda_Polka_PersonCard_model - модель, для работы с таблицей PersonCard (Вологда)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      27.05.2015
*/

require_once(APPPATH.'models/Polka_PersonCard_model.php');

class Vologda_Polka_PersonCard_model extends Polka_PersonCard_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
	}

	/**
	 * @param $object
	 * @return array
	 */
	function objectToArray($object) {
		if (!is_object($object) && !is_array($object)) {
			return $object;
		}
		if (is_object($object)) {
			$object = get_object_vars($object);
		}
		if (empty($object)) {
			return null;
		}
		return array_map(array($this, 'objectToArray'), $object);
	}

	/**
	 *	Список прикрепленного населения к указанной МО на указанную дату
	 */
	function loadAttachedList($data)
	{
		$filterList = array();
		$queryParams = array();

		if ( !empty($data['AttachLpu_id']) ) {
			$filterList[] = 'PC.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['AttachLpu_id'];
		}

		$query = "
			select
				PC.PersonCard_Code as ID_PAC, -- Номер истории болезни
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as OT, -- Отчество
				PS.Sex_id as W, -- Пол застрахованного
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения застрахованного
				PT.PolisType_CodeF008 as VPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as SPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as NPOLIS
			from
				v_PersonState PS with (nolock)
				inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
				inner join v_Polis PLS with (nolock) on PLS.Polis_id = ps.Polis_id
				inner join v_PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
			where PC.LpuAttachType_id = 1
				and (PC.CardCloseCause_id is null or PC.CardCloseCause_id <> 4)
				and (PLS.Polis_endDate is null or PLS.Polis_endDate > dbo.tzGetDate())
				and (
					(PLS.PolisType_id = 4 and dbo.getRegion() <> 2 and PS.Person_EdNum is not null)
					or ((PLS.PolisType_id <> 4 or dbo.getRegion() = 2) and PLS.Polis_Num is not null)
				)
				and PT.PolisType_CodeF008 is not null
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$PERS = $result->result('array');

		if ( !is_array($PERS) || count($PERS) == 0) {
			return array(
				'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
			);
		}

		$ZGLV = array(
			array(
				'CODE_MO' => ''
			,'SMO' => ''
			,'ZAP' => 0
			)
		);

		// Получаем код МО
		if ( !empty($data['AttachLpu_id']) ) {
			$query = "
				select top 1
					 Lpu_f003mcod as CODE_MO
					,null as SMO
				from v_Lpu with (nolock)
				where Lpu_id = :Lpu_id
			";
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return false;
			}

			$ZGLV = $result->result('array');

			if ( !is_array($ZGLV) || count($ZGLV) == 0) {
				return array(
					'Error_Code' => 1, 'Error_Msg' => 'Ошибка при получении кода МО!'
				);
			}
		}

		$data = array();
		$data['Error_Code'] = 0;

		$ZGLV[0]['ZAP'] = count($PERS);

		$data['PERS'] = $PERS;
		$data['ZGLV'] = $ZGLV;

		return $data;
	}

	/**
	 *	Получение данных для формы списка заявлений о выборе МО
	 */
	function loadPersonCardAttachGrid($data)
	{
		//var_dump($data);die;
		$filter = '';
		$params = array();
		if(!empty($data['Lpu_aid']))
		{
			$filter .= ' and PCA.Lpu_aid = :Lpu_aid';
			$params['Lpu_aid'] = $data['Lpu_aid'];
		}
		if( !empty($data['Person_SurName']) ) {
			$filter .= " and PS.Person_SurName like :Person_SurName + '%'";
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		
		if( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName like :Person_FirName + '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		
		if( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName like :Person_SecName + '%'";
			$params['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if(!empty($data['PersonCardAttachStatusType_id'])) {
			$filter .= " and PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$params['PersonCardAttachStatusType_id'] = $data['PersonCardAttachStatusType_id'];
		}
		if(isset($data['Person_BirthDay_Range'][0])){
			$filter .= " and PS.Person_BirthDay >= :begBirthday";
			$params['begBirthday'] = $data['Person_BirthDay_Range'][0];
		}
		if(isset($data['Person_BirthDay_Range'][1])){
			$filter .= " and PS.Person_BirthDay <= :endBirthday";
			$params['endBirthday'] = $data['Person_BirthDay_Range'][1];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][0])){
			$filter .= " and PCA.PersonCardAttach_setDate >= :betAttachDate";
			$params['betAttachDate'] = $data['PersonCardAttach_setDate_Range'][0];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][1])){
			$filter .= " and PCA.PersonCardAttach_setDate <= :endAttachDate";
			$params['endAttachDate'] = $data['PersonCardAttach_setDate_Range'][1];
		}
		if( !empty($data['RecMethodType_id']) ) {
			$filter .= " and RMT.RecMethodType_id = :RecMethodType_id ";
			$params['RecMethodType_id'] = rtrim($data['RecMethodType_id']);
		}
		$query = "
			select
				--select
				s.*
				--end select
			from
				--from
				(
					select
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate,
						ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_Name as LpuRegion_fapName,
						case when PC.PersonCard_id is null then 'false' else 'true' end as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA (nolock)
					inner join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
					left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
					left join v_RecMethodType RMT (nolock) on RMT.RecMethodType_id = PCA.RecMethodType_id
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
					) PCAS
					inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join PersonCard PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PCA.LpuRegion_fapid
					where PCA.LpuRegion_id is not null
					{$filter}

					union --Костылина, т.к. старые заявления не имеют ни участка, ни персона, ни врача (проверяется по LpuRegion_id - если его нет, значит это старое заявление)
					select 
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate,
						ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_id as LpuRegion_fapName,
						'true' as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA
					cross apply
					(
						select top 1 PCard.PersonCard_id,
						PCard.LpuRegion_id,
						PCard.LpuRegion_fapid,
						PCard.Lpu_id,
						PCard.MedStaffFact_id,
						PCard.Person_id
						from v_PersonCard_all PCard (nolock)
						where PCard.PersonCardAttach_id = PCA.PersonCardAttach_id
					) PC
					inner join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PC.LpuRegion_fapid
					inner join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
					left join v_RecMethodType RMT (nolock) on RMT.RecMethodType_id = PCA.RecMethodType_id
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
					) PCAS
					inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id

					where PCA.LpuRegion_id is null
					{$filter}
				) S
				--end from
			where
				--where
				(1=1)
				--end where
			order by
				-- order by
				PersonCardAttach_setDate2 desc
				-- end order by

		";
		//echo getDebugSQL($query, $params);die;
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Проверка наличия активного прикрепления
	 */
	function checkPersonCardActive($data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'	=> $data['Lpu_id']
		);
		$query = "
			select
				PC.PersonCard_id,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
				LR.LpuRegion_Name,
				LRT.LpuRegionType_Name
			from v_PersonCard PC (nolock)
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			where PC.Lpu_id = :Lpu_id and PC.Person_id = :Person_id and PC.LpuAttachType_id=1
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	 *	Получение данных по заявлению о выборе МО
	 */
	function loadPersonCardAttachForm($data)
	{
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);

		$query = "
			select top 1
				PCA.PersonCardAttach_id,
				PCA.Lpu_aid,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(PCA.Person_id, PS.Person_id) as Person_id,
				PCAS.PersonCardAttachStatus_id,
				ISNULL(LR.LpuRegion_id, LR2.LpuRegion_id) as LpuRegion_id,
				ISNULL(fLR.LpuRegion_id, fLR2.LpuRegion_id) as LpuRegion_fapid,
				COALESCE(LR.LpuRegionType_id, LR2.LpuRegionType_id, PCA.LpuRegionType_id) as LpuRegionType_id,
				ISNULL(PCA.MedStaffFact_id, PC.MedStaffFact_id) as MedStaffFact_id,
				PAC.PersonAmbulatCard_id,
				rtrim(rtrim(ISNULL(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code))) as PersonCard_Code,
				PCA.PersonCardAttach_ExpNameFile,
				PCA.PersonCardAttach_ExpNumRow
			from
				v_PersonCardAttach PCA with(nolock)
				outer apply (
					select top 1 PCAS.PersonCardAttachStatus_id,
					PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS (nolock)
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_setDate desc
				) PCAS
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuRegion fLR (nolock) on fLR.LpuRegion_id = PCA.LpuRegion_fapid
				--left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_PersonCard_all PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_LpuRegion LR2 (nolock) on LR2.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegion fLR2 (nolock) on fLR2.LpuRegion_id = PC.LpuRegion_fapid
				left join v_PersonAmbulatCardLink PACL (nolock) on PACL.PersonCard_id = PC.PersonCard_id
				left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = ISNULL(PCA.PersonAmbulatCard_id,PACL.PersonAmbulatCard_id)
			where
				PCA.PersonCardAttach_id = :PersonCardAttach_id
		";


		
		$result = $this->queryResult($query, $params);

		if (isset($result[0]) && !empty($result[0]['PersonCardAttach_id'])) {
			$files = $this->getFilesOnPersonCardAttach(array(
				'PersonCardAttach_id' => $result[0]['PersonCardAttach_id'],
				'PersonCard_id' => null,
			));
			if (!$files) {
				$this->createError('Ошибка при получении списка прикрепленных файлов');
			}
			$result[0]['files'] = $files;
		}


		return $result;
	}

	/**
	 *	Сохранение заявления о выборе МО
	 */
	function savePersonCardAttachForm($data) {
		$params = array(
			'PersonCardAttach_id'			=> !empty($data['PersonCardAttach_id'])?$data['PersonCardAttach_id']:null,
			'Lpu_id' 						=> $data['Lpu_aid'],
			'Lpu_aid' 						=> $data['Lpu_aid'],
			'LpuRegionType_id' 				=> $data['LpuRegionType_id'],
			'LpuRegion_id' 					=> $data['LpuRegion_id'],
			'LpuRegion_fapid' 				=> $data['LpuRegion_fapid'],
			'PersonCardAttach_setDate'		=> $data['PersonCardAttach_setDate'],
			'MedStaffFact_id' 				=> $data['MedStaffFact_id'],
			'Person_id' 					=> $data['Person_id'],
			'PersonAmbulatCard_id' 			=> $data['PersonAmbulatCard_id'],
			'PersonCardAttach_IsSMS' 		=> 1,
			'PersonCardAttach_SMS' 			=> null,
			'PersonCardAttach_IsEmail' 		=> 1,
			'PersonCardAttach_Email' 		=> null,
			'PersonCardAttach_IsHimself' 	=> null,
			'PersonCardAttach_ExpNameFile'	=> !empty($data['PersonCardAttach_ExpNameFile'])?$data['PersonCardAttach_ExpNameFile']:null,
			'PersonCardAttach_ExpNumRow'	=> !empty($data['PersonCardAttach_ExpNumRow'])?$data['PersonCardAttach_ExpNumRow']:null,
			'RecMethodType_id' 				=> !empty($data['PersonCardAttach_id']) ? 16 : 
			//При добавлении заявления устанавливать источник записи «Промед: регистратор»
												(!empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : null),
			'pmUser_id' 					=> $data['pmUser_id']
		);
		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

		$this->beginTransaction();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardAttach_id;
			exec {$procedure}
				@PersonCardAttach_id = @Res output,
				@PersonCardAttach_setDate = :PersonCardAttach_setDate,
				@Lpu_id = :Lpu_id,
				@Lpu_aid = :Lpu_aid,
				@Person_id = :Person_id,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@LpuRegion_id = :LpuRegion_id,
				@LpuRegion_fapid = :LpuRegion_fapid,
				@LpuRegionType_id = :LpuRegionType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@Address_id = null,
				@Polis_id = null,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@PersonCardAttach_ExpNameFile = :PersonCardAttach_ExpNameFile,
				@PersonCardAttach_ExpNumRow = :PersonCardAttach_ExpNumRow,
				@RecMethodType_id = :RecMethodType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении заявления');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		//При добавлении заявления сохраняется статус "Принято"
		if (empty($data['PersonCardAttach_id'])) {
			$resp = $this->savePersonCardAttachStatus(array(
				'PersonCardAttach_id' => $response[0]['PersonCardAttach_id'],
				'PersonCardAttachStatusType_Code' => 1,
				'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 *	Установка статуса заявления
	 */
	function changePersonCardAttachStatus($data){
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id'],
			'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res_Str = array('success'=>true,'string'=>'');
		$queryCheck = "
			select top 1 
				PC.PersonCard_id,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
				ISNULL(LRT.LpuRegionType_Name,'') as LpuRegionType_Name,
				ISNULL(L.Lpu_Nick,'') as Lpu_Nick,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCard_all PC (nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			left join v_PersonCardAttach PCA (nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultCheck = $this->db->query($queryCheck, $params);
		if(!is_object($resultCheck))
		{
			$query = "
			update dbo.PersonCardAttachStatus with (ROWLOCK) set
				PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
				pmUser_updID = :pmUser_id,
				PersonCardAttachStatus_updDT = GetDate()
				where PersonCardAttach_id = :PersonCardAttach_id
			";
			$result = $this->db->query($query, $params);
		}
		else
		{
			$resultCheck = $resultCheck->result('array');
			if(count($resultCheck) == 0)
			{
				$query = "
					update dbo.PersonCardAttachStatus with (ROWLOCK) set
					PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
					pmUser_updID = :pmUser_id,
					PersonCardAttachStatus_updDT = GetDate()
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result = $this->db->query($query, $params);
			}
			else
			{
				$res_Str['string'] = 'Заявление от '.$resultCheck[0]['PersonCardAttach_setDate'].' ('. $resultCheck[0]['Person_FIO'].') '.'связано с прикреплением. Смена статуса невозможна.';
			}
		}
		return $res_Str;
		//return true;
	}

	/**
	 *	Установка статуса заявления по имеющемуся PersonCard_id
	 */
	function changePersonCardAttachStatusByPersonCard($data)
	{
		$params_get_PersonCardAttach = array(
			'PersonCard_id' => $data['PersonCard_id']
		);
		$query_get_PersonCardAttach = "
			select top 1 PC.PersonCardAttach_id
			from v_PersonCard_all PC (nolock)
			where PC.PersonCard_id = :PersonCard_id
		";
		$result_get_PersonCardAttach = $this->db->query($query_get_PersonCardAttach,$params_get_PersonCardAttach);
		if(is_object($result_get_PersonCardAttach))
		{
			$result_get_PersonCardAttach = $result_get_PersonCardAttach->result('array');
			if(is_array($result_get_PersonCardAttach) && count($result_get_PersonCardAttach) > 0)
			{
				$params = array(
					'PersonCardAttach_id' => $result_get_PersonCardAttach[0]['PersonCardAttach_id'],
					'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$query = "
					update dbo.PersonCardAttachStatus with (ROWLOCK) set
					PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
					pmUser_updID = :pmUser_id,
					PersonCardAttachStatus_updDT = GetDate()
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result = $this->db->query($query, $params);
			}
		}
		return true;
	}

	/**
	 *	Проверка связи заявления с прикреплением
	 */
	function checkPersonCardByAttach($data) {
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id']
		);
		$query = "
			select top 1 
				PC.PersonCard_id,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
				ISNULL(LRT.LpuRegionType_Name,'') as LpuRegionType_Name,
				ISNULL(L.Lpu_Nick,'') as Lpu_Nick,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCard_all PC (nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			left join v_PersonCardAttach PCA (nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 *	Проверка статуса заявления
	 */
	function checkAttachStatus($data){
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id']
		);
		$query = "
		select 
			PCAS.PersonCardAttachStatusType_Code,
			PCAS.PersonCardAttachStatusType_Name,
			convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
			ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
		from
			v_PersonCardAttach PCA with(nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
			outer apply (
				select top 1 PCAST.PersonCardAttachStatusType_Code, PCAST.PersonCardAttachStatusType_Name
				from v_PersonCardAttachStatus PCAS (nolock)
				left join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
				order by PCAS.PersonCardAttachStatusType_id desc
			) PCAS
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 *	Добавление прикрепления на основе заявления
	 */
	function addPersonCardByAttach($data){
		$queryAttach = "
			select
				PCA.Lpu_aid as Lpu_id,
				PCA.Person_id,
				PCA.LpuRegion_id,
				PCA.MedStaffFact_id,
				PCA.LpuRegion_fapid,
				ISNULL(PCA.PersonAmbulatCard_id,0) as PersonAmbulatCard_id,
				ISNULL(PAC.PersonAmbulatCard_Num,'') as PersonAmbulatCard_Code
			from v_PersonCardAttach PCA (nolock)
			left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = PCA.PersonAmbulatCard_id
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultAttach = $this->db->query($queryAttach,array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
		if(is_object($resultAttach)){
			$resultAttach = $resultAttach->result('array');
			$params = array(
				'PersonCard_id' => null,
				'CardCloseCause_id' => null,
				'Lpu_id' => $resultAttach[0]['Lpu_id'],
				'Person_id' => $resultAttach[0]['Person_id'],
				'LpuRegion_id' => $resultAttach[0]['LpuRegion_id'],
				'MedStaffFact_id' => $resultAttach[0]['MedStaffFact_id'],
				'LpuRegion_fapid' => $resultAttach[0]['LpuRegion_fapid'],
				'PersonAmbulatCard_id' => $resultAttach[0]['PersonAmbulatCard_id'],
				'PersonAmbulatCard_Code' => $resultAttach[0]['PersonAmbulatCard_Code'],
				'pmUser_id' => $data['pmUser_id']
			);
			if($resultAttach[0]['PersonAmbulatCard_id'] == 0){ //Если не указана амбулаторная карта, то берем последнюю у пациента, либо создаем новую
				$query_SearchAmbulatCard = "
					select top 1 PersonAmbulatCard_Num
					from v_PersonAmbulatCard
					where Person_id = :Person_id
					order by PersonAmbulatCard_id desc
				";
				$resultAmbulatCard = $this->db->query($query_SearchAmbulatCard,$resultAttach[0]);
				$resultAmbulatCard = $resultAmbulatCard->result('array');
				if(isset($resultAmbulatCard[0]['PersonAmbulatCard_Num']))
					$params['PersonAmbulatCard_Code'] = $resultAmbulatCard[0]['PersonAmbulatCard_Num'];
				else { //У пациента нет АК, поэтому нужно создать
					$params_PersonAmbulatCard = array();
					$data['Lpu_id'] = $resultAttach[0]['Lpu_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_id'] = null;
                    $params_PersonAmbulatCard['Server_id'] = $data['Server_id'];
                    $params_PersonAmbulatCard['Person_id'] = $resultAttach[0]['Person_id'];
                    $PersonCardCode_res = $this->getPersonCardCode($data);
                    $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $PersonCardCode_res[0]['PersonCard_Code'];
                    $personCard_Code = $params_PersonAmbulatCard['PersonAmbulatCard_Num'];
                    $params_PersonAmbulatCard['Lpu_id'] = $data['Lpu_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_CloseCause'] = null;
                    $params_PersonAmbulatCard['PersonAmbulatCard_endDate'] = null;
                    $params_PersonAmbulatCard['pmUser_id'] = $data['pmUser_id'];
                    $query_PersonAmbulatCard = "
                        declare
                            @Res bigint,
                            @ErrCode int,
                            @time datetime,
                            @ErrMessage varchar(4000);

                        set @Res = :PersonAmbulatCard_id;
                        set @time = (select dbo.tzGetDate());
                        exec p_PersonAmbulatCard_ins
                            @Server_id = :Server_id,
                            @PersonAmbulatCard_id = @Res output,
                            @Person_id = :Person_id,
                            @PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
                            @Lpu_id = :Lpu_id,
                            @PersonAmbulatCard_CloseCause =:PersonAmbulatCard_CloseCause,
                            @PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
                            @PersonAmbulatCard_begDate = @time,
                            @pmUser_id = :pmUser_id,
                            @Error_Code = @ErrCode output,
                            @Error_Message = @ErrMessage output;

                        select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                    ";
                    $result_PersonAmbulatCard = $this->db->query($query_PersonAmbulatCard,$params_PersonAmbulatCard);
                    $params['PersonAmbulatCard_Code'] = $personCard_Code;
                    if(is_object($result_PersonAmbulatCard)){
                        $result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
                        $change_lpu = 1;
                        //Теперь добавляем PersonAmbulatCardLocat - движение амбулаторной карты
                        $PersonAmbulatCard_id = $result_PersonAmbulatCard[0]['PersonAmbulatCard_id'];
                        $params_PersonAmbulatCardLocat = array();
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_id'] = null;
                        $params_PersonAmbulatCardLocat['Server_id'] = $data['Server_id'];
                        $params_PersonAmbulatCardLocat['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                        $params_PersonAmbulatCardLocat['AmbulatCardLocatType_id'] = 1;
                        $params_PersonAmbulatCardLocat['MedStaffFact_id'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_begDate'] = date('Y-m-d H:i');
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_Desc'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_OtherLocat'] = null;
                        $params_PersonAmbulatCardLocat['pmUser_id'] = $data['pmUser_id'];
                        $query_PersonAmbulatCardLocat = "
                            declare
                                @Res bigint,
                                @ErrCode int,
                                @ErrMessage varchar(4000);

                            set @Res = :PersonAmbulatCardLocat_id;
                            exec p_PersonAmbulatCardLocat_ins
                                @Server_id = :Server_id,
                                @PersonAmbulatCardLocat_id = @Res output,
                                @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                                @AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
                                @MedStaffFact_id = :MedStaffFact_id,
                                @PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
                                @PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
                                @PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;

                            select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                        $result_PersonAmbulatCardLocat = $this->db->query($query_PersonAmbulatCardLocat,$params_PersonAmbulatCardLocat);
                    }
				}
			}
			
			$procedure = 'p_PersonCard_ins';
			$resultPersonCard = array();
			//Проверим, а есть ли у этого пациента активное прикрепление
			$queryPersonCard = "
				select top 1 *
				from v_PersonCard (nolock)
				where Person_id = :Person_id
				and LpuAttachType_id = 1
				order by PersonCard_begDate desc
			";
			$resultPersonCard = $this->db->query($queryPersonCard,$params);
			$resultPersonCard = $resultPersonCard->result('array');
			if(count($resultPersonCard) > 0){
				$params['PersonCard_id'] = $resultPersonCard[0]['PersonCard_id'];
				$params['CardCloseCause_id'] = 1;
				$procedure = 'p_PersonCard_upd';
				if($resultPersonCard[0]['Lpu_id'] == $resultAttach[0]['Lpu_id'])
					$params['CardCloseCause_id'] = 4;


				$upd_params = array();
				$beg_date = date('Y-m-d H:i:00.000');
				$upd_params['BegDate'] = $beg_date;

				if (!empty($data['PersonCard_begDate'])) {
					$upd_params['BegDate'] = $data['PersonCard_begDate'];
				} else {
					//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
					$query_get_AttachDate = "
						select top 1 CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
						from v_PersonCardAttach
						where PersonCardAttach_id = :PersonCardAttach_id
					";
					$result_get_AttachDate = $this->getFirstResultFromQuery($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
					if (!empty($result_get_AttachDate)) {
						$upd_params['BegDate'] = $result_get_AttachDate;
					}
				}
                //$beg_date = date('Y-m-d H:i:00.000');
                $upd_params['PersonCard_id'] = $params['PersonCard_id'];
                $upd_params['Lpu_id'] = $params["Lpu_id"];
                $upd_params['Server_id'] = $data["Server_id"];
                $upd_params['Person_id'] = $params["Person_id"];
                $upd_params['PersonCard_IsAttachCondit'] = null;
                //$upd_params['BegDate'] = $beg_date;
                $upd_params['EndDate'] = null;
                $upd_params['CardCloseCause_id'] = $params['CardCloseCause_id'];
                $upd_params['pmUser_id'] = $params['pmUser_id'];
                $upd_params['PersonCard_Code'] = $params['PersonAmbulatCard_Code'];
                $upd_params['LpuRegion_id'] = $params["LpuRegion_id"];
                $upd_params['LpuRegion_Fapid'] = $params['LpuRegion_fapid'];
                $upd_params['LpuAttachType_id'] = 1;
                $upd_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $upd_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = :PersonCard_id;
						exec p_PersonCard_upd
							@PersonCard_id = @Res output,
							@Lpu_id = :Lpu_id,
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@PersonCard_begDate = :BegDate,
							@PersonCard_endDate = :EndDate,
							@PersonCard_Code = :PersonCard_Code,
							@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
							@LpuRegion_id = :LpuRegion_id,
							@LpuRegion_fapid = :LpuRegion_Fapid,
							@LpuAttachType_id = :LpuAttachType_id,
							@CardCloseCause_id = :CardCloseCause_id,
							@PersonCardAttach_id = :PersonCardAttach_id,
							@MedStaffFact_id = :MedStaffFact_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
                $result = $this->db->query($sql, $upd_params);
			}
			else
			{
				$beg_date = date('Y-m-d H:i:00.000');
				$ins_params = array();
				$ins_params['PersonCard_begDate'] = $beg_date;

				if (!empty($data['PersonCard_begDate'])) {
					$ins_params['PersonCard_begDate'] = $data['PersonCard_begDate'];
				} else {
					//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
					$query_get_AttachDate = "
						select CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
						from v_PersonCardAttach
						where PersonCardAttach_id = :PersonCardAttach_id
					";
					$result_get_AttachDate = $this->getFirstResultFromQuery($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
					if (!empty($result_get_AttachDate)) {
						$ins_params['PersonCard_begDate'] = $result_get_AttachDate;
					}
				}

                $ins_params['Lpu_id'] = $resultAttach[0]['Lpu_id'];
                $ins_params['Server_id'] = $data["Server_id"];
                $ins_params['Person_id'] = $params["Person_id"];
                $ins_params['PersonCard_IsAttachCondit'] = null;
                //$ins_params['PersonCard_begDate'] = $beg_date;
                $ins_params['PersonCard_Code'] = $params['PersonAmbulatCard_Code'];
                $ins_params['EndDate'] = null;
                $ins_params['pmUser_id'] = $data['pmUser_id'];
                $ins_params['LpuRegion_id'] = $params["LpuRegion_id"];
                $ins_params['LpuRegion_Fapid'] = $params['LpuRegion_fapid'];
                $ins_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $ins_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
                    declare
                        @Res bigint,
                        @ErrCode int,
                        @ErrMessage varchar(4000);
                    set @Res = null;
                    exec p_PersonCard_ins
                        @PersonCard_id = @Res output,
                        @Lpu_id = :Lpu_id,
                        @Server_id = :Server_id,
                        @Person_id = :Person_id,
                        @PersonCard_begDate = :PersonCard_begDate,
                        @PersonCard_Code = :PersonCard_Code,
                        @PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
                        @PersonCard_IsAttachAuto = 2,
                        @LpuRegion_id = :LpuRegion_id,
                        @LpuRegion_fapid = :LpuRegion_Fapid,
                        @LpuAttachType_id = 1,
                        @CardCloseCause_id = null,
                        @PersonCardAttach_id = :PersonCardAttach_id,
                        @MedStaffFact_id = :MedStaffFact_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                ";
                //echo getDebugSQL($sql, $ins_params);die;
                $result = $this->db->query($sql, $ins_params);
			}
			return $result->result('array');
		}
		else
			return false;
	}

	/**
	 *	Получение номера прикрепления
	 */
	function getPersonCardCode($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
		);
		$query = "
			declare
				@ObjID bigint;
			exec xp_GenpmID 
				@ObjectName = 'PersonCard', 
				@Lpu_id = :Lpu_id,
				@ObjectID = @ObjID output;
			select @ObjID as PersonCard_Code;
		";
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при генерации номера амбулаторной карты');
		}
		$result[0]['success'] = true;
		return $result;
	}

	/**
	* Поиск человека по ФИО, ДР и СНИЛС
	*/
	function searchPerson($data){
		$query = "
			select top 1 Person_id
			from v_PersonState (nolock)
			where REPLACE(REPLACE(Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:SNILS,'-',''),' ','')
			and Person_SurName = :FAM
			and Person_FirName = :IM
			and Person_SecName = :OT
			and Person_BirthDay = :DR
		";
		$result = $this->db->query($query,$data);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result)>0)
				return $result[0]['Person_id'];
			else
				return 0;
		}
		else
			return 0;
	}

	/**
	* Поиск врача по СНИЛС
	*/
	function searchMedPersonal($SSD,$LPUC){
		$query = "
			select top 1 MP.Person_Fio
			from v_MedPersonal MP (nolock)
			inner join v_Lpu L (nolock) on L.Lpu_id = MP.Lpu_id
			where REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE('{$SSD}','-',''),' ','')
			and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = '{$LPUC}'
		";
		//echo getDebugSQL($query,array());die;
		$result = $this->db->query($query,array());
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result)>0)
				return $result[0]['Person_Fio'];
			else
				return 'не указан';
		}
		else
			return 'не указан';
	}

	/**
	*	Поиск открепления/прикрепления/заявления
	*/
	function searchPersonCard($data)
	{
		$result_ret = array(
			'PersonCard_id' => '0',
			'PersonCardAttach_id' => '0',
			'ItemExists' => '0'
		);
		$params = array(
			'Person_id' => $data['PER_ID'],
			'Lpu_Code' 	=> $data['LPU_CODE'],
			'LpuRegion_Name' => $data['LR_N'],
			'MedPersonal_Snils' => $data['SSD'],
			'PersonCard_Date' => $data['DATE_1']
		);

		$and_date = '';
		if($data['T_PRIK'] == '2') //Открепление
		{
			$and_date = ' and convert(varchar(10), PC.PersonCard_endDate, 120) = :PersonCard_Date';
		}
		else //Прикрепление
		{
			$and_date = ' and convert(varchar(10), PC.PersonCard_begDate, 120) = :PersonCard_Date';
		}
		$query = "
			select top 1 PC.PersonCard_id
			from v_PersonCard_all PC (nolock)
			inner join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			inner join v_Lpu (nolock) L on L.Lpu_id = PC.Lpu_id
			inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
			left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
			where (1=1)
			and PC.Person_id = :Person_id
			and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
			and LR.LpuRegion_Name = :LpuRegion_Name
			and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
			and (PC.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
			{$and_date}
		";
		/*else if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '2') //Заявительное прикрепление
		{

		}*/
		//echo getDebugSQL($query,$params);die;
		/*if($data['PER_ID'] == '60690')
		{
			echo getDebugSQL($query,$params);die;
		}*/
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0) //Нашли прикрепление/открепление. Возвращаем его.
			{
				$result_ret['PersonCard_id'] = $result[0]['PersonCard_id'];
				$result_ret['ItemExists'] = '1';
				return $result_ret;
			}
			else
			{
				if($data['T_PRIK'] == '2') //Открепление. Не нашли.
					return $result_ret;
				if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '1') //Территориальное прикрепление. Не нашли.
					return $result_ret;
				if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '2') //Заявительное прикрепление. Не нашли. Тогда поищем заявление.
				{
					$query_a = "
						select top 1 PCA.PersonCardAttach_id
						from v_PersonCardAttach PCA (nolock)
						left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
						left join v_Lpu (nolock) L on L.Lpu_id = PCA.Lpu_aid
						left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
						left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
						left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
						where (1=1)
						and PCA.Person_id = :Person_id
						and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
						and LR.LpuRegion_Name = :LpuRegion_Name
						and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
						and (PCA.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
						and convert(varchar(10), PCA.PersonCardAttach_setDate, 120) = :PersonCard_Date
					";
					//echo getDebugSQL($query_a,$params);die;
					/*if($data['PER_ID'] == '1673')
					{echo getDebugSQL($query_a,$params);die;}*/
					$result_a = $this->db->query($query_a,$params);
					//var_dump($result_a);die;
					if(is_object($result_a))
					{
						$result_a = $result_a->result('array');
						//var_dump($result_a);die;
						if(count($result_a) > 0)
						{
							$result_ret['PersonCardAttach_id'] = $result_a[0]['PersonCardAttach_id'];
							$result_ret['ItemExists'] = '1';
							return $result_ret;
						}
						else
						{
							return $result_ret;
						}
					}
					else
						return $result_ret;
				}
			}
		}
		else
			return $result_ret;
	}

	/**
	 * Формирование пути до каталога для экспорта
	 * @param string|null $mode
	 * @return string
	 */
	function getExportPersonCardAttachPath($mode = null) {
		$out_dir = $this->regionNick . '_person_card_attach';
		if ($mode) $out_dir .= '_' . $mode;
		return EXPORTPATH_PC . $out_dir . (empty($mode) ? '' : '_' . time());
	}

	/**
	 * Отвязка заявлений о прикреплении от файла экспорта
	 * @param array $data
	 * @return array
	 */
	function clearPersonCardAttachFile($data) {
		$params = array(
			'PersonCardAttach_ExpNameFile' => $data['PersonCardAttach_ExpNameFile'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			declare @date datetime = (select top 1 dbo.tzGetDate())
			set nocount on
			begin try
				update 
					PersonCardAttach with(rowlock)
				set
					PersonCardAttach_ExpNumRow = null,
					PersonCardAttach_ExpNameFile = null,
					PersonCardAttach_updDT = @date,
					pmUser_updID = :pmUser_id
				where
					PersonCardAttach_ExpNameFile = :PersonCardAttach_ExpNameFile
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при отвязке заявлений о прикреплении от файла экспорта');
		}
		return $response;
	}

	/**
	 * Экспорт заявлений о прикреплении
	 * @param array $data
	 * @return array
	 */
	function exportPersonCardAttach($data) {
		$params = array(
			'Lpu_aid' => $data['Lpu_aid'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate'],
		);

		$response = array(
			'success' => true,
			'xmllink' => null,
			'loglink' => null,
		);

		$query = "
			declare @date date = dbo.tzGetDate()
			declare @repdate date = :endDate
			select
				L.Lpu_f003mcod as CODE_MO,
				SMO.Orgsmo_f002smocod as SMO,
				convert(varchar(10), @date, 120) as DATE,
				year(@repdate) as YEAR,
				month(@repdate) as MONTH
			from
				(select 1 as a) t
				inner join v_Lpu L with(nolock) on L.Lpu_id = :Lpu_aid
				inner join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = :OrgSMO_id
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных для заголовка файла');
		}

		$MO = $resp['CODE_MO'];
		$SMO = $resp['SMO'];
		$YY = substr($data['year'], 2, 2);
		$MM = sprintf("%02d", $data['month']);
		$NN = (strlen($data['packageNumber']) < 2) ? sprintf('%02d', $data['packageNumber']) : $data['packageNumber'];

		$filename = "SZPM{$MO}S{$SMO}_{$YY}{$MM}{$NN}";
		
		$xmlfilename = $filename . '.xml';
		$logfilename = $filename . '_log.txt';

		$out_path = $this->getExportPersonCardAttachPath();
		if (!is_dir($out_path)) mkdir($out_path);

		$xmlfilepath = $out_path . "/" . $xmlfilename;
		$logfilepath = $out_path . "/" . $logfilename;
		
		if (is_file($xmlfilepath)) {
			return $this->createError('', 'В указанном отчетном периоде уже был сформирован пакет с данным номером. Укажите другой номер пакета.');
		}

		$query = "
			with MedSpecTree as (
				select
					t.MedSpec_id,
					t.MedSpec_pid,
					t.MedSpec_Code as MedSpec_rCode
				from fed.MedSpec t with(nolock)
				where t.MedSpec_pid is null
				union all
				select
					t.MedSpec_id,
					t.MedSpec_pid,
					t1.MedSpec_rCode
				from fed.MedSpec t
				inner join MedSpecTree t1 on t1.MedSpec_id = t.MedSpec_pid
				where t.MedSpec_pid is not null
			)
			select
				PCA.PersonCardAttach_id as ID_ATTACH,
				ROW_NUMBER() over (order by PCA.PersonCardAttach_id) as N_ZAP,
				case when PCAST.PersonCardAttachStatusType_Code = 4
					then 1 else 0
				end as PR_NOV,
				PS.Person_id as ID_PAC,
				rtrim(PS.Person_SurName) as FAM,
				rtrim(PS.Person_FirName) as IM,
				rtrim(PS.Person_SecName) as OT,
				Sex.Sex_fedid as W,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				PT.PolisType_CodeF008 as VPOLIS,
				case when PT.PolisType_CodeF008 = 3 
					then PS.Person_EdNum else P.Polis_Num 
				end as NPOLIS,
				nullif(P.Polis_Ser, '') as SPOLIS,
				SMO.Orgsmo_f002smocod as SMO,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 120) as DATEZ,
				2 as PRZ,
				null as REZ,
				null as DATEREZ,
				(
					left(MSF.Person_Snils, 3) + '-' + substring(MSF.Person_Snils, 4, 3) + '-' + 
					substring(MSF.Person_Snils, 7, 3) + ' ' + right(MSF.Person_Snils, 2)
				) as DOC_CODE,
				case when MS.MedSpec_rCode = '204'
					then 2 else 1
				end as DOC_POST,
				case when MSR.MedStaffRegion_endDate < PCA.PersonCardAttach_setDate
					then 0 else 1
				end as DOC_ACTUAL,
				null as COMENTZ,
				A.KLTown_id, 
				A.KLStreet_id, 
				A.Address_House 
			from
				v_PersonCardAttach PCA with(nolock)
				inner join v_Lpu L with(nolock) on L.Lpu_id = PCA.Lpu_id
				cross apply (
					select top 1 PS.*
					from v_Person_all PS with(nolock)
					where PS.Person_id = PCA.Person_id
					and cast(PS.PersonEvn_insDT as date) <= cast(PCA.PersonCardAttach_setDate as date)
					order by PS.PersonEvn_insDT desc
				) PS
				left join [Address] A on A.Address_id = PS.UAddress_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = P.PolisType_id
				left join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = P.OrgSMO_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join MedSpecTree MS with(nolock) on MS.MedSpec_id = MSO.MedSpec_id
				outer apply (
					select top 1
						MSR.*
					from
						v_MedStaffRegion MSR with(nolock)
					where
						MSR.MedStaffFact_id = MSF.MedStaffFact_id
						and MSR.LpuRegion_id = PCA.LpuRegion_id
						and MSR.MedStaffRegion_begDate <= PCA.PersonCardAttach_setDate
					order by
						MSR.MedStaffRegion_isMain desc,
						MSR.MedStaffRegion_begDate desc
				) MSR
				outer apply (
					select top 1 PCAS.PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS with(nolock)
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PCAS.PersonCardAttachStatus_setDate desc
				) PCAS
				left join v_PersonCardAttachStatusType PCAST with(nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
			where
				L.Lpu_id = :Lpu_aid
				and PCA.PersonCardAttach_setDate between :begDate and :endDate
				and PCAST.PersonCardAttachStatusType_Code in (1,4,5)
		";
		$pers_list = $this->queryResult($query, $params);
		if (!is_array($pers_list)) {
			return $this->createError('','Ошибка при получении даных заявлений');
		}
		if (count($pers_list) == 0) {
			return $this->createError('','Отсутствуют данные для экспорта');
		}
		
		$this->RegisterListLog_model->setpmUser_id($data['pmUser_id']);
		$this->RegisterListLog_model->setRegisterList_id(35);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		$this->RegisterListLog_model->setLpu_id($data['Lpu_aid']);
		$this->RegisterListLog_model->save();

		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "На отчетный период {$MM}.{$YY} найдено ".count($pers_list)." заявлений о прикреплении, доступных для экспорта", $RegisterListLog_id, $data['pmUser_id']);

		$zl = array_merge($resp, array(
			'VERSION' => '1.0',
			'FILENAME' => $filename,
			'ZAP' => count($pers_list),
			'LETTER' => null,
			'COMMENT' => null,
			'PERS' => $pers_list,
		));
		unset($pers_list);

		$this->beginTransaction();

		$this->load->model('Reg_model');
		$this->load->helper('Reg');

		try {
			$resp = $this->clearPersonCardAttachFile(array(
				'PersonCardAttach_ExpNameFile' => $filename,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$currDate = $this->currentDT->format('Y-m-d');
			$log = array();
			foreach ($zl['PERS'] as $idx => $pers) {
				$resp = $this->swUpdate('PersonCardAttach', array(
					'PersonCardAttach_id' => $pers['ID_ATTACH'],
					'PersonCardAttach_ExpNumRow' => $pers['N_ZAP'],
					'PersonCardAttach_ExpNameFile' => $filename,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				$resp = $this->savePersonCardAttachStatus(array(
					'PersonCardAttach_id' => $pers['ID_ATTACH'],
					'PersonCardAttachStatusType_Code' => 2,
					'PersonCardAttachStatus_setDate' => $currDate,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				// Определяем значение PRZ
				if ( !empty($pers['KLTown_id']) || !empty($pers['KLStreet_id']) ) {
					$RegionList = $this->_findAddressRegions($data['Lpu_aid'], $pers['KLTown_id'], $pers['KLStreet_id'], $pers['Address_House']);

					if (count($RegionList) > 0) {
						$zl['PERS'][$idx]['PRZ'] = 1;
					}
				}
			}

			if ($zl['ZAP'] > 0) {
				$this->load->library('parser');
				$tpl = 'export_xml/vologda_person_card_attach';
				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n" . $this->parser->parse_ext($tpl, $zl, true);
				file_put_contents($xmlfilepath, toAnsi($xml, true));
				$response['xmllink'] = $xmlfilepath;
			}
		
			foreach($log as $log_item) {
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, $log_item, $RegisterListLog_id, $data['pmUser_id']);
			}
			
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "Экспортировано: ".count($zl['PERS'])." заявлений о прикреплении. <a target=\"_blank\" download href=\"{$xmlfilepath}\">Скачать файл ".basename($xmlfilepath)."</a>", $RegisterListLog_id, $data['pmUser_id']);

			if (count($log) > 0) {
				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
			
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListLog_AllCount($zl['ZAP']);
			$this->RegisterListLog_model->setRegisterListLog_UploadCount(count($zl['PERS']));
			$this->RegisterListLog_model->setRegisterListResultType_id(1);
			$this->RegisterListLog_model->save();
			
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Импорт предварительного ответа по прикрепленному населению
	 * @param array $data
	 * @return array
	 */
	function importPersonCardAttachResponse($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'recievedcount' => 0,
		);
		
		$t006 = [
			2 => 'Прикрепление к МО уже выполнено',
			10 => 'Не застрахован в Вологодском филиале ОАО «Страховая компания «СОГАЗ-Мед»',
			11 => 'Некорректные персональные данные для указанного номера полиса',
			12 => 'Повторное заявление в течение отчетного года',
			13 => 'Дублирование записи в списке одной МО',
			14 => 'Дублирование записи в отчетном периоде в списках одной МО',
			15 => 'МО не может обслуживать ЗЛ такого возраста',
			16 => 'Нарушение сроков предоставления',
			17 => 'ЗЛ подал заявление в несколько МО в отчетный период',
			18 => 'Причина выбора: 1  – выбор МО по месту жительства; 2  – выбор по заявлению застрахованного',
			19 => 'при PRZ=3 требуется прикрепление к данной МО',
			20 => 'при PRZ=3 требуется заполнение СНИЛС врача',
		];

		$invalidFileMsg = 'Выбранный файл не является предварительным ответом от СМО по прикрепленному населению';

		if ($data['File']['type'] != 'text/xml') {
			return $this->createError('',$invalidFileMsg);
		}

		$xml = $this->objectToArray(simplexml_load_file($data['File']['tmp_name'], "SimpleXMLElement", LIBXML_NOERROR));
		if (!$xml) {
			return $this->createError('','Не удалось открыть файл');
		}

		if (!isset($xml['PERS'])) {
			$xml['PERS'] = array();
		} else if (!is_array($xml['PERS'])) {
			$xml['PERS'] = array($xml['PERS']);
		}

		$response['infilecount'] = count($xml['PERS']);

		$filename = $xml['ZGLV']['FILENAME'];
		$pattern  = '/^SZPS(\d+)M(\d+)_(\d{2})(\d{2})(\d+)$/';

		if (!preg_match($pattern, $filename, $matches)) {
			return $this->createError('', 'Наименование файла не соответствует установленному формату. Импорт невозможен.');
		}
		list(,$SMO,$MO,$YY,$MM,$NN) = $matches;

		$Lpu_id = $this->getFirstResultFromQuery("
			select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
		", array(
			'Lpu_f003mcod' => $MO
		), true);
		if ($Lpu_id === false) {
			return $this->createError('','Ошибка при получении идентификатора МО');
		}
		if (!empty($data['Lpu_aid']) && $Lpu_id != $data['Lpu_aid']) {
			return $this->createError('', 'Данный файл импорта предназначен другой МО. Импорт невозможен.');
		}
		
		$xmlfilename = "SZPM{$MO}S{$SMO}_{$YY}{$MM}{$NN}.xml";
		$out_path = $this->getExportPersonCardAttachPath();
		$xmlfilepath = $out_path . "/" . $xmlfilename;
		
		if (!is_file($xmlfilepath)) {
			return $this->createError('', 'Для данного файла не найден файл экспорта. Импорт невозможен.');
		}

		$log = array();
		$date = $this->currentDT->format('Y-m-d');

		$this->beginTransaction();
		
		$this->RegisterListLog_model->setpmUser_id($data['pmUser_id']);
		$this->RegisterListLog_model->setRegisterList_id(36);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		$this->RegisterListLog_model->setLpu_id($Lpu_id);
		$this->RegisterListLog_model->setRegisterListLog_NameFile($filename);
		$this->RegisterListLog_model->save();

		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "К обработке: ".count($xml['PERS'])." записей", $RegisterListLog_id, $data['pmUser_id']);
		
		$approved_cnt = 0;
		$rejected_cnt = 0;

		try {
			foreach ($xml['PERS'] as $item) {
				$PersonCardAttach_id = $this->getFirstResultFromQuery("
					select top 1
						PCA.PersonCardAttach_id
					from
						v_PersonCardAttach PCA with(nolock)
						cross apply (
							select top 1 PS.*
							from v_Person_all PS with(nolock)
							where PS.Person_id = PCA.Person_id
							and PS.PersonEvn_insDT <= PCA.PersonCardAttach_setDate
							order by PS.PersonEvn_insDT desc
						) PS
						left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
					where
						PCA.PersonCardAttach_setDate = :DATEZ
						and PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
						and PS.Person_BirthDay = :DR
						and (P.Polis_Num = :NPOLIS or PS.Person_EdNum = :NPOLIS)
					order by
						PCA.PersonCardAttach_insDT desc
				", $item, true);
				if ($PersonCardAttach_id === false) {
					throw new Exception('Ошибка при поиске заявления по ЗЛ');
				}

				if (empty($PersonCardAttach_id)) {
					$log[] = "Для записи по ЗЛ {$item['FAM']} {$item['IM']} {$item['OT']} {$item['DR']} {$item['NPOLIS']} не удалось найти заявление";
					continue;
				}

				$resp = $this->savePersonCardAttachStatus(array(
					'PersonCardAttach_id' => $PersonCardAttach_id,
					'PersonCardAttachStatusType_Code' => ($item['REZ'] == 1 ? 3 : 4),
					'PersonCardAttachStatus_setDate' => $date,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				
				if ($item['REZ'] != 1) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']} {$item['DR']} {$item['NPOLIS']} Отказ в прикреплении по причине: {$t006[$item['REZ']]}";
					$rejected_cnt++;
				} else {
					$approved_cnt++;
				}

				$response['recievedcount']++;
			}
		
			foreach($log as $log_item) {
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, $log_item, $RegisterListLog_id, $data['pmUser_id']);
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_log.txt';

				$out_path = $this->getExportPersonCardAttachPath('response');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
			
			$msg = "Обработано {$response['recievedcount']} ответов СМО по заявлениям. Из них: {$approved_cnt} одобрено, {$rejected_cnt} отказано. ";
			if (!empty($response['loglink'])) {
				$msg .= " <a target=\"_blank\" download href=\"{$logfilepath}\">Скачать файл {$logfilename}</a>";
			}
			
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, $msg, $RegisterListLog_id, $data['pmUser_id']);
			
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListLog_AllCount(count($xml['PERS']));
			$this->RegisterListLog_model->setRegisterListLog_UploadCount($response['recievedcount']);
			$this->RegisterListLog_model->setRegisterListResultType_id(1);
			$this->RegisterListLog_model->save();
			
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Импорт сведений о ЗЛ, открепленных от МО
	 * @param array $data
	 * @return array
	 */
	function importPersonCardDetach($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'recievedcount' => 0,
		);

		$invalidFileMsg = 'Выбранный файл не является файлом со сведениями о ЗЛ, открепленных от МО';

		if ($data['File']['type'] != 'text/xml') {
			return $this->createError('',$invalidFileMsg);
		}

		$xml = $this->objectToArray(simplexml_load_file($data['File']['tmp_name'], "SimpleXMLElement", LIBXML_NOERROR));
		if (!$xml) {
			return $this->createError('','Не удалось открыть файл');
		}

		if (!isset($xml['PERS'])) {
			$xml['PERS'] = array();
		} else if (!is_array($xml['PERS'])) {
			$xml['PERS'] = array($xml['PERS']);
		}

		$response['infilecount'] = count($xml['PERS']);

		$filename = $xml['ZGLV']['FILENAME'];
		$pattern  = '/^OZPS(\d+)M(\d+)_(\d{2})(\d{2})$/';

		if (!preg_match($pattern, $filename, $matches)) {
			return $this->createError('', 'Наименование файла не соответствует установленному формату. Импорт невозможен.');
		}
		list(,$SMO,$MO,$YY,$MM) = $matches;

		$Lpu_id = $this->getFirstResultFromQuery("
			select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
		", array(
			'Lpu_f003mcod' => $MO
		), true);
		if ($Lpu_id === false) {
			return $this->createError('','Ошибка при получении идентификатора МО');
		}
		if (!empty($data['Lpu_aid']) && $Lpu_id != $data['Lpu_aid']) {
			return $this->createError('', 'Данный файл импорта предназначен другой МО. Импорт невозможен.');
		}

		$log = array();
		$date = sprintf('%d-%02d-01', $xml['ZGLV']['YEAR'], $xml['ZGLV']['MONTH']);

		$this->beginTransaction();
		
		$this->RegisterListLog_model->setpmUser_id($data['pmUser_id']);
		$this->RegisterListLog_model->setRegisterList_id(37);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		$this->RegisterListLog_model->setLpu_id($Lpu_id);
		$this->RegisterListLog_model->setRegisterListLog_NameFile($filename);
		$this->RegisterListLog_model->save();

		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "К обработке: ".count($xml['PERS'])." записей", $RegisterListLog_id, $data['pmUser_id']);
		
		$notident_cnt = 0;

		try {
			foreach ($xml['PERS'] as $item) {
				$Person_ids = $this->queryList("
					select
						PS.Person_id
					from
						v_PersonState PS with(nolock)
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
						and PS.Person_BirthDay = :DR
						and (PS.Polis_Num = :NPOLIS or PS.Person_EdNum = :NPOLIS)
				", $item, true);
				if (!is_array($Person_ids)) {
					throw new Exception('Ошибка при поиске человека');
				}

				if (count($Person_ids) != 1) {
					$log[] = "Не удалось идентифицировать человека {$item['FAM']} {$item['IM']} {$item['OT']} {$item['DR']} {$item['NPOLIS']}";
					$notident_cnt++;
					continue;
				}

				$resp = $this->closePersonCardByImport(array(
					'Lpu_id' => $Lpu_id,
					'Person_id' => $Person_ids[0],
					'date' => $date,
					'prz' => $item['PRZ'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				if (!empty($resp[0]['PersonCard_id'])) {
					$log[] = "N_ZAP={$item['N_ZAP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['NPOLIS']}" .
						" был откреплен {$resp[0]['PersonCard_endDate']} по причине \"{$resp[0]['PRZ_Name']}\"";
				} else {
					$log[] = "N_ZAP={$item['N_ZAP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['NPOLIS']}" .
						". Прикрепление не соответствует МО загрузки файла";
				}

				$response['recievedcount']++;
			}
		
			foreach($log as $log_item) {
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, $log_item, $RegisterListLog_id, $data['pmUser_id']);
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_log.txt';

				$out_path = $this->getExportPersonCardAttachPath('response');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
			
			$msg = "Откреплено: {$response['recievedcount']} человек. Не идентифицировано: {$notident_cnt} человек. ";
			if (!empty($response['loglink'])) {
				$msg .= " <a target=\"_blank\" download href=\"{$logfilepath}\">Скачать файл {$logfilename}</a>";
			}
			
			RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, $msg, $RegisterListLog_id, $data['pmUser_id']);
			
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListLog_AllCount(count($xml['PERS']));
			$this->RegisterListLog_model->setRegisterListLog_UploadCount($response['recievedcount']);
			$this->RegisterListLog_model->setRegisterListResultType_id(1);
			$this->RegisterListLog_model->save();
			
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Открепление при импорте сведений о ЗЛ
	 * @param array $data
	 * @return array
	 */
	function closePersonCardByImport($data) {
		$response = array(
			'success' => true,
			'PersonCard_id' => null,
			'PersonCard_endDate' => null,
			'PRZ_Name' => null,
			'Error_Code' => null,
			'Error_Msg' => null,
		);

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'date' => $data['date'],
			'prz' => $data['prz'],
		);
		$query = "
			declare
				@date date = :date,
				@prz int = :prz;
			with PRZ as (
				select 1 as PRZ_Code, 'Не является застрахованным' as PRZ_Name, 8 as CardCloseCause_Code
				union select 2 as PRZ_Code, 'Умерший' as PRZ_Name, 2 as CardCloseCause_Code
				union select 3 as PRZ_Code, 'Прикреплен к другой МО' as PRZ_Name, 1 as CardCloseCause_Code
				union select 4 as PRZ_Code, 'Смена МО по возрастному принципу' as PRZ_Name, 3 as CardCloseCause_Code
				union select 5 as PRZ_Code, 'Изменение территориального деления' as PRZ_Name, 8 as CardCloseCause_Code
			)
			select top 1
				PC.PersonCard_id,
				PC.Person_id,
				PC.Lpu_id,
				PC.LpuRegion_id,
				PC.LpuAttachType_id,
				PC.PersonCard_Code,
				convert(varchar(10), PC.PersonCard_begDate, 120) as PersonCard_begDate,
				convert(varchar(10), dateadd(day, -1, @date), 120) as PersonCard_endDate,
				CCC.CardCloseCause_id,
				PRZ.PRZ_Name,
				PC.PersonCard_IsAttachCondit,
				PC.OrgSMO_id,
				PC.PersonCardAttach_id,
				PC.LpuRegion_fapid,
				PC.LpuRegionType_id,
				PC.MedStaffFact_id
			from
				v_PersonCard PC with(nolock)
				left join v_LpuAttachType LAT with(nolock) on LAT.LpuAttachType_id = PC.LpuAttachType_id
				left join PRZ with(nolock) on PRZ.PRZ_Code = :prz
				left join v_CardCloseCause CCC with(nolock) on CCC.CardCloseCause_Code = PRZ.CardCloseCause_Code
			where
				PC.Lpu_id = :Lpu_id
				and PC.Person_id = :Person_id
				and PC.PersonCard_begDate < :date
				and PC.PersonCard_endDate is null
				and LAT.LpuAttachType_SysNick = 'main'
			order by
				PC.PersonCard_begDate desc
		";
		$PersonCard = $this->getFirstRowFromQuery($query, $params, true);
		if ($PersonCard === false) {
			return $this->createError('','Ошибка при поиске прикрепления человека');
		}

		if (empty($PersonCard)) {
			return array($response);
		}

		$params = array_merge($PersonCard, array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCard_id;
			exec p_PersonCard_upd
				@PersonCard_id = @Res output,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonCard_begDate = :PersonCard_begDate,
				@PersonCard_endDate = :PersonCard_endDate,
				@PersonCard_Code = :PersonCard_Code,
				@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
				@OrgSMO_id = :OrgSMO_id,
				@LpuRegion_id = :LpuRegion_id,
				@LpuRegion_fapid = :LpuRegion_fapid,
				@LpuAttachType_id = :LpuAttachType_id,
				@CardCloseCause_id = :CardCloseCause_id,
				@PersonCardAttach_id = :PersonCardAttach_id,
				@LpuRegionType_id = :LpuRegionType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при закрытии прикрепления');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$response['PersonCard_id'] = $resp[0]['PersonCard_id'];
		$response['PersonCard_endDate'] = $PersonCard['PersonCard_endDate'];
		$response['PRZ_Name'] = $PersonCard['PRZ_Name'];

		return array($response);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @desctiption Импорт регистра прикрепленного населения
	 * @throws Exception
	 */
	function importPersonCardRegister($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'createdcount' => 0,
		);

		$invalidFileMsg = 'Структура выбранного файла не соответствует структуре файла с регистром прикрепленного населения';

		$struct = array(
			'N_PP' => 'int',
			'FAM' => 'string',
			'IM' => 'string',
			'OT' => 'string',
			'DR' => 'date',
			'W' => 'int',
			'T_POL' => 'int',
			'N_POL' => 'string',
			'MCOD' => 'string',
			'DATA_ZVL' => 'date',
			'DOC_CODE' => 'string'
		);

		$convertRecord = function($record) use($struct) {
			$_record = array();
			foreach($record as $key => $value) {
				$type = isset($struct[$key])?$struct[$key]:'string';
				switch(true) {
					case ($type == 'int'):
						$_record[$key] = (int)$value;
						break;
					case ($type == 'date'):
						$_record[$key] = date_create(trim($value))->format('Y-m-d');
						if ($_record[$key] == '2049-12-31') $_record[$key] = null;
						break;
					case ($key == 'DOC_CODE'):
						$_record[$key] = str_replace(array('-',' '), '', trim($value));
						break;
					default:
						ConvertFromWin866ToUtf8($value);
						$_record[$key] = trim($value);
						break;
				}
			}
			return $_record;
		};

		$filenameparts = explode('.', $data['File']['name']);
		$filename = $filenameparts[0];
		$fileext = $filenameparts[1];

		if (!in_array($fileext, ['dbf', 'xml'])) {
			return $this->createError('',$invalidFileMsg);
		}
		
		if ($fileext == 'xml') {
			
			$xml = $this->objectToArray(simplexml_load_file($data['File']['tmp_name'], "SimpleXMLElement", LIBXML_NOERROR));
			if (!$xml) {
				return $this->createError('','Не удалось открыть файл');
			}
			
			if (!isset($xml['PERS'])) {
				$xml['PERS'] = array();
			} else if (!is_array($xml['PERS'])) {
				$xml['PERS'] = array($xml['PERS']);
			}
			
			$count = count($xml['PERS']);
			if ($count == 0) {
				return array($response);
			}
			$response['infilecount'] = $count;
			
			$Lpu_f003mcod = false;
			foreach ($xml['PERS'] as $item) {
				if (empty($item['CODE_MO'])) {
					return $this->createError('', $invalidFileMsg);
				}
				if ($Lpu_f003mcod != false && $item['CODE_MO'] != $Lpu_f003mcod) {
					return $this->createError('', 'Импортируемый файл содержит данные о прикреплении к разным МО. Импорт невозможен.');
				}
				$Lpu_f003mcod = $item['CODE_MO'] ;
			}
			
			$Lpu_id = $this->getFirstResultFromQuery("
				select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
			", array(
				'Lpu_f003mcod' => $Lpu_f003mcod
			));
			
			if ($Lpu_id === false) {
				return $this->createError('','Импортируемый файл содержит данные о прикреплении к МО, отсутствующей в Промед. Импорт невозможен.');
			}
			
		} else {
			
			$dbf = dbase_open($data['File']['tmp_name'], 0);
			if (!$dbf) {
				return $this->createError('','Не удалось открыть файл');
			}

			$dbf_header = dbase_get_header_info($dbf);
			if (!$dbf_header) {
				return $this->createError('','Не удалось прочитать файл');
			}

			$structCountdown = count($struct);
			foreach($dbf_header as $field) {
				if (isset($struct[$field['name']])) {
					$structCountdown--;
				}
			}
			if ($structCountdown > 0) {
				return $this->createError('',$invalidFileMsg);
			}

			$count = dbase_numrecords($dbf);
			if ($count == 0) {
				return array($response);
			}
			$response['infilecount'] = $count;
			
			$Lpu_OGRN = false;
			for ($i = 1; $i <= $count; $i++) {
				$item = $convertRecord(dbase_get_record_with_names($dbf, $i));
				if ($Lpu_OGRN != false && $item['MCOD'] != $Lpu_OGRN) {
					return $this->createError('', 'Импортируемый файл содержит данные о прикреплении к разным МО. Импорт невозможен.');
				}
				$Lpu_OGRN = $item['MCOD'] ;
			}
			
			$Lpu_id = $this->getFirstResultFromQuery("
				select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_OGRN = :Lpu_OGRN
			", array(
				'Lpu_OGRN' => $Lpu_OGRN
			));
			
			if ($Lpu_id === false) {
				return $this->createError('','Импортируемый файл содержит данные о прикреплении к МО, отсутствующей в Промед. Импорт невозможен.');
			}
			
		}
		
		if (!empty($data['Lpu_aid']) && $Lpu_id != $data['Lpu_aid']) {
			return $this->createError('', 'В загружаемом файле представлены записи, не относящиеся к вашей МО. Импорт невозможен. ');
		}

		$this->sendImportResponse();

		$log = array();
		$date = $this->currentDT->format('Y-m-01');
		$currDate = $this->currentDT->format('Y-m-d');
		
		$this->RegisterListLog_model->setpmUser_id($data['pmUser_id']);
		$this->RegisterListLog_model->setRegisterList_id(34);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		$this->RegisterListLog_model->setRegisterListLog_AllCount($count);
		$this->RegisterListLog_model->setLpu_id($Lpu_id);
		$this->RegisterListLog_model->setRegisterListLog_NameFile($data['File']['name']);
		$this->RegisterListLog_model->save();

		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "К обработке: {$count} записей", $RegisterListLog_id, $data['pmUser_id']);
		
		$notident_cnt = 0;
		$error_cnt = 0;

		for ($i = 1; $i <= $count; $i++) {

			try {

				if ($fileext == 'xml') {
					$item = $xml['PERS'][($i-1)];
					$PersonCard_begDate = !empty($item['DATEZ'])?$item['DATEZ']:$date;
					$item['N_PP'] = $item['N_ZAP'];
					$item['N_POL'] = $item['NPOLIS'];
					$item['DOC_CODE'] = str_replace(array('-',' '), '', trim($item['DOC_CODE']));
				} else {
					$item = $convertRecord(dbase_get_record_with_names($dbf, $i));
					$PersonCard_begDate = !empty($item['DATA_ZVL'])?$item['DATA_ZVL']:$date;
				}

				//Поиск человека
				$PersonList = $this->queryResult("
					declare
						@date date = :PersonCard_begDate;
					select
						PS.Person_id,
						dbo.Age2(PS.Person_BirthDay, @date) as Person_Age
					from
						v_PersonState PS with(nolock)
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
						and PS.Person_BirthDay = :DR
						and (PS.Polis_Num = :N_POL or PS.Person_EdNum = :N_POL)
				", array_merge($item, array(
					'PersonCard_begDate' => $PersonCard_begDate
				)));
				if (!is_array($PersonList)) {
					throw new Exception('Ошибка при поиске человека');
				}
				if (count($PersonList) != 1) {
					$log[] = "Не удалось идентифицировать человека" .
						" N_PP={$item['N_PP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']}";
					$notident_cnt++;
					$log_item = end($log);
					RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, $log_item, $RegisterListLog_id, $data['pmUser_id']);
					continue;
				}

				//Поиск действующего прикрепления
				$PersonCard = $this->getFirstRowFromQuery("
					select top 1
						PC.PersonCard_id
					from
						v_PersonCard PC with(nolock)
						inner join v_LpuAttachType LAT with(nolock) on LAT.LpuAttachType_id = PC.LpuAttachType_id
					where
						PC.Lpu_id = :Lpu_id
						and PC.Person_id = :Person_id
						and PC.PersonCard_endDate is null
						and LAT.LpuAttachType_SysNick = 'main'
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'Person_id' => $PersonList[0]['Person_id'],
				), true);
				if ($PersonCard === false) {
					throw new Exception('Ошибка при поиске прикрепления');
				}
				if ($PersonCard) {
					$this->setDocument($PersonList[0]['Person_id'], $data['Server_id'], $item);
					$this->setAddress($PersonList[0]['Person_id'], $data['Server_id'], $item);
					$log[] = "Пациент уже прикреплен" .
						" N_PP={$item['N_PP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']}";
					$log_item = end($log);
					RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, $log_item, $RegisterListLog_id, $data['pmUser_id']);
					continue;
				}

				//Если нет действующего прикрепления то выполняется поиск заявления для создания нового прикрепления
				$params = array(
					'Person_id' => $PersonList[0]['Person_id'],
					'Lpu_id' => $data['Lpu_id'],
					'PersonCard_begDate' => $PersonCard_begDate,
				);
				$query = "
					declare
						@Person_id bigint = :Person_id,
						@Lpu_id bigint = :Lpu_id,
						@PersonCard_begDate date = :PersonCard_begDate;
					select top 1
						'add' as action,
						null as PersonCard_id,
						PrevPC.PersonCard_id as PrevPersonCard_id,
						null as PersonCardAttach_id,
						@Lpu_id as Lpu_id,
						@Person_id as Person_id,
						LAT.LpuAttachType_id,
						convert(varchar(10), @PersonCard_begDate, 120) as PersonCard_begDate,
						null as LpuRegion_id,
						null as LpuRegion_Name,
						null as LpuRegionType_id,
						null as MedStaffFact_id,
						null as MedPersonal_Fio
					from
						v_LpuAttachType LAT with(nolock)
						outer apply(
							select top 1 
								PC.PersonCard_id
							from 
								v_PersonCard PC with(nolock)
							where 
								PC.Person_id = @Person_id
								and PC.LpuAttachType_id = LAT.LpuAttachType_id
								and PC.PersonCard_endDate is null
							order by 
								PC.PersonCard_begDate desc
						) PrevPC
					where
						LAT.LpuAttachType_SysNick = 'main'
				";
				$PersonCard = $this->getFirstRowFromQuery($query, $params);
				if ($PersonCard === false) {
					throw new Exception('Ошибка при определении данных для прикрепления');
				}

				$resp = $this->findOrCreatePersonCardCode(array_merge($PersonCard, array(
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				)));
				if (!$this->isSuccessful($resp)) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']} не был прикреплен: " .
						"{$resp[0]['Error_Msg']}";
					$error_cnt++;
				}

				$PersonCardResp = $this->savePersonCard(array_merge($PersonCard, array(
					'isAutoImport' => true,
					'PersonCard_Code' => $resp[0]['PersonCard_Code'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				)));
				if (!is_array($PersonCardResp)) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']} не был прикреплен: " .
						"Ошибка при создании прикрепления";
					$error_cnt++;
				}
				if (!$this->isSuccessful($PersonCardResp)) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']} не был прикреплен: " .
						"{$PersonCardResp[0]['Error_Msg']}";
					$error_cnt++;
				}

				if (!empty($PersonCardResp[0]['PersonCard_id'])) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']} был прикреплен {$PersonCard['PersonCard_begDate']} к МО";

					$response['createdcount']++;

					$this->setDocument($PersonList[0]['Person_id'], $data['Server_id'], $item);
					$this->setAddress($PersonList[0]['Person_id'], $data['Server_id'], $item);
				}

				$log_item = end($log);
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, $log_item, $RegisterListLog_id, $data['pmUser_id']);
				$this->RegisterListLog_model->setRegisterListLog_UploadCount($response['createdcount']);
				$this->RegisterListLog_model->save();

			} catch(Exception $e) {
				$log[] = $e->getMessage();
				$error_cnt++;
				$log_item = end($log);
				RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, $log_item, $RegisterListLog_id, $data['pmUser_id']);
			}
		}

		if (count($log) > 0) {
			$logfilename = translit($filename) . '_log.txt';

			$out_path = $this->getExportPersonCardAttachPath('register');
			if (!is_dir($out_path)) mkdir($out_path);

			$logfilepath = $out_path . "/" . $logfilename;

			file_put_contents($logfilepath, implode("\n\n", $log));
			$response['loglink'] = $logfilepath;
		}

		$msg = "Прикреплено: {$response['createdcount']} человек. Не идентифицировано: {$notident_cnt} человек. Ошибка прикрепления: {$error_cnt} записей. ";
		if (!empty($response['loglink'])) {
			$msg .= " <a target=\"_blank\" download href=\"{$logfilepath}\">Скачать файл {$logfilename}</a>";
		}
			
		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, $msg, $RegisterListLog_id, $data['pmUser_id']);

		$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
		$this->RegisterListLog_model->setRegisterListLog_UploadCount($response['createdcount']);
		$this->RegisterListLog_model->setRegisterListResultType_id(1);
		$this->RegisterListLog_model->save();

		if ($fileext == 'dbf') dbase_close($dbf);

		$this->load->model('Messages_model');
		$text = "Файл {$data['File']['name']} обработан. {$msg} ";
		$noticeData = array(
			'autotype' => 3,
			'pmUser_id' => $data['pmUser_id'],
			'User_rid' => $data['pmUser_id'],
			'type' => 1,
			'title' => 'Импорт регистра прикрепленного населения завершен',
			'text' => $text
		);
		$this->Messages_model->autoMessage($noticeData);

		return true;
	}

	/**
	 * Получение DocumentType_id по коду
	 * @param $DocumentType_Code
	 * @return int|null
	 */
	function getDocumentTypeId($DocumentType_Code) {
		if (empty($DocumentType_Code)) return null;
		return $this->getFirstResultFromQuery("
			select DocumentType_id from v_DocumentType (nolock) where DocumentType_Code = :DocumentType_Code
		", array(
			'DocumentType_Code' => $DocumentType_Code
		), true);
	}

	/**
	 * Получение OrgDep_id по наименованию
	 * @param $Org_Name
	 * @return int|null
	 */
	function getOrgDepId($Org_Name) {
		if (empty($Org_Name)) return null;
		return $this->getFirstResultFromQuery("
			select top 1 OrgDep_id from v_OrgDep (nolock) where Org_Name = :Org_Name or Org_Nick = :Org_Name
		", array(
			'Org_Name' => $Org_Name
		), true);
	}

	/**
	 * Сохранение документа из РПН
	 * @param $person_id
	 * @param $server_id
	 * @param array $data
	 * @return void
	 */
	private function setDocument($person_id, $server_id, $data) {
		// Сохраняем, только если есть полные данные по документу
		// Иначе у нас может быть актуальнее
		if (empty($data['DOCNUM'])) return;
		if ($data['DOCNUM'] == '0001-01-01') return;
		if ($data['DOCORG'] == 'НЕИЗВЕСТНО') return;

		$old = $this->getFirstRowFromQuery("
			select top 1
				p.Document_Ser,
				p.Document_Num,
				convert(varchar(10), d.Document_begDate, 120) as Document_begDate,
				d.OrgDep_id
			from v_PersonState p (nolock)
				inner join v_Document d (nolock) on p.Document_id = d.Document_id
			where p.Person_id = :Person_id
		", array('Person_id' => $person_id));

		if (
			empty($old) || // у нас документа нет вообще
			empty($old['Document_begDate']) || // у нас в документе нет даты
			empty($old['OrgDep_id']) || // у нас в документе нет кем выдан
			$data['DOCDATE'] > $old['Document_begDate'] // у них более свежий документ
		) {
			$this->execCommonSP('p_PersonDocument_ins', [
				'Server_id' => $server_id,
				'Person_id' => $person_id,
				'DocumentType_id' => $this->getDocumentTypeId($data['DOCTYPE']),
				'OrgDep_id' => $this->getOrgDepId($data['DOCORG']),
				'Document_Ser' => $data['DOCSER'],
				'Document_Num' => $data['DOCNUM'],
				'Document_begDate' => $data['DOCDATE'],
				'pmUser_id' => 1,
			]);
		}
	}

	/**
	 * Сохранение адреса из РПН
	 * @param $person_id
	 * @param $server_id
	 * @param array $data
	 * @return void
	 */
	private function setAddress($person_id, $server_id, $data) {
		if (empty($data['FIAS_AOID'])) return;

		$address = $this->getFirstRowFromQuery("
			select 
				:Server_id as Server_id,
				:Person_id as Person_id,
				klc.KLCountry_id,
				null as KLAreaType_id,
				klc.KLRgn_id,
				klc.KLSubRgn_id,
				klc.KLCity_id,
				klc.KLTown_id,
				kls.KLStreet_id,
				kls.KLAdr_Index as Address_Zip,
				:Address_House as Address_House,
				:Address_Corpus as Address_Corpus,
				:Address_Flat as Address_Flat,
				null as Address_Address,
				1 as pmUser_id
			from v_KLStreet kls (nolock)
				inner join v_KladrCache klc (nolock) on klc.KLStreet_id = kls.KLStreet_id
			where 
				kls.KLStreet_AOID = :KLStreet_AOID
		", [
			'Server_id' => $server_id,
			'Person_id' => $person_id,
			'KLStreet_AOID' => $data['FIAS_AOID'],
			'Address_House' => $data['DOM'],
			'Address_Corpus' => $data['KOR'],
			'Address_Flat' => $data['KV'],
		]);

		if ($address == false) return;

		$this->execCommonSP('p_PersonUAddress_ins', $address);
	}

	/**
	 * Поиск или создание амбулаторной карты
	 * @param array $data
	 * @return array
	 */
	function findOrCreatePersonCardCode($data) {
		$response = array(
			'success' => true,
			'PersonCard_Code' => null,
			'Error_Msg' => null
		);

		$query = "
			select top 1 PersonAmbulatCard_Num
			from v_PersonAmbulatCard
			where Person_id = :Person_id
			order by PersonAmbulatCard_id desc
		";
		$response['PersonCard_Code'] = $this->getFirstResultFromQuery($query, $data, true);
		if ($response['PersonCard_Code'] === false) {
			return $this->createError('','Ошибка при поиске амбулаторной карты');
		}
		if (!empty($response['PersonCard_Code'])) {
			return array($response);
		}

		$resp = $this->getPersonCardCode($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$response['PersonCard_Code'] = $resp[0]['PersonCard_Code'];

		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonAmbulatCard_Num' => $response['PersonCard_Code'],
			'PersonAmbulatCard_CloseCause' => null,
			'PersonAmbulatCard_begDate' => $data['PersonCard_begDate'],
			'PersonAmbulatCard_endDate' => null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonAmbulatCard_ins
				@Server_id = :Server_id,
				@PersonAmbulatCard_id = @Res output,
				@Person_id = :Person_id,
				@PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
				@Lpu_id = :Lpu_id,
				@PersonAmbulatCard_CloseCause = :PersonAmbulatCard_CloseCause,
				@PersonAmbulatCard_begDate = :PersonAmbulatCard_begDate,
				@PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при создании амбулаторной карты');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$params = array(
			'PersonAmbulatCard_id' => $resp[0]['PersonAmbulatCard_id'],
			'AmbulatCardLocatType_id' => 1,
			'MedStaffFact_id' => null,
			'PersonAmbulatCardLocat_begDate' => $data['PersonCard_begDate'],
			'PersonAmbulatCardLocat_Desc' => null,
			'PersonAmbulatCardLocat_OtherLocat' => null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonAmbulatCardLocat_ins
				@Server_id = :Server_id,
				@PersonAmbulatCardLocat_id = @Res output,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
				@PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
				@PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при создании записи движения амбулаторной карты');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array($response);
	}

	/**
	 * @param $sKLTown
	 * @param $sStreet
	 * @param $sHouse
	 * @return array
	 * @description Поиск участков по заданному населенному пункту, улице, номеру дома
	 */
	protected function _findAddressRegions($Lpu_id, $KLTown_id = null, $KLStreet_id = null, $Address_House = null) {
		$Regions = [];
		$params = [
			'Lpu_id' => $Lpu_id,
			'KLTown_id' => $KLTown_id,
			'KLStreet_id' => $KLStreet_id,
		];

		$resp = $this->queryResult("
			select
				LRS.LpuRegionStreet_HouseSet,
				LRS.LpuRegion_id
			from LpuRegionStreet LRS (nolock)
				inner join v_LpuRegion LR on LR.LpuRegion_id = LRS.LpuRegion_id 
			where
				LR.Lpu_id = :Lpu_id  
				and (:KLStreet_id is null or LRS.KLStreet_id = :KLStreet_id)
				and (:KLTown_id is null or LRS.KLTown_id = :KLTown_id)
		", $params);

		if ( $resp !== false && is_array($resp) ) {
			foreach ( $resp as $row ) {
				if ( (empty($Address_House) || HouseMatchRange(trim($Address_House), trim($row['LpuRegionStreet_HouseSet']))) && !in_array($row['LpuRegion_id'], $Regions) ) {
					$Regions[] = $row['LpuRegion_id'];
				}
			}
		}

		return $Regions;
	}

	/**
	 * Разрыв соединения c клиентом после запуска импорта
	 */
	function sendImportResponse() {
		ignore_user_abort(true);

		if (function_exists('fastcgi_finish_request')) {
			echo json_encode(array("success" => "true", 'background' => 'true'));
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		} else {
			ob_start();
			echo json_encode(array("success" => "true", 'background' => 'true'));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
	}
}