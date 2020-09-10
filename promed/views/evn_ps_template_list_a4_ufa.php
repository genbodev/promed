<html>
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
</head>

<body>

<table style="width: 100%;"><tr>
<td style="width: 70%; vertical-align: top;">Министерство здравоохранения РФ</td>
<td style="width: 30%; vertical-align: top;">
	<div>Форма № 066/у-02<div>
	<div>Утверждена приказом Минздрава РФ<div>
	<div>от 30.12.2002 г. №413<div>
		<td style="width: 5%; font-size: 22px; padding-right: 20px;"><b>{PersonRefugOrForeigner}</b></td>
</td>
</tr></table>

<div style="text-align: center; font-weight: bold;">СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО ИЗ СТАЦИОНАРА</div>

<table style="width: 100%;"><tr>
<td style="width: 15%;">№ медкарты</td>
<td style="width: 85%; border-bottom: 1px solid #000;"><b>{EvnPS_NumCard}</b></td>

</tr><tr>
<td style="border-bottom: 1px solid #000;" colspan="2"><b>{PolisType_Name} {Polis_Ser} {Polis_Num} {OrgSmo_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">1. Фамилия, имя, отчество</td>
<td style="width: 55%; border-bottom: 1px solid #000;"><b>{Person_Fio}</b></td>
<td style="width: 10%; border-bottom: 1px solid #000;">2. Пол</td>
<td style="width: 10%; border-bottom: 1px solid #000;"><b>{Sex_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">2.1. Масса</td>
<td style="width: 75%; border-bottom: 1px solid #000;"><b>{PersonWeight_Weight}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 12%;">3. Возраст</td>
<td style="width: 33%; border-bottom: 1px solid #000; text-align: center;"><b>{Person_Birthday} {Person_Age}</b></td>
<td style="width: 55%;"> (полных лет, для детей: до 1 года - месяцев, до 1 месяца - дней)</td>
</tr></table>


<table style="width: 100%;"><tr>
<td style="width: 45%;">4. Документ, удостов. личность: название, серия, номер</td>
<td style="width: 55%; border-bottom: 1px solid #000;"><b>{DocumentType_Name} {Document_Ser} {Document_Num}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 20%;">5. Постоянное место</td>
<td style="width: 30%; border-bottom: 1px solid #000; text-align: center;"><b>{KLAreaType_Name}</b></td>
<td style="width: 15%;">Телефон: </td>
<td style="width: 35%; border-bottom: 1px solid #000; text-align: center;"><b>{Person_Phone}</b></td>
</tr>
<tr><td colspan="4">
	<div style="border-bottom: 1px solid #000; font-weight: bold;">{PAddress_Name}</div>
	<div>Адрес регистрации:</div>
	<div style="border-bottom: 1px solid #000; font-weight: bold;">{UAddress_Name}</div>
</td></tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">6. Вид</td>
<td style="width: 25%; border-bottom: 1px solid #000;"><b>{PayType_Name}</b></td>
<td style="width: 20%;">7. Социальный статус</td>
<td style="width: 45%; border-bottom: 1px solid #000;"><b>{SocStatus_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">8. Код категории</td>
<td style="width: 25%; border-bottom: 1px solid #000;"><b>{InvalidType_Name}</b></td>
<td style="width: 15%;">9. Кем направлен</td>
<td style="width: 45%; border-bottom: 1px solid #000;"><b>{PrehospOrg_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">10. Кем доставлен</td>
<td style="width: 85%; border-bottom: 1px solid #000;"><b>{PrehospArrive_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 35%;">11. Диагноз направившего учреждения</td>
<td style="width: 65%; border-bottom: 1px solid #000;"><b>{PrehospDiag_Name}</b></td>
</tr><tr>
<td>12. Диагноз приемного отделения</td>
<td style="border-bottom: 1px solid #000;"><b>{AdmitDiag_Name}</b></td>
</tr><tr>
<td>12.1 Состояние при поступлении</td>
<td style="border-bottom: 1px solid #000;"><b>{DiagSetPhase_Name}</b></td>
</tr><tr>
<td>13. Доставлен в состоянии опьянения</td>
<td style="border-bottom: 1px solid #000;"><b>{PrehospToxic_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 20%;">14. Госпитализирован</td>
<td style="width: 80%; border-bottom: 1px solid #000;"><b>{PrehospType_Name} {EvnPS_HospCount}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 35%;">15. Доставлен в стационар от начала</td>
<td style="width: 65%; border-bottom: 1px solid #000;"><b>{EvnPS_TimeDesease} {EvnPS_TimeDeseaseUnit}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">16. Травма</td>
<td style="width: 90%; border-bottom: 1px solid #000;"><b>{PrehospTrauma_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 35%;">17. Дата и время поступления в приемное</td>
<td style="width: 65%; border-bottom: 1px solid #000;"><b>{EvnPS_setDate} {EvnPS_setTime}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">18. Отделение</td>
<td style="width: 45%; border-bottom: 1px solid #000;"><b>{LpuSectionFirst_Name}</b></td>
<td style="width: 15%;">19. Поступил</td>
<td style="width: 25%; border-bottom: 1px solid #000;"><b>{EvnSectionFirst_setDate} {EvnSectionFirst_setTime}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 35%;">Подпись врача приемного отделения</td>
<td style="width: 1%;"><nobr><b>{MPFirst_Fio}</b>&nbsp;</nobr></td>
<td style="border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 15%;">20. Дата выписки</td>
<td style="width: 45%; border-bottom: 1px solid #000;"><b>{EvnPS_disDate}</b></td>
<td style="width: 15%;">Время</td>
<td style="width: 25%; border-bottom: 1px solid #000;"><b>{EvnPS_disTime}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 45%;">21. Продолжительность госпитализации (койко-дней)</td>
<td style="width: 25%; border-bottom: 1px solid #000;"><b>{EvnPS_KoikoDni}</b></td>
<td style="width: 30%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 10%;">22. Исход</td>
<td style="width: 35%; border-bottom: 1px solid #000;"><b>{LeaveType_Name}</b></td>
<td style="width: 15%;">22.1. Результат</td>
<td style="width: 40%; border-bottom: 1px solid #000;"><b>{ResultDesease_Name}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 40%;">23. Листок нетрудоспособности: открыт</td>
<td style="width: 20%; border-bottom: 1px solid #000;"><b>{EvnStick_setDate}</b></td>
<td style="width: 10%;">закрыт</td>
<td style="width: 30%; border-bottom: 1px solid #000;"><b>{EvnStick_disDate}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 40%;">23.1. По уходу за больным. Полных лет:</td>
<td style="width: 20%; border-bottom: 1px solid #000;"><b>{PersonCare_Age}</b></td>
<td style="width: 10%;">Пол: </td>
<td style="width: 10%; border-bottom: 1px solid #000;"><b>{PersonCare_SexName}</b></td>
<td style="width: 20%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td>24. Движение пациента по отделениям</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;">
<tr>
<th style="width: 35%;">Отделение</th>
<th style="width: 15%;">Дата поступл.</th>
<th style="width: 15%;">Дата выписки</th>
<th style="width: 10%;">Код МКБ</th>
<th style="width: 10%;">Код КСГ</th>
<th style="width: 5%;">Вид оплаты</th>
<th style="width: 10%;">УКЛ</th>
</tr>
{EvnSectionData}
<tr>
<td class="cell"><b>{LpuSection_Name} {LpuSectionNarrowBedProfile_Name}</b></td>
<td class="cell"><b>{EvnSection_setDT}</b></td>
<td class="cell"><b>{EvnSection_disDT}</b></td>
<td class="cell"><b>{EvnSectionDiagOsn_Code}</b></td>
<td class="cell"><b>{EvnSection_KSG}</b></td>
<td class="cell"><b>{PayType_Name}</b></td>
<td class="cell"><b>{EvnSection_UKL} </b></td>
</tr>
{/EvnSectionData}
</table>

<table style="width: 100%;"><tr>
<td>25. Хирургические операции</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<th style="width: 5%;">Дата, час</th>
<th style="width: 10%;">Код хирурга</th>
<th style="width: 10%;">Код отделения</th>
<th style="width: 15%;">Наименование операции</th>
<th style="width: 5%;">Код</th>
<th style="width: 15%;">Осложнение</th>
<th style="width: 5%;">Код</th>
<th style="width: 15%;">Анест.</th>
<th style="width: 5%;">энд.</th>
<th style="width: 5%;">лазер</th>
<th style="width: 5%;">криог.</th>
<th style="width: 5%;">Вид оплаты</th>
</tr>
{EvnUslugaOperData}
<tr class="underline">
<td class="cell"><b>{EvnUslugaOper_setDT}</b></td>
<td class="cell"><b>{EvnUslugaOperMedPersonal_Code}</b></td>
<td class="cell"><b>{EvnUslugaOperLpuSection_Code}</b></td>
<td class="cell"><b>{EvnUslugaOper_Name}</b></td>
<td class="cell"><b>{EvnUslugaOper_Code}</b></td>
<td class="cell"><b>{AggType_Name}</b></td>
<td class="cell"><b>{AggType_Code}</b></td>
<td class="cell"><b>{EvnUslugaOperAnesthesiaClass_Name}</b></td>
<td class="cell" style="text-align: center;">&nbsp;<b>{EvnUslugaOper_IsEndoskop}</b>&nbsp;</td>
<td class="cell" style="text-align: center;">&nbsp;<b>{EvnUslugaOper_IsLazer}</b>&nbsp;</td>
<td class="cell" style="text-align: center;">&nbsp;<b>{EvnUslugaOper_IsKriogen}</b>&nbsp;</td>
<td class="cell"><b>{EvnUslugaOperPayType_Name}</b></td>
</tr>
{/EvnUslugaOperData}
</table>

<table style="width: 100%;"><tr>
<td style="width: 20%;">26. Обследован: RW 1</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 18%;">Обследован: AIDS 2</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 32%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td>27. Диагноз стационара (при выписке)</td>
</tr></table>

<table style="width: 100%; border-collapse: collapse;"><tr>
<td style="width: 20%;" class="cell">&nbsp;</td>
<td style="width: 19%;" class="cell">Основн. заболевание</td>
<td style="width: 7%;" class="cell">МКБ</td>
<td style="width: 20%;" class="cell">Осложнение</td>
<td style="width: 7%;" class="cell">МКБ</td>
<td style="width: 20%;" class="cell">Сопутствующее</td>
<td style="width: 7%;" class="cell">МКБ</td>
</tr><tr>
<td class="cell">Клинич. заключит.</td>
<td class="cell"><b>{LeaveDiag_Name}</b></td>
<td class="cell"><b>{LeaveDiag_Code}</b></td>
<td class="cell"><b>{LeaveDiagAgg_Name}</b></td>
<td class="cell"><b>{LeaveDiagAgg_Code}</b></td>
<td class="cell"><b>{LeaveDiagSop_Name}</b></td>
<td class="cell"><b>{LeaveDiagSop_Code}</b></td>
</tr><tr>
<td class="cell">Пат.-анатомический</td>
<td class="cell"><b>{AnatomDiag_Name}</b></td>
<td class="cell"><b>{AnatomDiag_Code}</b></td>
<td class="cell"><b>{AnatomDiagAgg_Name}</b></td>
<td class="cell"><b>{AnatomDiagAgg_Code}</b></td>
<td class="cell"><b>{AnatomDiagSop_Name}</b></td>
<td class="cell"><b>{AnatomDiagSop_Code}</b></td>
</tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 40%;">28. В случае смерти указать основную причину</td>
<td style="width: 45%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 5%;">МКБ</td>
<td style="width: 10%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 30%;">29. Дефекты догоспитального этапа</td>
<td style="width: 70%; border-bottom: 1px solid #000;"><b>{EvnPS_IsDiagMismatch} {EvnPS_IsImperHosp} {EvnPS_IsShortVolume} {EvnPS_IsWrongCure}</b></td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 25%;">Подпись лечащего врача</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 25%;">Подпись зав. отделения</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

</body>

</html>