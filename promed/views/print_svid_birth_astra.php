<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать свидетельства о рождении</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family:  'Times New Roman', Times, serif; font-size: 8pt; }
	td { vertical-align: middle; border: none; }
	.noprint { display: auto; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family:  'Times New Roman', Times, serif; font-size: 8pt; }
	td { vertical-align: middle; border: none; }
	.noprint { display: none; }
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
	div.selector { display:none; }
	div.show_selector { display:none; }
	div.single_selector { display:inline; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }
    div.cutlineempty { border: 1px black none; border-bottom-style:none; border-weight:0px; text-align:center; font-size:0.em; }
</style>

<script type="text/javascript">
	function activateSelectors() {
		var arr = document.getElementsByTagName("div");			
		for(var i = 0; i < arr.length; i++) {
			if (arr[i].className == "selector") {
				var span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(var j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML) span_arr[j].style.textDecoration = "underline";
				}
			}
			if (arr[i].className == "show_selector") {
				var span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(var j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML)
						span_arr[j].style.display = "inline";
					else
						span_arr[j].style.display = "none";
				}
			}
			if (arr[i].className == "single_selector") {
				var span_arr = arr[i].getElementsByTagName("span");
				var empty = true;
				for(var j = 0; j < span_arr.length; j++) {
					if (!empty)
						span_arr[j].style.display = 'none';
					if(span_arr[j].innerHTML.replace('&nbsp;','') != '')
						empty = false;
				}
			}
		}
	}
</script>
</head>

<body class="portrait" style="margin:6em auto;width: 99%;">

<table style="width: 100%; border: none; margin-bottom: 2em;" cellspacing="0" cellpadding="2">
<tr>
	<td>&nbsp;</td>
	<td style="float: right; width: 280px;">
		<!--Приложение № 2<br/>
        к приказу Министерства здравоохранения и социального развития Российской Федерации<br/>
		от 27.12.2011 г. №1687н-->
	</td>
</tr>
</table>

<div style="font-weight: bold; text-align: center;">
    <!--УЧЕТНАЯ ФОРМА № 103/У «МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О РОЖДЕНИИ»<br/>
    КОРЕШОК МЕДИЦИНСКОГО СВИДЕТЕЛЬСТВА О РОЖДЕНИИ<br/><br>
	СЕРИЯ {BirthSvid_Ser} № {BirthSvid_Num}<br/>-->
	Дата выдачи {BirthSvid_GiveDate}<br/><br/>
</div>

<div style="text-align: left; margin-bottom:4em;">
	1. Ребёнок родился: число {BirthSvid_BirthDateDay}, месяц {BirthSvid_BirthDateMonth}, год {BirthSvid_BirthDateYear}, час {BirthSvid_BirthTimeHour}, мин.{BirthSvid_BirthTimeMin}<br/>
	2. Фамилия, имя, отчество матери: {Person_FIO}<br/>
	3. Дата рождения матери: число {Person_BirthDayDay}, месяц {Person_BirthDayMonth}, год {Person_BirthDayYear}<br/>
	4. Место постоянного жительства (регистрации) матери ребёнка: республика, край, область {KLRGN_Name}<br/>
	<table style="margin-left:1em; width:100%;">
		<tr>
			<td>район {KLSubRGN_Name}</td>
			<td>город {KLCity_Name}</td>
			<td style="width:45%;">населённый пункт {KLTown_Name}</td>
		</tr>
	</table>	
	<table style="margin-left:1em; width:100%;">
		<tr>
			<td>улица {KLStreet_name}</td>
			<td style="width:28%;">дом {Address_House}</td>		
			<td style="width:28%;">кв. {Address_Flat}</td>	
		</tr>	
	</table>
	5. Местность: <span class="val_01">городская</span> - 1, <span class="val_02">сельская</span> - 2 <div class="selector">0{KlareaType_id}</div><br/>
	6. Пол ребенка:	<span class="val_11">мальчик</span> - 1, <span class="val_12">девочка</span> - 2, <div class="selector">1{BirthSex_id}</div><br/><br/>
	{BirthSvid_IsFromMother_Text}
<!--<div class="cutline" style="margin-top:11em;">Линия отреза</div>-->
</div>


<table style="width:50%; margin-bottom:2em">
<tr>
<td style="vertical-align:top;">
	<table style="width:100%;">
		<!--<tr><td colspan="2">Министерство здравоохранения и социального развития</td></tr>
		<tr><td colspan="2">Российской Федерации</td></tr>
		<tr><td colspan="2" style="border-top: 1px black dotted;">Наименование медицинской организации</td></tr>
		<tr><td colspan="2">{Lpu_Name}</td></tr>
        <tr><td colspan="2">Номер лицензии на осуществление медицинской</td></tr>
        <tr><td>деятельности</td><td>{LpuLicence_Num}</td></tr>
		<tr><td style="width: 85px; vertical-align: top;">адрес</td><td>{orgaddress_uaddress}</td></tr>
		<tr><td colspan="2">Код по ОКПО {org_okpo}</td></tr>
		<tr><td colspan="2">Для индивидуального предпринимателя, осуществляющего медицинскую деятельность:</td></tr>
		<tr><td colspan="2">номер лицензии на осуществление медицинской</td></tr>
		<tr><td>деятельности</td><td style="border-bottom: 1px black dashed;">&nbsp;</td></tr>
		<tr><td>адрес</td><td style="border-bottom: 1px black dashed;">&nbsp;</td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>-->
		<tr><td style="height:6em"></td></tr>
		<tr><td style="height:3em">{Lpu_Name}</td></tr>
		<tr><td style="height:2em;padding-left: 43px">{orgaddress_uaddress}</td></tr>
		<tr><td style="height:2em;padding-left: 94px">{org_okpo}</td></tr>
		<tr><td style="height:12em">{LpuLicence_Num}</td></tr>
	</table>
</td>
<!--<td style="width:10px;">&nbsp;</td>
<td style="border: 1px black dotted; vertical-align:top; margin-left:1em">
	<table style="width: 100%;"><tr><td style="border-bottom: 1px black dotted;">
	Код формы по ОКУД<br/>
	Медицинская документация<br/>
    </td></tr></table>
	Форма №103/у<br/>
    Утверждена приказом Министерства здравоохранения и<br>
    социального развития Российской Федерации<br/>
	от 27 декабря 2011 г. № 1687н
</td>-->
</tr>
</table>
<div class="cutlineempty" style="margin-top:11em;"></div>
<br>
<div style="font-weight: bold; text-align: center;">
	<!--МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О РОЖДЕНИИ<br/><br/>
	СЕРИЯ {BirthSvid_Ser} № {BirthSvid_Num}<br/>-->
	Дата выдачи {BirthSvid_GiveDate}<br/><br/>
</div>

<div style="text-align: left; margin-bottom:1em;">
	1. Ребёнок родился: число {BirthSvid_BirthDateDay}, месяц {BirthSvid_BirthDateMonth}, год {BirthSvid_BirthDateYear}, час {BirthSvid_BirthTimeHour}, мин.{BirthSvid_BirthTimeMin}<br/>
</div>

<table style="width: 100%; margin-bottom: 34em;">
<tr>
	<td style="font-weight: bold; text-align: center; width:50%;">Мать</td>
	<td style="font-weight: bold; text-align: center;">Ребёнок</td>
</tr>
<tr>
	<td style="border: 1px black solid; vertical-align: top;">
		<table class="ct ctleft" style="margin-bottom:3em;">
			<tr><td class="small">2.</td><td>Фамилия, имя, отчество:</br>{Person_FIO}</td></tr>
			<tr><td>3.</td><td>Дата рождения: число {Person_BirthDayDay}, месяц {Person_BirthDayMonth}, год {Person_BirthDayYear}</td></tr>
			<tr><td>4.</td><td>Место постоянного жительства(регистрации):</br>республика, край, область {KLRGN_Name}</br>район {KLSubRGN_Name}</br>город (село) {KLAddress_Summ}</br>улица {KLStreet_name}&nbsp;&nbsp;дом {Address_House}&nbsp;&nbsp;кв. {Address_Flat}</td></tr>
			<tr><td>5.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{KlareaType_id}</div></td></tr>
			<tr><td>6.</td><td>Семейное положение: <span class="val_1">состоит в зарегистрированном браке</span> - 1, <span class="val_2">не состоит в зарегистрированном браке</span> - 2, <span class="val_3">неизвестно</span> - 3<div class="selector">{BirthFamilyStatus_id}</div></td></tr>
		</table>
	</td>
	<td style="border: 1px black solid; vertical-align: top;">
		<table class="ct ctleft" style="margin-bottom:3em;">
			<tr><td class="small">11.</td><td>Фамилия ребёнка</br>{BirthSvid_ChildFamil}</td></tr>
			<tr><td>12.</td><td>Место рождения:</br>республика, край, область {BKLRGN_Name}</br>район {BKLSubRGN_Name}</br>город (село) {BKLAddress_Summ}</td></tr>
			<tr><td>13.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{BKlareaType_id}</div></td></tr>
			<tr><td>14.</td><td>Роды произошли: <span class="val_1">в <nobr>стационаре</span> - 1,</nobr> <nobr><span class="val_2">дома</span> - 2,</nobr>	<nobr><span class="val_3">в другом месте</span> - 3,</nobr> <nobr><span class="val_4">неизвестно</span> - 4.</nobr> <div class="selector">{BirthPlace_id}</div></td></tr>
			<tr><td>15.</td><td>Пол: <span class="val_1">мальчик</span> - 1, <span class="val_2">девочка</span> - 2, <div class="selector">{BirthSex_id}</div></td></tr>
		</table>
	</td>	
</tr></table>
<table>
<tr style="page-break-before: always;">
	<td colspan="2">
		<table style="width: 100%;">
			<tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
			<tr>
				<td style="text-align: right;">
					Оборотная сторона&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
			</tr>
		</table>
		<table style="margin-top:1em; margin-bottom:5.2em;" class="ct ctcenter">
			<tr><td class="small tleft">7.</td><td colspan="5" class="tleft" style="padding-bottom:1em;">Роды произошли: <span class="val_1">в стационаре</span> - 1, <span class="val_2">дома</span> - 2, <span class="val_3">в другом месте</span> - 3, <span class="val_4">неизвестно</span> - 4. <div class="selector">{BirthPlace_id}</div></td><td rowspan="8" style="width:12px;">&nbsp;</td></tr>
			<tr><td class="tleft">8.</td><td class="dashed">{MedPersonal_Post}</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">{MedPersonal_FIO}</td></tr>
			<tr><td></td><td>(должность врача (фельдшера, акушерки),</br>выдавшего медицинское свидетельство)</td><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
			<tr><td class="tleft">9.</td><td colspan="5" class="tleft dashed">Получатель</br>{birthsvid_PolFio}&nbsp;{DeputyKind_Name}</td></tr>
			<tr><td></td><td colspan="5">(фамилия, имя, отчество и отношение к ребёнку)</td></tr>
			<tr><td></td><td colspan="5" class="tleft dashed">{BirthSvid_RcpDocument}</td></tr>
			<tr><td></td><td colspan="5" style="padding-bottom:5em;">(документ, удостоверяющий личность получателя, серия, номер, кем выдан)</td></tr>
			<tr><td></td><td colspan="2" class="tleft">{BirthSvid_RcpDate}</td><td colspan="2" style="text-align:right;">Подпись получателя</td><td class="dashed">&nbsp;</td></tr>
		</table>
		<div class="cutline" style="height: 2.1em; border:white" ></div>
	</td>
</tr>
<tr>
	<td style="border: 1px black solid; border-top-style: none; vertical-align: top;">
		<table class="ct ctleft">
		<tr><td class="small">7.</td><td>Образование:</td></tr>
		<tr><td></td><td>профессиональное: <nobr><span class="val_1">высшее</span> - 1,</nobr> <span class="val_2">неполное <nobr>высшее</span> - 2,</nobr> <nobr><span class="val_3">среднее</span> - 3,</nobr> <nobr><span class="val_4">начальное</span> - 4;</nobr> общее: <nobr><span class="val_5">среднее (полное)</span> - 5,</nobr> <nobr><span class="val_6">основное</span> - 6,</nobr> <nobr><span class="val_7">начальное</span> - 7,</nobr> <span class="val_8">не имеет начального <nobr>образования</span> - 8,</nobr> <nobr><span class="val_9">неизвестно</span> - 9</nobr><div class="selector">{BirthEducation_Code}</div></td></tr>
		<tr><td>8.</td><td>Занятость: была занята в экономике: <span class="val_1">руководители и специалисты высшего уровня <nobr>квалификации</span> - 1,</nobr> <span class="val_2">прочие <nobr>специалисты</span> - 2,</nobr> <span class="val_3">квалифицированные <nobr>рабочие</span> - 3,</nobr> <span class="val_4">неквалифицированные <nobr>рабочие</span> - 4,</nobr> <span class="val_5">занятые на военной <nobr>службе</span> - 5;</nobr> <span class="val_6">не была занята в экономике: <nobr>пенсионеры</span> - 6,</nobr> <span class="val_7">студенты и <nobr>учащиеся</span> - 7,</nobr> <span class="val_8">работавшие в личном подсобном <nobr>хозяйстве</span> - 8,</nobr> <nobr><span class="val_9">безработные</span> - 9,</nobr> <nobr><span class="val_10">прочие</span> - 10.</nobr><div class="selector">{BirthZanat_id}</div></td></tr>
		<tr><td>9.</td><td>Срок первой явки к врачу (фельдшеру, акушерке) {BirthSvid_Week}	недель</td></tr>
		<tr><td>10.</td><td>Который по счету родившийся ребенок у матери: {BirthSvid_ChildCount}</td></tr>
		</table>
	</td>
	<td style="border: 1px black solid; border-top-style: none; vertical-align: top;">
		16. Масса тела ребенка при рождении: {BirthSvid_Mass} {Okei_mid_Okei_NationSymbol}</br>
		17. Длина тела ребенка при рождении: {BirthSvid_Height} см.</br>			
		</br>
		18. Ребёнок родился:</br>
			при одноплодных родах <input type="checkbox" {BirthSvid_IsMnogoplod_Checked} onClick="return false;"/></br>
			при многоплодных родах: которым по счёту {BirthSvid_PlodIndex}</br>
			<span style="padding-left:120px;">число родившихся {BirthSvid_PlodCount}</span></br></br>
	</td>
</tr>
</table>

<div style="text-align: left; margin-bottom:1em;">
	<table style="margin-top:2em; margin-bottom:1em;" class="ct ctcenter">
		<tr><td class="small">19.</td><td colspan="5" class="tleft" style="padding-bottom:2em;">Лицо, принимавшее роды:</br><span class="val_1">врач - акушер - гинеколог</span> - 1, <span class="val_2">фельдшер, акушерка</span> - 2, <span class="val_3">другое лицо</span> - 3 <div class="selector">{BirthSpecialist_id}</div></td><td rowspan="6">&nbsp;</td></tr>
		<tr><td rowspan="5">20.</td><td class="dashed">{MedPersonal_Post}</td><td rowspan="4" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="4" class="small">&nbsp;</td><td class="dashed">{MedPersonal_FIO}</td></tr>		
		<tr><td style="padding-bottom:1em;">(должность врача (фельдшера, акушерки),</br>заполнившего медицинское свидетельство)</td><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
		<tr><td class="tleft"><span class="val_1">Руководитель медицинской организации</span>,</br>Индивидуальный предприниматель, осуществляющий медицинскую деятельность<div class="selector">1</div></td><td class="dashed">&nbsp;</td><td class="dashed">{OrgHead_Fio}</td></tr>
		<tr><td class="tleft" style="padding-bottom:2em;">(нужное подчеркнуть)</td><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
		<tr><td colspan="5" class="tleft">Печать</td></tr>
	</table>
</div>

<script type="text/javascript">activateSelectors();</script>
</body>

</html>