<html>
<head>
    <title>УВЕДОМЛЕНИЕ</title>
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

		p.punkt 
		{
			text-indent: 2em;
			margin: 0;
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
	<div style="text-align: right; font-weight: bold; margin: 1em;">Приложение №7 к приказу<br />от &quot;27&quot; декабря 2012 года № 964</div>

    <div style="text-align: center; font-weight: bold; margin: 1em;">
		УВЕДОМЛЕНИЕ
    </div>
	
    <div style="text-align: justify; font-size: 1.1em; margin-top:10px;margin-bottom: 20px;">
	<p class="punkt">Во исполнение требований п. 15 Правил предоставления медицинскими организациями платных медицинских услуг,
	утвержденных постановлением Правительства РФ от 04.10.2012 № 1006, уведомляем Вас о том, что несоблюдение указаний (рекомендаций) медицинского работника
	государственного бюджетного учреждения здравоохранения &quot;Самарский областной клинический госпиталь для ветеранов войн&quot;, предоставляющего платную
	медицинскую услугу, в том числе назначенного режима лечения, могут снизить качество ьпредоставляемой платной  медицинской услуги, повлечь за собой
	невозможность её завершения в срок или отрицательно сказаться на состоянии Вашего здоровья.</p>
	</div>
	
	<div>
		<span style="float: left; width: 50%; display: block; ">
            <p>&quot;____&quot;_____________ 2014 года</p>
			<p style="font-size: .75em; padding-top:8px;">_____________________<span style=" float: right; width:200px; display:block;">/{Person_Fio}/</span></p>            
        </span>
		<span style="float: right;">
            <p>Начальник госпиталя</p>
            <p>________________________/О.Г. Яковлев/</p>
        </span>
    </div>

    
</div>    
</body>
</html>
