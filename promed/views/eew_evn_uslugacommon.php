<?php
$isAllowMediaData = true;
$isAllowStudyViews = true;
if (!empty($isLab) && getRegionNumber() == 63) {
	//Не отображать разделы "Файлы" и "Прикрепленное изображение" для лабораторных исследований в Самаре
	$isAllowMediaData = false;
	$isAllowStudyViews = false;
}
?>
<div id="EvnUslugaCommon_{EvnUslugaCommon_id}" class="frame evn_usluga_par" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaCommon_{EvnUslugaCommon_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaCommon_{EvnUslugaCommon_id}_toolbar').style.display='none'">

    <div class="columns">
        <div class="left">

            {EvnUslugaCommon_data}
            
        </div>

        <div class="right">
            <div id="EvnUslugaCommon_{EvnUslugaCommon_id}_toolbar" class="toolbar">
                <a id="EvnUslugaCommon_data_{EvnUslugaCommon_id}_editEvnUslugaCommon" class="button icon icon-edit16" title="Редактировать стат.данные услуги"><span></span></a>
                <a id="EvnUslugaCommon_{EvnUslugaCommon_id}_printEvnUslugaCommon" class="button icon icon-print16" title="Печать"><span></span></a>
            </div>
        </div>

    </div>
    <div class="noprint">
	<?php
    if ($isAllowMediaData && !empty($items)) { ?>
        {EvnMediaData}
	<?php } ?>
    </div>

    <div id="EvnUslugaCommon_protocol_{EvnUslugaCommon_id}" class="data-table read-only" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_toolbar').style.display='none'">

        <div class="caption">
            <div id="EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_toolbar" class="toolbar">
                <a id="EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_edit" class="button icon icon-edit16" title="Редактировать протокол"><span></span></a>
                <!--a id="EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_print" class="button icon icon-print16" title="Печать протокола "><span></span></a-->
            </div>
        </div>

        <?php if (isset($EvnUslugaCommon_protocol) && !strpos($EvnUslugaCommon_protocol, 'Нет данных документа')) { ?>
            <div id="EvnUslugaCommon_protocol_{EvnUslugaCommon_id}_content">
                {EvnUslugaCommon_protocol}
            </div>
        <?php } ?>

    </div>
	
	<div class="clear">
    </div>
</div>
<?php
if ($isAllowStudyViews && !empty($StudyViews)) {
	foreach ($StudyViews as $StudyView)	{
        $trimed_view = trim($StudyView);
        if (!empty($trimed_view)) {
?>
<div class="frame">
	<div class="data-table"<?php echo /*если существует study_id исследования в базе*/((true)?'':' style="display: none;"'); ?>>
		<div class="caption">
		<h2><span >Прикрепленное изображение</span></h2>
		</div>
		<div style="display: block; height: 700px; position: relative;width: 100%;" >
			<?php echo $StudyView; ?>
		</div>
	</div>
</div>
<?php
        }
	} // end foreach $StudyViews
} // end if $isAllowStudyViews
?>
