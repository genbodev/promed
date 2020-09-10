<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>Печать свидетельства о смерти</title>
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
    <table style="width: 100%;"><tr>
            <br><br><br>
            <div style="font-weight: bold; text-align: center;">
                КОРЕШОК МЕДИЦИНСКОГО СВИДЕТЕЛЬСТВА О СМЕРТИ К УЧЕТНОЙ ФОРМЕ № 106/У-08<br/>
                СЕРИЯ {DeathSvid_Ser} № {DeathSvid_Num}<br/>
                Дата выдачи {DeathSvid_GiveDate}<br/>
                {DeathSvidType_Name}<br/>
                <table style="width: 100%; margin-bottom: 1em;">
                    <tr>
                        <td style="width: 50%;  text-align: right;">{subhead}</td>
                        <td>&nbsp;{DeathSvid_disDate}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: left; margin-bottom:1em;">
                1. Фамилия, имя, отчество умершего(ей): {Person_FIO}<br/>
                2. Пол: <span class="val_01">мужской</span> - 1, <span class="val_02">женский</span> - 2 <div class="selector">0{PersonSex_id}</div><br/>
                3. Дата рождения: {Person_BirthDay}<br/>
                4. Дата смерти:	{DeathSvid_DeathDate_Date}, время: {DeathSvid_DeathDate_Time}<br/>
                5. Место постоянного жительства (регистрации) умершего (ей):  республика, край, область	{KLRGN_Name}<br/>
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
                        <td style="width:18%;">дом {Address_House}</td>
                        <td style="width:18%;">корп {Address_Corpus}</td>
                        <td style="width:18%;">кв. {Address_Flat}</td>
                    </tr>
                </table>

                6. Смерть наступила: <span class="val_11">на месте происшествия</span> - 1, <span class="val_12">в машине скорой помощи</span> - 2, <span class="val_13">в стационаре</span> - 3, <span class="val_14">дома</span> - 4, <span class="val_15">в другом месте</span> - 5 <div class="selector">1{DeathPlace_Code}</div><br/>
                <span style="font-weight: bold;">Для детей, умерших в возрасте до 1 года:</span><br/>
                7. Дата рождения: {Person_lessYear_Date}<br/>
                8. Место рождения: {BAddress_Address}<br/>
                9. Фамилия, имя, отчество матери: {DeathSvid_MotherFio}<br/>
                <div class="cutline" style="margin-top:9.48em;">Линия отреза</div>
            </div>

            <table style="width:100%; margin-bottom:0.2em">
                <tr>
                    <td style="border: 1px black dotted; vertical-align:top;">
                        <table>
                            <tr><td colspan="2">Министерство здравоохранения и социального развития</td></tr>
                            <tr><td colspan="2">Российской Федерации</td></tr>
                            <tr><td colspan="2">Наименование медицинской организации</td></tr>
                            <tr><td colspan="2">{Lpu_Name}</td></tr>
                            <tr><td>адрес</td><td>{orgaddress_uaddress}</td></tr>
                            <tr><td colspan="2">Код по ОКПО {org_okpo}</td></tr>
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
                <br><br><br>
                МЕДИЦИНСКОЕ СВИДЕТЕЛЬСТВО О СМЕРТИ<br/>
                СЕРИЯ {DeathSvid_Ser} № {DeathSvid_Num}<br/>
                Дата выдачи {DeathSvid_GiveDate}<br/>
                {DeathSvidType_Name}<br/>
                <table style="width: 100%; margin-bottom: 1em;">
                    <tr>
                        <td style="width: 50%;  text-align: right;">{subhead}</td>
                        <td>&nbsp;{DeathSvid_disDate}</td>
                    </tr>
                </table>
            </div>

            <table class="ct">
                <tr><td class="small">1.</td><td >Фамилия, имя, отчество умершего(ей): {Person_FIO}</td></tr>
                <tr><td>2.</td><td>Пол: <span class="val_1">мужской</span> - 1, <span class="val_2">женский</span> - 2   <div class="selector">{PersonSex_id}</div></td></tr>
                <tr><td>3.</td><td>Дата рождения: {Person_BirthDay}</td></tr>
                <tr><td>4.</td><td>Дата смерти:	{DeathSvid_DeathDate_Date}, время: {DeathSvid_DeathDate_Time}</td></tr>
                <tr><td>5.</td><td>Место постоянного жительства (регистрации) умершего (ей):  республика, край, область	{KLRGN_Name}<br/>
                    <table style="width:100%;">
                        <tr>
                            <td>район {KLSubRGN_Name}</td>
                            <td>город {KLCity_Name}</td>
                            <td style="width:45%;">населённый пункт {KLTown_Name}</td>
                        </tr>
                    </table>
                    <table style="width:100%;">
                        <tr>
                            <td>улица {KLStreet_name}</td>
                            <td style="width:18%;">дом {Address_House}</td>
                            <td style="width:18%;">корп {Address_Corpus}</td>
                            <td style="width:18%;">кв. {Address_Flat}</td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td>6.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{KlareaType_id}</div></td></tr>
                <tr><td>7.</td><td>Место смерти: республика,край, область {DKLRGN_Name}</br>
                    <table style="width:100%;">
                        <tr>
                            <td>район {DKLSubRGN_Name}</td>
                            <td>город {DKLCity_Name}</td>
                            <td style="width:45%;">населённый пункт {DKLTown_Name}</td>
                        </tr>
                    </table>
                    <table style="width:100%;">
                        <tr>
                            <td>улица {DKLStreet_name}</td>
                            <td style="width:18%;">дом {DAddress_House}</td>
                            <td style="width:18%;">корп {DAddress_Corpus}</td>
                            <td style="width:18%;">кв. {DAddress_Flat}</td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td>8.</td><td>Местность: <span class="val_1">городская</span> - 1, <span class="val_2">сельская</span> - 2 <div class="selector">{DKlareaType_id}</div></td></tr>
                <tr><td>9.</td><td>Смерть наступила: <span class="val_1">на месте <nobr>происшествия</span> - 1,</nobr> <span class="val_2">в машине скорой <nobr>помощи</span> - 2,</nobr> <span class="val_3">в <nobr>стационаре</span> - 3,</nobr> <nobr><span class="val_4">дома</span> - 4,</nobr> <span class="val_5">в другом <nobr>месте</span> - 5.</nobr><div class="selector">{DeathPlace_Code}</div></td></tr>
                <tr><td>10.</td><td>Для детей, умерших в возрасте от 168 час. до 1 месяца: <span class="val_1">доношенный <nobr>(37 - 41 недель)</span> - 1,</nobr> <span class="val_2">недоношенный <nobr>(менее 37 недель)</span> - 2,</nobr> <span class="val_3">переношенный <nobr>(42 недель и более)</span> - 3.</nobr><div class="selector">{DonosType_id}</div></td></tr>
                <tr><td>11.</td><td> Для детей, умерших в возрасте от 168 часов до 1 года: масса тела ребёнка при рождении <nobr> {DeathSvid_Mass} грамм - 1,</nobr> каким по счёту был ребёнок у матери <nobr>(считая умерших и несчитая мертворожденных) {DeathSvid_ChildCount} - 2,</nobr> дата рождения <nobr>матери {DeathSvid_MotherBirthday} - 3,</nobr> возраст <nobr>матери (полных лет) {DeathSvid_MotherAge} - 4,</nobr> фамилия <nobr>матери {DeathSvid_MotherFamaly} - 5,</nobr> <nobr>имя {DeathSvid_MotherName} - 6,</nobr> <nobr>отчество {DeathSvid_MotherSecName} - 7.</nobr></td></tr>
                <tr><td>12.</td><td>*Семейное положение: <span class="val_1">состоял(а) в зарегистрированном <nobr>браке</span> - 1,</nobr> <span class="val_2">не состоял(а) в зарегистрированном <nobr>браке</span> - 2,</nobr>  <nobr><span class="val_5">неизвестно</span> - 3.</nobr><div class="selector">{DeathFamilyStatus_Code}</div></td></tr>
                <tr><td>13.</td><td>*Образование: профессиональное: <nobr><span class="val_1">высшее</span> - 1,</nobr> <span class="val_2">неполное <nobr>высшее</span> - 2,</nobr> <nobr><span class="val_3">среднее</span> - 3,</nobr> <nobr><span class="val_4">начальное</span> - 4;</nobr> общее: <nobr><span class="val_5">среднее(полное)</span> - 5,</nobr> <nobr><span class="val_6">основное</span> - 6,</nobr> <nobr><span class="val_7">начальное</span> - 7;</nobr> <span class="val_8">не имеет начального <nobr>образования</span> - 8;</nobr> <nobr><span class="val_9">неизвестно</span> - 9.</nobr><div class="selector">{DeathEducation_Code}</div></td></tr>
                <tr><td>14.</td><td>*Занятность: был(а) занят(а) в экономике: <span class="val_1">руководители и специалисты высшего уровня <nobr>квалификации</span> - 1,</nobr> <span class="val_2">прочие <nobr>специалисты</span> - 2,</nobr> <span class="val_3">квалифицированные <nobr>рабочие</span> - 3,</nobr> <span class="val_4">неквалифицированные <nobr>рабочие</span> - 4,</nobr> <span class="val_5">занятые на военной <nobr>службе</span> - 5;</nobr> не был(а) занят(а) в экономике: <nobr><span class="val_6">пенсионеры</span> - 6,</nobr> <span class="val_7">студенты и <nobr>учащиеся</span> - 7,</nobr> <span class="val_8">работавшие в личном подсобном <nobr>хозяйстве</span> - 8,</nobr> <nobr><span class="val_9">безработные</span> - 9,</nobr> <nobr><span class="val_10">прочие</span> - 10.</nobr><div class="selector">{DeathZanat_id}</div></td></tr>
                <tr><td>15.</td><td>Смерть произошла от: <nobr><span class="val_1">заболевания</span> - 1,</nobr> несчастного случая: <span class="val_2">не связанного с <nobr>производством</span> - 2,</nobr> <span class="val_3">связанного с <nobr>производством</span> - 3,</nobr> <nobr><span class="val_4">убийства</span> - 4;</nobr> <nobr><span class="val_5">самоубийства</span> - 5;</nobr> <span class="val_7">в ходе военных действий: <nobr>военных</span> - 6,</nobr> <nobr><span class="val_8">террористических</span> - 7,</nobr> <span class="val_6">род смерти не <nobr>установлен</span> - 8</nobr><div class="selector">{DeathCause_id}</div></td></tr>
                <tr><td colspan="2">* В случае смерти детей, возраст которых указан а пунктах 10-11, пункты. 12-14 заполняются в отношении их матерей.</td></tr>
            </table>
            <br>
            <table class="ct">
                <tr style="page-break-before: always;"><td colspan="2">
                    <table class="ct ctleft">
                        <tr><td class="small">10.</td><td colspan="2">Причины смерти:</td><td colspan="3" class="tcent" style="border-left-style: dashed; border-right-style: dashed;">Приблизительный период времени</br>между началом патологического</br>процесса и смертью</td><td rowspan="9" class="small">&nbsp;</td><td colspan="5">Код по МКБ-10</td></tr>
                        <tr><td rowspan="8">I.</td><td rowspan="2" class="small">а)</td><td class="dashed">{Diag1_Name}</td><td rowspan="8" class="small" style="border-left-style: dashed;">&nbsp;</td><td class="dashed tcent">&nbsp;</td><td rowspan="8" class="small" style="border-right-style: dashed;">&nbsp;</td><td class="diat">{D11}</td><td class="diat">{D12}</td><td class="diat">{D13}</td><td class="diat">{D14}</td><td class="diat">{D15}</td></tr>
                        <tr><td class="tcent">(болезнь или состояние непосредственно приведшее к смерти)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">б)</td><td class="dashed">{Diag2_Name}</td><td class="dashed tcent">&nbsp;</td><td class="diat">{D21}</td><td class="diat">{D22}</td><td class="diat">{D23}</td><td class="diat">{D24}</td><td class="diat">{D25}</td></tr>
                        <tr><td class="tcent">(патологическое состояние, которое привело к возникновению вышеуказанной причины)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">в)</td><td class="dashed">{Diag3_Name}</td><td class="dashed tcent">{DeathSvid_PribPeriod}</td><td class="diat">{D31}</td><td class="diat">{D32}</td><td class="diat">{D33}</td><td class="diat">{D34}</td><td class="diat">{D35}</td></tr>
                        <tr><td class="tcent">(первоначальная причина смерти указывается последней)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">г)</td><td class="dashed">{Diag4_Name}</td><td class="dashed tcent">&nbsp;</td><td class="diat">{D41}</td><td class="diat">{D42}</td><td class="diat">{D43}</td><td class="diat">{D44}</td><td class="diat">{D45}</td></tr>
                        <tr><td class="tcent">(внешняя причина при травмах и отравлениях)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="3">II.</td><td colspan="11">Прочие важные состояния, способствовавшие смерти, но не связанные с болезнью или патологическим состоянием,приведшим к ней, включая употребление алкоголя, наркотических средств, психотропных и других токсических веществ, содержание их в крови, а  так же операции (название,</td></tr>
                        <tr><td>дата)</td><td colspan="4" class="dashed">{Diag5_Name}</td><td>&nbsp;</td><td class="diat">{D51}</td><td class="diat">{D52}</td><td class="diat">{D53}</td><td class="diat">{D54}</td><td class="diat">{D55}</td></tr>
                        <tr><td colspan="11">{DeathSvid_Oper}</td></tr>
                    </table>
                </td></tr>
                <tr><td>11.</td><td>В случае смерти в результате ДТП: <span class="val_1">смерть наступила - в течении 30 суток</span> - 1, <span class="val_2">из них в течении 7 суток</span> - 2. <div class="selector">{DeathDtpType_id}</div></td></tr>
                <tr><td>12.</td><td><span class="val_1">В случае смерти беременной <nobr>(независимо от срока и локализации)</span> - 1</nobr>, <span class="val_2">в процессе родов <nobr>(аборта)</span> - 2,</nobr> <span class="val_3">в течение 42 дней после  окончания беременности, родов <nobr>(аборта)</span> - 3;</nobr> <span class="val_4">кроме того в течение 43-365 дней после окончания беременности, <nobr>родов</span> - 4.</nobr><div class="selector">{DeathWomanType_id}</div></td></tr>
                <tr><td>13.</td><td>Фамилия, имя, отчество врача (фельдшера, акушерки), заполнившего Медицинское свидетельство о смерти</br>
                    <table class="ct ctleft">
                        <tr><td class="dashed">{MedPersonal_FIO}</td><td style="width:70px;padding-left: 5px;">Подпись</td><td class="dashed" style="width: 200px;">&nbsp;</td><td class="small">&nbsp;</td></tr>
                    </table>
                </td></tr>
                <tr><td>14.</td><td>
                    <table class="ct ctleft"><tr><td style="width: 230px;">Фамилия, имя, отчество получателя</td><td class="dashed">{DeathSvid_PolFio}</td><td class="small">&nbsp;</td></tr></table>
                    <table class="ct ctleft">
                        <tr><td style="width: 475px;">Документ, удостоверяющий личность получателя (серия, номер, кем выдан)</td><td class="dashed"><div id="broken_text_start_1_35" class="broken_text">{DeathSvid_PolDoc}</div></td><td class="small">&nbsp;</td></tr>
                        <tr><td colspan="2" class="dashed"><div id="broken_text_end_1" class="broken_text">&nbsp;</div></td><td class="small">&nbsp;</td></tr>
                    </table>
                    <table class="ct ctcenter"><tr><td class="tleft">&nbsp;{DeathSvid_PolDate}</td><td style="width:150px;">Подпись получателя</td><td class="dashed" style="width:200px;">&nbsp;</td><td class="small">&nbsp;</td></tr></table>
                </td></tr>
                <tr><td colspan="2">
                    <div class="cutline" style="margin-bottom:1em;">Линия отреза</div>
                </td></tr>
                <tr><td>16.</td><td> В случае смерти от несчастного случая, убийства, самоубийства, от военных и террористических действий, при неустановленном роде смерти - указать дату травмы (отравления): число {DeathSvid_TraumaDay}, месяц {DeathSvid_TraumaMonth}, год {DeathSvid_TraumaYear}, время {DeathSvid_TraumaTime}</br>
                    а также место и обстоятельства, при которых произошла травма (отравление):</br>
                    {DeathSvid_TraumaObstUfa}
                </td></tr>
                <tr><td>17.</td><td> Причины смерти установлены: <span class="val_1">врачом, только установившим смерть</span> - 1, <span class="val_2">лечащим врачом</span> - 2, <span class="val_3">фельдшером (акушеркой)</span> - 3, <span class="val_4">патологоанатомом</span> - 4, <span class="val_5">судебно-медицинским экспертом</span> - 5. <div class="selector">{DeathSetType_id}</div></td></tr>
                <tr><td>18.</td><td> Я, {MedPersonal_Post}  {MedPersonal_FIO}</br>
                    удостоверяю, что на основании: <span class="val_1">осмотра трупа</span> - 1, <span class="val_2">записей в медицинской документации</span> - 2, <span class="val_3">предшествующего наблюдения за больным(ой)</span> - 3, <span class="val_4">вскрытия</span> - 4 мною определена последовательность патологических процессов (состояний), приведших к смерти,
                    и установлены причины смерти. <div class="selector">{DeathSetCause_id}</div>
                </td></tr>
                <tr><td colspan="2">
                    <table class="ct ctleft">
                        <tr><td class="small"></br>19.</td><td colspan="2"></br>Причины смерти:</td><td colspan="3" class="tcent" style="border-left-style: dashed; border-right-style: dashed;">Приблизительный период времени</br>между началом патологического</br>процесса и смертью</td><td rowspan="9" class="small">&nbsp;</td><td colspan="5">Код по МКБ-10</td></tr>
                        <tr><td rowspan="8">I.</td><td rowspan="2" class="small">а)</td><td class="dashed">{Diag1_Name}</td><td rowspan="8" class="small" style="border-left-style: dashed;">&nbsp;</td><td class="dashed tcent">&nbsp;</td><td rowspan="8" class="small" style="border-right-style: dashed;">&nbsp;</td><td class="diat">{D11}</td><td class="diat">{D12}</td><td class="diat">{D13}</td><td class="diat">{D14}</td><td class="diat">{D15}</td></tr>
                        <tr><td class="tcent">(болезнь или состояние непосредственно приведшее к смерти)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">б)</td><td class="dashed">{Diag2_Name}</td><td class="dashed tcent">&nbsp;</td><td class="diat">{D21}</td><td class="diat">{D22}</td><td class="diat">{D23}</td><td class="diat">{D24}</td><td class="diat">{D25}</td></tr>
                        <tr><td class="tcent">(патологическое состояние, которое привело к возникновению вышеуказанной причины)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">в)</td><td class="dashed">{Diag3_Name}</td><td class="dashed tcent">{DeathSvid_PribPeriod}</td><td class="diat">{D31}</td><td class="diat">{D32}</td><td class="diat">{D33}</td><td class="diat">{D34}</td><td class="diat">{D35}</td></tr>
                        <tr><td class="tcent">(первоначальная причина смерти указывается последней)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="2">г)</td><td class="dashed">{Diag4_Name}</td><td class="dashed tcent">&nbsp;</td><td class="diat">{D41}</td><td class="diat">{D42}</td><td class="diat">{D43}</td><td class="diat">{D44}</td><td class="diat">{D45}</td></tr>
                        <tr><td class="tcent">(внешняя причина при травмах и отравлениях)</td><td>&nbsp;</td><td colspan="5">&nbsp;</td></tr>
                        <tr><td rowspan="3">II.</td><td colspan="11">Прочие важные состояния, способствовавшие смерти, но не связанные с болезнью или патологическим состоянием,приведшим к ней, включая употребление алкоголя, наркотических средств, психотропных и других токсических веществ, содержание их в крови, а  так же операции (название,</td></tr>
                        <tr><td>дата)</td><td colspan="4" class="dashed">{Diag5_Name}</td><td>&nbsp;</td><td class="diat">{D51}</td><td class="diat">{D52}</td><td class="diat">{D53}</td><td class="diat">{D54}</td><td class="diat">{D55}</td></tr>
                        <tr><td colspan="11">{DeathSvid_Oper}</td></tr>
                    </table>
                </td></tr>
                <tr><td>20.</td><td>В случае смерти в результате ДТП: <span class="val_1">смерть наступила - в течении 30 суток</span> - 1, <span class="val_2">из них в течении 7 суток</span> - 2. <div class="selector">{DeathDtpType_id}</div></td></tr>
                <tr><td>21.</td><td><span class="val_1">В случае смерти беременной <nobr>(независимо от срока и локализации)</span> - 1</nobr>, <span class="val_2">в процессе родов <nobr>(аборта)</span> - 2,</nobr> <span class="val_3">в течение 42 дней после  окончания беременности, родов <nobr>(аборта)</span> - 3;</nobr> <span class="val_4">кроме того в течение 43-365 дней после окончания беременности, <nobr>родов</span> - 4.</nobr><div class="selector">{DeathWomanType_id}</div></td></tr>
                <tr><td>22.</td><td>Фамилия, имя, отчество врача (фельдшера, акушерки), заполнившего Медицинское свидетельство о смерти</br>
                    <table class="ct ctleft">
                        <tr><td class="dashed">{MedPersonal_FIO}</td><td style="width:70px;padding-left: 5px;">Подпись</td><td class="dashed" style="width: 200px;">&nbsp;</td><td class="small">&nbsp;</td></tr>
                    </table>
                    <table class="ct ctcenter" style="margin-bottom:1em;">
						<tr>
							<?php if(empty($OrgHeadPost_Name)) { ?>
							<td class="tleft"><span class="val_1">Руководитель медицинской организации</span>,</br>частнопрактикующий врач (подчеркнуть)<div class="selector">1</div></td>
							<?php } else { ?>
							<td class="tleft">{OrgHeadPost_Name}</td>
							<?php } ?>
							<td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">{OrgHead_Fio}</td><td rowspan="2" class="small">&nbsp;</td>
						</tr>
                        <tr><td>&nbsp;</td><td>(подпись)</td><td>(Фамилия, имя, отчество)</td></tr>
                        <tr><td colspan="5" style="padding-left:2em; text-align:left;">Печать</td></tr>
                    </table>
                </td></tr>
                <tr style="border-top-weight:2px; border-top-style: solid;"><td>23.</td><td> Свидетельство проверено врачом, ответсвенным за правильность заполнения медицинских свидетельств.</td></tr>
            </table>
            <table class="ct ctcenter">
                <tr><td rowspan="2" class="tleft">&nbsp;"____"_______________20___г.</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td><td class="dashed">&nbsp;</td><td rowspan="2" class="small">&nbsp;</td></tr>
                <tr><td>(подпись)</td><td>(Фамилия, имя, отчество)</td></tr>
            </table>
    </tr></table>


<script type="text/javascript">activateSelectors();</script>
</body>

</html>