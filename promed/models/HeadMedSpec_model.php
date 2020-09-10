<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для общих операций используемых во всех модулях
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2016 Swan Ltd.
 * @author				Alexander Kurakin
 * @link				http://swan.perm.ru/PromedWeb
 * @version				05.2016
 */

class HeadMedSpec_model extends swModel {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *  Функция загрузки списка специалистов
	 */
	function loadHeadMedSpecList($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}

		$params = array();
		$filter = "(1=1)";
		if(!empty($data['Search_Day'])){
			$filter .= " and HMS.HeadMedSpec_begDT <= :Search_Day and (HMS.HeadMedSpec_endDT is null or HMS.HeadMedSpec_endDT > :Search_Day) ";
			$params['Search_Day'] = $data['Search_Day'];
		}
		if(!empty($data['Person_SurName'])){
			$filter .= " and MW.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName'].'%';
		}
		if(!empty($data['Person_FirName'])){
			$filter .= " and MW.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName'].'%';
		}
		if(!empty($data['Person_SecName'])){
			$filter .= " and MW.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName'].'%';
		}
		if(!empty($data['HeadMedSpecType_Name'])){
			$filter .= " and HMST.HeadMedSpecType_Name like :HeadMedSpecType_Name ";
			$params['HeadMedSpecType_Name'] = '%'.$data['HeadMedSpecType_Name'].'%';
		}
		
		$query = "
			Select 
				-- select
				HMS.HeadMedSpec_id,
				convert(varchar,cast(HMS.HeadMedSpec_begDT as datetime),104) as HeadMedSpec_begDT,
				convert(varchar,cast(HMS.HeadMedSpec_endDT as datetime),104) as HeadMedSpec_endDT,
				rtrim(isnull(MW.Person_SurName,'') + ' ' + isnull(MW.Person_FirName,'') + ' ' + isnull(MW.Person_SecName,'')) as Person_Fio,
				convert(varchar,cast(MW.Person_BirthDay as datetime),104) as Person_BirthDay,
				HMST.HeadMedSpecType_Name,
				HMST.HeadMedSpecType_id,
				MW.MedWorker_id,
				MW.Person_id
				-- end select
			from 
				-- from
				v_HeadMedSpec HMS with (nolock)
				left join persis.v_MedWorker MW with (nolock) on MW.MedWorker_id = HMS.MedWorker_id
				left join v_HeadMedSpecType HMST with (nolock) on HMST.HeadMedSpecType_id = HMS.HeadMedSpecType_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by 
				-- order by
				HMS.HeadMedSpec_begDT
				-- end order by
		";
		//echo getDebugSql(getLimitSQLPH($query, 0, 100), $params);exit;
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
	 * Сохранение записи о специалисте
	 */
	function saveHeadMedSpec($data) {

		$cWhere = '';
		$cParams = array();
		$cParams['HeadMedSpecType_id'] = $data['HeadMedSpecType_id'];
		$cParams['HeadMedSpec_begDT'] = $data['HeadMedSpec_begDT'];
		if(isset($data['HeadMedSpec_endDT']) && isset($data['HeadMedSpec_begDT'])) {
			$cWhere = " and (
				(HeadMedSpec_begDT < :HeadMedSpec_begDT and HeadMedSpec_endDT is null)
				or (HeadMedSpec_begDT < :HeadMedSpec_begDT and HeadMedSpec_endDT > :HeadMedSpec_endDT)
				or (HeadMedSpec_begDT >= :HeadMedSpec_begDT and HeadMedSpec_endDT <= :HeadMedSpec_endDT)
				or (HeadMedSpec_begDT > :HeadMedSpec_begDT and HeadMedSpec_begDT <= :HeadMedSpec_endDT)
				or (HeadMedSpec_begDT < :HeadMedSpec_begDT and HeadMedSpec_endDT >= :HeadMedSpec_begDT)
				or (HeadMedSpec_begDT < :HeadMedSpec_endDT and HeadMedSpec_endDT >= :HeadMedSpec_endDT)
				)";
			$cParams['HeadMedSpec_endDT'] = $data['HeadMedSpec_endDT'];
		} else if (isset($data['HeadMedSpec_begDT'])) {
			$cWhere = " and (
				(HeadMedSpec_begDT >= :HeadMedSpec_begDT)
				or (HeadMedSpec_begDT < :HeadMedSpec_begDT and HeadMedSpec_endDT is null)
				or (HeadMedSpec_begDT < :HeadMedSpec_begDT and HeadMedSpec_endDT >= :HeadMedSpec_begDT)
				)";
		}
		if(isset($data['HeadMedSpec_id'])){
			$cParams['HeadMedSpec_id'] = $data['HeadMedSpec_id'];
			$cWhere .= " and HeadMedSpec_id <> :HeadMedSpec_id ";
		}

		$query = "
				select
					convert(varchar,cast(HeadMedSpec_begDT as datetime),104) as HeadMedSpec_begDT,
					convert(varchar,cast(HeadMedSpec_endDT as datetime),104) as HeadMedSpec_endDT
				from
					v_HeadMedSpec with (nolock)
				where
					HeadMedSpecType_id = :HeadMedSpecType_id
					{$cWhere}
					-- and (HeadMedSpec_begDT <= dbo.tzGetDate() and (HeadMedSpec_endDT is null or HeadMedSpec_endDT > dbo.tzGetDate()))
			";
		//echo getDebugSQL($query, array('HeadMedSpecType_id'=>$data['HeadMedSpecType_id']));exit;
		$result = $this->db->query($query, $cParams);
		if ( !is_object($result) ) {
			return false;
		}
		$reslt = $result->result('array');
		if(count($reslt) > 0){
			$period_new = 'с '.date("d.m.Y", strtotime($data['HeadMedSpec_begDT']));
			if(isset($data['HeadMedSpec_endDT'])){
				$period_new .= ' по '.date("d.m.Y", strtotime($data['HeadMedSpec_endDT']));
			}
			if(count($reslt) == 1) {
				return array('Error_Msg'=>'Запись в регистр с периодом '.$period_new.' не может быть добавлена, т.к. этот период имеет пересечение с другими записями регистра по выбранной специальности.');
				//return array('Error_Msg'=>'Выбранная специальность в периоде c '.$reslt[0]['HeadMedSpec_begDT'].(isset($reslt[0]['HeadMedSpec_endDT'])?(' по '.$reslt[0]['HeadMedSpec_endDT']):'').' занята и не может быть выбрана');
			} else {
				return array('Error_Msg'=>'Запись в регистр с периодом '.$period_new.' не может быть добавлена, т.к. этот период имеет пересечения с другими записями регистра по выбранной специальности.');
				/*$periods = '';
				foreach ($reslt as $key) {
					$periods .= ( ' c '.$key['HeadMedSpec_begDT'].(isset($key['HeadMedSpec_endDT'])?(' по '.$key['HeadMedSpec_endDT'].','):',') );
				}
				$periods = rtrim($periods, ",");
				return array('Error_Msg'=>'Выбранная специальность в периодах'.$periods.' занята и не может быть выбрана');*/
			}
				
		}

		$params = array(
			'HeadMedSpec_begDT' => $data['HeadMedSpec_begDT'],
			'HeadMedSpec_endDT' => isset($data['HeadMedSpec_endDT'])?$data['HeadMedSpec_endDT']:null,
			'MedWorker_id' => $data['MedWorker_id'],
			'HeadMedSpecType_id' => $data['HeadMedSpecType_id'],
			'HeadMedSpec_id' => $data['HeadMedSpec_id'],
			'pmUser_id' => $data['pmUser_id'],
			'DocumentStrValues_id' => isset($data['DocumentStrValues_id'])?$data['DocumentStrValues_id']:null,
		);
		if (!isset($data['HeadMedSpec_id'])) {
			$proc = 'p_HeadMedSpec_ins';
			$extra = ',
			@res BIGINT = NULL;';
			$HeadMedSpec_id = '@res';
			$outpt = '@res';
		} else {
			$proc = 'p_HeadMedSpec_upd';
			$extra = ';';
			$HeadMedSpec_id = ':HeadMedSpec_id';
			$outpt = '@HeadMedSpec_id';
			$query = "
				select top 1
					isnull(DocumentStrValues_id,'') as DocumentStrValues_id
				from
					v_HeadMedSpec with (nolock)
				where
					HeadMedSpec_id = :HeadMedSpec_id
			";
			//echo getDebugSQL($query, array('HeadMedSpec_id'=>$data['HeadMedSpec_id']));exit;
			$result = $this->db->query($query, array('HeadMedSpec_id'=>$data['HeadMedSpec_id']));
			if ( !is_object($result) ) {
				return false;
			}
			$hmsData = $result->result('array');
			if($hmsData[0]['DocumentStrValues_id'] === '0') {
				$hmsData[0]['DocumentStrValues_id'] = null;
			}
			$params['DocumentStrValues_id'] = $hmsData[0]['DocumentStrValues_id'];
		}

		$query = "
			declare
				@HeadMedSpec_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@res BIGINT = :HeadMedSpec_id;
			exec dbo.{$proc}
				@HeadMedSpec_id = @res output,
				@HeadMedSpecType_id = :HeadMedSpecType_id,
				@MedWorker_id = :MedWorker_id,
				@HeadMedSpec_begDT = :HeadMedSpec_begDT,
				@HeadMedSpec_endDT = :HeadMedSpec_endDT,
				@DocumentStrValues_id = :DocumentStrValues_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @res as HeadMedSpec_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		//echo getDebugSQL($query, $params);exit;
		//return $this->queryResult($query, $params);
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка записи о специалисте
	 */
	function checkHeadMedSpec($data) {
		
		$query = "
			select count(*) as cnt
			from v_DrugRequest dr with (nolock)
			left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id=dr.MedPersonal_id
			left join persis.v_MedWorker mw with (nolock) on mw.Person_id=mp.Person_id
			where dr.DrugRequestCategory_id = 6 and mw.Person_id=:Person_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, array('Person_id'=>$data['Person_id']));
		if ( !is_object($result) ) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Удаление записи о специалисте
	 */
	function deleteHeadMedSpec($data) {

		$query = "
			declare
				@HeadMedSpec_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@res BIGINT = :HeadMedSpec_id;
			exec dbo.p_HeadMedSpec_del
				@HeadMedSpec_id = @res,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		//echo getDebugSQL($query, array('HeadMedSpec_id'=>$data['HeadMedSpec_id']));exit;
		$res = $this->db->query($query, array('HeadMedSpec_id'=>$data['HeadMedSpec_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка специальностей
	 */
	function loadHeadMedSpecTypeList($data) {
		$params = array();
		$where = '';
		if(isset($data['action']) && $data['action'] == 'add'){
			$where .= " and (HMSa.HeadMedSpec_id is null or HMSa.existInReg = 0)";
		} else if(isset($data['action']) && $data['action'] == 'edit' && isset($data['HeadMedSpec_id'])){
			$params['HeadMedSpec_id'] = $data['HeadMedSpec_id'];
			$where .= " and ((HMSa.HeadMedSpec_id is null or HMSa.HeadMedSpec_id = :HeadMedSpec_id) or HMSa.existInReg = 0)";
		}
		if(isset($data['query'])){
			$params['q'] = '%'.$data['query'].'%';
			$where .= " and HMST.HeadMedSpecType_Name like :q";
		}
		if(isset($data['HeadMedSpecType_Name'])){
			$params['HeadMedSpecType_Name'] = '%'.$data['HeadMedSpecType_Name'].'%';
			$where .= " and HMST.HeadMedSpecType_Name like :HeadMedSpecType_Name";
		}
		$query = "
			select
				HMST.HeadMedSpecType_id,
				HMST.HeadMedSpecType_Name,
				HMSa.HeadMedSpec_id,
				HMSa.existInReg,
				-- post.Post_Name,
				-- post.Post_id
				post.name as Post_Name,
				post.id as Post_id
			from
				v_HeadMedSpecType HMST with (nolock)
				outer apply(select top 1 
						HMS.HeadMedSpec_id,
						case when (HMS.HeadMedSpec_begDT <= dbo.tzGetDate() and (HMS.HeadMedSpec_endDT is null or HMS.HeadMedSpec_endDT > dbo.tzGetDate())) then 1
							else 0 end as existInReg
					from v_HeadMedSpec HMS with (nolock) 
					where HMS.HeadMedSpecType_id = HMST.HeadMedSpecType_id
					order by HMS.HeadMedSpec_begDT desc
				) HMSa
				-- left join v_Post post with (nolock) on post.Post_id = HMST.Post_id
				left join persis.v_Post post with (nolock) on post.id = HMST.Post_id
			where
				(1=1)
				{$where}
			order by
				HMST.HeadMedSpecType_Name
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение записи о специальности
	 */
	function saveHeadMedSpecType($data) {
		$params = array(
			'Post_id' => $data['Post_id'],
			'HeadMedSpecType_id' => $data['HeadMedSpecType_id'],
			'HeadMedSpecType_Name' => $data['HeadMedSpecType_Name'],
			'pmUser_id' => $data['pmUser_id']
		);
		if (!isset($data['HeadMedSpecType_id'])) {
			$proc = 'p_HeadMedSpecType_ins';
			$extra = ',
			@res BIGINT = NULL;';
			$HeadMedSpec_id = '@res';
			$outpt = '@res';
		} else {
			$proc = 'p_HeadMedSpecType_upd';
			$extra = ';';
			$HeadMedSpecType_id = ':HeadMedSpecType_id';
			$outpt = '@HeadMedSpecType_id';
		}

		$query = "
			declare
				@HeadMedSpecType_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@res BIGINT = :HeadMedSpecType_id;
			exec dbo.{$proc}
				@HeadMedSpecType_id = @res output,
				@HeadMedSpecType_Name = :HeadMedSpecType_Name,
				@Post_id = :Post_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @res as HeadMedSpecType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		//echo getDebugSQL($query, $params);exit;
		//return $this->queryResult($query, $params);
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка записи о специальности
	 */
	function checkHeadMedSpecType($data) {
		
		$query = "
			select count(*) as cnt
			from v_HeadMedSpec HMS with (nolock)
			where HMS.HeadMedSpecType_id = :HeadMedSpecType_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, array('HeadMedSpecType_id'=>$data['HeadMedSpecType_id']));
		if ( !is_object($result) ) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Удаление записи о специальности
	 */
	function deleteHeadMedSpecType($data) {

		$query = "
			declare
				@HeadMedSpecType_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@res BIGINT = :HeadMedSpecType_id;
			exec dbo.p_HeadMedSpecType_del
				@HeadMedSpecType_id = @res,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		//echo getDebugSQL($query, array('HeadMedSpecType_id'=>$data['HeadMedSpecType_id']));exit;
		$res = $this->db->query($query, array('HeadMedSpecType_id'=>$data['HeadMedSpecType_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
}