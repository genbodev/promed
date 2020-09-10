/**
* Форма Постановка детей на учет
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnErsChildEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Постановка детей на учет',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 650,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',

	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE }),
			params = {
				Lpu_id: base_form.findField('Lpu_id').getValue(),
				EvnErsChild_OrgName: base_form.findField('Lpu_id').getFieldValue('Lpu_Name'),
				EvnErsChild_OrgINN: base_form.findField('Org_INN').getValue(),
				EvnErsChild_OrgOGRN: base_form.findField('Org_OGRN').getValue(),
				EvnErsChild_OrgKPP: base_form.findField('Org_KPP').getValue()
			};
		
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
		
		params.ChildGridData = Ext.util.JSON.encode(getStoreRecords(this.ChildGrid.getGrid().getStore(), {
			clearFilter: true
		}));
		
		this.ChildGrid.getGrid().getStore().filterBy(function(record){
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
		sw.Promed.swEvnErsChildEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.EvnErsChild_id = arguments[0].EvnErsChild_id || null;
		this.EvnErsChild_pid = arguments[0].EvnErsChild_pid || null;
		this.EvnERSBirthCertificate_Number = arguments[0].EvnERSBirthCertificate_Number || null;
		this.Person_id = arguments[0].Person_id || null;
		
		base_form.reset();
		this.ChildGrid.getGrid().getStore().removeAll();
		
		switch (this.action){
			case 'add':
				this.setTitle('Постановка детей на учет: Добавление');
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle('Постановка детей на учет: Редактирование');
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle('Постановка детей на учет: Просмотр');
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
						loadMask.hide();
						base_form.findField('EvnErsChild_pid').setValue(win.EvnErsChild_pid);
						base_form.findField('EvnERSBirthCertificate_Number').setValue(win.EvnERSBirthCertificate_Number);
						win.onLoad();
						base_form.findField('LpuFSSContract_id').focus();
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
				win.ChildGrid.loadData({
					params: {EvnErsChild_id: win.EvnErsChild_id},
					globalFilters: {EvnErsChild_id: win.EvnErsChild_id}
				});
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				base_form.load({
					url: '/?c=EvnErsChild&m=load',
					params: {
						EvnErsChild_id: win.EvnErsChild_id
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
		
		this.LpuPanel.loadLpuFSSContractCombo();
	},	
	
	addErsChildInfo: function() {
		var win = this,
			grid = this.ChildGrid.getGrid();

		getWnd('swPersonSearchWindow').show({
			searchMode: 'all',
			onSelect: function(pdata) {
				getWnd('swPersonSearchWindow').hide();
				getWnd('swErsChildInfoEditWindow').show({
					action: 'add',
					formParams: {
						Person_id: pdata.Person_id,
						Person_Birthday: pdata.Person_Birthday,
						Person_Firname: pdata.Person_Firname,
						Person_Secname: pdata.Person_Secname,
						Person_Surname: pdata.Person_Surname,
						Polis_Num: pdata.Polis_Num
					},
					callback: function (data) {
						if (!data) {
							return false;
						}
						
						if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('ErsChildInfo_id')) {
							grid.getStore().removeAll();
						}
						data.ErsChildInfo_id = -swGenTempId(grid.getStore());
						grid.getStore().loadData([ data ], true);
					}
				});
			}
		});
		
		
	},	
	
	openErsChildInfo: function(action) {
		var win = this,
			params = {},
			grid = this.ChildGrid.getGrid();
		
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
				var grid_fields = [];
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});
				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}
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
		
		getWnd('swErsChildInfoEditWindow').show(params);
	},
	
	initComponent: function() {
		var win = this;
		
		this.LpuPanel = new sw.Promed.ErsLpuPanel;
		
		this.PersonPanel = new sw.Promed.ErsPersonPanel({
			object: 'EvnErsChild'
		});
		
		this.ChildGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			height: 160,
			pageSize: 20,
			border: true,
			useEmptyRecord: false,
			enableColumnHide: false,
			autoLoadData: false,
			object: 'ErsChildInfo',
			actions: [
				{ name: 'action_add', handler: this.addErsChildInfo.createDelegate(this) },
				{ name: 'action_edit', handler: this.openErsChildInfo.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'ErsChildInfo_id', type: 'int', hidden: true, key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Person_Birthday', type: 'string', header: 'Дата рождения', width: 120},
				{ name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 120},
				{ name: 'Person_Firname', type: 'string', header: 'Имя', width: 120},
				{ name: 'Person_Secname', type: 'string', header: 'Отчество', width: 120},
				{ name: 'Polis_Num', type: 'string', header: 'Полис ОМС', width: 150},
				{ name: 'ERSChildInfo_WatchBegDate', type: 'string', header: 'Дата постановки на учет', id: 'autoexpand'},
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
			],
			paging: false,
			title: 'Сведения о наблюдаемых детях',
			dataUrl: '/?c=EvnErsChild&m=loadChildGrid',
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
				name: 'EvnErsChild_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnErsChild_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			},
			this.LpuPanel,
			this.PersonPanel,
			{
				xtype: 'textfield',
				width: 250,
				disabled: true,
				name: 'EvnERSBirthCertificate_Number',
				fieldLabel: 'Номер ЭРС'
			},
			this.ChildGrid
			],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'EvnErsChild_id' },
				{ name: 'EvnErsChild_pid' },
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
				{ name: 'Org_INN' },
				{ name: 'Org_KPP' },
				{ name: 'Org_OGRN' },
				{ name: 'LpuFSSContract_id' },
				{ name: 'EvnErsChild_PolisNoReason' },
				{ name: 'EvnErsChild_SnilsNoReason' },
				{ name: 'EvnErsChild_DocNoReason' },
				{ name: 'EvnErsChild_AddressNoReason' }
			]),
			url: '/?c=EvnErsChild&m=save'
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
				handler: function () {
					this.doSave({send2fss: true});
				}.createDelegate(this),
				text: 'Отправить в ФСС'
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
		
		sw.Promed.swEvnErsChildEditWindow.superclass.initComponent.apply(this, arguments);
	}
});