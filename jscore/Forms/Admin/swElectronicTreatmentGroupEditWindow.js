/**
 * swElectronicTreatmentGroupEditWindow - группа поводов обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
sw.Promed.swElectronicTreatmentGroupEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'swElectronicTreatmentGroupEditWindow',
	maximizable: false,
	maximized: false,
	layout: 'form',
	modal: true,
	resizable: false,
	title: 'Группа поводов обращений',
	width: 550,

	/* методы */
	doSave: function() {
		var
			params = {},
			wnd = this,
			base_form = wnd.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT,
				icon: Ext.Msg.WARNING,
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(true);
				}
			});

			return false;
		}


		if ( base_form.findField('Lpu_id').disabled ) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result && action.result.ElectronicTreatment_id ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}	
			}.createDelegate(this)
		});

		return true;
	},
	show: function() {
		sw.Promed.swElectronicTreatmentGroupEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = wnd.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		wnd.action = null;
		wnd.callback = Ext.emptyFn;
		wnd.mode = 'SuperAdmin';

		if ( !arguments[0] || typeof arguments[0].formParams != 'object' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: lang['oshibka'],
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				fn: function() {
					wnd.hide();
				}
			});
		}

		var args = arguments[0];
		base_form.reset();

		wnd.setTitle("Группа поводов обращений");

		for ( var field_name in args ) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		base_form.setValues(args.formParams);

		loadMask.show();

		switch ( wnd.action ) {
			case 'add':
				wnd.setTitle(wnd.title + ": Добавление");
				wnd.enableEdit(true);

				if ( wnd.mode == 'SuperAdmin' && isSuperAdmin() ) {
					base_form.findField('Lpu_id').enable();
				}
				else {
					base_form.findField('Lpu_id').disable();
					base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				}

				loadMask.hide();

				if ( !base_form.findField('Lpu_id').disabled ) {
					base_form.findField('Lpu_id').focus(true, 100);
				}
				else {
					base_form.findField('ElectronicTreatment_Code').focus(true, 100);
				}

				base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = args.formParams.Lpu_id
				base_form.findField('LpuBuilding_id').getStore().load();

				break;

			case 'edit':
			case 'view':
				wnd.setTitle(wnd.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.enableEdit(wnd.action == "edit");

				if ( wnd.mode == 'SuperAdmin' && isSuperAdmin() && wnd.action == 'edit' ) {
					base_form.findField('Lpu_id').enable();
				}
				else {
					base_form.findField('Lpu_id').disable();
				}

	
				if (args.formParams.Lpu_id) {
					base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = args.formParams.Lpu_id
					base_form.findField('LpuBuilding_id').getStore().removeAll();
					base_form.findField('LpuBuilding_id').getStore().load({
						callback: function() {
							base_form.findField('LpuBuilding_id').setValue(base_form.findField('LpuBuilding_id').getValue());
						}
					});
				}

				loadMask.hide();
				break;
		}
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			id: wnd.id + 'FormPanel',
			items: [{
				name: 'ElectronicTreatment_id',
				xtype: 'hidden'
			}, {
				name: 'ElectronicTreatmentLevel_id',
				value: 1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				width: 350,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = wnd.FormPanel.getForm();
						if (newValue) {
							base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = newValue;
							base_form.findField('LpuBuilding_id').getStore().removeAll();
							base_form.findField('LpuBuilding_id').getStore().load();
						}
					}
				},
				xtype: 'swlpucombo'
			}, {
				fieldLabel: langs('Подразделение'),
				hiddenName: 'LpuBuilding_id',
				width: 350,
				xtype: 'swlpubuildingcombo'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Код',
				name: 'ElectronicTreatment_Code',
				width: 350,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Наименование',
				name: 'ElectronicTreatment_Name',
				width: 350,
				xtype: 'textfield'
			}, {
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Примечание',
				name: 'ElectronicTreatment_Descr',
				width: 350,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				format: 'd.m.Y',
				name: 'ElectronicTreatment_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания',
				format: 'd.m.Y',
				name: 'ElectronicTreatment_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}],
			labelAlign: 'right',
			labelWidth: 150,
			layout: 'form',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'ElectronicTreatment_id' },
				{ name: 'ElectronicTreatmentLevel_id' },
				{ name: 'Lpu_id' },
				{ name: 'ElectronicTreatment_Code' },
				{ name: 'ElectronicTreatment_Name' },
				{ name: 'ElectronicTreatment_Descr' },
				{ name: 'ElectronicTreatment_begDate' },
				{ name: 'ElectronicTreatment_endDate' }
			]),
			region: 'center',
			url: '/?c=ElectronicTreatment&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					wnd.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swElectronicTreatmentGroupEditWindow.superclass.initComponent.apply(this, arguments);
	}
});