<?php
$isAllowAdd = false;
if (isset($item_arr) && is_array($item_arr) && count($item_arr) > 0) {
    $cnt = count($item_arr);
    $i = $cnt-1;
    $person_age = null;
    if (isset($item_arr[$i]['Person_Age'])) {
        $person_age = $item_arr[$i]['Person_Age'];
        unset($item_arr[$i]);
    }
    if ($person_age >= 18) {
        $isAllowAdd = true;
    }
} else {
    $item_arr = array();
}
if (count($item_arr) > 0) {
	$curTime = strtotime(date('Y-m-d'));
	$yar = 365*24*60*60;
	foreach ($item_arr as $row) {
		$difTime = $curTime - strtotime($row['PersonOnkoProfile_setDate']);
		if ($difTime < $yar) {
			$isAllowAdd = false;
			break;
		}
	}
}
$isAllowAdd = true;
?>

<div id="PersonOnkoProfile_{id}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonOnkoProfileList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonOnkoProfileList_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2>Список опросов</h2>
		<div id="PersonOnkoProfileList_{pid}_toolbar" class="toolbar">
			<a id="PersonOnkoProfileList_{pid}_add" class="button icon icon-add16" title="Добавить" style="display: <?php
			if (empty($isAllowAdd)) {
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
		<col class="last" />
		<col style="width: 20%" class="toolbar" />
		<thead>
		<tr>
			<th>Дата проведения опроса </th>
			<th>Тип опроса</th>
            <th>Пользователь, заполнивший анкету </th>
			<th>Статус пациента</th>
			<th class="toolbar">
		</tr>
		</thead>
		<tbody id="PersonOnkoProfileList_{pid}">
		<?php
		$checkScreening = true; $checkIBS = true; $checkAG = true; $checkLG = true; 
		foreach ($item_arr as $row) {
			$section_id = "PersonOnkoProfile_{$row['PersonOnkoProfile_id']}";
			?>
			<tr <?php 
					if($row['ReportType'] == 'registrBSK' && getRegionNick() != 'kz') {
						$currdate = new DateTime();
						$BSKdate = new DateTime($row['PersonOnkoProfile_setDate']);
						$BSKtype = $row['PersonProfileType_Name'];
						$BSKRisk = $row['Monitored_Name'];
						if(strpos($BSKtype, 'Скрининг')) {
							switch($BSKRisk) {
								case 'I группа риска': $BSKdate->add(new DateInterval('P18M')); break;
								case 'II группа риска': $BSKdate->add(new DateInterval('P12M')); break;
								case 'III группа риска': $BSKdate->add(new DateInterval('P6M')); break;
							}
						} else { 
							$BSKdate->add(new DateInterval('P6M'));
						}
						if(isset($row['EvnNotifyBase_setDate'])) {
							$BSKdate = new DateTime($row['EvnNotifyBase_setDate']);
						}
						if(((strpos($BSKtype, 'Скрининг') && $checkScreening == true) || (strpos($BSKtype, 'Ишемическая') && $checkIBS == true) || (strpos($BSKtype, 'Артериальная') && $checkAG == true) || (strpos($BSKtype, 'Легочная') && $checkLG == true)) && $currdate > $BSKdate) {
							?>style="color: red;"<?php
						}
						if (strpos($BSKtype, 'Скрининг')) {$checkScreening = false;}
						if (strpos($BSKtype, 'Ишемическая')) {$checkIBS = false;}
						if (strpos($BSKtype, 'Артериальная')) {$checkAG = false;}
						if (strpos($BSKtype, 'Легочная')) {$checkLG = false;}
					}
			?>id="<?php echo $section_id; ?>" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $section_id; ?>_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('<?php echo $section_id; ?>_toolbar').style.display='none'">
				<td><?php echo $row['PersonOnkoProfile_setDate']; ?></td>
                <td><?php echo $row['PersonProfileType_Name']; ?></td>
                <td><?php echo $row['PMUser_Name']; ?></td>
				<td><?php echo $row['Monitored_Name']; ?></td>
				<td class="toolbar">
					<div id="<?php echo $section_id; ?>_toolbar" class="toolbar">
                        <?php if ( $row['ReportType'] == 'onko' ) { ?><a id="<?php echo $section_id; ?>_print" class="button icon icon-print16" title="Печать"><span></span></a><?php } ?>
						<a id="<?php echo $section_id; ?>_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
						<a id="<?php echo $section_id; ?>_edit" class="button icon icon-edit16" title="Редактирование" style="display: <?php echo $row['displayEditBtn']; ?>;"><span></span></a>
						<a id="<?php echo $section_id; ?>_del" class="button icon icon-delete16" title="Удалить" style="display: <?php echo $row['displayDelBtn']; ?>;"><span></span></a>
					</div>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div>