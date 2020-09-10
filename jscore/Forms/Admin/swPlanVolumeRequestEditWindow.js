/**
* swPlanVolumeRequestEditWindow - окно просмотра, добавления и редактирования тарифов по бюджету
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      21.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swPlanVolumeRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPlanVolumeRequestEditWindow',
	objectSrc: '/jscore/Forms/Admin/swPlanVolumeRequestEditWindow.js',

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
	id: 'swPlanVolumeRequestEditWindow',
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

		if (base_form.findField('PlanVolumeRequest_begDT').getValue().getTime() > base_form.findField('PlanVolumeRequest_endDT').getValue().getTime()) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата окончания не может быть раньше даты начала.');
			return;
		}

		if (Ext.isEmpty(base_form.findField('PlanVolumeRequest_Num').getValue())) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Не указан номер заявки.');
			return;
		}

		if (base_form.findField('PlanVolumeRequest_Num').disabled) {
			params.PlanVolumeRequest_Num = base_form.findField('PlanVolumeRequest_Num').getValue();
		}

		if (base_form.findField('Lpu_id').disabled) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}

		if (base_form.findField('MedicalCareBudgType_id').disabled) {
			params.MedicalCareBudgType_id = base_form.findField('MedicalCareBudgType_id').getValue();
		}

		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if (base_form.findField('QuoteUnitType_id').disabled) {
			params.QuoteUnitType_id = base_form.findField('QuoteUnitType_id').getValue();
		}

		if (base_form.findField('PlanVolumeRequest_Value').disabled) {
			params.PlanVolumeRequest_Value = base_form.findField('PlanVolumeRequest_Value').getValue();
		}

		if (base_form.findField('PlanVolumeRequest_begDT').disabled) {
			params.PlanVolumeRequest_begDT = base_form.findField('PlanVolumeRequest_begDT').getValue().format('d.m.Y');
		}

		if (base_form.findField('PlanVolumeRequest_endDT').disabled) {
			params.PlanVolumeRequest_endDT = base_form.findField('PlanVolumeRequest_endDT').getValue().format('d.m.Y');
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

				win.callback(win.owner,action.result.PlanVolumeRequest_id);
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
				anchor: '100%',
				fieldLabel: 'МО',
				hiddenName: 'Lpu_id',
				listeners: {
					'change': function(combo, newValue) {
						win.getPlanVolumeRequestNumber();
					}
				},
				xtype: 'swlpucombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Номер',
				disabled: true,
				name: 'PlanVolumeRequest_Num',
				xtype: 'numberfield'
			}, {
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
				name: 'PlanVolumeRequest_Value',
				anchor: '100%',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				name: 'PlanVolumeRequest_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Дата окончания',
				name: 'PlanVolumeRequest_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Примечание',
				name: 'PlanVolumeRequest_Comment',
				anchor: '100%',
				xtype: 'textfield'
			}, {
				name: 'PlanVolumeRequest_id',
				xtype: 'hidden'
			}, {
				name: 'PlanVolumeRequestStatus_id',
				xtype: 'hidden'
			}, {
				name: 'PlanVolumeRequestSourceType_id',
				xtype: 'hidden'
			}, {
				name: 'PlanVolume_id',
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
				{ name: 'PlanVolumeRequest_id', type: 'int' },
				{ name: 'Lpu_id' },
				{ name: 'PlanVolumeRequest_Num' },
				{ name: 'MedicalCareBudgType_id' },
				{ name: 'PayType_id' },
				{ name: 'QuoteUnitType_id' },
				{ name: 'PlanVolumeRequest_Value' },
				{ name: 'PlanVolumeRequest_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'PlanVolumeRequest_endDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'PlanVolumeRequest_Comment' },
				{ name: 'PlanVolumeRequestStatus_id', type: 'int' },
				{ name: 'PlanVolumeRequestSourceType_id', type: 'int' },
				{ name: 'PlanVolume_id', type: 'int' }
			]),
			timeout: 600,
			url: '/?c=PlanVolume&m=savePlanVolumeRequest'
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
		sw.Promed.swPlanVolumeRequestEditWindow.superclass.initComponent.apply(this, arguments);
	},
	onEnableEdit: function(enable) {
		var base_form = this.formPanel.getForm();
		if (!haveArmType('spec_mz')) {
			base_form.findField('Lpu_id').disable();
		}
	},
	getPlanVolumeRequestNumber: function() {
		// получение номера заявки
		var win = this;
		var base_form = this.formPanel.getForm();
		var Lpu_id = base_form.findField('Lpu_id').getValue();

		if (!Ext.isEmpty(Lpu_id)) {
			win.getLoadMask('Получение номера заявки').show();
			Ext.Ajax.request({
				url: '/?c=PlanVolume&m=getPlanVolumeRequestNumber',
				params: {
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							base_form.findField('PlanVolumeRequest_Num').setValue(result.PlanVolumeRequest_Num);
						}
					}
				}
			});
		} else {
			base_form.findField('PlanVolumeRequest_Num').setValue(null);
		}
	},
	show: function() {
		sw.Promed.swPlanVolumeRequestEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.PlanVolume_id = arguments[0].PlanVolume_id || null;

		this.doReset();
		this.center();

		var win = this,
			base_form = this.formPanel.getForm();

		base_form.setValues(arguments[0]);
		
		switch (this.action) {
			case 'view':
				this.setTitle('Плановый объём (заявка): Просмотр');
			break;

			case 'edit':
			case 'decline':
			case 'editrequest':
				this.setTitle('Плановый объём (заявка): Редактирование');
			break;

			case 'add':
			case 'editvolume':
				this.setTitle('Плановый объём (заявка): Добавление');
			break;

			default:
				log('swPlanVolumeRequestEditWindow - action invalid');
				return false;
			break;
		}

		if (this.action == 'add') {
			this.enableEdit(true);
			if (!haveArmType('spec_mz')) {
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());
			}
			base_form.findField('PlanVolumeRequestStatus_id').setValue(1); // новая
			base_form.findField('PlanVolumeRequestSourceType_id').setValue(haveArmType('spec_mz') ? 2 : 1);
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
					PlanVolumeRequest_id: base_form.findField('PlanVolumeRequest_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					win.enableEdit(win.action != 'view');

					switch(win.action) {
						case 'editrequest':
							base_form.findField('Lpu_id').disable();
							base_form.findField('PlanVolumeRequestStatus_id').setValue(2); // на рассмотрении
							break;

						case 'decline':
							base_form.findField('PlanVolumeRequest_Comment').setValue(null);
							base_form.findField('Lpu_id').disable();
							base_form.findField('MedicalCareBudgType_id').disable();
							base_form.findField('PayType_id').disable();
							base_form.findField('QuoteUnitType_id').disable();
							base_form.findField('PlanVolumeRequest_Value').disable();
							base_form.findField('PlanVolumeRequest_begDT').disable();
							base_form.findField('PlanVolumeRequest_endDT').disable();
							base_form.findField('PlanVolumeRequestStatus_id').setValue(4); // отклонена
							break;

						case 'editvolume':
							win.getPlanVolumeRequestNumber();
							base_form.findField('PlanVolumeRequest_id').setValue(null);
							base_form.findField('PlanVolumeRequest_Comment').setValue(null);
							base_form.findField('Lpu_id').disable();
							//base_form.findField('MedicalCareBudgType_id').disable();
							base_form.findField('PayType_id').disable();
							base_form.findField('QuoteUnitType_id').disable();
							base_form.findField('PlanVolumeRequestStatus_id').setValue(1); // новая
							base_form.findField('PlanVolumeRequestSourceType_id').setValue(haveArmType('spec_mz') ? 2 : 1);
							base_form.findField('PlanVolume_id').setValue(win.PlanVolume_id);
							break;
					}
				},
				url: '/?c=PlanVolume&m=loadPlanVolumeRequestEditWindow'
			});
		}
	}
});