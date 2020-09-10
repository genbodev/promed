/**
 * swUnionRegistryEditWindow - окно просмотра, добавления и редактирования объединённых реестров
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
 */

sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closeAction: 'hide',
	draggable: true,
	id: 'swUnionRegistryEditWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	firstTabIndex: TABINDEX_UREW,
	title: '',
	width: 600,
	
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};
		
		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		
		if (
			base_form.findField('Registry_accDate').getValue() <= base_form.findField('Registry_endDate').getValue()
			&& base_form.findField('Registry_accDate').getValue() >= base_form.findField('Registry_begDate').getValue()
		) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата не должна входить в период реестра.');
			return;
		}
		
		var begDate = base_form.findField('Registry_begDate').getValue();
		var endDate = base_form.findField('Registry_endDate').getValue();
		
		// Проверяем чтобы период был не больше календарного месяца с 1-е по последнее число
		if ( begDate.getFullYear() != endDate.getFullYear() || begDate.getMonth() != endDate.getMonth() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						// Сохранение реестра
						win.getLoadMask('Подождите, сохраняется запись...').show();
						
						base_form.submit({
							failure: function (form, action) {
								win.getLoadMask().hide();
							},
							params: params,
							success: function(form, action) {
								win.getLoadMask().hide();
								win.hide();
								win.callback(win.owner, action.result.Registry_id);
							}
						});
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Указанный период более одного календарного месяца. Рекомендуется формировать реестры за один календарный месяц. Продолжить формирование?',
				title: ERR_INVFIELDS_TIT
			});
			sw.swMsg.getDialog().buttons[2].focus();
			
			return false;
		}
		else {
			// Сохранение реестра
			win.getLoadMask('Подождите, сохраняется запись...').show();
			
			base_form.submit({
				failure: function (form, action) {
					win.getLoadMask().hide();
				},
				params: params,
				success: function(form, action) {
					win.getLoadMask().hide();
					win.hide();
					win.callback(win.owner, action.result.Registry_id);
				}
			});
		}
	},
	show: function() {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.center();
		
		var win = this,
			base_form = win.formPanel.getForm();
		
		base_form.reset();
		
		win.syncSize();
		win.doLayout();
		
		base_form.setValues(arguments[0]);
		
		switch ( win.action ) {
			case 'view':
				win.setTitle('Объединённый реестр: Просмотр');
				break;
			
			case 'edit':
				win.setTitle('Объединённый реестр: Редактирование');
				break;
			
			case 'add':
				win.setTitle('Объединённый реестр: Добавление');
				break;
			
			default:
				log('swUnionRegistryEditWindow - action invalid');
				return false;
				break;
		}
		
		base_form.findField('OrgSMO_id').getStore().filterBy(function(rec) {
			return (rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});
		
		base_form.findField('OrgSMO_id').setBaseFilter(function(rec) {
			return (rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});
		base_form.findField('KatNasel_id').getStore().load();
		
		if ( win.action == 'add' ) {
			win.enableEdit(true);
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			base_form.findField('Registry_Num').focus(true, 250);
		}
		else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			
			win.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					Registry_id: base_form.findField('Registry_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					
					if ( win.action == 'edit') {
						win.enableEdit(true);
					}
					
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					base_form.findField('KatNasel_id').fireEvent('change', base_form.findField('KatNasel_id'), base_form.findField('KatNasel_id').getValue());
					
					if ( win.action == 'edit') {
						base_form.findField('Registry_Num').focus(true, 100);
					}
					else {
						win.buttons[win.buttons.length - 1].focus();
					}
					
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	},
	
	/* конструктор */
	initComponent: function() {
		var win = this;
		
		win.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 180,
			region: 'center',
			items: [{
				allowBlank: false,
				allowDecimals: false,
				allowLeadingZeroes: true,
				allowNegative: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "15",
					autocomplete: "off"
				},
				disabled: false,
				enableKeyEvents: true,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				minValue: 1,
				name: 'Registry_Num',
				tabIndex: win.firstTabIndex++,
				width: 120,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: win.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Категория населения',
				width: 250,
				xtype: 'swkatnaselcombo',
				hiddenName: 'KatNasel_id',
				tabIndex: win.firstTabIndex++,
				listeners: {
					change: function(combo, newVal, OldVal){
						var orgSMOCombo = win.formPanel.getForm().findField('OrgSMO_id'),
							SysNick = combo.getFieldValue('KatNasel_SysNick');
						
						orgSMOCombo.setContainerVisible(SysNick && SysNick==='oblast');
						orgSMOCombo.setAllowBlank(!(SysNick && SysNick==='oblast'));
						if(!(SysNick && SysNick==='oblast')){
							orgSMOCombo.clearValue();
						}
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
				tabIndex: win.firstTabIndex++,
				lastQuery: '',
				minChars: 1,
				queryDelay: 1
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( win.action != 'view' ) {
								win.doSave();
							}
							break;
						case Ext.EventObject.J:
							win.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: win,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'KatNasel_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Lpu_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});
		
		Ext.apply(win, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: win.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(win, win.firstTabIndex++),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onShiftTabAction: function() {
					win.buttons[win.buttons.length - 2].focus();
				},
				onTabAction: function() {
					var base_form = win.formPanel.getForm();

					if ( !base_form.findField('Registry_Num').disabled ) {
						base_form.findField('Registry_Num').focus(true, 100);
					}
					else {
						win.buttons[win.buttons.length - 2].focus();
					}
				},
				tabIndex: win.firstTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				win.formPanel
			]
		});
		
		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(win, arguments);
	}
});