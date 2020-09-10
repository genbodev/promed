<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать свидетельства о перинатальной смерти</title>
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
	table.ct td.small { width: 16px; }
	table.ct td.tleft { text-align: left; }
	table.ct td.tcent { text-align: center; }
	table.ct td.diat { border: dashed 1px black; vertical-align: top; text-align: center; width: 14px; }
	table.ct td.dashed { border-bottom-style: dashed; vertical-align: bottom; }
</style>

<style type="text/css">
	div.selector { display:none; }
	div.show_selector { display:none; }
	div.single_selector { display:inline; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }
	.broken_text { display: inline; padding: 0px; margin: 0px; }
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
			if (arr[i].id.substring(0,18) == 'broken_text_start_') {
				var obj_arr = arr[i].id.split('_');
				var start_obj = arr[i];
				var end_obj = document.getElementById('broken_text_end_'+obj_arr[3]);
				var max_len = obj_arr[4];
				var words = start_obj.innerHTML.split(' ');
				var cont = false;
				start_obj.innerHTML = '';
				end_obj.innerHTML = '';
				
				for (var j = 0; j < words.length; j++) {
					if (start_obj.innerHTML.length + words[j].length + 1 <= max_len && !cont)
						start_obj.innerHTML += words[j] + ' ';
					else
						cont = true;
					if (cont)
						end_obj.innerHTML += words[j] + ' ';
				}
			}
		}
	}
</script>
</head>

<body class="portrait">

<table style="width: 100%; border: false; margin-bottom: 2em;" cellspacing="0" cellpadding="2">
<tr>
<td>&nbsp;</td>
<td style="float: right; width: 280px;">
Приложение № 3<br/>
к приказу Минздравсоцразвития России<br/>
от 26.12.2008 г. №782н
</td>
</tr>
</table>
<div style="font-weight: bold; text-align: center;">
КОРЕШОК МЕДИЦИНСКОГО СВИДЕТЕЛЬСТВА О ПЕРИНАТАЛЬНОЙ СМЕРТИ К ФОРМЕ № 106-2/У-08<br/>
СЕРИЯ {PntDeathSvid_Ser} № {PntDeathSvid_Num}<br/>
Дата выдачи {PntDeathSvid_GiveDate}<br/>
{DeathSvidType_Name}<br/>
<table style="width: 100%; margin-bottom: 1em;">
	<tr>
		<td style="width: 50%;  text-align: right;">{subhead}</td>
		<td>&nbsp;{PntDeathSvid_disDate}</td>
	</tr>
</table>
</div>

<div style="text-align: left; margin-bottom:1em;">
1. Роды мёртвым плодом: {PntDeathSvid_Condition_1}<br/>
2. Ребёнок родился живым: {PntDeathSvid_Condition_2_1}<br/>
<div style="margin-left:1em;">и умер дата: {PntDeathSvid_Condition_2_2}</div>
3. Смерть наступила: <span class="val_01">до начала родов</span> - 1, <span class="val_02">во время родов</span> - 2, <span class="val_03">после родов</span> - 3, <span class="val_04">неизвестно</span> - 4 <div class="selector">0{PntDeathTime_id}</div><br/>
4. Фамилия, имя, отчество матери: {Person_FIO}<br/>
5. Дата рождения матери: {Person_Birthday}<br/>
6. Место постоянного жительства (регистрации)  матери умершего(мертворожденного) ребёнка:<br/>
<table style="margin-left:1em; width:100%;">
	<tr>
		<td colspan="2">республика, край, область {KLRGN_Name}</td>
	</tr>
	<tr>
		<td>район {KLSubRGN_Name}</td>
		<td>город (село) {KLAddress_Summ}</td>
	</tr>
</table>	
<table style="margin-left:1em; width:100%;">
	<tr>
		<td>улица {KLStreet_name}</td>
		<td style="width:28%;">дом {Address_House}</td>		
		<td style="width:28%;">кв. {Address_Flat}</td>	
	</tr>	
</table>
7. Местность: <span class="val_11">городская</span> - 1, <span class="val_12">сельская</span> - 2 <div class="selector">1{KlareaType_id}</div><br/>
8. Фамилия, имя, отчество умершего ребенка (фамилия плода): {PntDeathSvid_ChildFio}<br/>
9. Пол: <span class="val_21">мальчик</span> - 1, <span class="val_22">девочка</span> - 2 <div class="selector">2{Sex_id}</div><br/>
10. Смерть (мертворождение) произошла: <span class="val_31">в стационаре</span> - 1, <span class="val_32">дома</span> - 2, <span class="val_33">в другом месте</span> - 3 <div class="selector">3{PntDeathPlace_id}</div><br/>

<div class="cutline" style="margin-top:3.5em;">Линия отреза</div>
</div>

<table style="width:100%; margin-bottom:0.2em">
	<tr>
		<td style="border: 1px black dotted; vertical-align:top;">
			<table style="width:100%">
				<tr><td colspan="2">Министерство здравоохранения и социального развития</td></tr>
				<tr><td colspan="2">Российской Федерации</td></tr>
				<tr><td colspan="2">Наименование медицинской организации</td></tr>
				<tr><td colspan="2">{Lpu_Name}</td></tr>
				<tr><td>адрес</td><td>{orgaddress_uaddress}</td></tr>
				<tr><td colspan="2">Код по ОКПО {org_okpo}</td></tr>
				<tr><td colspan="2">Для врача, занимающего частной практикой:</td></tr>
				<tr><td colspan="2">номер лицензии на медицинскую</td></tr>																						
				<tr><td>деятельность</td><td style="border-bottom: 1px black dashed;">&nbsp;</td></tr>
				<tr><td>адрес</td><td>&nbsp;</td></tr>
			</table>
		</td>
		<td style="width:10px;">&nbsp;</td>
		<td style="border: 1px black dotted; vertical-align:top;">
			Код формы по ОКУД<br/><br/>
			Медицинская документация<br/>									
			Учётная форма №106/у-08<br/>								
			Утверждена приказом Минздравсоцразвития России<br/>
			&nbsp;&nbsp;&nbsp;&nbsp;от 26.12.2008 г. № 782н
		</td>
	</tr>
</table>

<div style="font-weight: bold; text-align: center;">
МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О ПЕРИНАТАЛЬНОЙ СМЕРТИ<br/>
СЕРИЯ {PntDeathSvid_Ser} № {PntDeathSvid_Num}<br/>
Дата выдачи {PntDeathSvid_GiveDate}<br/>
{DeathSvidType_Name}<br/>
<table style="width: 100%; margin-bottom: 0em;">
	<tr>
		<td style="width: 50%;  text-align: right;">{subhead}</td>
		<td>&nbsp;{PntDeathSvid_disDate}</td>
	</tr>
</table>
</div>

<div style="text-align: left; margin-bottom:1em;">

<table class="ct">
<tr><td class="small">1.</td><td>Роды мёртвым плодом: {PntDeathSvid_Condition_1}</td></tr>
<tr><td>2.</td><td>Ребёнок родился живым: {PntDeathSvid_Condition_2_1}<br/>и умер дата: {PntDeathSvid_Condition_2_2}</td></tr>
<tr><td>3.</td><td>Смерть наступила: <span class="val_1">до начала родов</span> - 1, <span class="val_2">во время родов</span> - 2, <span class="val_3">после родов</span> - 3, <span class="val_4">неизвестно</span> - 4 <div class="selector">{PntDeathTime_id}</div></td></tr>
</table>

<table style="width:100%;">
<tr>
	<td style="font-weight: bold; text-align: center; width:50%;">Мать</td>
	<td style="font-weight: bold; text-align: center;">Ребёнок</td>
</tr>
<tr>
	<td style="border: 1px black solid; vertical-align: top;">
		<table class="ct ctleft">
			<tr><td class="small">4.</td><td>Фамилия, имя, отчество матери:</br>{Person_FIO}</td></tr>
			<tr><td>5.</td><td>Дата рождения: {Person_Birthday}</td></tr>
			<tr><td>6.</td><td>Место постоянного жительства(регистрации):</br>республика, край, область {KLRGN_Name}</br>район {KLSubRGN_Name}</br>город (село) {KLAddress_Summ}</br>улица {KLStreet_name}&nbsp;&nbsp;дом {Address_House}&nbsp;&nbsp;кв. {Address_Flat}</td></tr>
			<tr><td>7.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{KlareaType_id}</div></td></tr>
			<tr><td>8.</td><td>Семейное положение матери: <span class="val_1">состоит в зарегистрированном <nobr>браке</span> - 1,</nobr> <span class="val_2">не состоит в зарегистрированном <nobr>браке</span> - 2,</nobr> <span class="val_3">неизвестно</span> - 3 </nobr><div class="selector">{PntDeathFamilyStatus_Code}</div></td></tr>
			<tr><td>9.</td><td>Образование:<br/>профессиональное: <nobr><span class="val_1">высшее</span> - 1,</nobr> <span class="val_2">неполное <nobr>высшее</span> - 2,</nobr> <nobr><span class="val_3">среднее</span> - 3,</nobr> <nobr><span class="val_4">начальное</span> - 4;</nobr> общее: <nobr><span class="val_5">среднее (полное)</span> - 5,</nobr> <nobr><span class="val_6">основное</span> - 6,</nobr> <nobr><span class="val_7">начальное</span> - 7,</nobr> <span class="val_8">не имеет начального <nobr>образования</span> - 8,</nobr> <nobr><span class="val_9">неизвестно</span> - 9 </nobr><div class="selector">{PntDeathEducation_Code}</div></td></tr>
			<tr><td>10.</td><td>Занятость: была занята в экономике: <span class="val_1">руководители и специалисты высшего уровня <nobr>квалификации</span> - 1,</nobr> <span class="val_2">прочие <nobr>специалисты</span> - 2,</nobr> <span class="val_3">квалифицированные <nobr>рабочие</span> - 3,</nobr> <span class="val_4">неквалифицированные <nobr>рабочие</span> - 4,</nobr> <span class="val_5">занятые на военной <nobr>службе</span> - 5;</nobr> не была занята в экономике: <nobr><span class="val_6">пенсионеры</span> - 6,</nobr> <span class="val_7">студенты и <nobr>учащиеся</span> - 7,</nobr> <span class="val_8">работавшие в личном подсобном <nobr>хозяйстве</span> - 8,</nobr> <nobr><span class="val_9">безработные</span> - 9,</nobr> <nobr><span class="val_10">прочие</span> - 10. </nobr><div class="selector">{PntDeathZanat_id}</div></td></tr>
			<tr><td>11.</td><td>Которые по счету роды: {PntDeathSvid_ChildCount}</td></tr>
		</table>
	</td>
	<td style="border: 1px black solid; vertical-align: top;">
		<table class="ct ctleft">
			<tr><td class="small">12.</td><td>Фамилия ребёнка</br>{PntDeathSvid_ChildFio}</td></tr>
			<tr><td>13.</td><td>Место смерти (мертворождения):</br>республика, край, область {DKLRGN_Name}</br>район {DKLSubRGN_Name}</br>город (село) {DKLAddress_Summ}</td></tr>
			<tr><td>14.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{DKlareaType_id}</div></td></tr>
			<tr><td>15.</td><td>Смерть (мертворождение) произошло: <span class="val_1">в <nobr>стационаре</span> - 1,</nobr> <nobr><span class="val_2">дома</span> - 2,</nobr>	<nobr><span class="val_3">в другом месте</span> - 3,</nobr> <nobr><span class="val_4">неизвестно</span> - 4. </nobr><div class="selector">{BirthPlace_id}</div></td></tr>
			<tr><td>16.</td><td>Пол: <span class="val_1">мальчик</span> - 1, <span class="val_2">девочка</span> - 2, <div class="selector">{Sex_id}</div></td></tr>
			<tr><td>17.</td><td>Масса тела ребенка (плода) при рождении: {PntDeathSvid_Mass} г.</td></tr>
			<tr><td>18.</td><td>Длина ребенка (плода) при рождении: {PntDeathSvid_Height} см.</td></tr>
			<tr><td>19.</td><td>Мертворождение или живорождение произошло:</br>
				при одноплодных родах <input type="checkbox" {PntDeathSvid_IsMnogoplod_Checked} onClick="return false;"/></br>
				при многоплодных родах: которыми по счёту {PntDeathSvid_PlodIndex}</br>
				число детей родившихся (живыми и мёртвыми) {PntDeathSvid_PlodCount}
			</td></tr>
		</table>
	</td>	
</tr>
</table>
<div class="noprint">&nbsp;</div>
<table class="ct">
<tr><td colspan="2">
	<table class="ct ctleft">
		<tr><td rowspan="11" class="small">11.</td><td colspan="2">Причины перинатальной смерти:</td><td rowspan="11" class="small">&nbsp;</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2" class="small">а)</td><td class="dashed">{Diag1_Name}</td><td class="diat">{D11}</td><td class="diat">{D12}</td><td class="diat">{D13}</td><td class="diat">{D14}</td><td class="diat">{D15}</td></tr>
		<tr><td class="tcent">(основное заболевание или патологическое состояние плода или ребёнка)</td><td colspan="5">&nbsp;</td></tr>			
		<tr><td rowspan="2">б)</td><td class="dashed">{Diag2_Name}</td><td class="diat">{D21}</td><td class="diat">{D22}</td><td class="diat">{D23}</td><td class="diat">{D24}</td><td class="diat">{D25}</td></tr>
		<tr><td class="tcent">(другие заболевания или патологические состояния плода или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">в)</td><td class="dashed">{Diag3_Name}</td><td class="diat">{D31}</td><td class="diat">{D32}</td><td class="diat">{D33}</td><td class="diat">{D34}</td><td class="diat">{D35}</td></tr>
		<tr><td class="tcent">(основное заболевание или патологическое состояние матери, оказавшее неблагоприятное влияние на плод или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">г)</td><td class="dashed">{Diag4_Name}</td><td class="diat">{D41}</td><td class="diat">{D42}</td><td class="diat">{D43}</td><td class="diat">{D44}</td><td class="diat">{D45}</td></tr>
		<tr><td class="tcent">(другие заболевания или патологические состояния матери, оказавшее неблагоприятное влияние на плод или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">д)</td><td class="dashed">{Diag5_Name}</td><td class="diat">{D51}</td><td class="diat">{D52}</td><td class="diat">{D53}</td><td class="diat">{D54}</td><td class="diat">{D55}</td></tr>
		<tr><td class="tcent">(другие обстоятельства, имевшие отношение к мертворождению, смерти)</td><td colspan="5">&nbsp;</td></tr>
	</table>
</td></tr>
<tr><td colspan="2">
	
	<table class="ct ctcenter">
		<tr><td class="small tleft">12.</td><td class="dashed">{MedPersonal_Post}</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">{MedPersonal_FIO}</td></tr>
		<tr><td></td><td>(должность врача (фельдшера, акушерки), заполнившего</br>Медицинское свидетельство о перинатальной смерти)	</td><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
	</table>	
	
	<table class="ct ctleft">
		<tr>
			<td class="small">13.</td>
			<td style="width:395px;">Запись акта о мертворождении, смерти (нужное подчеркнуть) №</td>
			<td style="width:120px;" class="dashed tcent">{PntDeathSvid_ActNumber}</td>
			<td style="padding-left:1em;">от <!--"_______"___________________20____-->{PntDeathSvid_ActDT}г.</td>
		</tr>
	</table>
	<table class="ct ctleft">
		<tr>
			<td class="small">&nbsp;</td>
			<td style="width:168px;">наименование органа  ЗАГС</td>
			<td class="dashed tcent">{Org_Name}</td>
			<td class="small">&nbsp;</td>
		</tr>
	</table>
	<table class="ct ctleft">
		<tr>
			<td class="small">&nbsp;</td>
			<td style="width:290px;">фамилия, имя, отчество работника органа ЗАГС</td>
			<td class="dashed tcent">{PntDeathSvid_ZagsFIO}</td>
			<td class="small">&nbsp;</td>
		</tr>
	</table>
	
	<table class="ct ctcenter">
		<tr>
			<td class="small" rowspan="2">14.</td>
			<td class="tleft" style="width:75px;" rowspan="2">Получатель</td>
			<td class="dashed">{PntDeathSvid_PolFio}&nbsp;{DeputyKind_Name}</td>
		</tr>
		<tr>
			<td class="ctcenter">(фамилия, имя, отчество и отношение к мертворожденному (умершему ребёнку)</td>
			<td class="small">&nbsp;</td>
		</tr>
	</table>

	<table class="ct tleft">
		<tr><td class="small">&nbsp;</td><td style="width:400px;">Документ , удостоверяющий личность получателя (серия, номер, кем выдан)</td><td class="dashed"><div id="broken_text_start_1_35" class="broken_text">{PntDeathSvid_PolDoc}</div></td><td class="small">&nbsp;</td></tr>
		<tr><td class="small">&nbsp;</td><td colspan="2" class="dashed"><div id="broken_text_end_1" class="broken_text">&nbsp;</div></td><td class="small">&nbsp;</td></tr>
	</table>
	
	<table class="ct ctcenter">
		<tr><td class="small tleft" rowspan="2">&nbsp;</td><td class="tleft" rowspan="2">{PntDeathSvid_PolDate}</td><td style="width:150px;" class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td></tr>
		<tr><td>(подпись)</td></tr>
	</table>

<div class="cutline" style="margin-bottom:1em;">Линия отреза</div>
</td></tr>																																																			
<tr><td>20.</td><td>Которым по счёту ребёнок был рождён у матери (считая умерших и не считая мертворожденных: {PntDeathSvid_ChildCount}</td></tr>
<tr><td>21.</td><td>Смерть ребенка (плода) произошла: <span class="val_1">от <nobr>заболевания</span> - 1,</nobr> <span class="val_2">несчастного <nobr>случая</span> - 2,</nobr> <nobr><span class="val_3">убийства</span> - 3,</nobr>	<span class="val_4">род смерти не <nobr>установлен</span> - 4 </nobr><div class="selector">{PntDeathBirthCause_id}</div></td></tr>
<tr><td>22.</td><td>Лицо, принимавшее роды: <nobr><span class="val_1">врач</span> - 1,</nobr> <span class="val_2">фельдшер, <nobr>акушерка</span> - 2,</nobr> <nobr><span class="val_3">другое</span> - 3 </nobr><div class="selector">{PntDeathGetBirth_Code}</div></td></tr>
<tr><td colspan="2">
	<table class="ct ctleft">
		<tr><td rowspan="11" class="small">23.</td><td colspan="2">Причины перинатальной смерти:</td><td rowspan="11" class="small">&nbsp;</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2" class="small">а)</td><td class="dashed">{Diag1_Name}</td><td class="diat">{D11}</td><td class="diat">{D12}</td><td class="diat">{D13}</td><td class="diat">{D14}</td><td class="diat">{D15}</td></tr>
		<tr><td class="tcent">(основное заболевание или патологическое состояние плода или ребёнка)</td><td colspan="5">&nbsp;</td></tr>			
		<tr><td rowspan="2">б)</td><td class="dashed">{Diag2_Name}</td><td class="diat">{D21}</td><td class="diat">{D22}</td><td class="diat">{D23}</td><td class="diat">{D24}</td><td class="diat">{D25}</td></tr>
		<tr><td class="tcent">(другие заболевания или патологические состояния плода или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">в)</td><td class="dashed">{Diag3_Name}</td><td class="diat">{D31}</td><td class="diat">{D32}</td><td class="diat">{D33}</td><td class="diat">{D34}</td><td class="diat">{D35}</td></tr>
		<tr><td class="tcent">(основное заболевание или патологическое состояние матери, оказавшее неблагоприятное влияние на плод или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">г)</td><td class="dashed">{Diag4_Name}</td><td class="diat">{D41}</td><td class="diat">{D42}</td><td class="diat">{D43}</td><td class="diat">{D44}</td><td class="diat">{D45}</td></tr>
		<tr><td class="tcent">(другие заболевания или патологические состояния матери, оказавшее неблагоприятное влияние на плод или ребёнка)</td><td colspan="5">&nbsp;</td></tr>
		<tr><td rowspan="2">д)</td><td class="dashed">{Diag5_Name}</td><td class="diat">{D51}</td><td class="diat">{D52}</td><td class="diat">{D53}</td><td class="diat">{D54}</td><td class="diat">{D55}</td></tr>
		<tr><td class="tcent">(другие обстоятельства, имевшие отношение к мертворождению, смерти)</td><td colspan="5">&nbsp;</td></tr>
	</table>
</td></tr>
<tr><td>24.</td><td>Причины смерти установлены:</td></tr>																																																				
<tr><td>а)</td><td><span class="val_1">врачом, установившим <nobr>смерть</span> - 1,</nobr> <span class="val_2">врачом-акушером, принимавшим <nobr>роды</span> - 2,</nobr> <span class="val_3">врачом-педиатром, лечившим <nobr>ребенка</span> - 3,</nobr> <nobr><span class="val_4">патологоанатомом</span> - 4,</nobr> <nobr><span class="val_5">судебно-медицинским экспертом</span> - 5,</nobr> <nobr><span class="val_6">акушеркой</span> - 6,</nobr> <nobr><span class="val_7">фельдшером</span> - 7 </nobr><div class="selector">{PntDeathSetType_id}</div></td></tr>
<tr><td>б)</td><td>на основании: <span class="val_1">осмотра <nobr>трупа</span> - 1,</nobr> <span class="val_2">записей в медицинской <nobr>документации</span> - 2,</nobr> <span class="val_3">предшествовавшего <nobr>наблюдения</span> - 3,</nobr> <nobr><span class="val_4">вскрытия</span> - 4 </nobr><div class="selector">{PntDeathSetCause_id}</div></td></tr>
<tr><td colspan="2">	
	<table class="ct ctcenter" style="margin-top:1em;">
		<tr><td class="small tleft" rowspan="5">25.</td><td class="dashed">{MedPersonal_Post}</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">{MedPersonal_FIO}</td></tr>
		<tr><td class="tcenter" style="padding-bottom:2em;">(должность врача (фельдшера, акушерки),</br>заполнившего Медицинское свидетельство</br>о перинатальной смерти)	</td><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
		<tr>
			<?php if (empty($OrgHeadPost_Name)) { ?>
			<td class="tleft" rowspan="2">
				<span class="val_1">Руководитель медицинской организации</span>,</br>частопрактикующий врач (подчекнуть)
				<div class="selector">1</div>
			</td>
			<?php } else { ?>
			<td class="tleft" rowspan="2">{OrgHeadPost_Name}</td>
			<?php } ?>
			<td rowspan="2" class="small">&nbsp;</td>
			<td class="dashed">&nbsp;</td>
			<td rowspan="2" class="small">&nbsp;</td>
			<td class="dashed">{OrgHead_Fio}</td>
		</tr>
		<tr><td>(подпись)</td><td>(фамилия, имя, отчество)</td></tr>
		<tr><td colspan="5" style="padding:1em; padding-bottom:2em; text-align:left;">Печать</td></tr>
	</table>
</td></tr>
<tr style="border-top-weight:2px; border-top-style: solid;"><td>26.</td><td>Свидетельство проверено врачом, ответственным за правильность заполнения медицинских свидетельств.</td></tr>
</table>

<table class="ct ctcenter">
	<tr><td rowspan="2" class="tleft">&nbsp;"____"_______________20___г.</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td></tr>
	<tr><td>(подпись)</td><td>(Фамилия, имя, отчество)</td></tr>
</table>

<script type="text/javascript">activateSelectors();</script>
</body>

</html>