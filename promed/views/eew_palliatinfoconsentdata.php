<div id="PalliatInfoConsentData_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PalliatInfoConsentData_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PalliatInfoConsentData_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2>Информированное согласие/отказ в рамках паллиативной помощи</h2>
		<div id="PalliatInfoConsentData_{pid}_toolbar" class="toolbar">
			<a id="PalliatInfoConsentDataList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
			<a id="PalliatInfoConsentData_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
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
			<th>Дата</th>
			<th>Тип события</th>
			<th>ФИО специалиста</th>
			<th>МО</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="PalliatInfoConsentDataList_{pid}">

		{items}

		</tbody>

	</table>

</div>