<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      02.2017
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Қосалқы аурулары (Сопутствующие заболевания)</span></h2>
        <div id="MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusOnkoSopDiagList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display: none;"'; } ?>><span></span></a>
        </div>
    </div>

    <table id="MorbusOnkoSopDiagTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">		
		<col class="first last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Сопутствующее заболевание</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>