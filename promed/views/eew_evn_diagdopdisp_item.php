<?php
	if (empty($EvnDiagDopDispType)) {
		$EvnDiagDopDispType = "EvnDiagDopDisp";
	}
?>
<tr id="<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_toolbar').style.display='none'">
	<td>{Diag_Name}</td>
	<td>{Diag_Code}</td>
	<?php if ($EvnDiagDopDispType == 'EvnDiagDopDispAndRecomendation') { ?>
		<td>{DeseaseDispType_Name}</td>
		<td>{DispSurveilType_Name}</td>
	<?php } ?>
	<?php if (!in_array($EvnDiagDopDispType, array('EvnDiagDopDispFirst','EvnDiagDopDispAndRecomendation'))) { ?>
	<td>{EvnDiagDopDisp_setDate}</td>
	<?php } ?>
	<?php if (!in_array($EvnDiagDopDispType, array('EvnDiagDopDispBefore','EvnDiagDopDispAndRecomendation'))) { ?>
	<td>{DiagSetClass_Name}</td>
	<?php } ?>
	<td class="toolbar">
		<div id="<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_toolbar" class="toolbar">
			<a id="<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
			<a id="<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="<?php echo $EvnDiagDopDispType; ?>_{EvnDiagDopDisp_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
		</div>
	</td>
</tr>