<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size:10pt;}    
p {margin:0 0 10px}
table {font-size:12pt; vertical-align: top;}
table td {font-size:10pt;}
.lefttd {align: left; width: 400px; font-weight: bold; vertical-align: top;}
.leftminitd {align: left; width: 400px; vertical-align: top;}
.righttd {align: left; border-bottom: #aaaaaa 1px solid; vertical-align: top;}
.linetd {background-color: #000; height: 2px;}
.tit {font-family:Arial; font-weight:bold; font-size:12pt; text-align:center}
.podval {font-size:8pt}
.v_ok:after {content: "V"; font-family: Verdana; font-size: 14px; font-weight: bold; border: 1px solid #000; height: 12px; display: inline-block; line-height: 12px;}
.v_no {border: 1px solid #000; width:11px; height: 12px;}
.head110 {font-size:14px; vertical-align: top;}
.head110 big {font-size:12px; line-height:14px;}
table.time {border-collapse:collapse; border:1px solid #000}
    table.time td {border:1px solid #000; text-align: center; font-size: 13px;}
span {display:inline-block; width:30px}
.wrapper110 {display: inline-block;}
.innerwrapper {display:inline-block}
.innerwrapper .v_ok, .innerwrapper .v_no {display:inline-block; margin: 0 15px 0 0;}
.innerwrapper u {margin: 0 15px 0 0}

.wrapper110 u.reason-info {display: none}
.wrapper110.other-selected u.reason-info {display: inline-block}


</style>

<title>КАРТА ВЫЗОВА СМП №{Year_num}/{Day_num} ОТ {CallCardDate}</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<table width="100%" class="head110">
    <tr>
	<td width="60%" align="center" valign="top"><big>{Lpu_name}<br> {UAddress_Address} т.{Lpu_Phone}</big></td>	
	<td width="40%" align="center"  valign="top">
	<?php /*?>
		 * Приложение № 3<br>
		к приказу Министерства здравоохранения <br>и социального развития Российской Федерации<br>
			от 2 декабря 2009г. № 942 <br>                                    
		Медицинская документация<br>
		<strong>Учетная форма № 110/у</strong><br>
		Утверждена приказом<br>
		Министерства здравоохранения <br>и социального развития Российской Федерации<br>
		от 2 декабря 2009г. № 942    <?*/?>    
		<?php if(isset($CmpCloseCardWhere_DT) && trim($CmpCloseCardWhere_DT) != '&nbsp;' || isset($CmpCloseCardWhereReported_name) && trim($CmpCloseCardWhereReported_name) != '&nbsp;'){?>
		<table style="height: 100px;">
			<tr>
				<td align="center" valign="top">					
					<b>Учетная форма № 110/у</b><br>
					Утверждена приказом<br>
					Министерства здравоохранения<br>
					и социального развития Российской Федерации<br>
					от 2 декабря 2009 г. № 942					
				</td>
				<td style="border: 1px solid" valign="top">{CmpCloseCardWhereReported_name}</td>
				<td style="border: 1px solid" valign="top">{CmpCloseCardWhere_DT}</td>
			</tr>
		</table>
		<?php } else {?>
			<b>Учетная форма № 110/у</b><br>
			Утверждена приказом<br>
			Министерства здравоохранения<br>
			и социального развития Российской Федерации<br>
			от 2 декабря 2009 г. № 942
		<?php }?>

</td>
    </tr>
</table>       
<center>
	<h2>КАРТА<br>
	вызова скорой медицинской помощи №{Year_num}/{Day_num}<br>
	{CallCardDate}</h2>
</center>

<p>1. Номер фельдшера по приему вызова:<span></span>{FeldsherAccept_TabCode}</p>
<p>2. Номер станции (подстанции), отделения:<span></span>{StationNum}</p>
<p>3. Номер бригады скорой медицинской помощи:<span></span>{EmergencyTeamNum}</p>
<p>4. Время (часы, минуты):</p>
<table width="90%" class="time">
    <tr>
	<td width="10%">приема вызова</td>
	<td width="10%">передачи вызова бригаде скорой медицинской помощи</td>
	<td width="10%">выезда на вызов</td>
	<td width="10%">прибытия на место вызова</td>
	<td width="10%">начало транспортировки больного</td>
	<td width="10%">прибытия в медицинскую организацию</td>
	<td width="10%">окончания вызова</td>
	<td width="10%">возвращения на станцию(подстанцию, отделение)</td>
	<td width="10%">затраченное на выполнение вызова</td>
    </tr>
    <tr>
	<td>{AcceptTime}</td>
	<td>{TransTime}</td>
	<td>{GoTime}</td>
	<td>{ArriveTime}</td>
	<td>{TransportTime}</td>
	<td>{ToHospitalTime}</td>
	<td>{EndTime}</td>
	<td>{BackTime}</td>
	<td>{SummTime}</td>
    </tr>
</table>
<table>
    <tr>
	<td width="60%">
	    5. Aдрес вызова:<br>
	    Район {Area}, Город/село {City},
	    Населенный пункт {Town},
	    Улица <?php echo isset($Street) ? $Street : $Street2 ?><br>
		Дом <?php
		if ( !empty($secondStreetName)) { ?>
			{secondStreetName}
		<?php } else{ ?>
			{House}
		<?php } ?>
		,
		Корпус {Korpus}, Квартира {Office}, Комната {Room},
	    Подъезд {Entrance}, Код подъезда {CodeEntrance}, Этаж {Level}<br><br>
	    7. Кто вызывал: {CmpCallerType_Name}, № телефона вызывающего: {Phone}<br>
	    8. Фельдшер, принявший вызов: {FeldsherAcceptName}<br>
	    9. Фельдшер, передавший вызов: {FeldsherTransName}
	</td>
	</tr>
	<tr>
	<td width="40%" style= "padding: 10px 0;">
	    6. Сведения о больном:<br>
	    Фамилия: {Fam}<br>
	    Имя: {Name}<br>
	    Отчество: {Middle}<br>
	    Возраст: {Age} <?php print(trim($C_AgeType) == '<div class="wrapper110"></div>')?'Лет':$C_AgeType;?><br>
	    Пол: {Sex_name}<br>
	    Место работы: {Work}<br>
	    Серия и номер документа, удостоверяющего личность: {DocumentNum}<br>
		<?php if(!empty($Person_Snils)) print('Номер пенсионного свидетельства: '.$Person_Snils); ?><br>
		Серия и номер полиса обязательного медицинского страхования: {Person_PolisSer} {Person_PolisNum}
	</td>
    </tr>
</table>
<p>10. Место регистрации больного: {C_PersonRegistry_id}</p>
<p>11. Социальное положение больного: {C_PersonSocial_id}</p>
<p>12. Повод к вызову: {C_CallPovodNew_id}<u class="reason-info">{Reason}</u></div></p>
<p>13. Вызов: {CmpCallType_Code}</p>
<p>14. Место получения вызова бригадой скорой медицинской помощи: {C_CallTeamPlace_id}</p>
<p>15. Причины выезда с опозданием: {C_Delay_id}</p>
<p>16. Состав бригады скорой медицинской помощи: {C_TeamComplect_id}</p>
<p>17. Место вызова: {C_CmpCallPlaceType_id}</p>
<p>18. Причина несчастного случая: {C_AccidentReason_id}</p>
<p>{C_Trauma_id}</p>
<p>19. Наличие клиники опьянения: {isAlcoBlocks}</p>
<p>20. Жалобы:<br>{Complaints}</p>
<p>21. Анамнез:<br>{Anamnez}</p>
<p>22. Объективные данные:</p>
Общее состояние: {Condition_id}<br>
Поведение: {Behavior_id}<br>
Сознание: {Cons_id}<br>
Менингеальные знаки: {isMenen}<br>
Зрачки: {Pupil_id}<br>
Нистагм: {isNist}<br>
Анизокория: {isAnis}<br>
Реакция на свет: {isLight}<br>
Кожные покровы: {Kozha_id}<br>
Акроцианоз: {isAcro}<br>
Мраморность: {isMramor}<br>
Отеки: {Hypostas_id}<br>
Сыпь: {Crop_id}<br>
Дыхание: {Hale_id}<br>
Хрипы: {Rattle_id}<br>
Одышка: {Shortwind_id}</p>
<p>Органы системы кровообращения:<br>
Тоны сердца: {Heart_id}<br>
Шум: {Noise_id}<br>
Пульс: {Pulse_id}</p>
<p>Органы пищеварения:<br>
Язык: {Lang_id}<br>
Живот: {Gaste_id}<br>
Участвует в акте дыхания: {isHaleBlocks}<br>
Симптомы раздражения брюшины: {isPeritBlocks}</p>
<p>Печень: {Liver_id}</p>
<p>Мочеиспускание: {Urine}</p>
<p>Стул: {Shit}</p>
<p>Другие симптомы : {OtherSympt}</p>
<table width="60%">
    <tr>
	<td width="50%">Рабочее АД: {WorkAD} мм. рт.ст.</td>
	<td width="50%">АД: {AD} мм. рт.ст.</td>
    </tr>
    <tr>
	<td>Пульс : {Pulse} ударов в минуту</td>
	<td>ЧСС: {Chss} в минуту</td>
    </tr>
    <tr>
	<td>ЧД : {Chd} в минуту</td>
	<td>t: {Temperature}&deg;C</td>
    </tr>
    <tr>
	<td>Пульсоксиметрия:{Pulsks}</td>
	<td>Глюкометрия: {Gluck}</td>
    </tr>
</table>
<p>Дополнительные объективные данные. Локальный статус.</p>
{LocalStatus}
<p>Электрокардиограмма (ЭКГ)<br><br>
ЭКГ до оказания медицинской помощи:<br>
{Ekg1} Время {Ekg1Time}</p>
<p>ЭКГ после оказания медицинской помощи:<br>
{Ekg2} Время {Ekg2Time}</p>
<p>23. Диагноз:<br>
{Diag}. Код по МКБ-10: {CodeDiag}</p>
<p>
<p>24. Осложнения:<br>{Complicat_id}</p>
<p>25. Эффективность мероприятий при осложнении:<br>{ComplicatEf_id}</p>
<p>26. Оказанная помощь на месте вызова (проведенные манипуляции и мероприятия).<br>
{HelpPlace}</p>
<p>27. Оказанная помощь в автомобиле скорой медицинской помощи (проведенные манипуляции и мероприятия):<br>{HelpAuto}</p>
<p>28. Эффективность проведенных мероприятий:<br>
<table width="60%">			
	<tr>
		<td width="50%">АД: {EfAD}</td>			
		<td width="50%">Пульс : {EfPulse}</td>
	</tr>
	<tr>
		<td>ЧСС: {EfChss}</td>
		<td>ЧД: {EfChd}</td>
	</tr>
	<tr>
		<td colspan="2">t: {EfTemperature}</td>
	</tr>
	<tr>
		<td>Пульсоксиметрия: {EfPulsks}</td>
		<td>Глюкометрия: {EfGluck}</td>
	</tr>
</table>

<?php if ( !empty( $equipment ) ): ?>
	<p>Использованное оборудование (на месте/в машине)</p>

	<table width="100%">
	<tr>
	<?php
	$columns = 3;
	$cnt = sizeof( $equipment );
	$col_length = ceil( $cnt / $columns );
	for( $i=0; $i<$cnt; $i++ ){
		if ( $i>0 && ( ($i%$col_length) === 0 ) ) {
			echo '</tr></tr>';
		}
		echo '
			<td>'.$equipment[ $i ]['CmpEquipment_Name'].'</td>
			<td>'.$equipment[ $i ]['CmpCloseCardEquipmentRel_UsedOnSpotCnt'].'/'.$equipment[ $i ]['CmpCloseCardEquipmentRel_UsedInCarCnt'].'</td>
		';
	}
	?>
	</tr>
	</table>

	<p></p>
<?php endif; ?>


<p>29. Согласие на медицинское вмешательство <b>{isSogl}</b><br>
В соответствии со ст. 32 Основ законодательства Российской Федерации об охране здоровья граждан информированное добровольное согласие на медицинское вмешательство с учетом риска возможных осложнений получено<br>
_____________________________ (ФИО пациента) <span></span><span></span><span></span><span></span><span></span><span></span> ________________ (подпись)<br><br><br>
_____________________________ (Ф.И.О, должность медицинского работника) <span></span> ________________ (подпись)
</p>
<p>30. Отказ от медицинского вмешательства <b>{isOtkazMed}</b><br>
В соответствии со ст. 33 Основ законодательства Российской Федерации об охране здоровья граждан отказ от медицинского вмешательства или требование прекратить медицинское вмешательство. Возможные осложнения и последствия отказа в доступной для меня форме разъяснены.<br>
_____________________________ (ФИО пациента) <span></span><span></span><span></span><span></span><span></span><span></span> ________________ (подпись)<br><br><br>
_____________________________ (Ф.И.О, должность медицинского работника) <span></span> ________________ (подпись)
</p>
<p>31. Отказ от транспортировки для госпитализации в стационар. <b>{isOtkazHosp}</b><br>
Возможные осложнения и последствия отказа в доступной для меня форме разъяснены.<br>
"___"  __________ 20 … г. в ______ часов.<br>
_____________________________ (ФИО пациента) <span></span><span></span><span></span><span></span><span></span><span></span> ________________ (подпись)<br><br><br>
_____________________________ (Ф.И.О, должность медицинского работника) <span></span> ________________ (подпись)</p>
<p>32. Результат оказания скорой медицинской помощи:<br>
{Result_id}</p>
<p>33. Больной:<br>
{Patient_id}</p>
<p>34. Способ доставки больного в автомобиль скорой медицинской помощи:<br>
{TransToAuto_id}</p>
<p>35. Результат выезда:</p>
{ResultUfa_id}
<p>36. Километраж выезда: {Kilo} км.</p>
<p>37. Примечания: {DescText}</p>
<br/>
<p>38. Манипуляции:</p>
<?php foreach($uslugalist as $drug):?>
	<p><?= isset($drug['UslugaComplex_Name'])  ?  $drug['UslugaComplex_Name'] : ''?>,
		Кол-во: <?= isset($drug['CmpCallCardUsluga_Kolvo'])  ?  $drug['CmpCallCardUsluga_Kolvo'] : ''?></p>
<?php endforeach?>
</p>
<p>
<br/>
<p>39. Расходные материалы:</p>
<?php foreach($druglist as $drug):?>
	<p ><?= isset($drug['DrugComplexMnn_RusName'])  ?  $drug['DrugComplexMnn_RusName'] : ''?>, <?= isset($drug['DrugTorg_Name'])  ?  $drug['DrugTorg_Name'] : ''?>,
		Кол-во: <?= isset($drug['CmpCallCardDrug_Kolvo'])  ?  $drug['CmpCallCardDrug_Kolvo'] : ''?>, Ед. изм.: <?= isset($drug['GoodsUnit_Name'])  ?  $drug['GoodsUnit_Name'] : ''?>;</p>
<?php endforeach?>
</p>
<p>Врач (фельдшер) _________________________________(ФИО)     ____________________________(Подпись)  </p>
<p>Фельдшер I _________________________________(ФИО)</p>
<p>Фельдшер II _________________________________(ФИО)</p>
<p>Санитар _________________________________(ФИО)</p>
<p>Водитель _________________________________(ФИО)</p>

<p>Карта проверена (результат экспертной оценки):</p>
<p>Старший врач смены  _________________________________(ФИО)            ____________________________(Подпись) </p>
 
<p>Заведующий подстанцией _________________________________(ФИО)     ____________________________(Подпись) </p>
 </body>
</html>