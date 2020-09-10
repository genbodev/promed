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
            {EvnUslugaTelemed_data}
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
    '{section_id}' => 'EvnUslugaTelemed_{EvnUslugaTelemed_id}',
    '{buttons}' => '',
    '{files}' => '',
    '{protocol}' => '',
    '{frames}' => '',
);
$mainTplData['{onmouseover}'] = strtr($onMouseOverTpl, $mainTplData);
$mainTplData['{onmouseout}'] = strtr($onMouseOutTpl, $mainTplData);
if ($isAllowStudyViews && !empty($StudyViews)) {
    $frameTpl = '<div class="frame clear">
        <div class="data-table"{visible}>
            <div class="caption">
            <h2><span >Прикрепленное изображение</span></h2>
            </div>
            <div style="display: block; height: 700px; position: relative;width: 100%;" >
                {StudyView}
            </div>
        </div>
    </div>';
    foreach ($StudyViews as $StudyView) {
        if (is_string($StudyView)) {
            $frameTplData = array(
                // если существует study_id исследования в базе
                '{visible}' => ((1 == 1) ? '' : ' style="display: none;"'),
                '{StudyView}' => trim($StudyView),
            );
            if (!empty($frameTplData['{StudyView}'])) {
                $mainTplData['{frames}'] .= strtr($frameTpl, $frameTplData);
            }
        }
    }
}

$mainTplData['{buttons}'] = '<a id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}_editEvnUslugaTelemed" class="button icon icon-edit16" title="Редактировать стат.данные услуги"><span></span></a>
                <a id="EvnUslugaTelemed_{EvnUslugaTelemed_id}_printEvnUslugaTelemed" class="button icon icon-print16" title="Печать"><span></span></a>';

$mainTplData['{buttons}'] .= '<div class="emd-here" data-objectname="EvnXml" data-disabledsign="1" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}"></div>';

if ($isAllowMediaData && $isVisibleEvnMediaData) {
    $mainTplData['{files}'] = '{EvnMediaData}';
}

// убрал класс data-table, чтобы таблицы в протоколе выглядели также, как в редакторе шаблонов
$protocolTpl = '<div id="{section_id}" class="read-only clear NewStyleDoc" onmouseover="{onmouseover}" onmouseout="{onmouseout}">
            {content}
    </div>';
$protocolTplData = array(
    '{section_id}' => 'EvnUslugaTelemed_protocol_{EvnUslugaTelemed_id}',
    '{content}' => '',
);
$protocolTplData['{onmouseover}'] = strtr($onMouseOverTpl, $protocolTplData);
$protocolTplData['{onmouseout}'] = strtr($onMouseOutTpl, $protocolTplData);

if (!strpos($EvnUslugaTelemed_protocol, 'Нет данных документа')) {
    $protocolTplData['{content}'] = strtr('
            <div id="{section_id}_content" class="clear WrapDoc">
                <div id="{section_id}_toolbar" class="toolbar" style="float: right;">
            	</div> {EvnUslugaTelemed_protocol}
            </div>', $protocolTplData);
}
$mainTplData['{protocol}'] = strtr($protocolTpl, $protocolTplData);

echo strtr($mainTpl, $mainTplData);