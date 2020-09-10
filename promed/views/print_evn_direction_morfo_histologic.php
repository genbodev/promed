<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать направления на патоморфогистологическое исследование</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 8pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 8pt; }
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
		<td style="width: 50%; vertical-align: top;">
			<div>Министерство здравоохранения и социального</div>
			<div>развития Российской Федерации</div>
			<div style="text-align: center;">
				<div style="padding-top: 0.5em;">{Lpu_Name} {Lpu_Code}</div>
				<div style="border-top: 1px solid #000;" class="small">(наименование лечебно-профилактического учреждения)</div>
				<div style="padding-top: 0.5em;">{Lpu_Address}</div>
				<div style="border-top: 1px solid #000;" class="small">(адрес, телефон)</div>
				<div style="padding-top: 0.5em;">{Lpu_OGRN}</div>
				<div style="border-top: 1px solid #000;" class="small">(ОГРН)</div>
			</div>
		</td>
		<td style="width: 50%;">&nbsp;</td>
	</tr>
</tbody>
</table>

<div style="text-align: center; margin: 2em 0em 2em 0em;">
	<div>НАПРАВЛЕНИЕ № {EvnDirectionMorfoHistologic_Ser} {EvnDirectionMorfoHistologic_Num}</div>
	<div style="font-weight: bold; text-decoration: underline;">на патоморфогистологическое исследование трупа</div>
	<div>в {OrgAnatom_Name}</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">1.</td>
	<td style="width: 25%;">Фамилия, имя, отчество</td>
	<td style="width: 71%; border-bottom: 1px solid #000;">{Person_Surname} {Person_Firname} {Person_Secname}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">2.</td>
	<td style="width: 15%;">Дата рождения</td>
	<td style="width: 81%; border-bottom: 1px solid #000;">{Person_Birthday}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">3.</td>
	<td style="width: 35%;">Адрес постоянного места жительства</td>
	<td style="width: 61%; border-bottom: 1px solid #000;">{Person_Address}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">4.</td>
	<td style="width: 25%;">Страховой полис ОМС (ДМС)</td>
	<td style="width: 71%; border-bottom: 1px solid #000;">{Polis_Ser} {Polis_Num} {Polis_begDate}</td>
</tr><tr>
	<td>&nbsp;</td>
	<td>Страховая компания</td>
	<td style="border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">5.</td>
	<td style="width: 15%;">Профиль</td>
	<td style="border-bottom: 1px solid #000;" colspan="5">{LpuSectionProfile_Name}</td>
</tr><tr>
	<td>&nbsp;</td>
	<td>Отделение</td>
	<td style="width: 30%; border-bottom: 1px solid #000;">{LpuSection_Name}</td>
	<td style="width: 20%; text-align: right;">Контактный телефон</td>
	<td style="width: 30%; border-bottom: 1px solid #000;">{EvnDirectionMorfoHistologic_Phone}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">6.</td>
	<td style="width: 20%;">Дата и время смерти</td>
	<td style="width: 76%;"><span style="border-bottom: 1px solid #000;">{deathHours} час. {deathMinutes} мин. {deathDate}г.</span></td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">7.</td>
	<td style="width: 20%;">Тип госпитализации</td>
	<td style="width: 76%;">{PrehospType_Name}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">8.</td>
	<td style="width: 20%;">Сроки лечения</td>
	<td style="width: 76%;"><span style="border-bottom: 1px solid #000;">{EvnPS_dateRange}</span></td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">9.</td>
	<td style="width: 20%;">Код диагноза МКБ:</td>
	<td style="width: 12%; text-align: right;">основной</td>
	<td style="width: 10%; border-bottom: 1px solid #000;">{DiagOsn_Code}</td>
	<td style="width: 14%; text-align: right;">осложнение</td>
	<td style="width: 10%; border-bottom: 1px solid #000;">{DiagOsl_Code}</td>
	<td style="width: 21%; text-align: right;">сопутствующий</td>
	<td style="width: 10%; border-bottom: 1px solid #000;">{DiagSop_Code}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">10.</td>
	<td style="width: 25%;">Обоснование направления</td>
	<td style="width: 71%; border-bottom: 1px solid #000;">&nbsp;{EvnDirectionMorfoHistologic_Descr1}</td>
</tr><tr>
	<td>&nbsp;</td>
	<td style="border-bottom: 1px solid #000;" colspan="2">&nbsp;{EvnDirectionMorfoHistologic_Descr2}</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%; vertical-align: top;">11.</td>
	<td style="width: 35%; vertical-align: top;">Перечень прилагаемых документов:</td>
	<td style="width: 61%;">
		<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
		<tbody>
		{EvnDirectionMorfoHistologicItems1}
			<tr>
			<td style="width: 5%;">{Record_Num}</td>
			<td style="width: 65%; border-bottom: 1px solid #000;">{EvnDirectionMorfoHistologicItems_Descr}</td>
			<td style="width: 5%; text-align: right;">(</td>
			<td style="width: 5%; border-bottom: 1px solid #000; text-align: center;">{EvnDirectionMorfoHistologicItems_Count}</td>
			<td style="width: 20%;">) {MorfoHistologicItemsType_Name}</td>
			</tr>
		{/EvnDirectionMorfoHistologicItems1}
		</tbody></table>
	</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 4%;">12.</td>
	<td colspan="2">Перечень и краткое описание предметов (ценностей), доставленных с телом:</td>
</tr><tr>
	<td>&nbsp;</td>
	<td style="width: 35%;">&nbsp;</td>
	<td style="width: 61%;">
		<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
		<tbody>
		{EvnDirectionMorfoHistologicItems2}
			<tr>
			<td style="width: 5%;">{Record_Num}</td>
			<td style="width: 95%; border-bottom: 1px solid #000;">{EvnDirectionMorfoHistologicItems_Descr}</td>
			</tr>
		{/EvnDirectionMorfoHistologicItems2}
		</tbody></table>
	</td>
</tr></tbody>
</table>

<table style="width: 100%; border: none; margin: 2em 0em 2em 0em;" cellspacing="0" cellpadding="2">
<tbody><tr>
	<td style="width: 50%; vertical-align: top;">
		<div>Должность медицинского работника,</div>
		<div>направившего больного</div>
		<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2"><tbody><tr>
			<td style="border-bottom: 1px solid #000;" colspan="3">{Post_Name}</td>
		</tr><tr>
			<td style="width: 55%; border-bottom: 1px solid #000;">{MedPersonal_Fio}&nbsp;{MedPersonal_Snils}</td>
			<td style="width: 5%;">&nbsp;</td>
			<td style="width: 40%; border-bottom: 1px solid #000;">&nbsp;</td>
		</tr><tr>
			<td style="text-align: center;">(ФИО)</td>
			<td>&nbsp;</td>
			<td style="text-align: center;">(подпись)</td>
		</tr></tbody></table>
	</td>
	<td style="width: 50%; vertical-align: top;">
		<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2"><tbody><tr>
			<td style="width: 25%;">Главный врач</td>
			<td style="width: 40%; border-bottom: 1px solid #000;">{GlavVrach_Fio}</td>
			<td style="width: 5%;">&nbsp;</td>
			<td style="width: 30%; border-bottom: 1px solid #000;">&nbsp;</td>
		</tr><tr>
			<td>&nbsp;</td>
			<td style="text-align: center;">(ФИО)</td>
			<td>&nbsp;</td>
			<td style="text-align: center;">(подпись)</td>
		</tr></tbody></table>
	</td>
</tr></tbody>
</table>

<div>Должность уполномоченного лица медицинской организации доставившего тело,</div>
<div>прилагаемые документы, предметы (ценности) ______________________________</div>
<div>______________________   _______________</div>
<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(ФИО)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(подпись)</div>

<div style="margin-top: 2em;">Должность уполномоченного лица медицинской организации принявшего тело,</div>
<div>прилагаемые документы, предметы (ценности) ______________________________</div>
<div>______________________   _______________</div>
<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(ФИО)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(подпись)</div>

</body>

</html>