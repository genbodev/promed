<?php
class Stick_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Какая-то проверка
	 */
	function checkLastEvnStickInStacData($data)
	{
		$query = "
			select top 1
				ES.EvnStick_id
			from v_EvnStick ES with(nolock)
				inner join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnStick_mid -- режем по стацу
			where 
				(ES.EvnStick_id <> :EvnStick_id OR :EvnStick_id IS NULL)
				and ESB.EvnStickBase_disDate <= :EvnStick_setDate 
				and ESB.EvnStickBase_disDate >= dateadd(day,-1,:EvnStick_setDate)
				and ES.Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => '', 'MaxDaysLimitAfterStac' => true);
			}
		}

		return array('Error_Msg' => '', 'MaxDaysLimitAfterStac' => false);
	}
	
	/**
	 *	Получение даты выдачи ЛВН
	 */
	function getEvnStickSetdate($data)
	{
		$query = "
			select top 1
				convert(varchar(10),EvnVizitPL.EvnVizitPL_setDate,104) as EvnStick_setDate
			from 
				v_EvnVizitPL EvnVizitPL with(nolock)
			where
				EvnVizitPL.EvnVizitPL_pid = :EvnStick_mid
			order by
				EvnVizitPL.EvnVizitPL_setDate desc
		";
		//echo getDebugSQL($query, $data);exit; 
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Функция запроса различных ФИО / Даты рождения / Пола человека из периодик с даты до даты.
	 *	в $data: Person_id, PersonEvn_insDT, type
	 */
	function getPersonEvnRecords($data) 
	{
		
		$addquery = "";
		
		if(isset($data['PersonEvn_insDTend'])) {
			 $addquery = "and pa.PersonEvn_insDT <= :PersonEvn_insDTend";
		}
		
		$query = "
			select
				pa.PersonEvn_id as PersonEvn_id,
				ISNULL(pa.Person_Fio,'') + ' ' + ISNULL(convert(varchar(10), pa.Person_BirthDay, 104),'') + ' ' + ISNULL(s.Sex_name,'') as Person_Descr,
				pa.Person_Fio,
				pa.Server_id,
				pa.Person_BirthDay,
				s.Sex_name
			from 
				v_Person_all as pa with (nolock)
				left join v_Sex s with (nolock) on s.Sex_id = pa.Sex_id
			where pa.Person_id = :Person_id and pa.PersonEvn_insDT >= :PersonEvn_insDTstart {$addquery}
			
			union all
			
			select
				slct.PersonEvn_id as PersonEvn_id,
				slct.Person_Descr as Person_Descr,
				slct.Person_Fio,
				slct.Server_id,
				slct.Person_BirthDay,
				slct.Sex_name
			from (
				select
					TOP 1
					pa.PersonEvn_id as PersonEvn_id,
					ISNULL(pa.Person_Fio,'') + ' ' + ISNULL(convert(varchar(10), pa.Person_BirthDay, 104),'') + ' ' + ISNULL(s.Sex_name,'') as Person_Descr,
					pa.Person_Fio,
					pa.Server_id,
					pa.Person_BirthDay,
					s.Sex_name
				from 
					v_Person_all as pa with (nolock)
					left join v_Sex s with (nolock) on s.Sex_id = pa.Sex_id
				where pa.Person_id = :Person_id and pa.Person_Fio is not null and pa.PersonEvn_insDT < :PersonEvn_insDTstart order by pa.PersonEvn_insDT desc
			) as slct
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$res = $result->result('array');
		} else {
			$res = array();
		}
		
		// если не нашли, достём последнюю периодику по человеку
		if (count($res) < 1) {
			$addquery2 = " and pa.PersonEvn_insDT <= :PersonEvn_insDTstart";
			if(isset($data['PersonEvn_insDTend'])) {
				 $addquery2 = "and pa.PersonEvn_insDT <= :PersonEvn_insDTend";
			}
		
			$query = "
				select top 1
					pa.PersonEvn_id as PersonEvn_id,
					ISNULL(pa.Person_Fio,'') + ' ' + ISNULL(convert(varchar(10), pa.Person_BirthDay, 104),'') + ' ' + ISNULL(s.Sex_name,'') as Person_Descr,
					pa.Person_Fio,
					pa.Server_id,
					pa.Person_BirthDay,
					s.Sex_name
				from 
					v_Person_all as pa with (nolock)
					left join v_Sex s with (nolock) on s.Sex_id = pa.Sex_id
				where
					pa.Person_id = :Person_id {$addquery2}
				order by
					pa.PersonEvn_insDT
				desc
			";
			
			$result = $this->db->query($query, $data);
			
			if ( is_object($result) ) {
				$res = $result->result('array');
			} else {
				$res = array();
			}
		}
		
		$tempArray = array();
		$response = array();
		
		if(!empty($res) && is_array($res)) {
			foreach($res as $new) {

				if (!empty($new['Person_BirthDay'])) {
					$Person_BirthDay = $new['Person_BirthDay']->getTimestamp();
				} else {
					$Person_BirthDay = '';
				}
				
				switch($data['type']) {
					// проверяем только одинаковые ФИО
					case 1:
						
						if (!in_array($new['Person_Fio'], $tempArray)) {
							$tempArray[] = $new['Person_Fio'];
							$response[] = $new;
						}
					break;
					
					// ФИО и дату рождения
					case 2:
						
						if (!in_array($new['Person_Fio'].$Person_BirthDay, $tempArray))  {
							$tempArray[] = $new['Person_Fio'].$Person_BirthDay;
							$response[] = $new;
						}
					break;
					
					// ФИО и дату рождения и пол
					case 3:
						if (!in_array($new['Person_Fio'].$Person_BirthDay.$new['Sex_name'], $tempArray))  {
							$tempArray[] = $new['Person_Fio'].$Person_BirthDay.$new['Sex_name'];
							$response[] = $new;
						}
					break;
				}
			}
		}

		return $response;
	}
		
	/**
	 *	Добавление связки ЛВН и учетного документа
	 */
	function addEvnLink($data) {
		$queryParams = array(
			'Evn_id' => $data['EvnStickBase_mid'],
			'Evn_lid' => $data['EvnStickBase_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// Проверяем, есть ли добавляемая связка в БД
		$query = "
			select count(EvnLink_id) as cnt
			from v_EvnLink with (nolock)
			where Evn_id = :Evn_id
				and Evn_lid = :Evn_lid
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка наличия в БД добавляемой связки документа с ЛВН)'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при проверке наличия в БД добавляемой связки документа с ЛВН'));
		}
		else if ( !empty($response[0]['cnt']) ) {
			return array(array('Error_Msg' => 'ЛВН уже связан с текущим документом'));
		}

		$query = "
			declare
				@EvnLink_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnLink_ins
				@EvnLink_id = @EvnLink_id output,
				@Evn_id = :Evn_id ,
				@Evn_lid = :Evn_lid ,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @EvnLink_id as EvnLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSQL($query, $queryParams); exit();

		return $this->queryResult($query, $queryParams);
	}


	/**
	 *	Удаление связки ЛВН и учетного документа
	 */
	function removeEvnLink($data, $evnStickWorkRelease, $EvnStick_id) {

		$filter = '';

		$begDate = null;
		$endDate = null;

		
		if(getRegionNick() == 'perm' && !empty($data['EvnStick_mid'])) {
			$query = "
				SELECT EvnPL_LastVizitDT
				from
					EvnPL
				where EvnPL_id = :EvnPL_id
			";
			$queryParams = array(
				'EvnPL_id' => $data['EvnStick_mid']
			);
			$EvnPL_LastVizitDT = $this->getFirstResultFromQuery($query, $queryParams);
		}
		
		foreach ($evnStickWorkRelease as $rec) {
			$begDateOne = date('Y-m-d', strtotime($rec['EvnStickWorkRelease_begDate']));
			$endDateOne = date('Y-m-d', strtotime($rec['EvnStickWorkRelease_endDate']));

			if (empty($begDate) || $begDate > $begDateOne) {
				$begDate = $begDateOne;
			}

			if (empty($endDate) || $endDate < $endDateOne) {
				$endDate = $endDateOne;
			}
		}

		if (!empty($begDate)) {
			$endDate = date('Y-m-d', strtotime($endDate) + 24 * 60 * 60); // + 1 день
			$filter .= " and (Evn.Evn_setDate > '{$endDate}' or (Evn.Evn_disDate is not null and Evn.Evn_disDate < '{$begDate}'))";
		}


		if (!empty($data['EvnStick_setDate'])) {
			$begDate = date('Y-m-d', strtotime($data['EvnStick_setDate']));
			$endDate = date('Y-m-d', strtotime($data['EvnStick_setDate']));
			$filter .= " and (Evn.Evn_setDate > '{$endDate}' or (Evn.Evn_disDate is not null and Evn.Evn_disDate < '{$begDate}'))";
		}

		if (getRegionNick() != 'kz') {
			$filter .= " and SI.StickIrregularity_Code not in('24', '25')";
		} else {
			$filter .= " and SI.StickIrregularity_Code not in('3')";
		}

		// Проверяем, есть ли связанные события, с которыми теперь не пересекается ни одно освобождение от работы
		$query = "
			select
				EL.EvnLink_id,
				EL.Evn_id
			from
				EvnLink EL (nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
				inner join v_Evn Evn with (nolock) on Evn.Evn_id = EL.Evn_id
				left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
				left join v_StickIrregularity SI with (nolock) on SI.StickIrregularity_id = ES.StickIrregularity_id
			where
				Evn_lid = :EvnStick_id
				and ESB.EvnStickBase_rid != EL.Evn_id
				{$filter}
		";

		//echo getDebugSQL($query, array('EvnStick_id' => $EvnStick_id));die;
		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка наличия в БД добавляемой связки документа с ЛВН)'));
		}

		$removeLink = false;
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnLink_id'])) {

			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnLink_del
					@EvnLink_id = :EvnLink_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			foreach ($response as $key => $value) {
				if (
					!empty($data['EvnStick_mid']) 
					&& $value['Evn_id'] == $data['EvnStick_mid']
					&& !(
						!empty($EvnPL_LastVizitDT) && !empty($data['EvnStick_disDate'])
						&& getRegionNick() == 'perm'
						&& date_format($EvnPL_LastVizitDT, 'Y-m-d') == date('Y-m-d', strtotime($data['EvnStick_disDate']))
						&& $data['StickLeaveType_id'] == 7
						&& $data['StickIrregularity_id'] == 1
					)
				) {
					$removeLink = true;
				}
				if(
					getRegionNick() != 'perm' 
					|| !(
						!empty($EvnPL_LastVizitDT) && !empty($data['EvnStick_disDate'])
						&& date_format($EvnPL_LastVizitDT, 'Y-m-d') == date('Y-m-d', strtotime($data['EvnStick_disDate']))
						&& $data['StickLeaveType_id'] == 7
						&& $data['StickIrregularity_id'] == 1
					)
				) {
					$result = $this->db->query($query, array(
						'EvnLink_id' => $value['EvnLink_id']
					));
				}
				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при удалении связи ЛВН с учётным документом'));
				}
			}

			if ($removeLink) {
				return array(array('Error_Msg' => 'Сохранение невозможно: общий период нетрудоспособности (+ 1 день) не пересекается с периодом лечения и дата выдачи ЛВН не входит в период лечения по случаю.'));
			}
		} else {
			return true;
		}

		return true;
	}


	/**
	 *	Какая-то проверка
	 */
	function checkEvnStickOrg($data) {
		$query = "
			select
				count(EvnStickBase_id) as cnt
			from
				v_EvnStickBase ESB with (nolock)
			where
				ESB.EvnStickBase_id != ISNULL(:EvnStickBase_id, 0)
				and (ESB.EvnStickBase_pid = :EvnStickBase_pid or ESB.EvnStickBase_id = :EvnStickBase_pid)
				and ESB.Org_id = :Org_id
				and (ESB.EvnStickBase_mid = :EvnStickBase_mid
					or exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = :EvnStickBase_mid and Evn_lid = ESB.EvnStickBase_id)
				)
		";

		$queryParams = array(
			'EvnStickBase_id' => $data['EvnStick_id'],
			'EvnStickBase_mid' => $data['EvnStick_mid'],
			'EvnStickBase_pid' => $data['EvnStickDop_pid'],
			'Org_id' => $data['Org_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}		
	}

	/**
	 *	Проверка возможности удалить ТАП или КВС
	 */
	function checkEvnDeleteAbility($data) {
		/*$query = "
			select
				 ESB.EvnStickBase_id as EvnStick_id
				,convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate -- Дата выдачи
				,convert(varchar(10), ESB.EvnStickBase_disDT, 104) as EvnStick_disDate -- Дата закрытия
				,RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser -- Серия
				,RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num -- Номер
				,case
					when EPS.EvnPS_id is not null then 'КВС'
					when EPL.EvnPL_id is not null then 'ТАП'
					else ''
				end as ParentEvn_Type
				,ISNULL(EPS.EvnPS_NumCard, EPL.EvnPL_NumCard) as ParentEvn_NumCard -- Номер ТАП или КВС
				,ISNULL(L.Lpu_Nick, '') as Lpu_Nick -- Наименование ЛПУ
			from v_EvnStickBase ESB with (nolock)
				inner join v_EvnLink EL with (nolock) on EL.Evn_lid = ESB.EvnStickBase_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EL.Evn_id
				left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EL.Evn_id
				left join v_Lpu L with (nolock) on L.Lpu_id = ISNULL(EPS.Lpu_id, EPL.Lpu_id)
			where
				ESB.EvnStickBase_pid = :Evn_id
				and EL.Evn_id != :Evn_id
			order by
				ESB.EvnStickBase_setDT
		";*/
		$query = "
			select
				 ESB.EvnStickBase_id as EvnStick_id
				,convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate -- Дата выдачи
				,convert(varchar(10), ESB.EvnStickBase_disDT, 104) as EvnStick_disDate -- Дата закрытия
				,RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser -- Серия
				,RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num -- Номер
			from v_EvnStickBase ESB with (nolock)
			where
				ESB.EvnStickBase_pid = :Evn_id
			order by
				ESB.EvnStickBase_setDT
		";
		
		// echo getDebugSQL($query, array('EvnStick_pid' => $data['EvnStick_pid'], 'Lpu_id' => $data['Lpu_id']));

		$result = $this->db->query($query,
			array(
				'Evn_id' => $data['Evn_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Определяем являеться ли ЛВН дубликатом
	 *
	 * Duplikat Дупликат "П" :))))) пофиг, исправлять не буду, добавим немного юмора в этот бездушный код
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _isDuplikat($EvnStick_id){
		$isDuplikat = false;

		$query = "
			SELECT
				EvnStickBase_IsOriginal
			FROM
				v_EvnStickBase with(nolock)
			WHERE
				v_EvnStickBase.EvnStickBase_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if(
			isset($response[0]['EvnStickBase_IsOriginal']) &&
			! empty($response[0]['EvnStickBase_IsOriginal']) &&
			is_numeric($response[0]['EvnStickBase_IsOriginal']) &&
			$response[0]['EvnStickBase_IsOriginal'] == 2
		){
			// ЛВН является дубликатом
			$isDuplikat = true;
		}

		return $isDuplikat;
	}

	/**
	 * Определям является ли ЛВН оригиналом
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _isOriginal($EvnStick_id){
		$isOriginal = false;

		$query = "
			SELECT
				EvnStickBase_IsOriginal
			FROM
				v_EvnStickBase with(nolock)
			WHERE
				v_EvnStickBase.EvnStickBase_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if(
			isset($response[0]['EvnStickBase_IsOriginal']) &&
			! empty($response[0]['EvnStickBase_IsOriginal']) &&
			is_numeric($response[0]['EvnStickBase_IsOriginal']) &&
			$response[0]['EvnStickBase_IsOriginal'] == 1
		){
			// ЛВН является дубликатом
			$isOriginal = true;
		}

		return $isOriginal;
	}

	/**
	 * Существует ли дубликат
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _hasDuplikat($EvnStick_id){
		return (bool) $this->_getDuplikat($EvnStick_id);
	}

	/**
	 * Получаем id дубликата
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _getDuplikat($EvnStick_id){

		$query = "
			SELECT
				EvnStick_id
			FROM
				v_EvnStick_all with(nolock)
			WHERE
				v_EvnStick_all.EvnStick_oid = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if( ! isset($response[0]['EvnStick_id']) || empty($response[0]['EvnStick_id']) || ! is_numeric($response[0]['EvnStick_id'])){
			return false;
		}

		return $response[0]['EvnStick_id'];
	}

	/**
	 * Существует ли оригинал
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _existOriginal($EvnStick_id){
		return (bool) $this->_getOriginal($EvnStick_id);

	}

	/**
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _getOriginal($EvnStick_id){
		$query = "
			SELECT
				EvnStickBase_oid
			FROM
				v_EvnStickBase with(nolock)
			WHERE
				v_EvnStickBase.EvnStickBase_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if( ! isset($response[0]['EvnStickBase_oid']) || empty($response[0]['EvnStickBase_oid']) || ! is_numeric($response[0]['EvnStickBase_oid'])){
			return false;
		}

		return $response[0]['EvnStickBase_oid'];
	}

	/**
	 * Существует ли продолжение
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _hasProdoljenie($EvnStick_id){
		return (bool) $this->_getProdoljenie($EvnStick_id);
	}


	/**
	 * Получаем id продолжения
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _getProdoljenie($EvnStick_id){

		$query = "
			SELECT
				EvnStick_id
			FROM
				v_EvnStick with(nolock)
			WHERE
				v_EvnStick.EvnStick_prid = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array') ;

		if ( ! is_array($response) ) {
			return false;
		}

		if( ! isset($response[0]['EvnStick_id']) || empty($response[0]['EvnStick_id']) || ! is_numeric($response[0]['EvnStick_id'])){
			return false;
		}

		return $response[0]['EvnStick_id'];
	}

	/**
	 * У дубликата есть продолжение
	 */
	private function _existUdalenProd($EvnStick_id){

		$existUdalenProd = false;

		if($this->_isDuplikat($EvnStick_id) == true){
			$original_id = $this->_getOriginal($EvnStick_id);
			if($this->_existOriginal($original_id) && $this->_isDeleted($original_id) && $this->_hasProdoljenie($original_id)){
				$existUdalenProd = true;
			}


		} else if($this->_isOriginal($EvnStick_id) == true){
			$duplikat_id = $this->_getDuplikat($EvnStick_id);

			if($this->_hasDuplikat($EvnStick_id) && $this->_isDeleted($duplikat_id) && $this->_hasProdoljenie($duplikat_id)){
				$existUdalenProd = true;
			}
		}

		return $existUdalenProd;
	}


	/**
	 * Есть ли у ЛВН метка "удален"
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _isDeleted($EvnStick_id){
		$isDeleted = false;


		$query = "
			SELECT
				Evn.Evn_deleted
			FROM
				Evn
			WHERE
				Evn.Evn_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if(
			isset($response[0]['Evn_deleted']) &&
			! empty($response[0]['Evn_deleted']) &&
			is_numeric($response[0]['Evn_deleted']) &&
			$response[0]['Evn_deleted'] == 2
		){
			// ЛВН удален
			$isDeleted = true;
		}

		return $isDeleted;
	}


	/**
	 * Определяем, находить ли ЛВН в очереди на удаление
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _inQueueDeleted($EvnStick_id){

		// ЛВН не находиться в очереди на удаление
		$inQueue = false;

		$query = "
			SELECT
				ESB.EvnStickBase_IsDelQueue as IsDelQueue
			FROM
				v_EvnStickBase ESB WITH (nolock)
			WHERE
				ESB.EvnStickBase_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if( ! isset($response[0]['IsDelQueue']) || empty($response[0]['IsDelQueue']) || ! is_numeric($response[0]['IsDelQueue'])){
			return false;
		}

		if($response[0]['IsDelQueue'] == 2){
			// ЛВН находиться в очереди на удаление
			$inQueue = true;
		}

		return $inQueue;
	}

	/**
	 * Получаем порядок выдачи
	 *
	 * @param $EvnStick_id
	 * @return bool
	 */
	private function _getStickOrderCode($EvnStick_id){


		$query = "
			SELECT
				SO.StickOrder_Code as StickOrder_Code
			FROM
				v_EvnStickBase ESB WITH (nolock)
				inner join StickOrder SO with(nolock) on SO.StickOrder_id = ESB.StickOrder_id
			WHERE
				ESB.EvnStickBase_id = :EvnStick_id
		";

		$result = $this->db->query($query, array('EvnStick_id' => $EvnStick_id));

		if ( ! is_object($result)){
			return false;
		}

		$response = $result->result('array');

		if ( ! is_array($response) ) {
			return false;
		}

		if( ! isset($response[0]['StickOrder_Code']) || empty($response[0]['StickOrder_Code']) || ! is_numeric($response[0]['StickOrder_Code'])){
			return false;
		}

		return $response[0]['StickOrder_Code'];

	}

	/**
	 *	Удаление ЛВН
	 */
	function deleteEvnStick($data) {
		$query = "
			select top 1 EvnStickBase_mid
			from v_EvnStickBase with (nolock)
			where EvnStickBase_id = :EvnStick_id
		";
		$result = $this->db->query($query, array('EvnStick_id' => $data['EvnStick_id']));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора родительского события)'));
		}

		$rec = $result->result('array');

		$result = $this->db->query("
			select top 1 
				ESB.EvnStickBase_id
			from
				EvnStickBase ESB with(nolock)
				left join v_StickFSSData sfd (nolock) on sfd.StickFSSData_id = ESB.StickFSSData_id
			where
				ESB.EvnStickBase_id = :EvnStick_id
				and (esb.EvnStickBase_IsFSS != 2 or sfd.StickFSSDataStatus_id = 4)
				and isnull(ESB.EvnStickBase_IsInReg, 1) != 2 and isnull(ESB.EvnStickBase_IsPaid, 1) != 2
				and (
					exists (
						select top 1 *
						from 
							v_EvnStickWorkRelease ESWR with(nolock)
							inner join Lpu L with(nolock) on L.Org_id = ESWR.Org_id
						where 
							ESWR.EvnStickBase_id = ESB.EvnStickBase_id
							and L.Lpu_id != :Lpu_id
					)
					or (
						ESB.StickLeaveType_rid is not null
						and ESB.Lpu_outid is not null
						and ESB.Lpu_outid != :Lpu_id
					)
				)
		", array(
			'EvnStick_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['session']['lpu_id']
		));
		if ( is_object($result) ) {
			$result = $result->result('array');
			if( !empty($result) ) {
				return array(array('Error_Msg' => 'Удаление ЛВН невозможно: ЛВН содержит периоды нетрудоспособности и/или исход, добавленные в другой МО.'));
			}
		}

		if (getRegionNick() != 'kz') {
			$this->load->model("Options_model");
			$options = $this->Options_model->getOptionsGlobals($data);

			if(
				!empty($options['globals']['lpu_cancel_stick_access'])
				&& $options['globals']['lpu_cancel_stick_access'] == 1
			) {// аннулировать разрешено только открывшей МО
				$lpu_cancel_stick_access = 1;

				$fields = "
					eswr2.EvnStickWorkRelease_id,
					isnull(eswr2.EvnStickWorkRelease_IsPaid, 1) as EvnStickWorkRelease_IsPaid,
					eswr2.Org_id,
					eswr2.Lpu_id,
					eswr2.MedPersonal_id,
				";

				$ESWRQuery = "
					outer apply (
						select top 1
							eswrFirst.EvnStickWorkRelease_id,
							eswrFirst.EvnStickWorkRelease_IsPaid,
							eswrFirst.Org_id,
							lpu.Lpu_id,
							case
								when eswrFirst.MedPersonal_id = :MedPersonal_id
									or eswrFirst.MedPersonal2_id = :MedPersonal_id
									or eswrFirst.MedPersonal3_id = :MedPersonal_id
								then :MedPersonal_id else 0
							end as MedPersonal_id
						from 
							v_EvnStickWorkRelease eswrFirst (nolock)
							left join v_Lpu lpu with (nolock) on lpu.Org_id = eswrFirst.Org_id
						where 
							eswrFirst.EvnStickBase_id = esb.EvnStickBase_id
						order by
							eswrFirst.evnStickWorkRelease_begDT
					) as eswr2
				";
			} else { // открывшей и закрывшей МО
				$lpu_cancel_stick_access = 2;

				$fields = "
					eswr2.EvnStickWorkRelease_id,
				";

				$intersect = array();

				if (!empty($data['session']['ARMList'])) {
					$intersect = array_intersect(array('lvn', 'mstat'), $data['session']['ARMList']);
				}

				if ( //Оператор, Статистик, Регистратор ЛВН.
					!empty($intersect)
					|| empty($data['session']['medpersonal_id'])
				) {
					$filter = "";
					$join = "";
				} else {
					$filter = "
						and (					
							eswrAny.MedPersonal_id = :MedPersonal_id
							or eswrAny.MedPersonal2_id = :MedPersonal_id
							or eswrAny.MedPersonal3_id = :MedPersonal_id							
							or (
								esb.MedPersonal_id = :MedPersonal_id
								and isnull(esb.EvnStickBase_IsPaid, 1) = 2
							)
						)
					";

				}
				
				if (array_key_exists('linkedLpuIdList', $data['session'])) {
					$filter .= " and (eswrAny.Org_id = :Org_id or lpu.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")) ";
				} else {
					$filter .= " and eswrAny.Org_id = :Org_id ";
				}

				$ESWRQuery = "
					outer apply (
						select top 1 
							eswrAny.EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease eswrAny (nolock)
							left join v_Lpu lpu with (nolock) on lpu.Org_id = eswrAny.Org_id
						where
							isnull(eswrAny.EvnStickWorkRelease_IsPaid, 1) = 2
							{$filter}
					) as eswr2
				";
			}
			
			$dbRegES = $this->load->database('registry_es', true);
			$result_check = $dbRegES->query("
				select
					{$fields}
					case when esb.EvnStickBase_IsInReg = 2 and ISNULL(esb.EvnStickBase_IsPaid, 1) = 1 then 1 else 0 end + ISNULL(eswr.countInRegNotPaid, 0) as countInRegNotPaid,
					case when esb.EvnStickBase_IsPaid = 2 then 1 else 0 end + ISNULL(eswr.countPaid, 0) as countPaid,
					case when ISNULL(esb.EvnStickBase_IsPaid, 1) = 1 then 1 else 0 end + ISNULL(eswr.countNotPaid, 0) as countNotPaid,
					case when exists(select top 1 esb2.EvnStickBase_id from v_EvnStickBase esb2 (nolock) where esb2.EvnStickBase_oid = esb.EvnStickBase_id) then 1 else 0 end as existDuplicate		
				from
					EvnStickBase esb (nolock)
					outer apply (
						select
							sum(case when eswr.EvnStickWorkRelease_IsInReg = 2 and ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1 then 1 else 0 end) as countInRegNotPaid,
							sum(case when eswr.EvnStickWorkRelease_IsPaid = 2 then 1 else 0 end) as countPaid,
							sum(case when ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1 then 1 else 0 end) as countNotPaid
						from
							v_EvnStickWorkRelease eswr (nolock)
						where
							eswr.EvnStickBase_id = esb.EvnStickBase_id 
					) eswr
					{$ESWRQuery}
				where
					esb.EvnStickBase_id = :EvnStick_id
			", array(
				'EvnStick_id' => $data['EvnStick_id'],
				'Org_id' => $data['session']['org_id'],
				'MedPersonal_id' => isset($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : 0
			));

			if ( is_object($result_check) ) {
				$resp_check = $result_check->result('array');

				if (!empty($resp_check[0])) {
					// •	Если у ЛВН или хотя бы у одного освобождения от работы заполнен признак нахождения в реестре (_IsInReg)
					// и одновременно не заполнен признак «принят ФСС» (_IsPaid), то при попытке удалить ЛВН,
					// сообщение об ошибке «ЛВН отправлен в ФСС. Функция удаления временно недоступна». ЛВН не удалять.
					if ($resp_check[0]['countInRegNotPaid'] > 0) {
						return array(array('Error_Msg' => 'ЭЛН отправлен в ФСС. Функция аннулирования временно недоступна'));
					} else if ($resp_check[0]['countPaid'] > 0) {

						$intersect = array_intersect(array('lvn', 'mstat'), $data['session']['ARMList']);
						if ( $lpu_cancel_stick_access == 1) {
							if( 
								!(
									(//текущий врач есть в освобождении или имеет роль: оператора, статистика или регистратора ЛВН
										!empty($intersect)
										|| empty($data['session']['medpersonal_id'])
										|| $resp_check[0]['MedPersonal_id'] != 0
									)
									&& $resp_check[0]['EvnStickWorkRelease_IsPaid'] == 2 // принят в ФСС
									&& (
										$resp_check[0]['Org_id'] == $data['session']['org_id'] || // освобождение выписано в текущей МО
										(array_key_exists('linkedLpuIdList', $data['session']) && in_array($resp_check[0]['Lpu_id'], $data['session']['linkedLpuIdList'])) // или МО-правопреемнике
									)
								)
							) {
								return array(array('Error_Msg' => 'Аннулирование может быть выполнено только врачом, выписавшим ЭЛН'));
							}
						} else {
							if( empty($resp_check[0]['EvnStickWorkRelease_id']) ) {
								return array(array('Error_Msg' => 'Аннулирование ЭЛН недоступно.'));
							}
						}

						if (empty($data['StickCauseDel_id'])) {
							return array(array(
								'Error_Msg' => 'YesNo',
								'Cancel_Error_Handle' => true,
								'Alert_Code' => 705,
								'countNotPaid' => $resp_check[0]['countNotPaid'],
								'existDuplicate' => $resp_check[0]['existDuplicate'],
								'Alert_Msg' => "Необходимо выбрать причину прекращения действия ЭЛН"
							));
						}

						$query = "
							select top 1
								ESprev.EvnStick_id as EvnStickPrev_id,
								ESprev.EvnStick_Num as EvnStickPrev_Num,
								ESBprev.EvnStickBase_IsInReg as EvnStickPrev_isInReg,
								ESBprev.EvnStickBase_IsPaid as EvnStickPrev_IsPaid,
								ESnext.EvnStick_id as EvnStickNext_id,
								ESnext.EvnStick_Num as EvnStickNext_Num
							from
								v_EvnStick ES with (nolock)
								left join v_EvnStick ESprev with (nolock) on ESprev.EvnStick_id = ES.EvnStick_prid
								left join v_EvnStickBase ESBprev with (nolock) on ESBprev.EvnStickBase_id = ESprev.EvnStick_id
								left join v_EvnStick ESnext with (nolock) on ESnext.EvnStick_prid = ES.EvnStick_id
							where
								ES.EvnStick_id = :EvnStick_id

							union all

							select top 1
								ESDprev.EvnStickDop_id as EvnStickPrev_id,
								ESDprev.EvnStickDop_Num as EvnStickPrev_Num,
								ESBprev.EvnStickBase_IsInReg as EvnStickPrev_isInReg,
								ESBprev.EvnStickBase_IsPaid as EvnStickPrev_IsPaid,
								ESDnext.EvnStickDop_id as EvnStickNext_id,
								ESDnext.EvnStickDop_Num as EvnStickNext_Num
							from
								v_EvnStickDop ESD with (nolock)
								left join v_EvnStickDop ESDprev with (nolock) on ESDprev.EvnStickDop_id = ESD.EvnStickDop_prid
								left join v_EvnStickBase ESBprev with (nolock) on ESBprev.EvnStickBase_id = ESDprev.EvnStickDop_id
								left join v_EvnStickDop ESDnext with (nolock) on ESDnext.EvnStickDop_prid = ESD.EvnStickDop_id
							where
								ESD.EvnStickDop_id = :EvnStick_id
						";
						$resp_check = $this->queryResult($query, array('EvnStick_id' => $data['EvnStick_id']));

						if (!empty($resp_check[0]['EvnStickNext_id'])) {
							// у ЭЛН есть продолжение
							// Проверяем наличие актуального дубликата
							$query = "
								select top 1
									ESB.EvnStickBase_id
								from 
									v_EvnStickBase ESB with (nolock)
								where
									ESB.EvnStickBase_oid = :EvnStickBase_id
									and isnull(ESB.EvnSTickBase_IsDelQueue, 1) = 1
									and isnull(ESB.EvnStickBase_IsInRegDel, 1) = 1
							";
							$check_dub = $this->getFirstRowFromQuery($query, array(
								'EvnStickBase_id' => $data['EvnStick_id']
							));
							if ( !empty($check_dub['EvnStickBase_id']) ) {
								if( empty($data['ignoreStickHasProlongation']) ) {
									return array(array(
										'Error_Msg' => 'YesNo',
										'Alert_Code' => 706,
										'Alert_Msg' => "Вы аннулируете ЭЛН, на который оформлено продолжение: {$resp_check[0]['EvnStickNext_Num']}. Продолжить?"
									));
								}
							} else {
								return array(array(
									'Error_Msg' => "На данный ЭЛН оформлено продолжение: {$resp_check[0]['EvnStickNext_Num']}. Прежде чем аннулировать этот (первичный) ЭЛН, необходимо создать на него дубликат."
								));
							}
						}




						// не удаляем ЛВН, а только проставляем признак "В очереди на удаление"
						$resp_del = $this->queryResult("
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
			
							exec p_EvnStick_setIsDelQueue
								@EvnStick_id = :EvnStick_id,
								@StickCauseDel_id = :StickCauseDel_id,
								@EvnStickBase_IsDelQueue = 2,
								@Lpu_did = :Lpu_did,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
			
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						", array(
							'EvnStick_id' => $data['EvnStick_id'],
							'StickCauseDel_id' => $data['StickCauseDel_id'],
							'Lpu_did' => $data['Lpu_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$resp_del[0]['IsDelQueue'] = 1;
						return $resp_del;
					}
				}
			} else {
				return array(array(
					'Error_Msg' => "Ошибка при получении данных о ЛВН"
				));
			}
		}

		if (!empty($rec[0]['EvnStickBase_mid']) && $rec[0]['EvnStickBase_mid'] == $data['EvnStick_mid']){
			// Проверки на наличие связанных ЛВН refs #154460
			// Если текущий ЛВН, выбран в качестве «ЛВН первичного» в ЛВН-продолжении
			$resp_check = $this->queryResult("
				select
					ES.EvnStick_id as id,
					ES.EvnStick_Num as num
				from
					v_EvnStick ES (nolock)
				where
					ES.EvnStick_prid = :EvnStick_id
					
				union all
				
				select
					ES.EvnStickDop_id as id,
					ES.EvnStickDop_Num as num
				from
					v_EvnStickDop ES (nolock)
				where
					ES.EvnStickDop_prid = :EvnStick_id
			", array(
				'EvnStick_id' => $data['EvnStick_id']
			));

			if (!empty($resp_check[0]['id'])) {
				$resp_stick = $this->queryResult("
					select
						ESB.EvnStickBase_id,
						RESS.RegistryESStorage_id,
						ESWR.EvnStickWorkRelease_id
					from
						v_EvnStickBase ESB with (nolock)
							outer apply (
							select top 1
								RESS.RegistryESStorage_id
							from
								v_RegistryESStorage RESS with (nolock)
							where
								RESS.EvnStickBase_id = ESB.EvnStickBase_id
						) RESS
						outer apply (
							select top 1
								ESWR.EvnStickWorkRelease_id
							from
								v_EvnStickWorkRelease ESWR with (nolock)
							where
								ESWR.EvnStickWorkRelease_IsPaid = 2
								and ESWR.EvnStickBase_id = ESB.EvnStickBase_id
						) ESWR
					where
						ESB.EvnStickBase_id = :EvnStick_id
				", array(
					'EvnStick_id' => $data['EvnStick_id']
				));
				// Удаляемый ЛВН является электронным
				if (!empty($resp_stick[0]['RegistryESStorage_id']) ) {
					if (empty($data['ignoreStickHasProlongation'])) {
						return array(array(
							'Error_Msg' => 'YesNo',
							'Alert_Code' => 706,
							'Alert_Msg' => "Вы удаляете ЛВН на который оформлено продолжение: {$resp_check[0]['num']}. Продолжить?"
						));
					}
				} else {
					// Проверяем есть ли на ЛВН дубликат
					$query = "
						select top 1
							ESB.EvnStickBase_id
						from
							v_EvnStickBase ESB with (nolock)
						where
							ESB.EvnStickBase_oid = :EvnStick_id
					";

					$dub_check = $this->getFirstRowFromQuery($query, array('EvnStick_id' => $data['EvnStick_id']));
					if (empty($dub_check['EvnStickBase_id'])) {
						return array(array(
							'Error_Msg' => "На данный ЛВН оформлено продолжение: {$resp_check[0]['num']}. Прежде чем удалять этот (первичный) ЛВН, необходимо создать на него дубликат."
						));
					}
					if (empty($data['ignoreStickHasProlongation'])) {
						// Удаляемый ЛВН выписан на бланке и имеет дубликат, то предупреждение пользователю
						return array(array(
							'Error_Msg' => 'YesNo',
							'Alert_Code' => 706,
							'Alert_Msg' => "Вы удаляете ЛВН на который оформлено продолжение: {$resp_check[0]['num']}. Продолжить?"
						));
					}
				}
			}

			// ---------------------------------------------------------------------------------------------------------
			// https://redmine.swan.perm.ru/issues/5992
			// 2.1. Если ЛВН с типом занятости = "По основному месту работы", то нужно проверить на наличие ЛВН по совместительству, при их
			// наличии выдать сообщение "Удаление невозможно, т.к. присутствуют ЛВН по совместительству: ЛВН Серия Номер Дата выдачи" и с
			// переводом каретки привести все ЛВН по совместительству.
			$query = "
				select
					 ESD.EvnStickDop_id
					,convert(varchar(10), ESD.EvnStickDop_setDT, 104) as EvnStickDop_setDate -- Дата выдачи
					,convert(varchar(10), ESD.EvnStickDop_disDT, 104) as EvnStickDop_disDate -- Дата закрытия
					,RTRIM(ISNULL(ESD.EvnStickDop_Ser, '')) as EvnStickDop_Ser -- Серия
					,RTRIM(ISNULL(ESD.EvnStickDop_Num, '')) as EvnStickDop_Num -- Номер
				from
					v_EvnStickDop ESD with (nolock)
				where
					ESD.EvnStickDop_pid = :EvnStick_id
				order by
					ESD.EvnStickDop_setDT
			";

			$result = $this->db->query($query,
				array(
					'EvnStick_id' => $data['EvnStick_id']
				)
			);

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка возможности удалить ЛВН)'));
			}

			$response = $result->result('array');

			if ( !is_array($response) ) {
				return array(array('Error_Msg' => 'Ошибка при проверке возможности удалить ЛВН'));
			}
			else if ( count($response) > 0 ) {
				$error = "<div>Удаление невозможно, т.к. присутствуют ЛВН по совместительству:</div>";

				foreach ( $response as $array ) {
					$error .= "<div>"
						. $array['EvnStickDop_Ser'] . " "
						. $array['EvnStickDop_Num'] . ", выдан "
						. $array['EvnStickDop_setDate']
						. "</div>"
					;
				}

				return array(array('Error_Msg' => $error));
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			if (getRegionNick() != 'kz') {
				if (empty($data['ignoreStickFromFSS'])) {
					// если ЛВН имеет флаг «ЛВН из ФСС», то:
					// если связанный с ЛВН запрос в ФСС имеет статус, отличный от статусов [«Ошибка отправки», «ЭЛН не подтверждён»], то выходит предупреждение: «По данному ЛВН создан запрос в ФСС или уже получен положительный ответ, Вы действительно хотите его удалить?» (кнопки «Да», «Нет»).
					$resp_sfd = $this->queryResult("
						select top 1
							sfd.StickFSSDataStatus_id
						from
							v_EvnStickBase esb (nolock)
							inner join v_StickFSSData sfd (nolock) on sfd.StickFSSData_id = esb.StickFSSData_id
						where
							esb.EvnStickBase_id = :EvnStick_id
							and esb.EvnStickBase_IsFSS = 2
							and sfd.StickFSSDataStatus_id in (1,2,4)
					", array(
						'EvnStick_id' => $data['EvnStick_id']
					));

					if (!empty($resp_sfd[0]['StickFSSDataStatus_id'])) {
						if (in_array($resp_sfd[0]['StickFSSDataStatus_id'], array(1,2))) {
							return array(array('Error_Msg' => 'Удаление невозможно: по данному ЛВН ещё не получен ответ на запрос в ФСС'));
						} elseif (in_array($resp_sfd[0]['StickFSSDataStatus_id'], array(4))) {
							return array(array(
								'Error_Msg' => 'YesNo',
								'Alert_Code' => 704,
								'Alert_Msg' => "ЭЛН будет удалён из учётного документа, аннулирование ЭЛН выполнено не будет. Продолжить?"
							));
						}
					}
				}
			}
			// ---------------------------------------------------------------------------------------------------------

			// Если текущий ЛВН сохранен в качестве ЛВН продолжения в предыдущем ЛВН
			$err = $this->updateNextStickLink($data, 'del');
			if (!empty($err['Error_Msg'])) {
				return array($err);
			}

			// Удаление ЛВН
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnStick_del
					@EvnStick_id = :EvnStick_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'pmUser_id' => $data['pmUser_id']
			);
		}
		else {
			$query = "
				select 
					ESD.EvnStickDop_Ser, 
					ESD.EvnStickDop_Num, 
					ESD.EvnStickDop_setDate
				from
					v_EvnStickDop ESD (nolock)
					inner join EvnLink EL (nolock) on EL.Evn_lid = ESD.EvnStickDop_id
				where 
					ESD.EvnStickDop_pid = :EvnStick_id
					and EL.Evn_id = :EvnStick_mid
			";
			// Проверка на существование ссылок на ЛВН по совместительству, в текущем случае
			$response = $this->queryResult($query, array( 
				'EvnStick_id' => $data['EvnStick_id'],
				'EvnStick_mid' => $data['EvnStick_mid']
			));
			if(!empty($response)) {

				$error = "<div>Удаление невозможно, т.к. присутствуют ЛВН по совместительству:</div>";
				foreach ( $response as $array ) {
					$error .= "<div>"
						. $array['EvnStickDop_Ser'] . " "
						. $array['EvnStickDop_Num'] . ", выдан "
						. $array['EvnStickDop_setDate']->format('d.m.Y')
						. "</div>"
					;
				}
				return array(array('Error_Msg' => $error));
			}

			// Если текущий ЛВН сохранен в качестве ЛВН продолжения в предыдущем ЛВН
			$err = $this->updateNextStickLink($data, 'del');
			if (!empty($err['Error_Msg'])) {
				return array($err);
			}

			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000),
					@id bigint;

				set @id = (select top 1 EvnLink_id from EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = :EvnStick_id);

				exec p_EvnLink_del
					@EvnLink_id = @id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'EvnStick_mid' => $data['EvnStick_mid']
			);
		}

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление ' . ($rec[0]['EvnStickBase_mid'] == $data['EvnStick_mid'] ? 'ЛВН' : 'связи ЛВН с текущим документом') . ')'));
		}
	}

	/**
	 * Восстановление ЛВН
	 * @param $data
	 * @return array|false
	 */
	function undoDeleteEvnStick($data) {
		// Возможно восстановить ЛВН с признаком «в очереди на удаление» и без признака «В реестре на удалении» (IsInRegDel).
		$resp_check = $this->queryResult("
			select
				esb.EvnStickBase_id,
				ISNULL(esb.EvnStickBase_IsInRegDel, 1) as EvnStickBase_IsInRegDel,
				ISNULL(esb.EvnStickBase_IsDelQueue, 1) as EvnStickBase_IsDelQueue
			from
				EvnStickBase esb (nolock)
			where
				esb.EvnStickBase_id = :EvnStick_id
		", array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		if (!empty($resp_check[0])) {
			if ($resp_check[0]['EvnStickBase_IsInRegDel'] == 2) {
				return array('Error_Msg' => 'Невозможно восстановить ЛВН, т.к. он включён в реестр');
			}
			if ($resp_check[0]['EvnStickBase_IsDelQueue'] == 1) {
				return array('Error_Msg' => ''); // уже восстановлён
			}

			$resp_del = $this->queryResult("
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnStick_setIsDelQueue
					@EvnStick_id = :EvnStick_id,
					@StickCauseDel_id = null,
					@EvnStickBase_IsDelQueue = 1,
					@Lpu_did = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'EvnStick_id' => $data['EvnStick_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			return $resp_del;
		} else {
			return array('Error_Msg' => 'Не найден ЛВН для восстановления');
		}

		return array('Error_Msg' => '');
	}


	/**
	 *	Удаление записи по уходу
	 */
	function deleteEvnStickCarePerson($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnStickCarePerson_del
				@EvnStickCarePerson_id = :EvnStickCarePerson_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'EvnStickCarePerson_id' => $data['EvnStickCarePerson_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление ошибки клинической диагностики)'));
		}
	}


	/**
	 *	Удаление справки учащегося
	 */
	function deleteEvnStickStudent($data) {
		$query = "
			select top 1 EvnStickBase_mid
			from v_EvnStickBase with (nolock)
			where EvnStickBase_id = :EvnStickStudent_id
		";
		$result = $this->db->query($query, array('EvnStickStudent_id' => $data['EvnStickStudent_id']));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошщибка при выполнении запроса к базе данных (получение идентификатора родительского события)'));
		}

		$rec = $result->result('array');

		if ( $rec[0]['EvnStickBase_mid'] == $data['EvnStickStudent_mid'] ) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_EvnStickStudent_del
					@EvnStickStudent_id = :EvnStickStudent_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnStickStudent_id' => $data['EvnStickStudent_id'],
				'pmUser_id' => $data['pmUser_id']
			);
		}
		else {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000),
					@id bigint;

				set @id = (select top 1 EvnLink_id from EvnLink with (nolock) where Evn_id = :EvnStickStudent_mid and Evn_lid = :EvnStickStudent_id);

				exec p_EvnLink_del
					@EvnLink_id = @id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'EvnStickStudent_id' => $data['EvnStickStudent_id'],
				'EvnStickStudent_mid' => $data['EvnStickStudent_mid']
			);
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление ' . ($rec[0]['EvnStickBase_mid'] == $data['EvnStickStudent_mid'] ? 'справки учащегося' : 'связи справки учащегося с текущим документом') . ')'));
		}
	}


	/**
	 *	Удаление записи об освобождении от работы
	 */
	function deleteEvnStickWorkRelease($data) {
		$query = "
			select top 1 EVK.EvnVK_NumProtocol
			from v_EvnVK EVK with(nolock)
			where EVK.EvnStickWorkRelease_id = :EvnStickWorkRelease_id
		";
		$NumProtocol = $this->getFirstResultFromQuery($query, $data, true);
		if ($NumProtocol === false) {
			return $this->createError('','Ошибка поиска протокола ВК, связанного с освобождением');
		}
		if (!empty($NumProtocol)) {
			return $this->createError('',"Освобождение связано с протоколом заседания врачебной комиссии № {$NumProtocol}.");
		}

		$dbRegES = $this->load->database('registry_es', true);
		$query = "
			select top 1
				ESWR.EvnStickBase_id,
				case when isnull(ESWR.EvnStickWorkRelease_IsInReg,1) = 2 then 1 else 0 end as isInReg,
				case when isnull(ESWR.EvnStickWorkRelease_IsPaid,1) = 2 then 1 else 0 end as isPaid,
				case when RESD.Cnt > 0 then 1 else 0 end as hasRegistryESDataLink
			from v_EvnStickWorkRelease ESWR with(nolock)
			outer apply(
				select top 1 count(*) as Cnt
				from v_RegistryESData with(nolock) 
				where ESWR.EvnStickWorkRelease_id in (FirstEvnStickWorkRelease_id,SecondEvnStickWorkRelease_id,ThirdEvnStickWorkRelease_id)
			) RESD
			where ESWR.EvnStickWorkRelease_id = :EvnStickWorkRelease_id
		";
		$result = $dbRegES->query($query, $data);
		if (!is_object($result)) {
			return $this->createError('','Ошибка при выполнении запроса к БД (получение данных освобождения)');
		}
		$info = $result->result('array');
		if (!is_array($info)) {
			return $this->createError('','Ошибка при получении данных освобождения');
		}
		if (!empty($info[0])) {
			if ($info[0]['isInReg'] || $info[0]['isPaid']) {
				return $this->createError('','Выбранный период освобождения от работы отправлен в ФСС. Удаление невозможно');
			}
			if ($info[0]['hasRegistryESDataLink']) {
				$query = "
					update RegistryESData with(rowlock) set 
						FirstEvnStickWorkRelease_id = null,
						FirstEvnStickWorkRelease_begDT = null,
						FirstEvnStickWorkRelease_endDT = null,
						FirstMedPersonal_Fin = null,
						FirstMedPersonal_Hash = null,
						FirstMedPersonal_Inn = null,
						FirstMedPersonal_SignedData = null,
						FirstMedPersonal_Token = null,
						FirstMedPersonalVK_Fin = null,
						FirstMedPersonalVK_Inn = null,
						FirstPost_Code = null,
						FirstPostVK_Code = null,
						FirstVK_Hash = null,
						FirstVK_SignedData = null,
						FirstVK_Token = null
					where FirstEvnStickWorkRelease_id = :EvnStickWorkRelease_id
					
					update RegistryESData with(rowlock) set 
						SecondEvnStickWorkRelease_id = null,
						SecondEvnStickWorkRelease_begDT = null,
						SecondEvnStickWorkRelease_endDT = null,
						SecondMedPersonal_Fin = null,
						SecondMedPersonal_Hash = null,
						SecondMedPersonal_Inn = null,
						SecondMedPersonal_SignedData = null,
						SecondMedPersonal_Token = null,
						SecondMedPersonalVK_Fin = null,
						SecondMedPersonalVK_Inn = null,
						SecondPost_Code = null,
						SecondPostVK_Code = null,
						SecondVK_Hash = null,
						SecondVK_SignedData = null,
						SecondVK_Token = null
					where SecondEvnStickWorkRelease_id = :EvnStickWorkRelease_id
					
					update RegistryESData with(rowlock) set
						ThirdEvnStickWorkRelease_id = null,
						ThirdEvnStickWorkRelease_begDT = null,
						ThirdEvnStickWorkRelease_endDT = null,
						ThirdMedPersonal_Fin = null,
						ThirdMedPersonal_Hash = null,
						ThirdMedPersonal_Inn = null,
						ThirdMedPersonal_SignedData = null,
						ThirdMedPersonal_Token = null,
						ThirdMedPersonalVK_Fin = null,
						ThirdMedPersonalVK_Inn = null,
						ThirdPost_Code = null,
						ThirdPostVK_Code = null,
						ThirdVK_Hash = null,
						ThirdVK_SignedData = null,
						ThirdVK_Token = null
					where ThirdEvnStickWorkRelease_id = :EvnStickWorkRelease_id
				";
				$dbRegES->query($query, $data);
			}
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnStickWorkRelease_del
				@EvnStickWorkRelease_id = :EvnStickWorkRelease_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, array(
			'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id']
		));
		if (!is_array($response)) {
			return $this->createError('','Ошибка при выполнении запроса к базе данных (удаление освобождения от работы)');
		}

		return $response;
	}


	/**
	 *	Получение чего-то непонятного
	 */
	function getEvnStickChange($data) {
		$filter = '';

		if ( $data['StickExisting'] == 0 ) {
			$filter .= "
				and (ESB.EvnStickBase_disDate is null OR exists(select top 1 ESWR.EvnStickWorkRelease_id from v_EvnStickWorkRelease ESWR (nolock) where ESWR.EvnStickWorkRelease_IsDraft = 2 and ESWR.Org_id = :Org_id))
				and ESB.EvnStickBase_mid != :EvnStick_mid
				and not exists (select Evn_lid from EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = ESB.EvnStickBase_id)
			";

			// @task https://redmine.swan.perm.ru/issues/133269
			/*$subfilter = '';
			$EvnClass_SysNick = $this->getFirstResultFromQuery("
				select top 1 EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
			", array('Evn_id' => $data['EvnStick_mid']));
			if ($EvnClass_SysNick == 'EvnPL') {
				$subfilter = "or (
					select top 1 LU.LpuUnitType_SysNick
					from v_EvnStickWorkRelease ESWR with(nolock)
					inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = ESWR.LpuSection_id
					inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					where ESWR.EvnStickBase_id = ESB.EvnStickBase_id
					order by ESWR.evnStickWorkRelease_begDT desc
				) = 'polka'";
			}

			$maxWR = getRegionNick()=='kz'?4:3;
			$filter .= "
				and (
					ESB.EvnStickBase_disDate is not null
					or (
						select top 1 count(EvnStickWorkRelease_id)
						from v_EvnStickWorkRelease with(nolock)
						where EvnStickBase_id = ESB.EvnStickBase_id
					) < {$maxWR}
					{$subfilter}
				)
			";*/
		}

		$query = "
			with ESB as (
				select *
				from 
					v_EvnStickBase ESB with (nolock)
					outer apply (
						select top 1 EvnStick_id
						from v_EvnStick es with (nolock)
						where es.EvnStick_mid = :EvnStick_mid
							and es.StickWorkType_id in (1, 3)
							and es.StickOrder_id = 1
							and esb.StickOrder_id = 1 -- только для первичных ищем первичный в случае
					) ES
				where ES.EvnStick_id is null
			)
			select
				ESB.EvnStickBase_id as EvnStick_id,
				ESB.EvnStickBase_pid as EvnStick_pid,
				ESB.EvnStickBase_mid as EvnStick_mid,
				ESB.Lpu_id,
				convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate,
				convert(varchar(10), ESB.EvnStickBase_disDT, 104) as EvnStick_disDate,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType, -- Вид док-та (код)
				SWT.StickWorkType_Name,
				'Существующий' as EvnStickDoc,
				'ЛВН' as StickType_Name,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				case
					when ESTATUS.EvnStatus_SysNick = 'draft' then ''
					when EPL.EvnPL_id is not null then 'ТАП'
					when EPLS.EvnPLStom_id is not null then 'Стом. ТАП'
					when EPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName,
				case
					when EPL.EvnPL_id is not null then 'EvnPL'
					when EPLS.EvnPLStom_id is not null then 'EvnPLStom'
					when EPS.EvnPS_id is not null then 'EvnPS'
					else ''
				end as parentClass,
				case
					when ESTATUS.EvnStatus_SysNick = 'draft' then ''
					when EPL.EvnPL_id is not null then EPL.EvnPL_NumCard
					when EPLS.EvnPLStom_id is not null then EPLS.EvnPLStom_NumCard
					when EPS.EvnPS_id is not null then EPS.EvnPS_NumCard
				   else ''
				end as EvnStick_ParentNum,
				ESTATUS.EvnStatus_Name
			from ESB
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid and EC.EvnClass_SysNick = 'EvnStickDop'
				inner join StickOrder SO with (nolock) on SO.StickOrder_id = ISNULL(ESBD.StickOrder_id, ESB.StickOrder_id)
				left join v_EvnStick ES2 with (nolock) on ES2.EvnStick_id = ESB.EvnStickBase_id
				left join v_StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = ESB.EvnStickBase_mid
				left join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = ESB.EvnStickBase_mid
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ESB.EvnStickBase_mid
				left join v_EvnStick ESTICK (nolock) on ESTICK.EvnStick_id = ESB.EvnStickBase_id
				left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ESTICK.EvnStatus_id
			where ESB.Person_id = :Person_id
				and ESB.StickLeaveType_rid is null
				and (
					exists(select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = ESB.EvnStickBase_id)
					OR ESB.Org_did = :Org_id
				)
				and (ISNULL(ESBD.EvnStickBase_mid, :EvnStick_mid) = :EvnStick_mid OR exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = ESBD.EvnStickBase_id)) -- не даём добавить ЛВН по совместительству, если по основному месту работы не добавлен.
				" . $filter . "
			union
			select
				ESB.EvnStickBase_id as EvnStick_id,
				ESB.EvnStickBase_pid as EvnStick_pid,
				ESB.EvnStickBase_mid as EvnStick_mid,
				ESB.Lpu_id,
				convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate,
				convert(varchar(10), ESB.EvnStickBase_disDT, 104) as EvnStick_disDate,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType, -- Вид док-та (код)
				SWT.StickWorkType_Name,
				'Существующий' as EvnStickDoc,
				'ЛВН' as StickType_Name,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				case
					when ESTATUS.EvnStatus_SysNick = 'draft' then ''
					when EPL.EvnPL_id is not null then 'ТАП'
					when EPLS.EvnPLStom_id is not null then 'Стом. ТАП'
					when EPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName,
				case
					when EPL.EvnPL_id is not null then 'EvnPL'
					when EPLS.EvnPLStom_id is not null then 'EvnPLStom'
					when EPS.EvnPS_id is not null then 'EvnPS'
					else ''
				end as parentClass,
				case
					when ESTATUS.EvnStatus_SysNick = 'draft' then ''
					when EPL.EvnPL_id is not null then EPL.EvnPL_NumCard
					when EPLS.EvnPLStom_id is not null then EPLS.EvnPLStom_NumCard
					when EPS.EvnPS_id is not null then EPS.EvnPS_NumCard
				   else ''
				end as EvnStick_ParentNum,
				ESTATUS.EvnStatus_Name
			from ESB
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid and EC.EvnClass_SysNick = 'EvnStickDop'
				inner join StickOrder SO with (nolock) on SO.StickOrder_id = ISNULL(ESBD.StickOrder_id, ESB.StickOrder_id)
				left join v_StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = ESB.EvnStickBase_mid
				left join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = ESB.EvnStickBase_mid
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ESB.EvnStickBase_mid
				outer apply (
					select top 1 EvnStick_id
					from v_EvnStick es with (nolock)
					where es.EvnStick_mid = :EvnStick_mid
						and es.StickWorkType_id in (1, 3)
						and es.StickOrder_id = 1
						and esb.StickOrder_id = 1 -- только для первичных ищем первичный в случае
				) ES
				left join v_EvnStick ESTICK (nolock) on ESTICK.EvnStick_id = ESB.EvnStickBase_id
				left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ESTICK.EvnStatus_id
			where exists (select EvnStickCarePerson_id from v_EvnStickCarePerson with (nolock) where Evn_id = ESB.EvnStickBase_id and Person_id = :Person_id)
				and ES.EvnStick_id is null
				and (
					exists(select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = ESB.EvnStickBase_id)
					OR ESB.Org_did = :Org_id
				)
				and (ISNULL(ESBD.EvnStickBase_mid, :EvnStick_mid) = :EvnStick_mid OR exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = ESBD.EvnStickBase_id)) -- не даём добавить ЛВН по совместительству, если по основному месту работы не добавлен.
				" . $filter . "
		";

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'EvnStick_mid' => $data['EvnStick_mid'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id']
		);

		//echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {

			$response_arr = $result->result('array');
			if($this->getRegionNick() == 'ufa') {
				$sql = "
					select lp.Lpu_id
					from v_AccessRightsOrg ar with (nolock)
					left join v_Lpu lp with (nolock) on lp.Org_id = ar.Org_id
				";
				$result = $this->db->query($sql);
				if ( !is_object($result) )
				{
					throw new Exception('Ошибка запроса списка ЛПУ с особым статусом', 500);
				}
				$res_arr = $result->result('array');
				$_list_vip_lpu = array();
				foreach($res_arr as $row)
				{
					$_list_vip_lpu[] = $row['Lpu_id'];
				}
				$groups = explode('|', $data['session']['groups']);
				foreach ($groups as $key => $value) {
					$groups[$key] = "'".$value."'";
				}
				$groups = implode(',',$groups);

				foreach($response_arr as $i => $row)
				{
					if (
						isset($row['Lpu_id']) && in_array($row['Lpu_id'],$_list_vip_lpu) && ($row['Lpu_id'] != $data['Lpu_id'])
					)
					{
						$queryParams = array();
						$queryParams['Lpu_iid'] = $row['Lpu_id'];
						$queryParams['Lpu_id'] = $data['Lpu_id'];
						$queryParams['pmUser_id'] = $data['pmUser_id'];
						$join = '';
						$where = '';
						if(isset($data['user_MedStaffFact_id'])){
							$join = "left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = :MedStaffFact_id";
							$where = " or arl.Post_id = msf.Post_id ";
							$queryParams['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
						}
						$sql = "
							select 
							lp.Lpu_id
							from v_Lpu lp with (nolock) 
							inner join v_AccessRightsOrg ar with (nolock) on ar.Org_id = lp.Org_id
							inner join v_AccessRightsLimit arl with (nolock) on arl.AccessRightsName_id = ar.AccessRightsName_id
							left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id
							{$join}
							where lp.Lpu_id = :Lpu_iid and (arl.Org_id = lpu.Org_id or (arl.AccessRightsType_UserGroups in ({$groups})) or arl.AccessRightsType_User = :pmUser_id {$where})
						";
						$result = $this->db->query($sql,$queryParams);
						if ( !is_object($result) )
						{
							throw new Exception('Ошибка проверки исключений доступа к ЛПУ с особым статусом', 500);
						}
						$res = $result->result('array');
						if(!(count($res) > 0))
							unset($response_arr[$i]);
					}
				}
				$response_arr = array_values($response_arr);
				return $response_arr;
			} else {
				return $response_arr;
			}
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение списка основных ЛВН
	 */
	function getEvnStickMainList($data) {
		$queryParams = array(
			'EvnStick_mid' => $data['EvnStick_mid'],
			'Lpu_id' => $data['Lpu_id']
		);
		$union = "";

		if (!empty($data['EvnStick_id'])) {
			// ЛВН пришедший с формы загружаем в обход фильтров refs #164841
			$queryParams['EvnStick_id'] = $data['EvnStick_id'];
			$union .= "
				union
				
				select
					ES.EvnStick_id
				from
					v_EvnStick ES with (nolock)
				where
					ES.EvnStick_id = :EvnStick_id
					and ES.StickWorkType_id in (1, 3)
			";
		}

		$query = "
			with EvnStickList as (
				select
					ES.EvnStick_id
				from
					v_EvnStick ES with (nolock)
				where
					ES.EvnStick_mid = :EvnStick_mid
					and ES.StickWorkType_id in (1, 3)
						
				union
							
				select
					ES.EvnStick_id
						from
					v_EvnStick ES with (nolock)
				where
					exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = ES.EvnStick_id)
					and ES.StickWorkType_id in (1, 3)
					
				{$union}
			)
			
			select
				ES.EvnStick_id,
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				RTRIM(LTRIM(RTRIM(ISNULL(PS.Person_SurName, '')) + ' ' + RTRIM(ISNULL(PS.Person_FirName, '')) + ' ' + RTRIM(ISNULL(PS.Person_SecName, '')))) as Person_Fio,
				ES.StickOrder_id,
				ES.EvnStick_mid,
				ESDP.Org_id,
				ESDP.EvnStick_id as EvnStick_prid,
				ESDP.StickLeaveType_Code as PridStickLeaveType_Code,
				convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
				
				case
					when ISNULL(ESDP.EvnStick_id, 0) != 0 then RTRIM(LTRIM(RTRIM(ISNULL(ESDP.EvnStick_Ser, '')) + ' ' + RTRIM(ISNULL(ESDP.EvnStick_Num, '')) + ', ' + ISNULL(convert(varchar(10), ESDP.EvnStick_setDate, 104), '')))
					else NULL
				end as EvnStickLast_Title,
				
				ES.StickCause_id,
				ES.StickCauseDopType_id,
				ES.StickCause_did,
				convert(varchar(10), ES.EvnStick_StickDT, 104) as EvnStick_StickDT,
				RTRIM(ISNULL(ES.EvnStick_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ES.EvnStick_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				ISNULL(ES.EvnStick_sstNum, '') as EvnStick_sstNum,
				ES.Org_did,
				O.Org_Nick,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				ES.EvnStick_IsRegPregnancy,
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ES.EvnStick_IsDisability,
				ES.MedStaffFact_id,
				ES.MedPersonal_id,
				-- ES.MedPersonal_mseid,
				ES.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), ES.EvnStick_disDT, 104) as EvnStick_disDate,
				ES.Lpu_oid,
				RTRIM(RTRIM(ISNULL(ES.EvnStick_Ser, '')) + ' ' + RTRIM(ISNULL(ES.EvnStick_Num, '')) + ', ' + convert(varchar(10), ES.EvnStick_setDate, 104)) as EvnStick_Title,
				ES.InvalidGroupType_id,
				ES.EvnStick_consentDT
			from
				EvnStickList ESL with (nolock)
				inner join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESL.EvnStick_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
				outer apply(
					select top 1
						*
					from (
						select top 1 
							ESD.Org_id,
							ESD.EvnStickDop_id as EvnStick_id,
							ESD.EvnStickDop_Ser as EvnStick_Ser,
							ESD.EvnStickDop_Num as EvnStick_Num,
							ESD.EvnStickDop_setDate as EvnStick_setDate,
							SLT.StickLeaveType_Code,
							1 as ReturnOrder
						from
							v_EvnStickDop ESD with (nolock)
							left join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = ESD.EvnStickDop_id
							left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
						where 
							ES.EvnStick_prid = ESD.EvnStickDop_pid
							and not exists(select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = ESD.EvnStickDop_id)
							and SLT.StickLeaveType_Code in ('31', '37')
						
						union all
	
						select top 1 
							ESD.Org_id,
							ESD.EvnStick_id as EvnStick_id,
							ESD.EvnStick_Ser as EvnStick_Ser,
							ESD.EvnStick_Num as EvnStick_Num,
							ESD.EvnStick_setDate as EvnStick_setDate,
							SLT.StickLeaveType_Code,
							2 as ReturnOrder
						from
							v_EvnStick ESD with (nolock)
							left join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESD.EvnStick_id
							left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
						where ESD.EvnStick_mid = :EvnStick_mid
							and ESD.StickWorkType_id = 2
							and not exists(select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = ESD.EvnStick_id)
					) ESDP
					order by ESDP.ReturnOrder
				) ESDP
				left join v_Org O with (nolock) on O.Org_id = ES.Org_did
		";

		// echo getDebugSQL($query, $queryParams); return false;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}		
	}


	/**
	 *	Получение списка ЛВН-оригиналов
	 */
	function getEvnStickOriginalsList($data) {
		if(empty($data['EvnStick_id'])){ $data['EvnStick_id'] = 0; }

		$esBaseFilter = "";
		$esBaseUnion = "";
		//#158564 Базовый(кроме Казахстана) должна быть возможность оформить дубликат на дубликат 
		if(getRegionNick() == 'kz') { 
			$esBaseFilter .= "
				and ISNULL(ESB.EvnStickBase_IsOriginal, 1) = 1
			";

		}

		$queryParams = array(
			'EvnStick_mid' => $data['EvnStick_mid'],
			'EvnStick_id' => $data['EvnStick_id']
		);

		if (!empty($data['EvnStick_oid'])) {
			$esBaseUnion .= "
				union

				select :EvnStick_oid as EvnStickBase_id
			";
			$queryParams['EvnStick_oid'] = $data['EvnStick_oid'];
		}

		// Электронные ЛН попадают в список, только если у них есть хотя бы один принятый период нетрудоспособности  в ФСС (признак IsPaid) на открытие ЛН
		$query = "
			-- ЛВН добавлен в текущем случае или связан с ним
			with ESBASE as (
				select
					ESB.EvnStickBase_id
				from
					EvnStickBase ESB (nolock)
				where
					ESB.EvnStickBase_mid = :EvnStick_mid
					and ESB.EvnStickBase_id <> :EvnStick_id
					and ISNULL(ESB.StickCauseDel_id, 2) = 2
					{$esBaseFilter}
					and (
						not exists ( -- не электронный элн
							select top 1
								RegistryESStorage_id
							from
								v_RegistryESStorage with (nolock)
							where
								EvnStickBase_id = ESB.EvnStickBase_id
						) or exists ( -- или есть хотя бы один принятый период
							select top 1
								EvnStickWorkRelease_id
							from
								v_EvnStickWorkRelease
							where
								EvnStickWorkRelease_IsPaid = 2
								and EvnStickBase_id = ESB.EvnStickBase_id
						)
					)
					
				union
				
				select
					ESB.EvnStickBase_id
				from
					EvnStickBase ESB (nolock)
				where
					exists (select EvnLink_id from v_EvnLink with (nolock) where Evn_id = :EvnStick_mid and Evn_lid = ESB.EvnStickBase_id)
					and ESB.EvnStickBase_id <> :EvnStick_id
					and ISNULL(ESB.StickCauseDel_id, 2) = 2
					{$esBaseFilter}
					and (
						not exists ( -- не электронный элн
							select top 1
								RegistryESStorage_id
							from
								v_RegistryESStorage with (nolock)
							where
								EvnStickBase_id = ESB.EvnStickBase_id
						) or exists ( -- или есть хотя бы один принятый период
							select top 1
								EvnStickWorkRelease_id
							from
								v_EvnStickWorkRelease
							where
								EvnStickWorkRelease_IsPaid = 2
								and EvnStickBase_id = ESB.EvnStickBase_id
						)
					)

				{$esBaseUnion}
			)
			
			select
				ES.EvnStick_id,
				ES.EvnStick_id as EvnStick_GridId,
				ESB.EvnStickBase_nid as EvnStick_nid,
				E.Person_id,
				E.PersonEvn_id,
				E.Server_id,
				P.Person_Snils,
				ESB.StickWorkType_id,
				RTRIM(LTRIM(RTRIM(ISNULL(PS.Person_SurName, '')) + ' ' + RTRIM(ISNULL(PS.Person_FirName, '')) + ' ' + RTRIM(ISNULL(PS.Person_SecName, '')))) as Person_Fio,
				ESB.StickOrder_id,
				ESB.EvnStickBase_mid as EvnStick_mid,
				null as EvnStickDop_pid,
				ES.EvnStick_prid,
				ISNULL(convert(varchar(10), E.Evn_setDT, 104), '') as EvnStick_setDate,
				RTRIM(LTRIM(RTRIM(ISNULL(ESP.EvnStick_Ser, '')) + ' ' + RTRIM(ISNULL(ESP.EvnStick_Num, '')) + ', ' + ISNULL(convert(varchar(10), ESP.EvnStick_setDate, 104), ''))) as EvnStickLast_Title,
				ESB.StickCause_id,
				ESB.StickCauseDopType_id,
				ESB.StickCause_did,
				SC.StickCause_SysNick,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				ISNULL(ES.EvnStick_sstNum, '') as EvnStick_sstNum,
				ESB.Org_did,
				O.Org_Nick,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				ESB.EvnStickBase_IsRegPregnancy as EvnStick_IsRegPregnancy,
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ESB.EvnStickBase_IsDisability,
				ESB.MedStaffFact_id,
				ESB.MedPersonal_id,
				-- ES.MedPersonal_mseid,
				ESB.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), E.Evn_disDT, 104) as EvnStick_disDate,
				ES.Lpu_oid,
				RTRIM(RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) + ' ' + RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) + ', ' + ISNULL(convert(varchar(10), E.Evn_setDT, 104), '')) as EvnStick_Title,
				convert(varchar(10), ESB.EvnStickBase_consentDT, 104) as EvnStickBase_consentDT,
				ESB.Org_id,
				RTRIM(ISNULL(ESB.EvnStickBase_OrgNick, '')) as EvnStick_OrgNick,
				RTRIM(ISNULL(ESB.Post_Name, '')) as Post_Name,
				convert(varchar(10), ESB.EvnStickBase_StickDT, 104) as EvnStick_StickDT,
				ESB.InvalidGroupType_id,
				case
					when E.Evn_deleted = 2 then 'ЛВН аннулирован'
					when ESB.EvnStickBase_IsInRegDel = 2 then 'В реестре на удаление'
					when ESB.EvnStickBase_IsDelQueue = 2 then 'В очереди на удаление'
					else ''
				end as Status
			from
				ESBASE with (nolock)
				inner join Evn E with (nolock) on E.Evn_id = ESBASE.EvnStickBase_id
				inner join EvnStickBase ESB with (nolock) on E.Evn_id = ESB.EvnStickBase_id
				inner join v_EvnStick_all ES with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_Person_all P with (nolock) on P.PersonEvn_id = E.PersonEvn_id and P.Server_id = E.Server_id
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				--left join v_Org O with (nolock) on O.Org_id = ES.Org_id
				left join v_Org O with (nolock) on O.Org_id = ESB.Org_did
				inner join v_PersonState PS with (nolock) on PS.Person_id = E.Person_id
				left join v_EvnStick_all ESP with (nolock) on ESP.EvnStick_id = ES.EvnStick_prid
			where
				E.EvnClass_id = 20
				and (ISNULL(E.Evn_deleted, 1) = 1 OR ESB.EvnStickBase_IsInRegDel = 2)

			union
			
			select
				ISNULL(ESD.EvnStickDop_id, 0) as EvnStick_id,
				E.Evn_pid as EvnStick_GridId,
				ESB.EvnStickBase_nid as EvnStick_nid,
				ISNULL(ES.Person_id, 0) as Person_id,
				ISNULL(ES.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(ES.Server_id, -1) as Server_id,
				P.Person_Snils,
				ESB.StickWorkType_id,
				RTRIM(LTRIM(RTRIM(ISNULL(PS.Person_SurName, '')) + ' ' + RTRIM(ISNULL(PS.Person_FirName, '')) + ' ' + RTRIM(ISNULL(PS.Person_SecName, '')))) as Person_Fio,
				ES.StickOrder_id,
				ESB.EvnStickBase_mid as EvnStick_mid,
				E.Evn_pid as EvnStickDop_pid,
				ISNULL(ESD.EvnStickDop_prid, 0) as EvnStick_prid,
				ISNULL(convert(varchar(10), E.Evn_setDT, 104), '') as EvnStick_setDate,
				RTRIM(LTRIM(RTRIM(ISNULL(ESDPB.EvnStickBase_Ser, '')) + ' ' + RTRIM(ISNULL(ESDPB.EvnStickBase_Num, '')) + ', ' + ISNULL(convert(varchar(10), ESDPE.Evn_setDT, 104), ''))) as EvnStickLast_Title,
				ES.StickCause_id,
				ES.StickCauseDopType_id,
				ES.StickCause_did,
				SC.StickCause_SysNick,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				RTRIM(ISNULL(ES.EvnStick_sstNum, '')) as EvnStick_sstNum,				
				ES.Org_did,
				O.Org_Nick,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				ES.EvnStick_IsRegPregnancy,				
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ES.EvnStick_IsDisability as EvnStick_IsDisability,
				ESB.MedStaffFact_id,
				ES.MedPersonal_id,
				-- ES.MedPersonal_mseid,
				ESB.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), E.Evn_disDT, 104) as EvnStick_disDate,
				ES.Lpu_oid,
				RTRIM(RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) + ' ' + RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) + ', ' + ISNULL(convert(varchar(10), E.Evn_setDT, 104), '')) as EvnStick_Title,
				convert(varchar(10), ESB.EvnStickBase_consentDT, 104) as EvnStickBase_consentDT,
				ESB.Org_id,
				RTRIM(ISNULL(ES.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
				RTRIM(ISNULL(ESB.Post_Name, '')) as Post_Name,
				null as EvnStick_StickDT,
				ESB.InvalidGroupType_id,
				case
					when E.Evn_deleted = 2 then 'ЛВН аннулирован'
					when ESB.EvnStickBase_IsInRegDel = 2 then 'В реестре на удаление'
					when ESB.EvnStickBase_IsDelQueue = 2 then 'В очереди на удаление'
					else ''
				end as Status
			from
				ESBASE with (nolock)
                inner join Evn E with (nolock) on E.Evn_id = ESBASE.EvnStickBase_id
				inner join EvnStickBase ESB with (nolock) on E.Evn_id = ESB.EvnStickBase_id
				inner join EvnStickDop ESD with (nolock) on ESB.EvnStickBase_id = ESD.EvnStickBase_id
				inner join v_EvnStick_all ES with(nolock) on ES.EvnStick_id = E.Evn_pid
				inner join v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
				left join v_Person_all P with(nolock) on P.PersonEvn_id = ES.PersonEvn_id and P.Server_id = ES.Server_id
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				--left join v_Org O with (nolock) on O.Org_id = ESD.Org_id
				left join v_Org O with (nolock) on O.Org_id = ES.Org_did
				left join EvnStickDop ESDP with (nolock) on ESDP.EvnStickDop_id = ESD.EvnStickDop_prid
				left join EvnStickBase ESDPB with (nolock) on ESDPB.EvnStickBase_id = ESDP.EvnStickDop_id
				left join Evn ESDPE with (nolock) on ESDPE.Evn_id = ESDPB.Evn_id
			where
				(ISNULL(E.Evn_deleted, 1) = 1 OR ESB.EvnStickBase_IsInRegDel = 2)
		";

		//echo getDebugSQL($query, $queryParams) return false;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}		
	}
	
	
	/**
	 *	Генерирование номера справки учащегося
	 */
	function getEvnStickStudentNumber($data) {
		$query = "
			declare @EvnStickStudent_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnStickStudent', @Lpu_id = :Lpu_id, @ObjectID = @EvnStickStudent_Num output;
			select @EvnStickStudent_Num as EvnStickStudent_Num;
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * Получает список идентификаторов МО, рабочие места которых указаны в освобождениях от работы
	 */
	function getWorkReleaseLpuList($data) {
		$query = "
			select distinct MSF.Lpu_id 
			from EvnStick ES
				inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = ES.EvnStickBase_id
				inner join EvnStickWorkRelease ESWR on ESWR.EvnStickBase_id = ESB.EvnStickBase_id
				inner join v_MedStaffFact MSF on (
					MSF.MedStaffFact_id = ESWR.MedStaffFact_id 
					or MSF.MedStaffFact_id = ESWR.MedStaffFact2_id 
					or MSF.MedStaffFact_id = ESWR.MedStaffFact3_id
				)
			where 
				ES.EvnStick_id = :EvnStick_id
		";
		return $this->queryResult($query, $data);
	}


	/**
	 *	Получение максимальной и минимальной допустимых дат для освобождения от работы
	 */
	function getEvnStickWorkReleaseDateLimits($data) {
		$response = array(
			'maxEndDate' => null,
			'minBegDate' => null,
			'Error_Msg' => ''
		);

		if ( !empty($data['EvnStickBase_prid']) ) {
			// Получаем максимальную дату в предыдущем ЛВН
			$query = "
				select convert(varchar(10), max(ESWR.EvnStickWorkRelease_endDT), 104) as maxEndDate
				from v_EvnStickWorkRelease ESWR with (nolock)
					inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				where ESB.EvnStickBase_id = :EvnStickBase_prid
			";

			$queryParams = array(
				'EvnStickBase_prid' => $data['EvnStickBase_prid']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение даты окончания освобождения в предыдущем ЛВН)';
				return $response;
			}


			$res = $result->result('array');

			if ( is_array($res) && !empty($res[0]['maxEndDate']) ) {
				$response['maxEndDate'] = $res[0]['maxEndDate'];
			}
		}

		if ( !empty($data['EvnStickBase_id']) ) {
			// Получаем минимальную дату в следующем ЛВН
			$query = "
				select 
				    convert(varchar(10), min(ESWR.EvnStickWorkRelease_begDT), 104) as minBegDate
				from 
				    v_EvnStickWorkRelease ESWR with (nolock)
					inner join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESWR.EvnStickBase_id
				where 
				    ES.EvnStick_prid = :EvnStickBase_id
			";

			$queryParams = array(
				'EvnStickBase_id' => $data['EvnStickBase_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение даты начала освобождения от работы в ЛВН-продолжении)';
				return $response;
			}

			$res = $result->result('array');

			if ( is_array($res) && !empty($res[0]['minBegDate']) ) {
				$response['minBegDate'] = $res[0]['minBegDate'];
			}
		}

		return $response;
	}


	/**
	 *	Полчение краткого наименования организации
	 */
	function getOrgStickNick($data) {
		$query = "
			select top 1
				Org_id,
				RTRIM(ISNULL(Org_StickNick, '')) as Org_StickNick
			from v_Org with (nolock)
			where Org_id = :Org_id
		";
		$queryParams = array(
			'Org_id' => $data['Org_id']
		);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Загрузка списка пациентов по уходу
	 */
	function loadEvnStickCarePersonGrid($data) {

		if (!empty($data['EvnStickBase_IsFSS']) ) {//ЛВН из ФСС
			$query = "
				select 
					accessType,
					EvnStickCarePerson_id,
					Evn_id,
					Person_id,
					Person_pid,
					RelatedLinkType_id,
					Person_Fio,
					Person_Birthday,
					Person_Surname,
					Person_Firname,
					Person_Secname,
					Person_Age,
					RelatedLinkType_Name,
					RecordStatus_Code,
					1 as position
				from (
					select top 1
						'view' as accessType,
						null as EvnStickCarePerson_id,
						null as Evn_id,
						null as Person_id,
						null as Person_pid,
						RLT.RelatedLinkType_id,
						SFDG.FirstRelated_FIO as Person_Fio,
						null as Person_Birthday,
						null as Person_Surname,
						null as Person_Firname,
						null as Person_Secname,
						SFDG.FirstRelated_Age as Person_Age,
						RTRIM(ISNULL(RLT.RelatedLinkType_Name, '')) as RelatedLinkType_Name,
						1 as RecordStatus_Code
					from
						StickFSSDataGet SFDG with (nolock)
						left join RelatedLinkType RLT with (nolock) on RLT.RelatedLinkType_Code = SFDG.FirstRelatedLinkType_Code
					where
						SFDG.EvnSTickBase_id = :Evn_id
						and SFDG.FirstRelated_FIO is not null
					order by
						SFDG.StickFSSDataGet_updDT desc
				) as fir

				union

				select 
					accessType,
					EvnStickCarePerson_id,
					Evn_id,
					Person_id,
					Person_pid,
					RelatedLinkType_id,
					Person_Fio,
					Person_Birthday,
					Person_Surname,
					Person_Firname,
					Person_Secname,
					Person_Age,
					RelatedLinkType_Name,
					RecordStatus_Code,
					2 as position

				from (
					select top 1
						'view' as accessType,
						null as EvnStickCarePerson_id,
						null as Evn_id,
						null as Person_id,
						null as Person_pid,
						RLT.RelatedLinkType_id,
						SFDG.SecondRelated_FIO as Person_Fio,
						null as Person_Birthday,
						null as Person_Surname,
						null as Person_Firname,
						null as Person_Secname,
						SFDG.SecondRelated_Age as Person_Age,
						RTRIM(ISNULL(RLT.RelatedLinkType_Name, '')) as RelatedLinkType_Name,
						1 as RecordStatus_Code
					from
						StickFSSDataGet SFDG with (nolock)
						left join RelatedLinkType RLT with (nolock) on RLT.RelatedLinkType_Code = SFDG.SecondRelatedLinkType_Code
					where
						SFDG.EvnSTickBase_id = :Evn_id
						and SFDG.SecondRelated_FIO is not null
					order by
						SFDG.StickFSSDataGet_updDT desc
				) as sec

				order by
					position
			";
		} else {
			$query = "
				select
					case when
						case
							when E.Lpu_id = :Lpu_id then 1
							" . (count($data['session']['linkedLpuIdList']) > 1 ? "when E.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(E.Evn_IsTransit, 1) = 2 then 1" : "") . "
							else 0
						end = 1
					then 'edit' else 'view' end as accessType,
					ESCP.EvnStickCarePerson_id,
					ESCP.Evn_id,
					isnull(ESCP.Person_id, PS.Person_id) as Person_id,
					E.Person_id as Person_pid,
					ESCP.RelatedLinkType_id,
					RTRIM(LTRIM(ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, ''))) as Person_Fio,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					PS.Person_Surname,
					PS.Person_Firname,
					PS.Person_Secname,
					dbo.Age2(PS.Person_Birthday, coalesce(firstVizit.EvnVizitPL_setDate, firstEvnSection.EvnSection_setDate, E.Evn_setDT)) as Person_Age,
					RTRIM(ISNULL(RLT.RelatedLinkType_Name, '')) as RelatedLinkType_Name,
					1 as RecordStatus_Code,
					0 as position
				from v_EvnStickCarePerson ESCP with (nolock)
					inner join v_PersonState PS with (nolock) on PS.Person_id = ESCP.Person_id
					inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESCP.Evn_id
					outer apply (
						select top 1 *
						from
							v_EvnVizitPL EVPL with (nolock) 
						where
							EVPL.EvnVizitPL_pid = ESB.EvnStickBase_pid
						order by
							EVPL.EvnVizitPL_setDate
					) as firstVizit
					outer apply (
						select top 1 *
						from
							v_EvnSection ES with (nolock)
						where
							ES.EvnSection_pid = ESB.EvnStickBase_pid
							and isnull(ES.EvnSection_IsPriem, 1) = 1
						order by
							ES.EvnSection_setDate
					) as firstEvnSection
					inner join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
					left join RelatedLinkType RLT with (nolock) on RLT.RelatedLinkType_id = ESCP.RelatedLinkType_id
				where 
					ESCP.Evn_id = :Evn_id
			";
		}

		$result = $this->db->query($query, array('Evn_id' => $data['EvnStick_id'], 'Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получить идентификаторы дочерних типов МО
	 * @param $LpuType_id int
	 * @return array
	 */
	function getDotherLpuTypes($LpuType_id) {
		$query = "
			WITH LpuTypeRec (LpuType_id, LpuType_pid, LpuType_Name)
			AS
			(
			    SELECT L.LpuType_id, L.LpuType_pid, L.LpuType_Name
			    FROM
			    	v_LpuType L with (nolock)
			    WHERE 
			    	L.LpuType_id = :LpuType_id 
			    UNION ALL
			    SELECT L.LpuType_id, L.LpuType_pid, L.LpuType_Name
			    FROM 
			    	v_LpuType L with (nolock)
			        INNER JOIN LpuTypeRec r ON L.LpuType_pid = r.LpuType_id
			)
			SELECT LpuType_id, LpuType_pid, LpuType_Name
			FROM LpuTypeRec r
		";
		$result = $this->queryResult( $query, array('LpuType_id' => $LpuType_id) );
		if( empty($result) ) {
			return false;
		}

		$response = array();
		foreach ($result as $record) {
			$response[] = $record['LpuType_id'];
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return string
	 */
	function getEvnStickAccessType($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		$med_personal_id = !empty($data['session']['medpersonal_id'])?$data['session']['medpersonal_id']:null;

		$cond = array();
		$cond[] = "when ESB.Lpu_id = :Lpu_id then 1"; // в своей МО
		if (!empty($med_personal_list)) {
			$cond[] = "when ESB.Lpu_id = :Lpu_id and (ESB.MedPersonal_id is null or ESB.MedPersonal_id in (" . implode(',', $med_personal_list) . ")) then 1"; // пустой исход или врач в исходе совпадает
		} else {
			$cond[] = "when ESB.MedPersonal_id is null then 1"; // пустой исход
		}
		$cond[] = "when exists (select top 1 EvnStickWorkRelease_id from v_EvnStickWorkRelease ESWR with (nolock) where ESWR.EvnStickBase_id = ESB.EvnStickBase_id and ESWR.Org_id = :Org_id) then 1";
		$cond[] = "when exists (select top 1 EL.EvnLink_id from v_EvnLink EL with (nolock) inner join v_Evn E with (nolock) on E.Evn_id = EL.Evn_id where EL.Evn_lid = ESB.EvnStickBase_id and E.Lpu_id = :Lpu_id) then 1";
		if (!empty($med_personal_id)) {
			$cond[] = "when exists(select top 1 EvnStickWorkRelease_id from v_EvnStickWorkRelease with(nolock) where EvnStickBase_id = ESB.EvnStickBase_id and MedPersonal_id = {$med_personal_id}) then 1";
		}
		if (array_key_exists('linkedLpuIdList', $data['session'])) {
			$cond[] = "when exists (
				select top 1 EvnStickWorkRelease_id 
				from v_EvnStickWorkRelease ESWR with (nolock) 
				inner join v_Lpu lpu (nolock) on lpu.Org_id = ESWR.Org_id
				where
					ESWR.EvnStickBase_id = ESB.EvnStickBase_id
					and lpu.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ")
			) then 1";
		}
		$cond_str = count($cond)>0?implode("\n", $cond):"";

		$cond_dop = array();
		if (getRegionNick() != 'kz') {
			$cond_dop[] = "ISNULL(ESB.EvnStickBase_IsInReg, 1) = 1";
			$cond_dop[] = "ISNULL(ESB.EvnStickBase_IsPaid, 1) = 1";
		}

		//#156421 Доступ к редактированию и удалению ЭЛН закрыт, если ЭЛН находится в состоянии  «040 ЭЛН направление на МСЭ» (StickFSSType).
		$cond_dop[] = "isnull(SFT.StickFSSType_Code, 0) != '040'";

		// ЛВН не находится в очереди на удаление или в реестре на удаление"
		$cond_dop[] = "isnull(ESB.EvnStickBase_IsDelQueue, 1) = 1";
		$cond_dop[] = "isnull(ESB.EvnStickBase_IsInRegDel, 1) = 1";

		$cond_dop_str = count($cond_dop)>0?" and ".implode(" and ", $cond_dop):"";

		$accessType = "
			case when (
				case
					{$cond_str}
					else 0
				end = 1
			)
			{$cond_dop_str}
			then 'edit' else 'view' end as accessType, -- фильтры доступа для ЛВН
		";

		$cond = array();

		// Доступность кнопки на уровне пользователей.
		$isRegLvn = (!empty($data['session']['ARMList']) && in_array('lvn',$data['session']['ARMList']));
		if (!$isRegLvn && empty($data['session']['isMedStatUser']) && !empty($med_personal_list)) {
			// Если есть исход: Текущая учётная запись привязана к врачу, указанному в исходе.
			$cond[] = "when ESB.MedPersonal_id is not null and ESB.MedPersonal_id in (" . implode(',', $med_personal_list) . ") then 1"; // исход указан и врач в исходе
			// Если нет исхода: Текущая учётная запись привязана к врачу, указанному в последнем освобождении (в одном из полей: "Врач 1", "Врач 2", "Врач 3").
			$cond[] = "when ESB.MedPersonal_id is null and (select top 1 MedPersonal_id from v_EvnStickWorkRelease with(nolock) where EvnStickBase_id = ESB.EvnStickBase_id order by EvnStickWorkRelease_begDT desc) in (" . implode(',', $med_personal_list) . ") then 1"; // Врач указан в последнем освобождении от работы
			$cond[] = "when ESB.MedPersonal_id is null and (select top 1 MedPersonal2_id from v_EvnStickWorkRelease with(nolock) where EvnStickBase_id = ESB.EvnStickBase_id order by EvnStickWorkRelease_begDT desc) in (" . implode(',', $med_personal_list) . ") then 1"; // Врач указан в последнем освобождении от работы
			$cond[] = "when ESB.MedPersonal_id is null and (select top 1 MedPersonal3_id from v_EvnStickWorkRelease with(nolock) where EvnStickBase_id = ESB.EvnStickBase_id order by EvnStickWorkRelease_begDT desc) in (" . implode(',', $med_personal_list) . ") then 1"; // Врач указан в последнем освобождении от работы
		} else {
			$cond[] = "when 1=1 then 1";
		}
		$cond_str = count($cond)>0?implode("\n", $cond):"";

		$cond_dop = array();
		// Доступность кнопки на уровне МО
		// ЭЛН (электронный ЛВН), ЛВН с признаком "ЛВН из ФСС"
		$cond_dop[] = "
			when ESB.EvnStickBase_IsFSS = 2 OR exists(
				select top 1
					RegistryESStorage_id
				from
					v_RegistryESStorage with (nolock)
				where
					EvnStickBase_id = ESB.EvnStickBase_id
			) then
				case
					when 
						isnull(ESB.EvnStickBase_IsPaid, 1) = 1 and isnull(ESB.EvnStickBase_IsInReg, 1) = 1
						and not exists(select top 1 EvnStickWorkRelease_id from v_EvnStickWorkRelease with(nolock) where EvnStickBase_id = ESB.EvnStickBase_id and (EvnStickWorkRelease_IsInReg = 2 or EvnStickWorkRelease_IsPaid = 2))
					then 1
					else 0
				end
		";
		$cond_dop_str = count($cond_dop)>0?implode("\n", $cond_dop):"";

		$cond_dop2 = array();
		//#156421 Доступ к редактированию и удалению ЭЛН закрыт, если ЭЛН находится в состоянии  «040 ЭЛН направление на МСЭ» (StickFSSType).
		$cond_dop2[] = "isnull(SFT.StickFSSType_Code, 0)!= '040'";

		$cond_dop2_str = count($cond_dop2)>0?" and ".implode(" and ", $cond_dop2):"1=1";

		$accessType .= "
			case when (
				case
					{$cond_str}
					else 0
				end = 1
			)
			and (
				case
					{$cond_dop_str}
					else 1
				end = 1
			)
			{$cond_dop2_str}
			then 'edit' else 'view' end as delAccessType, -- фильтры доступа для удаления ЛВН
		";
		
		$cond = array();

		$cond[] = "when ESB.EvnStickBase_IsInReg = 2 or ESB.EvnStickBase_IsPaid = 2 then 1";
		$cond[] = "
			when exists(
				select top 1 EvnStickWorkRelease_id 
				from 
					v_EvnStickWorkRelease with(nolock) 
				where 
					EvnStickBase_id = ESB.EvnStickBase_id 
					and (EvnStickWorkRelease_IsInReg = 2 or EvnStickWorkRelease_IsPaid = 2 )
			) then 1";

		$cond_str = count($cond)>0?implode("\n", $cond):"";

		$cond_dop = array();

		$cond_dop[] = "
			and (
				isnull(ESB.EvnStickBase_IsFSS, 1) = 2
				or exists(
					select top 1
						RegistryESStorage_id
					from
						v_RegistryESStorage with (nolock)
					where
						EvnStickBase_id = ESB.EvnStickBase_id
				)
			)
		";


		$cond_dop_str = count($cond_dop)>0?implode("\n", $cond_dop):"";
		$accessType .= "
			case when (
				case
					{$cond_str}
					else 0
				end = 1
			)
			{$cond_dop_str}
			then 'edit' else 'view' end as cancelAccessType, -- фильтры доступа для аннулирования ЛВН
		";

		$cond = array();

		if( getRegionNick() != 'kz' ) {
			$LpuTypeIds = $this->getDotherLpuTypes(11);// получаем дочерние id санатория
			$LpuTypeIds_str = implode(', ', $LpuTypeIds);

			$query = "
				select Lpu_id
				from v_Lpu with (nolock)
				where
					Lpu_id = :Lpu_id
					and LpuType_id in ({$LpuTypeIds_str})
			";
			$result = $this->getFirstRowFromQuery($query, array('Lpu_id'=> $data['session']['lpu_id']));
			if(!empty($result)) {//текущая МО явялется санаторием
				$cond[] = "
					when isnull(ESB.EvnStickBase_IsFSS, 1) = 2 and exists(
						select top 1
							SFDG.StickFSSDataGet_id
						from 
							StickFSSDataGet SFDG with (nolock)
						where
							SFDG.EvnStickBase_id = ESB.EvnStickBase_id
							and SFDG.StickFSSDataGet_StickReason = '08'
					) then 1
				";
			}
		} else {
			$cond[] = 'when 1=1 then 1';
		}

		$cond[] = "when isnull(ESB.EvnStickBase_IsFSS, 1) = 1 then 1";


		$cond_str = count($cond)>0?implode("\n", $cond):"";

		$accessType .= "
			case when (
				case
					{$cond_str}
					else 0
				end = 1
			)
			then 'edit' else 'view' end as addWorkReleaseAccessType, -- фильтры доступа для добавления освобождений
		";

		return $accessType;
	}


	/**
	 *	Загрузка формы редактирования дополнительного ЛВН
	 */
	function loadEvnStickDopEditForm($data) {
		$fields = "";
		$join = "";

		$selectPersonData = "RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) as EvnStickFullNameText,";
		if (allowPersonEncrypHIV($data['session'])) {
			$join .= " left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id ";
			$selectPersonData = "case
				when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
				else RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))
			end as EvnStickFullNameText,";
		}

		if (getRegionNick() != 'kz') {
			$fields .= ", RESS.RegistryESStorage_id";
			$join .= " left join v_RegistryESStorage RESS (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id ";
		}

		$this->load->model('Options_model');
		$options = $this->Options_model->getOptions();

		$accessType = $this->getEvnStickAccessType($data);

		$query = "
			select top 1
				{$accessType}
				ISNULL(ESD.EvnStickDop_id, 0) as EvnStick_id,
				ESD.StickFSSData_id,
				case when ESD.EvnStickDop_IsFSS = 2 then 1 else 0 end as EvnStickBase_IsFSS,
				ISNULL(ESD.EvnStickDop_pid, 0) as EvnStickDop_pid,
				ISNULL(ES.EvnStick_pid, 0) as EvnStick_pid,
				ISNULL(ESD.EvnStickDop_prid, 0) as EvnStick_prid,
				ISNULL(ES.Person_id, 0) as Person_id,
				ISNULL(ES.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(ES.Server_id, -1) as Server_id,
				ISNULL(ESD.EvnStickDop_oid, 0) as EvnStick_oid,
				case when ESD.EvnStickDop_IsFSS = 2 then null else ISNULL(ESD.EvnStickDop_IsOriginal, 1) end as EvnStick_IsOriginal,
				{$selectPersonData}
				ESD.StickWorkType_id,
				ESB.StickOrder_id,
				RTRIM(LTRIM(RTRIM(ISNULL(ESBP.EvnStickBase_Ser, '')) + ' ' + RTRIM(ISNULL(ESBP.EvnStickBase_Num, '')) + ', ' + ISNULL(convert(varchar(10), ESBP.EvnStickBase_setDate, 104), ''))) as EvnStickLast_Title,
				SC.StickCause_SysNick as PridStickCause_SysNick,
				SCp.StickCause_SysNick as PridStickCauseDid_SysNick,
				SLT.StickLeaveType_Code as PridStickLeaveType_Code1,
				PREDSTAC_STICK.StickLeaveType_Code as PridStickLeaveType_Code2,
				RTRIM(ISNULL(ESD.EvnStickDop_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESD.EvnStickDop_Num, '')) as EvnStick_Num,
				convert(varchar(10), ESD.EvnStickDop_setDate, 104) as EvnStick_setDate,
				ESD.Org_id,
				P.Person_Snils,
				RTRIM(ISNULL(ESD.EvnStickDop_OrgNick, '')) as EvnStick_OrgNick,
				RTRIM(ISNULL(ESD.Post_Name, '')) as Post_Name,
				ESB.StickCause_id,
				ES.StickCauseDopType_id,
				ESD.StickCause_did,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				convert(varchar(10), ESD.EvnStickDop_StickDT, 104) as EvnStick_StickDT,
				RTRIM(ISNULL(ES.EvnStick_sstNum, '')) as EvnStick_sstNum,
				ES.Org_did,
				ES.EvnStick_IsRegPregnancy,
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ES.EvnStick_IsDisability as EvnStick_IsDisability,
				ESB.MedStaffFact_id,
				ES.MedPersonal_id,
				-- ES.MedPersonal_mseid,
				ESB.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), ESD.EvnStickDop_disDT, 104) as EvnStick_disDate,
				ES.Lpu_oid,
				(SELECT COUNT(EvnStickDop_id) FROM v_EvnStickDop with(nolock) WHERE EvnStickDop_oid = :EvnStick_id) as CountDubles,
				ESDates.EvnSection_setDate,
				ESDates.EvnSection_disDate,
				ESNEXT.EvnStick_id as EvnStickNext_id,
				ESNEXT.EvnStick_Num as EvnStick_NumNext,
				RTRIM(ISNULL(SC.StickCause_Code, 0)) as StickCause_Code,
				ESWR.WorkReleaseCount,
				ISNULL(ESWR.WorkReleaseCountInOwnLpu, 0) + ISNULL(ESWRADD.WorkReleaseCountInOwnLpu, 0) as WorkReleaseCountInOwnLpu,
				ESCP.CarePersonCount,
				case
					when (PREDSTAC_STICK.EvnStickDop_id IS NOT NULL) then 2 else 1
				end as MaxDaysLimitAfterStac,
				ESD.Signatures_id,
				ESD.Signatures_iid,
				ESD.Lpu_id,
				ES.InvalidGroupType_id,
				convert(varchar(10), ESD.EvnStickDop_consentDT, 104) as EvnStickBase_consentDT
				{$fields}
			from v_EvnStickDop_all ESD with (nolock)
				inner join v_EvnStick_all ES with (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
				inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ESD.EvnStickDop_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
				left join v_Person_all P with(nolock) on P.PersonEvn_id = ESD.PersonEvn_id
				--left join v_Org O with (nolock) on O.Org_id = ESD.Org_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				left join v_EvnStickBase ESBP with (nolock) on ESBP.EvnStickBase_id = ESD.EvnStickDop_prid
				left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
				left join v_StickCause SCp with (nolock) on SCp.StickCause_id = ESBP.StickCause_did
				left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
				outer apply (
					select top 1
						EvnStickDop_id,
						PRED_SLT.StickLeaveType_Code
					from
						v_EvnStickDop PRED_ESD with (nolock)
						inner join v_EvnStick PRED_ES with (nolock) on PRED_ES.EvnStick_id = PRED_ESD.EvnStickDop_pid
						left join v_EvnStickBase PRED_ESB with (nolock) on PRED_ESB.EvnStickBase_id = PRED_ES.EvnStick_id
						inner join v_EvnPS PRED_EPS with (nolock) on PRED_EPS.EvnPS_id = PRED_ESD.EvnStickDop_rid
						inner join v_StickLeaveType PRED_SLT with (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
					where
						PRED_ESD.EvnStickDop_id = ESD.EvnStickDop_prid
						and PRED_SLT.StickLeaveType_Code IN ('37')
				) PREDSTAC_STICK
				-- продолжение ЛВН.
				outer apply (
					SELECT TOP 1
						ESD2.EvnStickDop_id as EvnStick_id,
						ESD2.EvnStickDop_Num as EvnStick_Num
					FROM
						v_EvnStickDop ESD2 with (nolock)
					WHERE
						ESD.EvnStickDop_id = ESD2.EvnStickDop_prid
				) ESNEXT
				-- количество периодов освобождения
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCount,
						sum(case when wr.Org_id = '".$data['session']['org_id']."' then 1 else 0 end) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
				) ESWR
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
						and wr.Org_id is null
						and ES.Lpu_id = '".$data['session']['lpu_id']."'
				) ESWRADD
				-- количество пациентов в уходе
				outer apply (
					select
						COUNT(EvnStickCarePerson_id) as CarePersonCount
					from v_EvnStickCarePerson CP with (nolock)
					where CP.Evn_id = ES.EvnStick_id
				) ESCP
				-- даты начала и конца движений в КВС связанных с ЛВН (кроме текущего КВС)
				outer apply (
					SELECT 
						convert(varchar(10), MIN(ESC.EvnSection_setDate), 104) as EvnSection_setDate,
						CASE WHEN MIN(ISNULL(ESC.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
							NULL
						ELSE
							convert(varchar(10), MAX(ESC.EvnSection_disDate), 104)
						END
							as EvnSection_disDate
					FROM EvnLink ELN with (nolock)
						left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ELN.Evn_id AND EPS.EvnPS_id <> :EvnStick_pid
						left join v_EvnSection ESC with (nolock) on ESC.EvnSection_pid = EPS.EvnPS_id
					WHERE ELN.Evn_lid = ES.EvnStick_id AND EPS.EvnPS_id IS NOT NULL
				) ESDates
				{$join}
			where ESD.EvnStickDop_id = :EvnStick_id
		";

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id'],
			'EvnStick_pid' => $data['EvnStick_pid']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			if (!empty($options['notice']['is_popup_message'])) {
				$RegistryESErrors = $this->getErrStickMessage($data['EvnStick_id']);
				if (!empty($RegistryESErrors) && !empty($result[0])) {
					$result[0]['RegistryESErrors'] = $RegistryESErrors;
				}
			}

			return $result;

		}
		else {
			return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
		}
	}

	/**
	 * Получение данных о предыдущем ЛВН
	 */
	function getEvnStickPridValues($data) {
		$resp = $this->queryResult("
			select top 1
				ISNULL(SLT.StickLeaveType_Code, 1) as PridStickLeaveType_Code2,
				case
					when (PRED_EPS.EvnPS_id IS NOT NULL) then 2 else 1
				end as MaxDaysLimitAfterStac,
				RTRIM(ISNULL(ES.EvnStick_Ser, '') + ' ' + ISNULL(ES.EvnStick_Num, '') + ', ' + ISNULL(convert(varchar(10), ES.EvnStick_setDate, 104), '')) as EvnStickLast_Title,
				convert(varchar(10), PredLastRelease.EvnStickWorkRelease_endDT, 104) as PridEvnStickWorkRelease_endDate,
				ESB.StickCause_id,
				SC.StickCause_SysNick,
				ESB.StickCause_did,
				SCd.StickCause_SysNick as StickCauseDid_SysNick,
				1 as StickWorkType_id,
				'' as Error_Msg
			from
				v_EvnStick ES (nolock)
				inner join v_EvnStickBase ESB (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_StickCause SC (nolock) on SC.StickCause_id = ESB.StickCause_id
				left join v_StickCause SCd (nolock) on SCd.StickCause_id = ESB.StickCause_did
				inner join v_StickLeaveType SLT (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
				outer apply(
					select top 1
						EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease with(nolock)
					where EvnStickBase_id = ES.EvnStick_id
					order by evnStickWorkRelease_endDT desc
				) PredLastRelease
				left join v_EvnPS PRED_EPS (nolock) on PRED_EPS.EvnPS_id = ES.EvnStick_rid
			where
				ES.EvnStick_id = :EvnStick_prid
			
			union all
			
			select
				ISNULL(SLT.StickLeaveType_Code, 1) as PridStickLeaveType_Code2,
				case
					when (PRED_EPS.EvnPS_id IS NOT NULL) then 2 else 1
				end as MaxDaysLimitAfterStac,
				RTRIM(ISNULL(ESD.EvnStickDop_Ser, '') + ' ' + ISNULL(ESD.EvnStickDop_Num, '') + ', ' + ISNULL(convert(varchar(10), ESD.EvnStickDop_setDate, 104), '')) as EvnStickLast_Title,
				convert(varchar(10), PredLastRelease.EvnStickWorkRelease_endDT, 104) as PridEvnStickWorkRelease_endDate,
				ESB.StickCause_id,
				SC.StickCause_SysNick,
				ESB.StickCause_did,
				SCd.StickCause_SysNick as StickCauseDid_SysNick,
				2 as StickWorkType_id,
				'' as Error_Msg
			from
				v_EvnStickDop ESD (nolock)
				left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
				left join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_StickCause SC (nolock) on SC.StickCause_id = ESB.StickCause_id
				left join v_StickCause SCd (nolock) on SCd.StickCause_id = ESB.StickCause_did
				inner join v_StickLeaveType SLT (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
				outer apply(
					select top 1
						EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease with(nolock)
					where EvnStickBase_id = ESD.EvnStickDop_id
					order by evnStickWorkRelease_endDT desc
				) PredLastRelease
				left join v_EvnPS PRED_EPS (nolock) on PRED_EPS.EvnPS_id = ESD.EvnStickDop_rid
			where
				ESD.EvnStickDop_id = :EvnStick_prid
		", array(
			'EvnStick_prid' => $data['EvnStick_prid']
		));

		return $resp;
	}


	/**
	 * Получение данных о продолжении ЛВН
	 */
	function getEvnStickProdValues($data) {
		$resp = $this->queryResult("
			select top 1
				RTRIM(ISNULL(ESnext.EvnStick_Ser, '') + ' ' + ISNULL(ESnext.EvnStick_Num, '') + ', ' + ISNULL(convert(varchar(10), ESnext.EvnStick_setDate, 104), '')) as EvnStick_Title,
				'' as Error_Msg
			from
				v_EvnStickBase ESB (nolock)
				left join v_EvnStick ESnext (nolock) on ESnext.EvnStick_id = ESB.EvnStickBase_nid
				left join v_EvnStickDop ESDnext (nolock) on ESDnext.EvnStickDop_id = ESB.EvnStickBase_nid
			where
				ESB.EvnStickBase_id = :EvnStick_id
		", array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		return $resp;
	}

	/**
	 * Получение данных о оригинале ЛВН
	 */
	function getEvnStickOriginInfo($data) {
		$resp = $this->queryResult("
			select top 1
				EvnStick_stacBegDate,
				EvnStick_stacEndDate,
				'' as Error_Msg
			from
				v_EvnStick ES (nolock)
			where
				ES.EvnStick_oid = :EvnStick_id
		", array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		return $resp;
	}


	/**
	 * Получение данных о ЛВН
	 */
	function getEvnStickInfo($data) {
		$resp = $this->queryResult("
			select top 1
				EvnStick_stacBegDate,
				EvnStick_stacEndDate,
				'' as Error_Msg
			from
				v_EvnStick ES (nolock)
			where
				ES.EvnStick_id = :EvnStick_id
		", array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		return $resp;
	}

	/**
	 * Получение ошибок ФЛК и ФСС
	 */
	function getErrStickMessage($EvnStick_id) {
		// получаем ошибки по последнему реестру для ЛВН
		$query = "
			select
				REET.RegistryESErrorStageType_id,
				REE.RegistryESError_Descr
			from 
				RegistryESError REE with (nolock)
				inner join v_RegistryESErrorType REET with (nolock) on REET.RegistryESErrorType_id = REE.RegistryESErrorType_id
				cross apply (
					select top 1 
						RES.RegistryES_id
					from 
						v_RegistryES RES with (nolock)
						inner join v_RegistryESData RESD with (nolock) on RESD.RegistryES_id = RES.RegistryES_id
					where 
						RESD.Evn_id = REE.Evn_id
					order by
						RES.RegistryES_insDT desc
				) RE
			where 
				REE.Evn_id = :EvnStick_id
				and RE.RegistryES_id = REE.RegistryES_id
				and REET.RegistryESErrorStageType_id in (1, 2)
		";
		$queryParams = array('EvnStick_id' => $EvnStick_id);
		$resp = $this->queryResult($query, $queryParams);
		return $resp;
	}


	/**
	 *	Загрузка формы редактирования ЛВН
	 */
	function loadEvnStickEditForm($data) {
		$EvnStickDop_pid = $this->getFirstResultFromQuery("
			select top 1 pES.EvnStick_id
			from v_EvnStick ES with(nolock)
			inner join v_EvnStick_all pES with(nolock) on pES.EvnStick_id = ES.EvnStick_pid
			where ES.StickWorkType_id = 2
			and ES.EvnStick_id = :EvnStick_id
		", $data, true);

		if ($EvnStickDop_pid === false) {
			return false;
		}

		$this->load->model('Options_model');
		$options = $this->Options_model->getOptions();

		$fields = "";
		$join = "";

		if (!empty($EvnStickDop_pid)) {
			$selectPersonData = "RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) as EvnStickFullNameText,";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id ";
				$selectPersonData = "case
					when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
					else RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))
				end as EvnStickFullNameText,";
			}

			if (getRegionNick() != 'kz') {
				$fields .= ", RESS.RegistryESStorage_id";
				$join .= " left join v_RegistryESStorage RESS (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id ";
			}

			$accessType = $this->getEvnStickAccessType($data);

			$query = "
				select top 1
					{$accessType}
					ISNULL(ESD.EvnStick_id, 0) as EvnStick_id,
					ESD.StickFSSData_id,
					case when ESD.EvnStick_IsFSS = 2 then 1 else 0 end as EvnStickBase_IsFSS,
					ISNULL(ESD.EvnStick_pid, 0) as EvnStickDop_pid,
					ISNULL(ES.EvnStick_pid, 0) as EvnStick_pid,
					ISNULL(ESD.EvnStick_prid, 0) as EvnStick_prid,
					ISNULL(ES.Person_id, 0) as Person_id,
					ISNULL(ES.PersonEvn_id, 0) as PersonEvn_id,
					ISNULL(ES.Server_id, -1) as Server_id,
					ISNULL(ESD.EvnStick_oid, 0) as EvnStick_oid,
					case when ESD.EvnStick_IsFSS = 2 then null else ISNULL(ESD.EvnStick_IsOriginal, 1) end as EvnStick_IsOriginal,
					{$selectPersonData}
					ESD.StickWorkType_id,
					ESB.StickOrder_id,
					PS.Person_id as Person_rid,
					P.Person_Snils,
					RTRIM(LTRIM(RTRIM(ISNULL(ESDP.EvnStick_Ser, '')) + ' ' + RTRIM(ISNULL(ESDP.EvnStick_Num, '')) + ', ' + ISNULL(convert(varchar(10), ESDP.EvnStick_setDate, 104), ''))) as EvnStickLast_Title,
					ESDP.EvnStick_id as EvnStickLast_id,
					SLT.StickLeaveType_Code as PridStickLeaveType_Code1,
					PREDSTAC_STICK.StickLeaveType_Code as PridStickLeaveType_Code2,
					SCp.StickCause_SysNick as PridStickCause_SysNick,
					SCpd.StickCause_SysNick as PridStickCauseDid_SysNick,
					RTRIM(ISNULL(ESD.EvnStick_Ser, '')) as EvnStick_Ser,
					RTRIM(ISNULL(ESD.EvnStick_Num, '')) as EvnStick_Num,
					convert(varchar(10), ESD.EvnStick_setDate, 104) as EvnStick_setDate,
					ESD.Org_id,
					RTRIM(ISNULL(ESD.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
					RTRIM(ISNULL(ESD.Post_Name, '')) as Post_Name,
					case 
						when isnull(ESB.EvnStickBase_IsFSS, 1) = 2 then FSS_DATA.StickCause_id
						else ESB.StickCause_id
					end as StickCause_id,
					ES.StickCauseDopType_id,
					ES.StickCause_did,
					ES.EvnStick_IsDateInReg,
					ES.EvnStick_IsDateInFSS,					
					ESB.pmUser_insID,
					convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
					convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
					convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
					RTRIM(ISNULL(ES.EvnStick_sstNum, '')) as EvnStick_sstNum,
					ES.Org_did,
					ES.EvnStick_IsRegPregnancy,
					ES.StickIrregularity_id,
					convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
					convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
					convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
					convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
					convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
					convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
					ES.EvnStick_IsDisability as EvnStick_IsDisability,
					ISNULL(ES.MedStaffFact_id, ESB.MedStaffFact_id) as MedStaffFact_id,
					ISNULL(ES.MedPersonal_id, ESB.MedPersonal_id) as MedPersonal_id,
					-- ES.MedPersonal_mseid,
					ESB.StickLeaveType_rid as StickLeaveType_id,
					convert(varchar(10), ESD.EvnStick_disDT, 104) as EvnStick_disDate,
					ES.Lpu_oid,
					(SELECT COUNT(EvnStick_id) FROM v_EvnStick with(nolock) WHERE EvnStick_oid = :EvnStick_id) as CountDubles,
					ESDates.EvnSection_setDate,
					ESDates.EvnSection_disDate,
					ESNEXT.EvnStickBase_id as EvnStickNext_id,
					ESNEXT.EvnStickBase_Num as EvnStick_NumNext,
					RTRIM(ISNULL(SC.StickCause_Code, 0)) as StickCause_Code,
					ESWR.WorkReleaseCount,
					ISNULL(ESWR.WorkReleaseCountInOwnLpu, 0) + ISNULL(ESWRADD.WorkReleaseCountInOwnLpu, 0) as WorkReleaseCountInOwnLpu,
					ESCP.CarePersonCount,
					case
						when (PREDSTAC_STICK.EvnStick_id IS NOT NULL) then 2 else 1
					end as MaxDaysLimitAfterStac,
					ESD.Signatures_id,
					ESD.Signatures_iid,
					ESD.Lpu_id,
					ESB.EvnStickBase_nid as EvnStick_nid,
					convert(varchar(10), ESD.EvnStickBase_consentDT, 104) as EvnStickBase_consentDT
					{$fields}
				from v_EvnStick_all ESD with (nolock)
					inner join v_EvnStick_all ES with (nolock) on ES.EvnStick_id = ESD.EvnStick_pid
					inner join v_EvnStickBase_all ESBp with (nolock) on ESBp.EvnStickBase_id = ES.EvnStick_id
					inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ESD.EvnStick_id
					inner join v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
					left join v_Person_all P with(nolock) on P.PersonEvn_id = ESD.PersonEvn_id
					--left join v_Org O with (nolock) on O.Org_id = ESD.Org_id
					left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
					left join v_EvnStick ESDP with (nolock) on ESDP.EvnStick_id = ESD.EvnStick_prid
					left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
					left join v_StickCause SCp with (nolock) on SCp.StickCause_id = ESBp.StickCause_id
					left join v_StickCause SCpd with (nolock) on SCpd.StickCause_id = ESBp.StickCause_did
					left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ESBp.StickLeaveType_rid
					outer apply (
						select top 1
							PRED_ES.EvnStick_id,
							PRED_SLT.StickLeaveType_Code
						from
							v_EvnStick PRED_ESD with (nolock)
							inner join v_EvnStick PRED_ES with (nolock) on PRED_ES.EvnStick_id = PRED_ESD.EvnStick_pid
							inner join v_EvnStickBase PRED_ESB with (nolock) on PRED_ESB.EvnStickBase_id = PRED_ES.EvnStick_id
							inner join v_EvnPS PRED_EPS with (nolock) on PRED_EPS.EvnPS_id = PRED_ESD.EvnStick_rid
							inner join v_StickLeaveType PRED_SLT with (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
						where
							PRED_ESD.EvnStick_id = ESD.EvnStick_prid
							and PRED_SLT.StickLeaveType_Code IN ('37')
					) PREDSTAC_STICK
					-- продолжение ЛВН.
					outer apply (
						SELECT TOP 1
							ESB2.EvnStickBase_id,
							ESB2.EvnStickBase_Num
						FROM
							v_EvnStickBase ESB2 with (nolock)
						WHERE
							ESB2.EvnStickBase_id = ESB.EvnStickBase_nid
					) ESNEXT
					-- количество периодов освобождения
					outer apply (
						select
							COUNT(EvnStickWorkRelease_id) as WorkReleaseCount,
							sum(case when wr.Org_id = '".$data['session']['org_id']."' then 1 else 0 end) as WorkReleaseCountInOwnLpu
						from v_EvnStickWorkRelease WR with (nolock)
						where WR.EvnStickBase_id = ES.EvnStick_id
					) ESWR
					outer apply (
						select top 1
							SCs.StickCause_id
						from 
							v_StickFSSDataGet SFDGs with (nolock)
							inner join v_StickCause SCs with (nolock) on SCs.StickCause_Code = SFDGs.StickFSSDataGet_StickReason
						where
							SFDGs.EvnStickBase_id = ESB.EvnStickBase_id
						order by
							SFDGs.StickFSSDataGet_updDT desc
					) FSS_DATA
					outer apply (
						select
							COUNT(EvnStickWorkRelease_id) as WorkReleaseCountInOwnLpu
						from v_EvnStickWorkRelease WR with (nolock)
						where WR.EvnStickBase_id = ES.EvnStick_id
							and wr.Org_id is null
							and ES.Lpu_id = '".$data['session']['lpu_id']."'
					) ESWRADD
					-- количество пациентов в уходе
					outer apply (
						select
							COUNT(EvnStickCarePerson_id) as CarePersonCount
						from v_EvnStickCarePerson CP with (nolock)
						where CP.Evn_id = ES.EvnStick_id
					) ESCP
					-- даты начала и конца движений в КВС связанных с ЛВН (кроме текущего КВС)
					outer apply (
						SELECT 
							convert(varchar(10), MIN(ESC.EvnSection_setDate), 104) as EvnSection_setDate,
							CASE WHEN MIN(ISNULL(ESC.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
								NULL
							ELSE
								convert(varchar(10), MAX(ESC.EvnSection_disDate), 104)
							END
								as EvnSection_disDate
						FROM EvnLink ELN with (nolock)
							left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ELN.Evn_id AND EPS.EvnPS_id <> :EvnStick_pid
							left join v_EvnSection ESC with (nolock) on ESC.EvnSection_pid = EPS.EvnPS_id
						WHERE ELN.Evn_lid = ES.EvnStick_id AND EPS.EvnPS_id IS NOT NULL
					) ESDates
					{$join}
				where ESD.EvnStick_id = :EvnStick_id
			";

			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'Lpu_id' => $data['Lpu_id'],
				'Org_id' => $data['session']['org_id'],
				'EvnStick_pid' => $data['EvnStick_pid']
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
			}
		} else {
			$selectPersonData = "RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) as EvnStickFullNameText,";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id ";
				$selectPersonData = "case
					when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
					else RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))
				end as EvnStickFullNameText,";
			}

			if (getRegionNick() != 'kz') {
				$fields .= ", RESS.RegistryESStorage_id";
				$join .= " left join v_RegistryESStorage RESS (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id ";
			}
			if (getRegionNick() == 'kz') {
				$fields .= ", case when D.Diag_Code between 'A15.0' and 'A19.9' or D.Diag_Code between 'A30.1' and 'A30.2' then 1 else 0 end as isTubDiag";
				$join .= " left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = ES.EvnStick_mid";
				$join .= " left join v_Diag D with(nolock) on D.Diag_id = EPL.Diag_id";
			}

			$accessType = $this->getEvnStickAccessType($data);

			$query = "
			select top 1
				{$accessType}
				ISNULL(ES.EvnStick_id, 0) as EvnStick_id,
				ES.StickFSSData_id,
				case when ES.EvnStick_IsFSS = 2 then 1 else 0 end as EvnStickBase_IsFSS,
				ISNULL(ES.EvnStick_mid, 0) as EvnStick_mid,
				ISNULL(ES.EvnStick_pid, 0) as EvnStick_pid,
				ISNULL(ES.EvnStick_prid, 0) as EvnStick_prid,
				ISNULL(ES.EvnStick_IsPaid, 1) as EvnStick_IsPaid,
				ISNULL(ES.EvnStick_IsInReg, 1) as EvnStick_IsInReg,
				ISNULL(ES.Person_id, 0) as Person_id,
				ISNULL(ES.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(ES.Server_id, -1) as Server_id,
				ISNULL(ES.EvnStick_oid, 0) as EvnStick_oid,
				case when ES.EvnStick_IsFSS = 2 then null else ISNULL(ES.EvnStick_IsOriginal, 1) end as EvnStick_IsOriginal,
				ISNULL(PRED_STICK.StickLeaveType_Code, 1) as PridStickLeaveType_Code2,
				SCp.StickCause_SysNick as PridStickCause_SysNick,
				SCpd.StickCause_SysNick as PridStickCauseDid_SysNick,
				convert(varchar(10), PRED_STICK.EvnStickWorkRelease_endDT, 104) as PridEvnStickWorkRelease_endDate,
				{$selectPersonData}
				ES.StickWorkType_id,
				PS.Person_id as Person_rid,
				P.Person_Snils,
				ES.StickOrder_id,
				RTRIM(ISNULL(ESP.EvnStick_Ser, '') + ' ' + ISNULL(ESP.EvnStick_Num, '') + ', ' + ISNULL(convert(varchar(10), ESP.EvnStick_setDate, 104), '')) as EvnStickLast_Title,
				RTRIM(ISNULL(ES.EvnStick_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ES.EvnStick_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
				ES.Org_id,
				RTRIM(ISNULL(ES.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
				RTRIM(ISNULL(ES.Post_Name, '')) as Post_Name,
				ES.StickCause_id,
				ES.StickCauseDopType_id,
				ES.StickCause_did,
				ES.EvnStick_IsDateInReg,
				ES.EvnStick_IsDateInFSS,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				RTRIM(ISNULL(ES.EvnStick_sstNum, '')) as EvnStick_sstNum,
				ES.Org_did,
				ES.EvnStick_IsRegPregnancy,
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ES.EvnStick_IsDisability as EvnStick_IsDisability,
				-- ES.MedPersonal_mseid,
				ESB.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), ES.EvnStick_disDT, 104) as EvnStick_disDate,
				ISNULL(ES.MedStaffFact_id, ESB.MedStaffFact_id) as MedStaffFact_id,
				ISNULL(ISNULL(ES.MedPersonal_id,MSF.MedPersonal_id), ESB.MedPersonal_id) as MedPersonal_id,
				ES.Lpu_oid,
				EL.EvnLink_id,
				(SELECT COUNT(EvnStick_id) FROM v_EvnStick with(nolock) WHERE EvnStick_oid = :EvnStick_id) as CountDubles,
				
				ESDates.EvnSection_setDate,
				ESDates.EvnSection_disDate,

				case 
					when isnull(ESB.EvnStickBase_IsFSS, 1) = 2 then FSS_DATA.StickCause_id
					else ESB.StickCause_id
				end as StickCause_id,
				ESNEXT.EvnStickBase_id as EvnStickNext_id,
				ESNEXT.EvnStickBase_Num as EvnStick_NumNext,
				RTRIM(ISNULL(SC.StickCause_Code, 0)) as StickCause_Code,
				ESWR.WorkReleaseCount,
				ISNULL(ESWR.WorkReleaseCountInOwnLpu, 0) + ISNULL(ESWRADD.WorkReleaseCountInOwnLpu, 0) as WorkReleaseCountInOwnLpu,
				ESCP.CarePersonCount,
				case
					when (PREDSTAC_STICK.EvnStick_id IS NOT NULL) then 2 else 1
				end as MaxDaysLimitAfterStac,
				
				convert(varchar(10), ES.EvnStick_adoptDate, 104) as EvnStick_adoptDate,
				convert(varchar(10), ES.EvnStick_regBegDate, 104) as EvnStick_regBegDate,
				convert(varchar(10), ES.EvnStick_regEndDate, 104) as EvnStick_regEndDate,
				ES.StickRegime_id,
				ES.EvnStick_IsDisability,
				convert(varchar(10), ES.EvnStick_StickDT, 104) as EvnStick_StickDT,
				ES.InvalidGroupType_id,
				ES.Signatures_id,
				ES.Signatures_iid,
				ES.Lpu_id,
				ESB.EvnStickBase_nid as EvnStick_nid,
				ESB.pmUser_insID,
				convert(varchar(10), ES.EvnStickBase_consentDT, 104) as EvnStickBase_consentDT,
				SFDG.EvnStick_NumPar
				{$fields}
			from v_EvnStick_all ES with (nolock)
				inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_PersonState PS with (nolock) on PS.Person_id = ES.Person_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
				left join v_Person_all P with(nolock) on P.PersonEvn_id = ES.PersonEvn_id
				--left join v_Org O with (nolock) on O.Org_id = ES.Org_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				left join v_EvnStick_all ESP with (nolock) on ESP.EvnStick_id = ES.EvnStick_prid
				left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
				left join v_StickCause SCp with (nolock) on SCp.StickCause_id = ESp.StickCause_id
				left join v_StickCause SCpd with (nolock) on SCpd.StickCause_id = ESp.StickCause_did
				left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
				left join v_StickFSSDataGet SFDG with (nolock) on SFDG.StickFSSData_id = ESB.StickFSSData_id
				outer apply (
					select top 1
						EvnStick_id
					from
						v_EvnStick PRED_ESD (nolock)
						inner join v_EvnStickBase PRED_ESB (nolock) on PRED_ESB.EvnStickBase_id = PRED_ESD.EvnStick_id
						inner join v_EvnPS PRED_EPS (nolock) on PRED_EPS.EvnPS_id = PRED_ESD.EvnStick_rid
						inner join v_StickLeaveType PRED_SLT (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
					where
						PRED_ESD.EvnStick_id = ES.EvnStick_prid
						and PRED_SLT.StickLeaveType_Code IN ('37')
				) PREDSTAC_STICK
			
				-- Исход первичного ЛВН
				outer apply (
					select top 1
						StickLeaveType_Code,
						EvnStickWorkRelease_endDT
					from
						v_EvnStick PRED_ESD (nolock)
						inner join v_EvnStickBase PRED_ESB (nolock) on PRED_ESB.EvnStickBase_id = PRED_ESD.EvnStick_id
						inner join v_StickLeaveType PRED_SLT (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
						outer apply(
							select top 1
								EvnStickWorkRelease_endDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = PRED_ESD.EvnStick_id
							order by evnStickWorkRelease_endDT desc
						) PredLastRelease
					where
						PRED_ESD.EvnStick_id = ES.EvnStick_prid
				) PRED_STICK
				outer apply (
					select top 1 EvnLink_id
					from v_EvnLink with (nolock)
					where Evn_lid = ES.EvnStick_id
				) EL
				-- продолжение ЛВН.
				outer apply (
					SELECT TOP 1
						ESB2.EvnStickBase_id,
						ESB2.EvnStickBase_Num
					FROM
						v_EvnStickBase_all ESB2 with (nolock)
					WHERE
						ESB2.EvnStickBase_id = ESB.EvnStickBase_nid
				) ESNEXT
				-- количество периодов освобождения
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCount,
						sum(case when wr.Org_id = '".$data['session']['org_id']."' then 1 else 0 end) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
				) ESWR
				outer apply (
					select top 1
						SCs.StickCause_id
					from 
						v_StickFSSDataGet SFDGs with (nolock)
						inner join v_StickCause SCs with (nolock) on SCs.StickCause_Code = SFDGs.StickFSSDataGet_StickReason
					where
						SFDGs.EvnStickBase_id = ESB.EvnStickBase_id
					order by
						SFDGs.StickFSSDataGet_updDT desc
				) FSS_DATA
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
						and wr.Org_id is null
						and ES.Lpu_id = '".$data['session']['lpu_id']."'
				) ESWRADD
				-- количество пациентов в уходе
				outer apply (
					select
						COUNT(EvnStickCarePerson_id) as CarePersonCount
					from v_EvnStickCarePerson CP with (nolock)
					where CP.Evn_id = ES.EvnStick_id
				) ESCP
				-- даты начала и конца движений в КВС связанных с ЛВН (кроме текущего КВС)
				outer apply (
					SELECT 
						convert(varchar(10), MIN(ESC.EvnSection_setDate), 104) as EvnSection_setDate,
						
						CASE WHEN MIN(ISNULL(ESC.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
							NULL
						ELSE
							convert(varchar(10), MAX(ESC.EvnSection_disDate), 104)
						END
							as EvnSection_disDate
					FROM EvnLink ELN with (nolock)
						left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ELN.Evn_id AND EPS.EvnPS_id <> ES.EvnStick_mid
						left join v_EvnSection ESC with (nolock) on ESC.EvnSection_pid = EPS.EvnPS_id AND ISNULL(ESC.EvnSection_IsPriem, 1) = 1
					WHERE ELN.Evn_lid = ES.EvnStick_id AND EPS.EvnPS_id IS NOT NULL
				) ESDates
				{$join}
			where ES.EvnStick_id = :EvnStick_id
		";

			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'Lpu_id' => $data['Lpu_id'],
				'EvnStick_pid' => $data['EvnStick_pid'],
				'Org_id' => $data['session']['org_id']
			);
			// echo getDebugSQL($query, $queryParams);exit;
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$result = $result->result('array');
				if (!empty($options['notice']['is_popup_message'])) {
					$RegistryESErrors = $this->getErrStickMessage($data['EvnStick_id']);
					if (!empty($RegistryESErrors) && !empty($result[0])) {
						$result[0]['RegistryESErrors'] = $RegistryESErrors;
					}
				}
				return $result;
			}
			else {
				return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
			}
		}
	}

	/**
	 *	Загрузка формы редактирования ЛВН
	 */
	function loadEvnStickEditFormForDelDocs($data) {

		$this->load->model('Options_model');
		$options = $this->Options_model->getOptions();

		$fields = "";
		$join = "";

		$selectPersonData = "RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) as EvnStickFullNameText,";
			if (allowPersonEncrypHIV($data['session'])) {
				$join .= " left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id ";
				$selectPersonData = "case
					when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
					else RTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))
				end as EvnStickFullNameText,";
			}

			if (getRegionNick() != 'kz') {
				$fields .= ", RESS.RegistryESStorage_id";
				$join .= " left join v_RegistryESStorage RESS (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id ";
			}
			if (getRegionNick() == 'kz') {
				$fields .= ", case when D.Diag_Code between 'A15.0' and 'A19.9' or D.Diag_Code between 'A30.1' and 'A30.2' then 1 else 0 end as isTubDiag";
				$join .= " left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = ES.EvnStick_mid";
				$join .= " left join v_Diag D with(nolock) on D.Diag_id = EPL.Diag_id";
			}

			$query = "
			select top 1
				'view' as accessType,
				ISNULL(ES.EvnStick_id, 0) as EvnStick_id,
				ESB.StickFSSData_id,
				case when ESB.EvnStickBase_IsFSS = 2 then 1 else 0 end as EvnStickBase_IsFSS,
				ISNULL(ESB.EvnStickBase_mid, 0) as EvnStick_mid,
				ISNULL(E.Evn_pid, 0) as EvnStick_pid,
				ISNULL(ES.EvnStick_prid, 0) as EvnStick_prid,
				ISNULL(ESB.EvnStickBase_IsPaid, 1) as EvnStick_IsPaid,
				ISNULL(ESB.EvnStickBase_IsInReg, 1) as EvnStick_IsInReg,
				ISNULL(E.Person_id, 0) as Person_id,
				ISNULL(E.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(E.Server_id, -1) as Server_id,
				ISNULL(ESB.EvnStickBase_oid, 0) as EvnStick_oid,
				case when ESB.EvnStickBase_IsFSS = 2 then null else ISNULL(ESB.EvnStickBase_IsOriginal, 1) end as EvnStick_IsOriginal,
				ISNULL(PRED_STICK.StickLeaveType_Code, 1) as PridStickLeaveType_Code2,
				SCp.StickCause_SysNick as PridStickCause_SysNick,
				SCpd.StickCause_SysNick as PridStickCauseDid_SysNick,
				convert(varchar(10), PRED_STICK.EvnStickWorkRelease_endDT, 104) as PridEvnStickWorkRelease_endDate,
				{$selectPersonData}
				ESB.EvnStickBase_id,
				PS.Person_id as Person_rid,
				P.Person_Snils,
				ESB.StickOrder_id,
				RTRIM(ISNULL(ESP.EvnStick_Ser, '') + ' ' + ISNULL(ESP.EvnStick_Num, '') + ', ' + ISNULL(convert(varchar(10), ESP.EvnStick_setDate, 104), '')) as EvnStickLast_Title,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				convert(varchar(10), E.Evn_setDT, 104) as EvnStick_setDate,
				ESB.Org_id,
				RTRIM(ISNULL(ESB.EvnStickBase_OrgNick, '')) as EvnStick_OrgNick,
				RTRIM(ISNULL(ESB.Post_Name, '')) as Post_Name,
				ESB.StickCause_id,
				ESB.StickCauseDopType_id,
				ESB.StickCause_did,
				ES.EvnStick_IsDateInReg,
				ES.EvnStick_IsDateInFSS,
				convert(varchar(10), ES.EvnStick_BirthDate, 104) as EvnStick_BirthDate,
				convert(varchar(10), ES.EvnStick_sstBegDate, 104) as EvnStick_sstBegDate,
				convert(varchar(10), ES.EvnStick_sstEndDate, 104) as EvnStick_sstEndDate,
				RTRIM(ISNULL(ES.EvnStick_sstNum, '')) as EvnStick_sstNum,
				ESB.Org_did,
				ESB.EvnStickBase_IsRegPregnancy,
				ES.StickIrregularity_id,
				convert(varchar(10), ES.EvnStick_irrDT, 104) as EvnStick_irrDate,
				convert(varchar(10), ESB.EvnStickBase_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ESB.EvnStickBase_stacEndDate, 104) as EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 104) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 104) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 104) as EvnStick_mseExamDate,
				ESB.EvnStickBase_IsDisability as EvnStick_IsDisability,
				-- ES.MedPersonal_mseid,
				ESB.StickLeaveType_rid as StickLeaveType_id,
				convert(varchar(10), E.Evn_disDT, 104) as EvnStick_disDate,
				ISNULL(ESB.MedStaffFact_id, ESB.MedStaffFact_id) as MedStaffFact_id,
				ISNULL(ISNULL(ESB.MedPersonal_id,MSF.MedPersonal_id), ESB.MedPersonal_id) as MedPersonal_id,
				ES.Lpu_oid,
				EL.EvnLink_id,
				(SELECT COUNT(EvnStick_id) FROM v_EvnStick with(nolock) WHERE EvnStick_oid = :EvnStick_id) as CountDubles,
				
				ESDates.EvnSection_setDate,
				ESDates.EvnSection_disDate,

				case 
					when isnull(ESB.EvnStickBase_IsFSS, 1) = 2 then FSS_DATA.StickCause_id
					else ESB.StickCause_id
				end as StickCause_id,
				ESNEXT.EvnStickBase_id as EvnStickNext_id,
				ESNEXT.EvnStickBase_Num as EvnStick_NumNext,
				RTRIM(ISNULL(SC.StickCause_Code, 0)) as StickCause_Code,
				ESWR.WorkReleaseCount,
				ISNULL(ESWR.WorkReleaseCountInOwnLpu, 0) + ISNULL(ESWRADD.WorkReleaseCountInOwnLpu, 0) as WorkReleaseCountInOwnLpu,
				ESCP.CarePersonCount,
				case
					when (PREDSTAC_STICK.EvnStick_id IS NOT NULL) then 2 else 1
				end as MaxDaysLimitAfterStac,
				
				convert(varchar(10), ES.EvnStick_adoptDate, 104) as EvnStick_adoptDate,
				convert(varchar(10), ES.EvnStick_regBegDate, 104) as EvnStick_regBegDate,
				convert(varchar(10), ES.EvnStick_regEndDate, 104) as EvnStick_regEndDate,
				ES.StickRegime_id,
				ESB.EvnStickBase_IsDisability,
				convert(varchar(10), ESB.EvnStickBase_StickDT, 104) as EvnStick_StickDT,
				ESB.InvalidGroupType_id,
				ES.Signatures_id,
				ES.Signatures_iid,
				E.Lpu_id,
				ESB.EvnStickBase_nid as EvnStick_nid,
				E.pmUser_insID,
				convert(varchar(10), ESB.EvnStickBase_consentDT, 104) as EvnStickBase_consentDT,
				SFDG.EvnStick_NumPar
				{$fields}
			from EvnStick ES with (nolock)
				inner join Evn E on E.Evn_id = ES.EvnStick_id
				inner join EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_PersonState PS with (nolock) on PS.Person_id = E.Person_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
				left join v_Person_all P with(nolock) on P.PersonEvn_id = E.PersonEvn_id
				--left join v_Org O with (nolock) on O.Org_id = ES.Org_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				left join v_EvnStick_all ESP with (nolock) on ESP.EvnStick_id = ES.EvnStick_prid
				left join StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				left join v_StickCause SCp with (nolock) on SCp.StickCause_id = ESp.StickCause_id
				left join v_StickCause SCpd with (nolock) on SCpd.StickCause_id = ESp.StickCause_did
				left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
				left join v_StickFSSDataGet SFDG with (nolock) on SFDG.StickFSSData_id = ESB.StickFSSData_id
				outer apply (
					select top 1
						EvnStick_id
					from
						v_EvnStick PRED_ESD (nolock)
						inner join v_EvnStickBase PRED_ESB (nolock) on PRED_ESB.EvnStickBase_id = PRED_ESD.EvnStick_id
						inner join v_EvnPS PRED_EPS (nolock) on PRED_EPS.EvnPS_id = PRED_ESD.EvnStick_rid
						inner join v_StickLeaveType PRED_SLT (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
					where
						PRED_ESD.EvnStick_id = ES.EvnStick_prid
						and PRED_SLT.StickLeaveType_Code IN ('37')
				) PREDSTAC_STICK
			
				-- Исход первичного ЛВН
				outer apply (
					select top 1
						StickLeaveType_Code,
						EvnStickWorkRelease_endDT
					from
						v_EvnStick PRED_ESD (nolock)
						inner join v_EvnStickBase PRED_ESB (nolock) on PRED_ESB.EvnStickBase_id = PRED_ESD.EvnStick_id
						inner join v_StickLeaveType PRED_SLT (nolock) on PRED_SLT.StickLeaveType_id = PRED_ESB.StickLeaveType_rid
						outer apply(
							select top 1
								EvnStickWorkRelease_endDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = PRED_ESD.EvnStick_id
							order by evnStickWorkRelease_endDT desc
						) PredLastRelease
					where
						PRED_ESD.EvnStick_id = ES.EvnStick_prid
				) PRED_STICK
				outer apply (
					select top 1 EvnLink_id
					from v_EvnLink with (nolock)
					where Evn_lid = ES.EvnStick_id
				) EL
				-- продолжение ЛВН.
				outer apply (
					SELECT TOP 1
						ESB2.EvnStickBase_id,
						ESB2.EvnStickBase_Num
					FROM
						v_EvnStickBase_all ESB2 with (nolock)
					WHERE
						ESB2.EvnStickBase_id = ESB.EvnStickBase_nid
				) ESNEXT
				-- количество периодов освобождения
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCount,
						sum(case when wr.Org_id = '".$data['session']['org_id']."' then 1 else 0 end) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
				) ESWR
				outer apply (
					select top 1
						SCs.StickCause_id
					from 
						v_StickFSSDataGet SFDGs with (nolock)
						inner join v_StickCause SCs with (nolock) on SCs.StickCause_Code = SFDGs.StickFSSDataGet_StickReason
					where
						SFDGs.EvnStickBase_id = ESB.EvnStickBase_id
					order by
						SFDGs.StickFSSDataGet_updDT desc
				) FSS_DATA
				outer apply (
					select
						COUNT(EvnStickWorkRelease_id) as WorkReleaseCountInOwnLpu
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ES.EvnStick_id
						and wr.Org_id is null
						and E.Lpu_id = '".$data['session']['lpu_id']."'
				) ESWRADD
				-- количество пациентов в уходе
				outer apply (
					select
						COUNT(EvnStickCarePerson_id) as CarePersonCount
					from v_EvnStickCarePerson CP with (nolock)
					where CP.Evn_id = ES.EvnStick_id
				) ESCP
				-- даты начала и конца движений в КВС связанных с ЛВН (кроме текущего КВС)
				outer apply (
					SELECT 
						convert(varchar(10), MIN(ESC.EvnSection_setDate), 104) as EvnSection_setDate,
						
						CASE WHEN MIN(ISNULL(ESC.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
							NULL
						ELSE
							convert(varchar(10), MAX(ESC.EvnSection_disDate), 104)
						END
							as EvnSection_disDate
					FROM EvnLink ELN with (nolock)
						left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ELN.Evn_id AND EPS.EvnPS_id <> ESB.EvnStickBase_mid
						left join v_EvnSection ESC with (nolock) on ESC.EvnSection_pid = EPS.EvnPS_id AND ISNULL(ESC.EvnSection_IsPriem, 1) = 1
					WHERE ELN.Evn_lid = ES.EvnStick_id AND EPS.EvnPS_id IS NOT NULL
				) ESDates
				{$join}
			where ES.EvnStick_id = :EvnStick_id
		";

			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'Lpu_id' => $data['Lpu_id'],
				'EvnStick_pid' => $data['EvnStick_pid'],
				'Org_id' => $data['session']['org_id']
			);
			// echo getDebugSQL($query, $queryParams);exit;
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$result = $result->result('array');
				if (!empty($options['notice']['is_popup_message'])) {
					$RegistryESErrors = $this->getErrStickMessage($data['EvnStick_id']);
					if (!empty($RegistryESErrors) && !empty($result[0])) {
						$result[0]['RegistryESErrors'] = $RegistryESErrors;
					}
				}
				return $result;
			}
			else {
				return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
			}
		
	}
	
	/**
	 * получение даты начала и конца лечения в стационаре
	 *
	 * $data["EvnPS_id"]
	 *
	 * @param $data
	 * @return bool
	 */
	function getBegEndDatesInStac($data){
		$query = "
			SELECT 
				convert(varchar(10), MIN(ES.EvnSection_setDate), 104) as EvnSection_setDate,
				CASE WHEN MIN(ISNULL(ES.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
					NULL
				ELSE
					convert(varchar(10), MAX(ES.EvnSection_disDate), 104)
				END
					as EvnSection_disDate
			FROM 
				v_EvnSection ES with (nolock)
			WHERE 
				ES.EvnSection_pid = :EvnPS_id AND ISNULL(ES.EvnSection_IsPriem, 1) = 1
		";

		$result = $this->db->query($query, array('EvnPS_id' => $data['EvnPS_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение даты начала и даты окончания действия отделения
	 */
	function getEvnSectionDatesForEvnStick($data) {
		$query = "
			SELECT 
				convert(varchar(10), MIN(ES.EvnSection_setDate), 104) as EvnSection_setDate,
				CASE WHEN (MIN(cast(ISNULL(ES.EvnSection_disDate,0) as integer)) = 0) THEN 
					NULL
				ELSE
					convert(varchar(10), MAX(ISNULL(ES.EvnSection_disDate,0)), 104)
				END
					as EvnSection_disDate
			FROM EvnLink EL with(nolock)
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EL.Evn_id
				left join v_EvnSection ES with(nolock) on ES.EvnSection_pid = EPS.EvnPS_id
			WHERE EL.Evn_lid = :EvnStick_id AND EPS.EvnPS_id IS NOT NULL
		";

		$result = $this->db->query($query, array('EvnStick_id' => $data['EvnStick_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Поиск КВС через EvnLink (для ЛВН которые заведены через ТАП)
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFromEvnLink($data){
		$query = "
			SELECT 
				EPS.EvnPS_id
			FROM EvnLink EL with(nolock)
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EL.Evn_id
			WHERE EL.Evn_lid = :EvnStick_id AND EPS.EvnPS_id IS NOT NULL
		";

		$result = $this->db->query($query, array('EvnStick_id' => $data['EvnStick_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Находим движения в найденой КВС для ЛВН (используется для определения доступа к полняв формы ЛВН)
	 *
	 * @param $data
	 * @return bool
	 */
	function getEvnSectionList($data){
		$query = "
			select
				v_LpuUnit.LpuUnitType_SysNick,
				v_EvnSection.EvnSection_id
			from 
				v_EvnSection with (nolock)
				inner join LpuSection  with (nolock) on LpuSection.LpuSection_id = v_EvnSection.LpuSection_id
				inner join v_LpuUnit with (nolock) on v_LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
			where 
				v_EvnSection.EvnSection_pid = :EvnPS_id
		";

		$result = $this->db->query($query, array('EvnPS_id' => $data['EvnPS_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}



	/**
	 *	Загрузка списка ЛВН
	 */
	function loadEvnStickGrid($data) {
		$accessType = $this->getEvnStickAccessType($data);

		$query = "
			select
				{$accessType}
				ESB.EvnStickBase_IsDelQueue as EvnStick_IsDelQueue,
				ESB.EvnStickBase_id as EvnStick_id,
				ESB.EvnStickBase_mid as EvnStick_mid,
				ESB.EvnStickBase_pid as EvnStick_pid,
				case
					when EvnPL.EvnPL_id is not null then 'EvnPL'
					when EvnPLStom.EvnPLStom_id is not null then 'EvnPLStom'
					when EvnPS.EvnPS_id is not null then 'EvnPS'
					else ''
				end as parentClass,
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType, -- Вид док-та (код)
				case
					when EC.EvnClass_SysNick in ('EvnStick', 'EvnStickDop') then 'ЛВН'
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 'Справка учащегося'
					else ''
				end as StickType_Name, -- Вид док-та (наименование)
				RTRIM(ISNULL(SWT.StickWorkType_Name, '')) as StickWorkType_Name,
				ESB.Person_id,
				ESB.PersonEvn_id,
				ESB.Server_id,
				convert(varchar(10), ISNULL(ESB.EvnStickBase_setDT, ESBD.EvnStickBase_setDT), 104) as EvnStick_setDate, -- Дата выдачи
				convert(varchar(10), ESBWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate, -- Освобожден с
				convert(varchar(10), ESBWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate, -- Освобожден по
				case
					when EC.EvnClass_SysNick = 'EvnStickStudent' then convert(varchar(10), ESBWR.EvnStickWorkRelease_endDT, 104)
					else convert(varchar(10), ESB.EvnStickBase_disDT, 104)
				end as EvnStick_disDate, -- Дата закрытия
				case
					when ESB.EvnStickBase_mid = :EvnStick_pid then 'Текущий'
					when EvnPL.EvnPL_id is not null then 'ТАП'
					when EvnPLStom.EvnPLStom_id is not null then 'Стом. ТАП'
					when EvnPS.EvnPS_id is not null then 'КВС'
					else ''
				end as EvnStick_ParentTypeName, -- оригинальность
				case
					when ISNULL(ESB.EvnStickBase_IsOriginal,1) = 1 then 'Оригинал'
					else 'Дубликат'
				end as EvnStick_IsOriginal, -- тип родительского документа
				case
					when ESB.EvnStickBase_mid = :EvnStick_pid then ''
					when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
					when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
					when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
					else ''
				end as EvnStick_ParentNum, -- номер родительского документа
				case
					when  RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 1
					else 0
				end as EvnStick_isELN,
				
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_stacBegDate, 104) as EvnStick_stacBegDate,
				convert(varchar(10), ES.EvnStick_stacEndDate, 104) as EvnStick_stacEndDate,
				ESDates.EvnSection_setDate,
				ESDates.EvnSection_disDate,
				ESTATUS.EvnStatus_Name,
				SFT.StickFSSType_Name,
				isnull(SFD.StickFSSData_id, 0) as requestExist,
				O.Org_Nick as Lpu_Nick,
				EC.EvnClass_SysNick
			from v_EvnStickBase ESB with (nolock)
			 	inner join EvnClass EC with(nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
					and EC.EvnClass_SysNick = 'EvnStickDop'
				-- EVN_STICK
				left join v_EvnStick ES with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
				-- ТАП/КВС
				left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
				left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
				left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
				-- end ТАП/КВС
				left join StickOrder SO with(nolock) on SO.StickOrder_id = ISNULL(ESBD.StickOrder_id, ESB.StickOrder_id)
				left join StickWorkType SWT with(nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				outer apply (
					select top 1 StickFSSData_id
					from v_StickFSSData with (nolock)
					where
						StickFSSData_StickNum = ESB.EvnStickBase_Num
						and StickFSSDataStatus_id not in (3, 4, 5)
				) as SFD
				left join v_RegistryESStorage RESS with (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id
				-- даты начала и конца движений в КВС связанных с ЛВН (кроме текущего КВС)
				outer apply (
					SELECT 
						convert(varchar(10), MIN(ESC.EvnSection_setDate), 104) as EvnSection_setDate,
						CASE WHEN MIN(ISNULL(ESC.EvnSection_disDate,'1900-01-01')) = '1900-01-01' THEN 
							NULL
						ELSE
							convert(varchar(10), MAX(ESC.EvnSection_disDate), 104)
						END
							as EvnSection_disDate
					FROM EvnLink ELN with (nolock)
						left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = ELN.Evn_id AND EPS.EvnPS_id <> :EvnStick_pid
						left join v_EvnSection ESC with (nolock) on ESC.EvnSection_pid = EPS.EvnPS_id
					WHERE ELN.Evn_lid = ESB.EvnStickBase_id AND EPS.EvnPS_id IS NOT NULL
				) ESDates
				outer apply (
					select
						max(EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endDT,
						min(EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDT
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ISNULL(ESB.EvnStickBase_id, ESBD.EvnStickBase_id)
				) ESBWR
				outer apply (
					select top 1
						WR.Org_id
					from v_EvnStickWorkRelease WR with (nolock)
					where WR.EvnStickBase_id = ISNULL(ESBD.EvnStickBase_id, ESB.EvnStickBase_id) order by WR.EvnStickWorkRelease_begDT asc
				) ESBWRFIRST
				left join v_Lpu L (nolock) on L.Lpu_id = ESB.Lpu_id
				left join Org O (nolock) on O.Org_id = ISNULL(ESBWRFIRST.Org_id, L.Org_id)
			where
				ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :EvnStick_pid
				)
			order by
				EvnStick_ParentTypeName desc
		";

		//echo getDebugSQL($query, array('EvnStick_pid' => $data['EvnStick_pid'], 'Lpu_id' => $data['Lpu_id']));die;
		$result = $this->db->query($query, array(
			'EvnStick_pid' => $data['EvnStick_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');

			if (count($response) > 0) {
				$ParentEvnClass_SysNick = $this->getFirstResultFromQuery("
					select top 1 EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
				", array('Evn_id' => $data['EvnStick_pid']));

				$this->load->model($ParentEvnClass_SysNick.'_model', 'parent_model');
				$params = array_merge($data, array('Evn_id' => $data['EvnStick_pid']));
				$parentAccessType = $this->parent_model->getAccessType($params);
				if (!$parentAccessType) {
					return $this->createError('','Ошибка при получении прав доступа к родительсому событию');
				}

				foreach($response as &$item) {
					if ($item['EvnClass_SysNick'] == 'EvnStickStudent' && $item['accessType'] == 'view') {
						$item['accessType'] = $parentAccessType;
					}
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 *	Функция проверки совпадения (Для КВС, в которой уже добавлен исход госпитализации с результатом Смерть требуется, чтобы дата закрытия ЛВН равнялась дате смерти и исход ЛВН был равен Смерть.)
	 */
	function CheckEvnStickDie($data) {
		if (!empty($data['EvnStick_pid'])) {
			// выбираем дату из КВС с причиной закрытия смерть
			$query = "SELECT TOP 1 convert(varchar(10), EvnPS_disDate, 104) as EvnPS_disDate FROM v_EvnPS with (nolock) WHERE EvnPS_id = :EvnStick_pid AND LeaveType_id = 3";

			$result = $this->db->query($query, array(
				'EvnStick_pid' => $data['EvnStick_pid']
			));

			if ( is_object($result) ) {
				$response = $result->result('array');
				if ( is_array($response) && count($response) > 0 ) {

					$date1 = date('d.m.Y',strtotime($data['EvnStick_disDate']));
					$date2 = $response[0]['EvnPS_disDate'];

					if (($data['StickLeaveType_id']==4)&&($date1==$date2)) {
						return true;
					} else {
						return false;
					}
				}
				// если не найдено то при исходе лвн смерть выводим предупреждение
				else {
					if ($data['StickLeaveType_id']==4) { return false; }
				}
			}
		}
		return true;
	}
	
	
	/**
	 *	Получение суммарного периода освобождений для цепочки ЛВН (Первичный -> Продолжение -> .. )
	 */
	function getWorkReleaseSumPeriod($data) {
		if (!empty($data['getEvnPS24'])) { // получаем периоды освобождения в круглосуточном стационаре отдельно от остальных
			$query = "
				with ESBASE (EvnStick_Num, EvnStick_id, EvnStickBase_IsFSS, EvnStick_prid, EvnStickParent_Type) as (
					select 
						ES.EvnStick_Num, 
						ES.EvnStick_id,
						ESB.EvnStickBase_IsFSS,
						case when ES.EvnStick_prid is not null and ES.EvnStick_prid != ES.EvnStick_id 
							then ES.EvnStick_prid else null
						end as EvnStick_prid,
						case 
							when LUT.EvnPS_id is not null then 'EvnPS24' -- ЛВН в КВС с движениями в кругл. стационаре
							else 'other'
						end as EvnStickParent_Type
					from 
						v_EvnStick ES with (nolock)
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
						outer apply (
							select top 1 EPS.EvnPS_id
							from 
								v_EvnPS EPS with (nolock)
								inner join v_EvnSection ESs with (nolock) on ESs.EvnSection_pid = EPS.EvnPS_id
								inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = ESs.LpuSection_id
								inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
								inner join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
							where
								EPS.EvnPS_id = ESB.EvnStickBase_pid
								and LUT.LpuUnitType_SysNick = 'stac'
						) LUT
				),
				tree (EvnStick_Num, EvnStick_id, EvnStickBase_IsFSS, EvnStick_prid, EvnStickParent_Type) as (
					select 
						ESBASE.EvnStick_Num, 
						ESBASE.EvnStick_id,
						ESBASE.EvnStickBase_IsFSS,
						case when ESBASE.EvnStick_prid is not null and ESBASE.EvnStick_prid != ESBASE.EvnStick_id 
							then ESBASE.EvnStick_prid else null
						end as EvnStick_prid,
						ESBASE.EvnStickParent_Type
					from 
						ESBASE
					WHERE ESBASE.EvnStick_id = :EvnStick_id

					union all

					select 
						ESBASE.EvnStick_Num, 
						ESBASE.EvnStick_id,
						ESBASE.EvnStickBase_IsFSS,
						case when ESBASE.EvnStick_prid is not null and ESBASE.EvnStick_prid != ESBASE.EvnStick_id 
							then ESBASE.EvnStick_prid else null
						end as EvnStick_prid,
						ESBASE.EvnStickParent_Type
					from 
						ESBASE
						inner join tree on tree.EvnStick_prid = ESBASE.EvnStick_id
				)

				select
					T.EvnStickParent_Type,
					sum(
						ESWR.EvnStickSumPeriod
						+ ISNULL(DATEDIFF(day, SFDG.StickFSSDataGet_FirstBegDate, SFDG.StickFSSDataGet_FirstEndDate)+1,0)
						+ ISNULL(DATEDIFF(day, SFDG.StickFSSDataGet_SecondBegDate, SFDG.StickFSSDataGet_SecondEndDate)+1,0)
						+ ISNULL(DATEDIFF(day, SFDG.StickFSSDataGet_ThirdBegDate, SFDG.StickFSSDataGet_ThirdEndDate)+1,0)
					) as WorkReleaseSumm

				from tree T
					outer apply (
						select ISNULL(SUM(DATEDIFF(day, EvnStickWorkRelease_begDT, EvnStickWorkRelease_endDT)+1),0) as EvnStickSumPeriod
						from v_EvnStickWorkRelease with (nolock)
						where
							EvnStickBase_id = T.EvnStick_id
					) ESWR
					outer apply (
						select top 1 *
						from v_StickFSSDataGet SFDGs with (nolock)
						where
							SFDGs.EvnStickBase_id = T.EvnStick_id
							and T.EvnStickBase_IsFSS = 2
						order by
							SFDGs.StickFSSDataGet_insDT desc
					) as SFDG
				group by
					T.EvnStickParent_Type
			";
		} else {
			$query = "
				with tree (EvnStick_Num, EvnStick_id, EvnStickBase_IsFSS, EvnStick_prid) as (
					select 
						ES.EvnStick_Num, 
						ES.EvnStick_id,
						ESB.EvnStickBase_IsFSS,
						case when ES.EvnStick_prid is not null and ES.EvnStick_prid != ES.EvnStick_id 
							then ES.EvnStick_prid else null
						end as EvnStick_prid
					from 
						v_EvnStick ES with (nolock)
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
					WHERE ES.EvnStick_id = :EvnStick_id

					union all

					select 
						ES.EvnStick_Num, 
						ES.EvnStick_id,
						ESB.EvnStickBase_IsFSS, 
						case when ES.EvnStick_prid is not null and ES.EvnStick_prid != ES.EvnStick_id 
							then ES.EvnStick_prid else null 
						end as EvnStick_prid
					from 
						v_EvnStick ES with (nolock)
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
						inner join tree on tree.EvnStick_prid = ES.EvnStick_id
				)

				select
					ISNULL(SUM(DATEDIFF(day, ESWR.EvnStickWorkRelease_begDT, ESWR.EvnStickWorkRelease_endDT)+1),0)
					+ ISNULL(SUM(DATEDIFF(day, SFDG.StickFSSDataGet_FirstBegDate, SFDG.StickFSSDataGet_FirstEndDate)+1),0)
					+ ISNULL(SUM(DATEDIFF(day, SFDG.StickFSSDataGet_SecondBegDate, SFDG.StickFSSDataGet_SecondEndDate)+1),0)
					+ ISNULL(SUM(DATEDIFF(day, SFDG.StickFSSDataGet_ThirdBegDate, SFDG.StickFSSDataGet_ThirdEndDate)+1),0)
					as WorkReleaseSumm,
					'' as Error_Msg
				from tree T
					left join v_EvnStickWorkRelease ESWR with (nolock) ON ESWR.EvnStickBase_id = T.EvnStick_id
					outer apply (
						select top 1 *
						from v_StickFSSDataGet SFDGs with (nolock)
						where
							SFDGs.EvnStickBase_id = T.EvnStick_id
							and T.EvnStickBase_IsFSS = 2
						order by						
							SFDGs.StickFSSDataGet_insDT desc
					) as SFDG

			";
		}
		

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		// echo getDebugSQL($query, $queryParams); exit();

		if ( is_object($result) ) {
			$array = $result->result('array');
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Поиск ЛВН
	 */
	function searchEvnStick($data) {
		$where = '(1=1)';
		$queryParams = array();

		if (!empty($data['EvnStick_id'])) {
			$where .= " and ES.EvnStick_id = :EvnStick_id";
			$queryParams['EvnStick_id'] = $data['EvnStick_id'];
		}
		if (!empty($data['Person_id'])) {
			$where .= " and (
				ES.Person_id = :Person_id
				or :Person_id in (select Person_id from v_EvnStickCarePerson with(nolock) where Evn_id = ES.EvnStick_id)
			)";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['Evn_id'])) {
			$where .= " and ES.EvnStick_rid = :Evn_id";
			$queryParams['Evn_id'] = $data['Evn_id'];
		}

		$query = "
			select
				ES.EvnStick_id,
				ES.Org_id,
				RTRIM(ISNULL(ES.EvnStick_Ser, '')) as EvnStick_Ser,
				RTRIM(ISNULL(ES.EvnStick_Num, '')) as EvnStick_Num,
				convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
				convert(varchar(10), ES.EvnStick_disDate, 104) as EvnStick_disDate,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				(
					isnull(ES.EvnStick_Ser, '')+' '+isnull(ES.EvnStick_Num, '')+
					(case when (ES.EvnStick_setDate IS NOT NULL) then ' выдан: '+convert(varchar(10), cast(ES.EvnStick_setDate as datetime), 104) else '' end)+
					(case when (ES.EvnStick_disDate IS NOT NULL) then ' по '+convert(varchar(10), cast(ES.EvnStick_disDate as datetime), 104) else '' end)
				) as EvnStick_all
			from
				v_EvnStick ES with(nolock)
				left join v_StickOrder SO with(nolock) on SO.StickOrder_id = ES.StickOrder_id
			where
				{$where}
		";

		$result = $this->db->query($query, $queryParams);

		// echo getDebugSQL($query, $queryParams); exit();

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	
	/**
	 *	Загрузка списка первичных ЛВН для выбора на форме добалвения/редактирования ЛВН-продолжения
	 */
	function loadEvnStickList($data) {
		if ($data['StickWorkType_id'] == 2) {
			// загружаем список из EvnStickDop
			$query = "
				with PersonEvnStick (
					EvnStick_id,
					EvnStick_IsFSS,
					Org_id,
					EvnStick_Ser,
					EvnStick_Num,
					EvnStick_setDate,
					EvnStick_disDate,
					EvnStickWorkRelease_begDate,
					EvnStickWorkRelease_endDate,
					StickOrder_Name,
					StickWorkType_Name,
					EvnStatus_Name,
					Post_Name,
					EvnStick_OrgNick,
					EvnStick_stacBegDate,
					StickLeaveType_Code,
					StickCause_id,
					StickCause_SysNick,
					StickCauseDid_SysNick,
					StickCause_did,
					Lpu_oid,
					Org_did,
					EvnStickDop_rid
				)
				as (
					select
						ESD.EvnStickDop_id as EvnStick_id,
						ESD.EvnStickDop_IsFSS as EvnStick_IsFSS,
						ESD.Org_id,
						RTRIM(ISNULL(ESD.EvnStickDop_Ser, '')) as EvnStick_Ser,
						RTRIM(ISNULL(ESD.EvnStickDop_Num, '')) as EvnStick_Num,
						convert(varchar(10), ESD.EvnStickDop_setDate, 104) as EvnStick_setDate,
						convert(varchar(10), ESD.EvnStickDop_disDate, 104) as EvnStick_disDate,
						convert(varchar(10), FirstRelease.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
						convert(varchar(10), LastRelease.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
						RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
						SWT.StickWorkType_Name,
						ESTATUS.EvnStatus_Name,
						RTRIM(ISNULL(ES.Post_Name, '')) as Post_Name,
						RTRIM(ISNULL(ES.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
						case when ES.Lpu_id = :Lpu_id then convert(varchar(10), ES.EvnStick_stacBegDate, 104) else null end as EvnStick_stacBegDate,
						SLT.StickLeaveType_Code,
						ES.StickCause_id,
						SC.StickCause_SysNick,
						SCd.StickCause_SysNick as StickCauseDid_SysNick,
						ES.StickCause_did,
						ES.Lpu_oid,
						ES.Org_did,
						ESD.EvnStickDop_rid
					from v_EvnStickDop ESD with (nolock)
						inner join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESD.EvnStickDop_id
						left join StickOrder SO with (nolock) on SO.StickOrder_id = ES.StickOrder_id
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join v_StickCause SCd with (nolock) on SCd.StickCause_id = ES.StickCause_did
						left join v_StickWorkType SWT with (nolock) on SWT.StickWorkType_id = 2
						left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
						left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
						outer apply(
							select top 1
								EvnStickWorkRelease_begDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by EvnStickWorkRelease_begDT
						) FirstRelease
						outer apply(
							select top 1
								EvnStickWorkRelease_endDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by EvnStickWorkRelease_endDT desc
						) LastRelease
					where ES.Person_id = :Person_id
						and ES.EvnStick_id <> ISNULL(:EvnStick_id, 0)
						and (
							SLT.StickLeaveType_Code in ('31', '37')
							OR (
								ES.EvnStick_id in (
									select top 1
										EvnStick_id 
									from
										v_EvnStick ES2 (nolock)
										inner join v_EvnStickDop ESD2 with(nolock) on ES2.EvnStick_id = ESD2.EvnStickDop_pid
										inner join StickCause SC2 with (nolock) on SC2.StickCause_id = ES2.StickCause_id
										inner join StickOrder SO2 with (nolock) on SO2.StickOrder_id = ES2.StickOrder_id
									where
										ES2.Person_id = :Person_id
										and ES2.EvnStick_id <> ISNULL(:EvnStick_id, 0)
										and SC2.StickCause_SysNick = 'pregn' 
										and ESD2.EvnStickDop_disDate IS NOT NULL
										and SO2.StickOrder_id = 1
										and not exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = ESD2.EvnStickDop_id)
									order by
										ES2.EvnStick_disDate desc
								)
							)
						)
					
					union all
					
					-- грузим так же ЛВН по совместительству для которых отсутствует ЛВН по основному месту работы (сохраняются в EvnStick, а не в EvnStickDop)
					select
						ES.EvnStick_id as EvnStick_id,
						ES.EvnStick_IsFSS as EvnStick_IsFSS,
						ES.Org_id,
						RTRIM(ISNULL(ES.EvnStick_Ser, '')) as EvnStick_Ser,
						RTRIM(ISNULL(ES.EvnStick_Num, '')) as EvnStick_Num,
						convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
						convert(varchar(10), ES.EvnStick_disDate, 104) as EvnStick_disDate,
						convert(varchar(10), FirstRelease.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
						convert(varchar(10), LastRelease.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
						RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
						SWT.StickWorkType_Name,
						ESTATUS.EvnStatus_Name,
						RTRIM(ISNULL(ES.Post_Name, '')) as Post_Name,
						RTRIM(ISNULL(ES.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
						case when ES.Lpu_id = :Lpu_id then convert(varchar(10), ES.EvnStick_stacBegDate, 104) else null end as EvnStick_stacBegDate,
						SLT.StickLeaveType_Code,
						ES.StickCause_id,
						SC.StickCause_SysNick,
						SCd.StickCause_SysNick as StickCauseDid_SysNick,
						ES.StickCause_did,
						ES.Lpu_oid,
						ES.Org_did,
						ES.EvnStick_rid
					from v_EvnStick ES with (nolock)
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
						left join StickOrder SO with (nolock) on SO.StickOrder_id = ES.StickOrder_id
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join v_StickCause SCd with (nolock) on SCd.StickCause_id = ES.StickCause_did
						left join v_StickWorkType SWT with (nolock) on SWT.StickWorkType_id = 2
						left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
						left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
						outer apply(
							select top 1
								EvnStickWorkRelease_begDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by EvnStickWorkRelease_begDT
						) FirstRelease
						outer apply(
							select top 1
								EvnStickWorkRelease_endDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by EvnStickWorkRelease_endDT desc
						) LastRelease
					where ES.Person_id = :Person_id
						and ES.EvnStick_id <> ISNULL(:EvnStick_id, 0)
						and ES.StickWorkType_id = 2
						and (
							SLT.StickLeaveType_Code in ('31', '37')
							OR (
								ES.EvnStick_id in (
									select top 1
										EvnStick_id 
									from
										v_EvnStick ES2 (nolock)
										inner join v_EvnStickDop ESD2 with(nolock) on ES2.EvnStick_id = ESD2.EvnStickDop_pid
										inner join StickCause SC2 with (nolock) on SC2.StickCause_id = ES2.StickCause_id
										inner join StickOrder SO2 with (nolock) on SO2.StickOrder_id = ES2.StickOrder_id
									where
										ES2.Person_id = :Person_id
										and ES2.EvnStick_id <> ISNULL(:EvnStick_id, 0)
										and SC2.StickCause_SysNick = 'pregn' 
										and ESD2.EvnStickDop_disDate IS NOT NULL
										and SO2.StickOrder_id = 1
										and not exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = ESD2.EvnStickDop_id)
									order by
										ES2.EvnStick_disDate desc
								)
							)
						)
				)

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL ) then 2 else 1 end as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStickDop_rid
					) EPS
				where 
					exists (select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = PES.EvnStick_id)
					and not exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)

				union all

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL ) then 2 else 1 end as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStickDop_rid
					) EPS
				where 
					PES.Org_did = :Org_id
					and not exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)
					
				union all

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL ) then 2 else 1 end as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStickDop_rid
					) EPS
				where 
					PES.EvnStick_IsFSS = 2
					and not exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)

				union all
				
				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					left join v_EvnStickDop ESDP with (nolock) ON ESDP.EvnStickDop_prid = PES.EvnStick_id
					left join v_EvnStick ESP with (nolock) on ESP.EvnStick_id = ESDP.EvnStickDop_pid
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where 
					exists (select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = PES.EvnStick_id)
					and exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)

				union all
				
				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					left join v_EvnStickDop ESDP with (nolock) ON ESDP.EvnStickDop_prid = PES.EvnStick_id
					left join v_EvnStick ESP with (nolock) on ESP.EvnStick_id = ESDP.EvnStickDop_pid
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where 
					PES.Org_did = :Org_id
					and exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)

				union all
				
				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from PersonEvnStick PES with (nolock)
					left join v_EvnStickDop ESDP with (nolock) ON ESDP.EvnStickDop_prid = PES.EvnStick_id
					left join v_EvnStick ESP with (nolock) on ESP.EvnStick_id = ESDP.EvnStickDop_pid
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where 
					PES.EvnStick_IsFSS = 2
					and exists (select EvnStickDop_id from v_EvnStickDop with (nolock) where EvnStickDop_prid = PES.EvnStick_id)

				order by
					[disabled] asc, PES.EvnStick_id desc
			";
		} else {
			$query = "
				with EvnStickData (
					EvnStick_id,
					EvnStick_IsFSS,
					Org_id,
					EvnStick_Ser,
					EvnStick_Num,
					EvnStick_setDate,
					EvnStick_disDate,
					EvnStickWorkRelease_begDate,
					EvnStickWorkRelease_endDate,
					StickOrder_Name,
					StickWorkType_Name,
					EvnStatus_Name,
					Post_Name,
					EvnStick_OrgNick,
					EvnStick_stacBegDate,
					StickLeaveType_Code,
					StickCause_id,
					StickCause_SysNick,
					StickCauseDid_SysNick,
					StickCause_did,
					Lpu_oid,
					Org_did,
					EvnStick_rid
				) as (
					select
						ES.EvnStick_id,
						ES.EvnStick_IsFSS,
						ES.Org_id,
						RTRIM(ISNULL(ES.EvnStick_Ser, '')) as EvnStick_Ser,
						RTRIM(ISNULL(ES.EvnStick_Num, '')) as EvnStick_Num,
						convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
						convert(varchar(10), ES.EvnStick_disDate, 104) as EvnStick_disDate,
						convert(varchar(10), FirstRelease.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
						convert(varchar(10), LastRelease.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
						RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
						SWT.StickWorkType_Name,
						ESTATUS.EvnStatus_Name,
						RTRIM(ISNULL(ES.Post_Name, '')) as Post_Name,
						RTRIM(ISNULL(ES.EvnStick_OrgNick, '')) as EvnStick_OrgNick,
						case when ES.Lpu_id = :Lpu_id then convert(varchar(10), ES.EvnStick_stacBegDate, 104) else null end as EvnStick_stacBegDate,
						RTRIM(ISNULL(SLT.StickLeaveType_Code, '')) as StickLeaveType_Code,
						ES.StickCause_id,
						SC.StickCause_SysNick,
						SCd.StickCause_SysNick as StickCauseDid_SysNick,
						ES.StickCause_did,
						ES.Lpu_oid,
						ES.Org_did,
						ES.EvnStick_rid
					from v_EvnStick ES with (nolock)
						inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
						left join StickOrder SO with (nolock) on SO.StickOrder_id = ES.StickOrder_id
						left join StickCause SC with (nolock) on SC.StickCause_id = ES.StickCause_id
						left join v_StickCause SCd with (nolock) on SCd.StickCause_id = ES.StickCause_did
						left join v_StickWorkType SWT with (nolock) on SWT.StickWorkType_id = 1
						left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
						left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = ESB.StickLeaveType_rid
						outer apply(
							select top 1
								EvnStickWorkRelease_begDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by evnStickWorkRelease_begDT
						) FirstRelease
						outer apply(
							select top 1
								EvnStickWorkRelease_endDT
							from v_EvnStickWorkRelease with(nolock)
							where EvnStickBase_id = ES.EvnStick_id
							order by evnStickWorkRelease_endDT desc
						) LastRelease
					where ES.Person_id = :Person_id
						and ES.EvnStick_id <> ISNULL(:EvnStick_id, 0)
						and (
							SLT.StickLeaveType_Code in ('31', '37')
							OR (
								ES.EvnStick_id in (
									select top 1
										EvnStick_id 
									from
										v_EvnStick ES2 (nolock)
										inner join StickCause SC2 with (nolock) on SC2.StickCause_id = ES2.StickCause_id
									where
										ES2.Person_id = :Person_id
										and ES2.EvnStick_id <> ISNULL(:EvnStick_id, 0)
										and SC2.StickCause_SysNick = 'pregn' 
										and ES2.EvnStick_disDate IS NOT NULL
										and not exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = ES2.EvnStick_id)
									order by
										ES2.EvnStick_disDate desc
								)
							)
						)
				)

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL) then 2 else 1
					end as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStick_rid
					) EPS
				where
					PES.Org_did = :Org_id
					and not exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)

				union all

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL) then 2 else 1
					end as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStick_rid
					) EPS
				where
					exists (select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = PES.EvnStick_id)
					and not exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)

				union all

				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					'' as ResumedIn,
					'' as ResumedInNum,
					0 as [disabled],
					case when (PES.StickLeaveType_Code IN ('37') AND EPS.EvnPS_id IS NOT NULL) then 2 else 1
					end as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					outer apply(
						select top 1 EvnPS_id from v_EvnPS (nolock) where EvnPS_id = PES.EvnStick_rid
					) EPS
				where
					PES.EvnStick_IsFSS = 2
					and not exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)

				union all

				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,	
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					left join v_EvnStick ESP with (nolock) ON ESP.EvnStick_prid = PES.EvnStick_id
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where
					exists (select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = PES.EvnStick_id)
					and exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)

				union all

				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,	
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					left join v_EvnStick ESP with (nolock) ON ESP.EvnStick_prid = PES.EvnStick_id
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where
					PES.Org_did = :Org_id
					and exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)

				union all

				-- имеющие продолжение в тап/квс
				select
					PES.EvnStick_id,
					PES.Org_id,
					PES.EvnStick_Ser,
					PES.EvnStick_Num,
					PES.EvnStick_setDate,
					PES.EvnStick_disDate,
					PES.EvnStickWorkRelease_begDate,
					PES.EvnStickWorkRelease_endDate,
					PES.StickOrder_Name,
					PES.StickWorkType_Name,
					PES.EvnStatus_Name,
					PES.Post_Name,
					PES.StickLeaveType_Code,
					PES.EvnStick_OrgNick,
					PES.EvnStick_stacBegDate,
					PES.StickCause_id,
					PES.StickCause_SysNick,
					PES.StickCause_did,
					PES.StickCauseDid_SysNick,
					PES.Lpu_oid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as ResumedIn,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as ResumedInNum,	
					1 as [disabled],
					1 as MaxDaysLimitAfterStac
				from EvnStickData PES with (nolock)
					left join v_EvnStick ESP with (nolock) ON ESP.EvnStick_prid = PES.EvnStick_id
					left join v_EvnPL TAP with (nolock) on ESP.EvnStick_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESP.EvnStick_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESP.EvnStick_mid = KVS.EvnPS_id
				where
					PES.EvnStick_IsFSS = 2
					and exists (select EvnStick_id from v_EvnStick with (nolock) where EvnStick_prid = PES.EvnStick_id)
					
				order by
					[disabled] asc, PES.EvnStick_id desc
			";
		}

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id'],
			'Person_id' => $data['Person_id']
		);

		$result = $this->db->query($query, $queryParams);

		// echo getDebugSQL($query, $queryParams); exit();

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($data['EvnStickOriginal_prid'])) {
				// Если для создаваемого/редактируемого ЛВН в поле «Оригинал» указано значение «Дубликат», то (#170984):
				// ЛВН, выбранный в качестве первичного для ЛВН из поля «Оригинал ЛВН», доступен для выбора.
				foreach($resp as $key => $value) {
					if ($value['EvnStick_id'] == $data['EvnStickOriginal_prid']) {
						$resp[$key]['disabled'] = 0;
					}
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных для формы редактирования справки учащегося
	 */
	function loadEvnStickStudentEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$session = $data['session'];
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
        $isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);
		$query = "
			select top 1
				case when (
					case
						when ESS.Lpu_id = :Lpu_id then 1
						" . (count($data['session']['linkedLpuIdList']) > 1 ? "when ESS.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(ESS.EvnStickStudent_IsTransit, 1) = 2 then 1" : "") . "
						when " . (count($med_personal_list) > 0 ? 1 : 0) . " = 1 then 1
						when " . ($isMedStatUser || $isPolkaRegistrator || $isARMLVN ? 1 : 0) . " = 1 then 1
						else 0
					end = 1
				)
				" . (!$isPolkaRegistrator && !$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and (ESS.MedPersonal_id is null or ESS.MedPersonal_id in (".implode(',',$med_personal_list).") )" : "") . " then 'edit' else 'view' end as accessType,
				ISNULL(ESS.EvnStickStudent_id, 0) as EvnStick_id,
				ISNULL(ESS.EvnStickStudent_pid, 0) as EvnStick_pid,
				Parent.EvnClass_SysNick as ParentEvnClass_SysNick,
				ISNULL(ESS.Person_id, 0) as Person_id,
				ISNULL(ESS.PersonEvn_id, 0) as PersonEvn_id,
				ISNULL(ESS.Server_id, -1) as Server_id,
				RTRIM(ISNULL(ESS.EvnStickStudent_Num, '')) as EvnStick_Num,
				convert(varchar(10), ESS.EvnStickStudent_setDate, 104) as EvnStick_setDate,
				ISNULL(ESS.Org_id, 0) as Org_id,
				ESS.StickRecipient_id as StickRecipient_id,
				ESS.StickCause_id as StickCause_id,
				ESS.EvnStickStudent_IsContact as EvnStick_IsContact,
				ESS.EvnStickStudent_ContactDescr as EvnStick_ContactDescr,
				ESS.MedStaffFact_id,
				ESS.MedPersonal_id,
				convert(varchar(10), ESS.EvnStickStudent_begDT, 104) as EvnStickStudent_begDT,
				ESS.EvnStickStudent_Days,
				ESS.Okei_id,
				0 as isParentOwner
			from
				v_EvnStickStudent ESS with (nolock)
				left join v_Evn Parent with(nolock) on  Parent.Evn_id = ESS.EvnStickStudent_pid
			where ESS.EvnStickStudent_id = :EvnStickStudent_id
		";

		$queryParams = array(
			'EvnStickStudent_id' => $data['EvnStickStudent_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$response = $this->queryResult($query, $queryParams);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при получении данных для формы редактирования справки учащегося');
		}

		//Проверяем права для родительского события
		if (isset($response[0]) && !empty($response[0]['ParentEvnClass_SysNick'])) {
			$this->load->model($response[0]['ParentEvnClass_SysNick'].'_model', 'parent_model');
			$params = array_merge($data, array('Evn_id' => $response[0]['EvnStick_pid']));
			$parentAccessType = $this->parent_model->getAccessType($params);
			if (!$parentAccessType) {
				return $this->createError('','Ошибка при получении прав доступа к родительсому событию');
			}

			$response[0]['isParentOwner'] = ($parentAccessType=='edit')?1:0;

			if ($response[0]['accessType'] == 'view') {
				$response[0]['accessType'] = $parentAccessType;
			}
		}

		return $response;
	}


	/**
	 *	Загрузка списка освобождений от работы
	 */
	function loadEvnStickStudentWorkReleaseGrid($data) {
		$accessType = 'view';
		$parent = $this->getFirstRowFromQuery("
			select top 1 Parent.Evn_id, Parent.EvnClass_SysNick
			from v_EvnStickBase ESB with(nolock)
			inner join v_Evn Parent with(nolock) on Parent.Evn_id = ESB.EvnStickBase_pid
			where ESB.EvnStickBase_id = :EvnStickBase_id
		", array('EvnStickBase_id' => $data['EvnStick_id']));

		$this->load->model($parent['EvnClass_SysNick'].'_model', 'parent_model');
		$params = array_merge($data, array('Evn_id' => $parent['Evn_id']));
		$accessType = $this->parent_model->getAccessType($params);
		if (!$accessType) {
			return $this->createError('','Ошибка при получении прав доступа к родительсому событию');
		}

		if ($accessType == 'view') {
			$this->load->helper('MedStaffFactLink');
			$med_personal_list = getMedPersonalListWithLinks();

			$session = $data['session'];
			$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
			$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
			$isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);

			$accessType = "
				case
					when (
						case
							when ESB.Lpu_id = :Lpu_id then 1
							" . (count($data['session']['linkedLpuIdList']) > 1 ? "when ESB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(ESB.EvnStickBase_IsTransit, 1) = 2 then 1" : "") . "
							when ESWR.Org_id = :Org_id then 1
							else 0
						end = 1
						and ESWR.EvnStickWorkRelease_IsDraft = 2
					)
					OR (ISNULL(ESWR.EvnStickWorkRelease_IsDraft, 1) = 1 and O.Org_id = :Org_id " . (!$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and ESWR.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . ")
				then 'edit' else 'view' end
			";
		} else {
			$accessType = "'{$accessType}'";
		}

		$query = "
			select
				$accessType as accessType,
				ESWR.EvnStickWorkRelease_id,
				ESWR.EvnStickBase_id,
				ESWR.LpuSection_id,
				LUT.LpuUnitType_SysNick,
				ESWR.MedPersonal_id,
				ESWR.MedPersonal2_id,
				ESWR.MedPersonal3_id,
				ESWR.MedStaffFact_id,
				ESWR.MedStaffFact2_id,
				ESWR.MedStaffFact3_id,
				ESWR.Post_id,
				ESWR.Org_id,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				RTRIM(ISNULL(O.Org_Nick, '')) as Org_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				MP.Person_id as Person_id,
				1 as RecordStatus_Code,
				CASE ISNULL(EvnStickWorkRelease_IsPredVK, 1) 
					WHEN 1 THEN 0
					WHEN 2 THEN 1 
				END
				as EvnStickWorkRelease_IsPredVK,
				CASE ISNULL(EvnStickWorkRelease_IsDraft, 1)
					WHEN 1 THEN 0
					WHEN 2 THEN 1
				END
				as EvnStickWorkRelease_IsDraft,
				EVK.EvnVK_id,
				EVK.EvnVK_descr
			from v_EvnStickWorkRelease ESWR with (nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				outer apply (
					select top 1
						Person_Fio,
						Person_id
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ESWR.MedPersonal_id
				) MP
				outer apply (
					select top 1
						VK.EvnVK_id, 
						'№' + cast(VK.EvnVK_NumProtocol as varchar(50)) + ' (' + isnull(EVT.ExpertiseEventType_Name,'') + ')' as EvnVK_descr
					from v_EvnVK VK with (nolock)
						left join v_ExpertiseEventType EVT with (nolock) ON VK.ExpertiseEventType_id = EVT.ExpertiseEventType_id
					where EvnStickWorkRelease_id = ESWR.EvnStickWorkRelease_id
				) EVK
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = ESWR.LpuSection_id
				left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join Lpu L with (nolock) on L.Lpu_id = LB.Lpu_id
				left join Org O with (nolock) on O.Org_id = ISNULL(ESWR.Org_id, L.Org_id)
			where ESWR.EvnStickBase_id = :EvnStickBase_id
		";

		$result = $this->db->query($query, array(
			'EvnStickBase_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => empty($data['Org_id']) ? $data['session']['org_id'] : $data['Org_id']
		));
		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 *	Загрузка списка освобождений от работы
	 */
	function loadEvnStickWorkReleaseGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$session = $data['session'];
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
        $isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);
        $isDopLvn = !empty($data['StickWorkType_id']) && $data['StickWorkType_id'] == 2;

        $accessCond = '';
        if ($isDopLvn && !$isMedStatUser && !$isARMLVN) { // для ЛВН по совместительству период должен быть добавлен в текущей МО
        	$accessCond .= " and ESWR.Org_id = :Org_id";
        }

		$query = "
			select
				case
					when (
						(
							case
								when E.Lpu_id = :Lpu_id then 1
								" . (count($data['session']['linkedLpuIdList']) > 1 ? "when E.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(E.Evn_IsTransit, 1) = 2 then 1" : "") . "
								when ESWR.Org_id = :Org_id then 1
								else 0
							end = 1
							and ESWR.EvnStickWorkRelease_IsDraft = 2
						)
						OR (ISNULL(ESWR.EvnStickWorkRelease_IsDraft, 1) = 1 and O.Org_id = :Org_id " . (!$isDopLvn && !$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and ESWR.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "{$accessCond})
					)
				then 'edit' else 'view' end as accessType,
				case 
					when (
						ISNULL(ESWR.EvnStickWorkRelease_IsInReg, 1) = 1
						OR (
							ESWR.EvnStickWorkRelease_IsInReg = 2
							and exists(
								select top 1
									resr.Evn_id
								from
									v_RegistryESError resr (nolock)
									left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
								where
									resr.Evn_id = ESWR.EvnStickBase_id
									and ISNULL(ret.RegistryESErrorType_Code, 0) = '005' -- разрешить при наличии ошибок ФЛК по подписи
							)
						)
					)
					and ISNULL(ESWR.EvnStickWorkRelease_IsPaid, 1) = 1
					or :ignoreRegAndPaid = 1
				then 'edit' else 'view' end as signAccess,
				ESWR.EvnStickWorkRelease_id,
				ESWR.EvnStickBase_id,
				ESWR.LpuSection_id,
				LUT.LpuUnitType_SysNick,
				ESWR.MedStaffFact_id,
				ESWR.MedStaffFact2_id,
				ESWR.MedStaffFact3_id,
				ESWR.MedPersonal_id,
				ESWR.MedPersonal2_id,
				ESWR.MedPersonal3_id,
				ESWR.Post_id,
				ESWR.Org_id,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				RTRIM(ISNULL(O.Org_Nick, '')) as Org_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				MP.Person_id as Person_id,
				1 as RecordStatus_Code,
				CASE ISNULL(EvnStickWorkRelease_IsPredVK, 1)
					WHEN 1 THEN 0
					WHEN 2 THEN 1
				END
				as EvnStickWorkRelease_IsPredVK,
				CASE ISNULL(EvnStickWorkRelease_IsDraft, 1)
					WHEN 1 THEN 0
					WHEN 2 THEN 1
				END
				as EvnStickWorkRelease_IsDraft,
				CASE ISNULL(EvnStickWorkRelease_IsSpecLpu, 1)
					WHEN 1 THEN 0
					WHEN 2 THEN 1
				END
				as EvnStickWorkRelease_IsSpecLpu,
				EVK.EvnVK_id,
				EvnVK_NumProtocol,
				EVK.EvnVK_descr,
				convert(varchar(20), ESWR.EvnStickWorkRelease_updDT, 120) as EvnStickWorkRelease_updDT,
				ESWR.Signatures_mid,
				isnull(SMP.SignaturesStatus_id, 2) as SMPStatus_id,
				SMPS.SignaturesStatus_Name as SMP_Status_Name,
				convert(varchar(10), SMP.Signatures_updDT, 104) + ' ' + convert(varchar(5), SMP.Signatures_updDT, 108) as SMP_updDT,
				SMPU.PMUser_Name as SMP_updUser_Name,
				ESWR.Signatures_wid,
				isnull(SVK.SignaturesStatus_id, 2) as SVKStatus_id,
				SVKS.SignaturesStatus_Name as SVK_Status_Name,
				convert(varchar(10), SVK.Signatures_updDT, 104) + ' ' + convert(varchar(5), SVK.Signatures_updDT, 108) as SVK_updDT,
				SVKU.PMUser_Name as SVK_updUser_Name,
				ESWR.EvnStickWorkRelease_IsInReg,
				ESWR.EvnStickWorkRelease_IsPaid,
				msf.Lpu_id
			from v_EvnStickWorkRelease ESWR with (nolock)
				inner join EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				inner join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = eswr.MedStaffFact_id
				outer apply (
					select top 1
						Person_Fio,
						Person_id
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ESWR.MedPersonal_id
				) MP
				outer apply (
					select top 1
						VK.EvnVK_id,
						VK.EvnVK_NumProtocol,
						'№' + cast(VK.EvnVK_NumProtocol as varchar(50)) + ' (' + isnull(EVT.ExpertiseEventType_Name,'') + ')' as EvnVK_descr
					from v_EvnVK VK with (nolock)
						left join v_ExpertiseEventType EVT with (nolock) ON VK.ExpertiseEventType_id = EVT.ExpertiseEventType_id
					where EvnStickWorkRelease_id = ESWR.EvnStickWorkRelease_id
				) EVK
				left join LpuSection LS with (nolock) on LS.LpuSection_id = ESWR.LpuSection_id
				left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join Lpu L with (nolock) on L.Lpu_id = LB.Lpu_id
				left join Org O with (nolock) on O.Org_id = ISNULL(ESWR.Org_id, L.Org_id)
				left join v_Signatures SMP with (nolock) on SMP.Signatures_id = ESWR.Signatures_mid
				left join v_pmUserCache SMPU with (nolock) on SMPU.PMUser_id = SMP.pmUser_updID
				left join v_SignaturesStatus SMPS with (nolock) on SMPS.SignaturesStatus_id = isnull(SMP.SignaturesStatus_id, 2)
				left join v_Signatures SVK with (nolock) on SVK.Signatures_id = ESWR.Signatures_wid
				left join v_pmUserCache SVKU with (nolock) on SVKU.PMUser_id = SVK.pmUser_updID
				left join v_SignaturesStatus SVKS with (nolock) on SVKS.SignaturesStatus_id = isnull(SVK.SignaturesStatus_id, 2)
			where 
				ESWR.EvnStickBase_id = :EvnStickBase_id
			order by
				ESWR.EvnStickWorkRelease_begDT
		";

		$result = $this->db->query($query, array(
			'EvnStickBase_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => empty($data['Org_id']) ? $data['session']['org_id'] : $data['Org_id'],
			'ignoreRegAndPaid' => empty($data['ignoreRegAndPaid']) ? '0' : '1'
		));
		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');

		if (count($response) == 0 && !empty($data['EvnStickDop_pid'])) {
			$result = $this->db->query($query, array(
				'EvnStickBase_id' => $data['EvnStickDop_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Org_id' => empty($data['Org_id']) ? $data['session']['org_id'] : $data['Org_id'],
				'ignoreRegAndPaid' => empty($data['ignoreRegAndPaid']) ? '0' : '1'
			));
			if ( !is_object($result) ) {
				return false;
			}
			$response = $result->result('array');
		}

		// Выполнять при несовпадении реестровой и текущей БД
		if ( file_exists(APPPATH.'config/'.$this->regionNick.'/database'.EXT) ) {
			require(APPPATH.'config/'.$this->regionNick.'/database'.EXT);
		}
		else {
			require(APPPATH.'config/database'.EXT);
		}

		if ( array_key_exists('registry_es', $db) && $db['registry_es'] != $db['default'] ) {
			$eswrIdList = array();

			foreach ( $response as $row ) {
				$eswrIdList[] = $row['EvnStickWorkRelease_id'];
			}

			if ( count($eswrIdList) > 0 ) {
				$dbRegES = $this->load->database('registry_es', true);

				// Получаем значения signAccess
				$query = "
					select
						case 
							when (
								ISNULL(ESWR.EvnStickWorkRelease_IsInReg, 1) = 1
								OR (
									ESWR.EvnStickWorkRelease_IsInReg = 2
									and exists(
										select top 1
											resr.Evn_id
										from
											v_RegistryESError resr (nolock)
											left join v_RegistryESErrorType ret (nolock) on ret.RegistryESErrorType_id = resr.RegistryESErrorType_id
										where
											resr.Evn_id = ESWR.EvnStickBase_id
											and ISNULL(ret.RegistryESErrorType_Code, 0) = '005' -- разрешить при наличии ошибок ФЛК по подписи
									)
								)
							)
							and ISNULL(ESWR.EvnStickWorkRelease_IsPaid, 1) = 1
						then 'edit' else 'view' end as signAccess,
						ESWR.EvnStickWorkRelease_id,
						ESWR.EvnStickWorkRelease_IsInReg,
						ESWR.EvnStickWorkRelease_IsPaid
					from v_EvnStickWorkRelease ESWR with (nolock)
					where ESWR.EvnStickWorkRelease_id in (" . implode(',', $eswrIdList) . ")
				";

				$result = $dbRegES->query($query);
				if ( !is_object($result) ) {
					return false;
				}
				$responseESWR = $result->result('array');

				foreach ( $responseESWR as $row ) {
					foreach ( $response as $key => $respRow ) {
						if ( $respRow['EvnStickWorkRelease_id'] == $row['EvnStickWorkRelease_id'] ) {
							$response[$key]['signAccess'] = $row['signAccess'];
							$response[$key]['EvnStickWorkRelease_IsInReg'] = $row['EvnStickWorkRelease_IsInReg'];
							$response[$key]['EvnStickWorkRelease_IsPaid'] = $row['EvnStickWorkRelease_IsPaid'];
						}
					}
				}
			}
		}

		foreach ($response as $key => $respRow) {
			if (
				empty($data['ignoreRegAndPaid']) 
				&&($respRow['EvnStickWorkRelease_IsInReg'] == 2 || $respRow['EvnStickWorkRelease_IsPaid'] == 2) 
			) {
				$response[$key]['accessType'] = 'view';
			}
		}

		// если ЛВН из ФСС, то тянем на форму освобождения из данных ФСС
		$resp_esb = $this->queryResult("
			select top 1
				esb.EvnStickBase_IsFSS,
				sfdg.Lpu_StickNick,
				sfdg.FirstEvnStickWorkRelease_id,
				convert(varchar(10), sfdg.StickFSSDataGet_FirstBegDate, 104) as StickFSSDataGet_FirstBegDate,
				convert(varchar(10), sfdg.StickFSSDataGet_FirstEndDate, 104) as StickFSSDataGet_FirstEndDate,
				sfdg.MedPersonal_FirstFIO,
				sfdg.SecondEvnStickWorkRelease_id,
				convert(varchar(10), sfdg.StickFSSDataGet_SecondBegDate, 104) as StickFSSDataGet_SecondBegDate,
				convert(varchar(10), sfdg.StickFSSDataGet_SecondEndDate, 104) as StickFSSDataGet_SecondEndDate,
				sfdg.MedPersonal_SecondFIO,
				sfdg.ThirdEvnStickWorkRelease_id,
				convert(varchar(10), sfdg.StickFSSDataGet_ThirdBegDate, 104) as StickFSSDataGet_ThirdBegDate,
				convert(varchar(10), sfdg.StickFSSDataGet_ThirdEndDate, 104) as StickFSSDataGet_ThirdEndDate,
				sfdg.MedPersonal_ThirdFIO,
				sft.StickFSSType_Name
			from
				EvnStickBase esb (nolock)
				inner join v_StickFSSDataGet sfdg (nolock) on sfdg.StickFSSData_id = esb.StickFSSData_id or sfdg.EvnStickBase_id = esb.EvnStickBase_id
				left join v_StickFSSType sft (nolock) on sft.StickFSSType_Code = sfdg.StickFSSDataGet_StickStatus
			where
				esb.EvnStickBase_id = :EvnStickBase_id
			order by
				sfdg.StickFSSDataGet_updDT desc
		", array(
			'EvnStickBase_id' => $data['EvnStick_id']
		));

		if (!empty($resp_esb[0]['EvnStickBase_IsFSS']) && $resp_esb[0]['EvnStickBase_IsFSS'] == 2) {
			// ЛВН из ФСС
			if (empty($resp_esb[0]['ThirdEvnStickWorkRelease_id']) && !empty($resp_esb[0]['StickFSSDataGet_ThirdBegDate'])) {
				array_unshift($response, array(
					'EvnStickWorkRelease_begDate' => $resp_esb[0]['StickFSSDataGet_ThirdBegDate'],
					'EvnStickWorkRelease_endDate' => $resp_esb[0]['StickFSSDataGet_ThirdEndDate'],
					'Org_Name' => $resp_esb[0]['Lpu_StickNick'],
					'MedPersonal_Fio' => $resp_esb[0]['MedPersonal_ThirdFIO'],
					'StickFSSType_Name' => $resp_esb[0]['StickFSSType_Name']
				));
			}
			if (empty($resp_esb[0]['SecondEvnStickWorkRelease_id']) && !empty($resp_esb[0]['StickFSSDataGet_SecondBegDate'])) {
				array_unshift($response, array(
					'EvnStickWorkRelease_begDate' => $resp_esb[0]['StickFSSDataGet_SecondBegDate'],
					'EvnStickWorkRelease_endDate' => $resp_esb[0]['StickFSSDataGet_SecondEndDate'],
					'Org_Name' => $resp_esb[0]['Lpu_StickNick'],
					'MedPersonal_Fio' => $resp_esb[0]['MedPersonal_SecondFIO'],
					'StickFSSType_Name' => $resp_esb[0]['StickFSSType_Name']
				));
			}
			if (empty($resp_esb[0]['FirstEvnStickWorkRelease_id']) && !empty($resp_esb[0]['StickFSSDataGet_FirstBegDate'])) {
				array_unshift($response, array(
					'EvnStickWorkRelease_begDate' => $resp_esb[0]['StickFSSDataGet_FirstBegDate'],
					'EvnStickWorkRelease_endDate' => $resp_esb[0]['StickFSSDataGet_FirstEndDate'],
					'Org_Name' => $resp_esb[0]['Lpu_StickNick'],
					'MedPersonal_Fio' => $resp_esb[0]['MedPersonal_FirstFIO'],
					'StickFSSType_Name' => $resp_esb[0]['StickFSSType_Name']
				));
			}
		}

		return $response;
	}

	/**
	 * Определение Lpu_lid - МО из последнего периода освобождения
	 */
	function defineLpuFields($data) {
		$lpuFields = array(
			'Lpu_lid' => null,
			'Lpu_fid' => null,
			'Lpu_sid' => null,
			'Lpu_tid' => null,
			'Lpu_outid' => null
		);

		if (!empty($data['EvnStickBase_id'])) {
			// если сохранен ЛВН, то из последнего освобождения
			$resp_ms = $this->queryResult("
				select
					msf.Lpu_id
				from
					v_EvnStickWorkRelease eswr (nolock)
					inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = eswr.MedStaffFact_id
				where
					eswr.EvnStickBase_id = :EvnStickBase_id
				order by eswr.EvnStickWorkRelease_begDT asc
			", array(
				'EvnStickBase_id' => $data['EvnStickBase_id']
			));

			if (!empty($resp_ms[0])) { // первое
				$lpuFields['Lpu_fid'] = $resp_ms[0]['Lpu_id'];
				$lpuFields['Lpu_lid'] = $resp_ms[0]['Lpu_id'];
			}

			if (!empty($resp_ms[1])) { // второе
				$lpuFields['Lpu_sid'] = $resp_ms[1]['Lpu_id'];
				$lpuFields['Lpu_lid'] = $resp_ms[1]['Lpu_id'];
			}

			if (!empty($resp_ms[2])) { // третье
				$lpuFields['Lpu_tid'] = $resp_ms[2]['Lpu_id'];
				$lpuFields['Lpu_lid'] = $resp_ms[2]['Lpu_id'];
			}
		}

		if (!empty($data['MedStaffFact_id'])) {
			// если есть врач исхода, берём из врача исхода
			$resp_ms = $this->queryResult("select top 1 Lpu_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", array(
				'MedStaffFact_id' => $data['MedStaffFact_id']
			));

			if (!empty($resp_ms[0]['Lpu_id'])) {
				if (empty($lpuFields['Lpu_lid'])) {
					$lpuFields['Lpu_lid'] = $resp_ms[0]['Lpu_id'];
				}

				$lpuFields['Lpu_outid'] = $resp_ms[0]['Lpu_id']; // МО исхода
			}
		}

		if (empty($lpuFields['Lpu_lid'])) {
			// иначе просто Lpu_id
			$lpuFields['Lpu_lid'] = $data['Lpu_id'];
		}

		return $lpuFields;
	}

	/**
	 * Обновление Lpu_lid - МО из последнего периода освобождения / Lpu_fid / Lpu_sid / Lpu_tid / Lpu_outid.
	 */
	function updateLpuFields($data) {
		$resp_es = $this->queryResult("
			select
				esb.EvnStickBase_id,
				ISNULL(esb2.MedStaffFact_id, esb.MedStaffFact_id) as MedStaffFact_id,
				esb.Lpu_id
			from
				v_EvnStickBase esb (nolock)
				left join v_EvnStickBase esb2 (nolock) on esb2.EvnStickBase_id = esb.EvnStickBase_pid
			where
				esb.EvnStickBase_id = :EvnStickBase_id
		", array(
			'EvnStickBase_id' => $data['EvnStickBase_id']
		));


		if (!empty($resp_es[0]['EvnStickBase_id'])) {
			$lpuFields = $this->defineLpuFields(array(
				'MedStaffFact_id' => $resp_es[0]['MedStaffFact_id'],
				'EvnStickBase_id' => $resp_es[0]['EvnStickBase_id'],
				'Lpu_id' => $resp_es[0]['Lpu_id']
			));

			$this->db->query("update EvnStickBase with (rowlock) set Lpu_lid = :Lpu_lid, Lpu_fid = :Lpu_fid, Lpu_sid = :Lpu_sid, Lpu_tid = :Lpu_tid, Lpu_outid = :Lpu_outid where EvnStickBase_id = :EvnStickBase_id", array(
				'EvnStickBase_id' => $resp_es[0]['EvnStickBase_id'],
				'Lpu_lid' => $lpuFields['Lpu_lid'],
				'Lpu_fid' => $lpuFields['Lpu_fid'],
				'Lpu_sid' => $lpuFields['Lpu_sid'],
				'Lpu_tid' => $lpuFields['Lpu_tid'],
				'Lpu_outid' => $lpuFields['Lpu_outid']
			));
		}
	}

	/**
	 * Определение диагноза для ЛВН
	 * Входящие параметры: EvnStick_id, EvnStick_mid, EvnStick_IsFSS, RegistryESStorage_id
	 */
	function defineDiagPid($data) {
		$Diag_pid = null;
		$isInRegistry = false;
		if (!empty($data['EvnStick_id'])) {
			// проверяем признаки "В реестре" на освоождения от работы и достаём текущий диагноз ЛВН
			$resp_es = $this->queryResult("
				select
					es.Diag_pid,
					eswr.EvnStickWorkRelease_id
				from
					v_EvnStick es with (nolock)
					outer apply (
						select top 1
							eswr.EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease eswr with (nolock)
						where
							eswr.EvnStickBase_id = es.EvnStick_id
							and eswr.EvnStickWorkRelease_IsInReg = 2
					) eswr
				where
					es.EvnStick_id = :EvnStick_id
			", array(
				'EvnStick_id' => $data['EvnStick_id']
			));

			if (!empty($resp_es[0]['Diag_pid'])) {
				$Diag_pid = $resp_es[0]['Diag_pid'];
			}
			if (!empty($resp_es[0]['EvnStickWorkRelease_id'])) {
				$isInRegistry = true;
			}
		}

		if (
			!empty($data['RegistryESStorage_id']) //электронный ЛН (наличие связи с хранилищем номеров ЛН),
			&& !$isInRegistry // у ЛН нет ни одного периода нетрудоспособности с признаком «В реестре»,
			&& $data['EvnStick_IsFSS'] == 1 // у ЛН нет признака «ЛВН из ФСС»
		) {
			// необходимо проверять настройку региона  «Передавать информацию о диагнозе в ФСС» (см. ТЗ форма Параметры системы.docx).
			// Если флаг поднят, то сохраняется диагноз из последнего посещения/движения в связанном случае лечения
			$resp_ds = $this->queryResult("
				select top 1
					ds.DataStorage_id
				from
					DataStorage ds (nolock)
				where
					ds.DataStorage_Name = 'enable_fss_send_diag'
					and ds.DataStorage_Value = '1'
					and ds.Lpu_id is null
			");
			if (!empty($resp_ds[0]['DataStorage_id'])) {
				// Достаём диагно из последнего посещения/движения
				$resp_evpl = $this->queryResult("
					select top 1
						Diag_id
					from
						v_EvnVizitPL (nolock)
					where
						EvnVizitPL_pid = :pid
					order by
						EvnVizitPL_setDT desc
				", array(
					'pid' => $data['EvnStick_mid']
				));

				if (!empty($resp_evpl[0])) {
					$Diag_pid = $resp_evpl[0]['Diag_id'];
				} else {
					$resp_es = $this->queryResult("
						select top 1
							Diag_id
						from
							v_EvnSection (nolock)
						where
							EvnSection_pid = :pid
						order by
							EvnSection_setDT desc
					", array(
						'pid' => $data['EvnStick_mid']
					));
					if (!empty($resp_es[0])) {
						$Diag_pid = $resp_es[0]['Diag_id'];
					}
				}
			}
		}

		return $Diag_pid;
	}

	/**
	 * Удаление ненужных связей ЛВН с учётным документом
	 */
	function deleteEvnStickLinks($data, $EvnStick_mid) {
		if ( empty($data['EvnStick_id']) || empty($data['session']['lpu_id']) ) { return false; }
		$query = "	
			delete from 
				EvnLink with (rowlock)
			where
				Evn_id = :EvnStick_mid
				and Evn_lid = :EvnStick_id
				and not exists (
					select top 1 ESWR.EvnStickWorkRelease_id
					from
						v_EvnStickWorkRelease ESWR with (nolock) 
						left join v_Lpu L with (nolock) on L.Org_id = ESWR.Org_id 
					where
						L.Lpu_id = :Lpu_id
						and ESWR.EvnStickBase_id = :EvnStick_id
				)
				and not exists (
					select top 1 ESB.EvnStickBase_id
					from
						v_EvnStickBase ESB with (nolock)
					where
						ESB.EvnStickBase_id = :EvnStick_id
						and ESB.Lpu_outid = :Lpu_id
				)
		";
		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['session']['lpu_id'],
			'EvnStick_mid' => $EvnStick_mid
		);
		$this->db->query($query, $queryParams);

	}

	/**
	 *	Сохранение ЛВН
	 */
	function saveEvnStick($data) {
		$session = $data['session'];
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;

		//#90048 проверка при сохранении нового первичного ЛВН. Проверяется наличие ранее сохраненного первичного ЛВН
		if ($data['StickOrder_id'] == 1 && empty($data['EvnStick_id']) && empty($data['ignoreStickOrderCheck'])) {
			$query = "
				select top 1
					count(ES.EvnStick_id) as cnt
				from
					v_EvnStick ES with(nolock)
				where
					ES.EvnStick_mid = :EvnStick_mid
					and ES.StickOrder_id = 1
					and ES.StickWorkType_id in (1,3)
					and not exists(
						select ESB1.EvnStickBase_id
						from v_EvnStickBase ESB1 with(nolock)
						inner join v_StickLeaveType SLT1 with(nolock) on SLT1.StickLeaveType_id = ESB1.StickLeaveType_rid
						where ESB1.EvnStickBase_mid = :EvnStick_mid and SLT1.StickLeaveType_Code = '32'
					)
			";

			$queryParams = array(
				'EvnStick_mid' => $data['EvnStick_mid']
			);

			$cnt = $this->getFirstResultFromQuery($query, $queryParams);
			if ($cnt === false) {
				return $this->createError('','Ошибка при проверке наличия первичного ЛВН');
			}
			if ($cnt > 0) {
				return array(array(
					'Error_Msg' => 'YesNo',
					'Error_Code' => 101,
					'Alert_Msg' => "Внимание! В рамках текущего документа уже заведен первичный ЛВН. Сохранить изменения?"
				));
			}
		}

		$EvnStick_mid = $data['EvnStick_mid'];

		// Вытаскиваем Lpu_id и EvnStick_pid + поля из шапки, если есть связка текущего учетного документа с ЛВН из другого документа
		if( ! empty($data['EvnStick_id'])){
			$query = "
				select top 1
					ES.EvnStick_Num,
					ES.EvnStick_mid,
					ES.EvnStick_pid,
					ES.EvnStick_prid,
					ES.EvnStick_Ser,
					convert(varchar(10), ES.EvnStick_setDT, 120) as EvnStick_setDate,
					ES.Lpu_id,
					ES.Person_id,
					ES.PersonEvn_id,
					ES.Server_id,
					ES.StickOrder_id,
					ES.StickWorkType_id,
					ESB.EvnStickBase_IsPaid
				from 
					v_EvnStick ES with (nolock)
					inner join EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				where
					EvnStick_id = ISNULL(:EvnStick_id, 0)
			";
		
			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id']
			);
		
			$result = $this->db->query($query, $queryParams);

			$response = $result->result('array');
		
			if ( !is_array($response) || count($response) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при получении данных по ЛВН'));
			}

			if ( $response[0]['EvnStick_mid'] != $data['EvnStick_mid'] ) {
				foreach ( $response[0] as $key => $value ) {
					// https://jira.is-mis.ru/browse/PROMEDWEB-9927
					if (empty($data[$key])) {
						$data[$key] = $value;
					}
				}
			}

			$data['Lpu_id'] = $response[0]['Lpu_id'];
		}


		if($data['StickWorkType_id'] == 1 && isset($data['Org_id']) && ! empty($data['Org_id'])){

			$this->load->model('Person_model');

			$dataCurrentJobInfo = $this->Person_model->getCurrentPersonJob($data['Person_id']);

			if(empty($data['ignoreCheckChangeJobInfo']) && $data['Org_id'] != $dataCurrentJobInfo['Org_id']){

				if(empty($data['doUpdateJobInfo'])){

					return array(array(
						'Error_Msg' => 'YesNo',
						'Error_Code' => 102,
						'Alert_Msg' => "Вы указали новое место работы пациента. Обновить данные формы «Человек»?"
					));

				} else {


					// Если в форме указана должность, то находим по названию идентификатор должности
					// ВАЖНО: Должность не обязательна для заполнения на форме
					$Post_id = null;
					if(isset($data['Post_Name']) && ! empty($data['Post_Name'])){
						$Post_id = $this->Person_model->getPostIdFromPostName($data['Post_Name'], array(
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}


					// Обновляем данные о месте работы,
					// Метод "update_PersonJob" проверяет значения и обновляет только то, что указано
					$this->Person_model->update_PersonJob(array(
						'Person_id' => $data['Person_id'],
						'Post_id' => $Post_id,
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Org_id' => $data['Org_id']
					));
				}

			}
		}

		// проверяем что в ЛВН по основному месту работы совпадают поля с ЛВН по совместительству(если он в реестре или принят)
		if ($data['StickWorkType_id'] == 1) {
			$query = "
				select top 1 
					ESD.EvnStickDop_id, 
					ESB.StickOrder_id, 
					ESB.StickCause_id
				from
					v_EvnStickDop ESD with (nolock)
					inner join v_EvnStickBase ESB with (nolock) on  ESB.EvnSTickBase_id = ESD.EvnStickDop_id
					outer apply(
						select top 1
							EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease with (nolock)
						where
							EvnStickBase_id = ESD.EvnStickDop_id
							and (
								isnull(EvnStickWorkRelease_IsInReg, 1) = 2
								or isnull(EvnStickWorkRelease_IsPaid, 1) = 2
							)
					) as ESWR
				where
					ESD.EvnStickDop_pid = :EvnStick_id
					and (
						ESWR.EvnStickWorkRelease_id is not null
						or isnull(ESB.EvnStickBase_IsInReg, 1) = 2
						or isnull(ESB.EvnStickBase_IsPaid, 1) = 2
					)
					and (
						ESD.StickOrder_id != :StickOrder_id
						or ESD.StickCause_id != :StickCause_id
					)
			";
			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id'],
				'StickOrder_id' => $data['StickOrder_id'],
				'StickCause_id' => $data['StickCause_id']
			);

			$resp_checkDopFields = $this->getFirstRowFromQuery($query, $queryParams);

			if (!empty($resp_checkDopFields)) {
				if (empty($data['ignoreCheckFieldStickOrder']) && $resp_checkDopFields['StickOrder_id'] != $data['StickOrder_id']) {
					return array(array(
						'Error_Msg' => 'YesNoCancel',
						'Alert_Msg' => 'Порядок выдачи отличаются от данных ЛВН по совместительству, находящегося в реестре или принятого ФСС. Указать данные из ЛВН по совместительству?',
						'StickOrder_id' => $resp_checkDopFields['StickOrder_id'],
						'Error_Code' => 205
					));
				}

				if ( empty($data['ignoreCheckFieldStickCause']) && $resp_checkDopFields['StickCause_id'] != $data['StickCause_id']) {
					return array(array(
						'Error_Msg' => 'YesNoCancel',
						'Alert_Msg' => 'Причина нетрудоспособности отличаются от данных ЛВН по совместительству, находящегося в реестре или принятого ФСС. Указать данные из ЛВН по совместительству?',
						'StickCause_id' => $resp_checkDopFields['StickCause_id'],
						'Error_Code' => 206
					));
				}
			}
		}

		

		// если не заполнен исход, то не долнжны быть сохранены и все поля по исходу
		if (empty($data['StickLeaveType_id'])) {
			$data['EvnStick_disDate'] = null;
			$data['MedStaffFact_id'] = null;
			$data['MedPersonal_id'] = null;
			$data['Lpu_oid'] = null;
		}

		if (getRegionNick() != 'kz') {
			if (!empty($data['RegistryESStorage_id'])) {
				// берем номер из хранилища
				$resp_es = $this->queryResult("
					select
						EvnStickBase_Num,
						EvnStickBase_id
					from
						v_RegistryESStorage (nolock)
					where
						RegistryESStorage_id = :RegistryESStorage_id
				", array(
					'RegistryESStorage_id' => $data['RegistryESStorage_id']
				));

				if (!empty($resp_es[0]['EvnStickBase_id']) && (empty($data['EvnStick_id']) || $data['EvnStick_id'] != $resp_es[0]['EvnStickBase_id'])) {
					return array(array('Error_Msg' => 'Данный номер ЭЛН уже использован. Необходимо получить новый номер.', 'Error_Code' => '401'));
				}

				if (!empty($resp_es[0])) {
					$data['EvnStick_Num'] = $resp_es[0]['EvnStickBase_Num'];
				}
			}
		}

		if (empty($data['ignoreStickLeaveTypeCheck']) && !empty($data['EvnStick_id'])) {
			$resp_esb = $this->queryResult("
				SELECT
					esb.MedStaffFact_id,
					esb.MedPersonal_id,
					esb.StickLeaveType_rid as StickLeaveType_id,
					convert(varchar(10), esb.EvnStickBase_disDate, 120) as EvnStick_disDate
				FROM
					v_EvnStickBase esb (nolock)
					inner join v_EvnStickDop esd (nolock) on esd.EvnStickDop_id = esb.EvnStickBase_id
				WHERE
					esd.EvnStickDop_pid = :EvnStickBase_id
					and esb.EvnStickBase_IsInReg = 2
			", array(
				'EvnStickBase_id' => $data['EvnStick_id']
			));

			if (!empty($resp_esb[0]['MedStaffFact_id'])) {
				if (
					$data['MedStaffFact_id'] != $resp_esb[0]['MedStaffFact_id']
					|| $data['StickLeaveType_id'] != $resp_esb[0]['StickLeaveType_id']
					|| $data['EvnStick_disDate'] != $resp_esb[0]['EvnStick_disDate']
				) {
					$resp_esb[0]['EvnStick_disDate'] = date('d.m.Y', strtotime($resp_esb[0]['EvnStick_disDate']));

					return array(array(
						'Error_Msg' => 'YesNo',
						'Error_Code' => 202,
						'Alert_Msg' => 'Дата и значение исхода отличаются от данных ЛВН по совместительству, находящегося в реестре или принятого ФСС.<br>Указать данные из ЛВН по совместительству?',
						'LeaveData' => $resp_esb[0]
					));
				}
			}
		}

		$lpuFields = $this->defineLpuFields(array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'EvnStickBase_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		$data['Lpu_lid'] = $lpuFields['Lpu_lid'];
		$data['Lpu_fid'] = $lpuFields['Lpu_fid'];
		$data['Lpu_sid'] = $lpuFields['Lpu_sid'];
		$data['Lpu_tid'] = $lpuFields['Lpu_tid'];
		$data['Lpu_outid'] = $lpuFields['Lpu_outid'];

		if ($data['EvnStickBase_IsFSS']) { // checkbox
			$data['EvnStick_IsFSS'] = 2;
		} else {
			$data['EvnStick_IsFSS'] = 1;
		}

		$data['Diag_pid'] = $this->defineDiagPid(array(
			'EvnStick_id' => $data['EvnStick_id'],
			'EvnStick_mid' => $data['EvnStick_mid'],
			'EvnStick_IsFSS' => $data['EvnStick_IsFSS'],
			'RegistryESStorage_id' => $data['RegistryESStorage_id']
		));

		if(!empty($data['EvnStick_id'])) {
			$err = $this->updateNextStickLink($data, 'edit');

			if (!empty($err['Error_Msg'])) {
				return array($err);
			}
		}

		if (!empty($data['EvnStick_pid'])) {
			// иногда в EvnStick_pid почему то сохраняется ссылка на ЛВН на другого человека, хотя в EvnStick поле pid должно ссылаться только на случай (ТАП/КВС)
			// добавил проверку, чтобы найти место, где такое может происходить refs #169715
			$resp_check = $this->queryResult("
				select
					EvnClass_SysNick
				from
					v_Evn (nolock)
				where
					Evn_id = :EvnStick_pid
			", array(
				'EvnStick_pid' => $data['EvnStick_pid']
			));

			if (!empty($resp_check[0]['EvnClass_SysNick']) && !in_array($resp_check[0]['EvnClass_SysNick'], array('EvnPL', 'EvnPLStom', 'EvnPS'))) {
				return array(array('Error_Msg' => 'Неверно определён родительский случай для сохраняемого ЛВН'));
			}
		}

		$this->beginTransaction();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnStick_id;

			exec p_EvnStick_" . (!empty($data['EvnStick_id']) ? "upd" : "ins") . "
				@EvnStick_id = @Res output,
				-- @EvnStick_CallFrom = 'Stick_saveEvnStick_" . (!empty($data['EvnStick_id']) ? "upd" : "ins") . "',
				@EvnStick_pid = :EvnStick_pid,
				@EvnStick_mid = :EvnStick_mid,
				@EvnStick_nid = :EvnStick_nid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@StickType_id = 1,
				@StickCause_id = :StickCause_id,
				@StickCause_did = :StickCause_did,
				@StickCauseDopType_id = :StickCauseDopType_id,
				@EvnStick_Ser = :EvnStick_Ser,
				@EvnStick_Num = :EvnStick_Num,
				@EvnStick_setDT = :EvnStick_setDate,
				@EvnStick_consentDT = :EvnStickBase_consentDT,
				@EvnStick_disDT = :EvnStick_disDate,
				@StickOrder_id = :StickOrder_id,
				@EvnStick_prid = :EvnStick_prid,
				@Org_id = :Org_id,
				@EvnStick_OrgNick = :EvnStick_OrgNick,
				@Post_Name = :Post_Name,
				@StickWorkType_id = :StickWorkType_id,
				@EvnStick_BirthDate = :EvnStick_BirthDate,
				@EvnStick_sstBegDate = :EvnStick_sstBegDate,
				@EvnStick_sstEndDate = :EvnStick_sstEndDate,
				@EvnStick_sstNum = :EvnStick_sstNum,
				@Org_did = :Org_did,
				@EvnStick_mseDT = :EvnStick_mseDT,
				@EvnStick_mseRegDT = :EvnStick_mseRegDT,
				@EvnStick_mseExamDT = :EvnStick_mseExamDT,
				@StickIrregularity_id = :StickIrregularity_id,
				@EvnStick_irrDT = :EvnStick_irrDT,
				@StickLeaveType_id = :StickLeaveType_id,
				@StickLeaveType_rid = :StickLeaveType_id,
				@EvnStick_stacBegDate = :EvnStick_stacBegDate,
				@EvnStick_stacEndDate = :EvnStick_stacEndDate,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@EvnStick_StickDT = :EvnStick_StickDT,
				@EvnStick_IsRegPregnancy = :EvnStick_IsRegPregnancy,
				@EvnStick_IsOriginal = :EvnStick_IsOriginal,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@EvnStick_oid = :EvnStick_oid,
				@EvnStick_adoptDate = :EvnStick_adoptDate,
				@EvnStick_regBegDate = :EvnStick_regBegDate,
				@EvnStick_regEndDate = :EvnStick_regEndDate,
				@StickRegime_id = :StickRegime_id,
				@Lpu_oid = :Lpu_oid,
				@Signatures_id = :Signatures_id,
				@Signatures_iid = :Signatures_iid,
				@Lpu_lid = :Lpu_lid,
				@Lpu_fid = :Lpu_fid,
				@Lpu_sid = :Lpu_sid,
				@Lpu_tid = :Lpu_tid,
				@Lpu_outid = :Lpu_outid,
				@Diag_pid = :Diag_pid,
				@StickFSSData_id = :StickFSSData_id,
				@EvnStick_IsFSS = :EvnStick_IsFSS,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//		-- @MedPersonal_mseid = :MedPersonal_mseid,

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'EvnStick_oid' => $data['EvnStick_oid'],
			'EvnStick_pid' => $data['EvnStick_pid'],
			'EvnStick_mid' => $data['EvnStick_mid'],
			'EvnStick_nid' => $data['EvnStick_nid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'StickCause_id' => $data['StickCause_id'],
			'StickCause_did' => $data['StickCause_did'],
			'StickCauseDopType_id' => $data['StickCauseDopType_id'],
			'EvnStick_Ser' => $data['EvnStick_Ser'],
			'EvnStick_Num' => $data['EvnStick_Num'],
			'EvnStick_setDate' => $data['EvnStick_setDate'],
			'EvnStickBase_consentDT' => $data['EvnStickBase_consentDT'],
			'EvnStick_disDate' => $data['EvnStick_disDate'],
			'StickOrder_id' => $data['StickOrder_id'],
			'EvnStick_prid' => $data['EvnStick_prid'],
			'Org_id' => $data['Org_id'],
			'EvnStick_OrgNick' => $data['EvnStick_OrgNick'],
			'Post_Name' => $data['Post_Name'],
			'StickWorkType_id' => $data['StickWorkType_id'],
			'EvnStick_BirthDate' => $data['EvnStick_BirthDate'],
			'EvnStick_sstBegDate' => $data['EvnStick_sstBegDate'],
			'EvnStick_sstEndDate' => $data['EvnStick_sstEndDate'],
			'EvnStick_sstNum' => $data['EvnStick_sstNum'],
			'Org_did' => $data['Org_did'],
			'EvnStick_mseDT' => $data['EvnStick_mseDate'],
			'EvnStick_mseRegDT' => $data['EvnStick_mseRegDate'],
			'EvnStick_mseExamDT' => $data['EvnStick_mseExamDate'],
			// 'MedPersonal_mseid' => $data['MedPersonal_mseid'],
			'StickIrregularity_id' => $data['StickIrregularity_id'],
			'EvnStick_irrDT' => $data['EvnStick_irrDate'],
			'StickLeaveType_id' => $data['StickLeaveType_id'],
			'EvnStick_stacBegDate' => $data['EvnStick_stacBegDate'],
			'EvnStick_stacEndDate' => $data['EvnStick_stacEndDate'],
			'EvnStick_IsDisability' => $data['EvnStick_IsDisability'],
			'InvalidGroupType_id' => $data['InvalidGroupType_id'],
			'EvnStick_StickDT' => $data['EvnStick_StickDT'],
			'EvnStick_IsRegPregnancy' => $data['EvnStick_IsRegPregnancy'],
			'EvnStick_IsOriginal' => $data['EvnStick_IsOriginal'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_oid' => $data['Lpu_oid'],
			'EvnStick_adoptDate' => $data['EvnStick_adoptDate'],
			'EvnStick_regBegDate' => $data['EvnStick_regBegDate'],
			'EvnStick_regEndDate' => $data['EvnStick_regEndDate'],
			'StickRegime_id' => $data['StickRegime_id'],
			'Signatures_id' => $data['Signatures_id'],
			'Signatures_iid' => $data['Signatures_iid'],
			'Lpu_lid' => $data['Lpu_lid'],
			'Lpu_fid' => $data['Lpu_fid'],
			'Lpu_sid' => $data['Lpu_sid'],
			'Lpu_tid' => $data['Lpu_tid'],
			'Lpu_outid' => $data['Lpu_outid'],
			'Diag_pid' => $data['Diag_pid'],
			'StickFSSData_id' => $data['StickFSSData_id'],
			'EvnStick_IsFSS' => $data['EvnStick_IsFSS'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($data['EvnStickDop_pid']) && $data['EvnStickDop_pid'] > 0) {
			//в ЛВН с типом занятости "Основная работа" не может быть ссылки на другой ЛВН (PROMEDWEB-6948)
			//$queryParams['EvnStick_pid'] = $data['EvnStickDop_pid'];
			return array(array('Error_Msg' => 'Неверно определён тип занятости, в ЛВН по основной работе не должно быть ссылки на другой ЛВН'));
		}

		if (!empty($queryParams['EvnStick_id']) && !empty($queryParams['EvnStick_pid']) && $queryParams['EvnStick_id'] == $queryParams['EvnStick_pid']) {
			$this->rollbackTransaction();
			return $this->createError('','Идентификатор ЛВН и идентификатор родительского события не должны быть одинаковыми');
		}

		$result = $this->db->query($query, $queryParams);

		// удаляем связь с учётным документом если в ЛВН нет периодов или исхода добавленных в текущей МО
		$this->deleteEvnStickLinks($data, $EvnStick_mid);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			$this->UpdEvnStickDop($data);

			if (empty($data['EvnStick_id'])) {
				$data['EvnStick_id'] = $resp[0]['EvnStick_id'];
				$err = $this->updateNextStickLink($data, 'add');
				
				if (!empty($err['Error_Msg'])) {
					return $err;
				}
			}

			if (getRegionNick() != 'kz') {
				if (!empty($resp[0]['EvnStick_id'])) {
					// убираем не нужные ссылки на ЛВН
					$filter_up = "";
					if (!empty($data['RegistryESStorage_id'])){
						$filter_up .= " and RegistryESStorage_id <> :RegistryESStorage_id";
					}
					$this->db->query("
						update RegistryESStorage with (rowlock) set EvnStickBase_id = null, RegistryESStorage_bookDT = null where EvnStickBase_id = :EvnStickBase_id {$filter_up};
					", array(
						'RegistryESStorage_id' => (!empty($data['RegistryESStorage_id']) ? $data['RegistryESStorage_id'] : null),
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}

				if (!empty($resp[0]['EvnStick_id']) && !empty($data['RegistryESStorage_id'])) {
					// адпейтим ссылку на ЛВН
					$this->db->query("
						update RegistryESStorage with (rowlock) set EvnStickBase_id = :EvnStickBase_id, RegistryESStorage_bookDT = null where RegistryESStorage_id = :RegistryESStorage_id;
					", array(
						'RegistryESStorage_id' => $data['RegistryESStorage_id'],
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}

				if (!empty($resp[0]['EvnStick_id']) && !empty($data['StickFSSData_id'])) {
					// адпейтим ссылку на ЛВН
					$this->db->query("
						update StickFSSDataGet with (rowlock) set EvnStickBase_id = :EvnStickBase_id where StickFSSData_id = :StickFSSData_id;
					", array(
						'StickFSSData_id' => $data['StickFSSData_id'],
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}
				
                if (!empty($data['Signatures_id'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_id'],
                        'Evn_id' => $resp[0]['EvnStick_id'],
                        'SignObject' => 'leave'
                    ));
                    if (!empty($check['Error_Msg'])) {
                    	$this->rollbackTransaction();
                        return array($check);
                    }
                }

                if (!empty($data['Signatures_iid'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_iid'],
                        'Evn_id' => $resp[0]['EvnStick_id'],
                        'SignObject' => 'irr'
                    ));
                    if (!empty($check['Error_Msg'])) {
                    	$this->rollbackTransaction();
                        return array($check);
                    }
                }
			}

			$this->commitTransaction();

			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Устанавливаем ссылку на ЛВН продолжение
	 */
	function setEvnStick_nid($EvnStick_id, $EvnStick_nid) {

		// обновляем ссылку в ЛВН
		$query = "
			update EvnStickBase with (rowlock) 
			set EvnStickBase_nid = :EvnStick_nid 
			where EvnStickBase_id = :EvnStick_id
		";
		$queryParams = array(
			'EvnStick_id' => $EvnStick_id,
			'EvnStick_nid' => $EvnStick_nid
		);
		$this->db->query($query, $queryParams);

		//меняем статус подписи исхода на "документ не актуален"
		$query = "
			update Signatures with (rowlock)
			set SignaturesStatus_id = 3 -- документ не актуален
			where 
				Signatures_id in (
					select S.Signatures_id
					from 
						Signatures S with (nolock)
						left join EvnStick ES with (nolock) on ES.Signatures_id = S.Signatures_id
						left join EvnStickDop ESD with (nolock) on ESD.Signatures_id = S.Signatures_id
					where
						(
							ES.EvnStick_id = :EvnStick_id
							or ESD.EvnStickDop_id = :EvnStick_id
						)
						and S.SignaturesStatus_id = 1
				)
		";
		$this->db->query($query, $queryParams);
	}

	/**
	 * Обновление ссылки на ЛВН продолжение в предыдущем ЛВН
	 */
	function updateNextStickLink($data, $action) {
		// запрос изменения ссылки на продолжение, в ЛВН первичном
		// $queryChangeLink = "
		// 	update EvnStickBase with (rowlock) 
		// 	set EvnStickBase_nid = :EvnStickBase_nid 
		// 	where EvnStickBase_id = :EvnStickBase_id
		// ";

		$queryStick = "
			select top 1
				isnull(ES.EvnStick_prid, ESD.EvnStickDop_prid) as EvnStick_prid,
				ESB.EvnStickBase_id,
				ESB.EvnStickBase_Num,
				case 
					when exists(
					select EvnStickBase_id 
					from v_EvnStickBase with (nolock)
					where 
					EvnStickBase_id = ESB.EvnStickBase_oid  
					and isnull(EvnStickBase_IsDelQueue, 1) = 1
					) then ESB.EvnStickBase_oid else null
				end as EvnStickBase_oid,
				ESB.EvnStickBase_nid,
				ESB.StickOrder_id,
				isnull(ESB.EvnStickBase_IsInReg, 1) as EvnStickBase_IsInReg,
				isnull(ESB.EvnStickBase_IsPaid, 1) as EvnStickBase_IsPaid,
				isnull(ESB.EvnStickBase_IsInRegDel, 1) as EvnStickBase_IsInRegDel,
				isnull(E.Evn_deleted, 1) as Evn_deleted
			from
				EvnStickBase ESB with (nolock)
				left join EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
				left join EvnStickDop ESD with (nolock) on ESD.EvnStickDop_id = ESB.EvnStickBase_id
				inner join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
			where 
				ESB.EvnStickBase_id = :EvnStick_id
		";
		$curStick = $this->getFirstRowFromQuery($queryStick, array('EvnStick_id' => $data['EvnStick_id']));
		if (empty($curStick)) {
			return array(
				'Error_Msg' => 'Ошибка при получении текущего ЛВН'
			);

		}
		if ($curStick['StickOrder_id'] == 1 && !empty($data['StickOrder_id']) && $data['StickOrder_id'] == 2) { //первичный стал продолжением
			$action = 'add';
		}
		
		switch ($action) {
			case 'add':
				// обновление ссылки при создании нового ЛВН продолжения
				if($data['StickOrder_id'] == 2 && !empty($data['EvnStick_prid'])) {
					$query = "
						select top 1 
							ESB.EvnStickBase_Num,
							ESB.EvnStickBase_nid,
							isnull(ESB.EvnStickBase_IsInReg, 1) as EvnStickBase_IsInReg,
							isnull(ESB.EvnStickBase_IsPaid, 1) as EvnStickBase_IsPaid
						from
							EvnStickBase ESB with (nolock)
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
					$prevStick = $this->getFirstRowFromQuery($query, array('EvnStickBase_id' => $data['EvnStick_prid']));

					if (
						$prevStick['EvnStickBase_IsPaid'] == 1
						&& (
							$prevStick['EvnStickBase_IsInReg'] == 1
							|| $prevStick['EvnStickBase_nid'] == null
						)
					) {
						// $queryParams = array(
						// 	'EvnStickBase_id' => $data['EvnStick_prid'],
						// 	'EvnStickBase_nid' => $data['EvnStick_id']
						// );
						// $this->db->query($queryChangeLink, $queryParams);
						$this->setEvnStick_nid($data['EvnStick_prid'], $data['EvnStick_id']);
					}					
				}
				break;
			case 'edit':	
				if (!empty($curStick['EvnStick_prid'])) {
					$prevStickOld = $this->getFirstRowFromQuery($queryStick, array('EvnStick_id' => $curStick['EvnStick_prid']));
				}
				if (!empty($data['EvnStick_prid'])) {
					$prevStickNew = $this->getFirstRowFromQuery($queryStick, array('EvnStick_id' => $data['EvnStick_prid']));
				}
				

				//изменилась ссылка на предыдущий ЛВН
				if($data['StickOrder_id'] == 2 && $curStick['EvnStick_prid'] != $data['EvnStick_prid'] && !empty($prevStickOld)) {

					if ($prevStickOld['EvnStickBase_IsInReg'] == 2 && $prevStickOld['EvnStickBase_IsPaid'] == 1) {
						return array(
							'Error_Msg' => 'Ok',
							'Error_Code' => 203,
							'EvnStick_prid' => $prevStickOld['EvnStickBase_id'],
							'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'], 
							'Alert_Msg' => "Изменение ЛВН временно недоступно. ЛВН {$curStick['EvnStickBase_Num']} направлен в единую базу данных ФСС в качестве ЛВН продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}"
						);
					}

					if (
						$prevStickOld['EvnStickBase_IsPaid'] == 2
						&& $prevStickOld['EvnStickBase_IsInRegDel'] == 1
						&& $prevStickOld['Evn_deleted'] == 1
					) {
						if(empty($data['ignoreQuestionPrevInFSS'])) {
							return array(
								'Error_Msg' => 'YesNo',
								'Error_Code' => 104,
								'EvnStick_prid' => $prevStickOld['EvnStickBase_id'],
								'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'],
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_Num']} сохранен в единой базе данных ФСС в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}. В поле  \"Предыдущий\" указан другой ЛВН. Продолжить сохранение?"
							);
						} else {
							if (
								$prevStickNew['EvnStickBase_IsPaid'] == 1
								&& (
									$prevStickNew['EvnStickBase_IsInReg'] == 1
									|| $prevStickNew['EvnStickBase_nid'] == null
								)
							) {
								// $queryParams = array(
								// 	'EvnStickBase_id' => $data['EvnStick_prid'],
								// 	'EvnStickBase_nid' => $data['EvnStick_id']
								// );
								// $this->db->query($queryChangeLink, $queryParams);
								$this->setEvnStick_nid($data['EvnStick_prid'], $data['EvnStick_id']);
							}					
							return false; // продолжаем сохранение ЛВН
						}
					}

					if ($prevStickOld['EvnStickBase_IsInReg'] == 1) {

						if (empty($data['ignoreQuestionChangePrev'])) { 
							return array(
								'Error_Msg' => 'YesNo',
								'Error_Code' => 105,
								'EvnStick_prid' => $prevStickOld['EvnStickBase_id'],
								'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'],
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_Num']} сохранен в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}. При изменении, необходимо оформить продолжение для ЛВН {$prevStickOld['EvnStickBase_Num']}  либо удалить.  Продолжить сохранение?"
							);
						} else {
							if (
								$prevStickNew['EvnStickBase_IsPaid'] == 1
								&& (
									$prevStickNew['EvnStickBase_IsInReg'] == 1
									|| $prevStickNew['EvnStickBase_nid'] == null
								)
							) {
								// $queryParams = array(
								// 	'EvnStickBase_id' => $data['EvnStick_prid'],
								// 	'EvnStickBase_nid' => $data['EvnStick_id']
								// );
								// $this->db->query($queryChangeLink, $queryParams);
								$this->setEvnStick_nid($data['EvnStick_prid'], $data['EvnStick_id']);
							}

							// $queryParams = array(
							// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
							// 	'EvnStickBase_nid' => null
							// );
							// $this->db->query($queryChangeLink, $queryParams);
							$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], null);

							return false;
						}
					}
					 
					

					// удаляем ссылку на текущий ЛВН из предыдущего ЛВН
					if (!empty($prevStickOld['EvnStickBase_id'])) {
						// $queryParams = array(
						// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
						// 	'EvnStickBase_nid' => null
						// );
						// $this->db->query($queryChangeLink, $queryParams);
						$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], null);
					}
					

					// сохраняемм ссылку на текущий ЛВН в новый предыдущий ЛВН
					if (
						$prevStickNew['EvnStickBase_IsPaid'] == 1
						&& (
							$prevStickNew['EvnStickBase_IsInReg'] == 1
							|| $prevStickNew['EvnStickBase_nid'] == null
						)
					) {
						// $queryParams = array(
						// 	'EvnStickBase_id' => $data['EvnStick_prid'],
						// 	'EvnStickBase_nid' => $data['EvnStick_id']
						// );
						// $this->db->query($queryChangeLink, $queryParams);
						$this->setEvnStick_nid($data['EvnStick_prid'], $data['EvnStick_id']);
					}
					
				}
				// продолжение стало первичным ЛВН
				if($data['StickOrder_id'] == 1 && !empty($curStick['EvnStick_prid'])) {

					// ЛВН первичный в реестре, но ещё не принят
					if (
						$prevStickOld['EvnStickBase_IsInReg'] == 2
						&& $prevStickOld['EvnStickBase_IsPaid'] == 1
					) {
						return array(
							'Error_Msg' => 'Ok',
							'Error_Code' => 204,
							'EvnStick_prid' => $prevStickOld['EvnStickBase_id'],
							'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'],
							'Alert_Msg' => "Изменение порядка выдачи ЛВН временно недоступно. ЛВН {$curStick['EvnStickBase_id']} направлен в единую базу данных ФСС в качестве ЛВН продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}"
						);
					}

					// ЛВН первичный принят в ФСС
					if (
						$prevStickOld['EvnStickBase_IsPaid'] == 2
						&& $prevStickOld['EvnStickBase_IsInRegDel'] == 1
						&& $prevStickOld['Evn_deleted'] == 1
					) {
						if (empty($data['ignoreQuestionPrevInFSS'])) {
							return array(
								'Error_Msg' => 'YesNo',
								'Error_Code' => 106,
								'EvnStick_prid' => $prevStickOld['EvnStickBase_id'],
								'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'],
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_Num']} сохранен в единой базе данных ФСС в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}. В поле  \"Порядок выдачи\" указан «первичный». Продолжить сохранение?"
							);
						} else {
							return false;
						}					
					}

					// ЛВН первичный в реестре
					if ($prevStickOld['EvnStickBase_IsInReg'] == 1) {
						if (empty($data['ignoreQuestionChangePrev'])) {
							return array(
								'Error_Msg' => 'YesNo',
								'Error_Code' => 107,
								'EvnStick_prid' => $prevStickOld['EvnStick_prid'],
								'EvnStick_prNum' => $prevStickOld['EvnStickBase_Num'],
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_id']} сохранен в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}. При изменении, необходимо оформить продолжение для ЛВН {$prevStickOld['EvnStickBase_Num']}  либо удалить ЛВН.  Продолжить сохранение?"
							);
						} else {
							if ( $prevStickOld['EvnStickBase_IsPaid'] == 1) {
								// $queryParams = array(
								// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
								// 	'EvnStickBase_nid' => null
								// );
								// $this->db->query($queryChangeLink, $queryParams);
								$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], null);
							}
							
							return false;
						}
					}

					// в остальных случаях ссылка удаляется
					// $queryParams = array(
					// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
					// 	'EvnStickBase_nid' => null
					// );
					// $this->db->query($queryChangeLink, $queryParams);
					$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], null);
				}
				break;
			case 'del':
			case 'cancel':
				if (!empty($curStick['EvnStick_prid'])) {
					$prevStickOld = $this->getFirstRowFromQuery($queryStick, array('EvnStick_id' => $curStick['EvnStick_prid']));
				}

				$nextId = $curStick['EvnStickBase_oid'];//если удаляем дубликат то меняем ссылку в предыдущем на оригинал

				if (!empty($prevStickOld)) {
					// предыдущий ЛВН в реестре, но ещё не принят
					if (
						$prevStickOld['EvnStickBase_IsInReg'] == 2
						&& $prevStickOld['EvnStickBase_IsPaid'] == 1
					) {
						return array(
							'Error_Msg' => "Удаление ЛВН временно недоступно. ЛВН {$curStick['EvnStickBase_Num']} направлен в единую базу данных ФСС в качестве ЛВН продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}."
						);
					}

					// предыдущий ЛВН принят в ФСС
					if (
						$prevStickOld['EvnStickBase_IsPaid'] == 2
						&& $prevStickOld['EvnStickBase_IsInRegDel'] == 1
						&& $prevStickOld['Evn_deleted'] == 1
					) {
						if (empty($data['ignoreStickHasPrevious'])) {
							return array(
								'Error_Msg' => 'YesNo',
								'Alert_Code' => 707,
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_Num']} сохранен в единой базе данных ФСС в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}.  Продолжить удаление?"
							);
						} else {
							return false;
						}
					}

					// предыдущий ЛВН не в реестре и не удалён
					if (
						$prevStickOld['EvnStickBase_IsInReg'] == 1
						&& $prevStickOld['Evn_deleted'] == 1
					) {
						if (empty($data['ignoreStickHasPrevious'])) {
							return array(
								'Error_Msg' => 'YesNo',
								'Alert_Code' => 707,
								'Alert_Msg' => "ЛВН {$curStick['EvnStickBase_Num']} сохранен в качестве продолжения ЛВН {$prevStickOld['EvnStickBase_Num']}. При удалении необходимо оформить продолжение для ЛВН {$prevStickOld['EvnStickBase_Num']}  либо удалить.  Продолжить удаление?"
							);
						} else {
							// удаляем ссылку в предыдущем ЛВН
							// $queryParams = array(
							// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
							// 	'EvnStickBase_nid' => null
							// );
							// $this->db->query($queryChangeLink, $queryParams);
							$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], $nextId);
							return false;
						}
					}

					// для остальных ситуаций удаляем ссылку в предыдущем ЛВН
					// $queryParams = array(
					// 	'EvnStickBase_id' => $prevStickOld['EvnStickBase_id'],
					// 	'EvnStickBase_nid' => null
					// );
					// $this->db->query($queryChangeLink, $queryParams);
					$this->setEvnStick_nid($prevStickOld['EvnStickBase_id'], $nextId);

					return false; 
				}
				
				break;
		}
		return false;
	}

	/**
	 * Проверка расхождений ФСС
	 */
	function checkStickFSS($data) {
		
		if (!empty($data['EvnStickBase_id'])) {
			$queryParams_sf = array('EvnStickBase_id' => $data['EvnStickBase_id']);
			
			// проверяем наличие запроса в ФСС и ответа из ФСС
			$resp_sf = $this->queryResult("
				select top 1
					SFD.StickFSSData_id,
					SFD.StickFSSDataStatus_id,
					SFDG.StickFSSDataGet_id
				from
					v_StickFSSData SFD (nolock)
					left join v_StickFSSDataGet SFDG (nolock) on SFDG.StickFSSData_id = SFD.StickFSSData_id
				where
					SFDG.EvnStickBase_id = :EvnStickBase_id
					and SFD.StickFSSDataStatus_id in (4, 5)
				order by
					SFD.StickFSSData_xmlExpDT desc

			", $queryParams_sf);

			if (!empty($resp_sf[0]['StickFSSData_id']) && $resp_sf[0]['StickFSSDataStatus_id'] == 5) {
				// пришёл отрицательный ответ (статус запроса «ЭЛН не подтверждён» – см. ТЗ "Реестры ЛВН"): выводится сообщение «ЭЛН с указанными параметрами не обнаружен в ФСС», сохранение ЛВН не производится.
				return array(array('Error_Msg' => 'ЭЛН с указанными параметрами не обнаружен в ФСС.'));
			}


			if (!empty($resp_sf[0]) && $resp_sf[0]['StickFSSDataStatus_id'] == 4 && !empty($resp_sf[0]['StickFSSDataGet_id'])) {
				// пришёл положительный ответ (статус запроса «ЭЛН подтверждён» – см. ТЗ "Реестры ЛВН"): выполняются проверки на наличие расхождений (см. ТЗ "Реестры ЛВН").
				$this->load->model('StickFSSData_model');
				$resp_err = $this->StickFSSData_model->checkStickFSSDataGet(array(
					'StickFSSDataGet_id' => $resp_sf[0]['StickFSSDataGet_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp_err[0])) {
					$resp_errname = $this->queryResult("
						select top 1
							StickFSSErrorType_Name
						from
							v_StickFSSErrorType (nolock)
						where
							StickFSSErrorType_id = :StickFSSErrorType_id
					", array(
						'StickFSSErrorType_id' => $resp_err[0]
					));

					if (!empty($resp_errname[0]['StickFSSErrorType_Name'])) {
						return array(array('Error_Msg' => $resp_errname[0]['StickFSSErrorType_Name']));
					}
				}
			}
		}
	}
	
	/**
	 * Обновление исхода в ЛВН по совместительству
	 */
	function UpdEvnStickDop($data) {
		if (empty($data['EvnStick_id'])) {
			return false;
		}
		$query = "
			select ESB.EvnStickBase_id, convert(varchar(10), max(EvnStickWorkRelease_endDT), 120) as EvnStick_disDate
			from v_EvnStickBase ESB with(nolock)
			left join v_EvnStickWorkRelease ESWR with(nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
			where ESB.EvnStickBase_pid = :EvnStick_id
			group by ESB.EvnStickBase_id
		";
		$stick_dop = $this->db->query($query, array(
			'EvnStick_id' => $data['EvnStick_id']
		));
		$stick_dop = $stick_dop->result('array');
		foreach ($stick_dop as $sd) {
			$EvnStick_disDate = !empty($data['StickLeaveType_id'])
				? $data['StickLeaveType_id'] == 1 ? date('Y-m-d', strtotime("+1 day", strtotime($sd['EvnStick_disDate']))) : $sd['EvnStick_disDate']
				: NULL;
			$this->db->query("update Evn set Evn_disDT = :EvnStick_disDate where Evn_id = :EvnStickBase_id", array(
				'EvnStickBase_id' => $sd['EvnStickBase_id'],
				'EvnStick_disDate' => $EvnStick_disDate
			));
		}
	}

	/**
	 * Сохранение статуса ЛВН
	 */
	function ReCacheEvnStickStatus($data) {
		if (empty($data['EvnStick_id'])) {
			return false;
		}

		// получаем текущий статус и нужный статус
		$query = "
			select
				ESTATUS.EvnStatus_SysNick,
				ESWR_DRAFT.EvnStickWorkRelease_id
			from
				v_EvnStick es (nolock)
				left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
				outer apply(
					select top 1
						eswr.EvnStickWorkRelease_id
					from
						v_EvnStickWorkRelease eswr (nolock)
					where
						eswr.EvnStickBase_id = es.EvnStick_id
						and eswr.EvnStickWorkRelease_IsDraft = 2
				) ESWR_DRAFT
			where
				es.EvnStick_id = :EvnStick_id
		";

		$result = $this->db->query($query, array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		$EvnStatus_SysNick = 'Confirm';

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				if (!empty($resp[0]['EvnStickWorkRelease_id'])) {
					$EvnStatus_SysNick = 'Draft';
				}

				if ($EvnStatus_SysNick != $resp[0]['EvnStatus_SysNick']) {
					$this->load->model('Evn_model', 'Evn_model');
					$this->Evn_model->updateEvnStatus(array(
						'Evn_id' => $data['EvnStick_id'],
						'EvnStatus_SysNick' => $EvnStatus_SysNick,
						'EvnClass_SysNick' => 'EvnStick',
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		return false;
	}
	

	/**
	 *	Сохранение записи по уходу за пациентов
	 */
	function saveEvnStickCarePerson($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnStickCarePerson_id;

			exec p_EvnStickCarePerson_" . (!empty($data['EvnStickCarePerson_id']) && $data['EvnStickCarePerson_id'] > 0 ? "upd" : "ins") . "
				@EvnStickCarePerson_id = @Res output,
				@Evn_id = :Evn_id,
				@Person_id = :Person_id,
				@RelatedLinkType_id = :RelatedLinkType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnStickCarePerson_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnStickCarePerson_id' => (!empty($data['EvnStickCarePerson_id']) && $data['EvnStickCarePerson_id'] > 0 ? $data['EvnStickCarePerson_id'] : NULL),
			'Evn_id' => $data['Evn_id'],
			'Person_id' => $data['Person_id'],
			'RelatedLinkType_id' => empty($data['RelatedLinkType_id']) ? null : $data['RelatedLinkType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение ЛВН по совместиетельству
	 */
	function saveEvnStickDop($data) {
		if (getRegionNick() != 'kz') {
			if (!empty($data['RegistryESStorage_id'])) {
				// берем номер из хранилища
				$resp_es = $this->queryResult("
					select
						EvnStickBase_Num,
						EvnStickBase_id
					from
						v_RegistryESStorage (nolock)
					where
						RegistryESStorage_id = :RegistryESStorage_id
				", array(
					'RegistryESStorage_id' => $data['RegistryESStorage_id']
				));

				if (!empty($resp_es[0]['EvnStickBase_id']) && (empty($data['EvnStick_id']) || $data['EvnStick_id'] != $resp_es[0]['EvnStickBase_id'])) {
					return array(array('Error_Msg' => 'Данный номер ЭЛН уже использован. Необходимо получить новый номер.', 'Error_Code' => '401'));
				}

				if (!empty($resp_es[0])) {
					$data['EvnStick_Num'] = $resp_es[0]['EvnStickBase_Num'];
				}
			}
		}

		// Вытаскиваем Lpu_id и EvnStick_pid + поля из шапки, если есть связка текущего учетного документа с ЛВН из другого документа
		if ( !empty($data['EvnStick_id']) ) {
			$query = "
				select top 1
					EvnStickDop_Num as EvnStick_Num,
					EvnStickDop_mid as EvnStick_mid,
					EvnStickDop_pid as EvnStick_pid,
					EvnStickDop_prid as EvnStick_prid,
					EvnStickDop_Ser as EvnStick_Ser,
					convert(varchar(10), EvnStickDop_setDT, 120) as EvnStick_setDate,
					Lpu_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					StickOrder_id,
					StickWorkType_id
				from 
					v_EvnStickDop ES with (nolock)
				where
					EvnStickDop_id = ISNULL(:EvnStick_id, 0)
			";

			$queryParams = array(
				'EvnStick_id' => $data['EvnStick_id']
			);

			$result = $this->db->query($query, $queryParams);

			$response = $result->result('array');

			if ( !is_array($response) ) {
				return array(array('Error_Msg' => 'Ошибка при получении данных по ЛВН'));
			}
			if (isset($response[0])) {
				if ($response[0]['EvnStick_mid'] != $data['EvnStick_mid']) {
					foreach ($response[0] as $key => $value) {
						// if ( $key != 'EvnStick_mid' ) {
						$data[$key] = $value;
						// }
					}
				}

				$data['Lpu_id'] = $response[0]['Lpu_id'];
			}
		}

		if (empty($data['ignoreStickLeaveTypeCheck']) && !empty($data['EvnStickDop_pid'])) {
			$resp_esb = $this->queryResult("
				SELECT
					MedStaffFact_id,
					MedPersonal_id,
					StickLeaveType_rid as StickLeaveType_id,
					convert(varchar(10), EvnStickBase_disDate, 120) as EvnStick_disDate
				FROM
					v_EvnStickBase (nolock)
				WHERE
					EvnStickBase_id = :EvnStickBase_id
					and EvnStickBase_IsInReg = 2
			", array(
				'EvnStickBase_id' => $data['EvnStickDop_pid']
			));

			if (!empty($resp_esb[0]['MedStaffFact_id'])) {
				if (
					$data['MedStaffFact_id'] != $resp_esb[0]['MedStaffFact_id']
					|| $data['StickLeaveType_id'] != $resp_esb[0]['StickLeaveType_id']
					|| $data['EvnStick_disDate'] != $resp_esb[0]['EvnStick_disDate']
				) {
					$resp_esb[0]['EvnStick_disDate'] = date('d.m.Y', strtotime($resp_esb[0]['EvnStick_disDate']));

					return array(array(
						'Error_Msg' => 'YesNo',
						'Error_Code' => 202,
						'Alert_Msg' => 'Дата и значение исхода отличаются от данных ЛВН по основному месту работы, находящегося в реестре или принятого ФСС.<br>Указать данные из ЛВН по основному месту работы?',
						'LeaveData' => $resp_esb[0]
					));
				}
			}
		}

		// проверяем что в ЛВН по совместительству совпадают поля с ЛВН по основному месту работы(если он в реестре или принят)
		if ($data['StickWorkType_id'] == 2 && !empty($data['EvnStickDop_pid'])) {
			$query = "
				select top 1
					ESB.EvnStickBase_id,
					ESB.StickOrder_id,
					ESB.StickCause_id
				from
					v_EvnStickBase ESB with (nolock)
					outer apply(
						select top 1 EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease with (nolock)
						where
							EvnStickBase_id = ESB.EvnStickBase_id
							and (
								isnull(EvnStickWorkRelease_IsInReg, 1) = 2
								or isnull(EvnStickWorkRelease_IsPaid, 1) = 2
							)
					) as ESWR
				where
					ESB.EvnStickBase_id = :EvnStickDop_pid
					and (
						ESWR.EvnStickWorkRelease_id is not null
						or isnull(ESB.EvnStickBase_IsInReg, 1) = 2
						or isnull(ESB.EvnStickBase_IsPaid, 1) = 2
					)
			";
			$queryParams = array(
				'EvnStickDop_pid' => $data['EvnStickDop_pid']
			);

			$resp_checkOrigfFields = $this->getFirstRowFromQuery($query, $queryParams);
			if (!empty($resp_checkOrigfFields)) {
				if( empty($data['ignoreCheckFieldStickOrder']) && $resp_checkOrigfFields['StickOrder_id'] != $data['StickOrder_id'] ) {
					return array(array(
						'Error_Msg' => 'YesNoCancel',
						'Alert_Msg' => 'Причина нетрудоспособности отличается от данных ЛВН по основному месту работы, находящегося в реестре или принятого ФСС. Указать данные из ЛВН по основному месту работы?',
						'StickOrder_id' => $resp_checkOrigfFields['StickOrder_id'],
						'Error_Code' => 205
					));
				}
				if( empty($data['ignoreCheckFieldStickCause']) && $resp_checkOrigfFields['StickCause_id'] != $data['StickCause_id'] ) {
					return array(array(
						'Error_Msg' => 'YesNoCancel',
						'Alert_Msg' => 'Порядок выдачи отличается от данных ЛВН по основному месту работы, находящегося в реестре или принятого ФСС. Указать данные из ЛВН по основному месту работы?',
						'StickCause_id' => $resp_checkOrigfFields['StickCause_id'],
						'Error_Code' => 206
					));
				}
			}
		}

		$lpuFields = $this->defineLpuFields(array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'EvnStickBase_id' => $data['EvnStick_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		$data['Lpu_lid'] = $lpuFields['Lpu_lid'];
		$data['Lpu_fid'] = $lpuFields['Lpu_fid'];
		$data['Lpu_sid'] = $lpuFields['Lpu_sid'];
		$data['Lpu_tid'] = $lpuFields['Lpu_tid'];
		$data['Lpu_outid'] = $lpuFields['Lpu_outid'];

		if ($data['EvnStickBase_IsFSS']) { // checkbox
			$data['EvnStickDop_IsFSS'] = 2;
		} else {
			$data['EvnStickDop_IsFSS'] = 1;
		}

		if (!empty($data['EvnStick_id'])) {			
			$err = $this->updateNextStickLink($data, 'edit');

			if (!empty($err['Error_Msg'])) {
				return array($err);
			}
		}

		$this->beginTransaction();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnStickDop_id;

			exec p_EvnStickDop_" . (empty($data['EvnStick_id']) ? "ins" : "upd") . "
				@EvnStickDop_id = @Res output,
				@EvnStickDop_pid = :EvnStickDop_pid,
				@EvnStickDop_mid = :EvnStickDop_mid,
				@StickType_id = 1,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnStickDop_setDT = :EvnStickDop_setDate,
				@EvnStickDop_consentDT = :EvnStickBase_consentDT,
				@EvnStickDop_disDT = :EvnStickDop_disDate,
				@EvnStickDop_StickDT = :EvnStickDop_StickDT,
				@StickCause_did = :StickCause_did,
				@StickOrder_id = :StickOrder_id,
				@StickCause_id = :StickCause_id,
				@StickWorkType_id = :StickWorkType_id,
				@Org_id = :Org_id,
				@EvnStickDop_OrgNick = :EvnStickDop_OrgNick,
				@Post_Name = :Post_Name,
				@EvnStickDop_prid = :EvnStickDop_prid,
				@EvnStickDop_Ser = :EvnStickDop_Ser,
				@EvnStickDop_oid = :EvnStickDop_oid,
				@EvnStickDop_Num = :EvnStickDop_Num,
				@EvnStickDop_IsOriginal = :EvnStickDop_IsOriginal,
				@Signatures_id = :Signatures_id,
				@Signatures_iid = :Signatures_iid,
				@Lpu_lid = :Lpu_lid,
				@Lpu_fid = :Lpu_fid,
				@Lpu_sid = :Lpu_sid,
				@Lpu_tid = :Lpu_tid,
				@Lpu_outid = :Lpu_outid,
				@StickFSSData_id = :StickFSSData_id,
				@EvnStickDop_IsFSS = :EvnStickDop_IsFSS,
				@StickLeaveType_rid = :StickLeaveType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnStickDop_id' => (!empty($data['EvnStick_id']) ? $data['EvnStick_id'] : NULL),
			'EvnStickDop_pid' => $data['EvnStickDop_pid'],
			'EvnStickDop_mid' => $data['EvnStick_mid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnStickDop_setDate' => $data['EvnStick_setDate'],
			'EvnStickBase_consentDT' => $data['EvnStickBase_consentDT'],
			'EvnStickDop_disDate' => $data['EvnStick_disDate'],
			'StickWorkType_id' => $data['StickWorkType_id'],
			'EvnStickDop_StickDT' => $data['EvnStick_StickDT'],
			'StickCause_did' => $data['StickCause_did'],
			'StickOrder_id' => $data['StickOrder_id'],
			'StickCause_id' => $data['StickCause_id'],
			'Org_id' => $data['Org_id'],
			'EvnStickDop_OrgNick' => $data['EvnStick_OrgNick'],
			'Post_Name' => $data['Post_Name'],
			'EvnStickDop_Ser' => $data['EvnStick_Ser'],
			'EvnStickDop_prid' => $data['EvnStick_prid'],
			'EvnStickDop_Num' => $data['EvnStick_Num'],
			'Signatures_id' => $data['Signatures_id'],
			'Signatures_iid' => $data['Signatures_iid'],
			'Lpu_lid' => $data['Lpu_lid'],
			'Lpu_fid' => $data['Lpu_fid'],
			'Lpu_sid' => $data['Lpu_sid'],
			'Lpu_tid' => $data['Lpu_tid'],
			'Lpu_outid' => $data['Lpu_outid'],
			'StickFSSData_id' => $data['StickFSSData_id'],
			'EvnStickDop_IsFSS' => $data['EvnStickDop_IsFSS'],
			'StickLeaveType_id' => $data['StickLeaveType_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnStickDop_oid' => $data['EvnStick_oid'],
			'EvnStickDop_IsOriginal' => $data['EvnStick_IsOriginal']
		);

		if (!empty($queryParams['EvnStickDop_id']) && !empty($queryParams['EvnStickDop_pid']) && $queryParams['EvnStickDop_id'] == $queryParams['EvnStickDop_pid']) {
			$this->rollbackTransaction();

			return $this->createError('','Идентификатор ЛВН и идентификатор родительского события не должны быть одинаковыми');
		}

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnStick_id']) && empty($data['EvnStick_id'])) {
				$data['EvnStick_id'] = $resp[0]['EvnStick_id'];
				
				$err = $this->updateNextStickLink($data, 'add');

				if (!empty($err['Error_Msg'])) {
					$this->rollbackTransaction();

					return array($err);
				}
			}

			if (getRegionNick() != 'kz') {
				if (!empty($resp[0]['EvnStick_id'])) {
					// убираем не нужные ссылки на ЛВН
					$filter_up = "";
					if (!empty($data['RegistryESStorage_id'])){
						$filter_up .= " and RegistryESStorage_id <> :RegistryESStorage_id";
					}
					$this->db->query("
						update RegistryESStorage with (rowlock) set EvnStickBase_id = null, RegistryESStorage_bookDT = null where EvnStickBase_id = :EvnStickBase_id {$filter_up};
					", array(
						'RegistryESStorage_id' => $data['RegistryESStorage_id'],
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}

				if (!empty($resp[0]['EvnStick_id']) && !empty($data['RegistryESStorage_id'])) {
					// адпейтим ссылку на ЛВН
					$this->db->query("
						update RegistryESStorage with (rowlock) set EvnStickBase_id = :EvnStickBase_id, RegistryESStorage_bookDT = null where RegistryESStorage_id = :RegistryESStorage_id
					", array(
						'RegistryESStorage_id' => $data['RegistryESStorage_id'],
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}

				if (!empty($resp[0]['EvnStick_id']) && !empty($data['StickFSSData_id'])) {
					// адпейтим ссылку на ЛВН
					$this->db->query("
						update StickFSSDataGet with (rowlock) set EvnStickBase_id = :EvnStickBase_id where StickFSSData_id = :StickFSSData_id;
					", array(
						'StickFSSData_id' => $data['StickFSSData_id'],
						'EvnStickBase_id' => $resp[0]['EvnStick_id']
					));
				}

                if (!empty($data['Signatures_id'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_id'],
                        'Evn_id' => $resp[0]['EvnStick_id'],
                        'SignObject' => 'leave'
                    ));
                    if (!empty($check['Error_Msg'])) {
                    	$this->rollbackTransaction();

                        return array($check);
                    }
                }

                if (!empty($data['Signatures_iid'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_iid'],
                        'Evn_id' => $resp[0]['EvnStick_id'],
                        'SignObject' => 'irr'
                    ));
                    if (!empty($check['Error_Msg'])) {
                    	$this->rollbackTransaction();

                        return array($check);
                    }
                }
			}

			$this->commitTransaction();

			return $resp;
		}
		else {
			$this->rollbackTransaction();

			return false;
		}
	}


	/**
	 *	Сохранение справки учащегося
	 */
	function saveEvnStickStudent($data) {

		if (!empty($data['Org_id'])) {
			$this->load->model('Person_model');
			if (!empty($data['doUpdateJobInfo'])) {
				// Обновляем данные о месте работы,
				// Метод "update_PersonJob" проверяет значения и обновляет только то, что указано
				$this->Person_model->update_PersonJob(array(
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Org_id' => $data['Org_id']
				));
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnStickStudent_id;

			exec p_EvnStickStudent_" . (!empty($data['EvnStick_id']) && $data['EvnStick_id'] > 0 ? "upd" : "ins") . "
				@EvnStickStudent_id = @Res output,
				@EvnStickStudent_pid = :EvnStickStudent_pid,
				@EvnStickStudent_mid = :EvnStickStudent_mid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnStickStudent_setDT = :EvnStickStudent_setDT,
				@EvnStickStudent_Num = :EvnStickStudent_Num,
				@StickCause_id = :StickCause_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@Org_id = :Org_id,
				@StickType_id = 2,
				@StickRecipient_id = :StickRecipient_id,
				@EvnStickStudent_IsContact = :EvnStickStudent_IsContact,
				@EvnStickStudent_ContactDescr = :EvnStickStudent_ContactDescr,
				@EvnStickStudent_begDT = :EvnStickStudent_begDT,
				@EvnStickStudent_Days = :EvnStickStudent_Days,
				@Okei_id = :Okei_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnStickStudent_id' => $data['EvnStick_id'],
			'EvnStickStudent_pid' => $data['EvnStick_pid'],
			'EvnStickStudent_mid' => $data['EvnStick_pid'], // Возможно, замена на EvnStick_mid не потребуется
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnStickStudent_setDT' => $data['EvnStick_setDate'],
			'EvnStickStudent_Num' => $data['EvnStick_Num'],
			'StickCause_id' => $data['StickCause_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Org_id' => $data['Org_id'],
			'StickRecipient_id' => $data['StickRecipient_id'],
			'EvnStickStudent_IsContact' => $data['EvnStick_IsContact'],
			'EvnStickStudent_ContactDescr' => $data['EvnStick_ContactDescr'],
			'EvnStickStudent_begDT' => $data['EvnStickStudent_begDT'],
			'EvnStickStudent_Days' => $data['EvnStickStudent_Days'],
			'Okei_id' => $data['Okei_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка есть ли ЛВН в учётном документе
	 */
	function checkLvnExist($data) {
		if (empty($data['Evn_id'])) {return false;}

		$query = "
			select top 1 ESB.EvnStickBase_id
			from 
				v_EvnStickBase ESB with (nolock)
			where
				ESB.EvnStickBase_pid = :Evn_id

			union all

			select top 1 ESB.EvnStickBase_id
			from
				v_EvnStickBase ESB with (nolock)
				inner join v_EvnLink EL with (nolock) on EL.Evn_lid = ESB.EvnStickBase_id
			where
				EL.Evn_id = :Evn_id
		";

		$resp = $this->getFirstResultFromQuery($query, array('Evn_id' => $data['Evn_id']));

		return $resp;
	}

	/**
	 * Получает MedPersonal_id соответствующий месту работы врача
	 */
	function getMedPerosnal($MedStaffFact_id) {
		$query = "
			select top 1
				MedPersonal_id
			from
				v_MedStaffFact MSF with (nolock)
			where
				MedStaffFact_id = :MedStaffFact_id
		";
		return $this->getFirstResultFromQuery($query, array('MedStaffFact_id' => $MedStaffFact_id));
	}

	/**
	 *	Сохранение записи об освобождении от работы
	 */
	function saveEvnStickWorkRelease($data, $mode = 'remote') {
		if ( $mode == 'remote' ) {
			// проверка на перечения дат с предыдущими случаями
			if ( $this->countCrossDate($data) > 0 ) {
				// надо привести коды ошибок в соответствие
				return array(
					array(
						'Error_Code' => 1110,
						'Error_Msg' => 'Обнаружено пересечение периодов освобождения от работы!<br/>Проверьте указанные сроки и исправьте.'
					)
				);
			}
		}

		if (!empty($data['EvnStickWorkRelease_id'])) {
			// проверяем, что освобождение не в реестре, иначе не сохраняем.
			$dbreg = $this->load->database('registry', true);
			$result_inreg = $dbreg->query("
				select top 1
					EvnStickWorkRelease_id
				from
					v_EvnStickWorkRelease with (nolock)
				where
					EvnStickWorkRelease_id = :EvnStickWorkRelease_id
					and EvnStickWorkRelease_IsInReg = 2
			", array(
				'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id']
			));
			if ( is_object($result_inreg) ) {
				$resp_inreg = $result_inreg->result('array');
				if (!empty($resp_inreg[0]['EvnStickWorkRelease_id'])) {
					return array(array('Error_Msg' => 'Нельзя отредактировать период освобождения от работы, т.к. он находится в реестре'));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка проверки наличия периода освобождения в реестре'));
			}
		}
		
		// 4. Ограничить максимальный срок выдачи ЛВН одним врачом 30 днями нетрудоспособности, т.е. <Первая дата начала освобождения от работы> –
		// <Последняя дата конца освобождения> + 1 день = не больше 30 дней. Проверка идет только в случае, если врач один
		if ( !empty($data['MedPersonal2_id']) && !isset($data['MedPersonal3_id']) && $data['Override30Day'] != 'true' ) {
			$DayCount = $this->getDayReleaseCountByMP($data);

			if ( $DayCount === NULL ) {
				$DayCount = 0;
			}

			$DayCount += round((strtotime($data['EvnStickWorkRelease_endDate']) - strtotime($data['EvnStickWorkRelease_begDate']))/(24*60*60));

			if ( $DayCount + 1 > 30 ) {
				return array(
					array(
						'Error_Code' => 1111,
						'Error_Msg' => 'Длительность ЛВН превышает 30 дней, вы точно хотите выписать продление без комиссии?'
					)
				);
			}
		}

		$resp = $this->checkEvnStickWorkReleaseCount($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		
		if (empty($data['MedPersonal2_id'])) { $data['MedPersonal2_id'] = null; }
		if (empty($data['MedPersonal3_id'])) { $data['MedPersonal3_id'] = null; }
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnStickWorkRelease_id;

			exec p_EvnStickWorkRelease_" . (!empty($data['EvnStickWorkRelease_id']) && $data['EvnStickWorkRelease_id'] > 0 ? "upd" : "ins") . "
				@EvnStickWorkRelease_id = @Res output,
				@EvnStickBase_id = :EvnStickBase_id,
				@EvnStickWorkRelease_begDT = :EvnStickWorkRelease_begDate,
				@EvnStickWorkRelease_endDT = :EvnStickWorkRelease_endDate,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedStaffFact2_id = :MedStaffFact2_id,
				@MedStaffFact3_id = :MedStaffFact3_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedPersonal2_id = :MedPersonal2_id,
				@MedPersonal3_id = :MedPersonal3_id,
				@EvnStickWorkRelease_IsPredVK = :EvnStickWorkRelease_IsPredVK,
				@EvnStickWorkRelease_IsDraft = :EvnStickWorkRelease_IsDraft,
				@EvnStickWorkRelease_IsSpecLpu = :EvnStickWorkRelease_IsSpecLpu,
				@Org_id = :Org_id,
				@LpuSection_id = :LpuSection_id,
				@Post_id = :Post_id,
				@Signatures_mid = :Signatures_mid,
				@Signatures_wid = :Signatures_wid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnStickWorkRelease_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if($data['EvnStickWorkRelease_IsPredVK']==0) { $data['EvnStickWorkRelease_IsPredVK'] = 1; } else { $data['EvnStickWorkRelease_IsPredVK'] = 2; }
		if($data['EvnStickWorkRelease_IsDraft']==0) { $data['EvnStickWorkRelease_IsDraft'] = 1; } else { $data['EvnStickWorkRelease_IsDraft'] = 2; }
		if($data['EvnStickWorkRelease_IsSpecLpu']==0) { $data['EvnStickWorkRelease_IsSpecLpu'] = 1; } else { $data['EvnStickWorkRelease_IsSpecLpu'] = 2; }
		$queryParams = array(
			'EvnStickWorkRelease_id' => (!empty($data['EvnStickWorkRelease_id']) && $data['EvnStickWorkRelease_id'] > 0 ? $data['EvnStickWorkRelease_id'] : NULL),
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_begDate' => $data['EvnStickWorkRelease_begDate'],
			'EvnStickWorkRelease_endDate' => $data['EvnStickWorkRelease_endDate'],
			'MedPersonal_id' => (!empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null),
			'MedPersonal2_id' => $data['MedPersonal2_id'],
			'MedPersonal3_id' => $data['MedPersonal3_id'],
			'MedStaffFact_id' => (empty($data['MedStaffFact_id']))?null:$data['MedStaffFact_id'],
			'MedStaffFact2_id' => (empty($data['MedStaffFact2_id']))?null:$data['MedStaffFact2_id'],
			'MedStaffFact3_id' => (empty($data['MedStaffFact3_id']))?null:$data['MedStaffFact3_id'],
			'LpuSection_id' => (!empty($data['LpuSection_id'])?$data['LpuSection_id']:null),
			'EvnStickWorkRelease_IsPredVK' => $data['EvnStickWorkRelease_IsPredVK'],
			'EvnStickWorkRelease_IsDraft' => $data['EvnStickWorkRelease_IsDraft'],
			'EvnStickWorkRelease_IsSpecLpu' => $data['EvnStickWorkRelease_IsSpecLpu'],
			'Org_id' => (!empty($data['Org_id'])?$data['Org_id']:null),
			'Post_id' => (!empty($data['Post_id'])?$data['Post_id']:null),
			'Signatures_mid' => (!empty($data['Signatures_mid'])?$data['Signatures_mid']:null),
			'Signatures_wid' => (!empty($data['Signatures_wid'])?$data['Signatures_wid']:null),
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($queryParams['MedStaffFact_id']) && empty($queryParams['MedPersonal_id'])) {
			$queryParams['MedPersonal_id'] = $this->getMedPerosnal($queryParams['MedStaffFact_id']);
		}
		if (!empty($queryParams['MedStaffFact2_id']) && empty($queryParams['MedPersonal2_id'])) {
			$queryParams['MedPersonal2_id'] = $this->getMedPerosnal($queryParams['MedStaffFact2_id']);
		}
		if (!empty($queryParams['MedStaffFact3_id']) && empty($queryParams['MedPersonal3_id'])) {
			$queryParams['MedPersonal3_id'] = $this->getMedPerosnal($queryParams['MedStaffFact3_id']);
		}

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$ret = $result->result('array');
			if (!empty($ret[0]['EvnStickWorkRelease_id'])) {
				// обновить ссылку на EvnVK
				if (!empty($data['EvnVK_id'])) {				
					$query = "UPDATE EvnVK SET EvnStickWorkRelease_id = :EvnStickWorkRelease_id WHERE EvnVK_id = :EvnVK_id";
					
					$queryParams = array(
						'EvnStickWorkRelease_id' => $ret[0]['EvnStickWorkRelease_id'],
						'EvnVK_id' => $data['EvnVK_id']
					);
				} else {
					$query = "UPDATE EvnVK SET EvnStickWorkRelease_id = NULL WHERE EvnStickWorkRelease_id = :EvnStickWorkRelease_id";
					
					$queryParams = array(
						'EvnStickWorkRelease_id' => $ret[0]['EvnStickWorkRelease_id']
					);				
				}
				$this->db->query($query, $queryParams);

				// обновляем Lpu_lid в EvnStick
				$this->updateLpuFields(array(
					'EvnStickBase_id' => $data['EvnStickBase_id']
				));

                if (!empty($data['Signatures_mid'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_mid'],
                        'Evn_id' => $ret[0]['EvnStickWorkRelease_id'],
                        'SignObject' => 'MP'
                    ));
                    if (!empty($check['Error_Msg'])) {
                        return array($check);
                    }
                }
                if (!empty($data['Signatures_wid'])) {
                    $check = $this->checkSignatureHash(array(
                        'Signatures_id' => $data['Signatures_wid'],
                        'Evn_id' => $ret[0]['EvnStickWorkRelease_id'],
                        'SignObject' => 'VK'
                    ));
                    if (!empty($check['Error_Msg'])) {
                        return array($check);
                    }
                }
			}		
			return $ret;
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка количества освобождений в ЛВН
	 */
	function checkEvnStickWorkReleaseCount($data) {
		$queryParams = array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id']
		);

		$query = "
			select top 1
				ST.StickType_SysNick,
				WR.Count as WorkReleaseCount
			from v_EvnStickBase ESB with(nolock)
			left join v_StickType ST with(nolock) on ST.StickType_id = ESB.StickType_id
			outer apply (
				select top 1 count(EvnStickWorkRelease_id) as Count
				from v_EvnStickWorkRelease with(nolock)
				where EvnStickBase_id = ESB.EvnStickBase_id
				and EvnStickWorkRelease_id <> isnull(:EvnStickWorkRelease_id, 0)
			) WR
			where ESB.EvnStickBase_id = :EvnStickBase_id
		";

		$EvnStick = $this->getFirstRowFromQuery($query, $queryParams);
		if (!is_array($EvnStick)) {
			return $this->createError('1112', 'Ошибка при проверке освобождения от работы');
		}


		if( ! isset($data['EvnStickWorkRelease_id']) || empty($data['EvnStickWorkRelease_id'])){
			// прибавляем +1 т.к. далее мы добавим новый период и всего периодов станет на 1 больше
			$EvnStick['WorkReleaseCount'] += 1;
		}

		$isKZ = ($this->getRegionNick() == 'kz');
		if ($EvnStick['StickType_SysNick'] == 'spravka' && $EvnStick['WorkReleaseCount'] > 2) {
			return $this->createError('1113', 'Количество записей об освобождении от работы не может превышать двух');
		} else if ($EvnStick['StickType_SysNick'] == 'blist' && $EvnStick['WorkReleaseCount'] > ($isKZ?4:3)) {
			return $this->createError('1113', 'Количество записей об освобождении от работы не может превышать '.($isKZ?'четырех':'трех'));
		}

		return array(array('success' => true));
	}


	/**
	 * Проверка номера на занятость существующими ЛВН в текущем году и со свободными из хранилища номеров
	 */
	function checkEvnStickNumDouble($data) {
		//находим ЛВН с таким же номером (исключая текущий ЛВН) в текущей МО в текущем году
		$filter = !empty($data['EvnStick_id']) ? 'and EvnStickBase_id <> :EvnStick_id':'';
		$query = "
			declare @getYear int = year(dbo.tzGetDate());

			select top 1
				EvnStickBase_Num
			from v_EvnStickBase (nolock)
			where 1 = 1
				and EvnStickBase_Num like :EvnStickNum
				and Lpu_id = :Lpu_id
				{$filter}
				and isnull(year(EvnStickBase_setDate), year(EvnStickBase_insDT)) = @getYear
		";
		$doubleNum = $this->getFirstRowFromQuery($query, $data);
		if(!empty($doubleNum)) {
			return array('success' => false, 'error_code' => 1, 'message' => 'Номер ЛВН уже используется в рамках текущего года. Проверьте корректность введенного номера. Для создания электронного больничного получите номер ЭЛН из хранилища.');
		}
		
		//находим незанятый номер, совпадающий с введенным
		$query = "
			declare @curDT datetime = dbo.tzGetDate();
			
			select top 1
				EvnStickBase_Num
			from
				v_RegistryESStorage (nolock)
			where
				EvnStickBase_id is null
				and Lpu_id = :Lpu_id
				and RegistryESStorage_NumQuery <> ''
				and EvnStickBase_Num like :EvnStickNum
		";
		$numInStore = $this->getFirstRowFromQuery($query, $data);
		if(!empty($numInStore)) {
			return array('success' => false, 'error_code' => 2, 'message' => 'Проверьте корректность номера ЛВН. При создании электронного ЛВН необходимо получить номер из хранилища, для этого нажмите кнопку "Получить номер ЭЛН".');
		}
		
		return array('success' => true, 'error_code' => 0, 'message' => '');
	}

	/**
	 *	Обновление краткого наименования организации в справочнике организаций
	 */
	function updateOrgStickNick($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_Org_StickNick_set
				@Org_id = :Org_id,
				@Org_StickNick = :Org_StickNick,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Org_id' => $data['Org_id'],
			'Org_StickNick' => $data['Org_StickNick']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Проверка дубликатов ЛВН по серии и номеру
	 */
	function checkEvnStickSerNum($data) {
		$checkResult = array(
			'success' => true,
			'Error_Msg' => ''
		);
		$filter = '';
		$queryParams = array(
			'EvnStick_Num' => $data['EvnStick_Num'],
			'EvnStick_Ser' => $data['EvnStick_Ser'],
			'pmUser_id' => $data['pmUser_id']
		);
		$selectionFields = '';

		$filter .= "
			and ESB.EvnStickBase_Ser = :EvnStick_Ser
			and ESB.EvnStickBase_Num = :EvnStick_Num
		";

		if ( !empty($data['EvnStick_id']) ) {
			$filter .= "and ESB.EvnStickBase_id != ISNULL(:EvnStick_id, 0)";
			$queryParams['EvnStick_id'] = $data['EvnStick_id'];
		}
		else if ( !empty($data['EvnStickDop_id']) ) {
			$filter .= "and ESB.EvnStickBase_id != ISNULL(:EvnStickDop_id, 0)";
			$queryParams['EvnStickDop_id'] = $data['EvnStickDop_id'];
		}

		$query = "
			select top 1
				RTRIM(ISNULL(L.Lpu_Nick, '')) as Lpu_Name
				" . $selectionFields. "
			from
				v_EvnStickBase ESB with (nolock)
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
				inner join v_Lpu L with (nolock) on L.Lpu_id = ESB.Lpu_id
			where EC.EvnClass_SysNick in ('EvnStick', 'EvnStickDop')
				" . $filter . "
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( count($response) > 0 ) {
					$checkResult['success'] = false;
					$checkResult['Error_Msg'] = 'В базе данных обнаружен дубль ЛВН по серии и номеру (' . $response[0]['Lpu_Name'] . ')';
				}
			}
			else {
				$checkResult['success'] = false;
				$checkResult['Error_Msg'] = 'Ошибка при проверке ЛВН на дубли по серии и номеру';
			}
		}
		else {
			$checkResult['success'] = false;
			$checkResult['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка ЛВН на дубли по серии и номеру)';
		}

		return $checkResult;
	}


	/**
	 *	Получение данных для печати справки учащегося
	 */
	function getEvnStickStudentFields($data) {
		$query = "
			select top 1
				ISNULL(ESS.EvnStickStudent_ContactDescr, '') as EvnStickStudent_ContactDescr,
				DAY(ESS.EvnStickStudent_setDate) as EvnStickStudent_setDate_Day,
				MONTH(ESS.EvnStickStudent_setDate) as EvnStickStudent_setDate_Month,
				YEAR(ESS.EvnStickStudent_setDate) as EvnStickStudent_setDate_Year,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(MP.Person_SurName, '')) + ' ' + ISNULL(LEFT(MP.Person_FirName, 1) + '.', '') + RTRIM(LEFT(MP.Person_SecName, 1) + '.') as MedPersonal_Fin,
				RTRIM(ISNULL(Org.Org_Name, '')) as Org_Name,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(SC.StickCause_Code, 0)) as StickCause_Code,
				RTRIM(ISNULL(SC.StickCause_Name, 0)) as StickCause_Name,
				RTRIM(ISNULL(SR.StickRecipient_Code, 0)) as StickRecipient_Code,
				(datediff(year, PS.Person_BirthDay, dbo.tzGetDate())
					+ case when month(PS.Person_BirthDay) > month(dbo.tzGetDate()) or (month(PS.Person_BirthDay) = month(dbo.tzGetDate()) and day(PS.Person_BirthDay) > day(dbo.tzGetDate()))
					then -1 else 0 end
				) as Person_Age,
				DAY(PS.Person_BirthDay) as Person_Birthday_Day,
				MONTH(PS.Person_BirthDay) as Person_Birthday_Month,
				YEAR(PS.Person_BirthDay) as Person_Birthday_Year,
				ISNULL(YesNo.YesNo_Code, 0) as EvnStickStudent_IsContact
			from v_EvnStickStudent ESS with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = ESS.Person_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ESS.Lpu_id
				inner join StickCause SC with (nolock) on SC.StickCause_id = ESS.StickCause_id
				inner join StickRecipient SR with (nolock) on SR.StickRecipient_id = ESS.StickRecipient_id
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ESS.MedPersonal_id
					and MP.Lpu_id = ESS.Lpu_id
				inner join Org with (nolock) on Org.Org_id = ESS.Org_id
				inner join YesNo with (nolock) on YesNo.YesNo_id = ESS.EvnStickStudent_IsContact
			where ESS.EvnStickStudent_id = :EvnStickStudent_id
				-- and ESS.Lpu_id = :Lpu_id
		";

		$queryParams = array(
			'EvnStickStudent_id' => $data['EvnStickStudent_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных для печати справки учащегося)'));
		}

	}


	/**
	 *	Получение списка освобождений от работы для печати ЛВН
	 */
	function getEvnStickStudentWorkReleaseFields($data) {
		$query = "
			select top 2
				DAY(ESWR.EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDay,
				MONTH(ESWR.EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begMonth,
				YEAR(ESWR.EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begYear,
				DAY(ESWR.EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endDay,
				MONTH(ESWR.EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endMonth,
				YEAR(ESWR.EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endYear
			from v_EvnStickWorkRelease ESWR with (nolock)
			where ESWR.EvnStickBase_id = :EvnStickBase_id
		";

		$queryParams = array(
			'EvnStickBase_id' => $data['EvnStickStudent_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Метод недоступен'));
		}

	}


	/**
	 *	Получение списка ЛВН для формы ЛВН: Поиск
	 */
	function loadEvnStickSearchGrid($data) {
		$filterList = array("(1 = 1)");
		$filterListLS = array("(1 = 1)");
		$joinList = array();
		$joinListES = array();
        $join_section = "";
		$orgFilterList = array();
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id']
		);

		if ( empty($data['SearchType_id']) || $data['SearchType_id'] == 1 ) { // 1. По выписанным в нашем МО
			// по этой МО 
			$orgFilterList[] = "ESB.Lpu_id = :Lpu_id";
			 // если есть освобождение этой МО
			$orgFilterList[] = "exists (
				select top 1 Org_id
				from v_EvnStickWorkRelease ESWR with (nolock)
				where ESWR.EvnStickBase_id = ESB.EvnStickBase_id
					and ESWR.Org_id = :Org_id
			)";
		}

		if ( empty($data['SearchType_id']) || $data['SearchType_id'] == 2 ) { // 2. По направленным в наше МО
			// по направленным в наше МО (либо направлен в МО либо МО в поле Санаторий)
			$orgFilterList[] = "(ES.Lpu_oid = :Lpu_id)";
			$orgFilterList[] = "(ESB.Org_did = :Org_id)";
		}

		// Если зашли не под МО, то подразумеваем, что это санаторий, и добавляем фильтр ко всем вариантам поиска
		if ( !isset($data['session']['orgtype']) || (isset($data['session']['orgtype']) && $data['session']['orgtype'] != 'lpu') || empty($data['Lpu_id']) ) { 
			$orgFilterList[]  = "ESB.Org_did = :Org_id";
		}

		if ( !empty($data['EvnStickBase_begDate']) ) {
			if ( !empty($data['EvnStickBase_begDate'][0]) && !empty($data['EvnStickBase_begDate'][1]) ) {
				$filterList[] = "ESWRBDT.EvnStickWorkRelease_begDT between :EvnStickBase_begDate0 and :EvnStickBase_begDate1";
				$queryParams['EvnStickBase_begDate0'] = $data['EvnStickBase_begDate'][0];
				$queryParams['EvnStickBase_begDate1'] = $data['EvnStickBase_begDate'][1];
			}
			else if ( !empty($data['EvnStickBase_begDate'][0]) ) {
				$filterList[] = "ESWRBDT.EvnStickWorkRelease_begDT >= :EvnStickBase_begDate0";
				$queryParams['EvnStickBase_begDate0'] = $data['EvnStickBase_begDate'][0];
			}
			else if ( !empty($data['EvnStickBase_begDate'][1]) ) {
				$filterList[] = "ESWRBDT.EvnStickWorkRelease_begDT <= :EvnStickBase_begDate1";
				$queryParams['EvnStickBase_begDate1'] = $data['EvnStickBase_begDate'][1];
			}
		}

		if ( !empty($data['EvnStickBase_endDate']) ) {
			if ( !empty($data['EvnStickBase_endDate'][0]) && !empty($data['EvnStickBase_endDate'][1]) ) {
				$filterList[] = "ESWREDT.EvnStickWorkRelease_endDT between :EvnStickBase_endDate0 and :EvnStickBase_endDate1";
				$queryParams['EvnStickBase_endDate0'] = $data['EvnStickBase_endDate'][0];
				$queryParams['EvnStickBase_endDate1'] = $data['EvnStickBase_endDate'][1];
			}
			else if ( !empty($data['EvnStickBase_endDate'][0]) ) {
				$filterList[] = "ESWREDT.EvnStickWorkRelease_endDT >= :EvnStickBase_endDate0";
				$queryParams['EvnStickBase_endDate0'] = $data['EvnStickBase_endDate'][0];
			}
			else if ( !empty($data['EvnStickBase_endDate'][1]) ) {
				$filterList[] = "ESWREDT.EvnStickWorkRelease_endDT <= :EvnStickBase_endDate1";
				$queryParams['EvnStickBase_endDate1'] = $data['EvnStickBase_endDate'][1];
			}
		}
                //Сигнальная информация
		if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
                    $filterList[] = "ESWREDT.EvnStickWorkRelease_endDT >= DATEADD (day,-1, @getDT)";
                    //$queryParams['EvnStickBase_endDate0'] = $data['EvnStickBase_endDate'][0];
                }

		if ( !empty($data['EvnStickBase_Num']) ) {
			$filterList[] = "ESB.EvnStickBase_Num = :EvnStickBase_Num";
			$queryParams['EvnStickBase_Num'] = $data['EvnStickBase_Num'];
		}

		if ( !empty($data['EvnStickBase_Ser']) ) {
			$filterList[] = "ESB.EvnStickBase_Ser = :EvnStickBase_Ser";
			$queryParams['EvnStickBase_Ser'] = $data['EvnStickBase_Ser'];
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filterList[] = "PS.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['Person_Firname']) ) {
			$filterList[] = "PS.Person_Firname like :Person_Firname";
			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		if ( !empty($data['Person_Secname']) ) {
			$filterList[] = "PS.Person_Secname like :Person_Secname";
			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		if ( !empty($data['Person_Surname']) ) {
			$filterList[] = "PS.Person_Surname like :Person_Surname";
			$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';
		}

		if ( !empty($data['StickType_id']) ) {
			$filterList[] = "ESB.StickType_id = :StickType_id";
			$queryParams['StickType_id'] = $data['StickType_id'];
		}

		// @task https://redmine.swan.perm.ru/issues/85664
		// Такая вот извращенная логика джойна v_EvnStick из-за того, что в v_EvnStickDop нет поля StickLeaveType_id
		// Возможно, потом придется расширять список фильтров, при которых указанный джойн должен идти сперва через v_EvnStickDop
		if ( !empty($data['EvnStick_IsClosed']) ) {
			$joinListES[] = "left join v_EvnStickDop ESD with (nolock) on ESD.EvnStickDop_id = ESB.EvnStickBase_id";
			$joinListES[] = "left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ISNULL(ESD.EvnStickDop_pid, ESB.EvnStickBase_id)";
		}
		else {
			$joinListES[] = "left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id";
			$joinListES[] = "left join v_EvnStickDop ESD with (nolock) on ESD.EvnStickDop_id = ESB.EvnStickBase_id";
		}

		$joinList = array_merge($joinList, $joinListES);

		if ( !empty($data['EvnStick_IsClosed']) ) {
			if ( $data['EvnStick_IsClosed'] == 2 ) {
				$filterList[] = "ESB.StickLeaveType_rid is not null";
			}
			else if ( $data['EvnStick_IsClosed'] == 1 ) {
				$filterList[] = "ESB.StickLeaveType_rid is null";
			}
		}

        if ( !empty($data['CurLpuSection_id']) || !empty($data['CurLpuUnit_id']) || !empty($data['CurLpuBuilding_id']) )
        {
            $join_section .= " left join v_LpuSection LS_PL with (nolock) on LS_PL.LpuSection_id = TAP.LpuSection_id";
            $join_section .= " left join v_LpuSection LS_PS with (nolock) on LS_PS.LpuSection_id = KVS.LpuSection_id";
            $join_section .= " left join v_LpuSection LS_PLStom with (nolock) on LS_PLStom.LpuSection_id = TAPStom.LpuSection_id";

            if(!empty($data['CurLpuSection_id'])) {
                $filterListLS[] = "(LS_PL.LpuSection_id = :CurLpuSection_id or LS_PS.LpuSection_id = :CurLpuSection_id or LS_PLStom.LpuSection_id = :CurLpuSection_id)";
                $queryParams['CurLpuSection_id'] = $data['CurLpuSection_id'];
            }
            if(!empty($data['CurLpuUnit_id'])) {
                $filterListLS[] = "(LS_PL.LpuUnit_id = :CurLpuUnit_id or LS_PS.LpuUnit_id = :CurLpuUnit_id or LS_PLStom.LpuUnit_id = :CurLpuUnit_id)";
                $queryParams['CurLpuUnit_id'] = $data['CurLpuUnit_id'];
            }
            if(!empty($data['CurLpuBuilding_id'])) {
                $filterListLS[] = "(LS_PL.LpuBuilding_id = :CurLpuBuilding_id or LS_PS.LpuBuilding_id = :CurLpuBuilding_id or LS_PLStom.LpuBuilding_id = :CurLpuBuilding_id)";
                $queryParams['CurLpuBuilding_id'] = $data['CurLpuBuilding_id'];
            }
        }

		if ( !empty($data['MedPersonal1_id']) ) {
			$queryParams['MedPersonal1_id'] = $data['MedPersonal1_id'];

			$joinList[] = "
				cross apply (
					select top 1
						EvnStickWorkRelease_id
					from
						v_EvnStickWorkRelease ESWR (nolock)
					where
						ESWR.EvnStickBase_id = ESB.EvnStickBase_id
						and ESWR.MedPersonal_id = :MedPersonal1_id
				) ESWR1
			";
		}

		if ( !empty($data['MedPersonal2_id']) ) {
			$queryParams['MedPersonal2_id'] = $data['MedPersonal2_id'];

			$joinList[] = "
				cross apply (
					select top 1
						EvnStickWorkRelease_id
					from
						v_EvnStickWorkRelease ESWR (nolock)
					where
						ESWR.EvnStickBase_id = ESB.EvnStickBase_id
						and ESWR.MedPersonal2_id = :MedPersonal2_id
				) ESWR2
			";
		}

		if ( !empty($data['MedPersonal3_id']) ) {
			$queryParams['MedPersonal3_id'] = $data['MedPersonal3_id'];

			$joinList[] = "
				cross apply (
					select top 1
						EvnStickWorkRelease_id
					from
						v_EvnStickWorkRelease ESWR (nolock)
					where
						ESWR.EvnStickBase_id = ESB.EvnStickBase_id
						and ESWR.MedPersonal3_id = :MedPersonal3_id
				) ESWR3
			";
		}

		if (!empty($data['EvnStick_IsNeedSign'])) {
			$joinList[] = 'left join v_Signatures SI with (nolock) on SI.Signatures_id = ES.Signatures_id or SI.Signatures_id = ESD.Signatures_id';
			$joinList[] = 'left join v_Signatures SII with (nolock) on SII.Signatures_id = ES.Signatures_iid or SII.Signatures_id = ESD.Signatures_iid';

			$filterList[] = 'RESS.RegistryESStorage_id is not null';

			if ($data['EvnStick_IsNeedSign'] == 2) {
				// не всё подписано или подпись не актуальна
				$filterList[] = "
				(
					ESB.StickLeaveType_rid is not null and ISNULL(SI.SignaturesStatus_id, 3) <> 1 

					or 
			
					(
						(
							ES.Signatures_iid is null or 
							ISNULL(SII.SignaturesStatus_id, 3) <> 1
						) and 
						
						ES.StickIrregularity_id is not null
					) or 
					
					exists (
						select top 1 
							eswr.EvnStickWorkRelease_id 
						from 
							v_EvnStickWorkRelease eswr (nolock) 
							left join v_Signatures SIM with (nolock) on SIM.Signatures_id = eswr.Signatures_mid 
						where 
							eswr.EvnStickBase_id = es.EvnStick_id and 
							(
								eswr.Signatures_mid is null or 
								ISNULL(SIM.SignaturesStatus_id, 3) <> 1
							)
					)
				)";
			} else {
				// всё подписано и всё актуально
				$filterList[] = "(
					
					(ESB.StickLeaveType_rid is null or SI.SignaturesStatus_id = 1)
					and (
						(ES.Signatures_iid is not null and SII.SignaturesStatus_id = 1) 
						or ES.StickIrregularity_id is null
					) and not exists (
						select top 1 eswr.EvnStickWorkRelease_id 
						from 
							v_EvnStickWorkRelease eswr (nolock) 
							left join v_Signatures SIM with (nolock) on SIM.Signatures_id = eswr.Signatures_mid 
						where 
							eswr.EvnStickBase_id = es.EvnStick_id 
							and (eswr.Signatures_mid is null or ISNULL(SIM.SignaturesStatus_id, 3) <> 1)
					)
				)";
			}
		}

		if (!empty($data['RegistryESType_id'])) {
			switch($data['RegistryESType_id']) {
				case 1: // Электронные ЛН
					// Отображается список ЛВН, в ЛВН есть подписанные ЭП блоки, у которых нет признака включения в реестр (_IsInReg) и нет признака положительного ответа от ФСС (_IsPaid)
					$filterList[] = "(
						(
							ES.Signatures_id is not null and 
							ISNULL(ES.EvnStick_IsInReg,1) = 1 and 
							ISNULL(ES.EvnStick_IsPaid,1) = 1 and 
							ES.EvnStick_disDate is not null
						)
						OR exists (
							select top 1 
								eswr.EvnStickWorkRelease_id 
							from 
								v_EvnStickWorkRelease eswr (nolock) 
							where 
								eswr.EvnStickBase_id = es.EvnStick_id and 
								eswr.Signatures_mid is not null and 
								ISNULL(eswr.EvnStickWorkRelease_IsInReg, 1) = 1 and 
								ISNULL(eswr.EvnStickWorkRelease_IsPaid, 1) = 1
						)
					)";
					$filterList[] = 'RESS.RegistryESStorage_id is not null';
					break;
				case 2: // Обычные ЛН
					// Отображаются закрытые ЛВН без признака включения в реестр.
					$filterList[] = "(ISNULL(ES.EvnStick_IsInReg,1) = 1 and ES.EvnStick_disDate is not null)";
					// ЛВН, в которых нет ни одного подписанного блока
					$filterList[] = "(ES.Signatures_id is null and ES.Signatures_iid is null and not exists (select top 1 eswr.EvnStickWorkRelease_id from v_EvnStickWorkRelease eswr (nolock) where eswr.EvnStickBase_id = es.EvnStick_id and eswr.Signatures_mid is not null))";
					$filterList[] = 'RESS.RegistryESStorage_id is null';
					break;
				case 3: // ЛН на удаление
					// Отображаются ЛВН с признаком «в очереди на удаление» и без признака «включения в реестр» (IsInReg).
					$filterList[] = "(ESB.EvnStickBase_IsDelQueue = 2 and ISNULL(ES.EvnStick_IsInReg,1) = 1)";
					break;
			}
		}
		// Вид ЛВН: на бумажном носителе / электронный
		if (!empty($data['LvnType'])) {
			switch ($data['LvnType']) {
				case 1:
					$filterList[] = 'RESS.RegistryESStorage_id IS NULL';
					$filterList[] = 'ISNULL(ESB.EvnStickBase_IsFSS, 1) = 1';
					break;
				case 2:
					$filterList[] = '(RESS.RegistryESStorage_id IS NOT NULL OR ESB.EvnStickBase_IsFSS = 2)';
					break;
			}
		}

		$addQuery = "";
		$archive_database_enable = $this->config->item('archive_database_enable');
		if ( !empty($archive_database_enable) ) {
			$addQuery .= "
				, case when ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if ( empty($_REQUEST['useArchive']) ) {
				// только актуальные
				$filterList[] = "ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1";
			}
			else if ( !empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1 ) {
				// только архивные
				$data['start'] = $data['start'] - $data['archiveStart']; 
				$filterList[] = "ISNULL(ESB.EvnStickBase_IsArchive, 1) = 2";
			}
		}

		$orderbyarchive = "";

		if ( !empty($archive_database_enable) ) {
			$orderbyarchive = "case when ISNULL(ESB.EvnStickBase_IsArchive, 1) = 1 then 0 else 1 end,";
		}

		// Формируем запрос для секции WITH
		$baseWithQuery = "
			SELECT
				ESB.EvnStickBase_id,
				ESB.EvnClass_id,
				ESB.Lpu_id,
				ESB.Org_id,
				ESB.Lpu_outid,
				ESB.Lpu_fid,
				ESB.Lpu_lid,
				ESB.Lpu_tid,
				ESB.Lpu_sid,
				ESB.EvnStickBase_IsFSS,
				ESB.MedPersonal_id,
				ESB.Person_id,
				ESB.PersonEvn_id,
				ESB.Server_id,
				ESB.EvnStickBase_Ser,
				ESB.EvnStickBase_Num,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				PS.Person_Birthday,
				ESB.Post_Name,
				ESWRBDT.EvnStickWorkRelease_begDT,
				ESWRBDT.MedPersonal_id as MedPersonal_bid,
				ESWREDT.EvnStickWorkRelease_endDT as EvnStickWorkRelease_endDate,
				ESWREDT.MedPersonal_id as MedPersonal_eid,
				DATEDIFF(DAY, ESWRBDT.EvnStickWorkRelease_begDT, ESWREDT.EvnStickWorkRelease_endDT) + 1 as EvnStickWorkRelease_DaysCount,
				ESB.EvnStickBase_mid,
				ESB.EvnStickBase_pid,
				ESB.EvnStickBase_IsInReg,
				ESB.EvnStickBase_IsInRegDel,
				ESB.EvnStickBase_IsPaid,
				ESB.EvnStickBase_IsArchive,
				ESB.StickOrder_id,
				ESB.StickCause_id,
				ESB.EvnStickBase_IsDelQueue,
				ESB.StickFSSType_id,
				RESS.RegistryESStorage_id
			FROM  
				v_EvnStickBase ESB with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = ESB.Person_id
				" . implode(' ', $joinList) ."
				outer apply (
					select top 1
						MedPersonal_id,
						EvnStickWorkRelease_begDT
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = ESB.EvnStickBase_id
					order by EvnStickWorkRelease_begDT
				) ESWRBDT
				outer apply (
					select top 1 RegistryESStorage_id
					from RegistryESStorage with (nolock)
					where EvnStickBase_id = ESB.EvnStickBase_id
				) RESS
				outer apply (
					select top 1
						MedPersonal_id,
						EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = ESB.EvnStickBase_id
					order by EvnStickWorkRelease_endDT desc
				) ESWREDT
		";
		$queryWithArray = array();

		foreach ( $orgFilterList as $filter ) {
			$queryWithArray[] = $baseWithQuery . "
				" . "WHERE " . implode(' and ', $filterList) . "
				" . "and " . $filter;
		}

		$accessType = $this->getEvnStickAccessType($data);

		$query = "
                        --variables
			declare @getDT datetime = dbo.tzGetDate();
			--end variables
			-- addit with
			with ESB (
				EvnStickBase_id,
				EvnClass_id,
				Lpu_id,
				Org_id,
				Lpu_outid,
				Lpu_fid,
				Lpu_lid,
				Lpu_tid,
				Lpu_sid,
				EvnStickBase_IsFSS,
				MedPersonal_id,
				Person_id,
				PersonEvn_id,
				Server_id,
				EvnStickBase_Ser,
				EvnStickBase_Num,
				Person_Surname,
				Person_Firname,
				Person_Secname,
				Person_Birthday,
				Post_Name,
				EvnStickWorkRelease_begDT,
				MedPersonal_bid,
				EvnStickWorkRelease_endDT,
				MedPersonal_eid,
				EvnStickWorkRelease_DaysCount,
				EvnStickBase_mid,
				EvnStickBase_pid,
				EvnStickBase_IsInReg,
				EvnStickBase_IsInRegDel,
				EvnStickBase_IsPaid,
				EvnStickBase_IsArchive,
				StickOrder_id,
				StickCause_id,
				EvnStickBase_IsDelQueue,
				StickFSSType_id,
				RegistryESStorage_id
			) as (
				" . implode(' union ', $queryWithArray) . "
			)
			-- end addit with

			select
				-- select
				{$accessType}
				ESB.EvnStickBase_IsDelQueue as EvnStick_IsDelQueue,
				ESB.EvnStickBase_id,
				case
					when ESC.EvnClass_SysNick = 'EvnStick' then 1
					when ESC.EvnClass_SysNick = 'EvnStickDop' then 2
					when ESC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType,
				case
					when TAP.EvnPL_id is not null then 'EvnPL'
					when TAPStom.EvnPLStom_id is not null then 'EvnPLStom'
					when KVS.EvnPS_id is not null then 'EvnPS'
				   else ''
				end as parentClass,
				ESB.Person_id,
				COALESCE(TAP.Person_id, TAPStom.Person_id, KVS.Person_id) as Person_pid,
				ESB.PersonEvn_id,
				ESB.Server_id,
				case
					when ESC.EvnClass_SysNick = 'EvnStick' then 'Основной ЛВН'
					when ESC.EvnClass_SysNick = 'EvnStickDop' then 'Дополнительный ЛВН'
					when ESC.EvnClass_SysNick = 'EvnStickStudent' then 'Справка учащегося'
					else ''
				end as EvnStickClass_Name,
				case
					when TAP.EvnPL_id is not null then 'ТАП'
					when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
					when KVS.EvnPS_id is not null then 'КВС'
					else ''
				end as CardType,
				case
					when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
					when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
					when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
				   else ''
				end as NumCard,
				ISNULL(SO.StickOrder_Code, '0') as StickOrder_Code,
				ESB.EvnStickBase_Ser,
				ESB.EvnStickBase_Num,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				ESB.Person_Surname,
				ESB.Person_Firname,
				ESB.Person_Secname,
				convert(varchar(10), ESB.Person_Birthday, 104) as Person_Birthday,
				dbo.Age2(ESB.Person_BirthDay, @getDT)  as Person_Age,
				ISNULL(Org.Org_Name, '') as OrgJob_Name,
				COALESCE(ESB.Post_Name, ES.Post_Name, '') as Post_Name,
				ISNULL(MPFirst.Person_FIO, '') as MedPersonalFirst_Fio,
				ISNULL(MPLast.Person_FIO, '') as MedPersonalLast_Fio,
				convert(varchar(10), ESB.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESB.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				DATEDIFF(DAY, ESB.EvnStickWorkRelease_begDT, ESB.EvnStickWorkRelease_endDT) + 1 as EvnStickWorkRelease_DaysCount,
				ESB.EvnStickBase_mid as EvnStick_mid,
				ESB.EvnStickBase_pid as EvnStick_pid,
				'' as DirectLpu_Name,
				SC.StickCause_Code,
				case 
					when ESTATUS.EvnStatus_Name is not null and ESTATUS.EvnStatus_Name != '' then ESTATUS.EvnStatus_Name
					when ESB.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 'Электронный'
					else 'На бланке'
				end as EvnStatus_Name,
				SFT.StickFSSType_Name,
				REE.RegistryESError_Descr,
				ESB.EvnStickBase_IsInReg
				{$addQuery}
				-- end select
			from
				-- from
				ESB with (nolock)
				inner join EvnClass ESC with (nolock) on ESC.EvnClass_id = ESB.EvnClass_id
				-- БЛ
				" . implode(' ', $joinListES) . "
				left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = ES.EvnStatus_id
				--ТАП или КВС
				left join v_EvnPL TAP with (nolock) on ESB.EvnStickBase_mid = TAP.EvnPL_id
				left join v_EvnPLStom TAPStom with (nolock) on ESB.EvnStickBase_mid = TAPStom.EvnPLStom_id
				left join v_EvnPS KVS with (nolock) on ESB.EvnStickBase_mid = KVS.EvnPS_id
				{$join_section}
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ESB.Lpu_id
				left join v_StickOrder SO with (nolock) on SO.StickOrder_id = ESB.StickOrder_id
				left join v_StickCause SC with (nolock) on SC.StickCause_id = ESB.StickCause_id
				left join v_Org Org with (nolock) on Org.Org_id = ESB.Org_id
				left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
				outer apply (
					select top 1 Person_FIO
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ESB.MedPersonal_bid
				) MPFirst
				left join v_MedStaffFact MSFLast with(nolock) on MSFLast.MedStaffFact_id = ES.MedStaffFact_id
				outer apply (
					select top 1 Person_FIO
					from v_MedPersonal with (nolock)
					where MedPersonal_id = isnull(ESB.MedPersonal_id, MSFLast.MedPersonal_id)
				) MPLast
				outer apply (
					select top 1 RegistryESError_Descr
					from RegistryESError REE (nolock)
					inner join RegistryESData RED (nolock) on 
						RED.Evn_id = REE.Evn_id and 
						RED.RegistryES_id = REE.RegistryES_id 
					where 
						REE.Evn_id = ESB.EvnStickBase_id
						and RED.RegistryESDataStatus_id = 3
					order by 
						REE.RegistryES_id desc
				) REE
				-- end from
			where
				-- where
				" . implode(' and ', $filterListLS) . "
				-- end where
			order by
				-- order by
				{$orderbyarchive}
				ESB.EvnStickBase_id desc
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}


	/**
	 *	Получение количества освобождений от работы с пересекаюзимися датами
	 */
	function countCrossDate($data) { 
		$query = "
			select
				count(EvnStickWorkRelease_id) as rec
			from v_EvnStickWorkRelease with (nolock)
			where EvnStickBase_id = :EvnStickBase_id
				and EvnStickWorkRelease_id != isnull(:EvnStickWorkRelease_id, 0)
				and (
			      (:EvnStickWorkRelease_begDT between EvnStickWorkRelease_begDT and EvnStickWorkRelease_endDT)
				  or (:EvnStickWorkRelease_endDT between EvnStickWorkRelease_begDT and EvnStickWorkRelease_endDT)
				  or (EvnStickWorkRelease_endDT < :EvnStickWorkRelease_endDT and EvnStickWorkRelease_begDT > :EvnStickWorkRelease_begDT)
				)
		";

		$queryParams = array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_begDT' => $data['EvnStickWorkRelease_begDate'],
			'EvnStickWorkRelease_endDT' => $data['EvnStickWorkRelease_endDate'],
			'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$res = $res->result('array');

			if ( count($res) > 0 ) {
				return $res[0]['rec'];
			}
			else {
				return false;
			}
		}
	}
	
	
	/**
	 * Берем количество дней, на которые уже выписал освобождение переданный врач
	 */
	function getDayReleaseCountByMP($data) {
		$params = array();
		$params['EvnStickBase_id'] = $data['EvnStickBase_id'];
		$params['MedPersonal_id'] = $data['MedPersonal_id'];
		$params['EvnStickWorkRelease_id'] = $data['EvnStickWorkRelease_id'];
		
		$sql = "
			select
				datediff(day, min(EvnStickWorkRelease_begDT), max(EvnStickWorkRelease_endDT)) as DayCount
			from v_EvnStickWorkRelease ESWR
			where EvnStickBase_id = :EvnStickBase_id
				and MedPersonal_id = :MedPersonal_id
				and MedPersonal2_id is null
				and MedPersonal3_id is null
				and not exists(
					select 1
					from v_EvnStickWorkRelease
					where
						EvnStickBase_id = :EvnStickBase_id
						and MedPersonal_id != :MedPersonal_id
						and MedPersonal2_id is null
						and MedPersonal3_id is null
				)
				and EvnStickWorkRelease_id != :EvnStickWorkRelease_id
		";

		$res = $this->db->query($sql,$params);
		if (is_object($res))
			$res = $res->result('array');
		if (count($res)>0)
		{
			return $res[0]['DayCount'];
		}
		else 
		{
			return false;
		}
	}

	/**
	 * Получение списка ЛВН с расчетом дней нетрудоспособности по уходу за ребенком
	 */
	function loadClosedEvnStickGrid($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			declare @cur_date date = dbo.tzGetDate()
			declare @beg_date date = str(year(@cur_date))+'-01-01'
			declare @end_date date = str(year(@cur_date))+'-12-31'
			declare @limit_date date = @end_date
			declare @birthday date = (
				select top 1 Person_BirthDay
				from v_PersonState with(nolock) where Person_id = :Person_id
			)
			declare @age int = dbo.Age(@birthday, @end_date)

			declare @is_invalid int = 0
			if exists(
				select * from v_PersonPrivilege with(nolock)
				where Person_id = :Person_id and PrivilegeType_Code = '84'
				and PersonPrivilege_begDate <= @end_date
				and (PersonPrivilege_endDate > @beg_date or PersonPrivilege_endDate is null)
			) set @is_invalid = 1

			if (@is_invalid = 1 and @age = 15) or (@is_invalid = 0 and @age = 7)
				set @limit_date = str(year(@cur_date))+'-'+str(month(@birthday))+'-'+str(day(@birthday))

			select
				-- select
				ES.EvnStick_id,
				case
				    when TAP.EvnPL_id is not null then 'ТАП'
				    when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
				    when KVS.EvnPS_id is not null then 'КВС'
				    else ''
				end as CardType,
				case
				    when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
				    when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
				    when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
				   else ''
				end as NumCard,
				ES.EvnStick_Num,
				Lpu.Lpu_Nick,
				ISNULL(MPFirst.Person_FIO, '') as MedPersonalFirst_Fio,
				ISNULL(MPLast.Person_FIO, '') as MedPersonalLast_Fio,
				convert(varchar(10), ESWRBDT.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWREDT.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				DATEDIFF(DAY, ESWRBDT.EvnStickWorkRelease_begDT, ESWREDT.EvnStickWorkRelease_endDT) + 1 as EvnStickWorkRelease_DaysCount
				-- end select
			from
				-- from
				v_EvnStickBase ESB with (nolock)
				inner join EvnClass ESC with (nolock) on ESC.EvnClass_id = ESB.EvnClass_id
                -- БЛ
                left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
				--ТАП или КВС
				inner join v_Evn E with(nolock) on E.Evn_id = ESB.EvnStickBase_mid
				left join v_EvnPL TAP with (nolock) on ESB.EvnStickBase_mid = TAP.EvnPL_id
				left join v_EvnPLStom TAPStom with (nolock) on ESB.EvnStickBase_mid = TAPStom.EvnPLStom_id
				left join v_EvnPS KVS with (nolock) on ESB.EvnStickBase_mid = KVS.EvnPS_id
				left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ESB.Lpu_id
				outer apply (
					select top 1
						MedPersonal_id,
						case
							when EvnStickWorkRelease_begDT < @beg_date then @beg_date
							else EvnStickWorkRelease_begDT
						end as EvnStickWorkRelease_begDT
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = ESB.EvnStickBase_id
					order by EvnStickWorkRelease_begDT
				) ESWRBDT
				outer apply (
					select top 1
						MedPersonal_id,
						case
							when EvnStickWorkRelease_endDT > @limit_date then @limit_date
							else EvnStickWorkRelease_endDT
						end as EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease with (nolock)
					where EvnStickBase_id = ESB.EvnStickBase_id
					order by EvnStickWorkRelease_endDT desc
				) ESWREDT
				outer apply (
					select top 1
						Person_FIO
					from v_MedPersonal MP with (nolock)
					where MP.MedPersonal_id = ESWRBDT.MedPersonal_id
				) MPFirst
				outer apply (
					select top 1
						Person_FIO
					from v_MedPersonal MPLast with (nolock)
					where MPLast.MedPersonal_id = ESB.MedPersonal_id
				) MPLast
				-- end from
			where
				E.Person_id = :Person_id
				and ESB.EvnStickBase_disDate >= @beg_date
				and ESB.EvnStickBase_disDate <= @end_date
				and ESWRBDT.EvnStickWorkRelease_begDT < @limit_date
				and exists(
					select t.EvnStickCarePerson_id
					from v_EvnStickCarePerson t with(nolock)
					where t.Evn_id = ES.EvnStick_id and t.Person_id = E.Person_id
				)
				and ESB.StickLeaveType_rid is not null
		";
		$response = $this->queryResult($query, $params);

		return $response;
	}

	/**
	 * Получение данных расчета дней нетрудоспособности по уходу за ребенком
	 */
	function getEvnStickWorkReleaseCalculation($data) {
		$params = array('Person_id' => $data['Person_id']);

		$filter = "";
		$filter2 = "";
		if (!empty($data['StickCause_id'])) {
			$filter .= " and SC.StickCause_id = :StickCause_id";
			$params['StickCause_id'] = $data['StickCause_id'];
		}

		if (!empty($data['exceptEvnStick_id'])) {
			$filter2 .= " and ESB.EvnStickBase_id != :exceptEvnStick_id";
			$params['exceptEvnStick_id'] = $data['exceptEvnStick_id'];
		}

		$LimitDaysCountQuery = "
			select top 1
				case
					when dbo.Age(PS.Person_BirthDay, @cur_date) < 7 and StickCause_SysNick in ('uhod','uhodnoreb') then 60
					when dbo.Age(PS.Person_BirthDay, @cur_date) < 7 and StickCause_SysNick like 'uhodreb' then 90
					when dbo.Age(PS.Person_BirthDay, @cur_date) < 15 and StickCause_SysNick like 'rebinv' then 120
					else 15
				end as LimitDaysCount
			from
				v_EvnStick ES with(nolock)
				inner join v_Evn E with(nolock) on E.Evn_id = ES.EvnStick_mid
				inner join v_StickCause SC with(nolock) on SC.StickCause_id = ES.StickCause_id
			where
				E.Person_id = PS.Person_id
				and exists(
					select t.EvnStickCarePerson_id
					from v_EvnStickCarePerson t with(nolock)
					where t.Evn_id = ES.EvnStick_id and t.Person_id = E.Person_id
				)
				{$filter}
			order by LimitDaysCount desc
		";

		$query = "
			declare @cur_date date = dbo.tzGetDate()
			declare @beg_date date = str(year(@cur_date))+'-01-01'
			declare @end_date date = str(year(@cur_date))+'-12-31'
			declare @limit_date date = @end_date
			declare @birthday date = (
				select top 1 Person_BirthDay
				from v_PersonState with(nolock) where Person_id = :Person_id
			)
			declare @age int = dbo.Age(@birthday, @end_date)

			declare @is_invalid int = 0
			if exists(
				select * from v_PersonPrivilege with(nolock)
				where Person_id = :Person_id and PrivilegeType_Code = '84'
				and PersonPrivilege_begDate <= @end_date
				and (PersonPrivilege_endDate > @beg_date or PersonPrivilege_endDate is null)
			) set @is_invalid = 1

			if (@is_invalid = 1 and @age = 15) or (@is_invalid = 0 and @age = 7)
				set @limit_date = str(year(@cur_date))+'-'+str(month(@birthday))+'-'+str(day(@birthday))

			select top 1
				RTRIM(PS.Person_SurName)+' '+RTRIM(PS.Person_FirName)+RTRIM(' '+ISNULL(PS.Person_SecName,'')) as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), @beg_date, 120) as beg_date,
				convert(varchar(10), @end_date, 120) as end_date,
				convert(varchar(10), @limit_date, 120) as limit_date,
				isnull(LDC.LimitDaysCount, 15) as LimitDaysCount
			from
				v_PersonState PS with(nolock)
				outer apply ({$LimitDaysCountQuery}) LDC
			where
				PS.Person_id = :Person_id
		";
		$resp1 = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp1)) {
			return $this->createError('', 'Ошибка при получении данных');
		}

		$params['beg_date'] = $resp1[0]['beg_date'];
		$params['end_date'] = $resp1[0]['end_date'];
		$params['limit_date'] = $resp1[0]['limit_date'];

		$query = "
			declare @beg_date date = :beg_date
			declare @end_date date = :end_date
			declare @limit_date date = :limit_date

			select top 1
				isnull(SUM(DATEDIFF(DAY, BWR.beg_date, EWR.end_date) + 1),0) as SumDaysCount
			from
				v_EvnStick ES with(nolock)
				left join v_Evn E with(nolock) on E.Evn_id = ES.EvnStick_mid
				inner join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				outer apply (
					select top 1
						case
							when EvnStickWorkRelease_begDT < @beg_date then @beg_date
							else EvnStickWorkRelease_begDT
						end as beg_date,
						EvnStickWorkRelease_begDT
					from v_EvnStickWorkRelease t with (nolock)
					where EvnStickBase_id = ES.EvnStick_id
					order by EvnStickWorkRelease_begDT
				) BWR
				outer apply (
					select top 1
						case
							when EvnStickWorkRelease_endDT > @limit_date then @limit_date
							else EvnStickWorkRelease_endDT
						end as end_date,
						EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease t with (nolock)
					where EvnStickBase_id = ES.EvnStick_id
					order by EvnStickWorkRelease_endDT desc
				) EWR
			where
				E.Person_id = :Person_id
				and ESB.EvnStickBase_disDate >= @beg_date
				and ESB.EvnStickBase_disDate <= @end_date
				and BWR.EvnStickWorkRelease_begDT < @limit_date
				and exists(
					select t.EvnStickCarePerson_id
					from v_EvnStickCarePerson t with(nolock)
					where t.Evn_id = ES.EvnStick_id and t.Person_id = E.Person_id
				)
				{$filter2}
				and ESB.StickLeaveType_rid is not null
		";
		$resp2 = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp2)) {
			return $this->createError('', 'Ошибка при получении данных');
		}

		return array(
			'Person_Fio' => $resp1[0]['Person_Fio'],
			'Person_BirthDay' => $resp1[0]['Person_BirthDay'],
			'LimitDaysCount' => $resp1[0]['LimitDaysCount'],
			'SumDaysCount' => $resp2[0]['SumDaysCount'],
			'success' => true
		);
	}

	/**
	 * Получение списка случаев лечения для АРМа регистратора ЛВН
	 */
	function loadEvnStickPids($data){
		if($data['EvnStick_pidType_id'] == 1) //ТАП
		{
			$EvnStick_pidType = 'EvnPL';
			$Stick_pidType_Name = "'ТАП'";
            $ES_join = "";
            $mp_join = "left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ESP.MedPersonal_id and MP.Lpu_id = ESP.Lpu_id";
		}
		else //КВС
		{
			$EvnStick_pidType = 'EvnPS';
			$Stick_pidType_Name = "'КВС'";
            $ES_join = "
				left join v_EvnSection MP_Section with (nolock) on MP_Section.EvnSection_pid = ESP.EvnPS_id
					and MP_Section.EvnSection_Index = MP_Section.EvnSection_Count - 1
			";
            $mp_join = "
				outer apply(
					select top 1
						MedPersonal_id,
						Person_SurName,
						Person_FirName,
						Person_SecName
					from v_MedPersonal with(nolock)
					where MedPersonal_id = ISNULL(MP_Section.MedPersonal_id, ESP.MedPersonal_pid)
				) MP
            ";
		}

		$params = array();
		$and_str = "";
		$joinRegistryESStorage = '';
		$params['Lpu_id'] = $data['Lpu_id'];
		if(isset($data['EvnStick_pidIsFinish'])){
			if($data['EvnStick_pidType_id'] == 1)
			{
				$and_str .= " and ESP.EvnPL_IsFinish = :EvnStick_pidIsFinish";
				$params['EvnStick_pidIsFinish'] = $data['EvnStick_pidIsFinish'];
			}
			else
			{
				if($data['EvnStick_pidIsFinish'] == 1)
				{
					$and_str .= " and ESP.EvnPS_disDT is not null";
				}
				else
				{
					$and_str .= " and ESP.EvnPS_disDT is null";
				}
			}
		}
		if(isset($data['Person_Surname']))
		{
			$params['Person_Surname'] = $data['Person_Surname'].'%';
			$and_str .= " and PS.Person_Surname like :Person_Surname";
		}
		if(isset($data['Person_Firname']))
		{
			$params['Person_Firname'] = $data['Person_Firname'].'%';
			$and_str .= " and PS.Person_Firname like :Person_Firname";
		}
		if(isset($data['Person_Secname']))
		{
			$params['Person_Secname'] = $data['Person_Secname'].'%';
			$and_str .= " and PS.Person_Secname like :Person_Secname";
		}

		if ( isset($data['Person_Birthday']) )
		{
			$params['Person_Birthday'] = $data['Person_Birthday'];
			$and_str .= " and PS.Person_Birthday = :Person_Birthday";
		}

		if( isset($data['EvnStick_pidNum']))
		{
			$params['EvnStick_pidNum'] = $data['EvnStick_pidNum'];
			$and_str .= " and ESP.{$EvnStick_pidType}_NumCard = :EvnStick_pidNum";
		}

		if ( !empty($data['EvnStick_pidDate'][0]) ) {
			$and_str .= " and cast(ESP.{$EvnStick_pidType}_setDT as date) >= cast(:EvnStick_pidDate_0 as datetime)";
			$params['EvnStick_pidDate_0'] = $data['EvnStick_pidDate'][0];
		}

		if( !empty($data['EvnStick_pidDate'][1]) ) {
			$and_str .= " and cast(ESP.{$EvnStick_pidType}_setDT as date) <= cast(:EvnStick_pidDate_1 as datetime)";
			$params['EvnStick_pidDate_1'] = $data['EvnStick_pidDate'][1];
		}

		if(isset($data['LpuSection_id']))
		{
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$and_str .= " and LS.LpuSection_id = :LpuSection_id";
		}

        if(isset($data['CurLpuSection_id']) && $data['CurLpuSection_id'] != '')
        {
            $params['CurLpuSection_id'] = $data['CurLpuSection_id'];
            $and_str .= " and LS.LpuSection_id = :CurLpuSection_id";
        }

        if(isset($data['CurLpuBuilding_id']) && $data['CurLpuBuilding_id'] != '')
        {
            $params['CurLpuBuilding_id'] = $data['CurLpuBuilding_id'];
            $and_str .= " and LS.LpuBuilding_id = :CurLpuBuilding_id";
        }
        if(isset($data['CurLpuUnit_id']) && $data['CurLpuUnit_id'] != '')
        {
            $params['CurLpuUnit_id'] = $data['CurLpuUnit_id'];
            $and_str .= " and LS.LpuUnit_id = :CurLpuUnit_id";
        }

		if(isset($data['Polis_Ser']))
		{
			$params['Polis_Ser'] = $data['Polis_Ser'];
			$and_str .= " and PS.Polis_Ser = :Polis_Ser";
		}

		if(isset($data['Polis_Num']))
		{
			$params['Polis_Num'] = $data['Polis_Num'];
			$and_str .= " and PS.Polis_Num = :Polis_Num";
		}
		if(isset($data['Person_Code']))
		{
			$params['Person_EdNum'] = $data['Person_Code'];
			$and_str .= " and PS.Person_EdNum = :Person_EdNum";
		}
		if (!empty($data['LvnType'])) {
			switch ($data['LvnType']) {
				case 1:
					$joinRegistryESStorage = 'LEFT JOIN v_RegistryESStorage RESS on RESS.EvnStickBase_id = ESB.EvnStickBase_id';
					$and_str .= ' and RESS.EvnStickBase_id IS NULL';
					$and_str .= ' and ESB.EvnStickBase_id IS NOT NULL';
					$and_str .= ' and ISNULL(ESB.EvnStickBase_IsFSS, 1) = 1';
					break;
				case 2:
					$joinRegistryESStorage = 'LEFT JOIN v_RegistryESStorage RESS on RESS.EvnStickBase_id = ESB.EvnStickBase_id';
					$and_str .= ' and (RESS.EvnStickBase_id IS NOT NULL OR ESB.EvnStickBase_IsFSS = 2)';
					break;
			}
		}

		$query = "

			select
			-- select
				ESP.{$EvnStick_pidType}_id as Evn_id,
				{$Stick_pidType_Name} as EvnStick_pidType,
				ESP.{$EvnStick_pidType}_NumCard as EvnStick_pidNum,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_SecName,'') as Person_FIO,
				ISNULL(MP.Person_SurName,'') + ' ' + ISNULL(MP.Person_FirName,'') + ' ' + ISNULL(MP.Person_SecName,'') as MedPersonal_FIO,
				convert(varchar(10), ESP.{$EvnStick_pidType}_setDate, 104) as EvnStick_pidBegDate,
				convert(varchar(10), ESP.{$EvnStick_pidType}_disDate, 104) as EvnStick_pidEndDate,
				ISNULL(LS.LpuSection_FullName,'') as LpuSection_Name,
				case when ESB.EvnStickBase_id is null then 'false' else 'true' end as HasEvnStick,
				isnull(RTRIM(PJ.Org_id), '') as JobOrg_id,
				PS.Person_id,
				ESP.PersonEvn_id,
				ESP.Server_id,
				isnull(convert(varchar(10), PS.Person_BirthDay, 104), '') as Person_Birthday,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				ISNULL(PS.Person_SurName,'') as Person_SurName,
				ISNULL(PS.Person_FirName,'') as Person_FirName,
				ISNULL(PS.Person_SecName,'') as Person_SecName
			-- end select
			from
			 -- from
				v_{$EvnStick_pidType} ESP WITH (NOLOCK)
                outer apply (
                    select top 1
                    	EvnStickBase_id,
                    	EvnStickBase_IsFSS
                    from v_EvnStickBase WITH (NOLOCK)
                    where
                        EvnStickBase_id in (
                            select EvnStickBase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = ESP.{$EvnStick_pidType}_id
                            union all
                            select EvnStickBase_id from v_EvnStickBase with (nolock) where EvnStickBase_rid =  ESP.{$EvnStick_pidType}_id
                            union all
                            select Evn_lid from EvnLink with (nolock) where Evn_id =  ESP.{$EvnStick_pidType}_id
                    )
                ) as ESB
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = ESP.Person_id
				--left join StickType ST WITH (NOLOCK) on ST.StickType_id = ES.StickType_id
				{$joinRegistryESStorage}
				{$ES_join}
				{$mp_join}
				left join v_LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = ESP.LpuSection_id
				left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
				left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
				left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
			-- end from
			WHERE
				-- where
				ESP.Lpu_id = :Lpu_id
				{$and_str}
				-- end where
			ORDER BY
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
		";

		//echo getDebugSQL($query,$params);die;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка ЛВН для выбранного ТАП или КВС для АРМа регистратора ЛВН
	 */
	function loadEvnStickForARM($data){
		$params = array();
		$params['Evn_id'] = $data['Evn_id'];
		$params['Lpu_id'] = $data['session']['lpu_id'];
		$params['Org_id'] = $data['session']['org_id'];
		$params['MedPersonal_id'] = !empty($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id']: 0;
		/*$query = "
			select
				ES.EvnStick_id,
				convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate,
				ISNULL(ST.StickType_Name,'') as StickType_Name,
				ISNULL(ES.EvnStick_Ser,'') + ISNULL(ES.EvnStick_Num,'') as EvnStick_Num,
				ISNULL(SO.StickOrder_Name,'') as StickOrder_Name,
				ISNULL(SWT.StickWorkType_Name,'') as StickWorkType_Name,
				ISNULL(EST.EvnStatus_Name, '') as EvnStatus_Name,
				ISNULL(Org.Org_Name, '') as OrgJob_Name
			from v_EvnStick ES
			left join v_StickType ST with(nolock) on ST.StickType_id = ES.StickType_id
			left join v_StickOrder SO with(nolock) on SO.StickOrder_id = ES.StickOrder_id
			left join v_StickWorkType SWT with(nolock) on SWT.StickWorkType_id = ES.StickWorkType_id
			left join v_EvnStatus EST (nolock) on EST.EvnStatus_id = ES.EvnStatus_id
			left join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
			left join Org with (nolock) on Org.Org_id = ESB.Org_id
			where ES.EvnStick_pid = :Evn_id
		";*/
		$accessType = $this->getEvnStickAccessType($data);
        $query = "
        	select
        		{$accessType}
				ESB.EvnStickBase_id as EvnStick_id,
				convert(varchar(10), ISNULL(ESBD.EvnStickBase_setDT, ESB.EvnStickBase_setDT), 104) as EvnStick_setDate,
				case
					when EC.EvnClass_SysNick in ('EvnStick', 'EvnStickDop') then 'ЛВН'
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 'Справка учащегося'
					else ''
				end as StickType_Name,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				RTRIM(ISNULL(SO.StickOrder_Name, '')) as StickOrder_Name,
				ISNULL(SWT.StickWorkType_Name,'') as StickWorkType_Name,
				ISNULL(EST.EvnStatus_Name, '') as EvnStatus_Name,
				ISNULL(Org.Org_Name, '') as OrgJob_Name,
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType,
				case 
					when RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 'Электронный'
					else 'На бланке'
				end as EvnStatus_Name,
				ESB.EvnStickBase_IsDelQueue as EvnStick_IsDelQueue
			from v_EvnStickBase ESB WITH (NOLOCK)
			inner join EvnClass EC WITH (NOLOCK) on EC.EvnClass_id = ESB.EvnClass_id
			left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
			left join v_RegistryESStorage RESS with (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id
			left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
					and EC.EvnClass_SysNick = 'EvnStickDop'
			left join v_EvnStick ES WITH (NOLOCK) on ES.EvnStick_id = ESB.EvnStickBase_id
			left join v_StickType ST WITH (NOLOCK) on ST.StickType_id = ES.StickType_id
			left join StickOrder SO WITH (NOLOCK) on SO.StickOrder_id = ISNULL(ESBD.StickOrder_id, ESB.StickOrder_id)
			left join StickWorkType SWT WITH (NOLOCK) on SWT.StickWorkType_id = ESB.StickWorkType_id
			left join v_EvnStatus EST (nolock) on EST.EvnStatus_id = ES.EvnStatus_id
			--left join v_EvnStickBase ESB with(nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
			left join Org with (nolock) on Org.Org_id = ISNULL(ESB.Org_id,ESBD.Org_id)
			where
			ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :Evn_id
					union all
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_rid = :Evn_id
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :Evn_id
				)
        ";
        //echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			return $result->result('array');
		}
		else
			return false;

	}

    /**
     * Получаем уровень, в которм лежит наша служба
     */
    function getMedServiceParent($data){
        $params = array();
        $params['MedService_id'] = $data['MedService_id'];
        $query = "
            select
                MS.LpuSection_id,
                MS.LpuBuilding_id,
                MS.LpuUnit_id
            from v_MedService MS with (nolock)
            where MS.MedService_id = :MedService_id
        ";
        //echo getDebugSQL($query,$params);die;
        $result = $this->db->query($query,$params);
        if(is_object($result))
        {
            return $result->result('array');
        }
        else
            return false;
    }

	/**
	 * Проверка инфы о врачах в освобождениях от работы (https://redmine.swan.perm.ru/issues/83780)
	 */
	function WorkReleaseMedStaffFactCheck($data) {
		$params = array(
			'EvnStickBase_id' => $data['EvnStickBase_id']
		);
		$query = "
			SELECT TOP 1
				ESWR.EvnStickWorkRelease_id,
				convert(varchar(10), ESWR.evnStickWorkRelease_begDT, 104) as evnStickWorkRelease_begDT,
				convert(varchar(10), ESWR.evnStickWorkRelease_endDT, 104) as evnStickWorkRelease_endDT
			FROM
				v_EvnStickWorkRelease ESWR WITH (nolock)
				inner join v_EvnStickBase esb with (nolock) on esb.EvnStickBase_id = eswr.EvnStickBase_id
				INNER JOIN persis.post P WITH (nolock) ON P.id = ESWR.Post_id
			WHERE
				ESWR.EvnStickBase_id = :EvnStickBase_id
				AND ISNULL(ESWR.EvnStickWorkRelease_IsPredVK,1) = 2
				AND P.name LIKE '%фельдшер%'
				AND ESWR.MedStaffFact2_id IS NULL
				and ISNULL(ESB.EvnStickBase_IsFSS, 1) = 1
				and not exists(
					select top 1
						RegistryESStorage_id
					from
						v_RegistryESStorage with (nolock)
					where
						EvnStickBase_id = ESB.EvnStickBase_id
				)
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0){
				return $result;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

    /**
     * Проверка хэша в подписи
     */
    function checkSignatureHash($data) {
        $resp_sign = $this->queryResult("select Signatures_Token, Signatures_Hash, SignaturesStatus_id from Signatures with (nolock) where Signatures_id = :Signatures_id", array(
            'Signatures_id' => $data['Signatures_id']
        ));
        if (!empty($resp_sign[0]['Signatures_Hash'])) {
            if ($resp_sign[0]['SignaturesStatus_id'] == 3) {
                return array('Error_Msg' => ''); // если уже неактуальная, то не проверяем
            }
            $data['SignedToken'] = $resp_sign[0]['Signatures_Token'];
            $data['needHash'] = true; // нужен хэш
            $signHash = $this->getWorkReleaseSslHash($data);
            if (!empty($signHash['Hash'])) {
				$resp_sign[0]['Signatures_Hash'] = preg_replace('/\s/ui', '', $resp_sign[0]['Signatures_Hash']);
                if ($resp_sign[0]['Signatures_Hash'] != $signHash['Hash']) {
                    // подпись стала неакутальна!
                    $this->setSignStatus(array(
                        'Signatures_id' => $data['Signatures_id'],
                        'SignaturesStatus_id' => 3
                    ));
                }
            } else {
                if (!empty($signHash['Error_Msg'])) {
                    return array('Error_Msg' => 'Ошибка проверки актуальности подписи: ' . $signHash['Error_Msg']);
                } else {
                    return array('Error_Msg' => 'Ошибка проверки актуальности подписи');
                }
            }
        }

        return array('Error_Msg' => '');
    }
	
	/**
	* Хэш для освобождения
	*/
	function getWorkReleaseSslHash($data) {
		$xml = false;
		$this->load->library('parser');

		if (in_array($data['SignObject'], array('irr', 'leave'))) {
			// Evn_id = ид ЛВН
			$EvnStick_id = $data['Evn_id'];
		} else {
			// Evn_id = ид освобождения, надо получить ид ЛВН
			$EvnStick_id = $this->getFirstResultFromQuery("select EvnStickBase_id from v_EvnStickWorkRelease (nolock) where EvnStickWorkRelease_id = :EvnStickWorkRelease_id", array(
				'EvnStickWorkRelease_id' => $data['Evn_id']
			));
		}

		if (empty($EvnStick_id)) {
			return array('Error_Msg' => 'Ошибка получения идентификатора ЛВН');
		}

		$prequery = "
			set nocount on
			
			declare @result table (
				Evn_id bigint,
				EvnStick_Num varchar(MAX),
				StickIrregularity_Code varchar(MAX),
				EvnStick_irrDT datetime,
				InvalidGroupType_id bigint,
				StickLeaveType_Code varchar(MAX),
				EvnStick_disDate datetime,
				EvnStick_returnDate datetime,
				EvnStick_NumNext varchar(MAX),
				FirstEvnStickWorkRelease_id bigint,
				FirstPostVK_Code varchar(MAX),
				FirstMedPersonalVK_Fin varchar(MAX),
				FirstEvnStickWorkRelease_begDT datetime,
				FirstEvnStickWorkRelease_endDT datetime,
				FirstPost_Code varchar(MAX),
				FirstMedPersonal_Fin varchar(MAX),
				SecondEvnStickWorkRelease_id bigint,
				SecondPostVK_Code varchar(MAX),
				SecondMedPersonalVK_Fin varchar(MAX),
				SecondEvnStickWorkRelease_begDT datetime,
				SecondEvnStickWorkRelease_endDT datetime,
				SecondPost_Code varchar(MAX),
				SecondMedPersonal_Fin varchar(MAX),
				ThirdEvnStickWorkRelease_id bigint,
				ThirdPostVK_Code varchar(MAX),
				ThirdMedPersonalVK_Fin varchar(MAX),
				ThirdEvnStickWorkRelease_begDT datetime,
				ThirdEvnStickWorkRelease_endDT datetime,
				ThirdPost_Code varchar(MAX),
				ThirdMedPersonal_Fin varchar(MAX)
			)
			
			insert into @result 
			exec dbo.p_Registry_EvnStick @EvnStick_id = :EvnStick_id
			
			set nocount off
		";

		$EvnStickWorkRelease_Num = 1;
		$EvnStick_Num = '';

		switch($data['SignObject']) {
			case 'irr':
				$query = "
					{$prequery}
					
                    SELECT TOP 1
                        RESD.EvnStick_Num,
                        RESD.StickIrregularity_Code as 'HOSPITAL_BREACH_CODE',
                        convert(varchar(10),RESD.EvnStick_irrDT,20) as 'HOSPITAL_BREACH_DT'
                    from
                        @result RESD
				";
				$resp_xml = $this->queryResult($query, array(
					'EvnStick_id' => $EvnStick_id
				));
				if (!empty($resp_xml[0])) {
                    $EvnStick_Num = $resp_xml[0]['EvnStick_Num'];
                    $template = 'export_registry_es_hb';
                    $xml = $this->parser->parse('export_xml/'.$template, $resp_xml[0], true);
				}
				break;

			case 'leave':
				$query = "
					{$prequery}
					
                    SELECT TOP 1
                        RESD.EvnStick_Num,
                        RESD.StickLeaveType_Code as 'MSE_RESULT',
                        ISNULL(convert(varchar(10),RESD.EvnStick_disDate,20), '###') as 'OTHER_STATE_DT',
                        ISNULL(convert(varchar(10),RESD.EvnStick_returnDate,20), '###') as 'RETURN_DATE_LPU',
                        RESD.EvnStick_NumNext as 'NEXT_LN_CODE'
                    from
                        @result RESD
				";
				$resp_xml = $this->queryResult($query, array(
					'EvnStick_id' => $EvnStick_id
				));
                if (!empty($resp_xml[0])) {
                    $EvnStick_Num = $resp_xml[0]['EvnStick_Num'];
                    $template = 'export_registry_es_lr';
                    $xml = $this->parser->parse('export_xml/'.$template, $resp_xml[0], true);
				}
				break;

			case 'VK':
				$query = "
					{$prequery}
					
					SELECT TOP 1
					    case
					        when RESD.FirstEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 1
					        when RESD.SecondEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 2
					        when RESD.ThirdEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 3
					    end as num,
					    RESD.EvnStick_Num,
						RESD.FirstPostVK_Code as 'TREAT1_CHAIRMAN_ROLE',
						RESD.FirstMedPersonalVK_Fin as 'TREAT1_CHAIRMAN',
						convert(varchar(10),RESD.FirstEvnStickWorkRelease_begDT,20) as 'TREAT1_DT1',
						convert(varchar(10),RESD.FirstEvnStickWorkRelease_endDT,20) as 'TREAT1_DT2',
						RESD.FirstPost_Code as 'TREAT1_DOCTOR_ROLE',
						RESD.FirstMedPersonal_Fin as 'TREAT1_DOCTOR',
						RESD.SecondPostVK_Code as 'TREAT2_CHAIRMAN_ROLE',
						RESD.SecondMedPersonalVK_Fin as 'TREAT2_CHAIRMAN',
						convert(varchar(10),RESD.SecondEvnStickWorkRelease_begDT,20) as 'TREAT2_DT1',
						convert(varchar(10),RESD.SecondEvnStickWorkRelease_endDT,20) as 'TREAT2_DT2',
						RESD.SecondPost_Code as 'TREAT2_DOCTOR_ROLE',
						RESD.SecondMedPersonal_Fin as 'TREAT2_DOCTOR',
						RESD.ThirdPostVK_Code as 'TREAT3_CHAIRMAN_ROLE',
						RESD.ThirdMedPersonalVK_Fin as 'TREAT3_CHAIRMAN',
						convert(varchar(10),RESD.ThirdEvnStickWorkRelease_begDT,20) as 'TREAT3_DT1',
						convert(varchar(10),RESD.ThirdEvnStickWorkRelease_endDT,20) as 'TREAT3_DT2',
						RESD.ThirdPost_Code as 'TREAT3_DOCTOR_ROLE',
						RESD.ThirdMedPersonal_Fin as 'TREAT3_DOCTOR'
					from
						@result RESD
				";
				$resp_xml = $this->queryResult($query, array(
					'EvnStick_id' => $EvnStick_id,
					'EvnStickWorkRelease_id' => $data['Evn_id']
				));

				if (!empty($resp_xml[0])) {
                    $EvnStickWorkRelease_Num = $resp_xml[0]['num'];
                    $EvnStick_Num = $resp_xml[0]['EvnStick_Num'];
					$template = 'export_registry_es_tfp';
					$xml = $this->parser->parse('export_xml/'.$template, array(
					    'doc_sign' => ' wsu:Id="ELN_' . $EvnStick_Num . '_' . (2 + $EvnStickWorkRelease_Num) . '_doc"',
                        'TREAT_CHAIRMAN_ROLE' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_CHAIRMAN_ROLE'],
                        'TREAT_CHAIRMAN' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_CHAIRMAN'],
                        'TREAT_DT1' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DT1'],
                        'TREAT_DT2' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DT2'],
                        'TREAT_DOCTOR_ROLE' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DOCTOR_ROLE'],
                        'TREAT_DOCTOR' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DOCTOR']
                    ), true);
				}
				break;

			case 'MP':
				$query = "
					{$prequery}
					
                    SELECT TOP 1
                        case
                            when RESD.FirstEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 1
                            when RESD.SecondEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 2
                            when RESD.ThirdEvnStickWorkRelease_id = :EvnStickWorkRelease_id then 3
                        end as num,
                        RESD.EvnStick_Num,
                        convert(varchar(10),RESD.FirstEvnStickWorkRelease_begDT,20) as 'TREAT1_DT1',
                        convert(varchar(10),RESD.FirstEvnStickWorkRelease_endDT,20) as 'TREAT1_DT2',
                        RESD.FirstPost_Code as 'TREAT1_DOCTOR_ROLE',
                        RESD.FirstMedPersonal_Fin as 'TREAT1_DOCTOR',
                        convert(varchar(10),RESD.SecondEvnStickWorkRelease_begDT,20) as 'TREAT2_DT1',
                        convert(varchar(10),RESD.SecondEvnStickWorkRelease_endDT,20) as 'TREAT2_DT2',
                        RESD.SecondPost_Code as 'TREAT2_DOCTOR_ROLE',
                        RESD.SecondMedPersonal_Fin as 'TREAT2_DOCTOR',
                        convert(varchar(10),RESD.ThirdEvnStickWorkRelease_begDT,20) as 'TREAT3_DT1',
                        convert(varchar(10),RESD.ThirdEvnStickWorkRelease_endDT,20) as 'TREAT3_DT2',
                        RESD.ThirdPost_Code as 'TREAT3_DOCTOR_ROLE',
                        RESD.ThirdMedPersonal_Fin as 'TREAT3_DOCTOR'
                    from
                        @result RESD
				";
				$resp_xml = $this->queryResult($query, array(
					'EvnStick_id' => $EvnStick_id,
					'EvnStickWorkRelease_id' => $data['Evn_id']
				));
				if (!empty($resp_xml[0])) {
                    $EvnStickWorkRelease_Num = $resp_xml[0]['num'];
                    $EvnStick_Num = $resp_xml[0]['EvnStick_Num'];
                    $template = 'export_registry_es_tp';
                    $xml = $this->parser->parse('export_xml/' . $template, array(
                        'TREAT_DT1' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DT1'],
                        'TREAT_DT2' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DT2'],
                        'TREAT_DOCTOR_ROLE' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DOCTOR_ROLE'],
                        'TREAT_DOCTOR' => $resp_xml[0]['TREAT' . $EvnStickWorkRelease_Num . '_DOCTOR']
                    ), true);
				}
				break;
		}

		if (!$xml) {
			return false;
		}

		$tag_name = "";
		$block_id = "";
		switch ($data['SignObject']) {
			case 'irr':
				$data['type'] = '_1_doc';
				$block_id = 'ELN_' . $EvnStick_Num . $data['type'];
				$data['xml'] = '<HOSPITAL_BREACH wsu:Id="' . $block_id . '">' . $xml . '</HOSPITAL_BREACH>';
				$SignObject = 'Signatures_iid';
				$object = 'EvnStick';
				$tag_name = "HOSPITAL_BREACH";
				break;
			case 'leave':
				$data['type'] = '_2_doc';
				$block_id = 'ELN_' . $EvnStick_Num . $data['type'];
				$data['xml'] = '<LN_RESULT wsu:Id="' . $block_id . '">' . $xml . '</LN_RESULT>';
				$SignObject = 'Signatures_id';
				$object = 'EvnStick';
				$tag_name = "LN_RESULT";
				break;
			case 'VK':
				$data['type'] = '_' . (2 + $EvnStickWorkRelease_Num) . '_vk';
				$block_id = 'ELN_' . $EvnStick_Num . $data['type'];
				$data['xml'] = '<TREAT_FULL_PERIOD wsu:Id="' . $block_id . '">' . $xml . '</TREAT_FULL_PERIOD>';
				$SignObject = 'Signatures_wid';
				$object = 'EvnStickWorkRelease';
				$tag_name = "TREAT_FULL_PERIOD";
				break;
			case 'MP':
				$data['type'] = '_' . (2 + $EvnStickWorkRelease_Num) . '_doc';
				$block_id = 'ELN_' . $EvnStick_Num . $data['type'];
				$data['xml'] = '<TREAT_PERIOD wsu:Id="' . $block_id . '">' . $xml . '</TREAT_PERIOD>';
				$SignObject = 'Signatures_mid';
				$object = 'EvnStickWorkRelease';
				$tag_name = "TREAT_PERIOD";
				break;
		}

		if ($object == 'EvnStick') {
			// может быть по своеместительству
			$resp = $this->queryResult("
				select EvnClass_SysNick from v_Evn with (nolock) where Evn_id = :Evn_id
			", array(
				'Evn_id' => $data['Evn_id']
			));
			if (!empty($resp[0]['EvnClass_SysNick'])) {
				$object = $resp[0]['EvnClass_SysNick'];
			}
		}

		// удаляем пустые теги
		$data['xml'] = preg_replace('/<\w*><\/\w*>/u', '', $data['xml']);
		// удаляем родительские пустые теги
		$data['xml'] = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $data['xml']);
		// удаляем родительские пустые теги
		$data['xml'] = preg_replace('/<\w*>[\s]*<\/\w*>/u', '', $data['xml']);

		// тэги которые могут быть пустыми, но должны присутствовать
		$data['xml'] = preg_replace('/<(\w*)>###<\/\w*>/u', '<$1 xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />', $data['xml']);

		// добавляем нэймспейс fil: ко всем тегам
		$data['xml'] = preg_replace('/<([^\/]+?.*?)>/u', '<fil:$1>', $data['xml']);
		$data['xml'] = preg_replace('/<\/(.*?)>/u', '</fil:$1>', $data['xml']);

		$xml = "<?xml version='1.0' encoding='UTF-8' standalone='no'?>\n";
		$xml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:fil="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><soapenv:Header>';

		$data['DigestValue'] = '';
		$data['SignatureValue'] = '';
		if (!empty($data['needSignatures'])) {
			$query = "
				SELECT TOP 1
					S.Signatures_id,
					S.Signatures_Version,
					S.Signatures_Hash,
					S.Signatures_SignedData,
					S.Signatures_Token
				FROM v_{$object} ESWR WITH (nolock)
				INNER JOIN v_Signatures S WITH (nolock) on ESWR.{$SignObject} = S.Signatures_id
				WHERE ESWR.{$object}_id = :Evn_id
			";

			$signatures = $this->getFirstRowFromQuery($query, array(
				'Evn_id' => $data['Evn_id']
			));

			if (!isset($signatures['Signatures_id'])) {
				return false;
			}

			$data['SignedToken'] = $signatures['Signatures_Token'];
			$data['DigestValue'] = $signatures['Signatures_Hash'];
			$data['SignatureValue'] = $signatures['Signatures_SignedData'];
		}

		$this->load->helper('openssl');
		$certAlgo = getCertificateAlgo($data['SignedToken']);

		$xml .= $this->parser->parse('export_xml/xml_signature', array(
			'id' => 'http://eln.fss.ru/actor/doc/' . $EvnStick_Num.$data['type'],
			'block' => $block_id,
			'BinarySecurityToken' => $data['SignedToken'],
			'DigestValue' => $data['DigestValue'],
			'SignatureValue' => $data['SignatureValue'],
			'signatureMethod' => $certAlgo['signatureMethod'],
			'digestMethod' => $certAlgo['digestMethod']
		), true);

		$xml .= '</soapenv:Header>';
		$xml .= '<soapenv:Body>';
		$xml .= '<fil:ROWSET>';
		$xml .= $data['xml'];
		$xml .= '</fil:ROWSET>';
		$xml .= '</soapenv:Body>';
		$xml .= '</soapenv:Envelope>';

		if (!empty($_REQUEST['getDebug'])) {
			echo "<textarea>$xml</textarea>";
		}

		if (!empty($data['needHash']) || !empty($data['needVerifyOpenSSL'])) {
			$doc = new DOMDocument();
			$doc->loadXML($xml);
			$toHash = $doc->getElementsByTagName($tag_name)->item(0)->C14N(false, false);
			// считаем хэш
			$cryptoProHash = getCryptCpHash($toHash, $data['SignedToken']);
			// 2. засовываем хэш в DigestValue
			$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue = implode("\n", str_split($cryptoProHash, 64));
			// 3. считаем хэш по SignedInfo
			$toSign = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(false, false);
			$Base64ToSign = base64_encode($toSign);
			
			if (!empty($data['needVerifyOpenSSL'])) {
				if (!empty($signatures['Signatures_id'])) {
					$verifyStatus = "not valid";

					$this->load->helper('openssl');
					$verified = checkSignature($data['SignedToken'], $toSign, base64_decode($data['SignatureValue']));
					if ($verified) {
						$verifyStatus = 'valid';
					}

					return array('Error_Msg' => '', 'verifyStatus' => $verifyStatus, 'Signatures_id' => $signatures['Signatures_id']);
				} else {
					return array('Error_Msg' => 'Не найдена подпись документа');
				}
			}

			$resp = array('success' => true, 'xml' => $xml, 'Base64ToSign' => $Base64ToSign, 'Hash' => $cryptoProHash);
		} else {
			$resp = array('success' => true, 'xml' => $xml);
		}

		if (!empty($signatures['Signatures_id'])) {
			$resp['Signatures_id'] = $signatures['Signatures_id'];
		}

		return $resp;
	}
	
	/**
	* Подписание освобождения
	*/
	function signWorkRelease ($data) {
		switch ($data['SignObject']) {
			case 'VK':
				$SignObject = 'Signatures_wid';
				$object = 'EvnStickWorkRelease';
				break;
			case 'MP':
				$SignObject = 'Signatures_mid';
				$object = 'EvnStickWorkRelease';
				break;
			case 'leave':
				$SignObject = 'Signatures_id';
				$object = 'EvnStick';
				break;
			case 'irr':
				$SignObject = 'Signatures_iid';
				$object = 'EvnStick';
				break;
		}

		if ($object == 'EvnStick') {
			// может быть по своеместительству
			$resp = $this->queryResult("
				select EvnClass_SysNick from v_Evn with (nolock) where Evn_id = :Evn_id
			", array(
				'Evn_id' => $data['Evn_id']
			));
			if (!empty($resp[0]['EvnClass_SysNick'])) {
				$object = $resp[0]['EvnClass_SysNick'];
			}
		}

		$queryParams = array(
			'Evn_id' => $data['Evn_id']
		);
		$query = "
				SELECT TOP 1
					S.Signatures_id,
					S.Signatures_Version
				FROM v_{$object} ESWR WITH (nolock)
				INNER JOIN v_Signatures S WITH (nolock) on ESWR.{$SignObject} = S.Signatures_id
				WHERE ESWR.{$object}_id = :Evn_id
		";
		$signatures = $this->getFirstRowFromQuery($query, $queryParams);
		$signatures['Signatures_id'] = isset($signatures['Signatures_id']) && $signatures['Signatures_id'] > 0 ? $signatures['Signatures_id'] : NULL;
		$signatures['Signatures_Version'] = isset($signatures['Signatures_Version']) && $signatures['Signatures_Version'] > 0 ? $signatures['Signatures_Version'] + 1 : 1;

		$this->load->helper('openssl');
		if (!checkCertificateCenter($data['SignedToken'])) {
			return array('Error_Msg' => 'Сертификат выдан неаккредитованным УЦ, подписание невозможно.');
		}

		if (in_array($data['signType'], array('authapplet', 'authapi', 'authapitomee'))) {
			/* $SignatureValue = implode(PHP_EOL, str_split($data['SignedData'], 64)); */
			$SignatureValue = $data['SignedData'];
			$DigestValue = $data['Hash'];
		} else {
			$xmldata = new DOMDocument();
			$xmldata->loadXML($data['xml']);
			$SignatureValue = $xmldata->getElementsByTagName('SignatureValue')->item(0)->nodeValue;
			$DigestValue = $xmldata->getElementsByTagName('DigestValue')->item(0)->nodeValue;
		}

		// логируем все подписи, чтобы проще разбираться что пошло не так.
		$this->load->library('textlog', array('file' => 'EvnStickSign_' . date('Y-m-d') . '.log'));
		$this->textlog->add('Evn_id: ' . $data['Evn_id'] . ', SignObject: ' . $data['SignObject'] . ', signType: ' . $data['signType']);
		$this->textlog->add('xml: ' . $data['xml']);
		$this->textlog->add('Signatures_Token: ' . $data['SignedToken']);
		$this->textlog->add('Signatures_SignedData: ' . $SignatureValue);
		$this->textlog->add('Signatures_Hash: ' . $DigestValue);

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :Signatures_id;

			exec p_Signatures_" . ($signatures['Signatures_id'] > 0 ? "upd" : "ins") . "
				@Signatures_id = @Res output,
				@Signatures_Version = :Signatures_Version,
				@SignaturesStatus_id = :SignaturesStatus_id,
				@Signatures_Hash = :Signatures_Hash,
				@Signatures_SignedData = :Signatures_SignedData,
				@Signatures_Token = :Signatures_Token,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as Signatures_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Signatures_id' => $signatures['Signatures_id'],
			'Signatures_Version' => $signatures['Signatures_Version'],
			'SignaturesStatus_id' => 1,
			'Signatures_Hash' => $DigestValue,
			'Signatures_SignedData' => $SignatureValue,
			'Signatures_Token' => $data['SignedToken'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Signatures_id'])) {
				if (empty($signatures['Signatures_id'])) {
					$query = "update {$object} with (rowlock) set {$SignObject} = :Signatures_id where {$object}_id = :Evn_id";
					$this->db->query($query, array(
						'Evn_id' => $data['Evn_id'],
						'Signatures_id' => $resp[0]['Signatures_id']
					));
				}
				$signatures['Signatures_id'] = $resp[0]['Signatures_id'];

				if (!empty($data['updateInRegistryESData'])) {
					$dbRegES = $this->load->database('registry_es', true);

					switch ($data['SignObject']) {
						case 'VK':
							$query = "
								update RegistryESData with (rowlock) set FirstVK_Token = :Token, FirstVK_Hash = :Hash, FirstVK_SignedData = :SignedData where FirstEvnStickWorkRelease_id = :Evn_id;
								update RegistryESData with (rowlock) set SecondVK_Token = :Token, SecondVK_Hash = :Hash, SecondVK_SignedData = :SignedData where SecondEvnStickWorkRelease_id = :Evn_id;
								update RegistryESData with (rowlock) set ThirdVK_Token = :Token, ThirdVK_Hash = :Hash, ThirdVK_SignedData = :SignedData where ThirdEvnStickWorkRelease_id = :Evn_id;
							";
							$dbRegES->query($query, array(
								'Evn_id' => $data['Evn_id'],
								'Hash' => $DigestValue,
								'SignedData' => $SignatureValue,
								'Token' => $data['SignedToken'],
							));
							break;
						case 'MP':
							$query = "
								update RegistryESData with (rowlock) set FirstMedPersonal_Token = :Token, FirstMedPersonal_Hash = :Hash, FirstMedPersonal_SignedData = :SignedData where FirstEvnStickWorkRelease_id = :Evn_id;
								update RegistryESData with (rowlock) set SecondMedPersonal_Token = :Token, SecondMedPersonal_Hash = :Hash, SecondMedPersonal_SignedData = :SignedData where SecondEvnStickWorkRelease_id = :Evn_id;
								update RegistryESData with (rowlock) set ThirdMedPersonal_Token = :Token, ThirdMedPersonal_Hash = :Hash, ThirdMedPersonal_SignedData = :SignedData where ThirdEvnStickWorkRelease_id = :Evn_id;
							";
							$dbRegES->query($query, array(
								'Evn_id' => $data['Evn_id'],
								'Hash' => $DigestValue,
								'SignedData' => $SignatureValue,
								'Token' => $data['SignedToken'],
							));
							break;
						case 'leave':
							$query = "update RegistryESData with (rowlock) set EvnStickLeave_Token = :Token, EvnStickLeave_Hash = :Hash, EvnStickLeave_SignedData = :SignedData where Evn_id = :Evn_id";
							$dbRegES->query($query, array(
								'Evn_id' => $data['Evn_id'],
								'Hash' => $DigestValue,
								'SignedData' => $SignatureValue,
								'Token' => $data['SignedToken'],
							));
							break;
						case 'irr':
							$query = "update RegistryESData with (rowlock) set StickIrregularity_Token = :Token, StickIrregularity_Hash = :Hash, StickIrregularity_SignedData = :SignedData where Evn_id = :Evn_id";
							$dbRegES->query($query, array(
								'Evn_id' => $data['Evn_id'],
								'Hash' => $DigestValue,
								'SignedData' => $SignatureValue,
								'Token' => $data['SignedToken'],
							));
							break;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :SignaturesHistory_id;

			exec p_SignaturesHistory_ins
				@SignaturesHistory_id = @Res output,
				@Signatures_id = :Signatures_id,
				@Signatures_Version = :Signatures_Version,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as SignaturesHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'SignaturesHistory_id' => NULL,
			'Signatures_id' => $signatures['Signatures_id'],
			'Signatures_Version' => $signatures['Signatures_Version'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	* 	Возвращает статус подписания исхода ЛВН или режима
	*/
	function getEvnStickSignStatus($data) {
		$SignObject = $data['SignObject'] == 'irr' ? 'Signatures_iid' : 'Signatures_id';
		$query = "
			select 
				S.Signatures_id,
				isnull(S.SignaturesStatus_id, 2) as SStatus_id,
				SS.SignaturesStatus_Name + '. ' +
				isnull(convert(varchar(10), S.Signatures_updDT, 104) + ' ', '') + 
				isnull(convert(varchar(5), S.Signatures_updDT, 108) + '. ', '') +
				isnull(SU.PMUser_Name, '') as SStatus_Name,
				SU.MedPersonal_id
			from EvnStick ES with(nolock) 
			left join v_Signatures S with(nolock) on ES.{$SignObject} = S.Signatures_id
			left join v_pmUserCache SU with (nolock) on SU.PMUser_id = S.pmUser_updID
			left join v_SignaturesStatus SS with (nolock) on SS.SignaturesStatus_id = isnull(S.SignaturesStatus_id, 2)
			where ES.EvnStick_id = :EvnStick_id
			
			union all
			
			select 
				S.Signatures_id,
				isnull(S.SignaturesStatus_id, 2) as SStatus_id,
				SS.SignaturesStatus_Name + '. ' +
				isnull(convert(varchar(10), S.Signatures_updDT, 104) + ' ', '') + 
				isnull(convert(varchar(5), S.Signatures_updDT, 108) + '. ', '') +
				isnull(SU.PMUser_Name, '') as SStatus_Name,
				SU.MedPersonal_id
			from EvnStickDop ES with(nolock) 
			left join v_Signatures S with(nolock) on ES.{$SignObject} = S.Signatures_id
			left join v_pmUserCache SU with (nolock) on SU.PMUser_id = S.pmUser_updID
			left join v_SignaturesStatus SS with (nolock) on SS.SignaturesStatus_id = isnull(S.SignaturesStatus_id, 2)
			where ES.EvnStickDop_id = :EvnStick_id
		";

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id']
		);
		
		$result = $this->getFirstRowFromQuery($query, $queryParams);

		if ( !empty($result) ) {
			return $result;
		}
		else {
			return false;
		}
		
	}
	
	/**
	* Верификация подписи
	*/
	function verifyEvnStickSign($data) {
		$data['needSignatures'] = true;
		return $this->getWorkReleaseSslHash($data);
	}
	
	/**
	 * Список версий
	 */
	function loadStickVersionList($data) {
		
		$query = "
				SELECT
					SH.Signatures_id,
					SH.SignaturesHistory_id,
					SH.Signatures_Version,
					convert(varchar(10), SH.SignaturesHistory_insDT, 104) + ' ' + convert(varchar(5), SH.SignaturesHistory_insDT, 108) as SignaturesHistory_insDT,
					SU.PMUser_Name
				FROM v_SignaturesHistory SH WITH (nolock)
				INNER JOIN v_pmUserCache SU with (nolock) on SU.PMUser_id = SH.pmUser_insID
				WHERE SH.Signatures_id = :Signatures_id
				order by SignaturesHistory_insDT asc
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
		
	}
	
	/**
	* Обновление статуса подписи
	*/
	function setSignStatus($data) {
	
		if ($data['SignaturesStatus_id'] != 3) {
			return false;
		}
		
		$query = "update Signatures set SignaturesStatus_id = :SignaturesStatus_id where Signatures_id = :Signatures_id";
		$this->db->query($query, array('Signatures_id' => $data['Signatures_id'], 'SignaturesStatus_id' => $data['SignaturesStatus_id']));

		return array('success' => true, 'Error_Msg' => '');
	}
	
	/**
	 * Проверка является ли ЛВН электронным
	 */
	function checkELN($data) {
		$query = "
			select top 1 *
			from
				v_RegistryESStorage RES with (nolock)
			where 
				RES.EvnStickBase_id = :EvnStick_id
		";
		if(empty($data['EvnStick_id']) && !empty($data['RegistryESStorage_id'])) {
			$query = "
				select top 1 *
				from
					RegistryESStorage with (nolock)
				where
					RegistryESStorage_id = :RegistryESStorage_id
			";
		}
		$response = $this->getFirstRowFromQuery($query, $data);
		return !empty($response);
	}

	/**
	 * Проверка соответствия пациента в ЛВН и пациента в родительском событии
	 */
	function checkEvnStickPerson($EvnStick_pid, $Person_id, $evnStickCarePerson) {
		$evn = $this->getFirstRowFromQuery("
			select top 1 Person_id, EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
		", array('Evn_id' => $EvnStick_pid));
		if (!is_array($evn)) {
			return $this->createError('', 'Ошибка при получении данных из родительского события');
		}
		$Person_pid = $evn['Person_id'];
		$parentClass = in_array($evn['EvnClass_SysNick'], array('EvnPL','EvnPLStom')) ? 'ТАП' : 'КВС';

		$check = false;
		if ($Person_pid == $Person_id) {
			$check = true;
		}
		foreach($evnStickCarePerson as $item) {
			if ($item['RecordStatus_Code'] != 3 && $item['Person_id'] == $Person_pid) {
				$check = true;
			}
		}

		if (!$check) {
			$error = "Человек, на которого заведен {$parentClass}, должен быть указан в качестве получателя ЛВН или присутствовать в списке пациентов, нуждающихся в уходе.";
			return $this->createError('', $error);
		}
		return array(array('success' => 'true'));
	}

	/**
	 * Получение списка ЛВН. Метод для API
	 */
	function loadEvnStickListForAPI($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filters = array();

		if (!empty($data['Evn_pid'])) {
			$filters[] = "
				ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :EvnStick_pid
					union all
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_rid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id = :EvnStick_pid
				)
			";
			$params['EvnStick_pid'] = $data['Evn_pid'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$filters[] = "ESB.Lpu_id = :Lpu_id";

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				ESB.EvnStickBase_id,
				ESB.EvnStickBase_pid as Evn_id,
				ESB.StickType_id,
				RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
				convert(varchar(10), ESB.EvnStickBase_setDate, 120) as EvnStick_setDate
			from
				v_EvnStickBase ESB with(nolock)
			where
				{$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных ЛВН. Метод для API
	 */
	function getEvnStickForAPI($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filters = array();

		if (!empty($data['EvnStickBase_id'])) {
			$filters[] = "ES.EvnStick_id = :EvnStickBase_id";
			$params['EvnStickBase_id'] = $data['EvnStickBase_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filters[] = "ES.EvnStick_pid = :Evn_pid";
			$params['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['EvnStick_Num'])) {
			$filters[] = "ES.EvnStick_Num = :EvnStick_Num";
			$params['EvnStick_Num'] = $data['EvnStick_Num'];
		}
		if (!empty($data['EvnStick_setDate'])) {
			$filters[] = "ES.EvnStick_setDate = :EvnStick_setDate";
			$params['EvnStick_setDate'] = $data['EvnStick_setDate'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$dateSelect = " convert(varchar(10), ES.EvnStick_setDate, 120) as EvnStick_setDate, ";
		$forGravitySelect = '';
		$forGravityJoin = '';
		if (!empty($data['fromMobile'])) {
			$dateSelect = "
				convert(varchar(10), ES.EvnStick_setDate, 104) as EvnStick_setDate, 
			";
			// по отдельной просьбе Гравити вывод Code и Name issues/106230#note-363
			$forGravitySelect = "
				,SWT.StickWorkType_Code
				,SWT.StickWorkType_Name
				,SC.StickCause_Code
				,SC.StickCause_Name
				,SCDP.StickCauseDopType_Code
				,SCDP.StickCauseDopType_Name
				,SCd.StickCause_Code as StickCause_dCode
				,SCd.StickCause_Name as StickCause_dName
				,case when ES.EvnStick_IsFSS = 2 then 1 else 0 end as EvnStickBase_IsFSS_Code
				,case when ES.EvnStick_IsFSS = 2 then 'Да' else 'Нет' end as EvnStickBase_IsFSS_Name
				,convert(varchar(10), ESB.EvnStickBase_StickDT, 104) as EvnStick_StickDT
			";
			$forGravityJoin = "
				left join StickCauseDopType SCDP (nolock) on SCDP.StickCauseDopType_id = ES.StickCauseDopType_id
				left join StickCause SC (nolock) on SC.StickCause_id = ES.StickCause_id
				left join StickCause SCd (nolock) on SCd.StickCause_id = ES.StickCause_did
				left join StickWorkType SWT (nolock) on SWT.StickWorkType_id = ES.StickWorkType_id
			";
		}

		$filters[] = "Lpu_id = :Lpu_id";

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				ES.EvnStick_id as EvnStickBase_id,
				ES.EvnStick_pid as Evn_id,
				ES.EvnStick_isOriginal,
				ES.StickWorkType_id,
				ES.StickOrder_id,
				ES.EvnStick_prid,
				ES.EvnStick_Num,
				{$dateSelect}
				ES.StickCause_id,
				ES.StickCauseDopType_id,
				ES.StickCause_did,
				ES.StickIrregularity_id,
				ES.EvnStick_stacBegDate,
				ES.EvnStick_stacEndDate,
				convert(varchar(10), ES.EvnStick_mseDT, 120) as EvnStick_mseDate,
				convert(varchar(10), ES.EvnStick_mseRegDT, 120) as EvnStick_mseRegDate,
				convert(varchar(10), ES.EvnStick_mseExamDT, 120) as EvnStick_mseExamDate,
				ES.InvalidGroupType_id,
				ES.StickLeaveType_id,
				convert(varchar(10), ES.EvnStick_disDate, 120) as EvnStick_disDate,
				ES.MedStaffFact_id,
				ES.Lpu_oid,
				ESB.EvnStickBase_nid as EvnStick_nid,
				ES.EvnStick_IsFSS as EvnStickBase_IsFSS,
				RESS.RegistryESStorage_id,
				ES.EvnStick_consentDT,
				ES.StickFSSData_id,
				ES.Post_Name
				{$forGravitySelect}
			from
				v_EvnStick ES with(nolock)
				inner join EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
				outer apply (
					select top 1 RegistryESStorage_id
					from RegistryESStorage with (nolock)
					where EvnStickBase_id = ES.EvnStick_id
				) RESS
				{$forGravityJoin}
			where
				{$filters_str}
		";

		//echo '<pre>',print_r(getDebugSQL($query, $params)),'</pre>'; die();
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных о справке учащегося. Метод для API
	 */
	function getEvnStickStudentForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['EvnStickBase_id'])) {
			$filters[] = "ESS.EvnStickStudent_id = :EvnStickBase_id";
			$params['EvnStickBase_id'] = $data['EvnStickBase_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filters[] = "ESS.EvnStickStudent_pid = :Evn_pid";
			$params['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['EvnStick_Num'])) {
			$filters[] = "ESS.EvnStickStudent_Num = :EvnStick_Num";
			$params['EvnStick_Num'] = $data['EvnStick_Num'];
		}
		if (!empty($data['EvnStick_setDate'])) {
			$filters[] = "ESS.EvnStickStudent_setDate = :EvnStick_setDate";
			$params['EvnStick_setDate'] = $data['EvnStick_setDate'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$filters[] = "ESS.Lpu_id = :Lpu_id";
		$params['Lpu_id'] = $data['Lpu_id'];

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				ESS.EvnStickStudent_id as EvnStickBase_id,
				ESS.EvnStickStudent_pid as Evn_id,
				ESS.EvnStickStudent_pid as Evn_pid,
				ESS.StickType_id,
				ESS.EvnStickStudent_Num as EvnStick_Num,
				convert(varchar(10), ESS.EvnStickStudent_setDate, 120) as EvnStick_setDate,
				ESS.Org_id,
				ESS.StickRecipient_id,
				ESS.StickCause_id,
				IsContact.YesNo_Code as EvnStick_isContact,
				ESS.EvnStickStudent_ContactDescr as EvnStick_ContactDescr,
				ESS.MedStaffFact_id
			from
				v_EvnStickStudent ESS with(nolock)
				left join v_YesNo IsContact with(nolock) on IsContact.YesNo_id = ESS.EvnStickStudent_isContact
			where
				{$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение информации для редактирования ЛВН. Метод для API
	 */
	function getEvnStickInfoForAPI($data) {
		$query = "
			select top 1
				P.Person_id,
				P.PersonEvn_id,
				P.Server_id,
				MSF.MedPersonal_id
			from
			 	v_Evn E with(nolock)
				outer apply(
					select top 1 P.Person_id, P.PersonEvn_id, P.Server_id
					from v_Person_all P with(nolock)
					where P.Person_id = E.Person_id and PersonEvn_insDT <= :EvnStick_setDate
					order by P.PersonEvn_insDT desc
				) P
				outer apply(
					select top 1 MedPersonal_id 
					from v_MedStaffFact 
					where MedStaffFact_id = :MedStaffFact_id
				) MSF
			where 
				E.Evn_id = :Evn_pid
				and E.Lpu_id = :Lpu_id
		";
		return $this->getFirstRowFromQuery($query, $data);
	}

	/**
	 * Получение списка периодов освобождения. Метод для API
	 */
	function loadEvnStickWorkReleaseListForAPI($data) {

		$select = ",
			convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 120) as EvnStickWorkRelease_begDate,
			convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 120) as EvnStickWorkRelease_endDate
		";

		$params = array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['fromMobile'])) {
			$select = ",
				ESWR.Org_id,
				ESWR.EvnStickWorkRelease_IsDraft,
				ESWR.EvnStickWorkRelease_IsSpecLpu,
				ESWR.Signatures_mid,
				ESWR.Signatures_wid,
				ESWR.LpuSection_id,
				ESWR.MedPersonal_id,
				ESWR.MedPersonal2_id,
				ESWR.MedPersonal3_id,
				ESWR.MedStaffFact_id,
				ESWR.MedStaffFact2_id,
				ESWR.MedStaffFact3_id,
				ESWR.EvnStickWorkRelease_IsPredVK,
				ESWR.Post_id,
				1 as RecordStatus_Code,
				null as EvnVK_id,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 104) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 104) as EvnStickWorkRelease_endDate,
				isnull(lpu.Lpu_Nick, lpu.Lpu_Name) as mo,
				(msf.Person_SurName + ' ' + msf.Person_FirName) as doctorName
			";
		}

		$query = "
			select
				ESWR.EvnStickBase_id,
				ESWR.EvnStickWorkRelease_id
				{$select}
			from 
				v_EvnStickWorkRelease ESWR with(nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				left join v_Lpu lpu (nolock) on lpu.Org_id = ESWR.Org_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ESWR.MedStaffFact_id
			where 
				ESWR.EvnStickBase_id = :EvnStickBase_id
				and ESB.Lpu_id = :Lpu_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка списка пациентов по уходу. Метод для API
	 * @param $data array
	 */
	function loadEvnStickCarePersonListForAPI($data) {
		$params = array('Evn_id' => $data['EvnStickBase_id']);

		$query = "
			SELECT
				ESCP.EvnStickCarePerson_id,
				ESCP.Evn_id,
				ESCP.Person_id,
				E.Person_id as Person_pid,
				ESCP.RelatedLinkType_id,
				RTRIM(LTRIM(ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, ''))) as Person_Fio,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				PS.Person_id,
				dbo.Age2(PS.Person_Birthday, E.Evn_setDT) as Person_Age,
				RTRIM(ISNULL(RLT.RelatedLinkType_Name, '')) as RelatedLinkType_Name,
				RLT.RelatedLinkType_Code,
				1 as RecordStatus_Code
			FROM v_EvnStickCarePerson ESCP (nolock)
			inner join v_PersonState PS (nolock) on PS.Person_id = ESCP.Person_id
			inner join EvnStickBase ESB (nolock) on ESB.EvnStickBase_id = ESCP.Evn_id
			inner join Evn E (nolock) on E.Evn_id = ESB.Evn_id
			left join RelatedLinkType RLT (nolock) on RLT.RelatedLinkType_id = ESCP.RelatedLinkType_id
			where
				ESCP.Evn_id = :Evn_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных о периоде освобождения. Метод для API
	 */
	function getEvnStickWorkReleaseForAPI($data) {
		$params = array();
		$filters = array();

		if (!empty($data['EvnStickWorkRelease_id'])) {
			$filters[] = "ESWR.EvnStickWorkRelease_id = :EvnStickWorkRelease_id";
			$params['EvnStickWorkRelease_id'] = $data['EvnStickWorkRelease_id'];
		}
		if (!empty($data['EvnStickBase_id'])) {
			$filters[] = "ESWR.EvnStickBase_id = :EvnStickBase_id";
			$params['EvnStickBase_id'] = $data['EvnStickBase_id'];
		}
		if (!empty($data['EvnStickWorkRelease_begDate'])) {
			$filters[] = "cast(ESWR.EvnStickWorkRelease_begDT as date) = :EvnStickWorkRelease_begDate";
			$params['EvnStickWorkRelease_begDate'] = $data['EvnStickWorkRelease_begDate'];
		}
		if (!empty($data['EvnStickWorkRelease_endDate'])) {
			$filters[] = "cast(ESWR.EvnStickWorkRelease_endDT as date) = :EvnStickWorkRelease_endDate";
			$params['EvnStickWorkRelease_endDate'] = $data['EvnStickWorkRelease_endDate'];
		}

		if (count($filters) == 0) {
			return $this->createError('','Не было передано ни одного параметра');
		}

		$filters[] = "ESB.Lpu_id = :Lpu_id";
		$params['Lpu_id'] = $data['Lpu_id'];

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				ESWR.EvnStickBase_id,
				ESWR.EvnStickWorkRelease_id,
				IsDraft.YesNo_Code as EvnStickWorkRelease_isDraft,
				convert(varchar(10), ESWR.EvnStickWorkRelease_begDT, 120) as EvnStickWorkRelease_begDate,
				convert(varchar(10), ESWR.EvnStickWorkRelease_endDT, 120) as EvnStickWorkRelease_endDate,
				ESWR.LpuSection_id,
				ESWR.MedStaffFact_id,
				ESWR.MedStaffFact2_id,
				ESWR.MedStaffFact3_id,
				IsPredVK.YesNo_Code as EvnStickWorkRelease_isPredVK
			from 
				v_EvnStickWorkRelease ESWR with(nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				left join v_YesNo IsDraft with(nolock) on IsDraft.YesNo_id = ESWR.EvnStickWorkRelease_isDraft
				left join v_YesNo IsPredVK with(nolock) on IsPredVK.YesNo_id = ESWR.EvnStickWorkRelease_isPredVK
			where 
				{$filters_str}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка связей случая лечения с ЛВН. Метод для API
	 */
	function getEvnLinkListForAPI($data) {
		$params = array('Evn_id' => $data['Evn_id']);
		$query = "
			select
				EL.Evn_id,
				EL.Evn_lid
			from v_EvnLink EL with(nolock)
			where EL.Evn_id = :Evn_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Удаление связи случая лечения с ЛВН
	 */
	function deleteEvnLinkForAPI($data) {
		$params = array(
			'Evn_id' => $data['Evn_id'],
			'Evn_lid' => $data['Evn_lid'],
		);
		$query = "
			select top 1 EL.EvnLink_id
			from v_EvnLink EL with(nolock)
			where EL.Evn_id = :Evn_id and EL.Evn_lid = :Evn_lid
		";
		$EvnLink_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($EvnLink_id === false) {
			return $this->createError('','Ошибка при получении идентификатора связи событий');
		}
		if (empty($EvnLink_id)) {
			return $this->createError('','Не найден связь переданных событий');
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnLink_del
				@EvnLink_id = :EvnLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, array('EvnLink_id' => $EvnLink_id));
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении связи событий');
		}
		return $response;
	}

	/**
	 *  Получение списка ЛВН в ЭМК
	 */
	function loadEvnStickPanel($data)
	{
		$accessType = $this->getEvnStickAccessType($data);
	
		$sql = "
			-- addit with
			with ESBASE as (
				select
					ESB.EvnStickBase_id
				from
					v_EvnStickBase ESB (nolock)
					inner join v_Evn E (nolock) on E.Evn_id = ESB.EvnStickBase_mid
				where
					E.Person_id = :Person_id

				union

				select
					ESB.EvnStickBase_id
				from
					v_EvnStickBase ESB (nolock)
				where
					ESB.Person_id = :Person_id
			)
			-- end addit with
			
			select
				-- select
				sticks.*, SC.StickCause_SysNick
				-- end select
			from 
			-- from
			(
				select
					{$accessType}
					ISNULL(es.EvnStick_IsPaid, 1) as EvnStick_IsPaid,
					ISNULL(es.EvnStick_IsInReg, 1) as EvnStick_IsInReg,
					es.EvnStick_id,
					es.EvnStick_prid,
					es.EvnStick_IsSigned as EvnStick_IsSigned,
					es.Person_id,
					es.Server_id,
					es.PersonEvn_id,
					es.EvnStick_mid,
					es.EvnStick_pid,
					1 as evnStickType,
					es.StickCause_id,
					es.EvnStick_setDT,
					convert(varchar(10), es.EvnStick_setDT, 104) as EvnStick_setDate,
					convert(varchar(10), es.EvnStick_disDT, 104) as EvnStick_disDate,
					es.EvnStick_Num,
					swt.StickWorkType_Name,
					so.StickOrder_id,
					so.StickOrder_Name,
					slt.StickLeaveType_id,
					slt.StickLeaveType_Name,
					slt.StickLeaveType_Code,
					case when es.EvnStick_mid = :EvnStick_pid or ESL.EvnLink_id is not null then :EvnStick_pid else es.EvnStick_mid end as EvnStick_rid,
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as CardType,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as NumCard,
					case
						when linkTAP.EvnPL_id is not null then 'ТАП'
						when linkTAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when linkKVS.EvnPS_id is not null then 'КВС'
						else ''
					end as linkCardType,
					case
						when linkTAP.EvnPL_id is not null then linkTAP.EvnPL_NumCard
						when linkTAPStom.EvnPLStom_id is not null then linkTAPStom.EvnPLStom_NumCard
						when linkKVS.EvnPS_id is not null then linkKVS.EvnPS_NumCard
					   else ''
					end as linkNumCard,
					case
						when TAP_Lpu.Lpu_id is not null then TAP_Lpu.Lpu_Nick
						when TAPStom_Lpu.Lpu_id is not null then TAPStom_Lpu.Lpu_Nick
						when KVS_Lpu.Lpu_id is not null then KVS_Lpu.Lpu_Nick
					   else ''
					end as Lpu_Nick,
					case
						when linkTAPLpu.Lpu_id is not null then linkTAPLpu.Lpu_Nick
						when linkTAPStomLpu.Lpu_id is not null then linkTAPStomLpu.Lpu_Nick
						when linkKVSLpu.Lpu_id is not null then linkKVSLpu.Lpu_Nick
					   else ''
					end as linkLpu_Nick,
					E.Evn_pid,
					RESS.RegistryESStorage_id as ISELN,
					isnull(ESL.EvnLink_id, 0) as EvnStickLinked,
					isnull(SFD.StickFSSData_id, 0) as requestExist,
					ESB.EvnStickBase_IsDelQueue
				from
					ESBASE
					inner join v_EvnStick es with (nolock) on es.EvnStick_id = ESBASE.EvnStickBase_id
					--inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ES.EvnStick_id
					cross apply (
						Select top 1 * from v_EvnStickBase_all ESB with (nolock) where ESB.EvnStickBase_id = ES.EvnStick_id
					) ESB
					left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id

					left join v_StickWorkType swt (nolock) on swt.StickWorkType_id = es.StickWorkType_id
					left join v_StickOrder so (nolock) on so.StickOrder_id = es.StickOrder_id
					left join v_StickLeaveType slt (nolock) on slt.StickLeaveType_id = esb.StickLeaveType_rid
					
					left join v_EvnPL TAP with (nolock) on ESB.EvnStickBase_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESB.EvnStickBase_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESB.EvnStickBase_mid = KVS.EvnPS_id
					
					left join v_Lpu_all TAP_Lpu (nolock) on TAP_Lpu.Lpu_id = TAP.Lpu_id
					left join v_Lpu_all TAPStom_Lpu (nolock) on TAPStom_Lpu.Lpu_id = TAPStom.Lpu_id
					left join v_Lpu_all KVS_Lpu (nolock) on KVS_Lpu.Lpu_id = KVS.Lpu_id
					
					left join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
					outer apply (
						select top 1 StickFSSData_id
						from v_StickFSSData with (nolock)
						where
							StickFSSData_StickNum = ESB.EvnStickBase_Num
							and StickFSSDataStatus_id not in (3, 4, 5)
					) as SFD
					outer apply (
						select top 1
							RegistryESStorage_id
						from
							v_RegistryESStorage with (nolock)
						where
							EvnStickBase_id = ESB.EvnStickBase_id
					) RESS
					outer apply (
						select top 1
							EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease
						where
							(EvnStickWorkRelease_IsInReg = 2 or EvnStickWorkRelease_IsPaid = 2)
							and EvnStickBase_id = ESB.EvnStickBase_id
					) ESWR
					left join v_EvnStatus ESTATUS (nolock) on ESTATUS.EvnStatus_id = es.EvnStatus_id
					outer apply (
						select top 1
							EL.EvnLink_id, EL.Evn_id, EL.Evn_lid
						from v_EvnLink EL with (nolock)
						where EL.Evn_id = :EvnStick_pid and EL.Evn_lid = es.EvnStick_id
					) ESL
					left join v_EvnPL linkTAP with (nolock) on ESL.Evn_id = linkTAP.EvnPL_id
					left join v_EvnPLStom linkTAPStom with (nolock) on ESL.Evn_id = linkTAPStom.EvnPLStom_id
					left join v_EvnPS linkKVS with (nolock) on ESL.Evn_id = linkKVS.EvnPS_id
					
					left join v_Lpu_all linkTAPLpu (nolock) on linkTAPLpu.Lpu_id = linkTAP.Lpu_id
					left join v_Lpu_all linkTAPStomLpu (nolock) on linkTAPStomLpu.Lpu_id = linkTAPStom.Lpu_id
					left join v_Lpu_all linkKVSLpu (nolock) on linkKVSLpu.Lpu_id = linkKVS.Lpu_id
				union all
				
				select
					{$accessType}
					ISNULL(es.EvnStick_IsPaid, 1) as EvnStick_IsPaid,
					ISNULL(es.EvnStick_IsInReg, 1) as EvnStick_IsInReg,
					esd.EvnStickDop_id as EvnStick_id,
					esd.EvnStickDop_prid as EvnStick_prid,
					esd.EvnStickDop_IsSigned as EvnStick_IsSigned,
					es.Person_id,
					es.Server_id,
					es.PersonEvn_id,
					es.EvnStick_mid,
					es.EvnStick_pid,
					2 as evnStickType,
					ES.StickCause_id,
					esd.EvnStickDop_setDT as EvnStick_setDT,
					convert(varchar(10), esd.EvnStickDop_setDT, 104) as EvnStick_setDate,
					convert(varchar(10), esd.EvnStickDop_disDT, 104) as EvnStick_disDate,
					esd.EvnStickDop_Num as EvnStick_Num,
					swt.StickWorkType_Name,
					so.StickOrder_id,
					so.StickOrder_Name,
					slt.StickLeaveType_id,
					slt.StickLeaveType_Name,
					slt.StickLeaveType_Code,
					case when esd.EvnStickDop_mid = :EvnStick_pid or ESL.EvnLink_id is not null then :EvnStick_pid else esd.EvnStickDop_mid end as EvnStick_rid,
					
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as CardType,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as NumCard,
					case
						when linkTAP.EvnPL_id is not null then 'ТАП'
						when linkTAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when linkKVS.EvnPS_id is not null then 'КВС'
						else ''
					end as linkCardType,
					case
						when linkTAP.EvnPL_id is not null then linkTAP.EvnPL_NumCard
						when linkTAPStom.EvnPLStom_id is not null then linkTAPStom.EvnPLStom_NumCard
						when linkKVS.EvnPS_id is not null then linkKVS.EvnPS_NumCard
					   else ''
					end as linkNumCard,
					case
						when TAP_Lpu.Lpu_id is not null then TAP_Lpu.Lpu_Nick
						when TAPStom_Lpu.Lpu_id is not null then TAPStom_Lpu.Lpu_Nick
						when KVS_Lpu.Lpu_id is not null then KVS_Lpu.Lpu_Nick
					   else ''
					end as Lpu_Nick,
					case
						when linkTAPLpu.Lpu_id is not null then linkTAPLpu.Lpu_Nick
						when linkTAPStomLpu.Lpu_id is not null then linkTAPStomLpu.Lpu_Nick
						when linkKVSLpu.Lpu_id is not null then linkKVSLpu.Lpu_Nick
					   else ''
					end as linkLpu_Nick,
					
					E.Evn_pid,
					RESS.RegistryESStorage_id as ISELN,
					isnull(ESL.EvnLink_id, 0) as EvnStickLinked,
					isnull(SFD.StickFSSData_id, 0) as requestExist,
					ESB.EvnStickBase_IsDelQueue
				from
					ESBASE
					inner join v_EvnStickDop ESD with (nolock) on ESD.EvnStickDop_id = ESBASE.EvnStickBase_id
					inner join v_EvnStick ES (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
					--inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ESD.EvnStickDop_id
					cross apply (
						Select top 1 * from v_EvnStickBase_all ESB with (nolock) where ESB.EvnStickBase_id = ESD.EvnStickDop_id
					) ESB
					left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
					
					left join v_StickWorkType swt (nolock) on swt.StickWorkType_id = ESD.StickWorkType_id
					left join v_StickOrder so (nolock) on so.StickOrder_id = ESD.StickOrder_id
					left join v_StickLeaveType slt (nolock) on slt.StickLeaveType_id = ESB.StickLeaveType_rid
									
					left join v_EvnPL TAP with (nolock) on ESB.EvnStickBase_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESB.EvnStickBase_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESB.EvnStickBase_mid = KVS.EvnPS_id
					
					left join v_Lpu_all TAP_Lpu (nolock) on TAP_Lpu.Lpu_id = TAP.Lpu_id
					left join v_Lpu_all TAPStom_Lpu (nolock) on TAPStom_Lpu.Lpu_id = TAPStom.Lpu_id
					left join v_Lpu_all KVS_Lpu (nolock) on KVS_Lpu.Lpu_id = KVS.Lpu_id
					
					left join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
					outer apply (
						select top 1 StickFSSData_id
						from v_StickFSSData with (nolock)
						where
							StickFSSData_StickNum = ESB.EvnStickBase_Num
							and StickFSSDataStatus_id not in (3, 4, 5)
					) as SFD
					outer apply (
						select top 1
							RegistryESStorage_id
						from
							v_RegistryESStorage with (nolock)
						where
							EvnStickBase_id = ESB.EvnStickBase_id
					) RESS
					outer apply (
						select top 1
							EvnStickWorkRelease_id
						from
							v_EvnStickWorkRelease
						where
							(EvnStickWorkRelease_IsInReg = 2 or EvnStickWorkRelease_IsPaid = 2)
							and EvnStickBase_id = ESB.EvnStickBase_id
					) ESWR
					outer apply (
						select top 1
							EL.EvnLink_id, EL.Evn_id, EL.Evn_lid
						from v_EvnLink EL with (nolock)
						where EL.Evn_id = :EvnStick_pid and EL.Evn_lid = ESD.EvnStickDop_id
					) ESL
					left join v_EvnPL linkTAP with (nolock) on ESL.Evn_id = linkTAP.EvnPL_id
					left join v_EvnPLStom linkTAPStom with (nolock) on ESL.Evn_id = linkTAPStom.EvnPLStom_id
					left join v_EvnPS linkKVS with (nolock) on ESL.Evn_id = linkKVS.EvnPS_id
					
					left join v_Lpu_all linkTAPLpu (nolock) on linkTAPLpu.Lpu_id = linkTAP.Lpu_id
					left join v_Lpu_all linkTAPStomLpu (nolock) on linkTAPStomLpu.Lpu_id = linkTAPStom.Lpu_id
					left join v_Lpu_all linkKVSLpu (nolock) on linkKVSLpu.Lpu_id = linkKVS.Lpu_id
					
				union all
				
				select
					{$accessType}
					ISNULL(ess.EvnStickStudent_IsPaid, 1) as EvnStick_IsPaid,
					ISNULL(ess.EvnStickStudent_IsInReg, 1) as EvnStick_IsInReg,
					ess.EvnStickStudent_id as EvnStick_id,
					null as EvnStick_prid,
					ess.EvnStickStudent_IsSigned as EvnStick_IsSigned,
					ess.Person_id,
					ess.Server_id,
					ess.PersonEvn_id,
					ess.EvnStickStudent_mid,
					ess.EvnStickStudent_pid,
					3 as evnStickType,
					null as StickCause_id,
					ess.EvnStickStudent_setDT as EvnStick_setDT,
					convert(varchar(10), ess.EvnStickStudent_setDT, 104) as EvnStick_setDate,
					convert(varchar(10), ess.EvnStickStudent_disDT, 104) as EvnStick_disDate,
					ess.EvnStickStudent_Num as EvnStick_Num,
					swt.StickWorkType_Name,
					so.StickOrder_id,
					so.StickOrder_Name,
					null as StickLeaveType_id,
					null as StickLeaveType_Name,
					null as StickLeaveType_Code,
					case when ess.EvnStickStudent_mid = :EvnStick_pid or ESL.EvnLink_id is not null then :EvnStick_pid else ess.EvnStickStudent_mid end as EvnStick_rid,
					
					case
						when TAP.EvnPL_id is not null then 'ТАП'
						when TAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when KVS.EvnPS_id is not null then 'КВС'
						else ''
					end as CardType,
					case
						when TAP.EvnPL_id is not null then TAP.EvnPL_NumCard
						when TAPStom.EvnPLStom_id is not null then TAPStom.EvnPLStom_NumCard
						when KVS.EvnPS_id is not null then KVS.EvnPS_NumCard
					   else ''
					end as NumCard,
					case
						when linkTAP.EvnPL_id is not null then 'ТАП'
						when linkTAPStom.EvnPLStom_id is not null then 'Стом. ТАП'
						when linkKVS.EvnPS_id is not null then 'КВС'
						else ''
					end as linkCardType,
					case
						when linkTAP.EvnPL_id is not null then linkTAP.EvnPL_NumCard
						when linkTAPStom.EvnPLStom_id is not null then linkTAPStom.EvnPLStom_NumCard
						when linkKVS.EvnPS_id is not null then linkKVS.EvnPS_NumCard
					   else ''
					end as linkNumCard,
					
					case
						when TAP_Lpu.Lpu_id is not null then TAP_Lpu.Lpu_Nick
						when TAPStom_Lpu.Lpu_id is not null then TAPStom_Lpu.Lpu_Nick
						when KVS_Lpu.Lpu_id is not null then KVS_Lpu.Lpu_Nick
					   else ''
					end as Lpu_Nick,
					case
						when linkTAPLpu.Lpu_id is not null then linkTAPLpu.Lpu_Nick
						when linkTAPStomLpu.Lpu_id is not null then linkTAPStomLpu.Lpu_Nick
						when linkKVSLpu.Lpu_id is not null then linkKVSLpu.Lpu_Nick
					   else ''
					end as linkLpu_Nick,
					E.Evn_pid,
					0 as ISELN,
					isnull(ESL.EvnLink_id, 0) as EvnStickLinked,
					0 as requestExist,
					ESB.EvnStickBase_IsDelQueue
				from
					ESBASE
					inner join v_EvnStickStudent ESS with (nolock) on ESS.EvnStickStudent_id = ESBASE.EvnStickBase_id
					--inner join v_EvnStickBase_all ESB with (nolock) on ESB.EvnStickBase_id = ess.EvnStickStudent_id
					cross apply (
						Select top 1 * from v_EvnStickBase_all ESB with (nolock) where ESB.EvnStickBase_id = ess.EvnStickStudent_id
					) ESB
					left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
					
					left join v_StickWorkType swt (nolock) on swt.StickWorkType_id = ess.StickWorkType_id
					left join v_StickOrder so (nolock) on so.StickOrder_id = ess.StickOrder_id
					
					left join v_EvnPL TAP with (nolock) on ESB.EvnStickBase_mid = TAP.EvnPL_id
					left join v_EvnPLStom TAPStom with (nolock) on ESB.EvnStickBase_mid = TAPStom.EvnPLStom_id
					left join v_EvnPS KVS with (nolock) on ESB.EvnStickBase_mid = KVS.EvnPS_id
					
					left join v_Lpu_all TAP_Lpu (nolock) on TAP_Lpu.Lpu_id = TAP.Lpu_id
					left join v_Lpu_all TAPStom_Lpu (nolock) on TAPStom_Lpu.Lpu_id = TAPStom.Lpu_id
					left join v_Lpu_all KVS_Lpu (nolock) on KVS_Lpu.Lpu_id = KVS.Lpu_id
					
					left join Evn E with (nolock) on E.Evn_id = ESB.EvnStickBase_id
					outer apply (
						select top 1
							EL.EvnLink_id, EL.Evn_id, EL.Evn_lid
						from v_EvnLink EL with (nolock)
						where EL.Evn_id = :EvnStick_pid and EL.Evn_lid = ess.EvnStickStudent_id
					) ESL
					left join v_EvnPL linkTAP with (nolock) on ESL.Evn_id = linkTAP.EvnPL_id
					left join v_EvnPLStom linkTAPStom with (nolock) on ESL.Evn_id = linkTAPStom.EvnPLStom_id
					left join v_EvnPS linkKVS with (nolock) on ESL.Evn_id = linkKVS.EvnPS_id
					
					left join v_Lpu_all linkTAPLpu (nolock) on linkTAPLpu.Lpu_id = linkTAP.Lpu_id
					left join v_Lpu_all linkTAPStomLpu (nolock) on linkTAPStomLpu.Lpu_id = linkTAPStom.Lpu_id
					left join v_Lpu_all linkKVSLpu (nolock) on linkKVSLpu.Lpu_id = linkKVS.Lpu_id
			) as sticks
			left join v_StickCause SC on sticks.StickCause_id = SC.StickCause_id
			-- end from
			order by
				-- order by
				sticks.EvnStick_setDT DESC
				-- end order by 
		";
		//~ echo getDebugSQL($sql, $data);exit;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $data);
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = count($response['data']) + intval($data['start']);
			return $response;
		} else {
			return false;
		}
		//return $this->queryResult($sql, $data);
	}


	function getEvnStickDopByPid($data)
	{
		$query = "
				select 
					EvnStickDop_id
				from 
					v_EvnStickDop with (nolock)
				where 
					EvnStickDop_pid = :EvnStick_id
			";
		return $this->dbmodel->queryResult($query, $data);
	}

	function checkStickCause($data)
	{
		$result = $this->db->query("
			select top 1
				StickCause_SysNick
			from v_StickCause with (nolock)
			where StickCause_id = :StickCause_id
		", array('StickCause_id' => $data['StickCause_id']));

		if (is_object($result)) {
			$result= $result->result('array');
			if ($result[0]['StickCause_SysNick'] != 'pregn') {
				echo json_return_errors("Дата окончания пребывания в стационаре должна совпадать с датой окончания одного из периодов нетрудоспособности");
				return false;
			}
		}
	}

	function getEvnStickPridData(&$data)
	{
		if ( ! empty($data['EvnStick_prid'])) {
			$data['PridStickLeaveType_Code'] = $this->dbmodel->getFirstResultFromQuery("
					select top 1 
					    stl.StickLeaveType_Code
					from 
                        EvnStickBase esb (nolock)
                        left join v_EvnStick es with(nolock) on es.EvnStick_id = esb.EvnStickBase_id
                        left join v_EvnStickDop esd with(nolock) on esd.EvnStickDop_id = esb.EvnStickBase_id
                        left join v_EvnStick pes with(nolock) on pes.EvnStick_id = esd.EvnStickDop_pid
                        inner join v_StickLeaveType stl (nolock) on stl.StickLeaveType_id = isnull(es.StickLeaveType_id, pes.StickLeaveType_id)
					where 
					    esb.EvnStickBase_id = :EvnStick_id
				", array('EvnStick_id' => $data['EvnStick_prid']));
		}

		$data['StickCause_SysNick'] = $this->dbmodel->getFirstResultFromQuery("
            select 
                sc.StickCause_SysNick 
            from 
                v_StickCause sc (nolock) 
            where 
                sc.StickCause_id = :StickCause_id
        ", array('StickCause_id' => $data['StickCause_id']));

        $data['StickParentClass'] = $this->dbmodel->getFirstResultFromQuery("
        	select EC.EvnClass_SysNick
        	from
        		v_Evn E with (nolock)
        		inner join v_EvnClass EC with (nolock) on EC.EvnClass_id = E.EvnClass_id
        	where E.Evn_id = :EvnStick_mid
        ", array('EvnStick_mid' => $data['EvnStick_mid']));

		if (getRegionNick() == 'ufa' && !empty($data['EvnStick_id'])) {
			if ( !empty($data['StickLeaveType_id']) ) {
				$data['StickLeaveType_Code'] = $this->dbmodel->getFirstResultFromQuery("
						select 
							slt.StickLeaveType_Code
						from 
							v_StickLeaveType slt (nolock) 
						where 
							slt.StickLeaveType_id = :StickLeaveType_id
					", array('StickLeaveType_id' => $data['StickLeaveType_id']));
			}

			$data['NextStickCause_SysNick'] = $this->dbmodel->getFirstResultFromQuery("
					select 
						sc.StickCause_SysNick 
					from
						v_EvnStick es (nolock)
						inner join v_StickCause sc (nolock) on sc.StickCause_id = es.StickCause_id 
					where 
						es.EvnStick_prid = :EvnStick_id
				", array('EvnStick_id' => $data['EvnStick_id']));
		}
	}
	
	function getPersonByEvnStickPid($data) {
		$query = "
			select top 1 
				es.Person_id 
			from 
				v_EvnStick es (nolock) 
			where 
				es.EvnStick_pid = :Evn_id
		";
		return $this->dbmodel->getFirstResultFromQuery($query, $data, true);
	}
}
