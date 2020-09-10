<tr id="PersonLpuInfoPersData_{PersonLpuInfo_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonLpuInfoPersData_{PersonLpuInfo_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonLpuInfoPersData_{PersonLpuInfo_id}_toolbar').style.display='none'">
	<td>
		<?php
			if($PersonLpuInfoType == 'ReceptElectronic') {
				echo 'На рецепт в форме электронного документа';
			} else {
				echo 'На обработку персональных данных';
			}
		?>
	</td>
	<td>
		<?php
			if ($PersonLpuInfoType == 'ReceptElectronic') {
				if ($PersonLpuInfo_IsAgree == 2) {
					echo 'Дано';
				} else {
					echo 'Отозвано';
				}
			} else {
				if ($PersonLpuInfo_IsAgree == 2) {
					echo 'Согласие';
				} else {
					echo 'Отказ';
				}
			}
		?>
	</td>
	<td>{PersonLpuInfo_setDate}</td>
	<td>{Lpu_Nick}</td>
	<td class="toolbar">

		<div id="PersonLpuInfoPersData_{PersonLpuInfo_id}_toolbar" class="toolbar">
			<?php if ($PersonLpuInfoType == 'ReceptElectronic') { ?>
				<a id="PersonLpuInfoPersData_{PersonLpuInfo_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<?php } ?>
		</div>


	</td>
</tr>
