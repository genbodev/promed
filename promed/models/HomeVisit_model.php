<?php
/**
 * HomeVisit - модель для вызовов врачей на дом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      20.09.2013
 */

class HomeVisit_model extends swModel 
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Получение списка вызовов на дом на дату по ЛПУ
	 */
	function getHomeVisitList($data)
	{
		$params = array();
		$filter = "(1=1)";
		
		if(isset($data['begDate']) && !empty($data['begDate'])){
			$filter .= " and cast(hv.HomeVisit_setDT as date) >= cast(:begDate as date)";
			$params['begDate'] = $data['begDate'];
		}

		if(isset($data['endDate']) && !empty($data['endDate'])){
			$filter .= " and cast(hv.HomeVisit_setDT as date) <= cast(:endDate as date)";
			$params['endDate'] = $data['endDate'];
		}

		if (!empty($data['Lpu_id']) && empty($data['allLpu']))
		{
			$filter .= " and hv.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
			$lpuJoin = " left join v_Lpu l (nolock) on hv.Lpu_id = l.Lpu_id ";
		} else {
			$lpuJoin = " inner join v_Lpu l (nolock) on hv.Lpu_id = l.Lpu_id ";
		}
		if (!empty($data['Person_Surname'])) 
		{
			$filter .= " and p.Person_SurName like (:Person_Surname+'%')";
			$params['Person_Surname'] = rtrim($data['Person_Surname']);
		}
		if (!empty($data['Person_Firname'])) 
		{
			$filter .= " and p.Person_FirName like (:Person_Firname+'%')";
			$params['Person_Firname'] = rtrim($data['Person_Firname']);
		}
		if (!empty($data['Person_Secname'])) 
		{
			$filter .= " and p.Person_SecName like (:Person_Secname+'%')";
			$params['Person_Secname'] = rtrim($data['Person_Secname']);
		}
		if (!empty($data['Person_BirthDay'])) 
		{
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['HomeVisitStatus_id'])) 
		{
			if ($data['HomeVisitStatus_id'] == -1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is not null";
			} elseif ($data['HomeVisitStatus_id'] == -2) {
				$filter .= " and hv.HomeVisitStatus_id is null";
			} elseif($data['HomeVisitStatus_id'] == 1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is null";
			}
			else {
				$filter .= " and hv.HomeVisitStatus_id = :HomeVisitStatus_id";
				$params['HomeVisitStatus_id'] = $data['HomeVisitStatus_id'];
			}
		}
		if (!empty($data['HomeVisitCallType_id'])) 
		{
			$filter .= " and hv.HomeVisitCallType_id = :HomeVisitCallType_id";
			$params['HomeVisitCallType_id'] = $data['HomeVisitCallType_id'];
		}
		if (!empty($data['HomeVisit_setTimeFrom'])) 
		{
			$filter .= " and cast(hv.HomeVisit_setDT as time) >= cast(:HomeVisit_setTimeFrom as time)";
			$params['HomeVisit_setTimeFrom'] = $data['HomeVisit_setTimeFrom'];
		}
		if (!empty($data['HomeVisit_setTimeTo'])) 
		{
			$filter .= " and cast(hv.HomeVisit_setDT as time) <= cast(:HomeVisit_setTimeTo as time)";
			$params['HomeVisit_setTimeTo'] = $data['HomeVisit_setTimeTo'];
		}


		if ( ! empty($data['MedStaffFact_id']))
		{

			$tmpFilterMedPersonal_id = '';
			if ( ! empty($data['MedPersonal_id']) && $data['MedPersonal_id'] != -1) {
				$tmpFilterMedPersonal_id = " OR (msf.MedStaffFact_id IS NULL and mp.MedPersonal_id = :MedPersonal_id)";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}

			$filter .= " and ( msf.MedStaffFact_id = :MedStaffFact_id $tmpFilterMedPersonal_id)";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		else if ( ! empty($data['MedPersonal_id'])) {
			if ($data['MedPersonal_id'] == -1) {
				$filter .= " and mp.MedPersonal_id is null";
			} else {
				$filter .= " and mp.MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
		}


		/*if (!empty($data['LpuRegion_id'])) {
			$params['LpuRegion_id'] = $data['LpuRegion_id'];
			$filter .= " and HV.LpuRegion_id = :LpuRegion_id";
		}*/

		//Подразделение - врач из вызова на дом должен быть с выбранного участка
		if (!empty($data['LpuRegion_id']) || !empty($data['LpuBuilding_id'])) {
			$exists_filter = "";

			if (!empty($data['LpuRegion_id'])) {
				$params['LpuRegion_id'] = $data['LpuRegion_id'];
				//$filter .= " and isnull(lr.LpuRegion_id, hv.LpuRegion_cid) = :LpuRegion_id";
				$filter .= " and hv.LpuRegion_cid = :LpuRegion_id";
				$exists_filter .= " and t3.LpuSection_id = lr.LpuSection_id";
			}
			if (!empty($data['LpuBuilding_id'])) {
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$exists_filter .= " and t3.LpuBuilding_id = :LpuBuilding_id";
			}

			$main_exists_filter = "exists (
				select top 1
					t1.MedStaffRegion_id
				from
					v_MedStaffRegion t1 with (nolock)
					inner join v_LpuRegion t2 with (nolock) on t2.LpuRegion_id = t1.LpuRegion_id
					inner join v_LpuSection t3 with(nolock) on t3.LpuSection_id = t2.LpuSection_id
					left join v_MedStaffFact t4 with (nolock) on t4.MedStaffFact_id = t1.MedStaffFact_id
				where
					ISNULL(t1.MedPersonal_id, t4.MedPersonal_id) = hv.MedPersonal_id
					and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate > dbo.tzGetDate())
					{$exists_filter}
			)";

			if (!empty($data['LpuBuilding_id'])) {
				$filter .= " and {$main_exists_filter}";
			} else {
				$filter .= " and (hv.MedPersonal_id is null or lr.LpuSection_id is null or ({$main_exists_filter}))";
			}
		}

		if (!empty($data['LpuRegion_cid'])) {
			$params['LpuRegion_cid'] = $data['LpuRegion_cid'];
			$filter .= " and hv.LpuRegion_cid = :LpuRegion_cid ";
		}

		if (!empty($data['CallProfType_id'])) {
			$params['CallProfType_id'] = $data['CallProfType_id'];
			$filter .= " and hv.CallProfType_id = :CallProfType_id ";
		}

		$sql = "
			select 
				-- select
				msf.MedStaffFact_id,
				hv.HomeVisit_id,
				hv.Person_id,
				hv.HomeVisit_Num,
				convert(varchar(10), hv.HomeVisit_setDT, 104) as HomeVisit_setDate,
				p.PersonEvn_id,
				p.Server_id,
				p.Person_Surname,
				p.Person_Firname,
				p.Person_Secname,
				convert(varchar(10),p.Person_BirthDay,104) as Person_Birthday,
				rtrim(hv.Address_Address) as Address_Address,
				mp.Person_FIO as MedPersonal_FIO,
				isnull(lr.LpuRegion_id,lrc.LpuRegion_id) as LpuRegion_id,
				isnull(lrc.LpuRegion_Name,'') + ' ' + isnull(lrc.LpuRegionType_Name,'') as LpuRegion_Name,
				lb.LpuBuilding_id,
				lb.LpuBuilding_Name,
				case
					when hv.HomeVisitStatus_id is null then '0. Требует подтверждения'
					when hv.HomeVisitStatus_id = 1 and hvsrc.HomeVisitSource_Code = 10  and hv.CmpCallCard_id is not null then '1.	Актив из СМП'
					else cast((1 + hvs.HomeVisitStatus_Code) as varchar) +'. ' + hvs.HomeVisitStatus_Name
				end as HomeVisitStatus_Nameg,
				case
					when hv.HomeVisitStatus_id is null then 'Требует подтверждения'
					when hv.HomeVisitStatus_id = 1 and hvsrc.HomeVisitSource_Code = 10  and hv.CmpCallCard_id is not null then 'Актив из СМП'
					else hvs.HomeVisitStatus_Name
				end as HomeVisitStatus_Name,
				dbo.Age2(p.Person_Birthday, getdate()) as Person_Age,
				hv.HomeVisit_Phone,
				hv.HomeVisitStatus_id,
				hv.HomeVisitCallType_id,
				hvwc.HomeVisitWhoCall_Name,
				hvct.HomeVisitCallType_Name,
				HomeVisit_Symptoms,
				HomeVisit_Comment,
				HomeVisit_LpuComment,
				isnull(hv.CallProfType_id,1) as CallProfType_id,
				isnull(cpt.CallProfType_Name,'Терапевтический/педиатрический') as CallProfType_Name,
				convert(varchar(5),hv.HomeVisit_setDT,108) as HomeVisit_setTime,
				hv.Lpu_id,
				hv.HomeVisitSource_id,
				l.Lpu_Nick,
				ccc.CmpCallCard_id,
				case when hvsrc.HomeVisitSource_Code <> 5 then ccc.CmpCallCard_Ngod end as CmpCallCard_Ngod,
				case when ccc.CmpCallCard_id is not null and hvsrc.HomeVisitSource_Code <> 5 then convert(varchar(10),htsh.HomeVisitStatusHist_setDT,104) else null	end as HomeVisitStatusHist_setDT,
				case when lat.Lpu_Nick is null then '' else isnull(lat.Lpu_Nick,'') + '/' + isnull(pc.LpuRegion_Name,'') + isnull(pc.LpuRegionType_Name,'') end as LpuRegionAttach,
				RTRIM(ISNULL(LTRIM(RTRIM(msf.MedPersonal_TabCode)+ ' '), '') + ISNULL(LTRIM(RTRIM(mp.Person_FIO)+ ' '), '') + ISNULL(LTRIM(RTRIM('[' + LTRIM(RTRIM(msfls.LpuSection_Code)) + '. ' + LTRIM(RTRIM(msfls.LpuSection_Name)) + ']')+ ' '), '') + ISNULL(LTRIM(RTRIM(post.name)), '')) as MedStaff_Comp,
				hv.HomeVisit_isQuarantine
				-- end select
			from
				-- from
				v_HomeVisit hv with (nolock)
				{$lpuJoin}
				left join v_PersonState p with (nolock) on hv.Person_id = p.Person_id
				outer apply(
					select top 1 *
					from v_MedStaffFact with(nolock)
					where MedStaffFact_id = hv.MedStaffFact_id
				) msf
				left join persis.Post post with (nolock) on post.id = msf.Post_id
				left join v_LpuSection msfls with(nolock) on msfls.LpuSection_id = msf.LpuSection_id
				outer apply (
					select top 1 MedPersonal_id, Person_FIO
					from v_MedPersonal with(nolock)
					where MedPersonal_id = ISNULL(msf.MedPersonal_id, hv.MedPersonal_id)
						and Lpu_id = hv.Lpu_id
				) mp
				outer apply(
					select top 1 *
					from v_PersonCard_all with(nolock)
					where Person_id = hv.Person_id
						and LpuAttachType_id = 1 
						and hv.HomeVisit_insdt >= PersonCard_begDate
						and (hv.HomeVisit_insdt <= PersonCard_endDate or PersonCard_endDate IS NULL)
				) pc
				left join v_Lpu lat (nolock) on lat.Lpu_id = pc.Lpu_id
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = hv.LpuRegion_id and lr.Lpu_id = l.Lpu_id
				left join v_LpuRegion lrc with (nolock) on lrc.LpuRegion_id = hv.LpuRegion_cid
				left join v_LpuSection ls with(nolock) on ls.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding lb with(nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
				left join v_HomeVisitStatus hvs with (nolock) on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
				left join v_HomeVisitWhoCall hvwc with (nolock) on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
				left join v_HomeVisitCallType hvct with (nolock) on hv.HomeVisitCallType_id = hvct.HomeVisitCallType_id
				left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = hv.CmpCallCard_id
				left join v_CallProfType cpt with (nolock) on cpt.CallProfType_id = hv.CallProfType_id
				left join v_HomeVisitSource hvsrc with (nolock) on hvsrc.HomeVisitSource_id = hv.HomeVisitSource_id
				outer apply (
					select top 1 htsh.HomeVisitStatusHist_setDT
					from v_HomeVisitStatusHist htsh with(nolock)
					where htsh.HomeVisit_id = hv.HomeVisit_id and HomeVisitStatus_id = 1
				) htsh
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				hv.HomeVisitStatus_id,
				hv.HomeVisit_id
				-- end order by
		";

		// echo getDebugSQL($sql, $params);die;

		return $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
	}
	
	
	/**
	 * Одобрить вызов на дом
	 */
	function confirmHomeVisit($data)
	{
		$info =$this->getHomeVisitEditWindow($data);
		$data['MedPersonal_id']=$info[0]['MedPersonal_id'];
		$data['MedStaffFact_id']=$info[0]['MedStaffFact_id'];
		$data['HomeVisit_LpuComment']=$info[0]['HomeVisit_LpuComment'];
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'MedPersonal_id', 'MedStaffFact_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => 3 // статус Одобрено
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function setStatusNew($data)
	{
		$info =$this->getHomeVisitEditWindow($data);
		$data['MedPersonal_id']=null;
		$data['MedStaffFact_id']=null;
		$data['HomeVisit_LpuComment']=$info[0]['HomeVisit_LpuComment'];
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'MedPersonal_id', 'MedStaffFact_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => 1 // статус Одобрено
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function takeMP($data)
	{
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'MedPersonal_id', 'MedStaffFact_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => 6 // статус назначен врач
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			
			// Отправляем пуш уведомление
			$homevisit = $this->getHomeVisitForAPI($data);
			if (!empty($homevisit[0])) {
				$homevisit = $homevisit[0];
				$this->load->model('Person_model');
				$this->load->model('MedPersonal_model');
				
				$person = $this->Person_model->getPersonCombo($homevisit);
				$ms = $this->MedPersonal_model->getMedPersonInfo(array('MedStaffFact_id' => $homevisit['MedStaffFact_id']));
				
				if (!empty($person[0]['Person_Fio']) && !empty($ms[0]['MedPersonal_FIO'])) {
					$this->load->helper('Notify');
					$notifyResult = sendPushNotification(
						array(
							'Person_id' => $homevisit['Person_id'], // персона которая заходит
							'message' => 'Пациенту '
								.$person[0]['Person_Fio']
								.' назначен врач '
								.$ms[0]['MedPersonal_FIO']
								.' по адресу '
								.$homevisit['Address_Address'],
							'PushNoticeType_id' => 2,
							'action' => 'call'
						)
					);
				}
			}
			
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}
	/**
	 * Отказать в вызове на дом
	 */
	function denyHomeVisit($data)
	{
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => 2 // статус Отказ
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}

	/**
	 * Отменить вызов на дом
	 */
	function cancelHomeVisit($data) {
		$queryParams = array(
			'HomeVisit_id' => $data['HomeVisit_id'],
			'HomeVisit_LpuComment' => !empty($data['HomeVisit_LpuComment'])?$data['HomeVisit_LpuComment']:null,
			'HomeVisitStatus_id' => 5,
			'pmUser_id' => $data['pmUser_id']
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		} else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}
	
	/**
     * Получение улицы
     */
    function getKLStreetByGUID($guid) {	
		$params = array('KLStreet_AOGUID' => $guid);
		$query = "
			select
				*
			from v_KLStreet
			where
				KLStreet_AOGUID = :KLStreet_AOGUID
				and KLAdr_Actual = 0
		";
		return $this->getFirstRowFromQuery($query, $params);
	
	}

	/**
	 * Получение улицы
	 */
	function getGUIDByKLStreet($klstreet_id) {
		$params = array('KLStreet_id' => $klstreet_id);
		$query = "
			select KLStreet_AOGUID
			from v_KLStreet
			where
				KLStreet_id = :KLStreet_id
				and KLAdr_Actual = 0
		";
		return $this->getFirstRowFromQuery($query, $params);

	}
	
	/**
     * Поиск участков по адресу
     */
    function searchRegionsByAddress($address) {
		
		/**
		* Определение входит ли номер дома в диапазон (3 функции, вся эта жесть взята без изменений из старой версии сайта "к врачу")
		* Главная функция HouseMatchRange
		*/
		function getHouseArray($arr) {
			$arr = trim(mb_strtoupper($arr));
			$matches = array();
			$matches2 = array();
			if (preg_match("/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/ui", $arr, $matches)) {
				// Четный или нечетный
				$matches[count($matches)] = 1;
				return $matches;
			} elseif (preg_match("/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/ui", $arr, $matches)) {
				// Обычный диапазон
				$matches[count($matches)] = 2;
				return $matches;
			} elseif (preg_match("/^(\d+[а-яА-Я]?[\/]?\d{0,2}+[а-яА-Я]?)$/ui", $arr, $matches)) {
				//print $arr." ";
				if (preg_match("/^(\d+)/i", $matches[1], $matches2)) {
					$matches[count($matches)] = $matches2[1];
				} else {
					$matches[count($matches)] = '';
				}
				$matches[count($matches)] = 3;
				return $matches;
			}
			return array();
		}

		/**
		* Возвращает признак вхождения в диапазон домов
		*/
		function HouseExist($h_arr, $houses) {
			// Сначала разбираем h_arr и определяем:
			// 1. Обычный диапазон
			// 2. Четный диапазон
			// 3. Нечетный диапазон
			// 4. Перечисление
			// Разбиваем на номера домов и диапазоны с которым будем проверять
			$hs_arr = preg_split('[,|;]', $houses, -1, PREG_SPLIT_NO_EMPTY);
			//$i = 0;
			foreach ($h_arr as $row_arr) {
				//print $row_arr."   | ";
				$ch = getHouseArray($row_arr); // сохраняемый
				//print_r($ch);
				if (count($ch) > 0) {
					//print $i."-";
					foreach ($hs_arr as $rs_arr) {
						$chn = getHouseArray($rs_arr); // выбранный
						if (count($chn) > 0) {
							// Проверка на правильность указания диапазона
							if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($ch[2] > $ch[4])) {
								return false;
							}

							if ((($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Ч') && ($chn[1] == 'Ч')) || // сверяем четный с четным
									(($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Н') && ($chn[1] == 'Н')) || // сверяем нечетный с нечетным
									((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 2))) {        // или любой диапазон с обычным
								if (($ch[2] <= $chn[4]) && ($ch[4] >= $chn[2])) {
									return true; // Перечесение (С) и (В) диапазонов
								}
							}
							if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 3)) { // Любой диапазон с домом
								if ((($ch[1] == 'Ч') && ($chn[2] % 2 == 0)) || // если четный
										(($ch[1] == 'Н') && ($chn[2] % 2 <> 0)) || // нечетный
										($ch[count($ch) - 1] == 2)) { // обычный
									if (($ch[2] <= $chn[2]) && ($ch[4] >= $chn[2])) {
										return true; // Перечесение диапазона с конкретным домом
									}
								}
							}
							if ((($chn[count($chn) - 1] == 1) || ($chn[count($chn) - 1] == 2)) && ($ch[count($ch) - 1] == 3)) { // Любой дом с диапазоном
								if ((($chn[1] == 'Ч') && ($ch[2] % 2 == 0)) || // если четный
										(($chn[1] == 'Н') && ($ch[2] % 2 <> 0)) || // нечетный
										($chn[count($chn) - 1] == 2)) { // обычный
									if (($chn[2] <= $ch[2]) && ($chn[4] >= $ch[2])) {
										return true; // Перечесение дома с каким-либо диапазоном
									}
								}
							}
							if (($ch[count($ch) - 1] == 3) && ($chn[count($chn) - 1] == 3)) { // Дом с домом
								if (strtolower($ch[0]) == strtolower($chn[0])) {
									return true; // Перечесение дома с домом
								}
							}
						}
					}
				} else {
					return false; // Перечесение дома с домом
				}
			}
			return "";
		}
		
		/**
		* Проверка попадания номера дома в список домов
		*/
		function HouseMatchRange($sHouse, $sRange) {
			if ($sRange == "") {
				return true;
			}
			return HouseExist(array($sHouse), $sRange);
		}
		
		$sql = "
			select distinct
				l.Lpu_id,
				l.Lpu_Phone,
				lr.LpuRegion_id,
                lr.LpuRegion_Name,
                lrt.LpuRegionType_Name,
				lrt.LpuRegionType_SysNick,
                l.Lpu_Name,
                l.Lpu_Nick,
                MedStaffFact_List,
                lrs.LpuRegionStreet_HouseSet
			from LpuRegionStreet lrs with (nolock)
				inner join v_KLStreet kls with (nolock) on (kls.KLStreet_id = lrs.KLStreet_id and kls.KLStreet_AOGUID = :KLStreet_AOGUID)
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = lrs.LpuRegion_id
				left join v_LpuRegionType lrt with (nolock) on lr.LpuRegionType_id = lrt.LpuRegionType_id
				outer apply ( 
					select TOP 1
						ERTerr_id
					from ERTerr Terr with (nolock)
					where
						(
							((lrs.KLCountry_id = Terr.KLCountry_id) or (Terr.KLCountry_id is null)) and
							((lrs.KLRGN_id = Terr.KLRGN_id) or (Terr.KLRGN_id is null)) and
							((lrs.KLSubRGN_id = Terr.KLSubRGN_id) or (Terr.KLSubRGN_id is null)) and
							((lrs.KLCity_id = Terr.KLCity_id) or (Terr.KLCity_id is null)) and
							((lrs.KLTown_id = Terr.KLTown_id) or (Terr.KLTown_id is null))
						)
				) Terr
				cross apply (
					select 
						( select cast(msf2.MedStaffFact_id as varchar) + '=' + msf2.Person_FIO + ';' + cast(msf2.LpuUnit_id as varchar)+ ';' + cast(msf2.Lpu_id as varchar)+ ';' + cast(ls2.LpuSectionProfile_id as varchar) + '|' as 'data()'
							from v_MedStaffRegion msr2 with (nolock) 
							inner join v_MedStaffFact msf2 with (nolock) on msr2.MedStaffFact_id = msf2.MedStaffFact_id and isnull(msr2.MedStaffRegion_endDate, '2030-01-01') > getdate() 
							inner join v_LpuSection ls2 (nolock) on ls2.LpuSection_id = msf2.LpuSection_id
							inner join v_LpuSectionProfile lsp2 (nolock) on lsp2.LpuSectionProfile_id = ls2.LpuSectionProfile_id and lsp2.LpuSectionProfile_IsArea = 2
							inner join v_LpuUnit lu2 with (nolock) on ls2.LpuUnit_id = lu2.LpuUnit_id and isnull(lu2.LpuUnit_IsEnabled, 1) = 2
							where 
								msr2.LpuRegion_id = msr.LpuRegion_id and msr.Lpu_id = msr2.Lpu_id
							order by msf2.Person_FIO
							for xml path('') 
						) as MedStaffFact_List,
						msf.LpuUnit_id,
						ls.LpuSectionProfile_id
					from v_MedStaffRegion msr with (nolock) 
					inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = msr.MedStaffFact_id and isnull(msr.MedStaffRegion_endDate, '2030-01-01') > getdate()
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
					inner join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id and LpuSectionProfile_IsArea = 2
					where
						msf.Lpu_id = lr.Lpu_id
						and lr.LpuRegion_id = msr.LpuRegion_id
				) msf
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_Lpu l with (nolock) on lu.Lpu_id = l.Lpu_id
				left join LpuSectionProfile lsp with (nolock) on msf.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			where 
				lu.LpuUnitType_id = 2
                and l.Lpu_id = lr.Lpu_id
				and ISNULL(l.Lpu_IsTest, 1) = 1
				and (l.Lpu_endDate is null or l.Lpu_endDate >= dbo.tzGetDate())
			order by
                l.Lpu_id,
				LpuRegion_Name";

		
		$result = $this->db->query($sql, array('KLStreet_AOGUID' => $address->KLStreet_AOGUID));		
		$result = $result->result('object');
		
		if (count($result) > 0) {
			
			$result_grouped = array();

			// Дополнительно группируем для случая если на участке несколько раз заведена одна улица
			foreach($result as $data) {
				if (! (count($result_grouped) > 0 && $result_grouped[count($result_grouped)-1]->MedStaffFact_List == $data->MedStaffFact_List && $result_grouped[count($result_grouped)-1]->LpuRegion_id == $data->LpuRegion_id ) ) {
					$result_grouped[] = $data;
				}
			}

			$res = array();
			foreach ($result_grouped as $data) {
				if (empty($address->Address_House) || HouseMatchRange($address->Address_House, trim($data->LpuRegionStreet_HouseSet))) {

					if (!isset($hospital) || $hospital->Lpu_id != $data->Lpu_id) {
						$hospital = new StdClass();
						$hospital->Lpu_id = $data->Lpu_id;
						$hospital->Lpu_Name = $data->Lpu_Name;

						$hospital->regions = array();
						$res[] = $hospital;
					}

					$data->MedStaffFact_List = preg_replace('/[|]+$/', '', trim($data->MedStaffFact_List));
					$data->doctors = array();
					if (!empty($data->MedStaffFact_List)) {
						$doctors = explode('|', $data->MedStaffFact_List);
						foreach($doctors as $doctor) {
							list($k, $v) = explode('=', $doctor);
							$data->doctors[$k] = $v;
						}
					}
					$hospital->regions[] = $data;
				}
			}

			return $res;
			
		} else {
			return false;
		}
    }
	

	/**
	 * Получение доступности услуги по времени работы
	 */
	function getAllowTimeHomeVisit($lpu_id) {		
		$result = $this->queryResult("
			SELECT dbo.getAllowHomeVisitDay(:Lpu_id) as allow
		", array(
			'Lpu_id' => $lpu_id
		));
		if (!empty($result[0]['allow']) && $result[0]['allow'] == '1')  return true;
        return false;		
	}

	/**
	 * Получение времени работы 
	 */
	function getHomeVisitDayWorkTime($lpu_id) {		
		$result = $this->queryResult("
			SELECT dbo.getHomeVisitDayWorkTime(:Lpu_id) as datebetween
		", array(
			'Lpu_id' => $lpu_id
		));
		if (!empty($result[0]['datebetween'])) $result[0]['datebetween'];
        return false;		
	}
	

	/**
	 * Завершить обслуживание вызова на дом
	 */
	function completeHomeVisit($data)
	{
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'MedPersonal_id', 'MedStaffFact_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => 4 // статус Обслужено
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}

	/**
	 * Получение атрибутов вызова на дом. Метод для API.
	 */
	function getHomeVisitForAPI($data) {
		$resp = $this->queryResult("
			select
				hv.Person_id,
				hv.CallProfType_id,
				hv.Address_Address,
				hv.HomeVisitCallType_id,
				convert(varchar(19), hv.HomeVisit_setDT, 120) as HomeVisit_setDT,
				hv.HomeVisit_Num,
				hv.MedStaffFact_id,
				hv.HomeVisit_Phone,
				hv.HomeVisitWhoCall_id,
				hv.HomeVisit_Symptoms,
				hv.HomeVisit_Comment,
				hv.HomeVisitStatus_id,
				hv.HomeVisit_LpuComment,
				l.Lpu_Name,
				l.PAddress_Address,
				o.Org_Phone,
				msf.Person_Fio,
				mso.MedSpecOms_Name
			from
				v_HomeVisit hv (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = hv.Lpu_id
				left join v_Org o with (nolock) on o.Org_id = l.Org_id
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = hv.MedStaffFact_id
				left join v_MedSpecOms mso with (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
			where
				hv.HomeVisit_id = :HomeVisit_id
		", array(
			'HomeVisit_id' => $data['HomeVisit_id']
		));

		return $resp;
	}
	
	/**
	 * ff
	 */
	function getHomeVisitEditWindow($data){
		$sql = "
			select TOP 1
				hv.HomeVisit_id,
				hv.HomeVisit_Num,
				hv.Person_id,
				hv.CallProfType_id,
				hv.HomeVisitSource_id,
				p.PersonEvn_id,
				p.Server_id,
				hv.Lpu_id,
				hv.KLRgn_id,
				hv.KLSubRgn_id,
				hv.KLCity_id,
				hv.KLTown_id,
				hv.KLStreet_id,
				hv.Address_House,
				hv.Address_Corpus,
				hv.Address_Flat,
				rtrim(hv.Address_Address) as Address_Address,
				mp.MedPersonal_id,
				msf.MedStaffFact_id,
				hv.LpuRegion_cid,
				hv.LpuRegion_id,
				lr.LpuRegion_Name,
				cast(hvs.HomeVisitStatus_Code as varchar) +'. ' + hvs.HomeVisitStatus_Name as HomeVisitStatus_Name,
				dbo.Age2(p.Person_Birthday, getdate()) as Person_Age,
				l.Lpu_Nick + isnull(', участок: ' + lr.LpuRegion_Name, '') as Person_Attach,
				hv.HomeVisit_Phone,
				hv.HomeVisitStatus_id,
				hv.HomeVisitWhoCall_id,
				hv.HomeVisitCallType_id,
				hv.CmpCallCard_id,
				convert(varchar(10),hv.HomeVisit_setDT,104) as HomeVisit_setDate,
				convert(varchar(5),hv.HomeVisit_setDT,108) as HomeVisit_setTime,
				HomeVisit_Symptoms,
				HomeVisit_Comment,
				HomeVisit_LpuComment,
				hv.HomeVisit_isQuarantine
			from v_HomeVisit hv with (nolock)
			left join v_PersonState p with (nolock) on hv.Person_id = p.Person_id
			left join v_MedStaffFact msf with (nolock) on hv.MedStaffFact_id = msf.MedStaffFact_id
			outer apply (
				select top 1 MedPersonal_id, Person_FIO
				from v_MedPersonal with(nolock)
				where MedPersonal_id = ISNULL(hv.MedPersonal_id, msf.MedPersonal_id)
					and Lpu_id = hv.Lpu_id
			) mp
			left join v_PersonCard pc (nolock) on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
			left join v_Lpu l (nolock) on l.Lpu_id = pc.Lpu_id
			left join v_LpuRegion lr with (nolock) on hv.LpuRegion_id = lr.LpuRegion_id
			left join v_HomeVisitStatus hvs with (nolock) on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
			left join v_HomeVisitWhoCall hvwc with (nolock) on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
			where
				HomeVisit_id =  :HomeVisit_id";
		
		//echo getDebugSQL($sql,  array('HomeVisit_id' => $data['HomeVisit_id']));
		$result = $this->db->query($sql, array('HomeVisit_id' => $data['HomeVisit_id']));
		if (is_object($result)) {
			$res = $result->result('array');
			return $res;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение информации о вызове на дом
	 */
	function getHomeVisitInfo($HomeVisit_id) {
		$sql = "
			select TOP 1
				hv.HomeVisit_id,
				hv.Person_id,
				p.PersonEvn_id,
				p.Server_id,
				p.Person_Surname,
				p.Person_Firname,
				p.Person_Secname,
				p.Person_Surname + ' ' + p.Person_Firname + isnull(' ' + p.Person_Secname, '') as Person_FIO,
				convert(varchar(10),p.Person_BirthDay,104) as Person_Birthday,
				rtrim(hv.Address_Address) as Address_Address,
				isnull(mp.Person_FIO,'') as MedPersonal_FIO,
				hv.LpuRegion_id,
				lr.LpuRegion_Name,
				cast(hvs.HomeVisitStatus_Code as varchar) +'. ' + hvs.HomeVisitStatus_Name as HomeVisitStatus_Name,
				dbo.Age2(p.Person_Birthday, getdate()) as Person_Age,
				hv.HomeVisit_Phone,
				hv.HomeVisitStatus_id,
				hvwc.HomeVisitWhoCall_Name,
				HomeVisit_Symptoms,
				HomeVisit_Comment,
				HomeVisit_LpuComment,
				hv.pmUser_insId,
				convert(varchar(10),hv.HomeVisit_insDT,104) as HomeVisit_date
			from v_HomeVisit hv with (nolock)
			left join v_PersonState p with (nolock) on hv.Person_id = p.Person_id
			left join v_MedStaffFact msf with (nolock) on hv.MedStaffFact_id = msf.MedStaffFact_id
			outer apply (
				select top 1 MedPersonal_id, Person_FIO
				from v_MedPersonal with(nolock)
				where MedPersonal_id = ISNULL(hv.MedPersonal_id, msf.MedPersonal_id)
					and Lpu_id = hv.Lpu_id
			) mp
			left join v_LpuRegion lr with (nolock) on hv.LpuRegion_id = lr.LpuRegion_id
			left join v_HomeVisitStatus hvs with (nolock) on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
			left join v_HomeVisitWhoCall hvwc with (nolock) on hv.HomeVisitWhoCall_id = hvwc.HomeVisitWhoCall_id
			where
				HomeVisit_id = :HomeVisit_id";
		
		$result = $this->db->query($sql, array('HomeVisit_id' => $HomeVisit_id));
		if (is_object($result)) {
			$res = $result->result('array');
			return $res[0];
		} else {
			return false;
		}
	}
	
	/**
	 * Получение информации для оформления вызова на дом
	 */
	function getHomeVisitAddData($data) {
		$sql = "
			select TOP 1
				p.Person_id,
				a.Address_id,
				a.KLCountry_id,
				a.KLRgn_id,
				a.KLSubRgn_id,
				a.KLCity_id,
				a.KLTown_id,
				a.KLStreet_id,
				a.Address_House,
				a.Address_Corpus,
				a.Address_Flat,
				a.Address_Address,
				l.Lpu_Nick + isnull(', участок: ' + lr.LpuRegion_Name, '') as Person_Attach,
				lS.Lpu_Nick + isnull(', участок: ' + lrS.LpuRegion_Name, '') as Person_AttachS,
				lr.LpuRegion_id,
				l.Lpu_id,
				isnull(pi.PersonInfo_InternetPhone, p.Person_Phone) as HomeVisit_Phone,
				datediff(year,p.Person_BirthDay,dbo.tzGetDate()) as Person_Age
			from v_PersonState p with (nolock)
			left join v_Address a (nolock) on isnull(p.PAddress_id, p.UAddress_id) = a.Address_id
			left join v_PersonCard pc (nolock) on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 1
			left join v_Lpu l (nolock) on l.Lpu_id = pc.Lpu_id
			left join v_LpuRegion lr (nolock) on lr.LpuRegion_id = pc.LpuRegion_id
			left join v_PersonCard pcS (nolock) on p.Person_id = pc.Person_id and pc.LpuAttachType_id = 3
			left join v_Lpu lS (nolock) on lS.Lpu_id = pcS.Lpu_id
			left join v_LpuRegion lrS (nolock) on lrS.LpuRegion_id = pcS.LpuRegion_id
			left join v_PersonInfo pi (nolock) on p.Person_id = pi.Person_id
			where
				p.Person_id = :Person_id";
		
		$result = $this->db->query($sql, array('Person_id' => $data['Person_id']));
		if (is_object($result)) {
			$res = $result->result('array');
			if (
				is_array($res) 
				&& count($res)>0 
				&& !empty($res[0]['Address_id']) 
				&& empty($res[0]['LpuRegion_id']) 
				&& !empty($res[0]['Address_House'])
			) {
				if(!empty($res[0]['Person_Age']) && $res[0]['Person_Age'] < 18){
					$where = " and lr.LpuRegionType_SysNick in ('ped','vop')";
				} else {
					$where = " and lr.LpuRegionType_SysNick in ('ter','vop')";
				}
				$lr_params = array('Address_id' => $res[0]['Address_id']);
				if(!empty($data['Lpu_id'])){
					$lr_params['Lpu_id'] = $data['Lpu_id'];
					$where .= " and lr.Lpu_id = :Lpu_id";
				}
				$sql = "
					select
						lrs.LpuRegion_id,
						lrs.LpuRegionStreet_HouseSet
					from v_Address a (nolock)
					inner join LpuRegionStreet lrs with (nolock) on
						lrs.KLCountry_id = a.KLCountry_id
						and isnull(lrs.KLRGN_id,0) = isnull(a.KLRgn_id,0)
						and isnull(lrs.KLSubRGN_id,0) = isnull(a.KLSubRgn_id,0)
						and isnull(lrs.KLCity_id,0) = isnull(a.KLCity_id,0)
						and isnull(lrs.KLTown_id,0) = isnull(a.KLTown_id,0)
						and isnull(lrs.KLStreet_id,0) = isnull(a.KLStreet_id,0)
					inner join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = lrs.LpuRegion_id
					where
						a.Address_id = :Address_id
						{$where}
				";
				$result = $this->db->query($sql, $lr_params);
				if (is_object($result)) {
					$resl = $result->result('array');
					if(is_array($resl) && count($resl)>0){
						// Ищем по вхождению дома в список домов
						foreach ($resl as $value) {
							if(!empty($value['LpuRegionStreet_HouseSet'])){
								if(strpos($value['LpuRegionStreet_HouseSet'],',') > 0){
									$houses = explode(',', $value['LpuRegionStreet_HouseSet']);
									if(is_array($houses) && count($houses)>0){
										if(in_array($res[0]['Address_House'], $houses)){
											$res[0]['LpuRegion_id'] = $value['LpuRegion_id'];
											break;
										}
										// Если не нашли, то проверим на периоды (дома могут быть заданы периодами по типу {номер_дома}-{номер_дома})
										foreach ($houses as $house) {
											if(strpos($house, '-')>0){
												$house_set = explode('-', $house);
												if(is_array($house_set) && count($house_set)>0){
													if($res[0]['Address_House'] > $house_set[0] && $res[0]['Address_House'] < $house_set[0]){
														$res[0]['LpuRegion_id'] = $value['LpuRegion_id'];
														break;
													}
												}
											}
										}
									}
								} else if(strpos($value['LpuRegionStreet_HouseSet'],'-') > 0){
									// указан только период домов
									$house_set = explode('-', $value['LpuRegionStreet_HouseSet']);
									if(is_array($house_set) && count($house_set)>0){
										if($res[0]['Address_House'] > $house_set[0] && $res[0]['Address_House'] < $house_set[0]){
											$res[0]['LpuRegion_id'] = $value['LpuRegion_id'];
										}
									}
								} else if(trim($value['LpuRegionStreet_HouseSet']) == $res[0]['Address_House']){
									$res[0]['LpuRegion_id'] = $value['LpuRegion_id'];
								}
							} else {
								// не указаны дома - участок обслуживает все дома на улице
								$res[0]['LpuRegion_id'] = $value['LpuRegion_id'];
							}
							// нашли участок - выходим из цикла по списку домов
							if(!empty($res[0]['LpuRegion_id'])){
								break;
							}
						}
					}
				}
			}
			return $res;
		} else {
			return false;
		}
	}
	
	/**
     * Получение справочника симптомов в виде иерархической структуры
     */
	function getSymptoms() {
		
		/**
         * Генерация дерева симптомов
         */
        function buildTree(array $elements, $parentId = 0) {
            $branch = array();

            foreach ($elements as $element) {
                if ($element['pid'] == $parentId) {
                    $children = buildTree($elements, $element['id']);
                    if ($children) {
                        $element['children'] = $children;
                    }
                    $branch[] = $element;
                }
            }

            return $branch;
        }
		
        $sql = "
			select
				HomeVisitSymptom_id as id,
                HomeVisitSymptom_pid as pid,
                HomeVisitSymptom_Name as name,
                HomeVisitSymptom_IsRadioGroup as radio,
                case when HomeVisitSymptomType_id = 2 then 'stom' else 'ther' end as visittype
            from HomeVisitSymptom with(nolock)
		";
        $result = $this->db->query($sql);
		if (is_object($result)) {
			$result = $result->result('array');
			
			$symptoms_arr = array();

			foreach($result as $row) {
				$symptoms_arr[$row['id']] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'radio' => $row['radio'],
					'pid' => $row['pid'],
					'visittype' => $row['visittype']
				);
				if (isset($row['pid'])) {
					if ( isset($symptoms_arr[$row['pid']]['radio']) ) {
						$symptoms_arr[$row['id']]['type'] = 'radio';
					} else {
						$symptoms_arr[$row['id']]['type'] = 'check';
					}
				} else {
					$symptoms_arr[$row['id']]['type'] = 'maingroup';

				}
			}
			
			$tree = buildTree($symptoms_arr);
		
			return $tree;
		
		} else {
			return false;
		}
    }
	
	
	/**
	 * Сохранение вызова на дом
	 */
	function addHomeVisit($data, $callSMP = false) {
		$status = $data['HomeVisitStatus_id'];
		$old_status = 0;
		$proc = 'p_HomeVisit_ins';
		if($data['HomeVisit_id']>0){
			$proc = 'p_HomeVisit_upd';
			$tmp = $this->getFirstRowFromQuery("
				SELECT
					HomeVisitStatus_id,
					HomeVisitSource_id,
					HomeVisit_PhoneCall,
					HomeVisit_GUID
				FROM v_HomeVisit with(nolock)
				WHERE HomeVisit_id = :HomeVisit_id
			", $data);
			if ( is_array($tmp) && count($tmp) > 0 ) {
				$old_status = $tmp['HomeVisitStatus_id'];
				$data['HomeVisitSource_id'] = $tmp['HomeVisitSource_id'];
				$data['HomeVisit_PhoneCall'] = $tmp['HomeVisit_PhoneCall'];
				$data['HomeVisit_GUID'] = $tmp['HomeVisit_GUID'];
			}
		}
		else {
			$data['HomeVisitStatus_id'] = 1;

		}
		if($status==1&&!empty($data['MedPersonal_id'])){
			$status = 6;
		}
		if($status==1&&!empty($data['MedStaffFact_id'])){
			$status = 6;
		}

		if ( empty($data['HomeVisit_setDT']) && !empty($data['HomeVisit_setDate']) ) {
			$data['HomeVisit_setDT'] = $data['HomeVisit_setDate'] . ' ' . (!empty($data['HomeVisit_setTime']) ? $data['HomeVisit_setTime'] : "00:00");
		}
		
		if(empty($data['HomeVisitSource_id']) && !empty($data['session']['CurArmType'])){
			if($data['session']['CurArmType'] == 'regpol') {
				$data['HomeVisitSource_id'] = 8;
			}elseif($data['session']['CurArmType'] == 'callcenter'){
				$data['HomeVisitSource_id'] = 9;
			}elseif($data['session']['CurArmType'] == 'common'){
				$data['HomeVisitSource_id'] = 1;
			}
		}elseif(empty($data['HomeVisitSource_id']) && $callSMP){
			$data['HomeVisitSource_id'] = 10;
		}

		$queryParams = getArrayElements(
			$data,
			array(
				'Person_id', 'Lpu_id', 'LpuRegion_id', 'LpuRegion_cid', 'MedPersonal_id','MedStaffFact_id', 'KLRgn_id', 'KLSubRgn_id', 'KLCity_id', 'KLTown_id', 'KLStreet_id', 'Address_House', 
				'Address_Flat', 'Address_Corpus', 'Address_Address', 'HomeVisit_Phone', 'HomeVisitWhoCall_id', 'HomeVisit_Symptoms', 'HomeVisit_Comment', 'HomeVisit_LpuComment',
				'HomeVisitStatus_id', 'HomeVisitCallType_id', 'HomeVisit_setDT', 'pmUser_id','HomeVisit_id','CallProfType_id', 'HomeVisit_Num', 'CmpCallCard_id',
				'HomeVisitSource_id', 'HomeVisit_PhoneCall', 'HomeVisit_GUID', 'HomeVisit_isQuarantine'
			)
		);
		if(empty($queryParams['HomeVisit_id'])){
			$queryParams['HomeVisit_id']=array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			);
		}

		if ( ($resp = $this->execCommonSP($proc, $queryParams)) ) {

			$IsSMPServer = $this->config->item('IsSMPServer');

			if($IsSMPServer && !empty($resp[0]['HomeVisit_id']) && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE){

				$this->load->model('Replicator_model');
				$this->Replicator_model->sendRecordToActiveMQ(array(
					'table' => 'HomeVisit',
					'type' => empty($data['HomeVisit_id']) ? 'insert' : 'update',
					'keyParam' => 'HomeVisit_id',
					'keyValue' => $resp[0]['HomeVisit_id']
				), '/queue/dbReplicator.HomeVisit.ProMed.Emergency.HomeVisit');

			}

			if(!$data['HomeVisit_id'] > 0){
				$data['HomeVisit_id'] = $resp[0]['HomeVisit_id'];
			}

			if($status!=$data['HomeVisitStatus_id']){
				$data['HomeVisitStatus_id']=$status;
				$this->updateStatus($data);
			} elseif ($status != $old_status) {
				$this->saveHomeVisitStatusHist($data);
			}
			
			if(!empty($data['isSavedCVI']) ) {
				$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
				$params = [
					'Person_id' => $data['Person_id'],
					'HomeVisit_id' => !empty($data['HomeVisit_id']) ? $data['HomeVisit_id'] : null,
					'PlaceArrival_id' => $data['PlaceArrival_id'],
					'KLCountry_id' => $data['CVICountry_id'],
					'OMSSprTerr_id' => $data['OMSSprTerr_id'],
					'ApplicationCVI_arrivalDate' => $data['ApplicationCVI_arrivalDate'],
					'ApplicationCVI_flightNumber' => $data['ApplicationCVI_flightNumber'],
					'ApplicationCVI_isContact' => $data['ApplicationCVI_isContact'],
					'ApplicationCVI_isHighTemperature' => $data['ApplicationCVI_isHighTemperature'],
					'Cough_id' => $data['Cough_id'],
					'Dyspnea_id' => $data['Dyspnea_id'],
					'ApplicationCVI_Other' => $data['ApplicationCVI_Other']
				];
				$res = $this->ApplicationCVI_model->doSave($params, false);
				if( !$this->isSuccessful($res) ) {
					throw new Exception($res['Error_Msg']);
				}
			}

			return $resp;
		}
		else {
			return array(array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			));
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function updateStatus($data){
		$queryParams = getArrayElements(
			$data,
			array(
				'HomeVisit_id', 'MedPersonal_id', 'MedStaffFact_id', 'HomeVisit_LpuComment', 'pmUser_id'
			)
		) + array(
			'HomeVisitStatus_id' => $data['HomeVisitStatus_id'], // статус Одобрено
			//'HomeVisitSource_id' => (!empty($data['HomeVisitSource_id'])) ? $data['HomeVisitSource_id'] : null, // статус Одобрено
		);

		if(!key_exists('HomeVisitSource_id', $data)){// если не передан источник, то берем существующий
			if($homeVisitSourceId = $this->dbmodel->getFirstResultFromQuery('select top 1 HomeVisitSource_id from HomeVisit (nolock) where HomeVisit_id = :HomeVisit_id', array('HomeVisit_id' => $data['HomeVisit_id']))){
				$queryParams['HomeVisitSource_id'] = $homeVisitSourceId;
			}
		}else{
			$queryParams['HomeVisitSource_id'] = $data['HomeVisitSource_id'];
		}

		if ( ($resp = $this->execCommonSP("p_HomeVisit_setStatus", $queryParams)) ) {
			$this->saveHomeVisitStatusHist($queryParams);
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}
	
	/**
	 * Проверка, что вызов на дом уже не существует
	 */
	function checkHomeVisitExists($data) {
		$params = array('Person_id' => $data['Person_id']);
		if(!empty($data['HomeVisit_setDate'])){
			$params['setDate'] = $data['HomeVisit_setDate'];
		} else if(!empty($data['HomeVisit_setDT'])){
			$params['setDate'] = $data['HomeVisit_setDT'];
		} else {
			$params['setDate'] = date("Y-m-d");
		}
		$sql = "
			select 
				hv.Lpu_id,
				lpu.Lpu_Nick,
				isnull(hv.HomeVisit_Num,'') as HomeVisit_Num,
				convert(varchar(10), hv.HomeVisit_setDT, 104) as HomeVisit_setDT
			from v_HomeVisit hv with (nolock)
			left join v_Lpu lpu with (nolock) on lpu.Lpu_id = hv.Lpu_id
			where
				hv.Person_id = :Person_id
				and hv.HomeVisitStatus_id in (1,3,6)
				and cast(hv.HomeVisit_setDT as date) = cast(:setDate as date)";
		
		$result = $this->db->query(
			$sql, 
			$params
		);
		if (is_object($result)&&!isset($data['HomeVisit_id'])) {
			$res = $result->result('array');
			if(isset($res[0]) && $res[0]['Lpu_id'] > 0)
				return $res;
			else 
				return false;
		} else {
			return false;
		}
	}

	/**
	 * История статусов вызова
	 */
	function loadHomeVisitStatusHist($data) {

		$q = "
			SELECT
				HVSH.HomeVisitStatusHist_id
				,HVS.HomeVisitStatus_Name
				,convert(varchar(10), HVSH.HomeVisitStatusHist_setDT, 104) + ' ' + convert(varchar(5), HVSH.HomeVisitStatusHist_setDT, 108) as HomeVisitStatusHist_setDT
				,U.pmUser_Name
			FROM
				v_HomeVisitStatusHist HVSH WITH (NOLOCK)
				INNER JOIN v_HomeVisitStatus HVS WITH (NOLOCK) ON HVS.HomeVisitStatus_id = HVSH.HomeVisitStatus_id
				LEFT JOIN v_pmUser U with (nolock) on U.pmUser_id = HVSH.pmUser_insID and (U.Kind != '1' or U.Kind is null)
			WHERE
				HVSH.HomeVisit_id = ?
		";
		$result = $this->db->query($q, array($data['HomeVisit_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка грида с доп временем или формы
	 */
	function loadHomeVisitAdditionalSettings($data) {

		$where = "HVAS.Lpu_id = :Lpu_id";

		$params = array(
			"Lpu_id" => $data['Lpu_id']
		);

		//var_dump($data["HomeVisitAdditionalSettings_id"]); exit;
		if(!empty($data["HomeVisitAdditionalSettings_id"])){
			$where .= " and HVAS.HomeVisitAdditionalSettings_id = :HomeVisitAdditionalSettings_id";
			$params["HomeVisitAdditionalSettings_id"] = $data["HomeVisitAdditionalSettings_id"];
		}

		$q = "
			SELECT
				HVAS.HomeVisitAdditionalSettings_id,
				convert(varchar(10), HVAS.HomeVisitAdditionalSettings_begDate, 104) as HomeVisitAdditionalSettings_begDate,
				convert(varchar(10), HVAS.HomeVisitAdditionalSettings_endDate, 104) as HomeVisitAdditionalSettings_endDate,
				convert(varchar(5), HVAS.HomeVisitAdditionalSettings_begTime, 108) as HomeVisitAdditionalSettings_begTime,
				convert(varchar(5), HVAS.HomeVisitAdditionalSettings_endTime, 108) as HomeVisitAdditionalSettings_endTime,
				HVAS.Lpu_id,
				HVAS.HomeVisitPeriodType_id,
				HVPT.HomeVisitPeriodType_Name
			FROM
				v_HomeVisitAdditionalSettings HVAS WITH (NOLOCK)
				INNER JOIN v_HomeVisitPeriodType HVPT WITH (NOLOCK) ON HVAS.HomeVisitPeriodType_id = HVPT.HomeVisitPeriodType_id
			WHERE
				{$where}
		";
		$result = $this->db->query($q, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	
	
	/**
	 *	Удаление доп времени
	 */
	function deleteHomeVisitAdditionalSettings($data)
	{
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec p_HomeVisitAdditionalSettings_del
				@HomeVisitAdditionalSettings_id = :HomeVisitAdditionalSettings_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}

		return false;
	}

	
	/**
	 * Сохранение истории статусов вызова
	 */
	function saveHomeVisitStatusHist($data) {

		$params = array (
			'HomeVisitStatusHist_id' => isset($data['HomeVisitStatusHist_id']) ? $data['HomeVisitStatusHist_id'] : null,
			'HomeVisit_id' => $data['HomeVisit_id'],
			'HomeVisitStatus_id' => $data['HomeVisitStatus_id'],
			'MedPersonal_id' => isset($_SESSION['medpersonal_id']) ? $_SESSION['medpersonal_id'] : null,
			'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime,
				@HomeVisitStatusHist_id bigint = :HomeVisitStatusHist_id;
			set @curdate = dbo.tzGetDate();
			exec p_HomeVisitStatusHist_ins
				@HomeVisitStatusHist_id = @HomeVisitStatusHist_id output,
				@HomeVisit_id = :HomeVisit_id,
				@HomeVisitStatus_id = :HomeVisitStatus_id,
				@HomeVisitStatusHist_setDT = @curdate,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @HomeVisitStatusHist_id as HomeVisitStatusHist_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			$IsSMPServer = $this->config->item('IsSMPServer');
			if($IsSMPServer && !empty($resp[0]['HomeVisitStatusHist_id']) && defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE){

				$this->load->model('Replicator_model');
				$this->Replicator_model->sendRecordToActiveMQ(array(
					'table' => 'HomeVisitStatusHist',
					'type' => 'insert',
					'keyParam' => 'HomeVisitStatusHist_id',
					'keyValue' => $resp[0]['HomeVisitStatusHist_id']
				), '/queue/dbReplicator.HomeVisit.ProMed.Emergency.HomeVisitStatusHistory');


			}
			return $resp;
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранение режима работы
	 */
	function saveHomeVisitWorkMode($data) {

		// Удаляем все что относится к ЛПУ		
		$query = "
			delete from
				HomeVisitWorkMode
			where
				Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query,  
			array ('Lpu_id' => $data['Lpu_id'])
		);
		
		// Вставляем
		if (!empty($data['homevizit_isallowed']) && $data['homevizit_isallowed'] == 'on') {
			for ($i = 1; $i <=7; $i++) {
				if (isset($data['homevizit_day'.$i]) && $data['homevizit_day'.$i] == 'on') {
					$params = array (
						'week_id' => $i,
						'lpu_id' => $data['Lpu_id'],
						'homevizit_begtime' => isset($data['homevizit_begtime'.$i]) ? $data['homevizit_begtime'.$i] : null,
						'homevizit_endtime' => isset($data['homevizit_endtime'.$i]) ? $data['homevizit_endtime'.$i] : null,
						'pmUser_id' => $data['pmUser_id']
					);
					$query = "
						declare
							@HomeVisitWorkMode_id bigint,
							@Error_Code bigint,
							@Error_Message varchar(4000),
							@curdate datetime
							
						set @HomeVisitWorkMode_id = null;
						
						exec p_HomeVisitWorkMode_ins
							@HomeVisitWorkMode_id = @HomeVisitWorkMode_id output,
							@HomeVisitWorkMode_begDate = :homevizit_begtime,
							@HomeVisitWorkMode_endDate = :homevizit_endtime,
							@Lpu_id = :lpu_id,
							@CalendarWeek_id = :week_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @HomeVisitWorkMode_id as HomeVisitWorkMode_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";
					$result = $this->db->query($query, $params);
				}
			}
		}
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранение доп времени работы вызова врача
	 */
	function saveHomeVisitAdditionalSettings($data) {

		$preQuery = "
			SELECT
				HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime) as HomeVisitAdditionalSettings_begDate,
				HomeVisitAdditionalSettings_endDate + cast(HomeVisitAdditionalSettings_endTime as datetime) as HomeVisitAdditionalSettings_endDate
			FROM v_HomeVisitAdditionalSettings with (nolock)
			WHERE
				Lpu_id = :Lpu_id
				AND HomeVisitPeriodType_id = :HomeVisitPeriodType_id
				AND HomeVisitAdditionalSettings_id != ISNULL(:HomeVisitAdditionalSettings_id, 0)
				AND (
					--На 2016 сервере склейки такого вида DATETIME +' '+ TIME не работают.
					--прибавить время к дате напрямую на 2016 сервере нельзя. Сначала время переведем в дату а уже потом склеиваем их.
					(:HomeVisitAdditionalSettings_begDateTime BETWEEN HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime) AND HomeVisitAdditionalSettings_endDate + cast(HomeVisitAdditionalSettings_endTime AS datetime)) or
					(:HomeVisitAdditionalSettings_endDateTime BETWEEN HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime) AND HomeVisitAdditionalSettings_endDate + cast(HomeVisitAdditionalSettings_endTime AS datetime)) or
					(HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime AS datetime) BETWEEN :HomeVisitAdditionalSettings_begDateTime AND :HomeVisitAdditionalSettings_endDateTime)
				)
		";

		$preParams = array(
			'HomeVisitAdditionalSettings_id' => !empty($data['HomeVisitAdditionalSettings_id']) ? $data['HomeVisitAdditionalSettings_id'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'HomeVisitPeriodType_id' => $data['HomeVisitPeriodType_id'],
			'HomeVisitAdditionalSettings_begDateTime' => $data['HomeVisitAdditionalSettings_begDate'] .' '. $data['HomeVisitAdditionalSettings_begTime'],
			'HomeVisitAdditionalSettings_endDateTime' => $data['HomeVisitAdditionalSettings_endDate'] .' '. $data['HomeVisitAdditionalSettings_endTime']
		);

		$result = $this->db->query($preQuery, $preParams);

		$result = $result->result('array');

		if (count($result) > 0) {
			return array('Error_Msg' => 'Пересечение даты периода работы или выходных. Проверьте корректность введенной даты', 'success' => false);
		}

		$procedure = !empty($data['HomeVisitAdditionalSettings_id']) ? 'p_HomeVisitAdditionalSettings_upd' : 'p_HomeVisitAdditionalSettings_ins';

		$query = "
			declare
				@HomeVisitAdditionalSettings_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000)

			set @HomeVisitAdditionalSettings_id = :HomeVisitAdditionalSettings_id;

			exec {$procedure}
				@HomeVisitAdditionalSettings_id = @HomeVisitAdditionalSettings_id output,
				@HomeVisitAdditionalSettings_begDate = :HomeVisitAdditionalSettings_begDate,
				@HomeVisitAdditionalSettings_endDate = :HomeVisitAdditionalSettings_endDate,
				@HomeVisitAdditionalSettings_begTime = :HomeVisitAdditionalSettings_begTime,
				@HomeVisitAdditionalSettings_endTime = :HomeVisitAdditionalSettings_endTime,
				@Lpu_id = :Lpu_id,
				@HomeVisitPeriodType_id = :HomeVisitPeriodType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @HomeVisitAdditionalSettings_id as HomeVisitAdditionalSettings_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	
	/**
	 * Сохранение режима работы
	 */
	function getHomeVisitWorkMode($data) {

		 $query = "
            Select
               *
            from v_HomeVisitWorkMode HVWM with (nolock)
            where
               HVWM.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $data);
        $result = $this->db->query($query,  
			array ('Lpu_id' => $data['Lpu_id'])
		);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
	}

	/**
     * Возвращает список ЛПУ с действующими периодом, в котором МО может производить обслуживание населения на дому по стоматологическим профилям
     */
    function getLpuPeriodStomMOList($data)
    {
        $query = "
            Select
                LPS.Lpu_id
            from v_LpuPeriodStom LPS with (nolock)
            where
                LPS.LpuPeriodStom_begDate <= dbo.tzGetDate()
                and (LPS.LpuPeriodStom_endDate is null or LPS.LpuPeriodStom_endDate > dbo.tzGetDate())
        ";
        //echo getDebugSQL($query, $data);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Возвращает номер активного нумератора
     */
    function getHomeVisitNum($data, $numerator = null)
    {
    	$params = array(
			'NumeratorObject_SysName' => 'HomeVisit',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'onDate' => $data['onDate'],
			'Numerator_id' => $data['Numerator_id']
		);
		$name = 'Вызов врача на дом';
        $this->load->model('Numerator_model');

		$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);

		if (!empty($resp['Numerator_Num'])) {
			return $resp;
		} else {
			if (!empty($resp['Error_Msg'])) {
				return array('Error_Msg' => $resp['Error_Msg'], 'success' => false);
			}
			return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.', 'Error_Code' => 'numerator404', 'success' => false);
		}
    }

	/**
	 * Получение количества вызовов с назначенным врачем за день
	 */
	function getHomeVisitCount($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'date' => $data['date'],
		);
		$query = "
			select top 1
				count(*) as HomeVisitCount
			from
				v_HomeVisit HV with(nolock)
			where
				HV.HomeVisitStatus_id = 6
				and HV.MedPersonal_id = :MedPersonal_id
				and cast(HV.HomeVisit_setDT as date) = :date
		";

		$HomeVisitCount = $this->getFirstResultFromQuery($query, $params);
		if ($HomeVisitCount === false) {
			return $this->createError('','Ошибка при получении количества вызовов');
		}
		return array(array('success' => true, 'Error_Msg' => '', 'HomeVisitCount' => $HomeVisitCount));
	}

	/**
	 * Установка статуса
	 */
	function setHomeVisitStatus($data) {
		$resp = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_HomeVisit_setStatus
				@HomeVisit_id = :HomeVisit_id,
				@HomeVisitStatus_id = :HomeVisitStatus_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@HomeVisitSource_id = :HomeVisitSource_id,
				@HomeVisit_LpuComment = :HomeVisit_LpuComment,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'HomeVisit_id' => $data['HomeVisit_id'],
			'HomeVisitStatus_id' => $data['HomeVisitStatus_id'],
			'pmUser_id' => $data['pmUser_id'],
			'HomeVisitSource_id' => $data['HomeVisitSource_id'],
			'HomeVisit_LpuComment' => !empty($data['HomeVisit_LpuComment']) ? $data['HomeVisit_LpuComment'] : null,
			'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null
		));

		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		return $resp;
	}

	/**
	 * Получение ближайшего возможного времени для вызова на дом
	 * $desiredDate - желаемая дата
	 */
	function getHomeVisitNearestWorkDay($data, $desiredDate = false, $secondRun = false){
		if(empty($data['Lpu_id'])) return false;

		$desiredDate = ($desiredDate && ($desiredDate instanceof DateTime)) ? $desiredDate : $this->getCurrentDT();

		$respDate = $desiredDate;
		//$respDate = null;

		$CalendarWeek_id = $desiredDate->format('N');

		//Расписание работы сервиса ПН - ВС
		$arrHomeVisitWorkMode = $this->getHomeVisitWorkMode(array('Lpu_id' => $data['Lpu_id']));

		$dateInWorkPeriod = NULL;

		for ($i = 0; $i <7; $i++) {

			// перебираем неделю начиная с сегодня на 7 дней вперед
			$calcDay = (($CalendarWeek_id+$i) % 7);
			if($calcDay == 0) $calcDay = 7;

			//перебираем массив сохраненного расписания
			foreach($arrHomeVisitWorkMode as $workDay){
				//нашли тот день по порядку в цикле(начиная с сегодня)
				if($workDay['CalendarWeek_id'] == $calcDay){

					$begDate = new DateTime($desiredDate->format('Y-m-d') . ' ' . $workDay['HomeVisitWorkMode_begDate']->format('H:i:s'));
					$endDate = new DateTime($desiredDate->format('Y-m-d') . ' ' . $workDay['HomeVisitWorkMode_endDate']->format('H:i:s'));

					//Если день совпал с нашим (если заведен - будет первым)
					if($workDay['CalendarWeek_id'] == $CalendarWeek_id) {
						//Проверяем время
						if ($begDate <= $desiredDate && $endDate > $desiredDate) {
							//Время вовпало - возвращаем желаемую дату
							$respDate = $desiredDate;
							$dateInWorkPeriod = true;
							goto jumpOut;
						}else{
							//Время не наступило - возвращаем ближ. дату
							if($begDate > $desiredDate ){
								$respDate = $begDate;
								$dateInWorkPeriod = false;
								goto jumpOut;
							}
						}
					}
					else{
						//нет? - просто берем первый попавшийся и уходим
						$begDate->add(new DateInterval('P'.$i.'D'));
						$respDate = $begDate;
						$dateInWorkPeriod = false;
						goto jumpOut;
					}
				}
			}
		}

		jumpOut:;


		//Проверим входит ли этот день в список дополнительных выходных
		$weekendQuery = "
			SELECT TOP 1
			(convert(varchar(10), cast(HomeVisitAdditionalSettings_endDate as datetime), 120)+' '+convert(varchar(8), cast(HomeVisitAdditionalSettings_endTime as datetime), 108)) as HomeVisitAdditionalSettings_endDate
			FROM v_HomeVisitAdditionalSettings
			WHERE Lpu_id=:Lpu_id AND HomeVisitPeriodType_id = 2 AND
			(:Date BETWEEN HomeVisitAdditionalSettings_begDate AND HomeVisitAdditionalSettings_endDate) AND
			(:Time >= CAST(HomeVisitAdditionalSettings_begTime AS TIME) AND :Time < CAST(HomeVisitAdditionalSettings_endTime AS TIME))
		";

		$weekendParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Date' => $respDate->format('Y-m-d'),
			'Time' => $respDate->format('H:i:s')
		);

		$weekendSettings = $this->queryResult($weekendQuery, $weekendParams);

		if(count($weekendSettings)>0 ){
			$this->getHomeVisitNearestWorkDay($data, DateTime::createFromFormat('Y-m-d H:i:s', $weekendSettings[0]['HomeVisitAdditionalSettings_endDate']), true);
			$dateInWorkPeriod = false;
		}


		//Проверим есть ли дополнительные рабочие дни ранее
		//(больше желаемой даты и меньше итоговой)
		$workQuery = "
			SELECT TOP 1
				HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime) as HomeVisitAdditionalSettings_begDT,
                case when :desiredDate >= HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime) then 2 else 1 end as dateInWorkPeriod
			FROM v_HomeVisitAdditionalSettings
			WHERE
				Lpu_id=:Lpu_id
				AND HomeVisitPeriodType_id = 1
				AND	((
					HomeVisitAdditionalSettings_begDate + cast(HomeVisitAdditionalSettings_begTime as datetime)
					BETWEEN :desiredDate and :respDate
				)
				OR(
					HomeVisitAdditionalSettings_endDate + cast(HomeVisitAdditionalSettings_endTime as datetime)
					BETWEEN :desiredDate and :respDate
					)
				)
			ORDER BY HomeVisitAdditionalSettings_begTime
		";

		$workParams = array('Lpu_id' => $data['Lpu_id'], 'desiredDate' => $desiredDate->format('Y-m-d H:i:s'), 'respDate' => $respDate->format('Y-m-d H:i:s'));

		$workSettings = $this->queryResult($workQuery, $workParams);

		//Если есть то берем первый
		if(count($workSettings) > 0){
			if($workSettings[0]['dateInWorkPeriod'] == 2){
				$respDate = $desiredDate;
				$dateInWorkPeriod = true;
			}
			else{
				$respDate = new DateTime($workSettings[0]['HomeVisitAdditionalSettings_begDT']);
				$dateInWorkPeriod = false;
			}
		}

		return array(
			'NearestDate' => $respDate,
			'DateInPeriod' => $dateInWorkPeriod
		);

	}

	/**
	 * Проверка на наличие обслуженного вызова на дом
	 */
	function checkHomeVizit($data)	{
		if ($data['EvnClass_SysNick'] == 'EvnVizitPL') {
			$from = 'v_EvnVizitPL';
			$where = 'EvnVizitPL_id';
		} else {
			$from = 'v_EvnVizitPLStom';
			$where = 'EvnVizitPLStom_id';
		}

		$query = "
			select top 1 1
			from
				{$from} with (nolock)
			where
				{$where} = :Evn_id
				and HomeVisit_id is not null
		";

		$res = $this->getFirstResultFromQuery($query, $data);

		return $res;
	}


	/**
	 * Изменение статуса вызова на дом в посещении
	 */
	function revertHomeVizitStatus($data) {
		$from = "v_{$data['EvnClass_SysNick']}";
		$where = "{$data['EvnClass_SysNick']}_id";

		$query = "
			select top 1
				HomeVisit_id
			from {$from} hv with (nolock)
			where {$where} = :Evn_id
		";

		$res = $this->getFirstResultFromQuery($query, $data);

		if ($res) {
			$query = "
				declare
					@id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@status_id bigint,
					@delStatus bigint,
					@curMedStaffFact_id bigint;
					
				set @id = :HomeVizit_id;
				set @status_id = (
					select top 1
						HomeVisitStatus_id
					from (
						select top 2
							HomeVisitStatus_id,
							row_number() over (order by HomeVisitStatusHist_updDT) as rownum
						from v_HomeVisitStatusHist with (nolock)
						where HomeVisit_id = @id
						order by HomeVisitStatusHist_setDT desc) as t
					where rownum = 2
				);
				set @delStatus = (
					select top 1
						HomeVisitStatusHist_id
					from v_HomeVisitStatusHist with (nolock)
					where HomeVisit_id = @id
					order by HomeVisitStatusHist_setDT desc
				);
				set @curMedStaffFact_id = (
					select top 1
						MedStaffFact_id
					from v_HomeVisitStatusHist with (nolock)
					where HomeVisit_id = @id
					order by HomeVisitStatusHist_setDT desc
				);
				
				exec p_HomeVisitStatusHist_del
					@HomeVisitStatusHist_id = @delStatus;
						
				exec p_HomeVisit_setStatus
					@HomeVisit_id = @id,
					@HomeVisitStatus_id = @status_id,
					@MedStaffFact_id = @curMedStaffFact_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @status_id as Status_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;	
			";

			$result = $this->db->query($query, array(
				'HomeVizit_id' => $res,
				'pmUser_id' => $data['pmUser_id']
			));

			$result = $result->result('array');
			if (isset($result[0])) {
				return $result;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * * Получение МО по адресу
	 */
	function getMO($data)
	{
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['KLTown_id'])) {
			$filters .= " and lrs.KLTown_id = :KLTown_id";
			$params['KLTown_id'] = $data['KLTown_id'];
		}

		if (!empty($data['KLStreet_id'])) {
			$filters .= " and lrs.KLStreet_id = :KLStreet_id";
			$params['KLStreet_id'] = $data['KLStreet_id'];
		}

		if (!empty($data['KLCity_id'])) {
			$filters .= " and lrs.KLCity_id = :KLCity_id";
			$params['KLCity_id'] = $data['KLCity_id'];
		}

		$query = "
			SELECT
				KLCity_id, 
				KLTown_id, 
				KLStreet_id, 
				RTRIM(LpuRegionStreet_HouseSet) as LpuRegionStreet_HouseSet,
				lr.LpuRegion_id,
				lr.LpuRegion_Name,
				lr.LpuRegionType_SysNick,
				lr.Lpu_id
			FROM 
				LpuRegionStreet lrs  with (nolock)
				inner join v_LpuRegion lr with (nolock)
					on lrs.LpuRegion_id = lr.LpuRegion_id and 
					lr.LpuRegionType_SysNick in ('ter','ped') 
					and 
					( (lrs.KLTown_id is not null) or (lrs.KLStreet_id is not null) or (lrs.KLCity_id is not null) )
			where
				{$filters}
		";

		//echo(getDebugSQL($query, $params));die;
		$res = $this->db->query($query, $params);

		if (is_object($res)) {
			$lpuregions_data = $res->result('array');
			if (is_array($lpuregions_data) && count($lpuregions_data) > 0) {
				foreach ($lpuregions_data as $lpuregion_area) {
					if (strlen($lpuregion_area['LpuRegionStreet_HouseSet']) > 0) {
						if (strlen($data['Address_House']) > 0) {
							$this->load->model('AutoAttach_model', 'autoattachmodel');
							if ($this->autoattachmodel->HouseExist(array($data['Address_House']), $lpuregion_area['LpuRegionStreet_HouseSet']) === true) {
								if ($data['Person_Age'] <= 17 && $lpuregion_area['LpuRegionType_SysNick'] == 'ped')
									return array(
										'Lpu_id'=>$lpuregion_area['Lpu_id']
									);
								if ($data['Person_Age'] > 17 && $lpuregion_area['LpuRegionType_SysNick'] != 'ped')
									return array(
										'Lpu_id'=>$lpuregion_area['Lpu_id']
									);
							}
						}
					}
				}
			}
		}
	}
}
