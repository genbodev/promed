<tr id="EvnVKExpert_{EvnVKExpert_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnVKExpert_{EvnVKExpert_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnVKExpert_{EvnVKExpert_id}_toolbar').style.display='none'">
	<td>{MedPersonal_Fio}</td>
	<td>{ExpertMedStaffType_Name}</td>	
	<?php if(getRegionNick() == 'vologda') { ?> 
	<td>{EvnVKExpert_isApprovedName}</td>
	<td>{EvnVKExpert_Descr}</td>
	<?php } ?>
	<td class="toolbar">
		<div id="EvnVKExpert_{EvnVKExpert_id}_toolbar" class="toolbar">
			<?php if(getRegionNick() != 'vologda' || $EvnVK_isInternal == 2) { ?> 
			<a id="EvnVKExpert_{EvnVKExpert_id}_editExpert" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="EvnVKExpert_{EvnVKExpert_id}_deleteExpert" class="button icon icon-delete16" title="Удалить"><span></span></a>
			<?php } ?>
		</div>
	</td>
</tr>