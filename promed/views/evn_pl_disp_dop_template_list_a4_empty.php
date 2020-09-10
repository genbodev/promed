<html>
<head>
<title>{EvnPLTemplateTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 30px; font-family: times, tahoma, verdana; font-size: 14px;}
table { border-collapse: collapse; width: 100%}
span, div, td { font-family: times, tahoma, verdana; font-size: 14px; }
th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; }
h1 { text-align: center; font-size: 20px; font-weight: bold}
.printtable {border: 1px solid black}
.printtable th {text-align: center; padding: 3px; font-weight: normal}
.printtable td {text-align: center; padding: 3px; border: 1px solid black}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 30px; font-family: times, tahoma, verdana; font-size: 14px;}
table {  border-collapse: collapse; width: 100%}
span, div, td { font-family: times, tahoma, verdana; font-size: 14px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; }
h1 { text-align: center; font-size: 20px; font-weight: bold}
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.pg {page-break-before: always}
.printtable {border: 1px solid black}
.printtable th {text-align: center; padding: 3px; font-weight: normal}
.printtable td {text-align: center; padding: 3px; border: 1px solid black}
</style>
</head>

<body>
<table><tr>
<td style="vertical-align: top; padding: 10px 30px 0 30px">
<div style="text-transform: uppercase;">{Lpu_Name}, {Lpu_OGRN}</div>
<div style="border-top: 1px solid black; text-align: center">(наименование учреждения здравоохранения, проводящего диспансеризацию, код по ОГРН)</div>
</td>
<td style="width: 20%;">
Приложение №2</br>
к приказу Министерства здравоохранения</br>
социального развития Российской Федерации</br>
<span style="padding-right: 90px">от</span>№</br>
Медицинская документация</br>
Учётная форма №131/у-ДД-09</br>
Утверждена Приказом</br>
Минздравсоцразвития России</br>
от 24.02.2009 № 67н
</td>
</tr></table>
<div><h1>КАРТА УЧЕТА ДОПОЛНИТЕЛЬНОЙ ДИСПАНСЕРИЗАЦИИ РАБОТАЮЩЕГО ГРАЖДАНИНА</h1></div>
<div style="text-align: center; font-weight: bold; padding-bottom: 20px">( медицинская карта амбулаторного больного №<span style="width: 50px; border-bottom: 1px solid black; padding: 0 5px 0 5px;">{PersonCard_Code}&nbsp;</span><span> )</span></div>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">1. Фамилия, имя, отчество</td>
<td style="width: 100%; text-transform: uppercase; border-bottom: 1px solid black">{Person_FIO}</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">2. Пол:</td>
<td style="width: 100%;">{Sex_Name}</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">3. Номер страхового полиса ОМС:</td>
<td style="width: 100%;">{OrgSMO_Nick}, {Polis_Ser}, {Polis_Num}</td>
</tr></table>
<table><tr>
<td style="width: 190px; white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">4. Дата рождения (число, месяц, год)</td>
<td style="width: 100px; text-align: center; border-bottom: 1px solid black">{Person_BirthDay}</td>
<td>&nbsp;</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">5. Адрес места жительства:</td>		
<td style="width: 100%; text-transform: uppercase; border-bottom: 1px solid black;">{Person_Address}&nbsp;</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">6. Место работы</td>
<td style="width: 100%; text-transform: uppercase; border-bottom: 1px solid black;" colspan="5">{Org_Nick}&nbsp;</td>
</tr>
<tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0;">&nbsp;</td>
<td style="white-space: nowrap;">телефон</td><td style="width: 15%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td><td style="white-space: nowrap; width: 10%; padding-left: 50px">телефон служебный</td><td style="width: 15%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td><td style="width: 70%;">&nbsp;</td>
</tr>
</table>
<table><tr>
<td style="width: 140px; white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">7. Организация бюджетная:</td>
<td style="width: 140px; text-align: center; border-bottom: 1px solid black">{EvnPLDispDop_IsBud}&nbsp;</td>
<td>&nbsp;</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">8. Профессия, должность</td>
<td style="width: 100%; border-bottom: 1px solid black;">&nbsp;</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">9. Прикреплен в данном учреждении здравоохранения для:</td>
<td style="width: 100%; border-bottom: 1px solid black;">{AttachType_Name}&nbsp;</td>
</tr></table>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">10. Учреждение здравоохранения, к которому прикреплен для постоянного динамического наблюдения (название, юридический адрес)</td>
</tr><tr>
<td style="width: 100%; border-bottom: 1px solid black;">{Lpu_AName}, {Lpu_AAddress}&nbsp;</td>
</tr></table>
<table class="pg" style="margin-top: 20px">
<tr>
<td>
<div style="font-weight: bold; text-align: center">11. Осмотры врачей-специалистов</div>
<table class="printtable">
<tr>
  <th colspan="28" rowspan="11">Специальность врача</th>
  <th rowspan="11">№ строки</th>
  <th colspan="9" rowspan="11">Код врача</th>
  <th colspan="9" rowspan="11">Дата осмотра</th>
  <th colspan="32" rowspan="2">Заболевания (код по МКБ-10)</th>
  <th colspan="74">Результат дополнительной диспансеризации (ДД)</th>
  <th colspan="10" rowspan="11">Ф.И.О. (подпись врача)</th>
</tr>
 <tr>
  <th colspan="10" rowspan="10">практи-</br>чески здоров (I группа здоровья)</th>
  <th colspan="10" rowspan="10">риск развития заболева-</br>ния (II группа здоровья)</th>
  <th colspan="54">нуждается в лечении</th>
 </tr>
 <tr>
  <th colspan="10" rowspan="9">ранее известное хроническое</th>

  <th colspan="12" rowspan="9">выявленное во время ДД </th>
  <th colspan="10" rowspan="9">в том числе на поздней стадии</th>
  <th colspan="10" rowspan="9">амбула-</br>торном (III группа здоровья)</th>
  <th colspan="10" rowspan="9">в том числе по заболе-</br>ваниям, вы-</br>явленным при ДД</th>
  <th colspan="10" rowspan="9">стацио-</br>нарном (IV группа здоровья)</th>
  <th colspan="14">в том числе</th>
  <th colspan="10" rowspan="9">санатор-</br>но-курортном</th>
 </tr>
 <tr>
  <th colspan="14">в оказании</th>
 </tr>
 <tr>
  <th colspan="14">высокотехно-</th>
 </tr>
 <tr>
  <th colspan="14">логической</th>
 </tr>
 <tr>
  <th colspan="14">медицинской</th>
 </tr>
 <tr>

  <th colspan="14">помощи</th>
 </tr>
 <tr>
  <th colspan="14">(ВМП)</th>
 </tr>
<tr>
  <th colspan="14">(V группа</th>

</tr>
 <tr>
  <th colspan="14">здоровья)</th>
</tr>
 <tr>
  <td colspan="28">1</td>
  <td>2</td>
  <td colspan="9">3</td>
  <td colspan="9">4</td>
  <td colspan="10">5</td>
  <td colspan="12">6</td>
  <td colspan="10">7</td>
  <td colspan="10">8</td>
  <td colspan="10">9</td>
  <td colspan="10">10</td>
  <td colspan="10">11</td>
  <td colspan="10">12</td>
  <td colspan="14">13</td>
  <td colspan="10">14</td>
  <td colspan="10">15</td>
 </tr>
 <tr>
  <td colspan="28">Терапевт</td>
  <td>1</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="28">Акушер-гинеколог</td>
  <td>2</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="28">Невролог</td>
  <td>3</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="28">Хирург</td>
  <td>4</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="28">Офтальмолог</td>
  <td>5</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="28">Дополнительная консультация</td>
  <td>6</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="9">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="12">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="14">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
  <td colspan="10">&nbsp;</td>
 </tr>
 </table>
</td>
</tr>
</table>
<table class="pg" style="margin-top: 20px"><tr>
<td style="width: 45%; border-top: 1px solid black; font-weight: bold"><div>12. Лабораторные и функциональные исследования (*)</div></td>
<td style="width: 55%"><div>&nbsp;</div></td>
</tr><tr>
<tr>
<!-- левая сторона -->
<td style="width: 45%;" rowspan="2">
<table class="printtable">
<tr>
<th style="width: 40%;">Перечень исследований</th>
<th style="width: 10%;">№ строки</th>
<th style="width: 25%;">Дата исследования</th>
<th style="width: 25%;">Дата получения результата</th>
</tr>
<tr>
<td style="width: 40%; text-align: left">Клинический анализ крови&nbsp;</td>
<td style="width: 10%;">01&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Биохимический анализ крови:&nbsp;</td>
<td style="width: 10%;">02&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">общий белок&nbsp;</td>
<td style="width: 10%;">03&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">холестерин крови&nbsp;</td>
<td style="width: 10%;">04&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">липопротеиды низкой плотности сыворотки крови&nbsp;</td>
<td style="width: 10%;">05&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Триглицериды сыворотки крови&nbsp;</td>
<td style="width: 10%;">06&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">креатинин крови&nbsp;</td>
<td style="width: 10%;">07&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">мочевая кислота крови&nbsp;</td>
<td style="width: 10%;">08&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">билирубин крови&nbsp;</td>
<td style="width: 10%;">09&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">амилаза крови&nbsp;</td>
<td style="width: 10%;">10&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">сахар крови&nbsp;</td>
<td style="width: 10%;">11&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Клинический анализ мочи&nbsp;</td>
<td style="width: 10%;">12&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Онкомаркер CA-125 (женщинам)&nbsp;</td>
<td style="width: 10%;">13&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Онкомаркер PSI(мужчинам)&nbsp;</td>
<td style="width: 10%;">14&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Электрокардиография&nbsp;</td>
<td style="width: 10%;">15&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Флюорография&nbsp;</td>
<td style="width: 10%;">16&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Маммография&nbsp;</td>
<td style="width: 10%;">17&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Цитологическое исследование мазка из цервикального канала&nbsp;</td>
<td style="width: 10%;">18&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align: left">Дополнительные исследования&nbsp;</td>
<td style="width: 10%;">19&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
<td style="width: 25%;">&nbsp;</td>
</tr>
</table>
</td>
<!-- правая сторона -->
<td style="width: 55%; padding-left: 20px; vertical-align: top">
<div>13. Рекомендации по индивидуальной программе профилактических мероприятий</div>
<div style="border-bottom: 1px solid black">&nbsp;</div>
<table>
<tr>
<td style="white-space: nowrap;">14. Взят под диспансерное наблюдение</td><td style="width: 25%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td><td style="white-space: nowrap; width: 5%;">, с диагнозом (МКБ-10)</td><td style="width: 45%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td>
</tr>
<tr>
<td style="white-space: nowrap;">&nbsp;</td><td style="width: 25%; white-space: nowrap; text-align: center">(дата)</td><td style="white-space: nowrap; width: 5%;">&nbsp;</td><td style="width: 45%; white-space: nowrap;">&nbsp;</td>
</tr>
</table>
<div>15. Диагноз (МКБ-10), установленный через 6 месяцев после ДД</div>
<div style="border-bottom: 1px solid black">&nbsp;</div>
<div>16. Снят с диспансерного наблюдения в течение года по причине:</div>
<div style="padding: 10px 0 10px 0">&nbsp;</div>
<table><tr>
<td style="white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">17. Причина смерти (диагноз по МКБ-10)</td>
<td style="width: 100%; border-bottom: 1px solid black;">&nbsp;</td>
</tr></table>
</td>
</tr><tr>
<td style="vertical-align: bottom; padding-left: 20px;">
<table><tr>
<td style="width: 130px; white-space: nowrap; vertical-align: top; padding: 0 5px 0 0">Дата завершения ДД</td>
<td style="width: 100px; text-align: center; border-bottom: 1px solid black; border-top: 1px solid black">&nbsp;</td>
<td>&nbsp;</td>
</tr></table>
<table>
<tr>
<td style="white-space: nowrap; padding: 15px 0 0 0; vertical-align: bottom;">Врач-терапевт участковый</td><td style="width: 25%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td><td>&nbsp;&nbsp;&nbsp;</td><td style="width: 45%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td>
</tr>
<tr>
<td style="white-space: nowrap; padding: 30px 0 0 0; vertical-align: bottom;"></td><td style="width: 25%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td><td>&nbsp;&nbsp;&nbsp;</td><td style="width: 45%; white-space: nowrap; border-bottom: 1px solid black;">&nbsp;</td>
</tr>
<tr>
<td style="white-space: nowrap; padding: 15px 0 0 0; vertical-align: bottom;"></td><td style="width: 25%; white-space: nowrap; text-align: center">(подпись)</td><td>&nbsp;&nbsp;&nbsp;</td><td style="width: 45%; white-space: nowrap; text-align: center;">(расшифровка подписи)</td>
</tr>
</table>
</td>
</tr></table>
<table style="margin: 5px 0 0 0;">
<tr>
<td style="border-bottom: 1px solid black">&nbsp;</td><td style="width: 80%;">&nbsp;</td>
</tr>
<tr>
<td colspan="2" style="padding-left: 15px; font-size: 11px">* Копии результатов исследований прилагаются для передачи в учреждение здравоохранения, осуществляющее динамическое наблюдение гражданина.</td>
</tr>
</table>
</body>
</html>