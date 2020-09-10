/**
* Форма Талоны Родового сертификата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsTicketListWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Талоны Родового сертификата',
	modal: true,
	resizable: false,
	maximized: false,
	width: 900,
	height: 400,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swEvnErsTicketListWindow.superclass.show.apply(this, arguments);
		
		var win = this;
			
		if (!arguments.length) arguments = [{}];
		
		this.EvnERSBirthCertificate_id = arguments[0].EvnERSBirthCertificate_id || null;
		this.EvnERSBirthCertificate_Number = arguments[0].EvnERSBirthCertificate_Number || null;
		this.Person_id = arguments[0].Person_id || null;
		
		this.TicketsGrid.addActions({
			name: 'ticket_add_menu', 
			iconCls: 'add16',
			text: 'Добавить', 
			menu: [{
				name: 'ticket1', 
				text: 'Талон 1',
				handler: function() {
					win.addTicket(1);
				}
			}, {
				name: 'ticket2', 
				text: 'Талон 2',
				handler: function() {
					win.addTicket(2);
				}
			}, {
				name: 'ticket31', 
				text: 'Талон 3-1',
				handler: function() {
					win.addTicket(31);
				}
			}, {
				name: 'ticket32', 
				text: 'Талон 3-2',
				handler: function() {
					win.addTicket(32);
				}
			}]
		}, 0);
		
		
		var grid = this.TicketsGrid.getGrid();
		grid.getStore().baseParams = {EvnERSTicket_pid: this.EvnERSBirthCertificate_id};
		grid.getStore().load();
		
	},
	
	/**
	 * Контроль наличия данных Получателя услуг (запрос данных)
	 */
	_checkPerson: function(callback) {
		
		var win = this;
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		Ext.Ajax.request({
			url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				Person_id: win.Person_id
			},
			method: 'post',
			callback: function(opt, success, response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (success && response_obj.length) {
					win.checkPerson(response_obj[0], callback);
				}
				else {
					sw.swMsg.alert('Ошибка', 'При проверке данных Получателя услуг произошла ошибка');
				}
			}
		});
	},
	
	/**
	 * Контроль наличия данных Получателя услуг (сама проверка)
	 */
	checkPerson: function(pdata, callback) {
		
		var chk = [];
		if (Ext.isEmpty(pdata.Person_SurName)) chk.push('Фамилия');
		if (Ext.isEmpty(pdata.Person_FirName)) chk.push('Имя');
		if (Ext.isEmpty(pdata.Person_BirthDay)) chk.push('Дата рождения');
		
		if (chk.length) {
			sw.swMsg.alert('Ошиюка', 'Для пациента не указаны обязательные данные: ' + chk.join(', ') + '. Внесите недостающие данные.');
			return;
		}
		
		chk = [];
		if (Ext.isEmpty(pdata.Polis_Num)) chk.push('Полис ОМС');
		if (Ext.isEmpty(pdata.Person_Snils)) chk.push('СНИЛС');
		if (Ext.isEmpty(pdata.Document_Num)) chk.push('Документ, удостоверяющий личность');
		if (Ext.isEmpty(pdata.Address_Address)) chk.push('Адрес');
		
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
	
	/**
	 * Контроль наличия действующего договора с ФСС по определенному Виду услуг
	 */
	checkLpuFSSContractType: function(LpuFSSContractType_id, callback) {
		var win = this;

		var lm = this.getLoadMask('Проверка МО');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=EvnErsBirthCertificate&m=checkLpuFSSContractType',
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				LpuFSSContractType_id: LpuFSSContractType_id
			},
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
	
	/**
	 * Контроль наличия для данного Родового сертификата постановки детей на учет в ФСС в статусе «Успешно зарегистрирован в ФСС».
	 */
	checkErsChildInfo: function(callback, ignore) {
		
		if (ignore) {
			callback();
			return;
		}
		
		// todo
		callback();
	},
	
	addTicket: function(TicketType) {
		
		var win = this, 
			wnd,
			ERSTicketType_id,
			LpuFSSContractType_id;
		
		switch (TicketType) {
			case 1:
				ERSTicketType_id = LpuFSSContractType_id = 1;
				doCheckErsChildInfo = false;
				wnd = 'swEvnErsTicket1EditWindow';
				break;
			case 2:
				ERSTicketType_id = LpuFSSContractType_id = 2;
				doCheckErsChildInfo = false;
				wnd = 'swEvnErsTicket2EditWindow';
				break;
			case 31:
				ERSTicketType_id = LpuFSSContractType_id = 3;
				doCheckErsChildInfo = true;
				wnd = 'swEvnErsTicket3EditWindow';
				break;
			case 32:
				ERSTicketType_id = LpuFSSContractType_id = 4;
				doCheckErsChildInfo = true;
				wnd = 'swEvnErsTicket3EditWindow';
				break;
		}
		
		win.checkLpuFSSContractType(LpuFSSContractType_id, function() {
			win._checkPerson(function() {
				win.checkErsChildInfo(function() {
					getWnd(wnd).show({
						action: 'add',
						EvnERSTicket_pid: win.EvnERSBirthCertificate_id,
						EvnERSBirthCertificate_Number: win.EvnERSBirthCertificate_Number,
						ERSTicketType_id: ERSTicketType_id,
						Person_id: win.Person_id,
						callback: function () {
							 win.TicketsGrid.getGrid().getStore().load();
						}
					});
				}, !doCheckErsChildInfo);
			});
		});
	},
	
	openTicket: function(action) {
		
		var win = this,
			wnd,
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
			action: action,
			EvnERSTicket_id: rec.get('EvnERSTicket_id'),
			callback: function () {
				win.TicketsGrid.loadData();
			}
		});
	},
	
	initComponent: function() {
		var win = this;
		
		this.TicketsGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			border: false,
			enableColumnHide: false,
			autoLoadData: false,
			obj_isEvn: true,
			object: 'EvnERSTicket',
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', handler: this.openTicket.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openTicket.createDelegate(this, ['view']) },
				{ name: 'action_delete', msg: 'Удалить талон?'},
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'EvnERSTicket_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnERSTicket_pid', type: 'int', hidden: true },
				{ name: 'ERSTicketType_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_id', type: 'int', hidden: true },
				{ name: 'ERSStatus_Code', type: 'int', hidden: true },
				{ name: 'ErsRequestStatus_id', type: 'int', hidden: true },
				{ name: 'ERSRequest_id', type: 'int', hidden: true },
				{ name: 'EvnERSTicket_setDate', type: 'string', header: 'Дата формирования', width: 130},
				{ name: 'ERSTicketType_Name', type: 'string', header: 'Тип талона', width: 90},
				{ name: 'ERSStatus_Name', type: 'string', header: 'Статус талона', width: 150},
				{ name: 'ErsRequestType_Name', type: 'string', header: 'Тип запроса', width: 150},
				{ name: 'ErsRequestStatus_Name', type: 'string', header: 'Статус запроса', width: 150},
				{ name: 'ErsRequestError', type: 'string', header: 'Ошибки обработки', id: 'autoexpand'},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				if (rowIdx >=0  && rec.get('ERSStatus_Code')) {
					win.TicketsGrid.getAction('action_delete').setDisabled(!rec.get('ERSStatus_Code').inlist([21, 22, 23, 24, 26]));
				}
			},
			// paging: false,
			dataUrl: '/?c=EvnErsTicket&m=loadList',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.TicketsGrid
			],
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}]
		});
		
		sw.Promed.swEvnErsTicketListWindow.superclass.initComponent.apply(this, arguments);
	}
});