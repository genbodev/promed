<?php

$post = $_POST;

if (!(is_array($post)&&isset($post['data'])&&isset($post['action']))) {
	return false;
}

$data = json_decode($post['data'],true);

if (!is_array($data)) {
	return false;
}
//var_dump($data);
include_once 'Dicom/dicom.php';

switch ($post['action']) {
	case 'cfind':
		$AETReceiver = $data['AETReceiver'];
		$WorkStationArray = $data['WorkStations'];
		$patientid = '';
		$studyid = '';
		$accession = '';
		$referdoc = '';
		$date = date('Ymd',strtotime($data['begDate'])).'-'.date('Ymd',strtotime($data['endDate']));
		$result = array();
		foreach ($WorkStationArray as $LpuPacs) {
			
			$hostname = '';//$remote_server['hostname'];

			$error = '';
			
			$assoc = new Association( $LpuPacs['LpuPacs_ip'], $hostname, $LpuPacs['LpuPacs_port'], $LpuPacs['LpuPacs_aetitle'], $AETReceiver );
			if ($assoc->socket) {
				$identifier = new CFindIdentifierStudyRoot( $studyid, $date, $accession, $referdoc );

				$matches = $assoc->find( $identifier, $error );
				if ( strlen( $error ) ) {
					return json_encode(array( array( 'Error_Msg' => sprintf( 'find() failed: error = %s', $error ) ) ));
				} else {
					$currentResult = outputRemoteStudies( $identifier, $matches );
					for ($i=0;$i<count($currentResult);$i++) {
						$currentResult[$i]['LpuPacs_ip'] = $LpuPacs['LpuPacs_ip'];
						$currentResult[$i]['LpuPacs_port'] = $LpuPacs['LpuPacs_port'];
						$currentResult[$i]['LpuPacs_aetitle'] = $LpuPacs['LpuPacs_aetitle'];
						$currentResult[$i]['LpuSection_FullName'] = $LpuPacs["LpuSection_FullName"];
					}
					$result = array_merge($result,$currentResult);
				}
			}
		}
		echo (json_encode($result));
		return;
		break;
	case 'cmove':
		set_time_limit(0);
		$assoc = new Association( $data['LpuPacs_ip'], '', $data['LpuPacs_port'], $data['LpuPacs_aetitle'], 'PROMED_LOCAL' );
		$identifier = new CMoveIdentifierStudy($data['Patient_id'],$data['Study_UID']);
		$res = $assoc->move($data['AETReceiver'], $identifier, $error);
		
		if ( strlen( $error ) ) {
			return json_encode(array( array( 'success' =>false,'Error_Msg' => sprintf( 'move() failed: error = %s', $error ) ) ));
		} else {
			return json_encode( array( array('success' =>true, 'Error_Msg' => '' ) ));
		}
		break;
	default:
		break;
}


function outputRemoteStudies( $identifier, $matches ){

	$output = array();	

	$count = 0;
	// some AEs (e.g., CONQUEST) returns 2 C-FIND datasets with one of them being empty
	foreach ( $matches as $match ) if ( sizeof( $match->attrs ) ) {
		$count++;
	}
	if ( $count ) {
		$checkbox = 1;
	}


	//
	// Получаем заголовки столбцов
	//

	$attrs = $identifier->attrs;
	foreach ( $attrs as $attr ) {
		if ( $attr == 0x00200010 ) {
			continue;
		}
		$name = $identifier->getAttributeName( $attr );
		// display Study ID instead of Study UID
		if ( $attr == 0x0020000d ) {
			$name = pacsone_gettext("Study ID");
		}
	}


	//
	// Получаем значения столбцов построчно
	//

	$i = 0;
	foreach ( $matches as $match ) {
		if ( !sizeof( $match->attrs ) ) {
			continue;
		}

		$level = $match->getQueryLevel();
		//print "<input type='hidden' name='level[]' value=$level>";
		if ( $checkbox ) {
			$uid = urlencode( $match->getStudyUid() );
			//print "<td><input type='checkbox' name='entry[]' value='$uid'></input></td>";
			// pass along the Patient ID of the returned study
			if ( $match->hasKey( 0x00100020 ) ) {
				$value = http_build_query( array( $uid => $match->getPatientId() ) );
				//print "<input type='hidden' name='patientids[]' value=\"$value\">";
			}
		}
		$output[ $i ] = array();
		foreach ( $attrs as $key ) {
			if ( $key == 0x00200010 ) {
				continue;
			}
			if ( $match->hasKey( $key ) ) {
				$value = $match->attrs[ $key ];
				if ( $key == 0x0020000d ) {
					$patientid = urlencode( $match->getPatientId() );
					//$href = "remoteSeries.php?aetitle=$aetitle&patientid=$patientid&uid=$value";
					$value = "Study Details";
					if ( isset( $match->attrs[ 0x00200010 ] ) && strlen( $match->attrs[ 0x00200010 ] ) ) {
						$value = $match->attrs[ 0x00200010 ];
					}
					//print "<td><a href=$href>$value</a></td>";
				} else {
					$value = trim( $value );
					if ( strlen( $value ) ) {
						//print "<td>$value</td>";
					} else {
						//print "<td>" . pacsone_gettext("N/A") . "</td>";
						$value = pacsone_gettext("N/A");
					}
				}
			} else {
				//print "<td>" . pacsone_gettext("N/A") . "</td>";
				$value = pacsone_gettext("N/A");
			}

			// Для вывода в грид определим имена ключей более понятным языком
			switch( $key ){
				// Study Date
				case 0x00080020: $grid_key = 'study_date';break;
				// Study Description
				case 0x00081030: $grid_key = 'study_description'; break;
				// Patient's Name
				case 0x00100010: $grid_key = 'patient_name'; break;
				// Patient ID
				case 0x00100020: $grid_key = 'patient_id'; break;
				// display Study ID instead of Study UID
				case 0x0020000d: $grid_key = 'study_id'; break;
				// Number of Study Related Instances
				case 0x00201208: $grid_key = 'number_of_study_related_instances'; break;
				//
				default: 
//						echo $key.' '.$value;
					$grid_key = null; break;
			}
			if ( $grid_key !== null ) {
				$output[ $i ][ $grid_key ] = $value;
			}
		}

		$output[ $i ] ['Study_UID'] = $uid;
		$i++;
		
	}
	return $output;
}		
?>
