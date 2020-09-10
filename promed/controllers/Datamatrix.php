<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Datamatrix - контроллер для получение штрих-кода формата DataMatrix
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.10.2013
 */

class Datamatrix extends swController{
	var $NeedCheckLogin = false;
	public $inputRules = array();
	/**
	 * construct + inputrules
	 */
	function __construct(){
		parent::__construct();

		$this->load->database();
		$this->load->model('Datamatrix_model','dbmodel');
		$this->inputRules = array(
			'GetDatamatrix' => array(
				array(
					'field' => 'EvnStick_id',
					'label' => 'Идентификатор рецепта',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * @return bool
	 * Получение данных, формирование строки и получение ссылки на печать матричного кода
	 */
	function GetDatamatrix(){
		$data = $this->ProcessInputData('GetDatamatrix',false);
		if($data===false){
			return false;
		}
		$this->load->database();
		$this->load->model('Datamatrix_model','dbmodel');
		$this->load->helper('Options');
		$this->load->helper('Barcode');
		$response = $this->dbmodel->GetDatamatrixFields($data);
		array_walk_recursive($response, 'ConvertFromUTF8ToWin1251', true);
		$DatamatrixFields = array();
		$DatamatrixFields[0] = '!!8!!'; //Префикс
		$DatamatrixFields[1] = '05'; //Версия структуры DataMatrix
		$DatamatrixFields[2] = substr($response['LN_EMPLOYER'],0,80); //Страхователь: Наименование
		$DatamatrixFields[3] = $response['LN_EMPL_FLAG']; //Страхователь: признак места работы
		$DatamatrixFields[4] = $response['LN_CODE']; //Листок нетрудоспособности: Номер ЛВН
		$DatamatrixFields[5] = $response['PREV_LN_CODE']; //Листок нетрудоспособности: Номер предыдущего ЛВН
		$DatamatrixFields[6] = $response['PRIMARY_FLAG']; //Листок нетрудоспособности: Первичный или продолжение
		$DatamatrixFields[7] = $response['DUPLICATE_FLAG']; //Листок нетрудоспособности: Дубликат или оригинал
		$DatamatrixFields[8] = $response['LN_DATE']; //Листок нетрудоспособности: Дата выдачи
		$DatamatrixFields[9] = substr($response['LPU_NAME'],0,90); //Листок нетрудоспособности: Наименование ЛПУ
		$DatamatrixFields[10] = substr($response['LPU_ADDRESS'],0,100); //Листок нетрудоспособности: Адрес ЛПУ
		$DatamatrixFields[11] = $response['LPU_OGRN']; //Листок нетрудоспособности: ОГРН ЛПУ
		$DatamatrixFields[12] = $response['LN_LAST_NAME']; //Застрахованное лицо: Фамилия
		$DatamatrixFields[13] = $response['LN_FIRST_NAME']; //Застрахованное лицо: Имя
		$DatamatrixFields[14] = $response['LN_PATRONYMIC']; //Застрахованное лицо: Отчество
		$DatamatrixFields[15] = $response['BIRTHDAY']; //Застрахованное лицо: Дата рождения
		$DatamatrixFields[16] = $response['GENDER']; //Застрахованное лицо: Пол
		$DatamatrixFields[17] = $response['REASON1']; //Листок нетрудоспособности: Причина нетрудоспособности
		$DatamatrixFields[18] = $response['REASON2']; //Листок нетрудоспособности: дополнительный код нетрудоспособности
		$DatamatrixFields[19] = $response['REASON3']; //Листок нетрудоспособности: код изм. нетрудоспособности
		$DatamatrixFields[20] = $response['PARENT_CODE']; //Листок нетрудоспособности: Номер ЛН, предъявляемого на основном месте работы
		$DatamatrixFields[21] = $response['DATE1']; //Листок нетрудоспособности: Дата изменения причины нетрудоспособности, предполагаемая дата родов, дата начала путевки
		$DatamatrixFields[22] = $response['DATE2']; //Листок нетрудоспособности: Дата окончания путевки
		$DatamatrixFields[23] = $response['VOUCHER_NO']; //Листок нетрудоспособности: Номер путевки
		$DatamatrixFields[24] = $response['VOUCHER_OGRN']; //Листок нетрудоспособности: ОРГН сатанория или клиники НИИ
		$DatamatrixFields[25] = ($response['SERV1_AGE']==0)?'':$response['SERV1_AGE']; //Листок нетрудоспособности: По уходу за первым родственником: Возраст (лет)
		$DatamatrixFields[26] = ($response['SERV1_MM']==0)?'':$response['SERV1_MM']; //Листок нетрудоспособности: По уходу за первым родственником: Возраст (мес)
		$DatamatrixFields[27] = $response['SERV1_RELATION_CODE']; //Листок нетрудоспособности: По уходу за первым родственником: Родственная связь
		$DatamatrixFields[28] = substr($response['SERV1_FIO'],0,90); //Листок нетрудоспособности: По уходу за первым родственником: ФИО родственника
		$DatamatrixFields[29] = ($response['SERV2_AGE']==0)?'':$response['SERV2_AGE']; //Листок нетрудоспособности: По уходу за вторым родственником: Возраст (лет)
		$DatamatrixFields[30] = ($response['SERV1_MM']==0)?'':$response['SERV1_MM']; //Листок нетрудоспособности: По уходу за вторым родственником: Возраст (мес)
		$DatamatrixFields[31] = $response['SERV2_RELATION_CODE']; //Листок нетрудоспособности: По уходу за вторым родственником: Родственная связь
		$DatamatrixFields[32] = substr($response['SERV2_FIO'],0,90); //Листок нетрудоспособности: По уходу за вторым родственником: ФИО родственника
		$DatamatrixFields[33] = $response['PREGN12W_FLAG']; //Листок нетрудоспособности: Постановка на учет в ранние сроки беременности (до 12 недель)
		$DatamatrixFields[34] = $response['BOZ_FLAG']; //Состоит на учете в органах занятости населения
		$DatamatrixFields[35] = $response['HOSPITAL_DT1']; //Листок нетрудоспособности:Стационар: Находился в стационаре с (дата)
		$DatamatrixFields[36] = $response['HOSPITAL_DT2']; //Листок нетрудоспособности:Стационар: Находиляс в стационаре по (дата)
		$DatamatrixFields[37] = $response['HOSPITAL_BREACH_CODE']; //Листок нетрудоспособности: Код нарушения режима
		$DatamatrixFields[38] = $response['HOSPITAL_BREACH_DT']; //Листок нетрудоспособности: Дата нарушения режима
		$DatamatrixFields[39] = $response['MSE_DT1']; // Листок нетрудоспособности: Инвалидность: Дата направления в бюро МСЭ
		$DatamatrixFields[40] = $response['MSE_DT2']; // Листок нетрудоспособности: Инвалидность: Дата регистрации документов в бюро МСЭ
		$DatamatrixFields[41] = $response['MSE_DT3']; // Листок нетрудоспособности: Инвалидность: Дата освидетельствования в бюро МСЭ
		$DatamatrixFields[42] = $response['MSE_INVALID_GROUP']; //Листок нетрудоспособности: Установлена/изменена группа инвалидность (1-первая, 2-вторая, 3-третья)
		$DatamatrixFields[43] = $response['MSE_RESULT']; //Листок нетрудоспособности: Установлен/изменен статус нетрудоспособного.
		$DatamatrixFields[44] = $response['TREAT1_DT1']; // Листок нетрудоспособности: Освобождение от работы: Дата начала освобождения
		$DatamatrixFields[45] = $response['TREAT1_DT2']; // Листок нетрудоспособности: Освобождение от работы: Дата окончания освобождения
		$DatamatrixFields[46] = $response['TREAT1_DOCTOR_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача
		$DatamatrixFields[47] = substr($response['TREAT1_DOCTOR'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача
		$DatamatrixFields[48] = $response['TREAT1_DOC_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача
		$DatamatrixFields[49] = $response['TREAT1_DOCTOR2_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача-председателя ВК
		$DatamatrixFields[50] = substr($response['TREAT1_CHAIRMAN_VK'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача-председателя ВК
		$DatamatrixFields[51] = $response['TREAT1_DOC2_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача-председателя ВК

		$DatamatrixFields[52] = $response['TREAT2_DT1']; // Листок нетрудоспособности: Освобождение от работы: Дата начала освобождения
		$DatamatrixFields[53] = $response['TREAT2_DT2']; // Листок нетрудоспособности: Освобождение от работы: Дата окончания освобождения
		$DatamatrixFields[54] = $response['TREAT2_DOCTOR_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача
		$DatamatrixFields[55] = substr($response['TREAT2_DOCTOR'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача
		$DatamatrixFields[56] = $response['TREAT2_DOC_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача
		$DatamatrixFields[57] = $response['TREAT2_DOCTOR2_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача-председателя ВК
		$DatamatrixFields[58] = substr($response['TREAT2_CHAIRMAN_VK'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача-председателя ВК
		$DatamatrixFields[59] = $response['TREAT2_DOC2_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача-председателя ВК

		$DatamatrixFields[60] = $response['TREAT3_DT1']; // Листок нетрудоспособности: Освобождение от работы: Дата начала освобождения
		$DatamatrixFields[61] = $response['TREAT3_DT2']; // Листок нетрудоспособности: Освобождение от работы: Дата окончания освобождения
		$DatamatrixFields[62] = $response['TREAT3_DOCTOR_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача
		$DatamatrixFields[63] = substr($response['TREAT3_DOCTOR'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача
		$DatamatrixFields[64] = $response['TREAT3_DOC_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача
		$DatamatrixFields[65] = $response['TREAT3_DOCTOR2_ROLE']; //Листок нетрудоспособности: Освобождение от работы: Должность врача-председателя ВК
		$DatamatrixFields[66] = substr($response['TREAT3_CHAIRMAN_VK'],0,90); //Листок нетрудоспособности: Освобождение от работы: ФИО врача-председателя ВК
		$DatamatrixFields[67] = $response['TREAT3_DOC2_ID']; //Листок нетрудоспособности: Освобождение от работы: Идентификационный номер врача-председателя ВК
		$DatamatrixFields[68] = $response['OTHER_STATE_DT']; //Листок нетрудоспособности: Дата изменения состояния нетрудоспособного
		$DatamatrixFields[69] = $response['RETURN_DATE_LPU']; // Листок нетрудоспособности: Дата начала работы
		$DatamatrixFields[70] = $response['NEXT_LN_CODE']; // Листок нетрудоспособности: Номер следующего ЛН

		$result_string = $DatamatrixFields[0];
		for ($i=1;$i<=70; $i++){
			$result_string .='!'.$DatamatrixFields[$i];
		}
		//print getPromedUrl().'/datamatrix.php?s='. $result_string;
		$this->dbmodel->getDatamatrixImage($result_string);
		return true;
	}
}