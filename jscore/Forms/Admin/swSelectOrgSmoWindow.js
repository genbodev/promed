/**
* swSelectOrgSmoWindow - окно выбора СМО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2016 Swan Ltd.
*/
/*NO PARSE JSON*/
sw.Promed.swSelectOrgSmoWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSelectOrgSmoWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	closable: false,
	width : 500,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Выбор СМО',
	onSelect: Ext.emptyFn,
	show: function()
	{
		sw.Promed.swSelectOrgSmoWindow.superclass.show.apply(this, arguments);

		if (arguments[0] && arguments[0].onSelect) {
			this.onSelect = arguments[0].onSelect;
		}

		if (arguments[0] && arguments[0].RegistryType_id) {
			this.RegistryType_id = arguments[0].RegistryType_id;
		} else {
			this.RegistryType_id = null;
		}

		this.filterOrgSMOCombo();

		var base_form = this.SelectOrgSmoForm.getForm();
		base_form.reset();
		base_form.findField('OrgSmo_id').focus();
		base_form.findField('Registry_IsZNO').setContainerVisible(this.RegistryType_id && this.RegistryType_id.toString().inlist(['1', '2', '6']));
		this.syncShadow();
	},
	filterOrgSMOCombo: function () {
		var date = new Date();
		var OrgSMOCombo = this.SelectOrgSmoForm.getForm().findField('OrgSmo_id');

		OrgSMOCombo.lastQuery = '';
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function (rec) {
			if (/.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		});
	},
	initComponent: function()
	{
		var win = this;
		this.SelectOrgSmoForm = new Ext.form.FormPanel({
			autoHeight: true,
			layout : 'form',
			border : false,
			frame : true,
			style : 'padding: 10px',
			labelWidth : 120,
			items : [new Ext.ux.Andrie.Select({
				multiSelect: true,
				mode: 'local',
				anchor: '100%',
				fieldLabel: 'СМО',
				displayField: 'OrgSMO_Nick',
				valueField: 'OrgSMO_id',
				allowBlank: false,
				hiddenName: 'OrgSmo_id',
				name: 'OrgSmo_id',
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null && values.OrgSMO_id !=8) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
					'</div></tpl>'),
				minChars: 1,
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.SelectOrgSmoForm.getForm();

						if (combo.getValue().split(',').length > 1) {
							base_form.findField('Registry_IsNotInsur').setValue(0);
							base_form.findField('Registry_IsNotInsur').disable();
						} else {
							base_form.findField('Registry_IsNotInsur').enable();
						}
					}
				},
				store: new Ext.db.AdapterStore({
					dbFile: 'Promed.db',
					tableName: 'OrgSMO',
					key: 'OrgSMO_id',
					autoLoad: false,
					fields: [
						{name: 'OrgSMO_id', type:'int'},
						{name: 'Org_id', type:'int'},
						{name: 'OrgSMO_RegNomC', type:'int'},
						{name: 'OrgSMO_RegNomN', type:'int'},
						{name: 'OrgSMO_Nick', type:'string'},
						{name: 'OrgSMO_isDMS', type:'int'},
						{name: 'KLRgn_id', type:'int'},
						{name: 'OrgSMO_endDate', type: 'string'},
						{name: 'OrgSMO_IsTFOMS', type: 'int'}
					]
				})
			}), {
				xtype: 'checkbox',
				name: 'Registry_IsNotInsur',
				hideLabel: true,
				boxLabel: 'Незастрахованные лица'
			}, {
				xtype: 'checkbox',
				name: 'Registry_IsZNO',
				hideLabel: true,
				boxLabel: 'ЗНО'
			}]
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [ this.SelectOrgSmoForm ],
			buttons : [{
				text : "Выбрать",
				iconCls : 'ok16',
				handler : function(button, event) {
					var base_form = win.SelectOrgSmoForm.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								base_form.findField('OrgSmo_id').focus();
							},
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					var OrgSmo_ids = base_form.findField('OrgSmo_id').getValue();
					win.onSelect({
						OrgSmo_ids: OrgSmo_ids,
						Registry_IsNotInsur: base_form.findField('Registry_IsNotInsur').checked?2:1,
						Registry_IsZNO: base_form.findField('Registry_IsZNO').checked?2:1
					});
					win.hide();
				}.createDelegate(this)
			}, {
				text: '-'
			}, {
				handler: function() {
					win.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swSelectOrgSmoWindow.superclass.initComponent.apply(this, arguments);
	}
});