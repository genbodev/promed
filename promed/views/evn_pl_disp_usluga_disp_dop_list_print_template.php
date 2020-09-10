<html>
<head>
	<title>{DispClass_Name}</title>
	<style type="text/css">
		@font-face {
			font-family: 'Roboto';
			font-style: normal;
			font-weight: 400;
			src: local('Roboto'), local('Roboto-Regular'), url("/extjs6/modern/theme-material/resources/fonts/roboto/Roboto-Regular.ttf") format('truetype');
		}
		@font-face {
			font-family: 'Roboto';
			font-style: normal;
			font-weight: 500;
			src: local('Roboto'), local('Roboto-Medium'), url("/extjs6/modern/theme-material/resources/fonts/roboto/Roboto-Medium.ttf") format('truetype');
		}
		html, body {
			font: 14px/18px 'Roboto';
			margin: 0;
			padding: 0;
			min-height: 100%;
			height: 100%;
		}
		body {
			background: rgb(204,204,204);
		}
		.page {
			background: white;
			display: block;
			margin: 10px auto;
			margin-bottom: 0.5cm;
			box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
			width: 21cm;
			min-height: 100%;
		}
		hr {
			display: block;
			height: 1px;
			border: 0;
			border-top: 1px solid #ccc;
			margin: 1.5em auto;
			padding: 0;
		}

		hr.break {
			height: 10px;
			border: 0;
			box-shadow: 0 5px 5px -4px #8c8b8b inset;
			width: 100%;
			margin: 0.5em auto;
		}

		hr.break.invert {
			box-shadow: 0 2px 2px -2px #8c8b8b inset;
		}

		.evn-usluga-wrapper {
			padding: 10px 0;
			margin: 0 auto;
			width: 90%;
		}
		.evn-usluga-header {
			padding:0;
			margin: 0 auto;
			width: 90%;
		}
		.evn-usluga {
			padding: 0;
			margin: 0 auto;
			width: 90%;
			/*padding-top: 20px;*/
			/*padding-left: 35px;*/
		}

		.evn-usluga p {
			padding: 0;
			margin: 0;
			font-weight: 500;
		}

		.person-info {
			list-style: none;
			margin: 0;
			padding: 0;
			width: 100%;
		}


		.person-info li {
			width: 100%;
			font-size: 17px;
			line-height: 20px;
			list-style-position: outside;

			/*
			 * Because the bullet is outside of the list’s
			 * container, indent the list entirely
			 */
			margin-left: 1em;
		}

		.person-info li span {
			font-weight: 500;
			min-width: 210px;
			display: inline-block;
			vertical-align: top;
		}

		.person-info i{
			font-style: normal;
			display: inline-block;
			width: 350px;
		}

		h2,h3,h4 {
			text-align: left;
			width: 100%;
			padding: 5px 0;
			line-height: 0px;
			font-weight: 500;
		}

		h3 {
			font-size: 18px;
			padding-top: 25px;
			margin: 10px 0;
		}

		table {
			border-spacing: 0;
			border-collapse: separate;
			font-size: 14px;
			width: 100%;
			margin-top: 10px;
		}

		td,th {
			padding: 4px;
		}

		table, th, td {
			border: 1px solid black;
		}

		@media print {

			html, body {
				height: unset;
			}

			html, body, .page, .evn-usluga-wrapper {
				margin: 0;
				min-height: unset;
			}
			.evn-usluga-wrapper {
				padding: 0;
				width: 90%;
			}
			.page {
				width: 100%;
				box-shadow: unset;
				margin-bottom: 0;
				/*min-height: unset;*/
			}
			.evn-usluga, .evn-usluga-header {
				width: 90%;
			}

		}
	</style>
</head>
<body>
<div class="page">

	<div class="evn-usluga-wrapper">
		<h2>{DispClass_Name}</h2>
		<div class="evn-usluga-header">
		<ul class="person-info">
			<li><span>ФИО:</span><i>{Person_Fio}</i>
			<li><span>Дата рождения:</span><i>{Person_Birthday}</i></li>
			<li><span>Адрес регистрации:</span><i>{UAddress}</i></li>
			<li><span>Адрес проживания:</span><i>{PAddress}</i></li>
			<li><span>Возраст:</span><i><?php echo (!empty($Person_Age) ? $Person_Age." ".$PersonAgeDesc : ""); ?><?php echo (!empty($Person_AgeInMonth) ? " ".$Person_AgeInMonth." ".$PersonAgeInMonthDesc : "") ?></i></li>
			<li><span>Дата прохождения:</span><i>{EvnPLDisp_setDate}</i></li>
			<li><span>Рост:</span><i>{AssessmentHealth_Height} см</i></li>
			<li><span>Вес:</span><i>{AssessmentHealth_Weight} кг</i></li>

		</ul>
		</div>

		<?php foreach ($disp_dop_list as $disp_dop) { ?>
			<?php if (
				$disp_dop['SurveyType_IsVizit'] == 2
				|| ($disp_dop['SurveyType_IsVizit'] == 1 && in_array($disp_dop['SurveyType_Code'], array(17)))
			)  { ?>
<!--				<hr class="break invert"/>-->
				<h3><?php echo $disp_dop['SurveyType_Name'] ?></h3>
				<hr class="break"/>
				<div class="evn-usluga">


<!--					<hr class="break"/>-->
					<?php if (!empty($disp_dop['html'])) { echo $disp_dop['html'] ?>

					<?php } else { ?>
						<hr/><hr/><hr/><hr/><hr/>
					<?php } ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
</div>
</body>
</html>