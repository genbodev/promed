<html>
<head>
<title>Печать талона</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 10px; width: 500px }
table { border-collapse: collapse; }
span, div, td { font-family: 'times new roman'; font-size: 10px; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.block {height: 12pt; width: 12pt; border: 1px solid #000; border-top: none; text-align: center; font-weight: bold; font-size: 13px;}
td {vertical-align: top}
small {font-size: 11px}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin-left: 10px; padding: 10px;}
span, div, td { font-family: 'times new roman'; font-size: 10px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 11px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.block {height: 12pt; width: 12pt; border: 1px solid #000; border-top: none; text-align: center; font-weight: bold; font-size: 13px;}
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
<script>
    window.print();
   // window.onmouseover = function() { window.close(); };
</script>

<table style="width: 100%; font-size: 7.5px;">
<tr>
	<td style="width: 45%; border: 1px solid #000; text-align: center;">
		Минздрав РФ
	</td>
	<td style="border: 1px solid #000;" rowspan="2">
	
	</td>
	<td style="width: 45%; border: 1px solid #000; text-align: center;" rowspan="2">
		МЕДИЦИНСКАЯ ДОКУМЕНТАЦИЯ<br>
		<u>Форма №025-12/у</u>
	</td>
</tr>
<tr>
	<td style="width: 45%; border: 1px solid #000; text-align: center;">
		{Lpu_Name}<br>
		{LpuAddress}<br>
		Код ОГРН: {Lpu_OGRN}
	</td>
</tr>
</table>

<div style="font-size: 12px; text-align: center; margin: 3px;">ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</div>

<table style="width: 100%;">
<tr>
	<td style="width: 40%;">Номер участка </td>
	<td style="width: 50%;"></td>
	<td class="block">{lrn1}</td><td class="block">{lrn2}</td><td class="block">{lrn3}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 80%;">Вид оплаты (1- ОМС; 2 – бюджет; 3 – платные услуги; 4 – ДМС;  5 другие) </td>
	<td style="width: 15%;"></td>
	<td class="block">{PayType_Code}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 50%;">1. Код врача, начавшего лечение </td>
	<td style="width: 25%; border-bottom: 1px solid;"><td class="block">{mpc1}</td><td class="block">{mpc2}</td><td class="block">{mpc3}</td><td class="block">{mpc4}</td><td class="block">{mpc5}</td><td class="block">{mpc6}</td><td class="block">{mpc7}</td></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 40%;">2. Наименование страховой организации </td>
	<td style="width: 60%; border-bottom: 1px solid;">{OrgSmo_Name}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px; ">
<tr>
	<td style="width: 40%;">Номер полиса </td>
	<td style="width: 40%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Polis_Num}</td>
	<td style="width: 5%;">ОТ </td>
	<td style="width: 15%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Polis_begDate}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 60%;">Серия полиса </td>
	<td style="width: 40%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Polis_Ser}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 10%;">СНИЛС </td>
	<td style="width: 90%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Person_Snils}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 30%;">3. Фамилия, Имя, Отчество </td>
	<td style="width: 70%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Person_Fio}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 30%;">4. Пол: мужчина – 1, женщина – 2  </td>
	<td style="width: 65%;"></td>
	<td class="block">{Sex_Code}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 60%;">5. Дата рождения (число, месяц, год)  </td>
	<td style="width: 40%; border-bottom: 1px solid; font-size: 13px; font-weight: bold;">{Person_Birthday}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 25%;">6. <b>Социальный статус:</b></td>
	<td style="width: 75%;" colspan="3">
		1 – дошкольник;  2– учащийся (студент);  3 – работающий; <br>
		4 – не работающий;  5 – пенсионер;  6 – военнослужащий; 
	</td>
</tr>
<tr>
	<td style="width: 25%;"></td>
	<td style="width: 65%;">
		7 – член семьи военнослужащего;  8 – БОМЖ;  9 – призывник
	</td>
	<td class="block" width="10%" align="center">{SocStatus_Code}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 100%;" colspan="3">
		7. <b>Категория льгот:</b>  1 – инвалид ВОВ;  2 - участник ВОВ;  3 – воин-интернационалист;  4 – лицо, <br>
		&nbsp;&nbsp;&nbsp;&nbsp;подвергшееся  радиационному облучению, в т.ч. 5 – подвергшееся радиационному облучению в Чернобыле;
	</td>
</tr>
<tr>
	<td style="width: 90%;">
		&nbsp;&nbsp;&nbsp;&nbsp;6 – инв.1 гр.;  7 – инв.2 гр.;  8 – инв.3 гр.;9 – ребенок инвалид;  10 – инвалид с детства;  11 – прочие
	</td>
	<td class="block"></td><td class="block"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 30%;">8. Житель: город -1; село – 2</td>
	<td style="width: 65%;"></td>
	<td class="block">{KLAreaType_Code}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 20%;">9. Адрес проживания: </td>
	<td style="width: 80%; border-bottom: 1px solid;">{PAddress_Name}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 100%;" colspan="3">
		10. <b>Медицинская помощь:</b><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 – амбулаторный прием;  8 – общеврачебная практика;  9 – стоматологическая помощь; 
	</td>
</tr>
<tr>
	<td style="width: 90%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;10 – реабилитационная помощь
	</td>
	<td class="block">{MedicalCareKind_Code1}</td><td class="block">{MedicalCareKind_Code2}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 100%;" colspan="3">
		11. <b>Цель обращения:</b> 11 – обращение по поводу заболевания; 2 – посещения с профилактической целью<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(в т.ч.: 21 – разовое по поводу заболевания; 22 - диспансеризация граждан;  23 – диспанс.наблюдение;<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;24 – профосмотр; 25 – центр здоровья; 26 – патронаж;  27 – иная); 31 – посещения по поводу неотложной <br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;помощи; 41- разовое посещение в приемном отделении по поводу заболевания без последующей
	</td>
</tr>
<tr>
	<td style="width: 90%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;госпитализации; 51 – паллиативная помощь
	</td>
	<td class="block">{vtc1}</td><td class="block">{vtc2}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 95%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Диспансеризация: 1 – сирот; 2 – 14-лет. подростков; 3 – работающих; 4 – неработающих; 5 - учащихся
	</td>
	<td class="block"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 100%;">
		12. <b>Место обслуживания:</b>
	</td>
</tr>
</table>

<table border="1" cellspacing="0" cellpadding="0" width="500" style="font-size: 7px;">
 <tbody><tr>
  <td width="130" rowspan="2">
  <p align="center"><span>&nbsp;</span></p>
  </td>
  <td width="255" rowspan="2" align="center">
  Посещения выполнены<br>
  (число, месяц, врач)
  </td>
  <td width="132" colspan="2">
  <p align="center"><span>Всего</span></p>
  </td>
 </tr>
 <tr>
  <td width="71">
  <p align="center"><span>посещений</span></p>
  </td>
  <td width="61">
  <p align="center"><span>дней</span></p>
  </td>
 </tr>
 <tr>
  <td width="130" rowspan="1" style="vertical-align: middle">
  <p><span>&nbsp;Поликлиника…………...</span></p>
  </td>
  <td width="255" height="20px" style="font-size: 13px; font-weight: bold;">{vizitDataPol_1}</td>
  <td width="71" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{Pol_1}</td>
  <td width="61" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{DaysPol_Count_1}</td>
 </tr>
 <!--<tr>
  <td width="255" height="20px">{vizitDataPol_2}</td>
  <td width="71" height="20px">{Pol_2}</td>
  <td width="61" height="20px">{DaysPol_Count_2}</td>
 </tr>
 <tr>
     <td width="255" height="20px">{vizitDataPol_3}</td>
     <td width="71" height="20px">{Pol_3}</td>
     <td width="61" height="20px">{DaysPol_Count_3}</td>
 </tr>-->
 <tr>
  <td width="130" rowspan="1" style="vertical-align: middle">
  <p><span>&nbsp;На дому …………….……</span></p>
  </td>
  <td width="255" height="20px" style="font-size: 13px; font-weight: bold;">{vizitDataHome_1}</td>
  <td width="71" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{Home_1}</td>
  <td width="61" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{DaysHome_Count_1}</td>
 </tr>
 <!--<tr>
  <td width="255" height="20px">{vizitDataHome_2}</td>
  <td width="71" height="20px">{Home_2}</td>
  <td width="61" height="20px">{DaysHome_Count_2}</td>
 </tr>-->
 <tr>
  <td width="130" rowspan="1" style="vertical-align: middle">
  <p><span>&nbsp;Актив на дому ……..</span></p>
  </td>
  <td width="255" height="20px" style="font-size: 13px; font-weight: bold;">{vizitDataHomeAct_1}</td>
  <td width="71" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{HomeAct_1}</td>
  <td width="61" height="20px" style="font-size: 13px; font-weight: bold; text-align: center;">{DaysHomeAct_Count_1}</td>
 </tr>
<!-- <tr>
  <td width="255" height="20px">{vizitDataHomeAct_2}</td>
  <td width="71" height="20px">{HomeAct_2}</td>
  <td width="61" height="20px">{DaysHomeAct_Count_2}</td>
 </tr>-->
</tbody></table>

<div style="page-break-after: always;"></div>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 100%;">
		13. <b>Диагнозы:</b>
	</td>
</tr>
</table>

<table border="1" cellspacing="0" cellpadding="0" width="500" style="font-size: 7.5px;">
 <tbody><tr>
  <td width="67" rowspan="3" align="center">
  Тип<br> диагноза (1-основ., 2-сопутст.) 
  </td>
  <td width="60" rowspan="3" align="center">
  Код <br>диагноза <br>по<br> МКБ-10
  </td>
  <td width="48" rowspan="3" align="center">
  Харак-<br> тер
  заболе-<br> вания
  </td>
  <td width="48" rowspan="3" align="center" style="vertical-align: middle">
  Трав-<br>ма
  </td>
  <td width="322" colspan="4" align="center">
  Группа<br>
  диспансерного наблюдения&nbsp; 1, 2, 3
  </td>
 </tr>
 <tr>
  <td width="60" rowspan="2" align="center" style="vertical-align: middle">
  Состоит
  </td>
  <td width="63" rowspan="2" align="center" style="vertical-align: middle">
  Взят
  </td>
  <td width="198" colspan="2" valign="top" align="center">
  Снят
  </td>
 </tr>
 <tr>
  <td width="104" valign="top" align="center">
  по выздоровлению
  </td>
  <td width="94" valign="top" align="center">
  по др. причинам
  </td>
 </tr>
 <tr>
  <td width="67" valign="top">{diagType1}</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Diag_Code1}</td>
  <td width="48" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DeseaseType_Code1}</td>
  <td width="48" valign="top">&nbsp;</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{IsDisp1}</td>
  <td width="93" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Disp_Date1}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Zdorov1}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Other1}</td>
 </tr>
 <tr>
  <td width="67" valign="top">{diagType2}</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Diag_Code2}</td>
  <td width="48" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DeseaseType_Code2}</td>
  <td width="48" valign="top">&nbsp;</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{IsDisp2}</td>
  <td width="93" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Disp_Date2}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Zdorov2}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Other2}</td>
 </tr>
 <tr>
  <td width="67" valign="top">{diagType3}</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Diag_Code3}</td>
  <td width="48" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DeseaseType_Code3}</td>
  <td width="48" valign="top">&nbsp;</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{IsDisp3}</td>
  <td width="93" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Disp_Date3}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Zdorov3}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Other3}</td>
 </tr>
 <tr>
  <td width="67" valign="top">{diagType4}</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Diag_Code4}</td>
  <td width="48" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DeseaseType_Code4}</td>
  <td width="48" valign="top">&nbsp;</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{IsDisp4}</td>
  <td width="93" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Disp_Date4}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Zdorov4}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Other4}</td>
 </tr>
 <tr>
  <td width="67" valign="top">{diagType5}</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Diag_Code5}</td>
  <td width="48" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DeseaseType_Code5}</td>
  <td width="48" valign="top">&nbsp;</td>
  <td width="60" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{IsDisp5}</td>
  <td width="93" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{Disp_Date5}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Zdorov5}</td>
  <td width="84" valign="top" style="font-size: 13px; font-weight: bold; text-align: center;">{DOT_Other5}</td>
 </tr>
</tbody></table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 20%;">
		Характер заболевания:
	</td>
	<td style="width: 80%;">
		1 – острое заболевание, 2 – впервые в жизни  зарегистрированное хроническое, <br>
		3 – известное ранее хроническое,  4 – обострение хронического.
	</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td><u>Травма:</u></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 20%;">
		Производственная:
	</td>
	<td style="width: 80%;">
		<span class="val_1">1 – промышленная</span>; <span class="val_2">2 – транспортная</span>, в т.ч. <span class="val_3">3 – ДТП</span>; <span class="val_4">4 – сельскохозяйственная</span>; <br>
		<span class="val_5">5 – прочие</span>
        <div class="selector">{PrehospTrauma_Code}</div>
	</td>
</tr>
<tr>
	<td style="width: 20%;">
		Не производственная:
	</td>
	<td style="width: 80%;">
		<span class="val_6">6 – бытовая</span>; <span class="val_7">7 – уличная</span>; <span class="val_8">8 – транспортная</span>, в т.ч. <span class="val_9">9 – ДТП</span>; <span class="val_10">10 – школьная</span>; <br>
		<span class="val_11">11 – спортивная</span>, 12 – противоправная; <span class="val_12">13 – прочие</span>
        <div class="selector">{PrehospTrauma_Code}</div>
	</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td>
		14. <b>Выполненные медицинские услуги:</b>
	</td>
	<td colspan="14" align="center">
		код услуги
	</td>
	<td></td>
	<td colspan="2" align="center" style="width: 10%;">
		кол-во
	</td>
</tr>
<tr>
	<td style="width: 40%;">
	</td>
	<!--<td class="block">{u11}</td><td class="block">{u12}</td><td class="block">{u13}</td><td class="block">{u14}</td><td class="block"></td><td class="block">{u21}</td><td class="block">{u22}</td>
	<td class="block">{u23}</td><td class="block">{u24}</td><td class="block"></td><td class="block">{u31}</td><td class="block">{u32}</td><td class="block">{u33}</td><td class="block">{u34}</td>
	-->
    <td class="block">{u11}</td><td class="block">{u12}</td><td class="block">{u13}</td><td class="block">{u14}</td><td class="block">{u15}</td><td class="block">{u16}</td><td class="block">{u17}</td>
    <td class="block">{u18}</td><td class="block">{u19}</td><td class="block">{u110}</td><td class="block">{u111}</td><td class="block">{u112}</td><td class="block">{u113}</td><td class="block">{u114}</td>

    <td style="width: 3%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
	<td class="block" style="width: 4%;">{uk11}</td>
	<td class="block" style="width: 4%;">{uk12}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 40%;">
		&nbsp;
	</td>
    <!--<td class="block">{u41}</td><td class="block">{u42}</td><td class="block">{u43}</td><td class="block">{u44}</td><td class="block"></td><td class="block">{u51}</td><td class="block">{u52}</td>
    <td class="block">{u53}</td><td class="block">{u54}</td><td class="block"></td><td class="block">{u61}</td><td class="block">{u62}</td><td class="block">{u63}</td><td class="block">{u64}</td>
    -->
    <td class="block">{u21}</td><td class="block">{u22}</td><td class="block">{u23}</td><td class="block">{u24}</td><td class="block">{u25}</td><td class="block">{u26}</td><td class="block">{u27}</td>
    <td class="block">{u28}</td><td class="block">{u29}</td><td class="block">{u210}</td><td class="block">{u211}</td><td class="block">{u212}</td><td class="block">{u213}</td><td class="block">{u214}</td>
	<td style="width: 3%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
	<td class="block" style="width: 4%;">{uk21}</td>
	<td class="block" style="width: 4%;">{uk22}</td>
</tr>
</table>
<table style="width: 100%; margin-top: 3px;">
    <tr>
        <td style="width: 40%;">
            &nbsp;
        </td>
        <td class="block">{u31}</td><td class="block">{u32}</td><td class="block">{u33}</td><td class="block">{u34}</td><td class="block">{u35}</td><td class="block">{u36}</td><td class="block">{u37}</td>
        <td class="block">{u38}</td><td class="block">{u39}</td><td class="block">{u310}</td><td class="block">{u311}</td><td class="block">{u312}</td><td class="block">{u313}</td><td class="block">{u314}</td>
        <td style="width: 3%;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
		<td class="block" style="width: 4%;">{uk31}</td>
		<td class="block" style="width: 4%;">{uk32}</td>
    </tr>
</table>
<table style="width: 100%; margin-top: 3px;">
    <tr>
        <td style="width: 40%;">
            &nbsp;
        </td>
        <td class="block">{u41}</td><td class="block">{u42}</td><td class="block">{u43}</td><td class="block">{u44}</td><td class="block">{u45}</td><td class="block">{u46}</td><td class="block">{u47}</td>
        <td class="block">{u48}</td><td class="block">{u49}</td><td class="block">{u410}</td><td class="block">{u411}</td><td class="block">{u412}</td><td class="block">{u413}</td><td class="block">{u414}</td>
        <td style="width: 3%;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
		<td class="block" style="width: 4%;">{uk41}</td>
		<td class="block" style="width: 4%;">{uk42}</td>
    </tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td colspan="10">
		15. <b>Документ о временной нетрудоспособности:</b>
	</td>
</tr>
<tr>
	<td style="width: 45%;">
		<div style="float: left; height: 0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_1">1 – лист временной нетрудоспособности</span><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="val_2">2 – справка</span><div class="selector">{StickType_Code}</div></div>
	</td>
	<td style="width: 10%;">открыт</td>
	<td class="block">{sb1}</td><td class="block">{sb2}</td><td class="block">{sb3}</td><td class="block">{sb4}</td><td class="block">{sb5}</td><td class="block">{sb6}</td><td class="block">{sb7}</td><td class="block">{sb8}</td>
	<td style="width: 5%;"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 45%;">
	</td>
	<td style="width: 10%;">закрыт</td>
	<td class="block">{se1}</td><td class="block">{se2}</td><td class="block">{se3}</td><td class="block">{se4}</td><td class="block">{se5}</td><td class="block">{se6}</td><td class="block">{se7}</td><td class="block">{se8}</td>
	<td style="width: 5%;"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 20%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Причина выдачи:</td>
	<td style="width: 75%;">1 – заболевание, 2 – по уходу,  3 – карантин,  4 – прерывание беременности,
	<td></td>
</tr>
<tr>
	<td style="width: 20%;"></td>
	<td style="width: 75%;">
		5 – отпуск по беременности и родам, 6 – санаторно-курортное лечение</td>
	<td class="block">{StickCause_Type}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 30%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;По уходу:  пол 1-муж, 2-жен</td>
	<td class="block">{EvnStick_Sex}</td>
	<td style="width: 60%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Полных лет (возраст лица получившего листок в/н)</td>
	<td class="block">{EvnStick_Age}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 10%;">
		16. <b>Исход:</b>
	</td>
	<td style="width: 75%;">
		301 - выздоровление, 302 - ремиссия, 303 - улучшение, 304 - без перемен, 
	</td>
</tr>
<tr>
	<td></td>
	<td style="width: 75%;">
		305 - ухудшение, 306 -осмотр
	</td>
	<td class="block">{ResultDeseaseType_Code1}</td><td class="block">{ResultDeseaseType_Code2}</td><td class="block">{ResultDeseaseType_Code3}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td colspan="4">
		17. <b>Результат обращения:</b> 301 – лечение завершено; 302 – лечение прервано по инициативе пациента;       
	</td>
</tr>
<tr>
	<td colspan="4">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;303 – лечение прервано по инициативе ЛПУ; 304 – лечение продолжено;<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;305 – направлен на госпитализацию;  306 – направлен в дневной стационар;  307 – направлен в <br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;стационар на дому;  308 – направлен на консультацию;  309 – направлен на консультацию в др. ЛПУ;<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;310 – направлен в реабилитационное отделение; 311 – направлен на санитарно-курортное лечение;<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;312 – проведена дополнительная диспансеризация, 313 – констатация факта смерти;
	</td>
</tr>
<tr>
	<td style="width: 88%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;314 - динамическое наблюдение;  315 – направлен на обследование
	</td>
	<td class="block">{ResultClass_Code1}</td><td class="block">{ResultClass_Code2}</td><td class="block">{ResultClass_Code3}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 35%;">В случае госпитализации:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;код  ЛПУ</td>
	<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>
	<td style="width: 25%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;диагноз по МКБ-10</td>
	<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 35%;">В случае консультации: код специал-ти</td>
	<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>
	<td style="width: 25%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;диагноз по МКБ-10</td>
	<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td style="width: 33%;">18. Код врача, закончившего лечение</td>
	<td class="block">{mpcl1}</td><td class="block">{mpcl2}</td><td class="block">{mpcl3}</td><td class="block">{mpcl4}</td><td class="block">{mpcl5}</td><td class="block">{mpcl6}</td><td class="block">{mpcl6}</td>
	<td style="width: 27%;">&nbsp;&nbsp;&nbsp;Код мед.сестры (фельдш.)</td>
	<td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td><td class="block"></td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td>19. Документ удост.личность (для иногородних): {Document}</td>
</tr>
</table>

<table style="width: 100%; margin-top: 3px;">
<tr>
	<td>20. <b>Рецептурный бланк:</b></td>
</tr>
</table>

<table border="1" cellspacing="0" cellpadding="0" width="247" align="left">
 <tbody><tr>
  <td width="48" valign="top">
  <p align="center"><span>Серия</span></p>
  </td>
  <td width="60" valign="top">
  <p align="center"><span>Номер</span></p>
  </td>
  <td width="60" valign="top">
  <p align="center"><span>Дата</span></p>
  </td>
  <td width="75" valign="top">
  <p align="center"><span>Врач</span></p>
  </td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
</tbody></table>


<table border="1" cellspacing="0" cellpadding="0" width="247" align="left" style="margin-left: 5px">
 <tbody><tr>
  <td width="48" valign="top">
  <p align="center"><span>Серия</span></p>
  </td>
  <td width="60" valign="top">
  <p align="center"><span>Номер</span></p>
  </td>
  <td width="60" valign="top">
  <p align="center"><span>Дата</span></p>
  </td>
  <td width="75" valign="top">
  <p align="center"><span>Врач</span></p>
  </td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
 <tr>
  <td width="48">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="60">&nbsp;</td>
  <td width="75">&nbsp;</td>
 </tr>
</tbody></table>
<script type="text/javascript">activateSelectors();</script>

</body>

</html>