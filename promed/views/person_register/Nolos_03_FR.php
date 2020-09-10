<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Форма № 03-ФР</title>
<style type="text/css">
body { font-family: "Times New Roman", serif; font-size: 9pt; margin: 0; padding: 0; }
table { border-collapse: collapse; border-spacing: 0; width: 100%; }
td.cell-head { vertical-align: top; border: 1px solid #000; text-align: center; font-size: 9pt;  }
td.cell-data { vertical-align: middle; border: 1px solid #000; text-align: center; font-size: 9pt;  }
</style>
</head>

<body>

<p style="float: right; text-align: left;">
	<span style="font-size:7pt;">
		Приложение № 4<br />
		к приказу Министерства здравоохранения Российской Федерации<br />
		от 15.02.2013 № 69н</span><br />
</p>
<div style="clear: both"></div>
<p style="font-size:10pt; font-weight: bold; text-align: right;">Форма № 03-ФР</p>
<br />
<?php
$Lpu_Name = '';
foreach ($item_arr as $row) {
	if (isset($row['Lpu_Name'])) {
		$Lpu_Name = $row['Lpu_Name'];
		break;
	}
}
?>
<p style="text-align: center;">
	<b>
		ЖУРНАЛ<br />
		учета выдачи направлений на включение сведений (внесение изменений в сведения) о больном в Федеральном регистре лиц, больных гемофилией, муковисцидозом,<br />
		гипофизарным нанизмом, болезнью Гоше, злокачественными новообразованиями лимфоидной, кроветворной и родственных им тканей, рассеянным склерозом,<br />
		лиц после трансплантации органов и (или) тканей, и выдачи извещений об исключении сведений из данного Федерального регистра *
	</b><br />
</p>
<div style="text-align: center">
	<b><?php echo $Lpu_Name; ?></b>
	<hr align="center" size="1" width="100%" style="margin: 0.2em" />
	<span style="text-align: center; font-size:7pt;">(наименование медицинской организации субъекта Российской Федерации, медицинской организации<br />
	муниципальной системы здравоохранения, медицинской организации, подведомственной ФСИН России<br />
	или ФМБА России)
	</span>
	</div>
<br />
<br />

<table>
	<tbody>
		<tr>
			<td class="cell-head">
				№<br />
				п/п</td>
			<td class="cell-head">
				Дата</td>
			<td class="cell-head">
				Ф.И.О.<br />
				больного</td>
			<td class="cell-head">
				Выданный<br />
				документ<br />
				(направление<br />
				или извещение)</td>
			<td class="cell-head">
				Номер<br />
				направления<br />
				(извещения)</td>
			<td class="cell-head">
				Код или номер<br />
				медицинской карты<br />
				амбулаторного<br />
				больного<br />
				(истории развития<br />
				ребенка)
			<td class="cell-head">
				Ф.И.О. врача,<br />
				выдавшего<br />
				направление<br />
				(извещение)</td>
			<td class="cell-head">
				Ф.И.О. секретаря<br />
				врачебной<br />
				комиссии,<br />
				оформившего<br />
				запись</td>
			<td class="cell-head">
				Ф.И.О. председателя<br />
				врачебной комиссии<br />
				медицинской организации,<br />
				выдавшего направление<br />
				(извещение)</td>
		</tr>
	<?php
	$num = 0;
	foreach ($item_arr as $row) {
		$num++;
		?>
		<tr>
			<td class="cell-data"><?php echo $num; ?></td>
			<td class="cell-data"><?php echo $row['EvnNotifyRegister_setDate']; ?></td>
			<td class="cell-data"><?php
				if (!empty($row['Person_SurName_p'])) {
					echo $row['Person_SurName_p'];
					if (!empty($row['Person_FirName_p'])) {
						echo ' ' . mb_substr($row['Person_FirName_p'],0,1) . '.';
					}
					if (!empty($row['Person_SecName_p'])) {
						echo mb_substr($row['Person_SecName_p'],0,1) . '.';
					}
				} else {
					echo '&nbsp;';
				}
			?></td>
			<td class="cell-data"><?php echo (3 == $row['NotifyType_id']) ? 'извещение' : 'направление'; ?></td>
			<td class="cell-data"><?php echo $row['EvnNotifyRegister_Num']; ?></td>
			<td class="cell-data"><?php echo $row['PersonCard_Code']; ?></td>
			<td class="cell-data"><?php 
				if (!empty($row['Person_SurName_m'])) {
					echo $row['Person_SurName_m'];
					if (!empty($row['Person_FirName_m'])) {
						echo ' ' . mb_substr($row['Person_FirName_m'],0,1) . '.';
					}
					if (!empty($row['Person_SecName_m'])) {
						echo mb_substr($row['Person_SecName_m'],0,1) . '.';
					}
				} else {
					echo '&nbsp;';
				}
			?></td>
			<td class="cell-data"><?php 
				if (!empty($row['Person_SurName_s'])) {
					echo $row['Person_SurName_s'];
					if (!empty($row['Person_FirName_s'])) {
						echo ' ' . mb_substr($row['Person_FirName_s'],0,1) . '.';
					}
					if (!empty($row['Person_SecName_s'])) {
						echo mb_substr($row['Person_SecName_s'],0,1) . '.';
					}
				} else {
					echo '&nbsp;';
				}
			?></td>
			<td class="cell-data"><?php 
				if (!empty($row['Person_SurName_v'])) {
					echo $row['Person_SurName_v'];
					if (!empty($row['Person_FirName_v'])) {
						echo ' ' . mb_substr($row['Person_FirName_v'],0,1) . '.';
					}
					if (!empty($row['Person_SecName_v'])) {
						echo mb_substr($row['Person_SecName_v'],0,1) . '.';
					}
				} else {
					echo '&nbsp;';
				}
			?></td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
<br />
<div>
	<hr align="left" size="1" width="33%" />
	<span style="font-size:7pt;">* Журнал прошнуровывается, пронумеровывается, на титульном листе отмечаются даты начала и окончания ведения журнала, заверяется подписью руководителя медицинской организации.</span>
</div>

</body></html>
