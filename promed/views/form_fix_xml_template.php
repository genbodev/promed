<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<title>Проверка и правки данных шаблонов и документов</title>
<style type="text/css">
form, span, div { font-family: tahoma, verdana; font-size: 10pt; }
.do-action { color: red; }
</style>
</head>

<body>
<h3>Сводная информация об имеющихся ошибках и количестве записей, которые нуждаются в исправлении</h3>
<form id="form_fix_xml_template" method="post" action="/?c=EvnXmlConvert&m=index">
<input id="form_fix_xml_template_action" name="action" type='hidden'  value=""/>
<?php
if ($message) {
?><div class="do-action">{message}</div><?php
}
?>
<fieldset><legend>Шаблоны:</legend>
    <div>Число шаблонов, которые были неправильно удалены: <?php
		if ($XmlTemplate_notCorrectDeletedCnt > 0) {
			?><strong class="do-action">{XmlTemplate_notCorrectDeletedCnt}</strong>
            <input type="button" value="Исправить"
                   onclick="{document.getElementById('form_fix_xml_template_action').value='fixXmlTemplateNotCorrectDeleted'; document.getElementById('form_fix_xml_template').submit()}"/>
			<?php
		} else {
			?>{XmlTemplate_notCorrectDeletedCnt}<?php
		}
		?></div>
    <div>Число шаблонов с неизвестным типом: <?php
		if ($XmlTemplate_withUndefinedTypeCnt > 0) {
			?><strong class="do-action">{XmlTemplate_withUndefinedTypeCnt}</strong>
            <input type="button" value="Определить тип для шаблонов"
                   onclick="{document.getElementById('form_fix_xml_template_action').value='fixXmlTemplateWithUndefinedType'; document.getElementById('form_fix_xml_template').submit()}"/>
			За один запрос может быть обработано только 20 шаблонов<?php
		} else {
			?>{XmlTemplate_withUndefinedTypeCnt}<?php
		}
		?></div>
    <div>Число шаблонов, которые возможно конвертировать автоматически в новый формат: <?php
		if ($XmlTemplate_allowAutoConvertCnt > 0) {
			?><strong class="do-action">{XmlTemplate_allowAutoConvertCnt}</strong>
            <input type="button" value="Конвертировать в новый формат"
                   onclick="{document.getElementById('form_fix_xml_template_action').value='autoConvertXmlTemplate'; document.getElementById('form_fix_xml_template').submit()}"/>
			<?php
		} else {
			?>{XmlTemplate_allowAutoConvertCnt}<?php
		}
		?></div>
	</fieldset>
<fieldset><legend>Документы:</legend>
    <div>Число документов с неизвестным типом шаблона: <?php
		if ($EvnXml_withUndefinedTemplateTypeCnt > 0) {
			?><strong class="do-action">{EvnXml_withUndefinedTemplateTypeCnt}</strong>
			<?php
			if ($XmlTemplate_withUndefinedTypeCnt > 0) {
				?>Перед исправлением необходимо <b>определить тип для шаблонов</b>, у которых он не указан!<?php
			} else {
				?>
                <input type="button" value="Определить тип шаблона у документов"
                       onclick="{document.getElementById('form_fix_xml_template_action').value='fixEvnXmlWithUndefinedXmlTemplateType'; document.getElementById('form_fix_xml_template').submit()}"/>
                За один запрос может быть обработано только 100 документов<?php
			}
		} else {
			?>{EvnXml_withUndefinedTemplateTypeCnt}<?php
		}
		?></div>
    <div>Число документов с неизвестным типом: <?php
		if ($EvnXml_withUndefinedTypeCnt > 0) {
			?><strong class="do-action">{EvnXml_withUndefinedTypeCnt}</strong>
			<?php
			if ($EvnXml_withUndefinedTemplateTypeCnt > 0) {
				?>Перед исправлением необходимо <b>определить тип шаблона у документов</b>!<?php
			} else {
				?>
                <input type="button" value="Определить тип документов"
                       onclick="{document.getElementById('form_fix_xml_template_action').value='fixXmlType'; document.getElementById('form_fix_xml_template').submit()}"/>
                За один запрос может быть обработано только 100 документов
				<?php
			}
		} else {
			?>{EvnXml_withUndefinedTypeCnt}<?php
		}
		?></div>
    <div>Число документов без атрибутов шаблона: <?php
		if ($EvnXml_withoutTemplateDataCnt > 0) {
			?><strong class="do-action">{EvnXml_withoutTemplateDataCnt}</strong>
			<?php
			if ($XmlTemplate_withUndefinedTypeCnt > 0) {
				?>Перед исправлением необходимо <b>определить тип для шаблонов</b>, у которых он не указан!<?php
			} else {
				?>
                <input type="button" value="Скопировать атрибуты шаблона в документ"
                       onclick="{
document.getElementById('form_fix_xml_template_action').value='copyXmlTemplateDataToEvnXml'; document.getElementById('form_fix_xml_template').submit()
}"/> За один запрос может быть обработано только 20 документов<?php
			}
		} else {
			?>{EvnXml_withoutTemplateDataCnt}<?php
		}
		?></div>
    </fieldset>
</form>
</body>

</html>