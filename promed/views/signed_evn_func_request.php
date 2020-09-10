<h3>Посещение пациентом поликлиники</h3>
<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
?> 
Номер направления: <?php echo empty($EvnDirection_Num)?$empty_str:'{EvnDirection_Num}';?><br />
Дата направления: <?php echo empty($EvnDirection_setDT)?$empty_str:'{EvnDirection_setDT}';?><br />
Кем направлен: <?php echo empty($PrehospDirect_Name)?$empty_str:'{PrehospDirect_Name}';?><br />
Организация: <?php echo empty($Lpu_Nick)?$empty_str:'{Lpu_Nick}';?><br />
Отделение: <?php echo empty($LpuSection_Name)?$empty_str:'{LpuSection_Name}';?><br />
Врач: <?php echo empty($MedPersonal_Fin)?$empty_str:'{MedPersonal_Fin}';?><br />
Cito!: <?php echo empty($EvnDirection_IsCito)?$empty_str:'{EvnDirection_IsCito}';?><br />
Вид оплаты: <?php echo empty($PayType_Name)?$empty_str:'{PayType_Name}';?><br />