<?php if($EvnSection_id != $EvnSection_pid)
{
?>
Создан: {insDT} {ins_Name}
<hr>
<?php
}
?>
{EvnSection_data}
<hr>
<?php echo (($EvnSection_id == $EvnSection_pid)?'':'{EvnSectionNarrowBed}'); ?>
{EvnDrug}    
{EvnUslugaStac}

