<!html>
<head>
<title>{EvnPSTemplateTitle}</title>
<style type="text/css">
span.unchecked
		{
		    
		}		
		span.checked
		{
		    text-decoration: underline;
		}
		
@page port { size: portrait }
@page land { size: landscape }
body { padding: 0px; width:120mm; margin: auto; }
table { border-collapse: collapse; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.underline td {border-bottom: 1px solid #000;}
div.separator { display:block;}
</style>

<style type="text/css" media="print">
body { padding: 0px; width:120mm; }
span, div, td { font-family: times, tahoma, verdana; font-size: 10px; }
div.printLandscape{margin-top:-1mm; margin-left:-1mm;}
td { vertical-align: bottom; }
th { text-align: center; font-size: 10px; border-collapse: collapse; border: 1px solid black; }
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.underline td {border-bottom: 1px solid #000;}
div.separator { display:none;}
</style>

<style>
@media all
{
    div#print-control-block a.print-control
    {
        background-position: 0px 2px;
        display: block;        
        border-radius: 6px;
        height: 28px;
        width: 120px;
        font-weight: bold;
        color: #575656;
        text-decoration: none;
        padding: 12px 1px 1px 44px;
        background-color: #FFFFFF;
        float: right;
        background-image: url('/img/Print-icon_small.png');
        background-repeat: no-repeat;
    }

    div#print-control-block a.print-control:active
    {
        background-position: 1px 3px;
        padding: 13px 0 0 45px;    
    }

    div#print-control-block
    {
        display: block;
        width: 100%;
        top: 0px;
        left: 0px;        
        position:fixed;        
        padding: .5em 1em .5em 0;
        height: 44px;     
    }
}

@media print
{
    div#print-control-block, div#separator
    {
        display: none;
    }    
    
    
    div.firstpage
    {
     -moz-transform: rotate(90deg); /* Для Firefox */
     -ms-transform: rotate(90deg); /* Для IE */
     -webkit-transform: rotate(90deg); /* Для Safari, Chrome, iOS */
     -o-transform: rotate(90deg); /* Для Opera */
     transform: rotate(90deg);    	
    }
    
    div.secondpage
    {
     -moz-transform: rotate(-90deg); /* Для Firefox */
     -ms-transform: rotate(-90deg); /* Для IE */
     -webkit-transform: rotate(-90deg); /* Для Safari, Chrome, iOS */
     -o-transform: rotate(-90deg); /* Для Opera */
     transform: rotate(-90deg);    	
    }
    
        

}
</style>

</head>

<body>
<div id="print-control-block">
        <a class="print-control" href="javascript: window.print()" title="Вывод документа на печать">Отправить на печать</a>    
</div>
    
<div class="printLandscape firstpage">
<div class="separator"  style="height:2mm; border-top: 1px dashed #000;">&nbsp;</div>

<table style="width: 100%;"><tr>
<td style="width: 32%; text-align:center; ">{Lpu_Name}</td>
<td style="width: 68%; "></td>
</tr></table>

<div style="text-align: center; font-weight: bold;">
	<div>СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО № {EvnPS_NumCard}</div>
	<!-- <div>из {LpuUnitType_Name}</div>
	<div>№ {EvnPS_NumCard}</div> -->
</div>

<table style="width: 100%; margin-top: .3em;"><tr>
<td style="width: 3%; font-weight: bold;">0.</td>
<td style="width: 18%; font-weight: bold;">Код пациента</td>
<td style="width: 27%; border-bottom: 1px solid #000;">{PersonCard_Code}</td>
<td style="width: 52%;">&nbsp;</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 18%; font-weight: bold;">Ф.И.О. пациента</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{Person_Fio}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 18%; font-weight: bold;">Дата рождения</td>
<td style="width: 12%; border-bottom: 1px solid #000;">{Person_Birthday}</td>
<td style="width: 10%; font-weight: bold; text-align: center;">3. Пол</td>
<td style="width: 13%; border-bottom: 1px solid #000;">{Sex_Name}</td>
<td style="width: 13%; font-weight: bold; text-align: center;">4. Участок</td>
<td style="width: 34%; border-bottom: 1px solid #000;">{LpuRegion_Name}</td>
</tr></table>

<table style="width: 100%"><tr>
 <td style="width:25%; font-weight: bold; vertical-align:top; ">Документ, удост. личн.:</td>
 <td style="width:40%; border-bottom: 1px solid #000;">{DocumentType_Name}</td>
 <td style="width:10%; font-weight: bold; vertical-align:top; ">Номер</td>
 <td style="width:5%; border-bottom: 1px solid #000">{Document_Ser}</td>
 <td style="width:10%; font-weight: bold; vertical-align:top; ">Серия</td>
 <td style="width:5%; border-bottom: 1px solid #000">{Document_Num}</td>
 <!-- <td style="width: 8%; text-align: right; padding-right: 1em;">Выдан</td>
<td style="width: 37%; border-bottom: 1px solid #000;">{OrgDep_Name}</td> -->
 </tr></table>

<table style="width: 100%;"><tr>
<td style="width: 23%; font-weight: bold; vertical-align:top; ">Адрес регистрации</td>
<td colspan="2" style="border-bottom: 1px solid #000;">{UAddress_Name}</td>
</tr><tr>
<td style="font-weight: bold; vertical-align:top; ">Адрес факт. прожив.</td>
<td style="width: 12%; border-bottom: 1px solid #000;">{KLAreaType_Name}</td>
<td style="width: 70%; border-bottom: 1px solid #000; border-left: 1px solid #000; padding-left: 4px;">{PAddress_Name}</td>
</tr></table>

<table style="width:100%"><tr>
 <td style="width:32%; font-weight: bold;">Житель - код тер. проживания</td>
 <td style="width:30%; border-bottom: 1px solid #000">&nbsp;{OmsSprTerr_Code}</td>
 <td style="width:21%; font-weight: bold;">&nbsp;&nbsp;
 	<span class="<?php if($KLAreaType_SysNick == "city")  echo "checked"; else echo "unchecked"; ?>">города</span>, 
 	<span class="<?php if($KLAreaType_SysNick == "town")  echo "checked"; else echo "unchecked"; ?>">села</span>
 </td>
 <td style="width:17%; font-size:.5em">(нужн. подчерк.)</td>
 </tr></table> 

<table style="width: 100%;"><tr>
<td style="width: 18%; font-weight: bold; vertical-align:top;">Страховой полис</td>
<td style="width: 26%; border-bottom: 1px solid #000; vertical-align:top;">{PolisType_Name}</td>
<td style="width: 10%; text-align: right; padding-right: .1em; vertical-align:top;">серия, №</td>
<td style="width: 15%; border-bottom: 1px solid #000; vertical-align:top;">{Polis_Ser} {Polis_Num}</td>
<td style="width: 5%; text-align: right; padding-right: 1em; vertical-align:top;">выдан</td>
<td style="width: 26%; border-bottom: 1px solid #000; vertical-align:top;">{OrgSmo_Nick}</td>
</tr></table>

<!-- 
<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">&nbsp;</td>
<td style="width: 15%;">Терр. страхования</td>
<td style="width: 5%;">Код</td>
<td style="width: 10%; border-bottom: 1px solid #000;">{OmsSprTerr_Code}</td>
<td style="width: 10%; text-align: center;">Наименование</td>
<td style="width: 57%; border-bottom: 1px solid #000;">{OmsSprTerr_Name}</td>
</tr></table>
 -->
 
 <table style="width:100%;"><tr>
  <td style="width: 23px; font-weight:bold">Страховая категория</td>
  <td style="width:77%; border-bottom: 1px solid #000">&nbsp;</td>
</tr></table>

<table style="width: 100%; "><tr>
<td style="width: 18%; font-weight: bold;">Вид оплаты</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{PayType_Name}</td>
</tr></table>

<table style="width: 100%;"><tr>
<td style="width: 23%; font-weight: bold;">Социальный статус</td>
<td style="width: 30%; border-bottom: 1px solid #000;">{SocStatus_Name}</td>
<td style="width: 42%; font-weight: bold; text-align: right; padding-right: 1em;">Член семьи воен.</td>
<td style="width: 5%;">[&nbsp;&nbsp;&nbsp;]</td>
</tr></table>
  
<table style="width: 100%;"><tr>
<td style="width: 23%; font-weight: bold; vertical-align: top;">Категория льготы</td>
<td style="width: 30%; border-bottom: 1px solid #000;">{PrivilegeType_Name}</td>
<td style="width: 20%; font-weight: bold; padding-left: .1em;  vertical-align: top;">№ удост. льготн.</td>
<td style="width: 27%; border-bottom: 1px solid #000;">{EvnUdost_SerNum}</td>
</tr></table>


<!-- <table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">9.</td>
<td style="width: 15%; font-weight: bold;">Место работы</td>
<td style="width: 82%; border-bottom: 1px solid #000;">{OrgJob_Name}, {Post_Name}</td>
</tr></table> -->

<!-- Блок "Кем направлен" и "Цель госпитализации" -->
<table style="width:100%; margin:.5em 0 0 0;"><tr>
<td style="width:50%; vertical-align:top; border-right: 2px solid #000;">
<div style="margin-right: .5em">

<div style="width:100%;font-weight: bold;">Кем направлен:</div>
<div style="width:100%;border-bottom:1px solid #000;">{PrehospLpu_Id} {PrehospOrg_Name}</div>

<div style="width:100%;font-weight: bold;">Ф.И.О. направ. врача:</div>
<div style="width:100%;border-bottom:1px solid #000;">{MedPersonal_did}</div>

<div style="width:100%;font-weight: bold;">Диагноз направив. учреждения:</div>
<div style="width:100%;">
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
	<!--  {/EvnDiagPSHospData} -->	
	</table>
</div>

<div style="width:100%;font-weight: bold;">Доставлен в состян. опьянен.:</div>
<div style="width:100%;border-bottom:1px solid #000;">{PrehospToxic_Name}</div>

<div style="width:100%;font-weight: bold;">Доставлен в стационар по экстренным показ.:</div>
<div style="width:100%;border-bottom:1px solid #000;">{PrehospType_Name}</div>

<div style="width:100%;border-bottom:1px solid #000;">
<span style="font-weight: bold;">Доставка от начала заболевания:</span>
<span>{EvnPS_TimeDesease}</span>
</div>

<div style="width:100%;font-weight: bold;">Госпитал. в данном году по поводу данного заб-ния:</div>
<div style="width:100%;border-bottom:1px solid #000;">{EvnPS_HospCount}</div>

<div style="width:100%;font-weight: bold;">Дата поступления в стационар</div>
<div style="width:100%;border-bottom:1px solid #000;">{EvnPS_setDate}&nbsp;{EvnPS_setTime}</div>

</div>

</td>

<td style="width:50%; vertical-align:top;">

<div style="margin-left: .5em">
<div style="width:100%;font-weight: bold;">Цель госпитализации:</div>
<div style="width:100%;border-bottom:1px solid #000;"><!-- (1-лечение, 2-роды, 3-аборт, 4дообсл., 5-уточ. д-зов, 6-экспертный случ., 7-медико-соц. уход, 8-прочие) --> {HospType_Name}</div>

<div style="width:100%;font-weight: bold;">Срок беремен. (в неделях)</div>
<div style="width:100%;border-bottom:1px solid #000;">&nbsp;</div>

<div style="width:100%;font-weight: bold;">№ берем.</div>
<div style="width:100%;border-bottom:1px solid #000;">&nbsp;</div>

<div style="width:100%;font-weight: bold;">Вид травмы:</div>
<div style="width:100%;border-bottom:1px solid #000;">{PrehospTrauma_Name}</div>

<div style="width:100%;font-weight: bold;">Отделение</div>
<div style="width:100%;border-bottom:1px solid #000;">{HospSection_Name} </div>

<div style="width:100%;font-weight: bold;">Профиль коек</div>
<div style="width:100%;border-bottom:1px solid #000;">{HospSectionBedProfile_Name}</div>

<div style="width:100%;font-weight: bold;">Дата выписки (смерти)</div>
<div style="width:100%;">{Hosp_disDate}&nbsp;{EvnPS_disTime}</div>

<div style="width:100%;font-weight: bold;">Проведено дней
<span style="width:100%;border-bottom:1px solid #000;">&nbsp;&nbsp;&nbsp;{HospitalDays}&nbsp;&nbsp;&nbsp;</span></div>


<div> <span style="font-weight: bold;">Исход заболевания:</span>
<span style="width:100%;">
<span class="<?php if($HospLeaveType_Nick == "leave") echo "checked"; else echo "unchecked"?>">1 - выписан</span>,
<span class="<?php if($HospLeaveType_Nick == "die") echo "checked"; else echo "unchecked"?>">2 - умер</span>,
<span class="<?php if($HospLeaveType_Nick == "other") echo "checked"; else echo "unchecked"?>">3 - выписан в др. стационар</span>,
4 - выписан с наруш. режима,
5- - лечен. продол.,
<span class="<?php if($HospLeaveType_Nick == "ctac") echo "checked"; else echo "unchecked"?>">7 - выписан в дн. стац. при АПУ
</span>
</div>

<div> <span style="font-weight: bold;">Результат госпитал.:</span>
<span style="width:100%;">
<span class="<?php if($HospResultDesease_SysNick == "zdor") echo "checked"; else echo "unchecked"?>">1 - вызд.</span>,
<span class="<?php if($HospResultDesease_SysNick == "uluc") echo "checked"; else echo "unchecked"?>">2 - улучш.</span>,
<span class="<?php if($HospResultDesease_SysNick == "noefect") echo "checked"; else echo "unchecked"?>">3 - без измен.</span>,
<span class="<?php if($HospResultDesease_SysNick == "progress") echo "checked"; else echo "unchecked"?>">4 - ухуд.</span>,
<span class="<?php if($HospResultDesease_SysNick == "zdorvosst") echo "checked"; else echo "unchecked"?>">5 - здоров</span>
</span>
</div>

</div>
</td>
</tr></table>

<!-- 

<table style="width: 100%;"><tr>
<td style="width: 3%; font-weight: bold;">12.</td>
<td style="width: 15%; font-weight: bold;">Инвалидность</td>
<td style="width: 32%; border-bottom: 1px solid #000;">{InvalidType_Name}</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Дата установл.</td>
<td style="width: 13%; border-bottom: 1px solid #000;">{InvalidType_begDate}</td>
<td style="width: 12%; font-weight: bold; padding-left: 1em;">Шифр МКБ-X</td>
<td style="width: 13%; border-bottom: 1px solid #000;">&nbsp;</td>
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
-->

<div class="separator" style="height:8mm; border-bottom: 1px dashed #000">&nbsp;</div>
</div>
<div style="page-break-after: always;"></div>

<div class="printLandscape secondpage">
<div class="separator" style="height:8mm;">&nbsp;</div>

<table style="width: 100%;"><tr>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">Код<br />отделения</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">Профиль<br />коек</td>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">Врач</td>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">Дата<br />поступ-<br />ления</td>
<td style="width: 14%; text-align:center; vertical-align: top; border: 1px solid #000;">Дата<br />выписки<br />(перевода)</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">Код<br />диагноза<br />МКБ</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">Вид<br />лечения</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">Код<br />услуги</td>
</tr>
<tr>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 14%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 14%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 14%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 13%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top:2mm"><tr>
<td style="width: 40%; text-align:center; vertical-align: top; border: 1px solid #000;">Д-ноз основ. Выяв. впер.<br />Да; Нет</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">Осложнения</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">Диагноз сопутств.</td>
</tr>
<tr>
<td style="width: 40%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="width: 40%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 30%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin-top:2mm"><tr>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">Основной пат. диагноз</td>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">Осложнения</td>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">Сопутствующ. заб-ния</td>
</tr>
<tr>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="width: 33%; text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 40%; font-weight: bold; text-align:left; ">Непосредственная причина смерти</td>
<td style="width: 60%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 35%; font-weight: bold; text-align:left; ">Заболевание вызвавшее смерть</td>
<td style="width: 65%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 15%; font-weight: bold; text-align:left; ">Код услуги</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; font-weight: bold; text-align:left; ">Доля помощи</td>
<td style="width: 45%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 15%; font-weight: bold; text-align:left; ">Обследование на RW</td>
<td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; font-weight: bold; text-align:left; ">AIDS</td>
<td style="width: 35%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="font-weight: bold; text-align:left; ">Хирургические операции</td>
</tr>
</table>

<table style="width: 100%;"><tr>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">Дата<br />операц.</td>
<td style="width: 10%; text-align:center; vertical-align: top; border: 1px solid #000;">Время<br />операц</td>
<td style="width: 21%; text-align:center; vertical-align: top; border: 1px solid #000;">Операция</td>
<td style="width: 12%; text-align:center; vertical-align: top; border: 1px solid #000;">Основ<br />ной</td>
<td style="width: 14%; text-align:center; vertical-align: top; border: 1px solid #000;">Осложне<br />ния</td>
<td style="width: 11%; text-align:center; vertical-align: top; border: 1px solid #000;">Отде<br />ление</td>
<td style="width: 11%; text-align:center; vertical-align: top; border: 1px solid #000;">Врач</td>
<td style="width: 11%; text-align:center; vertical-align: top; border: 1px solid #000;">Аппа<br />ратура</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
<tr>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
<td style="text-align:center; vertical-align: top; border: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 20%; text-align:left; ">Тип обезбаливания</td>
<td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 20%; text-align:left; ">Вид оплаты анест.</td>
<td style="width: 40%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 20%; font-weight: bold; text-align:left; ">Вид нетрудосп.:</td>
<td style="width: 80%;">забол.;несчаст. случ.;карантин;уход за бол.;сан-кур. леч.;декр.оотп.;</td>
</tr>
</table>

<table style="width: 100%; margin: 2mm"><tr>
<td style="width: 25%; text-align:left; ">Дата открытия<br /><span style="text-size:.5em">(на закрытый бол. лист)</span></td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; text-align:left; ">Дата закрытия</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 15%; text-align:left; ">кол-во дней</td>
<td style="width: 15%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 60%; margin: 2mm"><tr>
<td style="width: 25%; text-align:left; ">Пол ухажив.</td>
<td style="width: 25%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 20%; text-align:left; ">Возраст ух.</td>
<td style="width: 30%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<table style="width: 80%; margin: 2mm"><tr>
<td style="width: 20%; font-weight: bold; text-align:left; ">Лечащий врач</td>
<td style="width: 80%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>

<div class="separator" style="height:4mm; border-bottom: 1px dashed #000">&nbsp;</div>
</div>
</body>

</html>