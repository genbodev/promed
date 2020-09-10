<?php 
	$accessMorbusCrazyForceTreat = (isset($accessMorbusCrazyForceTreat))?$accessMorbusCrazyForceTreat:1;
	$is_allow_edit = (($accessType == 1) && ($accessMorbusCrazyForceTreat == 1));
?>

<div id="MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Принудительное лечение</span></h2>
        <div id="MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusCrazyForceTreatList_{MorbusCrazy_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusCrazyForceTreatTable_{MorbusCrazy_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
				<col class="first" />
				<col class="last" />
				<col class="toolbar"/>
		<thead>
 		<tr>
			<th>Период лечения</th>
			<th>Вид лечения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>