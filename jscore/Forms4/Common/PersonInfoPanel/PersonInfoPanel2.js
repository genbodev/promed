/**
 * Элемент панели информации пациента. Изначально делался для EvnUslugaCommonEditWindow, пока нигде больше не используется
 * Пока непонятно, нужны ли будут панели на формах. Если нужны - этот вариант написан проще чем common.EMK.PersonInfoPanel, удобен для повторного использования
 * Использует элемент viewModel и bind'ы
 *
 */

// темплейт взял из панели common.EMK.PersonInfoPanel
var PersonInfoPanelTemplate = new Ext6.XTemplate(
	'<tpl for=".">',
	'<div class="PersonFrame">',
	'<tpl if="this.allowPersonEncrypHIV() == true">',
	'<div><i>Шифр:</i> {PersonEncrypHIV_Encryp}</div>',
	'</tpl>',
	'<tpl if="this.allowPersonEncrypHIV() == false">',
	'<div data-qtip="{Person_Surname} {Person_Firname} {Person_Secname}"><i>ФИО:</i> {Person_Surname} {Person_Firname} {Person_Secname}</div>',
	'<div data-qtip="{[Ext6.util.Format.date(values.Person_Birthday, \"d.m.Y\")]}"><i>Д/р:</i> {[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</div>',
	'<div data-qtip="{Sex_Name}"><i>Пол:</i> {Sex_Name}</div>',
	'{[(String(values.Person_deadDT) != "null" ? "<div>Дата смерти: <span color=red>" + String(Ext6.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</span></div>" : "")]}',
	'{[(String(values.Person_closeDT) != "null" ? "<div>Дата закрытия: <span color=red>" + String(Ext6.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</span></div>" : "")]}',
	'<div data-qtip="{SocStatus_Name}"><i>Соц. статус:</i> {SocStatus_Name}</div>',
	'<div data-qtip="{[snilsRenderer(values.Person_Snils)]}"><i>СНИЛС:</i> {[snilsRenderer(values.Person_Snils)]}</div>',
	'<div data-qtip="{Person_RAddress}"><i>Регистрация:</i> {Person_RAddress}</div>',
	'<div data-qtip="{Person_PAddress}"><i>Проживает:</i> {Person_PAddress}</div>',
	'<div data-qtip="{Person_Phone}"><i>Телефон:</i> {Person_Phone}</div>',
	"<div data-qtip='{[this.getPolisStr(values)]}'>",
	'<i>Полис:</i> {[this.getPolisStr(values)]}',
	"</div>",
	"<div data-qtip='{[this.getDocumentStr(values)]}'>",
	'<i>Документ:</i> {[this.getDocumentStr(values)]}',
	"</div>",
	'<div data-qtip="{Person_Job}"><i>Работа:</i> {Person_Job}</div>',
	'<div data-qtip="{Person_Post}"><i>Должность:</i> {Person_Post}</div>',
	'<div data-qtip="{Lpu_Nick}"><i>МО:</i> {Lpu_Nick}</div>',
	'<div><i>Участок:</i> {LpuRegion_Name} ({[Ext6.util.Format.date(values.PersonCard_begDate, "d.m.Y")]})</div>',
	'<div data-qtip="{NewslatterAccept}"><i>Согласие на получение уведомлений:</i> {NewslatterAccept}</div>',
	'</tpl>',
	'</div>',
	'</tpl>',
	{
		allowPersonEncrypHIV: function () {
			return false; //(!Ext6.isEmpty(form.getFieldValue('PersonEncrypHIV_Encryp')));
		},
		getPolisStr: function (values) {
			var StrPolis = values.Polis_Ser+' '+values.Polis_Num;
			if(values.Polis_begDate || values.OrgSmo_Name)
				StrPolis += ' Выдан: '+Ext6.util.Format.date(values.Polis_begDate, "d.m.Y")+' '+values.OrgSmo_Name+'.';
			if(values.Polis_endDate)
				StrPolis += ' Закрыт:'+Ext6.util.Format.date(values.Polis_endDate, "d.m.Y");
			return StrPolis;
			//return StrPolis.replace(new RegExp('"','g'),"'");
		},
		getDocumentStr: function (values) {
			var StrDocument = values.Document_Ser+' '+values.Document_Num;
			if(values.Document_begDate || values.OrgDep_Name)
				StrDocument += ' Выдан: '+Ext6.util.Format.date(values.Document_begDate, "d.m.Y")+' '+values.OrgDep_Name+'.';
			return StrDocument;
		}
	}
);


Ext6.define('PersonInfoModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.PersonInfoModel',
	idProperty: 'Person_id',
	fields: [
		{name: 'Person_id'},
		{name: 'Server_pid'},
		{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'Document_Num'},
		{name: 'Document_Ser'},
		{name: 'KLAreaType_id'},
		{name: 'Lpu_Nick'},
		{name: 'Lpu_id'},
		{name: 'LpuRegion_Name'},
		{name: 'OrgDep_Name'},
		{name: 'OrgSmo_Name'},
		{name: 'Person_Age'},
		{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'BirthdayInFormat', calculate: function (data) {
				return data.Person_Birthday ? Ext6.util.Format.date(data.Person_Birthday, 'd.m.Y') : '';
			} },
		{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'PersonEvn_id'},
		{name: 'Server_id'},
		{name: 'Person_Firname'},
		{name: 'Person_Job'},
		{name: 'Person_PAddress'},
		{name: 'PAddress_Address'},
		{name: 'Person_Phone'},
		{name: 'JobOrg_id'},
		{name: 'Person_Post'},
		{name: 'Person_RAddress'},
		{name: 'RAddress_Address'},
		{name: 'Person_Secname'},
		{name: 'Person_Snils'},
		{name: 'Person_Surname'},
		{name: 'Person_EdNum'},
		{name: 'SurNameLetter'},
		{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'Polis_Num'},
		{name: 'Polis_Ser'},
		{name: 'OmsSprTerr_id'},
		{name: 'OmsSprTerr_Code'},
		{name: 'Sex_Code'},
		{name: 'Sex_id'},
		{name: 'SocStatus_id'},
		{name: 'Sex_Name'},
		{name: 'SocStatus_Name'},
		{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
		{name: 'PersonCloseCause_id'},
		{name: 'Person_IsDead'},
		{name: 'Person_IsBDZ'},
		{name: 'PersonEncrypHIV_Encryp'},
		{name: 'Person_IsUnknown', type: 'int'},
		{name: 'Person_IsAnonym', type: 'int'},
		{name: 'personAgeText'}
	]
});


Ext6.define('PersonInfoStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.PersonInfoStore',
	model: 'PersonInfoModel',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		extraParams: {
			mode: 'PersonInfoPanel'
		},
		url: '/?c=Common&m=loadPersonData',
		reader: {
			type: 'json',
			rootProperty: 'data'
		}
	}
});


// непосредственно сама информация, будет лежать внутри панели
Ext6.define('PersonInfoPanelContents', {
	extend: 'Ext6.DataView',
	alias: 'widget.PersonInfoPanelContents',
	itemSelector: 'div.PersonFrame',
	margin: '10px 0px 30px 0px',
	store: Ext6.create('PersonInfoStore'),
	tpl: PersonInfoPanelTemplate,
	listeners: {
		render: function ()
		{
			var panel = this,
				wnd = panel.up('window'),
				vm = wnd ? wnd.getViewModel() : null;

			this.getStore().on('load', function (store, records, successful, operation, eOpts) {

				if (successful && vm)
				{
					//console.log(vm)
					//console.log(records)
					//vm.set('personPanel', records[0]);
				}

				return true;
			});
		}
	}
});

Ext6.define('common.PersonInfoPanel.PersonInfoPanel2', {
	extend: 'Ext6.Panel',
	alias: 'widget.PersonInfoPanel2',
	header: {
		cls: 'arrow-expander-panel person-info-panel',
		titlePosition: 2 // помещаем иконку открытия перед иконкой пола и заголовком
	},
	bind: { // title панели привязан к значенияем из записи человека, обновляется со сменой записи автоматически

		title: '{personPanel.Person_Surname} {personPanel.Person_Firname} {personPanel.Person_Secname} <span class="birth-age">{personPanel.BirthdayInFormat} ({personPanel.personAgeText})</span>'
	},
	tools: [
		{
			bind: { // класс иконки человека тоже привязан к записи человека

				iconCls: '{personPanel.Sex_id == 1 ? "man-icon" : "woman-icon"}'
			}
		}
	],

	items: [{xtype: 'PersonInfoPanelContents'}]
});