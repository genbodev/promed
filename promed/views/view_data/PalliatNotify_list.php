<?php
$properties = $item_arr[count($item_arr) - 1];
unset($item_arr[count($item_arr) - 1]);
?>

<div id="PalliatNotify_{pid}" style="display: <?php
if (empty($properties['showPalliatNotifyList'])) {
	echo 'none';
} else{
	echo 'block';
}
?>" style="margin-top: 10px;" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PalliatNotifyList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PalliatNotifyList_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2>Извещение</h2>
		<div id="PalliatNotifyList_{pid}_toolbar" class="toolbar">
			<a id="PalliatNotifyList_{pid}_addNotify" class="button icon icon-add16" title="Создать извещение о пациенте, имеющего признаки нуждаемости в паллиативной помощи" style="display: <?php
			if (empty($properties['allowAddPalliatNotifyButton'])) {
				echo 'none';
			} else{
				echo 'block';
			}
			?>"><span></span></a>
		</div>
	</div>

	<table>
		<col style="width: 10%" class="first" />
		<col />
		<col />
		<col />
		<col />
		<col class="last" />
		<col style="width: 20%" class="toolbar" />
		<thead>
		<tr>
			<th>Дата заполнения извещения</th>
			<th>Диагноз</th>
			<th>Врач, заполнивший направление</th>
			<th>МО заполнения направления</th>
			<th>Дата обработки извещения</th>
			<th>Включен в регистр</th>
			<th class="toolbar">
		</tr>
		</thead>
		<tbody id="PalliatNotifyList_{pid}">
		<?php
		foreach ($item_arr as $row) {
			$section_id = "PalliatNotify_{$row['PalliatNotify_id']}";
			?>
			<tr id="<?php echo $section_id; ?>" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $section_id; ?>_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $section_id; ?>_toolbar').style.display='none'">
				<td><?php echo $row['EvnNotifyBase_setDate']; ?></td>
				<td><?php echo $row['Diag_FullName']; ?></td>
				<td><?php echo $row['MedPersonal_Fio']; ?></td>
				<td><?php echo $row['Lpu_Nick']; ?></td>
				<td><?php echo $row['procDate']; ?></td>
				<td><?php echo $row['isInRegister']; ?></td>
				<td class="toolbar">
					<div id="<?php echo $section_id; ?>_toolbar" class="toolbar">
						<a id="<?php echo $section_id; ?>_viewNotify" class="button icon icon-view16" title="Извещение о пациенте, нуждающемся в ПМП"><span></span></a>
					</div>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>