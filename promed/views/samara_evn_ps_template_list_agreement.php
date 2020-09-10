<html>
<head>
    <title>РАСПИСКА</title>
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

	
	<div style="width: 100%; height: 100mm; display: block;">&nbsp;</div>
	
	<div style="text-align: center; font-weight: bold; margin: 1em;">
		РАСПИСКА
    </div>

    <div style="text-align: justify; font-size: 1.1em; margin-top:10px;margin-bottom: 20px;">	
	<p class="punkt">Я, находясь на стационарном лечении в ГБУЗ СОКГВВ обязуюсь соблюдать лечебно-охранительный режим, в том числе
	не употреблять спиртные напиткии не мешать отдыху других пациентов, не покидать территорию госпиталя.</p>
	<p class="punkt">В случае нарушения мной режима осведомлен, что могу быть выписан досрочно с отметкой он нарушении режима в больничном листе
	и сообщением в ЛПУ, военкомат.</p>
	<p class="punkt">Администрация не несет ответственности за несданные денежные средства и сотовые телефоны.</p>
	<p class="punkt">Документы сдавать старшей медсестре.</p>
	<p class="punkt">Беседа со мной проведена в присутствии лечащего врача.</p>
	
	<table class="t c1" style="margin-top: 10px;">
		<tr>
			<td class="title">&nbsp;</td>
			<td class="value" style="text-align: center;">{Person_Fio}</td>
		</tr>	
	</table>
	
	<div>
		<span style="width: 35%; float: left;">
			<p style="text-align: center;">Дата {EvnPS_setDate}</p>
		</span>			
		<span style="float: right; width:60%">
			<p style="text-align:right;">Подпись пациента _________________________</p>
		</span>
	</div>
    

	<div style="text-align: center; font-weight: bold; margin: 1em;text-transform: uppercase;">
		Самарский областной клинический госпиталь для ветеранов войн - зона свободная от курения.
    </div>    
	
	<p class="punkt">С 1 июня 2013 года согласно ФЗ №15 от 23.02.2013 &quot;Об охране здоровья граждан от воздействия окружающего табачного дыма и последствий потребления табака&quot;
	курение в ГБУЗ СОКГВВ запрещено. Курить на территории госпиталя можно только в специально отведенном месте: <b>на лестничной площадке в подчердачном помещении и
	вне здания на площадке рядом с мусорным контейнером.</b></p>
	<p class="punkt">Подробную информацию Вы можете получить у своего лечащего врача.</p>
	
	<div>
		<span style="width: 35%; float: left;">
			<p style="text-align: center;">Дата {EvnPS_setDate}</p>
		</span>			
		<span style="float: right; width:60%">
			<p style="text-align:right;">Подпись _________________________</p>
		</span>
	</div>
	</div>
</div>    
</body>
</html>
