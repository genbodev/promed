<div id="NeedConsultList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NeedConsultList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NeedConsultList_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2><span id="NeedConsultList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Показания к консультации врача-специалиста
		</span></h2>
		<div id="NeedConsultList_{pid}_toolbar" class="toolbar">
			<a id="NeedConsultList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="NeedConsultTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col style="width: 30%" class="last" />
		<col class="toolbar"/>

		<thead>
			<tr>
				<th>Врач-специалист</th>
				<th>Место проведения</th>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="NeedConsultList_{pid}">

			{items}

		</tbody>

	</table>

</div>