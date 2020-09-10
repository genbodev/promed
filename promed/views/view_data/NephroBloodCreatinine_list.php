<div id="NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
			<h2><span id="NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}_toggleDisplay" <?php if (!empty($items)) echo 'class="collapsible"'; ?>>
				Результаты услуги "Исследование уровня креатинина в крови"</span></h2>
		<div id="NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
			<a id="NephroBloodCreatinineList_{MorbusNephro_pid}_{pid}_selectIsLast" class="link viewAll">Отображать только последние</a>
		</div>
	</div>
	<table id="NephroBloodCreatinineTable_{MorbusNephro_pid}_{pid}" style="display: <?php
	if (empty($items)) { echo 'none'; } else { echo 'block'; }
	?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<thead>
		<tr>
			<th>Дата</th>
			<th>Показатель</th>
			<th>Значение</th>
			<th>Единица измерения</th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>