<?php
/**
* LpuRegion_model - модель для работы с участками, прикреплением
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
*/

class LpuRegion_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Определение типа прикрепления в зависимости от профиля врача
	 *
	 * @param array $data Стандартный массив с входящими параметрами
	 * @return int LpuAttachType_id
	 */
	public function defineLpuAttachTypeId($data) {
		// По умолчанию основной тип
		$LpuAttachType_id = 1;
		if (empty($data['LpuSectionProfile_Code']) && !empty($data['MedStaffFact_id'])) {
			$query = "
				select top 1
					LpuSectionProfile_Code
				from v_MedStaffFact msf (nolock)
				inner join v_LpuSection ls (nolock) on msf.LpuSection_id = ls.LpuSection_id
				where MedStaffFact_id = :MedStaffFact_id
			";
			$result = $this->db->query($query, array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (count($res)>0) {
					$data['LpuSectionProfile_Code'] = $res[0]['LpuSectionProfile_Code'];
				}
			}
		}
		if (empty($data['LpuSectionProfile_Code'])) {
			return $LpuAttachType_id;
		}
		switch ( $data['session']['region']['nick'] ) {
			case 'ufa':
			case 'perm':
				$_REGION = $this->config->item($data['session']['region']['nick']);
				if (in_array($data['LpuSectionProfile_Code'], $_REGION['GIN_LSP_CODE_LIST'] )) {
					//Гинекологический
					$LpuAttachType_id = 2;
				} else if (in_array($data['LpuSectionProfile_Code'], $_REGION['STOM_LSP_CODE_LIST'], true )) {
					// Стоматологический
					$LpuAttachType_id = 3;
				}
				break;
			default:
				// для других регионов
				if (substr($data['LpuSectionProfile_Code'], 0, 2) == '25') {
					//Гинекологический
					$LpuAttachType_id = 2;
				} else if (substr($data['LpuSectionProfile_Code'], 0, 2) == '18' || in_array($data['LpuSectionProfile_Code'], array('7181', '7182'))) {
					// Стоматологический
					$LpuAttachType_id = 3;
				}
				break;
		}
		return $LpuAttachType_id;
	}

	/**
	 * Получение списка участков врача в определенной МО с указанием типа участка и типа прикрепления
	 * Если будет передан код профиля или идентификатор рабочего места,
	 * то участки будут отфильтрованы по типу прикрепления в зависимости от профиля врача
	 * Входящие данные: $_POST['MedPersonal_id'] $_POST['Lpu_id'] $_POST['LpuSectionProfile_Code'] $_POST['MedStaffFact_id']
	 */
	public function getMedPersLpuRegionList($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$filters = '';
		$responseIfEmpty = array();

		if (!empty($data['LpuSectionProfile_Code']) || !empty($data['MedStaffFact_id'])) {
			$params['LpuAttachType_id'] = $this->defineLpuAttachTypeId($data);
			switch ($params['LpuAttachType_id']) {
				case 2: // гинеколог
					$params['LpuRegionType_id'] = 3; // Гинекологический
					$filters .= ' AND lr.LpuRegionType_id = :LpuRegionType_id';
					break;
				case 3: // Стоматологический
					$params['LpuRegionType_id'] = 5; //Стоматологический
					$filters .= ' AND lr.LpuRegionType_id = :LpuRegionType_id';
					break;
				case 4: // Служебный
					$params['LpuRegionType_id'] = 6; // Служебный
					$filters .= ' AND lr.LpuRegionType_id = :LpuRegionType_id';
					break;
				default:
					$filters .= ' AND lr.LpuRegionType_id not in (3,5,6)';
					break;
			}
			$params['LpuRegion_id'] = null;
			$responseIfEmpty[] = $params;
		}

		if(!empty($data['Ignore_Closed']) && $data['Ignore_Closed'] == 1)
		{
			$filters .= "
				and (msr.MedStaffRegion_endDate is null or msr.MedStaffRegion_endDate >= dbo.tzGetDate())
			";
		}

		$sql = "
			SELECT
				msr.LpuRegion_id as LpuRegion_id, -- Участок
				lr.LpuRegionType_id as LpuRegionType_id, -- Тип участка
				case
					when lr.LpuRegionType_id = 6  then 4
					when lr.LpuRegionType_id = 5  then 3
					when lr.LpuRegionType_id = 3  then 2
					else 1
				end as LpuAttachType_id -- Тип прикрепления
			FROM
				v_MedStaffRegion msr (nolock)
				inner join v_LpuRegion lr (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
				inner join v_LpuRegionType lrt (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
			WHERE
				msr.MedPersonal_id = :MedPersonal_id AND msr.Lpu_id = :Lpu_id
				{$filters}
		";
		//echo getDebugSQL($sql, $params);die;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) ) {
			$response = $res->result('array');
			if (empty($response)) {
				return $responseIfEmpty;
			}
			return $response;
		}
		else
			return false;
	}

    /**
     * Получение фельдшердских участков
     */
    function getLpuRegionListFeld($data)
    {
        $params = array();
        $and = "";
        $join = "";
        if(isset($data['LpuRegion_date'])){
            //var_dump($data['LpuRegion_date']);
            $params['LpuRegion_date'] = $data['LpuRegion_date'];
            $and .= " and LR.LpuRegion_begDate <= :LpuRegion_date and (LR.LpuRegion_endDate is null or LR.LpuRegion_endDate >= :LpuRegion_date)";
        }
        if(isset($data['Lpu_id'])){
            $params['Lpu_id'] = $data['Lpu_id'];
            $and .= " and LR.Lpu_id = :Lpu_id";
        }
        if(isset($data['Org_id'])){
            $join = "inner join v_Lpu L with (nolock) on L.Lpu_id = LR.Lpu_id";
            $and .= " and L.Org_id = :Org_id";
            $params['Org_id'] = $data['Org_id'];
        }
        $sql = "
            select
                LR.LpuRegion_id as LpuRegion_Fapid,
                LR.LpuRegion_Name as LpuRegion_FapName,
                LR.LpuRegion_Descr as LpuRegion_FapDescr
            from v_LpuRegionType LRT with (nolock)
            inner join v_LpuRegion LR with (nolock) on LR.LpuRegionType_id = LRT.LpuRegionType_id
            {$join}
            where LRT.LpuRegionType_SysNick = 'feld'
            {$and}
        ";
        //echo getDebugSQL($sql,$params);die;
        $res = $this->db->query($sql,$params);
        if(is_object($res))
        {
            $res = $res->result('array');
            if(is_array($res) && count($res) > 0)
                return $res;
            else
                return false;
        }
        else return false;
    }
	
	/**
	* Получение участков по адресу КЛАДР с типом педитария или врач общей практики
	*/
    function getLpuRegionsByAddress($data)
    {
        if (
            !isset($data['Lpu_id']) ||
            !isset($data['KLCity_id']) ||
            !isset($data['domNum'])
        ) return false;

        $where = array();
        $where[] = "(lr.LpuRegion_endDate is null or lr.LpuRegion_endDate > dbo.tzGetDate())";
        $where[] = "(l.Lpu_endDate is null or l.Lpu_endDate > dbo.tzGetDate())";
        $where[] = "lrs.KLCity_id = :KLCity_id";
        if ($data['KLStreet_id'] != '') {
            $where[] = "lrs.KLStreet_id = :KLStreet_id";
        } else {
            $where[] = "lrs.KLStreet_id is null";
        }
        //LpuRegionType_Codes указываются через запятую
        $where[] = "lrt.LpuRegionType_Code in ({$data['LpuRegionType_Codes']})";

        $params = array(
            'KLCity_id' => $data['KLCity_id'],
            'KLStreet_id' => $data['KLStreet_id']
        );

        $sql = "
			Select
				lb.LpuBuilding_id,
				case when lb.LpuBuilding_Nick is not null then lb.LpuBuilding_Nick else lb.LpuBuilding_Name end as LpuBuilding_Name,
				isnull(addr.Address_Nick, addr.Address_Address) as LpuBuilding_Address,
				LpuRegionStreet_HouseSet
			from LpuRegionStreet lrs with (nolock)
				left join KLArea c with (nolock) on c.Klarea_id = lrs.KLCity_id
				left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
				left join v_LpuRegion lr with (nolock) on lrs.LpuRegion_id = lr.LpuRegion_id
				left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding lb with (nolock) on ls.LpuBuilding_id = lb.LpuBuilding_id
				left join v_Lpu l with (nolock) on l.Lpu_id = lb.Lpu_id
				left join v_Address addr on lb.Address_id = addr.Address_id
				left join KLArea t with (nolock) on t.KLArea_id = lrs.KLTown_id
				left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
				left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = lrs.KLStreet_id
			" . ImplodeWherePH($where) . "
		";

        $currNumHouse = $data['domNum'];
        $currNumHouseWithoutKorp = explode('/',$currNumHouse);
        $currNumHouseWithoutKorp = $currNumHouseWithoutKorp[0];
        $address_result = array();
        $res = $this->db->query($sql, $params)->result('array');
        if (count($res) == 0) {
            $where = array();
            $where[] = "(lr.LpuRegion_endDate is null or lr.LpuRegion_endDate > dbo.tzGetDate())";
            $where[] = "(l.Lpu_endDate is null or l.Lpu_endDate > dbo.tzGetDate())";
            $where[] = "lrs.KLTown_id = :KLTown_id";
            if ($data['KLStreet_id'] != '') {
                $where[] = "lrs.KLStreet_id = :KLStreet_id";
            } else {
                $where[] = "lrs.KLStreet_id is null";
            }

            //LpuRegionType_Codes указываются через запятую
            $where[] = "lrt.LpuRegionType_Code in ({$data['LpuRegionType_Codes']})";

            $params = array(
                'KLTown_id' => $data['KLCity_id'],
                'KLStreet_id' => $data['KLStreet_id']
            );

            $sql = "
			Select
				lb.LpuBuilding_id,
				case when lb.LpuBuilding_Nick is not null then lb.LpuBuilding_Nick else lb.LpuBuilding_Name end as LpuBuilding_Name,
				isnull(addr.Address_Nick, addr.Address_Address) as LpuBuilding_Address,
				LpuRegionStreet_HouseSet
			from LpuRegionStreet lrs with (nolock)
				left join KLArea c with (nolock) on c.Klarea_id = lrs.KLTown_id
				left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
				left join v_LpuRegion lr with (nolock) on lrs.LpuRegion_id = lr.LpuRegion_id
				left join v_LpuRegionType lrt with (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding lb with (nolock) on ls.LpuBuilding_id = lb.LpuBuilding_id
				left join v_Lpu l with (nolock) on l.Lpu_id = lb.Lpu_id
				left join v_Address addr on lb.Address_id = addr.Address_id
				left join KLArea t with (nolock) on t.KLArea_id = lrs.KLTown_id
				left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
				left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = lrs.KLStreet_id
			" . ImplodeWherePH($where) . "
		";

            $currNumHouse = $data['domNum'];
            $currNumHouseWithoutKorp = explode('/',$currNumHouse);
            $currNumHouseWithoutKorp = $currNumHouseWithoutKorp[0];
            $res = $this->db->query($sql, $params)->result('array');

        }
        if ((isset($currNumHouse)) && ($currNumHouse != '')) {
            foreach ($res as $row) {
                if (strlen($row["LpuRegionStreet_HouseSet"]) > 0) {
                    $houseNums = mb_split(",", $row["LpuRegionStreet_HouseSet"]);
                    //есть ли дом через запятую
                    foreach ($houseNums as $nh) {
                        if ((string)$currNumHouse == $nh && !in_array($row, $address_result)) {
                            $address_result[] = $row;
                        }
                    }
                    //есть ли дом в интервале
                    //при поиске в интервале не учитываем корпус
                    if (strstr($row["LpuRegionStreet_HouseSet"], "-")) {
                        foreach ($houseNums as $str) {

                            if (strstr($str, "-")) {
                                $nstr = mb_split("-", $str);
                                //проверка на букву (проверяем 2 индекс интервала и вводимое значение, не совпадают - не наш случай)
                                $odd = $even = false;
                                if (mb_substr($nstr[0], 0, 1) == "Ч") $even = true;
                                if (mb_substr($nstr[0], 0, 1) == "Н") $odd = true;
                                $nstr[0] = str_replace(array('Н', 'н', 'Ч', 'ч', '('), "", $nstr[0]);
                                $nstr[1] = str_replace(')', "", $nstr[1]);
                                settype($nstr[0], 'integer');
                                settype($nstr[1], 'integer');

                                if ((strlen((int)$currNumHouseWithoutKorp) == strlen($currNumHouseWithoutKorp)) && ($nstr[0] <= $currNumHouseWithoutKorp) && ($nstr[1] >= $currNumHouseWithoutKorp)) {

                                    if ($odd == true && $currNumHouseWithoutKorp % 2 != 0 && !in_array($row, $address_result)) {
                                        $address_result[] = $row;
                                    }
                                    if ($even == true && $currNumHouseWithoutKorp % 2 == 0 && !in_array($row, $address_result)) {
                                        $address_result[] = $row;
                                    }
                                    if ($odd == false && $even == false && !in_array($row, $address_result)) {
                                        $address_result[] = $row;
                                    }
                                    continue;
                                }

                            }
                        }
                    }
                } else {
                    $address_result[] = $row;
                }
            }
        }
        $address = array();
        //убираем адреса без отделения
        foreach($address_result as $item){
            if(!empty($item['LpuBuilding_id'])){
                $address[0] = $item;
                break;
            }
        }
        //Возвращаем если есть адрес с отделением
        if (count($address) > 0 ) {
            //здесь мы получаем группу отделений к которому относится подразделение
            $this->load->model("LpuStructure_model", "lsmodel");
            $LpuUnit = $this->lsmodel->getLpuUnitList(array(
                'LpuBuilding_id' => $address[0]['LpuBuilding_id'],
                'object' => 'LpuUnit'
            ));

            $result = array(
                'success' => true,
                'data' => array(
                    'LpuBuilding_id' => $address[0]['LpuBuilding_id'],
                    'LpuBuilding_Name' => $address[0]['LpuBuilding_Name'],
                    'LpuBuildingPhone' => (isset($LpuUnit) && isset($LpuUnit[0])) ? $LpuUnit[0]['LpuUnit_Phone'] : null,
                    'LpuBuilding_Address' => $address[0]['LpuBuilding_Address']
                )
            );
            return $result;
        }

        return false;
    }
	/**
	 * Получить список участков обслуживаемых врачом
	 *
	 * @param int $msf_id Идентификатор врача
	 */
	public function getLpuRegionByMedStaffFact($msf_id) {
		$result = $this->queryResult("
		select
            lr.LpuRegion_id,
            lr.LpuRegion_Name
        from v_LpuRegion lr 
        left join v_MedStaffRegion msr  on msr.LpuRegion_id = lr.LpuRegion_id
        left join v_MedStaffFact msf  on msr.MedStaffFact_id = msf.MedStaffFact_id and isnull(msr.MedStaffRegion_endDate, '2030-01-01') > getdate()
        where msf.MedStaffFact_id = :MedStaffFact_id
        order by
            lr.LpuRegion_Name",'MedStaffFact_id', $msf_id);
		return $result;
	}
}