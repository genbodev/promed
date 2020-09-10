<!---/*NO PARSE JSON*/--->
<style rel="stylesheet" type="text/css">
	#scheduleWorkDoctorTable {
		border: 0px;
		width: 100%;
	}
	#scheduleWorkDoctorTable th {
		font-weight: bold;
	}
	#scheduleWorkDoctorTable td {
		border: 1px solid gray;
		text-align: center;
		font-size: 10px;
		padding: 2px 0px;
		padding-left: 10px;
		padding-right: 10px;
	}

</style>


<table class="table" width="100%" cellspacing="0" cellpadding="0" id="scheduleWorkDoctorTable">
	<thead>
	<tr>
		<th rowspan="2" style="text-align: center;">Должность</th>
		<th rowspan="2" style="text-align: center;">ФИО врача</th>
		<th rowspan="2" style="text-align: center;">Кабинет</th>
		<th rowspan="2" style="text-align: center;">Участок</th>
		<th colspan="7" style="text-align: center;">Время приема</th>
	</tr>
	<tr>
		<td style="text-align: center;">Понедельник</td>
		<td style="text-align: center;">Вторник</td>
		<td style="text-align: center;">Среда</td>
		<td style="text-align: center;">Четверг</td>
		<td style="text-align: center;">Пятница</td>
		<td style="text-align: center;">Суббота</td>
		<td style="text-align: center;">Воскресенье</td>
	</tr>
	</thead>

	<tbody>

	<?php foreach($rows as $row){?>
	<tr>
		<td><?php echo $row['Post_Name'];?></td>
		<td><?php echo $row['Person_Fio'];?></td>
		<td><a href="#" onclick="getWnd('swChoiceLpuBuildingOfficeEditWindow').show({formParams: {
			LpuBuildingOffice_Number: '<?php echo $row['LpuBuildingOffice_Number'];?>',
			Person_Fio: '<?php echo $row['Person_Fio'];?>',
			LpuBuildingOfficeMedStaffLink_id: '<?php echo $row['LpuBuildingOfficeMedStaffLink_id'];?>',
			LpuBuildingOffice_id: '<?php echo $row['LpuBuildingOffice_id'];?>',
			MedStaffFact_id: '<?php echo $row['MedStaffFact_id'];?>',
			LpuBuildingOfficeMedStaffLink_begDate: '<?php echo $row['LpuBuildingOfficeMedStaffLink_begDate'];?>',
			LpuBuildingOfficeMedStaffLink_endDate: '<?php echo $row['LpuBuildingOfficeMedStaffLink_endDate'];?>'
		}}); return false;"><?php echo  (! empty($row['LpuBuildingOffice_Number'])?$row['LpuBuildingOffice_Number']:'Выбор кабинета');?></a></td>
		<td><?php echo $row['LpuRegion_Name'];?></td>


		<?php for($weekDay=1; $weekDay<=7; $weekDay++){?>
			<?php

			// 86400 - 1 day
			$curDate = date('Y-m-d',strtotime($mondayDate) + ((86400 * $weekDay) - 86400));

			$isExistVisitTime = false;

			if(
				isset($row['LpuBuildingOfficeVizitTimeData']) &&
				! empty($row['LpuBuildingOfficeVizitTimeData']) &&
				is_array($row['LpuBuildingOfficeVizitTimeData'])
			){
				if(isset($row['LpuBuildingOfficeVizitTimeData'][$weekDay])){
					$rowVizitTime = $row['LpuBuildingOfficeVizitTimeData'][$weekDay];
					$isExistVisitTime = true;
					?>
					<td><a href="#" onclick="getWnd('swChoiceVizitTimeEditWindow').show({formParams: {
						LpuBuildingOfficeMedStaffLink_id: '<?php echo $row['LpuBuildingOfficeMedStaffLink_id'];?>',
						LpuBuildingOfficeVizitTime_id: '<?php echo $rowVizitTime['LpuBuildingOfficeVizitTime_id']; ?>',
						CalendarWeek_id: '<?php echo $rowVizitTime['CalendarWeek_id']; ?>',
						curDate: '<?php echo $curDate?>',
						LpuBuildingOfficeVizitTime_begDate: '<?php echo $rowVizitTime['LpuBuildingOfficeVizitTime_begDate']; ?>',
						LpuBuildingOfficeVizitTime_endDate: '<?php echo $rowVizitTime['LpuBuildingOfficeVizitTime_endDate']; ?>'
					}}); return false;"><?php echo $rowVizitTime['LpuBuildingOfficeVizitTime_begDate']?> - <?php echo $rowVizitTime['LpuBuildingOfficeVizitTime_endDate']?></a></td>
					<?php
					unset($rowVizitTime);
				}
			}

			if($isExistVisitTime == false){
				?>
				<td>


					<?php /*
 					// Пока не нужно
 					?>
					<a href="#" onclick="getWnd('swChoiceVizitTimeEditWindow').show({formParams: {
						LpuBuildingOfficeMedStaffLink_id: '<?php echo $row['LpuBuildingOfficeMedStaffLink_id'];?>',
						CalendarWeek_id: '<?php echo $weekDay; ?>',
						curDate: '<?php echo $curDate?>'
					}}); return false;">Выбор времени приёма</a>
 					<?php */ ?>

				</td>
				<?php
			}
			?>
		<?php } ?>
	</tr>
	<?php } ?>
	</tbody>
</table>
