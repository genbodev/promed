/**
* swWhsDocumentSupplyAdditionalViewWindow - окно просмотра списка дополнительных контрактов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Salakhov Rustam
* @version      27.08.2015
*/
sw.Promed.swWhsDocumentSupplyAdditionalViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentSupplyAdditionalViewWindow',
	title: lang['dopolnitelnyie_soglasheniya'],
	layout: 'border',
	maximizable: false,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	setOrg: function(combo, data) { //вспомогательная функция для комбобоксов для выбора организации
		if (data && data['Org_id'] > 0) {
			combo.setValue(data['Org_id']);
			combo.setRawValue(data['Org_Name']);
		}
	},
	setDefaultDateRange: function() {
		var date_fld = this.form.findField('WhsDocumentUc_DateRange');
		var start_date = new Date();
		var month = start_date.getMonth();
		start_date.setDate(1);
		start_date.setMonth(month - (month%3));
		date_fld.setValue(Ext.util.Format.date(start_date.clearTime(), 'd.m.Y')+' - '+Ext.util.Format.date(start_date.add(Date.MONTH, 3).clearTime(), 'd.m.Y'))
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},	
	show: function() {
		sw.Promed.swWhsDocumentSupplyAdditionalViewWindow.superclass.show.apply(this, arguments);
		this.onlyView = false;
        this.ARMType = null;

        if(arguments[0] && arguments[0].onlyView){
            this.onlyView = true;
        }
        if(arguments[0] && arguments[0].ARMType){
            this.ARMType = arguments[0].ARMType;
        }

        if (!this.checkARMType()) {
            this.hide();
        }
        this.SearchGrid.setReadOnly(this.onlyView);

        this.getLoadMask().show();
        this.center();
        this.maximize();
        this.doSearch(true, true);
        this.getLoadMask().hide();
	},
    checkARMType: function() { //проверка типа АРМ, настройки поиска в зависимости от типа АРМ
        var error_msg = null;
        var view_allowed_types = [
            'lpupharmacyhead', //АРМ Заведующего аптекой МО - пока не реализован (#88871)
            'hn', //АРМ Главной медсестры МО
            'leadermo', //АРМ Руководителя МО
            'minzdravdlo', //АРМ Специалиста ЛЛО ОУЗ
            'adminllo', //АРМ Администратора ЛЛО
            'spesexpertllo', //АРМ Специалиста центра экспертизы
            'zakup', //АРМ Специалиста по закупкам
            'pmllo' //АРМ Поставщика
        ];
        var edit_allowed_types = [
            'lpupharmacyhead', //АРМ Заведующего аптекой МО - пока не реализован (#88871)
            'hn', //АРМ Главной медсестры МО
            'minzdravdlo', //АРМ Специалиста ЛЛО ОУЗ
            'adminllo' //АРМ Администратора ЛЛО
        ];

        if (Ext.isEmpty(this.ARMType)) {
            error_msg = lang['ne_udalos_opredelit_tip_vyibrannogo_arma'];
        } else if (view_allowed_types.indexOf(this.ARMType) < 0) {
            error_msg = lang['nevernyiy_tip_arm'];
        }

        if (!this.onlyView && edit_allowed_types.indexOf(this.ARMType) >= 0) { //редактирование доступно только если нt было запрещено принудительно и только для некторых АРМ-ов
            this.onlyView = false;
        } else {
            this.onlyView = true;
        }


        //передача типа АРМ-а на форму редактирования доп. соглашения
        this.SearchGrid.setParam('ARMType', this.ARMType, false);

        //установка фильтров на список контрактов
        this.SearchGrid.setParam('OrgFilter_Org_sid', null, true); //Поставщик
        this.SearchGrid.setParam('OrgFilter_Org_cid', null, true); //Заказчик
        this.SearchGrid.setParam('OrgFilter_Org_pid', null, true); //Плательщик
        this.SearchGrid.setParam('OrgFilter_Type', 'or', true); //Тип фильтра по организации (в данном случае необходимо уодвлетворение одного из условий)
        switch(this.ARMType) {
            //case '': //АРМ Заведующего аптекой МО - пока не реализован (#88871)
            case 'hn': //АРМ Главной медсестры МО
            case 'leadermo': //АРМ Руководителя МО
                this.SearchGrid.setParam('OrgFilter_Org_cid', getGlobalOptions().org_id, true);
                this.SearchGrid.setParam('OrgFilter_Org_pid', getGlobalOptions().org_id, true);
                break;
            case 'minzdravdlo': //АРМ Специалиста ЛЛО ОУЗ
            case 'adminllo': //АРМ Администратора ЛЛО
            case 'spesexpertllo': //АРМ Специалиста центра экспертизы
                this.SearchGrid.setParam('OrgFilter_Org_cid', getGlobalOptions().minzdrav_org_id, true);
                this.SearchGrid.setParam('OrgFilter_Org_pid', getGlobalOptions().minzdrav_org_id, true);
                break;
            case 'zakup': //АРМ Специалиста по закупкам
                if (isUserGroup('LpuUser')) { //если пользователь является пользователем ЛПУ
                    this.SearchGrid.setParam('OrgFilter_Org_cid', getGlobalOptions().org_id, true);
                    this.SearchGrid.setParam('OrgFilter_Org_pid', getGlobalOptions().org_id, true);
                } else {
                    this.SearchGrid.setParam('OrgFilter_Org_cid', getGlobalOptions().minzdrav_org_id+','+getGlobalOptions().org_id, true);
                    this.SearchGrid.setParam('OrgFilter_Org_pid', getGlobalOptions().minzdrav_org_id, true);
                }
                break;
            case 'pmllo': //АРМ Поставщика
                this.SearchGrid.setParam('OrgFilter_Org_sid', getGlobalOptions().org_id, true);
                break;
        }

        //если жестко задан фильтр по поставщику, нужно заблокировать соответсвующий фильтр
        if (!Ext.isEmpty(this.SearchGrid.getParam('OrgFilter_Org_sid', true))) {
            this.form.findField('Org_sid').disable();
        } else {
            this.form.findField('Org_sid').enable();
        }

        if (!Ext.isEmpty(error_msg)) {
            Ext.Msg.alert(lang['oshibka'], error_msg);
            return false;
        } else {
            return true;
        }
    },
	doSearch: function(clear, default_values) {
		var form = this;

		if (clear) {
			this.form.reset();
		}
		if (default_values) {
			form.setDefaultDateRange();
		}

		var params = this.form.getValues();
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	initComponent: function() {
		var form = this;
		var current_window = this;

		this.SearchPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			id: 'wdsavSearchForm',
			labelWidth: 165,
			labelAlign: 'right',
			items:
				[{
					layout: 'column',
					border: false,
					labelAlign: 'right',
					items: [{
						layout: 'form',
						border: false,
						items: [
							new Ext.form.DateRangeField({
								width: 220,
								fieldLabel: lang['data_kontrakta'],
								name: 'WhsDocumentUc_DateRange',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								enableKeyEvents: true,
								listeners: {
									'keydown': function(f, e) {
										if(e.getKey() == Ext.EventObject.ENTER) {
											e.stopEvent();
											current_window.doSearch();
										}
									}
								}
							})
						]
					}, {
						layout: 'form',
						items: [{
							xtype: 'sworgcombo',
							fieldLabel : lang['postavschik'],
							tabIndex: current_window.firstTabIndex + 10,
							hiddenName: 'Org_sid',
							id: 'wdsavOrg_sid',
							width: 220,
							editable: false,
							onTrigger1Click: function() {
								if (this.disabled)
									return false;
								var combo = this;
								var win = Ext.getCmp('WhsDocumentSupplyAdditionalViewWindow');
								if (!this.formList) {
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['poisk_organizatsii'],
										id: 'OrgSearch_' + this.id,
										object: 'Org',
										prefix: 'lsswdsav',
										editformclassname: 'swOrgEditWindow',
										store: this.getStore()
									});
								}
								this.formList.show({
									onSelect: function(data) {
										var win = Ext.getCmp('WhsDocumentSupplyAdditionalViewWindow');
										win.setOrg(combo, data);
									}
								});
								return false;
							},
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										current_window.doSearch();
									}
								}
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							disabled: false,
							fieldLabel: lang['nomer_kontrakta'],
							name: 'WhsDocumentUc_Num',
							width: 220,
							xtype: 'textfield',
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										current_window.doSearch();
									}
								}
							}
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['status'],
							tabIndex: current_window.firstTabIndex + 10,
							hiddenName: 'WhsDocumentStatusType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentStatusType_Code',
							comboSubject: 'WhsDocumentStatusType',
							width: 220,
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										current_window.doSearch();
									}
								}
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							fieldLabel: lang['istochnik_finansirovaniya'],
							tabIndex: current_window.firstTabIndex + 10,
							hiddenName: 'DrugFinance_id',
							xtype: 'swcommonsprcombo',
							sortField:'DrugFinance_Code',
							comboSubject: 'DrugFinance',
							width: 220,
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										current_window.doSearch();
									}
								}
							}
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['statya_rashodov'],
							hiddenName: 'WhsDocumentCostItemType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentCostItemType_Code',
							comboSubject: 'WhsDocumentCostItemType',
							width: 220,
							allowBlank: true,
							enableKeyEvents: true,
							listeners: {
								'keydown': function(f, e) {
									if(e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										current_window.doSearch();
									}
								}
							}
						}]
					}, {
						layout: 'form',
						width: 300,
						items: [{
							layout: 'column',
							items: [{
								layout: 'form',
								bodyStyle:'padding-left:15px;padding-right:5px;',
								items: [{
									xtype: 'button',
									text: lang['nayti'],
									minWidth: 80,
									handler: function () {
										Ext.getCmp('WhsDocumentSupplyAdditionalViewWindow').doSearch();
									}
								}]
							}, {
								layout: 'form',
								items: [{
									xtype: 'button',
									text: lang['sbros'],
									minWidth: 80,
									handler: function () {
										Ext.getCmp('WhsDocumentSupplyAdditionalViewWindow').doSearch(true);
									}
								}]
							}]
						}]
					}]
				}]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'/*, handler: function() {
				 getWnd('swSelectWhsDocumentTypeWindow').show({
				 onSelect: function() {
				 if (arguments[0] && arguments[0].WhsDocumentType_id) {
				 var viewframe = current_window.SearchGrid;
				 getWnd(viewframe.editformclassname).show({
				 WhsDocumentType_id: arguments[0].WhsDocumentType_id,
				 WhsDocumentType_Name: arguments[0].WhsDocumentType_Name,
				 callback: viewframe.refreshRecords,
				 owner: viewframe,
				 action: 'add'
				 });
				 }
				 }
				 });
				 }*/},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=WhsDocumentSupply&m=delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentSupply&m=loadWhsDocumentSupplyAdditionalList',
			height: 180,
			object: 'WhsDocumentSupply',
			editformclassname: 'swWhsDocumentSupplyAdditionalEditWindow',
			id: 'WhsDocumentSupplyAdditionalGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			onRowSelect: function(sm,index,record) {
				if (record.get('WhsDocumentStatusType_id') == 2 || form.SearchGrid.readOnly) {
					form.SearchGrid.setActionDisabled('action_delete', true);
				} else {
                    form.SearchGrid.setActionDisabled('action_delete', form.SearchGrid.readOnly);
				}
			},
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'WhsDocumentSupply_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width:75 },
				{ name: 'WhsDocumentStatusType_id', type: 'int', header: '', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_soglasheniya'], width:220 },
				{ name: 'WhsDocumentUc_pNum', type: 'string', header: lang['№_i_data_kontrakta'], width:220 },
				{ name: 'ActualDateRange', type: 'string', header: lang['data_soglasheniya'], width:150 },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width:175 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashodov'], width:125 },
				{ name: 'ProtInf', type: 'string', header: lang['protokol'], width:175 },
				{ name: 'WhsDocumentUc_ppName', type: 'string', header: lang['kotirovka_zayavka'], width:125 },
				{ name: 'Org_Nick', type: 'string', header: lang['postavschik'], width:100 }
			],
			title: null,
			toolbar: true
		});

		Ext.apply(this, {
			layout:'border',
			defaults: {split: true},
			buttons:
				[{
					text: '-'
				},
					HelpButton(this),
					{
						handler: function()
						{
							this.ownerCt.hide()
						},
						iconCls: 'close16',
						text: BTN_FRMCLOSE
					}],
			items:
				[this.SearchPanel,
					{
						border: false,
						xtype: 'panel',
						region: 'center',
						layout:'border',
						items: [form.SearchGrid]
					}]
		});
		sw.Promed.swWhsDocumentSupplyAdditionalViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('wdsavSearchForm').getForm();
	}
});