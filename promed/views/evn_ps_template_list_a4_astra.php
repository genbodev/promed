<html>
<head>
    <title>{EvnPSTemplateTitle}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 10px; padding: 10px;  width: 100%;}
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
        body { margin: 0px; padding: 0px;}
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
        <div>Приложение №5</div>
            <div>к приказу Минздрава России</div>
                <div>от 30.12.2002 г. №413</div>
		<br><br>
		<div>Медицинская документация</div>
        <div>Форма № 066/у-02</div>
    </td>
</tr></table>
<br>
<div style="text-align: center; font-weight: bold;">СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО ИЗ СТАЦИОНАРА<br>круглосуточного пребывания, дневного стационара при больничном учреждении,
дневного стационара при амбулаторно-поликлиническом учреждении, стационара на дому</div>

<table style="width: 50%; margin: 0 auto;"><tr>
    <td style="width: 60%; font-weight: bold; text-align:right; padding-right:10px;">№ медицинской карты</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">{EvnPS_NumCard}</td>
    <td style="width: 20%;"></td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 11%;">1. Код пациента*</td>
    <td style="width: 12%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
    <td style="width: 7%;">2. Ф.И.О.</td>
    <td style="border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 6%;">3. Пол</td>
    <td style="width: 17%"><span class="val_1">муж. - 1;</span><span class="val_2">жен. - 2</span><div class="selector">{Sex_Code}</div> </td>
    <td style="width: 12%;">4. Дата рождения</td>
    <td style="border-bottom: 1px solid #000; text-align: center;">{Person_Birthday}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">5. Документ, удостов. личность: название, серия, номер</td>
    <td style="width: 55%; border-bottom: 1px solid #000;">{DocumentType_Name} {Document_Ser} {Document_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>6. Адрес: регистрация по месту жительства: <div style="border-bottom: 1px solid #000; font-weight: bold;">{UAddress_Name}</div></td>
</tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 25%;">7. Код территории проживания</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{Person_OKATO}</td>
    <td style="width: 25%; text-align:right;">Житель</td>
    <td style="width: 25%;">&nbsp;&nbsp; <span class="val_1">город - 1</span>; <span class="val_2">село - 2</span> <div class="selector">{KLAreaType_id}</div></td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">8. Страховой полис (серия, номер):</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{Polis_Ser} {Polis_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 9%;">Выдан кем:</td>
    <td style="width: 70%; border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
    <td style="width: 9%; text-align:right;">Код терр:</td>
    <td style="border-bottom: 1px solid #000;">&nbsp;&nbsp;{OMSSprTerr_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 13%;">9. Вид оплаты:</td>
    <td><span class="val_1">ОМС - 1</span>; <span class="val_2">Бюджет - 2</span>; <span class="val_3">Платные услуги - 3</span>; <span class="val_4">в т.ч. ДМС - 4</span>; <span class="val_5">Другое - 5</span><div class="selector">{PayType_Code}</div> </td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>
        10. Социальный статус:
		<span class="val_999">дошкольник - 1</span>;
        <span class="val_1">организован - 2</span>;
        <span class="val_2">неорганизован - 3</span>;
        <span class="val_3">учащийся - 4</span>;
        <span class="val_4">работает - 5</span>;
        <span class="val_5">не работает - 6</span>;
        <span class="val_7">БОМЖ - 7</span>;
        <span class="val_6">пенсионер - 8</span>;
        <span class="val_9">военнослужащий - 9</span>; Код - ___________;
        <span class="val_10">Член семьи военнослужащего - 10</span>
		<div class="selector">{SocStatus_Code}</div>
	</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>
		11. Категории льготности:
		<span class = "val_11">инвалил ВОВ - 1</span>;
        <span class = "val_20">участник ВОВ - 2</span>;
        <span class = "val_999">воин-интернационалист - 3</span>;
        <span class = "val_999">лицо, подвергшееся радиационному облучению - 4</span>;
        <span class = "val_91">в т.ч. в Чернобыле - 5</span>;
        <span class = "val_83">инвалиды Iгр - 6</span>;
        <span class = "val_82">инвалиды IIгр - 7</span>;
        <span class = "val_81">инвалиды IIIгр - 8</span>;
        <span class = "val_84">ребенок-инвалид - 9</span>;
        <span class = "val_999">инвалид с детства - 10</span>;
        <span class = "val_999">прочие - 11</span>;
		<div class="selector">{PrivilegeType_Code}</div>
	</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">12. Кем направлен</td>
    <td style="width: 85%; border-bottom: 1px solid #000;">{PrehospOrg_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">13. Кем доставлен</td>
    <td style="width: 85%; border-bottom: 1px solid #000;">{PrehospArrive_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">14. Диагноз направившего учреждения</td>
    <td style="width: 65%; border-bottom: 1px solid #000;">{PrehospDiag_Name}</td>
</tr><tr>
    <td>15. Диагноз приемного отделения</td>
    <td style="border-bottom: 1px solid #000;">{AdmitDiag_Name}</td>
</tr><tr>
    <td>
		16. Доставлен в состоянии опьянения:**
	</td>
	<td>
		<span class="val_1">Алкогольного - 1</span>;
        <span class="val_2">Наркотического - 2</span>;
        <div class="selector">{PrehospToxic_Code}</div>
	</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">17. Госпитализирован по поводу данного заболевания в текущем году:</td>
	<td style="width: 17%;">
		<span class="val_1">первично - 1</span>;
        <span class="val_2">повторно - 2</span>;
		<div class="selector">{IsFirst}</div>
	</td>
	<td>
        <span class="val_2">по экстренным показаниям - 3</span>;
        <span class="val_1">в плановом порядке - 4</span>;
        <div class="selector">{PrehospType_Code}</div>
	</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">18. Доставлен в стационар от начала заболевания (получения травмы):</td>
    <td>
        <span class="val_1">в первые 6 часов - 1</span>;
        <span class="val_2">в теч. 7-24 часов - 2</span>;
        <span class="val_3">позднее 24 часов - 3</span>;
        <div class="selector">{EvnPS_TimeDesease}</div>
	</td>
</tr></table>

<table style="width: 100%;">
    <tr>
        <td style="width: 20%;">19. Травма - производственная: </td>
        <td>
            <span class="val_1">промышленная - 1</span>,
            <span class="val_3">транспортная - 2, в т.ч. ДТП - 3</span>;
            <span class="val_4">c/xоз - 4</span>;
            <span class="val_5">прочие - 5</span>\ж
            <div class="selector">{PrehospTrauma_Code}</div>
        </td>
    </tr>
    <tr>
        <td style="width: 20%; text-align: right;"> - непроизводственная: </td>
        <td>
            <span class="val_6">бытовая - 6</span>,
            <span class="val_7">уличная - 7</span>;
            <span class="val_8">транспортная - 8, в т.ч.ДТП - 9</span>;
            <span class="val_10">школьная - 10</span>;
            <span class="val_11">спортивная 11</span>;
            <span class="val_999">противоправная травма - 12</span>;
            <span class="val_12">прочие - 12</span>;
            <div class="selector">{PrehospTrauma_Code}</div>
        </td>
    </tr>
</table>




<table style="width: 100%;"><tr>
    <td style="width: 35%;">20. Дата поступления в приемное отделение:</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
    <td style="width: 15%; text-align:right;">Время</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">21. Название отделения</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{LpuSectionFirst_Name}</td>
    <td style="width: 15%; text-align:right;">Дата поступления</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnSectionFirst_setDate}</td>
    <td style="width: 15%; text-align:right;">Время</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnSectionFirst_setTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">Подпись врача приемного отделения</td>
    <td style="width: 65%; border-bottom: 1px solid #000;">{FIO_Priem}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 19%;">22. Дата выписки(смерти):</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{EvnPS_disDate}</td>
    <td style="width: 15%;">Время</td>
    <td style="border-bottom: 1px solid #000;">{EvnPS_disTime}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">23. Продолжительность госпитализации (койко-дней)</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{EvnPS_KoikoDni}</td>
    <td style="width: 30%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 10%;">24. Исход</td>
    <td>
		<span class="val_1">выписан - 1</span>;
        <span class="val_2">в т.ч. в дневной стационар - 2</span>;
        <span class="val_3">в круглосуточный стационар - 3</span>;
        <span class="val_4">переведен в другой стационар - 4</span>;
		<div class="selector">{LeaveType_aCode}</div>
	</td>
</tr></table>
<table style="width: 100%;"><tr>
    <td style="width: 15%;">24.1. Результат</td>
	<td>
		<span class="val_1">выздоровление - 1</span>;
		<span class="val_2">улучшение - 2</span>;
		<span class="val_3">без перемен - 3</span>;
		<span class="val_4">ухудшение - 4</span>;
        <span class="val_5">здоров - 5</span>;
        <span class="val_6">умер - 6</span>;
		<div class="selector">{ResultDesease_aCode}</div>
    </td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 40%;">25. Листок нетрудоспособности: открыт</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">{EvnStick_setDate}</td>
    <td style="width: 10%;">закрыт</td>
    <td style="width: 30%; border-bottom: 1px solid #000;">{EvnStick_disDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 40%;">25.1. По уходу за больным. Полных лет:</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">{PersonCare_Age}</td>
    <td style="width: 10%;">Пол: </td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{PersonCare_SexName}</td>
    <td style="width: 20%;">&nbsp;</td>
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
<div style="page-break-after: always;">&nbsp;</div>
<table style="width: 100%;"><tr>
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
        <td class="cell">&nbsp;</td>
        <td class="cell">&nbsp;</td>
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
    <td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 18%;">Обследован: AIDS 2</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 32%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>29. Диагноз стационара (при выписке)</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr style="height: 51px;">
    <th style="width: 20%;" class="cell">&nbsp;</th>
    <th style="width: 19%;" class="cell">Основн. заболевание</th>
    <th style="width: 7%;" class="cell">Код МКБ</th>
    <th style="width: 20%;" class="cell">Осложнение</th>
    <th style="width: 7%;" class="cell">Код МКБ</th>
    <th style="width: 20%;" class="cell">Сопутствующее заболевание</th>
    <th style="width: 7%;" class="cell">Код МКБ</th>
</tr><tr style="height: 33px;">
    <td class="cell">Клинич. заключит.</td>
    <td class="cell">{LeaveDiag_Name}</td>
    <td class="cell">{LeaveDiag_Code}</td>
    <td class="cell">{LeaveDiagAgg_Name}</td>
    <td class="cell">{LeaveDiagAgg_Code}</td>
    <td class="cell">{LeaveDiagSop_Name}</td>
    <td class="cell">{LeaveDiagSop_Code}</td>
</tr><tr style="height: 33px;">
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
    <td style="width: 45%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 5%;">код по МКБ</td>
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