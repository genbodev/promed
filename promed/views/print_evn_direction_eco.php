<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать направления на ЭКО</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family: 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family: 'Times New Roman', Times, serif; font-size: 9pt; }
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

<div style="text-align: right; margin-bottom: 3em;">
    <div>Приложение 11 к приказу</div>
    <div>Министерства здравоохранения Пермского края</div>
    <div>от 31 мая 2018 года № СЭД-34-01-06-418</div>
</div>

<div style="text-align: center; margin: 1em 0em 3em 0em; font-weight: bold;">
	<div>НАПРАВЛЕНИЕ</div>
	<div>для проведения процедуры ЭКО за счет средств обязательного</div>
	<div>медицинского страхования</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="0">
    <tbody><tr>
        <td style="width: 50%;">№ {EvnDirectionEco_Num}</td>
        <td style="width: 50%; text-align: right;">{EvnDirectionEco_setDate}</td>
    </tr></tbody>
</table>

<div style="text-align: center; margin: 1em 0em 3em 0em;">
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Person_Surname} {Person_Firname} {Person_Secname}</div>
    <div class="small">(ФИО пациента, направляемого для проведения ЭКО)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Person_Birthday}, {Person_Age}</div>
    <div class="small">(шифр пациента) (дата рождения) (возраст пациента)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{DocumentType_Name} {Document_Ser} № {Document_Num}, {Document_begDate}</div>
    <div class="small">(документ, удостоверяющий личность)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Polis_Ser} {Polis_Num}, {Person_Snils}</div>
    <div class="small">(полис ОМС, СНИЛС)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Person_Address}</div>
    <div class="small">(адрес регистрации/места жительства)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Diag_Code}</div>
    <div class="small">(код диагноза по МКБ)</div>
</div>

<div style="text-align: center; margin: 1em 0em 1em 0em;">
    <div style="border-bottom: 1px solid black; margin-top: 1em;">Министерство здравоохранения Пермского края</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">ул. Ленина, д. 51, г. Пермь, 614006; Тел. (342)217 79 00; факс (342) 217 76 81; E-mail: info@minzdrav.permkrai.ru</div>
    <div class="small">(наименование органа исполнительной власти субъекта Российской Федерации в сфере здравоохранения выдавшего направление, адрес, телефон, факс, адрес эл. почты)</div>
</div>

<div style="text-align: center; margin: 1em 0em 1em 0em;">
    <div>Пациент обязан явиться в клинику в течение 30 дней с даты выдачи направления</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
    <tbody><tr>
        <td style="width: 50%;">Председатель комиссии</td>
        <td style="width: 50%; text-align: right;">подпись</td>
    </tr><tr>
        <td colspan="2" style="border-bottom: 1px solid black;">&nbsp;</td>
    </tr></tbody>
</table>

<div style="page-break-after: always;"></div>

<div style="text-align: right; margin: 1em 0em 3em 0em;">
    <div>Приложение 12 к приказу</div>
    <div>Министерства здравоохранения Пермского края</div>
    <div>от «&nbsp;&nbsp;&nbsp;» &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2018 года №</div>
</div>

<div style="text-align: center; margin: 1em 0em 3em 0em; font-weight: bold;">
    <div>Сведения о медицинской организации, выполнившей процедуру ЭКО по направлению для проведения процедуры ЭКО за счет средств ОМС</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="0">
    <tbody><tr>
        <td style="width: 50%;">№</td>
        <td style="width: 50%; text-align: right;">от «&nbsp;&nbsp;&nbsp;» &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 20&nbsp;&nbsp;&nbsp;&nbsp;г.</td>
    </tr></tbody>
</table>

<div style="text-align: center; margin: 1em 0em 3em 0em;">
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Lpu_Name}</div>
    <div class="small">(наименование медицинской организации, выполнившей процедуру ЭКО)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">{Person_Birthday}, {Person_Age}</div>
    <div class="small">(шифр пациента) (дата рождения) (возраст пациента)</div>
    <div style="border-bottom: 1px solid black; margin-top: 1em;">&nbsp;</div>
    <div class="small">(период проведения ЭКО) (результат проведенного лечения)</div>
</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
    <tbody><tr>
        <td style="width: 40%; text-align: center;" class="small">(руководитель медицинской организации)</td>
        <td style="width: 20%; text-align: center;" class="small">М.П.</td>
        <td style="width: 40%; text-align: center;" class="small">(Ф.И.О.)</td>
    </tr></tbody>
</table>
</body>

</html>