<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<title>Автоматическое прикрепление</title>
<style type="text/css">
label, form, span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #000; }
.required { color: red; }
.label { width: 150px; float: left;}
.select { width: 300px; }
</style>
</head>

<body>
<form id="auto_attach_form" method="post" action="/?c=AutoAttach&m=doAutoAttach">

<div class="label"><label for="form_Lpu_id"><strong class="required">*</strong>ЛПУ:</label></div>
<select name="Lpu_id" id="form_Lpu_id" class="select">
{lpus_data}
<option value="{Lpu_id}" {Lpu_IsChecked}>{Lpu_Nick}</option>
{/lpus_data}
</select>
<BR />
<div class="label"><label for="form_LpuAttachType_id" class="label"><strong class="required">*</strong>Тип прикрепления:</label></div>
<select name="LpuAttachType_id" id="form_LpuAttachType_id" class="select">
{lpuatts_data}
<option value="{LpuAttachType_id}" {LpuAtt_IsChecked}>{LpuAttachType_Name}</option>
{/lpuatts_data}
</select>
<BR />
<input type="submit" value="Проставить участки"/>
<BR />
<input id="otkat_field" name="otkat" type='hidden'  value="1"/>
<input type="button" value="Откатить автоматически проставленные участки" onclick="{document.getElementById('otkat_field').value=2; document.getElementById('auto_attach_form').submit()}"/>
</form>
</body>

</html>