<div id="DirFailList_{pid}" class="data-table component read-only" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DirFailList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DirFailList_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2>Список отмененных направлений</h2>
		<div id="DirFailList_{pid}_toolbar" class="toolbar">
			<a id="DirFailList_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<a id="DirFailList_{pid}_showAll" class="button icon icon-view16" title="Все отмененные направления"><span></span></a>
		</div>
	</div>

	<table>

		<col style="width: 10%" class="first" />
		<col />
		<col style="width: 10%" />
		<col style="width: 20%" />
		<col class="last" />

		<thead>
		<tr>
			<th>Дата создания</th>
			<th>Врач, создавший направление</th>
			<th>Дата отклонения</th>
			<th>Причина отклонения</th>
			<th>Врач, отклонивший направление</th>
		</tr>
		</thead>

		<tbody id="DirFailListList_{pid}">

			{items}

		</tbody>

	</table>

</div>
