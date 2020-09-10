/**
 * swLpuBuildingOfficeEditWindow - кабинет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Bykov Stanislav
 * @version     11.2017
 */
sw.Promed.swLpuBuildingOfficeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'swLpuBuildingOfficeEditWindow',
	layout: 'form',
	maximizable: false,
	modal: true,
	resizable: false,
	title: 'Кабинет',
	width: 600,

	/* методы */
	doSave: function() {
		var wnd = this,
			base_form = this.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		if ( !base_form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var params = {
			Lpu_id: base_form.findField('Lpu_id').getValue()
		};

		if ( base_form.findField('LpuBuilding_id').disabled ) {
			params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
		}

		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					wnd.hide();
					wnd.callback();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}
			}.createDelegate(this)
		});

		return true;
	},
	show: function() {
		sw.Promed.swLpuBuildingOfficeEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = this.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		wnd.action = null;
		wnd.callback = Ext.emptyFn;

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

			return false;
		}

		wnd.setTitle("Кабинет");

		var args = arguments[0];
		base_form.reset();

		for ( var field_name in args ) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		loadMask.show();

		base_form.findField('LpuBuildingOffice_begDate').setMaxValue(undefined);
		base_form.findField('LpuBuildingOffice_endDate').setMinValue(undefined);

		swLpuBuildingGlobalStore.clearFilter();
		swLpuBuildingGlobalStore.filterBy(function(rec) {
			return (rec.get('Lpu_id') == getGlobalOptions().lpu_id);
		});
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		base_form.setValues(args.formParams);

		switch ( wnd.action ) {
			case 'add':
				wnd.setTitle(wnd.title + ": Добавление");
				wnd.enableEdit(true);
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				break;

			case 'edit':
			case 'view':
				wnd.setTitle(wnd.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.enableEdit(wnd.action == "edit");

				if ( wnd.msfCount > 0 ) {
					base_form.findField('LpuBuilding_id').disable();
				}
				break;
		}

		base_form.findField('Lpu_id').disable();
		//base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());

		loadMask.hide();

		if ( wnd.action == 'view' ) {
			wnd.buttons[wnd.buttons.length - 1].focus();
		}
		else {
			base_form.findField('LpuBuilding_id').focus(true, 100);
		}
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			items: [{
				name: 'LpuBuildingOffice_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				hiddenName: 'Lpu_id',
				width: 350,
				xtype: 'swlpulocalcombo'
			}, {
				allowBlank: false,
				hiddenName: 'LpuBuilding_id',
				lastQuery: '',
				listWidth: 600,
				width: 350,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 10,
					tag: "input"
				},
				fieldLabel: 'Номер',
				name: 'LpuBuildingOffice_Number',
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 100,
					tag: "input"
				},
				fieldLabel: 'Наименование',
				name: 'LpuBuildingOffice_Name',
				width: 350,
				xtype: 'textfield'
			}, {
				autoCreate: {
					autocomplete: "off",
					maxLength: 200,
					tag: "input"
				},
				fieldLabel: 'Комментарий',
				name: 'LpuBuildingOffice_Comment',
				width: 350,
				xtype: 'textfield'
			}, {
				autoHeight: true,
				layout: 'form',
				style: 'padding-left: 0px;',
				title: 'Период действия',
				xtype: 'fieldset',
				items: [{
					allowBlank: false,
					fieldLabel: 'Дата начала',
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = wnd.FormPanel.getForm();

							if ( !Ext.isEmpty(newValue) ) {
								base_form.findField('LpuBuildingOffice_endDate').setMinValue(Ext.util.Format.date(newValue, 'd.m.Y'));
							}
							else {
								base_form.findField('LpuBuildingOffice_endDate').setMinValue(undefined);
							}
						}
					},
					name: 'LpuBuildingOffice_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Дата окончания',
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = wnd.FormPanel.getForm();

							if ( !Ext.isEmpty(newValue) ) {
								base_form.findField('LpuBuildingOffice_begDate').setMaxValue(Ext.util.Format.date(newValue, 'd.m.Y'));
							}
							else {
								base_form.findField('LpuBuildingOffice_begDate').setMaxValue(undefined);
							}
						}
					},
					name: 'LpuBuildingOffice_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}]
			}],
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 200,
			region: 'north',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'LpuBuildingOffice_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuBuildingOffice_Number' },
				{ name: 'LpuBuildingOffice_Name' },
				{ name: 'LpuBuildingOffice_Comment' },
				{ name: 'LpuBuildingOffice_begDate' },
				{ name: 'LpuBuildingOffice_endDate' }
			]),
			url: '/?c=LpuBuildingOffice&m=save'
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
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				wnd.FormPanel
			]
		});

		sw.Promed.swLpuBuildingOfficeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});