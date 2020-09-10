<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size:10pt;}    
p {margin:0 0 1px; line-height: 11px;}
table {font-size:12pt; vertical-align: top;}
table td {font-size:10pt;}
.lefttd {align: left; width: 400px; font-weight: bold; vertical-align: top;}
.leftminitd {align: left; width: 400px; vertical-align: top;}
.righttd {align: left; border-bottom: #aaaaaa 1px solid; vertical-align: top;}
.linetd {background-color: #000; height: 2px;}
.tit {font-family:Arial; font-weight:bold; font-size:12pt; text-align:center}
.podval {font-size:8pt}
.u {text-decoration: underline;}
.v_ok:after {content: "V"; font-family: Verdana; font-size: 14px; font-weight: bold; border: 1px solid #000; height: 12px; display: inline-block; line-height: 12px;}
.v_no {border: 1px solid #000; width:11px; height: 12px;}
.head110 {font-size:14px; vertical-align: top;}
.head110 big {font-size:12px; line-height:14px;}
table.time {border-collapse:collapse; border:1px solid #000}
    table.time td {border:1px solid #000; text-align: center; font-size: 13px;}
.wrapper110 {display: inline-block;}
.innerwrapper {display:inline-block}
.innerwrapper .v_ok, .innerwrapper .v_no {display:inline-block; margin: 0 15px 0 0;}
.innerwrapper u {margin: 0 15px 0 0}
</style>

<title>КАРТА ВЫЗОВА СМП №{CardNum} {ServiceDT}</title>

</head>
<body>

<div align="center">
    <table width="711" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td width="237">
                    <p align="center">
                        Министерство здравоохранения Республики Казахстан
                    </p>
                </td>
                <td rowspan="2" width="237">
                </td>
                <td width="237">
                    <p align="center">
                        Қазақстан Республикасы Денсаулық сақтау министрінің м.а. 2010 жылғы «23» қарашадағы № 907 бұйрығымен бекітілген
                    </p>
                    <p align="center">
                        № 110/е- нысанды медициналық құжаттама
                    </p>
                </td>
            </tr>
            <tr>
                <td width="237">
                    <p align="center">
                        Ұйымның атауы
                    </p>
                    <p align="center">
                        Наименование организации
                    </p>
                    <p align="center">
                        <strong>{Lpu_name}</strong>
                    </p>
                </td>
                <td width="237">
                    <p align="center">
                        Медицинская документация Форма № 110/у Утверждена приказом и.о. Министра здравоохранения Республики Казахстан
                    </p>
                    <p align="center">
                        «23 » ноября 2010 года №907___
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<p align="center">
    <strong>Карта вызова бригады скорой и неотложной медицинской помощи № <u>{CardNum}</u></strong>
</p>
<p align="center">
    <strong>Жедел және шұғыл медициналық жәрдем бригадасын шақырту картасы № <u>{CardNum}</u></strong>
</p>
<p align="center">
    <strong> </strong>
</p>
<div align="center">
    <table width="718" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td width="129">
                    <p align="center">
                        Шақыртуды
                    </p>
                    <p align="center">
                        қабылдау
                    </p>
                    <p align="center">
                        Время приема вызова
                    </p>
                </td>
                <td width="103">
                    <p align="center">
                        Бригадаға берілу
                    </p>
                    <p align="center">
                        Время передачи бригаде
                    </p>
                </td>
                <td width="75">
                    <p align="center">
                        Шығу Время выезда
                    </p>
                </td>
                <td width="103">
                    <p align="center">
                        Науқасқа келу Время приезда к больному
                    </p>
                </td>
                <td width="103">
                    <p align="center">
                        Қызымет көрсетуді аятауы Время окончания обслуживания
                    </p>
                </td>
                <td width="81">
                    <p align="center">
                        Қайтып келу Время возвращения
                    </p>
                </td>
                <td width="125">
                    <p align="center">
                        Келесі шақыртуды қабылдау
                    </p>
                    <p align="center">
                        Время приема следующего вызова
                    </p>
                </td>
            </tr>
            <tr>
                <td width="129">
					{AcceptTime}
                </td>
                <td width="103">
					{TransTime}
                </td>
                <td width="75">
					{GoTime}
                </td>
                <td width="103">
					{ArriveTime}
                </td>
                <td width="103">
					{ToHospitalTime}
                </td>
                <td width="81">
					{EndTime}
                </td>
                <td width="125">
					{NextTime}
                </td>
            </tr>
        </tbody>
    </table>
</div>
<p align="center">
    <strong> </strong>
</p>
<div align="center">
    <table width="718" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td width="274">
					<table><tr><td>
                    <p>
                        Тегі
                    </p>
                    <p>
                        Фамилия
                    </p>
					</td><td><b>{Fam}</b></td></table>
                </td>
                <td width="123">
					
                    <p align="center">
                        Жынысы Пол
                    </p>
                    <p align="center">
                        <?php if ($Sex_id == 1) { ?><u>Е м</u><?php } else {?>Е м<?php }?>
						<?php if ($Sex_id == 2) { ?><u>ә ж</u><?php } else {?>ә ж<?php }?>
                    </p>
					
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        Қызмет көрсету уақыты
                    </p>
                    <p>
                        Дата обслуживания
                    </p>
					</td><td><b>{ServiceDT}</b></td></table>
                </td>
            </tr>
            <tr>
                <td width="274">
					<table><tr><td>
                    <p>
                        Аты
                    </p>
                    <p>
                        Имя
                    </p>
					</td><td><b>{Name}</b></td></table>
                </td>
                <td width="123">
                    <p>
                        Қайта шақ Повтор
                    </p>
                    <p>
						<?php echo ($CallType_id == 4)?'<u>иә да</u> жоқ нет':'иә да <u>жоқ нет</u>'; ?>
                    </p>					
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        № шығу бригадасының құрамы
                    </p>
                    <p>
                        Состав выездной бригады №
                    </p>
					</td><td><b>{EmergencyTeamNum}</b></td></table>
                </td>
            </tr>
            <tr>
                <td width="274">
					<table><tr><td>
                    <p>
                        Әкесінің аты
                    </p>
                    <p>
                        Отчество
                    </p>
					</td><td><b>{Middle}</b></td></table>
                </td>
                <td rowspan="2" width="123">
                    <p>
                        Нәтиже Результат
                    </p>
					<?php echo ($ResultV_id[196]['flag'] == '1')?'<p class="u">Оставлен на месте</p><p>Госпитализирован</p>':''?>
					<?php echo ($ResultV_id[197]['flag'] == '1')?'<p>Оставлен на месте</p><p class="u">Госпитализирован</p>':''?>
					<?php echo ($ResultV_id[196]['flag'] != 1 && $ResultV_id[197]['flag'] != 1)?'<p>Оставлен на месте</p><p>Госпитализирован</p>':''?>
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        Дәрігер
                    </p>
                    <p>
                        Врач
                    </p>
					</td><td><b>{Doctor}</b></td></table>
                </td>
            </tr>
            <tr>
                <td width="274">
					<table><tr><td>
                    <p>
                        Жасы
                    </p>
                    <p>
                        Возраст
                    </p>
					</td><td><b>
							<?php
							if ($Birthday == '') {
								$Birthday = "01.01.".(date("Y")-$Age);
							}
                            echo($Birthday);

							$timeZone = new DateTimeZone ( 'Europe/Moscow' ); // временная зона
							$datetime1 = new DateTime ( $Birthday, $timeZone ); // д.р.
							$datetime2 = new DateTime (); // текущая дата
							$interval = $datetime1->diff ( $datetime2 ); // собственно вычисление

							if ($interval->y > 3) echo " (".$interval->format ( '%y лет' ).")";
							elseif ($interval->y > 0 && $interval->y <= 3) echo " (".$interval->format ( '%y лет %m месяцев' ).")";
							elseif ($interval->y == 0 && $interval->m == 0) echo " (".$interval->format ( '%d дней' ).")";
							else echo " (".$interval->format ( '%m месяцев %d дней' ).")";
							
							?>
					</b></td></table>
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        Фельдшер
                    </p>
                    <p>
                        Фелдшер
                    </p>
					</td><td><b>{Feldsher}</b></td></table>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="397">
					<table><tr><td>
                    <p>
                        Мекен жайы
                    </p>
                    <p>
                        Адрес
                    </p>
					</td><td><b>{Adress}</b></td></table>
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        Жүргізуші
                    </p>
                    <p>
                        Водитель
                    </p>
					</td><td><b>{Driver}</b></td></table>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="397">
					<table><tr><td>
                    <p>
                        Жұмыс орны
                    </p>
                    <p>
                        Место работы
                    </p>
					</td><td><b>{Work}</b></td></table>
                </td>
                <td colspan="4" width="321">
					<table><tr><td>
                    <p>
                        Шақырту себебі
                    </p>
                    <p>
                        Повод к вызову
                    </p>
					</td><td><b>{Reason}</b></td></table>
                </td>
            </tr>
            <tr>
                <td rowspan="4" width="274">
                    <p>
                        Қаралу, көмек көрсету, ауруханаға жатудан бас тарту
                    </p>
                    <p>
                        Отказ от осмотра, оказания помощи, госпитализации
                    </p>
                    <div>
                        <p>
                            Мен________________________________________
                        </p>
                        <p>
                            қаралу, көмек көрсету, ауруханаға жатудан бас тартамын. Жедел жәрдем бригадасы қызметкерлерімен мүмкін болар асқыну мен бас
                            тартудың салдары туралы ескертілдім.
                        </p>
                    </div>
                    <p>
                        Қолы_____
                    </p>
                    <div>
                        <p>
                            Я_____________________________________________
                        </p>
                        <p>
                            Отказываюсь от осмотра, помощи, госпитализации. Сотрудниками бригады скорой помощи я предупрежден о возможных осложнениях и
                            последствиях своего отказа.
                        </p>
                        <p>
                            Подпись
                        </p>
                    </div>
                </td>
                <td width="123">
                    <p align="center">
                        Жарақат түрі
                    </p>
                    <p align="center">
                        Вид травматизма
                    </p>
                </td>
                <td colspan="4" width="321">
                    <p <?php echo ($Travm_id[193]['flag'] == 1)?'class="u"':'';?> >
                        ЖМЖ бригадасына актив Актив для бригады СМП
                    </p>
                    <p <?php echo ($Travm_id[194]['flag'] == 1)?'class="u"':'';?> >
                        Учаскелік дәрігерге актив Актив для участ. Врача
                    </p>
                </td>
            </tr>
            <tr>
                <td width="123">
                    <p align="center">
                        Алкоголь
                    </p>
                    <p align="center">
						<?php echo ($isAlco == 1)?'Да <u>Нет</u>':'';?>
						<?php echo ($isAlco == 2)?'<u>Да</u> Нет':'';?>
						<?php echo ($isAlco != 1 && $isAlco != 2)?'Да Нет':'';?>                     
                    </p>
                </td>
                <td colspan="4" width="321">
                    <p>
                        Жеткізілді және тапсырылды
                    </p>
                    <p>
                        Доставлен и передан
                    </p>
                </td>
            </tr>
            <tr>
                <td rowspan="2" width="123">
                    <p align="center">
                        Қашықтық (км)
                    </p>
                    <p align="center">
                        Километраж (км)						
                    </p>
					<p align="center">
                        <b>{Kilo}</b>
                    </p>
                </td>
                <td width="86">
                    <p align="center">
                        Мекемеге
                    </p>
                    <p align="center">
                        В учреждение<em></em>
                    </p>
                </td>
                <td width="67">
                    <p align="center">
                        Уақыты
                    </p>
                    <p align="center">
                        время
                    </p>
                </td>
                <td width="86">
                    <p align="center">
                        Ауруханаға
                    </p>
                    <p align="center">
                        жатқызу
                    </p>
                    <p align="center">
                        госпитализация
                    </p>
                </td>
                <td width="83">
                    <p align="center">
                        қолы
                    </p>
                    <p align="center">
                        роспись
                    </p>
                </td>
            </tr>
            <tr>
                <td width="86">
					{Lpu_name}
                </td>
                <td width="67">
					{DeliveryTime}
                </td>
                <td width="86">
					{isHosp}
                </td>
                <td width="83">					
                </td>
            </tr>
        </tbody>
    </table>
</div>
<p align="center">
    <strong>Сараптама бағасы Экспертная оценка</strong>
</p>
<div align="center">
    <table width="718" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td width="232">
                    <p>
                        Сараптама кезеңі Этап экспертизы
                    </p>
                </td>
                <td width="480">
					{ExpEtap}
                </td>
            </tr>
            <tr>
                <td width="232">
                    <p>
                        Аға дәрігер Старший врач
                    </p>
                </td>
               <td width="480">
				   {ExpDoctor}
               </td>
            </tr>
            <tr>
                <td width="232">
                    <p>
                        Бөлімше меңгеруш Зав. отделением
                    </p>
                </td>
                <td width="480">
					{ExpZav}
                </td>
            </tr>
            <tr>
                <td width="232">
                    <p>
                        Бас дәр.орынб. Зам. главного врача
                    </p>
                </td>
                <td width="480">
					{ExpGlav}
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div align="center">
    <table width="718" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
					<p>
						Шағымы Жалобы: 
					</p>					
					<p>
						<u>{Complaints}</u>
					</p>
					<p>
						Ауруы жөніндегі сыртарытқы Анамнез настоящего заболевания: 
					</p>
					<p>
						<u>{Anamnez}</u>
					</p>
					<p>
						Өмір сыртартқысы Анамнез жизни:
					</p>
					<p>
						<u>{AnamnezLife}</u>
					</p>
					<p>
						Status Localis:
					</p>
					<p>
						<u>{LocalStatus}</u>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div style="page-break-after: always;"></div>

<div align="center">
    <table width="718" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td valign="top" width="130">
                    <p align="center">
                        <strong>Жалпы жағдайы</strong>
                    </p>
                    <p align="center">
                        <strong>Общее состояние</strong>
                    </p>
                    <p align="center" <?php echo ($Condition_id[2]['flag'] == 1)?'class="u"':'';?>>
                        Қанағат\удовлетворител.
                    </p>
                    <p align="center" <?php echo ($Condition_id[3]['flag'] == 1)?'class="u"':'';?>>
                        Орташа\средней тяжес 
					</p>
					<p align="center" <?php echo ($Condition_id[4]['flag'] == 1)?'class="u"':'';?>>
						нашар тяжелое 
					</p>
					<p align="center" <?php echo ($Condition_id[5]['flag'] == 1)?'class="u"':'';?>>
						агониялық агональное
                    </p>
                    <p align="center" <?php echo ($Condition_id[6]['flag'] == 1)?'class="u"':'';?>>
                        биологиялық өлім биологическая смерть
                    </p>
					
                    <p align="center">
                        <strong>Санасы</strong>
                    </p>
                    <p align="center">
                        <strong>Сознание</strong>
                    </p>
                    <p align="center" <?php echo ($Cons_id[8]['flag'] == 1)?'class="u"':'';?>>
                        Айқын ясное
                    </p>
                    <p align="center">
                        <span <?php echo ($Cons_id[9]['flag'] == 1 || $Cons_id[10]['flag'] == 1)?'class="u"':'';?>>айқын емес</span> <span <?php echo ($Cons_id[9]['flag'] == 1)?'class="u"':'';?>>1</span>,<span <?php echo ($Cons_id[10]['flag'] == 1)?'class="u"':'';?>>2</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Cons_id[9]['flag'] == 1 || $Cons_id[10]['flag'] == 1)?'class="u"':'';?>>оглушенность</span> <span <?php echo ($Cons_id[9]['flag'] == 1)?'class="u"':'';?>>1</span>,<span <?php echo ($Cons_id[10]['flag'] == 1)?'class="u"':'';?>>2</span>
                    </p>
                    <p align="center" <?php echo ($Cons_id[12]['flag'] == 1 || $Cons_id[13]['flag'] == 1 || $Cons_id[14]['flag'] == 1)?'class="u"':'';?>>
                        сопор
                    </p>
                    <p align="center">
                        <span <?php echo ($Cons_id[12]['flag'] == 1 || $Cons_id[13]['flag'] == 1 || $Cons_id[14]['flag'] == 1)?'class="u"':'';?>>кома</span> <span <?php echo ($Cons_id[12]['flag'] == 1)?'class="u"':'';?>>1</span>,<span <?php echo ($Cons_id[13]['flag'] == 1)?'class="u"':'';?>>2</span>,<span <?php echo ($Cons_id[14]['flag'] == 1)?'class="u"':'';?>>3</span>
                    </p>
                    <p align="center" <?php echo ($Cons_id[11]['flag'] == 1)?'class="u"':'';?>>
                        ессіз отсутствует
                    </p>
                    <p align="center">
                        <strong>Көңіл күйі</strong>
                    </p>
                    <p align="center">
                        <strong>Поведение</strong>
                    </p>
                    <p align="center" <?php echo ($Behavior_id[16]['flag'] == 1)?'class="u"':'';?>>
                        Сабырлы спокоен
                    </p>
                    <p align="center" <?php echo ($Behavior_id[17]['flag'] == 1)?'class="u"':'';?>>
                        қозған возбужден
                    </p>
                    <p align="center">
                        <span <?php echo ($Behavior_id[18]['flag'] == 1)?'class="u"':'';?>>әлсіз вял</span>, <span <?php echo ($Behavior_id[19]['flag'] == 1)?'class="u"':'';?>>тежелген заторможен</span>
                    </p>
                    <p align="center">
                        <strong>Көз қарашығы</strong>
                    </p>
                    <p align="center">
                        <strong>Зрачки</strong>
                    </p>
                    <p align="center" <?php echo ($Pupil_id[21]['flag'] == 1)?'class="u"':'';?>>
                        Қалыпты нормальное
                    </p>
                    <p align="center">
                        <span <?php echo ($Pupil_id[22]['flag'] == 1)?'class="u"':'';?>>миоз</span>-<span <?php echo ($Pupil_id[23]['flag'] == 1)?'class="u"':'';?>>мидриаз</span>
                    </p>
                    <p align="center">
                        жарыққа әсері:
                    </p>
                    <p align="center">
                        реакция на свет
                    </p>
                    <p align="center" <?php echo ($Light_id[25]['flag'] == 1)?'class="u"':'';?>>
                        жанды живая
                    </p>
                    <p align="center" <?php echo ($Light_id[26]['flag'] == 1)?'class="u"':'';?>>
                        әлсіз ослабленная
                    </p>
                    <p align="center" <?php echo ($Light_id[27]['flag'] == 1)?'class="u"':'';?>>
                        жоқ отсутствует
                    </p>
                    <p align="center">
                        <span <?php echo ($Aniz_id[29]['flag'] == 1 || $Aniz_id[30]['flag'] == 1)?'class="u"':'';?>>анизокария</span>   
						<span <?php echo ($Aniz_id[29]['flag'] == 1)?'class="u"':'';?>>D</span>  <span <?php echo ($Aniz_id[30]['flag'] == 1)?'class="u"':'';?>>S</span>
                    </p>
                    <p align="center">
                        <strong>Тері қабаты</strong>
                    </p>
                    <p align="center">
                        <strong>Кожные покровы</strong>
                    </p>
                    <p align="center">
                        физиолог. Түсті
                    </p>
                    <p align="center" <?php echo ($Kozha_id[32]['flag'] == 1)?'class="u"':'';?>>
                        бозғылт бледные
                    </p>
                    <p align="center" <?php echo ($Kozha_id[33]['flag'] == 1)?'class="u"':'';?>>
                        бозғылт желтушные
                    </p>
                    <p align="center" <?php echo ($Kozha_id[34]['flag'] == 1)?'class="u"':'';?>>
                        цианоз (акроцианоз)
                    </p>
                    <p align="center">
                        қызғылт гиперемия ылғалды құрғақ <span <?php echo ($Kozha_id[36]['flag'] == 1)?'class="u"':'';?>>сухие</span>-<span <?php echo ($Kozha_id[37]['flag'] == 1)?'class="u"':'';?>>влажные</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Kozha_id[38]['flag'] == 1)?'class="u"':'';?>>таза</span>-<span <?php echo ($Kozha_id[39]['flag'] == 1)?'class="u"':'';?>>бөртпе</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Kozha_id[38]['flag'] == 1)?'class="u"':'';?>>чистые</span>-<span <?php echo ($Kozha_id[39]['flag'] == 1)?'class="u"':'';?>>сыпь</span>                        
                    </p>
                    <p align="center" <?php echo ($Kozha_id[40]['flag'] == 1)?'class="u"':'';?>>
                        гипостаза
                    </p>
                    <p align="center">
                        <strong>Жүрек - қан тамыр.жүйесі</strong>
                    </p>
                    <p align="center">
                        <strong>Сер.-сосудист. Система</strong>
                    </p>
                    <p align="center">
                        Жүрек дыбысы: <span <?php echo ($Heart_id[42]['flag'] == 1)?'class="u"':'';?>>айқын</span>,
                    </p>
                    <p align="center">
                        Тоны сердца:<span <?php echo ($Heart_id[42]['flag'] == 1)?'class="u"':'';?>>ясные</span>
                    </p>
                    <p align="center">                       
						<span <?php echo ($Heart_id[43]['flag'] == 1)?'class="u"':'';?>>тұншыққан</span>, <span <?php echo ($Heart_id[44]['flag'] == 1)?'class="u"':'';?>>тұнық</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Heart_id[43]['flag'] == 1)?'class="u"':'';?>>приглушенные</span>, <span <?php echo ($Heart_id[44]['flag'] == 1)?'class="u"':'';?>>глухие</span>
                    </p>
                    <p align="center">
                        Шуылы: <span <?php echo ($Noise_id[46]['flag'] == 1)?'class="u"':'';?>>жоқ</span>, Шумы:<span <?php echo ($Noise_id[46]['flag'] == 1)?'class="u"':'';?>>нет</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Noise_id[47]['flag'] == 1)?'class="u"':'';?>>систолик</span>, <span <?php echo ($Noise_id[48]['flag'] == 1)?'class="u"':'';?>>диастолик</span>.
                    </p>
                    <p align="center">
                        Тамыр соғуы: Пульс
                    </p>
                    <p align="center" <?php echo ($Pulse_id[50]['flag'] == 1)?'class="u"':'';?>>
                        Қанағаттанарлық
                    </p>
                    <p align="center" <?php echo ($Pulse_id[50]['flag'] == 1)?'class="u"':'';?>>
                        Удов. качес
                    </p>
                </td>
                <td valign="top" width="168">
                    <p align="center" <?php echo ($Pulse_id[51]['flag'] == 1)?'class="u"':'';?>>
                        Ырғақты ритмичный
                    </p>
                    <p align="center" <?php echo ($Pulse_id[52]['flag'] == 1)?'class="u"':'';?>>
                        Ырғақсыз аритмичный
                    </p>
                    <p align="center" <?php echo ($Pulse_id[53]['flag'] == 1)?'class="u"':'';?>>
                        Толымды напряжен
                    </p>
                    <p align="center" <?php echo ($Pulse_id[54]['flag'] == 1)?'class="u"':'';?>>
                        Толымсыз слабого наполнения
                    </p>
                    <p align="center">
                        <strong>Тыныс алу жүйесі</strong>
                    </p>
                    <p align="center">
                        <strong>Дыхательная система</strong>
                    </p>
                    <p align="center">
                        Экскурсия груд.кл:
                    </p>
                    <p align="center">
                        қалыпты; <span <?php echo ($Exkurs_id[56]['flag'] == 1)?'class="u"':'';?>>нормаль</span>.
                    </p>
                    <p align="center">
                        <span <?php echo ($Exkurs_id[58]['flag'] == 1 || $Exkurs_id[59]['flag'] == 1)?'class="u"':'';?>>төмендеген </span>
						<span <?php echo ($Exkurs_id[58]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Exkurs_id[59]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Exkurs_id[58]['flag'] == 1 || $Exkurs_id[59]['flag'] == 1)?'class="u"':'';?>>снижена</span>
						<span <?php echo ($Exkurs_id[58]['flag'] == 1)?'class="u"':'';?>>П.</span>
						<span <?php echo ($Exkurs_id[59]['flag'] == 1)?'class="u"':'';?>>Л.</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Exkurs_id[60]['flag'] == 1)?'class="u"':'';?>>дем алуы ауытқулы;</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Exkurs_id[60]['flag'] == 1)?'class="u"':'';?>>патолог. дыхание</span>
                    </p>
                    <p align="center" <?php echo ($Exkurs_id[61]['flag'] == 1)?'class="u"':'';?>>
                        жоқ отсутствует
                    </p>
                    <p align="center">
                        Тынысын тыңдау:
                    </p>
                    <p align="center">
                        Дыхание аускульт:
                    </p>
                    <p align="center" <?php echo ($Hale_id[63]['flag'] == 1)?'class="u"':'';?>>
                        Везикулярлық
                    </p>
                    <p align="center" <?php echo ($Hale_id[63]['flag'] == 1)?'class="u"':'';?>>
                        везикулярное
                    </p>
                    <p align="center" <?php echo ($Hale_id[64]['flag'] == 1)?'class="u"':'';?>>
                        пуэрилдік
                    </p>
                    <p align="center" <?php echo ($Hale_id[64]['flag'] == 1)?'class="u"':'';?>>
                        пуэрильное
                    </p>
                    <p align="center" <?php echo ($Hale_id[65]['flag'] == 1)?'class="u"':'';?>>
                        қатқыл жесткое
                    </p>
                    <p align="center">
                        <span <?php echo ($Hale_id[66]['flag'] == 1)?'class="u"':'';?>>әлсізденген</span>
                        <span <?php echo ($Hale_id[67]['flag'] == 1)?'class="u"':'';?>>О</span>
                        <span <?php echo ($Hale_id[68]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Hale_id[66]['flag'] == 1)?'class="u"':'';?>>ослаблено</span>
                        <span <?php echo ($Hale_id[67]['flag'] == 1)?'class="u"':'';?>>П</span>
                        <span <?php echo ($Hale_id[68]['flag'] == 1)?'class="u"':'';?>>Л</span>                         
                    </p>
                    <p align="center" <?php echo ($Hale_id[69]['flag'] == 1)?'class="u"':'';?>>
                        бронхореялық
                    </p>
                    <p align="center" <?php echo ($Hale_id[69]['flag'] == 1)?'class="u"':'';?>>
                        бронхорея
                    </p>
                    <p align="center">
                        Сырылы: <span <?php echo ($Rattle_id[71]['flag'] == 1)?'class="u"':'';?>>жоқ</span> 
                    </p>
                    <p align="center">
                        Хрипы: <span <?php echo ($Rattle_id[71]['flag'] == 1)?'class="u"':'';?>>нет</span> 
                    </p>
                    <p align="center" <?php echo ($Rattle_id[72]['flag'] == 1)?'class="u"':'';?>>
                        Құрғақ сухие
                    </p>
                    <p align="center" <?php echo ($Rattle_id[73]['flag'] == 1)?'class="u"':'';?>>
                        Ылғалды влажные
                    </p>
                    <p align="center">
                        Демікпесі: <span <?php echo ($Shortwind_id[78]['flag'] == 1)?'class="u"':'';?>>жоқ</span> 
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[76]['flag'] == 1)?'class="u"':'';?>>
                        экспираторлы
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[75]['flag'] == 1)?'class="u"':'';?>>
                        инспираторлы
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[77]['flag'] == 1)?'class="u"':'';?>>
                        аралас
                    </p>
                    <p align="center">
                        Одышка: <span <?php echo ($Shortwind_id[78]['flag'] == 1)?'class="u"':'';?>>нет</span> 
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[76]['flag'] == 1)?'class="u"':'';?>>
                        Экспираторная
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[75]['flag'] == 1)?'class="u"':'';?>>
                        Инспираторная
                    </p>
                    <p align="center" <?php echo ($Shortwind_id[77]['flag'] == 1)?'class="u"':'';?>>
                        Смешанная
                    </p>
                    <p align="center">
                        <strong>Жүйке жүйесі:</strong>
                    </p>
                    <p align="center">
                        <strong>Невролог. Статус:</strong>
                    </p>
                    <p align="center" <?php echo ($Nev_id[80]['flag'] == 1)?'class="u"':'';?>>
                        Патологиясыз
                    </p>
                    <p align="center" <?php echo ($Nev_id[80]['flag'] == 1)?'class="u"':'';?>>
                        Без патологии
                    </p>
                    <p align="center">
                        Менингеалдық симптомдары:
                    </p>
                    <p align="center" <?php echo ($Menen_id[82]['flag'] == 1)?'class="u"':'';?>>
                        сірескен <U><?php echo $Menen_id[82]['Localize']?></U>
                    </p>
                    <p align="center" <?php echo ($Menen_id[83]['flag'] == 1)?'class="u"':'';?>>
                        Кернига (+-)
                    </p>
                    <p align="center" <?php echo ($Menen_id[84]['flag'] == 1)?'class="u"':'';?>>
                        Брудзинский (+-)
                    </p>
                    <p align="center">
                        Менингеальн. Симпт. <span <?php echo ($Menen_id[82]['flag'] == 1)?'class="u"':'';?>>Регидность <U><?php echo $Menen_id[82]['Localize']?></U> п.п.</span> <span <?php echo ($Menen_id[83]['flag'] == 1)?'class="u"':'';?>>Кернига (+-)</span>
                    </p>
                    <p align="center" <?php echo ($Menen_id[84]['flag'] == 1)?'class="u"':'';?>>
                        Брудзинский (+-)
                    </p>
                    <p align="center">
                        Көз ұясы:
                    </p>
                    <p align="center">
                        <span <?php echo ($Eye_id[86]['flag'] == 1)?'class="u"':'';?>>Парез қыли</span>
						<span <?php echo ($Eye_id[87]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Eye_id[88]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center">
                        көлденең , тік
                    </p>
                    <p align="center">
                        Глазные яблоки: 
						<span <?php echo ($Eye_id[86]['flag'] == 1)?'class="u"':'';?>>парез взора</span>
						<span <?php echo ($Eye_id[87]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Eye_id[88]['flag'] == 1)?'class="u"':'';?>>Л</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Eye_id[89]['flag'] == 1)?'class="u"':'';?>>Нистагм.</span>
						<span <?php echo ($Eye_id[90]['flag'] == 1)?'class="u"':'';?>>гориз. </span>
						<span <?php echo ($Eye_id[91]['flag'] == 1)?'class="u"':'';?>>верт.</span>                        
                    </p>
                </td>
                <td valign="top" width="140">
                    <p align="center">
                        ЧМН: 
						<span <?php echo ($Chmn_id[93]['flag'] == 1)?'class="u"':'';?>>птоз</span>
						<span <?php echo ($Chmn_id[94]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Chmn_id[95]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center"  <?php echo ($Chmn_id[96]['flag'] == 1)?'class="u"':'';?>>
                        мұрын-ерін қатпары жазық;
                    </p>
                    <p align="center"  <?php echo ($Chmn_id[97]['flag'] == 1)?'class="u"':'';?>>
                        жұтынуы бұзылған;
                    </p>
                    <p align="center">
						<span <?php echo ($Chmn_id[98]['flag'] == 1)?'class="u"':'';?>>тілі ауытқулы </span>
						<span <?php echo ($Chmn_id[99]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Chmn_id[100]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center"  <?php echo ($Chmn_id[101]['flag'] == 1)?'class="u"':'';?>>
                        төменгі еріннің салбырауы
                    </p>
                    <p align="center">
                        ЧМН:
						<span <?php echo ($Chmn_id[93]['flag'] == 1)?'class="u"':'';?>>птоз</span>
						<span <?php echo ($Chmn_id[94]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Chmn_id[95]['flag'] == 1)?'class="u"':'';?>>Л</span>
                    </p>
                    <p align="center" <?php echo ($Chmn_id[96]['flag'] == 1)?'class="u"':'';?>>
                        Носогубн.скл.сглаж.
                    </p>
                    <p align="center">
                        <span <?php echo ($Chmn_id[97]['flag'] == 1)?'class="u"':'';?>>нарушения глотания </span>
						<span <?php echo ($Chmn_id[98]['flag'] == 1)?'class="u"':'';?>>отклон.языка </span>
						<span <?php echo ($Chmn_id[99]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Chmn_id[100]['flag'] == 1)?'class="u"':'';?>>Л </span>
						<span <?php echo ($Chmn_id[101]['flag'] == 1)?'class="u"':'';?>>опущение угла рта</span>
                    </p>
                    <p align="center">
                        Сіңір рефлекстері:
                    </p>
                    <p align="center" <?php echo ($Reflex_id[103]['flag'] == 1)?'class="u"':'';?>>
                        қалыпты екі жақты
                    </p>
                    <p align="center">
                        <span <?php echo ($Reflex_id[104]['flag'] == 1)?'class="u"':'';?>>төмендеген</span>
						<span <?php echo ($Reflex_id[105]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Reflex_id[106]['flag'] == 1)?'class="u"':'';?>>С</span>
						<span <?php echo ($Reflex_id[107]['flag'] == 1)?'class="u"':'';?>>Ж</span>
						<span <?php echo ($Reflex_id[108]['flag'] == 1)?'class="u"':'';?>>Т</span>						
                    </p>
                    <p align="center">
						<span <?php echo ($Reflex_id[109]['flag'] == 1)?'class="u"':'';?>>жоғарылаған</span>
						<span <?php echo ($Reflex_id[110]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Reflex_id[111]['flag'] == 1)?'class="u"':'';?>>С</span>
						<span <?php echo ($Reflex_id[112]['flag'] == 1)?'class="u"':'';?>>Ж</span>
						<span <?php echo ($Reflex_id[113]['flag'] == 1)?'class="u"':'';?>>Т</span>                      
                    </p>
                    <p align="center" <?php echo ($Reflex_id[114]['flag'] == 1)?'class="u"':'';?>>
                        жоқ
                    </p>
                    <p align="center">
                        Сухожильн.рефлексы:
                    </p>
                    <p align="center" <?php echo ($Reflex_id[103]['flag'] == 1)?'class="u"':'';?>>
                        нормальн. Симметр.
                    </p>
                    <p align="center">
						<span <?php echo ($Reflex_id[104]['flag'] == 1)?'class="u"':'';?>>Снижены</span>
						<span <?php echo ($Reflex_id[105]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Reflex_id[106]['flag'] == 1)?'class="u"':'';?>>Л</span>
						<span <?php echo ($Reflex_id[107]['flag'] == 1)?'class="u"':'';?>>В</span>
						<span <?php echo ($Reflex_id[108]['flag'] == 1)?'class="u"':'';?>>Н</span>                        
                    </p>
                    <p align="center">
						<span <?php echo ($Reflex_id[109]['flag'] == 1)?'class="u"':'';?>>Повышен</span>
						<span <?php echo ($Reflex_id[110]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Reflex_id[111]['flag'] == 1)?'class="u"':'';?>>Л</span>
						<span <?php echo ($Reflex_id[112]['flag'] == 1)?'class="u"':'';?>>В</span>
						<span <?php echo ($Reflex_id[113]['flag'] == 1)?'class="u"':'';?>>Н</span>                      
                    </p>
                    <p align="center" <?php echo ($Reflex_id[114]['flag'] == 1)?'class="u"':'';?>>
                        Отсутствует
                    </p>
                    <p align="center">
                        Қозғалу сферасы:
                    </p>
                    <p align="center" <?php echo ($Move_id[116]['flag'] == 1)?'class="u"':'';?>>
                        парез (плегия)
                    </p>
                    <p align="center">
						<span <?php echo ($Move_id[117]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Move_id[118]['flag'] == 1)?'class="u"':'';?>>С</span>
						<span <?php echo ($Move_id[119]['flag'] == 1)?'class="u"':'';?>>Ж</span>
						<span <?php echo ($Move_id[120]['flag'] == 1)?'class="u"':'';?>>Т</span> 
                    </p>
                    <p align="center">
                        ет қуаты <span <?php echo ($Move_id[121]['flag'] == 1)?'class="u"':'';?>>жоғарылаған</span>  <span <?php echo ($Move_id[122]['flag'] == 1)?'class="u"':'';?>>(төмендеген)</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Move_id[123]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Move_id[124]['flag'] == 1)?'class="u"':'';?>>С</span>
						<span <?php echo ($Move_id[125]['flag'] == 1)?'class="u"':'';?>>Ж</span>
						<span <?php echo ($Move_id[126]['flag'] == 1)?'class="u"':'';?>>Т</span> 
                    </p>
                    <p align="center">
                        Двигатель. Сфера 
						<span <?php echo ($Move_id[116]['flag'] == 1)?'class="u"':'';?>>парез (плегия)</span>
						<span <?php echo ($Move_id[117]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Move_id[118]['flag'] == 1)?'class="u"':'';?>>Л</span>
						<span <?php echo ($Move_id[119]['flag'] == 1)?'class="u"':'';?>>В</span>
						<span <?php echo ($Move_id[120]['flag'] == 1)?'class="u"':'';?>>Н</span> 
						тонус мышц <span <?php echo ($Move_id[121]['flag'] == 1)?'class="u"':'';?>>повышен</span>  <span <?php echo ($Move_id[122]['flag'] == 1)?'class="u"':'';?>>(снижен)</span> 
                    </p>
                    <p align="center">
                        <span <?php echo ($Move_id[123]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Move_id[124]['flag'] == 1)?'class="u"':'';?>>Л</span>
						<span <?php echo ($Move_id[125]['flag'] == 1)?'class="u"':'';?>>В</span>
						<span <?php echo ($Move_id[126]['flag'] == 1)?'class="u"':'';?>>Н</span> 
                    </p>
                    <p align="center">
                        Ауру сезімталдығы
                    </p>
                    <p align="center">
                        <span <?php echo ($Bol_id[128]['flag'] == 1)?'class="u"':'';?>>төмендеген</span>
						<span <?php echo ($Bol_id[129]['flag'] == 1)?'class="u"':'';?>>О</span>
						<span <?php echo ($Bol_id[130]['flag'] == 1)?'class="u"':'';?>>С</span>
						<span <?php echo ($Bol_id[131]['flag'] == 1)?'class="u"':'';?>>Ж</span>
						<span <?php echo ($Bol_id[132]['flag'] == 1)?'class="u"':'';?>>Т</span>
                    </p>
                    <p align="center">
                        Болевая чувствитель 
						<span <?php echo ($Bol_id[128]['flag'] == 1)?'class="u"':'';?>>снижен</span>
						<span <?php echo ($Bol_id[129]['flag'] == 1)?'class="u"':'';?>>П</span>
						<span <?php echo ($Bol_id[130]['flag'] == 1)?'class="u"':'';?>>Л</span>
						<span <?php echo ($Bol_id[131]['flag'] == 1)?'class="u"':'';?>>В</span>
						<span <?php echo ($Bol_id[132]['flag'] == 1)?'class="u"':'';?>>Н</span>						
                    </p>
                    <p align="center">
                        Афазия:<span <?php echo ($Afaz_id[134]['flag'] == 1)?'class="u"':'';?>>моторлы</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Afaz_id[135]['flag'] == 1)?'class="u"':'';?>>сенсорлы</span>, <span <?php echo ($Afaz_id[136]['flag'] == 1)?'class="u"':'';?>>тоталды</span>
                    </p>
                    <p align="center">
                        Афазия:<span <?php echo ($Afaz_id[134]['flag'] == 1)?'class="u"':'';?>>моторная</span>, <span <?php echo ($Afaz_id[135]['flag'] == 1)?'class="u"':'';?>>сенсорная</span>,<span <?php echo ($Afaz_id[136]['flag'] == 1)?'class="u"':'';?>>тотальная</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Sbabin_id[138]['flag'] == 1 || $Sbabin_id[139]['flag'] == 1)?'class="u"':'';?>>Бабинский симпт.</span>			 
						<span <?php echo ($Sbabin_id[138]['flag'] == 1)?'class="u"':'';?>>О</span>	 
						<span <?php echo ($Sbabin_id[139]['flag'] == 1)?'class="u"':'';?>>С</span>
                    </p>
                    <p align="center">
						<span <?php echo ($Soppen_id[141]['flag'] == 1 || $Soppen_id[142]['flag'] == 1)?'class="u"':'';?>>Оппенгейм симп.</span>			 
						<span <?php echo ($Soppen_id[141]['flag'] == 1)?'class="u"':'';?>>О</span>	 
						<span <?php echo ($Soppen_id[142]['flag'] == 1)?'class="u"':'';?>>С</span>                       
                    </p>
                    <p align="center" <?php echo ($Soppen_id[143]['flag'] == 1)?'class="u"':'';?>>
                        перифериялық нервтердің
                    </p>
                    <p align="center">
                        тартылу симптомы
                    </p>
                    <p align="center">
						<span <?php echo ($Sbabin_id[138]['flag'] == 1 || $Sbabin_id[139]['flag'] == 1)?'class="u"':'';?>>с.Бабинского</span>			 
						<span <?php echo ($Sbabin_id[138]['flag'] == 1)?'class="u"':'';?>>П</span>	 
						<span <?php echo ($Sbabin_id[139]['flag'] == 1)?'class="u"':'';?>>Л</span>                         
                    </p>
                    <p align="center">
						<span <?php echo ($Soppen_id[141]['flag'] == 1 || $Soppen_id[142]['flag'] == 1)?'class="u"':'';?>> с. Оппенгейма </span>			 
						<span <?php echo ($Soppen_id[141]['flag'] == 1)?'class="u"':'';?>>П</span>	 
						<span <?php echo ($Soppen_id[142]['flag'] == 1)?'class="u"':'';?>>Л</span>                       
                    </p>
                    <p align="center"  <?php echo ($Soppen_id[143]['flag'] == 1)?'class="u"':'';?>>
                        с. натяж.периф.нервов
                    </p>
                    <p align="center">
                        <strong>Аңқасы:Зев:</strong>
                    </p>
                    <p align="center" <?php echo ($Zev_id[145]['flag'] == 1)?'class="u"':'';?>>
                        Қалыпты спокойный
                    </p>
                    <p align="center" <?php echo ($Zev_id[146]['flag'] == 1)?'class="u"':'';?>>
                        Қызарған гиперимия
                    </p>
                    <p align="center" <?php echo ($Zev_id[147]['flag'] == 1)?'class="u"':'';?>>
                        Ісіңкі отечность
                    </p>
                    <p align="center">
                        Таңдай:<span <?php echo ($Mindal_id[149]['flag'] == 1)?'class="u"':'';?>>ұлғайған.</span>,
                    </p>
                    <p align="center">
                        <span <?php echo ($Mindal_id[150]['flag'] == 1)?'class="u"':'';?>>іркілдеп тұр.,</span>
                    </p>
                    <p align="center">
                        іріңдеп тұр
                    </p>
                    <p align="center">
                        Миндалины: <span <?php echo ($Mindal_id[149]['flag'] == 1)?'class="u"':'';?>>увелич</span>, <span <?php echo ($Mindal_id[150]['flag'] == 1)?'class="u"':'';?>>рыхлые</span>
                    </p>
                    <p align="center">
                        Пробкибездері
                    </p>
                </td>
                <td valign="top" width="100">
                    <p align="center">
                        <strong>Ас қорыту жүйесі:</strong>
                    </p>
                    <p align="center">
                        <strong>Пищеварительная</strong>
                    </p>
                    <p align="center">
                        <strong>система:</strong>
                    </p>
                    <p align="center">
                        Тілі: 
						<span <?php echo ($Lang_id[152]['flag'] == 1)?'class="u"':'';?>>таза,</span>
						<span <?php echo ($Lang_id[153]['flag'] == 1)?'class="u"':'';?>>ылғалды</span>-
						<span <?php echo ($Lang_id[154]['flag'] == 1)?'class="u"':'';?>>құрғақ</span>
                    </p>
                    <p align="center">
                        Язык: 
						<span <?php echo ($Lang_id[152]['flag'] == 1)?'class="u"':'';?>>чистый</span>,                    
                        <span <?php echo ($Lang_id[153]['flag'] == 1)?'class="u"':'';?>>влажный</span>-
						<span <?php echo ($Lang_id[154]['flag'] == 1)?'class="u"':'';?>>сухой</span>
                    </p>
                    <p align="center" <?php echo ($Lang_id[155]['flag'] == 1)?'class="u"':'';?>>
                        жағындымен
                    </p>
                    <p align="center" <?php echo ($Lang_id[155]['flag'] == 1)?'class="u"':'';?>>
                        обложен налетом
                    </p>
                    <p align="center">
                        Іші: 
						<span <?php echo ($Gaste_id[157]['flag'] == 1)?'class="u"':'';?>>жұмсақ</span>, 
						<span <?php echo ($Gaste_id[158]['flag'] == 1)?'class="u"':'';?>>ауырсынбайды ауырсынады</span>,                  
                        <span <?php echo ($Gaste_id[159]['flag'] == 1)?'class="u"':'';?>>керілген</span>, 
						<span <?php echo ($Gaste_id[160]['flag'] == 1)?'class="u"':'';?>>кеуіп</span>
						<span <?php echo ($Gaste_id[161]['flag'] == 1)?'class="u"':'';?>>тұр</span>;    						
						<span <?php echo ($Gaste_id[162]['flag'] == 1)?'class="u"':'';?>>тыныс алуға қатысуда (иә,<u>жоқ</u>)</span> 
						<span <?php echo ($Gaste_id[162]['flag'] != 1)?'class="u"':'';?>>тыныс алуға қатысуда (<u>иә</u>,жоқ)</span> 
                    </p>
                    <p align="center">
                        <strong>Живот:</strong>
						<span <?php echo ($Gaste_id[157]['flag'] == 1)?'class="u"':'';?>>мягкий</span>, 
						<span <?php echo ($Gaste_id[158]['flag'] == 1)?'class="u"':'';?>>безболезн</span>,                  
                        <span <?php echo ($Gaste_id[159]['flag'] == 1)?'class="u"':'';?>>болезн</span>, 
						<span <?php echo ($Gaste_id[160]['flag'] == 1)?'class="u"':'';?>>напряжен</span>
						<span <?php echo ($Gaste_id[161]['flag'] == 1)?'class="u"':'';?>>вздут</span>;    						
						<span <?php echo ($Gaste_id[162]['flag'] == 1)?'class="u"':'';?>></span> 
						<span <?php echo ($Gaste_id[162]['flag'] != 1)?'class="u"':'';?>>участ.в дых. (<u>да</u>,нет)</span>                        
                    </p>
                    <p align="center">
                        Симптомдары:
                    </p>
                    <p align="center">
                        Симптомы:
                    </p>
                    <p align="center" <?php echo ($Sympt_id[164]['flag'] == 1)?'class="u"':'';?>>
                        Щеткина-Бл + -
                    </p>
                    <p align="center" <?php echo ($Sympt_id[165]['flag'] == 1)?'class="u"':'';?>>
                        Ровзинга + -
                    </p>
                    <p align="center" <?php echo ($Sympt_id[166]['flag'] == 1)?'class="u"':'';?>>
                        Ситковского + -
                    </p>
                    <p align="center" <?php echo ($Sympt_id[167]['flag'] == 1)?'class="u"':'';?>>
                        Ортнера + -
                    </p>
                    <p align="center">
                        Бауыры: 
						<span <?php echo ($Liver_id[169]['flag'] == 1)?'class="u"':'';?>>қалыпты</span>
                    </p>
                    <p align="center">
                       <span <?php echo ($Liver_id[170]['flag'] == 1)?'class="u"':'';?>><U><?php echo $Liver_id[170]['Localize']?></U> см-ге ұлғайған</span>  
                    </p>
                    <p align="center">
                       <span <?php echo ($Liver_id[171]['flag'] == 1)?'class="u"':'';?>>қатты</span>
					   <span <?php echo ($Liver_id[172]['flag'] == 1)?'class="u"':'';?>>ауырсынады</span> 
                    </p>
                    <p align="center">
                        Печень: 
						<span <?php echo ($Liver_id[169]['flag'] == 1)?'class="u"':'';?>>в норме</span> 
						<span <?php echo ($Liver_id[170]['flag'] == 1)?'class="u"':'';?>>увеличена <U><?php echo $Liver_id[170]['Localize']?></U> см </span> 
						<span <?php echo ($Liver_id[171]['flag'] == 1)?'class="u"':'';?>>плотная</span>  
						<span <?php echo ($Liver_id[172]['flag'] == 1)?'class="u"':'';?>>болезнен</span> 
                    </p>
                    <p align="center">
                        Көк б.
						<span <?php echo ($Selez_id[174]['flag'] == 1)?'class="u"':'';?>>қалыпты</span>
                        <span <?php echo ($Selez_id[175]['flag'] == 1)?'class="u"':'';?>><U><?php echo $Selez_id[175]['Localize']?></U> см-ге ұлғайған</span> 
						<span <?php echo ($Selez_id[176]['flag'] == 1)?'class="u"':'';?>>қатты</span>
						<span <?php echo ($Selez_id[177]['flag'] == 1)?'class="u"':'';?>>ауырсы-нады</span> 
                    </p>
                    <p align="center">
                        Селезенка 
						<span <?php echo ($Selez_id[174]['flag'] == 1)?'class="u"':'';?>>в норме </span>
						<span <?php echo ($Selez_id[175]['flag'] == 1)?'class="u"':'';?>>увеличена <U><?php echo $Selez_id[175]['Localize']?></U> см </span>
						<span <?php echo ($Selez_id[176]['flag'] == 1)?'class="u"':'';?>>плотная </span>
						<span <?php echo ($Selez_id[177]['flag'] == 1)?'class="u"':'';?>>болезнен</span>
                    </p>
                    <p align="center">
                        <strong>Несеп жыныс жүйесі</strong>
                    </p>
                    <p align="center">
                        <strong>Мочеполовая система</strong>
                    </p>
                    <p align="center">
                        Несеп жүруі:
                    </p>
                    <p align="center">
                        <span <?php echo ($Moch_id[179]['flag'] == 1)?'class="u"':'';?>>қалыпты</span>                   
                        <span <?php echo ($Moch_id[180]['flag'] == 1)?'class="u"':'';?>>дизурия <U><?php echo $Moch_id[180]['Localize']?></U></span>
                    </p>
                    <p align="center">
                        Мочеотделение:
                    </p>
                    <p align="center">
                        <span <?php echo ($Moch_id[179]['flag'] == 1)?'class="u"':'';?>>Нормальное</span>
                        <span <?php echo ($Moch_id[180]['flag'] == 1)?'class="u"':'';?>>Дизурия <U><?php echo $Moch_id[180]['Localize']?></U></span>
                    </p>
                    <p align="center">
                        Стул <u>{Shit}</u>
                    </p>
                </td>
                <td valign="top" width="100">
                    <p align="center">
                        Етеккір циклы:
                    </p>
                    <p align="center">
                        <span <?php echo ($Menst_id[182]['flag'] == 1)?'class="u"':'';?>>бұзылмаған</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Menst_id[183]['flag'] == 1)?'class="u"':'';?>>бұзылған</span>
                    </p>
                    <p align="center">
                        Менструальный цикл:
                    </p>
                    <p align="center">
                        <span <?php echo ($Menst_id[182]['flag'] == 1)?'class="u"':'';?>>Без нарушений</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Menst_id[183]['flag'] == 1)?'class="u"':'';?>>Нарушения</span>
                    </p>
                    <p align="center">
                        <strong>Перифериялық</strong>
                    </p>
                    <p align="center">
                        <strong>ісіну</strong>
                    </p>
                    <p align="center">
                        <strong>Периферические отеки</strong>
                    </p>
                    <p align="center">
                        <span <?php echo ($Per_id[185]['flag'] == 1)?'class="u"':'';?>>Жоқ отсутствует</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Per_id[186]['flag'] == 1)?'class="u"':'';?>>Ісіңкі пастозность</span>
                    </p>
                    <p align="center">
                        <span <?php echo ($Per_id[187]['flag'] == 1)?'class="u"':'';?>>іскен Отечность</span>
                    </p>
                    <p align="center">
                        ТАЖ ЧДД <br><b>{Chd}</b>
                    </p>
                    <p align="center">
                        t º Т С <br><b>{Temperature}</b>
                    </p>
                    <p align="center">
                        Пульс <br><b>{Pulse}</b>
                    </p>
                    <p align="center">
                        ЖСЖ
                    </p>
                    <p align="center">
                        ЧСС <br><b>{Chss}</b>
                    </p>
                    <p align="center">
                        АҚҚ
                    </p>
                    <p align="center">
                        оң
                    </p>
                    <p align="center">
                        сол
                    </p>
                    <p align="center">
                        АД <br><b>{AD}</b>
                    </p>
                    <p align="center">
                        Прав  <br><b>{WorkAD}</b>
                    </p>
                    <p align="center">
                        Қалыпты
                    </p>
                    <p align="center">
                        қан қысымы
                    </p>
                    <p align="center">
                        SaO  <br><b>{SaO}</b>
                    </p>
                    <p align="center">
                        Қан
                    </p>
                    <p align="center">
                        Құрамын-
                    </p>
                    <p align="center">
                        дағы қант
                    </p>
                    <p align="center">
                        Сахар крови  <br><b>{Gluck}</b>
                    </p>
                </td>
                <td valign="top" width="80">
                    <p align="center">
                        Емдеу
                    </p>
                    <p align="center">
                        нәтижесі
                    </p>
                    <p align="center">
                        Результаты
                    </p>
                    <p align="center">
                        лечения:
                    </p>
                    <p align="center" <?php echo ($Result_id[189]['flag'] == 1)?'class="u"':'';?>>
                        жақсарды
                    </p>
                    <p align="center" <?php echo ($Result_id[190]['flag'] == 1)?'class="u"':'';?>>
                        өзгеріссіз
                    </p>
                    <p align="center" <?php echo ($Result_id[191]['flag'] == 1)?'class="u"':'';?>>
                        нашарлады
                    </p>
                    <p align="center" <?php echo ($Result_id[189]['flag'] == 1)?'class="u"':'';?>>
                        улучшение
                    </p>
                    <p align="center" <?php echo ($Result_id[190]['flag'] == 1)?'class="u"':'';?>>
                        без изменений
                    </p>
                    <p align="center" <?php echo ($Result_id[191]['flag'] == 1)?'class="u"':'';?>>
                        ухудшение
                    </p>
					
					<p align="center">
                        ТАЖ ЧДД <br><b>{AfterChd}</b>
                    </p>
                    <p align="center">
                        t º Т С <br><b>{AfterTemperature}</b>
                    </p>
                    <p align="center">
                        Пульс <br><b>{AfterPulse}</b>
                    </p>
                    <p align="center">
                        ЖСЖ
                    </p>
                    <p align="center">
                        ЧСС <br><b>{AfterChss}</b>
                    </p>
                    <p align="center">
                        АҚҚ
                    </p>
                    <p align="center">
                        оң
                    </p>
                    <p align="center">
                        сол
                    </p>
                    <p align="center">
                        АД <br><b>{AfterAD}</b>
                    </p>
                    <p align="center">
                        Прав  <br><b>{AfterWorkAD}</b>
                    </p>
                    <p align="center">
                        Қалыпты
                    </p>
                    <p align="center">
                        қан қысымы
                    </p>
                    <p align="center">
                        SaO  <br><b>{AfterSaO}</b>
                    </p>
                    <p align="center">
                        Қан
                    </p>
                    <p align="center">
                        Құрамын-
                    </p>
                    <p align="center">
                        дағы қант
                    </p>
                    <p align="center">
                        Сахар крови  <br><b>{AfterGluck}</b>
                    </p>
					
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div align="center">
    <table width="718" border="1" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td width="150">
                    <p align="center">
                        <strong>Status localis</strong>
                    </p>
                </td>
                <td colspan="3">
					&nbsp;
                </td>                
            </tr>          
            <tr>
                <td width="150">
                    <p align="center">
                        <strong>Жедел жәрдем диагнозы</strong><br/>				
                        <strong>Диагноз скорой помощи</strong>
                    </p>
                </td>
                <td width="209">
                    <p align="center">
                        <strong>Диагностиканың аспаптық әдістері</strong><br/>        
                        <strong>Инструментальные методы диагностики:</strong>
                    </p>
                </td>
				<td width="180">
                    <p align="center">
                        <strong>Емдеу іс-шаралары</strong><br/>
                        <strong>Лечебные мероприятия</strong>
                    </p>
                </td>
                <td width="180">
                    <p align="center">
                        <strong>Шығын:</strong><br/>                   
                        <strong>Расход</strong>
                    </p>
                </td>               
            </tr>
            <tr>
                <td width="150">
					{Diag}
                </td>
                <td width="209">
					{Instrument}
                </td>
                 <td width="180" >
					{Lecheb}
                </td>
                <td width="180" >
					{Rashod}
                </td>
            </tr>
            <tr>
                <td width="150">
                </td>
                <td width="209">
                </td>
                <td width="180">
                </td>
                <td width="180">
                </td>
            </tr>
            <tr>
                <td width="150">
                </td>
                <td width="209">
                </td>
                <td width="180">
                </td>
                <td width="180">
                </td>
            </tr>
            <tr>
                <td width="150">
                </td>
                <td width="209">
                </td>
                <td width="180">
                </td>
                <td width="180">
                </td>
            </tr>
            <tr>
                <td width="150">
                </td>
                <td width="209">
                </td>
                <td width="180">
                </td>
                <td width="180">
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div align="center">
    <table width="718" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
					Картаны толтырған: дәрігер (фельдшер) ________________________________________
				</td>
				<td width="200">
					(Қолы) ___________________
				</td>
			</tr>
			<tr>
				<td>
						Карту заполнил: 
						<?php
						if ($Doctor && $Doctor != '&nbsp;') echo "<u>врач</u> (фельдшер) &nbsp;".$Doctor;
						elseif ($Feldsher && $Feldsher != '&nbsp;') echo "врач (<u>фельдшер</u>) &nbsp;".$Feldsher;
						else echo "врач (фельдшер)&nbsp;";
						//ФИО Старшего бригады, обслужившей вызов EmergencyTeam_HeadShiftFIO
						?>
				</td>
				<td width="200">
					(Подпись) _________________
				</td>
			</tr>
		</tbody>
	</table>
</div>

</body>
</html>