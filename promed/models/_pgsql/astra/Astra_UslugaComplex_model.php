<?php
require_once(APPPATH.'models/_pgsql/UslugaComplex_model.php');

/**
 * @property Usluga_model Usluga_model
 */
class Astra_UslugaComplex_model extends UslugaComplex_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	*  Читает дерево комплексных услуг
	*/
	function loadUslugaComplexTree($data) {
		$queryParams = array(
			 'Lpu_id' => $data['Lpu_id']
			,'Lpu_uid' => $data['Lpu_uid']
			,'UslugaCategory_id' => $data['UslugaCategory_id']
			,'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplexLevel_id' => $data['UslugaComplexLevel_id']
		);

		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);
		
		switch ( $data['level'] ) {
			case 0:
				$query = "
					select
						'ucat' || ucat.UslugaCategory_id as id
						,null as code
						,ucat.UslugaCategory_Name as name
						,'UslugaCategory' as object
						,ucat.UslugaCategory_id as \"UslugaCategory_id\"
						,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
						,null as UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
						,case when ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms', 'lpu') or uccgost.cnt > 0
							then 0
							else 1
						end as leaf
					from
						v_UslugaCategory ucat
						left join lateral(
							select count(UslugaComplex_id) as cnt
							from v_UslugaComplex
							where UslugaCategory_id = ucat.UslugaCategory_id
								and UslugaCategory_SysNick in ('gost2011', 'gost2004', 'Kod7')
								and UslugaComplexLevel_id is not null
						) uccgost on true
					order by
						ucat.UslugaCategory_Code
				";
			break;

			case 1:
				switch ( $data['UslugaCategory_SysNick'] ) {
					case 'lpu':
						$query = "
							select
								 'lpu' || Lpu_id as id
								,null as code
								,Lpu_Nick as name
								,'Lpu' as object
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,null as \"UslugaComplexLevel_id\"
								,1 as leaf
							from
								v_Lpu
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
							" . ( !isSuperadmin() ? "where Lpu_id = :Lpu_id" : "") . "
							order by
								Lpu_Nick
						";
					break;

					case 'gost2004':
					case 'gost2011':
					case 'Kod7':
						$query = "
							select
								 'ucom' || uc.UslugaComplex_id as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when uc.UslugaComplexLevel_id = 1 or ucc.cnt = 0
									then 1
									else 0
								end as leaf
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral(
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (5, 6)
								) ucc on true
							where
								uc.UslugaComplexLevel_id in (1, 4)
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;

					case 'tfoms':
						$query = "
							select
								 'ucom' || uc.UslugaComplex_id as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when uc.UslugaComplexLevel_id = 1 or ucc.cnt = 0
									then 1
									else 0
								end as leaf
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral(
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
								) ucc on true
							where
								uc.UslugaComplexLevel_id in (1, 4)
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
				}
			break;

			default:
				switch ( $data['UslugaCategory_SysNick'] ) {
					case 'gost2004':
					case 'gost2011':
					case 'Kod7':
						$query = "
							select
								 'ucom' || uc.UslugaComplex_id as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when ucc.cnt = 0
									then 1
									else 0
								end as leaf
							from
								v_UslugaComplex uc
								inner join v_UslugaComplex ucp on ucp.UslugaComplex_id = uc.UslugaComplex_pid
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral(
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (5, 6)
								) ucc on true
							where
								ucp.UslugaComplexLevel_id = :UslugaComplexLevel_id
								and ucp.UslugaComplex_id = :UslugaComplex_id
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;

					case 'tfoms':
						$query = "
							with UCtmp as (
								select
									t1.UslugaComplex_id,
									t3.cnt
								from v_UslugaComplex t1
									inner join v_UslugaComplex t2 on t2.UslugaComplex_pid = t1.UslugaComplex_id
									left join lateral(
										select count(UslugaComplex_id) as cnt
										from v_UslugaComplex
										where UslugaComplex_pid = t2.UslugaComplex_id
									) t3 on true
								where
									t1.UslugaComplex_pid = :UslugaComplex_id
									and t1.UslugaCategory_id = :UslugaCategory_id
							)

							select
								 'ucom' || uc.UslugaComplex_id as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when ucctmp.UslugaComplex_id is null
									then 1
									else 0
								end as leaf
							from
								v_UslugaComplex uc
								inner join v_UslugaComplex ucp on ucp.UslugaComplex_id = uc.UslugaComplex_pid
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral(
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
								) ucc on true
								left join lateral(
									select
										UslugaComplex_id
									from UCtmp
									where UslugaComplex_id = uc.UslugaComplex_id
										and cnt > 0
									limit 1
								) ucctmp on true
							where
								ucp.UslugaComplexLevel_id = :UslugaComplexLevel_id
								and ucp.UslugaComplex_id = :UslugaComplex_id
								and uc.UslugaCategory_id = :UslugaCategory_id
								and ucc.cnt > 0
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
				}
			break;
		}

		// echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result =  $result->result('array');
			foreach ($result as $key => $value) {
				if (isset($value['leaf'])) {
					$result[$key]['leaf'] = intval($value['leaf']);
				}
			}
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение состава комплексной услуги
	 */
	function loadUslugaContentsGrid($data) {
		$filters = "";
		
		$queryParams = array(
			 'Lpu_uid' => $data['Lpu_uid']
			,'UslugaCategory_id' => $data['UslugaCategory_id']
			,'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);
		
		if (!empty($data['UslugaComplex_CodeName'])) {
			$filters .= " AND coalesce(uc.UslugaComplex_Code, '') || ' ' || coalesce(uc.UslugaComplex_Name, '') ilike '%' || :UslugaComplex_CodeName || '%'";
			$queryParams['UslugaComplex_CodeName'] = $data['UslugaComplex_CodeName'];
		}

		if (!empty($data['isClose'])){
			if ($data['isClose'] == 1){ // открытые
				$filters .= " and (dbo.tzGetDate() < uc.UslugaComplex_endDT or uc.UslugaComplex_endDT IS NULL)";
			} elseif ($data['isClose'] == 2){ // закрытые
				$filters .= " and (dbo.tzGetDate() >= uc.UslugaComplex_endDT)";
			}
		}
		
		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);
		
		if ( $data['contents'] == 2 ) {
			$query = "
				select
					-- select
					 ucc.UslugaComplexComposition_id as \"UslugaComplexComposition_id\"
					,uc.UslugaComplex_id as \"UslugaComplex_id\"
					,uc.Lpu_id as \"Lpu_id\"
					,ucat.UslugaCategory_id as \"UslugaCategory_id\"
					,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
					,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
					,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					,uc.UslugaComplex_Name as \"UslugaComplex_Name\"
					,coalesce(l.Lpu_Nick, '') as \"Lpu_Name\"
					,1 as \"RecordStatus_Code\"
					,UCCCount.count as \"CompositionCount\"
					,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
					,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
					-- end select
				from
					-- from
					v_UslugaComplexComposition ucc
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucc.UslugaComplex_id
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_Lpu l on l.Lpu_id = uc.Lpu_id
					left join lateral(
						select
							count(UslugaComplexComposition_id) as count
						from v_UslugaComplexComposition ucc
						where ucc.UslugaComplex_pid = uc.UslugaComplex_id
					) UCCCount on true
					-- end from
				where
					-- where
					ucc.UslugaComplex_pid = :UslugaComplex_pid
					{$filters}
					-- end where
				order by
					-- order by
					uc.UslugaComplex_Code
					-- end order by
			";
		}
		else {
			$query = "
				select
					-- select
					 null as \"UslugaComplexComposition_id\"
					,uc.UslugaComplex_id as \"UslugaComplex_id\"
					,uc.Lpu_id as \"Lpu_id\"
					,ucat.UslugaCategory_id as \"UslugaCategory_id\"
					,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
					,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
					,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					,uc.UslugaComplex_Name as \"UslugaComplex_Name\"
					,coalesce(l.Lpu_Nick, '') as \"Lpu_Name\"
					,1 as \"RecordStatus_Code\"
					,UCCCount.count as \"CompositionCount\"
					,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
					,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
					-- end select
				from
					-- from
					v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_Lpu l on l.Lpu_id = uc.Lpu_id
					left join lateral(
						select
							count(UslugaComplexComposition_id) as count
						from v_UslugaComplexComposition ucc
						where ucc.UslugaComplex_pid = uc.UslugaComplex_id
					) UCCCount on true
					" . (in_array($data['UslugaCategory_SysNick'],array('pskov_foms', 'tfoms')) ? "outer apply (
						select count(UslugaComplex_id) as cnt
						from v_UslugaComplex
						where UslugaComplex_pid = uc.UslugaComplex_id
					) uccont" : "") . "
					-- end from
				where
					-- where
					uc.UslugaCategory_id = :UslugaCategory_id
					" . (in_array($data['UslugaCategory_SysNick'],array('pskov_foms', 'tfoms')) ? "and uccont.cnt = 0 and uc.UslugaComplex_pid = :UslugaComplex_pid" : "") . "
					" . (in_array($data['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'Kod7')) ? "and (uc.UslugaComplex_pid = :UslugaComplex_pid or UslugaComplex_pid in (select UslugaComplex_id from v_UslugaComplex where UslugaComplex_pid = :UslugaComplex_pid))" : "") . "
					" . ($data['UslugaCategory_SysNick'] == 'lpu' ? "and uc.Lpu_id = :Lpu_uid" : "") . "
					{$filters}
					-- end where
				order by
					-- order by
					uc.UslugaComplex_Code
					-- end order by
			";
		}

		$response = array();

		if ( $data['paging'] == 2 ) {
			if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
				$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
				$result = $this->db->query($limit_query, $queryParams);
			}
			else {
				$result = $this->db->query($query, $queryParams);
			}

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) ) {
					if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
						$response['data'] = $res;
						$response['totalCount'] = count($res);
					}
					else {
						$response['data'] = $res;
						$get_count_query = getCountSQLPH($query);
						$get_count_result = $this->db->query($get_count_query, $queryParams);

						if ( is_object($get_count_result) ) {
							$count = $get_count_result->result('array');
							$response['totalCount'] = $count[0]['cnt'];
						}
						else {
							return false;
						}
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			//echo getDebugSql($query, $queryParams); die();
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response = $result->result('array');
			}
			else {
				return false;
			}
		}

		return $response;
	}
}
