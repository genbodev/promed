<?php $onMouseOver =""; $onMouseOut = "";
        $onMouseOver = "if (isMouseLeaveOrEnter(event, this))
                document.getElementById('{EvnPrescrDispDop_id}_toolbar').style.display='block'";

        $onMouseOut = "if (isMouseLeaveOrEnter(event, this))
                document.getElementById('{EvnPrescrDispDop_id}_toolbar').style.display='none'";
?>
<div
    id="EvnPrescrDispDop_{EvnPrescrDispDop_id}"
    class="EvnUslugaDispDopDirInfo prescriptions"
     onmouseover="<?php echo $onMouseOver; ?>"
     onmouseout="<?php echo $onMouseOut; ?>"
>
    <div class='dirinfoinner'><img src='/img/icons/place_icon.png' />&nbsp;{RecTo}
        <?php if (isset($EvnXml_id) && !empty($EvnXml_id)) { ?>
            <span class="collapsible" id="EvnUslugaDispDop_{DopDispInfoConsent_id}_xml">Результаты...</span>
        <?php } ?>
    </div>
    <div class='dirinfoinner'>
        <img src='/img/icons/napr_icon.png' />&nbsp;<span id='EvnPrescrDispDop_{EvnPrescrDispDop_id}_viewdir' class='link' title='Просмотр направления'>Направление {EvnDirection_Num}</span>
    </div>
    <div class='dirinfoinner'><img src='/img/icons/time_icon.png' />&nbsp;{RecDate}</div>
    <div class="prescr-toolbar" id="{EvnPrescrDispDop_id}_toolbar">
        <?php if (empty($accessType) || $accessType == 'edit') { ?>
            <a id="EvnPrescrDispDop_{EvnPrescrDispDop_id}_openDirActionMenu"
               class="button prescrMenuButton"><span></span>
            </a>
        <?php } ?>
        <a id="EvnPrescrDispDop_{EvnPrescrDispDop_id}_add"
           class="button icon icon-add16 btn-dispdop-add-prescr"
           title="Создать назначение"><span></span></a>
    </div>
</div>