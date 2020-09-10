<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Список проб рабочего списка
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class AnalyzerWorksheetEvnLabSample_model extends SwPgModel {
	private $AnalyzerWorksheetEvnLabSample_id;//AnalyzerWorksheetEvnLabSample_id
	private $AnalyzerWorksheet_id;//Рабочий список
	private $EvnLabSample_id;//Проба на лабораторное исследование
	private $AnalyzerWorksheetEvnLabSample_X;//Координата расположения пробы по оси X
	private $AnalyzerWorksheetEvnLabSample_Y;//Координата расположения пробы по оси Y
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetEvnLabSample_id() { return $this->AnalyzerWorksheetEvnLabSample_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetEvnLabSample_id($value) { $this->AnalyzerWorksheetEvnLabSample_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheet_id() { return $this->AnalyzerWorksheet_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheet_id($value) { $this->AnalyzerWorksheet_id = $value; }

	/**
	 * Comment
	 */
	public function getEvnLabSample_id() { return $this->EvnLabSample_id;}
	/**
	 * Comment
	 */
	public function setEvnLabSample_id($value) { $this->EvnLabSample_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetEvnLabSample_X() { return $this->AnalyzerWorksheetEvnLabSample_X;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetEvnLabSample_X($value) { $this->AnalyzerWorksheetEvnLabSample_X = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetEvnLabSample_Y() { return $this->AnalyzerWorksheetEvnLabSample_Y;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetEvnLabSample_Y($value) { $this->AnalyzerWorksheetEvnLabSample_Y = $value; }

	/**
	 * Comment
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Comment
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Comment
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Comment
	 */
	function load() {
		$q = "
			select
				AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
				AnalyzerWorksheet_id as \"AnalyzerWorksheet_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				AnalyzerWorksheetEvnLabSample_X as \"AnalyzerWorksheetEvnLabSample_X\",
				AnalyzerWorksheetEvnLabSample_Y as \"AnalyzerWorksheetEvnLabSample_Y\"
			from
				lis.v_AnalyzerWorksheetEvnLabSample
			where
				AnalyzerWorksheetEvnLabSample_id = :AnalyzerWorksheetEvnLabSample_id
		";
		$r = $this->db->query($q, array('AnalyzerWorksheetEvnLabSample_id' => $this->AnalyzerWorksheetEvnLabSample_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerWorksheetEvnLabSample_id = $r[0]['AnalyzerWorksheetEvnLabSample_id'];
				$this->AnalyzerWorksheet_id = $r[0]['AnalyzerWorksheet_id'];
				$this->EvnLabSample_id = $r[0]['EvnLabSample_id'];
				$this->AnalyzerWorksheetEvnLabSample_X = $r[0]['AnalyzerWorksheetEvnLabSample_X'];
				$this->AnalyzerWorksheetEvnLabSample_Y = $r[0]['AnalyzerWorksheetEvnLabSample_Y'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function getAnalyzerModelDimensions($data) {
		$query = "
			select
				AnalyzerRack.AnalyzerRack_DimensionX as \"AnalyzerRack_DimensionX\",
				AnalyzerRack.AnalyzerRack_DimensionY as \"AnalyzerRack_DimensionY\"
			from lis.v_AnalyzerWorksheet AnalyzerWorksheet
				left join lis.v_AnalyzerRack AnalyzerRack on AnalyzerRack.AnalyzerRack_id =  AnalyzerWorksheet.AnalyzerRack_id
			where AnalyzerWorksheet.AnalyzerWorksheet_id = :AnalyzerWorksheet_id
		";
		$result_query = $this->db->query($query, $data);
		if ( is_object($result_query) ) {
			$result = $result_query->result('array');
			// приводим полученные данные к нужному виду
			if (is_array($result) && (count($result)>0)) {
				return $result[0];
			}
		}
		return false;
	}

	/**
	 * Comment
	 */
	function loadMatrix($data) {
		// читаем список проб из БД по одному рабочему
		$where = array();
		if ($data['AnalyzerWorksheet_id']>0) {
			$filter = 'awels.AnalyzerWorksheet_id = :AnalyzerWorksheet_id';
		} else {
			// Список можно получить только по существующему рабочему списку
			return false;
		}

		$query = "
			SELECT
				awels.AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
				awels.AnalyzerWorksheet_id as \"AnalyzerWorksheet_id\",
				awels.EvnLabSample_id as \"EvnLabSample_id\",
				awels.AnalyzerWorksheetEvnLabSample_X as \"AnalyzerWorksheetEvnLabSample_X\",
				awels.AnalyzerWorksheetEvnLabSample_Y as \"AnalyzerWorksheetEvnLabSample_Y\",
				EvnLabSample.EvnLabSample_Num as \"EvnLabSample_Num\",
				RTRIM(LTRIM(COALESCE(ps.Person_Surname, '') || ' ' || SUBSTRING(COALESCE(ps.Person_Firname, ''),1,1) || ' ' || SUBSTRING(COALESCE(ps.Person_Secname, ''),1,1))) as \"Person_FIO\"
			FROM
				lis.v_AnalyzerWorksheetEvnLabSample awels
				LEFT JOIN dbo.v_EvnLabSample EvnLabSample ON EvnLabSample.EvnLabSample_id = awels.EvnLabSample_id
				LEFT JOIN dbo.v_EvnLabRequest EvnLabRequest ON EvnLabSample.EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id
				LEFT JOIN v_PersonState ps ON ps.Person_id = EvnLabRequest.Person_id
			where {$filter}
			order by AnalyzerWorksheetEvnLabSample_Y, AnalyzerWorksheetEvnLabSample_X
		";
		//echo getDebugSQL($query, $data);
		$result_query = $this->db->query($query, $data);
		if ( is_object($result_query) ) {
			$result = $result_query->result('array');
			// приводим полученные данные к нужному виду
			if (is_array($result)) {
				// получаем размер матрицы
				$counts = $this->getAnalyzerModelDimensions($data);
				if (!$counts) {
					return false;
				}
				$count_x = $counts['AnalyzerRack_DimensionX'];
				$count_y = $counts['AnalyzerRack_DimensionY'];
				// создаем пустую матрицу
				$matrix = array();
				for($yi=1; $yi<=$count_y; $yi++) { // по строкам
					$matrix[$yi] = array();
					$matrix[$yi]['AnalyzerWorksheet_id'] = $data['AnalyzerWorksheet_id'];
					for($xi=1; $xi<=$count_x; $xi++) { // по колонкам
						$matrix[$yi]['AnalyzerWorksheetEvnLabSample_id'.$xi] = '';
						$matrix[$yi]['AnalyzerWorksheetEvnLabSample_X'.$xi] = '';
						//$matrix[$yi][$xi] = '';
					}
				}
				// заполняем матрицу данными
				foreach($result as $row) {
					$matrix[$row['AnalyzerWorksheetEvnLabSample_Y']]['AnalyzerWorksheetEvnLabSample_X'.$row['AnalyzerWorksheetEvnLabSample_X']] = ''.substr($row['EvnLabSample_Num'], -4).'<br/><small>'.$row['Person_FIO'].'</small>';
					$matrix[$row['AnalyzerWorksheetEvnLabSample_Y']]['AnalyzerWorksheetEvnLabSample_id'.$row['AnalyzerWorksheetEvnLabSample_X']] = $row['AnalyzerWorksheetEvnLabSample_id'];
				}
			}
			return $matrix;
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function clearMatrix($data) {
		// очищаем список проб (только если статус у списка = новый)
		$query = "
			Delete lis.AnalyzerWorksheetEvnLabSample
			From lis.AnalyzerWorksheetEvnLabSample awels
			inner join lis.AnalyzerWorksheet av ON av.AnalyzerWorksheet_id = awels.AnalyzerWorksheet_id and av.AnalyzerWorksheetStatusType_id = 1
			where awels.AnalyzerWorksheet_id = :AnalyzerWorksheet_id;
			Select :AnalyzerWorksheet_id as \"AnalyzerWorksheet_id\"
		";

		$result_query = $this->db->query($query, $data);
		if ( is_object($result_query) ) {
			$result = $result_query->result('array');
			return $result;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Возвращает набор проб, входящих в один рабочий список для отправки проб в ЛИС-систему
	 */
	function loadEvnLabSampleWithAnalyzerWorksheet($data) {
		// читаем список проб из БД по одному рабочему
		$where = array();
		if ($data['AnalyzerWorksheet_id']>0) {
			$filter = 'awels.AnalyzerWorksheet_id = :AnalyzerWorksheet_id';
		} else {
			// Список можно получить только по существующему рабочему списку
			return array();
		}

		$query = "
			SELECT
				awels.AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
				awels.AnalyzerWorksheet_id as \"AnalyzerWorksheet_id\",
				awels.EvnLabSample_id as \"EvnLabSample_id\",
				awels.AnalyzerWorksheetEvnLabSample_X as \"AnalyzerWorksheetEvnLabSample_X\",
				awels.AnalyzerWorksheetEvnLabSample_Y as \"AnalyzerWorksheetEvnLabSample_Y\",
				EvnLabSample.EvnLabSample_Num as \"EvnLabSample_Num\",
				RTRIM(LTRIM(COALESCE(ps.Person_Surname, '') || ' ' || SUBSTRING(COALESCE(ps.Person_Firname, ''),1,1) || ' ' || SUBSTRING(COALESCE(ps.Person_Secname, ''),1,1))) as \"Person_FIO\",
				link.lis_id as \"lis_id\" -- если ранее заявка уже отправлялась, то ее возможно ее надо будет проапдейтить 
			FROM
				lis.v_AnalyzerWorksheetEvnLabSample awels
				LEFT JOIN dbo.v_EvnLabSample EvnLabSample ON EvnLabSample.EvnLabSample_id = awels.EvnLabSample_id
				LEFT JOIN dbo.v_EvnLabRequest EvnLabRequest ON EvnLabSample.EvnLabRequest_id = EvnLabRequest.EvnLabRequest_id
				LEFT JOIN v_PersonState ps ON ps.Person_id = EvnLabRequest.Person_id
				left join lis.v_Link link on link.object_id = EvnLabSample.EvnLabSample_id and link.link_object = 'EvnLabSample'
			where {$filter}
			order by AnalyzerWorksheetEvnLabSample_Y, AnalyzerWorksheetEvnLabSample_X
		";
		//echo getDebugSQL($query, $data);die();
		$result_query = $this->db->query($query, $data);
		if ( is_object($result_query) ) {
			return $result_query->result('array');
		}
		else {
			return array();
		}
	}
	
	/**
	 * Comment
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['AnalyzerWorksheetEvnLabSample_id']) && $filter['AnalyzerWorksheetEvnLabSample_id']) {
			$where[] = 'v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_id = :AnalyzerWorksheetEvnLabSample_id';
			$p['AnalyzerWorksheetEvnLabSample_id'] = $filter['AnalyzerWorksheetEvnLabSample_id'];
		}
		if (isset($filter['AnalyzerWorksheet_id']) && $filter['AnalyzerWorksheet_id']) {
			$where[] = 'v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheet_id = :AnalyzerWorksheet_id';
			$p['AnalyzerWorksheet_id'] = $filter['AnalyzerWorksheet_id'];
		}
		if (isset($filter['EvnLabSample_id']) && $filter['EvnLabSample_id']) {
			$where[] = 'v_AnalyzerWorksheetEvnLabSample.EvnLabSample_id = :EvnLabSample_id';
			$p['EvnLabSample_id'] = $filter['EvnLabSample_id'];
		}
		if (isset($filter['AnalyzerWorksheetEvnLabSample_X']) && $filter['AnalyzerWorksheetEvnLabSample_X']) {
			$where[] = 'v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_X = :AnalyzerWorksheetEvnLabSample_X';
			$p['AnalyzerWorksheetEvnLabSample_X'] = $filter['AnalyzerWorksheetEvnLabSample_X'];
		}
		if (isset($filter['AnalyzerWorksheetEvnLabSample_Y']) && $filter['AnalyzerWorksheetEvnLabSample_Y']) {
			$where[] = 'v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_Y = :AnalyzerWorksheetEvnLabSample_Y';
			$p['AnalyzerWorksheetEvnLabSample_Y'] = $filter['AnalyzerWorksheetEvnLabSample_Y'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
				v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheet_id as \"AnalyzerWorksheet_id\",
				v_AnalyzerWorksheetEvnLabSample.EvnLabSample_id as \"EvnLabSample_id\",
				v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_X as \"AnalyzerWorksheetEvnLabSample_X\",
				v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheetEvnLabSample_Y as \"AnalyzerWorksheetEvnLabSample_Y\",
				AnalyzerWorksheet_id_ref.AnalyzerWorksheet_Name as \"AnalyzerWorksheet_id_Name\",
				EvnLabSample_id_ref.EvnLabSample_Num as \"EvnLabSample_Num\"
			FROM
				lis.v_AnalyzerWorksheetEvnLabSample
				LEFT JOIN lis.v_AnalyzerWorksheet AnalyzerWorksheet_id_ref ON AnalyzerWorksheet_id_ref.AnalyzerWorksheet_id = v_AnalyzerWorksheetEvnLabSample.AnalyzerWorksheet_id
				LEFT JOIN dbo.v_EvnLabSample EvnLabSample_id_ref ON EvnLabSample_id_ref.EvnLabSample_id = v_AnalyzerWorksheetEvnLabSample.EvnLabSample_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function save() {

        // Проверяем есть ли записанная проба на эту ячейку, если есть - удаляеи
        $queryParams = array(
            'AnalyzerWorksheetEvnLabSample_X' => $this->AnalyzerWorksheetEvnLabSample_X,
            'AnalyzerWorksheetEvnLabSample_Y' => $this->AnalyzerWorksheetEvnLabSample_Y,
            'AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id
        );

        $query = "
            select
                AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\"
            from
                lis.AnalyzerWorksheetEvnLabSample
            where
            AnalyzerWorksheetEvnLabSample_X = :AnalyzerWorksheetEvnLabSample_X
            and AnalyzerWorksheetEvnLabSample_Y = :AnalyzerWorksheetEvnLabSample_Y
            and AnalyzerWorksheet_id = :AnalyzerWorksheet_id
        ";

        //echo getDebugSQL($query, $queryParams); die;
        $result = $this->db->query($query, $queryParams);

        if ( is_object($result) ) {
			$response = $result->result('array');
            if (!empty($response[0])) {
                foreach ($response as $row) {
                    $query = "
                        select 
                            Error_Code as \"Error_Code\",
                            Error_Message as \"Error_Msg\"
                        from lis.p_AnalyzerWorksheetEvnLabSample_del (
                            AnalyzerWorksheetEvnLabSample_id := :AnalyzerWorksheetEvnLabSample_id
                            )
                    ";
                    $result = $this->db->query($query, array(
                        'AnalyzerWorksheetEvnLabSample_id' => $row['AnalyzerWorksheetEvnLabSample_id']
                    ));
                }
            }
		}

		$procedure = 'p_AnalyzerWorksheetEvnLabSample_ins';
		if ( $this->AnalyzerWorksheetEvnLabSample_id > 0 ) {
			$procedure = 'p_AnalyzerWorksheetEvnLabSample_upd';
		}
		$q = "
			select 
			    AnalyzerWorksheetEvnLabSample_id as \"AnalyzerWorksheetEvnLabSample_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from lis." . $procedure . " (
				AnalyzerWorksheetEvnLabSample_id := :AnalyzerWorksheetEvnLabSample_id,
				AnalyzerWorksheet_id := :AnalyzerWorksheet_id,
				EvnLabSample_id := :EvnLabSample_id,
				AnalyzerWorksheetEvnLabSample_X := :AnalyzerWorksheetEvnLabSample_X,
				AnalyzerWorksheetEvnLabSample_Y := :AnalyzerWorksheetEvnLabSample_Y,
				pmUser_id := :pmUser_id
				)
		";
		$p = array(
			'AnalyzerWorksheetEvnLabSample_id' => $this->AnalyzerWorksheetEvnLabSample_id,
			'AnalyzerWorksheet_id' => $this->AnalyzerWorksheet_id,
			'EvnLabSample_id' => $this->EvnLabSample_id,
			'AnalyzerWorksheetEvnLabSample_X' => $this->AnalyzerWorksheetEvnLabSample_X,
			'AnalyzerWorksheetEvnLabSample_Y' => $this->AnalyzerWorksheetEvnLabSample_Y,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerWorksheetEvnLabSample_id = $result[0]['AnalyzerWorksheetEvnLabSample_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Comment
	 */
	function delete() {
		$q = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from lis.p_AnalyzerWorksheetEvnLabSample_del (
				AnalyzerWorksheetEvnLabSample_id := :AnalyzerWorksheetEvnLabSample_id
				)
		";
		$r = $this->db->query($q, array(
			'AnalyzerWorksheetEvnLabSample_id' => $this->AnalyzerWorksheetEvnLabSample_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	public function saveBulk($AnalyzerWorksheet_id, $PickedEvnLabSamples){
		$PickedEvnLabSamples_decoded = json_decode($PickedEvnLabSamples, true);
		if (null !== $PickedEvnLabSamples_decoded) {
			if (count($PickedEvnLabSamples_decoded)) {
				$p = array(
					'AnalyzerWorksheet_id' => $AnalyzerWorksheet_id,
					'pmUser_id' => $this->pmUser_id,
				);
				foreach($PickedEvnLabSamples_decoded as $EvnLabSample_id) {
					$p['s'.$EvnLabSample_id] = $EvnLabSample_id;
				}
				$samples = '(:s'.implode('),(:s',$PickedEvnLabSamples_decoded).')';
				$q = <<<Q
SET NOCOUNT ON
Declare
	@EvnLabSample_id BIGINT,
	@x BIGINT,
	@y BIGINT,
	@AnalyzerWorksheet_id BIGINT = :AnalyzerWorksheet_id,
	@pmUser_id BIGINT = :pmUser_id,
	@AnalyzerWorksheetEvnLabSample_id bigint,
	@ErrCode int,
	@ErrMessage varchar(4000);
Declare
	@tbl table (EvnLabSample_id BIGINT PRIMARY KEY)
Declare
	samples CURSOR FOR SELECT EvnLabSample_id FROM @tbl
DECLARE --получение списка свободных ячеек в штативе этого рабочего списка
	coord CURSOR FOR WITH X(ID) AS
	(
	 SELECT 1
	 UNION ALL
	 SELECT ID + 1 FROM X WHERE ID<(SELECT AnalyzerRack_DimensionX FROM lis.AnalyzerRack WHERE AnalyzerRack_id  = (SELECT AnalyzerRack_id FROM lis.AnalyzerWorksheet WHERE AnalyzerWorksheet_id = @AnalyzerWorksheet_id))
	),
	Y(ID) AS
	(
	 SELECT 1
	 UNION ALL
	 SELECT ID + 1 FROM Y WHERE ID<(SELECT AnalyzerRack_DimensionY FROM lis.AnalyzerRack WHERE AnalyzerRack_id  = (SELECT AnalyzerRack_id FROM lis.AnalyzerWorksheet WHERE AnalyzerWorksheet_id = @AnalyzerWorksheet_id))
	)
	SELECT x.ID AS x, y.ID AS y FROM X,Y
	WHERE NOT EXISTS (SELECT * FROM lis.AnalyzerWorksheetEvnLabSample WHERE AnalyzerWorksheet_id = @AnalyzerWorksheet_id AND AnalyzerWorksheetEvnLabSample_X = x.ID AND AnalyzerWorksheetEvnLabSample_Y = y.ID)
	ORDER BY 1,2
BEGIN TRY
	BEGIN TRANSACTION
	--заполняем массив с пробами
	INSERT INTO @tbl VALUES $samples
	OPEN samples;
	OPEN coord;
	FETCH NEXT FROM coord with(nolock) INTO @x, @y; --получаем первые свободные координаты
	IF 0 = @@FETCH_STATUS BEGIN --если получилось берем первую пробу
		FETCH NEXT FROM samples INTO @EvnLabSample_id
	END ELSE BEGIN
		RAISERROR('В штативе нет свободных ячеек', 16, 1)
	END
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @AnalyzerWorksheetEvnLabSample_id = NULL;
		EXEC lis.p_AnalyzerWorksheetEvnLabSample_ins --сохраняем пробу
			@AnalyzerWorksheetEvnLabSample_id = @AnalyzerWorksheetEvnLabSample_id output,
			@AnalyzerWorksheet_id = @AnalyzerWorksheet_id,
			@EvnLabSample_id = @EvnLabSample_id,
			@AnalyzerWorksheetEvnLabSample_X = @x,
			@AnalyzerWorksheetEvnLabSample_Y = @y,
			@pmUser_id = @pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
		IF ('' != @ErrMessage) BEGIN
			RAISERROR(@ErrMessage, 16, 1) --если что-то пошло не так вываливаемся из цикла в блок CATCH
		END
		FETCH NEXT FROM coord with(nolock) INTO @x, @y;--получаем следующую свободную ячейку
		IF 0 = @@FETCH_STATUS BEGIN
			FETCH NEXT FROM samples with(nolock) INTO @EvnLabSample_id--если получилась берем следующую пробу
		END	ELSE BEGIN
			FETCH NEXT FROM samples with(nolock) INTO @EvnLabSample_id--если не получилось, смотрим есть ли еще пробы
			IF 0 = @@FETCH_STATUS BEGIN -- если есть значит проб больше чем свободных ячеек
				RAISERROR('Количество выбраных проб больше чем количество свободных ячеек в штативе', 16, 1) --поднимаем панику
			END
		END
	END
	CLOSE coord
	DEALLOCATE coord
	CLOSE samples
	DEALLOCATE samples
	COMMIT TRANSACTION
END TRY
BEGIN CATCH
	--что-то видимо пошло не так
	ROLLBACK TRANSACTION
	--закрываем и освобождаем курсоры, если они открыты
	IF (CURSOR_STATUS('global','coord') IN (1,0)) BEGIN
		-- 1 Результирующий набор курсора включает как минимум одну строку.
		-- 0 Результирующий набор курсора пуст.*
		CLOSE coord;
	END
	IF (CURSOR_STATUS('global','coord') IN (-1)) BEGIN
		-- -1 Курсор закрыт.
		DEALLOCATE coord;
	END
	IF (CURSOR_STATUS('global','samples') IN (1,0)) BEGIN
		-- 1 Результирующий набор курсора включает как минимум одну строку.
		-- 0 Результирующий набор курсора пуст.*
		CLOSE samples;
	END
	IF (CURSOR_STATUS('global','samples') IN (-1)) BEGIN
		-- -1 Курсор закрыт.
		DEALLOCATE samples;
	END
	set @ErrCode = error_number()
	set @ErrMessage = error_message()
END CATCH
select @ErrMessage as Error_Msg;
Q;

				$r = $this->db->query($q, $p, true);
				if ( is_object($r) ) {
					$result = $r->result('array');
					foreach($PickedEvnLabSamples_decoded as $EvnLabSample_id) {
						// обонвить анализатор в пробе
						$query = "
							update
								EvnLabSample els
							set
								els.Analyzer_id = (
									select
										aw.Analyzer_id
									from
										lis.v_AnalyzerWorksheet aw
										inner join lis.v_AnalyzerWorksheetEvnLabSample awels on awels.AnalyzerWorksheet_id = aw.AnalyzerWorksheet_id
									where
										awels.EvnLabSample_id = els.EvnLabSample_id
                                    limit 1
								)
							where
								els.EvnLabSample_id = :EvnLabSample_id							
						";
						
						$this->db->query($query, array(
							'EvnLabSample_id' => $EvnLabSample_id
						));
					}
				} else {
					log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
					$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
				}
			} else {
				$result = array(array('Error_Msg' => 'Выберите пробы для добавления в рабочий список'));
			}
		} else {
			log_message('error', 'Не удалось разбрать JSON: '.$PickedEvnLabSamples);
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
}