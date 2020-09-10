<div id="EvnUslugaDispDopList_{pid}"
	 class="data-table"
	 onmouseover="if (isMouseLeaveOrEnter(event, this))
	 	document.getElementById('EvnUslugaDispDopList_{pid}_toolbar').style.display='block'"
	 onmouseout="if (isMouseLeaveOrEnter(event, this))
	 	document.getElementById('EvnUslugaDispDopList_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="EvnUslugaDispDopList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Маршрутная карта
		</span></h2>
		<div id="EvnUslugaDispDopList_{pid}_toolbar" class="toolbar">
			<a id="EvnUslugaDispDopList_{pid}_print" class="button icon icon-print16" title="Печать рекомендаций"><span></span></a>
		</div>
	</div>
	<table id="EvnUslugaDispDopTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col width="40%" class="first" />
		<col width="10%" />
		<col width="50%" class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Наименование осмотра / исследования</th>
			<th>Дата выполнения</th>
			<th>Назначение / направление</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="EvnUslugaDispDopList_{pid}">

		{items}

		</tbody>

	</table>

</div>