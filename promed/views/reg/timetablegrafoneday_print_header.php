Расписание приема<br/>
Врач: <?php echo $data['MedPersonal_FIO']?><br/>
<?php echo $data['Lpu_Nick'].", ".$data['LpuUnit_Name']?><br/>
Адрес: <?php echo $data['Address_Address']?><br/>
Отделение:  <?php 
	echo $data['LpuSectionProfile_Name'];
	if ( isset($data['LpuRegion_Name']) ) {
		echo ", ".$data['LpuRegion_Name'];
	}
?><br/>
Дата: <?php echo $data['date']?><br/>
<br/>