<?php defined('BASEPATH') or die ('No direct script access allowed');

class MedWorker_model extends swModel {

	public $inputRules = array(
		'loadMedWorker' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
		),
		'getMedWorkerById' => array(
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id')
		),
		'createMedWorker' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'CodeDLO','label' => 'код ДЛО','rules' => '','type' => 'string'),
			array('field' => 'HonouredBrevetDate','label' => 'Дата получения почетного звания Заслуженный врач Российской Федерации','rules' => '','type' => 'date'),
			array('field' => 'PeoplesBrevetDate','label' => 'Дата получения почетного звания Народный врач СССР','rules' => '','type' => 'date')
		),
		'createSpecialityDiploma' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'YearOfGraduation', 'label' => 'Год окончания', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DiplomaNumber', 'label' => 'Номер диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DiplomaSeries', 'label' => 'Серия диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DiplomaSpeciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationType_id', 'label' => 'Тип образования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'IsSpecSet', 'label' => 'Признак «Целевой набор»', 'rules' => '', 'type' => 'int'),
			array('field' => 'FRMPTerritories_id', 'label' => 'Субъект РФ', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCountry_id', 'label' => 'Ид страны учебного заведения (из справочника учебных заведений)', 'rules' => '', 'type' => 'id'),
			array('field' => 'YearOfAdmission', 'label' => 'Год поступления', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи диплома', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Qualification_id', 'label' => 'Квалификация сотрудника', 'rules' => '', 'type' => 'id'),
		),
		'updateSpecialityDiploma' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о среднем/профессиональном образовании', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'YearOfGraduation', 'label' => 'Год окончания', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DiplomaNumber', 'label' => 'Номер диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DiplomaSeries', 'label' => 'Серия диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DiplomaSpeciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationType_id', 'label' => 'Тип образования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'IsSpecSet', 'label' => 'Признак «Целевой набор»', 'rules' => '', 'type' => 'int'),
			array('field' => 'FRMPTerritories_id', 'label' => 'Субъект РФ', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCountry_id', 'label' => 'Ид страны учебного заведения (из справочника учебных заведений)', 'rules' => '', 'type' => 'id'),
			array('field' => 'YearOfAdmission', 'label' => 'Год поступления', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи диплома', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Qualification_id', 'label' => 'Квалификация сотрудника', 'rules' => '', 'type' => 'id'),
		),
		'deleteSpecialityDiploma' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о среднем/профессиональном образовании', 'rules' => 'required', 'type' => 'id'),
		),
		'getSpecialityDiploma' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'EducationType_id', 'label' => 'Тип образования', 'rules' => '', 'type' => 'string'),
		),
		'createPostgraduateEducation' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'graduationDate', 'label' => 'Дата получения диплома', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'startDate', 'label' => 'Дата начала обучения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'endDate', 'label' => 'Дата окончания обучения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'DiplomaNumber', 'label' => 'Номер диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DiplomaSeries', 'label' => 'Серия диплома', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PostgraduateEducationType_id', 'label' => 'Тип образования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AcademicMedicalDegree_id', 'label' => 'Ученая степень', 'rules' => '', 'type' => 'id'),
			array('field' => 'Speciality_id', 'label' => 'Специальность при обучении в интернатуре, ординатуре', 'rules' => '', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'IsSpecSet', 'label' => 'Признак «Целевой набор»', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'FRMPTerritories_id', 'label' => 'Субъект РФ', 'rules' => '', 'type' => 'id'),
			array('field' => 'SpecialityAspirant_id', 'label' => 'Специальность при обучении в аспирантуре и докторнатуре', 'rules' => '', 'type' => 'id'),
		),
		'updatePostgraduateEducation' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о послевузовском образовании', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'graduationDate', 'label' => 'Дата получения диплома', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'startDate', 'label' => 'Дата начала обучения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'endDate', 'label' => 'Дата окончания обучения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'DiplomaNumber', 'label' => 'Номер диплома', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DiplomaSeries', 'label' => 'Серия диплома', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PostgraduateEducationType_id', 'label' => 'Тип образования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AcademicMedicalDegree_id', 'label' => 'Ученая степень', 'rules' => '', 'type' => 'id'),
			array('field' => 'Speciality_id', 'label' => 'Специальность при обучении в интернатуре, ординатуре', 'rules' => '', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'IsSpecSet', 'label' => 'Признак «Целевой набор»', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'FRMPTerritories_id', 'label' => 'Субъект РФ', 'rules' => '', 'type' => 'id'),
			array('field' => 'SpecialityAspirant_id', 'label' => 'Специальность при обучении в аспирантуре и докторнатуре', 'rules' => '', 'type' => 'id'),
		),
		'deletePostgraduateEducation' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о послевузовском образовании', 'rules' => 'required', 'type' => 'id'),
		),
		'createRetrainingCourse' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'PassYear', 'label' => 'Год прохождения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'HoursCount', 'label' => 'Количество часов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи диплома', 'rules' => 'required', 'type' => 'date'),
		),
		'updateRetrainingCourse' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о курсе переподготовки', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'PassYear', 'label' => 'Год прохождения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'HoursCount', 'label' => 'Количество часов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи диплома', 'rules' => 'required', 'type' => 'date'),
		),
		'deleteRetrainingCourse' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о курсе переподготовки', 'rules' => 'required', 'type' => 'id'),
		),
		'createQualificationImprovementCourse' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'Year', 'label' => 'Год прохождения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'HoursCount', 'label' => 'Количество часов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Round', 'label' => 'Цикл', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
		),
		'updateQualificationImprovementCourse' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о курсе повышения квалификации', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'Year', 'label' => 'Год прохождения', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentRecieveDate', 'label' => 'Дата выдачи документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'HoursCount', 'label' => 'Количество часов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Round', 'label' => 'Цикл', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
		),
		'deleteQualificationImprovementCourse' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о курсе повышения квалификации', 'rules' => 'required', 'type' => 'id'),
		),
		'createCertificate' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'CertificateReceipDate', 'label' => 'Дата получения сертификата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'CertificateNumber', 'label' => 'Номер сертификата', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'CertificateSeries', 'label' => 'Серия сертификата', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
		),
		'updateCertificate' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о сертификате', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'CertificateReceipDate', 'label' => 'Дата получения сертификата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'CertificateNumber', 'label' => 'Номер сертификата', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'CertificateSeries', 'label' => 'Серия сертификата', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Идентификатор учебного заведения', 'rules' => '', 'type' => 'id'),
		),
		'deleteCertificate' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о сертификате', 'rules' => 'required', 'type' => 'id'),
		),
		'getCertificate' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
		),
		'createQualificationCategory' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'Category_id', 'label' => 'Квалификационная категория', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AssigmentDate', 'label' => 'Дата присвоения', 'rules' => 'required', 'type' => 'date'),
		),
		'updateQualificationCategory' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о квалификационной категории', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'Category_id', 'label' => 'Квалификационная категория', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Speciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'AssigmentDate', 'label' => 'Дата присвоения', 'rules' => 'required', 'type' => 'date'),
		),
		'deleteQualificationCategory' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о квалификационной категории', 'rules' => 'required', 'type' => 'id'),
		),
		'getQualificationCategory' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
		),
		'createReward' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'name', 'label' => 'Название награды', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'number', 'label' => 'Номер', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'FRMPNomination_id', 'label' => 'Номинация', 'rules' => 'required', 'type' => 'id'),
		),
		'updateReward' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о награде', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'date', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'name', 'label' => 'Название награды', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'number', 'label' => 'Номер', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'FRMPNomination_id', 'label' => 'Номинация', 'rules' => 'required', 'type' => 'id'),
		),
		'deleteReward' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи о награде', 'rules' => 'required', 'type' => 'id'),
		),
		'createAccreditation' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'RegNumber', 'label' => 'Регистрационный номер', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AccreditationType_id', 'label' => 'Вид аккредитации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiplomaSpeciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ProfStandard_id', 'label' => 'Профессиональный стандарт', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Учебное заведение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PassDate', 'label' => 'Дата проведения', 'rules' => 'required', 'type' => 'date'),
		),
		'updateAccreditation' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи об аккредитации', 'rules' => 'required', 'type' => 'id', 'notForQuery' => true),
			array('field' => 'DocumentNumber', 'label' => 'Номер документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'DocumentSeries', 'label' => 'Серия документа', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'RegNumber', 'label' => 'Регистрационный номер', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AccreditationType_id', 'label' => 'Вид аккредитации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiplomaSpeciality_id', 'label' => 'Специальность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ProfStandard_id', 'label' => 'Профессиональный стандарт', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EducationInstitution_id', 'label' => 'Учебное заведение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'OtherEducationalInstitution', 'label' => 'Иное учебное заведение', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PassDate', 'label' => 'Дата проведения', 'rules' => 'required', 'type' => 'date'),
		),
		'deleteAccreditation' => array(
			array('field' => 'id', 'label' => 'Идентификатор записи об аккредитации', 'rules' => 'required', 'type' => 'id'),
		),
		'getWorkPlace' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedWorker_id', 'label' => 'Идентификатор сотрудника', 'rules' => '', 'type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
	 * Получение сотрудника
	 */
	public function loadMedWorker($data) {
		$query = "
			select 
				id as MedWorker_id
			from
				persis.MedWorker with(nolock)
			where
				Person_id = :Person_id
		";
		return $this->queryResult($query, $data);
	}

    /**
	 * Получение сотрудника по идентификтору
	 */
	public function getMedWorkerById($data) {
		$query = "
			select
				Person_id,
				CodeDLO,
				convert(varchar(10), HonouredBrevetDate, 120) as HonouredBrevetDate,
				convert(varchar(10), PeoplesBrevetDate, 120) as PeoplesBrevetDate
			from
				persis.MedWorker with(nolock)
			where
				id = :MedWorker_id
		";
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение сотрудника (метод для API)
	 */
	public function getMedWorkerForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				convert(varchar(10), CT.CertificateReceipDate, 120) as CertificateReceipDate,
				CT.CertificateNumber,
				CT.CertificateSeries,
				CT.OtherEducationalInstitution,
				CT.Speciality_id,
				CT.EducationInstitution_id
			FROM persis.Certificate CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных о квалификационных категориях сотрудника (метод для API)
	 */
	public function getQualificationCategoryForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				convert(varchar(10), CT.AssigmentDate, 120) as AssigmentDate,
				CT.Category_id,
				CT.Speciality_id
			FROM persis.QualificationCategory CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных о курсах переподготовки сотрудника (метод для API)
	 */
	public function getRetrainingCourseForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				CT.PassYear,
				CT.HoursCount,
				CT.DocumentNumber,
				CT.DocumentSeries,
				CT.OtherEducationalInstitution,
				CT.EducationInstitution_id,
				convert(varchar(10), CT.DocumentRecieveDate, 120) as DocumentRecieveDate,
				CT.Speciality_id
			FROM persis.RetrainingCourse CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных о среднем или профессиональном образовании сотрудника (метод для API)
	 */
	public function getSpecialityDiplomaForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		if(!empty($data['EducationType_id'])){
			//$data['EducationType_id'] - это строка, Ид указываются через запятую
			$string = trim($data['EducationType_id']);
			$array = explode(',', $string);
			$arrayInt = array_map('intval', array_filter($array, 'is_numeric'));
			
			$where .= ' AND CT.EducationType_id in ('.implode(",", $arrayInt).')';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				CT.YearOfGraduation,
				CT.DiplomaNumber,
				CT.DiplomaSeries,
				CT.OtherEducationalInstitution,
				CT.DiplomaSpeciality_id,
				CT.EducationType_id,
				CT.IsSpecSet,
				CT.FRMPTerritories_id,
				--CT.KLCountry_id,
				CT.YearOfAdmission,
				CT.EducationInstitution_id,
				convert(varchar(10), CT.DocumentRecieveDate, 120) as DocumentRecieveDate,
				CT.Qualification_id,
				EI.KLCountry_id
			FROM persis.SpecialityDiploma CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
				left join persis.EducationInstitution EI (nolock) on EI.id = CT.EducationInstitution_id
			WHERE 1=1
				{$where}
		";
		
		return $this->queryResult($query, $data);
	}

	/**
	 * Получение сотрудника по идентификтору (метод для API)
	 */
	public function getMedWorkerByIdAPI($data) {
		if(empty($data['MedWorker_id'])) return false;

		$query = "
			SELECT top 1
				MD.Person_id,
				DLO.CodeDLO,
				convert(varchar(10), MD.HonouredBrevetDate, 120) as HonouredBrevetDate,
				convert(varchar(10), MD.PeoplesBrevetDate, 120) as PeoplesBrevetDate
			FROM
				persis.MedWorker MD with (nolock)
				outer apply(
					SELECT top 1
						CodeDLO
					FROM persis.KodDLO with (nolock)
					WHERE 
						MedWorker_id = MD.id
				) as DLO
			WHERE 1=1
				and MD.id = :MedWorker_id
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Сохранение сотрудника
	 */
	public function saveMedWorker($data) {
		
		$region = $this->getRegionNumber();

		if ( empty($region) ) {
			return array(array('Error_Msg' => 'Неизвестен регион применения'));
		}

		$codeDLOquery = '';

		if ( !empty($data['CodeDLO']) ) {
			$codeDLOquery = "
				insert into persis.KodDLO with (rowlock) (
					MedWorker_id
					,Lpu_id
					,CodeDLO
					,KodDLO_Order
				) values (
					@MedWorker_id
					,0
					,:CodeDLO
					,0
				)
			";
		}

		$query = "
			declare
				@MedWorker_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				insert into persis.MedWorker with (rowlock) (
					insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					,Person_id
					,CodeDLO
					,HonouredBrevetDate
					,PeoplesBrevetDate
					,current_region
				) values (
					dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					,:Person_id
					,:CodeDLO
					,:HonouredBrevetDate
					,:PeoplesBrevetDate
					,{$region}
				)

				set @MedWorker_id = (select scope_identity())

				{$codeDLOquery}

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				if @@trancount>0
					rollback tran
			end catch

			set nocount off;

			select @MedWorker_id as MedWorker_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->queryResult($query, $data);

		return $resp;
	}

	/**
	 * Сохранение данных о параметре мед. работника
	 */
	public function saveMedWorkerParam($object, $data) {
		if ( empty($data['id']) ) {
			// create
			if ( empty($data['MedWorker_id']) ) {
				$data['MedWorker_id'] = $this->getFirstResultFromQuery("
					select top 1 id as MedWorker_id
					from persis.MedWorker with (nolock)
					where Person_id = :Person_id
				", $data);

				if ( $data['MedWorker_id'] === false || empty($data['MedWorker_id']) ) {
					return array(array('Error_Msg' => 'Не удалось определить идентификатор сотрудника'));
				}
			}

			$fieldsToInsert = array();
			$paramsToInsert = array();

			foreach ( $this->inputRules['update' . $object] as $row ) {
				if ( isset($row['notForQuery']) && $row['notForQuery'] === true ) {
					continue;
				}

				$fieldsToInsert[] = '[' . $row['field'] . ']';
				$paramsToInsert[] = ':' . $row['field'];
			}


			$mainQuery = "
				insert into persis.{$object} with (rowlock) (
					 [insDT]
					,[pmUser_insID]
					,[updDT]
					,[pmUser_updID]
					,[version]
					" . (count($fieldsToInsert) > 0 ? "," . implode(",", $fieldsToInsert) : "") . "
				) values (
					 dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					" . (count($paramsToInsert) > 0 ? "," . implode(",", $paramsToInsert) : "") . "
				)

				set @id = (select scope_identity())
			";
		}
		else {
			// update
			$id = $this->getFirstResultFromQuery("
				select top 1 id
				from persis.{$object} with (nolock)
				where id = :id
			", $data);

			if ( $id === false || empty($id) ) {
				return array(array('Error_Msg' => 'Запись с указанным идентификатором отсутствует в БД'));
			}

			$fieldsToUpdate = array();

			foreach ( $this->inputRules['update' . $object] as $row ) {
				if ( isset($row['notForQuery']) && $row['notForQuery'] === true ) {
					continue;
				}

				$fieldsToUpdate[] = '[' . $row['field'] . '] = :' . $row['field'];
			}

			$mainQuery = "
				set @id = :id;

				update persis.{$object} with (rowlock)
				set
					 updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					" . (count($fieldsToUpdate) > 0 ? "," . implode(",", $fieldsToUpdate) : "") . "
				where id = @id;
			";
		}

		return $this->queryResult("
			declare
				@id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				{$mainQuery}

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()

				if ( @@trancount > 0 )
					rollback tran
			end catch

			set nocount off;

			select @id as {$object}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", $data);
	}

	/**
	 * Удаление объекта из БД
	 */
	public function deletePersisObject($object, $data) {
		$id = $this->getFirstResultFromQuery("
			select top 1 id as {$object}_id
			from persis.{$object} with (nolock)
			where id = :id
		", $data);

		if ( $id === false || empty($id) ) {
			return array(array('Error_Msg' => 'Запись с указанным идентификатором отсутствует в БД'));
		}

		return $this->queryResult("
			declare
				@Error_Code int = null,
				@Error_Message varchar(4000) = null;

			set nocount on;

			begin try
				begin tran

				delete from persis.{$object} with (rowlock) where id = :id

				commit tran
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()

				if ( @@trancount > 0 )
					rollback tran
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", $data);
	}
	
	/**
	 * Получение данных о послевузовском образовании сотрудника (метод для API)
	 */
	public function getPostgraduateEducationForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				convert(varchar(10), CT.graduationDate, 120) as graduationDate,
				convert(varchar(10), CT.startDate, 120) as startDate,
				CT.DiplomaNumber,
				convert(varchar(10), CT.endDate, 120) as endtDate,
				CT.DiplomaSeries,
				CT.PostgraduateEducationType_id,
				CT.OtherEducationalInstitution,
				CT.AcademicMedicalDegree_id,
				CT.Speciality_id,
				CT.EducationInstitution_id,
				CT.IsSpecSet,
				CT.FRMPTerritories_id,
				CT.SpecialityAspirant_id
			FROM persis.PostgraduateEducation CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных об аккредитации сотрудника (метод для API)
	 */
	public function getAccreditationForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				CT.DocumentNumber,
				CT.DocumentSeries,
				CT.RegNumber,
				CT.AccreditationType_id,
				CT.DiplomaSpeciality_id,
				CT.ProfStandard_id,
				CT.EducationInstitution_id,
				CT.OtherEducationalInstitution,
				convert(varchar(10), CT.PassDate, 120) as PassDate
			FROM persis.Accreditation CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных о наградах сотрудника (метод для API)
	 */
	public function getRewardForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				convert(varchar(10), CT.date, 120) as date,
				CT.name,
				CT.number,
				CT.FRMPNomination_id
			FROM persis.Reward CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		
		return $this->queryResult($query, $data);
	}
	
	/**
	 * Получение данных о курсах повышения квалификации (метод для API)
	 */
	public function getQualificationImprovementCourseForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return false;
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}
		
		$query = "
			SELECT
				CT.id,
				MW.id as MedWorker_id,
				MW.Person_id,
				CT.Year,
				convert(varchar(10), CT.DocumentRecieveDate, 120) as DocumentRecieveDate,
				CT.HoursCount,
				CT.DocumentNumber,
				CT.DocumentSeries,
				CT.OtherEducationalInstitution,
				CT.Round,
				CT.Speciality_id,
				CT.EducationInstitution_id
			FROM persis.QualificationImprovementCourse CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
			WHERE 1=1
				{$where}
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Получение данных о месте работы сотрудника (метод для API)
	 */
	public function getWorkPlaceForAPI($data) {
		if(empty($data['MedWorker_id']) && empty($data['Person_id'])) return array(array('Error_Msg' => 'Ни один из параметров не задан'));
		$where = '';
		if(!empty($data['MedWorker_id'])){
			$where .= ' AND MW.id = :MedWorker_id';
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND MW.Person_id = :Person_id';
		}

		$query = "
			SELECT
				MW.id as MedWorker_id,
				MW.Person_id,
				WP.Lpu_id,
				WP.LpuSection_id,
				CT.PostOccupationType_id,
				WP.Post_id,
				CT.Rate,
				convert(varchar(10), CT.beginDate, 120) as beginDate,
				convert(varchar(10), CT.endDate, 120) as endDate,
				CT.LeaveRecordType_id,
				CT.LeaveRecordDelType_id,
				LU.LpuUnit_FRMOid as LpuUnit_FRMO,
				LS.LpuSection_FRMOSectionId as LpuSection_FRMO,
				CT.DismissalReasonType_id as DismissalReason_id
			FROM persis.WorkPlace CT (nolock)
				left join persis.MedWorker MW (nolock) on MW.id = CT.MedWorker_id
				left join v_MedStaffFact WP (nolock) on CT.id = WP.MedStaffFact_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = WP.LpuUnit_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = WP.LpuSection_id
			WHERE 1=1
				{$where}
		";

		return $this->queryResult($query, $data);
	}
}