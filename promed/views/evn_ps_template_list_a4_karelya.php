<html xmlns="http://www.w3.org/1999/html">
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
    <td style="width: 70%; vertical-align: top;">Министерство здравоохранения РФ<br />Российской Федерации<br />{Lpu_Name}</td>
    <td style="width: 30%; vertical-align: top;">
        <div>МЕДИЦИНСКАЯ ДОКУМЕНТАЦИЯ</div><div></div> Форма № 066/у-02<div>
            <div>Утверждена приказом Министерства здравоохранения<div>
                <div>и социального развития Республики Карелия от 23.07.2013 г. №1498<div>
    </td>
</tr></table>

<div style="text-align: center; font-weight: bold;">СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО ИЗ СТАЦИОНАРА</div>

<table style="width: 50%; margin: 0 auto;"><tr>
    <td style="width: 60%; font-weight: bold; text-align:right; padding-right:10px;">№ медицинской карты</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">{EvnPS_NumCard}</td>
    <td style="width: 20%;"></td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 25%;">1. Код пациента</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 25%;">2. Ф.И.О.</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 25%;">3. Пол</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{Sex_Name}</td>
    <td style="width: 25%;">4. Дата рождения</td>
    <td style="width: 25%; border-bottom: 1px solid #000; text-align: center;">{Person_Birthday}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">5. Документ, удостов. личность: название, серия, номер</td>
    <td style="width: 55%; border-bottom: 1px solid #000;">{DocumentType_Name} {Document_Ser} {Document_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>6. Адрес регистрации по месту жительства: <div style="border-bottom: 1px solid #000; font-weight: bold;">{UAddress_Name}</div></td>
</tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 25%;">7. Код территории проживания</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{Person_OKATO}</td>
    <td style="width: 25%; text-align:right;">Житель</td>
    <td style="width: 25%;">&nbsp;&nbsp; 1-<span class="val_1">город</span>, 2-<span class="val_2">село</span> <div class="selector">{KLAreaType_id}</div></td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">8. Страховой полис (серия, номер):</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{Polis_Ser} {Polis_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">Выдан кем:</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
    <td style="width: 20%; text-align:right;">Код терр:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;&nbsp;{OMSSprTerr_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">9. Вид оплаты</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{PayType_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">10. Социальный статус</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{SocStatus_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">11. Категории льготности</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{PrivilegeType_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">12. Кем направлен</td>
    <td style="width: 51%; border-bottom: 1px solid #000;">{PrehospOrg_Name}</td>
    <td style="width: 2%;"></td>
    <td style="width: 3%;">№ напр.</td>
    <td style="width: 5%; border-bottom: 1px solid #000;">{EvnDirection_Num}</td>
    <td style="width: 2%;"></td>
    <td style="width: 3%;">Дата</td>
    <td style="width: 5%; border-bottom: 1px solid #000;">{EvnDirection_SetDT}</td>
    <td style="width: 2%;"></td>
    <td style="width: 3%;">Призывник</td>
    <td style="width: 2%;"></td>
    <td style="width: 7%;"><span class="val_1">1-да</span>, <span class="val_2">2-нет</span><div class="selector">{IsRecruit}</div></td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">13. Кем доставлен</td>
    <td style="width: 40%; border-bottom: 1px solid #000;">{PrehospArrive_Name}</td>
    <td style="width: 4%;"></td>
    <td style="width: 2%;">Код</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_CodeConv}</td>
    <td style="width: 8%;"></td>
    <td style="width: 6%;">Номер наряда</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_NumConv}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">14. Диагноз направившего учреждения</td>
    <td style="width: 65%; border-bottom: 1px solid #000;">{PrehospDiag_Name}</td>
</tr><tr>
    <td>15. Диагноз приемного отделения</td>
    <td style="border-bottom: 1px solid #000;">{AdmitDiag_Name}</td>
</tr><tr>
    <td>16. Доставлен в состоянии опьянения</td>
    <td style="border-bottom: 1px solid #000;">{PrehospToxic_Name}</td>
</tr></table>

<div>
    17. Госпитализирован по поводу данного заболевания в текущем году:<br>
    <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;17.1 &nbsp;&nbsp;&nbsp; <span class="val_1">1 - первично</span>, &nbsp;&nbsp; <span class="val_2">2 - повторно</span><div class="selector">{IsFirst}</div></div>
    <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;17.2 &nbsp;&nbsp;&nbsp; <span class="val_2">1 - по экстренным показаниям</span>, &nbsp;&nbsp; <span class="val_1">2 - в плановом порядке</span><div class="selector">{PrehospType_Code}</div></div>
</div>
<div>
    18. Доставлен в стационар от начала заболевания (получения травмы): &nbsp;&nbsp;&nbsp;&nbsp;<span class="val_1">1 - в первые 6 час.;</span>&nbsp;&nbsp;<span class="val_2">2 - в теч. 7-24 час.;</span>&nbsp;&nbsp;<span class="val_3">3 - позднее 24 час.</span><div class="selector">{EvnPS_TimeDeseaseType}</div>
</div>

<table style="width: 100%;"><tr>
    <td style="width: 10%;">19. Травма</td>
    <td style="width: 90%; border-bottom: 1px solid #000;">{PrehospTrauma_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">20. Дата поступления в приемное отделение</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
    <td style="width: 15%; text-align:right;">Время</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">21. Название отделения</td>
    <td style="width: 55%; border-bottom: 1px solid #000;">{LpuSectionFirst_Name}</td>
    <td style="width: 2%;"></td>
    <td style="width: 6%;">Профиль койки</td>
    <td style="width: 22%; border-bottom: 1px solid #000;">{LpuSectionBedProfile_Name_Beg}</td>
	</tr>
</table>
<table style="width: 100%;">
	<tr>
   		<td style="width: 15%;"></td>
    	<td style="width: 7%;">Дата поступления</td>
    	<td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setDate}</td>
    	<td style="width: 10%;"></td>
    	<td style="width: 5%;">Время</td>
    	<td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setTime}</td>
    	<td style="width: 33%;"></td>
	</tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="width: 15%;"></td>
        <td style="width: 12%;">Подпись врача приемного отделения</td>
        <td style="width: 10%; border-bottom: 1px solid #000;"></td>
        <td style="width: 10%;"></td>
        <td style="width: 5%;">Код</td>
        <td style="width: 15%; border-bottom: 1px solid #000;">{MPFirst_Code}</td>
        <td style="width: 33%;"></td>
    </tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">22. Дата выписки (смерти)</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{EvnPS_disDate}</td>
    <td style="width: 15%;">Время</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{EvnPS_disTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">23. Продолжительность госпитализации (койко-дней)</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{EvnPS_KoikoDni}</td>
    <td style="width: 30%;">&nbsp;</td>
</tr></table>
<br>
<div> 24. Исход госпитализации: <i>стационар круглосут. пребывания:</i> &nbsp;&nbsp;&nbsp;<span class="val_101">101 - выздоровление;</span> <span class="val_102">102 - улучшение;</span> <span class="val_103">103 - без перемен;</span> <span class="val_104">104 - ухудшение.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <i>дневной стационар, стационар на дому:</i>&nbsp;&nbsp;&nbsp; <span class="val_201">201 - выздоровление;</span> <span class="val_202">202 - улучшение;</span> <span class="val_203">203 - без перемен;></span> <span class="val_204">204 - ухудшение</span>. <div class="selector">{ResultDesease_Code}</div></div>
<br>
<div>
	24.1 Результат госпитализации: <i>стационар круглосут. пребывания:</i> &nbsp;&nbsp;&nbsp;<span class="val_101"> 101 - выписан; </span> <span class="val_102">102 - переведен в др. ЛПУ ___________;</span> <span class="val_103">103 - выписан в дневной стационар;</span>
	<span class="val_105">105 - умер;</span> <span class="val_106">106 - умер в приемном покое;</span> <span class="val_107">107 - лечение прервано по инициативе пациента;</span> <span class="val_108">108 - лечение прервано по инициативе ЛПУ;</span> <span class="val_110">110 - самовольно прерванное лечение.</span>
    &nbsp;&nbsp;&nbsp;<i>дневной стационар, стационар на дому:</i> &nbsp;&nbsp;&nbsp;<span class="val_201">201 - выписан;</span> <span class="val_202">202 - переведен в др. ЛПУ ___________;</span> <span class="val_203">203 - переведен в круглосуточный стационар;</span> <span class="val_205">205 - умер;</span> <span class="val_206">206 - умер в приемном покое;</span> <span class="val_207">207 - лечение прервано по инициативе пациента;</span> <span class="val_208">208 - лечение прервано по инициативе ЛПУ;</span><div class="selector">{LeaveType_Code}</div>
</div>
<br>
<div>
	24.2 Причина досрочной выписки: <i>стационар круглосут. пребывания:</i> &nbsp;&nbsp;&nbsp; <span class="val_11">1.1 - коронарография;</span> <span class="val_12">1.2 - лапароскопическая операция;</span> <span class="val_13">1.3 - проведение химиотерапии больным гематологического профиля;</span>
	<span class="val_14">1.4 - литотрипсия;</span> <span class="val_15">1.5 - проведение антицитокиновой терапии;</span> <span class="val_16">1.6 - прочие случаи с коротким сроком лечения включенные в тарифное соглашение;</span> <span class="val_21">2.1 - снятие интоксикации;</span> <span class="val_22">2.2 - снятие острого отравления алкоголем;</span>
	<span class="val_23">2.3 - снятие острого отравления другими ядами;</span> <span class="val_24">2.4 - купирование болевого синдрома;</span> <span class="val_25">2.5 - восстановление сердечного ритма;</span> <span class="val_26">2.6 - снятие прочих острых явлений;</span> <span class="val_27">2.7 - госпитализация больных, требующих наблюдения в течение от 1 до 3 суток;</span>
	<span class="val_28">2.8 - аборт без осложнений;</span> <span class="val_29">2.9 - карантин.</span>
    &nbsp;&nbsp;&nbsp;<i>дневной стационар, стационар на дому:</i> &nbsp;&nbsp;&nbsp;<span class="val_27">2.7 - госпитализация больных, требующих наблюдения в течение от одних до трех суток;</span><span class="val_28">2.8 - аборт без осложнений;</span> <span class="val_29">2.9 - карантин;</span>
	<span class="val_210">2.10 - досрочная выписка по вине больного за нарушение режима;</span> <span class="val_211">2.11 - досрочная выписка по просьбе больного или родственников;</span> <span class="val_212">2.12 - самовольный уход пациента.</span><div class="selector">{LeaveCause_Code}</div>
</div>
<br>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">25. Листок нетрудоспособности: открыт</td>
    <td style="width: 40%; border-bottom: 1px solid #000;">{EvnStick_setDate}</td>
    <td style="width: 10%;">закрыт</td>
    <td style="width: 30%; border-bottom: 1px solid #000;">{EvnStick_disDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 40%;">25.1. По уходу за больным. Полных лет:</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">{PersonCare_Age}</td>
    <td style="width: 20%;">Пол: <span class="val_1">муж - 1</span>; <span class="val_2">жен - 2</span><div class="selector">{PersonCare_SexId}</div></td>
    <td style="width: 20%;">&nbsp;</td>
</tr></table>



<table style="width: 100%;"><tr>
    <td>26. Хирургические операции</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
    <th style="width: 5%;" rowspan='2'>№</th>
    <th style="width: 10%;" rowspan='2'>Дата, час</th>
    <th style="width: 10%;" rowspan='2'>Длит. опер.</th>
    <th style="width: 15%;" colspan='2'>Операция</th>
    <th style="width: 15%;" colspan='2'>Осложнение</th>
    <th style="width: 15%;" rowspan='2'>Вид анестезии</th>
    <th style="width: 5%;" colspan='4' rowspan='2'>Использование спец. аппаратуры(методик): 1-эндоскоп., 2-лазерн.,3-криог.,4-микрохир</th>
    <th style="width: 5%;" rowspan='2'>Вид оплаты</th>
</tr>
    <tr>
        <th>Наименование</th>
        <th>Код</th>
        <th>Опер.</th>
        <th>П/О</th>
    </tr>
    {EvnUslugaOperData}
    <tr class="underline">
        <td class="cell">{Number}</td>
        <td class="cell">{EvnUslugaOper_setDT}</td>
        <td class="cell">{Oper_dur}</td>
        <td class="cell">{EvnUslugaOper_Name}</td>
        <td class="cell">{EvnUslugaOper_Code}</td>
        <td class="cell">{AggType_Name_1}</td>
        <td class="cell">{AggType_Name_2}</td>
        <td class="cell">{EvnUslugaOperAnesthesiaClass_Name}</td>
        <td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsEndoskop}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsLazer}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsKriogen}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{EvnUslugaOper_IsMicrSurg}&nbsp;</td>
        <td class="cell">{EvnUslugaOperPayType_Name}</td>
    </tr>
    {/EvnUslugaOperData}
</table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
    <th style="width: 5%;" rowspan='2'>№</th>
    <th style="width: 7%;" rowspan='2'>Дата, час</th>
    <th style="width: 34%;" colspan='2'>Оперирующий хирург</th>
    <th style="width: 34%;" colspan='2'>Анестезиолог</th>
    <th style="width: 20%;" colspan='4'>Ассистенты</th>
</tr>
    <tr>
        <th>Ф.И.О.</th>
        <th>Код</th>
        <th>Ф.И.О.</th>
        <th>Код</th>
        <th>первый</th>
        <th>код</th>
        <th>второй</th>
        <th>код</th>
    </tr>
    {EvnUslugaOperMedData}
    <tr class="underline">
        <td class="cell">{Number}</td>
        <td class="cell">{EvnUslugaOper_setDT}</td>
        <td class="cell">{OperSurgeon_Name}</td>
        <td class="cell">{OperSurgeon_Code}</td>
        <td class="cell">{OperAnesthetist_Name}</td>
        <td class="cell">{OperAnesthetist_Code}</td>
        <td class="cell" style="text-align: center;">&nbsp;{Oper1Assistant_Name}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{Oper1Assistant_Code}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{Oper2Assistant_Name}&nbsp;</td>
        <td class="cell" style="text-align: center;">&nbsp;{Oper2Assistant_Code}&nbsp;</td>
    </tr>
    {/EvnUslugaOperMedData}
</table>
<br>

<div>27. В случае беременности: <span class="val_1">1-первая</span>; <span class="val_2">2-последующие</span>.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Срок беременности {BirthSpecStac_OutcomPeriod} недель.<div class="selector">{BirthSpecStac_CountPregnancy}</div></div>
<table style="width: 100%;"><tr>
    <td style="width: 20%;">28. Обследован: RW</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 18%;">Обследован: AIDS</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 32%;">&nbsp;</td>
</tr></table>
<div>28а. Результат: 1-отрицательный, 2-положительный. В случае положительного результата: 1 - впервые в жизни, 2 - повторно.</div>
<div>29. Отметка о совместном пребывании в стационаре родителя с ребенком: <span class="val_2">1-да</span>, <span class="val_1">2-нет </span> <div class="selector">{EvnSection_IsAdultEscort}</div>.</div>

<table style="width: 100%;"><tr>
    <td>30. Движение пациента по отделениям</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="width: 2%;">№</th>
        <th style="width: 20%;">Код отделения</th>
        <th style="width: 10%;">Профиль коек</th>
		<th style="width: 16%;">Ф.И.О. врача</th>
        <th style="width: 3%;">Код врача</th>
        <th style="width: 9%;">Дата поступления</th>
        <th style="width: 9%;">Дата выписки, перевода<sup>3</sup></th>
        <th style="width: 8%;">Код диагноза по МКБ</th>
        <th style="width: 8%;">Код медицинского стандарта<sup>1</sup></th>
        <th style="width: 8%;">Код прерванного случая<sup>2</sup></th>
        <th style="width: 7%;">Вид оплаты</th>
    </tr>
    {EvnSectionData}
    <tr>
        <td class="cell">{Index}</td>
        <td class="cell">{LpuSection_CodeName}</td>
        <td class="cell">{LpuSectionBedProfile_Name}</td>
        <td class="cell">{MedPersonal_FIO}</td>
        <td class="cell">{MPCode}</td>
        <td class="cell">{EvnSection_setDT}</td>
        <td class="cell">{EvnSection_disDT}</td>
        <td class="cell">{EvnSectionDiagOsn_Code}</td>
        <td class="cell">{EvnSectionMesOsn_Code}</td>
        <td class="cell"></td>
        <td class="cell">{EvnSectionPayType_Name}</td>
    </tr>
    {/EvnSectionData}
</table>

<table style="width: 100%;"><tr>
    <td>31. Диагноз стационара (при выписке)</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
    <td style="width: 20%;" class="cell">&nbsp;</td>
    <td style="width: 19%;" class="cell">Основн. заболевание</td>
    <td style="width: 7%;" class="cell">Код МКБ</td>
    <td style="width: 20%;" class="cell">Осложнение</td>
    <td style="width: 7%;" class="cell">Код МКБ</td>
    <td style="width: 20%;" class="cell">Сопутствующее заболевание</td>
    <td style="width: 7%;" class="cell">Код МКБ</td>
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
    <td style="width: 40%;">32. В случае смерти указать основную причину</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 5%;">МКБ</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>
<div>
	33. Дефекты догоспитального этапа:
		<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_2">1 - несвоевременность госпитализации;</span><div class="selector">{EvnPS_IsImperHosp}</div></div>
		<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_2">2 - недостаточный объем клинико-диагностического обследования;</span><div class="selector">{EvnPS_IsShortVolume}</div></div>
		<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_2">3 - неправильная тактика лечения;</span><div class="selector">{EvnPS_IsWrongCure}</div></div>
		<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_2">4 - несовпадение диагноза.</span><div class="selector">{EvnPS_IsDiagMismatch}</div></div>
</div>

<table style="width: 100%; margin-top:10px;"><tr>
    <td style="width: 10%;">Лечащий врач</td>
    <td style="width: 30%; border-bottom: 1px solid #000;text-align: center">{MP_Last_FIO}</td>
    <td style="width: 10%; border-bottom: 1px solid #000;text-align: center">{MP_Last_Code}</td>
    <td style="width: 10%; border-bottom: 1px solid #000;text-align: center">&nbsp;</td>
    <td style="width: 40%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top:1px;"><tr>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 30%;text-align: center"><sup>Ф.И.О.</sup></td>
    <td style="width: 10%;text-align: center"><sup>код</sup></td>
    <td style="width: 10%;text-align: center"><sup>подпись</sup></td>
    <td style="width: 40%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top:10px;"><tr>
    <td style="width: 10%;">Заведующий отделением</td>
    <td style="width: 30%; border-bottom: 1px solid #000;text-align: center">{OrgHead_FIO}</td>
    <td style="width: 10%; border-bottom: 1px solid #000;text-align: center">{OrgHead_Code}</td>
    <td style="width: 10%; border-bottom: 1px solid #000;text-align: center">&nbsp;</td>
    <td style="width: 40%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top:1px;"><tr>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 30%;text-align: center"><sup>Ф.И.О.</sup></td>
    <td style="width: 10%;text-align: center"><sup>код</sup></td>
    <td style="width: 10%;text-align: center"><sup>подпись</sup></td>
    <td style="width: 40%;">&nbsp;</td>
</tr></table>

<br>
<br>
<script type="text/javascript">activateSelectors();</script>
</body>

</html>