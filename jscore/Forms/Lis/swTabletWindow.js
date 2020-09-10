/**
* ЛИС: форма Выбор планшета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    19.11.2019
*/

sw.Promed.swTabletWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swTabletWindow',
	winTitle: 'Выбор планшета',
	modal: true,
	maximized: false,
	width: 800,
	cls: 'swTabletWindow',
	height: 360,

	show: function () {
		let params = arguments[0],
			win = this,
			form = win.formPanel.getForm();

		win.action = params.action ? params.action : 'view';
		win.vertSizeField.disable();
		win.horizSizeField.disable();
		delete params.action;

		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		sw.Promed.swTabletWindow.superclass.show.apply(win, arguments);

		switch (win.action) {
			case "edit":
				win.setTitle(win.winTitle + ': Редактирование');
				break;
			case "add":
				win.setTitle(win.winTitle + ": Добавление");
				break;
			default:
				win.setTitle(win.winTitle + ': Просмотр');
		}

		let baseParams = {
			MedService_id: params.MedService_id,
			mode: 'ifalab'
		};
		win.analyzerCombo.getStore().baseParams = baseParams;
		win.methodsIFACombo.getStore().baseParams = baseParams;

		win.analyzerCombo.getStore().load();

		//win.enableEdit(win.action.inlist(["edit", "add"]));
		if(win.action.inlist(['edit','view']))
			win.formPanel.loadForm({ Tablet_id: params.Tablet_id });
		form.reset();
		form.setValues(params);
	},

	initComponent: function () {
		let win = this;

		let wholeRadio = new Ext.form.Radio({
			name: 'tabletSizeGroup',
			inputValue: 1,
			boxLabel: 'Целый планшет',
			checked: true,
			listeners: {
				change: function (radio) {
					let sizesRec = win.tabletTypeCombo.getSelectedRecordData();
					win.vertSizeField.disable();
					win.horizSizeField.disable();
					win.vertSizeField.setValue(sizesRec.TabletType_VertSize);
					win.horizSizeField.setValue(sizesRec.TabletType_HorizSize);
				}
			}
		});

		let vertRadio = new Ext.form.Radio({
			name: 'tabletSizeGroup',
			inputValue: 2,
			boxLabel: 'Разделить по вертикали',
			listeners: {
				change: function (radio) {
					let sizesRec = win.tabletTypeCombo.getSelectedRecordData();
					win.vertSizeField.enable();
					win.horizSizeField.disable();
					win.vertSizeField.setValue(sizesRec.TabletType_VertSize);
					win.horizSizeField.setValue(sizesRec.TabletType_HorizSize);
				}
			}
		});

		let horizRadio = new Ext.form.Radio({
			name: 'tabletSizeGroup',
			inputValue: 3,
			boxLabel: 'Разделить по горизонтали',
			listeners: {
				change: function (radio) {
					let sizesRec = win.tabletTypeCombo.getSelectedRecordData();
					win.vertSizeField.disable();
					win.horizSizeField.enable();
					win.vertSizeField.setValue(sizesRec.TabletType_VertSize);
					win.horizSizeField.setValue(sizesRec.TabletType_HorizSize);
				}
			}
		});

		win.radioGroup = new Ext.form.RadioGroup({
			labelSeparator: '',
			xtype: 'radiogroup',
			value: 1,
			items: [
				wholeRadio,
				vertRadio,
				horizRadio
			]
		});

		win.analyzerCombo = new sw.Promed.SwAnalyzerCombo({
			fieldLabel: 'Анализатор',
			allowBlank: false,
			editable: true,
			listWidth: 300,
			anchor: '100%',
			store: new Ext.data.JsonStore({
				fields: [
					{ type: 'int', name: 'Analyzer_id' },
					{ type: 'int', name: 'Analyzer_Code' },
					{ type: 'int', name: 'AnalyzerModel_id' },
					{ type: 'int', name: 'Analyzer_IsUseAutoReg' },
					{ type: 'date', name: 'Analyzer_begDT' },
					{ type: 'string', name: 'Analyzer_Name' }
				],
				url: '?c=Analyzer&m=loadList'
			}),
			fireEventSelect: function() {
				let combo = this,
					rec = this.getStore().getById(combo.getValue());
				this.fireEvent('select', this, rec);
			},
			listeners: {
				select: function (combo) {
					if (win.action !== 'view') {
						win.methodsIFACombo.clearValue();
						win.methodsIFACombo.setDisabled(!combo.getValue());
					}

					win.methodsIFACombo.getStore().removeAll();

					win.methodsIFACombo.getStore().baseParams.Analyzer_id = combo.getValue();
					win.methodsIFACombo.getStore().load();
				}
			}
		});

		win.methodsIFACombo = new sw.Promed.SwBaseLocalCombo({
			hiddenName: 'MethodsIFA_id',
			valueField: 'MethodsIFA_id',
			displayField: 'MethodsIFA_Name',
			fieldLabel: 'Методики',
			allowBlank: false,
			disabled: true,
			anchor: '100%',
			editable: true,
			store: new Ext.data.JsonStore({
				fields: [
					{ type: 'int', name: 'MethodsIFA_id' },
					{ type: 'string', name: 'MethodsIFA_Name' }
				],
				key: 'MethodsIFA_id',
				url: '/?c=MethodsIFA&m=loadFilterCombo',
				listeners: {
					load: function (store) {
						win.methodsIFACombo.setValue(win.methodsIFACombo.getValue());
					}
				}
			}),
			fireEventSelect: function() {
				let combo = this,
					rec = this.getStore().getById(combo.getValue());
				this.fireEvent('select', this, rec);
			},
			listeners: {
				select: function(combo,rec) {
					win.tabletTypeCombo.clearValue();
					win.tabletTypeCombo.getStore().removeAll();
					win.tabletTypeCombo.getStore().baseParams.MethodsIFA_id = combo.getValue();
					win.tabletTypeCombo.getStore().load();
				}
			}
		});

		win.tabletTypeCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Формат планшета',
			hiddenName: 'MethodsIFATabletType_id',
			valueField: 'MethodsIFATabletType_id',
			displayField: 'TabletType_Name',
			allowBlank: false,
			store: new Ext.data.JsonStore({
				baseParams: {},
				fields: [
					{ type: 'int', name: 'MethodsIFATabletType_id' },
					{ type: 'int', name: 'TabletType_id' },
					{ type: 'int', name: 'TabletType_VertSize' },
					{ type: 'int', name: 'TabletType_HorizSize' },
					{ type: 'string', name: 'TabletType_Code' },
					{ type: 'string', name: 'TabletType_Name',
						convert: function (val, row) {
							return row.TabletType_VertSize + 'x' + row.TabletType_HorizSize;
						}
					}
				],
				key: 'MethodsIFATabletType_id',
				url: '/?c=Tablet&m=loadCombo',
				listeners: {
					load: function (store) {
						win.tabletTypeCombo.setValue( win.tabletTypeCombo.getValue() );
					}
				}
			}),
			listeners: {
				select: function(combo, rec, idx) {
					if(!rec) return;
					win.vertSizeField.setValue(rec.get('TabletType_VertSize'));
					win.horizSizeField.setValue(rec.get('TabletType_HorizSize'));
				}
			}
		});

		win.tabletFillTypeRadioList = [
			new Ext.form.Radio({name: 'mode', boxLabel: 'Одна лунка', inputValue: 1, checked: true }),
			new Ext.form.Radio({name: 'mode', boxLabel: 'Заполнение с дублями',  inputValue: 2 }),
			new Ext.form.Radio({name: 'mode', boxLabel: 'Проверочный планшет', inputValue: 3 })
		];

		win.tabletFillTypeRadio = new Ext.form.RadioGroup({
			labelSeparator: '',
			border: true,
			items: win.tabletFillTypeRadioList,
			listeners: {
				change: function(radiogroup, radio) {
					let form = win.formPanel.getForm(),
						holeCountField = form.findField('Tablet_HoleCount'),
						isDoublesField = form.findField('Tablet_IsDoublesFill'),
						isTestTabletField = form.findField('Tablet_IsTestTablet'),
						value = radio.getGroupValue(),
						isTest = value == 3;

					holeCountField.setDisabled(!isTest);
					holeCountField.setContainerVisible(isTest);
					isDoublesField.setValue(value == 2 ? 2 : 1);
					isTestTabletField.setValue(value == 3 ? 2 : 1);

				}
			}
		});

		win.horizFillRadioList = [
			new Ext.form.Radio({ boxLabel: 'Вертикально', name: 'Tablet_IsHorizFill', inputValue: 1, checked: true }),
			new Ext.form.Radio({ boxLabel: 'Горизонтально', name: 'Tablet_IsHorizFill', inputValue: 2 })
		];

		win.horizFillRadio = new Ext.form.RadioGroup({
			fieldLabel: 'Заполнение',
			focusClass: 'x-form-radio-wrap',
			items: win.horizFillRadioList
		});

		win.vertSizeField = new Ext.form.NumberField({
			name: 'Tablet_VertSize',
			fieldLabel: 'Размер по вертикали',
			disabled: true,
			allowBlank: false,
			allowNegative: false,
			maxLength: 3,
			listeners: {
				render: function (field) {
					field.setContainerVisible(!field.hidden);
				}
			}
		});

		win.horizSizeField = new Ext.form.NumberField({
			name: 'Tablet_HorizSize',
			fieldLabel: 'Размер по горизонтали',
			disabled: true,
			allowBlank: false,
			allowNegative: false,
			maxLength: 3,
			listeners: {
				render: function(field) {
					field.setContainerVisible(!field.hidden);
				}
			}
		});

		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=Tablet&m=doSave',
			url: '/?c=Tablet&m=loadEditForm',
			style: 'padding: 0 10px 0 10px;',
			object: 'Tablet',
			labelWidth: 200,
			items: [
				{
					xtype: 'hidden',
					name: 'MedService_id',
				},
				{
					xtype: 'hidden',
					name: 'Tablet_id'
				},
				{
					xtype: 'hidden',
					name: 'Tablet_defectDT'
				},
				{
					xtype: 'hidden',
					name: 'DefectCauseType_id'
				},
				win.analyzerCombo,
				win.methodsIFACombo,
				{
					xtype: 'numberfield',
					fieldLabel: 'Штрих-код планшета',
					name: 'Tablet_Barcode',
					allowBlank: false
				},
				win.tabletTypeCombo,
				win.horizFillRadio,
				win.tabletFillTypeRadio,
				{
					xtype: 'hidden',
					name: 'Tablet_IsDoublesFill',
					value: 1
				},
				{
					xtype: 'hidden',
					name: 'Tablet_IsTestTablet',
					value: 1
				},
				{
					xtype: 'numberfield',
					name: 'Tablet_HoleCount',
					hidden: true,
					allowBlank: false,
					disabled: true,
					minValue: 1,
					maxValue: 12,
					fieldLabel: 'Количество лунок для теста',
					listeners: {
						render: function (field) {
							field.setContainerVisible(!field.hidden);
						}
					}
				},
				win.radioGroup,
				win.vertSizeField,
				win.horizSizeField
			],
			afterLoad: function(data) {
				win.analyzerCombo.fireEventSelect();
				win.methodsIFACombo.setValue(data.MethodsIFA_id);
				win.methodsIFACombo.fireEventSelect();
				win.tabletTypeCombo.setValue(data.MethodsIFATabletType_id);
				win.horizFillRadioList[0].setValue(data.Tablet_IsHorizFill);
				win.tabletFillTypeRadioList[0].setValue(data.fillTypeRadioValue);
				wholeRadio.setValue(data.sizeRadioValue);
				switch (data.sizeRadioValue) {
					case 1:
						wholeRadio.setValue(true);
						win.vertSizeField.disable();
						win.horizSizeField.disable();
						break;
					case 2:
						vertRadio.setValue(true);
						win.vertSizeField.enable();
						win.horizSizeField.disable();
						break;
					case 3:
						horizRadio.setValue(true);
						win.vertSizeField.disable();
						win.horizSizeField.enable();
				}
				win.horizSizeField.setValue(data.Tablet_HorizSize);
				win.vertSizeField.setValue(data.Tablet_VertSize);
			},
			afterSave: function (data) {
				win.hide();
				win.callback(data.Tablet_id);
			},
			beforeSave(params) {
				params.Tablet_HorizSize = win.horizSizeField.getValue();
				params.Tablet_VertSize = win.vertSizeField.getValue();
			}
		});

		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swTabletWindow.superclass.initComponent.apply(this, arguments);
	}
});