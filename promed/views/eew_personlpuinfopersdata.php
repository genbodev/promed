<div id="PersonLpuInfoPersData_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonLpuInfoPersData_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonLpuInfoPersData_{pid}_toolbar').style.display='none'">

<div class="caption" style="float:left;width:100%;clear:both;">
        <h2>Информированное добровольное согласие</h2>
        <div id="PersonLpuInfoPersData_{pid}_toolbar" class="toolbar">
			<a id="PersonLpuInfoPersData_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
        </div>
    </div>
	<table>

		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>

		<thead>
		<tr>
			<th>Согласие</th>
			<th>Результат</th>
			<th>Дата</th>
			<th>МО</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="PersonLpuInfoPersDataList_{pid}">

		{items}

		</tbody>

	</table>
</div>
