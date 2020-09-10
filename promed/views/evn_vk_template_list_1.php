<html>
<head>
    <title>Печать списка протоколов ВК</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 0px; }
        table { border-collapse: collapse; }
        span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
        td { vertical-align: middle; border: 1px solid #000; }
    </style>

    <style type="text/css" media="print">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 0px; }
        span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
        td { vertical-align: middle; border: 1px solid #ccc; }
    </style>
</head>

<body class="land">
<!-- /*NO PARSE JSON*/ -->
Отчёт сформирован: {date}
<p align="center">ЖУРНАЛ УЧЕТА<br />КЛИНИКО - ЭКСПЕРТНОЙ РАБОТЫ<br />ЛЕЧЕБНО - ПРОФИЛАКТИЧЕСКОГО УЧРЕЖДЕНИЯ<br />{year}г.</p>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
    <td style="width: 5%;">№ п/п</td>
    <td style="width: 8%;">Дата экспертизы</td>
    <td style="width: 10%;">Наименование ЛПУ, фамилия врача, направившего пациента на экспертизу</td>
    <td style="width: 10%;">Фамилия, имя, очество пациента</td>
    <td style="width: 10%;">Адрес (либо № страхового полиса или медицинского документа) пациента</td>
    <td style="width: 10%;">Дата рождения</td>
    <td style="width: 3%;">Пол</td>
    <td style="width: 10%;">Социальный статус, профессия</td>
    <td style="width: 10%;">Причина обращения. Диагноз (основной, сопутствующий) в соответствии с МКБ-10</td>
    <td style="width: 10%;">Характеристика случая экспертизы</td>
    <td style="width: 17%;">Вид и предмет экспертизы</td>

</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
    <td>1</td>
    <td>2</td>
    <td>3</td>
    <td>4</td>
    <td>5</td>
    <td>6</td>
    <td>7</td>
    <td>8</td>
    <td>9</td>
    <td>10</td>
    <td>11</td>
</tr>

{search_results}
<tr>
    <td style="text-align: center;">{num}</td>
    <td style="text-align: center;">{EvnVK_ExpertiseDate}</td>
    <td style="text-align: center;">{MedPersonal_Fin} {Lpu_Nick}</td> <!--Наименование ЛПУ, фамилия врача, направившего пациента на экспертизу-->
    <td style="text-align: center;">{Person_Fin}</td>
    <td style="text-align: center;">{Person_Polis_Addr}</td> <!--Адрес (либо № страхового полиса или медицинского документа) пациента-->
    <td style="text-align: center;">{Person_BirthDay}</td>
    <td style="text-align: center;">{Person_Sex}</td> <!--Пол-->
    <td style="text-align: center;">{PatientStatusType_Prof}</td> <!--Социальный статус, профессия-->
    <td style="font-size: 8pt;text-align: center;">{Person_Diag} {Person_Diag_s} <br> {CauseTreatmentType_Name}</td><!--Причина обращения. Диагноз (основной, сопутствующий) в соответствии с МКБ-10-->
    <td style="text-align: center;">{ExpertiseEventType_SysNick}</td><!--Характеристика случая экспертизы-->
    <td style="text-align: center;">{ExpertiseNameType}<br>{ExpertiseNameSubjectType}<br>{EvnVK_LVN} {EvnVK_WorkReleasePeriod}<br>{EvnVK_StickDuration}</td><!--Вид и предмет экспертизы-->
</tr>
{/search_results}

</tbody></table>

</body>

</html>