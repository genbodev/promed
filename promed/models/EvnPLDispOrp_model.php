<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispOrp_model - модель для работы с талонами по доп. диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей aka Зачетный Копипастер (вообще все что связано с детьми-сиротами - все скопировано с ДД, по уму то конечно многое надо бы переписать... но времени нету - на талон и регистр по детям-сиротам три дня)
* @version      май 2010 
*/

class EvnPLDispOrp_model extends CI_Model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Удаление
	 */
	function deleteEvnPLDispOrp($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispOrp_del
				@EvnPLDispOrp_id = :EvnPLDispOrp_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)'));
		}
	}

	/**
	 * Загрузка формы
	 */
	function loadEvnPLDispOrpEditForm($data)
	{
		$query = "
			SELECT TOP 1
				EPLDD.EvnPLDispOrp_id,
				EPLDD.EvnPLDispOrp_IsFinish,
				convert(varchar(10), EPLDD.EvnPLDispOrp_setDate, 104) as EvnPLDispOrp_setDate,
				--Okved_id as EvnPLDispOrp_Okved_id,
				EPLDD.AttachType_id,
				EPLDD.Lpu_aid,
				EPLDD.PersonEvn_id
			FROM
				v_EvnPLDispOrp EPLDD with (nolock)
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispOrp_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispOrp_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}
	
	/**
	 * Получение полей
	 */
	function getEvnPLDispOrpFields($data)
	{
		$query = "
			SELECT TOP 1
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(isnull(lp1.Lpu_Name, '')) as Lpu_AName,
				rtrim(isnull(addr1.Address_Address, '')) as Lpu_AAddress,
				rtrim(lp.Lpu_OGRN) as Lpu_OGRN,
				isnull(pc.PersonCard_Code, '') as PersonCard_Code,
				ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_FIO,
				sx.Sex_Name,
				isnull(osmo.OrgSMO_Nick, '') as OrgSMO_Nick,
				isnull(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as Polis_Ser,
				isnull(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as Polis_Num,
				isnull(osmo.OrgSMO_Name, '') as OrgSMO_Name,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				isnull(addr.Address_Address, '') as Person_Address,
				jborg.Org_Nick,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispOrp_disDate, 104) as EvnPLDispOrp_disDate
			FROM
				v_EvnPLDispOrp EPLDD with (nolock)
				inner join v_Lpu lp with (nolock) on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 with (nolock) on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 with (nolock) on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc with (nolock) on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps with (nolock) on ps.Person_id = EPLDD.Person_id
				inner join Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
				left join Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo with (nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr with (nolock) on addr.Address_id = ps.PAddress_id
				left join Job jb with (nolock) on jb.Job_id = ps.Job_id
				left join Org jborg with (nolock) on jborg.Org_id = jb.Org_id
				left join AttachType atype with (nolock) on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispOrp_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispOrp_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpGrid($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_setDate, 104) as EvnVizitDispOrp_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.OrpDispSpec_Name) as OrpDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.OrpDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispOrp_IsSanKur,
				EVZDD.EvnVizitDispOrp_IsOut,
				EVZDD.DopDispAlien_id,
				1 as Record_Status
			from v_EvnVizitDispOrp EVZDD with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS with (nolock) on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D with (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpData($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_setDate, 104) as EvnVizitDispOrp_setDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(isnull(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(DDS.OrpDispSpec_Name) as OrpDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.OrpDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispOrp_IsSanKur,
				EVZDD.EvnVizitDispOrp_IsOut,
				EVZDD.DopDispAlien_id,
				1 as Record_Status
			from v_EvnVizitDispOrp EVZDD with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS with (nolock) on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D with (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpGrid($data)
	{
		
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_setDate, 104) as EvnUslugaDispOrp_setDate,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_didDate, 104) as EvnUslugaDispOrp_didDate,
				EUDD.OrpDispUslugaType_id,
				RTRIM(DDUT.OrpDispUslugaType_Name) as OrpDispUslugaType_Name,
				EUDD.LpuSection_uid as LpuSection_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EUDD.MedPersonal_id,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EUDD.Usluga_id as Usluga_id,
				RTRIM(U.Usluga_Name) as Usluga_Name,
				RTRIM(U.Usluga_Code) as Usluga_Code,
				EUDD.ExaminationPlace_id,
				1 as Record_Status
			from v_EvnUslugaDispOrp EUDD with (nolock)
				left join OrpDispUslugaType DDUT with (nolock) on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_Usluga U with (nolock) on U.Usluga_id = EUDD.Usluga_id
			where EUDD.EvnUslugaDispOrp_pid = ?
		";
		
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));
	
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpData($data)
	{		
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_setDate, 104) as EvnUslugaDispOrp_setDate,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_didDate, 104) as EvnUslugaDispOrp_didDate,
				EUDD.OrpDispUslugaType_id
			from v_EvnUslugaDispOrp EUDD with (nolock)
			where EUDD.EvnUslugaDispOrp_pid = ?
		";	
		
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Загрузка списка
	 */
	function loadEvnPLDispOrpStreamList($data)
	{
		$filter = '';
		$queryParams = array();

       	$filter .= " and [EPL].[pmUser_insID] = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']) )
		{
        	$filter .= " and [EPL].[EvnPL_insDT] >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

        if ( isset($data['Lpu_id']) )
        {
        	$filter .= " and [EPL].[Lpu_id] = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
        	SELECT DISTINCT TOP 100
				[EPL].[EvnPL_id] as [EvnPL_id],
				[EPL].[Person_id] as [Person_id],
				[EPL].[Server_id] as [Server_id],
				[EPL].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([EPL].[EvnPL_NumCard]) as [EvnPL_NumCard],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				convert(varchar(10), [EPL].[EvnPL_setDate], 104) as [EvnPL_setDate],
				convert(varchar(10), [EPL].[EvnPL_disDate], 104) as [EvnPL_disDate],
				[EPL].[EvnPL_VizitCount] as [EvnPL_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPL_IsFinish]
			FROM [v_EvnPL] [EPL] with (nolock)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EPL].[Person_id]
				left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPL].[EvnPL_IsFinish]
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY [EPL].[EvnPL_id] desc
    	";
        $result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}

	/**
	 * Загрузка списка
	 */
	function loadEvnVizitPLDispOrpGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.ProfGoal_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.EvnVizitPL_Time,
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(PT.PayType_Name) as PayType_Name,
				RTrim(ST.ServiceType_Name) as ServiceType_Name,
				RTrim(VT.VizitType_Name) as VizitType_Name,
				1 as Record_Status
			from v_EvnVizitPL EVPL with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT with (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST with (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}

	/**
	* Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	*/
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id,
				SocStatus_id,
				ps.UAddress_id as Person_UAddress_id,
				ps.Polis_Ser,
				ps.Polis_Num,
				o.Org_Name,
				o.Org_INN,
				o.Org_OGRN,
				o.UAddress_id as Org_UAddress_id,
				--o.Okved_id,
				os.OrgSmo_Name,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
				+ case when month(ps.Person_Birthday) > month(dbo.tzGetDate())
				or (month(ps.Person_Birthday) = month(dbo.tzGetDate()) and day(ps.Person_Birthday) > day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
			from v_persondisporp pdd with (nolock)
			left join v_PersonState ps with (nolock) on ps.Person_id=pdd.Person_id
			left join v_Job j with (nolock) on j.Job_id=ps.Job_id
			left join v_Org o with (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol with (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os with (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where pdd.Person_id = ?
		";

		$result = $this->db->query($query, array($data['Person_id']));
		$response = $result->result('array');
		
		if ( !is_array($response) || count($response) == 0 )
			return array(array('Error_Msg' => 'Этого человека нет в регистре по диспансеризации детей-сирот!'));
		
		$error = Array();
		if (ArrayVal($response[0], 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($response[0], 'SocStatus_id') == '')
			$errors[] = 'Не заполнен Соц. статус';
		if (ArrayVal($response[0], 'Person_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($response[0], 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($response[0], 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($response[0], 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		if (ArrayVal($response[0], 'Org_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес места работы';
		if (ArrayVal($response[0], 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН места работы';
		if (ArrayVal($response[0], 'Org_OGRN') == '')
			$errors[] = 'Не заполнена ОГРН организации, в которой содержится ребенок';
		

		If (count($error)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array(array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>'.$errstr));
		}
		return array( "Ok", ArrayVal($response[0], 'Sex_id'), ArrayVal($response[0], 'Person_Age'), ArrayVal($response[0], 'Person_Birthday') );
	}

	/**
	 * Сохранение
	 */
    function saveEvnPLDispOrp($data)
    {
		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);
		
		If ( $checkResult[0]!="Ok" ) {
			return $checkResult;
		}
		
		// поверяем, есть ли все обязательные осмотры и исследования, если проставляется законченность случая								
		$err_str = "";
		if ( isset($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 )
		{
			if ( $data['EvnVizitDispOrp'] )
				$test_vizits = $data['EvnVizitDispOrp'];
			else
				$test_vizits = array();
				
			if ( $data['EvnUslugaDispOrp'] )
				$test_usluga = $data['EvnUslugaDispOrp'];
			else
				$test_usluga = array();
			
			if ( isset($data['EvnPLDispOrp_id']) )
			{
				$sel = $this -> loadEvnVizitDispOrpGrid( $data );
				if ( count($sel) > 0 ) {
					foreach ( $sel as $record ) {
						$test_vizits[] = $record;
					}
				}
				
				$sel = $this -> loadEvnUslugaDispOrpGrid($data);
				if ( count($sel) > 0 ) {
					foreach ( $sel as $record ) {
						$test_usluga[] = $record;
					}
				}
			}
			
			// осмотры
			// массив обязательных осмотров
			$vizits_array = array(
				'1' => 'Педиатрия',
				'2' => 'Неврология',
				'3' => 'Офтальмология',
				'4' => 'Детская хирургия',
				'5' => 'Отоларингология',
				//'6' => 'Гинекология',
				'7' => 'Стоматология детская',
				'8' => 'Ортопедия-травматология'
				//'9' => 'Психиатрия',
				//'10' => 'Детская урология-андрология'
				//'11' => 'Детская эндокринология',
			);
			
			$deleted_vizits = array();
			$ped_time = time();
			$pers_time = strtotime($checkResult[3]);
			foreach ( $test_vizits as $key => $record )
			{
				if ( $record['OrpDispSpec_id'] == 1 )
				{
					$ped_time = strtotime($record['EvnVizitDispOrp_setDate']);					
				}
				if ( $record['Record_Status'] == 3 )
						$deleted_vizits[] = $record['EvnVizitDispOrp_id'];
			}
			
			if ( $checkResult[1] == 2 )			
			{
				$vizits_array['6'] = 'Гинекология';
			}
			else 
			{
				//if ( $checkResult[2] >= 5 )
				if ( strtotime('+5 year', $pers_time) < $ped_time )
					$vizits_array['10'] = 'Детская урология-андрология';
			}
			
			//if ( $checkResult[2] >= 3 )
			if ( strtotime('+3 year', $pers_time) < $ped_time )
			{
				$vizits_array['9'] = 'Психиатрия';
			}
			//if ( $checkResult[2] >= 5 )
			if ( strtotime('+5 year', $pers_time) < $ped_time )
			{
				$vizits_array['9'] = 'Детская эндокринология';
			}
			
			if ( $test_vizits )
			{
				$deleted_vizits = array();
				foreach ( $test_vizits as $key => $record ) {
					if ( $record['Record_Status'] != 3 && isset($vizits_array[(string)$record['OrpDispSpec_id']]) && !in_array($record['EvnVizitDispOrp_id'], $deleted_vizits) )
					{
						unset($vizits_array[(string)$record['OrpDispSpec_id']]);
					}
					if ( $record['Record_Status'] == 3 )
						$deleted_vizits[] = $record['EvnVizitDispOrp_id'];
				}
			}
			if ( count($vizits_array) > 0 )
			{
				
				$err_str = "<p>В талоне отсутствуют осмотры следующих специалистов:</p>";
				foreach ( $vizits_array as $value )
				{
					$err_str .= "<p>".$value."</p>";
				}
			}
			
			// исследования
			// массив обязательных исследований
			$usluga_array = array(
				'1' => '02000101	Общий анализ крови',
				'2' => '02000130	Общий анализ мочи',
				'3' => '02001101	Экг старше 15 лет',
				'4' => '02001301	Узи печени',
				'5' => '02001304	Узи почки с надпочечниками',
				'6' => '02001311	УЗИ печени и желчного пузыря'
			);
			if ( $checkResult[2] < 1 )
			{
				$usluga_array['7'] = '02001311	Узи сустава';
			}
			
			if ( $test_usluga )
			{
				$deleted_usluga = array();
				foreach ( $test_usluga as $key => $record ) {
					if ( $record['Record_Status'] != 3 && isset($usluga_array[(string)$record['OrpDispUslugaType_id']]) && !in_array($record['EvnUslugaDispOrp_id'], $deleted_usluga) )
					{
						unset($usluga_array[(string)$record['OrpDispUslugaType_id']]);
					}
					if ( $record['Record_Status'] == 3 )
						$deleted_usluga[] = $record['EvnUslugaDispOrp_id'];
				}
			}
			if ( count($usluga_array) > 0 )
			{
				$err_str .= "<p>&nbsp;</p><p>В талоне отсутствуют следующие исследования:</p>";
				foreach ( $usluga_array as $value )
				{
					$err_str .= "<p>".$value."</p>";
				}
			}

			if ( $err_str != "" )
				return array(array('Error_Msg' => '<p>Случай не может быть закончен!</p><p>&nbsp;</p>' . $err_str));
		}
		
		$procedure = '';
		if ( !isset($data['EvnPLDispOrp_id']) )
		{
			$data['EvnPLDispOrp_setDT'] = date('Y-m-d');
			$data['EvnPLDispOrp_disDT'] = null;
			$data['EvnPLDispOrp_didDT'] = null;
			$data['EvnPLDispOrp_VizitCount'] = 0;
			$procedure = 'p_EvnPLDispOrp_ins';			
		}
		else
		{
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					convert(varchar,cast(EvnPLDispOrp_setDT as datetime),112) as EvnPLDispOrp_setDT,
					convert(varchar,cast(EvnPLDispOrp_disDT as datetime),112) as EvnPLDispOrp_disDT,
					convert(varchar,cast(EvnPLDispOrp_didDT as datetime),112) as EvnPLDispOrp_didDT,					
					EvnPLDispOrp_VizitCount
				from
					v_EvnPLDispOrp with (nolock)
				where EvnPLDispOrp_id = ?
			";
			$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));
			$response = $result->result('array');
			$data['EvnPLDispOrp_setDT'] = $response[0]['EvnPLDispOrp_setDT'];
			$data['EvnPLDispOrp_disDT'] = $response[0]['EvnPLDispOrp_disDT'];
			$data['EvnPLDispOrp_didDT'] = $response[0]['EvnPLDispOrp_didDT'];
			$data['EvnPLDispOrp_VizitCount'] = $response[0]['EvnPLDispOrp_VizitCount'];
			$procedure = 'p_EvnPLDispOrp_upd';
	    }
   		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;
			set @curdate = dbo.tzGetDate();
			set @Res = ?;
			exec " . $procedure . " "
				. "@EvnPLDispOrp_id = @Res output, "
				. "@Lpu_id = ?, "
				. "@Server_id = ?, "
				. "@PersonEvn_id = ?, "
				. "@EvnPLDispOrp_setDT = ?, "
				. "@EvnPLDispOrp_disDT = ?, "
				. "@EvnPLDispOrp_didDT = ?, "
				. "@EvnPLDispOrp_VizitCount = ?, "
				. "@EvnPLDispOrp_IsFinish = ?, "
				. "@AttachType_id = ?, "
				. "@Lpu_aid = ?, "
				//. "@EvnPLDispOrp_IsBud = ?, "
				//. "@Okved_id = ?, "
				. "@pmUser_id = ?, "
				. "@Error_Code = @ErrCode output, "
				. "@Error_Message = @ErrMessage output;
			select @Res as EvnPLDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, 
			array(
				$data['EvnPLDispOrp_id'],
				$data['Lpu_id'],
				$data['Server_id'],
				$data['PersonEvn_id'],
				$data['EvnPLDispOrp_setDT'],
				$data['EvnPLDispOrp_disDT'],
				$data['EvnPLDispOrp_didDT'],
				$data['EvnPLDispOrp_VizitCount'],
				$data['EvnPLDispOrp_IsFinish'],
				$data['AttachType_id'],
				$data['Lpu_aid'],
				//$data['EvnPLDispOrp_IsBud'],
				//$data['EvnPLDispOrp_Okved_id'],
				$data['pmUser_id']
			)
		);
		
		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0)
		{
			return false;
		}
		else if ($response[0]['Error_Msg'])
		{
			return $response;
		}

		if ( !isset($data['EvnPLDispOrp_id']) )
		{
			$data['EvnPLDispOrp_id'] = $response[0]['EvnPLDispOrp_id'];
		}
		
		// Осмотры врача-специалиста
		foreach ($data['EvnVizitDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление посещений
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnVizitDispOrp_del "
						. "@EvnVizitDispOrp_id = ?, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array($record['EvnVizitDispOrp_id'], $data['pmUser_id']));

				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление осмотра врача-специалиста)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении осмотра врача-специалиста'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
			else {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnVizitDispOrp_ins';
				}
				else
				{
					$procedure = 'p_EvnVizitDispOrp_upd';
				}
				
				// проверяем, есть ли уже такое посещение
				$query = "
					select 
						count(*) as cnt
					from
						v_EvnVizitDispOrp with (nolock)
					where
						EvnVizitDispOrp_pid = ?
						and OrpDispSpec_id = ?
						and ( EvnVizitDispOrp_id <> isnull(?, 0) )
				";
				$result = $this->db->query(
					$query,
					array(
						$data['EvnPLDispOrp_id'],
						$record['OrpDispSpec_id'],
						$record['Record_Status'] == 0 ? null : $record['EvnVizitDispOrp_id']
					)
				);
				if (!is_object($result))
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
				}
				$response = $result->result('array');
				if (!is_array($response) || count($response) == 0)
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
				}
				else if ($response[0]['cnt'] >= 1)
				{
					return array(array('Error_Msg' => 'Обнаружено дублирование осмотров, это недопустимо.'));
				}
				// окончание проверки
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = ?;
					exec " . $procedure . " "
						. "@EvnVizitDispOrp_id = @Res output, "
						. "@EvnVizitDispOrp_pid = ?, "
						. "@Lpu_id = ?, "
						. "@Server_id = ?, "
						. "@PersonEvn_id = ?, "
						. "@EvnVizitDispOrp_setDT = ?, "
						. "@EvnVizitDispOrp_disDT = null, "
						. "@EvnVizitDispOrp_didDT = null, "
						. "@LpuSection_id = ?, "
						. "@MedPersonal_id = ?, "
						. "@MedPersonal_sid = null, "
						. "@PayType_id = null, "
						. "@OrpDispSpec_id = ?, "
						. "@Diag_id = ?, "
						. "@HealthKind_id = ?, "
						. "@DeseaseStage_id = ?, "
						. "@DopDispDiagType_id = ?, "
						. "@EvnVizitDispOrp_IsSanKur = ?, "
						. "@EvnVizitDispOrp_IsOut = ?, "
						. "@DopDispAlien_id = ?, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @Res as EvnVizitDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					$record['Record_Status'] == 0 ? null : $record['EvnVizitDispOrp_id'],
					$data['EvnPLDispOrp_id'],
					$data['Lpu_id'],
					$data['Server_id'],
					$data['PersonEvn_id'],
					$record['EvnVizitDispOrp_setDate'],
					$record['LpuSection_id'],
					$record['MedPersonal_id'],
					$record['OrpDispSpec_id'],
					$record['Diag_id'],
					$record['HealthKind_id'],
					(isset($record['DeseaseStage_id']) && $record['DeseaseStage_id'] > 0) ? $record['DeseaseStage_id'] : null,
					(isset($record['DopDispDiagType_id']) && $record['DopDispDiagType_id'] > 0) ? $record['DopDispDiagType_id'] : null,
					$record['EvnVizitDispOrp_IsSanKur'],
					$record['EvnVizitDispOrp_IsOut'],
					$record['DopDispAlien_id'],
					$data['pmUser_id']
				));
				if (!is_object($result))
				{
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
				}
				$response = $result->result('array');
				if (!is_array($response) || count($response) == 0)
				{
					return false;
				}
				else if ($response[0]['Error_Msg'])
				{
					return $response;
				}
				$record['EvnVizitDispOrp_id'] = $response[0]['EvnVizitDispOrp_id'];
			}
		}
		
		// Лабораторные исследования
		foreach ($data['EvnUslugaDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnUslugaDispOrp_del "
						. "@EvnUslugaDispOrp_id = ?, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array($record['EvnUslugaDispOrp_id'], $data['pmUser_id']));

				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
			else {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnUslugaDispOrp_ins';
				}
				else
				{
					$procedure = 'p_EvnUslugaDispOrp_upd';
				}
				
				// проверяем, есть ли уже такое исследование
				$query = "
					select 
						count(*) as cnt
					from
						v_EvnUslugaDispOrp with (nolock)
					where
						EvnUslugaDispOrp_pid = ?
						and OrpDispUslugaType_id = ?
						and ( EvnUslugaDispOrp_id <> isnull(?, 0) )
				";
				$result = $this->db->query(
					$query,
					array(
						$data['EvnPLDispOrp_id'],
						$record['OrpDispUslugaType_id'],
						$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispOrp_id']						
					)
				);
				if (!is_object($result))
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				$response = $result->result('array');
				if (!is_array($response) || count($response) == 0)
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				else if ($response[0]['cnt'] >= 1)
				{
					return array(array('Error_Msg' => 'Обнаружено дублирование исследований, это недопустимо.'));
				}
				// окончание проверки
				if ($record['LpuSection_id']=='')
					$record['LpuSection_id'] = Null;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = ?;
					exec " . $procedure . " "
						. "@EvnUslugaDispOrp_id = @Res output, "
						. "@EvnUslugaDispOrp_pid = ?, "
						. "@Lpu_id = ?, "
						. "@Server_id = ?, "
						. "@PersonEvn_id = ?, "
						. "@EvnUslugaDispOrp_setDT = ?, "
						. "@EvnUslugaDispOrp_disDT = null, "
						. "@EvnUslugaDispOrp_didDT = ?, "
						. "@LpuSection_uid = ?, "
						. "@MedPersonal_id = ?, "
						. "@OrpDispUslugaType_id = ?, "
						. "@Usluga_id = ?, "
						. "@PayType_id = 7, "
						. "@UslugaPlace_id = 1, "
						. "@Lpu_uid = ?, "
						. "@EvnUslugaDispOrp_Kolvo = 1, "
						. "@ExaminationPlace_id = ?, "
						. "@EvnPrescrTimetable_id = null, "
						. "@EvnPrescr_id = null, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @Res as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispOrp_id'],
					$data['EvnPLDispOrp_id'],
					$data['Lpu_id'],
					$data['Server_id'],
					$data['PersonEvn_id'],
					$record['EvnUslugaDispOrp_setDate'],
					$record['EvnUslugaDispOrp_didDate'],
					$record['LpuSection_id'],
					$record['MedPersonal_id'],
					$record['OrpDispUslugaType_id'],
					$record['Usluga_id'],
					$data['Lpu_id'],
					$record['ExaminationPlace_id'],
					$data['pmUser_id']
				));

				if (!is_object($result))
				{
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
				}
				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return false;
				}
				else if ($response[0]['Error_Msg'])
				{
					return $response;
				}

				$record['EvnUslugaDispOrp_id'] = $response[0]['EvnUslugaDispOrp_id'];
			}
		}
		return array(0 => array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'], 'Error_Msg' => ''));
    }

	/**
	 * Поиск талонов по ДД
	 */
    function searchEvnPLDispOrp($data)
    {
		$filter    = "";
		$join_str  = "";

		if ($data['PersonAge_Min'] > $data['PersonAge_Max'])
		{
			return false;
		}
		
		$queryParams = array();

		if (($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0))
		{
			$join_str .= " inner join [Document] with (nolock) on [Document].[Document_id] = [PS].[Document_id]";

			if ($data['DocumentType_id'] > 0)
			{
				$join_str .= " and [Document].[DocumentType_id] = :DocumentType_id";
				$queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}

			if ($data['OrgDep_id'] > 0)
			{
				$join_str .= " and [Document].[OrgDep_id] = :OrgDep_id";
				$queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}
		}

		if (($data['OMSSprTerr_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['PolisType_id'] > 0))
		{
			$join_str .= " inner join [Polis] with (nolock) on [Polis].[Polis_id] = [PS].[Polis_id]";

			if ($data['OMSSprTerr_id'] > 0)
			{
				$join_str .= " and [Polis].[OmsSprTerr_id] = :OMSSprTerr_id";
				$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
			}

			if ($data['OrgSmo_id'] > 0)
			{
				$join_str .= " and [Polis].[OrgSmo_id] = :OrgSmo_id";
				$queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
			}

			if ($data['PolisType_id'] > 0)
			{
				$join_str .= " and [Polis].[PolisType_id] = :PolisType_id";
				$queryParams['PolisType_id'] = $data['PolisType_id'];
			}
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0))
		{
			$join_str .= " inner join [Job] with (nolock) on [Job].[Job_id] = [PS].[Job_id]";

			if ($data['Org_id'] > 0)
			{
				$join_str .= " and [Job].[Org_id] = :Org_id";
				$queryParams['Org_id'] = $data['Org_id'];
			}

			if ($data['Post_id'] > 0)
			{
				$join_str .= " and [Job].[Post_id] = :Post_id";
				$queryParams['Post_id'] = $data['Post_id'];
			}
		}

		if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0))
		{
			$join_str .= " inner join [Address] with (nolock) on [Address].[Address_id] = [PS].[UAddress_id]";

			if ($data['KLRgn_id'] > 0)
			{
				$filter .= " and [Address].[KLRgn_id] = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			if ($data['KLSubRgn_id'] > 0)
			{
				$filter .= " and [Address].[KLSubRgn_id] = :KLSubRgn_id";
				$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if ($data['KLCity_id'] > 0)
			{
				$filter .= " and [Address].[KLCity_id] = :KLCity_id";
				$queryParams['KLCity_id'] = $data['KLCity_id'];
			}

			if ($data['KLTown_id'] > 0)
			{
				$filter .= " and [Address].[KLTown_id] = :KLTown_id";
				$queryParams['KLTown_id'] = $data['KLTown_id'];
			}

			if ($data['KLStreet_id'] > 0)
			{
				$filter .= " and [Address].[KLStreet_id] = :KLStreet_id";
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (strlen($data['Address_House']) > 0)
			{
				$filter .= " and [Address].[Address_House] = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}
		}

		if ( isset($data['EvnPLDispOrp_disDate'][1]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_disDate] <= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate1'] = $data['EvnPLDispOrp_disDate'][1];
		}

		if ( isset($data['EvnPLDispOrp_disDate'][0]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_disDate] >= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate0'] = $data['EvnPLDispOrp_disDate'][0];
		}

		if ($data['EvnPLDispOrp_IsFinish'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_IsFinish] = :EvnPLDispOrp_IsFinish";
			$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
		}

		if ( isset($data['EvnPLDispOrp_setDate'][1]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_setDate] <= :EvnPLDispOrp_setDate1";
			$queryParams['EvnPLDispOrp_setDate1'] = $data['EvnPLDispOrp_setDate'][1];
		}

		if ( isset($data['EvnPLDispOrp_setDate'][0]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_setDate] >= :EvnPLDispOrp_setDate0";
			$queryParams['EvnPLDispOrp_setDate0'] = $data['EvnPLDispOrp_setDate'][0];
		}

		if ($data['PersonAge_Max'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[Person_Age] <= :PersonAge_Max";
			$queryParams['PersonAge_Max'] = $data['PersonAge_Max'];
		}

		if ($data['PersonAge_Min'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[Person_Age] >= :PersonAge_Min";
			$queryParams['PersonAge_Min'] = $data['PersonAge_Min'];
		}

		if (($data['PersonCard_Code'] != '') || ($data['LpuRegion_id'] > 0))
		{
			$join_str .= " inner join [v_PersonCard] PC with (nolock) on [PC].[Person_id] = [PS].[Person_id]";
			
			if (strlen($data['PersonCard_Code']) > 0)
			{
				$filter .= " and [PC].[PersonCard_Code] = :PersonCard_Code";
				$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (strlen($data['LpuRegion_id']) > 0)
			{
				$filter .= " and [PC].[LpuRegion_id] = :LpuRegion_id";
				$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			}
		}
		if ( isset($data['Person_Birthday'][1]) )
		{
			$filter .= " and [PS].[Person_Birthday] <= :Person_Birthday1";
			$queryParams['Person_Birthday1'] = $data['Person_Birthday'][1];
		}

		if ( isset($data['Person_Birthday'][0]) )
		{
			$filter .= " and [PS].[Person_Birthday] >= :Person_Birthday0";
			$queryParams['Person_Birthday0'] = $data['Person_Birthday'][0];
		}

		if (strlen($data['Person_Firname']) > 0)
		{
			$filter .= " and [PS].[Person_Firname] like :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname']."%";
		}

		if (strlen($data['Person_Secname']) > 0)
		{
			$filter .= " and [PS].[Person_Secname] like :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname']."%";
		}

		if ($data['Person_Snils'] > 0)
		{
			$filter .= " and [PS].[Person_Snils] = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (strlen($data['Person_Surname']) > 0)
		{
			$filter .= " and [PS].[Person_Surname] like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname']."%";
		}

		if ($data['PrivilegeType_id'] > 0)
		{
			$join_str .= " inner join [v_PersonPrivilege] [PP] with (nolock) on [PP].[Person_id] = [EvnPLDispOrp].[Person_id] and [PP].[PrivilegeType_id] = :PrivilegeType_id and [PP].[PersonPrivilege_begDate] is not null and [PP].[PersonPrivilege_begDate] <= dbo.tzGetDate() and ([PP].[PersonPrivilege_endDate] is null or [PP].[PersonPrivilege_endDate] >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)) and [PP].[Lpu_id] = :Lpu_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];			
			$queryParams['Lpu_id'] = $data['Lpu_id'];			
		}	

		if ($data['Sex_id'] >= 0)
		{
			$filter .= " and [PS].[Sex_id] = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if ($data['SocStatus_id'] > 0)
		{
			$filter .= " and [PS].[SocStatus_id] = :SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		$query = "
			SELECT DISTINCT TOP 100
				[EvnPLDispOrp].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
				[EvnPLDispOrp].[Person_id] as [Person_id],
				[EvnPLDispOrp].[Server_id] as [Server_id],
				[EvnPLDispOrp].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnPLDispOrp].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
			FROM [v_EvnPLDispOrp] [EvnPLDispOrp] with (nolock)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EvnPLDispOrp].[Person_id]
				left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EvnPLDispOrp].[EvnPLDispOrp_IsFinish]
				" . $join_str . "
			WHERE (1 = 1)
				" . $filter . "
		";

		$result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
    }
	
	/**
	 * Получение списка записей для потокового ввода
	 */
	function getEvnPLDispOrpStreamList($data)
    {

		$query = "
			SELECT DISTINCT TOP 100
				[EvnPLDispOrp].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
				[EvnPLDispOrp].[Person_id] as [Person_id],
				[EvnPLDispOrp].[Server_id] as [Server_id],
				[EvnPLDispOrp].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) + ' ' + RTRIM([PS].[Person_Firname]) + ' ' + RTRIM([PS].[Person_Secname]) as [Person_Fio],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnPLDispOrp].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
			FROM [v_EvnPLDispOrp] [EvnPLDispOrp] with (nolock)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EvnPLDispOrp].[Person_id]
				left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EvnPLDispOrp].[EvnPLDispOrp_IsFinish]
			WHERE EvnPLDispOrp_updDT >= ? and [EvnPLDispOrp].pmUser_updID= ? ";

		$result = $this->db->query($query, array($data['begDate']." ".$data['begTime'], $data['pmUser_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
    }
	
	/**
	* Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	*/
	function getEvnPLDispOrpYears($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispOrp_id) as count,
				year(EvnPLDispOrp_setDate) as EvnPLDispOrp_Year
			FROM
				v_EvnPLDispOrp with (nolock)
			WHERE
				Lpu_id = ?
				and year(EvnPLDispOrp_setDate) <= 2012
			GROUP BY
				year(EvnPLDispOrp_setDate)
			ORDER BY
				year(EvnPLDispOrp_setDate)
		";

		$res = $this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }
	
	/**
	* Проверка, есть ли талон на этого человека в этом году
	*/
	function checkIfEvnPLDispOrpExists($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispOrp_id) as count
			FROM
				v_EvnPLDispOrp with (nolock)
			WHERE
				Person_id = ? and Lpu_id = ? and year(EvnPLDispOrp_setDate) = year(dbo.tzGetDate())
		";

		$res = $this->db->query($sql, array($data['Person_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 )
				return array(array('Error_Msg' => '', 'isEvnPLDispOrpExists' => false));
			else
				return array(array('Error_Msg' => '', 'isEvnPLDispOrpExists' => true));
		}
 	    else
 	    	return false;
    }
}
?>