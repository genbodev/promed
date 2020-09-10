<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
<div id="MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Химиотерапевтическое лечение</span></h2>
        <div id="MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="MorbusOnkoChemTerList_{MorbusOnko_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusOnkoChemTerTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">		
        <col class="first" />
		<col />
		<col />
		<col />
		<col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата начала</th>
			<th>Дата окончания</th>
			<th>ЛПУ</th>
			<th>Вид химиотерапии</th>
			<th>Преимущественная направленность</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>