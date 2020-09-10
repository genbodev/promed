<html>
<head>
    <title>Медицинская карта амбулаторного бального</title>
    <style type="text/css">
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
			font-size: 1em;
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
	<div style="text-align: left; font-weight: bold; margin: 1em;">{Lpu_Name}</div>

    <div style="text-align: left; font-weight: bold; margin: 1em;">
        Медицинская карта амбулаторного больного № {EvnPS_NumCard}
    </div>
	
	<hr>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">Ф.И.О:</td>
                <td class="value">{Person_Fio}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">Пол:</td>
                <td class="value">{Sex_Name}</td>
                <td class="title">4. Дата рождения</td>
                <td class="value">{Person_Birthday}</td>
            </tr>
        </tbody>
    </table>

	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">Адрес:</td>
                <td class="value">{UAddress_Name}</td>
            </tr>
        </tbody>
    </table>
	
	<table class="t c3">
        <tbody>
            <tr>
                <td class="title">СМО:</td>
                <td class="value">{OrgSmo_Nick}</td>
                <td class="title">Полис номер:</td>
                <td class="value">{Polis_Num}</td>
				<td class="title">серия:</td>
                <td class="value">{Polis_Ser}</td>
            </tr>
        </tbody>
    </table>
	
    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">5. Документ, удостов. личность:</td>
                <td class="value">{DocumentType_Name} {Document_Ser}&nbsp;&nbsp;{Document_Num}</td>
            </tr>
        </tbody>
    </table>

    

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">ЛПУ прикрепления:</td>
                <td class="value">{AttachedLpuNick}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">ЛПУ направления:</td>
                <td class="value">{PrehospLpu_Id} {PrehospOrg_Nick}</td>
            </tr>
        </tbody>
    </table>

	<table class="t c2">
        <tbody>
            <tr>
                <td class="title">История болезни №</td>
                <td class="value"></td>
                <td class="title">Дата поступления:</td>
                <td class="value">{EvnPS_outcomeDate}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">Льгота:</td>
                <td class="value">{PrivilegeType_Name}</td>				
			</tr>
		</tbody>
	<table>

    <table class="t c2">
        <tbody>
            <tr>
				<td class="title">Удостоверение: Серия</td>
				<td class="value">{PersonPrivilege_Serie}</td>
				<td class="title">Номер:</td>
				<td class="value">{PersonPrivilege_Number}</td>
            </tr>
        </tbody>
    </table>
   
   
   <table class="t c1">
        <tbody>
            <tr>
                <td class="title">Социальный статус:</td>
                <td class="value">{SocStatus_Name}
				<?php if ($OrgJob_Name  == "&nbsp;") echo $OrgJob_Name; else echo ",&nbsp;&nbsp;".$OrgJob_Name; ?>				
				<?php if ($Post_Name == "&nbsp;") echo $Post_Name; else echo ",&nbsp;&nbsp;".$Post_Name; ?>
				</td>
            </tr>
        </tbody>
    </table>    
	
	<div>{PrehospDirect_Name}</div>
	
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">Родственники:</td>
                <td class="value">&nbsp;{DeputyKind_Name}&nbsp;{EvnPS_DeputyFIO}
					<?php if ($EvnPS_DeputyContact === "нет") echo ""; else echo " тел. ".$EvnPS_DeputyContact; ?></td>
            </tr>
        </tbody>
    </table>
</div>    
</body>
</html>
