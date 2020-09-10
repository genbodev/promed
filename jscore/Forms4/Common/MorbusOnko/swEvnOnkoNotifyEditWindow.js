/**
 * swEvnOnkoNotifyEditWindow - Извещение на онкобольного
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @comment
 */

Ext6.define('common.MorbusOnko.swEvnOnkoNotifyEditWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnOnkoNotifyEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnOnkoNotifyeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Извещение',
	width: 700,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this,
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);

		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		var date = base_form.findField('EvnOnkoNotify_setDT').getValue();
		params.EvnOnkoNotify_setDT = Ext6.util.Format.date(date,'d.m.Y');
		
		base_form.submit({
			params: params,
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) || !action.result.EvnOnkoNotify_id ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}
				
				var data = action.result;
				var EvnOnkoNotify_id = action.result.EvnOnkoNotify_id;
				win.printNotification(EvnOnkoNotify_id);
				win.callback(data);
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	
	printNotification: function(EvnOnkoNotify_id) {
		if ( !EvnOnkoNotify_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'OnkoNotify.rptdesign',
			'Report_Params': '&paramEvnOnkoNotify=' + EvnOnkoNotify_id,
			'Report_Format': 'pdf'
		});
	},
	
	show: function() {
		this.callParent(arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();

		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
                    win.hide();
				}
			});
            return false;
		}
		
		this.focus();
		this.center();
		
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		
		if (arguments[0].EvnOnkoNotify_id) 
			this.EvnOnkoNotify_id = arguments[0].EvnOnkoNotify_id;
		else 
			this.EvnOnkoNotify_id = null;
			
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			if ( ( this.EvnOnkoNotify_id ) && ( this.EvnOnkoNotify_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		base_form.findField('Lpu_sid').getStore().load({
			params: { object: 'Lpu' },
			callback: function () {
				var combo = base_form.findField('Lpu_sid');
				combo.getStore().clearFilter();
				combo.lastQuery = '';
				combo.getStore().filterBy(function(record) {
					// #15891
					var is_onko = false;
					switch (getGlobalOptions().region && getGlobalOptions().region.nick) {
						case 'ufa':
							is_onko = ( record.get('LpuType_Code') == 43 );
							break;
						case 'tambov': // тамбов - уфимский справочник
							is_onko = ( record.get('LpuType_Code') == 71 );
							break;
						default: // пермский справочник
							is_onko = ( record.get('LpuType_Code').toString().inlist(['30','43']) );
							break;
					}
					return is_onko;
				});
				if ( base_form.findField('Lpu_sid').getStore().getCount() == 1 && win.action == 'add' ) {
					combo.setValue(combo.getStore().getAt(0).get('Lpu_id'))
				}
			}.createDelegate(this)
		});

		if (this.action != 'add') {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                msg: langs('Ошибка открытия формы.<br/>Форма может быть открыта только для создания извещения!'),
                title: langs('Ошибка'),
                fn: function() {
                    win.hide();
                }
            });
            return false;
		}
		
		var medstafffact_filter_params = {
			medPersonalIdList: [base_form.findField('MedPersonal_id').getValue()]
		};

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedPersonal_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
		base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});
		
        base_form.findField('EvnOnkoNotify_setDT').setValue(getGlobalOptions().date);
		
		this.setTitle(langs('Извещение: Добавление'));
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model'
		});
		
		win.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanelShort', {
			region: 'north',
			addToolbar: false,
			bodyPadding: '3 20 0 25',
			border: false,
			style: 'border-bottom: 1px solid #d0d0d0;',
			ownerWin: this
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnOnkoNotify&m=save',
			items: [{
				name: 'EvnOnkoNotify_id',
				xtype: 'hidden'
			}, {
				name: 'EvnOnkoNotify_pid',
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Diag_id',
				xtype: 'hidden'
			}, {
				width: 600,
				allowBlank: false,
				fieldLabel: langs('Направить извещение'),
				name: 'Lpu_sid',
				triggerAction: 'all',
				valueField: 'Lpu_id',
				displayField: 'Lpu_Nick',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'Lpu_id', type: 'int'},
						{name: 'Lpu_Nick', type: 'string'},
						{name: 'LpuType_Code', type: 'int'}
					],
					proxy: {
						type: 'ajax',
						actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url: C_GETOBJECTLIST,
						reader: {type: 'json'}
					}
				},
				listeners: {
					select: function() {
						
					},
					render: function() {
						
					}
				},
				xtype: 'baseCombobox'
			}, {
				allowBlank: false,
				disabled: true,
				fieldLabel: langs('Дата заполнения извещения'),
				name: 'EvnOnkoNotify_setDT',
				xtype: 'datefield'
			}, {
				changeDisabled: false,
				disabled: true,
				fieldLabel: langs('Врач, заполнивший извещение'),
				name: 'MedPersonal_id',
				xtype: 'swMedStaffFactCombo',
				valueField: 'MedPersonal_id',
				listWidth: 750,
				width: 600
			}]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel,
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				text: 'Сохранить и напечатать', 
				handler:function () {
					win.save();
				}
			}]
		});

		this.callParent(arguments);
    }
});