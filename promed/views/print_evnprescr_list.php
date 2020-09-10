<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!--title>Лист назначений</title-->
<!--style type="text/css">
@media print {
	table {
		page-break-after: always;
	} 
}
</style-->
   <!--style type="text/css">
td.swcielnumeric { vertical-align: middle; text-align: center; padding: 2px; }
td.swciellevel0 { vertical-align: top; text-align: center; padding: 2px; }
td.swciellevel1 { vertical-align: top; text-align: center; padding: 2px; padding-left: 5px; }
td.swcieltitle { vertical-align: middle; text-align: center; padding: 5px; font-weight: bold; }
h2.swsectiontitle { text-align: center; margin: 10px; }
</style-->

<style type="text/css">
<!--
td.swvertext { /* Стиль текста */
	-moz-transform: rotate(270deg);
	-webkit-transform: rotate(270deg);
	-o-transform: rotate(270deg);
	text-align: center;
	font-weight: bold;
	height: 100px;
	width: 20px;
}
-->
</style>
<!--[if IE]>
<style type="text/css">
td.swvertext { /* Отдельные стили для IE */
	writing-mode:tb-rl;
	text-align: center;
	font-weight: bold;
	height: 100px;
	width: 20px;
}
</style><![endif]--> 
</head>

<body class="land" style="font-family: tahoma, verdana; font-size: 10pt; ">

<?php
$l = count($ep_list)-1;
$max_day = $ep_list[$l]['max_day'];
$EvnPrescr_begDate = $ep_list[$l]['EvnPrescr_begDate'];
unset($ep_list[$l]);
/**
 * Рекурсивная функция постраничной печати листа
 * @param array $rows строки
 * @param int $max_day Номер последнего дня
 * @param int $beg_day Номер дня, с которого должна начинаться страница листа
 * @param int $beg_time Дата (timestamp) дня, с которого должна начинаться страница листа
 */
function printNewPage($rows,$max_day,$beg_day,$beg_time) {
	$limit_day = 10;//число дней на странице
	$new_time = $beg_time;
	echo '
	<div style="text-align: left;"><p>Лист врачебных назначений &emsp; &#8470; {NumCard}</p></div>
	<div style="text-align: left;"><p><b>МО:</b> {Lpu_Name}</p></div>
	<div style="text-align: left;"><p><b>Отделение:</b> {LpuSection_Code} {LpuSection_Name}</p></div>
	<div style="text-align: left;"><p><b>Лечащий врач:</b> {MedPersonal_Fio}</p></div>
	<div style="margin-bottom: 1em; text-align: left;"><p><b>Пациент:</b> {Person_FIO} &emsp; {Person_Birthday}</p></div>
	<table cellspacing="0" cellpadding="2" border="1" bordercolor="#000000" width="100%" style="border-collapse: collapse;">
	<thead>
	<tr>
	<td class="swcieltitle" rowspan="2"><b>Назначения</b></td>
	<td class="swvertext" rowspan="2" style="text-align: center;"><b>Исполнит.</b></td>
	<td class="swvertext" rowspan="2" style="text-align: center;"><b>Дата</b></td>
	<td class="swcieltitle" colspan="10" style="text-align: center;"><b>Отметка о назначении и выполнении</b></td>
	</tr>
	<tr>';
	for($i=1;$i<=$limit_day;$i++) {
		echo '
		<td class="swcieltitleday">'.date('d.m.y',$new_time).'</td>';
		$new_time = strtotime('+'.$i.' day', $beg_time);
	}
	echo '
	</tr>
	</thead>
	<tbody>';
	$nursesArr = array();
	foreach($rows as $row) {
		//врач
		echo '
		<tr><td class="swciellevel0" rowspan="2">'.$row['EvnPrescr_Name'].'</td>
		<td class="swciellevel1" colspan="2" style="text-align: center; font-weight: bold;">врач</td>
		';
		for($i=$beg_day;$i<($beg_day+$limit_day);$i++) {
			echo '
			<td class="swciellevel1" style="text-align: center;">'.(empty($row['EvnPrescr_Day'.$i])?'&nbsp;':$row['EvnPrescr_Day'.$i]).'</td>';
		}
		echo '
		</tr>';
		//сестра
		echo '
		<tr>
		<td class="swciellevel1" colspan="2" style="text-align: center; font-weight: bold;">сестра</td>
		';
		for($i=$beg_day;$i<($beg_day+$limit_day);$i++) {
			echo '
			<td class="swciellevel1" style="text-align: center;">'.(empty($row['EvnPrescr_Day'.$i.'S'])?'&nbsp;':$row['EvnPrescr_Day'.$i.'S']).'</td>';
			if(!empty($row['EvnPrescr_Day'.$i.'S_FIO']) && !in_array($row['EvnPrescr_Day'.$i.'S_FIO'], $nursesArr)){
				$nursesArr[] =$row['EvnPrescr_Day'.$i.'S_FIO'];
			}
		}
		echo '
		</tr>';
	}
	echo '
	</tbody>
	</table>
	<!--div>i: '.$i.'; max_day: '.$max_day.'</div-->';
	
	if((count($nursesArr)>0)){
		$nurses = 'Список медсестер:<br>&emsp;';
		$nursesStr= $nurses.'';		

		$nursArr = array();
		foreach($nursesArr as $nurs){
			//$nursesStr .= "<p>&emsp;<b>".mb_substr($nurs, 0, 1)."</b> - ".$nurs."</p>";
			$nursArr[] = "<b>".mb_substr($nurs, 0, 1)."</b> - ".$nurs;
		}
		$nursesStr .= implode(', &nbsp;', $nursArr);
	}else{
		$nurses = '&nbsp;';
		$nursesStr = '&nbsp;';
	}
	
	echo '
	<table width="100%" style="margin-top: 20px;"><tr>
	<td width="50%" style="text-align: left;">'.$nursesStr.'</td>
	<td width="25%" style="text-align: right;">Подпись:</td>
	<td width="25%">___________________________</td>
	</tr></table>';
	if($i < ($max_day+1))
	{
		echo '
		<pagebreak />';
		printNewPage($rows,$max_day,$i,$new_time);
	}
}

printNewPage($ep_list,$max_day,1,strtotime($EvnPrescr_begDate));

?>

</body>

</html>