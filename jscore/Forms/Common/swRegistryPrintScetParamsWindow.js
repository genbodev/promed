/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 16.08.16
 * Time: 15:02
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swRegistryPrintScetParamsWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	draggable: true,
	width: 450,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Печать счета',
	id: 'RegistryPrintScetParamsWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'RPSPW_PrintButton',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['napechatat']
			}, {
				text: '-'
			},
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'RPSPW_CloseButton',
					onShiftTabAction: function() {
						this.buttons[0].focus();
					}.createDelegate(this),
					onTabAction: function() {
						this.buttons[0].focus();
					}.createDelegate(this),
					text: BTN_FRMCLOSE
				}],
			items: [

				new Ext.form.FormPanel
					({
						id: 'RPSPW_RegistryPrintScetParamsPanel',
						style : 'padding: 3px',
						autoheight: true,
						region: 'center',
						layout : 'form',
						border : false,
						frame : true,
						items: [
							{
								allowBlank: false,
								fieldLabel: 'Счет №',
								name: 'RPSW_RegistryNum',
								width: 300,
								xtype: 'textfield'
							},
							{
								allowBlank: false,
								fieldLabel: 'Дата счета',
								name: 'RPSW_Registry_accDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								format: 'd.m.Y',
								width: 100,
								xtype: 'swdatefield'
							},
							{
								allowBlank: false,
								editable: false,
								displayField: 'OrgHeadPerson_Fio',
								fieldLabel: 'Руководитель',
								name: 'GLPerson_id',
								id: 'RPSPW_GLPerson',
								store: new Ext.data.Store({
									autoLoad: true,
									reader: new Ext.data.JsonReader({
										id: 'Person_id'
									}, [
										{ name: 'Person_id', mapping: 'Person_id'},
										{ name: 'OrgHeadPerson_Fio', mapping: 'OrgHeadPerson_Fio' }
									]),
									url: '/?c=Common&m=loadOrgHeadGLList'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3>{OrgHeadPerson_Fio}</h3></td></tr></table>',
									'</div></tpl>'
								),
								triggerAction: 'all',
								hideTrigger: false,
								valueField: 'Person_id',
								width: 300,
								listWidth: 300,
								xtype: 'swbaselocalcombo'
							},
							{
								allowBlank: false,
								editable: false,
								displayField: 'OrgHeadPerson_Fio',
								fieldLabel: 'Главный бухгалтер',
								name: 'BUHPerson_id',
								id: 'RPSPW_BUHPerson',
								store: new Ext.data.Store({
									autoLoad: true,
									reader: new Ext.data.JsonReader({
										id: 'Person_id'
									}, [
										{ name: 'Person_id', mapping: 'Person_id'},
										{ name: 'OrgHeadPerson_Fio', mapping: 'OrgHeadPerson_Fio' }
									]),
									url: '/?c=Common&m=loadOrgHeadBUHList'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3>{OrgHeadPerson_Fio}</h3></td></tr></table>',
									'</div></tpl>'
								),
								triggerAction: 'all',
								hideTrigger: false,
								valueField: 'Person_id',
								width: 300,
								listWidth: 300,
								xtype: 'swbaselocalcombo'
							},
							{
								allowBlank: false,
								editable: false,
								displayField: 'IspolnPerson_Fio',
								fieldLabel: 'Ответственный',
								name: 'IspolnPerson_id',
								id: 'RPSPW_IspolnPerson',
								store: new Ext.data.Store({
									autoLoad: true,
									reader: new Ext.data.JsonReader({
										id: 'Person_id'
									}, [
										{ name: 'Person_id', mapping: 'Person_id'},
										{ name: 'IspolnPerson_Fio', mapping: 'IspolnPerson_Fio' }
									]),
									url: '/?c=Common&m=loadMedPersList'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3>{IspolnPerson_Fio}</h3></td></tr></table>',
									'</div></tpl>'
								),
								triggerAction: 'all',
								hideTrigger: false,
								valueField: 'Person_id',
								width: 300,
								listWidth: 300,
								xtype: 'swbaselocalcombo'
							},
							{
								allowBlank: false,
								width: 300,
								listWidth: 300,
								hiddenName: 'OrgRSchet_id',
								id: 'RPSPW_OrgRSchet_Combo',
								xtype: 'sworgrschetcombo'
							}
						]
					})
			]
		});
		sw.Promed.swRegistryPrintScetParamsWindow.superclass.initComponent.apply(this, arguments);
	},
	minWidth: 300,
	onSelect: function() {
		var form = this.findById("RPSPW_RegistryPrintScetParamsPanel").getForm();

		if ( !form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById("RPSPW_RegistryPrintScetParamsPanel").getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var paramRegistry = this.Registry_id;
		var paramRegistryName = form.findField("RPSW_RegistryNum").getValue();
		var paramDate = Ext.util.Format.date(form.findField("RPSW_Registry_accDate").getValue(), 'd.m.Y');
		var paramGlVrach = form.findField("GLPerson_id").getValue();
		var paramGlBuh = form.findField("BUHPerson_id").getValue();
		var paramIspoln = form.findField("IspolnPerson_id").getValue();
		var paramOrgRSchet = form.findField("OrgRSchet_id").getValue();
		printBirt({
			'Report_FileName': 'ScetStrah.rptdesign',
			'Report_Params': '&paramRegistry=' + paramRegistry + '&paramRegistryName=' + paramRegistryName + '&paramDate=' + paramDate + '&paramGlVrach=' + paramGlVrach + '&paramGlBuh=' + paramGlBuh + '&paramIspoln=' + paramIspoln + '&paramOrgRSchet=' +  paramOrgRSchet ,
			'Report_Format': 'pdf'
		});
		this.hide();
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swRegistryPrintScetParamsWindow.superclass.show.apply(this, arguments);
		this.center();
		this.Registry_id = 0;
		var win = this;
		var form = this.findById("RPSPW_RegistryPrintScetParamsPanel").getForm();
		form.reset();
		if(arguments[0] && arguments[0].Registry_id)
			this.Registry_id = arguments[0].Registry_id;
		if(arguments[0] && arguments[0].Registry_Num)
			form.findField("RPSW_RegistryNum").setValue(arguments[0].Registry_Num);
		if(arguments[0] && arguments[0].Registry_accDate)
			form.findField("RPSW_Registry_accDate").setValue(arguments[0].Registry_accDate);
		
		if ( form.findField('OrgRSchet_id').getStore().getCount() == 0 ) {
			form.findField('OrgRSchet_id').getStore().load({
				callback: function() {
					win.filterOrgRSchetCombo();
				},
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: '',
					OrgRSchetType_id: '',
					OrgRSchet_begDate: '',
					OrgRSchet_endDate: ''
				}
			});
		}
		else {
			win.filterOrgRSchetCombo();
		}
		
		
	},
	
	filterOrgRSchetCombo: function(OrgRSchet_id) {
		var combo = this.findById('RPSPW_OrgRSchet_Combo');
		

		combo.getStore().clearFilter();
		combo.lastQuery = '';

		
		combo.getStore().filterBy(function(rec) {
			return (rec.get('OrgRSchetType_id') == 1);
		});
	}
	

	
});