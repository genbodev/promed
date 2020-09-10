<html>
<head>
    <title>{statement_template_title}</title>
    <style type="text/css">
        body { margin: 0px; padding: 10px; }
        table { border-collapse: collapse; }
        span, div, td { font-family: verdana; font-size: 14px; }
        .bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 8pt; }
    </style>

    <style type="text/css" media="print">
        body { margin: 0px; padding: 5px; }
        span, div, td { font-family: verdana; font-size: 14px; }
        td { vertical-align: bottom; }
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
<!-- /*NO PARSE JSON*/ -->

<table width="100%">
    <tr>
        <td width="70%">
        </td>
        <td>
            Приложение №1 <br> к Порядку застрахованных лиц, прикрепленных к медицинским организациям Республики Бурятия
            для оказания первичной медико-санитарной помощи по территориальному принципу
            <br>&nbsp;
        </td>
    </tr>
</table>

<table width="100%" style="border: 1px solid;">
    <tr style="border: 1px solid;">
        <td width="50%" style="border: 1px solid; padding: 15px;">
            <h4 align="center"> РЕШЕНИЕ <br>
            Администрации МО<br></h4>
            <hr/><br><hr/><br><hr/><br><hr/><br>
            <table width="100%">
                <tr>
                    <td width="18%">Дата</td>
                    <td width="32%" style="border-bottom: 1px solid;">&nbsp;</td>
                    <td width="20%">&nbsp;&nbsp;Подпись</td>
                    <td width="30%" style="border-bottom: 1px solid;">&nbsp;</td>
                </tr>
            </table>
            <br><br>М.П.<br><br><br>
        </td>
        <td style="border: 1px solid; padding: 15px;">Главному врачу <u>{Lpu_Name} <br>({Lpu_Address})</u><br><br>
            <hr/><br>
            <p align="center"> <sup>(наименование и адрес медицинской организации)</sup></p>
            <hr/><br><hr/>
            <p align="center"> <sup>(ФИО заявителя в соответствие с документом)</sup></p>
        </td>
    </tr>
</table>


<h4 align="center">ЗАЯВЛЕНИЕ<br />
    о выборе медицинской организации <br>(прикрепления)</h4>
Прошу принять меня (гражданина, представителем которого я являюсь) (нужное подчеркнуть) на медицинское обслуживание. <br>
Информация о гражданине, осуществляющим выбор МО:<br>

<table width="100%">
    <tr>
        <td width="6%">Фамилия</td>
        <td width="44%" style="border-bottom: 1px solid; text-align: center;">{Person_Surname}</td>
        <td width="3%">&nbsp;&nbsp;&nbsp;&nbsp;Имя</td>
        <td width="47%" style="border-bottom: 1px solid; text-align: center;">{Person_Firname}</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="27%">Отчество (при наличии)</td>
        <td width="73%" style="border-bottom: 1px solid; text-align: center;">{Person_Secname}</td>
    </tr>
    <tr>
        <td width="27%">&nbsp;</td>
        <td width="73%" style="text-align: center;"><sup>(в соответствием с документом, удостоверяющим личность)</sup></td>
    </tr>
</table>

<div>Пол: <span class="val_1">муж.,</span><span class="val_2">жен.</span><div class="selector">{Sex_Code}</div>(нужное подчеркнуть)</div>
<table width="100%">
    <tr>
        <td width="20%">Дата рождения</td>
        <td width="80%" style="border-bottom: 1px solid; text-align: center;">{Person_Birthday}</td>
    </tr>
    <tr>
        <td width="20%">&nbsp;</td>
        <td width="80%" style="text-align: center;"><sup>(число, месяц, год)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="20%">Место рождения</td>
        <td width="80%" style="border-bottom: 1px solid; text-align: center;">{Person_BAddress}</td>
    </tr>
    <tr>
        <td width="20%">&nbsp;</td>
        <td width="80%" style="text-align: center;"><sup>(в соответствие с документом, удостоверяющим личность)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="20%">Гражданство</td>
        <td width="80%" style="border-bottom: 1px solid; text-align: center;">{Nationality}</td>
    </tr>
    <tr>
        <td width="20%">&nbsp;</td>
        <td width="80%" style="text-align: center;"><sup>(название государства; лицо без гражданства)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="45%">Вид документа, удостоверяющего личность</td>
        <td width="55%" style="border-bottom: 1px solid; text-align: center;">{DocumentType_Name}</td>
    </tr>
</table>
<div>Серия <u>{Document_Ser}</u>&nbsp; &nbsp;&nbsp;&nbsp;Номер <u>{Document_Num}</u></div>
<div>Когда и кем выдан <u>{Document_begDate} {OrgDep_Name}</u></div>
<br>

<div><b>Адрес регистрации по месту жительства</b></div>
<table width="100%">
    <tr>
        <td width="30%">субъект Российской Федерации</td>
        <td width="70%" style="border-bottom: 1px solid; text-align: center;">{URgn_Name}</td>
    </tr>
    <tr>
        <td width="30%">&nbsp;</td>
        <td width="70%" style="text-align: center;"><sup>(республика, край, область, округ)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="5%">район</td>
        <td width="22%" style="border-bottom: 1px solid; text-align: center;">{USubRgn_Name}</td>
        <td width="5%">город</td>
        <td width="22%" style="border-bottom: 1px solid; text-align: center;">{UCity_Name}</td>
        <td width="18%">населенный пункт</td>
        <td width="28%" style="border-bottom: 1px solid; text-align: center;">{UTown_Name}</td>
    </tr>
    <tr>
        <td colspan="5">&nbsp;</td>
        <td width="28%" style="text-align: center;"><sup>(село, поселок и т.п.)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="30%">улица (проспект, переулок и т.п)</td>
        <td width="70%" style="border-bottom: 1px solid; text-align: center;">{UStreet_Name}</td>
    </tr>
</table>
<div>№ дома (владения) <u>&nbsp;&nbsp;&nbsp;{UAddress_House}&nbsp;&nbsp;&nbsp;</u>
    &nbsp;&nbsp;&nbsp;корпус (строение) <u>&nbsp;&nbsp;&nbsp;{UAddress_Corpus}&nbsp;&nbsp;&nbsp;</u>
    &nbsp;&nbsp;&nbsp;квартира <u>&nbsp;&nbsp;&nbsp;{UAddress_Flat}&nbsp;&nbsp;&nbsp;</u>
</div>
<br>
<table width="100%">
    <tr>
        <td width="40%">Дата регистрации по месту жительства</td>
        <td width="60%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
</table>
<br>
<div><b>Адрес места пребывания</b> (указывается для оказания медицинской помощи на дому по вызову)</div>
<table width="100%">
    <tr>
        <td width="5%">район</td>
        <td width="22%" style="border-bottom: 1px solid; text-align: center;">{PSubRgn_Name}</td>
        <td width="5%">город</td>
        <td width="22%" style="border-bottom: 1px solid; text-align: center;">{PCity_Name}</td>
        <td width="18%">населенный пункт</td>
        <td width="28%" style="border-bottom: 1px solid; text-align: center;">{PTown_Name}</td>
    </tr>
    <tr>
        <td colspan="5">&nbsp;</td>
        <td width="28%" style="text-align: center;"><sup>(село, поселок и т.п.)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="30%">улица (проспект, переулок и т.п)</td>
        <td width="70%" style="border-bottom: 1px solid; text-align: center;">{PStreet_Name}</td>
    </tr>
</table>
<br>
<div>№ дома (владения) <u>&nbsp;&nbsp;&nbsp;{PAddress_House}&nbsp;&nbsp;&nbsp;</u>
    &nbsp;&nbsp;&nbsp;корпус (строение) <u>&nbsp;&nbsp;&nbsp;{PAddress_Corpus}&nbsp;&nbsp;&nbsp;</u>
    &nbsp;&nbsp;&nbsp;квартира <u>&nbsp;&nbsp;&nbsp;{PAddress_Flat}&nbsp;&nbsp;&nbsp;</u>
</div>

<table width="100%">
    <tr>
        <td width="20%">№ полиса)</td>
        <td width="80%" style="border-bottom: 1px solid; text-align: center;">{Polis_Ser} {Polis_Num}</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="45%">Наименование СМО, застраховавшей гражданина</td>
        <td width="55%" style="border-bottom: 1px solid; text-align: center;">{OrgSmo_Name}</td>
    </tr>
</table>
<br>
<div>Наименование медицинской организации, в которой гражданин находится на медицинском обслуживании на момент подачи заявления <u>{Lpu_Nick}</u></div>
<br>
<div><b>Сведения о представителе гражданина:</b></div>
<table width="100%">
    <tr>
        <td width="6%">Фамилия</td>
        <td width="44%" style="border-bottom: 1px solid; text-align: center;">{DPerson_Surname}</td>
        <td width="3%">&nbsp;&nbsp;&nbsp;&nbsp;Имя</td>
        <td width="47%" style="border-bottom: 1px solid; text-align: center;">{DPerson_Firname}</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="27%">Отчество (при наличии)</td>
        <td width="73%" style="border-bottom: 1px solid; text-align: center;">{DPerson_Secname}</td>
    </tr>
    <tr>
        <td width="27%">&nbsp;</td>
        <td width="73%" style="text-align: center;"><sup>(в соответствием с документом, удостоверяющим личность)</sup></td>
    </tr>
    <tr>
        <td width="27%">Отношение к гражданину</td>
        <td width="73%" style="text-align: center; border-bottom: 1px solid">{DeputyKind_Name}</td>
    </tr>
</table>
<br>
<div><b>Данные о документе, удостоверяющем личность представителя:</b></div>
<table width="100%">
    <tr>
        <td width="45%">Вид документа, удостоверяющего личность</td>
        <td width="55%" style="border-bottom: 1px solid; text-align: center;">{DDocumentType_Name}</td>
    </tr>
</table>
<br>
<div>Серия <u>{DDocument_Ser}</u>&nbsp; &nbsp;&nbsp;&nbsp;Номер <u>{DDocument_Num}</u></div>
<div>Когда и кем выдан <u>{DDocument_begDate} {DOrgDep_Name}</u></div>
<br>
<div><b>Контактная информация:</b></div>
<table width="100%">
    <tr>
        <td width="27%">Телефон(с кодом): домашний</td>
        <td width="23%" style="border-bottom: 1px solid; text-align: center;">{Phone} </td>
        <td width="10%">; служебный</td>
        <td width="40%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td width="27%">Адрес электронной почты</td>
        <td colspan="3" width="80%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="70%">Подпись лица, подающего заявление (представителя застрахованного)</td>
        <td width="30%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="30%">Дата подачи заявления</td>
        <td width="70%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="20%">Заявление принял</td>
        <td width="80%" style="border-bottom: 1px solid; text-align: center;">&nbsp;</td>
    </tr>
    <tr>
        <td width="20%">&nbsp;</td>
        <td width="80%" style="text-align: center;"><sup>(подпись представителя медицинской организации)(расшифровка подписи)</sup></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td width="60%">&nbsp;</td>
        <td width="10%">Дата</td>
        <td width="30%" style="text-align: center; border-bottom: 1px solid;"></td>
    </tr>
    <tr>
        <td colspan="2" width="70%">&nbsp;</td>
        <td width="30%" style="text-align: center;"><sup>(число, месяц, год)</sup></td>
    </tr>
</table>
<script type="text/javascript">activateSelectors();</script>
</body>
</html>