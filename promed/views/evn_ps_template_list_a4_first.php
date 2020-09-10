<html>
<head>
<title>{EvnPSTemplateTitle}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.underline td {border-bottom: 1px solid #000;}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
td { vertical-align: bottom; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.underline td {border-bottom: 1px solid #000;}
</style>
</head>

<body>

<table style="width: 100%;"><tr>
<td style="width: 32%;">Наименование учреждения здравоохранения</td>
<td style="width: 68%; font-weight: bold; border-bottom: 1px solid #000;">{Lpu_Name}</td>
</tr></table>

<div style="text-align: center; font-weight: bold;">
	<div>СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО</div>
	<div>из {LpuUnitType_Name}</div>
	<div>№ {EvnPS_NumCard}</div>
</div>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%; font-weight: bold;">0.</td>
<td style="width: 15%; font-weight: bold;">Код пациента</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
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
<td style="width: 22%; border-bottom: 1px solid #000;">{Person_Birthday}</td>
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
<td style="width: 15%; border-bottom: 1px solid #000;">{PolisType_Name}</td>
<td style="width: 8%; text-align: right; padding-right: 1em;">серия, №</td>
<td style="width: 20%; border-bottom: 1px solid #000;">{Polis_Ser} {Polis_Num}</td>
<td style="width: 8%; text-align: right; padding-right: 1em;">выдан</td>
<td style="width: 31%; border-bottom: 1px solid #000;">{OrgSmo_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">&nbsp;</td>
<td style="width: 15%;">Терр. страхования</td>
<td style="width: 5%;">Код</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{OmsSprTerr_Code}</td>
<td style="width: 10%; text-align: center;">Наименование</td>
<td style="width: 57%; border-bottom: 1px solid #000;">{OmsSprTerr_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">8.</td>
<td style="width: 15%; font-weight: bold;">Документ</td>
<td style="width: 37%; border-bottom: 1px solid #000;">{DocumentType_Name} {Document_Ser} {Document_Num}</td>
<td style="width: 8%; text-align: right; padding-right: 1em;">Выдан</td>
<td style="width: 37%; border-bottom: 1px solid #000;">{OrgDep_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">9.</td>
<td style="width: 15%; font-weight: bold;">Место работы</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{OrgJob_Name}, {Post_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">10.</td>
<td style="width: 15%; font-weight: bold;">Категория льготы</td>
<td style="width: 35%; border-bottom: 1px solid #000;">{PrivilegeType_Name}</td>
<td style="width: 15%; font-weight: bold; padding-left: 1em;">11. № удост. льготн.</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{EvnUdost_SerNum}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">12.</td>
<td style="width: 15%; font-weight: bold;">Инвалидность</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{InvalidType_Name}</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Дата установл.</td>
<td style="width: 13%; border-bottom: 1px solid #000;">{InvalidType_begDate}</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Шифр МКБ-X</td>
<td style="width: 13%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%; font-weight: bold;">13.</td>
<td style="width: 15%; font-weight: bold;">Вид оплаты</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{PayType_Name}</td>
<td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">14.</td>
<td style="width: 15%; font-weight: bold;">Дата поступления</td>
<td style="width: 17%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
<td style="width: 8%; font-weight: bold; text-align: center;">Время</td>
<td style="width: 12%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
<td style="width: 45%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">15.</td>
<td style="width: 15%; font-weight: bold;">Кем направлен</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{PrehospDirect_Name}</td>
<td style="width: 5%;">&nbsp;</td>
<td style="width: 55%; border-bottom: 1px solid #000;">{PrehospOrg_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%;">&nbsp;</td>
<td style="width: 15%;">№ направления</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{EvnDirection_Num}</td>
<td style="width: 8%; text-align: center;">Дата</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{EvnDirection_setDate}</td>
<td style="width: 30%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">16.</td>
<td style="width: 15%; font-weight: bold;">Кем доставлен</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{PrehospArrive_Name}</td>
<td style="width: 8%; text-align: center;">Код</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{EvnPS_CodeConv}</td>
<td style="width: 12%; text-align: center;">Номер наряда</td>
<td style="width: 18%; border-bottom: 1px solid #000;">{EvnPS_NumConv}</td>
</tr></table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">17.</td>
<td style="width: 97%; font-weight: bold;">Диагнозы направившего учреждения</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 20%;">Вид диагноза</th>
<th style="width: 15%;">Код МКБ-10</th>
<th style="width: 65%;">Наименование диагноза</th>
</tr>
{EvnDiagPSHospData}
<tr class="underline">
<td class="cell">{DiagSetClass_Name}</td>
<td class="cell">{Diag_Code}</td>
<td class="cell">{Diag_Name}</td>
</tr>
{/EvnDiagPSHospData}
</table>

<table style="width: 100%; margin-top: 0.5em;"><tr>
<td style="width: 3%; font-weight: bold;">18.</td>
<td style="width: 97%; font-weight: bold;">Диагнозы приемного отделения</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 20%;">Вид диагноза</th>
<th style="width: 15%;">Код МКБ-10</th>
<th style="width: 65%;">Наименование диагноза</th>
</tr>
{EvnDiagPSAdmitData}
<tr class="underline">
<td class="cell">{DiagSetClass_Name}</td>
<td class="cell">{Diag_Code}</td>
<td class="cell">{Diag_Name}</td>
</tr>
{/EvnDiagPSAdmitData}
</table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%; font-weight: bold;">19.</td>
<td style="width: 15%; font-weight: bold;">Состояние опьянения</td>
<td style="width: 22%; border-bottom: 1px solid #000;">{PrehospToxic_Name}</td>
<td style="width: 15%;">&nbsp;</td>
<td style="width: 20%; font-weight: bold;">20. Тип госпитализации</td>
<td style="width: 25%; border-bottom: 1px solid #000;">{PrehospType_Name}</td>
</tr><tr>
<td style="font-weight: bold;">21.</td>
<td style="font-weight: bold;" nowrap="nowrap">Кол-во госпитализаций</td>
<td style="border-bottom: 1px solid #000;">{EvnPS_HospCount}</td>
<td>&nbsp;</td>
<td style="font-weight: bold;">22. Время с начала заболев.</td>
<td style="border-bottom: 1px solid #000;">{EvnPS_TimeDesease}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">23.</td>
<td style="width: 15%; font-weight: bold;">Наличие травмы</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{PrehospTrauma_Name}</td>
<td style="width: 14%; font-weight: bold; padding-left: 1em;">Противоправная</td>
<td style="width: 6%;">[&nbsp;{EvnPS_IsUnlaw}&nbsp;]</td>
<td style="width: 23%; font-weight: bold; padding-left: 1em;">24. Нетранспортабельность</td>
<td style="width: 7%;">[&nbsp;{EvnPS_IsUnport}&nbsp;]</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">25.</td>
<td style="width: 15%; font-weight: bold;">Название отделения</td>
<td style="width: 42%; border-bottom: 1px solid #000;">{LpuSection_Name}</td>
<td style="width: 40%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">26.</td>
<td style="width: 15%; font-weight: bold;">Врач прием. отд-ния</td>
<td style="width: 42%; border-bottom: 1px solid #000;">{PreHospMedPersonal_Fio}</td>
<td style="width: 40%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
<td style="width: 3%;">&nbsp;</td>
<td style="width: 15%; font-weight: bold;">Подпись врача</td>
<td style="width: 22%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; text-align: center;">Примечание</td>
<td style="width: 45%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<div style="page-break-after: always;"></div>

</body>

</html>