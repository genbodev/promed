<?php
/*
 * Обрабатываем данные по зубам этого сегмента челюсти
 * Формируем и парсим шаблоны зубов этого сегмента челюсти
 */
$jawPart = new SwJawPart($JawPartType_Code);
$ToothCellList = '';
$ToothCodeCellList = '';
$ToothStateClassCodeCellList = '';
if ($jawPart->isTop()) {
$toothCellTpl = '<td id="t{Person_id}_{history_date}_{Tooth_Code}_{position}_{Tooth_SysNum}" class="toothStates">
<div style="width:45px;height:50px;">{ToothLayers}</div></td>';
}else{
	$toothCellTpl = '<td id="t{Person_id}_{history_date}_{Tooth_Code}_{position}_{Tooth_SysNum}" class="toothStates">
<div style="width:45px;height:50px;margin-top: -50px; ">{ToothLayers}</div></td>';
}
$statesTopCellTpl = '<td style="border: 0; text-align: center; font-size: 10pt;" class="statesLabelTop">{CodeList}</td>';
$statesBotCellTpl = '<td style="border: 0; text-align: center; font-size: 10pt;" class="statesLabelBot">{CodeList}</td>';
$numberCellTpl = '<td style="border: 0; text-align: center; font-size: 10pt; width: 30px;" class="toothNumber">{Tooth_Code}</td>';
/*$toothLayerTpl = '<div class="tooth{Tooth_Code} {className} layer{order}" {attr} style="z-index:{z-index};width:42px;height:77px;display: {display};position:absolute;background-image:url(\'/img/toothmap/{Image_Name}.png\')">

</div>';*/
$toothLayerTpl ='<div class="tooth{Tooth_Code} {className} layer{order}" style="display: {display};width:42px;height:50px;position:absolute;">
<img src="/img/{folder}/{Image_Name}.png" width="42" height="77" />
</div>';

foreach ($ToothList as $row) {
	$tooth = new SwTooth($row['Tooth_Code'], $jawPart, $row['states']);
	$position = $tooth->getPosition();
	$toothCellData = array(
		'{history_date}' => str_replace(array(' ','-',':'), array(''), $history_date),
		'{Person_id}' => $Person_id,
		'{position}' => $position,
		'{Tooth_SysNum}' => $row['Tooth_SysNum'],
		'{Tooth_Code}' => $row['Tooth_Code'],
		'{ToothLayers}' => '',
	);
	$ToothStateClassCodeList = $tooth->getStateClassCodeList();
	$layers = ToothMap::getLayers($tooth, $ToothStateClassCodeList);

	foreach ($layers as $order => $layer) {
		$layer['{order}'] = $order;
		$toothCellData['{ToothLayers}'] .= strtr($toothLayerTpl, $layer);
	}
	$ToothCellList .= strtr($toothCellTpl, $toothCellData);
	$ToothCodeCellList .= strtr($numberCellTpl, array(
		'{Tooth_Code}' => $row['Tooth_Code'],
	));
	$str='';
	foreach ($ToothStateClassCodeList as $s) {
		$str.='<div>'.$s.'</div>';
	}
	if ($jawPart->isTop()) {
	$ToothStateClassCodeCellList .= strtr($statesTopCellTpl, array(
		'{CodeList}' => $str,
	));	
	}else{
	$ToothStateClassCodeCellList .= strtr($statesBotCellTpl, array(
		'{CodeList}' => $str,
	));	
	}
	
}
/*
 * Данные по зубам этого сегмента челюсти обработаны
 * Формируем и парсим шаблон сегмента челюсти, выводим результат
 */
$toothRowTpl = '<tr class="toothRow">{ToothCellList}</tr>';
$statesRowTpl = '<tr class="statesRow">{ToothStateClassCodeCellList}</tr>';
$numberRowTpl = '<tr class="numberRow">{ToothCodeCellList}</tr>';
$tableTpl = '<table class="JawPartType{JawPartType_Code}" style="border-collapse: collapse; font-family: tahoma,arial,helvetica,sans-serif;"><tbody>';
if ($jawPart->isTop()) {
	$tableTpl .= ($statesRowTpl . $toothRowTpl . $numberRowTpl);
} else {
	$tableTpl .= ($numberRowTpl . $toothRowTpl . $statesRowTpl);
}
$tableTpl .= '</tbody></table>';

$tableData = array(
	'{JawPartType_Code}' => $JawPartType_Code,
	'{ToothCellList}' => $ToothCellList,
	'{ToothCodeCellList}' => $ToothCodeCellList,
	'{ToothStateClassCodeCellList}' => $ToothStateClassCodeCellList,
);

echo strtr($tableTpl, $tableData);