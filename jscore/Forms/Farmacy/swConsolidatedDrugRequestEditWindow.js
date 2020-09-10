/**
* swConsolidatedDrugRequestEditWindow - окно редактирования сводной заявки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      11.2012
* @comment      
*/
sw.Promed.swConsolidatedDrugRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Сводная заявка на закуп медикаментов',
	layout: 'border',
	id: 'ConsolidatedDrugRequestEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	exportToCSV: function() {
		var wnd = this;
		var params = new Object();

		params = wnd.FilterPanel.getForm().getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;

		wnd.getLoadMask('Формирование файла...').show();
		Ext.Ajax.request({
			scope: this,
			params: params,
			url: '/?c=MzDrugRequest&m=exportDrugRequestPurchaseSpecList',
			callback: function(o, s, r) {
				wnd.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open(obj.url);
					}
				}
			}
		});
	},
    saveDrugRequestQuotaTotal: function(data) {
        var wnd = this;

        Ext.Ajax.request({
            params: {
                DrugRequest_id: wnd.RegionDrugRequest_id,
                DrugRequestQuota_Total: wnd.DrugRequestQuotaPanel.getForm().findField('DrugRequestQuota_Total').getValue()
            },
            callback: function (options, success, response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (data.callback && typeof data.callback == 'function') {
                    data.callback(result);
                }
            },
            url:'/?c=MzDrugRequest&m=saveDrugRequestQuotaTotal'
        });
    },
    checkLimit: function() {
        var region = getGlobalOptions().region.nick;
        var wnd = this;
        var limit = 10;
        var sum = 0;
        var msg = ' ';

        if (region == 'saratov') {
            limit = wnd.DrugRequestQuotaPanel.getForm().findField('DrugRequestQuota_Total').getValue()*1;
        } else {
            limit = wnd.DrugRequestQuota_SumTotal;
        }

        wnd.DrugGrid.getGrid().getStore().each(function(record) {
            sum += record.get('DrugRequestPurchaseSpec_pSum')*1;
        });

        if (sum > 0 && sum > limit) {
            msg = '<span style="color: red;">Превышение '+(getRegionNick()=='perm'?'норматива':'лимита')+': '+sw.Promed.Format.rurMoney(sum-limit)+' р.</span>';
        }

        wnd.InformationPanel.setData('overflow_limit_data', msg);
        wnd.InformationPanel.showData();
    },
    openExecDataWindow: function() {
        var wnd = this;

        if (!wnd.DrugGrid.readOnly) {
            getWnd('swDrugRequestExecViewWindow').show({
                DrugRequest_id: wnd.DrugRequest_id,
                onHide: function() {
                    wnd.DrugGrid.refreshRecords(null, 0);
                }
            });
        }
    },
	doSearch: function() {
		var wnd = this;
		var params = this.FilterPanel.getForm().getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;

		wnd.SumGrid.removeAll();
        wnd.DrugGrid.removeAll();

		wnd.SumGrid.loadData({
			globalFilters: params
		});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
	},
	setDisabledAction: function(action, disabled) {
		var actions = this.DrugGrid.getAction('action_cdrew_actions').items[0].menu.items,
			idx = actions.findIndexBy(function(a) { return a.name == action; });
		if( idx == -1 ) {
			return;
		}
		actions.items[idx].setDisabled(disabled);
		this.DrugGrid.getAction('action_cdrew_actions').items[1].menu.items.items[idx].setDisabled(disabled);
	},
	show: function() {
        var wnd = this;
		sw.Promed.swConsolidatedDrugRequestEditWindow.superclass.show.apply(this, arguments);		
		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.DrugRequest_id = null;
		this.DrugRequestQuota_SumTotal = null;
		this.RegionDrugRequest_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		} else {
			this.DrugRequest_id = null;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_Name = arguments[0].DrugRequest_Name;
		} else {
			this.DrugRequest_Name = null;
		}

		this.doReset();
		this.InformationPanel.clearData();
		this.InformationPanel.setData('request_name', this.DrugRequest_Name);
		this.InformationPanel.showData();
		this.setTitle('Сводная заявка на закуп медикаментов');

        wnd.DrugGrid.setParam('DrugRequest_Name', this.DrugRequest_Name, false);

        if(!wnd.DrugGrid.getAction('action_cdrew_execute')) {
            wnd.DrugGrid.addActions({
                name: 'action_cdrew_execute',
                text: 'Исполнение сводной заявки',
                iconCls: 'ok16',
                handler: function() {
                    wnd.openExecDataWindow();
                }
            });
        }

        if(!wnd.SumGrid.getAction('action_cdrew_calculate_price')) {
            wnd.SumGrid.addActions({
                name: 'action_cdrew_calculate_price',
                text: 'Расчет цены',
                iconCls: 'edit16',
                handler: function() {
                    var selected_record = wnd.SumGrid.getGrid().getSelectionModel().getSelected();
                    if (wnd.SumGrid.readOnly || Ext.isEmpty(selected_record.get('DrugRequestPurchaseSpec_id'))) {
                        return false;
                    } else {
                        getWnd('swWhsDocumentProcurementPriceEditWindow').show({
                            DrugRequestPurchaseSpec_id: selected_record.get('DrugRequestPurchaseSpec_id'),
                            params: {
                                Drug_Name: selected_record.get('DrugComplexMnnName_Name') + (!Ext.isEmpty(selected_record.get('TRADENAMES_Name')) ? ' / '+selected_record.get('TRADENAMES_Name') : ''),
                                InJnvlp: selected_record.get('InJnvlp')
                            },
                            callback: function() {
                                wnd.SumGrid.refreshRecords(null,0);
                            }
                        });
                    }
                }
            });
        }

		if(!wnd.DrugGrid.getAction('action_cdrew_actions')) {
			wnd.DrugGrid.addActions({
				name:'action_cdrew_actions',
				text:'Действия',
				menu: [{
					name: 'action_close',
					text: 'Установить запрет редактирования строки',
					tooltip: 'Установить запрет редактирования строки',
					handler: function() {
						var arr = new Array();

						if (wnd.DrugGrid.readOnly) {
							return false;
						}

						wnd.DrugGrid.getGrid().getStore().each(function(record) {
							if (record.get('isOpen_Code') == 1 && record.get('checked'))
								arr.push(record.get('DrugRequestPurchaseSpec_id'));
						});

						if(arr.length > 0) {
							Ext.Ajax.request({
								failure: function() {
									sw.swMsg.alert('Ошибка', 'Не удалось установить запрет редактирования строки');
								},
								params: {Id_List: arr.join(',')},
								success: function(response, options) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if ( response_obj.success == true ) {
										wnd.DrugGrid.refreshRecords(null,0);
									} else if(response_obj.Error_Msg){
										sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
									}
								}.createDelegate(this),
								url: '/?c=MzDrugRequest&m=closeDrugRequestPurchaseSpec'
							});
						}
					},
					iconCls: 'delete16'
				}, {
					name: 'action_open',
					text: 'Разрешить редактировать строку',
					tooltip: 'Разрешить редактировать строку',
					handler: function() {
						var arr = new Array();

						if (wnd.DrugGrid.readOnly) {
							return false;
						}

						wnd.DrugGrid.getGrid().getStore().each(function(record) {
							if (record.get('isOpen_Code') != 1 && record.get('checked')) {
                                arr.push(record.get('DrugRequestPurchaseSpec_id'));
                            }
						});

						if(arr.length > 0) {
							Ext.Ajax.request({
								failure: function() {
									sw.swMsg.alert('Ошибка', 'Не удалось снять запрет редактирования строки');
								},
								params: {Id_List: arr.join(',')},
								success: function(response, options) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if ( response_obj.success == true ) {
										wnd.DrugGrid.refreshRecords(null,0);
									} else if(response_obj.Error_Msg){
										sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
									}
								}.createDelegate(this),
								url: '/?c=MzDrugRequest&m=openDrugRequestPurchaseSpec'
							});
						}
					},
					iconCls: 'ok16'
				}],
				iconCls: 'actions16'
			});
		}

		if(!wnd.DrugGrid.getAction('export_csv')) {
			wnd.DrugGrid.addActions({
				name:'export_csv',
				text:'Экспорт в CSV',
				handler: wnd.exportToCSV.createDelegate(wnd),
				iconCls: 'rpt-xls'
			});
		}

		this.DrugGrid.setReadOnly(this.action == 'view');
		this.setDisabledAction('action_close', this.DrugGrid.readOnly);
        this.DrugGrid.getAction('action_cdrew_execute').setDisabled(this.DrugGrid.readOnly);
        this.SumGrid.getAction('action_cdrew_calculate_price').setDisabled(this.DrugGrid.readOnly);

        //отображение колонок грида в зависимости от региона
        var region = getGlobalOptions().region.nick;
        //this.DrugGrid.setColumnHidden('DrugRequestPurchaseSpec_RefuseCount', region != 'saratov');
        this.DrugGrid.setColumnHidden('DrugRequestRow_Kolvo', region != 'saratov');
        this.DrugGrid.setColumnHidden('DocumentUcStr_Count', region != 'saratov');
        this.SumGrid.setColumnHidden('PhGr_Name', region != 'saratov');

        if (region == 'saratov') {
            wnd.DrugRequestQuotaPanel.show();
            wnd.FilterPanel.getForm().findField('RlsClsPhGrLimp_id').ownerCt.show();
        } else {
            wnd.DrugRequestQuotaPanel.hide();
            wnd.FilterPanel.getForm().findField('RlsClsPhGrLimp_id').ownerCt.hide();
        }

        var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.title + (wnd.action == 'edit' ? ': Редактирование' : ': Просмотр'));
				wnd.doSearch();
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequest_id: wnd.DrugRequest_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							wnd.DrugRequestQuota_SumTotal = result[0].DrugRequestQuota_SumTotal;
							wnd.RegionDrugRequest_id = result[0].RegionDrugRequest_id;
                            wnd.DrugRequestQuotaPanel.getForm().findField('DrugRequestQuota_Total').setValue(result[0].DrugRequestQuota_Total);

                            var limit_str = region != 'saratov' ? 'Объемы финансирования: '+sw.Promed.Format.rurMoney(wnd.DrugRequestQuota_SumTotal > 0 ? wnd.DrugRequestQuota_SumTotal : 0)+' р. ' : ' ';
                            wnd.InformationPanel.setData('limit_data', limit_str);
                            wnd.InformationPanel.showData();

                            wnd.checkLimit();
						}
						loadMask.hide();
					},
					url:'/?c=MzDrugRequest&m=loadConsolidatedDrugRequest'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;
		
		this.SumGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'},
				{name: 'action_refresh'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestPurchaseSpecSumList',
			height: 180,
			region: 'center',
			object: 'DrugRequestPurchaseSpec',
			editformclassname: 'swConsolidatedDrugRequestRowEditWindow',
			id: wnd.id + 'SumGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestPurchaseSpec_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugComplexMnnCode_Code', type: 'string', header: 'Код', width: 100},
                {name: 'PhGr_Name', type: 'string', header: 'ФТГ', width: 100},
				{name: 'Atc_id', type: 'int', hidden: true},
				{name: 'Atc_Name', type: 'string', header: 'АТХ', width: 120},
                {name: 'InJnvlp', header: 'ЖНВЛП', width: 100, renderer: function(v, p, r){ return v != '1' && r.get('DrugRequestPurchaseSpec_id') > 0  ? 'нет' : '';}},
                {name: 'DrugComplexMnn_id', type: 'int', hidden: true},
				{name: 'DrugComplexMnnName_Name', type: 'string', header: 'МНН', width: 120, id: 'autoexpand'},
				{name: 'TRADENAMES_id', type: 'int', hidden: true},
				{name: 'TRADENAMES_Name', type: 'string', header: 'Торг. наим.', width: 120},
				{name: 'ClsDrugForms_Name', type: 'string', header: 'Лекарственная форма', width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: 'Дозировка', width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: 'Фасовка', width: 100},
				{name: 'DrugRequestPurchaseSpec_lKolvo', type: 'float', header: 'Кол-во', width: 120},
				{name: 'DrugRequestPurchaseSpec_Price', type: 'money', header: 'Цена', width: 120},
				{name: 'DrugRequestPurchaseSpec_Sum', type: 'money', header: 'Сумма', width: 120},
                {name: 'DrugRequestPurchaseSpec_RefuseCount', type: 'float', header: 'Исполнение заявки<br/>Отказ', width: 100},
                {name: 'DrugRequestPurchaseSpec_RestCount', type: 'float', header: 'Исполнение заявки<br/>Из остатков', width: 100},
                {name: 'DrugRequestExec_Count', type: 'float', header: 'Исполнение заявки<br/>В т.ч. к закупу', width: 100},
                {name: 'DrugRequestPurchaseSpec_pKolvo', type: 'float', header: 'Исполнение заявки<br/>К закупу', width: 100},
				{name: 'DrugRequestPurchaseSpec_pSum', type: 'money', header: 'Сумма закупа', width: 120},
                {name: 'Evn_id', type: 'int', hidden: true},
                {name: 'Evn_Name', header: 'Протокол ВК', width: 100, renderer: function(v, p, r){ return Ext.isEmpty(v) && r.get('DrugRequestPurchaseSpec_id') > 0  ? 'нет' : v;}}
			],
			title: 'Итоги по сводной заявке',
			toolbar: true,
			editable: true,
			params: {
				callback: function(owner) {
					owner.refreshRecords(null, 0);
				}
			},
            onRowSelect: function(sm, rowIdx, record) {
                var params = new Object();

                params.DrugRequest_id = wnd.DrugRequest_id;
                params.DrugFinance_id = wnd.FilterPanel.getForm().findField('DrugFinance_id').getValue();
                params.DrugComplexMnn_id = record.get('DrugComplexMnn_id');
                params.TRADENAMES_id = record.get('TRADENAMES_id');
                params.Evn_id = record.get('Evn_id');

                wnd.DrugGrid.removeAll();
                if (!Ext.isEmpty(record.get('DrugRequestPurchaseSpec_id'))) {
                    wnd.DrugGrid.loadData({
                        globalFilters: params
                    });
                }
            }
		});
        
        this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'},
				{name: 'action_save', url: '/?c=MzDrugRequest&m=saveDrugRequestPurchaseSpecParams'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestPurchaseSpecList',
			height: 180,
			region: 'south',
			object: 'DrugRequestPurchaseSpec',
			editformclassname: 'swConsolidatedDrugRequestRowEditWindow',
			id: wnd.id + 'DrugGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestPurchaseSpec_id', type: 'int', header: 'ID', key: true},
				{name: 'checked', header: '<input type="checkbox" id="cdre_checkAll_checkbox" onClick="var wnd = getWnd(\'swConsolidatedDrugRequestEditWindow\'); if (!wnd.DrugGrid.readOnly) {wnd.DrugGrid.checkAll(this.checked); return true;} else {return false;}">', width: 65, renderer: sw.Promed.Format.checkColumn},
				{name: 'isOpen_Code', type: 'int', hidden: true},
				{name: 'isOpen_Name', type: 'string', header: 'Статус', width: 120, hidden: true},
                {name: 'Org_Nick', type: 'string', header: 'Заказчик', width: 120, id: 'autoexpand'},
                {name: 'DrugGroup_Name', type: 'string', header: 'Группа<br/>медикаментов'},
				{name: 'PersonRegisterType_Name', type: 'string', header: 'Тип<br/>регистра'},
				{name: 'DrugFinance_id', type: 'int', hidden: true},
				{name: 'DrugFinance_Name', type: 'string', header: 'Источник<br/>финансирования', width: 120},
				{name: 'Atc_id', type: 'int', hidden: true},
				{name: 'Atc_Name', type: 'string', hidden: true},
				{name: 'DrugComplexMnnName_Name', type: 'string', hidden: true},
				{name: 'TRADENAMES_Name', type: 'string', hidden: true},
				{name: 'ClsDrugForms_Name', type: 'string', hidden: true},
				{name: 'DrugComplexMnnDose_Name', type: 'string', hidden: true},
				{name: 'DrugComplexMnnFas_Name', type: 'string', hidden: true},
                {name: 'DrugRequestPurchaseSpec_lKolvo', type: 'float', header: 'Кол-во', width: 120},
                {name: 'DrugRequestRow_Kolvo', type: 'string', header: 'Заказано<br/>(пред.год)', width: 100},
                {name: 'DocumentUcStr_Count', type: 'string', header: 'Отпущено<br/>(пред.год)', width: 100},
				{name: 'DrugRequestPurchaseSpec_Price', type: 'money', header: 'Цена', width: 120},
				{name: 'DrugRequestPurchaseSpec_Sum', type: 'money', header: 'Сумма', width: 120},
                {name: 'DrugRequestPurchaseSpec_RefuseCount', type: 'float', header: 'Исполнение заявки<br/>Отказ', editor: new Ext.form.NumberField(), css: 'background-color: #dfe8f6;'},
                {name: 'DrugRequestPurchaseSpec_RestCount', type: 'float', header: 'Исполнение заявки<br/>Из остатков', width: 100},
                {name: 'DrugRequestExec_Count', type: 'float', header: 'Исполнение заявки<br/>В т.ч. к закупу', width: 100},
                {name: 'DrugRequestPurchaseSpec_pKolvo', type: 'float', header: 'Исполнение заявки<br/>К закупу', width: 100, editor: new Ext.form.NumberField()},
				{name: 'DrugRequestPurchaseSpec_pSum', type: 'money', header: 'Сумма закупа', width: 120},
                {name: 'WhsDocumentProcurementRequestSpec_Kolvo', hidden: true},
                {name: 'WhsDocumentProcurementRequestSpec_Sum', hidden: true},
                {name: 'WhsDocumentProcurementRequest_Data', header: 'Закуплено<br/>по заявке', width: 200, renderer: function(v, p, r){
                    var data_str = '';

                    if (r.get('WhsDocumentProcurementRequestSpec_Kolvo')*1 > 0) {
                        data_str += r.get('WhsDocumentProcurementRequestSpec_Kolvo')*1;
                    }
                    if (r.get('WhsDocumentProcurementRequestSpec_Sum')*1 > 0) {
                        data_str += ' на '+sw.Promed.Format.rurMoney(r.get('WhsDocumentProcurementRequestSpec_Sum'), p, r)+' р.';
                    }

                    var link = '<a href="#" onclick="getWnd(\'swConsolidatedDrugRequestEditWindow\').openExecDataWindow(); return false;" style="cursor: pointer; color: #0000EE;">'+data_str+'</a>';

                    return !Ext.isEmpty(data_str) ? link : null;
                }},
				{name: 'DetailsData', type: 'string', header: 'Данные', hidden: true}
			],
			title: 'Медикаменты сводной заявки',
			toolbar: true,
			editable: true,
			params: {
				callback: function(owner) {
					owner.refreshRecords(null, 0);
				}
			},
            onLoadData: function() {
                document.getElementById('cdre_checkAll_checkbox').checked = false;
                wnd.checkLimit();
                var per_reg = wnd.DrugGrid.getGrid().getStore().findBy(function(rec){
                	return (!Ext.isEmpty(rec.get('PersonRegisterType_Name')));
                });
                if(per_reg == -1){
                	per_reg = true;
                } else {
                	per_reg = false;
                }
                // Скрыть/показать клонки Тип регистра, Источник финансирования
                wnd.DrugGrid.getGrid().getView().cm.setHidden(6,per_reg);
                wnd.DrugGrid.getGrid().getView().cm.setHidden(28,per_reg);
            },
			onDblClick: function(grid) {
				if (!this.readOnly) {
					var record = grid.getSelectionModel().getSelected();
					record.set('checked', (record.get('checked') == false));
					record.commit();
				}
			},
			checkAll: function(checked) {
				if (!this.readOnly) {
					this.getGrid().getStore().each(function(record){
						if (record.get('DrugRequestPurchaseSpec_id') > 0) {
							record.set('checked', checked);
							record.commit();
						}
					});
				}
			},
			onBeforeEdit: function(o) {
				return (
					(o.field == 'DrugRequestPurchaseSpec_RefuseCount' && getRegionNick() == 'saratov' && o.record.get('isOpen_Code') > 0) ||
					(o.field == 'DrugRequestPurchaseSpec_pKolvo' && getRegionNick() == 'krym')
				);
			},
			onAfterEdit: function(o) {
                if (o.field == 'DrugRequestPurchaseSpec_RefuseCount') {
                    var l_kolvo = o.record.get('DrugRequestPurchaseSpec_lKolvo');
                    var refuse_kolvo = o.record.get('DrugRequestPurchaseSpec_RefuseCount');

                    if (refuse_kolvo > l_kolvo) {
                        refuse_kolvo = l_kolvo;
                    }

                    if (refuse_kolvo < 0) {
                        refuse_kolvo = null;
                    }

                    Ext.Ajax.request({
                        params: {
                            DrugRequestPurchaseSpec_id: o.record.get('DrugRequestPurchaseSpec_id'),
                            DrugRequestPurchaseSpec_RefuseCount: refuse_kolvo
                        },
                        callback: function (options, success, response) {
                            var result = Ext.util.JSON.decode(response.responseText);
                            if (result.success) {
                                o.record.set('DrugRequestPurchaseSpec_RefuseCount', !Ext.isEmpty(result.DrugRequestPurchaseSpec_RefuseCount) ? result.DrugRequestPurchaseSpec_RefuseCount*1 : null);
                                o.record.set('DrugRequestPurchaseSpec_pKolvo', !Ext.isEmpty(result.DrugRequestPurchaseSpec_pKolvo) ? result.DrugRequestPurchaseSpec_pKolvo*1 : null);
                                o.record.set('DrugRequestPurchaseSpec_pSum', result.DrugRequestPurchaseSpec_pSum);
                                o.record.commit();

                                wnd.checkLimit();
                            }
                        },
                        url:'/?c=MzDrugRequest&m=saveDrugRequestPurchaseSpecRefuseCount'
                    });
				}
			}
		});

		//По наименованию
		this.FilterRequestPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'swdrugfinancecombo',
				hiddenName: 'DrugFinance_id',
				fieldLabel: 'Источник финансирования',
				width: 250,
				allowBlank: true
			}, {
				xtype: 'textfield',
				fieldLabel: 'Наименование',
				name: 'Drug_Name',
				width: 250
			}]
		});

		//По классификации
		this.FilterClassPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'swrlsclsatccombo',
				fieldLabel: 'АТХ',
				name: 'RlsClsatc_Name',
				anchor: null,
				width: 250
			}, {
                layout: 'form',
                items: [{
                    xtype: 'swrlsclsphgrlimpcombo',
                    fieldLabel: 'Фарм.группа ЖНВЛС',
                    hiddenName: 'RlsClsPhGrLimp_id',
                    anchor: null,
                    width: 250
                }]
            }]
		});

		this.FilterTabs = new Ext.TabPanel({
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 100,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				title: 'По заявке',
				layout: 'fit',
				border:false,
				items: [this.FilterRequestPanel]
			}, {
				title: 'По классификации',
				layout: 'fit',
				border:false,
				items: [this.FilterClassPanel]
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
						text: 'Найти',
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
						text: 'Сброс',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: this,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterTabs,
				this.FilterButtonsPanel
			]
		});

		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			border: false,
			autoHeight: true,
			frame: false,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: this,
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},			
			showData: function() {
				var html = this.html_tpl;				
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');				
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});
		
		var tpl = "";
		
		tpl += "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td>Наименование заявки: {request_name}</td></tr>";
		tpl += "<tr><td>{limit_data}{overflow_limit_data}</td></tr>";
		tpl += "</table>";

		this.InformationPanel.setTpl(tpl);

        this.DrugRequestQuotaPanel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: false,
            fileUpload: true,
            labelAlign: 'left',
            labelWidth: 50,
            border: false,
            bodyStyle: 'margin-left: 8px;',
            items: [{
                fieldLabel: 'Лимит',
                name: 'DrugRequestQuota_Total',
                xtype: 'numberfield',
                allowDecimals: true,
                allowNegative: false,
                allowBlank: true,
                width: 100,
                listeners: {
                    change: function(field, new_value, old_value) {
                        wnd.saveDrugRequestQuotaTotal({
                            callback: function(data) {
                                if(!data.success) {
                                    field.setValue(old_value);
                                }
                                wnd.checkLimit();
                            }
                        });
                    }
                }
            }]
        });
		
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
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
			items:[{				
				autoHeight: true,
				region: 'north',
				layout: 'form',
				items:[
                    {
                        frame: true,
                        items: [
                            this.InformationPanel,
                            this.DrugRequestQuotaPanel
                        ]
                    },
					this.FilterPanel
				]
			},
			this.SumGrid,
			this.DrugGrid]
		});
		sw.Promed.swConsolidatedDrugRequestEditWindow.superclass.initComponent.apply(this, arguments);
	}
});