<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Состояние пациента</span></h2>
        <div id="MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display: none;"'; } ?>><span></span></a>
			<a id="MorbusOnkoBasePersonStateList_{MorbusOnko_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusOnkoBasePersonStateTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
        <col class="first" />
		<col class="last" />
        <col class="toolbar"/>
		<thead>
 		<tr>
			<th>Дата наблюдения</th>
			<th>Общее состояние пациента</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>