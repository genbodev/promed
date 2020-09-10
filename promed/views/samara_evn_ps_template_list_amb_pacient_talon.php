<html>
<head>
    <title>Талон амбулаторного пациента</title>
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
        
        .bordertablebordertable{
            border: 1px solid #000;
			font-size: 1em;
        }
        
        .bordertable table, .bordertable tr, .bordertable td, .bordertable th {
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

		p.punkt 
		{
			text-indent: 2em;		
		}
		
		p
		{
			margin: 0;
		}
		
		font.lower
		{
			text-transform: lowercase;
		}
		
		font.italic
		{
			font-style: italic;
		}
		
		font.normal
		{
			font-weight: normal;
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
	<div style="text-align: right; margin: .6em; font-size: .6em;">Форма № 025-12а/у-04<br />Утверждена приказом МЗСО от 02.12.04 № 368</div>

    <div style="font-size: .7em; text-align: center; font-weight: bold; margin: .5em; text-transform: uppercase">
		Талон амбулаторного пациента
    </div>
	
    <div style="text-align: justify; font-size: .67em; border: 2px solid black; padding:.5em;">
		<p><b>Дата открытия талона:</b> {EvnPS_setDate} <b>закрытия:</b> {EvnPS_disDate} <b>Код ЛПУ, направившего на лечение:</b> <?php echo(str_replace("(", "", str_replace(")", "", $PrehospLpu_Id))); ?> </p>
		<p><b>Вид обращения:</b> <font class="lower italic">{PrehospType_Name}</font> 
			<b>Цель обращения:</b> <font class="lower italic"><b>1</b>-лечебно-диагност., <b>2</b>-консульт., <b>3</b>-диспансер., <b>4</b>-профилакт., <b>6</b>-другое </font></p>
		<p><b>Результат обращения:</b> <font class="lower italic"><b>1</b>-выздоровл., <b>2</b>-улучшение, <b>3</b>-динамическое наблюден., <b>4</b>-госпитализация, <b>5</b>-в дневной стационар <b>6</b>-стационар на дому,
		<b>7</b>-на консультацию, <b>8</b>-на консультацию в другое ЛПУ, <b>9</b>-справка для получения путевки, <b>10</b>-сан.-кур. карта, <b>11</b>-справка и сан.кур. карта</font></p>
	</div>
    
	<div style="font-size: .8em; text-align: left; font-weight: bold; margin: .5em;">
		1. Сведения о пациенте
    </div>
	
	<div style="text-align: justify; font-size: .67em; border: 2px solid black; padding:.5em;">
		<p><b>Номер медицинской карты амбулаторного больного:</b> {EvnPS_NumCard} <span style="margin-left: 5%;"><b>Дата рождения</b> {Person_Birthday}</span>
		<span style="float: right"><b>Особые отметки: <font class="italic lower">1</b>-беременность, <b>2</b>-</font></span></p>
		<p><b>Код категории льготы</b> &nbsp;&nbsp;&nbsp; <b>Код категории льготы субъекта РФ</b> <?php if (strlen($PrivilegeType_Code) >4) echo(substr($PrivilegeType_Code, 2, 4)); else echo($PrivilegeType_Code); ?>  
		<span style="margin-left:5%"><b>СНИЛС</b>
		<script>
			var inp = '{Person_Snils}'; 
			var regexp = /^(\d{3})(\d{3})(\d{3})(\d{2})$/;			
			var snils = inp.replace(regexp, '$1-$2-$3 $4');
			document.write(snils);			
		</script>
		</span>
		<span style="float: right">
		<b>ЕНП</b> {Person_EdNum}</span>
		</p>
		<p><b>Ф.И.О. пациента</b> {Person_Fio} 
		<span style="float:right">
		<b>Пол: <font class="lower">{Sex_Code}-{Sex_Name}</font></b>
		</span></p>
		<p><b>Код СМО:</b> {OrgSmo_Code} <span style="float: right"><b>№ полиса</b> {Polis_Num} <b>Серия полиса:</b> {Polis_Ser}</span></p>
		<p><b>Документ:</b> код-{DocumentType_Code} наименование-{DocumentType_Name} 
		<span style="float: right;"><b>Серия:</b> {Document_Ser} <b>Номер:</b> {Document_Num}</span></p>
		<p><b>Адрес регистрации</b> {UAddress_Name}</p>
		<p><b>Адрес места жительства</b> {PAddress_Name}</p>
		<p><b>Житель:</b> <font class="lower">{KLAreaType_Name}</font></p>
		<p><b>Место работы (учебы)/должность:</b> {OrgJob_Name}, {Post_Name}</p>
		<p><b>Социальный статус:</b> {SocStatus_Name} 
		<span style="margin-left: 20%"><b>ЛПУ прикрепления:</b> {AttachedLpuNick}</span></p>
		<p><b>Страховая категория:</b> <font class="italic lower">1-застрах. работ., 2-застрах. неработ., 3-врем. незастрах., 4-бомж, 5-иногородний нражданин РФ, 6-иностранец</font></p>
		<p><b>Инвалидность:</b> <font class="lower">{InvalidType_Name}</font></p>
	</div>
	
	<div style="font-size: .8em; text-align: left; font-weight: bold; margin: .5em;">
		2. Сведения о посещениях <font class="lower italic normal" style="margin-left: 10%">1-врачебных&nbsp;&nbsp;&nbsp;2-среднего персонала</font>
    </div>
	
	<div style="text-align: justify; font-size: .67em; border: 2px solid black; padding:.5em;  display:block; height:15em;">
		<table style="float: left; width:50%; font-size:.9em;" class="bordertable">
			<thead>
				<tr>
					<th>Дата<br />посещения</th>
					<th>Место<br />обслуж.</th>
					<th>Цель<br />посещения</th>
					<th>Вид<br />оплаты</th>
					<th>Код<br />посещения</th>
					<th>Код врача<br /> или сред.<br />медраб.</th>
					<th>Код<br />должности</th>
				</tr>
				<tr>
					<th>1.</th>
					<th>2.</th>
					<th>3.</th>
					<th>4.</th>
					<th>5.</th>
					<th>6.</th>
					<th>7.</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		
		<table style="float: right; width50%; font-size:.9em;" class="bordertable">
			<thead>
				<tr>
					<th>Дата<br />посещения</th>
					<th>Место<br />обслуж.</th>
					<th>Цель<br />посещения</th>
					<th>Вид<br />оплаты</th>
					<th>Код<br />посещения</th>
					<th>Код врача<br /> или сред.<br />медраб.</th>
					<th>Код<br />должности</th>
				</tr>
				<tr>
					<th>1.</th>
					<th>2.</th>
					<th>3.</th>
					<th>4.</th>
					<th>5.</th>
					<th>6.</th>
					<th>7.</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div style="text-align: justify; font-size: .67em; border: 2px solid black; padding:.5em; margin-top: .5em;">
	<p><b>Талон: <span style="margin-left:25em">Дата:</span> <span style="margin-left: 13em;">Время:</span></b></p>
	<p><b>Врач: <span style="margin-left:41.7em">Кабинет:</span></b></p>
	</div>
	
</div>
</body>
</html>


