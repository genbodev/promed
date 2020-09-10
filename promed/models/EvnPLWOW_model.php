<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLWOW_model - модель для работы с талонами углубленных обследований ВОВ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author				Марков Андрей
* @version			14.03.2010
*/

require_once('EvnPL_model.php');
class EvnPLWOW_model extends EvnPL_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 35;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLWow';
	}

	/**
	 * Получение данных с формы редактирования
	 * Входящие данные: $data['EvnPLWOW_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnPLWOWEditForm($data)
	{
		$query = "
			SELECT 
				EvnPLWOW.EvnPLWOW_id,
				EvnPLWOW.EvnPLWOW_IsFinish,
				EvnPLWOW.ResultClass_id,
				cast(EvnPLWOW.EvnPLWOW_UKL as float) as EvnPLWOW_UKL,
				EvnPLWOW.Person_id,
				EvnPLWOW.PersonEvn_id,
				EvnPLWOW.Server_id,
				EvnPLWOW.Lpu_id
			FROM
				v_EvnPLWOW EvnPLWOW with (nolock)
			WHERE
				(1 = 1)
				and EvnPLWOW.EvnPLWOW_id = ?
				and EvnPLWOW.Lpu_id = ?
		";
		/*
		echo getDebugSql($query,  array($data['EvnPLWOW_id'], $data['Lpu_id']));
		exit;
		*/
		$result = $this->db->query($query, array($data['EvnPLWOW_id'], $data['Lpu_id']));
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
	 * Получение списка осмотров врача-специалиста в талоне угл. обсл.
	 * Входящие данные: $data['EvnPLWOW_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitPLWOW($data)
	{
		
		$query = "
			select
				EVPLW.EvnVizitPLWOW_id,
				convert(varchar(10), EVPLW.EvnVizitPLWOW_setDate, 104) as EvnVizitPLWOW_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EVPLW.MedPersonal_id,
				RTRIM(MP.Person_Fio) as MedPersonal_FIO,
				EVPLW.DispWOWSpec_id,
				RTRIM(DWS.DispWOWSpec_Name) as DispWOWSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVPLW.LpuSection_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EVPLW.Diag_id
			from v_EvnVizitPLWOW EVPLW with (nolock)
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EVPLW.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPLW.MedPersonal_id
				left join DispWowSpec DWS with (nolock) on DWS.DispWowSpec_id = EVPLW.DispWowSpec_id
				left join Diag D with (nolock) on D.Diag_id = EVPLW.Diag_id
			where EVPLW.EvnVizitPLWOW_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLWOW_id']));
		/*
		echo getDebugSql($query, array($data['EvnPLWOW_id']));
		exit;
		*/
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
	 * Получение списка обследований в талоне по ДД
	 * Входящие данные: $data['EvnPLWOW_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaWOW($data)
	{
		$filter = '(1=1)';
		$params = array();
		
		if (isset($data['Lpu_id']))
		{
			$filter .= " and EUW.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		
		if (isset($data['EvnPLWOW_id']))
		{
			$filter .= " and EUW.EvnUslugaWOW_pid = :EvnPLWOW_id";
			$params['EvnPLWOW_id'] = $data['EvnPLWOW_id'];
		}
		
		if (isset($data['EvnUslugaWOW_id']))
		{
			$filter .= " and EUW.EvnUslugaWOW_id = :EvnUslugaWOW_id";
			$params['EvnUslugaWOW_id'] = $data['EvnUslugaWOW_id'];
		}
		
		$query = "
			select
				EUW.EvnUslugaWOW_id,
				EUW.EvnUslugaWOW_pid as EvnPLWOW_id,
				convert(varchar(10), EUW.EvnUslugaWOW_setDate, 104) as EvnUslugaWOW_setDate,
				convert(varchar(10), EUW.EvnUslugaWOW_didDate, 104) as EvnUslugaWOW_didDate,
				EUW.DispWowUslugaType_id,
				EUW.PersonEvn_id,
				RTRIM(DWUT.DispWowUslugaType_Name) as DispWowUslugaType_Name,
				EUW.LpuSection_uid as LpuSection_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EUW.MedPersonal_id,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EUW.Usluga_id as Usluga_id,
				RTRIM(U.Usluga_Name) as Usluga_Name,
				RTRIM(U.Usluga_Code) as Usluga_Code
			from v_EvnUslugaWOW EUW with (nolock)
				left join DispWowUslugaType DWUT with (nolock) on DWUT.DispWowUslugaType_id = EUW.DispWowUslugaType_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EUW.LpuSection_uid
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EUW.MedPersonal_id
				left join v_Usluga U with (nolock) on U.Usluga_id = EUW.Usluga_id
			where {$filter}
		";
		
		$result = $this->db->query($query, $params);
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
	 * Загрузка потокового ввода
	 */
	function loadEvnPLWOWStreamList($data)
	{
		$filter = '';
		$params = array();
		$filter .= " and EPW.pmUser_insID = :pmUser_id ";
		$params['pmUser_id'] = $data['pmUser_id'];

		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']))
		{
			$filter .= " and EPW.EvnPLWOW_insDT >= :date_time";
			$params['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

		if (isset($data['Lpu_id']))
		{
			$filter .= " and EPW.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT DISTINCT TOP 100
				EPW.EvnPLWOW_id as EvnPLWOW_id,
				EPW.Person_id as Person_id,
				EPW.Server_id as Server_id,
				EPW.PersonEvn_id as PersonEvn_id,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EPW.EvnPLWOW_setDate, 104) as EvnPLWow_setDate,
				convert(varchar(10), EPW.EvnPLWOW_disDate, 104) as EvnPLWow_disDate,
				EPW.EvnPLWOW_VizitCount as EvnPLWow_VizitCount,
				IsFinish.YesNo_Name as EvnPLWOW_IsFinish,
				PTW.PrivilegeTypeWow_id,
				PTW.PrivilegeTypeWOW_Name
			FROM v_EvnPLWOW EPW with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPW.Person_id
				-- здесь должен быть inner
				left join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id
				left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
				left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPW.EvnPLWow_IsFinish
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY EPW.EvnPLWOW_id desc
			";
		$result = $this->db->query($query, $params);
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
	 * Получение списка
	 */
	function loadEvnVizitPLDispDopGrid($data)
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
				o.Okved_id,
				os.OrgSmo_Name
			from v_persondopdisp pdd with (nolock)
			left join v_PersonState ps with (nolock) on ps.Person_id=pdd.Person_id
			left join v_Job j with (nolock) on j.Job_id=ps.Job_id
			left join v_Org o with (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol with (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os with (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where PersonEvn_id = ?
		";

		$result = $this->db->query($query, array($data['PersonEvn_id']));
		$response = $result->result('array');
		
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
			$errors[] = 'Не заполнена ОГРН места работы';
		if (ArrayVal($response[0], 'Okved_id') == '')
			$errors[] = 'Не заполнен ОКВЭД места работы';
		
		If (count($error)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>'.$errstr);
		}
		return "Ok";
	}

	/**
	 * Сохранение карты вов
	 */
	function saveEvnPLWOW($data)
	{
		/* Если надо будет проверку по различным параметрам - можно включить ее здесь 
		$checkResult = $this->checkPersonData($data);
		If ($checkResult!="Ok") {
			return $checkResult;
		}
		*/
		// Результат лечения
		if ($data['save']!=0)
		if ( ($data['EvnPLWOW_IsFinish'] == 2) && (!isset($data['ResultClass_id'])) ) 
		{
			return array(array('Error_Code' => 100002, 'Error_Msg' => 'Ошибка при сохранении талона угл. иссл. (не заполнено поле "Результат лечения")'));
		}
		// УКЛ
		if ( ($data['EvnPLWOW_IsFinish'] == 2) && ((!isset($data['EvnPLWOW_UKL'])) || ($data['EvnPLWOW_UKL'] <= 0) || ($data['EvnPLWOW_UKL'] > 1)) ) 
		{
			return array(array('Error_Code' => 100002, 'Error_Msg' => 'Ошибка при сохранении талона угл. иссл. (неверно задано значение поля "УКЛ")'));
		}
		
		$proc = '';
		if (!isset($data['EvnPLWOW_id']))
		{
			$proc = 'p_EvnPLWOW_ins';
		}
		else
		{
			$proc = 'p_EvnPLWOW_upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;
			set @curdate = dbo.tzGetDate();
			set @Res = :EvnPLWOW_id;
			exec " . $proc . " 
				@EvnPLWOW_id = @Res output, 
				@Lpu_id = :Lpu_id, 
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 
				@EvnPLWOW_setDT = @curdate, 
				@EvnPLWOW_disDT = null, 
				@EvnPLWOW_didDT = null, 
				@EvnPLWOW_VizitCount = 0, 
				@EvnPLWOW_IsFinish = :EvnPLWOW_IsFinish, 
				@ResultClass_id = :ResultClass_id, 
				@EvnPLWOW_UKL = :EvnPLWOW_UKL, 
				@pmUser_id = :pmUser_id, 
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMessage output;
			select @Res as EvnPLWOW_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$params = array(
			'EvnPLWOW_id'=>$data['EvnPLWOW_id'], 
			'Lpu_id'=>$data['Lpu_id'], 
			'Server_id'=>$data['Server_id'], 
			'PersonEvn_id'=>$data['PersonEvn_id'], 
			'EvnPLWOW_IsFinish'=>$data['EvnPLWOW_IsFinish'], 
			'ResultClass_id'=>$data['ResultClass_id'], 
			'EvnPLWOW_UKL'=>$data['EvnPLWOW_UKL'], 
			'pmUser_id'=>$data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$res = $this->db->query($query, $params);
		
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка
	 */
	function checkUniDispWowUslugaType($data)
	{
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnUslugaWOW_id']>0)
		{
			$filter .= " and EUW.EvnUslugaWow_id != :EvnUslugaWOW_id";
			$params['EvnUslugaWOW_id'] = $data['EvnUslugaWOW_id'];
		}
		if ($data['EvnPLWOW_id']>0)
		{
			$filter .= " and EUW.EvnUslugaWOW_pid = :EvnUslugaWOW_pid";
			$params['EvnUslugaWOW_pid'] = $data['EvnPLWOW_id'];
		}
		
		if ($data['DispWowUslugaType_id']>0)
		{
			$filter .= " and EUW.DispWowUslugaType_id = :DispWowUslugaType_id";
			$params['DispWowUslugaType_id'] = $data['DispWowUslugaType_id'];
		}
		$sql = "
		Select 
			count(*) as record_count
			from v_EvnUslugaWow EUW with (nolock)
			where
				{$filter}
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter .= " and EUW.Lpu_id = :Lpu_id";
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка
	 */
	function checkUniWowCard($data)
	{
		$filter = "";
		$params = array();
		if ($data['EvnPLWOW_id']>0)
		{
			$filter .= " and EvnPLWow.EvnPLWOW_id != :EvnPLWOW_id";
			$params['EvnPLWOW_id'] = $data['EvnPLWOW_id'];
		}
		$params['Lpu_id'] = $data['Lpu_id'];
		if ($data['Person_id']>0)
		{
			$params['Person_id'] = $data['Person_id'];
		}
		else 
		{
			return false;
		}
		$params['Lpu_id'] = $data['Lpu_id'];
		
		$sql = "
			Declare @Person_id as bigint = :Person_id;
			Declare @Lpu_id as bigint = :Lpu_id;
			Declare @NeLpu as int;
			Declare @Lpu as int;
			Declare @Lpu_Nick as char(100) = '';
			Select 
				@NeLpu = Sum(case when EvnPLWow.Lpu_id!=@Lpu_id then 1 else 0 end),
				@Lpu = Sum(case when EvnPLWow.Lpu_id=@Lpu_id then 1 else 0 end)
			from v_EvnPLWow EvnPLWow with (nolock)
			where 
				EvnPLWow.Person_id = @Person_id {$filter}
				and YEAR(EvnPLWow.EvnPLWow_setDate) = YEAR(dbo.tzGetDate())
				
			if ((@Lpu=0) and (@NeLpu>0))
				Set @Lpu_Nick = 
				(
					Select top 1 Lpu_Nick from v_EvnPLWow EvnPLWow with (nolock)
					inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EvnPLWow.Lpu_id
					where EvnPLWow.Person_id = @Person_id and EvnPLWow.Lpu_id != @Lpu_id {$filter}
				)
			Select @NeLpu as NeLpu, @Lpu as Lpu, @Lpu_Nick as Lpu_Nick
		";

		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка дубля человека
	 */
	function checkDoublePerson($data)
	{
		$data['EvnPLWOW_id'] = 0;
		$result = $this->checkUniWowCard($data);
		if (is_array($result) && (count($result) > 0))
		{
			if ($result[0]['Lpu']>0)
			{
				$result[0]['success'] = false;
				$result[0]['Error_Code'] = 100010;
				$result[0]['Error_Msg'] = 'На данного человека уже добавлен талон по углубленному осмотру ВОВ.';
			}
			elseif ($result[0]['NeLpu']>0)
			{
				$result[0]['success'] = true;
				$result[0]['Error_Code'] = 100011;
				$result[0]['Error_Msg'] = '<b>Обратите внимание!</b><br/> На данного человека талон заведен в ЛПУ: <br/>'.$result[0]['Lpu_Nick'];
			}
			else 
			{
				$result[0]['success'] = true;
				$result[0]['Error_Msg'] = '';
			}
			return $result;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Проверка на заполненность всех необходимых осмотров
	 */
	function checkIsVizit($data)
	{
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnPLWOW_id']>0)
		{
			$params['EvnPLWOW_id'] = $data['EvnPLWOW_id'];
		}
		else 
		{
			return false;
		}
		//Запрос изменет в соответствие с задачей 10729 - теперь обязателен ТОЛЬКО осмотр терапевта
		$sql = "
			Declare @Sex_id as bigint
			Declare @EvnPLWow_id as bigint = :EvnPLWOW_id;
			Declare @Lpu_id as bigint = :Lpu_id;
			
			Set @Sex_id = (Select Sex_id from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id)
			
			Select 
			dws.DispWowSpec_id,
			dws.DispWowSpec_Name,
			@Sex_id Sex_id
			from DispWowSpec dws with (nolock)
			left join v_EvnVizitPLWow EvnVizit with (nolock) on dws.DispWowSpec_id = EvnVizit.DispWowSpec_id and EvnVizit.EvnVizitPLWow_pid = @EvnPLWow_id
			where 
			EvnVizit.EvnVizitPLWow_id is null
			and dws.DispWowSpec_id = 1
			/*and
			((@Sex_id = 1 and dws.DispWowSpec_id in (1,2,3,4,5,6,8,9)) or 
			(@Sex_id = 2 and dws.DispWowSpec_id in (1,2,3,4,5,6,7,8,9)))
			group by dws.DispWowSpec_id, dws.DispWowSpec_Name*/
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка
	 */
	function checkIsUsluga($data)
	{
		$filter = "(1=1)";
		$params = array();
		if ($data['EvnPLWOW_id']>0)
		{
			$params['EvnPLWOW_id'] = $data['EvnPLWOW_id'];
		}
		else 
		{
			return false;
		}
		$sql = "
			Declare @Sex_id as bigint
			Declare @Person_Age as int
			Declare @EvnPLWow_id as bigint = :EvnPLWOW_id;
			Declare @Lpu_id as bigint = :Lpu_id;
			
				Select @Sex_id = Sex_id, @Person_Age = EvnPLWow.Person_Age from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id
			
			Select 
			dwut.DispWowUslugaType_id,
			dwut.DispWowUslugaType_Name,
			@Sex_id Sex_id
			from DispWowUslugaType dwut with (nolock)
			left join v_EvnUslugaWow EvnUsluga with (nolock) on dwut.DispWowUslugaType_id = EvnUsluga.DispWowUslugaType_id and EvnUsluga.EvnUslugaWow_pid = @EvnPLWow_id
			where 
			EvnUsluga.EvnUslugaWow_id is null and
			--((@Sex_id = 1 and dwut.DispWowUslugaType_id in (1,2,3,7,8,10,12,13,14,15,16)) or
			--(@Sex_id = 2 and dwut.DispWowUslugaType_id in (1,2,3,4,5,6,8,9,10,11,12,13,14,15,16)))
			--((@Sex_id = 1 and dwut.DispWowUslugaType_id in (1,2,10,12,13,14,15)) or
			--(@Sex_id = 2 and dwut.DispWowUslugaType_id in (1,2,9,10,12,13,14,15)))
			-- or (@Sex_id = 2 and dwut.DispWowUslugaType_id = 11 and @Person_Age>=45))
			dwut.DispWowUslugaType_id in (1,2,13)
			group by dwut.DispWowUslugaType_id, dwut.DispWowUslugaType_Name
		";
		$params['Lpu_id'] = $data['Lpu_id'];
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка
	 */
	function CheckUslugaVisit($data, $index)
	{
		//Если index = 1 - то проверяем, введены ли те исследования, на которые нужен осмотр офтальмолога (и введен ли этот осмотр)
		//Если index = 2 - делаем аналогично по осмотру акушера-гинеколога

		if ($index == 1)
		{
			$filter_Usluga = "and (dwut.DispWowUslugaType_id = 15 or dwut.DispWowUslugaType_id = 16)";
			$filter_Vizit = "and dws.DispWowSpec_id = 5";
		}
		if ($index == 2)
		{
			$filter_Usluga = "and dwut.DispWowUslugaType_id = 9";
			$filter_Vizit = "and dws.DispWowSpec_id = 7";
		}

		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['EvnPLWOW_id'] = $data['EvnPLWOW_id'];

		$sql_Usluga = "Declare @Sex_id as bigint
			Declare @Person_Age as int
			Declare @EvnPLWow_id as bigint = :EvnPLWOW_id;
			Declare @Lpu_id as bigint = :Lpu_id;

				Select @Sex_id = Sex_id, @Person_Age = EvnPLWow.Person_Age from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id

			Select
			dwut.DispWowUslugaType_id,
			dwut.DispWowUslugaType_Name,
			@Sex_id Sex_id
			from DispWowUslugaType dwut with (nolock)
			left join v_EvnUslugaWow EvnUsluga with (nolock) on dwut.DispWowUslugaType_id = EvnUsluga.DispWowUslugaType_id and EvnUsluga.EvnUslugaWow_pid = @EvnPLWow_id
			where
			EvnUsluga.EvnUslugaWow_id is null ".$filter_Usluga;
		
		$sql_Visit = "Declare @Sex_id as bigint
			Declare @EvnPLWow_id as bigint = :EvnPLWOW_id;
			Declare @Lpu_id as bigint = :Lpu_id;

			Set @Sex_id = (Select Sex_id from v_EvnPLWow EvnPLWow with (nolock)
				inner join v_PersonState ps with (nolock) on EvnPLWow.Person_id = ps.Person_id
				where EvnPLWow.EvnPLWow_id = @EvnPLWow_id and EvnPLWow.Lpu_id = @Lpu_id)

			Select
			dws.DispWowSpec_id,
			dws.DispWowSpec_Name,
			@Sex_id Sex_id
			from DispWowSpec dws with (nolock)
			left join v_EvnVizitPLWow EvnVizit with (nolock) on dws.DispWowSpec_id = EvnVizit.DispWowSpec_id and EvnVizit.EvnVizitPLWow_pid = @EvnPLWow_id
			where
			EvnVizit.EvnVizitPLWow_id is null ".$filter_Vizit;

		$res = $this->db->query($sql_Usluga, $params);

		if (count($res->result('array')) < 2) //Если данное обследование заведено, проверяем осмотры
		{
			$res = $this->db->query($sql_Visit,$params);

			if (count($res->result('array')) == 0) //Необходимый осмотр заведен
				return true;
			else
				return false; //Обследование заведено, а необходимый осмотр - нет
		}
		else
		{
			return true; //Данные обследования не заведены
		}

	}

	/**
	 * Сохранение услуги ВОВ
	 */
	function saveEvnUslugaWOW($data)
	{
		$proc = '';
		
		if (!isset($data['EvnUslugaWOW_id']))
		{
			$proc = 'p_EvnUslugaWOW_ins';
		}
		else
		{
			$proc = 'p_EvnUslugaWOW_upd';
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;
			set @curdate = dbo.tzGetDate();
			set @Res = :EvnUslugaWOW_id;
			exec " . $proc . " 
				@EvnUslugaWOW_id = @Res output, 
				@EvnUslugaWOW_pid = :EvnPLWOW_id, 
				@Lpu_id = :Lpu_id, 
				@Server_id = :Server_id, 
				@PayType_id = :PayType_id,
				@UslugaPlace_id = 1,
				@PersonEvn_id = :PersonEvn_id, 
				@EvnUslugaWOW_setDT = :EvnUslugaWOW_setDT, 
				@EvnUslugaWOW_didDT = :EvnUslugaWOW_didDT, 
				@Usluga_id = :Usluga_id,
				@MedPersonal_id = :MedPersonal_id,
				@LpuSection_uid = :LpuSection_uid,
				@DispWOWUslugaType_id = :DispWOWUslugaType_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id, 
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMessage output;
			select @Res as EvnUslugaWOW_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$params = array(
			'EvnUslugaWOW_id'=>$data['EvnUslugaWOW_id'], 
			'EvnPLWOW_id'=>$data['EvnPLWOW_id'], 
			'Lpu_id'=>$data['Lpu_id'], 
			'Server_id'=>$data['Server_id'], 
			'PersonEvn_id'=>$data['PersonEvn_id'], 
			'EvnUslugaWOW_setDT'=>$data['EvnUslugaWOW_setDate'], 
			'EvnUslugaWOW_didDT'=>$data['EvnUslugaWOW_didDate'], 
			'Usluga_id'=>$data['Usluga_id'], 
			'PayType_id'=>$this->getFirstResultFromQuery("select top 1 PayType_id from PayType with(nolock) where PayType_Code = 1"),
			'MedPersonal_id'=>$data['MedPersonal_id'],
			'LpuSection_uid'=>$data['LpuSection_id'], 
			'DispWOWUslugaType_id'=>$data['DispWowUslugaType_id'], 
			'pmUser_id'=>$data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$res = $this->db->query($query, $params);
		
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
}
?>