<h3>Посещение пациентом поликлиники</h3>
<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
?> 
Дата: {EvnVizitPL_setDate} {EvnVizitPL_setTime} <br />
Место: {ServiceType_Name} <br />
Отделение: {LpuSection_Name} <br />
Врач: {MedPersonal_Fin} <br />
Прием: <?php echo empty($VizitClass_Name)?$empty_str:'{VizitClass_Name}';?><br />
Цель посещения: <?php echo empty($VizitType_Name)?$empty_str:'{VizitType_Name}';?><br />
Фактор риска: <?php echo empty($RiskLevel_Name)?$empty_str:'{RiskLevel_Name}';?><br />
Вид оплаты: {PayType_Name}.<br />
Основной диагноз: {Diag_Code} {Diag_Name}</span><br />
Характер заболевания: <?php echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}';?><br />