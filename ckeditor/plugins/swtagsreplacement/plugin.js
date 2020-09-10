/*
 * Плагин отвечает за работу с тегами для подстановки данных.
 * Группы тегов замены:
 * 1) Теги для подстановки данных события или данных связанных с событием
 * 2) Теги для подстановки данных шаблона
 * todo Сделать замену тегов подстановки на данные в предварительном просмотре Preview - модифицировать плагин 'preview'
 */
 
/*
 * Объект тег для подстановки данных
 */
CKEDITOR.tools.extend( CKEDITOR,{
	swtagreplacement: function(display,tag)
	{
		this.display	= display;
		this.tag		= tag;
		this.getTag		= function(){
			return this.tag;
		};
		this.getDisplay	= function(){
			return this.display + ' ' + this.tag;
		};
	}
});

CKEDITOR.config.swtagsreplacement = {
	template: [
		new CKEDITOR.swtagreplacement('Заголовок шаблона','{XmlTemplate_Caption}')
	],
	events: {
		EvnVizitPL: [
			new CKEDITOR.swtagreplacement('Название класса события','{EvnClass_Name}'),
			new CKEDITOR.swtagreplacement('Наименование ЛПУ','{Lpu_Name}'),
			new CKEDITOR.swtagreplacement('Адрес ЛПУ','{Lpu_Address}'),
			new CKEDITOR.swtagreplacement('Код отделения','{LpuSection_Name}'),
			new CKEDITOR.swtagreplacement('Название отделения','{LpuSection_Name}'),
			new CKEDITOR.swtagreplacement('Фамилия и инициалы врача','{MedPersonal_Fin}'),
			new CKEDITOR.swtagreplacement('Код врача','{MedPersonal_TabCode}'),
			new CKEDITOR.swtagreplacement('Дата посещения','{EvnVizitPL_setDate}'),
			new CKEDITOR.swtagreplacement('Время посещения','{EvnVizitPL_setTime}'),
			new CKEDITOR.swtagreplacement('Кратность приема','{VizitClass_Name}'),
			new CKEDITOR.swtagreplacement('Цель приема','{VizitType_Name}'),
			new CKEDITOR.swtagreplacement('Код места посещения','{ServiceType_Code}'),
			new CKEDITOR.swtagreplacement('Место посещения','{ServiceType_Name}'),
			new CKEDITOR.swtagreplacement('Название вида оплаты','{PayType_Name}'),
			new CKEDITOR.swtagreplacement('ФИО пациента','{Person_FIO}'),
			new CKEDITOR.swtagreplacement('Дата рождения пациента','{Person_BirthDay}'),
			new CKEDITOR.swtagreplacement('Возраст пациента','{Person_Age}'),
			new CKEDITOR.swtagreplacement('Пол пациента','{Person_Sex_Name}'),
			new CKEDITOR.swtagreplacement('Кто направил','{PrehospDirect_Name}'),
			new CKEDITOR.swtagreplacement('Номер кабинета','{Cabinet_Num}'),
			new CKEDITOR.swtagreplacement('Основной клинический диагноз','{Diag_Text}'),
			new CKEDITOR.swtagreplacement('Основной диагноз по МКБ','{Diag_Name}'),
			new CKEDITOR.swtagreplacement('Код основного диагноза по МКБ','{Diag_Code}')
		]
	}
};
/*
 * Метод получения массива с объектами тегов для подстановки данных события или данных связанных с событием
 */
CKEDITOR.editor.prototype.getSwTagsEvent = function(event)
{
	return CKEDITOR.config.swtagsreplacement.events[event];
};
/*
 * Метод получения массива с объектами тегов для подстановки данных шаблона
 */
CKEDITOR.editor.prototype.getSwTagsTemplate = function()
{
	return CKEDITOR.config.swtagsreplacement.template;
};

CKEDITOR.plugins.add( 'swtagsreplacement',
{
	init : function( editor )
	{
		editor.addCommand('swtagreplacement_add', new CKEDITOR.dialogCommand('swtagreplacement_add'));
		editor.ui.addButton('swtagsreplacement_add', {
			label : 'Вставить тег для подстановки данных'
			,command : 'swtagreplacement_add'
			,icon: '/img/icons/template-data-tag.png'
		});
		CKEDITOR.dialog.add('swtagreplacement_add',this.path+'dialogs/tagsreplacement.js');
		/*
		 При исполнении команды 'preview' происходит отображение исходного кода, который получается методом GetData()
		 Необходимо чтобы метод GetData() отдал отпарсенный исходный код, но сам исходный код остался без изменений
		editor.on('beforeGetData',function (e){
			console.log(e.data); //undefined
		});
		 Другой вариант: модифицировать плагин 'preview' так и сделаю
		*/
	}
	,requires : ['swtags']
});