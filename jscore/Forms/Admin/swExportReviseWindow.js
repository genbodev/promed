

sw.Promed.swExportReviseWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['vyigruzka_provedennoy_sverki'],
	id: 'ExportReviseWindow',
	height: 115,
	width: 350,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	
	show: function() {
		sw.Promed.swExportReviseWindow.superclass.show.apply(this, arguments);

		this.ReviseList_id = null;

		if ( !arguments[0] || Ext.isEmpty(arguments[0].ReviseList_id) ) {
			sw.swMsg.alert(lang['oshibka'], 'Неверные параметры', function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}

		this.ReviseList_id = arguments[0].ReviseList_id;
	},
	initComponent: function() {
		var that = this;
		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text      : lang['zapusk'],
					tabIndex  : -1,
					tooltip   : lang['zapustit_zagruzku'],
					iconCls   : 'actions16',
					handler   : function() {
						//that.getLoadMask('Пожалуйста, подождите, производится импорт...').show();
						var form = that.findById('ExportReviseForm').getForm();
						
						if ( !Ext.isEmpty(that.ReviseList_id) && !Ext.isEmpty(form.findField('typeFormat').getGroupValue()) ) {
							window.open('/?c=NarcoRevise&m=Export&ReviseList_id=' + that.ReviseList_id + '&typeFormat=' + form.findField('typeFormat').getGroupValue(), '_blank');
							that.hide();
						}
					}
				}, {
					text      : lang['otmena'],
					tabIndex  : -1,
					tooltip   : lang['otmena'],
					iconCls   : 'cancel16',
					handler   : function() {
						this.ownerCt.hide();
					}
				}],
			layout: 'border',
			items: [
				new Ext.form.FormPanel({
					region: 'center',
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'ExportReviseForm',
					labelAlign: 'right',
					//fileUpload: true,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							layout: 'form',
							border: false,
							width: 230,
							labelWidth: 200,
							items: [{
								xtype: 'radio',
								labelSeparator: '',
								fieldLabel: lang['vyiberite_format_vyigruzki_dbf'],
								inputValue: 'DBF',
								name: 'typeFormat'
							}]
						}, {
							layout: 'form',
							border: false,
							width: 50,
							labelWidth: 30,
							items: [{
								xtype: 'radio',
								labelSeparator: '',
								fieldLabel: 'XLS',
								inputValue: 'XLS',
								name: 'typeFormat'
							}]
						}]
					}],
					keys: [{
						alt: true,
						fn: function(inp, e) {
							switch (e.getKey()) {
								case Ext.EventObject.C:
									if (this.action != 'view') {
										this.doSave(false);
									}
									break;
								case Ext.EventObject.J:
									this.hide();
									break;
							}
						},
						key: [ Ext.EventObject.C, Ext.EventObject.J ],
						scope: this,
						stopEvent: true
					}],
					params: {
						
					},
					reader: new Ext.data.JsonReader({
							success: function() {
								//
							}
						},[
							
						]),
					timeout: 60000,
					url: '/?c=NarcoRevise&m=Export'
				})
			]});
		sw.Promed.swExportReviseWindow.superclass.initComponent.apply(this, arguments);
	}
});
