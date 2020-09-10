<?php
$is_allow_edit = true;
?>
<div id="MorbusHIVChemList_{MorbusHIV_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVChemList_{MorbusHIV_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVChemList_{MorbusHIV_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusHIVChemList_{MorbusHIV_pid}_{pid}_toggleDisplay" class="<?php echo !empty($items)?'collapsible':'collapsible-empty';?>">Проведение химиопрофилактики ВИЧ-инфекции</span></h2>
        <div id="MorbusHIVChemList_{MorbusHIV_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusHIVChemList_{MorbusHIV_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display: none;"'; } ?>><span></span></a>
        </div>
    </div>

    <table id="MorbusHIVChemTable_{MorbusHIV_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">		
        <col class="first" />
		<col />
		<col />
		<col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Препарат</th>
			<th>Доза</th>
			<th>Дата начала</th>
			<th>Дата окончания</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>