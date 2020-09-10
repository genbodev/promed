<html>
	<head>
		<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
		<title>Сравнительный отчет данных медицинских организаций</title>
	</head>
	<body>

		<p>Сравнительный отчет данных медицинских организаций на <?= $compareDate?></p>

		<table width="100%" class="compare-table">
			<tr>
				<td></td>
				<td colspan="9">Данные ЕЦИС</td>
				<td colspan="10">Данные ПРОМЕД</td>
			</tr>
			<tr>
				<th>№ п/п</th>
				<th>Фамилия</th>
				<th>Имя</th>
				<th>Отчество</th>
				<th>Дата рождения</th>
				<th>Отделение</th>
				<th>Должность</th>
				<th>Ставка</th>
				<th>Вид оклада</th>
				<th>Статус</th>

				<th>Фамилия</th>
				<th>Имя</th>
				<th>Отчество</th>
				<th>Дата рождения</th>
				<th>МО</th>
				<th>Отделение</th>
				<th>Должность</th>
				<th>Ставка</th>
				<th>Вид оклада</th>
				<th>Статус</th>
			</tr>
			<?php foreach($mspersonList as $key => $value):?>
				<tr>
					<?php if(!empty($value['EcisPerson_SurName'])){?>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['insexEcis']?></td>

						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisPerson_SurName']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisPerson_FirName']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisPerson_SecName']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisPerson_BirthDay']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisLpuSection_Name']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisDolgnost_Name']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisMedStaffFact_Stavka']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisPostOccupationTypeName']?></td>
						<td<?php if($value['CountProMs']>1) echo(' rowspan="'.$value['CountProMs'].'"');?>><?= $value['EcisMoveInOrgRecordType_id']?></td>
					<?php } ?>
					<td><?= $value['Person_SurName']?></td>
					<td><?= $value['Person_FirName']?></td>
					<td><?= $value['Person_SecName']?></td>
					<td><?= $value['Person_BirthDay']?></td>
					<td><?= $value['Lpu_Nick']?></td>
					<td><?= $value['LpuSection_Name']?></td>
					<td><?= $value['Dolgnost_Name']?></td>
					<td><?= $value['MedStaffFact_Stavka']?></td>
					<td><?= $value['PostOccupationTypeName']?></td>
					<td><?= $value['MoveInOrgRecordType_id']?></td>

				</tr>
			<?php endforeach?>
		</table>


	</body>
</html>