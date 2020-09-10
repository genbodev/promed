/**
* Форма Реестры талонов и счета на оплату
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsRegistryJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Реестры талонов и счета на оплату',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swErsRegistryJournalWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.SearchFilters.getForm();

		base_form.findField('ERSStatus_id').getStore().clearFilter();
		base_form.findField('ERSStatus_id').lastQuery = '';
		base_form.findField('ERSStatus_id').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 210;
		});

		base_form.findField('ERSStatus_BillId').getStore().clearFilter();
		base_form.findField('ERSStatus_BillId').lastQuery = '';
		base_form.findField('ERSStatus_BillId').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 206;
		});
		
		this.ErsRegistryGrid.addActions({
			iconCls: 'add16',
			name: 'action_bill_add', 
			text: 'Включить в счет',
			handler: function() {
				win.addErsBill();
			}
		}, 9);
		
		this.ErsRegistryGrid.addActions({
			iconCls: 'edit16',
			name: 'action_bill_edit', 
			text: 'Изменить счет',
			handler: function() {
				win.openErsBill('edit');
			}
		}, 10);
		
		this.ErsRegistryGrid.addActions({
			iconCls: 'delete16',
			name: 'action_bill_delete', 
			text: 'Удалить счет',
			handler: function() {

				var rec = win.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
				if (!rec || !rec.get('ErsBill_id')) return false;

				sw.swMsg.confirm('', 'Удалить счет на оплату?', function (btn) {
					if (btn !== 'yes') return false;
					var lm = win.getLoadMask(LOAD_WAIT_DELETE);
					lm.show();
					Ext.Ajax.request({
						url: '/?c=ErsBill&m=delete',
						params: {ErsBill_id: rec.get('ErsBill_id')},
						method: 'post',
						callback: function(opt, success, response) {
							lm.hide();
							win.doSearch();
						}
					});
				});
			}
		}, 11);
		
		this.ErsRegistryGrid.addActions({
			name: 'action_r_sign', 
			text: 'Подписать', 
			menu: [{
				text: 'Подписать счет на оплату от лица Бухгалтера', 
				handler: function() {
					win.doSign({
						ERSSignatureType_id: 3,
						isMOSign: false
					});
				}
			}, {
				text: 'Подписать счет на оплату от лица МО',
				handler: function() {
					win.doSign({
						ERSSignatureType_id: 1,
						isMOSign: true
					});
				}
			}, {
				text: 'Подписать счет на оплату от лица Руководителя МО',
				handler: function() {
					win.doSign({
						ERSSignatureType_id: 2,
						isMOSign: false
					});
				}
			}]
		}, 12);
		
		this.ErsRegistryGrid.addActions({
			name: 'action_r_fss_requests', 
			text: 'Запросы в ФСС', 
			menu: [{
				text: 'Зарегистрировать счет на оплату в ФСС',
				handler: function() {
					win.sendToFss();
				}
			}, {
				text: 'Запросить результат регистрации счета в ФСС', 
				handler: function() {
					win.getFssResult();
				}
			}, {
				text: 'Запросить текущий статус счета из ФСС', 
				handler: function() {
					
				}
			}]
		}, 13);
		
		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.ErsRegistryGrid.getGrid(),
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
			rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('ErsBill_id') ) return false;
		
		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Счет на оплату и реестр талонов' + ' от ' + rec.get('ErsBill_Date'),
			isMOSign: params.isMOSign,
			callback: function(data) {
				params.EvnERS_id = rec.get('ErsBill_id');
				win.doSaveSign(params);
			}
		});
	},
	
	doSaveSign: function(params) {
		var win = this;	
		var lm = this.getLoadMask('Выполняется подписание документа');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=ErsSignature&m=doSignBill',
			params: params,
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				win.doSearch();
			}
		});
	},

	sendToFss: function() {
		
		var win = this,
			wnd,
			rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('ErsBill_id') ) return false;
		
		var lm = this.getLoadMask(LOAD_WAIT_SAVE);
		lm.show();
		Ext.Ajax.request({
			url: '/?c=ErsBill&m=sendToFss',
			params: {
				EvnERS_id: rec.get('ErsBill_id'),
				EvnERS_pid: rec.get('ErsRegistry_id'),
			},
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				win.doSearch();
				sw.swMsg.show({buttons: sw.swMsg.OK, icon: sw.swMsg.INFO, msg: 'Запрос успешно сформирован и отправлен в ФСС'});
			}
		});
	},
	
	getFssResult: function() {
		var win = this,
			wnd,
			rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('ErsBill_id') ) return false;
		
		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Запрос статуса платежных документов от ' + getGlobalOptions().date,
			formTitle: 'Запрос статуса регистрации счета на оплату в ФСС',
			isMOSign: true,
			callback: function(data) {
				
				var lm = win.getLoadMask('Выполняется подписание документа');
				lm.show();
				Ext.Ajax.request({
					url: '/?c=ErsRegistry&m=getFssResult',
					params: {
						EvnERS_id: rec.get('ErsBill_id'),
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
	
	addErsRegistry: function() {
		var win = this;
		this.checkCanCreate(function () {
			win._addErsRegistry();
		});
	},

	_addErsRegistry: function() {
		var win = this;

		getWnd('swErsRegistryEditWindow').show({
			action: 'add',
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	openErsRegistry: function( action ) {
		var win = this;
		var rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec || !rec.get('ErsRegistry_id') ) return false;
		
		getWnd('swErsRegistryEditWindow').show({
			ErsRegistry_id: rec.get('ErsRegistry_id'),
			action: action,
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	addErsBill: function( action ) {
		var win = this;
		var rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec || !rec.get('ErsRegistry_id') ) return false;
		
		getWnd('swErsBillEditWindow').show({
			ErsRegistry_id: rec.get('ErsRegistry_id'),
			action: action,
			callback: function () {
				win.doSearch();
			}
		});
	},
	
	openErsBill: function( action ) {
		var win = this;
		var rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec || !rec.get('ErsBill_id') ) return false;
		
		getWnd('swErsBillEditWindow').show({
			ErsBill_id: rec.get('ErsBill_id'),
			action: action,
			callback: function () {
				win.doSearch();
			}
		});
	},

	openTicket: function() {

		var wnd,
			rec = this.TicketsGrid.getGrid().getSelectionModel().getSelected();

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
			action: 'view',
			EvnERSTicket_id: rec.get('EvnERSTicket_id')
		});
	},

	loadTicketsGrid: function() {
		var rec = this.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
		var grid = this.TicketsGrid.getGrid();

		grid.getStore().removeAll();

		if( !rec || !rec.get('ErsRegistry_id') ) return false;

		grid.getStore().baseParams = {
			ErsRegistry_id: rec.get('ErsRegistry_id')
		};
		grid.getStore().load();
	},

	checkCanCreate: function(callback) {
		var win = this;
		var lm = this.getLoadMask(LOAD_WAIT);
		lm.show();
		Ext.Ajax.request({
			url: '/?c=ErsRegistry&m=checkCanCreate',
			params: {Lpu_id: getGlobalOptions().lpu_id},
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					callback();
				}
				else if (response_obj.Error_Message) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Message);
				}
				else {
					sw.swMsg.alert('Ошибка', 'При проверке МО произошла ошибка');
				}
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
			height: 100,
			labelWidth: 120,
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;',
					columnWitdh: .3
				},
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: 'Дата реестра',
						name: 'ErsRegistry_Date_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSStatus',
						fieldLabel: 'Статус реестра',
						moreFields: [
							{ name: 'EvnClass_id', mapping: 'EvnClass_id' }
						],
						width: 250
					}, {
						xtype: 'numberfield',
						name: 'ErsRegistry_Number',
						fieldLabel: 'Номер реестра',
						width: 180
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: 'Дата счета',
						name: 'ErsBill_Date_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'ERSStatus',
						fieldLabel: 'Статус счета',
						hiddenName: 'ERSStatus_BillId',
						moreFields: [
							{ name: 'EvnClass_id', mapping: 'EvnClass_id' }
						],
						width: 250
					}, {
						xtype: 'numberfield',
						name: 'ErsBill_Number',
						fieldLabel: 'Номер счета',
						width: 180
					}]
				}, {
					layout: 'form',
					items: [{
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
					}, {
						xtype: 'swlpucombo',
						fieldLabel: 'МО',
						disabled: true,
						hiddenName: 'Lpu_id',
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
		
		this.ErsRegistryGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
            //root: 'data',
			border: true,
			enableColumnHide: false,
			obj_isEvn: true,
			linkedTables: '',
			object: 'ErsRegistry',
			actions: [
				{ name: 'action_add', text: 'Добавить реестр', handler: this.addErsRegistry.createDelegate(this) },
				{ name: 'action_edit', text: 'Изменить реестр', handler: this.openErsRegistry.createDelegate(this, ['edit']) },
				{ name: 'action_view', text: 'Просмотреть реестр', handler: this.openErsRegistry.createDelegate(this, ['view']) },
				{ name: 'action_delete', text: 'Удалить реестр', handler: function() {

					var rec = win.ErsRegistryGrid.getGrid().getSelectionModel().getSelected();
					if (!rec || !rec.get('ErsRegistry_id')) return false;

					var msg = !rec.get('ErsBill_id')
						? 'Удалить реестр?'
						: 'Реестр включен в счет на оплату, удалить реестр и счет на оплату?';

					sw.swMsg.confirm('', msg, function (btn) {
						if (btn !== 'yes') return false;
						var lm = win.getLoadMask(LOAD_WAIT_DELETE);
						lm.show();
						Ext.Ajax.request({
							url: '/?c=ErsRegistry&m=delete',
							params: {ErsRegistry_id: rec.get('ErsRegistry_id')},
							method: 'post',
							callback: function(opt, success, response) {
								lm.hide();
								win.doSearch();
							}
						});
					});
				}},
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'ErsRegistry_id', type: 'int', hidden: true, key: true },
				{ name: 'ErsBill_id', type: 'int', hidden: true },
				{ name: 'ErsBill_Date', type: 'string', hidden: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_BillId', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequest_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'ErsRegistry_Number', type: 'string', header: 'Номер реестра', width: 120},
				{ name: 'ErsRegistry_Date', type: 'string', header: 'Дата реестра', width: 120},
				{ name: 'ErsRegistry_TicketsCount', type: 'string', header: 'Количество талонов', width: 150},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус реестра', width: 200},
				{ name: 'ErsBill_Number', type: 'string', header: 'Счет на оплату', width: 150},
				{ name: 'ERSStatus_BillName', type: 'string', header: 'Статус счета', width: 200},
				{ name: 'ErsRequestType_Name', type: 'string', header: 'Тип запроса', width: 250},
				{ name: 'ErsRequestStatus_Name', type: 'string', header: 'Статус запроса', width: 150},
				{ name: 'ErsRequestError', type: 'string', header: 'Ошибки обработки', width: 150, id: 'autoexpand'},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				win.loadTicketsGrid();
				win.ErsRegistryGrid.getAction('action_edit').setDisabled(!rec || !rec.get('ErsRegistry_id') || !rec.get('ERSStatus_id').inlist([21]));
				win.ErsRegistryGrid.getAction('action_delete').setDisabled(!rec || !rec.get('ErsRegistry_id') || !rec.get('ERSStatus_id').inlist([21,19,28]));
				win.ErsRegistryGrid.getAction('action_bill_add').setDisabled(!rec || !rec.get('ErsRegistry_id') || !!rec.get('ErsBill_id'));
				win.ErsRegistryGrid.getAction('action_bill_edit').setDisabled(!rec || !rec.get('ErsBill_id') || !rec.get('ERSStatus_BillId').inlist([21,4,5,6,7,22,23,24,26,28]));
				win.ErsRegistryGrid.getAction('action_bill_delete').setDisabled(!rec || !rec.get('ErsBill_id') || !rec.get('ERSStatus_BillId').inlist([21,4,5,6,7,22,23,24,26,28]));
				win.ErsRegistryGrid.getAction('action_r_sign').setDisabled(!rec || !rec.get('ErsBill_id') || !rec.get('ERSStatus_BillId').inlist([21,4,5,6,7,22,23,24]));
			},
			//paging: true,
			//pageSize: 100,
			dataUrl: '/?c=ErsRegistry&m=loadJournal',
			totalProperty: 'totalCount'
		});

		this.TicketsGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'south',
			border: true,
			height: 250,
			enableColumnHide: false,
			title: 'Талоны в реестре',
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', handler: this.openTicket.createDelegate(this) },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnERSTicket_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnERSBirthCertificate_id', type: 'int', hidden: true},
				{ name: 'ERSTicketType_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequest_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnERSBirthCertificate_Number', type: 'string', header: 'Номер ЭРС', width: 150},
				{ name: 'Person_SurName', type: 'string', header: 'Фамилия', width: 150},
				{ name: 'Person_FirName', type: 'string', header: 'Имя', width: 150},
				{ name: 'Person_SecName', type: 'string', header: 'Отчество', width: 150},
				{ name: 'EvnErsBirthCertificate_PregnancyRegDate', type: 'string', header: 'Дата ЭРС', width: 150},
				{ name: 'ERSTicketType_Name', type: 'string', header: 'Тип талона', width: 150},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус талона', width: 150},
				{ name: 'EvnERSTicket_setDate', type: 'string', header: 'Дата талона', width: 150},
			],
			onDblClick: function() {
				win.openTicket();
			},
			dataUrl: '/?c=ErsRegistry&m=loadTickets',
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
				this.ErsRegistryGrid,
				this.TicketsGrid
			]
		});
		
		sw.Promed.swErsRegistryJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});