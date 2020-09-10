/**
 * swPersonDopDispPlanExportWindow - окно настроек экспорта данных плана профилактического мероприятия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swPersonDopDispPlanExportTfomsWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonDopDispPlanExportTfomsWindow',
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanExportTfomsWindow.js',
	closable: false,
	width : 500,
	height : 200,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Отправка в ТФОМС плана профилактического мероприятия',
	params: null,
	callback: Ext.emptyFn,

	doExport: function() {
		var win = this;
		var form = this.findById('PersonDopDispPlanExportForm');
		var base_form = form.getForm();

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

		win.getLoadMask('Выполняется экспорт...').show();
		base_form.submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide();
			},
			params: {
				PersonDopDispPlan_ids: Ext.util.JSON.encode(win.PersonDopDispPlan_ids),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				DispCheckPeriod_id: base_form.findField('DispCheckPeriod_id').getValue()
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();
				win.callback();
				win.hide();
			}
		});
	},

	filterOrgSMOCombo: function() {
		var OrgSMOCombo = this.findById('PersonDopDispPlanExportForm').getForm().findField('OrgSMO_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getRegionNumber());
		});
		OrgSMOCombo.lastQuery = '';
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getRegionNumber());
		});
	},

	show: function() {
		sw.Promed.swPersonDopDispPlanExportTfomsWindow.superclass.show.apply(this, arguments);
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		var win = this;
		var form = this.findById('PersonDopDispPlanExportForm').getForm();
		form.reset();
		this.filterOrgSMOCombo();

		if (arguments[0]['DispClass_id']) {
			this.DispClass_id = arguments[0]['DispClass_id'];
		} else {
			this.DispClass_id = null;
		}

		if (arguments[0]['PersonDopDispPlan_ids']) {
			this.PersonDopDispPlan_ids = arguments[0]['PersonDopDispPlan_ids'];
		} else {
			this.PersonDopDispPlan_ids = null;
		}

		form.findField('DispClass_id').setContainerVisible(getRegionNick() != 'astra');
		form.findField('DispClass_id').setValue(this.DispClass_id);
		form.findField('DispClass_id').fireEvent('change', form.findField('DispClass_id'), form.findField('DispClass_id').getValue());

		form.findField('DispCheckPeriod_id').getStore().baseParams = {
			PersonDopDispPlan_ids: Ext.util.JSON.encode(this.PersonDopDispPlan_ids),
			DispClass_id: this.DispClass_id
		};
		form.findField('DispCheckPeriod_id').getStore().load({
			callback: function() {
				var dcp_combo = form.findField('DispCheckPeriod_id');
				var record = dcp_combo.getStore().getAt(0);
				if (record) {
					dcp_combo.setValue(record.get('DispCheckPeriod_id'));
					dcp_combo.fireEvent('change', dcp_combo, dcp_combo.getValue());
				}
			}
		});
		win.syncSize();
		win.syncShadow();
	},

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;

		win.FormPanel = new Ext.form.FormPanel({
			id : 'PersonDopDispPlanExportForm',
			url: '/?c=TFOMSAutoInteract&m=publicateDopDispPlan',
			timeout: 1800,
			layout : 'form',
			autoHeight: true,
			border : false,
			frame : true,
			bodyStyle : 'padding: 5px',
			labelWidth : 1,
			items : [{
				style : 'padding-left: 5px',
				layout : 'form',
				labelWidth : 100,
				labelAlign : 'right',
				items: [{
					anchor: '100%',
					comboSubject: 'DispClass',
					hiddenName: 'DispClass_id',
					fieldLabel: 'Тип',
					lastQuery: '',
					typeCode: 'int',
					xtype: 'swcommonsprcombo',
					disabled: true
				}, {
					anchor: '100%',
					hiddenName: 'DispCheckPeriod_id',
					fieldLabel: 'Период плана',
					lastQuery: '',
					typeCode: 'int',
					id: 'pddpePeriod_id',
					xtype: 'swbaselocalcombo',
					store: new Ext.data.JsonStore({
						key: 'DispCheckPeriod_id',
						autoLoad: false,
						fields: [
							{name:'DispCheckPeriod_id',type: 'int'},
							{name:'PeriodCap_id', type: 'int'},
							{name:'DispCheckPeriod_Year', type: 'int'},
							{name:'DispCheckPeriod_Name', type: 'string'}
						],
						url: '/?c=PersonDopDispPlan&m=getDispCheckPeriod'
					}),
					valueField: 'DispCheckPeriod_id',
					displayField: 'DispCheckPeriod_Name',
					disabled: true
				}, new Ext.ux.Andrie.Select({
					hideLabel: getRegionNick() == 'ekb',
					hidden: getRegionNick() == 'ekb',
					clearBaseFilter: function() {
						this.baseFilterFn = null;
						this.baseFilterScope = null;
					},
					setBaseFilter: function(fn, scope) {
						this.baseFilterFn = fn;
						this.baseFilterScope = scope || this;
						this.store.filterBy(fn, scope);
					},
					multiSelect: true,
					mode: 'local',
					anchor: '100%',
					fieldLabel: langs('СМО'),
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
					}),
					displayField: 'OrgSMO_Nick',
					valueField: 'OrgSMO_id',
					hiddenName: 'OrgSMO_id',
					tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
						'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
						'</div></tpl>')
				}), {
					allowBlank: false,
					width: 100,
					name: 'PacketNumber',
					fieldLabel: 'Порядковый номер пакета',
					autoCreate: {tag: "input", maxLength: (getRegionNick().inlist([ 'khak' ]) ? "2" : getRegionNick().inlist([ 'perm', 'ekb' ]) ? "1" : null), autocomplete: "off"}, // надо с этой жутью что-то сделать
					allowDecimals: false,
					allowNegative: false,
					xtype: 'numberfield'
				}]
			}]
		});

		Ext.apply(this, {
			items: [
				win.FormPanel
			],
			buttons: [{
				text : 'Сформировать',
				iconCls : 'ok16',
				handler : function(button, event) {
					win.doExport();
				}.createDelegate(this)
			}, {
				text: '-'
			}, HelpButton(this), {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swPersonDopDispPlanExportTfomsWindow.superclass.initComponent.apply(this, arguments);
	}
});