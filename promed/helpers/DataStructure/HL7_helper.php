<?php
/**
 * HL7_helper - хэлпер с описанием структур данных формата HL7
 *
 *
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Petukhov Ivan (ethereallich@gmail.com)
 * @version      21.11.2012
 */


/**
 * Класс, описывающий любой сегмент сообщения HL7
 */
class HL7_Segment {
	/**
	 * Название сегмента
	 */
	var $name;
	
	/**
	 * Поля сегмента
	 */
	var $fields;
	
	/**
	 * Создание нового объекта сегмента из строки
	 */
	function __construct($str) {
		$this->fields = explode('|', $str);
		$this->name = $this->fields[0];
	}

	/**
	 * Возвращает сегмент, как строку
	 */
	function getAsString() {
		return implode('|', $this->fields);
	}
	
	/**
	 * Возвращает поле по индексу
	 */
	function getField($index) 
	{
		return $this->fields[$index];
	}
	
	/**
	 * Возвращает часть поля по индексу поля и индексу части
	 */
	function getPart($index, $part_index) 
	{
		$data = $this->fields[$index];
		$data_arr = explode('^', $data);
		if (count($data_arr) > $part_index)
			return $data_arr[$part_index];
		else
			return null;
	}

	/**
	 * Возвращает часть сотавного поля по индексу поля, индексу части и индексу состава
	 */
	function getPart2($index, $part_index, $part_index2)
	{
		$data = $this->fields[$index];
		$data_arr = explode('~', $data);
		if (count($data_arr) > $part_index) {
			$data = $data_arr[$part_index];
			$data_arr = explode('^', $data);
			if (count($data_arr) > $part_index2) {
				return $data_arr[$part_index2];
			}
		}

		return null;
	}

	/**
	 * Возвращает часть сотавного поля по индексу поля и названию
	 */
	function getPartTxt($index, $part_name, $obr = false)
	{
		$data = $this->fields[$index];
		$data_arr = explode('~', $data);
		foreach($data_arr as $one) {
			$one_part = explode('^', $one);
			if (!$obr && count($one_part) > 1 && $one_part[0] == $part_name) {
				return $one_part[1];
			}
			if ($obr && count($one_part) > 1 && $one_part[1] == $part_name) {
				return $one_part[0];
			}
		}

		return null;
	}
	
}

/**
 * Класс сообщение HL7
 */
class HL7_Message {
	
	var $segments = array();
	
	
	/**
	 * Генерирует объект сообщения по переданному тексту
	 */
	function __construct($str) {
		$seg_strings = explode("\r\n", $str);
		foreach ($seg_strings as $segment_string) {
			$this->segments[] = new HL7_Segment($segment_string);
		}
	}
	
	/**
	 * Возвращает заданный сегмент
	 * если сегмент не найден возвращает false
	 */
	function getSegment($name) {
		foreach($this->segments as $segment) {
			if ($segment->name == $name)
				return $segment;
		}

		throw new Exception("В сообещнии формата HL7 найден сегмент ".$name);
	}
	
	
	/**
	 * Возвращает тип cобытия, вызвавшего сообщение
	 */
	function getEventType() {
		//Получаем заголовок у сообщения
		$head = $this->getSegment('MSH');
		if ($head) {
			return $head->getPart(8, 1);
		} else {
			//Что-то пошло не так, заголовка нет, выдаем ошибку
			DieWithSoapFault(0, 'Сообщение в формате HL7 не содержит сегмента заголовка');
		}
	}

	/**
	 * Получение отправителя
	 */
	function getSendingApplication() {
		$MSH = $this->getSegment('MSH');
		return $MSH->getField(2);
	}

	/**
	 * Получение отправителя
	 */
	function getSendingFacility() {
		$MSH = $this->getSegment('MSH');
		return $MSH->getField(3);
	}

	/**
	 * Получение получателя
	 */
	function getReceivingApplication() {
		$MSH = $this->getSegment('MSH');
		return $MSH->getField(4);
	}

	/**
	 * Получение получателя
	 */
	function getReceivingFacility() {
		$MSH = $this->getSegment('MSH');
		return $MSH->getField(5);
	}
}

/**
 * Сообщение о записи человека
 */
class HL7_RecordMessage extends HL7_Message {
	/**
	 * Получение идентификатора врача
	 */
	function getMedStaffFactId() {
		$ARQ = $this->getSegment('ARQ');
		$MedStaffFact_id = $ARQ->getField(5);
		if (!empty($MedStaffFact_id)) {
			return $MedStaffFact_id;
		}

		throw new Exception("В сегменте ARQ не указан идентификатор врача");
	}

	/**
	 * Получение фамилии человека
	 */
	function getPersonSurName() {
		$PID = $this->getSegment('PID');
		return $PID->getPart(5, 0);
	}

	/**
	 * Получение имени человека
	 */
	function getPersonFirName() {
		$PID = $this->getSegment('PID');
		return $PID->getPart(5, 1);
	}

	/**
	 * Получение отчества человека
	 */
	function getPersonSecName() {
		$PID = $this->getSegment('PID');
		return $PID->getPart(5, 2);
	}

	/**
	 * Получение даты рождения человека
	 */
	function getPersonBirthDay() {
		$PID = $this->getSegment('PID');
		if (DateTime::createFromFormat('Ymd', $PID->getField(7))) {
			return DateTime::createFromFormat('Ymd', $PID->getField(7))->format('Y-m-d');
		}
		return null;
	}

	/**
	 * Получение пола человека
	 */
	function getPersonSex() {
		$PID = $this->getSegment('PID');
		$SexField = $PID->getField(8);
		switch($SexField) {
			case 'М':
			case 'M':
				return 1;
				break;
			case 'Ж':
			case 'F':
				return 2;
				break;
		}
		return null;
	}

	/**
	 * Получение телефона человека
	 */
	function getPersonPhone() {
		$PID = $this->getSegment('PID');
		return $PID->getField(13);
	}

	/**
	 * Получение адреса человека
	 */
	function getPersonAddress() {
		$PID = $this->getSegment('PID');
		$Address_Address = $PID->getField(11);
		$Address_Address = str_replace('^', ', ', $Address_Address);
		return $Address_Address;
	}

	/**
	 * Получение СНИЛС человека
	 */
	function getPersonSnils() {
		$PID = $this->getSegment('PID');
		return $PID->getPart2(3, 0, 0);
	}

	/**
	 * Получение серии паспорта человека
	 */
	function getPersonDocumentSer() {
		$PID = $this->getSegment('PID');
		$passport = $PID->getPartTxt(3, 'ПАСПОРТ', true);
		if (!empty($passport)) {
			return mb_substr($passport, 0, 4);
		}

		return null;
	}

	/**
	 * Получение номера паспорта человека
	 */
	function getPersonDocumentNum() {
		$PID = $this->getSegment('PID');
		$passport = $PID->getPartTxt(3, 'ПАСПОРТ', true);
		if (!empty($passport)) {
			return mb_substr($passport, 4);
		}

		return null;
	}

	/**
	 * Получение идентификатора очереди, в которую записываемся
	 */
	function getResourceId() {
		$ARQ = $this->getSegment('ARQ');
		return $ARQ->getField(5);
	}

	/**
	 * Получение идентификатора сервиса
	 */
	function getServiceId() {
		$APR = $this->getSegment('APR');
		return $APR->getPart(2, 0);
	}

	/**
	 * Получение идентификатора услуги
	 */
	function getUslugaId() {
		$APR = $this->getSegment('APR');
		return $APR->getPart2(2, 0, 1);
	}

	/**
	 * Получение даты/времени начала
	 */
	function getHomeVisitSetDT() {
		$ARQ = $this->getSegment('ARQ');
		if (DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 0))) {
			return DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 0))->format('Y-m-d H:i:s');
		} else if (DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 0))) {
			return DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 0))->format('Y-m-d H:i:s');
		}
		return null;
	}

	/**
	 * Получение даты/времени начала
	 */
	function getTimeTableGrafBeg() {
		$ARQ = $this->getSegment('ARQ');
		if (DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 0))) {
			return DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 0))->format('Y-m-d');
		} else if (DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 0))) {
			return DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 0))->format('Y-m-d');
		}
		return null;
	}

	/**
	 * Получение даты/времени окончания
	 */
	function getTimeTableGrafEnd() {
		$ARQ = $this->getSegment('ARQ');
		if (DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 1))) {
			return DateTime::createFromFormat('YmdHis', $ARQ->getPart(11, 1))->format('Y-m-d');
		} else if (DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 1))) {
			return DateTime::createFromFormat('YmdHi', $ARQ->getPart(11, 1))->format('Y-m-d');
		}
		return null;
	}

	/**
	 * Получение желаемого времени приёма
	 */
	function getPrefStart() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'PREFSTART');
	}

	/**
	 * Получение желаемого времени приёма
	 */
	function getPrefEnd() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'PREFEND');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefMon() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'MON');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefTue() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'TUE');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefWed() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'WED');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefThu() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'THU');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefFri() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'FRI');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefSat() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'SAT');
	}

	/**
	 * Получение желаемой даты
	 */
	function getPrefSun() {
		$APR = $this->getSegment('APR');
		return $APR->getPartTxt(1, 'SUN');
	}
	
	/**
	 * Получение идентификатора направления
	 */
	function getSlotId() {
		$ARQ = $this->getSegment('ARQ');
		return $ARQ->getField(1);
	}
	
	/**
	 * Получение идентификатора направления
	 */
	function getRecordType() {
		$ARQ = $this->getSegment('ARQ');
		return $ARQ->getField(8);
	}
	
	/**
	 * Получение данных по человеку
	 */
	function getPerson() {
		$PID = $this->getSegment('PID');
		return $PID->getPart(5, 0) . ' ' . $PID->getPart(5, 1) . ' ' . $PID->getPart(5, 2);
	}
}

/**
 * Сообщение об отмене человека
 */
class HL7_CancelMessage extends HL7_Message {
	/**
	 * Получение идентификатора освобождаемого слота в МИС
	 */
	function getMisSlotId() {
		$ARQ = $this->getSegment('ARQ');
		return $ARQ->getField(1);
	}

	/**
	 * Получение идентификатора освобождаемого слота
	 */
	function getSlotId() {
		$ARQ = $this->getSegment('ARQ');
		return $ARQ->getField(2);
	}

	/**
	 * Получение идентификатора пациента
	 */
	function getPersonId() {
		$ARQ = $this->getSegment('ARQ');
		$Person_id = $ARQ->getPart(2, 0);
		if (!empty($Person_id)) {
			return $Person_id;
		}

		throw new Exception("В сегменте ARQ не указан идентификатор пациента");
	}

	/**
	 * Получение идентификатора бирки
	 */
	function getTimeTableGrafId() {
		$ARQ = $this->getSegment('ARQ');
		$TimetableGraf_id = $ARQ->getPart(2, 1);
		if (!empty($TimetableGraf_id)) {
			return $TimetableGraf_id;
		}

		throw new Exception("В сегменте ARQ не указан идентификатор бирки");
	}
}

/**
 * Сообщение о запросе расписания
 */
class HL7_GetTimetableMessage extends HL7_Message {
	/**
	 * Получение идентификатора врача
	 */
	function getMedStaffFactId() {
		$QRD = $this->getSegment('QRD');
		$MedStaffFact_id = $QRD->getField(8);
		if (!empty($MedStaffFact_id)) {
			return $MedStaffFact_id;
		}

		throw new Exception("В сегменте QRD не указан идентификатор врача");
	}

	/**
	 * Получение даты/времени начала
	 */
	function getTimeTableGrafBeg() {
		$QRF = $this->getSegment('QRF');
		if (DateTime::createFromFormat('YmdHis', $QRF->getPart(9, 0))) {
			return DateTime::createFromFormat('YmdHis', $QRF->getPart(9, 0))->format('Y-m-d H:i:s');
		} else if (DateTime::createFromFormat('YmdHi', $QRF->getPart(9, 0))) {
			return DateTime::createFromFormat('YmdHi', $QRF->getPart(9, 0))->format('Y-m-d H:i:s');
		}
		return null;
	}

	/**
	 * Получение даты/времени окончания
	 */
	function getTimeTableGrafEnd() {
		$QRF = $this->getSegment('QRF');
		if (DateTime::createFromFormat('YmdHis', $QRF->getPart(9, 1))) {
			return DateTime::createFromFormat('YmdHis', $QRF->getPart(9, 1))->format('Y-m-d H:i:s');
		} else if (DateTime::createFromFormat('YmdHi', $QRF->getPart(9, 1))) {
			return DateTime::createFromFormat('YmdHi', $QRF->getPart(9, 1))->format('Y-m-d H:i:s');
		}
		return null;
	}

	/**
	 * Получение идентификатора запроса
	 */
	function getQueryIdent() {
		$QRD = $this->getSegment('QRD');
		return $QRD->getField(4);
	}
}

/**
 * Сообщение о запросе списка врачей
 */
class HL7_GetMedStaffMessage extends HL7_Message {
	/**
	 * Получение идентификатора врача
	 */
	function getLpuId() {
		$QRD = $this->getSegment('QRD');
		$Lpu_id = $QRD->getField(8);
		if (!empty($Lpu_id)) {
			return $Lpu_id;
		}

		throw new Exception("В сегменте QRD не указан идентификатор МО");
	}
}
