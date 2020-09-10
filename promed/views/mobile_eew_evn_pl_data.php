<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
?> 
<div><h2>Случай амбулаторно-поликлинического лечения №&nbsp;<strong>{EvnPL_NumCard}</strong><br />{EvnPL_setDate}-{EvnPL_disDate}<br />{Lpu_Nick}</h2><div>
<p>Кем направлен: <?php 
	echo empty($PrehospDirect_Name)?$empty_str:'{PrehospDirect_Name} ';
	echo empty($LpuSectionD_Name)?(($PrehospDirect_Code==1)?$empty_str:''):'{LpuSectionD_Name}';
	echo empty($OrgD_Name)?(in_array($PrehospDirect_Code,array(2,3,4,5,6))?$empty_str:''):'{OrgD_Name}';
	echo empty($EvnDirection_Num)?'':'<br />Направление № {EvnDirection_Num} от {EvnDirection_setDate}'; 
	?><br />Диагноз направившего учреждения: <?php echo empty($DiagD_Name)?$empty_str:'{DiagD_Code} {DiagD_Name}'; ?></p><p>Травма: <?php echo (empty($PrehospTrauma_Name))?$empty_str:'{PrehospTrauma_Name}. Противоправная: {IsUnlaw_Name}'; ?><br />Нетранспортабельность: {IsUnport_Name}</p>	<p><span>Диагноз</span>:<span>{Diag_Name}</span>.<br />Характер заболевания: <span><?php echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}'; ?></span></p><p>Случай закончен: <strong>{IsFinish_Name}</strong>. Результат: <?php echo empty($ResultClass_Name)?$empty_str:'<strong>{ResultClass_Name}</strong>'; ?></p></div></div>
    
