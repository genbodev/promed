<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div class="left">
	<div id="EvnPLDispProf_data_{EvnPLDispProf_id}">
		<div class="caption">
			<h2>{DispClass_Name}</h2>
		</div>
		<div class="text">
			<p>Автор документа: {AuthorInfo}</p>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид оплаты: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputPayType" style='color:#000;' class="link" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<!-- Информированное добровольное согласие -->
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата подписания согласия/отказа: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputConsDate" style='color:#000;' class="link" dataid='{EvnPLDispProf_consDate}'><?php } echo empty($EvnPLDispProf_consDate)?$empty_str:'{EvnPLDispProf_consDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaConsDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			{DopDispInfoConsent}
			<!-- Маршрутная карта -->
			{EvnUslugaDispDop}
			<!-- Ранее известные имеющиеся заболевания -->
			{EvnDiagDopDispBefore}
			<!-- Наследственность по заболеваниям -->
			{HeredityDiag}
			<!-- Подозрение на заболевания, медицинские показания к обследованиям -->
			<div class="data-table">
				<div class="caption">
					<h2><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_toggleDisplayDataList" class="collapsible">
						Подозрение на заболевания, медицинские показания к обследованиям
					</span></h2>
				</div>
				<div id="EvnPLDispProfDataListContent_{EvnPLDispProf_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">Подозрение на наличие стенокардии напряжения: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsStenocard" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsStenocard}'><?php } echo empty($EvnPLDispProf_IsStenocard)?$empty_str:(($EvnPLDispProf_IsStenocard == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsStenocard" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Показания к проведению дуплексного сканирования брахицефальных артерий: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsDoubleScan" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsDoubleScan}'><?php } echo empty($EvnPLDispProf_IsDoubleScan)?$empty_str:(($EvnPLDispProf_IsDoubleScan == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsDoubleScan" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsTub" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsTub}'><?php } echo empty($EvnPLDispProf_IsTub)?$empty_str:(($EvnPLDispProf_IsTub == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsTub" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Показания к проведению эзофагогастродуоденоскопии: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsEsophag" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsEsophag}'><?php } echo empty($EvnPLDispProf_IsEsophag)?$empty_str:(($EvnPLDispProf_IsEsophag == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsEsophag" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>
			<!-- Показания к углублённому профилактическому консультированию -->
			{ProphConsult}
			<!-- Поведенческие факторы риска -->
			<div class="data-table">
				<div class="caption">
					<h2><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_toggleDisplayDataList2" class="collapsible">
						Поведенческие факторы риска
					</span></h2>
				</div>
				<div id="EvnPLDispProfDataList2Content_{EvnPLDispProf_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">Курение: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsSmoking" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsSmoking}'><?php } echo empty($EvnPLDispProf_IsSmoking)?$empty_str:(($EvnPLDispProf_IsSmoking == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsSmoking" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Риск пагубного потребления алкоголя: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsRiskAlco" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsRiskAlco}'><?php } echo empty($EvnPLDispProf_IsRiskAlco)?$empty_str:(($EvnPLDispProf_IsRiskAlco == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsRiskAlco" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на зависимость от алкоголя: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsAlcoDepend" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsAlcoDepend}'><?php } echo empty($EvnPLDispProf_IsAlcoDepend)?$empty_str:(($EvnPLDispProf_IsAlcoDepend == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsAlcoDepend" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Низкая физическая активность: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsLowActiv" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsLowActiv}'><?php } echo empty($EvnPLDispProf_IsLowActiv)?$empty_str:(($EvnPLDispProf_IsLowActiv == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsLowActiv" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нерациональное питание: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsIrrational" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsIrrational}'><?php } echo empty($EvnPLDispProf_IsIrrational)?$empty_str:(($EvnPLDispProf_IsIrrational == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsIrrational" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>
			<!-- Впервые выявленные заболевания -->
			{EvnDiagDopDispFirst}
			<!-- Значения параметров, потенциальных или имеющихся биологических факторов риска -->
			<div class="data-table">
				<div class="caption">
					<h2><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_toggleDisplayDataList3" class="collapsible">
						Значения параметров, потенциальных или имеющихся биологических факторов риска
					</span></h2>
				</div>
				<div id="EvnPLDispProfDataList3Content_{EvnPLDispProf_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">АД (мм рт.ст.): <?php echo empty($systolic_blood_pressure) && empty($diastolic_blood_pressure)?$empty_str:'{systolic_blood_pressure} / {diastolic_blood_pressure}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гипотензивная терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsHypoten" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsHypoten}'><?php } echo empty($EvnPLDispProf_IsHypoten)?$empty_str:(($EvnPLDispProf_IsHypoten == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsHypoten" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Вес (кг): <?php echo empty($person_weight)?$empty_str:'{person_weight}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Рост (см): <?php echo empty($person_height)?$empty_str:'{person_height}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Окружность талии (см): <?php echo empty($waist_circumference)?$empty_str:'{waist_circumference}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Индекс массы тела (кг/м2): <?php echo empty($body_mass_index)?$empty_str:'{body_mass_index}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Риск сердечно-сосудистых заболеваний: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputCardioRiskType" style='color:#000;' class="link" dataid='{EvnPLDispProf_CardioRiskType}'><?php } echo empty($CardioRiskType_Name)?$empty_str:'{CardioRiskType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaCardioRiskType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Общий холестерин (ммоль/л): <?php echo empty($total_cholesterol)?$empty_str:'{total_cholesterol}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гиполипидемическая терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsLipid" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsLipid}'><?php } echo empty($EvnPLDispProf_IsLipid)?$empty_str:(($EvnPLDispProf_IsLipid == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsLipid" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Глюкоза (ммоль/л): <?php echo empty($glucose)?$empty_str:'{glucose}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гипогликемическая терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsHypoglyc" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsHypoglyc}'><?php } echo empty($EvnPLDispProf_IsHypoglyc)?$empty_str:(($EvnPLDispProf_IsHypoglyc == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsHypoglyc" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на хроническое неинфекционное заболевание, требующее дообследования: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputDiag" style='color:#000;' class="link" dataid='{EvnPLDispProf_Diag}'><?php } echo empty($Diag_Name)?$empty_str:'{Diag_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaDiag" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Взят на диспансерное наблюдение: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsDisp" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsDisp}'><?php } echo empty($EvnPLDispProf_IsDisp)?$empty_str:(($EvnPLDispProf_IsDisp == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsDisp" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нуждается в дополнительном лечении (обследовании): <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputNeedDopCure" style='color:#000;' class="link" dataid='{NeedDopCure_id}'><?php } echo empty($NeedDopCure_Name)?$empty_str:'{NeedDopCure_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaNeedDopCure" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нуждается в санаторно-курортном лечении: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsSanator" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsSanator}'><?php } echo empty($EvnPLDispProf_IsSanator)?$empty_str:(($EvnPLDispProf_IsSanator == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsSanator" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Суммарный сердечно-сосудистый риск, процент (%): <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputSumRick" style='color:#000;' class="link" dataid='{EvnPLDispProf_SumRick}'><?php } echo empty($EvnPLDispProf_SumRick)?$empty_str:'{EvnPLDispProf_SumRick}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaSumRick" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Суммарный сердечно-сосудистый риск, тип риска: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputRiskType" style='color:#000;' class="link" dataid='{RiskType_id}'><?php } echo empty($RiskType_Name)?$empty_str:'{RiskType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaRiskType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Школа пациента проведена: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsSchool" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsSchool}'><?php } echo empty($EvnPLDispProf_IsSchool)?$empty_str:(($EvnPLDispProf_IsSchool == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsSchool" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Углубленное профилактическое консультирование проведено: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsProphCons" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsSchool}'><?php } echo empty($EvnPLDispProf_IsProphCons)?$empty_str:(($EvnPLDispProf_IsProphCons == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsProphCons" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>

			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Группа здоровья: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputHealthKind" style='color:#000;' class="link" dataid='{HealthKind_id}'><?php } echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaHealthKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Случай профосмотра закончен: <?php if($is_allow_edit) { ?><span id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputIsEndStage" style='color:#000;' class="link" dataid='{EvnPLDispProf_IsEndStage}'><?php } echo ($EvnPLDispProf_IsEndStage == 2)?'Да':'Нет'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispProf_data_{EvnPLDispProf_id}_inputareaIsEndStage" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
		</div>
	</div>
</div>