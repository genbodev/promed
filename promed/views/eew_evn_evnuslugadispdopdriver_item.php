<?php

$align_class = ''; $onMouseOver = '';  $onMouseOut = '';

// выравнивание по верхнему краю
if (!empty($DispDopDirections_Count) && $DispDopDirections_Count > 1 )  {$align_class = " va-top";}

$enableDirections = !empty($DispDopDirections_Count);

// если есть назначения или направления, не показываем меню добавления назначения на строке TR
// вместо этого меню добавления назначения будет показываться по наведению на ячейку назначения\направления
if (!$enableDirections) {
    $onMouseOver = "if (isMouseLeaveOrEnter(event, this))
		document.getElementById('EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar').style.display='block'";

    $onMouseOut = "if (isMouseLeaveOrEnter(event, this))
		document.getElementById('EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar').style.display='none'";
}

if (!empty($DispClass_id) && $DispClass_id == 26 && getRegionNick() == 'perm') {

    switch($UslugaComplex_Code) {
        case 'B04.029.002': // Профилактический прием (осмотр, консультация) врача-офтальмолога
            if (!havingGroup('DrivingCommissionOphth') && !havingGroup('DrivingCommissionTherap')) {
                $accessType = 'view';
            }
            break;
        case 'B04.035.002': // Профилактический прием (осмотр, консультация) врача-психиатра
            if (!havingGroup('DrivingCommissionPsych') && !havingGroup('DrivingCommissionTherap')) {
                $accessType = 'view';
            }
            break;
        case 'B04.036.002': // Профилактический прием (осмотр, консультация) врача психиатра-нарколога
            if (!havingGroup('DrivingCommissionPsychNark') && !havingGroup('DrivingCommissionTherap')) {
                $accessType = 'view';
            }
            break;
        case 'B04.047.002': // Профилактический прием (осмотр, консультация) врача-терапевта
            if (!havingGroup('DrivingCommissionTherap')) {
                $accessType = 'view';
            }
            break;
    }
}

$linkName = 'edit';
if (!empty($accessType)) { $linkName = $accessType; }
?>
<tr id="EvnUslugaDispDop_{DopDispInfoConsent_id}"
    class="list-item<?php echo $align_class ?>"
    onmouseover="<?php echo $onMouseOver; ?>"
    onmouseout="<?php echo $onMouseOut; ?>"
>
    <td><?php if (empty($EvnUslugaDispDop_id) && !empty($accessType) && $accessType != 'edit') { echo $SurveyType_Name; }
        else {?>
            <a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_<?php echo $linkName; ?>" class=""
               title="<?php echo empty($accessType) || $accessType == 'edit' ? "Редактировать" : "Просмотреть"; ?>
		   ">{SurveyType_Name}
            </a>
        <?php } ?>
    </td>
    <td>{EvnUslugaDispDop_didDate}</td>
    <td class="prescr">{EvnPrescrDispDop}</td>
    <?php if ($enableDirections)  { ?><td class="toolbar"></td><?php }
    else { ?>
        <td class="toolbar">
            <div id="EvnUslugaDispDop_{DopDispInfoConsent_id}_toolbar" class="toolbar">
                <a id="EvnUslugaDispDop_{DopDispInfoConsent_id}_add"
                   class="button icon icon-add16" title="Создать назначение"><span></span>
                </a>
            </div>
        </td>
    <?php }?>
</tr>