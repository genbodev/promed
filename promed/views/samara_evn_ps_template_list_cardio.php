<html>
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
        @page port {
            size: portrait;
        }

        @page land {
            size: landscape;
        }

        body {
            padding: 0px;
        	width: 190mm;
            margin: auto;
        }

        table {
            border-collapse: collapse;
        }

        .wordbreak{
            word-break:break-all;
        }
        
        .bordertable{
            border: 1px solid #000;
        	margin: .7em 0 .7em 0;
        }
        
        .bordertable table, .bordertable tr, .bordertable td {
            border: 1px solid #000;
        }
        
        .t2 table, .t2 tr, .t2 td {
            border: 1px solid #000;
        }

        span, div, td {
            font-family: times, tahoma, verdana;
            font-size: 1em;
        	padding: 0 0 0 .5em;
        }
        
        td.value{
        	vertical-align: top;
			font-weight : bold;
        }
        
        td.title{
        	vertical-align: top;
        }

        .t {
            width: 100%;
        }

            .t td:nth-child(odd) {
                white-space: nowrap;
            }

            .t td:nth-child(even) {            	
                border-bottom: 1px solid #000;
            }

        .c1 td:nth-child(even) {
            width: 100%;
        }

        .c2 td:nth-child(even) {
            width: 50%;
        }

        .c3 td:nth-child(even) {
            width: 33.3%;
        }


        thead {
            text-align: center;
        }

        .rotate {
            -webkit-transform: rotate(-90deg);
            -moz-transform: rotate(-90deg);
            -ms-transform: rotate(-90deg);
            -o-transform: rotate(-90deg);
            transform: rotate(-90deg);
            /* also accepts left, right, top, bottom coordinates; not required, but a good idea for styling */
            -webkit-transform-origin: 50% 50%;
            -moz-transform-origin: 50% 50%;
            -ms-transform-origin: 50% 50%;
            -o-transform-origin: 50% 50%;
            transform-origin: 50% 50%;
            /* Should be unset in IE9+ I think. */
            filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);        	
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
    	font-size: .8em;    	
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
    
    
    div.printLandscape
    {
     -moz-transform: rotate(90deg); /* Для Firefox */
     -ms-transform: rotate(90deg); /* Для IE */
     -webkit-transform: rotate(90deg); /* Для Safari, Chrome, iOS */
     -o-transform: rotate(90deg); /* Для Opera */
     transform: rotate(90deg);    	
    }
}
    </style>
</head>
<body>


<div id="print-control-block">
        <a class="print-control" href="javascript: window.print()" title="Вывод документа на печать">Отправить на печать</a>    
</div>

<div style='width:180mm'>
    <table style="font-size:0.8em">
        <tr>
            <td style="width: 30%; margin-left:1em; text-align:center; font-size:1.1em; vertical-align:top; padding-left:1em;">РФ
                <div>Министерство здравоохранения</div>
                <div style="font-weight:bold">{Lpu_Nick}</div>
                <div style="font-size:.8em">(Наименование учреждения)</div>
                <div style="font-weight:bold">{HospSection_Name}</div>
                <div style="font-size:.8em">(Отделение)</div>
            </td>
            <td style="width:40%;font-size:.9em; text-align:left; vertical-align:top; padding-left:1em;">
            	<div style="white-space: nowrap">ФГ</div>
            	<div style="white-space: nowrap">РВ</div>
            	<div style="white-space: nowrap">Гинеколог для женщин</div>
            	<div style="white-space: nowrap">Группа крови</div>
            	<div style="white-space: nowrap">Резус-принадлежность</div>
            	<div style="white-space: nowrap">Виды транспортировки:&nbsp;{EntranceModeType_id}</div>
            	<div style="white-space: nowrap">Дата и время поступления:&nbsp;{EvnPS_setDate}&nbsp;{EvnPS_setTime}</div>
            	<div style="white-space: nowrap">Дата и время выписки:&nbsp;{EvnPS_disDate}&nbsp;{EvnPS_disTime}</div>
            	<div style="white-space: nowrap">Проведено койко-дней:&nbsp;{HospitalDays}</div>
            </td>
            <td style="width:30%;vertical-align:top;text-align:center; padding-left:1em;">
            	<div>Учётная форма №3</div>
            	<div>Утверждена</div>
            	<div style="white-space:nowrap;">Министерством здравоохранения</div>
            	<div>СССР 13.12.1966 г.</div>
            </td>
        </tr>
       
    </table>

    <div style="text-align: left; font-weight: bold; margin-left:28%; margin-top:1.2em; margin-bottom:1em">
        <div style="text-transform: upercase; font-size:1.1em;">КАРТА № <span style="text-decoration: underline">{EvnPS_NumCard}</span></div>
        <div style="font-size: 1.1em">кардиологического больного</div>
    </div>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">Палата</td>
                <td class="value">&nbsp;</td>
                <td class="title">Переведен в отделение</td>
                <td class="value">&nbsp;{OtherStac_Name}&nbsp;{OtherLpu_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t с1">
        <tbody>
            <tr>
                <td class="title">1. Фамилия, имя, отчество:</td>
                <td class="value">{Person_Fio}</td>                
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">2. Возраст: </td>
                <td class="value">{Person_Age}</td>
                <td class="title">(полных лет) </td>                
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">3. Постоянное место жительства:
                <span class="<?php if($KLAreaType_SysNick == "city")  echo "checked"; else echo "unchecked"; ?>">город</span>, 
       			<span class="<?php if($KLAreaType_SysNick == "town")  echo "checked"; else echo "unchecked"; ?>">село</span>
                (подчеркнуть):
                </td>
                <td>&nbsp;</td>
            </tr>                        
       </tbody>
    </table>

        <table class="t c1">
        <tbody>
            <tr>
                <td class="title">&nbsp;</td>
                <td class="value">&nbsp;{UAddress_Name}</td>
            </tr>                        
       </tbody>
    </table>
    
     <table class="t c1">
        <tbody>
            <tr>
                <td class="title">&nbsp;</td>
                <td class="value">&nbsp;{DeputyKind_Name}&nbsp;{EvnPS_DeputyFIO}&nbsp;{EvnPS_DeputyContact}</td>
            </tr>                        
            <tr>
             	<td colspan="2" style="text-align:center; font-size: .75em; vertical-align: top">(вписать адрес, указав для периезжих-область, район, нас. пункт, адрес родственников и № тел.)</td>
            </tr>            
       </tbody>
    </table>
    
            
    
    <table class="t c1">
        <tbody>
            <tr>
               <td class="title">4. Место работы, профессия или должность</td>
               <td class="value">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
            <tr>
               <td class="title">&nbsp;</td>
               <td class="value">&nbsp;{OrgJob_Name}</td>
            </tr>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
            <tr>
               <td class="title">&nbsp;</td>
               <td class="value">&nbsp;{Post_Name}</td>
            </tr>
            <tr>
             	<td colspan="2" style="text-align:center; font-size: .75em; vertical-align: top">(для учащихся-место учебы, для инвалидов-род игруппа инвалидности, ИОВ-да, нет (подчеркнуть), УОВ-да, нет (подчеркнуть))</td>
            </tr>            
            </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">5. Кем направлен больной</td>
                <td class="value">{KemNapravlen}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td colspan="2" class="title">6. Доставлен в стационар по экстренным показаниям: 
                <span class="<?php if($PrehospType_SysNick == "extreme")  echo "unchecked"; else echo "checked"; ?>">&nbsp;нет,&nbsp;</span>
       			<span class="<?php if($PrehospType_SysNick == "extreme")  echo "checked"; else echo "unchecked"; ?>">&nbsp;да&nbsp;</span>                 
                через<span style="text-decoration: underline;">&nbsp;{EvnPS_TimeDesease}&nbsp;</span> часов после начала</td>
            </tr>
            <tr>
                <td class="title">заболевания, 
                <span class="<?php if($PrehospType_SysNick == "plan")  echo "checked"; else echo "unchecked"; ?>">&nbsp;госпитализирован в плановом порядке&nbsp;</span>
                (подчеркнуть)</td>
                <td class="value">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">7. Диагноз при поступлении</td>
                <td class="value">{PrimaryHospDiag_Name}</td>
            </tr>
            </tr>            
        </tbody>
    </table>
            
    <table class="t c1">
        <tbody>
                <td class="title">&nbsp;</td>
            	<td class="value">{EvnPS_PhaseDescr_did}</td>
        </tbody>
    </table>    
    
    <table class='bordertable wordbreak' style="width:100%;">
        <tbody>
            <tr>
                <td class="title"  style="width:50%">8. Даты и становления</td>
                <td class="title"  style="width:50%">9. Диагноз клинический</td>
            </tr>
            <tr>
            	<td class="title">&nbsp;{EvnPS_setDate}</td>
                <td class="title">&nbsp;{EvnPS_PhaseDescr_pid}</td>
          	</tr>            
          	<tr>
            	<td class="title">&nbsp;</td>
                <td class="title">&nbsp;</td>
          	</tr>
          	<tr>
            	<td class="title">&nbsp;</td>
                <td class="title">&nbsp;</td>
          	</tr>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
                <td class="title">10. Диагноз заключительный клинический</td>
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>  
    
    <table class="t c1">
        <tbody>
                <td class="title">а) основной</td>
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">б) осложнения основного</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">в) сопутствующий</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
        		<td class="title">&nbsp;</td>                
            	<td class="value">&nbsp;</td>
        </tbody>
    </table>                    
</div>    
</body>
</html>
