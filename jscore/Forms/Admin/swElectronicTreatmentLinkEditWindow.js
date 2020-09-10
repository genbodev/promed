/**
 * swElectronicTreatmentLinkEditWindow - окно редактирования назначения очередей на повод обращения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Bykov Stanislav
 * @version      08.2017
 * @comment
 */
sw.Promed.swElectronicTreatmentLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	id: 'ElectronicTreatmentLinkEditWindow',
	layout: 'form',
	modal: true,
	resizable: false,
	width: 680,

	/* методы */
	doSave:  function() {
		var wnd = this,
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

        var params = base_form.getValues(),
            combo = base_form.findField('ElectronicQueueInfo_id'),
            eqi_data = combo.getSelectedRecordData();

        params.ElectronicQueueInfo_Code = eqi_data.ElectronicQueueInfo_Code;
        params.ElectronicQueueInfo_Name = eqi_data.ElectronicQueueInfo_Name;
        params.LpuBuilding_Name = eqi_data.LpuBuilding_Name;
        params.LpuSection_Name = eqi_data.LpuSection_Name;
        params.MedService_Name = eqi_data.MedService_Name;
        params.ElectronicQueueInfo_begDate = eqi_data.ElectronicQueueInfo_begDate;
        params.ElectronicQueueInfo_endDate = eqi_data.ElectronicQueueInfo_endDate;

        if ( wnd.callback(params) == true ) {
			wnd.hide();
		}

		return true;
	},
	show: function() {
		sw.Promed.swElectronicTreatmentLinkEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = wnd.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		wnd.action = null;
		wnd.LpuBuilding_id = null;
		wnd.callback = Ext.emptyFn;

		if ( !arguments[0] ) {
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

		if (args.LpuBuilding_id) {
			this.LpuBuilding_id = args.LpuBuilding_id;
		}

		wnd.setTitle("Назначение электронной очереди");

		for ( var field_name in args ) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		base_form.setValues(args);

		loadMask.show();

		var combo = base_form.findField('ElectronicQueueInfo_id'),
			Lpu_id = base_form.findField('Lpu_id').getValue();

		combo.getStore().baseParams.Lpu_id = Lpu_id;
		combo.getStore().baseParams.LpuBuilding_id = wnd.LpuBuilding_id ? wnd.LpuBuilding_id : null;

		combo.getStore().load({
			callback: function(){
				switch ( wnd.action ) {
					case 'add':
						wnd.setTitle(wnd.title + ": Добавление");
						loadMask.hide();
						break;
				}
			}
		});
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: '{padding: 15px 0px 15px 0px;}',
			border: false,
			bodyBorder: false,
			frame: true,
			id: wnd.id + 'FormPanel',
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			url:'/?c=ElectronicTreatment&m=saveElectronicTreatmentLink',

			items: [{
				xtype: 'hidden',
				name: 'ElectronicTreatmentLink_id'
			}, {
				xtype: 'hidden',
				name: 'Lpu_id'
			}, {
				allowBlank: false,
				displayField: 'ElectronicQueueInfo_Name',
				fieldLabel: 'Наименование',
				hiddenName: 'ElectronicQueueInfo_id',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'ElectronicQueueInfo_id', mapping: 'ElectronicQueueInfo_id' },
						{ name: 'ElectronicQueueInfo_Name', mapping: 'ElectronicQueueInfo_Name' },
						{ name: 'ElectronicQueueInfo_Code', mapping: 'ElectronicQueueInfo_Code' },
						{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
						{ name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' },
						{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
						{ name: 'MedService_Name', mapping: 'MedService_Name' },
						{ name: 'ElectronicQueueInfo_begDate', mapping: 'ElectronicQueueInfo_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'ElectronicQueueInfo_endDate', mapping: 'ElectronicQueueInfo_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					key: 'ElectronicQueueInfo_id',
					sortInfo: { field: 'ElectronicQueueInfo_Name' },
					url:'/?c=ElectronicTreatment&m=loadElectronicQueueInfoCombo',
					listeners: {
						'load': function(store) {
							var base_form = wnd.FormPanel.getForm();
							if (wnd.LpuBuilding_id) {
								base_form.findField('ElectronicQueueInfo_id').lastQuery = '';
								store.clearFilter();
								store.filterBy(function(rec) {
									return !rec.get('LpuBuilding_id') || rec.get('LpuBuilding_id') == wnd.LpuBuilding_id;
								});
							}
						}
					}
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{ElectronicQueueInfo_Code}</font>&nbsp;{ElectronicQueueInfo_Name}',
					'</div></tpl>'
				),
				valueField: 'ElectronicQueueInfo_id',
				width: 475,
				xtype: 'swbaselocalcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMADD
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
				wnd.FormPanel
			]
		});

		sw.Promed.swElectronicTreatmentLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});