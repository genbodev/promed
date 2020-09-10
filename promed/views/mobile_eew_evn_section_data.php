<h2>Движение пациента</h2>
<div>
	<p>{LpuSection_Name} - {EvnSection_setDate} {EvnSection_setTime}<?php echo ((empty($EvnSection_disDate))?'':' - {EvnSection_disDate} {EvnSection_disTime}'); ?> Вид оплаты: {PayType_Name}<?php echo ((empty($TariffClass_Name))?'':', Вид тарифа: {TariffClass_Name}'); ?></p></br>
	<p>Врач: {MedPersonal_Fio}</p>
	<p>Палата: {LpuSectionWard_Name}</p>
	<div class="left">
	<p><strong>Исход госпитализации:</strong>
	<?php
	switch($LeaveType_id)
	{
		case -3://Нет ни отказа, ни госпитализации в отделение
		?>
			</p></div>
		<?php
		break;
		case -2://Госпитализация в отделение
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Госпитализация в отделение: {LpuSection_o_Name}</strong></p></div>
		<?php
		break;
		case -1://Отказ 
			if (empty($PrehospWaifRefuseCause_Name))
			{
				echo '</p></div>';
			}
			else
			{
				echo '<strong>Отказ</strong></p><p>Причина отказа: {PrehospWaifRefuseCause_Name}</p></div>';
			}
		break;
		case 1://Выписка
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Выписка</strong> УКЛ {EvnLeave_UKL}</p>
			<p>Причина выписки: {LeaveCause_Name}		Исход заболевания: {ResultDesease_Name}</p>
			<p>Направлен на амб.лечение <?php echo ((empty($EvnLeave_IsAmbul))?'':'{EvnLeave_IsAmbul}'); ?></p>										
			</div>
		<?php
		break;
		case 2://Перевод в другое ЛПУ Org_oid
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в {Lpu_l_Name}</strong> УКЛ {EvnLeave_UKL}</p>
			<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>
			</div>
		<?php
		break;
		case 3://Смерть AnatomWhere_id LpuSection_aid Org_aid MedStaffFact_aid Diag_aid
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Смерть</strong> УКЛ {EvnLeave_UKL}</p>
			<p>Врач, установивший смерть: {MedPersonal_d_Fin}</p>
			<p>Необходимость экспертизы: <?php echo ((empty($EvnDie_IsAnatom))?'':'{EvnDie_IsAnatom}'); ?></p>
			<p><strong><i>Патологоанатомическая экспертиза:</i></strong></p>
			<p>{EvnDie_expDate} {EvnDie_expTime}  Место проведения:  {EvnDie_locName}</p>
			<p>Врач:{MedPersonal_a_Fin}</p>
			<p>Основной патологоанатомический диагноз: {Diag_a_Code}.{Diag_a_Name}</p>
			</div>
		<?php
		break;
		case 4://Перевод в стационар другого типа LpuUnitType_oid LpuSection_oid
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в стационар другого типа</strong> УКЛ {EvnLeave_UKL}</p>
			<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>
			<p>Тип стационара: {LpuUnitType_o_Name}	Отделение: {LpuSection_o_Name}</p>
			</div>
		<?php
		break;
		case 5://Перевод в другое отделение
		?>
			{EvnSection_leaveDate} {EvnSection_leaveTime} <strong>Перевод в другое отделение: {LpuSection_o_Name}</strong><?php echo ((empty($EvnLeave_UKL))?'':' УКЛ {EvnLeave_UKL}'); ?></p>
			<?php echo ((empty($LeaveCause_Name))?'':'<p>Причина перевода: {LeaveCause_Name} 	Исход госпитализации: {ResultDesease_Name}</p>'); ?>
			</div>
		<?php
		break;
		default:
		?>
			</p></div>
		<?php
		break;
	}
	?>
	</br>
	<p><strong>Основной диагноз:</strong>
	{Diag_Code} 
	.{Diag_Name}</p>

	{EvnDiagPS}
	<?php
	if(75 == $LpuSectionProfile_id)
	{
		echo ((empty($EvnPS_HospCount))?'':'<p>Количество госпитализаций: {EvnPS_HospCount}</p>');
		echo ((empty($EvnPS_TimeDesease))?'':'<p>Время с начала заболевания: {EvnPS_TimeDesease}</p>');
		echo ((empty($EvnPS_IsNeglectedCase))?'':'<p>Случай запущен: {EvnPS_IsNeglectedCase}</p>');
		echo ((empty($PrehospToxic_Name))?'':'<p>Состояние опьянения: {PrehospToxic_Name}</p>');
		echo ((empty($PrehospTrauma_Name))?'':'<p>Травма : {PrehospTrauma_Name}');
		echo ((empty($EvnPS_IsUnlaw))?'</p>':' Противоправная: {EvnPS_IsUnlaw}</p>');
		echo ((empty($EvnPS_IsUnport))?'':'<p>Нетранспортабельность: {EvnPS_IsUnport}</p>');
	}
	else if(getRegionNumber() != 63) // отображаем в движении для всех кроме Самары
	{
		if (empty($Mes_Name)) {
			echo '<p>'.getMESAlias().': не выбран</p>';
		} else {
			echo '<p>'.getMESAlias().': <u>{Mes_Code}.{Mes_Name}</u></p>
			<p>Норматив койкодней: {Mes_KoikoDni}, фактические койкодни: {EvnSection_KoikoDni},	выполнение '.getMESAlias().': {Procent_KoikoDni}%</p>
			<!--table style="text-align: left; font-family: tahoma,arial,helvetica,sans-serif; font-size: 13px;" cellspacing="0" height="30" width="400"><tbody>
			<tr valign="bottom"><td rowspan="2" valign="bottom" width="80">Выполнение:&nbsp;</td><td style="font-size: 8pt;" width="55"><div style="float: left; margin-left: -1px;">0%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">25%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">50%<br>|</div></td><td style="font-size: 8pt;" width="55"><div style="float: left;">75%<br>|</div></td><td rowspan="2" style="font-size: 8pt;" valign="top" width="30"><div style="float: left; margin-left: -2px;">100%<br>|</div></td><td rowspan="2" valign="bottom">({Procent_KoikoDni}%)</td></tr>
			<tr height="6"><td colspan="4"><table style="border: 1px solid rgb(0, 0, 0);" cellspacing="0" height="6" width="100%"><tbody><tr><td style="background: green none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous;" width="{Procent_KoikoDni}%"></td><td width="0%"></td></tr></tbody></table></td></tr>
			</tbody></table-->';
		}
	}
	?>
	{EvnPrescrPlan}

	{EvnDirectionStac}
</div>