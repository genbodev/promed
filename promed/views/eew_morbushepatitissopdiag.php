<div id="MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Сопутствующий диагноз</span></h2>
        <div id="MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}_toolbar" class="toolbar">
 			<a id="MorbusHepatitisSopDiagList_{MorbusHepatitis_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusHepatitisSopDiagTable_{MorbusHepatitis_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">

        <col class="first" />
        <col />
        <col class="last" />
		<thead>
 		<tr>
			<td>Дата</td>
			<td>Шифр МКБ</td>
			<td>Диагноз</td>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>
