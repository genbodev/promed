/**
 * swRegistryEditWindow - окно редактирования/добавления реестра (счета).
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Shayahmetov
 * @version      23.11.2019
 * @comment      Префикс для id компонентов rege (RegistryEditForm)
 */

sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		var
			base_form = this.RegistryForm.getForm(),
			form = this.RegistryForm,
			win = this;
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var begDate = base_form.findField('Registry_begDate').getValue();
		var endDate = base_form.findField('Registry_endDate').getValue();
		
		if ( !Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) && begDate > endDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('Registry_begDate').focus(false);
				},
				icon: Ext.Msg.ERROR,
				msg: 'Дата окончания не может быть меньше даты начала.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет постановка реестра в очередь на формирование..."});
		loadMask.show();
		
		var params = {
			Registry_accDate: base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y'),
			RegistryType_id: base_form.findField('RegistryType_id').getValue()
		};
		
		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
				
				var msg = 'При постановке реестра в очередь на формирование произошла ошибка';
				
				if ( action.result && !Ext.isEmpty(action.result.Error_Msg) ) {
					msg = action.result.Error_Msg;
				}
				
				sw.swMsg.alert('Ошибка', msg);
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				
				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						var records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						}
						
						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'При выполнении операции сохранения произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
		
		return true;
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.RegistryForm.getForm();
		
		if ( enable ) {
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();
			
			this.buttons[0].show();
		}
		else {
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('OrgSMO_id').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();
			
			this.buttons[0].hide();
		}
	},
	firstTabIndex: 15100,
	id: 'RegistryEditWindow',
	initComponent: function() {
		// Форма с полями
		var form = this;
		
		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				name: 'Registry_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RegistryStatus_id',
				value: 3, // По умолчанию при добавлении
				xtype: 'hidden'
			}, {
				name: 'Registry_IsActive',
				value: 2, // По умолчанию при добавлении
				xtype: 'hidden'
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				tabIndex: form.firstTabIndex + 1,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 2,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 3,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				tabIndex: form.firstTabIndex + 4,
				width: 150,
				xtype: 'swpaytypecombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: form.firstTabIndex++,
				loadParams: {params: {where: " where KatNasel_SysNick <> 'all'"}},
				listeners: {
					change: function(combo, newVal, OldVal){
						var orgSMOCombo = form.RegistryForm.getForm().findField('OrgSMO_id'),
							SysNick = combo.getFieldValue('KatNasel_SysNick');
						
						orgSMOCombo.setContainerVisible(SysNick && SysNick==='oblast');
						orgSMOCombo.setAllowBlank(!(SysNick && SysNick==='oblast'));
					}
				}
			}, {
				width: 250,
				fieldLabel: 'СМО',
				xtype: 'sworgsmocombo',
				hiddenName: 'OrgSMO_id',
				withoutTrigger: true,
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				tabIndex: form.firstTabIndex++,
				lastQuery: '',
				minChars: 1,
				queryDelay: 1
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex + 9,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				hiddenName: 'OrgRSchet_id',
				tabIndex: form.firstTabIndex + 10,
				width: 280,
				xtype: 'sworgrschetcombo'
			}, {
				allowBlank: false,
				disabled: true,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex + 11,
				width: 100,
				xtype: 'swdatefield'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e)
				{
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
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
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryType_id' },
				{ name: 'RegistryStatus_id' },
				{ name: 'KatNasel_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_IsActive' },
				{ name: 'PayType_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex + 21,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, form.firstTabIndex + 22),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				tabIndex: form.firstTabIndex + 23,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.RegistryForm
			]
		});
		
		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		hide: function() {
			swLpuBuildingGlobalStore.clearFilter();
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + win.id + '.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			win.hide();
			return false;
		}
		
		var base_form = this.RegistryForm.getForm();
		base_form.reset();
		
		base_form.setValues(arguments[0]);
		
		win.action = null;
		win.callback = Ext.emptyFn;
		win.onHide = Ext.emptyFn;
		win.owner = null;
		win.RegistryStatus_id = null;
		win.RegistryType_id = null;
		
		if ( arguments[0].action ) {
			win.action = arguments[0].action;
		}
		
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			win.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			win.onHide = arguments[0].onHide;
		}
		
		if ( arguments[0].owner ) {
			win.owner = arguments[0].owner;
		}
		
		if ( arguments[0].RegistryStatus_id ) {
			win.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}
		
		if ( arguments[0].RegistryType_id ) {
			win.RegistryType_id = arguments[0].RegistryType_id;
		}
		
		base_form.findField('RegistryType_id').fireEvent('change', base_form.findField('RegistryType_id'), win.RegistryType_id);
		
		base_form.findField('OrgSMO_id').getStore().filterBy(function(rec) {
			return (rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});
		
		base_form.findField('OrgSMO_id').setBaseFilter(function(rec) {
			return (rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});
		base_form.findField('KatNasel_id').getStore().load({params: {where: " where KatNasel_SysNick <> 'all'"}});
		
		win.syncSize();
		win.syncShadow();
		
		if ( win.action == 'edit' ) {
			win.buttons[0].setText('Переформировать');
		}
		else {
			win.buttons[0].setText('Сохранить');
		}
		
		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( win.RegistryStatus_id == 4 ) {
			win.action = "view";
		}
		
		if ( base_form.findField('OrgRSchet_id').getStore().getCount() == 0 ) {
			base_form.findField('OrgRSchet_id').getStore().load({
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}

		win.syncSize();
		win.doLayout();

		var loadMask = new Ext.LoadMask(win.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		switch ( win.action ) {
			case 'add':
				win.setTitle(WND_ADMIN_REGISTRYADD);
				win.enableEdit(true);
				loadMask.hide();
				
				base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', getPayTypeSysNickOms());
				
				base_form.findField('Registry_begDate').focus(true, 50);
				base_form.findField('OrgSMO_id').setContainerVisible(false);
				base_form.findField('OrgSMO_id').setAllowBlank(true);
				break;
			
			case 'edit':
			case 'view':
				if ( win.action == 'edit' ) {
					win.setTitle(WND_ADMIN_REGISTRYEDIT);
					win.enableEdit(true);
				}
				else {
					win.setTitle(WND_ADMIN_REGISTRYVIEW);
					win.enableEdit(false);
				}
				
				base_form.load({
					params: {
						Registry_id: base_form.findField('Registry_id').getValue()
					},
					failure: function() {
						loadMask.hide();
						
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
							title: 'Ошибка'
						});
					},
					success: function() {
						loadMask.hide();

						base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());

						if ( win.action == 'edit' ){
							base_form.findField('Registry_begDate').focus(true, 50);
						}
						else {
							win.buttons[win.buttons.length - 1].focus();
						}
					},
					url: '/?c=Registry&m=loadRegistry'
				});
				break;
		}
	},
	width: 600
});