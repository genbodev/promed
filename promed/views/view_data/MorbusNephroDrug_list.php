<?php if (getRegionNick() == 'ufa'): ?>
<div id="MorbusNephroDrugList_{MorbusNephro_pid}_{pid}" class="data-table" 
		onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" 
		onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">

		<div class="caption">
			<h2>
				<span id="MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_toggleDisplay" <?= (!empty($items)) ? 'class="collapsible"' : '' ?>>Назначенное лекарственное лечение</span>
			</h2>
			<div id="MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
				<a id="MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
				<a id="MorbusNephroDrugList_{MorbusNephro_pid}_{pid}_selectIsLast" class="link viewAll">Отображать только последние</a>
			</div>
		</div>
		
	<table id="MorbusNephroDrugTable_{MorbusNephro_pid}_{pid}" style="display: <?= (empty($items)) ? 'none' : 'block'; ?>;">
		<col class="first" />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата</th>
			<th>Медикамент</th>
			<th>№ схемы</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
<?php endif; ?>
