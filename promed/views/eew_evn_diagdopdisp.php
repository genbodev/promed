<?php
	$EvnDiagDopDispTitle = 'Выявленные заболевания';
	if (empty($EvnDiagDopDispType)) {
		$EvnDiagDopDispType = "EvnDiagDopDisp";
	}

	switch($EvnDiagDopDispType) {
		case 'EvnDiagDopDispBefore':
			$EvnDiagDopDispTitle = 'Ранее известные имеющиеся заболевания';
			break;
		case 'EvnDiagDopDispFirst':
			$EvnDiagDopDispTitle = 'Впервые выявленные заболевания';
			break;
		case 'EvnDiagDopDispAndRecomendation':
			$EvnDiagDopDispTitle = 'Состояние здоровья до проведения диспансеризации / профосмотра';
			break;
	}
?>
<div id="<?php echo $EvnDiagDopDispType; ?>List_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $EvnDiagDopDispType; ?>List_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $EvnDiagDopDispType; ?>List_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2><span id="<?php echo $EvnDiagDopDispType; ?>List_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			<?php echo $EvnDiagDopDispTitle; ?>
		</span></h2>
		<div id="<?php echo $EvnDiagDopDispType; ?>List_{pid}_toolbar" class="toolbar">
			<a id="<?php echo $EvnDiagDopDispType; ?>List_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="<?php echo $EvnDiagDopDispType; ?>Table_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col style="width: 15%" />
		<?php if (!in_array($EvnDiagDopDispType, array('EvnDiagDopDispFirst','EvnDiagDopDispBefore'))) { ?>
		<col style="width: 15%" />
		<?php } ?>
		<col style="width: 15%" class="last" />
		<col class="toolbar"/>

		<thead>
			<tr>
				<th>Диагноз</th>
				<th>Код МКБ-10</th>
				<?php if ($EvnDiagDopDispType == 'EvnDiagDopDispAndRecomendation') { ?>
				<th>Установлен впервые</th>
				<th>Диспансерное наблюдение</th>
				<?php } ?>
				<?php if (!in_array($EvnDiagDopDispType, array('EvnDiagDopDispFirst','EvnDiagDopDispAndRecomendation'))) { ?>
				<th>Дата постановки диагноза</th>
				<?php } ?>
				<?php if (!in_array($EvnDiagDopDispType, array('EvnDiagDopDispBefore','EvnDiagDopDispAndRecomendation'))) { ?>
				<th>Тип</th>
				<?php } ?>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="<?php echo $EvnDiagDopDispType; ?>List_{pid}">

			{items}

		</tbody>

	</table>

</div>