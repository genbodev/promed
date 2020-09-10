/**
 * swCommercialOfferEditWindow - окно редактирования коммерческих предложений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swCommercialOfferEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['kommercheskoe_predlojenie'],
	layout: 'border',
	id: 'CommercialOfferEditWindow',
	modal: false,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
    setOrgOGRN: function(org_ogrn) {
        this.isSkFarm = (org_ogrn == '090340007747'); //090340007747 - ОГРН организации "СК Фармацея"

        //блокировка функций грида для Казахстана, в слуае если поставщик является организацией "СК Фармация"
        this.DrugGrid.enable_blocked = (getRegionNick() == 'kz' && this.isSkFarm && !haveArmType('superadmin'));
        this.setDisabled(this.action == 'view'); //обновление доступа к компонентам формы
    },
	setOrg: function(combo, data) { //вспомогательная функция для комбобоксов для выбора организации
        var wnd = this;

        if (!data && combo.getValue() > 0) {
			Ext.Ajax.request({
				url: C_ORG_LIST,
				params: {
					Org_id: combo.getValue()
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Org_id && result[0].Org_Name) {
						combo.setValue(result[0].Org_id);
						combo.setRawValue(result[0].Org_Name);
					}
                    wnd.setOrgOGRN(result[0].Org_OGRN);
				}
			});
		} else if (data) {
			combo.setValue(data['Org_id']);
			combo.setRawValue(data['Org_Name']);
            wnd.setOrgOGRN(data['Org_OGRN']);
		}
	},
	doImport: function() {
		var wnd = this;
		wnd.doSave(function(params){
			if (wnd.action != 'edit') {
				//переоткрываем окно после сохранения
				wnd.show({
					action: 'edit',
					callback: wnd.callback,
					owner: wnd.owner,
					CommercialOffer_id: params.CommercialOffer_id,
					onShow: function() {
						getWnd('swCommercialOfferImportWindow').show({
							CommercialOffer_id: params.CommercialOffer_id,
							callback: function() {
								wnd.DrugGrid.refreshRecords(null,0);
							}
						});
					}
				});
			} else {
				getWnd('swCommercialOfferImportWindow').show({
					CommercialOffer_id: params.CommercialOffer_id,
					callback: function() {
						wnd.DrugGrid.refreshRecords(null,0);
					}
				});
			}
		});
	},
	doSave:  function(callback) {
		var wnd = this;
        var region_nick = getRegionNick();

        if (region_nick == 'kz' && wnd.isSkFarm && !haveArmType('superadmin')) {
            var error_msg = (wnd.action == 'add' ? "Создать" : "Редактировать")+" прайс СК Фармация может только администратор ЦОД";
            sw.swMsg.alert(lang['oshibka'], error_msg);
            return false;
        }

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('CommercialOfferEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//сохраняем данные из грида с медикаментами
		wnd.form.findField('DrugListJSON').setValue(wnd.DrugGrid.getJSONChangedData());

		this.submit(callback);
		return true;
	},
	submit: function(callback) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var params = {};
		params.action = wnd.action;
		params.Org_did = wnd.org_did_combo.getValue();

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.CommercialOffer_id > 0) {
					var id = action.result.CommercialOffer_id;
					//сохраняем приложенные файлы
					wnd.FileUploadPanel.listParams = {
						ObjectName: 'CommercialOffer',
						ObjectID: id
					};
					wnd.FileUploadPanel.saveChanges();

					if ( callback && typeof callback == 'function' ) {
						callback({
							CommercialOffer_id: id
						});
					} else {
						wnd.callback(wnd.owner, id);
						wnd.hide();
					}
				}
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = this.form;
		var field_arr = [
			'CommercialOffer_begDT',
			'Org_id',
			'CommercialOffer_Comment',
			'Org_did'
		];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
            var field = form.findField(field_arr[i]);
			if (disable || field.enable_blocked) {
                field.disable();
            } else {
                field.enable();
            }
		}

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.FileUploadPanel.disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			wnd.FileUploadPanel.enable();
		}

        var grid_read_only = (disable || wnd.DrugGrid.enable_blocked);
		wnd.DrugGrid.setReadOnly(grid_read_only);
        wnd.DrugGrid.ViewActions.action_import.setDisabled(grid_read_only);
	},
    setTitleByAction: function() {
        this.setTitle(lang['kommercheskoe_predlojenie']);
        switch (this.action) {
            case 'add':
                this.setTitle(this.title+lang['_dobavlenie']);
                break;
            case 'view':
                this.setTitle(this.title+lang['_prosmotr']);
                break;
            case 'edit':
                this.setTitle(this.title+lang['_redaktirovanie']);
                break;
        }
    },
    setRegionSettings: function() { //отображение полей с учетом особенностей региона
        var region_nick = getRegionNick();
        var form_height = 258;

        if (region_nick == 'kz') {
            form_height += 24;
            this.form.findField('Status_Name').ownerCt.ownerCt.show();
            this.DrugGridFilterPanel.show();
        } else {
            this.form.findField('Status_Name').ownerCt.ownerCt.hide();
            this.DrugGridFilterPanel.hide();
        }

        this.form_panel.setHeight(form_height);
        this.doLayout();
    },
    doSearch: function() {
        var search_params = this.DrugGridFilterPanel.getForm().getValues();
        this.DrugGrid.setSearchParams(search_params);
        this.DrugGrid.setFilter();
    },
    doReset: function() {
        this.DrugGridFilterPanel.getForm().reset();
        if (this.DrugGrid.SearchParams.length > 0) { //нет смысла делать сброс параметров и фильтров в гриде, если параметры уже сброшены
            this.DrugGrid.clearSearchParams();
            this.DrugGrid.setFilter();
        }
    },
	show: function() {
		var wnd = this;
        var region_nick = getRegionNick();

		sw.Promed.swCommercialOfferEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onShow = Ext.emptyFn;
		this.CommercialOffer_id = null;
		this.isSkFarm = false; //признак того что организация поставщика - "СК Фармацея"

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onShow && typeof arguments[0].onShow == 'function' ) {
			this.onShow = arguments[0].onShow;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].CommercialOffer_id ) {
			this.CommercialOffer_id = arguments[0].CommercialOffer_id;
		}

		this.form.reset();
		this.DrugGrid.removeAll();
		this.FileUploadPanel.reset();

        this.DrugGrid.enable_blocked = false;

		this.DrugGrid.addActions({
			name: 'action_import',
			text: lang['import'],
			iconCls: 'add16',
			handler: function() {
				wnd.doImport();
			}
		});

        this.org_did_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
        this.org_did_combo.getStore().load();
        this.org_did_combo.enable_blocked = (getGlobalOptions().lpu_id > 0);
        this.setTitleByAction();
        this.setRegionSettings();

        //настройка видимости колонок в таблице
        this.DrugGrid.setColumnHidden('DrugPrepFasCode_Code', region_nick == 'kz');
        this.DrugGrid.setColumnHidden('ClsMzPhGroup_Name', region_nick == 'kz');
        this.DrugGrid.setColumnHidden('Atc_Name', region_nick == 'kz');
        this.DrugGrid.setColumnHidden('ActMatters_RusName', region_nick == 'kz');
        this.DrugGrid.setColumnHidden('DrugForm_FullName', region_nick == 'kz');
        this.DrugGrid.setColumnHidden('TradeName_Name', region_nick == 'kz');

        this.DrugGrid.setColumnHidden('', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('DrugPrep_Name', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('Drug_Name', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('GoodsUnit_Name', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('KM_Drug_MnnName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_MnnName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_PharmName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_Form', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_Package', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_UnitName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_RegCertName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_ProdName', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_ProdCountry', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_PriceDetail', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_PrevPriceDetail', region_nick != 'kz');
        this.DrugGrid.setColumnHidden('CommercialOfferDrug_updDT', region_nick != 'kz');

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setDisabled(false);
				this.FileUploadPanel.listParams = {
					ObjectName: 'CommercialOffer',
					ObjectID: null
				};
                if (this.org_did_combo.enable_blocked) {
                    this.org_did_combo.setValueById(getGlobalOptions().org_id);
                }
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				//загружаем файлы
				this.FileUploadPanel.listParams = {
					ObjectName: 'CommercialOffer',
					ObjectID: wnd.CommercialOffer_id,
					callback: function() {
						wnd.setDisabled(wnd.action == 'view');
					}
				};
				this.FileUploadPanel.loadData();

				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						CommercialOffer_id: wnd.CommercialOffer_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						wnd.form.setValues(result[0]);
                        if (result[0].Org_did) {
                            wnd.org_did_combo.setValueById(result[0].Org_did);
                        }
						if (result[0].Org_id) {
							wnd.setOrg(wnd.form.findField('Org_id'), {
								Org_id: result[0].Org_id,
								Org_Name: result[0].Org_Name,
                                Org_OGRN: result[0].Org_OGRN
							});

                            var region_nick = getRegionNick();

                            //автопереход в режим просмотра для Казахстана
                            if (wnd.action != 'view' && region_nick == 'kz' && !haveArmType('superadmin') && wnd.isSkFarm) {
                                wnd.action = 'view';
                                wnd.setDisabled(true);
                                wnd.setTitleByAction();
                            }
						}
						loadMask.hide();
						return true;
					},
					url:'/?c=CommercialOffer&m=load'
				});
				this.DrugGrid.loadData({
					globalFilters: {
						CommercialOffer_id: wnd.CommercialOffer_id
					}
				});
				break;
		}
		if (this.onShow) {
			this.onShow(this);
		}
		return true;
	},
	initComponent: function() {
		var wnd = this;

        this.org_did_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['organizatsiya'],
            hiddenName: 'Org_did',
            displayField: 'Org_Name',
            valueField: 'Org_id',
            allowBlank: true,
            editable: false,
            anchor: '100%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Org_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Org_id', mapping: 'Org_id' },
                    { name: 'Org_Name', mapping: 'Org_Name' }
                ],
                key: 'Org_id',
                sortInfo: { field: 'Org_Name' },
                url:'/?c=CommercialOffer&m=loadOrgDidCombo'
            }),
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                combo.lastQuery = '';
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.Org_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.Org_id = null;
                    }
                });
            }
        });

		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			id: 'coeFileUploadPanel',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile'
		});

		this.form_panel = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 258,
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'CommercialOfferEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:0px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				region: 'north',
				url:'/?c=CommercialOffer&m=save',
				items: [{
					name: 'CommercialOffer_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'DrugListJSON',
					xtype: 'hidden'
				},
                this.org_did_combo,
                {
                    fieldLabel: lang['naimenovanie'],
                    name: 'CommercialOffer_Name',
                    xtype: 'textfield',
                    disabled: true,
                    anchor: '100%'
                }, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							fieldLabel: lang['data'],
							name: 'CommercialOffer_begDT',
							xtype: 'swdatefield',
							allowBlank: false,
                            width: 120
						}]
					}, {
						layout: 'form',
						labelAlign: 'right',
						items: [{
							fieldLabel: lang['postavschik'],
							hiddenName: 'Org_id',
							xtype: 'sworgcombo',
							allowBlank: false,
							editable: false,
                            needOrgOGRN: true,
                            width: 350,
							onTrigger1Click: function() {
								if (this.disabled) {
                                    return false;
                                }

								var combo = this;
								if (!this.formList) {
									this.formList = new sw.Promed.swListSearchWindow({
										title: lang['poisk_organizatsii'],
										id: 'OrgSearch_' + this.id,
										object: 'Org',
										prefix: 'lsscoe',
										editformclassname: 'swOrgEditWindow',
										store: this.getStore(),
                                        useBaseParams: true
									});
								}

								this.formList.show({
									onSelect: function(data) {
										wnd.setOrg(combo, data);
									}
								});

								return false;
							},
                            onTrigger2Click: function() {
                                if (!this.disabled) {
                                    this.clearValue();
                                    wnd.setOrgOGRN(null);
                                }
                            }
						}]
					}]
				}, {
                        layout: 'column',
                        items:[{
                            layout: 'form',
                            items: [{
                                fieldLabel: lang['status'],
                                name: 'Status_Name',
                                xtype: 'textfield',
                                disabled: true,
                                width: 120
                            }]
                        }, {
                            layout: 'form',
                            labelAlign: 'right',
                            items: [{
                                fieldLabel: 'Действует до',
                                name: 'CommercialOffer_endDT',
                                xtype: 'swdatefield',
                                disabled: true,
                                width: 120
                            }]
                        }]
                }, {
					fieldLabel: lang['primechanie'],
					name: 'CommercialOffer_Comment',
					allowBlank: true,
					xtype: 'textfield',
					maxLength: 100,
					//width: 549
					anchor: '-1'
				}]
			}, {
				xtype: 'fieldset',
				autoScroll: true,
				height: 130,
				title: lang['faylyi'],
				style: 'padding: 3px; display:block;',
				items:
					[this.FileUploadPanel]
			}]
		});



        this.DrugGridFilterFormPanel = new sw.Promed.Panel({
            layout: 'column',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 100,
            border: false,
            frame: true,
            items: [{
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    name: 'Drug_Name',
                    fieldLabel: 'Медикамент',
                    width: 150
                }]
            }, {
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    name: 'CommercialOfferDrug_PriceDetail',
                    fieldLabel: 'Код СКП',
                    width: 150
                }]
            }, {
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    name: 'CommercialOfferDrug_ProdName',
                    fieldLabel: 'Производитель',
                    width: 150
                }]
            }, {
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    name: 'CommercialOfferDrug_ProdCountry',
                    fieldLabel: 'Страна',
                    width: 150
                }]
            }]
        });

        this.DrugGridFilterButtonsPanel = new sw.Promed.Panel({
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
                        text: lang['nayti'],
                        iconCls: 'search16',
                        minWidth: 100,
                        handler: function() {
                            wnd.doSearch();
                        }.createDelegate(this)
                    }]
                }, {
                    layout:'form',
                    items: [{
                        style: "padding-left: 10px",
                        xtype: 'button',
                        text: lang['sbros'],
                        iconCls: 'reset16',
                        minWidth: 100,
                        handler: function() {
                            wnd.doReset();
                        }.createDelegate(this)
                    }]
                }]
            }]
        });

        this.DrugGridFilterPanel = getBaseFiltersFrame({
            region: 'north',
            defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
            ownerWindow: this,
            toolBar: this.WindowToolbar,
            hidden: true,
            items: [
                this.DrugGridFilterFormPanel,
                this.DrugGridFilterButtonsPanel
            ]
        });

		this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.DrugGrid.editGrid('add') }},
				{name: 'action_edit', handler: function() { wnd.DrugGrid.editGrid('edit') }},
				{name: 'action_view', handler: function() { wnd.DrugGrid.editGrid('view') }},
				{name: 'action_delete', handler: function() { wnd.DrugGrid.deleteRecord() }},
				{name: 'action_print'},
				{name: 'action_refresh', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'dbo',
			obj_isEvn: false,
			border: false,
			dataUrl: '/?c=CommercialOffer&m=loadCommercialOfferDrugList',
			height: 180,
			region: 'center',
			object: 'CommercialOfferDrug',
			editformclassname: 'swCommercialOfferDrugEditWindow',
			id: this.id+'DrugGrid',
			paging: false,
			style: 'margin-bottom: 10px',
            // для объединения заголовков полей грида
            headergrouping: getRegionNick() == 'kz', //не самое удачно решение, но иначе не удается изменять шапку на нормальную для регионов
            title: false,
            toolbar: true,
			stringfields: [
				{name: 'CommercialOfferDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'CommercialOffer_id', type: 'int', hidden: true},
                {name: 'DrugPrepFas_id', type: 'int', hidden: true},
                {name: 'Drug_id', type: 'int', hidden: true},
                {name: 'GoodsUnit_id', type: 'int', hidden: true},
                {name: 'state', type: 'string', header: 'state', hidden: true},

				{name: 'Num', header: lang['№_p_p'], type: 'rownumberer', width: 50},

				{name: 'DrugPrepFasCode_Code', type: 'string', header: lang['kod'], width: 80},
                {name: 'ClsMzPhGroup_Name', header: lang['farmgruppa'], width: 80},
                {name: 'Atc_Name', header: lang['ath'], width: 80},
                {name: 'ActMatters_RusName', header: lang['mnn'], width: 80},
                {name: 'DrugForm_FullName', header: lang['formavyipuska'], width: 95},
                {name: 'TradeName_Name', header: lang['torgovoe_naimenovanie'], width: 80, id: 'autoexpand'},

                {name: 'DrugPrep_Name', header: 'Груп.наим.', width: 80},
                {name: 'Drug_Name', header: 'Медикамент', width: 200, renderer: function(v, p, r) {
                    p.css += 'transferLine';
                    return v;
                }},
                {name: 'GoodsUnit_Name', header: 'Ед.изм.', width: 80},

				{name: 'CommercialOfferDrug_Price', type: 'money', header: lang['tsena'], width: 100},

                {name: 'KM_Drug_MnnName', header: 'Тип медикамента', width: 80},
                {name: 'CommercialOfferDrug_MnnName', header: 'Непатентованное наименование', width: 80},
                {name: 'CommercialOfferDrug_PharmName', header: 'Наименование', width: 80},
                {name: 'CommercialOfferDrug_Form', header: 'Форма выпуска', width: 80},
                {name: 'CommercialOfferDrug_Package', header: 'Кол-во в уп.', width: 80},
                {name: 'CommercialOfferDrug_UnitName', header: 'Ед.изм.', width: 80},
                {name: 'CommercialOfferDrug_RegCertName', header: 'РУ', width: 80},
                {name: 'CommercialOfferDrug_ProdName', header: 'Производитель', width: 80},
                {name: 'CommercialOfferDrug_ProdCountry', header: 'Срана производства', width: 80},
                {name: 'CommercialOfferDrug_PriceDetail', header: 'Код СКП', width: 80},
                {name: 'CommercialOfferDrug_PrevPriceDetail', header: 'Код СКП (пред.)', width: 80},
                {name: 'CommercialOfferDrug_updDT', header: 'Дата обновления', width: 80}
            ],
            gridplugins: [
                new Ext.ux.plugins.GroupHeaderGrid({
                    rows: [
                        [
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {},
                            {header: 'Данные прайса СК Фармация', colspan: 12, width: 300, align: 'center', id: 'SK_Farm_Colspan'}
                        ]
                    ],
                    hierarchicalColMenu: false
                })
            ],
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('CommercialOfferDrug_id') > 0) {
					this.ViewActions.action_edit.setDisabled(this.readOnly);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(this.readOnly);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			editGrid: function (action) {
				if (action == null)	action = 'add';

				var view_frame = this;
				var store = view_frame.getGrid().getStore();
                var org_did = wnd.form.findField('Org_did').getValue();

				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('CommercialOfferDrug_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					var params = new Object();
                    params.Org_did = org_did;
                    params.isSkFarm = wnd.isSkFarm;

					getWnd(view_frame.editformclassname).show({
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('CommercialOfferDrug_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);
							view_frame.clearFilter();
							data.CommercialOfferDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
							data.state = 'add';
							view_frame.getGrid().getStore().insert(record_count, new record(data));
							view_frame.setFilter();
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('CommercialOfferDrug_id') > 0) {
						var params = selected_record.data;
                        params.Org_did = org_did;
                        params.isSkFarm = wnd.isSkFarm;

						getWnd(view_frame.editformclassname).show({
							action: action,
							params: params,
							callback: function(data) {
								view_frame.clearFilter();
								for(var key in data) {
                                    selected_record.set(key, data[key]);
                                }
								if (selected_record.get('state') != 'add') {
                                    selected_record.set('state', 'edit');
                                }
								selected_record.commit();
								view_frame.setFilter();
							}
						});
					}
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('state') == 'add') {
					view_frame.getGrid().getStore().remove(selected_record);
				} else {
					selected_record.set('state', 'delete');
					selected_record.commit();
					view_frame.setFilter();
				}
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')
						data.push(record.data);
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
            clearSearchParams: function() {
                this.setSearchParams();
            },
            setSearchParams: function(search_params) { //установка праметров поиска для фильтрации грида
                this.SearchParams = new Array();

                for (var s in search_params) {
                    this.SearchParams.push({
                        name: s,
                        value: search_params[s]
                    });
                }
            },
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
                var view_frame = this;

				this.getGrid().getStore().filterBy(function(record){
                    var result = record.get('state') != 'delete';

                    if (view_frame.SearchParams && !Ext.isEmpty(view_frame.SearchParams.length)) {
                        var condition = false;

                        for (var i = 0; i < view_frame.SearchParams.length; i++) {
                            if (result && !Ext.isEmpty(view_frame.SearchParams[i].value)) {
                                param_value = view_frame.SearchParams[i].value.toLowerCase();

                                if (view_frame.SearchParams[i].name == 'Drug_Name') {
                                    str1 = record.get('CommercialOfferDrug_PharmName').toLowerCase();
                                    str2 = record.get('DrugPrep_Name').toLowerCase();
                                    str3 = record.get('Drug_Name').toLowerCase();
                                    condition = (str1.indexOf(param_value) == 0 || str2.indexOf(param_value) == 0 || str3.indexOf(param_value) == 0);
                                } else {
                                    str = record.get(view_frame.SearchParams[i].name).toLowerCase();
                                    condition = (str.indexOf(param_value) == 0);
                                }

                                result = (result && condition);
                            }
                        }
                    }

					return result;
				});
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:[
				{
					handler: function() {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items:[
				this.form_panel,
                {
                    xype: 'panel',
                    region: 'center',
                    layout: 'border',
                    title: lang['spisok_medikamentov'],
                    items: [
                        this.DrugGridFilterPanel,
                        this.DrugGrid
                    ]
                }
			]
		});
		sw.Promed.swCommercialOfferEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('CommercialOfferEditForm').getForm();
	}
});