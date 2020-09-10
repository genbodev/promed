/**
* Документы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      декабрь.2012
*/
sw.Promed.swMinzdravDLODocumentsWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swMinzdravDLODocumentsWindow').show();
	id: 'swMinzdravDLODocumentsWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	getButtonSearch: function() {
		return Ext.getCmp(this.id + 'BtnSearch');
	},
	doSign: function(record) {
		var wnd = this;

		var url = null;
		var params = new Object();

		params.WhsDocumentType_id = record.get('WhsDocumentType_id');

		switch (record.get('WhsDocumentType_id')) {
			case '7':
			case '8':
			case '17':
			case '23':
				url = '?c=WhsDocumentOrderAllocation&m=sign';
				params.WhsDocumentOrderAllocation_id = record.get('WhsDocumentUc_id');
				break;
			case '12':
			case '13':
				url = '?c=WhsDocumentOrderReserve&m=sign';
				params.WhsDocumentOrderReserve_id = record.get('WhsDocumentUc_id');
			break;
		}

		if (url != null) {
			Ext.Ajax.request({
				url: url,
				params: params,
				success: function(response, action) {
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							Ext.Msg.alert(lang['soobschenie'], lang['dokument_uspeshno_podpisan']);
							wnd.doSearch();
						} else if(answer.Error_Msg) {
							//Ext.Msg.alert('Ошибка', answer.Error_Msg);
						}
					} else {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_podpisanii_otsutstvuet_otvet_servera']);
					}
				}
			});
		}
	},
	doReset: function() {
		
		var base_form = this.FilterPanel.getForm();
		base_form.reset();
				
	},
	doSearch: function(params) {
		
		var base_form = this.FilterPanel.getForm();
		
		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('MinzdravDLODocumentsForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.RootViewFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('MinzdravDLODocumentsForm'));

		post.limit = 100;
		post.start = 0;

        this.object_type_id = base_form.findField('WhsDocumentType_id').getValue();

		switch (this.object_type_id) {
			case 7:
			case 8:
			case 23:
				this.object = 'WhsDocumentOrderAllocation';
				break;

			case 12:
			case 13:
				this.object = 'WhsDocumentOrderReserve';
				break;

			case 15:
				this.object = 'SupplierAllocation';
				break;

			case 17:
				this.object = 'FarmacyDrugAllocation';
				break;
		}

		switch (base_form.findField('WhsDocumentType_id').getValue()) {
			case 15:
			case 17:
				this.object_loadlist_url = '/?c=WhsDocumentOrderAllocation&m=loadList';
				this.object_delete_url = '/?c=WhsDocumentOrderAllocation&m=delete';
				break;

			default:
				this.object_loadlist_url = '/?c=' + this.object + '&m=loadList';
				this.object_delete_url = '/?c=' + this.object + '&m=delete';
				break;
		}

		if ( base_form.isValid() ) {
			this.RootViewFrame.ViewActions.action_refresh.setDisabled(false);
			this.RootViewFrame.removeAll();
			this.RootViewFrame.loadData({
				url: this.object_loadlist_url,
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post,
				globalFilters: post
			});
		}
		
	},
	show: function() {
		sw.Promed.swMinzdravDLODocumentsWindow.superclass.show.apply(this, arguments);
		
		var base_form = this.FilterPanel.getForm();
		var that = this;

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		this.WhsDocumentType_id = null;		
		this.object = null;
		this.object_type_id = null;
		this.object_loadlist_url = null;
		this.object_delete_url = null;
        this.onlyView = false;

		if (arguments[0].WhsDocumentType_id && arguments[0].WhsDocumentType_id > 0) {
			this.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
		}

        if(arguments[0] && arguments[0].onlyView){
            this.onlyView = true;
        }

        this.RootViewFrame.addActions({
            name:'action_mdd_actions',
            text:lang['deystviya'],
            menu: [{
                name: 'action_mdd_sign',
                text: lang['podpisat'],
                tooltip: lang['podpisat'],
                handler: function() {
                    var record = that.RootViewFrame.getGrid().getSelectionModel().getSelected();
                    if (record.get('WhsDocumentUc_id') > 0) {
                        that.doSign(record);
                    }
                },
                icon: '/img/icons/signature16.png'
            }],
            iconCls: 'actions16'
        });

        this.RootViewFrame.setReadOnly(this.onlyView);
        this.RootViewFrame.ViewActions.action_mdd_actions.setDisabled(true);

		if (!Ext.isEmpty(this.WhsDocumentType_id)) {
			base_form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
		}
		
		this.FilterPanel.fieldSet.expand();
		
		this.doLayout();
		this.doSearch();
	},
	initComponent: function() {
		var curWnd = this;

        //определяем список допустимых типов документов
        this.allowed_type_id = [
            7,  //Распоряжение на выдачу разнарядки на выписку рецептов
            8,  //Распоряжение на отзыв разнарядки на выписку рецептов
            12, //Распоряжение на включение в резерв
            13, //Распоряжение на исключение из резерва
            15, //План поставок
            17,
			23 // Распоряжение на ввод остатков по разнарядке
        ];

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			id: 'MinzdravDLODocumentsForm',
			owner: curWnd,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					curWnd.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			labelWidth: 120,
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 500,
						labelWidth: 120,
						items: [{
							fieldLabel: lang['period'],
							name: 'WhsDocumentUc_Date_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							xtype: 'daterangefield'
						}]
					}, {
						layout: 'form',
						width: 450,
						labelWidth: 180,
						items: [{
							fieldLabel: lang['istochnik_finansirovaniya'],
							hiddenName: 'DrugFinance_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'DrugFinance',
							width: 250
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						width: 500,
						labelWidth: 120,
						items: [{
							fieldLabel: lang['vid_dokumenta'],
							hiddenName: 'WhsDocumentType_id',
							xtype: 'swcommonsprcombo',
							loadParams: {params: {where: ' where WhsDocumentType_Code > 6'}},
							comboSubject: 'WhsDocumentType',
							width: 350,
							listWidth: 400,
							typeCode: 'int',
							allowBlank: false,
                            onLoadStore: function(store) {
                                store.each(function(record) {
                                    if (curWnd.allowed_type_id.indexOf(record.get('WhsDocumentType_id')) < 0) {
                                        store.remove(record);
                                    }
                                });
                            }
						}]
					}, {
						layout: 'form',
						labelWidth: 180,
						width: 450,
						items: [{
							fieldLabel: lang['statya_rashoda'],
							hiddenName: 'WhsDocumentCostItemType_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'WhsDocumentCostItemType',
							width: 250,
							typeCode: 'int'
						}]
					},{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: curWnd.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								curWnd.doSearch();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								curWnd.doReset();
							}
						}]
					}]
				}]
			}
		});
		
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: false, iconCls: 'x-btn-text', handler: function() {
					getWnd('sw'+curWnd.object+'EditWindow').show({
						action: 'add',
						WhsDocumentType_id: curWnd.object_type_id,
						callback: function() {
							this.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this)
					});
				}.createDelegate(this)},
				{name: 'action_edit', disabled: false, handler: function() { 
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					getWnd('sw'+curWnd.object+'EditWindow').show({
						action: 'edit',
						WhsDocumentUc_id: selected_record.data.WhsDocumentUc_id,
                        WhsDocumentType_id: selected_record.data.WhsDocumentType_id,
						onHide: function() {
							this.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this),
						callback: function() {
							this.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this)
					});
				}.createDelegate(this)},
				{name: 'action_view', handler: function() { 
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					getWnd('sw'+curWnd.object+'EditWindow').show({
						action: 'view',
						WhsDocumentUc_id: selected_record.data.WhsDocumentUc_id,
                        WhsDocumentType_id: selected_record.data.WhsDocumentType_id,
						callback: function() {
							this.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this)
					});
				}.createDelegate(this)},
				{name: 'action_delete', handler: function() { 
					grid = this.RootViewFrame.getGrid();
					var selected_record = grid.getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								Ext.Ajax.request({
									callback: function(options, success, response) {
										if ( success ) {
											var response_obj = Ext.util.JSON.decode(response.responseText);

											if ( response_obj.success == false ) {
												sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_dokumenta']);
											}
											else {
												grid.getStore().remove(selected_record);

												if ( grid.getStore().getCount() == 0 ) {
													LoadEmptyRow(grid, 'data');
												}
											}

											grid.focus();
											grid.getSelectionModel().selectFirstRow();
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_dokumenta_voznikli_oshibki']);
										}
									},
									params: {
										'WhsDocumentUc_id': selected_record.data.WhsDocumentUc_id
									},
									url: curWnd.object_delete_url
								});
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: lang['udalit_dokument'],
						title: lang['vopros']
					});
				}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: null,
			id: 'MinzdravDLODocumentsSearchGrid',
			object: '',
			pageSize: 100,
			paging: true,
			region: 'center',
			//root: 'data',
			stringfields: [
				{name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentType_id', hidden: true},
				{name: 'WhsDocumentType_Name', type: 'string', header: lang['vid_dokumenta'], width: 200},
				{name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_dokumenta'], width: 100},
				{name: 'WhsDocumentUc_Date', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: lang['data'], width: 100},
				{name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width: 160},
				{name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashoda'], width: 150},
				{name: 'WhsDocumentUc_Sum', type: 'money', header: lang['summa_rub'], width: 150},
				{name: 'WhsDocumentStatusType_id', hidden: true},
				{name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width: 150},
				{name: 'WhsDocumentUc_updDT', type: 'string', header: lang['vremya_i_data_izmeneniya'], width: 150}
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onRowSelect: function(sm,rowIdx,record) {				
				if (this.readOnly || Ext.isEmpty(record.get('WhsDocumentUc_id')) || record.get('WhsDocumentStatusType_id') > 1 ) {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				} else {
                    this.ViewActions.action_edit.setDisabled(false);
                    this.ViewActions.action_delete.setDisabled(false);
				}

                if (!this.readOnly && !Ext.isEmpty(record.get('WhsDocumentUc_id'))) {
                    this.ViewActions.action_mdd_actions.setDisabled(false);
                } else {
                    this.ViewActions.action_mdd_actions.setDisabled(true);
                }
			},
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if (!this.ViewActions.action_edit.isDisabled()) {
					this.ViewActions.action_edit.execute();
				} else {
					this.ViewActions.action_view.execute();
				}
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_TTMGW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('MinzdravDLODocumentsForm');
				}
				return this.filterForm;
			},
			menuPrintForm: null,
			items: [this.FilterPanel,this.RootViewFrame]
		});
		
		sw.Promed.swMinzdravDLODocumentsWindow.superclass.initComponent.apply(this, arguments);
	},
	title: lang['dokumentyi'],
	width: 800
});