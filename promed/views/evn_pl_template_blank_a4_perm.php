<html>
<head>
<title>{EvnPLTemplateBlankTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
</style>
</head>

<body>

<table style="width: 100%;"><tr>
<td style="width: 32%;">Наименование учреждения здравоохранения</td>
<td style="width: 68%; font-weight: bold; border-bottom: 1px solid #000;">{Lpu_Name}</td>
</tr></table>

<table style="width: 100%;"><tr style="font-weight: bold;">
<td style="width: 55%; letter-spacing: 0.5em; text-align: center;">ТАЛОН АМБУЛАТОРНОГО ПАЦИЕНТА</td>
<td style="width: 25%; padding-right: 1em; text-align: right;">№ медицинской карты</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%; font-weight: bold;">0.</td>
<td style="width: 15%; font-weight: bold;">Код пациента</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">1.</td>
<td style="width: 15%; font-weight: bold;">Ф.И.О. пациента</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">2.</td>
<td style="width: 15%; font-weight: bold;">Дата рождения</td>
<td style="width: 22%; border-bottom: 1px solid #000;font-size: 14px;">{Person_Birthday}</td>
<td style="width: 7%; font-weight: bold; text-align: center;">3. Пол</td>
<td style="width: 13%; border-bottom: 1px solid #000;">{Sex_Name}</td>
<td style="width: 10%; font-weight: bold; text-align: center;">4. Участок</td>
<td style="width: 30%; border-bottom: 1px solid #000;">{LpuRegion_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">5.</td>
<td style="width: 15%; font-weight: bold;">Социальный статус</td>
<td style="width: 27%; border-bottom: 1px solid #000;">{SocStatus_Name}</td>
<td style="width: 50%; font-weight: bold; text-align: right; padding-right: 1em;">Член семьи воен.</td>
<td style="width: 5%;">[&nbsp;&nbsp;&nbsp;]</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">6.</td>
<td style="width: 15%; font-weight: bold;">Адрес регистрации</td>
<td colspan="2" style="border-bottom: 1px solid #000;">{UAddress_Name}</td>
</tr><tr>
<td>&nbsp;</td>
<td style="font-weight: bold;">Адрес факт. прожив.</td>
<td style="width: 12%; border-bottom: 1px solid #000;">{KLAreaType_Name}</td>
<td style="width: 70%; border-bottom: 1px solid #000; border-left: 1px solid #000; padding-left: 4px;">{PAddress_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">7.</td>
<td style="width: 15%; font-weight: bold;">Страховой полис</td>
<td style="width: 5%;">тип</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{PolisType_Name}</td>
<td style="width: 12%; text-align: right; padding-right: 1em;">серия, №</td>
<td style="border-bottom: 1px solid #000;font-size: 14px;" colspan="3">{Polis_Ser} {Polis_Num}</td>
</tr><tr>
<td colspan="2">&nbsp;</td>
<td>выдан</td>
<td style="border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
<td style="text-align: right; padding-right: 1em;">дата выдачи</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{Polis_begDate}</td>
<td style="width: 15%; text-align: right; padding-right: 1em;">дата окончания</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{Polis_endDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">8.</td>
<td style="width: 15%; font-weight: bold;">Документ</td>
<td style="width: 5%;">тип</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{DocumentType_Name}</td>
<td style="width: 8%; text-align: right; padding-right: 1em;">серия, №</td>
<td style="width: 37%; border-bottom: 1px solid #000;">{Document_Ser} {Document_Num}</td>
</tr><tr>
<td colspan="2">&nbsp;</td>
<td>выдан</td>
<td style="border-bottom: 1px solid #000;">{OrgDep_Name}</td>
<td style="text-align: right; padding-right: 1em;">дата</td>
<td style="border-bottom: 1px solid #000;">{Document_begDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">9.</td>
<td style="width: 15%; font-weight: bold;">Место работы</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{Org_Name}, {Post_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">10.</td>
<td style="width: 15%; font-weight: bold;">№ удостов. льготн.</td>
<td style="width: 27%; border-bottom: 1px solid #000;">{EvnUdost_Ser} {EvnUdost_Num}</td>
<td style="width: 5%;">&nbsp;</td>
<td style="width: 50%; border-bottom: 1px solid #000; font-size: 9px;">серия/номер для учета помощи, оказанной населению, имеющему право на льготу</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">11.</td>
<td style="width: 15%; font-weight: bold;">Инвалидность</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{PrivilegeType_Name}</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Шифр МКБ-10</td>
<td style="width: 13%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Дата установл.</td>
<td style="width: 13%; border-bottom: 1px solid #000;">{PersonPrivilege_begDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">12.</td>
<td style="width: 15%; font-weight: bold;">Кем направлен</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 5%;">&nbsp;</td>
<td style="width: 55%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr><tr>
<td style="font-weight: bold;">13.</td>
<td style="font-weight: bold;">Диагноз направ. учр.</td>
<td style="border-bottom: 1px solid #000;">&nbsp;</td>
<td>&nbsp;</td>
<td style="border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">14.</td>
<td style="width: 15%; font-weight: bold;">Наличие травмы</td>
<td style="width: 32%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 14%; font-weight: bold; padding-left: 1em;">Противоправная</td>
<td style="width: 6%;">[&nbsp;&nbsp;&nbsp;]</td>
<td style="width: 23%; font-weight: bold; padding-left: 1em;">15. Нетранспортабельность</td>
<td style="width: 7%;">[&nbsp;&nbsp;&nbsp;]</td>
</tr></table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">16.</td>
<td style="width: 97%; font-weight: bold;">Перечень посещений</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 7%;">Дата</th>
<th style="width: 7%;">Отделение</th>
<th style="width: 22%;">Врач</th>
<th style="width: 7%;">м/п</th>
<th style="width: 25%;">Наименование</th>
<th style="width: 12%;">Место обслуживания</th>
<th style="width: 10%;">Цель обращения</th>
<th style="width: 10%;">Вид оплаты</th>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">17.</td>
<td style="width: 97%; font-weight: bold;">Диагноз основной</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 10%;">Дата</th>
<th style="width: 20%;">Отделение</th>
<th style="width: 10%;">Код врача</th>
<th style="width: 20%;">Шифр МКБ-10</th>
<th style="width: 40%;">Характер заболевания</th>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">18.</td>
<td style="width: 97%; font-weight: bold;">Диагноз сопутствующий</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 10%;">Дата</th>
<th style="width: 20%;">Отделение</th>
<th style="width: 10%;">Код врача</th>
<th style="width: 20%;">Шифр МКБ-10</th>
<th style="width: 40%;">Характер заболевания</th>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">19.</td>
<td style="width: 97%; font-weight: bold;">Диспансеризация</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 15%;">Диспансерный учет</th>
<th style="width: 10%;">Шифр МКБ-10</th>
<th style="width: 15%;">Дата следующей явки</th>
<th style="width: 10%;">Взят</th>
<th style="width: 10%;">Снят</th>
<th style="width: 40%;">Причина снятия</th>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">20.</td>
<td style="width: 97%; font-weight: bold;">Документ временной нетрудоспособности</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 10%;" rowspan="2">Открыт</th>
<th style="width: 10%;" rowspan="2">Закрыт</th>
<th style="width: 10%;" rowspan="2">Тип</th>
<th style="width: 10%;" rowspan="2">Серия, №</th>
<th style="width: 20%;" rowspan="2">Причина выдачи</th>
<th style="width: 20%;" rowspan="2">Отметка о нарушении режима</th>
<th colspan="2">По уходу</th>
</tr><tr>
<th style="width: 10%;">Пол</th>
<th style="width: 10%;">Полных лет</th>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
<tr>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
<td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">21.</td>
<td style="width: 97%; font-weight: bold;">Освобождение от работы</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 25%;">ЛВН</th>
<th style="width: 15%;">Дата с</th>
<th style="width: 15%;">Дата по</th>
<th style="width: 45%;">Врач</th>
</tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
<tr><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td><td class="cell">&nbsp;</td></tr>
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">22.</td>
<td style="width: 15%; font-weight: bold;">Случай закончен</td>
<td style="width: 10%;">[&nbsp;&nbsp;&nbsp;]&nbsp;&nbsp;&nbsp;закончен</td>
<td style="width: 5%;"></td>
<td style="width: 67%;">[&nbsp;&nbsp;&nbsp;]&nbsp;&nbsp;&nbsp;не закончен</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%; font-weight: bold;">23.</td>
<td style="width: 15%; font-weight: bold;">Результат</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">24.</td>
<td style="width: 15%; font-weight: bold;">УКЛ</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">25.</td>
<td style="width: 15%; font-weight: bold;">Направление</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 18%;">&nbsp;</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 5%;"></td>
<td style="width: 62%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 2em;"><tr>
<td style="width: 55%; text-align: right; padding-right: 1em;">Код врача:</td>
<td style="width: 15%; border: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; text-align: right; padding-right: 1em;">Подпись врача:</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

</body>

</html>