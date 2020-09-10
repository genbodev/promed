<html>
<head>
<title>{EvnPLTemplateBlankTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 10px; width: 800px }
table { border-collapse: collapse; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 12px; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
td {vertical-align: top}
small {font-size: 11px}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 12px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.block {height: 13pt; width: 13pt; border: 1px solid #000}
td {vertical-align: top}
small {font-size: 11px}
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



<table style="width: 100%;">
<tr>
	<td style="width: 40%;">
		<div style="text-align:center; margin-bottom: 10px">Министерство здравоохранения и социального<br />развития Российской Федерации
			<b><i><u><br />{Lpu_Name}
			<br />{LpuAddress}</u></i></b>
		</div>
		Код ОГРН: <b>{Lpu_OGRN}</b><br>
		СНИЛС: <b>{Person_Snils}</b><br>
		№ медицинской карты: <b>{PersonCard_Code}</b>
	</td>
	<td style="text-align: right;">
		Медицинская документация<br />Форма № 025-12/у _______<br />утв. приказом Минздравсоцразвития России<br />от 22.11.2004г. № 255<br>
		<div style="font-size: 14px; margin: 7px"><b>ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</b></div>
		Дата: <b style="width: 100px; display: block; float: right">{TimetableGraf_recDate}</b>
	</td>
</tr>
</table>
	
<table style="width: 100%;">
<tr><td style="width: 40%;">1. Код категории льгот: </td></tr>
<tr><td style="text-align: left;font-size: 14px;">2. Номер страхового полиса ОМС: <b>{OrgSmo_Name} {Polis_Ser} {Polis_Num}</b></td></tr>
<tr><td style="text-align: left;">3. Место работы: <b>{OrgJob_Name} / {OrgUnion_Name} / {Post_Name}</b></td></tr>
</table>
	

<table style="width: 100%;">
	<tr>
		<td style="border: 1px solid">4. Пациент: Ф.И.О. <b><span style="font-size: 14px;">{Person_Fio}</span></b></td>
	</tr>
	<tr>
		<td style="border: 1px solid;font-size: 14px;">
			5. Пол: <b>{Sex_Name}</b> &nbsp;
			6. Дата рождения: <b>{Person_Birthday}</b>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid">7. Документ, удостоверяющий личность: <b>{DocumentType_Name} {Document_Ser} {Document_Num} {Document_begDate}</b></td>
	</tr>
	<tr>
		<td style="border: 1px solid">
			8. Адрес регистрации по месту жительства: <b>{UAddress_Name}</b><br>
			9. Житель: <b>{KLAreaType_Name}</b>
		</td>
	
	</tr>
	<tr>
		<td style="border: 1px solid">10. Социальный статус: <b>{SocStatus_Name}</b></td>
	</tr>
	<tr>
		<td style="border: 1px solid">11. Инвалидность: <b>{PrivilegeType_Name}</b></td>
	</tr>
</table>
	

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; width: 180px">12. Специалист: код </td>
		{MedPersonal_TabCode}
		<td style="border: 1px solid">Ф.И.О. <b>{MSF_Fio}</b></td>
	</tr>		
	<tr>
		<td style="border: 1px solid">13. Специалист: код</td>
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>		
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>
		<td class="block"></td>
		<td style="border: 1px solid">Ф.И.О.</td>
	</tr>	
	<tr>
		<td style="border: 1px solid" colspan="12">14. Вид оплаты: 1 - <span class="val_1">ОМС</span>, 2 - <span class="val_3">бюджет</span>, 3 - <span class="val_5">платные услуги</span>, в т.ч. 4 - <span class="val_2">ДМС</span>, 5 - <span class="val_6">другое</span> <div class="selector">{PayType_Code}</div></td>
	</tr>	
	<tr>
		<td colspan="12" style="border: 1px solid">15. Место обслуживания: 1 - <span class="val_1">поликлиника</span>, 2 - <span class="val_2">на дому</span>, в т.ч.  3 - <span class="val_3">актив</span> <div class="selector">{ServiceType_Code}</div></td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">
			16. Цель посещения: <b>
				1 - <span class="val_1">Лечебно-диагностический</span>, 
				2 - <span class="val_2">Консультативный</span>, 
				3 - <span class="val_3">Диспансерное наблюдение</span>, 
				4 - <span class="val_4">Профилактический</span>, 
				5 - <span class="val_5">Профосмотр</span>, 
				6 - <span class="val_6">Реабилитационный</span>, 
				7 - <span class="val_7">Зубопротезный</span>, 
				8 - <span class="val_8">Протезно-ортопедический</span>, 
				10 - <span class="val_10">Доп.диспансеризация</span>, 
				11 - <span class="val_11">Медико-социальный</span>, 
				9 - <span class="val_9">Прочий</span></b> 
			<div class="selector">{VizitType_Code}</div></td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">17. Законченность случая: закончен - 1, не закончен - 2, продолжение СПО - 3</td>
	</tr>
	<tr>
		<td colspan='12' style="border: 1px solid">
			<small>
				17a. 0 - текущий случай, 1 - выписан с выздоровлением, 2 - выписан с улучшением, 3 - динамическое наблюдение (онкол.б-ные), 4 - направлен на госпитализацию, 5 - направлен в дневной стационар, 6 - направлен в дневной стационар на дому, 7 - переведен к специалисту др. профиля внутри ЛПУ, 8 - направлен в др. ЛПУ, 9 - выдача справки для получения путевки, 
				10 - оформление сан.кур. карты, 15 - неявка на повторный прием, 16 - выписка пациента по собственному желанию, нарушение режима, 17 - профилактический осмотр, 18 - консультация специалистов (по показ.) в стационаре, 19 - летальный исход, 20 - переведен к др. участковому терапевту, 21 - продолжает болеть (> 4 мес.), 22 - диспансерное наблюдение.				
			</small>
		</td>
	</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; width: 200px;">18. Диагноз МКБ-10: </td>
		<td style="border: 1px solid; width: 200px;" ></td>
		<td style="border: 1px solid"></td>
	</tr>		
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; width: 200px;" colspan="19">19. Код мед.услуги (посещение, СМП, КЭС)</td>
		<td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
	<tr>
		<td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block"></td><td class="block"></td><td class="block"></td>
		<td class="block" style="border-width: 2px"></td>
		<td class="block"></td>
	</tr>	
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid; border-right: none; width: 160px;">20. Характер заболевания: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">острое</span> (+), <span class="val_2">впервые в жизни установленное хроническое</span> (+)<br>
			2 - <span class="val_3">диагноз установлен в прошлом году или ранее</span> (-)
			<div class="selector">{DeseaseTypeSop_Code}</div>
		</td>
	</tr>		
	<tr>
		<td style="border: 1px solid; border-right: none; width: 160px;">21. Диспансерный учёт: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - состоит, 2 - взят, 3 - снят, в т.ч. 1 - выздоровление, 2 - переезд, 3 - перевод, 4 - смерть,<br>
			5 - прочее
		</td>
	</tr>		
</table>
	
<table style="width: 100%; margin-top: -1px;">
	<tr>
		<td style="border: 1px solid; border-right: none; width: 200px;">22. Травма - производственная: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">промышленная</span>, 
			2 - на строительстве, 
			3 - <span class="val_3">дорожно-транспортная</span><br>
			4 - <span class="val_4">сельскохозяйственная</span>, 
			5 - <span class="val_5">прочие</span>
			<div class="selector">{PrehospTrauma_Code}</div>
		</td>	
	</tr>
	<tr>
		<td style="border: 1px solid; border-right: none; width: 200px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; непроизводственная: </td>
		<td style="border: 1px solid; border-left: none;">
			6 - <span class="val_6">бытовая</span>, 
			7 - <span class="val_7">уличная</span>, 
			8 - <span class="val_8">дорожно-транспортная</span>, 
			9 - <span class="val_10">в школе</span>, 
			10 - <span class="val_11">спортивная</span>,<br>
			11 - <span class="val_12">прочие</span>
			<div class="selector">{PrehospTrauma_Code}</div>
		</td>
	</tr>	
	<tr>
		<td style="border: 1px solid;" colspan="2">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 13 - полученная в результате террористических действий
		</td>
	</tr>		
</table>

<table style="width: 100%; margin-top: 3px;">
	<tr>
		<td style="border: 1px solid;" colspan="2">
			23. Документ временной нетрудоспособности: 1-<span class="val_1">открыт</span>, 2-<span class="val_2">закрыт</span> <div class="selector">{EvnStick_Open}</div>;
			серия ____________ № _________________<br/>
			б/лист [&nbsp;&nbsp;&nbsp;&nbsp;]<br/>
			справка [&nbsp;&nbsp;&nbsp;&nbsp;]<br/>
			<table style="width: 100%; margin-top: -20px;">
				<tr>
					<td style="width: 20%;"></td>
					<td style="width: 20%;">&nbsp;</td>
					<td style="width: 20%;">&nbsp;</td>
				</tr>
				<tr>
					<td style="width: 20%;"></td>
					<td style="width: 20%;"><small>дата выдачи</small></td>
					<td style="width: 20%;"><small>дата закрытия</small></td>
					<td style="width: 20%;"><small>врач открывший</small></td>
					<td style="width: 20%;"><small>врач закрывший</small></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid; border-right: none; width: 120px;">23. Причина выдачи: </td>
		<td style="border: 1px solid; border-left: none;">
			1 - <span class="val_1">заболевание</span>, 2 - <span class="val_2">по уходу</span>, 3 - <span class="val_3">карантин</span>, 4 - <span class="val_4">прерывание беременности</span>,<br/>
			5 - <span class="val_5">отпуск по беременности и родам</span>, 6 - <span class="val_6">санаторно-курортное лечение</span><div class="selector">{StickCause}</div>
		</td>
	</tr>	
	</tr>
	<tr>
		<td style="border: 1px solid" colspan="2">
			24.1  по уходу: пол 1-<span class="val_1">муж</span>, 2-<span class="val_2">жен</span><div class="selector">{EvnStick_Sex}</div> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (возраст лица получившего документ в/н) 
		</td>
	</tr>	
</table>

<script type="text/javascript">activateSelectors();</script>

</body>

</html>