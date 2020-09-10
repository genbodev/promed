<?php 
	$is_allow_edit = ('edit' == $AccessType);
?>
<div id="MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Лабораторные подтверждения</span></h2>
        <div id="MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			<a id="MorbusHepatitisLabConfirmList_{MorbusHepatitis_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusHepatitisLabConfirmTable_{MorbusHepatitis_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
        <col class="first" />
 		<col />
        <col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата исследования</th>
			<th>Тип</th>
			<th>Результат</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>

</div>
