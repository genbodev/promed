<?php
$is_perm = (isset($region_nick) && $region_nick == 'perm');
$is_kareliya = (isset($region_nick) && $region_nick == 'kareliya');
?>


<body class="land EvnDirectionBody">
<link rel="stylesheet" type="text/css" href="/css/printGroup.css" />
<div align='center'>
	<table width='100%' border='0'>

		<tr>
			<td class="style2" style='width: 50%; text-align: center; vertical-align: top;'><small>Министерство здравоохранения и социального<br>&nbsp;&nbsp;&nbsp;&nbsp;pазвития Российской Федерации<br><br>
					<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_Name}</div>
					<center>(наименование лечебно-профилактического учреждения)</center>
					<div class="item3" style="">{Address_Address}</div>
					<div class="item2" style="">{LpuUnit_Phone}</div>
					<center>(адрес, телефоны)</center>
					<div class="item2" style="">{Lpu_OGRN}</div>
					<center>(ОГРН)</center>
			</td>
			<td class="style2 item4" style=''>
				<?php
				if($is_perm)
				{
					?>
					Приложение №2<br>
					к Порядку оказания медицинской помощи<br>
					на территории Пермского края<br>
					учреждениями здравоохранения независимо от формы собственности<br>
					в системе обязательного медицинского страхования<br>
					в том числе в условиях фондодержания,<br>
					оплаты по подушевым нормативам амбулаторно-поликлинической помощи<br>
					<center><span class="style1">
					Медицинская документация<br>
					&nbsp;&nbsp;&nbsp;&nbsp;Форма _________</span></center>
					<?php
				}
				else
				{
					echo '&nbsp;';
				}
				?>
			</td></tr>
	</table>
	<br>
	<table width='100%' border='0'>
		<tr><td>
				<center>
					<b>НАПРАВЛЕНИЕ № {EvnDirection_Num}
						<br>
						<small>
							{dirstring}
					</b><br>
					(нужное подчеркнуть)
					</small>
					<div class="item5" style="">{dLpu_Name}</div>
					<small><center>(наименование медицинского учреждения, куда направлен пациент)</center></small>
					<div class="item3" style="">{dLpuUnit_Name} {LpuUnit_Address}</div>
					<div class="item3" style=""><small>{Сontact_Phone}</small></div>
				</center>
			</td></tr>
	</table>
	<table width='100%' border='0'>
		<tr>
			<td class="style1">1.Профиль</td>
			<td class="item6" style=''>
				<div class="item7" style="">{LpuSectionProfile_Name} </div></td>
			<td class="style1 item8" style=''>{TType}</td>
			<td class="item9" style=''>
				<div class="item10" style="">{RecMP}</div></td>
			<td class="style1 item11" style=''>
				&nbsp;</td>
			<td class="item12" style=''>
				<div class="item13" style="">&nbsp;</div></td>
		</tr>
	</table>

	<table width='100%' border='0'>
		<tr>
			<td class="style1">2.Услуга</td>
			<td class="item14" style=''>
				<div class="item15" style="">{Usluga_Name} </div></td>
			<td class="style1 item16" style=''>Дата и время приема</td>
			<td class="item17" style=''>
				<div style=""><u>{RecDate}</u></div></td>
			<td class="style1 item18" style=''>
				&nbsp;</td>
			<td class="item19" style=''>
				<div style="">&nbsp;</div></td>
		</tr>
	</table>

	<table width='100%' border='0'><tr>
			<td  class="style1 item20" style=''>
				2.Номер страхового полиса ОМС</td>
			<td class="item21" style=''>
				<div class="item22" style="">{Polis}&nbsp;</div></td>
			<td class="style1 item23" style=''>
				3.Страховая компания</td>
			<td class="item24" style=''>
				<div class="item25" style="">{OrgSmo_Nick}&nbsp;</div></td></tr>
	</table>

	<table width='100%' border='0'><tr>
			<td class="style1 item26" style=''>
				4.Тип госпитализации</td>
			<td class="item27" style=''>
				{hospstring}
			</td>
			<td class="style1 item28" style=''>
				5.Код льготы</td>
			<td class="item29" style=''>
				<div class="item30" style="">&nbsp;</div></td></tr><tr></tr></td>
	</table>

	<table width='100%' border='0'><tr>
			<td class="style1">
				6.Фамилия, имя, отчество</td>
			<td class="item31" style=''>
				<div class="item7" style="">{Person_FIO}</div></td></tr><tr></tr></td>
	</table>

	<table width='100%' border='0'>
		<tr>
			<td class="style1">7.Дата рождения</td>
			<td class="item32" style=''>
				<div class="item7" style="">{Person_Birthdate}</div></td>
			<td class="style1 item33" style=''>
				Контактный телефон</td>
			<td class="item34" style=';'>
				<div class="item7" style="">{Person_Phone}&nbsp;</div></td>
			</td>
		</tr>
	</table>

	<table width='100%' border='0'><tr>
			<td class="style1">
				8.Адрес постоянного места жительства</td>
			<td class="item35" style=''>
				<div class="item7" style="">{Person_Address}</div></td></tr></td>
	</table>

	<table width='100%' border='0'><tr>
			<td class="style1">
				9.Место работы, должность</td>
			<td class="item36" style=''>
				<div class="item7" style="">{JobPost}</div></td></tr></td>
	</table>

	<?php if ($is_kareliya) { ?>
		<table width='100%' border='0'><tr>
				<td class="style1">
					10.Код диагноза по МКБ</td>
				<td class="item36" style=''>
					<div class="item7" style="">{Diag_Code} {Diag_Name}</div></td></tr></td>
		</table>
	<?php } else { ?>
		<table width='100%' border='0'><tr>
				<td class="style1 item37" style=''>
					10.Код диагноза по МКБ</td>
				<td class="item38" style=''>
					<div class="item7" style="">{Diag_Code}</div></td>
				<td class="style1 item33" style=''></td>
				<td class="item38" style=''></td></tr>
		</table>
	<?php } ?>

	<table width='100%' border='0'><tr>
			<td class="style1 item33" style='text-align: left; vertical-align: top;'>
				11.Обоснование направления</td>
			<td class="item36" style=''>
				<div class="item7" style="">{EvnDirection_Descr}</div>
				<div class="item7" style="">&nbsp;</div>
			</td></tr>
	</table>

	<table width='100%' border='0'>
		<tr>
			<td class="style2 item39" style=''>Должность медицинского работника,<br>
				направившего больного <u>{MedDol}</u>
				<div class="item40" style="">{med_init}</div>
				ФИО&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;подпись
			</td>
			<td width='50'></td>
			<td class="style2 item39" style=''>
				Заведующий отделением
				<div class="item41" style="">{zav_init}</div>
				ФИО&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;подпись
				<br><br><center>
					<u>«{Dir_Day}»{Dir_Month} {Dir_Year}</u> г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;МП
			</td></tr>
	</table>
</div>
</body>