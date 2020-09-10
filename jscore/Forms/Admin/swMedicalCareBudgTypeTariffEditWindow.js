/**
* swMedicalCareBudgTypeTariffEditWindow - окно просмотра, добавления и редактирования тарифов по бюджету
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swMedicalCareBudgTypeTariffEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedicalCareBudgTypeTariffEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedicalCareBudgTypeTariffEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swMedicalCareBudgTypeTariffEditWindow',
	width: 450,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}

		if (base_form.findField('MedicalCareBudgTypeTariff_begDT').getValue().getTime() > base_form.findField('MedicalCareBudgTypeTariff_endDT').getValue().getTime()) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата окончания не может быть раньше даты начала.');
			return;
		}

		win.getLoadMask('Подождите, сохраняется запись...').show();
		base_form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();

				win.callback(win.owner,action.result.MedicalCareBudgTypeTariff_id);
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [{
				allowBlank: false,
				anchor: '100%',
				comboSubject: 'MedicalCareBudgType',
				ctxSerach: true,
				editable: true,
				enableKeyEvents: true,
				fieldLabel: 'Тип мед. помощи',
				hiddenName: 'MedicalCareBudgType_id',
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				fieldLabel: 'МО',
				hiddenName: 'Lpu_id',
				xtype: 'swlpucombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Вид оплаты',
				hiddenName: 'PayType_id',
				loadParams: {
					params: {where: getRegionNick() == 'kareliya' ? " where PayType_SysNick in ('bud', 'fbud', 'subrf')" : " where PayType_SysNick in ('bud', 'fbud')"}
				},
				xtype: 'swpaytypecombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				comboSubject: 'QuoteUnitType',
				fieldLabel: 'Единица измерения',
				hiddenName: 'QuoteUnitType_id',
				loadParams: {
					params: {where: " where QuoteUnitType_Code in (1, 2, 3)"}
				},
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Значение',
				name: 'MedicalCareBudgTypeTariff_Value',
				anchor: '100%',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				name: 'MedicalCareBudgTypeTariff_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Дата окончания',
				name: 'MedicalCareBudgTypeTariff_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				name: 'MedicalCareBudgTypeTariff_id',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave();
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'MedicalCareBudgTypeTariff_id', type: 'int' },
				{ name: 'MedicalCareBudgType_id' },
				{ name: 'Lpu_id' },
				{ name: 'PayType_id' },
				{ name: 'QuoteUnitType_id' },
				{ name: 'MedicalCareBudgTypeTariff_Value' },
				{ name: 'MedicalCareBudgTypeTariff_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'MedicalCareBudgTypeTariff_endDT', type: 'date', dateFormat: 'd.m.Y' }
			]),
			timeout: 600,
			url: '/?c=LpuPassport&m=saveMedicalCareBudgTypeTariff'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swMedicalCareBudgTypeTariffEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swMedicalCareBudgTypeTariffEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.doReset();
		this.center();

		var win = this,
			base_form = this.formPanel.getForm();

		base_form.setValues(arguments[0]);
		
		switch (this.action) {
			case 'view':
				this.setTitle('Тариф (бюджет): Просмотр');
			break;

			case 'edit':
				this.setTitle('Тариф (бюджет): Редактирование');
			break;

			case 'add':
				this.setTitle('Тариф (бюджет): Добавление');
			break;

			default:
				log('swMedicalCareBudgTypeTariffEditWindow - action invalid');
				return false;
			break;
		}

		if (this.action == 'add') {
			this.enableEdit(true);
		} else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() {
						win.hide();
					});
				},
				params: {
					MedicalCareBudgTypeTariff_id: base_form.findField('MedicalCareBudgTypeTariff_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					win.enableEdit(win.action == 'edit');
				},
				url: '/?c=LpuPassport&m=loadMedicalCareBudgTypeTariffEditWindow'
			});
		}
	}
});