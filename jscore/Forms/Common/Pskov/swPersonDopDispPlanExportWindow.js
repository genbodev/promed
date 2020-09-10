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

sw.Promed.swPersonDopDispPlanExportWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	border: false,
	closable: false,
	closeAction: 'hide',
	height: 200,
	modal: true,
	params: null,
	plain : false,
	resizable: false,
	title: langs('Экспорт планов профилактических мероприятий'),
	width: 500,

	/* методы */
	callback: Ext.emptyFn,
	doExport: function() {
		var
			win = this,
			form = win.FormPanel,
			base_form = form.getForm();

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
				PersonDopDispPlan_ids: Ext.util.JSON.encode(win.PersonDopDispPlan_ids)
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result ) {
					if(!action.result.count || action.result.count==0 || !action.result.link) {
						sw.swMsg.alert('Результат', 'По данному запросу нет данных');
					} else {
						if ( action.result.count == 1 ) {
							sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a target="_blank" href="' + action.result.link + '">Скачать и сохранить архив экспорта</a>');
						} else {
							var links = action.result.link.split('|');
							var msg = '';
							for(i=0; i<links.length; i++) {
								filename = links[i].match( /D\d+\.zip/ );
								msg += '<br/><a target="_blank" href="' + links[i] + '">Скачать ' + filename[0] + '</a>';
							}
							sw.swMsg.alert('Результат', 'Экспорт успешно завершён'+msg);
						}
					}

					win.callback();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), 'При экспорте данных произошла ошибка');
				}
			}
		});
	},
	filterOrgSMOCombo: function() {
		var OrgSMOCombo = this.FormPanel.getForm().findField('OrgSMO_id');
		
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
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swPersonDopDispPlanExportWindow.superclass.show.apply(this, arguments);		

		var
			win = this,
			base_form = win.FormPanel.getForm();

		base_form.reset();

		win.callback = Ext.emptyFn;
		win.PersonDopDispPlan_ids = null;

		if ( typeof arguments[0].callback == 'function' ) {
			win.callback = arguments[0].callback;
		}

		if ( arguments[0]['PersonDopDispPlan_ids'] ) {
			win.PersonDopDispPlan_ids = arguments[0]['PersonDopDispPlan_ids'];
		}

		if ( arguments[0]['PersonDopDispPlan_Year'] ) {
			base_form.findField('PersonDopDispPlanExport_Year').setValue(arguments[0]['PersonDopDispPlan_Year']);
		}

		win.filterOrgSMOCombo();
		win.syncSize();
		win.syncShadow();

		base_form.findField('PersonDopDispPlanExport_expDate').setValue(getGlobalOptions().date);
		base_form.findField('ExportByOrgSMO').fireEvent('check', base_form.findField('ExportByOrgSMO'), false);

		base_form.findField('PersonDopDispPlanExport_expDate').focus(250, true);
	},

	/* Конструктор */
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			defaults: {
				msgTarget: 'side'
			},
			frame: true,
			id: win.id + 'Form',
			labelAlign: 'right',
			labelWidth: 100,
			layout : 'form',
			style: 'padding-left: 5px',
			timeout: 1800,
			url: '/?c=PersonDopDispPlan&m=exportPersonDopDispPlan',

			items: [{
				name: 'PersonDopDispPlanExport_Year',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				blankText: 'Укажите дату, на которую подготовлены данные',
				fieldLabel: 'Отчетная дата',
				name: 'PersonDopDispPlanExport_expDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				boxLabel: 'В разрезе СМО',
				fieldLabel: '',
				labelSeparator: '',
				listeners: {
					'check': function(checkbox, checked) {
						var base_form = win.FormPanel.getForm();

						if ( checked ) {
							base_form.findField('OrgSMO_id').enable();
							base_form.findField('ExportByOrgSMO_flag').setValue(true);
						}
						else {
							base_form.findField('OrgSMO_id').disable();
							base_form.findField('OrgSMO_id').reset();
							base_form.findField('ExportByOrgSMO_flag').setValue(false);
						}
					}.createDelegate(this)
				},
				name: 'ExportByOrgSMO',
				xtype: 'checkbox'
			}, {
				name: 'ExportByOrgSMO_flag',
				xtype: 'hidden'
			}, new Ext.ux.Andrie.Select({
				anchor: '95%',
				clearBaseFilter: function() {
					this.baseFilterFn = null;
					this.baseFilterScope = null;
				},
				displayField: 'OrgSMO_Nick',
				fieldLabel: langs('СМО'),
				hiddenName: 'OrgSMO_id',
				multiSelect: true,
				mode: 'local',
				setBaseFilter: function(fn, scope) {
					this.baseFilterFn = fn;
					this.baseFilterScope = scope || this;
					this.store.filterBy(fn, scope);
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
				}),
				tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
					'{OrgSMO_Nick}' + '{[(values.OrgSMO_endDate != "" && values.OrgSMO_endDate!=null) ? " (не действует с " + values.OrgSMO_endDate + ")" : "&nbsp;"]}'+
				'</div></tpl>'),
				valueField: 'OrgSMO_id'
			})]
		});

		Ext.apply(win, {
			buttons: [{
				text: 'Сформировать',
				iconCls: 'ok16',
				handler: function(button, event) {							
					win.doExport();
				}
			}, {
				text: '-'
			},
			HelpButton(win),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign: "right",
			items: [
				win.FormPanel
			]
		});

		sw.Promed.swPersonDopDispPlanExportWindow.superclass.initComponent.apply(this, arguments);
	}
});