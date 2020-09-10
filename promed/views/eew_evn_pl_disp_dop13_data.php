<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div class="left">
	<div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}">
		<div class="caption">
			<h2>{DispClass_Name}</h2>
		</div>
		<div class="text">
			<p>Автор документа: {AuthorInfo}</p>
			<div class="data-row-container"><div class="data-row">Вид оплаты: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputPayType" style='color:#000;' class="link" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<!-- Информированное добровольное согласие -->
			<div class="data-row-container"><div class="data-row">Дата подписания согласия/отказа: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputConsDate" style='color:#000;' class="link" dataid='{EvnPLDispDop13_consDate}'><?php } echo empty($EvnPLDispDop13_consDate)?$empty_str:'{EvnPLDispDop13_consDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaConsDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div class="data-row-container"><div class="data-row">Случай обслужен мобильной бригадой: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsMobile" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsMobile}'><?php } echo empty($EvnPLDispDop13_IsMobile)?$empty_str:(($EvnPLDispDop13_IsMobile == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsMobile" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div class="data-row-container"><div class="data-row">МО мобильной бригады: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputLpuM" style='color:#000;' class="link" dataid='{Lpu_mid}'><?php } echo empty($Lpu_mName)?$empty_str:'{Lpu_mName}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaLpuM" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
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
					<h2><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_toggleDisplayDataList" class="collapsible">
						Подозрение на заболевания, медицинские показания к обследованиям
					</span></h2>
				</div>
				<div id="EvnPLDispDop13DataListContent_{EvnPLDispDop13_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">Подозрение на наличие стенокардии напряжения: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsStenocard" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsStenocard}'><?php } echo empty($EvnPLDispDop13_IsStenocard)?$empty_str:(($EvnPLDispDop13_IsStenocard == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsStenocard" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на ранее перенесенное нарушение мозгового кровообращения: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsBrain" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsBrain}'><?php } echo empty($EvnPLDispDop13_IsBrain)?$empty_str:(($EvnPLDispDop13_IsBrain == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsBrain" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Показания к проведению дуплексного сканирования брахицефальных артерий: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsDoubleScan" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsDoubleScan}'><?php } echo empty($EvnPLDispDop13_IsDoubleScan)?$empty_str:(($EvnPLDispDop13_IsDoubleScan == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsDoubleScan" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsTub" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsTub}'><?php } echo empty($EvnPLDispDop13_IsTub)?$empty_str:(($EvnPLDispDop13_IsTub == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsTub" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Показания к проведению эзофагогастродуоденоскопии: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsEsophag" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsEsophag}'><?php } echo empty($EvnPLDispDop13_IsEsophag)?$empty_str:(($EvnPLDispDop13_IsEsophag == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsEsophag" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на некоторые инфекционные и паразитарные болезни: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputDiagS" style='color:#000;' class="link" dataid='{Diag_sid}'><?php } echo empty($Diag_sName)?$empty_str:'{Diag_sName}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaDiagS" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>
			<!-- Показания к углублённому профилактическому консультированию -->
			{ProphConsult}
			<!-- Показания к консультации врача-специалиста -->
			{NeedConsult}
			<!-- Поведенческие факторы риска -->
			<div class="data-table">
				<div class="caption">
					<h2><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_toggleDisplayDataList2" class="collapsible">
						Поведенческие факторы риска
					</span></h2>
				</div>
				<div id="EvnPLDispDop13DataList2Content_{EvnPLDispDop13_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">Курение: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsSmoking" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsSmoking}'><?php } echo empty($EvnPLDispDop13_IsSmoking)?$empty_str:(($EvnPLDispDop13_IsSmoking == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsSmoking" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Риск пагубного потребления алкоголя: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsRiskAlco" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsRiskAlco}'><?php } echo empty($EvnPLDispDop13_IsRiskAlco)?$empty_str:(($EvnPLDispDop13_IsRiskAlco == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsRiskAlco" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на зависимость от алкоголя: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsAlcoDepend" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsAlcoDepend}'><?php } echo empty($EvnPLDispDop13_IsAlcoDepend)?$empty_str:(($EvnPLDispDop13_IsAlcoDepend == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsAlcoDepend" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Низкая физическая активность: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsLowActiv" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsLowActiv}'><?php } echo empty($EvnPLDispDop13_IsLowActiv)?$empty_str:(($EvnPLDispDop13_IsLowActiv == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsLowActiv" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нерациональное питание: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsIrrational" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsIrrational}'><?php } echo empty($EvnPLDispDop13_IsIrrational)?$empty_str:(($EvnPLDispDop13_IsIrrational == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsIrrational" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>
			<!-- Впервые выявленные заболевания -->
			{EvnDiagDopDispFirst}
			<!-- Значения параметров, потенциальных или имеющихся биологических факторов риска -->
			<div class="data-table">
				<div class="caption">
					<h2><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_toggleDisplayDataList3" class="collapsible">
						Значения параметров, потенциальных или имеющихся биологических факторов риска
					</span></h2>
				</div>
				<div id="EvnPLDispDop13DataList3Content_{EvnPLDispDop13_id}" style="display: block;">
					<div class="data-row-container"><div class="data-row">Проведено индивидуальное краткое профилактическое консультирование: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsShortCons" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsShortCons}'><?php } echo empty($EvnPLDispDop13_IsShortCons)?$empty_str:(($EvnPLDispDop13_IsShortCons == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsShortCons" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">АД (мм рт.ст.): <?php echo empty($systolic_blood_pressure) && empty($diastolic_blood_pressure)?$empty_str:'{systolic_blood_pressure} / {diastolic_blood_pressure}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гипотензивная терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsHypoten" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsHypoten}'><?php } echo empty($EvnPLDispDop13_IsHypoten)?$empty_str:(($EvnPLDispDop13_IsHypoten == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsHypoten" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Вес (кг): <?php echo empty($person_weight)?$empty_str:'{person_weight}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Рост (см): <?php echo empty($person_height)?$empty_str:'{person_height}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Окружность талии (см): <?php echo empty($waist_circumference)?$empty_str:'{waist_circumference}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Индекс массы тела (кг/м2): <?php echo empty($body_mass_index)?$empty_str:'{body_mass_index}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Риск сердечно-сосудистых заболеваний: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputCardioRiskType" style='color:#000;' class="link" dataid='{EvnPLDispDop13_CardioRiskType}'><?php } echo empty($CardioRiskType_Name)?$empty_str:'{CardioRiskType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaCardioRiskType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Общий холестерин (ммоль/л): <?php echo empty($total_cholesterol)?$empty_str:'{total_cholesterol}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гиполипидемическая терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsLipid" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsLipid}'><?php } echo empty($EvnPLDispDop13_IsLipid)?$empty_str:(($EvnPLDispDop13_IsLipid == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsLipid" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Глюкоза (ммоль/л): <?php echo empty($glucose)?$empty_str:'{glucose}'; ?></div></div>
					<div class="data-row-container"><div class="data-row">Гипогликемическая терапия: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsHypoglyc" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsHypoglyc}'><?php } echo empty($EvnPLDispDop13_IsHypoglyc)?$empty_str:(($EvnPLDispDop13_IsHypoglyc == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsHypoglyc" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Подозрение на хроническое неинфекционное заболевание, требующее дообследования: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputDiag" style='color:#000;' class="link" dataid='{EvnPLDispDop13_Diag}'><?php } echo empty($Diag_Name)?$empty_str:'{Diag_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaDiag" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Взят на диспансерное наблюдение: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsDisp" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsDisp}'><?php } echo empty($EvnPLDispDop13_IsDisp)?$empty_str:(($EvnPLDispDop13_IsDisp == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsDisp" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нуждается в дополнительном лечении (обследовании): <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputNeedDopCure" style='color:#000;' class="link" dataid='{NeedDopCure_id}'><?php } echo empty($NeedDopCure_Name)?$empty_str:'{NeedDopCure_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaNeedDopCure" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Нуждается в санаторно-курортном лечении: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsSanator" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsSanator}'><?php } echo empty($EvnPLDispDop13_IsSanator)?$empty_str:(($EvnPLDispDop13_IsSanator == 2)?'Да':'Нет'); if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsSanator" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Суммарный сердечно-сосудистый риск, процент (%): <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputSumRick" style='color:#000;' class="link" dataid='{EvnPLDispDop13_SumRick}'><?php } echo empty($EvnPLDispDop13_SumRick)?$empty_str:'{EvnPLDispDop13_SumRick}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaSumRick" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
					<div class="data-row-container"><div class="data-row">Суммарный сердечно-сосудистый риск, тип риска: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputRiskType" style='color:#000;' class="link" dataid='{RiskType_id}'><?php } echo empty($RiskType_Name)?$empty_str:'{RiskType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaRiskType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				</div>
			</div>

			<div class="data-row-container"><div class="data-row">Группа здоровья: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputHealthKind" style='color:#000;' class="link" dataid='{HealthKind_id}'><?php } echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaHealthKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div class="data-row-container"><div class="data-row">Случай диспансеризации <?php echo $DispClass_id == 2 ? '2' : '1'; ?> этап закончен: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsEndStage" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsEndStage}'><?php } echo ($EvnPLDispDop13_IsEndStage == 2)?'Да':'Нет'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsEndStage" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<?php
				if ( $DispClass_id == 1 ) {
			?>
			<div class="data-row-container"><div class="data-row">Направлен на 2 этап диспансеризации: <?php if($is_allow_edit) { ?><span id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputIsTwoStage" style='color:#000;' class="link" dataid='{EvnPLDispDop13_IsTwoStage}'><?php } echo ($EvnPLDispDop13_IsTwoStage == 2)?'Да':'Нет'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispDop13_data_{EvnPLDispDop13_id}_inputareaIsTwoStage" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<?php
				}
			?>
		</div>
	</div>
</div>