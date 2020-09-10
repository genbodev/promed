<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
    <title>{EvnPLTemplateBlankTitle}</title>
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
    <tr>
		<td style="width: 30%; text-align:center;">Министерство здравоохранения и<br />социального<br />развития Российской Федерации<br />{Lpu_Name}<br /><small>(наименование медицинского учреждения)</small><br /><br />{LpuAddress}<br /><small>(адрес)</small></td>
		<td style="text-align: right;">Медицинская документация<br />Форма № 025-12/у<br />утв. приказом Минздравсоцразвития России<br />от 22.11.2004г. № 255</td>
	</tr>
</table>
<table class='headert' style="width: 100%;">
    <tr><td style="width: 35%; text-align:center;">Код по ОГРН {Lpu_OGRN}</td><td style="text-align: left; font-weight:bold;">Талон амбулаторного пациента</td></tr>
    <tr><td></td><td>№ медицинской карты <b>{PersonCard_Code}</b> дата <b>{TimetableGraf_recDate}</b></td></tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="width: 40%;">1. Код категории льгот: </td>
        <td style="text-align: left;font-size: 14px;">2. Номер страхового полиса ОМС {Polis_Ser} <b>{Polis_Num} {OrgSmo_Name}</b></td>
        <td>3. СНИЛС {Person_Snils}</td>
    </tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>4. Пациент: код<sup>1</sup> </td><td>ф.и.о. {Person_Fio}</td></tr>
	<tr><td>5. Пол<sup>4</sup> <b>{Sex_Name}</b></td><td style='font-size: 14px;'>6. Дата рождения <b>{Person_Birthday}</b></td></tr>
	<tr><td colspan='2'>7. Документ, удостоверяющий личность (название, серия и номер)<sup>4</sup>: &nbsp;<b>{DocumentType_Name}&nbsp;{Document_Ser}&nbsp;{Document_Num}</b></td></tr>
	<tr>
		<td style="border-right: none">8. Адрес регистрации по месту жительства<sup>4</sup>: <b>{UAddress_Name}</b></td>
		<td style='border-left: none; text-align:right;'>9. Житель<sup>4</sup>: 1-<span class="val_1">город</span>, 2-<span class="val_2">село</span> <div class="selector">{KLAreaType_id}</div></td>
	</tr>
	<tr><td colspan='2'>10. Социальный статус, в т.ч. занятость:
		1-<span class="val_0">дошкольник</span>;
		1.1-<span class="val_1">организован</span>;
		1.2-<span class="val_2">не организован</span>;
		2-<span class="val_3">учащийся</span>;
		3-<span class="val_4">работающий</span>;
		4-<span class="val_5">неработающий</span>;
		5-<span class="val_6">пенсионер</span>;
		6-<span class="val_11">военнослужащий</span>, код &nbsp;&nbsp;&nbsp;,
		7-<span class="val_9">член семьи военнослужащего</span>,
		8-<span class="val_7">без определенного места жительства</span> <div class="selector">{SocStatus_Code}</div></td></tr>
	<tr><td colspan='2'>11. Инвалидность: 1-Iгр., 2-IIгр., 3-IIIгр., 4-установлена впервые в жизни, 5-степень инвалидности &nbsp;&nbsp;, 6-ребенок-инвалид, 7-инвалид с детства, 8-снята</td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td style="width: 40%;">12. Специалист: код {MedPersonal_TabCode}</td><td style="text-align: left;">ф.и.о. <b>{MSF_Fio}</b></td></tr>
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
	<tr><td>18. Диагноз код МКБ: </td></tr>
	<tr><td>19. Код мед.услуги (посещения), СМП, КЭС <sup>3</sup></td></tr>
	<tr><td>20. Характер заболевания: 1-<span class="val_1">острое</span>, <span class="val_2">впервые в жизни установленное хроническое</span> 2-<span class="val_3">диагноз установлен в предыдущ. году или ранее.</span> <div class="selector">0</div></td></tr>
	<tr><td>21. Диспансерный учёт: 1-состоит, 2-взят, 3-снят, в т.ч. 4-по выздоровлению</td></tr>
	<tr><td>22. Травма: производственная: 1-<span style="{PrehospTrauma_1}">промышленная</span>, 2-<span style="{PrehospTrauma_2}">транспортная</span>, в т.ч. 3-<span style="{PrehospTrauma_21}">ДТП</span>, 4-<span style="{PrehospTrauma_3}">сельско-хозяйственная</span>, 5-<span style="{PrehospTrauma_4}">прочая</span> <br />не производственная: 6-<span style="{PrehospTrauma_6}">бытовая</span>, 7-<span style="{PrehospTrauma_7}">уличная</span>, 8-<span style="{PrehospTrauma_8}">транспортная</span>, в т.ч.9-<span style="{PrehospTrauma_81}">ДТП</span>, 10-<span style="{PrehospTrauma_9}">школьная</span>, 11-<span style="{PrehospTrauma_10}">спорт</span>, 12-<span style="{PrehospTrauma_11}">прочие</span><br />13 - полученная в результате террористических действий</td>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>23. Диагноз код: </td></tr>
	<tr><td>24. Код мед.услуги (посещения), СМП, КЭС <sup>3</sup></td></tr>
	<tr><td>25. Характер заболевания: 1-<span class="val_1">острое</span>, <span class="val_2">впервые в жизни установленное хроническое</span> 2-<span class="val_3">диагноз установлен в предыдущ. году или ранее.</span> <div class="selector">{DeseaseTypeSop_Code}</div></td></tr>
	<tr><td>26. Диспансерный учёт: 1-состоит, 2-взят, 3-снят, в т.ч. 4-по выздоровлению</td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td colspan='3'>27. Заполняется только при изменении диагноза: ранее зарегистрированный диагноз</td></tr>
	<tr><td width='60%'></td><td>Код МКБ-10:</td><td>Дата регистрации изменяемого диагноза:</td></tr>
</table>
<table style="width: 100%;" class='withBorder'>
	<tr><td>28. Документ временной нетрудоспособности: 1-<span class="val_1">открыт __________________________</span>, 2-<span class="val_2">закрыт__________________________ </span>. <div class="selector"></div></td></tr>
	<tr><td>29. Причина выдачи: 1-<span style="{StickCause_1}">заболевание</span>, 2-<span style="{StickCause_2}">по уходу</span>, 3-<span style="{StickCause_3}">карантин</span>, 4-<span style="{StickCause_4}">прерывание беременности</span>, 5-<span style="{StickCause_5}">отпуск по беременности и родам</span>, 6-<span style="{StickCause_6}">санаторно-курортное лечение</span></td></tr>
	<tr><td>29.1. по уходу: пол 1-<span style="{EvnStick_Sex1}">муж</span>, 2-<span style="{EvnStick_Sex2}">жен</span> (возраст лица получившего документ в/н) </td></tr>
	<tr><td>30. Рецептурный бланк: серия и №, дата выписки 30.1 _____________ 30.2 _____________ 30.3 _____________ 30.4 _____________ </td></tr>
</table>
<script type="text/javascript">activateSelectors();</script>
</body>

</html>