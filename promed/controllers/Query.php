<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Query - временно
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
  */
class Query extends swController {
	/**
	 * Query constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return mixed
	 */
	function GetRealIp()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * @return bool
	 */
	function Index() {
		return false;
	}

	/**
	 * Получение информации о конкретном пользователе
	 */
	function LpuReceptsCount() 
	{
		$ip =  $this->GetRealIp();
		if (true /*substr($ip,0,9) == '172.19.61'*/)
		{
			$data = $_REQUEST;
			$d = date( "d.m.Y");
			$this->load->database();
			
			$sql = "
				select * from v_Lpu where Org_Code = :Person_id
			";
			$data = array();
			$data['Person_id'] = NULL;
			//$res = $this->db->query($sql, array('Person_id' => $data['Person_id']));
			//die();
			$this->load->model("Query_model", "qmodel");
			$dList = $this->qmodel->getLpuReceptsCount($data);
			$val = array();
			if ( $dList != false && count($dList) > 0 )
			{
				echo "<table border=1>";
				echo "<tr><td><b>ЛПУ</b></td><td><b>Средняя выписка</b></td><td><b>Кол-во рецептов (где EvnRecept_insDT={$d}), всего </b></td><td><b>В % к средней</b></td><td><b>Из них, кол-во рецептов (где EvnRecept_setDate<>EvnRecept_insDT)</b></td></tr>";
				$s = 0;
				$s1 = 0;
				$i = 0;
				$kn = 0;
				$sr = 0;
				foreach ( $dList as $rows )
				{
					$i++;
					if ((!empty($rows['Srednee'])) && ($rows['Srednee']>0)) 
						$proc = Round($rows['Recept_Count']*100/$rows['Srednee'],2);
					else
						$proc = 0;
					if ($proc>100) 
						$proc_style = "style = \"text-weight: bold; color: green;\"";
					elseif ($proc==0)  
					{
						$kn++;
						$proc_style = "style = \"background-color:#f1f1f1; text-weight: bold; color: red;\"";
					}
					else 
						$proc_style = "style = \"color: #333;\"";
					echo "<tr><td>".trim($rows['Lpu_Nick'])."</td><td>".trim(Round($rows['Srednee']))."&nbsp;</td><td>".trim($rows['Recept_Count'])."&nbsp;</td><td {$proc_style}>".$proc."%&nbsp;</td><td>".trim($rows['Recept_DCount'])."&nbsp;</td></tr>";
					$s = $s+$rows['Recept_Count'];
					$s1 = $s1+$rows['Recept_DCount'];
					$sr = $sr+$rows['Srednee'];
				}
				if ($sr>0)
						$proc = Round($s*100/$sr,2);
					else
						$proc = 0;
				echo "<tr><td><b>Всего ({$i}) (- {$kn})</b></td><td><b>&nbsp;".Round($sr,2)."</b></td><td><b>{$s}</b></td><td><b>{$proc}&nbsp;</b></td><td><b>{$s1}</b></td></tr>";
				echo "</table>";
			}
			else
				echo "В какой-то момент времени что-то пошло не так";
		}
	}
}
?>
