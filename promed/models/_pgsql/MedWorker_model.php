<?php defined('BASEPATH') or die ('No direct script access allowed');

class MedWorker_model extends swPgModel {

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
				id as \"MedWorker_id\"
			from
				persis.MedWorker
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
				Person_id as \"Person_id\",
				CodeDLO as \"CodeDLO\",
				to_char(HonouredBrevetDate, 'YYYY-MM-DD') as \"HonouredBrevetDate\",
				to_char(PeoplesBrevetDate, 'YYYY-MM-DD') as \"PeoplesBrevetDate\"
			from
				persis.MedWorker
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				to_char(CT.CertificateReceipDate, 'YYYY-MM-DD') as \"CertificateReceipDate\",
				CT.CertificateNumber as \"CertificateNumber\",
				CT.CertificateSeries as \"CertificateSeries\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				CT.Speciality_id as \"Speciality_id\",
				CT.EducationInstitution_id as \"EducationInstitution_id\"
			FROM persis.Certificate CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
//				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				to_char(CT.AssigmentDate, 'YYYY-MM-DD') as \"AssigmentDate\",
				CT.Category_id as \"Category_id\",
				CT.Speciality_id as \"Speciality_id\"
			FROM persis.QualificationCategory CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				CT.PassYear as \"PassYear\",
				CT.HoursCount as \"HoursCount\",
				CT.DocumentNumber as \"DocumentNumber\",
				CT.DocumentSeries as \"DocumentSeries\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				CT.EducationInstitution_id as \"EducationInstitution_id\",
				to_char(CT.DocumentRecieveDate, 'YYYY-MM-DD') as \"DocumentRecieveDate\",
				CT.Speciality_id as \"Speciality_id\"
			FROM persis.RetrainingCourse CT 
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				CT.YearOfGraduation as \"YearOfGraduation\",
				CT.DiplomaNumber as \"DiplomaNumber\",
				CT.DiplomaSeries as \"DiplomaSeries\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				CT.DiplomaSpeciality_id as \"DiplomaSpeciality_id\",
				CT.EducationType_id as \"EducationType_id\",
				CT.IsSpecSet as \"IsSpecSet\",
				CT.FRMPTerritories_id as \"FRMPTerritories_id\",
				--CT.KLCountry_id as \"KLCountry_id\",
				CT.YearOfAdmission as \"YearOfAdmission\",
				CT.EducationInstitution_id as \"EducationInstitution_id\",
				to_char(CT.DocumentRecieveDate, 'YYYY-MM-DD') as \"DocumentRecieveDate\",
				CT.Qualification_id as \"Qualification_id\",
				EI.KLCountry_id as \"KLCountry_id\"
			FROM persis.SpecialityDiploma CT 
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
				left join persis.EducationInstitution EI on EI.id = CT.EducationInstitution_id
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
			SELECT
				MD.Person_id as \"Person_id\",
				DLO.CodeDLO as \"CodeDLO\",
				to_char(MD.HonouredBrevetDate, 'YYYY-MM-DD') as \"HonouredBrevetDate\",
				to_char(MD.PeoplesBrevetDate, 'YYYY-MM-DD') as \"PeoplesBrevetDate\"
			FROM
				persis.MedWorker MD 
				LEFT JOIN LATERAL (
					SELECT
						CodeDLO
					FROM persis.KodDLO 
					WHERE 
						MedWorker_id = MD.id
					limit 1
				) as DLO ON true
			WHERE 1=1
				and MD.id = :MedWorker_id
			LIMIT 1
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
		
		try {
			
			$this->beginTransaction();
			$resp = $this->queryResult("
				insert into persis.MedWorker (
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
				returning id as \"MedWorker_id\"
				", $data);
			
			
			if (empty($resp[0]['MedWorker_id'])) {
				throw new Exception('Ошибка сохранения сотрудника');
			}
					
			$med_worker_id = $resp[0]['MedWorker_id'];
			
			if ( !empty($data['CodeDLO']) ) {
				$resp = $this->queryResult("
					insert into persis.KodDLO (
						MedWorker_id
						,Lpu_id
						,CodeDLO
						,KodDLO_Order
					) values (
						{$med_worker_id}
						,0
						,:CodeDLO
						,0
					)", $data);
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
		
		return array(array('MedWorker_id' => $med_worker_id , 'Error_Code' => 0, 'Error_Msg' => ''));
	}

	/**
	 * Сохранение данных о параметре мед. работника
	 */
	public function saveMedWorkerParam($object, $data) {
		if ( empty($data['id']) ) {
			// create
			if ( empty($data['MedWorker_id']) ) {

				$data['MedWorker_id'] = $this->getFirstResultFromQuery("
					select id as MedWorker_id
					from persis.MedWorker
					where Person_id = :Person_id
					limit 1
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

				$fieldsToInsert[] = $row['field'];
				$paramsToInsert[] = ':' . $row['field'];
			}


			$mainQuery = "
				insert into persis.{$object} (
					 insDT
					,pmUser_insID
					,updDT
					,pmUser_updID
					,version
					" . (count($fieldsToInsert) > 0 ? "," . implode(",", $fieldsToInsert) : "") . "
				) values (
					 dbo.tzGetDate()
					,:pmUser_id
					,dbo.tzGetDate()
					,:pmUser_id
					,1
					" . (count($paramsToInsert) > 0 ? "," . implode(",", $paramsToInsert) : "") . "
				)

				returning id as \"{$object}_id\"
			";
		}
		else {
			// update
			$id = $this->getFirstResultFromQuery("
				select id
				from persis.{$object} 
				where id = :id
				limit 1
			", $data);

			if ( $id === false || empty($id) ) {
				return array(array('Error_Msg' => 'Запись с указанным идентификатором отсутствует в БД'));
			}

			$fieldsToUpdate = array();

			foreach ( $this->inputRules['update' . $object] as $row ) {
				if ( isset($row['notForQuery']) && $row['notForQuery'] === true ) {
					continue;
				}

				$fieldsToUpdate[] = $row['field'] . ' = :' . $row['field'];
			}

			$mainQuery = "
				update persis.{$object}
				set
					 updDT = dbo.tzGetDate()
					,pmUser_updID = :pmUser_id
					" . (count($fieldsToUpdate) > 0 ? "," . implode(",", $fieldsToUpdate) : "") . "
				where id = :id
				returning id as \"{$object}_id\";
			";
		}

		$resp = $this->queryResult($mainQuery, $data);
				
		if (empty($resp[0][$object . '_id'])) {
			throw new Exception('Ошибка сохранения данных о параметре мед. работника');
		}
		
		return array(array($object . '_id' => $resp[0][$object . '_id'] , 'Error_Code' => 0, 'Error_Msg' => ''));
	}

	/**
	 * Удаление объекта из БД
	 */
	public function deletePersisObject($object, $data) {
		$id = $this->getFirstResultFromQuery("
			select id as \"{$object}_id\"
			from persis.{$object}
			where id = :id
			limit 1
		", $data);

		if ( $id === false || empty($id) ) {
			return array(array('Error_Msg' => 'Запись с указанным идентификатором отсутствует в БД'));
		}

		return $this->queryResult("
			delete from persis.{$object} where id = :id
			returning 0 as \"Error_Code\", '' as \"Error_Msg\"
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				to_char(CT.graduationDate, 'YYYY-MM-DD') as \"graduationDate\",
				to_char(CT.startDate, 'YYYY-MM-DD') as \"startDate\",
				CT.DiplomaNumber as \"DiplomaNumber\",
				to_char(CT.endDate, 'YYYY-MM-DD') as \"endtDate\",
				CT.DiplomaSeries as \"DiplomaSeries\",
				CT.PostgraduateEducationType_id as \"PostgraduateEducationType_id\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				CT.AcademicMedicalDegree_id as \"AcademicMedicalDegree_id\",
				CT.Speciality_id as \"Speciality_id\",
				CT.EducationInstitution_id as \"EducationInstitution_id\",
				CT.IsSpecSet as \"IsSpecSet\",
				CT.FRMPTerritories_id as \"FRMPTerritories_id\",
				CT.SpecialityAspirant_id as \"SpecialityAspirant_id\"
			FROM persis.PostgraduateEducation CT 
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				CT.DocumentNumber as \"DocumentNumber\",
				CT.DocumentSeries as \"DocumentSeries\",
				CT.RegNumber as \"RegNumber\",
				CT.AccreditationType_id as \"AccreditationType_id\",
				CT.DiplomaSpeciality_id as \"DiplomaSpeciality_id\",
				CT.ProfStandard_id as \"ProfStandard_id\",
				CT.EducationInstitution_id as \"EducationInstitution_id\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				to_char(CT.PassDate, 'YYYY-MM-DD') as \"PassDate\"
			FROM persis.Accreditation CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				to_char(CT.date, 'YYYY-MM-DD') as \"date\",
				CT.name as \"name\",
				CT.number as \"number\",
				CT.FRMPNomination_id as \"FRMPNomination_id\"
			FROM persis.Reward CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				CT.id as \"id\",
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				CT.Year as \"Year\",
				to_char(CT.DocumentRecieveDate, 'YYYY-MM-DD') as \"DocumentRecieveDate\",				
				CT.HoursCount as \"HoursCount\",
				CT.DocumentNumber as \"DocumentNumber\",
				CT.DocumentSeries as \"DocumentSeries\",
				CT.OtherEducationalInstitution as \"OtherEducationalInstitution\",
				CT.Round as \"Round\",
				CT.Speciality_id as \"Speciality_id\",
				CT.EducationInstitution_id as \"EducationInstitution_id\"
			FROM persis.QualificationImprovementCourse CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
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
				MW.id as \"MedWorker_id\",
				MW.Person_id as \"Person_id\",
				WP.Lpu_id as \"Lpu_id\",
				WP.LpuSection_id as \"LpuSection_id\",
				CT.PostOccupationType_id as \"PostOccupationType_id\",
				WP.Post_id as \"Post_id\",
				CT.Rate as \"Rate\",
				to_char(CT.beginDate, 'YYYY-MM-DD') as \"beginDate\",
				to_char(CT.endDate, 'YYYY-MM-DD') as \"endDate\",
				CT.LeaveRecordType_id as \"LeaveRecordType_id\",
				CT.LeaveRecordDelType_id as \"LeaveRecordDelType_id\",
				LU.LpuUnit_FRMOid as \"LpuUnit_FRMO\",
				LS.LpuSection_FRMOSectionId as \"LpuSection_FRMO\",
				CT.DismissalReasonType_id as \"DismissalReason_id\"
			FROM persis.WorkPlace CT
				left join persis.MedWorker MW on MW.id = CT.MedWorker_id
				left join v_MedStaffFact WP on CT.id = WP.MedStaffFact_id
				left join v_LpuUnit LU on LU.LpuUnit_id = WP.LpuUnit_id
				left join v_LpuSection LS on LS.LpuSection_id = WP.LpuSection_id
			WHERE 1=1
				{$where}
		";

		return $this->queryResult($query, $data);
	}
}