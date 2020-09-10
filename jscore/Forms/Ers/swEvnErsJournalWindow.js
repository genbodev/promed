/**
* Форма Журнал ЭРС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Журнал Родовых сертификатов',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swEvnErsJournalWindow',
	closeAction: 'hide',
	show: function() {
		sw.Promed.swEvnErsJournalWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.SearchFilters.getForm();

		base_form.findField('ERSStatus_id').getStore().clearFilter();
		base_form.findField('ERSStatus_id').lastQuery = '';
		base_form.findField('ERSStatus_id').getStore().filterBy(function(rec) {
			return !rec.get('EvnClass_id') || rec.get('EvnClass_id') == 205;
		});
		
		win.ignoreCheckLpu = false;
		
		this.findById(win.id + 'SearchFilterTabbar').hideTabStripItem(win.id + 'SearchFilterTabbarfilterLgota');
		this.findById(win.id + 'SearchFilterTabbar').hideTabStripItem(win.id + 'SearchFilterTabbarfilterUser');
		
		this.ErsGrid.addActions({
			name: 'action_fss_requests', 
			text: 'Запросы в ФСС', 
			menu: [{
				name: 'send2fss', 
				text: 'Отправить новый ЭРС на регистрацию в ФСС',
				handler: function() {
					win.sendErsToFss();
				}
			}, {
				name: 'get_fss_requests_result', 
				text: 'Запросить результат регистрации нового ЭРС в ФСС', 
				handler: function() {
					//
				}
			}, {
				name: 'get_data_by_number', 
				text: 'Запросить актуальные данные ЭРС по номеру', 
				handler: function() {
					win.getByErsNumber();
				}
			}, {
				name: 'get_data_by_snils', 
				text: 'Запросить актуальные данные ЭРС по СНИЛС пациента',
				handler: function() {
					win.getBySnils();
				}
			}, {
				id: this.id + 'get_data_by_ers', 
				name: 'get_data_by_ers', 
				text: 'Запросить актуальные данные по выбранному ЭРС',
				handler: function() {
					win.getByErs();
				}
			}]
		}, 1);
		
		this.ErsGrid.addActions({
			name: 'action_close', 
			text: 'Закрыть',
			handler: function() {
				win.closeEvnERS();
			}
		}, 4);
		
		this.ErsGrid.addActions({
			name: 'action_tickets', 
			text: 'Талоны',
			handler: function() {
				win.openEvnERSTicket();
			}
		}, 5);
		
		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.ErsGrid.getGrid(),
			form = this.SearchFilters.getForm();
			
		if( !form.isValid() ) {
			return false;
		}
		
		grid.getStore().baseParams = form.getValues();
		grid.getStore().load();
	},
	
	doReset: function() {
		this.SearchFilters.getForm().reset();
		this.doSearch();
	},
	
	/**
	 * -----------------------
	 */
	sendErsToFss: function() {
		var win = this,
			loadMask = lm = this.getLoadMask(LOAD_WAIT)
			params = {},
			rec = this.ErsGrid.getGrid().getSelectionModel().getSelected();

		if ( !rec || !rec.get('EvnERSBirthCertificate_id') ) return false;			
		
		params.EvnERSBirthCertificate_id = rec.get('EvnERSBirthCertificate_id');

		console.log(params);

		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Запрос в ФСС от ' + getGlobalOptions().date,
			isMOSign: true,
			callback: function(data) {
				// заглушка
				Ext.Ajax.request({
					url: '/?c=ErsRequest&m=sendErsToFss',
					params: {EvnERS_id: rec.get('EvnERSBirthCertificate_id')},
					method: 'post',
					callback: function(opt, success, response) {
						lm.hide();
						win.doSearch();
					}
				});				
				// console.log(data);
				// $.ajax({
				// 	url: '/?c=ServiceERS&m=createErs',
				// 	data: params,
				// 	method: 'GET',
				// 	timeout: 1000,
				// 	success: function(response) {
				// 		console.log(response);
				// 	}
				// });

			}
		});		
	},
	
	/**
	 * Контроль наличия действующего договора с ФСС + Контроль наличия данных МО
	 */
	checkLpu: function(callback) {
		var win = this;
		
		if (win.ignoreCheckLpu) {
			callback();
			return;
		}

		var lm = this.getLoadMask('Проверка МО');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=EvnErsBirthCertificate&m=checkLpu',
			params: {Lpu_id: getGlobalOptions().lpu_id},
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				
				if (response_obj.success) {
					// в случае успешной проверки сохраним результат, чтобы каждый раз не делать запрос
					win.ignoreCheckLpu = true; 
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
	
	/**
	 * Контроль наличия данных Получателя услуг
	 */
	checkPerson: function(pdata, callback) {
		
		var chk = [];
		if (Ext.isEmpty(pdata.Person_Surname)) chk.push('Фамилия');
		if (Ext.isEmpty(pdata.Person_Firname)) chk.push('Имя');
		if (Ext.isEmpty(pdata.Person_Birthday)) chk.push('Дата рождения');
		
		if (chk.length) {
			sw.swMsg.alert('Ошиюка', 'Для пациента не указаны обязательные данные: ' + chk.join(', ') + '. Внесите недостающие данные.');
			return;
		}
		
		chk = [];
		if (Ext.isEmpty(pdata.Polis_EdNum)) chk.push('Полис ОМС');
		if (Ext.isEmpty(pdata.Person_Snils)) chk.push('СНИЛС');
		if (Ext.isEmpty(pdata.Document_Num)) chk.push('Документ, удостоверяющий личность');
		if (Ext.isEmpty(pdata.PAddress_AddressText) || Ext.isEmpty(pdata.UAddress_AddressText)) chk.push('Адрес');
		
		if (chk.length) {
			sw.swMsg.confirm(
				'Внимание', 
				'В системе отсутствуют следующие данные пациентки: ' + chk.join(', ') + '. При дальнейшей работе будет необходимо указать причину отсутствия данной информации. Продолжить?', 
				function(btn) {
					if (btn == 'yes') {
						callback();
					}
				}
			);
			return;
		}
			
		callback();
	},
	
	checkErsExists: function(params) {
		var win = this;

		var lm = this.getLoadMask('Проверка наличия ЭРС');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=EvnErsBirthCertificate&m=checkErsExists',
			params: {Person_id: params.Person_id},
			method: 'post',
			callback: function(opt, success, response){
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.length && response_obj[0].msg) {
					sw.swMsg.alert('Ошибка', response_obj[0].msg);
				} else {
					params.callback();
				}
			}
		});
	},
	
	getByErs: function() {
		var win = this,
			rec = this.ErsGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSBirthCertificate_id') ) return false;
		
		win.checkLpu(function() {
			getWnd('swErsRequestWindow').show({
				EvnERSBirthCertificate_id: rec.get('EvnERSBirthCertificate_id'),
				ERSRequest_ERSNumber: rec.get('EvnERSBirthCertificate_Number'),
				Person_id: rec.get('Person_id'),
				Server_id: rec.get('Server_id'),
				callback: function () {
					win.doSearch();
				}
			});
		});
	},
	
	getByErsNumber: function() {
		var win = this;
		
		win.checkLpu(function() {
			getWnd('swPersonSearchWindow').show({
				searchMode: 'ers',
				onSelect: function(pdata) {
					
					getWnd('swPersonSearchWindow').hide();
					getWnd('swErsRequestWindow').show({
						Person_id: pdata.Person_id,
						Server_id: pdata.Server_id
					});
				}
			});
		});
	},
	
	getBySnils: function() {
		var win = this;
		
		win.checkLpu(function() {
			getWnd('swPersonSearchWindow').show({
				searchMode: 'erssnils',
				onSelect: function(pdata) {
					
					getWnd('swPersonSearchWindow').hide();
					getWnd('swErsRequestWindow').show({
						Person_id: pdata.Person_id,
						Server_id: pdata.Server_id,
						ERSRequest_Snils: pdata.Person_Snils
					});
				}
			});
		});
	},
	
	closeEvnERS: function() {
		var win = this,
			rec = this.ErsGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSBirthCertificate_id') ) return false;
		
		win.checkLpu(function() {
			getWnd('swEvnErsCloseWindow').show({
				EvnERSBirthCertificate_id: rec.get('EvnERSBirthCertificate_id'),
				EvnERSBirthCertificate_Number: rec.get('EvnERSBirthCertificate_Number'),
				callback: function () {
					win.doSearch();
				}
			});
		});
	},
	
	openEvnERSTicket: function() {
		var win = this,
			rec = this.ErsGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnERSBirthCertificate_id') ) return false;
		
		getWnd('swEvnErsTicketListWindow').show({
			EvnERSBirthCertificate_id: rec.get('EvnERSBirthCertificate_id'),
			EvnERSBirthCertificate_Number: rec.get('EvnERSBirthCertificate_Number'),
			Person_id: rec.get('Person_id'),
		});
	},
	
	addEvnERS: function() {
		var win = this;
		
		win.checkLpu(function() {
			getWnd('swPersonSearchWindow').show({
				searchMode: 'ers',
				onSelect: function(pdata) {
					
					getWnd('swPersonSearchWindow').hide();
					
					win.checkErsExists({
						Person_id: pdata.Person_id,
						callback: function() {
							
							win.checkPerson(pdata, function() {
								getWnd('swEvnErsBirthCertificateEditWindow').show({
									action: 'add',
									Person_id: pdata.Person_id,
									callback: function () {
										win.doSearch();
									}
								});
							});
						}
					});
				}
			});
		});
	},
	
	openEvnERS: function( action ) {
		var win = this;
		var rec = this.ErsGrid.getGrid().getSelectionModel().getSelected();
		
		if( !rec ) return false;
		
		win.checkLpu(function() {
			getWnd('swEvnErsBirthCertificateEditWindow').show({
				EvnERSBirthCertificate_id: rec.get('EvnERSBirthCertificate_id'),
				action: action,
				callback: function () {
					win.doSearch();
				}
			});
		});
	},
	
	initComponent: function() {
		var win = this;
		
		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'EvnERSFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'EvnERSBirthCertificate',
			tabPanelHeight: 235,
			tabPanelId: win.id + 'SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 200,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = win.SearchFilters.getForm();
						form.findField('EvnERSBirthCertificate_Number').focus(250, true);
					}
				},
				title: '5. ЭРС',
				items: [{
					xtype: 'numberfield',
					name: 'EvnERSBirthCertificate_Number',
					fieldLabel: 'Номер ЭРС',
					width: 180
				}, {
					fieldLabel: 'Дата формирования ЭРС',
					name: 'EvnERSBirthCertificate_CreateDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'ERSStatus',
					fieldLabel: 'Статус ЭРС',
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
		});
		
		this.ErsGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
            root: 'data',
			border: false,
			enableColumnHide: false,
			obj_isEvn: true,
			linkedTables: '',
			object: 'EvnERSBirthCertificate',
			actions: [
				{ name: 'action_add', text: 'Новый ЭРС', handler: this.addEvnERS.createDelegate(this) },
				{ name: 'action_edit', handler: this.openEvnERS.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openEvnERS.createDelegate(this, ['view']) },
				{ name: 'action_delete', msg: 'Удалить Родовой сертификат?' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true  }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnERSBirthCertificate_id', type: 'int', hidden: true, key: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ErsRequest_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 150},
				{ name: 'Person_Firname', type: 'string', header: 'Имя', width: 150},
				{ name: 'Person_Secname', type: 'string', header: 'Отчество', width: 150},
				{ name: 'EvnERSBirthCertificate_Number', type: 'string', header: 'Номер ЭРС', width: 150},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус ЭРС', width: 150},
				{ name: 'ERSCloseCauseType_Name', type: 'string', header: 'Причина закрытия ЭРС', width: 150},
				{ name: 'EvnErsBirthCertificate_setDT', type: 'string', header: 'Дата формирования ЭРС', width: 150},
				{ name: 'ErsRequestType_Name', type: 'string', header: 'Тип запроса', width: 150},
				{ name: 'ErsRequestStatus_Name', type: 'string', header: 'Статус запроса', width: 150},
				{ name: 'ErsRequestError', type: 'string', header: 'Ошибки обработки', width: 150},
				{ name: 'EvnERSTicket1', type: 'string', header: 'Талон 1', width: 150},
				{ name: 'EvnERSTicket2', type: 'string', header: 'Талон 2', width: 150},
				{ name: 'EvnERSTicket31', type: 'string', header: 'Талон 3-1', width: 150},
				{ name: 'EvnERSTicket32', type: 'string', header: 'Талон 3-2', width: 150},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				if (!rec || !rec.get('EvnERSBirthCertificate_id')) return false;
				win.ErsGrid.getAction('action_fss_requests').items[0].menu.items.items[0].setDisabled(!(rec.get('ERSStatus_id').inlist([21]) || rec.get('ErsRequestStatus_id').inlist([10])));
				win.ErsGrid.getAction('action_fss_requests').items[0].menu.items.items[1].setDisabled(!(rec.get('ERSStatus_id').inlist([29]) && Ext.isEmpty(rec.get('ErsRequest_id'))));
				win.ErsGrid.getAction('action_fss_requests').items[0].menu.items.items[4].setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]));
				win.ErsGrid.getAction('action_edit').setDisabled(!(rec.get('ERSStatus_id').inlist([21, 28]) && rec.get('Lpu_id') == getGlobalOptions().lpu_id));
				win.ErsGrid.getAction('action_close').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2]));
				win.ErsGrid.getAction('action_tickets').setDisabled(!rec.get('ERSStatus_id').inlist([1, 2, 3]));
				win.ErsGrid.getAction('action_delete').setDisabled(!rec.get('ERSStatus_id').inlist([21, 28]));
			},
			paging: true,
			pageSize: 100,
			dataUrl: C_SEARCH,
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
				this.ErsGrid
			]
		});
		
		sw.Promed.swEvnErsJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});