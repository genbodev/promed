<?php 
	$is_allow_edit = (1 == $accessType);
?>
<div class="columns" id="MorbusTubAdvice_{MorbusTub_pid}_{MorbusTubAdvice_id}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubAdvice_{MorbusTub_pid}_{MorbusTubAdvice_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubAdvice_{MorbusTub_pid}_{MorbusTubAdvice_id}_toolbar').style.display='none'">
	<div class="left">
		<div class="data">
			<p>Дата консультации: <strong>{MorbusTubAdvice_setDT}</strong></p>             
			<p>Результат консультации: <strong>{TubAdviceResultType_Name}</strong></p> 
			{MorbusTubAdviceOper}
		</div>
	</div>
	<div class="right">
		<div id="MorbusTubAdvice_{MorbusTub_pid}_{MorbusTubAdvice_id}_toolbar" class="toolbar">
			<a id="MorbusTubAdvice_{MorbusTub_pid}_{MorbusTubAdvice_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</div>
</div>