/**
* Форма загрузки файла для функционала: Импорт ошибок ФЛК по ЗЛ, Импорт ответа от СМО по ЗЛ, Импорт территориальных прикреплений/ откреплений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

//swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation

sw.Promed.swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	modal: true,
	height: 100,
	width: 400,
	shim: false,
	plain: true,
	buttonAlign: 'right',
	closeAction: 'hide',
	currentForm: '',
	id: 'swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation',
	
	show: function()
	{
		if( Ext.isEmpty(arguments[0]['Form']) ) return false;

		sw.Promed.swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation.superclass.show.apply(this, arguments);		
		this.center();

		this.currentForm = arguments[0]['Form'];
		this.showLink(false);

		switch(this.currentForm){
			case 'ImportErrors_FLKforZL':
				this.title = 'Импорт ошибок ФЛК по ЗЛ';
				break;
			case 'ImportResponseFrom_SMOforPL':
				this.title = 'Импорт ответа от СМО по ЗЛ';
				break;
			case 'ImportOfTerritorialAttachmentsDetachments':
				this.title = 'Импорт территориальных прикреплений/ откреплений';
				break;
			default:
				return false;
				break;
		}

		this.setTitle(this.title)
	},
	
	listeners: {
		hide: function() {
			this.showLink(false);
			this.FormPanel.getForm().findField('file').reset();
			this.doLayout();
		}
	},
	
	createReport: function() {
		var form = this.FormPanel.getForm();
		if( form.findField('file').getValue() == '' ) return false;

		var params = {};
		params.actions = this.currentForm;
		
		form.submit({
			params: params,
			failure: function(f, r) {
				var msg = 'Не удалось выполнить операции при формировании данных.<br>Обратитесь к разработчику';
				if(r && r.response && r.response.responseText){
					var obj = Ext.util.JSON.decode(r.response.responseText);
					if(obj.Error_Msg) msg = obj.Error_Msg;
				}
				sw.swMsg.alert(this.title + ': <span style="color:red">Ошибка</sapn>', msg);
				this.hide();
			}.createDelegate(this),
			success: function(f, r) {
				var obj = Ext.util.JSON.decode(r.response.responseText);
				if( obj.success ) {
					// var msg = 'Операции по формированию данных успешно выполнены !!!';
					// if(obj.action && obj.action == 'ImportErrors_FLKforZL') {
					// 	sw.swMsg.alert(this.title, msg);
					// }
					
					var lf = this.FormPanel.find('name', 'link')[0],
						ff = this.FormPanel.find('name', 'filerow')[0];
					
					lf.tpl = new Ext.Template(this.linkTpl);
					lf.tpl.overwrite(lf.body, obj);
					this.showLink(true);
					
				} else if(obj.Error_Msg) {
					sw.swMsg.alert(this.title + ': <span style="color:red">Ошибка</sapn>', obj.Error_Msg);
				} else {
					sw.swMsg.alert(this.title + ': <span style="color:red">Ошибка</sapn>', 'Не удалось выполнить операции при формировании данных.<br>Обратитесь к разработчику');
				}
				//this.hide();
			}.createDelegate(this)
		});
	},
	
	showLink: function(m) {
		var form = this.FormPanel,
			lf = form.find('name', 'link')[0],
			ff = form.find('name', 'filerow')[0];
		lf.setVisible(m);
		ff.setVisible(!m);
		this.buttons[0].setDisabled(m);
		this.doLayout();
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.linkTpl = '<a href="{Link}" target="_blank" style="font-size: 10pt">скачать отчет</a>';
		
		this.FormPanel = new Ext.FormPanel({
			layout: 'form',
			frame: true,
			url: '/?c=AttachmentCheck&m=uploadFileImport',
			fileUpload: true,
			labelWidth: 35,
			labelAlign: 'right',
			items: [
				{
					layout: 'form',
					name: 'filerow',
					items: [
						{
							xtype: 'fileuploadfield',
							anchor: '100%',
							name: 'file',
							buttonText: langs('Выбрать'),
							fieldLabel: langs('Файл'),
							listeners: {
								fileselected: function(f, v) {
									if( f.hidden ) return false;
									var accessTypes = ['dbf'];
									if( !(new RegExp(accessTypes.join('|'), 'ig')).test(v) ) {
										var errmsg = langs('Данный тип файла не поддерживается!<br />Поддерживаемые типы: ');
										for(var i=0; i<accessTypes.length; i++) {
											errmsg += '*' + accessTypes[i] + (i+1 < accessTypes.length ? ', ' : '' );
										}
										sw.swMsg.alert(langs('Ошибка'), errmsg, f.reset.createDelegate(f));
										return false;
									}
								}
							}
						}
					]
				}, {
					name: 'link',
					hidden: true
				}
			]
		});
		
		
		Ext.apply(this,	{
			items: [this.FormPanel],
			buttons: [
				{
					text: langs('Загрузить данные'),
					tooltip: langs('Загрузить данные'),
					iconCls: 'refresh16',
					handler: this.createReport.createDelegate(this)
				},
				'-',
				HelpButton(this),
				{
					text: langs('Закрыть'),
					tabIndex: -1,
					tooltip: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swUploadFileWindowFor_ImportErrors_ResponseFromSMO_ChangesLocation.superclass.initComponent.apply(this, arguments);
	}
});