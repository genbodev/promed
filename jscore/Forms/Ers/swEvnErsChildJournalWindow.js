/**
* Форма Журнал учета детей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsChildJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Журнал учета детей',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swEvnErsChildJournalWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.SearchFilters.getForm();
		
		base_form.findField('ERSStatus_id').getStore().clearFilter();
		base_form.findField('ERSStatus_id').lastQuery = '';
		base_form.findField('ERSStatus_id').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 205;
		});
		
		base_form.findField('ERSStatus_ChildId').getStore().clearFilter();
		base_form.findField('ERSStatus_ChildId').lastQuery = '';
		base_form.findField('ERSStatus_ChildId').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 209;
		});
		
		win.ignoreCheckLpu = false;

		if (!this.ErsChildGrid.getAction('action_c_fss_requests')) {
			this.ErsChildGrid.addActions({
				name: 'action_c_fss_requests',
				text: 'Запросы в ФСС',
				menu: [{
					name: 'send2fss',
					text: 'Отправить данные детей на регистрацию в ФСС',
					handler: function () {
						win.sendToFss();
					}
				}, {
					name: 'get_fss_requests_result',
					text: 'Запросить результаты регистрации детей в ФСС',
					handler: function () {

					}
				}]
			}, 4);
		}

		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.ErsChildGrid.getGrid(),
			form = this.SearchFilters.getForm();
			
		if( !form.isValid() ) {
			return false;
		}
		
		grid.getStore().baseParams = form.getValues();
		grid.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
		grid.getStore().load();
	},
	
	doReset: function() {
		this.SearchFilters.getForm().reset();
		this.doSearch();
	},
	
	sendToFss: function() {
		
		var win = this,
			wnd,
			rec = this.ErsChildGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnErsChild_id') ) return false;
		
		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Запрос в ФСС от ' + getGlobalOptions().date,
			callback: function(data) {
				params.EvnERS_id = rec.get('EvnErsChild_id');
				win.doSendToFss(params);
			}
		});
	},
	
	doSendToFss: function(params) {
		var win = this;	
		var lm = this.getLoadMask(LOAD_WAIT_SAVE);
		lm.show();
		Ext.Ajax.request({
			url: '/?c=EvnErsChild&m=sendToFss',
			params: params,
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				win.doSearch();
			}
		});
	},
	
	addEvnErsChild: function() {
		var win = this;
		var rec = this.ErsChildGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec ) return false;
		
		getWnd('swEvnErsChildEditWindow').show({
			action: 'add',
			Person_id: rec.get('Person_id'),
			EvnErsChild_pid: rec.get('EvnERSBirthCertificate_id'),
			EvnERSBirthCertificate_Number: rec.get('EvnERSBirthCertificate_Number'),
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	openEvnErsChild: function( action ) {
		var win = this;
		var rec = this.ErsChildGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec || !rec.get('EvnErsChild_id') ) return false;
		
		getWnd('swEvnErsChildEditWindow').show({
			action: action,
			EvnErsChild_id: rec.get('EvnErsChild_id'),
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	initComponent: function() {
		var win = this;
		
		this.SearchFilters = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 170,
			labelWidth: 160,
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						xtype: 'numberfield',
						name: 'EvnERSBirthCertificate_Number',
						fieldLabel: 'Номер ЭРС',
						width: 180
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSStatus',
						fieldLabel: 'Статус ЭРС',
						moreFields: [
							{ name: 'EvnClass_id', mapping: 'EvnClass_id' }
						],
						width: 250
					}, {
						fieldLabel: 'Дата выдачи ЭРС',
						name: 'EvnERSBirthCertificate_CreateDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SurName',
						fieldLabel: 'Фамилия матери',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_FirName',
						fieldLabel: 'Имя матери',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SecName',
						fieldLabel: 'Отчество матери',
						maskRe: /[^%]/
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfieldpmw',
						width: 250,
						name: 'PersonChild_SurName',
						fieldLabel: 'Фамилия ребенка',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'PersonChild_FirName',
						fieldLabel: 'Имя ребенка',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'PersonChild_SecName',
						fieldLabel: 'Отчество ребенка',
						maskRe: /[^%]/
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSStatus',
						fieldLabel: 'Статус учета детей',
						hiddenName: 'ERSStatus_ChildId',
						moreFields: [
							{ name: 'EvnClass_id', mapping: 'EvnClass_id' }
						],
						width: 250
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSRequestType',
						fieldLabel: 'Тип запроса',
						showCodefield: false,
						width: 250
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSRequestStatus',
						fieldLabel: 'Статус запроса',
						showCodefield: false,
						width: 250
					}]
				}]
			}],
			keys: [{
				fn: function(e) {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		
		this.ErsChildGrid = new sw.Promed.ViewFrame({
			id: this.id + 'ErsChildGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
            //root: 'data',
			border: false,
			enableColumnHide: false,
			obj_isEvn: true,
			linkedTables: '',
			actions: [
				{ name: 'action_add', text: 'Поставить на учет', handler: this.addEvnErsChild.createDelegate(this) },
				{ name: 'action_edit', handler: this.openEvnErsChild.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openEvnErsChild.createDelegate(this, ['view']) },
				{ name: 'action_delete', handler: function() {

					var rec = win.ErsChildGrid.getGrid().getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnErsChild_id')) return false;

					sw.swMsg.confirm('', 'Удалить данные о постанове детей на учет?', function (btn) {
						if (btn !== 'yes') return false;
						var lm = win.getLoadMask(LOAD_WAIT_DELETE);
						lm.show();
						Ext.Ajax.request({
							url: '/?c=EvnErsChild&m=delete',
							params: {EvnErsChild_id: rec.get('EvnErsChild_id')},
							method: 'post',
							callback: function(opt, success, response) {
								lm.hide();
								win.doSearch();
							}
						});
					});
				}},
				{ name: 'action_refresh', hidden: false },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnERSBirthCertificate_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnErsChild_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequest_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnERSBirthCertificate_Number', type: 'string', header: 'Номер ЭРС', width: 150},
				{ name: 'EvnErsBirthCertificate_setDT', type: 'string', header: 'Дата выдачи ЭРС', width: 150},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус ЭРС', width: 150},
				{ name: 'Person_Fio', type: 'string', header: 'ФИО матери', width: 200},
				{ name: 'Person_ChildFio', type: 'string', header: 'ФИО детей', width: 200},
				{ name: 'ERSStatus_ChildName', type: 'string', header: 'Статус учета детей', width: 150},
				{ name: 'ErsRequestType_Name', type: 'string', header: 'Тип запроса', width: 150},
				{ name: 'ErsRequestStatus_Name', type: 'string', header: 'Статус запроса', width: 150},
				{ name: 'ErsRequestError', type: 'string', header: 'Ошибки обработки', width: 150},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				if (!rec || !rec.get('EvnERSBirthCertificate_id')) return false;
				win.ErsChildGrid.getAction('action_add').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]) || !!rec.get('EvnErsChild_id'));
				win.ErsChildGrid.getAction('action_edit').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]) || !rec.get('EvnErsChild_id'));
				win.ErsChildGrid.getAction('action_view').setDisabled(!rec.get('EvnErsChild_id'));
				win.ErsChildGrid.getAction('action_delete').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]) || !rec.get('EvnErsChild_id'));
			},
			//paging: true,
			//pageSize: 100,
			dataUrl: '/?c=EvnErsChild&m=loadJournal',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [{
				handler: this.doSearch.createDelegate(this),
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			},
			{
				handler: this.doReset.createDelegate(this),
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			},
			'-',
			HelpButton(this),
			{
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: BTN_FRMCLOSE,
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}],
			items: [
				this.SearchFilters, 
				this.ErsChildGrid
			]
		});
		
		sw.Promed.swEvnErsChildJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});