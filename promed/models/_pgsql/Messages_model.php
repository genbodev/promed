<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Messages
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Dmitry Storozhev
 * @version      23.08.2011
 */

class Messages_model extends SwPgModel
{
    public $mainDB = null;

    /**
     * Method description
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Method description
     */
    function getMessagesFolder($data)
    {
        $query = "
			select
				count(MessageRecipient_id) as \"InputCount_all\",
				(
					select
						COUNT(MessageRecipient_id)
					from
						msg.v_MessageRecipient 

					where
						UserRecipient_id = :pmUser_id and COALESCE(Message_isRead,0)=0

				) as \"InputCount_new\",
				(
					select
						COUNT(Message_id)
					from
						msg.v_Message 

					where
						UserSend_ID = :pmUser_id
						and COALESCE(Message_isDelete,0)=0

						and COALESCE(Message_isSent,0)=1

				) as \"OutputCount\",
				(
					select
						COUNT(Message_id)
					from
						msg.v_Message 

					where
						UserSend_ID = :pmUser_id
						and COALESCE(Message_isSent,0)=0

				) as \"DraftCount\"
			from
				msg.v_MessageRecipient 

			where
				UserRecipient_id = :pmUser_id
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
     * Method description
     */
    function loadMessagesGrid($data)
    {
        $queryParams = array();
        $filter = '1=1';
        $MesRepJoin = '';
        $MesRepSelect = '';
        $MesRepOrdBy = '';
        switch($data['FolderType_id'])
        {
            case 1:
                $MesRepSelect .= '
					MR.Message_isRead as "Message_isRead",
					MR.MessageRecipient_id as "MessageRecipient_id",
					MR.UserRecipient_id as "UserRecipient_id",
					MR.Message_isFlag as "Message_isFlag",
				';
                $MesRepJoin .= 'left join msg.v_MessageRecipient MR  on MR.Message_id = M.Message_id';

                $MesRepOrdBy .= 'MR.Message_isRead,';
                $filter .= ' and MR.UserRecipient_id = :UserRecipient_id and M.Message_isSent is not null';
                $queryParams['UserRecipient_id'] = $data['pmUser_id'];

                if(!empty($data['UserSend_id']))
                {
                    $filter .= ' and M.UserSend_id = :UserSend_id';
                    $queryParams['UserSend_id'] = $data['UserSend_id'];
                }
                if(!empty($data['NoticeType_id']))
                {
                    $filter .= ' and M.NoticeType_id = :NoticeType_id';
                    $queryParams['NoticeType_id'] = $data['NoticeType_id'];
                }
                if(!empty($data['Message_isRead']))
                {
                    if($data['Message_isRead'] == 1)
                        $filter .= ' and MR.Message_isRead is null';
                    else
                        $filter .= ' and MR.Message_isRead is not null';
                }
                if(!empty($data['MessagePeriodDate'][0]) && !empty($data['MessagePeriodDate'][1]))
                {
                    $filter .= " and (M.Message_setDT >= cast(:startdate as date) and M.Message_setDT <= cast(:enddate as date))";
                    $queryParams['startdate'] = $data['MessagePeriodDate'][0];
                    $queryParams['enddate'] = $data['MessagePeriodDate'][1];
                }

                break;

            case 2:
                $filter .= ' and M.UserSend_ID = :UserSend_ID and M.Message_isSent is not null and M.Message_isDelete is null';
                $queryParams['UserSend_ID'] = $data['pmUser_id'];
                break;

            case 3:
                $filter .= ' and M.UserSend_ID = :UserSend_ID and M.Message_isSent is null and M.Message_isDelete is null';
                $queryParams['UserSend_ID'] = $data['pmUser_id'];
                break;
        }


        $query = "
			select
				-- select
				M.Message_id as \"Message_id\",
				M.Message_pid as \"Message_pid\",
				to_char(cast(M.Message_setDT as timestamp), 'DD.MM.YYYY HH24:MI:SS') as \"Message_setDT\",
				M.UserSend_ID as \"UserSend_ID\",
				M.Message_Subject as \"Message_Subject\",
				COALESCE(M.Message_Text, '') as \"Message_Text\",

				{$MesRepSelect}
				M.NoticeType_id as \"NoticeType_id\",
				case when M.UserSend_ID = 0 then 'PromedWeb' else RTRIM(PUC.PMUser_Login) end as \"PMUser_Login\",
				PUC.PMUser_id as \"PMUser_id\",
				LPU.Lpu_Nick as \"Lpu_Nick\",
				MP.Dolgnost_Name as \"Dolgnost_Name\",
				case when M.UserSend_ID = 0 then 'Система' else
					RTRIM((case when (PUC.PMUser_surName is not null) then RTRIM(PUC.PMUser_surName) || ' ' else '' end) ||
						(case when (PUC.PMUser_firName is not null) then SUBSTRING(RTRIM(PUC.PMUser_firName),1,1) || '. ' else '' end) ||
						(case when (PUC.PMUser_secName is not null) then SUBSTRING(RTRIM(PUC.PMUser_secName),1,1) || '.' else '' end)
					) end as \"PMUser_Name\"
				-- end select
			from
				-- from
				msg.v_Message M 

				--left join msg.v_NoticeType NT  on NT.NoticeType_id = M.NoticeType_id

				{$MesRepJoin}
				left join pmUserCache PUC  on PUC.PMUser_id = M.UserSend_ID

				left join v_Lpu_all LPU  on LPU.Lpu_id = PUC.Lpu_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = PUC.MedPersonal_id

					and PUC.Lpu_id = MP.Lpu_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				{$MesRepOrdBy}
				M.Message_setDT desc
				-- end order by
		";

        $response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
        //print_r($response); exit();
        foreach($response['data'] as $k=>$res) {
            $response['data'][$k]['Message_Text'] = mb_substr(strip_tags($res['Message_Text']),0,100);
            $response['data'][$k]['Message_Subject'] = strip_tags($res['Message_Subject']);
        }
        return $response;
    }

    /**
     * Method description
     */
    function getLpuforUserData($lpu_id)
    {
        if(empty($lpu_id)) return false;
        $query = "
			select
				Lpu_Nick as \"Lpu_Nick\"
			from
				v_Lpu 

			where
				Lpu_id = ".$lpu_id."
		";
        $result = $this->db->query($query);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function getMedStaffactsforUser($data)
    {
        $query = "
			select
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				LS.LpuSection_FullName as \"LpuSection_FullName\",
				MC.PostMed_Name as \"MedSpec_Name\",
				MP.Person_FirName as \"Person_FirName\",
				MP.Person_SecName as \"Person_SecName\",
				MP.Person_SurName as \"Person_SurName\",
				COALESCE(L.Lpu_Nick, L.Lpu_Name) as \"Lpu_Name\",

				to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\",

				to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"

			from
				pmUserCache usr 

				inner join v_MedPersonal MP  on MP.MedPersonal_id = usr.MedPersonal_id

				left join v_MedStaffFact MSF  on MSF.MedPersonal_id = usr.MedPersonal_id

				left join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id

					and LS.Lpu_id = MSF.Lpu_id
				left join v_PostMed MC  on MC.PostMed_id = MSF.Post_id

				left join v_Lpu L  on L.Lpu_id = MSF.Lpu_id

			where
				usr.pmUser_id = :user_id 
		"; // --and user.Lpu_id = :Lpu_id

        $result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Поиск пользователей по кэшу.
     */
    function loadUserSearchGrid($data)
    {
        // ищем не по LDAP, а по кешу для снижения нагрузки
        $filter = "(1=1)";
        $filter_in = "(1=1)";
        $params = array();
        if ( strlen($data['pmUser_surName']) > 0 ) {
            $filter .= " and pmUser_surName ilike :pmUser_surName";
            $params['pmUser_surName'] = $data['pmUser_surName']."%";
        }
        if ( strlen($data['pmUser_firName']) > 0 ) {
            $filter .= " and pmUser_firName ilike :pmUser_firName";
            $params['pmUser_firName'] = $data['pmUser_firName']."%";
        }
        if ( strlen($data['pmUser_secName']) > 0 ) {
            $filter .= " and pmUser_secName ilike :pmUser_secName";
            $params['pmUser_secName'] = $data['pmUser_secName']."%";
        }
        if ( strlen($data['Lpu_Nick']) > 0 ) {
            $filter .= " and Lpu_Nick ilike :Lpu_Nick";
            $params['Lpu_Nick'] = "%".$data['Lpu_Nick']."%";
        }
        if ( strlen($data['LpuSection_Name']) > 0 ) {
            $filter_in .= " and LpuSection_Name ilike :LpuSection_Name";
            $params['LpuSection_Name'] = "%".$data['LpuSection_Name']."%";
        }
        if ( strlen($data['MedSpec_Name']) > 0 ) {
            $filter_in .= " and PostMed.PostMed_Name ilike :MedSpec_Name";
            $params['MedSpec_Name'] = "%".$data['MedSpec_Name']."%";
        }
        /*
        if ( $data['Lpu_id'] > 0 ) {
            $filter .= " and pmUserCache.Lpu_id = :Lpu_id";
        }
        */
        // Если это не форма поиска, а выборка по адресной книге, то надо подготовить условие
        if ( (isset($data['users'])) && (count($data['users'])>0) ) {
            $filter .= " and pmUser_id in (".implode(',', $data['users']).")";
        }

        $query = "
			select
				-- select
				pmUser_id as \"pmUser_id\",
				RTrim(pmUser_Login) as \"pmUser_Login\",
				pmUser_Name as \"pmUser_Name\",
				RTrim(pmUser_surName) as \"pmUser_surName\",
				RTrim(pmUser_firName) as \"pmUser_firName\",
				RTrim(pmUser_secName) as \"pmUser_secName\",
				RTrim(pmUser_Email) as \"pmUser_Email\",
				RTrim(pmUser_Avatar) as \"pmUser_Avatar\",
				pmUser_About as \"pmUser_About\",
				pmUser_Blocked as \"pmUser_Blocked\",
				pmUserCache.Lpu_id as \"Lpu_id\", -- здесь возможно надо будет поменять и если у пользователя указано несколько ЛПУ - показывать их всех 
				RTrim(Lpu_Nick) as \"Lpu_Nick\", 
				RTrim(LpuSection_Name) as \"LpuSection_Name\", 
				RTrim(Spec.MedSpec_Name) as \"MedSpec_Name\", 
				pmUserCache.MedPersonal_id as \"MedPersonal_id\",
				pmUserCache.pmUser_updID as \"pmUser_updID\",
				pmUserCache_updDT as \"pmUserCache_updDT\"
			-- end select
			from
				-- from
				pmUserCache 

				left join v_Lpu Lpu  on Lpu.Lpu_id = pmUserCache.Lpu_id

				LEFT JOIN LATERAL (

					Select  
						MedStaffFact.LpuSection_id, 
						MedStaffFact.Post_id as MedSpec_id, 
						LpuSection.LpuSection_Name, 
						PostMed.PostMed_Name as MedSpec_Name
					from v_MedStaffFact MedStaffFact  

					left join v_LpuSection LpuSection  on LpuSection.LpuSection_id = MedStaffFact.LpuSection_id

					left join v_PostMed PostMed  on MedStaffFact.Post_id = PostMed.PostMed_id

					where
						MedStaffFact.MedPersonal_id = pmUserCache.MedPersonal_id and {$filter_in}
						limit 1
				) as Spec on true
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by 
			-- order by
				pmUser_surName,
				pmUser_firName,
				pmUser_secName
			-- end order by
		";
        /*
        echo getDebugSql($query, $params);
        exit;
        */
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
        /*
        $this->load->helper('Text');
        $user = pmAuthUser::find($data['session']['login']);

        $users = new pmAuthUsers("(&(organizationalstatus=1)(o=".$data['Lpu_id']."))");

        $val = array();
        foreach ($users->users as $user) {
            $user_groups = "";
            foreach ( $user->group as $user_group )
                $user_groups .= (empty($user_groups)?$user_group->name:(", ".$user_group->name));
            $val[] = array(
                'pmuser_id'=>$user->pmuser_id,
                'login'=>$user->login,
                'firname'=>$user->firname,
                'surname'=>$user->surname,
                'secname'=>$user->secname,
                'desc'=>$user->desc,
                'medpersonal_id'=>$user->medpersonal_id,
                'IsMedPersonal'=>$user->medpersonal_id > 0 ? 'true' : 'false',
                'groups'=>$user_groups
            );
        }
        return $val;
        */
    }

    /**
     * Method description
     */
    function getUsersInLpu($Lpu, $pmUser_Group = null)
    {
        $filter = '';

        if(isset($Lpu) && count($Lpu) > 0) {
            $filter .= " and Lpu_id in (".implode(',', $Lpu).")";
        }
        if (!empty($pmUser_Group)) {
            $filter .= " and pmUser_Groups like '%{\"name\":\"{$pmUser_Group}\"}%'";
        }

        $query = "
			select
				PMUser_id as \"PMUser_id\"
			from
				pmUserCache 

			where
				COALESCE(PMUser_Blocked,0) = 0

				{$filter}
		";

        $result = $this->db->query($query);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function insMessage($data)
    {
	    if(isset($data['UserSend_ID']) )
		    $UserSend =  ':UserSend_ID';
	    else
		    $UserSend = ':pmUser_id' ;
        $query = "
        SELECT
        	Message_id as \"Message_id\",
        	error_code as \"Error_Code\",
        	error_message as \"Error_Msg\"
        FROM
        msg.p_Message_{$data['action']}(
        	Message_id => :Message_id,
        	Message_pid => :Message_pid,
			Message_setDT => dbo.tzGetDate(),
			UserSend_ID => {$UserSend},
			Message_Subject => :Message_Subject,
			Message_Text => :Message_Text,
			Message_isSent => :Message_isSent,
			NoticeType_id => :NoticeType_id,
			Message_isFlag => :Message_isFlag,
			Message_isDelete => :Message_isDelete,
			pmUser_id => :pmUser_id
        )
        ";

        //echo getDebugSql($query, $data);
        $result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function insMessageLink($Message_id, $recipient, $data)
    {
        $query = "
        SELECT
        	MessageLink_id as \"MessageLink_id\",
        	error_code as \"Error_Code\",
        	error_message as \"Error_Msg\"
        FROM
        msg.p_MessageLink_{$data['action']}(
        	MessageLink_id => :MessageLink_id,
        	Message_id => :Message_id,
			RecipientType_id => :RecipientType_id,
			UserRecipient_id => :UserRecipient_id,
			pmUser_id => :pmUser_id
        )
        ";

        $queryParams = array(
            'MessageLink_id'	=> null,
            'Message_id'		=> $Message_id,
            'RecipientType_id'	=> $data['RecipientType_id'],
            'UserRecipient_id'	=> $recipient,
            'pmUser_id'			=> $data['pmUser_id']
        );

        //echo getDebugSql($query, $queryParams);
        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function sendMessage($data, $userrecipient, $Message_id)
    {
        $query = "
        SELECT
			MessageRecipient_id as \"MessageRecipient_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
        FROM
        msg.p_MessageRecipient_ins(
        	MessageRecipient_id => :MessageRecipient_id,
        	Message_id => :Message_id,
			UserRecipient_id => :UserRecipient_id,
			Message_isRead => :Message_isRead,
			Message_ReadDT => null,
			pmUser_id => :pmUser_id
        )
        ";

        $queryParams = array(
            'MessageRecipient_id'	=> $data['MessageRecipient_id'],
            'Message_id'			=> $Message_id,
            'UserRecipient_id'		=> $userrecipient,
            'Message_isRead'		=> $data['Message_isRead'],
            'pmUser_id'				=> $data['pmUser_id']
        );

        //echo getDebugSql($query, $queryParams);
        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function getMessage($data)
    {
        $query = "
			select
				M.Message_id as \"Message_id\",
				M.Message_pid as \"Message_pid\",
				M.UserSend_ID as \"UserSend_ID\",
				M.Message_Subject as \"Message_Subject\",
				M.Message_Text as \"Message_Text\",
				M.Message_isFlag as \"Message_isFlag\",
				ML.RecipientType_id as \"RecipientType_id\",
				ML.UserRecipient_id as \"Group_id\" -- это только тогда когда получатель - группа
			from
				msg.v_Message M 

				left join msg.v_MessageLink ML  on ML.Message_id = M.Message_id

			where
				M.Message_id = :Message_id
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
     * Method description
     */
    function getDestinations_users($data)
    {
        $query = "
			select
				pUC.PMUser_id as \"pmUser_id\",
				RTRIM(pUC.PMUser_Login) as \"pmUser_Login\"
			from
				msg.v_Message M 

				left join msg.v_MessageLink ML  on ML.Message_id = M.Message_id

				left join pmUserCache pUC  on pUC.PMUser_id = ML.UserRecipient_id

			where
				M.Message_id = :Message_id
				and ML.RecipientType_id = 1
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
     * Method description
     */
    function getDestinations_lpus($data)
    {
        $query = "
			select
				LPU.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\"
			from
				msg.v_Message M 

				left join msg.v_MessageLink ML  on ML.Message_id = M.Message_id

				left join v_Lpu_all LPU  on LPU.Lpu_id = ML.UserRecipient_id

			where
				M.Message_id = :Message_id
				and ML.RecipientType_id = 4
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
     * Method description
     */
    function deleteMessage($data)
    {
        switch($data['FolderType_id'])
        {
            case 1:
                $query = "
					SELECT
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					FROM
					msg.p_MessageRecipient_del(
					  MessageRecipient_id => :MessageRecipient_id
					)
                ";
                break;

            case 2:
                $query = "
					update msg.Message
					set Message_isDelete = 1
					where Message_id = :Message_id
				";
                break;

            case 3:
                $query = "
					SELECT
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					FROM
					msg.p_Message_del(
					  Message_id => :Message_id
					)
                ";
                break;
        }

        $result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function deleteMessages($data)
    {
        switch($data['FolderType_id'])
        {
            case 1:
                foreach($data['MessageRecipient_ids'] as $MessageRecipient_id) {
                    $data['MessageRecipient_id'] = $MessageRecipient_id;
                    $query = "
						SELECT
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						FROM
						msg.p_MessageRecipient_del(
						  MessageRecipient_id => :MessageRecipient_id
						)
                    ";
                    $result = $this->db->query($query, $data);
                }
                break;

            case 2:
                foreach($data['Message_ids'] as $Message_id) {
                    $data['Message_id'] = $Message_id;
                    $query = "
						update msg.Message
						set Message_isDelete = 1
						where Message_id = :Message_id
					";
                    $result = $this->db->query($query, $data);
                }
                break;

            case 3:
                foreach($data['Message_ids'] as $Message_id) {
                    $data['Message_id'] = $Message_id;
                    $query = "
						SELECT
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						FROM
						msg.p_Message_del(
						  Message_id => :Message_id
						)
                    ";
                    $result = $this->db->query($query, $data);
                }
                break;
        }

        if ( isset($result) && is_object($result) ) {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Method description
     */
    function setMessageIsRead($data)
    {
        $query = "
			update
				msg.MessageRecipient
			set
				Message_isRead = 1,
				Message_ReadDT = dbo.tzGetDate()
			where
				MessageRecipient_id = :MessageRecipient_id
				and Message_id = :Message_id
				and UserRecipient_id = :pmUser_id
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
     * Method description
     */
    function setMessageActive($data)
    {
        $query = "
			update
				msg.MessageRecipient
			set
				Message_isFlag = :Message_isFlag
			where
				MessageRecipient_id = :MessageRecipient_id
				and Message_id = :Message_id
				and UserRecipient_id = :pmUser_id
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
     * Method description
     */
    function getFlagMessage($data)
    {
        $query = "
			select
				Message_isFlag as \"Message_isFlag\"
			from
				msg.v_MessageRecipient 

			where
				MessageRecipient_id = :MessageRecipient_id
				and Message_id = :Message_id
				and UserRecipient_id = :pmUser_id
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
     * Method description
     */
    function getUsersSend($data)
    {
        $query = "
			select distinct
				pUC.PMUser_id as \"UserSend_id\",
				RTRIM((case when (pUC.PMUser_surName is not null) then RTRIM(pUC.PMUser_surName) || ' ' else '' end) ||
					(case when (pUC.PMUser_firName is not null) then SUBSTRING(RTRIM(pUC.PMUser_firName),1,1) || '. ' else '' end) ||
					(case when (pUC.PMUser_secName is not null) then SUBSTRING(RTRIM(pUC.PMUser_secName),1,1) || '. ' else ' ' end) ||
					(case when (LPU.Lpu_Nick is not null) then '/' || RTRIM(LPU.Lpu_Nick) else '' end) ||
					(case when (MP.Dolgnost_Name is not null) then '/' || RTRIM(MP.Dolgnost_Name) else '' end)
				) as UserSend_Name
			from
				msg.v_MessageRecipient MR 

				left join msg.v_Message M  on M.Message_id = MR.Message_id

				left join pmUserCache pUC  on pUC.PMUser_id = M.UserSend_ID

				left join v_Lpu_all LPU  on LPU.Lpu_id = pUC.Lpu_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = pUC.MedPersonal_id

					and pUC.Lpu_id = MP.Lpu_id
			where
				UserRecipient_id = :pmuser_id
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
     * Создание и запись в бд автоматического сообщения
     * Пример вызова
     * $data['User_rid'] = 10000; - Пользователю с ID 10000
     * $data['text'] = 'Состояние пациента <a href="#" onClick="открытие события">Иванов И.И.</a> изменилось на положительное.';
     * $data['type'] = 1;  - Информационное (обычное)
     * $data['autotype'] = 5;  - изменение состояния пациента
     * $data['title'] = 'изменение состояния пациента';
     * $this->model->autoMessage($data);
     */
    function autoMessage($data) {
        // Сообщения пишем в основную БД. Получаем явно соединение, т.к. метод может вызываться из реестров
        if ( is_null($this->mainDB) ) {
            $CI =& get_instance();
            $this->mainDB = $CI->load->database('postgres', true);
        }
        if ( !function_exists('insMessage') ) {
            /**
             * Method description
             */
            function insMessage(&$t, $data) {
                if (empty($data['Evn_id'])) {
                    // если случай не указан, сохраняем NULL.
                    $data['Evn_id'] = null;
                }
                $query = "
                SELECT
                	Message_id as \"Message_id\",
                	error_code as \"Error_Code\",
                	error_message as \"Error_Msg\"
                FROM
                msg.p_Message_ins(
                	Message_pid => null,
					Message_setDT => dbo.tzGetDate(),
					UserSend_ID => 0,
					Message_Subject => :title,
					Message_Text => :text,
					Message_isSent => 1,
					NoticeType_id => :autotype, 
					pmUser_id => :pmUser_id,
					Evn_id => :Evn_id
                )
                ";
                try {
                    //echo getDebugSql($query, $data);
                    $result = $t->mainDB->query($query, $data);
                } catch (Exception $e) {
                    $result = null;
                }
                if ( is_object($result) ) {
                    $r = $result->result('array');
                    if (count($r)>0) {
                        if (strlen($r[0]['Error_Msg'])==0) {
                            return $r[0]['Message_id'];
                        } else {
                            return null;
                        }
                    }
                }
                else {
                    return null;
                }
            }
        }

        if ( !function_exists('insMessageRecipient') ) {
            /**
             * Method description
             */
            function insMessageRecipient(&$t, $recipient, $data) {
                $query = "
                SELECT
                	MessageRecipient_id as \"MessageRecipient_id\",
                	error_code as \"Error_Code\",
                	error_message as \"Error_Msg\"
                FROM
                msg.p_MessageRecipient_ins(
                	MessageRecipient_id => :MessageRecipient_id,
					Message_id => :Message_id,
					UserRecipient_id => :UserRecipient_id,
					pmUser_id => :pmUser_id
                )
                ";

                $params = array(
                    'MessageRecipient_id' => null,
                    'Message_id' => $data['Message_id'],
                    'UserRecipient_id' => $recipient,
                    'pmUser_id' => $data['pmUser_id']
                );
                /*print $recipient;
                echo getDebugSql($query, $params);
                exit;*/
                $result = $t->mainDB->query($query, $params);

                if ( is_object($result) ) {
                    $r = $result->result('array');
                    if (count($r)>0) {
                        if (strlen($r[0]['Error_Msg'])==0) {
                            return $r[0]['MessageRecipient_id'];
                        } else {
                            return null;
                        }
                    }
                }
                else {
                    return null;
                }
            }
        }

        if ( !function_exists('getUsersForSend') ) {
            /**
             * Method description
             */
            function getUsersForSend(&$t, $data, $sender) {
                if (($sender['field']!='PMUser_id') && ($sender['field']!='MedPersonal_id'))
                    $filter = 'COALESCE(UC.PMUser_Blocked,0) = 0';

                else
                    $filter = '(1=1)';
                if(isset($sender['MedServiceType_SysNick'])) {
                    $filter .= " and MST.MedServiceType_SysNick = :MedServiceType_SysNick 
							and MSP.MedServiceMedPersonal_endDT is null
							and UC.Lpu_id = :Lpu_id
							";
                    $query = "
						SELECT DISTINCT
							UC.PMUser_id as \"PMUser_id\"
						FROM pmUserCache UC
							left join v_MedServiceMedPersonal MSP  on MSP.MedPersonal_id = UC.MedPersonal_id

							left join v_MedService MS  on MS.MedService_id = MSP.MedService_id

							left join v_MedServiceType MST  on MST.MedServiceType_id = MS.MedServiceType_id

						WHERE 
							{$filter}
					";
                } else {
                    $filter .= ' and '. $sender['field']." = :recepient";
                    $data['recepient'] = $sender['recepient'];
                    if (isset($sender['Lpu_rid'])) {
                        $filter .= " and Lpu_id = :Lpu_rid";
                        $data['Lpu_rid'] = $sender['Lpu_rid'];
                    }

                    $query = "
						select
							UC.PMUser_id as \"PMUser_id\"
						from
							pmUserCache UC 

						where
							{$filter}
					";
                }
                //echo getDebugSQL($query, $data);
                $result = $t->mainDB->query($query, $data);

                if ( is_object($result) ) {
                    return $result->result('array');
                }
                else {
                    return false;
                }
            }
        }

        // данные которые нужны:  (login || User_rid || MedPersonal_rid || LpuSection_rid || Lpu_rid) && (text || type || autotype || title)
        $sender = array();
        // Проверки пришедших данных
        if (isset($data['User_rid']) && ($data['User_rid']>0)) {
            // отправка сообщения конкретному пользователю
            $sender['field'] = 'PMUser_id';
            $sender['recepient'] = $data['User_rid'];
        } elseif (isset($data['login']) && (strlen($data['login'])>0)) {
            $sender['field'] = 'PMUser_login';
            $sender['recepient'] = $data['login'];
        } elseif ((isset($data['MedPersonal_rid']) && ($data['MedPersonal_rid']>0)) && (isset($data['Lpu_rid']) && ($data['Lpu_rid']>0))) {
            $sender['field'] = 'MedPersonal_id';
            $sender['recepient'] = $data['MedPersonal_rid'];
            $sender['Lpu_rid'] = $data['Lpu_rid'];
        } elseif (isset($data['LpuSection_rid']) && ($data['LpuSection_rid']>0)) {
            $sender['field'] = 'LpuSection_id';
            $sender['recepient'] = $data['LpuSection_rid'];
        } elseif (isset($data['Lpu_rid']) && ($data['Lpu_rid']>0)) {
            $sender['field'] = 'Lpu_id';
            $sender['recepient'] = $data['Lpu_rid'];
        } elseif(isset($data['Lpu_id']) && isset($data['MedServiceType_SysNick'])) {//рассылка сотрудникам, работающим на заданной службе МО
            $sender['field'] = '';
            $sender['recepient'] = '';
            $sender['MedServiceType_SysNick'] = $data['MedServiceType_SysNick'];
        } else {
            return array('Error_Msg'=>'Получатель сообщения указан неверно.');
        }

        if (isset($data['text']) && (strlen($data['text'])>0)) {
            $sender['text'] = $data['text'];
        }
        if (isset($data['title']) && (strlen($data['title'])>0)) {
            $sender['title'] = $data['title'];
        } elseif (isset($data['text'])) {
            $sender['title'] = mb_substr($data['text'],0,150); // TODO: title лучше брать из справочника NoticeType
        }
        if (!isset($data['type'])) {
            $data['type'] = 1; // Обычное информационное сообщение (msg.MessageAutoPopUpType)
        }
        $sender['type'] = $data['type'];
        $sender['autotype'] = null; // Тип события (msg.MessageAutoType)
        if (isset($data['autotype'])) {
            $sender['autotype'] = $data['autotype'];
        }
        // Автоматическая генерация сообщений взависимости от типа
        /*
        switch ($sender['autotype']) {

        }
        */
        // Сохранение сообщения
        $data['Message_id'] = insMessage($this, $data);

        $users = getUsersForSend($this, $data, $sender);
        if(!is_array($users))
        {
            return array('Error_Msg'=>'Получатель не найден');
        }
        foreach($users as $user)
        {
            // Отправка сообщения (в цикле, если много)
            $data['MessageLink_id'] = insMessageRecipient($this, $user['PMUser_id'], $data);
        }
        return array('Message_id'=>$data['Message_id'], 'Error_Msg'=>'');
    }

   /**
     * Функция получает новые сообщения текущего пользователя
     */
 function getNewMessages($data)
    {
        $query = "
        select
            Message_id as \"Message_id\",
            Message_Subject as \"Message_Subject\",
            Message_Text as \"Message_Text\",
            NoticeType_id as \"NoticeType_id\",
            PMUser_Login as \"PMUser_Login\",
            PMUser_Name as \"PMUser_Name\",
            Lpu_Nick as \"Lpu_Nick\",
            Dolgnost_Name as \"Dolgnost_Name\",
            Evn_id as \"Evn_id\",
            EvnClass_SysNick as \"EvnClass_SysNick\",
            Person_id as \"Person_id\",
            Person_SurName as \"Person_SurName\",
            Person_FirName as \"Person_FirName\",
            Person_SecName as \"Person_SecName\"
        from msg.p_Messages_new
            (
                :pmUser_id
            )";


        $result = $this->db->query($query, $data);
        $unread = 0;
        $records = array();
        if ( is_object($result) ) {
            $records = $result->result('array');
            $query = "
				select
					COUNT(MessageRecipient_id) as \"InputCount_new\"
				from
					msg.v_MessageRecipient

				where
					UserRecipient_id = :pmUser_id and COALESCE(Message_isRead,0)=0

			";
            $result = $this->db->query($query, $data);
            if ( is_object($result) ) {
                $r = $result->result('array');
                if (count($r)>0) {
                    $unread = $r[0]['InputCount_new'];
                }
            }
            return array('data' => $records, 'totalCount'=> $unread);
        }
        else {
            return false;
        }
    }

    /**
     * Функция получает новые сообщения текущего пользователя в новом интерфейсе
     */
    function getNewMessagesExt6($data) {

        $query = "
        select
            Message_id as \"Message_id\",
            Message_Subject as \"Message_Subject\",
            Message_Text as \"Message_Text\",
            NoticeType_id as \"NoticeType_id\",
            PMUser_Login as \"PMUser_Login\",
            PMUser_Name as \"PMUser_Name\",
            Lpu_Nick as \"Lpu_Nick\",
            Dolgnost_Name as \"Dolgnost_Name\",
            Evn_id as \"Evn_id\",
            EvnClass_SysNick as \"EvnClass_SysNick\",
            Person_id as \"Person_id\",
            Person_SurName as \"Person_SurName\",
            Person_FirName as \"Person_FirName\",
            Person_SecName as \"Person_SecName\"
        from msg.p_Messages_new
            (
                :pmUser_id
            )";

        $result = $this->db->query($query, $data);
        $unread = 0;
        $records = array();
        if ( is_object($result) ) {
            $records = $result->result('array');
            $count = $this->dbmodel->getUnreadNoticeCount($data);
            if($count and !empty($count['totalCount'])) $count = $count['totalCount']; else $count = 0;

            return array('data' => $records, 'totalCount'=> $count);
        }
        else {
            return false;
        }
    }

    /**
     * Функция получает непрочитанные сообщения текущего пользователя от Админа ЦОД
     */
    function getAdminMessages($data)
    {
        return array('data' => array(), 'totalCount'=> 0, 'newmes' => false); // временно отключено, т.к. тяжелые запросы тормозят уфу.

        $records = array();
        $query = "
			select
				COUNT(MessageRecipient_id) as \"InputCount_new\"
			from
				msg.v_MessageRecipient mr 

				left join msg.v_Message mes  on mes.Message_id = mr.Message_id

				left join v_pmUserCache pUC  on pUC.PMUser_id = mes.UserSend_ID

			where
				UserRecipient_id = :pmUser_id and COALESCE(Message_isRead,0)=0 and pUC.pmUser_groups like '%SuperAdmin%'

		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            $r = $result->result('array');
            $newmes = false;
            if (count($r)>0) {
                $unread = $r[0]['InputCount_new'];
                $query = "
					select 
						DATEDIFF(s,mes.Message_setDT,dbo.tzGetDate()) as diff
					from
						msg.v_MessageRecipient mr 

						left join msg.v_Message mes  on mes.Message_id = mr.Message_id

						left join v_pmUserCache pUC  on pUC.PMUser_id = mes.UserSend_ID

					where
						UserRecipient_id = :pmUser_id and COALESCE(Message_isRead,0)=0 and pUC.pmUser_groups like '%SuperAdmin%'

					order by
						mes.Message_setDT DESC
						limit 1
				";
                $timeresult = $this->db->query($query, $data);
                if ( is_object($timeresult) ) {
                    $tr = $timeresult->result('array');
                    if (count($tr)>0) {
                        if($tr[0]['diff']<=300)
                            $newmes = true;
                    }
                }
            }
            return array('data' => $records, 'totalCount'=> $unread, 'newmes' => $newmes);
        } else {
            return false;
        }
    }

    /**
     * Функция возвращает номер SIM карты планшета
     */
    function sendNotificationEmergencyTeam($data){
        //здесь мы получаем настройки опер отдела
        $sql = "
        SELECT
			LB.LpuBuildingSmsType_id as \"LpuBuildingSmsType_id\"
        FROM
            v_LpuBuilding LB
            inner join v_MedService MS on MS.MedService_id = :MedService_id
        WHERE
          	MS.LpuBuilding_id = LB.LpuBuilding_id
        ";

        $params = array(
            'MedService_id' => $data['MedService_id']
        );

        $query = $this->db->query($sql, $params );

        $res = $query->result('array');

        $response['LpuBuildingSmsType_id'] = null;
        if (is_array($res) && count($res)>0) {
            $response['LpuBuildingSmsType_id'] = $res[0]['LpuBuildingSmsType_id'];
        }
        $sql = "
			SELECT
				CMPTabletPC_SIM as \"CMPTabletPC_SIM\"
			FROM
				v_CMPTabletPC TPC 

				inner join EmergencyTeam ET  on ET.CMPTabletPC_id = TPC.CMPTabletPC_id

				left join EmergencyTeamDuty ETD  on ETD.EmergencyTeam_id = ET.EmergencyTeam_id

			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
				and dbo.tzGetDate() between ETD.EmergencyTeamDuty_DTStart and ETD.EmergencyTeamDuty_DTFinish
		";

        $query = $this->db->query($sql, array(
            'EmergencyTeam_id' => $data['EmergencyTeam_id']
        ));

        $res = $query->result('array');

        if (is_array($res) && count($res)>0) {
            $response['SIM_number'] = $res[0]['CMPTabletPC_SIM'];
            return $response;
        }
        return false;
    }
    /**
     * Получение количества непрочитанных сообщений.
     * используется в виджете уведомлений (колокольчик)
     * в главном тулбаре Промеда
     */
    function getUnreadNoticeCount($data) {
        $queryParams = array();
        $filter = 'M.Evn_id is not null';
        $MesRepJoin = '';
        $MesRepSelect = '';
        $MesRepOrdBy = '';

        $MesRepSelect .= '
			MR.Message_isRead as "Message_isRead",
			MR.MessageRecipient_id as "MessageRecipient_id",
			MR.UserRecipient_id as "UserRecipient_id",
			MR.Message_isFlag as "Message_isFlag",
		';
        $MesRepJoin .= 'left join msg.v_MessageRecipient MR  on MR.Message_id = M.Message_id';

        $MesRepOrdBy .= 'MR.Message_isRead,';
        $filter .= ' and MR.UserRecipient_id = :UserRecipient_id and M.Message_isSent is not null';
        $queryParams['UserRecipient_id'] = $data['pmUser_id'];

        if(!empty($data['UserSend_id']))
        {
            $filter .= ' and M.UserSend_id = :UserSend_id';
            $queryParams['UserSend_id'] = $data['UserSend_id'];
        }
        /*	if(!empty($data['NoticeType_id']))
        {
            $filter .= ' and M.NoticeType_id = :NoticeType_id';
            $queryParams['NoticeType_id'] = $data['NoticeType_id'];
        }*/
        $filter .= ' and MR.Message_isRead is null';

        $query = "
			select
				count(M.Message_id)
			from
				-- from
				msg.v_Message M 

				--left join msg.v_NoticeType NT  on NT.NoticeType_id = M.NoticeType_id

				{$MesRepJoin}
				left join pmUserCache PUC  on PUC.PMUser_id = M.UserSend_ID

				left join v_Lpu_all LPU  on LPU.Lpu_id = PUC.Lpu_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = PUC.MedPersonal_id

					and PUC.Lpu_id = MP.Lpu_id
				-- end from
			where
				-- where
				
				{$filter}
				-- end where
		";
        //~ echo getDebugSQL($query, $queryParams);exit;

        $result_count = $this->getFirstResultFromQuery($query, $queryParams);
        return array('totalCount' => $result_count);

    }
    /**
     * Получение списка сообщений.
     * используется в виджете уведомлений (колокольчик)
     * в главном тулбаре Промеда
     */
    function getMessagesListData($data) {
        $queryParams = array();
        $filter = 'M.Evn_id is not null and M.UserSend_ID = 0 ';
        $MesRepJoin = '';
        $MesRepSelect = '';
        $MesRepOrdBy = '';

        $MesRepSelect .= '
			MR.Message_isRead as "Message_isRead",
			MR.MessageRecipient_id as "MessageRecipient_id",
			MR.UserRecipient_id as "UserRecipient_id",
			MR.Message_isFlag as "Message_isFlag",
		';
        $MesRepJoin .= 'left join msg.v_MessageRecipient MR  on MR.Message_id = M.Message_id';

        $MesRepOrdBy .= 'MR.Message_isRead,';
        $filter .= ' and MR.UserRecipient_id = :UserRecipient_id and M.Message_isSent is not null';
        $queryParams['UserRecipient_id'] = $data['pmUser_id'];

        if(!empty($data['UserSend_id']))
        {
            $filter .= ' and M.UserSend_id = :UserSend_id';
            $queryParams['UserSend_id'] = $data['UserSend_id'];
        }
        /*	if(!empty($data['NoticeType_id']))
        {
            $filter .= ' and M.NoticeType_id = :NoticeType_id';
            $queryParams['NoticeType_id'] = $data['NoticeType_id'];
        }*/
        if(!empty($data['Message_isRead']))
        {
            if($data['Message_isRead'] == 1)
                $filter .= ' and MR.Message_isRead is null';
            else
                $filter .= ' and MR.Message_isRead is not null';
        }
        if(!empty($data['MessagePeriodDate'][0]) && !empty($data['MessagePeriodDate'][1]))
        {
            $filter .= " and (M.Message_setDT >= cast(:startdate as date) and M.Message_setDT <= cast(:enddate as date))";
            $queryParams['startdate'] = $data['MessagePeriodDate'][0];
            $queryParams['enddate'] = $data['MessagePeriodDate'][1];
        }
        if(!empty($data['query'])) {
            $filter.=" AND P.Person_Fio LIKE :query || '%' ";
            $queryParams['query'] = $data['query'];
        }

        $query = "
			select
				-- select
				M.Evn_id as \"Evn_id\",
				M.Message_id as \"Message_id\",
				M.Message_pid as \"Message_pid\",
				to_char(M.Message_setDT, 'DD.MM.YYYY') || ' ' || to_char(M.Message_setDT, 'HH24:MI:SS') as \"Message_setDT\",
				M.UserSend_ID as \"UserSend_ID\",
				M.Message_Subject as \"Message_Subject\",
				COALESCE(M.Message_Text, '') as \"Message_Text\",

				{$MesRepSelect}
				M.NoticeType_id as \"NoticeType_id\",
				E.EvnClass_Name as \"EvnClass_Name\",
				E.PersonEvn_id as \"PersonEvn_id\",
				E.Server_id as \"Server_id\",
				E.Evn_rid as \"Evn_rid\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				rEvn.EvnClass_SysNick as \"EvnClass_rSysNick\",
				E.Person_id as \"Person_id\",
				P.Person_Fio as \"Person_Fio\",
				exml.EvnXml_id as \"EvnXml_id\" 
				-- end select
			from
				-- from
				msg.v_Message M 
				left join msg.v_MessageLink ML on ML.Message_id = M.Message_id

				--left join msg.v_NoticeType NT  on NT.NoticeType_id = M.NoticeType_id

				{$MesRepJoin}
				left join pmUserCache MP  on MP.PMUser_id = :UserRecipient_id

				left join v_Evn E  on E.Evn_id = M.Evn_id

				left join v_Evn rEvn  on rEvn.Evn_id = E.Evn_rid

				left join v_Person_all P  on P.Person_id=E.Person_id and P.PersonEvn_id=E.PersonEvn_id
				left join v_Evnxml exml on e.Evn_rid = exml.Evn_id

				-- end from
			where
				-- where
				
				{$filter}
				-- end where
			order by
				-- order by
				{$MesRepOrdBy}
				M.Message_setDT desc
				-- end order by
		";
        //~ echo getDebugSQL($query, $queryParams);exit;

        $result_count = $this->db->query(getCountSQLPH($query), $queryParams);
        $response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        $response['totalCount'] = $count;
        //print_r($response); exit();
        foreach($response['data'] as $k=>$res) {
            $response['data'][$k]['Message_Text'] = mb_substr(strip_tags($res['Message_Text']),0,100);
            $response['data'][$k]['Message_Subject'] = strip_tags($res['Message_Subject']);
        }
        return $response;
    }

    /**
     * Сделать прочитанными все сообщения
     * используется в виджете уведомлений (колокольчик)
     * в главном тулбаре Промеда
     */
    function setMessagesIsReaded($data) {
        $queryParams['UserRecipient_id'] = $data['pmUser_id'];

        $query = "
			update msg.MessageRecipient
			set 
				Message_isRead = 1,
				Message_ReadDT = dbo.tzGetDate()
			where 
				UserRecipient_id = :UserRecipient_id and Message_id in (
					select M.Message_id
					from msg.v_Message M  inner join msg.v_MessageRecipient MR  on MR.Message_id=M.Message_id

					where M.Evn_id is not null and MR.UserRecipient_id = :UserRecipient_id
				)
		";
        //~ echo getDebugSQL($query, $data);exit;
        $result = $this->db->query($query, $queryParams);// возвращает true при успешном обновлении

        if ( $result === true ) {
            return true;
        }
        else {
            return false;
        }
    }

    function getOpenLpus()
	{
		return $this->queryResult("
			select
				Lpu_id as \"Lpu_id\"
			from v_Lpu
			where Lpu_endDate is null
		");
	}

	function checkPmUser($pmUser_id) {
		// проверка на существование получателя сообщения
		return $this->getFirstRowFromQuery("
			select pmUser_id  as \"pmUser_id\" from v_pmUserCache where pmUser_id = :pmUser_id
		", array('pmUser_id'=>$pmUser_id));
	}

	function mSendPush($data) {
		$checkPmUser_id = $this->checkPmUser($data['UserRecipient_id']);

		if(empty($checkPmUser_id)) {
			return array(
				"Error_code"=>1,
				"Error_msg"=> "Идентификатор ".$data['UserRecipient_id']." не найден"
			);
		}

		$sql = "
		select MessageLink_id as \"MessageLink_id\", ErrCode as \"Error_Code\", ErrMsg as \"Error_Msg\"            
		from msg.p_MessageLink_ins(
			MessageLink_id := null,
			Message_id := :Message_id,
			RecipientType_id := :RecipientType_id,
			UserRecipient_id := :UserRecipient_id,
			pmUser_id := :pmUser_id);
	";
		$params = array(
			'Message_id' => $data['Message_id'],
			'RecipientType_id' => 1,
			'UserRecipient_id' => $data['UserRecipient_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса сохранения записи!'));
		}
	}

	function mSetTimeWorkTime($data) {
		$checkPmUser_id = $this->checkPmUser($data['pmUser_tid']);

		if(empty($checkPmUser_id)) {
			return array(
				"Error_code"=>1,
				"Error_msg"=> "Идентификатор ".$data['pmUser_tid']." не найден"
			);
		}

		$sql = "
		select TimeJournal_id as \"TimeJournal_id\", ErrCode as \"Error_Code\", ErrMsg as \"Error_Msg\"            
		from p_TimeJournal_ins(
			TimeJournal_id := null,
			pmUser_tid := :pmUser_tid,
			TimeJournal_BegDT := :TimeJournal_BegDT,
			TimeJournal_EndDT := :TimeJournal_EndDT,
			Server_id := :Server_id,
			pmUser_id := :pmUser_id);
	";
		$params = array(
			'pmUser_tid' => $data['pmUser_tid'],
			'TimeJournal_BegDT' => $data['TimeJournal_BegDT'],
			'TimeJournal_EndDT' => $data['TimeJournal_EndDT'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса сохранения записи!'));
		}
	}

	function mCheckActiveWorkTime($data) {
		$checkPmUser_id = $this->checkPmUser($data['pmUser_tid']);

		if(empty($checkPmUser_id)) {
			return array(
				"Error_code"=>1,
				"Error_msg"=> "Идентификатор ".$data['pmUser_tid']." не найден"
			);
		}

		$sql = "
			select 
				TimeJournal_id  as \"TimeJournal_id\"
			from 
				v_TimeJournal 
			where 
				pmUser_tid = :pmUser_tid and
				dbo.tzGetDate() < TimeJournal_EndDT";
		$result = $this->getFirstResultFromQuery($sql, array('pmUser_tid' => $data['pmUser_tid']));

		if(!empty($result)) {
			return array('TimeJournal_id'=>$result, 'PersonAtWork'=>true);
		} else {
			return array('Error_msg'=>'Запись в журнале не найдена','PersonAtWork'=>false);
		}
	}

	function mSetEndDTWorkTime($data) {
		$CheckActiveWorkTime = $this->mCheckActiveWorkTime(array('pmUser_tid'=>$data['pmUser_tid']));

		if(!empty($CheckActiveWorkTime['TimeJournal_id'])) {
			$response = $this->getFirstRowFromQuery("
				update TimeJournal
					set TimeJournal_EndDT = dbo.tzGetDate()
				where TimeJournal_id = :TimeJournal_id;
				select '' as \"Error_Code\", '' as \"Error_Msg\";
			", array(
					'TimeJournal_id' => $CheckActiveWorkTime['TimeJournal_id']
				)
			);


			if (!empty($response['Error_Msg'])) {
				return array('Error_Msg'=>$response['Error_Msg'], 'Error_Code'=>2);
			}
		} else {
			if (!empty($CheckActiveWorkTime['Error_msg'])) {
				return array('Error_Msg'=>$CheckActiveWorkTime['Error_msg'], 'Error_Code'=>1);
			}
		}
		return array($response);
	}
}
