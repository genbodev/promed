<html>
<head>
    <title>ДОГОВОР об оказании услуг</title>
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
        	padding: 0 0 0 0;
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
			font-size:inherit;
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
		
		p.punkt 
		{
			text-indent: 2em;
			margin: 0;
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
    <table style="width:100%">
        <tr>
            <td style="width: 50%; margin-left:1em">&nbsp;</td>

            <td style="width: 50%;">
            <div style='text-align:right; font-size:.7em;'>
				Приложение № 12 к приказу<br />
				от &quot;20&quot; декабря 2012 № 964
            </div>
            </td>
        </tr>
       
    </table>

    <div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">
        <div>ДОГОВОР № ______</div>
        <div>об оказании услуг</div>        
    </div>

	<div style="margin-bottom:.8em; font-size:.7em;">
		<span>г. Самара</span>
		<span style="float:right;">&quot;_____&quot; ___________ 201__ г.</span>
	</div>
	
	
    <div style="font-size:1em; text-align: justify;">
	<div style="font-size: .7em;">
	<p class="punkt">Государственное бюджетное учреждение здравоохранения &quot;Самарский областной клинический госпиталь для ветеранов войн&quot;, 
	именуемое в дальнейшем &quot;Исполнитель&quot;, в лице начальника госпиталя Яковлева Олега Григорьевича, действующего на основании Устава и лицензии от 06.12.2013г.
	№ ЛО-63-01-002353 выданной Министерством здравоохранения Самарской области (443020, г. Самара, ул. Ленина, д.73, т. 88463329309), ОГРН 1036300897665 свидетельство 
	о внесении записи в ЕГРЮЛ серия 63 №002562617 от 14.03.2003, с одной стороны, и</p> 
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">&nbsp;</td>
                <td class="value" style="text-align: center;">{Person_Fio}</td>
            </tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">(ФИО физического лица, наименование юридического лица)</td>
            </tr>
        </tbody>
    </table>	
	именуемого в дальнейшем &quot;Заказчик&quot;, действующий на основании
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">&nbsp;</td>
                <td class="value" style="text-align: center;">{DocumentType_Name}, {Document_Ser} № {Document_Num}</td>
            </tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">(документ, удостоверяющий личность, либо уставные документы)</td>
            </tr>
        </tbody>
    </table>	
	с  другой стороны, заключили договор о нижеследующем:
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">1. Предмет договора</div>
	
	<p class="punkt">1.1 Исполнитель обязуеься оказать услуги по предоставлению бытовых и сервисных услуг повышенной комфортности сверх установленных стандартов в рамках
	государственных гарантий бесплатной медицинской помощи, а Заказчик обязуется их оплатить.</p>
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">2. Порядок расчетов</div>
	
	<p class="punkt">2.1 Оказываемые по настоящему договору услуги могут оплачены самим Заказчиком либо третьим заинтересованным физическим или юридическим лицом на
	основании счета-фактуры, являющейся неотъемлемой частью договора, путем безналичного расчета через кредитные организации (банки) либо внесения наличных денег непосредственно в кассу
	ГБУЗ &quot;СОКГВВ&quot; с выдачей Заказчику документа, подтверждающего оплату (кассового чека).</p>
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">3. Права и обязанности сторон</div>
	<p class="punkt">3.1 Исполнитель обязуется:</p>
	<ul style="list-style-type: none; margin: 0;">
	<li>3.1.1 Предоставить Заказчику бытовые и сервисные услуги повышенной комфортности.</li>
	<li>3.1.2 Обеспечить соблюдение прав Заказчика, предусмотренные законодательством Российской Федерации.</li>
	<li>3.1.3 Проинформировать Заказчика о возможностях и условиях получения медицинской помощи в рамках государственных гарантий бесплатной медицинской помощи.</li>	
	</ul>
	
	<p class="punkt">3.2 Исполнитель имеет право:</p>
	<ul style="list-style-type: none; margin: 0;">
	<li>3.2.1 Требовать оплату за оказанную услугу.</li>	
	</ul>
	
	<p class="punkt">3.3 Заказчик обязан:</p>
	<ul style="list-style-type: none; margin: 0;">
	<li>3.3.1 Соблюдать распорядок дня, бережно относиться к имуществу ГБУЗ &quot;СОКГВВ&quot;.</li>	
	<li>3.3.2 Оплатить оказанные услуги.</li>	
	</ul>
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">4. Ответственность сторон</div>
	<p class="punkt">4.1 За неисполнение или ненадлежащее исполнение условий договора стороны несут ответственность в соответствии с действующим
	законодательством Российской Федерации.</p>
	<p class="punkt">4.2 Исполнитель освобождается от ответственности за неисполнение или ненадлежащее исполнение услуги в случае непреодолимой
	силы, при возникновении осложнений, по независящим от Исполнителя причинам.</p>
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">5. Заключительные положения</div>
	<p class="punkt">5.1 Договор вступает в силу со дня подписания его сторонами и действует до окончания исполнения обязательств.</p>
	<p class="punkt">5.2 Претезнии и споры, возникшие между Исполнителем и Заказчиком, разрешаются по соглашению сторон или в судебном порядке
	в соответствии с законодательством Российской Федерации.</p>
	<p class="punkt">5.3 Настоящий договор составлен в двух экземплярах, имеющих одинаковую силу, по одному экземпляру для каждой из сторон.</p>
	
	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">6. Адреса и реквизиты сторон</div>
	
	<div style="width:100%">
	<span style="width: 48%; display:block; margin-right:5px; float:left;">
		<p style="margin:5px 0;"><b>Исполнитель:</b> {Lpu_Name}</p>
		<p style="margin:5px 0;"><b>л/с:</b> 612.01.022.0 в Министерстве иуправления финансами Самарской области</p>
		<p style="margin:5px 0;"><b>ИНН:</b> 6315801407&nbsp;<b>КПП:</b> 631901001</p>
		<p style="margin:5px 0;"><b>Банк:</b> ГРКЦ ГУ Банка России по Самарской области г. Самара</p>
		<p style="margin:5px 0;"><b>БИК:</b> 043601001&nbsp;<b>ОГРН:</b> 1036300897665</p>
		<p style="margin:5px 0;"><b>Р/сч:</b> 40601810036013000002</p>
		<p style="margin:5px 0;"><b>Адрес:</b> 443063, г. Самара, ул. XXII Партсъезда, 43</p>
		<p style="margin:5px 0;"><b>Телефон:</b> 951-75-81</p>
		<p style="margin:5px 0;">Начальник госпиталя:</p>
		<p>___________________________________/О.Г. Яковлев/</p>
	</span>	
	
	<span style="width: 48%; margin-left:5px; float: right">
		<p style="margin:5px 0;"><b>Заказчик:</b> {Person_Fio}</p>
		<p style="margin:5px 0;"><b>Документ:</b> {DocumentType_Name}, {Document_Ser} № {Document_Num}</p>
		<p style="margin:5px 0;"><b>Адрес регистрации:</b> {UAddress_Name}</p>
	</span>	
	
	</div>
	</div>
	</div>
	
</body>
</html>
