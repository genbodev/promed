<html>
<head>
	<title>Паспорт здоровья</title>
	<style type="text/css">
		@page port { size: portrait }
		@page land { size: landscape }
		body { page: land; margin: 0px; padding: 0px; font-size:10pt; }
		.pagetable TD.page { border: 1px none black; width:48%; text-align: center; vertical-align: top; padding: 0em 0em; }
	</style>

	<style type="text/css" media="print">
		@page port { size: portrait }
		@page land { size: landscape }
		body { page: land; margin: 0px; padding: 0px; font-size:10pt; }	
		.twopage { page-break-after: always; }
		.pagetable TD.page { border: 1px none black; width:48%; text-align: center; vertical-align: top; padding: 0em 0em; }
	</style>
	
	
	<style>
		.pagetable { width:100%;  border-collapse: collapse; }
		.pagetable TD { font-size:10pt; font-family:"Times New Roman",Georgia,Serif; }
		.pagetable TD.page_separetor { display: block; }
		.divlft { text-align: left; }
		.divrgt { text-align: right; }
		.trhidden { display: none; }
		.pagenumber { margin-bottom: 1em; }
		.simpletable TD { border: 1px solid black; }
		
		TABLE.title { width: 75%; margin-left: 5em; }
		TABLE.title TD { font-size: 12pt; font-weight: bolder; }
		TD.title { font-size: 12pt; }

		.struc { border-collapse: collapse; width:100%; }
		.struc TD { border: 1px none red; vertical-align: top; padding: 0em 0.5em; padding-top: 0.4em; text-align: left; }
		.struc TD.underline { border-bottom: 1px solid black; text-align: center; }
		.struc TD.ct { text-align: center; }
		.struc TD.measure { padding-left: 0em; }
		.struc TD.measure_rt { padding-right: 0em; }
		.struc TD.u_text { padding-top: 0em; }
		
		.info { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
		.info TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
		.info TD.header { font-weight: bolder; }
		.info TD.table_header { font-weight: bolder; border-style:none; height:2em; }
		.info TD.measure, .info TD.m { text-align:left; }
		.info TD.y { width: 50px; }
		.info TD.y2 { width: 60px; }
		
		.measures { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
		.measures TD { border: 1px solid black; vertical-align: top; padding: 0; text-align: center;  font-size: 9pt; }
		.measures TD.ms_name { text-align: left; font-size: 10pt; }
		.measures TD.date { width:62px; }
		.measures TD.s_date { width:58px; }
		.measures TD.value { width:63px; }
		.measures TD.s_value { width:56px; }
		.measures TR.headers TD { font-size: 9pt; }
		
		.diseases { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
		.diseases TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
		.diseases TD.text_block { height: 3em; text-align:left; }
		
		.recommendations { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
		.recommendations TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
		.recommendations TD.spec_name { text-align: left; vertical-align: middle; height: 4em; }
		.recommendations TD.text_block { text-align: left; vertical-align: top; }
		
		.conclusion_tbl { border-collapse: collapse; margin-bottom: 2em; width:100%; }
		.conclusion_tbl TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; }
		.conclusion_tbl TD.text_block { padding: 0.5em; }
		.conclusion_tbl TD.left_panel { width: 6em; height: 10em; }
	</style>
</head>
<body>
<table class="pagetable">
	<tr class="1trhidden twopage"> <!-- page 1 -->
		<td class="page">
			&nbsp;
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page title">
			<div style="text-align:left;  padding-left:35%; margin-top:1em;">
				Приложение № 5</br>
				к приказу Министерства здравоохранения</br>
				и социального развития</br>
				Российской Федерации</br>
				 от                                            № 
			</div>
			<div  style="text-align:left;  padding-left:50%;  margin-top:2em; margin-bottom: 3em;">
				Медицинская документация</br>
				Учетная форма № 025/у-ПЗ</br>
				Утверждена приказом</br>
				Минздравсоцразвития  России</br>
				от                                            № 
			</div>
			<div style="font-weight: bolder; text-align:center;">
				Министерство здравоохранения и социального развития</br>
				Российской Федерации</br></br></br>
				ПАСПОРТ ЗДОРОВЬЯ</br></br></br></br>				
				<table class="struc title">
					<tr><td class="measure" style="width: 50px;">Фамилия</td><td class="underline">{person_surname}</td></tr>					
				</table>
				<table class="struc title">
					<tr><td class="measure" style="width: 50px;">Имя</td><td class="underline">{person_firname}</td></tr>					
				</table>
				<table class="struc title" style="margin-bottom:2em;">
					<tr><td class="measure" style="width: 50px;">Отчество</td><td class="underline">{person_secname}</td></tr>					
				</table>				
			</div>
		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 2 -->
		<td class="page">
			<div class="pagenumber">1</div>
				
			<table class="struc">
				<tr><td class="measure" style="width: 50px;">1.Ф.И.О.</td><td class="underline">{person_surname}&nbsp;{person_firname}&nbsp;{person_secname}</td></tr>
				<tr><td colspan="2" class="underline">&nbsp;</td></tr>
			</table>			
			<table class="struc">
				<tr>
					<td class="measure">2.Пол: муж., жен.</td>
					<td style="width: 105px;">3.Дата рождения:</td>
					<td style="width: 40px;" class="underline">{p_bd_m}</td>
					<td style="width: 5px; padding: 0px;">&nbsp;</td>
					<td style="width: 40px;" class="underline">{p_bd_d}</td>
					<td style="width: 5px; padding: 0px;">&nbsp;</td>
					<td style="width: 25px;" class="underline">{p_bd_y}</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
					<td class="ct u_text">месяц</td>
					<td style="padding: 0px;">&nbsp;</td>
					<td class="ct u_text">число</td>
					<td style="padding: 0px;">&nbsp;</td>
					<td class="ct u_text">год</td>
				</tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 50px;"><nobr>4.Адрес :</nobr></td><td class="underline">{p_a}</td></tr>
			</table>
			<table class="struc">
				<tr>
					<td class="measure" style="width: 20px;">ул.</td>
					<td class="underline">{p_a_st}</td>
					<td class="measure_rt" style="width: 26px;">дом</td>
					<td class="underline" style="width: 20px;">{p_a_h}</td>
					<td class="measure_rt" style="width: 32px;">корп</td>
					<td class="underline" style="width: 20px;">{p_a_c}</td>
					<td class="measure_rt" style="width: 15px;">кв</td>
					<td class="underline" style="width: 20px;">{p_a_fl}</td>
				</tr>
			</table>
			<table class="struc">
				<tr>
					<td class="measure" style="width: 115px;">5.Страховой полис:</td>
					<td class="measure_rt" style="width: 38px;">серия</td>
					<td class="underline" style="width: 60px;">&nbsp;</td>
					<td class="measure_rt" style="width: 15px;">№</td>
					<td class="underline">&nbsp;</td>
				</tr>
			</table>	
			<table class="struc">
				<tr><td rowspan="2" style="width: 9px; padding: 0px;">&nbsp;</td><td colspan="4" class="measure">наименование страховой медицинской организации</td></tr>
				<tr><td colspan="4" class="underline">&nbsp;</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 175px;"><nobr>6. Наблюдается поликлиникой</nobr></td><td class="underline">{dd_lpu}</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 145px;"><nobr>7. Телефон  поликлиники</nobr></td><td class="underline">{dd_lpu_phone}</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 280px;"><nobr>8.Медицинская карта амбулаторного больного №</nobr></td><td class="underline">&nbsp;</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure"><nobr>9. Ф.И.О. участкового врача терапевта (врача общей практики (семейного врача)</nobr></td></tr>
				<tr><td class="underline">&nbsp;</td></tr>
			</table>			
			<table class="struc" style="margin-top:2em;">			
				<tr><td colspan="2" class="ct" style="font-weight: bolder;">Сигнальные отметки</td></tr>
				<tr><td class="measure" style="width: 215px;">Группа и Rh-принадлежность крови :</td><td class="underline">{p_blood}&nbsp;{p_blood_rh}</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 190px;">Лекарственная непереносимость</td><td class="underline">{p_dead_drug}</td></tr>
				<tr><td style="padding:0px;">&nbsp;</td><td style="padding:0px;" class="ct u_text">(указать на какой препарат)</td></tr>
			</table>
			<table class="struc">
				<tr><td class="measure" style="width: 140px;">Аллергическая реакция</td><td class="underline">{p_allerg}</td></tr>
				<tr><td>&nbsp;</td><td class="ct u_text">(да/нет)</td></tr>
			</table>			
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">
			<div class="pagenumber">2</div>

			<table class="info">
				<tr><td colspan="5" class="table_header">Дополнительная  диспансеризация</td></tr>
				<tr><td rowspan="2" class="m" style="vertical-align: bottom; padding-bottom: 0.5em;">Наименование</td><td colspan="4">Годы (вписать)</td></tr>
				<tr><td class="y2">{ddyr1}</td><td class="y2">{ddyr2}</td><td class="y2">{ddyr3}</td><td class="y2">{ddyr4}</td></tr>
				<tr><td class="m">Дата</td><td>{dd1v1}</td><td>{dd1v2}</td><td>{dd1v3}</td><td>{dd1v4}</td></tr>
				<tr><td class="m">Группа cостояния здоровья *</td><td>{dd2v1}</td><td>{dd2v2}</td><td>{dd2v3}</td><td>{dd2v4}</td></tr>
				<tr><td class="m" style="height: 2.5em;">Подпись врача</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td colspan="5" class="m" style="border-style: none; padding-top:1.5em;">
						______________</br>
						*</br>
						I группа - практически здоров</br>
						II группа – риск развития заболевания, нуждается в проведении профилактических ме-роприятий.</br>
						III группа - нуждается в дополнительном обследовании для уточнения (установления) диагноза (впервые установленное хроническое заболевание) или лечении в амбулатор-ных условиях</br>
						IV группа - нуждающиеся в дополнительном обследовании и лечении в стационарных условиях;</br>
						V группа –имеет показания для оказания высокотехнологичной медицинской помощи (медицинская документация  направляется в орган исполнительной власти субъекта Российской Федерации в  сфере здравоохранения).
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 3 -->
		<td class="page">
		<div class="pagenumber">3</div>
		
			<table class="info">
				<tr><td colspan="6" class="table_header">Показатели состояния здоровья</td></tr>
				<tr><td rowspan="2">№<br/>пп</td><td rowspan="2" class="m">Наименование</td><td colspan="4">Годы (вписать)</td></tr>
				<tr><td class="y">{yr1}</td><td class="y">{yr2}</td><td class="y">{yr3}</td><td class="y">{yr4}</td></tr>
				<tr><td class="m">1</td><td class="m">Рост</td><td>{inf1v1}</td><td>{inf1v2}</td><td>{inf1v3}</td><td>{inf1v4}</td></tr>
				<tr><td class="m">2</td><td class="m">Вес</td><td>{inf2v1}</td><td>{inf2v2}</td><td>{inf2v3}</td><td>{inf2v4}</td></tr>
				<tr><td class="m">3</td><td class="m">Частота сердечных сокращений</td><td>{inf3v1}</td><td>{inf3v2}</td><td>{inf3v3}</td><td>{inf3v4}</td></tr>
				<tr><td class="m">4</td><td class="m">Артериальное давление (АД)</td><td>{inf4v1}</td><td>{inf4v2}</td><td>{inf4v3}</td><td>{inf4v4}</td></tr>
				<tr><td>&nbsp;</td><td class="m">Прочие показатели:</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td class="m">Подпись врача</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td colspan="6" style="border-style: none;">&nbsp;</td></tr>
				<tr><td colspan="6" class="table_header">Факторы риска развития социально-значимых заболеваний***</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td>{yr1}*</td><td>{yr2}</td><td>{yr3}</td><td>{yr4}</td></tr>
				<tr><td class="m">1</td><td class="m">Наследственность (ССЗ*, СД**, онкологические заболе-вания )</td><td>{inf5v1}</td><td>{inf5v2}</td><td>{inf5v3}</td><td>{inf5v4}</td></tr>
				<tr><td class="m">2</td><td class="m">Курение</td><td>{inf6v1}</td><td>{inf6v2}</td><td>{inf6v3}</td><td>{inf6v4}</td></tr>
				<tr><td class="m">3</td><td class="m">Избыточный вес</td><td>{inf7v1}</td><td>{inf7v2}</td><td>{inf7v3}</td><td>{inf7v4}</td></tr>
				<tr><td class="m">4</td><td class="m">Гиподинамия</td><td>{inf8v1}</td><td>{inf8v2}</td><td>{inf8v3}</td><td>{inf8v4}</td></tr>
				<tr><td class="m">5</td><td class="m">Стресс</td><td>{inf9v1}</td><td>{inf9v2}</td><td>{inf9v3}</td><td>{inf9v4}</td></tr>
				<tr><td class="m">6</td><td class="m">Повышенное АД</td><td>{inf10v1}</td><td>{inf10v2}</td><td>{inf10v3}</td><td>{inf10v4}</td></tr>
				<tr><td class="m">7</td><td class="m">Нерациональное питание</td><td>{inf11v1}</td><td>{inf11v2}</td><td>{inf11v3}</td><td>{inf11v4}</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td class="m">Подпись врача</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td colspan="6" class="m" style="border-style:none; padding-left: 0em; padding-top: 0.3em;">
						* после {yr1} г. - вписать<br/>
						*ССЗ – сердечно-сосудистые заболевания,<br/>
						**СД– сахарный диабет<br/>
						***отметить: есть, нет, не известно
					</td>
				</tr>
			</table>
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">			
			<div class="pagenumber">4</div>
			
			<table class="info" style="margin-bottom: 2em;">
				<tr><td colspan="3" class="table_header">Классификация артериальной гипертензии</td></tr>
				<tr>
					<td class="header measure">Показатели</td>
					<td class="header">АД</br>систолическое</td>
					<td class="header">АД диастолистоли-ческое</td>
				</tr>
				<tr><td class="measure">Оптимальное</td><td>&#60; 120</td><td>&#60;80</td></tr>
				<tr><td class="measure">Нормальное</td><td>120-129</td><td>80-84</td></tr>
				<tr><td class="measure">Высокое нормальное</td><td>130-139</td><td>85-89</td></tr>
				<tr><td colspan="3" class="header" style="height: 2em;">Артериальная гипертензия</td></tr>
				<tr><td class="measure">АГ I степени ("мягкая")</td><td>140-159</td><td>90-99</td></tr>
				<tr><td class="measure">АГ II степени ("умеренная")</td><td>160-179</td><td>100-109</td></tr>
				<tr><td class="measure">АГ III степени ("тяжелая")</td><td>&#62;=180</td><td>&#62;=110</td></tr>
				<tr><td class="measure">Изолированная систолическая гипертензия</td><td>&#62;=140</td><td>&#60;90</td></tr>
			</table>

			<table class="info">
				<tr><td>Норма сахара крови натощак</td><td>6,1 ммоль/л (Европейские рекоменда-</br>ции)</td></tr>
				<tr><td>Целевой уровень холестерина без<br/>КБС</td><td>менее 5 ммоль/л</td></tr>
				<tr><td colspan="2" style="font-weight:bolder; padding-top: 1em; border-style:none;">Расчет индекса массы тела (ИМТ):</td></tr>
				<tr>
					<td colspan="2" style="padding: 0.8em; padding-top: 0.1em; border-style:none;">						
						Вес (кг)<br/>
						ИМТ = ------------------------ = {imt}&nbsp;&nbsp;&nbsp;<br/>
						&nbsp;&nbsp;&nbsp;&nbsp;Рост (в метрах) в квадрате<br/>
					</td>
				</tr>
				<tr><td>норма</td><td>18,5-24,9</td></tr>
				<tr><td>предожирение</td><td>25-29,9</td></tr>
				<tr><td>ожирение I степени</td><td>30 – 34,9</td></tr>
				<tr><td>ожирение II степени</td><td>35 – 39,9</td></tr>
				<tr><td>ожирение III степени</td><td>40 и более</td></tr>
			</table>
		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 4 -->
		<td class="page">
			<div class="pagenumber">5</div>			
			<table class="measures">
				<tr>
					<td colspan="9" style="font-weight: bolder; height: 2em; font-size: 12pt; text-align:left; border-style:none;">Проведенные лабораторные исследования</td>
				</tr>
				<tr class="headers">
					<td>Наименование показателя</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>					
				</tr>
				<tr><td class="ms_name">Клинический анализ крови:</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td class="ms_name">- гемоглобин</td><td>{m1d1}</td><td>{m1v1}</td><td>{m1d2}</td><td>{m1v2}</td><td>{m1d3}</td><td>{m1v3}</td><td>{m1d4}</td><td>{m1v4}</td></tr>
				<tr><td class="ms_name">- лейкоциты</td><td>{m2d1}</td><td>{m2v1}</td><td>{m2d2}</td><td>{m2v2}</td><td>{m2d3}</td><td>{m2v3}</td><td>{m2d4}</td><td>{m2v4}</td></tr>
				<tr><td class="ms_name">- тромбоциты</td><td>{m3d1}</td><td>{m3v1}</td><td>{m3d2}</td><td>{m3v2}</td><td>{m3d3}</td><td>{m3v3}</td><td>{m3d4}</td><td>{m3v4}</td></tr>
				<tr><td class="ms_name">- СОЭ</td><td>{m4d1}</td><td>{m4v1}</td><td>{m4d2}</td><td>{m4v2}</td><td>{m4d3}</td><td>{m4v3}</td><td>{m4d4}</td><td>{m4v4}</td></tr>				
				<tr><td class="ms_name">Биохимический анализ крови:</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td class="ms_name">- исследование сахара крови</td><td>{m5d1}</td><td>{m5v1}</td><td>{m5d2}</td><td>{m5v2}</td><td>{m5d3}</td><td>{m5v3}</td><td>{m5d4}</td><td>{m5v4}</td></tr>
				<tr><td class="ms_name">- билирубин</td><td>{m6d1}</td><td>{m6v1}</td><td>{m6d2}</td><td>{m6v2}</td><td>{m6d3}</td><td>{m6v3}</td><td>{m6d4}</td><td>{m6v4}</td></tr>
				<tr><td class="ms_name">- общий белок сыворотки кро- ви</td><td>{m7d1}</td><td>{m7v1}</td><td>{m7d2}</td><td>{m7v2}</td><td>{m7d3}</td><td>{m7v3}</td><td>{m7d4}</td><td>{m7v4}</td></tr>
				<tr><td class="ms_name">- исследование уровня холесте- рина крови</td><td>{m8d1}</td><td>{m8v1}</td><td>{m8d2}</td><td>{m8v2}</td><td>{m8d3}</td><td>{m8v3}</td><td>{m8d4}</td><td>{m8v4}</td></tr>
				<tr><td class="ms_name">- амилаза</td><td>{m9d1}</td><td>{m9v1}</td><td>{m9d2}</td><td>{m9v2}</td><td>{m9d3}</td><td>{m9v3}</td><td>{m9d4}</td><td>{m9v4}</td></tr>
				<tr><td class="ms_name">- креатинин</td><td>{m10d1}</td><td>{m10v1}</td><td>{m10d2}</td><td>{m10v2}</td><td>{m10d3}</td><td>{m10v3}</td><td>{m10d4}</td><td>{m10v4}</td></tr>
				<tr><td class="ms_name">- исследование уровня  липо- протеидов низ- кой плотности</td><td>{m11d1}</td><td>{m11v1}</td><td>{m11d2}</td><td>{m11v2}</td><td>{m11d3}</td><td>{m11v3}</td><td>{m11d4}</td><td>{m11v4}</td></tr>
				<tr><td class="ms_name">- исследование уровня тригли- церидов сыво- ротки крови</td><td>{m12d1}</td><td>{m12v1}</td><td>{m12d2}</td><td>{m12v2}</td><td>{m12d3}</td><td>{m12v3}</td><td>{m12d4}</td><td>{m12v4}</td></tr>
				<tr><td class="ms_name">- мочевая кисло- та</td><td>{m13d1}</td><td>{m13v1}</td><td>{m13d2}</td><td>{m13v2}</td><td>{m13d3}</td><td>{m13v3}</td><td>{m13d4}</td><td>{m13v4}</td></tr>
			</table>
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">
			<div class="pagenumber">6</div>

			<table class="measures">
				<tr>
					<td colspan="9" style="font-weight: bolder; height: 2em; font-size: 12pt; text-align:right; border-style:none;">продолжение таблицы</td>
				</tr>
				<tr class="headers">
					<td>Наименование показателя</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>
					<td class="date">дата</td>
					<td class="value">значения</td>					
				</tr>
				<tr><td class="ms_name">Клинический анализ мочи:</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td class="ms_name">- белок</td><td>{m14d1}</td><td>{m14v1}</td><td>{m14d2}</td><td>{m14v2}</td><td>{m14d3}</td><td>{m14v3}</td><td>{m14d4}</td><td>{m14v4}</td></tr>
				<tr><td class="ms_name">- сахар</td><td>{m15d1}</td><td>{m15v1}</td><td>{m15d2}</td><td>{m15v2}</td><td>{m15d3}</td><td>{m15v3}</td><td>{m15d4}</td><td>{m15v4}</td></tr>
				<tr><td class="ms_name">- лейкоциты</td><td>{m16d1}</td><td>{m16v1}</td><td>{m16d2}</td><td>{m16v2}</td><td>{m16d3}</td><td>{m16v3}</td><td>{m16d4}</td><td>{m16v4}</td></tr>
				<tr><td class="ms_name">- эритроциты</td><td>{m17d1}</td><td>{m17v1}</td><td>{m17d2}</td><td>{m17v2}</td><td>{m17d3}</td><td>{m17v3}</td><td>{m17d4}</td><td>{m17v4}</td></tr>
				<tr><td class="ms_name">Онкмаркер спе- цифический СА-125</td><td>{m18d1}</td><td>{m18v1}</td><td>{m18d2}</td><td>{m18v2}</td><td>{m18d3}</td><td>{m18v3}</td><td>{m18d4}</td><td>{m18v4}</td></tr>
				<tr><td class="ms_name">Онкмаркер спе- цифический PSA</td><td>{m19d1}</td><td>{m19v1}</td><td>{m19d2}</td><td>{m19v2}</td><td>{m19d3}</td><td>{m19v3}</td><td>{m19d4}</td><td>{m19v4}</td></tr>
				<tr><td class="ms_name">Цитология маз- ка из церви-кального канала</td><td>{m20d1}</td><td>{m20v1}</td><td>{m20d2}</td><td>{m20v2}</td><td>{m20d3}</td><td>{m20v3}</td><td>{m20d4}</td><td>{m20v4}</td></tr>
			</table>
		
			<table class="measures">
				<tr>
					<td colspan="9" style="font-weight: bolder; height: 2em; font-size: 12pt; text-align:center; border-style:none;">Проведенные функциональные исследования</td>
				</tr>
				<tr class="headers">
					<td>Наименование показателя</td>
					<td class="s_date">дата</td>
					<td class="s_value">значения</td>
					<td class="s_date">дата</td>
					<td class="s_value">значения</td>
					<td class="s_date">дата</td>
					<td class="s_value">значения</td>
					<td class="s_date">дата</td>
					<td class="s_value">значения</td>
				</tr>
				<tr><td class="ms_name">Электрокардиография</td><td>{m21d1}</td><td>{m21v1}</td><td>{m21d2}</td><td>{m21v2}</td><td>{m21d3}</td><td>{m21v3}</td><td>{m21d4}</td><td>{m21v4}</td></tr>
				<tr><td class="ms_name">Флюорография</td><td>{m22d1}</td><td>{m22v1}</td><td>{m22d2}</td><td>{m22v2}</td><td>{m22d3}</td><td>{m22v3}</td><td>{m22d4}</td><td>{m22v4}</td></tr>
				<tr><td class="ms_name">Маммография</td><td>{m23d1}</td><td>{m23v1}</td><td>{m23d2}</td><td>{m23v2}</td><td>{m23d3}</td><td>{m23v3}</td><td>{m23d4}</td><td>{m23v4}</td></tr>
			</table>
		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 5 -->
		<td class="page">
			<div class="pagenumber">7</div>

			<table class="diseases">
				<tr>
					<td colspan="3" style="font-weight: bolder; height:2em; border-style: none;">
						Заболевания, выявленные в ходе дополнительной диспансеризации
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:7em;">Дата установле-<br/>ния</td>
					<td>{year1}</td>
					<td rowspan="2" style="width:4.2em;">Код по<br/>МКБ- X</td>
				</tr>
				<tr>
					<td>Наименование заболевания</td>
				</tr>
				<tr><td>{ds1_date1}</td><td class="text_block">{ds1_name1}</td><td>{ds1_cd1}</td></tr>
				<tr><td>{ds1_date2}</td><td class="text_block">{ds1_name2}</td><td>{ds1_cd2}</td></tr>
				<tr><td>{ds1_date3}</td><td class="text_block">{ds1_name3}</td><td>{ds1_cd3}</td></tr>
				<tr><td>{ds1_date4}</td><td class="text_block">{ds1_name4}</td><td>{ds1_cd4}</td></tr>
				<tr><td>{ds1_date5}</td><td class="text_block">{ds1_name5}</td><td>{ds1_cd5}</td></tr>
			</table>

			<table class="diseases">
				<tr>
					<td colspan="3" style="font-weight: bolder; height:2em; border-style: none;">
						Заболевания, выявленные в ходе дополнительной диспансеризации
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:7em;">Дата установле-<br/>ния</td>
					<td>{year2}</td>
					<td rowspan="2" style="width:4.2em;">Код по<br/>МКБ- X</td>
				</tr>
				<tr>
					<td>Наименование заболевания</td>
				</tr>
				<tr><td>{ds2_date1}</td><td class="text_block">{ds2_name1}</td><td>{ds2_cd1}</td></tr>
				<tr><td>{ds2_date2}</td><td class="text_block">{ds2_name2}</td><td>{ds2_cd2}</td></tr>
				<tr><td>{ds2_date3}</td><td class="text_block">{ds2_name3}</td><td>{ds2_cd3}</td></tr>
				<tr><td>{ds2_date4}</td><td class="text_block">{ds2_name4}</td><td>{ds2_cd4}</td></tr>
				<tr><td>{ds2_date5}</td><td class="text_block">{ds2_name5}</td><td>{ds2_cd5}</td></tr>
			</table>
			
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">
			<div class="pagenumber">8</div>

			<table class="diseases">
				<tr>
					<td colspan="3" style="font-weight: bolder; height:2em; border-style: none;">
						Заболевания, выявленные в ходе дополнительной диспансеризации
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:7em;">Дата установле-<br/>ния</td>
					<td>{year3}</td>
					<td rowspan="2" style="width:4.2em;">Код по<br/>МКБ- X</td>
				</tr>
				<tr>
					<td>Наименование заболевания</td>
				</tr>
				<tr><td>{ds3_date1}</td><td class="text_block">{ds3_name1}</td><td>{ds3_cd1}</td></tr>
				<tr><td>{ds3_date2}</td><td class="text_block">{ds3_name2}</td><td>{ds3_cd2}</td></tr>
				<tr><td>{ds3_date3}</td><td class="text_block">{ds3_name3}</td><td>{ds3_cd3}</td></tr>
				<tr><td>{ds3_date4}</td><td class="text_block">{ds3_name4}</td><td>{ds3_cd4}</td></tr>
				<tr><td>{ds3_date5}</td><td class="text_block">{ds3_name5}</td><td>{ds3_cd5}</td></tr>
			</table>

			<table class="diseases">
				<tr>
					<td colspan="3" style="font-weight: bolder; height:2em; border-style: none;">
						Заболевания, выявленные в ходе дополнительной диспансеризации
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:7em;">Дата установле-<br/>ния</td>
					<td>{year4}</td>
					<td rowspan="2" style="width:4.2em;">Код по<br/>МКБ- X</td>
				</tr>
				<tr>
					<td>Наименование заболевания</td>
				</tr>
				<tr><td>{ds4_date1}</td><td class="text_block">{ds4_name1}</td><td>{ds4_cd1}</td></tr>
				<tr><td>{ds4_date2}</td><td class="text_block">{ds4_name2}</td><td>{ds4_cd2}</td></tr>
				<tr><td>{ds4_date3}</td><td class="text_block">{ds4_name3}</td><td>{ds4_cd3}</td></tr>
				<tr><td>{ds4_date4}</td><td class="text_block">{ds4_name4}</td><td>{ds4_cd4}</td></tr>
				<tr><td>{ds4_date5}</td><td class="text_block">{ds4_name5}</td><td>{ds4_cd5}</td></tr>
			</table>
			
		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 6 -->
		<td class="page">
			<div class="pagenumber">9</div>
			
			<table class="recommendations">
				<tr>
					<td colspan="4" style="font-weight: bolder; height:2em; border-style: none;">Заключение (рекомендации)  врачей-специалистов</td>
				</tr>	
				<tr>
					<td style="width: 8em;">Врач-специалист</td>
					<td style="width: 3em;">{year1}</td>
					<td>Заключение (рекомендации)</td>
					<td style="width: 3.7em;">Подпись</td>
				</tr>				
				<tr>
					<td class="spec_name">Акушер-гинеколог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_1_spec1}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Невролог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_1_spec2}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Офтальмолог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_1_spec3}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Хирург</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_1_spec4}</td>
					<td>&nbsp;</td>
				</tr>
			</table>

			<table class="recommendations">
				<tr>
					<td colspan="4" style="font-weight: bolder; height:2em; border-style: none;">Заключение (рекомендации)  врачей-специалистов</td>
				</tr>	
				<tr>
					<td style="width: 8em;">Врач-специалист</td>
					<td style="width: 3em;">{year2}</td>
					<td>Заключение (рекомендации)</td>
					<td style="width: 3.7em;">Подпись</td>
				</tr>				
				<tr>
					<td class="spec_name">Акушер-гинеколог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_2_spec1}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Невролог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_2_spec2}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Офтальмолог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_2_spec3}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Хирург</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_2_spec4}</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			
		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">
			<div class="pagenumber">10</div>

			<table class="recommendations">
				<tr>
					<td colspan="4" style="font-weight: bolder; height:2em; border-style: none;">Заключение (рекомендации)  врачей-специалистов</td>
				</tr>	
				<tr>
					<td style="width: 8em;">Врач-специалист</td>
					<td style="width: 3em;">{year3}</td>
					<td>Заключение (рекомендации)</td>
					<td style="width: 3.7em;">Подпись</td>
				</tr>				
				<tr>
					<td class="spec_name">Акушер-гинеколог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_3_spec1}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Невролог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_3_spec2}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Офтальмолог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_3_spec3}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Хирург</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_3_spec4}</td>
					<td>&nbsp;</td>
				</tr>
			</table>

			<table class="recommendations">
				<tr>
					<td colspan="4" style="font-weight: bolder; height:2em; border-style: none;">Заключение (рекомендации)  врачей-специалистов</td>
				</tr>	
				<tr>
					<td style="width: 8em;">Врач-специалист</td>
					<td style="width: 3em;">{year4}</td>
					<td>Заключение (рекомендации)</td>
					<td style="width: 3.7em;">Подпись</td>
				</tr>				
				<tr>
					<td class="spec_name">Акушер-гинеколог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_4_spec1}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Невролог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_4_spec2}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Офтальмолог</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_4_spec3}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="spec_name">Хирург</td>
					<td>&nbsp;</td>
					<td class="text_block">{recommendations_4_spec4}</td>
					<td>&nbsp;</td>
				</tr>
			</table>

		</td>
	</tr>
	<tr class="1trhidden twopage"> <!-- page 7 -->
		<td class="page">
			<div class="pagenumber">11</div>
			
			<table class="conclusion_tbl">
				<tr>
					<td colspan="2" style="border: none; text-align: center; font-weight: bolder;">
						Заключение  (рекомендации) врача-терапевта участкового
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="height: 4.2em; text-align: center; font-weight: bolder;">
						группа со-<br/>стояния<br/>здоровья
					</td>
					<td style="text-align: center;">{year1}</td>
				</tr>
				<tr>
					<td rowspan="2" class="text_block">{recommendations_1_spec5}</td>
				</tr>
				<tr>
					<td class="left_panel">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-right-style: none;">Дата</td>
					<td style="border-left-style: none; padding-left: 4.5em;">
						Подпись врача-</br>терапевта участкового (врача общей практики)
					</td>
				</tr>
			</table>
			
			<table class="conclusion_tbl">
				<tr>
					<td colspan="2" style="border: none; text-align: center; font-weight: bolder;">
						Заключение  (рекомендации) врача-терапевта участкового
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="height: 4.2em; text-align: center; font-weight: bolder;">
						группа со-<br/>стояния<br/>здоровья
					</td>
					<td style="text-align: center;">{year2}</td>
				</tr>
				<tr>
					<td rowspan="2" class="text_block">{recommendations_2_spec5}</td>
				</tr>
				<tr>
					<td class="left_panel">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-right-style: none;">Дата</td>
					<td style="border-left-style: none; padding-left: 4.5em;">
						Подпись врача-</br>терапевта участкового (врача общей практики)
					</td>
				</tr>
			</table>

		</td>
		<td class="page_separator">&nbsp;</td>
		<td class="page">
			<div class="pagenumber">&nbsp;</div>
			
			<table class="conclusion_tbl">
				<tr>
					<td colspan="2" style="border: none; text-align: center; font-weight: bolder;">
						Заключение  (рекомендации) врача-терапевта участкового
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="height: 4.2em; text-align: center; font-weight: bolder;">
						группа со-<br/>стояния<br/>здоровья
					</td>
					<td style="text-align: center;">{year3}</td>
				</tr>
				<tr>
					<td rowspan="2" class="text_block">{recommendations_3_spec5}</td>
				</tr>
				<tr>
					<td class="left_panel">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-right-style: none;">Дата</td>
					<td style="border-left-style: none; padding-left: 4.5em;">
						Подпись врача-</br>терапевта участкового (врача общей практики)
					</td>
				</tr>
			</table>
			
			<table class="conclusion_tbl">
				<tr>
					<td colspan="2" style="border: none; text-align: center; font-weight: bolder;">
						Заключение  (рекомендации) врача-терапевта участкового
					</td>
				</tr>
				<tr>
					<td rowspan="2" style="height: 4.2em; text-align: center; font-weight: bolder;">
						группа со-<br/>стояния<br/>здоровья
					</td>
					<td style="text-align: center;">{year4}</td>
				</tr>
				<tr>
					<td rowspan="2" class="text_block">{recommendations_4_spec5}</td>
				</tr>
				<tr>
					<td class="left_panel">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-right-style: none;">Дата</td>
					<td style="border-left-style: none; padding-left: 4.5em;">
						Подпись врача-</br>терапевта участкового (врача общей практики)
					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>	
</body>
</html>