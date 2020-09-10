<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
	<style>
		html {
			margin: 0;
			padding: 0;
		}

		body {
			Font-family: 'Times New Roman', Times, serif;
			font-size: 6pt;
			margin: 0;
			padding: 0;
		}

		p {
			margin: 0 0 5px
		}

		table {
			font-size: 12pt;
			vertical-align: top;
		}

		table td {
			font-size: 6pt;
		}

		.head110 {
			vertical-align: top;
			height: 0px;
		}

		.head110 big {
			line-height: 14px;
		}

		table.time {
			width: 100%;
			border-collapse: collapse;
		}

		table.time td {
			border: 1px solid #000;
			text-align: center;
			border-bottom: 0px;
		}

		table.time td.ender {
			border-bottom: 1px solid #000;
		}

		span {
			display: inline-block;
		}

		.under {
			border-bottom: 1px solid;
		}

		.lister {
			width: 26cm;
			height: 18cm;
			-webkit-transform: rotate(-90deg);
			-moz-transform: rotate(-90deg);
			-o-transform: rotate(-90deg);
			-ms-transform: rotate(-90deg);
			transform: rotate(-90deg);
			filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
			margin-left: -4cm;
			margin-top: 4cm;
			border: 1 px solid;
		}

		.page {
			width: 13cm;
			height: 17.4cm;
			float: left;
			display: block;
		}

		.pageLeft {
		}

		.pageRight {
		}
</style>

<title></title>

</head>
<body>
<script type="text/javascript">
	window.print();
</script>

<div class="lister">
	<div class="page pageRight">
		&nbsp;
	</div>
	<div class="page pageLeft">
		<table class="head110">
			<tr>
			<td width="50%" align="center" valign="bottom"><span class="under">{Lpu_name}<br/> {UAddress_Address} т.{Lpu_Phone}</span><br/><small>наименование организации, адрес, телефон</small></td>
			<td width="20%" align="left"  valign="top"></td>
			<td width="30%" align="left"  valign="top">
				Учетная форма<br/>
				утверждена приказом Минздрав РБ<br/>
				от 9 ноября 2015 г. № 3386-Д
			</td>
			</tr>
		</table>
		<center>
			<h2>КАРТА вызова скорой медицинской помощи № <span class="under">{Day_num}</span><br>
			<span class="under"><?= (isset($CallCardDate)) ? $CallCardDate : ''?></span>г.</h2>
		</center>


		<p>1. Подстанция:<span style="width: 200px;" class="under"><?= (isset($LpuBuilding_Name)) ? $LpuBuilding_Name : ''?></span> 2. Номер бригады СМП:<span style="width: 50px;" class="under"><?= ($EmergencyTeam_Num) ? $EmergencyTeam_Num : ''?></span></p>
		<p>3. Время (часы, минуты):</p>


		<table  class="time">
			<tr>
				<td width="24%">Время приёма вызова</td>
				<td width="9%"><?= (isset($AcceptTime)) ? $AcceptTime : ''?></td>
				<td width="24%">Время убытия от больного</td>
				<td width="9%"><?= (isset($TransportTime)) ? $TransportTime : ''?></td>
				<td width="24%">Окончание вызова</td>
				<td width="10%"><?= (isset($endService)) ? $endService : ''?></td>
			</tr>
			<tr>
				<td width="24%">Время передачи вызова </td>
				<td width="9%"><?= (isset($TransTime)) ? $TransTime : ''?></td>
				<td width="24%">Начало транспортировки</td>
				<td width="9%"><?= (isset($startTransportir)) ? $startTransportir : ''?></td>
				<td width="24%">Время возвращ. на подстанцию</td>
				<td width="10%"><?= (isset($BackTime)) ? $BackTime : ''?></td>
			</tr>
			<tr>
				<td width="24%">Время выезда на вызов</td>
				<td width="9%"><?= (isset($GoTime)) ? $GoTime : ''?></td>
				<td width="24%">Прибытия в МО</td>
				<td width="9%"><?= (isset($arrivalMO)) ? $arrivalMO : ''?></td>
				<td width="24%">Затраченное время</td>
				<td width="10%"><?= (isset($SummTime)) ? $SummTime : ''?></td>
			</tr>
			<tr>
				<td width="24%">Время прибытия к больному</td>
				<td width="9%"><?= (isset($ArriveTime)) ? $ArriveTime : ''?></td>
				<td width="24%">Время отзвона</td>
				<td width="9%"><?= (isset($EndTime)) ? $EndTime : ''?></td>
				<td width="24%"></td>
				<td width="10%"></td>
			</tr>
		</table>

		<table class="time">
			<tr>
				<td width="20%" >Место вызова</td>
				<td width="80%" rowspan="7" style="text-align: left">
					<?=!empty($City) ? "г. {City}," : ""?>
					<b>
						<?=!empty($SubRegion) ? "Р-Н {SubRegion}," : ""?>
						<?=!empty($SocrTw) ? "{SocrTw}." : ""?>
						<?=!empty($Town) ? " {Town}," : ""?>
						<?=!empty($SocrSt) ? "{SocrSt}." : ""?>
						<?=!empty($Street) ? "{Street}," : ""?>
						<?=!empty($secondStreetName) ? "{secondStreetName}," : "{secondStreetName}"?>
						<?=!empty($Adress_Object) ? "{$Adress_Object}," : ""?>
					</b>
					<?=!empty($House) ? "д. <b>{House}</b>," : ""?>
					<?=!empty($Korpus) ? "корп. <b>{Korpus}</b>," : ""?>
					<?=!empty($Office) ? "кв. <b>{Office}</b>" : ""?>
					</td>
			</tr>
		</table>
		<table class="time">
			<tr>
				<td width="20%">Этаж</td>
				<td width="5%"><?=($Level > 0)?$Level:''?></td>
				<td width="15%">Подъезд</td>
				<td width="10%"><?=($Entrance > 0)?$Entrance:''?></td>
				<td width="15%">Код подъезда</td>
				<td width="10%"><?= ($CodeEntrance) ? $CodeEntrance : ''?></td>
				<td width="10%">Телефон</td>
				<td width="30%"><?= ($Phone) ? $Phone : ''?></td>
			</tr>
		</table>
		<table class="time">
			<tr style="height: 25px;">
				<td width="10%">Фамилия</td>
				<td width="15%"><?= ($Fam) ? $Fam : ''?> <?=(mb_substr($Name, 0, 1))?>. <?=(mb_substr($Middle, 0, 1))?>.</td>
				<td width="10%">Возраст</td>
				<td width="10%"><?=(($Age>0)?$Age:$AgePS)?></td>
				<td width="5%"><?=(($Age>0)?'Лет':$AgeTypeValue)?></td>
				<td width="10%">Пол</td>
				<td width="10%"><?= ($Sex_name) ? $Sex_name : ''?></td>
				<td width="15%" rowspan="2">
					<?= (!empty($CallType)) ? $CallType : ''?>
				<td width="10%"><?=((!empty($CmpCallCard_IsExtra) && $CmpCallCard_IsExtra != '2')?'скорая':'неотложка')?></td>
			</tr>
		</table>
		<table class="time">
			<tr>
				<td class="ender" style="text-align: left;">
					<b>Вызывает:</b> <span class="under" style="width: 100px;" ><?= ($CmpCallerType_Name) ? $CmpCallerType_Name : ''?></span>
					<b>Повод к вызову</b> <span class="under" style="width: 191px;" ><?= ($Reason) ? $Reason : ''?></span><br/>
					<b>Дополнительная информация</b> (ориентиры подъезда к адресу) <span style="width: 190px;" class="under"><?= $withPolis = ( (isset($Polis_id)) && (strlen($Polis_id) > 0) && ($Polis_id != '&nbsp;') );?><?if($withPolis):?>{Polis_Ser} {Polis_Num}<?endif?><?if($Person_IsBDZ == 'true'):?>, пациент сверен с РСЕРЗ<?endif?></span>
					<span class="under" style="width: 377px;"><?= ($CmpCallCard_Comm) ? $CmpCallCard_Comm : ''?></span>
				</td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>