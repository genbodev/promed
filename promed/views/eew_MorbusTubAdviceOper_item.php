<?php 
	$is_allow_edit = (1 == 1);
?>
		<tr id="MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}_toolbar').style.display='none'">
			<td>{MorbusTubAdviceOper_setDT}</td>
			<td>{UslugaComplex_Name}</td>
			<td class="toolbar">
				<div id="MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}_toolbar" class="toolbar">
					<!--a id="MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusTubAdviceOper_{MorbusTub_pid}_{MorbusTubAdviceOper_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a-->
				</div>
			</td>
		</tr>
