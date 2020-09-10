/**
* Форма Талон 3
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsTicket3EditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Талон 3',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 555,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE })
			params = {};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		params.NewbornGridData = Ext.util.JSON.encode(getStoreRecords(this.NewbornGrid.getGrid().getStore(), {
			clearFilter: true
		}));
		
		this.NewbornGrid.getGrid().getStore().filterBy(function(record){
			if (record.data.Record_Status != 3) return true;
		});

		loadMask.show();	
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.callback();
					}	
				}
				else {
					Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
				}
				
			}
		});
	},
	
	show: function() {
		sw.Promed.swEvnErsTicket3EditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.Person_id = arguments[0].Person_id || null;
		this.EvnERSTicket_id = arguments[0].EvnERSTicket_id || null;
		this.EvnERSTicket_pid = arguments[0].EvnERSTicket_pid || null;
		this.EvnERSBirthCertificate_Number = arguments[0].EvnERSBirthCertificate_Number || null;
		this.ERSTicketType_id = arguments[0].ERSTicketType_id || null;
		
		base_form.reset();
		
		this.NewbornGrid.getGrid().getStore().removeAll();
		
		switch (this.action){
			case 'add':
				this.setTitle('Талон 3: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Талон 3: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Талон 3: Просмотр');
				this.enableEdit(false);
				break;
		}
		
		switch (this.action){
			case 'add':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
					params: {
						Lpu_id: getGlobalOptions().lpu_id,
						Person_id: this.Person_id
					},
					success: function (form, action) {
						base_form.findField('EvnERSTicket_pid').setValue(win.EvnERSTicket_pid);
						base_form.findField('EvnERSBirthCertificate_Number').setValue(win.EvnERSBirthCertificate_Number);
						base_form.findField('ERSTicketType_id').setValue(win.ERSTicketType_id);
						loadMask.hide();
						win.onLoad();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});	
				break;
			case 'edit':
			case 'view':
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsTicket&m=load',
					params: {
						EvnERSTicket_id: win.EvnERSTicket_id
					},
					success: function (form, action) {
						loadMask.hide();
						win.onLoad();
					},
					failure: function (form, action) {
						loadMask.hide();
						if (!action.result.success) {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							this.hide();
						}
					}
				});
				break;
		}
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.NewbornGrid.loadData({
			params: {
				EvnErsChild_pid: base_form.findField('EvnERSTicket_pid').getValue(),
				ERSTicketType_id: base_form.findField('ERSTicketType_id').getValue(), 
			},
			globalFilters: {
				EvnErsChild_pid: base_form.findField('EvnERSTicket_pid').getValue(),
				ERSTicketType_id: base_form.findField('ERSTicketType_id').getValue(), 
			}
		});
			
		if (this.action == 'view') return false;
		
		this.PersonPanel.checkFields();
	},	
	
	openErsNewborn: function(action) {
		var win = this,
			params = {},
			grid = this.NewbornGrid.getGrid();
		
		params.formParams = {};
		params.callback = function(data) {
			if (!data) {
				return false;
			}
			
			data.RecordStatus_Code = 0;
			
			var record = grid.getStore().getById(data.ErsChildInfo_id);
			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.RecordStatus_Code = 2;
				}
				record.set('ErsChildInfo_WatchEndDate', data['ErsChildInfo_WatchEndDate']);
				record.set('RecordStatus_Code', data.RecordStatus_Code);
				record.commit();
			}
		}
		
		params.action = action;
		
		if (action.inlist(['view','edit'])) {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}
			var rec = grid.getSelectionModel().getSelected();
			params.formParams = rec.data;
		}
		
		getWnd('swErsWatchEndDateEditWindow').show(params);
	},
	
	initComponent: function() {
		var win = this;
		
		this.PersonPanel = new sw.Promed.ErsPersonPanel({
			object: 'EvnERSTicket'
		});
		
		this.TicketInfo = new sw.Promed.Panel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			title: 'Сведения о талоне',
			items: [{
				xtype: 'textfield',
				width: 250,
				disabled: true,
				name: 'EvnERSBirthCertificate_Number',
				fieldLabel: 'Номер ЭРС'
			}]
		});
		
		this.NewbornGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			height: 160,
			pageSize: 20,
			border: true,
			enableColumnHide: false,
			autoLoadData: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', text: 'Дата окончания наблюдения', handler: this.openErsNewborn.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'ErsChildInfo_id', type: 'int', hidden: true, key: true },
				{ name: 'Person_BirthDay', type: 'string', header: 'Дата рождения', width: 100},
				{ name: 'Person_SurName', type: 'string', header: 'Фамилия', width: 150},
				{ name: 'Person_FirName', type: 'string', header: 'Имя', width: 150},
				{ name: 'Person_SecName', type: 'string', header: 'Отчество', width: 150},
				{ name: 'Polis_Num', type: 'string', header: 'Полис', width: 150},
				{ name: 'ErsChildInfo_WatchBegDate', type: 'string', header: 'Начало наблюдения', width: 130},
				{ name: 'ErsChildInfo_WatchEndDate', type: 'string', header: 'Окончание наблюдения', width: 150},
			],
			paging: false,
			title: 'Сведения о наблюдаемых детях',
			dataUrl: '/?c=EvnErsTicket&m=getErsChildInfo',
			totalProperty: 'totalCount'
		});

		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				name: 'EvnERSTicket_id',
				xtype: 'hidden'
			}, {
				name: 'EvnERSTicket_pid',
				xtype: 'hidden'
			}, {
				name: 'ERSTicketType_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			},
			this.PersonPanel, 
			this.TicketInfo, 
			this.NewbornGrid
			],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'EvnERSTicket_id' },
				{ name: 'EvnERSTicket_pid' },
				{ name: 'EvnERSBirthCertificate_Number' },
				{ name: 'ERSTicketType_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Person_SurName' },
				{ name: 'Person_FirName' },
				{ name: 'Person_SecName' },
				{ name: 'Person_BirthDay' },
				{ name: 'Person_Snils' },
				{ name: 'DocumentType_Name' },
				{ name: 'Document_Ser' },
				{ name: 'Document_Num' },
				{ name: 'Document_begDate' },
				{ name: 'OrgDep_Name' },
				{ name: 'Polis_Num' },
				{ name: 'Polis_begDate' },
				{ name: 'Address_Address' },
				{ name: 'Lpu_id' },
				{ name: 'EvnERSTicket_PolisNoReason' },
				{ name: 'EvnERSTicket_SnilsNoReason' },
				{ name: 'EvnERSTicket_DocNoReason' },
				{ name: 'EvnERSTicket_AddressNoReason' }
			]),
			url: '/?c=EvnErsTicket&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swEvnErsTicket3EditWindow.superclass.initComponent.apply(this, arguments);
	}
});