<?php
$isAllowMediaData = true;
$isAllowStudyViews = true;
$regionNum = getRegionNumber();
if (!empty($isLab) && $regionNum == 63) {
	//Не отображать разделы "Файлы" и "Прикрепленное изображение" для лабораторных исследований в Самаре
	$isAllowMediaData = false;
	$isAllowStudyViews = false;
}

$onMouseOverTpl = "if (isMouseLeaveOrEnter(event, this)) document.getElementById('{section_id}_toolbar').style.display='block'";
$onMouseOutTpl = "if (isMouseLeaveOrEnter(event, this)) document.getElementById('{section_id}_toolbar').style.display='none'";
$mainTpl = '
<div id="{section_id}" class="frame evn_usluga_par" onmouseover="{onmouseover}" onmouseout="{onmouseout}">
    <div class="">
    <div class="columns">
        <div class="left">
            {EvnUslugaPar_data}
        </div>
        <div class="right">
            <div id="{section_id}_toolbar" class="toolbar">
            {buttons}
            </div>
        </div>
    </div>
    <div class="noprint">
    {files}
    </div>
    </div>
    {protocol}
</div>
{frames}';
$mainTplData = array(
    '{section_id}' => 'EvnUslugaPar_{EvnUslugaPar_id}',
    '{buttons}' => '',
    '{files}' => '',
    '{protocol}' => '',
    '{frames}' => '',
);
$mainTplData['{onmouseover}'] = strtr($onMouseOverTpl, $mainTplData);
$mainTplData['{onmouseout}'] = strtr($onMouseOutTpl, $mainTplData);
if ($isAllowStudyViews && !empty($StudyViews[0]['view'])) {
    $frameTpl = '<div class="frame clear">
        <div class="data-table"{visible}>
            <div class="caption">
            <h2><span >Прикрепленное изображение</span></h2>{newExternalLink}{externalLink}
            </div>
            <div style="display: block; height: 700px; position: relative;width: 100%;" >
                {StudyView}
            </div>
        </div>
    </div>';
    foreach ($StudyViews as $StudyView) {
        if (!empty($StudyView['view']) && is_string($StudyView['view'])) {
            $frameTplData = array(
                // если существует study_id исследования в базе
                '{visible}' => ((1 == 1) ? '' : ' style="display: none;"'),
				'{StudyView}' => trim($StudyView['view']),
				'{newExternalLink}' => ((!empty($StudyView['newLink'])) ? "<a target=\"_blank\" href=\"" . $StudyView['newLink']. "\" style=\"float:right;display:block;\"> Ссылка DigiPacs </a>" : ''),
                '{externalLink}' => ((!empty($StudyView['link']))?$StudyView['link']:'')
            );
            if (!empty($frameTplData['{StudyView}'])) {
                $mainTplData['{frames}'] .= strtr($frameTpl, $frameTplData);
            }
        }
    }
}

if (count($LabStydyResult)) {
	$LabStydyResultData = '';
	for($i = 1; $i <= count($LabStydyResult); $i++) {
		$LabStydyResultData .= "<p><a href=\"#\" onClick=\"getWnd('swEvnXmlViewWindow').show({ EvnXml_id: '{$LabStydyResult[($i-1)]['EvnXml_id']}', title: 'Результат лабораторного исследования' });\">Результат исследования {$i}</a></p>";
	}
    $mainTplData['{frames}'] .= '<div class="frame noprint">'.$LabStydyResultData.'</div>';
}

$mainTplData['{buttons}'] = '<a id="EvnUslugaPar_data_{EvnUslugaPar_id}_editEvnUslugaPar" class="button icon icon-edit16" title="Редактировать стат.данные услуги"><span></span></a>';
if (!empty($EvnPrescr_id)) {
	$mainTplData['{buttons}'] .= '<a id="EvnUslugaPar_data_{EvnUslugaPar_id}_editEvnUslugaParSimple" class="button icon icon-edit16" title="Редактировать привязку услуги"><span></span></a>';
}
$mainTplData['{buttons}'] .= '<a id="EvnUslugaPar_{EvnUslugaPar_id}_printEvnUslugaPar" class="button icon icon-print16" title="Печать"><span></span></a>';
$mainTplData['{buttons}'] .= '<div class="emd-here" data-objectname="EvnUslugaPar" data-disabledsign="1" data-objectid="{EvnUslugaPar_id}" data-issigned="{EvnUslugaPar_IsSigned}"></div>';

if ($isAllowMediaData && !empty($items)) {
    $mainTplData['{files}'] = '{EvnMediaData}';
}

// убрал класс data-table, чтобы таблицы в протоколе выглядели также, как в редакторе шаблонов
$protocolTpl = '<div id="{section_id}" class="read-only clear NewStyleDoc" onmouseover="{onmouseover}" onmouseout="{onmouseout}">
            {content}
    </div>';
$protocolTplData = array(
    '{section_id}' => 'EvnUslugaPar_protocol_{EvnUslugaPar_id}',
    '{content}' => '',
);
$protocolTplData['{onmouseover}'] = strtr($onMouseOverTpl, $protocolTplData);
$protocolTplData['{onmouseout}'] = strtr($onMouseOutTpl, $protocolTplData);

if (!strpos($EvnUslugaPar_protocol, 'Нет данных документа')) {
	if (empty($isLab)) {
		$protocolTplData['{content}'] = strtr('
			<div id="{section_id}_content" class="clear WrapDoc">
				<div id="{section_id}_toolbar" class="toolbar" style="float: right;">
				<a id="{section_id}_edit" class="button icon icon-edit16" title="Редактировать протокол"><span></span></a>
				<a id="{section_id}_print" class="button icon icon-print16" title="Печать протокола"><span></span></a>
				</div> {EvnUslugaPar_protocol}
			</div>', $protocolTplData);
	} else {
		$protocolTplData['{onmouseover}'] = '';
		$protocolTplData['{onmouseout}'] = '';
		$protocolTplData['{content}'] = strtr('
			<div id="{section_id}_content" class="clear">
				{EvnUslugaPar_protocol}
			</div>', $protocolTplData);
	}
}
$mainTplData['{protocol}'] = strtr($protocolTpl, $protocolTplData);

echo strtr($mainTpl, $mainTplData);