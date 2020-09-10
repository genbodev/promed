<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QueryEvn_model - Журнал запросов сторонних МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

class QueryEvn_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление запроса
	 */
	function delete($data) {
		
		$tmp = $this->queryList("select QueryEvnMessage_id  as \"QueryEvnMessage_id\" from QueryEvnMessage  where QueryEvn_id = ?", array($data['QueryEvn_id']));

		foreach($tmp as $qem) {
			$tmp2 = $this->queryList("select QueryEvnMessageFile_id  as \"QueryEvnMessageFile_id\" from QueryEvnMessageFile  where QueryEvnMessage_id = ?", array($qem));

			foreach($tmp2 as $qemf) {
				$this->db->query("
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from p_QueryEvnMessageFile_del(
						QueryEvnMessageFile_id := ?);
				", array($qemf));
			}
			
			$this->db->query("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_QueryEvnMessage_del(
					QueryEvnMessage_id := ?,
					IsRemove := 2);
			", array($qem));
		}
		
		$tmp = $this->queryList("select QueryEvnUpd_id  as \"QueryEvnUpd_id\" from QueryEvnUpd  where QueryEvn_id = ?", array($data['QueryEvn_id']));

		foreach($tmp as $qeu) {
			$this->db->query("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_QueryEvnUpd_del(
					QueryEvnUpd_id := ?)
			", array($qeu));
		}
		
		$tmp = $this->queryList("select QueryEvnUser_id  as \"QueryEvnUser_id\" from QueryEvnUser  where QueryEvn_id = ?", array($data['QueryEvn_id']));

		foreach($tmp as $qeu) {	
			$this->db->query("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_QueryEvnUser_del(
					QueryEvnUser_id := ?);
			", array($qeu));
		}
		
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_QueryEvn_del(
				QueryEvn_id := :QueryEvn_id);
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * История
	 */
	function doLoadHistory($data) {
		$sql = "
			select
				qeu.QueryEvnUpd_id as \"QueryEvnUpd_id\"
				,to_char(qeu.QueryEvnUpd_InsDT, 'DD.MM.YYYY') || ' ' || COALESCE(to_char(qeu.QueryEvnUpd_InsDT, 'HH24:MI:SS'), '') as \"QueryEvnUpd_Date\"



				,qeut.QueryEvnUpdType_Name as \"QueryEvnUpdType_Name\"
				,usr.pmUser_Name as \"pmUser_NameChange\"
				,usrR.pmUser_Name as \"pmUser_NameResp\"
				,usrE.pmUser_Name as \"pmUser_NameExec\"
				,qes.QueryEvnStatus_Name as \"QueryEvnStatus_Name\"
			from QueryEvnUpd qeu  

				left join QueryEvnUpdType qeut  on qeut.QueryEvnUpdType_id = qeu.QueryEvnUpdType_id

				left join QueryEvnStatus qes  on qes.QueryEvnStatus_id = qeu.QueryEvnStatus_id

				left join v_pmUserCache usr  on usr.pmUser_id = qeu.pmUser_insID

				left join v_pmUserCache usrR  on usrR.pmUser_id = qeu.pmUser_rid

				left join v_pmUserCache usrE  on usrE.MedPersonal_id = qeu.MedPersonal_id

			where 
				qeu.QueryEvn_id = :QueryEvn_id
			order by 
				qeu.QueryEvnUpd_InsDT desc 
		";
		
		return $this->queryResult($sql, $data);
	}

	/**
	 * Возвращает список запросов
	 */
	function loadList($data) {
	
		$filter = '(1=1)';
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
			'QueryEvnUserType_id' => $data['QueryEvnUserType_id'],
		);
		
		if ($data['onlyMy'] == 1) {
			$filter .= ' and (qeut.pmUser_rid = :pmUser_id or qeut.MedPersonal_id = :MedPersonal_id) ';
		} else {
			$filter .= ' and usr.Lpu_id = :Lpu_id ';
		}
		
		switch($data['StatusFilter_id']) {
			case 2:
				$filter .= ' and qe.QueryEvnStatus_id = 2 ';
				break;
			case 3:
				$filter .= ' and qe.QueryEvnStatus_id = 1 ';
				break;
		}
		
		if(!empty($data['Person_Fio'])) {
			$filter .= ' and (
				ps.Person_SurName iLIKE :Person_Fio OR

				ps.Person_FirName iLIKE :Person_Fio OR

				ps.Person_SecName iLIKE :Person_Fio

			)';
			$params['Person_Fio'] = "%{$data['Person_Fio']}%";
		}
	
		$query = "
			select
			-- select
				qe.QueryEvn_id as \"QueryEvn_id\",
				qe.QueryEvnStatus_id as \"QueryEvnStatus_id\",
				qet.QueryEvnType_Name as \"QueryEvnType_Name\",
				to_char(QueryEvn_insDT, 'DD.MM.YYYY') as \"QueryEvn_Date\",

				rtrim(rtrim(COALESCE(ps.Person_Surname, '')) || ' ' || rtrim(COALESCE(ps.Person_Firname, '')) || ' ' || rtrim(COALESCE(ps.Person_Secname, ''))) || ' (' || dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate())::varchar(3) || ' г.)' as \"Person_Fio\",

				case when qe.QueryEvnStatus_id = 2 then 'true' else 'false' end as \"QueryEvn_Ready\",
				uCreat.pmUser_id as \"pmUser_idCreat\",
				uCreat.pmUser_Name as \"pmUser_NameCreat\",
				uExec.pmUser_id as \"pmUser_idExec\",
				uExec.pmUser_Name as \"pmUser_NameExec\",
				uResp.pmUser_id as \"pmUser_idResp\",
				uResp.pmUser_Name as \"pmUser_NameResp\",
				qeuCreat.MedStaffFact_id as \"MedStaffFact_idCreat\",
				qeuExec.MedStaffFact_id as \"MedStaffFact_idExec\",
				qeuResp.MedStaffFact_id as \"MedStaffFact_idResp\",
				qeuCreat.MedPersonal_id as \"MedPersonal_idCreat\",
				qeuExec.MedPersonal_id as \"MedPersonal_idExec\",
				qeuResp.MedPersonal_id as \"MedPersonal_idResp\"
			-- end select
			from
			-- from
				v_QueryEvn qe 

				inner join v_Evn evn  on evn.Evn_id = qe.Evn_id

				inner join v_PersonState ps  on ps.Person_id = evn.Person_id

				inner join QueryEvnType qet  on qet.QueryEvnType_id = qe.QueryEvnType_id

				
				left join QueryEvnUser qeuCreat  on -- Автор

					qeuCreat.QueryEvn_id = qe.QueryEvn_id and 
					qeuCreat.QueryEvnUserType_id = 1 and 
					qeuCreat.QueryEvnUser_endDate is null
				left join v_pmUserCache uCreat  on uCreat.PMUser_id = qeucreat.pmUser_rid

				
				left join QueryEvnUser qeuExec  on  -- Исполнитель

					qeuExec.QueryEvn_id = qe.QueryEvn_id and 
					qeuExec.QueryEvnUserType_id = 2 and 
					qeuExec.QueryEvnUser_endDate is null
				left join v_pmUserCache uExec  on uExec.PMUser_id = qeuExec.pmUser_rid

				
				left join QueryEvnUser qeuResp  on  -- Ответственный

					qeuResp.QueryEvn_id = qe.QueryEvn_id and 
					qeuResp.QueryEvnUserType_id = 3 and 
					qeuResp.QueryEvnUser_endDate is null
				left join v_pmUserCache uResp  on uResp.PMUser_id = qeuResp.pmUser_rid

				
				inner join QueryEvnUser qeut  on

					qeut.QueryEvn_id = qe.QueryEvn_id and 
					qeut.QueryEvnUser_endDate is null and
					qeut.QueryEvnUserType_id = :QueryEvnUserType_id
				inner join v_pmUserCache usr  on usr.pmUser_id = qeut.pmUser_rid

			-- end from
			where
			-- where
				{$filter}
			-- end where
			order by
			-- order by
				qe.QueryEvn_insDT desc
			-- end order by
		";
		
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Возвращает запрос
	 */
	function load($data) {

		$sql = "
			select
				qe.QueryEvn_id as \"QueryEvn_id\"
				,qe.Evn_id as \"Evn_id\"
				,qe.QueryEvnStatus_id as \"QueryEvnStatus_id\"
				,qes.QueryEvnStatus_Name as \"QueryEvnStatus_Name\"
				,qe.QueryEvnType_id as \"QueryEvnType_id\"
				,ps.Person_id as \"Person_id\"
				,ps.Server_id as \"Server_id\"
				,ps.PersonEvn_id as \"PersonEvn_id\"
				,rtrim(rtrim(COALESCE(ps.Person_Surname, '')) || ' ' || 

					rtrim(COALESCE(ps.Person_Firname, '')) || ' ' || 

					rtrim(COALESCE(ps.Person_Secname, ''))) || ' ' || 

					to_char(ps.Person_BirthDay, 'DD.MM.YYYY') || 

					' (' || dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate())::text || ' г.)'  as \"Person_Fio\",
				uCreat.pmUser_id as \"pmUser_idCreat\",
				rtrim(uCreat.pmUser_Name) || ' / ' || COALESCE(LpuCreat.Lpu_Nick, '') || ' / ' || coalesce(psCreat.PostMed_Name,MpCreat.Dolgnost_Name,'') as \"pmUser_NameCreat\",

				uExec.pmUser_id as \"pmUser_idExec\",
				uExec.pmUser_Name as \"pmUser_NameExec\",
				qeuExec.MedPersonal_id as \"MedPersonal_id\",
				qeuExec.MedStaffFact_id as \"MedStaffFact_id\",
				uResp.pmUser_id as \"pmUser_idResp\",
				uResp.pmUser_Name as \"pmUser_NameResp\",
				qemQ.QueryEvnMessage_Text as \"QueryEvnMessage_TextRequest\",
				qemR.QueryEvnMessage_Text as \"QueryEvnMessage_TextResponse\",
				qeut.QueryEvnUserType_id as \"QueryEvnUserType_id\"
			from
				v_QueryEvn qe 

				left join QueryEvnStatus qes  on qes.QueryEvnStatus_id = qe.QueryEvnStatus_id

				inner join v_Evn evn  on evn.Evn_id = qe.Evn_id

				inner join v_PersonState ps  on ps.Person_id = evn.Person_id

				
				left join QueryEvnUser qeuCreat  on qeuCreat.QueryEvn_id = qe.QueryEvn_id and qeuCreat.QueryEvnUserType_id = 1 and qeuCreat.QueryEvnUser_endDate is null -- Автор

				left join v_pmUserCache uCreat  on uCreat.PMUser_id = qeucreat.pmUser_rid

				left join v_MedStaffFact MfsCreat  on MfsCreat.MedStaffFact_id = qeuCreat.MedStaffFact_id

				left join v_MedPersonal MpCreat  on MpCreat.MedPersonal_id = qeuCreat.MedPersonal_id and MpCreat.Lpu_id = evn.Lpu_id

				left join v_PostMed psCreat  on psCreat.PostMed_id = MfsCreat.Post_id

				left join v_Lpu LpuCreat  on LpuCreat.Lpu_id = COALESCE(MfsCreat.Lpu_id,uCreat.Lpu_id)


				
				left join QueryEvnUser qeuExec  on qeuExec.QueryEvn_id = qe.QueryEvn_id and qeuExec.QueryEvnUserType_id = 2 and qeuExec.QueryEvnUser_endDate is null -- Исполнитель

				left join v_pmUserCache uExec  on uExec.PMUser_id = qeuExec.pmUser_rid

				
				left join QueryEvnUser qeuResp  on qeuResp.QueryEvn_id = qe.QueryEvn_id and qeuResp.QueryEvnUserType_id = 3 and qeuResp.QueryEvnUser_endDate is null -- Ответственный

				left join v_pmUserCache uResp  on uResp.PMUser_id = qeuResp.pmUser_rid

				
				left join QueryEvnMessage qemQ  on qemQ.QueryEvnUser_id = qeuCreat.QueryEvnUser_id -- текст запроса

				left join QueryEvnMessage qemR  on qemR.QueryEvnUser_id = qeuExec.QueryEvnUser_id -- текст ответа

				
				LEFT JOIN LATERAL(

					select qeut.QueryEvnUserType_id
					from QueryEvnUser qeut 

					where
						qeut.QueryEvn_id = qe.QueryEvn_id and 
						(qeut.pmUser_rid = :pmUser_id or qeut.MedPersonal_id = :MedPersonal_id) and 
						qeut.QueryEvnUser_endDate is null
					order by QueryEvnUserType_id desc
                   	limit 1
				) qeut ON true -- пытаемся опеределить роль текущего пользователя
				
			where
				QE.QueryEvn_id = :QueryEvn_id
		";
		
		//echo getDebugSQL($sql, $data);die;
		$result = $this->queryResult($sql, $data);
		
		if (is_array($result) && count($result)) {
			$result[0]['messages'] = $this->getQueryEvnMessage($data);
		}
		
		return $result;
	}
	
	/**
	* Получение сообщений
	*/
	function getQueryEvnMessage($data) {
		
		$result = $this->queryResult("
			select
				qem.QueryEvnMessage_id as \"QueryEvnMessage_id\",
				qem.QueryEvnMessage_Text as \"QueryEvnMessage_TextResponse\",
				usr.MedPersonal_id as \"MedPersonal_id\",
				usr.pmUser_id as \"pmUser_id\",
				rtrim(usr.pmUser_Name) as \"pmUser_Name\"
			from v_QueryEvnMessage qem  

			inner join QueryEvnUser qeu  on qeu.QueryEvnUser_id = qem.QueryEvnUser_id and qeu.QueryEvnUserType_id in (2,3) 

			inner join v_pmUserCache usr  on usr.PMUser_id = qeu.pmUser_rid

			where qem.QueryEvn_id = :QueryEvn_id
		", array('QueryEvn_id' => $data['QueryEvn_id']));
		
		if (is_array($result) && count($result)) {
			foreach($result as $k => $row) {
				$result[$k]['files'] = $this->getQueryEvnMessageFile($row, $data);
			}
		}
		
		return $result;
		
	}
	
	/**
	* Получение файлов
	*/
	function getQueryEvnMessageFile($msg, $data) {
		$result = $this->queryResult("
			select
				COALESCE(qemf.EvnXml_id,-qemf.EvnMediadata_id) as \"EvnXml_id\",

				COALESCE(ex.EvnXml_Name,emd.EvnMediadata_FileName) as \"EvnXml_Name\",

				emd.EvnMediadata_FilePath as \"FilePath\"
			from QueryEvnMessageFile qemf  

			left join v_EvnXml ex  on ex.EvnXml_id = qemf.EvnXml_id

			left join v_EvnMediadata emd  on emd.EvnMediadata_id = qemf.EvnMediadata_id

			where qemf.QueryEvnMessage_id = :QueryEvnMessage_id
		", array('QueryEvnMessage_id' => $msg['QueryEvnMessage_id']));
		return $result;
	}

	/**
	 * Сохраняет запрос
	 */
	function save($data) {

		$params = array(
			'QueryEvn_id' => empty($data['QueryEvn_id']) ? null : $data['QueryEvn_id'],
			'Evn_id' => $data['Evn_id'],
			'QueryEvnType_id' => $data['QueryEvnType_id'],
			'QueryEvnStatus_id' => $data['QueryEvnStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($data['QueryEvn_id']) ? 'p_QueryEvn_ins' : 'p_QueryEvn_upd';
		
		$QueryEvnStatus_id_old = null;
		if (!empty($data['QueryEvn_id'])) {
			$QueryEvnStatus_id_old = $this->getFirstResultFromQuery("select QueryEvnStatus_id  as \"QueryEvnStatus_id\" from QueryEvn  where QueryEvn_id = ?", array($data['QueryEvn_id']));

		}

		$sql = "
			select QueryEvn_id as \"QueryEvn_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				QueryEvn_id := :QueryEvn_id,
				Evn_id := :Evn_id,
				QueryEvnType_id := :QueryEvnType_id,
				QueryEvnStatus_id := :QueryEvnStatus_id,
				pmUser_id := :pmUser_id)
		";
		
		$result = $this->queryResult($sql, $params);
		
		if (count($result) && isset($result[0]['QueryEvn_id'])) {
			
			if (empty($data['QueryEvn_id'])) {
				$data['QueryEvn_id'] = $result[0]['QueryEvn_id'];
				$usr = $this->saveUserFirst($data, 1); // автор
				$data['QueryEvnUser_id'] = $usr[0]['QueryEvnUser_id'];
			} else {
				$usr = $this->getFirstRowFromQuery("
					select QueryEvnUser_id  as \"QueryEvnUser_id\"
					from QueryEvnUser  

					where 
						QueryEvn_id = :QueryEvn_id and 
						(pmUser_rid = :pmUser_id or MedPersonal_id = :MedPersonal_id) and 
						QueryEvnUser_endDate is null 
						order by QueryEvnUserType_id desc
				", array(
					'QueryEvn_id' => $data['QueryEvn_id'],
					'MedPersonal_id' => $data['MedPersonal_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				$data['QueryEvnUser_id'] = $usr['QueryEvnUser_id'];
			}
			
			if ($data['QueryEvnStatus_id'] == 1) {
				// определяем исполнителя и ответственного, если нет
				$this->findAnswerUsers($data);
			}
			
			$this->saveMessage($data);
		}
		
		if ($QueryEvnStatus_id_old != $data['QueryEvnStatus_id']) {
			$this->addToHistory(array(
				'QueryEvn_id' => $data['QueryEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			), 1);
			
			// Отправка всплышвающего сообщения
			$this->sendAutoMessage($data);
		}
		
		return $result;
	}
	
	
	
	/**
	* Определение исполнителя и ответственного
	*/
	function findAnswerUsers($data) {
		// исполнитель
		$usr = $this->getFirstRowFromQuery("select QueryEvnUser_id  as \"QueryEvnUser_id\" from QueryEvnUser  where QueryEvn_id = ? and QueryEvnUserType_id = 2", array($data['QueryEvn_id']));

		if (!$usr) {
			$usr = $this->getFirstRowFromQuery("			
				select
					evn.Evn_id as \"Evn_id\",
					evn.Lpu_id as \"Lpu_id\",
					coalesce(evpl.MedStaffFact_id,es.MedStaffFact_id,eup.MedStaffFact_id) as \"MedStaffFact_id\",
					coalesce(evpl.MedPersonal_id,es.MedPersonal_id,eup.MedPersonal_id) as \"MedPersonal_id\",
					usr.PMUser_id as \"PMUser_id\"
				from v_Evn evn  

					left join v_EvnPL epl  on epl.EvnPL_id = evn.Evn_id

					LEFT JOIN LATERAL (

						select *
						from v_EvnVizitPL 

						where EvnVizitPL_pid = epl.EvnPL_id and MedStaffFact_id is not null
						order by EvnVizitPL_setDT desc
                       	limit 1
					) evpl ON true
					left join v_EvnPS eps  on eps.EvnPS_id = evn.Evn_id

					LEFT JOIN LATERAL (

						select *
						from v_EvnSection 

						where EvnSection_pid = eps.EvnPS_id and MedStaffFact_id is not null
						order by EvnSection_setDT desc
                        limit 1
					) es ON true
					left join v_EvnUslugaPar eup  on eup.EvnUslugaPar_id = evn.Evn_id

					left join v_pmUserCache usr  on usr.MedPersonal_id = coalesce(evpl.MedPersonal_id,es.MedPersonal_id,eup.MedPersonal_id)

				where 
					evn.Evn_id = :Evn_id
			", array(
				'Evn_id' => $data['Evn_id']
			));
			if($usr) {
				$prm = $data;
				$prm['QueryEvnUser_id'] = null;
				$prm['MedStaffFact_id'] = $usr['MedStaffFact_id'];
				$prm['MedPersonal_id'] = $usr['MedPersonal_id'];
				$prm['pmUser_rid'] = $usr['PMUser_id'];
				$this->saveUserFirst($prm, 2);
			}
		}
		$usr = $this->getFirstRowFromQuery("select QueryEvnUser_id  as \"QueryEvnUser_id\" from QueryEvnUser  where QueryEvn_id = ? and QueryEvnUserType_id = 3", array($data['QueryEvn_id']));

		// ответственный
		if (!$usr) {
			$usr = $this->getFirstRowFromQuery("
				select 
				    PMUser_id as \"PMUser_id\", 
				    MedPersonal_id as \"MedPersonal_id\"
				from v_pmUserCache usr
				inner join v_Evn evn  on evn.Lpu_id = usr.Lpu_id

				LEFT JOIN LATERAL (

					select count(*) cnt
					from QueryEvn qe 

					inner join QueryEvnUser qeu  on qeu.QueryEvn_id = qe.QueryEvn_id

					where 
						qeu.QueryEvnUserType_id = 3 and 
						qe.QueryEvnStatus_id != 2
				) qecnt ON true
				where
					evn.Evn_id = :Evn_id and
					PATINDEX('%QueryEvnResp%', usr.pmUser_groups) > 0
				order by 
					qecnt.cnt desc
                limit 1
			", array(
				'Evn_id' => $data['Evn_id']
			));
			if($usr) {
				$prm = $data;
				$prm['QueryEvnUser_id'] = null;
				$prm['MedStaffFact_id'] = null;
				$prm['MedPersonal_id'] = $usr['MedPersonal_id'];
				$prm['pmUser_rid'] = $usr['PMUser_id'];
				$this->saveUserFirst($prm, 3);
			}
		}
	}
	
	/**
	* Сохранение пользователей (первоначальное)
	*/
	function saveUserFirst($data, $QueryEvnUserType_id) {

		$params = array(
			'QueryEvnUser_id' => empty($data['QueryEvnUser_id']) ? null : $data['QueryEvnUser_id'],
			'QueryEvn_id' => $data['QueryEvn_id'],
			'QueryEvnUserType_id' => $QueryEvnUserType_id,
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_msid' => !empty($data['MedPersonal_msid']) ? $data['MedPersonal_msid'] : null,
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'QueryEvnUser_begDate' => empty($data['QueryEvnUser_begDate']) ? date('Y-m-d') : $data['QueryEvnUser_begDate'],
			'QueryEvnUser_endDate' =>  empty($data['QueryEvnUser_endDate']) ? null : $data['QueryEvnUser_endDate'],
			'pmUser_rid' => $data['pmUser_rid'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure = empty($data['QueryEvnUser_id']) ? 'p_QueryEvnUser_ins' : 'p_QueryEvnUser_upd';

		$sql = "
			select QueryEvnUser_id as \"QueryEvnUser_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				QueryEvnUser_id := :QueryEvnUser_id,
				QueryEvn_id := :QueryEvn_id,
				QueryEvnUserType_id := :QueryEvnUserType_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_msid := :MedPersonal_msid,
				QueryEvnUser_begDate := :QueryEvnUser_begDate,
				QueryEvnUser_endDate := :QueryEvnUser_endDate,
				pmUser_rid := :pmUser_rid,
				pmUser_id := :pmUser_id);
		";
		
		return $this->queryResult($sql, $params);
	}
	
	/**
	* Сохранение сообщений
	*/
	function saveMessage($data) {
		
		// запрос
		if (empty($data['QueryEvnStatus_id']) || ($data['QueryEvnStatus_id'] == 1 && in_array($data['scenario'], array(1,2)))) {
			$msg = $this->getFirstRowFromQuery("
				select
					qem.QueryEvnMessage_id as \"QueryEvnMessage_id\",
					qem.QueryEvnMessage_Text as \"QueryEvnMessage_Text\",
					qem.QueryEvnUser_id as \"QueryEvnUser_id\"
				from QueryEvnMessage qem  
				where 
					qem.QueryEvn_id = :QueryEvn_id and 
					qem.pmUser_insID = :pmUser_id
                limit 1
			", array(
				'QueryEvn_id' => $data['QueryEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			if(!$msg || $msg['QueryEvnMessage_Text'] != $data['QueryEvnMessage_TextRequest']) {
				$procedure = !$msg ? 'p_QueryEvnMessage_ins' : 'p_QueryEvnMessage_upd';
				$data['QueryEvnMessage_id'] = !$msg ? null : $msg['QueryEvnMessage_id'];
				$data['QueryEvnMessage_Text'] = $data['QueryEvnMessage_TextRequest'];
				$sql = "
					select QueryEvnMessage_id as \"QueryEvnMessage_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from {$procedure}(
						QueryEvnMessage_id := :QueryEvnMessage_id,
						QueryEvn_id := :QueryEvn_id,
						QueryEvnUser_id := :QueryEvnUser_id,
						QueryEvnMessage_Text := :QueryEvnMessage_Text,
						pmUser_id := :pmUser_id);
				";
				
				$res = $this->queryResult($sql, $data);
			}
		} else { // ответ
			foreach ($data['QueryEvnMessageAnswers'] as $ans) {
				$ans = (array)$ans;
				$msg = false;
				if ($ans['QueryEvnMessage_id'] > 0) {
					$msg = $this->getFirstRowFromQuery("
						select 
							qem.QueryEvnMessage_id as \"QueryEvnMessage_id\",
							qem.QueryEvnMessage_Text as \"QueryEvnMessage_Text\",
							qem.QueryEvnUser_id as \"QueryEvnUser_id\"
						from QueryEvnMessage qem  

						where 
							qem.QueryEvnMessage_id = :QueryEvnMessage_id
						limit 1
					", array(
						'QueryEvnMessage_id' => $ans['QueryEvnMessage_id']
					));
				}
				
				if(!$msg || $msg['QueryEvnMessage_Text'] != $data['QueryEvnMessage_TextResponse']) {
					$procedure = !$msg ? 'p_QueryEvnMessage_ins' : 'p_QueryEvnMessage_upd';
					$data['QueryEvnMessage_id'] = !$msg ? null : $msg['QueryEvnMessage_id'];
					$data['QueryEvnMessage_Text'] = $data['QueryEvnMessage_TextResponse'];
					$sql = "
						select QueryEvnMessage_id as \"QueryEvnMessage_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from {$procedure}(
							QueryEvnMessage_id := :QueryEvnMessage_id,
							QueryEvn_id := :QueryEvn_id,
							QueryEvnUser_id := :QueryEvnUser_id,
							QueryEvnMessage_Text := :QueryEvnMessage_Text,
							pmUser_id := :pmUser_id);
					";
					
					$res = $this->queryResult($sql, $data);
				}
				
				$data['QueryEvnMessage_id'] = $msg ? $msg['QueryEvnMessage_id'] : $res[0]['QueryEvnMessage_id'];
				$data['QueryEvnMessageFile'] = $ans['files'];
				
				$this->saveFiles($data);
			}
		}
	}
	
	/**
	* Сохранение файлов
	*/
	function saveFiles($data) {
		
		$files = $this->queryList("
			select COALESCE(EvnXml_id,-EvnMediadata_id) as \"EvnXml_id\"

			from QueryEvnMessageFile  

			where QueryEvnMessage_id = :QueryEvnMessage_id", array(
			'QueryEvnMessage_id' => $data['QueryEvnMessage_id']
		));
		
		// добавляем то, чего ещё нет
		foreach($data['QueryEvnMessageFile'] as $file) {
			if (!in_array($file, $files)) {
				$sql = "
					select QueryEvnMessageFile_id as \"QueryEvnMessageFile_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from p_QueryEvnMessageFile_ins(
						QueryEvnMessageFile_id := :QueryEvnMessageFile_id,
						QueryEvnMessage_id := :QueryEvnMessage_id,
						EvnMediadata_id := :EvnMediadata_id,
						EvnXml_id := :EvnXml_id,
						pmUser_id := :pmUser_id);
				";
				
				$this->queryResult($sql, array(
					'QueryEvnMessageFile_id' => null,
					'QueryEvnMessage_id' => $data['QueryEvnMessage_id'],
					'EvnMediadata_id' => $file > 0 ? null : -$file,
					'EvnXml_id' => $file > 0 ? $file : null,
					'pmUser_id' => $data['pmUser_id'],
				));
			}
		}
		
		// то, что было в БД, но уже нет на форме - удаляем
		$delfiles = array_diff($files, $data['QueryEvnMessageFile']);
		foreach($delfiles as $file) {
			$sql = "
				select Error_Code as \"Error_Code\",
                       Error_Message as \"Error_Msg\"
                from p_QueryEvnMessageFile_del(
                    QueryEvnMessageFile_id :=
                     (
                       select QueryEvnMessageFile_id
                       from QueryEvnMessageFile
                       where EvnMediadata_id = :EvnMediadata_id or
                             EvnXml_id = :EvnXml_id
                       limit 1
                     ));
			";
			
			$this->queryResult($sql, array(
				'EvnMediadata_id' => $file > 0 ? null : -$file,
				'EvnXml_id' => $file > 0 ? $file : null,
			));
		}
	}
	
	/**
	* Список случаев пациента
	*/
	function loadEvnList($data) {
		$filter = '';
		if(!empty($data['Evn_id'])) {
			$filter = ' and evn.Evn_id = :Evn_id ';
		}
		
		$sql = "
			select
				evn.Evn_id as \"Evn_id\",
				evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				case 
					when evn.EvnClass_SysNick = 'EvnPL' then 'Случай амбул. лечения №' || epl.EvnPL_NumCard
					when evn.EvnClass_SysNick = 'EvnPS' then 'Случай стационар. лечения №' || eps.EvnPS_NumCard
					when evn.EvnClass_SysNick = 'EvnUslugaPar' then 'Выполнение услуги ' || uc.UslugaComplex_Name
				end 
				|| ' / ' 
				|| l.Lpu_Nick
				|| ' / ' 
				|| COALESCE(ls.LpuSection_Name,'') as \"Evn_Name\"

			from v_Evn evn  

				left join v_EvnPL epl  on epl.EvnPL_id = evn.Evn_id

				left join v_EvnPS eps  on eps.EvnPS_id = evn.Evn_id

				left join v_EvnUslugaPar eup  on eup.EvnUslugaPar_id = evn.Evn_id

				left join v_EvnDirection_all ed  on ed.EvnDirection_id = eup.EvnDirection_id

				left join v_UslugaComplex uc  on uc.UslugaComplex_id = eup.UslugaComplex_id

				inner join v_Lpu l  on l.Lpu_id = evn.Lpu_id

				left join v_LpuSection ls  on ls.LpuSection_id = coalesce(epl.LpuSection_id,eps.LpuSection_id,eup.LpuSection_uid)

			where 
				evn.Person_id = :Person_id and 
				evn.EvnClass_id in (3,30,47) and
				evn.Evn_setDate is not null and
				(ed.EvnStatus_id = 15 or evn.EvnClass_SysNick != 'EvnUslugaPar')
				{$filter}
		";
		
		return $this->queryResult($sql, $data);
	}
	
	/**
	* Список документов по случаю
	*/
	function doLoadEvnXmlList($data) {
		$sql = "
			select 
				EvnXml_id as \"EvnXml_id\",
				EvnXml_Name as \"EvnXml_Name\",
				FilePath as \"FilePath\",
				to_char(EvnXml_updDT, 'DD.MM.YYYY') as \"EvnXml_updDT\",

				pmuser.pmUser_Name as \"pmUser_Name\",
				isFile as \"isFile\"
			from (
				select
					doc.EvnXml_id,
					doc.EvnXml_Name,
					null as FilePath,
					doc.EvnXml_updDT,
					doc.pmUser_updID,
					0 as isFile
				from v_EvnXml doc  

				inner join v_Evn evn  on evn.Evn_id = doc.Evn_id

				where 
					(evn.Evn_id = :Evn_id or evn.Evn_pid = :Evn_id) and 
					doc.XmlType_id in (8,9,10,2, 4,7,17)
					
				union all
					
				select
					-doc.EvnMediadata_id as EvnXml_id,
					doc.EvnMediadata_FileName as EvnXml_Name,
					doc.EvnMediadata_FilePath as FilePath,
					doc.EvnMediadata_updDT as EvnXml_updDT,
					doc.pmUser_updID,
					1 as isFile
				from v_EvnMediadata doc  

				where 
					doc.Evn_id = :Evn_id
			) as t
			left join v_pmUserCache pmuser  on pmuser.PMUser_id = t.pmUser_updID

			order by t.EvnXml_updDT asc
		";
		
		return $this->queryResult($sql, $data);		
	}
	
	/**
	* Список пользователей
	*/
	function loadUsersList($data) {
		
		// исполнители
		if ($data['QueryEvnUserType_id'] == 2) {
			$sql = "
				select distinct
					usr.PMUser_id::text || coalesce(evpl.MedStaffFact_id::text,es.MedStaffFact_id::text,eup.MedStaffFact_id::text, '') as \"uid\",
					coalesce(evpl.MedStaffFact_id,es.MedStaffFact_id,eup.MedStaffFact_id) as \"MedStaffFact_id\",
					coalesce(evpl.MedPersonal_id,es.MedPersonal_id,eup.MedPersonal_id) as \"MedPersonal_id\",
					usr.PMUser_id as \"PMUser_id\",
					mp.Person_Fin || ' / ' || COALESCE(lpu.Lpu_Nick, '') || ' / ' || COALESCE(ps.PostMed_Name,mp.Dolgnost_Name) as \"Person_Fin\"

				from v_Evn evn  

					left join v_EvnVizitPL evpl  on evpl.EvnVizitPL_pid = evn.Evn_id

					left join v_EvnSection es  on es.EvnSection_pid = evn.Evn_id

					left join v_EvnUslugaPar eup  on eup.EvnUslugaPar_id = evn.Evn_id

					LEFT JOIN LATERAL (

						select * from 
						v_pmUserCache 

						where MedPersonal_id = coalesce(evpl.MedPersonal_id,es.MedPersonal_id,eup.MedPersonal_id)
                        limit 1
					) usr ON true
					inner join v_MedPersonal mp  on mp.MedPersonal_id = coalesce(evpl.MedPersonal_id,es.MedPersonal_id,eup.MedPersonal_id)

					left join v_MedStaffFact msf  on msf.MedStaffFact_id = coalesce(evpl.MedStaffFact_id,es.MedStaffFact_id,eup.MedStaffFact_id)

					left join v_PostMed ps  on ps.PostMed_id=msf.Post_id

					left join v_Lpu lpu  on lpu.Lpu_id = COALESCE(msf.Lpu_id,evn.Lpu_id)


				where evn.Evn_id = :Evn_id
			";
		} 
		
		// ответственные
		if ($data['QueryEvnUserType_id'] == 3) {
			$sql = "
				select 
					usr.MedPersonal_id as \"MedPersonal_id\",
					null as \"MedStaffFact_id\",
					usr.PMUser_id as \"PMUser_id\",
					usr.PMUser_Name as \"Person_Fin\"
				from v_Evn evn  

				inner join v_pmUserCache usr  on usr.Lpu_id = evn.Lpu_id

				where 
					evn.Evn_id = :Evn_id and 
					PATINDEX('%QueryEvnResp%', usr.pmUser_groups) > 0
			";
		}
		
		return $this->queryResult($sql, $data);		
	}
	
	/**
	* Сохранение пользователей
	*/
	function saveUser($data) {
		
		// Ищем активного
		$user = $this->getFirstRowFromQuery("
			select 
                QueryEvnUser_id as \"QueryEvnUser_id\",
                QueryEvn_id as \"QueryEvn_id\",
                QueryEvnUserType_id as \"QueryEvnUserType_id\",
                MedPersonal_id as \"MedPersonal_id\",
                MedStaffFact_id as \"MedStaffFact_id\",
                MedPersonal_msid as \"MedPersonal_msid\",
                QueryEvnUser_begDate as \"QueryEvnUser_begDate\",
                QueryEvnUser_endDate as \"QueryEvnUser_endDate\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                QueryEvnUser_insDT as \"QueryEvnUser_insDT\",
                QueryEvnUser_updDT as \"QueryEvnUser_updDT\",
                pmUser_rid as \"pmUser_rid\"
			from QueryEvnUser  
			where 
				QueryEvn_id = :QueryEvn_id and 
				QueryEvnUserType_id = :QueryEvnUserType_id and
				QueryEvnUser_endDate is null
            limit 1
		", array(
			'QueryEvn_id' => $data['QueryEvn_id'],
			'QueryEvnUserType_id' => $data['QueryEvnUserType_id']
		));
		
		if (
			$user && 
			$user['MedStaffFact_id'] == $data['MedStaffFact_id'] && 
			$user['MedPersonal_id'] == $data['MedPersonal_id'] && 
			$user['pmUser_rid'] == $data['pmUser_rid']
		) {
			return array(array('success' => true));
		}
		
		// Если есть - закрываем
		if (is_array($user)) {
			$sql = "
				select QueryEvnUser_id as \"QueryEvnUser_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_QueryEvnUser_upd(
					QueryEvnUser_id := :QueryEvnUser_id,
					QueryEvn_id := :QueryEvn_id,
					QueryEvnUserType_id := :QueryEvnUserType_id,
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					MedPersonal_msid := :MedPersonal_msid,
					QueryEvnUser_begDate := :QueryEvnUser_begDate,
					QueryEvnUser_endDate := :QueryEvnUser_endDate,
					pmUser_rid := :pmUser_rid,
					pmUser_id := :pmUser_id);
			";
			
			$this->queryResult($sql, array(
				'QueryEvnUser_id' => $user['QueryEvnUser_id'],
				'QueryEvn_id' => $user['QueryEvn_id'],
				'QueryEvnUserType_id' => $data['QueryEvnUserType_id'],
				'MedPersonal_id' => $user['MedPersonal_id'],
				'MedStaffFact_id' => $user['MedStaffFact_id'],
				'MedPersonal_msid' => null,
				'MedStaffFact_id' => $user['MedStaffFact_id'],
				'QueryEvnUser_begDate' => $user['QueryEvnUser_begDate'],
				'QueryEvnUser_endDate' => date('Y-m-d H:i:s'),
				'pmUser_rid' => $user['pmUser_rid'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		
		// Сохраняем нового
		$sql = "
			select QueryEvnUser_id as \"QueryEvnUser_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_QueryEvnUser_ins(
				QueryEvnUser_id := :QueryEvnUser_id,
				QueryEvn_id := :QueryEvn_id,
				QueryEvnUserType_id := :QueryEvnUserType_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_msid := :MedPersonal_msid,
				QueryEvnUser_begDate := :QueryEvnUser_begDate,
				QueryEvnUser_endDate := :QueryEvnUser_endDate,
				pmUser_rid := :pmUser_rid,
				pmUser_id := :pmUser_id);
		";
		
		$result = $this->queryResult($sql, array(
			'QueryEvnUser_id' => null,
			'QueryEvn_id' => $data['QueryEvn_id'],
			'QueryEvnUserType_id' => $data['QueryEvnUserType_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_msid' => null,
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'QueryEvnUser_begDate' => date('Y-m-d H:i:s'),
			'QueryEvnUser_endDate' => null,
			'pmUser_rid' => $data['pmUser_rid'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if ($data['QueryEvnUserType_id'] == 2) {
			$this->addToHistory(array(
				'QueryEvn_id' => $data['QueryEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			), 2);
		} elseif($data['QueryEvnUserType_id'] == 3) {
			$this->addToHistory(array(
				'QueryEvn_id' => $data['QueryEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			), 3);
		}
		
		return $result;
	}
	
	/**
	* Прикрепление файлов из ЭМК
	*/
	function addDoc($data) {
		
		if (!is_array($data['loadedFiles'])) return false;
		
		//$data['pmUser_id'] = 229523552;
		
		foreach($data['loadedFiles'] as $file) {
			
			$QueryEvnUser_id = $this->getFirstResultFromQuery("
				select qeu.QueryEvnUser_id as \"QueryEvnUser_id\"
				from QueryEvnUser qeu  
				where 
					qeu.QueryEvn_id = :QueryEvn_id and 
					qeu.QueryEvnUserType_id = 2 and 
					qeu.QueryEvnUser_endDate is null and 
					qeu.pmUser_rid = :pmUser_id
			", array(
				'QueryEvn_id' => $file->QueryEvn_id,
				'pmUser_id' => $data['pmUser_id']
			));
			
			$msg = $this->getFirstRowFromQuery("
				select 
					qem.QueryEvnMessage_id as \"QueryEvnMessage_id\",
					qem.QueryEvnMessage_Text as \"QueryEvnMessage_Text\",
					qem.QueryEvnUser_id as \"QueryEvnUser_id\"
				from QueryEvnMessage qem  

				where 
					qem.QueryEvn_id = :QueryEvn_id and 
					qem.pmUser_insID = :pmUser_id
                limit 1
			", array(
				'QueryEvn_id' => $file->QueryEvn_id,
				'pmUser_id' => $data['pmUser_id']
			));
			
			if (!$msg) {
				$sql = "
					select QueryEvnMessage_id as \"QueryEvnMessage_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from p_QueryEvnMessage_ins(
						QueryEvnMessage_id := :QueryEvnMessage_id,
						QueryEvn_id := :QueryEvn_id,
						QueryEvnUser_id := :QueryEvnUser_id,
						QueryEvnMessage_Text := ' ',
						pmUser_id := :pmUser_id);
				";
				$tmp = $this->queryResult($sql, array(
					'QueryEvn_id' => $file['QueryEvn_id'],
					'QueryEvnUser_id' => $QueryEvnUser_id,
					'pmUser_id' => $data['pmUser_id']
				));
				$msg['QueryEvnMessage_id'] = $tmp[0]['QueryEvnMessage_id'];
			}
			
			$sql = "
				select QueryEvnMessageFile_id as \"QueryEvnMessageFile_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_QueryEvnMessageFile_ins(
					QueryEvnMessageFile_id := :QueryEvnMessageFile_id,
					QueryEvnMessage_id := :QueryEvnMessage_id,
					EvnMediadata_id := :EvnMediadata_id,
					EvnXml_id := null,
					pmUser_id := :pmUser_id);
			";
			$this->queryResult($sql, array(
				'QueryEvnMessage_id' => $msg['QueryEvnMessage_id'],
				'EvnMediadata_id' => $file->EvnMediadata_id,
				'pmUser_id' => $data['pmUser_id'],
			));
		}
		
		return array(array('success' => true));
	}
	
	

	/**
	 * Сохранение состояния в историю
	 */
	function addToHistory($data, $QueryEvnUpdType_id) {
		
		$sql = "
			select
				qe.QueryEvn_id as \"QueryEvn_id\"
				,qe.QueryEvnStatus_id as \"QueryEvnStatus_id\"
				,qeuExec.MedPersonal_id as \"MedPersonal_id\"
				,qeuExec.MedStaffFact_id as \"MedStaffFact_id\"
				,qeuResp.pmUser_rid as \"pmUser_rid\"
			from
				v_QueryEvn qe 

				left join QueryEvnUser qeuExec  on qeuExec.QueryEvn_id = qe.QueryEvn_id and qeuExec.QueryEvnUserType_id = 2 and qeuExec.QueryEvnUser_endDate is null -- Исполнитель

				left join QueryEvnUser qeuResp  on qeuResp.QueryEvn_id = qe.QueryEvn_id and qeuResp.QueryEvnUserType_id = 3 and qeuResp.QueryEvnUser_endDate is null -- Ответственный

			where
				QE.QueryEvn_id = :QueryEvn_id
		";
		
		$params = $this->getFirstRowFromQuery($sql, array('QueryEvn_id' => $data['QueryEvn_id']));
		$params['pmUser_id'] = $data['pmUser_id'];
		
		$sql = "
			select QueryEvnUpd_id as \"QueryEvnUpd_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_QueryEvnUpd_ins(
				QueryEvn_id := :QueryEvn_id,
				QueryEvnUpdType_id := :QueryEvnUpdType_id,
				QueryEvnStatus_id := :QueryEvnStatus_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				pmUser_rid := :pmUser_rid,
				pmUser_id := :pmUser_id);
		";
		$this->queryResult($sql, array(
			'QueryEvn_id' => $params['QueryEvn_id'],
			'QueryEvnUpdType_id' => $QueryEvnUpdType_id,
			'QueryEvnStatus_id' => $params['QueryEvnStatus_id'],
			'MedPersonal_id' => $params['MedPersonal_id'],
			'MedStaffFact_id' => $params['MedStaffFact_id'],
			'pmUser_rid' => $params['pmUser_rid'],
			'pmUser_id' => $params['pmUser_id'],
		));
	}
	
	
	/**
	 * Отправка всплышвающего сообщения
	 */
	function sendAutoMessage($data) {
		
		$this->load->model('Messages_model', 'Messages_model');
		
		$sql = "
			select
				qe.QueryEvn_id as \"QueryEvn_id\"
				,qe.QueryEvnStatus_id as \"QueryEvnStatus_id\"
				,qeuExec.MedPersonal_id as \"MedPersonal_id\"
				,qeuExec.MedStaffFact_id as \"MedStaffFact_id\"
				,qeuCreat.pmUser_rid as \"pmUser_crid\"
				,qeuResp.pmUser_rid as \"pmUser_rid\"
				,l.Lpu_id as \"Lpu_id\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,case 
					when evn.EvnClass_SysNick = 'EvnPL' then 'Случай амбул. лечения №' || epl.EvnPL_NumCard
					when evn.EvnClass_SysNick = 'EvnPS' then 'Случай стационар. лечения №' || eps.EvnPS_NumCard
					when evn.EvnClass_SysNick = 'EvnUslugaPar' then 'Выполнение услуги ' || uc.UslugaComplex_Name
				end as \"Evn_Name\"
				,rtrim(rtrim(COALESCE(ps.Person_Surname, '')) || ' ' || rtrim(COALESCE(ps.Person_Firname, '')) || ' ' || rtrim(COALESCE(ps.Person_Secname, ''))) as \"Person_Fio\"

			from
				v_QueryEvn qe 

				left join QueryEvnUser qeuCreat  on qeuCreat.QueryEvn_id = qe.QueryEvn_id and qeuCreat.QueryEvnUserType_id = 1 and qeuCreat.QueryEvnUser_endDate is null -- Автор

				left join QueryEvnUser qeuExec  on qeuExec.QueryEvn_id = qe.QueryEvn_id and qeuExec.QueryEvnUserType_id = 2 and qeuExec.QueryEvnUser_endDate is null -- Исполнитель

				left join QueryEvnUser qeuResp  on qeuResp.QueryEvn_id = qe.QueryEvn_id and qeuResp.QueryEvnUserType_id = 3 and qeuResp.QueryEvnUser_endDate is null -- Ответственный

				inner join v_Evn evn  on evn.Evn_id = qe.Evn_id

				inner join v_PersonState ps  on ps.Person_id = evn.Person_id

				inner join v_Lpu l  on l.Lpu_id = evn.Lpu_id

				left join v_EvnPL epl  on epl.EvnPL_id = qe.Evn_id

				left join v_EvnPS eps  on eps.EvnPS_id = qe.Evn_id

				left join v_EvnUslugaPar eup  on eup.EvnUslugaPar_id = qe.Evn_id

				left join v_UslugaComplex uc  on uc.UslugaComplex_id = eup.UslugaComplex_id

			where
				QE.QueryEvn_id = :QueryEvn_id
		";
		
		$params = $this->getFirstRowFromQuery($sql, array('QueryEvn_id' => $data['QueryEvn_id']));
		
		//запрос
		if ($data['QueryEvnStatus_id'] == 1) {
			
			$text = 'Получен новый запрос по '.$params['Evn_Name']. '<br>
				Пациент: '.$params['Person_Fio']. '<br><br>
				<a href="#" onclick="getWnd(\'swQueryEvnEditWindow\').show({QueryEvn_id: '.$params['QueryEvn_id']. '}); return false;">Открыть</a>
			';
			
			// Исполнителю
			if (!empty($params['MedPersonal_id'])) {
				$this->Messages_model->autoMessage(array(
					'pmUser_id' => $data['pmUser_id'],
					'Lpu_rid' => $params['Lpu_id'],
					'MedPersonal_rid' => $params['MedPersonal_id'],
					'type' => 1,
					'autotype' => 1,
					'title' => 'Новый запрос от ' . $params['Lpu_Nick'],
					'text' => $text
				));
			}
			
			// Ответственному
			if (!empty($params['pmUser_rid'])) {
				$this->Messages_model->autoMessage(array(
					'pmUser_id' => $data['pmUser_id'],
					'User_rid' => $params['pmUser_rid'],
					'type' => 1,
					'autotype' => 1,
					'title' => 'Новый запрос от ' . $params['Lpu_Nick'],
					'text' => $text
				));
			}
		}
		
		// ответ
		if ($data['QueryEvnStatus_id'] == 2) {
			
			$text = 'Запрос по '.$params['Evn_Name']. ' пациента '.$params['Person_Fio']. ' выполнен<br><br>
				<a href="#" onclick="getWnd(\'swQueryEvnEditWindow\').show({QueryEvn_id: '.$params['QueryEvn_id']. '}); return false;">Открыть</a>
			';
			
			// Автору
			if (!empty($params['pmUser_crid'])) {
				$this->Messages_model->autoMessage(array(
					'pmUser_id' => $data['pmUser_id'],
					'User_rid' => $params['pmUser_crid'],
					'type' => 1,
					'autotype' => 1,
					'title' => 'Запрос выполнен',
					'text' => $text
				));
			}
		}
		
	}

	/**
	 * Отправка запроса
	 */
	function send($data) {
		
		$QueryEvnStatus_id = $this->getFirstResultFromQuery("select QueryEvnStatus_id  as \"QueryEvnStatus_id\" from QueryEvn  where QueryEvn_id = ?", array($data['QueryEvn_id']));

		
		$QueryEvnStatus_id++;
		
		if ($QueryEvnStatus_id > 2) return false;
		
		$this->db->query("update QueryEvn set QueryEvnStatus_id = :QueryEvnStatus_id where QueryEvn_id = :QueryEvn_id", array(
			'QueryEvn_id' => $data['QueryEvn_id'],
			'QueryEvnStatus_id' => $QueryEvnStatus_id
		));
		
		if ($QueryEvnStatus_id == 1) {
			$data['Evn_id'] = $this->getFirstResultFromQuery("select Evn_id  as \"Evn_id\" from QueryEvn  where QueryEvn_id = ?", array($data['QueryEvn_id']));

			// определяем исполнителя и ответственного, если нет
			$this->findAnswerUsers($data);
		}
		
		$this->addToHistory(array(
			'QueryEvn_id' => $data['QueryEvn_id'],
			'pmUser_id' => $data['pmUser_id']
		), 1);
			
		return array(array('success' => true));
	}

}