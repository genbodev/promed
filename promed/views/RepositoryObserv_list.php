<?php
if (
	(!empty($EvnClass_id) && in_array($EvnClass_id, [11]))
	|| (
	(empty($EvnSection_IsPriem) || $EvnSection_IsPriem == 1)
		&& (
			(getRegionNick() == 'msk' && !empty($CovidType_id) && in_array($CovidType_id, [2,3]))
			|| (
				!empty($Diag_Code)
				&& (
					in_array($Diag_Code, ['U07.1', 'U07.2', 'Z03.8', 'Z11.5', 'Z20.8', 'Z22.8', 'B34.2', 'B33.8'])
					|| (
						substr($Diag_Code, 0, 3) >= 'J12'
						&& substr($Diag_Code, 0, 3) <= 'J19'
					)
				)
			)
		)
	)
) {
?>
	<div id="RepositoryObserv_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('RepositoryObserv_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('RepositoryObserv_{pid}_toolbar').style.display='none'">
		<div class="caption">
			<h2><?php echo (!empty($EvnClass_id) && in_array($EvnClass_id, [11])) ? 'Анкетирование пациента с подозрением на COVID-19' : 'Наблюдения за пациентом с пневмонией, подозрением на COVID-19 и COVID-19'; ?></h2>
			<div id="RepositoryObserv_{pid}_toolbar" class="toolbar">
				<a id="RepositoryObservList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
			</div>
		</div>

		<table>
			<col class="first" />
			<col class="last" />
			<col class="toolbar"/>

			<thead>
			<tr>
				<th><?php echo (!empty($EvnClass_id) && in_array($EvnClass_id, [11])) ? 'Дата и время анкетирования' : 'Дата и время наблюдения'; ?></th>
				<th>Врач</th>
				<th class="toolbar"></th>
			</tr>
			</thead>

			<tbody id="RepositoryObservList_{pid}">
				{items}
			</tbody>
		</table>
	</div>
<?php
}
?>