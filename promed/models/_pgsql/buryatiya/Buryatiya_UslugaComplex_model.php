<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Buryatiya_UslugaComplex_model - модель для комплексных услуг Бурятии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*/

require_once(APPPATH.'models/_pgsql/UslugaComplex_model.php');

class Buryatiya_UslugaComplex_model extends UslugaComplex_model {
	/**
	 * Конструктор
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
						'ucat' || cast(ucat.UslugaCategory_id as varchar(20)) as \"id\"
						,null as \"code\"
						,ucat.UslugaCategory_Name as \"name\"
						,'UslugaCategory' as \"object\"
						,ucat.UslugaCategory_id as \"UslugaCategory_id\"
						,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
						,null as UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
						,case when ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms', 'lpu') or uccgost.cnt > 0 then 0 else 1 end as \"leaf\"
					from
						v_UslugaCategory ucat
						left join lateral (
							select count(UslugaComplex_id) as cnt
							from v_UslugaComplex
							where UslugaCategory_id = ucat.UslugaCategory_id
								and UslugaCategory_SysNick in ('gost2011', 'gost2004', 'Kod7', 'simple', 'tfoms')
								and UslugaComplexLevel_id is not null
						) uccgost on true
					order by
						ucat.UslugaCategory_Code
				";
				break;

			case 1:
				switch ( $data['UslugaCategory_SysNick'] ) {
					case 'pskov_foms':
						$query = "
							select
								'ucom' || cast(uc.UslugaComplex_id as varchar(20)) as \"id\"
								,null as \"code\"
								,uc.UslugaComplex_Name as \"name\"
								,'UslugaComplex' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,null as UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,1 as \"leaf\"
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
							where
								uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid is null
							order by
								uc.UslugaComplex_Name
						";
						break;

					case 'lpu':
						$query = "
							select
								'lpu' || cast(Lpu_id as varchar(20)) as \"id\"
								,null as \"code\"
								,Lpu_Nick as \"name\"
								,'Lpu' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,null as UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,1 as \"leaf\"
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
								'ucom' || cast(uc.UslugaComplex_id as varchar(20)) as \"id\"
								,cast(uc.UslugaComplex_Code as varchar(50)) as \"code\"
								,uc.UslugaComplex_Name as \"name\"
								,'UslugaComplex' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when uc.UslugaComplexLevel_id = 1 or ucc.cnt = 0 then 1 else 0 end as \"leaf\"
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral (
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
					case 'simple':
					case 'tfoms':
						$query = "
							select
								'ucom' || cast(uc.UslugaComplex_id as varchar(20)) as \"id\"
								,cast(uc.UslugaComplex_Code as varchar(50)) as \"code\"
								,uc.UslugaComplex_Name as \"name\"
								,'UslugaComplex' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when ucc.cnt = 0 then 1 else 0 end as \"leaf\"
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id = 1
								) ucc on true
							where
								uc.UslugaComplexLevel_id = 1
								and uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid IS NULL
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
								'ucom' || cast(uc.UslugaComplex_id as varchar(20)) as \"id\"
								,cast(uc.UslugaComplex_Code as varchar(50)) as \"code\"
								,uc.UslugaComplex_Name as \"name\"
								,'UslugaComplex' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when ucc.cnt = 0 then 1 else 0 end as \"leaf\"
							from
								v_UslugaComplex uc
								inner join v_UslugaComplex ucp on ucp.UslugaComplex_id = uc.UslugaComplex_pid
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral (
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
					case 'simple':
					case 'tfoms':
						$query = "
							select
								'ucom' || cast(uc.UslugaComplex_id as varchar(20)) as \"id\"
								,cast(uc.UslugaComplex_Code as varchar(50)) as \"code\"
								,uc.UslugaComplex_Name as \"name\"
								,'UslugaComplex' as \"object\"
								,ucat.UslugaCategory_id as \"UslugaCategory_id\"
								,ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
								,uc.UslugaComplexLevel_id as \"UslugaComplexLevel_id\"
								,case when ucc.cnt = 0 then 1 else 0 end as \"leaf\"
							from
								v_UslugaComplex uc
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = :UslugaCategory_id
								left join lateral (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id = 1
								) ucc on true
							where
								uc.UslugaComplexLevel_id = 1
								and uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid = :UslugaComplex_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
						break;
				}
				break;
		}

		//echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
