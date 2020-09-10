<html class="x-ux-grid-printer">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link href="extjs4/src/ux/grid/gridPrinterCss/print.css" rel="stylesheet" type="text/css">
    <title>Вызовы</title></head>
<body class="x-ux-grid-printer-body">
<div class="x-ux-grid-printer-noprint x-ux-grid-printer-links">
    <button class="x-ux-grid-printer-linkprint" href="javascript:void(0);" onclick="window.print();">Печать</button>
    <button class="x-ux-grid-printer-linkclose" href="javascript:void(0);" onclick="window.close();">Закрыть</button>
</div>
<h1></h1>
<table>
    <tbody>
    <tr>
        <th style="text-align: left">Дата и время</th>
        <th style="text-align: left">№ В/Д</th>
        <th style="text-align: left">№ В/Г</th>
        <th style="text-align: left">Пациент</th>
        <th style="text-align: left">Возраст</th>
        <th style="text-align: left">Адрес</th>
        <th style="text-align: left">Тип вызова</th>
        <th style="text-align: left">Вид вызова</th>
        <th style="text-align: left">Повод</th>
        <th style="text-align: left">Статус вызова</th>
        <th style="text-align: left">Доп. информация</th>
        <th style="text-align: left">СМП / НМП</th>
        <th style="text-align: left">Диагноз</th>
        <th style="text-align: left">Подразделение СМП</th>
        <th style="text-align: left">Бригада</th>
        <th style="text-align: left">МО НМП</th>
        <th style="text-align: left">МО передачи актива</th>
    </tr>
    <?php $data = isset($data) ? $data : $_ci_vars;
    foreach($data as $key => $val):?>
    <tr class="odd">
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_prmDate']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_Numv']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_Ngod']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['Person_FIO']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['personAgeText']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['Adress_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallType_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_IsExtraText']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpReason_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCardStatusType_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_Comm']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['CmpCallCard_IsExtra']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['Diag']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['LpuBuilding_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['EmergencyTeam_Num']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['Lpu_NMP_Name']?></div>
        </td>
        <td>
            <div style="text-align: left;" ><?php echo $val['ActiveVisitLpu_Nick']?></div>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
</body>
</html>