<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Person_model - модель, для работы с людьми
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
class Person_model extends swModel {

	/**
	 * @var bool Загружать библиотеку swMongoCache?
	 */
	protected $loadMongoCacheLib = true;

	public $fromApi = false;
	public $exceptionOnValidation = false;

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
		if ( $this->loadMongoCacheLib ) {
			$this->load->library('swMongoCache');
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	public function checkChildrenDuplicates($data){
		if(!isset($data['Person_pid'])){
			return true;
		}
		$parent = '';
		$children = '';
		$query = "select 
			convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
			PS.Person_SurName,
			PS.Person_FirName,
			PS.Person_SecName,
			case when PS.Sex_id = 1 then 'муж' else 'жен' end as Sex
			from v_PersonDeputy PD with(nolock)
left join v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id
where PD.Person_pid=:Person_pid
and PD.DeputyKind_id = :DeputyKind_id
and PS.Sex_id = :Sex_id
and cast(ps.Person_BirthDay as date)=cast(:Person_BirthDay as date)";
		$res = $this->db->query($query,$data);
		if (is_object($res)) {
			$res = $res->result('array');
			if(count($res) >0){
				foreach($res as $item){
					$children.=$item['Person_SurName'].' '.$item['Person_FirName'].' '.$item['Person_SecName'].'; д/р:'.$item['Person_BirthDay'].'; пол:'.$item['Sex']."<br>";
				}
				$query = "select top 1
			convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
			PS.Person_SurName,
			PS.Person_FirName,
			PS.Person_SecName
			from v_PersonState PS with(nolock) where Person_id = :Person_pid";
				$res = $this->db->query($query,$data);
				$res = $res->result('array');
				$parent = $res[0]['Person_SurName'].' '.$res[0]['Person_FirName'].' '.$res[0]['Person_SecName'].'; д/р:'.$res[0]['Person_BirthDay'];
				
				$text = "В качестве родителя ".$parent." указана у пациентов:<br>
".$children."
Отменить добавление пациента ".$data['Person_SurName']." ".$data['Person_FirName']." ".$data['Person_SecName'].", д/р: ".$data['Person_BirthDay']."?";
				return array('warning'=>$text);
			}
		}
		return true;
	}
	/**
	 *
	 * @param string $data
	 * @return boolean 
	 */
	function getPersonIdentData($data){
		$result=array(array('success'=>true));
		if(isset($data['KLStreet_Name'])&&isset($data['KLStreet_Name'])){
			$query="select  
						isnull(KLRegion.KLArea_id, 0) as KLRegion_id,
						isnull(KLSubRegion.KLArea_id, 0) as KLSubRegion_id,
						case when KLCity.KLAreaLevel_id = 3 then KLCity.KLArea_id end as KLCity_id,
						case when KLCity.KLAreaLevel_id = 4 then KLCity.KLArea_id end as KLTown_id,
						KLRegion.KLCountry_id as KLCountry_id,
						KLStreet.KLStreet_id as KLStreet_id
					from  KLStreet KLStreet with(nolock)

					left join KLArea KLCity with(nolock) on KLCity.KLAreaLevel_id in (3,4) and KLStreet.KLArea_id = KLCity.KLArea_id
					LEFT JOIN KLArea KLSubRegion with (nolock) ON KLSubRegion.KLAreaLevel_id = 2 and KLCity.KLArea_pid = KLSubRegion.KLArea_id 
					LEFT JOIN KLArea KLRegion with (nolock) ON KLRegion.KLAreaLevel_id = 1 and ( (KLSubRegion.KLArea_pid = KLRegion.KLArea_id ) or (KLCity.KLArea_pid = KLRegion.KLArea_id ) )
					--left join PersonSprTerrDop pstd with (nolock) on pstd.KLAdr_Ocatd = KLHouse.KLAdr_Ocatd
					where KLStreet_Name = :KLStreet_Name  and KLStreet.KLAdr_Ocatd like :KLAdr_Ocatd ";
			$data['KLAdr_Ocatd']=$data['KLAdr_Ocatd'].'%';
		
			//echo getDebugSQL($query,$data);
			$res = $this->db->query($query,$data);
			if (is_object($res)) {
				$res = $res->result('array');
				if(count($res) >= 1)
				$result[0]=$res[0];
			}
		}
		if(isset($data['Org_Name'])){
			$query="SELECT od.OrgDep_id as Org_id 
FROM
v_OrgDep od with (nolock)
inner join Org og with (nolock) on og.Org_id = od.Org_id
WHERE od.OrgDep_Name LIKE :Org_Name";
			$res = $this->db->query($query,$data);
			if (is_object($res)) {
				$res = $res->result('array');
				if(count($res) >= 1)
				$result[0]['OrgDep_id']=$res[0]['Org_id'];
			}
		}
		$result[0]['success']=true;
		return $result;
	}
	
	/**
	 *
	 * @param type $data 
	 */
	function getOrgSMO($data){
		$sql = "
			select top 1
				os.OrgSMO_id,
				ost.OMSSprTerr_id
			from v_OrgSMO os with (nolock)
				inner join v_Org o with (nolock) on o.Org_id = os.Org_id
				outer apply (
					select top 1 OMSSprTerr_id
					from v_OMSSprTerr with (nolock)
					where replicate('0', 5 - len(isnull(OMSSprTerr_OKATO, ''))) + OMSSprTerr_OKATO = :Org_OKATO
				) ost
			where o.Org_OGRN = :Org_OGRN
				and left(o.Org_OKATO, 5) = :Org_OKATO
		";
		$params = array('Org_OGRN'=>$data['Org_OGRN'], 'Org_OKATO'=>$data['Org_OKATO']);
		$res = $this->db->query($sql,$params);
		if (is_object($res)) {
			$res = $res->result('array');
			return $res;
		}
	}

	/**
	 * Загрузка СНИЛС
	 */
	function getPersonSnils($data)
	{
		$query = "
			select
				Person_Snils
			from
				v_PersonState (nolock)
			where
				Person_id = :Person_id
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				$resp[0]['Error_Msg'] = '';
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Загрузка данных пациента
	 */
	function getPersonCombo($data)
	{
		$query = "
			select
				PS.Person_id,
				ISNULL(PS.Person_SurName,'') + ISNULL(' ' + PS.Person_FirName,'') + ISNULL(' ' + PS.Person_SecName,'') as Person_Fio
			from
				v_PersonState PS (nolock)
			where
				PS.Person_id = :Person_id
		";

		return $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));
	}
	
	/**
	 * @param $data
	 * @return bool
	 * Сохранение СМО данных
	 */
	function savePersonSmoData($data) {
		// сохраняем соц. статус
		// для прав суперадмина
		$serv_id = $data['Server_id'];
		$pid = $data['ID'];

		$SocStatus_id = $data['ID_STATUS'];
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonSocStatus_ins
			@Server_id = ?,
			@Person_id = ?,
			@SocStatus_id = ?,
			@pmUser_id = ?,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		";
		$res = $this->db->query($sql, array($serv_id, $pid, $SocStatus_id, $data['pmUser_id']));
		$this->ValidateInsertQuery($res);

		// получаем идешник страховой
		$sql = "
			select
				OrgSmo_id
			from
				OrgSmo osm with (nolock)
				inner join Org og with(nolock) on og.Org_id = osm.Org_id and Org_Code = ?
		";
		$res = $this->db->query($sql, array($data['SMO']));
		$sel = $res->result('array');
		if (count($sel) >= 1) {
			$OrgSmo_id = $sel[0]['OrgSmo_id'];
		}
		else
			return false;

		// но сначала получаем данные текущего полиса
		$sql = "
			select
				pls.OMSSprTerr_id,
				pls.PolisType_id,
				pls.OrgSMO_id,
				pls.Polis_Ser,
				pls.PolisFormType_id,
				pls.Polis_Num,
				convert(varchar,cast(pls.Polis_begDate as datetime),112) as Polis_begDate,
				convert(varchar,cast(pls.Polis_endDate as datetime),112) as Polis_endDate
			from
				Polis pls with (nolock)
				inner join v_PersonState ps with (nolock) on ps.Polis_id = pls.Polis_id
			where
				ps.Person_id = ?
		";
		$res = $this->db->query($sql, array($data['ID']));
		$sel = $res->result('array');
		if (count($sel) == 0) {
			return false;
		}

		// сохраняем полис
		$OmsSprTerr_id = (empty($sel[0]['OMSSprTerr_id']) ? NULL : $sel[0]['OMSSprTerr_id']);
		$PolisType_id = (empty($sel[0]['PolisType_id']) ? NULL : $sel[0]['PolisType_id']);
		$Polis_Ser = (empty($sel[0]['Polis_Ser']) ? '' : $sel[0]['Polis_Ser']);
		$PolisFormType_id = (empty($sel[0]['PolisFormType_id']) ? null : $sel[0]['PolisFormType_id']);
		$Polis_Num = (empty($data['POL_NUM']) ? '' : $data['POL_NUM']);
		$Polis_begDate = empty($sel[0]['Polis_begDate']) ? NULL : $sel[0]['Polis_begDate'];
		$Polis_endDate = empty($sel[0]['Polis_endDate']) ? NULL : $sel[0]['Polis_endDate'];
		if (isset($OmsSprTerr_id) && (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_PersonPolis_ins
				@Server_id = ?,
				@Person_id = ?,
				@PersonPolis_insDT = ?,
				@OmsSprTerr_id = ?,
				@PolisType_id = ?,
				@OrgSmo_id = ?,
				@Polis_Ser = ?,
				@PolisFormType_id =?,
				@PolisFormType_id = ?
				@Polis_Num = ?,
				@Polis_begDate = ?,
				@Polis_endDate = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";
			$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
	}

	/**
	 * checkPeriodicBegDate
	 *
	 * Выполняется проверка:
	 * Если минимальная дата по всем периодикам данного атрибута
	 * не совпадает с датой по данному атрибуту из таблицы PersonEvnClass_begDT,
	 * то добавление/изменение/удаление отменять и выводить сообщение 
	 * «Дата начала самой ранней периодики должна быть равна < PersonEvnClass_begDT по данному атрибуту>».
	 * Если PersonEvnClass_begDT=Null, то проверку не выполнять.
	 * $param integer $Person_id - идентификатор человека
	 * $param integer $PersonEvnClass_id - идентификатор класса периодики
	 * $param string $PersonEvn_begDate - дата периодики со временем.
	 * @return boolean
	 */
	function checkPeriodicBegDate($Person_id, $PersonEvnClass_id, $PersonEvnClass_begDate) {
		$sql = "
			select
				count(pe.PersonEvn_id) as cnt
			from
				v_PersonEvn pe with (nolock)
				inner join PersonEvnClass pec with (nolock) on
					and pe.Person_id = ?
					and pe.PersonEvnClass_id = ?
					and pe.PersonEvnClass_id = pec.PersonEvnClass
					and ( pe.PersonEvn_insDT = pec.PersonEvnClass_begDT || pec.PersonEvnClass_begDT is null || ( pec.PersonEvnClass_begDT = ? )
			
		";
		$res = $this->db->query($sql, array($Person_id, $PersonEvnClass_id, $PersonEvnClass_begDate));
		if (is_object($res)) {
			$sel = $res->result('array');
			if ($sel[0]['cnt']) {
				foreach ($res->result('array') as $rows) {
					if (!empty($rows['ErrMsg'])) {
						//$this->db->trans_rollback();
						$err = addslashes($rows['ErrMsg']);
						DieWithError('Произошла ошибка при сохранении данных формы. <p style=\"color: red\">' . $err . '</p>');
					}
				}
			}
		} else {
			//$this->db->trans_rollback();
			DieWithError('Непонятная ошибка при сохранении данных формы.');
		}
	}


	/**
	 * Функция замены символов для поиска
	 * @param $value
	 * @param array $symbols
	 * @return mixed|string
	 */
	function prepareSearchSymbol($value, $symbols = array()) {

		if(empty($symbols)) {
			$symbols = array(
				'ё' => "е", "ә" => "а", "і" => "э", "ң" => "н", "ғ" => "г", "ү" => "у", "ұ" => "у", "қ" => "к", "ө" => "о", "һ" => "х",
				// ПОИСК ПО 2 АПОСТРОФАМ ` '
				'(?!\[)\'(?!\])' => '[`\']','(?!\[)\`(?!\'\])' => '[`\']'
			);
		}

		$value = trim($value);
		foreach($symbols as $symbol => $replace) {
			$value = preg_replace('/'.$symbol.'/iu', $replace, $value);
		}

		return $value;
	}

	/**
	 * @param $data
	 * @return bool
	 * Метод редактирует или добавляет данные периодики человека, относительно определенного события добавления периодики.
	 *  $data['ObjectName'] - наименование сохраняемого объекта
	 *  $data['ObjectField'] - поля, сохраняемого объекта
	 *  $data['ObjectData'] - сохраняемое значение
	 *  $data['Server_id'] - идентификатор сервера
	 *  $data['Person_id'] - идентификатор персона
	 *  $data['PersonEvn_id'] - идентификатор события вставки периодики
	 *  $data['pmUser_id'] - идентификатор пользователя
	 *  $data['PersonEvnClass_id'] - идентификатор события вставки периодики
	 */
	function savePersonEvnSimpleAttr($data) {
		if (!(isset($data['AllowEmpty']) && $data['AllowEmpty'] === true) && (!isset($data['ObjectData']) || $data['ObjectData'] == '' )) {
			return false;
		}

		$sel = array();
		$insDT = null; //'2000-01-01 00:00:00.000';
		if (isset($data['insDT'])) {
			$insDT = $data['insDT'];
		}
		if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
			$sel[0]['Server_id'] = $data['Server_id'];
			$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
		} else {
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				declare @serverId bigint
							
				set @serverId =  :Server_id
				
				select
					top 1 PersonEvn_id,
					Server_id
				from
					v_PersonEvn with (nolock)
				where
					PersonEvnClass_id = " . $data['PersonEvnClass_id'] . " and
					PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = @serverId) and
					Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
			";
			//echo getDebugSQL($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));exit;
			$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
			$sel = $res->result('array');
		}
		// если не было, то добавляем атрибут на дату
		if (count($sel) == 0||(isset($data['insPeriodic'])&&$data['insPeriodic']==true)) {
			if ($insDT == null) {
				$insDT = '2000-01-01 00:00:00.000';
			}
			$serv_id = $data['Server_id'];

			if (isset($data['insPeriodic']) && $data['insPeriodic'] == true) {
				// проверяем наличие периодики
				$resp_check = $this->queryResult("
					select top 1 {$data['ObjectName']}_id from v_{$data['ObjectName']} (nolock) where {$data['ObjectField']} = :ObjectData and {$data['ObjectName']}_insDT = :insDT
				", array(
					'ObjectData' => $data['ObjectData'],
					'insDT' => $insDT
				));

				if (!empty($resp_check[0]["{$data['ObjectName']}_id"])) {
					// дубль периодики нам не нужен.
					return;
				}
			}

			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_" . $data['ObjectName'] . "_ins
				@Server_id = ?,
				@Person_id = ?,
				@" . $data['ObjectName'] . "_insDT = ?,
				@" . $data['ObjectField'] . " = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";
			//echo getDebugSQL($sql, array($data['Server_id'], $data['Person_id'], $insDT, $data['ObjectData'], $data['pmUser_id']));exit;
			$res = $this->db->query($sql, array($data['Server_id'], $data['Person_id'], $insDT, $data['ObjectData'], $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
		// иначе редактируем этот атрибут
		else {
			$serv_id = $sel[0]['Server_id'];
			$peid = $sel[0]['PersonEvn_id'];
			$AdditFields = "";
			$AdditValues = array();
			if (isset($data['AdditFields'])) {
				$arr = array_keys($data['AdditFields']);
				$AdditFields = join(' = ?, ', $arr);
				$AdditFields = $arr[count($arr) - 1] . ' = ?, ';
				$AdditValues = array_values($data['AdditFields']);
			}
			if ($peid <= 0) {
				return false;
			}
			if ($insDT != null) {
				$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_" . $data['ObjectName'] . "_upd
				@Server_id = ?,
				@Person_id = ?,
				@" . $data['ObjectName'] . "_insDT = ?,
				@" . $data['ObjectName'] . "_id = ?,
				@" . $data['ObjectField'] . " = ?,
				@pmUser_id = ?,
				" . $AdditFields . "
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
				
				select @ErrMsg as ErrMsg
			";
				//echo getDebugSQL($sql, array_merge(array($serv_id, $data['Person_id'], $insDT, $peid, $data['ObjectData'], $data['pmUser_id']), $AdditValues));exit;
				$res = $this->db->query($sql, array_merge(array($serv_id, $data['Person_id'], $insDT, $peid, $data['ObjectData'], $data['pmUser_id']), $AdditValues));
				//$this->editPersonEvnDate(array('PersonEvn_id' => $peid, 'Date' => $insDT, 'Server_id' => $serv_id, 'pmUser_id' => $data['pmUser_id']));
			} else {
				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)

					exec p_" . $data['ObjectName'] . "_upd
					@Server_id = ?,
					@Person_id = ?,
					@" . $data['ObjectName'] . "_id = ?,
					@" . $data['ObjectField'] . " = ?,

					@pmUser_id = ?,
					" . $AdditFields . "
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

					select @ErrMsg as ErrMsg
				";
				//echo getDebugSQL($sql, array_merge(array($serv_id, $data['Person_id'], $peid, $data['ObjectData'], $data['pmUser_id']), $AdditValues));exit;
				$res = $this->db->query($sql, array_merge(array($serv_id, $data['Person_id'], $peid, $data['ObjectData'], $data['pmUser_id']), $AdditValues));
			}
			$this->ValidateInsertQuery($res);
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getPersonEvalEditWindow($data) {
		if ($data["EvalType"]) {
			$type = $data["EvalType"];
		}


		$query = "
			SELECT
				Person" . $type . "_id as PersonEval_id,
				Person_id,
				Evn_id,
				Person" . $type . "_" . $type . ",
				Person" . $type . "_IsAbnorm,
				" . $type . "AbnormType_id,
				" . $type . "MeasureType_id,
				convert(varchar(10),Person" . $type . "_setDT, 104) as PersonEval_setDT,
				Okei_id
			FROM v_Person" . $type . " with(nolock) 
			WHERE Person" . $type . "_id = :PersonEval_id		
		";

		$params = array("PersonEval_id" => $data["PersonEval_id"]);
		//echo getDebugSQL($query,$params);exit();
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function deletePersonEval($data)
	{
		if ($data["EvalType"])
			$type = $data["EvalType"];
		else
			return false;

		$sql = "
				declare @Error_Code bigint
				declare @Error_Message varchar(400)
			
				exec p_" . $type . "_del
				@" . $type . "_id = :PersonEval_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output

				select @Error_Message as ErrMsg
				";

		$params = array("PersonEval_id" => $data["PersonEval_id"]);

		$result = $this->db->query($sql, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function savePersonEvalEditWindow($data) {
		if ($data["EvalType"]) {
			$type = $data["EvalType"];
		}
		$p_type = 'p_Person' . $type . '_ins';
		if (!empty($data["PersonEval_id"])) {
			$p_type = 'p_Person' . $type . '_upd';
		} else if($data["EvalMeasureType_id"]==1){
			$query = 'select Person' . $type . '_id as PersonEval_id from v_Person' . $type . ' with(nolock) where ' . $type . 'MeasureType_id=1 and Person_id=:Person_id';
			//echo getDebugSQL($query, array('Person_id'=>$data['Person_id']));exit();
			$result = $this->db->query($query, array('Person_id'=>$data['Person_id']));
			if ( is_object($result) ) {
				$res = $result->result('array');
				if(count($res)>0){
					$p_type = "p_Person" . $type . "_upd";
					$data['PersonEval_id'] = $res[0]['PersonEval_id'];
				}
			}
		}
		$sql = "
				declare @Error_Code int
				declare @Error_Message varchar(400)
			
				exec " . $p_type . "
				@Person" . $type . "_id = :PersonEval_id,
				@Person_id = :Person_id,
				@Person" . $type . "_setDT = :PersonEval_setDT,
				@Person" . $type . "_" . $type . " = :PersonEval_Value,
				@Person" . $type . "_IsAbnorm = :PersonEval_IsAbnorm,
				@" . $type . "AbnormType_id = :EvalAbnormType_id,
				@" . $type . "MeasureType_id = :EvalMeasureType_id,
				@Okei_id = :Okei_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
select @Error_Message as ErrMsg
";
		$params = array("PersonEval_id" => $data["PersonEval_id"],
			"Person_id" => $data["Person_id"],
			"PersonEval_setDT" => $data["PersonEval_setDT"],
			"PersonEval_Value" => $data["PersonEval_Value"],
			"PersonEval_IsAbnorm" => $data["PersonEval_IsAbnorm"],
			"EvalAbnormType_id" => $data["EvalAbnormType_id"],
			"EvalMeasureType_id" => $data["EvalMeasureType_id"],
			"Okei_id" => $data["Okei_id"],
			"pmUser_id" => $data["pmUser_id"],
			"Server_id" => $data["Server_id"],
			"Evn_id" => !empty($data["Evn_id"]) ? $data["Evn_id"] : null,
		);
		//echo getDebugSQL($sql,$params);exit();
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadPersonEval($data) {

		$query = "with PersonEval(
	PersonEvalClass,
	PersonEvalClass_id,		
	PersonEval_id,
	EvalType,
	Person_id,
	PersonEval_setDT,
	PersonEval_value,
	PersonEval_isAbnorm,
	EvalAbnormType,
	EvalMeasureType,
	EvalMeasureTypeCode
	) as (
	select
		'PersonHeight_id' as PersonEvalClass,
		PH.PersonHeight_id as PersonEvalClass_id,
		'PersonHeight' + cast(PH.PersonHeight_id as varchar(40)) as PersonEval_id,
		'Рост(см)' as EvalType,
		PH.Person_id,
		convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonEval_setDT,
		cast(PH.PersonHeight_Height as float) as PersonEval_value,
		ISNULL(IsAbnorm.YesNo_Name, '') as PersonEval_isAbnorm,
		ISNULL(HAT.HeightAbnormType_Name, '') as EvalAbnormType,
		ISNULL(HMT.HeightMeasureType_Name, '') as EvalMeasureType,
		ISNULL(HMT.HeightMeasureType_Code, '') as EvalMeasureTypeCode
	from v_PersonHeight PH with (nolock)
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT with (nolock) on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
	where Person_id = :Person_id
	union all
	select
		'PersonWeight_id' as PersonEvalClass,
		PW.PersonWeight_id as PersonEvalClass_id,
		'PersonWeight'+cast(PW.PersonWeight_id as varchar(40)) as PersonEval_id,
		'Вес(кг)' as EvalType,
		PW.Person_id,
		convert(varchar(10), PW.PersonWeight_setDT, 104) as PersonEval_setDT,
		case when pw.Okei_id = 36 then
					cast(pw.PersonWeight_Weight as float) / 1000
				else
					pw.PersonWeight_Weight
				end as PersonEval_value,
		ISNULL(IsAbnorm.YesNo_Name, '') as PersonEval_isAbnorm,
		ISNULL(WAT.WeightAbnormType_Name, '') as EvalAbnormType,
		ISNULL(WMT.WeightMeasureType_Name, '') as EvalMeasureType,
		ISNULL(WMT.WeightMeasureType_Code, '') as EvalMeasureTypeCode
	from v_PersonWeight PW  with (nolock)
		inner join WeightMeasureType WMT with (nolock) on WMT.WeightMeasureType_id = PW.WeightMeasureType_id
		left join YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PW.PersonWeight_IsAbnorm
		left join WeightAbnormType WAT with (nolock) on WAT.WeightAbnormType_id = PW.WeightAbnormType_id
	where Person_id = :Person_id
	union all
	select
		'HeadCircumference_id' as PersonEvalClass,
		HC.HeadCircumference_id as PersonEvalClass_id,
		'HeadCircumference' + cast(HC.HeadCircumference_id as varchar(40)) as PersonEval_id,
		'Окружность головы (см)' as EvalType,
		PC.Person_id,
		convert(varchar(10), HC.HeadCircumference_insDT, 104) as PersonEval_setDT,
		cast(HC.HeadCircumference_Head as float) as PersonEval_value,
		'' as PersonEval_isAbnorm,
		'' as EvalAbnormType,
		ISNULL(HMT.HeightMeasureType_Name, '') as EvalMeasureType,
		ISNULL(HMT.HeightMeasureType_Code, '') as EvalMeasureTypeCode
	from v_HeadCircumference HC with (nolock)
		inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
		left join v_PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
	where PC.Person_id = :Person_id
	union all
	select
		'ChestCircumference_id' as PersonEvalClass,
		CC.ChestCircumference_id as PersonEvalClass_id,
		'ChestCircumference' + cast(CC.ChestCircumference_id as varchar(40)) as PersonEval_id,
		'Окружность груди (см)' as EvalType,
		PC.Person_id,
		convert(varchar(10), CC.ChestCircumference_insDT, 104) as PersonEval_setDT,
		cast(CC.ChestCircumference_Chest as float) as PersonEval_value,
		'' as PersonEval_isAbnorm,
		'' as EvalAbnormType,
		ISNULL(HMT.HeightMeasureType_Name, '') as EvalMeasureType,
		ISNULL(HMT.HeightMeasureType_Code, '') as EvalMeasureTypeCode
	from v_ChestCircumference CC with (nolock)
		inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = CC.HeightMeasureType_id
		left join v_PersonChild PC with (nolock) on PC.PersonChild_id = CC.PersonChild_id
	where PC.Person_id = :Person_id
	)
	
	select * from PersonEval";

		$params = array("Person_id" => $data["Person_id"]);
		//echo getDebugSQL($query,$params);exit();
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Метод редактирует или добавляет данные периодики человека, если последний атрибут с датой меньше заданной не совпадает с новым.
	 *  $data['ObjectName'] - наименование сохраняемого объекта
	 *  $data['ObjectField'] - поля, сохраняемого объекта
	 *  $data['ObjectData'] - сохраняемое значение
	 *  $data['Server_id'] - идентификатор сервера
	 *  $data['Person_id'] - идентификатор персона
	 *  $data['pmUser_id'] - идентификатор пользователя
	 *  $data['PersonEvnClass_id'] - идентификатор события вставки периодики
	 *  $data['insDT'] - заданная дата
	 */
	function savePersonEvnSimpleAttrNew($data) {
		if (!(isset($data['AllowEmpty']) && $data['AllowEmpty'] === true) && (!isset($data['ObjectData']) || $data['ObjectData'] == '' ))
			return false;
		// получаем последний атрибут, который был до этого Evn
		$sql = "
				select
					top 1 PersonEvn_id,
					Server_id
				from
					v_PersonEvn with (nolock)
				where
					PersonEvnClass_id = " . $data['PersonEvnClass_id'] . " and
					Person_id = :Person_id and
					PersonEvn_insDT <= :insDT
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc					
			";
		$res = $this->db->query($sql, array('Person_id' => $data['Person_id'], 'insDT' => $data['insDT']));
		$sel = $res->result('array');

		$add = false;
		// если отсутсвует или не совпадает со значением то добавляем новое
		if (count($sel) == 0) {
			$add = true;
		} else {
			$sel = $sel[0];
			$sqlob = "
				select
					top 1 {$data['ObjectField']}
				from
					{$data['ObjectName']} with (nolock)
				where
					{$data['ObjectName']}_id = {$sel['PersonEvn_id']}
			";
			$resob = $this->db->query($sqlob);
			$selob = $resob->result('array');
			if (count($selob) > 0) {
				$selob = $selob[0];
				if ($selob[$data['ObjectField']] != $data['ObjectData']) {
					$add = true;
				}
			} else {
				$add = true;
			}
		}

		if ($add) {
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_" . $data['ObjectName'] . "_ins
				@Server_id = ?,
				@Person_id = ?,
				@" . $data['ObjectName'] . "_insDT = ?,
				@" . $data['ObjectField'] . " = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";
			$res = $this->db->query($sql, array($data['Server_id'], $data['Person_id'], $data['insDT'], $data['ObjectData'], $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Удаление атрибута
	 */
	function deletePersonEvnAttribute($data) {
		if(!isset($_SESSION))
			session_start();
		unset($_SESSION['person']);
		unset($_SESSION['person_short']);
		session_write_close();
		
		$this->load->library('textlog', array('file'=>'xp_PersonRemovePersonEvn.log'));
		$this->textlog->add('Дата и время '.date('Y-m-d H:i:s').' ID пользователя '.$data['pmUser_id'].' // ');
		if ($data['Person_id'] > 0 && $data['PersonEvn_id'] > 0) {
			
			$params = array(
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'PersonEvnClass_id' => $data['PersonEvnClass_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			
			// Для периодик ФИО, ДР, пол, соцстатус проверим что она не одна 
			if (in_array($data['PersonEvnClass_id'], array(1,2,3,4,5,7))) {
				$sql = "
					select top 1 Server_id, PersonEvn_id 
					from v_Person_all with (nolock)
					where Person_id = :Person_id and (Server_id <> :Server_id or PersonEvn_id <> :PersonEvn_id) and PersonEvnClass_id = :PersonEvnClass_id
					order by PersonEvn_insDT desc, PersonEvn_TimeStamp desc
				";
				$res = $this->db->query($sql, $params);
				if ( is_object($res) ) {
					$response = $res->result('array');
					if ( is_array($response) && count($response) == 0 ) {
						$response[0]['Error_Msg'] = 'Удаление периодики не возможно, так как тип удаляемой периодики обязательный и периодика этого типа последняя у выбранного человека.';
						return $response;
					}
				}
				else {
					return false;
				}
			}
			
			$sql = "
				declare @Err_Msg varchar(4000), @Err_Code int;

				begin try
					exec xp_PersonRemovePersonEvn
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Err_Code output,
						@Error_Message = @Err_Msg output;
				end try
				
				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
			";
			$this->textlog->add('Запрос '.getDebugSQL($sql, $params));
			$res = $this->db->query($sql, $params);
		}
		if ( is_object($res) ) {
			$response = $res->result('array');

			if ( is_array($response) && count($response) > 0 && (isset($response[0]['Error_Code']) && $response[0]['Error_Code'] == 547 )) {
				$response[0]['Error_Msg'] = 'Удаляемая периодика используется в реестрах, удаление невозможно';
			}

			return $response;
		}
		else {
			return false;
		}

		/*if ($_SESSION['region']['nick'] == 'ufa'&&$data['PersonEvnClass_id']==8) {
			$query = "
				select top 1
					ps.Person_id,
					ps.Server_pid
				from
					v_PersonState ps (nolock)
				where
					ps.Person_id = :Person_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Person_id'])) {
					if (isSuperAdmin() && $resp[0]['Server_pid'] == 0) {
						$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Person_server
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
						$res = $this->db->query($sql, array("Person_id" => $resp[0]['Person_id'], "Server_id" => $_SESSION['server_id'], "pmUser_id" => $data['pmUser_id']));
					}
				}
			}
		}*/
		//return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * Редактирование атрибута
	 */
	function editPersonEvnAttributeNew($data) {
		if (empty($data['PersonIdentState_id']) || !in_array(intval($data['PersonIdentState_id']), array(1, 2, 3))) {
			$data['PersonIdentState_id'] = 0;
		}
		$person_is_identified=false;
		if ($data['PersonIdentState_id'] == 1) {
			$person_is_identified = true;
			$sql = "
				declare
					@getdate datetime = getdate(),
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_Person_ident
					@Person_id = :Person_id,
					@Person_identDT = @getdate,
					@PersonIdentState_id = :PersonIdentState_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$res = $this->db->query($sql, array(
				'Person_id' => $data['Person_id'],
				'PersonIdentState_id' => $data['PersonIdentState_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			$query = "select top 1 Person_BDZCode from v_PersonInfo where Person_id = :Person_id";

			$result = $this->getFirstResultFromQuery($query, array('Person_id' => $data['Person_id']));
			if (empty($result) && isset($data['rz']) && $data['rz'] != '') {
				$query = "update personinfo with (rowlock) set Person_BDZCode = :rz where Person_id = :Person_id";
				$this->db->query($query, array('rz' => $data['rz'], 'Person_id' => $data['Person_id']));
			}
		}
		if (!empty($data['Person_IsInErz']) && $data['Person_IsInErz'] == 2) {
			$person_is_identified = true;
		}

		$oldMainFields = array();

		if (getRegionNick() == 'penza') {
			$oldMainFields = $this->getMainFields($data);
			if (!is_array($oldMainFields)) {
				return $this->createError('','Ошибка при получении актуальных атрибутов человека');
			}
		}
		
		// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан, 
		// чтобы лишний раз в сессию не писать, экономим на спичках
		if(!isset($_SESSION))
			session_start();
		if (isset($data['session']) && isset($data['session']['person']) && isset($data['session']['person']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person']['Person_id'])
			unset($_SESSION['person']);

		if (isset($data['session']) && isset($data['session']['person_short']) && isset($data['session']['person_short']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person_short']['Person_id'])
			unset($_SESSION['person_short']);
		session_write_close();

		$is_superadmin = isSuperadmin();
		$server_id = $data['Server_id'];
		$pid = $data['Person_id'];
		$peid = $data['PersonEvn_id'];
		$sel = array();

		$BDZaffected = false;
		$evn_types = explode('|', $data['EvnType']);
		$count_evn_types = count($evn_types);
		for ($i = 0; $i < $count_evn_types; $i++) {

			switch ($evn_types[$i]) {
				case 'Deputy':
					if (isset($data['DeputyKind_id']) && isset($data['DeputyPerson_id'])) {
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @PD_id bigint
							
							set @PD_id = (select top 1 PersonDeputy_id from PersonDeputy with (nolock) where Person_id = ?)
							
							exec p_PersonDeputy_del
							@PersonDeputy_id = @PD_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$this->db->query($sql, array($pid));

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonDeputy_ins
							@Server_id = ?,
							@Person_id = ?,
							@Person_pid = ?,
							@DeputyKind_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						//echo getDebugSQL($sql, array($server_id, $pid, $data['DeputyPerson_id'], $data['DeputyKind_id'], $data['pmUser_id']));
						$res = $this->db->query($sql, array($server_id, $pid, $data['DeputyPerson_id'], $data['DeputyKind_id'], $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					} else {
						// если ни один не задан, то удаляем
						if (!isset($data['DeputyKind_id']) && !isset($data['DeputyPerson_id'])) {
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
								declare @PD_id bigint
							
								set @PD_id = (select top 1 PersonDeputy_id from PersonDeputy with (nolock) where Person_id = ?)

								exec p_PersonDeputy_del
								@PersonDeputy_id = @PD_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output

								select @ErrMsg as ErrMsg
							";
							$this->db->query($sql, array($pid));
						}
					}

					break;

				case 'Polis':
					
					if (!isset($data['OMSSprTerr_id']) || empty($data['OMSSprTerr_id'])){
						continue 2;
					}
					$OmsSprTerr_id = (empty($data['OMSSprTerr_id']) ? NULL : $data['OMSSprTerr_id']);
					$PolisType_id = (empty($data['PolisType_id']) ? NULL : $data['PolisType_id']);
					$OrgSmo_id = (empty($data['OrgSMO_id']) ? NULL : $data['OrgSMO_id']);
					$PolisFormType_id = (empty($data['PolisFormType_id']) ? null : $data['PolisFormType_id']);
					$Polis_Ser = (empty($data['Polis_Ser']) ? '' : $data['Polis_Ser']);
					$PolisFormType_id = (empty($data['PolisFormType_id']) ? null : $data['PolisFormType_id']);
					$Polis_Num = (empty($data['Polis_Num']) ? '' : $data['Polis_Num']);
					$Polis_begDate = empty($data['Polis_begDate']) ? NULL : $data['Polis_begDate'];
					$Polis_endDate = empty($data['Polis_endDate']) ? NULL : $data['Polis_endDate'];
					$Polis_Guid = empty($data['Polis_Guid'])? NULL : $data['Polis_Guid'];
					// получаем последний атрибут, который был до этого Evn
					$Federal_Num = empty($data['Federal_Num']) ? NULL : $data['Federal_Num'];
					$Evn_setDT = empty($data['Evn_setDT']) ? NULL : $data['Evn_setDT'];

					if (!empty($Federal_Num) && strlen($Federal_Num) < 16) {
						return array(array('success' => false, 'Error_Msg' => 'Единый номер полиса должен иметь длину в 16 цифр'));
					}

					if ($PolisType_id == 4) {
						$Polis_Num = $Federal_Num;
						$data['Polis_Num'] = $Federal_Num;
					}

					if ( (empty($data['apiAction']) || $data['apiAction'] != 'create') && (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) ) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						$flt='';
						// получаем последний атрибут, который был до этого Evn
						if($Evn_setDT!=null){
							$flt="Person.PersonPolis_insDT <= :Evn_setDT and";
						}else{
							$flt="Person.PersonPolis_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and";
						}
						/*if ($data['session']['region']['nick'] == 'buryatiya') { // странное условие, из-за него на Бурятии не обновляется полис вообще, если периодика старее существующего полиса.
							$flt="";
						}*/
						$sql = "
								select
								top 1 Person.PersonPolis_id as PersonEvn_id,
								Person.Server_id,
								Person.Polis_id
							from
								v_PersonPolis Person with (nolock)
								outer apply (select top 1 Person_edNum from v_Person_all with (nolock) where Person.Polis_id = Polis_id) edNum
							where
								".$flt."
								Person.Person_id = :Person_id and
								:Polis_Num = case when Person.PolisType_id=4 then edNum.Person_EdNum else Person.Polis_Num end and
								(isnull(Person.Polis_Ser,'')= isnull(:Polis_Ser,'')) and 
                                Person.PolisType_id = :PolisType_id and
                                Person.OrgSMO_id = :OrgSMO_id 
							order by
								Person.PersonPolis_insDT desc,
								Person.PersonPolis_TimeStamp desc
						";

						/* echo getDebugSQL($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id'], 'Polis_Num' => $data['Polis_Num'], 'Polis_Ser' => $data['Polis_Ser'], 'PolisType_id' => $data['PolisType_id'], 'OrgSMO_id' => $data['OrgSMO_id']));
						  exit(); */
						$res = $this->db->query($sql, array('Evn_setDT' => $Evn_setDT,'PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id'], 'Polis_Num' => $data['Polis_Num'], 'Polis_Ser' => $data['Polis_Ser'], 'PolisType_id' => $data['PolisType_id'], 'OrgSMO_id' => $data['OrgSMO_id']));
						$sel = $res->result('array');
					}
					$check = $this->checkPolisIntersection($data, true);
					if(isset($check['PersonEvn_id']) && isset($check['Server_id'])){
						$sel[0]['Server_id'] = $check['Server_id'];
						$sel[0]['PersonEvn_id'] = $check['PersonEvn_id'];
					}
					if (count($sel) > 0 && isset($check['deletedPersonEvnList']) && in_array($sel[0]['PersonEvn_id'], $check['deletedPersonEvnList'])) {
						$sel = array();
					}

					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0 && (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {


						if ($check === false) {
							return array(array('success' => false, 'Error_Msg' => 'Периоды полисов не могут пересекаться.'));
						}
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Polis_id bigint

							exec p_PersonPolis_ins
							@Polis_id = @Polis_id output,
							@Server_id = ?,
							@Person_id = ?,
							@PersonPolis_insDT = ?,
							@OmsSprTerr_id = ?,
							@PolisType_id = ?,
							@OrgSmo_id = ?,
							@Polis_Ser = ?,
							@PolisFormType_id =?,
							@Polis_Guid=?,
							@Polis_Num = ?,
							@Polis_begDate = ?,
							@Polis_endDate = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @Polis_id as Polis_id, @ErrMsg as ErrMsg
						";
						if($person_is_identified){
							$serv_id=0;
						}
						//echo getDebugSQL($sql, array($serv_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id,$Polis_Guid,$Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id'])); die();
						$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id,$Polis_Guid,$Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
						$resp = $res->result('array');

						$this->_saveResponse['Polis_id'] = $resp[0]['Polis_id'];
						$flt='';
						if ($person_is_identified) {
							$fsql = "
								select
								top 1 PE.PersonPolisEdNum_id
								from v_PersonPolisEdNum PE with (nolock)
								where 
								PE.Person_id =:Person_id
								and pe.PersonPolisEdNum_Ednum =:edNum
								order by
								PersonPolisEdNum_insDT desc,
								PersonPolisEdNum_TimeStamp desc
							";
						} else {
							if ($Evn_setDT!=null) {
								$flt = ":Evn_setDT";
							} else {
								$flt = "(select PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :serverId)";
							}
							$fsql = "
								select
								top 1 PE.PersonPolisEdNum_id
								from v_PersonPolisEdNum PE with (nolock)
								where 
								PE.PersonPolisEdNum_insDT <= ".$flt."
								and PE.Person_id =:Person_id
								and pe.PersonPolisEdNum_Ednum =:edNum
								and PE.PersonPolisEdNum_insDT <= :begdate
								order by
								PersonPolisEdNum_insDT desc,
								PersonPolisEdNum_TimeStamp desc
							";
						}
						$fres = $this->db->query($fsql, array('Evn_setDT'=>$Evn_setDT,'PersonEvn_id' => $data['PersonEvn_id'], 'Person_id' => $data['Person_id'], 'edNum' => $Federal_Num, 'begdate' => $Polis_begDate,'serverId'=>$serv_id));
						$fsel = $fres->result('array');


						// если не было, то добавляем атрибут на дату
						if (count($fsel) == 0) {
							$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $data['Person_id'], 'begdate' => $Polis_begDate));
							if ($checkEdNum === false) {
								$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
								return array(array('success' => false, 'Error_Msg' => "На дату {$date} уже создан Ед. номер."));
							}
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)

								exec p_PersonPolisEdNum_ins
								@Server_id = ?,
								@Person_id = ?,
								@PersonPolisEdNum_insDT = ?,
								@PersonPolisEdNum_EdNum = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output

								select @ErrMsg as ErrMsg
							";
							if ($Federal_Num != NULL) {
								//echo getDebugSQL($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));
								$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));
								$this->ValidateInsertQuery($res);
							}
						}
					}
					// иначе редактируем этот атрибут
					elseif (count($sel)) {

						$sql = "
								select top 1 1,
								convert(varchar(10),Person.Polis_endDate, 104) as Polis_endDate
								 
							from
								v_PersonPolis Person with (nolock)
								outer apply (select top 1 Person_edNum from v_Person_all with (nolock) where Person.Polis_id = Polis_id) edNum
							where
								Person.OMSSprTerr_id = :OMSSprTerr_id and
								Person.PersonPolis_id = :PersonEvn_id and
								:Polis_Num = case when Person.PolisType_id=4 then edNum.Person_EdNum else Person.Polis_Num end and
								(isnull(Person.Polis_Ser,'') = isnull(:Polis_Ser,'')) and 
                                Person.OrgSMO_id = 	:OrgSMO_id
                                and cast(Person.Polis_begDate as date) = :Polis_begDate
						";

						 /*echo getDebugSQL($sql, array(
							'PersonEvn_id' => $data['PersonEvn_id'],
						 	'OMSSprTerr_id'=>$OmsSprTerr_id,
						  	'Person_id' => $data['Person_id'],
						   	'Polis_Num' => $Polis_Num,
						    'Polis_Ser' => $Polis_Ser,
					     	'Polis_begDate' => $Polis_begDate,
					      	'OrgSMO_id' => $OrgSmo_id));
						  exit(); */
						$res = $this->db->query($sql, array(
							'PersonEvn_id' => $data['PersonEvn_id'],
						 	'OMSSprTerr_id'=>$OmsSprTerr_id,
						  	'Person_id' => $data['Person_id'],
						   	'Polis_Num' => $Polis_Num,
						    'Polis_Ser' => $Polis_Ser,
					     	'Polis_begDate' => $Polis_begDate,
					      	'OrgSMO_id' => $OrgSmo_id));
						$isChng = $res->result('array');
						if(count($isChng)==0){
							$BDZaffected = true;
							$data['Polis_Guid'] = NULL;
						}else if($isChng[0]['Polis_endDate']!=$Polis_endDate){
							$BDZaffected = true;
						}

						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Polis_id bigint
							
							exec p_PersonPolis_upd
							@Polis_id = @Polis_id output,
							@PersonPolis_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@OmsSprTerr_id = :OmsSprTerr_id,
							@PolisType_id = :PolisType_id,
							@OrgSmo_id = :OrgSmo_id,
							@Polis_Ser = :Polis_Ser,
							@PolisFormType_id=:PolisFormType_id,
							@Polis_Num = :Polis_Num,
							@Polis_begDate = :Polis_begDate,
							@Polis_endDate = :Polis_endDate,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @Polis_id as Polis_id, @ErrMsg as ErrMsg
						";
						if(empty($Polis_endDate) || $Polis_endDate>=$Polis_begDate){
							$res = $this->db->query($sql, array('peid' => $peid,
								'serv_id'=>$serv_id,
								'pid'=>$pid,
								'OmsSprTerr_id'=>$OmsSprTerr_id,
								'PolisType_id'=>$PolisType_id,
								'OrgSmo_id'=>$OrgSmo_id,
								'Polis_Ser'=>$Polis_Ser,
								'PolisFormType_id'=>$PolisFormType_id,
								'Polis_Num'=>$Polis_Num,
								'Polis_begDate'=>$Polis_begDate,
								'Polis_endDate'=>$Polis_endDate,
								'pmUser_id'=>$data['pmUser_id']));
							$this->editPersonEvnDate(array('person_is_identified' => $person_is_identified, 'session'=>$data['session'],'PersonEvn_id' => $peid, 'Date' => $Polis_begDate, 'Server_id' => $serv_id, 'pmUser_id' => $data['pmUser_id']));
							$this->ValidateInsertQuery($res);
							$resp = $res->result('array');
							$this->_saveResponse['Polis_id'] = $resp[0]['Polis_id'];
						}
						/*-----*/
						if($person_is_identified||count($isChng)==0){
							$sql = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000),
									@Polis_id bigint;

									set @Polis_id=(select top 1 polis_id from v_PersonPolis with(nolock) where PersonPolis_id=:PersonEvn_id)

								exec p_Polis_server
									@Polis_id = @Polis_id,
									@Server_id = :Server_id,
									@Polis_Guid = :Polis_Guid,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;

								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$res = $this->db->query($sql, array(
								'PersonEvn_id' => $peid,
								'Server_id' => 0,
								'Polis_Guid'=>(isset($data['Polis_Guid']))?$data['Polis_Guid']:null,
								'pmUser_id' => $data['pmUser_id']
									));

							if (!is_object($res)) {
								return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)'));
							}

							$response = $res->result('array');

							if (!is_array($response) || count($response) == 0) {
								return array(array('success' => false, 'Error_Msg' => 'Ошибка при проставлении признака идентификации по сводной базе застрахованных'));
							}
						}
						
						if (isset($data['Federal_Num'])) {
							if ($person_is_identified) {
								$fsql = "
									select
									top 1 PE.PersonPolisEdNum_id
									from v_PersonPolisEdNum PE with (nolock)
									where 
									PE.Person_id =:Person_id
									and pe.PersonPolisEdNum_Ednum =:edNum
									order by
									PersonPolisEdNum_insDT desc,
									PersonPolisEdNum_TimeStamp desc
								";
								$fres = $this->db->query($fsql, array('Evn_setDT'=>$Evn_setDT,'PersonEvn_id' => $data['PersonEvn_id'], 'Person_id' => $data['Person_id'], 'edNum' => $Federal_Num, 'begdate' => $Polis_begDate,'serverId'=>$serv_id));
								$fsel = $fres->result('array');

								// если не было, то добавляем атрибут на дату
								if (count($fsel) == 0) {
									$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $data['Person_id'], 'begdate' => $Polis_begDate));
									if ($checkEdNum === false) {
										$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
										return array(array('success' => false, 'Error_Msg' => "На дату {$date} уже создан Ед. номер."));
									}
									$sql = "
										declare @ErrCode int
										declare @ErrMsg varchar(400)
		
										exec p_PersonPolisEdNum_ins
										@Server_id = ?,
										@Person_id = ?,
										@PersonPolisEdNum_insDT = ?,
										@PersonPolisEdNum_EdNum = ?,
										@pmUser_id = ?,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
		
										select @ErrMsg as ErrMsg
									";
									if ($Federal_Num != NULL) {
										//echo getDebugSQL($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));
										$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));
										$this->ValidateInsertQuery($res);
									}
								}
							} else {
								$this->savePersonEvnSimpleAttr(array(
									'ObjectName' => 'PersonPolisEdNum',
									'ObjectField' => 'PersonPolisEdNum_EdNum',
									'ObjectData' => empty($data['Federal_Num']) ? '' : $data['Federal_Num'],
									//'insDT' => $Polis_begDate,
									'Server_id' => $data['Server_id'],
									'Person_id' => $data['Person_id'],
									'PersonEvn_id' => $data['PersonEvn_id'],
									'pmUser_id' => $data['pmUser_id'],
									'PersonEvnClass_id' => 16
								));
							}
						}
					}
					if (!empty($data['Person_id']) && $data['Person_id'] != 0 && $data['Person_id'] != null) {
						$sql = "exec xp_PersonTransferEvn @Person_id = ?";
						$this->db->query($sql, array($data['Person_id']));
					}
					break;
				case 'NationalityStatus':
					$KLCountry_id = empty($data['KLCountry_id']) ? NULL : $data['KLCountry_id'];
					$NationalityStatus_IsTwoNation = !empty($data['NationalityStatus_IsTwoNation']) && $data['NationalityStatus_IsTwoNation'] ? 2 : 1;
					$LegalStatusVZN_id = empty($data['LegalStatusVZN_id']) ? null : $data['LegalStatusVZN_id'];
					// получаем последний атрибут, который был до этого Evn
					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						// получаем последний атрибут, который был до этого Evn
						$sql = "
							select top 1 
								P.PersonEvn_id,
								P.Server_id,
								NS.NationalityStatus_id,
								NS.LegalStatusVZN_id,
								P.PersonEvn_insDT
							from
								v_Person_all P with (nolock)
								left join v_NationalityStatus NS with(nolock) on NS.NationalityStatus_id = P.NationalityStatus_id
							where
								P.PersonEvnClass_id = 23 and
								P.PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								P.Person_id = :Person_id
							order by
								P.PersonEvn_insDT desc,
								P.PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}
					$resp = $this->validateNationalityStatus(array(
						'KLCountry_id' => $KLCountry_id,
						'Person_id' => $pid,
						'PersonEvn_id' => isset($sel[0])?$sel[0]['PersonEvn_id']:null
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @NationalityStatus_id bigint

							exec p_PersonNationalityStatus_ins
							@NationalityStatus_id = @NationalityStatus_id output,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@PersonNationalityStatus_insDT = :ins_dt,
							@KLCountry_id = :KLCountry_id,
							@NationalityStatus_IsTwoNation = :NationalityStatus_IsTwoNation,
							@LegalStatusVZN_id = :LegalStatusVZN_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @NationalityStatus_id as NationalityStatus_id, @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array(
							'serv_id' => $serv_id,
							'pid' => $pid,
							'ins_dt' => '2000-01-01 00:00:00.000',
							'KLCountry_id' => $KLCountry_id,
							'NationalityStatus_IsTwoNation' => $NationalityStatus_IsTwoNation,
							'LegalStatusVZN_id' => $LegalStatusVZN_id,
							'pmUser_id' => $data['pmUser_id'],
						));
						$this->ValidateInsertQuery($res);
						$resp = $res->result('array');
						$this->_saveResponse['NationalityStatus_id'] = $resp[0]['NationalityStatus_id'];
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @NationalityStatus_id bigint

							exec p_PersonNationalityStatus_upd
							@NationalityStatus_id = @NationalityStatus_id output,
							@PersonNationalityStatus_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@KLCountry_id = :KLCountry_id,
							@NationalityStatus_IsTwoNation = :NationalityStatus_IsTwoNation,
							@LegalStatusVZN_id = :LegalStatusVZN_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @NationalityStatus_id as NationalityStatus_id, @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'KLCountry_id'=>$KLCountry_id,
							'NationalityStatus_IsTwoNation'=>$NationalityStatus_IsTwoNation,
							'LegalStatusVZN_id'=>$LegalStatusVZN_id,
							'pmUser_id'=>$data['pmUser_id']
						));
						$this->ValidateInsertQuery($res);
						$resp = $res->result('array');
						$this->_saveResponse['NationalityStatus_id'] = $resp[0]['NationalityStatus_id'];
					}
					break;
				case 'Document':
					if (!isset($data['DocumentType_id']) || empty($data['DocumentType_id']))
						continue 2;
					$DocumentType_id = (empty($data['DocumentType_id']) ? NULL : $data['DocumentType_id']);
					$OrgDep_id = (empty($data['OrgDep_id']) ? NULL : $data['OrgDep_id']);
					$Document_Ser = (empty($data['Document_Ser']) ? '' : $data['Document_Ser']);
					$Document_Num = (empty($data['Document_Num']) ? '' : $data['Document_Num']);
					$Document_begDate = empty($data['Document_begDate']) ? NULL : $data['Document_begDate'];
					// получаем последний атрибут, который был до этого Evn
					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						// получаем последний атрибут, который был до этого Evn
						$sql = "
							select
								top 1 PersonEvn_id,
								Server_id,
								Document_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 9 and
								PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}

					$resp = $this->validateDocument(array(
						'DocumentType_id' => $DocumentType_id,
						'Document_begDate' => $Document_begDate,
						'Person_id' => $pid,
						'PersonEvn_id' => isset($sel[0])?$sel[0]['PersonEvn_id']:null,
						'Server_id' => $server_id,
						'pmUser_id' => $data['pmUser_id'],
						'type' => isset($sel[0])?'upd':'ins',
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Document_id bigint

							exec p_PersonDocument_ins
							@Document_id = @Document_id output,
							@Server_id = ?,
							@Person_id = ?,
							@PersonDocument_insDT = ?,
							@DocumentType_id = ?,
							@OrgDep_id = ?,
							@Document_Ser = ?,
							@Document_Num = ?,
							@Document_begDate = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @Document_id as Document_id, @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, '2000-01-01 00:00:00.000', $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
						$resp = $res->result('array');
						$this->_saveResponse['Document_id'] = $resp[0]['Document_id'];
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Document_id bigint
							
							exec p_PersonDocument_upd
							@Document_id = @Document_id output,
							@PersonDocument_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@DocumentType_id = :DocumentType_id,
							@OrgDep_id = :OrgDep_id,
							@Document_Ser = :Document_Ser,
							@Document_Num = :Document_Num,
							@Document_begDate = :Document_begDate,
							@Document_endDate = null,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @Document_id as Document_id, @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'DocumentType_id'=>$DocumentType_id,
							'OrgDep_id'=>$OrgDep_id,
							'Document_Ser'=>$Document_Ser,
							'Document_Num'=>$Document_Num,
							'Document_begDate'=>$Document_begDate,
							'pmUser_id'=>$data['pmUser_id']
							));
						$this->ValidateInsertQuery($res);
						$resp = $res->result('array');
						$this->_saveResponse['Document_id'] = $resp[0]['Document_id'];
					}
					break;
				case 'BAddress':
					$Address_Address = trim(empty($data['BAddress_Address']) ? null : $data['BAddress_Address']);

					$KLCountry_id = (empty($data['BKLCountry_id']) ? NULL : $data['BKLCountry_id']);
					$KLRgn_id = (empty($data['BKLRGN_id']) ? NULL : $data['BKLRGN_id']);
					$KLRgnSocr_id = (empty($data['BKLRGNSocr_id']) ? NULL : $data['BKLRGNSocr_id']);
					$KLSubRgn_id = (empty($data['BKLSubRGN_id']) ? NULL : $data['BKLSubRGN_id']);
					$KLSubRgnSocr_id = (empty($data['BKLSubRGNSocr_id']) ? NULL : $data['BKLSubRGNSocr_id']);
					$KLCity_id = (empty($data['BKLCity_id']) ? NULL : $data['BKLCity_id']);
					$KLCitySocr_id = (empty($data['BKLCitySocr_id']) ? NULL : $data['BKLCitySocr_id']);
					$KLTown_id = (empty($data['BKLTown_id']) ? NULL : $data['BKLTown_id']);
					$KLTownSocr_id = (empty($data['BKLTownSocr_id']) ? NULL : $data['BKLTownSocr_id']);
					$KLStreet_id = (empty($data['BKLStreet_id']) ? NULL : $data['BKLStreet_id']);
					$KLStreetSocr_id = (empty($data['BKLStreetSocr_id']) ? NULL : $data['BKLStreetSocr_id']);
					$Address_Zip = (empty($data['BAddress_Zip']) ? '' : $data['BAddress_Zip']);
					$Address_House = (empty($data['BAddress_House']) ? '' : $data['BAddress_House']);
					$Address_Corpus = (empty($data['BAddress_Corpus']) ? '' : $data['BAddress_Corpus']);
					$Address_Flat = (empty($data['BAddress_Flat']) ? '' : $data['BAddress_Flat']);
					$PersonSprTerrDop_id = (empty($data['BPersonSprTerrDop_id']) ? NULL : $data['BPersonSprTerrDop_id']);



					$serv_id = $server_id;

					// Сохранение данных стран кроме РФ, которые ранее отсутствовали
					list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
							$this->saveAddressAll($serv_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
					$sql = "
						select 
							 Address_id
							,PersonBirthPlace_id
						from 
							PersonBirthPlace (nolock)
						where 
							Person_id = ?
					";

					$res = $this->db->query($sql, array($pid));
					$sel = $res->result('array');

					if (!is_array($sel) || count($sel) == 0) {

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Address_id bigint

							exec p_Address_ins
							@Server_id = ?,
							@Address_id = @Address_id output,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg, @Address_id as Address_id  
						";
						$res = $this->db->query($sql, array($serv_id, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);

						$address_id = $res->result('array');



						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonBirthPlace_ins

							@Person_id = ?,
							@Address_id = ?,
							@pmUser_id = ?,

							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";

						$res = $this->db->query($sql, array($pid, $address_id[0]['Address_id'], $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					} else {
						$arr = array($KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $Address_Address);
						$delete = true;
						foreach ($arr as $key) {
							if (!empty($key)) {
								$delete = false;
							}
						}
						if (!$delete) {
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
								declare @Address_id bigint
							
								exec p_Address_upd
								@Server_id = :serv_id,
								@Address_id = :Address_id,
								@KLAreaType_id = null,
								@KLCountry_id = :KLCountry_id,
								@KLRgn_id = :KLRgn_id,
								@KLSubRgn_id = :KLSubRgn_id,
								@KLCity_id = :KLCity_id,
								@KLTown_id = :KLTown_id,
								@KLStreet_id = :KLStreet_id,
								@Address_Zip = :Address_Zip,
								@Address_House = :Address_House,
								@Address_Corpus = :Address_Corpus,
								@Address_Flat = :Address_Flat,
								@PersonSprTerrDop_id = :PersonSprTerrDop_id,
								@Address_Address = :Address_Address,
								@KLAreaStat_id = null,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output

								select @ErrMsg as ErrMsg  
							";
							$res = $this->db->query($sql, array(
								'serv_id'=>$serv_id,
								'Address_id'=>$sel[0]['Address_id'],
								'KLCountry_id'=>$KLCountry_id,
								'KLRgn_id'=>$KLRgn_id,
								'KLSubRgn_id'=>$KLSubRgn_id,
								'KLCity_id'=>$KLCity_id,
								'KLTown_id'=>$KLTown_id,
								'KLStreet_id'=>$KLStreet_id,
								'Address_Zip'=>$Address_Zip,
								'Address_House'=>$Address_House,
								'Address_Corpus'=>$Address_Corpus,
								'Address_Flat'=>$Address_Flat,
								'PersonSprTerrDop_id'=>$PersonSprTerrDop_id,
								'Address_Address'=>$Address_Address,
								'pmUser_id'=>$data['pmUser_id']));
							$this->ValidateInsertQuery($res);
						} else {
							$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
					declare @PersonBirthPlace_id bigint
					declare @Address_id bigint
					
					exec p_PersonBirthPlace_del
					@PersonBirthPlace_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
					
					/*
					exec p_Address_del
					@Address_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
					*/
					
					select @ErrMsg as ErrMsg; 
					";
							$res = $this->db->query($sql, array($sel[0]['PersonBirthPlace_id'], $sel[0]['Address_id']));
							$this->ValidateInsertQuery($res);
						}
					}
					break;
				case 'UAddress':
					if (empty($data['UKLCountry_id']) && getRegionNick() != 'kz')
						continue 2;
					$KLCountry_id = (empty($data['UKLCountry_id']) ? NULL : $data['UKLCountry_id']);
					$KLRgn_id = (empty($data['UKLRGN_id']) ? NULL : $data['UKLRGN_id']);
					$KLSubRgn_id = (empty($data['UKLSubRGN_id']) ? NULL : $data['UKLSubRGN_id']);
					$KLCity_id = (empty($data['UKLCity_id']) ? NULL : $data['UKLCity_id']);
					$KLTown_id = (empty($data['UKLTown_id']) ? NULL : $data['UKLTown_id']);
					$KLStreet_id = (empty($data['UKLStreet_id']) ? NULL : $data['UKLStreet_id']);
					$Address_Zip = (empty($data['UAddress_Zip']) ? '' : $data['UAddress_Zip']);
					$Address_House = (empty($data['UAddress_House']) ? '' : $data['UAddress_House']);
					$Address_Corpus = (empty($data['UAddress_Corpus']) ? '' : $data['UAddress_Corpus']);
					$Address_Flat = (empty($data['UAddress_Flat']) ? '' : $data['UAddress_Flat']);
					$Address_Address = (empty($data['UAddress_Address']) ? '' : $data['UAddress_Address']);
					//$Address_begDate = (empty($data['UAddress_begDate']) ? NULL : $data['UAddress_begDate']);
					$PersonSprTerrDop_id = (empty($data['UPersonSprTerrDop_id']) ? NULL : $data['UPersonSprTerrDop_id']);
					// получаем последний атрибут, который был до этого Evn
					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						$sql = "
							select
								top 1 PersonEvn_id,
								Server_id,
								UAddress_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 10 and
								PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonUAddress_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonUAddress_insDT = ?,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, '2000-01-01 00:00:00.000', $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
											
							exec p_PersonUAddress_upd
							@PersonUAddress_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@KLCountry_id = :KLCountry_id,
							@KLAreaType_id = null,
							@KLRgn_id = :KLRgn_id,
							@KLSubRgn_id = :KLSubRgn_id,
							@KLCity_id = :KLCity_id,
							@KLTown_id = :KLTown_id,
							@KLStreet_id = :KLStreet_id,
							@Address_Zip = :Address_Zip,
							@Address_House = :Address_House,
							@Address_Corpus = :Address_Corpus,
							@Address_Flat = :Address_Flat,
							@PersonSprTerrDop_id = :PersonSprTerrDop_id,
							@Address_Address = null,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'KLCountry_id'=>$KLCountry_id,
							'KLRgn_id'=>$KLRgn_id,
							'KLSubRgn_id'=>$KLSubRgn_id,
							'KLCity_id'=>$KLCity_id,
							'KLTown_id'=>$KLTown_id,
							'KLStreet_id'=>$KLStreet_id,
							'Address_Zip'=>$Address_Zip,
							'Address_House'=>$Address_House,
							'Address_Corpus'=>$Address_Corpus,
							'Address_Flat'=>$Address_Flat,
							'PersonSprTerrDop_id'=>$PersonSprTerrDop_id,
							'pmUser_id'=>$data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					break;
				case 'PAddress':
					if (empty($data['PKLCountry_id']) && getRegionNick() != 'kz')
						continue 2;
					$KLCountry_id = (empty($data['PKLCountry_id']) ? NULL : $data['PKLCountry_id']);
					$KLRgn_id = (empty($data['PKLRGN_id']) ? NULL : $data['PKLRGN_id']);
					$KLSubRgn_id = (empty($data['PKLSubRGN_id']) ? NULL : $data['PKLSubRGN_id']);
					$KLCity_id = (empty($data['PKLCity_id']) ? NULL : $data['PKLCity_id']);
					$KLTown_id = (empty($data['PKLTown_id']) ? NULL : $data['PKLTown_id']);
					$KLStreet_id = (empty($data['PKLStreet_id']) ? NULL : $data['PKLStreet_id']);
					$Address_Zip = (empty($data['PAddress_Zip']) ? '' : $data['PAddress_Zip']);
					$Address_House = (empty($data['PAddress_House']) ? '' : $data['PAddress_House']);
					$Address_Corpus = (empty($data['PAddress_Corpus']) ? '' : $data['PAddress_Corpus']);
					$Address_Flat = (empty($data['PAddress_Flat']) ? '' : $data['PAddress_Flat']);
					$Address_Address = (empty($data['PAddress_Address']) ? '' : $data['PAddress_Address']);
					//$Address_begDate = (empty($data['PAddress_begDate']) ? NULL : $data['PAddress_begDate']);
					$PersonSprTerrDop_id = (empty($data['PPersonSprTerrDop_id']) ? NULL : $data['PPersonSprTerrDop_id']);
					// получаем последний атрибут, который был до этого Evn
					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						$sql = "
							select
								top 1 PersonEvn_id,
								Server_id,
								PAddress_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 11 and
								PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonPAddress_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonPAddress_insDT = ?,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, '2000-01-01 00:00:00.000', $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
											
							exec p_PersonPAddress_upd
							@PersonPAddress_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@KLCountry_id = :KLCountry_id,
							@KLAreaType_id = null,
							@KLRgn_id = :KLRgn_id,
							@KLSubRgn_id = :KLSubRgn_id,
							@KLCity_id = :KLCity_id,
							@KLTown_id = :KLTown_id,
							@KLStreet_id = :KLStreet_id,
							@Address_Zip = :Address_Zip,
							@Address_House = :Address_House,
							@Address_Corpus = :Address_Corpus,
							@Address_Flat = :Address_Flat,
							@PersonSprTerrDop_id = :PersonSprTerrDop_id,
							@Address_Address = null,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'KLCountry_id'=>$KLCountry_id,
							'KLRgn_id'=>$KLRgn_id,
							'KLSubRgn_id'=>$KLSubRgn_id,
							'KLCity_id'=>$KLCity_id,
							'KLTown_id'=>$KLTown_id,
							'KLStreet_id'=>$KLStreet_id,
							'Address_Zip'=>$Address_Zip,
							'Address_House'=>$Address_House,
							'Address_Corpus'=>$Address_Corpus,
							'Address_Flat'=>$Address_Flat,
							'PersonSprTerrDop_id'=>$PersonSprTerrDop_id,
							'pmUser_id'=>$data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					break;
				case 'Job':
					//if ( !isset($data['Org_id']) || empty($data['Org_id']) )
					//continue 2;
					$Post_id = (empty($data['Post_id']) ? NULL : $data['Post_id']);
					$Org_id = (empty($data['Org_id']) ? NULL : $data['Org_id']);
					$OrgUnion_id = (empty($data['OrgUnion_id']) ? NULL : $data['OrgUnion_id']);
					if (isset($data['PostNew']) && !empty($data['PostNew'])) {
						$post_new = $data['PostNew'];

						if (is_numeric($post_new)) {

							$numPostID = 1;

							$sql = "
								select
									Post_id
								from
									v_Post with (nolock)
								where
									Post_id = ?
							";
							$result = $this->db->query($sql, array($post_new));
						} else {

							$sql = "
								select
									Post_id
								from
									v_Post with (nolock)
								where
									Post_Name like ? and Server_id = ?
							";
							$result = $this->db->query($sql, array($post_new, $data['Server_id']));
						}

						if (is_object($result)) {
							$sel = $result->result('array');
							if (isset($sel[0])) {
								if ($sel[0]['Post_id'] > 0)
									$Post_id = $sel[0]['Post_id'];
							} else if (isset($numPostID)) {
								$Post_id = null;
							} else {
								$sql = "
									declare @Psto_id bigint
									exec p_Post_ins
										@Post_Name = ?,
										@pmUser_id = ?,
										@Server_id = ?,
										@Post_id=@Psto_id output
									select @Psto_id as Post_id;
								";
								$result = $this->db->query($sql, array($post_new, $data['pmUser_id'], $data['Server_id']));
								if (is_object($result)) {
									$sel = $result->result('array');
									if ($sel[0]['Post_id'] > 0)
										$Post_id = $sel[0]['Post_id'];
								}
							}
						}
					}


					// OrgUnion может быть добавлен
					if (isset($data['OrgUnionNew']) && !empty($data['OrgUnionNew']) && !empty($data['Org_id']) && is_numeric($data['Org_id'])) {
						$org_union_new = $data['OrgUnionNew'];

						if (is_numeric($org_union_new)) {

							$numOrgUnionID = 1;

							$sql = "
								select
									OrgUnion_id
								from
									v_OrgUnion with (nolock)
								where
									OrgUnion_id = ?
							";

							$result = $this->db->query($sql, array($org_union_new));
						} else {

							$sql = "
								select
									OrgUnion_id
								from
									v_OrgUnion with (nolock)
								where
									OrgUnion_Name like ? and Server_id = ? and Org_id = ?
							";
							$result = $this->db->query($sql, array($org_union_new, $data['Server_id'], $data['Org_id']));
						}

						if (is_object($result)) {
							$sel = $result->result('array');
							if (isset($sel[0])) {
								if ($sel[0]['OrgUnion_id'] > 0)
									$OrgUnion_id = $sel[0]['OrgUnion_id'];
							} else if (isset($numOrgUnionID)) {
								$OrgUnion_id = null;
							} else {
								$sql = "
									declare @OrgUn_id bigint
									exec p_OrgUnion_ins
										@OrgUnion_Name = ?,
										@Org_id = ?,
										@pmUser_id = ?,
										@Server_id = ?,
										@OrgUnion_id=@OrgUn_id output
									select @OrgUn_id as OrgUnion_id;
								";
								$result = $this->db->query($sql, array($org_union_new, $data['Org_id'], $data['pmUser_id'], $data['Server_id']));
								if (is_object($result)) {
									$sel = $result->result('array');
									if ($sel[0]['OrgUnion_id'] > 0)
										$OrgUnion_id = $sel[0]['OrgUnion_id'];
								}
							}
						}
					}
					
					
					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						// получаем последний атрибут, который был до этого Evn
						$sql = "
							select
								top 1 PersonEvn_id,
								Server_id,
								Job_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 12 and
								PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonJob_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonJob_insDT = ?,
							@Org_id = ?,
							@OrgUnion_id = ?,
							@Post_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, '2000-01-01 00:00:00.000', $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
											
							exec p_PersonJob_upd
							@PersonJob_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@Org_id = :Org_id,
							@OrgUnion_id = :OrgUnion_id,
							@Post_id = :Post_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						//echo "<pre>";
						//die(getDebugSQL($sql,
						//array($peid, $serv_id, $pid, $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id'])));
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'Org_id'=>$Org_id,
							'OrgUnion_id'=>$OrgUnion_id,
							'Post_id'=>$Post_id,
							'pmUser_id'=>$data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					break;
				case 'Person_SurName':
					$BDZaffected = true;
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonSurName',
						'ObjectField' => 'PersonSurName_SurName',
						'ObjectData' => $data['Person_SurName'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'insPeriodic'=> (isset($data['insPeriodic']) && $data['insPeriodic']),
						'insDT'=> empty($data['Polis_begDate']) ? NULL : $data['Polis_begDate'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 1
					));
					break;
				case 'Person_SecName':
					$BDZaffected = true;
					$this->savePersonEvnSimpleAttr(array(
						'AllowEmpty' => true,
						'ObjectName' => 'PersonSecName',
						'ObjectField' => 'PersonSecName_SecName',
						'ObjectData' => $data['Person_SecName'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 3
					));
					break;
				case 'Person_FirName':
					$BDZaffected = true;
					if (!empty($data['Person_FirName'])) {
						$this->savePersonEvnSimpleAttr(array(
							'ObjectName' => 'PersonFirName',
							'ObjectField' => 'PersonFirName_FirName',
							'ObjectData' => $data['Person_FirName'],
							'Server_id' => $data['Server_id'],
							'Person_id' => $data['Person_id'],
							'PersonEvn_id' => $data['PersonEvn_id'],
							'pmUser_id' => $data['pmUser_id'],
							'PersonEvnClass_id' => 2
						));
					}
					break;
				case 'PersonPhone_Phone':
					if (!empty($data['PersonPhone_Phone'])) {
						$replace_symbols = array("-", "(", ")", " ");
						$data['PersonPhone_Phone'] = str_replace($replace_symbols, "", trim((string) $data['PersonPhone_Phone']));
					} else {
						$data['PersonPhone_Phone'] = '';
					}
					$this->savePersonEvnSimpleAttr(array(
						'AllowEmpty' => true,
						'ObjectName' => 'PersonPhone',
						'ObjectField' => 'PersonPhone_Phone',
						'ObjectData' => $data['PersonPhone_Phone'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 18
					));
					break;
				case 'PersonInn_Inn':
					$this->savePersonEvnSimpleAttr(array(
						'AllowEmpty' => true,
						'ObjectName' => 'PersonInn',
						'ObjectField' => 'PersonInn_Inn',
						'ObjectData' => $data['PersonInn_Inn'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 20
					));
					break;
				case 'PersonSocCardNum_SocCardNum':
					$this->savePersonEvnSimpleAttr(array(
						'AllowEmpty' => true,
						'ObjectName' => 'PersonSocCardNum',
						'ObjectField' => 'PersonSocCardNum_SocCardNum',
						'ObjectData' => $data['PersonSocCardNum_SocCardNum'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 21
					));
					break;
				case 'PersonRefuse_IsRefuse':
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonRefuse',
						'ObjectField' => 'PersonRefuse_IsRefuse',
						'ObjectData' => $data['PersonRefuse_IsRefuse'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 15,
						'AdditFields' => array(
							'@PersonRefuse_Year' => date('Y')
						)
					));
					break;
				case 'Person_BirthDay':
					$BDZaffected = true;
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonBirthDay',
						'ObjectField' => 'PersonBirthDay_BirthDay',
						'ObjectData' => empty($data['Person_BirthDay']) ? NULL : $data['Person_BirthDay'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 4
					));
					break;
				case 'Person_SNILS':
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonSnils',
						'ObjectField' => 'PersonSnils_Snils',
						'ObjectData' => $data['Person_SNILS'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 6
					));
					break;
				case 'PersonSex_id':
					$BDZaffected = true;
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonSex',
						'ObjectField' => 'Sex_id',
						'ObjectData' => !isset($data['PersonSex_id']) || !is_numeric($data['PersonSex_id']) ? NULL : $data['PersonSex_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 5
					));
					break;
				case 'SocStatus_id':
					$BDZaffected = true;
					$this->savePersonEvnSimpleAttr(array(
						'AllowEmpty' => $this->regionNick == 'kz',
						'ObjectName' => 'PersonSocStatus',
						'ObjectField' => 'SocStatus_id',
						'ObjectData' => empty($data['SocStatus_id']) ? NULL : $data['SocStatus_id'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 7
					));
					break;
				case 'FamilyStatus_id':

					$PersonFamilyStatus_IsMarried = (empty($data['PersonFamilyStatus_IsMarried']) ? NULL : $data['PersonFamilyStatus_IsMarried']);
					$FamilyStatus_id = (empty($data['FamilyStatus_id']) ? NULL : $data['FamilyStatus_id']);

					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						return array(array('success' => false, 'Error_Msg' => 'Хотя бы одно из полей "Семейное положение" или "Состоит в зарегистрированном браке" должно быть заполнено'));
					}

					if (isset($data['cancelCheckEvn']) && $data['cancelCheckEvn'] == true) {
						$sel[0]['Server_id'] = $data['Server_id'];
						$sel[0]['PersonEvn_id'] = $data['PersonEvn_id'];
					} else {
						// получаем последний атрибут, который был до этого Evn
						$sql = "
							select
								top 1 PersonEvn_id,
								Server_id,
								FamilyStatus_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 22 and
								PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) and
								Person_id = :Person_id
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$res = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Server_id' => $data['Server_id'], 'Person_id' => $data['Person_id']));
						$sel = $res->result('array');
					}
					// если не было, то добавляем атрибут на дату
					if (count($sel) == 0) {
						// для прав суперадмина
						$serv_id = $server_id;

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonFamilyStatus_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonFamilyStatus_insDT = ?,
							@FamilyStatus_id = ?,
							@PersonFamilyStatus_IsMarried = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
							
							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, '2000-01-01 00:00:00.000', $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					// иначе редактируем этот атрибут
					else {
						$serv_id = $sel[0]['Server_id'];
						$peid = $sel[0]['PersonEvn_id'];

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
											
							exec p_PersonFamilyStatus_upd
							@PersonFamilyStatus_id = :peid,
							@Server_id = :serv_id,
							@Person_id = :pid,
							@FamilyStatus_id = :FamilyStatus_id,
							@PersonFamilyStatus_IsMarried = :PersonFamilyStatus_IsMarried,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						
						$res = $this->db->query($sql, array(
							'peid'=>$peid,
							'serv_id'=>$serv_id,
							'pid'=>$pid,
							'FamilyStatus_id'=>$FamilyStatus_id,
							'PersonFamilyStatus_IsMarried'=>$PersonFamilyStatus_IsMarried,
							'pmUser_id'=>$data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}

					break;
				case 'Federal_Num':
					$BDZaffected = true;
					if (in_array('Polis', $evn_types)) {
						break;
					}

					//echo getDebugSQL($sql,$params);exit();
					$this->savePersonEvnSimpleAttr(array(
						'ObjectName' => 'PersonPolisEdNum',
						'ObjectField' => 'PersonPolisEdNum_EdNum',
						'ObjectData' => empty($data['Federal_Num']) ? '' : $data['Federal_Num'],
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id'],
						'PersonEvnClass_id' => 16
					));
					$sql = "declare @next date
set @next = (select
		top 1 cast(PersonEvn_insDT as DATE)
	from
		v_Person_all with (nolock)
	where
		PersonEvnClass_id = 16 and
		Person_id = :Person_id
		and PersonEvn_insDT > :Federal_begDate
	order by
		PersonEvn_insDT desc,
		PersonEvn_TimeStamp desc)

update Polis set Polis_Num = :Federal_Num where polis_id in(
select
	Person.Polis_id
from
	v_Person_all Person with (nolock)
	left join v_Polis Polis with (nolock) on Polis.Polis_id = Person.Polis_id
where
	Person.PersonEvnClass_id = 8 and
	Person.Person_id = :Person_id and
	Person.PersonEvn_insDT>=cast(:Federal_begDate as DATE) and
	(@next is null or Person.PersonEvn_insDT<@next) and
    Polis.PolisType_id = 4 )
";
					$params = array(
						'Federal_begDate' => $data['Federal_begDate'],
						'Person_id' => $data['Person_id'],
						'Federal_Num' => empty($data['Federal_Num']) ? '' : $data['Federal_Num']
					);
					if ($data['Person_id'] > 0) {
						//echo getDebugSQL($sql, $params);exit();
						$result = $this->db->query($sql, $params);
					}
					if (!empty($data['Person_id']) && $data['Person_id'] != 0 && $data['Person_id'] != null) {
						$sqls = "exec xp_PersonTransferEvn @Person_id = ?";
						$res = $this->db->query($sqls, array($data['Person_id']));
					}
					break;
				default:
			}
		}

		$not_evn_types = isset($data['NotEvnType'])?explode("|", $data['NotEvnType']):array();
		for ($i = 0; $i < count($not_evn_types); $i++) {
			switch($not_evn_types[$i]) {
				case 'Person':
					$params = array(
						'Person_id' => $data['Person_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					if (array_key_exists('Person_Comment', $data)) {
						$params['Person_Comment'] = $data['Person_Comment'];
					}
					if (array_key_exists('Person_deadDT', $data)) {
						$params['Person_deadDT'] = $data['Person_deadDT'];
					}
					if (array_key_exists('Person_IsInErz', $data)) {
						$params['Person_IsInErz'] = $data['Person_IsInErz'];
					}
					if (array_key_exists('BDZ_id', $data)) {
						$params['BDZ_id'] = $data['BDZ_id'];
					}
					if (array_key_exists('Person_IsUnknown', $data)) {
						$params['Person_IsUnknown'] = !empty($data['Person_IsUnknown']) ? 2 : 1;
					}
					$resp = $this->updatePerson($params);
					$this->ValidateInsertQuery($resp);
					break;
				case 'PersonChild':
					$resp = $this->savePersonChild($data);
					$this->ValidateInsertQuery($resp);
					break;
			}
		}

		if (count($evn_types) > 0) {
			$sql = "
				declare cur1 cursor read_only for
				select evn_id from v_evn with(nolock) where person_id =:Person_id
	
				declare @Person_id bigint
				declare @Evn_id bigint
				set @Person_id = :Person_id
				open cur1
				fetch next from cur1 into @Evn_id
				while @@FETCH_STATUS = 0
				begin
					exec xp_PersonTransferEvn
					@Person_id = 	@Person_id,
					@Evn_id = @Evn_id
	
					fetch next from cur1 into @Evn_id
				end
				close cur1
				deallocate cur1
			";
			$res = $this->db->query($sql, array("Person_id" => $data['Person_id'], "Server_id" => $data['Server_id']));
			if (!is_object($res)) {
				DieWithError('Ошибка БД при удалении данных.');
			}
		}
		// если ключевая периодика и не суперадмин и Карелия, то выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
		if ((!$person_is_identified && getRegionNick() == 'kareliya') || getRegionNick() == 'ufa') {
			$query = "
				select top 1
					ps.Person_id,
					ps.Server_pid,
					BDZ.BDZ_Guid
				from
					v_PersonState ps with(nolock)
					outer apply(select top 1 BDZ_Guid from v_Person p with(nolock) where p.Person_id=ps.Person_id) BDZ
				where
					ps.Person_id = :Person_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Person_id'])) {
					if ($BDZaffected && (((getRegionNick()=='ufa') && isSuperAdmin()&&!$person_is_identified)||!isSuperAdmin()) && $resp[0]['Server_pid'] == 0) {
						$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Person_server
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@BDZ_Guid = :BDZ_Guid,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
						$BDZGUID = (isset($resp[0]['BDZ_Guid']))?$resp[0]['BDZ_Guid']:null;
						$res = $this->db->query($sql, array("BDZ_Guid"=>$BDZGUID, "Person_id" => $resp[0]['Person_id'], "Server_id" => $data['session']['server_id'], "pmUser_id" => $data['pmUser_id']));
					}
				}
			}
		}
		//Устанавливается признак БДЗ
		if ($person_is_identified && in_array(getRegionNick(), array('ekb','buryatiya'))) {
			$BDZ_Guid = $this->getFirstResultFromQuery("
				select top 1 P.BDZ_Guid from v_Person P with(nolock) where P.Person_id = :Person_id
			", $data);
			$params = array(
				'Person_id' => $data['Person_id'],
				'BDZ_Guid' => $BDZ_Guid,
				'Server_id' => 0,
				'pmUser_id' => $data['pmUser_id'],
			);
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)
				exec p_Person_server
					@Server_id = :Server_id,
					@Person_id = :Person_id,
					@BDZ_Guid = :BDZ_Guid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrMsg as ErrMsg
			";
			$resp = $this->queryResult($sql, $params);
			$this->ValidateInsertQuery($resp);
		}
		if(getRegionNick()=='perm'){
			$query = "
				select top 1
					ps.Person_id,
					ps.Person_IsInErz
				from
					v_PersonState ps with(nolock)
				where
					ps.Person_id = :Person_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Person_id'])&&!empty($resp[0]['Person_IsInErz'])&&$resp[0]['Person_IsInErz']==1) {
					$sql = "update Person set Person_IsInErz = null where Person_id = :Person_id";
					$this->db->query($sql, array('Person_id' => $data['Person_id']));
				}
			}
		}
		if (getRegionNick() == 'penza') {
			$newMainFields = $this->getMainFields($data);
			if (!is_array($newMainFields)) {
				return $this->createError('','Ошибка при получении актуальных атрибутов человека');
			}
			$isInErz = ($newMainFields['Person_IsInErz'] == 2);
			if ($isInErz) {
				foreach($newMainFields as $field => $value) {
					if ($field == 'Person_IsInErz') {
						continue;
					}
					if ($oldMainFields[$field] != $value) {
						$isInErz = false;break;
					}
				}
				if (!$isInErz) {
					$sql = "update Person set Person_IsInErz = 1 where Person_id = :Person_id";
					$this->db->query($sql, array('Person_id' => $data['Person_id']));
				}
			}
		}
		if(!empty($data['BDZ_Guid']) && $person_is_identified){
			$bdzData = $this->getBDZPersonData($data);
			if($bdzData){
				if($this->checkExistPersonDouble($bdzData['Person_id'],$data['Person_id'])){
					return array(array('Error_Msg' => 'Человек уже находится в очереди на объединение двойников'));
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = null;
					exec pd.p_PersonDoubles_ins
						@PersonDoubles_id = @Res output,
						@Person_id = :Person_id,
						@Person_did = :Person_did,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";
				$queryParams=array(
					'Person_id'=>$bdzData['Person_id'],
					'Person_did'=>$data['Person_id'],
					'pmUser_id'=>$data['pmUser_id']
					);
				$result = $this->db->query($query, $queryParams);
				//Если возвращается ошибка, то выдаем пользователю и выходим
				if ( !$result )
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (объединение)'));
				}else{
					return array(array('Error_Msg' => 'Человек добавлен в очередь на объединение двойников'));
				}
				
			}
		}
		return array(array('success' => true));
	}

	/**
	 * @param $data
	 * @return array|bool
	 * Редактирование атрибута
	 */
	function editPersonEvnAttribute($data) {
		$is_superadmin = isSuperadmin();
		$server_id = $data['Server_id'];
		$pid = $data['Person_id'];
		$peid = $data['PersonEvn_id'];

		$peoid = $data['PersonEvnObject_id'];

		$evn_types = explode('|', $data['EvnType']);
		for ($i = 0; $i < count($evn_types); $i++) {
			switch ($evn_types[$i]) {
				case 'Polis':
					$OmsSprTerr_id = (empty($data['OMSSprTerr_id']) ? NULL : $data['OMSSprTerr_id']);
					$PolisType_id = (empty($data['PolisType_id']) ? NULL : $data['PolisType_id']);
					$OrgSmo_id = (empty($data['OrgSMO_id']) ? NULL : $data['OrgSMO_id']);
					$Polis_Ser = (empty($data['Polis_Ser']) ? '' : $data['Polis_Ser']);
					$PolisFormType_id = (empty($data['PolisFormType_id']) ? null : $data['PolisFormType_id']);
					$Polis_Num = (empty($data['Polis_Num']) ? '' : $data['Polis_Num']);
					$Polis_begDate = empty($data['Polis_begDate']) ? NULL : $data['Polis_begDate'];
					$Polis_endDate = empty($data['Polis_endDate']) ? NULL : $data['Polis_endDate'];
					// для прав суперадмина
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Polis_upd
						@Server_id = ?,
						@Polis_id = ?,
						@OmsSprTerr_id = ?,
						@PolisType_id = ?,
						@OrgSmo_id = ?,
						@Polis_Ser = ?,
						@PolisFormType_id=?,
						@Polis_Num = ?,
						@Polis_begDate = ?,
						@Polis_endDate = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					if(empty($Polis_endDate) || $Polis_endDate>=$Polis_begDate){
						$res = $this->db->query($sql, array($serv_id, $peoid, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
						$this->editPersonEvnDate(array('person_is_identified' => false, 'session'=>$data['session'],'PersonEvn_id' => $peoid, 'Date' => $Polis_begDate, 'Server_id' => $serv_id, 'pmUser_id' => $data['pmUser_id']));
					}
					break;
				case 'Document':
					$DocumentType_id = (empty($data['DocumentType_id']) ? NULL : $data['DocumentType_id']);
					$OrgDep_id = (empty($data['OrgDep_id']) ? NULL : $data['OrgDep_id']);
					$Document_Ser = (empty($data['Document_Ser']) ? '' : $data['Document_Ser']);
					$Document_Num = (empty($data['Document_Num']) ? '' : $data['Document_Num']);
					$Document_begDate = empty($data['Document_begDate']) ? NULL : $data['Document_begDate'];
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Document_upd
						@Server_id = ?,
						@Document_id = ?,
						@DocumentType_id = ?,
						@OrgDep_id = ?,
						@Document_Ser = ?,
						@Document_Num = ?,
						@Document_begDate = ?,
						@Document_endDate = null,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $peoid, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'UAddress':
					$KLCountry_id = (empty($data['UKLCountry_id']) ? NULL : $data['UKLCountry_id']);
					$KLRgn_id = (empty($data['UKLRGN_id']) ? NULL : $data['UKLRGN_id']);
					$KLSubRgn_id = (empty($data['UKLSubRGN_id']) ? NULL : $data['UKLSubRGN_id']);
					$KLCity_id = (empty($data['UKLCity_id']) ? NULL : $data['UKLCity_id']);
					$KLTown_id = (empty($data['UKLTown_id']) ? NULL : $data['UKLTown_id']);
					$KLStreet_id = (empty($data['UKLStreet_id']) ? NULL : $data['UKLStreet_id']);
					$Address_Zip = (empty($data['UAddress_Zip']) ? '' : $data['UAddress_Zip']);
					$Address_House = (empty($data['UAddress_House']) ? '' : $data['UAddress_House']);
					$Address_Corpus = (empty($data['UAddress_Corpus']) ? '' : $data['UAddress_Corpus']);
					$Address_Flat = (empty($data['UAddress_Flat']) ? '' : $data['UAddress_Flat']);
					$PersonSprTerrDop_id =(empty($data['PersonSprTerrDop_id']) ? NULL : $data['PersonSprTerrDop_id']);
					$Address_Address = (empty($data['UAddress_Address']) ? '' : $data['UAddress_Address']);
					//$Address_begDate = (empty($data['UAddress_begDate']) ? NULL : $data['UAddress_begDate']);
					
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Address_upd
						@Server_id = ?,
						@Address_id = ?,
						@KLCountry_id = ?,
						@KLAreaType_id = null,
						@KLRgn_id = ?,
						@KLSubRgn_id = ?,
						@KLCity_id = ?,
						@KLTown_id = ?,
						@KLStreet_id = ?,
						@Address_Zip = ?,
						@Address_House = ?,
						@Address_Corpus = ?,
						@Address_Flat = ?,
						@PersonSprTerrDop_id = ?,
						@Address_Address = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $peoid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PAddress':
					$KLCountry_id = (empty($data['PKLCountry_id']) ? NULL : $data['PKLCountry_id']);
					$KLRgn_id = (empty($data['PKLRGN_id']) ? NULL : $data['PKLRGN_id']);
					$KLSubRgn_id = (empty($data['PKLSubRGN_id']) ? NULL : $data['PKLSubRGN_id']);
					$KLCity_id = (empty($data['PKLCity_id']) ? NULL : $data['PKLCity_id']);
					$KLTown_id = (empty($data['PKLTown_id']) ? NULL : $data['PKLTown_id']);
					$KLStreet_id = (empty($data['PKLStreet_id']) ? NULL : $data['PKLStreet_id']);
					$Address_Zip = (empty($data['PAddress_Zip']) ? '' : $data['PAddress_Zip']);
					$Address_House = (empty($data['PAddress_House']) ? '' : $data['PAddress_House']);
					$Address_Corpus = (empty($data['PAddress_Corpus']) ? '' : $data['PAddress_Corpus']);
					$PersonSprTerrDop_id = (empty($data['PPersonSprTerrDop_id']) ? NULL : $data['PPersonSprTerrDop_id']);
					$Address_Flat = (empty($data['PAddress_Flat']) ? '' : $data['PAddress_Flat']);
					$Address_Address = (empty($data['PAddress_Address']) ? '' : $data['PAddress_Address']);
					//$Address_begDate = (empty($data['PAddress_begDate']) ? NULL : $data['PAddress_begDate']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_Address_upd
						@Server_id = ?,
						@Address_id = ?,
						@KLAreaType_id = null,
						@KLCountry_id = ?,
						@KLRgn_id = ?,
						@KLSubRgn_id = ?,
						@KLCity_id = ?,
						@KLTown_id = ?,
						@KLStreet_id = ?,
						@Address_Zip = ?,
						@Address_House = ?,
						@Address_Corpus = ?,
						@Address_Flat = ?,
						@PersonSprTerrDop_id = ?,
						@Address_Address = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					//echo getDebugSQL($sql, array($server_id, $peoid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
					$res = $this->db->query($sql, array($server_id, $peoid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Job':
					$Post_id = (empty($data['Post_id']) ? NULL : $data['Post_id']);
					$Org_id = (empty($data['Org_id']) ? NULL : $data['Org_id']);
					$OrgUnion_id = (empty($data['OrgUnion_id']) ? NULL : $data['OrgUnion_id']);
					$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_Job_upd
							@Server_id = ?,
							@Job_id = ?,
							@Org_id = ?,
							@OrgUnion_id = ?,
							@Post_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
					$res = $this->db->query($sql, array($server_id, $peoid, $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SurName':
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSurName_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonSurName_id = ?,
						@PersonSurName_SurName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $peid, $data['Person_SurName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SecName':
					// для прав суперадмина
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSecName_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonSecName_id = ?,
						@PersonSecName_SecName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $data['Person_SecName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_FirName':
					// для прав суперадмина
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonFirName_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonFirName_id = ?,
						@PersonFirName_FirName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $data['Person_FirName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_BirthDay':
					$serv_id = $server_id;

					$date = empty($data['Person_BirthDay']) ? NULL : $data['Person_BirthDay'];
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonBirthDay_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonBirthDay_id = ?,
						@PersonBirthDay_BirthDay = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $date, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SNILS':
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSnils_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonSnils_id = ?,
						@PersonSnils_Snils = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $data['Person_SNILS'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonSex_id':
					$serv_id = $server_id;

					$Sex_id = (!isset($data['PersonSex_id']) || !is_numeric($data['PersonSex_id']) ? NULL : $data['PersonSex_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSex_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonSex_id = ?,
						@Sex_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $Sex_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'SocStatus_id':
					$serv_id = $server_id;

					$SocStatus_id = (empty($data['SocStatus_id']) ? NULL : $data['SocStatus_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSocStatus_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonSocStatus_id = ?,
						@SocStatus_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $SocStatus_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'FamilyStatus_id':
					$serv_id = $server_id;

					$PersonFamilyStatus_IsMarried = (empty($data['PersonFamilyStatus_IsMarried']) ? NULL : $data['PersonFamilyStatus_IsMarried']);
					$FamilyStatus_id = (empty($data['FamilyStatus_id']) ? NULL : $data['FamilyStatus_id']);

					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						return array(array('success' => false, 'Error_Msg' => 'Хотя бы одно из полей "Семейное положение" или "Состоит в зарегистрированном браке" должно быть заполнено'));
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonFamilyStatus_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonFamilyStatus_id = ?,
						@FamilyStatus_id = ?,
						@PersonFamilyStatus_IsMarried = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Federal_Num':
					$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);

					// для прав суперадмина
					$serv_id = $server_id;

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonPolisEdNum_upd
						@Server_id = ?,
						@Person_id = ?,
						@PersonPolisEdNum_id = ?,
						@PersonPolisEdNum_EdNum = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";

					$res = $this->db->query($sql, array($serv_id, $pid, $peid, $Federal_Num, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);

					break;
				default:
			}
		}
		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение данных полиса
	 */
	function loadPolisData($data) {
		$sql = "
			SELECT  VP.OMSSprTerr_id,
				VP.OrgSMO_id,
				VP.Polis_Ser,
				case when VP.PolisType_id = 4 then '' else VP.Polis_Num end as Polis_Num,
				convert(varchar,cast(VP.Polis_begDate as datetime),104) as Polis_begDate,
				convert(varchar,cast(VP.Polis_endDate as datetime),104) as Polis_endDate,
				VP.PolisType_id,
				VP.PolisFormType_id,
				VP.BDZ_id,
				OST.KLRgn_id,
				pst.Person_edNum as Federal_Num,
				a.PersonEvn_id as FederalEvn_id,
				a.server_id as FederalServer_id,
				VP.Polis_Guid
			from v_Person_all ps with (nolock)
			left join Polis VP with (nolock) on vp.Polis_id=ps.Polis_id
			left join v_PersonState pst with (nolock) on pst.Person_id = ps.Person_id
			LEFT JOIN v_OmsSprTerr OST with (nolock) ON OST.OMSSprTerr_id = VP.OMSSprTerr_id
			outer apply (
			select
					top 1 PersonEvn_id,
					Server_id
				from
					v_PersonEvn with (nolock)
				where
					PersonEvnClass_id = 16 and
					PersonEvn_insDT <= VP.Polis_begDate and
					Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
			) as a
			where ps.Person_id= :Person_id
			and ps.PersonEvn_id= :PersonEvn_id
			
		";
		//echo getDebugSQL($sql, array('PersonEvn_id'=>$data['PersonEvn_id'],'Person_id'=>$data['Person_id'],'Server_id'=>$data['Server_id']));exit();
		$result = $this->db->query($sql, array('PersonEvn_id' => $data['PersonEvn_id'], 'Person_id' => $data['Person_id'], 'Server_id' => $data['Server_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка серии/номера документа на валидность
	 */
	function checkDocument($data) {
		$error = "";

		if (!empty($data['DocumentType_id'])) {
			$resp_dt = $this->queryResult("
				select top 1
					DocumentType_MaskSer,
					DocumentType_MaskNum
				from
					v_DocumentType
				where
					DocumentType_id = :DocumentType_id
			", array(
				'DocumentType_id' => $data['DocumentType_id']
			));

			if (!empty($resp_dt[0]['DocumentType_MaskSer'])) {
				if (!preg_match('/'.$resp_dt[0]['DocumentType_MaskSer'].'/ui', $data['Document_Ser'])) {
					$error .= 'серия документа не удовлетворяет маске: '.$resp_dt[0]['DocumentType_MaskSer'];
				}
			}

			if (!empty($resp_dt[0]['DocumentType_MaskNum'])) {
				if (!preg_match('/'.$resp_dt[0]['DocumentType_MaskNum'].'/ui', $data['Document_Num'])) {
					if (!empty($error)) {
						$error .= ', ';
					}
					$error .= 'номер документа не удовлетворяет маске: '.$resp_dt[0]['DocumentType_MaskNum'];
				}
			}
		}

		return $error;
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение данных о документе
	 */
	function loadDocumentData($data) {
		$sql = "
			select
				DocumentType_id,
				Document_Ser,
				Document_Num,
				OrgDep_id,
				convert(varchar,cast(Document_begDate as datetime),104) as Document_begDate
			from
				v_Document with (nolock)
			where
				Document_id = ?
		";

		$result = $this->db->query($sql, array($data['Document_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение данных о гражданстве
	 */
	function loadNationalityStatusData($data) {
		$sql = "
			select
				KLCountry_id,
				case when NationalityStatus_IsTwoNation = 2 then 1 else 0 end as NationalityStatus_IsTwoNation,
				LegalStatusVZN_id
			from
				v_NationalityStatus with (nolock)
			where
				NationalityStatus_id = :NationalityStatus_id
		";

		return $this->queryResult($sql, $data);
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение данных о работе
	 */
	function loadJobData($data) {
		$sql = "
			select
				Org_id,
				OrgUnion_id,
				Post_id
			from
				v_Job with (nolock)
			where
				Job_id = ?
		";

		$result = $this->db->query($sql, array($data['Job_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	function checkPesonEdNumOnDate($data) {
		$fsql = "
							select
								top 1 PersonEvn_id,
								Server_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 16 and
								Person_id = :Person_id
								and PersonEvn_insDT = :begdate
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
		$fres = $this->db->query($fsql, $data);
		if (is_object($fres)) {
			$fsel = $fres->result('array');
			if (count($fsel) == 1) {
				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * Проверка дат полиса на пересечение с другими полисами человека
	 */
	function checkPolisIntersection($data, $attr=false) {
		// отключить для уфы (refs #25773)
		if (getRegionNick() == 'ufa') {
			return true;
		}
		$isAstra = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'astra');
		$isKareliya = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'kareliya');
		$isEkb = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'ekb');
		$isBuryatiya = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'buryatiya');
		$isKrym = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'krym');
		$isPskov = (isset($data['session']['region']) && $data['session']['region']['nick'] == 'pskov');
		$Polis_begDate = empty($data['Polis_begDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_begDate']));
		$Polis_endDate = empty($data['Polis_endDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_endDate']));
		$Polis_closeDate = empty($data['Polis_begDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_begDate'] . "-1 days"));
		$Polis_openDate = empty($data['Polis_endDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_endDate'] . "+1 days"));
		$OrgSMO_id = empty($data['OrgSMO_id']) ? NULL : $data['OrgSMO_id'];
		$Polis_Ser = empty($data['Polis_Ser']) ? NULL : $data['Polis_Ser'];
		$Polis_Num = empty($data['Polis_Num']) ? NULL : $data['Polis_Num'];
		$Federal_Num = empty($data['Federal_Num']) ? NULL : $data['Federal_Num'];
		$PersonEvn_id = empty($data['PersonEvn_id']) ? NULL : $data['PersonEvn_id'];
		$Polis_id = empty($data['Polis_id']) ? NULL : $data['Polis_id'];
		$isIdent = (
			(isset($data['PersonIdentState_id']) && $data['PersonIdentState_id'] != 0) ||
			(isset($data['Person_IsInErz']) && $data['Person_IsInErz'] == 2)
		);

		if (!empty($PersonEvn_id)) {
			// PersonEvn_id - редактируемая периодика..
			// получаем последний атрибут, который был до этого Evn
			$sql = "
				select
					top 1 PersonEvn_id,
					Server_id,
					Polis_id
				from
					v_Person_all with (nolock)
				where
					PersonEvnClass_id = 8 and
					PersonEvn_insDT <= (select top 1 PersonEvn_insDT from v_PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id) and
					Person_id = :Person_id
				order by
					PersonEvn_insDT desc,
					PersonEvn_TimeStamp desc
			";

			//echo getDebugSQL($sql, array('PersonEvn_id' => $PersonEvn_id, 'Person_id' => $data['Person_id'])); die;
			$res = $this->db->query($sql, array('PersonEvn_id' => $PersonEvn_id, 'Person_id' => $data['Person_id']));
			$sel = $res->result('array');

			if (count($sel) > 0) {
				$PersonEvn_id = $sel[0]['PersonEvn_id'];
			} else {
				$PersonEvn_id = NULL;
			}
		}

		// запрос проверяющй пересечения периодов.
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $PersonEvn_id,
			'Polis_begDate' => $Polis_begDate,
			'Polis_endDate' => $Polis_endDate,
			'Polis_closeDate' => $Polis_closeDate,
			'Polis_openDate' => $Polis_openDate,
			'Polis_id' => $Polis_id,
		);

		$query = "
			select
				pa.Server_id,
				pa.PersonEvn_id,
				pol.Polis_id,
				pol.PolisType_id,
				CONVERT(varchar,pol.Polis_begDate, 104) as Polis_begDate,
				CONVERT(varchar,pol.Polis_endDate, 104) as Polis_endDate,
				pol.Polis_Ser,
				case when pol.PolisType_id = 4 
					then pa.Person_EdNum else pol.Polis_Num 
				end as Polis_Num,
				pol.OrgSMO_id,
				EdNum.PersonEvn_id as EdNumPersonEvn_id,
				EdNum.Server_id as EdNumServer_id
			from v_Person_all pa with (nolock) 
				inner join v_Polis pol with (nolock) ON pa.Polis_id = pol.Polis_id
				outer apply(
					select top 1 
						pped.PersonPolisEdNum_id as PersonEvn_id,
						pped.Server_id
					from v_PersonPolisEdNum pped with(nolock)
					where pped.Person_id = pa.Person_id
					and pped.PersonPolisEdNum_EdNum = pa.Person_EdNum
					and pped.PersonPolisEdNum_insDate <= pa.PersonEvn_insDT
					order by pped.PersonPolisEdNum_insDate desc
				) EdNum
			where
				pa.Person_id = :Person_id and
				pa.PersonEvnClass_id = 8 and
				pol.Polis_id <> isnull(:Polis_id, 0) and
				(cast(pol.Polis_begDate as date) < :Polis_endDate or :Polis_endDate is null) and
				(cast(pol.Polis_endDate as date) > :Polis_begDate or pol.Polis_endDate is null)
				
			";
		if (!$attr) {
			$query .="and 
			(pa.PersonEvn_id != :PersonEvn_id or :PersonEvn_id is null)";
		}
		//echo getDebugSQL($query, $queryParams);exit();
		$response = $this->queryResult($query, $queryParams);

		if (!is_array($response)) {
			// ошибка запроса
			return false;
		}

		if (count($response) == 0) {
			return true;
		}

		if ($isIdent) {		//С идентификацией
			if (count($response) > 0 && ($isEkb || $isBuryatiya || $isPskov || $isKrym)) {
				$listForClose = array();	//Изменить дату закрытие
				$listForUpdate = array();	//Изменить дату начала
				$listForDelete = array();	//Удалить полис
				$updatePolis = null;		//Обновить полис пришедшеми данными

				//Разбираем масив с пересечениями
				foreach($response as $polis) {
					if (strtotime($polis['Polis_begDate'])==strtotime($Polis_begDate) &&
						$polis['OrgSMO_id']==$OrgSMO_id && ($polis['Polis_Num']==$Polis_Num || $polis['Polis_Num']==$Federal_Num)
					) {
						$updatePolis = $polis;
						continue;
					}

					if (empty($Polis_endDate)) {
						if(strtotime($polis['Polis_begDate'])>=strtotime($Polis_begDate)){
							$listForDelete[] = $polis;
						}else{
							$listForClose[] = $polis;
						}
					} else {
						if (strtotime($polis['Polis_begDate'])>=strtotime($Polis_begDate) &&
							strtotime($polis['Polis_begDate'])<=strtotime($Polis_endDate) &&
							strtotime($polis['Polis_endDate'])>=strtotime($Polis_begDate) &&
							strtotime($polis['Polis_endDate'])<=strtotime($Polis_endDate)
						) {
							$listForDelete[] = $polis;	//Полностью попадет в период действия - удалить
						} else if (
							strtotime($polis['Polis_begDate'])>=strtotime($Polis_begDate) &&
							strtotime($polis['Polis_begDate'])<=strtotime($Polis_endDate) &&
							(empty($polis['Polis_endDate']) || strtotime($polis['Polis_endDate'])>strtotime($Polis_endDate))
						) {
							$listForUpdate[] = $polis;	//Дата начала попадет в период действия - изменить дату начала
						} else {
							$listForClose[] = $polis;	//Закрыть
						}
					}
				}

				foreach($listForUpdate as $polis) {
					$sql = "update Polis set Polis_begDate = :Polis_openDate where Polis_id = :Polis_id";
					$queryParams['Polis_id'] = $polis['Polis_id'];
					$this->db->query($sql, $queryParams);
				}
				foreach($listForClose as $polis) {
					$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
					$queryParams['Polis_id'] = $polis['Polis_id'];
					$this->db->query($sql, $queryParams);
				}
				$minDate = null;
				$maxDate = null;
				$deletedPersonEvnList = array();
				foreach($listForDelete as $polis) {
					if (getRegionNick() == 'buryatiya') {
						// необходимо проверять связана ли удаляемая периодика со случаями лечения, которые входят в реестры
						// в бурятии нет реестровой БД, да и хранимка ругается на форин по основной БД, поэтому на реестровую специально не перключаемся.
						$resp = $this->queryResult("
							select
								convert(varchar(10), MIN(Evn_disDate), 120) as Evn_minDate,
								convert(varchar(10), MAX(Evn_disDate), 120) as Evn_maxDate
							from
								r3.RegistryData (nolock)
							where
								PersonEvn_id = :PersonEvn_id
						", array(
							'PersonEvn_id' => $polis['PersonEvn_id']
						));

						if (!empty($resp[0]['Evn_minDate'])) {
							// Если периодика используется, то
							// Периодика не удаляется, а редактируется
							// o Дата начала = дата окончания случая, который входит в реестр (если таких случаев больше одного, то используется с наименьшей датой (самый ранний случай).
							// o Дата окончания = дата окончания случая, который входит в реестр (если таких случаев больше одного, то используется с наибольшей датой (последний случай).
							$sql = "update Polis with (rowlock) set Polis_endDate = :Polis_endDate, Polis_begDate = :Polis_begDate where Polis_id = :Polis_id";
							$this->db->query($sql, array(
								'Polis_begDate' => $resp[0]['Evn_minDate'],
								'Polis_endDate' => $resp[0]['Evn_maxDate'],
								'Polis_id' => $polis['Polis_id']
							));

							$sql = "update PersonEvn with (rowlock) set PersonEvn_begDT = :Polis_begDate, PersonEvn_insDT = :Polis_begDate where PersonEvn_id = :PersonEvn_id";
							$this->db->query($sql, array(
								'Polis_begDate' => $resp[0]['Evn_minDate'],
								'PersonEvn_id' => $polis['PersonEvn_id']
							));

							if (empty($minDate) || $resp[0]['Evn_minDate'] < $minDate) {
								$minDate = $resp[0]['Evn_minDate'];
							}
							if (empty($maxDate) || $resp[0]['Evn_maxDate'] > $maxDate) {
								$maxDate = $resp[0]['Evn_maxDate'];
							}

							continue; // пропускаем
						}

					}
					$deletedPersonEvnList[] = $polis['PersonEvn_id'];
					$this->deletePersonEvnAttribute(array(
						'Person_id' => $data['Person_id'],
						'PersonEvn_id' => $polis['PersonEvn_id'],
						'PersonEvnClass_id' => 8,
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $polis['Server_id']
					));
					if (!empty($polis['EdNumPersonEvn_id'])) {
						$count = $this->getFirstResultFromQuery("
							select count(*) as cnt
							from v_Person_all with(nolock)
							where Person_id = :Person_id
							and Person_EdNum = :Polis_Num
						", array(
							'Person_id' => $data['Person_id'],
							'Polis_Num' => $polis['Polis_Num']
						));
						if ($count === 0) {
							$this->deletePersonEvnAttribute(array(
								'Person_id' => $data['Person_id'],
								'PersonEvn_id' => $polis['EdNumPersonEvn_id'],
								'PersonEvnClass_id' => 16,
								'pmUser_id' => $data['pmUser_id'],
								'Server_id' => $polis['EdNumServer_id']
							));
						}
					}
				}

				if (is_array($updatePolis)) {
					return $updatePolis;
				} else if (getRegionNick() == 'buryatiya' && !empty($minDate)) {
					// возвращаем даты для которых надо создать полисы (2 шт.)
					return array('deletedPersonEvnList' => $deletedPersonEvnList, 'minDate' => $minDate, 'maxDate' => $maxDate);
				} else {
					return array('deletedPersonEvnList' => $deletedPersonEvnList);
				}
			} else if (count($response) == 1) {
				// если одно пересечение и сохранение после идентификации, то проставляем дату закрытия предыдущему полису.
				// если у пересекающегося полиса пустая дата конца и есть дата начала проставляем дату конца.
				if($isAstra){
					if($response[0]['Server_id']!==0){
						if(!empty($response[0]['Polis_begDate'])){
							if(strtotime($response[0]['Polis_begDate'])>=strtotime($Polis_begDate)){
								return $response[0];
							}else{
								$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
								$queryParams['Polis_id'] = $response[0]['Polis_id'];
								$this->db->query($sql, $queryParams);
								return true;
							}
						}else{

							$sql = "update Polis set Polis_endDate = :Polis_closeDate,Polis_begDate = :Polis_begDate where Polis_id = :Polis_id";
							$queryParams['Polis_begDate'] = '2000-01-02';
							$queryParams['Polis_id'] = $response[0]['Polis_id'];
							$this->db->query($sql, $queryParams);
							return true;
						}
					}
				} else if($isKareliya){
					if(
						strtotime($response[0]['Polis_begDate'])>=strtotime($Polis_begDate) && 
						!empty($response[0]['Polis_endDate']) && 
						strtotime($response[0]['Polis_endDate'])<=strtotime($Polis_endDate)
					){
						return $response[0];
					}else if(strtotime($response[0]['Polis_begDate'])>=strtotime($Polis_begDate)){
						$sql = "update Polis set Polis_begDate = :Polis_openDate where Polis_id = :Polis_id";
						$queryParams['Polis_id'] = $response[0]['Polis_id'];
						$this->db->query($sql, $queryParams);
						return true;
					}else{
						$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
						if(strtotime($Polis_closeDate)<strtotime($response[0]['Polis_begDate'])){
							$queryParams['Polis_closeDate'] = date('Y-m-d', strtotime($response[0]['Polis_begDate']));
						}
						$queryParams['Polis_id'] = $response[0]['Polis_id'];
						$this->db->query($sql, $queryParams);
						return true;
					}
				}else if (!empty($response[0]['Polis_begDate']) && (empty($response[0]['Polis_endDate']) || $response[0]['PolisType_id'] == 3 )) {
					$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
					if(strtotime($Polis_closeDate)<strtotime($response[0]['Polis_begDate'])){
						if ($isKrym) {
							$ts = strtotime($response[0]['Polis_begDate']);
							$queryParams['Polis_closeDate'] = date_create("@$ts")->modify("-1 day")->format('Y-m-d');
						} else {
							$queryParams['Polis_closeDate'] = date('Y-m-d', strtotime($response[0]['Polis_begDate']));
						}
					}
					$queryParams['Polis_id'] = $response[0]['Polis_id'];
					$this->db->query($sql, $queryParams);
					return true;
				}
			}
		} else {	//Без идентификации
			if (count($response) == 1) {
				$polis = $response[0];

				if ($isBuryatiya || $isKrym) {
					if ($polis['PolisType_id'] == 3 && strtotime($polis['Polis_begDate'])<strtotime($Polis_begDate)) {
						$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
						$queryParams['Polis_id'] = $polis['Polis_id'];
						$this->db->query($sql, $queryParams);
						return true;
					}
				}
				if (empty($polis['Polis_endDate']) && strtotime($polis['Polis_begDate']) < strtotime($Polis_begDate)) {
					$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
					$queryParams['Polis_id'] = $polis['Polis_id'];
					$this->db->query($sql, $queryParams);
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Проверка активности территории полиса
	 */
	function checkOMSSprTerrDate($data) {
		if ( empty($data['OMSSprTerr_id']) ) {
			return true;
		}

		$queryParams = array(
			'OMSSprTerr_id' => $data['OMSSprTerr_id'],
			'Polis_begDate' => empty($data['Polis_begDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_begDate'])),
			'Polis_endDate' => empty($data['Polis_endDate']) ? NULL : date('Y-m-d', strtotime($data['Polis_endDate']))
		);

		$query = "
			SELECT top 1
				OMSSprTerr_id
			FROM v_OMSSprTerr with (nolock)
			where OMSSprTerr_id = :OMSSprTerr_id
				and (OMSSprTerr_begDate <= :Polis_begDate or OMSSprTerr_begDate is null)
				and (OMSSprTerr_endDate >= :Polis_begDate or OMSSprTerr_endDate is null)
				and (OMSSprTerr_begDate <= :Polis_endDate or OMSSprTerr_begDate is null or :Polis_endDate is null)
				and (OMSSprTerr_endDate >= :Polis_endDate or OMSSprTerr_endDate is null or :Polis_endDate is null)
		";
		
		$result = $this->db->query($query, $queryParams);
		
		if (!is_object($result)) {
			return false;
		}
		
		$response = $result->result('array');
		if (is_array($response) && count($response) > 0) {
			return true;
		}
		
		return false;
	}

	/**
	 * Проверка единого номера полиса на уникальность
	 * @task https://redmine.swan.perm.ru/issues/88654
	 * Вынесено в региональную модель для Перми по задаче https://redmine.swan.perm.ru/issues/93041
	 */
	function checkFederalNumUnique($data) {
		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * Сохранение атрибутов
	 */
	function saveAttributeOnDate($data) {
		// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан, 
		// чтобы лишний раз в сессию не писать, экономим на спичках

		if(!isset($_SESSION))
			session_start();
		if (isset($data['session']['person']) && isset($data['session']['person']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person']['Person_id'])
			unset($_SESSION['person']);

		if (isset($data['session']['person_short']) && isset($data['session']['person_short']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person_short']['Person_id'])
			unset($_SESSION['person_short']);
		session_write_close();

		$oldMainFields = array();

		if (getRegionNick() == 'penza') {
			$oldMainFields = $this->getMainFields($data);
			if (!is_array($oldMainFields)) {
				return $this->createError('','Ошибка при получении актуальных атрибутов человека');
			}
		}

		$is_superadmin = isSuperadmin();
		$server_id = $data['Server_id'];
		$pid = $data['Person_id'];
		$ins_dt = substr(trim($data['Date']), 6, 4) . "-" . substr(trim($data['Date']), 3, 2) . "-" . substr(trim($data['Date']), 0, 2) . ' ' . $data['Time'] . ':00.000';
		$evn_types = explode('|', $data['EvnType']);
		for ($i = 0; $i < count($evn_types); $i++) {
			switch ($evn_types[$i]) {
				case 'Polis':
					$OmsSprTerr_id = (empty($data['OMSSprTerr_id']) ? NULL : $data['OMSSprTerr_id']);
					$PolisType_id = (empty($data['PolisType_id']) ? NULL : $data['PolisType_id']);
					$OrgSmo_id = (empty($data['OrgSMO_id']) ? NULL : $data['OrgSMO_id']);
					$Polis_Ser = (empty($data['Polis_Ser']) ? '' : $data['Polis_Ser']);
					$PolisFormType_id = (empty($data['PolisFormType_id']) ? null : $data['PolisFormType_id']);
					$Polis_Num = (empty($data['Polis_Num']) ? '' : $data['Polis_Num']);
					$Polis_begDate = empty($data['Polis_begDate']) ? NULL : substr(trim($data['Polis_begDate']), 6, 4) . "-" . substr(trim($data['Polis_begDate']), 3, 2) . "-" . substr(trim($data['Polis_begDate']), 0, 2);
					$Polis_endDate = empty($data['Polis_endDate']) ? NULL : substr(trim($data['Polis_endDate']), 6, 4) . "-" . substr(trim($data['Polis_endDate']), 3, 2) . "-" . substr(trim($data['Polis_endDate']), 0, 2);
					// для прав суперадмина
					$serv_id = $server_id;
					$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);
					if ($PolisType_id == 4) {
						$Polis_Num = $Federal_Num;
						$data['Polis_Num']=$Federal_Num;
					}
					if ($is_superadmin) {
						//$serv_id = 0;
					}


					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}
					if (!empty($Federal_Num)) {
						$fsql = "
							select
								top 1 PersonEvn_id,
								Server_id
							from
								v_Person_all with (nolock)
							where
								PersonEvnClass_id = 16 and
								Person_id = :Person_id
								and person_Ednum = :edNum
								and PersonEvn_insDT <= :begdate
							order by
								PersonEvn_insDT desc,
								PersonEvn_TimeStamp desc
						";
						$fres = $this->db->query($fsql, array('Person_id' => $data['Person_id'], 'edNum' => $Federal_Num, 'begdate' => $Polis_begDate));
						$fsel = $fres->result('array');

						// если не было, то добавляем атрибут на дату
						if (count($fsel) == 0) {
							$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $data['Person_id'], 'begdate' => $Polis_begDate));
							if ($checkEdNum === false) {
								$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
								return array(array('success' => false, 'Error_Msg' => "На дату {$date} уже создан Ед. номер."));
							}
							$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
	
							exec p_PersonPolisEdNum_ins
								@Server_id = ?,
								@Person_id = ?,
								@PersonPolisEdNum_insDT = ?,
								@PersonPolisEdNum_EdNum = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
							select @ErrMsg as ErrMsg
						";
							$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));
							$this->ValidateInsertQuery($res);
						}
					}
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonPolis_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonPolis_insDT = ?,
						@OmsSprTerr_id = ?,
						@PolisType_id = ?,
						@OrgSmo_id = ?,
						@Polis_Ser = ?,
						@PolisFormType_id =?,
						@Polis_Num = ?,
						@Polis_begDate = ?,
						@Polis_endDate = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					if(empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate){
						$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}

					break;
				case 'NationalityStatus':
					$KLCountry_id = (empty($data['KLCountry_id']) ? NULL : $data['KLCountry_id']);
					$NationalityStatus_IsTwoNation = (!empty($data['NationalityStatus_IsTwoNation']) ? 2 : 1);
					$LegalStatusVZN_id = (empty($data['LegalStatusVZN_id']) ? NULL : $data['LegalStatusVZN_id']);

					$resp = $this->validateNationalityStatus(array(
						'KLCountry_id' => $KLCountry_id,
						'Person_id' => $pid,
						'PersonEvn_insDT' => $ins_dt
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonNationalityStatus_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonNationalityStatus_insDT = ?,
						@KLCountry_id = ?,
						@NationalityStatus_IsTwoNation = ?,
						@LegalStatusVZN_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $KLCountry_id, $NationalityStatus_IsTwoNation, $LegalStatusVZN_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Document':
					$DocumentType_id = (empty($data['DocumentType_id']) ? NULL : $data['DocumentType_id']);
					$OrgDep_id = (empty($data['OrgDep_id']) ? NULL : $data['OrgDep_id']);
					$Document_Ser = (empty($data['Document_Ser']) ? '' : $data['Document_Ser']);
					$Document_Num = (empty($data['Document_Num']) ? '' : $data['Document_Num']);
					$Document_begDate = empty($data['Document_begDate']) ? NULL : substr(trim($data['Document_begDate']), 6, 4) . "-" . substr(trim($data['Document_begDate']), 3, 2) . "-" . substr(trim($data['Document_begDate']), 0, 2);

					$resp = $this->validateDocument(array(
						'DocumentType_id' => $DocumentType_id,
						'Document_begDate' => $Document_begDate,
						'Person_id' => $pid,
						'PersonEvn_insDT' => $ins_dt,
						'Server_id' => $server_id,
						'pmUser_id' => $data['pmUser_id'],
						'type' => 'ins',
					));
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonDocument_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonDocument_insDT = ?,
						@DocumentType_id = ?,
						@OrgDep_id = ?,
						@Document_Ser = ?,
						@Document_Num = ?,
						@Document_begDate = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'UAddress':
					$KLCountry_id = (empty($data['UKLCountry_id']) ? NULL : $data['UKLCountry_id']);
					$KLRgn_id = (empty($data['UKLRGN_id']) ? NULL : $data['UKLRGN_id']);
					$KLSubRgn_id = (empty($data['UKLSubRGN_id']) ? NULL : $data['UKLSubRGN_id']);
					$KLCity_id = (empty($data['UKLCity_id']) ? NULL : $data['UKLCity_id']);
					$KLTown_id = (empty($data['UKLTown_id']) ? NULL : $data['UKLTown_id']);
					$KLStreet_id = (empty($data['UKLStreet_id']) ? NULL : $data['UKLStreet_id']);
					$Address_Zip = (empty($data['UAddress_Zip']) ? '' : $data['UAddress_Zip']);
					$Address_House = (empty($data['UAddress_House']) ? '' : $data['UAddress_House']);
					$Address_Corpus = (empty($data['UAddress_Corpus']) ? '' : $data['UAddress_Corpus']);
					$Address_Flat = (empty($data['UAddress_Flat']) ? '' : $data['UAddress_Flat']);
					$PersonSprTerrDop_id = (empty($data['UPersonSprTerrDop_id']) ? NULL : $data['UPersonSprTerrDop_id']);
					$Address_Address = (empty($data['UAddress_Address']) ? '' : $data['UAddress_Address']);
					//$Address_begDate = (empty($data['UAddress_begDate']) ? NULL : $data['UAddress_begDate']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonUAddress_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonUAddress_insDT = ?,
						@KLCountry_id = ?,
						@KLRgn_id = ?,
						@KLSubRgn_id = ?,
						@KLCity_id = ?,
						@KLTown_id = ?,
						@KLStreet_id = ?,
						@Address_Zip = ?,
						@Address_House = ?,
						@Address_Corpus = ?,
						@Address_Flat = ?,
						@PersonSprTerrDop_id = ?,
						@Address_Address = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PAddress':
					$KLCountry_id = (empty($data['PKLCountry_id']) ? NULL : $data['PKLCountry_id']);
					$KLRgn_id = (empty($data['PKLRGN_id']) ? NULL : $data['PKLRGN_id']);
					$KLSubRgn_id = (empty($data['PKLSubRGN_id']) ? NULL : $data['PKLSubRGN_id']);
					$KLCity_id = (empty($data['PKLCity_id']) ? NULL : $data['PKLCity_id']);
					$KLTown_id = (empty($data['PKLTown_id']) ? NULL : $data['PKLTown_id']);
					$KLStreet_id = (empty($data['PKLStreet_id']) ? NULL : $data['PKLStreet_id']);
					$Address_Zip = (empty($data['PAddress_Zip']) ? '' : $data['PAddress_Zip']);
					$Address_House = (empty($data['PAddress_House']) ? '' : $data['PAddress_House']);
					$Address_Corpus = (empty($data['PAddress_Corpus']) ? '' : $data['PAddress_Corpus']);
					$Address_Flat = (empty($data['PAddress_Flat']) ? '' : $data['PAddress_Flat']);
					$PersonSprTerrDop_id = (empty($data['PPersonSprTerrDop_id']) ? NULL : $data['PPersonSprTerrDop_id']);
					$Address_Address = (empty($data['PAddress_Address']) ? '' : $data['PAddress_Address']);
					//$Address_begDate = (empty($data['PAddress_begDate']) ? NULL : $data['PAddress_begDate']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonPAddress_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonPAddress_insDT = ?,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, NULL, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Job':
					$Post_id = (empty($data['Post_id']) ? NULL : $data['Post_id']);
					$Org_id = (empty($data['Org_id']) ? NULL : $data['Org_id']);
					$OrgUnion_id = (empty($data['OrgUnion_id']) ? NULL : $data['OrgUnion_id']);
					$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonJob_ins
								@Server_id = ?,
								@Person_id = ?,
								@PersonJob_insDT = ?,
								@Org_id = ?,
								@OrgUnion_id = ?,
								@Post_id = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
							select @ErrMsg as ErrMsg
						";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SurName':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSurName_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonSurName_insDT = ?,
							@PersonSurName_SurName = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $ins_dt, $data['Person_SurName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SecName':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSecName_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonSecName_insDT = ?,
							@PersonSecName_SecName = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['Person_SecName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_FirName':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonFirName_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonFirName_insDT = ?,
							@PersonFirName_FirName = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['Person_FirName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonPhone_Phone':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonPhone_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonPhone_insDT = ?,
							@PersonPhone_Phone = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonPhone_Phone'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonInn_Inn':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonInn_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonInn_insDT = ?,
							@PersonInn_Inn = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonInn_Inn'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonSocCardNum_SocCardNum':
					if ($is_superadmin) {
						// для прав суперадмина
						$serv_id = $server_id;
						if ($is_superadmin) {
							//$serv_id = 0;
						}

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_PersonSocCardNum_ins
								@Server_id = ?,
								@Person_id = ?,
								@PersonSocCardNum_insDT = ?,
								@PersonSocCardNum_SocCardNum = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonSocCardNum_SocCardNum'], $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					break;
				case 'PersonRefuse_IsRefuse':
					if ($is_superadmin) {
						// для прав суперадмина
						$serv_id = $server_id;
						if ($is_superadmin) {
							//$serv_id = 0;
						}

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @year int
							set @year = year(dbo.tzGetDate())

							exec p_PersonRefuse_ins
								@Person_id = ?,
								@PersonRefuse_IsRefuse = ?,
								@PersonRefuse_Year = @year,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($pid, $data['PersonRefuse_IsRefuse'], $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
					break;
				case 'PersonChildExist_IsChild':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$PersonChildExist_IsChild = (empty($data['PersonChildExist_IsChild']) ? NULL : $data['PersonChildExist_IsChild']);

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonChildExist_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonChildExist_setDT = ?,
							@PersonChildExist_IsChild = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonChildExist_IsChild, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonCarExist_IsCar':
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$PersonCarExist_IsCar = (empty($data['PersonCarExist_IsCar']) ? NULL : $data['PersonCarExist_IsCar']);

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonCarExist_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonCarExist_setDT = ?,
							@PersonCarExist_IsCar = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonCarExist_IsCar, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonHeight_Height':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonHeight_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonHeight_setDT = ?,
							@PersonHeight_Height = ?,
							@Okei_id = 2,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonHeight_Height'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonWeight_Weight':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonWeight_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonWeight_setDT = ?,
							@PersonWeight_Weight = ?,
							@Okei_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonWeight_Weight'], $data['Okei_id'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_BirthDay':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$date = empty($data['Person_BirthDay']) ? NULL : $data['Person_BirthDay'];
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonBirthDay_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonBirthDay_insDT = ?,
							@PersonBirthDay_BirthDay = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $date, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Person_SNILS':
					$serv_id = $server_id;
					if ($is_superadmin) {
						$serv_id = 1;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSnils_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonSnils_insDT = ?,
							@PersonSnils_Snils = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['Person_SNILS'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'PersonSex_id':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$Sex_id = (!isset($data['PersonSex_id']) || !is_numeric($data['PersonSex_id']) ? NULL : $data['PersonSex_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSex_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonSex_insDT = ?,
							@Sex_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $Sex_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'SocStatus_id':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$SocStatus_id = (empty($data['SocStatus_id']) ? NULL : $data['SocStatus_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonSocStatus_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonSocStatus_insDT = ?,
							@SocStatus_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $SocStatus_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'FamilyStatus_id':
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}

					$PersonFamilyStatus_IsMarried = (empty($data['PersonFamilyStatus_IsMarried']) ? NULL : $data['PersonFamilyStatus_IsMarried']);
					$FamilyStatus_id = (empty($data['FamilyStatus_id']) ? NULL : $data['FamilyStatus_id']);

					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						return array(array('success' => false, 'Error_Msg' => 'Хотя бы одно из полей "Семейное положение" или "Состоит в зарегистрированном браке" должно быть заполнено'));
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonFamilyStatus_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonFamilyStatus_insDT = ?,
							@FamilyStatus_id = ?,
							@PersonFamilyStatus_IsMarried = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				case 'Federal_Num':
					$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);
					$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $pid, 'begdate' => $ins_dt));
					if ($checkEdNum === false) {
						$date = ConvertDateFormat($ins_dt, 'd.m.Y');
						return array(array('success' => false, 'Error_Msg' => "На дату {$date} уже создан Ед. номер."));
					}
					// для прав суперадмина
					$serv_id = $server_id;
					if ($is_superadmin) {
						//$serv_id = 0;
					}
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)

						exec p_PersonPolisEdNum_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonPolisEdNum_insDT = ?,
							@PersonPolisEdNum_EdNum = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $Federal_Num, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					break;
				default:
					break;
			}
		}
		if (getRegionNick() == 'penza') {
			$newMainFields = $this->getMainFields($data);
			if (!is_array($newMainFields)) {
				return $this->createError('','Ошибка при получении актуальных атрибутов человека');
			}
			$isInErz = ($newMainFields['Person_IsInErz'] == 2);
			if ($isInErz) {
				foreach($newMainFields as $field => $value) {
					if ($field == 'Person_IsInErz') {
						continue;
					}
					if ($oldMainFields[$field] != $value) {
						$isInErz = false;break;
					}
				}
				if (!$isInErz) {
					$sql = "update Person set Person_IsInErz = 1 where Person_id = :Person_id";
					$this->db->query($sql, array('Person_id' => $data['Person_id']));
				}
			}
		}
		return array(array('success' => true));
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function getMainFields($data) {
		$params = array('Person_id' => $data['Person_id']);
		$query = null;
		if (getRegionNick() == 'penza') {
			$query = "
				select top 1
					PS.Person_SurName,
					PS.Person_FirName,
					PS.Person_SecName,
					convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay,
					PS.Sex_id as PersonSex_id,
					PS.Person_SNILS,
					PS.PolisType_id,
					PS.Polis_Ser,
					PS.Polis_Num,
					PS.Person_IsInErz
				from v_PersonState PS with(nolock)
				where PS.Person_id = :Person_id
			";
		}
		if (empty($query)) {
			return array();
		}
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Проверка введенных данных по человеку на двойника (старая)
	 */
	function checkPersonDoubles($data) {
		$queryParams = array();

		$queryParams['Person_SurName'] = preg_replace('/[ё]/iu', 'Е', trim($data['Person_SurName']));
		$queryParams['Person_FirName'] = preg_replace('/[ё]/iu', 'Е', trim($data['Person_FirName']));

		if ($data['Person_id'] > 0)
			$queryParams['Person_id'] = $data['Person_id'];
		else
			$queryParams['Person_id'] = NULL;
		//echo strlen($data['Person_SecName'])." = ".mb_strlen($data['Person_SecName']);exit();
		if (!empty($data['Person_SecName']) && mb_strlen($data['Person_SecName']) > 0 && $data['Person_SecName'] != '- - -')
			$queryParams['Person_SecName'] = preg_replace('/[ё]/iu', 'Е', $data['Person_SecName']);
		else
			$queryParams['Person_SecName'] = NULL;

		if (isset($data['Person_BirthDay'])) {
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		} else {
			$queryParams['Person_BirthDay'] = NULL;
		}

		if (isset($data['OMSSprTerr_id'])) {
			$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
		}

		if (isset($data['Person_IsUnknown'])) {
			$queryParams['Person_IsUnknown'] = $data['Person_IsUnknown'];
		} else {
			$queryParams['Person_IsUnknown'] = NULL;
		}

		/*if (isset($data['Polis_Ser']) && mb_strlen($data['Polis_Ser']) > 0 && isset($data['Polis_Num']) && mb_strlen($data['Polis_Num']) > 0) {
			$queryParams['Polis_Ser'] = trim($data['Polis_Ser']);
			$queryParams['Polis_Num'] = trim($data['Polis_Num']);
		} else {
			$queryParams['Polis_Ser'] = null;
			$queryParams['Polis_Num'] = null;
		}*/

		$queryParams['Polis_Ser'] = '';
		$queryParams['Polis_Num'] = '';
		$queryParams['Federal_Num'] = '';
		if(isset($data['Polis_Ser']) && mb_strlen($data['Polis_Ser']) > 0){
			$queryParams['Polis_Ser'] = trim($data['Polis_Ser']);
		}
		if(isset($data['Polis_Num']) && mb_strlen($data['Polis_Num']) > 0){
			$queryParams['Polis_Num'] = trim($data['Polis_Num']);
		}
		if(!in_array($data['session']['region']['nick'], array('kz', 'ufa')))
		{
			if(isset($data['Federal_Num']) && mb_strlen($data['Federal_Num']) > 0){
				$queryParams['Federal_Num'] = trim($data['Federal_Num']);
			}
		}

		$query = "
			declare @DT_id int
			
			exec xp_PersonDoublesCheck
				@Person_id       = :Person_id,
				@Person_SurName  = :Person_SurName, -- фамилия
				@Person_FirName  = :Person_FirName, -- имя
				@Person_SecName  = " . (!empty($queryParams['Person_SecName']) ? ":Person_SecName, -- отчество" : "null,") . "
				@Person_BirthDay = :Person_BirthDay, -- ДР
				@Polis_Ser = :Polis_Ser, -- серия полиса
				@Polis_Num = :Polis_Num, -- номер полиса
				@Federal_Num = :Federal_Num, --ЕНП
				@DoubleType_id = @DT_id output, -- критерий проверки
				@IsShowDouble = null, -- показывать или нет двойников
				@Person_IsUnknown = :Person_IsUnknown	-- признак Личность неизвестна
			select @DT_id as DoubleType_id
		";
		$resp = $this->queryResult($query, $queryParams);

		if (is_array($resp)) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Проверка на дублирование номеров СНИЛС
	 * @param $data
	 */
	function checkSnilsDoubles($data) {
		$queryParams = array();
		$queryParams['Person_SNILS'] = '';
		if (!empty($data['Person_SNILS']))
			$queryParams['Person_SNILS'] = preg_replace('/\-/iu', '', $data['Person_SNILS']);
		$query = "
			SELECT top(1) Person_SurName as PersonSurName_SurName, 
				Person_FirName as PersonFirName_FirName, 
				Person_SecName as PersonSecName_SecName, 
				CONVERT(varchar,Person_BirthDay,104) as PersonBirthDay,
				Person_Snils as PersonSnils_Snils,
				Person_id
			from v_PersonState with(nolock)
			where Person_Snils = :Person_SNILS
				
		";
		if (!empty($data['Person_id'])) {
			$query .= "and Person_id <> :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp = $this->queryResult($query, $queryParams);
		if (is_array($resp)) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	* Проверка на дубли согласно задаче redmine.swan.perm.ru/issues/93041 - Либо ЕНП + Фамилия + Год рождения, либо ЕНП + Имя + Отчество + Год рождения
	*/
	function check_ENP($params){
		$and = "";
		if($params['Check_Type'] == 1){
			$and .= " and PS.Person_SurName = :Person_SurName";
		}
		if($params['Check_Type'] == 2){
			$and .= " and PS.Person_FirName = :Person_FirName";
			$and .= " and PS.Person_SecName = :Person_SecName";
		}
		$query = "
			declare @Server_pid bigint = null
			declare @Person_id bigint = :Person_id
			if @Person_id is not null
				begin
					select @Server_pid = Server_pid
						--@Person_SurName = Person_SurName, @Person_FirName = Person_FirName, @Person_SecName = Person_SecName, @Person_BirthDay = Person_BirthDay,
						--@Polis_Ser = Polis_Ser, @Polis_Num = Polis_Num
					from v_PersonState with (nolock) where Person_id = @Person_id
				end
			select top 1
				PS.* 
			from v_PersonState PS
			where (1=1)
			and PS.Person_EdNum = :Federal_Num
			and YEAR(PS.Person_BirthDay) = YEAR(:Person_BirthDay)
			{$and}
			and (PS.Person_id <> @Person_id or @Person_id is null)
			and ((PS.Server_pid <> 0 and @Server_pid = 0) or @Person_id is null or @Server_pid <> 0)
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0)
				return false;
			else
				return true;
		}
		else
			return true;
	}
	/**
	 * Проверка введенных данных по человеку на двойника (старая)
	 */
	/* function checkPersonDoubles($data) {
	  $filter = "(1 = 1)";
	  $join_str = "";
	  $queryParams = array();

	  $filter .= " and PS.Person_SurName = :Person_SurName";
	  $filter .= " and PS.Person_FirName = :Person_FirName";

	  $queryParams['Person_SurName'] = trim($data['Person_SurName']);
	  $queryParams['Person_FirName'] = trim($data['Person_FirName']);

	  if ( $data['Person_id'] > 0 ) {
	  $filter .= " and PS.Person_id <> :Person_id";
	  $queryParams['Person_id'] = $data['Person_id'];
	  }

	  if ( strlen($data['Person_SecName']) > 0 && $data['Person_SecName'] != '- - -' ) {
	  $filter .= " and ISNULL(PS.Person_SecName, '') = :Person_SecName";
	  $queryParams['Person_SecName'] = $data['Person_SecName'];
	  }
	  else {
	  $filter .= " and NULLIF(PS.Person_SecName, '- - -') is null";
	  }

	  if ( isset($data['Person_BirthDay']) ) {
	  $filter .= " and cast(convert(varchar(10), PS.Person_BirthDay, 112) as datetime) = cast(:Person_BirthDay as datetime)";
	  $queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
	  }

	  if ( isset($data['OMSSprTerr_id']) ) {
	  $filter .= " and Polis.OMSSprTerr_id = :OMSSprTerr_id";
	  $join_str .= "left join Polis with(nolock) on Polis.Polis_id = PS.Polis_id";
	  $queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
	  }

	  $query = "
	  SELECT count(*) as cnt
	  FROM v_PersonState PS with (nolock)
	  " . $join_str . "
	  WHERE " . $filter . "
	  ";
	  $res = $this->db->query($query, $queryParams);

	  if ( is_object($res) )
	  return $res->result('array');
	  else
	  return false;
	  } */

	/**
	 * @param $data
	 * @return bool
	 * Сохранение EvnDate
	 */
	function editPersonEvnDate($data) {
		$person_is_identified = false;
		if (!empty($data['person_is_identified']) && $data['person_is_identified'] == true) {
			$person_is_identified = true;
		}
		// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан, 
		// чтобы лишний раз в сессию не писать, экономим на спичках
		//if ( isset($data['session']['person']) && isset($data['session']['person']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person']['Person_id'] )
		if(!isset($_SESSION))
			session_start();
		unset($_SESSION['person']);

		//if ( isset($data['session']['person_short']) && isset($data['session']['person_short']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person_short']['Person_id'] )
		unset($_SESSION['person_short']);
		session_write_close();
		if (isset($data['Time'])) {
			$data['Date'] = $data['Date'] . ' ' . $data['Time'];
		} else {
			$data['Date'] = $data['Date'] . ' 00:00:00';
		}
		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec xp_PersonTransferDate
				@Server_id = ?,
				@PersonEvn_id = ?,
				@PersonEvn_begDT = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @ErrMsg as ErrMsg
		";

		if ($data['PersonEvn_id'] > 0) {
			$res = $this->db->query($sql, array($data['Server_id'], $data['PersonEvn_id'], $data['Date'], $data['pmUser_id']));
			$this->ValidateInsertQuery($res);
		}

		// если ключевая периодика и не суперадмин и Карелия, то выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
		if (!$person_is_identified && getRegionNick() == 'kareliya') {
			$query = "
				select top 1
					pe.Person_id,
					pe.PersonEvnClass_SysNick,
					ps.Server_pid
				from
					v_PersonEvn pe (nolock)
					inner join v_PersonState ps (nolock) on ps.Person_id = pe.Person_id
				where
					pe.PersonEvn_id = :PersonEvn_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Person_id'])) {
					$BDZaffected = in_array($resp[0]['PersonEvnClass_SysNick'], array('PersonSurName', 'PersonFirName', 'PersonSecName', 'PersonBirthDay', 'PersonSex', 'PersonSocStatus', 'PersonPolisEdNum', 'PersonPolis'));
					if ($BDZaffected && !isSuperAdmin() && $resp[0]['Server_pid'] == 0) {
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)

							exec p_Person_server
								@Server_id = :Server_id,
								@Person_id = :Person_id,
								@BDZ_Guid = :BDZ_Guid,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output

							select @ErrMsg as ErrMsg
						";
						$BDZGUID = (isset($data['BDZ_Guid']))?$data['BDZ_Guid']:null;
						//echo getDebugSQL($sql, array('BDZ_Guid' =>$BDZGUID ,"Person_id" => $resp[0]['Person_id'], "Server_id" => $data['session']['server_id'], "pmUser_id" => $data['pmUser_id']));
						$res = $this->db->query($sql, array('BDZ_Guid' =>$BDZGUID ,"Person_id" => $resp[0]['Person_id'], "Server_id" => $data['session']['server_id'], "pmUser_id" => $data['pmUser_id']));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Проверка документа
	 */
	function validateDocument($data) {
		$params = array(
			'DocumentType_id' => $data['DocumentType_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => !empty($data['PersonEvn_id'])?$data['PersonEvn_id']:null,
			'PersonEvn_insDT' => !empty($data['PersonEvn_insDT'])?$data['PersonEvn_insDT']:'2000-01-01',
		);

		if (empty($params['DocumentType_id'])) {
			return array(array('success' => true));
		}

		if (
			($this->regionNick == 'vologda' || $this->fromApi)
			&& !in_array($data['DocumentType_id'], array(3,9,17,19,22))
			&& !empty($data['Document_begDate'])
		) {
			$Person_Birthday = $this->getFirstResultFromQuery("
				select top 1 convert(varchar(10), Person_Birthday, 120) as Person_Birthday
				from v_PersonState with (nolock)
				where Person_id = :Person_id  
			", $data);

			if ( $Person_Birthday !== false && !empty($Person_Birthday) && getCurrentAge($Person_Birthday, $data['Document_begDate']) < 14 ) {
				return $this->createError('163758', 'Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.');
			}
		}

		$query = "
			declare @DocumentType_id bigint = :DocumentType_id
			declare @Person_id bigint = :Person_id
			declare @PersonEvn_id bigint = :PersonEvn_id
			declare @dt datetime = :PersonEvn_insDT
			
			if @PersonEvn_id is not null
			set @dt = (
				select top 1 PD.PersonDocument_insDT
				from v_PersonDocument PD with(nolock)
				where PD.PersonDocument_id = @PersonEvn_id
			)
			
			select top 1
				PD.PersonEvn_insDT,
				DT.DocumentType_Code,
				nationalityBefore.KLCountry_id as beforeKLCountry_id,
				nationalityAfter.KLCountry_id as afterKLCountry_id
			from
				(
					select
						@Person_id as Person_id,
						@dt as PersonEvn_insDT
				) as PD
				left join v_DocumentType DT with(nolock) on DT.DocumentType_id = @DocumentType_id
				outer apply(
					select top 1
						PNS.NationalityStatus_id,
						PNS.KLCountry_id
					from
						v_PersonNationalityStatus PNS with(nolock)
					where
						PNS.Person_id = PD.Person_id
						and PNS.PersonNationalityStatus_insDT <= PD.PersonEvn_insDT
					order by
						PNS.PersonNationalityStatus_insDT desc
				) nationalityBefore
				outer apply(
					select top 1
						PNS.NationalityStatus_id,
						PNS.KLCountry_id
					from
						v_PersonNationalityStatus PNS with(nolock)
					where
						PNS.Person_id = PD.Person_id
						and PNS.PersonNationalityStatus_insDT >= PD.PersonEvn_insDT
						and PNS.NationalityStatus_id <> nationalityBefore.NationalityStatus_id
					order by
						PNS.PersonNationalityStatus_insDT desc
				) nationalityAfter
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получени данных гражданства, период которых пересекаются с периодом документа.');
		}

		if ($resp['DocumentType_Code'] == 22) {
			if (!empty($resp['beforeKLCountry_id']) || !empty($resp['afterKLCountry_id'])) {
				if ($data['type'] == 'upd') {
					return $this->createError('','В заданном периоде указано Гражданство. Гражданство должно быть пустым. Удалите соответствующую периодику или измените даты начала.');
				} else if ($data['type'] == 'ins') {
					//добавить пустую периодику гражданства
					$this->exceptionOnValidation = true;
					try {
						$resp = $this->saveAttributeOnDate(array(
							'Person_id' => $data['Person_id'],
							'PersonEvnClass_id' => 23,
							'EvnType' => 'NationalityStatus',
							'Date' => $resp['PersonEvn_insDT']->format('d.m.Y'),
							'Time' => $resp['PersonEvn_insDT']->format('H:i'),
							'pmUser_id' => $data['pmUser_id'],
							'Server_id' => $data['Server_id'],
						));
					} catch(Exception $e) {
						$resp = $this->createError($e->getCode(), $e->getMessage());
					}
					$this->exceptionOnValidation = false;
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Проверка гражданства
	 */
	function validateNationalityStatus($data) {
		$params = array(
			'KLCountry_id' => $data['KLCountry_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => !empty($data['PersonEvn_id'])?$data['PersonEvn_id']:null,
			'PersonEvn_insDT' => !empty($data['PersonEvn_insDT'])?$data['PersonEvn_insDT']:'2000-01-01',
		);

		if (empty($params['KLCountry_id'])) {
			return array(array('success' => true));
		}

		$query = "
			declare @Person_id bigint = :Person_id
			declare @PersonEvn_id bigint = :PersonEvn_id
			declare @dt datetime = :PersonEvn_insDT
			
			if @PersonEvn_id is not null
			set @dt = (
				select top 1 PNS.PersonNationalityStatus_insDT
				from v_PersonNationalityStatus PNS with(nolock)
				where PNS.PersonNationalityStatus_id = @PersonEvn_id
			)
			
			select top 1
				documentBefore.DocumentType_Code as beforeDocumentType_Code,
				documentAfter.DocumentType_Code as afterDocumentType_Code
			from
				(
					select
						@Person_id as Person_id,
						@dt as PersonEvn_insDT 
				) as PNS
				outer apply(
					select top 1
						PD.Document_id,
						DT.DocumentType_Code
					from
						v_PersonDocument PD with(nolock)
						left join v_DocumentType DT with(nolock) on DT.DocumentType_id = PD.DocumentType_id
					where
						PD.Person_id = PNS.Person_id
						and isnull(PD.Document_begDate, PD.PersonDocument_insDT) <= PNS.PersonEvn_insDT
					order by
						isnull(PD.Document_begDate, PD.PersonDocument_insDT) desc
				) documentBefore
				outer apply(
					select top 1
						PD.Document_id,
						DT.DocumentType_Code
					from
						v_PersonDocument PD with(nolock)
						left join v_DocumentType DT with(nolock) on DT.DocumentType_id = PD.DocumentType_id
					where
						PD.Person_id = PNS.Person_id
						and isnull(PD.Document_begDate, PD.PersonDocument_insDT) >= PNS.PersonEvn_insDT
						and PD.Document_id <> documentBefore.Document_id
					order by
						isnull(PD.Document_begDate, PD.PersonDocument_insDT) asc
				) documentAfter
		";

		$resp = $this->getFirstRowFromQuery($query, $params, true);
		if ($resp === false) {
			return $this->createError('','Ошибка при получени данных документов, период которых пересекаются с периодом гражданства.');
		}
		else if ( is_array($resp) && count($resp) > 0 ) {
			if ($resp['beforeDocumentType_Code'] == 22 || $resp['afterDocumentType_Code'] == 22) {
				return $this->createError('','В заданном периоде указан документ лица без гражданства. Гражданство должно быть пустым.');
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Проверка введенных данных по человеку на двойника по полису
	 */
	function checkPersonPolisDoubles($data) {
		$filter = "";
		$join_str = "";
		$queryParams = array();

		if (isset($data['Polis_Ser']) && mb_strlen($data['Polis_Ser']) > 0 && isset($data['Polis_Num']) && mb_strlen($data['Polis_Num']) > 0) {
			$filter .= " and PS.Polis_Ser = :Polis_Ser";
			$filter .= " and PS.Polis_Num = :Polis_Num";

			$queryParams['Polis_Ser'] = trim($data['Polis_Ser']);
			$queryParams['Polis_Num'] = trim($data['Polis_Num']);
		} else {
			return array(array('cnt' => 0));
		}

		if ($data['Person_id'] > 0) {
			$filter .= " and Person_id <> :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if (isset($data['OMSSprTerr_id'])) {
			$filter .= " and Polis.OMSSprTerr_id = :OMSSprTerr_id";
			$join_str .= "left join Polis with(nolock) on Polis.Polis_id = PS.Polis_id";
			$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
		}

		$query = "
			SELECT
				top 1
				count(*) as cnt,
				PS.Person_id,
				PS.Server_id
			FROM v_PersonState PS with (nolock)
				" . $join_str . "
			WHERE (1 = 1) " . $filter . "
			GROUP BY PS.Server_id, PS.Person_id
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);

		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Запрос в форме поиска человека c использованием функции модификации запроса с плейсхолдерами
	 */
	function getPersonSearchGrid($data) {
		$join = "";
		$queryParams = array();
		$filters = array();
		$orderFirst = '';
		$extraSelect = '';
		$includePerson_ids = '';
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		// проверяем не идет ли поиск по типу регистра
		if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] > 0 && isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id'] > 0) {
			$queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
			$queryParams['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];

			$query = "
				select
					PersonRegisterType_SysNick
				from
					v_PersonRegisterType with(nolock)
				where
					PersonRegisterType_id = :PersonRegisterType_id;
			";
			$person_register_nick = $this->getFirstResultFromQuery($query, $queryParams);

            $is_common = (mb_strpos($person_register_nick, 'common') !== false); //флаг поиска как для общерапевтической группы

            if (getRegionNick() == 'ufa' && in_array($person_register_nick, array('diab_fl', 'diab_rl', 'orphan'))) {
                $is_common = true;
            }

			if (!$is_common) { //если морбус не является общетерапевтическим
				$filters[] = "
					ps.Person_id in (
						select
							Person_id
						from
							v_PersonRegister with (nolock)
						where
							(
								PersonRegister_disDate is null or
								PersonRegister_disDate > (select DrugRequestPeriod_begDate from v_DrugRequestPeriod with(nolock) where DrugRequestPeriod_id = :DrugRequestPeriod_id)
							)
							and PersonRegisterType_id = :PersonRegisterType_id
					)
				";
			} else {
                switch ($person_register_nick) {
                    case 'common_fl': //ОНЛС: общетерапевтическая группа
                    case 'diab_fl': //Диабет (ОНЛП)
                        $filters[] = "fedl.Person_id is not null"; //только федеральные льготники
                        break;
                    case 'common_rl': //РЛО: общетерапевтическая группа
                    case 'diab_rl': //Диабет (РЛО)
                        $filters[] = "regl.OwnLpu is not null"; //только региональные льготники
                        break;
                    case 'orphan': //Орфанное
                    case 'common': //Общетерапевтическая группа
                        $filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
                        break;
                }
			}
		}

		// добавляем выбранных двойников к ответу
		if ( !empty($data['Double_ids']) ) {
			$arr = json_decode($data['Double_ids']);
			$err = false;
			if (is_array($arr)) {
				foreach ($arr as $item) {
					if (!is_integer(0 + $item) ) {
						$err = true;
						break;
					}
				}
			} else {
				$err = true;
			}
			
			if (!$err && count($arr) > 0) {
				$Person_idsStr = implode(', ', $arr);
				$includePerson_ids = " or ps.Person_id in ({$Person_idsStr}) ";
			}
		}

		// отображение только женщин
		if (mb_strtolower($data['searchMode']) == 'women_only') {
			$filters[] = "ps.Sex_id = 2";
			$data['searchMode'] = 'all';
		}
		else if (mb_strtolower($data['searchMode']) == 'men_only') {
			$filters[] = "ps.Sex_id = 1";
			$data['searchMode'] = 'all';
		}

		// отображение только зашифрованных ВИЧ-инфицированных
		if (mb_strtolower($data['searchMode']) == 'encryponly') {
			$filters[] = "ps.Person_IsEncrypHIV = 2";
			$data['searchMode'] = 'all';
		}

		// если ищем по ДД, то добавляем еще один inner join c PersonDopDisp
		if (mb_strtolower($data['searchMode']) == 'dd') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join PersonDopDisp pdd with(nolock) on pdd.Person_id=ps.Person_id and pdd.Lpu_id = :Lpu_id and pdd.PersonDopDisp_Year = :Year ";
			$filters[] = "pdd.PersonDopDisp_Year = :Year";
			$data['searchMode'] = 'all';
		}

		// только не включенные в регистр ФМБА
		if (mb_strtolower($data['searchMode']) == 'fmba') {
			$filters[] = "
				not exists (
				select top 1 * from v_PersonRegister PerReg with (nolock)
				left join v_PersonRegisterType PerRegT with (nolock) on PerRegT.PersonRegisterType_id = PerReg.PersonRegisterType_id 
				where PerReg.Person_id = ps.Person_id and PerRegT.PersonRegisterType_SysNick = 'fmba'
				)
			";
			$data['searchMode'] = 'all';
		}

		// поиск для регистра главных внештатных специалистов
		if (mb_strtolower($data['searchMode']) == 'hms') {

			if ( $this->getRegionNick() == 'kz' ) {
				$doctorCodes = "(2, 3, 5, 6, 104, 105, 109, 171, 172, 173, 178, 10008, 10209, 10214, 10227, 10228, 10229)";
			}
			else {
				$doctorCodes = "(6, 48, 111, 216, 262, 263, 264, 287, 10002, 10236, 10240)";
			}
			$lpuFilter = '';
			$extraSelect .= ',mw.MedWorker_id';
			$join .= "  inner join persis.v_MedWorker mw with (nolock) on mw.Person_id = ps.Person_id 
						outer apply (
							select top 1 msf.MedStaffFact_id
							from v_MedStaffFact msf with (nolock)
							left join persis.Post p with (nolock) on p.id = msf.Post_id
							where 
								msf.Person_id = ps.Person_id 
								and msf.PostOccupationType_id = 1 
								and (msf.PostKind_id = 1 or p.code in {$doctorCodes})
						) MSFp
			";
			$filters[] = "MSFp.MedStaffFact_id is not null";
			$data['searchMode'] = 'all';
		}

		// только прикреплённые
		if (mb_strtolower($data['searchMode']) == 'att') {
			$filters[] = "
				pcard.Lpu_id = :Lpu_id
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode'] = 'all';
		}

		// только прикреплённые (основное или служебное)
		if (mb_strtolower($data['searchMode']) == 'att_1_4') {
			$filters[] = "
                exists(
                    select top 1
                        i_pc.PersonCard_id
                    from
                        v_PersonCard_all i_pc with (nolock)
                    where
                        i_pc.Lpu_id = :Lpu_id and
                        (:LpuRegion_id is null or i_pc.LpuRegion_id = :LpuRegion_id) and
                        i_pc.Person_id = ps.Person_id and
                        i_pc.LpuAttachType_id in (1, 4) and
                        (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @curDT) and
						(i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @curDT)
                )
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
            $queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
            $data['searchMode'] = 'all';
        } else if (!empty($data['LpuRegion_id'])) {
            $filters[] = "
                exists(
                    select top 1
                        i_pc.PersonCard_id
                    from
                        v_PersonCard_all i_pc with (nolock)
                    where
                        i_pc.Person_id = ps.Person_id and
                        i_pc.LpuRegion_id = :LpuRegion_id and
                        (i_pc.PersonCard_begDate is null or i_pc.PersonCard_begDate <= @curDT) and
						(i_pc.PersonCard_endDate is null or i_pc.PersonCard_endDate >= @curDT)
                )
			";
            $queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
        }

        // только прикреплённые и в объёме
		if (mb_strtolower($data['searchMode']) == 'att_vol') {
			$allowWithoutAttach = false;
			// проверяем наличие объёма "Без прикрепления"
			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ОН_Б_ПРИК'); -- ОН_Б_ПРИК
				declare @curDate datetime = dbo.tzGetDate();

				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and av.AttributeValue_ValueIdent = :Lpu_id
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
					and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate

			", array(
				'Lpu_id' => $data['Lpu_id']
			));
			if (!empty($resp_vol[0]['AttributeValue_id'])) {
				// Если МО имеет объем открытый объем «ОН_Б_ПРИК», то проверку на прикрепление к разрешенным МО не проводим
				$allowWithoutAttach = true;
			}

			if (!$allowWithoutAttach) {
				$data['VolumeType_id'] = 88; // Мед. осмотры несовершеннолетних в чужой МО
				if (!empty($data['VolumeType_id'])) {
					$filters[] = "
						(
							pcard.Lpu_id = :Lpu_id
							or exists (
								SELECT  TOP 1
									av.AttributeValue_id
								FROM
									v_AttributeVision avis (nolock)
									inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
									inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
									cross apply(
										select top 1
											av2.AttributeValue_ValueIdent
										from
											v_AttributeValue av2 (nolock)
											inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
										where
											av2.AttributeValue_rid = av.AttributeValue_id
											and a2.Attribute_TableName = 'dbo.Lpu'
											and ISNULL(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id -- МО проведения
									) MOFILTER
								WHERE
									avis.AttributeVision_TableName = 'dbo.VolumeType'
									and avis.AttributeVision_TablePKey = :VolumeType_id
									and avis.AttributeVision_IsKeyValue = 2
									and ISNULL(av.AttributeValue_begDate, @curDT) <= @curDT
									and ISNULL(av.AttributeValue_endDate, @curDT) >= @curDT
									and av.AttributeValue_ValueIdent = pcard.Lpu_id -- МО прикрепления
							)
						)
					";
					$queryParams['VolumeType_id'] = $data['VolumeType_id'];
				} else {
					$filters[] = "
						pcard.Lpu_id = :Lpu_id
					";
				}
			}

			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode'] = 'all';
		}

		// только прикреплённые или старше 3 лет
		if (mb_strtolower($data['searchMode']) == 'attbefore3') {
			$filters[] = "
				(pcard.Lpu_id = :Lpu_id or (dbo.Age2(ps.Person_BirthDay, @curDT) >= 3))
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode'] = 'all';
		}

		if ((!empty($data['showAll']) && $data['showAll'] == 1) || in_array($data['searchMode'], ['palliat'])) {
			$filters[] .= "ps.Person_deadDT is null";
		}

		if (!empty($data['isNotDead']) && $data['isNotDead'] == 2) {
			$this->load->model("Options_model", "Options_model");
			$limit_days_after_death_to_create_cal = $this->Options_model->getOptionsGlobals($data,'limit_days_after_death_to_create_call');
			$daysByDeath = $limit_days_after_death_to_create_cal ? $limit_days_after_death_to_create_cal : 0;
			$filters[] = "((ps.Person_deadDT <= @curDT AND DATEDIFF(day,cast( ps.Person_deadDT as datetime), @curDT) <= ".$daysByDeath." ) OR ps.Person_deadDT is null)";
		}
		// дети младше 6 лет
		if (mb_strtolower($data['searchMode']) == 'dt6') {
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, @curDT) < 6)
				and pcard.Lpu_id = :Lpu_id
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$data['searchMode'] = 'all';
		}

		// только старше 14 лет и не умершие
		if (mb_strtolower($data['searchMode']) == 'older14notdead') {
			$filters[] = "
				((dbo.Age2(ps.Person_BirthDay, @curDT) >= 14)
				and
				(ps.Person_deadDT is null) and (ps.Person_IsDead is null))
			";
			$data['searchMode'] = 'all';
		}

		// если ищем по ДД 14, то добавляем фильтр по дате рождения
		if (mb_strtolower($data['searchMode']) == 'dt14') {
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, @curDT) between 13 and 15)
			";
			$data['searchMode'] = 'all';
		}

		// старше 60 лет
		if (mb_strtolower($data['searchMode']) == 'geriatrics') {
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, @curDT) >= 60)
			";

			if ( !isSuperAdmin() && !havingGroup('GeriatryRegistryFullAccess') ) {
				$filters[] = "
					pcard.Lpu_id = :Lpu_id
				";
				$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			}

			$data['searchMode'] = 'all';
		}

		// если ищем по регистру детей-сирот, то добавляем еще один inner join c PersonDopDisp
		if (mb_strtolower($data['searchMode']) == 'ddorp') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join v_persondisporp ddorp (nolock) on ddorp.Person_id=ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id <= 7";
			$filters[] = "ddorp.PersonDispOrp_Year = :Year";
			$filters[] = "ddorp.CategoryChildType_id <= 7";
			$data['searchMode'] = 'all';

			/*if ($data['session']['region']['nick'] == 'buryatiya') {
				// только прикреплённые
				$filters[] = "
					pcard.Lpu_id = :Lpu_id
				";
				$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			}*/
		}

		// если ищем по открытым КВС
		if (mb_strtolower($data['searchMode']) == 'hasopenevnps') {
			$filters[] = "
				exists(
					select top 1 es.EvnSection_id from v_EvnSection es (nolock) where es.Lpu_id = :Lpu_id and es.Person_id = ps.Person_id and es.LeaveType_id is null and ISNULL(es.EvnSection_IsPriem, 1) = 1
				)
			";
			$data['searchMode'] = 'all';
		}

		// если ищем по картам первого этапа детей-сирот, то добавляем еще один inner join c EvnPLDispOrp
		if (mb_strtolower($data['searchMode']) == 'ddorpsec') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join v_EvnPLDispOrp epldorp (nolock) on epldorp.Person_id=ps.Person_id and epldorp.Lpu_id = :Lpu_id and epldorp.EvnPLDispOrp_IsTwoStage = 2 and epldorp.EvnPLDispOrp_IsFinish = 2 and epldorp.DispClass_id IN (3,7) and not exists(
				select top 1 EvnPLDispOrp_id from v_EvnPLDispOrp epldorpsec (nolock) where epldorpsec.EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id
			)";
			$filters[] = "epldorp.EvnPLDispOrp_IsTwoStage = 2";
			$filters[] = "epldorp.EvnPLDispOrp_IsFinish = 2";
			$filters[] = "epldorp.DispClass_id IN (3,7)";
			$data['searchMode'] = 'all';
		}

		// периодический осмотр
		if (mb_strtolower($data['searchMode']) == 'ddorpperiod') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join v_persondisporp ddorp with(nolock) on ddorp.Person_id=ps.Person_id and ddorp.Lpu_id = :Lpu_id and ddorp.PersonDispOrp_Year = :Year and ddorp.CategoryChildType_id = 8";
			$filters[] = "ddorp.PersonDispOrp_Year = :Year";
			$filters[] = "ddorp.CategoryChildType_id = 8";
			$data['searchMode'] = 'all';
		}

		// ДВН 2
		if (mb_strtolower($data['searchMode']) == 'dddispclass2') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');

			// У человека нет карты ДВН 1 этап в году, указанном в поле «Год» на форме «Диспансеризация взрослого населения – 2 этап: Поиск»
			// У человека нет карты ДВН 2 этап в году, указанном в поле «Год» на форме «Диспансеризация взрослого населения – 2 этап: Поиск»
			$filters[] = "not exists(
				select top 1
					EvnPLDispDop13_id
				from
					v_EvnPLDispDop13 (nolock)
				where
					Person_id = ps.Person_id
					and YEAR(EvnPLDispDop13_consDT) = :Year
			)";
			// Для человека выполняются требования прохождения ДВН 1 этап в году, указанном в поле «Год» на форме «Диспансеризация взрослого населения – 2 этап: Поиск» (требования аналогичны тем, которые действуют в «Диспансеризация взрослого населения – 1 этап: Поиск»)
			$this->load->model('EvnPLDispDop13_model');

			$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();
			$maxage = 999;
			$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList($queryParams['Year'] . '-01-01');

			if ( !empty($dateX) && $dateX <= date('Y-m-d') ) {
				$add_filter = "
					dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') >= 40
					or (
						dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') >= 18
						and ((dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') - 18) % 3 = 0)
					)
				";
			}
			else {
				$add_filter = "
					(dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') - 21) % 3 = 0)
				";
			}

			if (in_array(getRegionNick(), array('ufa', 'ekb', 'kareliya', 'penza', 'astra'))) {
				$add_filter .= "
					or exists (select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = PS.Person_id)
				";
			}

			if ( count($personPrivilegeCodeList) > 0 ) {
				$add_filter .= "
					or (
						(dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') BETWEEN 18 AND {$maxage})
						and exists (
							select top 1 pp.PersonPrivilege_id
							from v_PersonPrivilege pp (nolock)
								inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
							where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
								and pp.Person_id = PS.Person_id
								and pp.PersonPrivilege_begDate <= cast(:Year as varchar) + '-12-31'
								and (pp.PersonPrivilege_endDate > cast(:Year as varchar) + '-12-31' or pp.PersonPrivilege_endDate is null)
						)
					) -- refs #23044
				";
			}

			$filters[] = "
				(
					{$add_filter}
				)
				and dbo.Age2(PS.Person_BirthDay, cast(:Year as varchar) + '-12-31') <= {$maxage}
			";

			$data['searchMode'] = 'all';
		}

		// если ищем по картам первого этапа профосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (mb_strtolower($data['searchMode']) == 'evnpldtipro') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join v_EvnPLDispTeenInspection epldti (nolock) on epldti.Person_id=ps.Person_id and epldti.Lpu_id = :Lpu_id and epldti.EvnPLDispTeenInspection_IsTwoStage = 2 and epldti.EvnPLDispTeenInspection_IsFinish = 2 and epldti.DispClass_id = 10 and not exists(
				select top 1 EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection epldtisec (nolock) where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id
			)";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsTwoStage = 2";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsFinish = 2";
			$filters[] = "epldti.DispClass_id = 10";
			$data['searchMode'] = 'all';
		}

		// если ищем по картам первого этапа предвосмотра несовершеннолетних, то добавляем еще один inner join c EvnPLDispTeenInspection
		if (mb_strtolower($data['searchMode']) == 'evnpldtipre') {
			$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$join .= " inner join v_EvnPLDispTeenInspection epldti (nolock) on epldti.Person_id=ps.Person_id and epldti.Lpu_id = :Lpu_id and epldti.EvnPLDispTeenInspection_IsTwoStage = 2 and epldti.EvnPLDispTeenInspection_IsFinish = 2 and epldti.DispClass_id = 9 and not exists(
				select top 1 EvnPLDispTeenInspection_id from v_EvnPLDispTeenInspection epldtisec (nolock) where epldtisec.EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id
			)";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsTwoStage = 2";
			$filters[] = "epldti.EvnPLDispTeenInspection_IsFinish = 2";
			$filters[] = "epldti.DispClass_id = 9";
			$data['searchMode'] = 'all';
		}

		// если ищем для скринингового исследования, то возраст пациента на конец выбранного года должен соответствовать
		if (mb_strtolower($data['searchMode']) == 'evnpldispscreen') {
			/*$queryParams['Year'] = ( isset($data['Year']) && (int) $data['Year'] > 1970 ) ? (int) $data['Year'] : date('Y');
			$filters[] = "
				(dbo.Age2(ps.Person_BirthDay, cast(:Year as varchar) + '-12-31') IN (30,34,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70))
			";*/
			$data['searchMode'] = 'all';
		}

		if (mb_strtolower($data['searchMode']) == 'wow') {
			$join .= " inner join PersonPrivilegeWOW PPW with(nolock) on PPW.Person_id = ps.Person_id";
			$data['searchMode'] = 'all';
		}

		if ((mb_strtolower($data['searchMode']) == 'attachrecipients') &&
				(!isMinZdrav()) &&
				(!isOnko()) &&
				(!isRA()) &&
				(!isPsih()) &&
				(!isOnkoGem()) &&
				(!isGuvd())
		) {
			// только по льготникам и прикрепленным 
			$data['searchMode'] = 'all';
			$filters[] = "Lpu.Lpu_id = :Lpu_id";
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
			$filterscard = "pc.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		} else {
			$filterscard = "(1=1)";
		}

		if ((mb_strtolower($data['searchMode']) == 'withlgotonly') &&
				(!isMinZdrav()) &&
				(!isOnko()) &&
				(!isRA()) &&
				(!isPsih()) &&
				(!isOnkoGem()) &&
				(!isGuvd())
		) {
			// только по льготникам 
			$data['searchMode'] = 'all';
			$filters[] = "(fedl.Person_id is not null or regl.OwnLpu is not null)";
		}
		//Поиск в арм смо. Для Перми - последние 3 года.
		if ((mb_strtolower($data['searchMode']) == 'smo' or mb_strtolower($data['searchMode']) == 'smo3') and !empty($data['session']['org_id'])) {
			//#PROMEDWEB-5677 информация про СМО может быть не СМО id, а Org_id
			$filtertmp = "(pls.OrgSmo_id = :OrgSmo_id or vos.Org_id = :OrgSmo_id)";
			$queryParams['OrgSmo_id'] = $data['session']['org_id'];
			
			if(mb_strtolower($data['searchMode']) == 'smo3') $filtertmp.=" and (pls.Polis_endDate >= dateadd(year, -3, getdate()) or pls.Polis_endDate is null) and pls.Polis_begDate <= getdate()";
			$filters[] = "(".$filtertmp.")";
			
			$data['searchMode'] = 'all';
		}
		if (mb_strtolower($data['searchMode']) == 'erssnils') {
			$filters[] = "ps.Person_Snils is not null";
			$data['searchMode'] = 'ers';
		}
		if (mb_strtolower($data['searchMode']) == 'ers') {
			$filters[] = "ps.Sex_id = 2";
			$filters[] = "dbo.Age2(ps.Person_BirthDay, @curDT) >= 14";
			$data['searchMode'] = 'all';
		}

		if (isset($data['ParentARM'])
				&& ( $data['ParentARM'] == 'smpdispatchcall' || $data['ParentARM'] == 'smpadmin' || $data['ParentARM'] == 'smpdispatchdirect' )
				&& !empty($data['PersonAge_AgeFrom']) && empty($data['PersonAge_AgeTo'])
		) {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay, @curDT) between :PersonAge_AgeFrom - 5 and :PersonAge_AgeFrom + 5)";
			$extraSelect .= ',ABS(:PersonAge_AgeFrom - (datediff(year,ps.Person_BirthDay,@curDT)
				+ case when month(ps.Person_BirthDay)>month(@curDT)
				or (month(ps.Person_BirthDay)=month(@curDT) and day(ps.Person_BirthDay)>day(@curDT))
				then -1 else 0 end)) as YearDifference';
			$orderFirst .= 'YearDifference ASC,';
		} else {
			if (!empty($data['PersonAge_AgeFrom'])) {
				$filters[] = "(dbo.Age2(ps.Person_BirthDay, @curDT) >= :PersonAge_AgeFrom)";
			}

			if (!empty($data['PersonAge_AgeTo'])) {
				$filters[] = "(dbo.Age2(ps.Person_BirthDay, @curDT) <= :PersonAge_AgeTo)";
			}
		}

		if (!empty($data['PersonBirthYearFrom'])) {
			$filters[] = "(year(ps.Person_BirthDay) >= :PersonBirthYearFrom)";
			$queryParams['PersonBirthYearFrom'] = $data['PersonBirthYearFrom'];
		}

		if (!empty($data['PersonBirthYearTo'])) {
			$filters[] = "(year(ps.Person_BirthDay) <= :PersonBirthYearTo)";
			$queryParams['PersonBirthYearTo'] = $data['PersonBirthYearTo'];
		}

		$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		$queryParams['PersonAge_AgeTo'] = $data['PersonAge_AgeTo'];

		if (!empty($data['personBirtDayFrom'])) {
			$filters[] = "(ps.Person_BirthDay >= :personBirtDayFrom)";
			$queryParams['personBirtDayFrom'] = $data['personBirtDayFrom'];
		}

		if (!empty($data['personBirtDayTo'])) {
			$filters[] = "(ps.Person_BirthDay <= :personBirtDayTo)";
			$queryParams['personBirtDayTo'] = $data['personBirtDayTo'];
		}


		if (!empty($data['EvnUdost_Ser']) || !empty($data['EvnUdost_Num'])) {
			$join .= " inner join v_EvnUdost eu with(nolock) on eu.Person_id = ps.Person_id and EvnUdost_disDate is null";
			if (!empty($data['EvnUdost_Ser'])) {
				$join .= " and eu.EvnUdost_Ser = :EvnUdost_Ser ";
				$filters[] = "eu.EvnUdost_Ser = :EvnUdost_Ser";
				$queryParams['EvnUdost_Ser'] = $data['EvnUdost_Ser'];
			}
			if (!empty($data['EvnUdost_Num'])) {
				$join .= " and eu.EvnUdost_Num = :EvnUdost_Num ";
				$filters[] = "eu.EvnUdost_Num = :EvnUdost_Num";
				$queryParams['EvnUdost_Num'] = $data['EvnUdost_Num'];
			}
		}

		if (!empty($data['PersonCard_id'])) {
			$filters[] = "exists (select top 1 PersonCard_id from v_PersonCard_all with (nolock) where Person_id = PS.Person_id and PersonCard_id = :PersonCard_id)";
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}

		if (!empty($data['PersonCard_Code'])) {
			$filters[] = "exists (select top 1 PersonCard_id from v_PersonCard_all with (nolock) where Person_id = PS.Person_id and PersonCard_Code = :PersonCard_Code and (PersonCard_endDate is null or PersonCard_endDate >= @curDT) and Lpu_id = :Lpu_id)";
			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}

		if (!empty($data['EvnPS_NumCard']) && $data['EvnPS_NumCard'] != '') {
			$join .= " inner join v_EvnPS eps1 with(nolock) on eps1.Person_id=ps.Person_id and rtrim(eps1.EvnPS_NumCard) = :EvnPS_NumCard and eps1.Lpu_id = :Lpu_id ";
			$filters[] = "rtrim(eps1.EvnPS_NumCard) = :EvnPS_NumCard";
			$queryParams['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
		}
		if (!empty($data['Person_id'])) {
			$filters[] = "ps.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$extraSelect .= "
			,'edit' as accessType
		";
		$isSearchByEncryp = false;
		$select_person_data = ",ISNULL(ps.Person_SurName, '') as PersonSurName_SurName
			,ISNULL(ps.Person_FirName, '') as PersonFirName_FirName
			,ISNULL(ps.Person_SecName, '') as PersonSecName_SecName
			,pls.Polis_Ser
			,pls.PolisFormType_id
			,pls.OrgSMO_id
			,pls.OMSSprTerr_id
			,convert(varchar(10), pls.Polis_endDate, 104) as Polis_endDate
			,ps.Person_Snils
			,case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end as Polis_Num
			,ps.Document_Ser
			,ps.Document_Num
			,ps.Person_edNum as Polis_EdNum
			,dbo.Age(ps.Person_BirthDay, @curDT) as Person_Age
			,convert(varchar,cast(ps.Person_BirthDay as datetime),104) as PersonBirthDay_BirthDay
			,convert(varchar,cast(ps.Person_deadDT as datetime),104) as Person_deadDT
			,ps.Sex_id
			,lpu.Lpu_Nick as Lpu_Nick
			,lpu.Lpu_id as CmpLpu_id
			,pcard1.PersonCardState_Code as PersonCard_Code
		";
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['PersonSurName_SurName']);
				if ($isSearchByEncryp) {
					// нельзя ни редактировать, ни просмотреть
					$extraSelect .= "
						,'list' as accessType
					";
					$select_person_data = ",peh.PersonEncrypHIV_Encryp as PersonSurName_SurName
						,'' as PersonFirName_FirName
						,'' as PersonSecName_SecName
						,'' as Polis_Ser
						,'' as Polis_Num
						,'' as PolisFormType_id
						,'' as OrgSMO_id
						,'' as OMSSprTerr_id
						,null as Polis_endDate
						,'' as Document_Ser
						,'' as Document_Num
						,'' as Polis_EdNum
						,null as Person_Age
						,null as PersonBirthDay_BirthDay
						,null as Person_deadDT
						,null as Sex_id
						,'' as Lpu_Nick
						,null as CmpLpu_id
						,'' as PersonCard_Code
					";
				}
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters[] = "not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
				)";
			}
		}
		
		if (!isSuperAdmin() && strlen($data['PersonSurName_SurName'])>0)  {
			$data['PersonSurName_SurName'] = trim(str_replace(array('%','_'), '', $data['PersonSurName_SurName']));
			if (strlen($data['PersonSurName_SurName'])==0) {
				DieWithError("Поле Фамилия обязательно для заполнения (использование знаков % и _  недопустимо).");
			}
		}
		
		if (!empty($data['PersonSurName_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id and peh.PersonEncrypHIV_Encryp like :Person_SurName";
				$filters[] = "1=1";//чтобы не выходила ошибка "Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров."
				$filters[] = "peh.PersonEncrypHIV_Encryp like :Person_SurName";
			} else {
				$filters[] = "ps.Person_SurNameR LIKE :Person_SurName + '%'";
			}
			$queryParams['Person_SurName'] = $this->prepareSearchSymbol($data['PersonSurName_SurName']);
		}
		if (!empty($data['PersonFirName_FirName'])) {
			$filters[] = "ps.Person_FirNameR LIKE :Person_FirName + '%'";
			$queryParams['Person_FirName'] =  $this->prepareSearchSymbol($data['PersonFirName_FirName']);
		}
		if (!empty($data['PersonSecName_SecName'])) {
			$filters[] = "ps.Person_SecNameR LIKE :Person_SecName + '%'";
			$queryParams['Person_SecName'] = $this->prepareSearchSymbol($data['PersonSecName_SecName']);
		}
		/*
		if (!empty($data['PersonSurName_SurName'])) {
			$filters[] = "ps.Person_SurNameR LIKE '".trim(preg_replace('/ё/iu', 'е', $data['PersonSurName_SurName'])) . "%'";
		}
		if (!empty($data['PersonFirName_FirName'])) {
			$filters[] = "ps.Person_FirNameR LIKE '".trim(preg_replace('/ё/iu', 'е', $data['PersonFirName_FirName'])) . "%'";
		}
		if (!empty($data['PersonSecName_SecName'])) {
			$filters[] = "ps.Person_SecNameR LIKE '".trim(preg_replace('/ё/iu', 'е', $data['PersonSecName_SecName'])) . "%'";
		}
		*/
		if (!empty($data['PersonBirthDay_BirthDay'])) {
			$filters[] = "ps.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['PersonBirthDay_BirthDay'];
		}

		if (!empty($data['Person_Snils'])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (!empty($data['Person_Inn'])) {
			$filters[] = "ps.Person_Inn = :Person_Inn";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		if (!empty($data['Polis_Ser'])) {
			$filters[] = "ps.Polis_Ser = :Polis_Ser";
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		}

		if (!empty($data['Polis_Num'])) {
			$filters[] = "ps.Polis_Num = :Polis_Num";
			$queryParams['Polis_Num'] = $data['Polis_Num'];
		}
		if (!empty($data['Polis_EdNum'])) {
			$filters[] = "ps.Person_edNum = :Polis_edNum";
			$queryParams['Polis_edNum'] = $data['Polis_EdNum'];
		}

		if (!empty($data['Sex_id'])) {
			$filters[] = "ps.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

        //BOB - 21.03.2017
        if (isset($data['Person_ids']) && !empty($data['Person_ids'])) {
			$filters[] = "ps.Person_id in (".$data['Person_ids'].")";
		}
        //BOB - 21.03.2017

                
                
                
		/**
		 *  2009-04-27 [savage]
		 *  Изменил определение признака "льготник" с учетом принадлежности пользователя к ЛПУ
		 */
		/**
		 *  2009-07-08 [ivp]
		 *  Изменил определение признака "льготник" - федеральный, региональный, региональный не своего ЛПУ
		 *  Добавил определение признака 7ми нозологий
		 */
		// исходный запрос
		// если ищем по соц. карте, то задаем еще один жесткий джоин
		if (isset($data['soc_card_id']) && mb_strlen($data['soc_card_id']) >= 25) {
			$queryParams['SocCardNum'] = mb_substr($data['soc_card_id'], 0, 19);
			$join .= " inner join PersonSocCardNum pscn with(nolock) on ps.PersonSocCardNum_id = pscn.PersonSocCardNum_id and LEFT(pscn.PersonSocCardNum_SocCardNum, 19) = :SocCardNum ";
			$filters[] = "LEFT(pscn.PersonSocCardNum_SocCardNum, 19) = :SocCardNum";
		}

		if (!empty($data['PersonRefuse_IsRefuse']) && !empty($data['DrugRequestPeriod_id']) && !empty($data['PersonRegisterType_id'])) {
			//проверяем морбус проверку нужно осуществлять только по общетерапевтическому морбусу с федеральным финансированием
			$query = "
				select
					ltrim(rtrim(PersonRegisterType_SysNick)) as PersonRegisterType_SysNick
				from
					v_PersonRegisterType with(nolock)
				where
					PersonRegisterType_id = :PersonRegisterType_id;
			";
			$person_register_nick = $this->getFirstResultFromQuery($query, array('PersonRegisterType_id' => $data['PersonRegisterType_id']));
			if (in_array($person_register_nick, array('common', 'common_fl'))) {
				//получаем год из периода заявки
				$query = "
					select
						year(DrugRequestPeriod_begDate) as DrugRequestPeriod_Year
					from
						v_DrugRequestPeriod with(nolock)
					where
						DrugRequestPeriod_id = :DrugRequestPeriod_id;
				";
				$drp_year = $this->getFirstResultFromQuery($query, array('DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']));

				$filters[] = "isnull(drp_refuse.PersonRefuse_IsRefuse, 1) = :PersonRefuse_IsRefuse";
				$queryParams['DrugRequestPeriod_Year'] = $drp_year > 0 ? $drp_year : null;
				$queryParams['PersonRefuse_IsRefuse'] = $data['PersonRefuse_IsRefuse'];
				$join = " left join v_PersonRefuse drp_refuse with (nolock) on drp_refuse.Person_id = ps.Person_id and drp_refuse.PersonRefuse_Year = :DrugRequestPeriod_Year";
			}
		}

		if ( !empty($data['getPersonWorkFields']) && $data['getPersonWorkFields'] == 1 ) {
			$extraSelect .= "
				,O.Org_id as Person_Work_id
				,O.Org_Nick as Person_Work
			";
			$join .= "
				left join v_Job J with (nolock) on ps.Job_id = J.Job_id
				left join v_Org O with (nolock) on O.Org_id = J.Org_id
			";
		}

		$filters[] = "isnull(per.Person_IsUnknown,1) <> 2 ";

		if (count($filters)<=1 && empty($data['EvnPS_NumCard'])) {
			return array('success' =>false,'Error_Msg' => toUtf('Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров.'));
		}
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @curDT, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if(getRegionNick() == 'perm'){
			$isBDZ ="case
					when ps.Server_pid = 0 /*and ps.Person_IsInErz = 2*/ then 'true'
					when ps.Person_IsInErz = 1 then 'blue'
					when ps.Server_pid = 0 and pls.Polis_endDate < cast(@curDT as date) then
						case when ps.Person_deadDT is null then 'yellow' else 'red' end
					when ps.Server_pid = 2 and ps.Person_IsInErz <> 1 then 'false'
				end as [Person_IsBDZ],";
		}
		if(getRegionNick() == 'penza'){
			$isBDZ ="case
					when ps.Person_IsInErz = 1 then 'orange'
					when ps.Person_IsInErz = 2 then 'true'
					else 'false'
				end as [Person_IsBDZ],";
		}
		if (getRegionNick() == 'kz') {
			$isBDZ ="case
		when ps.Person_IsInErz = 1 then 'red'
		when ps.Person_IsInErz = 2 then 'true'
		else 'false'
	end as [Person_IsBDZ],
			case
				when per.Person_IsInFOMS = 1 then 'orange'
				when per.Person_IsInFOMS = 2 then 'true'
				else 'false'
			end as Person_IsInFOMS,";
		}
		$sql = "
			-- variables
			declare @curDT datetime = dbo.tzGetdate();
			-- end variables

			select top 1000
				-- select 
				ps.Person_id,
				ps.Server_id,
				ps.PersonEvn_id,
				ps.Person_IsInErz,
				ps.Person_Phone,
				ps.Person_Inn,
				CASE WHEN ISNULL(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as Person_isOftenCaller,
				CASE WHEN PersonRefuse.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as [Person_IsRefuse],
				CASE WHEN (ps.Person_deadDT is not null) or (ps.Person_IsDead = 2) THEN 'true' ELSE 'false' END as [Person_IsDead],
				CASE WHEN fedl.Person_id is not null then 'true' else 'false' end as [Person_IsFedLgot],
				CASE WHEN regl.OwnLpu = 1 THEN 'true' ELSE CASE WHEN regl.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_IsRegLgot],
				CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_Is7Noz],
				uaddr.Address_Address as UAddress_AddressText,
				paddr.Address_Address as PAddress_AddressText,
				".$isBDZ."
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn,
				CASE WHEN exists (
					select
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Person_id = ps.Person_id
						and LpuAttachType_id = 5
						and PersonCard_endDate >= @curDT
						and CardCloseCause_id is null
				) THEN 'true' ELSE 'false' END as PersonCard_IsDms
				{$select_person_data}
				{$extraSelect}
			-- end select
			from
			-- from
				v_PersonState ps with (nolock)
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = ps.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				{$join}
				left join Person per with(nolock) on per.Person_id = ps.Person_id
				outer apply (
					select top 1 PersonCardState_Code
					from PersonCardState with (nolock)
					where Person_id = ps.Person_id
						and Lpu_id = :Lpu_id
						and LpuAttachType_id = 1
				) pcard1
				left join Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSMO vos with (nolock) on vos.OrgSmo_id = pls.OrgSmo_id
				--Информаия про СМО refs #PROMEDWEB-5677
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard pc with (nolock)
					where
						pc.Person_id = ps.Person_id
						and LpuAttachType_id = 1
						and {$filterscard}
					order by PersonCard_begDate desc
				) as pcard
				LEFT JOIN v_Lpu lpu with (nolock) on pcard.Lpu_id=lpu.Lpu_id
				--LEFT JOIN v_PersonRefuse with (nolock) ON v_PersonRefuse.Person_id = ps.Person_id and v_PersonRefuse.PersonRefuse_IsRefuse = 2 and v_PersonRefuse.PersonRefuse_Year = YEAR(@curDT)
				outer apply (
					select top 1 PersonRefuse_IsRefuse
					from v_PersonRefuse pr with (nolock)
					where
						pr.Person_id = ps.Person_id
						and pr.PersonRefuse_IsRefuse = 2
						and pr.PersonRefuse_Year = YEAR(@curDT)
				) as PersonRefuse
				outer apply (
					select top 1 Person_id
					from v_personprivilege pp with (nolock)
					left join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
						pp.person_id = ps.person_id
						--and pp.privilegetype_id <= 249
						and pt.ReceptFinance_id = 1
						and pp.personprivilege_begdate <= @curDT
						and (IsNull(pp.personprivilege_enddate, @curDT) >= cast(@curDT as date))
				) as fedl
				outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from v_personprivilege pp with (nolock)
					left join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
					where
						pp.person_id = ps.person_id
						and pt.ReceptFinance_id = 2
						and pp.personprivilege_begdate <= @curDT
						and (IsNull(pp.personprivilege_enddate, @curDT) >= cast(@curDT as date))
				) as regl
				outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from [v_PersonDisp] with (nolock)
					where
						Person_id = ps.Person_id
						and (IsNull(PersonDisp_endDate, @curDT+1) > @curDT)
						and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp
				outer apply (
					select top 1 OftenCallers_id
					from v_OftenCallers WITH (nolock)
					where Person_id = ps.Person_id
				) OC
				LEFT JOIN v_Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
				LEFT JOIN v_Address paddr with (nolock) on ps.PAddress_id = paddr.Address_id
			-- end from
			where
			-- where
			(" . Implode(' and ', $filters) . ")
			{$includePerson_ids}
			-- end where
			order by 
			-- order by
				{$orderFirst}
				ps.Person_SurNameR ASC, 
				ps.Person_FirNameR ASC, 
				ps.Person_SecNameR ASC
			-- end order by
		";
		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 * Валидация СНИЛС
	 */
	function verifyPersonSnils(array $data):array {
		// получаем текущие данные по пациенту
		$resp_ps = $this->queryResult("
			select
				Person_id,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				Sex_id,
				Person_BirthDay
			from
				v_PersonState (nolock)
			where
				Person_id = :Person_id
		", [
			'Person_id' => $data['Person_id']
		]);

		if (empty($resp_ps[0]['Person_id'])) {
			return ['Error_Msg' => 'Указанный человек не найден в БД'];
		}

		$this->db->query("update PersonState with (rowlock) set PersonState_IsSnils = :PersonState_IsSnils where Person_id = :Person_id", [
			'Person_id' => $data['Person_id'],
			'PersonState_IsSnils' => null
		]);

		$resp_psq = $this->queryResult("
			select
				PersonSnilsQueue_id
			from
				v_PersonSnilsQueue (nolock)
			where
				Person_id = :Person_id
				and Person_Snils is null -- только те, по которым не получен ответ
		", [
			'Person_id' => $data['Person_id']
		]);

		$this->load->model('PersonSnilsQueue_model');
		foreach($resp_psq as $one_psq) {
			$this->PersonSnilsQueue_model->deletePersonSnilsQueue([
				'PersonSnilsQueue_id' => $one_psq['PersonSnilsQueue_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return $this->PersonSnilsQueue_model->savePersonSnilsQueue([
			'Person_id' => $data['Person_id'],
			'Person_SurName' => $resp_ps[0]['Person_SurName'],
			'Person_FirName' => $resp_ps[0]['Person_FirName'],
			'Person_SecName' => $resp_ps[0]['Person_SecName'],
			'Person_Sex' => $resp_ps[0]['Sex_id'],
			'Person_BirthDay' => $resp_ps[0]['Person_BirthDay'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getAddressByPersonId($data){
		$query="
			Select 
				uaddr.Address_Zip as UAddress_Zip,
				uaddr.KLCountry_id as UKLCountry_id,
				uaddr.KLRGN_id as UKLRGN_id,
				uaddr.KLSubRGN_id as UKLSubRGN_id,
				uaddr.KLCity_id as UKLCity_id,
				uaddr.KLTown_id as UKLTown_id,
				uaddr.KLStreet_id as UKLStreet_id,
				uaddr.Address_House as UAddress_House,
				uaddr.Address_Corpus as UAddress_Corpus,
				uaddr.Address_Flat as UAddress_Flat,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Address as UAddress_Address,
				uaddr.PersonSprTerrDop_id as UPersonSprTerrDop_id,

				paddr.Address_Zip as PAddress_Zip,
				paddr.KLCountry_id as PKLCountry_id,
				paddr.KLRGN_id as PKLRGN_id,
				paddr.KLSubRGN_id as PKLSubRGN_id,
				paddr.KLCity_id as PKLCity_id,
				paddr.KLTown_id as PKLTown_id,
				paddr.KLStreet_id as PKLStreet_id,
				paddr.Address_House as PAddress_House,
				paddr.Address_Corpus as PAddress_Corpus,
				paddr.Address_Flat as PAddress_Flat,
				paddr.Address_Address as PAddress_AddressText,
				paddr.Address_Address as PAddress_Address,
				paddr.PersonSprTerrDop_id as PPersonSprTerrDop_id
				from v_Personstate vper with (nolock)
			left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			left join v_Address paddr with (nolock) on vper.PAddress_id = paddr.Address_id
			where vper.Person_id =  :Person_id
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			$response = $res->result('array');
			return $response;
		}
		else
			return false;
	}
	
	/**
	* Сохранение адреса регистрации
	*/
	function savePersonUAddress($data, $adressType = 'U')
	{
		$params = array(
			'Person_id' 			=> $data['Person_id'],
			'Server_id'				=> $data['Server_id'],
			'PersonEvn_id'			=> $data['PersonEvn_id'],
			'Person'.$adressType.'Address_insDT' 	=> !empty($data['insDT']) ? $data['insDT']:'2000-01-01 00:00:00.000',
			'Person'.$adressType.'address_id' 	=> $data['PersonEvn_id'],
			'KLAreaType_id'			=> null,
			'KLCountry_id'			=> $data['KLCountry_id'],
			'KLRgn_id'				=> $data['KLRgn_id'],
			'KLSubRgn_id'			=> $data['KLSubRgn_id'],
			'KLCity_id'				=> $data['KLCity_id'],
			'KLTown_id'				=> $data['KLTown_id'],
			'KLStreet_id'			=> $data['KLStreet_id'],
			'Address_Zip'			=> $data['Address_Zip'],
			'Address_House'			=> $data['Address_House'],
			'Address_Corpus'		=> $data['Address_Corpus'],
			'Address_Flat'			=> $data['Address_Flat'],
			'PersonSprTerrDop_id'	=> $data['PersonSprTerrDop_id'],
			'Address_Address'		=> $data['Address_Address'],
			'pmUser_id'				=> $data['pmUser_id']
		);
		/*$sql = "
			select
				top 1 PersonEvn_id,
				Server_id,
				UAddress_id
			from
				v_Person_all with (nolock)
			where
				PersonEvnClass_id = 10 and
				PersonEvn_insDT <= (select PersonEvn_insDT from PersonEvn with (nolock) where PersonEvn_id = :PersonEvn_id) and
				Person_id = :Person_id
			order by
				PersonEvn_insDT desc,
				PersonEvn_TimeStamp desc
		";*/
		$sql = "
			select 
				PS.Server_id,
				PUA.PersonUAddress_id,
				PPA.PersonPAddress_id
			from v_PersonState PS (nolock)
			left join v_PersonUAddress PUA (nolock) on PUA.Address_id = PS.UAddress_id
			left join v_PersonPAddress PPA (nolock) on PPA.Address_id = PS.PAddress_id
			where PS.Person_id = :Person_id
		";
		$res = $this->db->query($sql,$params);
		$sel = $res->result('array');
		if(empty($sel[0]['Person'.$adressType.'Address_id'])){
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_Person{$adressType}Address_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@Person{$adressType}Address_insDT = :Person{$adressType}Address_insDT,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_Zip = :Address_Zip,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@PersonSprTerrDop_id = :PersonSprTerrDop_id,
				@Address_Address = :Address_Address,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrCode as Error_Code, @ErrMsg as ErrMsg
			";
		}
		else
		{
			$params['Server_id'] = $sel[0]['Server_id'];
			$params['Person'.$adressType.'address_id'] = $sel[0]['Person'.$adressType.'Address_id'];//$sel[0]['PersonEvn_id'];
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)
								
				exec p_Person{$adressType}Address_upd
				@Person{$adressType}Address_id = :Person{$adressType}address_id,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@KLCountry_id = :KLCountry_id,
				@KLAreaType_id = null,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_Zip = :Address_Zip,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@PersonSprTerrDop_id = :PersonSprTerrDop_id,
				@Address_Address = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrCode as Error_Code, @ErrMsg as ErrMsg
			";
		}
		//echo getDebugSQL($sql, $params);die;
		return $this->queryResult($sql, $params);
		
	}

	/**
	 * Сохранение СНИЛС
	 */
	function savePersonSnils($data) {
		$params = [
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'Person_Snils' => $data['Person_Snils'],
			'pmUser_id' => $data['pmUser_id']
		];

		$sql = "
			declare @ErrCode int
			declare @ErrMsg varchar(400)
								
			exec p_PersonSnils_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@PersonSnils_Snils = :Person_Snils,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
				
			select @ErrCode as Error_Code, @ErrMsg as ErrMsg
		";

		return $this->queryResult($sql, $params);
	}

	/**
	 * Запрос для получения списка истории изменения всех периодик человека
	 */
	function getAllPeriodics($data) {
		$query = "";
		$filter = "";
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		
		$classname = (getRegionNick()!='kz')?"pec.PersonEvnClass_Name":"case when pec.PersonEvnClass_id = 20 then 'ИИН' else pec.PersonEvnClass_Name end as PersonEvnClass_Name";
		
		$sql = "select
			pa.PersonEvn_id,
			pa.Server_id,
			pa.Server_pid,
			pa.Person_id,
			pa.PersonEvnClass_id,
			{$classname},
			convert(varchar, pa.PersonEvn_insDT, 104) + ' ' + convert(varchar(8), pa.PersonEvn_insDT, 114) as PersonEvn_insDT,
			case
				when pa.PersonEvnClass_id = 1 then isnull(pa.Person_SurName, '')
				when pa.PersonEvnClass_id = 2 then isnull(pa.Person_FirName, '')
				when pa.PersonEvnClass_id = 3 then isnull(pa.Person_SecName, '')
				when pa.PersonEvnClass_id = 4 then isnull(convert(varchar, pa.Person_BirthDay, 104), '')
				when pa.PersonEvnClass_id = 5 then isnull(sx.Sex_Name, '')
				when pa.PersonEvnClass_id = 6 then isnull(Person_Snils, '')
				when pa.PersonEvnClass_id = 7 then isnull(ss.SocStatus_Name, '')
				when pa.PersonEvnClass_id = 8 then isnull(rtrim(sprt.OMSSprTerr_Name), '') + ' ' + isnull(rtrim(osmo.OrgSMO_Nick), '') + ' ' + isnull(rtrim(pls.Polis_Ser), '') + ' ' + isnull(rtrim(pls.Polis_Num), '') + isnull( ' Открыт: ' + convert(varchar, pls.Polis_begDate, 104), '') + isnull( ' Закрыт: ' + convert(varchar, pls.Polis_endDate, 104), '')
				when pa.PersonEvnClass_id = 9 then isnull(rtrim(dt.DocumentType_Name), '') + ' ' + isnull(rtrim(doc.Document_Ser), '') + ' ' + isnull(RTRIM(doc.Document_Num), '') + ' ' + isnull(convert(varchar, doc.Document_begDate, 104), '')
				when pa.PersonEvnClass_id = 10 then isnull(rtrim(uaddr.Address_Address), '')
				when pa.PersonEvnClass_id = 11 then isnull(rtrim(paddr.Address_Address), '')
				when pa.PersonEvnClass_id = 12 then isnull(rtrim(jorg.Org_Name), '')
				when pa.PersonEvnClass_id = 15 then isnull(ref.YesNo_Name, '')
				when pa.PersonEvnClass_id = 16 then isnull(rtrim(pa.Person_EdNum), '')
				when pa.PersonEvnClass_id = 18 then isnull(rtrim(pa.PersonPhone_Phone), '')
				when pa.PersonEvnClass_id = 20 then isnull(rtrim(pa.PersonInn_Inn), '')
				when pa.PersonEvnClass_id = 21 then isnull(rtrim(pa.PersonSocCardNum_SocCardNum), '')
				when pa.PersonEvnClass_id = 22 then isnull(fs.FamilyStatus_Name, '')
				when pa.PersonEvnClass_id = 23 then isnull(klc.KLCountry_Code+'. '+klc.KLCountry_Name, '')+isnull(case when ns.NationalityStatus_IsTwoNation = 2 then ', Двойное гражданство (РФ и иностранное государство)' end, '')
				else ''
			end as PersonEvn_Value,
			case
				when pa.PersonEvnClass_id = 8 and pls.BDZ_id is not null then 1
				else 0
			end as PersonEvn_readOnly,
			case
				when pa.PersonEvnClass_id = 8 then pa.Polis_id
				when pa.PersonEvnClass_id = 9 then pa.Document_id
				when pa.PersonEvnClass_id = 10 then pa.UAddress_id
				when pa.PersonEvnClass_id = 11 then pa.PAddress_id
				when pa.PersonEvnClass_id = 12 then pa.Job_id
				when pa.PersonEvnClass_id = 23 then pa.NationalityStatus_id
				else null
			end as PersonEvnObject_id
			{$query}
		from
			v_Person_all as pa with (nolock)
			inner join PersonEvnClass pec with (nolock) on pa.PersonEvnClass_id = pec.PersonEvnClass_id
			inner join v_PersonEvn pe (nolock) on pe.PersonEvn_id = pa.PersonEvn_id
			left join Sex sx with (nolock) on sx.Sex_id = pa.Sex_id
			left join SocStatus ss with (nolock) on ss.SocStatus_id = pa.SocStatus_id
			left join FamilyStatus fs with (nolock) on fs.FamilyStatus_id = pa.FamilyStatus_id
			left join v_Polis pls with (nolock) on pls.Polis_id = pa.Polis_id
			left join OMSSprTerr sprt with (nolock) on sprt.OMSSprTerr_id = pls.OmsSprTerr_id
			left join v_OrgSmo osmo with (nolock) on osmo.OrgSMO_id = pls.OrgSmo_id
			left join v_PersonRefuse PR with(nolock) on PR.Person_id = pa.Person_id and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
			left join YesNo ref with (nolock) on ref.YesNo_id = PR.PersonRefuse_IsRefuse
			left join Document doc with (nolock) on doc.Document_id = pa.Document_id
			left join DocumentType dt with (nolock) on dt.DocumentType_id = doc.DocumentType_id
			left join NationalityStatus ns with(nolock) on ns.NationalityStatus_id = pa.NationalityStatus_id
			left join KLCountry klc with(nolock) on klc.KLCountry_id = ns.KLCountry_id
			left join Address uaddr with (nolock) on uaddr.Address_id = pa.UAddress_id
			left join Address paddr with (nolock) on paddr.Address_id = pa.PAddress_id
			left join Job jb with (nolock) on jb.Job_id = pa.Job_id
			left join Org jorg with (nolock) on jorg.Org_id = jb.Org_id
		where
			pa.Person_id = :Person_id
			{$filter}
		order by
			pa.PersonEvn_insDT desc,
			pa.PersonEvn_TimeStamp desc
		";
		//die('<pre>'.getDebugSQL($sql, array($data['Person_id'])));
		$res = $this->db->query($sql, $queryParams);

		if (is_object($res)) {
			$response = $res->result('array');
			return $response;
		}
		else
			return false;
	}

	/**
	 * Поиск человека в форме РПН: Прикрепление
	 * В отличие от предыдущего поиска человека добавлено больше фильтров
	 */
	function getPersonCardGrid($data, $print = false, $get_count = false) {
		$filters = array('(1 = 1)');
		$queryParams = array();
		$mongoParams = array();
		// Рефакторинг
		// Разбиваем запрос на несколько частей
		// Сначала собираем фильтры по PersonState

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['soc_card_id']) && mb_strlen($data['soc_card_id']) >= 25) {
			$filters[] = "LEFT(ps.Person_SocCardNum, 19) = :SocCardNum";
			$queryParams['SocCardNum'] = mb_substr($data['soc_card_id'], 0, 19);
		}

		if (!empty($data['Person_SurName']) && $data['Person_SurName'] != '_') {
			$filters[] = "ps.Person_SurName LIKE :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']) . '%';

			if (checkMongoDb() == 'mongodb') {
				$mongoParams['Person_SurName'] = new MongoDB\BSON\Regex("^{$data['Person_SurName']}", "i");
			} else {
				$regex = "/^{$data['Person_SurName']}/i";
				$mongoParams['Person_SurName'] = new MongoRegex($regex);
			}
		}

		if (!empty($data['Person_FirName']) && $data['Person_FirName'] != '_') {
			$filters[] = "ps.Person_FirName LIKE :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']) . '%';

			if (checkMongoDb() == 'mongodb') {
				$mongoParams['Person_FirName'] = new MongoDB\BSON\Regex("^{$data['Person_FirName']}", "i");
			} else {
				$regex = "/^{$data['Person_FirName']}/i";
				$mongoParams['Person_FirName'] = new MongoRegex($regex);
			}
		}

		if (!empty($data['Person_SecName']) && $data['Person_SecName'] != '_') {
			$filters[] = "ps.Person_SecName LIKE :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']) . '%';

			if (checkMongoDb() == 'mongodb') {
				$mongoParams['Person_SecName'] = new MongoDB\BSON\Regex("^{$data['Person_SecName']}", "i");
			} else {
				$regex = "/^{$data['Person_SecName']}/i";
				$mongoParams['Person_SecName'] = new MongoRegex($regex);
			}
		}

		if (!empty($data['Person_BirthDay'][0]) || !empty($data['Person_BirthDay'][1])) {
			if (!empty($data['Person_BirthDay'][0])) {
				$filters[] = "ps.Person_BirthDay >= :Person_BirthDayStart";
				$queryParams['Person_BirthDayStart'] = $data['Person_BirthDay'][0];
			}

			if (!empty($data['Person_BirthDay'][1])) {
				$filters[] = "ps.Person_BirthDay <= :Person_BirthDayEnd";
				$queryParams['Person_BirthDayEnd'] = $data['Person_BirthDay'][1];
			}
		}

		if (!empty($data['Person_Snils'])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (!empty($data['Person_Inn'])) {
			$filters[] = "ps.Person_Inn = :Person_Inn";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		if (!($data['PersonAge_From'] == 0 && $data['PersonAge_To'] == 200)) {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay,@getdate) between :PersonAge_From and :PersonAge_To)";
			$queryParams['PersonAge_From'] = $data['PersonAge_From'];
			$queryParams['PersonAge_To'] = $data['PersonAge_To'];
		}

		// Фильтры по адресу
		if (!empty($data['KLAreaType_id']) || !empty($data['KLCountry_id']) || !empty($data['KLRgn_id'])
				|| !empty($data['KLSubRgn_id']) || !empty($data['KLCity_id']) || !empty($data['KLTown_id'])
				|| !empty($data['KLStreet_id']) || !empty($data['Address_House']) || !empty($data['Address_Corpus'])
		) {
			// Адрес регистрации
			if ($data['AddressStateType_id'] == 1) {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "uaddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "uaddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "uaddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "uaddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "uaddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "uaddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "uaddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}
				//Corpus
				if (!empty($data['Address_Corpus'])) {
					$filters[] = "uaddr.Address_Corpus = :Address_Corpus";
					$queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "uaddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			}
			// Адрес проживания
			else if ($data['AddressStateType_id'] == 2) {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "paddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "paddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "paddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "paddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "paddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "paddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "paddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "paddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			} else {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "uaddr.KLCountry_id = :KLCountry_id";
					$filters[] = "paddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "uaddr.KLRgn_id = :KLRgn_id";
					$filters[] = "paddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "uaddr.KLSubRgn_id = :KLSubRgn_id";
					$filters[] = "paddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "uaddr.KLCity_id = :KLCity_id";
					$filters[] = "paddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "uaddr.KLTown_id = :KLTown_id";
					$filters[] = "paddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "uaddr.KLStreet_id = :KLStreet_id";
					$filters[] = "paddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "uaddr.Address_House = :Address_House";
					$filters[] = "paddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}
				//Corpus
				if (!empty($data['Address_Corpus'])) {
					$filters[] = "uaddr.Address_Corpus = :Address_Corpus";
					$filters[] = "paddr.Address_Corpus = :Address_Corpus";
					$queryParams['Address_Corpus'] = $data['Address_Corpus'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "paddr.KLAreaType_id = :KLAreaType_id";
					$filters[] = "uaddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			}
		}

		// Вхождение в регистр льготников
		if (!empty($data['RegisterSelector_id']) && in_array($data['RegisterSelector_id'], array(1, 2))) {
			$privilegeFilter = "exists (
				select top 1 PersonPrivilege_id
				from v_PersonPrivilege t1 with (nolock)
					inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
				where t1.Person_id = ps.Person_id
					and t2.ReceptFinance_id = :ReceptFinance_id
					and t1.PersonPrivilege_begDate <= @getdate
					and (IsNull(t1.PersonPrivilege_endDate, @getdate) >= cast(@getdate as date))
			";

			$queryParams['ReceptFinance_id'] = $data['RegisterSelector_id'];

			switch ($data['RegisterSelector_id']) {
				case 2:
					//$privilegeFilter .= "and t1.Lpu_id = :Lpu_id";
					break;
			}

			$privilegeFilter .= ")";

			$filters[] = $privilegeFilter;
		}

		// Отказ от льготы
		if (!empty($data['Refuse_id'])) {
			$filters[] = ($data['Refuse_id'] == 1 ? "not " : "") . "exists (
				select top 1 PersonRefuse_IsRefuse
				from v_PersonRefuse with (nolock)
				where
					Person_id = ps.Person_id
					and PersonRefuse_IsRefuse = 2
					and PersonRefuse_Year = YEAR(@getdate)
			)";
		}

		// Отказ от льготы на следующий год
		if (!empty($data['RefuseNextYear_id'])) {
			$filters[] = ($data['RefuseNextYear_id'] == 1 ? "not " : "") . "exists (
				select top 1 PersonRefuse_IsRefuse
				from v_PersonRefuse with (nolock)
				where
					Person_id = ps.Person_id
					and PersonRefuse_IsRefuse = 2
					and PersonRefuse_Year = YEAR(@getdate) + 1
			)";
		}

		// Есть действующий полис
		if (!empty($data['PersonCard_IsActualPolis'])) {
			$filters[] = ($data['PersonCard_IsActualPolis'] == 1 ? "not " : "") . "exists (
				select top 1 Polis_id
				from v_Polis with (nolock)
				where
					Polis_id = ps.Polis_id
					and (IsNull(Polis_endDate, @getdate+1) > cast(@getdate as date))
			)";
		}

		if (!empty($data['dontShowUnknowns'])) {// #158923 показывать ли неизвестных в РПН: Прикрепление
			$filters[] = 'isnull(ps.Person_IsUnknown,1) != 2';
		}

		// Фильтры по прикреплению
		if (
				!empty($data['PersonCard_Code']) || !empty($data['LpuRegion_id']) || !empty($data['LpuRegion_Fapid']) || !empty($data['LpuRegionType_id']) || !empty($data['LpuRegionType_id'])
				|| !empty($data['PersonCard_begDate'][0]) || !empty($data['PersonCard_begDate'][1])
				|| !empty($data['PersonCard_endDate'][0]) || !empty($data['PersonCard_endDate'][1])
				|| !empty($data['AttachLpu_id']) || !empty($data['PersonCard_IsAttachCondit'])
				|| (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] != 3)
		) {
			$personCardFilters = array('Person_id = ps.Person_id');

			if (!empty($data['PersonCard_Code'])) {
				$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (!empty($data['LpuRegion_id'])) {
				if ($data['LpuRegion_id'] == -1) {
					$personCardFilters[] = "LpuRegion_id is null";
				} else {
					$personCardFilters[] = "LpuRegion_id = :LpuRegion_id";
					$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
				}
			}

            if(!empty($data['LpuRegion_Fapid'])) {
                $personCardFilters[] = "LpuRegion_fapid = :LpuRegion_Fapid";
                $queryParams['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
            }

			if (!empty($data['LpuRegionType_id'])) {
				$personCardFilters[] = "LpuRegionType_id = :LpuRegionType_id";
				$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
			}

			if (!empty($data['PersonCard_begDate'][0])) {
				$personCardFilters[] = "cast(PersonCard_begDate as date) >= :PersonCard_begDateStart";
				$queryParams['PersonCard_begDateStart'] = $data['PersonCard_begDate'][0];
			}

			if (!empty($data['PersonCard_begDate'][1])) {
				$personCardFilters[] = "cast(PersonCard_begDate as date) <= :PersonCard_begDateEnd";
				$queryParams['PersonCard_begDateEnd'] = $data['PersonCard_begDate'][1];
			}

			if (!empty($data['PersonCard_endDate'][0])) {
				$personCardFilters[] = "cast(PersonCard_endDate as date) >= :PersonCard_endDateStart";
				$queryParams['PersonCard_endDateStart'] = $data['PersonCard_endDate'][0];
			}

			if (!empty($data['PersonCard_endDate'][1])) {
				$personCardFilters[] = "cast(PersonCard_endDate as date) <= :PersonCard_endDateEnd";
				$queryParams['PersonCard_endDateEnd'] = $data['PersonCard_endDate'][1];
			}

			if (!empty($data['AttachLpu_id'])) {
				$personCardFilters[] = "Lpu_id = :AttachLpu_id";
				$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
			}

			if (!empty($data['PersonCard_IsAttachCondit'])) {
				$personCardFilters[] = "isnull(PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
				$queryParams['PersonCard_IsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
			}

			if (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] == 1) {
				$personCardFilters[] = "(IsNull(PersonCard_endDate, @getdate+1) > @getdate)";
			}

			$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard" . (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] == 1 ? "" : "_all") . " with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";
		}
		/*if ($this->swmongocache->isEntry('PersonCard', $mongoParams)) {
			return array('data'=>$this->swmongocache->get('PersonCard', $mongoParams));
		}*/
		// Выборка людей
		$query = "
			-- variables
			declare @getdate datetime = dbo.tzGetDate();
			-- end variables

			select
				" . (!$print ? "top 1000" : "") . "
				-- select
				 ps.Person_id
				,ps.Server_id
				,ps.PersonEvn_id
				,ps.Person_SurName
				,ps.Person_FirName
				,ps.Person_SecName
				,ISNULL(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress
				,ISNULL(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress
				,convert(varchar(10), ps.Person_BirthDay, 104) as PersonBirthDay
				,convert(varchar(10), ps.Person_deadDT, 104) as Person_deadDT
				,null as Person_IsBDZ
				,null as Lpu_Nick
				,null as Person_IsRefuse
				,null as Person_IsFedLgot
				,null as Person_IsRegLgot
				,null as Person_Is7Noz
				,null as PersonCard_IsDms
				-- end select
			from
				-- from
				v_PersonState ps with (nolock)
				left join [Address] uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join [Address] paddr with (nolock) on paddr.Address_id = ps.PAddress_id
				-- end from
			where
				-- where
				" . implode(" and ", $filters) . "
				-- end where
			order by
				-- order by
				ps.Person_id
				-- end order by
		";
		//echo getDebugSQL($query, $queryParams); exit();

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

		$personIdList = array();

		foreach ($response['data'] as $row) {
			$personIdList[] = $row['Person_id'];
		}
		$isPerm = $data['session']['region']['nick'] == 'perm';
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @getdate, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if($isPerm){
			$isBDZ ="case
					when ps.Server_pid = 0 /*and ps.Person_IsInErz = 2*/ then 'true'
					when ps.Person_IsInErz = 1 then 'blue'
					when ps.Server_pid = 0 and pls.Polis_endDate < cast(@getdate as date) then
						case when ps.Person_deadDT is null then 'yellow' else 'red' end
					when ps.Server_pid = 2 and ps.Person_IsInErz <> 1 then 'false'
				end as [Person_IsBDZ],";
		}
		// Если есть записи...
		if (count($personIdList) > 0) {
			// ... тянем остальные поля
			$query = "
				declare @getdate datetime = dbo.tzGetDate();
				select
					 ps.Person_id
					,l.Lpu_Nick
					,
					".$isBDZ."
					case when ref.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end as Person_IsRefuse
					,case when fedl.Person_id is not null then 'true' else 'false' end as Person_IsFedLgot
					,case
						when regl.Lpu_id is null then 'false'
						when regl.Lpu_id = :Lpu_id then 'true'
						else 'gray'
					 end as Person_IsRegLgot
					,case
						when disp.Lpu_id is null then 'false'
						when disp.Lpu_id = :Lpu_id then 'true'
						else 'gray'
					 end as Person_Is7Noz
					,case when dms.PersonCard_id is not null then 'true' else 'false' end as PersonCard_IsDms
				from v_PersonState ps with (nolock)
					left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
					outer apply (
						select top 1 Lpu_id
						from v_PersonCard with (nolock)
						where Person_id = ps.Person_id
							and LpuAttachType_id = 1
							and PersonCard_begDate <= @getdate
							and IsNull(PersonCard_endDate, @getdate+1) > cast(@getdate as date)
						order by PersonCard_begDate desc
					) pc
					left join v_Lpu l with (nolock) on l.Lpu_id = pc.Lpu_id
					outer apply (
						select top 1 PersonRefuse_IsRefuse
						from v_PersonRefuse with (nolock)
						where
							Person_id = ps.Person_id
							and PersonRefuse_IsRefuse = 2
							and PersonRefuse_Year = YEAR(@getdate)
					) ref
					outer apply (
						select top 1 Person_id
						from v_PersonPrivilege t1 with (nolock)
							inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
						where
							t1.Person_id = ps.Person_id
							and t2.ReceptFinance_id = 1
							and t1.PersonPrivilege_begDate <= @getdate
							and IsNull(t1.PersonPrivilege_endDate, @getdate) >= cast(@getdate as date)
					) as fedl
					outer apply (
						select top 1
							Lpu_id
						from v_PersonPrivilege t1 with (nolock)
							inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
						where
							t1.Person_id = ps.Person_id
							and t2.ReceptFinance_id = 2
							and t1.PersonPrivilege_begDate <= @getdate
							and IsNull(t1.PersonPrivilege_endDate, @getdate) >= cast(@getdate as date)
						order by
							case when t1.Lpu_id = :Lpu_id then 1 else 0 end desc
					) as regl
					outer apply (
						select top 1 PersonCard_id
						from v_PersonCard with (nolock)
						where
							Person_id = ps.Person_id
							and LpuAttachType_id = 5
							and PersonCard_endDate >= @getdate
							and CardCloseCause_id is null
					) dms
					outer apply (
						select top 1 Lpu_id
						from v_PersonDisp with (nolock)
						where Person_id = ps.Person_id
							and IsNull(PersonDisp_endDate, @getdate+1) >= cast(@getdate as date)
							and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
						order by
							case when Lpu_id = :Lpu_id then 1 else 0 end desc
					) as disp
				where ps.Person_id in (" . implode(', ', $personIdList) . ")
			";
			$result = $this->db->query($query, $queryParams);

			if (!is_object($result)) {
				return false;
			}

			$res = $result->result('array');

			if (!is_array($res) || count($res) == 0) {
				return false;
			}

			foreach ($res as $row) {
				foreach ($response['data'] as $key => $array) {
					if ($row['Person_id'] == $array['Person_id']) {
						$response['data'][$key] = array_merge($response['data'][$key], $row);
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Поиск людей
	 */
	function getPersonGrid($data, $print = false, $get_count = false) {
		$addrFilter = '';
		$filters = array('(1 = 1)');
		$queryParams = array();

		// Разбиваем запрос на несколько частей
		// Сначала собираем фильтры по PersonState
		$filterfio = '';
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( !empty($data['Person_id']) ) {
			$queryParams['Person_id'] = $data['Person_id'];
			$filterfio .= "and ps.Person_id = :Person_id ";
		}

		$isSearchByEncryp = false;
		$select_person_data = "
				case when PC.Lpu_id = :Lpu_id then PC.PersonCard_Code else null end as PersonCard_Code,
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num as PersonAmbulatCard_Num,
				isnull('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ case when pls.PolisType_id = 4 and isnull(ps.Person_EdNum, '') != '' then ps.Person_EdNum else isnull(ps.Polis_Ser, '') + ' ' + isnull(ps.Polis_Num, '') end +'</a>','') as Person_PolisInfo,
				isnull(ps.Person_Inn,'') as Person_Inn,
				case
					when
						ISNULL(ps.Person_Phone, Pinf.PersonInfo_InternetPhone) IS NOT NULL
					then
						isnull('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ isnull(Pinf.PersonInfo_InternetPhone + ' (портал самозаписи); ','') + isnull(ps.Person_Phone + ' (БД)','') +'</a>','')
					else
						'<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' + CAST(ps.Person_id as varchar) + '});''>'+ 'Отсутствует' +'</a>'
				end as Person_Phone,
				RTRIM(PS.Person_SurName) as Person_Surname,
				RTRIM(PS.Person_FirName) as Person_Firname,
				RTRIM(PS.Person_SecName) as Person_Secname,
				convert(varchar(10), cast(PS.Person_BirthDay as datetime), 104) as Person_Birthday,
				convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
				case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, @getdate) end as Person_Age,
				ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
				ISNULL(AttachLpu.Lpu_id, 0) as AttachLpu_id,
				convert(varchar(10), cast(PC.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
				convert(varchar(10), cast(PC.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
				PC.LpuAttachType_Name,
				PC.LpuRegionType_Name,
				LR.LpuRegion_Name,
				ISNULL(LR_Fap.LpuRegion_Name,'') as LpuRegion_FapName,
				isnull(paddr.Address_Nick, paddr.Address_Address) as Person_PAddress,
				isnull(uaddr.Address_Nick, uaddr.Address_Address) as Person_UAddress,
		";
		$join = '';
		if (isEncrypHIVRegion($this->regionNick)) {
			if (allowPersonEncrypHIV($data['session'])) {
				$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_Surname']);
				if ($isSearchByEncryp) {
					$select_person_data = "'' as PersonCard_Code,
						'' as Person_PolisInfo,
						'' as Person_Inn,
						'' as Person_Phone,
						peh.PersonEncrypHIV_Encryp as Person_Surname,
						'' as Person_Firname,
						'' as Person_Secname,
						null as Person_Birthday,
						null as Person_deadDT,
						null as Person_Age,
						'' as AttachLpu_Name,
						null as AttachLpu_id,
						null as PersonCard_begDate,
						null as PersonCard_endDate,
						'' as LpuAttachType_Name,
						'' as LpuRegionType_Name,
						'' as LpuRegion_Name,
						'' as Person_PAddress,
						'' as Person_UAddress,
					";
				}
			} else {
				//Не отображать анонимных шифрованных пациентов
				$filters[] = "not exists(
					select top 1 peh.PersonEncrypHIV_Encryp
					from v_PersonEncrypHIV peh with(nolock)
					inner join v_EncrypHIVTerr eht with(nolock) on eht.EncrypHIVTerr_id = peh.EncrypHIVTerr_id
						and isnull(eht.EncrypHIVTerr_Code,0) = 20
					where peh.Person_id = ps.Person_id
				)";
			}
		}

		if ( !empty($data['Person_Surname']) && $data['Person_Surname'] != '_' ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$join .= " inner join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id and peh.PersonEncrypHIV_Encryp like :Person_Surname";
			} else {
				$filterfio .= "and ps.Person_SurNameR LIKE :Person_Surname + '%' ";
			}
			$queryParams['Person_Surname'] = $this->prepareSearchSymbol($data['Person_Surname']);
		}

		if ( !empty($data['Person_Firname']) && $data['Person_Firname'] != '_' ) {
			$queryParams['Person_Firname'] = $this->prepareSearchSymbol($data['Person_Firname']);
			$filterfio .= "and ps.Person_FirnameR LIKE :Person_Firname + '%' ";
		}

		if ( !empty($data['Person_Secname']) && $data['Person_Secname'] != '_' ) {
			$queryParams['Person_Secname'] = $this->prepareSearchSymbol($data['Person_Secname']);
			$filterfio .= "and ps.Person_SecnameR LIKE :Person_Secname + '%' ";
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filters[] = "ps.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['Person_Code']) ) {
			$filters[] = "ps.Person_EdNum = :Person_EdNum";
			$queryParams['Person_EdNum'] = $data['Person_Code'];
		}

		if ( !empty($data['Person_Inn']) ) {
			$filters[] = "exists (select top 1 Person_id from v_PersonState with (nolock) where Person_id = ps.Person_id and Person_Inn = :Person_Inn)";
			$queryParams['Person_Inn'] = $data['Person_Inn'];
		}

		if ( !empty($data['Polis_Num']) ) {
			$filters[] = "pls.Polis_Num = :Polis_Num";
			$queryParams['Polis_Num'] = $data['Polis_Num'];
		}

		if ( !empty($data['Polis_Ser']) ) {
			$filters[] = "pls.Polis_Ser = :Polis_Ser";
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
		}

		if (!empty($data['dontShowUnknowns'])) {// #158923 показывать ли неизвестных в АРМ регистратора поликлиники
			$filters[] = 'isnull(PS.Person_IsUnknown,1) != 2';
		}

		// Фильтр по адресу
		// todo: Есть еще один момент, можно забить номер дома, но не указывать улицу, это долго :)
		if ( !empty($data['Address_Street']) || !empty($data['Address_House']) ) {
			if (
				(empty($data['Person_Surname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Surname']))
				&& (empty($data['Person_Firname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Firname']))
				&& (empty($data['Person_Secname']) || !preg_match("/^[\w\-\s]+$/iu", $data['Person_Secname']))
			) {
				// Для поиска по адресу требуется заполнить хотя бы одно поле из ФИО
				return false;
			}

			$addrFilters = array();
			if ( !empty($data['Address_Street']) ) {
				$addrFilters[] = "ks.KLStreet_Name like :Address_Street";
				$queryParams['Address_Street'] = $data['Address_Street'] . '%';
			}
			if ( !empty($data['Address_House']) ) {
				$addrFilters[] = "a.Address_House = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}

			$filters[] = "
				exists(select top 1 Address_id
					from [Address] a with (nolock)
					left join v_KLStreet ks with (nolock) on ks.KLStreet_id = a.KLStreet_id
					where a.Address_id in (ps.UAddress_id, ps.PAddress_id) and " . implode(' and ', $addrFilters) . "
				)
			";
		}

		$orderby = "";
		// Фильтры по прикреплению
		if ( !empty($data['PersonCard_Code']) ) {
			$personCardFilters = array('Person_id = ps.Person_id');
			$personCardFilters[] = 'Lpu_id = :Lpu_id'; // только в рамках своей МО

			if (!empty($data['PartMatchSearch'])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($this->config->config['blockSlowDownFunctions'])) {
					return array('Error_Msg' => 'Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.');
				}

				//$personCardFilters[] = "PersonCard_Code LIKE '%'+:PersonCard_Code+'%'";
				$personCardFilters[] = "PersonAmbulatCard_Num LIKE '%'+:PersonCard_Code+'%'";
				$orderby = "case when ISNULL(CHARINDEX(:PersonCard_Code, pc.PersonCard_Code), 0) > 0 then CHARINDEX(:PersonCard_Code, pc.PersonCard_Code) else 99 end,";
			} else {
				//$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$personCardFilters[] = "PersonAmbulatCard_Num = :PersonCard_Code";
			}

			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];

			/*$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";*/
			$filters[] = "exists (
				select top 1 PersonAmbulatCard_id
				from v_PersonAmbulatCard with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";
		}
		
		
		If (count($queryParams)<=1) { // Если указан только обязательный фильтр по ЛПУ
			// Сообщим пользователю что нужно ввести хотя бы одно значение в фильтрах (при текущих проверках по этой ветке не должно пойти, но на всякий случай)
			return array('success' =>false,'Error_Msg' => toUtf('Не указан ни один фильтр. Для поиска заполните хотя бы один параметр в блоке фильтров.'));
		}
		$isPerm = $data['session']['region']['nick'] == 'perm';
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @getdate, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN ps.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if($isPerm){
			$isBDZ ="case 
				when ps.Server_pid = 0 then 
	case when ps.Person_IsInErz = 1  then 'blue' 
	else case when pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @getdate, 112) as datetime) THEN 
		case when ps.Person_deadDT is not null then 'red' else 'yellow' end
	else 'true' end end 
	else 'false' end as [Person_IsBDZ],";
		}
		if (getRegionNick() == 'kz') {
			$isBDZ ="case
				when pers.Person_IsInFOMS = 1 then 'orange'
				when pers.Person_IsInFOMS = 2 then 'true'
				else 'false'
			end as [Person_IsBDZ],";
		}
		// Основной поисковый запрос
		$query = "
			-- variables
			declare @getdate datetime = dbo.tzGetDate();
			-- end variables
			select
				-- select
				PC.PersonCard_id,
				PS.Person_id,
				PS.Server_id,
				PS.PersonEvn_id,
				{$select_person_data}
				CASE WHEN PS.Person_DeadDT is not null  THEN 'true' ELSE 'false' END as Person_IsDead,
				CASE WHEN ISNULL(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as PersonCard_IsAttachCondit,
				CASE WHEN persdata.agree = 2 THEN 'V' WHEN persdata.agree = 1 THEN 'X' else '' END as PersonLpuInfo_IsAgree,
				NA.NewslatterAccept_id,
				ISNULL(convert(varchar(11), NA.NewslatterAccept_begDate, 104), 'Отсутствует') as NewslatterAccept,
				CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
				CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
				CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
				CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
				CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
				".$isBDZ."
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn,
				CASE 
					WHEN disp.OwnLpu = 1 THEN 'true'
					WHEN disp.OwnLpu is not null THEN 'gray'
					ELSE 'false'
				END as Person_Is7Noz
				--CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				
				-- end select
			from
				-- from
				v_PersonState_All PS with (nolock)
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				OUTER APPLY(
					SELECT TOP 1
						RTRIM(PersonInfo_InternetPhone) AS PersonInfo_InternetPhone
					FROM
						v_PersonInfo with (nolock)
					WHERE
						Person_id = PS.Person_id
				) Pinf
                outer apply (
                    select top 1 
                        case 
                            when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then PersonCard_id -- если есть активное прикрепление к этой МО, то до этой ветки 
                            when LpuAttachType_id = 1 then PersonCard_id
                            when LpuAttachType_id in (2,3) and Lpu_id = :Lpu_id then PersonCard_id
                            else null
                        end as PersonCard_id
                    from v_PersonCard_all with(nolock)
                    where Person_id = PS.Person_id
						and PersonCard_endDate is null
						and LpuAttachType_id is not null
                    order by
						case when LpuAttachType_id = 4 and Lpu_id = :Lpu_id then 0 else LpuAttachType_id end,
						PersonCard_begDate
                ) as PersonCard
				left join v_PersonCard_all PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id
                left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id 
                --left join v_PersonState Inn with(nolock) on Inn.Person_id = ps.Person_id 
                left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
                left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
                left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_Person pers with (nolock) on pers.Person_id = ps.Person_id
				left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR(@getdate)+1) 
				left join v_NewslatterAccept NA with (nolock) on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null
				outer apply (
                    select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
                    from PersonDisp with (nolock)
                    where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > @getdate)
                    and Sickness_id IN (1,3,4,5,6,7,8)
                ) as disp
				outer apply (
					select top 1
						PersonLpuInfo_IsAgree as agree
					from v_PersonLpuInfo pli with (nolock)
					where pli.Person_id = PS.Person_id and pli.Lpu_id = :Lpu_id
					order by pli.PersonLpuInfo_setDT desc
				) persdata
				outer apply (
					select top 1
						PersonAmbulatCard_id,
						PersonAmbulatCard_Num
					from v_PersonAmbulatCard with (nolock)
					where Person_id = PS.Person_id and Lpu_id = :Lpu_id and ISNULL(PersonAmbulatCard_endDate, @getdate) >= @getdate
					order by Person_id desc
				) PAC
				{$join}
				-- end from
			where
				-- where
				" . implode(" and ", $filters) . " " . $filterfio . " 
				-- end where
			order by
				-- order by
				{$orderby}
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

    /**
     * Получение списка ЗЛ для автоприкрепления
     */
    function getPersonGridPersonCardAuto($data)
    {
        $params = array();
        $and = '';
        $orgFilter = '(1 = 1)';
        if(!empty($data['Sex_id'])) {
            $and .= " and PS.Sex_id = :Sex_id";
            $params['Sex_id'] = $data['Sex_id'];
        }
        /*if(!empty($data['PSPCALpu_id'])) {
            $and .= " and PC.Lpu_id = :Lpu_id";
            $params['Lpu_id'] = $data['PSPCALpu_id'];
        }*/
		if(!empty($data['PSPCAOrg_id'])) {
			$orgFilter .= " and O.Org_id = :Org_id";
			$params['Org_id'] = $data['PSPCAOrg_id'];
		}
        if(!empty($data['PSPCALpuRegion_id'])) {
            if($data['PSPCALpuRegion_id'] == -1)
            {
                $and .= " and PC.LpuRegion_id is null";
            }
            else
            {
                $and .= " and PC.LpuRegion_id = :LpuRegion_id";
                $params['LpuRegion_id'] = $data['PSPCALpuRegion_id'];
            }
        }
        if(!empty($data['LpuRegion_Fapid'])){
            $and .= " and PC.LpuRegion_fapid = :LpuRegion_Fapid";
            $params['LpuRegion_Fapid'] = $data['LpuRegion_Fapid'];
        }

        if(!empty($data['PSPCALpuRegionType_id'])) {
            $and .= " and PC.LpuRegionType_id = :LpuRegionType_id";
            $params['LpuRegionType_id'] = $data['PSPCALpuRegionType_id'];
        }
        if(!empty($data['PersonAge_Min'])) {
            $and .= " and (dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) >= :PersonAge_Min)";
            $params['PersonAge_Min'] = $data['PersonAge_Min'];
        }
        if(!empty($data['PersonAge_Max'])) {
            $and .= " and (dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) <= :PersonAge_Max)";
            $params['PersonAge_Max'] = $data['PersonAge_Max'];
        }

        //Адрес проживания
        if(!empty($data['PKLCountry_id'])) {
            $and .= " and PA.KLCountry_id = :PKLCountry_id";
            $params['PKLCountry_id'] = $data['PKLCountry_id'];
        }
        if(!empty($data['PKLRGN_id'])) {
            $and .= " and PA.KLRGN_id = :PKLRGN_id";
            $params['PKLRGN_id'] = $data['PKLRGN_id'];
        }
        if(!empty($data['PKLSubRGN_id'])) {
            $and .= " and PA.KLSubRGN_id = :PKLSubRGN_id";
            $params['PKLSubRGN_id'] = $data['PKLSubRGN_id'];
        }
        if(!empty($data['PKLCity_id'])) {
            $and .= " and PA.KLCity_id = :PKLCity_id";
            $params['PKLCity_id'] = $data['PKLCity_id'];
        }
        if(!empty($data['PKLTown_id'])) {
            $and .= " and PA.KLTown_id = :PKLTown_id";
            $params['PKLTown_id'] = $data['PKLTown_id'];
        }
        if(!empty($data['PKLStreet_id'])) {
            $and .= " and PA.KLStreet_id = :PKLStreet_id";
            $params['PKLStreet_id'] = $data['PKLStreet_id'];
        }
		if(!empty($data['PAddress_House'])){
			$and .= " and PA.Address_House = :PAddress_House";
			$params['PAddress_House'] = $data['PAddress_House'];
		}
		if(!empty($data['PAddress_Corpus'])){
			$and .= " and PA.Address_Corpus = :PAddress_Corpus";
			$params['PAddress_Corpus'] = $data['PAddress_Corpus'];
		}
		if(!empty($data['PAddress_Flat'])){
			$and .= " and PA.Address_Flat = :PAddress_Flat";
			$params['PAddress_Flat'] = $data['PAddress_Flat'];
		}

        //Адрес регистрации
        if(!empty($data['UKLCountry_id'])) {
            $and .= " and UA.KLCountry_id = :UKLCountry_id";
            $params['UKLCountry_id'] = $data['UKLCountry_id'];
        }
        if(!empty($data['UKLRGN_id'])) {
            $and .= " and UA.KLRGN_id = :UKLRGN_id";
            $params['UKLRGN_id'] = $data['UKLRGN_id'];
        }
        if(!empty($data['UKLSubRGN_id'])) {
            $and .= " and UA.KLSubRGN_id = :UKLSubRGN_id";
            $params['UKLSubRGN_id'] = $data['UKLSubRGN_id'];
        }
        if(!empty($data['UKLCity_id'])) {
            $and .= " and UA.KLCity_id = :UKLCity_id";
            $params['UKLCity_id'] = $data['UKLCity_id'];
        }
        if(!empty($data['UKLTown_id'])) {
            $and .= " and UA.KLTown_id = :UKLTown_id";
            $params['UKLTown_id'] = $data['UKLTown_id'];
        }
        if(!empty($data['UKLStreet_id'])) {
            $and .= " and UA.KLStreet_id = :UKLStreet_id";
            $params['UKLStreet_id'] = $data['UKLStreet_id'];
        }
		if(!empty($data['UAddress_House'])){
			$and .= " and UA.Address_House = :UAddress_House";
			$params['UAddress_House'] = $data['UAddress_House'];
		}
		if(!empty($data['UAddress_Corpus'])){
			$and .= " and UA.Address_Corpus = :UAddress_Corpus";
			$params['UAddress_Corpus'] = $data['UAddress_Corpus'];
		}
		if(!empty($data['UAddress_Flat'])){
			$and .= " and UA.Address_Flat = :UAddress_Flat";
			$params['UAddress_Flat'] = $data['UAddress_Flat'];
		}

        $query = "
			-- addit with
			WITH LpuTable as (
				SELECT Lpu_id, Lpu_Nick
				FROM v_lpu AS L with (nolock)
					left join v_Org O with (nolock) on O.Org_id = L.Org_id
				WHERE {$orgFilter}
			)
			-- end addit with
 
			select
        		-- select
        		0 as Is_Checked,
                PS.Person_id,
                ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_SecName,'') as Person_FIO,
                convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
                case when PC.PersonCard_id is not null then 'Да' else 'Нет' end as PersonStatus,
                case when PC.PersonCard_id is not null then ISNULL(L.Lpu_Nick,'') else '' end as Lpu_Name,
                case when PC.PersonCard_id is not null then ISNULL(PC.LpuRegion_Name,'') else '' end as LpuRegion_Name,
                case when PC.PersonCard_id is not null then ISNULL(LR.LpuRegion_Name,'') else '' end as LpuRegion_FapName,
                case when PC.PersonCard_id is not null then ISNULL(PC.LpuRegionType_Name,'') else '' end as LpuRegionType_Name,
                ISNULL(UA.Address_Address,'') as UAddress_Name,
                ISNULL(PA.Address_Address,'') as PAddress_Name,
                S.Sex_Name,
                CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), dbo.tzGetdate(), 112) as datetime) THEN 'yellow' ELSE CASE WHEN ps.PersonCloseCause_id = 2 and Person_closeDT is not null THEN 'red' ELSE CASE WHEN ps.Server_pid = 0 THEN 'true' ELSE 'false' END END END as [Person_IsBDZ]
                -- end select
            from
            -- from
            /*v_PersonState PS with (nolock)
            left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1*/
            v_PersonCard PC with (nolock)
            inner join v_PersonState PS with (nolock) on PS.Person_id = PC.Person_id
            left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_fapid
            inner join LpuTable L with (nolock) on L.Lpu_id = PC.Lpu_id
            left join v_Address UA with (nolock) on UA.Address_id = PS.UAddress_id
            left join v_Address PA with (nolock) on PA.Address_id = PS.PAddress_id
            left join v_Sex S with (nolock) on S.Sex_id = PS.Sex_id
            left join Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
            -- end from
            where
            -- where
            PS.Person_deadDT is null
            and ((ISNULL(PS.Person_SurName,'')<>'') or (ISNULL(PS.Person_FirName,'')<>'') or (ISNULL(PS.Person_SecName,'')<>''))
            and PC.LpuAttachType_id = 1
            ".$and."
            -- end where
            order by
            -- order by
            PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
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

        /*
        $result = $this->db->query($query,$params);
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }*/
    }

    /**
	 * df
	 */
	function CheckSpecifics($data)
    {
		$records = json_decode($data['Records'], true);

		if ( !is_array($records) || count($records) < 2 ) {
			return false;
		}

		$query = '
			select *
			from v_PersonChild with (nolock)
			where Person_id in ('.implode(',', $records).')
		';
		$result = $this->db->query($query, array());
		
		if (!is_object($result)) {
			return false;
		}
		// @task https://redmine.swan.perm.ru/issues/87140
		$arr = array(
			'ResidPlace_id',
			'PersonChild_id',
			'PersonChild_IsManyChild',
			'PersonChild_IsBad',
			'PersonChild_IsIncomplete',
			'PersonChild_IsTutor',
			'PersonChild_IsMigrant',
			'HealthKind_id',
			'PersonChild_IsYoungMother',
			'FeedingType_id',
			'PersonChild_CountChild',
			'InvalidKind_id',
			'PersonChild_invDate',
			'HealthAbnorm_id',
			'HealthAbnormVital_id',
			'Diag_id',
			'PersonChild_IsInvalid',
			'PersonSprTerrDop_id'
		);
		
		$res = $result->result('array');

		// Доработал проверку, т.к. в PersonChild может быть несколько записей на одного пациента
		// @task https://redmine.swan.perm.ru/issues/105148
		$cnt = 0;
		$personArray = array();

		foreach($res as $item){
			if ( in_array($item['Person_id'], $personArray) ) {
				continue;
			}

			foreach ( $item as $key => $val ) {
				if ( in_array($key, $arr)&&!empty($val) ) {
					$personArray[] = $item['Person_id'];
					$cnt++;
					break;
				}
			}
		}

		if ( $cnt > 1 ) {
			return array('success' =>false,'Error_Msg' => 'У нескольких пациентов есть специфика детства. Объединение невозможно!');
		}

		/*$query = '
			select top 1 count(distinct Person_id) as cnt
			from v_PersonNewBorn with (nolock)
			where Person_id in ('.implode(',', $records).')
		';
		$cnt = $this->getFirstResultFromQuery($query, array());
		if ($cnt === false) {
			return false;
		}
		if ($cnt>1) {
			return array('success' =>false,'Error_Msg' => toUtf('У нескольких пациентов есть специфика новорожденного. Объединение невозможно!'));
		}*/

		return array('success' => true, 'Error_Msg' =>null);
		
	}

	/**
	 * Получение данных человека для объединения
	 */
	function getInfoForDouble($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select top 1
				PS.Person_id,
				case when MP.MedPersonal_id is null then 0 else 1 end as IsMedPersonal
			from
				v_PersonState PS with(nolock)
				outer apply(
					select top 1 MedPersonal_id
					from v_MedPersonal with(nolock)
					where Person_id = PS.Person_id
				) MP
			where
				PS.Person_id = :Person_id
		";

		$response = $this->queryResult($query, $params);

		return $response[0];
	}

	/**
	 * Экспорт людей из картотеки
	 */
	function exportPersonCardForIdentification($data) {
		set_time_limit(0);
		$filters = array('(1 = 1)');
		$queryParams = array();

		// Рефакторинг
		// Разбиваем запрос на несколько частей
		// Сначала собираем фильтры по PersonState

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if (!empty($data['soc_card_id']) && mb_strlen($data['soc_card_id']) >= 25) {
			$filters[] = "LEFT(ps.Person_SocCardNum, 19) = :SocCardNum";
			$queryParams['SocCardNum'] = mb_substr($data['soc_card_id'], 0, 19);
		}

		if (!empty($data['Person_SurName']) && $data['Person_SurName'] != '_') {
			$filters[] = "ps.Person_SurName LIKE :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']) . '%';
		}

		if (!empty($data['Person_FirName']) && $data['Person_FirName'] != '_') {
			$filters[] = "ps.Person_FirName LIKE :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']) . '%';
		}

		if (!empty($data['Person_SecName']) && $data['Person_SecName'] != '_') {
			$filters[] = "ps.Person_SecName LIKE :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']) . '%';
		}

		if (!empty($data['Person_BirthDay'][0]) || !empty($data['Person_BirthDay'][1])) {
			if (!empty($data['Person_BirthDay'][0])) {
				$filters[] = "ps.Person_BirthDay >= :Person_BirthDayStart";
				$queryParams['Person_BirthDayStart'] = $data['Person_BirthDay'][0];
			}

			if (!empty($data['Person_BirthDay'][1])) {
				$filters[] = "ps.Person_BirthDay <= :Person_BirthDayEnd";
				$queryParams['Person_BirthDayEnd'] = $data['Person_BirthDay'][1];
			}
		}

		if (!empty($data['Person_Snils'])) {
			$filters[] = "ps.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (!($data['PersonAge_From'] == 0 && $data['PersonAge_To'] == 200)) {
			$filters[] = "(dbo.Age2(ps.Person_BirthDay,dbo.tzGetDate()) between :PersonAge_From and :PersonAge_To)";
			$queryParams['PersonAge_From'] = $data['PersonAge_From'];
			$queryParams['PersonAge_To'] = $data['PersonAge_To'];
		}

		// Фильтры по адресу
		if (!empty($data['KLAreaType_id']) || !empty($data['KLCountry_id']) || !empty($data['KLRgn_id'])
				|| !empty($data['KLSubRgn_id']) || !empty($data['KLCity_id']) || !empty($data['KLTown_id'])
				|| !empty($data['KLStreet_id']) || !empty($data['Address_House'])
		) {
			// Адрес регистрации
			if ($data['AddressStateType_id'] == 1) {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "uaddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "uaddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "uaddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "uaddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "uaddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "uaddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "uaddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "uaddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			}
			// Адрес проживания
			else if ($data['AddressStateType_id'] == 2) {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "paddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "paddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "paddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "paddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "paddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "paddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "paddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "paddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			} else {
				// Страна
				if (!empty($data['KLCountry_id'])) {
					$filters[] = "uaddr.KLCountry_id = :KLCountry_id";
					$filters[] = "paddr.KLCountry_id = :KLCountry_id";
					$queryParams['KLCountry_id'] = $data['KLCountry_id'];
				}

				// Регион
				if (!empty($data['KLRgn_id'])) {
					$filters[] = "uaddr.KLRgn_id = :KLRgn_id";
					$filters[] = "paddr.KLRgn_id = :KLRgn_id";
					$queryParams['KLRgn_id'] = $data['KLRgn_id'];
				}

				// Район
				if (!empty($data['KLSubRgn_id'])) {
					$filters[] = "uaddr.KLSubRgn_id = :KLSubRgn_id";
					$filters[] = "paddr.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				// Город
				if (!empty($data['KLCity_id'])) {
					$filters[] = "uaddr.KLCity_id = :KLCity_id";
					$filters[] = "paddr.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				// Населенный пункт
				if (!empty($data['KLTown_id'])) {
					$filters[] = "uaddr.KLTown_id = :KLTown_id";
					$filters[] = "paddr.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				// Улица
				if (!empty($data['KLStreet_id'])) {
					$filters[] = "uaddr.KLStreet_id = :KLStreet_id";
					$filters[] = "paddr.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				// Номер дома
				if (!empty($data['Address_House'])) {
					$filters[] = "uaddr.Address_House = :Address_House";
					$filters[] = "paddr.Address_House = :Address_House";
					$queryParams['Address_House'] = $data['Address_House'];
				}

				// Тип адреса
				if (!empty($data['KLAreaType_id'])) {
					$filters[] = "paddr.KLAreaType_id = :KLAreaType_id";
					$filters[] = "uaddr.KLAreaType_id = :KLAreaType_id";
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
			}
		}

		// Вхождение в регистр льготников
		if (!empty($data['RegisterSelector_id']) && in_array($data['RegisterSelector_id'], array(1, 2))) {
			$privilegeFilter = "exists (
				select top 1 PersonPrivilege_id
				from v_PersonPrivilege t1 with (nolock)
					inner join v_PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
				where t1.Person_id = ps.Person_id
					and t2.ReceptFinance_id = :ReceptFinance_id
					and t1.PersonPrivilege_begDate <= dbo.tzGetDate()
					and (t1.PersonPrivilege_endDate is null or t1.PersonPrivilege_endDate >= cast(dbo.tzGetDate() as date))
			";

			$queryParams['ReceptFinance_id'] = $data['RegisterSelector_id'];

			switch ($data['RegisterSelector_id']) {
				case 2:
					//$privilegeFilter .= "and t1.Lpu_id = :Lpu_id";
					break;
			}

			$privilegeFilter .= ")";

			$filters[] = $privilegeFilter;
		}

		// Отказ от льготы
		if (!empty($data['Refuse_id'])) {
			$filters[] = ($data['Refuse_id'] == 1 ? "not " : "") . "exists (
				select top 1 PersonRefuse_IsRefuse
				from v_PersonRefuse with (nolock)
				where
					Person_id = ps.Person_id
					and PersonRefuse_IsRefuse = 2
					and PersonRefuse_Year = YEAR(dbo.tzGetDate())
			)";
		}

		// Отказ от льготы на следующий год
		if (!empty($data['RefuseNextYear_id'])) {
			$filters[] = ($data['RefuseNextYear_id'] == 1 ? "not " : "") . "exists (
				select top 1 PersonRefuse_IsRefuse
				from v_PersonRefuse with (nolock)
				where
					Person_id = ps.Person_id
					and PersonRefuse_IsRefuse = 2
					and PersonRefuse_Year = YEAR(dbo.tzGetDate()) + 1
			)";
		}

		// Есть действующий полис
		if (!empty($data['PersonCard_IsActualPolis'])) {
			$filters[] = ($data['PersonCard_IsActualPolis'] == 1 ? "not " : "") . "exists (
				select top 1 Polis_id
				from v_Polis with (nolock)
				where
					Polis_id = ps.Polis_id
					and (Polis_endDate is null or Polis_endDate > cast(dbo.tzGetDate() as date))
			)";
		}

		// Фильтры по прикреплению
		if (
				!empty($data['PersonCard_Code']) || !empty($data['LpuRegion_id']) || !empty($data['LpuRegionType_id']) || !empty($data['LpuRegionType_id'])
				|| !empty($data['PersonCard_begDate'][0]) || !empty($data['PersonCard_begDate'][1])
				|| !empty($data['PersonCard_endDate'][0]) || !empty($data['PersonCard_endDate'][1])
				|| !empty($data['AttachLpu_id']) || !empty($data['PersonCard_IsAttachCondit'])
				|| (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] != 3)
		) {
			$personCardFilters = array('Person_id = ps.Person_id');

			if (!empty($data['PersonCard_Code'])) {
				$personCardFilters[] = "PersonCard_Code = :PersonCard_Code";
				$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (!empty($data['LpuRegion_id'])) {
				if ($data['LpuRegion_id'] == -1) {
					$personCardFilters[] = "LpuRegion_id is null";
				} else {
					$personCardFilters[] = "LpuRegion_id = :LpuRegion_id";
					$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
				}
			}

			if (!empty($data['LpuRegionType_id'])) {
				$personCardFilters[] = "LpuRegionType_id = :LpuRegionType_id";
				$queryParams['LpuRegionType_id'] = $data['LpuRegionType_id'];
			}

			if (!empty($data['PersonCard_begDate'][0])) {
				$personCardFilters[] = "cast(PersonCard_begDate as date) >= :PersonCard_begDateStart";
				$queryParams['PersonCard_begDateStart'] = $data['PersonCard_begDate'][0];
			}

			if (!empty($data['PersonCard_begDate'][1])) {
				$personCardFilters[] = "cast(PersonCard_begDate as date) <= :PersonCard_begDateEnd";
				$queryParams['PersonCard_begDateEnd'] = $data['PersonCard_begDate'][1];
			}

			if (!empty($data['PersonCard_endDate'][0])) {
				$personCardFilters[] = "cast(PersonCard_endDate as date) >= :PersonCard_endDateStart";
				$queryParams['PersonCard_endDateStart'] = $data['PersonCard_endDate'][0];
			}

			if (!empty($data['PersonCard_endDate'][1])) {
				$personCardFilters[] = "cast(PersonCard_endDate as date) <= :PersonCard_endDateEnd";
				$queryParams['PersonCard_endDateEnd'] = $data['PersonCard_endDate'][1];
			}

			if (!empty($data['AttachLpu_id'])) {
				$personCardFilters[] = "Lpu_id = :AttachLpu_id";
				$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
			}

			if (!empty($data['PersonCard_IsAttachCondit'])) {
				$personCardFilters[] = "isnull(PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
				$queryParams['PersonCard_IsAttachCondit'] = $data['PersonCard_IsAttachCondit'];
			}

			if (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] == 1) {
				$personCardFilters[] = "(PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())";
			}

			$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard" . (!empty($data['PersonCardStateType_id']) && $data['PersonCardStateType_id'] == 1 ? "" : "_all") . " with (nolock)
				where " . implode(' and ', $personCardFilters) . "
			)";
		} else if ($this->regionNick == 'ekb' && count($filters) > 1) {
			$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard with (nolock) 
				where (PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())
			)";
		}

		if ($this->regionNick == 'ekb' && count($filters) == 1) {
			$filters[] = "exists (
				select top 1 PersonCard_id
				from v_PersonCard with (nolock) 
				where Lpu_id = :Lpu_id
				and (PersonCard_endDate is null or PersonCard_endDate > dbo.tzGetDate())
			)";
		}

		$filters_str = implode(" and ", $filters);

		if ($this->regionNick == 'ekb') {
			$query = "
				-- variables
				declare @date date = dbo.tzGetDate();
				-- end variables
				-- addit with
				with PERS as (
					select top 100000
						PS.Person_id,
						PS.Person_BirthDay
					from
						v_PersonState PS with(nolock)
						left join [Address] uaddr with (nolock) on uaddr.Address_id = PS.UAddress_id
						left join [Address] paddr with (nolock) on paddr.Address_id = PS.PAddress_id
					where {$filters_str}
				),
				PERS1 as (
					select distinct
						PS.PersonEvn_id,
						PS.Server_id
					from PERS P
					left join v_PersonDeputy PD with(nolock) on PD.Person_id = P.Person_id
					outer apply (
						select case when 
						PD.Person_pid is not null and dbo.Age_newborn(P.Person_BirthDay, @date) < 1
						then 1 else 0 end as flag
					) deputy
					cross apply (
						select top 1 *
						from v_PersonState with(nolock)
						where Person_id in (P.Person_id, PD.Person_pid)
						order by case when 
						(deputy.flag=1 and Person_id = PD.Person_pid) or 
						(deputy.flag=0 and Person_id = P.Person_id) 
						then 0 else 1 end
					) PS
				)
				-- end addit with
				select
					-- select
					PS.Person_id,
					null as Evn_id,
					null as CmpCallCard_id,
					convert(varchar(10), @date, 120) as PersonIdentPackagePos_identDT,
					convert(varchar(10), @date, 120) as PersonIdentPackagePos_identDT2
					-- end select
				from
					-- from
					PERS1 P
					inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = P.PersonEvn_id and PS.Server_id = P.Server_id
					-- end from
				order by
					-- order by
					ps.Person_id
					-- end order by
			";

			$this->load->model('PersonIdentPackage_model');

			try {
				$this->PersonIdentPackage_model->beginTransaction();
				$stat = array('PackageCount' => 0, 'PersonCount' => 0);
				$file_zip_name = $this->PersonIdentPackage_model->createCustomPersonIdentPackages($query, $queryParams, false, $stat);
				if ($stat['PersonCount'] == 0) {
					throw new Exception('Не найдены пациенты для экспорта');
				}
				$this->PersonIdentPackage_model->commitTransaction();
			} catch(Exception $e) {
				$this->PersonIdentPackage_model->rollbackTransaction();
				return $this->createError($e->getCode(), $e->getMessage());
			}
		} else {
			$query = "
				select
					-- select
					top 100000
					ps.Person_SurName as SName
					,ps.Person_FirName as Fi
					,ISNULL(ps.Person_SecName, '-') as Si
					,convert(varchar(10), ps.Person_BirthDay, 112) as BornDt
					,ps.Sex_id as Sex
					,convert(varchar(10), dbo.tzGetDate(), 112) as EntrDt
					,convert(varchar(10), dbo.tzGetDate(), 112) as ReleDt
					-- end select
				from
					-- from
					v_PersonState ps with (nolock)
					left join [Address] uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
					left join [Address] paddr with (nolock) on paddr.Address_id = ps.PAddress_id
					-- end from
				where
					-- where
					{$filters_str}
					-- end where
				order by
					-- order by
					ps.Person_id
					-- end order by
			";

			//echo getDebugSQL($query, $queryParams); exit();

			$out_dir = "ident_" . time();
			if (!file_exists(EXPORTPATH_PC))
				mkdir(EXPORTPATH_PC);
			mkdir(EXPORTPATH_PC . $out_dir);

			$DBF = array(
				array( "SName",		"C",	40,	0),
				array( "Fi",		"C",	40,	0),
				array( "Si",		"C",	40,	0),
				array( "BornDt",	"D",	8,	0),
				array( "Sex",		"N",	1,	0),
				array( "EntrDt",	"D",	8,	0),
				array( "ReleDt",	"D",	8,	0)
			);

			$DBF_FILENAME = EXPORTPATH_PC . $out_dir . "/QuerySCD.dbf";
			$h = dbase_create($DBF_FILENAME, $DBF);
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as $row) {
					// засовываем в DBF-ку.
					array_walk($row, 'ConvertFromUtf8ToCp866');
					dbase_add_record($h, array_values($row));
				}
			}
			dbase_close($h);

			// запаковываем DBF-ку
			$zip = new ZipArchive();
			$file_zip_name = EXPORTPATH_PC . $out_dir . "/00" . date('dHi') . ".SCD";
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($DBF_FILENAME, "QuerySCD.dbf");
			$zip->close();

			unlink($DBF_FILENAME);
		}

		// отдаём пользователю то, что получилось
		if (!file_exists($file_zip_name)) {
			return $this->createError('','Ошибка создания архива экспорта');
		}

		return array(array('Error_Msg' => '', 'filename' => $file_zip_name));
	}

	/**
	 * Запрос всех полей по выбранному человеку
	 * Используется в форме редактирования человека
	 */
	function getPersonEvnEditWindow($data) {
		$sql = "
			declare @curdate datetime = dbo.tzGetDate();

			SELECT TOP 1
				vper.Person_SurName,
				vper.Person_SecName,
				vper.Person_FirName,
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case 
					when PersonPrivilegeFed.Person_id is not null then 1 
					else 0 
				end as Person_IsFedLgot,
				vper.Server_pid,
				vper.Person_id,
				convert(varchar,cast(vper.Person_BirthDay as datetime),104) as Person_BirthDay,
				vper.Sex_id as PersonSex_id,
				vper.Person_Snils as Person_SNILS,
				vper.SocStatus_id,
				vper.FamilyStatus_id,
				vper.PersonFamilyStatus_IsMarried,
				vper.Person_edNum as Federal_Num,
				vper.UAddress_id,
				-- адрес рождения
				baddr.Address_id,
				baddr.KLCountry_id as BKLCountry_id,
				baddr.KLRGN_id as BKLRGN_id,
				baddr.KLSubRGN_id as BKLSubRGN_id,
				baddr.KLCity_id as BKLCity_id,
				baddr.KLTown_id as BKLTown_id,
				baddr.KLStreet_id as BKLStreet_id,
				baddr.Address_House as BAddress_House,
				baddr.Address_Corpus as BAddress_Corpus,
				baddr.Address_Flat as BAddress_Flat,
				baddr.Address_Address as BAddress_AddressText,
				baddr.Address_Address as BAddress_Address,
				baddr.PersonSprTerrDop_id as BPersonSprTerrDop_id,
				-- адрес регистрации
				uaddr.Address_Zip as UAddress_Zip,
				uaddr.KLCountry_id as UKLCountry_id,
				uaddr.KLRGN_id as UKLRGN_id,
				uaddr.KLSubRGN_id as UKLSubRGN_id,
				uaddr.KLCity_id as UKLCity_id,
				uaddr.KLTown_id as UKLTown_id,
				uaddr.KLStreet_id as UKLStreet_id,
				uaddr.Address_House as UAddress_House,
				uaddr.Address_Corpus as UAddress_Corpus,
				uaddr.Address_Flat as UAddress_Flat,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Address as UAddress_Address,
				--convert(varchar,cast(uaddr.Address_begDate as datetime),104) as UAddress_begDate,
				
				uaddr.PersonSprTerrDop_id as UPersonSprTerrDop_id,
				vper.PAddress_id,
				-- адрес фактического проживания
				paddr.Address_Zip as PAddress_Zip,
				paddr.KLCountry_id as PKLCountry_id,
				paddr.KLRGN_id as PKLRGN_id,
				paddr.KLSubRGN_id as PKLSubRGN_id,
				paddr.KLCity_id as PKLCity_id,
				paddr.KLTown_id as PKLTown_id,
				paddr.KLStreet_id as PKLStreet_id,
				paddr.Address_House as PAddress_House,
				paddr.Address_Corpus as PAddress_Corpus,
				paddr.Address_Flat as PAddress_Flat,
				paddr.Address_Address as PAddress_AddressText,
				paddr.Address_Address as PAddress_Address,
				--convert(varchar,cast(paddr.Address_begDate as datetime),104) as PAddress_begDate,
				
				paddr.PersonSprTerrDop_id as PPersonSprTerrDop_id,
				pol.OmsSprTerr_id as OMSSprTerr_id,
				pol.PolisType_id,
				pol.Polis_Ser,
				pol.PolisFormType_id,
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as Polis_Num,
				pol.OrgSmo_id as OrgSMO_id,
				convert(varchar,cast(pol.Polis_begDate as datetime),104) as Polis_begDate,
				convert(varchar,cast(pol.Polis_endDate as datetime),104) as Polis_endDate,
				doc.DocumentType_id,
				doc.Document_Ser,
				doc.Document_Num,
				doc.OrgDep_id as OrgDep_id,
				ns.KLCountry_id,
				ns.LegalStatusVZN_id,
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as NationalityStatus_IsTwoNation,
				pjob.Org_id,
				pjob.OrgUnion_id,
				pjob.Post_id,
				convert(varchar,cast(doc.Document_begDate as datetime),104) as Document_begDate,
				PDEP.DeputyKind_id,
				PDEP.Person_pid as DeputyPerson_id,
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName + ' ' + isnull(PDEPSTATE.Person_FirName, '') + ' ' + isnull(PDEPSTATE.Person_SecName, '') ELSE '' END as DeputyPerson_Fio,
				ResidPlace_id,
				PersonChild_id,
				PersonChild_IsManyChild,
				PersonChild_IsBad,
				PersonChild_IsYoungMother,
				PersonChild_IsIncomplete,
				PersonChild_IsInvalid,
				PersonChild_IsTutor,
				PersonChild_IsMigrant,
				HealthKind_id,
				ph.PersonHeight_IsAbnorm,
				ph.HeightAbnormType_id,
				pw.WeightAbnormType_id,
				pw.PersonWeight_IsAbnorm,
				PCh.PersonSprTerrDop_id,
				FeedingType_id,
				PersonChild_CountChild,
				InvalidKind_id,
				convert(varchar,cast(PersonChild_invDate as datetime),104) as PersonChild_invDate,
				HealthAbnorm_id,
				HealthAbnormVital_id,
				Diag_id,
				null as Person_deadDT,
				null as Person_closeDT,
				--rtrim(vper.PersonPhone_Phone) as PersonPhone_Phone,
				case
					when len(vper.PersonPhone_Phone) = 10 then '(' + left(vper.PersonPhone_Phone, 3) + ')-' + substring(vper.PersonPhone_Phone, 4, 3) + '-' + 
						substring(vper.PersonPhone_Phone, 7, 2) + '-' + right(vper.PersonPhone_Phone, 2)
					else ''
				end as PersonPhone_Phone,
				--rtrim(vper.PersonPhone_Comment) as PersonPhone_Comment,
				rtrim(per.Person_Comment) as Person_Comment,
				rtrim(vper.PersonInn_Inn) as PersonInn_Inn,
				rtrim(vper.PersonSocCardNum_SocCardNum) as PersonSocCardNum_SocCardNum,
				rtrim(pr.PersonRefuse_IsRefuse) as PersonRefuse_IsRefuse,
				rtrim(pce.PersonCarExist_IsCar) as PersonCarExist_IsCar,
				rtrim(pche.PersonChildExist_IsChild) as PersonChildExist_IsChild,
				ph.PersonHeight_Height as PersonHeight_Height,
				ISNULL(pw.Okei_id, 37) as Okei_id,
				pw.PersonWeight_Weight as PersonWeight_Weight,
				pi.Ethnos_id,
				mop.OnkoOccupationClass_id as OnkoOccupationClass_id,
				per.BDZ_id,
				per.BDZ_Guid,
				pol.Polis_Guid,
				IsUnknown.YesNo_Code as Person_IsUnknown,
				IsAnonym.YesNo_Code as Person_IsAnonym,
				ISNULL(per.Person_IsNotINN, 1) as Person_IsNotINN
			from v_Person_all vper with (nolock)
			left join v_Person per with (nolock) on per.Person_id=vper.Person_id
			left join v_PersonRefuse PR (nolock) on PR.Person_id = vper.Person_id and PR.PersonRefuse_Year = YEAR(@curdate)
			left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			left join v_Address paddr with (nolock) on vper.PAddress_id = paddr.Address_id
			-- Адрес рождения
			left join PersonBirthPlace pbp with (nolock) on vper.Person_id = pbp.Person_id
			left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
			-- end. Адрес рождения
			left join Polis pol with (nolock) on pol.Polis_id=vper.Polis_id
			left join Document doc with (nolock) on doc.Document_id=vper.Document_id
			left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = vper.NationalityStatus_id
			left join PersonInfo pi with (nolock) on pi.Person_id = vper.Person_id
			left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
			left join PersonDeputy PDEP with (nolock) on PDEP.Person_id = vper.Person_id
			left join v_PersonState PDEPSTATE with (nolock) on PDEPSTATE.Person_id = PDEP.Person_pid
			left join PersonChild PCh with (nolock) on PCh.Person_id = vper.Person_id
			left join v_YesNo IsUnknown with(nolock) on IsUnknown.YesNo_id = isnull(per.Person_IsUnknown,1)
			left join v_YesNo IsAnonym with(nolock) on IsAnonym.YesNo_id = isnull(per.Person_IsAnonym,1)
			outer apply (
				select top 1 
					OnkoOccupationClass_id
				from
					v_MorbusOnkoPerson with (nolock)
				where
					Person_id = :Person_id
				order by
					MorbusOnkoPerson_insDT desc
			) as mop
			outer apply (
				select top 1 
					PersonCarExist_IsCar
				from
					PersonCarExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonCarExist_setDT desc
			) as pce
			outer apply (
				select top 1 
					PersonChildExist_IsChild
				from
					PersonChildExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonChildExist_setDT desc
			) as pche
			outer apply (
				select top 1 
					PersonHeight_Height,
					PersonHeight_IsAbnorm,
					HeightAbnormType_id
				from
					PersonHeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonHeight_setDT desc
			) as ph
			outer apply (
				select top 1 
					PersonWeight_Weight,
					WeightAbnormType_id,
					PersonWeight_IsAbnorm,
					Okei_id
				from
					PersonWeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonWeight_setDT desc
			) as pw
			-- федеральный льготник
			outer apply (
				select top 1
					pp.Person_id
				from
					v_PersonPrivilege pp with (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pt.ReceptFinance_id = 1
					and pp.PersonPrivilege_begDate <= @curdate
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(@curdate as date))
					and pp.Person_id = vper.Person_id
			) PersonPrivilegeFed
			where vper.Person_id= :Person_id
			and vper.PersonEvn_id= :PersonEvn_id
		";
		/* echo getDebugSQL($sql,
		  array(
		  'Person_id' => $data['person_id'],
		  'PersonEvn_id' => $data['PersonEvn_id']
		  ));exit(); */
		$res = $this->db->query(
				$sql, array(
			'Person_id' => $data['person_id'],
			'PersonEvn_id' => $data['PersonEvn_id']
				)
		);
		if (is_object($res)) {
			$return = $res->result('array');

			if (empty($return[0])) {
				return array(array('Error_Msg'=>'Не удалось найти периодику по указанному случаю, возможно она была изменена. Попробуйте обновить список случаев реестра.'));
			}

			// если порожден электронной регистратурой, то отправляем сразу его с открытым на редактирование
			if ($return[0]['Server_pid'] == 3) {
				$return[0]["Servers_ids"] = "[3]";
				return $return;
			}
			$sql = "
				SELECT 
					distinct Server_id
				FROM
					v_Person_all with (nolock)
				WHERE
					Person_id = :Person_id
				union all
				select case when exists(
					SELECT
						personprivilege_id 
					FROM
						personprivilege reg with (nolock)
						left join PrivilegeType pt with(nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					WHERE
						reg.person_id = :Person_id
						--and reg.privilegetype_id <= 249
						and pt.ReceptFinance_id = 1
						and reg.personprivilege_begdate <= dbo.tzGetDate()
						and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))) 
				then 1 end
			";
			$res = $this->db->query(
					$sql, array(
				'Person_id' => $data['person_id']
					)
			);
			if (is_object($res)) {
				$servers = $res->result('array');
				$servers_arr = array();
				$sys_server_flag = false;
				foreach ($servers as $row) {
					if ($row['Server_id'] != '') {
						if ($return[0]['Server_pid'] > 0 && $row['Server_id'] == 0)
							continue;
						$servers_arr[] = $row['Server_id'];
						if ($row['Server_id'] == 1 || $row['Server_id'] == 0)
							$sys_server_flag = true;
					}
				}
				if ($sys_server_flag === true) {
					$servers_new_arr = array();
					foreach ($servers_arr as $row) {
						if ($return[0]['Server_pid'] > 0 && $row == 0)
							continue;
						if ($row == 1 || $row == 0) {
							$servers_new_arr[] = $row;
						}
					}
					$servers_arr = $servers_new_arr;
				}
				// если суперадмин, то отсылаем его для предоставления возможности редактирования недоступных полей
				if (preg_match("/SuperAdmin/u", $data['session']['groups']))
					$servers_str = "['SuperAdmin']";
				else
					$servers_str = "[" . implode(", ", $servers_arr) . "]";
				$return[0]["Servers_ids"] = $servers_str;
				return $return;
			}
			else
				return false;
		}
		else
			return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getPersonEvnIdByEvnId($data) {
		$sql = "select PersonEvn_id,Server_id from v_Evn with (nolock) where Evn_id = :Evn_id";
		$params = array('Evn_id' => $data['Evn_id']);
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$return = $res->result('array');
			if (count($return) > 0) {
				return $return;
			}
			return false;
		}
		return false;
	}

	/**
	 * TODO: Запрос всех полей по выбранному человеку
	 * Используется в форме редактирования человека
	 */
	function getPersonEditWindow($data) {
		$snils_sepatator = ($data['session']['region']['nick']=='astra')?"' '":"'-'";
		$sql = "
			declare @curdate datetime = dbo.tzGetDate();

			SELECT TOP 1
				vper.Person_SurName,
				vper.Person_SecName,
				vper.Person_FirName,
				vper.PersonState_IsSnils,
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case
					when PersonPrivilegeFed.Person_id is not null then 1 
					else 0 
				end as Person_IsFedLgot,
				vper.Server_pid,
				vper.Person_IsInErz,
				vper.PersonIdentState_id,
				vper.Person_id,
				convert(varchar,cast(vper.Person_BirthDay as datetime),104) as Person_BirthDay,
				vper.Sex_id as PersonSex_id,
				case
					when len(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) + '-' + substring(vper.Person_Snils, 4, 3) + '-' + 
						substring(vper.Person_Snils, 7, 3) + {$snils_sepatator} + right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as Person_SNILS,
				vper.SocStatus_id,
				vper.FamilyStatus_id,
				vper.PersonFamilyStatus_IsMarried,
				vper.Person_edNum as Federal_Num,
				vper.UAddress_id,
				vper.PersonEduLevel_id,
				vper.EducationLevel_id,
				vper.PersonEmployment_id,
				vper.Employment_id,
				uaddr.PersonSprTerrDop_id as UPersonSprTerrDop_id,
				uaddr.Address_Zip as UAddress_Zip,
				uaddr.KLCountry_id as UKLCountry_id,
				uaddr.KLRGN_id as UKLRGN_id,
				uaddr.KLSubRGN_id as UKLSubRGN_id,
				uaddr.KLCity_id as UKLCity_id,
				uaddr.KLTown_id as UKLTown_id,
				uaddr.KLStreet_id as UKLStreet_id,
				uaddr.Address_House as UAddress_House,
				uaddr.Address_Corpus as UAddress_Corpus,
				uaddr.Address_Flat as UAddress_Flat,
				uaddrsp.AddressSpecObject_id as UAddressSpecObject_id,
				uaddrsp.AddressSpecObject_Name as UAddressSpecObject_Value,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Address as UAddress_Address,
				--convert(varchar,cast(uaddr.Address_begDate as datetime),104) as UAddress_begDate,

				baddr.PersonSprTerrDop_id as BPersonSprTerrDop_id,
				baddr.Address_id,
				baddr.KLCountry_id as BKLCountry_id,
				baddr.KLRGN_id as BKLRGN_id,
				baddr.KLSubRGN_id as BKLSubRGN_id,
				baddr.KLCity_id as BKLCity_id,
				baddr.KLTown_id as BKLTown_id,
				baddr.KLStreet_id as BKLStreet_id,
				baddr.Address_House as BAddress_House,
				baddr.Address_Corpus as BAddress_Corpus,
				baddr.Address_Flat as BAddress_Flat,
				baddrsp.AddressSpecObject_id as BAddressSpecObject_id,
				baddrsp.AddressSpecObject_Name as BAddressSpecObject_Value,
				baddr.Address_Zip as BAddress_Zip,
				baddr.Address_Address as BAddress_AddressText,
				baddr.Address_Address as BAddress_Address,
				pcc.PolisCloseCause_Code as polisCloseCause,
				vper.PAddress_id,
				paddr.PersonSprTerrDop_id as PPersonSprTerrDop_id,
				paddr.Address_Zip as PAddress_Zip,
				paddr.KLCountry_id as PKLCountry_id,
				paddr.KLRGN_id as PKLRGN_id,
				paddr.KLSubRGN_id as PKLSubRGN_id,
				paddr.KLCity_id as PKLCity_id,
				paddr.KLTown_id as PKLTown_id,
				paddr.KLStreet_id as PKLStreet_id,
				paddr.Address_House as PAddress_House,
				paddr.Address_Corpus as PAddress_Corpus,
				paddr.Address_Flat as PAddress_Flat,
				paddrsp.AddressSpecObject_id as PAddressSpecObject_id,
				paddrsp.AddressSpecObject_Name as PAddressSpecObject_Value,
				paddr.Address_Address as PAddress_AddressText,
				paddr.Address_Address as PAddress_Address,
				--convert(varchar,cast(paddr.Address_begDate as datetime),104) as PAddress_begDate,

				pi.Nationality_id as PersonNationality_id,
				pol.OmsSprTerr_id as OMSSprTerr_id,
				pol.PolisType_id,
				pol.Polis_Ser,
				pol.PolisFormType_id,
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as Polis_Num,
				pol.OrgSmo_id as OrgSMO_id,
				convert(varchar,cast(pol.Polis_begDate as datetime),104) as Polis_begDate,
				convert(varchar,cast(pol.Polis_endDate as datetime),104) as Polis_endDate,
				doc.DocumentType_id,
				doc.Document_Ser,
				doc.Document_Num,
				doc.OrgDep_id as OrgDep_id,
				ns.KLCountry_id,
				ns.LegalStatusVZN_id,
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as NationalityStatus_IsTwoNation,
				pjob.Org_id,
				pjob.OrgUnion_id,
				pjob.Post_id,
				convert(varchar,cast(doc.Document_begDate as datetime),104) as Document_begDate,
				PDEP.DeputyKind_id,
				PDEP.Person_pid as DeputyPerson_id,
				DDEP.DocumentAuthority_id,
				DDEP.DocumentDeputy_Ser,
				DDEP.DocumentDeputy_Num,
				DDEP.DocumentDeputy_Issue,
				convert(varchar,cast(DDEP.DocumentDeputy_begDate as datetime),104) as DocumentDeputy_begDate,
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName + ' ' + isnull(PDEPSTATE.Person_FirName, '') + ' ' + isnull(PDEPSTATE.Person_SecName, '') ELSE '' END as DeputyPerson_Fio,
				ResidPlace_id,
				PersonChild_id,
				PersonChild_IsManyChild,
				PersonChild_IsBad,
				PersonChild_IsYoungMother,
				PersonChild_IsIncomplete,
				PersonChild_IsInvalid,
				PersonChild_IsTutor,
				PersonChild_IsMigrant,
				HealthKind_id,
				ph.PersonHeight_IsAbnorm,
				ph.HeightAbnormType_id,
				pw.WeightAbnormType_id,
				pw.PersonWeight_IsAbnorm,
				PCh.PersonSprTerrDop_id,
				FeedingType_id,
				PersonChild_CountChild,
				InvalidKind_id,
				convert(varchar,cast(PersonChild_invDate as datetime),104) as PersonChild_invDate,
				HealthAbnorm_id,
				HealthAbnormVital_id,
				Diag_id,
				convert(varchar,cast(vper.Person_deadDT as datetime),104) as Person_deadDT,
				convert(varchar,cast(vper.Person_closeDT as datetime),104) as Person_closeDT,
				--rtrim(vper.Person_Phone) as PersonPhone_Phone,
				case
					when len(vper.Person_Phone) = 10 then '(' + left(vper.Person_Phone, 3) + ')-' + substring(vper.Person_Phone, 4, 3) + '-' + 
						substring(vper.Person_Phone, 7, 2) + '-' + right(vper.Person_Phone, 2)
					else ''
				end as PersonPhone_Phone,
				--rtrim(vper.PersonPhone_Comment) as PersonPhone_Comment,
				case
					when isnull(PPH.PersonPhoneStatus_id, 1) = 3 and len(PPH.PersonPhone_Phone) = 10
					then '(' + left(PPH.PersonPhone_Phone, 3) + ')-' + substring(PPH.PersonPhone_Phone, 4, 3) + '-' + 
						substring(PPH.PersonPhone_Phone, 7, 2) + '-' + right(PPH.PersonPhone_Phone, 2)
					else ''
				end as PersonPhone_VerifiedPhone,
				rtrim(per.Person_Comment) as Person_Comment,
				rtrim(pi.PersonInfo_InternetPhone) as PersonInfo_InternetPhone,
				rtrim(vper.Person_Inn) as PersonInn_Inn,
				rtrim(vper.Person_SocCardNum) as PersonSocCardNum_SocCardNum,
				rtrim(Ref.PersonRefuse_IsRefuse) as PersonRefuse_IsRefuse,
				rtrim(pce.PersonCarExist_IsCar) as PersonCarExist_IsCar,
				rtrim(pche.PersonChildExist_IsChild) as PersonChildExist_IsChild,
				ph.PersonHeight_Height as PersonHeight_Height,
				ISNULL(pw.Okei_id, 37) as Okei_id,
				pw.PersonWeight_Weight as PersonWeight_Weight,
				-- признак того, что человек БДЗшный и у него закончился полис и можно дать ввести иногородний
				CASE WHEN
					vper.Server_pid = 0
					and pol.Polis_endDate is not null
					and pol.Polis_endDate < @curdate
				THEN
					1
				ELSE
					0
				END as Polis_CanAdded,
				pi.Ethnos_id,
				mop.OnkoOccupationClass_id as OnkoOccupationClass_id,
				per.BDZ_id,
				per.BDZ_Guid,
				pol.Polis_Guid,
				IsUnknown.YesNo_Code as Person_IsUnknown,
				IsAnonym.YesNo_Code as Person_IsAnonym,
				ISNULL(per.Person_IsNotINN, 1) as Person_IsNotINN,
				case when PCitySocr.KLSocr_Nick in ('Г','ПГТ') then 1 else 0 end as CitizenType
			from v_PersonState vper with (nolock)
			left join v_Person per with (nolock) on per.Person_id=vper.Person_id
			left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			left join v_AddressSpecObject uaddrsp with (nolock) on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
			left join v_Address paddr with (nolock) on vper.PAddress_id = paddr.Address_id
			left join v_AddressSpecObject paddrsp with (nolock) on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
			left join v_KLRgn PRgn with(nolock) on PRgn.KLRgn_id = paddr.KLRgn_id
			left join v_KLCity PCity with(nolock) on PCity.KLCity_id = paddr.KLCity_id
			left join v_KLTown PTown with(nolock) on PTown.KLTown_id = paddr.KLTown_id
			left join v_KLSocr PCitySocr with(nolock) on PCitySocr.KLSocr_id = coalesce(PTown.KLSocr_id, PCity.KLSocr_id, PRgn.KLSocr_id)
			-- Адрес рождения
			left join PersonBirthPlace pbp with (nolock) on vper.Person_id = pbp.Person_id
			left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
			left join v_AddressSpecObject baddrsp with (nolock) on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
			-- end. Адрес рождения

			left join Polis pol with (nolock) on pol.Polis_id=vper.Polis_id
			left join v_PolisCloseCause pcc (nolock) on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
			left join Document doc with (nolock) on doc.Document_id=vper.Document_id
			left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = vper.NationalityStatus_id
			left join PersonInfo pi with (nolock) on pi.Person_id = vper.Person_id
			left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
			left join PersonDeputy PDEP with (nolock) on PDEP.Person_id = vper.Person_id
			left join DocumentDeputy DDEP with (nolock) on DDEP.DocumentDeputy_id = PDEP.DocumentDeputy_id
			left join v_PersonState PDEPSTATE with (nolock) on PDEPSTATE.Person_id = PDEP.Person_pid
			left join PersonChild PCh with (nolock) on PCh.Person_id = vper.Person_id
			left join v_YesNo IsUnknown with(nolock) on IsUnknown.YesNo_id = isnull(per.Person_IsUnknown,1)
			left join v_YesNo IsAnonym with(nolock) on IsAnonym.YesNo_id = isnull(per.Person_IsAnonym,1)
			-- федеральный льготник
			-- полис, который был в истории периодик
			-- outer apply (select top 1 pls1.Person_id, pls1.BDZ_id, pls1.Polis_endDate, pls1.PolisCloseCause_id from v_PersonPolis pls1 with (nolock) where pls1.Person_id = vper.Person_id and pls1.BDZ_id is not null order by Polis_begDate desc) as bdz_pol
			outer apply (
				select top 1
					pp.Person_id
				from
					v_PersonPrivilege pp with (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pt.ReceptFinance_id = 1
					and pp.PersonPrivilege_begDate <= @curdate
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(@curdate as date))
					and pp.Person_id = vper.Person_id
			) PersonPrivilegeFed
			outer apply (
				select top 1 
					OnkoOccupationClass_id
				from
					v_MorbusOnkoPerson with (nolock)
				where
					Person_id = :Person_id
				order by
					MorbusOnkoPerson_insDT desc
			) as mop
			outer apply (
				select top 1 
					PersonRefuse_IsRefuse
				from
					v_PersonRefuse with (nolock)
				where
					Person_id = :Person_id
					and PersonRefuse_Year = year(@curdate)
				order by
					PersonRefuse_insDT desc
			) as Ref
			outer apply (
				select top 1 
					PersonCarExist_IsCar
				from
					PersonCarExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonCarExist_setDT desc
			) as pce
			outer apply (
				select top 1 
					PersonChildExist_IsChild
				from
					PersonChildExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonChildExist_setDT desc
			) as pche
			outer apply (
				select top 1 
					PersonHeight_Height,
					PersonHeight_IsAbnorm,
					HeightAbnormType_id
				from
					PersonHeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonHeight_setDT desc
			) as ph
			outer apply (
				select top 1 
					PersonWeight_Weight,
					WeightAbnormType_id,
					PersonWeight_IsAbnorm,
					Okei_id
				from
					PersonWeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonWeight_setDT desc
			) as pw
			outer apply (
				select top 1
					PP.PersonPhone_Phone,
					PPH.PersonPhoneStatus_id
				from
					v_PersonPhoneHist PPH with(nolock)
					left join v_PersonPhone PP with(nolock) on PP.PersonPhone_id = PPH.PersonPhone_id
				where 
					PPH.Person_id = vper.Person_id
				order by 
					PPH.PersonPhoneHist_insDT desc
			) PPH
			where vper.Person_id= :Person_id
		";
		 /*echo getDebugSQL($sql, array(
		  'Person_id' => $data['person_id']
		  ));exit(); */
		$res = $this->db->query(
				$sql, array(
			'Person_id' => $data['person_id']
				)
		);
		if (is_object($res)) {

			$return = $res->result('array');
			if (count($return) > 0) {
				// если порожден электронной регистратурой, то отправляем сразу его с открытым на редактирование
				if ($return[0]['Server_pid'] == 3) {
					$return[0]["Servers_ids"] = "[3]";
					return $return;
				}
				$sql = "
					declare @curdate datetime = dbo.tzGetDate();

					SELECT 
						distinct Server_id
					FROM
						v_Person_all with (nolock)
					WHERE
						Person_id = :Person_id
					union all
					select case when exists(
						SELECT top 1
							PersonPrivilege_id
						FROM
							v_PersonPrivilege reg with (nolock)
							inner join PrivilegeType pt with(nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
						WHERE
							reg.person_id = :Person_id
							and pt.ReceptFinance_id = 1
							and reg.personprivilege_begdate <= @curdate
							and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), @curdate, 112) as datetime)))
					then 1 end
				";

				$res = $this->db->query(
						$sql, array(
					'Person_id' => $data['person_id']
						)
				);
				if (is_object($res)) {
					$servers = $res->result('array');
					$servers_arr = array();
					$sys_server_flag = false;
					foreach ($servers as $row) {
						if ($row['Server_id'] != '') {
							if ($return[0]['Server_pid'] > 0 && $row['Server_id'] == 0)
								continue;
							$servers_arr[] = $row['Server_id'];
							if ($row['Server_id'] == 1 || $row['Server_id'] == 0)
								$sys_server_flag = true;
						}
					}
					if ($sys_server_flag === true) {
						$servers_new_arr = array();
						foreach ($servers_arr as $value) {
							if ($return[0]['Server_pid'] > 0 && $value == 0)
								continue;
							if ($value == 1 || $value == 0) {
								$servers_new_arr[] = $value;
							}
						}
						$servers_arr = $servers_new_arr;
					}
					// если суперадмин, то отсылаем его для предоставления возможности редактирования недоступных полей
					if (preg_match("/SuperAdmin/u", $data['session']['groups']))
						$servers_str = "['SuperAdmin']";
					else
						$servers_str = "[" . implode(", ", $servers_arr) . "]";
					$return[0]["Servers_ids"] = $servers_str;
					return $return;
				}
				else
					return false;
			}
			else {
				// Ошибкама - по этому Person_id человечка не нашли
				return false;
			}
		}
		else
			return false;
	}

	/**
	 *
	 * @param type $value ff
	 */
	function addSpecObject($value,$pmUser_id){
		$query = "
            select 
				AddressSpecObject_id
			from
				AddressSpecObject with (nolock)
			where AddressSpecObject_Name = :value
        ";
        $result = $this->db->query($query,array('value'=>$value));
		
        if(is_object($result)){
			
            $result = $result->result('array');
            if(is_array($result) && count($result)>0){
				
				return $result[0]['AddressSpecObject_id'];
			}else{
				
				$query = '
				declare
				@AddressSpecObject_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

				exec p_AddressSpecObject_ins
				@AddressSpecObject_id = @AddressSpecObject_id OUTPUT
				,@AddressSpecObject_Name = :value
				,@pmUser_id = :pmUser_id
				,@Error_Code = @ErrCode output
				,@Error_Message = @ErrMessage output;
				select @AddressSpecObject_id as AddressSpecObject_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$result = $this->db->query($query,array('value'=>$value,'pmUser_id'=>$pmUser_id));
				if(is_object($result)){
					$result = $result->result('array');
					//print_r($result);
					return $result[0]['AddressSpecObject_id'];
				}
			}
		}
		
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function getBDZPersonData($data){
		//print_r($data);exit();
		$filter='';
		$params = array();
		if(isset($data['Person_id'])){
			$filter .= 'and vper.Person_id!=:Person_id';
			$params['Person_id']=$data['Person_id'];
		}
		$sql = "
			declare @curdate datetime = dbo.tzGetDate();

			SELECT TOP 1
				vper.Person_SurName,
				vper.Person_SecName,
				vper.Person_FirName,
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case
					when PersonPrivilegeFed.Person_id is not null then 1 
					else 0 
				end as Person_IsFedLgot,
				vper.Server_pid,
				vper.Person_id,
				convert(varchar,cast(vper.Person_BirthDay as datetime),104) as Person_BirthDay,
				vper.Sex_id as PersonSex_id,
				case
					when len(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) + '-' + substring(vper.Person_Snils, 4, 3) + '-' + 
						substring(vper.Person_Snils, 7, 3) + '-' + right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as Person_SNILS,
				vper.SocStatus_id,
				vper.FamilyStatus_id,
				vper.PersonFamilyStatus_IsMarried,
				vper.Person_edNum as Federal_Num,
				vper.UAddress_id,
				uaddr.PersonSprTerrDop_id as UPersonSprTerrDop_id,
				uaddr.Address_Zip as UAddress_Zip,
				uaddr.KLCountry_id as UKLCountry_id,
				uaddr.KLRGN_id as UKLRGN_id,
				uaddr.KLSubRGN_id as UKLSubRGN_id,
				uaddr.KLCity_id as UKLCity_id,
				uaddr.KLTown_id as UKLTown_id,
				uaddr.KLStreet_id as UKLStreet_id,
				uaddr.Address_House as UAddress_House,
				uaddr.Address_Corpus as UAddress_Corpus,
				uaddr.Address_Flat as UAddress_Flat,
				uaddrsp.AddressSpecObject_id as UAddressSpecObject_id,
				uaddrsp.AddressSpecObject_Name as UAddressSpecObject_Value,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Address as UAddress_Address,
				--convert(varchar,cast(uaddr.Address_begDate as datetime),104) as UAddress_begDate,

				baddr.PersonSprTerrDop_id as BPersonSprTerrDop_id,
				baddr.Address_id,
				baddr.KLCountry_id as BKLCountry_id,
				baddr.KLRGN_id as BKLRGN_id,
				baddr.KLSubRGN_id as BKLSubRGN_id,
				baddr.KLCity_id as BKLCity_id,
				baddr.KLTown_id as BKLTown_id,
				baddr.KLStreet_id as BKLStreet_id,
				baddr.Address_House as BAddress_House,
				baddr.Address_Corpus as BAddress_Corpus,
				baddr.Address_Flat as BAddress_Flat,
				baddrsp.AddressSpecObject_id as BAddressSpecObject_id,
				baddrsp.AddressSpecObject_Name as BAddressSpecObject_Value,
				baddr.Address_Zip as BAddress_Zip,
				baddr.Address_Address as BAddress_AddressText,
				baddr.Address_Address as BAddress_Address,
				pcc.PolisCloseCause_Code as polisCloseCause,
				vper.PAddress_id,
				paddr.PersonSprTerrDop_id as PPersonSprTerrDop_id,
				paddr.Address_Zip as PAddress_Zip,
				paddr.KLCountry_id as PKLCountry_id,
				paddr.KLRGN_id as PKLRGN_id,
				paddr.KLSubRGN_id as PKLSubRGN_id,
				paddr.KLCity_id as PKLCity_id,
				paddr.KLTown_id as PKLTown_id,
				paddr.KLStreet_id as PKLStreet_id,
				paddr.Address_House as PAddress_House,
				paddr.Address_Corpus as PAddress_Corpus,
				paddr.Address_Flat as PAddress_Flat,
				paddrsp.AddressSpecObject_id as PAddressSpecObject_id,
				paddrsp.AddressSpecObject_Name as PAddressSpecObject_Value,
				paddr.Address_Address as PAddress_AddressText,
				paddr.Address_Address as PAddress_Address,
				--convert(varchar,cast(paddr.Address_begDate as datetime),104) as PAddress_begDate,

				pi.Nationality_id as PersonNationality_id,
				pol.OmsSprTerr_id as OMSSprTerr_id,
				pol.PolisType_id,
				pol.Polis_Ser,
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as Polis_Num,
				pol.OrgSmo_id as OrgSMO_id,
				convert(varchar,cast(pol.Polis_begDate as datetime),104) as Polis_begDate,
				convert(varchar,cast(pol.Polis_endDate as datetime),104) as Polis_endDate,
				doc.DocumentType_id,
				doc.Document_Ser,
				doc.Document_Num,
				doc.OrgDep_id as OrgDep_id,
				ns.KLCountry_id,
				ns.LegalStatusVZN_id,
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as NationalityStatus_IsTwoNation,
				pjob.Org_id,
				pjob.OrgUnion_id,
				pjob.Post_id,
				convert(varchar,cast(doc.Document_begDate as datetime),104) as Document_begDate,
				PDEP.DeputyKind_id,
				PDEP.Person_pid as DeputyPerson_id,
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName + ' ' + isnull(PDEPSTATE.Person_FirName, '') + ' ' + isnull(PDEPSTATE.Person_SecName, '') ELSE '' END as DeputyPerson_Fio,
				ResidPlace_id,
				PersonChild_id,
				PersonChild_IsManyChild,
				PersonChild_IsBad,
				PersonChild_IsYoungMother,
				PersonChild_IsIncomplete,
				PersonChild_IsInvalid,
				PersonChild_IsTutor,
				PersonChild_IsMigrant,
				HealthKind_id,
				ph.PersonHeight_IsAbnorm,
				ph.HeightAbnormType_id,
				pw.WeightAbnormType_id,
				pw.PersonWeight_IsAbnorm,
				PCh.PersonSprTerrDop_id,
				FeedingType_id,
				PersonChild_CountChild,
				InvalidKind_id,
				convert(varchar,cast(PersonChild_invDate as datetime),104) as PersonChild_invDate,
				HealthAbnorm_id,
				HealthAbnormVital_id,
				Diag_id,
				convert(varchar,cast(vper.Person_deadDT as datetime),104) as Person_deadDT,
				convert(varchar,cast(vper.Person_closeDT as datetime),104) as Person_closeDT,
				rtrim(vper.Person_Phone) as PersonPhone_Phone,
				rtrim(pi.PersonInfo_InternetPhone) as PersonInfo_InternetPhone,
				rtrim(vper.Person_Inn) as PersonInn_Inn,
				rtrim(vper.Person_SocCardNum) as PersonSocCardNum_SocCardNum,
				rtrim(Ref.PersonRefuse_IsRefuse) as PersonRefuse_IsRefuse,
				rtrim(pce.PersonCarExist_IsCar) as PersonCarExist_IsCar,
				rtrim(pche.PersonChildExist_IsChild) as PersonChildExist_IsChild,
				ph.PersonHeight_Height as PersonHeight_Height,
				ISNULL(pw.Okei_id, 37) as Okei_id,
				pw.PersonWeight_Weight as PersonWeight_Weight,
				-- признак того, что человек БДЗшный и у него закончился полис и можно дать ввести иногородний
				CASE WHEN
					vper.Server_pid = 0
					and pol.Polis_endDate is not null
					and pol.Polis_endDate < @curdate
				THEN
					1
				ELSE
					0
				END as Polis_CanAdded,
				pi.Ethnos_id,
				mop.OnkoOccupationClass_id as OnkoOccupationClass_id,
				per.BDZ_Guid,
				pol.Polis_Guid
			from v_PersonState vper with (nolock)
			left join v_Person per with (nolock) on per.Person_id=vper.Person_id
			left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			left join v_AddressSpecObject uaddrsp with (nolock) on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
			left join v_Address paddr with (nolock) on vper.PAddress_id = paddr.Address_id
			left join v_AddressSpecObject paddrsp with (nolock) on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
			-- Адрес рождения
			left join PersonBirthPlace pbp with (nolock) on vper.Person_id = pbp.Person_id
			left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
			left join v_AddressSpecObject baddrsp with (nolock) on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
			-- end. Адрес рождения

			left join Polis pol with (nolock) on pol.Polis_id=vper.Polis_id
			left join v_PolisCloseCause pcc (nolock) on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
			left join Document doc with (nolock) on doc.Document_id=vper.Document_id
			left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = vper.NationalityStatus_id
			left join PersonInfo pi with (nolock) on pi.Person_id = vper.Person_id
			left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
			left join PersonDeputy PDEP with (nolock) on PDEP.Person_id = vper.Person_id
			left join v_PersonState PDEPSTATE with (nolock) on PDEPSTATE.Person_id = PDEP.Person_pid
			left join PersonChild PCh with (nolock) on PCh.Person_id = vper.Person_id
			-- федеральный льготник
			-- полис, который был в истории периодик
			-- outer apply (select top 1 pls1.Person_id, pls1.BDZ_id, pls1.Polis_endDate, pls1.PolisCloseCause_id from v_PersonPolis pls1 with (nolock) where pls1.Person_id = vper.Person_id and pls1.BDZ_id is not null order by Polis_begDate desc) as bdz_pol
			outer apply (
				select top 1
					pp.Person_id
				from
					v_PersonPrivilege pp with (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pt.ReceptFinance_id = 1
					and pp.PersonPrivilege_begDate <= @curdate
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(@curdate as date))
					and pp.Person_id = vper.Person_id
			) PersonPrivilegeFed
			outer apply (
				select top 1 
					OnkoOccupationClass_id
				from
					v_MorbusOnkoPerson with (nolock)
				where
					Person_id = vper.Person_id
				order by
					MorbusOnkoPerson_insDT desc
			) as mop
			outer apply (
				select top 1 
					PersonRefuse_IsRefuse
				from
					v_PersonRefuse with (nolock)
				where
					Person_id = vper.Person_id
					and PersonRefuse_Year = year(@curdate)
				order by
					PersonRefuse_insDT desc
			) as Ref
			outer apply (
				select top 1 
					PersonCarExist_IsCar
				from
					PersonCarExist with (nolock)
				where
					Person_id = vper.Person_id
				order by
					PersonCarExist_setDT desc
			) as pce
			outer apply (
				select top 1 
					PersonChildExist_IsChild
				from
					PersonChildExist with (nolock)
				where
					Person_id = vper.Person_id
				order by
					PersonChildExist_setDT desc
			) as pche
			outer apply (
				select top 1 
					PersonHeight_Height,
					PersonHeight_IsAbnorm,
					HeightAbnormType_id
				from
					PersonHeight with (nolock)
				where
					Person_id = vper.Person_id
				order by
					PersonHeight_setDT desc
			) as ph
			outer apply (
				select top 1 
					PersonWeight_Weight,
					WeightAbnormType_id,
					PersonWeight_IsAbnorm,
					Okei_id
				from
					PersonWeight with (nolock)
				where
					Person_id = vper.Person_id
				order by
					PersonWeight_setDT desc
			) as pw
			where per.BDZ_Guid=:BDZ_Guid
			".$filter."
			--
		";
		 /*echo getDebugSQL($sql, array(
		  ));exit();*/
		$params['BDZ_Guid'] = $data['BDZ_Guid'];
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {

			$return = $res->result('array');
			if(count($return)==1){
				//print_r($return[0]);exit();
				return $return[0];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Сохранение информации о человеке
	 */
	public function savePersonInfo($data) {
		$query = "
			select top 1
				PI.Server_id,
				PI.PersonInfo_id,
				PI.UPersonSprTerrDop_id,
				PI.PPersonSprTerrDop_id,
				PI.BPersonSprTerrDop_id,
				PI.PersonInfo_InternetPhone,
				PI.Nationality_id,
				PI.Ethnos_id,
				PI.PersonInfo_IsSetDeath,
				PI.PersonInfo_IsParsDeath,
				PI.Person_BDZCode,
				PI.PersonInfo_Email
			from PersonInfo PI with(nolock)
			where PI.Person_id = :Person_id
		";
		$resp = $this->queryResult($query, array(
			'Person_id' => $data['Person_id']
		));
		if (!is_array($resp)) {
			return $this->createError('', 'Ошибка при получении информации о человеке');
		}

		if (count($resp) == 0) {
			$procedure = 'p_PersonInfo_ins';
		} else {
			$procedure = 'p_PersonInfo_upd';

			foreach($resp[0] as $field => $value) {
				if (!isset($data[$field])) {
					$data[$field] = $value;
				}
			}
		}

		$query = "
			declare
				@PersonInfo_id bigint = :PersonInfo_id,
				@ErrCode int,
				@ErrMsg varchar(4000);
			exec {$procedure}
				@PersonInfo_id = @PersonInfo_id output,
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@UPersonSprTerrDop_id = :UPersonSprTerrDop_id,
				@PPersonSprTerrDop_id = :PPersonSprTerrDop_id,
				@BPersonSprTerrDop_id = :BPersonSprTerrDop_id,
				@PersonInfo_InternetPhone = :PersonInfo_InternetPhone,
				@Nationality_id = :Nationality_id,
				@Ethnos_id = :Ethnos_id,
				@PersonInfo_IsSetDeath = :PersonInfo_IsSetDeath,
				@PersonInfo_IsParsDeath = :PersonInfo_IsParsDeath,
				@PersonInfo_Email = :PersonInfo_Email,
				@Person_BDZCode = :Person_BDZCode,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @PersonInfo_id as PersonInfo_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'PersonInfo_id' => !empty($data['PersonInfo_id'])?$data['PersonInfo_id']:null,
			'UPersonSprTerrDop_id' => !empty($data['UPersonSprTerrDop_id'])?$data['UPersonSprTerrDop_id']:null,
			'PPersonSprTerrDop_id' => !empty($data['PPersonSprTerrDop_id'])?$data['PPersonSprTerrDop_id']:null,
			'BPersonSprTerrDop_id' => !empty($data['BPersonSprTerrDop_id'])?$data['BPersonSprTerrDop_id']:null,
			'PersonInfo_InternetPhone' => !empty($data['PersonInfo_InternetPhone'])?$data['PersonInfo_InternetPhone']:null,
			'Nationality_id' => !empty($data['Nationality_id'])?$data['Nationality_id']:null,
			'Ethnos_id' => !empty($data['Ethnos_id'])?$data['Ethnos_id']:null,
			'PersonInfo_IsSetDeath' => !empty($data['PersonInfo_IsSetDeath'])?$data['PersonInfo_IsSetDeath']:null,
			'PersonInfo_IsParsDeath' => !empty($data['PersonInfo_IsParsDeath'])?$data['PersonInfo_IsParsDeath']:null,
			'PersonInfo_Email' => !empty($data['PersonInfo_Email'])?$data['PersonInfo_Email']:null,
			'Person_BDZCode' => !empty($data['Person_BDZCode'])?$data['Person_BDZCode']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		//echo getDebugSQL($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение специфики детства
	 */
	function savePersonChild($data) {
		// проверяем наличие записи о PersonChild
		$query = "
			select top 1 *
			from v_PersonChild with(nolock) 
			where Person_id = :Person_id
			order by PersonChild_id desc
		";
		$PersonChild = $this->getFirstRowFromQuery($query, $data, true);
		if ($PersonChild === false) {
			return $this->createError('', 'Ошибка при поиске специфики детства');
		}

		$queryParams = array();
		$queryParams['PersonChild_id'] = null;
		$queryParams['Server_id'] = $data['Server_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['ResidPlace_id'] = !empty($data['ResidPlace_id'])?$data['ResidPlace_id']:null;
		$queryParams['PersonChild_IsManyChild'] = !empty($data['PersonChild_IsManyChild'])?$data['PersonChild_IsManyChild']:null;
		$queryParams['PersonChild_IsBad'] = !empty($data['PersonChild_IsBad'])?$data['PersonChild_IsBad']:null;
		$queryParams['PersonChild_IsYoungMother'] = !empty($data['PersonChild_IsYoungMother'])?$data['PersonChild_IsYoungMother']:null;
		$queryParams['PersonChild_IsIncomplete'] = !empty($data['PersonChild_IsIncomplete'])?$data['PersonChild_IsIncomplete']:null;
		$queryParams['PersonChild_IsTutor'] = !empty($data['PersonChild_IsTutor'])?$data['PersonChild_IsTutor']:null;
		$queryParams['PersonChild_IsMigrant'] = !empty($data['PersonChild_IsMigrant'])?$data['PersonChild_IsMigrant']:null;
		$queryParams['HealthKind_id'] = !empty($data['HealthKind_id'])?$data['HealthKind_id']:null;
		$queryParams['FeedingType_id'] = !empty($data['FeedingType_id'])?$data['FeedingType_id']:null;
		$queryParams['PersonChild_CountChild'] = !empty($data['PersonChild_CountChild'])?$data['PersonChild_CountChild']:null;
		$queryParams['InvalidKind_id'] = !empty($data['InvalidKind_id'])?$data['InvalidKind_id']:null;
		$queryParams['PersonChild_IsInvalid'] = !empty($data['PersonChild_IsInvalid'])?$data['PersonChild_IsInvalid']:null;
		$queryParams['PersonChild_invDate'] = !empty($data['PersonChild_invDate'])?$data['PersonChild_invDate']:null;
		$queryParams['HealthAbnorm_id'] = !empty($data['HealthAbnorm_id'])?$data['HealthAbnorm_id']:null;
		$queryParams['HealthAbnormVital_id'] = !empty($data['HealthAbnormVital_id'])?$data['HealthAbnormVital_id']:null;
		$queryParams['Diag_id'] = !empty($data['Diag_id'])?$data['Diag_id']:null;
		$queryParams['PersonSprTerrDop_id'] = !empty($data['PersonSprTerrDop_id'])?$data['PersonSprTerrDop_id']:null;
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$queryParams['ChildTermType_id'] = null;
		$queryParams['PersonChild_IsAidsMother'] = null;
		$queryParams['PersonChild_IsBCG'] = null;
		$queryParams['PersonChild_BCGSer'] = null;
		$queryParams['PersonChild_BCGNum'] = null;
		$queryParams['BirthSvid_id'] = null;
		$queryParams['ChildPositionType_id'] = null;
		$queryParams['PersonChild_IsRejection'] = null;
		$queryParams['BirthSpecStac_id'] = null;

		$procedure = 'p_PersonChild_ins';
		if (!empty($PersonChild)) {
			$procedure = 'p_PersonChild_upd';
			foreach($queryParams as $key => &$value) {
				if (!key_exists($key, $data) && !empty($PersonChild[$key])) {
					$value = $PersonChild[$key];
				}
			}
		}

		$query = "
			declare
				@Error_Message varchar(400),
				@Error_Code bigint,
				@PersonChild_id bigint = :PersonChild_id
			exec {$procedure}
				@PersonChild_id = @PersonChild_id output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@ResidPlace_id = :ResidPlace_id,
				@BirthSpecStac_id = :BirthSpecStac_id,
				@PersonChild_IsManyChild = :PersonChild_IsManyChild,
				@PersonChild_IsBad = :PersonChild_IsBad,
				@PersonChild_IsYoungMother = :PersonChild_IsYoungMother,
				@PersonChild_IsIncomplete = :PersonChild_IsIncomplete,
				@PersonChild_IsTutor = :PersonChild_IsTutor,
				@PersonChild_IsMigrant = :PersonChild_IsMigrant,
				@HealthKind_id = :HealthKind_id,
				@FeedingType_id = :FeedingType_id,
				@PersonChild_IsInvalid = :PersonChild_IsInvalid,
				@InvalidKind_id = :InvalidKind_id,
				@PersonChild_invDate = :PersonChild_invDate,
				@HealthAbnorm_id = :HealthAbnorm_id,
				@HealthAbnormVital_id = :HealthAbnormVital_id,
				@Diag_id = :Diag_id,
				@PersonSprTerrDop_id = :PersonSprTerrDop_id,
				@pmUser_id = :pmUser_id,
				@ChildTermType_id         = :ChildTermType_id        ,
				@PersonChild_IsAidsMother = :PersonChild_IsAidsMother,
				@PersonChild_IsBCG        = :PersonChild_IsBCG       ,
				@PersonChild_BCGSer       = :PersonChild_BCGSer      ,
				@PersonChild_BCGNum       = :PersonChild_BCGNum      ,
				@BirthSvid_id             = :BirthSvid_id            ,
				@PersonChild_CountChild   = :PersonChild_CountChild  ,
				@ChildPositionType_id     = :ChildPositionType_id    ,
				@PersonChild_IsRejection  = :PersonChild_IsRejection ,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @PersonChild_id as PersonChild_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$response = $this->queryResult($query, $queryParams);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении специфики детства');
		}
		return $response;
	}

	/**
	 * Удаление данных человека
	 */
	public function deletePerson($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@ErrCode int,
				@ErrMsg varchar(4000);
			exec p_Person_del
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select  @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$IsSMPServer = $this->config->item('IsSMPServer');
		if ($IsSMPServer) {
			// подключаем основную бд
			$db = $this->load->database('main', true);
			// удаляем на основной бд
			$res = $db->query($query, $params);
			/*
			if (is_array($res)) {
				// удаляем на текущей бд
				$resp = $this->queryResult($query, $params);
			}
			*/
		}
		//else {
			// удаляем на текущей бд
			$resp = $this->queryResult($query, $params);
		//}

		//$resp = $this->queryResult($query, $params);


		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении человека');
		}
		return $resp;
	}

	/**
	 * Обновление данных человека
	 */
	public function updatePerson($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select top 1
				P.Server_id,
				P.Person_IsUnknown,
				P.Person_IsAnonym,
				p.Person_IsNotINN,
				P.Person_IsDead,
				P.BDZ_id,
				P.Lgot_id,
				P.ProMed_id,
				P.Person_Guid,
				P.Person_IsInErz,
				convert(varchar, P.Person_deadDT, 121) as Person_deadDT,
				P.PersonCloseCause_id,
				convert(varchar, P.Person_closeDT, 121) as Person_closeDT,
				convert(varchar, P.Person_MaxEvnDT, 121) as Person_MaxEvnDT,
				convert(varchar, P.Person_identDT, 121) as Person_identDT,
				P.PersonIdentState_id,
				P.Person_IsEncrypHIV,
				P.Person_Comment
			from Person P with(nolock)
			where P.Person_id = :Person_id
		";
		$person = $this->getFirstRowFromQuery($query, $params);
		if ($person === false) {
			return $this->createError('', 'Ошибка при получении данных человека');
		}
		foreach($person as $field => $value) {
			if (isset($data[$field])) {
				$params[$field] = $data[$field];
			} else {
				$params[$field] = $value;
			}
		}

		$query = "
			declare
				@Person_id bigint = :Person_id,
				@ErrCode int,
				@ErrMsg varchar(4000);
			exec p_Person_upd
				@Person_id = @Person_id output,
				@Server_id = :Server_id,
				@Person_IsUnknown = :Person_IsUnknown,
				@Person_IsAnonym = :Person_IsAnonym,
				@Person_IsNotINN = :Person_IsNotINN,
				@Person_IsDead = :Person_IsDead,
				@BDZ_id = :BDZ_id,
				@Lgot_id = :Lgot_id,
				@Person_IsInErz=:Person_IsInErz,
				@ProMed_id = :ProMed_id,
				@Person_Guid = :Person_Guid,
				@Person_deadDT = :Person_deadDT,
				@PersonCloseCause_id = :PersonCloseCause_id,
				@Person_closeDT = :Person_closeDT,
				@Person_MaxEvnDT = :Person_MaxEvnDT,
				@Person_identDT = :Person_identDT,
				@PersonIdentState_id = :PersonIdentState_id,
				@Person_IsEncrypHIV = :Person_IsEncrypHIV,
				@Person_Comment = :Person_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Person_id as Person_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении данных человека');
		}
		return $resp;
	}

	/**
	 * Сохранение данных о человеке
	 * Вызывается из формы редактирования человека
	 */
	public function savePersonEditWindow($data, $api=false) {
		$this->load->library('textlog', array('file' => 'Person_save.log'));

		$IsSMPServer = $this->config->item('IsSMPServer');
		$IsLocalSMP = $this->config->item('IsLocalSMP');
		$is_superadmin = stripos( $data['session']['groups'], 'SuperAdmin' ) !== false ? true : false;
		$needSnilsVerification = false;

		$region ='';
		if(isset($data['session']['region'])){
			$region = $data['session']['region']['nick'];
		}
		$isPerm = ($region=='perm')?true:false;
		$isKrym = ($region=='krym')?true:false;
		$is_ufa = ($region=='ufa')?true:false;
		$is_kareliya = ($region=='kareliya')?true:false;
		$is_kz = ($region=='kz')?true:false;
		$is_ekb =($region=='ekb')?true:false;
		$is_saratov = ($region=='saratov')?true:false;
		$is_astra = ($region=='astra')?true:false;
		$is_bur = ($region=='buryatiya')?true:false;
		$is_pskov = ($region=='pskov')?true:false;
		$is_kaluga = ($region=='kaluga')?true:false;
		$is_penza = ($region=='penza')?true:false;
		$is_vologda = ($region=='vologda')?true:false;
		/*if ( isset( $data['session']['region'] ) ) {
			$is_{(string)$data['session']['region']['nick']} = true;
		}*/

		$regionWithOutActiveMQ =  ($is_bur || $is_ufa || $is_kareliya  || $is_astra);
		$saveWithOutActiveMQ = ($IsSMPServer || $IsLocalSMP ) && $regionWithOutActiveMQ;

		if ($saveWithOutActiveMQ && empty($data['useSMP'])) {
			// подключаем основную бд
			unset($this->db);
			$this->db = $this->load->database('main', true);
		}
		if ($saveWithOutActiveMQ && !empty($data['useSMP']) && $data['useSMP'] == true  ) {
			// подключаем бд СМП
			unset($this->db);
			$this->db = $this->load->database('',true);
		}

		if(isset($data['UAddressSpecObject_Value'])){
			if($data['UAddressSpecObject_Value']!=''&&$data['UAddressSpecObject_id']<=0){
				$data['UAddressSpecObject_id']=$this->addSpecObject($data['UAddressSpecObject_Value'],$data['pmUser_id']);
			}
		}
		if(isset($data['BAddressSpecObject_Value'])){
			if($data['BAddressSpecObject_Value']!=''&&$data['BAddressSpecObject_id']<=0){
				$data['BAddressSpecObject_id']=$this->addSpecObject($data['BAddressSpecObject_Value'],$data['pmUser_id']);
			}
		}
		if(isset($data['PAddressSpecObject_Value'])){
			if($data['PAddressSpecObject_Value']!=''&&$data['PAddressSpecObject_id']<=0){
				$data['PAddressSpecObject_id']=$this->addSpecObject($data['PAddressSpecObject_Value'],$data['pmUser_id']);
			}
		}

		if (empty($data['PersonIdentState_id']) || !in_array(intval($data['PersonIdentState_id']), array(1, 2, 3, 4))) {
			$data['PersonIdentState_id'] = 0;
		}

		$person_is_identified = false;
		if (($is_ufa === true || $is_kareliya === true || $is_pskov === true || $is_ekb === true) && $data['PersonIdentState_id'] != 0 && !empty($data['Person_identDT'])) {
			$person_is_identified = true;
		}

		if (getRegionNick() == 'kareliya' && $person_is_identified) {
			// проверяем полис, если закрыт, то признак БДЗ ставить не надо.
			if (!empty($data['Polis_endDate']) && strtotime($data['Polis_endDate']) < time()) {
				$person_is_identified = false;
			}
		}

		try {
			// @task https://redmine.swan-it.ru/issues/163758
			if (
				$this->regionNick == 'vologda'
				&& !empty($data['DocumentType_id']) && !in_array($data['DocumentType_id'], array(3,9,17,19,22))
				&& !empty($data['Document_begDate']) && !empty($data['Person_BirthDay'])
				&& getCurrentAge($data['Person_BirthDay'], $data['Document_begDate']) < 14
			) {
				throw new Exception('Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.');
			}

			$this->beginTransaction();
			$this->exceptionOnValidation = true;

			if (!isset($data['missSocStatus'])) {
				//Проверка на соответствие возраста соцстатусу https://redmine.swan.perm.ru/issues/40510
				$params_socstat = array();
				$params_socstat['SocStatus_id'] = $data['SocStatus_id'];
				$query_socstat = "
					select SocStatus_SysNick,
						  SocStatus_AgeFrom as AgeFrom,
						  SocStatus_AgeTo as AgeTo
					from v_SocStatus with (nolock)
					where SocStatus_id = :SocStatus_id
				";
				$result_socstat = $this->db->query($query_socstat, $params_socstat);
				if (is_object($result_socstat)) {
					$result_socstat = $result_socstat->result('array');
					if (is_array($result_socstat) && count($result_socstat) > 0) {
						$socstat_sysnick = $result_socstat[0]['SocStatus_SysNick'];
						$days_diff = date_diff(new DateTime(), new DateTime($data['Person_BirthDay']))->days; //Возраст в днях
						$years_diff = getCurrentAge($data['Person_BirthDay']); //Возраст в годах
						$compare_param = $years_diff;
						if ($socstat_sysnick == 'newborn'){ //Если новорожденный, то в v_SocStatus диапазон возраста указан в днях, так что берем days_diff
							$compare_param = $days_diff;
						}
						if (isset($result_socstat[0]['AgeFrom']) && isset($result_socstat[0]['AgeTo'])) {
							if (!(($compare_param >= $result_socstat[0]['AgeFrom']) && ($compare_param <= $result_socstat[0]['AgeTo']))) {
								$this->_saveResponse['type'] = 'SocStatus';
								throw new Exception('Несоответствие социального статуса и возраста человека!');
							}
						}
					}
				}
			}

			$bdzData=array();
			$bdzFlag=false;
			$is_double = false;

			if($person_is_identified&&isset($data['BDZ_Guid'])&&$is_ufa){
				$bdzData = $this->getBDZPersonData($data);
				if($bdzData){

					if($data['mode']=='add'){
						$data['mode']='edit';
						$data['Person_id']=$bdzData['Person_id'];
						$data['Server_id']=0;
						$bdzFlag=true;
					}else{
						$is_double = true;
					}
				}
				//print_r($datas);exit();

			}
			if($bdzFlag){
				foreach ($bdzData as $val=>$item) {
					if ($val == 'Person_BirthDay' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val == 'Person_deadDT' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val == 'Document_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					/*if ($val == 'PAddress_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val == 'UAddress_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}*/
					if ($val == 'Polis_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val == 'Polis_endDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val == 'PersonChild_invDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if (array_key_exists($val, $data)) {
						if (in_array($val, array('Person_SurName', 'Person_FirName', 'Person_SecName'))) {
							if (mb_strtoupper(trim((string) $data[$val])) !== mb_strtoupper(trim((string) $item))) {
								$newFields[$val] = $item;
							}
						} else if (in_array($val, array("Person_SNILS"))) {
							if (trim((string) $data[$val]) !== str_replace("-", "", trim((string) $item))) {
								$newFields[$val] = $item;
							}
						} else if(in_array($val, array("PersonPhone_Phone"))) {
							$replace_symbols = array("-", "(", ")", " ");
							if (trim((string) $data[$val]) !== str_replace($replace_symbols, "", trim((string) $item))) {
								$newFields[$val] = $item;
							}
						}
						else {
							if (trim((string) $data[$val]) !== trim((string) $item)) {
								$newFields[$val] = $item;

							}
						}
					}
				}
			}
			else if ($data['mode'] != 'add') {
				// оставим только изменившиеся поля
				$oldValues = explode('&', urldecode($data['oldValues']));
				$newFields = array();
				foreach ($oldValues as $oldValue) {
					$val = explode('=', $oldValue);
					$fieldVal = "";
					$flag = false;
					foreach ($val as $item) {
						// первый пропускаем
						if (!$flag)
							$flag = true;
						else
							$fieldVal .= $item;
					}
					$item = toAnsi($item);
					if ($val[0] == 'Person_BirthDay' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val[0] == 'Person_deadDT' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val[0] == 'Document_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					/*if ($val[0] == 'PAddress_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val[0] == 'UAddress_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}*/
					if ($val[0] == 'Polis_begDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val[0] == 'Polis_endDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if ($val[0] == 'PersonChild_invDate' && $item != '') {
						$item = date('Y-m-d', strtotime($item));
					}
					if($val[0] == 'NationalityStatus_IsTwoNation' && $item != ''){
						$item = ($item=='true')?1:0;
					}
					if ($val[0] == 'Person_IsUnknown' && $item != '') {
						if($api){
							$item = ($item=='true' || $item==2)?1:0;
						}else{
							$item = ($item=='true')?1:0;
						}
					}
					if ($val[0] == 'Person_IsAnonym' && $item != '') {
						$item = ($item=='true')?1:0;
					}
					if (array_key_exists($val[0], $data)) {
						if (in_array($val[0], array('Person_SurName', 'Person_FirName', 'Person_SecName'))) {
							if (getRegionNick() == 'kz') {
								if (trim((string) $data[$val[0]]) !== trim((string) $item)) {
									$newFields[$val[0]] = $item;
								}
							} else {
								if (mb_strtoupper(trim((string) $data[$val[0]])) !== mb_strtoupper(trim((string) $item))) {
									$newFields[$val[0]] = $item;
								}
							}
						} else if (in_array($val[0], array("Person_SNILS"))) {
							if (trim((string) $data[$val[0]]) !== str_replace(array("-"," "), "", trim((string) $item))) {
								$newFields[$val[0]] = $item;
							}
						} else {
							if (trim((string) $data[$val[0]]) !== trim((string) $item)) {
								$newFields[$val[0]] = $item;

							}
						}
					}
				}
				//unset($data['oldValues']);
				//print_r($newFields);exit();
				$pid = $data['Person_id'];

				// атрибуты редактируются, очищаем инфо о человеке из сессии, но предварительно проверяем, он ли там записан,
				// чтобы лишний раз в сессию не писать, экономим на спичках
				if(!isset($_SESSION))
					session_start();
				if (isset($data['session']['person']) && isset($data['session']['person']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person']['Person_id'])
					unset($_SESSION['person']);

				if (isset($data['session']['person_short']) && isset($data['session']['person_short']['Person_id']) && isset($data['Person_id']) && $data['Person_id'] == $data['session']['person_short']['Person_id'])
					unset($_SESSION['person_short']);
				session_write_close();
			}

			if ($data['mode'] == 'add') {
				$newFields = $data;
				foreach ($newFields as $key => $value)
					if (empty($value))
						unset($newFields[$key]);

				if($api) {
					//параметры из API могут быть =0
					if(isset($data['Person_IsUnknown']) && !isset($newFields['Person_IsUnknown'])) $newFields['Person_IsUnknown'] = $data['Person_IsUnknown'];
				}
			}

			// стартуем транзакцию
			//$this->db->trans_begin();
			//Проверка СНИЛС на уникальность только для саратова. Проверки вынесены в региональный контроллер и доработаны. Salakhov R.

			/*if ($is_saratov == true){
				// выполняется только для пациентов от 3 лет
				$birthday = (!empty($data['Person_BirthDay']))?strtotime($data['Person_BirthDay']):0;
				if ((strtotime("+3 year", $birthday) < time())) { //если пациенту уже есть три года
					if ( empty($data['Person_SNILS']) ) {
						throw new Exception('Поле СНИЛС обязательно для заполнения.');
					} else {
						$query = "
							select count(Person_id) as cnt
							from v_PersonState with (nolock)
							where Person_id != ISNULL(:Person_id, 0)
							and  Person_SNILS = :Person_SNILS
						";

						$result = $this->db->query($query, array(
							'Person_id' => $data['Person_id'],
							'Person_SNILS' => $data['Person_SNILS'])
						);
						$response = $result->result('array');
						if ( !empty($response[0]['cnt']) ) {
							throw new Exception('Человек с введённым номером СНИЛС уже есть в базе.');
						}
					}
				}
			}*/
			$pid = $data['Person_id'];
			$server_id = $data['Server_id'];

			$flBDZ = false;
			$arr = array(
				'Person_SurName',
				'Person_FirName',
				'Person_SecName',
				'Person_BirthDay',
				"PolisType",
				"OMSSprTerr_id",
				"Polis_Ser",
				"Polis_Num",
				"Federal_Num",
				"OrgSMO_id",
				"Polis_begDate",
				"Polis_endDate"
			);
			foreach ($arr as $value) {
				if (array_key_exists($value, $newFields)) {
					$flBDZ = true;
					break;
				}
			}
			$polisChange = false;
			if(array_key_exists('OMSSprTerr_id', $newFields) || array_key_exists('PolisType_id', $newFields) ||
				array_key_exists('Polis_Ser', $newFields) || array_key_exists('PolisFormType_id', $newFields) ||
				array_key_exists('Polis_Num', $newFields) || array_key_exists('OrgSMO_id', $newFields) ||
				array_key_exists('Polis_begDate', $newFields) || array_key_exists('Polis_endDate', $newFields) || array_key_exists('Federal_Num', $newFields)){
				$polisChange=true;
			}
			$documentChange = false;
			if(array_key_exists('DocumentType_id', $newFields) || array_key_exists('Document_Ser', $newFields) ||
				array_key_exists('Document_Num', $newFields) || array_key_exists('OrgDep_id', $newFields) ||
				array_key_exists('Document_begDate', $newFields) || array_key_exists('KLCountry_id', $newFields) || array_key_exists('NationalityStatus_IsTwoNation', $newFields)
			){
				$documentChange=true;
			}
			$mainChange = false;	//флаг для сброса идентификации при изменении основных данных
			if ($isPerm || $is_kareliya) {
				if(array_key_exists('Person_SurName', $newFields) || array_key_exists('Person_FirName', $newFields) ||
					array_key_exists('Person_SecName', $newFields) || array_key_exists('Person_BirthDay', $newFields) ||
					array_key_exists('Person_SNILS', $newFields) || $polisChange || $documentChange
				) {
					$mainChange = empty($data['Person_identDT']);	//не сбрасывать, если сохранение данных после идентификации
				}
			} else if($is_penza || $isKrym) {
				if(array_key_exists('Person_SurName', $newFields) || array_key_exists('Person_FirName', $newFields) ||
					array_key_exists('Person_SecName', $newFields) || array_key_exists('Person_BirthDay', $newFields) ||
					array_key_exists('Person_SNILS', $newFields) || array_key_exists('PersonSex_id', $newFields) ||
					$polisChange || $documentChange
				) {
					$mainChange = true;
				}
			} else {
				if(array_key_exists('Person_SurName', $newFields) || array_key_exists('Person_FirName', $newFields) ||
					array_key_exists('Person_SecName', $newFields) || array_key_exists('Person_BirthDay', $newFields) ||
					array_key_exists('PersonInn_Inn', $newFields) || array_key_exists('PersonSex_id', $newFields)
				) {
					$mainChange=true;
				}
			}
			if ((array_key_exists('Federal_Num', $newFields) && !empty($data['Federal_Num']) && strlen($data['Federal_Num']) !== 16) || 
				(!empty($data['Federal_Num']) && strlen($data['Federal_Num']) !== 16)) {
				throw new Exception('Единый номер полиса должен иметь длину в 16 цифр');
			}
			// новая хранимка p_PersonAll_ins вызывается только при добавлении людей или обновления как минимум ФИО и ДР, иначе вызываются поштучные хранимки, так же вызывается  если регион Карелия изменено одно из полей ФИО ИЛИ ДР ИЛИ ПОЛИС.
			if (($data['mode'] == 'add') || ($flBDZ == true && ($is_kareliya == true || $is_ufa == true || $is_astra == true)) || (array_key_exists('Person_SurName', $newFields) && array_key_exists('Person_FirName', $newFields) && array_key_exists('Person_SecName', $newFields) && array_key_exists('Person_BirthDay', $newFields))) {

				$queryParams = array(
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $person_is_identified ? 0 : $data['Server_id'],
					'Person_Guid' => !empty($data['Person_Guid'])?$data['Person_Guid']:null,
					'Person_id' => $data['Person_id'],
					'Person_Comment' => !empty($data['Person_Comment'])?$data['Person_Comment']:null
				);
				$query = "
					declare
						@Pers_id bigint = :Person_id,
						@Person_Guid varchar(1000) = :Person_Guid,
						@ErrCode int,
						@ErrMessage varchar(4000);
								
					exec p_PersonAll_ins
						@Person_id = @Pers_id OUTPUT
						,@Person_Guid = @Person_Guid OUTPUT
						,@Server_id = :Server_id
						,@Person_Comment = :Person_Comment
				";
				if ($person_is_identified) {
					$query .= "
						,@Person_identDT = :Person_identDT
						,@PersonIdentState_id = :PersonIdentState_id
						,@BDZ_Guid = :BDZ_Guid
					";
					$queryParams['BDZ_Guid'] = 	$data['BDZ_Guid'];
					$queryParams['Person_identDT'] = date('Y-m-d', $data['Person_identDT']);
					$queryParams['PersonIdentState_id'] = $data['PersonIdentState_id'];
				}
				if (!empty($data['BDZ_id'])) {
					$query .= "
					,@BDZ_id = :BDZ_id
				";
					$queryParams['BDZ_id'] = 	$data['BDZ_id'];
				}
				if ($is_kareliya && !$person_is_identified) {
					$data['Person_IsInErz'] = null;
				} else if (isset($data['Person_IsInErz']) && $data['Person_IsInErz']==1){
					$data['Person_IsInErz'] = null;
				}
				/*if($is_kz){
					$data['Person_IsInErz'] = null;
				}*/
				$query .= "
					,@Person_IsInErz = :Person_IsInErz
				";
				$queryParams['Person_IsInErz'] = (isset($data['Person_IsInErz']))?$data['Person_IsInErz']:null;
				if (array_key_exists('Person_SurName', $newFields)) {
					$query .= "
						,@PersonSurName_SurName = :PersonSurName_SurName
					";
					$queryParams['PersonSurName_SurName'] = $data['Person_SurName'];
				}
				if (array_key_exists('Person_FirName', $newFields)) {
					$query .= "
						,@PersonFirName_FirName = :PersonFirName_FirName
					";
					$queryParams['PersonFirName_FirName'] = $data['Person_FirName'];
				}
				if (array_key_exists('Person_SecName', $newFields)) {
					$query .= "
						,@PersonSecName_SecName = :PersonSecName_SecName
					";
					$queryParams['PersonSecName_SecName'] = $data['Person_SecName'];
				}
				if (array_key_exists('Person_BirthDay', $newFields)) {
					$query .= "
						,@PersonBirthDay_BirthDay = :PersonBirthDay_BirthDay
					";
					$queryParams['PersonBirthDay_BirthDay'] = $data['Person_BirthDay'];
				}
				if (array_key_exists('PersonSex_id', $newFields)) {
					$query .= "
						,@Sex_id = :Sex_id
					";
					$queryParams['Sex_id'] = $data['PersonSex_id'];
				}
				if (array_key_exists('Person_SNILS', $newFields)) {
					$query .= "
						,@PersonSnils_Snils = :PersonSnils_Snils
					";
					$queryParams['PersonSnils_Snils'] = $data['Person_SNILS'];
				}
				if (array_key_exists('SocStatus_id', $newFields)) {
					$query .= "
						,@SocStatus_id = :SocStatus_id
					";
					$queryParams['SocStatus_id'] = $data['SocStatus_id'];
				}
				/*if (array_key_exists('Federal_Num', $newFields)) {
					$query .= "
						,@PersonPolisEdNum_EdNum = :PersonPolisEdNum_EdNum
					";
					$queryParams['PersonPolisEdNum_EdNum'] = $data['Federal_Num'];
				} */
				if (array_key_exists('PersonPhone_Phone', $newFields)) {
					$query .= "
						,@PersonPhone_Phone = :PersonPhone_Phone
					";
					$queryParams['PersonPhone_Phone'] = $data['PersonPhone_Phone'];
				}
				/*if (array_key_exists('PersonPhone_Comment', $newFields)) {
					$query .= "
						,@PersonPhone_Comment = :PersonPhone_Comment
					";
					$queryParams['PersonPhone_Comment'] = $data['PersonPhone_Comment'];
				}*/
				if (array_key_exists('PersonInn_Inn', $newFields)) {
					$query .= "
						,@PersonInn_Inn = :PersonInn_Inn
					";
					$queryParams['PersonInn_Inn'] = $data['PersonInn_Inn'];
				}
				if ((isSuperadmin() || $person_is_identified) && array_key_exists('PersonSocCardNum_SocCardNum', $newFields)) {
					$query .= "
						,@PersonSocCardNum_SocCardNum = :PersonSocCardNum_SocCardNum
					";
					$queryParams['PersonSocCardNum_SocCardNum'] = $data['PersonSocCardNum_SocCardNum'];
				}
				if (array_key_exists('FamilyStatus_id', $newFields)) {
					$query .= "
						,@FamilyStatus_id = :FamilyStatus_id
					";
					$queryParams['FamilyStatus_id'] = $data['FamilyStatus_id'];
				}
				if (array_key_exists('PersonFamilyStatus_IsMarried', $newFields)) {
					$query .= "
						,@PersonFamilyStatus_IsMarried = :PersonFamilyStatus_IsMarried
					";
					$queryParams['PersonFamilyStatus_IsMarried'] = $data['PersonFamilyStatus_IsMarried'];
				}

				if (array_key_exists('Person_IsUnknown', $newFields)) {
					$query .= "
						,@Person_IsUnknown = :Person_IsUnknown
					";
					$queryParams['Person_IsUnknown'] = $data['Person_IsUnknown'] ? 2 : 1;
				}

				if (array_key_exists('Person_IsAnonym', $newFields)) {
					$query .= "
						,@Person_IsAnonym = :Person_IsAnonym
					";
					$queryParams['Person_IsAnonym'] = $data['Person_IsAnonym'] ? 2 : 1;
				}

				if (array_key_exists('Person_IsNotINN', $newFields)) {
					$query .= "
						,@Person_IsNotINN = :Person_IsNotINN
					";
					$queryParams['Person_IsNotINN'] = $data['Person_IsNotINN'];
				}

				if ($is_kareliya && $person_is_identified && !empty($data['Polis_begDate'])) {
					$query .= "
						,@PersonEvn_insDT = :PersonEvn_insDT
					";
					$queryParams['PersonEvn_insDT'] = $data['Polis_begDate'];
				}

				$query .= "
					,@pmUser_id = :pmUser_id
					,@Error_Code = @ErrCode output
					,@Error_Message = @ErrMessage output;
					
					select @Pers_id as Pid, @Person_Guid as Person_Guid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				//echo getDebugSql($query, $queryParams); die();
				$res = $this->db->query($query, $queryParams);
				if (!is_object($res)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных');
				}
				$rows = $res->result('array');

				if (!is_array($rows) || count($rows) == 0) {
					throw new Exception('Ошибки сохранения человека');
				} else if (!empty($rows[0]['Error_Msg'])) {
					throw new Exception($rows[0]['Error_Msg']);
				}
				$pid = $rows[0]['Pid'];
				$pguid = $rows[0]['Person_Guid'];

				if ($data['mode'] != 'add' && empty($data['Person_IsInErz'])) {
					$query = "update Person set Person_IsInErz = null, Person_IsInFOMS = null where Person_id = :Person_id";
					$queryParams = array('Person_id' => $data['Person_id']);
					$this->db->query($query, $queryParams);
				}

				if ($data['mode'] == 'add') {
					//Закомментировал (мало ли потом снова потребуется).
					//Перенес этот фунционал в PersonCard на сохранение прикрепления (shorev https://redmine.swan.perm.ru/issues/60393)
					/*
					// проверяем, есть ли человеку 3 года
					if (!empty($data['Person_BirthDay'])) {
						//$birthday = strtotime(ConvertDateFormat(trim($data['Person_BirthDay'])));
						$birthday = strtotime($data['Person_BirthDay']);
						// Закрываем для Казахстана
						// https://redmine.swan.perm.ru/issues/39959
						if ($is_kareliya === false && $is_kz === false && strtotime("+3 year", $birthday) > time()) {
							// добавляем льготу
							$this->load->database();
							$CI = & get_instance();
							$CI->load->model('Privilege_model', 'ppmodel', true);
							$model = & $CI->ppmodel;

							$priv_data = array();

							$priv_data['PrivilegeType_id'] = $model->getPrivilegeTypeIdBySysNick('child_und_three_year', date('Y-m-d'));

							if ($priv_data['PrivilegeType_id'] === false) {
								return array(array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы (получение идентификатора категории льготы)'));
							}

							$priv_data['Lpu_id'] = $data['Lpu_id'];
							$priv_data['pmUser_id'] = $data['pmUser_id'];
							$priv_data['PersonPrivilege_id'] = 0;
							$priv_data['Person_id'] = $pid;
							$priv_data['Server_id'] = ($person_is_identified === true ? 0 : $data['Server_id']);
							$priv_data['Privilege_begDate'] = date("Y-m-d", $birthday);
							$priv_data['Privilege_endDate'] = date("Y-m-d", strtotime("+3 year", $birthday) - 60 * 60 * 24);
							$priv_data['session'] = $data['session'];
							$res = $model->savePrivilege($priv_data);
							if (count($res) > 0) {
								if (isset($res[0])) {
									if (isset($res[0]['success']) && $res[0]['success'] == false) {
										//$this->db->trans_rollback();
										return $res;
									}
								} else {
									//$this->db->trans_rollback();
									return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы'));
								}
							} else {
								//$this->db->trans_rollback();
								return array(0 => array('success' => false, 'Error_Msg' => 'Ошибка при добавлении льготы'));
							}
						}
					}*/
				}
				$needSnilsVerification = true;
			} else {
				// если не поменялось фио и др и это не добавление человека, то старый код..
				if ($person_is_identified) {
					// Проставляем человеку признак "из БДЗ
					$sql = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
	
						exec p_Person_server
							@Person_id = :Person_id,
							@Server_id = :Server_id,
							@BDZ_Guid = :BDZ_Guid,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
	
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

					$res = $this->db->query($sql, array(
						'Person_id' => $pid,
						'Server_id' => 0,
						'BDZ_Guid'=>$data['BDZ_Guid'],
						'pmUser_id' => $data['pmUser_id']
					));

					if (!is_object($res)) {
						throw new Exception('Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)');
					}

					$response = $res->result('array');

					if (!is_array($response) || count($response) == 0) {
						throw new Exception('Ошибка при проставлении признака идентификации по сводной базе застрахованных');
					}

					if (!empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}

					$sql = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
	
						exec p_Person_ident
							@Person_id = :Person_id,
							@Person_identDT = :Person_identDT,
							@PersonIdentState_id = :PersonIdentState_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
	
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$res = $this->db->query($sql, array(
						'Person_id' => $pid,
						'Person_identDT' => date('Y-m-d', $data['Person_identDT']),
						'PersonIdentState_id' => $data['PersonIdentState_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if (!is_object($res)) {
						throw new Exception('Ошибка при выполнении запроса к базе данных (обновление данных об идентификации по сводной базе застрахованных)');
					}

					$response = $res->result('array');

					if (!is_array($response) || count($response) == 0) {
						throw new Exception('Ошибка при обновлении данных об идентификации по сводной базе застрахованных');
					}

					if (!empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}
				}
				if (
					//($polisChange&&$isPerm&&$data['Person_IsInErz']==1)||
					($is_penza && $mainChange && $data['Person_IsInErz'] == 2)||
					(($is_kz||$is_kaluga||$is_kareliya)&&($mainChange||array_key_exists('Person_IsInErz',$newFields)||(!empty($data['Person_IsInErz'])&&$data['Person_IsInErz']==1)))||
					(array_key_exists('Person_IsUnknown', $newFields) || array_key_exists('Person_IsAnonym', $newFields) || array_key_exists('Person_IsNotINN', $newFields) || array_key_exists('BDZ_id', $newFields))&&!empty($data['Person_id'])
				) {
					$params = array('Person_id' => $data['Person_id']);

					$sql = "
						select top 1
							P.Server_id,
							P.Person_id,
							P.Person_IsUnknown,
							P.Person_IsAnonym,
							P.Person_IsNotINN,
							P.Person_IsDead,
							P.Person_IsInErz,
							P.Person_IsInFOMS,
							P.BDZ_id,
							P.Lgot_id,
							P.ProMed_id,
							P.Person_Guid,
							convert(varchar, P.Person_deadDT, 121) as Person_deadDT,
							P.PersonCloseCause_id,
							convert(varchar, P.Person_closeDT, 121) as Person_closeDT,
							convert(varchar, P.Person_MaxEvnDT, 121) as Person_MaxEvnDT,
							convert(varchar, P.Person_identDT, 121) as Person_identDT,
							P.PersonIdentState_id,
							P.Person_IsEncrypHIV
						from Person P with(nolock)
						where P.Person_id = :Person_id
					";

					$resp = $this->queryResult($sql, $params);
					if (!$this->isSuccessful($resp) || count($resp) == 0) {
						throw new Exception('Ошибка при запросе данных человека');
					}

					$resp[0]['BDZ_id'] = !empty($data['BDZ_id']) ? $data['BDZ_id'] : null;
					if (array_key_exists('Person_IsUnknown', $newFields)) {
						$resp[0]['Person_IsUnknown'] = (isset($data['Person_IsUnknown']) && $data['Person_IsUnknown']) ? 2 : 1;
					}
					if (array_key_exists('Person_IsAnonym', $newFields)) {
						$resp[0]['Person_IsAnonym'] = (!empty($data['Person_IsAnonym']) && $data['Person_IsAnonym']) ? 2 : 1;
					}
					if (array_key_exists('Person_IsNotINN', $newFields)) {
						$resp[0]['Person_IsNotINN'] = (!empty($data['Person_IsNotINN'])) ? $data['Person_IsNotINN'] : null;
					}
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					/*if ($isPerm) {
						$resp[0]['Person_IsInErz'] = ($polisChange&&$isPerm&&isset($data['Person_IsInErz'])&&$data['Person_IsInErz']==1)?null:$resp[0]['Person_IsInErz'];
					}*/
					if ($is_kareliya) {
						$resp[0]['Person_IsInErz'] = (($mainChange && !array_key_exists('Person_IsInErz',$newFields))||empty($data['Person_IsInErz']))?null:$data['Person_IsInErz'];
					}
					if ($is_kz || $is_kaluga) {
						$resp[0]['Person_IsInErz'] = ($mainChange||empty($data['Person_IsInErz'])||$data['Person_IsInErz']==1)?null:$data['Person_IsInErz'];
					}
					if ($is_penza) {
						$resp[0]['Person_IsInErz'] = ($mainChange && $data['Person_IsInErz'] == 2)?1:$data['Person_IsInErz'];
					}
					if ($is_kz && $mainChange) {
						$resp[0]['Person_IsInFOMS'] = null;
					}
					$sql = "
						declare
							@Person_id bigint = :Person_id,
							@ErrCode int,
							@ErrMsg varchar(4000);
						exec p_Person_upd
							@Person_id = @Person_id output,
							@Server_id = :Server_id,
							@Person_IsUnknown = :Person_IsUnknown,
							@Person_IsAnonym = :Person_IsAnonym,
							@Person_IsNotINN = :Person_IsNotINN,
							@Person_IsDead = :Person_IsDead,
							@Person_IsInErz = :Person_IsInErz,
							@Person_IsInFOMS = :Person_IsInFOMS,
							@BDZ_id = :BDZ_id,
							@Lgot_id = :Lgot_id,
							@ProMed_id = :ProMed_id,
							@Person_Guid = :Person_Guid,
							@Person_deadDT = :Person_deadDT,
							@PersonCloseCause_id = :PersonCloseCause_id,
							@Person_closeDT = :Person_closeDT,
							@Person_MaxEvnDT = :Person_MaxEvnDT,
							@Person_identDT = :Person_identDT,
							@PersonIdentState_id = :PersonIdentState_id,
							@Person_IsEncrypHIV = :Person_IsEncrypHIV,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Person_id as Person_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					//echo getDebugSQL($sql, $resp[0]);die();
					$res = $this->db->query($sql, $resp[0]);
					$this->ValidateInsertQuery($res);

				}

				//Изменилась фамилия
				if (array_key_exists('Person_SurName', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSurName_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonSurName_SurName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_SurName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}
				//Изменилось имя
				if (array_key_exists('Person_FirName', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonFirName_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonFirName_FirName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_FirName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}

				//Изменилось отчество
				if (array_key_exists('Person_SecName', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSecName_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonSecName_SecName = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_SecName'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				// Изменился номер соц. карты
				if ((isSuperadmin() || $person_is_identified) && array_key_exists('PersonSocCardNum_SocCardNum', $newFields)) {
					// для прав суперадмина
					$serv_id = $server_id;
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSocCardNum_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonSocCardNum_SocCardNum = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonSocCardNum_SocCardNum'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				// Изменился номер телефона
				if (array_key_exists('PersonPhone_Phone', $newFields)/* || array_key_exists('PersonPhone_Comment', $newFields)*/) {

					$replace_symbols = array("-", "(", ")", " ");
					if(str_replace($replace_symbols, "", $newFields['PersonPhone_Phone']) != $data['PersonPhone_Phone'])
					{
						// для прав суперадмина
						$serv_id = $server_id;
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
		
							exec p_PersonPhone_ins
							@Server_id = ?,
							@Person_id = ?,
							@PersonPhone_Phone = ?,
							@PersonPhone_Comment = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
		
							select @ErrMsg as ErrMsg
						";
						//echo getDebugSQL($sql, array($serv_id, $pid, $data['PersonPhone_Phone'], $data['PersonPhone_Comment'], $data['pmUser_id']));die;
						//$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonPhone_Phone'], $data['PersonPhone_Comment'], $data['pmUser_id']));
						$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonPhone_Phone'], "", $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					}
				}
				//else if (array_key_exists('PersonPhone_Comment', $newFields))//Проверяем, а не изменился ли коммент к телефону. В этом случае ins уже не делаем, а просто апдейтим
				if(array_key_exists('Person_Comment', $newFields))
				{
					//var_dump('34324324234');die;
					/*$queryPhone = "
						select top 1 PersonPhone_id
						from v_PersonPhone
						where Person_id = ?
						and PersonPhone_Phone = ?
					";
					$resPhone = $this->db->query($queryPhone, array($pid,$data['PersonPhone_Phone']));
					if(is_object($resPhone)){
						$resPhone = $resPhone->result('array');
						if(count($resPhone) > 0){
							$PersonPhone_id = $resPhone[0]['PersonPhone_id'];
							$serv_id = $server_id;
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)

								exec p_PersonPhone_upd
								@PersonPhone_id = ?,
								@Server_id = ?,
								@Person_id = ?,
								@PersonPhone_Phone = ?,
								@PersonPhone_Comment = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output

								select @ErrMsg as ErrMsg
							";
							//echo getDebugSQL($sql, array($PersonPhone_id, $serv_id, $pid, $data['PersonPhone_Phone'], $data['PersonPhone_Comment'], $data['pmUser_id']));die;
							$res = $this->db->query($sql, array($PersonPhone_id, $serv_id, $pid, $data['PersonPhone_Phone'], $data['PersonPhone_Comment'], $data['pmUser_id']));
						}
					}*/
					$query_upd = "
						update Person set Person_Comment = ? where Person_id = ?
					";
					$res_upd = $this->db->query($query_upd, array($data['Person_Comment'],$pid));
				}

				// Изменился INN
				if (array_key_exists('PersonInn_Inn', $newFields)) {
					// для прав суперадмина
					$serv_id = $server_id;
					$data['PersonInn_Inn'] = ($data['PersonInn_Inn']) ? $data['PersonInn_Inn'] : null;
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonInn_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonInn_Inn = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['PersonInn_Inn'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				//Изменилась дата рождения
				if (array_key_exists('Person_BirthDay', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$date = empty($data['Person_BirthDay']) ? NULL : $data['Person_BirthDay'];
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonBirthDay_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonBirthDay_BirthDay = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $date, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				//Изменился СНИЛС
				if (array_key_exists('Person_SNILS', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						$serv_id = 1;
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSnils_ins
						@Server_id = ?,
						@Person_id = ?,
						@PersonSnils_Snils = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $data['Person_SNILS'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
					$needSnilsVerification = true;
				}

				//Изменился пол. o_O фигасе!
				if (array_key_exists('PersonSex_id', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$Sex_id = (!isset($data['PersonSex_id']) || !is_numeric($data['PersonSex_id']) ? NULL : $data['PersonSex_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSex_ins
						@Server_id = ?,
						@Person_id = ?,
						@Sex_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $Sex_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				//Изменился социальный статус
				if (array_key_exists('SocStatus_id', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$SocStatus_id = (empty($data['SocStatus_id']) ? NULL : $data['SocStatus_id']);
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonSocStatus_ins
						@Server_id = ?,
						@Person_id = ?,
						@SocStatus_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $SocStatus_id, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}

				// изменилось семейное положение
				if (array_key_exists('FamilyStatus_id', $newFields) || array_key_exists('PersonFamilyStatus_IsMarried', $newFields)) {
					$serv_id = $server_id;
					// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
					if ($is_superadmin || $person_is_identified) {
						//$serv_id = 0;
					}

					$FamilyStatus_id = (empty($data['FamilyStatus_id']) ? NULL : $data['FamilyStatus_id']);
					$PersonFamilyStatus_IsMarried = (empty($data['PersonFamilyStatus_IsMarried']) ? NULL : $data['PersonFamilyStatus_IsMarried']);

					if (empty($PersonFamilyStatus_IsMarried) && empty($FamilyStatus_id)) {
						throw new Exception('Хотя бы одно из полей "Семейное положение" или "Состоит в зарегистрированном браке" должно быть заполнено');
					}

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonFamilyStatus_ins
						@Server_id = ?,
						@Person_id = ?,
						@FamilyStatus_id = ?,
						@PersonFamilyStatus_IsMarried = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $FamilyStatus_id, $PersonFamilyStatus_IsMarried, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}
			}

			if (array_key_exists('Person_deadDT', $newFields)) {
				$deadDT = $this->getFirstResultFromQuery("
					select top 1
					convert(varchar(10), Person_deadDT, 120) as Person_deadDT
					from Person with(nolock)
					where Person_id = :Person_id
				", array('Person_id' => $pid));

				if (empty($data['Person_deadDT']) && !empty($deadDT)) {
					$resp = $this->revivePerson(array(
						'Person_id' => $pid,
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				} else if(!empty($data['Person_deadDT'])) {
					$resp = $this->killPerson(array(
						'Person_id' => $pid,
						'Person_deadDT' => $data['Person_deadDT'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				}
			}

			/*if(array_key_exists('BDZ_Guid', $newFields)&&$is_ufa){
				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)

					exec p_Person_server
					@Server_id = ?,
					@Person_id = ?,
					@BDZ_Guid = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array(0,$data['Person_id'],$data['BDZ_Guid'] $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}*/
			////-----
			$query_persdeputy = "
					select top 1
						PersonDeputy_id,
						DocumentDeputy_id
					from v_PersonDeputy with (nolock)
					where Person_id = :Person_id
				";
			$result_persdeputy = $this->db->query($query_persdeputy, array('Person_id' => $data['Person_id']))->result('array');
			
			if (!empty($data['DeputyKind_id']) && !empty($data['DeputyPerson_id'])) {

				$procedure = !empty($result_persdeputy[0]['DocumentDeputy_id']) ? 'p_DocumentDeputy_upd' : 'p_DocumentDeputy_ins';

				$query = "
				declare @Res bigint,
						@ErrCode int,
						@ErrMsg varchar(4000);
				set @Res = :DocumentDeputy_id;
				exec {$procedure}
						@DocumentDeputy_id = @Res output,
						@DocumentAuthority_id = :DocumentAuthority_id,
						@DocumentDeputy_Ser = :DocumentDeputy_Ser,
						@DocumentDeputy_Num = :DocumentDeputy_Num,
						@DocumentDeputy_Issue = :DocumentDeputy_Issue,
						@DocumentDeputy_begDate = :DocumentDeputy_begDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @Res as DocumentDeputy_id, @ErrCode as ErrCode, @ErrMsg as ErrMsg;
					";
				$queryParams = array(
					'DocumentDeputy_id' => (!empty($result_persdeputy[0]['DocumentDeputy_id']) ? $result_persdeputy[0]['DocumentDeputy_id'] : NULL),
					'DocumentAuthority_id' => $data['DocumentAuthority_id'],
					'DocumentDeputy_Ser' => $data['DocumentDeputy_Ser'],
					'DocumentDeputy_Num' => $data['DocumentDeputy_Num'],
					'DocumentDeputy_Issue' => $data['DocumentDeputy_Issue'],
					'DocumentDeputy_begDate' => $data['DocumentDeputy_begDate'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($query, $queryParams)->result('array');

				if ( is_array($result) ) {
					$procedure = !empty($result_persdeputy[0]['PersonDeputy_id']) ? 'p_PersonDeputy_upd' : 'p_PersonDeputy_ins';
					$query = "
					declare @ErrCode int,
					 		@ErrMsg varchar(4000);

					exec {$procedure}
						@PersonDeputy_id = :PersonDeputy_id,
						@Server_id = :Server_id,
						@Person_id = :Person_id,
						@Person_pid = :Person_pid,
						@DeputyKind_id = :DeputyKind_id,
						@pmUser_id = :pmUser_id,
						@DocumentDeputy_id = :DocumentDeputy_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
					select @ErrMsg as ErrMsg";
					
					if (!empty($data['Person_id']))
						$this->db->query($query, array(
							'Server_id' => $data['Server_id'],
							'Person_id' => $data['Person_id'],
							'DeputyKind_id' => $data['DeputyKind_id'],
							'Person_pid' => $data['DeputyPerson_id'],
							'DocumentDeputy_id' => $result[0]['DocumentDeputy_id'],
							'PersonDeputy_id' => !empty($result_persdeputy[0]['PersonDeputy_id']) ? $result_persdeputy[0]['PersonDeputy_id'] : null,
							'pmUser_id' => $data['pmUser_id']));
				}
			} else {
				if(empty($data['DeputyPerson_id'])) {
					if (!empty($result_persdeputy[0]['PersonDeputy_id'])) {
						$query = "
							declare @ErrCode int
							declare @ErrMsg varchar(4000)
							
							exec p_PersonDeputy_del
							@PersonDeputy_id = :PersonDeputy_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
	
							select @ErrMsg as ErrMsg
						";
						$this->db->query($query, array('PersonDeputy_id' => $result_persdeputy[0]['PersonDeputy_id']));
					}
	
					if (!empty($result_persdeputy[0]['DocumentDeputy_id'])) {
						$query = "
						declare @ErrCode int,
						@ErrMsg varchar(4000);
						
						exec p_DocumentDeputy_del
						@DocumentDeputy_id = :DocumentDeputy_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
						";
						$this->db->query($query, array('DocumentDeputy_id' => $result_persdeputy[0]['DocumentDeputy_id']));
					}
				}
			}
			
			$terr_dop_change = array('P' => false, 'U' => false, 'B' => false);
			// Изменилась национальность, этническая группа, территория адреса проживания или адреса регистрации для Уфы
			// http://redmine.swan.perm.ru/issues/22988
			if (
				array_key_exists('PersonNationality_id', $newFields)
				|| array_key_exists('Ethnos_id', $newFields)
				|| ($is_astra && isset($data['rz'])&&$data['rz']!=null)
				|| ($is_ufa === true && array_key_exists('PPersonSprTerrDop_id', $newFields))
				|| ($is_ufa === true && array_key_exists('UPersonSprTerrDop_id', $newFields))
				|| ($is_ufa === true && array_key_exists('BPersonSprTerrDop_id', $newFields))
			) {
				//проверяем, есть ли уже запись на этого персона в этой таблице
				$sql = "
					select top 1
						 PersonInfo_id
						,PersonInfo_InternetPhone
						,UPersonSprTerrDop_id
						,PPersonSprTerrDop_id
						,BPersonSprTerrDop_id
						,Person_BDZCode
						,PersonInfo_IsSetDeath
						,PersonInfo_IsParsDeath
						,PersonInfo_Email
					from
						PersonInfo with (nolock)
					where
						Person_id = :Person_id
					order by PersonInfo_updDT desc
				";
				$res = $this->db->query($sql, array('Person_id' => $pid));

				if (is_object($res)) {
					$rows = $res->result('array');

					if (!is_array($rows) || count($rows) == 0) {
						$rows = array(array(
							'PersonInfo_id' => NULL,
							'PersonInfo_InternetPhone' => NULL
						));

						$procedure = 'p_PersonInfo_ins';
						$terr_dop_change = array('P' => true, 'U' => true, 'B' => true);
					} else {
						$procedure = 'p_PersonInfo_upd';
						if(empty($data['rz'])){
							$data['rz']=$rows[0]['Person_BDZCode'];
						}
						$terr_dop_change = array(
							'P' => ($rows[0]['PPersonSprTerrDop_id'] != $data['PPersonSprTerrDop_id']),
							'U' => ($rows[0]['UPersonSprTerrDop_id'] != $data['UPersonSprTerrDop_id']),
							'B' => ($rows[0]['BPersonSprTerrDop_id'] != $data['BPersonSprTerrDop_id'])
						);
					}

					// выполняем хранимку
					$sql = "
						declare
						   @Res bigint,
						   @ErrCode int,
						   @ErrMsg varchar(4000);
	
					   set @Res = :PersonInfo_id;
	
					   exec " . $procedure . "
						   @Server_id = :Server_id,
						   @PersonInfo_id = @Res output,
						   @Person_id = :Person_id,
						   @UPersonSprTerrDop_id = :UPersonSprTerrDop_id,
						   @PPersonSprTerrDop_id = :PPersonSprTerrDop_id,
						   @BPersonSprTerrDop_id = :BPersonSprTerrDop_id,
						   @PersonInfo_InternetPhone = :PersonInfo_InternetPhone,
						   @Nationality_id = :Nationality_id,
						   @Ethnos_id = :Ethnos_id,
						   @PersonInfo_IsSetDeath = :PersonInfo_IsSetDeath,
						   @PersonInfo_IsParsDeath = :PersonInfo_IsParsDeath,
						   @PersonInfo_Email = :PersonInfo_Email,
						   @Person_BDZCode = :rz,
						   @pmUser_id = :pmUser_id,
						   @Error_Code = @ErrCode output,
						   @Error_Message = @ErrMsg output;
					
					   select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array(
						'Server_id' => $server_id,
						'PersonInfo_id' => $rows[0]['PersonInfo_id'],
						'Person_id' => $pid,
						'UPersonSprTerrDop_id' => (!empty($data['UPersonSprTerrDop_id']) ? $data['UPersonSprTerrDop_id'] : NULL),
						'PPersonSprTerrDop_id' => (!empty($data['PPersonSprTerrDop_id']) ? $data['PPersonSprTerrDop_id'] : NULL),
						'BPersonSprTerrDop_id' => (!empty($data['BPersonSprTerrDop_id']) ? $data['BPersonSprTerrDop_id'] : NULL),
						'PersonInfo_InternetPhone' => (!empty($rows[0]['PersonInfo_InternetPhone']) ? $rows[0]['PersonInfo_InternetPhone'] : NULL),
						'PersonInfo_IsSetDeath' => (!empty($rows[0]['PersonInfo_IsSetDeath']) ? $rows[0]['PersonInfo_IsSetDeath'] : NULL),
						'PersonInfo_IsParsDeath' => (!empty($rows[0]['PersonInfo_IsParsDeath']) ? $rows[0]['PersonInfo_IsParsDeath'] : NULL),
						'PersonInfo_Email' => (!empty($rows[0]['PersonInfo_Email']) ? $rows[0]['PersonInfo_Email'] : NULL),
						'Nationality_id' => (!empty($data['PersonNationality_id']) ? $data['PersonNationality_id'] : NULL),
						'Ethnos_id' => (!empty($data['Ethnos_id']) ? $data['Ethnos_id'] : NULL),
						'rz'=> (!empty($data['rz']) ? $data['rz'] : NULL),
						'pmUser_id' => $data['pmUser_id']
					));
					$this->ValidateInsertQuery($res);
				}
			}

			// Изменилось поле "Отказ от льготы"
			if (isSuperadmin() && array_key_exists('PersonRefuse_IsRefuse', $newFields)) {
				if (isset($data['PersonRefuse_IsRefuse'])) {
					// для прав суперадмина
					$serv_id = $server_id;
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
						declare @year int
	
						set @year = year(dbo.tzGetDate())
	
						exec p_PersonRefuse_ins
						@Person_id = ?,
						@PersonRefuse_IsRefuse = ?,
						@PersonRefuse_Year = @year,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($pid, $data['PersonRefuse_IsRefuse'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				}
			}

			// Изменился рост
			if (array_key_exists('PersonHeight_Height', $newFields) || array_key_exists('HeightAbnormType_id', $newFields) || array_key_exists('PersonHeight_IsAbnorm', $newFields)) {
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				//$serv_id = 0;
				$ins_dt = date('Y-m-d H:i:s', time());

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
	
					exec p_PersonHeight_ins
					@Server_id = ?,
					@Person_id = ?,
					@PersonHeight_setDT = ?,
					@PersonHeight_Height = ?,
					@HeightAbnormType_id = ?,
					@PersonHeight_IsAbnorm = ?,
					@Okei_id = 2,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
	
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonHeight_Height'], $data['HeightAbnormType_id'], $data['PersonHeight_IsAbnorm'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}

			// Изменился вес
			if (array_key_exists('PersonWeight_Weight', $newFields) || (array_key_exists('Okei_id', $newFields) && !empty($data['PersonWeight_Weight'])) || array_key_exists('WeightAbnormType_id', $newFields) || array_key_exists('PersonWeight_IsAbnorm', $newFields)) {
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				//$serv_id = 0;
				$ins_dt = date('Y-m-d H:i:s', time());

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
	
					exec p_PersonWeight_ins
					@Server_id = ?,
					@Person_id = ?,
					@PersonWeight_setDT = ?,
					@PersonWeight_Weight = ?,
					@WeightAbnormType_id = ?,
					@PersonWeight_IsAbnorm = ?,
					@WeightMeasureType_id = 3,
					@Okei_id = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
	
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $data['PersonWeight_Weight'], $data['WeightAbnormType_id'], $data['PersonWeight_IsAbnorm'], $data['Okei_id'], $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}

			// Изменилось поле "Есть дети до 16-ти"
			if (array_key_exists('PersonChildExist_IsChild', $newFields)) {
				$PersonChildExist_IsChild = (empty($data['PersonChildExist_IsChild']) ? NULL : $data['PersonChildExist_IsChild']);
				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				//$serv_id = 0;
				$ins_dt = date('Y-m-d H:i:s', time());

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
	
					exec p_PersonChildExist_ins
					@Server_id = ?,
					@Person_id = ?,
					@PersonChildExist_setDT = ?,
					@PersonChildExist_IsChild = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
	
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonChildExist_IsChild, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}

			// Изменилось поле "Есть автомобиль"
			if (array_key_exists('PersonCarExist_IsCar', $newFields)) {
				$PersonCarExist_IsCar = (empty($data['PersonCarExist_IsCar']) ? NULL : $data['PersonCarExist_IsCar']);

				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				//$serv_id = 0;
				$ins_dt = date('Y-m-d H:i:s', time());

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
	
					exec p_PersonCarExist_ins
					@Server_id = ?,
					@Person_id = ?,
					@PersonCarExist_setDT = ?,
					@PersonCarExist_IsCar = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
	
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $ins_dt, $PersonCarExist_IsCar, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}

			// Изменились поля "Социально-профессиональная группа" или "Этническая группа"
			if (array_key_exists('Ethnos_id', $newFields) || array_key_exists('OnkoOccupationClass_id', $newFields)) {
				$Ethnos_id = (empty($data['Ethnos_id']) ? NULL : $data['Ethnos_id']);
				$OnkoOccupationClass_id = (empty($data['OnkoOccupationClass_id']) ? NULL : $data['OnkoOccupationClass_id']);
				$MorbusOnkoPerson_id = NULL;
				$proc = "p_MorbusOnkoPerson_ins";

				// получить предыдущий MorbusOnkoPerson_id, если есть его обновить, иначе добавить
				$sql = "
					select top 1 
						MorbusOnkoPerson_id
					from
						v_MorbusOnkoPerson with (nolock)
					where
						Person_id = ?
					order by
						MorbusOnkoPerson_insDT desc
				";
				$res = $this->db->query($sql, array($pid));
				if (is_object($res)) {
					$resp = $res->result('array');
					if (count($resp) > 0) {
						$MorbusOnkoPerson_id = $resp[0]['MorbusOnkoPerson_id'];
						$proc = "p_MorbusOnkoPerson_upd";
					}
				}

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
	
					exec {$proc}
						@Person_id = ?,
						@Ethnos_id = ?,
						@OnkoOccupationClass_id = ?,
						@pmUser_id = ?,
						@MorbusOnkoPerson_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($pid, $Ethnos_id, $OnkoOccupationClass_id, $data['pmUser_id'], $MorbusOnkoPerson_id));
				$this->ValidateInsertQuery($res);
			}

			if (array_key_exists('Post_id', $newFields) || (isset($data['PostNew']) && !empty($data['PostNew'])) || array_key_exists('Org_id', $newFields) || array_key_exists('OrgUnion_id', $newFields) || (!empty($data['OrgUnionNew']))) {
				$Post_id = (empty($data['Post_id']) ? NULL : $data['Post_id']);
				$Org_id = (empty($data['Org_id']) ? NULL : $data['Org_id']);
				$OrgUnion_id = (empty($data['OrgUnion_id']) ? NULL : $data['OrgUnion_id']);


				// POST может быть добавлен
				if (isset($data['PostNew']) && !empty($data['PostNew'])) {

					$post_new = $data['PostNew'];

					if (is_numeric($post_new)) {

						$numPostID = 1;

						$sql = "
							select
								Post_id
							from
								v_Post with (nolock)
							where
								Post_id = ?
						";
						$result = $this->db->query($sql, array($post_new));
					} else {

						$sql = "
							select
								Post_id
							from
								v_Post with (nolock)
							where
								Post_Name like ? and Server_id = ?
						";
						$result = $this->db->query($sql, array($post_new, $server_id));
					}

					if (is_object($result)) {
						$sel = $result->result('array');
						if (isset($sel[0])) {
							if ($sel[0]['Post_id'] > 0)
								$Post_id = $sel[0]['Post_id'];
						} else if (isset($numPostID)) {
							$Post_id = null;
						} else {
							$sql = "
								declare @Psto_id bigint
								exec p_Post_ins
									@Post_Name = ?,
									@pmUser_id = ?,
									@Server_id = ?,
									@Post_id=@Psto_id output
								select @Psto_id as Post_id;
							";
							$result = $this->db->query($sql, array($post_new, $data['pmUser_id'], $server_id));
							if (is_object($result)) {
								$sel = $result->result('array');
								if ($sel[0]['Post_id'] > 0)
									$Post_id = $sel[0]['Post_id'];
							}
						}
					}
				}


				// OrgUnion может быть добавлен
				if (isset($data['OrgUnionNew']) && !empty($data['OrgUnionNew']) && !empty($data['Org_id']) && is_numeric($data['Org_id'])) {
					$org_union_new = $data['OrgUnionNew'];

					if (is_numeric($org_union_new)) {

						$numOrgUnionID = 1;

						$sql = "
							select
								OrgUnion_id
							from
								v_OrgUnion with (nolock)
							where
								OrgUnion_id = ?
						";

						$result = $this->db->query($sql, array($org_union_new));
					} else {

						$sql = "
							select
								OrgUnion_id
							from
								v_OrgUnion with (nolock)
							where
								OrgUnion_Name like ? and Server_id = ? and Org_id = ?
						";
						$result = $this->db->query($sql, array($org_union_new, $server_id, $data['Org_id']));
					}

					if (is_object($result)) {
						$sel = $result->result('array');
						if (isset($sel[0])) {
							if ($sel[0]['OrgUnion_id'] > 0)
								$OrgUnion_id = $sel[0]['OrgUnion_id'];
						} else if (isset($numOrgUnionID)) {
							$OrgUnion_id = null;
						} else {
							$sql = "
								declare @OrgUn_id bigint
								exec p_OrgUnion_ins
									@OrgUnion_Name = ?,
									@Org_id = ?,
									@pmUser_id = ?,
									@Server_id = ?,
									@OrgUnion_id=@OrgUn_id output
								select @OrgUn_id as OrgUnion_id;
							";
							$result = $this->db->query($sql, array($org_union_new, $data['Org_id'], $data['pmUser_id'], $server_id));
							if (is_object($result)) {
								$sel = $result->result('array');
								if ($sel[0]['OrgUnion_id'] > 0)
									$OrgUnion_id = $sel[0]['OrgUnion_id'];
							}
						}
					}
				}

				
				
				if (!isset($Org_id)) {
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonJob_del
						@Server_id = ?,
						@Person_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($server_id, $pid, $data['pmUser_id']));
				} else {


					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonJob_ins
						@Server_id = ?,
						@Person_id = ?,
						@Org_id = ?,
						@OrgUnion_id = ?,
						@Post_id = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";

					$res = $this->db->query($sql, array($server_id, $pid, $Org_id, $OrgUnion_id, $Post_id, $data['pmUser_id']));
				}

				$this->ValidateInsertQuery($res);
			}

			if($polisChange){
				//получаем последние данные по полису
				$policy = $this->getLastPeriodicalsByPolicy(array('Person_id' => $pid));
				// #174743. Если в блоке полей «Полис» поле «Территория» не заполнено и в активном состоянии человека отсутствует полис, 
				//то действия с полисом в активном состоянии человека и периодикой «Полис» не производятся.				
				if(empty($data['OMSSprTerr_id']) && empty($policy['Polis_id'])) $polisChange = false;
			}
			//Изменились атрибуты полиса
			if ($polisChange) {
				$OmsSprTerr_id = (empty($data['OMSSprTerr_id']) ? NULL : $data['OMSSprTerr_id']);
				$PolisType_id = (empty($data['PolisType_id']) ? NULL : $data['PolisType_id']);
				$OrgSmo_id = (empty($data['OrgSMO_id']) ? NULL : $data['OrgSMO_id']);
				$Polis_Ser = (empty($data['Polis_Ser']) ? '' : $data['Polis_Ser']);
				$PolisFormType_id = (empty($data['PolisFormType_id']) ? null : $data['PolisFormType_id']);
				$Polis_Num = (empty($data['Polis_Num']) ? '' : $data['Polis_Num']);
				$Polis_begDate = empty($data['Polis_begDate']) ? NULL : $data['Polis_begDate'];
				$Polis_endDate = empty($data['Polis_endDate']) ? NULL : $data['Polis_endDate'];

				if ($PolisType_id == 4) {
					$Polis_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);
					if (array_key_exists('Federal_Num', $newFields)) {
						$newFields['Polis_Num'] = $data['Federal_Num'];
					}
				}
				// если не указана территория, но указаны другие данные о полисе, очищаем другие данные и делаем пометку в логе. (refs #16940)
				if ($is_ufa && empty($OmsSprTerr_id) && (!empty($PolisType_id) || !empty($OrgSmo_id) || !empty($Polis_Ser) || !empty($Polis_Num) || !empty($Polis_begDate) || !empty($Polis_endDate))) {
					$this->textlog->add("Соханение человека с полисными данными без указанной территории страхования. Person_id: {$pid}, PolisType_id: {$PolisType_id}, OrgSmo_id: {$OrgSmo_id}, Polis_Ser: {$Polis_Ser}, Polis_Num: {$PolisType_id}, Polis_Num: {$PolisType_id}, Polis_begDate: {$Polis_begDate}, Polis_endDate: {$Polis_endDate}");

					$PolisType_id = NULL;
					$OrgSmo_id = NULL;
					$Polis_Ser = '';
					$Polis_Num = '';
					$Polis_begDate = NULL;
					$Polis_endDate = NULL;
				}

				$serv_id = $server_id;
				// для прав суперадмина или в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ($is_superadmin || $person_is_identified) {
					//$serv_id = 0;
				}


				// если человек из БДЗ и можем добавлять иннотериториальный полис
				if (!$is_ekb && !$is_astra && !$is_vologda && !$is_superadmin && !($is_pskov && $data['PersonIdentState_id'] != 0 && !empty($data['Person_identDT'])) && !$person_is_identified && isset($data['Polis_CanAdded']) && $data['Polis_CanAdded'] == 1) {
					// проверяем, иная ли территория
					$sql = "
						select
							KLRgn_id
						from
							OMSSprTerr with (nolock)
						where
							OMSSprTerr_id = ?
					";
					$res = $this->db->query($sql, array($OmsSprTerr_id));
					$sel = $res->result('array');

					if (count($sel) >= 1) {
						$region = $data['session']['region'];
						if (isset($region) && isset($region['number']) && $region['number'] > 0 && isset($sel[0]['KLRgn_id']) && $sel[0]['KLRgn_id'] > 0 && $sel[0]['KLRgn_id'] != $region['number']) {
							//Изменился единый номер
							$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);
							if (array_key_exists('Federal_Num', $newFields)) {
								if ($Federal_Num == '' && $PolisType_id == 4) {
									throw new Exception('Поле Ед. номер не может быть пустым');
								}
							}
							$serv_id = $server_id;
							// для прав суперадмина
							if ($is_superadmin) {
								//$serv_id = 0;
							}
							$fsql = "
								select
									top 1 PersonEvn_id,
									Server_id
								from
									v_Person_all with (nolock)
								where
									PersonEvnClass_id = 16 and
									Person_id = :Person_id
									and PersonEvn_insDT <= :begdate
								order by
									PersonEvn_insDT desc,
									PersonEvn_TimeStamp desc
							";
							$fres = $this->db->query($fsql, array('Person_id' => $pid, 'edNum' => $Federal_Num, 'begdate' => $Polis_begDate));
							if (is_object($fres)) {
								$fsel = $fres->result('array');
								if (count($fsel) == 0) {
									$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $pid, 'begdate' => $Polis_begDate));
									if ($checkEdNum === false) {
										$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
										throw new Exception("На дату {$date} уже создан Ед. номер.");
									}
									$sql = "
										declare @ErrCode int
										declare @ErrMsg varchar(400)
		
										exec p_PersonPolisEdNum_ins
										@Server_id = ?,
										@Person_id = ?,
										@PersonPolisEdNum_insDT = ?,
										@PersonPolisEdNum_EdNum = ?,
										@pmUser_id = ?,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
		
										select @ErrMsg as ErrMsg
									";
									if ($Federal_Num != '') {
										$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));

										$this->ValidateInsertQuery($res);
									}
								}
							}

							// сохраняем
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
	
								exec p_PersonPolis_ins
								@Server_id = ?,
								@Person_id = ?,
								@PersonPolis_insDT = ?,
								@OmsSprTerr_id = ?,
								@PolisType_id = ?,
								@OrgSmo_id = ?,
								@Polis_Ser = ?,
								@PolisFormType_id =?,
								@Polis_Num = ?,
								@Polis_begDate = ?,
								@Polis_endDate = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
	
								select @ErrMsg as ErrMsg
							";

							if(empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate){
								$res = $this->db->query($sql, array($server_id, $pid, $Polis_begDate, $OmsSprTerr_id, $PolisType_id, $OrgSmo_id, $Polis_Ser,$PolisFormType_id, $Polis_Num, $Polis_begDate, $Polis_endDate, $data['pmUser_id']));
								$this->ValidateInsertQuery($res);
							}

							// выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
	
								exec p_Person_server
								@Server_id = ?,
								@BDZ_Guid = ?,
								@Person_id = ?,
								@pmUser_id = ?,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output
	
								select @ErrMsg as ErrMsg
							";
							$BDZGUID = (isset($data['BDZ_Guid']))?$data['BDZ_Guid']:null;
							//echo getDebugSql($sql, array($server_id,$BDZGUID, $pid, $data['pmUser_id']));
							$res = $this->db->query($sql, array($server_id,$BDZGUID, $pid, $data['pmUser_id']));
							$this->ValidateInsertQuery($res);
						}else{
							throw new Exception('Регион инотериториального полиса совпадает с регионом текущей ЛПУ.');
						}
					}else{
						throw new Exception('Не найдены данные о регионе.');
					}
				}
				// сохраняем как обычно
				else {

					if (isset($OmsSprTerr_id)) {
						$check = $this->checkPolisIntersection($data);
						// если изменились какие то поля кроме дат, то создаём новую периодику
						if (
							(!isset($check['PersonEvn_id'])||!isset($check['Server_id']))&&
							(
								array_key_exists('OMSSprTerr_id', $newFields)
								|| array_key_exists('PolisType_id', $newFields)
								|| array_key_exists('Polis_Ser', $newFields)
								|| array_key_exists('Polis_Num', $newFields)
								|| array_key_exists('OrgSMO_id', $newFields)
								||array_key_exists('Federal_Num', $newFields)
								||($data['session']['region']['nick'] == 'kareliya'
									&&array_key_exists('Polis_begDate', $newFields)
									&&strtotime($data['Polis_begDate'])>strtotime($newFields['Polis_begDate'])
								)
							)
						) {
							// проверка есть ли предыдущий не закрытый полис и его закрытие (только если сохранение после идентификации).
							if ($check === false) {
								throw new Exception('Периоды полисов не могут пересекаться.');
							}

							$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);
							if (array_key_exists('Federal_Num', $newFields)) {
								if ($Federal_Num == '' && $PolisType_id == 4) {
									throw new Exception('Поле Ед. номер не может быть пустым');
								}
							}
							$serv_id = $server_id;
							// для прав суперадмина
							if ($is_superadmin) {
								//$serv_id = 0;
							}
							$fsql = "
								select top 1 
									PersonEvn_id,
									Server_id,
									Person_EdNum
								from
									v_Person_all with (nolock)
								where
									PersonEvnClass_id = 16
									and Person_id = :Person_id
									and PersonEvn_insDT = :begdate
								order by
									PersonEvn_insDT desc,
									PersonEvn_TimeStamp desc,
									case when Person_EdNum = :edNum then 0 else 1 end
							";
							$params = array('Person_id' => $pid, 'edNum' => $Federal_Num, 'begdate' => $Polis_begDate);
							$fres = $this->queryResult($fsql, $params);
							if (!is_array($fres)) {
								throw new Exception('Ошибка при получении данныз для проверки ед.номера полиса');
							}
							if (count($fres) == 0) {
								$checkEdNum = $this->checkPesonEdNumOnDate(array('Person_id' => $pid, 'begdate' => $Polis_begDate));
								if ($checkEdNum === false) {
									$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
									throw new Exception("На дату {$date} уже создан Ед. номер.");
								}

								$sql = "
									declare @ErrCode int
									declare @ErrMsg varchar(400)
	
									exec p_PersonPolisEdNum_ins
									@Server_id = ?,
									@Person_id = ?,
									@PersonPolisEdNum_insDT = ?,
									@PersonPolisEdNum_EdNum = ?,
									@pmUser_id = ?,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMsg output
	
									select @ErrMsg as ErrMsg
								";
								if ($Federal_Num != '') {
									$res = $this->db->query($sql, array($serv_id, $pid, $Polis_begDate, $Federal_Num, $data['pmUser_id']));

									$this->ValidateInsertQuery($res);
								}
							} else if ($fres[0]['Person_EdNum'] != $Federal_Num) {
								$date = ConvertDateFormat($Polis_begDate, 'd.m.Y');
								throw new Exception("На дату {$date} уже создан Ед. номер.");
							}

							if (getRegionNick() == 'buryatiya' && !empty($check['minDate'])) {
								// создаём 2 полиса
								if ($Polis_begDate < $check['minDate']) {
									// o	Дата начала = дата начала, полученная при идентификации;
									// o	Дата окончания = дата окончания случая (для СМП дата карты), который входит в реестр (если таких случаев больше одного, то используется с наименьшей датой (самый ранний случай) минус 1 день.
									$sql = "
										declare @ErrCode int
										declare @ErrMsg varchar(400)
										declare @res bigint
			
										set @res = null
										exec p_PersonPolis_ins
											@Polis_id = @res output,
											@Server_id = :Server_id,
											@Person_id = :Person_id,
											@PersonPolis_insDT = :Polis_begDate,
											@OmsSprTerr_id = :OmsSprTerr_id,
											@PolisType_id = :PolisType_id,
											@OrgSmo_id = :OrgSmo_id,
											@Polis_Ser = :Polis_Ser,
											@PolisFormType_id =:PolisFormType_id,
											@Polis_Num = :Polis_Num,
											@Polis_begDate = :Polis_begDate,
											@Polis_endDate = :Polis_endDate,
											@Polis_Guid = :Polis_Guid,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMsg output
			
										select @res as Polis_id, @ErrMsg as ErrMsg
									";
									/*echo getDebugSQL($sql, array(
										'Server_id' => $serv_id,
										'Person_id' => $pid,
										'OmsSprTerr_id' => $OmsSprTerr_id,
										'PolisType_id' => $PolisType_id,
										'OrgSmo_id' => $OrgSmo_id,
										'Polis_Ser' => $Polis_Ser,
										'PolisFormType_id' => $PolisFormType_id,
										'Polis_Num' => $Polis_Num,
										'Polis_Guid'=>($person_is_identified&&isset($data['Polis_Guid']))?$data['Polis_Guid']:null,
										'Polis_begDate' => $Polis_begDate,
										'Polis_endDate' => $Polis_endDate,
										'pmUser_id' => $data['pmUser_id']
									));*/

									$res = $this->db->query($sql, array(
											'Server_id' => ($person_is_identified) ? 0 : $serv_id,
											'Person_id' => $pid,
											'OmsSprTerr_id' => $OmsSprTerr_id,
											'PolisType_id' => $PolisType_id,
											'OrgSmo_id' => $OrgSmo_id,
											'Polis_Ser' => $Polis_Ser,
											'PolisFormType_id' => $PolisFormType_id,
											'Polis_Num' => $Polis_Num,
											'Polis_Guid' => ($person_is_identified && isset($data['Polis_Guid'])) ? $data['Polis_Guid'] : null,
											'Polis_begDate' => $Polis_begDate,
											'Polis_endDate' => date('Y-m-d', strtotime($check['minDate']) - 24*60*60),
											'pmUser_id' => $data['pmUser_id']
										)
									);

									if ($person_is_identified) {
										$resbdz = $res->result('array');

									}
									$this->ValidateInsertQuery($res);
								}

								if (empty($Polis_endDate) || $Polis_endDate > $check['maxDate']) {
									// o	Дата начала = дата окончания случая (для СМП дата карты), который входит в реестр (если таких случаев больше одного, то используется с наибольшей датой (последний случай) плюс 1.
									// o	Дата окончания = дата полученная, при идентификации

									$sql = "
										declare @ErrCode int
										declare @ErrMsg varchar(400)
										declare @res bigint
			
										set @res = null
										exec p_PersonPolis_ins
											@Polis_id = @res output,
											@Server_id = :Server_id,
											@Person_id = :Person_id,
											@PersonPolis_insDT = :Polis_begDate,
											@OmsSprTerr_id = :OmsSprTerr_id,
											@PolisType_id = :PolisType_id,
											@OrgSmo_id = :OrgSmo_id,
											@Polis_Ser = :Polis_Ser,
											@PolisFormType_id =:PolisFormType_id,
											@Polis_Num = :Polis_Num,
											@Polis_begDate = :Polis_begDate,
											@Polis_endDate = :Polis_endDate,
											@Polis_Guid = :Polis_Guid,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMsg output
			
										select @res as Polis_id, @ErrMsg as ErrMsg
									";
									/*echo getDebugSQL($sql, array(
										'Server_id' => $serv_id,
										'Person_id' => $pid,
										'OmsSprTerr_id' => $OmsSprTerr_id,
										'PolisType_id' => $PolisType_id,
										'OrgSmo_id' => $OrgSmo_id,
										'Polis_Ser' => $Polis_Ser,
										'PolisFormType_id' => $PolisFormType_id,
										'Polis_Num' => $Polis_Num,
										'Polis_Guid'=>($person_is_identified&&isset($data['Polis_Guid']))?$data['Polis_Guid']:null,
										'Polis_begDate' => $Polis_begDate,
										'Polis_endDate' => $Polis_endDate,
										'pmUser_id' => $data['pmUser_id']
									));*/

									$res = $this->db->query($sql, array(
											'Server_id' => ($person_is_identified) ? 0 : $serv_id,
											'Person_id' => $pid,
											'OmsSprTerr_id' => $OmsSprTerr_id,
											'PolisType_id' => $PolisType_id,
											'OrgSmo_id' => $OrgSmo_id,
											'Polis_Ser' => $Polis_Ser,
											'PolisFormType_id' => $PolisFormType_id,
											'Polis_Num' => $Polis_Num,
											'Polis_Guid' => ($person_is_identified && isset($data['Polis_Guid'])) ? $data['Polis_Guid'] : null,
											'Polis_begDate' => date('Y-m-d', strtotime($check['maxDate']) + 24*60*60),
											'Polis_endDate' => $Polis_endDate,
											'pmUser_id' => $data['pmUser_id']
										)
									);

									if ($person_is_identified) {
										$resbdz = $res->result('array');

									}
									$this->ValidateInsertQuery($res);
								}
							} else {
								$allowCreate = true;

								if (getRegionNick() == 'ufa') {
									$query = "
										declare
											@begDate date = :Polis_begDate,
											@endDate date = :Polis_endDate,
											@defaultDate date = '2000-01-01'
										select top 1 
											count(*) as cnt
										from
											v_PersonPolis with (nolock)
										where
											PersonEvnClass_id = 8
											and Person_id = :Person_id
											and cast(PersonPolis_insDT as date) = @begDate
											and OmsSprTerr_id = :OmsSprTerr_id
											and PolisType_id = :PolisType_id
											and OrgSmo_id = :OrgSmo_id
											and isnull(Polis_Ser, '') = isnull(:Polis_Ser, '')
											and isnull(PolisFormType_id, '') = isnull(:PolisFormType_id, '')
											and isnull(Polis_Num, '') = isnull(:Polis_Num, '')
											and Polis_begDate = @begDate
											and isnull(Polis_endDate, @defaultDate) = isnull(@endDate, @defaultDate)
									";
									$params = array(
										'Server_id' => ($person_is_identified) ? 0 : $serv_id,
										'Person_id' => $pid,
										'OmsSprTerr_id' => $OmsSprTerr_id,
										'PolisType_id' => $PolisType_id,
										'OrgSmo_id' => $OrgSmo_id,
										'Polis_Ser' => $Polis_Ser,
										'PolisFormType_id' => $PolisFormType_id,
										'Polis_Num' => $Polis_Num,
										'Polis_begDate' => $Polis_begDate,
										'Polis_endDate' => $Polis_endDate,
									);
									//echo getDebugSQL($query, $params);exit;
									$cnt = $this->getFirstResultFromQuery($query, $params);
									if ($cnt === false) {
										throw new Exception('Ошибка при проверке существования полиса');
									}
									if ($cnt > 0) {
										$allowCreate = false;
									}
								}

								if ($allowCreate) {
									$sql = "
										declare @ErrCode int
										declare @ErrMsg varchar(400)
										declare @res bigint
			
										set @res = null
										exec p_PersonPolis_ins
											@Polis_id = @res output,
											@Server_id = :Server_id,
											@Person_id = :Person_id,
											@PersonPolis_insDT = :Polis_begDate,
											@OmsSprTerr_id = :OmsSprTerr_id,
											@PolisType_id = :PolisType_id,
											@OrgSmo_id = :OrgSmo_id,
											@Polis_Ser = :Polis_Ser,
											@PolisFormType_id =:PolisFormType_id,
											@Polis_Num = :Polis_Num,
											@Polis_begDate = :Polis_begDate,
											@Polis_endDate = :Polis_endDate,
											@Polis_Guid = :Polis_Guid,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMsg output
			
										select @res as Polis_id, @ErrMsg as ErrMsg
									";
									$params = array(
										'Server_id' => ($person_is_identified) ? 0 : $serv_id,
										'Person_id' => $pid,
										'OmsSprTerr_id' => $OmsSprTerr_id,
										'PolisType_id' => $PolisType_id,
										'OrgSmo_id' => $OrgSmo_id,
										'Polis_Ser' => $Polis_Ser,
										'PolisFormType_id' => $PolisFormType_id,
										'Polis_Num' => $Polis_Num,
										'Polis_Guid' => ($person_is_identified && isset($data['Polis_Guid'])) ? $data['Polis_Guid'] : null,
										'Polis_begDate' => $Polis_begDate,
										'Polis_endDate' => $Polis_endDate,
										'pmUser_id' => $data['pmUser_id']
									);
									/*echo getDebugSQL($sql, $params);*/
									if (empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate) {
										$res = $this->db->query($sql, $params);
										if ($person_is_identified) {
											$resbdz = $res->result('array');
										}
										$this->ValidateInsertQuery($res);
									}
								}
							}
						}
						// если изменились только даты полиса то обновляем периодику
						else {

							if (isset($check['PersonEvn_id']) && isset($check['Server_id'])) {
								// периодика по полису, с которым пересекается полис, пришедший при идентификации
								$sel = array($check);
								$data['PersonEvn_id'] = $sel[0]['PersonEvn_id'];
							} else {
								// получаем последнюю периодику по полису
								$sql = "
									select
										top 1 PersonEvn_id,
										Server_id,
										Polis_id
									from
										v_Person_all with (nolock)
									where
										PersonEvnClass_id = 8 and
										Person_id = :Person_id
									order by
										PersonEvn_insDT desc,
										PersonEvn_TimeStamp desc
								";
								$sel = $this->queryResult($sql, array('Person_id' => $pid));
								if (is_array($sel) && count($sel) > 0) {
									$data['PersonEvn_id'] = $sel[0]['PersonEvn_id'];
									$check = $this->checkPolisIntersection($data);
									if ($check === false) {
										throw new Exception('Периоды полисов не могут пересекаться.');
									}
								}
							}

							if (is_array($sel) && count($sel) > 0) {
								if (array_key_exists('Federal_Num', $newFields) && (!isset($check['Polis_Num']) || (string)$data['Federal_Num'] !== (string)$check['Polis_Num'])) {
									$fsql = "
										select
											top 1 PersonEvn_id,
											Server_id
										from
											v_Person_all with (nolock)
										where
											PersonEvnClass_id = 16 and
											Person_id = :Person_id
										order by
											PersonEvn_insDT desc,
											PersonEvn_TimeStamp desc
									";
									//echo getDebugSQL($fsql, array('Person_id' => $pid));exit;
									$fres = $this->db->query($fsql, array('Person_id' => $pid));
									if (is_object($fres)) {
										$fsel = $fres->result('array');
										if (count($fsel) > 0) {
											$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);

											if ($Federal_Num == '' && $PolisType_id == 4) {
												throw new Exception('Поле Ед. номер не может быть пустым');
											}

											$serv_id = $server_id;
											// для прав суперадмина
											if ($is_superadmin) {
												//$serv_id = 0;
											}

											$sql = "
												declare @ErrCode int
												declare @ErrMsg varchar(400)
												declare @serverId bigint
	
												set @serverId = (select Server_id
																from dbo.v_PersonEvn with (nolock)
																where Server_id = :serv_id
																and PersonEvn_id = :peid)
												if @serverId is null
												begin
												select @serverId = Server_id from dbo.v_PersonEvn with (nolock) where PersonEvn_id = :peid
												end
	
												exec p_PersonPolisEdNum_upd
												@Server_id = @serverId,
												@Person_id = :pid,
												@PersonPolisEdNum_id = :peid,
												@PersonPolisEdNum_EdNum = :Federal_Num,
												@pmUser_id = :pmUser_id,
												@Error_Code = @ErrCode output,
												@Error_Message = @ErrMsg output
	
												select @ErrMsg as ErrMsg
											";
											/*echo getDebugSQL($sql, array(
												'serv_id'=>$serv_id,
												'pid'=>$pid,
												'peid'=>$fsel[0]['PersonEvn_id'],
												'Federal_Num'=>$Federal_Num,
												'pmUser_id'=>$data['pmUser_id']
											));exit;*/
											$res = $this->db->query($sql, array(
												'serv_id'=>$serv_id,
												'pid'=>$pid,
												'peid'=>$fsel[0]['PersonEvn_id'],
												'Federal_Num'=>$Federal_Num,
												'pmUser_id'=>$data['pmUser_id']
											));
											$this->ValidateInsertQuery($res);
											//$this->editPersonEvnDate(array('PersonEvn_id' => $fsel[0]['PersonEvn_id'], 'Date' => $Polis_begDate, 'Server_id' => $serv_id, 'pmUser_id' => $data['pmUser_id']));
										} else {
											// создаём
											$Federal_Num = (empty($data['Federal_Num']) ? '' : $data['Federal_Num']);

											if ($Federal_Num == '' && $PolisType_id == 4) {
												throw new Exception('Поле Ед. номер не может быть пустым');
											}

											$serv_id = $server_id;
											// для прав суперадмина
											if ($is_superadmin) {
												//$serv_id = 0;
											}

											$sql = "
												declare @ErrCode int
												declare @ErrMsg varchar(400)
	
												exec p_PersonPolisEdNum_ins
												@Server_id = :serv_id,
												@Person_id = :pid,
												@PersonPolisEdNum_insDT = :PersonPolisEdNum_insDT,
												@PersonPolisEdNum_EdNum = :Federal_Num,
												@pmUser_id = :pmUser_id,
												@Error_Code = @ErrCode output,
												@Error_Message = @ErrMsg output
	
												select @ErrMsg as ErrMsg
											";
											/*echo getDebugSQL($sql, array(
												'serv_id' => $serv_id,
												'pid' => $pid,
												'PersonPolisEdNum_insDT' => $Polis_begDate,
												'Federal_Num' => $Federal_Num,
												'pmUser_id' => $data['pmUser_id']
											));exit;*/
											$res = $this->db->query($sql, array(
												'serv_id' => $serv_id,
												'pid' => $pid,
												'PersonPolisEdNum_insDT' => $Polis_begDate,
												'Federal_Num' => $Federal_Num,
												'pmUser_id' => $data['pmUser_id']
											));
											$this->ValidateInsertQuery($res);
										}
									}
								}

								$sql = "
									declare @ErrCode int
									declare @ErrMsg varchar(400)
									declare @serverId bigint
	
									set @serverId = (select Server_id
													from dbo.v_PersonEvn with(nolock)
													where Server_id = :serv_id
													and PersonEvn_id = :peid)
									if @serverId is null
									begin
									select @serverId = Server_id from dbo.v_PersonEvn with(nolock) where PersonEvn_id = :peid
									end
	
									exec p_PersonPolis_upd
										@PersonPolis_id = :peid,
										@Server_id = @serverId,
										@Person_id = :Person_id,
										@OmsSprTerr_id = :OmsSprTerr_id,
										@PolisType_id = :PolisType_id,
										@OrgSmo_id = :OrgSmo_id,
										@Polis_Ser = :Polis_Ser,
										@PolisFormType_id =:PolisFormType_id,
										@Polis_Num = :Polis_Num,
										@Polis_begDate = :Polis_begDate,
										@Polis_endDate = :Polis_endDate,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
	
									select @ErrMsg as ErrMsg
								";
								/*echo getDebugSQL($sql, array(
									'peid' => $sel[0]['PersonEvn_id'],
									'serv_id' => $sel[0]['Server_id'],
									'Person_id' => $pid,
									'OmsSprTerr_id' => $OmsSprTerr_id,
									'PolisType_id' => $PolisType_id,
									'OrgSmo_id' => $OrgSmo_id,
									'Polis_Ser' => $Polis_Ser,
									'PolisFormType_id'=>$PolisFormType_id,
									'Polis_Num' => $Polis_Num,
									'Polis_begDate' => $Polis_begDate,
									'Polis_endDate' => $Polis_endDate,
									'pmUser_id' => $data['pmUser_id']
								));exit;*/
								if(empty($Polis_endDate) || $Polis_endDate>=$Polis_begDate){
									$res = $this->db->query($sql, array(
										'peid' => $sel[0]['PersonEvn_id'],
										'serv_id' => $sel[0]['Server_id'],
										'Person_id' => $pid,
										'OmsSprTerr_id' => $OmsSprTerr_id,
										'PolisType_id' => $PolisType_id,
										'OrgSmo_id' => $OrgSmo_id,
										'Polis_Ser' => $Polis_Ser,
										'PolisFormType_id'=>$PolisFormType_id,
										'Polis_Num' => $Polis_Num,
										'Polis_begDate' => $Polis_begDate,
										'Polis_endDate' => $Polis_endDate,
										'pmUser_id' => $data['pmUser_id']
									));

									$this->ValidateInsertQuery($res);
									$this->editPersonEvnDate(array('person_is_identified' => $person_is_identified, 'session'=>$data['session'],'PersonEvn_id' => $sel[0]['PersonEvn_id'], 'Date' => $Polis_begDate, 'Server_id' => $sel[0]['Server_id'], 'pmUser_id' => $data['pmUser_id']));
								}
								/*-----*/
								if($person_is_identified){
									$sql = "
										declare
											@ErrCode int,
											@ErrMessage varchar(4000),
											@Polis_id bigint;
	
										set @Polis_id=(select top 1 polis_id from v_PersonPolis with(nolock) where PersonPolis_id=:PersonEvn_id)
	
										exec p_Polis_server
											@Polis_id = @Polis_id,
											@Server_id = :Server_id,
											@Polis_Guid = :Polis_Guid,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
	
										select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";
									/*echo getDebugSQL($sql, array(
										'PersonEvn_id' => $sel[0]['PersonEvn_id'],
										'Server_id' => 0,
										'Polis_Guid'=>$data['Polis_Guid'],
										'pmUser_id' => $data['pmUser_id']
									));*/
									$res = $this->db->query($sql, array(
										'PersonEvn_id' => $sel[0]['PersonEvn_id'],
										'Server_id' => 0,
										'Polis_Guid'=>(isset($data['Polis_Guid']))?$data['Polis_Guid']:null,
										'pmUser_id' => $data['pmUser_id']
									));

									if (!is_object($res)) {
										throw new Exception('Ошибка при выполнении запроса к базе данных (проставление признака идентификации по сводной базе застрахованных)');
									}

									$response = $res->result('array');

									if (!is_array($response) || count($response) == 0) {
										throw new Exception('Ошибка при проставлении признака идентификации по сводной базе застрахованных');
									}
								}else if(array_key_exists('Polis_begDate', $newFields)){
									$sql = "
										declare
											@ErrCode int,
											@ErrMessage varchar(4000),
											@Polis_id bigint;
	
										set @Polis_id=(select top 1 polis_id from v_PersonPolis with(nolock) where PersonPolis_id=:PersonEvn_id)
	
										exec p_Polis_server
											@Polis_id = @Polis_id,
											@Server_id = :Server_id,
											@Polis_Guid = :Polis_Guid,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
	
										select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";
									/*echo getDebugSQL($sql, array(
										'PersonEvn_id' => $sel[0]['PersonEvn_id'],
										'Server_id' => 0,
										'Polis_Guid'=>$data['Polis_Guid'],
										'pmUser_id' => $data['pmUser_id']
									));*/
									$res = $this->db->query($sql, array(
										'PersonEvn_id' => $sel[0]['PersonEvn_id'],
										'Server_id' => 0,
										'Polis_Guid'=>null,
										'pmUser_id' => $data['pmUser_id']
									));
								}
							}
						}
					} elseif (
						empty($OmsSprTerr_id) && empty($PolisType_id) && empty($OrgSmo_id)
						&& empty($Polis_Ser) && empty($Polis_Num) && empty($Polis_begDate) && empty($Polis_endDate)
					) {
						// получаем последнюю периодику по полису
						$sql = "
							select
								top 1 PersonPolis_id,
								Server_id,
								Polis_id,
								Polis_endDate
							from
								v_PersonPolis with (nolock)
							where
								Person_id = :Person_id
							order by
								PersonPolis_insDT desc,
								PersonPolis_TimeStamp desc
						";
						//echo getDebugSQL($sql, array('Person_id' => $pid));exit;
						$res = $this->db->query($sql, array('Person_id' => $pid));
						if (is_object($res)) {
							$sel = $res->result('array');
							if (count($sel) > 0) {
								if(empty($sel[0]['Polis_endDate'])){
									//в ТЗ не описано, но по логике как подразумевается, что закрытый полис закрывать дважды не надо (согласно комента проектировщика)
									$sql = "update Polis set Polis_endDate = dbo.tzGetDate() where Polis_id = :Polis_id";
									$this->db->query($sql, array('Polis_id' => $sel[0]['Polis_id']));
								}

								/*
								 * o	#174743 новая периодика не создается
								/*
								 * o	#174743 новая периодика не создается
								$sql = "
									declare @ErrCode int
									declare @ErrMsg varchar(400)
									declare @PersonPolis_id bigint
	
									exec p_PersonPolis_del
										@PersonPolis_id = @PersonPolis_id output,
										@Server_id = :Server_id,
										@Person_id = :Person_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
	
									select @PersonPolis_id as PersonEvn_id, @ErrMsg as ErrMsg
								";

								$res = $this->db->query($sql, array(
									'Server_id' => $sel[0]['Server_id'],
									'Person_id' => $pid,
									'pmUser_id' => $data['pmUser_id']
								));
								$this->ValidateInsertQuery($res);
								$sel = $res->result('array');
								$data['PersonEvn_id'] = $sel[0]['PersonEvn_id'];
								*/
							}
						}
					}
				}

				if (!empty($data['Person_id']) && $data['Person_id'] != 0 && $data['Person_id'] != null) {
					$sql = "exec xp_PersonTransferEvn @Person_id = ?";
					$res = $this->db->query($sql, array($data['Person_id']));
				}
			} else {
				// если атрибуты полиса не менялись, но выбран полис иной территории, то надо снять признак БДЗ (для Карелии по краней мере)
				if (getRegionNick() == 'kareliya') {
					$OmsSprTerr_id = (empty($data['OMSSprTerr_id']) ? NULL : $data['OMSSprTerr_id']);
					// проверяем, иная ли территория
					$sql = "
						select
							KLRgn_id
						from
							OMSSprTerr with (nolock)
						where
							OMSSprTerr_id = ?
					";
					$res = $this->db->query($sql, array($OmsSprTerr_id));
					$sel = $res->result('array');
					if (count($sel) >= 1) {
						$regionNumber = getRegionNumber();
						if (!empty($regionNumber) && !empty($sel[0]['KLRgn_id']) && $sel[0]['KLRgn_id'] != $regionNumber) {
							// выводим из рядов БДЗ (Устанавливаем сервер ид ЛПУ)
							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
				
								exec p_Person_server
									@Server_id = ?,
									@BDZ_Guid = ?,
									@Person_id = ?,
									@pmUser_id = ?,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMsg output
				
								select @ErrMsg as ErrMsg
							";
							$BDZGUID = (isset($data['BDZ_Guid'])) ? $data['BDZ_Guid'] : null;
							$res = $this->db->query($sql, array($server_id, $BDZGUID, $pid, $data['pmUser_id']));
							$this->ValidateInsertQuery($res);
						}
					}
				}
			}

			/*$result = $this->getFirstResultFromQuery($query, array('Person_id' => $pid));
			if($result['Person_BDZCode']==null&&isset($data['rz'])&&$data['rz']!=''){
				$query ="update personinfo set Person_BDZCode = :rz where Person_id = :Person_id";
				$this->db->query($query,array('rz'=>$data['rz'], 'Person_id' => $pid));
			}*/
			//Изменились атрибуты гражданства
			if (array_key_exists('KLCountry_id', $newFields) ||
				array_key_exists('NationalityStatus_IsTwoNation', $newFields) ||
				array_key_exists('LegalStatusVZN_id', $newFields)
			) {
				$serv_id = $server_id;

				$KLCountry_id = empty($data['KLCountry_id']) ? NULL : $data['KLCountry_id'];
				$NationalityStatus_IsTwoNation = $data['NationalityStatus_IsTwoNation'] ? 2 : 1;
				$LegalStatusVZN_id = empty($data['LegalStatusVZN_id']) ? NULL : $data['LegalStatusVZN_id'];

				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)

					exec p_PersonNationalityStatus_ins
					@Server_id = ?,
					@Person_id = ?,
					@KLCountry_id = ?,
					@NationalityStatus_IsTwoNation = ?,
					@LegalStatusVZN_id = ?,
					@pmUser_id = ?,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql, array($serv_id, $pid, $KLCountry_id, $NationalityStatus_IsTwoNation, $LegalStatusVZN_id, $data['pmUser_id']));
				$this->ValidateInsertQuery($res);
			}
			//Изменились атрибуты документа
			if (array_key_exists('DocumentType_id', $newFields) || array_key_exists('Document_Ser', $newFields) ||
				array_key_exists('Document_Num', $newFields) ||
				array_key_exists('OrgDep_id', $newFields) || array_key_exists('Document_begDate', $newFields)
			) {
				$serv_id = $server_id;
				// в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ($person_is_identified) {
					//$serv_id = 0;
				}

				$DocumentType_id = (empty($data['DocumentType_id']) ? NULL : $data['DocumentType_id']);
				$OrgDep_id = (empty($data['OrgDep_id']) ? NULL : $data['OrgDep_id']);
				$Document_Ser = (empty($data['Document_Ser']) ? '' : $data['Document_Ser']);
				$Document_Num = (empty($data['Document_Num']) ? '' : $data['Document_Num']);
				$Document_begDate = empty($data['Document_begDate']) ? NULL : $data['Document_begDate'];
				if (isset($DocumentType_id)) {
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonDocument_ins
						@Server_id = ?,
						@Person_id = ?,
						@DocumentType_id = ?,
						@OrgDep_id = ?,
						@Document_Ser = ?,
						@Document_Num = ?,
						@Document_begDate = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($serv_id, $pid, $DocumentType_id, $OrgDep_id, $Document_Ser, $Document_Num, $Document_begDate, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				} elseif (
					empty($DocumentType_id) && empty($OrgDep_id)
					&& empty($Document_Ser) && empty($Document_Num) && empty($Document_begDate)
				) {
					// получаем последнюю периодику по документу
					$sql = "
						select top 1
							PersonDocument_id,
							Server_id,
							Document_id,
							Document_endDate
						from
							v_PersonDocument with (nolock)
						where
							Person_id = :Person_id
						order by
							PersonDocument_insDT desc,
							PersonDocument_TimeStamp desc
					";
					//echo getDebugSQL($sql, array('Person_id' => $pid));exit;
					$res = $this->db->query($sql, array('Person_id' => $pid));
					if (is_object($res)) {
						$sel = $res->result('array');
						if (count($sel) > 0) {
							$sql = "update Document set Document_endDate = dbo.tzGetDate() where Document_id = :Document_id";
							$this->db->query($sql, array('Document_id' => $sel[0]['Document_id']));

							$sql = "
								declare @ErrCode int
								declare @ErrMsg varchar(400)
								declare @PersonDocument_id bigint
								exec p_PersonDocument_del
									@PersonDocument_id = @PersonDocument_id output,
									@Server_id = :Server_id,
									@Person_id = :Person_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMsg output
								select @PersonDocument_id as PersonEvn_id, @ErrMsg as ErrMsg
							";

							$res = $this->db->query($sql, array(
								'Server_id' => $sel[0]['Server_id'],
								'Person_id' => $pid,
								'pmUser_id' => $data['pmUser_id']
							));

							$this->ValidateInsertQuery($res);
							$sel = $res->result('array');
							$data['PersonEvn_id'] = $sel[0]['PersonEvn_id'];
						}
					}
				}
			}
			//Изменились атрибуты адреса регистрации
			if (array_key_exists('UKLCountry_id', $newFields) || array_key_exists('UKLRGN_id', $newFields) ||
				array_key_exists('UKLSubRGN_id', $newFields) ||
				array_key_exists('UKLCity_id', $newFields) || array_key_exists('UKLTown_id', $newFields) ||
				array_key_exists('UKLStreet_id', $newFields) ||
				array_key_exists('UAddress_House', $newFields) || array_key_exists('UAddress_Corpus', $newFields) ||
				array_key_exists('UAddress_Flat', $newFields) ||
				array_key_exists('UAddress_Zip', $newFields) ||
				array_key_exists('UAddress_Address', $newFields) ||
				/*array_key_exists('UAddress_begDate', $newFields) ||*/( $is_ufa === true && array_key_exists('UPersonSprTerrDop_id', $newFields) && $data['UPersonSprTerrDop_id'] > 0 )
			) {
				$KLCountry_id = (empty($data['UKLCountry_id']) ? NULL : $data['UKLCountry_id']);
				$KLRgn_id = (empty($data['UKLRGN_id']) ? NULL : $data['UKLRGN_id']);
				$KLRgnSocr_id = (empty($data['UKLRGNSocr_id']) ? NULL : $data['UKLRGNSocr_id']);
				$KLSubRgn_id = (empty($data['UKLSubRGN_id']) ? NULL : $data['UKLSubRGN_id']);
				$KLSubRgnSocr_id = (empty($data['UKLSubRGNSocr_id']) ? NULL : $data['UKLSubRGNSocr_id']);
				$KLCity_id = (empty($data['UKLCity_id']) ? NULL : $data['UKLCity_id']);
				$KLCitySocr_id = (empty($data['UKLCitySocr_id']) ? NULL : $data['UKLCitySocr_id']);
				$KLTown_id = (empty($data['UKLTown_id']) ? NULL : $data['UKLTown_id']);
				$KLTownSocr_id = (empty($data['UKLTownSocr_id']) ? NULL : $data['UKLTownSocr_id']);
				$KLStreet_id = (empty($data['UKLStreet_id']) ? NULL : $data['UKLStreet_id']);
				$KLStreetSocr_id = (empty($data['UKLStreetSocr_id']) ? NULL : $data['UKLStreetSocr_id']);
				$Address_Zip = (empty($data['UAddress_Zip']) ? '' : $data['UAddress_Zip']);
				$Address_House = (empty($data['UAddress_House']) ? '' : $data['UAddress_House']);
				$Address_Corpus = (empty($data['UAddress_Corpus']) ? '' : $data['UAddress_Corpus']);
				$Address_Flat = (empty($data['UAddress_Flat']) ? '' : $data['UAddress_Flat']);
				//$Address_begDate = (empty($data['UAddress_begDate']) ? NULL : $data['UAddress_begDate']);
				$Address_Address = (empty($data['UAddress_Address']) ? (empty($data['UAddress_AddressText']) ? '' : $data['UAddress_AddressText']) : $data['UAddress_Address']);
				$PersonSprTerrDop_id = (empty($data['UPersonSprTerrDop_id']) ? NULL : $data['UPersonSprTerrDop_id']);
				$AddressSpecObject_id= (empty($data['UAddressSpecObject_id']) ? NULL : $data['UAddressSpecObject_id']);

				/*$address_cond = "";
				if ($is_ufa === true) {
					if ($data['UPersonSprTerrDop_id'] > 0) {
						$Address_Address = (empty($data['UAddress_Address']) ? null : $data['UAddress_Address']);
						$address_cond = " ISNULL(a.Address_Address, '') != ISNULL(:Address_Address, '') or ";
					} else {
						$Address_Address = null;
					}
				}*/

				$serv_id = $server_id;
				// в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ($person_is_identified) {
					//$serv_id = 0;
				}
				$sql = "
					select
						count(*) as cnt
					from
						v_PersonState s with (nolock)
						left join [address] a with (nolock) on a.address_id = s.UAddress_id
					where
						Person_id = :Person_id and(
							ISNULL(a.KLCountry_id, 0) != ISNULL(:KLCountry_id, 0) or
							ISNULL(a.KLRgn_id, 0) != ISNULL(:KLRgn_id, 0) or 
							ISNULL(a.KLSubRgn_id, 0) != ISNULL(:KLSubRgn_id, 0) or
							ISNULL(a.KLCity_id, 0) != ISNULL(:KLCity_id, 0) or
							ISNULL(a.KLTown_id, 0) != ISNULL(:KLTown_id, 0) or
							ISNULL(a.KLStreet_id, 0) != ISNULL(:KLStreet_id, 0) or
							ISNULL(a.Address_Zip, '') != ISNULL(:Address_Zip, '') or
							ISNULL(a.Address_House, '') != ISNULL(:Address_House, '') or
							ISNULL(a.Address_Corpus, '') != ISNULL(:Address_Corpus, '') or
							ISNULL(a.Address_Flat, '') != ISNULL(:Address_Flat, '') or
							ISNULL(a.Address_Address, '') != ISNULL(:Address_Address, '') or
							ISNULL(a.PersonSprTerrDop_id, 0) != ISNULL(:PersonSprTerrDop_id, 0) or
							s.UAddress_id is null
						)
				";

				$result = $this->db->query($sql, array(
					'Person_id' => $pid,
					'KLCountry_id' => $KLCountry_id,
					'KLRgn_id' => $KLRgn_id,
					'KLSubRgn_id' => $KLSubRgn_id,
					'KLCity_id' => $KLCity_id,
					'KLTown_id' => $KLTown_id,
					'KLStreet_id' => $KLStreet_id,
					'Address_Zip' => $Address_Zip,
					'Address_House' => $Address_House,
					'Address_Corpus' => $Address_Corpus,
					'Address_Flat' => $Address_Flat,
					'Address_Address' => $Address_Address,
					'PersonSprTerrDop_id' => $PersonSprTerrDop_id
				));

				$result = $result->result('array');
				if ($result[0]['cnt'] == 1 || $terr_dop_change['U']) {
					if (!empty($Address_Address) || !empty($KLRgn_id)) {
						// Сохранение данных стран кроме РФ, которые ранее отсутствовали
						list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
							$this->saveAddressAll($serv_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);

						// Сохранение непосредственно адреса (ИДов)
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
	
							exec p_PersonUAddress_ins
							@Server_id = ?,
							@Person_id = ?,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@AddressSpecObject_id =?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
	
							select @ErrMsg as ErrMsg
						";
						//echo getDebugSQL($sql, array($serv_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));exit;
						$res = $this->db->query($sql, array($serv_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat,$AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "
							select top 1
								Server_id,
								PersonUAddress_id
							from v_PersonUAddress with(nolock)
							where
								Person_id = :Person_id
							order by
								PersonUAddress_insDT desc,
								PersonUAddress_TimeStamp desc
						";
						$res = $this->db->query($sql, array('Person_id' => $pid));
						if (is_object($res)) {
							$sel = $res->result('array');
							if (count($sel) > 0) {
								$sql = "
									declare @ErrCode int
									declare @ErrMsg varchar(400)
									declare @PersonUAddress_id bigint
	
									exec p_PersonUAddress_del
										@PersonUAddress_id = @PersonUAddress_id output,
										@Server_id = :Server_id,
										@Person_id = :Person_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
	
									select @PersonUAddress_id as PersonEvn_id, @ErrMsg as ErrMsg
								";

								$res = $this->db->query($sql, array(
									'Server_id' => $sel[0]['Server_id'],
									'Person_id' => $pid,
									'pmUser_id' => $data['pmUser_id']
								));
								$this->ValidateInsertQuery($res);
							}
						}
					}
				}
			}




			//TODO:Изменились атрибуты адреса рождения

			if (array_key_exists('BKLCountry_id', $newFields) || array_key_exists('BKLRGN_id', $newFields) ||
				array_key_exists('BKLSubRGN_id', $newFields) ||
				array_key_exists('BKLCity_id', $newFields) || array_key_exists('BKLTown_id', $newFields) ||
				array_key_exists('BKLStreet_id', $newFields) ||
				array_key_exists('BAddress_House', $newFields) || array_key_exists('BAddress_Corpus', $newFields) ||
				array_key_exists('BAddress_Flat', $newFields) ||
				array_key_exists('BAddress_Zip', $newFields) || array_key_exists('BAddress_Address', $newFields) ||
				/*array_key_exists('BAddress_begDate', $newFields) ||*/( $is_ufa === true && array_key_exists('BPersonSprTerrDop_id', $newFields) && $data['BPersonSprTerrDop_id'] > 0 )
			) {
				$Address_Address = trim(empty($data['BAddress_Address']) ? null : $data['BAddress_Address']);

				$KLCountry_id = (empty($data['BKLCountry_id']) ? NULL : $data['BKLCountry_id']);
				$KLRgn_id = (empty($data['BKLRGN_id']) ? NULL : $data['BKLRGN_id']);
				$KLRgnSocr_id = (empty($data['BKLRGNSocr_id']) ? NULL : $data['BKLRGNSocr_id']);
				$KLSubRgn_id = (empty($data['BKLSubRGN_id']) ? NULL : $data['BKLSubRGN_id']);
				$KLSubRgnSocr_id = (empty($data['BKLSubRGNSocr_id']) ? NULL : $data['BKLSubRGNSocr_id']);
				$KLCity_id = (empty($data['BKLCity_id']) ? NULL : $data['BKLCity_id']);
				$KLCitySocr_id = (empty($data['BKLCitySocr_id']) ? NULL : $data['BKLCitySocr_id']);
				$KLTown_id = (empty($data['BKLTown_id']) ? NULL : $data['BKLTown_id']);
				$KLTownSocr_id = (empty($data['BKLTownSocr_id']) ? NULL : $data['BKLTownSocr_id']);
				$KLStreet_id = (empty($data['BKLStreet_id']) ? NULL : $data['BKLStreet_id']);
				$KLStreetSocr_id = (empty($data['BKLStreetSocr_id']) ? NULL : $data['BKLStreetSocr_id']);
				$Address_Zip = (empty($data['BAddress_Zip']) ? '' : $data['BAddress_Zip']);
				$Address_House = (empty($data['BAddress_House']) ? '' : $data['BAddress_House']);
				$Address_Corpus = (empty($data['BAddress_Corpus']) ? '' : $data['BAddress_Corpus']);
				$Address_Flat = (empty($data['BAddress_Flat']) ? '' : $data['BAddress_Flat']);
				$PersonSprTerrDop_id = (empty($data['BPersonSprTerrDop_id']) ? NULL : $data['BPersonSprTerrDop_id']);
				$AddressSpecObject_id= (empty($data['BAddressSpecObject_id']) ? NULL : $data['BAddressSpecObject_id']);
				/*if ($is_ufa === true && !empty($KLRgn_id) && empty($data['BPersonSprTerrDop_id'])) {
					$Address_Address = null;
				}*/

				$serv_id = $server_id;
				// в случае идентицикации человека по сводному регистру застрахованных в Уфе
				if ($person_is_identified) {
					//$serv_id = 0;
				}

				// Сохранение данных стран кроме РФ, которые ранее отсутствовали
				list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
					$this->saveAddressAll($serv_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);
				$sql = "
					select 
						Address_id,
						PersonBirthPlace_id
					from 
						PersonBirthPlace (nolock)
					where 
						Person_id = ?
				";

				$res = $this->db->query($sql, array($pid));
				$sel = $res->result('array');

				if (count($sel) == 0) {
					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
						declare @Address_id bigint
	
						exec p_Address_ins
						@Server_id = ?,
						@Address_id = @Address_id output,
						@KLCountry_id = ?,
						@KLRgn_id = ?,
						@KLSubRgn_id = ?,
						@KLCity_id = ?,
						@KLTown_id = ?,
						@KLStreet_id = ?,
						@Address_Zip = ?,
						@Address_House = ?,
						@Address_Corpus = ?,
						@Address_Flat = ?,
						@AddressSpecObject_id = ?,
						@PersonSprTerrDop_id = ?,
						@Address_Address = ?,
						@pmUser_id = ?,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg, @Address_id as Address_id  
					";
					$res = $this->db->query($sql, array($serv_id, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat,$AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));
					$this->ValidateInsertQuery($res);

					$address_id = $res->result('array');

					$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
	
						exec p_PersonBirthPlace_ins
							@Person_id = ?,
							@Address_id = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
	
						select @ErrMsg as ErrMsg
					";

					$res = $this->db->query($sql, array($pid, $address_id[0]['Address_id'], $data['pmUser_id']));
					$this->ValidateInsertQuery($res);
				} else {
					$arr = array($KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat, $PersonSprTerrDop_id, $Address_Address);
					$delete = true;
					foreach ($arr as $key) {
						if (!empty($key)) {
							$delete = false;
						}
					}
					if (!$delete) {
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @Address_id bigint
	
							exec p_Address_upd
							@Server_id = ?,
							@Address_id = ?,
							@KLAreaType_id = null,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@AddressSpecObject_id = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@KLAreaStat_id = null,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
	
							select @ErrMsg as ErrMsg  
						";
						$res = $this->db->query($sql, array($serv_id, $sel[0]['Address_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat,$AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
							declare @PersonBirthPlace_id bigint
							declare @Address_id bigint
							
							exec p_PersonBirthPlace_del
							@PersonBirthPlace_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
							
							/*
							exec p_Address_del
							@Address_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
							*/
							
							select @ErrMsg as ErrMsg; 
						";
						$res = $this->db->query($sql, array($sel[0]['PersonBirthPlace_id'], $sel[0]['Address_id']));
						$this->ValidateInsertQuery($res);
					}
				}
			}

			//Изменились атрибуты адреса проживания
			if (array_key_exists('PKLCountry_id', $newFields) || array_key_exists('PKLRGN_id', $newFields) ||
				array_key_exists('PKLSubRGN_id', $newFields) ||
				array_key_exists('PKLCity_id', $newFields) || array_key_exists('PKLTown_id', $newFields) ||
				array_key_exists('PKLStreet_id', $newFields) ||
				array_key_exists('PAddress_House', $newFields) || array_key_exists('PAddress_Corpus', $newFields) ||
				array_key_exists('PAddress_Flat', $newFields) ||
				array_key_exists('PAddress_Zip', $newFields) ||
				array_key_exists('PAddress_Address', $newFields) ||
				/*array_key_exists('PAddress_begDate', $newFields) ||*/( $is_ufa === true && array_key_exists('PPersonSprTerrDop_id', $newFields) && $data['PPersonSprTerrDop_id'] > 0 )
			) {
				$KLCountry_id = (empty($data['PKLCountry_id']) ? NULL : $data['PKLCountry_id']);
				$KLRgn_id = (empty($data['PKLRGN_id']) ? NULL : $data['PKLRGN_id']);
				$KLRgnSocr_id = (empty($data['PKLRGNSocr_id']) ? NULL : $data['PKLRGNSocr_id']);
				$KLSubRgn_id = (empty($data['PKLSubRGN_id']) ? NULL : $data['PKLSubRGN_id']);
				$KLSubRgnSocr_id = (empty($data['PKLSubRGNSocr_id']) ? NULL : $data['PKLSubRGNSocr_id']);
				$KLCity_id = (empty($data['PKLCity_id']) ? NULL : $data['PKLCity_id']);
				$KLCitySocr_id = (empty($data['PKLCitySocr_id']) ? NULL : $data['PKLCitySocr_id']);
				$KLTown_id = (empty($data['PKLTown_id']) ? NULL : $data['PKLTown_id']);
				$KLTownSocr_id = (empty($data['PKLTownSocr_id']) ? NULL : $data['PKLTownSocr_id']);
				$KLStreet_id = (empty($data['PKLStreet_id']) ? NULL : $data['PKLStreet_id']);
				$KLStreetSocr_id = (empty($data['PKLStreetSocr_id']) ? NULL : $data['PKLStreetSocr_id']);
				$Address_Zip = (empty($data['PAddress_Zip']) ? '' : $data['PAddress_Zip']);
				$Address_House = (empty($data['PAddress_House']) ? '' : $data['PAddress_House']);
				$Address_Corpus = (empty($data['PAddress_Corpus']) ? '' : $data['PAddress_Corpus']);
				$Address_Flat = (empty($data['PAddress_Flat']) ? '' : $data['PAddress_Flat']);
				//$Address_begDate = (empty($data['PAddress_begDate']) ? NULL : $data['PAddress_begDate']);
				$Address_Address = (empty($data['PAddress_Address']) ? '' : $data['PAddress_Address']);
				$PersonSprTerrDop_id = (empty($data['PPersonSprTerrDop_id']) ? NULL : $data['PPersonSprTerrDop_id']);
				$AddressSpecObject_id= (empty($data['PAddressSpecObject_id']) ? NULL : $data['PAddressSpecObject_id']);
				/*$address_cond = "";
				if ($is_ufa === true) {
					if ($data['PPersonSprTerrDop_id'] > 0) {
						$Address_Address = (empty($data['PAddress_Address']) ? null : $data['PAddress_Address']);
						$address_cond = " or ISNULL(a.Address_Address, '') != ISNULL(:Address_Address, '') ";
					} else {
						$Address_Address = null;
					}
				}*/

				$sql = "
					select
						count(*) as cnt
					from
						v_PersonState s with (nolock)
						left join [address] a with (nolock) on a.Address_id = s.PAddress_id
					where Person_id = :Person_id
						and (
							ISNULL(a.KLCountry_id, 0) != ISNULL(:KLCountry_id, 0)
							or ISNULL(a.KLRgn_id, 0) != ISNULL(:KLRgn_id, 0)
							or ISNULL(a.KLSubRgn_id, 0) != ISNULL(:KLSubRgn_id, 0)
							or ISNULL(a.KLCity_id, 0) != ISNULL(:KLCity_id, 0)
							or ISNULL(a.KLTown_id, 0) != ISNULL(:KLTown_id, 0)
							or ISNULL(a.KLStreet_id, 0) != ISNULL(:KLStreet_id, 0)
							or ISNULL(a.Address_Zip, '') != ISNULL(:Address_Zip, '')
							or ISNULL(a.Address_House, '') != ISNULL(:Address_House, '')
							or ISNULL(a.Address_Corpus, '') != ISNULL(:Address_Corpus, '')
							or ISNULL(a.Address_Flat, '') != ISNULL(:Address_Flat, '')
							or ISNULL(a.PersonSprTerrDop_id, 0) != ISNULL(:PersonSprTerrDop_id, 0)
							or s.PAddress_id is null
						)
				";

				$result = $this->db->query($sql, array(
					'Person_id' => $pid,
					'KLCountry_id' => $KLCountry_id,
					'KLRgn_id' => $KLRgn_id,
					'KLSubRgn_id' => $KLSubRgn_id,
					'KLCity_id' => $KLCity_id,
					'KLTown_id' => $KLTown_id,
					'KLStreet_id' => $KLStreet_id,
					'Address_Zip' => $Address_Zip,
					'Address_House' => $Address_House,
					'Address_Corpus' => $Address_Corpus,
					'Address_Flat' => $Address_Flat,
					'PersonSprTerrDop_id' => $PersonSprTerrDop_id,
					'Address_Address' => $Address_Address
				));
				$result = $result->result('array');
				if ($result[0]['cnt'] == 1 || $terr_dop_change['P']) {
					if (!empty($Address_Address) || !empty($KLRgn_id)) {
						// Сохранение данных стран кроме РФ, которые ранее отсутствовали
						list($KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id) =
							$this->saveAddressAll($server_id, $data['pmUser_id'], $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $KLRgnSocr_id, $KLSubRgnSocr_id, $KLCitySocr_id, $KLTownSocr_id, $KLStreetSocr_id);

						$sql = "
							declare @ErrCode int
							declare @ErrMsg varchar(400)
	
							exec p_PersonPAddress_ins
							@Server_id = ?,
							@Person_id = ?,
							@KLCountry_id = ?,
							@KLRgn_id = ?,
							@KLSubRgn_id = ?,
							@KLCity_id = ?,
							@KLTown_id = ?,
							@KLStreet_id = ?,
							@Address_Zip = ?,
							@Address_House = ?,
							@Address_Corpus = ?,
							@Address_Flat = ?,
							@AddressSpecObject_id = ?,
							@PersonSprTerrDop_id = ?,
							@Address_Address = ?,
							@pmUser_id = ?,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
	
							select @ErrMsg as ErrMsg
						";
						$res = $this->db->query($sql, array($server_id, $pid, $KLCountry_id, $KLRgn_id, $KLSubRgn_id, $KLCity_id, $KLTown_id, $KLStreet_id, $Address_Zip, $Address_House, $Address_Corpus, $Address_Flat,$AddressSpecObject_id, $PersonSprTerrDop_id, $Address_Address, $data['pmUser_id']));
						$this->ValidateInsertQuery($res);
					} else {
						$sql = "
							select top 1
								Server_id,
								PersonPAddress_id
							from v_PersonPAddress with(nolock)
							where
								Person_id = :Person_id
							order by
								PersonPAddress_insDT desc,
								PersonPAddress_TimeStamp desc
						";
						$res = $this->db->query($sql, array('Person_id' => $pid));
						if (is_object($res)) {
							$sel = $res->result('array');
							if (count($sel) > 0) {
								$sql = "
									declare @ErrCode int
									declare @ErrMsg varchar(400)
									declare @PersonPAddress_id bigint
		
									exec p_PersonPAddress_del
										@PersonPAddress_id = @PersonPAddress_id output,
										@Server_id = :Server_id,
										@Person_id = :Person_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMsg output
		
									select @PersonPAddress_id as PersonEvn_id, @ErrMsg as ErrMsg
								";

								$res = $this->db->query($sql, array(
										'Server_id' => $sel[0]['Server_id'],
										'Person_id' => $pid,
										'pmUser_id' => $data['pmUser_id']
									)
								);
								$this->ValidateInsertQuery($res);
							}
						}
					}
				}
			}

			//Изменились атрибуты специфики детства
			if (array_key_exists('ResidPlace_id', $newFields)
				|| array_key_exists('PersonChild_IsManyChild', $newFields)
				|| array_key_exists('PersonChild_IsBad', $newFields)
				|| array_key_exists('PersonChild_IsYoungMother', $newFields)
				|| array_key_exists('PersonChild_IsIncomplete', $newFields)
				|| array_key_exists('PersonChild_IsTutor', $newFields)
				|| array_key_exists('PersonChild_IsMigrant', $newFields)
				|| array_key_exists('HealthKind_id', $newFields)
				|| array_key_exists('FeedingType_id', $newFields)
				|| array_key_exists('PersonChild_CountChild', $newFields)
				|| array_key_exists('PersonChild_IsInvalid', $newFields)
				|| array_key_exists('InvalidKind_id', $newFields)
				|| array_key_exists('PersonChild_invDate', $newFields)
				|| array_key_exists('HealthAbnorm_id', $newFields)
				|| array_key_exists('HealthAbnormVital_id', $newFields)
				|| array_key_exists('Diag_id', $newFields)
				|| array_key_exists('PersonSprTerrDop_id', $newFields)
			) {
				$resp = $this->savePersonChild(array_merge($data, array(
					'Person_id' => $pid,
					'Server_id' => $server_id
				)));
				$this->ValidateInsertQuery($resp);
			}
			// Выбираем запись либо с Server_id больницы, либо если ее нет, с Server_id = 0
			$sql = "
				select top 1 
					PersonEvn_id,
					Server_id 
				from PersonState with (nolock)
				where Person_id = :Person_id
				order by Server_id desc
			";
			$params = array('Person_id' => $pid);
			$resp = $this->getFirstRowFromQuery($sql, $params, true);
			if ($resp === false) {
				throw new Exception('Ошибка при получени текущей периодики человека');
			}
			if (!empty($resp)) {
				$peid = $resp['PersonEvn_id'];
				$server_id = $resp['Server_id']; // берем server_id из выборки, ибо он может быть и 0
			} else {
				$peid = 'NULL';
			}
			$this->_saveResponse = array_merge($this->_saveResponse, array(
				'Person_id' => $pid, 'PersonEvn_id' => $peid, 'Server_id' => $server_id
			));
			if (!empty($pguid)) {
				$this->_saveResponse['Person_Guid'] = $pguid;
			} else {
                $this->_saveResponse['Person_Guid'] = $this->getFirstResultFromQuery("
                    select top 1 Person_Guid from Person with(nolock) where Person_id = :Person_id
                ", array('Person_id' => $pid), true);
                if ($this->_saveResponse['Person_Guid'] === false) {
                    throw new Exception('Ошибка при получени GUID человека');
                }
            }
			if($is_double){
				$dbl = $this->checkExistPersonDouble($bdzData['Person_id'],$data['Person_id']);
				if($dbl==true){
					//throw new Exception('Человек уже находится в очереди на объединение двойников');
					$this->addInfoMsg('Человек уже находится в очереди на объединение двойников');
				}else{
					$query = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = null;
						exec pd.p_PersonDoubles_ins
							@PersonDoubles_id = @Res output,
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as PersonDoubles_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					$queryParams = array(
						'Person_id'=>$bdzData['Person_id'],
						'Person_did'=>$data['Person_id'],
						'pmUser_id'=>$data['pmUser_id']
					);
					$result = $this->queryResult($query, $queryParams);
					//Если возвращается ошибка, то выдаем пользователю и выходим
					if (!is_array($result)) {
						throw new Exception('Ошибка при выполнении запроса к базе данных (объединение)');
					} else {
						//throw new Exception('Человек добавлен в очередь на объединение двойников');
						$this->addInfoMsg('При идентификации обнаружен двойник. Отправлено в очередь на объединение');
					}
				}
			}

			if(!empty($data['Employment_id'])) {
				$procedure = '';
				$sql ='';
				$timestamp = strtotime(date('Y-m-d H:i:s', time()));
				if(!empty($data['PersonEmployment_id'])) {
					$procedure = 'p_PersonEmployment_upd';
					$sql ='@PersonEmployment_Rowversion = :PersonEmployment_Rowversion,';
				} else{
					$procedure = 'p_PersonEmployment_ins';
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :PersonEmployment_id;
		
					exec {$procedure}
						@PersonEmployment_id = @Res output,
						@Person_id = :Person_id,
						@Server_id = :Server_id,
						@pmUser_id = :pmUser_id,
						@Employment_id = :Employment_id,
						{$sql}
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @Res as PersonEmployment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$queryParams = array(
					'PersonEmployment_id' => (!empty($data['PersonEmployment_id']) ? $data['PersonEmployment_id']: NULL),
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Employment_id' => $data['Employment_id'],
					'PersonEmployment_Rowversion' => $timestamp,
				);

				$result = $this->db->query($query, $queryParams)->result('array');
				if (!is_array($result)) {
					throw new Exception('Ошибка при выполнении запроса сохранения занятости');
				}
			}
			if(!empty($data['EducationLevel_id'])) {
				$sql = '';
				if(!empty($data['PersonEduLevel_id'])) {
					$procedure = 'p_PersonEduLevel_upd';
					$sql ='@PersonEduLevel_Rowversion = :PersonEduLevel_Rowversion,';
				} else{
					$procedure = 'p_PersonEduLevel_ins';
				}
				$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :PersonEduLevel_id;
	
				exec {$procedure}
					@PersonEduLevel_id = @Res output,
					@Person_id = :Person_id,
					@Server_id = :Server_id,
					@pmUser_id = :pmUser_id,
					@EducationLevel_id = :EducationLevel_id,
					{$sql}
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
	
				select @Res as PersonEduLevel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

				$queryParams = array(
					'PersonEduLevel_id' => (!empty($data['PersonEduLevel_id']) ? $data['PersonEduLevel_id']: NULL),
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
					'EducationLevel_id' => $data['EducationLevel_id'],
					'PersonEduLevel_Rowversion' => $timestamp,
				);

				$result = $this->db->query($query, $queryParams)->result('array');
				if (!is_array($result)) {
					throw new Exception('Ошибка при выполнении запроса сохранения образования');
				}
			}


			
			if(!isset($data['Server_pid'])){
				$data['Server_pid'] = 0;
			}

			$currDate = date_create($this->getCurrentDT()->format('Y-m-d'));
			$begDate = !empty($data['Polis_begDate'])?date_create($data['Polis_begDate']):null;
			$endDate = !empty($data['Polis_endDate'])?date_create($data['Polis_endDate']):null;
			$hasPolis = (!empty($begDate) && $begDate <= $currDate && (empty($endDate) || $endDate > $currDate));

			//Отправка в очередь на идентификацию в ЦС ЕРЗ
			if (
				($isPerm && !empty($pid) && $mainChange && ($data['Server_pid'] != 0 || ($data['Server_pid'] == 0 && !$hasPolis))) ||
				($is_penza && !empty($pid) && $data['mode'] == 'add')
			) {
				$this->isAllowTransaction = false;
				//метод addPersonRequestData для Пензы переопределен в региональной модели
				$resp = $this->addPersonRequestData(array(
					'Person_id' => $pid,
					'pmUser_id' => $data['pmUser_id'],
					'PersonRequestSourceType_id' => ($data['mode'] == 'add')?5:1,	//Новый человек:Редактирование человека
				));
				$this->isAllowTransaction = true;
				if (!$this->isSuccessful($resp) && !in_array($resp[0]['Error_Code'], array(302, 303))) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}

			//Сохранение записи для идентификации в ТФОМС
			if ($isKrym && !empty($pid) && ($mainChange || $data['mode']) == 'add') {
				$this->load->model('PersonIdentPackage_model');
				$this->isAllowTransaction = false;
				$this->PersonIdentPackage_model->addPersonIdentPackagePos(array(
					'Person_id' => $pid,
					'pmUser_id' => $data['pmUser_id'],
					'PersonIdentPackageTool_id' => 1,
				));
				$this->isAllowTransaction = true;
				if (!$this->isSuccessful($resp) && !in_array($resp[0]['Error_Code'], array(302, 303))) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}

			//Синхронизация прикреплений из сервиса РПН Казахстана
			if ($is_kz && !empty($pid) && !empty($data['BDZ_id']) && !empty($data['Person_identDT']) && $data['Person_IsInErz'] == 2) {
				$this->load->model('ServiceRPN_model');

				$this->ServiceRPN_model->saveSyncObject('Person', $pid, $data['BDZ_id']);

				$resp = $this->ServiceRPN_model->syncPersonCards(array(
					'Person_id' => $pid,
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				$Lpu_Nick = $this->getFirstResultFromQuery("
					select top 1 L.Lpu_Nick
					from v_PersonCard PC with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					where PC.Person_id = :Person_id
				", array('Person_id' => $pid), true);
				if ($Lpu_Nick === false) {
					throw new Exception('Ошибка при получении последнего прикрепления');
				}
				$this->_saveResponse['Lpu_Nick'] = $Lpu_Nick;
			}
		} catch(Exception $e) {
			if (isset($_REQUEST['isDebug'])) {
				$this->textlog->add("Строка: {$e->getLine()}. Ошибка: {$e->getMessage()}");
			}

			$this->rollbackTransaction();


			$this->_saveResponse['Error_Msg'] = $e->getMessage();



			$this->_saveResponse['Error_Code'] = $e->getCode();
			return array($this->_saveResponse);
		}

		$this->exceptionOnValidation = false;
		$this->commitTransaction();

		if (!empty($this->_saveResponse['Person_id']) && $needSnilsVerification && (!$saveWithOutActiveMQ || !empty($data['useSMP']))) {
			$this->verifyPersonSnils([
				'Person_id' => $this->_saveResponse['Person_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		if (($IsLocalSMP === true || $IsSMPServer === true) && !$regionWithOutActiveMQ) {
			if (!empty($this->_saveResponse['Person_id'])) {
				// отправляем человека в основную БД через очередь ActiveMQ
				$this->load->model('Replicator_model');
				$this->Replicator_model->sendRecordToActiveMQ(array(
					'table' => 'Person',
					'type' => ($data['mode'] == 'add') ? 'insert' : 'update',
					'keyParam' => 'Person_id',
					'keyValue' => $this->_saveResponse['Person_id']
				));
			}
		}

		if ($saveWithOutActiveMQ && !empty($this->_saveResponse['Person_id']) && empty($data['useSMP'])) {
			// персона с таким же Person_id создаем в БД СМП.
			$data['useSMP'] = true;
			$data['Person_id'] = $this->_saveResponse['Person_id'];
			$data['Person_Guid'] = $this->_saveResponse['Person_Guid'];
			$this->savePersonEditWindow($data);
		}
		return array($this->_saveResponse);
	}

	/**
	 *
	 * @param type $Person_id
	 * @param type $Person_did
	 * @return type 
	 */
	function checkExistPersonDouble($Person_id,$Person_did){
		$sql = "
			select COUNT(*) as cnt
			from pd.PersonDoubles PD with(nolock)
			where (PD.Person_id=:Person_id and PD.Person_did = :Person_did)
			or(PD.Person_id=:Person_did and PD.Person_did = :Person_id)
		";
		$queryParams=array(
			'Person_id'=>$Person_id,
			'Person_did'=>$Person_did
		);
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			if($sel[0]['cnt']>0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $level
	 * @param $name
	 * @param $pid
	 * @param $socr_id
	 * @return int|null
	 * Сохранение данных стран кроме РФ, которые ранее отсутствовали
	 * Эта функция сохраняет и возвращает ИД только 1 части адреса: "Регион", "Район", "Город", "Нас. пункт", "Улица"
	 */
	function saveAddressPart($srv_id, $user_id, $country, $level, $name, $pid, $socr_id) {
		if ($level < 5) {
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(200)

				exec p_KLArea_ins
				@KLCountry_id = ?,
				@KLAreaLevel_id = ?,
				@KLArea_pid = ?,
				@KLArea_Name = ?,
				@KLAdr_Actual = ?,
				@KLSocr_id = ?,
				@Server_id = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";

			$res = $this->db->query($sql, array($country, $level, $pid, $name, 0, $socr_id, $srv_id, $user_id));
		} else {
			$sql = "
				declare @ErrCode int
				declare @ErrMsg varchar(200)

				exec p_KLStreet_ins
				@KLArea_id = ?,
				@KLSocr_id = ?,
				@KLStreet_Name = ?,
				@KLAdr_Code = ?,
				@KLAdr_Actual = ?,
				@Server_id = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";

			$res = $this->db->query($sql, array($pid, $socr_id, $name, NULL, 0, $srv_id, $user_id));
		}
		$this->ValidateInsertQuery($res);

		if (is_object($res)) {
			return $this->db->insert_id();
		} else {
			return NULL;
		}
	}

	/**
	 * @param $srv_id
	 * @param $user_id
	 * @param $country
	 * @param $region
	 * @param $subregion
	 * @param $city
	 * @param $town
	 * @param $street
	 * @param $region_socr
	 * @param $subregion_socr
	 * @param $city_socr
	 * @param $town_socr
	 * @param $street_socr
	 * @return array
	 * Сохранение адреса
	 */
	function saveAddressAll($srv_id, $user_id, $country, $region, $subregion, $city, $town, $street, $region_socr, $subregion_socr, $city_socr, $town_socr, $street_socr) {
		if (isset($region) && !is_numeric($region)) { // Новый регион
			$region = $this->saveAddressPart($srv_id, $user_id, $country, 1, $region, $country, $region_socr);
		}
		if (isset($subregion) && !is_numeric($subregion)) { // Новый сабрегион
			$subregion = $this->saveAddressPart($srv_id, $user_id, $country, 2, $subregion, (isset($region) ? $region : $country), $subregion_socr);
		}
		if (isset($city) && !is_numeric($city)) { // Новый город
			$city = $this->saveAddressPart($srv_id, $user_id, $country, 3, $city, (isset($subregion) ? $subregion : (isset($region) ? $region : $country)), $city_socr);
		}
		if (isset($town) && !is_numeric($town)) { // Новый нас. пункт
			$town = $this->saveAddressPart($srv_id, $user_id, $country, 4, $town, (isset($city) ? $city : (isset($subregion) ? $subregion : (isset($region) ? $region : $country))), $town_socr);
		}
		if (isset($street) && !is_numeric($street)) { // Новая улица
			$street = $this->saveAddressPart($srv_id, $user_id, $country, 5, $street, (isset($town) ? $town : (isset($city) ? $city : (isset($subregion) ? $subregion : (isset($region) ? $region : $country)))), $street_socr);
		}
		return array($region, $subregion, $city, $town, $street);
	}

	/**
	 * @param $res
	 * Проверка результатов выполнения запроса, возврат ошибки, если что-то пошло не так.
	 */
	function ValidateInsertQuery($res) {
		$error_text = '';
		$arr = array();
		if (is_array($res)) $arr = $res;
		if (is_object($res)) $arr = $res->result('array');
		if (count($arr) > 0) {
			foreach ($arr as $rows) {
				$err = null;
				if (!empty($rows['ErrMsg'])) $err = $rows['ErrMsg'];
				if (!empty($rows['Error_Msg'])) $err = $rows['Error_Msg'];
				if ($err) {
					$err = addslashes($err);
					$error_text = "Произошла ошибка при сохранении данных. <p style=\"color: red\">{$err}</p>";
				}
			}
		} else {
			$error_text = 'Непонятная ошибка при сохранении данных.';
		}
		if (!empty($error_text)) {
			if ($this->exceptionOnValidation) {
				throw new Exception($error_text);
			} else {
				DieWithError($error_text);
			}
		}
	}

	/**
	 * 	Получение антропометрических данных человека
	 */
	function getAnthropometryViewData($data) {
		$query = "
			select
				PH.PersonHeight_id,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate,
				PH.PersonHeight_Height,
				ISNULL(IsAbnorm.YesNo_Name, '') as PersonHeight_IsAbnorm,
				ISNULL(HAT.HeightAbnormType_Name, '') as HeightAbnormType_Name,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name
			from
				v_PersonHeight PH with (nolock)
				left join v_YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join v_HeightAbnormType HAT with (nolock) on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
			where
				PH.Person_id = :Person_id
			order by
				PH.PersonHeight_setDT
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
				));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 	Получение списка двойников из БДЗ по ФИО + ДР с открытыми полисами на заданную дату
	 */
	function getPersonDoublesByFIODR($data) {
		$query = "
			select top 1
				PS.Person_id,
				P.Polis_Ser,
				P.Polis_Num
			from
				v_PersonState PS with (nolock)
				inner join Polis P with (nolock) on P.Polis_id = PS.Polis_id
			where
				PS.Person_id <> :Person_id
				and PS.Server_pid = 0
				and PS.Person_BirthDay = :Person_Birthday
				and PS.Person_FirName = :Person_Firname
				and PS.Person_SecName = :Person_Secname
				and PS.Person_SurName = :Person_Surname
				and (P.Polis_begDate is null or P.Polis_begDate <= :Date)
				and (P.Polis_endDate is null or P.Polis_endDate > :Date)
		";

		$queryParams = array(
			'Date' => $data['Date'],
			'Person_id' => $data['Person_id'],
			'Person_Birthday' => $data['Person_Birthday'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
			'Person_Surname' => $data['Person_Surname']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 	Получение информации полиса
	 */
	function getPersonPolisInfo($data) {

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
                (ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '')) as Polis_FIO,
                SMO.OrgSMO_Name as PolisOrgSmo,
				case
					when Polis.PolisType_id = 4 and ISNULL(PS.Person_EdNum, '') != '' then PS.Person_EdNum
					else RTRIM(LTRIM(ISNULL(Polis.Polis_Ser, '') + ' ' + ISNULL(Polis.Polis_Num, '')))
				end as PolisSerNum,
                ISNULL(convert(varchar, cast(Polis.Polis_begDate as datetime),104), 'Действует') as Polis_begDate,
                ISNULL(convert(varchar, cast(Polis.Polis_endDate as datetime),104),'Действует') as Polis_endDate
            from
                v_PersonState_All PS with (nolock)
                left join v_Polis Polis with (nolock) on Polis.Polis_id = PS.Polis_id
                left join v_OrgSMO SMO with (nolock) on SMO.OrgSmo_id = Polis.OrgSmo_id
            where
                PS.Person_id = :Person_id
		";

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 	Получение телефона
	 */
	function getPersonPhoneInfo($data) {

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
            select
                PS.Person_id,
                PS.Server_pid as Server_id,
                PS2.PersonPhone_id,
                --RTRIM(PS.Person_Phone) as Phone_Promed,
				case
					when len(RTRIM(PS.Person_Phone)) = 10 then '(' + left(RTRIM(PS.Person_Phone), 3) + ')-' + substring(RTRIM(PS.Person_Phone), 4, 3) + '-' + 
						substring(RTRIM(PS.Person_Phone), 7, 2) + '-' + right(RTRIM(PS.Person_Phone), 2)
					else ''
				end as Phone_Promed,
                RTRIM(PIF.PersonInfo_InternetPhone) as Phone_Site,
                (ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '')) as Person_FIO
            from
                v_PersonState PS with (nolock)
                left join v_PersonInfo PIf with (nolock) on PIf.Person_id = PS.Person_id
                left join PersonState PS2 with (nolock) on PS2.Person_id = PS.Person_id
            where
            PS.Person_id = :Person_id
		";

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 	Получение данных о месте работы
	 */
	function getPersonJobInfo($data) {

		$select = ""; $join = "";

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		if (!empty($data['fromMobile'])) {
			$select = ",
				o.Org_Name,
				o.Org_StickNick,
				o.Org_Nick
			";

			$join = " left join v_Org o (nolock) on o.Org_id = pjob.Org_id ";
		}

		$query = "
		select
			pjob.Org_id,
			pjob.OrgUnion_id,
			pjob.Post_id,
			p.Post_Name
			{$select}
		from 
			v_PersonState vper with (nolock)
			left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
			left join v_Post p with (nolock) on p.Post_id = pjob.Post_id
			{$join}
		where
			vper.Person_id= :Person_id
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение телефона пациента в промеде
	 */
	function savePersonPhoneInfo($data) {

		if (!empty($data['PersonPhone_id'])) {
			$proc = 'upd';

			$query = "
                select top 1
                    Server_id
                from
                    v_PersonPhone with (nolock)
                where
                    PersonPhone_id = :PersonPhone_id
            ";

			$result = $this->db->query($query, array('PersonPhone_id' => $data['PersonPhone_id']));

			if (is_object($result)) {
				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 ) {
					$data['Server_id'] = $response[0]['Server_id'];
				}
			} else {
				return false;
			}
		} else {
			$proc = 'ins';
		}

		$query = "
			declare
				@PersonPhone_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonPhone_id = :PersonPhone_id;
			exec dbo.p_PersonPhone_" . $proc . "
				@PersonPhone_id = @PersonPhone_id output,
				@PersonPhone_Phone = :Phone_Promed,
				@Person_id = :Person_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonPhone_id as Person_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'Phone_Promed' => $data['Phone_Promed'],
			'PersonPhone_id' => $data['PersonPhone_id'],
			'Person_id' => $data['Person_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSql($query, $params);die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			return $result->result('array');
		} else {

			return false;
		}
	}

	/**
	 * Получение Person_id по ФИО + Полис + Д/Р, полученные из УЕК
	 */
	function getPersonByUecData($data) {
		$query = "
			select top 1
				PS.Person_id, 
				PS.Server_id, 
				PS.PersonEvn_id
			from
				v_PersonState PS with (nolock)
			where
				PS.Person_BirthDay = :Person_BirthDay
				and PS.Person_FirName = :Person_FirName
				and PS.Person_SecName = :Person_SecName
				and PS.Person_SurName = :Person_SurName
				and PS.Person_EdNum = :Polis_Num
			order by case when ps.Server_pid = 0 THEN 1 ELSE 0 END desc
		";

		$queryParams = array(
			'Person_BirthDay' => $data['Person_BirthDay'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName'],
			'Person_SurName' => $data['Person_SurName'],
			'Polis_Num' => $data['Polis_Num']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array($resp[0]); // возвращаем первого попавшегося
			} else {
				// если не нашли ищем только по фио и ДР среди тех у кого не указан ЕНП
				$query = "
					select top 1
						PS.Person_id, 
						PS.Server_id, 
						PS.PersonEvn_id
					from
						v_PersonState PS with (nolock)
					where
						PS.Person_BirthDay = :Person_BirthDay
						and PS.Person_FirName = :Person_FirName
						and PS.Person_SecName = :Person_SecName
						and PS.Person_SurName = :Person_SurName
						and PS.Person_EdNum IS NULL
					order by case when ps.Server_pid = 0 THEN 1 ELSE 0 END desc
				";

				$queryParams = array(
					'Person_BirthDay' => $data['Person_BirthDay'],
					'Person_FirName' => $data['Person_FirName'],
					'Person_SecName' => $data['Person_SecName'],
					'Person_SurName' => $data['Person_SurName']
				);

				$result = $this->db->query($query, $queryParams);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						return array($resp[0]); // возвращаем первого попавшегося
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * 	Получение Person_id по ФИО + Полис + Д/Р + Пол, прочитанные из штрих-кода
	 */
	function getPersonByBarcodeData($data) {
		$filterList = array();
		$queryParams = $data;

		//В рамках задач http://redmine.swan.perm.ru/issues/22161 и http://redmine.swan.perm.ru/issues/22891 изменил алгоритм поиска
		//Сначала получим результаты обоих запросов.
		$query_oms = "
				select top 1
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					1 as resultType
				from v_PersonState PS with (nolock)
				where PS.Polis_Num = :Polis_Num
			";
		$result_oms = $this->db->query($query_oms, $queryParams);

		if (!empty($data['Person_Surname'])) {
			$filterList[] = "PS.Person_Surname = :Person_Surname";
		}
		if (!empty($data['Person_Firname'])) {
			$filterList[] = "PS.Person_Firname = :Person_Firname";
		}
		if (!empty($data['Person_Secname'])) {
			$filterList[] = "PS.Person_Secname = :Person_Secname";
		}
		if (!empty($data['Person_Birthday'])) {
			$filterList[] = "PS.Person_Birthday = :Person_Birthday";
		}
		if (!empty($data['Polis_Num'])) {
			$filterList[] = "PS.Polis_Num = :Polis_Num";
		}
		$query_person = "
				select top 1
					 PS.Person_id
					,PS.Server_id
					,PS.PersonEvn_id
					,2 as resultType
				from
					v_PersonState PS with (nolock)
				where " . implode(' and ', $filterList) . "
			";
		$result_person = $this->db->query($query_person, $queryParams);

		//Теперь проверяем, что из этого получилось
		if (is_object($result_oms) && count($result_oms->result('array')) == 1) { //Если нашли по коду ОМС, то возвращаем с resultType=1
			$response_oms = $result_oms->result('array');
			return $response_oms;
		} else if (is_object($result_person) && count($result_person->result('array')) == 1) { //Если нашли по ФИО и ДР, то возвращаем с resultType=2
			$response_person = $result_person->result('array');
			return $response_person;
		} else {
			return false;
		}
	}

	/**
	 * Проверка на дублирование СНИЛС-а
	 * Возвращает true если дублей не найдено
	 */
	function checkPersonSnilsDoubles($data) {
		$query = "
			select
				count(Person_id) as cnt
			from
				v_PersonState ps with (nolock)
			where
				ps.Person_Snils = :Person_SNILS and
				(:Person_id is null or ps.Person_id <> :Person_id) and
				(Person_closeDT is null or Person_closeDT > dbo.tzGetDate());
		";

		return ($this->getFirstResultFromQuery($query, $data) < 1);
	}

	/**
	 * @param $ednum
	 * @return bool
	 */
	function checkEdNumFedSignature($ednum){
		if (!preg_match('/^\d{16}$/', $ednum)){
			return false;
		}
		
		$key = $ednum[strlen($ednum) - 1];

		$str_chet = '';
		$str_nechet = '';

		for ($i = 14; $i >= 0; $i--){
			if ($i % 2 === 0){
				$str_nechet .= $ednum[$i];
			}else{
				$str_chet .= $ednum[$i];
			}
		}
		
		$str_number = $str_chet . ((int)$str_nechet * 2);
		$summ = 0;

		for ($i = 0; $i < strlen($str_number); $i++){
			$summ += (int)$str_number[$i];
		}
		
		$number_key = $summ % 10 === 0 ? 0 : 10 - $summ % 10;
		if ($number_key === $key){
			return  true;
		}
		
		return false;
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 */
	function exportPersonPolisToXml($data) {
		$params = array(
			'PersonPolis_Date' => $data['PersonPolis_Date'],
			'KLRgn_id' => $data['KLRgn_id']
		);

		$bezrab_sysnick_str = "'child','child_yasli','child_doma','study','nrab','pen','bomzh','chwar'";
		$cross = "";
		$filter = "";
		if ($data['PersonPolis_Date'] == date("Y-m-d")) { // сегодня
			$table  = "v_PersonState p";
			//$filter = "and p.PersonState_insDT <= @PersonPolis_Date";
		} else {
			$table  = "v_Person P1";
			$filter = "and P1.Person_insDT <= @PersonPolis_Date";
			$cross = "
				cross apply (
					select top 1
						t.Person_id,
						t.Person_Snils,
						RTRIM(t.Person_SurName) as Person_SurName,
						RTRIM(t.Person_FirName) as Person_FirName,
						RTRIM(t.Person_SecName) as Person_SecName,
						t.Person_BirthDay,
						t.Sex_id,
						t.Document_id,
						t.SocStatus_id,
						t.Polis_id,
						t.UAddress_id,
						t.Person_deadDT
					from v_Person_all t with(nolock)
					where
						t.PersonEvn_insDT is not null
						and t.PersonEvn_insDT <= @PersonPolis_Date
						and t.Person_id = P1.Person_id
					order by t.PersonEvn_insDT desc
				) P";
		}

		$query = "
			declare @PersonPolis_Date date = :PersonPolis_Date
			declare @KLRgn_id int = :KLRgn_id

			select
				P.Person_id as person_id, -- идентификатор застрахованного
				P.Person_Snils as snils, -- СНИЛС
				P.Person_SurName as fam, -- Фамилия
				P.Person_FirName as im, -- Имя
				P.Person_SecName as ot, -- Отчество
				convert(varchar(10), P.Person_BirthDay, 104) as dr, -- Дата рождения
				(case
					when S.Sex_SysNick like 'woman' then 'Ж'
					when S.Sex_SysNick in ('man','issex') then 'М'
				end) as w, -- Пол
				AB.Address_Address as address_r, -- Адрес места рождения
				AU.Address_Zip as [index], -- Почтовый индекс места регистрации
				AU.Address_Address as address_reg, -- Адрес места регистрации
				(case
					when SC.SocStatus_SysNick in ({$bezrab_sysnick_str}) then 2 else 1
				end) as id_zl, -- Отметка о статусе работающего лица
				SC.SocStatus_SysNick,
				DT.DocumentType_Name as name_doc, -- Наименование документа, удостоверяющего личность
				D.Document_Ser as s_doc, -- Серия документа
				D.Document_Num as n_doc, -- Номер документа
				convert(varchar(10), D.Document_begDate, 104) as data_doc -- Дата выдачи документа
			from
				{$table} with(nolock)
				{$cross}
				inner join v_SocStatus SC with(nolock) on SC.SocStatus_id = P.SocStatus_id
				inner join v_Polis Polis with(nolock) on Polis.Polis_id = P.Polis_id
				inner join v_OMSSprTerr OST with(nolock) on OST.OMSSprTerr_id = Polis.OmsSprTerr_id
				inner join v_Sex S with(nolock) on S.Sex_id = P.Sex_id
				left join v_PersonBirthPlace PBP with(nolock) on PBP.Person_id = P.Person_id
				left join [Address] AB with(nolock) on AB.Address_id = PBP.Address_id
				left join [Address] AU with(nolock) on AU.Address_id = P.UAddress_id
				inner join v_Document D with(nolock) on D.Document_id = P.Document_id
				inner join v_DocumentType DT with(nolock) on DT.DocumentType_id = D.DocumentType_id
			where
				P.Person_deadDT is null
				{$filter}
				and Polis.Polis_begDate <= @PersonPolis_Date
				and (Polis.Polis_endDate is null or Polis.Polis_endDate > @PersonPolis_Date)
				and SC.SocStatus_SysNick in ({$bezrab_sysnick_str})
				and OST.KLRgn_id = @KLRgn_id
		";

		$this->db->query_timeout = 7200; // 2 часа
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result;
	}

	/**
	 * "Воскрешает" человека
	 */
	function revivePerson($data) {
		$params = array('Person_id' => $data['Person_id'], 'pmUser_id' => $data['pmUser_id']);

		$query = "
			select top 1
				count(DeathSvid_id) as Count
			from
				v_DeathSvid DS with (nolock)
			where
				DS.Person_id = :Person_id
				and ISNULL(DS.DeathSvid_IsBad, 1) = 1
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return $this->createError('','Ошибка запроса свидетельства о смерти!');
		}

		$resp_arr = $result->result('array');

		if (!is_array($resp_arr) || count($resp_arr) == 0) {
			return $this->createError('','Ошибка запроса свидетельства о смерти!');
		} else if ($resp_arr[0]['Count'] > 0) {
			return $this->createError('','Удаление признака смерти невозможно, т.к. имеется свидетельство о смерти!');
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_Person_revive
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Message
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}

		return $this->createError('','Ошибка при снятии признака смерти у человека');
	}

	/**
	 * "Убивает" человека
	 */
	function killPerson($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Person_deadDT' => $data['Person_deadDT'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@PersonCloseCause_id bigint;
			set @PersonCloseCause_id = (
				select top 1 PersonCloseCause_id
				from v_PersonCloseCause with(nolock)
				where PersonCloseCause_SysNick like 'death'
			);
			exec p_Person_kill
				@Person_id = :Person_id,
				@PersonCloseCause_id = @PersonCloseCause_id,
				@Person_deadDT = :Person_deadDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Message
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','3 Ошибка при сохранении признака смерти человека');
		}
		return $response;
	}

	/**
	 * 	Перечитать историю
	 */
	function extendPersonHistory($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			exec xp_PersonHistoryExtend @Person_id = :Person_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return array('success' => true);
		}

		return array('success' => false);
	}
	
	/**
	 * Получение диагнозов человека на диспансерном учете
	 */
	function getDiagnosesPersonOnDisp($data) {
		$params = array('Person_id' => $data['Person_id']);
		$filters = "";

		$diagFilters = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilters)) $filters .= " and $diagFilters";
		
		if( !empty($data['actualForToday']) && $data['actualForToday'] ){
			$filters .= " and (PD.PersonDisp_endDate > dbo.tzGetDate() OR PD.PersonDisp_endDate is null)";
		}

		$query = "
			SELECT 
				PD.Diag_id,
				Diag.Diag_Code,
				Diag.Diag_Name
			FROM v_PersonDisp PD
				LEFT JOIN v_PersonState_All PS with (nolock) ON PD.Person_id = PS.Person_id 
				LEFT JOIN v_Diag Diag (nolock) on Diag.Diag_id = PD.Diag_id
			WHERE 
				PS.Person_id = :Person_id 
				{$filters}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение адреса человека
	 */
	function getPersonAddress($data) {
		$params = array('Person_id' => $data['Person_id']);
		$query = "
			select
				URegion.KLRgn_Name as UKLRgn_Name,
				UCity.KLCity_Name as UKLCity_Name,
				UPSTD.PersonSprTerrDop_Name as UPersonSprTerrDop_Name,
				UStreet.KLStreet_Name as UKLStreet_Name,
				UAddr.Address_House as UAddress_House,
				UAddr.Address_Corpus as UAddress_Corpus,
				UAddr.Address_Flat as UAddress_Flat
			from v_PersonState PS with(nolock)
				left join v_Address UAddr with(nolock) on UAddr.Address_id = PS.UAddress_id
				left join v_KLRgn URegion with(nolock) on URegion.KLRgn_id = UAddr.KLRgn_id
				left join v_KLCity UCity with(nolock) on UCity.KLCity_id = UAddr.KLCity_id
				left join v_KLStreet UStreet with(nolock) on UStreet.KLStreet_id = UAddr.KLStreet_id
				left join v_PersonInfo PInfo with(nolock)  on PInfo.Person_id = PS.Person_id
				left join v_PersonSprTerrDop UPSTD with(nolock)  on UPSTD.PersonSprTerrDop_Code = PInfo.UPersonSprTerrDop_id
			where
				PS.Person_id = :Person_id
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Возвращает номер региона территории страхования
	 */
	function getPersonPolisRegionId($data) {
		$params = array(
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
		);

		$query = "
			select top 1 Terr.KLRgn_id
			from v_Person_all PS with(nolock)
			left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
			left join OMSSprTerr Terr with(nolock) on Terr.OMSSprTerr_id = P.OmsSprTerr_id
			where PS.PersonEvn_id = :PersonEvn_id and PS.Server_id = :Server_id
		";

		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Проверка существования кода анонимного пациента, генерация нового кода
	 * @param $data
	 * @return array
	 */
	function checkAnonimCodeUnique($data){
		$select = '';
		if($data['Person_id']){// если передан Person_id из режима правки
			$select .= 'AND PS.Person_id != '.$data['Person_id'];// не искать самого себя
		}

		$query = "
			SELECT TOP 1 PS.Person_SurName
				FROM v_Person P WITH(nolock)
				INNER JOIN v_PersonState PS WITH(nolock) ON PS.Person_id = P.Person_id
				WHERE
					P.Person_IsAnonym = 2
					--AND ISNUMERIC(PS.Person_Surname + 'e0') = 1
					AND PS.Person_SurName = :CheckName
					{$select}
		";
		$result = $this->db->query($query, array('CheckName' => $data['Person_SurName']));

		if(! is_object($result)){
			return $this->createError('', 'Ошибка поиска федерального реестрого кода МО');
		}

		if($result->row()){// код уже существует
			$response = $this->getPersonAnonymCodeExt($data);
			$response[0]['success'] = false;
			$response[0]['Error_Msg'] = 'Обнаружен дубль по коду, сгенерирован новый код, повторите сохранение';
			return $response;
		}

		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Получение кода анонимного пациента в формате ККККГГННННН #140580
	 * с контролем переполнения
	 * @param $data
	 * @return array
	 */
	function getPersonAnonymCodeExt($data){
		$qmcod = $this->db->query("
			SELECT TOP 1
				Lpu_f003mcod
				,RIGHT(CONVERT(varchar(4), dbo.tzGetDate(), 112), 2) AS curYear
			FROM v_Lpu WITH(nolock)
			WHERE Lpu_id = :Lpu_id
		", array('Lpu_id' => $data['Lpu_id']));

		if(is_object($qmcod)){
			$response = $qmcod->result('array');
			if(isset($response[0])){
				$f003mcod = $response[0]['Lpu_f003mcod'];
				$curYear = $response[0]['curYear'];// последние 2 цифры текущего года
			}else{
				return $this->createError('', 'Не найден федеральный реестровый код МО');
			}
		}else{
			return $this->createError('', 'Ошибка поиска федерального реестрого кода МО');
		}
		unset($response, $qmcod);

		$f003mcod = substr(str_pad($f003mcod, 4, '0', STR_PAD_LEFT), -4);

		$query = "
			SELECT PS.Person_SurName
				FROM v_Person P WITH(nolock)
				INNER JOIN v_PersonState PS WITH(nolock) ON PS.Person_id = P.Person_id
				WHERE
					P.Person_IsAnonym = 2
					--AND ISNUMERIC(PS.Person_Surname + 'e0') = 1
					AND LEFT(PS.Person_SurName, 6) = :Code
		";
		$result = $this->db->query($query, array('Code' => $f003mcod.$curYear));
		if($result === false) {
			return $this->createError('', 'Ошибка получения порядкого номера анонимного пациента в МО');
		}

		$codeArr = array();
		$maxCode = 0;// новый код
		//$result->free_result();// ни одной записи в БД

		foreach($result->result() as $row){
			$start = mb_strlen($row->Person_SurName) - 5;
			if($start != 6 /*&& $start != 4*/) continue;// длина ключа не равна 11 или 9 - не правильный код анонимного пациента
			$code = (int)substr($row->Person_SurName, $start);
			if($maxCode < $code){
				$maxCode = $code;
			}
			$codeArr[$code] = true;
		}
		$result->free_result();
		unset($result);

		//$maxCode = 99999;// максимальное число максимально
		$maxCode++;

		$missed = 0;
		if($maxCode > 99999){
			for($i = 1; $i <= 99999; $i++){
				if(! array_key_exists($i, $codeArr)){
					$missed = $i;
					break;
				}
			}
			if(! $missed){
				return $this->createError('', 'Все номера от 1 до 99999 на текущий год заняты');// это провал, остался только номер 0
			}
			$maxCode = $missed;
		}
		unset($codeArr);

		return array(array(
			'success' => true,
			'Person_AnonymCode' => $f003mcod.$curYear.str_pad(''.$maxCode, 5, '0', STR_PAD_LEFT),
			'Person_IsAnonym' => true,
			'Error_Msg' => ''));
	}

	/**
	 * Получение кода анонимного пациента
	 */
	function getPersonAnonymCode($data) {
		$Lpu_f003mcod = $this->getFirstResultFromQuery("
			select top 1 Lpu_f003mcod
			from v_Lpu with(nolock)
			where Lpu_id = :Lpu_id
		", array('Lpu_id' => $data['Lpu_id']));
		if ($Lpu_f003mcod === false) {
			return $this->createError('', 'Ошибка поиска федерального реестрого кода МО');
		}
		if (empty($Lpu_f003mcod)) {
			return $this->createError('', 'Не найден федеральный реестровый код МО');
		}

		$params = array(
			'Lpu_f003mcod' => $Lpu_f003mcod,
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null
		);
		$query = "
			declare @number int = 0
			if :Person_id is not null
			set @number = isnull((
				select top 1 cast(right(PS.Person_SurName,5) as int)
				from v_Person P with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
				where P.Person_id = :Person_id and P.Person_IsAnonym = 2 and ISNUMERIC(PS.Person_Surname + 'e0') = 1
			),0)
			if @number = 0
			set @number = isnull((
				select top 1 cast(max(right(PS.Person_SurName,5)) as int)+1
				from v_Person P with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
				where PS.Person_SurName like right('0000'+:Lpu_f003mcod,4)+replicate('[0-9]',5) and ISNUMERIC(PS.Person_Surname + 'e0') = 1
				--PS.Person_SurName LIKE RIGHT('0000' + :Lpu_f003mcod, 4) + RIGHT(CONVERT(varchar(4), dbo.tzGetDate(), 112), 2) + REPLICATE('[0-9]', 5) AND ISNUMERIC(PS.Person_Surname + 'e0') = 1
				and P.Person_IsAnonym = 2
			),1)
			select right(:Lpu_f003mcod,4)+right('00000'+cast(@number as varchar),5) as Person_AnonymCode
				--RIGHT(:Lpu_f003mcod, 4) + RIGHT(CONVERT(varchar(4), dbo.tzGetDate(), 112), 2) + RIGHT('00000' + CAST(@number AS varchar), 5) AS Person_AnonymCode
		";
		$Person_AnonymCode = $this->getFirstResultFromQuery($query, $params);
		if ($Person_AnonymCode === false) {
			return $this->createError('', 'Ошибка получения порядкого номера анонимного пациента в МО');
		}

		return array(array('success' => true, 'Person_AnonymCode' => $Person_AnonymCode, 'Error_Msg' => ''));
	}

	/**
	 * Получение данных анонимного пациента
	 */
	function getPersonAnonymData($data) {
		$params = array('Person_id' => $data['Person_id']);
		$query = "
			select top 1
				P.Person_SurName,
				P.Person_FirName,
				P.Person_SecName
			from 
				v_Person_all P
			where 
				P.Person_id = :Person_id
				and ISNUMERIC(P.Person_SurName + 'e0') = 0
			order by
				P.PersonEvn_insDT desc
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('', 'Ошибка при получении данных анонимного пациента');
		}
		return array(array('success' => true, 'PersonAnonymData' => $resp, 'Error_Msg' => ''));
	}

	/**
	 * Получение истории идентификации человека в ЦС ЕРЗ
	 */
	function loadPersonRequestDataGrid($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select
				-- select
				PRD.PersonRequestData_id,
				PRD.Person_id,
				PRST.PersonRequestSourceType_id,
				PRST.PersonRequestSourceType_Name,
				convert(varchar(10), PRD.PersonRequestData_insDT, 104)+' '+convert(varchar(5), PRD.PersonRequestData_insDT, 108) as PersonRequestData_insDT,
				convert(varchar(10), PRD.PersonRequestData_csDT, 104)+' '+convert(varchar(5), PRD.PersonRequestData_csDT, 108) as PersonRequestData_csDT,
				--convert(varchar(10), PRD.Evn_disDT, 104)+' '+convert(varchar(5), PRD.Evn_disDT, 108) as Evn_disDT,
				coalesce(EPL.EvnPL_id, EPS.EvnPS_id, CCC.CmpCallCard_id) as Evn_id,
				case 
					when EPL.EvnPL_id is not null then 'EvnPL'
					when EPS.EvnPS_id is not null then 'EvnPS'
					when CCC.CmpCallCard_id is not null then 'CmpCallCard'
				end EvnClass,
				case 
					when EPL.EvnPL_id is not null then 'ТАП / №'+cast(EPL.EvnPL_NumCard as varchar)
					when EPS.EvnPS_id is not null then 'КВС / №'+cast(EPS.EvnPS_NumCard as varchar)
					when CCC.CmpCallCard_id is not null then 'СМП / №'+cast(CCC.CmpCallCard_Ngod as varchar)
				end as Evn_Name,
				PRDS.PersonRequestDataStatus_id,
				PRDS.PersonRequestDataStatus_Name,
				PNIC.PersonNoIdentCause_id,
				PNIC.PersonNoIdentCause_Name,
				case when PNIC.PersonNoIdentCause_Name is not null
					then PNIC.PersonNoIdentCause_Name else PRD.PersonRequestData_Error
				end as NoIdentCause
				-- end select
			from
				-- from
				erz.v_PersonRequestData PRD with(nolock)
				left join erz.v_PersonRequestSourceType PRST with(nolock) on PRST.PersonRequestSourceType_id = PRD.PersonRequestSourceType_id
				left join erz.v_PersonRequestDataStatus PRDS with(nolock) on PRDS.PersonRequestDataStatus_id = PRD.PersonRequestDataStatus_id
				left join erz.v_PersonNoIdentCause PNIC with(nolock) on PNIC.PersonNoIdentCause_id = PRD.PersonNoIdentCause_id
				left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = PRD.Evn_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_EvnSection ES with(nolock) on ES.EvnSection_id = PRD.Evn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_CmpCallCard CCC with(nolock) on CCC.CmpCallCard_id = PRD.Evn_id
				-- end from
			where
				-- where
				PRD.Person_id = :Person_id
				-- end where
			order by
				-- order by
				PRD.PersonRequestData_insDT desc,
				PRD.PersonRequestData_csDT desc
				-- end order by
		";

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
	 * Добавление данных человека на идентификацию в ЦС ЕРЗ
	 */
	function addPersonRequestData($data) {
		$fromClient = (isset($data['fromClient']) && $data['fromClient']);
		$identImmediately = (isset($data['identImmediately']) && $data['identImmediately']);
		$params = array(
			'Person_id' => $data['Person_id'],
			'PersonRequestSourceType_id' => $data['PersonRequestSourceType_id'],
			'Person_identDT' => !empty($data['Person_identDT'])?$data['Person_identDT']:null,
		);

		if (empty($params['Person_identDT'])) {
			$params['Person_identDT'] = $this->getFirstResultFromQuery("select convert(varchar, dbo.tzGetDate(), 121)");
			if ($params['Person_identDT'] === false) {
				return $this->createError('', 'Ошибка при получении текущей даты');
			}
		}

		if ( $params['Person_identDT'] instanceof DateTime ) {
			$params['Person_identDT'] = $params['Person_identDT']->format('Y-m-d H:i:s');
		}

		//Получение идентификатора предыдущего запроса на идентификацию
		$query = "
			declare @dt datetime = :Person_identDT
			select top 1 PRD.PersonRequestData_id
			from erz.v_PersonRequestData PRD with(nolock)
			where PRD.Person_id = :Person_id and cast(PRD.Evn_disDT as date) = cast(@dt as date)
			and PRD.PersonRequestDataStatus_id <> 7
			order by PRD.PersonRequestData_insDT desc
		";
		$PersonRequestData_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($PersonRequestData_id === false) {
			return $this->createError('','Ошибка при поиске человека в пакетах на идентификацию');
		}
		if ($fromClient && !empty($PersonRequestData_id)) {
			return $this->createError(302,'Уже существует запись в пакете на идентификацию человека');
		}

		$query = "
			select top 1
				Person_IsInErz,
				PersonIdentState_id,
				convert(varchar, Person_identDT, 121) as Person_identDT
			from v_Person with(nolock)
			where Person_id = :Person_id
		";
		$lastIdent = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($lastIdent)) {
			return $this->createError('','Ошибка при получении статуса последней идентификации');
		}

		$fields = array(
			'Person_Surname' => 'Фамилия',
			'Person_Firname' => 'Имя',
			'Person_Secname' => 'Отчество',
			'Person_Sex' => 'Пол',
			'Person_Birthday' => 'Дата рождения',
			'Person_ENP' => 'Единый номер',
			'Person_Snils' => 'СНИЛС',
			'DocumType_Code' => 'Тип документа',
			'Docum_Ser' => 'Серия документа',
			'Docum_Num' => 'Номер документа',
			'PolisType_id' => 'Тип полиса',
			'Polis_Ser' => 'Серия',
			'Polis_Num' => 'Номер',
		);

		$person = array();

		if ($fromClient) {
			foreach($fields as $nick => $name) {
				$person[$nick] = !empty($data[$nick])?$data[$nick]:null;
			}
		} else {
			$query = "
				select top 1
					PersonAll.Person_id,
					PersonAll.Person_SurName as Person_Surname,
					PersonAll.Person_FirName as Person_Firname,
					PersonAll.Person_SecName as Person_Secname,
					PersonAll.Sex_id as Person_Sex,
					convert(varchar(10), PersonAll.Person_Birthday, 120) as Person_Birthday,
					PersonAll.Person_EdNum as Person_ENP,
					PersonAll.Person_Snils,
					DocumentType.DocumentType_Code as DocumType_Code,
					Document.Document_Ser as Docum_Ser,
					Document.Document_Num as Docum_Num,
					Polis.PolisType_id,
					Polis.Polis_Ser,
					Polis.Polis_Num
				from
					v_Person_bdz PersonAll with(nolock)
					left join v_Polis Polis with(nolock) on Polis.Polis_id =PersonAll.Polis_id
					left join dbo.v_Document Document with(nolock) on Document.Document_id = PersonAll.Document_id
					left join dbo.v_DocumentType DocumentType with(nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				where
					PersonAll.Person_id = :Person_id
					and PersonAll.PersonEvn_insDT <= :Person_identDT
				order by
					PersonAll.PersonEvn_insDT desc
			";
			$person = $this->getFirstRowFromQuery($query, $params);
			if (!is_array($person)) {
				return $this->createError('','Ошибка при получении данных человека для идентификации');
			}
		}

		$requireFieldError = null;

		if ($fromClient) {
			$requiresList = array();

			if (in_array(getRegionNick(), ['msk', 'vologda'])) {
				$requiredEveryFields = array('Person_Surname','Person_Firname','Person_Birthday');
			} else {
				$requiredEveryFields = array('Person_Surname','Person_Firname','Person_Birthday','Person_Sex');
			}

			$requireField = null;
			foreach($requiredEveryFields as $field) {
				if (empty($person[$field])) {
					$requiresList[] = $fields[$field];
					break;
				}
			}

			if (getRegionNick() == 'vologda' &&
				empty($person['Docum_Num']) && empty($person['Person_Snils']) &&
				empty($person['Polis_Num']) && empty($person['Person_ENP'])
			) {
				$requiresList[] = 'ДУДЛ или СНИЛС или ДПФС или ЕНП';
			}

			if (getRegionNick() == 'msk' &&
				empty($person['Docum_Num']) && empty($person['Person_Snils'])
			) {
				$requiresList[] = 'ДУДЛ или СНИЛС';
			}

			if (count($requiresList) > 0) {
				$requiresListStr = implode(' и ', $requiresList);
				$requireFieldError = "Идентификация не может быть проведена, т.к. не заполнены обязательные поля. Необходимо заполнить \"{$requiresListStr}\"";
			}
		}

		$this->beginTransaction();

		if (!empty($PersonRequestData_id)) {
			//Проставление причины отказа от идентификации "В процессе идентификации были изменены данные человека" для предыдущего запроса
			$resp = $this->setPersonRequestDataStatus(array(
				'PersonRequestData_id' => $PersonRequestData_id,
				//'PersonRequestDataStatus_id' => 7,
				'PersonNoIdentCause_id' => 3,
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Добавление записи для идентификации службой
		$query = "
			declare
				@PersonRequestData_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@GUID uniqueidentifier;
			set @GUID = newid();
			exec erz.p_PersonRequestData_ins_old
				@PersonRequestData_id = @PersonRequestData_id output,
				@PersonRequestDataStatus_id = :PersonRequestDataStatus_id,
				@PersonRequestData_ReqGUID = @GUID,
				@Evn_id = null,
				@Evn_disDT = :Evn_disDT,			--Идентификация на дату
				@Person_id = :Person_id,
				@Person_Surname = :Person_Surname,
				@Person_Firname = :Person_Firname,
				@Person_Secname = :Person_Secname,
				@Person_Sex = :Person_Sex,
				@Person_Birthday = :Person_Birthday,
				@Person_ENP = :Person_ENP,
				@Person_Snils = :Person_Snils,
				@DocumType_Code = :DocumType_Code,
				@Docum_Ser = :Docum_Ser,
				@Docum_Num = :Docum_Num,
				@PolisType_id = :PolisType_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@PersonRequestData_csDT = null,
				@PersonRequestData_flcDT = :PersonRequestData_flcDT,
				@PersonRequestData_Error = :PersonRequestData_Error,
				@PersonRequestSourceType_id = :PersonRequestSourceType_id,
				@PersonRequest_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonRequestData_id as PersonRequestData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'PersonRequestDataStatus_id' => 1,	//Новая
			'Evn_disDT' => $params['Person_identDT'],
			'Person_id' => $params['Person_id'],
			'PersonRequestSourceType_id' => $params['PersonRequestSourceType_id'],
			'Person_Surname' => $person['Person_Surname'],
			'Person_Firname' => $person['Person_Firname'],
			'Person_Secname' => $person['Person_Secname'],
			'Person_Sex' => $person['Person_Sex'],
			'Person_Birthday' => $person['Person_Birthday'],
			'Person_ENP' => $person['Person_ENP'],
			'Person_Snils' => $person['Person_Snils'],
			'DocumType_Code' => $person['DocumType_Code'],
			'Docum_Ser' => $person['Docum_Ser'],
			'Docum_Num' => $person['Docum_Num'],
			'PolisType_id' => $person['PolisType_id'],
			'Polis_Ser' => $person['Polis_Ser'],
			'Polis_Num' => $person['Polis_Num'],
			'PersonRequestData_flcDT' => null,
			'PersonRequestData_Error' => null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (!empty($requireFieldError)) {
			$queryParams['PersonRequestDataStatus_id'] = 7;		//Ошибка
			$queryParams['PersonRequestData_flcDT'] = $this->currentDT->format('Y-m-d H:i:s');
			$queryParams['PersonRequestData_Error'] = $requireFieldError;
		}
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('Ошибка при добавлении человека на идентификацию');
		}
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$PersonRequestData_id = $resp[0]['PersonRequestData_id'];

		if (empty($lastIdent['Person_IsInErz']) || date_create($lastIdent['Person_identDT']) <= date_create($params['Person_identDT'])) {
			if (!empty($requireFieldError)) {
				$lastIdent['PersonIdentState_id'] = 5;
				$lastIdent['Person_identDT'] = $params['Person_identDT'];
			} else {
				$lastIdent['Person_IsInErz'] = null;
				$lastIdent['PersonIdentState_id'] = 4;
				$lastIdent['Person_identDT'] = $params['Person_identDT'];
			}

			$resp = $this->updatePerson(array(
				'Person_id' => $params['Person_id'],
				'Person_IsInErz' => $lastIdent['Person_IsInErz'],
				'PersonIdentState_id' => $lastIdent['PersonIdentState_id'],
				'Person_identDT' => $lastIdent['Person_identDT'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при изменении статуса идентификации человека');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		if (!empty($requireFieldError)) {
			$response = array_merge($lastIdent, array('success' => false, 'Error_Msg' => $requireFieldError));
		} else {
			$response = array_merge($lastIdent, array('success' => true));
		}

		$response = array($response);
		if (!$this->isSuccessful($response)) {
			return $response;
		}

		if ($identImmediately) {
			$this->load->model('PersonIdentRequest_model');
			$response = $this->PersonIdentRequest_model->identPerson(array(
				'PersonRequestData_id' => $PersonRequestData_id,
				'pmUser_id' => $data['pmUser_id'],
			));
		}

		return $response;
	}

	/**
	 * Обновление статусов запроса на идентификацию в ЦС ЕРЗ
	 */
	function setPersonRequestDataStatus($data) {
		$params = array(
			'PersonRequestData_id' => $data['PersonRequestData_id'],
		);
		$set_status = array();

		if (!empty($data['PersonRequestDataStatus_id'])) {
			$set_status[] = "PersonRequestDataStatus_id = :PersonRequestDataStatus_id";
			$params['PersonRequestDataStatus_id'] = $data['PersonRequestDataStatus_id'];
		}
		if (!empty($data['PersonNoIdentCause_id'])) {
			$set_status[] = "PersonNoIdentCause_id = :PersonNoIdentCause_id";
			$params['PersonNoIdentCause_id'] = $data['PersonNoIdentCause_id'];
		}
		if (count($set_status) == 0) {
			return $this->createError('','Не переданы статусы запроса на идентификацию для обновления');
		}
		$set_status_str = implode(", ", $set_status);

		$query = "
			set nocount on;
			declare
				@Err_Msg varchar(4000), 
				@Err_Code int;
			begin try
				update erz.PersonRequestData with(rowlock)
				set {$set_status_str}
				where PersonRequestData_id = :PersonRequestData_id
			end try
			begin catch
				set @Err_Code = error_number();
				set @Err_Msg = error_message();
			end catch;
			set nocount off;
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении причины отказа от идентификации');
		}
		return $resp;
	}

	/**
	 * Сохранение согласия/отзыва согласия на обработку перс.данных
	 */
	function savePersonLpuInfo($data) {

		$query = "
			declare
				@PersonLpuInfo_id bigint,
				@curdate datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @curdate = dbo.tzGetDate();
			exec dbo.p_PersonLpuInfo_ins
				@PersonLpuInfo_id = @PersonLpuInfo_id output,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@PersonLpuInfo_IsAgree = :PersonLpuInfo_IsAgree,
				@PersonLpuInfo_setDT = @curdate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonLpuInfo_id as PersonLpuInfo_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('Ошибка при согласия/отзыва согласия на обработку перс.данных');
		}
		return $resp;
	}

	/**
	 * Сохранение согласия/отзыва согласия на обработку перс.данных
	 */
	function saveElectroReceptInfo($data) {
		 if (!empty($data['Refuse']) && empty($data['ReceptElectronic_id'])){
		 	return false;
		 }

		if (!empty($data['Refuse'])) {
		$query = "
			declare 
				@ReceptElectronic_id bigint = :ReceptElectronic_id,
				@curdate datetime;
			set nocount on;
			set @curdate = dbo.tzGetDate();
			update
				ReceptElectronic with (rowlock) 
			set
				ReceptElectronic_endDT = @curdate 
			where 
				Person_id = :Person_id and 
				ReceptElectronic_id = @ReceptElectronic_id;		
			set nocount off;	
			select @ReceptElectronic_id as ReceptElectronic_id, null as Error_Code, null as Error_Msg;
			";
		} else {
		$query = "
			declare
				@ReceptElectronic_id bigint,
				@curdate datetime,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @curdate = dbo.tzGetDate();
			exec dbo.p_ReceptElectronic_ins
				@ReceptElectronic_id = @ReceptElectronic_id output,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@ReceptElectronic_begDT = @curdate,                  
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ReceptElectronic_id as ReceptElectronic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		}

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('Ошибка при сохранении согласия/отзыва на оформление рецепта в форме электронного документа');
		}
		return $resp;
	}

	/**
	 * Получение данных по согласию на обработку перс.данных для ЭМК
	 */
	function getPersonLpuInfoPersData($data) {

		$query = "
			select top 1 
				pi.Person_id,
				isnull(convert(varchar, pi.PersonLpuInfo_setDT, 104),'') as PersonLpuInfo_setDT,
				case when isnull(pi.PersonLpuInfo_IsAgree,2) = 1 then 'отзыва согласия' else 'согласия' end as caption
			from v_PersonLpuInfo pi with (nolock)
			where pi.Person_id = :Person_id and pi.Lpu_id = :Lpu_id
			order by pi.PersonLpuInfo_setDT desc
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('Ошибка при получении данных согласия/отзыва согласия на обработку перс.данных');
		}
		if(count($resp) > 0) {
			return $resp;
		} else {
			return array(array('Person_id'=>$data['Person_id'],'PersonLpuInfo_setDT'=>'','caption'=>'согласия'));
		}
		
	}

	/**
	 * Получение истории операций по согласию/отзыву согласия на обработку перс.данных
	 */
	function loadPersonLpuInfoList($data) {

		$query = "
			select 
				pi.PersonLpuInfo_id,
				isnull(convert(varchar, pi.PersonLpuInfo_setDT, 104),'') as PersonLpuInfo_setDT,
				case when isnull(pi.PersonLpuInfo_IsAgree,2) = 1 then 'Отзыв согласия' else 'Согласие' end as Doc_type,
				pu.PMUser_Name
			from v_PersonLpuInfo pi with (nolock)
			left join v_pmUserCache pu with (nolock) on pu.PMUser_id = pi.pmUser_insID
			where pi.Person_id = :Person_id and pi.Lpu_id = :Lpu_id
			order by pi.PersonLpuInfo_setDT desc
		";

		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return $this->createError('Ошибка при получении данных согласия/отзыва согласия на обработку перс.данных');
		}
		return $resp;
	}

	/**
	 * Получение списка свидетельств для ЭМК
	 */
	function getPersonSvidInfo($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
			select top 1
				BS.Person_id,
				BS.Server_id,
				BS.BirthSvid_id as PersonSvid_id,
				BS.BirthSvid_id as PersonSvidInfo_id,
				BS.BirthSvid_IsSigned as PersonSvid_IsSigned,
				'BirthSvid' as PersonSvid_Object,
				'birth' as PersonSvidType_Code,
				'Свидетельство о рождении' as PersonSvidType_Name,
				ISNULL(BS.BirthSvid_Ser,'') as PersonSvid_Ser,
				ISNULL(BS.BirthSvid_Num,'') as PersonSvid_Num,
				convert(varchar, BS.BirthSvid_GiveDate, 104) as PersonSvid_GiveDate
			from v_BirthSvid BS (nolock)
			where
				BS.Person_id = :Person_id
				and ISNULL(BS.BirthSvid_IsBad,1) = '1'
			union
			select top 1
				PDS.Person_id,
				PDS.Server_id,
				PDS.PntDeathSvid_id as PersonSvid_id,
				PDS.PntDeathSvid_id as PersonSvidInfo_id,
				null as PersonSvid_IsSigned,
				'PntDeathSvid' as PersonSvid_Object,
				'pntdeath' as PersonSvidType_Code,
				'Свидетельство о перинатальной смерти' as PersonSvidType_Name,
				ISNULL(PDS.PntDeathSvid_Ser,'') as PersonSvid_Ser,
				ISNULL(PDS.PntDeathSvid_Num,'') as PersonSvid_Num,
				convert(varchar, PDS.PntDeathSvid_GiveDate, 104) as PersonSvid_GiveDate
			from v_PntDeathSvid PDS (nolock)
			where
				PDS.Person_id = :Person_id
				and ISNULL(PDS.PntDeathSvid_IsBad,1) = '1'
			union
			select top 1
				DS.Person_id,
				DS.Server_id,
				DS.DeathSvid_id as PersonSvid_id,
				DS.DeathSvid_id as PersonSvidInfo_id,
				null as PersonSvid_IsSigned,
				'DeathSvid' as PersonSvid_Object,
				'death' as PersonSvidType_Code,
				'Свидетельство о смерти' as PersonSvidType_Name,
				ISNULL(DS.DeathSvid_Ser,'') as PersonSvid_Ser,
				ISNULL(DS.DeathSvid_Num,'') as PersonSvid_Num,
				convert(varchar, DS.DeathSvid_GiveDate, 104) as PersonSvid_GiveDate
			from v_DeathSvid DS (nolock)
			where
				DS.Person_id = :Person_id
				and ISNULL(DS.DeathSvid_IsBad,1) = '1'

			order by 5
		";
		$result = $this->db->query($query,$params);

		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		else
			return false;
	}

	/**
	 * Получение списка людей для API
	 */
	function loadPersonListForAPI($data) {
		$offset = !empty($data['offset'])?$data['offset']:0;
		$params = array();
		$filters = array('1=1');

		if (!empty($data['Person_id'])) {
			$filters[] = "PS.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['PersonSurName_SurName'])) {
			$filters[] = "PS.Person_SurName = :Person_SurName";
			$params['Person_SurName'] = $data['PersonSurName_SurName'];
		}
		if (!empty($data['PersonFirName_FirName'])) {
			$filters[] = "PS.Person_FirName = :Person_FirName";
			$params['Person_FirName'] = $data['PersonFirName_FirName'];
		}
		if (!empty($data['PersonSecName_SecName'])) {
			$filters[] = "PS.Person_SecName = :Person_SecName";
			$params['Person_SecName'] = $data['PersonSecName_SecName'];
		}
		if (!empty($data['PersonBirthDay_BirthDay'])) {
			$filters[] = "PS.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['PersonBirthDay_BirthDay'];
		}
		if (!empty($data['PersonSnils_Snils'])) {
			$filters[] = "PS.Person_Snils = :Person_Snils";
			$params['Person_Snils'] = $data['PersonSnils_Snils'];
		}
		if (!empty($data['Polis_Ser'])) {
			$filters[] = "PS.Polis_Ser = :Polis_Ser";
			$params['Polis_Ser'] = $data['Polis_Ser'];
		}
		if (!empty($data['Polis_Num'])) {
			$filters[] = "(PS.Polis_Num = :Polis_Num or PS.Person_EdNum = :Polis_Num)";
			$filters[] = 'isnull(PS.Polis_Num, PS.Person_EdNum) = :Polis_Num'; // Person_EdNum проверяем если только Polis_Num пустой, но сам по себе такой фильтр работает медленно, поэтому дублируем через or
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		if (!empty($data['Person_pid'])) {
			$filters[] = "PD.Person_pid = :Person_pid";
			$params['Person_pid'] = $data['Person_pid'];
		}
		if (!empty($data['DeputyKind_id'])) {
			$filters[] = "PD.DeputyKind_id = :DeputyKind_id";
			$params['DeputyKind_id'] = $data['DeputyKind_id'];
		}
		if (!empty($data['Person_isUnknown'])) {
			$filters[] = "P.Person_isUnknown = :Person_isUnknown";
			$params['Person_isUnknown'] = $data['Person_isUnknown'];
		}
		if (!empty($data['Person_iin'])) {
			$filters[] = 'PS.Person_Inn = :Person_Inn';
			$params['Person_Inn'] = $data['Person_iin'];
		}
		
		if(count($params) == 0 ){
			return array(
				'error_code' => 6,
				'error_msg' => 'Не передан ни один из параметров поиска'
			);
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				-- select
				PS.Person_id,
				PS.Person_SurName as PersonSurName_SurName,
				PS.Person_FirName as PersonFirName_FirName,
				PS.Person_SecName as PersonSecName_SecName,
				convert(varchar(10), PS.Person_BirthDay, 120) as PersonBirthDay_BirthDay,
				PS.Sex_id as Person_Sex_id,
				PS.Person_Phone as PersonPhone_Phone,
				PS.Person_Snils as PersonSnils_Snils,
				PS.Person_Inn as PersonInn_Inn,
				PS.SocStatus_id,
				PS.Polis_id,
				PS.PolisType_id,
				PS.Polis_Ser,
				PS.Polis_Num,
				PS.UAddress_id,
				PS.PAddress_id,
				PBP.Address_id as BAddress_id,
				J.Org_id,
				J.Post_id,
				P.BDZ_guid,
				P.BDZ_id,
				PD.Person_pid,
				--PD.DeputyKind_id,
				ISNULL(PD.DeputyKind_id, 0) as DeputyKind_id,
				P.Person_isUnknown
				-- end select
			from
				--from
				v_PersonState PS with (nolock)
				left join v_Person P with (nolock) on P.Person_id = PS.Person_id
				left join v_PersonBirthPlace PBP with (nolock) on PBP.Person_id = PS.Person_id
				left join v_Job J with (nolock) on J.Job_id = PS.Job_id
				left join v_PersonDeputy PD with(nolock) on PD.Person_id = PS.Person_id
				--end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PS.Person_id
				-- end order by
		";
		//echo getDebugSQL($query,$params);die();
		$result = $this->queryResult(getLimitSQLPH($query, $offset, 100), $params);
		$result_count = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($result) || !is_array($result_count)) {
			return false;
		}

		return array('totalCount' => $result_count[0]['cnt'], 'data' => $result);
	}

	/**
	 * Получение данных последнего полиса человека. Метод для API
	 */
	function getLastPolisForAPI($data) {
		$params = array();

		$where = '1=1';
		if(!empty($data['Person_id'])) {
			$where .= ' and PS.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		if(!empty($data['Polis_id'])) {
			$where .= ' and P.Polis_id = :Polis_id';
			$params['Polis_id'] = $data['Polis_id'];
		}
		if(!empty($data['Polis_Ser'])) {
			$where .= ' and P.Polis_Ser = :Polis_Ser';
			$params['Polis_Ser'] = $data['Polis_Ser'];
		}
		if(!empty($data['Polis_Num'])) {
			$where .= ' and (PS.Polis_Num = :Polis_Num or PS.Person_EdNum = :Polis_Num)';
			$where .= ' and isnull(PS.Polis_Num, PS.Person_EdNum) = :Polis_Num'; // Person_EdNum проверяем если только Polis_Num пустой, но сам по себе такой фильтр работает медленно, поэтому дублируем через or
			$params['Polis_Num'] = $data['Polis_Num'];
		}

		if(empty($params)){
			return array(
				"error_code" => 6,
				"error_msg" => "Не передан ни один из параметров поиска"
			);
		}

		$query = "
			select top 1
				PS.Person_id,
				P.Polis_id,
				P.OmsSprTerr_id,
				P.PolisType_id,
				P.Polis_Ser,
				P.Polis_Num,
				P.OrgSmo_id,
				convert(varchar(10), P.Polis_begDate, 120) as Polis_begDate,
				convert(varchar(10), P.Polis_endDate, 120) as Polis_endDate,
				P.PolisFormType_id
			from
				v_PersonState PS with(nolock)
				left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
			where 
				{$where}
			order by P.Polis_begDate desc
		";
		$resp = $this->queryResult($query, $params);

		return (array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение данных полиса. Метод для API
	 */
	function getPolisForAPI($data) {
		$params = array('Polis_id' => $data['Polis_id']);
		$query = "
			select top 1
				PP.PersonPolis_id,
				PP.Server_id,
				PP.Polis_id,
				PP.Person_id,
				PP.OMSSprTerr_id,
				PP.PolisType_id,
				PP.Polis_Ser,
				PP.Polis_Num,
				PP.OrgSmo_id,
				convert(varchar(10), PP.Polis_begDate, 120) as Polis_begDate,
				convert(varchar(10), PP.Polis_endDate, 120) as Polis_endDate,
				PP.PolisFormType_id
			from
				v_PersonPolis PP with(nolock)
			where 
				PP.Polis_id = :Polis_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных последнего документа человека. Метод для API
	 */
	function getLastDocumentForAPI($data) {
		$params = array('Person_id' => $data['Person_id']);
		$query = "
			select
				PD.Person_id,
				PD.Document_id,
				PD.DocumentType_id,
				PD.Document_Ser,
				PD.Document_Num,
				PD.OrgDep_id,
				convert(varchar(10), PD.Document_begDate, 120) as Document_begDate,
				NS.KLCountry_id
			from
				v_PersonState PS with(nolock)
				inner join v_PersonDocument PD with(nolock) on PD.Document_id = PS.Document_id
				left join v_NationalityStatus NS with(nolock) on NS.NationalityStatus_id = PS.NationalityStatus_id
			where
				PS.Person_id = :Person_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных документа. Метод для API
	 */
	function getDocumentForAPI($data) {
		$params = array('Document_id' => $data['Document_id']);
		$query = "
			select
				PD.Person_id,
				PD.Server_id,
				PD.PersonDocument_id,
				PD.Document_id,
				PD.DocumentType_id,
				PD.Document_Ser,
				PD.Document_Num,
				PD.OrgDep_id,
				convert(varchar(10), PD.Document_begDate, 120) as Document_begDate,
				NS.KLCountry_id
			from
				v_PersonDocument PD
				inner join v_Person_all P with(nolock) on P.PersonEvn_id = PD.PersonDocument_id
				left join v_NationalityStatus NS with(nolock) on NS.NationalityStatus_id = P.NationalityStatus_id
			where
				PD.Document_id = :Document_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных периодики
	 */
	function getPersonEvnAndPolisData($data) {
		$params = array('PersonEvn_id' => $data['PersonEvn_id']);
		$query = "
			select
				PD.Person_id,
				PD.PersonEvn_id,
				pa.Polis_id,
				CONVERT(varchar,pol.Polis_begDate, 104) as Polis_begDate,
				CONVERT(varchar,pol.Polis_endDate, 104) as Polis_endDate
			from
				v_PersonEvn PD with (nolock)
				inner join v_Person_all pa with (nolock) on pa.PersonEvn_id = PD.PersonEvn_id and pa.Person_id = PD.Person_id
				inner join v_Polis pol with (nolock) ON pa.Polis_id = pol.Polis_id
			where
				PD.PersonEvn_id = :PersonEvn_id
				and PD.PersonEvnClass_id = 8
		";
		$resp = $this->queryResult($query, $params);
		//Для Уфы проверка пересечений полисов отключена
		if(is_array($resp) && count($resp) > 0 && getRegionNick() != 'ufa'){
			$query = "
				select
					pa.PersonEvn_id
				from v_Person_all pa with (nolock) 
					inner join v_Polis pol with (nolock) ON pa.Polis_id = pol.Polis_id
				where
					pa.Person_id = :Person_id and
					pa.PersonEvnClass_id = 8 and
					pol.Polis_id <> isnull(:Polis_id, 0) and
					(pol.Polis_begDate < :Polis_endDate or :Polis_endDate is null) and
					(pol.Polis_endDate > :Polis_begDate)
				";
			$params = $resp[0];
			/*if(!empty($data['Date'])){
				$params['Polis_begDate'] = date('Y-m-d', strtotime($data['Date']));
			}*/
			if(!empty($params['Polis_begDate'])){
				$params['Polis_begDate'] = date('Y-m-d', strtotime($params['Polis_begDate']));
			}
			if(!empty($params['Polis_endDate'])){
				$params['Polis_endDate'] = date('Y-m-d', strtotime($params['Polis_endDate']));
			}
			//echo getDebugSQL($query, $params);exit();
			$response = $this->queryResult($query, $params);
			if(is_array($response) && count($response) > 0){
				return array('Error_Msg'=>'Периоды полисов не могут пересекаться!');
			}
		}
		return $resp;
	}

    /**
     * Получение списка сотрудников
     */
    function loadPersonWorkList($data) {
        $where = array();

        if (!empty($data['PersonWork_id'])) {
            $where[] = "pw.PersonWork_id = :PersonWork_id";
        } else {
            if (!empty($data['Person_id'])) {
                $where[] = "pw.Person_id = :Person_id";
            }
            if (!empty($data['Post_id'])) {
                $where[] = "pw.Post_id = :Post_id";
            }
            if (!empty($data['Org_id'])) {
                $where[] = "pw.Org_id = :Org_id";
            }
            if (!empty($data['query'])) {
                $where[] = "nm.PersonWork_Name like :query";
                $data['query'] = $data['query'].'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
			select top 100
                pw.PersonWork_id,
                nm.PersonWork_Name,
                pw.Person_id,
                pw.Post_id
            from
                v_PersonWork pw with (nolock)
                left join v_PersonState ps with (nolock) on ps.Person_id = pw.Person_id
                left join v_Post p with (nolock) on p.Post_id = pw.Post_id
                outer apply (
                    select
                        (
                            isnull(ltrim(rtrim(ps.Person_SurName)), '')+
                            isnull(' '+ltrim(rtrim(ps.Person_FirName)), '')+
                            isnull(' '+ltrim(rtrim(ps.Person_SecName)), '')+
                            isnull(' '+convert(varchar(10), ps.Person_BirthDay, 104), '')+
                            isnull(' '+p.Post_Name, '')
                        ) as PersonWork_Name
                ) nm
            {$where_clause}
            order by
                pw.PersonWork_id
		";

        $resp = $this->queryResult($query, $data);

        return $resp;
    }

	/**
	 * Удаление данных о сотруднике организации
	 */
    function deletePersonWork($data) {
    	$params = array('PersonWork_id' => $data['PersonWork_id']);

		$query = "
			select top 1
				PMUser.Cnt as pmUserCount,
				StorageWork.Cnt as StorageWorkCount
			from 
				v_PersonWork PW with(nolock)
				outer apply(
					select top 1 count(*) as Cnt
					from v_pmUserCacheOrg with(nolock)
					where pmUserCacheOrg_id = PW.pmUserCacheOrg_id
				) PMUser
				outer apply(
					select top 1 count(*) as Cnt
					from v_DocumentUcStorageWork with(nolock)
					where (
						(Person_cid = PW.Person_id and Post_cid = PW.Post_id) or 
						(Person_eid = PW.Person_id and Post_eid = PW.Post_id)
					)
				) StorageWork
			where
				PW.PersonWork_id = :PersonWork_id
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных сотрудника');
		}
		if ($resp['pmUserCount'] > 0 || $resp['StorageWorkCount'] > 0) {
			return $this->createError('','В системе есть объекты, связанные с сотрудником. Удаление сотрудника не возможно');
		}

		$resp = $this->deleteObject('PersonWork', $params);

		return array($resp);
	}

	/**
	 * Получение данных о сотруднике организации для редактирования
	 */
    function loadPersonWorkForm($data) {
    	$params = array('PersonWork_id' => $data['PersonWork_id']);

		$query = "
			select top 1
				PW.PersonWork_id,
				convert(varchar(10), PW.PersonWork_begDate, 104) as PersonWork_begDate,
				convert(varchar(10), PW.PersonWork_endDate, 104) as PersonWork_endDate,
				PW.Org_id,
				PW.OrgStruct_id,
				PW.Person_id,
				(
					isnull(ltrim(rtrim(PS.Person_SurName)), '')+
					isnull(' '+ltrim(rtrim(PS.Person_FirName)), '')+
					isnull(' '+ltrim(rtrim(PS.Person_SecName)), '')
				) as Person_Fio,
				PW.Post_id,
				PW.pmUserCacheOrg_id
			from
				v_PersonWork PW with(nolock)
				left join v_PersonState PS with(nolock) on PS.Person_id = PW.Person_id
			where 
				PW.PersonWork_id = :PersonWork_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка сотрудников
	 */
    function loadPersonWorkGrid($data) {
		$params = array('Org_id' => $data['Org_id']);
		$filters = array('PW.Org_id = :Org_id');

		if (!empty($data['OrgStruct_id'])) {
			$filters[] = "PW.OrgStruct_id = :OrgStruct_id";
			$params['OrgStruct_id'] = $data['OrgStruct_id'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				-- select
				PW.PersonWork_id,
				convert(varchar(10), PW.PersonWork_begDate, 104) as PersonWork_begDate,
				convert(varchar(10), PW.PersonWork_endDate, 104) as PersonWork_endDate,
				PS.Person_id,
				(
					isnull(ltrim(rtrim(PS.Person_SurName)), '')+
					isnull(' '+ltrim(rtrim(PS.Person_FirName)), '')+
					isnull(' '+ltrim(rtrim(PS.Person_SecName)), '')
				) as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				P.Post_id,
				P.Post_Name
				-- end select
			from
				-- from
				v_PersonWork PW with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = PW.Person_id
				left join v_Post P with(nolock) on P.Post_id = PW.Post_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				Person_Fio asc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение данных по сигнальной информации пациента
	 */
	function getPersonSignalInfo($data) {
		$resp = $this->queryResult("
			select top 1
				pmh.PersonMedHistory_id,
				pmh.PersonMedHistory_Descr,
				pmh.PersonMedHistory_Text,
				convert(varchar(10), pmh.PersonMedHistory_setDT, 120) as PersonMedHistory_setDT,
				pbg.PersonBloodGroup_id,
				pbg.BloodGroupType_id,
				bgt.BloodGroupType_Code,
				bgt.BloodGroupType_Name,
				pbg.RhFactorType_id,
				rft.RhFactorType_Code,
				rft.RhFactorType_Name,
				convert(varchar(10), pbg.PersonBloodGroup_setDT, 120) as PersonBloodGroup_setDT,
				ph.PersonHeight_id,
				convert(varchar(10), ph.PersonHeight_setDT, 120) as PersonHeight_setDT,
				ph.PersonHeight_Height,
				case when ph.PersonHeight_IsAbnorm = 2 then 1 else 0 end as PersonHeight_IsAbnorm,
				ph.HeightAbnormType_id,
				ph.HeightMeasureType_id,
				pw.PersonWeight_id,
				convert(varchar(10), pw.PersonWeight_setDT, 120) as PersonWeight_setDT,
				pw.PersonWeight_Weight,
				case when pw.PersonWeight_IsAbnorm = 2 then 1 else 0 end as PersonWeight_IsAbnorm,
				pw.WeightAbnormType_id,
				pw.WeightMeasureType_id,
				pw.Okei_id 
			from
				v_PersonState ps (nolock)
				outer apply (
					select top 1 * from v_PersonMedHistory pmh (nolock) where pmh.Person_id = ps.Person_id order by pmh.PersonMedHistory_setDT desc
				) pmh
				outer apply (
					select top 1 * from v_PersonBloodGroup pbg (nolock) where pbg.Person_id = ps.Person_id order by pbg.PersonBloodGroup_setDT desc
				) pbg
				outer apply (
					select top 1 * from v_PersonHeight ph (nolock) where ph.Person_id = ps.Person_id order by ph.PersonHeight_setDT desc
				) ph
				outer apply (
					select top 1 * from v_PersonWeight pw (nolock) where pw.Person_id = ps.Person_id order by pw.PersonWeight_setDT desc
				) pw
				left join v_BloodGroupType BGT with (nolock) on BGT.BloodGroupType_id = PBG.BloodGroupType_id
				left join v_RhFactorType RFT with (nolock) on RFT.RhFactorType_id = PBG.RhFactorType_id
			where
				ps.Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id']
		));

		if (!empty($resp[0])) {
			$resp[0]['PersonAllergicReactionList'] = $this->queryResult("
				select
					par.PersonAllergicReaction_id,
					par.AllergicReactionLevel_id,
					arl.AllergicReactionLevel_Name,
					par.AllergicReactionType_id,
					art.AllergicReactionType_Name,
					par.DrugMnn_id,
					par.PersonAllergicReaction_Kind,
					convert(varchar(10), par.PersonAllergicReaction_setDT, 120) as PersonAllergicReaction_setDT
				from
					v_PersonAllergicReaction par (nolock)
					left join v_AllergicReactionLevel arl (nolock) on arl.AllergicReactionLevel_id = par.AllergicReactionLevel_id
					left join v_AllergicReactionType art (nolock) on art.AllergicReactionType_id = par.AllergicReactionType_id
				where
					par.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['PrivilegeTypeList'] = $this->queryResult("
				select
					pt.PrivilegeType_id,
					pt.PrivilegeType_Name,
					pt.PrivilegeType_Code,
					convert(varchar(10), pp.PersonPrivilege_begDate, 120) as PersonPrivilege_begDate,
					convert(varchar(10), pp.PersonPrivilege_endDate, 120) as PersonPrivilege_endDate
				from
					v_PersonPrivilege pp (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pp.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['PersonDispList'] = $this->queryResult("
				select
					pd.Diag_id,
					convert(varchar(10), pd.PersonDisp_begDate, 120) as PersonDisp_begDate,
					convert(varchar(10), pd.PersonDisp_endDate, 120) as PersonDisp_endDate,
					d.Diag_Code,
					pd.DispOutType_id,
					dot.DispOutType_Name,
					pd.LpuSection_id,
					ls.LpuSectionProfile_id,
					pd.MedPersonal_id
				from
					v_PersonDisp pd (nolock)
					left join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
					left join v_DispOutType dot (nolock) on dot.DispOutType_id = pd.DispOutType_id
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = pd.LpuSection_id
				where
					pd.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['EvnPLDispList'] = $this->queryResult("
				select
					epldd13.EvnPLDispDop13_id as EvnPLDisp_id,
					epldd13.DispClass_id,
					epldd13.HealthKind_id,
					epldd13.Lpu_id,
					convert(varchar(10), epldd13.EvnPLDispDop13_consDT, 120) as EvnPLDisp_consDT,
					convert(varchar(10), epldd13.EvnPLDispDop13_setDT, 120) as EvnPLDisp_setDT,
					case when epldd13.EvnPLDispDop13_IsEndStage = 2 then 1 else 0 end as EvnPLDisp_IsEndStage
				from
					v_EvnPLDispDop13 epldd13 (nolock)
				where
					epldd13.Person_id = :Person_id
					
				union all
				
				select
					epldp.EvnPLDispProf_id as EvnPLDisp_id,
					epldp.DispClass_id,
					epldp.HealthKind_id,
					epldp.Lpu_id,
					convert(varchar(10), epldp.EvnPLDispProf_consDT, 120) as EvnPLDisp_consDT,
					convert(varchar(10), epldp.EvnPLDispProf_setDT, 120) as EvnPLDisp_setDT,
					case when epldp.EvnPLDispProf_IsEndStage = 2 then 1 else 0 end as EvnPLDisp_IsEndStage
				from
					v_EvnPLDispProf epldp (nolock)
				where
					epldp.Person_id = :Person_id
					
				union all
				
				select
					epldti.EvnPLDispTeenInspection_id as EvnPLDisp_id,
					epldti.DispClass_id,
					null as HealthKind_id,
					epldti.Lpu_id,
					convert(varchar(10), epldti.EvnPLDispTeenInspection_consDT, 120) as EvnPLDisp_consDT,
					convert(varchar(10), epldti.EvnPLDispTeenInspection_setDT, 120) as EvnPLDisp_setDT,
					case when epldti.EvnPLDispTeenInspection_IsFinish = 2 then 1 else 0 end as EvnPLDisp_IsEndStage
				from
					v_EvnPLDispTeenInspection epldti (nolock)
				where
					epldti.Person_id = :Person_id
					
				union all
				
				select
					epldo.EvnPLDispOrp_id as EvnPLDisp_id,
					epldo.DispClass_id,
					null as HealthKind_id,
					epldo.Lpu_id,
					convert(varchar(10), epldo.EvnPLDispOrp_consDT, 120) as EvnPLDisp_consDT,
					convert(varchar(10), epldo.EvnPLDispOrp_setDT, 120) as EvnPLDisp_setDT,
					case when epldo.EvnPLDispOrp_IsFinish = 2 then 1 else 0 end as EvnPLDisp_IsEndStage
				from
					v_EvnPLDispOrp epldo (nolock)
				where
					epldo.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));

			if (!empty($resp[0]['EvnPLDispList'])) {
				foreach($resp[0]['EvnPLDispList'] as $key => $value) {
					$resp[0]['EvnPLDispList'][$key]['EvnPLDispDiagList'] = $this->queryResult("
						select
							eddd.Diag_id,
							d.Diag_Code
						from
							v_EvnDiagDopDisp eddd (nolock)
							left join v_Diag d (nolock) on d.Diag_id = eddd.Diag_id
						where
							eddd.EvnDiagDopDisp_pid = :EvnPLDisp_id
					", array(
						'EvnPLDisp_id' => $value['EvnPLDisp_id']
					));

					unset($resp[0]['EvnPLDispList'][$key]['EvnPLDisp_id']);
				}
			}

			$resp[0]['PersonDiagOsnList'] = $this->queryResult("
				select
					eds.Diag_id,
					d.Diag_Code,
					eds.Lpu_id,
					lsp.LpuSectionProfile_Name,
					convert(varchar(10), eds.EvnDiagSpec_setDT, 120) as EvnDiag_setDT,
					eds.MedStaffFact_id
				from
					v_EvnDiagSpec eds (nolock)
					left join v_Diag d (nolock) on d.Diag_id = eds.Diag_id
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = eds.MedStaffFact_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
				where
					eds.Person_id = :Person_id
					
				union all
				
				select
					es.Diag_id,
					d.Diag_Code,
					es.Lpu_id,
					lsp.LpuSectionProfile_Name,
					convert(varchar(10), es.EvnSection_setDT, 120) as EvnDiag_setDT,
					es.MedStaffFact_id
				from
					v_EvnSection es (nolock)
					left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = es.MedStaffFact_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
				where
					es.Person_id = :Person_id
					
				union all
				
				select
					edps.Diag_id,
					d.Diag_Code,
					edps.Lpu_id,
					null as LpuSectionProfile_Name,
					convert(varchar(10), edps.EvnDiagPS_setDT, 120) as EvnDiag_setDT,
					null as MedStaffFact_id
				from
					v_EvnDiagPS edps (nolock)
					left join v_Diag d (nolock) on d.Diag_id = edps.Diag_id
				where
					edps.Person_id = :Person_id
					
				union all
				
				select
					evpl.Diag_id,
					d.Diag_Code,
					evpl.Lpu_id,
					lsp.LpuSectionProfile_Name,
					convert(varchar(10), evpl.EvnVizitPL_setDT, 120) as EvnDiag_setDT,
					evpl.MedStaffFact_id
				from
					v_EvnVizitPL evpl (nolock)
					left join v_Diag d (nolock) on d.Diag_id = evpl.Diag_id
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
				where
					evpl.Person_id = :Person_id
					
				union all
				
				select
					edpls.Diag_id,
					d.Diag_Code,
					edpls.Lpu_id,
					null as LpuSectionProfile_Name,
					convert(varchar(10), edpls.EvnDiagPLSop_setDT, 120) as EvnDiag_setDT,
					null as MedStaffFact_id
				from
					v_EvnDiagPLSop edpls (nolock)
					left join v_Diag d (nolock) on d.Diag_id = edpls.Diag_id
				where
					edpls.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['EvnUslugaOperList'] = $this->queryResult("
				select
					convert(varchar(10), euo.EvnUslugaOper_setDT, 120) as EvnUslugaOper_setDT,
					convert(varchar(10), euo.EvnUslugaOper_disDT, 120) as EvnUslugaOper_disDT,
					uc.UslugaComplex_Name,
					uc.UslugaComplex_Code,
					euo.Lpu_id
				from
					v_EvnUslugaOper euo (nolock)
					left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = euo.UslugaComplex_id
				where
					euo.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['EvnDirectionFailList'] = $this->queryResult("
				select
					ed.EvnDirection_id,
					ed.MedStaffFact_id,
					convert(varchar(10), ed.EvnDirection_statusDate, 120) as EvnDirection_failDT,
					convert(varchar(10), ed.EvnDirection_setDate, 120) as EvnDirection_setDate,
					esh.EvnStatusCause_id,
					puc.pmUser_Name
				from
					v_EvnDirection_all ed (nolock)
					outer apply (
						select top 1
							esh.EvnStatusCause_id,
							esh.pmUser_insID
						from
							v_EvnStatusHistory esh (nolock)
						where
							esh.Evn_id = ed.EvnDirection_id
						order by
							esh.EvnStatusHistory_insDT desc
					) esh
					left join v_pmUserCache puc (nolock) on puc.pmUser_id = esh.pmUser_insID
				where
					ed.EvnStatus_id in (12,13) -- отменённые
					and ed.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));
			$resp[0]['OpenEvnStickList'] = $this->queryResult("
				select
					convert(varchar(10), es.EvnStick_setDate, 120) as EvnStick_setDate,
					es.StickWorkType_id,
					es.EvnStick_Num,
					es.EvnStick_Ser,
					es.StickOrder_id
				from
					v_EvnStick es (nolock)
				where
					es.Person_id = :Person_id
			", array(
				'Person_id' => $data['Person_id']
			));


			return $resp[0];
		}

		return false;
	}

	/**
	 * Данные по профилактическим мероприятиям
	 */
	public function exportPersonProfData($data) {
		$db = $this->load->database('default', true); // получаем коннект к БД
		$db->close(); // коннект должен быть закрыт
		$db->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)

		$filterList = array();
		$queryParams = array(
			 'Lpu_id' => $data['Lpu_id']
			,'Year' => $data['Year']
			,'Month' => sprintf('%02d', $data['Month'])
		);
		
		$filterDispList = " and (
			case when EPLD.EvnPLDisp_IsFinish = 2 then EPLD.EvnPLDisp_disDT else EPLD.EvnPLDisp_setDT end between @PredPredDate and @PredDate - 1
		)";
		
		if (
			($data['Year'] == 2017 && $data['Month'] == 11)
			|| $data['Month'] == 1
		) {
			// В первый месяц выгружаем всех
			$exportVariant = 1;
			$filterList[] = "pc.PersonCard_begDate <= @Date";
			$filterList[] = "(pc.PersonCard_endDate > @Date or pc.PersonCard_endDate is null)";
			
			if ($data['Year'] == 2017 && $data['Month'] == 11 && getRegionNick() == 'kareliya') //https://redmine.swan.perm.ru/issues/116592
			{
				$filterDispList = " and (EPLD.EvnPLDisp_setDT >= @BegYear and EPLD.EvnPLDisp_disDT <= @PredPredDateEnd)";
			}
		}
		else {
			// В остальные месяцы тянем прикрепленных за предыдущий месяц
			$exportVariant = 2;
			$filterList[] = "pc.PersonCard_begDate between @PredDate + 1 and @Date";
			$filterList[] = "(pc.PersonCard_endDate > @Date or pc.PersonCard_endDate is null)";
		}

		// Запрос #1
		// Выгрузка подлежащих осмотру
		// Запрос #2
		// Выгрузка прошедших осмотр
		// Затем сборка массива

		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();
		$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList($queryParams['Year'] . '-01-01');

		if ( !empty($dateX) && $dateX <= $queryParams['Year'] . '-' . $queryParams['Month'] . '-01' ) {
			$dvnCondition = "
				when PS.Person_Age >= 40" . ($exportVariant == 2 ? " and DispClass_1.EvnPLDispDop13_id is null" : "") . " then 1
				when PS.Person_Age >= 18 and PS.Person_Age % 3 = 0" . ($exportVariant == 2 ? " and DispClass_1.EvnPLDispDop13_id is null" : "") . " then 1
			";
		}
		else {
			$dvnCondition = "
				when PS.Person_Age >= 21 and PS.Person_Age % 3 = 0 " . ($exportVariant == 2 ? "and DispClass_1.EvnPLDispDop13_id is null" : "") . "  then 1
			";
		}

		$query = "
			declare @Date datetime = cast(:Year + :Month + '01' as datetime);
			declare @PredDate datetime = DATEADD(MONTH, -1, @Date);
			declare @PredPredDate datetime = DATEADD(MONTH, -1, @PredDate);
			
			declare @BegYear datetime = cast(:Year + '01' + '01' as datetime);
			declare @PredPredDateEnd datetime = DATEADD(DAY,-1,@PredDate);

			declare
				@getDate datetime = dbo.tzGetdate(),
				@childDispKv int,
				@Lpu_id bigint = :Lpu_id,
				@DateEndYear date = :Year + '-12-31';

			set @childDispKv = (MONTH(@getDate) - 1) / 3 + 1;

			select
				1 as exportVariant,
				SMO.Org_id,
				SMO.Orgsmo_f002smocod as SMO,
				PS.Person_id as ID_PAC, -- Уникальный в пределах МО идентификатор гражданина
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' then '' else PS.Person_Secname end)), '') as OT, -- Отчество
				case when PS.Sex_id = 3 then 1 else PS.Sex_id end as W, -- Пол
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения
				PT.PolisType_CodeF008 as VPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as SPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as NPOLIS,
				convert(varchar(10), PC.PersonCard_begDate, 120) as DATE,
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and ISNULL(PC.PersonCard_IsAttachCondit,1) = 2) then 1
					else 0
				end as SP_PRIK,
				case
					when CCC.CardCloseCause_Code is null then 1
					when CCC.CardCloseCause_Code = 1 then 2
					--when CCC.CardCloseCause_Code = 3 then 5
					when CCC.CardCloseCause_Code = 7 then 4
					when ADDRESSCHANGE.PersonUAddress_id IS NOT NULL then 3 -- Выгружать, если с момента прикрепления к предыдущей МО адрес изменялся
					else 0
				end as T_PRIK,
				right('00' + isnull(left(LPS.LpuSection_Code, 2), ''), 2) as KOD_PODR,
				PC.LpuRegion_Name as NUM_UCH,
				case
					when LRT.LpuRegionType_SysNick = 'ter' then 1
					when LRT.LpuRegionType_SysNick = 'ped' then 2
					when LRT.LpuRegionType_SysNick = 'vop' then 3
					when LRT.LpuRegionType_SysNick = 'feld' then 3
					else null
				end as TIP_UCH,
				MEDSnils.Person_Snils as SNILS_VR,
				ISNULL(DD.DISP, 0) as DISP,
				case
					when DD.DISP IS NULL then NULL
					when dbo.Age2(PS.Person_BirthDay, @getDate) < 3 then @childDispKv
					else (MONTH(PS.Person_BirthDay) - 1) / 3 + 1
				end as DISP_KV,
				[PI].PersonInfo_InternetPhone as PHONE1,
				null as PHONE2,
				convert(varchar(10), case
					when DD.DISP in (4, 5) and DispClass_2.EvnPLDispDop13_IsRefusal = 2 then DispClass_2.EvnPLDispDop13_setDT
					when DD.DISP in (1, 2) and DispClass_1.EvnPLDispDop13_IsRefusal = 2 then DispClass_1.EvnPLDispDop13_setDT
					when DD.DISP = 3 and DispClass_5.EvnPLDisp_IsRefusal = 2 then DispClass_5.EvnPLDisp_setDT
					when DD.DISP = 7 and DispClassChildSecond.EvnPLDispTeenInspection_IsRefusal = 2 then DispClassChildSecond.EvnPLDispTeenInspection_setDT
					when DD.DISP = 6 and DispClassChildFirst.EvnPLDispTeenInspection_IsRefusal = 2 then DispClassChildFirst.EvnPLDispTeenInspection_setDT
				end, 120) as REJECT_DATE,
				null as DISP_FACT,
				null as DATE_NPM,
				null as DATE_OPM,
				null as DISP2_NPM
			from
				v_PersonCard_all PC with (nolock)
				outer apply (
					select top 1 Person_id, Server_pid, Polis_id, Person_EdNum, UAddress_id, Person_SurName, Person_FirName, Person_Secname, Sex_id, Person_BirthDay, Person_deadDT, dbo.Age2(Person_BirthDay, @DateEndYear) as Person_Age
					from v_Person_all P with(nolock)
					where P.Person_id = PC.Person_id
						and cast(P.PersonEvn_insDT as date) <= @Date
					order by P.PersonEvn_insDT desc, P.PersonEvn_id desc
				) PS
				outer apply (
					select top 1 PersonInfo_InternetPhone
					from v_PersonInfo with (nolock)
					where Person_id = PC.Person_id
						and PersonInfo_InternetPhone is not null
				) [PI]
				outer apply (
					select top 1 PersonPrivilegeWOW_id
					from v_PersonPrivilegeWOW (nolock)
					where Person_id = PS.Person_id
				) PPWOW
				outer apply (
					" . (count($personPrivilegeCodeList) > 0 ? "
					select top 1 t1.PersonPrivilege_id
					from v_PersonPrivilege t1 with (nolock)
					where t1.Person_id = PS.Person_id
						and t1.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
					" : "select top 1 null as PersonPrivilege_id") . "
				) PP
				outer apply (
					select top 1 EvnPLDispDop13_id, EvnPLDispDop13_IsFinish, EvnPLDispDop13_IsTwoStage, EvnPLDispDop13_IsRefusal, EvnPLDispDop13_setDT
					from v_EvnPLDispDop13 with (nolock)
					where Person_id = PS.Person_id
						and YEAR(EvnPLDispDop13_setDT) = :Year
						and DispClass_id = 1
				) DispClass_1
				outer apply (
					select top 1 EvnPLDispDop13_id, EvnPLDispDop13_IsRefusal, EvnPLDispDop13_setDT
					from v_EvnPLDispDop13 with (nolock)
					where Person_id = PS.Person_id
						and YEAR(EvnPLDispDop13_setDT) = :Year
						and DispClass_id = 2
				) DispClass_2
				outer apply (
					select top 1 EvnPLDisp_id, EvnPLDisp_IsRefusal, EvnPLDisp_setDT
					from v_EvnPLDisp with (nolock)
					where Person_id = PS.Person_id
						and YEAR(EvnPLDisp_setDT) = :Year
						and DispClass_id = 5
				) DispClass_5
				outer apply (
					select top 1 EvnPLDisp_id
					from v_EvnPLDisp with (nolock)
					where Person_id = PS.Person_id
						and DispClass_id = 5
						and YEAR(EvnPLDisp_setDT) = YEAR(@Date) - 1
				) DispClass_5_LastYear
				outer apply (
					select top 1 EvnPLDispTeenInspection_id, EvnPLDispTeenInspection_IsFinish, EvnPLDispTeenInspection_IsTwoStage, EvnPLDispTeenInspection_IsRefusal, EvnPLDispTeenInspection_setDT
					from v_EvnPLDispTeenInspection with (nolock)
					where Person_id = PS.Person_id
						and YEAR(EvnPLDispTeenInspection_setDT) = :Year
						and DispClass_id in (10)
				) DispClassChildFirst
				outer apply (
					select top 1 EvnPLDispTeenInspection_id, EvnPLDispTeenInspection_IsRefusal, EvnPLDispTeenInspection_setDT
					from v_EvnPLDispTeenInspection with (nolock)
					where Person_id = PS.Person_id
						and YEAR(EvnPLDispTeenInspection_setDT) = :Year
						and DispClass_id in (12)
				) DispClassChildSecond
				outer apply (
					select top 1
						case
							when PPWOW.PersonPrivilegeWOW_id is not null and ISNULL(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and ISNULL(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 5
							when PP.PersonPrivilege_id is not null and ISNULL(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and ISNULL(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 4
							when ISNULL(DispClass_1.EvnPLDispDop13_IsFinish, 1) = 2 and ISNULL(DispClass_1.EvnPLDispDop13_IsTwoStage, 1) = 2 and DispClass_2.EvnPLDispDop13_id is null then 4
							when ISNULL(DispClassChildFirst.EvnPLDispTeenInspection_IsFinish, 1) = 2 and ISNULL(DispClassChildFirst.EvnPLDispTeenInspection_IsTwoStage, 1) = 2 and DispClassChildSecond.EvnPLDispTeenInspection_id is null then 7
							when PS.Person_Age >= 18 and PPWOW.PersonPrivilegeWOW_id is not null " . ($exportVariant == 2 ? "and DispClass_1.EvnPLDispDop13_id is null" : "") . " then 2
							when PS.Person_Age >= 18 and PP.PersonPrivilege_id is not null " . ($exportVariant == 2 ? "and DispClass_1.EvnPLDispDop13_id is null" : "") . "  then 1
							" . $dvnCondition . "
							when PS.Person_Age >= 18 and DispClass_5.EvnPLDisp_id is null " . ($exportVariant == 2 ? "and DispClass_5_LastYear.EvnPLDisp_id is null" : "") . "  then 3
							when PS.Person_Age < 18 " . ($exportVariant == 2 ? "and ISNULL(DispClassChildFirst.EvnPLDispTeenInspection_IsTwoStage, 1) = 1" : "") . "  then 6
							else 0
						end as DISP
				) DD
				inner join v_Lpu L with (nolock) on L.Lpu_id = PC.Lpu_id
				inner join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
				inner join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = PLS.OrgSmo_id and SMO.KLRgn_id = 10
				outer apply (
					select top 1 CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t with(nolock)
					where t.Person_id = PC.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and t.PersonCard_endDate = PC.PersonCard_begDate
					order by t.PersonCard_begDate desc
				) PCL
				outer apply (
					select top 1
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua (nolock)
					where
						pua.Person_id = pc.Person_id
						and pua.PersonUAddress_insDate >= PCL.PersonCard_begDate
						and pua.PersonUAddress_insDate <= @Date
				) ADDRESSCHANGE
				left join v_CardCloseCause CCC with (nolock) on CCC.CardCloseCause_id = PCL.CardCloseCause_id
				left join [Address] A with (nolock) on A.Address_id = PS.UAddress_id
				outer apply(
					select top 1 MedPers.Person_Snils
					from v_MedStaffRegion MSR with(nolock)
						inner join v_MedPersonal MedPers with(nolock) on MedPers.MedPersonal_id = MSR.MedPersonal_id
						inner join v_MedStaffFact msf with (nolock) on msf.MedPersonal_id = MedPers.MedPersonal_id
					where MSR.LpuRegion_id = PC.LpuRegion_id
						and MedPers.Person_Snils is not null
						and msf.Lpu_id = @Lpu_id
						and (msf.WorkData_begDate is null or cast(msf.WorkData_begDate as date) <= @Date)
						and (msf.WorkData_endDate is null or cast(msf.WorkData_endDate as date) >= @Date)
						and (MSR.MedStaffRegion_begDate is null or cast(MSR.MedStaffRegion_begDate as date) <= @Date)
						and (MSR.MedStaffRegion_endDate is null or cast(MSR.MedStaffRegion_endDate as date) >= @Date)
					order by MSR.MedStaffRegion_isMain desc
				) as MEDSnils
				left join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = PC.LpuRegionType_id
				left join v_LpuSection LPS with(nolock) on LPS.LpuSection_id = LR.LpuSection_id
			where PC.LpuAttachType_id = 1
				--and PS.Server_pid = 0
				and DD.DISP is not null
				and (PLS.Polis_endDate is null or PLS.Polis_endDate >= @Date)
				and (
					(PLS.PolisType_id = 4 and PS.Person_EdNum is not null)
					or (PLS.PolisType_id <> 4 and PLS.Polis_Num is not null)
				)
				and PT.PolisType_CodeF008 is not null
				and PC.Lpu_id = @Lpu_id
				and (PS.Person_deadDT is null or PS.Person_deadDT > @Date)
				" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "

			union all

			select
				2 as exportVariant,
				SMO.Org_id,
				SMO.Orgsmo_f002smocod as SMO,
				PS.Person_id as ID_PAC, -- Уникальный в пределах МО идентификатор гражданина
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(upper(case when replace(PS.Person_Secname, ' ', '') = '---' then '' else PS.Person_Secname end)), '') as OT, -- Отчество
				case when PS.Sex_id = 3 then 1 else PS.Sex_id end as W, -- Пол
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения
				PT.PolisType_CodeF008 as VPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as SPOLIS,
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as NPOLIS,
				convert(varchar(10), PC.PersonCard_begDate, 120) as DATE,
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and ISNULL(PC.PersonCard_IsAttachCondit,1) = 2) then 1
					else 0
				end as SP_PRIK,
				case
					when CCC.CardCloseCause_Code is null then 1
					when CCC.CardCloseCause_Code = 1 then 2
					--when CCC.CardCloseCause_Code = 3 then 5
					when CCC.CardCloseCause_Code = 7 then 4
					when ADDRESSCHANGE.PersonUAddress_id IS NOT NULL then 3 -- Выгружать, если с момента прикрепления к предыдущей МО адрес изменялся
					else 0
				end as T_PRIK,
				right('00' + isnull(left(LPS.LpuSection_Code, 2), ''), 2) as KOD_PODR,
				LR.LpuRegion_Name as NUM_UCH,
				case
					when LRT.LpuRegionType_SysNick = 'ter' then 1
					when LRT.LpuRegionType_SysNick = 'ped' then 2
					when LRT.LpuRegionType_SysNick = 'vop' then 3
					when LRT.LpuRegionType_SysNick = 'feld' then 3
					else null
				end as TIP_UCH,
				MEDSnils.Person_Snils as SNILS_VR,
				DD.DISP,
				case
					when DD.DISP in (4, 5, 7) then (MONTH(ISNULL(EPLDF.EvnPLDisp_disDT, EPLD.EvnPLDisp_disDT)) - 1) / 3 + 1
					when dbo.Age2(PS.Person_BirthDay, @getDate) < 3 then @childDispKv
					else (MONTH(PS.Person_BirthDay) - 1) / 3 + 1
				end as DISP_KV,
				[PI].PersonInfo_InternetPhone as PHONE1,
				null as PHONE2,
				case when EPLD.EvnPLDisp_IsRefusal = 2 then convert(varchar(10), EPLD.EvnPLDisp_setDT, 120) else null end as REJECT_DATE,
				case
					when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null then 2
					when EPLD.DispClass_id = 1 then 1
					when EPLD.DispClass_id = 2 and PPWOW.PersonPrivilegeWOW_id is not null then 5
					when EPLD.DispClass_id = 2 then 4
					when EPLD.DispClass_id = 5 then 3
					when EPLD.DispClass_id in (6, 9, 10) then 6
					when EPLD.DispClass_id in (11, 12) then 7
				end as DISP_FACT,
				convert(varchar(10), EPLD.EvnPLDisp_setDT, 120) as DATE_NPM,
				convert(varchar(10), EPLD.EvnPLDisp_disDT, 120) as DATE_OPM,
				case
					when IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 1
					when IsTwoStageChild.EvnPLDispTeenInspection_IsTwoStage = 2 then 1
					else 0
				end as DISP2_NPM
			from
				v_EvnPLDisp EPLD with (nolock)
				outer apply (
					select top 1 EvnPLDispDop13_IsTwoStage
					from EvnPLDispDop13 with (nolock)
					where EvnPLDisp_id = EPLD.EvnPLDisp_id
				) IsTwoStageAdult
				outer apply (
					select top 1 EvnPLDispTeenInspection_IsTwoStage
					from EvnPLDispTeenInspection with (nolock)
					where EvnPLDisp_id = EPLD.EvnPLDisp_id
				) IsTwoStageChild
				inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = EPLD.PersonEvn_id and PS.Server_id = EPLD.Server_id
				inner join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT with (nolock) on PT.PolisType_id = PLS.PolisType_id
				inner join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = PLS.OrgSmo_id and SMO.KLRgn_id = 10
				outer apply (
					select top 1 PersonInfo_InternetPhone
					from v_PersonInfo with (nolock)
					where Person_id = PS.Person_id
						and PersonInfo_InternetPhone is not null
				) [PI]
				outer apply (
					select top 1 PersonPrivilegeWOW_id
					from v_PersonPrivilegeWOW (nolock)
					where Person_id = PS.Person_id
				) PPWOW
				outer apply (
					" . (count($personPrivilegeCodeList) > 0 ? "
					select top 1 t1.PersonPrivilege_id
					from v_PersonPrivilege t1 with (nolock)
					where t1.Person_id = PS.Person_id
						and t1.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
					" : "select top 1 null as PersonPrivilege_id") . "
				) PP
				cross apply (
					select top 1 Lpu_id, PersonCard_id, LpuRegion_id, PersonCard_begDate, LpuRegionType_id, PersonCardAttach_id, PersonCard_IsAttachCondit
					from v_PersonCard_all with (nolock)
					where Person_id = PS.Person_id
						and LpuAttachType_id = 1
						and PersonCard_begDate <= @PredDate
						and (PersonCard_endDate is null or PersonCard_endDate > @PredDate)
				) PC
				inner join v_Lpu L with (nolock) on L.Lpu_id = PC.Lpu_id
				outer apply (
					select top 1
						CardCloseCause_id,
						PersonCard_begDate
					from v_PersonCard_all t with(nolock)
					where t.Person_id = PS.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and t.PersonCard_endDate = PC.PersonCard_begDate
					order by t.PersonCard_begDate desc
				) PCL
				outer apply (
					select top 1
						PersonUAddress_id
					from
						v_PersonUAddress with (nolock)
					where
						Person_id = PS.Person_id
						and PersonUAddress_insDate >= PCL.PersonCard_begDate
						and PersonUAddress_insDate <= @PredDate
				) ADDRESSCHANGE
				left join v_CardCloseCause CCC with (nolock) on CCC.CardCloseCause_id = PCL.CardCloseCause_id
				left join [Address] A with (nolock) on A.Address_id = PS.UAddress_id
				outer apply(
					select top 1 MedPers.Person_Snils
					from v_MedStaffRegion MSR with(nolock)
						inner join v_MedPersonal MedPers with(nolock) on MedPers.MedPersonal_id = MSR.MedPersonal_id
						inner join v_MedStaffFact msf with (nolock) on msf.MedPersonal_id = MedPers.MedPersonal_id
					where MSR.LpuRegion_id = PC.LpuRegion_id
						and MedPers.Person_Snils is not null
						and msf.Lpu_id = @Lpu_id
						and (msf.WorkData_begDate is null or cast(msf.WorkData_begDate as date) <= @Date)
						and (msf.WorkData_endDate is null or cast(msf.WorkData_endDate as date) >= @Date)
						and (MSR.MedStaffRegion_begDate is null or cast(MSR.MedStaffRegion_begDate as date) <= @Date)
						and (MSR.MedStaffRegion_endDate is null or cast(MSR.MedStaffRegion_endDate as date) >= @Date)
					order by MSR.MedStaffRegion_isMain desc
				) as MEDSnils
				left join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT with(nolock) on LRT.LpuRegionType_id = PC.LpuRegionType_id
				left join v_LpuSection LPS with(nolock) on LPS.LpuSection_id = LR.LpuSection_id
				left join v_EvnPLDisp EPLDF with (nolock) on EPLDF.EvnPLDisp_id = EPLD.EvnPLDisp_fid and EPLD.DispClass_id in (2, 12) -- карта первого этапа
				outer apply (
					select top 1
						case
							when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null and IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 5
							when EPLD.DispClass_id = 1 and IsTwoStageAdult.EvnPLDispDop13_IsTwoStage = 2 then 4
							when EPLD.DispClass_id = 1 and PPWOW.PersonPrivilegeWOW_id is not null then 2
							when EPLD.DispClass_id = 1 then 1
							when EPLD.DispClass_id = 2 and PPWOW.PersonPrivilegeWOW_id is not null then 5
							when EPLD.DispClass_id = 2 then 4
							when EPLD.DispClass_id = 5 then 3
							when EPLD.DispClass_id in (11, 12) then 7
							when EPLD.DispClass_id in (6, 9, 10) and IsTwoStageChild.EvnPLDispTeenInspection_IsTwoStage = 2 then 7
							when EPLD.DispClass_id in (6, 9, 10) then 6
						end as DISP
				) DD
			where
				EPLD.Lpu_id = @Lpu_id
				and EPLD.DispClass_id in (1, 2, 5, 10, 12)
				and DD.DISP is not null
				{$filterDispList}
				and (PLS.Polis_endDate is null or PLS.Polis_endDate >= @Date)
				and (
					(PLS.PolisType_id = 4 and PS.Person_EdNum is not null)
					or (PLS.PolisType_id <> 4 and PLS.Polis_Num is not null)
				)
				and PT.PolisType_CodeF008 is not null
			
			order by exportVariant
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$PERS = $result->result('array');

		if ( !is_array($PERS) || count($PERS) == 0) {
			return array('Error_Msg' => 'Отсутствуют данные для выгрузки');
		}

		$response = array();
		$response['SMO'] = array();

		// Получаем данные МО
		$query = "
			select top 1
				L.Org_id,
				L.Lpu_f003mcod as CODE_MO,
				PassT.PassportToken_tid as ID_MO,
				convert(varchar(10), dbo.tzGetDate(), 120) as DATA
			from v_Lpu L with (nolock)
				left join fed.v_PassportToken PassT with (nolock) on PassT.Lpu_id = L.Lpu_id
			where L.Lpu_id = :Lpu_id
		";
		$result = $db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$ZGLV = $result->result('array');

		if ( !is_array($ZGLV) || count($ZGLV) == 0) {
			return 'Ошибка при получении кода МО!';
		}

		$SMO = array();
		$SMO_PERS = array();

		foreach ( $PERS as $row ) {
			if ( empty($row['DISP']) ) {
				continue;
			}

			$smo_code = $row['SMO'];

			if ( !empty($row['PHONE1']) ) {
				$row['PHONE1'] = preg_replace('/[^\d]/ui', '', $row['PHONE1']); // выпиливаем из телефона всё, кроме цифр
				$row['PHONE1'] = substr(trim($row['PHONE1'], '+'), 0, 11);
			}

			if ( !in_array($smo_code, $SMO) ) {
				$SMO[] = $smo_code;
			}

			if ( !array_key_exists($smo_code, $SMO_PERS) ) {
				$SMO_PERS[$smo_code] = array();
			}

			$row['DISP_FACT_LIST'] = array();

			if ( $row['exportVariant'] == 1 ) {
				$SMO_PERS[$smo_code][$row['ID_PAC']] = $row;
			}
			else if ( !array_key_exists($row['ID_PAC'], $SMO_PERS[$smo_code]) ) {
				$SMO_PERS[$smo_code][$row['ID_PAC']] = $row;
			}

			unset($SMO_PERS[$smo_code][$row['ID_PAC']]['DISP_FACT']);
			unset($SMO_PERS[$smo_code][$row['ID_PAC']]['DATE_NPM']);
			unset($SMO_PERS[$smo_code][$row['ID_PAC']]['DATE_OPM']);
			unset($SMO_PERS[$smo_code][$row['ID_PAC']]['DISP2_NPM']);
			if ( !empty($row['DISP_FACT']) && empty($row['REJECT_DATE']) ) {
				if($row['DISP2_NPM'] == 0 && getRegionNick() == 'kareliya')
				{
					if($row['DISP_FACT'] == 1 && !empty($row['DATE_OPM']))
					{
						$SMO_PERS[$smo_code][$row['ID_PAC']]['DISP_FACT_LIST'][] = array(
							'DISP_FACT' => $row['DISP_FACT'],
							'DATE_NPM' => $row['DATE_NPM'],
							'DATE_OPM' => $row['DATE_OPM'],
							'DISP2_NPM' => $row['DISP2_NPM']
						);
					}
					else
					{
						$SMO_PERS[$smo_code][$row['ID_PAC']]['DISP_FACT_LIST'][] = array(
							'DISP_FACT' => $row['DISP_FACT'],
							'DATE_NPM' => $row['DATE_NPM'],
							'DATE_OPM' => $row['DATE_OPM'],
							'DISP2_NPM' => NULL
						);
					}
				}
				else
				{
					$SMO_PERS[$smo_code][$row['ID_PAC']]['DISP_FACT_LIST'][] = array(
						'DISP_FACT' => $row['DISP_FACT'],
						'DATE_NPM' => $row['DATE_NPM'],
						'DATE_OPM' => $row['DATE_OPM'],
						'DISP2_NPM' => $row['DISP2_NPM']
					);
				}
			}
		}

		for ( $i = 0; $i < count($SMO); $i++ ) {
			$smo_code = $SMO[$i];
			$item = array();
			$itemZGLV = $ZGLV;

			$itemZGLV[0]['SMO'] = $smo_code;
			$itemZGLV[0]['ZAP'] = count($SMO_PERS[$smo_code]);

			$item['PERS'] = $SMO_PERS[$smo_code];
			$item['ZGLV'] = $itemZGLV;

			unset($SMO_PERS[$smo_code]);

			$response['SMO_PERS'][] = $item;

			unset($item);
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function checkPersonPhoneStatus($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MedStaffFact_id' => !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null,
		);
		$query = "
			declare
				@Person_id bigint = :Person_id,
				@MedStaffFact_id bigint = :MedStaffFact_id,
				@date date = dbo.tzGetDate();
			select top 1
				PP.PersonPhone_id,
				PP.PersonPhone_Phone,
				cast(PPH.PersonPhoneHist_insDT as date) as PersonPhoneHist_Date,
				isnull(PPS.PersonPhoneStatus_Code, 1) as PersonPhoneStatus_Code,
				@date as today
			from
				Person P with(nolock)
				outer apply (
					select top 1 PP.*
					from v_PersonPhone PP with(nolock)
					where PP.Person_id = P.Person_id
					order by PP.PersonPhone_insDT desc
				) PP
				outer apply (
					select top 1 PPH.*
					from v_PersonPhoneHist PPH with(nolock)
					where PPH.PersonPhone_id = PP.PersonPhone_id
					--and isnull(PPH.MedStaffFact_id, 0) = isnull(@MedStaffFact_id, 0)
					order by PPH.PersonPhoneHist_insDT desc
				) PPH
				left join v_PersonPhoneStatus PPS with(nolock) on PPS.PersonPhoneStatus_id = PPH.PersonPhoneStatus_id
			where
				P.Person_id = @Person_id
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении статуса подтверждения телефонного номера человека');
		}

		$response = array(
			'success' => true,
			'PersonPhone_Phone' => $resp['PersonPhone_Phone'],
			'isVerified' => (!empty($resp['PersonPhone_Phone']) && $resp['PersonPhoneStatus_Code'] != 1),
			'wasVerificationToday' => ($resp['PersonPhoneHist_Date'] == $resp['today'])
		);

		return array($response);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function addPersonPhoneHist($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
			select top 1 
				PP.PersonPhone_id,
				PP.PersonPhone_Phone
			from v_PersonPhone PP with(nolock)
			where PP.Person_id = :Person_id
			order by PP.PersonPhone_insDT desc
		";
		$PersonPhone = $this->getFirstRowFromQuery($query, $params, true);
		if ($PersonPhone === false) {
			return $this->createError('','Ошибка при получении последний периодики по номеру телефона');
		}

		$this->beginTransaction();

		//Если номер изменился, то добавляется периодика
		if (!empty($PersonPhone) && (empty($data['PersonPhone_Phone']) || $PersonPhone['PersonPhone_Phone'] == $data['PersonPhone_Phone'])) {
			$data['PersonPhone_id'] = $PersonPhone['PersonPhone_id'];
		} else {
			$params = array(
				'PersonPhone_Phone' => $data['PersonPhone_Phone'],
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				declare
					@PersonPhone_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_PersonPhone_ins
					@PersonPhone_id = @PersonPhone_id output,
					@PersonPhone_Phone = :PersonPhone_Phone,
					@Person_id = :Person_id,
					@Server_id = :Server_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @PersonPhone_id as PersonPhone_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при сохранении номера телефона');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
			$data['PersonPhone_id'] = $resp[0]['PersonPhone_id'];
		}

		$params = array(
			'Person_id' => $data['Person_id'],
			'PersonPhone_id' => $data['PersonPhone_id'],
			'MedStaffFact_id' => !empty($data['MedStaffFact_id'])?$data['MedStaffFact_id']:null,
			'PersonPhoneStatus_id' => $data['PersonPhoneStatus_id'],
			'PersonPhoneFailCause_id' => !empty($data['PersonPhoneFailCause_id'])?$data['PersonPhoneFailCause_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@PersonPhoneHist_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonPhoneHist_ins
				@PersonPhoneHist_id = @PersonPhoneHist_id output,
				@Person_id = :Person_id,
				@PersonPhone_id = :PersonPhone_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@PersonPhoneStatus_id = :PersonPhoneStatus_id,
				@PersonPhoneFailCause_id = :PersonPhoneFailCause_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonPhoneHist_id as PersonPhoneHist_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении статуса номера телефона');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Получение списка согласий пациента для ЭМК
	 */
	function loadPersonLpuInfoPanel($data) {
		return $this->queryResult("
			declare @curDate datetime = dbo.tzGetDate();
			select 
				'PersonLpuInfo' as PersonLpuInfoType,
				PersonLpuInfo_id,
				PersonLpuInfo_IsAgree,
				PersonLpuInfo_setDate,
				Lpu_Nick
			from (	select top 1
						PersonLpuInfo_id,
						PersonLpuInfo_IsAgree,
						convert(varchar(10), PersonLpuInfo_setDT, 104) as PersonLpuInfo_setDate,
						Lpu_Nick
					from	
						v_PersonLpuInfo pli (nolock) 
						left join v_Lpu l (nolock) on l.Lpu_id = pli.Lpu_id
					where
						Person_id = :Person_id and pli.Lpu_id = :Lpu_id 
					order by PersonLpuInfo_id desc) as PLI_Last
			union all
			--названия полей в результате запроса будут из первой части запроса (до union all), они используются при загрузке грида в PersonLpuInfoPanel.js.
			--различие типа добровольного информированного согласия в PersonLpuInfoPanel.js происходит по PersonLpuInfoType
			select  
				'ReceptElectronic' as PersonLpuInfoType,
				ReceptElectronic_id,
				ReceptElectronic_IsAgree,
				ReceptElectronic_setDate,
				Lpu_Nick
			from (	select top 1
						ReceptElectronic_id,
						case when ReceptElectronic_endDT is null or cast(ReceptElectronic_endDT as datetime) > @curDate then 2 else 1 end as ReceptElectronic_IsAgree,
						case when ReceptElectronic_endDT is null then convert(varchar(10), ReceptElectronic_begDT, 104) else convert(varchar(10), ReceptElectronic_begDT, 104) + ' - ' + convert(varchar(10), ReceptElectronic_endDT, 104) end as ReceptElectronic_setDate,
						Lpu_Nick
					from
						v_ReceptElectronic re (nolock)
						left join v_Lpu l (nolock) on l.Lpu_id = re.Lpu_id
					where
						Person_id = :Person_id and re.Lpu_id = :Lpu_id 
					order by ReceptElectronic_id desc) as RE_Last
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Метод для API. Получение списка согласий пациента для ЭМК
	 */
	function loadPersonLpuInfoPanelForAPI($data) {

		$filter = " PLI.Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " PLI.Person_id in ({$data['person_in']}) ";
			$select = " ,PLI.Person_id ";
		}

		return $this->queryResult("
			select
				PLI.PersonLpuInfo_id,
				convert(varchar(10), PLI.PersonLpuInfo_setDT, 104) as PersonLpuInfo_setDate,
				PLI.PersonLpuInfo_IsAgree,
				YN.YesNo_Name as PersonLpuInfo_IsAgreeText,
				'На обработку персональных данных' as PersonLpuInfo_Type
				{$select}
			from
				v_PersonLpuInfo PLI(nolock)
				left join v_YesNo YN with(nolock) on YN.YesNo_id = PLI.PersonLpuInfo_IsAgree
			where {$filter}
		", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * Проверяет есть ли на данного неизвестного человека
	 * Талон вызова СМП
	 * Карта закрытия вызова СМП
	 * Бирка
	 * Событие(смотрим в таблице Evn - сможем отследить направления, ТАП, КВС и т.д.)
	 */
	public function checkToDelPerson($data){
		if(empty($data['Person_id'])) return false;
		$where[] = 'P.Person_id = :Person_id';

		if(!empty($data['CmpCallCard_id'])){
			$where[] = 'C.CmpCallCard_id != :CmpCallCard_id';
		}
		if(!empty($data['Evn_id'])){
			$where[] = 'E.Evn_id != :Evn_id';
		}
		if(!empty($data['TimetableGraf_id'])){
			$where[] = 'TG.TimetableGraf_id != :TimetableGraf_id';
		}

		return $this->getFirstResultFromQuery("
			select top 1
				COALESCE(E.Person_id, C.Person_id, TG.Person_id, TS.Person_id, TP.Person_id, HV.Person_id) as Person_id
			from
				v_Person P (nolock)
				left join v_Evn E (nolock) on P.Person_id = E.Person_id
				left join v_CmpCallCard C (nolock) on P.Person_id = C.Person_id
				left join v_TimeTableGraf_lite TG (nolock) on P.Person_id = TG.Person_id
				left join v_TimeTableStac_lite TS (nolock) on P.Person_id = TS.Person_id
				left join v_TimeTablePar TP (nolock) on P.Person_id = TP.Person_id
				left join v_HomeVisit HV (nolock) on P.Person_id = HV.Person_id
			where
				".implode(' and ', $where)."
		", $data, true);
	}


	/**
	 * По названию должности получаем идентификатор должности, если такой должности не существует, то создаем новую запись и возвращаем ее идентификатор
	 *
	 * @param $post_new - строка
	 *
	 * @param array $data
	 * $data['Server_id']
	 * $data['pmUser_id']
	 *
	 * @return null или идентификатор должности
	 */
	static public function getPostIdFromPostName($post_new, $data = array()){
		$CI = &get_instance();

		$Post_id = null;

		if (is_numeric($post_new)) {

			$numPostID = 1;

			$sql = "
					select
						Post_id
					from
						v_Post with (nolock)
					where
						Post_id = ?
				";



			$result = $CI->db->query($sql, array($post_new));
		} else {

			$sql = "
					select
						Post_id
					from
						v_Post with (nolock)
					where
						Post_Name like ? and Server_id = ?
				";
			$result = $CI->db->query($sql, array($post_new, $data['Server_id']));
		}

		if (is_object($result)) {

			$sel = $result->result('array');

			if (isset($sel[0])) {

				if ($sel[0]['Post_id'] > 0){
					$Post_id = $sel[0]['Post_id'];
				}

			} else if (isset($numPostID)) {
				$Post_id = null;

			} else {

				$sql = "
						declare @Psto_id bigint
						exec p_Post_ins
							@Post_Name = ?,
							@pmUser_id = ?,
							@Server_id = ?,
							@Post_id=@Psto_id output
						select @Psto_id as Post_id;
					";

				$result = $CI->db->query($sql, array($post_new, $data['pmUser_id'], $data['Server_id']));

				if (is_object($result)) {

					$sel = $result->result('array');

					if ($sel[0]['Post_id'] > 0){
						$Post_id = $sel[0]['Post_id'];
					}

				}
			}
		}

		return $Post_id;
	}


	/**
	 * Получаем текущие значения полей блока "Место работы"
	 *
	 * @param $Person_id
	 * @return array
	 */
	static public function getCurrentPersonJob($Person_id){
		$CI = &get_instance();

		$Org_id = null;
		$Post_id = null;
		$OrgUnion_id = null;

		$query = "
				select top 1
					pjob.Org_id,
					pjob.OrgUnion_id,
					pjob.Post_id
				from 
					v_PersonState vper with (nolock)
					left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
				where
					vper.Person_id = :Person_id
			";

		$result = $CI->db->query($query, array(
			'Person_id' => $Person_id
		));

		if (is_object($result)) {
			$dataPersonState = $result->result('array');

			$Org_id = isset($dataPersonState[0]['Org_id'])?$dataPersonState[0]['Org_id']:null;
			$Post_id = isset($dataPersonState[0]['Post_id'])?$dataPersonState[0]['Post_id']:null;
			$OrgUnion_id = isset($dataPersonState[0]['OrgUnion_id'])?$dataPersonState[0]['OrgUnion_id']:null;
		}

		return array(
			'Org_id' => $Org_id,
			'Post_id' => $Post_id,
			'OrgUnion_id' => $OrgUnion_id,
		);
	}


	/**
	 * Обновляем "Организацию"
	 *
	 * @param $Person_id
	 * @param $new_Org_id
	 * @param $data
	 * @return bool
	 */
	static public function update_Org_id($Person_id, $new_Org_id, $data){

		if( ! is_numeric($Person_id) || empty($Person_id)){
			return false;
		}

		if( ! is_numeric($new_Org_id) || empty($new_Org_id)){
			return false;
		}

		if( ! is_array($data) || empty($data)){
			return false;
		}

		if( ! isset($data['Server_id']) || empty($data['Server_id'])){
			return false;
		}

		if( ! isset($data['pmUser_id']) || empty($data['pmUser_id'])){
			return false;
		}


		self::update_PersonJob(array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $Person_id,
			'Org_id' => $new_Org_id,
			'pmUser_id' => $data['pmUser_id']
		));

		return true;
	}

	/**
	 * Обновляем "Должность"
	 *
	 * @param $Person_id
	 * @param $new_Post_id
	 * @param $data
	 * @return bool
	 */
	static public function update_Post_id($Person_id, $new_Post_id, $data){

		if( ! is_numeric($Person_id) || empty($Person_id)){
			return false;
		}

		if( ! is_numeric($new_Post_id) || empty($new_Post_id)){
			return false;
		}

		if( ! is_array($data) || empty($data)){
			return false;
		}

		if( ! isset($data['Server_id']) || empty($data['Server_id'])){
			return false;
		}

		if( ! isset($data['pmUser_id']) || empty($data['pmUser_id'])){
			return false;
		}


		self::update_PersonJob(array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $Person_id,
			'Post_id' => $new_Post_id,
			'pmUser_id' => $data['pmUser_id']
		));


		return true;
	}


	/**
	 * Обновляем данные блока "Место работы": поля "Организация" (Org_id), "Должность" (Post_id) и "Подразделение" (OrgUnion_id)
	 *
	 * @param array $data
	 *
	 * $data['Person_id']
	 * $data['Post_id']
	 * $data['Server_id']
	 * $data['pmUser_id']
	 * $data['Org_id']
	 *
	 * @return bool
	 */
	static public function update_PersonJob($data = array()){

		$CI = &get_instance();


		if( ! isset($data['Org_id']) && ! isset($data['OrgUnion_id']) && ! isset($data['Post_id'])){
			return false;
		}

		$params = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);


		if(isset($data['Org_id']) && ! empty($data['Org_id'])){
			$params['Org_id'] = $data['Org_id'];
		}

		if(isset($data['OrgUnion_id']) && ! empty($data['OrgUnion_id'])){
			$params['OrgUnion_id'] = $data['OrgUnion_id'];
		}

		if(isset($data['Post_id']) && ! empty($data['Post_id'])){
			$params['Post_id'] = $data['Post_id'];
		}

		$CI->db->query("
			declare @ErrCode int
			declare @ErrMsg varchar(400)

			exec p_PersonJob_ins
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				".((isset($params['Org_id']) && ! empty($params['Org_id']))? "@Org_id = :Org_id,":"")."
				".((isset($params['OrgUnion_id']) && ! empty($params['OrgUnion_id']))? "@OrgUnion_id = :OrgUnion_id,":"")."
				".((isset($params['Post_id']) && ! empty($params['Post_id']))? "@Post_id = :Post_id,":"")."
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @ErrMsg as ErrMsg
		", $params);


		return true;
	}

	/**
	 * @param $paramType
	 * @param $data
	 * @return array
	 * Поиск пациента по полису/документам, удостоверяющим личность/СНИЛС/ФИО
	 */
	public function findPersonByParams($paramType, $data){
		$join = '';
		switch($paramType){
			case 'Polis':
				if(empty($data['Polis_EdNum']) && empty($data['Polis_Num'])){
					return false;
				}

				$join .= ' left join Polis pls with (nolock) on pls.Polis_id = ps.Polis_id';
				$join .= ' left join OrgSmo os with (nolock) on pls.OrgSmo_id = os.OrgSmo_id';
				$join .= ' left join Org o with (nolock) on os.Org_id = o.Org_id';

				if(!empty($data['Polis_EdNum'])){
					$filters[] = 'ps.Person_edNum = :Polis_EdNum';
				}
				if(!empty($data['Polis_Num'])){
					$filters[] = 'pls.Polis_Num = :Polis_Num';
				}
				if(!empty($data['Polis_Ser'])){
					$filters[] = 'pls.Polis_Ser = :Polis_Ser';
				}
				if(!empty($data['OrgSmo_id'])){
					$filters[] = 'os.OrgSmo_id = :OrgSmo_id';
				}
				if(!empty($data['Org_Code'])){
					$filters[] = 'o.Org_Code = :Org_Code';
				}
				if(!empty($data['Orgsmo_f002smocod'])){
					$filters[] = 'os.Orgsmo_f002smocod = :Orgsmo_f002smocod';
				}
				break;

			case 'Document':
				if(empty($data['Document_Num'])){
					return false;
				}

				$join .= ' left join v_Document d with (nolock) on d.Document_id = ps.Document_id';
				$join .= ' left join v_DocumentType dt with (nolock) on dt.DocumentType_id = d.DocumentType_id';
				$join .= ' left join v_OrgDep od with (nolock) on od.OrgDep_id = d.OrgDep_id';

				if(!empty($data['Document_Ser'])){
					$filters[] = 'd.Document_Ser = :Document_Ser';
				}
				if(!empty($data['Document_Num'])){
					$filters[] = 'd.Document_Num = :Document_Num';
				}
				if(!empty($data['DocumentType_Code'])){
					$filters[] = 'dt.DocumentType_Code = :DocumentType_Code';
				}
				if(!empty($data['OrgDep_id'])){
					$filters[] = 'od.OrgDep_id = :OrgDep_id';
				}
				if(!empty($data['Org_Code'])){
					$filters[] = 'od.Org_Code = :Org_Code';
				}
				break;

			case 'Snils':
				if(empty($data['Person_Snils'])){
					return false;
				}
				$filters[] = 'ps.Person_Snils = :Person_Snils';
				break;

			Case 'Fio':
				if(!empty($data['Person_SurName'])){
					$filters[] = 'ps.Person_SurName = :Person_SurName';
				}
				if(!empty($data['Person_FirName'])){
					$filters[] = 'ps.Person_FirName = :Person_FirName';
				}
				if(!empty($data['Person_SecName'])){
					$filters[] = 'ps.Person_SecName = :Person_SecName';
				}
				if(!empty($data['Person_BirthDay'])){
					$filters[] = 'ps.Person_BirthDay = :Person_BirthDay';
					$data['Person_BirthDay'] = new DateTime($data['Person_BirthDay']);
					$data['Person_BirthDay'] = $data['Person_BirthDay']->format('Y-m-d H:i:s');

				}
				if(!empty($data['Sex_id'])){
					$filters[] = 'ps.Sex_id = :Sex_id';
				}
				break;

			default:
				return false;
		}

		$sql = "
			SELECT top 10 ps.Person_id
			FROM v_PersonState ps with (nolock)
			{$join}
			WHERE " . implode(" and ", $filters);

		return $this->queryResult($sql, $data);

	}

	/**
	 * Обновляем атрибут для апи (пока только полиса данные)
	 */
	function savePersonAttributeForApi($data) {

		if ($data['EvnType'] === 'Polis') {

			if ($data['apiAction'] === 'create') {
				$data['PersonEvn_id'] = $this->dbmodel->getFirstResultFromQuery("
					select top 1 PersonEvn_id from v_PersonState PS with(nolock) where PS.Person_id = :Person_id
				", $data);

				if (empty($data['PersonEvn_id'])) return array('Error_Msg' => 'Пациент не найден в системе');
				
				//сделаем дополнительную проверку пересечения полисов т.к. в p_PersonPolis_ins идет проверка 
				// на уществование полисов с датой начала больше, чем передаваемая, при этом учитывается дата вставки (подразумевается что дата вставки и начала равны)
				// а в функции checkPolisIntersection() учитывается дата начала
				// таким образом процедура p_PersonPolis_ins создаст полис, но запись в PersonState не будет добавлена если существует полис с датой вставки больше, чем передаваемая
				$result = $this->dbmodel->getFirstRowFromQuery("
					select polis_id from v_personpolis where Person_id = :Person_id and PersonPolis_insDate >= :Polis_begDate
				", $data);
				if(!empty($result['polis_id'])){
					return array('Error_Msg' => 'Период полиса Polis_id='.$result['polis_id'].' пересекается с передаваемой датой Polis_begDate');
				}
				
				if ($data['PolisType_id'] == 1 && !empty($data['OrgSmo_id'])) {
					$region = $this->dbmodel->getFirstResultFromQuery("
						select top 1 KLRgn_id from v_OrgSMO PS (nolock) where OrgSmo_id = :OrgSmo_id
					", $data);
					$regex = $region == $this->getRegionNumber() ? '/^\d+$/' : '/^[\d\.\/]+$/';
					if (!preg_match($regex, $data['Polis_Num'])) {
						return array('Error_Msg' => 'Неверный формат поля Polis_Num');
					}
				}
				
				if ($data['PolisType_id'] == 3) {
					if (empty($data['Polis_Ser']) && strlen($data['Polis_Num']) == 9) {
						$data['Polis_Ser'] = substr($data['Polis_Num'], 0, 3);
						$data['Polis_Num'] = substr($data['Polis_Num'], 3, 6);
					}
					
					if (!empty($data['Polis_Ser']) && strlen($data['Polis_Ser']) != 3) {
						return array('Error_Msg' => 'Неверный формат данных в поле Polis_Ser для переданного PolisType');
					}
					
					if (!empty($data['Polis_Ser']) && !empty($data['Polis_Num']) && strlen($data['Polis_Num']) != 6) {
						return array('Error_Msg' => 'Неверный формат поля Polis_Ser, Polis_Num');
					}
				}
			}

			if ($data['apiAction'] === 'update') {
				$resp = $this->getPolisForAPI($data);

				if (!is_array($resp) || !count($resp) || (!empty($data['Person_id']) && $data['Person_id'] != $resp[0]['Person_id'])) return array('Error_Msg' => 'Нет данных о полисе пациента с указанным Polis_id');

				$polis = $resp[0];
				unset($data['Server_id']); // берётся из периодики

				$data = array_replace($polis, $data);

				foreach($data as $key => $value) {
					if (empty($data[$key])) {
						if (isset($polis[$key])) {
							$data[$key] = $polis[$key];
						} else {
							unset($data[$key]);
						}
					}
				}

				$data['PersonEvn_id'] = $data['PersonPolis_id'];
			}

			if (!empty($data['PolisType_id']) && $data['PolisType_id'] == 4 && empty($data['Federal_Num'])) {
				$data['Federal_Num'] = (!empty($data['Polis_EdNum']) ? $data['Polis_EdNum'] : $data['Polis_Num']);
			}

			$data['OMSSprTerr_id'] = !empty($data['OMSSprTerr_id']) ? $data['OMSSprTerr_id'] : null;
			$data['OrgSMO_id'] = !empty($data['OrgSMO_id']) ? $data['OrgSMO_id'] : null;
			$data['EvnType'] = 'Polis';
		}

		$data['cancelCheckEvn'] = true;

		try{

			$this->dbmodel->exceptionOnValidation = true;
			$resp = $this->dbmodel->editPersonEvnAttributeNew($data);

			if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$this->dbmodel->exceptionOnValidation = false;

		} catch(Exception $e) {

			$this->dbmodel->exceptionOnValidation = false;
			return (array('error_msg' => $e->getMessage()));
		}

		$response = $this->dbmodel->getSaveResponse();
		return $response;
	}


	/**
	 * Получить записей человека
	 */
	function getPersonRecords($data) {

		$object = $data['tt'];
		$select = ""; $join = "";

		switch ($object)  {

			case "TimetableGraf":

				$select = ",
                    tt.TimetableGraf_IsModerated as is_moderated,
                    rtrim(msf.Person_Surname)+' '+rtrim(msf.Person_Firname)+' '+rtrim(msf.Person_Secname) as MedPersonal_FIO,
                    lsp.ProfileSpec_Name
                ";

				$join = "
                    left join v_Medstafffact msf (nolock) on tt.MedStaffFact_id = msf.MedStaffFact_id
                    left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
                    left join v_LpuSectionProfile lsp (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
                    left join v_LpuUnit_ER lu(nolock) on lu.LpuUnit_id = ls.LpuUnit_id
                    left join v_Lpu lpu (nolock) on lpu.Lpu_id = lu.Lpu_id
                ";

				break;
			case "TimetableMedService":

				$select = ",
                    mst.MedServiceType_Name,
                    uc.UslugaComplex_Nick
                ";

				$join = "
                    left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = tt.UslugaComplexMedService_id
                    left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
                    left join v_MedService ms (nolock) on ms.MedService_id = ucms.MedService_id
                    left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
                    left join v_Lpu lpu (nolock) on lpu.Lpu_id = ms.Lpu_id
                    left join v_LpuUnit lu (nolock) on ms.LpuUnit_id = lu.LpuUnit_id
                    left join v_LpuBuilding lpub (nolock) on lpub.LpuBuilding_id = ms.LpuBuilding_id
                ";

				break;
			case "TimetableResource":
				break;
		}

		$query = "
			select
					tt.{$object}_id as Timetable_id,
					datediff(minute,tt.{$object}_begTime, dbo.tzGetDate()) as datediff,
                    convert(varchar, tt.{$object}_begTime,104) as Timetable_Date,
                    left(convert(varchar, tt.{$object}_begTime,108),5) as Timetable_Time,
                    lpu.Lpu_id,
                    lpu.Lpu_Nick,
                    lu.LpuUnit_Name,
                    rtrim(str.KLStreet_Name)+' '+rtrim(a.Address_House) as LpuUnit_Address,
                    p.Person_id
	                {$select}
                from v_{$object} tt with (nolock)
                left join v_Person_ER p with (nolock) on tt.Person_id = p.Person_id
                {$join}
                left join v_Address a (nolock) on a.Address_id = lu.Address_id
                left join v_KLStreet str (nolock) on str.KLStreet_id = a.KLStreet_id
				left join v_EvnDirection_all (nolock) as ed on ed.EvnDirection_id = tt.EvnDirection_id
				left join v_ElectronicTalon (nolock) as et on et.EvnDirection_id = ed.EvnDirection_id
                where
                    (1=1)
                    and tt.Person_id = :Person_id
                    and tt.{$object}_begTime is not null
                    and isnull(lpu.Lpu_IsTest, 1) = 1
                order by tt.{$object}_begTime desc
		";

		$result = $this->queryResult($query, $data);
		$response = array('future_records' => array(), 'complete_records' => array());

		if (!empty($result)) {

			foreach ($result as $record) {

				$diff = $record['datediff'];
				unset($record['datediff']);

				if ($diff >= 0) {
					$response['complete_records'][] = $record;
				} else $response['future_records'][] = $record;
			}
		}
		return $response;
	}
	
	/**
	 * Проверка наличия ЗНО в последнем случае
	 */
	function checkEvnZNO_last($data) {
		$params = array('Person_id' => $data['Person_id']);
		$filter = "";
		$params['Evn_id'] = $data['Evn_id'];
		$sql = "
			with evnz (iszno, Diag_spid, evn_date, evn_id) as (
			select
				EVPL.EvnVizitPL_IsZNO as iszno,
				EVPL.Diag_spid,
				convert(varchar(10), EVPL.EvnVizitPL_setDT, 120) as evn_date,
				EVPL.EvnVizitPL_id as evn_id
			from v_EvnVizitPL EVPL (nolock) where EVPL.EvnClass_id = 11 and EVPL.Person_id = :Person_id".($data['object']=='EvnVizitPL' ? " AND EVPL.EvnVizitPL_id != ISNULL(:Evn_id, 0)":"")."
			union
			select 
				EPS.EvnPS_IsZNO as iszno,
				EPS.Diag_spid,
				convert(varchar(10), EPS.EvnPS_setDT, 120) as evn_date,
				EPS.EvnPS_id as evn_id
			from v_EvnPS EPS (nolock) where EPS.Person_id = :Person_id".(($data['object']=='EvnPS' or $data['object']=='EvnSection') ? " AND EPS.EvnPS_id != ISNULL(:Evn_id, 0)": "")."
			union
			select
				STOM.EvnDiagPLStom_IsZNO as iszno,
				STOM.Diag_spid,
				convert(varchar(10), STOM.EvnDiagPLStom_setDT, 120) as evn_date,
				STOM.EvnDiagPLStom_id as evn_id
			from v_EvnDiagPLStom STOM (nolock) where STOM.Person_id = :Person_id".($data['object']=='EvnDiagPLStom' ? " AND STOM.EvnDiagPLStom_pid != ISNULL(:Evn_id, 0)":"")."
			union
			select
				ES.EvnSection_IsZNO as iszno,
				ES.Diag_spid,
				convert(varchar(10), ES.EvnSection_setDT, 120) as evn_date,
				ES.EvnSection_id as evn_id
			from v_EvnSection ES (nolock) where ES.Person_id = :Person_id".($data['object']=='EvnSection' ? " AND ES.EvnSection_pid != ISNULL(:Evn_id, 0)":"")."
			)
			select top 1 evnz.iszno, evnz.Diag_spid, D.Diag_Code, D.Diag_Name
			from evnz 
				left join v_Diag D (nolock) on D.Diag_id = evnz.Diag_spid
			order by evnz.evn_date DESC, iszno DESC, evnz.evn_id DESC
		";
		//echo getDebugSQL($sql, $params);exit;
		$res = $this->getFirstRowFromQuery($sql, $params);
		return $res;
	}
	
	/**
	 * Изменение признака подозрения на ЗНО
	 */
	function changeEvnZNO($data) {
		$params = array('Evn_id' => $data['Evn_id'], 'isZNO' => $data['isZNO']);
		$zno_remove = "";
		$object = $data['object'];
		
		switch($object) {
			case 'EvnPS':
			case 'EvnVizitPL':
			case 'EvnSection':
			case 'EvnDiagPLStom':
				if($params['isZNO']=='2') //устанавливается флаг признака подозрения на ЗНО
					$zno_remove = ", {$object}_IsZNORemove = 1";//снимаем "признак снятия признака подозрения на ЗНО"
				else
					$zno_remove = ", {$object}_IsZNORemove = 2";
				$sql = "
					UPDATE {$object} with(rowlock) SET {$object}_IsZNO = :isZNO $zno_remove
					WHERE {$object}_id = :Evn_id
				";
				//echo getDebugSQL($sql, $params);exit;
				$result = $this->db->query($sql, $params);
				break;
			default:
				return array(array(
					'success' => true,
					'ErrorMsg' => 'Неверный параметр "объект"'
				));
		}
		return array(array(
			'success' => true,
			'ErrorMsg' => ''
		));
	}
	
	/**
	 * Получить дату взятия биопсии из последнего случая с признаком ЗНО
	 */
	function getEvnBiopsyDate($data) {
		$sql = "
			with evnz (evn_id, evn_date, sortdate) as (
				select EVPL.EvnVizitPL_id as evn_id,
					convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as evn_date,
					EVPL.EvnVizitPL_setDate as sortdate
				from v_EvnVizitPL EVPL where EVPL.Person_id = :Person_id and EVPL.EvnVizitPL_IsZNO = 2
				union
				select EPS.EvnPS_id as evn_id,
					convert(varchar(10), EPS.EvnPS_setDate, 104) as evn_date,
					EPS.EvnPS_setDate as sortdate
				from v_EvnPS EPS (nolock) where EPS.Person_id = :Person_id and EPS.EvnPS_IsZNO = 2
				union
				select STOM.EvnDiagPLStom_id as evn_id,
					convert(varchar(10), STOM.EvnDiagPLStom_setDate, 104) as evn_date,
					STOM.EvnDiagPLStom_setDate as sortdate
				from v_EvnDiagPLStom STOM (nolock) where STOM.Person_id = :Person_id and STOM.EvnDiagPLStom_IsZNO = 2
				union
				select ES.EvnSection_id as evn_id,
					convert(varchar(10), ES.EvnSection_setDate, 104) as evn_date,
					ES.EvnSection_setDate as sortdate
				from v_EvnSection ES where ES.Person_id = :Person_id and ES.EvnSection_IsZNO = 2
			)
			select top 1 convert(varchar(10), EDH.EvnDirectionHistologic_didDate, 104) as BiopsyDate
			from
				v_EvnDirectionHistologic EDH (nolock)
			inner join evnz on evnz.evn_id = EDH.EvnDirectionHistologic_pid
			order by evnz.sortdate DESC
		";
		//echo getDebugSql($sql, array('Person_id'=>$data['Person_id'])); exit;
		$res = $this->getFirstResultFromQuery($sql, array('Person_id'=>$data['Person_id']));
		return $res;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getPersonIdByPersonEvnId($data) {
		$params = array(
			'PersonEvn_id' => $data['PersonEvn_id']
		);
		$query = "
			select top 1 Person_id
			from v_PersonEvn with(nolock)
			where PersonEvn_id = :PersonEvn_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получаем все необходимые параметры по человеку для определения референсных значений
	 */
	function getPersonDataForRefValues($data)
	{
		$person = array(
			'Sex_id' => null,
			'Person_AgeYear' => 0,
			'Person_AgeMonth' => 0,
			'Person_AgeDay' => 0,
			'Person_AgeWeek' => 0,
			'TimeOfDay' => 0,
			'Pregnancy_Value' => 0,
			'HormonalPhaseType_id' => null
		);

		// получаем все необходимые параметры по человеку + ограничения из назначений
		$query = "
			select top 1
				Sex_id,
				datediff(year, Person_BirthDay, :EvnLabSample_setDT) as Person_AgeYear,
				datediff(month, Person_BirthDay, :EvnLabSample_setDT) as Person_AgeMonth,
				datediff(day, Person_BirthDay, :EvnLabSample_setDT) as Person_AgeDay,
				datediff(week, Person_BirthDay, :EvnLabSample_setDT) as Person_AgeWeek,
				datepart(hour, :EvnLabSample_setDT) as TimeOfDay,
				preg.EvnPrescrLimit_ValuesNum as Pregnancy_Value,
				phaze.EvnPrescrLimit_Values as HormonalPhaseType_id
			from
				v_PersonState (nolock)
				outer apply(
					select top 1
						EvnPrescrLimit_ValuesNum
					from
						v_EvnPrescrLimit epl (nolock)
						inner join v_LimitType lt (nolock) on lt.LimitType_id = epl.LimitType_id
					where
						lt.LimitType_SysNick = 'PregnancyUnitType'
						and epl.EvnPrescr_id = :EvnPrescr_id
				) preg
				outer apply(
					select top 1
						EvnPrescrLimit_Values
					from
						v_EvnPrescrLimit epl (nolock)
						inner join v_LimitType lt (nolock) on lt.LimitType_id = epl.LimitType_id
					where
						lt.LimitType_SysNick = 'HormonalPhaseType'
						and epl.EvnPrescr_id = :EvnPrescr_id
				) phaze
			where
				Person_id = :Person_id;
		";
		$result_person = $this->db->query($query, $data);
		if (is_object($result_person)) {
			$resp_person = $result_person->result('array');
			if (count($resp_person) > 0) {
				$person['Sex_id'] = $resp_person[0]['Sex_id'];
				$person['Person_AgeYear'] = $resp_person[0]['Person_AgeYear'];
				$person['Person_AgeMonth'] = $resp_person[0]['Person_AgeMonth'];
				$person['Person_AgeDay'] = $resp_person[0]['Person_AgeDay'];
				$person['Person_AgeWeek'] = $resp_person[0]['Person_AgeWeek'];
				$person['TimeOfDay'] = $resp_person[0]['TimeOfDay'];
				$person['Pregnancy_Value'] = $resp_person[0]['Pregnancy_Value'];
				$person['HormonalPhaseType_id'] = $resp_person[0]['HormonalPhaseType_id'];
			}
		}

		return $person;
	}

	/**
	 * Определение сервера по PersonEvn_id
	 */
	function serverByPersonEvn($data) {
		$res = $this->getFirstResultFromQuery("
			SELECT
				Server_id
			FROM
				v_PersonEvn
			WHERE
				PersonEvn_id = :PersonEvn_id
		", $data);

		return [
			'Server_id' => $res
		];
	}

	/**
	 * Получение данных о человеке для InnovaSysService_model
	 */
	function getPersonForInnova($data) {
		$query = "
			select top 1
				ps.Person_id as Code,
				pac.PersonAmbulatCard_Num as Card,
				ps.Person_FirName as FirstName,
				ps.Person_SecName as MiddleName,
				ps.Person_SurName as LastName,
				year(ps.Person_Birthday) as BirthYear,
				month(ps.Person_Birthday) as BirthMonth,
				day(ps.Person_Birthday) as BirthDay,
				convert(varchar(33), ps.Person_Birthday, 126) as BirthDate,
				isnull(ps.Sex_id, 0) as Sex,
				isnull(case when ps.Polis_Ser is not null then ps.Polis_Ser else '' end, '') as PolicySeries,
				isnull(case when ps.Polis_Num is not null then ps.Polis_Num else '' end, '') as PolicyNumber,
				pa.Address_Address as pAddress,
				pa.Address_House as Building,
				pa.Address_Flat as Flat
			from
				v_PersonState ps with (nolock)
				left join v_Address pa with (nolock) on ps.PAddress_id = pa.Address_id
				left join v_PersonAmbulatCard pac with (nolock) on ps.Person_id = pac.Person_id
			where
				ps.Person_id = :Person_id
			order by pac.PersonAmbulatCard_id desc
			";

		return $this->queryResult($query, $data);
	}
	function getPersonEvn($data) {
		return $this->getFirstResultFromQuery("
		select top 1
			PersonEvn_id 
		from 
			v_PersonEvn 
		where 
			Person_id = :Person_id and 
			Server_id = :Server_id
		order by PersonEvn_id desc",
			array('Person_id' => $data['Person_id'], 'Server_id'=>$data['Server_id']));
	}

	/**
	 * Проверка наличие согласия на рецепт в электрнной форме для формы выписки льготных рецептов
	 */
	function isReceptElectronicStatus($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			declare @curDate datetime = dbo.tzGetDate();
			select top 1
						ReceptElectronic_id,
						case when ReceptElectronic_endDT is null or cast(ReceptElectronic_endDT as datetime) > @curDate then 2 else 1 end as ReceptElectronic_IsAgree
					from
						v_ReceptElectronic re (nolock)
						left join v_Lpu l (nolock) on l.Lpu_id = re.Lpu_id
					where
						Person_id = :Person_id and re.Lpu_id = :Lpu_id 
					order by ReceptElectronic_id desc
		";

		$response = $this->queryResult($query, $params);

		if (empty($response)){
			return array(array('ReceptElectronic_IsAgree' => 0));				//нету информации о согласии на электронный рецепт
		}
		return $response;
	}
	
	/*
	 * получаем последнюю периодику по полису
	 */
	function getLastPeriodicalsByPolicy($data){
		$resultArr = array();
		if(empty($data['Person_id'])) return $resultArr;
		
		$sql = "
			select
				top 1 PersonPolis_id,
				Server_id,
				Polis_id,
				Polis_endDate
			from
				v_PersonPolis with (nolock)
			where
				Person_id = :Person_id
			order by
				PersonPolis_insDT desc,
				PersonPolis_TimeStamp desc
		";
		//echo getDebugSQL($sql, array('Person_id' => $data['Person_id'])); die();
		$res = $this->db->query($sql, array('Person_id' => $data['Person_id']));
		if (is_object($res)) {
			$sel = $res->result('array');
			if (count($sel) > 0) $resultArr = $sel[0];
		}
		return $resultArr;
	}

	/**
	 * Проверка на то, что возраст пацаиента меньше заданного
	 */
	function checkPersonAgeIsLess($data) {
		return $this->queryResult("
			SELECT TOP 1
			ps.Person_id
			FROM v_PersonState ps (nolock)
			WHERE Person_id = :Person_id
				AND dbo.Age(Person_BirthDay, dbo.tzGetDate()) < :age
		", $data);
	}

	/**
	 * Проверка, что дата смерти не больше даты рождения
	*/
	function checkPersonDeathDate($data) {
		return $this->queryResult("
			select
				Person_id
			from v_PersonState (nolock)
			where Person_id = :Person_id
				and ISNULL(Person_BirthDay, CAST(:DeathDate as DATETIME)) <= CAST(:DeathDate as DATETIME)
		", $data);
	}

	function deletePersonHeight($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonHeight_del
				@PersonHeight_id = :PersonHeight_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'PersonHeight_id' => $data['PersonHeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных об измерениях роста человека
	 */
	function getPersonHeightViewData($data) {
		$query = "
			select
				PH.Person_id,
				0 as Children_Count,
				PH.PersonHeight_id,
				PH.PersonHeight_id as Anthropometry_id,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate,
				cast(PH.PersonHeight_Height as float) as PersonHeight_Height,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				ISNULL(IsAbnorm.YesNo_Name, '') as PersonHeight_IsAbnorm,
				ISNULL(HAT.HeightAbnormType_Name, '') as HeightAbnormType_Name,
				PH.pmUser_insID,
				ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_PersonHeight PH with (nolock)
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
				left join YesNo IsAbnorm with (nolock) on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join HeightAbnormType HAT with (nolock) on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(PH.pmUser_updID, PH.pmUser_insID)
			where
				PH.Person_id = :Person_id
			order by
				PH.PersonHeight_setDT
		";

		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id']
		));

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
	function loadPersonHeightEditForm($data) {
		$query = "
			select top 1
				PH.PersonHeight_id,
				PH.Person_id,
				PH.Evn_id,
				PH.Server_id,
				PH.HeightAbnormType_id,
				PH.HeightMeasureType_id,
				PH.PersonHeight_IsAbnorm,
				PH.PersonHeight_Height,
				convert(varchar(10), PH.PersonHeight_setDT, 104) as PersonHeight_setDate
			from
				v_PersonHeight PH with (nolock)
			where (1 = 1)
				and PH.PersonHeight_id = :PersonHeight_id
		";
		$result = $this->db->query($query, array(
			'PersonHeight_id' => $data['PersonHeight_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	function loadAnthropometricData($data) {
		$this->load->model('PersonWeight_model');
		$this->load->model('PersonHeight_model');

		$height_arr = $this->PersonHeight_model->loadPersonHeightGrid($data);
		$weight_arr = $this->PersonWeight_model->loadPersonWeightPanel($data);

		$weightInfo = array();
		$heightInfo = array();
		$dates = array();
		if(!empty($weight_arr)) {
			foreach($weight_arr as $item) {
				$weightInfo[] = array(
					'ObserveDate'=> date('Y-m-d',strtotime($item['PersonWeight_setDate'])),
					'weight_value'=>round($item['PersonWeight_Weight']),
					'height_value'=>null,
					'person_imt' => $item['PersonWeight_Imt']
				);
			}
		}

		if(!empty($height_arr)) {
			foreach ($height_arr as $item) {
				$heightInfo[] = array(
					'ObserveDate' => date('Y-m-d', strtotime($item['PersonHeight_setDate'])),
					'weight_value' => null,
					'height_value' => round($item['PersonHeight_Height']),
					'person_imt' => $item['PersonHeight_Imt']
				);
				//$measures[] = array('Measure_id'=>$i, 'ChartInfo_id'=>$i, );
			}
		}

		$result = array_merge($weightInfo,$heightInfo);

		if(!empty($result)) {
			foreach ($result as $item) { // набираем массив дат в unix формате, чтобы потом вычленить минимальную и максимальную
				if(!empty($item['ObserveDate'])) {
					$dates[] = strtotime($item['ObserveDate']);
				}
			}
		}
		$dataTitle = array();

		if(!empty($heightInfo)) {
			$lastElementHeightArray = array_pop($heightInfo); // для title берём последние данные
		}
		if(!empty($weightInfo)) {
			$lastElementWeightArray = array_pop($weightInfo); // для title берём последние данные
		}

		if(!empty($lastElementWeightArray) && !empty($lastElementHeightArray)) {
			$dataTitle = array(
				'imt' => $lastElementWeightArray['person_imt'],
				'height' => $lastElementHeightArray['height_value'],
				'weight' => $lastElementWeightArray['weight_value'],
			);
		}

		if(!empty($dates)) {
			$minDate = date('d.m.Y',min($dates));
			$maxDate = date('d.m.Y',max($dates));

			$minAndMaxDate = array('minObserveDate'=>$minDate, 'maxObserveDate'=>$maxDate);
		}
		//$result[] = $weight_arr;

//		var_dump(array(
//			'info'=> $info,//замеры
//			'measures'=>$measures, //отдельно данные по показателям к ним
//			'rates'=>array(23), //нормы
//			'totalCount'=>6, //количество замеров
//			'minimax'=>array('minObserveDate'=>'2019-03-05', 'maxObserveDate'=>'2019-04-10'), //временной промежуток на все замеры
//			'result' => $result
//		));
//		die();
//		return $result;
//		$result['data'] = array();
//		$result['data']['info'] = array();
//		$result['data']['measures'] = array();
//		$result['data']['rates'] = array();
//		$result['data'][] = array('totalCount'=>6);
//		$result['data']['minimax'] = array('minObserveDate'=> '123', 'maxObserveDate'=>'321');

//		$info = array(
//			array('ChartInfo_id'=>1, 'ObserveDate'=> '2019-12-12 12:59', 'TimeOfDay_id'=>1, 'Complaint'=>null, 'FeedbackMethod_id'=>null),
//			array('ChartInfo_id'=>2, 'ObserveDate'=> '2019-21-12 15:03', 'TimeOfDay_id'=>2, 'Complaint'=>null, 'FeedbackMethod_id'=>null)
//		);
//		$measures = array(
//			array('Measure_id'=>1, 'Value'=>230, 'ChartInfo_id'=>1, 'RateType_id'=>38),
//			array('Measure_id'=>2, 'Value'=>222, 'ChartInfo_id'=>2, 'RateType_id'=>53),
//		);

//		var_dump($test2);
//		die();

		if(!empty($result)) {
			return array(
				'info'=> $result,//замеры
				'totalCount'=>count($result), //количество замеров
				'minimax'=>$minAndMaxDate, //временной промежуток на все замеры
				'dataTitle' => $dataTitle

			);
		} else {
			return array();
		}
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	function saveAnthropometricMeasures($data) {

//		var_dump($data);
		$this->load->model('PersonHeight_model');
		$this->load->model('PersonWeight_model');
		try {
			$heightResponce = $this->PersonHeight_model->savePersonHeight($data);
			$weightResponce = $this->PersonWeight_model->savePersonWeight($data);
		} catch (Exception $e) {

		}
		$heightResponce = $weightResponce;
		return $heightResponce;
	}

	/**
	 * Получение списка измерений массы пациента для ЭМК
	 */
	function loadPersonHeightPanel($data) {

		$filter = " ph.Person_id = :Person_id ";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " ph.Person_id in ({$data['person_in']}) ";
		}

		return $this->queryResult("
    		select
    			ph.PersonHeight_id,
    			ph.PersonHeight_Height,
    			convert(varchar(10), ph.PersonHeight_setDT, 104) as PersonHeight_setDate,
    			wmt.HeightMeasureType_Name,
    			wat.HeightAbnormType_Name,
    			ph.Person_id
    		from
    			v_PersonHeight ph (nolock)
    			left join v_HeightMeasureType wmt (nolock) on wmt.HeightMeasureType_id = ph.HeightMeasureType_id
    			left join v_HeightAbnormType wat (nolock) on wat.HeightAbnormType_id = ph.HeightAbnormType_id
    		where {$filter}
    	", array(
			'Person_id' => $data['Person_id']
		));
	}

	/**
	 * @param $data
	 * вызов пациента(отправка уведомления)
	 */
	function mSendPersonCallNotify($data) {

		if (!in_array($data['ARMType_id'], array(5,19))) {
			return array('Error_Msg' => 'Вызов пацента из данного АРМа не поддерживается.');
		}

		$phoneData = $this->checkPersonPhoneStatus($data);

		if (!empty($phoneData[0])) $phoneData = $phoneData[0];

		if (!empty($phoneData['Error_Msg'])) {
			return array('Error_Msg' => $phoneData['Error_Msg']);
		}

		if (!empty($phoneData['PersonPhone_Phone']) && !empty($phoneData['isVerified'])) {

			$text = 'Просьба подойти';

			if ($data['ARMType_id'] === 5) {
				$text .= ' к  постовой медсестре.';
			}

			if ($data['ARMType_id'] === 19) {
				$text .= ' в процедурный кабинет.';
			}

			$this->load->helper('notify');
			sendNotifySMS(array(
				'UserNotify_Phone' => $phoneData['PersonPhone_Phone'],
				'text' => $text,
				'User_id' => $data['pmUser_id']
			));

			return array();

		} else {
			return array('Error_Msg' => 'Номер пациента не подтвержден.');
		}
	}

	/**
	 * Определение прикрепления
	 *
	 * @param int $patient пациент
	 * @param int $type_id id типа прикрепления (LpuAttachType)
	 */
	public function getPersonAttach($data) {
		$attach = array();

		// Пытаемся извлечь прикрепление из карты
		$params = array(
			'person_id' => $data['Person_id'],
			'type_id' => $data['attach_type']
		);
		$result = $this->getFirstRowFromQuery("
			select top 1
                    Terr.ERTerr_id as Terr_id,
                    pc.Lpu_id,
                    pc.LpuRegion_id,
                    l.Lpu_Nick as Lpu_Nick,
                    lr.LpuRegion_Name,
                    pc.LpuRegion_FapName,
                    pc.LpuRegion_fapid
                from v_PersonCard pc with (nolock)
					left join LpuRegionStreet lrs with (nolock) on lrs.LpuRegion_id = pc.LpuRegion_id
					left outer join v_Lpu l with (nolock) on pc.Lpu_id = l.Lpu_id
					left outer join v_LpuRegion lr with (nolock) on pc.LpuRegion_id = lr.LpuRegion_id
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
                where pc.Person_id = :person_id
                    and pc.LpuAttachType_id = :type_id
                    and pc.PersonCard_endDate is null
					and ISNULL(l.Lpu_IsTest, 1) = 1
		", $params);

		if (!empty($result)) {
			$attach = $result;
			$this->load->model('LpuRegionStreets_model');
			$attach['doctors'] = $this->LpuRegionStreets_model->getLpuRegionMedStaffFactList($attach['LpuRegion_id']);
		}
		return $attach;
	}

	/**
	 * Получить данные человека из основной базы данных
	 *
	 * @param stdClass $person_id Идентификатор человека
	 */
	public function getPersonMain($data) {
		$result = $this->getFirstRowFromQuery("
        		declare @curDate datetime = dbo.tzGetDate();
        
        		select top 1
                    p.Person_id,
					p.Person_SurName as Person_Surname,
					p.Person_FirName as Person_Firname,
					p.Person_SecName as Person_Secname,
					p.Sex_id as PersonSex_id,
					Person_BirthDay as Person_BirthDate,
					Polis_Ser,
					Polis_Num,
					rtrim(p.Person_Surname)+' '+rtrim(p.Person_Firname)+isnull(' '+rtrim(p.Person_Secname), '') as Person_FIO,
					case 
						when p.Polis_id is not null and ISNULL(p.Polis_endDate, @curDate) >= @curDate 
						then 0 else 1 
					end as PolisIsClosed,
					ISNULL(pers.Person_IsUnknown, 1) as Person_IsUnknown
                from
                	v_Person_ER p with (nolock)
                	left join v_Person pers with (nolock) on pers.Person_id = p.Person_id
				where
					p.Person_id = :person_id", array("person_id" => $data["Person_id"]));
		if (!empty($result)) {
			// Начальная обработка результатов
			$result['Person_Surname'] = ucwords($result['Person_Surname']);
			$result['Person_Firname'] = ucwords($result['Person_Firname']);
			$result['Person_Secname'] = ucwords($result['Person_Secname']);
			$result['Person_FIO'] = ucwords($result['Person_FIO']);
			$result['Person_SecureFIO'] = $result['Person_Firname'] . ' ' . $result['Person_Secname'] . ' ' . mb_substr($result['Person_Surname'], 0, 1) . '.';
		}

		return $result;
	}

	public function getPersonRecordsAll($data) {

		if (!empty($data['pastRecords'])) {
			$data['sign'] = "<";
		}

		if (!empty($data['futureRecords'])) {
			if (!empty($data['showTodayRecords'])) {
				$data['sign'] = ">=";
			} else {
				$data['sign'] = ">";
			}
		}
		$user_id = 0;
		//todo грузить с портала
		if(!empty($data['pmuser_id'])) {
			$user_id = $data['pmuser_id'];
		}
		// Если вошли по ЕСИА показываем все записи человека (включая записи в промед)
		if (!is_null($data['pmuser_id']) && $data['pmuser_id'] == 0) {
			$user_id = 0;
		}

		$select = ""; $join = ""; $filter = "";

		$params = array(
			'user_id' => $user_id,
			'person_id' => $data['Person_id']
		);

		$signFilter = '';

		if (!empty($data['futureRecords'])) {
			$signFilter = " or (datediff(d, dbo.tzGetdate(), tt.TimetableGraf_begTime) = 0 and et.ElectronicTalon_id is not null and et.ElectronicTalonStatus_id < 4)  ";
		}

		if (!empty($data['pastRecords'])) {
			$signFilter = " or (datediff(d, dbo.tzGetdate(), tt.TimetableGraf_begTime) < 0 and et.ElectronicTalon_id is not null) ";
		}

		if (!empty($data['sign'])) {
			if (empty($data['showTodayRecords'])) {
				$filter = " and (tt.TimetableGraf_begTime {$data['sign']} dbo.tzGetdate() and et.ElectronicTalon_id is null {$signFilter}";
			} else {
				$filter = " and (cast(tt.TimetableGraf_begTime as date) {$data['sign']} cast(dbo.tzGetdate() as date) and et.ElectronicTalon_id is null {$signFilter}";
			}
		}

		$select = ",
				'TimetableGraf' as object,
				'Timetable' as viewGroup,
               	tt.TimetableGraf_IsModerated,
                    msf.MedStaffFact_id,
                    msf.MedSpecOms_id,
                    msf.MedStaffFactCache_IsPaidRec,
                    msf.MedStaffFactCache_CostRec,
                    rtrim(msf.Person_Surname)+' '+left(msf.Person_Firname,1)+'. '+left(msf.Person_Secname,1)+'.' as MedPersonal_FIO,
                    rtrim(msf.Person_Surname)+' '+rtrim(msf.Person_Firname)+' '+rtrim(msf.Person_Secname) as MedPersonal_FullFIO,
                    lsp.ProfileSpec_Name,
                    lsp.LpuSectionProfile_id as Profile_id,
					case when (
						select
							count(AV.AttributeValue_id)
						from
							v_AttributeValue AV (nolock)
							inner join v_Attribute A (nolock) on A.Attribute_id = AV.Attribute_id and A.Attribute_SysNick in ('portalzno', 'EarlyDetect')
							left join v_AttributeVision AVI (nolock) on AVI.Attribute_id = A.Attribute_id
							left join v_AttributeSignValue ASV (nolock) on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						where 
							AV.AttributeValue_TableName = 'dbo.LpuSection'
							and AV.AttributeValue_ValueBoolean = 1
							and AV.AttributeValue_TablePKey = ls.LpuSection_id
							and isnull(AVI.AttributeVision_begDate, '2000-01-01') <= @curDate
							and isnull(AVI.AttributeVision_endDate, '2030-01-01') >= @curDate
							and isnull(A.Attribute_begDate, '2000-01-01') <= @curDate
							and isnull(A.Attribute_endDate, '2030-01-01') >= @curDate
							and isnull(ASV.AttributeSignValue_begDate, '2000-01-01') <= @curDate
							and isnull(ASV.AttributeSignValue_endDate, '2030-01-01') >= @curDate
					) = 2 then 1 else 0 end as cabinetDetectionZNO
            ";

		$join = "
               left join v_Medstafffact msf (nolock) on tt.MedStaffFact_id = msf.MedStaffFact_id
                    left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
                    left join v_LpuSectionProfile lsp (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
                    left join v_LpuUnit_ER lu(nolock) on lu.LpuUnit_id = ls.LpuUnit_id
                    left join v_Lpu lpu (nolock) on lpu.Lpu_id = lu.Lpu_id
            ";

		$offset = ''; $limit = '';

		if (!empty($data['offset'])) {
			$data['offset'] = intval($data['offset']);
			if (!empty($data['offset'])) {
				$offset = "OFFSET {$data['offset']} ROWS";
			}
		}

		if (!empty($data['limit'])) {
			$data['limit'] = intval($data['limit']);
			if (!empty($offset) && !empty($data['limit'])) {
				$limit = "FETCH NEXT {$data['limit']} ROWS ONLY";
			}
		}

		$result = $this->queryResult("
				declare @curDate date = dbo.tzGetDate();
				select distinct
					tt.TimetableGraf_id,
                    tt.TimetableGraf_Day,
                    tt.TimetableGraf_updDT,
                    tt.TimetableGraf_begTime,
                    convert(varchar, tt.TimetableGraf_begTime,104) as RecordSetDate,
                    left(convert(varchar, tt.TimetableGraf_begTime,108),5) as RecordSetTime,
                    tt.TimetableGraf_begTime as sortField,
                    datediff(mi, dbo.tzGetdate(), tt.TimetableGraf_begTime) as DateDiff,
                    datediff(mm, tt.TimetableGraf_begTime, dbo.tzGetdate()) as MonthDiff,
                    lpu.Lpu_id,
                    lpu.Lpu_Nick as Lpu_Nick,
                    lpu.Lpu_Name as Lpu_Name,
                    lu.LpuUnit_id,
                    lu.LpuUnit_Name,
                    lu.LpuUnit_Phone,
                    rtrim(str.KLStreet_Name)+' '+rtrim(a.Address_House) as LpuUnit_Address,
                    rtrim(p.Person_Surname)+' '+rtrim(p.Person_Firname)+isnull(' '+rtrim(p.Person_Secname), '') as Person_FIO,
                    p.Person_id,
					ed.EvnDirection_TalonCode,
					et.ElectronicTalon_Num,
					'pm_paid' as source_system
	                {$select}
                from v_TimetableGraf tt with (nolock)
                left join v_Person_ER p with (nolock) on tt.Person_id = p.Person_id
                {$join}
                left join v_Address a (nolock) on a.Address_id = lu.Address_id
                left join v_KLStreet str (nolock) on str.KLStreet_id = a.KLStreet_id
				left join v_EvnDirection_all (nolock) as ed on ed.EvnDirection_id = tt.EvnDirection_id
				left join v_ElectronicTalon (nolock) as et on et.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnQueue q (nolock) on q.TimetableGraf_id = tt.TimetableGraf_id
                where
                    (1=1)
                    and (1 = CASE
                		 -- если есть связанная заявка
						  WHEN eqr.EvnQueue_id is not null
						  	THEN case when (eqr.pmUser_insID = :user_id or :user_id = 0) then 1
							else 0 end
						  WHEN eqr.EvnQueue_id is null
						   	THEN case when (tt.pmUser_updID = :user_id or :user_id = 0) then 1
							else 0 end
						  ELSE 0
				   	END)
                    and tt.Person_id = :person_id
                    and tt.TimetableGraf_begTime is not null
                    {$filter}
                    and isnull(lpu.Lpu_IsTest, 1) = 1
                    and (q.EvnQueueStatus_id = 3 or q.EvnQueueStatus_id is NULL)

                order by tt.TimetableGraf_begTime desc
                {$offset}
                {$limit}
			", $params);
		return $result;
	}

	/**
	 * Получить данных пациента для medSvid.
	 * @param $data
	 * @return |null
	 */
	function getPersonForMedSvid($data)
	{
		$sql = "
			declare
				@Person_id bigint = :Person_id,
				@date datetime, @tmpdate datetime, @years int, @months int, @days int,
				@Year bigint = YEAR(dbo.tzGetDate()),
				@curDate datetime = dbo.tzGetDate(),
				@YearLastDay datetime = cast(YEAR(dbo.tzGetDate()) as varchar) + '-12-31';
			select @date = Person_BirthDay from v_PersonState (nolock) where Person_id = @Person_id;
			select @tmpdate = @date;
			select @years = DATEDIFF(yy, @tmpdate, @curDate) - CASE WHEN (MONTH(@date) > MONTH(@curDate)) OR (MONTH(@date) = MONTH(@curDate) AND DAY(@date) > DAY(@curDate)) THEN 1 ELSE 0 END;
			select @tmpdate = DATEADD(yy, @years, @tmpdate);
			select @months = DATEDIFF(m, @tmpdate, @curDate) - CASE WHEN DAY(@date) > DAY(@curDate) THEN 1 ELSE 0 END;
			select @tmpdate = DATEADD(m, @months, @tmpdate);
			select @days = DATEDIFF(d, @tmpdate, @curDate);
			select top 1 
				PersonEvn_id,  
				convert(varchar(10), Person_BirthDay, 120) as Person_BirthDay,
				dbo.Age2(ps.Person_BirthDay, @curDate) as Person_Age,
				dbo.Age2(ps.Person_BirthDay, @YearLastDay) as Person_AgeEndYear,
				@months as Person_AgeMonths, 
				@days as Person_AgeDays,
				Sex_id
			from v_PersonState PS (nolock) 
			where PS.Person_id = @Person_id;
		";
		$resp = $this->dbmodel->getFirstRowFromQuery($sql, $data);
		return (!empty($resp)) ? $resp : null;
	}

	public function getPersonDeputy($data){
		$query = "
			SELECT TOP 1 
				 PA.Person_Fio as \"Deputy_Fio\",
				 PA.PersonPhone_Phone as \"Deputy_Phone\",
			     PD.Person_pid as \"Deputy_id\"
			FROM dbo.v_PersonDeputy PD with (nolock)
			LEFT JOIN dbo.v_Person_all PA with (nolock) ON PA.Person_id = PD.Person_pid
			WHERE pd.Person_id = :Person_id
		";
		
		$resp = $this->queryResult($query, ['Person_id' => $data['Person_id']]);
		return $resp;
	}
}

