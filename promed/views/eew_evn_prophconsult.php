<div id="ProphConsultList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ProphConsultList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ProphConsultList_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2><span id="ProphConsultList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Показания к углублённому профилактическому консультированию
		</span></h2>
		<div id="ProphConsultList_{pid}_toolbar" class="toolbar">
			<a id="ProphConsultList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="ProphConsultTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first last" />
		<col class="toolbar"/>

		<thead>
			<tr>
				<th>Фактор риска</th>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="ProphConsultList_{pid}">

			{items}

		</tbody>

	</table>

</div>