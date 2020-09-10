<?php
	$is_morbus = (strtotime($EvnPLStom_setDate) >= getEvnPLStomNewBegDate());

	if ( $is_morbus === true ) {
?>
<div id="EvnDiagPLStom_{EvnDiagPLStom_id}" class="data-table component">
	<div class="caption">
		<h2>Заболевание</h2>
	</div>
	<div class="clear">
		<div id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_content">
			<div>Дата начала: <b>{EvnDiagPLStom_setDate}</b></div>
			<?php if (!empty($EvnDiagPLStom_disDate)) { ?><div>Дата окончания: <b>{EvnDiagPLStom_disDate}</b></div><?php } ?>
			<div>Заболевание закрыто: <b><?php echo (!empty($EvnDiagPLStom_IsClosed) && $EvnDiagPLStom_IsClosed == 2 ? "Да" : "Нет"); ?></b></div>
			<div>Диагноз: <b>{Diag_Code}. {Diag_Name}</b></div>
			<?php if ( !empty($Tooth_Code) ) { ?><div>Номер зуба: <b>{Tooth_Code}</b></div><?php } ?> 
			<?php if ( !empty($Mes_Code) ) { ?><div>КСГ: <b>{Mes_Code}. {Mes_Name}</b></div><?php } ?> 
		</div>
	</div>
</div>
<?php
	}
?>

