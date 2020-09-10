/**
* swFarmContractImportWindow- импорт спика контрактов из XML упакованных в ZIP
*/

sw.Promed.swFarmContractImportWindow = Ext.extend(sw.Promed.BaseForm, {
	alwaysOnTop: true,
	id: 'swFarmContractImportWindow',
	objectName: 'swFarmContractImportWindow',
	layout: 'form',
	buttonAlign: 'center',
	title : 'Импорт контрактов',
	modal : true,
	width : 340,
	fieldWidth:40,
	autoHeight : true,
	closable : true,
	resizable: false,
	bodyStyle:'padding:10px',
	closeAction : 'hide',
	draggable : true,
	callback: Ext.emptyFn,
	initComponent: function()
	{
		var wnd = this;	
		Ext.apply(this,
		{
			autoHeight: true,
			buttonAlign: 'right',
			buttons: [
				{
					text: 'Импортировать',
					handler: function(){
						var importZipForm = Ext.getCmp('FC_importZipForm');
						if (importZipForm.getForm().isValid()) {
							var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите..."});
								loadMask.show();

							importZipForm.getForm().submit({
								url: '/?c=Farmacy&m=Contract_importXml',
								success: function(form, data) {
										loadMask.hide();
										importZipForm.getForm().reset();
										wnd.callback(wnd.owner, null, null);
										wnd.hide();
										var cnt = data.result.Cnt; 
										Ext.Msg.alert('Импорт контрактов', 'Операция успешно завершена!<br><br>Загружено записей - ' + cnt + ' шт.' );
								},
								failure: function(form, answer) {
									loadMask.hide();
									var response = Ext.util.JSON.decode(answer.response.responseText);
									Ext.Msg.alert('Импорт контрактов', response.Error_Msg);
								}
							});
						}
						else {
							Ext.Msg.alert('Ошибка ввода', 'Выберите файл контрактов для импорта');
						}
					}
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					text: 'Отмена',
					handler: function(){
						Ext.getCmp('FC_importZipForm').getForm().reset();
						wnd.refresh();
					}
				}
			],
			items : [
				new Ext.FormPanel({
					bodyStyle:'padding:5px 5px 0',
					layout: 'form',
					id:'FC_importZipForm',
					frame: true,
					fileUpload: true,
					items: [
						{
							xtype: 'textfield',
							inputType: 'file',
							autoCreate: { tag: 'input', name: 'Contract_import', type: 'text', size: '20', autocomplete: 'off' },
							name: 'Contract_import',
							regex: /^.*\.(zip)$/,
							hideLabel:true,
							regexText:'Вводимый файл должен быть архивом zip',
							width:280,
						}
					]
				})
			],
		});
		sw.Promed.swFarmContractImportWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swFarmContractImportWindow.superclass.show.apply(this, arguments);
			this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
	}

});