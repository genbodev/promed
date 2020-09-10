<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать оснащенности компьютерным оборудованием</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family:  'Times New Roman', Times, serif; }
	td { vertical-align: middle; border: none; }
	.noprint { display: auto; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family:  'Times New Roman', Times, serif; }
	td { vertical-align: middle; border: none; }
	.noprint { display: none; }
</style>

<style type="text/css">
	table.ct { width:100%; }
	table.mid td { vertical-allign: middle important; }
	table.ct td { border: none 1px black; vertical-align: top; }
	table.ct td.uline { border-bottom: 1px solid black; }	
	table.ctleft td { text-align:left; }
	table.ctcenter td { text-align:center; }
	table.ct td.small { width: 14px; }
	table.ct td.tleft { text-align: left; }
	table.ct td.tright { text-align: right; }
	table.ct td.dashed { border-bottom-style: dashed; vertical-align: bottom; }
	table.ct td.border { border: 1px solid black; vertical-align: middle; }
</style>

<style type="text/css">
	table.mt { width:100%; }
	table.mt td { border: solid 1px black; text-align:center; vertical-align:middle; }
	table.mt td.tleft { text-align:left; }
	table.mt tr.header td { }
	
	td.border { border: 1px solid black; vertical-align: middle; height:35px; width: 90px; }
	table.mid { width:100%; }
	table.mid td { text-align: center; vertical-align: middle; }
	table.mid td.tleft { text-align: left; }
	table.mid td.tright { text-align: right; }
</style>

</head>

<body class="portrait">

<table class="mt ctcenter" >
	<tr><td style="text-align: left; border: 0px;">(7000)</td>
		<td colspan="9" style="text-align: right; border: 0px;">Код по ОКЕИ: штука - 796</td>
	</tr>
	<tr>
		<td rowspan="3">Наименование устройств</td>
		<td rowspan="3">№ строки</td>
		<td rowspan="3">Всего</td>
		<td colspan="5">в том числе (из гр. 3)</td>
	</tr>
	<tr>
		<td colspan="2">для административно-хозяйственной деятельности организации</td>
		<td colspan="2">для медицинского персонала (для автоматизации лечебного процесса)</td>
		<td rowspan="2">прочие</td>
	</tr>
	<tr>
		<td>в подразделениях, оказывающих медицинскую помощь в амбулаторных условиях</td>
		<td>в подразделениях, оказывающих медицинскую помощь в стационарных условиях</td>
		<td>в подразделениях, оказывающих медицинскую помощь в амбулаторных условиях</td>
		<td>в подразделениях, оказывающих медицинскую помощь в стационарных условиях</td>
	</tr>
	<tr>
		<td>1</td>
		<td>2</td>
		<td>3</td>
		<td>4</td>
		<td>5</td>
		<td>6</td>
		<td>7</td>
		<td>8</td>
	</tr>

	<?php



		foreach ($categories as $cat) {

			// исключаем высокоскоростные каналы
			if ($cat->Device_Code != '11') {

				echo '<tr><td style="text-align: left;">'
					. $cat->Device_Name . '</td>' .
					'<td>' . $cat->Device_Code . '</td>';

				if (isset($cat->Total)) {

					echo '<td>' . $cat->Total . '</td>' .
						'<td>' . $cat->ComputerEquip_AHDAmb . '</td>' .
						'<td>' . $cat->ComputerEquip_AHDStac . '</td>' .
						'<td>' . $cat->ComputerEquip_MedPAmb . '</td>' .
						'<td>' . $cat->ComputerEquip_MedPStac . '</td>' .
						'<td>' . $cat->ComputerEquip_other . '</td>';
				} else
					echo '<td></td><td></td><td></td><td></td><td></td><td></td>';

				echo '</tr>';
			}
		}
	?>

</table>
<br><br>

<table class="ct">
	<tr><td style="text-align: left; border: 0px;">(7001)</td>
		<td style="text-align: right; border: 0px;">Код по ОКЕИ: единица - 642</td>
	</tr>
	<tr><td colspan="2"></td>
	<tr>
		<td colspan="2">
			Число  кабинетов  медицинской статистики, имеющих доступ к
			высокоскоростным каналам  передачи  данных
			<span style="text-decoration: underline">
				<?php echo isset($medstatcabs['11']) ? $medstatcabs['11']->ComputerEquip_MedStatCab : '0'; ?></span>, в  том числе к сети Интернет по типам
			подключения:  коммутируемый  (модемный)
			<span style="text-decoration: underline">
				<?php echo isset($medstatcabs['9.1']) ? $medstatcabs['9.1']->ComputerEquip_MedStatCab : '0'; ?></span>;
			широкополосный доступ по
			технологии xDSL
			<span style="text-decoration: underline">
				<?php echo isset($medstatcabs['9.2']) ? $medstatcabs['9.2']->ComputerEquip_MedStatCab : '0'; ?></span>;
			VPN через сеть общего пользования
			<span style="text-decoration: underline">
				<?php echo isset($medstatcabs['9.6']) ? $medstatcabs['9.6']->ComputerEquip_MedStatCab : '0'; ?></span>.
		</td>
	</tr>
</table>

</body>
</html>