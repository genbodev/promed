/**
* Форма Талон 2
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsTicket2EditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Талон 2',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 600,
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
		sw.Promed.swEvnErsTicket2EditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.Person_id = arguments[0].Person_id || null;
		this.EvnERSTicket_id = arguments[0].EvnERSTicket_id || null;
		this.EvnERSTicket_pid = arguments[0].EvnERSTicket_pid || null;
		this.EvnERSBirthCertificate_Number = arguments[0].EvnERSBirthCertificate_Number || null;
		
		base_form.reset();
		this.NewbornGrid.getGrid().getStore().removeAll();
		
		this.NewbornGrid.addActions({
			name: 'newborn_add_menu', 
			iconCls: 'add16',
			text: 'Добавить', 
			menu: [{
				name: 'new', 
				text: 'Новый',
				handler: function() {
					win.openErsNewborn('add');
				}
			}, {
				name: 'fromspec', 
				text: 'Из специфики родов',
				handler: function() {
					win.addFromPersonNewborn();
				}
			}]
		}, 0);
		
		switch (this.action){
			case 'add':
				this.setTitle('Талон 2: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Талон 2: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Талон 2: Просмотр');
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
						Person_id: this.Person_id,
						EvnERSBirthCertificate_id: this.EvnERSTicket_pid
					},
					success: function (form, action) {
						base_form.findField('EvnERSTicket_pid').setValue(win.EvnERSTicket_pid);
						base_form.findField('EvnERSBirthCertificate_Number').setValue(win.EvnERSBirthCertificate_Number);
						base_form.findField('ERSTicketType_id').setValue(2);
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
				win.NewbornGrid.loadData({
					params: {EvnERSTicket_id: win.EvnERSTicket_id},
					globalFilters: {EvnERSTicket_id: win.EvnERSTicket_id}
				});
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
			
			var record = grid.getStore().getById(data.ERSNewborn_id);
			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.RecordStatus_Code = 2;
				}
				var grid_fields = [];
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});
				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}
				record.commit();
			} else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('ERSNewborn_id')) {
					grid.getStore().removeAll();
				}
				data.ERSNewborn_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([ data ], true);
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
		
		getWnd('swErsNewbornEditWindow').show(params);
	},
	
	deleteErsNewborn: function() {
		var grid = this.NewbornGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record) return false;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить сведения о новорожденном?',
			title: lang['vopros']
		});
	},	
	
	addFromPersonNewborn: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm(),
			grid = this.NewbornGrid.getGrid();
		
		var lm = this.getLoadMask(LOAD_WAIT);
		lm.show();
		Ext.Ajax.request({
			url: '/?c=EvnErsTicket&m=getPersonNewborn',
			params: {
				BirthSpecStac_id: 1559,
				Person_id: base_form.findField('Person_id').getValue()
			},
			method: 'post',
			callback: function(opt, success, response) {
				lm.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.length) {
					response_obj.forEach(function(item) {
						var index = grid.getStore().findBy(function(record) {
							return (
								(!!record.get('PersonNewborn_id') && record.get('PersonNewborn_id') == item.PersonNewborn_id) || 
								(!!record.get('ChildDeath_id') && record.get('ChildDeath_id') == item.ChildDeath_id)
							);
						});

						if ( index == -1 ) {
							item.ERSNewborn_id = -swGenTempId(grid.getStore());
							grid.getStore().loadData([item], true);
						}
					});
				} 
				else if (response_obj.Error_Message) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Message);
				}
			}
		});
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
			collapsible: false,
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			title: 'Сведения о талоне',
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
						xtype: 'textfield',
						width: 250,
						disabled: true,
						name: 'EvnERSBirthCertificate_Number',
						fieldLabel: 'Номер ЭРС'
					}, {
						xtype: 'swdatefield',
						width: 100,
						name: 'EvnERSTicket_ArrivalDT',
						fieldLabel: 'Дата поступления на роды'
					}, {
						layout: 'column',
						border: false,
						defaults: {
							border: false,
							style: 'margin-right: 10px;'
						},
						items: [{
							layout: 'form',
							items: [{
								xtype: 'swdatefield',
								width: 100,
								name: 'EvnERSTicket_BirthDate',
								fieldLabel: 'Дата и время родов'
							}]
						}, {
							layout: 'form',
							labelWidth: 160,
							items: [{
								xtype: 'swtimefield',
								width: 80,
								name: 'EvnERSTicket_BirthTime',
								hideLabel: true
							}]
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 160,
					items: [{
						xtype: 'swdiagcombo',
						width: 340,
						name: 'Diad_id',
						MorbusType_SysNick: 'pregnancy',
						fieldLabel: 'Исход родов по МКБ-10'
					}, {
						xtype: 'textfield',
						width: 340,
						name: 'EvnERSTicket_DeathReason',
						fieldLabel: 'Причина смерти матери'
					}, {
						border: false,
						layout: 'form',
						labelWidth: 400,
						items: [{
							xtype: 'numberfield',
							width: 100,
							name: 'EvnERSTicket_ChildrenCount',
							fieldLabel: 'Число детей пациентки (включая рожденных ранее)'
						}]
					}]
				}]
			}]
		});
		
		this.NewbornGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			height: 160,
			pageSize: 20,
			border: true,
			useEmptyRecord: false,
			enableColumnHide: false,
			autoLoadData: false,
			object: 'ERSNewborn',
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', handler: this.openErsNewborn.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openErsNewborn.createDelegate(this, ['view']) },
				{ name: 'action_delete', handler: this.deleteErsNewborn.createDelegate(this) },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'ERSNewborn_id', type: 'int', hidden: true, key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'PersonNewborn_id', type: 'int', hidden: true },
				{ name: 'ChildDeath_id', type: 'int', hidden: true },
				{ name: 'ERSNewborn_Gender', type: 'int', hidden: true },
				{ name: 'Sex_Name', type: 'string', header: 'Пол', width: 120},
				{ name: 'ERSNewborn_Height', type: 'string', header: 'Рост', width: 120},
				{ name: 'ERSNewborn_Weight', type: 'string', header: 'Вес', width: 120},
				{ name: 'ERSNewborn_DeathReason', type: 'string', header: 'Причина смерти', id: 'autoexpand'},
			],
			paging: false,
			title: 'Сведения о новорожденных',
			dataUrl: '/?c=EvnErsTicket&m=loadNewbornGrid',
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
				{ name: 'EvnERSTicket_ArrivalDT' },
				{ name: 'EvnERSTicket_BirthDate' },
				{ name: 'EvnERSTicket_BirthTime' },
				{ name: 'EvnERSTicket_DeathReason' },
				{ name: 'EvnERSTicket_ChildrenCount' },
				{ name: 'Diag_id' },
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
		
		sw.Promed.swEvnErsTicket2EditWindow.superclass.initComponent.apply(this, arguments);
	}
});