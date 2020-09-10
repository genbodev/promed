
<h2>Случай стационарного лечения №&nbsp;<strong>{EvnPS_NumCard}</strong>, {EvnPS_setDate}, {EvnPS_setTime} - {EvnPS_disDate}, {EvnPS_disTime}</h2>
	<p>Диагноз:
		{Diag_Code}
		{Diag_Name}.</p>
	<p>Исход госпитализации: <strong>{LeaveType_Name}</strong></p>
	<p>Тип госпитализации: <strong>{PrehospType_Name}</strong></p>
	<p>Доставлен: {PrehospArrive_Name} <?php echo (($PrehospArrive_id != 2)?'':', Код: {EvnPS_CodeConv} Номер наряда: {EvnPS_NumConv}');?></p>
	<?php echo (empty($EvnPS_IsCont)?'':'<p>Переведен из: {Lpu_p_Name}</p>');?>
	<p>Направление: <?php echo (empty($EvnDirection_Num)?'нет':'{EvnDirection_Num} от');?> {EvnDirection_setDate}</p>
	<p>Кем выдано: {Lpu_d_Name}</p>
	<p>Диагноз направившего учреждения: <strong>{Diag_d_Code}. {Diag_d_Name}</strong></p>
	<p>
		Сопутствующие диагнозы направившего учреждения:<br />
		{EvnDiagDirectPS}
	</p>
	<p>
		<?php echo ((2 == $EvnPS_IsImperHosp || 2 == $EvnPS_IsShortVolume || 2 == $EvnPS_IsWrongCure || 2 == $EvnPS_IsDiagMismatch)?'Дефекты догоспитального этапа:<br />':'');?>
		<?php echo ((2 == $EvnPS_IsImperHosp)?'<strong>Несвоевременность госпитализации</strong><br />':'');?>
		<?php echo ((2 == $EvnPS_IsShortVolume)?'<strong>Недостаточный объем клинико-диагностического обследования</strong><br />':'');?>
		<?php echo ((2 == $EvnPS_IsWrongCure)?'<strong>Неправильная тактика лечения</strong><br />':'');?>
		<?php echo ((2 == $EvnPS_IsDiagMismatch)?'<strong>Несовпадение диагноза</strong><br />':'');?>
	</p>
	<br />