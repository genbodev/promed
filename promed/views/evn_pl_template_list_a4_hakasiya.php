<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
    <title>{EvnPLTemplateTitle}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 0px; }
        table { border-collapse: collapse; }
        span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
        th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
        .withBorder { margin-top: 0px; }
        .withBorder td{ border:1px solid #000; }
        .headert td { font-size: 14px; }
    </style>

    <style type="text/css" media="print">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 0px; }
        span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
        th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
        .cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
    </style>

    <style type="text/css">
        div.selector { display:none; }
        div.show_selector { display:none; }
        div.single_selector { display:inline; }
        div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }
        .broken_text { display: inline; padding: 0px; margin: 0px; }
        table.fuck-this-shit tr td {
            padding: 0;
            margin: 0;
            font-size: 8px;
            line-height: 6px;
        }
    </style>

<script type="text/javascript">
	function activateSelectors() {
        var span_arr;
        var j;
		var arr = document.getElementsByTagName("div");
		for(var i = 0; i < arr.length; i++) {
			if (arr[i].className == "selector") {
                span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML) span_arr[j].style.textDecoration = "underline";
				}
			}
			if (arr[i].className == "show_selector") {
				span_arr = arr[i].parentNode.getElementsByTagName("span");
				for(j = 0; j < span_arr.length; j++) {
					if (span_arr[j].className == "val_" + arr[i].innerHTML)
						span_arr[j].style.display = "inline";
					else
						span_arr[j].style.display = "none";
				}
			}
			if (arr[i].className == "single_selector") {
				span_arr = arr[i].getElementsByTagName("span");
				var empty = true;
				for(j = 0; j < span_arr.length; j++) {
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

				for (j = 0; j < words.length; j++) {
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
    <tr><td style="width: 30%; text-align:center;">Министерство здравоохранения и<br />социального<br />развития Российской Федерации<br />{Lpu_Name}<br /><small>(наименование медицинского учреждения)</small><br /><br />{Lpu_Address}<br /><small>(адрес)</small></td><td style="text-align: right;">Медицинская документация<br />Форма № 025-12/у<br />утв. приказом Минздравсоцразвития России<br />от 22.11.2004г. № 255</td></tr>
</table>
<table class='headert' style="width: 100%;">
    <tr><td style="width: 35%; text-align:center;">Код по ОГРН {Lpu_OGRN}</td><td style="text-align: left; font-weight:bold;">Талон амбулаторного пациента</td></tr>
    <tr><td></td><td>№ медицинской карты {PersonCard_Code} дата {EvnPL_setDate}</td></tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="width: 40%;">1. Код категории льгот: {PrivilegeType_Code} </td>
        <td style="text-align: left; font-size: 14px;">2. Номер страхового полиса ОМС {Polis_Ser} {Polis_Num}</td>
        <td>3. СНИЛС {Person_Snils}</td>
    </tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>4. Пациент: код<sup>1</sup> {EvnPL_NumCard}</td><td>ф.и.о. {Person_Fio}</td></tr>
	<tr><td>5. Пол<sup>4</sup> 1-<span class="val_1">муж</span>, 2-<span class="val_2">жен</span> <div class="selector">{Sex_id}</div></td><td style="font-size: 14px;">6. Дата рождения {Person_Birthday}</td></tr>
	<tr><td colspan='2'>7. Документ, удостоверяющий личность (название, серия и номер)<sup>4</sup>: {Person_Docum}</td></tr>
	<tr>
		<td style="border-right: none">8. Адрес регистрации по месту жительства<sup>4</sup>: {UAddress_Name}</td>
		<td style='border-left: none; text-align:right;'>9. Житель<sup>4</sup>: 1-<span class="val_1">город</span>, 2-<span class="val_2">село</span> <div class="selector">{KlareaType_id}</div></td>
	</tr>
	<tr><td colspan='2'>10. Социальный статус, в т.ч. занятость: 1-<span class="val_0">дошкольник</span>; 1.1-<span class="val_1">организован</span>; 1.2-<span class="val_2">не организован</span>; 2-<span class="val_3">учащийся</span>; 3-<span class="val_4">работающий</span>; 4-<span class="val_5">неработающий</span>; 5-<span class="val_6">пенсионер</span>; 6-<span class="val_11"><span class="val_12"><span class="val_13"><span class="val_14"><span class="val_15"><span class="val_16"><span class="val_17"><span class="val_18"><span class="val_19"><span class="val_20">военнослужащий</span></span></span></span></span></span></span></span></span></span>, код {SocStatus_Code}, 7-<span class="val_9">член семьи военнослужащего</span>, 8-<span class="val_7">без определенного места жительства</span> <div class="selector">{SocStatus_Code}</div></td></tr>
	<tr><td colspan='2'>11. Инвалидность: 1-<span style="{PrivilegeType_1gr}">Iгр.</span>, 2-<span style="{PrivilegeType_2gr}">IIгр.</span>, 3-<span style="{PrivilegeType_3gr}">IIIгр.</span>, 4-<span class="val_0">установлена впервые в жизни</span>, 5-<span class="val_0">степень инвалидности:   </span> &nbsp;&nbsp;, 6-<span style="{PrivilegeType_child}">ребенок-инвалид</span>, 7-<span style="{PrivilegeType_fromBirth}">инвалид с детства</span>, 8-<span class="val_0">снята</span></td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td style="width: 40%;">12. Специалист: код {MedPersonal_TabCode}</td><td style="text-align: left;">ф.и.о. {MedPersonal_Fio}</td></tr>
	<tr><td>13. Специалист: код<sup>2</sup></td><td style="text-align: left;">ф.и.о.</td></tr>
	<tr><td colspan='2'>14. Вид оплаты: 1-<span class="val_1">ОМС</span>, 2-<span class="val_3">бюджет</span>, 3-<span class="val_5">платные услуги</span>, в т.ч. 4-<span class="val_2">ДМС</span>, 5-<span class="val_6">другое</span> <div class="selector">{PayType_Code}</div></td></tr>
	<tr><td colspan='2'>15. Место обслуживания: 1-<span class="val_1">поликлиника</span>, 2-<span class="val_2">на дому</span>, в т.ч.  3-<span class="val_3">актив</span> <div class="selector">{ServiceType_Code}</div></td></tr>
	<tr><td colspan='2'>16. Цель посещения: 1-<span class="val_1">заболевание</span>, 2-<span class="val_2">профосмотр</span>, 3-<span class="val_3">патронаж</span>, 4-<span class="val_4">другое</span> <div class="selector">{VizitType_Code}</div></td></tr>
	<tr><td colspan='2'>17. Результат обращения<sup>5</sup>: случай закончен: 1-<span class="val_1">выздоровл</span>, 2-<span class="val_2">улучшение</span>, 3-<span class="val_3">динамическое забол</span> <div class="selector">{ResultClass_Code}</div>., направлен: 4-<span class="val_1">на госпитализацию</span>, 5-<span class="val_3">в дневной стационар</span>, 6-<span class="val_5">стационар на дому</span>, 7-<span class="val_6">на консультацию</span>, 8-<span class="val_9">на консультацию в др. ЛПУ</span>, 9-<span class="val_9">справка для получения путевки</span>, 10-<span class="val_7">санаторно-курортная карта</span> <div class="selector">{DirectType_Code}</div></td></tr>
</table>
<table class="fuck-this-shit" style="width: 100%;">
	<tr><td><sup>1</sup> - при использовании кода, принятого в ЛПУ</td>
		<td><sup>2</sup> - заполняется при учёте работы среднего мед.персонала</td></tr>
	<tr><td><sup>3</sup> - при оплате: по посещению подставляется код посещения или стандарта медицинской помощи (СМП), КЭС</td>
		<td><sup>4</sup> - заполняется при разовом обращении пациента (например, иногородний)</td></tr>
	<tr><td><sup>5</sup> - заполняется при последнем посещении по данному случаю</td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>18. Диагноз код МКБ: {FinalDiag_Code}</td></tr>
	<tr><td>19. Код мед.услуги (посещения), СМП, КЭС <sup>3</sup></td></tr>
	<tr><td>20. Характер заболевания: 1-<span class="val_3">острое</span>, 2-<span class="val_1">впервые в жизни установленное хроническое</span> 3-<span class="val_2">диагноз установлен в предыдущ. году или ранее.</span> <div class="selector">{DeseaseType_id}</div></td></tr>
    <tr><td>21. Диспансерный учёт: 1-<span class="val_1">состоит</span>, 2-взят, 3-снят, в т.ч. 4-по выздоровлению<div class="selector">{PersonDisp}</div></td></tr>
	<tr><td>22. Травма: производственная: 1-<span style="{PrehospTrauma_1}">промышленная</span>, 2-<span style="{PrehospTrauma_2}">транспортная</span>, в т.ч. 3-<span style="{PrehospTrauma_21}">ДТП</span>, 4-<span style="{PrehospTrauma_3}">сельско-хозяйственная</span>, 5-<span style="{PrehospTrauma_4}">прочая</span> <br />не производственная: 6-<span style="{PrehospTrauma_6}">бытовая</span>, 7-<span style="{PrehospTrauma_7}">уличная</span>, 8-<span style="{PrehospTrauma_8}">транспортная</span>, в т.ч.9-<span style="{PrehospTrauma_81}">ДТП</span>, 10-<span style="{PrehospTrauma_9}">школьная</span>, 11-<span style="{PrehospTrauma_10}">спорт</span>, 12-<span style="{PrehospTrauma_11}">прочие</span><br />13 - полученная в результате террористических действий</td>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>23. Диагноз код: {DiagSop_Code}</td></tr>
	<tr><td>24. Код мед.услуги (посещения), СМП, КЭС <sup>3</sup></td></tr>
	<tr><td>25. Характер заболевания: 1-<span class="val_1">острое</span>, 2-<span class="val_2">впервые в жизни установленное хроническое</span> 3-<span class="val_3">диагноз установлен в предыдущ. году или ранее.</span> <div class="selector">{DeseaseTypeSop_Code}</div></td></tr>
	<tr><td>26. Диспансерный учёт: 1-<span class="val_1">состоит</span>, 2-взят, 3-снят, в т.ч. 4-по выздоровлению<div class="selector">{PersonDispSop}</div></td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td colspan='3'>27. Заполняется только при изменении диагноза: ранее зарегистрированный диагноз</td></tr>
	<tr><td width='60%'></td><td>Код МКБ-10:</td><td>Дата регистрации изменяемого диагноза:</td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>28. Документ временной нетрудоспособности: 1-<span class="val_1">открыт {EvnStick_begDate}</span>, 2-<span class="val_2">закрыт {EvnStick_endDate}</span>. <div class="selector">{EvnStick_Open}</div></td></tr>
	<tr><td>29. Причина выдачи: 1-<span style="{StickCause_1}">заболевание</span>, 2-<span style="{StickCause_2}">по уходу</span>, 3-<span style="{StickCause_3}">карантин</span>, 4-<span style="{StickCause_4}">прерывание беременности</span>, 5-<span style="{StickCause_5}">отпуск по беременности и родам</span>, 6-<span style="{StickCause_6}">санаторно-курортное лечение</span></td></tr>
	<tr><td>29.1. по уходу: пол 1-<span style="{EvnStick_Sex1}">муж</span>, 2-<span style="{EvnStick_Sex2}">жен</span> (возраст лица получившего документ в/н {EvnStick_Age})</td></tr>
	<tr><td>30. Рецептурный бланк: серия и №, дата выписки 30.1 _____________ 30.2 _____________ 30.3 _____________ 30.4 _____________ </td></tr>
</table>
<script type="text/javascript">activateSelectors();</script>
</body>

</html>