<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}">
            <div id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_content">
			Дата начала: {EvnDiagPLStom_setDate};
			<?php if (!empty($EvnDiagPLStom_disDate)) { ?>Дата окончания: {EvnDiagPLStom_disDate};<?php } ?>
			Заболевание закрыто: <?php echo (!empty($EvnDiagPLStom_IsClosed) && $EvnDiagPLStom_IsClosed == 2 ? "Да" : "Нет"), ";"; ?>
			Диагноз: <strong>{Diag_Code}. {Diag_Name}</strong>;
			<?php if ( !empty($Tooth_Code) ) { ?>Номер&nbsp;зуба:&nbsp;{Tooth_Code};<?php } ?> 
			<?php if ( !empty($Mes_Code) ) { ?>КСГ:&nbsp;{Mes_Code}. {Mes_Name};<?php } ?> 
			<!--{DeseaseType_Name}. {EvnDiagPLStomSop_setDate} {MedPersonal_Fin}/{LpuSection_Name}-->
			</div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_toolbar" class="toolbar">
			<?php if (getRegionNick() != 'kz') {  ?>
            <a id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_printZno" class="button icon icon-print16" title="Печать КЛУ при ЗНО"><span></span></a>
            <?php 	if (getRegionNick() == 'ekb') { ?>
            <a id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_printOnko" class="button icon icon-print16" title="Печать выписки при онкологии"><span></span></a>
			<?php 	} ?>
			<?php }  ?>
            <a id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_edit" class="button icon icon-edit16" title="Редактировать заболевание"><span></span></a>
            <a id="EvnDiagPLStom_{EvnDiagPLStom_vid}_{EvnDiagPLStom_id}_delete" class="button icon icon-delete16" title="Удалить заболевание"><span></span></a>
        </div>
    </td>
</tr>
