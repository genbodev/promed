<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size:10pt;}    
p {margin:0 0 10px}
table {font-size:12pt; vertical-align: top;}
table td {font-size:10pt; padding-right: 30px;}
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

<title>Приказ по режиму работы ССМП	{CallCardDate}</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->
     
<center>
	<h2>Приказ по режиму работы ССМП<br/>
	<?
	
	$dateStart = $_GET['dateStart'];
	$dateFinish = $_GET['dateFinish'];
	
	if($dateFinish == $dateStart) { 
		print $dateFinish;
	} else {
		print "с ".$dateStart." по ".$dateFinish;
	}?>
	</h2>
</center>
<table>

<?
$resp = $resp['data'];
$build = '';
foreach($resp as $row) if ($row['closed'] == 'true') {?>
	<tr>
		<td style="min-width:70px;"><?=($build != $row['LpuBuilding_Name'])?$row['LpuBuilding_Name']:''?></td>
		<td style="min-width:70px;"></td>
		<td style="min-width:70px;"></td>
		<td style="min-width:70px;"></td>
		<td style="min-width:50px;"></td>
		<td style="min-width:50px;"></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td><?=$row['EmergencyTeam_Num']?></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_HeadShiftFIO']?></td>
		<td>Старший бригады</td>
		<td><?=($row['EmergencyTeam_Head1StartTime'] != '')?$row['EmergencyTeam_Head1StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Head1FinishTime'] != '')?$row['EmergencyTeam_Head1FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td><?=($row['EmergencyTeamDuty_Comm'] != '')?$row['EmergencyTeamDuty_Comm']:$row['EmergencyTeamDuty_Comm']?></td>						
	</tr>
	<?if ($row['EmergencyTeam_HeadShift2FIO'] != '') {?>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_HeadShift2FIO']?></td>
		<td>Старший бригады</td>
		<td><?=($row['EmergencyTeam_Head2StartTime'] != '')?$row['EmergencyTeam_Head2StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Head2FinishTime'] != '')?$row['EmergencyTeam_Head2FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td></td>
	</tr>
	<?}?>
	<?if ($row['EmergencyTeam_DriverFIO'] != '') {?>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_DriverFIO']?></td>
		<td>Водитель</td>
		<td><?=($row['EmergencyTeam_Driver1StartTime'] != '')?$row['EmergencyTeam_Driver1StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Driver1FinishTime'] != '')?$row['EmergencyTeam_Driver1FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td></td>
	</tr>
	<?}?>
	<?if ($row['EmergencyTeam_Driver2FIO'] != '') {?>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_Driver2FIO']?></td>
		<td>Водитель</td>
		<td><?=($row['EmergencyTeam_Driver2StartTime'] != '')?$row['EmergencyTeam_Driver2StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Driver2FinishTime'] != '')?$row['EmergencyTeam_Driver2FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td></td>
	</tr>
	<?}?>
	<?if ($row['EmergencyTeam_Assistant1FIO'] != '') {?>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_Assistant1FIO']?></td>
		<td>Ассистент</td>
		<td><?=($row['EmergencyTeam_Assistant1StartTime'] !='')?$row['EmergencyTeam_Assistant1StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Assistant1FinishTime'] != '')?$row['EmergencyTeam_Assistant1FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td></td>
	</tr>
	<?}?>
	<?if ($row['EmergencyTeam_Assistant2FIO'] != '') {?>
	<tr>
		<td></td>
		<td></td>
		<td><?=$row['EmergencyTeam_Assistant2FIO']?></td>
		<td>Ассистент</td>
		<td><?=($row['EmergencyTeam_Assistant2StartTime'] != '')?$row['EmergencyTeam_Assistant2StartTime']:$row['EmergencyTeamDuty_TStart']?></td>				
		<td><?=($row['EmergencyTeam_Assistant2FinishTime'] != '')?$row['EmergencyTeam_Assistant2FinishTime']:$row['EmergencyTeamDuty_TFinish']?></td>						
		<td></td>
	</tr>
	<?}?>
<?
$build = $row['LpuBuilding_Name'];
}?>
</table>
 </body>
</html>