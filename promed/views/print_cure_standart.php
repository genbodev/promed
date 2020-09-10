<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{CureStandart_Name}</title>
<style type="text/css">
td.swcielnumeric { vertical-align: middle; text-align: center; padding: 2px; }
td.swciellevel0 { vertical-align: top; text-align: center; padding: 2px; }
td.swcieltitle { vertical-align: middle; text-align: center; padding: 5px; font-weight: bold; }
h2.swsectiontitle { text-align: center; margin: 10px; }
caption.swprescrtypetitle { text-align: center; font-weight: bold; }
p.blockHeader {color:#666;}
p.blockHeader span {color:#000;}
.tableWrapper {width:840px;}
.tableWrapper table thead tr {border-bottom: 1px solid #999; }
.isExistsInMedService { font-weight: bold; }
.isNotExistsInCureStandart { color:#666; }
.innerCheckBoxWrapper {float: left; margin-right: 5px; margin-top: -2px;}
</style>

</head>

<body class="land" style="margin: 5px; font-family: Tahoma, verdana; font-size: 10pt;">

<div id="CureStandart_{CureStandart_id}" style="text-align: left; width:840px">
	<h2>{CureStandart_Name}</h2>
	<p class="blockHeader"><span>Категория возрастная:</span> {CureStandartAgeGroupType_Name}<br />
		<span>Нозологическая форма:</span> {Diag_Name}<br />
		<span>Код по МКБ-10:</span> {Diag_Code}<br />
		<span>Фаза:</span> {CureStandartPhaseType_Name}<br />
		<span>Стадия:</span> {CureStandartStageType_Name}<br />
		<span>Осложнения:</span> {CureStandartComplicationType_Name}<br />
		<span>Условия оказания:</span> {CureStandartConditionsType_Name}
	</p>
</div>
<h2 class="swsectiontitle">ДИАГНОСТИКА</h2>
<?php

if (count($FuncDiagData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Инструментальная диагностика</caption>
        <thead>
        <tr>
			<?php echo ($print == true)?'':'<td width="30" class="swcieltitle"><div id="FuncDiagData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach($FuncDiagData as $k=>$ld)
		{
			?>
        <tr>
			<?php echo ($print == true)?'':'<td class="swciellevel0"><div id="FuncDiagData_'. $ld['UslugaComplex_id'] .'_checkbox"></div></td>'; ?>
            <td<?php echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':''); ?>><?php echo $ld['name']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
            <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
			<?php
		}
		?>
        </tbody>
    </table>
</div>
<?php
}

if (count($LabDiagData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Лабораторная диагностика</caption>
        <thead>
        <tr>
			<?php echo ($print == true)?'':'<td width="30" class="swcieltitle"><div id="LabDiagData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach($LabDiagData as $k=>$ld)
		{
			$itemsData = array();
			if (!empty($LabItemDiagData) && !empty($LabItemDiagData[$k]) && is_array($LabItemDiagData[$k])) {
				$itemsData = $LabItemDiagData[$k];
			}
			?>
	<tr>
	<?php echo ($print == true)?'':'<td class="swciellevel0"><div id="LabDiagData_'. $ld['UslugaComplex_id'] .'_checkbox"'.(empty($itemsData)?'':' title="отметить все/снять все').'"></div></td>'; ?>
	<td<?php
			echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':'');
		?>><?php
			echo $ld['name'];
		?></td>
        <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
        <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
		<?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
			<?php
			if(!empty($itemsData)) {
				foreach($itemsData as $row) {
					?><tr>
					<?php echo ($print == true)?'':'<td class="swciellevel0"> </td>'; ?>
                    <td><?php
	                    if (false == $print) {
		                    echo '<div id="LabItemDiagData_'.$row['UslugaComplex_id'].'_checkbox" class="innerCheckBoxWrapper"></div>';
	                    }
						?><p class="swchildItem<?php
		                    echo (!empty($row['is_exists_in_ms'])?' isExistsInMedService':'');
		                    echo (empty($row['is_exists_in_cs'])?' isNotExistsInCureStandart':'');
		                    ?>"><?php echo $row['name']; ?></p></td>
                    <td class="swcielnumeric"><?php echo $row['freq']; ?></td>
                    <td class="swcielnumeric"><?php echo $row['kolvo']; ?></td>
					<?php echo ($print == true)?'':'<td class="swcielnumeric">' . $row['count'] . '</td>'; ?>
                </tr><?php
				}
			}
		}
		?>
        </tbody>
    </table>
</div>
<?php
}
?>

<h2 class="swsectiontitle">ЛЕЧЕНИЕ ИЗ РАСЧЕТА {CureStandartTreatment_Duration} ДНЕЙ</h2>
<?php
if (count($LabTreatmentData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Лабораторная диагностика</caption>
        <thead>
        <tr>
            <?php echo ($print == true)?'':'<td width="30" class="swciellevel0"><div id="LabTreatmentData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($LabTreatmentData as $k=>$ld)
        {
            $itemsData = array();
            if (!empty($LabItemTreatmentData) && !empty($LabItemTreatmentData[$k]) && is_array($LabItemTreatmentData[$k])) {
                $itemsData = $LabItemTreatmentData[$k];
            }
            ?>
    <tr>
        <?php
        if (false == $print) {
            echo '<td class="swciellevel0"><div id="LabTreatmentData_'. $ld['UslugaComplex_id'] .'_checkbox"'.(empty($itemsData)?'':' title="отметить все/снять все"').'></div></td>';
        } ?>
        <td<?php
            echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':'');
        ?>><?php
            echo $ld['name'];
        ?></td>
        <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
        <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
        <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
            <?php
            if(!empty($itemsData)) {
                foreach($itemsData as $row) {
                    ?><tr>
                    <?php echo ($print == true)?'':'<td class="swciellevel0"> </td>'; ?>
                    <td><?php
                        if (false == $print) {
                            echo '<div id="LabItemTreatmentData_'.$row['UslugaComplex_id'].'_checkbox" class="innerCheckBoxWrapper"></div>';
                        }
                        ?><p class="swchildItem<?php
                            echo (!empty($row['is_exists_in_ms'])?' isExistsInMedService':'');
                            echo (empty($row['is_exists_in_cs'])?' isNotExistsInCureStandart':'');
                            ?>"><?php echo $row['name']; ?></p></td>
                    <td class="swcielnumeric"><?php echo $row['freq']; ?></td>
                    <td class="swcielnumeric"><?php echo $row['kolvo']; ?></td>
                    <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $row['count'] . '</td>'; ?>
                </tr><?php
                }
            }
        }
        ?>
        </tbody>
    </table>
</div>
<?php
}

if (count($FuncTreatmentData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Инструментальная диагностика</caption>
        <thead>
        <tr>
            <?php echo ($print == true)?'':'<td width="30" class="swciellevel0"><div id="FuncTreatmentData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($FuncTreatmentData as $k=>$ld)
        {
            ?>
        <tr>
            <?php echo ($print == true)?'':'<td class="swciellevel0"><div id="FuncTreatmentData_'. $ld['UslugaComplex_id'] .'_checkbox"></div></td>'; ?>
            <td<?php echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':''); ?>><?php echo $ld['name']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
            <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php
}

if (count($OperData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Инвазивные методы, с анестезией</caption>
        <thead>
        <tr>
			<?php echo ($print == true)?'':'<td width="30" class="swciellevel0"><div id="OperData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach($OperData as $k=>$ld)
		{
			?>
        <tr>
			<?php echo ($print == true)?'':'<td class="swciellevel0"><div id="OperData_'.$ld['UslugaComplex_id'].'_checkbox"></div></td>'; ?>
            <td<?php echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':''); ?>><?php echo $ld['name']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
	        <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
			<?php
		}
		?>
        </tbody>
    </table>
</div>
<?php
}

if (count($ProcData) > 0) {
?>

<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Манипуляции и процедуры и немедикаментозные методы лечения</caption>
        <thead>
        <tr>
			<?php echo ($print == true)?'':'<td width="30" class="swciellevel0"><div id="ProcData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">Ср. кол-во</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. кол-во</td>'; ?>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach($ProcData as $k=>$ld)
		{
			?>
        <tr>
			<?php echo ($print == true)?'':'<td class="swciellevel0"><div id="ProcData_'.$ld['UslugaComplex_id'].'_checkbox"></div></td>'; ?>
            <td<?php echo (!empty($ld['is_exists_in_ms'])?' class="isExistsInMedService"':''); ?>><?php echo $ld['name']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['freq']; ?></td>
            <td class="swcielnumeric"><?php echo $ld['kolvo']; ?></td>
	        <?php echo ($print == true)?'':'<td class="swcielnumeric">' . $ld['count'] . '</td>'; ?>
        </tr>
			<?php
		}
		?>
        </tbody>
    </table>
</div>
<?php
}
if (count($DrugData) > 0) {
?>
<div class="tableWrapper" style="margin-bottom: 2em;">
    <table style="border-collapse: collapse;" width="100%">
        <caption class="swprescrtypetitle">Лекарственное лечение</caption>
        <thead>
        <tr>
			<?php echo ($print == true)?'':'<td width="30" class="swciellevel0"><div id="DrugData_Section_checkbox" title="отметить все/снять все"></div></td>'; ?>
            <td class="swcieltitle">Наименование МНН</td>
            <td width="120" class="swcieltitle">Частота</td>
            <td width="120" class="swcieltitle">ОДД</td>
            <td width="120" class="swcieltitle">ЭКД</td>
            <?php echo ($print == true)?'':'<td width="120" class="swcieltitle">Назн. КД</td>'; ?>
        </tr>
        </thead>
        <tbody>
        {DrugData}
        <tr>
			<?php echo ($print == true)?'':'<td class="swciellevel0"><div id="DrugData_{CureStandartTreatmentDrug_id}_checkbox"></div></td>'; ?>
            <td><?php
	            if ($print == true) {
		            ?>{ActMatters_Name}<?php
	            } else {
		            ?><a href="#" onclick="getWnd('swRlsViewForm').show(&#123;ActMatters_Name:'{ActMatters_Name}'&#125;)">{ActMatters_Name}</a><?php
	            }
	        ?></td>
            <td class="swcielnumeric">{CureStandartTreatmentDrug_FreqDelivery}</td>
            <td class="swcielnumeric">{CureStandartTreatmentDrug_ODD}</td>
            <td class="swcielnumeric">{CureStandartTreatmentDrug_EKD}</td>
	        <?php echo ($print == true)?'':'<td class="swcielnumeric">{DrugCount}</td>'; ?>
        </tr>
        {/DrugData}
        </tbody>
    </table>
</div>
<?php
}
?>
<!--h2 class="swsectiontitle">Консервированная кровь человека и ее компоненты</h2>
<h2 class="swsectiontitle">Питательные смеси</h2-->
 <?php
//var_dump($LabTreatmentData);
/* if ($print == true) var_dump($LabItemDiagData);*/ ?>
</body>

</html>