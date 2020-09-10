<div id="PersonPrivilegeRegAllList_{pid}" class="data-table component">
	<div class="caption">
        <h2><span id="PersonPrivilegeRegAllList_{pid}_toggleDisplay"<?php echo empty($item_arr) ? '' : ' class="collapsible"'; ?>>Региональная льгота</span></h2>
	</div>
	<table id="PersonPrivilegeRegAllTable_{pid}" style="display: <?php echo empty($item_arr) ? 'none' : 'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<thead>
		<tr>
			<th>Код</th>
			<th>Дата начала</th>
			<th>Дата окончания</th>
			<th>Отказано в текущем году</th>
		</tr>
		</thead>
		<tbody>
        <?php
        foreach ($item_arr as $row) {
            ?>
            <tr class="list-item">
                <td><?php echo $row['PrivilegeType_Code']; ?></td>
                <td><?php echo $row['PersonPrivilege_begDate']; ?></td>
                <td><?php echo $row['PersonPrivilege_endDate']; ?></td>
                <td><?php echo $row['PersonRefuse_IsRefuse_Name']; ?></td>
            </tr>
			<?php
        }
        ?>
		</tbody>
	</table>
</div>