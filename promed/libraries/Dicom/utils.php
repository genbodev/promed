<?php
//
// utils.php
//
// Module for various utility functions
//
// CopyRight (c) 2004-2011 RainbowFish Software
//
function my_usort(&$a, $sort, $toggle)
{
    usort($a, $sort);
    if ($toggle)
        $a = array_reverse($a);
}

function cmp_timestamp($rowa, $rowb)
{
	$a = $rowa['lastaccess'];
	$b = $rowb['lastaccess'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
	if ($a < $b) return 1;
	if ($a > $b) return -1;

	return 0;
}

function hour2schedule($hour, $ampm)
{
	if ($hour == 12) {
		$schedule = ($ampm)? 12 : 0;
	} else {
		$schedule = ($ampm)? ($hour + 12) : $hour;
	}
	return $schedule;
}

function cmp_id($rowa, $rowb)
{
	$a = addslashes($rowa['origid']);
	$b = addslashes($rowb['origid']);
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_name($rowa, $rowb)
{
	$a = $rowa['lastname'];
	$b = $rowb['lastname'];

	return strcasecmp($a, $b);
}

function cmp_birthdate($rowa, $rowb)
{
	$a = $rowa['birthdate'];
	$b = $rowb['birthdate'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_patientid($rowa, $rowb)
{
	$a = addslashes($rowa['patientid']);
	$b = addslashes($rowb['patientid']);
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_studyid($rowa, $rowb)
{
	$a = $rowa['id'];
	$b = $rowb['id'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_studydate($rowa, $rowb)
{
	$a = $rowa['studydate'];
	$b = $rowb['studydate'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
	$aa = $rowa['studytime'];
	$bb = $rowb['studytime'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;
	if ($aa < $bb) return -1;
	if ($aa > $bb) return 1;

	return 0;
}

function cmp_seriesdate($rowa, $rowb)
{
	$a = $rowa['seriesdate'];
	$b = $rowb['seriesdate'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
	$aa = $rowa['seriestime'];
	$bb = $rowb['seriestime'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;
	if ($aa < $bb) return -1;
	if ($aa > $bb) return 1;

	return 0;
}

function cmp_accession($rowa, $rowb)
{
	$a = $rowa['accessionnum'];
	$b = $rowb['accessionnum'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_seriesnum($rowa, $rowb)
{
	$a = $rowa['seriesnumber'];
	$b = $rowb['seriesnumber'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_received($rowa, $rowb)
{
	die( __FUNCTION__ );
    global $dbcon;
    $a = addslashes($rowa['origid']);
    $b = addslashes($rowb['origid']);
    $result = $dbcon->query("select received from study where patientid='$a' order by received desc");
    $ra = $dbcon->fetch_row($result);
    $result = $dbcon->query("select received from study where patientid='$b' order by received desc");
    $rb = $dbcon->fetch_row($result);
    $a = $ra[0];
    $b = $rb[0];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
    if ($a < $b) return 1;
    if ($a > $b) return -1;

    return 0;
}

function cmp_received_opt($rowa, $rowb)
{
    $a = $rowa['received'];
    $b = $rowb['received'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
    if ($a < $b) return 1;
    if ($a > $b) return -1;

    return 0;
}

function cmp_when($rowa, $rowb)
{
	$a = $rowa['timestamp'];
	$b = $rowb['timestamp'];
    if (isset($_SESSION["_isOracle"])) {
        $a = strtotime($a);
        $b = strtotime($b);
    }
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_username($rowa, $rowb)
{
	$a = $rowa['username'];
	$b = $rowb['username'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_what($rowa, $rowb)
{
	$a = $rowa['what'];
	$b = $rowb['what'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_description($rowa, $rowb)
{
	$a = $rowa['description'];
	$b = $rowb['description'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_referdoc($rowa, $rowb)
{
	$a = $rowa['referringphysician'];
	$b = $rowb['referringphysician'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_readingdoc($rowa, $rowb)
{
	$a = $rowa['readingphysician'];
	$b = $rowb['readingphysician'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_sourceae($rowa, $rowb)
{
	$a = $rowa['sourceae'];
	$b = $rowb['sourceae'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cmp_sex($rowa, $rowb)
{
	$a = $rowa['sex'];
	$b = $rowb['sex'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function getSopClassName($uid)
{
	$sopClassTbl = array(
		"1.2.840.10008.5.1.4.1.1.1"			=> "Computed Radiogaphy",
		"1.2.840.10008.5.1.4.1.1.2"			=> "CT",
		"1.2.840.10008.5.1.1.30"			=> "Hardcopy Color Image",
		"1.2.840.10008.5.1.1.29"			=> "Hardcopy Grayscale Image",
		"1.2.840.10008.5.1.4.1.1.4"			=> "MR",
		"1.2.840.10008.5.1.4.1.1.20"		=> "Nuclear Medicine",
		"1.2.840.10008.5.1.4.1.1.128"		=> "Positron Emission Tomography",
		"1.2.840.10008.5.1.4.1.1.481.2"		=> "RT Dose",
		"1.2.840.10008.5.1.4.1.1.481.1"		=> "RT Image",
		"1.2.840.10008.5.1.4.1.1.481.5"		=> "RT Plan",
		"1.2.840.10008.5.1.4.1.1.481.3"		=> "RT Structure Set",
		"1.2.840.10008.5.1.4.1.1.481.4"		=> "RT Beams Treatment Record",
		"1.2.840.10008.5.1.4.1.1.481.6"		=> "RT Brachy Treatment Record",
		"1.2.840.10008.5.1.4.1.1.481.7"		=> "RT Treatment Summary Record",
		"1.2.840.10008.5.1.4.1.1.7"			=> "Secondary Capture",
		"1.2.840.10008.5.1.4.1.1.7.1"		=> "Multi-frame Single Bit Secondary Capture",
		"1.2.840.10008.5.1.4.1.1.7.2"		=> "Multi-frame Grayscale Byte Secondary Capture",
		"1.2.840.10008.5.1.4.1.1.7.3"		=> "Multi-frame Grayscale Word Secondary Capture",
		"1.2.840.10008.5.1.4.1.1.7.4"		=> "Multi-frame True Color Secondary Capture",
		"1.2.840.10008.5.1.4.1.1.9"			=> "Stand-alone Curve",
		"1.2.840.10008.5.1.4.1.1.9.1"		=> "12-lead ECG Waveform",
		"1.2.840.10008.5,1,4,1,1,9,2"		=> "General ECG Waveform",
		"1.2.840.10008.5.1.4.1.1.9.3"		=> "Ambulatory ECG Waveform",
		"1.2.840.10008.5.1.4.1.1.9.2.1"		=> "Hemodynamic Waveform",
		"1.2.840.10008.5.1.4.1.1.9.3.1"		=> "Cardiac Eletrophysiology Waveform",
		"1.2.840.10008.5.1.4.1.1.9.4.1"		=> "Basic Voice Audio Waveform",
		"1.2.840.10008.5.1.4.1.1.10"		=> "Stand-alone Modality LUT",
		"1.2.840.10008.5.1.4.1.1.8"			=> "Stand-alone Overlay",
		"1.2.840.10008.5.1.4.1.1.11"		=> "Stand-alone VOI LUT",
		"1.2.840.10008.5.1.4.1.1.11.1"		=> "Grayscale Softcopy Presentation State",
		"1.2.840.10008.5.1.4.1.1.129"		=> "Stand-alone PET Curve",
		"1.2.840.10008.5.1.1.27"			=> "Stored Print",
		"1.2.840.10008.5.1.4.1.1.6.1"		=> "Ultrasound",
		"1.2.840.10008.5.1.4.1.1.6"			=> "Ultrasound (Retired)",
		"1.2.840.10008.5.1.4.1.1.3.1"		=> "Ultrasound Multi-frame Image",
		"1.2.840.10008.5.1.4.1.1.3"			=> "Ultrasound Multi-frame Image (Retired)",
		"1.2.840.10008.5.1.4.1.1.12.1"		=> "X-Ray Angiographic Image",
		"1.2.840.10008.5.1.4.1.1.12.2"		=> "X-Ray Radiofluoroscopic Image",
		"1.2.840.10008.5.1.4.1.1.1.1"		=> "Digital X-Ray - For Presentation",
		"1.2.840.10008.5.1.4.1.1.1.1.1"		=> "Digital X-Ray - For Processing",
		"1.2.840.10008.5.1.4.1.1.1.2"		=> "Digital Mammography - For Presentation",
		"1.2.840.10008.5.1.4.1.1.1.2.1"		=> "Digital Mammography - For Processing",
		"1.2.840.10008.5.1.4.1.1.1.3"		=> "Digital Intra-oral X-Ray - For Presentation",
		"1.2.840.10008.5.1.4.1.1.1.3.1"		=> "Digital Intra-oral X-Ray - For Processing",
		"1.2.840.10008.5.1.4.1.1.77.1.1"	=> "VL Endoscopic",
		"1.2.840.10008.5.1.4.1.1.77.1.2"	=> "VL Microscopic",
		"1.2.840.10008.5.1.4.1.1.77.1.3"	=> "VL Slide-Coordinates Microscopic",
		"1.2.840.10008.5.1.4.1.1.77.4"		=> "VL Photographic",
	);
	$value = "";
	if (isset($sopClassTbl[$uid]))
		$value = $sopClassTbl[$uid];
	return $value;
}

function isHL7OptionInstalled()
{
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $dir = str_replace("\\", "/", $dir);
    $dir = substr($dir, 0, strrpos($dir, '/') + 1);
    return (file_exists($dir . "PacsOneHL7.exe") || file_exists($dir . "MediPacsHL7.exe"));
}

function encodeHeader($input, $charset = 'ISO-8859-1')
{
    preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $input, $matches);
    foreach ($matches[1] as $value) {
        $replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
        $input = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $input);
    }
    return $input;
}

function MakeStudyUid()
{
    $uid = "1.2.826.0.1.3680043.2.737." . rand(100, 32768) . ".";
    $uid .= date("Y.n.j.G");
    $mins = 0 + date("i");
    $secs = 0 + date("s");
    $uid .= ".$mins.$secs";
    return $uid;
}

function reverseDate($date)
{
    $value = $date;
    $tokens = explode("-", str_replace(".", "-", $date));
    if (count($tokens) == 3) {
        $value = sprintf("%s-%s-%s", $tokens[2], $tokens[1], $tokens[0]);
    }
    return $value;
}

function urlReplace($url, $param, $value)
{
    $after = stristr($url, "$param=");
    if ($after) {
        if (strchr($after, '&')) {
            $pattern = "/(.*)$param=(.*?)&(.*)/i";
            $repl = '${1}' . "$param=$value&" . '${3}';
        } else {
            $pattern = "/(.*)$param=(.*)/i";
            $repl = '${1}' . "$param=$value";
        }
        $url = preg_replace($pattern, $repl, $url);
    } else {
        // parameter not found, append it
        $and = (strrpos($url, "?") == false)? "?" : "&";
        $url .= $and . "$param=$value";
    }
    return $url;
}

function cmp_reqdoc($rowa, $rowb)
{
	$a = $rowa['requestingphysician'];
	$b = $rowb['requestingphysician'];
	if ($a < $b) return -1;
	if ($a > $b) return 1;

	return 0;
}

function cleanPostPath($path, $toUnixPath = true)
{
    // strip the extra '\' added by the magic quotes
    $ret = get_magic_quotes_gpc()? stripslashes($path) : $path;
    if ($toUnixPath) {
        // change to Unix-style path
        $ret = str_replace("\\", "/", $ret);
    }
    return $ret;
}

function parseIniFile(&$inifile)
{
    $result = array();
    $file = file($inifile);
    foreach ($file as $line) {
        $tokens = preg_split("/[\s=]+/", $line);
        if (count($tokens) > 1) {
            if (strcasecmp($tokens[0], "Database") == 0) {
                $result['Database'] = $tokens[1];
            }
            else if (strcasecmp($tokens[0], "Schema") == 0) {
                $result['Schema'] = $tokens[1];
            }
            else if (strcasecmp($tokens[0], "DatabaseHost") == 0) {
                $result['DatabaseHost'] = $tokens[1];
            }
        }
    }
    return $result;
}

function getServerInstances()
{
    $result = array();
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
    // goto the parent directory of "/php"
    $dir = substr($dir, 0, strlen($dir) - 3);
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (strcasecmp(filetype($dir . $file), "dir")) {
                    $tokens = explode(".", $file);
                    if ( count($tokens) == 2 &&
                         (strcasecmp($tokens[1], "ini") == 0) )
                    {
                        $inifile = $dir . $file;
                        $keyValue = parseIniFile($inifile);
                        $result[ strtolower($tokens[0]) ] = $keyValue['Database'];
                    }
                }
            }
            closedir($dh);
        }
    }
    return $result;
}

function getDatabaseHost($aetitle)
{
    $hostname = "localhost";
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $dir = substr($dir, 0, strlen($dir) - 3);
    $ini = $dir . $aetitle . ".ini";
    if (file_exists($ini)) {
        $parsed = parseIniFile($ini);
        if (count($parsed) && isset($parsed['DatabaseHost'])) {
            $hostname = $parsed['DatabaseHost'];
        }
    }
    return $hostname;
}

function pacsone_gettext($text)
{
    return function_exists("gettext")? _($text) : $text;
}

function getDatabaseName($aetitle)
{
    $value = "";
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $dir = substr($dir, 0, strlen($dir) - 3);
    $ini = $dir . $aetitle . ".ini";
    if (file_exists($ini)) {
        $parsed = parseIniFile($ini);
        if (count($parsed) && isset($parsed['Database'])) {
            $value = $parsed['Database'];
        }
    }
    return $value;
}

function getDatabaseNames(&$oracle)
{
    $result = array();
    $dir = dirname($_SERVER['SCRIPT_FILENAME']);
    // goto the parent directory of "/php"
    $dir = substr($dir, 0, strlen($dir) - 3);
    if (is_dir($dir)) {
        // check if this is an Oracle database
        global $ORACLE_CONFIG_FILE;
        $file = $dir . $ORACLE_CONFIG_FILE;
        if (file_exists($file))
            $oracle = true;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (strcasecmp(filetype($dir . $file), "dir")) {
                    $tokens = explode(".", $file);
                    if ( count($tokens) == 2 &&
                         (strcasecmp($tokens[1], "ini") == 0) )
                    {
                        $inifile = $dir . $file;
                        $database = parseIniFile($inifile);
                        if (count($database))
                          $result[] = $database;
                    }
                }
            }
            closedir($dh);
        }
    }
    return $result;
}

function getThumbnailImageFlashDirs(&$dbcon, &$thumbnaildir, &$imagedir, &$flashdir) {
	die( __FUNCTION__ );
    $flashdir = dirname($_SERVER['SCRIPT_FILENAME']);
    $thumbnaildir = $flashdir . "/";
    $imagedir = $flashdir . "/";
    $flashdir .= "/flash/";
    $result = $dbcon->query("select thumbnaildir,imagedir,flashdir from config");
    if ($result && ($row = $dbcon->fetch_row($result))) {
        if (strlen($row[0]))
            $thumbnaildir = $row[0];
        if (strcmp(substr($thumbnaildir, strlen($thumbnaildir)-1, 1), "/"))
    	    $thumbnaildir .= "/";
        if (strlen($row[1]))
            $imagedir = $row[1];
        if (strcmp(substr($imagedir, strlen($imagedir)-1, 1), "/"))
    	    $imagedir .= "/";
        if (strlen($row[2]))
            $flashdir = $row[2];
        if (strcmp(substr($flashdir, strlen($flashdir)-1, 1), "/"))
    	    $flashdir .= "/";
    }
}

function deleteImages($entry)
{
	die( __FUNCTION__ );
    if (count($entry) == 0)
        return "home.php";
    global $dbcon;
	$url = "image.php";
	$ok = array();
	$errors = array();
	// find patient, study and series ids
	$result = $dbcon->query("select seriesuid from image where uuid='$entry[0]'");
	$row = $dbcon->fetch_row($result);
	$seriesid = $row[0];
	$result = $dbcon->query("select studyuid from series where uuid='$seriesid'");
	$row = $dbcon->fetch_row($result);
	$studyid = $row[0];
	$result = $dbcon->query("select patientid from study where uuid='$studyid'");
	$row = $dbcon->fetch_row($result);
	$patientid = $row[0];
	// update the URL for refreshing
	$url .= "?patientId=$patientid&studyId=$studyid&seriesId=$seriesid";
    $thumbnaildir = $imagedir = $flashdir = "";
    getThumbnailImageFlashDirs($dbcon, $thumbnaildir, $imagedir, $flashdir);
	foreach ($entry as $value) {
		$junks = array(
        	$thumbnaildir . "thumbnails/$value.jpg",
        	$thumbnaildir . "thumbnails/$value.gif",
        	$imagedir . "images/$value.jpg",
        	$imagedir . "images/$value.gif",
        	$imagedir . "images/temp.$value.jpg",
        	$imagedir . "images/temp.$value.gif",
		);
        // delete any related attachments
        $result = $dbcon->query("select path from attachment where uuid='$value'");
        if ($result) {
            while ($row = $dbcon->fetch_row($result))
                unlink($row[0]);
        }
        $dbcon->query("delete from attachment where uuid='$value'");
        // delete image notes
        $dbcon->query("delete from imagenotes where uuid='$value'");
        // remove physical storage file
        $query = "select * from image where uuid='$value'";
        $result = $dbcon->query($query);
        if ($dbcon->num_rows($result) == 1) {
            $row = $dbcon->fetch_array($result);
            if (file_exists( $row['path'] ))
                unlink( $row['path'] );
            // remove post-receive compressed files
            $junks[] = $row['path'] . ".ls";
            $junks[] = $row['path'] . ".ly";
            $junks[] = $row['path'] . ".rle";
            $junks[] = $flashdir . basename($row['path']) . ".swf";
            $junks[] = $row['path'] . ".j2k";
            $junks[] = $row['path'] . ".encap";
        }
		$query = "delete from image where uuid='$value'";
		if (!$dbcon->query($query)) {
			$errors[$value] = "Database Error " . $dbcon->getErrno() . ": " . $dbcon->getError();
		}
		else
			$ok[] = $value;
		// remove any thumbnail and cached ImageMagick files
		foreach ($junks as $file) {
			if (file_exists($file))
				unlink($file);
		}
		// delete any derived tables
		$query = "delete from conceptname where uuid='$value'";
		$dbcon->query($query);
		$query = "delete from commitsopref where sopinstance='$value'";
		$dbcon->query($query);
	}
	return $url;
}

function deleteSeries($entry)
{
	die( __FUNCTION__ );
    if (count($entry) == 0)
        return "home.php";
    global $dbcon;
	$url = "series.php";
	$ok = array();
	$errors = array();
	// find study ID and patient ID
	$result = $dbcon->query("select studyuid from series where uuid='$entry[0]'");
    if (!$result || ($dbcon->num_rows($result) == 0))
        return "home.php";
	$row = $dbcon->fetch_row($result);
	$studyid = $row[0];
	$result = $dbcon->query("select patientid from study where uuid='$studyid'");
	$row = $dbcon->fetch_row($result);
	$patientid = $row[0];
	// update URL for refreshing
	$url .= "?patientId=$patientid&studyId=$studyid";
	foreach ($entry as $value) {
        // find all related image rows
        $images = array();
        $query = "select * from image where seriesuid='$value'";
        $result = $dbcon->query($query);
        while ($row = $dbcon->fetch_array($result)) {
            $images[] = $row['uuid'];
        }
        // delete all related image rows
        deleteImages($images);
		$query = "delete from series where uuid='$value'";
		if (!$dbcon->query($query)) {
			$errors[$value] = "Database Error " . $dbcon->getErrno() . ": " . $dbcon->getError();
		}
		else
			$ok[] = $value;
	}
	return $url;
}

function deleteStudies($entry)
{
	die( __FUNCTION__ );
    if (count($entry) == 0)
        return "home.php";
    global $dbcon;
	$url = "study.php";
	$ok = array();
	$errors = array();
	// find the patient id
	$result = $dbcon->query("select patientid from study where uuid='$entry[0]'");
    if (!$result || ($dbcon->num_rows($result) == 0))
        return "home.php";
	$row = $dbcon->fetch_row($result);
	$patientid = $row[0];
	// update the URL for refreshing
	$url .= "?patientId=$patientid";
	foreach ($entry as $value) {
        // delete any related attachments
        $result = $dbcon->query("select path from attachment where uuid='$value'");
        if ($result) {
            while ($row = $dbcon->fetch_row($result)) {
                if (file_exists($row[0]))
                    unlink($row[0]);
            }
        }
        $dbcon->query("delete from attachment where uuid='$value'");
        // delete study notes
        $dbcon->query("delete from studynotes where uuid='$value'");
        // find all related series rows
        $series = array();
        $query = "select * from series where studyuid='$value'";
        $result = $dbcon->query($query);
        while ($row = $dbcon->fetch_array($result)) {
            $series[] = $row['uuid'];
        }
        // delete all related series rows
        deleteSeries($series);
		$query = "delete from study where uuid='$value'";
		if (!$dbcon->query($query)) {
			$errors[$value] = "Database Error " . $dbcon->getErrno() . ": " . $dbcon->getError();
		}
		else
			$ok[] = $value;
	}
	return $url;
}

function deletePatients($entry)
{
	die( __FUNCTION__ );
    global $dbcon;
	$url = "browse.php";
    $subTables = array(
        "otherpatientids",
        "patientspeciescode",
        "patientbreedcode",
        "breedregistration",
    );
	foreach ($entry as $value) {
        $value = urldecode($value);
        $escaped = $dbcon->escapeQuote($value);
        // find all related study rows
        $studies = array();
        $query = "select * from study where patientid='$escaped'";
        $result = $dbcon->query($query);
        while ($row = $dbcon->fetch_array($result)) {
            $studies[] = $row['uuid'];
        }
        // delete all related study rows
        deleteStudies($studies);
        // delete sub-tables
        foreach ($subTables as $sub) {
            $query = "delete from $sub where patientid='$escaped'";
		    if (!$dbcon->query($query)) {
			    die("Database Error " . $dbcon->getErrno() . ": " . $dbcon->getError());
		    }
        }
		$query = "delete from patient where origid='$escaped'";
		if (!$dbcon->query($query)) {
			die("Database Error " . $dbcon->getErrno() . ": " . $dbcon->getError());
		}
	}
	return $url;
}

function deleteWorklists($entry)
{
	die( __FUNCTION__ );
    global $dbcon;
	$url = "worklist.php";
	$ok = array();
	$errors = array();
    $tables = array(
        "worklist", "scheduledps", "requestedprocedure", "protocolcode", "procedurecode",
        "referencedpps", "referencedstudy", "referencedpatient", "referencedvisit",
    );
	foreach ($entry as $uid) {
        $success = true;
        // delete all related table rows
        foreach ($tables as $table) {
		    $query = "delete from $table where studyuid='$uid'";
		    if (!$dbcon->query($query)) {
			    $errors[$uid] = "Database Error deleting '$table' table rows: " . $dbcon->getErrno() . ": " . $dbcon->getError();
                print "<p><h3><font color=red>";
                print pacsone_gettext("Fatal Error: ") . $errors[$uid] . "</font></h3>\n";
                exit();
		    }
        }
        if ($success)
            $ok[] = $uid;
	}
    return $url;
}