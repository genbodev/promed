/**
* swIPRARegistryImportWindow- импорт выписок ИПРА из XML упакованных в ZIP
*/

sw.Promed.swIPRARegistryImportWindow = Ext.extend(sw.Promed.BaseForm, {
	alwaysOnTop: true,
	id: 'swIPRARegistryImportWindow',
	objectName: 'swIPRARegistryImportWindow',
	objectSrc : '/jscore/Forms/Admin/swIPRARegistryImportWindow.js',
	layout: 'form',
	buttonAlign: 'center',
	title : 'Загрузить архив с файлами ИПРА',
	modal : true,
	width : 340,
	fieldWidth:40,
	autoHeight : true,
	closable : true,
	resizable: false,
	bodyStyle:'padding:10px',
	closeAction : 'hide',
	draggable : true,
	initComponent: function()
	{
		var form = this;
		Ext.apply(this,
		{
			autoHeight: true,
			buttonAlign: 'right',
			buttons: [
				{
					text: 'Импортировать',
					handler: function(){
						if (Ext.getCmp('importZipForm').getForm().isValid()) {

							var loadMask = new Ext.LoadMask(form.getEl(), {msg: "Подождите..."});
							loadMask.show();


							Ext.getCmp('importZipForm').getForm().submit({
								url: '/?c=IPRARegister&m=IPRARegistry_importNewFormat',
								success: function(form, answer) {
										loadMask.hide();
										Ext.getCmp('swIPRARegistryViewWindow').IPRAdata_decode = Ext.util.JSON.decode(answer.response.responseText).data;

										var params = {
											IPRAdata_decode: Ext.getCmp('swIPRARegistryViewWindow').IPRAdata_decode
										}

										getWnd('swIPRARegistryGridWindow').show(params);

										Ext.getCmp('importZipForm').getForm().reset();
										Ext.getCmp('swIPRARegistryImportWindow').refresh();
								},
								failure: function(form, answer) {
									loadMask.hide();
									var response = Ext.util.JSON.decode(answer.response.responseText);
									Ext.Msg.alert('Импорт выписок ИПРА пациентов', response.Error_Msg);
								}
							});
						}
						else {
							Ext.Msg.alert('Ошибка ввода', 'Выберите архив ИПРА для импорта');
						}
					}
				},
				{
					text: 'Отмена',
					handler: function(){
						Ext.getCmp('importZipForm').getForm().reset();
						Ext.getCmp('swIPRARegistryImportWindow').refresh();
					}
				}
			],
			items : [
				new Ext.FormPanel({
					bodyStyle:'padding:5px 5px 0',
					layout: 'form',
					id:'importZipForm',
					frame: true,
					fileUpload: true,
					items: [
						{
							xtype: 'textfield',
							inputType: 'file',
							autoCreate: { tag: 'input', name: 'IPRARegistry_import', type: 'text', size: '20', autocomplete: 'off' },
							name: 'IPRARegistry_import',
							regex: /^.*\.(zip)$/,
							hideLabel:true,
							regexText:'Вводимый файл должен быть архивом zip',
							width:210,
						}
					]
				})
			],
		});
		sw.Promed.swIPRARegistryImportWindow.superclass.initComponent.apply(this, arguments);
	},
	refresh : function(){
		var objectClass = this.objectClass;
		var lastArguments = this.lastArguments;
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;

		if (sw.Promed.Actions.loadLastObjectCode){
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
		}

		this.hide();
		this.destroy();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

		if (sw.ReopenWindowOnRefresh) {
			getWnd(objectClass).show(lastArguments);
		}
	},
	close : function(){

		this.hide();
		this.destroy();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

	},
	show: function(params)
	{
		sw.Promed.swIPRARegistryImportWindow.superclass.show.apply(this, arguments);
	}

});