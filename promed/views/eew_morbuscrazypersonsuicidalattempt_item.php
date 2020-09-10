<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyPersonSuicidalAttempt_Num}</td>
			<td>{MorbusCrazyPersonSuicidalAttempt_setDT}</td>
			<td class="toolbar">
				<div id="MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyPersonSuicidalAttempt_{MorbusCrazy_pid}_{MorbusCrazyPersonSuicidalAttempt_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
