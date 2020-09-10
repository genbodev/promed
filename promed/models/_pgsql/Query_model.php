<?php
/**
* Query_model - временно
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/

class Query_model extends SwPgModel {
	/**
	 * Query_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает кол-во рецептов по ЛПУ за дату
	 */
	function getLpuReceptsCount($data)
	{
		echo "312423423";
		$d = date( "Y-m-d");
		/*$sql = "
			Select 
			
			RTrim(Lpu_Nick) as [Lpu_Nick],
			temp_LpuRecepts.Srednee as Srednee,
			Sum(case when convert(char(10),EvnRecept_insDT, 20) = '{$d}' then 1 else 0 end) as [Recept_Count], 
			Sum(case when convert(char(10),EvnRecept_insDT, 20) = '{$d}' and cast(convert(char(10),EvnRecept_insDT, 20) as datetime)<>EvnRecept_setDate then 1 else 0 end) as [Recept_DCount] 
			from temp_LpuRecepts with(nolock)
			left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = temp_LpuRecepts.Lpu_id
			left join v_EvnRecept EvnRecept with(nolock) on EvnRecept.Lpu_id = temp_LpuRecepts.Lpu_id and  convert(char(10),EvnRecept_insDT, 20) = '{$d}'
			where temp_LpuRecepts.Lpu_id is not null
			group by temp_LpuRecepts.Srednee, Lpu_Nick
		";*/
		$sql = "select null as \"NNN\"";
		$res = $this->db->query($sql);
		var_dump($res->result('array'));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
 
}
?>
