<div id="HeredityDiagList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('HeredityDiagList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('HeredityDiagList_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2><span id="HeredityDiagList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Наследственность по заболеваниям
		</span></h2>
		<div id="HeredityDiagList_{pid}_toolbar" class="toolbar">
			<a id="HeredityDiagList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="HeredityDiagTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col style="width: 15%" />
		<col style="width: 15%" class="last" />
		<col class="toolbar"/>

		<thead>
			<tr>
				<th>Диагноз</th>
				<th>Код МКБ-10</th>
				<th>Наследственность</th>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="HeredityDiagList_{pid}">

			{items}

		</tbody>

	</table>

</div>