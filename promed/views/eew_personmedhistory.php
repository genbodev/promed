<div class="component">
	<div id="PersonMedHistory_{Person_id}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonMedHistory_{Person_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonMedHistory_{Person_id}_toolbar').style.display='none'">

		<div class="caption">
			<h2>Анамнез жизни</h2>
			<div id="PersonMedHistory_{Person_id}_toolbar" class="toolbar">
				<?php
			$style = '';
			if($accessType == 'edit')
			{
				$ico = 'icon-edit16';
				$title = 'Редактировать';
				if(empty($PersonMedHistory_id))
				{
					$ico = 'icon-add16';
					$title = 'Добавить';
				}
			}
			else
			{
				$ico = 'icon-view16';
				$title = 'Просмотр';
				$style = ' style="display: none"';
			}
				?>
				<a id="PersonMedHistory_{Person_id}_edit" class="button icon <?php echo $ico; ?>" title="<?php echo $title; ?>"<?php echo $style; ?>><span></span></a>
				<a id="PersonMedHistory_{Person_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			</div>
		</div>
		<div class="clear">
		Дата записи: {PersonMedHistory_setDT} <br />
		{PersonMedHistory_Descr}
		</div>

	</div>

	<?php if (count($PersonQuarantine) > 0) { ?>
	<div id="PersonQuarantine_{Person_id}" class="data-table">

		<div class="caption">
			<h2>Нахождение на карантине COVID-19</h2>
		</div>

		<table>

			<col class="first" />
			<col class="last" />
			<col class="toolbar"/>

			<thead>
				<tr>
					<th>Дата начала</th>
					<th>Дата окончания</th>
					<th class="toolbar"></th>
				</tr>
			</thead>

			<tbody id="PersonQuarantineList_{Person_id}">

				{PersonQuarantine}
				<tr id="PersonQuarantine_{PersonQuarantine_id}" class="list-item">
					<td>{PersonQuarantine_begDT}</td>
					<td>{PersonQuarantine_endDT}</td>
					<td class="toolbar"></td>
				</tr>
				{/PersonQuarantine}

			</tbody>

		</table>

	</div>
	<?php } ?>
</div>