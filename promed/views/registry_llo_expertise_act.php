Акт экспертизы реестра рецептов, обеспеченных ЛС

Экспертиза: №{ReceptUploadLog_id} от  {ActDateTime}
Организация: {Org_Name}
Реестр:	{RegistryLLO_Num} {RegistryLLO_begDate} - {RegistryLLO_endDate}, {KatNasel_Name}, {WhsDocumentCostItemType_Name}, {DrugFinance_Name}
Количество рецептов: {RegistryLLO_RecordCount}
Сумма на оплату: {RegistryLLO_Sum}

Результат: {ReceptUploadStatus_Name}
Количество ошибок: {RegistryLLO_ErrorCount}
Прошли экспертизу: {AcceptRecept_Count} рецептов на сумму {AcceptRecept_Sum} руб.
Отклонено: {RefuseRecept_Count} рецептов на сумму {RefuseRecept_Sum} руб

Ошибки: <?php if(!isset($errors) || count($errors) == 0) { echo 'нет'; } else { ?>
{errors}
{EvnRecept_Ser} {EvnRecept_Num}: {RegistryReceptErrorType_Type} {RegistryReceptErrorType_Name}{/errors}
<?php } ?>