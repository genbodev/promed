<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать акта о списании медикаментов</title>
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
<table class="ct ctcenter">
	<tr>
		<td style="width:40%;">&nbsp;</td>
		<td>
			<table class="ct ctcenter" style="margin-top: 10px; margin-bottom:20px;">
				<tr>
					<td colspan="4" style="height:35px;">Утверждаю</td>
				</tr>
				<tr>
					<td class="tleft">Руководитель</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
					<td class="small" rowspan="2">&nbsp;</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
				</tr>
				<tr>		
					<td class="tleft">учреждения</td>
					<td>(подпись)</td>
					<td>(расшифровка<br/>подписи)</td>
				</tr>
				<tr>
					<td colspan="4" class="tleft">"__" _______________ 20__ г.</td>
				</tr>

			</table>
		</td>		
	</tr>
</table>	

<table class="mid">
	<tr>
		<td rowspan="7" style="width:35px;">&nbsp;</td><td colspan="3" style="vertical-align: top; height: 60px;">АКТ О СПИСАНИИ МАТЕРИАЛЬНЫХ ЗАПАСОВ</td>
	</tr>	
	<tr>
		<td colspan="2">&nbsp;</td>
		<td class="border" style="width: 70px;">КОДЫ</td>
	</tr>
	<tr>
		<td colspan="2" class="tright" style="padding-right:10px;">Форма по ОКУД</td>
		<td class="border">0504230</td>
	</tr>
	<tr>
		<td>от "__" ________ 20__ г.</td>
		<td class="tright" style="width:70px; padding-right:10px;">Дата</td>
		<td class="border">{Act_Date}</td>
	</tr>
	<tr>
		<td><table class="ct ctcenter"><tr><td class="tleft" style="width:95px;">Учреждение</td><td class="uline">{Org1}</td></tr></table></td>
		<td class="tright" style="padding-right:10px; padding-left:25px;">по&nbsp;ОКПО</td>
		<td class="border">&nbsp;</td>
	</tr>
	<tr>
		<td><table class="ct ctcenter"><tr><td class="tleft" style="width:205px;">Структурное подразделение</td><td class="uline">{Org2}</td></tr></table></td>
		<td>&nbsp;</td>
		<td class="border">&nbsp;</td>
	</tr>
	<tr>
		<td><table class="ct ctcenter"><tr><td class="tleft" style="width:245px;">Материально ответственное лицо</td><td class="uline">{Mol_Name}</td></tr></table></td>
		<td>&nbsp;</td>
		<td class="border">&nbsp;</td>
	</tr>
</table>

<table class="ct ctcenter" style="margin-top:15px;">
	<tr><td class="tleft" style="width: 150px;">Комиссия в составе</td><td class="uline" colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td>(должность, фамилия, И.О.)</td></tr>
	<tr><td class="uline" colspan="2">&nbsp;</td></tr>
	<tr><td class="uline" colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" class="tleft" style="padding-top: 3px;">
			назначенная приказом (распоряжением) от&nbsp;"__"&nbsp;_______&nbsp;20__ г.&nbsp;N __,
			произвела проверку выданных со склада в подразделения материальных
			запасов и установила фактическое расходование следующих материалов:
		</td>
	</tr>
</table>

<table class="mt ctcenter" style="margin-top: 30px;">
	<tr>
		<td colspan="2">Материальные запасы</td>
		<td rowspan="2">Единица измерения</td>
		<td rowspan="2">Норма расхода</td>
		<td colspan="3">Фактически израсходовано</td>
		<td rowspan="2">Направление расхода</td>
		<td colspan="2">Бухгалтерская запись</td>
	</tr>
	<tr>
		<td>наименование материала</td>
		<td>код</td>
		<td>колиество</td>
		<td>цена, руб.</td>
		<td>сумма, руб.</td>
		<td>дебет</td>
		<td>кредит</td>
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
		<td>9</td>
		<td>10</td>
	</tr>
	{table_body}	
</table>

<table class="ct" style="margin-top: 15px;">
	<tr>
		<td colspan="5">Всего по настоящему акту списано материалов на</td>
	</tr>
	<tr>
		<td>общую сумму</td>
		<td class="border" style="text-align:center;">{total_sum}</td>
		<td>&nbsp;</td>
		<td class="uline" style="text-align:center; vertical-align: bottom;">{total_sum_words}</td>
		<td style="padding-left: 5px; text-align: left; vertical-align: bottom;">руб.</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
		<td style="text-align: center;">(сумма прописью)</td>
		<td>&nbsp;</td>
	</tr>
</table>

<table class="ct">
	<tr><td style="width: 170px;">Заключение комиссии:</td><td class="uline">&nbsp;</td></tr>
	<tr><td class="uline" colspan="2">&nbsp;</td></tr>
	<tr><td class="uline" colspan="2">&nbsp;</td></tr>	
</table>

<table class="ct">
	<tr>
		<td style="width: 10%;">&nbsp;</td>
		<td>
			<table class="ct ctcenter" style="margin-top: 15px";>
				<tr>
					<td class="tleft">Председатель комиссии:</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
					<td class="small" rowspan="2">&nbsp;</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
					<td class="small" rowspan="2">&nbsp;</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
				</tr>
				<tr>		
					<td>&nbsp;</td>
					<td>(должность)</td>
					<td>(подпись)</td>
					<td>(расшифровка<br/>подписи)</td>
				</tr>
			</table>
			<table class="ct ctcenter" style="margin-top: 10px; margin-bottom:20px;">
				<tr>
					<td class="tleft">Члены комиссии:</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
					<td class="small" rowspan="2">&nbsp;</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
					<td class="small" rowspan="2">&nbsp;</td>
					<td class="uline" style="width:135px;">&nbsp;</td>
				</tr>
				<tr>		
					<td>&nbsp;</td>
					<td>(должность)</td>
					<td>(подпись)</td>
					<td>(расшифровка<br/>подписи)</td>
				</tr>
			</table>
			"__" _______________ 20__ г.
		</td>
	</tr>
</table>
</body>
</html>