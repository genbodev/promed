<?php
class Wialon_model extends SwPgModel {
	/**
	 * @var Основные свойства
	 */
	const FLAG_OBJ_BASE = 0x00000001;

	/**
	 * @var Последнее сообщение и местоположение
	 */
	const FLAG_OBJ_LAST_MSG = 0x00000400;

	/**
	 * @var Группы объекта
	 */
	const FLAG_OBJ_GROUPS = 0x00000008;

	/**
	 * @var string Логин
	 */
	protected $_user;

	/**
	 * @var string Пароль
	 */
	protected $_password;
	
	/**
	 * @var string Токен
	 */
	protected $_token;

	/**
	 * @var string Хост с изображениями
	 */
	protected $_image_host;

	/**
	 * @var stting Wialon API URL
	 */
	protected $_api_url;

	/**
	 * @var string Ключ сессии
	 */
	protected $_session_key = 'ssid';

	/**
	 * @var bool Флаг завершения всех скриптов при ошибке авторизации
	 */
	public $exit_on_auth_err = true;

	/**
	 * Связывает бригаду скорой помощи с Виалоновской
	 * 
	 * @param array $data Данные ProcessInputData
	 * @return array or false
	 */
	public function mergeEmergencyTeam( $data ){

		if ( !isset( $data['EmergencyTeam_id'] ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Error_Msg' => 'Данные для объединения бригад указаны неверно.') );
		}
		
		if ( !isset( $data['WialonEmergencyTeamId'] ) || !$data['WialonEmergencyTeamId'] ) {
			return $this->deleteMergeEmergencyTeam( $data );
		}

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		
		$sqlArr = array(
			'EmergencyTeam_id'			=> $data['EmergencyTeam_id'],
			'WialonEmergencyTeamId'		=> $data['WialonEmergencyTeamId'] > 0 ? $data['WialonEmergencyTeamId'] : null,
			'pmUser_id'					=> $data['pmUser_id'],
		);

		$sql = "SELECT \"EmergencyTeamWialonRel_id\" FROM dbo.\"v_EmergencyTeamWialonRel\" WHERE \"EmergencyTeam_id\"=:EmergencyTeam_id";
		$query = $this->db->query( $sql, $sqlArr );
		$result = $query->first_row('array');
		$sqlArr['EmergencyTeamWialonRel_id'] = !empty( $result ) && $result['EmergencyTeamWialonRel_id'] ? $result['EmergencyTeamWialonRel_id'] : null;

		if ( $is_pg ) {
			if ( !$sqlArr['EmergencyTeamWialonRel_id'] ) {
				$sql = "
					INSERT INTO dbo.EmergencyTeamWialonRel (
						EmergencyTeam_id,
						WialonEmergencyTeamId,
						pmUser_insID,
						pmUser_updID,
						EmergencyTeamWialonRel_insDT,
						EmergencyTeamWialonRel_updDT
					) VALUES (
						:EmergencyTeam_id,
						:WialonEmergencyTeamId,
						:pmUser_id,
						:pmUser_id,
						NOW(),
						NOW()
					) RETURNING EmergencyTeamWialonRel_id as \"EmergencyTeamWialonRel_id\", null as \"Error_Code\", null as \"Error_Msg\";
				";
			} else {
				$sql = "
					UPDATE dbo.EmergencyTeamWialonRel SET
						EmergencyTeam_id = :EmergencyTeam_id,
						WialonEmergencyTeamId = :WialonEmergencyTeamId,
						pmUser_updID = :pmUser_id,
						EmergencyTeamWialonRel_updDT = NOW()
					WHERE
						EmergencyTeamWialonRel_id = :EmergencyTeamWialonRel_id
					RETURNING
						EmergencyTeamWialonRel_id as \"EmergencyTeamWialonRel_id\", null as \"Error_Code\", null as \"Error_Msg\";
				";
			}
		} else {
			if ( $sqlArr['EmergencyTeamWialonRel_id'] ) {
				$procedure = 'p_EmergencyTeamWialonRel_upd';
			} else {
				$procedure = 'p_EmergencyTeamWialonRel_ins';
			}
			$sql = "
				SELECT EmergencyTeamWialonRel_id as \"EmergencyTeamWialonRel_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from ".$procedure."(
					EmergencyTeamWialonRel_id := :EmergencyTeamWialonRel_id,
					EmergencyTeam_id := :EmergencyTeam_id,
					WialonEmergencyTeamId := :WialonEmergencyTeamId,
					pmUser_id := :pmUser_id);
			";
		}
		
		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}				
	}
	
	/**
	 * Удаление записи о связи указанной бригады с Wialon
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	public function deleteMergeEmergencyTeam( $data ){

		if ( !isset( $data['EmergencyTeam_id'] ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Error_Msg' => 'Данные, для удаления связки бригады с Winalon, указаны неверно.') );
		}
		
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
		);

		$sql = "
				SELECT Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from p_EmergencyTeamWialonRel_delByETId(
					EmergencyTeam_id := :EmergencyTeam_id);
			";
		
		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return array( array( 'Error_Msg' => 'Во время удаления, связки бригады с Winalon, произошла ошибка.' ) );
		}
	}

	/**
	 * @return output Array Возвращает логин и пароль (Wialon) выбранного подразделения МО
	 */
	public function getWialonCredentialsByLpuDepartment($data){

		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$level='lpu';
		$filters = '';

		if (!empty($data['Sub_SysNick']))
		{
			$level = strtolower($data['Sub_SysNick']);

			switch($level){
				case 'lpubuilding';

					$filters .= ' and MS.LpuBuilding_id = :LpuBuilding_id';
					$params['LpuBuilding_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpuunittype';

					$filters .= ' and MS.LpuUnitType_id = :LpuUnitType_id';
					$params['LpuUnitType_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpuunit';

					$filters .= ' and MS.LpuUnit_id = :LpuUnit_id';
					$params['LpuUnit_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpusection';

					$filters .= ' and MS.LpuSection_id = :LpuSection_id';
					$params['LpuSection_id'] = $data['LpuDepartment_id'];
					break;
			}
		}

		$query = "
			SELECT	
				MS.MedService_id AS \"MedService_id\",
				MS.MedService_Name AS \"MedService_Name\",
				MS.MedService_WialonLogin AS \"MedService_WialonLogin\",
				MS.MedService_WialonPasswd AS \"MedService_WialonPasswd\",
				MS.MedService_WialonToken AS \"MedService_WialonToken\"
			FROM
				v_MedService MS 
				LEFT JOIN v_MedServiceType MST ON MS.MedServiceType_id = MST.MedServiceType_id
			WHERE
					MS.Lpu_id = :Lpu_id AND 
                    (MST.MedServiceType_Code IN (18,19,53))
					-- только Wialon
					AND MS.ApiServiceType_id = 2
					AND (MS.MedService_WialonLogin IS NOT NULL OR MS.MedService_WialonToken IS NOT NULL)
		";

		$result = $this->db->query(
			$query .
			$filters . '
			limit 1
		', $params);
		if (is_object($result))
		{
			$ret = $result->result('array');

			if (count($ret) > 0)
				return $ret;
			else {

				if ($level != 'lpubuilding'  && isset($data['LpuBuilding_id'])) {

					// вычисляем LpuBuilding_id, если результат пуст

					$filters = ' and MS.LpuBuilding_id = :LpuBuilding_id';
					$params['LpuBuilding_id'] = $data['LpuBuilding_id'];

					$result = $this->db->query(
						$query .
						$filters . '
						limit 1
					', $params);

					if (is_object($result)) {

						$ret = $result->result('array');
						return $ret;
					}

				} else
					return $ret;
			}
		}
		return false;
	}
	
	/**
	 * Возвращает список бригад Промеда и связей с ними для указанной ЛПУ
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	public function loadEmergencyTeamRelList( $data ){
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$sql = "
			SELECT
				et.EmergencyTeam_id as \"EmergencyTeam_id\",
				et.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				et.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				etw.WialonEmergencyTeamId as \"WialonEmergencyTeamId\"
			FROM
				v_EmergencyTeam et 

				LEFT JOIN v_EmergencyTeamWialonRel etw  ON( etw.EmergencyTeam_id=et.EmergencyTeam_id )

			WHERE
				et.Lpu_id = :Lpu_id
		";
		$query = $this->db->query( $sql, array(
			'Lpu_id' => $data['Lpu_id']
		) );

		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранение связи бригады скорой помощи с внешним ресурсом
	 * @param array $data Список идентификаторов которые связываем
	 * @return boolean
	 */
	public function saveEmergencyTeamRel( $data ){
		if ( !is_array( $data['data'] ) ) {
			$data['data'] = array();
		}
		foreach( $data['data'] as $k => $v ) {
			$prequery = "DELETE FROM v_EmergencyTeamWialonRel WHERE EmergencyTeam_id=:EmergencyTeam_id";
						//AND Lpu_id=:Lpu_id";
			$preparams = array(
				'Lpu_id'					=> $data['Lpu_id'],
				'EmergencyTeam_id'			=> $v->EmergencyTeam_id,
			);

			$this->db->query($prequery,$preparams);
			
			
			if ( (int)$v->EmergencyTeam_id ) {
				$query = $this->db->query("
					SELECT EmergencyTeamWialonRel_id as \"EmergencyTeamWialonRel_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from p_EmergencyTeamWialonRel_ins(
						Lpu_id := :Lpu_id,
						EmergencyTeam_id := :EmergencyTeam_id,
						WialonEmergencyTeamId := :WialonEmergencyTeamId,
						pmUser_id := :pmUser_id);
				",array(
					'Lpu_id'					=> $data['Lpu_id'],
					'EmergencyTeam_id'			=> $v->EmergencyTeam_id,
					'WialonEmergencyTeamId'		=> $v->WialonEmergencyTeamId,
					'pmUser_id'					=> $data['pmUser_id'],
				));
			}
		}

		if ( isset( $query ) ) {
			return $query->result_array();
		}

		return array('success'=>true);
	}
	
	/**
	 * Возвращает идентификатор бригады в Wialon по идентификатору бригады в Промед
	 * 
	 * @param array $data
	 * @return array or false on error
	 */
	public function getUnitIdByEmergencyTeamId( $data ){
		if ( !isset( $data['EmergencyTeam_id'] ) ) {
			return false;
		}
		
		$sql = "
			SELECT
				WialonEmergencyTeamId as \"WialonEmergencyTeamId\"
			FROM
				v_EmergencyTeamWialonRel 

			WHERE
				EmergencyTeam_id=:EmergencyTeam_id
		";
		
		//echo(getDebugSQL($sql, array( 'EmergencyTeam_id' => $data['EmergencyTeam_id'] ))); exit;
		$query = $this->db->query( $sql, array( 'EmergencyTeam_id' => $data['EmergencyTeam_id'] ) );

		if ( is_object( $query ) ) {
			return $query->row_array();
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для аутентификации в Wialon
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function retrieveAccessData( $data ){
		
		if ( isset($data['MedStaffFact']) && is_array($data['MedStaffFact']) && count($data['MedStaffFact']) > 0 ) {
			$filter = "msf.MedStaffFact_id=:MedStaffFact_id
				and (mst.MedServiceType_Code in (18,19,53) )";
		
			if (isset ($data['CurMedService_id'])){
				$filter .= " AND ms.MedService_id = :CurMedService_id";
			}
			$filter .= " ORDER BY ms.MedService_id DESC";
			
			// Проверяем привязан ли пользователь в качестве сотрудника к службе СМП
			$sql = "
				SELECT
					ms.MedService_id as \"MedService_id\",
					ms.MedService_WialonLogin as \"MedService_WialonLogin\",
					ms.MedService_WialonPasswd as \"MedService_WialonPasswd\",
					ms.MedService_WialonToken as \"MedService_WialonToken\"
				FROM
					v_MedService ms 

					INNER JOIN v_MedServiceMedPersonal msmp  ON( msmp.MedService_id=ms.MedService_id )

					INNER JOIN v_MedStaffFact msf  ON( msf.MedPersonal_id=msmp.MedPersonal_id )

					left JOIN v_MedServiceType mst  ON( ms.MedServiceType_id=mst.MedServiceType_id )

				WHERE
					".$filter."
			";
			
			$query = $this->db->query( $sql, array( 
				'MedStaffFact_id' => $data['MedStaffFact'][0],
				'CurMedService_id' => (!empty($data['CurMedService_id']) ? $data['CurMedService_id'] : null)
			) );
		}
		elseif ( isset ($_SESSION['MedPersonal_id']) )  {
			$filter = "msf.MedPersonal_id=:MedPersonal_id
				and (mst.MedServiceType_Code in (18,19,53) )";
		
			if (isset ($data['CurMedService_id'])){
				$filter .= " AND ms.MedService_id = :CurMedService_id";
			}

			// Проверяем привязан ли пользователь в качестве сотрудника к службе СМП
			$sql = "
				SELECT
					ms.MedService_id as \"MedService_id\",
					ms.MedService_WialonLogin as \"MedService_WialonLogin\",
					ms.MedService_WialonPasswd as \"MedService_WialonPasswd\",
					ms.MedService_WialonToken as \"MedService_WialonToken\"
				FROM
					v_MedService ms 

					INNER JOIN v_MedServiceMedPersonal msmp  ON( msmp.MedService_id=ms.MedService_id )

					INNER JOIN v_MedStaffFact msf  ON( msf.MedPersonal_id=msmp.MedPersonal_id )

					left JOIN v_MedServiceType mst  ON( ms.MedServiceType_id=mst.MedServiceType_id )

				WHERE
					".$filter."
			";
			
			$query = $this->db->query( $sql, array( 
				'MedStaffFact_id' => $_SESSION['MedPersonal_id'],
				'CurMedService_id' => (!empty($data['CurMedService_id']) ? $data['CurMedService_id'] : null)
			) );
		}

		// Результат может вернуть несколько привязок, но такого не должно быть на реальных данных
		// Поэтому берем только первую строку ответа
		if ( isset($query) && is_object( $query ) ) {
			return $query->row_array();
		} else {
			return false;
		}
	}

	/**
	 * Список номеров домов по указанному городу
	 * 
	 * @param int $city_id ID города в КЛАДРе
	 * @return array or false
	 */
	public function getHousesByCityId( $city_id, $page=1 ){
		$limit = 50;
		$start = $page * $limit - $limit;

		// @todo Перенести метод в модель КЛАДР
		// @todo Разбить выполнение по страницам
		$sql = "
			SELECT
				s.KLArea_id as \"KLArea_id\",
				s.KLStreet_id as \"KLStreet_id\",
				s.KLStreet_Name as \"KLStreet_Name\",
				h.KLHouse_id as \"KLHouse_id\",
				h.KLHouse_Name as \"KLHouse_Name\"
			FROM
				KLStreet s 

				LEFT JOIN KLHouse h  ON( h.KLStreet_id=s.KLStreet_id )

			WHERE
				s.KLArea_id=3310
				AND h.KLHouse_id BETWEEN ".$start." AND ".( $start+$limit )."
			ORDER BY
				s.KLStreet_id, h.KLHouse_id
		";
		$query = $this->db->query( $sql, array( 'KLArea_id' => $city_id ) );

		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	/**
	 * Список номеров домов по указанному городу
	 *
	 * @param int $city_id ID города в КЛАДРе
	 * @return array or false
	 */
	public function getStreetsByCityId( $city_id ){
		$sql = "
			SELECT
				s.KLArea_id as \"KLArea_id\",
				s.KLStreet_id as \"KLStreet_id\",
				s.KLStreet_Name as \"KLStreet_Name\"
			FROM
				KLStreet s 

			WHERE
				s.KLArea_id=3310
			ORDER BY
				s.KLStreet_id
		";
		$query = $this->db->query( $sql, array( 'KLArea_id' => $city_id ) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}



	/**
	 * Декомпиляция номеров домов из КЛАДРа
	 *
	 * @param string $name Номер дома (Например: Ч(2-80),Н(1-79),25стр1)
	 * @return array Список всех номеров домов
	 */
	public function decompileHouseName( $name ){
		$list = array();

		$parts = explode( ',', $name );
		foreach( $parts as $numbers ) {
			$numbers = trim( $numbers );
			if ( strpos( $numbers, 'Ч' ) === 0 || strpos( $numbers, 'Н' ) === 0 ) {
				$numbers = str_replace( array( '(', ')', 'Ч', 'Н' ), '', $numbers );
				list( $start, $end ) = explode( '-', $numbers );
				for( $i=$start; $i<=$end; $i=$i+2 ) {
					$list[] = trim( $i );
				}
			} elseif ( strpos( $numbers, '-' ) !== false ) {
				list( $start, $end ) = explode( '-', $numbers );
				for( $i=$start; $i<=$end; $i++ ) {
					$list[] = trim( $i );
				}
			} else {
				$list[] = trim( $numbers );
			}
		}

		return $list;
	}

	/**
	 * Проверка существуют ли уже координаты указанного дома
	 *
	 * @param type $house_num Номер дома
	 * @param type $house_id ID серии номеров из КЛАДРа
	 * @param type $street_id ID улицы из КЛАДРа
	 * @param type $city_id ID города из КЛАДРа
	 * @return boolean
	 */
	public function checkIfHouseLatLngExists( $house_num, $house_id, $street_id, $city_id ) {
		// Проверим существование записи
		$sql = "IF EXISTS( SELECT KLHouseCoords_id FROM KLHouseCoords khc  WHERE khc.KLArea_id=:KLArea_id AND khc.KLStreet_id=:KLStreet_id AND khc.KLHouse_id=:KLHouse_id AND khc.KLHouseCoords_Name=:KLHouseCoords_Name ) SELECT 1 as \"cnt\"";

		$query = $this->db->query( $sql, array(
			'KLArea_id' => $city_id,
			'KLStreet_id' => $street_id,
			'KLHouse_id' => $house_id,
			'KLHouseCoords_Name' => $house_num,
		) );
		$result = $query->row_array();
		if ( sizeof( $result ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохраням координаты дома
	 * 
	 * @param type $lat
	 * @param type $lng
	 * @param type $house_num
	 * @param type $house_id
	 * @param type $street_id
	 * @param type $city_id
	 */
	public function saveHouseLatLng( $lat, $lng, $house_num, $house_id, $street_id, $city_id ){
		$data = getSessionParams();

		if ( $this->checkIfHouseLatLngExists( $house_num, $house_id, $street_id, $city_id ) ) {
			return;
		}

		// Добавим запись
		$sql = "
			SELECT KLHouseCoords_id as \"KLHouseCoords_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_KLHouseCoords_ins(
				KLArea_id := :KLArea_id,
				KLStreet_id := :KLStreet_id,
				KLHouse_id := :KLHouse_id,
				KLHouseCoords_Name := :KLHouseCoords_Name,
				KLHouseCoords_LatLng := :KLHouseCoords_LatLng,
				pmUser_id := :pmUser_id);
		";

		$query = $this->db->query( $sql, array(
			'KLArea_id' => $city_id,
			'KLStreet_id' => $street_id,
			'KLHouse_id' => $house_id,
			'KLHouseCoords_Name' => $house_num,
			'KLHouseCoords_LatLng' => substr( $lat, 0, 18 ).' '.substr( $lng, 0, 18 ),
			'pmUser_id' => $data['pmUser_id'],
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * Список координат домов для указанного города
	 *
	 * @param type $city_id ID города из КЛАДРа
	 * @return boolean
	 */
	public function getHousesCoordsListByCityId( $city_id, $page=1 ) {
		$start = $page * 100 - 100;

		$sql = "
			SELECT
				Row as \"Row\",
				KLHouseCoords_id as \"KLHouseCoords_id\",
				KLHouseCoords_LatLng as \"KLHouseCoords_LatLng\"
			FROM (
				SELECT
					ROW_NUMBER() OVER (ORDER BY KLArea_id, KLHouseCoords_id) AS Row,
					KLHouseCoords_id,
					KLHouseCoords_LatLng
				FROM
					KLHouseCoords 

				WHERE
					KLArea_id=:KLArea_id
			) AS KLHouseCoordsWithRowNumbers
			WHERE
				Row >= ".$start." AND Row <= ".($start+100)."
		";
		$query = $this->db->query( $sql, array( 'KLArea_id' => $city_id ) );

		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 * Вычисляет входит ли точка в полигон
	 *
	 * @param array $polygon Список координат полигона
	 * @param array $point Координаты точки
	 * @return boolean
	 */
	public static function isPointInPolygon ( $polygon, $point ) {
		$result = false;
		$p1 = $polygon[ 0 ];
		$p2 = null;
		for( $i = 0, $j = sizeof( $polygon ); $i < $j; $i++ ){
			$p2 = $polygon[ $i%$j ];
			if ( $point['lng'] > min( array( $p1['lng'], $p2['lng'] ) ) ) {
				if ( $point['lng'] <= max( array( $p1['lng'], $p2['lng'] ) ) ) {
					if ( $point['lat'] <= max( array( $p1['lat'], $p2['lat'] ) ) ) {
						if ( $p1['lng'] != $p2['lng'] ) {
							$xinters = ( $point['lng'] - $p1['lng'] ) * ( $p2['lat'] - $p1['lat'] ) / ( $p2['lng'] - $p1['lng'] ) + $p1['lat'];
							if ( $p1['lat'] == $p2['lat'] || $point['lat'] <= $xinters ) {
								$result = !$result;
							}
						}
					}
				}
			}
			$p1 = $p2;
		}
		return $result;
	}

	/**
	 * Привязываем номер дома с координатами к зданию ЛПУ
	 * В первом случае это были подстанции СМП в Перми
	 *
	 * @param int $LpuBuilding_id
	 * @param int $KLHouseCoords_id
	 * @return type
	 */
	public function bindLpuBuildingHouse( $LpuBuilding_id, $KLHouseCoords_id ){
		// Проверим существование записи
		$sql = "
			SELECT
				LpuBuildingKLHouseCoordsRel_id as \"LpuBuildingKLHouseCoordsRel_id\"
			FROM LpuBuildingKLHouseCoordsRel
			WHERE LpuBuilding_id=:LpuBuilding_id
				AND KLHouseCoords_id=:KLHouseCoords_id
			LIMIT 1
		";

		$query = $this->db->query( $sql, array(
			'LpuBuilding_id' => $LpuBuilding_id,
			'KLHouseCoords_id' => $KLHouseCoords_id,
		) );

		if(is_object( $query )){
			$result = $query->result('array');

			if ( sizeof( $result ) ) {
				return;
			}
		}

		$data = getSessionParams();

		// Добавим запись
		$sql = "
			SELECT LpuBuildingKLHouseCoordsRel_id as \"LpuBuildingKLHouseCoordsRel_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_LpuBuildingKLHouseCoordsRel_ins(
				LpuBuilding_id := :LpuBuilding_id,
				KLHouseCoords_id := :KLHouseCoords_id,
				pmUser_id := :pmUser_id);
		";

		$query = $this->db->query( $sql, array(
			'LpuBuilding_id' => $LpuBuilding_id,
			'KLHouseCoords_id' => $KLHouseCoords_id,
			'pmUser_id' => $data['pmUser_id'],
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}

	}

	/**
	 * @return output JSON Список объектов с координатами
	 */
	public function getAllAvlUnitsWithCoords( $output = true , $assoc = FALSE ){
		return $this->getAllAvlUnits( self::FLAG_OBJ_BASE + self::FLAG_OBJ_LAST_MSG + self::FLAG_OBJ_GROUPS , $output, $assoc);
	}

	/**
	 * @return output JSON Список uhegg объектов
	 */
	public function getAllAvlGroupUnits( $output = true, $assoc = FALSE ){
		return $this->getAllAvlGroups( self::FLAG_OBJ_BASE + self::FLAG_OBJ_LAST_MSG , $output, $assoc);
	}

	/**
	 * @return JSON Список групп объектов
	 */
	public function getAllAvlGroups( $flag = 1, $output = true, $assoc = false ){
		$result = $this->searchItems( array(
			'spec' => array(
				'itemsType' => 'avl_unit_group',
				'propName' => 'sys_name',
				'propValueMask' => '*',
				'sortType' => 'sys_name',
			),
			'force' => 1,
			//'flags' => $flag,
			'flags' => 0x00000001+0x00000008,
			'from' => 0,
			'to' => 0xffffffff
		), $assoc );

		return $result;
	}

	/**
	 * @return JSON Список объектов
	 */
	public function getAllAvlUnits( $flag = 1, $output = true , $assoc = FALSE ){
		$result = $this->searchItems( array(
			'spec' => array(
				'itemsType' => 'avl_unit',
				'propName' => 'sys_name',
				'propValueMask' => '*',
				'sortType' => 'sys_name',
			),
			'force' => 1,
			'flags' => $flag,
			'from' => 0,
			'to' => 0xffffffff
		) , $assoc);

		return $result;
	}

	/**
	 * @return output массив объектов для привязки бригад
	 */
	public function getAllAvlUnitsForMergeData(){
		//$units = $this->getAllAvlUnits( self::FLAG_OBJ_BASE, false );
		$units = $this->getAllAvlUnits( self::FLAG_OBJ_BASE + self::FLAG_OBJ_LAST_MSG + self::FLAG_OBJ_GROUPS , false, true);
		//var_dump($units['items']); exit;
		if(isset($units['items'])) {return $units['items'];}
		else {return false;}
	}

	/**
	 * @return output JSON Список объектов для привязки бригад
	 */
	public function getAllAvlUnitsForMerge(){

		if ( empty($this->_api_url) ) {
			return false;
		}

		return $this->getAllAvlUnits( self::FLAG_OBJ_BASE, false );
	}

	/**
	 * Поиск элементов по заданным условиям
	 *
	 * @param string $itemsType Тип искомых элементов (см. список ниже)
	 *     avl_hw – тип оборудования;
	 *     avl_resource – ресурс;
	 *     avl_retranslator – ретранслятор;
	 *     avl_unit – объект;
	 *     avl_unit_group – группа объектов;
	 *     user – пользователь.
	 *
	 * @param string $propName Имя свойства, по которому будет осуществляться поиск (см. список возможных свойств ниже)
	 *     sys_name – имя элемента;
	 *     sys_unique_id – ID элемента;
	 *     sys_user_creator – ID создателя;
	 *     sys_comm_state – состояние оборудования (1 - подключено, 0 - отключено);
	 *     sys_hw_type – тип оборудования.
	 *
	 * @param string $propValueMask Значение свойства: может быть использован знак «*»
	 * @param string $sortType 	имя свойства, по которому будет осуществляться сортировка ответа
	 *
	 * @param string $propType Тип свойства (см. список типов ниже)
	 *     property – свойство;
	 *     guid – уникальный идентификатор;
	 *     list – список;
	 *     propitemname – имя подэлемента (например геозона является подэлементом ресурса);
	 *     customfield – произвольные поля;
	 *     creatortree – цепочка создателей (поиск с таким типом вернет список элементов, у которых в цепочке создателей есть создатель, указанный в условии поиска);
	 *     accounttree – цепочка учетных записей (поиск с таким типом вернет список элементов, у которых в цепочке учетных записей есть учетная запись, указанная в условии поиска).
	 *
	 * @params integer $force "0" - если такой поиск уже запрашивался, то вернет полученный результат, "1" - будет искать заново
	 * @params long $flags Флаги видимости для возвращаемого результата (Значение данного параметра зависит от типа элемента, который вы хотите найти. Форматы всех элементов, а так же их флаги описаны в разделе Форматы данных.)
	 * @params integer $from Индекс, начиная с которого возвращать элементы результирующего списка
	 * @params loing $to Индекс последнего возвращаемого элемента (0xffffffff - последний найденный)
	 */
	public function searchItems(array $params, $assoc = FALSE){

		if ($this->getSessionId()) {

			$data = array(
				'svc' => 'core/search_items',
				'params' => json_encode($params),
				$this->_session_key => $this->getSessionId()
			);

			return json_decode($this->_httpQuery($data), $assoc);

		} else {

			return false;
		}
	}

	/**
	 * @return string ИД сессии Wialon или false
	 */
	public function getSessionId(){

		if (isset($_SESSION['wialon'][$this->_session_key]))
			return $wialon_ssid = $_SESSION['wialon'][$this->_session_key];

		return false;
	}

	/**
	 * HTTP запрос к Виалону
	 *
	 * @param array $data набор параметров для запроса
	 * @return string
	 * @throws Exception
	 */
	protected function _httpQuery( $data ){
		$data = http_build_query( $data );
		//$data_string = json_encode( $data ) ;

		$ch = curl_init() ;

		curl_setopt( $ch , CURLOPT_URL ,  $this->_api_url.'?'.$data  ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		//curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , "POST" ) ;
		//curl_setopt( $ch , CURLOPT_POSTFIELDS , $data ) ;
		curl_setopt( $ch , CURLOPT_HTTPHEADER , array(
			'Accept: */*' ,
			'Accept-Charset: UTF-8,*;q=0.5' ,
			'Accept-Encoding: deflate' ,
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		) ) ;

		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			//curl_setopt($ch, CURLOPT_PROXY, '192.168.37.18:8080');
		}

		session_write_close() ;
		$fp = curl_exec( $ch ) ;
		session_start() ;



		/*
		// Подключаемся
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->_api_url.'?'.$data );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}
		//session_write_close();
		//$fp = curl_exec( $ch );
		//session_start();
		// ... если не получается, возможно мы сидим через прокси

		if (!$fp && $this->config->item( 'IS_DEBUG' ) ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->_api_url.'?'.$data);
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, ''.':'.'');
			//curl_setopt($ch, CURLOPT_PROXYPORT, 8080);
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');

			//$fp = curl_exec($ch);
		}

		session_write_close() ;
		$fp = curl_exec( $ch ) ;
		session_start() ;

		curl_close( $ch ) */

		if(isset($_GET['getDebug'])){
			echo 'Error Code:' . curl_errno( $ch ) . ' Error Text:' . (curl_error( $ch ));

			echo "<pre>";
			print_r(curl_getinfo( $ch ));
			echo "</pre>";
		}

		if ( !$fp ) {
			curl_close( $ch ) ;
			throw new Exception( "Error loading '".$this->_api_url."',"/* ".$php_errormsg */);
		}
		curl_close( $ch ) ;
		return $fp;
	}

	/**
	 * Инициализация
	 * Метод вызывается перед тем, как контроллер будет выполнен
	 * Вы можете переопределить этот метод для реализации собственной
	 * инициализации контролера
	 *
	 * @return void
	 */
	public function init($authByLoggedUser = true, $data = null){

		if ($authByLoggedUser)
			return $this->checkAuth();
		else {

			$auth = $this->authByDepartment($data);

			if (!$auth) {
				if ($this->getSessionId())
					unset($_SESSION['wialon'][$this->_session_key]);
			}

			return $auth;
		}
	}

	/**
	 * Проверка авторизации в Виалоне
	 * Вы можете переопределить этот медот для реализации собственной авторизации
	 */
	public function checkAuth(){

		// Теперь у каждого региона свои настройки авторизации

	}

	/**
	 * Авторизации по выбранному подразделению
	 */
	public function authByDepartment($data){

		$auth = false;
		$response = $this->getWialonCredentialsByLpuDepartment($data);

		if (count($response) > 0) {

			if (isset($response[0]['MedService_WialonLogin']) || isset($response[0]['MedService_WialonToken'])) {

				$wialon_login = isset($response[0]['MedService_WialonLogin']) ? trim($response[0]['MedService_WialonLogin']) : null;
				$wialon_passwd = isset($response[0]['MedService_WialonPasswd']) ? trim($response[0]['MedService_WialonPasswd']) : null;
				$wialon_token = isset($response[0]['MedService_WialonToken'])
						? trim($response[0]['MedService_WialonToken']) : null;

				if (!empty($wialon_login) || !empty($wialon_token)) {

					$credentials = array(

						'wialon_login' => $wialon_login,
						'wialon_passwd' => $wialon_passwd,
						'wialon_token' => $wialon_token

					);
					$this->_user = ($wialon_login) ? $wialon_login : null;
					$this->_password = ($wialon_passwd) ? $wialon_passwd: null;
					$this->_token = ($wialon_token) ? $wialon_token : null;
					
					$auth = $this->login($credentials);
				}
			}
		}		
		return $auth;
	}

	/**
	 * Поиск элемента по ID
	 */
	public function searchItem(){
		die( 'Method '.__METHOD__.' under construction' );
		$data = array(
			'svc' => 'core/search_item',
			'params' => json_encode( array(
				'id' => null,
				'flags' => null
			) ),
			$this->_session_key => $this->getSessionId()
		);
		$result = json_decode( $this->_httpQuery( $data ) );
	}

	/**
	 * Парсер координат по адресу
	 */
	public function parseAddressCoordinates(){
		set_time_limit(0);

		//
		// Парсер координат с Wialon
		//

		$city_name = 'Пермь';

		$data = array(
			'svc' => 'gis/search_cities',
			'params' => '{"name":"'.$city_name.'","mapTags":""}',
			$this->_session_key => $this->getSessionId()
		);
		$cities = json_decode( $this->_httpQuery( $data ) );
		if ( !$cities ) {
			echo "Can't find any city.";
			exit;
		}

		$this->load->library('textlog', array('file'=>'WialonAddressParser.log'));

		$page = isset( $_GET['page'] ) ? (int)$_GET['page'] : 1;

		// ID города в КЛАДРе
		$kl_city_id = 3310; // Пермь

		$total_houses_find = 0;
		$total_houses = 0;

		/*
		$list = $this->getHousesByCityId( $kl_city_id, $page );
		foreach( $list as $k => $v ) {
			if ( !$v['KLHouse_id'] ) {
				continue;
			}

			$log = "Searching street: ".$v['KLStreet_Name'];
			// $this->textlog->add($log);
			echo $log."<br />\n";

			$streets = array();
			foreach( $cities as $city ) {
				// Получаем идентификатор улицы
				$data = array(
					'svc' => 'gis/search_streets',
					'params' => '{"name":"'.$v['KLStreet_Name'].'","cityId":"'.$city->id.'"}',
					$this->_session_key => $this->getSessionId()
				);
				$result = json_decode( $this->_httpQuery( $data ) );
				if ( $result ) {
					$streets = array_merge( $streets, $result );
				}
			}

			if ( !sizeof( $streets ) ) {
				$log = "Can't find any street.";
				// $this->textlog->add($log);
				echo $log."<br />\n";
				continue;
			} else {
				$log = "Total: ".sizeof($streets).".";
				// $this->textlog->add($log);
				echo $log."<br />\n";
			}


			$log = "Parse houses nums: ".$v['KLHouse_Name'].'.';
			// $this->textlog->add($log);
			echo $log."<br />\n";

			$houses_nums = $this->decompileHouseName( $v['KLHouse_Name'] );
			if ( !$houses_nums ) {
				$log = "Can't decompile any house num.";
				// $this->textlog->add($log);
				echo $log."<br />\n";
				continue;
			} else {
				$log = "Decompiled: ".implode(',',$houses_nums).". Total: ".sizeof( $houses_nums ).".";
				// $this->textlog->add($log);
				echo $log."<br />\n";
				$total_houses += sizeof( $houses_nums );
			}

			foreach( $houses_nums as $num ){
				// Сперва проверим есть ли запись в базе и если есть ничего не будем делать
				if ( $this->checkIfHouseLatLngExists( $num, $v['KLHouse_id'], $v['KLStreet_id'], $v['KLArea_id'] ) ) {
					$total_houses_find++;
					$log = 'Already exists house '.$num.' (KLStreet_id '.$v['KLStreet_id'].').';
					// $this->textlog->add($log);
					echo $log."<br />\n";
					continue;
				}
				echo '<pre>';

				$houses = array();
				foreach( $streets as $street ) {
					$log = 'Searching process: '.$street->text.' ('.$street->id.'), house '.$num.'.';
					// $this->textlog->add($log);
					echo $log."<br />\n";

					// Получаем данные дома
					$data = array(
						'svc' => 'gis/search_houses',
						'params' => '{"name":"","streetId":"'.$street->id.'"}',
						$this->_session_key => $this->getSessionId()
					);
					$result = json_decode( $this->_httpQuery( $data ) );
					if ( $result ) {
						$houses = array_merge( $houses, $result );
						$log = 'Added to houses nums array.';
						// $this->textlog->add($log);
						echo $log."<br />\n";
					}
					var_dump( $result );
				}
				exit;

				if ( !sizeof( $houses ) ) {
					$log = 'Can\'t find any house: '.$num.'.';
					// $this->textlog->add($log);
					echo $log."<br />\n";
					continue;
				}

				foreach( $houses as $house ) {
					if ( $house->text == $num ) {
						$log = 'Find.';
						// $this->textlog->add($log);
						echo $log."<br />\n";
						$this->saveHouseLatLng( $house->lat, $house->lon, $house->text, $v['KLHouse_id'], $v['KLStreet_id'], $v['KLArea_id'] );
						$total_houses_find++;
						break;
					} else {
						$log = 'Not find ('.$house->text.' <> '.$num.').';
						// $this->textlog->add($log);
						echo $log."<br />\n";
					}
				}
			}
		}
		*/

		echo '<pre>';

		$list = $this->getStreetsByCityId( $kl_city_id );
		foreach( $list as $v ) {
			$streets = array();
			foreach( $cities as $city ) {
				// Получаем идентификатор улицы
				$data = array(
					'svc' => 'gis/search_streets',
					'params' => '{"name":"'.$v['KLStreet_Name'].'","cityId":"'.$city->id.'"}',
					$this->_session_key => $this->getSessionId()
				);
				$result = json_decode( $this->_httpQuery( $data ) );
				if ( $result ) {
					$streets = array_merge( $streets, $result );
				}
			}

			if ( !sizeof( $streets ) ) {
				echo "Street '".$v['KLStreet_Name']."' was not found<br />\n";
				continue;
			}

			$houses = array();
			foreach( $streets as $street ) {
				$log = 'Searching houses for street: '.$street->text.' ('.$street->id.').';
				// $this->textlog->add($log);
				echo $log."<br />\n";

				// Получаем данные дома
				$data = array(
					'svc' => 'gis/search_houses',
					'params' => '{"name":"","streetId":"'.$street->id.'"}',
					$this->_session_key => $this->getSessionId()
				);
				$result = json_decode( $this->_httpQuery( $data ) );
				if ( $result ) {
					$houses = array_merge( $houses, $result );
				}
				$log = 'Houses founded: '.sizeof( $result ).'.';
				// $this->textlog->add($log);
				echo $log."<br />\n";
			}

			if ( !sizeof( $houses ) ) {
				echo "Was not found any houses numbers.<br />\n";
				continue;
			}

			$total_houses_find += sizeof( $houses );

			foreach( $houses as $house ) {
				$this->saveHouseLatLng( $house->lat, $house->lon, $house->text, null, $v['KLStreet_id'], $v['KLArea_id'] );
			}

		}

		$this->textlog->add("Find ".$total_houses_find." houses from ".$total_houses." on page ".$page.".");

		echo 'Done';
		/*
		if ( sizeof ( $list ) ) {
			$url = '/?c=Wialon&m=parseAddressCoordinates&page='.(++$page);
			echo '
				<html>
				<head>
				</head>
				<body>
					<script type="text/javascript">
						setTimeout(\'location.replace("'.$url.'")\', 1000);
					</script>
					<a href="'.$url.'" title="">Redirect next page result.</a>
				</body>
				</html>
			';
		} else {
			echo 'Done';
		}
		 *
		 */

		/*
		//
		// Парсер координат с Яндекс карт
		//

		// ID города в КЛАДРе
		$kl_city_id = 3310; // Пермь

		$list = $this->getHousesByCityId( $kl_city_id );
		if ( !$list ){
			echo 'Empty houses list.';
			exit;
		}

		foreach( $list as $k => $v ) {
			if ( !$v['KLHouse_id'] ) {
				continue;
			}

			$houses_nums = $this->decompileHouseName( $v['KLHouse_Name'] );
			if ( !$houses_nums ) {
				continue;
			}

			foreach( $houses_nums as $num ){
				$query = http_build_query( array( 'geocode' => 'Пермь, '.$v[ 'KLStreet_Name' ].', '.$num ) );
				echo 'http://geocode-maps.yandex.ru/1.x/?format=json&'.$query."<br />\n";
				$data = file_get_contents( 'http://geocode-maps.yandex.ru/1.x/?format=json&'.$query );
				$result = json_decode( $data );
				if ( !$result ) {
					continue;
				}

				// Обходим полученные результаты
				foreach( $result->response->GeoObjectCollection->featureMember as $v ) {
					// Используем только точное совпадение
					if ( $v->GeoObject->metaDataProperty->GeocoderMetaData->kind == 'house' && $v->GeoObject->metaDataProperty->GeocoderMetaData->precision == 'exact' ) {
						list( $lng, $lat ) = explode( ' ', $v->GeoObject->Point->pos );
						$this->saveHouseLatLng( $v['KLArea_id'], $v['KLStreet_id'], $v['KLHouse_id'], $v[''] );
					}
				}
			}
		}
		*/
	}

	/**
	 * конверт массива координат
	 * возвращает адреса
	 */
	public function geocodeCoords($data){
		$params = array(
			'svc'		=> 'gis/get_locations',
			'params'    => '{"points":'.$data['coords'].'}',
			$this->_session_key => $this->getSessionId()
		);
		$result = json_decode( $this->_httpQuery( $params ) );
		return $result;
	}

	/**
	 * Авторизация
	 *
	 * @return output JSON
	 */
	public function login($credentials = array()){

		// если авторизация через выбранную службу, а не через выполнившего вход пользователя СМП
		if ($credentials) {

			// переопределяем учетные данные
			$this->_user = $credentials['wialon_login'];
			$this->_password = $credentials['wialon_passwd'];
			$this->_token = $credentials['wialon_token'];
		}

		$data = array(
			'svc' => 'core/login',
			'params' => json_encode( array(
				'user' => $this->_user,
				'password' => $this->_password
			))
		);

		$result = json_decode( $this->_httpQuery( $data ) );

		if (empty($result) || array_key_exists( 'error', $result ) ) {
			//unset($_SESSION['wialon'][$this->_session_key]);
			return false;
		} else {
			$_SESSION['wialon'][$this->_session_key] = (string) $result->{$this->_session_key};
			return true;
		}
	}

	/**
	 * Выход
	 */
	public function logout(){

		if ($this->getSessionId()) {

			$data = array(
				'svc' => 'core/logout',
				'params' => '{}',
				$this->_session_key => $this->getSessionId()
			);

			$this->_httpQuery($data);
		}
	}

	/**
	 * Получение строк из таблиц отчета
	 * @param type $tableIndex порядковый номер таблицы отчета (начинается с 0)
	 * @param type $indexFrom порядковый номер строки, с которой начать выборку (начинается с 0)
	 * @param type $indexTo порядковый номер строки, которой закончить выборку (начинается с 0)
	 * @return array
	 */
	protected function _getReportRows( $tableIndex = 0 , $indexFrom = 0 , $indexTo = 0 ) {

		$data = array(
			'svc' => 'report/get_result_rows' ,
			'params' => json_encode( array(
				'tableIndex' => ( int ) $tableIndex ,
				'indexFrom' => ( int ) $indexFrom ,
				'indexTo' => ( int ) $indexTo ,
			) ) ,
			$this->_session_key => $this->getSessionId()
		) ;

		return json_decode( $this->_httpQuery( $data ) , true ) ;
	}

	/**
	 *
	 * @param array $params
	 * 		reportResourceId - ID ресурса
	 * 		reportTemplateId - ID шаблона
	 * 		reportObjectId - ID элемента
	reportObjectSecId - ID подэлемента, например геозоны или уведомления, если у элемента нету подэлементов, то 0
	interval - настройки интервала отчета
	from - начало интервала, UNIX-время
	to - окончание интервала, UNIX-время
	flags - флаги интервала
	 *
	 * @return
	 */
	public function getReport( array $params ) {

		//
		//Прежде чем получать новый отчет, необходимо очистить сессию виалона от предыдущего отчета
		//
		//http://sdk.wialon.com/wiki/ru/kit/remoteapi/apiref/report/exec_report
		//
		//Очищаем сессию от предыдущего отчета

		$cleanUpReportResult = $this->cleanUpReport() ;
		if ( !is_object($cleanUpReportResult) || !property_exists( $cleanUpReportResult , 'error' ) || ( (int)$cleanUpReportResult->error !== 0 ) ) {
			return array(
				array(
					'success' => FALSE ,
					'Error_Msg' => 'При очистке прерыдущего отчета сессии виалон произошла ошибка. ' . ( is_object($cleanUpReportResult)  && property_exists( $cleanUpReportResult , 'error' ) ? ('Код: ' . $cleanUpReportResult->error) : '')
				)
			) ;
		}

		//Запрашиваем необходимый отчет

		$data = array(
			'svc' => 'report/exec_report' ,
			'params' => json_encode( $params ) ,
			$this->_session_key => $this->getSessionId()
		) ;

		return json_decode( $this->_httpQuery( $data ), true ) ;
	}

	/**
	 * Метод получения инициализационного массива для создания шаблона для отчета по ГСМ
	 * @return array
	 */
	protected function _getGasReportTemplateInitConfig() {

		$resource_id = $this->getResourceId() ;

		if ( !$resource_id ) {
			return false ;
		}

		$columns = $this->_getGasReportRequiredTblColumns() ;

		return array(
			'itemId' => $resource_id ,
			'callMode' => 'create' ,
			'n' => self::GAS_REPORT_TEMPLATE_NAME ,
			'ct' => self::GAS_REPORT_TEMPLATE_TYPE ,
			'p' => '' ,
			'tbl' => array(
				array(


					'n' => self::GAS_REP_TBL_NAME ,
					'l' => self::GAS_REP_TBL_DISPL_NAME,
					'c' => implode( ',' , array_keys( $columns ) ) ,
					'cl' => implode( ',' , $columns ),
					's' => '' ,
					'sl' => '' ,
					'p' => '{"base_eh_sensor":{"mask":"*"},"custom_interval":{"type":"0"},"sensor_name":"*","duration_format":"0"}' ,
					'sch' => array(
						'f1' => 0 ,
						'f2' => 0 ,
						't1' => 0 ,
						't2' => 0 ,
						'm' => 0 ,
						'y' => 0 ,
						'w' => 0
					) ,
					'f' => 0
				)
			)
		);

	}



	/**
	 * Метод поиска идентификатора шаблона для отчета Wialon по затратам ГСМ
	 * @return boolean|int
	 */
	public function _getWaybillGasReportTemplateId() {

		$all_templates_result = $this->getAllTemplates();

		if ( empty( $all_templates_result[ 'rep' ] ) || !is_array( $all_templates_result[ 'rep' ]) ) {

			return array( 'Error_Msg' => 'При получени списка шаблонов произошла ошибка.' . ( (empty( $all_templates_result[ 'error' ] )) ? '' : ' Код ' . $all_templates_result[ 'error' ] ) );
		}

		$all_tpl_data = $all_templates_result[ 'rep' ];

		$gas_report_template_name = self::GAS_REPORT_TEMPLATE_NAME;
		$gas_report_template_type = self::GAS_REPORT_TEMPLATE_TYPE;
		$gas_report_template_found = false;
		$gas_report_template_id = NULL;


		reset($all_tpl_data);
		while ( ( list( , $tpl_data) = each( $all_tpl_data ) ) && !$gas_report_template_found ) {
			if ($tpl_data[ 'nm' ] === $gas_report_template_name) {
				$gas_report_template_found = true ;
				if ($tpl_data[ 'ct' ] === $gas_report_template_type) {
					$gas_report_template_id = $tpl_data[ 'id' ] ;
				}
			}
		}

		if ( !$gas_report_template_found ) {
			return array( 'Error_Msg' => 'Шаблон "Расход топлива для РИАМС" не найден в Wialon', 'tplnotfound' );
		} elseif ( $gas_report_template_found && ($gas_report_template_id === NULL) ) {
			return array( 'Error_Msg' => 'Шаблон "Расход топлива для РИАМС" не соответствует необходимому типу', 'wrongtpltype' );
		}

		return $gas_report_template_id;

	}

	/**
	 * Возвращает список обязательных столбцов для таблицы в отчете Wialon по ГСМ
	 * @return array
	 */
	protected function _getGasReportRequiredTblColumns(  ) {
		return array(
			'fuel_consumption_math'=>'Потрачено по расчету',
			'fuel_consumption_rates'=>'Потрачено по нормам',
			'fuel_consumption_imp'=>'Потрачено по ДИРТ',
			'fuel_consumption_abs'=>'Потрачено по ДАРТ',
			'fuel_consumption_ins'=>'Потрачено по ДМРТ',
			'fuel_consumption_fls'=>'Потрачено по ДУТ',
		);
	}

	/**
	 * Метод проверки шаблона отчета по ГСМ на наличие в нем необходимых таблиц
	 * @param type $template_id идентификатор шаблона
	 * @return boolean
	 */
	protected function _checkRequiredTablesInGasReportTemplate( $template_id ) {

		$template_data = $this->_getTemplatesData(array($template_id));

		if (!$template_data) {
			return array( 'Error_Msg' => 'При получени информации по шаблону "Расход топлива для РИАМС" произошла ошибка, обратитесь к администратору' );
		}

		if (empty( $template_data[ 0 ][ 'tables' ] ) || !is_array( $template_data[0]['tables']) || !sizeof( $template_data[ 0 ][ 'tables' ] )) {
			return array( 'Error_Msg' => 'При получени информации по шаблону "Расход топлива для РИАМС" произошла ошибка, обратитесь к администратору' );
		}

		$tables = $template_data[ 0 ][ 'tables' ];
		$tbl_found = false; //Флаг найденной таблицы

		while ( ( list( , $tbl) = each( $tables ) ) && !$tbl_found ) {

			if (!empty($tbl['name']) && ($tbl['name'] === self::GAS_REP_TBL_NAME)) {

				$tbl_found = true;

				if (!$this->_checkColumnsInTplTable($tbl,  $this->_getGasReportRequiredTblColumns() , $missing_columns)) {
					return array( 'Error_Msg' => 'В таблице отчета отсутствуют или переименованы необходимые поля: </br>' . implode( '</br>', $missing_columns ) );
				}
			}
		}

		if (!$tbl_found) {
			return array( 'Error_Msg' => 'В шаблоне Wialon для получения данных о ГСМ отсутствует необходимая таблица:' .self::GAS_REP_TBL_DISPL_NAME );
		}

		return true;

	}

	/**
	 * Проверка наличия в таблице необходимых полей
	 * @param type $table массив с параметрами таблицы
	 * @param type $required_columns массив необходимых столбцов в формате [<системное имя> => <отображаемое имя>]
	 * @param type $missing_columns массив недостающих столбцов в формате [<системное имя> => <отображаемое имя>]
	 * @return boolean
	 */
	protected function _checkColumnsInTplTable( $table = array( ) , $required_columns = array( ) , &$missing_columns = array( ) ) {

		if ( empty( $table[ 'columns' ] ) ) {
			return false ;
		}

		$column_names = explode( ',' , $table[ 'columns' ] ) ; //Системные имена столбцов
		$column_display_names = explode( ',' , $table[ 'column_names' ] ) ; //Отображаемые имена столбцов (могут быть изменены)

		// В принципе быть не должно, но проверить необходимо
		if (sizeof($column_names) !== sizeof($column_display_names)) {
			return false;
		}

		//Флаг успешного нахождения всех необходимых столбцов
		$all_columns_found = false ;

		//Инициализация массива ненайденных полей (по умолчанию не найдено ни одного)
		$missing_columns = $required_columns;

		for	($i=0;$i < sizeof($column_names) && !$all_columns_found;$i++) {

			$column_found = false ; //Флаг нахождения текущего искомого столбца в наборе

			reset($required_columns);
			while ( ( list( $field_name , $field_displ_name ) = each( $required_columns ) ) && !$column_found ) {

				//Сравниваем и системные и отображаемые имена, поскольку в
				//получаемом отчете участвуют только отображаемые имена,
				//которые можно редактировать

				if ( $field_name === $column_names[ $i ] && $field_displ_name === $column_display_names[ $i ]) {
					$column_found = true ;
				}
			}

			// Если нашли столбец, удаляем его из массива ненайденный столбцов
			if ( $column_found ) {
				unset( $missing_columns[  $column_names[ $i ]  ] ) ;
			}

			// Если из массив ненайденных столбцов пуст, значит нашли все столбцы
			if ( !sizeof( $missing_columns ) ) {
				$all_columns_found = true ;
			}
		}

		return $all_columns_found ;
	}


	/**
	 * Получение информации по списку шаблонов
	 * @param type $template_id_arr массив идентификаторов шаблонов
	 * @return boolean|array
	 */
	protected function _getTemplatesData( $template_id_arr = array() ) {


		$resource_id = $this->getResourceId();

		if (!$resource_id) {
			return false;
		}

		$data = array(
			'svc' => 'report/get_report_data' ,
			'params' => json_encode( array(
				'itemId' => $resource_id,
				'col' => $template_id_arr
			) ) ,
			$this->_session_key => $this->getSessionId()
		) ;

		return json_decode( $this->_httpQuery( $data ), TRUE ) ;

	}

	/**
	 * Метод создания шаблона для отчета по ГСМ
	 * @return array
	 */
	public function createWayBillGasReportTemplate() {
		$data = array(
			'svc' => 'report/update_report',
			'params' => $this->custom_json_encode(
				$this->_getGasReportTemplateInitConfig()
			),
			$this->_session_key => $this->getSessionId()
		);

		$result = json_decode( $this->_httpQuery( $data ), TRUE );

		return $result;
	}

	/**
	 * Получение списка ресурсов
	 * @param type $output
	 * @return type
	 */
	public function getAllResources( $output = true ) {
		$result = $this->searchItems( array(
			'spec' => array(
				'itemsType' => 'avl_resource',
				'propName' => 'sys_id',
				'propValueMask' => '*',
				'sortType' => 'sys_id',
			),
			'force' => 1,
			'flags' => 0x00000001,
			'from' => 0,
			'to' => 0xffffffff
		) );
		return $result;
	}

	/**
	 * Получение идентификатора первого ресурса
	 * @return boolean
	 */
	protected function getResourceId() {

		$resources = $this->getAllResources( false );

		if ( !is_object($resources) || !property_exists( $resources , 'items' ) || !is_array( $resources->items ) || !sizeof( $resources->items ) ) {
			return false;
		}

		$resource = $resources->items[0];

		if ( !is_object( $resource ) || !property_exists( $resource , 'id' ) ) {
			return false;
		}

		return $resource->id;

	}

	/**
	 * Метод кастомной перекодировки из utf
	 * необходим для корректного сохранения русских символов
	 * @return array
	 */
	private function custom_json_encode($arr)
	{
		//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	/**
	 * Очистка предыдущего отчета из сессии виалон
	 * @return array ( 'error' -> код ошибки при очистке предыдущего отчета из сессии виалон )
	 */
	public function cleanUpReport() {

		$data = array(
			'svc' => 'report/cleanup_result' ,
			'params' => json_encode( array( ) ) ,
			$this->_session_key => $this->getSessionId()
		) ;

		return json_decode( $this->_httpQuery( $data ) ) ;
	}

	/**
	 * Получение списка шаблонов
	 * @param type $output
	 * @return type
	 */
	public function getAllTemplates( $output = true ) {
		$resource_id = $this->getResourceId();

		$data = array(
			'svc' => 'core/search_item',
			'params' => json_encode( array(
				'itemId' => $resource_id,
				'flags'=>0x00008000
			) ),
			$this->_session_key => $this->getSessionId()
		);

		return json_decode( $this->_httpQuery( $data ), true );
	}
	
	/**
	 * получение ID нужного шаблона
	 */
	public function _getSummaryReportStdMileagetTemplateId($param) {
		if( empty($param) || !$param['name'] || !$param['type']) {
			return false;
		}
		$all_templates_result = $this->getAllTemplates();
		$all_tpl_data = $all_templates_result[ 'rep' ];		
		if(!$all_tpl_data) return false;
		
		$std_report_template_name = $param['name'];
		$std_report_template_type = $param['type'];
		$std_report_template_found = false;
		$std_report_template_id = NULL;
		
		reset($all_tpl_data);
		while ( ( list( , $tpl_data) = each( $all_tpl_data ) ) && !$std_report_template_found ) {
			if ($tpl_data[ 'nm' ] === $std_report_template_name) {
				$std_report_template_found = true ;
				if ($tpl_data[ 'ct' ] === $std_report_template_type) {
					$std_report_template_id = $tpl_data[ 'id' ] ;
				}
			}
		}

		if ( !$std_report_template_found ) {
			return array( 'Error_Msg' => 'Шаблон "'.$param['name'].'" не найден в Wialon', 'tplnotfound' );
		} elseif ( $std_report_template_found && ($std_report_template_id === NULL) ) {
			return array( 'Error_Msg' => 'Шаблон "'.$param['name'].'" не соответствует необходимому типу', 'wrongtpltype' );
		}

		return $std_report_template_id;
	}
	
	/**
	 * Получение отчета 'СВОДНЫЙ ОТЧЕТ STD пробег'
	 */
	public function getSummaryReportStdMileage($param) {
		/* у каждого региона свои шаблоны отчетов */
		return false;
	}

	/**
	 * получение дистанции из отчета
	 */
	public function getTheDistanceTraveled($param){
		/* у каждого региона свои шаблоны отчетов */
		//получим нужный нам отчет
		$report = $this->getSummaryReportStdMileage($param);
		// возьмем необходимый параметр - пройденное расстояние
		if( !empty($report['km']) && $report['km'] ){
			return $report['km'];
		}else{
			return false;
		}
	}
	
	/**
	 * сделано для астрахани, для тестирования подключения.
	 */
	public function loginTest($params){
		//сделано для астрахани, для тестирования подключения.
		if( $params['loginTest'] == 'perm' ){
			if( empty($params['login']) || empty($params['password'])) return false;
			$data = array(
				'svc' => 'core/login',
				'params' => json_encode( array(
					'user' => $params['login'],
					'password' => $params['password']
				))
			);
		}elseif ($params['loginTest'] == 'krym' || $params['loginTest'] == 'astra') {
			if( empty($params['token']) ) return false;
			$data = array(
				'svc' => 'token/login',
				'params' => json_encode( array(
					'token' => $params['token']
				) )
			);
		}else{
			return false;
		}
		$data = http_build_query( $data );
		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL ,  $this->_api_url.'?'.$data  ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		//curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , "POST" ) ;
		//curl_setopt( $ch , CURLOPT_POSTFIELDS , $data ) ;
		curl_setopt( $ch , CURLOPT_HTTPHEADER , array(
			'Accept: */*' ,
			'Accept-Charset: UTF-8,*;q=0.5' ,
			'Accept-Encoding: deflate' ,
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		) ) ;

		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			//curl_setopt($ch, CURLOPT_PROXY, '192.168.37.18:8080');
		}

		session_write_close() ;
		$fp = curl_exec( $ch ) ;
		session_start() ;
		curl_close( $ch ) ;
		$result = json_decode( $fp );
		return ($result) ? $result : array('fp'=>$fp);
	}

	/**
	 * Возвращает данные для авторизации по medservice_id
	 * @param $data
	 * @return array
	 */
	public function getAccessDataByMedService($data){
		if(empty($data['MedService_id'])){
			return false;
		}

		$sql = "
				SELECT
					ms.MedService_id as \"MedService_id\",
					ms.MedService_WialonLogin as \"MedService_WialonLogin\",
					ms.MedService_WialonPasswd as \"MedService_WialonPasswd\",
					ms.MedService_WialonToken as \"MedService_WialonToken\"
				FROM
					v_MedService ms 

				WHERE
					ms.MedService_id = :MedService_id
				";

		$query = $this->db->query( $sql, array(
			'MedService_id' => $data['MedService_id']
		) );

		if ( is_object( $query ) ) {
			return $query->row_array();
		}
	}
}