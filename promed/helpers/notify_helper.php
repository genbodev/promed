<?php

/**
 * Notify_helper - хелпер c функциями для отправки оповещения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Petukhov Ivan (megatherion@list.ru)
 * @version      25.09.2013
 */
defined('BASEPATH') or die('No direct script access allowed');

/**
 * Отправка SMS c уведомлением
 */
function sendNotifySMS( $data ) {

	$CI = & get_instance();
	$CI->load->model('UserPortal_model', 'upmodel');

	// Cохраняем информацию в БД, чтобы получить идентификатор
	$sms_id = $CI->upmodel->createSMS($data);
	
	$CI->config->load('sms');
	$sms_config = $CI->config->item('sms');
	
	$lib = isset($sms_config['lib']) ? $sms_config['lib'] : 'MarketSMS';
	require_once("promed/libraries/{$lib}.php");
	
	// Транспорт для отправки
	$transport = new $lib( $sms_config['login'], $sms_config['password'], isset($sms_config['sender']) ? $sms_config['sender'] : null );

	// Отправка
	$res = $transport->send(array(
		'id' => 'pm_' . $sms_id,
		'number' => $data['UserNotify_Phone'],
		'text' => $data['text']
	));

	$CI->upmodel->setSMSStatus($sms_id, $transport->status);
	
	return $res;
}

/**
 * Отправка Email с уведомлением
 */
function sendNotifyEmail( $data ) {
	$CI = & get_instance();
	$CI->load->library('email');
	$data['wordwrap'] = (isset($data['wordwrap']) && $data['wordwrap'] === false) ? false : true;
	@$resultsend = $CI->email->sendKvrachu($data['EMail'], $data['title'], $data['body'], '', $data['wordwrap']);
}

/**
 * Отправка СМС пользователям Промеда
 */
function sendPmUserNotifySMS( $data, $ignoreException = false ) {
	$CI = & get_instance();
	$CI->config->load('sms');
	$sms_config = $CI->config->item('sms');
	
	$lib = isset($sms_config['lib']) ? $sms_config['lib'] : 'MarketSMS';
	require_once("promed/libraries/{$lib}.php");
	
	// Транспорт для отправки
	$transport = new $lib( $sms_config['login'], $sms_config['password'], isset($sms_config['sender']) ? $sms_config['sender'] : null );

	// Отправка
	if(!$ignoreException){
		set_error_handler(function ($errno, $errstr) {
			throw new Exception('Ошибка при отправке смс-сообщения!');
		}, E_ALL & ~E_NOTICE);
	}
	
	$res = $transport->send(array(
		'id' => 'pm_' . $data['sms_id'],
		'number' => $data['pmUser_Phone'],
		'text' => $data['text']
	));
	if(!$ignoreException) restore_error_handler();

	return $res;
}

/**
 *  отправить пуш-уведомление пользователю портала
 */
function sendPushNotification($data) {

	if (!defined('PUSH_NOTIFICATION_FCM_KEY')) return false;

	$CI = & get_instance();
	$CI->load->model('UserPortal_model', 'userPortalModel');

	$fcm_key = PUSH_NOTIFICATION_FCM_KEY;
	if (empty($fcm_key)) return false;

	$fcm_tokens = $CI->userPortalModel->getPushNotificationTokens($data);
	if (empty($fcm_tokens)) return false;

	foreach($fcm_tokens as $token) {

		if (!empty($token['FCM_Token'])) {

			$curlRequestData = array(
				'to'		=> $token['FCM_Token'],
				'notification'	=> array(
					'body' 	=> $data['message'],
					'title'	=> 'К-Врачу',
					'sound' => 'default'
				),
				'data' => array(
					'action' => empty($data['action']) ? "cardfile" : $data['action'],
					'type_id' => empty($data['PushNoticeType_id']) ? 2 : $data['PushNoticeType_id']
					// PushNoticeType_id	PushNoticeType_Name
					// 1	Код брони
					// 2	Напоминание о записи на прием к врачу
					// 3	Новости
					// 4	Вызов в кабинет
				)
			);

			$pushNotificationResult = $CI->userPortalModel->executeCurlRequest(
				(object) array(
					'url' => 'https://fcm.googleapis.com/fcm/send',
					'headers' => array (
						'Authorization: key=' . $fcm_key,
						'Content-Type: application/json'
					),
					'curlRequestData' => $curlRequestData,
				)
			);

			if (empty($data['disable_history'])) {
				$CI->userPortalModel->savePushNotificationHistory(
					array(
						'pmUser_did' => $token['pmUser_did'],
						'message' => $data['message'],
						'PushNoticeType_id' => $data['PushNoticeType_id']
					)
				); // сохраняем пуш в историю
			}
		}
	}

	return true;
}

/**
 *  отправить пуш-уведомление пользователю портала и только пуш
 */
function sendPushNotify($data) {

	if (!defined('PUSH_NOTIFICATION_FCM_KEY')) return false;

	$CI = & get_instance();
	$CI->load->model('UserPortal_model', 'userPortalModel');

	$fcm_key = PUSH_NOTIFICATION_FCM_KEY;

	if (empty($fcm_key)) return false;
	if (empty($data['FCM_Token'])) return false;

	$curlRequestData = array(
		'to'		=> $data['FCM_Token'],
		'notification'	=> array(
			'body' 	=> $data['message'],
			'title'	=> 'К-Врачу',
			'sound' => 'default'
		),
		'data' => array(
			'action' => empty($data['action']) ? "cardfile" : $data['action'],
			'type_id' => empty($data['PushNoticeType_id']) ? 2 : $data['PushNoticeType_id']
			// PushNoticeType_id	PushNoticeType_Name
			// 1	Код брони
			// 2	Напоминание о записи на прием к врачу
			// 3	Новости
			// 4	Вызов в кабинет
		)
	);

	$CI->userPortalModel->executeCurlRequest(
		(object) array(
			'url' => 'https://fcm.googleapis.com/fcm/send',
			'headers' => array (
				'Authorization: key=' . $fcm_key,
				'Content-Type: application/json'
			),
			'curlRequestData' => $curlRequestData,
		)
	);

	return true;
}