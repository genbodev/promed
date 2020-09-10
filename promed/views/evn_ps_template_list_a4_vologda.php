<html>
<head>
    <title>{EvnPSTemplateTitle}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 0px;  width: 100%;}
        table { border-collapse: collapse; }
        span, div, td { font-family: times, tahoma, verdana; font-size: 12px; }
        th { text-align: center; font-size: 12px; border-collapse: collapse; border: 1px solid black; }
        .underline td {border-bottom: 1px solid #000;}
        .withBorder { margin-top: 10px; }
        .withBorder td{ border:1px solid #000; }
        .line { text-decoration: underline; font-weight: bold; }
        article { margin: 5px; }
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
        .line { text-decoration: underline; font-weight: bold; }
        article { margin: 5px; }
    </style>

    <style type="text/css">
        div.selector { display:none; }
        div.show_selector { display:none; }
        div.single_selector { display:inline; }
        div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }
        .broken_text { display: inline; padding: 0px; margin: 0px; }
        .line { text-decoration: underline; font-weight: bold; }
        article { margin: 5px; }
    </style>
</head>

<body>
<article>

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

<table style="width: 60%;"><tr>
    <td style="width: 15%;">7. Код территории проживания</td>
    <td style="width: 5%;">&nbsp;</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{Person_OKATO}</td>
    <td style="width: 5%;">&nbsp;</td>
    <td style="width: 20%;">Житель: 
        <span<?php if($KLAreaType_id==1) { echo " class='line'"; }?> > город - 1</span>; 
        <span<?php if($KLAreaType_id==2) { echo " class='line'"; }?> > село - 2</span>
    </td>
    <!--<td style="border-bottom: 1px solid #000;">{KLAreaType_id} {KLAreaType_Name}</td>-->
	<td></td>
</tr></table>

<table style="width: 60%;">
    <tr>
        <td style="width: 15%;">8. Страховой полис (серия, номер): </td>
        <td style="width: 45%; border-bottom: 1px solid #000; font-weight: bold;">{Polis_Ser} {Polis_Num} {OrgSmo_Name} {OMSSprTerr_Code}</td>
    </tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 65%;">9. Вид оплаты: 
        <span<?php if($PayType_Code==1) { echo " class='line'"; }?> > ОМС - 1</span>; 
        <span<?php if($PayType_Code==2) { echo " class='line'"; }?> >бюджет - 2</span>; 
        <span<?php if($PayType_Code==3 || $PayType_Code==4) { echo " class='line'"; }?> >платные услуги - 3</span>; 
        <span<?php if($PayType_Code==5) { echo " class='line'"; }?> >в т.ч. ДМС - 4</span>; 
        <span<?php if($PayType_Code==6) { echo " class='line'"; }?> >другое - 5</span>
    </td>
</tr><tr>
    <td>10. Социальный статус: 
        <span<?php if($SocStatus_Code==4 || $SocStatus_Code==16) { echo " class='line'"; }?> >дошкольник – 1</span>;
        <span<?php if($SocStatus_Code==16) { echo " class='line'"; }?> >организован – 2</span>;
        <span>неорганизован – 3</span>;
        <span<?php if($SocStatus_Code==5 || $SocStatus_Code==9) { echo " class='line'"; }?> >учащийся – 4</span>;
        <span<?php if($SocStatus_Code==2) { echo " class='line'"; }?> >работает – 5</span>;
        <span<?php if($SocStatus_Code==1 || $SocStatus_Code==3) { echo " class='line'"; }?> >не работает – 6</span>;
        <span<?php if($SocStatus_Code==8) { echo " class='line'"; }?> >БОМЖ – 7</span>;  
        <span<?php if($SocStatus_Code==3) { echo " class='line'"; }?> >пенсионер – 8</span>;
        <span<?php if($SocStatus_Code==6) { echo " class='line'"; }?> >военнослужащий – 9</span>;
        <span>код - ________</span>; 
        <span<?php if($SocStatus_Code==7) { echo " class='line'"; }?> >Член семьи военнослужащего – 10</span>.
    </td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 70%;">11. Категория льготности: 
        <span<?php if($PrivilegeType_Code==1) { echo " class='line'"; }?> > инвалид ВОВ - 1</span>; 
        <span<?php if($PrivilegeType_Code==2) { echo " class='line'"; }?> > участник ВОВ - 2</span>; 
        <span<?php if($PrivilegeType_Code==3) { echo " class='line'"; }?> > воин-интернационалист - 3</span>; 
        <span<?php if($PrivilegeType_Code==4) { echo " class='line'"; }?> > лицо, подвергш. радиационному облуч. - 4</span>;
        <span<?php if($PrivilegeType_Code==5) { echo " class='line'"; }?> > в т.ч. в Чернобыле - 5</span>; 
        <span<?php if($PrivilegeType_Code==6) { echo " class='line'"; }?> > инв. Iгр - 6</span>; 
        <span<?php if($PrivilegeType_Code==7) { echo " class='line'"; }?> > инв. IIгр - 7</span>; 
        <span<?php if($PrivilegeType_Code==8) { echo " class='line'"; }?> > инв. IIIгр - 8</span>; 
        <span<?php if($PrivilegeType_Code==9) { echo " class='line'"; }?> > ребенок-инвалид - 9</span>; 
        <span<?php if($PrivilegeType_Code==10) { echo " class='line'"; }?> > инвалид с детства - 10</span>; 
        <span<?php if($PrivilegeType_Code==11) { echo " class='line'"; }?> > прочие - 11</span>
    </td>
    <!--<td style="width: 30%; border-bottom: 1px solid #000;">{PrivilegeType_Code}</td>-->
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
    <td style="width: 10%; text-align: center;">Номер наряда</td>
    <td style="width: 30%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr></table>

<table style="width: 90%;"><tr>
    <td style="width: 15%;">14. Диагноз направившего учреждения</td>
    <td style="width: 75%; border-bottom: 1px solid #000; font-weight: bold;">{PrehospDiag_Code} {PrehospDiag_Name}</td>
</tr><tr>
    <td>15. Диагноз приемного отделения</td>
    <td style="border-bottom: 1px solid #000; font-weight: bold;">{AdmitDiag_Code}</td>
</tr></table>

<table style="width: 100%;"><tr>
    <td style="width: 65%;">16. Доставлен в состоянии опьянения (**): &nbsp;
        <span<?php if($PrehospToxic_Code==1) { echo " class='line'"; }?> > алкогольного - 1</span>; 
        <span<?php if($PrehospToxic_Code==2) { echo " class='line'"; }?> > наркотического - 2</span>
    </td>
    <td style="width: 35%;">&nbsp;</td>
    <!--<td style="width: 35%; border-bottom: 1px solid #000;">{PrehospToxic_Code}</td>-->
</tr><tr>
    <td>17. Госпитализирован по поводу данного заболевания в текущем году: &nbsp;
        <span<?php if($PrehospType_Code==2 || $PrehospType_Code==3) { echo " class='line'"; }?> > по экстренным показаниям</span>; 
        <span<?php if($PrehospType_Code==1) { echo " class='line'"; }?> > в плановом порядке</span>
    </td>
    <td style="width: 35%;">&nbsp;</td>
    <!--<td style="border-bottom: 1px solid #000;">{EvnPS_HospCountCode}: {EvnPS_HospCountName} {PrehospType_Code}: {PrehospType_Name}</td>-->
</tr><tr>
    <td>18. Доставлен в стационар от начала заболевания (получения травмы): &nbsp;
        <span<?php if($EvnPS_TimeDesease==1) { echo " class='line'"; }?> > в первые 6 часов - 1</span>; 
        <span<?php if($EvnPS_TimeDesease==2) { echo " class='line'"; }?> > в теч. 7-24 часов - 2</span>; 
        <span<?php if($EvnPS_TimeDesease==3) { echo " class='line'"; }?> > позднее 24 часов - 3</span>
    </td>
    <td>&nbsp;</td>
    <!--<td style="border-bottom: 1px solid #000;">{EvnPS_TimeDesease}</td>-->
</tr></table>

<table style="width: 100%;">
    <tr>
        <td style="width: 80px;">19. Травма: </td> 
        <td> - производственная: &nbsp;
            <span<?php if($PrehospTrauma_Code==1) { echo " class='line'"; }?> > промышленная - 1</span>; 
            <span<?php if($PrehospTrauma_Code==2) { echo " class='line'"; }?> > транспортная - 2</span>; 
            <span<?php if($PrehospTrauma_Code==3) { echo " class='line'"; }?> > в т.ч. ДТП - 3</span>; 
            <span<?php if($PrehospTrauma_Code==4) { echo " class='line'"; }?> > c/xоз - 4</span>; 
            <span<?php if($PrehospTrauma_Code==5) { echo " class='line'"; }?> > прочие - 5</span>;
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td> - непроизводственная: &nbsp;
            <span<?php if($PrehospTrauma_Code==6) { echo " class='line'"; }?> > бытовая - 6</span>;
            <span<?php if($PrehospTrauma_Code==7) { echo " class='line'"; }?> > уличная - 7</span>; 
            <span<?php if($PrehospTrauma_Code==8) { echo " class='line'"; }?> > транспортная - 8</span>; 
            <span<?php if($PrehospTrauma_Code==9) { echo " class='line'"; }?> > в т.ч. ДТП - 9</span>; 
            <span<?php if($PrehospTrauma_Code==10) { echo " class='line'"; }?> > школьная - 10</span>; 
            <span<?php if($PrehospTrauma_Code==11) { echo " class='line'"; }?> > спортивная 11</span>; 
            <span<?php if($PrehospTrauma_Code==12) { echo " class='line'"; }?> > противоправная травма - 12</span>; 
            <span<?php if($PrehospTrauma_Code==13) { echo " class='line'"; }?> > прочие - 13</span>
        </td>
    </tr>
    <!--<td style="width: 25%; border-bottom: 1px solid #000;">{PrehospTrauma_Code}</td>-->
</table>

<table style="width: 100%;"><tr>
    <td style="width: 35%;">20. Дата поступления в приемное отделение:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setDate}</td>
    <td style="width: 10%; text-align: center;">время:</td>
    <td style="width: 15%; border-bottom: 1px solid #000;">{EvnPS_setTime}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;">
    <tr>
        <td style="width: 20%;">21. Название отделения</td>
        <td style="border-bottom: 1px solid #000;" colspan="5">{LpuSectionFirst_Code} {LpuSectionFirst_Name}</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td style="width: 15%;">Дата поступления: </td>
        <td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setDate}</td>
        <td style="width: 10%; text-align: center;">время:</td>
        <td style="width: 15%; border-bottom: 1px solid #000;">{EvnSectionFirst_setTime}</td>
        <td style="width: 25%;">&nbsp;</td>
    </tr>
</table>

<table style="width: 100%; margin-bottom: 1em;"><tr>
    <td style="width: 15%; padding-left: 15px;">Подпись врача приемного отделения</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
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

<table style="width: 65%;">
    <tr>
        <td style="width: 65%;">24. Исход госпитализации: &nbsp;
            <span<?php if($LeaveType_Code==1 || $LeaveType_Code==2 || $LeaveType_Code==3) { echo " class='line'"; }?> > выписан - 1</span>; 
            <span<?php if($LeaveType_Code==2) { echo " class='line'"; }?> > в т.ч. в дневной стационар - 2</span>; 
            <span<?php if($LeaveType_Code==3) { echo " class='line'"; }?> > в круглосуточный стационар - 3</span>; 
            <span<?php if($LeaveType_Code==4) { echo " class='line'"; }?> > переведен в другой стационар - 4.</span>
        </td>
        <!--<td style="width: 35%; border-bottom: 1px solid #000;">{LeaveType_Code}</td>-->
    </tr>
    <tr>
        <td>24.1. Результат госпитализации: &nbsp;
             <span<?php if($ResultDesease_aCode==1) { echo " class='line'"; }?> > выздоровление - 1</span>; 
             <span<?php if($ResultDesease_aCode==2) { echo " class='line'"; }?> > улучшение - 2</span>; 
             <span<?php if($ResultDesease_aCode==3) { echo " class='line'"; }?> > без перемен - 3</span>; 
             <span<?php if($ResultDesease_aCode==4) { echo " class='line'"; }?> > ухудшение - 4</span>; 
             <span<?php if($ResultDesease_aCode==5) { echo " class='line'"; }?> > здоров - 5</span>; 
             <span<?php if($ResultDesease_aCode==6) { echo " class='line'"; }?> > умер - 6</span>.
        </td>
        <!--<td style="border-bottom: 1px solid #000;">{ResultDesease_aCode}</td>-->
    </tr>
</table>

<table style="width: 80%;"><tr>
    <td style="width: 15%;">25. Листок нетрудоспособности:</td>
    <td style="width: 10%;">открыт: </td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnStick_setDate}</td>
    <td style="width: 10%; text-align: center;">закрыт:</td>
    <td style="width: 10%; border-bottom: 1px solid #000;">{EvnStick_disDate}</td>
    <td style="width: 25%;">&nbsp;</td>
</tr><tr>
    <td>25.1. По уходу за больным:</td>
    <td>полных лет: </td>
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

<table style="width: 100%; margin-top: 1em;">
    <tr>
        <td style="width: 20%;">30. В случае смерти указать основную причину</td>
        <td style="width: 80%; border-bottom: 1px solid #000;">&nbsp;</td>
    </tr>
</table>

<table style="width: 100%;"><tr>
    <td style="width: 60%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 10%; text-align: center; padding-top: 10px;">Код по МКБ</td>
    <td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 10%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 1em;"><tr>
    <td style="width: 55%;">31. Дефекты догоспитального этапа: &nbsp;
        <span<?php if($EvnPS_IsImperHosp==1) { echo " class='line'"; }?> > несвоевременность госпитализации - 1</span>; 
        <span<?php if($EvnPS_IsShortVolume==2) { echo " class='line'"; }?> > недостаточный объем клинико-диагностического обследования - 2</span>;		
        <span<?php if($EvnPS_IsWrongCure==3) { echo " class='line'"; }?> > неправильная тактика лечения - 3</span>; 
        <span<?php if($EvnPS_IsDiagMismatch==4) { echo " class='line'"; }?> > несовпадение диагноза - 4</span>
    </td>
    <!--<td style="width: 45%; border-bottom: 1px solid #000;">{EvnPS_IsImperHosp} {EvnPS_IsShortVolume} {EvnPS_IsWrongCure} {EvnPS_IsDiagMismatch}</td>-->
</tr></table>

<table style="width: 100%; margin-top: 2em;"><tr>
    <td style="width: 20%;">Подпись лечащего врача</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 45%;">&nbsp;</td>
</tr></table>

<table style="width: 100%; margin-top: 2em;"><tr>
    <td style="width: 20%;">Подпись заведующего отделением</td>
    <td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 45%;">&nbsp;</td>
</tr></table>

</article>
</body>

</html>