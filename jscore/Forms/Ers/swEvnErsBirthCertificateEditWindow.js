/**
* Форма редактирования ЭРС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsBirthCertificateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'ЭРС',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 500,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE }),
			params = {
				Lpu_id: base_form.findField('Lpu_id').getValue(),
				EvnERSBirthCertificate_OrgName: base_form.findField('Lpu_id').getFieldValue('Lpu_Name'),
				EvnERSBirthCertificate_OrgINN: base_form.findField('Org_INN').getValue(),
				EvnERSBirthCertificate_OrgOGRN: base_form.findField('Org_OGRN').getValue(),
				EvnERSBirthCertificate_OrgKPP: base_form.findField('Org_KPP').getValue(),
				EvnErsBirthCertificate_PregnancyRegDate: Ext.util.Format.date(base_form.findField('EvnErsBirthCertificate_PregnancyRegDate').getValue(), 'd.m.Y')
			};

		console.log(params);
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();	
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.callback();
					}	
				}
				else {
					Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
				}
				
			}
		});
	},
	
	show: function() {
		sw.Promed.swEvnErsBirthCertificateEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.EvnERSBirthCertificate_id = arguments[0].EvnERSBirthCertificate_id || null;
		this.Person_id = arguments[0].Person_id || null;
		
		base_form.reset();
		
		switch (this.action){
			case 'add':
				this.setTitle('ЭРС: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('ЭРС: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('ЭРС: Просмотр');
				this.enableEdit(false);
				break;
		}
		
		switch (this.action){
			case 'add':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
					params: {
						Lpu_id: getGlobalOptions().lpu_id,
						Person_id: this.Person_id
					},
					success: function (form, action) {
						loadMask.hide();
						win.onLoad();
						base_form.findField('LpuFSSContract_id').focus();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});	
				break;
			case 'edit':
			case 'view':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsBirthCertificate&m=load',
					params: {
						EvnERSBirthCertificate_id: win.EvnERSBirthCertificate_id
					},
					success: function (form, action) {
						loadMask.hide();
						win.onLoad();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});
				break;
		}
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.LpuPanel.loadLpuFSSContractCombo();

		var personregister_combo = base_form.findField('PersonRegister_id');
		personregister_combo.getStore().load({
			params: {Person_id: base_form.findField('Person_id').getValue()},
			callback: function () {
				personregister_combo.fireEvent('change', personregister_combo, personregister_combo.getValue());
			}
		});
			
		if (this.action == 'view') return false;
		
		this.PersonPanel.checkFields();
	},	
	
	initComponent: function() {
		var win = this;
		
		this.LpuPanel = new sw.Promed.ErsLpuPanel;
		
		this.PersonPanel = new sw.Promed.ErsPersonPanel;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				name: 'EvnERSBirthCertificate_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			},
			this.LpuPanel,
			this.PersonPanel, 
			{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						codeField: 'PersonRegister_Code',
						displayField: 'PersonRegister_Name',
						editable: false,
						fieldLabel: 'Карта беременной',
						hiddenName: 'PersonRegister_id',
						width: 350,
						ignoreCodeField: true,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							url: '/?c=EvnErsBirthCertificate&m=loadPersonRegisterList',
							fields: [
								{name: 'PersonRegister_id', mapping: 'PersonRegister_id'},
								{name: 'PersonRegister_Code', mapping: 'PersonRegister_Code'},
								{name: 'PersonRegister_setDate', mapping: 'PersonRegister_setDate'},
								{name: 'PersonRegister_Name', mapping: 'PersonRegister_Name'}
							],
							key: 'PersonRegister_id',
							sortInfo: {field: 'PersonRegister_Code'}
						}),
						valueField: 'PersonRegister_id',
						listeners: {
							'change': function(combo, nv, ov) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == nv);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function (combo, record) {
								var base_form = win.MainPanel.getForm();
								if(record && record.get('PersonRegister_id')) {
									base_form.findField('EvnErsBirthCertificate_PregnancyRegDate').setValue(record.get('PersonRegister_setDate'));
									base_form.findField('EvnErsBirthCertificate_PregnancyRegDate').disable();
								} else if (win.action !== 'view') {
									base_form.findField('EvnErsBirthCertificate_PregnancyRegDate').enable();
								}
							}
						},
						xtype: 'swbaselocalcombo'
					}]
				}, {
					layout: 'form',
					labelWidth: 160,
					items: [{
						xtype: 'swdatefield',
						name: 'EvnErsBirthCertificate_PregnancyRegDate',
						width: 100,
						fieldLabel: 'Дата постановки на учет'
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'EvnERSBirthCertificate_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Person_id' },
				{ name: 'Person_SurName' },
				{ name: 'Person_FirName' },
				{ name: 'Person_SecName' },
				{ name: 'Person_BirthDay' },
				{ name: 'Person_Snils' },
				{ name: 'DocumentType_Name' },
				{ name: 'Document_Ser' },
				{ name: 'Document_Num' },
				{ name: 'Document_begDate' },
				{ name: 'OrgDep_Name' },
				{ name: 'Polis_Num' },
				{ name: 'Polis_begDate' },
				{ name: 'Address_Address' },
				{ name: 'Lpu_id' },
				{ name: 'Org_INN' },
				{ name: 'Org_KPP' },
				{ name: 'Org_OGRN' },
				{ name: 'LpuFSSContract_id' },
				{ name: 'EvnERSBirthCertificate_PolisNoReason' },
				{ name: 'EvnERSBirthCertificate_SnilsNoReason' },
				{ name: 'EvnERSBirthCertificate_DocNoReason' },
				{ name: 'EvnERSBirthCertificate_AddressNoReason' },
				{ name: 'PersonRegister_id' },
				{ name: 'EvnErsBirthCertificate_PregnancyRegDate' }
			]),
			url: '/?c=EvnErsBirthCertificate&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function () {
					this.doSave({send2fss: true});
				}.createDelegate(this),
				text: 'Отправить в ФСС'
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swEvnErsBirthCertificateEditWindow.superclass.initComponent.apply(this, arguments);
	}
});