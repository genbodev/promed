<html>
<head>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<title>Печать поручения</title>
	<style>
		body {
			font: normal 12px/1.5em Verdana, Tahoma, Arial, sans-serif;
		}
		.wrapper {
			width: 800px;
			margin: 0 auto;
		}
		.head {
			width: 250px;
			/*float: right;*/
			margin: 0 0 0 auto;
			overflow: hidden;
			text-align: center;
		}
		.content {
			margin-top: 30px;
		}
		.signature {
			overflow: hidden;
			position: relative;
			margin-top: 30px;
		}
		.signature .sign {
			float: left;
			width: 30%;
			text-align: center;
		}
		.signature .name {
			float: left;
			width: 30%;
			margin-left: 155px;
		}
	</style>
</head>
<body>
<div class="wrapper">
	<div class="head">
		Приложение 1
		<br />
		К приказу №28 от 29.12.2012 г.
	</div>

	<div class="content">
		Я, {_ARMName} {_HeadName}
		{EvnDirectionForensic_begDate} на основании «Порядка организации и производства судебно-медицинских экспертиз
		в государственных судебно-экспертных учреждениях Российской Федерации», утвержденного приказом МЗ и СР
		№ 346н от 12.05.2010 г, поручаю судебно-медицинскому эксперту {Expert_Fio} производство медицинской судебной
		экспертизы по {EvnForensicType_Name} типу.
		<br />
		В срок до {EvnDirectionForensic_endDate}
	</div>

	<div class="signature">
		<div class="sign">
			________________________________________________
			<br />
			(подпись)
		</div>
		<div class="name">/ {_HeadName} /</div>
	</div>
</div>

</body>
</html>
