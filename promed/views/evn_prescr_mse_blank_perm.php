<html>
<head>
	<title>Направление на МСЭ</title>
	<style>
		h4 { text-align: center; font-weight: normal }
		mark { background: #eee; }
		p { padding: 0; margin: 0 }
		.clarification { font-size: 9pt; text-align: center; }
	</style>
	
</head>

<body>

<!-- /*NO PARSE JSON*/ -->
<table width="100%">
	<tr>
		<td width="60%"></td>
		<td>Приложение к Приказу Министерства здравоохранения и социального развития Российской Федерации от 31 января 2007 г. № 77 (в ред. Приказа Минздравсоцразвития РФ от 28.10.2009 № 853н)</td>
	</tr>
</table>

<h4>Медицинская документация Форма № 088/у-06<br />Министерство здравоохранения и социального развития Российской Федерации</h4>

<p align="center" style="font-size: 9pt;"><mark>{Lpu}</mark><br />
(Наименование и адрес организации, оказывающей лечебно-профилактическую помощь)</p>

<h4 style="font-weight: bold">НАПРАВЛЕНИЕ НА МЕДИКО-СОЦИАЛЬНУЮ ЭКСПЕРТИЗУ ОРГАНИЗАЦИЕЙ, ОКАЗЫВАЮЩЕЙ<br /> ЛЕЧЕБНО-ПРОФИЛАКТИЧЕСКУЮ ПОМОЩЬ</h4>

<p style="padding: 5px;">
	Дата выдачи: <mark>{EvnPrescrMse_issueDT}</mark><br />
	1. Фамилия, имя, отчество гражданина, направляемого на медико-социальную экспертизу (далее – гражданин):
	<p align="center"><mark>{Person_Fio}</mark></p>
	
	2. Дата рождения: <mark>{Person_BirthDay}</mark>
	
	3. Пол: <mark>{PersonSex}</mark><br />
	
	4. Фамилия, имя, отчество законного представителя гражданина (заполняется при наличии законного представителя):<br />
	<p align="center"><mark>{Person_Fio2}</mark></p>
	5. Адрес места жительства гражданина (при отсутствии места жительства указывается адрес пребывания, фактического проживания на территории Российской Федерации):<br />
	<p align="center"><mark>{Address_Address}</mark></p>
	
	<p name="parag">6. Инвалидом не является, инвалид первой, второй, третьей группы, категория «ребенок-инвалид» (нужное подчеркнуть).</p>
	
	7. Исключен.<br />
	8. Степень утраты профессиональной трудоспособности в процентах: <mark>{EvnPrescrMse_InvalidPercent}</mark><br />
	<p class="clarification">(заполняется при повторном направлении)</p>
	
	<p name="parag">9. Направляется первично, повторно (нужное подчеркнуть).</p>
	
	10. Кем работает на момент направления на медико-социальную экспертизу:<br />
	<mark>{post} {prof} {spec} {skill}</mark><br />
	<p class="clarification">(указать должность, профессию, специальность, квалификацию и стаж работы
	по указанной должности, профессии, специальности, квалификации; в отношении
	неработающих граждан сделать запись: «не работает»)</p>
	
	11. Наименование и адрес организации, в которой работает гражданин:
	<p align="center"><mark>{Org1}</mark></p>
	
	12. Условия и характер выполняемого труда:
	<p align="center"><mark>{EvnPrescrMse_CondWork}</mark></p>
	
	13. Основная профессия (специальность):
	<p align="center"><mark>{EvnPrescrMse_MainProf}</mark></p>
	
	14. Квалификация по основной профессии (класс, разряд, категория, звание): <mark>{EvnPrescrMse_MainProfSkill}</mark><br />
	
	15. Наименование и адрес образовательного учреждения:
	<p align="center"><mark>{Org2}</mark></p>
	
	<p name="parag">16. Группа, класс, курс (указываемое подчеркнуть): <mark>{EvnPrescrMse_Dop}</mark></p>
	
	17. Профессия (специальность), для получения которой проводится обучение:
	<p align="center"><mark>{EvnPrescrMse_ProfTraining}</mark></p>
	
	18. Наблюдается в организациях, оказывающих лечебно-профилактическую помощь, с <mark>{EvnPrescrMse_OrgMedDate}</mark> года.<br />
	
	19. История заболевания (начало, развитие, течение, частота и длительность обострений,
	проведенные лечебно-оздоровительные и реабилитационные мероприятия и их эффективность):
	<p align="center"><mark>{EvnPrescrMse_DiseaseHist}</mark></p>
	<p class="clarification">(подробно описывается при первичном направлении, при повторном направлении
	отражается динамика за период между освидетельствованиями, детально описываются
	выявленные в этот период новые случаи заболеваний, приведшие к стойким нарушениям функций организма)</p>
	
	20. Анамнез жизни (перечисляются перенесенные в прошлом заболевания, травмы, отравления,
	операции, заболевания, по которым отягощена наследственность, дополнительно в отношении
	ребенка указывается, как протекали беременность и роды у матери, сроки формирования
	психомоторных навыков, самообслуживания, познавательно-игровой деятельности, навыков
	опрятности и ухода за собой, как протекало раннее развитие (по возрасту, с отставанием, с опережением):
	<p align="center"><mark>{EvnPrescrMse_LifeHist}</mark></p>
	<p class="clarification">(заполняется при первичном направлении)</p>
	
	<br />
	21. Частота и длительность временной нетрудоспособности (сведения за последние 12 месяцев):
	<table style="width: 100%; text-align: center;" border="1" cellspacing="0">
		<tr>
			<td width="5%">№</td>
			<td width="15%">Дата (число, месяц, год) начала временной нетрудоспособности</td>
			<td width="15%">Дата (число, месяц, год) окончания временной нетрудоспособности</td>
			<td width="15%">Число дней (месяцев и дней) временной нетрудоспособности</td>
			<td width="50%">Диагноз</td>
		</tr>
		{sticks}
		<tr>
			<td>{number}</td>
			<td>{EvnStick_setDate}</td>
			<td>{EvnStick_disDate}</td>
			<td>{DayCount}</td>
			<td>{Diag_Name}</td>
		</tr>
		{/sticks}
	</table>
	<br />
	
	22. Результаты проведенных мероприятий по медицинской реабилитации в соответствии с
	индивидуальной программой реабилитации инвалида (заполняется при повторном направлении,
	указываются  конкретные виды восстановительной терапии, реконструктивной хирургии,
	санаторно-курортного лечения, технических средств медицинской реабилитации, в том числе
	протезирования и ортезирования, а также сроки, в которые они были предоставлены; перечисляются
	функции организма, которые удалось компенсировать или восстановить полностью или частично, либо
	делается отметка, что положительные результаты отсутствуют):
	<table style="width: 100%; text-align: center;" border="1" cellspacing="0">
		<tr>
			<td width="20%">Наименование</td>
			<td width="15%">Дата начала</td>
			<td width="15%">Дата окончания</td>
			<td width="50%">Результат</td>
		</tr>
		{MeasuresRehabMSE}
		<tr>
			<td>{MeasuresRehabMSE_Name}</td>
			<td>{MeasuresRehabMSE_BegDate}</td>
			<td>{MeasuresRehabMSE_EndDate}</td>
			<td>{MeasuresRehabMSE_Result}</td>
		</tr>
		{/MeasuresRehabMSE}
	</table>
	<br />
	
	23. Состояние гражданина при направлении на медико-социальную экспертизу
	(указываются жалобы, данные осмотра лечащим врачом и врачами других специальностей):
	<p align="center"><mark>{EvnPrescrMse_State}</mark></p>
	
	24. Результаты дополнительных методов исследования (указываются результаты проведенных лабораторных,
	рентгенологических, эндоскопических, ультразвуковых, психологических, функциональных и других видов исследований):
	<p align="center"><mark>{EvnPrescrMse_DopRes}</mark></p>
	
	25. Масса тела (кг) <mark>{PersonWeight_Weight}</mark>, рост (м) <mark>{PersonHeight_Height}</mark>, индекс массы тела <mark>{idxWeight}</mark>.<br />
	
	<p name="parag">26. Оценка физического развития: нормальное, отклонение (дефицит массы тела, избыток массы тела, низкий рост, высокий рост) (нужное подчеркнуть).</p>
	
	<p name="parag">27. Оценка психофизиологической выносливости: норма, отклонение (нужное подчеркнуть).</p>
	
	<p name="parag">28. Оценка эмоциональной устойчивости: норма, отклонение (нужное подчеркнуть).</p>
	
	29. Диагноз при направлении на медико-социальную экспертизу:<br />
	а) код основного заболевания по МКБ: <mark>{Diag1_Code}</mark><br />
	б) основное заболевание: <mark>{diag1_FullName}, {EvnPrescrMse_MainDisease}</mark><br />
	в) сопутствующие заболевания: <mark>{diag2_FullName}</mark><br />
	г) осложнения: <mark>{diag3_FullName}</mark><br />
	
	<p name="parag">30. Клинический прогноз: благоприятный, относительно благоприятный, сомнительный (неопределенный), неблагоприятный (нужное подчеркнуть).</p>
	
	<p name="parag">31. Реабилитационный потенциал: высокий, удовлетворительный, низкий (нужное подчеркнуть).</p>
	
	<p name="parag">32. Реабилитационный прогноз: благоприятный, относительно благоприятный, сомнительный (неопределенный), неблагоприятный (нужное подчеркнуть).</p>

    <p>33. Цель направления на медико-социальную экспертизу (нужное подчеркнуть): {MseDirectionAimType_id}</p>
    <p align="center"><mark>{EvnPrescrMse_AimMseOver}</mark></p>
	
	34. Рекомендуемые мероприятия по медицинской реабилитации для формирования или коррекции индивидуальной
	программы реабилитации инвалида (ребенка-инвалида), программы реабилитации пострадавшего в результате
	несчастного случая и профессионального заболевания:
	<p align="center"><mark>{EvnPrescrMse_Recomm}</mark></p>
	<p class="clarification">(указываются конкретные виды восстановительной терапии (включая лекарственное обеспечение при лечении
	заболевания, ставшего причиной инвалидности), реконструктивной хирургии (включая лекарственное обеспечение
	при лечении заболевания, ставшего причиной инвалидности),технических средств медицинской реабилитации,
	в том числе протезирования и ортезирования, заключение о санаторно-курортном лечении с предписанием профиля,
	кратности, срока и сезона рекомендуемого лечения, о нуждаемости в специальном медицинском уходе лиц,
	пострадавших в результате несчастных случаев на производстве и профессиональных заболеваний, о нуждаемости в
	лекарственных средствах для лечения последствий несчастных случаев на производстве и профессиональных
	заболеваний, другие виды медицинской реабилитации)</p>
</p>

<br />

<table width="100%">
	<tr>
		<td style="text-align: left;" width="40%">Председатель врачебной комиссии:</td>
		<td width="20%"></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align: center; font-size: 9pt;">________________<br />(подпись)</td>
		<td style="text-align: center; font-size: 9pt;"><font style="text-decoration: underline"> {vkchairman} </font><br />(расшифровка подписи)</td>
	</tr>
</table>

<br />

<table width="100%">
	<tr>
		<td style="text-align: left;" width="40%">Члены врачебной комиссии:</td>
		<td width="20%"></td>
		<td></td>
	</tr>
	
	{vkexperts}
	<tr>
		<td></td>
		<td style="text-align: center; font-size: 9pt;">________________<br />(подпись)<br /><br /></td>
		<td style="text-align: center; font-size: 9pt;"><font style="text-decoration: underline"> {MF_Person_FIO} </font><br />(расшифровка подписи)<br /><br /></td>
	</tr>
	{/vkexperts}
</table>

М.П.

<script type="text/javascript">
	(function(){
		// Находим параграфы где нужно подчеркнуть текст
		var parag = document.getElementsByName('parag'),
			html,
			values = [
				{InvalidGroupType_id}, // группа инв.
				{EvnPrescrMse_IsFirstTime}, // направляется [первично/повторно]
				{LearnGroupType_id}, // [группа/класс/курс]
				[
					{WeightAbnormType_id}, // избыток/дефицит массы тела
					{HeightAbnormType_id} // высокий/низкий рост
				],
				{StateNormType_id}, // Оценка психофизиологической выносливости
				{StateNormType_did}, // Оценка эмоциональной устойчивости
				{ClinicalForecastType_id}, // Клинический прогноз
				{ClinicalPotentialType_id}, // Реабилитационный потенциал
				{ClinicalForecastType_did} // Реабилитационный прогноз
			];
			
		// щаз самый гемор (_|_)
		switch(values[0]){
			case 1:	values[0] = 'Инвалидом не является'; break;
			case 2:	values[0] = 'инвалид первой'; break;
			case 3:	values[0] = 'второй'; break;
			case 4:	values[0] = 'третьей группы'; break;
			case 5:	values[0] = 'категория «ребенок-инвалид»'; break;
		}
		switch(values[1]){
			case 1:	values[1] = 'первично'; break;
			case 2:	values[1] = 'повторно'; break;
		}
		switch(values[2]){
			case 1:	values[2] = 'Группа'; break;
			case 2:	values[2] = 'класс'; break;
			case 3:	values[2] = 'курс'; break;
		}
		switch(values[3][0]){
			case 1: values[3][0] = 'дефицит массы тела'; break;
			case 2: values[3][0] = 'избыток массы тела'; break;
		}
		switch(values[3][1]){
			case 1: values[3][1] = 'низкий рост'; break;
			case 2: values[3][1] = 'высокий рост'; break;
		}
		switch(values[6]){
			case 1: values[6] = 'благоприятный'; break;
			case 2: values[6] = 'относительно благоприятный'; break;
			case 3: values[6] = 'сомнительный (неопределенный)'; break;
			case 4: values[6] = 'неблагоприятный'; break;
		}
		switch(values[7]){
			case 1: values[7] = 'высокий'; break;
			case 2: values[7] = 'удовлетворительный'; break;
			case 3: values[7] = 'низкий'; break;
		}
		switch(values[8]){
			case 1: values[8] = 'благоприятный'; break;
			case 2: values[8] = 'относительно благоприятный'; break;
			case 3: values[8] = 'сомнительный (неопределенный)'; break;
			case 4: values[8] = 'неблагоприятный'; break;
		}
		//var rxp;
		for(var i=0; i<parag.length; i++) {
			for(var j=0; j<values.length; j++) {
				if(i==j) {
					html = parag[i].innerHTML;
					if(typeof values[j] != 'object') {
						if(typeof values[j] != 'undefined') {
							html = html.replace(new RegExp(values[j],'i'), '<u>'+values[j]+'</u>');
						}
					} else {
						for(var z=0; z<values[j].length; z++) {
							if ( typeof values[j][z] != 'undefined' )
								html = html.replace(new RegExp(values[j][z],'gi'), '<u>'+values[j][z]+'</u>');
						}
					}
					parag[i].innerHTML = html;
				}
			}
		}
	})();
</script>

</body>
</html>