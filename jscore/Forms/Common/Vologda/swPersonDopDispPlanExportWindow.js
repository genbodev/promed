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
 
sw.Promed.swPersonDopDispPlanExportWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonDopDispPlanExportWindow',
	objectSrc: '/jscore/Forms/Common/Vologda/swPersonDopDispPlanExportWindow.js',
	closable: false,
	width : 550,
	height : 200,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Экспорт данных планов профилактических мероприятий',
	params: null,
	callback: Ext.emptyFn,
	doExport: function(params) {
		var win = this;
		var form = this.findById('PersonDopDispPlanExportForm');
		var base_form = form.getForm();
		
		params = params || {};

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
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result.Error_Msg == 'YesNo' ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								params.ignoreCheck = 2;
								win.doExport(params);
							}
						},
						msg: action.result.Alert_Msg,
						title: 'Внимание'
					});
					return false;
				}

				win.getPacketNumber();

				if (action.result && action.result.link) {
					if (action.result.link.length == 1) {
						sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a target="_blank" href="' + action.result.link + '">Скачать и сохранить архив экспорта</a>');
					} else {
						var files = '';
						for(var i=0; i < action.result.link.length; i++) {
							var id_salt = Math.random();
							var win_id = 'exp' + Math.floor(id_salt * 10000);
							files += 'var '+win_id+' = window.open(\'' + action.result.link[i] + '\'); window.setTimeout(function(){ '+win_id+'.close()}, 100);';
						}
						sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a href="#" onclick="' + files + 'return false;">Скачать и сохранить файлы экспорта</a>');
					}
					win.callback();
				} else {
					sw.swMsg.alert(lang['oshibka'], 'При экспорте данных произошла ошибка');
				}
			}
		});
	},
	getPacketNumber: function() {
		if (getRegionNick() != 'vologda') {
			return;
		}

		var win = this;
		var base_form = this.findById('PersonDopDispPlanExportForm').getForm();

		base_form.findField('PacketNumber').setValue('');
		if (!Ext.isEmpty(base_form.findField('DispCheckPeriod_id').getValue())) {
			win.getLoadMask('Получение порядкового номера пакета').show();
			Ext.Ajax.request({
				url: '/?c=PersonDopDispPlan&m=getPersonDopDispPlanExportPackNum',
				params: {
					PersonDopDispPlanExport_Year: base_form.findField('PersonDopDispPlanExport_Year').getValue()
				},
				callback: function (options, success, response) {
					if (success) {
						win.getLoadMask().hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.PacketNumber) {
							base_form.findField('PacketNumber').setValue(result.PacketNumber);
						}
					}
				}.createDelegate(this)
			});
		}
	},
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swPersonDopDispPlanExportWindow.superclass.show.apply(this, arguments);		
		if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        } else {
			this.callback = Ext.emptyFn;
		}

		var win = this;
		var form = this.findById('PersonDopDispPlanExportForm').getForm();
		form.reset();
		this.filterOrgSMOCombo();

		if (arguments[0]['PersonDopDispPlan_Year']) {
			form.findField('PersonDopDispPlanExport_Year').setValue(arguments[0]['PersonDopDispPlan_Year']);
		}
		
		if (arguments[0]['PersonDopDispPlan_ids']) {
			this.PersonDopDispPlan_ids = arguments[0]['PersonDopDispPlan_ids'];
		} else {
			this.PersonDopDispPlan_ids = null;
		}

		form.findField('DispCheckPeriod_id').getStore().load({
			callback: function() {
				win.filterDispCheckPeriod();
			}
		});
		win.syncSize();
		win.syncShadow();
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
	filterDispCheckPeriod: function() {
		var form = this.findById('PersonDopDispPlanExportForm').getForm(),
			year = form.findField('PersonDopDispPlanExport_Year').getValue(),
			dcp_combo = form.findField('DispCheckPeriod_id'),
			dcp_value = dcp_combo.getValue();

		dcp_combo._filterYear = year;
		dcp_combo.setDisabled(!year);
		
		if (!year) {
			dcp_combo.clearValue();
			dcp_combo.fireEvent('change', dcp_combo, '');
			return false;
		}

		dcp_combo.lastQuery = '';
		dcp_combo.getStore().clearFilter();
		if (!!year) {
			dcp_combo.getStore().filterBy(function(rec) {
				return (rec.get('DispCheckPeriod_Year') == year);
			});
			if (!dcp_combo.findRecord('DispCheckPeriod_id', dcp_value)) {
				dcp_combo.clearValue();
				dcp_combo.fireEvent('change', dcp_combo, '');
			}
		}
	},

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this,
			year_store = [];
		for ( var i = 2017; i <= 2099; i++ ) {
			year_store.push([i, String(i)]);
		}

    	Ext.apply(this, {
			items: [new Ext.form.FormPanel({
				id : 'PersonDopDispPlanExportForm',
				url: '/?c=PersonDopDispPlan&m=exportPersonDopDispPlan',
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
						xtype: 'swbaselocalcombo',
						fieldLabel: 'Год',
						triggerAction: 'all',
						hiddenName: 'PersonDopDispPlanExport_Year',
						width: 100,
						store: year_store,
						listeners: {
							'select': function() {
								win.filterDispCheckPeriod();
							},
							'change': function() {
								win.filterDispCheckPeriod();
							}
						}
					}, {
						anchor: '100%',
						hiddenName: 'DispCheckPeriod_id',
						fieldLabel: 'Период',
						lastQuery: '',
						typeCode: 'int',
						id: 'pddpePeriod_id',
						xtype: 'swbaselocalcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return rec.get(combo.valueField) == newValue;
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function(combo, record, index) {
								var form = win.findById('PersonDopDispPlanExportForm').getForm(),
									IsExportPeriod = form.findField('PersonDopDispPlanExport_IsExportPeriod');
								IsExportPeriod.setDisabled(!record || record.get('PeriodCap_id') != 4);
								if (!record || record.get('PeriodCap_id') != 4) {
									IsExportPeriod.setValue(false);
								}
								win.getPacketNumber();
							}
						},
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
						displayField: 'DispCheckPeriod_Name'
					}, {
						xtype: 'swcheckbox',
						name: 'PersonDopDispPlanExport_IsExportPeriod',
						boxLabel: 'Выгрузить за период, начиная с выбранного месяца до конца года',
						labelSeparator: ''
					}, new Ext.ux.Andrie.Select({
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
						fieldLabel: lang['smo'],
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
						readOnly: true,
						fieldLabel: 'Порядковый номер пакета',
						autoCreate: {tag: "input", maxLength: 1, autocomplete: "off"},
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}]
				}]
			})],
			buttons : [{
				text : 'Сформировать',
				iconCls : 'ok16',
				handler : function(button, event) {							
					win.doExport();
				}.createDelegate(this)
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});
		sw.Promed.swPersonDopDispPlanExportWindow.superclass.initComponent.apply(this, arguments);

		setTimeout(() =>
			{
				var dispCheckPeriodId = this.findById('PersonDopDispPlanExportForm').getForm().findField('DispCheckPeriod_id');
				dispCheckPeriodId.setBaseFilter(this._checkPeriodBaseFilter, dispCheckPeriodId);
			}, 1);
	}, //end initComponent()

	_checkPeriodBaseFilter: function(record)
	{
		return (record.get('DispCheckPeriod_Year') == this._filterYear);
	}
});