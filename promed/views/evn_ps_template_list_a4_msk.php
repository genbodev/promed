<html>
<head>
    <title>{EvnPSTemplateTitle}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 5px; padding: 5px;  width: 100%;}
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
</head>

<body>

<table style="width: 100%;"><tr>
    <td style="width: 30%; vertical-align: top;">{Lpu_Name}</td>
	<td style="width: 40%; vertical-align: top; text-align: center;">&nbsp;</td>
    <td style="width: 30%; vertical-align: top; text-align: right;">
		<div>Медицинская документация</div>
        <div>Форма № 066/у-02</div>
        <div>Утверждена приказом Минздрава</div>
        <div>России от 30.12.2002г. №413</div>
    </td>
</tr></table>
<br>
<div style="text-align: center; font-weight: bold;">
	<div>СТАТИСТИЧЕСКАЯ КАРТА</div>
	<div>ВЫБЫВШЕГО ИЗ СТАЦИОНАРА КРУГЛОСУТОЧНОГО ПРЕБЫВАНИЯ</div>
</div>

<table style="width: 100%; margin: 0 auto;"><tr>
    <td style="width: 40%; text-align:right; padding-right:10px;">Номер медицинской карты</td>
    <td style="width: 20%; border: 1px solid #000; text-align: center;">{EvnPS_NumCard}</td>
    <td style="width: 40%;"></td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 15%;">1. Код пациента*</td>
    <td style="width: 12%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
    <td style="width: 10%; text-align: center;">2. Ф.И.О.</td>
    <td style="border-bottom: 1px solid #000; font-weight: bold;">{Person_Fio}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 6%;">3. Пол</td>
    <td style="width: 6%">{Sex_Name}</td>
    <td style="width: 17%; text-align: center;">4. Дата рождения</td>
    <td style="width: 17%; border-bottom: 1px solid #000; text-align: center;">{Person_Birthday}</td>
    <td>Полных лет: {Person_AgeYears} лет {Person_AgeMonths} мес. {Person_AgeDays} дн.</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 30%;">5. Документ, удостоверяющий личность (название, серия, номер)</td>
    <td style="width: 70%; border-bottom: 1px solid #000;">{DocumentType_Name} {Document_Ser} {Document_Num}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td>6. Адрес: регистрация по месту жительства: <div style="border-bottom: 1px solid #000; font-weight: bold;">{UAddress_Name}</div></td>
</tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 25%;">7. Код территории проживания</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{Person_OKATO}</td>
    <td style="width: 60%;">&nbsp;</td>
</tr><tr>
    <td>Житель: город - 1; село - 2</td>
    <td style="border-bottom: 1px solid #000;">{KLAreaType_id} {KLAreaType_Name}</td>
	<td></td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">8. Страховой полис (серия, номер):</td>
    <td style="width: 45%; border-bottom: 1px solid #000; font-weight: bold;">{Polis_Ser} {Polis_Num} {OrgSmo_Name} {OMSSprTerr_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 65%;">9. Вид оплаты: ОМС - 1; бюджет - 2; платные услуги - 3; в т.ч. ДМС - 4; другое - 5</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{PayType_Code} - {PayType_Name}</td>
</tr><tr>
    <td>10. Социальный статус: дошкольник - 1; организован - 2; неорганизован - 3; учащийся - 4; работает - 5; не работает - 6; БОМЖ - 7; пенсионер - 8; военнослужащий - 9; код - ____; член семьи военнослужащего - 10</td>
    <td style="border-bottom: 1px solid #000;">{SocStatus_Code}: {SocStatus_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 70%;">11. Категория льготности: инвалид ВОВ - 1; участник ВОВ - 2; воин-интернационалист - 3; лицо, подвергш. радиационному облуч. - 4;
        в т.ч. в Чернобыле - 5; инв. Iгр - 6; инв. IIгр - 7; инв. IIIгр - 8; ребенок-инвалид - 9; инвалид с детства - 10; прочие - 11</td>
    <td style="width: 30%; border-bottom: 1px solid #000;">{PrivilegeType_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">12. Кем направлен</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{PrehospOrg_Name}</td>
    <td style="width: 7%; text-align: center;">№ напр.</td>
    <td style="width: 13%; border-bottom: 1px solid #000;">{EvnDirection_Num}</td>
    <td style="width: 7%; text-align: center;">дата:</td>
    <td style="width: 13%; border-bottom: 1px solid #000;">{EvnDirection_setDate}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 15%;">13. Кем доставлен</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{PrehospArrive_Name}</td>
    <td style="width: 15%; text-align: center;">Номер наряда</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">14. Диагноз направившего учреждения</td>
    <td style="width: 65%; border-bottom: 1px solid #000; font-weight: bold;">{PrehospDiag_Code} {PrehospDiag_Name}</td>
</tr><tr>
    <td>15. Диагноз приемного отделения</td>
    <td style="border-bottom: 1px solid #000; font-weight: bold;">{AdmitDiag_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 65%;">16. Доставлен в состоянии опьянения: (**) алкогольного - 1; наркотического - 2</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{PrehospToxic_Code}</td>
</tr><tr>
    <td>17. Госпитализирован по поводу данного заболевания в текущем году: первично - 1; повторно - 2; по экстренным показаниям - 3; в плановом порядке - 4</td>
    <td style="border-bottom: 1px solid #000;">{EvnPS_HospCountCode}: {EvnPS_HospCountName} {PrehospType_Code}: {PrehospType_Name}</td>
</tr><tr>
    <td>18. Доставлен в стационар от начала заболевания (получения травмы): в первые 6 часов - 1; в теч. 7-24 часов - 2; позднее 24 часов - 3</td>
    <td style="border-bottom: 1px solid #000;">{EvnPS_TimeDesease}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 75%;">19. Травма: производственная: промышленная - 1; транспортная - 2; в т.ч. ДТП - 3; c/xоз - 4; прочие - 5; непроизводственная: бытовая - 6;
		уличная - 7; транспортная - 8; в т.ч. ДТП - 9; школьная - 10; спортивная 11; противоправная травма - 12; прочие - 13</td>
    <td style="width: 25%; border-bottom: 1px solid #000;">{PrehospTrauma_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">20. Дата поступления в приемное отделение:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
    <td style="width: 10%; text-align: center;">время:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">21. Название отделения</td>
    <td style="border-bottom: 1px solid #000;" colspan="5">{LpuSectionFirst_Code} {LpuSectionFirst_Name}</td>
</tr><tr>
    <td>&nbsp;</td>
    <td style="width: 15%;">Дата поступления: </td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setDate}</td>
    <td style="width: 10%; text-align: center;">время:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setTime}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-bottom: 1em;"><tr>
    <td style="width: 30%;">Подпись врача приемного отделения</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 10%; text-align: center;">код:</td>
    <td style="width: 40%; border-bottom: 1px solid #000;">{MedPersonalPriem_Code} {MedPersonalPriem_FIO}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">22. Дата выписки(смерти):</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_disDate}</td>
    <td style="width: 10%; text-align: center;">время:</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_disTime}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">23. Продолжительность госпитализации (койко-дней)</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnPS_KoikoDni}</td>
    <td style="width: 45%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 65%;">24. Исход госпитализации: выписан - 1; в т.ч. в дневной стационар - 2; в круглосуточный стационар - 3; переведен в другой стационар - 4.</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">{LeaveType_Code}</td>
</tr><tr>
    <td>24.1. Результат госпитализации: выздоровление - 1; улучшение - 2; без перемен - 3; ухудшение - 4; здоров - 5; умер - 6.</td>
    <td style="border-bottom: 1px solid #000;">{ResultDesease_aCode}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 45%;">25. Листок нетрудоспособности: открыт:</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnStick_setDate}</td>
    <td style="width: 10%; text-align: center;">закрыт:</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnStick_disDate}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr><tr>
    <td>25.1. По уходу за больным: полных лет:</td>
    <td style="border-bottom: 1px solid #000;">{PersonCare_Age}</td>
    <td style="text-align: center;">пол:</td>
    <td style="border-bottom: 1px solid #000;">{PersonCare_SexName}</td>
    <td>&nbsp;</td>
</tr></table>

<div style="page-break-after: always; margin-top: 2em;">
	<div>* Идентификационный номер пациента или иной, принятый в ЛПУ</div>
	<div>** Определение состояния опьянения осуществляется в соответствии с порядком, установленным Минздравом Росссии</div>
</div>

<table style="width: 100%;"><tr>
    <td style="text-align: center;">26. Движение пациента по отделениям</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="width: 5%;">№</th>
        <th style="width: 10%;">Код отделения</th>
        <th style="width: 7%;">Профиль коек</th>
        <th style="width: 8%;">Код врача</th>
        <th style="width: 10%;">Дата поступления</th>
        <th style="width: 10%;">Дата выписки, перевода (***)</th>
        <th style="width: 10%;">Код диагноза по МКБ</th>
        <th style="width: 20%;">Код медицинского стандарта (*)</th>
        <th style="width: 10%;">Код прерв. случая (**)</th>
        <th style="width: 10%;">Вид оплаты</th>
    </tr>
    <tr style="text-align: center;">
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
    </tr>
    {EvnSectionData}
    <tr>
        <td class="cell" style="text-align: center;">{Index}</td>
        <td class="cell">{LpuSection_Code}</td>
        <td class="cell">{LpuSectionBedProfile_Code}</td>
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

<div style="margin-bottom: 1em;">
	<div>* Проставляется в случае утверждения в субъекте РФ в установленном порядке</div>
	<div>** Заполняется при использовании в системе оплаты</div>
	<div>*** При выписке, переводе из отделения реанимации указать время пребывания в часах</div>
</div>

<table style="width: 100%;"><tr>
    <td style="text-align: center;">27. Хирургические операции (обозначить: основную операцию, использование спец. аппаратуры)</td>
</tr></table>

<table class='withBorder' style="width: 100%; border-collapse: collapse;"><tr>
    <th style="width: 10%;" rowspan='2'>Дата, час</th>
    <th style="width: 10%;" rowspan='2'>Код хирурга</th>
    <th style="width: 10%;" rowspan='2'>Код отделения</th>
    <th style="width: 20%;" colspan='2'>Операция</th>
    <th style="width: 20%;" colspan='2'>Осложнение</th>
    <th style="width: 5%;" rowspan='2'>Ане-<br />стезия (*)</th>
    <th style="width: 20%;" colspan='3'>Использ. спец. аппаратуры</th>
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
    <tr style="text-align: center;">
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>9</th>
        <th>10</th>
        <th>11</th>
        <th>12</th>
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

<div style="margin-bottom: 1em;">
	<div>* Анестезия: общая - 1; местная - 2</div>
</div>

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

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 40%;">30. В случае смерти указать основную причину</td>
    <td style="width: 60%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 20%;">Код по МКБ</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 60%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 55%;">31. Дефекты догоспитального этапа: несвоевременность госпитализации - 1; недостаточный объем клинико-диагностического обследования - 2;
		неправильная тактика лечения - 3; несовпадение диагноза - 4</td>
    <td style="width: 45%; border-bottom: 1px solid #000;">{EvnPS_IsImperHosp} {EvnPS_IsShortVolume} {EvnPS_IsWrongCure} {EvnPS_IsDiagMismatch}</td>
</tr></table>

<table style="width: 100%; margin-top: 2em;"><tr>
    <td style="width: 35%;">Подпись лечащего врача</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 30%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 2em;"><tr>
    <td style="width: 35%;">Подпись заведующего отделением</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 30%;">&nbsp;</td>
</tr></table>

</body>

</html>