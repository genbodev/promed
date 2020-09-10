<div id="NephroAccessList_{MorbusNephro_pid}_{pid}" class="data-table" 
		onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroAccessList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" 
		onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroAccessList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">

		<div class="caption">
			<h2><span id="NephroAccessList_{MorbusNephro_pid}_{pid}_toggleDisplay" <?php if (!empty($items)) echo 'class="collapsible"'; ?>>
				Тип доступа</span></h2>
			<div id="NephroAccessList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
				<a id="NephroAccessList_{MorbusNephro_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
				<a id="NephroAccessList_{MorbusNephro_pid}_{pid}_selectIsLast" class="link viewAll">Отображать только последние</a>
			</div>
		</div>

	<table id="NephroAccessTable_{MorbusNephro_pid}_{pid}" style="display: <?php if (empty($items)) echo 'none'; else echo 'block'; ?>;">
		<col class="first" />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата заполнения</th>
			<th>Тип доступа</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>