<?php

/**
 * Interview_Model - модель для работы с формой опроса пользователей
 *
 */
class TabletComputers_model extends SwPgModel
{

    protected $schema = "dbo";  //региональная схема

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();

        //установка региональной схемы
        $config = get_config();
        if ($this->regionNick == 'kz' || $this->regionNick == 'ufa') {
            $this->schema = $config['regions'][getRegionNumber()]['schema'];
        }
    }

	/**
	 * Сохранение Планшетного компьютера
	 */
	public function saveTabletComputer($data) {
		//Проверим уникальность IMEI
		$sql = "
			SELECT CMPTabletPC_id as \"CMPTabletPC_id\"
			FROM v_CMPTabletPC
			WHERE CMPTabletPC_Code = :CMPTabletPC_Code
				and CMPTabletPC_id != COALESCE(:CMPTabletPC_id, 0)
            limit 1
		";

		$res = $this->db->query($sql, array(
			'CMPTabletPC_Code' => $data['CMPTabletPC_Code'],
			'CMPTabletPC_id' => (!empty($data['CMPTabletPC_id']) ? $data['CMPTabletPC_id'] : null)
		));

		if (count($res->result('array')) > 0) {
			return array('Error_Msg' => 'Планшетный компьютер с таким IMEI уже существует');
		}

		$queryParams = array(
			'CMPTabletPC_id' => (!empty($data['CMPTabletPC_id']) ? $data['CMPTabletPC_id'] : null),
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'CMPTabletPC_Code' => $data['CMPTabletPC_Code'],
			'CMPTabletPC_Name' => $data['CMPTabletPC_Name'],
			'CMPTabletPC_SIM' => $data['CMPTabletPC_SIM'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select 
			    CMPTabletPC_id as \"CMPTabletPC_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_CMPTabletPC_" . (empty($data['CMPTabletPC_id']) ? "ins" : "upd") . " (
				CMPTabletPC_id := :CMPTabletPC_id,
				LpuBuilding_id := :LpuBuilding_id,
				CMPTabletPC_Code := :CMPTabletPC_Code,
				CMPTabletPC_Name := :CMPTabletPC_Name,
				CMPTabletPC_SIM := :CMPTabletPC_SIM,
				pmUser_id := :pmUser_id
				)
		";
		$result = $this->db->query($query, $queryParams);
		return $result->result('array');
	}

	/**
	 * Удаление Планшетного компьютера
	 */
	public function deleteTabletComputer($data) {
		//переделать на выбор бригады к которой привязан планшет
		$sql = "
			SELECT et.EmergencyTeam_Num as \"EmergencyTeam_Num\"
			FROM v_EmergencyTeam et
			INNER JOIN v_EmergencyTeamDuty etd on etd.EmergencyTeam_id = et.EmergencyTeam_id and etd.EmergencyTeamDuty_isClose != 2
			WHERE CMPTabletPC_id = :CMPTabletPC_id
			limit 1
		";
		$query = $this->db->query($sql, array(
			'CMPTabletPC_id' => $data['CMPTabletPC_id']
		));

		$res = $query->result('array');

		if ( is_array($res) && count($res) > 0 ) {
			return array('Error_Msg' => 'Планшетный компьютер в данный момент используется бригадой № ' . $res[0]['EmergencyTeam_Num'] . '. Удаление невозможно.');
		}

		$query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from p_CMPTabletPC_del (
				CMPTabletPC_id := :CMPTabletPC_id,
				pmUser_id := :pmUser_id
				)
		";
		$result = $this->db->query($query, $data);
		return $result->result('array');
	}

    /**
     * Получение списка планшетных компьютеров
     */
    public function loadTabletComputersList()
    {
        $data = getSessionParams();
        if (!array_key_exists('Lpu_id', $data) || !$data['Lpu_id']) {
            return array(array('Err_Msg' => 'Не указан идентификатор МО'));
        }

        $sql = "
			SELECT
				tpc.CMPTabletPC_id as \"CMPTabletPC_id\"
				,tpc.CMPTabletPC_Code as \"CMPTabletPC_Code\"
				,tpc.CMPTabletPC_Name as \"CMPTabletPC_Name\"
				,tpc.CMPTabletPC_SIM as \"CMPTabletPC_SIM\"
				,tpc.LpuBuilding_id as \"LpuBuilding_id\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
			FROM
				v_CMPTabletPC tpc
				left join v_LpuBuilding lb on lb.LpuBuilding_id = tpc.LpuBuilding_id
			WHERE
				lb.lpu_id = :Lpu_id
        ";

        $query = $this->db->query($sql, array(
            'Lpu_id' => $data['Lpu_id']
        ));
        if (!is_object($query)) {
            return false;
        }
        $response = $query->result('array');
        return $response;

    }

    /**
     * Получение планшетного компьютера по id
     */
    public function loadTabletComputer($data)
    {

        if (!array_key_exists('CMPTabletPC_id', $data) || !$data['CMPTabletPC_id']) {
            return array(array('Err_Msg' => 'Не указан идентификатор планшета'));
        }

        $sql = "
        SELECT
            tpc.CMPTabletPC_id as \"CMPTabletPC_id\"
            ,tpc.CMPTabletPC_Code as \"CMPTabletPC_Code\"
            ,tpc.CMPTabletPC_Name as \"CMPTabletPC_Name\"
            ,tpc.CMPTabletPC_SIM as \"CMPTabletPC_SIM\"
            ,tpc.LpuBuilding_id as \"LpuBuilding_id\"
        FROM
            v_CMPTabletPC tpc
        WHERE
          tpc.CMPTabletPC_id = :CMPTabletPC_id
        ";

        $query = $this->db->query($sql, array(
            'CMPTabletPC_id' => $data['CMPTabletPC_id']
        ));
        $response = $query->result('array');

        return $response;
    }
}