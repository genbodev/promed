<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Treatment_model - модель журнала регистрации обращений, основная таблица Treatment
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      5.08.2010
*/

class Treatment_model extends SwPgModel {

	private $object = '';

	/**
	 *	Method description
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Method description
	 */
	private function getObject($data) {
		if (empty($data['Object']))
			$data['Object'] = '';
		switch ($data['Object']) {
			case 'TreatmentCat':
				return 'TreatmentCat';
				break;
			case 'TreatmentMethodDispatch':
				return 'TreatmentMethodDispatch';
				break;
			case 'TreatmentRecipientType':
				return 'TreatmentRecipientType';
				break;
			default:
				exit(json_encode(array('success' => false, 'Error_Code' => 777 , 'Error_Msg' => toUTF('Неправильный параметр Object'))));
		}
	}

	/**
	* Сохраняет обращение
	*/
  function saveTreatment($data) {
		//$this->object = $this->getObject($data);
		$procedure_action = '';
		// Сохраняем или редактируем запись
		if ( empty($data['Treatment_id']) )
		{
			$data['Treatment_id'] = NULL;
			$procedure_action = "ins";
			$out = "output";
		}
		else
		{
			$procedure_action = "upd";
			$out = "";
		}

 
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            Treatment_id as \"id\"
        from p_Treatment_" . $procedure_action . "
            (
                Server_id := :Server_id,
				Treatment_id := :id,
				Treatment_Reg := :Treatment_Reg,
				Treatment_DateReg := :Treatment_DateReg,
				TreatmentUrgency_id := :TreatmentUrgency_id,
				TreatmentMultiplicity_id := :TreatmentMultiplicity_id,
				TreatmentSenderType_id := :TreatmentSenderType_id,
				Treatment_SenderDetails := :Treatment_SenderDetails,
				TreatmentType_id := :TreatmentType_id,
				TreatmentCat_id := :TreatmentCat_id,
				TreatmentRecipientType_id := :TreatmentRecipientType_id,
				Lpu_rid := :Lpu_rid,
				TreatmentSubjectType_id := :TreatmentSubjectType_id,
				Org_sid := :Org_sid,
				MedPersonal_sid := :MedPersonal_sid,
				MedPersonal_Lpu_sid := :MedPersonal_Lpu_sid,
				Lpu_sid := :Lpu_sid,
				Treatment_Text := :Treatment_Text,
				Treatment_Document := :Treatment_Document,
				TreatmentMethodDispatch_id := :TreatmentMethodDispatch_id,
				Treatment_Comment := :Treatment_Comment,
				TreatmentReview_id := :TreatmentReview_id,
				Treatment_SenderPhone := :Treatment_SenderPhone,
				Person_id := :Person_id,
				Treatment_DateReview := :Treatment_DateReview,
				pmUser_id := :pmUser_id
            )";


		$queryParams = array(
			'id' => $data['Treatment_id'],
			'Server_id' => $data['Server_id'],
			'Treatment_Reg' => $data['Treatment_Reg'],
			'Treatment_DateReg' => $data['Treatment_DateReg'],
			'TreatmentUrgency_id' => $data['TreatmentUrgency_id'],
			'TreatmentMultiplicity_id' => $data['TreatmentMultiplicity_id'],
			'TreatmentSenderType_id' => $data['TreatmentSenderType_id'],
			'Treatment_SenderDetails' => $data['Treatment_SenderDetails'],
			'TreatmentType_id' => $data['TreatmentType_id'],
			'TreatmentCat_id' => $data['TreatmentCat_id'],
			'TreatmentRecipientType_id' => $data['TreatmentRecipientType_id'],
			'Lpu_rid' => $data['Lpu_rid'],
			'TreatmentSubjectType_id' => $data['TreatmentSubjectType_id'],
			'Org_sid' => $data['Org_sid'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'MedPersonal_Lpu_sid' => $data['MedPersonal_Lpu_sid'],
			'Lpu_sid' => $data['Lpu_sid'],
			'Treatment_Text' => $data['Treatment_Text'],
			'Treatment_Document' => $data['Treatment_Document'],
			'TreatmentMethodDispatch_id' => $data['TreatmentMethodDispatch_id'],
			'Treatment_Comment' => $data['Treatment_Comment'],
			'TreatmentReview_id' => $data['TreatmentReview_id'],
			'Treatment_SenderPhone' => $data['Treatment_SenderPhone'],
			'Person_id' => $data['Person_id'],
			'Treatment_DateReview' => $data['Treatment_DateReview'],
			'pmUser_id' => $data['pmUser_id']
		);
		//die(getDebugSQL($query, $queryParams));
		$res = $this->queryResult($query, $queryParams);
		if ( count($res) && !empty($res[0]['id']) && !empty($data['TreatmentMethodDispatch_fid']) ) {
			$data['Treatment_id'] = $res[0]['id'];
			$data['TreatmentMethodDispatch_id'] = $data['TreatmentMethodDispatch_fid'];
			$this->saveTreatmentFeedback($data);
		}
		return $res;
	}

	/**
	 * Установка статуса для обращения
	 */
 function setStatusTreatment($data){
	

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            Treatment_id as \"id\"
        from p_Treatment_setStatus
            (
                Treatment_id := :Treatment_id,
				TreatmentReview_id := :TreatmentReview_id,
				Treatment_DateReview := dbo.tzGetDate(),
				pmUser_id := :pmUser_id
            )";

		$queryParams = array(
			'Treatment_id' => $data['Treatment_id'],
			'TreatmentReview_id' => $data['TreatmentReview_id'],
			'pmUser_id' => $data['pmUser_id']
			//'Treatment_DateReview' => '2018-07-23' - возможно потом будет задаваться через форму
		);
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Сохранение ответа на обращение
	 */
 function saveTreatmentFeedback($data){

     
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
           TreatmentFeedback_id as \"id\"
        from p_TreatmentFeedback_ins
            (
                TreatmentFeedback_id := null,
				Treatment_id := :Treatment_id,
				TreatmentFeedback_Message := :TreatmentFeedback_Message,
				TreatmentFeedback_Note := :TreatmentFeedback_Note,
				TreatmentMethodDispatch_id := :TreatmentMethodDispatch_id,
				TreatmentFeedback_Document := :TreatmentFeedback_Document,
				pmUser_id := :pmUser_id
            )";


		$queryParams = array(
			'Treatment_id' => $data['Treatment_id'],
			'TreatmentFeedback_Message' => $data['TreatmentFeedback_Message'],
			'TreatmentFeedback_Note' => $data['TreatmentFeedback_Note'],
			'TreatmentMethodDispatch_id' => $data['TreatmentMethodDispatch_id'],
			'TreatmentFeedback_Document' => $data['TreatmentFeedback_Document'],
			'pmUser_id' => $data['pmUser_id']
		);

		//die(getDebugSQL($query, $queryParams));

		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Method description
	 */
 function getTreatmentList($data) {
		$query = "";
		$where = "";
		if (isset($data['TreatmentType_id']) && $data['TreatmentType_id'] > 0) $where .= " AND t.TreatmentType_id = ".$data['TreatmentType_id'];
		if (isset($data['Treatment_DateReg']) && strlen($data['Treatment_DateReg']) > 0) $where .= " AND t.Treatment_DateReg = '".$data['Treatment_DateReg']."'";

		$query = "
			select
				-- select
				t.Treatment_id as \"Treatment_id\",
				u.PMUser_Login as \"PMUser\",
				t.Treatment_Reg as \"Treatment_Reg\",
				--t.Treatment_DateReg as \"Treatment_DateReg\",
				to_char(t.Treatment_DateReg, 'dd.mm.yyyy') as \"Treatment_DateReg\",
				tt.TreatmentType_Name as \"TreatmentType\",
				tst.TreatmentSenderType_Name as \"TreatmentSenderType\",
				t.Treatment_SenderDetails as \"Treatment_SenderDetails\",
				trt.TreatmentRecipientType_Name as \"TreatmentRecipientType\"
				-- end select
			from
				-- from
				v_Treatment t 
				left join v_TreatmentType tt  on tt.TreatmentType_id = t.TreatmentType_id
				left join v_TreatmentSenderType tst  on tst.TreatmentSenderType_id = t.TreatmentSenderType_id
				left join v_TreatmentRecipientType trt  on trt.TreatmentRecipientType_id = t.TreatmentRecipientType_id
				left join pmUserCache u  on u.pmUser_id = t.pmUser_insID
				-- end from
			where
				-- where
				(1=1) ".$where."
				-- end where
			order by
				-- order by
				t.Treatment_DateReg DESC
				-- end order by
		";
		$queryParams = array();
		$response = array();
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);
		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$count = $get_count_result->result('array');
			$response['totalCount'] = $count[0]['cnt'];
		} else {
			return false;
		}
		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}
		return $response;
	}

	/**
	*  Возвращает данные записи для редактирования
	*/
 function getTreatment($data) {
		$query = "
			SELECT 
				t.Treatment_id as \"Treatment_id\",
				t.Treatment_Reg as \"Treatment_Reg\",
				t.to_char(Treatment_DateReg,'dd.mm.yyyy') as \"Treatment_DateReg\",
				t.TreatmentUrgency_id as \"TreatmentUrgency_id\",
				t.TreatmentMultiplicity_id as \"TreatmentMultiplicity_id\",
				t.TreatmentSenderType_id as \"TreatmentSenderType_id\",
				RTRIM(t.Treatment_SenderDetails) as \"Treatment_SenderDetails\",
				t.TreatmentType_id as \"TreatmentType_id\",
				t.TreatmentCat_id as \"TreatmentCat_id\",
				t.TreatmentRecipientType_id as \"TreatmentRecipientType_id\",
				t.Lpu_rid as \"Lpu_rid\",
				t.TreatmentSubjectType_id as \"TreatmentSubjectType_id\",
				t.Org_sid as \"Org_sid\",
				t.MedPersonal_Lpu_sid as \"MedPersonal_Lpu_sid\",
				t.MedPersonal_sid as \"MedPersonal_sid\",
				t.Lpu_sid as \"Lpu_sid\",
				--as SubjectName
				RTRIM(t.Treatment_Text) as \"Treatment_Text\",
				RTRIM(t.Treatment_Document) as \"Treatment_Document\",
				t.TreatmentMethodDispatch_id as \"TreatmentMethodDispatch_id\",
				RTRIM(t.Treatment_Comment) as \"Treatment_Comment\",
				t.TreatmentReview_id as \"TreatmentReview_id\",
				t.Treatment_SenderPhone as \"Treatment_SenderPhone\",
				tf.TreatmentFeedback_Message as \"TreatmentFeedback_Message\",
				tf.TreatmentFeedback_Note as \"TreatmentFeedback_Note\",
				tf.TreatmentFeedback_Document as \"TreatmentFeedback_Document\",
				tf.TreatmentMethodDispatch_id as \"TreatmentMethodDispatch_fid\",
				t.Person_id as \"Person_id\",
				ps.Person_SurName || coalesce(' ' + ps.Person_FirName,'') || coalesce(' ' + ps.Person_SecName,'') as \"Person_Fio\",
				to_char(t.Treatment_DateReview,'dd.mm.yyyy') as \"Treatment_DateReview\"
			FROM v_Treatment t
			left join v_TreatmentFeedback tf on tf.Treatment_id = t.Treatment_id
			left join v_PersonState ps on ps.Person_id = t.Person_id
			WHERE t.Treatment_id = :id
            LIMIT 1
		";
		$res = $this->db->query($query, array('id' => $data['Treatment_id']));
		if ( is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	*  Возвращает данные записи для печати
	*/
function printTreatment($id) {
		$query = "
			SELECT 
				t.Treatment_id as \"Treatment_id\",
				RTRIM(t.Treatment_Reg) as \"Treatment_Reg\",
				to_char(t.Treatment_DateReg,'dd.mm.yyyy') as \"Treatment_DateReg\",
				RTRIM(tu.TreatmentUrgency_Name) as \"TreatmentUrgency\",
				RTRIM(tm.TreatmentMultiplicity_Name) as \"TreatmentMultiplicity\",
				RTRIM(tst.TreatmentSenderType_Name) as \"TreatmentSenderType\",
				RTRIM(t.Treatment_SenderDetails) as \"Treatment_SenderDetails\",
				RTRIM(tt.TreatmentType_Name) as \"TreatmentType\",
				RTRIM(tc.TreatmentCat_Name) as \"TreatmentCat\",
				RTRIM(trt.TreatmentRecipientType_Name) as \"TreatmentRecipientType\",
				RTRIM(lr.Lpu_Name) as \"Lpu_r\",
				RTRIM(tsubt.TreatmentSubjectType_Name) as \"TreatmentSubjectType\",
				RTRIM(o.Org_Name) as \"Org\",
				RTRIM(lm.Lpu_Name) as \"Lpu_m\",
				RTRIM(m.Person_Fio) as \"MedPersonal\",
				RTRIM(ls.Lpu_Name) as \"Lpu_s\",
				RTRIM(t.Treatment_Text) as \"Treatment_Text\",
				RTRIM(t.Treatment_Document) as \"Treatment_Document\",
				RTRIM(tmd.TreatmentMethodDispatch_Name) as \"TreatmentMethodDispatch\",
				RTRIM(t.Treatment_Comment) as \"Treatment_Comment\",
				RTRIM(tr.TreatmentReview_Name) as \"TreatmentReview\",
				to_char(t.Treatment_DateReview,'dd.mm.yyyy') as \"Treatment_DateReview\"
			FROM
				v_Treatment t 
				left join v_TreatmentUrgency tu  on tu.TreatmentUrgency_id = t.TreatmentUrgency_id
				left join v_TreatmentMultiplicity tm  on tm.TreatmentMultiplicity_id = t.TreatmentMultiplicity_id
				left join v_TreatmentSenderType tst  on tst.TreatmentSenderType_id = t.TreatmentSenderType_id
				left join v_TreatmentType tt  on tt.TreatmentType_id = t.TreatmentType_id
				left join v_TreatmentCat tc  on tc.TreatmentCat_id = t.TreatmentCat_id
				left join v_TreatmentRecipientType trt  on trt.TreatmentRecipientType_id = t.TreatmentRecipientType_id
				left join v_Lpu lr  on lr.Lpu_id = t.Lpu_rid
				left join v_TreatmentSubjectType tsubt  on tsubt.TreatmentSubjectType_id = t.TreatmentSubjectType_id
				left join v_Org o  on o.Org_id = t.Org_sid
				left join v_Lpu lm  on lm.Lpu_id = t.MedPersonal_Lpu_sid
				left join v_MedPersonal m  on m.MedPersonal_id = t.MedPersonal_sid
				left join v_Lpu ls  on ls.Lpu_id = t.Lpu_sid
				left join v_TreatmentMethodDispatch tmd  on tmd.TreatmentMethodDispatch_id = t.TreatmentMethodDispatch_id
				left join v_TreatmentReview tr  on tr.TreatmentReview_id = t.TreatmentReview_id
			WHERE
				t.Treatment_id = :id
            LIMIT 1
		";
		$res = $this->db->query($query, array('id' => $id));
		if ( is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	*					RTRIM(m.Person_Fio) as name,
	*					(SELECT COUNT( * ) FROM v_Treatment with(nolock) WHERE MedPersonal_sid = m.MedPersonal_id ".$where." ) as number
	*				FROM
	*					v_MedPersonal m
	*				WHERE 
	*					m.Lpu_id = :Lpu_sid AND (SELECT COUNT( * ) FROM v_Treatment with(nolock) WHERE MedPersonal_sid = m.MedPersonal_id ".$where." ) > 0
	*  Удаление записи
	*/
 function delItem($data) {
		$this->object = $this->getObject($data);

   
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
         from p_". $this->object ."_del
            (
                ". $this->object ."_id := :id
            )";



		$result = $this->db->query($query, array(
			'id' => $data['id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('success' => false, 'Error_Code' => 666 , 'Error_Msg' => toUTF('Ошибка запроса к базе данных при удалении записи! Возможные причины: удаление записи запрещено или запись не найдена.'));
		}
	}

	/**
	 *	Method description
	 */
  function getTreatmentSearchList($data) {
		$query = "";
		$where = "";
        if (isset($data['pmUser']) && $data['pmUser'] > 0)
            $where .= " AND t.pmUser_insID = ".$data['pmUser'];

        if (isset($data['Treatment_Reg']) && strlen($data['Treatment_Reg']) > 0)
            $where .= " AND t.Treatment_Reg ILIKE '%".$data['Treatment_Reg']."%'";

        if (isset($data['Treatment_DateReg_Start']) && strlen($data['Treatment_DateReg_Start']) > 0)
            $where .= " AND cast(t.Treatment_DateReg as date) >= '".$data['Treatment_DateReg_Start']."'";
        if (isset($data['Treatment_DateReg_End']) && strlen($data['Treatment_DateReg_End']) > 0)
            $where .= " AND cast(t.Treatment_DateReg as date) <= '".$data['Treatment_DateReg_End']."'";

        if (isset($data['TreatmentUrgency_id']) && $data['TreatmentUrgency_id'] > 0)
            $where .= " AND t.TreatmentUrgency_id = ".$data['TreatmentUrgency_id'];

        if (isset($data['TreatmentMultiplicity_id']) && $data['TreatmentMultiplicity_id'] > 0)
            $where .= " AND t.TreatmentMultiplicity_id = ".$data['TreatmentMultiplicity_id'];

        if (isset($data['TreatmentSenderType_id']) && $data['TreatmentSenderType_id'] > 0)
            $where .= " AND t.TreatmentSenderType_id = ".$data['TreatmentSenderType_id'];

        if (isset($data['Treatment_SenderDetails']) && strlen($data['Treatment_SenderDetails']) > 0)
            $where .= " AND t.Treatment_SenderDetails ILIKE '%".$data['Treatment_SenderDetails']."%'";

        if (isset($data['TreatmentMethodDispatch_id']) && $data['TreatmentMethodDispatch_id'] > 0)
            $where .= " AND t.TreatmentMethodDispatch_id = ".$data['TreatmentMethodDispatch_id'];

        if (isset($data['TreatmentType_id']) && $data['TreatmentType_id'] > 0)
            $where .= " AND t.TreatmentType_id = ".$data['TreatmentType_id'];

        if (isset($data['TreatmentCat_id']) && $data['TreatmentCat_id'] > 0)
            $where .= " AND t.TreatmentCat_id = ".$data['TreatmentCat_id'];

        if (isset($data['TreatmentRecipientType_id']) && $data['TreatmentRecipientType_id'] > 0)
            $where .= " AND t.TreatmentRecipientType_id = ".$data['TreatmentRecipientType_id'];

        if (isset($data['Lpu_rid']) && $data['Lpu_rid'] > 0)
            $where .= " AND t.Lpu_rid = ".$data['Lpu_rid'];

        if (isset($data['TreatmentReview_id']) && $data['TreatmentReview_id'] > 0)
            $where .= " AND t.TreatmentReview_id = ".$data['TreatmentReview_id'];

		if (isset($data['Person_id']) && $data['Person_id'] > 0) 
			$where .= " AND t.Person_id = ".$data['Person_id'];

        if (isset($data['Treatment_DateReview_Start']) && strlen($data['Treatment_DateReview_Start']) > 0)
            $where .= " AND cast(t.Treatment_DateReview as date) >= '".$data['Treatment_DateReview_Start']."'";
        if (isset($data['Treatment_DateReview_End']) && strlen($data['Treatment_DateReview_End']) > 0)
            $where .= " AND cast(t.Treatment_DateReview as date) <= '".$data['Treatment_DateReview_End']."'";

        $query = "
				select
					-- select
					t.Treatment_id as \"Treatment_id\",
					u.PMUser_Login as \"PMUser\",
					t.Treatment_Reg as \"Treatment_Reg\",
					--t.Treatment_DateReg as \"Treatment_DateReg\",
					TO_CHAR(t.Treatment_DateReg, 'dd.mm.yyyy') as \"Treatment_DateReg\",
					tt.TreatmentType_Name as \"TreatmentType\",
					tst.TreatmentSenderType_Name as \"TreatmentSenderType\",
					t.Treatment_SenderDetails as \"Treatment_SenderDetails\",
					trt.TreatmentRecipientType_Name as \"TreatmentRecipientType\",
					to_char(t.Treatment_DateReview, 'dd.mm.yyyy') as \"Treatment_DateReview\",
					tr.TreatmentReview_Name as \"TreatmentReview\",
					tr.TreatmentReview_id as \"TreatmentReview_id\"
					-- end select
				from
					-- from
					v_Treatment t 
					left join v_TreatmentType tt  on tt.TreatmentType_id = t.TreatmentType_id
					left join v_TreatmentSenderType tst  on tst.TreatmentSenderType_id = t.TreatmentSenderType_id
					left join v_TreatmentRecipientType trt  on trt.TreatmentRecipientType_id = t.TreatmentRecipientType_id
					left join v_TreatmentReview tr  on tr.TreatmentReview_id = t.TreatmentReview_id
					left join pmUserCache u  on u.pmUser_id = t.pmUser_insID
					-- end from
				where
					-- where
					(1=1) ".$where."
					-- end where
				order by
					-- order by
					t.Treatment_DateReg DESC
					-- end order by
			";
		$queryParams = array();
		$response = array();
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);
		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$count = $get_count_result->result('array');
			$response['totalCount'] = $count[0]['cnt'];
		} else {
			return false;
		}

		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}

	/**
	* Получает отчет
	*/
 function getTreatmentReport($data) {
		$where = "";
		$select=array();
		$left_join=array();
		$response = array();
		$response['params'] = array();
		$response['data'] = array();
		if (isset($data['Treatment_DateReg_Start']) && strlen($data['Treatment_DateReg_Start']) > 0)
			$where .= " AND cast(Treatment_DateReg as date) >= '". $data['Treatment_DateReg_Start'] ."'";

		if (isset($data['Treatment_DateReg_End']) && strlen($data['Treatment_DateReg_End']) > 0)
			$where .= " AND cast(Treatment_DateReg as date) <= '". $data['Treatment_DateReg_End'] ."'";

		if (isset($data['Treatment_DateReview_Start']) && strlen($data['Treatment_DateReview_Start']) > 0)
			$where .= " AND cast(Treatment_DateReview as date) >= '". $data['Treatment_DateReview_Start'] ."'";

		if (isset($data['Treatment_DateReview_End']) && strlen($data['Treatment_DateReview_End']) > 0)
			$where .= " AND cast(Treatment_DateReview as date) <= '". $data['Treatment_DateReview_End'] ."'";

		if (isset($data['TreatmentMethodDispatch_id']))
		{
			$select[]="tmd.TreatmentMethodDispatch_Name as \"TreatmentMethodDispatch\"";
			$left_join[]='left join v_TreatmentMethodDispatch tmd  on tmd.TreatmentMethodDispatch_id = ' . $data['TreatmentMethodDispatch_id'];
			$where .= ' AND TreatmentMethodDispatch_id = ' . $data['TreatmentMethodDispatch_id'];
		}
		else
			$response['params']['TreatmentMethodDispatch'] = ' не указано ';

		if (isset($data['TreatmentMultiplicity_id']))
		{
			$select[]="tm.TreatmentMultiplicity_Name as \"TreatmentMultiplicity\"";
			$left_join[]='left join v_TreatmentMultiplicity tm  on tm.TreatmentMultiplicity_id = ' . $data['TreatmentMultiplicity_id'];
			$where .= ' AND TreatmentMultiplicity_id = ' . $data['TreatmentMultiplicity_id'];
		}
		else
			$response['params']['TreatmentMultiplicity'] = ' не указано ';

		if (isset($data['TreatmentType_id']))
		{
			$select[]="tt.TreatmentType_Name as \"TreatmentType\"";
			$left_join[]='left join v_TreatmentType tt  on tt.TreatmentType_id = ' . $data['TreatmentType_id'];
			$where .= ' AND TreatmentType_id = ' . $data['TreatmentType_id'];
		}
		else
			$response['params']['TreatmentType'] = ' не указано ';

		if (isset($data['TreatmentCat_id']))
		{
			$select[]="tc.TreatmentCat_Name as \"TreatmentCat\"";
			$left_join[]='left join v_TreatmentCat tc  on tc.TreatmentCat_id = ' . $data['TreatmentCat_id'];
			$where .= ' AND TreatmentCat_id = ' . $data['TreatmentCat_id'];
		}
		else
			$response['params']['TreatmentCat'] = ' не указано ';

		if (isset($data['TreatmentRecipientType_id']))
		{
			$select[]="trt.TreatmentRecipientType_Name as \"TreatmentRecipientType\"";
			$left_join[]='left join v_TreatmentRecipientType trt  on trt.TreatmentRecipientType_id = ' . $data['TreatmentRecipientType_id'];
			$where .= ' AND TreatmentRecipientType_id = ' . $data['TreatmentRecipientType_id'];
		}
		else
			$response['params']['TreatmentRecipientType'] = ' не указано ';

		if (isset($data['TreatmentReview_id']))
		{
			$select[]="tr.TreatmentReview_Name as \"TreatmentReview\"";
			$left_join[]='left join v_TreatmentReview tr  on tr.TreatmentReview_id = ' . $data['TreatmentReview_id'];
			$where .= ' AND TreatmentReview_id = ' . $data['TreatmentReview_id'];
		}
		else
			$response['params']['TreatmentReview'] = ' не указано ';

		switch ($data['node']) {
			case 'TRW_number':
				$query = "
					SELECT
						COUNT( * ) as all_item,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentType_id = 1 ".$where.") as number_1,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentType_id = 2 ".$where.") as number_2,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentType_id = 3 ".$where.") as number_3,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentType_id = 4 ".$where.") as number_4
					FROM v_Treatment
					WHERE (1=1) ".$where."
				";
				break;
			case 'TRW_cat':
				$query = "
					SELECT
						tc.TreatmentCat_Name as \"Name\",
						(SELECT COUNT( * ) FROM v_Treatment t  WHERE t.TreatmentCat_id = tc.TreatmentCat_id ".$where." ) as \"Number\"
					FROM v_TreatmentCat tc
				";
				$response = array();
				$res = $this->db->query($query, array());
				if ( is_object($res) )
					$response[] = $res->result('array');
				for ($i=1; $i < 5; $i++)
				{
					$query = "
						SELECT
							tc.TreatmentCat_Name as \"Name\",
							(SELECT COUNT( * ) FROM v_Treatment t  WHERE t.TreatmentType_id = ". $i ." AND TreatmentCat_id = tc.TreatmentCat_id ".$where." ) as \"Number\"
						FROM v_TreatmentCat tc
					";
					$res = $this->db->query($query, array());
					if ( is_object($res) )
						$response[] = $res->result('array');
				}
				return $response;
				break;
			case 'TRW_sender':
				$query = "
					SELECT
						COUNT( * ) as all_item,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 1 ".$where.") as patientes,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 2 ".$where.") as org,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 3 ".$where.") as com,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 4 ".$where.") as glav_vrach,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 5 ".$where.") as zav_otd,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 6 ".$where.") as vrach,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 7 ".$where.") as sister,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentSenderType_id = 8 ".$where.") as other
					FROM v_Treatment
					WHERE (1=1) ".$where."
				";
				//echo $query;
				break;
			case 'TRW_review':
				$query = "
					SELECT
						COUNT( * ) as all_item,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentReview_id = 2 ".$where.") as review,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentReview_id = 1 ".$where.") as notreview
					FROM v_Treatment
					WHERE (1=1) ".$where."
				";
				break;
			case 'TRW_multiplicity':
				$query = "
					SELECT
						COUNT( * ) as all_item,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentMultiplicity_id = 1 ".$where.") as first,
						(SELECT COUNT( * ) FROM v_Treatment  WHERE TreatmentMultiplicity_id = 2 ".$where.") as doubl
					FROM v_Treatment
					WHERE (1=1) ".$where."
				";
				break;
			case 'TRW_subjectLpu':
				if ( !empty($select) AND !empty($left_join) ) {
					$query = "
						SELECT
							". implode(",\n						", $select) ."
						FROM
							v_Treatment
							". implode("\n						", $left_join) ."
                        LIMIT 1
					";
					$res = $this->db->query($query, array());
					if ( is_object($res) )
					{
						$temp_array = $res->result('array');
						$response['params'] = array_merge($response['params'],$temp_array[0]);
						unset($temp_array);
					}
				}
				$query = "
					SELECT
						RTRIM(l.Lpu_Nick) as \"name\",
						(SELECT COUNT( * ) FROM v_Treatment  WHERE Lpu_sid = l.Lpu_id ".$where." ) as \"number\"
					FROM
						v_Lpu l
					WHERE
						(SELECT COUNT( * ) FROM v_Treatment  WHERE Lpu_sid = l.Lpu_id ".$where." ) > 0
				";
				$res = $this->db->query($query, array());
				if ( is_object($res) )
					$response['data'] = $res->result('array');
				return $response;
			case 'TRW_subjectMedpersonal':
				if ( isset($data['Lpu_sid']) )
				{
					$select[]='l.Lpu_Name as "Lpu"';
					$left_join[]='left join v_Lpu l  on l.Lpu_id = ' . $data['Lpu_sid'];
					$query = "
							SELECT
								". implode(",\n						", $select) ."
							FROM
								v_Treatment
								". implode("\n						", $left_join) ."
                            LIMIT 1
					";
					$res = $this->db->query($query, array());
					if ( is_object($res) )
					{
						$temp_array = $res->result('array');
						$response['params'] = array_merge($response['params'],$temp_array[0]);
						unset($temp_array);
					}
					$lpu = 'm.Lpu_id = '. $data['Lpu_sid'] .' AND ';
				}
				else
				{
					$response['params']['Lpu'] = ' По всем ЛПУ ';
					$lpu = '';
				}
				$query = "
					SELECT
						RTRIM(l.Lpu_Nick) as \"lpu\",
						RTRIM(m.Person_Fio) as \"name\",
						(SELECT COUNT( * ) FROM v_Treatment  WHERE MedPersonal_sid = m.MedPersonal_id AND MedPersonal_Lpu_sid = m.Lpu_id ".$where." ) as \"number\"
					FROM
						v_MedPersonal m
						left join v_Lpu l  on l.Lpu_id = m.Lpu_id
					WHERE
						".$lpu." (SELECT COUNT( * ) FROM v_Treatment  WHERE MedPersonal_sid = m.MedPersonal_id AND MedPersonal_Lpu_sid = m.Lpu_id ".$where." ) > 0
					ORDER BY
						l.Lpu_Nick ASC, m.Person_Fio ASC
				";
				$res = $this->db->query($query);
				if ( is_object($res) )
					$response['data'] = $res->result('array');
				return $response;
			default:
				exit('Неправильный параметр node');
		}
		$res = $this->db->query($query, array());
		if ( is_object($res) )
			return array('data' => $res->result('array'));
		else
			return array('success' => false, 'Error_Code' => 667 , 'Error_Msg' => toUTF('Ошибка запроса к базе данных!'));
	}
	
}