<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size:10pt;}    
p {margin:0 0 10px}
table {font-size:12pt; vertical-align: top;}
table td {font-size:10pt;}
.lefttd {align: left; width: 400px; font-weight: bold; vertical-align: top;}
.leftminitd {align: left; width: 400px; vertical-align: top;}
.righttd {align: left; border-bottom: #aaaaaa 1px solid; vertical-align: top;}
.linetd {background-color: #000; height: 2px;}
.tit {font-family:Arial; font-weight:bold; font-size:12pt; text-align:center}
.podval {font-size:8pt}
.v_ok:after {content: "V"; font-family: Verdana; font-size: 14px; font-weight: bold; border: 1px solid #000; height: 12px; display: inline-block; line-height: 12px;}
.v_no {border: 1px solid #000; width:11px; height: 12px;}
.head110 {font-size:14px; vertical-align: top;}
.head110 big {font-size:12px; line-height:14px;}
table.time {border-collapse:collapse; border:1px solid #000}
    table.time td {border:1px solid #000; text-align: center; font-size: 13px;}
span {display:inline-block; width:30px}
.wrapper110 {display: inline-block;}
.innerwrapper {display:inline-block}
.innerwrapper .v_ok, .innerwrapper .v_no {display:inline-block; margin: 0 15px 0 0;}
.innerwrapper u {margin: 0 15px 0 0}
</style>

<title>КАРТА ВЫЗОВА СМП №{Year_num}/{Day_num} ОТ {CallCardDate}</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<table width="100%" class="head110">
    <tr>
	<?/*?><td width="60%" valign="bottom" align="center"><big>Наименование организации<br>Адрес и телефон</big></td><?*/?>
	<td width="60%" align="center" valign="top"><big>{Lpu_name}<br> {UAddress_Address} т.{Lpu_Phone}</big></td>	
	<td width="40%" align="center"  valign="top">
	<?/*?>
		 * Приложение № 3<br>
		к приказу Министерства здравоохранения <br>и социального развития Российской Федерации<br>
			от 2 декабря 2009г. № 942 <br>                                    
		Медицинская документация<br>
		<strong>Учетная форма № 110/у</strong><br>
		Утверждена приказом<br>
		Министерства здравоохранения <br>и социального развития Российской Федерации<br>
		от 2 декабря 2009г. № 942   

		<b>Учетная форма № 110/у</b><br>
		Утверждена приказом<br>
		Министерства здравоохранения<br>
		и социального развития Российской Федерации<br>
		от 2 декабря 2009г. № 942
	 <?*/?>    
</td>
    </tr>
</table>       
<center>
	<h2>КАРТА<br>
	вызова скорой медицинской помощи №{Year_num}/{Day_num}<br>
	{CallCardDate}</h2>
</center>

<p>1. Номер станции (подстанции), отделения:<span></span>{StationNum}</p>
<p>2. Номер бригады скорой медицинской помощи:<span></span>{EmergencyTeamNum}</p>
<p>3. Время (часы, минуты):</p>
<table width="90%" class="time">
    <tr>
	<td width="10%">приема вызова</td>
	<td width="10%">передачи вызова бригаде скорой медицинской помощи</td>
	<td width="10%">выезда на вызов</td>
	<td width="10%">прибытия на место вызова</td>
	<td width="10%">начало транспортировки больного</td>
	<td width="10%">прибытия в медицинскую организацию</td>
	<td width="10%">окончания вызова</td>
	<td width="10%">возвращения на станцию(подстанцию, отделение)</td>
	<td width="10%">затраченное на выполнение вызова</td>
    </tr>
    <tr>
	<td>{AcceptTime}</td>
	<td>{TransTime}</td>
	<td>{GoTime}</td>
	<td>{ArriveTime}</td>
	<td>{TransportTime}</td>
	<td>{ToHospitalTime}</td>
	<td>{EndTime}</td>
	<td>{BackTime}</td>
	<td>{SummTime}</td>
    </tr>
</table>
<table width="100%">
    <tr>
	<td width="60%">
	    4. Aдрес вызова:<br>
	   {Adress_Name}
	</td>
	<td width="40%">
	    5. Сведения о больном:<br>
	    Фамилия: {Fam}<br>
	    Имя: {Name}<br>
	    Отчество: {Middle}<br>
	    Возраст: {Age} <br>
	    Пол: {Sex_name}<br>
	</td>
    </tr>
</table>
<p>6. Повод к вызову: {CmpReason_Name}</p>
<p>7. Вызов: {CmpCallType_Name}</p>
 </body>
</html>