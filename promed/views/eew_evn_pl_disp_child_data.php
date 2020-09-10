<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
?>
<div id="EvnPLDispChild_{EvnPLDisp_id}">

	<div id="EvnPLDispChild_wrap" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispChild_{EvnPLDisp_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispChild_{EvnPLDisp_id}_toolbar').style.display='none'">
		<div id="EvnPLDispChild_{EvnPLDisp_id}" class="columns">
			<div class="left">
				<div id="EvnPLDispChild_{EvnPLDisp_id}_content">
					<div class="caption">
						<h2 style="text-align: center">Результаты<br/>{DispClass_Name}</h2>
					</div>
					<div class="text">
						<p>Медицинская организация, в которой гражданин проходил диспансеризацию (профилактический медицинский осмотр): {Lpu_Nick}</p>
						<p>Дата медицинского осмотра: <?php echo empty($EvnPLDisp_setDate)?$empty_str:'{EvnPLDisp_setDate}'; ?></p>
						<p>Дата окончания медицинского осмотра: <?php echo empty($EvnPLDisp_disDate)?$empty_str:'{EvnPLDisp_disDate}'; ?></p>
						<p>
							Оценка   физического   развития   с  учетом  возраста  на  момент медицинского осмотра:<br/>
							<?php if ($Person_Age >= 0 && $Person_Age <= 4) { ?>
								Для детей в возрасте 0 - 4 лет:
								масса (кг) <?php echo empty($AssessmentHealth_Weight)?$empty_str:'{AssessmentHealth_Weight}'; ?>;
								рост (см) <?php echo empty($AssessmentHealth_Height)?$empty_str:'{AssessmentHealth_Height}'; ?>;
								окружность головы (см) <?php echo empty($AssessmentHealth_Head)?$empty_str:'{AssessmentHealth_Head}'; ?>;
								физическое развитие <?php echo empty($PhysicalCondition)?$empty_str:'{PhysicalCondition}'; ?>
							<?php } else if ($Person_Age >= 5 && $Person_Age <= 17) { ?>
								Для детей в возрасте 5 - 17 лет включительно:
								масса (кг) <?php echo empty($AssessmentHealth_Weight)?$empty_str:'{AssessmentHealth_Weight}'; ?>;
								рост (см) <?php echo empty($AssessmentHealth_Height)?$empty_str:'{AssessmentHealth_Height}'; ?>;
								физическое развитие <?php echo empty($PhysicalCondition)?$empty_str:'{PhysicalCondition}'; ?>
							<?php } ?>
						</p>
						<p>
							Оценка психического развития (состояния):<br/>
							<?php if ($Person_Age >= 0 && $Person_Age <= 4) { ?>
								Для детей в возрасте 0 - 4 лет:<br/>
								познавательная функция (возраст развития) (мес.) <?php echo empty($AssessmentHealth_Gnostic)?$empty_str:'{AssessmentHealth_Gnostic}'; ?><br/>
								моторная функция (возраст развития) (мес.) {AssessmentHealth_Motion}<br/>
								эмоциональная и социальная (контакт с окружающим миром) функции (возраст развития) (мес.) <?php echo empty($AssessmentHealth_Social)?$empty_str:'{AssessmentHealth_Social}'; ?><br/>
								предречевое и речевое развитие (возраст развития) (мес.)  <?php echo empty($AssessmentHealth_Speech)?$empty_str:'{AssessmentHealth_Speech}'; ?>.<br/>
							<?php } else if (!empty($Person_Age) && $Person_Age >= 5 && $Person_Age <= 17) { ?>
								Для детей в возрасте 5 - 17 лет:<br/>
								Психомоторная сфера: <?php echo empty($NormaDisturbanceTypePsych)?$empty_str:'{NormaDisturbanceTypePsych}'; ?><br/>
								Интелект: <?php echo empty($NormaDisturbanceTypeIntelligence)?$empty_str:'{NormaDisturbanceTypeIntelligence}'; ?><br/>
								Эмоционально-вегетативная сфера: <?php echo empty($NormaDisturbanceTypeEmotion)?$empty_str:'{NormaDisturbanceTypeEmotion}'; ?><br/>
							<?php } ?>
						</p>
						<?php if ($Person_Age >= 10) { ?><p>
							Оценка полового развития (с 10 лет):<br/>
							<?php if ($Sex_SysNick == 'man') { ?>
								Половая формула мальчика:
								P <?php echo empty($AssessmentHealth_P)?$empty_str:'{AssessmentHealth_P}'; ?>
								Ax <?php echo empty($AssessmentHealth_Ax)?$empty_str:'{AssessmentHealth_Ax}'; ?>
								Fa <?php echo empty($AssessmentHealth_Fa)?$empty_str:'{AssessmentHealth_Fa}'; ?>.
								<br/>
							<?php } else if ($Sex_SysNick == 'woman') { ?>
								Половая формула девочки:
								P <?php echo empty($AssessmentHealth_P)?$empty_str:'{AssessmentHealth_P}'; ?>
								Ax <?php echo empty($AssessmentHealth_Ax)?$empty_str:'{AssessmentHealth_Ax}'; ?>
								Ma <?php echo empty($AssessmentHealth_Ma)?$empty_str:'{AssessmentHealth_Ma}'; ?>
								Me <?php echo empty($AssessmentHealth_Me)?$empty_str:'{AssessmentHealth_Me}'; ?>;
								<br/>
								Характеристика менструальной функции: menarhe (лет, месяцев)
								<?php if (empty($AssessmentHealth_Year) && empty($AssessmentHealth_Month)) {
									echo $empty_str;
								} else {
									echo empty($AssessmentHealth_Year)?'':' {AssessmentHealth_Year} лет';
									echo empty($AssessmentHealth_Month)?'':' {AssessmentHealth_Month} месяцев';
									echo ';';
								} ?>
								<br/>
								menses (характеристика): <?php echo empty($Menses)?$empty_str:'{Menses}'; ?>.
							<?php } ?>
						</p><?php } ?>

						{EvnVizitDisp}

						<p>Группа состояния здоровья: <?php echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; ?></p>
						<p>Медицинская группа для занятий физической культурой: <?php echo empty($HealthGroupType_Name)?$empty_str:'{HealthGroupType_Name}'; ?></p>

						{EvnVizitDispRecommend}

						<p>Должность, Ф.И.О. медицинского работника:<br/>{Dolgnost_Name}, {MedPerson_Fio}</p>

					</div>
				</div>
			</div>
			<div class="right">
				<div id="EvnPLDispChild_{EvnPLDisp_id}_toolbar" class="toolbar" style="display: none">
					<a id="EvnPLDispChild_{EvnPLDisp_id}_edit" class="button icon icon-edit16" title="Редактирование"><span></span></a>
					<?php if( $Object == 'EvnPLDispOrp' || ($Object == 'EvnPLDispTeenInspection' && in_array($DispClass_Code, array(10))) ) { ?>
					<a id="EvnPLDispChild_{EvnPLDisp_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="clear">
		</div>
	</div>

</div>