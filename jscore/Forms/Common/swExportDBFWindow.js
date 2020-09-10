

sw.Promed.swExportDBFWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['vyigruzka_zp_v_dbf'],
	id: 'DbfImportWindow',
	height: 115,
	width: 400,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	
	show: function(callback) {
		this.callback = callback;
		sw.Promed.swExportDBFWindow.superclass.show.apply(this, arguments);
		
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
					
						that.findById('DbfImportForm').getForm().submit({
							timeout: 0,
							success: function (callback,ss,ff){
								//that.getLoadMask('').hide();
								
								that.callback(arguments);
									var sd =callback;
									var ssd="";
									for(var key in sd){
									    var val = sd[key];
									    ssd+=key+": "+val+"\n";
									    
									}
									//alert(ssd);
									that.hide();
								if (that.callback) {
									
								}
							},
							failure: function (callback,ss,ff){
								//that.getLoadMask('').hide();
								that.hide();
								

							}
						});
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
					id: 'DbfImportForm',
					labelAlign: 'right',
					labelWidth: 200,
					fileUpload: true,
					items: [
						{
					allowBlank: false,
					fieldLabel: lang['data_otcheta'],
					format: 'Y-m-d',
					listeners: {
						'select': function() {},
						'keydown': function (/*inp, e*/) {}
					},
					name: 'ReportDate',
					plugins: [ new Ext.ux.InputTextMask('9999-99-99', false) ],
					tabIndex: that.tabindex + 1,
					width: 100,
					xtype: 'swdatefield'
				}
					],
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
					url: '/?c=ImportSchema&m=exportTarifList'
				})
			]});
		sw.Promed.swExportDBFWindow.superclass.initComponent.apply(this, arguments);
                
		
	}
});
