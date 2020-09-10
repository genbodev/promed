<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif}    
table {font-size:12pt; vertical-align: top;}
.lefttd {align: left; width: 400px; font-weight: bold; vertical-align: top;}
.leftminitd {align: left; width: 400px; vertical-align: top;}
.righttd {align: left; border-bottom: #aaaaaa 1px solid; vertical-align: top;}
.linetd {background-color: #000; height: 2px;}
.tit {font-family:Arial; font-weight:bold; font-size:12pt; text-align:center}
.podval {font-size:8pt}
.v_ok:after {content: "X"; font-family: Verdana; font-size: 14px; font-weight: bold; border: 1px solid #000; height: 12px; display: inline-block; line-height: 12px;}
.v_no {border: 1px solid #000; width:11px; height: 12px;}
.head110 {font-size:10px;}
.head110 big {font-size:12px; line-height:14px;}
table.tbl {border-collapse:collapse; border:1px solid #000}
    table.tbl td {border:1px solid #000; text-align: center; padding: 3px;}
span {display:inline-block; width:30px}
.wrapper110 {display: inline-block;}
.innerwrapper {display:inline-block}
.innerwrapper .v_ok, .innerwrapper .v_no {display:inline-block; margin: 0 15px 0 0;}
.innerwrapper u {margin: 0 15px 0 0}
</style>

<title>СУТОЧНЫЙ РАПОРТ</title>

</head>
<body>
	
	<h2>СУТОЧНЫЙ РАПОРТ </h2>
	<p>{DayDate}</p>
	
	<h3>Список бригад по профилям</h3>
	{SpisokBrigad}
	
	<h3>Обслужено вызовов</h3>
	<p>Всего вызовов: {AllCalls}</p>
		
	<h3>Показатели по заболеваниям</h3>
	{Zabolevaniya}

	<h3>Передано в НМП</h3>
	Вызовов: {toNMP}
	
	<h3>Отказы в обслуживании</h3>
	Вызовов: {Reject}
	
</body>
</html>