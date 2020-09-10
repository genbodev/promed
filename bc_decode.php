<?php
	function GetBinaryFromStr($value)
	{
		// Битовая строка из символьной
		$result = '';

		for ($i = 0; $i < strlen($value); $i++)
		{
			$result .= sprintf('%08d', decbin(ord(substr($value, $i, 1))));
		}

		return $result;
	}

	function GetCharStrFromBinary($value)
	{
		// символьная строка из битовой строки
		$result = '';

		for ($i = 0; $i < strlen($value); $i += 8)
		{
			if (base_convert(substr($value, $i, 8), 2, 10) > 0)
			{
				$result .= chr(base_convert(substr($value, $i, 8), 2, 10));
			}
		}

		return $result;
	}

	$string = '';

	if ((isset($_POST['string'])) && (strlen(trim($_POST['string'])) > 0))
	{
		$string = trim($_POST['string']);
	}
?>
<html>
<head>
<title>Barcode string decoder</title>
</head>

<body>

<form action="" method="post">
<div>Введите строку: <input type="text" name="string" value="" size="80" /> <input type="submit" value="Decode" /></div>
</form>
<?php
	if (strlen($string) > 0)
	{
		// $string = urldecode('pADu3Ho3HjAxNDc2AAAAO7cejceNTkwNTAwMzU5MDUwMDMAAAAAAAAAAAAAAACYlyZGMTEuMQAAQAAACeLcCXThqfGJgAAAAAAAAAAAAAAAAAAAAAAAAAAAePETQgAAY%3D');
		// $string = "pAD4LNV2tDA2NTgxAAAAPgs1Xa0NTkwMDAwNzU5MDAwMDcAAAAAAAAAAAAAAACYlvFKNDUuOQAAQAAAJMFjBj10cvmRqAAAAAAAAAAAAAAAAAAAAAAAAAAfQKUTRQAAY=";
		echo "<div style='font-weight: bold;'>Строка для распознавания:</div>";
		echo "<div style='overflow: auto; padding: 1em; border: 1px solid #000;'>", $string, "</div>";

		echo "<div style='margin-top: 1em;'><span style='font-weight: bold;'>Результат:</span></div>";

		$string = substr($string, 1);
		// echo "<div>", $string, "</div>";

		$string = base64_decode($string);
		// echo "<div>", $string, "</div>";
		
		$string = GetBinaryFromStr($string);
		// echo "<div>", $string, "</div>";

		$version = substr($string, strlen($string) - 19, 19);
		$string = substr($string, 0, strlen($string) - 19);
		echo "<div>Version: ", base_convert($version, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$drug_is_kek = substr($string, strlen($string) - 1, 1);
		$string = substr($string, 0, strlen($string) - 1);
		echo "<div>Drug_IsKEK: ", base_convert($drug_is_kek, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$evn_recept_set_day = substr($string, strlen($string) - 5, 5);
		$string = substr($string, 0, strlen($string) - 5);
		echo "<div>EvnRecept_setDay: ", base_convert($evn_recept_set_day, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$evn_recept_set_month = substr($string, strlen($string) - 4, 4);
		$string = substr($string, 0, strlen($string) - 4);
		echo "<div>EvnRecept_setMonth: ", base_convert($evn_recept_set_month, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$evn_recept_set_year = substr($string, strlen($string) - 7, 7);
		$string = substr($string, 0, strlen($string) - 7);
		echo "<div>EvnRecept_setYear: ", base_convert($evn_recept_set_year, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$recept_valid_code = substr($string, strlen($string) - 1, 1);
		$string = substr($string, 0, strlen($string) - 1);
		echo "<div>ReceptValid_Code: ", base_convert($recept_valid_code, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$privilege_type_code = substr($string, strlen($string) - 10, 10);
		$string = substr($string, 0, strlen($string) - 10);
		echo "<div>PrivilegeType_Code: ", base_convert($privilege_type_code, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$drug_dose_count = substr($string, strlen($string) - 24, 24);
		$string = substr($string, 0, strlen($string) - 24);
		echo "<div>Drug_DoseCount: ", base_convert($drug_dose_count, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$drug_dose = substr($string, strlen($string) - 160, 160);
		$string = substr($string, 0, strlen($string) - 160);
		echo "<div>Drug_Dose: ", GetCharStrFromBinary($drug_dose), "</div>";
		// echo "<div>", $string, "</div>";

		$person_snils = substr($string, strlen($string) - 37, 37);
		$string = substr($string, 0, strlen($string) - 37);
		echo "<div>Person_Snils: ", base_convert($person_snils, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$drug_mnn_torg_code = substr($string, strlen($string) - 44, 44);
		$string = substr($string, 0, strlen($string) - 44);
		echo "<div>DrugMnnTorg_Code: ", base_convert($drug_mnn_torg_code, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$drug_is_mnn = substr($string, strlen($string) - 1, 1);
		$string = substr($string, 0, strlen($string) - 1);
		echo "<div>Drug_IsMnn: ", base_convert($drug_is_mnn, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$recept_discount_code = substr($string, strlen($string) - 1, 1);
		$string = substr($string, 0, strlen($string) - 1);
		echo "<div>ReceptDiscount_Code: ", base_convert($recept_discount_code, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$recept_finance_code = substr($string, strlen($string) - 2, 2);
		$string = substr($string, 0, strlen($string) - 2);
		echo "<div>ReceptFinance_Code: ", base_convert($recept_finance_code, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$diag_code = substr($string, strlen($string) - 56, 56);
		$string = substr($string, 0, strlen($string) - 56);
		echo "<div>Diag_Code: ", GetCharStrFromBinary($diag_code), "</div>";
		// echo "<div>", $string, "</div>";

		$evn_recept_num = substr($string, strlen($string) - 64, 64);
		$string = substr($string, 0, strlen($string) - 64);
		echo "<div>EvnRecept_Num: ", base_convert($evn_recept_num, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$evn_recept_ser = substr($string, strlen($string) - 112, 112);
		$string = substr($string, 0, strlen($string) - 112);
		echo "<div>EvnRecept_Ser: ", GetCharStrFromBinary($evn_recept_ser), "</div>";
		// echo "<div>", $string, "</div>";

		$lpu_code = substr($string, strlen($string) - 56, 56);
		$string = substr($string, 0, strlen($string) - 56);
		echo "<div>Lpu_Code: ", GetCharStrFromBinary($lpu_code), "</div>";
		// echo "<div>", $string, "</div>";

		$lpu_ogrn = substr($string, strlen($string) - 50, 50);
		$string = substr($string, 0, strlen($string) - 50);
		echo "<div>Lpu_Ogrn: ", base_convert($lpu_ogrn, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";

		$medpersonal_code = substr($string, strlen($string) - 56, 56);
		$string = substr($string, 0, strlen($string) - 56);
		echo "<div>MedPersonal_Code: ", GetCharStrFromBinary($medpersonal_code), "</div>";
		// echo "<div>", $string, "</div>";

		$lpu_ogrn = substr($string, strlen($string) - 50, 50);
		$string = substr($string, 0, strlen($string) - 50);
		echo "<div>Lpu_Ogrn: ", base_convert($lpu_ogrn, 2, 10), "</div>";
		// echo "<div>", $string, "</div>";
	}
?>

</body>
</html>