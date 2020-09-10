<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
?>
<div id="EvnPLDispAdult_{EvnPLDisp_id}">

	<div id="EvnPLDispAdult_wrap" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispAdult_{EvnPLDisp_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispAdult_{EvnPLDisp_id}_toolbar').style.display='none'">
		<div id="EvnPLDispAdult_{EvnPLDisp_id}" class="columns">
			<div class="left">
				<div id="EvnPLDispAdult_data_{EvnPLDisp_id}_content">
					<div class="caption">
						<h2 style="text-align: center">Результаты<br/>{DispClass_Name}</h2>
					</div>
					<div class="text">
						<p>Медицинская организация, в которой гражданин проходил диспансеризацию (профилактический медицинский осмотр): {Lpu_Nick}</p>
						<p>Дата начала диспансеризации (профилактического медицинского осмотра): <?php echo empty($EvnPLDisp_setDate)?$empty_str:'{EvnPLDisp_setDate}'; ?></p>
						<p>Дата окончания диспансеризации (профилактического медицинского осмотра): <?php echo empty($EvnPLDisp_disDate)?$empty_str:'{EvnPLDisp_disDate}'; ?></p>

						{EvnDiagDopDisp}

						<p>Имеется подозрение на ранее перенесенное нарушение мозгового кровообращения: <?php echo empty($IsBrain)?$empty_str:'{IsBrain}'; ?></p>
						<p>Группа состояния здоровья: <?php echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; ?></p>
						<p>Взят под диспансерское наблюдение: <?php echo empty($IsDisp)?$empty_str:'{IsDisp}'; ?></p>
						<p>Назначено лечение: <?php echo empty($NeedCure)?$empty_str:'{NeedCure}'; ?></p>
						<p>
							Дано направление на дополнительное диагностическое исследование, не входящее в объем диспансеризации (профилактического Медицинского осмотра):
							<?php echo empty($NeedOutDispCure)?$empty_str:'{NeedOutDispCure}'; ?>
						</p>
						<p>
							Дано направление для  получения  специализированной,  в  том  числе  высокотехнологичной медицинской помощи:
							<?php echo empty($NeedSpecCure)?$empty_str:'{NeedSpecCure}'; ?>
						</p>
						<p>Дано направление на санаторно-курортное лечение: <?php echo empty($IsSanator)?$empty_str:'{IsSanator}'; ?></p>
						<p>Должность, Ф.И.О. медицинского работника:<br/>{Dolgnost_Name}, {MedPerson_Fio}</p>
					</div>
				</div>
			</div>
			<div class="right">
				<div id="EvnPLDispAdult_{EvnPLDisp_id}_toolbar" class="toolbar" style="display: none">
					<a id="EvnPLDispAdult_{EvnPLDisp_id}_edit" class="button icon icon-edit16" title="Редактирование"><span></span></a>
				</div>
			</div>
		</div>
		<div class="clear">
		</div>
	</div>

</div>