<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать протокола патоморфогистологического исследования</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td, th { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td, th { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
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
			<div>{Lpu_Name}</div>
			<div style="border-top: 1px solid #000;" class="small">наименование учреждения</div>
		</td>
		<td style="width: 20%;">&nbsp;</td>
		<td style="width: 40%; vertical-align: top;">
			<div>Код формы по ОКУД _______________</div>
			<div>Код учреждения по ОКПО __________</div>
			<div>Медицинская документация</div>
			<div>Форма № 013/у</div>
			<div>Утверждена Минздравом СССР</div>
			<div>04.10.80 г. N 1030</div>
		</td>
	</tr>
</tbody>
</table>

<div style="margin: 1em 0em 1em 0em; text-align: center;">
	<div>ПРОТОКОЛ (карта)</div>
	<div style="margin-bottom: 1em;">патологоанатомического исследования N <span style="text-decoration: underline;">{EMHP_Ser} {EMHP_Num}</span></div>
	<div>"{EMHP_Day}" <span style="text-decoration: underline;">&nbsp;&nbsp;{EMHP_Month}&nbsp;&nbsp;</span> {EMHP_Year}г.</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 45%;">Адрес учреждения, составившего протокол</td>
	<td style="width: 55%; border-bottom: 1px solid #000;">{Lpu_Address}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 25%;">Республика (обл., край)</td>
	<td style="width: 25%; border-bottom: 1px solid #000;">{KLRgn_Name}</td>
	<td style="width: 20%; text-align: center;">Район (город)</td>
	<td style="width: 30%; border-bottom: 1px solid #000;">{KLCityTown_Name}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 15%;">Больница</td>
	<td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
	<td style="width: 15%; text-align: center;">отделение</td>
	<td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
	<td style="width: 20%; text-align: center;">Карта больного N</td>
	<td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_NumCard}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 5%;">1.</td>
	<td style="width: 25%;">Фамилия, имя, отчество</td>
	<td style="width: 70%; border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 5%;">2.</td>
	<td style="width: 10%;"><span style="{Sex_Code_1}">М</span>/<span style="{Sex_Code_0}">Ж</span></td>
	<td style="width: 85%;">3. Возраст <span style="text-decoration: underline;">&nbsp;&nbsp;{Person_Age}&nbsp;&nbsp;</span> (лет).</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 5%;">4.</td>
	<td style="width: 25%;">Место жительства</td>
	<td style="width: 70%; border-bottom: 1px solid #000;">{Person_PAddress}</td>
</tr><tr>
	<td style="width: 5%;">5.</td>
	<td style="width: 25%;">Профессия (до пенсии)</td>
	<td style="width: 70%; border-bottom: 1px solid #000;">{Post_Name}</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 5%;">6.</td>
	<td style="width: 95%;">Доставлен в  больницу  через <span style="text-decoration: underline;">______</span> часов (дней) после начала заболевания.</td>
</tr>
<tr>
	<td style="width: 5%;">7.</td>
	<td style="width: 95%;">Проведено <span style="text-decoration: underline;">______</span> койко/дней.</td>
</tr>
<tr>
	<td style="width: 5%;">8.</td>
	<td style="width: 95%;">Дата смерти <span style="text-decoration: underline;">{Person_Death_Date} {Person_Death_Time}</span> год, мес., число, час.</td>
</tr>
<tr>
	<td style="width: 5%;">9.</td>
	<td style="width: 95%;">Дата вскрытия <span style="text-decoration: underline;">{EMHP_Day} {EMHP_Month} {EMHP_Year}</span> год, мес., число, час.</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">10.</td>
	<td style="width: 15%;">Лечащий врач </td>
	<td style="width: 80%; border-bottom: 1px solid #000;">{MedPersonal_Fio}</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">11.</td>
	<td style="width: 30%;">Присутствовали на вскрытии: </td>
	<td style="width: 65%; border-bottom: 1px solid #000;"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;">{MedPersonal_Attended}</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: none; padding-right: 10em; text-align:right;">Коды:         </td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">12.</td>
	<td style="width: 35%;">Диагноз направившего учреждения: </td>
	<td style="width: 60%; border-bottom: 1px solid #000;">{Diag_Direction} {EMHP_DiagNameDirect}</td>
</tr>
<tr>
	
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">13.</td>
	<td style="width: 30%;">Диагноз при поступлении: </td>
	<td style="width: 65%; border-bottom: 1px solid #000;">{Diag_Income} {EMHP_DiagNameSupply}</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">14.</td>
	<td style="width: 60%;">Клинические диагнозы в стационаре и даты их установления: </td>
	<td style="width: 35%; border-bottom: 1px solid #000;"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;height:3em;">15.</td>
	<td style="width: 95%;">Заключительный диагноз и дата его установления (основное  заболевание, осложнения, сопутствующие заболевания): </td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em; margin-bottom: 1.5em"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none; margin-top: 1em;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;">16.</td>
	<td style="width: 50%;">Результаты клинико-лабораторных исследований: </td>
	<td style="width: 45%; border-bottom: 1px solid #000;"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;">{EMHP_ResultLabStudy}</td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em; margin-bottom: 1.5em"></td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 5%;height:3em;">17.</td>
	<td style="width: 95%;">Патологоанатомический диагноз (основное заболевание,осложнения,сопутствующие заболевания):</td>
</tr>
</tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;">{EMHP_DiagPathology}</td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em;"></td>
</tr>
<tr>
	<td style="width: 100%; border-bottom: 1px solid #000; height: 1.5em; margin-bottom: 1.5em"></td>
</tr>
</tbody>
</table>

<div style="margin: 1em; text-align: right;">
	<div>Для типографии!</div>
	<div>при изготовлении документа</div>
	<div>формат А4</div>
</div>

<div style="page-break-after: always;"></div>


<div style="margin-bottom: 1em;">Стр. 2 ф. 013/у</div>

<div>18. Ошибки клинической диагностики (подчеркнуть, вписать)</div>

<div style="margin: 1em; text-align: center;">Причины расхождения диагнозов</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
	<tr>
		<td style="width: 50%; vertical-align: top; border-right: 1px solid #000;">
			<div>Расхождение диагнозов по основному заболеванию <span style="text-decoration: underline;">{ByUnderlyingDisease}______________________</span></div>
			<div>по осложнениям <span style="text-decoration: underline;">{ByComplications}______________________</span></div>
			<div>по сопутствующим заболеваниям <span style="text-decoration: underline;">{ByConcomitantDiseases}______________________</span></div>
		</td>
		<td style="width: 50%; vertical-align: top;">
			<div>Запоздалая диагностика основного заболевания <span style="text-decoration: underline;">______________________</span></div>
			<div>смертельного осложнения <span style="text-decoration: underline;">______________________</span></div>
		</td>
	</tr>
</tbody>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 1em;" cellspacing="0" cellpadding="2">
<tbody>
	<tr>
		<th style="width: 16%; border: 1px solid #000;">Объективные трудности диагностики</th>
		<th style="width: 16%; border: 1px solid #000;">Кратковременное пребывание</th>
		<th style="width: 20%; border: 1px solid #000;">Недообследование больного</th>
		<th style="width: 16%; border: 1px solid #000;">Переоценка данных обследования</th>
		<th style="width: 16%; border: 1px solid #000;">Редкость заболевания</th>
		<th style="width: 16%; border: 1px solid #000;">Неправильное оформление диагноза</th>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
	<tr>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
</tbody>
</table>

<div style="margin-top: 1em;">19. Причина смерти (во врачебном свидетельстве о смерти N <span style="text-decoration: underline">&nbsp;&nbsp;{EMHP_NumDeath}&nbsp;&nbsp;</span> сделана следующая запись):</div>
<div>Коды:</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody>
	<tr>
		<td style="width: 15%; border-bottom: 1px solid #000; text-align: center; vertical-align: top;">{DiagV_Code}</td>
		<td style="width: 85%; vertical-align: top;">I. а) {DiagV_Descr}</td>
	</tr>
	<tr>
		<td style="border-bottom: 1px solid #000; text-align: center; vertical-align: top;">{DiagW_Code}</td>
		<td style="vertical-align: top;">б) {DiagW_Descr}</td>
	</tr>
	<tr>
		<td style="border-bottom: 1px solid #000; text-align: center; vertical-align: top;">{DiagX_Code}</td>
		<td style="vertical-align: top;">в) {DiagX_Descr}</td>
	</tr>
	<tr>
		<td style="border-bottom: 1px solid #000; text-align: center; vertical-align: top;">{DiagY_Code}</td>
		<td style="vertical-align: top;">г) {DiagY_Descr}</td>
	</tr>
	<tr>
		<td style="border-bottom: 1px solid #000; text-align: center; vertical-align: top;">{DiagZ_Code}</td>
		<td style="vertical-align: top;">II. {DiagZ_Descr}</td>
	</tr>
</tbody>
</table>

<div style="margin-top: 1em;">20. Клиникопатологоанатомический эпикриз.</div>
<div style="margin-bottom: 2em;">{EMHP_Epicrisis}</div>

<div style="margin-bottom: 1em;">Протокольная часть на _________ страницах прилагается.</div>
<div style="margin-bottom: 1em;">Фамилия патологоанатома <span style="text-decoration: underline;">&nbsp;&nbsp;{MedPersonal_Fio}&nbsp;&nbsp;</span> &nbsp;&nbsp; Подпись _________________</div>
<div style="margin-bottom: 1em;">Заведующий отделением <span style="text-decoration: underline;">&nbsp;&nbsp;{MedPersonal_FioZ}&nbsp;&nbsp;</span> &nbsp;&nbsp; Подпись _________________</div>
<div>Заполняется под  копирку  в  3-х  экземплярах  (первый - протокол,  второй - подшивается к карте больного, третий - секционная карта).</div>

<div style="page-break-after: always;"></div>


<div style="margin-bottom: 1em; text-align: right;">Стр. 3 ф. 013/у</div>

<div style="margin: 1em; text-align: center;">
	<div>Продолжение протокола патологоанатомического исследования N <span style="text-decoration: underline;">{EMHP_Ser} {EMHP_Num}</span></div>
	<div style="margin: 1em;">от "{EMHP_Day}" <span style="text-decoration: underline;">&nbsp;&nbsp;{EMHP_Month}&nbsp;&nbsp;</span> {EMHP_Year}г.</div>
	<div>РЕЗУЛЬТАТЫ ПАТОЛОГОАНАТОМИЧЕСКОГО ИССЛЕДОВАНИЯ</div>
</div>

<table style="width: 100%; border-collapse: collapse;" cellspacing="0" cellpadding="2">
<tbody>
	<tr>
		<th style="width: 10%; border: 1px solid #000;" rowspan="2">Рост</th>
		<th style="width: 10%; border: 1px solid #000;" rowspan="2">Вес тела</th>
		<th style="border: 1px solid #000;" colspan="8">Вес органов</th>
	</tr>
	<tr>
		<th style="width: 10%; border: 1px solid #000;">мозг</th>
		<th style="width: 10%; border: 1px solid #000;">сердце</th>
		<th style="width: 10%; border: 1px solid #000;">легкие</th>
		<th style="width: 10%; border: 1px solid #000;">печень</th>
		<th style="width: 10%; border: 1px solid #000;">селезенка</th>
		<th style="width: 20%; border: 1px solid #000;">почки левая/правая</th>
		<th style="width: 5%; border: 1px solid #000;">&nbsp;</th>
		<th style="width: 5%; border: 1px solid #000;">&nbsp;</th>
	</tr>
	<tr style="text-align: center;">
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">{EMHP_BrainWeight}</td>
		<td style="border: 1px solid #000;">{EMHP_HeartWeight}</td>
		<td style="border: 1px solid #000;">{EMHP_LungsWeight}</td>
		<td style="border: 1px solid #000;">{EMHP_LiverWeight}</td>
		<td style="border: 1px solid #000;">{EMHP_SpleenWeight}</td>
		<td style="border: 1px solid #000;">{EMHP_KidneyLeftWeight} / {EMHP_KidneyRightWeight}</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
		<td style="border: 1px solid #000;">&nbsp;</td>
	</tr>
</tbody>
</table>

<div style="margin: 1em;">
	<div>Взято кусочков для патологоанатомического исследования <span style="text-decoration: underline;">&nbsp;&nbsp;{EMHP_BitCount}&nbsp;&nbsp;</span></div>
	<div>Изготовлено блоков <span style="text-decoration: underline;">&nbsp;&nbsp;{EMHP_BlockCount}&nbsp;&nbsp;</span></div>
	<div>Взят материал для других методов исследования: <span style="text-decoration: underline;">&nbsp;&nbsp;{EMHP_MethodDescr}&nbsp;&nbsp;</span></div>
</div>

<div style="margin: 1em; text-align: center;">
	<div style="margin-bottom: 1em;">Текст протокола</div>
	<div>Заполняется в одном экземпляре. Схемы и фотоснимки прилагаются.</div>
</div>

<div>{EMHP_Protocol}</div>

<div style="page-break-after: always;"></div>


<div style="margin-bottom: 5em;">Стр. 4 ф. 013/у</div>

<div style="padding-left: 3em;">
	<div>Результаты гистологического исследования:</div>
	<div style="margin-bottom: 2em;">{EvnHistologicProtoData}</div>
	<div>Приложение на ______ листах.</div>
	<div>Схемы, таблицы, фото, рис. (сколько) _____________________</div>
	<div>Дата обсуждения на конференции ___________________________</div>
</div>

<div style="margin-top: 2em; padding-right: 3em; text-align: right;">
	<div>Фамилия патологоанатома <span style="text-decoration: underline;">&nbsp;&nbsp;{MedPersonal_Fio}&nbsp;&nbsp;</span></div>
	<div style="margin-bottom: 1em; margin-top: 1em;">Подпись _________________</div>
</div>

</body>

</html>