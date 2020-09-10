<html>
<head>
<title>{EvnPSTemplateTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
.underline td {border-bottom: 1px solid #000;}

.withBorder { margin-top: 10px; }
.withBorder td{ border:1px solid #000; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.underline td {border-bottom: 1px solid #000;}
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

<body>

<table style="width: 100%;"><tr>
<td style="width: 70%; vertical-align: top;">Министерство здравоохранения<br />Российской Федерации<br />{Lpu_Name}</td>
<td style="width: 30%; vertical-align: top;">
	<div>Приложение № 5</div>
	<div>к  приказу Минздрава России от 30.12.2002 № 413</div>
	<div>Медицинская документация</div>
	<div>Форма № 066/у-02</div>
	<div>Утверждена приказом Минздрава РФ</div>
	<div>от 30.12.2002 г. №413</div>
</td>
</tr></table>

<div style="text-align: center; font-weight: bold;">
    СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО ИЗ СТАЦИОНАРА<br>
    круглосуточного пребывания, дневного стационара при больничном<br>
    учреждении, дневного стационара при  амбулаторно-поликлиническом<br>
    учреждении, стационара на дому
</div>


<table style="width: 50%; margin: 0 auto;"><tr>
<td style="width: 60%; font-weight: bold; text-align:right; padding-right:10px;">№ медицинской карты</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{EvnPS_NumCard}</td>
<td style="width: 20%;"></td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 15%;">1. Код пациента</td>
<td style="width: 15%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
<td style="width: 10%;">2. Ф.И.О.</td>
<td style="width: 60%; border-bottom: 1px solid #000;"><b>{Person_Fio}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 8%;">3. Пол</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{Sex_Name}</td>
<td style="width: 15%;">4. Дата рождения</td>
<td style="width: 45%; border-bottom: 1px solid #000; text-align: center;">{Person_Birthday}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">5. Документ, удостов. личность: название, серия, номер</td>
<td style="width: 75%; border-bottom: 1px solid #000;">{DocumentType_Name} {Document_Ser} {Document_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td>6. Адрес, регистрация по месту жительства: <div style="border-bottom: 1px solid #000; font-weight: bold;">{UAddress_Name}</div></td>
</tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">7. Код территории проживания</td>
<td style="width: 30%; border-bottom: 1px solid #000;">{Person_OKATO}</td>
<td style="width: 10%; text-align:right;">Житель</td>
<td style="width: 35%;">&nbsp;&nbsp; 1-<span class="val_1">город</span>, 2-<span class="val_2">село</span> <div class="selector">{KLAreaType_id}</div></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 30%;">8. Страховой полис (серия, номер):</td>
<td style="width: 70%; border-bottom: 1px solid #000;">{Polis_Ser} {Polis_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">Выдан кем:</td>
<td style="width: 55%; border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
<td style="width: 10%; text-align:right;">Код терр:</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;&nbsp;{OrgSmo_OKATO}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 12%;">9. Вид оплаты</td>
<td style="width: 87%; border-bottom: 1px solid #000;">{PayType_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 22%;">10. Социальный статус</td>
<td style="width: 78%; border-bottom: 1px solid #000;">{SocStatus_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 22%;">11. Категории льготности</td>
<td style="width: 78%;">
1-<span class="val_1">инвалид ВОВ</span>; 2-<span class="val_2">участник ВОВ</span>; 3-<span class="val_3">воин-интернационалист</span>;
<?php foreach($PrivilegeType as $code) {echo '<div class="selector">'.$code.'</div>';} ?>
</td></tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 100%;">
4-<span class="val_4">лицо, подвергшееся радиационному облучению</span>; 5-<span class="val_5">в т.ч. в Чернобыле</span>; 6-<span class="val_6">инв. I гр.</span>; 7-<span class="val_7">инв. II гр.</span>;
<?php foreach($PrivilegeType as $code) {echo '<div class="selector">'.$code.'</div>';} ?>
</td></tr>
<tr><td>
8-<span class="val_8">инв. III гр.</span>; 9-<span class="val_9">ребенок-инвалид</span>; 10-<span class="val_10">инвалид с детства</span>; 11-<span class="val_11">прочие</span>.
<?php foreach($PrivilegeType as $code) {echo '<div class="selector">'.$code.'</div>';} ?>
</td></tr>
</table>


<table style="width: 100%;"><tr>
<td style="width: 15%;">12. Кем направлен</td>
<td style="width: 29%; border-bottom: 1px solid #000;">{PrehospOrg_Name}</td>
<td style="width: 8%; text-align: right;">№ напр.</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{EvnDirection_Num}</td>
<td style="width: 8%; text-align: right;">Дата</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{EvnDirection_SetDT}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">13. Кем доставлен</td>
<td style="width: 27%; border-bottom: 1px solid #000;">{PrehospArrive_Name}</td>
<td style="width: 5%; text-align: right;">Код</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{EvnPS_CodeConv}</td>
<td style="width: 13%; text-align: right;">Номер наряда</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{EvnPS_NumConv}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 32%;">14. Диагноз направившего учреждения</td>
<td style="width: 68%; border-bottom: 1px solid #000;">{PrehospDiag_Name}</td>
</tr><tr>
<td>15. Диагноз приемного отделения</td>
<td style="border-bottom: 1px solid #000;">{AdmitDiag_Name}</td>
</tr><tr>
<td>16. Доставлен в состоянии опьянения</td>
<td style="border-bottom: 1px solid #000;">{PrehospToxic_Name}</td>
</tr></table>

<table style="width: 100%;">
	<tr>
		<td>
			17. Госпитализирован&nbsp;по&nbsp;поводу&nbsp;данного&nbsp;заболевания&nbsp;в&nbsp;текущем&nbsp;году:
			1-<span class="val_1">первично</span>; 2-<span class="val_2">повторно</span>; <div class="selector">{IsFirst}</div>
			3-<span class="val_3">по экстренным показаниям</span>; 4-<span class="val_4">в плановом порядке</span>. <div class="selector">{PregospType_sCode}</div>
		</td>
	</tr>
</table>

<table style="width: 100%;">
	<tr>
		<td>18. Доставлен&nbsp;в&nbsp;стационар&nbsp;от&nbsp;начала&nbsp;заболевания&nbsp;(получения&nbsp;травмы):
			1-<span class="val_1">в первые 6 часов</span>; 2-<span class="val_2">в теч. 7 – 24 часов</span>; 3-<span class="val_3">позднее 24-х часов</span>. <div class="selector">{EvnPS_TimeDeseaseType}</div></td>
	</tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">19. Травма</td>
<td style="width: 90%; border-bottom: 1px solid #000;">{PrehospTrauma_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 40%;">20. Дата и время поступления в приемное отделение</td>
<td style="width: 35%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
<td style="width: 10%; text-align:right;">Время</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">21. Название отделения</td>
<td style="width: 35%; border-bottom: 1px solid #000;">{LpuSectionFirst_Name}</td>
<td style="width: 15%; text-align:right;">Дата поступления</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{EvnSectionFirst_setDate}</td>
<td style="width: 10%; text-align:right;">Время</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{EvnSectionFirst_setTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 35%;">Подпись врача приемного отделения</td>
<td style="width: 30%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; text-align:right;">Код</td>
<td style="width: 25%; border-bottom: 1px solid #000;">{MedPersonal_TabCode}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 22%;">22. Дата выписки (смерти)</td>
<td style="width: 40%; border-bottom: 1px solid #000;">{EvnPS_disDate}</td>
<td style="width: 13%; text-align:right;">Время</td>
<td style="width: 25%; border-bottom: 1px solid #000;">{EvnPS_disTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 45%;">23. Продолжительность госпитализации (койко-дней)</td>
<td style="width: 25%; border-bottom: 1px solid #000;">{EvnPS_KoikoDni}</td>
<td style="width: 30%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;">
<tr><td style="width: 20%;">24. Исход госпитализации:</td>
<!--<td style="width: 35%; border-bottom: 1px solid #000;">{LeaveType_Name}</td>-->
<td style="width: 80%;">
	1-<span class="val_1">выписан</span>; 2-<span class="val_2">в т.ч. в дневной стационар</span>; 3-<span class="val_3">в круглосуточный стационар</span>; 4-<span class="val_4">переведен в другой стационар</span>;<div class="selector">{LeaveType_sCode}</div>
</td>
</tr></table>
<table style="width: 100%;">
<tr>
	<td>24.1.&nbsp;Результат&nbsp;госпитализации:
	1-<span class="val_1">выздоровление</span>; 2-<span class="val_2">улучшение</span>; 3-<span class="val_3">без перемен</span>; 4-<span class="val_4">ухудшение</span>; 5-<span class="val_5">здоров</span>; 6-<span class="val_6">умер</span>; <div class="selector">{ResultDesease_sCode}</div></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 32%;">25. Листок нетрудоспособности: открыт</td>
<td style="width: 28%; border-bottom: 1px solid #000;">{EvnStick_setDate}</td>
<td style="width: 12%;">закрыт</td>
<td style="width: 28%; border-bottom: 1px solid #000;">{EvnStick_disDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 32%;">25.1. По уходу за больным. Полных лет:</td>
<td style="width: 28%; border-bottom: 1px solid #000;">{PersonCare_Age}</td>
<td style="width: 12%;">Пол: </td>
<td style="width: 28%; border-bottom: 1px solid #000;">{PersonCare_SexName}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td>26. Движение пациента по отделениям</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;">
<tr>
<th style="width: 5%;">№</th>
<th style="width: 20%;">Код отделения</th>
<th style="width: 10%;">Профиль коек</th>
<th style="width: 5%;">Код врача</th>
<th style="width: 10%;">Дата поступления</th>
<th style="width: 10%;">Дата выписки, перевода<sup>3</sup></th>
<th style="width: 10%;">Код диагноза по МКБ</th>
<th style="width: 10%;">Код медицинского стандарта<sup>1</sup></th>
<th style="width: 10%;">Код прерванного случая<sup>2</sup></th>
<th style="width: 10%;">Вид оплаты</th>
</tr>
{EvnSectionData}
<tr>
<td class="cell">{Index}</td>
<td class="cell">{LpuSection_Name}</td>
<td class="cell">{LpuSectionBedProfile_Name}</td>
<td class="cell">{MedPersonal_Code}</td>
<td class="cell">{EvnSection_setDT}</td>
<td class="cell">{EvnSection_disDT}</td>
<td class="cell">{EvnSectionDiagOsn_Code}</td>
<td class="cell">{EvnSectionMesOsn_Code}</td>
<td class="cell"></td>
<td class="cell">{EvnSectionPayType_Name}</td>
</tr>
{/EvnSectionData}
</table>

<table style="width: 100%; page-break-before: always;"><tr>
<td>27. Хирургические операции (обозначить: основную операцию, использование спец. аппаратуры)</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 5%;" rowspan='2'>Дата, час</th>
<th style="width: 10%;" rowspan='2'>Код хирурга</th>
<th style="width: 10%;" rowspan='2'>Код отделения</th>
<th style="width: 15%;" colspan='2'>Операция</th>
<th style="width: 15%;" colspan='2'>Осложнение</th>
<th style="width: 15%;" rowspan='2'>Анестезия *</th>
<th style="width: 5%;" colspan='3'>Использ. спец. аппаратуры</th>
<th style="width: 5%;" rowspan='2'>Вид оплаты</th>
</tr>
<tr> 
    <th>Наименование</th>
	<th>Код</th>
    <th>Наименование</th>
	<th>Код</th>
	<th>энд.</th>
	<th>лазер</th>
	<th>криог.</th>
</tr>
{EvnUslugaOperData}
<tr class="underline">
<td class="cell">{EvnUslugaOper_setDT}</td>
<td class="cell">{EvnUslugaOperMedPersonal_Code}</td>
<td class="cell">{EvnUslugaOperLpuSection_Code}</td>
<td class="cell">{EvnUslugaOper_Name}</td>
<td class="cell">{EvnUslugaOper_Code}</td>
<td class="cell">{AggType_Name}</td>
<td class="cell">{AggType_Code}</td>
<td class="cell">{EvnUslugaOperAnesthesiaClass_Name}</td>
<td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsEndoskop}&nbsp;</td>
<td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsLazer}&nbsp;</td>
<td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsKriogen}&nbsp;</td>
<td class="cell">{EvnUslugaOperPayType_Name}</td>
</tr>
{/EvnUslugaOperData}
</table>

<table style="width: 100%;"><tr>
<td style="width: 20%;">28. Обследован: RW 1</td>
<td style="width: 15%; border-bottom: 1px solid #000;">{IsRW}</td>
<td style="width: 18%;">Обследован: AIDS 2</td>
<td style="width: 15%; border-bottom: 1px solid #000;">{IsAIDS}</td>
<td style="width: 32%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td>29. Диагноз стационара (при выписке)</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 20%;" class="cell">&nbsp;</th>
<th style="width: 19%;" class="cell">Основн. заболевание</th>
<th style="width: 7%;" class="cell">Код МКБ</th>
<th style="width: 20%;" class="cell">Осложнение</th>
<th style="width: 7%;" class="cell">Код МКБ</th>
<th style="width: 20%;" class="cell">Сопутствующее заболевание</th>
<th style="width: 7%;" class="cell">Код МКБ</th>
</tr><tr>
<td class="cell">Клинич. заключит.</td>
<td class="cell">{LeaveDiag_Name}</td>
<td class="cell">{LeaveDiag_Code}</td>
<td class="cell">{LeaveDiagAgg_Name}</td>
<td class="cell">{LeaveDiagAgg_Code}</td>
<td class="cell">{LeaveDiagSop_Name}</td>
<td class="cell">{LeaveDiagSop_Code}</td>
</tr><tr>
<td class="cell">Пат.-анатомический</td>
<td class="cell">{AnatomDiag_Name}</td>
<td class="cell">{AnatomDiag_Code}</td>
<td class="cell">{AnatomDiagAgg_Name}</td>
<td class="cell">{AnatomDiagAgg_Code}</td>
<td class="cell">{AnatomDiagSop_Name}</td>
<td class="cell">{AnatomDiagSop_Code}</td>
</tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 40%;">30. В случае смерти указать основную причину</td>
<td style="width: 40%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 10%;">код по МКБ</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 30%;">31. Дефекты догоспитального этапа</td>
<td style="width: 70%; border-bottom: 1px solid #000;">{EvnPS_IsDiagMismatch} {EvnPS_IsImperHosp} {EvnPS_IsShortVolume} {EvnPS_IsWrongCure}</td>
</tr></table>

<table style="width: 100%; margin-top:10px;"><tr>
<td style="width: 25%;">Подпись лечащего врача</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 50%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top:10px;"><tr>
<td style="width: 25%;">Подпись заведующего отделением</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 50%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top:20px;"><tr>
<tr><td><sup>1</sup> Проставляется в случае утверждения в субъекте Российской Федерации в установленном порядке.</td></tr>
<tr><td><sup>2</sup> Заполняется при использовании в системе оплаты.</td></tr>
<tr><td><sup>3</sup> При выписке, переводе из отделения реанимации указать время пребывания в часах.</td></tr>
</table>
<script type="text/javascript">activateSelectors();</script>
</body>

</html>