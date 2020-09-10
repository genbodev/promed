<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать направления на патологогистологическое исследование</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css">
	table.ct { width:100%; }
	table.ct td { border: none 1px black; vertical-align: top; }
	table.ctleft td { text-align:left; }
	table.ctcenter td { text-align:center; }
	table.ct td.small { width: 14px; }
	table.ct td.tleft { text-align: left; }
	table.ct td.dashed { border-bottom-style: dashed; vertical-align: bottom; }
</style>

<style type="text/css">
	.small { font-size: 8pt; }	
	div.selector { display:none; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }	
</style>
</head>

<body class="portrait">

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
	<tr>
		<td style="width: 40%; text-align: center; vertical-align: top;">
			<div>Министерство здравоохранения</div>
			<div style="margin-bottom: 1em;">СССР</div>
			<div>{Lpu_Name} {Lpu_Code}</div>
			<div style="border-top: 1px solid #000;" class="small">наименование учреждения</div>
		</td>
		<td style="width: 20%;">&nbsp;</td>
		<td style="width: 40%; vertical-align: top;">
			<div>Код формы по ОКУД _______________</div>
			<div>Код учреждения по ОКПО __________</div>
			<div>Медицинская документация</div>
			<div>Форма № 014/у</div>
			<div>Утверждена Минздравом СССР</div>
			<div>04.10.80 г. N 1030</div>
		</td>
	</tr>
</tbody>
</table>

<div style="text-align: center; margin: 1em 0em 1em 0em;">
	<div>НАПРАВЛЕНИЕ серия <span style="text-decoration: underline;">&nbsp;{EDH_Ser}&nbsp;</span> № <span style="text-decoration: underline;">&nbsp;{EDH_Num}&nbsp;</span> &lt;*&gt;</div>
	<div style="margin-bottom: 1em;">на патологогистологическое исследование</div>
	<div>"{EDH_Day}" <span style="text-decoration: underline;">&nbsp;&nbsp;{EDH_Month}&nbsp;&nbsp;</span> {EDH_Year}г. <span style="text-decoration: underline;">&nbsp;{EDH_Hour}&nbsp;</span>час.</div>
	<div class="small">(дата и часы направления материала)</div>
</div>

<div>--------------------------------</div>
<div style="margin-bottom: 1em;"><*> - Заполняется под  копирку  в  двух  экземплярах.  Необходимое  вписать, подчеркнуть.</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 15%;">Отделение</td>
	<td style="width: 85%; border-bottom: 1px solid #000;">{LpuSection_Name}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 55%;">Карта стационарного больного (амбулаторная карта) №</td>
	<td style="width: 45%; border-bottom: 1px solid #000;">{EDH_NumCard}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 40%;">1. Фамилия, имя, отчество больного</td>
	<td style="width: 60%; border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 20%;">2. Пол <span style="{Sex_1}">&nbsp;М&nbsp;</span>/<span style="{Sex_2}">&nbsp;Ж&nbsp;</span></td>
	<td style="width: 40%;">3. Возраст <span style="text-decoration: underline;">&nbsp;&nbsp;{Person_Age}&nbsp;&nbsp;</span> лет.</td>
	<td style="width: 40%;">4. Биопсия <span style="{BiopsyOrder_1}">первичная</span>, <span style="{BiopsyOrder_2}">вторичная</span></td>
</tr><tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td class="small">(нужное подчеркнуть).</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 55%;">5. При повторной биопсии указать № и дату первичной</td>
	<td style="width: 45%; border-bottom: 1px solid #000;">{EDH_BiopsyNum}&nbsp;&nbsp;&nbsp;{EDH_BiopsyDate}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 30%;">6. Дата и вид операции</td>
	<td style="width: 70%; border-bottom: 1px solid #000;">{EDH_didDate}&nbsp;&nbsp;&nbsp;{EDH_Operation}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 45%;">7. Маркировка материала, число объектов</td>
	<td style="width: 55%; border-bottom: 1px solid #000;">{EDH_Marking_1}</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_Marking_2}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 30%;">8. Клинические данные</td>
	<td style="width: 70%; border-bottom: 1px solid #000;">{EDH_ClinicalData_1}</td>
</tr><tr>
	<td>&nbsp;</td>
	<td class="small">(продолжительность заболевания, проведенное лечение</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_2}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">при опухолях - точная локализация, темпы  роста, размеры, консистенция,</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_3}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">отношение к окружающим тканям, метастазы, наличие других опухолевых узлов,</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_4}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">специальное лечение; при исследовании лимфоузлов указать анализ крови,</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_5}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">соскобов, эндометрия молочных желез - начало и окончание последней</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_6}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">менструации, характер нарушения менструальной функции, дату начала</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalData_7}</td>
</tr><tr>
	<td class="small" colspan="2" style="text-align: center;">кровотечения)</td>
</tr><tr>
	<td>9. Клинический диагноз</td>
	<td style="border-bottom: 1px solid #000;">{EDH_ClinicalDiag_1}</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalDiag_2}</td>
</tr><tr>
	<td style="border-bottom: 1px solid #000;" colspan="2">{EDH_ClinicalDiag_3}</td>
</tr></tbody>
</table>

<div style="margin-top: 2em; padding-right: 3em; text-align: right;">
	<div>Фамилия лечащего врача <span style="text-decoration: underline;">&nbsp;{MedPersonal_Fio}&nbsp;{MedPersonal_Snils}&nbsp;</span></div>
	<div style="margin-top: 1em;">Подпись _________________</div>
</div>

</body>

</html>