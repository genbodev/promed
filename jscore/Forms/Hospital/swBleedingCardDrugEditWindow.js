/**
 * swBleedingCardDrugEditWindow - Форма добавления/редактирования лекарственного средства для карты наблюдения за кровотечениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://rtmis.ru/
 *
 * @package		Stac
 * @access		public
 * @copyright	Copyright (c) 2019 Swan Ltd.
 * @author		Stanislav Bykov
 * @version		11.12.2019
 */

sw.Promed.swBleedingCardDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	id: 'BleedingCardDrugEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('BleedingCardDrugEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					win.doSave();
					break;

				case Ext.EventObject.J:
					win.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	layout: 'form',
	maximizable: false,
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	tabIndexFirst: 990,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function() {
		var
			win = this,
			form = win.formPanel,
			base_form = form.getForm();

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

		win.callback({
			'BleedingCardDrug_id': base_form.findField('BleedingCardDrug_id').getValue(),
			'BleedingCardDrug_setDate': base_form.findField('BleedingCardDrug_setDate').getValue().format('d.m.Y'),
			'BleedingCardDrug_setTime': base_form.findField('BleedingCardDrug_setTime').getValue(),
			'DrugComplexMnn_id': base_form.findField('DrugComplexMnn_id').getValue(),
			'PrescriptionIntroType_id': base_form.findField('PrescriptionIntroType_id').getValue(),
			'GoodsUnit_id': base_form.findField('GoodsUnit_id').getValue(),
			'BleedingCardDrug_setDT': getValidDT(base_form.findField('BleedingCardDrug_setDate').getValue().format('d.m.Y'), base_form.findField('BleedingCardDrug_setTime').getValue()),
			'DrugComplexMnn_Name': base_form.findField('DrugComplexMnn_id').getFieldValue('DrugComplexMnn_Name'),
			'PrescriptionIntroType_Name': base_form.findField('PrescriptionIntroType_id').getFieldValue('PrescriptionIntroType_Name'),
			'BleedingCardDrug_Dosage': base_form.findField('BleedingCardDrug_Dosage').getValue(),
			'BleedingCardDrug_TotalScore': base_form.findField('BleedingCardDrug_TotalScore').getValue()
		});
		win.hide();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function(params) {
		sw.Promed.swBleedingCardDrugEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var base_form = this.formPanel.getForm();
		base_form.reset();

		this.action = arguments[0].action || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = arguments[0].onHide || Ext.emptyFn;

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.setValues(arguments[0].formParams);

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Лекарственное средство') + ': ' + langs('Добавление'));
				this.enableEdit(true);
				base_form.findField('BleedingCardDrug_setDate').focus(true);
				break;

			case 'edit':
				this.setTitle(langs('Лекарственное средство') + ': ' + langs('Редактирование'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('BleedingCardDrug_setDate').focus(true);
				break;

			case 'view':
				this.setTitle(langs('Лекарственное средство') + ': ' + langs('Просмотр'));
				this.enableEdit(false);
				this.buttons[this.buttons.length - 1].focus();
				break;
		}

		if ( this.action == 'add' ) {
			loadMask.hide();
		}
		else {
			var DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();

			base_form.findField('DrugComplexMnn_id').getStore().load({
				callback: function() {
					if ( base_form.findField('DrugComplexMnn_id').getStore().getCount() == 1 ) {
						base_form.findField('DrugComplexMnn_id').setValue(DrugComplexMnn_id);
					}
					else {
						base_form.findField('DrugComplexMnn_id').clearValue();
					}

					loadMask.hide();
				},
				params: {
					DrugComplexMnn_id: DrugComplexMnn_id
				}
			});
		}
	},

	/* конструктор */
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'BleedingCardDrugEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'BleedingCardDrug_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'BleedingCardDrug_setDate' },
				{ name: 'BleedingCardDrug_setTime' },
				{ name: 'DrugComplexMnn_id' },
				{ name: 'PrescriptionIntroType_id' },
				{ name: 'BleedingCardDrug_Dosage' },
				{ name: 'GoodsUnit_id' },
				{ name: 'BleedingCardDrug_TotalScore' }
			]),
			url: '/?c=BleedingCard&m=saveBleedingCardDrug',
			items: [{
				name: 'BleedingCardDrug_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Дата'),
						format: 'd.m.Y',
						name: 'BleedingCardDrug_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: form.tabIndexFirst++,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Время'),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'BleedingCardDrug_setTime',
						onTriggerClick: function() {
							var
								base_form = form.formPanel.getForm(),
								time_field = this;

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('BleedingCardDrug_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: form.id
							});
						},
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: form.tabIndexFirst++,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				anchor: '95%',
				fieldLabel: 'Лекарственное средство',
				hiddenName: 'DrugComplexMnn_id',
				tabIndex: form.tabIndexFirst++,
				xtype: 'swdrugcomplexmnncombo'
			}, {
				anchor: '95%',
				comboSubject: 'PrescriptionIntroType',
				fieldLabel: langs('Способ применения'),
				hiddenName: 'PrescriptionIntroType_id',
				tabIndex: form.tabIndexFirst++,
				xtype: 'swcommonsprcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowDecimals: true,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: langs('Дозировка'),
						name: 'BleedingCardDrug_Dosage',
						tabIndex: form.tabIndexFirst++,
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 5,
					items: [{
						comboSubject: 'GoodsUnit',
						fieldLabel: '',
						hiddenName: 'GoodsUnit_id',
						labelSeparator: '',
						listWidth: 300,
						tabIndex: form.tabIndexFirst++,
						width: 100,
						xtype: 'swcommonsprcombo'
					}]
				}]
			}, {
				allowDecimals: true,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Общее количество'),
				listeners: {
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
							e.stopEvent();
							form.buttons[form.buttons.length - 1].focus();
						}
					}
				},
				name: 'BleedingCardDrug_TotalScore',
				tabIndex: form.tabIndexFirst++,
				width: 100,
				xtype: 'numberfield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
					else {
						form.formPanel.getForm().findField('BleedingCardDrug_setDate').focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},
				tabIndex: form.tabIndexFirst++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},
				onTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 2].focus(true);
					}
					else {
						form.formPanel.getForm().findField('BleedingCardDrug_TotalScore').focus(true);
					}
				},
				tabIndex: form.tabIndexFirst++,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.formPanel
			]
		});

		sw.Promed.swBleedingCardDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});