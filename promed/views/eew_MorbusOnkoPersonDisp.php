<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      04.2017
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoPersonDispList_{MorbusOnko_pid}_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="MorbusOnkoPersonDispList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Контрольная карта диспансерного наблюдения</span></h2>
		<div style="display: <?php if ($can_add && !empty($haveCommonARM)) { ?>block<?php } else { ?>none<?php } ?>;">
            <a id="MorbusOnkoPersonDispList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить карту диспансерного наблюдения"><span></span></a>
            <a id="MorbusOnkoPersonDispList_{MorbusOnko_pid}_{pid}_select" class="button icon icon-select16" title="Найти карту диспансерного наблюдения"><span></span></a>
        </div>
	</div>

	<table id="MorbusOnkoPersonDispTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Взят</th>
			<th>Снят</th>
			<th>Поставивший врач</th>
			<th>Ответственный врач</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
