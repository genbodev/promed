<h3>Движение пациента</h3>
<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
?> 
{LpuSection_Name} - {EvnSection_setDate} {EvnSection_setTime}<?php echo ((empty($EvnSection_disDate))?'':' - {EvnSection_disDate} {EvnSection_disTime}'); ?><br />
Вид оплаты: {PayType_Name}<br />
<?php echo ((empty($TariffClass_Name))?'':', Вид тарифа: {TariffClass_Name}<br />'); ?>
Врач: {MedPersonal_Fio}<br />
Профиль коек: {LpuSectionBedProfile_Name}<br />
Палата: {LpuSectionWard_Name}<br />
Исход госпитализации:
<?php
switch($LeaveType_id)
{
	case -3://Нет ни отказа, ни госпитализации в отделение
	break;
	case -2://Госпитализация в отделение
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Госпитализация в отделение: {LpuSection_o_Name}</strong>
	<?php
	break;
	case -1://Отказ 
		if (empty($PrehospWaifRefuseCause_Name))
		{
		}
		else
		{
			echo '<strong>Отказ</strong><br />Причина отказа: {PrehospWaifRefuseCause_Name}';
		}
	break;
	case 1://Выписка
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Выписка</strong> УКЛ {EvnLeave_UKL}<br />
		Причина выписки: {LeaveCause_Name}		Исход заболевания: {ResultDesease_Name}<br />
		Направлен на амб.лечение <?php echo ((empty($EvnLeave_IsAmbul))?'':'{EvnLeave_IsAmbul}'); ?><br />
	<?php
	break;
	case 2://Перевод в другое ЛПУ Org_oid
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в {Lpu_l_Name}</strong> УКЛ {EvnLeave_UKL}<br />
		<br />Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}<br />
	<?php
	break;
	case 3://Смерть AnatomWhere_id LpuSection_aid Org_aid MedStaffFact_aid Diag_aid
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Смерть</strong> УКЛ {EvnLeave_UKL}<br />
		Врач, установивший смерть: {MedPersonal_d_Fin}<br />
		Необходимость экспертизы: <?php echo ((empty($EvnDie_IsAnatom))?'':'{EvnDie_IsAnatom}'); ?><br />
		<strong><i>Патологоанатомическая экспертиза:</i></strong><br />
		{EvnDie_expDate} {EvnDie_expTime}  Место проведения:  {EvnDie_locName}<br />
		Врач:{MedPersonal_a_Fin}<br />
		Основной патологоанатомический диагноз: {Diag_a_Code}.{Diag_a_Name}
	<?php
	break;
	case 4://Перевод в стационар другого типа LpuUnitType_oid LpuSection_oid
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в стационар другого типа</strong> УКЛ {EvnLeave_UKL}<br />
		Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}<br />
		Тип стационара: {LpuUnitType_o_Name}	Отделение: {LpuSection_o_Name}
	<?php
	break;
	case 5://Перевод в другое отделение
	?>
		{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в другое отделение: {LpuSection_o_Name}</strong><?php echo ((empty($EvnLeave_UKL))?'':' УКЛ {EvnLeave_UKL}'); ?><br />
		<?php echo ((empty($LeaveCause_Name))?'':'<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}<br />'); ?>
	<?php
	break;
	default:
	break;
}
?>
<br />
Основной диагноз: {Diag_Code} {Diag_Name}
