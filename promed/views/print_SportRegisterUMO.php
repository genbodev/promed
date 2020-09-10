<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>УМО смпортсмена</title>
<style type="text/css">
   td { 
    font-size: 12px; 
    font-family: Tahoma, Geneva, Arial, Helvetica, sans-serif; 
    /*color: #333366; */
   }
</style>
</head>
<body class="land">
<p>УМО спортсмена</p>
<br>
<table border="1" cellspacing="0" cellpadding="8">
	<thead>
		<tr><td>Дата включения в регистр</td><td>{SportRegister_updDT}</td></tr>
		<tr><td>Дата УМО</td><td>{SportRegisterUMO_UMODate}</td></tr>
	</thead>
	<tbody>
		<tr><td>ФИО</td><td>{SFS}</td></tr>
		<tr><td>Возраст спортсмена</td><td>{Person_BirthDay}</td></tr>
		<tr><td>Группа инвалидности</td><td>{InvalidGroupType_name}</td></tr>
		<tr><td>Паралимпийская группа</td><td>{SportParaGroup_name}</td></tr>
		<tr><td>Сборник</td><td>{SportRegisterUMO_IsTeamMember}</td></tr>
		<tr><td>ФИО врача</td><td>{MedPersonal_pname}</td></tr>
		<tr><td>ФИО медсестры</td><td>{MedPersonal_sname}</td></tr>
		<tr><td>Вид спорта</td><td>{SportType_name}</td></tr>
		<tr><td>Спортивная организация</td><td>{SportOrg_name}</td></tr>
		<tr><td>Спортивный разряд</td><td>{SportCategory_name}</td></tr>
		<tr><td>Этап спортивной подготовки</td><td>{SportStage_name}</td></tr>
		<tr><td>ФИО тренера</td><td>{SportTrainer_name}</td></tr>
		<tr><td>Заключение врача</td><td>{UMOResult_name}</td></tr>
		<?php if (!empty($UMOResult_comment)) { ?>
					<tr><td>Причина недопуска</td><td>{UMOResult_comment}</td></tr>
				<?php
			}
		?>
		<tr><td>Допуск с</td><td>{SportRegisterUMO_AdmissionDtBeg}</td></tr>
		<tr><td>Допуск до</td><td>{SportRegisterUMO_AdmissionDtEnd}</td></tr>
	</tbody>
</table>

</body>

</html>


