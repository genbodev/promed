<html>
<head>
    <title>добровольное согласие на виды  медицинских вмешательств</title>
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

<div style="font-size:.8em; text-align: justify;">
<table style="font-size: 1em; text-align: justify;">
<tr>
<td style="white-space: nowrap; vertical-align:top; padding: 0 .5em 0 0;">Наименование учреждения</td> <td style="font-weight:bold;">{Lpu_Name}</td>
</tr>
<tr>
<td style="white-space: nowrap; vertical-align:top; padding: 0 .5em 0 0;">Адрес:</td><td style="font-weight:bold;">{LpuAddress}</td>
</tr>
<tr>
<td style="white-space: nowrap; vertical-align:top; padding: 0 .5em 0 0;">От гражданина</td><td style="font-weight:bold;">{Person_Fio}</td>
</tr>
<tr>
<td style="white-space: nowrap; vertical-align:top; padding: 0 .5em 0 0;">зарегистрированного по адресу</td><td style="font-weight:bold;">{UAddress_Name}</td>
</tr>
<tr>
<td colspan="2" style="white-space: nowrap; vertical-align:top; padding: 0 .5em 0 0;">{DocumentType_Name} серия: {Document_Ser} номер: {Document_Num} выдан: {Document_begDate} {OrgDep_Name}</td>
</tr>
</table>
<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">
    <div>СОГЛАСИЕ НА  ОБРАБОТКУ  ПЕРСОНАЛЬНЫХ ДАННЫХ</div>
</div>

Настоящим заявлением я, {Person_Fio} своей волей и своем интересе даю согласие на обработку моих персональных данных СОКГВВ в соответствии с требованием статьи 9 Федерального закона от 27.07.2006 г. №152 ФЗ «О персональных данных», либо иному лицу, которому могут перейти права и обязанности СОКГВВ в результате универсального правопреемства.

<div style="font-weight:bold">Цель обработки персональных данных: </div>
- обеспечение прав ветеранов войн и лиц, приравненных к ним по льготам, в соответствии с Федеральным Законом «О ветеранах», а так же иных граждан, за счет средств обязательного медицинского страхования в соответствии с лицензией на медицинскую деятельность №ФС-63-01-001012 от 22.12.2008г., на стационарную и амбулаторную медицинскую помощь. 

<div style="font-weight:bold">Перечень персональных данных, на обработку которых дано настоящее согласие:</div>
- Ф.И.О., дата рождения, адрес, пол, паспортные данные, телефон, реквизиты полиса ОМС (ДМС),  страховой номер индивидуального расчетного счета в Пенсионном фонде РФ (СНИЛС), данные о состоянии моего здоровья,  случаях обращения за медицинской помощью в госпиталь, данные о льготах, сроки моего лечения в госпитале и другие сведения, полученные при оказании медицинских услуг.

<div style="font-weight:bold">Перечень действий с персональными данными, на совершение которых дается согласие:</div>
- сбор, систематизация, накопление, хранение, внесения их в электронную базу данных, включение в списки (реестры) и отчетные формы, уточнение, использование, распространение, обезличивание, блокирование, уничтожение персональных данных в соответствии с регламентом, установленным известными законами РФ.

<div style="font-weight:bold">Способы обработки персональных данных:</div>
- на бумажных носителях, в информационных системах персональных данных с использованием и без использования средства информатизации с соблюдением мер, обеспечивающих их защиту от несанкционированного доступа, конфиденциальности и безопасности, согласно действующего законодательства РФ, а также смешанным способом;  при участии и при непосредственном участии человека, отвечающего в установленном порядке за защиту персональных данных;
<br />
<span style="font-weight:bold">Срок хранения персональных данных:</span>
срок хранения моих персональных данных соответствует сроку хранения первичных медицинских документов и составляет двадцать пять лет (для стационара, пять лет – для поликлиники)
<br />
<span style="font-weight:bold">Срок, в течении которого действует согласие:</span>&nbsp;&nbsp;&nbsp;- бессрочно;

Настоящим я также выражаю свое согласие на обмен (прием и передачу) моих персональных данных со следующими организациями (третьими юридическими лицами – МЗ РФ и Самарской области, ТФОМС Самарской области, ООО «Медицинский информационный аналитический центр» (МИАЦ), ООО «Информационно-медицинский центр» (ИМЦ), страховыми медицинскими организациями (СМО), окружными и муниципальными органами управления здравоохранения Самарской области, лечебно-профилактическими учреждениями Самарской области (ЛПУ), Управление федеральной службы по надзору в сфере здравоохранения по Самарской области, ФГУЗ «Центр гигиены и эпидемиологии» в Самарской области, учреждениями медико-социальной экспертизы и другими контролирующим органами с использованием машинных носителей или по каналам связи с соблюдением мер, обеспечивающих их защиту от несанкционированного доступа при условии, что прием и обработка будут осуществляться с соблюдением всех требований по обеспечению конфиденциальности и безопасности персональных данных в соответствии с действующим законодательством.
<br />
<br />
Я оставляю за собой право отозвать свое согласие посредством составления соответствующего письменного документа, который будет вручен лично под расписку представителю Оператора.
При получении моего письменного заявления об отзыве настоящего согласия на обработку персональных данных, Оператор обязан прекратить их обработку в течение периода времени, необходимого для завершения взаиморасчетов между госпиталем, СМО, ЛПУ по оплате за оказанную мне в госпитале медицинскую помощь 

<div style="margin: 1em 0 0 0;">
Дата <u style="font-size:1.3em;"><?php echo date("d.m.Y"); ?> </u> г.
</div>

<table style="margin: 0 0 0 0; border-spacing: 10px; border-collapse: separate; width:100%; font-size:inherit;">
    <tbody>
		<tr>               
			<td class="value" style="width:20%;">&nbsp;</td>
			<td class="value" style="width:60%;">&nbsp;{Person_Fio}</td>
            <td class="value" style="width:20%;">&nbsp;</td>
		</tr>
		<tr>
            <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
			подпись</td>
            <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
			фамилия, имя, отчество</td>
			<td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
			тел. контакта</td>
        </tr>
    </tbody>
</table>
	
</div>	
</div>
</body>
</html>
