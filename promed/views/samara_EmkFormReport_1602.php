<!DOCTYPE html>
<html>
<head>
    <title>{EvnPSTemplateTitle}</title>
    <style type="text/css">
		body#EMK_Report
		{
			width: 170mm;
			margin-left: auto;
			margin-right: auto;
			font-size: 11px;    
			font-family: Arial, Helvetica, sans-serif;
		}

		body#EMK_Report td.title
		{
			vertical-align: top;
		}

		body#EMK_Report td.value
		{
			vertical-align: top;
			font-weight: bold;
		}

		body#EMK_Report span.unchecked
		{
			
		}

		body#EMK_Report span.checked
		{
			text-decoration: underline;
		}
        
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
				font-size: .9em;    	
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
		}
    </style>
</head>
<body id="EMK_Report">

<div id="print-control-block">
        <a class="print-control" href="javascript:window.print()" title="Вывод документа на печать">Отправить на печать</a>    
</div>

<div style='width:180mm; font-size: .9em;'>
    <table style="width:170mm;">
      <tr>
       <td style="width:100mm">&nbsp;</td>
       <td style="width:90mm; border:1px solid #000; font-size:1.2em;">
        <div style="padding: 1em 1em .3em 1em">Код формы по ОКУД  <?php if ($Lpu_id == 6011) echo("&nbsp;&nbsp;&nbsp;<span style='text-decoration: underline;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0609380&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"); else echo("&nbsp;&nbsp;&nbsp;<span style='text-decoration: underline;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>");  ?></div>
        <div style="padding: .3em 1em 1em 1em">Код учрежд. по ОКПО <?php if ($Lpu_id == 6011) echo("<span style='text-decoration: underline;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;01929347&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"); else echo("<span style='text-decoration: underline;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>");  ?></div>
       </td>
      </tr>
     </table>


     <table  style="margin-top:.2em; width:170mm; border-spacing: 0px;">
      <tr>

       <td style="width:60mm; font-size:1.1em; text-align:center; border:1px solid #000">
        Министерство здравоохранения РФ
       </td>

       <td rowspan="3" style="width:20mm; border: 1px solid #000;">&nbsp;</td>
       <td rowspan="3" style="width:40mm; border-top: 1px solid #000; border-bottom:1px solid #000;">&nbsp;</td>
       <td rowspan="3" style="width:50mm; border-top: 1px solid #000; border-bottom:1px solid #000; border-right: 1px solid #000; text-align:left;">
        Медицинская документация<br />Форма № 003/У Утверждена<br />Минздравом СССР<br />04.10.80 г. № 1030
        </td>
      </tr>
      <tr style="height:10mm">      
      <td style="font-size:.7em; text-align:center; border:1px solid #000">      
       {Lpu_Name}<br />{addressLpu}
       </td>
      </tr>
      <tr>
      <td style="text-align:center; border:1px solid #000">
       Наименование учреждения      
      </td>      
      </tr>     
     </table>
     
     <table style="width:170mm; margin-top: 2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">паспорт №</td>
       <td class="value" style="border-bottom:1px solid #000; width:34%">&nbsp; {Document_Num}</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">серия</td>
       <td class="value" style="border-bottom:1px solid #000; width:33%">&nbsp;{Document_Ser}</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">кем выдан</td>
       <td class="value" style="border-bottom:1px solid #000; width:33%">&nbsp;{Document_Org_Nick}</td>
      </tr>     
     </table>
     
     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">полис №</td>
       <td class="value" style="border-bottom:1px solid #000; width:50%">&nbsp;{Polis_Num}</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">серия</td>
       <td style="border-bottom:1px solid #000; width:50%">&nbsp;{Polis_Ser}</td>
      </tr>     
     </table>

     <div style="width:100%; text-align:center; font-size:1.4em; font-weight:bold; margin-top:.5em;">МЕДИЦИНСКАЯ КАРТА № <span style="text-decoration: underline;">&nbsp;{EvnPS_NumCard}</span></div>

     <div style="width:100%; text-align:center; font-size:1.4em; font-weight:bold">Стационарного больного</div>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Дата и время поступления</td>
       <td class="value" style="border-bottom:1px solid #000; width:100%;font-size:1.3em;word-spacing: 10px;">&nbsp;{EvnPS_setDT}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Дата и время выписки</td>
       <td class="value" style="border-bottom:1px solid #000; width:100%">&nbsp;{EvnPS_disDT}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Отделение</td>
       <td class="value" style="border-bottom:1px solid #000; width:50%">&nbsp;<?php if ($Lpu_id != 6011) echo(str_replace("Отделение", "", str_replace("отделение", "",$LpuSection_Name)))?></td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Палата №</td>
       <td style="border-bottom:1px solid #000; width:50%">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Переведен в отделение</td>
       <td class="value" style="border-bottom:1px solid #000; width:100%">&nbsp;{Lsection_name}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Проведено койко-дней</td>
       <td class="value" style="border-bottom:1px solid #000; width:100%">&nbsp;{koikodni}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Вид транспортировки</td>
       <td class="value" style="width:100%; font-size:1.1em; padding-left:1em;border-bottom:1px solid #000;">&nbsp;{EvnPS_EntranceMode}</td>
      </tr>     
     </table>
        
     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Группа крови</td>
       <td style="border-bottom:1px solid #000; width:50%">&nbsp;</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Резус-принадлежность</td>
       <td style="border-bottom:1px solid #000; width:50%">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Побочное действие лекарств (непереносимость)</td>
       <td style="width:100%; border-bottom:1px solid #000;">&nbsp;{EvnPS_DrugActions}</td>
      </tr>     
	  <tr>
	   <td>&nbsp;</td>
       <td class="title" style="text-align: center; vertical-align: top; font-size: .8em;">название препарата, характер побочного действия</td>
      </tr>
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
	   <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Страховая компания</td>
       <td class="value" colspan="4" style="width:100%; border-bottom:1px solid #000;font-size:1.3em;">&nbsp;{OSM_Name}</td>
       </tr>           
	   <tr>       
        <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Полис</td>
		<td class="title" style="font-size:1.1em;">Серия&nbsp;</td>
		<td class="value" style="width:50%;border-bottom:1px solid #000;font-size:1.3em;">&nbsp;&nbsp;&nbsp;{Polis_Ser}&nbsp;&nbsp;&nbsp;</td> 
		<td class="title" style="font-size:1.1em;">&nbsp;Номер&nbsp;</td>
		<td class="value" style="width:50%;border-bottom:1px solid #000;font-size:1.3em;">&nbsp;&nbsp;&nbsp;{Polis_Num}&nbsp;&nbsp;&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">1. Фамилия Имя Отчество</td>
       <td class="value" style="width:100%; border-bottom:1px solid #000; font-size:1.3em;">&nbsp;{fio}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td style="width:60%; border-bottom:1px solid #000;">&nbsp;</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">2. Пол</td>
       <td class="value" style="width:40%; border-bottom:1px solid #000;">&nbsp;{sex_name}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>       
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">3. Дата рождения</td>
       <td class="value" style="width:50%; border-bottom:1px solid #000;font-size:1.3em;">{Person_BirthDay}&nbsp;({age} лет)</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">(полных лет, для детей до 1 года ___ месяцев, до 1 месяца ____ дней)</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">4. Постоянное место жительства:    
	    <span class="<?php if($KLAreaType_SysNick == "city")  echo "checked"; else echo "unchecked"; ?>">город</span>, 
		<span class="<?php if($KLAreaType_SysNick == "town")  echo "checked"; else echo "unchecked"; ?>">село</span> (подчеркнуть)
	   </td>
       <td style="width:100%;border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>       
       <td class="value" style="width:100%; border-bottom:1px solid #000;font-size: 1.3em;">&nbsp;{Address_Address}
       </td>
      </tr>     
      <tr>
       <td style="text-align: center; vertical-align: top; font-size: .8em;">вписать адрес, указав для приезжих - область, район, нас. пункт, адрес регистрации.</td>
      </tr>
      <tr>       
       <td class="value" style="width:100%; border-bottom:1px solid #000; font-size: 1.3em;">
       &nbsp;<?php if(empty($PersonPhone_Phone) || $PersonPhone_Phone == 0) echo "" ;else echo " тел. ". $PersonPhone_Phone; ?>
       &nbsp;{DeputyKind_Name}&nbsp;{EvnPs_DeputyFIO}<?php if (empty($EvnPs_DeputyContact) || $EvnPs_DeputyContact == '&nbsp;') echo ""; else echo " тел. " . $EvnPs_DeputyContact; ?> </td>             
      </tr>     
      <tr>
      <td style="text-align: center; vertical-align: top; font-size: .8em;">№ телефона</td>
      </tr>
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">5. Место работы, профессия или должность</td>
       <td class="value" style="width:100%; border-bottom:1px solid #000;">&nbsp;{SocStatus_Name}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>       
       <td class="value" style="width:100%; border-bottom:1px solid #000;">&nbsp;{Org_Name},&nbsp;{Post_Name}</td>
      </tr>     
      <tr>
       <td style="text-align: center; vertical-align: top; font-size: .8em;">для учащихся - место учебы; для детей - название детского учреждения, школы; для инвалидов - род</td>
      </tr>
      <tr>       
       <td class="value" style="width:100%; border-bottom:1px solid #000;font-size: 1.3em;">{PrivilegeType_Code}&nbsp;{PrivilegeType_Name}&nbsp;{PersonPrivilege_Serie}&nbsp;{PersonPrivilege_Number}
       <?php if($PersonPrivilege_Group != "&nbsp;")  echo ", ".$PersonPrivilege_Group; ?>
       </td>
      </tr>     
      <tr>
      <td style="text-align: center; vertical-align: top; font-size: .8em;">и группа инвалидности, 
	    <span class="<?php if($PersonPrivilege_Group != "&nbsp;")  echo "checked"; else echo "unchecked"; ?>">да</span>, 
		<span class="<?php if($PersonPrivilege_Group == "&nbsp;")  echo "checked"; else echo "unchecked"; ?>">нет</span>  
      </tr>
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">6. Кем направлен больной</td>
       <td class="value" style="width:100%; border-bottom:1px solid #000;">&nbsp;<div style="width: 130mm; display:inline-block; position:absolute;font-size:1.1em; line-height: 4mm;">{WhoOrgDirected}, {WhoMedPersonalDirected}</div></td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: center; vertical-align: top; font-size: .8em;">название лечебного учреждения</td>
      </tr>
      <tr>       
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="font-size:1.2em">7. Доставлен в стационар по экстренным показаниям: 
	   <span class="checked">{extr}</span>;
       <span style="text-decoration: underline;">&nbsp;{TimeDeseaseType_Name}&nbsp;</span> после начала заболевания, получения травмы; 
       госпитализирован в плановом порядке (подчеркнуть) <span class="<?php if($pl == "да") echo "checked"; else echo "unchecked"; ?>">да</span></td>       
      </tr>      
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">8. Диагноз направившего учреждения</td>
       <td class="title" style="width:100%; border-bottom:1px solid #000;">&nbsp;{diagdir_name}</td>
      </tr>
      <tr>
        <td class="value" colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;{EvnPS_PhaseDescr_did}</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">9. Диагноз при поступлении</td>
       <td class="title" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr>
        <td class="value" colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">10. Диагноз клинический</td>
       <td style="border-bottom:1px solid #000; width:60%">&nbsp;</td>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Дата установления</td>
       <td class="value" style="border-bottom:1px solid #000; width:40%">&nbsp;</td>
      </tr>
      <tr>
       <td class="value" colspan="4" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
     </table>

     <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr>
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">11. Диагноз заключительный клинический</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      </table>

      <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr style="height:1.8em;">
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">а) основной</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height:1.8em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr style="height:1.8em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      </table>

      <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr style="height:1.8em;">
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">б) осложнение основного</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height:1.8em;">
       <td class="value" colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;{diagOsl_name}</td>
      </tr>           
      </table>

      <table style="width:170mm; margin-top: .2em; border-spacing: 0;">
      <tr style="height:1.8em;">
       <td class="title" style="white-space: nowrap; font-size:1.1em; vertical-align:top;">в) сопутствующий</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height:1.8em;">
       <td class="value" colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;{DiagSop_name}</td>
      </tr>           
     </table>
     
     <div style="page-break-after: always;"></div>
     <!--Следующая страница-->

      <table style="width:170mm; margin-top: .2em; border-spacing: 0;">     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000; height:1.8em;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000; height:1.8em;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000; height:1.8em;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000; height:1.8em;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="4" style="width:100%; border-bottom:1px solid #000; height:1.8em;">&nbsp;</td>
      </tr>     
     </table>

      <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="font-size:1.2em">12. Госпитализирован в данном году по поводу данного заболевания: впервые, повторно (подчеркнуть), всего _______ раз</td>       
      </tr>      
     </table>

     <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="font-size:1.2em">13. Хирургические операции, методы обезболивания и послеоперационные осложнения</td>       
      </tr>      
     </table>

     <table style="width:170mm; margin-top: .5em; border:2px solid #000; border-spacing: 0px;">
     <tr style="height:3em;">
      <td style="width: 50mm; font-weight:bold; text-align:center; border-bottom:2px solid #000; padding: .3em  1em .3em 1em; border-right:2px solid #000">Название операции</td>
      <td style="width: 30mm; font-weight:bold; text-align:center; border-bottom:2px solid #000; padding: .3em  1em .3em 1em; border-right:2px solid #000">Дата, час</td>
      <td style="width: 50mm; font-weight:bold; text-align:center; border-bottom:2px solid #000; padding: .3em  1em .3em 1em; border-right:2px solid #000">Метод обезболивания</td>
      <td style="width: 40mm; font-weight:bold; text-align:center; border-bottom:2px solid #000; padding: .3em  1em .3em 1em;">Осложнения</td>     
     </tr>
     <tr style="height:2em;">
      <td style="border-right:1px solid #000; text-align: left; padding: .3em  1em .3em 1em;"> 1. _______________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .3em  1em .3em 1em;"> _______________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .3em  1em .3em 1em;"> _________________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .3em  1em .3em 1em;"> _____________________</td>
     </tr>
     <tr style="height:2em;">
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .3em 1em;"> 2. _______________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .3em 1em;"> _______________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .3em 1em;"> _________________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .3em 1em;"> _____________________</td>
     </tr>
     <tr style="height:2em;">
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .5em 1em;"> 3. _______________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .5em 1em;"> _______________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .5em 1em;"> _________________________</td>
      <td style="border-right:1px solid #000; text-align: left; padding: .1em  1em .5em 1em;"> _____________________</td>
     </tr>
     </table>

     <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">14. Другие виды лечения</td>
       <td style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>
      <tr>
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: center; vertical-align: top; font-size: .8em;">(указать)</td>
      </tr>      
      <tr>
       <td colspan="2" style="width:100%;">для больных злокачественными новообразованиями - 1. Специальное лечение; хирургическое (дистанционная гамматерапия, рентгенотерапия, быстрые электроны, контактная гамматерапия и глубокая рентгенотерапия); комбинированное (хирургическое и гамматерапия, хирургическое и рентгенотерапия, хирургическое и сочетанное лучевое); химиопрепаратами, гормональными препаратами. 2. Поллмативное.  3. Симптоматическое лечение.</td>
      </tr>           
     </table>


      <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">15. Отметка о выдаче листка нетрудоспособности</td>
       <td style="width:100%;">&nbsp;</td>
      </tr>
      </table>

      <table style="width:170mm; margin-top: .5em;">
      <tr style="height:2em;">
       <td style="width:50%; padding: .1em  1em .1em 1em;"> № ______________ с ______________ по _____________</td>
       <td style="width:50%; padding: .1em  1em .1em 1em;"> № ______________ с ______________ по _____________</td>
      </tr>
      <tr style="height:2em;">
        <td style="width:50%; padding: .1em  1em .1em 1em;"> № ______________ с ______________ по _____________</td>
        <td style="width:50%; padding: .1em  1em .1em 1em;"> № ______________ с ______________ по _____________</td>
      </tr>      
     </table>

     <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">16. Исход заболевания</td>
       <td style="width:100%; border-bottom:1px solid #000;">&nbsp;{ResultDesease_Name}</td>
      </tr>
      </table>

      <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Переведен в другое учреждение</td>
       <td style="width:100%; border-bottom:1px solid #000;">&nbsp;{LeaveType_Name}</td>
      </tr>
     </table>

     <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="font-size:1.1em">17. Трудоспособность восстановлена полностью, снижена, временно утрачена, стойко утрачена в связи с данным заболеванием, с другими причинами (подчеркнуть)</td>       
      </tr>
     </table>


     <table style="width:170mm; margin-top: .5em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">18. Для поступивших на экспертизу - заключение</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
     <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
      <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
     </table>


     <table style="width:170mm; margin-top: .5em;">
      <tr style="height:2em;">
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">19. Особые отметки</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
     <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
      <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
     <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
      <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
      <tr style="height:2em;">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>           
     </table>


     <table style="width:170mm; margin-top: 5em;">
      <tr style="height:2em;">
       <td style="width:50%; font-weight: bold; text-align:center">Лечащий врач</td>
       <td style="width:50%; font-weight: bold; text-align:center">Зав. отделением</td>
      </tr>

      <tr style="height:4em;">
       <td style="text-align:center; vertical-align:bottom;">______________________________</td>
       <td style="text-align:center; vertical-align:bottom;">______________________________</td>
      </tr>           
     <tr>
       <td style="text-align:center; vertical-align:top; font-size:.8em;">подпись</td>
       <td style="text-align:center; vertical-align:top; font-size:.8em;">подпись</td>
      </tr>           

     </table>
<!--
     <div style="page-break-after: always;">&nbsp; </div>

     <table style="width:170mm; margin-top: 5em; height:118mm; border-bottom:2px solid #000;">
     <tr>
      <td style="width:100%; font-weight:bold; font-size:1.3em; text-align:center; vertical-align:top">ЗАПИСЬ ВРАЧА<br />ПРИЕМНОГО ПОКОЯ</td>
     </tr>
     </table>

     <table style="width:170mm; margin-top: 5em; height:118mm; border-bottom:2px solid #000;">
     <tr>
      <td style="width:100%; font-weight:bold; font-size:1.3em; text-align:center; vertical-align:top">ЖАЛОБЫ, АНАМНЕЗ<br />паталоги, предполоагаемый диагноз, план обследования</td>
     </tr>
     </table>

     <div style="page-break-after: always;">&nbsp; </div>

     <table style="width:170mm; margin-top: 5em; height:240mm; border-top:2px solid #000; border-spacing:0;">
     <tr style="height:12mm">
      <td style="width:30mm; text-align:center; vertical-align:middle; border-bottom:2px solid #000; border-right:1px solid #000;">Дата</td>
      <td style="width:140mm; text-align:center; border-bottom:2px solid #000;">Дневник</td>
     </tr>     
     <tr>
      <td style="border-right:1px solid #000;">&nbsp;</td>
      <td>&nbsp;</td>
     </tr>
     </table>


     <div style="page-break-after: always;">&nbsp;</div>

     <table style="width:170mm; margin-top: 5em; height:236mm; border-spacing:0;">
     <tr style="height:2em">
      <td style="width:40%; font-weight:bold; text-align:left">Карта № _____________________________</td>
      <td style="width:60%; font-weight:bold; text-align:left; border-bottom:1px solid #000;">&nbsp;</td>
     </tr>
     <tr style="height:2em">
      <td style="width:40%; text-align:center; vertical-align:top;">&nbsp;</td>
      <td style="width:60%; text-align:center; vertical-align:top;">Ф.И.О. больного</td>
     </tr>
     <tr>
      <td colspan="2" style="font-weight:bold; font-size: 1.2em; text-align:center; vertical-align:top;">ЭПИКРИЗ</td>
     <tr style="height:2em">
     <tr style="height:2em">
      <td>&nbsp;</td>
      <td style="font-weight:bold; text-align:left;">Подпись врача _____________________________</td>
     </tr>
     </table>

     <div style="page-break-after: always;">&nbsp;</div>

     <table style="width:170mm; margin-top: 5em; height:150mm; border-spacing:0;">
     <tr style="height:2em">
      <td style="width:100%; font-weight:bold; text-align:center; vertical-align:top;">Паталогическое (гитсологическое заключение)</td>      
     </tr>
     </table>

     
     <table style="width:170mm; margin-top: 1.2em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">Патоморофологический диагноз</td>
      </tr>
      </table>

      <table style="width:170mm; margin-top:0">
      <tr style="height: 2em">
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">а) основной</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height: 2em">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr>
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      <tr style="height: 2em">     
      </table>

      <table style="width:170mm; margin-top:  1.2em;">
      <tr style="height: 2em">
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">б) осложнение основного</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height: 2em">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr style="height: 2em">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      <tr style="height: 2em">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      </table>

      <table style="width:170mm; margin-top:  1.2em;">
      <tr style="height: 2em">
       <td style="white-space: nowrap; font-size:1.1em; vertical-align:top;">в) сопутствующий</td>
       <td style="border-bottom:1px solid #000; width:100%">&nbsp;</td>
      </tr>
      <tr style="height: 2em">
       <td colspan="2" style="width:100%; border-bottom:1px solid #000;">&nbsp;</td>
      </tr>     
      </table>

      <table style="width:170mm; margin-top: 1.4em;">
      <tr>
       <td style="white-space: nowrap; font-size:1.1em; text-align:right; font-weight:bold;">Подпись врача ____________________________________</td>
      </tr>
      </table>
	  -->
</div>    
</body>
</html>
