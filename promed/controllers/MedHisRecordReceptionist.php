<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedHisRecordReceptionist - контроллер для записей врачей приемного отделения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       SWAN developers
* @version      04.02.2011
*/

class MedHisRecordReceptionist extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadMedHisRecordReceptionistViewForm' => array(
				array(
					'field' => 'MedHisRecordReceptionist_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['MedHisRecordReceptionist_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadMedHisRecordReceptionistViewForm() {
		$data = $this->ProcessInputData('loadMedHisRecordReceptionistViewForm', true, true);
		if ( $data === false )
		{
			 return false;
		}
		$val = '';
		switch ($data['MedHisRecordReceptionist_id'])
		{
			case 80:
				$val = <<<EOD
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>Запись врача при первичном осмотре</title></head>
<body><p align="right">
	Дата:&nbsp; 19 Январь 2011 г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Время: 19:20</p>
<p align="right">
	Пациент: Плесовских Василий Кузьмич</p>
<p align="center">
	&nbsp;</p>
<p align="center">
	<strong>Запись врача при первоначальном осмотре</strong></p>
<p>
	&nbsp;</p>
<p>
	<strong>Жалобы: </strong>На постоянные боли в левой голени и стопе, с нарушением сна. Онемение, зябкость.</p>
<p>
	<strong>Анамнез заболевания</strong>. Симптомы &quot;перемежающейся хромоты&quot; появились около 6 месяцев назад.. Постепенно сокращалась дистанция безболевой ходьбы. Со слов больного, 7 дней назад появились боли в левой голени и стопе, за медицинской помощью не обращался. Доставлен ГСМП в ГКБ №4, консультирован ангиохирургом, госпитализирован для обследования и оперативного лечения в ОССХ.</p>
<p>
	<strong>Анамнез жизни. </strong>Рос, развивался удовлетворительно. Перенесенные заболевания - простудные. Туберкулез, вирусный гепатит, венерические заболевания отрицает. Травм не было. Операция- аппендэктомия в 1992г.&nbsp; Инфарктов не было. Инсульт в 2010г.&nbsp; Сопутствующие заболевания: поздний восстановительный период ишемического инсульта в бассейне левой СМАот 2010г. правосторонний центральный гемипарез. ДЭП 2 ст.&nbsp; Аллергологический и трансфузионный анамнезы не отягощены.</p>
<p>
	<strong>Экспертный анамнез. </strong>Пенсионер. Инвалид 2 гр.</p>
<p>
	<strong>Объективный статус:</strong></p>
<p>
	Состояние удовлетворительное. Кожа, слизистые физиологической окраски. Периферические лимфоузлы не увеличены. Сердце: тоны ритмичные,&nbsp; шумов нет. ЧСС 72 уд/мин. АД = 100/60 мм рт.ст. Дыхание везикулярное, проводится во все отделы, хрипов нет. Язык влажный, чистый. Живот мягкий, безболезненный при пальпации, перистальтика выслушивается. Печень - по краю реберной дуги, край острый. Селезенка не пальпируется. Симптом Пастернацкого отрицательный с обеих сторон. Стул, диурез в норме.</p>
<p>
	<strong>Локальный статус: </strong>Пульсация на обеих сонных артериях удовлетворительная. Справа выслушивается систолический шум. Справа пульсация на ОБА определяется, ниже пульсации нет. Выслушивается выраженный систолический шум на подвздошных артериях. Слева пульсации на ОБА нет. Стопа умеренно отечна, гиперимирована, прохладная, пальцы цианотичны. Движения и чувствительность сохранены. Трофических расстройств нет.&nbsp;</p>
<table border="1" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					<strong>Наличие пульсации</strong></p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					<strong>Наличие шума</strong></p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					справа</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					слева</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					справа</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					слева</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Брахиоцеф.ствол</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					+</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Сонная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Височная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Позвоночная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подключ.артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подмыш.артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Плечевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Лучевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Локтевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					АД на руках</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					100/60 мм рт.ст.</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					100/60 мм рт.ст.</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Брюшная аорта</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					+</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подвздошные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Бедренные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подколенные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					ЗББА</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					ПББА</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					АД на ногах</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<p>
	&nbsp;</p>
<p>
	<strong>Диагноз</strong>: Атеросклероз. Сочетанное поражение. Окклюзия ВСА слева, критический стенозы ВСА справа. ПОНМК в ЛСМА (май, 2010 г.). ХНМК IVст.. Синдром Лериша. Стенозы подвздошных артерий справа, окклюзии слева. Окклюзии ПБА с обеих сторон, ГБА справа. ХАН IVст. слева. Пролапс митрального клапана.</p>
<p>
	&nbsp;</p>
<p>
	Консультирован зав. ОССХ Опариным А.Ю., тактика согласована.</p>
<p>
	&nbsp;</p>
<p align="right">
	Врач: Курников Д.В. _______________</p>
</body></html>
EOD;
			break;
			case 81: // Тотьмянин
				$val = <<<EOD
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>Запись врача при первичном осмотре</title></head>
<body><p align="right">
	Дата:&nbsp; 26 Январь 2011 г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Время: 12:00</p>
<p align="right">
	Пациент: Тотьмянин Николай Григорьевич</p>
<p align="center">
	&nbsp;</p>
<p align="center">
	<strong>ЗАПИСЬ ВРАЧА ПРИ ПЕРВОНАЧАЛЬНОМ ОСМОТРЕ</strong></p>
<p>
	&nbsp;</p>
<p>
	<strong>Жалобы: </strong>&nbsp;На постоянные боли в покое в левой голени и стопе, &quot;онемение&quot;, похолодание голени и стопы, парестезии, нарушение чувствительности, снижение активных движений в стопе, сильные боли при ходьбе. Икроножная мышца болезнена при пальпации. Субфасциального отека нет.</p>
<p>
	<strong>Анамнез заболевания</strong>. Заболел 13 января 2011г.: внезапно появились сильные боли в левой голени, парестезии, &quot;онемение&quot; голени и стопы. В анамнезе ИБС. ПИКС(2006г.). 24.01.11г. Вызвал бригаду ГСМП, был доставлен в приемное отделение ГКБ № 4.&nbsp; Осмотрен ангиохирургом ГКБ №4, госпитализирован в ЭХО для лечения, рекомендовано выполнение ангиографии. К 26.01.11г. ухудшение состояния, появление симптомов острой ишемии. Пациент для дальнейшего лечения переведен в ОССХ.</p>
<p>
	<strong>Анамнез жизни. </strong>Рос, развивался удовлетворительно. Перенесенные заболевания - простудные. Туберкулез, вирусный гепатит, венерические заболевания отрицает. Травм не было. Аппендэктомия, грыжесечение более 40 лет назад. Инсультов не было. ПИКС 2006г. Сопутствующие заболевания: ИБС. Стенокардия IIфк. Гипертоническая болезнь IIIст, риск 4. ХСН IIА / IIфк.. Аллергологический анамнез спокойный. Гемотрансфузий не было.</p>
<p>
	<strong>Экспертный анамнез. </strong></p>
<p>
	<strong>Объективный статус:</strong></p>
<p>
	Состояние удовлетворительное. Периферические лимфоузлы не увеличены. Сердце: тоны ритмичные, приглушены, шумов нет. ЧСС прим. 90 уд/мин. АД = 150/100 мм рт.ст. Дыхание везикулярное, проводится во все отделы, хрипов нет. Язык влажный, чистый. Живот мягкий, безболезненный при пальпации, перистальтика выслушивается. Печень по краю реберной дуги, край острый. Селезенка не пальпируется. Симптом Пастернацкого отрицательный с обеих сторон. Стул, диурез в норме.</p>
<p>
	<strong>Локальный статус: </strong>Стопа и голень слева прохладные. Стопа слева цианотической окраски. Вены спавшиеся. Чувствительность конечности и движения снижены. Пассивные движения стопы и пальцев сохранены. Мышцы голени болезнены при пальпации. Субфасциального отека нет.</p>
<table border="1" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					<strong>Наличие пульсации</strong></p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					<strong>Наличие шума</strong></p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					справа</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					слева</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					справа</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					слева</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Брахиоцеф.ствол</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					+</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Сонная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Височная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Позвоночная артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подключ.артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подмыш.артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Плечевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Лучевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Локтевая артерия</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					АД на руках</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					150/100 мм рт.ст.</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					150/100 мм рт.ст.</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Брюшная аорта</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					+</p>
			</td>
			<td colspan="2" style="width: 227px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подвздошные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Бедренные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					+/-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					Подколенные артерии</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					ЗББА</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					ПББА</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
		</tr>
		<tr>
			<td style="width: 144px;">
				<p align="center">
					АД на ногах</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					-</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
			<td style="width: 113px;">
				<p align="center">
					&nbsp;</p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<p>
	<strong>Диагноз</strong>: Атеросклероз. Окклюзия ПБА с обеих сторон.. Тромбоз подвздошной артерии слева. ОИ IIА ст. слева. ИБС. ПИКС. (2006г.). Стенокардия IIфк. Гипертоническая болезнь IIIст, риск 4. ХСН IIА / IIфк. Тромбоз ОБА справа(01.02.2011г). ОИ 1 ст.</p>
<p>
	&nbsp;</p>
<p>
	Консультирован зав. ОССХ Опариным А.Ю., тактика согласована.</p>
<p>
	&nbsp;</p>
<p align="right">
	Врач: Ташкинов А.Л. _______________</p>
</body></html>
EOD;
			break;
		}
		$val = toUTF($val);
		echo json_encode(array("success"=>true, "html" => $val));
		return true;
	}

}
