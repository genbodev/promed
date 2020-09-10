<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusTubStudyResult_{MorbusTub_pid}_{MorbusTubStudyResult_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubStudyResult_{MorbusTub_pid}_{MorbusTubStudyResult_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubStudyResult_{MorbusTub_pid}_{MorbusTubStudyResult_id}_toolbar').style.display='none'">
			<td>{TubStageChemType_Name}</td>
			<td>{TubMicrosResultType_Name}<?php echo (isset($MorbusTubStudyMicrosResult_setDT) ? ' / {MorbusTubStudyMicrosResult_setDT}':'' ); ?></td>
			<td>{TubSeedResultType_Name}<?php echo (isset($MorbusTubStudySeedResult_setDT) ? ' / {MorbusTubStudySeedResult_setDT}':'' ); ?></td>
			<td>{TubHistolResultType_Name}<?php echo (isset($MorbusTubStudyHistolResult_setDT) ? ' / {MorbusTubStudyHistolResult_setDT}':'' ); ?></td>
			<td>{TubXrayResultType_Name}<?php echo (isset($MorbusTubStudyXrayResult_setDT) ? ' / {MorbusTubStudyXrayResult_setDT}':'' ); ?></td>
			<td>{PersonWeight_Weight}</td>
			<td class="toolbar">
				<div id="MorbusTubStudyResult_{MorbusTub_pid}_{MorbusTubStudyResult_id}_toolbar" class="toolbar">
					<a id="MorbusTubStudyResult_{MorbusTub_pid}_{MorbusTubStudyResult_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php if (!$is_allow_edit) { ?> style="display: none;"<?php } ?>><span></span></a>
				</div>
			</td>
		</tr>
