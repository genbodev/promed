<html>
<head>
    <title>Печать талона</title>
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
        @page land { size: 50% }
        body { margin: 0px; padding: 0px;}
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
        <td style="width: 50%;">
            <div style="text-align:center; margin-bottom: 10px">Министерство здравоохранения и социального развития Российской<br> Федерации
                <b><i><u><br />{Lpu_Name}</u></i></b>
                <br><sup>(наименование медицинского учреждения)</sup>
                <b><i><u><br>{Lpu_Address}</u></i></b>
                <br><sup>(адрес)</sup>
                <br><br><br>
                <table style="width: 90%;">
                    <tr>
                        <td style="none: 1px solid;">Код ОГРН</td> <td style="none"></td><td style="none"></td>
                        <td class="block">{ogrn1}</td><td class="block">{ogrn2}</td><td class="block">{ogrn3}</td>
                        <td class="block">{ogrn4}</td><td class="block">{ogrn5}</td><td class="block">{ogrn6}</td>
                        <td class="block">{ogrn7}</td><td class="block">{ogrn8}</td><td class="block">{ogrn9}</td>
                        <td class="block">{ogrn10}</td><td class="block">{ogrn11}</td><td class="block">{ogrn12}</td>
                        <td class="block">{ogrn13}</td>
                    </tr>
                </table>
            </div>
        </td>
        <td style="text-align: right;">
            Медицинская документация<br />Форма № 025-12/у _______<br><br>утверждена приказом Минздравсоцразвития России<br />от 22 ноября 2004 года № 255<br>
        </td>
    </tr>
</table>
<div style="font-size: 14px; text-align:center; margin: auto;">
    <b>ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</b><br>
    <table style="width:67%; margin: auto; font-size: 14px;"><tr>
        <td style="none; font-size: 14px;"><b>№ медицинской карты</b></td><td style="none"><b>{PersonCard_Code}</b></td>
        <td style="none; font-size: 14px;"><b>Дата</b></td>
        <td class="block">{PCD1}</td>
        <td class="block">{PCD2}</td>
        <td class="block">{PCD3}</td>
        <td class="block">{PCD4}</td>
        <td class="block">{PCD5}</td>
        <td class="block">{PCD6}</td>
    </tr></table>
</div>
<br>
<table style="width: 100%;">
    <tr>
        <td>1. Код категории льготы </td><td class="block">{PrivC1}</td><td class="block">{PrivC2}</td><td class="block">{PrivC3}</td>
        <td style="text-align: left; font-size: 14px;">2. Номер страхового полиса ОМС</td>
        <td class="block">{PSN1}</td><td class="block">{PSN2}</td><td class="block">{PSN3}</td><td class="block">{PSN4}</td>
        <td class="block">{PSN5}</td><td class="block">{PSN6}</td><td class="block">{PSN7}</td><td class="block">{PSN8}</td>
        <td class="block">{PSN9}</td><td class="block">{PSN10}</td><td class="block">{PSN11}</td><td class="block">{PSN12}</td>
        <td class="block">{PSN13}</td><td class="block">{PSN14}</td><td class="block">{PSN15}</td><td class="block">{PSN16}</td>
        <td class="block">{PSN17}</td><td class="block">{PSN18}</td>
    </tr>
</table>
<br>
<table>
    <tr>
        <td style="width: 25%;">&nbsp;</td>
        <td style="text-align: left; font-size: 14px;">3. СНИЛС<td></td><td style="width: 15%;">&nbsp;</td>
        <td class="block">{PSnils1}</td><td class="block">{PSnils2}</td><td class="block">{PSnils3}</td><td class="block">{PSnils4}</td>
        <td class="block">{PSnils5}</td><td class="block">{PSnils6}</td><td class="block">{PSnils7}</td><td class="block">{PSnils8}</td>
        <td class="block">{PSnils9}</td><td class="block">{PSnils10}</td><td class="block">{PSnils11}</td><td class="block">{PSnils12}</td>
        <td class="block">{PSnils13}</td><td class="block">{PSnils14}</td><td class="block">{PSnils15}</td><td class="block">{PSnils16}</td>
        <td class="block">{PSnils17}</td><td class="block">{PSnils18}</td><td class="block">{PSnils19}</td><td class="block">{PSnils20}</td>
    </tr>
</table>
<br>
<table style = "width: 100%;">
    <tr>
        <td class="block" style="width: 14%;">4.Пациент: код<sup>1</sup></td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style = "width: 58%;">ф.и.о. &nbsp;&nbsp;&nbsp;{Person_Fio}</td>
    </tr>
</table>
<table style = "width: 100%; border-top: none;">
    <tr>
        <td class="block" style="width: 42%; border-top: none;">5.Пол<sup>4</sup>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_1">1 - муж;</span><span class="val_2">2 - жен;</span><div class="selector">{Sex_Code}</div></td>
        <td class="block" style = "width: 17%; border-top: none;">6.дата рождения</td>
        <td class="block" style="border-top: none;">{PB1}</td>
        <td class="block" style="border-top: none;">{PB2}</td>
        <td class="block" style="border-top: none;">{PB3}</td>
        <td class="block" style="border-top: none;">{PB4}</td>
        <td class="block" style="border-top: none;">{PB5}</td>
        <td class="block" style="border-top: none;">{PB6}</td>
        <td class="block" style="border-top: none;">{PB7}</td>
        <td class="block" style="border-top: none;">{PB8}</td>
        <td class="block" style = "width: 18%; border-top: none;">&nbsp;</td>
    </tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="border: 1px solid; border-top: none;">7. Документ, удостоверяющий личность (название, серия и номер)<sup>4</sup>: <b>{DocumentType_Name} {Document_Ser} {Document_Num}</b></td>
    </tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="border: 1px solid; border-top: none;">
            8. Адрес регистрации по месту жительства<sup>4</sup>: <b>{UAddress_Name}</b><br>
            9. Житель<sup>4</sup>:<span class="val_1">1 - город;</span><span class="val_2">2 - сел</span> <div class="selector">{KLAreaType_Code}</div>
        </td>

    </tr>
    <tr>
        <td style="border: 1px solid">10. Социальный статус, в т.ч. занятость: 1. - дошкольник:
            <span class="val_1">1.1 - организован</span>, <span class="val_2">1.2 - неорганизован</span>,
            <span class="val_3">2 - учащийся</span>, <span class="val_4">3 - работающий</span>, <span class="val_5">4 - неработающий</span>,
            <span class="val_6">5 - пенсионер</span>, <span class="val_0">6 - военнослужащий, код |__|__|__|</span>,
            <span class="val_0">7 - член семьи военнослужащего</span>, <span class="val_7">8 - без определенного места жительства</span>
            <div class="selector">{SocStatus_Code}</div>
        </td>
    </tr>
    <tr>

        <td style="border: 1px solid">11. Инвалидность:
            <span class="val_83">1 - Iгр.,</span>
            <span class="val_82">2 - IIгр.,</span>
            <span class="val_81">3 - IIIгр.,</span>
            <span class="val_0">4 - установлена впервые в жизни,</span>
            <span class="val_0">5 - степень инвалидность |_|,</span>
            <span class="val_84">6 - ребенок-инвалид</span>,
            <span class="val_0">7 - инвалид с детства,</span>
            <span class="val_0">8 - снята</span>
            <div class="selector">{PrivilegeType_CodeStr}</div>
        </td>
    </tr>
</table>


<table style="width: 100%; margin-top: 3px;">
    <tr>
        <td style="border: 1px solid">12. Специалист: код </td>
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
        <td style="border: 1px solid">Ф.И.О. <b></b></td>
    </tr>
    <tr>
        <td style="border: 1px solid">13. Специалист: код<sup>2</sup></td>
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
        <td style="border: 1px solid">Ф.И.О. <b></b></td>
    </tr>
    <tr>
        <td style="border: 1px solid" colspan="12">14. Вид оплаты: 1 - ОМС, 2 - бюджет, 3 - платные услуги, в т.ч. 4 - ДМС, 5 - другое</td>
    </tr>
    <tr>
        <td colspan="12" style="border: 1px solid">15. Место обслуживания: 1 - поликлиника, 2 - на дому, в т.ч. - актив</td>
    </tr>
    <tr>
        <td colspan='12' style="border: 1px solid">
            16. Цель посещения:
            1 - заболевание,
            2 - профосмотр,
            3 - патронаж,
            4 - другое
        </td>
    </tr>
    <tr>
        <td colspan='12' style="border: 1px solid">
            17. Результат обращения<sup>5</sup>: случай закончен:
            1 - выздоровл.;
            2 - улучшение;
            3 - динамическое набл.,
            направлен:
            4 - на госпитализацию,
            5 - в дневной стационар,
            6 - стационар на дому,
            7 - на консультацию,
            8 - на консультацию в др. ЛПУ,
            9 - справка для получения путевки
        </td>
    </tr>
</table>
<div><small>
    <sup>1</sup> При использовании кода, принятого в ЛПУ.<br>
    <sup>2</sup> Заполняется при учете работы среднего мед. персонала.<br>
    <sup>3</sup> При оплате: по посещению проставляется код посещения или стандарта медицинской помощи (СМП), КЭС.<br>
    <sup>4</sup> Заполняется при разовом обращении пациента (например, иногородний).<br>
    <sup>5</sup> Заполняется при последнем обращении по данному случаю<br>
</small></div>

<table style="width: 100%; margin-top: 1px;">
    <tr>
        <td style="border: 1px solid; width: 200px;">18. Диагноз МКБ-10: </td>
        <td class="block"></td>
        <td class="block"></td>
        <td class="block"></td>
        <td class="block"></td>
        <td class="block"></td>
        <td style="border: 1px solid"></td>
    </tr>
</table>
<table style="width: 100%; margin-top: -1px;">
    <tr>
        <td style="border: 1px solid; width: 200px; border-top: none;" colspan="19">19. Код мед.услуги (посещение, СМП, КЭС)<sup>3</sup></td>
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
        <td class="block"></td><td class="block"></td><td class="block"></td>
        <td class="block"></td><td class="block"></td><td class="block"></td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
    </tr>
    <tr>
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
        <td class="block"></td><td class="block"></td><td class="block"></td>
        <td class="block"></td><td class="block"></td><td class="block"></td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
    </tr>

</table>

<table style="width: 100%; margin-top: -1px;">
    <tr>
        <td style="border: 1px solid; border-right: none; width: 160px;border-top: none;">20. Характер заболевания: </td>
        <td style="border: 1px solid; border-left: none;border-top: none;">
            1 - острое (+), впервые в жизни установленное хроническое(+);
            2 - диагноз установлен в прошлом году или ранее (-)
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid; border-right: none; width: 160px;">21. Диспансерный учёт: </td>
        <td style="border: 1px solid; border-left: none;">
            1 - состоит, 2 - взят, 3 - снят; в т.ч. 4 - по выздоровлению
        </td>
    </tr>
</table>

<table style="width: 100%; margin-top: -1px;">
    <tr>
        <td style="border: 1px solid; border-right: none; width: 200px;">22. Травма - производственная: </td>
        <td style="border: 1px solid; border-left: none;">
            1 - промышленная,
            2 - транспортная, в т.ч. 3 - ДТП;
            4 - сельскохозяйственная;
            5 - прочие
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid; border-right: none; width: 200px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; непроизводственная: </td>
        <td style="border: 1px solid; border-left: none;">
            6 - бытовая,
            7 - уличная;
            8 - транспортная, в т.ч. 9 - ДТП;
            10 - школьная;
            11 - спортивная;
            12 - прочие;
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid;" colspan="2">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 13 - полученная в результате террористических действий
        </td>
    </tr>
</table>






<table style="width: 100%; margin-top: 1px;">
    <tr>
        <td style="border: 1px solid; width: 200px;">23. Диагноз код </td>
        <td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td>
        <td style="border: 1px solid"></td>
    </tr>
</table>
<table style="width: 100%; margin-top: 0px;">
    <tr>
        <td style="border: 1px solid; width: 200px; border-top: none;" colspan="19">24. Код мед.услуги (посещение, СМП, КЭС)<sup>3</sup></td>
        <td class="block" style="border-top: none;"></td><td class="block" style="border-top: none;"></td>
        <td class="block" style="border-width: 2px; border-top: none;";>&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-width: 2px; border-top: none;">&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-width: 2px;border-top: none;">&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td><td class="block" style="border-top: none;">&nbsp;</td>
        <td class="block" style="border-width: 2px;border-top: none;"></td>
        <td class="block" style="border-top: none;"></td>
    </tr>
    <tr>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td class="block" style="border-width: 2px"></td>
        <td class="block"></td>
    </tr>


</table>

<table style="width: 100%; margin-top: -1px;">
    <tr>
        <td style="border: 1px solid; border-right: none; width: 160px; border-top: none;">26. Характер заболевания: </td>
        <td style="border: 1px solid; border-left: none; border-top: none;">
            1 - острое (+), впервые в жизни установленное хроническое (+);
            2 - диагноз установлен в прошлом году или ранее (-)
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid; border-right: none; width: 160px;">26. Диспансерный учёт: </td>
        <td style="border: 1px solid; border-left: none;">
            1 - состоит, 2 - взят, 3 - снят; в т.ч. 4 - по выздоровлению
        </td>
    </tr>
</table>

<table style="width: 100%; margin-top: 3px;">
    <tr>
        <td style="border: 1px solid;" colspan="8">27. Заполняется только при изменении диагноза: ранее зарегестрированный диагноз</td>
    </tr>
    <tr>
        <td style="border: 1px solid;width: 50%; text-align: right;"colspan="2">Код МКБ-10&nbsp;</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td style="border: 1px solid;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border: 1px solid;width: 50%;">Дата регистрации изменяемого диагноза:</td>
        <td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td><td class="block">&nbsp;</td>
        <td style="border: 1px solid;">&nbsp;</td>
    </tr>
</table>


<table style="width: 100%; margin-top: 3px;">
    <tr>
        <td style="border: 1px solid;" colspan="2">
            28. Документ временной нетрудоспособности: 1-открыт, 2-закрыт
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid; border-right: none; width: 120px;">29. Причина выдачи: </td>
        <td style="border: 1px solid; border-left: none;">
            1 - заболевание, 2 - по уходу, 3 - карантин, 4 - прерывание беременности,<br/>
            5 - отпуск по беременности и родам, 6 - санаторно-курортное лечение
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid" colspan="2">
            29.1  по уходу: пол 1-муж, 2-жен &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (возраст лица получившего документ в/н)
        </td>
    </tr>
</table>

<table style="border: 1px solid; border-top: none;">
    <tr>
        <td style="width: 30%; border-right: none;">30. Рецептурный бланк серия и №, дата выписки</td>
        <td style="width: 35%; border-right: none; border-left: none; text-decoration: underline;"><b>30.1</b></td>
        <td style="width: 35%; border-right: none; border-left: none; text-decoration: underline;"><b>30.2</b></td>
    </tr>
    <tr>
        <td style="width: 30%; border-right: none;">&nbsp;</td>
        <td style="width: 35%; border-right: none; border-left: none; text-decoration: underline;"><b>30.3</b></td>
        <td style="width: 35%; border-right: none; border-left: none; text-decoration: underline;"><b>30.4</b></td>
    </tr>
</table>
<script type="text/javascript">activateSelectors();</script>

</body>

</html>