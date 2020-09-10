<?php
/**
 * @package      MorbusOnko
 */
?>
<!--div id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'"-->
<div id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}" class="data-table">
    <div class="caption">
        <h2><span id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Схема лекарственной терапии</span></h2>
        <!--div id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
            <a id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
			<a id="DrugTherapySchemeList_{MorbusOnko_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div-->
    </div>

    <table id="DrugTherapySchemeTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">			
        <col class="first" />
		<col />
		<col />
		<col class="last" />
        <!--col class="toolbar"/-->
		<thead>
 		<tr>
			<th>Код</th>
			<th>Схема</th>
			<th>Кол-во дней введения (норматив)</th>
			<th>Кол-во дней введения (факт)</th>
			<!--th class="toolbar"></th-->
		</tr>
		</thead>
		<tbody>
       {items}
 		</tbody>
   </table>
</div>