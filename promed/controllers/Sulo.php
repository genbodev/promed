<?php
/**
 * PersonIdent
 */
class Sulo extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database('testsulo');
		//$this->load->database();
		$this->load->model('Sulo_model', 'sulomodel');
	}
	
	/**
	*	Получение данных из СУЛО
	*/
	function getDrugsFromSULO()
	{
		set_time_limit(0);
		$this->load->library('textlog', array('file'=>'SULO_'.date('Y-m-d').'.log'));
		
		$this->textlog->add('Запрос к СУЛО: стартуем!');
		$curl_log = fopen('curl_log.txt','a+');
		//var_dump(SULO_URL);die;
		$url_1 = SULO_URL.'/getDrugs?token='.SULO_TOKEN;
		$ch_1 = curl_init();
		curl_setopt($ch_1, CURLOPT_URL, $url_1);
		curl_setopt($ch_1, CURLOPT_HEADER, 0);
		curl_setopt($ch_1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch_1, CURLOPT_CONNECTTIMEOUT, 60);
		//curl_setopt($ch_1, CURLOPT_PROXY, '192.168.3.13');
		//curl_setopt($ch_1, CURLOPT_PROXYPORT, '3128');
		//curl_setopt($ch_1, CURLOPT_PROXYUSERPWD, 'zharkyn_bolashak'.':'.'');
		if(defined('SULO_PROXY')) {
			curl_setopt($ch_1, CURLOPT_PROXY, SULO_PROXY);
			curl_setopt($ch_1, CURLOPT_PROXYPORT, SULO_PORT);
			curl_setopt($ch_1, CURLOPT_PROXYUSERPWD, SULO_LOGIN.':'.SULO_PASSWORD);
		} 
		curl_setopt($ch_1, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch_1, CURLOPT_TIMEOUT, 100);

		curl_setopt($ch_1, CURLOPT_VERBOSE, 1);
		curl_setopt($ch_1, CURLOPT_STDERR, $curl_log);	

		curl_setopt( $ch_1, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch_1, CURLOPT_SSL_VERIFYHOST, false );

		curl_setopt($ch_1, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Accept: application/json"
		));

		$res = curl_exec($ch_1);
		$res_code = curl_getinfo($ch_1, CURLINFO_HTTP_CODE);
		$res_error = curl_error($ch_1);
		
		curl_close($ch_1);
		fclose($curl_log);

		$this->textlog->add('Запрос к СУЛО: закончили.');
		$resp = json_decode($res, true);

		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';
			print_r(array(
				SULO_URL,
				SULO_TOKEN,
				SULO_PROXY,
				SULO_PORT,
				SULO_LOGIN,
				SULO_PASSWORD,
				$url_1,
				$res_code,
				$res_error,
				$res,
			));exit;
		}

		$registerids = array_chunk($resp, 150);
		for($i=0; $i<count($registerids); $i++)
		{
			$registerids[$i] = implode(",", $registerids[$i]);
		}
		//$registerids = array();
		//$registerids[0] = '15050012746,15050012747,15050012748,15050012749,15050012750';
		$register_count = count($registerids);
		//Получили массив идентификаторов. По каждому набору (200шт) получаем подробные данные
		$this->textlog->add('Начали загрузку. Всего будет итераций: '.$register_count);
		//$this->textlog->add('Вжух - и итераций будет всего 5!');
		//$register_count = 5;
		
		//Добавляем запись в ObjectSynchronLog
		$ObjectSynchronLog_id = $this->sulomodel->AddRegisterToSyncronLog(999999);
		
		for($i=0; $i<$register_count; $i++)
		{
			$this->textlog->add('Итерация '.($i+1). ' из '.$register_count);
			$url_2 = $url_1.'&registerids='.$registerids[$i];
			$post = array(
				'token' => SULO_TOKEN,
				'registerids' => $registerids[$i]
			);
			$ch_2 = curl_init();
			curl_setopt($ch_2, CURLOPT_URL, $url_2);
			curl_setopt($ch_2, CURLOPT_HEADER, 0);
			curl_setopt($ch_2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch_2, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch_2, CURLOPT_POST, true);
			curl_setopt($ch_2, CURLOPT_POSTFIELDS, $post);
			if(defined('SULO_PROXY')) {
				curl_setopt($ch_2, CURLOPT_PROXY, SULO_PROXY);
				curl_setopt($ch_2, CURLOPT_PROXYPORT, SULO_PORT);
				curl_setopt($ch_2, CURLOPT_PROXYUSERPWD, SULO_LOGIN.':'.SULO_PASSWORD);
			}
			curl_setopt($ch_2, CURLOPT_TIMEOUT, 100);

			curl_setopt( $ch_2, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch_2, CURLOPT_SSL_VERIFYHOST, false );

			/*curl_setopt($ch_2, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json",
				"Accept: application/json"
			));*/


			$res = curl_exec($ch_2);
			curl_close($ch_2);
			$resp = json_decode($res, true);
			$this->textlog->add('Количество элементов - '.count($resp));
			for($k=0; $k<count($resp); $k++){
				
				$this->textlog->add('Итерация '.($i+1). ', элемент '.($k+1));
				//Добавляем данные в DR_Register
				$DR_Register = $resp[$k]['Register'];

				//Проверим, нет ли уже записей по этому Register_id. Если есть, то в этой итерации ничего не делаем.
				$Register_exists = $this->sulomodel->check_DR_Register($DR_Register['id']);
				if(!$Register_exists)
				{
					$this->textlog->add('Итерация '.($i+1). ', элемент '.($k+1).' id='.$DR_Register['id'].' - НОВЫЙ');
					$DR_Register_id = $this->sulomodel->Add_DR_Register($DR_Register, $ObjectSynchronLog_id);

					//Обрабатываем тег manufactures (их может быть несколько в рамках одного Register)
					$manufactures = $DR_Register['manufactures'];
					for($m=0; $m<count($manufactures); $m++)
					{
						$manufactures_success = $this->sulomodel->Add_manufactures($manufactures[$m], $DR_Register_id);
					}

					
					//Обрабатываем тег storageMeasure (единица измерения срока хранения). Попутно нужно будет обновить DR_Register, указав там StorageMeasure_id
					$storageMeasure = $DR_Register['storageMeasure'];
					$StorageMeasure_success = $this->sulomodel->addStorageMeasure($storageMeasure,$DR_Register_id);	

					//Обрабатываем тег Boxes
					$boxes = $DR_Register['boxes'];
					for($b=0; $b<count($boxes); $b++)
					{
						$boxes_success = $this->sulomodel->addBoxes($boxes[$b],$DR_Register_id);
					}

					//Обрабатываем тег detailsDrug
					$detailsDrug = $DR_Register['detailsDrug'];
					$detailsDrug_success = $this->sulomodel->addDetailsDrug($detailsDrug, $DR_Register_id);
					
					//Обрабатываем тег detailsTechnic
					$detailsTechnic = $DR_Register['detailsTechnic'];
					if(is_array($detailsTechnic) && count($detailsTechnic) > 0)
					{
						$detailsTechnic_success = $this->sulomodel->addDetailsTechnic($detailsTechnic, $DR_Register_id);
					}

					//Обрабатываем тег registermtparts
					$registermtparts = $DR_Register['registermtparts'];
					if(is_array($registermtparts) && count($registermtparts) > 0) 
					{
						for($r=0; $r < count($registermtparts); $r++)
						{
							$registermtparts_success = $this->sulomodel->addRegistermtparts($registermtparts[$r], $DR_Register_id);
						}
					}

					//Обрабатываем тег Certificate
					$Certificate = $resp[$k]['Certificate'];
					for($c=0; $c<count($Certificate); $c++)
					{
						$Certificate_success = $this->sulomodel->addCertificate($Certificate[$c], $DR_Register_id);
					}

					//Обрабатываем тег RegisterNmirk
					$RegisterNmirk = $resp[$k]['RegisterNmirk'];
					for($r=0; $r<count($RegisterNmirk); $r++)
					{
						$registerNmirk_success = $this->sulomodel->addRegisterNmirk($RegisterNmirk[$r], $DR_Register_id);
					}
				}
				else
				{
					$this->textlog->add('Итерация '.($i+1). ', элемент '.($k+1).' id='.$DR_Register['id'].' - УЖЕ СУЩЕСТВУЕТ');
				}
			}
		}
		$this->textlog->add('Finish');
		
		$this->textlog->add('Начинаем выполнение хранимой процедуры');
		$result_xp = $this->sulomodel->execDBUpdate();
		$this->textlog->add('Закончили выполнение хранимой процедуры');
		//var_dump($result_xp);die;
		return array(array('success' => true, 'Error_Msg' => ''));
	}

}

?>