<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar').style.visibility='visible'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar').style.visibility='hidden'">

	<div style="float: right;">
		<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar" class="toolbar" style="visibility: hidden">
			<a id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
		</div>
	</div>

	<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_content">

		<table style="border:solid 1px black;border-collapse:collapse;width:100%;font-size:8px">
			<tr style="border-bottom:solid 1px black">
				<td style="width:60%;text-align:center;">
					Қазақстан Республикасы Денсаулық сақтау Министрлігі
					<br />Министерство здравоохранения Республики Казахстан
				</td>
				<td style="width:40%;border-left:solid 1px black;padding-left:5px;">
					Қазақстан Республикасы  Денсаулық сақтау министрінің м.а. 2010 
					<br />жылғы «23» қарашадағы №907 бұйрығымен бекітілген
					<br />№ 027-1/е нысанды медициналық құжаттама
				</td>
			</tr>
			<tr>
				<td style="width:60%"></td>
				<td style="width:40%;border-left:solid 1px black;padding-left:5px;">
					Медицинская документация  Форма 027-1/у 
					<br />утверждена приказом и.о. Министра здравоохранения 
					<br />Республики Казахстан от «23» ноября 2010 года № 907
				</td>
			</tr>
		</table>
		
		<p style="text-align: center; font-weight: bold;">Қатерлі ісікпен ауырған стационарлық науқастың медициналық картасынан
			<br /><span style="font-size:14px;">К Ө Ш І Р М Е
			<br />ВЫПИСКА</span>
			<br />из медицинской карты стационарного больного злокачественным новообразованием
			<br />(барлық стационарларда толтырылады – заполняется всеми стационарами)
		<br /></p>

<?php
$Ethnos_Name = empty($Ethnos_Name) ? str_repeat('_', 15) : "<b>{$Ethnos_Name}</b>";
if(!empty($Sex_Name)){
	switch ($Sex_Name) {
		case 'Мужской':
			$Sex = '<span style="font-weight:bold;text-decoration:underline;">Е -1</span>, Ә -2';
			break;
		case 'Женский':
			$Sex = 'Е -1, <span style="font-weight:bold;text-decoration:underline;">Ә -2</span>';
			break;
		default:
			$Sex = 'Е -1, Ә -2';
			break;
	}
} else {
	$Sex = 'Е -1, Ә -2';
}
if(!empty($IsFirst_Name)){
	switch ($IsFirst_Name) {
		case 'Да':
			$IsOnkoFirst_kz = '<span style="font-weight:bold;text-decoration:underline;">ИӘ - 1</span>, ЖОҚ – 2';
			$IsOnkoFirst = '<span style="font-weight:bold;text-decoration:underline;">Да - 1</span>, Нет – 2';
			break;
		case 'Нет':
			$IsOnkoFirst_kz = 'ИӘ - 1, <span style="font-weight:bold;text-decoration:underline;">ЖОҚ – 2</span>';
			$IsOnkoFirst = 'Да - 1, <span style="font-weight:bold;text-decoration:underline;">Нет – 2</span>';
			break;
		default:
			$IsOnkoFirst_kz = 'ИӘ - 1, ЖОҚ – 2';
			$IsOnkoFirst = 'Да - 1, Нет – 2';
			break;
	}
} else {
	$IsOnkoFirst_kz = 'ИӘ - 1, ЖОҚ – 2';
	$IsOnkoFirst = 'Да - 1, Нет – 2';
}
$Person_Address = empty($Person_PAddress) ? str_repeat('_', 75) : "<b>{$Person_PAddress}</b>";
$EvnSection_disDate = empty($EvnSection_disDate) ? str_repeat('_', 49) : "<b>{$EvnSection_disDate}</b>";
$ProveDiag_Type = array('морфологиялық (морфологически) – 1',  'цитологиялық (цитологически) – 2',  
			'рентгенологиялық (рентгенологически) – 3',  'эндоскопиялық (эндоскопически) – 4', 'изотоптық әдіспен (изотопным методом) – 5', 
			'тек клиникалық (только клинически) - 6', 'ИГХ - 7', 'миелограмма (миелограмма) - 8', 'иммунофенотиптеу (иммунофенотипирование) - 9', 
			'иммунологиялық (иммунологический) - 10', 'цитогенетика (цитогенетика) - 11', 'цитохимия (цитохимия) - 12', 
			'Қан/несептегі М-гардиент (М-градиент в крови/моче) - 13', 'Көпше иеломадағы Lg (Lg пр множественной миеломе) - 14', 'рентгенография (рентгенография) - 15');
if(!empty($OnkoDiagConfType_Code)){
	switch ($OnkoDiagConfType_Code) {
		case '1':
			$ProveDiag_Type[0] = '<span style="font-weight:bold;text-decoration:underline;">'.$ProveDiag_Type[0].'</span>';
			break;
		case '2':
			$ProveDiag_Type[1] = '<span style="font-weight:bold;text-decoration:underline;">'.$ProveDiag_Type[1].'</span>';
			break;
		case '5':
			$ProveDiag_Type[5] = '<span style="font-weight:bold;text-decoration:underline;">'.$ProveDiag_Type[5].'</span>';
			break;
		default:
			# code...
			break;
	}
}
$ProveDiag_Type = implode(", ", $ProveDiag_Type);
$OnkoDiag_Name = empty($OnkoDiag_Name) ? str_repeat('_', 45) : "<b>{$OnkoDiag_Name}</b>";
$SpecTreatSetDT = empty($SpecTreatSetDT) ? str_repeat('_', 43) : "<b>{$SpecTreatSetDT}</b>";
$JobOrgName = empty($JobOrgName) ? str_repeat('_', 78) : "<b>{$JobOrgName}</b>";
$JobPostName = empty($JobPostName) ? str_repeat('_', 87) : "<b>{$JobPostName}</b>";

$OnkoOccupationClass_Name = empty($OnkoOccupationClass_Name) ? str_repeat('_', 49) : "<b>{$OnkoOccupationClass_Name}</b>";

$OnkoPurposeHospType_Name = empty($OnkoPurposeHospType_Name) ? str_repeat('_', 72) : "<b>{$OnkoPurposeHospType_Name}</b>";
$EvnDiagPsSopList = empty($EvnDiagPsSopList) ? str_repeat('_', 90) : "<b>{$EvnDiagPsSopList}</b>";
$TumorPrimaryTreatType_Name = empty($TumorPrimaryTreatType_Name) ? str_repeat('_', 35) : "<b>{$TumorPrimaryTreatType_Name}</b>";
$TumorRadicalTreatIncomplType_Name = empty($TumorRadicalTreatIncomplType_Name) ? str_repeat('_', 90) : "<b>{$TumorRadicalTreatIncomplType_Name}</b>";
$EvnUslugaOnkoSurg_setDate = empty($EvnUslugaOnkoSurg_setDate) ? '' : " Дата операции: <b>{$EvnUslugaOnkoSurg_setDate}</b>";
$Operation_NameL = empty($Operation_Name) ? '' : " Название операции <b>{$Operation_Name}</b>";
$SurgOslList = empty($SurgOslList) ? str_repeat('_', 90) : "<b>{$SurgOslList}</b>";
$EvnUslugaOnkoBeam_setDate = empty($EvnUslugaOnkoBeam_setDate) ? '' : " Дата начала курса лучевой терапии <b>{$EvnUslugaOnkoBeam_setDate}</b>";
$OnkoUslugaBeamIrradiationType_Name = empty($OnkoUslugaBeamIrradiationType_Name) ? '' : " Способ облучения <b>{$OnkoUslugaBeamIrradiationType_Name}</b>";
$OnkoUslugaBeamKindType_Name = empty($OnkoUslugaBeamKindType_Name) ? '' : " Вид лучевой терапии <b>{$OnkoUslugaBeamKindType_Name}</b>";
$OnkoUslugaBeamMethodType_Name = empty($OnkoUslugaBeamMethodType_Name) ? '' : " Методы лучевой терапии <b>{$OnkoUslugaBeamMethodType_Name}</b>";
$OnkoUslugaBeamRadioModifType_Name = empty($OnkoUslugaBeamRadioModifType_Name) ? '' : " Радиомодификаторы, применявшиеся при проведении лучевой терапии <b>{$OnkoUslugaBeamRadioModifType_Name}</b>";
$OnkoUslugaBeamFocusType_Name = empty($OnkoUslugaBeamFocusType_Name) ? '' : " Поля облучения <b>{$OnkoUslugaBeamFocusType_Name}</b>";
$TotalDoseTumor_Unit = empty($TotalDoseTumor_Unit) ? 'Гр' : "<b>{$TotalDoseTumor_Unit}</b>";
$EvnUslugaOnkoBeam_TotalDoseTumor = empty($EvnUslugaOnkoBeam_TotalDoseTumor) ? '' : " Суммарная доза на опухоль <b>{$EvnUslugaOnkoBeam_TotalDoseTumor}</b> (".$TotalDoseTumor_Unit.")";
$TotalDoseRegZone_Unit = empty($TotalDoseRegZone_Unit) ? 'Гр' : "<b>{$TotalDoseRegZone_Unit}</b>";
$EvnUslugaOnkoBeam_TotalDoseRegZone = empty($EvnUslugaOnkoBeam_TotalDoseRegZone) ? '' : " Суммарная доза на зоны регионарного метастазирования <b>{$EvnUslugaOnkoBeam_TotalDoseRegZone}</b> (".$TotalDoseRegZone_Unit.")";
$BeamOslList = empty($BeamOslList) ? str_repeat('_', 90) : "<b>{$BeamOslList}</b>";
$EvnUslugaOnkoChem_setDate = empty($EvnUslugaOnkoChem_setDate) ? '' : " Дата начала курса химиотерапии: <b>{$EvnUslugaOnkoChem_setDate}</b>";
$OnkoUslugaChemKindType_Name = empty($OnkoUslugaChemKindType_Name) ? '' : " Вид химиотерапии: <b>{$OnkoUslugaChemKindType_Name}</b>";
$OnkoDrugChemList = empty($OnkoDrugChemList) ? '' : " Препараты, суммарные дозы: <b>{$OnkoDrugChemList}</b>";
$ChemOslList = empty($ChemOslList) ? str_repeat('_', 90) : "<b>{$ChemOslList}</b>";
$CombTreat = str_repeat('_', 105).'</p><p>'.str_repeat('_', 105);
$ComplexTreat = str_repeat('_', 66).'</p><p>'.str_repeat('_', 105);
if(!empty($EvnUslugaOnkoSurg_setDate)&&!empty($EvnUslugaOnkoBeam_setDate)&&empty($EvnUslugaOnkoChem_setDate)){
	$CombTreat = $EvnUslugaOnkoSurg_setDate.$Operation_NameL.$EvnUslugaOnkoBeam_setDate.$OnkoUslugaBeamIrradiationType_Name.$OnkoUslugaBeamKindType_Name.$OnkoUslugaBeamMethodType_Name; 
	$CombTreat .= $OnkoUslugaBeamRadioModifType_Name.$OnkoUslugaBeamFocusType_Name.$EvnUslugaOnkoBeam_TotalDoseTumor.$EvnUslugaOnkoBeam_TotalDoseRegZone;
	$EvnUslugaOnkoSurg_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoBeam_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoChem_setDate = str_repeat('_', 105);
	$Operation_NameL = '';
	$OnkoUslugaBeamIrradiationType_Name = '';
	$OnkoUslugaBeamKindType_Name = '';
	$OnkoUslugaBeamMethodType_Name = '';
	$OnkoUslugaBeamRadioModifType_Name = '';
	$OnkoUslugaBeamFocusType_Name = '';
	$EvnUslugaOnkoBeam_TotalDoseTumor = '';
	$EvnUslugaOnkoBeam_TotalDoseRegZone = '';
} else if((!empty($EvnUslugaOnkoSurg_setDate)||!empty($EvnUslugaOnkoBeam_setDate))&&!empty($EvnUslugaOnkoChem_setDate)){
	$ComplexTreat = $EvnUslugaOnkoSurg_setDate.$Operation_NameL.$EvnUslugaOnkoBeam_setDate.$OnkoUslugaBeamIrradiationType_Name.$OnkoUslugaBeamKindType_Name.$OnkoUslugaBeamMethodType_Name; 
	$ComplexTreat .= $OnkoUslugaBeamRadioModifType_Name.$OnkoUslugaBeamFocusType_Name.$EvnUslugaOnkoBeam_TotalDoseTumor.$EvnUslugaOnkoBeam_TotalDoseRegZone;
	$ComplexTreat .= $EvnUslugaOnkoChem_setDate.$OnkoUslugaChemKindType_Name.$OnkoDrugChemList;
	$EvnUslugaOnkoSurg_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoBeam_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoChem_setDate = str_repeat('_', 105);
	$Operation_NameL = '';
	$OnkoUslugaBeamIrradiationType_Name = '';
	$OnkoUslugaBeamKindType_Name = '';
	$OnkoUslugaBeamMethodType_Name = '';
	$OnkoUslugaBeamRadioModifType_Name = '';
	$OnkoUslugaBeamFocusType_Name = '';
	$EvnUslugaOnkoBeam_TotalDoseTumor = '';
	$EvnUslugaOnkoBeam_TotalDoseRegZone = '';
	$OnkoUslugaChemKindType_Name = '';
	$OnkoDrugChemList = '';
} else if(!empty($EvnUslugaOnkoSurg_setDate)){
	$EvnUslugaOnkoBeam_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoChem_setDate = str_repeat('_', 105);
} else if(!empty($EvnUslugaOnkoBeam_setDate)){
	$EvnUslugaOnkoSurg_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoChem_setDate = str_repeat('_', 105);
} else if(!empty($EvnUslugaOnkoChem_setDate)){
	$EvnUslugaOnkoBeam_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoSurg_setDate = str_repeat('_', 105);
} else {
	$EvnUslugaOnkoSurg_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoBeam_setDate = str_repeat('_', 105);
	$EvnUslugaOnkoChem_setDate = str_repeat('_', 105);
}
$Operation_Name = empty($Operation_Name) ? str_repeat('_', 105) : "<b>{$Operation_Name}</b>";
?>
		<p>Көшірмені берген ұйымның мекен-жайы (Адрес организации, выдавшего выписку) <b>{Lpu_Address}</b></p>
		<p>Көшірме жолданған ұйымның атауы мен мекен-жайы (Название и адрес организации, куда направляется выписка) <?php echo str_repeat('_', 105); ?></p>
		<p>Қатерлі ісік диагнозы өмірінде бірінші рет қойылды: <?php echo $IsOnkoFirst_kz; ?> (Диагноз злокачественного новообразования установлен впервые в жизни: <?php echo $IsOnkoFirst; ?>)</p>
		<p>Науқастың тегі, аты, әкесінің аты (Ф.И.О. больного) <b>{Person_SurName} {Person_FirName} {Person_SecName}</b></p>
		<p style="margin-bottom:0;">Жынысы (Пол): <?php echo $Sex; ?>; &nbsp;&nbsp;&nbsp;Ұлты (Национальность) <?php echo $Ethnos_Name; ?>  Туған күні (Дата рождения) <b>{Person_BirthDay}</b></p>
		<p style="font-size:10px;text-align:right;margin:0;padding-right:15px;">Күні, айы, жылы</p>
		<p>Жұмыс орны (Место работы) <?php echo $JobOrgName; ?></p>
		<p>Кәсібі (Профессия) <?php echo $JobPostName; ?></p>
		<p>Науқастың мекен-жайы (толық) (Адрес больного (полностью)) <?php echo $Person_Address; ?></p>
		<p style="margin-bottom:0;">Стационарға түскен күні (Дата поступления в стационар) <b>{EvnSection_setDate}</b></p>
		<p style="font-size:10px;text-align:right;margin:0;padding-right:15px;">Күні, айы, жылы</p>
		<p style="margin-bottom:0;">Шыққан немесе қайтыс болған күні (Дата выписки или смерти) <?php echo $EvnSection_disDate; ?></p>
		<p style="font-size:10px;text-align:right;margin:0;padding-right:15px;">Күні, айы, жылы</p>
		<p>Стационарда болу ұзақтығы (күнмен) (Длительность нахождения в стационаре (в днях)) <b>{EvnSection_Day}</b></p>
		<p>Арнаулы емдеудің  басталған күні (Дата начала специального лечения) <?php echo $SpecTreatSetDT; ?></p>
		<p>Қорытынды диагноз (Заключительный диагноз) <b>{Diag_FullName}</b> <br />сатысы (стадия) <b>{TumorStage_Name}</b></p>
		<p>Диагноздың расталуы (Диагноз подтвержден): <?php echo $ProveDiag_Type; ?>.</p>
		<p>(С81-96) – нұсқалықты көрсету (указать вариантность):⁭-L1; ⁭-L2; ⁭-L3; ⁭-L4; ⁭-L5; ⁭-M0; ⁭-M1; ⁭-M2; ⁭-M3; ⁭-M4; ⁭-M5; ⁭-M6; ⁭-M7</p>
		<p>(С81-96) – қауіп тобы (группа риска): 1- стандартты (стандартный);   2 –жоғары (высокая);  24.9 – резистілік    (резистентность):1-бірінші (первичная); 2-екінші (вторичная).</p>
		<p>Ісіктің гистологиялық құрылысы (Гистологическая структура опухоли) <?php echo $OnkoDiag_Name; ?></p>
		<p>Емдеу (Лечение): радикалды (радикальное) – 1, паллиативті (паллиативное) – 2 <?php echo $TumorPrimaryTreatType_Name; ?></p>
		<p style="margin-bottom:0;">1. Тек хирургиялық (Только хирургическое) <?php echo $EvnUslugaOnkoSurg_setDate; echo $Operation_NameL; ?></p>
		<p style="font-size:10px;text-align:right;margin:0;padding-right:15px;">операция күні, аты, көлемі (дата операции, название и объем)</p>
		<p style="margin-bottom:0;">2. Тек сәулелік (Только лучевое) 
			<?php echo $EvnUslugaOnkoBeam_setDate; 
			echo $OnkoUslugaBeamIrradiationType_Name; 
			echo $OnkoUslugaBeamKindType_Name; 
			echo $OnkoUslugaBeamMethodType_Name; 
			echo $OnkoUslugaBeamRadioModifType_Name;
			echo $OnkoUslugaBeamFocusType_Name;
			echo $EvnUslugaOnkoBeam_TotalDoseTumor;
			echo $EvnUslugaOnkoBeam_TotalDoseRegZone;?>
		</p>
		<p style="font-size:10px;text-align:right;margin:0;padding-right:15px;">әдістемесі, қолданылу кезегі (методика, последовательность применения),</p>
		<p style="margin-bottom:0;"><?php echo str_repeat('_', 105); ?></p>
		<p style="font-size:10px;text-align:center;margin:0;padding-right:15px;">сәулелеудің әр түрлері үшін дозасын жеке көрсетіңіз (доза раздельно для различных видов облучения)</p>
		<p>а) қашықтықтық гамматерапия (дистанционная гамматерапия)<?php echo str_repeat('_', 51); ?></p>
		<p>б) рентген терапиясы (рентгенотерапия)<?php echo str_repeat('_', 70); ?></p>
		<p>в) жылдам электрондар (быстрые электроны)<?php echo str_repeat('_', 66); ?></p>
		<p>г) біріктірілген (сочетанное): 1 – түйісуші және қашықтықтық гамматерапия (контактная и дистанционная гамматерапия) <?php echo str_repeat('_', 105); ?></p>
		<p>д) 2 - түйісуші гамматерапия мен терең рентген терапиясы (контактная гамматерапия и глубокая рентгенотерапия) <?php echo str_repeat('_', 105); ?></p>
		<p>3. Аралас (Комбинированное): операция күні мен оның сипаты, сәулелеу әдістемесі мен түрі, қолданылу кезегі, сәулелеудің әр түрлері үшін дозасын жеке көрсетіңіз 
			(дата операции и ее характер, методика и вид облучения, последовательность применения, доза раздельно для каждого вида облучения) <?php echo $CombTreat; ?></p>
		<p>а) хирургиялық және гамматерапия (хирургическое и гамматерапия)<?php echo str_repeat('_', 46); ?></p>
		<p>б) хирургиялық және рентген терапиясы (хирургическое и рентгенотерапия)<?php echo str_repeat('_', 39); ?></p>
		<p>в) хирургиялық және біріктірілген сәулелік (хирургическое и сочетанное лучевое)<?php echo str_repeat('_', 35); ?></p>
		<p>4. Тек химиятерапиялық (Только химиотерапевтическое):дәрілердің атауы, дозалары (название лекарств, дозы) 
			<?php echo $EvnUslugaOnkoChem_setDate; 
				echo $OnkoUslugaChemKindType_Name;
				echo $OnkoDrugChemList;?>
		</p>
		<p>4.1.С91-95.9) үшін химиятерапия бойынша емдеу сатылары (Этапы  лечения по химиотерапии для:  (С91-95.9)) а) индукция (индукция);  б) консолидация (консолидация);  
			в) реиндукция (реиндукция); г) қолдаушы терапия (поддерживающая терапия);  д)  қайталануға қарсы курс (противорецидивный курс);  е) симтоматикалық терапия (симптоматическая терапия).</p>
		<p>4.2.В гепатитінің бар болуы (Наличие гепатита В):    ⁭ - химия терапияға дейін (до химиотерапии); ⁭-химия терапия кезінде (На фоне химиотерапии); ⁭-химия терапиядан кейін (После химиотерапии)
		С гепатитінің бар болуы (Наличие гепатита С):    ⁭ - химия терапияға дейін (до химиотерапии); ⁭-химия терапия кезінде (На фоне химиотерапии); ⁭-химия терапиядан кейін (После химиотерапии)
		тек гормондармен (только гормональное)</p>
		<p>5. Комплекстік емдеу (Комплексное лечение) <?php echo $ComplexTreat; ?></p>
		<p>6. Жүргізілген ем (операция түрі, көлемі) (Проведенное лечение: вид операции, объем) <?php echo $Operation_Name; ?></p>
		<p>7. Емдеудің басқа түрлері (Другие виды лечения) <?php echo str_repeat('_', 105); ?></p>
		<p>8. Ұсыныстар (Рекомендации) <?php echo str_repeat('_', 105); ?></p>
		<p>Дәрігердің тегі, аты, әкесінің аты мен қолы (Ф.И.О. и подпись врача) <b>{MedPersonal_Fin}</b> _______________</p>
		<p>«__»_______________ 20__ж.</p>
		<p>
			<br />
			<br />
			<br />Көшірме науқастың мекен-жайы бойынша онкологиялық диспансерге (бөлмеге) жіберіледі
			<br />Выписка пересылается в онкологический диспансер (кабинет) по месту жительства больного
		</p>
	</div>
</div>