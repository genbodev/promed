<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>     
        <div >
            <div >
               <p>Дата: {EvnVizitPL_setDate} {EvnVizitPL_setTime}&nbsp;&nbsp;&nbsp;Место: <?php echo empty($ServiceType_Name)?$empty_str:'{ServiceType_Name}'; ?>
			   <br />Отделение: {LpuSection_Name}&nbsp;&nbsp;&nbsp;Врач: {MedPersonal_Fin}</p>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Прием: <?php echo empty($VizitClass_Name)?$empty_str:'{VizitClass_Name}'; ?></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Цель посещения: <?php  echo empty($VizitType_Name)?$empty_str:'{VizitType_Name}';?></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;'><?php echo ($VizitType_SysNick == 'prof')?(empty($ProfGoal_Name)?$empty_str:'{ProfGoal_Name}'):''; ?><br />Вид оплаты: {PayType_Name}.</div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0px;'>Основной диагноз: 
				<?php 
					echo '{Diag_Code} ';
					$diag_str = $empty_str;
					if (!empty($Diag_Name))
					{
						$diag_str = '{Diag_Name}.';
					}
					echo $diag_str;
					
				?></div>
				</div>
                <div style='clear:both;'><div style='float:left;padding:5px 0px;'>Характер заболевания: <?php  echo empty($DeseaseType_Name)?$empty_str:'{DeseaseType_Name}';?></div></div>
            </div>
        </div>
