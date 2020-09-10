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

<div style='width:180mm; font-size: .9em'>
    <table style="width:100%">
        <tr>
            <td style="width: 50%; margin-left:1em">&nbsp;</td>

            <td style="width: 50%;">
            <div style='text-align:right; font-size:.7em;'>
				Приложение 2 к приказу ГБУЗ СОКГВВ № 743 от 09.09.2013<br>
				(согласно  приказу МЗ РФ от 20.12.2012 № 1177н)
            </div>
            </td>
        </tr>
       
    </table>

    <div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">
        <div>Информированное добровольное согласие</div>
        <div>на виды  медицинских вмешательств, включенные в Перечень определенных видов </div>
        <div>медицинских вмешательств, на которые граждане дают информированное добровольное</div>
        <div>согласие при выборе врача и медицинской организации для получения первичной</div>
        <div>медико-санитарной помощи.</div>
    </div>

    <div style="font-size:1em; text-align: justify;">
	
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">Я, </td>
                <td class="value">{Person_Fio}</td>
            </tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">фамилия, имя, отчество пациента</td>
            </tr>
        </tbody>
    </table>	
	
	<table class="t c2">
        <tbody>
            <tr>
                <td class="title">&nbsp;</td>
                <td class="value">{Person_Birthday}</td>
                <td class="title">года рождения,</td>
                <td style="display:none;">&nbsp;</td>
            </tr>
        </tbody>
    </table>
	
	зарегистрированный по адресу:
	<table class="t c1">
        <tbody>
			<tr>               
                <td class="title">&nbsp;</td>
                <td class="value">{UAddress_Name}</td>
			</tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
				(адрес места жительства гражданина, либо законного представителя)</td>
            </tr>
        </tbody>
    </table>
	
	даю информированное добровольное согласие на виды медицинских вмешательств, включенные в Перечень определенных 
	видов медицинских вмешательств, на которые граждане дают информационное добровольное согласие при выборе врача и 
	медицинской организации для получения первичной медико-санитарной помощи, утвержденный приказом 
	Министерства здравоохранения и социального развития РФ от 23 апреля 2012 г. № 390н 
	(зарегистрирован Министерством юстиции РФ 5 мая 2012 г. № 24082) (далее — Перечень), для получения первичной 
	медико-санитарной помощи \ получения  первичной медико-санитарной помощи лицом, законным представителем которого я 
	являюсь (ненужное зачеркнуть)
	
	<table class="t c1">
        <tbody>
			<tr>               
                <td class="title">в</td>
                <td class="value">&nbsp;{Lpu_Name}</td>
			</tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
				(полное наименование медицинской организации)</td>
            </tr>
        </tbody>
    </table>

	<table class="t c1">
        <tbody>
			<tr>               
                <td class="title">Медицинским работником</td>
                <td class="value">&nbsp;{Dolgnost_Name}&nbsp;{PreHospMedPersonal_Fio}</td>
			</tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
				(должность, ФИО медицинского работника)</td>
            </tr>
        </tbody>
    </table>									   
									   
	в доступной для меня форме мне разъяснены цели, методы оказания медицинской помощи, связанный с ними риск, 
	возможны варианты медицинских вмешательств, их последствия, в том числе вероятность  развития осложнений, 
	а также предполагаеые результаты оказания медицинской помощи. Мне разъяснено, что я имею право отказаться 
	от одного или нескольких видов медицинских вмешательств, включенных в Перечень, или потребовать его (их) 
	прекращения, за исключением случаев, предусмотренных частью 9 статьи 20ФЗ от 21 ноября 2011г. № 323-ФЗ 
	«Об основах охраны здоровья граждан в Российской Федерации» (Собрание законодательства РФ, 2011, № 48, 
	ст. 6724;2012, № 26, ст.3442,3446)
	<br />
    Сведения о выбранных мною лицах, которым в соответствии с пунктом 5 части 3 статьи 19Федерального закона 
	от 21 ноября 2011 № 323-ФЗ «Об основах охраны здоровья граждан в Российской Федерации» может быть передана 
	информация о состоянии моего здоровья или состояния лица, законным представителем которого я являюсь 
	(ненужное зачеркнуть):	
	
	<table class="t c1" style="margin-top:1em">
        <tbody>
			<tr>               
                <td class="title" style="font-size:1.05em;">&nbsp;</td>
                <td class="value">{Person_Fio}</td>
			</tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
				(ФИО гражданина, контактный телефон)</td>
            </tr>
        </tbody>
    </table>
	
	
	<table style="margin: 0 0 1em 0; border-spacing: 10px; border-collapse: separate; width:100%">
        <tbody>
			<tr>               
				<td class="value" style="width:30%;">&nbsp;</td>
                <td class="value" style="width:70%;">&nbsp;</td>
			</tr>
			<tr>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				(подпись)</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				(ФИО гражданина или законного представителя гражданина)</td>
            </tr>
        </tbody>
    </table>
                                   
	<table style="margin: 0 0 1em 0; border-spacing: 10px; border-collapse: separate; width:100%">
        <tbody>
			<tr>               
				<td class="value" style="width:30%;">&nbsp;</td>
                <td class="value" style="width:70%;">{PreHospMedPersonal_Fio}&nbsp;</td>
			</tr>
			<tr>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				(подпись)</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				(ФИО медицинского работника)</td>
            </tr>
        </tbody>
    </table>
								   
	
	<table style="margin: 0 0 1em 0;">
        <tbody>
			<tr>               
				<td class="value">{EvnPS_setDate} г.</td>    
			</tr>
			<tr>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top:1px solid #000000">
				(дата оформления)</td>
            </tr>
        </tbody>
    </table>
	</div>
	
	<div style="page-break-after: always;"></div>
	
	<table style="width:100%">
        <tr>
            <td style="width: 50%; margin-left:.8em">&nbsp;</td>

            <td style="width: 50%;">
            <div style='text-align:right; font-size:.7em;'>
				Приложение 3 к приказу ГБУЗ СОКГВВ № 743 от 09.09.2013
            </div>
            </td>
        </tr>       
    </table> 

	<div style="text-align: center; font-weight: bold; margin:.8em 0 .8em 0;">
        <div>Информированное добровольное согласие  на  медицинское вмешательство</div>
    </div>	

    <div style="font-size:.77em; text-align: justify;">
	
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">Я, </td>
                <td class="value">&nbsp;{Person_Fio}</td>
            </tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
				фамилия,имя,отчество пациента\ законного представителя</td>
            </tr>
        </tbody>
    </table>
    
 	в соответствии с ФЗ от 21.11.2011 № 323-ФЗ «Об основах охраны здоровья граждан в Российской Федерации» (далее — Основы)  информирован о правах пациента и даю  добровольное информированное согласие на медицинские вмешательства.
   	Согласно ст 20 Основ согласно моей воле, в доступной  для меня форме  проинформирован(а) в стационаре лечащем врачом о состоянии моего здоровья о наличии, характере, степени тяжести и  о возможных осложнениях моего заболевания, а именно, о нижеследующем:    
    
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">1.1.О диагнозе</td>
                <td class="value">&nbsp;</td>
            </tr>
			<tr>                
                <td  colspan="2" style="font-size:.7em; vertical-align:top;text-align:center;">
					установленный и\или предварительный диагноз в соответствии с МКБ10</td>
            </tr>
        </tbody>
    </table>

1.2.О методах диагностики: анализ крови общий и биохимический, кал на яйца глист, бактериологические исследования кала, мочи, кровь на RW, кровь на наличие вируса имунодефицита человека, маркеры вирусных гепатитов, анализа мочи общий, электрокардиография, проведения рентгенологических исследований: рентгенография, рентгеноскопия, компьютерная томография, магнитно-резонансная томография, ультрозвуковые исследования, допплерографические исследования., ультразвуковых и эндоскопических методов исследований;
	<span style="width:100%; border-bottom: 1px solid #000000;display:block;">&nbsp;</span>
1.3. О видах и методах лечения:
<br />
- Консервативное: прием таблетированных препаратов, иньекций, внутривенных вливаний, лечебных пункций,	
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title"> физиотерапевтических процедур;</td>
                <td class="value">&nbsp;</td>
            </tr>
        </tbody>
    </table>
	<table class="t c1">
        <tbody>
            <tr>
                <td class="title">- Оперативное:</td>
                <td class="value">&nbsp;</td>
            </tr>
        </tbody>
    </table>
1.4. О необходимости, целесообразности и наличии показаний к проведению указанных в п.1.2.  методах диагностики применению видов и методов лечения, указанных в п.1.3.
<br />
1.5. О целях, характере, продолжительности и возможных неблагоприятных эффектах предполагаемых к применению вариантов медицинского вмешательства.
<br />
1.6. О моих действиях до, во время и после проведения того или иного вида медицинского вмешательства на разных этапах лечения, о действии применяемых медикаментов и ожидаемых результатов от их применения.
<br />
1.7. О распорядке и правилах лечебно-охранительного режима.
<br />
2. Получив полные и всесторонние разъяснения, включая исчерпывающие ответы на заданные мною вопросы, подтверждая, что мне понятны суть заболевания и опасности, связанные с его дальнейшем развитием:
<br />
2.1.Добровольно в соответствии со ст 20 Закона«Об основах охраны здоровья граждан в Российской Федерации» даю свое согласие на применение методов диагностики и видов лечения, перечисленных в п.1.2., 1.3. настоящего согласия.
<br />
2.2. Мне разъяснено, что в случаях, когда мое состояние не позволит выразить мою волю, а необходимость проведения лечения будет неотложна, вопрос о медицинском вмешательстве, о его виде и тактике проведения, в т.ч. о дополнительном вмешательстве, в моих интересах решает консилиум, а при невозможности собрать консилиум - лечащий (дежурный )врач с последующем уведомлением должностных лиц ГБУЗ СОКГВВ.
<br />
2.3. Я осознаю, необходимость и значимость соблюдения распорядка и правил лечебно-охранительного режима, рекомендаций лечащего врача, режима приема препаратов, недопустимость самовольного использования медицинского инструментария и оборудования, самолечения и тем самым не буду возлагать ответственность на ГБУЗ СОКГВВ за последствия, которые могут возникнуть при несоблюдении мной данных требований.
<br />
2.4. Я подтверждаю, что мне пояснен смысл всех терминов, на меня не оказывается давление при применении решения о согласии на применение методов диагностики, видов и методов лечения, перечисленных в п.п. 1.2.,1.3. настоящего согласия.
<br />
2.5. Мне объяснено, что некоторые особенности негативной реакции организма на ход лечения нельзя предусмотреть заранее, но они не связаны с качеством проводимого лечения и не могут являться основанием к предъявлению с моей стороны претензий к лечащему врачу и лечебному учреждению
<br />
2.6. Я сообщил (а) точные и правдивые сведения о своем физическом и душевном здоровье, о своей  индивидуальной непереносимости лекарственных препаратов, обо всех имевших место аллергических или необычных реакциях на пищу, укусы насекомых, пыль, обо всех перенесенных мною и известных мне травмах, операциях, заболеваниях, реакциях кожи, кровотечениях и других состояниях, касающихся моего здоровья, об экологических и производственных факторах физической, химической или биологической природы, воздействовавших на меня, о принимаемых лекарственных средствах, наследственности, употреблении алкоголя, наркотических и токсических веществ
<br /> 
2.7. <span style="font-weight:bold;">Я разрешаю предоставлять информацию о состоянии моего здоровья</span> (состоянии  здоровья представляемого мной пациента) следующим членам моей семьи (иным лицам) 
<span style="width:100%; border-bottom: 1px solid #000000; display:block;">&nbsp;</span>

	<table style="margin: 0 0 0 0; border-spacing: 10px; border-collapse: separate; width:100%; font-size:inherit;">
        <tbody>
			<tr>               
				<td>Пациент</td>
				<td class="value" style="width:20%;">&nbsp;</td>
				<td class="value" style="width:60%;">&nbsp;{Person_Fio}</td>
                <td class="value" style="width:20%;">&nbsp;{EvnPS_setDate} г.</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				подпись</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				фамилия, имя, отчество</td>
				<td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				дата</td>
            </tr>
        </tbody>
    </table>
	
Я свидетельствую, что предварительно информировал пациента о сути, ходе выполнения, риске проведения лечения и в доступной форме, дал ответы на все вопросы.

	<table class="t c1">
        <tbody>
            <tr>
                <td class="title"> Наименование структурного подразделения</td>
                <td class="value">&nbsp;</td>
            </tr>
        </tbody>
    </table>

	<table style="margin: 0 0 0 0; border-spacing: 10px; border-collapse: separate; width:100%; font-size:inherit;">
        <tbody>
			<tr>               
				<td>Врач</td>
				<td class="value" style="width:20%;">&nbsp;</td>
				<td class="value" style="width:60%;">&nbsp;</td>
                <td class="value" style="width:20%;">&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				подпись</td>
                <td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				фамилия, имя, отчество</td>
				<td style="font-size:.7em; vertical-align:top;text-align:left;border-top: 1px solid #000000;">
				дата</td>
            </tr>
        </tbody>
    </table>
	
	</div>	
	</div>
</body>
</html>
