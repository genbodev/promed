<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * InetPerson_model - модель для работы с людьми из портала самозаписи (бд UserPortal)..
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Dmitry Valsenko
 * @version      03.02.2012
 */
class InetPerson_model extends swModel
{
	/**
	 * construct
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 *
	 * @param type $data
	 * @return int 
	 */
	function loadInetPersonGrid($data) 
	{
		$params = array();
		$filter = "(1=1)";
		if( !empty($data['ModerateType_id']) && $data['ModerateType_id'] != 1 ) {
			$NOT = "";
			if($data['ModerateType_id'] == 2) { $NOT = 'NOT'; }

			$filter .= " and P.Person_IsModerated IS {$NOT} NULL";
		}
		
		if (!empty($data['Person_Surname'])) {
			$filter .= " and P.Person_Surname LIKE :Person_Surname + '%'";
			$params['Person_Surname'] = $data['Person_Surname'];
		}
		
		if (!empty($data['Person_Firname'])) {
			$filter .= " and P.Person_Firname LIKE :Person_Firname + '%'";
			$params['Person_Firname'] = $data['Person_Firname'];
		}
		
		if (!empty($data['Person_Secname'])) {
			$filter .= " and P.Person_Secname LIKE :Person_Secname + '%'";
			$params['Person_Secname'] = $data['Person_Secname'];
		}
		
		if (!empty($data['Polis_Ser'])) {
			$filter .= " and P.Polis_Ser = :Polis_Ser";
			$params['Polis_Ser'] = $data['Polis_Ser'];
		}
		
		if (!empty($data['Polis_Num'])) {
			$filter .= " and P.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		
		if (!empty($data['Person_Phone'])) {
			$filter .= " and P.Person_Phone = :Person_Phone";
			$params['Person_Phone'] = $data['Person_Phone'];
		}
		
		$query = "
			Select
				-- select
				P.Person_id,
				P.Person_Surname,
				P.Person_Firname,
				P.Person_Secname,
				P.Polis_Ser,
				P.Polis_Num,
				P.Person_Phone,
				A.Address_Address,
				P.Person_IsModerated,
				convert(varchar,P.Person_BirthDate,104) as Person_BirthDate,
				convert(varchar,P.Person_insDT,104) as Person_insDT,
				U.username
				-- end select
			from
				-- from
				Person P (nolock)
				left join Address A (nolock) on P.Address_id = A.Address_id
				left join users U (nolock) on U.id = P.pmUser_id
			-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				P.Person_insDT
				-- end order by";
		/*
		 echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		 echo getDebugSql(getCountSQLPH($query), $params);
		 exit;
		*/

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadInetPersonModerationEditWindow($data)
	{
		$query = "
			select
				P.Person_id,
				P.Person_mainId,
				P.Person_Surname,
				P.Person_Firname,
				P.Person_Secname,
				P.PersonSex_id,
				P.Person_Phone,
				convert(varchar,P.Person_BirthDate,104) as Person_BirthDate,
				P.Polis_Ser,
				P.Polis_Num,
				A.KLCountry_id,
				A.KLRgn_id,
				A.KLSubRgn_id,
				A.KLCity_id,
				A.KLTown_id,
				A.KLStreet_id,
				A.Address_House,
				A.Address_Corpus,
				A.Address_Flat,
				A.Address_Address
			from
				Person P (nolock)
				left join Address A (nolock) on P.Address_id = A.Address_id
				left join users U (nolock) on U.id = P.pmUser_id
			where
				P.Person_id = :Person_id
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
	 *
	 * @param type $data
	 * @return type 
	 */
	function cancelInetPersonModeration($data)
	{
		$ar = array();
		
		$query = "
			select
				pmUser_id,
				rtrim(Person_Surname) as Person_Surname,
				rtrim(Person_Firname) as Person_Firname,
				rtrim(Person_Secname) as Person_Secname,
				rtrim(Polis_Ser) as Polis_Ser,
				rtrim(Polis_Num) as Polis_Num,
				case
					when PersonSex_id = 1
					then 'Мужской'
					when PersonSex_id = 2
					then 'Женский'
				end as Sex_Name,
				convert(varchar,P.Person_BirthDate,104) as Person_BirthDate,
				rtrim(u.first_name) as FirstName,
				rtrim(u.second_name) as MidName,
				rtrim(u.email) as EMail,
				a.Address_Address,
				p.Person_Phone,
				p.PersonSex_id,
				p.Person_updDT
			from Person p (nolock)
				left join Address a (nolock) on p.Address_id = a.Address_id
				left join users u (nolock) on u.id = p.pmUser_id
			where 
				Person_Id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				$ar = $response[0];
			} else {
				return false;
			}
		}
		else {
			return false;
		}
		
		$query = "
			delete from Person
			where Person_id = :Person_id;				
		";
		$this->db->query($query, $data);

		return $ar;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function personModerationFail($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMsg varchar(4000);
		
			exec p_Person_ModerateFail
				@pmUser_id = :pmUser_id,
				@User_id = :User_id,
				@Lpu_id = :Lpu_id,
				@Person_Surname = :Person_Surname,
				@Person_Firname = :Person_Firname,
				@Person_Secname = :Person_Secname,
				@PersonSex_id = :PersonSex_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Person_BirthDate = :Person_BirthDate,
				@PersonModeration_FailComment = :PersonModeration_FailComment,
				@Person_insDT = :Person_insDT,
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$this->db->query($query, $data);
		
		return true;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkPersonDouble($data)
	{
		
		$queryParams = array();

		$queryParams['Person_Surname'] = preg_replace('/[ё]/iu', 'Е', trim($data['Person_Surname']));
		$queryParams['Person_Firname'] = preg_replace('/[ё]/iu', 'Е', trim($data['Person_Firname']));

		$queryParams['Person_id'] = NULL;
		
		if (mb_strlen($data['Person_Secname']) > 0 && $data['Person_Secname'] != '- - -')
			$queryParams['Person_Secname'] = preg_replace('/[ё]/iu', 'Е', $data['Person_Secname']);
		else
			$queryParams['Person_Secname'] = NULL;

		if (isset($data['Person_BirthDate'])) {
			$queryParams['Person_BirthDate'] = $data['Person_BirthDate'];
		} else {
			$queryParams['Person_BirthDate'] = NULL;
		}

		if (isset($data['OMSSprTerr_id'])) {
			$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
		}

		if (isset($data['Polis_Ser']) && mb_strlen($data['Polis_Ser']) > 0 && isset($data['Polis_Num']) && mb_strlen($data['Polis_Num']) > 0) {
			$queryParams['Polis_Ser'] = trim($data['Polis_Ser']);
			$queryParams['Polis_Num'] = trim($data['Polis_Num']);
		} else {
			$queryParams['Polis_Ser'] = null;
			$queryParams['Polis_Num'] = null;
		}

		$query = "
			declare @DT_id int

			exec xp_PersonDoublesCheck
				@Person_id = :Person_id,
				@Person_SurName = :Person_Surname, -- фамилия
				@Person_FirName = :Person_Firname, -- имя
				@Person_SecName = :Person_Secname, -- отчество
				@Person_BirthDay = :Person_BirthDate, -- ДР
				@Polis_Ser = :Polis_Ser, -- серия полиса
				@Polis_Num = :Polis_Num, -- номер полиса
				@DoubleType_id = @DT_id output, -- критерий проверки
				@IsShowDouble = null -- показывать или нет двойников
				select @DT_id as DoubleType_id
		";

		/*echo getDebugSQL($query, $queryParams);
		exit();*/
		$res = $this->db->query($query, $queryParams);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
		
	}
	
	/**
	 *
	 * @param int $data
	 * @return type 
	 */
	function addPerson($data)
	{
		// Добавляем человека
		$sql = "
			declare @Person_id bigint

			exec p_Person_ins
				@Server_id = :Server_id,
				@Person_id = @Person_id output,
				@pmUser_id = :pmUser_id

			select @Person_id as Person_id
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query( $sql, $queryParams );
		
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				$Person_id = $response[0]['Person_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
		
		// Фамилия		
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonSurName_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@PersonSurName_SurName = :Person_Surname,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'Person_Surname' => mb_strtoupper($data['Person_Surname']),
		);
		//echo getDebugSQL($sql, $queryParams);exit();
		$this->db->query( $sql, $queryParams );
		
		// Имя
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonFirName_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@PersonFirName_FirName = :Person_Firname,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'Person_Firname' => mb_strtoupper($data['Person_Firname']),
		);
		
		$this->db->query( $sql, $queryParams );
		
		// Отчество
		if (!empty($data['Person_Secname'])) {

			$sql="
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_PersonSecName_ins
					@Server_id = :Server_id,
					@Person_id = :Person_id,
					@PersonSecName_SecName = :Person_Secname,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";
			$queryParams = array(
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Person_id' => $Person_id,
				'Person_Secname' => mb_strtoupper($data['Person_Secname']),
			);
			
			$this->db->query( $sql, $queryParams );
		}
		
		// День рождения
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonBirthDay_ins
			@Server_id = :Server_id,
			@Person_id = :Person_id,
			@PersonBirthDay_BirthDay = :Person_BirthDay,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'Person_BirthDay' => $data['Person_BirthDate'],
		);
		
		$this->db->query( $sql, $queryParams );
		
		// Пол
		if ( !isset($data['Sex_id']) )
			$data['Sex_id'] = 1;
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonSex_ins
			@Server_id = :Server_id,
			@Person_id = :Person_id,
			@Sex_id = :Sex_id,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'Sex_id' => $data['Sex_id'],
		);
		
		$this->db->query( $sql, $queryParams );
		
		// Телефон
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonPhone_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@PersonPhone_Phone = :Person_Phone,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrMsg as ErrMsg";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'Person_Phone' => $data['Person_Phone'],
		);
		
		$this->db->query( $sql, $queryParams );
	
		$sql = "
			declare @Address_Address varchar(300)
			declare @ErrCode int
			declare @ErrMsg varchar(400)
			
			set @Address_Address = isnull((select KLCountry_Name from KLCountry with(nolock) where KLCountry_id = :KLCountry_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select KLArea_FullName from v_KLArea with(nolock) where KLArea_id = :KLRgn_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select KLArea_FullName from v_KLArea with(nolock) where KLArea_id = :KLSubRgn_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select ISNULL(KLSocr_Nick,' ')+ ' ' +KLArea_name from KLArea with(nolock) left join KLSocr with(nolock) on KLArea.KLSocr_id = KLSocr.KLSocr_id where KLArea_id = :KLCity_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select KLArea_FullName from v_KLArea with(nolock) where KLArea_id = :KLTown_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select PersonSprTerrDop_Name from PersonSprTerrDop with(nolock) where PersonSprTerrDop_id = :PPersonSprTerrDop_id)+', ','')
			set @Address_Address = @Address_Address+isnull((select KLStreet_FullName from v_KLStreet with(nolock) where KLStreet_id = :KLStreet_id)+', ','')
			set @Address_Address = @Address_Address+isnull('д. '+:Address_House+', ','')
			set @Address_Address = @Address_Address+isnull('кор. '+:Address_Corpus+', ','')
			set @Address_Address = @Address_Address+isnull('кв. '+:Address_Flat,'')

			exec p_PersonPAddress_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_Zip = null,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@Address_Address = @Address_Address,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $Person_id,
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id'],
			'Address_House' => $data['Address_House'],
			'Address_Corpus' => isset($data['Address_Corpus']) ? $data['Address_Corpus'] : null,
			'Address_Flat' => $data['Address_Flat'],
			'PPersonSprTerrDop_id' => isset($data['PPersonSprTerrDop_id']) ? $data['PPersonSprTerrDop_id'] : null
		);
		
		$this->db->query( $sql, $queryParams );

		// Добавление полисных данных
		if (
			isset($data['Polis_Ser']) ||
			isset($data['Polis_Num']) ||
			isset($data['OrgSmo_id']) ||
			isset($data['OmsSprTerr_id'])
		) {
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)
				declare @Res bigint

				exec p_PersonPolis_ins
					@Server_id = :Server_id,
					@PersonPolis_id = @Res output,
					@Person_id = :Person_id,
					@PolisType_id = 1,
					@OrgSmo_id = :OrgSmo_id,
					@OmsSprTerr_id = :OmsSprTerr_id,
					@Polis_Ser = :Polis_Ser,
					@Polis_Num = :Polis_Num,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrMsg as ErrMsg";
			$queryParams = array(
				'Server_id' => $data['Server_id'],
				'Polis_Num' => isset($data['Polis_Num']) ? $data['Polis_Num']:null,
				'Polis_Ser' => isset($data['Polis_Ser']) ? $data['Polis_Ser']:null,
				'OrgSmo_id' => isset($data['OrgSmo_id']) ? $data['OrgSmo_id']:null,
				'OmsSprTerr_id' => isset($data['OMSSprTerr_id']) ? $data['OMSSprTerr_id']:null,
				'pmUser_id' => $data['pmUser_id'],
				'Person_id' => $Person_id
			);
			
			$this->db->query( $sql, $queryParams );
		}
		
		return $Person_id;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getPersonERData($data)
	{
		$query = " 
			select
				convert(varchar,Person_BirthDay,104) as Person_BirthDate,
				Sex_id as PersonSex_id,
				rtrim(Person_Surname) as Person_Surname,
				rtrim(Person_Firname) as Person_Firname,
				rtrim(Person_Secname) as Person_Secname,
				Polis_Ser,
				Polis_Num,
				a.Address_Address,
				Person_Phone
			from v_Person_ER p with (nolock)
			left join Address a with(nolock) on p.UAddress_id = a.Address_id
			where Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				return $response[0];
			}
		}
		
		return false;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkPersonAlreadyModerated($data)
	{
		$query = "
			select
				Person_id
			from Person with(nolock)
			where Person_mainid = :Person_id
				and pmUser_id = :pmUser_id
		";
		
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				return true;
			}
		}
		
		return false;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getUserPortalUserData($data)
	{
		$query = "
			select
				p.Person_Phone,
				u.first_name as FirstName,
				u.second_name as MidName,
				u.EMail,
				u.id
			from users u (nolock)
				inner join Person p (nolock) on u.id = p.pmUser_id
			where p.Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				return $response[0];
			}
		}
		
		return false;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function confirmPersonModeration($data)
	{
		$query = "
			update Person
			set 
				Person_mainId = :Person_mainId,
				Person_actDT = getdate(),
				Person_isModerated = 1
			where Person_Id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		
		return true;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function deleteFromPerson($data)
	{
		$query = "
			delete from Person
			where Person_Id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		
		return true;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function personSetInternetPhone($data)
	{
		$query = "
			declare
                @ErrCode int,
                @ErrMsg varchar(4000);

            exec p_Person_setInternetPhone
                @Server_id = 3,
                @Person_id = :Person_id,
                @PersonInfo_InternetPhone = :Person_Phone,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$this->db->query($query, $data);
		
		return true;
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function personModeration($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMsg varchar(4000);
		
			exec p_Person_Moderate
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@User_id = :User_id,
				@Lpu_id = :Lpu_id,
				@Person_Surname = :Person_Surname,
				@Person_Firname = :Person_Firname,
				@Person_Secname = :Person_Secname,
				@PersonSex_id = :PersonSex_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Person_BirthDate = :Person_BirthDate,
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$this->db->query($query, $data);
		
		return true;
	}

	function setPersonEmail($data){

		$result = $this->getFirstRowFromQuery("
			declare
				@Err_Code int,
				@Err_Msg varchar(4000);

			set nocount on;

			begin try
				update users with (rowlock)
				set email = :email
				where id = :account_id
			end try

			begin catch
				set @Err_Code = error_number();
				set @Err_Msg = error_message();
			end catch

			set nocount off;

			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		", array(
			'account_id' => $data['account_id'],
			'email' => $data['email'],
		));

		return $result;
	}

    function getEmail($data) {
        $filter = "";
        if(!empty($data['pmUsersList'])) {
            $filter = "where u.id in ({$data['pmUsersList']})";
        }
        $query = "
			select
				u.id
				u.EMail		
			from users (nolock) u
			{$filter}
		";

        $result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            $response = $result->result('array');
            if (count($response) > 0) {
                return $response[0];
            }
        }

        return false;
    }
}
?>