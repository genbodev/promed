/**
 * swPersonCardAttachListWindow - окно "Список заявлений о выборе МО"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.11.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachListWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 500,
	width: 800,
	id: 'swPersonCardAttachListWindow',
	title: 'Список заявлений о выборе МО',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	show: function()
	{
		sw.Promed.swPersonCardAttachListWindow.superclass.show.apply(this, arguments);

		var base_form = this.FiltersPanel.getForm();
		var grid = this.GridPanel.getGrid();

		base_form.reset();

		var date = new Date().format('d.m.Y');
		base_form.findField('PersonCardAttach_setDate_Range').setValue(date+' - '+date);

		if (arguments[0] && arguments[0].filterParams) {
			 base_form.setValues(arguments[0].filterParams);
		}

		if (!this.GridPanel.getAction('action_getrpnstatus')) {
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_getrpnstatus',
				text: 'Получить статус РПН',
				handler: function() {
					this.getRPNStatus();
				}.createDelegate(this)
			});
		}
		if (!this.GridPanel.getAction('action_sendtorpn')) {
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_sendtorpn',
				text: 'Отправить в РПН',
				handler: function() {
					this.sendToRPN();
				}.createDelegate(this)
			});
		}

		this.doSearch();
	},

	doSearch: function(reset, callback) {
		var grid = this.GridPanel.getGrid();
		var base_form = this.FiltersPanel.getForm();

		if (reset) {
			base_form.reset();

			var date = new Date().format('d.m.Y');
			base_form.findField('PersonCardAttach_setDate_Range').setValue(date+' - '+date);
		}

		grid.getStore().baseParams = base_form.getValues();
		grid.getStore().load({callback: callback || Ext.emptyFn});
	},

	sendToRPN: function() {
		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('PersonCardAttach_id'))) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Отправка заявления на прикрепление в РПН..."});
		loadMask.show();

		Ext.Ajax.request({
			params: {PersonCardAttach_id: record.get('PersonCardAttach_id')},
			url: '/?c=PersonCard&m=sendPersonCardAttachToRPN',
			callback: function(opt, scs, response) {
				loadMask.hide();
				this.GridPanel.getAction('action_refresh').execute();
			}.createDelegate(this),
		});
		return true;
	},

	getRPNStatus: function() {
		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('PersonCardAttach_id'))) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение статуса заявления из РПН..."});
		loadMask.show();

		Ext.Ajax.request({
			params: {PersonCardAttach_id: record.get('PersonCardAttach_id')},
			url: '/?c=PersonCard&m=getPersonCardAttachStatusFromRPN',
			callback: function(opt, scs, response) {
				loadMask.hide();
				this.GridPanel.getAction('action_refresh').execute();
			}.createDelegate(this),
		});
		return true;
	},

	openPersonCardAttachEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var base_form = this.FiltersPanel.getForm();
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;
		params.formParams = {};

		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		};

		if (action == 'add') {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
				return false;
			}

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();

					params.formParams.Person_id = person_data.Person_id;
					getWnd('swPersonCardAttachEditWindow').show(params);
				},
				personFirname: base_form.findField('Person_FirName').getValue(),
				personSecname: base_form.findField('Person_SecName').getValue(),
				personSurname: base_form.findField('Person_SurName').getValue(),
				searchMode: 'all'
			});
		} else {
			var record = grid.getSelectionModel().getSelected();
			if (!record) {
				return false;
			}
			params.formParams.PersonCardAttach_id = record.get('PersonCardAttach_id');

			getWnd('swPersonCardAttachEditWindow').show(params);
		}
		return true;
	},

	deletePersonCardAttach: function() {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PersonCardAttach_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {PersonCardAttach_id: record.get('PersonCardAttach_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=PersonCard&m=deletePersonCardAttachRPN'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:'Вы хотите удалить запись?',
			title:'Подтверждение'
		});
	},

	initComponent: function()
	{
		this.FiltersPanel = new Ext.FormPanel({
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(false, f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				title: 'Фильтр',
				autoHeight: true,
				labelAlign: 'right',
				collapsible: true,
				listeners: {
					collapse: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this),
					expand: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this)
				},
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 70,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'textfield',
							name: 'Person_SurName',
							fieldLabel: 'Фамилия'
						}, {
							xtype: 'textfield',
							name: 'Person_FirName',
							fieldLabel: 'Имя'
						}, {
							xtype: 'textfield',
							name: 'Person_SecName',
							fieldLabel: 'Отчество'
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 170,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'daterangefield',
							name: 'Person_BirthDay_Range',
							fieldLabel: 'Дата рождения',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							xtype: 'daterangefield',
							name: 'PersonCardAttach_setDate_Range',
							fieldLabel: 'Период подачи заявления',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							xtype: 'swlpucombo',
							hiddenName: 'Lpu_aid',
							fieldLabel: 'МО, принявшая заявление',
							listWidth: 400
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 150,
						defaults: {
							anchor: '100%'
						},
						items: [{
							editable: false,
							xtype: 'swpersoncardattachstatustypecombo',
							hiddenName: 'PersonCardAttachStatusType_id',
							fieldLabel: 'Статус заявления'
						}, {
							xtype: 'textfield',
							name: 'GetAttachment_Number',
							fieldLabel: 'Номер запроса (РПН)'
						}, {
							editable: false,
							xtype: 'swgetattachmentcasecombo',
							hiddenName: 'GetAttachmentCase_id',
							fieldLabel: 'Причина прикрепления',
							onLoadStore: function(store) {
								this.lastQuery = '';
								store.filterBy(function(rec){
									var code = rec.get('GetAttachmentCase_Code');
									return (code && code.inlist([100,200]));
								});
							}
						}]
					}]
				}]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + '_GridPanel',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			pageSize: 100,
			border: false,
			actions: [
				{ name: 'action_add', handler: function() {this.openPersonCardAttachEditWindow('add')}.createDelegate(this)},
				{ name: 'action_edit', handler: function(){this.openPersonCardAttachEditWindow('edit')}.createDelegate(this)},
				{ name: 'action_view', handler: function(){this.openPersonCardAttachEditWindow('view')}.createDelegate(this)},
				{ name: 'action_delete', handler: function(){this.deletePersonCardAttach()}.createDelegate(this)},
				{ name: 'action_refresh'},
				{ name: 'action_print', menuConfig: {
					printPersonCardAttach: {text: 'Печать заявления о выборе МО', handler: function(){
						var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

						if (!record || Ext.isEmpty(record.get('PersonCardAttach_id'))) {
							return false;
						}

						printBirt({
							'Report_FileName': 'han_EvnPrint_PersonCardAttach.rptdesign',
							'Report_Params': '&paramPersonCardAttach_id=' + record.get('PersonCardAttach_id'),
							'Report_Format': 'pdf'
						});
					}.createDelegate(this)}
				}}
			],
			autoLoadData: false,
			stripeRows: true,
			root: 'data',
			stringfields: [
				{name: 'PersonCardAttach_id', type: 'int', hidden: true, key: true },
				{name: 'PersonCardAttachStatusType_id', type: 'int', hidden: true},
				{name: 'PersonCardAttachStatusType_Code', type: 'int', hidden: true},
				{name: 'PersonCardAttach_setDate', header: 'Дата заявления', width: 110, type:'date'},
				{name: 'Person_Fio', header: 'ФИО пациента', width: 300 },
				{name: 'GetAttachmentCase_Name', header: 'Причина прикрепления', width: 300 },
				{name: 'GetAttachment_Number', header: 'Номер запроса (РПН)', width: 100 },
				{name: 'Lpu_aNick', type: 'string', header: 'МО, принявшая заявление ', id: 'autoexpand'},
				{name: 'PersonCardAttachStatusType_Name', type: 'string', header: 'Статус заявления', width: 200}
			],
			paging: true,
			dataUrl: '/?c=PersonCard&m=loadPersonCardAttachGrid',
			totalProperty: 'totalCount',
			onRowSelect: function(sm, index, record) {
				var status = record.get('PersonCardAttachStatusType_Code');

				this.setActionDisabled('action_edit', (status!=1));
				this.setActionDisabled('action_delete', (status!=1));
				this.setActionDisabled('action_sendtorpn', (!status || !status.inlist([1,4])));
				this.setActionDisabled('action_getrpnstatus', (status!=5));
			},
			onDblClick: function() {
				this.getAction('action_view').execute();
			},
			onEnter: function() {
				this.getAction('action_view').execute();
			}
		});

		Ext.apply(this, {
			layout: 'border',
			items: [this.FiltersPanel, this.GridPanel],
			buttons: [{
				handler: function() {
					this.doSearch(false);
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'PCALW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doSearch(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				id: 'PCALW_ResetButton',
				text: BTN_FRMRESET
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: 'Закрыть',
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swPersonCardAttachListWindow.superclass.initComponent.apply(this, arguments);
	}
});