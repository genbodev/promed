/**
 * swHeadMedSpecTypeListWindow - Номенклатура главных внештатных специалистов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Alexander Kurakin
 * @version      05.2016
 * @comment      Префикс для id компонентов HMSTLW
 */
sw.Promed.swHeadMedSpecTypeListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: 'Номенклатура главных внештатных специалистов',
	buttonAlign: 'left',
	closeAction: 'hide',
	modal: true,
	draggable: true,
	resizable: false,
	height: 550,
	width: 800,
	getButtonSearch: function() {
		return Ext.getCmp('HMSTLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('HMSTLW_SearchFilters').getForm();
		base_form.reset();
		this.SearchFrame.ViewActions.action_view.setDisabled(true);
		this.SearchFrame.ViewActions.action_delete.setDisabled(true);
		this.SearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.SearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('HMSTLW_SearchFilters').getForm();
		
		var grid = this.SearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('HMSTLW_SearchFilters'));

		//log(post);

		if ( base_form.isValid() ) {
			this.SearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	doSelect: function() {
		
		var grid = this.SearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('HeadMedSpecType_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected(),
			wnd = this;
		this.onSelect({HeadMedSpecType_id:record.get('HeadMedSpecType_id'),HeadMedSpecType_Name:record.get('HeadMedSpecType_Name')});
		this.hide();
	},
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['add','view','edit'])) {
			return false;
		}
		var wnd = this;
		var form = this.FilterPanel.getForm();
		var grid = this.SearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = {};
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'add':
				getWnd('swHeadMedSpecTypeEditWindow').show(params);
				break;
            case 'edit':
			case 'view':
				if (getWnd('swHeadMedSpecTypeEditWindow').isVisible()) {
					getWnd('swHeadMedSpecTypeEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('HeadMedSpecType_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_vyibora_zapisi']);
					return false;
				}
				params.onHide = function(isChange) {
					if (isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};

				params.Person_id = selected_record.data.Person_id;
				params.HeadMedSpecType_Name = selected_record.data.HeadMedSpecType_Name;
				params.HeadMedSpecType_id = selected_record.data.HeadMedSpecType_id;
				params.Post_id = selected_record.data.Post_id;
				getWnd('swHeadMedSpecTypeEditWindow').show(params);
				break;
		}
	},
	initComponent: function() {
		var wnd = this;
		this.SearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.openWindow('add'); }},
                {name: 'action_edit', handler: function() { wnd.openWindow('edit'); }},
                {name: 'action_view', handler: function() { wnd.openWindow('view'); }},
				{name: 'action_delete', handler: this.deleteHeadMedSpecType.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', menuConfig: null,
                    handler: function()
                    {
						wnd.SearchFrame.printRecords();
                    }
                }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=HeadMedSpec&m=loadHeadMedSpecTypeList',
			id: 'HMSTLW_RegistrySearchGrid',
			paging: false,
			region: 'center',
			root: '',
			stringfields: [
				{name: 'HeadMedSpecType_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Post_id', type: 'int', hidden: true},
				{name: 'HeadMedSpecType_Name', type: 'string', header: 'Наименование', width: 400, id: 'autoexpand'},
				{name: 'Post_Name', type: 'string', header: 'Профиль', width: 300}
			],
			toolbar: true,
			onBeforeLoadData: function() {
                wnd.getButtonSearch().disable();
			},
			onLoadData: function() {
                wnd.getButtonSearch().enable();
			},
			onRowSelect: function(sm,index,record) {
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('HeadMedSpecType_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('HeadMedSpecType_id')) );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('HeadMedSpecType_id')) );
            },
            onEnter: function() {
                var record = this.getGrid().getSelectionModel().getSelected();
                if (record && record.get('HeadMedSpecType_id')) {
                    this.getAction('action_edit').execute();
                }
            },
            onDblClick: function() {
                this.onEnter();
			}
		});

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							labelWidth: 100,
							items:
							[{
								xtype: 'textfieldpmw',
								width: 200,
								name: 'HeadMedSpecType_Name',
								fieldLabel: 'Наименование',
								listeners: {
									'keydown': wnd.onKeyDown
								}
							}]
						}]
					}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Поиск',
						iconCls: 'search16',
						id: 'HMSTLW_SearchButton',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Сброс',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			id: 'HMSTLW_SearchFilters',
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel,
				this.FilterButtonsPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    wnd.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
                    wnd.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
                    wnd.buttons[wnd.buttons.length - 2].focus();
				},
				onTabAction: function() {
                    
				},
				text: BTN_FRMCLOSE
			}],
			layout: 'border',
			items:
				[
					wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchFrame]
					}]
				}
				]
		});

		sw.Promed.swHeadMedSpecTypeListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'restore': function(win) {
			win.findById('HMSTLW_SearchFilters').doLayout();
		}
	},
	show: function() {
		sw.Promed.swHeadMedSpecTypeListWindow.superclass.show.apply(this, arguments);
		var wnd = this;
        
		var base_form = this.findById('HMSTLW_SearchFilters').getForm();

		this.restore();
		this.center();
		this.doReset();
		if (arguments[0].onSelect)
		{
			this.onSelect = arguments[0].onSelect;
		}
		
		this.doLayout();

		this.doSearch();
	},
	deleteHeadMedSpecType: function() {
		var grid = this.SearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('HeadMedSpecType_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected(),
			wnd = this;
		wnd.getLoadMask('Проверка записи').show();
		Ext.Ajax.request({
			url: '/?c=HeadMedSpec&m=checkHeadMedSpecType',
			params: {
				HeadMedSpecType_id: grid.getSelectionModel().getSelected().get('HeadMedSpecType_id')
			},
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				if (success) {	
					var obj = Ext.util.JSON.decode(response.responseText);
					if(obj[0].cnt == 0){
						Ext.Msg.show({
							title: lang['vopros'],
							msg: 'Удалить выбранную запись?',
							buttons: Ext.Msg.YESNO,
							fn: function(btn) {
								if (btn === 'yes') {
									wnd.getLoadMask(lang['udalenie']).show();
									Ext.Ajax.request({
										url: '/?c=HeadMedSpec&m=deleteHeadMedSpecType',
										params: {
											HeadMedSpecType_id: grid.getSelectionModel().getSelected().get('HeadMedSpecType_id')
										},
										callback: function(options, success, response) {
											wnd.getLoadMask().hide();
											if (success) {	
												var obj = Ext.util.JSON.decode(response.responseText);
												if( obj.success )
													grid.getStore().remove(record);
											} else {
												sw.swMsg.alert(lang['oshibka'], 'Ошибка при удалении записи');
											}
										}
									});
								}
							},
							icon: Ext.MessageBox.QUESTION
						});
					} else {
						sw.swMsg.alert(lang['oshibka'], 'Удаление данных не возможно, т.к. в регистре главных внештатных специалистов есть врач с выбранной специальностью.');
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], 'Ошибка при удалении специальности');
				}
			}
		});
	}
});
