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
	objectSrc: '/jscore/Forms/Common/swPersonDopDispPlanExportWindow.js',
	closable: false,
	width : 500,
	height : 200,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Экспорт данных плана профилактического мероприятия',
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
		
		if (arguments[0]['DispClass_id']) {
			this.DispClass_id = arguments[0]['DispClass_id'];
		} else {
			this.DispClass_id = null;
		}

		if (arguments[0]['PersonDopDispPlan_Year']) {
			form.findField('PersonDopDispPlanExport_Year').setValue(arguments[0]['PersonDopDispPlan_Year']);
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

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;

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
						listeners: {
							'change': function(combo, newValue, oldValue) {
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
					}),	{
						xtype: 'fieldset',
						autoHeight: true,
						hidden: !getRegionNick().inlist(['perm']),
						title: 'Отчетный период',
						layout: 'column',
						items: [{
							layout:'form',
							labelWidth: 90,
							width: 200,
							items: [{
								allowBlank: !getRegionNick().inlist(['perm']),
								fieldLabel: 'Месяц',
								anchor: '100%',
								triggerAction: 'all',
								store: [
									[1, lang['yanvar']],
									[2, lang['fevral']],
									[3, lang['mart']],
									[4, lang['aprel']],
									[5, lang['may']],
									[6, lang['iyun']],
									[7, lang['iyul']],
									[8, lang['avgust']],
									[9, lang['sentyabr']],
									[10, lang['oktyabr']],
									[11, lang['noyabr']],
									[12, lang['dekabr']]
								],
								hiddenName: 'PersonDopDispPlanExport_Month',
								xtype: 'combo'
							}]
						}, {
							layout:'form',
							labelWidth: 50,
							items: [{
								xtype: 'numberfield',
								fieldLabel: 'Год',
								name: 'PersonDopDispPlanExport_Year',
								allowDecimals: false,
								allowNegative: false,
								allowBlank: !getRegionNick().inlist(['perm']),
								width: 70,
								plugins: [new Ext.ux.InputTextMask('9999', false)],
								minLength: 4
							}]
						}]
					}, {
						allowBlank: !getRegionNick().inlist([ 'ekb' ]),
						hideLabel: !getRegionNick().inlist([ 'perm', 'ekb' ]),
						hidden: !getRegionNick().inlist([ 'perm', 'ekb' ]),
						fieldLabel: 'Квартал загрузки',
						width: 100,
						triggerAction: 'all',
						store: [
							[1,'1 квартал'],
							[2,'2 квартал'],
							[3,'3 квартал'],
							[4,'4 квартал']
						],
						hiddenName: 'PersonDopDispPlanExport_Quart',
						xtype: 'combo'
					}, {
						allowBlank: false,
						width: 100,
						name: 'PacketNumber',
						readOnly: getRegionNick() == 'vologda',
						fieldLabel: 'Порядковый номер пакета',
						autoCreate: {tag: "input", maxLength: (getRegionNick().inlist([ 'khak' ]) ? "2" : getRegionNick().inlist([ 'perm', 'ekb', 'vologda' ]) ? "1" : null), autocomplete: "off"}, // надо с этой жутью что-то сделать
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
	} //end initComponent()
});