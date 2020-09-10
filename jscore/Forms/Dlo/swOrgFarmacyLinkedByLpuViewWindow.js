/**
* swOrgFarmacyLinkedByLpuViewWindow - произвольное окно редактирования связей между аптеками и МО (подразделениями МО)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      10.2018
* @comment      
*/
sw.Promed.swOrgFarmacyLinkedByLpuViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Прикрепление аптек к подразделениям МО',
	layout: 'border',
	id: 'OrgFarmacyLinkedByLpuViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();
		var err_message = '';

		wnd.SearchGrid.removeAll();

		params.start = 0;
		params.limit = 100;
		params.Lpu_id = form.findField('Lpu_id').getValue();
		params.OrgFarmacy_id = form.findField('OrgFarmacy_id').getValue();
		params.WhsDocumentCostItemType_id = form.findField('WhsDocumentCostItemType_id').getValue();

		if (Ext.isEmpty(params.Lpu_id)) {
            err_message = 'Для просмотра списка аптек необходимо выбрать МО';
		} else if (wnd.need_cost_id && Ext.isEmpty(params.WhsDocumentCostItemType_id)) {
            err_message = 'Для просмотра списка аптек необходимо выбрать программу ЛЛО';
		}

		if (Ext.isEmpty(err_message)) {
            wnd.SearchGrid.loadData({params: params, globalFilters: params});
		} else {
            Ext.Msg.alert('Ошибка', err_message);
		}
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();

		form.reset();
        wnd.farmacy_combo.fullReset();

		//установка значенй по умолчанию
        if (getGlobalOptions().orgtype == 'lpu' && wnd.DefaultValues.Lpu_id > 0) {
            form.findField('Lpu_id').setValue(wnd.DefaultValues.Lpu_id);
        }
        if (wnd.DefaultValues.OrgFarmacy_id > 0) {
            form.findField('OrgFarmacy_id').setValue(wnd.DefaultValues.OrgFarmacy_id); //иначе поиск может не успеть подхватить значение
            form.findField('OrgFarmacy_id').setValueById(wnd.DefaultValues.OrgFarmacy_id);
        }

		wnd.SearchGrid.removeAll();
	},
	show: function() {
        var wnd = this;
        var form = wnd.FilterPanel.getForm();
		sw.Promed.swOrgFarmacyLinkedByLpuViewWindow.superclass.show.apply(this, arguments);

		this.need_cost_id = true; //системная настройка необходимости выбора программы ЛЛО
		this.show_storage = false;
		this.ARMType = null;
		this.DefaultValues = new Object();
		/*
			minzdravdlo - АРМ специалиста ЛЛО ОУЗ
			merch - АРМ товароведа
			lpuadmin - АРМ администратора МО
			adminllo - АРМ администратора ЛЛО
			leadermo - АРМ руководителя МО
			common - АРМ врача поликлиники
			orgadmin - АРМ администратора организации
			polkallo - - АРМ врача ЛЛО поликлиники
		*/

		if (arguments[0]) {
			if (arguments[0].ARMType) {
                this.ARMType = arguments[0].ARMType;
			}
		}

        form.findField('OrgFarmacy_id').getStore().baseParams.add_without_orgfarmacy_line = 0;
        form.findField('OrgFarmacy_id').getStore().load();

		if (getGlobalOptions().orgtype == 'lpu' && getGlobalOptions().lpu_id > 0 && (!this.ARMType || !this.ARMType.inlist(['adminllo', 'minzdravdlo']))) {
            this.DefaultValues.Lpu_id = getGlobalOptions().lpu_id;
            form.findField('Lpu_id').disable();
		} else {
            form.findField('Lpu_id').enable();
		}

        if (getGlobalOptions().orgtype == 'farm' && getGlobalOptions().OrgFarmacy_id > 0) {
            this.DefaultValues.OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id;
            form.findField('OrgFarmacy_id').disable();
        } else {
            form.findField('OrgFarmacy_id').enable();
        }

        if (this.ARMType && this.ARMType.inlist(['adminllo', 'merch', 'lpuadmin', 'orgadmin'])) {
            this.show_storage = true;
		}
        this.SearchGrid.getGrid().getStore().baseParams.show_storage = this.show_storage ? 1 : 0;

        this.doReset();

        this.SearchGrid.addActions({
            name:'oflblv_add_to_storage',
            text:'Прикрепить к складу',
            iconCls: 'edit16',
			disabled: true,
            handler: function() {
            	var action = wnd.ARMType == 'orgadmin' && getGlobalOptions().orgtype == 'farm' ? 'edit' : 'view'; //orgadmin - АРМ администратора организации
                wnd.SearchGrid.openEditForm('swOrgFarmacyStorageLinkedByLpuEditWindow', action);
            }
        });
        if (wnd.ARMType == 'merch' || (wnd.ARMType == 'orgadmin' && getGlobalOptions().orgtype == 'farm')) { //merch - АРМ товароведа, orgadmin - АРМ администратора организации
            this.SearchGrid.getAction('oflblv_add_to_storage').show();
		} else {
            this.SearchGrid.getAction('oflblv_add_to_storage').hide();
		}

		if (form.findField('Lpu_id').getValue() > 0 && (form.findField('WhsDocumentCostItemType_id').getValue() || !wnd.need_cost_id)) {
            this.doSearch();
		}
	},
	initComponent: function() {
		var wnd = this;

		this.cost_combo = new sw.Promed.SwBaseLocalCombo({
            codeField: 'WhsDocumentCostItemType_Code',
            displayField: 'WhsDocumentCostItemType_Name',
            valueField: 'WhsDocumentCostItemType_id',
            editable: false,
            fieldLabel: langs('Программа ЛЛО'),
            hiddenName: 'WhsDocumentCostItemType_id',
			width: 200,
			listWidth: 350,
            loadParams: {params: {where: " where WhsDocumentCostItemType_IsDlo is not null and WhsDocumentCostItemType_IsDlo = '2' "}},
            store: new Ext.db.AdapterStore({
                autoLoad: false,
                dbFile: 'Promed.db',
                fields: [
                    {name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name'},
                    {name: 'WhsDocumentCostItemType_Code', mapping: 'WhsDocumentCostItemType_Code'},
                    {name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'},
                    {name: 'WhsDocumentCostItemType_begDate', mapping: 'WhsDocumentCostItemType_begDate'},
                    {name: 'WhsDocumentCostItemType_endDate', mapping: 'WhsDocumentCostItemType_endDate'},
                    {name: 'WhsDocumentCostItemType_IsDlo', mapping: 'WhsDocumentCostItemType_IsDlo'}
                ],
                key: 'WhsDocumentCostItemType_id',
                sortInfo: {field: 'WhsDocumentCostItemType_Code'},
                tableName: 'WhsDocumentCostItemType'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="border: 0;"><td style="width: 25px;"><font color="red">{WhsDocumentCostItemType_Code}</font></td><td><h3>{WhsDocumentCostItemType_Name}&nbsp;</h3></td></tr></table>',
                '</div></tpl>'
            )
        });

        this.farmacy_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Аптека'),
            hiddenName: 'OrgFarmacy_id',
            displayField: 'OrgFarmacy_Nick',
            valueField: 'OrgFarmacy_id',
            editable: true,
            allowBlank: true,
            width: 200,
            listWidth: 350,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'OrgFarmacy_id'
                }, [
                    {name: 'OrgFarmacy_id', mapping: 'OrgFarmacy_id'},
                    {name: 'OrgFarmacy_Code', mapping: 'OrgFarmacy_Code'},
                    {name: 'OrgFarmacy_Nick', mapping: 'OrgFarmacy_Nick'},
                    {name: 'OrgFarmacy_HowGo', mapping: 'OrgFarmacy_HowGo'}
                ]),
                url: '/?c=Drug&m=loadOrgFarmacyCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="border: 0;">',
				'<tr><td style="width: 70px;"><font color="red">{OrgFarmacy_Code}</font></td><td><h3>&nbsp;{OrgFarmacy_Nick}</h3></td></tr>',
				'{[!Ext.isEmpty(values.OrgFarmacy_HowGo) ? "<tr><td colspan=\'2\'>"+values.OrgFarmacy_HowGo+"&nbsp;</td></td>" : ""]}',
				'</table>',
                '</div></tpl>'
            )
        });

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'column',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 110,
			border: false,
			frame: true,
			items: [{
                layout: 'form',
                labelWidth: 55,
                items: [{
                    xtype: 'swlpucombo',
                    fieldLabel: langs('МО'),
                    name: 'Lpu_id'
                }]
			}, {
				layout: 'form',
                labelWidth: 55,
				items: [this.farmacy_combo]
			}, {
				layout: 'form',
				items: [this.cost_combo]
			}, {
                layout:'form',
                items: [{
                    style: "padding-left: 10px",
                    xtype: 'button',
                    text: langs('Поиск'),
                    iconCls: 'search16',
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
                    text: langs('Очистить'),
                    iconCls: 'reset16',
                    minWidth: 100,
                    handler: function() {
                        wnd.doReset();
                    }
                }]
            }]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=OrgFarmacyLinkedByLpu&m=delete'},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Drug&m=getOrgFarmacyGridByLpu',
			height: 180,
			object: 'OrgFarmacyLinkedByLpu',
			editformclassname: 'swOrgFarmacyLinkedByLpuEditWindow',
			id: 'OrgFarmacyLinkedByLpuGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
                {name: 'OrgFarmacyIndex_id', type: 'int', header: 'ID', key: true},
                {name: 'OrgFarmacyIndex_Name', hidden: true},
                {name: 'OrgFarmacyIndex_Index', hidden: true},
                {name: 'Lpu_id', hidden: true},
                {name: 'WhsDocumentCostItemType_id', hidden: true},
                {name: 'OrgFarmacy_id', hidden: true},
                {name: 'OrgFarmacy_Code', hidden: true},
                {name: 'OrgFarmacy_HowGo', hidden: true},
                {name: 'OrgFarmacy_Nick', hidden: true},
                {name: 'OrgFarmacy_FullName', header: langs('Аптека'), id: 'autoexpand', renderer: function(v, p, r) {
					var full_name = '';
					if (r.get('OrgFarmacy_id') > 0) {
                        full_name = r.get('OrgFarmacy_Nick');
                        if (!Ext.isEmpty(r.get('OrgFarmacy_Code'))) {
                            full_name = r.get('OrgFarmacy_Code') + ' ' + full_name;
                        }
                        if (!Ext.isEmpty(r.get('OrgFarmacy_HowGo'))) {
                            full_name += '<br/>' + r.get('OrgFarmacy_HowGo');
                        }
					}
					return full_name;
				}},
                {name: 'OrgFarmacy_IsNarko', type: 'checkcolumn', header: langs('Лицензия на НС и ПВ'), width: 150},
                {name: 'LpuBuilding_Name', type: 'string', header: langs('Подразделения МО'), width: 250},
                {name: 'WhsDocumentCostItemType_Name', type: 'string', header: langs('Программа ЛЛО'), width: 250},
                {name: 'LsGroup_Name', type: 'string', header: langs('Группа ЛС'), width: 250},
                {name: 'OrgFarmacy_Vkl', hidden: true},
                {name: 'OrgFarmacy_Name_OrgFarmacy_Vkl', hidden: true},
                {name: 'OrgFarmacy_IsVkl', type: 'checkcolumn', header: langs('Включена'),  width: 65}
			],
			title: null,
			toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
				var edit_enabled = (!Ext.isEmpty(wnd.ARMType) && (wnd.ARMType.inlist(['adminllo', 'minzdravdlo']) || (wnd.ARMType == 'orgadmin' && getGlobalOptions().orgtype == 'farm')));
				var delete_enabled = (!Ext.isEmpty(wnd.ARMType) && wnd.ARMType.inlist(['adminllo', 'minzdravdlo']));

                if (record.get('OrgFarmacy_id') > 0 && !this.readOnly) {
                    this.getAction('action_edit').setDisabled(!edit_enabled);
                    this.getAction('action_delete').setDisabled(!delete_enabled);
                    this.getAction('oflblv_add_to_storage').setDisabled(Ext.isEmpty(record.get('LpuBuilding_Name')));
                } else {
                    this.getAction('action_edit').setDisabled(true);
                    this.getAction('action_delete').setDisabled(true);
                    this.getAction('oflblv_add_to_storage').setDisabled(true);
                }
			},
            editRecord: function (mode) {
                this.openEditForm(this.editformclassname, mode);
            },
            openEditForm: function (edit_form_name, mode) {
                var viewframe = this;
                var grid = viewframe.ViewGridPanel;
                var lpu_combo = wnd.FilterPanel.getForm().findField('Lpu_id');
                var cost_combo = wnd.FilterPanel.getForm().findField('WhsDocumentCostItemType_id');

                var params = new Object();
                params.callback = viewframe.refreshRecords;
                params.owner = viewframe;
                params.action = mode;

                if (lpu_combo.getValue()) {
                    params.Lpu_id = lpu_combo.getValue();
                    var idx = lpu_combo.getStore().findBy(function(rec) {
                        return (rec.get('Lpu_id') == params.Lpu_id);
                    });
                    if (idx > -1) {
                        params.Lpu_Nick = lpu_combo.getStore().getAt(idx).get('Lpu_Nick');
                    }
                }

                if (cost_combo.getValue()) {
                    params.WhsDocumentCostItemType_id = cost_combo.getValue();
                    var idx = cost_combo.getStore().findBy(function(rec) {
                        return (rec.get('WhsDocumentCostItemType_id') == params.WhsDocumentCostItemType_id);
                    });
                    if (idx > -1) {
                        params.WhsDocumentCostItemType_Name = cost_combo.getStore().getAt(idx).get('WhsDocumentCostItemType_Name');
                    }
                }

                var record = grid.getSelectionModel().getSelected();
                if (record) {
                    params.OrgFarmacy_id = record.get('OrgFarmacy_id');
                    params.OrgFarmacy_Nick = record.get('OrgFarmacy_Nick');
                    params.OrgFarmacy_HowGo = record.get('OrgFarmacy_HowGo');
                    params.IsNarko = (record.get('OrgFarmacy_IsNarko') == 'true');
                }

                if (!Ext.isEmpty(params.Lpu_id) && !Ext.isEmpty(params.OrgFarmacy_id) && (!Ext.isEmpty(params.WhsDocumentCostItemType_id) || !wnd.need_cost_id)) {
                    getWnd(edit_form_name).show(params);
                }
            },
            deleteRecord: function () {
                var viewframe = this;
                var grid = viewframe.ViewGridPanel;
                var selected_record = grid.getSelectionModel().getSelected();
                var delMessage = langs('Внимание! Для всех указанных подразделений МО будут удалены данные о прикреплении к выбранной аптеке. Продолжить удаление?');
                var delUrl = '/?c=Drug&m=deleteLpuBuildingLinkData';

                if (!selected_record || !selected_record.get('OrgFarmacy_id')) {
                    Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
                    return false;
                }

                var params = new Object();
                params.Lpu_id = selected_record.get('Lpu_id');
                params.OrgFarmacy_id = selected_record.get('OrgFarmacy_id');
                params.WhsDocumentCostItemType_id = selected_record.get('WhsDocumentCostItemType_id');

                sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: delMessage,
					title: langs('Подтверждение'),
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var loadMask = new Ext.LoadMask(viewframe.getEl(), {msg:langs('Удаление...')});
							loadMask.show();

							Ext.Ajax.request({
									url: delUrl,
									params: params,
									failure: function(response, options) {
										loadMask.hide();
										Ext.Msg.alert(langs('Ошибка'), langs('При удалении произошла ошибка!'));
									},
									success: function(response, action) {
										loadMask.hide();
										if (response.responseText) {
											var answer = Ext.util.JSON.decode(response.responseText);
											if (!answer.success) {
												if (answer.Error_Code && !answer.Error_Msg) {
													Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
												} else if (!answer.Error_Msg) {
													Ext.Msg.alert(langs('Ошибка'), langs('Удаление невозможно!'));
												}
											} else {
												grid.getStore().reload();
											}
										} else {
											Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
										}
									}
								});
						} else {
							if (grid.getStore().getCount() > 0) {
								grid.getView().focusRow(0);
							}
						}
					}
				});
            }
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swOrgFarmacyLinkedByLpuViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});