/**
* Форма Журнал талонов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsTicketJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Журнал талонов',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swEvnErsTicketJournalWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.SearchFilters.getForm();
		
		base_form.findField('ERSStatus_id').getStore().clearFilter();
		base_form.findField('ERSStatus_id').lastQuery = '';
		base_form.findField('ERSStatus_id').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 208;
		});

		if (!this.ErsTicketGrid.getAction('action_t_fss_requests')) {
			this.ErsTicketGrid.addActions({
				name: 'action_t_fss_requests',
				text: 'Запросы в ФСС',
				menu: [{
					text: 'Зарегистрировать талоны в ФСС',
					handler: function() {
						win.sendTicketsToFss();
					}
				}, {
					text: 'Запросить статус регистрации талонов в ФСС',
					handler: function() {
						win.getTicketStatus();
					}
				}, {
					text: 'Запросить актуальные данные талонов из ФСС',
					handler: function() {

					}
				}]
			}, 3);
		}

		if (!this.ErsTicketGrid.getAction('action_t_sign')) {
			this.ErsTicketGrid.addActions({
				name: 'action_t_sign',
				text: 'Подписать',
				menu: [{
					text: 'Подписать от лица МО',
					handler: function () {
						win.doSign({
							ERSSignatureType_id: 1,
							isMOSign: true
						});
					}
				}, {
					text: 'Подписать от лица Руководителя МО',
					handler: function () {
						win.doSign({
							ERSSignatureType_id: 2,
							isMOSign: false
						});
					}
				}]
			}, 3);
		}

		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.ErsTicketGrid.getGrid(),
			form = this.SearchFilters.getForm();
			
		if( !form.isValid() ) {
			return false;
		}
		
		grid.getStore().baseParams = form.getValues();
		grid.getStore().baseParams.Lpu_id = form.findField('Lpu_id').getValue();
		grid.getStore().load();
	},
	
	doReset: function() {
		var form = this.SearchFilters.getForm();
		form.reset();
		form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		this.doSearch();
	},
	
	doSign: function(params) {
		
		var win = this,
			wnd,
			rec = this.ErsTicketGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSTicket_id') ) return false;
		
		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: rec.get('ERSTicketType_Name') + ' от ' + rec.get('EvnERSTicket_setDate'),
			isMOSign: params.isMOSign,
			callback: function(data) {
				params.EvnERS_id = rec.get('EvnERSTicket_id');
				win.doSaveSign(params);
			}
		});
	},
	
	doSaveSign: function(params) {
		var win = this;	
		var lm = this.getLoadMask('Выполняется подписание документа');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=ErsSignature&m=doSignTicket',
			params: params,
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				win.doSearch();
			}
		});
	},
	
	sendTicketsToFss: function() {
		var win = this;
		
		getWnd('swSendTicketsToFssWindow').show({
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	getTicketStatus: function() {
		var win = this,
			wnd,
			rec = this.ErsTicketGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSTicket_id') ) return false;
		
		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Запрос статуса регистрации талонов в ФСС от ' + getGlobalOptions().date,
			formTitle: 'Запрос статуса регистрации талонов в ФСС',
			isMOSign: true,
			callback: function(data) {
				
				var lm = win.getLoadMask('Выполняется подписание документа');
				lm.show();
				Ext.Ajax.request({
					url: '/?c=EvnErsTicket&m=getFssResult',
					params: {
						EvnERSTicket_id: rec.get('EvnERSTicket_id'),
					},
					method: 'post',
					callback: function(opt, success, response) {
						lm.hide();
						win.doSearch();
						sw.swMsg.show({buttons: sw.swMsg.OK, icon: sw.swMsg.INFO, msg: 'Запрос успешно сформирован и отправлен в ФСС'});
					}
				});
			}
		});
	},
	
	openTicket: function(action) {
		
		var win = this,
			wnd,
			rec = this.ErsTicketGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSTicket_id') ) return false;
		
		switch (rec.get('ERSTicketType_id')) {
			case 1:
				wnd = 'swEvnErsTicket1EditWindow';
				break;
			case 2:
				wnd = 'swEvnErsTicket2EditWindow';
				break;
			case 3:
			case 4:
				wnd = 'swEvnErsTicket3EditWindow';
				break;
		}
		
		getWnd(wnd).show({
			action: action,
			EvnERSTicket_id: rec.get('EvnERSTicket_id'),
			callback: function () {
				win.doSearch();
			}
		});
	},

	deleteTicket: function() {
		console.log('Delete');
	},
	
	initComponent: function() {
		var win = this;
		
		this.SearchFilters = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 150,
			labelWidth: 120,
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
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						maskRe: /[^%]/
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSStatus',
						fieldLabel: 'Статус талона',
						moreFields: [
							{ name: 'EvnClass_id', mapping: 'EvnClass_id' }
						],
						width: 250
					}]
				}, {
					layout: 'form',
					labelWidth: 180,
					items: [{
						fieldLabel: 'Дата формирования талона',
						name: 'EvnERSTicket_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						xtype: 'swlpucombo',
						fieldLabel: 'МО',
						disabled: true,
						hiddenName: 'Lpu_id',
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
		
		this.ErsTicketGrid = new sw.Promed.ViewFrame({
			id: this.id + 'ErsTicketGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
            //root: 'data',
			border: false,
			enableColumnHide: false,
			obj_isEvn: true,
			object: 'EvnERSTicket',
			linkedTables: '',
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', handler: this.openTicket.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openTicket.createDelegate(this, ['view']) },
				{ name: 'action_delete', msg: 'Удалить талон?' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnERSTicket_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnERSBirthCertificate_id', type: 'int', hidden: true},
				{ name: 'ERSTicketType_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_Code', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequest_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnERSBirthCertificate_Number', type: 'string', header: 'Номер ЭРС', width: 150},
				{ name: 'Person_SurName', type: 'string', header: 'Фамилия', width: 150},
				{ name: 'Person_FirName', type: 'string', header: 'Имя', width: 150},
				{ name: 'Person_SecName', type: 'string', header: 'Отчество', width: 150},
				{ name: 'EvnErsBirthCertificate_setDT', type: 'string', header: 'Дата ЭРС', width: 150},
				{ name: 'ERSTicketType_Name', type: 'string', header: 'Тип талона', width: 150},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус талона', width: 150},
				{ name: 'EvnERSTicket_setDate', type: 'string', header: 'Дата талона', width: 150},
				{ name: 'Lpu_Nick', type: 'string', header: 'МО', width: 150},
				{ name: 'ErsRequestType_Name', type: 'string', header: 'Тип запроса', width: 150},
				{ name: 'ErsRequestStatus_Name', type: 'string', header: 'Статус запроса', width: 150},
				{ name: 'ErsRequestError', type: 'string', header: 'Ошибки обработки', width: 150},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				/*win.ErsTicketGrid.getAction('action_t_fss_requests').items[0].menu.items.items[0].setDisabled(!(rec.get('ERSStatus_id').inlist([21]) || rec.get('ErsRequestStatus_id').inlist([10])));
				win.ErsTicketGrid.getAction('action_t_fss_requests').items[0].menu.items.items[1].setDisabled(!(rec.get('ERSStatus_id').inlist([29]) && Ext.isEmpty(rec.get('ErsRequest_id'))));
				win.ErsTicketGrid.getAction('action_t_fss_requests').items[0].menu.items.items[4].setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]));
				win.ErsTicketGrid.getAction('action_edit').setDisabled(!(rec.get('ERSStatus_id').inlist([21, 28]) && rec.get('Lpu_id') == getGlobalOptions().lpu_id));
				win.ErsTicketGrid.getAction('action_close').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]));
				win.ErsTicketGrid.getAction('action_tickets').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2, 3]));
				win.ErsTicketGrid.getAction('action_delete').setDisabled(!rec.get('ERSStatus_id').inlist([21, 28]));*/
				if (rowIdx >=0 && rec.get('ERSStatus_Code')) {
					win.ErsTicketGrid.getAction('action_delete').setDisabled(!rec.get('ERSStatus_Code').inlist([21, 22, 23, 24, 26]));
				}
			},
			//paging: true,
			//pageSize: 100,
			dataUrl: '/?c=EvnErsTicket&m=loadJournal',
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
				this.ErsTicketGrid
			]
		});
		
		sw.Promed.swEvnErsTicketJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});