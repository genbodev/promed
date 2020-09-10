<?php
/**
* Polka_PersonDisp_model - модель, для работы с таблицей PersonDisp
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      30.06.2009
*/

class Polka_PersonDisp_model extends swModel
{
	/**
	 * Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список диспансерных карт по заданным фильтрам
	 */
	function getPersonDispHistoryList($data)
	{
		$filter = "";
		$diag_filters = getAccessRightsDiagFilter('v_Diag.Diag_Code', true);
		if (count($diag_filters) > 0) {
			$filter .= "and ".implode(' and ', $diag_filters);
		}

		$sql = "
			SELECT
				PersonDisp_id,
				convert(varchar,cast(PersonDisp_begDate as datetime),104) as PersonDisp_begDate,
				convert(varchar,cast(PersonDisp_endDate as datetime),104) as PersonDisp_endDate,
				v_Diag.Diag_Code,
				v_Lpu.Lpu_Nick,
				v_LpuSection.LpuSection_Name,
				v_LpuRegion.LpuRegion_Name,
				v_MedPersonal.Person_Fio as MedPersonal_FIO,
				CASE WHEN v_PersonDisp.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as IsOurLpu
			FROM
				v_PersonDisp with(nolock) LEFT JOIN
				v_Diag with(nolock) on v_PersonDisp.Diag_id=v_Diag.Diag_id LEFT JOIN
				v_Lpu on v_PersonDisp.Lpu_id=v_Lpu.Lpu_id LEFT JOIN
				v_MedPersonal with(nolock) on v_PersonDisp.MedPersonal_id=v_MedPersonal.MedPersonal_id LEFT JOIN
				v_LpuRegion on v_PersonDisp.LpuRegion_id = v_LpuRegion.LpuRegion_id LEFT JOIN
				v_LpuSection with(nolock) on v_PersonDisp.LpuSection_id = v_LpuSection.LpuSection_id
			WHERE
				v_PersonDisp.Person_id = :Person_id
				{$filter}
			ORDER BY
				PersonDisp_begDate,
				PersonDisp_endDate
		";
		$res=$this->db->query($sql, array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список диагнозов по заданным фильтрам
	 */
	function loadDiagList($Diag_pid)
	{
		$where = "";
        if ( $Diag_pid == 'null' )
			$where = " Diag_pid is null ";
		else
			$where = " Diag_pid = ? ";
		$sql = "
			SELECT
				Diag_Code,
				Diag_Name,
				DiagLevel_id,
				Diag_pid,
				Diag_id
			FROM
				v_Diag with(nolock)
			WHERE
				{$where}
			ORDER BY
				Diag_Code
		";
		$res=$this->db->query($sql, array($Diag_pid));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список медикаментов по заданным фильтрам
	 */
	function getPersonDispMedicamentList($PersonDisp_id)
	{
		$sql = "
			SELECT
            	PersonDispMedicament.PersonDispMedicament_id,
				PersonDispMedicament.PersonDisp_id,
				PersonDispMedicament.Drug_id,
				Drug.DrugMnn_id,
				Drug.Drug_Name,
				DrugState_Price as Drug_Price,
				PersonDispMedicament.PersonDispMedicament_Norma as Drug_Count,
				convert(varchar,cast(PersonDispMedicament.PersonDispMedicament_begDate as datetime),104) as PersonDispMedicament_begDate,
				convert(varchar,cast(PersonDispMedicament.PersonDispMedicament_endDate as datetime),104) as PersonDispMedicament_endDate
			FROM
				PersonDispMedicament with(nolock) left join
				DrugState with(nolock) on PersonDispMedicament.Drug_id=DrugState.Drug_id left join
				Drug on PersonDispMedicament.Drug_id=Drug.Drug_id
			WHERE
				PersonDisp_id = ?
		";
		$res=$this->db->query($sql, array($PersonDisp_id));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Возвращает количество медикаментов по заданным фильтрам
	 */
	function getPersonDispMedicamentCount($PersonDisp_id)
	{
		$sql = "
			SELECT
            	count(*) as cnt
			FROM
				PersonDispMedicament with(nolock) left join
				DrugState with(nolock) on PersonDispMedicament.Drug_id=DrugState.Drug_id left join
				Drug on PersonDispMedicament.Drug_id=Drug.Drug_id
			WHERE
				PersonDisp_id = ?
		";
		$res=$this->db->query($sql, array($PersonDisp_id));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getPersonDispNumber($data) {
		$this->load->library('swMongoExt');
		return array(array('PersonDisp_NumCard' => $this->swmongoext->generateCode('PersonDisp', '', array(
			'Lpu_id' => $data['Lpu_id']
		))));
	}

	/**
	 * Сохраняет медикамент
	 */
	function savePersonDispMedicament($data)
	{
		$procedure = 'p_PersonDispMedicament_ins';
		$res = NULL;
		if ( $data['PersonDispMedicament_id'] > 0 )
		{
			$procedure = 'p_PersonDispMedicament_upd';
			$res = $data['PersonDispMedicament_id'];
		}
		//$data['Course_begDate'] = "'".$data['Course_begDate']."'";
		if ( strtolower($data['Course_begDate']) == 'null' ) $data['Course_begDate'] = null;
		if ( strtolower($data['Course_endDate']) == 'null' ) $data['Course_endDate'] = null;
		$query = "
	        declare
               @Res bigint,
               @ErrCode int,
               @ErrMessage varchar(4000);
           set @Res = ?;
           exec " . $procedure . " "
               . "@PersonDispMedicament_id = @Res output, "
               . "@Server_id = ?, "
               . "@PersonDisp_id = ?, "
               . "@Drug_id = ?, "
               . "@PersonDispMedicament_Norma = ?, "
               . "@PersonDispMedicament_begDate = ?, "
               . "@PersonDispMedicament_endDate = ?, "
               . "@pmUser_id = ?, "
			   . "@Error_Code = @ErrCode output, "
               . "@Error_Message = @ErrMessage output;
        		select @Res as PersonDispMedicament_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$res=$this->db->query($query, array($res, $data['Server_id'], $data['PersonDisp_id'], $data['Drug_id'], $data['Course'], $data['Course_begDate'], $data['Course_endDate'], $data['pmUser_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Возвращает список карт по заданным фильтрам из дерева
	 */
	function getPersonDispListByTree($data) {
		$baseParams = array();
		$filters = array();
		$joinList = array();

		$filters[] = "PD.Lpu_id = :Lpu_id";
		$baseParams['Lpu_id'] = $data['Lpu_id'];

		// 0. Фильтры по дереву
		// включая не актуальные актуальные карты
		if ( !isset($data['view_all_id']) || $data['view_all_id'] == 1 ) {
			$filters[] = "(PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > @curDate)";
		}
	
		if  (isset($data['disp_med_personal']) && $data['disp_med_personal'] > 0) //Указали поставившего врача
		{
			$filters[] = "(PD.MedPersonal_id = :disp_med_personal)";
			$baseParams['disp_med_personal'] = $data['disp_med_personal'];
		}
		if (isset($data['hist_med_personal']) && $data['hist_med_personal'] > 0) //Указали ответственного врача
		{	
			$and_filter = '';
			if(isset($data['check_mph']) && $data['check_mph'])
			{
				$and_filter = ' and PerDH.MedPersonal_id = mph_last.MedPersonal_id_last';
			}
			$filters[] = "(
				exists (
					select top 1 1 
					from v_PersonDispHist PerDH (nolock)
					where PerDH.PersonDisp_id = PD.PersonDisp_id
					and PerDH.MedPersonal_id = :hist_med_personal
					{$and_filter}
				)
			)";
			
			$baseParams['hist_med_personal'] = $data['hist_med_personal'];
		}
		//1189120
		if ( isset($data['object']) ) {
			if ( isset($data['id']) || $data['object'] == 'LpuUnitType' ) {
				$id = $data['id'];

				switch ( $data['object'] ) {
					case 'Common':
						$filters[] .= "PD.Sickness_id is null";
					break;
					case 'Sickness':
						$filters[] .= "PD.Sickness_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuSection':
						$filters[] = "PD.LpuSection_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuSectionPid':
						$filters[] = "PD.LpuSection_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuRegion':
						$joinList[] = "INNER JOIN v_PersonCard pc1 with (nolock) on pc1.Person_id = PD.Person_id";
						$filters[] = "pc1.LpuRegion_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuUnit':
						$joinList[] = "INNER JOIN v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "INNER JOIN v_LpuUnit lu1 with (nolock) on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$filters[] = "lu1.LpuUnit_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuUnitType':
						$joinList[] = "INNER JOIN v_LpuSection ls1 with (nolock) on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "INNER JOIN v_LpuUnit lu1 with (nolock) on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$arr = explode('_', $id);
						$filters[] = "lu1.LpuUnitType_id = :id1";
						$filters[] = "lu1.LpuBuilding_id = :id2";
						$baseParams['id1'] = $arr[0];
						$baseParams['id2'] = $arr[1];
					break;
					case 'LpuBuilding':
						$joinList[] = "INNER JOIN LpuSection ls1 with (nolock) on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "INNER JOIN LpuUnit lu1 with (nolock) on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$filters[] .= "lu1.LpuBuilding_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'LpuRegionType':
						$joinList[] = "INNER JOIN v_PersonCard pc2 with(nolock) on pc2.Person_id = PD.Person_id";
						$filters[] = "pc2.LpuRegionType_id = :id";
						$baseParams['id'] = $id;
					break;
					case 'MedPersonal':
						if(!empty($data['view_mp_id']) && $data['view_mp_id'] != 2){
							if($data['view_mp_id'] == 1){
								$filters[] = "(
									PD.MedPersonal_id = :id 
									or exists(select top 1 1 from v_PersonDispHist PDH with (nolock) 
										where PDH.PersonDisp_id = PD.PersonDisp_id and PDH.MedPersonal_id = :id
									)
								)";
							} else if($data['view_mp_id'] == 3){
								if(!empty($data['view_mp_onDate'])){
									$filters[] = "(
										exists(select top 1 1 from v_PersonDispHist PDH with (nolock) 
											where PDH.PersonDisp_id = PD.PersonDisp_id and PDH.MedPersonal_id = :id
											and (
												(PDH.PersonDispHist_begDate <= cast(:onDate as datetime) and PDH.PersonDispHist_endDate is null)
												or
												(PDH.PersonDispHist_begDate <= cast(:onDate as datetime) and PDH.PersonDispHist_endDate >= cast(:onDate as datetime)) 
											)
										)
									)";
									$baseParams['onDate'] = $data['view_mp_onDate'];
								} else {
									$filters[] = "(
										exists(select top 1 1 from v_PersonDispHist PDH with (nolock) 
											where PDH.PersonDisp_id = PD.PersonDisp_id and PDH.MedPersonal_id = :id
										)
									)";
								}
							}
						} else {
							if (empty($baseParams['disp_med_personal']) && empty($baseParams['hist_med_personal']))
								$filters[] = "(PD.MedPersonal_id = :id or
									exists (
										select top 1 1 
										from v_PersonDispHist PerDH (nolock)
										where PerDH.PersonDisp_id = PD.PersonDisp_id
										and PerDH.MedPersonal_id = :id
										)
									)
								";
						}
						$baseParams['id'] = $id;
    				break;
					case 'Diag':
						switch ( $data['DiagLevel_id'] ) {
							case 1:
								$joinList[] = "LEFT JOIN v_Diag dg1 with(nolock) ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 with(nolock) ON dg1.Diag_pid = dg2.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg3 with(nolock) ON dg2.Diag_pid = dg3.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg4 with(nolock) ON dg3.Diag_pid = dg4.Diag_id";
								$filters[] = "dg4.Diag_id = :id";
								$baseParams['id'] = $id;
							break;
							case 2:
								$joinList[] = "LEFT JOIN v_Diag dg1 with(nolock) ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 with(nolock) ON dg1.Diag_pid = dg2.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg3 with(nolock) ON dg2.Diag_pid = dg3.Diag_id";
								$filters[] = "dg3.Diag_id = :id";
								$baseParams['id'] = $id;
							break;
							case 3:
								$joinList[] = "LEFT JOIN v_Diag dg1 with(nolock) ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 with(nolock) ON dg1.Diag_pid = dg2.Diag_id";
								$filters[] = "dg2.Diag_id = :id";
								$baseParams['id'] = $id;
							break;
							case 4:
								$joinList[] = "LEFT JOIN v_Diag dg1 with(nolock) ON dg1.Diag_id = PD.Diag_id";
								$filters[] = "dg1.Diag_id = :id";
								$baseParams['id'] = $id;
							break;
						}
					break;
				}
			}
		}
		//Фильтрация по ограничению доступа к группе диагнозов
		$filters = array_merge($filters, getAccessRightsDiagFilter('dg.Diag_Code', true));
		$sql = "
			--variables
			DECLARE @curDate date = dbo.tzGetDate();
			--end variables
			SELECT TOP 101
				-- select
				PD.PersonDisp_id,
				PD.Person_id,
				PD.Server_id,
				rtrim(PS.Person_SurName) as Person_SurName,
				rtrim(PS.Person_FirName) as Person_FirName,
				rtrim(PS.Person_SecName) as Person_SecName,
				dg.Diag_Code,
				mp1.Person_Fio as MedPersonal_FIO,
				mph_last.MedPersonal_FIO_last as MedPersonalHist_FIO,
				lpus1.LpuSection_Name as LpuSection_Name,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
				convert(varchar(10), isnull(PD.PersonDisp_NextDate, oapdv.PersonDispVizit_NextDate), 104) as PersonDisp_NextDate,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				Sickness.Sickness_Name,
				CASE
					WHEN noz.isnoz = 1 THEN 'true'
					WHEN noz.isnoz is not null THEN 'gray'
					ELSE 'false'
				END as Is7Noz,
				ISNULL(PCA.LpuRegion_Name,'') as LpuRegion_Name
				-- end select
			FROM
				-- from
				v_PersonDisp PD with(nolock)
				INNER JOIN v_PersonState PS with(nolock) on PD.Person_id = PS.Person_id
				LEFT JOIN v_Sickness Sickness with(nolock) on Sickness.Sickness_id = PD.Sickness_id
				LEFT JOIN v_Diag dg with(nolock) on PD.Diag_id = dg.Diag_id
				LEFT JOIN v_LpuSection lpus1 with(nolock) on PD.LpuSection_id = lpus1.LpuSection_id
				LEFT JOIN v_LpuRegion lpur1 with(nolock) on PD.LpuRegion_id = lpur1.LpuRegion_id				
				outer apply (
					select top 1 *
					from v_PersonCard_all with (nolock)
					where Person_id = PS.Person_id
						and LpuAttachType_id = 1
						and Lpu_id = :Lpu_id
					order by PersonCard_begDate desc
				) PCA
				outer apply (
					select
						max(
							case
								when 
									cast(PersonDispMedicament_begDate as date) <= @curDate
									and (
										PersonDispMedicament_endDate is null
										or cast(PersonDispMedicament_endDate as date) >= @curDate
										)
								then 1 
								when 
									cast(PersonDispMedicament_begDate as date) > @curDate
								then 0 
								else NULL 
							end
						) as isnoz
					from
						PersonDispMedicament with(nolock)
					where
						PersonDisp_id = PD.PersonDisp_id
						and PersonDispMedicament_begDate is not null
				) as noz
				" . (count($joinList) > 0 ? implode(' ', $joinList) : "") . "
				outer apply (
					select top 1 *
					from v_MedPersonal with (nolock)
					where MedPersonal_id = PD.MedPersonal_id
						and Lpu_id = :Lpu_id
				) mp1
				outer apply(
					select top 1 
						MP_L.MedPersonal_id as MedPersonal_id_last,
						MP_L.Person_Fio as MedPersonal_FIO_last
					from v_PersonDispHist PDH_L (nolock)
					left join v_MedPersonal MP_L (nolock) on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
					where PDH_L.PersonDisp_id = PD.PersonDisp_id
					order by PDH_L.PersonDispHist_begDate desc					
				) mph_last
				outer apply(
					select top 1 PersonDispVizit_NextDate
					from v_PersonDispVizit with (nolock)
					where (PersonDisp_id = PD.PersonDisp_id) and (cast(PersonDispVizit_NextDate as date) >= @curDate and (PersonDispVizit_NextFactDate is null))
					order by PersonDispVizit_NextDate asc
				) oapdv
				--end from
			WHERE 
				-- where 
				" . (count($filters) > 0 ? implode(' and ', $filters) : "") . "
				-- end where
			ORDER BY
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
		";

		return $this->getPagingResponse($sql, $baseParams, $data['start'], $data['limit'], true);
		}

	/**
	 * Проверка существования диспансерской карты
	 */
	function checkPersonDispExists($data) {
		$params = array(
			'PersonDisp_id' => $data['PersonDisp_id'],
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonDisp_begDate' => $data['PersonDisp_begDate'],
			'PersonDisp_endDate' => $data['PersonDisp_endDate']
		);

		$query = "select top 1 D.Diag_Code from v_Diag D with(nolock) where D.Diag_id = :Diag_id";
		$params['Diag_Code'] = $this->getFirstResultFromQuery($query, $params);
		if (!$params['Diag_Code']) {
			return false;
		}

		$query = "
			select top 1
				PD.PersonDisp_id,
				D.Diag_FullName,
				convert(varchar(10), PersonDisp_endDate, 104) as PersonDisp_endDate
			from
				v_PersonDisp PD with(nolock)
				inner join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
			where
				PD.Person_id = :Person_id
				and PD.Lpu_id = :Lpu_id
				and PD.PersonDisp_id != coalesce(:PersonDisp_id, 0)
				and (
					PD.PersonDisp_endDate is null
					or :PersonDisp_begDate between PD.PersonDisp_begDate and PD.PersonDisp_endDate
				)
				and substring(D.Diag_Code, 1, 2) like substring(:Diag_Code, 1, 2)
			order by
				PD.PersonDisp_begDate desc
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);

		if (count($response) == 0) {
			return false;
		} else {
			return array('existsPersonDisp' => $response[0], 'success' => true);
		}
	}

	/**
	 * Сохранение
	 */
	function savePersonDisp($data)
	{
		if ( $data['PersonDisp_id'] == 0 ) {
			$data['PersonDisp_id'] = null;
		}
		
		$chk = $this->getFirstRowFromQuery("
			select 
				PersonDisp_id,
				convert(varchar(10), PersonDisp_endDate, 104) as PersonDisp_endDate
			from v_PersonDisp (nolock)
			where 
				Person_id = :Person_id
				and Diag_id = :Diag_id
				and Lpu_id = :Lpu_id
				and PersonDisp_id != coalesce(:PersonDisp_id, 0)
				and (
					PersonDisp_endDate is null 
					or :PersonDisp_begDate between PersonDisp_begDate and PersonDisp_endDate
				)
		", $data);
		
		if (is_array($chk) && !empty($chk['PersonDisp_id'])) {
			if (!empty($chk['PersonDisp_endDate'])) {
				$nextday = date('j.m.Y', strtotime($chk['PersonDisp_endDate'] . "+1 days"));
				return $this->createError('',"У пациента уже есть закрытая карта с указанным диагнозом, действующая на {$chk['PersonDisp_endDate']}. Дата открытия новой карты должна быть не раньше {$nextday}).");
			} else {
				return $this->createError('','У пациента уже есть действующая карта с указанным диагнозом');
			}
		}

		if (!$data['ignoreExistsPersonDisp']) {
			$response = $this->checkPersonDispExists($data);
			if (!empty($response['Error_Msg']) || !empty($response['existsPersonDisp'])) {
				return $response;
			}
		}

		/*if ( !isset($data['Sickness_id']) )
		{
			$ci =& get_instance();
			$ci->load->model('Polka_PersonCard_model', 'pcmodel');
			$info = $ci->pcmodel->checkIfPersonCardIsExists($data);
			$response = array();
			if ($info[0]['cnt'] == 0)
			{
				$response[0]['success'] = false;
				$response[0]['Error_Code'] = 1;
				$response[0]['Error_Msg'] = 'У пациента нет прикрепления к ЛПУ на дату постановки на диспансерный учет.';
				return $response;
			}
		}*/

		if (empty($data['PersonDisp_endDate'])) {
			$Person_deadDT = $this->getFirstResultFromQuery("
				select convert(varchar(10), Person_deadDT, 120) as Person_deadDT 
				from v_PersonState (nolock) 
				where Person_id = :Person_id
			", $data);
			
			if (!empty($Person_deadDT)) {
				$data['PersonDisp_endDate'] = $Person_deadDT;
				$data['DispOutType_id'] = 4;
			}			
		}

		if (!empty($data['PersonDisp_endDate']) && empty($data['DispOutType_id'])) {
			return $this->createError('','При снятии пациента с учета должна быть указана причина снятия');
		}
				
		$procedure = '';
		$LabelDiag_id = null;//диагноз до изменения дисп.карты, по которому есть метка у пациента
		$LabelDispOutType_id = null;//причина снятия до изменения дисп.карты, для соотв.метки
		$PersonLabel_id = null;
		$LabelResp = array();
		
		if ( !isset($data['PersonDisp_id']) )
        {
            $procedure = 'p_PersonDisp_ins';
        }
		else
		{
			$procedure = 'p_PersonDisp_upd';
			//ищем открытую метку того же пациента и диагноза, что был в этой дисп.карте
			//кол-во строк - кол-во дисп.карт, подходящих по параметрам найденной метки
			$sql = "
				SELECT PD.PersonDisp_id, PD.Diag_id, PD.DispOutType_id, PL.PersonLabel_id
				FROM v_PersonDisp PDD (nolock)
					INNER JOIN v_PersonDisp PD (nolock) on PD.Person_id = PDD.Person_id and PD.Diag_id=PDD.Diag_id
					INNER JOIN v_PersonLabel PL (nolock) on PL.Person_id = PD.Person_id AND PL.Diag_id = PD.Diag_id
				WHERE PDD.PersonDisp_id = :PersonDisp_id
			";
			$params = array('PersonDisp_id' => $data['PersonDisp_id']);
			$result = $this->db->query($sql, $params);
			
			if(is_object($result)) {
				$LabelResp = $result->result('array');
				if(is_array($LabelResp) and count($LabelResp)>0) {
					$LabelDiag_id = $LabelResp[0]['Diag_id'];
					$LabelDispOutType_id = $LabelResp[0]['DispOutType_id'];
					$PersonLabel_id = $LabelResp[0]['PersonLabel_id'];
				}
			}
		}

		// стартуем транзакцию
		$this->db->trans_begin();
        $query = "
            declare
                @PersonDisp_new bigint,
                @PersonDisp_IsSignedEP bigint,
				@pmUser_signID bigint,
				@PersonDisp_signDate datetime,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @PersonDisp_new = ?;
            
            if @PersonDisp_new is not null
			  	begin
			  		select
			  			@PersonDisp_IsSignedEP = case when PersonDisp_IsSignedEP = 2 then 1 else PersonDisp_IsSignedEP end,
						@pmUser_signID = pmUser_signID,
						@PersonDisp_signDate = PersonDisp_signDate
					from
						v_PersonDisp (nolock)
					where
						PersonDisp_id = @PersonDisp_new
				end
				
            exec " . $procedure . " "
                . "@PersonDisp_id = @PersonDisp_new output, "
                . "@Lpu_id = ?, "
                . "@Server_id = ?, "
                . "@Person_id = ?, "
                . "@PersonDisp_NumCard = ?, "
                . "@PersonDisp_begDate = ?, "
                . "@PersonDisp_endDate = ?, "
                . "@PersonDisp_NextDate = ?, "
                . "@LpuSection_id = ?, "
                . "@MedPersonal_id = ?, "
                . "@Diag_id = ?, "
                . "@Diag_nid = ?, "
                . "@Diag_pid = ?, "
                . "@DispOutType_id = ?, "
                . "@Sickness_id = ?, "
				. "@PersonPrivilege_id = NULL, "
                . "@PersonDisp_IsDop = ?, "
                . "@PersonDisp_DiagDate = ?, "
                . "@DiagDetectType_id = ?, "
                . "@PersonDisp_IsTFOMS = ?, "
                . "@PersonDisp_IsSignedEP = @PersonDisp_IsSignedEP, "
                . "@pmUser_signID = @pmUser_signID, "
				. "@PersonDisp_signDate = @PersonDisp_signDate, "
                . "@DeseaseDispType_id = ?, "
                . "@pmUser_id = ?, "
                . "@Error_Code = @ErrCode output, "
                . "@Error_Message = @ErrMessage output;
            select @PersonDisp_new as PersonDisp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $result = $this->db->query($query, array (
			$data['PersonDisp_id'],
			$data['Lpu_id'],
			$data['Server_id'],
			$data['Person_id'],
			$data['PersonDisp_NumCard'],
			$data['PersonDisp_begDate'],
			$data['PersonDisp_endDate'],
			$data['PersonDisp_NextDate'],
			$data['LpuSection_id'],
			(isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) ? $data['MedPersonal_id'] : null,
			$data['Diag_id'],
			$data['Diag_nid'],
			$data['Diag_pid'],
			$data['DispOutType_id'],
			$data['Sickness_id'],
			$data['PersonDisp_IsDop'],
			$data['PersonDisp_DiagDate'],
			$data['DiagDetectType_id'],
			$data['PersonDisp_IsTFOMS'],
			$data['DeseaseDispType_id'],
			$data['pmUser_id']
		));
        $old_result = $result->result('array');
        if (is_object($result))
        {
			$response = $result->result('array');
			//связываем со сведениями о беременности, если карта была открыта оттуда
			if(isset($data['PersonPregnancy_id'])){
				$this->db->query("update PersonPregnancy with (rowlock) set PersonDisp_id = :PersonDisp_id where PersonPregnancy_id = :PersonPregnancy_id", array(
					'PersonDisp_id' => $response[0]['PersonDisp_id'],
					'PersonPregnancy_id' => $data['PersonPregnancy_id']
				));
			}
			// нам надо записать медикаменты
   			if (is_array($response) && count($response) > 0)
	   		{
    	   		if (strlen($response[0]['Error_Msg']) == 0)
				{
					// если пришли медикаменты, то надо записать
                    $person_disp_id = $response[0]['PersonDisp_id'];
					if ( isset($data['medicaments']) && !empty($data['medicaments']) && $data['medicaments'] != "[]" )
					{
						// запоминаем PersonDisp_id
						//$person_disp_id = $response[0]['PersonDisp_id'];
						$sql = "select PersonDispMedicament_id from PersonDispMedicament with(nolock) where PersonDisp_id = ?";
				        $result = $this->db->query($sql, array($response[0]['PersonDisp_id']));
						$result = $result->result('array');
	                    // приходится удалять медикаменты
						foreach ($result as $medicament)
						{
							$sql = "exec p_PersonDispMedicament_del	@PersonDispMedicament_id = {$medicament['PersonDispMedicament_id']}";
							$this->db->query($sql, array($medicament['PersonDispMedicament_id']));
						}
						// записываем новые
						$new_medicaments = json_decode($data['medicaments'], true);
						foreach ($new_medicaments as $inserting_medicament)
						{
							$beg_date = empty($inserting_medicament['PersonDispMedicament_begDate'])?NULL:substr(trim($inserting_medicament['PersonDispMedicament_begDate']), 6, 4)."-".substr(trim($inserting_medicament['PersonDispMedicament_begDate']), 3, 2)."-".substr(trim($inserting_medicament['PersonDispMedicament_begDate']), 0, 2);
	                        $end_date = empty($inserting_medicament['PersonDispMedicament_endDate'])?NULL:substr(trim($inserting_medicament['PersonDispMedicament_endDate']), 6, 4)."-".substr(trim($inserting_medicament['PersonDispMedicament_endDate']), 3, 2)."-".substr(trim($inserting_medicament['PersonDispMedicament_endDate']), 0, 2);							
							$sql = "
					            declare
					                @Res bigint,
					                @ErrCode int,
					                @ErrMessage varchar(4000);
					            set @Res = NULL;
					            exec p_PersonDispMedicament_ins "
					                . "@PersonDispMedicament_id = @Res output, "
					                . "@Server_id = ?, "
					                . "@PersonDisp_id = ?, "
					                . "@Drug_id = ?, "
					                . "@PersonDispMedicament_Norma = ?, "
					                . "@PersonDispMedicament_begDate = ?, "
					                . "@PersonDispMedicament_endDate = ?, "
					                . "@pmUser_id = ?, "
	                				. "@Error_Code = @ErrCode output, "
					                . "@Error_Message = @ErrMessage output;
					         		select @Res as PersonDispMedicament_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
							$result = $this->db->query($sql, array($data['Server_id'], $person_disp_id, $inserting_medicament['Drug_id'], $inserting_medicament['Drug_Count'], $beg_date, $end_date, $data['pmUser_id']));
					        if (is_object($result))
					        {
					       	    $response = $result->result('array');
					   			if (is_array($response) && count($response) > 0)
						   		{
					    	   		if (strlen($response[0]['Error_Msg']) > 0)
									{
										$this->db->trans_rollback();
										return $result;
									}
								}
								else
								{
									$this->db->trans_rollback();
									return false;
								}
							}
							else
							{
								$this->db->trans_rollback();
								return false;
							}
						}
					}
				}
				else
				{
					return false;
				}
			}
        }
        else
        {
        	return false;
        }
        		
		//проверка наличия связанных карт наблюдения (из дистанционного мониторинга)
		$sql = "
			SELECT 
				LOC.LabelObserveChart_id,
				LOC.LabelObserveChart_endDate, 
				LOC.DispOutType_id, 
				LOC.LabelObserveChart_IsAutoClose,
				LOC.PersonLabel_id
			FROM v_LabelObserveChart LOC (nolock) 
			WHERE LOC.PersonDisp_id = :PersonDisp_id
		";
		$params = array('PersonDisp_id' => $data['PersonDisp_id']);
		$LOCresp =  $this->db->query($sql, $params);
		$LOC = array();
		$chart_ids = array();
		
		if(is_object($LOCresp)) {
			$LOC = $LOCresp->result('array');
			if(count($LOC)>0) { //есть связанные карты наблюдения
				foreach($LOC as $L) {
					$chart_ids[] = $L['LabelObserveChart_id'];
				}
				if(!empty($data['PersonDisp_endDate'])) {//поле "снят" заполнено
					//закрываем карты наблюдения
					$sql = "
						UPDATE LabelObserveChart
						SET LabelObserveChart_endDate = :endDate,
						DispOutType_id = :DispOutType_id,
						LabelObserveChart_IsAutoClose = 1
						WHERE LabelObserveChart_id in (".implode(', ',$chart_ids).")
					";
					$params = array(
						'endDate' => $data['PersonDisp_endDate'],
						'DispOutType_id' => $data['DispOutType_id']
					);
					$res =  $this->db->query($sql, $params);
				} else {
					//открываем закрытые карты наблюдения
					$sql = "
						UPDATE LabelObserveChart
						SET LabelObserveChart_endDate = NULL,
						DispOutType_id = NULL,
						LabelObserveChart_IsAutoClose = NULL
						WHERE LabelObserveChart_id in (".implode(', ',$chart_ids).") 
							AND LabelObserveChart_endDate is not NULL
					";
					$res =  $this->db->query($sql, array());
				}
			}
		}
		//Метки
		$needUpdateLabelDiag = false;//необходимость обновить диагноз в той же метке
        if($LabelDiag_id) { //есть метка по диагнозу (который был до сохранения)
			$needCloseLabel = false;//необходимость закрыть метку (или открыть если disDate = null)
			$sql = "
				UPDATE PersonLabel SET PersonLabel_disDate = :disDate WHERE Person_id = :Person_id AND Diag_id = :Diag_id AND Label_id=1
			";
			$params = array(
				'disDate' => $data['PersonDisp_endDate'],
				'Diag_id' => $LabelDiag_id,
				'Person_id' => $data['Person_id']
			);
			if($LabelDiag_id != $data['Diag_id']) {//диагноз изменился
				//если до смены диагноза у пациента это была единственная диспансерная карта по этому диагнозу
				//нужно снять метку у пациента
				if ( is_array($LabelResp) and count($LabelResp) == 1 ) {
					//но если новый диагноз тоже из АГ, то нужно только обновить
					if(in_array($data['Diag_id'], array(5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742)))
						$needUpdateLabelDiag = true;
					else {//новый диагноз не из АГ
						//лучше бы удалить метку, но если есть карта наблюдения к метке, то удалить не получится
						if(count($LOC)==0) {
							$sql = "
								DELETE FROM PersonLabel WHERE Person_id = :Person_id AND Diag_id = :Diag_id AND Label_id=1
							";
						} else {
							//на открытии формы дисп.карты реализовано ограничение диагнозов в комбо, 
							//если есть карта наблюдения по метке АГ. Поэтому сюда попасть маловероятно.
							$needCloseLabel = true; 
							if(count($chart_ids)>0) {
								$sql = "
									UPDATE LabelObserveChart
									SET LabelObserveChart_endDate = :endDate,
									DispOutType_id = :DispOutType_id,
									LabelObserveChart_IsAutoClose = 1
									WHERE LabelObserveChart_id in (".implode(', ',$chart_ids).")
								";
								$params = array(
									'endDate' => $data['PersonDisp_endDate'],
									'DispOutType_id' => $data['DispOutType_id']
								);
								$res =  $this->db->query($sql, $params);
							}
						}
					}
				}
			} else {
				if(in_array($data['DispOutType_id'], array(1,4)) //снят по причине выздоровление или смерть
					) {	//закрываем метку
					$needCloseLabel = true;
				} else if($data['DispOutType_id']!=$LabelDispOutType_id) {//причина снятия изменилась (не выздоровление и не смерть)
					//открываем обратно метку
					$params['disDate'] = NULL;
					if(count($LOC)>0) {
						$params['PersonLabel_id'] = $LOC[0]['PersonLabel_id'];
					} else $params['PersonLabel_id'] = $PersonLabel_id;
					$sql.=" AND PersonLabel_id=:PersonLabel_id";
					$needCloseLabel = true;
				}
			}
			
			if($needCloseLabel) {
				$res =  $this->db->query($sql, $params);
			}
		}
		
		//проверяем необходимость добавить метку для дистанционного мониторинга (артериального давления)
		if(in_array($data['Diag_id'], array(5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742))) {
			if(empty($data['PersonDisp_endDate'])) { //поле "снят" пусто
				$sql = "
					SELECT count(*) FROM v_PersonLabel PL WHERE PL.Person_id = :Person_id AND PL.Diag_id=:Diag_id AND Label_id=1 AND PersonLabel_disDate is null
				";
				
				$PLcount = $this->getFirstResultFromQuery($sql, array('Person_id' => $data['Person_id'], 'Diag_id'=>$data['Diag_id']));
				if($PLcount==0 and !$needUpdateLabelDiag) { //метку не нашли - нужно создать
					$sql = "
					DECLARE	
							@PersonLabel_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_PersonLabel_ins
							@PersonLabel_id = @PersonLabel_id OUTPUT,
							@Label_id = 1,
							@Person_id = :Person_id,
							@Diag_id = :Diag_id,
							@pmUser_id = :pmUser_id,
							@PersonLabel_setDate = :setDate,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@PersonLabel_id as PersonLabel_id,
							@Error_Code as Error_Code,
							@Error_Message as Error_Message
					";
					
					$res =  $this->db->query($sql, array(
						'Person_id' => $data['Person_id'],
						'Diag_id' => $data['Diag_id'],
						'setDate' => $data['PersonDisp_begDate'],
						'pmUser_id' => $data['pmUser_id']
					));
				} elseif($needUpdateLabelDiag) {//метка есть и диагноз сменился - обновить диагноз в метке
					$sql = "
						UPDATE PersonLabel SET Diag_id = :NewDiag_id WHERE Person_id=:Person_id AND Diag_id=:Diag_id
					";
					
					$res =  $this->db->query($sql, array(
						'Person_id' => $data['Person_id'],
						'Diag_id' => $LabelDiag_id,
						'NewDiag_id' => $data['Diag_id']
					));
				}
			}
		}

        //Если все прошло нормально, добавляем новый диагноз в DiagDispCard (только для беременностей и родов (sickness_id = 9)
        if($data['Sickness_id'] == 9){
            //Проверим последний диагноз в истории. Если не совпадает с нововведенным, то добавим его
            $params_check['PersonDisp_id'] = $person_disp_id;
            $query_check = "
                select top 1 D.Diag_id
                from v_DiagDispCard D with (nolock)
                where D.PersonDisp_id = :PersonDisp_id
                order by D.DiagDispCard_insDT desc
            ";
            $result_check = $this->db->query($query_check,$params_check);
            if(is_object($result_check)){
                $resp_check = $result_check->result('array');
                if (is_array($resp_check) && count($resp_check)>0){
                    if($resp_check[0]['Diag_id'] != $data['Diag_id']){
                        $data['DiagDispCard_Date'] = date('Y-m-d');
                        $this->saveDiagDispCard($data);
                    }
                }
                else {
                    $data['DiagDispCard_Date'] = date('Y-m-d');
                    $data['PersonDisp_id'] = $person_disp_id;
                    $this->saveDiagDispCard($data);
                }
            }
        }
        if(!empty($data['PersonRegister_id'])) {
        	$params_check = array('PersonDisp_id'=>$person_disp_id, 'PersonRegister_id'=>$data['PersonRegister_id']);
        	$query_check = "
                select top 1 PRDL.PersonRegister_id
                from v_PersonRegisterDispLink PRDL with (nolock)
                where PRDL.PersonRegister_id = :PersonRegister_id and PRDL.PersonDisp_id = :PersonDisp_id
            ";
            $error = false;
			if ($this->getFirstResultFromQuery($query_check, $params_check) === false) {
				$resp = $this->execCommonSP('p_PersonRegisterDispLink_ins', array(
					'PersonRegisterDispLink_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
					'PersonRegister_id' => $data['PersonRegister_id'],
					'PersonDisp_id' => $person_disp_id,
					'pmUser_id' => $data['pmUser_id']
				), 'array_assoc');
				if(empty($resp['PersonRegisterDispLink_id']) || !empty($resp['Error_Msg'])) {
					$error = (!empty($resp['Error_Msg'])?$resp['Error_Msg']:true);
				}
			}
            if($error){
            	$this->db->trans_rollback();
            	if(gettype($error) == 'string'){
            		$errorMsg = $error;
            	} else {
            		$errorMsg = 'Ошибка при сохранении карты.';
            	}
            	return array(array('Error_Msg'=>$errorMsg));
            }
        }
		if ($this->getRegionNick() == 'kz') {
			$this->db->query("
				delete from r101.PersonDispGroupLink where PersonDisp_id = ?
			", array($person_disp_id));
			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = NULL;
				exec r101.p_PersonDispGroupLink_ins "
					. "@PersonDispGroupLink_id = @Res output, "
					. "@PersonDisp_id = ?, "
					. "@DispGroup_id = ?, "
					. "@pmUser_id = ?, "
					. "@Error_Code = @ErrCode output, "
					. "@Error_Message = @ErrMessage output;
					select @Res as PersonDispMedicament_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
			$result = $this->db->query($sql, array(
				$person_disp_id, 
				$data['DispGroup_id'], 
				$data['pmUser_id']
			));

			if (!empty($data['HumanUID']) && $data['action'] == 'add') {
				$this->queryResult("
				declare
					@Res bigint,
					@Error_Code int,
					@Error_Msg varchar(4000);

				exec r101.p_PersonDispUIDLink_ins
					@PersonDispUIDLink_id = @Res output,
					@PersonDisp_id = :PersonDisp_id,
					@UIDGuid = :UIDGuid,
					@PersonDisp_NumCard = :PersonDisp_NumCard,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Msg output;

				select @Res as PersonDispUIDLink_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
			", [
					'PersonDisp_id' => $person_disp_id,
					'UIDGuid' => $data['HumanUID'],
					'pmUser_id' => $data['pmUser_id'],
					'PersonDisp_NumCard' => $data['PersonDisp_NumCard']
				]);
			}
		}
		$this->db->trans_commit();
		return $old_result;
	}

	/**
	 *
	 * @return string 
	 */
	function getVizitTypeSysNick(){
		$sysNick ='disp';
		switch(getRegionNumber()){
			case '10':
				$sysNick ='consulspec';
				break;
			case '201':
				$sysNick ='dispdinnabl';
				break;
			case '3':
				$sysNick ='desease';
				break;
			default:
				$sysNick ='disp';
				break;
		}
		return $sysNick;
	}
	
	/**
	 * Получение данных для редактирования
	 */
	function loadPersonDispEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		//$med_personal_list = getMedPersonalListWithLinks();
		//$VizitType_SysNick = $this->getVizitTypeSysNick();
		$addselect = '';
		$addjoin = '';
		
		if ($this->getRegionNick() == 'kz') {
			$addselect .= ' ,dgl.DispGroup_id ';
			$addjoin .= ' left join r101.PersonDispGroupLink dgl (nolock) on dgl.PersonDisp_id = PD.PersonDisp_id ';
		}

		$params = array(
			'PersonDisp_id' => $data['PersonDisp_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['session']['medpersonal_id']
		);

		if (!empty($data['session']['medpersonal_id'])) {
			$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$accessType = "
				when mph_last.MedPersonal_id = :MedPersonal_id then 'edit'
			";
			$addjoin .= "
				outer apply(
					select top 1 
						PDH_L.MedPersonal_id,
						PDH_L.LpuSection_id
					from
						v_PersonDispHist PDH_L (nolock)
					where
						PDH_L.PersonDisp_id = PD.PersonDisp_id
					order by
						PDH_L.PersonDispHist_begDate desc					
				) mph_last
			";

			$lpu_section_list = getLpuSectionListFromMSF($data['Lpu_id'], $data['session']['medpersonal_id']);
			$lpu_section_list_str = $lpu_section_list?implode(',', $lpu_section_list):'';

			if (!empty($lpu_section_list_str)) {
				/*
				$accessType .= "
					when PD.LpuSection_id in ({$lpu_section_list_str}) then 'edit'
				";
				*/
				$accessType .= "
					when mph_last.LpuSection_id in ({$lpu_section_list_str}) then 'edit'
				";
			}
		} else {
			$accessType = "
				when 1=0 then 'edit'
			";
		}

		$query = "
			select top 1
				case
					{$accessType}
					else 'view'
				end as accessType,
				PD.Person_id,
				PD.Server_id,
				PD.LpuSection_id,
				PD.Lpu_id,
				PD.PersonDisp_NumCard,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
				convert(varchar(10), PD.PersonDisp_NextDate, 104) as PersonDisp_NextDate,
				PD.MedPersonal_id,
				PD.Diag_id,
				PD.Diag_pid,
				PD.Diag_nid,
				PD.DispOutType_id,
				ISNULL(PD.PersonDisp_IsDop, 1) as PersonDisp_IsDop,
				convert(varchar(10), PD.PersonDisp_DiagDate, 104) as PersonDisp_DiagDate,
				PD.DiagDetectType_id,
				PD.DeseaseDispType_id,
				PD.Sickness_id,
				(SELECT top 1 PregnancySpec_id FROM dbo.PregnancySpec ps with(nolock) WHERE ps.PersonDisp_id = pd.PersonDisp_id) AS PregnancySpec_id,
				PD.PersonDisp_IsTFOMS,
				PD.PersonDisp_IsSignedEP,
				PL.Label_id
				{$addselect}
			from
				v_PersonDisp PD with (nolock)
				left join Sickness S with (nolock) on S.Sickness_id = PD.Sickness_id
				left join v_LabelObserveChart LOC with (nolock) on LOC.PersonDisp_id = PD.PersonDisp_id
				left join v_PersonLabel PL on PL.PersonLabel_id=LOC.PersonLabel_id
				{$addjoin}
			where
				PD.PersonDisp_id = :PersonDisp_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление медикамента
	 */
	function deletePersonDispMedicament($data)
	{
		$sql = "exec p_PersonDispMedicament_del @PersonDispMedicament_id = ?";
        $result = $this->db->query($sql, array($data['PersonDispMedicament_id']));
	}

	/**
	 * Удаление
	 */
	public function deletePersonDisp($data)
	{
		$sql = "
			SELECT PD.Diag_id, PD2.Person_id FROM v_PersonDisp PD (nolock) 
			left join v_PersonDisp PD2 (nolock) on PD2.Diag_id = PD.Diag_id AND PD2.Person_id = PD.Person_id
			inner join v_PersonLabel PL (nolock) on PL.Person_id = PD.Person_id AND PL.Diag_id = PD.Diag_id
			WHERE PD.PersonDisp_id = :PersonDisp_id AND PL.Label_id = 1
		";
		$params = array('PersonDisp_id' => $data['PersonDisp_id']);
		$result = $this->db->query($sql, $params);
		
		if(is_object($result)) {
			$response = $result->result('array');   			
		} else return;
		
		$result = $this->queryResult("
			declare 
				@Err_Msg varchar(255), 
				@Err_Code varchar(255)

			exec p_PersonDisp_del 
				@PersonDisp_id = :PersonDisp_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Err_Code output,
				@Error_Message = @Err_Msg output

			select @Err_Msg as Error_Msg, @Err_Code as Error_Code
		", array(
			'PersonDisp_id' => $data['PersonDisp_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		
		if($this->isSuccessful($result)) {
   			if (is_array($result) && count($result) > 0 )
	   		{
    	   		if (strlen($result[0]['Error_Msg']) == 0) {
					//все нормально удалилось => проверяем метки
					if (is_array($response) && count($response) == 1) {
						//если у пациента это была единственная диспансерная карта по этому диагнозу
						//нужно удалить метку у пациента
						$sql = "
							DELETE FROM PersonLabel WHERE Person_id = :Person_id AND Diag_id = :Diag_id AND Label_id=1
						";
						$params = array(
							'Diag_id' => $response[0]['Diag_id'],
							'Person_id' => $response[0]['Person_id']
						);
						$res =  $this->db->query($sql, $params);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Получение категорий регистра заболеваний
	 */
	function loadSicknessList()
	{
		$sql = "SELECT 1 as Sickness_id, PrivilegeType_id, Sickness_Name, Sickness_id
			FROM v_Sickness Sickness
			with (NOLOCK) WHERE (1=1)
			/*gabdushev Убрал условие, т.к. отфильтровывается Беременность и роды and (PrivilegeType_id is not null)*/";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение данных для грида
	 */
	function loadPersonDispGrid($data)
	{
		$filter = "(1 = 1)";

		$filter .= " and PD.Lpu_id = ?";
		$filter .= " and PS.Person_id = ?";
		$filter .= " and PS.Server_id = ?";

		$query = "
			SELECT
				PD.PersonDisp_id,
				PS.Person_id,
				PS.Server_id,
				DG.Diag_Code,
				convert(varchar(10), cast(PD.PersonDisp_begDate as datetime), 104) as PersonDisp_begDate,
				convert(varchar(10), cast(PD.PersonDisp_endDate as datetime), 104) as PersonDisp_endDate,
				convert(varchar(10), cast(PD.PersonDisp_NextDate as datetime), 104) as PersonDisp_NextDate,
				LS.LpuSection_Code,
				MP.MedPersonal_Code,
				LR.LpuRegion_Name
			FROM v_PersonDisp PD with(nolock)
				INNER JOIN v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id and PS.Server_id = PD.Server_id
				LEFT JOIN v_Diag DG with(nolock) on PD.Diag_id = DG.Diag_id
				LEFT JOIN v_MedPersonal MP with(nolock) on MP.MedPersonal_id = PD.MedPersonal_id
				LEFT JOIN v_LpuSection LS with(nolock) on LS.LpuSection_id = PD.LpuSection_id
				LEFT JOIN v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				LEFT JOIN v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PD.LpuRegion_id
			WHERE " . $filter . "
		";
		$result = $this->db->query($query, array($data['Lpu_id'], $data['Person_id'], $data['Server_id']));

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Получение данных по дисп. учету человека для панели просмотра сигнальной информации ЭМК
	 */
	function getPersonDispViewData($data) {

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$filters = array_merge(
				getAccessRightsDiagFilter('D.Diag_Code', true),
				getAccessRightsLpuFilter('PD.Lpu_id', true),
				getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id', true),
				array('PD.Person_id = :Person_id')
			);
		}
		else
		{
			$filters = array_merge(array('PD.Person_id = :Person_id'));	
		}

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		$signFilter = "";
		if (!isLpuAdmin() && !empty($data['session']['medpersonal_id'])) {
			$signFilter = " and PDH.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		} else if (isLpuAdmin() && !empty($data['Lpu_id'])) {
			$signFilter = " and PDHLS.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($signFilter)) {
			$signAccess = "case when exists(
					select top 1
						PDH.PersonDispHist_id
					from
						v_PersonDispHist PDH (nolock)
						left join v_LpuSection PDHLS (nolock) on PDHLS.LpuSection_id = PDH.LpuSection_id
					where
						PDH.PersonDisp_id = PD.PersonDisp_id
						and ISNULL(PDH.PersonDispHist_begDate, @curDate) <= @curDate
						and ISNULL(PDH.PersonDispHist_endDate, @curDate) >= @curDate
						{$signFilter}
				) then 'edit' else 'view' end as signAccess
			";
		} else {
			$signAccess = "'view' as signAccess";
		}

		$query = "
			declare @curDate date = dbo.tzGetDate();
			
			select
				PD.Lpu_id,
				PD.Diag_id,
				PD.Person_id,
				0 as Children_Count,
				PD.PersonDisp_id,
				PD.PersonDisp_id as PersonDispInfo_id,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
				ISNULL(DOT.DispOutType_Name, '') as DispOutType_Name,
				UPPER  (case when Right( D.Diag_Code ,1) = '.'  then LEFT(D.Diag_Code, LEN(D.Diag_Code)-1) else D.Diag_Code end) AS Diag_Code,
				ISNULL(D.Diag_Name, '') as Diag_Name
				,ISNULL(MP.Person_Fio, '') as MedPersonal_Fio
				,ISNULL(LS.LpuSectionProfile_Name, '') as LpuSectionProfile_Name
				,PD.PersonDisp_IsSignedEP
				,{$signAccess} 
			from
				v_PersonDisp PD with (nolock)
				left join v_DispOutType DOT with (nolock) on DOT.DispOutType_id = PD.DispOutType_id
				left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
				outer apply (
					/*
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = PD.MedPersonal_id
					*/
					select top 1 DM.Person_Fio 
					from 
						v_PersonDispHist PDSD with(nolock)
						outer apply (select top 1 D2.Person_Fio from v_MedPersonal D2 with (nolock) where D2.MedPersonal_id = PDSD.MedPersonal_id) DM
					where PDSD.PersonDisp_id = PD.PersonDisp_id
					ORDER BY PDSD.PersonDispHist_begDate DESC
				) MP
			where
				".implode(' and ', $filters)."
			order by
				PD.PersonDisp_begDate,
				D.Diag_Code
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

	/**
	 * Список карт д-учёта, подходящих для связи с записью регистра
	 */
	function loadMorbusOnkoSelectList($data) {
		
		$data['Diag_pid'] = $this->getFirstResultFromQuery("
			select Diag_pid 
			from v_PersonRegister PR (nolock)
			inner join Diag D (nolock) on D.Diag_id = PR.Diag_id
			where PR.PersonRegister_id = :PersonRegister_id
		", $data);
		
		if (!$data['Diag_pid']) return [];
		
		$query = "
			select
				PD.PersonDisp_id
				,convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate
				,isnull(MP.Person_Fin, '') as MedPersonal_Fio
				,isnull(MPH.Person_Fin, '') as MedPersonalH_Fio
				,L.Lpu_Nick
			from
				v_PersonDisp PD (nolock)
				inner join Diag D (nolock) on D.Diag_id = PD.Diag_id
				inner join v_Lpu L (nolock) on L.Lpu_id = PD.Lpu_id
				outer apply (
					select top 1 Person_Fin
					from v_MedPersonal with (nolock)
					where MedPersonal_id = PD.MedPersonal_id
				) MP
				outer apply (
					select top 1 mpp.Person_Fin
					from v_PersonDispHist pdh with (nolock)
					left join v_MedPersonal mpp with (nolock) on mpp.MedPersonal_id = pdh.MedPersonal_id
					where PersonDisp_id = PD.PersonDisp_id and (
						(pdh.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate is null)
							or
						(PDH.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate >= dbo.tzGetDate()) 
					)
				) MPH
			where
				PD.Person_id = :Person_id
				and D.Diag_pid = :Diag_pid
				and PD.PersonDisp_endDate is null
				and not exists (
					select top 1 PersonDisp_id 
					from PersonRegisterDispLink PRDL (nolock) 
					where 
						PRDL.PersonDisp_id = PD.PersonDisp_id and
						PRDL.PersonRegister_id = :PersonRegister_id
				)
			order by
				PD.PersonDisp_begDate desc
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Cвязь с записью регистра
	 */
	function savePersonRegisterDispLink($data) {
		
		return $this->execCommonSP('p_PersonRegisterDispLink_ins', [
			'PersonRegisterDispLink_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonDisp_id' => $data['PersonDisp_id'],
			'pmUser_id' => $data['pmUser_id']
		]);
	}

	/**
	 *	Получение данных по дисп. учету человека для панели просмотра сигнальной информации ЭМК
	 */
	function getMorbusOnkoPersonDispViewData($data) {
		if(!empty($data['object']) && $data['object'] == 'MorbusOnkoPersonDisp' && !empty($data['object_value'])){
			$data['PersonRegister_id'] = $data['object_value'];
		}
		if(empty($data['PersonRegister_id'])){
			$data['PersonRegister_id'] = null;
		}
		if(empty($data['MorbusOnkoPersonDisp_id'])){
			$data['MorbusOnkoPersonDisp_id'] = null;
		}
		if(empty($data['PersonRegister_id']) && empty($data['MorbusOnkoPersonDisp_id'])){
			return array();
		}
		$query = "
			select
				PD.Lpu_id,
				PD.Diag_id,
				PD.Person_id,
				0 as Children_Count,
				PD.PersonDisp_id,
				PD.PersonDisp_id as MorbusOnkoPersonDisp_id,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate
				,convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate
				,ISNULL(MP.Person_Fio, '') as MedPersonal_Fio
				,ISNULL(MPH.Person_Fio, '') as MedPersonalH_Fio
				,PR.Morbus_id
			from
				v_PersonRegisterDispLink PRDL with (nolock)
				inner join v_PersonDisp PD with (nolock) on PD.PersonDisp_id = PRDL.PersonDisp_id
				inner join v_PersonRegister PR with (nolock) on PR.PersonRegister_id = PRDL.PersonRegister_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = PD.MedPersonal_id
				) MP
				outer apply (
					select top 1 mpp.Person_Fio
					from v_PersonDispHist pdh with (nolock)
					left join v_MedPersonal mpp with (nolock) on mpp.MedPersonal_id = pdh.MedPersonal_id
					where PersonDisp_id = PD.PersonDisp_id and (
						(pdh.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate is null)
							or
						(PDH.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate >= dbo.tzGetDate()) 
					)
				) MPH
			where
				PRDL.PersonRegister_id = :PersonRegister_id or PRDL.PersonDisp_id = :PersonDisp_id
			order by
				PD.PersonDisp_begDate
		";
		$result = $this->db->query($query, array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonDisp_id' => $data['MorbusOnkoPersonDisp_id']
		));

		if ( is_object($result) ) {
			$result = $result->result('array');
			if(is_array($result) && count($result)>0){
				foreach ($result as $key => $value) {
					$result[$key]['MorbusOnko_pid'] = $data['MorbusOnko_pid'];
				}
			}
			return swFilterResponse::filterNotViewDiag($result, $data);
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных по дисп. учету человека при открытии ЭМК #12461
	 */
	function getPersonDispSignalViewData($data) {
		$filter = '';
		$from = '';
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		$lpuFilter = getAccessRightsLpuFilter('PD.Lpu_id');
		$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id');

		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		if (!empty($lpuBuildingFilter)) {
			$filter .= " and $lpuBuildingFilter";
		}
		
		if($this->getRegionNick() == 'perm'){
			//https://redmine.swan.perm.ru/issues/122979
			$from = '
				inner join v_MorbusDiag MD with(nolock) on MD.Diag_id = D.Diag_id
				inner join v_MorbusType MT with(nolock) on MT.MorbusType_id = MD.MorbusType_id'
			;
			$filter .= " and PD.Person_id = :Person_id and PD.DispOutType_id is null and PD.PersonDisp_endDate is null and isnull(MT.MorbusType_SysNick,'') not like 'onko'";
		}else{
			$filter .= " and PD.Person_id = :Person_id and PD.DispOutType_id is null and PD.PersonDisp_endDate is null";
		}

		$query = "
		select DISTINCT * from (
			select
				PD.Lpu_id,
				PD.Diag_id,
				DU.PersonDispSetType_id,
				D.Diag_Code,
				D.Diag_Name,
				convert(varchar(10), LastOsmotr.setDate, 104) as LastOsmotr_setDate
			from
				v_PersonDisp PD with (nolock)
				left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
				left join v_MedStaffFact usermsf with (nolock) on usermsf.MedStaffFact_id = :UserMedStaffFact_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
				{$from}
				--определяем поставлен ли пациент на ДУ на участке текущего врача (2) или им самим (3) или чужими врачами (1)
				outer apply (
					select
						case
							-- поставлен ли пациент на ДУ текущим врачом
							when usermsf.MedPersonal_id = PD.MedPersonal_id
							then 3
							-- поставлен ли пациент на ДУ на участке текущего врача
							when (select COUNT(usermsr.LpuRegion_id) from v_MedStaffRegion usermsr with (nolock) where usermsr.MedPersonal_id = usermsf.MedPersonal_id and usermsr.Lpu_id = usermsf.Lpu_id and usermsr.LpuRegion_id in (select LpuRegion_id from v_MedStaffRegion with (nolock) where MedPersonal_id = PD.MedPersonal_id and Lpu_id = PD.Lpu_id)) > 0
							then 2
							-- пациент поставлен на ДУ чужим врачом
							else 1
						end as PersonDispSetType_id
				) DU
				--определяем дату последнего осмотра по ДУ
				outer apply (
					select top 1 osmotr.setDate from (
						select EvnVizitDisp_setDT as setDate
						from v_EvnVizitDisp with (nolock)
						where Person_id = PD.Person_id and Diag_id = PD.Diag_id
						union all
						select EvnVizitPl_setDT as setDate
						from v_EvnVizitPl with (nolock)
						where Person_id = PD.Person_id and Diag_id = PD.Diag_id
					) osmotr
					order by osmotr.setDate desc
				) LastOsmotr
			where (1=1)
				{$filter}
				
			union all 

			select
				isnull(MOL.Lpu_id, MOV.Lpu_id) as Lpu_id,
				PR.Diag_id,
				DU.PersonDispSetType_id,
				D.Diag_Code,
				D.Diag_Name,
				convert(varchar(10), isnull(MOL.setDate, MOV.setDate), 104) as LastOsmotr_setDate
			from v_PersonRegister PR with (nolock)
				inner join v_MorbusType MT with (nolock) on PR.MorbusType_id = MT.MorbusType_id and  MT.MorbusType_SysNick like 'onko'
				inner join v_Diag D with (nolock) on D.Diag_id = PR.Diag_id
				outer apply (
					select top 1 MOL2.Diag_id, MOL2.MorbusOnkoLeave_insDT as setDate, ES.Lpu_id, ES.MedPersonal_id
					from v_MorbusOnkoLeave MOL2 with (nolock) 
					inner join v_EvnSection ES with (nolock) on ES.EvnSection_id = MOL2.EvnSection_id
					where MOL2.Diag_id = PR.Diag_id and ES.Person_id = PR.Person_id
				) MOL
				outer apply (
					select top 1 MOLd2.Diag_id, MOLd2.MorbusOnkoVizitPLDop_setDT as setDate, EVP.Lpu_id, EVP.MedPersonal_id
					from v_MorbusOnkoVizitPLDop MOLd2 with (nolock) 
					inner join v_EvnVizitPL EVP with (nolock) on EVP.EvnVizitPL_id = MOLd2.EvnVizit_id
					where MOLd2.Diag_id = PR.Diag_id and EVP.Person_id = PR.Person_id
				) MOV
				left join v_MedStaffFact usermsf with (nolock) on usermsf.MedStaffFact_id = :UserMedStaffFact_id
				--определяем поставлен ли пациент на ДУ на участке текущего врача (2) или им самим (3) или чужими врачами (1)
				outer apply (
					select
						case
							-- поставлен ли пациент на ДУ текущим врачом
							when usermsf.MedPersonal_id = isnull(MOL.MedPersonal_id, MOV.MedPersonal_id)
							then 3
							-- поставлен ли пациент на ДУ на участке текущего врача
							when (select COUNT(usermsr.LpuRegion_id) from v_MedStaffRegion usermsr with (nolock) where usermsr.MedPersonal_id = usermsf.MedPersonal_id and usermsr.Lpu_id = usermsf.Lpu_id and usermsr.LpuRegion_id in (select LpuRegion_id from v_MedStaffRegion with (nolock) where MedPersonal_id = isnull(MOL.MedPersonal_id, MOV.MedPersonal_id) and Lpu_id = isnull(MOL.Lpu_id, MOV.Lpu_id))) > 0
							then 2
							-- пациент поставлен на ДУ чужим врачом
							else 1
						end as PersonDispSetType_id
				) DU
			where 
				PR.Person_id = :Person_id and
				(MOL.Diag_id is not null or MOV.Diag_id is not null)
			
		) as t
		
			order by
			PersonDispSetType_id desc,
			LastOsmotr_setDate
		";
		$params = array('UserMedStaffFact_id' => $data['UserMedStaffFact_id'], 'Person_id' => $data['Person_id']);
		//echo getDebugSql($query, $params); die;
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}

    /**
     * Получение истории изменений диагонозов карты ДУ
     */
    function loadDiagDispCardHistory($data){
        $params = array('PersonDisp_id' => $data['PersonDisp_id']);
        $query = "
            select
                DDC.DiagDispCard_id,
                convert(varchar(10), DDC.PersonDisp_begDate, 104) as DiagDispCard_Date,
                D.Diag_FullName,
                D.Diag_id
            from
                v_DiagDispCard DDC with (nolock)
            left join v_Diag D with (nolock) on D.Diag_id = DDC.Diag_id
            left join v_SicknessDiag SD with (nolock) on SD.Diag_id = D.Diag_id
            where
                SD.Sickness_id = 9
            and DDC.PersonDisp_id = :PersonDisp_id
            order by DDC.PersonDisp_begDate
        ";
        $result = $this->db->query($query,$params);
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
    }

    /**
     *  Загрузыка данных для формы "Диагноз в карте ДУ"
     */
    function loadDiagDispCardEditForm($data){
        $params = array('DiagDispCard_id' => $data['DiagDispCard_id']);
        $query = "
            select
                DDC.DiagDispCard_id,
                convert(varchar(10), DDC.PersonDisp_begDate, 104) as DiagDispCard_Date,
                DDC.Diag_id,
                DDC.PersonDisp_id
            from
                v_DiagDispCard DDC with (nolock)
            where DDC.DiagDispCard_id = :DiagDispCard_id
        ";
        $result = $this->db->query($query,$params);
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
    }

    /**
     * Удаление строки из истории изменений диагонозов в карте ДУ
     */
    function deleteDiagDispCard($data)
    {
        $sql = "
			declare
				@Err_Msg varchar(255),
				@Err_Code varchar(255)
			exec p_DiagDispCard_del
				@DiagDispCard_id = ?,
				@Error_Code = @Err_Code output,
				@Error_Message = @Err_Msg output

			select @Err_Msg as Error_Msg, @Err_Code as Error_Code
		";
        $result = $this->db->query($sql, array($data['DiagDispCard_id']));
        if (is_object($result))
        {
            //Обновим диспансерную карту - проставим ей последний диагноз из истории. Чтобы не наворотить много писанины, сделал пока через прямой апдейт.
            $query_set_diag = "
                update PersonDisp
                set Diag_id = (select top 1 DDC.Diag_id from v_DiagDispCard DDC with(nolock) where DDC.PersonDisp_id = ".$data['PersonDisp_id']." order by DDC.DiagDispCard_insDT desc)
                where PersonDisp_id = ".$data['PersonDisp_id']."
            ";
            $this->db->query($query_set_diag,$data['PersonDisp_id']);

            return $result->result('array');
        }
        else
        {
            return false;
        }
    }

    /**
     * Добавление/изменение строки из истории изменений диагонозов в карте ДУ
     */
    function saveDiagDispCard($data){
        $params = array();
        $DiagDispCard_id = null;
        $procedure = 'p_DiagDispCard';
        $params['DiagDispCard_Date'] =$data['DiagDispCard_Date'];
        $params['Diag_id'] = $data['Diag_id'];
        $params['PersonDisp_id'] = $data['PersonDisp_id'];

        if(isset($data['DiagDispCard_id']) && ($data['DiagDispCard_id'] <> 0)){
            $procedure .= '_upd';
            $params['DiagDispCard_id'] = $data['DiagDispCard_id'];
        }
        else{
            $procedure .= '_ins';
            $params['DiagDispCard_id'] = null;
        }
        $query = "
	        declare
               @DiagDispCard_id bigint,
               @ErrCode int,
               @begDate datetime,
               @ErrMessage varchar(4000);
			set @DiagDispCard_id = ?;
           exec ".$procedure."
               @DiagDispCard_id = @DiagDispCard_id output,
               @PersonDisp_id = ?,
               @Diag_id = ?,
               @PersonDisp_begDate = ?,
               @PersonDisp_endDate = '',
               @pmUser_id = ?,
			   @Error_Code = @ErrCode output,
               @Error_Message = @ErrMessage output;
        	select @DiagDispCard_id as DiagDispCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        //echo getDebugSQL($query, array($params['DiagDispCard_id'], $params['PersonDisp_id'], $params['Diag_id'], $params['DiagDispCard_Date'], $data['pmUser_id']));die;
        $result=$this->db->query($query, array($params['DiagDispCard_id'], $params['PersonDisp_id'], $params['Diag_id'], $params['DiagDispCard_Date'], $data['pmUser_id']));
        if ( is_object($result) ){
            $resp = $result->result('array');
            //Обновим диспансерную карту - проставим ей последний диагноз из истории. Чтобы не наворотить много писанины, сделал пока через прямой апдейт.
            $query_set_diag = "
                update PersonDisp
                set Diag_id = (select top 1 DDC.Diag_id from v_DiagDispCard DDC with(nolock) where DDC.PersonDisp_id = ".$params['PersonDisp_id']." order by DDC.DiagDispCard_insDT desc)
                where PersonDisp_id = ".$params['PersonDisp_id']."
            ";
            $this->db->query($query_set_diag,$params['PersonDisp_id']);
            return $resp;
        }
        else
            return false;
    }

    /**
	 * Возвращает список диспансерных карт по заданным фильтрам
	 */
	function getPersonDispHistoryListForPrint($data)
	{
		$filter = "";
		$diag_filters = getAccessRightsDiagFilter('d.Diag_Code', true);
		if (count($diag_filters) > 0) {
			$filter .= "and ".implode(' and ', $diag_filters);
		}

		$sql = "
			SELECT
				pd.PersonDisp_id,
				pd.MedPersonal_id,
				pd.LpuSection_id,
				convert(varchar(10),pd.PersonDisp_begDate,104) as PersonDisp_begDate,
				convert(varchar(10),pd.PersonDisp_endDate,104) as PersonDisp_endDate,
				d.Diag_Code,
				d.Diag_Name,
				l.Lpu_Nick,
				ls.LpuSection_Name,
				mp.Person_Fio as MedPersonal_FIO,
				p.name as Post_Name,
				CASE WHEN pd.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as IsOurLpu
			FROM
				v_PersonDisp pd with (nolock)
				LEFT JOIN v_Diag d with (nolock) on d.Diag_id = pd.Diag_id
				LEFT JOIN v_Lpu l with (nolock) on l.Lpu_id = pd.Lpu_id
				LEFT JOIN v_MedPersonal mp with (nolock) on mp.MedPersonal_id = pd.MedPersonal_id
				LEFT JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id = pd.LpuSection_id
				outer apply (
					select top 1 Post_id
					from v_MedStaffFact with (nolock)
					where MedPersonal_id = pd.MedPersonal_id
						and LpuSection_id = pd.LpuSection_id
						and (WorkData_begDate is null or WorkData_begDate <= pd.PersonDisp_begDate)
						and (WorkData_endDate is null or WorkData_endDate >= pd.PersonDisp_begDate)
				) msf
				left join persis.Post p with (nolock) on p.id = msf.Post_id
			WHERE
				pd.Person_id = :Person_id
				{$filter}
			ORDER BY
				PersonDisp_begDate,
				PersonDisp_endDate
		";
		$res=$this->db->query($sql, array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		}	
		else
			return false;
	}

	/**
	 * Возвращает список адресов пациента
	 */
	function getPersonDispHistoryListAdresses($data)
	{
		$sql = "
			SELECT
				pd.Address_id,
				PAdr.Address_Address as PAddress_Address,
				pd.PersonPAddress_begDT
			FROM
				v_PersonPAddress pd with (nolock)
				left join v_Address PAdr with (nolock) on PAdr.Address_id  = pd.Address_id
			WHERE
				pd.Person_id = :Person_id
			ORDER BY
				pd.PersonPAddress_begDT
		";
		$res=$this->db->query($sql, array('Person_id' => $data['Person_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		}	
		else
			return false;
	}
	

	/**
	 * Загрузка списка Контроля посещений
	 */
	function loadPersonDispVizitList($data) {		
		$sql = "
			SELECT
				PDV.PersonDispVizit_id,
				PDV.EvnVizitPL_id,
				(CASE WHEN PDV.PersonDispVizit_IsHomeDN = 2 THEN 'true' ELSE 'false' END) as PersonDispVizit_IsHomeDN,
				convert(varchar(10),PDV.PersonDispVizit_NextDate,104) as PersonDispVizit_NextDate,
				convert(varchar(10),PDV.PersonDispVizit_NextFactDate,104) as PersonDispVizit_NextFactDate
			FROM
				v_PersonDispVizit PDV with(nolock)
			WHERE
				PDV.PersonDisp_id = :PersonDisp_id
			ORDER BY
				PDV.PersonDispVizit_id
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка Контроля посещений
	 */
	function loadPersonDispVizit($data) {		
		$sql = "
			SELECT TOP 1
				PDV.PersonDispVizit_id,
				PDV.PersonDisp_id,
				(CASE WHEN PDV.PersonDispVizit_IsHomeDN = 2 THEN 'true' ELSE 'false' END) as PersonDispVizit_IsHomeDN,
				convert(varchar(10),PDV.PersonDispVizit_NextDate,104) as PersonDispVizit_NextDate,
				convert(varchar(10),PDV.PersonDispVizit_NextFactDate,104) as PersonDispVizit_NextFactDate
			FROM
				v_PersonDispVizit PDV with(nolock)
			WHERE
				PDV.PersonDispVizit_id = :PersonDispVizit_id
		";
		$res = $this->db->query($sql, array('PersonDispVizit_id' => $data['PersonDispVizit_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}	

	/**
	 * Проверяет, повторяется ли дата в поле "Назначено явиться" в контрольной карте, форма Контроль посещений
	 *
	 * @param {number} $PersonDisp_id - ID контрольной карты диспансерного наблюдения.
	 * @param {date} $PersonDispVizit_NextDate - Дата из поля "Назначено явиться".
	 * @param {number} $PersonDispVizit_id - ID контроля посещений.
	 * @return {boolean} true, если найдены повторения : false, если повторений нет или дата не введена.
	 */
	function checkVisitDoubleNextdate($PersonDisp_id, $PersonDispVizit_NextDate, $PersonDispVizit_id) {

		if(empty($PersonDispVizit_NextDate)) {
			return false;
		}

		$where = 'PDV.PersonDisp_id = :PersonDisp_id AND PersonDispVizit_NextDate = :PersonDispVizit_NextDate';
		if(!empty($PersonDispVizit_id)){
			$where .= ' AND PDV.PersonDispVizit_id <> :PersonDispVizit_id';
		}

		$sql = "
			SELECT
				PDV.PersonDispVizit_id
			FROM
				v_PersonDispVizit PDV with(nolock)
			WHERE {$where}
			";

		$res = $this->db->query($sql, array('PersonDisp_id' => $PersonDisp_id, "PersonDispVizit_NextDate" => $PersonDispVizit_NextDate, "PersonDispVizit_id" => $PersonDispVizit_id));
		if ( is_object($res) ) {
			$visitArray = $res->result('array');
			return (!empty($visitArray[0]));
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение Контроля посещений
	 */
	function savePersonDispVizit($data) {

		if ($this->checkVisitDoubleNextdate($data['PersonDisp_id'], $data['PersonDispVizit_NextDate'], $data['PersonDispVizit_id'])) {

			$error = array(
				"Error_Code" => 666,
				"Error_Msg" => 'Назначенная дата явки уже существует в списке контроля посещений. Укажите другую дату в поле "Назначено явиться"',
				"success" => false
			);

			return $error;
		}

		$data['PersonDispVizit_IsHomeDN'] = $data['PersonDispVizit_IsHomeDN'] ? 2 : 1;
		$procedure = $data['PersonDispVizit_id'] ? 'p_PersonDispVizit_upd' : 'p_PersonDispVizit_ins';
		
        $query = "
			declare
				@PersonDispVizit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispVizit_id = :PersonDispVizit_id;
			exec {$procedure}
				@PersonDispVizit_id = @PersonDispVizit_id output,
				@PersonDisp_id = :PersonDisp_id,
				@PersonDispVizit_NextDate = :PersonDispVizit_NextDate,
				@PersonDispVizit_NextFactDate = :PersonDispVizit_NextFactDate,
				@pmUser_id = :pmUser_id,
				@PersonDispVizit_IsHomeDN = :PersonDispVizit_IsHomeDN,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonDispVizit_id as PersonDispVizit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}
	
	/**
	 * Сохранение Контроля посещений
	 */
	function savePersonDispEvnVizitPL($data) {
		if(empty($data['PersonDispVizit_NextFactDate']) || empty($data['EvnVizitPL_id']) || empty($data['pmUser_id'])){
			return false;
		}
		$procedure = 'p_PersonDispVizit_ins';
		$data['PersonDispVizit_id'] = null;
		$data['PersonDispVizit_NextDate'] = null;
		$where = '';
		
		//смотрим есть ли запись, если есть то обновляем
		if(!empty($data['PersonDisp_id'])) $where = ' AND PersonDisp_id = :PersonDisp_id';
		$result = $this->dbmodel->getFirstRowFromQuery("
			select 
				PersonDispVizit_id, 
				PersonDispVizit_NextDate,
				PersonDisp_id
			from 
				v_PersonDispVizit PDV with(nolock) 
			where 
				EvnVizitPL_id = :EvnVizitPL_id 
				{$where}
		", $data);		
		if(!empty($result['PersonDispVizit_id'])){
			//обновляем запись
			$data['PersonDispVizit_id'] = $result['PersonDispVizit_id'];
			$data['PersonDispVizit_NextDate'] = $result['PersonDispVizit_NextDate'];
			if(empty($data['PersonDisp_id'])){
				$data['EvnVizitPL_id'] = null;
				$data['PersonDispVizit_NextFactDate'] = null;
				$data['PersonDisp_id'] = $result['PersonDisp_id'];
			}
			$procedure = 'p_PersonDispVizit_upd';
		}
		
		if(empty($data['PersonDispVizit_id']) && !empty($data['PersonDisp_id'])){
			$result = $this->dbmodel->getFirstRowFromQuery("
				select 
					PersonDispVizit_id, 
					PersonDispVizit_NextDate,
					PersonDisp_id
				from 
					v_PersonDispVizit PDV with(nolock) 
				where 
					PDV.PersonDispVizit_NextDate = :PersonDispVizit_NextFactDate
					AND PersonDisp_id = :PersonDisp_id
			", $data);		
			if(!empty($result['PersonDispVizit_id'])){
				//обновляем запись
				$data['PersonDispVizit_id'] = $result['PersonDispVizit_id'];
				$data['PersonDispVizit_NextDate'] = $result['PersonDispVizit_NextDate'];
				$procedure = 'p_PersonDispVizit_upd';
			}
		}
		
		if(empty($data['PersonDisp_id'])){
			//то создавать нечего, выходим
			return false;
		}
		$query = "
			declare
				@PersonDispVizit_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispVizit_id = :PersonDispVizit_id;
			exec {$procedure}
				@PersonDispVizit_id = @PersonDispVizit_id output,
				@PersonDisp_id = :PersonDisp_id,
				@PersonDispVizit_NextDate = :PersonDispVizit_NextDate,
				@PersonDispVizit_NextFactDate = :PersonDispVizit_NextFactDate,
				@EvnVizitPL_id = :EvnVizitPL_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonDispVizit_id as PersonDispVizit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}

	/**
	 * Удаление Контроля посещений
	 */
	function delPersonDispVizit($data) {
        $query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonDispVizit_del
				@PersonDispVizit_id = :PersonDispVizit_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}

	/**
	 * Загрузка списка Сопутствующих диагнозов
	 */
	function loadPersonDispSopDiaglist($data) {		
		$sql = "
			SELECT
				PDSD.PersonDispSopDiag_id,
				D.Diag_Code,
				D.Diag_Name,
				PDDT.DopDispDiagType_Name
			FROM
				v_PersonDispSopDiag PDSD with(nolock)
				left join v_Diag D with (nolock) on PDSD.Diag_id  = D.Diag_id
				left join v_DopDispDiagType PDDT with (nolock) on PDSD.DopDispDiagType_id  = PDDT.DopDispDiagType_id
			WHERE
				PDSD.PersonDisp_id = :PersonDisp_id
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка Сопутствующих диагнозов
	 */
	function loadPersonDispSopDiag($data) {		
		$sql = "
			SELECT
				PDSD.PersonDispSopDiag_id,
				PDSD.PersonDisp_id,
				PDSD.Diag_id,
				PDSD.DopDispDiagType_id
			FROM
				v_PersonDispSopDiag PDSD with(nolock)
			WHERE
				PDSD.PersonDispSopDiag_id = :PersonDispSopDiag_id
		";
		$res = $this->db->query($sql, array('PersonDispSopDiag_id' => $data['PersonDispSopDiag_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение Сопутствующих диагнозов
	 */
	function savePersonDispSopDiag($data) {

		$procedure = $data['PersonDispSopDiag_id'] ? 'p_PersonDispSopDiag_upd' : 'p_PersonDispSopDiag_ins';
		
        $query = "
			declare
				@PersonDispSopDiag_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispSopDiag_id = :PersonDispSopDiag_id;
			exec {$procedure}
				@PersonDispSopDiag_id = @PersonDispSopDiag_id output,
				@PersonDisp_id = :PersonDisp_id,
				@Diag_id = :Diag_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonDispSopDiag_id as PersonDispSopDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}

	/**
	 * Удаление Сопутствующих диагнозов
	 */
	function delPersonDispSopDiag($data) {
        $query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonDispSopDiag_del
				@PersonDispSopDiag_id = :PersonDispSopDiag_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}

	/**
	 * Загрузка списка врачей, ответственных за наблюдение
	 */
	function loadPersonDispHistlist($data) {		
		$sql = "
			SELECT
				PDSD.PersonDispHist_id,
				PDSD.MedPersonal_id,
				PDSD.LpuSection_id,
				D.Person_Fio as MedPersonal_Fio,
				PDDT.LpuSection_Name,
				convert(varchar,PersonDispHist_begDate,104) as PersonDispHist_begDate,
				convert(varchar,PersonDispHist_endDate,104) as PersonDispHist_endDate
			FROM
				v_PersonDispHist PDSD with(nolock)
				outer apply (select top 1 D2.Person_Fio from v_MedPersonal D2 with (nolock) where D2.MedPersonal_id = PDSD.MedPersonal_id) D
				left join v_LpuSection PDDT with (nolock) on PDSD.LpuSection_id  = PDDT.LpuSection_id
			WHERE
				PDSD.PersonDisp_id = :PersonDisp_id
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка Отвественного врача
	 */
	function loadPersonDispHist($data) {		
		$sql = "
			SELECT
				PDSD.PersonDispHist_id,
				PDSD.PersonDisp_id,
				PDSD.MedPersonal_id,
				PDSD.LpuSection_id,
				convert(varchar,PersonDispHist_begDate,104) as PersonDispHist_begDate,
				convert(varchar,PersonDispHist_endDate,104) as PersonDispHist_endDate
			FROM
				v_PersonDispHist PDSD with(nolock)
			WHERE
				PDSD.PersonDispHist_id = :PersonDispHist_id
		";
		$res = $this->db->query($sql, array('PersonDispHist_id' => $data['PersonDispHist_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение Ответственного врача
	 */
	function savePersonDispHist($data) {

		$check = $this->checkPersonDispHistDates($data);
		if(!empty($check) && !empty($check['Error_Msg'])){
			return $check;
		}

		$procedure = $data['PersonDispHist_id'] ? 'p_PersonDispHist_upd' : 'p_PersonDispHist_ins';
		
		//PROMEDWEB-9809
		//По какой-то причине - вызов хранимки на уфе и на других регионах отличается
		$notUfaAdding = $this->getRegionNick() != 'ufa' 
			? '@MedStaffFact_id = :MedStaffFact_id,'
			: '';
		
        $query = "
			declare
				@PersonDispHist_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispHist_id = :PersonDispHist_id;
			exec {$procedure}
				@PersonDispHist_id = @PersonDispHist_id output,
				@PersonDisp_id = :PersonDisp_id,
				@MedPersonal_id = :MedPersonal_id,
				@LpuSection_id = :LpuSection_id,
				@PersonDispHist_begDate = :PersonDispHist_begDate,
				@PersonDispHist_endDate = :PersonDispHist_endDate,
				@pmUser_id = :pmUser_id,
				{$notUfaAdding}
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select 
				@PersonDispHist_id as PersonDispHist_id, 
				@ErrCode as Error_Code, 
				@ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}

	/**
	 * Проверка на пересечение периодов действия отвественных врачей
	 */
	function checkPersonDispHistDates($data) {
		$personDispHist_begDate = strtotime($data['PersonDispHist_begDate']);
		$personDispHist_endDate = null;
		if(!empty($data['PersonDispHist_endDate'])){
			$personDispHist_endDate = strtotime($data['PersonDispHist_endDate']);
		}
        $query = "
			select top 1
			convert(varchar,PersonDisp_begDate,104) as PersonDisp_begDate, 
			convert(varchar,PersonDisp_endDate,104) as PersonDisp_endDate
			from v_PersonDisp with (nolock) 
			where PersonDisp_id = :PersonDisp_id 
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            $result = $result->result('array');
            if(empty($result[0]['PersonDisp_begDate'])){
            	return array('Error_Msg'=>'Ошибка при получении данных по диспансерной карте.');
            }
			$personDisp_begDate = strtotime($result[0]['PersonDisp_begDate']);
			$personDisp_endDate = null;
			if(!empty($result[0]['PersonDisp_endDate'])){
				$personDisp_endDate = strtotime($result[0]['PersonDisp_endDate']);
			}
			
            if($personDispHist_begDate < $personDisp_begDate){
            	return array('Error_Msg'=>'Дата начала не может быть раньше даты взятия под наблюдение');
            }
            if(!empty($personDisp_endDate) && $personDispHist_begDate > $personDisp_endDate){
            	return array('Error_Msg'=>'Дата начала не может быть позже даты снятия с наблюдения');
            }

            $where = "";
            if(!empty($data['PersonDispHist_id'])){
            	$where = " and PersonDispHist_id <> :PersonDispHist_id";
            }

            $query = "
				select
				convert(varchar,PersonDispHist_begDate,104) as PersonDispHist_begDate, 
				convert(varchar,PersonDispHist_endDate,104) as PersonDispHist_endDate
				from v_PersonDispHist with (nolock) 
				where PersonDisp_id = :PersonDisp_id {$where}
			";
	        $result = $this->db->query($query, $data);
	        if ( is_object($result) ){
	            $result = $result->result('array');
	            if(count($result) > 0){
	            	$error = false;
	            	foreach ($result as $value) {
	            		$begDate = strtotime($value['PersonDispHist_begDate']);
	            		$endDate = null;
						if(!empty($value['PersonDispHist_endDate'])){
							$endDate = strtotime($value['PersonDispHist_endDate']);
							if(!empty($personDispHist_endDate)){
								if($personDispHist_begDate <= $begDate && $personDispHist_endDate >= $endDate){
									$error = true;
									break;
								}
								if($personDispHist_begDate >= $begDate && $personDispHist_endDate <= $endDate){
									$error = true;
									break;
								}
								if($personDispHist_begDate <= $begDate && $personDispHist_endDate <= $endDate && $personDispHist_endDate >= $begDate){
									$error = true;
									break;
								}
								if($personDispHist_begDate >= $begDate && $personDispHist_endDate >= $endDate && $personDispHist_begDate <= $endDate){
									$error = true;
									break;
								}
							} else {
								if($personDispHist_begDate <= $begDate){
									$error = true;
									break;
								}
								if($personDispHist_begDate > $begDate && $personDispHist_begDate <= $endDate){
									$error = true;
									break;
								}
							}
						} else {
							if($personDispHist_begDate >= $begDate){
								$error = true;
								break;
							} else {
								if(!empty($personDispHist_endDate)){
									if($personDispHist_begDate < $begDate && $personDispHist_endDate >= $begDate){
										$error = true;
										break;
									}
								} else {
									// Два бесконечных периода в любом случае пересекаются
									$error = true;
									break;
								}
							}
						}
	            	}
	            	if($error == true){
						return array('Error_Msg'=>'Периоды ответственности не должны пересекаться');
					}
	            } else {
	            	return false;
	            }
	        } else {
	            return false;
			}
        } else {
            return false;
		}
	}

	/**
	 * Удаление Ответственного врача
	 */
	function deletePersonDispHist($data) {
        $query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonDispHist_del
				@PersonDispHist_id = :PersonDispHist_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( is_object($result) ){
            return $result->result('array');
        } else {
            return false;
		}
	}
	
	/**
	 * Загрузка списка Целевых показателей
	 */
	function loadPersonDispTargetRateList($data) {
		// TODO: Возможно, эти параметры стоит вынести в конфиги
		$rateTypes = array(
			53 => 130, 
			54 => 90, 
			55 => array(102, 88),
			58 => 7, 
			145 => 30, 
			146 => 5, 
			147 => 2
		);
		$sql = "
			SELECT
				RT.RateType_id,
				RT.RateType_Name,
				PDFR.PersonDispFactRate_id,
				PDFR.Rate_id,
				PDTR.RateValue as TargetRate_Value,
				PDFR.RateValue as FactRate_Value,
				convert(varchar(10),PDFR.PersonDispFactRate_setDT,104) as FactRate_setDT,
				PS.Sex_id
			FROM
				v_RateType RT (nolock)
				inner join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
				outer apply (
					select top 1 
					case
						when RVT.RateValueType_SysNick = 'int' THEN cast(R.Rate_ValueInt as varchar)
						when RVT.RateValueType_SysNick = 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
					end as RateValue
					from v_Rate R with (nolock)
					inner join v_PersonDispTargetRate PDTR with (nolock) on PDTR.Rate_did = R.Rate_id
					where R.RateType_id  = RT.RateType_id and PDTR.PersonDisp_id = :PersonDisp_id
					order by PDTR.PersonDispTargetRate_setDT desc
				) as PDTR
				outer apply (
					select top 1 
					case
						when RVT.RateValueType_SysNick = 'int' THEN cast(R.Rate_ValueInt as varchar)
						when RVT.RateValueType_SysNick = 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
					end as RateValue, 
					PDFR.PersonDispFactRate_setDT,
					PDFR.PersonDispFactRate_id,
					PDFR.Rate_id
					from v_Rate R with (nolock)
					inner join v_PersonDispFactRate PDFR with (nolock) on PDFR.Rate_id = R.Rate_id
					where R.RateType_id  = RT.RateType_id and PDFR.PersonDisp_id = :PersonDisp_id
					order by PDFR.PersonDispFactRate_setDT desc, PDFR.PersonDispFactRate_id desc
				) as PDFR
				outer apply (
					select top 1 PS.Sex_id
					from v_PersonSex PS with (nolock)
					inner join v_PersonDisp PD with (nolock) on PD.Person_id = PS.Person_id
					where PD.PersonDisp_id = :PersonDisp_id
				) as PS
			WHERE
				RT.RateType_id IN (".join(',', array_keys($rateTypes)).")
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as &$r) {
				if (empty($r['TargetRate_Value'])) {
					if ($r['RateType_id'] == 55) {
						$r['TargetRate_Value'] = $rateTypes[$r['RateType_id']][($r['Sex_id']==2)];	
					} else {
						$r['TargetRate_Value'] = $rateTypes[$r['RateType_id']];		
					}
				}
			}
			return $res;
		} else {
			return false;
		}		
	
	}
	
	/**
	 * Загрузка Целевых показателей
	 */
	function loadPersonDispTargetRate($data) {
		// TODO: Возможно, эти параметры стоит вынести в конфиги
		$rateTypes = array(
			53 => 130, 
			54 => 90, 
			55 => array(102, 88),
			58 => 7, 
			145 => 30, 
			146 => 5, 
			147 => 2
		);
		$sql = "
			SELECT TOP 1
				RT.RateType_id,
				RT.RateType_Name,
				RVT.RateValueType_SysNick,
				PDTR.RateValue as TargetRate_Value,
				PS.Sex_id
			FROM
				v_RateType RT (nolock)
				inner join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
				outer apply (
					select top 1 
					case
						when RVT.RateValueType_SysNick = 'int' THEN cast(R.Rate_ValueInt as varchar)
						when RVT.RateValueType_SysNick = 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
					end as RateValue
					from v_Rate R with (nolock)
					inner join v_PersonDispTargetRate PDTR with (nolock) on PDTR.Rate_did = R.Rate_id
					where R.RateType_id  = RT.RateType_id and PDTR.PersonDisp_id = :PersonDisp_id
					order by PDTR.PersonDispTargetRate_setDT desc
				) as PDTR
				outer apply (
					select top 1 PS.Sex_id
					from v_PersonSex PS with (nolock)
					inner join v_PersonDisp PD with (nolock) on PD.Person_id = PS.Person_id
					where PD.PersonDisp_id = :PersonDisp_id
				) as PS
			WHERE
				RT.RateType_id = :RateType_id
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id'], 'RateType_id' => $data['RateType_id']));
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as &$r) {
				if (empty($r['TargetRate_Value'])) {
					if ($r['RateType_id'] == 55) {
						$r['TargetRate_Value'] = $rateTypes[$r['RateType_id']][($r['Sex_id']==2)];	
					} else {
						$r['TargetRate_Value'] = $rateTypes[$r['RateType_id']];		
					}
				}
			}
			return $res;
		} else {
			return false;
		}	
	}
	
	/**
	 * Сохранение Целевых показателей
	 */
	function savePersonDispTargetRate($data) {
		
		$this->db->trans_begin();
		
		// ---------------- сохраняем целевые значения ------------
		
		$sql = "
			select top 1
			case
				when RVT.RateValueType_SysNick = 'int' THEN cast(R.Rate_ValueInt as varchar)
				when RVT.RateValueType_SysNick = 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
				when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
			end as RateValue
			from v_PersonDispTargetRate PDTR with(nolock)
			inner join v_Rate R (nolock) on R.Rate_id = PDTR.Rate_did
			inner join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
			inner join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
			where PDTR.PersonDisp_id = :PersonDisp_id and R.RateType_id = :RateType_id
			order by PersonDispTargetRate_setDT desc
		";		
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id'], 'RateType_id' => $data['RateType_id']));			
		if ( !is_object($res) ) {
			return false;
		}
		
		$res = $res->result('array');
		if (!count($res) || $res[0]['RateValue'] != $data['TargetRate_Value']) {
			$sql = "
				declare
					@Rate_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Rate_id = :Rate_id;
				exec p_Rate_ins
					@Rate_id = @Rate_id output,
					@RateType_id = :RateType_id,
					@Rate_ValueInt = :Rate_ValueInt,
					@Rate_ValueFloat = :Rate_ValueFloat,
					@Rate_ValueStr = :Rate_ValueStr,
					@Server_id = :Server_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'Rate_id' => NULL,
				'RateType_id' => $data['RateType_id'],
				'Rate_ValueInt' => NULL,
				'Rate_ValueFloat' => NULL,
				'Rate_ValueStr' => NULL,
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			switch ($data['RateValueType_SysNick']) {
				case 'int': $queryParams['Rate_ValueInt'] = $data['TargetRate_Value']; break;
				case 'float': $queryParams['Rate_ValueFloat'] = $data['TargetRate_Value']; break;
				case 'string': $queryParams['Rate_ValueStr'] = $data['TargetRate_Value']; break;
			}

			$res = $this->db->query($sql, $queryParams);
			if ( !is_object($res) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение целевого показателя)'));
			}
			$resp = $res->result('array');
			
			$sql = "
				declare
					@PersonDispTargetRate_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@getdate datetime = dbo.tzGetDate();
				set @PersonDispTargetRate_id = :PersonDispTargetRate_id;
				exec p_PersonDispTargetRate_ins
					@PersonDispTargetRate_id = @PersonDispTargetRate_id output,
					@PersonDisp_id = :PersonDisp_id,
					@PersonDispTargetRate_setDT = @getdate,
					@Rate_did = :Rate_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @PersonDispTargetRate_id as PersonDispTargetRate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'PersonDispTargetRate_id' => NULL,
				'PersonDisp_id' => $data['PersonDisp_id'],
				'Rate_id' => $resp[0]['Rate_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			$res = $this->db->query($sql, $queryParams);
			if ( !is_object($res) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение целевого показателя)'));
			}
			$resp = $res->result('array');			
		}	
		
		// ---------------- сохраняем фактические значения ------------
		
		$PersonDispFactRateData = json_decode($data['PersonDispFactRateData'], true);
		
		if (is_array($PersonDispFactRateData) && count($PersonDispFactRateData)) {			
			foreach ($PersonDispFactRateData as $prfr) {	
				$prfr = array_merge($data, $prfr);
				switch($prfr['RecordStatus_Code']) {
					case 1:
						break;
					case 0:
					case 2:	
						$resp = $this->savePersonDispFactRate($prfr);
						break;
					case 3:
						$resp = $this->deletePersonDispFactRate($prfr);
				}			
			}
		}		
		
		$this->db->trans_commit();
		return array(array('success' => true, 'Error_Code' => '', 'Error_Msg' => ''));	
	}
	
	/**
	 * Загрузка списка Фактических показателей
	 */
	function loadPersonDispFactRateList($data) {
		$sql = "
			select 
			PersonDispFactRate_id,
			1 as RecordStatus_Code,
			R.Rate_id,
			case
				when RVT.RateValueType_SysNick = 'int' THEN cast(R.Rate_ValueInt as varchar)
				when RVT.RateValueType_SysNick = 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
				when RVT.RateValueType_SysNick = 'string' THEN R.Rate_ValueStr
			end as PersonDispFactRate_Value, 
			convert(varchar(10),PDFR.PersonDispFactRate_setDT,104) as PersonDispFactRate_setDT
			from v_Rate R with (nolock)
			inner join v_PersonDispFactRate PDFR with (nolock) on PDFR.Rate_id = R.Rate_id
			inner join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
			inner join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
			where R.RateType_id = :RateType_id and PDFR.PersonDisp_id = :PersonDisp_id
			order by PDFR.PersonDispFactRate_setDT asc, PersonDispFactRate_id asc
		";
		$res = $this->db->query($sql, array('PersonDisp_id' => $data['PersonDisp_id'], 'RateType_id' => $data['RateType_id']));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Сохранение Фактических показателей
	 */
	function savePersonDispFactRate($data) {
		
		$proc = (!empty($data['PersonDispFactRate_id']) && $data['PersonDispFactRate_id'] > 0) ? 'upd' : 'ins';
		
		$sql = "
			declare
				@Rate_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Rate_id = :Rate_id;
			exec p_Rate_{$proc}
				@Rate_id = @Rate_id output,
				@RateType_id = :RateType_id,
				@Rate_ValueInt = :Rate_ValueInt,
				@Rate_ValueFloat = :Rate_ValueFloat,
				@Rate_ValueStr = :Rate_ValueStr,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'Rate_id' => ($proc == 'upd') ? $data['Rate_id'] : NULL,
			'RateType_id' => $data['RateType_id'],
			'Rate_ValueInt' => NULL,
			'Rate_ValueFloat' => NULL,
			'Rate_ValueStr' => NULL,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		switch ($data['RateValueType_SysNick']) {
			case 'int': $queryParams['Rate_ValueInt'] = $data['PersonDispFactRate_Value']; break;
			case 'float': $queryParams['Rate_ValueFloat'] = $data['PersonDispFactRate_Value']; break;
			case 'string': $queryParams['Rate_ValueStr'] = $data['PersonDispFactRate_Value']; break;
		}

		$res = $this->db->query($sql, $queryParams);
		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение фактического показателя)'));
		}
		$resp = $res->result('array');
		
		$sql = "
			declare
				@PersonDispFactRate_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonDispFactRate_id = :PersonDispFactRate_id;
			exec p_PersonDispFactRate_{$proc}
				@PersonDispFactRate_id = @PersonDispFactRate_id output,
				@PersonDisp_id = :PersonDisp_id,
				@PersonDispFactRate_setDT = :PersonDispFactRate_setDT,
				@Rate_id = :Rate_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonDispFactRate_id as PersonDispFactRate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'PersonDispFactRate_id' => ($proc == 'upd') ? $data['PersonDispFactRate_id'] : NULL,
			'PersonDisp_id' => $data['PersonDisp_id'],
			'Rate_id' => ($proc == 'upd') ? $data['Rate_id'] : $resp[0]['Rate_id'],
			'PersonDispFactRate_setDT' => $data['PersonDispFactRate_setDT'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($sql, $queryParams);
		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение фактического показателя)'));
		}
		
		return true;
	}
	
	/**
	 * Удаление Фактических показателей
	 */
	function deletePersonDispFactRate($data) {
		
        $query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonDispFactRate_del
				@PersonDispFactRate_id = :PersonDispFactRate_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( !is_object($result) ){
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление фактического показателя)'));
        }
		
        $query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_Rate_del
				@Rate_id = :Rate_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
        $result = $this->db->query($query, $data);
        if ( !is_object($result) ){
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление фактического показателя)'));
        }

		return true;
	}

	/**
	 * Получение списка дис. карт пациента
	 */
	function loadPersonDispList($data) {
		$filter = "(1 = 1)";
		if (!empty($data['PersonDisp_id'])) {
			$filter .= " and pd.PersonDisp_id = :PersonDisp_id";
		} else {
			$filter .= " and pd.Person_id = :Person_id and pd.Lpu_id = :Lpu_id";

			if (!empty($data['onDate'])) {
				$filter .= " and pd.PersonDisp_begDate <= :onDate";

				if (!in_array($this->regionNick, ['krasnoyarsk','vologda'])) {
					$filter .= " and ISNULL(pd.PersonDisp_endDate,:onDate) >= :onDate";
				}
			}
		}

		$query = "
			select
				pd.PersonDisp_id,
				ISNULL(convert(varchar(10), pd.PersonDisp_begDate, 104), '...') + ' - ' + ISNULL(convert(varchar(10), pd.PersonDisp_endDate, 104),'...') + ' ' + ISNULL(d.Diag_Code + ' ', '') + ISNULL(d.Diag_Name + ' ', '') as PersonDisp_Name
			from
				v_PersonDisp pd (nolock)
				left join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
			where
				{$filter}
			order by
				pd.PersonDisp_begDate desc
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения
	 */
	function exportPersonDispCard($data) {

		$region = $this->getRegionNumber();
		$VizitType_SysNick = $this->getVizitTypeSysNick();

		if ($data['Year'] < 1753) {
			$data['Year'] = 1753;
		}

		$periodMonthYear = $data['Year'] . str_pad($data['Month'], 2, "0", STR_PAD_LEFT);

		if ($data['Month'] != 12) {
			$endMonth = str_pad($data['Month']+1, 2, "0", STR_PAD_LEFT);
			$endYear = $data['Year'];
			$begMonth = str_pad($data['Month'], 2, "0", STR_PAD_LEFT);
			$begYear = $endYear;
		} else {
			$endMonth = '01';
			$endYear = $data['Year']+1;
			$begMonth = '12';
			$begYear = $data['Year'];
		}
		$allresult = array();
		// Все СМО региона http://redmine.swan.perm.ru/issues/87551#note-30
		$query = "
			select OSmo.OrgSMO_id, OSmo.Orgsmo_f002smocod
			from v_OrgSMO OSmo (nolock)
			where 
				OSmo.KLRgn_id = :Region AND
				(OSmo.OrgSmo_endDate is null or (OSmo.OrgSmo_endDate) >= :endDate) 
				AND isnull(OSmo.OrgSMO_isDMS,1) = 1
		";

		$allOrgSmo = $this->db->query($query, array(
			'endDate' => ($endYear.'-'.$endMonth.'-01'),
			'Region' => $region
		));

		if ( !is_object($allOrgSmo) ) {
			return array('Error_Msg' => 'Ошибка получения данных по СМО');
		}

		$allOrgSmo = $allOrgSmo->result('array');

		if ( '09' == $begMonth ) {
			$filter = '
				(pd.PersonDisp_begDate is null or pd.PersonDisp_begDate < :endDate)
				and (pd.PersonDisp_endDate is null or pd.PersonDisp_endDate >= :endDate)
			';
		}
		else {
			$filter = '
				(
					(
						YEAR(pd.PersonDisp_begDate) = :Year
						and MONTH(pd.PersonDisp_begDate) = :Month
					) 
					or (
						YEAR(pd.PersonDisp_endDate) = :Year
						and MONTH(pd.PersonDisp_endDate) = :Month
					)
					or exists (
						select top 1 
							PersonDispVizit_id
						from 
							v_PersonDispVizit with (nolock)
						where
							PersonDisp_id = pd.PersonDisp_id
							and YEAR(PersonDispVizit_NextFactDate) = :Year
							and MONTH(PersonDispVizit_NextFactDate) = :Month
					)
				)
			';
		}
		
		if ( $this->GetRegionNick() == 'kareliya' ) {
			$filter .= "
				and pls.Polis_begDate <= @periodEndDate
				and isnull(pls.Polis_endDate,'2100-01-01') >= :begDate
			";
		}

		foreach ($allOrgSmo as $orgsmo) {
			$query = "
				declare
					@curDate datetime = dbo.tzGetDate(),
					@periodEndDate datetime = DATEADD(DAY, -1, :endDate),
					@periodMonthYear varchar(6) = :periodMonthYear,
					@Lpu_id bigint = :Lpu_id,
					@OrgSMO_id bigint = :OrgSMO_id;

				select
					ps.Person_id as ID_PAC,
					RTRIM(ps.Person_Surname) as FAM,
					RTRIM(ps.Person_Firname) as IM,
					RTRIM(ps.Person_Secname) as OT,
					ps.Person_Birthday as DR,
					sx.Sex_fedid as W,
					ps.Person_Snils as SNILS,
					pt.PolisType_CodeF008 as VPOLIS,
					pls.Polis_Ser as SPOLIS,
					pls.Polis_Num as NPOLIS,
					pd.PersonDisp_begDate as DATE_IN,
					dg.Diag_Code as DS_DISP,
					case when pd.PersonDisp_endDate is not null and convert(varchar(6), pd.PersonDisp_endDate, 112) <= @periodMonthYear then pd.PersonDisp_endDate else null end as DATE_OUT,
					case when pd.PersonDisp_endDate is not null and convert(varchar(6), pd.PersonDisp_endDate, 112) <= @periodMonthYear then dot.DispOutType_Code else null end as RESULT_OUT,
					ISNULL(case
						when LD.PersonDisp_LastDate is not null and lapdv.PersonDispVizit_NextFactDate is not null
						then
							case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate then LD.PersonDisp_LastDate else lapdv.PersonDispVizit_NextFactDate end
						else isnull(lapdv.PersonDispVizit_NextFactDate,LD.PersonDisp_LastDate)
					end, pd.PersonDisp_begDate) as DATE_POC,
					mp.Person_Snils as SNILS_VR,
					pd.PersonDisp_id,
					pls.OrgSMO_id,
					pd.Lpu_id
				from v_PersonDisp pd with (nolock)
					left join dbo.v_PersonState ps with (nolock) on pd.Person_id = ps.Person_id
					outer apply (
						select top 1
							pdv.PersonDispVizit_NextFactDate
						from
							v_PersonDispVizit pdv with (nolock)
						where
							pd.PersonDisp_id = pdv.PersonDisp_id
						order by
							pdv.PersonDispVizit_NextFactDate desc
					) lapdv
					outer apply(
						select top 1
							EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
						from
							v_EvnVizitPL EVPL with(nolock)
							left join v_VizitType VT with(nolock) on VT.VizitType_id = EVPL.VizitType_id
						where
							VT.VizitType_SysNick='".$VizitType_SysNick."'
							and cast(PD.PersonDisp_begDate as date)<=cast(EVPL.EvnVizitPL_setDT as date)
							and PD.Diag_id = EVPL.Diag_id
							and EVPL.Person_id = PD.Person_id
						order by
							EVPL.EvnVizitPL_setDT desc
					) LD
					left join v_DispOutType dot with (nolock) on pd.DispOutType_id = dot.DispOutType_id
					outer apply (
						select top 1 mpp.Person_Snils
						from v_MedPersonal mpp with (nolock)
						inner join v_PersonDispHist pdhist with (nolock) on pdhist.MedPersonal_id = mpp.MedPersonal_id
						where pdhist.PersonDisp_id = pd.PersonDisp_id
							and pdhist.PersonDispHist_begDate < :endDate
							and (
								pdhist.PersonDispHist_endDate is null
								or pdhist.PersonDispHist_endDate >= :begDate
							)
						order by pdhist.PersonDispHist_begDate desc
					) mp
					left join dbo.v_Sex sx with (nolock) on sx.Sex_id = ps.Sex_id
					left join dbo.v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
					left join dbo.v_PolisType pt with (nolock) on pt.PolisType_id = pls.PolisType_id
					left join dbo.v_Diag dg with (nolock) on dg.Diag_id = pd.Diag_id
					outer apply (
						select top 1
							dsd.DispSickDiag_id
						from
							r10.v_DispSickDiag dsd (nolock)
						where
							dsd.Diag_id = pd.Diag_id
							and ISNULL(dsd.DispSickDiag_begDT, @periodEndDate) <= @periodEndDate
							and ISNULL(dsd.DispSickDiag_endDT, @periodEndDate) >= @periodEndDate
					) dsd
				where
					{$filter}
					and pd.Lpu_id = @Lpu_id
					and pls.OrgSMO_id = @OrgSMO_id
					and (dg.Diag_Code between 'C00' and 'C97.9' or dbo.Age2(ps.Person_BirthDay, @periodEndDate) >= 18) -- пациентов старше 18 лет или диагноз из диапазона С00 – С97
					and (
						(dg.Diag_Code not between 'C00' and 'C97.9' and dsd.DispSickDiag_id  is not null )
						or
						dg.Diag_Code between 'C00' and 'C97.9'
					)
			";

			$result = $this->db->query($query, array(
				'endDate' => ($endYear.'-'.$endMonth.'-01'),
				'begDate' => ($begYear.'-'.$begMonth.'-01'),
				'Year' => $data['Year'],
				'Month' => $data['Month'],
				'periodMonthYear' => $periodMonthYear,
				'Lpu_id' => $data['Lpu_id'],
				'OrgSMO_id' => $orgsmo['OrgSMO_id'],
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = array();

			while ( $row = $result->_fetch_assoc() ) {
				// @task https://redmine.swan.perm.ru/issues/110483
				$row['DS_DISP'] = trim($row['DS_DISP'], '.');
				$row['OrgSMO_id'] = $orgsmo['Orgsmo_f002smocod'];

				switch ( $row['RESULT_OUT'] ) {
					case 4:
					case 5:
					case 6:
					case 7:
						$row['RESULT_OUT'] = 4;
						break;
				}

				foreach ( $row as $key => $value ) {
					if ( $value instanceof DateTime ) {
						$row[$key] = $value->format('Y-m-d');
					}
				}

				if ( !isset($response[$row['ID_PAC']]) ) {
					$response[$row['ID_PAC']] = array(
						'ID_PAC' => $row['ID_PAC'],
						'FAM' => $row['FAM'],
						'IM' => $row['IM'],
						'OT' => $row['OT'],
						'DR' => $row['DR'],
						'W' => $row['W'],
						'SNILS' => $row['SNILS'],
						'VPOLIS' => $row['VPOLIS'],
						'SPOLIS' => $row['SPOLIS'],
						'NPOLIS' => $row['NPOLIS'],
						'DN_FACT' => array(),
					);
				}

				$response[$row['ID_PAC']]['DN_FACT'][] = array(
					'DATE_IN' => $row['DATE_IN'],
					'DS_DISP' => $row['DS_DISP'],
					'SNILS_VR' => $row['SNILS_VR'],
					'DATE_POC' => $row['DATE_POC'],
					'DATE_OUT' => $row['DATE_OUT'],
					'RESULT_OUT' => $row['RESULT_OUT'],
				);
			}

			$allresult[$orgsmo['Orgsmo_f002smocod']] = $response;
		}
		
		return $allresult;
	}

	/**
	 * Получение списка дисп.учета пациента для ЭМК
	 */
	function loadPersonDispPanel($data) {
		$filters = array("PD.Person_id = :Person_id");
		$select = "";
		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filters = array(
				"PD.Person_id in ({$data['person_in']})"
			);
			$select = " ,PD.Person_id ";
		}

		if (!haveARMType('spec_mz')) {
			$filters = array_merge(
				getAccessRightsDiagFilter('D.Diag_Code', true),
				getAccessRightsLpuFilter('PD.Lpu_id', true),
				getAccessRightsLpuBuildingFilter('LS.LpuBuilding_id', true),
				$filters
			);
		}

		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		$signFilter = "";
		if (!isLpuAdmin() && !empty($data['session']['medpersonal_id'])) {
			$signFilter = " and PDH.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		} else if (isLpuAdmin() && !empty($data['Lpu_id'])) {
			$signFilter = " and PDHLS.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($signFilter)) {
			$signAccess = "case when exists(
					select top 1
						PDH.PersonDispHist_id
					from
						v_PersonDispHist PDH (nolock)
						left join v_LpuSection PDHLS (nolock) on PDHLS.LpuSection_id = PDH.LpuSection_id
					where
						PDH.PersonDisp_id = PD.PersonDisp_id
						and ISNULL(PDH.PersonDispHist_begDate, @curDate) <= @curDate
						and ISNULL(PDH.PersonDispHist_endDate, @curDate) >= @curDate
						{$signFilter}
				) then 'edit' else 'view' end as signAccess
			";
		} else {
			$signAccess = "'view' as signAccess";
		}

		return $this->queryResult("
			declare @curDate date = dbo.tzGetDate();

			select
				PD.PersonDisp_id,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_setDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
				PD.PersonDisp_IsSignedEP,
				L.Lpu_Nick,
				D.Diag_Name,
				D.Diag_Code,
				LS.LpuSectionProfile_Name,
				{$signAccess}
				{$select}
			from
				v_PersonDisp PD with (nolock)
				left join v_Diag D with (nolock) on D.Diag_id = PD.Diag_id
				left join v_Lpu L with (nolock) on L.Lpu_id = PD.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = PD.LpuSection_id
			where
				" . implode(" and ", $filters) . "
    	", $queryParams);
	}
	
	/**
	 * ДМ: Количество меток
	 */
	function getPersonLabelCounts($data) {
		$params = array();
		$params['Lpu_id'] = $data['MonitorLpu_id'];
		$params['outBegDate'] = $data['outBegDate'];
		$params['outEndDate'] = $data['outEndDate'];
		$tabs = array('new' => 0, 'on' => 0, 'off' => 0, 'all' => 0);

		if(!empty($data['Label_id'])) {
			$params['Label_id'] = $data['Label_id'];
		} else {
			$params['Label_id'] = 1;
		}
		
		foreach($tabs as $key => $tab) {
			
			$filter = " 
				and PL.Label_id = :Label_id 
				and PS.Lpu_id = :Lpu_id 
			";
			
			$inner_filter = "";
			$exist_join = "";
			
			switch($key) {
				case 'new':	//Новые
					
					//	У человека есть запись о наличии метки (запись открыта)
					//	У человека нет связанной с меткой карты наблюдений в МО Пользователя (как открытой, так и закрытой)
					$filter.= " 
						and LOC.LabelObserveChart_id is null 
						and PL.PersonLabel_disDate is null
					";
					break;
					
				case 'on': //Включенные
					
					//	У человека есть открытая карта наблюдений в МО Пользователя
					$filter.= "
						and LOC.LabelObserveChart_id is not null 
					";
					
					$inner_filter = "
						and LOCO.LabelObserveChart_endDate is null
					";
					break;
					
				case 'off': //Выбывшие 
					
					//	У человека есть карта наблюдений в МО Пользователя с заполненной датой закрытия
					$filter.= "
						and LOC.LabelObserveChart_id is not null
						and LOCExist.LabelObserveChart_id is null
					";

					$inner_filter = "
						and LOCO.LabelObserveChart_endDate is not null
					";

					$exist_join .= "
						outer apply (
							select top 1 
								LOCO.LabelObserveChart_id,
								LOCO.LabelObserveChart_endDate
							from v_LabelObserveChart LOCO
								inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = LOCO.MedStaffFact_id
							WHERE LOCO.PersonLabel_id = PL.PersonLabel_id
								AND MSF.Lpu_id = :Lpu_id
								and LOCO.LabelObserveChart_endDate is null
						) LOCexist
					";
					break;
					
				case 'all': //Все пациенты
					//	У человека есть запись о наличии метки (запись открыта) 
					//	У человека есть карта наблюдений в МО Пользователя (как открытая, так и закрытая)
					$filter.= "
						and PL.PersonLabel_disDate is null 
						and LOC.LabelObserveChart_id is not null
					";
					break;
			}
			if(($key=='off' or $key=='all') and !empty($data['outBegDate']) and !empty($data['outEndDate']) ) {
				$filter.= ' and ((LOC.LabelObserveChart_endDate > :outBegDate 
				AND LOC.LabelObserveChart_endDate < :outEndDate) OR LOC.LabelObserveChart_endDate is null)';
			}
			
			$query = "
				SELECT count(PL.PersonLabel_id)
				FROM v_PersonLabel PL (nolock) 
					inner join v_PersonState PS (nolock) on PS.Person_id = PL.Person_id
					{$exist_join}
					outer apply (
						select top 1 
							LOCO.LabelObserveChart_id,
							LOCO.LabelObserveChart_endDate
						from v_LabelObserveChart LOCO
							inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = LOCO.MedStaffFact_id
						WHERE LOCO.PersonLabel_id = PL.PersonLabel_id
							AND MSF.Lpu_id = :Lpu_id
							{$inner_filter}
					) LOC
				WHERE (1=1) {$filter}
			";
			//~ echo getDebugSQL($query, $params);//exit;
			$count = $this->getFirstResultFromQuery($query, $params);
			$tabs[$key] = $count;
		}

		return $tabs;
	}
	
	/**
	 * ДМ: Получить список пациентов для дистанционного мониторинга
	 * Используется: Дистанционный мониторинг (RemoteMonitoringWindow.js)
	 */
	function loadPersonLabelList($data) {
		$select = array();
		$filter = array();
		$joinList = array();
		$filter1 = array();//(только PL & LOC)
		$filter2 = array();
		$params = array();
		
		if(!empty($data['Label_id'])) {
			$params['Label_id'] = $data['Label_id'];
		} else {
			$params['Label_id'] = 1;
		}
		$filter1[] = 'PL.Label_id = :Label_id';
		$filter2[] = 'PS.Lpu_id = :Lpu_id';
		

		$select[]='pers_labels.PersLabels';
		$joinList[]="outer apply (
				SELECT PersLabels = STUFF(CAST((
					SELECT [text()] = '|' + L_ls.Label_Name
						FROM v_PersonLabel PL_ls with(nolock)
							inner join v_Label L_ls with(nolock) on L_ls.Label_id=PL_ls.Label_id
						WHERE PL_ls.Person_id = PS.Person_id AND PL_ls.Label_id not in (1,7) AND PL_ls.PersonLabel_disDate is null
						ORDER BY PL_ls.Label_id ASC
					FOR XML PATH(''), TYPE) AS VARCHAR(MAX)), 1, 1, '')
				) pers_labels";
		
		$params['Lpu_id'] = $data['MonitorLpu_id'];
		if(!empty($data['DispOutType_id'])) {
			$filter2[] = 'DOT.DispOutType_id = :DispOutType_id';
			$params['DispOutType_id'] = $data['DispOutType_id'];
		}
		if(!empty($data['outBegDate']) && !empty($data['outEndDate']) ) {
			$filter1[] = '((LOC.LabelObserveChart_endDate > :outBegDate AND LOC.LabelObserveChart_endDate < :outEndDate) OR LOC.LabelObserveChart_endDate is null)';
			
			$params['outBegDate'] = $data['outBegDate'];
			$params['outEndDate'] = $data['outEndDate'];
		}
		if( !empty($data['Diags']) and count($data['Diags'])>0) {
			$diags = array();
			$alldiags = false;
			foreach($data['Diags'] as $diag) {
				if(is_numeric($diag)) $diags[]=$diag;
				if($diag == -1) $alldiags = true;
			}
			if($alldiags)
				$filter1[] = 'PL.Diag_id in (select LD.Diag_id from LabelDiag LD (nolock) where LD.Label_id=1 )';
			else {
				$diags = implode(', ',$diags);
				if(count($diags)>0) {
					$filter1[] = 'PL.Diag_id in ('.$diags.')';
				}
			}
		}
		if(!empty($data['LabelInviteStatus_id'])) {
			$filter2[] = 'LI.LabelInviteStatus_id = :LabelInviteStatus_id';
			$params['LabelInviteStatus_id'] = $data['LabelInviteStatus_id'];
		}
		/*if(!empty($data['fio'])) {
			$filter2[] = "PS.Person_SurName LIKE '%'+:fio+'%'";
			$params['fio'] = $data['fio'];
		}*/
		
		if(!empty($data['Person_id'])) {
			$filter2[] = "PS.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		switch($data['status']) {
			case 'new':	//Новые
				//	У человека есть запись о наличии метки (запись открыта)
				//	У человека нет связанной с меткой карты наблюдений в МО Пользователя (как открытой, так и закрытой)
				$filter1[] = "LOC.LabelObserveChart_id is null and PL.PersonLabel_disDate is null";
				break;
			case 'on': //Включенные
				//	У человека есть открытая карта наблюдений в МО Пользователя
				$filter1[] = "
					LOC.LabelObserveChart_id is not null and LOC.LabelObserveChart_endDate is null
				";
				break;
			case 'off': //Выбывшие 
				//	У человека есть карта наблюдений в МО Пользователя с заполненной датой закрытия
				$filter1[] = "
					LOC.LabelObserveChart_id is not null and LOC.LabelObserveChart_endDate is not null
				";
				break;
			case 'all': //Все пациенты
				//	У человека есть запись о наличии метки (запись открыта) 
				//	У человека есть карта наблюдений в МО Пользователя (как открытая, так и закрытая)
				$filter1[] = "
					PL.PersonLabel_disDate is null and LOC.LabelObserveChart_id is not null
				";
				break;
		}
		$filter = array_merge($filter1, $filter2);
		
		{
		$query = "
			-- variables
			DECLARE
				@getdate datetime = dbo.tzGetDate();
			-- end variables
			SELECT 
				-- select
				PL.Person_id,
				PL.PersonLabel_id,
				PS.Server_id,
				PS.PersonEvn_id,
				PS.Sex_id,
				LI.LabelInvite_id,
				LI.LabelInviteStatus_id,
				LI.LabelInvite_RefuseCause,
				LI.FeedbackMethod_id as LabelInviteFeedbackMethod_id,
				convert(varchar(16), LI.LabelInvite_updDT,120) as LabelInviteStatus_Date,
				RTRIM(LTRIM(ISNULL(PS.Person_Phone, ''))) as Person_Phone,
				RTRIM(LTRIM(ISNULL(LOC.LabelObserveChart_Phone, ''))) as Chart_Phone,
				LOC.LabelObserveChart_Email as Chart_Email,
				RTRIM(LTRIM(ISNULL(UPPER(PS.Person_SurName),''))) as Person_SurName,
				RTRIM(LTRIM(ISNULL(UPPER(PS.Person_FirName),''))) as Person_FirName,
				RTRIM(LTRIM(ISNULL(UPPER(PS.Person_SecName),''))) as Person_SecName,
				
				DATEDIFF(day, PS.Person_BirthDay, ISNULL(PS.Person_deadDT, @getdate )) as PersonAge,
				convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay,
				convert(varchar(10), PS.Person_deadDT, 120) as Person_DeadDT,
				L.Label_id,
				PM.PersonModel_id,
				PM.PersonModel_Name,
				LOC.LabelObserveChart_id as Chart_id,
				convert(varchar(10), LOC.LabelObserveChart_begDate, 104) as Chart_begDate,
				convert(varchar(10), LOC.LabelObserveChart_endDate, 104) as Chart_endDate,
				LOC.LabelObserveChart_IsAutoClose as Chart_IsAutoClose,
				LOC.PersonDisp_id as ChartDisp_id,
				DOT.DispOutType_Name,
				DOT.DispOutType_Code,
				
				FORMAT(ci.LabelObserveChartInfo_ObserveDate,'yyyy-MM-dd') as lastObserveDate,
				CM1.LabelObserveChartMeasure_Value as Rate1_Value,
				CM2.LabelObserveChartMeasure_Value as Rate2_Value,
				CM4.LabelObserveChartMeasure_Value as Rate4_Value,
				
				CR1.LabelObserveChartRate_Min as Rate1_Min,
				CR1.LabelObserveChartRate_Max as Rate1_Max,
				CR2.LabelObserveChartRate_Min as Rate2_Min,
				CR2.LabelObserveChartRate_Max as Rate2_Max,
				CR4.LabelObserveChartRate_Min as Rate4_Min,
				CR4.LabelObserveChartRate_Max as Rate4_Max,
				
				D.Diag_Code,
				D.Diag_Name,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate,
				PDCh.PersonDisp_id as ChartPersonDisp_id,
				PD.PersonDisp_id,
				
				case 
					when LOC.LabelObserveChart_id is null then 'new'
					when LOC.LabelObserveChart_endDate is not null then 'off'
					when LOC.LabelObserveChart_id is not null then 'on'
					else 'undefined'
				END as StatusNick,
				pcard.Lpu_id,
				coalesce(pcard_lpu.Lpu_Nick, '') as Lpu_Nick,
				pcard.LpuRegion_Name as AttachNum,
				coalesce(pcard_lpu.Lpu_Nick, '') + RIGHT(space(10) + coalesce(pcard.LpuRegion_Name,''), 10) as Attach,
				pcard.PersonCard_begDate as AttachDate,
				PL.PersonLabel_disDate,
				FM.FeedbackMethod_id,
				FM.FeedbackMethod_Name,
				case 
					when LOC.LabelObserveChart_id is null 
					then 10+ISNULL(LI.LabelInviteStatus_id, 0)
					when LOC.LabelObserveChart_endDate is not null
					then 30
					when LOC.LabelObserveChart_id is not null
					then 20+coalesce(LOC.PersonModel_id, 0)
				end as Status,
				coalesce(PR.PersonRefuse_IsRefuse,1) - 1 as Person_IsRefuse,
				case 
					when PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0
				END AS Person_IsFedLgot,
				case
					when regl.Lpu_id is null then 0
					when regl.Lpu_id = :Lpu_id then 1
					else 0
				END as Person_IsRegLgot
				".(count($select) > 0 ? ','.implode(',', $select) : "")."
				-- end select
			FROM
				--from
				v_PersonLabel PL with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = PL.Person_id
				inner join v_Label L with (nolock) on L.Label_id = PL.Label_id
				outer apply (
					SELECT TOP 1 
						LI1.LabelInvite_id, 
						LI1.LabelInviteStatus_id, 
						LI1.LabelInvite_updDT, 
						LI1.LabelInvite_RefuseCause,
						LI1.FeedbackMethod_id
					FROM v_LabelInvite LI1 with (nolock)
					inner join MedStaffFactcache MSF with (nolock) on MSF.MedStaffFact_id=LI1.MedStaffFact_id
					WHERE LI1.PersonLabel_id = PL.PersonLabel_id 
						and LI1.LabelInviteStatus_id is not null
						and MSF.Lpu_id = :Lpu_id
					ORDER BY LI1.LabelInvite_id DESC
				) LI
				outer apply (
					SELECT TOP 1 
						LOC3.PersonModel_id,
						LOC3.DispOutType_id,
						LOC3.LabelObserveChart_id,
						LOC3.PersonDisp_id,
						LOC3.LabelObserveChart_begDate,
						LOC3.LabelObserveChart_endDate,
						LOC3.LabelObserveChart_IsAutoClose,
						LOC3.LabelObserveChart_Phone,
						LOC3.LabelObserveChart_Email,
						LOC3.FeedbackMethod_id
					FROM v_LabelObserveChart LOC3 with (nolock) 
						inner join MedStaffFactcache MSF with (nolock) on MSF.MedStaffFact_id = LOC3.MedStaffFact_id
					WHERE LOC3.PersonLabel_id = PL.PersonLabel_id
						AND MSF.Lpu_id = :Lpu_id
					ORDER BY ISNULL(LOC3.LabelObserveChart_endDate, @getdate) DESC, LabelObserveChart_id DESC
				) LOC
				left join v_PersonModel PM with (nolock) on PM.PersonModel_id = LOC.PersonModel_id
				left join v_DispOutType DOT with (nolock) on DOT.DispOutType_id=LOC.DispOutType_id
				left join v_Diag D (nolock) on D.Diag_id=PL.Diag_id
				left join FeedbackMethod FM (nolock) on FM.FeedbackMethod_id = LOC.FeedbackMethod_id
				left join v_PersonRefuse PR WITH (NOLOCK) ON PR.Person_id = PL.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(@getdate)

				outer apply (
				SELECT TOP 1 CI.LabelObserveChartInfo_id, CI.LabelObserveChartInfo_ObserveDate 
					FROM v_LabelObserveChartInfo CI with (nolock)
						inner join v_LabelObserveChartMeasure CM with (nolock) on CM.LabelObserveChartInfo_id=CI.LabelObserveChartInfo_id
					WHERE CI.LabelObserveChart_id = LOC.LabelObserveChart_id 
						and CI.LabelObserveChartInfo_ObserveDate >= DATEADD(DAY,-1, CAST(FLOOR(CAST(GETDATE() AS FLOAT)) AS DATETIME ))
					ORDER BY CI.LabelObserveChartInfo_ObserveDate DESC, CI.TimeOfDay_id DESC, CI.LabelObserveChartInfo_id DESC
				) CI

				left join v_LabelObserveChartRate CR1 with(nolock) on CR1.LabelObserveChart_id=LOC.LabelObserveChart_id and CR1.LabelRate_id=1
				left join v_LabelObserveChartMeasure CM1 with (nolock) on CM1.LabelObserveChartRate_id = CR1.LabelObserveChartRate_id and CM1.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				
				left join v_LabelObserveChartRate CR2 with(nolock) on CR2.LabelObserveChart_id=LOC.LabelObserveChart_id and CR2.LabelRate_id=2
				left join v_LabelObserveChartMeasure CM2 with (nolock) on CM2.LabelObserveChartRate_id = CR2.LabelObserveChartRate_id and CM2.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				
				left join v_LabelObserveChartRate CR4 with(nolock) on CR4.LabelObserveChart_id=LOC.LabelObserveChart_id and CR4.LabelRate_id=4
				left join v_LabelObserveChartMeasure CM4 with (nolock) on CM4.LabelObserveChartRate_id = CR4.LabelObserveChartRate_id and CM4.LabelObserveChartInfo_id = CI.LabelObserveChartInfo_id
				
				outer apply
				(SELECT TOP 1
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					PersonPrivilege PP WITH (NOLOCK)
					inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PT.ReceptFinance_id = 1
					AND
					PP.PersonPrivilege_begDate <= @getdate 
					AND
					coalesce(PP.PersonPrivilege_endDate,@getdate) >= cast(@getdate AS date)
					AND
					PP.Person_id = PL.Person_id
					AND
					PersonPrivilege_deleted=1
				) PersonPrivilegeFed
				outer apply (
					SELECT TOP 1
						Lpu_id
					from PersonPrivilege t1 with (nolock)
						inner join PrivilegeType t2 with (nolock) on t2.PrivilegeType_id = t1.PrivilegeType_id
					where
						t1.Person_id = PL.Person_id
						and t2.ReceptFinance_id = 2
						and t1.PersonPrivilege_begDate <= @getdate
						and IsNull(t1.PersonPrivilege_endDate, @getdate) >= cast(@getdate as date)
						and PersonPrivilege_deleted=1
					order by
						case when t1.Lpu_id = :Lpu_id then 1 else 0 end desc
				) as regl
				
				left join v_PersonCard pcard (nolock) on pcard.Person_id = PL.Person_id and LpuAttachType_id = 1
				left join v_Lpu pcard_lpu (nolock) on pcard_lpu.lpu_id=pcard.lpu_id
				left join v_PersonDisp PDCh (nolock) on PDCh.PersonDisp_id = LOC.PersonDisp_id AND PDCh.PersonDisp_endDate is null AND PDCh.Lpu_id=:Lpu_id
				
				outer apply (
					SELECT TOP 1 
						PD6.PersonDisp_id, PD6.PersonDisp_begDate, PD6.PersonDisp_endDate
					FROM v_PersonDisp PD6 (nolock)
					WHERE PD6.Person_id=PL.Person_id AND PD6.Diag_id=PL.Diag_id AND PD6.Lpu_id=:Lpu_id AND PD6.PersonDisp_endDate is null
					ORDER BY PD6.PersonDisp_id DESC
				) PD
				".(count($joinList) > 0 ? implode(' ', $joinList) : "")."
				--end from
			WHERE 
				--where
				".(count($filter) > 0 ? implode(' AND ', $filter) : "")."
				--end where
			ORDER BY
				-- order by
				PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
				-- end order by
			";
		}
	
    	//~ echo getDebugSQL($query, $params);exit;
    			
		if($data['paging']) {
			//постраничный вывод:
			$result = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true, true);
			return $result;
		} else {
			//обычный вывод:
			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) {
				return false;
			}
			return array('data'=>$result->result('array') );
		}
	}
	
	/**
	 * ДМ: Получение информации о пациенте и его показателях
	 */
	function getPersonChartInfo($data) {
		
		$params = array(
			'Person_id' => $data['Person_id']
		);
		
		$select = "
				null as email,
				null as LOC.FeedbackMethod_id,
				null as LOC.PersonModel_id,	
				null as Chart_begDate,
				null as Chart_endDate,
				null as DOT.DispOutType_id,
				null as DOT.DispOutType_Name,
				null as ChartPhone,
				null as ChartEmail,
				null as MailingConsDT,
		"; 
		
		$join = "";
		
		if (!empty($data['Chart_id'])) {
			$params['Chart_id']	= $data['Chart_id'];
			$join .= " 
				left join v_LabelObserveChart LOC with (nolock) on LOC.LabelObserveChart_id = :Chart_id 
				left join v_DispOutType DOT (nolock) on DOT.DispOutType_id = LOC.DispOutType_id
			";
			
			$select = "
				LOC.LabelObserveChart_Email as email,
				LOC.FeedbackMethod_id,
				LOC.PersonModel_id,	
				convert(varchar(10), LOC.LabelObserveChart_begDate, 104) as Chart_begDate,
				convert(varchar(10), LOC.LabelObserveChart_endDate, 104) as Chart_endDate,
				DOT.DispOutType_id,
				DOT.DispOutType_Name,
				LOC.LabelObserveChart_Phone as ChartPhone,
				LOC.LabelObserveChart_Email as ChartEmail,
				LOC.HypertensionRiskGroup_id,
				convert(varchar(10), LOC.LabelObserveChart_consDT, 104) as MailingConsDT,
			";
		}
		
		$chartinfo = $this->getFirstRowFromQuery("
			SELECT TOP 1
				PS.Person_id,
				PS.Server_id,
				PS.Person_Phone as PersonPhone,
				cast(PH.PersonHeight_Height as float) as PersonHeight,
				case when PW.Okei_id = 36 then
					cast(PW.PersonWeight_Weight as float) / 1000
				else
					PW.PersonWeight_Weight
				end as PersonWeight,
				pcard.Lpu_id,
				pcard.Lpu_Nick,
				pcard.LpuRegion_Name as AttachNum,
				{$select}
				convert(varchar(10), pcard.AttachDate, 104) as AttachDate
			FROM
				v_PersonState PS with (nolock)
				{$join}
				outer apply (
					select top 1 PH.PersonHeight_Height
					from v_PersonHeight PH with (nolock)
					where PH.Person_id = PS.Person_id
					order by PH.PersonHeight_setDT DESC
				) PH
				outer apply (
					select top 1 
						PW.PersonWeight_Weight,
						PW.Okei_id
					from v_PersonWeight PW with (nolock)
					where PW.Person_id = PS.Person_id
					order by PW.PersonWeight_setDT DESC
				) PW
				outer apply (
					SELECT TOP 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						lpu.Lpu_Nick,
						pc.LpuRegion_id,
						pc.LpuRegion_Name,
						pc.PersonCard_begDate as AttachDate,
						case when pc.LpuAttachType_id = 1 then pc.PersonCard_Code else null end as PersonCard_Code
					FROM v_PersonCard pc with (nolock)
					LEFT JOIN v_Lpu lpu with (nolock) on pc.Lpu_id=lpu.Lpu_id
					WHERE pc.Person_id = PS.Person_id and LpuAttachType_id = 1
					ORDER BY PersonCard_begDate desc
				) as pcard
			WHERE
				PS.Person_id = :Person_id
		", $params);
		
		$rates = $this->queryResult("
			SELECT 
				CR.LabelObserveChartRate_id as ChartRate_id,
				CR.LabelObserveChartRate_Min as ChartRate_Min, 
				CR.LabelObserveChartRate_Max as ChartRate_Max, 
				CR.LabelObserveChartRate_IsShowValue,
				CR.LabelObserveChartRate_IsShowEMK,
				CR.LabelObserveChartSource_id,
				locs.LabelObserveChartSource_Name,
				CR.LabelRate_id,
				RT.RateType_id,
				RT.RateValueType_id,
				RT.RateType_SysNick
			FROM v_LabelObserveChartRate CR (nolock)
			left join v_LabelRate LR (nolock) on LR.LabelRate_id = CR.LabelRate_id
			left join v_RateType RT (nolock) on RT.RateType_id = coalesce(LR.RateType_id, CR.RateType_id)
			left join v_LabelObserveChartSource locs (nolock) on locs.LabelObserveChartSource_id = CR.LabelObserveChartSource_id
			WHERE Person_id = :Person_id
		", $params);
		
		//данные с портала
		$portal_db = $this->load->database('UserPortal', true);

		//если найдена запись - значит человек имеет учетку на портале и в моб.приложении
		//если FCM_Token is not null - точно пользовался моб.приложением (если NULL - неизвестно)
		// todo: это не верно, пациент может быть в нескольких картотеках разных аккаунтов
		$portal_resp = $portal_db->query("
			select top 1
				users.email,
				users.FCM_Token
			from Person (nolock)
			left join users (nolock) on users.id = Person.pmUser_id
			where Person.Person_mainId = :Person_id
			order by users.FCM_Token DESC
		", array('Person_id' => $chartinfo['Person_id'])
		);
		
		$portal_result = array();
		if (is_object($portal_resp)) {
			$portal_result = $portal_resp->result('array');
			if(!empty($portal_result[0])) {
				$portal_result = $portal_result[0];	
			}
		}
		
		//Настройка верхней и нижней границы САД
		//#198097
		
		if (getRegionNick() == 'ufa') {
			$index=-1;
			foreach($rates as $key=>$item) {
    			if ($item['RateType_SysNick'] == 'systolic_blood_pressure') {
        			$index = $key;
        			break;
    			}
			}
			if ($index > -1) {
				switch($chartinfo['HypertensionRiskGroup_id'])
				{
					case 3:
						$rates[$index]['ChartRate_Max'] = 130;
						$rates[$index]['ChartRate_Min'] = 110;
						break;
					case 2:
					case 1:
						$rates[$index]['ChartRate_Max'] = 120;
						$rates[$index]['ChartRate_Min'] = 100;
						break;
				}
			}
		}
		return array('info' => $chartinfo, 'rates' => $rates, 'portal' => $portal_result);
	}
	
	/**
	 * ДМ: Получить данные пациента с портала и мобильного приложения
	 * Используется: приглашение в Дистанционный мониторинг
	 */
	function getPersonDataFromPortal($data) {
		$Person_ids = array();
		foreach($data['Person_ids'] as $Person_id) {
			$Person_ids[] = intval($Person_id);
		}
		//данные с портала
		$portal_db = $this->load->database('UserPortal', true);
		$portal = array();

		foreach($Person_ids as $Person_id) {
			$sql = "
			select
				Person.Person_mainId as Person_id
				,users.FCM_Token as isApp
				,users.email
				,users.last_login
				,Person.Person_Phone as phone
			from Person (nolock)
				left join users (nolock) on users.id = Person.pmUser_Id
			where Person.Person_mainId = :Person_id
			order by Person.Person_updDT DESC
			";
			//~ echo getDebugSQL($sql, array('Person_id'=>$Person_id) );
			$res = $portal_db->query( $sql, array('Person_id'=>$Person_id) );
			if ( is_object($res) ) {
				$row = $res->result('array');
				if(count($row)>0)
					$portal[] = $row[0];
			}
		}
		return $portal;
	}
	
	/**
	 * ДМ: Сохранить способ обратной связи в Карте наблюдения
	 * Используется: Дистанционный мониторинг
	 */
	function savePersonChartFeedback($data) {
		$params = array('Chart_id' => $data['Chart_id'], 'FeedbackMethod_id' => $data['FeedbackMethod_id'] );
		$sql = "
			update LabelObserveChart
			set FeedbackMethod_id = :FeedbackMethod_id
			where LabelObserveChart_id = :Chart_id ";
			
		//~ echo getDebugSQL($sql, $params);exit;
		$res = $this->db->query($sql, $params);
		
		$sql = "
			select FeedbackMethod_Name
			from FeedbackMethod
			where FeedbackMethod_id = :FeedbackMethod_id
		";
		$feedback_name = $this->getFirstResultFromQuery($sql, $params);
		return array(array('success' => true, 'FeedbackMethod_Name'=>$feedback_name, 'FeedbackMethod_id'=>$data['FeedbackMethod_id'], 'Error_Msg' => ''));
	}
	
	/**
	 * ДМ: Сохранить целевые показатели для карты наблюдения
	 */
	function saveLabelObserveChartRate($data) {
		$sql = "
			update LabelObserveChartRate
			set LabelObserveChartRate_Min = :LabelRateMin,
				LabelObserveChartRate_Max = :LabelRateMax
			where LabelObserveChart_id = :Chart_id and LabelRate_id = :LabelRate_id";
		
		$res = $this->db->query($sql, $data);
		
		return array(array('success' => true, 'Error_Msg' => ''));
	}
		
	/**
	 * ДМ: Получить измерения для карты наблюдения
	 * Используется: таблица замеров карты наблюдения
	 */
	function loadLabelObserveChartMeasure($data) {

		$params = array(
			'Person_id' => $data['Person_id']
		);
		
		$minimax = array(
			'minObserveDate' => 0,
			'maxObserveDate' => 0
		);
		
		if (!empty($data['Chart_id'])) {
			$params['Chart_id']	= $data['Chart_id'];

			//количество замеров для индикатора вкладки "показания"
			$totalcount = $this->getFirstResultFromQuery("
				SELECT count(LabelObserveChartInfo_id) FROM
				v_LabelObserveChartInfo CI with (nolock)
				WHERE LabelObserveChart_id = :Chart_id
			", $params);

			//границы временного промежутка всех измерений в карте наблюдения
			$minimax = $this->getFirstRowFromQuery("
			SELECT
				convert(varchar(10), min(CI.LabelObserveChartInfo_ObserveDate), 120) as minObserveDate,
				convert(varchar(10), max(CI.LabelObserveChartInfo_ObserveDate), 120) as maxObserveDate
				FROM
					v_LabelObserveChartInfo CI with (nolock)
				WHERE LabelObserveChart_id = :Chart_id
			", $params);
		} else {

			//количество замеров для индикатора вкладки "показания"
			$totalcount = $this->getFirstResultFromQuery("
				select count(LOCI.LabelObserveChartInfo_id) 
				from v_LabelObserveChartInfo LOCI (nolock) 
				left join v_LabelObserveChartMeasure locm (nolock) on locm.LabelObserveChartInfo_id = LOCI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate locr (nolock) on locr.LabelObserveChartRate_id = locm.LabelObserveChartRate_id
				where locr.Person_id = :Person_id
			", $params);
		}

		//период = 1 неделя
		$period = 'day';
		
		$i = $data['start'];
		$k = 7;

		//период = 2 недели
		if($data['limit']==2) { 
			$k = 14;
		} else if($data['limit']==3) {
			//период = месяц
			$k = 30;
		}
		
		$rates = $this->queryResult("
			SELECT 
				CR.LabelObserveChartRate_id as ChartRate_id,
				CR.LabelObserveChartRate_Min as ChartRate_Min, 
				CR.LabelObserveChartRate_Max as ChartRate_Max,
				CR.LabelObserveChartSource_id,
				CR.LabelObserveChartRate_IsShowValue,
				CR.LabelObserveChartRate_IsShowEMK,
				CR.LabelRate_id,
				RT.RateType_id,
				RT.RateType_SysNick
			FROM v_LabelObserveChartRate CR with (nolock)
			left join v_LabelRate LR with(nolock) on LR.LabelRate_id = CR.LabelRate_id
			left join v_RateType RT with (nolock) on RT.RateType_id = coalesce(LR.RateType_id,CR.RateType_id)
			WHERE Person_id = :Person_id
		", $params);

		$filter = " AND DATEDIFF({$period}, CI.LabelObserveChartInfo_ObserveDate, @maxdate ) >= ".($i*$k)." 
			AND DATEDIFF({$period}, CI.LabelObserveChartInfo_ObserveDate, @maxdate ) < ".(($i+1)*$k);

		
		$chartInfo = $this->queryResult("
			declare @maxdate date
			set @maxdate = (
				select max(LOCI.LabelObserveChartInfo_ObserveDate) 
				from v_LabelObserveChartInfo LOCI (nolock)
				left join v_LabelObserveChartMeasure locm (nolock) on locm.LabelObserveChartInfo_id = LOCI.LabelObserveChartInfo_id
				left join v_LabelObserveChartRate locr (nolock) on locr.LabelObserveChartRate_id = locm.LabelObserveChartRate_id
				where locr.Person_id = :Person_id 
			);

			SELECT DISTINCT
				CI.LabelObserveChartInfo_id as ChartInfo_id,
				convert(varchar(16), CI.LabelObserveChartInfo_ObserveDate, 120) as ObserveDate,
				CI.LabelObserveChartInfo_ObserveDate,
				CI.TimeOfDay_id,
				CI.LabelObserveChartSource_id,
				locs.LabelObserveChartSource_Name,
				CI.LabelObserveChartInfo_Complaint as Complaint,
				CI.FeedbackMethod_id
			FROM v_LabelObserveChartRate locr (nolock)
			inner join v_LabelObserveChartMeasure locm (nolock) on locm.LabelObserveChartRate_id = locr.LabelObserveChartRate_id
			inner join v_LabelObserveChartInfo CI with (nolock) on CI.LabelObserveChartInfo_id = locm.LabelObserveChartInfo_id
			left join v_LabelObserveChartSource locs (nolock) on locs.LabelObserveChartSource_id = CI.LabelObserveChartSource_id
			WHERE (1=1)
				and locr.Person_id = :Person_id
				--and coalesce(CI.LabelObserveChartSource_id, 1) = 1
				--and (CI.FeedbackMethod_id is null or CI.FeedbackMethod_id < 4) 
				--and coalesce(locr.LabelObserveChartRate_IsShowEMK, 2) = 2
				{$filter}
			ORDER BY
				CI.LabelObserveChartInfo_ObserveDate DESC,
				CI.TimeOfDay_id ASC
		", $params);

		$measures = array();
		if (!empty($chartInfo)) {
			
			$ChartInfo_list = implode(',',array_column($chartInfo,'ChartInfo_id'));
			$measures = $this->queryResult("
				select 
					CM.LabelObserveChartMeasure_id as Measure_id,
					CM.LabelObserveChartMeasure_Value as Value,
					CM.LabelObserveChartInfo_id as ChartInfo_id,
					CR.RateType_id
				from LabelObserveChartMeasure CM
				inner join LabelObserveChartRate CR on CR.LabelObserveChartRate_id = CM.LabelObserveChartRate_id
				where (1=1)
					and CM.LabelObserveChartInfo_id in ({$ChartInfo_list})
			", $params);
		}
		
		return array(
			'info'=>$chartInfo, //замеры
			'measures'=>$measures, //отдельно данные по показателям к ним
			'rates'=>$rates, //нормы
			'totalCount'=>$totalcount, //количество замеров
			'minimax'=>$minimax //временной промежуток на все замеры
		);
	}
	
	/**
	 * ДМ: Сохранение/добавление измерения по целевому показателю
	 * Используется: saveLabelObserveChartMeasure
	 */
	function saveLabelObserveChartRateMeasure($data) {
		
		$params = array(
				'LabelObserveChartInfo_id' => $data['LabelObserveChartInfo_id'],
				'LabelObserveChartRate_id' => $data['ChartRate_id'],
				'LabelObserveChartMeasure_Value' => $data['Measure_value'],
				'pmUser_id' => $data['pmUser_id'],
				'LabelObserveChartMeasure_id' => null
			);

		$action = 'ins';

		if (!empty($measure['Measure_id'])) {
			$params['LabelObserveChartMeasure_id'] = $data['Measure_id'];
			$action = 'upd';
		}
		
		$query = "           
			DECLARE	@Error_Code int,
					@Error_Message varchar(4000),
					@LabelObserveChartMeasure_id bigint = :LabelObserveChartMeasure_id

			EXEC	p_LabelObserveChartMeasure_{$action}
					@LabelObserveChartMeasure_id = @LabelObserveChartMeasure_id output,
					@LabelObserveChartInfo_id = :LabelObserveChartInfo_id,
					@LabelObserveChartRate_id = :LabelObserveChartRate_id,
					@LabelObserveChartMeasure_Value = :LabelObserveChartMeasure_Value,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@LabelObserveChartMeasure_id as LabelObserveChartMeasure_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Message
		";
		//~ echo getDebugSQL($query, $params);return;
		return $this->getFirstRowFromQuery($query, $params);
	}
	
	/**
	 * ДМ: Сохранить/добавить измерения в карте наблюдения
	 * (строка в LabelObserveChartInfo и массив замеров к ней)
	 */
	function saveLabelObserveChartMeasure($data) {
		
		$params = array(
			'LabelObserveChart_id' => $data['Chart_id'],
			'LabelObserveChartInfo_ObserveDate' => $data['ObserveDate'],
			'TimeOfDay_id' => $data['ObserveTime_id'],
			'FeedbackMethod_id' => $data['FeedbackMethod_id'],
			'LabelObserveChartInfo_Complaint' => $data['Complaint'],
			'LabelObserveChartSource_id' => !empty($data['LabelObserveChartSource_id']) ? $data['LabelObserveChartSource_id'] : 1,
			'pmUser_id' => $data['pmUser_id'],
			'LabelObserveChartInfo_id' => null
		);

		$action = 'ins';

		if (!empty($data['ChartInfo_id'])) {
			$params['LabelObserveChartInfo_id'] = $data['ChartInfo_id'];
			$action = 'upd';

			// проверяем замер
			$chartSource = $this->getFirstRowFromQuery("
				select top 1
					LabelObserveChartInfo_id,
					LabelObserveChartSource_id
				from v_LabelObserveChartInfo (nolock)
				where LabelObserveChartInfo_id = :LabelObserveChartInfo_id
			", array(
				'LabelObserveChartInfo_id' => $data['ChartInfo_id']
			));

			// сохраняем тот же первоначальный источник, если есть
			$params['LabelObserveChartSource_id'] = !empty($chartSource['LabelObserveChartSource_id']) ? $chartSource['LabelObserveChartSource_id'] : 1;
		}
		
		$query = "
			DECLARE	@Error_Code int,
					@Error_Message varchar(4000),
					@LabelObserveChartInfo_id bigint = :LabelObserveChartInfo_id
			
			EXEC	p_LabelObserveChartInfo_{$action}
					@LabelObserveChartInfo_id = @LabelObserveChartInfo_id output,
					@LabelObserveChart_id = :LabelObserveChart_id,
					@LabelObserveChartInfo_ObserveDate = :LabelObserveChartInfo_ObserveDate,
					@TimeOfDay_id = :TimeOfDay_id,
					@LabelObserveChartInfo_Complaint = :LabelObserveChartInfo_Complaint,
					@FeedbackMethod_id = :FeedbackMethod_id,
					@LabelObserveChartSource_id = :LabelObserveChartSource_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT
					
			SELECT	@LabelObserveChartInfo_id as LabelObserveChartInfo_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg
		";

		$this->beginTransaction();
		$result = $this->getFirstRowFromQuery($query, $params);
		
		if (empty($result['LabelObserveChartInfo_id']) || !empty($result['Error_Msg'])) {
			
			$this->rollbackTransaction();
			$err_msg = (!empty($result['Error_Msg'])) ? ': '.$result['Error_Msg'] : '';

			return array(
				'Error_Msg' => 'Ошибка сохранения общей информации по замеру'.$err_msg
			);
		}
		
		//Сохранение замеров
		$result['measure'] = array();
		if (!empty($data['RateMeasures'])) {
			foreach($data['RateMeasures'] as $RateMeasure) {

				$RateMeasure->LabelObserveChartInfo_id = $result['LabelObserveChartInfo_id'];
				$RateMeasure->pmUser_id = $data['pmUser_id'];
				
				$measure = $this->saveLabelObserveChartRateMeasure((array)$RateMeasure);

				if (!empty($measure['Error_Msg'])) {

					$this->rollbackTransaction();
					$err_msg = (!empty($measure['Error_Msg'])) ? ': '.$measure['Error_Msg'] : '';

					return array(
						'Error_Msg' => 'Ошибка сохранения измерения'.$err_msg
					);
				}
				
				$result['measure'][] = array($measure, $RateMeasure);
			}
		}
		
		$this->commitTransaction();
		return $result;
	}
	
	/**
	 * ДМ: Удалить измерение в карте наблюдения
	 */
	function deleteLabelObserveChartMeasure($data) {
		$params = array(
			'ChartInfo_id' => $data['ChartInfo_id']
		);
		
		$query = "
			DELETE FROM LabelObserveChartMeasure 
			WHERE LabelObserveChartInfo_id = :ChartInfo_id
		";
		$res=$this->db->query($query, $params);
		
		$query = "
			DECLARE	@return_value int,
					@Error_Code int,
					@Error_Message varchar(4000)

			EXEC	p_LabelObserveChartInfo_del
					@LabelObserveChartInfo_id = :ChartInfo_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@Error_Code as N'@Error_Code',
					@Error_Message as N'@Error_Message'
		";
		$res=$this->db->query($query, $params);
		if ( !is_object($res) )
			return false;
		else return $res->result('array'); 
	}
	
	/**
	 * ДМ: Проверка количества открытых карт наблюдения по метке
	 */
	function checkOpenedLabelObserveChart($data) {
		$params = array(
			'PersonLabel_id' => $data['PersonLabel_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Label_id' => $data['Label_id']
		);
		$query = "
			SELECT count(*)
			FROM v_PersonLabel PL2 with (nolock) 
				inner join v_PersonLabel PL with (nolock) on PL2.Person_id = PL.Person_id AND PL.Label_id=:Label_id
				inner join v_LabelObserveChart LOC with (nolock) on LOC.PersonLabel_id=PL.PersonLabel_id
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = LOC.MedStaffFact_id
				inner join v_MedStaffFact MSF2 with (nolock) on MSF.Lpu_id=MSF2.Lpu_id 
					AND MSF2.MedStaffFact_id = :MedStaffFact_id
			WHERE PL2.PersonLabel_id = :PersonLabel_id 
				AND PL2.Label_id=:Label_id
				AND LOC.LabelObserveChart_endDate is null
		";
		return $this->getFirstResultFromQuery($query, $params);
	}
	
	/**
	 * ДМ: проверить наличие карт по пациенту
	 */
	function checkOpenedLabelObserveChartByPerson($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Label_id' => $data['Label_id']
		);
		$query = "
			SELECT count(*)
			FROM v_PersonLabel PL with (nolock)
				inner join v_LabelObserveChart LOC with (nolock) on LOC.PersonLabel_id=PL.PersonLabel_id
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = LOC.MedStaffFact_id
				inner join v_MedStaffFact MSF2 with (nolock) on MSF.Lpu_id=MSF2.Lpu_id 
					AND MSF2.MedStaffFact_id = :MedStaffFact_id
			WHERE LOC.LabelObserveChart_endDate is null 
				AND PL.Label_id = :Label_id
				AND PL.Person_id = :Person_id
		";
		//~ echo (getDebugSQL($query, $params));
		return $this->getFirstResultFromQuery($query, $params);
	}
	
	
	/**
	 * ДМ: Создать карту наблюдения
	 */
	function createLabelObserveChart($data) {
		
		$params = array(
			'PersonDisp_id' => $data['PersonDisp_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'PersonLabel_id' => $data['PersonLabel_id'],
			'pmUser_id' => $data['pmUser_id'],
			'dateConsent' => $data['dateConsent'],
			'allowMailing' => $data['allowMailing'],
			'Person_Phone' => $data['Person_Phone']
		);
		
		$params['HypertensionRiskGroup_id'] = "";
		//#198097 заполнение группы риска
		if (getRegionNick() == 'ufa') {
			$params['HypertensionRiskGroup_id'] = $this->getHypertensinGroupRisk($data);
		}
		if(!empty($data['allowMailing']) && $data['allowMailing']) {
			//дата согласия на оповещения == дата согласия на мониторинг при создании
			$params['MailingConsDT'] = $data['dateConsent']; 
		} else {
			$params['MailingConsDT'] = null;
		}

		$this->beginTransaction();

		$query = "
			DECLARE	@LabelObserveChart_id bigint,
					@Error_Code int,
					@Error_Message varchar(4000);

			EXEC	p_LabelObserveChart_ins
					@LabelObserveChart_id = @LabelObserveChart_id OUTPUT,
					@PersonDisp_id = :PersonDisp_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@PersonLabel_id = :PersonLabel_id,
					@LabelObserveChart_begDate = :dateConsent,
					@LabelObserveChart_Phone = :Person_Phone,
					@LabelObserveChart_consDT = :MailingConsDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT,
					@HypertensionRiskGroup_id = :HypertensionRiskGroup_id

			SELECT	@LabelObserveChart_id as LabelObserveChart_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg
		";
		
		$saveResult = $this->getFirstRowFromQuery($query, $params);
		if (empty($saveResult['LabelObserveChart_id']) || !empty($saveResult['Error_Msg'])) {

			$this->rollbackTransaction();
			$err = "";
			
			if (!empty($result['Error_Msg'])) {
				$err = ": ".$result['Error_Msg'];
			}
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения'.$err);
		}
		
		$updateStatusResult = $this->updateLabelInviteStatus($params);
		if (!empty($updateStatusResult['Error_Msg'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения: Не удалось сбросить статусы приглашений по метке'.$err);
		}
		
		$params['LabelObserveChart_id'] = $saveResult['LabelObserveChart_id'];
		$saveRateResult = $this->addLabelObserveChartRate($params);
		if (!empty($saveRateResult['Error_Msg'])) {

			$this->rollbackTransaction();
			$err = "";

			if (!empty($result['Error_Msg'])) {
				$err = ": ".$result['Error_Msg'];
			}
			return array('Error_Msg' => 'Не удалось сохранить карту наблюдения'.$err);
		}

		$this->commitTransaction();		
		return $saveResult;
	}

	/**
	 * ДМ: Получение группы риска для АГ
	 * Реализация в задаче #198097
	 * Высокий - 3, Средний - 2, Низкий 1
	 */
	function getHypertensinGroupRisk($data) {
		$highLevel = ['I11', 'I12', 'I13', 'I15', 'I20.0', 'I21', 'I22', 'I20.8', 'I48', 'I60',
		 'I61', 'I62', 'I63', 'I64', 'G45', 'I65', 'I66', ' I 70.0', 'I 70.1', ' I 70.2', 
		 'I70.8', 'I70.9', 'N18.3', 'N18.4', 'N18.5', 'E10', 'E11', 'E12', 'E13', 'E14', 
		 'Е78.0', 'H47.1', 'H34', 'H35.0', 'H35.2', 'H36.0'];
		
		$middleLevel = ['I10', 'I14'];

		$highLevelUsluga = ['A16.12.004.009', 'A16.12.004.010', 'A16.12.004.012', 'A16.12.004.013', 'A16.12.026', 'A16.12.026.011',
		'A16.12.026.012', 'A16.12.028.003', 'A16.12.028.017', 'A16.12.004.001', 
		'A16.12.004.002', 'A16.12.004.003', 'A16.12.004.004', 'A16.12.004.005', 
		'A16.12.004.006', 'A16.12.004.007', 'A16.12.004.011'];
		
		$query = "
		select 
			vd.Diag_Code 
		from 
			v_PersonDiag vpd2 with (nolock) 
			left outer join v_PersonDisp vpd with (nolock) on vpd2.Person_id = vpd.Person_id
			inner join v_Diag vd with (nolock) on vd.Diag_id=vpd2.Diag_id
		where vpd.PersonDisp_id = :PersonDisp_id";
		$resultDiag = $this->queryResult($query, $data);
		$resultDiag = array_column($resultDiag,'Diag_Code');

		$query = "
			select 
				count(1) as Count
			from 
				v_EvnUsluga veu with (nolock)
				inner join v_UslugaComplex vuc with (nolock) on vuc.UslugaComplex_id=veu.UslugaComplex_id
				left outer join v_PersonDisp vpd with (nolock) on vpd.Person_id = veu.Person_id
			where 
				vpd.PersonDisp_id = :PersonDisp_id
				and vuc.UslugaComplex_Code in (".implode(',',$highLevelUsluga).")";

		$resultUsluga = $this->getFirstRowFromQuery($query, $data);
		if (!empty(array_intersect($resultDiag, $highLevel)) || !empty($resultUsluga)) {
			return 3;
		}else if (!empty(array_intersect($resultDiag, $middleLevel))) {
			return 2;
		}
		return 1;
	}

	/**
	 * сброс статусов всех приглашений по метке пациента
	 */
	function updateLabelInviteStatus($data) {
		
		$result = $this->getFirstRowFromQuery("
				declare
					@Err_Code int,
					@Err_Msg varchar(4000);

				set nocount on;

				begin try
					update LabelInvite with (rowlock)
					set LabelInviteStatus_id = null
					where PersonLabel_id = :PersonLabel_id
				end try

				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				set nocount off;

				select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
			", array(
			'PersonLabel_id' => $data['PersonLabel_id']
		));
		
		return $result;
	}
	
	/*
	 * получаем все целевые показатели пациента
	 */
	function getPersonLabelObserveChartRates($data) {
		
		$result = $this->queryResult("
			select
				locr.LabelObserveChartRate_id,
				locr.LabelObserveChartRate_IsShowValue,
				locr.LabelObserveChartSource_id,
				rt.RateType_SysNick,
				rt.RateType_Name,
				rt.RateType_id
			from v_LabelObserveChartRate locr (nolock)
			left join v_ratetype rt (nolock) on rt.RateType_id = locr.RateType_id
			where (1=1)
				and locr.Person_id = :Person_id
		", array('Person_id' => $data['Person_id']));
		
		return $result;
	}
	
	/**
	 * ДМ: Создать целевые показатели для карты наблюдения
	 * Используется: function createLabelObserveChart
	 */
	function addLabelObserveChartRate($data) {
		
		$labelRates = $this->queryResult("
			select 
				lr.LabelRate_id,
				lr.LabelRate_Min,
				lr.LabelRate_Max,
				rt.RateType_SysNick,
				rt.RateType_Name,
				rt.RateType_id
			from v_PersonLabel pl (nolock)
			left join v_LabelRate lr (nolock) on lr.Label_id = pl.Label_id
			left join v_RateType rt (nolock) on rt.RateType_id = lr.RateType_id
			where PersonLabel_id = :PersonLabel_id
		", array(
			'PersonLabel_id' => $data['PersonLabel_id']
		));

		$labelRatesFiltered = array();
		
		// здесь придется только по одному брать первому попавшемуся,
		// чтобы избежать коллизий с позателями созданными из портала
		foreach ($labelRates as $lr) {
			if (!isset($labelRatesFiltered[$lr['RateType_id']])) {
				$labelRatesFiltered[$lr['RateType_id']] = $lr;
			} else {
				continue;
			}
		}

		$labelRates = array_values($labelRatesFiltered);
		
		$Person_id = $this->getFirstResultFromQuery("
			select top 1 Person_id
			from v_PersonDisp (nolock)
			where PersonDisp_id = :PersonDisp_id
		", array('PersonDisp_id' => $data['PersonDisp_id']));
		
		if (empty($Person_id)) {
			return array('Error_Msg' => 'Не удалось определить пациента');
		}
		
		// проверяем возможно показатели уже связаны с пользователем
		$personRates = $this->getPersonLabelObserveChartRates(array(
			'Person_id' => $Person_id
		));
		
		
		// если показатель уже связан, то мы его не добавляем
		if (!empty($labelRates) && !empty($personRates)) {
			foreach ($labelRates as $key => $lrate) {
				foreach ($personRates as $pkey => $prate) {
					if ($lrate['RateType_SysNick'] === $prate['RateType_SysNick']) {
						unset($labelRates[$key]);
						unset($personRates[$pkey]);
						break;
					}
				}
			}
		}
		
		// создаем только те показатели которые остались в $labelRates
		$resp = array();
		if (!empty($labelRates)) {
			
			foreach($labelRates as $rate) {
				$params = array(
					'LabelRate_id' => $rate['LabelRate_id'],
					'RateType_id' => $rate['RateType_id'],
					'LabelRate_Min' => $rate['LabelRate_Min'],
					'LabelRate_Max' => $rate['LabelRate_Max'],
					'pmUser_id' => $data['pmUser_id'],
					'Person_id' => $Person_id
				);
				
				$sql = "
					DECLARE	
							@LabelObserveChartRate_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_LabelObserveChartRate_ins
							@LabelObserveChartRate_id = @LabelObserveChartRate_id OUTPUT,
							@LabelRate_id = :LabelRate_id,
							@RateType_id = :RateType_id,
							@Person_id = :Person_id,
							@LabelObserveChartRate_Min = :LabelRate_Min,
							@LabelObserveChartRate_Max = :LabelRate_Max,
							@LabelObserveChartRate_IsShowEMK = 1,
							@LabelObserveChartRate_IsShowValue = 2,
							@LabelObserveChartSource_id = 1,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@LabelObserveChartRate_id as LabelObserveChartRate_id,
							@Error_Code as Error_Code,
							@Error_Message as Error_Msg
				";
				
				$result = $this->getFirstRowFromQuery($sql, $params);
				if (empty($result['LabelObserveChartRate_id']) || !empty($result['Error_Msg'])) {
					$err = "";
					if (!empty($result['Error_Msg'])) {
						$err = ": ".$result['Error_Msg'];
					}
					return array('Error_Msg' => 'Не удалось сохранить целевой показатель'.$err);
					break;
				}

				$resp['LabelObserveChartRate_id'][] = $result['LabelObserveChartRate_id'];
			}
		}
		return $resp;
	}
	
	/**
	 * ДМ: Сохранить поля в карте наблюдения
	 */
	function savePersonChartInfo($data) {
		$params = array(
			'Chart_id' => $data['Chart_id'],
			'Chart_begDate' => $data['Chart_begDate'],
			'PersonModel_id' => $data['PersonModel_id'],
			'email' => $data['email'],
			'sms' => $data['sms'],
			'voice' => $data['voice']
		);
		$set = "";
		if(!empty($data['Chart_begDate'])) {
			$set = "LabelObserveChart_begDate = :Chart_begDate";
		}
		if(!empty($data['PersonModel_id'])) {
			$set = "PersonModel_id = :PersonModel_id";
		}
		if(!empty($data['email'])) {
			$set = "LabelObserveChart_Email = :email";
		}
		if(!empty($data['sms'])) {
			$set = "LabelObserveChart_Phone = :sms";
		}
		if(!empty($data['voice'])) {
			$set = "LabelObserveChart_Phone = :voice";
		}
		if(!empty($set)) {
			$sql = "
				UPDATE LabelObserveChart 
				SET {$set}
				WHERE LabelObserveChart_id = :Chart_id
			";
			//~ echo getDebugSQL($sql, $params);return false;
			$res=$this->db->query($sql, $params);

			if ( !is_object($res) ) return false;
			return $res->result('array');
		} else return true;
	}
	
	/**
	 * ДМ: Количество замеров после даты
	 * Используется: перед исключением из программы мониторинга
	 */
	function getMeasuresNumberAfterDate($data) {
		$params = array(
			'Chart_id' => $data['Chart_id'],
			'endDate' => $data['endDate']
		);
		$sql = "
			SELECT count(CI.LabelObserveChart_id)
			FROM LabelObserveChartInfo CI
			WHERE CI.LabelObserveChart_id=:Chart_id AND CI.LabelObserveChartInfo_ObserveDate > :endDate
		";
		return $this->getFirstResultFromQuery($sql, $params);
	}
	
	/**
	 * ДМ: Исключить из программы Дистанционный мониторинг
	 */
	function removePersonFromMonitoring($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Chart_id' => $data['Chart_id'],
			'DispOutType_id' => $data['DispOutType_id'],
			'endDate' => $data['endDate']
		);
		
		$sql = "
			UPDATE LabelObserveChart SET LabelObserveChart_endDate = :endDate, DispOutType_id = :DispOutType_id
			WHERE LabelObserveChart_id = :Chart_id
		";
		$res=$this->db->query($sql, $params);
		if ($res===true) {
			if($data['Label_id']=='7') {
				$sql = "
					UPDATE PersonLabel SET PersonLabel_disDate = :endDate
					WHERE Person_id = :Person_id AND Label_id=7 AND PersonLabel_disDate is null
				";
				$res=$this->db->query($sql, $params);
				if ($res===true) return true;
				else return false;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Получить контакты кабинета здоровья в подразделении
	 */
	function getLpuBuildingHealth($data) {
		$params = array( 'LpuSection_id' => $data['LpuSection_id'] );
		$query = "
			SELECT 
				RTRIM(LTRIM(ISNULL(LBH.LpuBuildingHealth_Phone, ''))) as phone,
				RTRIM(LTRIM(ISNULL(LBH.LpuBuildingHealth_Email, ''))) as email,
				L.Lpu_Nick
			FROM v_LpuSection LS 
			LEFT JOIN v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
			LEFT JOIN v_Lpu L on L.Lpu_id = LU.Lpu_id
			LEFT JOIN v_LpuBuilding LB on LU.LpuBuilding_id = LB.LpuBuilding_id
			LEFT JOIN v_LpuBuildingHealth LBH on LBH.LpuBuilding_id = LB.LpuBuilding_id
			WHERE LS.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}
		return $result->first_row();
	}
	
	/**
	 * Получить правильно отформатированный номер телефона
	 */
	function getPhoneNumber($number) {
		$regexp = "/^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/";
		return (preg_match($regexp, $number, $match)) ? "{$match[2]}{$match[3]}{$match[4]}{$match[5]}" : "";
	}
	
	/**
	 * ДМ: Приглашение в программу дист.мониторинга
	 */
	function InviteInMonitoring($data) {
		$this->load->helper('Notify');
		if ($data['Persons']) {
			foreach($data['Persons'] as $person) {
				$msg = $data['MessageText'];
				if($data['isSingle']=='false') {
					$msg = str_replace("<ФИО>", $person->Person_SurName.' '.$person->Person_FirName.' '.$person->Person_SecName, $msg);
					$msg = str_replace("<Имя>", $person->Person_FirName, $msg);
					$msg = str_replace("<Первые буквы фамилии и отчества без пробела>", mb_substr($person->Person_SurName,0,1).mb_substr($person->Person_SecName,0,1), $msg);
				}
				$title = empty($data['MessageTitle']) ? "Программа дистанционного мониторинга здоровья" : $data['MessageTitle'];
				
				switch($data['FeedbackMethod']) {
					
					case 3:
						if(!empty($person->email))
						sendNotifyEmail(
							array(
								'EMail' => $person->email,
								'title' => $title,
								'body' => $msg
							)
						);
						break;
					case 1:
					case 2:
						if(!empty($person->phone)) {
							sendNotifySMS(
								array(
									'UserNotify_Phone' => $this->getPhoneNumber($person->phone),
									'text' => $msg,
									'User_id' => $data['pmUser_id'] // pmUser_id врача?
								)
							);
						}
						break;
					case 4:
					case 5:
						sendPushNotification(
							array(
								'Person_id' => $person->Person_id,
								'message' => $msg,
								'PushNoticeType_id' => 3,
								'action' => 'call'
							)
						);
						break;
				} //уведомление пациенту отправлено.
				//теперь запишем в таблицу для хранения приглашений:
				$params = array(
					'PersonLabel_id' => $person->PersonLabel_id,
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					'FeedbackMethod_id' => $data['FeedbackMethod_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				//но сначала сбросим статусы у имеющихся приглашений по метке
				$sql ="
					UPDATE LabelInvite with (rowlock)
					SET LabelInviteStatus_id = null
					WHERE PersonLabel_id = :PersonLabel_id
				";
				$res =  $this->db->query($sql, $params);
				
				$sql ="
					DECLARE	
							@LabelInvite_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_LabelInvite_ins
							@LabelInvite_id = @LabelInvite_id OUTPUT,
							@PersonLabel_id = :PersonLabel_id,
							@MedStaffFact_id = :MedStaffFact_id,
							@FeedbackMethod_id = :FeedbackMethod_id,
							@LabelInviteStatus_id = 1,
							@LabelInvite_RefuseCause = '',
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@LabelInvite_id as LabelInvite_id,
							@Error_Code as Error_Code,
							@Error_Message as Error_Message
				";
				$res =  $this->db->query($sql, $params);
				if(is_object($res)) {
					$res = $res->first_row();
					$params = array(
						'LabelInvite_id' => $res->LabelInvite_id,
						'LabelInviteStatus_id' => 1,
						'MedStaffFact_id' => $data['MedStaffFact_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$sql = "
						DECLARE
								@getdate datetime = dbo.tzGetDate(),
								@LabelInviteHistory_id bigint,
								@Error_Code int,
								@Error_Message varchar(4000)

						EXEC	p_LabelInviteHistory_ins
								@LabelInviteHistory_id = @LabelInviteHistory_id OUTPUT,
								@LabelInvite_id = :LabelInvite_id,
								@LabelInviteStatus_id = :LabelInviteStatus_id,
								@LabelInviteHistory_setDT = @getdate,
								@MedStaffFact_id = :MedStaffFact_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code OUTPUT,
								@Error_Message = @Error_Message OUTPUT

						SELECT	@LabelInviteHistory_id as LabelInviteHistory_id,
								@Error_Code as Error_Code,
								@Error_Message as Error_Message
					";
					$res =  $this->db->query($sql, $params);
				}
			}//foreach
		}
		return true;
	}
			
	/**
	 * ДМ: Отправить пациентам напоминание
	 */
	function RemindToMonitoring($data) {
		//Запасаемся справочником шаблонов текстов напоминаний по методам отправки
		$sql = "
			SELECT
				LabelMessageText_Text, 
				FeedbackMethod_id,
				Label_id
			FROM LabelMessageText 
			WHERE LabelMessageType_id=2
		";
		$res=$this->db->query($sql, array());
		$res=$res->result('array');
		$MsgAtFeedback=array();
		$MsgAtFeedback7=array();
		$title = '';
		foreach($res as $r) {
			$FeedbackMethod_id = $r['FeedbackMethod_id'];
			$s = $r['LabelMessageText_Text'];
			if($FeedbackMethod_id==3) { //email
				if(preg_match( "/ТЕМА\:(.+)/iu", $s, $matches)) {
					$title = trim($matches[1]);
					$s = substr($s, strlen($matches[0]));
					$body = trim($s);
				} else {
					$title = '';
					$body = trim($s);
				}
				if($r['Label_id']=='7') {
					$MsgAtFeedback7[$FeedbackMethod_id] = $body;
				} else {
					$MsgAtFeedback[$FeedbackMethod_id] = $body;
				}
			} else {
				if($r['Label_id']=='7') {
					$MsgAtFeedback7[$FeedbackMethod_id] = $s;
				} else {
					$MsgAtFeedback[$FeedbackMethod_id] = $s;
				}
			}
		}
		//Запасаемся контактами кабинета здоровья
		if(empty($data['session']) || empty($data['LpuSection_id'])) return false;

		$params = array( 'LpuSection_id' => $data['LpuSection_id'] );
		$sql = "
			SELECT 
				RTRIM(LTRIM(ISNULL(LBH.LpuBuildingHealth_Phone, ''))) as phone,
				RTRIM(LTRIM(ISNULL(LBH.LpuBuildingHealth_Email, ''))) as email,
				L.Lpu_Nick
			FROM v_LpuSection LS 
			LEFT JOIN v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
			LEFT JOIN v_Lpu L on L.Lpu_id = LU.Lpu_id
			LEFT JOIN v_LpuBuilding LB on LU.LpuBuilding_id = LB.LpuBuilding_id
			LEFT JOIN v_LpuBuildingHealth LBH on LBH.LpuBuilding_id = LB.LpuBuilding_id
			WHERE LS.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($sql, $params);
		if ( !is_object($result) ) {
			return false;
		}
		$cab = $result->first_row();

		$this->load->helper('Notify');
		if ($data['Persons']) {
			foreach($data['Persons'] as $person) {
				$FeedbackMethod_id = $person->FeedbackMethod_id;
				if($FeedbackMethod_id==1) $FeedbackMethod_id = 2;
				if($FeedbackMethod_id==4) $FeedbackMethod_id = 5;
				if($FeedbackMethod_id) {
					if($person->Label_id=='7')
						$msg = $MsgAtFeedback7[$FeedbackMethod_id];
					else
						$msg = $MsgAtFeedback[$FeedbackMethod_id];
					
					$msg = str_replace("<ФИО>", $person->Person_SurName.' '.$person->Person_FirName.' '.$person->Person_SecName, $msg);
					$msg = str_replace("<Имя>", $person->Person_FirName, $msg);
					$msg = str_replace("<Первые буквы фамилии и отчества без пробела>", mb_substr($person->Person_SurName,0,1).mb_substr($person->Person_SecName,0,1), $msg);
					
					$msg = str_replace("<номер телефона кабинета здоровья>", $cab->phone, $msg);
					$msg = str_replace("<адрес электронной почты кабинета здоровья>", $cab->email, $msg);
					$msg = str_replace("<краткое наименование МО>", $cab->Lpu_Nick, $msg);
				
					$title = $title=='' ? "Программа дистанционного мониторинга здоровья" : $title;
					$withError = false;
					switch($person->FeedbackMethod_id) {
						case 3:
							if(!empty($person->email)) {
								try {
									sendNotifyEmail(
										array(
											'EMail' => $person->email,
											'title' => $title,
											'body' => $msg
										)
									);
								} catch (Exception $e) {
									
								}
							}
							break;
						case 1:
						case 2:
							if(!empty($person->phone)) {
								try {
									sendNotifySMS(
										array(
											'UserNotify_Phone' => $this->getPhoneNumber($person->phone),
											'text' => $msg,
											'User_id' => $data['pmUser_id'] // pmUser_id врача?
										)
									);
								} catch (Exception $e) {
									
								}
							}
							break;
						case 4:
						case 5:
							try {
								sendPushNotification(
									array(
										'Person_id' => $person->Person_id,
										'message' => $msg,
										'PushNoticeType_id' => 3,
										'action' => 'call'
									)
								);
							} catch (Exception $e) {
								
							}
							break;
					} //уведомление пациенту отправлено.
					//Сделать запись об отправке
					$params = array(
						'MessageText' => $msg,
						'Chart_id' => $person->Chart_id,
						'FeedbackMethod_id' => $FeedbackMethod_id,
						'pmUser_id' => $data['pmUser_id']
					);
					$sql = "
					DECLARE	@getdate datetime = dbo.tzGetDate(),
							@LabelMessage_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_LabelMessage_ins
							@LabelMessage_id = @LabelMessage_id OUTPUT,
							@LabelObserveChart_id = :Chart_id,
							@LabelMessage_Text = :MessageText,
							@LabelMessageType_id = 2,
							@LabelMessage_sendDate = @getdate,
							@FeedbackMethod_id = :FeedbackMethod_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@LabelMessage_id as LabelMessage_id,
							@Error_Code as Error_Code,
							@Error_Message as Error_Message
					";
					$result = $this->db->query($sql, $params);
				}
			}
		}
		return true;
	}
	
	/**
	 * ДМ: Отправить пациенту сообщение
	 */
	function sendLabelMessage($data) {
		$this->load->helper('Notify');
		$msg = $data['MessageText'];
		$title = 'Программа дистанционного мониторинга';

		
		switch($data['FeedbackMethod_id']) {
			
			case 3:
				if(!empty($data['email']))
				sendNotifyEmail(
					array(
						'EMail' => $data['email'],
						'title' => $title,
						'body' => $msg
					)
				);
				break;
			case 1:
			case 2:
				if(!empty($data['phone']))
				sendNotifySMS(
					array(
						'UserNotify_Phone' => $this->getPhoneNumber($data['phone']),
						'text' => $msg,
						'User_id' => $data['pmUser_id'] // pmUser_id врача?
					)
				);
				break;
			case 4:
			case 5:
				sendPushNotification(
					array(
						'Person_id' => $data['Person_id'],
						'message' => $msg,
						'PushNoticeType_id' => 3,
						'action' => 'call'
					)
				);
				break;
		} //уведомление пациенту отправлено.
		
		//Сделать запись об отправке
		$params = array(
			'MessageText' => $msg,
			'Chart_id' => $data['Chart_id'],
			'FeedbackMethod_id' => $data['FeedbackMethod_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$sql = "
		DECLARE	@getdate datetime = dbo.tzGetDate(),
				@LabelMessage_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000)

		EXEC	p_LabelMessage_ins
				@LabelMessage_id = @LabelMessage_id OUTPUT,
				@LabelObserveChart_id = :Chart_id,
				@LabelMessage_Text = :MessageText,
				@LabelMessageType_id = 1,
				@LabelMessage_sendDate = @getdate,
				@FeedbackMethod_id = :FeedbackMethod_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT

		SELECT	@LabelMessage_id as LabelMessage_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Message
		";
		$result = $this->db->query($sql, $params);
		if ( !is_object($result) ) return false;
		return $result->result('array')[0];
	}
	
	/**
	 * ДМ: Изменение статуса приглашения в дист.мониторинг
	 */
	function ChangeLabelInviteStatus($data) {
		$params = array(
			'LabelInvite_id' => $data['LabelInvite_id'],
			'LabelInviteStatus_id' => $data['LabelInviteStatus_id'],
			'RefuseCause' => $data['RefuseCause'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$sql = "
			UPDATE LabelInvite with (rowlock) 
			SET LabelInviteStatus_id = :LabelInviteStatus_id, LabelInvite_RefuseCause = :RefuseCause
			WHERE LabelInvite_id = :LabelInvite_id
		";
		$res=$this->db->query($sql, $params);
		
		$sql = "
			DECLARE
					@getdate datetime = dbo.tzGetDate(),
					@LabelInviteHistory_id bigint,
					@Error_Code int,
					@Error_Message varchar(4000)

			EXEC	p_LabelInviteHistory_ins
					@LabelInviteHistory_id = @LabelInviteHistory_id OUTPUT,
					@LabelInvite_id = :LabelInvite_id,
					@LabelInviteStatus_id = :LabelInviteStatus_id,
					@LabelInviteHistory_setDT = @getdate,
					@MedStaffFact_id = :MedStaffFact_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@LabelInviteHistory_id as LabelInviteHistory_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Message
		";
		$res =  $this->db->query($sql, $params);
		if ( !is_object($res) ) return false;
		return $res->result('array');
	}
	
	/**
	 * ДМ: История включений в программу дистанционного мониторинга
	 * Используется: форма "История включения в программу" (InviteHistoryWindow)
	 */
	function loadLabelInviteHistory($data) {
		$sql = "
			SELECT 
				convert(varchar(19), LIH.LabelInviteHistory_setDT, 120) AS eventDate, 
				0 AS eventType, 
				LIH.LabelInviteStatus_id AS statusId
			FROM PersonLabel PL
			INNER JOIN LabelInvite LI on LI.PersonLabel_id=PL.PersonLabel_id
			INNER JOIN LabelInviteHistory LIH on LIH.LabelInvite_id = LI.LabelInvite_id
			WHERE PL.PersonLabel_id = :PersonLabel_id
			UNION ALL
			
			SELECT
				convert(varchar(19), LOC.LabelObserveChart_begDate, 120) AS eventDate, 
				1 AS eventType, 
				0 AS statusId
			FROM v_PersonLabel PL
			INNER JOIN LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
			WHERE PL.PersonLabel_id = :PersonLabel_id and LOC.LabelObserveChart_begDate is not null
			UNION ALL
			
			SELECT
				convert(varchar(19), LOC.LabelObserveChart_endDate, 120) AS eventDate, 
				2 AS eventType, 
				0 AS statusId
			FROM v_PersonLabel PL
			INNER JOIN LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
			WHERE PL.PersonLabel_id = :PersonLabel_id and LOC.LabelObserveChart_endDate is not null
		";
		//~ echo getDebugSQL($sql, array('Person_id'=>$data['Person_id']));exit;
		$res =  $this->db->query($sql, array('PersonLabel_id'=>$data['PersonLabel_id']));
		if ( !is_object($res) ) return false;
		return $res->result('array');
	}
	
	/**
	 * ДМ: Список сообщений в карте наблюдения
	 */
	function loadLabelMessages($data) {
		$params = array(
			'Chart_id' => $data['Chart_id']
		);
		$query = "
			SELECT
				-- select
				convert(varchar(19), LM.LabelMessage_sendDate, 120) as MessageDate,
				LM.LabelMessage_Text,
				LM.LabelMessageType_id,
				FM.FeedbackMethod_Name
				-- end select
			FROM
				-- from
				v_LabelMessage LM with (nolock)
				inner join FeedbackMethod FM with (nolock) on FM.FeedbackMethod_id=LM.FeedbackMethod_id
				-- end from
			WHERE
				-- where
				LM.LabelObserveChart_id = :Chart_id
				--end where
			ORDER BY
				-- order by
				LM.LabelMessage_sendDate DESC
				-- end order by
		";
		//~ echo getDebugSQL($query, $params);exit;
		$messages = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true, true);
		
		return array('messages'=>$messages);
	}
	
	/**
	 * Заполнение меток у пациентов
	 */
	function setLabels($userId) {
		ini_set('memory_limit', '2048M');
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");
		session_set_cookie_params(86400);
		ini_set("session.gc_maxlifetime",86400);
		ini_set("session.cookie_lifetime",86400);
		$this->load->library('textlog', array('file'=>'setLabels_'.date('Y-m-d').'.log'));
		
		$sql = "
			SELECT TOP 1 convert(varchar(19), Log_Time, 120) FROM log 
			WHERE Log_Object='PersonLabelUpdate'
			ORDER BY Log_Time DESC
		";
		$last_exec_DT = $this->getFirstResultFromQuery($sql, array());
		
		if(!empty($last_exec_DT)) $this->textlog->add('setLabels: previous start script at '.$last_exec_DT);
		$this->textlog->add('setLabels: start script.');
		$sql = "
			SELECT LD.Label_id FROM v_LabelDiag LD (nolock) WHERE LD.UslugaComplex_id is not null
		";
		$resp = $this->db->query($sql, array() );
		if(!is_object($resp)) return false;
		$labels = $resp->result('array');
		foreach($labels as $label) {
			$this->textlog->add('setLabels.add: start add for Label_id = '.$label['Label_id']);
			$sql = "
				DECLARE @curDate date = dbo.tzGetDate();
				SELECT
					PS.Person_id
				FROM v_labelDiag LD (nolock)
					inner join v_PersonState PS (nolock) on (PS.Sex_id = LD.Sex_id OR LD.Sex_id is null) AND PS.Person_deadDT is null
						AND (LD.LabelDiag_From is null OR DATEDIFF(year, PS.Person_BirthDay, @curDate ) > LD.LabelDiag_From)
						AND (LD.LabelDiag_To is null OR DATEDIFF(year, PS.Person_BirthDay, @curDate ) < LD.LabelDiag_To)
				WHERE LD.Label_id=:Label_id
					AND not exists (
						SELECT EUP.EvnUslugaPar_id 
						FROM v_EvnUslugaPar EUP (nolock) 
						WHERE EUP.UslugaComplex_id = LD.UslugaComplex_id 
							AND EUP.Person_id = PS.Person_id 
							AND DATEDIFF(year, EUP.EvnUslugaPar_disDT, @curDate ) < LD.LabelDiag_Period
					)
					AND not exists(
						SELECT PL.PersonLabel_id 
						FROM v_PersonLabel PL (nolock) 
						WHERE PL.Person_id=PS.Person_id AND PL.Label_id=:Label_id AND PL.PersonLabel_disDate is NULL
					)
			";
			$resp = $this->db->query($sql, array('Label_id'=>$label['Label_id']) );
			if(!is_object($resp)) return false;
			$persons = $resp->result('array');
			$rows=array();
			$query = "DECLARE @curDate datetime = dbo.tzGetDate(); INSERT INTO PersonLabel (Label_id,Person_id,PersonLabel_setDate,PersonLabel_disDate,Diag_id,pmUser_insID,pmUser_updID,PersonLabel_insDT,PersonLabel_updDT)  VALUES ";
			$totalcount = count($persons); $i = 0;
			foreach($persons as $row) {
				$i++;				
				$sql = "
					DECLARE
							@curDate datetime = dbo.tzGetDate(),
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_PersonLabel_ins
							@Label_id = :Label_id,
							@Person_id = :Person_id,
							@PersonLabel_setDate = @curDate,
							@PersonLabel_disDate = NULL,
							@Diag_id = NULL,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@Error_Code as Error_Code,
							@Error_Message as Error_Message
				";
				$resp = $this->db->query($sql, array(
					'Label_id'=>$label['Label_id'],
					'Person_id'=>$row['Person_id'],
					'pmUser_id'=>$userId
					) );
				if($i % 1000 == 0) 
					$this->textlog->add("setLabels.add: progress $i / $totalcount");
			}//foreach person
			unset($persons);
			$resp->free_result();
			$this->textlog->add("setLabels.add: count = [$totalcount] . Finish add for Label_id = ".$label['Label_id']);
		}//foreach label
		$this->textlog->add('setLabels.add: finish.');
		
		/// Проверка меток за время с последнего запуска
		if(!empty($last_exec_DT)) {
			$this->textlog->add('setLabels.upd: start update Labels.');
			foreach($labels as $label) {
				$this->textlog->add('setLabels.upd: update for Label_id = '.$label['Label_id']);
				$sql = "
					DECLARE @curDate date = dbo.tzGetDate();
					SELECT
						PS.Person_id, l_usluga.EvnUslugaPar_setDT, l_usluga.EvnUslugaPar_id
					FROM v_LabelDiag LD (nolock)
						inner join v_PersonState PS (nolock) on (PS.Sex_id = LD.Sex_id OR LD.Sex_id is null) AND PS.Person_deadDT is null
							AND (LD.LabelDiag_From is null OR DATEDIFF(year, PS.Person_BirthDay, @curDate ) > LD.LabelDiag_From)
							AND (LD.LabelDiag_To is null OR DATEDIFF(year, PS.Person_BirthDay, @curDate ) < LD.LabelDiag_To)
						outer apply (
							SELECT TOP 1 EUP.EvnUslugaPar_id, EUP.EvnUslugaPar_setDT, PL.PersonLabel_id
							FROM v_EvnUslugaPar EUP (nolock) 
								inner join v_PersonLabel PL (nolock) on LD.Label_id = PL.Label_id AND PL.Person_id=PS.Person_id AND PL.PersonLabel_disDate is NULL
							WHERE EUP.UslugaComplex_id = LD.UslugaComplex_id 
								AND EUP.Person_id = PS.Person_id 
								AND EUP.EvnUslugaPar_setDT is not null
								AND EUP.EvnUslugaPar_disDT > :last_exec_dt
							ORDER BY EUP.EvnUslugaPar_setDT DESC
						) l_usluga
					WHERE LD.Label_id=:Label_id
						AND l_usluga.EvnUslugaPar_id is not null
						AND l_usluga.PersonLabel_id is not null
				";
				$resp = $this->db->query($sql, array('Label_id'=>$label['Label_id'], 'last_exec_dt'=>$last_exec_DT) );
				if(!is_object($resp)) return false;
				$persons = $resp->result('array');
				$rows=array();
				$totalcount = count($persons); $i = 0;
				foreach($persons as $row) {
					$i++;
					$sql = "
						DECLARE	@curDate datetime = dbo.tzGetDate()
						UPDATE PersonLabel SET PersonLabel_disDate=:EvnUslugaPar_setDT 
						WHERE PersonLabel_id=:PersonLabel_id
					";
					$resp = $this->db->query($sql, array(
						'EvnUslugaPar_setDT'=>$label['EvnUslugaPar_setDT'],
						'PersonLabel_id'=>$row['PersonLabel_id']
						) );
					if($i % 1000 == 0) 
						$this->textlog->add("setLabels.upd: progress $i / $totalcount");
				}//foreach person
				unset($persons);
				$resp->free_result();
				$this->textlog->add("setLabels.upd: count = [$totalcount] . Finish update for Label_id = ".$label['Label_id']);
			}
		}
		$this->textlog->add('setLabels: script finished.');
		
		$sql="
			EXEC p_Log_set
			@Log_Object = 'PersonLabelUpdate',
			@Log_Message = 'Обновление меток',
			@Log_Type = 'debug'
		";
		$this->db->query($sql, array());
		return true;
	}
	
	/**
	 * Создать для пациента метку
	 */
	function createPersonLabel($data) {
		$sql = "
		DECLARE	
				@PersonLabel_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000)

		EXEC	p_PersonLabel_ins
				@PersonLabel_id = @PersonLabel_id OUTPUT,
				@Label_id = :Label_id,
				@Person_id = :Person_id,
				@Diag_id = :Diag_id,
				@pmUser_id = :pmUser_id,
				@PersonLabel_setDate = :setDate,
				@Error_Code = @Error_Code OUTPUT,
				@Error_Message = @Error_Message OUTPUT

		SELECT	@PersonLabel_id as PersonLabel_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Message
		";
		
		$res =  $this->db->query($sql, array(
			'Label_id' => $data['Label_id'],
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id'],
			'setDate' => $data['dateConsent'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if(!is_object($res)) return false;
		
		$res = $res->result('array');
		return $res[0];
	}
	
	/**
	 * Получить информацию по мониторингу температуры пациента
	 */
	function getMonitorTemperatureInfo($data) {
		$sql = "
			select
				convert(varchar(10), PLA.PersonLabel_setDate, 104) as MonitorTemperatureStartDate
			from
				v_PersonLabel PLA with (nolock)
				inner join v_LabelObserveChart LOC with (nolock) on PLA.PersonLabel_id=LOC.PersonLabel_id
			where
				PLA.Person_id = :Person_id AND PLA.PersonLabel_disDate is null AND PLA.Label_id=7
		";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);
		$res =  $this->db->query($sql, $params);
		if (is_object($res)) {
			return $res->result('array');
		}
		return false;
	}
	
	/**
	 * Получить список открытых карт наблюдения по пациенту
	 * Используется: вкладка "мониторинг" в ЭМК
	 */
	function getLabelObserveCharts($data) {
		
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Person_lpu_id']
		);
		
		$res = $this->queryResult("
			SELECT 
				PL.Label_id,
				LOC.LabelObserveChart_id,
				PL.Person_id
			FROM v_PersonLabel PL 
				inner join v_LabelObserveChart LOC on LOC.PersonLabel_id=PL.PersonLabel_id
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = LOC.MedStaffFact_id
			WHERE PL.Person_id = :Person_id AND LOC.LabelObserveChart_endDate is null AND MSF.Lpu_id=:Lpu_id
		", $params);
		
		return $res;
	}
	
	/**
	 * наличие диспансерной карты с причиной снятия Смерть
	 */
	function getAvailabilityDispensaryCardCauseDeath($data){
		if(empty($data['Person_id'])) return 0;
		$params['Person_id'] = $data['Person_id'];
		$sql = "
			SELECT count(*) as count
			FROM v_PersonDisp PD with (nolock)
				left join v_DispOutType DOT (nolock) on DOT.DispOutType_id = PD.DispOutType_id
			WHERE 
				PD.Person_id = :Person_id and 
				DOT.DispOutType_Code = 3
		";
		$res =  $this->db->query($sql, $params);
		
		if (!is_object($res)) return false;
		
		$res = $res->result('array');
		return $res[0]['count'];
	}
	
	/**
	 *  Замена ответственного врача
	 */
	function setResponsibleReplacementOptionsDoctor($data){
		if(empty($data['MedStaffFact_id']) || empty($data['personDispList'])) return false;
		$resultErr = array();
		$resultSuccessfully = array();
		$personDispARR = explode(',', $data['personDispList']);
		$curentDT = new DateTime();
		$dt = new DateTime();
		$curentDT = $curentDT->format('Y-m-d');
		$yesterdayDT = $dt->modify('-1 day')->format('Y-m-d');
		
		foreach ($personDispARR as $value) {
			$PersonDisp_id = $value;
			$curentDoctor = $this->getCurrentResponsibleDoctor(array('PersonDisp_id' => $PersonDisp_id));
			if($curentDoctor && !empty($curentDoctor[0]['PersonDispHist_id']) && empty($curentDoctor[0]['PersonDispHist_endDate'])){
				if($curentDoctor[0]['MedPersonal_id'] != $data['MedPersonal_id'] && $curentDoctor[0]['PersonDispHist_begDate'] < $curentDT){
					$flag = true;
					$paramsClose = array(
						'PersonDispHist_id' => $curentDoctor[0]['PersonDispHist_id'],
						'PersonDisp_id' => $curentDoctor[0]['PersonDisp_id'],
						'MedPersonal_id' => $curentDoctor[0]['MedPersonal_id'],						
						'MedStaffFact_id' => null,
						'LpuSection_id' => $curentDoctor[0]['LpuSection_id'],
						'PersonDispHist_begDate' => $curentDoctor[0]['PersonDispHist_begDate'],
						'PersonDispHist_endDate' => $yesterdayDT,
						'pmUser_id' => $data['pmUser_id']
					);
					//закрываем ответственного врача
					$resClose = $this->savePersonDispHist($paramsClose);
					if($resClose && (!empty($resClose['Error_Msg']) || empty($resClose[0]['PersonDispHist_id']))){
						$err = '';
						if((!empty($resClose['Error_Msg']))) $err .= $resClose['Error_Msg'].'. ';
						if(!empty($resClose[0]['Error_Msg'])) $err .= $resClose[0]['Error_Msg'];
						$resultErr[$value] = $err;
						$flag = false;
					}
					if($flag){
						$paramsAdd = array(
							'PersonDispHist_id' => null,
							'PersonDisp_id' => $curentDoctor[0]['PersonDisp_id'],
							'MedPersonal_id' => $data['MedPersonal_id'],
							'MedStaffFact_id' => $data['MedStaffFact_id'],
							'LpuSection_id' => $data['LpuSection_id'],
							'PersonDispHist_begDate' => $curentDT,
							'PersonDispHist_endDate' => null,
							'pmUser_id' => $data['pmUser_id']
						);
						// Создается новая запись «ответственный врач» 
						$resAdd = $this->savePersonDispHist($paramsAdd);
						if(!empty($resAdd[0]['PersonDispHist_id'])){
							$resultSuccessfully[$value] = $resAdd[0]['PersonDispHist_id'];
						}else{
							$resultErr[$value] = (!empty($resClose[0]['Error_Msg'])) ? $resClose[0]['Error_Msg'] : 'Ошибка при добавлении нового ответственного врача';
						}
					}
				}else{
					$resultErr[$value] = 'Не соответствует условию';
				}
			}
		}
		
		return array('resultErr' => $resultErr, 'resultSuccessfully' => $resultSuccessfully);
	}
	
	/**
	 * Получить текущего отвественного врача
	 */
	function getCurrentResponsibleDoctor($data) {
		if(empty($data['PersonDisp_id'])) return false;
		$sql = "
			select top 1 
				MP.MedPersonal_id,
				PDSD.PersonDisp_id,
				PDSD.PersonDispHist_id,
				PDSD.MedPersonal_id,
				PDSD.LpuSection_id,
				convert(varchar,PersonDispHist_begDate,23) as PersonDispHist_begDate,
				convert(varchar,PersonDispHist_endDate,23) as PersonDispHist_endDate
			from v_PersonDispHist PDSD with(nolock)
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = PDSD.MedPersonal_id
			where PDSD.PersonDisp_id = :PersonDisp_id
			order by PDSD.PersonDispHist_begDate desc	
		";
		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка существования показателей у пользователя
	 */
	function checkPersonLabelObserveChartRates($data) {
		$result = $this->queryResult("
			select top 1
				locr.LabelObserveChartRate_id
			from v_LabelObserveChartRate locr (nolock)
			left join v_ratetype rt (nolock) on rt.RateType_id = locr.RateType_id
			where (1=1)
				and locr.Person_id = :Person_id
		", array('Person_id' => $data['Person_id']));

		return $result;
	}

	/**
	 * #198097
	 * Поднятие группы риска
	 */
	function upRiskGroup($ids_select,$group_condition) {
		$highLevel = ['I11', 'I12', 'I13', 'I15', 'I20.0', 'I21', 'I22', 'I20.8', 'I48', 'I60',
		 'I61', 'I62', 'I63.0', 'I64', 'G45', 'I65', 'I66', ' I 70.0', 'I 70.1', ' I 70.2', 
		 'I70.8', 'I70.9', 'N18.3', 'N18.4', 'N18.5', 'E10', 'E11', 'E12', 'E13', 'E14', 
		 'Е78.0', 'H47.1', 'H34', 'H35.0', 'H35.2', 'H36.0'];
		
		$middleLevel = ['I10', 'I14'];

		$highLevelUsluga = ["A16.12.004.009", "A16.12.004.010", "A16.12.004.012", "A16.12.004.013", "A16.12.026", "A16.12.026.011",
		"A16.12.026.012", "A16.12.028.003", "A16.12.028.017", "A16.12.004.001", 
		"A16.12.004.002", "A16.12.004.003", "A16.12.004.004", "A16.12.004.005", 
		"A16.12.004.006", "A16.12.004.007", "A16.12.004.011"];

		$result = $this->db->query("
		with DD as (
			select 
				vloc.LabelObserveChart_id as LabelObserveChart_id
				,case {$group_condition} as HypertensionRiskGroup_id
			from 
				v_PersonLabel vpl (nolock)
				inner join v_LabelObserveChart vloc (nolock) on vpl.PersonLabel_id=vloc.PersonLabel_id
				outer apply(
					select top 1
						count(1) as Count
					from 
						v_PersonDiag vpd2 with (nolock) 
						left outer join v_PersonDisp vpd with (nolock) on vpd2.Person_id = vpd.Person_id
						inner join v_Diag vd with (nolock) on vd.Diag_id=vpd2.Diag_id
					where 
						vpd.PersonDisp_id = vloc.PersonDisp_id and vd.Diag_Code in ('".implode("','",$highLevel)."')
				) as high
				outer apply(
					select top 1
						count(1) as Count
					from 
						v_PersonDiag vpd2 with (nolock) 
						left outer join v_PersonDisp vpd with (nolock) on vpd2.Person_id = vpd.Person_id
						inner join v_Diag vd with (nolock) on vd.Diag_id=vpd2.Diag_id
					where 
						vpd.PersonDisp_id = vloc.PersonDisp_id and vd.Diag_Code in ('".implode("','",$middleLevel)."')
				) as middle
				outer apply(
					select top 1
		 				count(1) as Count
					from 
						v_EvnUsluga veu with (nolock)
						inner join v_UslugaComplex vuc with (nolock) on vuc.UslugaComplex_id=veu.UslugaComplex_id
						left outer join v_PersonDisp vpd with (nolock) on vpd.Person_id = veu.Person_id
					where 
						vpd.PersonDisp_id = vloc.PersonDisp_id
						and vuc.UslugaComplex_Code in ('".implode("','",$highLevelUsluga)."')
				) as usluga
			where 
				vpl.Label_id = 1 and vloc.LabelObserveChart_endDate is null
				and vpl.Person_id in ({$ids_select})
		)

		update 
			v_LabelObserveChart
		set 
			v_LabelObserveChart.HypertensionRiskGroup_id=DD.HypertensionRiskGroup_id
		from 
			v_LabelObserveChart
			inner join DD on DD.LabelObserveChart_id = v_LabelObserveChart.LabelObserveChart_id
		", array());

		return $result;
	}

	/**
	 * #198097
	 * Выставление риска для АГ
	 */
	function setHypertensionRisk() {
		//Со средней группы к высокой
		$ids_select="
		select DISTINCT 
			v.Person_id
		from 
			v_LabelObserveChart vlc (nolock)
			inner join v_PersonDisp v (nolock) on vlc.PersonDisp_id = v.PersonDisp_id
		where 
			vlc.HypertensionRiskGroup_id = 2";
		
		$group_condition="
		WHEN 
			high.Count = 1 or usluga.Count > 0 
		THEN 3
		else vloc.HypertensionRiskGroup_id
		end";

		$this->upRiskGroup($ids_select,$group_condition);

		//С низкой группы к средней и высокой
		$ids_select="
			select DISTINCT 
				v.Person_id
			from 
				v_LabelObserveChart vlc (nolock)
				inner join v_PersonDisp v (nolock) on vlc.PersonDisp_id = v.PersonDisp_id
			where 
				vlc.HypertensionRiskGroup_id = 1";
		
		$group_condition="
		WHEN 
			high.Count = 1 or usluga.Count >0 THEN 3
		else case 
			WHEN 
				middle.Count = 1 
			then 2 
			else 1 end 
		end";

		$this->upRiskGroup($ids_select,$group_condition);

		return true;
	}

	/**
	 * #198097
	 * Выставление Признака превышения
	 */
	function setIsDeviant() {
		//Со средней группы к высокой
		$endDT=date('Y-m-d',strtotime('+1 days'));
		$begDT=date('Y-m-d',time());
		$select="
		with DD as (
			select 
				vpd.Person_id,
				vloc.LabelObserveChart_id,
				vlocm.LabelObserveChartMeasure_id,
				vlocm.LabelObserveChartMeasure_insDT,
				vlocm.LabelObserveChartMeasure_Value,
				vloc.HypertensionRiskGroup_id,
				vrt.RateType_SysNick,
				case WHEN vloc.HypertensionRiskGroup_id=3
					then 130
					else 120 end as Max_Value,
				case WHEN vloc.HypertensionRiskGroup_id=3 and vrt.RateType_SysNick = 'systolic_blood_pressure'
					then 0.077
					else 0.333 end as SQ,
				case WHEN vloc.HypertensionRiskGroup_id=3 and vrt.RateType_SysNick = 'diastolic_blood_pressure'
					then 0.125
					else 0.25 end as DQ
			from 
				v_LabelObserveChartMeasure vlocm (nolock)
				inner join v_LabelObserveChartInfo vloi (nolock) on vloi.LabelObserveChartInfo_id=vlocm.LabelObserveChartInfo_id
				inner join v_LabelObserveChart vloc (nolock) on vloi.LabelObserveChart_id=vloc.LabelObserveChart_id
				inner join v_PersonLabel vpl (nolock) on vpl.PersonLabel_id = vloc.PersonLabel_id
				inner join v_Label vl (nolock) on vl.Label_id=vpl.Label_id
				inner join v_LabelObserveChartRate vlocr (nolock) on vlocr.LabelObserveChartRate_id=vlocm.LabelObserveChartRate_id
				inner join v_PersonDisp vpd (nolock) on vpd.PersonDisp_id=vloc.PersonDisp_id
				inner join v_ratetype vrt (nolock) on vrt.RateType_id=vlocr.RateType_id
			where 
				vrt.RateType_SysNick in ('systolic_blood_pressure','diastolic_blood_pressure')
				and vlocm.LabelObserveChartMeasure_insDT < '{$endDT}'
				and vlocm.LabelObserveChartMeasure_insDT >= '{$begDT}'
				and vl.Label_id=1)

			update 
				v_LabelObserveChartMeasure
			set 
				v_LabelObserveChartMeasure.LabelObserveChartMeasure_IsDeviant = 1
			from 
				v_LabelObserveChartMeasure vlocm 
				inner join DD on vlocm.LabelObserveChartMeasure_id = DD.LabelObserveChartMeasure_id
			where 
				RateType_SysNick = 'systolic_blood_pressure' and vlocm.LabelObserveChartMeasure_Value>=Max_Value+Max_Value*SQ
				OR
				RateType_SysNick = 'diastolic_blood_pressure' and vlocm.LabelObserveChartMeasure_Value>=Max_Value+Max_Value*DQ";


		$res = $this->db->query($select, array());
		//Заполнение сигнальной информаии
		$query="
		INSERT INTO 
			LabelObserveSignalInfo (Person_id,LabelObserveSignalInfo_MonthCount,LabelObserveSignalInfo_setDate,
			pmUser_insID,pmUser_updID,LabelObserveSignalInfo_insDT,LabelObserveSignalInfo_updDT,LabelObserveChartMeasure_id)
		select 
			vpd.Person_id,
			0 as LabelObserveSignalInfo_MonthCount,
			'{$begDT}' as LabelObserveSignalInfo_setDate,
			1 as  pmUser_insID,
			1 as  pmUser_updID,
			'{$begDT}' as LabelObserveSignalInfo_insDT,
			'{$begDT}' as LabelObserveSignalInfo_updDT,
			max(vlocm.LabelObserveChartMeasure_id) as LabelObserveChartMeasure_id
			from 
				v_LabelObserveChartMeasure vlocm (nolock)
				inner join v_LabelObserveChartInfo vloi (nolock) on vloi.LabelObserveChartInfo_id=vlocm.LabelObserveChartInfo_id
				inner join v_LabelObserveChart vloc (nolock) on vloi.LabelObserveChart_id=vloc.LabelObserveChart_id
				inner join v_PersonDisp vpd (nolock) on vpd.PersonDisp_id=vloc.PersonDisp_id
				left join v_LabelObserveSignalInfo vsi (nolock) on vsi.Person_id = vpd.Person_id
			where 
				vlocm.LabelObserveChartMeasure_IsDeviant = 1
				and vlocm.LabelObserveChartMeasure_insDT < '{$endDT}'
				and vlocm.LabelObserveChartMeasure_insDT > '{$begDT}'
				and vsi.LabelObserveSignalInfo_id is null
			group by 
				vpd.Person_id";

		$res = $this->db->query($query, array());
		$query="
			update 
				LabelObserveSignalInfo
			set 
				LabelObserveSignalInfo.LabelObserveSignalInfo_MonthCount = LabelObserveSignalInfo.LabelObserveSignalInfo_MonthCount+1
			from 
				v_LabelObserveChartMeasure vlocm (nolock)
				inner join v_LabelObserveChartInfo vloi (nolock) on vloi.LabelObserveChartInfo_id = vlocm.LabelObserveChartInfo_id
				inner join v_LabelObserveChart vloc (nolock) on vloi.LabelObserveChart_id = vloc.LabelObserveChart_id
				inner join v_PersonDisp vpd (nolock) on vpd.PersonDisp_id=vloc.PersonDisp_id
				inner join v_LabelObserveSignalInfo vsi (nolock) on vsi.Person_id = vpd.Person_id
			where 
				vlocm.LabelObserveChartMeasure_IsDeviant = 1
				and vlocm.LabelObserveChartMeasure_insDT < '{$endDT}'
				and vlocm.LabelObserveChartMeasure_insDT > '{$begDT}'";

		$res = $this->db->query($query, array());
		return true;
	}
}
