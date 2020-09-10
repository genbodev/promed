/**
 * swWhsDocumentUcEditWindow - окно редактирования заявки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Farmacy
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.03.2016
 */
sw.Promed.swWhsDocumentUcEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Редактирование',
	layout: 'border',
	id: 'WhsDocumentUcEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	checkEmptyPrepSeries: function() { //проверяет наличие в спецификации позиций без серии, возвращает true, если таких позиций нет
		var result = true;
		this.StrGrid.getGrid().getStore().each(function(record){
			var ser = record.get('DocumentUcStr_Ser');
			if (record.get('DocumentUcStr_id') > 0 && (!ser || ser == null || ser == '')) {
				result = false;
				return false;
			}
		});
		return result;
	},
	doSave:  function(options) {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentUcEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!this.checkDataBeforeSave()) {
			return false;
		}

		this.submit(options);

		return true;
	},
	submit: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var wnd = this;
		wnd.getLoadMask('Подождите, идет сохранение...').show();

		var params = new Object();
		params.action = wnd.action;
		params.Org_sid = wnd.form.findField('Org_sid').getValue();
		params.Org_tid = wnd.form.findField('Org_tid').getValue();
		params.WhsDocumentUc_Sum = wnd.inf_form.findField('WhsDocumentUc_Sum').getValue();
		params.WhsDocumentSpecificationJSON = wnd.StrGrid.getJSONChangedData();

		if (wnd.form.findField('WhsDocumentStatusType_id').disabled) {
			params.WhsDocumentStatusType_id = wnd.form.findField('WhsDocumentStatusType_id').getValue();
		}
		if (wnd.form.findField('DrugFinance_id').disabled) {
			params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		}
		if (wnd.form.findField('WhsDocumentCostItemType_id').disabled) {
			params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		}

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
				if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.DocumentUc_id);
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.WhsDocumentUc_id > 0) {
					wnd.WhsDocumentUc_id = action.result.WhsDocumentUc_id;
					if (typeof options.callback == 'function' ) {
						options.callback();
					} else if (typeof wnd.callback == 'function' ) {
                        wnd.callback(wnd.owner, action.result.DocumentUc_id);
                    }
				} else {
                    if (typeof wnd.callback == 'function' ) {
                        wnd.callback(wnd.owner, action.result.DocumentUc_id);
                    }
                }
			}
		});
	},
	doPrint: function(){
        var wnd = this;
        if (this.action == 'view') {
            wnd.printReport();
        } else { //нужно предварительно сохранить заявку
            this.doSave({
                callback: function() {
                    var show_params = new Object();
                    Ext.apply(show_params, wnd.lastShowArguments);
                    show_params.action = 'edit';
                    show_params.WhsDocumentUc_id = wnd.WhsDocumentUc_id;
                    wnd.printReport();
                    wnd.show(show_params); //открывем окно заявки заново
                    if (wnd.owner && 'refreshRecords' in wnd.owner) { //обновляем родительский грид, если он передан
                        wnd.owner.refreshRecords(null,0);
                    }
                }
            });
        }
	},
    printReport: function() {
        var doc_id = this.WhsDocumentUc_id;
        if (!Ext.isEmpty(doc_id)) {
            printBirt({
                'Report_FileName': 'Requisition_M11.rptdesign',
                'Report_Params': '&paramWhsDocumentUc='+doc_id,
                'Report_Format': 'pdf'
            });
        }
    },
	setDefaultValues: function () { //заполнение формы значениями "по умолчанию"
		var wnd = this;
		var current_date = new Date();

		this.form.findField('WhsDocumentClass_Code').setValue(this.WhsDocumentClass_Code);
		this.form.findField('WhsDocumentStatusType_id').setValue(1);
		this.form.findField('WhsDocumentUc_Date').setValue(current_date);

		if (this.FormParams) {
			this.form.setValues(this.FormParams);
			if (this.FormParams.Str_Data) {
				this.StrGrid.addRecords(this.FormParams.Str_Data);
			}
		}

		/*if (this.num_generating_enabled) {
			this.generateWhsDocumentUcNum();
		}*/

		var field_arr = ['WhsDocumentSupply_id','WhsDocumentCostItemType_id','DrugFinance_id', 'Org_tid'];
		for (var i=0; i<field_arr.length; i++) {
			if (this.FormParams[field_arr[i]] && this.FormParams[field_arr[i]] > 0) {
				this.form.findField(field_arr[i]).setValueById(this.FormParams[field_arr[i]]);
			} else {
				this.form.findField(field_arr[i]).setValueById(null);
			}
		}

		var storage_s_combo = this.form.findField('Storage_sid');
        wnd.org_t_combo.getStore().baseParams = new Object();

		if (this.isAptMu) {
			storage_s_combo.getStore().baseParams.Org_id = this.userMedStaffFact.Org_id;
			if (Ext.isEmpty(storage_s_combo.getValue())) {
				storage_s_combo.getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				storage_s_combo.getStore().load({
					callback: function() {
						storage_s_combo.getStore().baseParams.MedService_id = undefined;
						var record = storage_s_combo.getStore().getAt(0);
						if (record && !Ext.isEmpty(record.get('Storage_id'))) {
							storage_s_combo.setValue(record.get('Storage_id'));
							storage_s_combo.fireEvent('change', storage_s_combo, storage_s_combo.getValue());
						}
					}.createDelegate(this)
				});
			}

			if (Ext.isEmpty(wnd.org_t_combo.getValue())) {
                wnd.org_t_combo.setValueById(getGlobalOptions().org_id);
			}
		} else {
			if (Ext.isEmpty(wnd.org_t_combo.getValue())) {
                wnd.org_t_combo.getStore().baseParams.OrgType_id = 5; // 5 - Региональный склад ДЛО
                wnd.org_t_combo.getStore().load({
					callback: function() {
						var record = wnd.org_t_combo.getStore().getAt(0);
						if (record && !Ext.isEmpty(record.get('Org_id'))) {
                            wnd.org_t_combo.setValue(record.get('Org_id'));
                            wnd.org_t_combo.fireEvent('change', wnd.org_t_combo, wnd.org_t_combo.getValue());
						}
					}
				});
			}
		}

		this.document_combo.disableLinkedField();
	},
	setWhsDocumentClass: function(class_code) { //настройка внешнего вида формы в зависимости от класса заявки
		var form = this;
		var allowed_actions = new Array(); //список доступных пунктов в меню действия
		var current_date = new Date();
		var msf_store = sw.Promed.MedStaffFactByUser.store;
        var field_disable = this.StrGrid.readOnly; //по состояниию грида ориентируемся, заблокирована ли форма
		var region_nick = getRegionNick();

		if (!class_code) {
			class_code = this.WhsDocumentClass_Code;
		} else {
			this.WhsDocumentClass_Code = class_code*1;
		}

		//значения по умолчанию
		this.print_report_data = null;
		//this.num_generating_enabled = true;

		this.form.enable_blocked = false;
		this.StrGrid.enable_blocked = false;

		this.form.findField('Mol_sid').hideContainer();
		this.form.findField('Storage_sid').hideContainer();
		this.form.findField('Storage_sid').setAllowBlank(true);
		this.form.findField('Storage_sid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_sid').getStore().baseParams.MedService_id = null;
		//this.form.findField('Storage_sid').getStore().baseParams.StructFilterPreset = 'WhsDocumentUcEdit_Storage_sid';
		this.form.findField('Storage_tid').disable();
		this.form.findField('Storage_tid').hideContainer();
		this.form.findField('Storage_tid').setAllowBlank(true);
		this.form.findField('Storage_tid').getStore().baseParams.StructFilterPreset = 'WhsDocumentUcEdit_Storage_tid';
		this.form.findField('Storage_tid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_tid').getStore().baseParams.MedService_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.StorageForAptMuFirst = null;
        //this.form.findField('Storage_tid').getStore().baseParams.StructFilterPreset = 'WhsDocumentUcEdit_Storage_tid';
		this.form.findField('Org_tid').getStore().baseParams.ExpDate = null;
		this.form.findField('Org_tid').getStore().baseParams.OrgType_CodeList = null;
		this.form.findField('Org_tid').getStore().baseParams.Lpu_id = null;
		//this.form.findField('Org_tid').getStore().baseParams.Lpu_id = null;
        this.StrGrid.setColumnHidden('Drug_Name', true);

		if (!field_disable) {
			this.form.findField('Org_tid').enable();
			this.form.findField('DrugFinance_id').enable();
			this.form.findField('WhsDocumentCostItemType_id').enable();
		}

		//проверка перед сохранением по умолчанию
		this.checkDataBeforeSave = function() {
			return true;
		};

		if (!this.isAptMu) {
			this.form.findField('Org_tid').getStore().baseParams.OrgType_CodeList = '4,5';//Аптека, РАС
		}

		switch(class_code) {
			case 1: //Заявка на поставку

				break;
			case 2: //Накладная-требование
				if (this.isAptMu) {
					this.form.findField('Mol_sid').showContainer();
					this.form.findField('Storage_sid').setAllowBlank(false);
					this.form.findField('Storage_sid').showContainer();
				}
                this.StrGrid.setColumnHidden('Drug_Name', false);
				break;
		}
		this.doLayout();

		this.class_store.load({
			callback: function() {
				var record = this.class_store.findByCode(class_code);
				var doc_name = record ? record.get('WhsDocumentClass_Name') : 'Неизвестный класс';
				if (this.action == "add") {
					this.setTitle(doc_name + ": Добавление");
				} else {
					this.setTitle(doc_name + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				}
			}.createDelegate(this)
		});

		//установка видимости кнопки печати
		/*if (this.print_report_data != null && this.action != "add") {
			this.buttons[2].show();
		} else {
			this.buttons[2].hide();
		}*/
	},
	setFieldLabel: function(field, label) {
		var el = field.el.dom.parentNode.parentNode;
		if(el.children[0].tagName.toLowerCase() === 'label') {
			el.children[0].innerHTML = label+':';
		} else if (el.parentNode.children[0].tagName.toLowerCase() === 'label') {
			el.parentNode.children[0].innerHTML = label+':';
		}
	},
	enableEdit: function(enable) {
		sw.Promed.swWhsDocumentUcEditWindow.superclass.enableEdit.apply(this, arguments);

		this.StrGrid.setReadOnly(!enable);
		this.StrGrid.getAction('action_wdue_actions').items[0].menu.items.each(function(item) {
			item.setDisabled(!enable);
		});
	},
	loadMolCombo: function() {
		var mol_combo = this.form.findField('Mol_sid');
		var mol_id = mol_combo.getValue();
		var storage_id = this.form.findField('Storage_sid').getValue();
		var date = Ext.util.Format.date(this.form.findField('WhsDocumentUc_Date').getValue(), 'd.m.Y');

		mol_combo.enable();
		mol_combo.setValue(null);
		mol_combo.getStore().removeAll();
		if (!Ext.isEmpty(storage_id)) {
			mol_combo.getStore().load({
				params: {Storage_id: storage_id, onDate: date},
				callback: function() {
					var record = mol_combo.getStore().getById(mol_id);
					if (!record && mol_combo.getStore().getCount() > 0) {
						record = mol_combo.getStore().getAt(0);
					}
					if (record) {
						mol_combo.setValue(record.get('Mol_id'));
						mol_combo.setDisabled(mol_combo.getStore().getCount() == 1);
					}
				}
			});
		}
	},
	/*generateWhsDocumentUcNum: function() {
		var wnd = this;

		wnd.getLoadMask().show();
		Ext.Ajax.request({
			params: {
				WhsDocumentType_Code: 22//Заявка
			},
			callback: function(opt, success, resp) {
				wnd.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj[0].WhsDocumentUc_Num != '') {
					var new_num = response_obj[0].WhsDocumentUc_Num;
					var field = wnd.form.findField('');
					field.setValue(new_num);
					field.fireEvent('change', field, new_num);
				}
			},
			url: '/?c=WhsDocumentUc&m=generateWhsDocumentUcNum'
		});
	},*/
	createSpecifactionByWhsDocumentSupply: function() {
		var wnd = this;
		var supply_id = wnd.form.findField('WhsDocumentSupply_id').getValue();

		if ( supply_id > 0) {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (success && response_obj.success) {
							wnd.StrGrid.addRecords(response_obj.data);
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При создании списка медикаментов возникла ошибка');
						}
					}
				},
				params: {
					WhsDocumentSupply_id: supply_id
				},
				url: '/?c=WhsDocumentUc&m=createWhsDocumentSpecificationByWhsDocumentSupply'
			});
		} else {
			sw.swMsg.alert('Ошибка', 'Для выполнения действия необходимо указать контракт');
		}
	},
	createSpecifactionByPrescr: function() {
		var wnd = this;
		var storage_id = this.form.findField('Storage_sid').getValue();
		var finance_id = this.form.findField('DrugFinance_id').getValue();
		var cost_id = this.form.findField('WhsDocumentCostItemType_id').getValue();
		var supply_id = this.form.findField('WhsDocumentSupply_id').getValue();

		if (storage_id > 0) {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (success && response_obj.success) {
							wnd.StrGrid.addRecords(response_obj.data);

                            //поиск в пришедших данных строк без количества
                            var rec_arr = new Array();
                            if (response_obj.gu_data) {
                                for (i = 0; i < response_obj.gu_data.length; i++) {
                                    rec_arr.push({
                                        Drug_Name: !Ext.isEmpty(response_obj.gu_data[i].DrugTorg_Name) ? response_obj.gu_data[i].DrugTorg_Name : response_obj.gu_data[i].DrugComplexMnn_Name,
                                        GoodsUnit_Name: response_obj.gu_data[i].GoodsUnit_Name
                                    });
                                }
							}
                            //вывод результата операйции
                            if (rec_arr.length == 0) {
                                sw.swMsg.alert('Сообщение', 'Заявка сформирована');
                            } else {
                                sw.swMsg.show({
                                    icon: Ext.MessageBox.QUESTION,
                                    msg: 'Заявка сформирована. Для некоторых медикаментов потребность по назначениям была включена в заявку в полном объеме, т.к. нет достаточных данных для сравнения с остатками отделения. Распечатать список?',
                                    title: langs('Подтверждение'),
                                    buttons: Ext.Msg.YESNO,
                                    fn: function(buttonId, text, obj) {
                                        if ('yes' == buttonId) {
                                            wnd.showCreatedRecordsWithoutKolvo(rec_arr);
                                        }
                                    }
                                });
                            }
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При создании списка медикаментов возникла ошибка');
						}
					}
				},
				params: {
					Storage_id: storage_id,
					MedService_id: this.userMedStaffFact.MedService_id,
					DrugFinance_id: finance_id,
					WhsDocumentCostItemType_id: cost_id,
					WhsDocumentSupply_id: supply_id
				},
				url: '/?c=WhsDocumentUc&m=createWhsDocumentSpecificationByPrescr'
			});
		}
	},
	createSpecifactionBySupplierOst: function() {
		var wnd = this;

		var params = {
			Org_id: this.form.findField('Org_tid').getValue(),
			Storage_id: this.form.findField('Storage_tid').getValue(),
			WhsDocumentSupply_id: this.form.findField('WhsDocumentSupply_id').getValue(),
			DrugFinance_id: this.form.findField('DrugFinance_id').getValue(),
			WhsDocumentCostItemType_id: this.form.findField('WhsDocumentCostItemType_id').getValue()
		};

		params.onSelect = function(selected_data) {
			var selected_data_json = Ext.util.JSON.encode(selected_data);

			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (success && response_obj.success) {
							wnd.StrGrid.addRecords(response_obj.data);
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При создании списка медикаментов возникла ошибка');
						}
					}
				},
				params: {DrugOstatRegistryJSON: selected_data_json},
				url: '/?c=WhsDocumentUc&m=createWhsDocumentSpecificationByDrugOstatRegistry'
			});
		};

		getWnd('swDrugOstatRegistrySelectWindow').show(params);
	},
    printDataOnNewTab: function(data) { //функция для отображения списка в виде таблицы в отдельной вкладке браузера (код и стили позаимстовованы из ext.ux.gridprint)
        var page_title = data.title;
        var columns = data.header_data;
        var row_data = data.row_data;

        //автонумерация строк
        if (!Ext.isEmpty(data.addNumberColumn)) {
            columns.unshift({dataIndex: 'PrintNum', header: langs('№ п/п')});

            for (var i = 0; i < row_data.length; i++) {
                row_data[i].PrintNum = i+1;
            }
        }

        var headings = Ext.ux.GridPrinter.headerTpl.apply(columns);
        var body = Ext.ux.GridPrinter.bodyTpl.apply(columns);

        var html = new Ext.XTemplate(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            '<html>',
            '<head>',
            '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />',
            '<link href="' + Ext.ux.GridPrinter.stylesheetPath + '" rel="stylesheet" type="text/css" media="screen,print" />',
            '<title>' + page_title + '</title>',
            '</head>',
            '<body>',
            '<table>',
            headings,
            '<tpl for=".">',
            body,
            '</tpl>',
            '</table>',
            '</body>',
            '</html>'
        ).apply(row_data);

        // для того, чтобы вывод шел не в уже созданое окно
        var id_salt = Math.random();
        var win_id = 'printgrid' + Math.floor(id_salt*10000);
        // собственно открываем окно и пишем в него
        var win = window.open('', win_id);
        win.document.write(html);
        win.document.close();
    },
    showCreatedRecordsWithoutKolvo: function(data) { //функция для отображения списка в отдельной вкладке
        var header_data = [
            {   dataIndex: 'Drug_Name',
                header: 'Медикамент'
            },
            {   dataIndex: 'GoodsUnit_Name',
                header: 'Ед. измерения'
            }
        ];

        var print_data = new Object({
            title: 'Список медикаментов',
            addNumberColumn: true,
            header_data: header_data,
            row_data: data
        });

        this.printDataOnNewTab(print_data);
    },
	loadForm: function(callback) {
		var wnd = this;
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
				wnd.getLoadMask().hide();
				wnd.hide();
			},
			params:{
				WhsDocumentUc_id: wnd.WhsDocumentUc_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (!result[0]) {
					return false
				}
				wnd.enableEdit(wnd.action != 'view');
				wnd.setWhsDocumentClass(result[0].WhsDocumentClass_Code*1);

				wnd.form.setValues(result[0]);

				var status_combo = wnd.form.findField('WhsDocumentStatusType_id');
				status_combo.setDisabled(status_combo.getFieldValue('WhsDocumentStatusType_Code') == 6 || wnd.action == 'view');//Принят

				if (result[0].WhsDocumentSupply_id > 0) {
					wnd.document_combo.setValueById(result[0].WhsDocumentSupply_id);
				}
				if (result[0].Storage_tid > 0) {
					var storage_t_combo = wnd.form.findField('Storage_tid');
					storage_t_combo.setValueById(result[0].Storage_tid);
					storage_t_combo.fireEvent('change', storage_t_combo, storage_t_combo.getValue());
				}
				if (result[0].Storage_sid > 0) {
					var storage_s_combo = wnd.form.findField('Storage_sid');
					storage_s_combo.setValueById(result[0].Storage_sid);
					storage_s_combo.fireEvent('change', storage_s_combo, storage_s_combo.getValue());
				}
				if (result[0].Org_tid > 0) {
					wnd.org_t_combo.setValueById(result[0].Org_tid);
				}
				if (result[0].DrugFinance_id > 0) {
					wnd.form.findField('DrugFinance_id').setValueById(result[0].DrugFinance_id);
				}
				if (result[0].WhsDocumentCostItemType_id > 0) {
					wnd.form.findField('WhsDocumentCostItemType_id').setValueById(result[0].WhsDocumentCostItemType_id);
				}

				wnd.StrGrid.loadData({
					globalFilters: {
						WhsDocumentSpecificity_id: wnd.form.findField('WhsDocumentSpecificity_id').getValue()
					},
					callback: function() {
						wnd.StrGrid.updateSumm();
					}
				});
				if (typeof callback == 'function') {
					callback();
				}
			},
			url:'/?c=WhsDocumentUc&m=loadWhsDocumentUcForm'
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swWhsDocumentUcEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
		this.WhsDocumentClass_Code = 1;
		this.FormParams = new Object();
		this.userMedStaffFact = {};
		this.isAptMu = false;
        this.lastShowArguments = new Object(); //параметры последнего открытия формы

		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
			return false;
		} else {
            Ext.apply(this.lastShowArguments, arguments[0]);
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].WhsDocumentStatusType_Code  && arguments[0].WhsDocumentStatusType_Code == 6 ) { //Заявка принята
			this.action = 'view';
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentClass_Code ) {
			this.WhsDocumentClass_Code = arguments[0].WhsDocumentClass_Code*1;
		}
		if ( arguments[0].FormParams ) {
			this.FormParams = arguments[0].FormParams;
		}
		if ( arguments[0].userMedStaffFact ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		if (!Ext.isEmpty(this.userMedStaffFact.Lpu_id) && this.userMedStaffFact.MedServiceType_SysNick == 'merch') {
			this.isAptMu = true;
		}

		if(!wnd.StrGrid.getAction('action_wdue_actions')) {
			var actions_menu = new Ext.menu.Menu({
				items: [{
					id: 'WDUEW_create_by_contract',
					name: 'create_by_contract',
					disabled: true,
					iconCls: 'add16',
					text: 'Заполнить по спецификации контракта',
					handler: wnd.createSpecifactionByWhsDocumentSupply.createDelegate(wnd)
				},
				{
					id: 'WDUEW_create_by_prescr',
					name: 'create_by_prescr',
					iconCls: 'add16',
					text: 'Сформировать на основе назначений',
					handler: wnd.createSpecifactionByPrescr.createDelegate(wnd)
				},
				{
					id: 'WDUEW_create_by_ostat',
					name: 'create_by_ostat',
					iconCls: 'add16',
					text: 'Сформировать по остаткам поставщика',
					handler: wnd.createSpecifactionBySupplierOst.createDelegate(wnd)
				}]
			});

			wnd.StrGrid.addActions({
				name:'action_wdue_actions',
				text:'Действия',
				menu: actions_menu,
				iconCls: 'actions16'
			});
		}

		this.setWhsDocumentClass();
		this.form.reset();

        this.org_t_combo.fullReset();

        this.form.findField('Org_sid').setValue(getGlobalOptions().org_id);
        this.form.findField('Org_sName').setValue(getGlobalOptions().Org_Name);

        var storage_s_combo = this.form.findField('Storage_sid');
        var storage_t_combo = this.form.findField('Storage_tid');

        if (Ext.isEmpty(storage_s_combo.getStore().baseParams)) {
            storage_s_combo.getStore().baseParams = new Object();
        }
        if (Ext.isEmpty(storage_t_combo.getStore().baseParams)) {
            storage_t_combo.getStore().baseParams = new Object();
        }

        if (getGlobalOptions().orgtype == 'lpu') {
            var lpusection_id = getGlobalOptions().CurLpuSection_id;
            var lpusection_name = getGlobalOptions().CurLpuSection_Name;

            this.form.findField('LpuSection_Name').showContainer();
            this.form.findField('LpuSection_Name').setValue(lpusection_name);
            this.form.findField('LpuSection_id').setValue(lpusection_id);
            storage_s_combo.getStore().baseParams.LpuSection_id = lpusection_id;
            storage_t_combo.getStore().baseParams.LpuSection_id = lpusection_id;
        } else {
            this.form.findField('LpuSection_Name').hideContainer();
            storage_s_combo.getStore().baseParams.LpuSection_id = null;
            storage_t_combo.getStore().baseParams.LpuSection_id = null;
        }

        this.form.findField('WhsDocumentStatusType_id').lastQuery = '';
        this.form.findField('WhsDocumentStatusType_id').getStore().clearFilter();
        if (getGlobalOptions().org_id == this.userMedStaffFact.Org_id) { //это условие мне не совсем понятно, перенес его в этот блок с минимальными изменениями (Salakhov R.)
            this.form.findField('WhsDocumentStatusType_id').getStore().filterBy(function(rec) {
                return (Number(rec.get('WhsDocumentStatusType_Code')).inlist([1,4,5,8]));
            });
        }

        this.form.findField('WhsDocumentUc_Num').ownerCt.hide();

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
		loadMask.show();
		switch (this.action) {
			case 'add':
                wnd.form.findField('WhsDocumentUc_Num').ownerCt.hide();
                wnd.form.findField('WhsDocumentUc_Date').setLabelWidth(160);
				wnd.enableEdit(true);
				wnd.setWhsDocumentClass();
				wnd.StrGrid.removeAll();
				wnd.StrGrid.updateSumm();
				wnd.setDefaultValues();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
                wnd.form.findField('WhsDocumentUc_Num').ownerCt.show();
                wnd.form.findField('WhsDocumentUc_Date').setLabelWidth(30);
				this.loadForm(function(){loadMask.hide()});
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

		wnd.class_store = new Ext.db.AdapterStore({
			autoLoad: false,
			dbFile: 'Promed.db',
			fields: [
				{name: 'WhsDocumentClass_id', type: 'int'},
				{name: 'WhsDocumentClass_Code', type: 'int'},
				{name: 'WhsDocumentClass_Name', type: 'string'}
			],
			key: 'WhsDocumentClass_id',
			sortInfo: {field: 'WhsDocumentClass_Code'},
			tableName: 'WhsDocumentClass',
			findByCode: function(code) {
				var index = this.findBy(function(rec) { return rec.get('WhsDocumentClass_Code') == code; });
				return this.getAt(index);
			}
		});

		wnd.document_combo = new sw.Promed.SwDrugComplexMnnCombo({
			width: 600,
			displayField: 'WhsDocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: 'Контракт',
			forceSelection: true,
			hiddenName: 'WhsDocumentSupply_id',
			loadingText: 'Идет поиск...',
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			trigger2Class: 'x-form-search-trigger',
			resizable: true,
			selectOnFocus: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;">&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'WhsDocumentSupply_id',
			listeners: {
				select: function(combo, record) {
					this.setLinkedFieldValues(record);
				},
				keydown: function(combo, e) {
					if ( e.getKey() == e.DELETE)
					{
						combo.setValue(null);
						combo.setLinkedFieldValues();
						e.stopEvent();
						return true;
					}

					if (e.getKey() == e.F4)
					{
						combo.onTrigger2Click();
					}
				}.createDelegate(this)
			},
			onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var searchWindow = 'swWhsDocumentSupplySelectWindow';
				var params = this.getStore().baseParams;
				var combo = this;
				combo.disableBlurAction = true;
				getWnd(searchWindow).show({
					params: params,
					searchUrl: '/?c=Farmacy&m=loadWhsDocumentSupplyList',
					FilterPanelEnabled: true,
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (data) {
						combo.fireEvent('beforeselect', combo);

						combo.getStore().removeAll();
						combo.getStore().loadData([{
							WhsDocumentUc_id: data.WhsDocumentUc_id,
							WhsDocumentSupply_id: data.WhsDocumentSupply_id,
							WhsDocumentUc_Name: data.WhsDocumentUc_Name,
							WhsDocumentUc_Date: data.WhsDocumentUc_Date,
							WhsDocumentUc_Num: data.WhsDocumentUc_Num,
							Org_sid: data.Org_sid,
							DrugFinance_id: data.DrugFinance_id,
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id
						}], true);

						combo.setValue(data.WhsDocumentUc_id);
						var index = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentSupply_id') == data.WhsDocumentSupply_id; });

						if (index == -1) {
							return false;
						}

						var record = combo.getStore().getAt(index);

						if ( typeof record == 'object' ) {
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, record.get('WhsDocumentSupply_id'));
						}

						combo.setLinkedFieldValues(record);

						getWnd(searchWindow).hide();
					}
				});
			},
			resetCombo: function() {
				this.lastQuery = '';
				this.getStore().removeAll();
				this.getStore().baseParams.query = '';
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.WhsDocumentSupply_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.WhsDocumentSupply_id = null;
						combo.disableLinkedField();
					}
				});
			},
			setLinkedFieldValues: function(record) {
				if (!record) {
					record = this.getStore().getById(this.getValue());
				}

				if (!record || Ext.isEmpty(record.get('WhsDocumentSupply_id')) || wnd.StrGrid.readOnly) {
					Ext.getCmp('WDUEW_create_by_contract').disable();
				} else {
					Ext.getCmp('WDUEW_create_by_contract').enable();
				}

				wnd.form.findField('DrugFinance_id').setValueById(record?record.get('DrugFinance_id'):null);
				wnd.form.findField('WhsDocumentCostItemType_id').setValueById(record?record.get('WhsDocumentCostItemType_id'):null);
				wnd.form.findField('Org_tid').setValueById(record?record.get('Org_sid'):null);

				this.disableLinkedField(record);
			},
			disableLinkedField: function(record) {
				if (!record) {
					var index = this.getStore().findBy(function(rec) { return rec.get('WhsDocumentSupply_id') == this.getValue(); }.createDelegate(this));
					/*if (index == -1) {
						return false;
					}*/
					record = this.getStore().getAt(index);
				}
				var fin_combo = wnd.form.findField('DrugFinance_id');
				var cost_combo = wnd.form.findField('WhsDocumentCostItemType_id');

				if (record && fin_combo.getValue() == record.get('DrugFinance_id')) {
					fin_combo.disable();
				} else if (wnd.action != 'view') {
					fin_combo.enable();
				}

				if (record && cost_combo.getValue() == record.get('WhsDocumentCostItemType_id')) {
					cost_combo.disable();
				} else if (wnd.action != 'view') {
					cost_combo.enable();
				}

				if (record && wnd.org_t_combo.getValue() == record.get('Org_sid')) {
                    wnd.org_t_combo.disable();
				} else if (wnd.action != 'view') {
                    wnd.org_t_combo.enable();
				}
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'WhsDocumentSupply_id'
						},
						[
							{name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id'},
							{name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date'},
							{name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num'},
							{name: 'Org_sid', mapping: 'Org_sid'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'}
						]),
					url: '/?c=Farmacy&m=loadWhsDocumentSupplyList'
				});
			}
		});

        this.org_t_combo = new sw.Promed.SwCustomOwnerCombo({
            width: 600,
            anchor: false,
            name: 'Org_tid',
            fieldLabel: 'Поставщик',
            hiddenName: 'Org_tid',
            id: 'WDUEW_Org_tid',
            displayField: 'Org_Name',
            valueField: 'Org_id',
            allowBlank: true,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Org_id', mapping: 'Org_id' },
                    { name: 'Org_Name', mapping: 'Org_Name' }
                ],
                key: 'Org_id',
                sortInfo: { field: 'Org_Name' },
                url:'/?c=WhsDocumentUc&m=loadOrgCombo'
            }),
            ownerWindow: wnd,
            childrenList: ['Storage_tid'],
            setLinkedFieldValues: function(event_name) {
                var storage_combo = wnd.form.findField('Storage_tid');
                var storage_id = storage_combo.getValue();
                var field_disable = wnd.StrGrid.readOnly; //по состояниию грида ориентируемся, заблокирована ли форма

                if (wnd.isAptMu && this.getValue() == wnd.userMedStaffFact.Org_id && wnd.WhsDocumentClass_Code == 2) {
                	if (!field_disable) {
                        storage_combo.enable();
					} else {
                        storage_combo.disable();
					}
                    storage_combo.showContainer();
                    storage_combo.getStore().baseParams.Lpu_oid = wnd.userMedStaffFact.Lpu_id;
                    storage_combo.getStore().baseParams.StorageForAptMuFirst = true;
                } else {
                    storage_combo.disable();
                    storage_combo.hideContainer();
                    storage_combo.getStore().baseParams.Lpu_oid = null;
                    storage_combo.getStore().baseParams.StorageForAptMuFirst = null;
                }
                wnd.doLayout();

                if (event_name == 'set_by_id') {
                    wnd.document_combo.disableLinkedField();
                }

                if (event_name == 'change') {
                    Ext.getCmp('WDUEW_create_by_ostat').setDisabled(Ext.isEmpty(this.getValue()) || wnd.StrGrid.readOnly);
                    if (wnd.isAptMu && this.getValue() == wnd.userMedStaffFact.Org_id && wnd.WhsDocumentClass_Code == 2) {
                        storage_combo.getStore().load({
                            callback: function() {
                                var record = storage_combo.getStore().getById(storage_id);
                                if (!record) {
                                    var index = storage_combo.getStore().findBy(function(rec) { return rec.get('StorageStructLevel') == 'Lpu'; });
                                    record = storage_combo.getStore().getAt(index);
                                }
                                if (record && !Ext.isEmpty(record.get('Storage_id'))) {
                                    storage_combo.setValue(record.get('Storage_id'));
                                }
                            }
                        });
                    } else {
                        storage_combo.getStore().removeAll();
                        storage_combo.setValue(null);
                    }
                }
            }
        });

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			//height: 270,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'north',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentUcEditForm',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 160,
				labelAlign: 'right',
				collapsible: true,
				url:'/?c=WhsDocumentUc&m=saveWhsDocumentUc',
				items: [{
					xtype: 'hidden',
					name: 'WhsDocumentUc_id'
				}, {
					xtype: 'hidden',
					name: 'WhsDocumentSpecificity_id'
				}, {
					xtype: 'hidden',
					name: 'WhsDocumentClass_Code'
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [/*{
							allowBlank: false,
							enableKeyEvents: true,
							validateOnBlur: false,
							xtype: 'trigger',
							triggerClass: 'x-form-plus-trigger',
							name: 'WhsDocumentUc_Num',
							fieldLabel: '№',
							onTriggerClick: function() {
								var field = wnd.form.findField('WhsDocumentUc_Num');
								if (!field.disabled) {
									wnd.generateWhsDocumentUcNum();
								}
							}.createDelegate(this),
							width: 160
						}*/{
                            xtype: 'textfield',
                            name: 'WhsDocumentUc_Num',
                            fieldLabel: '№',
                            width: 160,
                            disabled: true
                        }]
					}, {
						layout: 'form',
						labelWidth: 30,
						items: [{
							allowBlank: false,
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'WhsDocumentUc_Date',
							fieldLabel: 'от',
							listeners: {
								'change': function(field, newValue, oldValue) {
									var date = (newValue instanceof Date)?newValue.format('d.m.Y'):newValue;

									this.form.findField('Storage_sid').filterByDate(date);
									this.loadMolCombo();
								}.createDelegate(this)
							},
							width: 120,
                            getLabelEl: function() {
                                if (this.el && this.el.parent('.x-form-item')) {
                                    return this.el.parent('.x-form-item').child('.x-form-item-label');
                                }
                                return null;
                            },
                            setLabelWidth: function(labelWidth) {
                                this.labelWidth = Number(labelWidth);
                                if (this.getLabelEl()) {
                                    this.getLabelEl().setWidth(this.labelWidth+5);
                                    this.container.setStyle('padding-left', (this.labelWidth+5)+'px');
                                }
                            }
						}]
					}, {
						layout: 'form',
						labelWidth: 80,
						items: [{
							allowBlank: false,
							xtype: 'swcommonsprcombo',
							comboSubject: 'WhsDocumentStatusType',
							hiddenName: 'WhsDocumentStatusType_id',
							fieldLabel: 'Статус',
							width: 200
						}]
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Организация',
						name: 'Org_sName',
                        disabled: true,
						width: 600
					}, {
						xtype: 'hidden',
						name: 'Org_sid'
					}]
				}, {
					layout: 'form',
					items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Отделение',
                        name: 'LpuSection_Name',
                        disabled: true,
                        width: 600
                    },{
                        xtype: 'hidden',
                        name: 'LpuSection_id'
                    }]
                }, {
					layout: 'form',
					items: [{
						xtype: 'swstoragecombo',
						fieldLabel: 'Склад',
						hiddenName: 'Storage_sid',
						onTrigger2Click: function() {
							var combo = this;
							combo.setValue(null);
							combo.fireEvent('change', this, this.getValue());
						},
						listeners: {
							'change': function(combo, newValue, oldValue) {
								Ext.getCmp('WDUEW_create_by_prescr').setDisabled(Ext.isEmpty(newValue) || wnd.StrGrid.readOnly);

								this.loadMolCombo();
							}.createDelegate(this)
						},
						width: 600
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swmolcombo',
						fieldLabel: 'МОЛ',
						hiddenName: 'Mol_sid',
						width: 600
					}]
				}, {
					layout: 'form',
					items: [this.document_combo]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swdrugfinanceremotecombo',
							name: 'DrugFinance_id',
							fieldLabel: 'Источник финансирования',
							setValueById: function(id) {
								var combo = this;
								combo.getStore().load({
									params: {DrugFinance_id: id},
									callback: function() {
										combo.setValue(id);
										combo.fireEvent('change', combo, combo.getValue());
										wnd.document_combo.disableLinkedField();
									}
								});
							},
							listWidth: 230,
							width: 230
						}]
					}, {
						layout: 'form',
						labelWidth: 115,
						items: [{
							xtype: 'swwhsdocumentcostitemtyperemotecombo',
							name: 'WhsDocumentCostItemType_id',
							fieldLabel: 'Статья расхода',
							setValueById: function(id) {
								var combo = this;
								combo.getStore().load({
									params: {WhsDocumentCostItemType_id: id},
									callback: function() {
										combo.setValue(id);
										combo.fireEvent('change', combo, combo.getValue());
										wnd.document_combo.disableLinkedField();
									}
								});
							},
							listWidth: 250,
							width: 250
						}]
					}]
				}, {
					layout: 'form',
					items: [
                        wnd.org_t_combo
					/*{
						allowBlank: false,
						xtype: 'sworgcombo',
						fieldLabel: 'Поставщик',
						hiddenName: 'Org_tid',
						onTrigger2Click: function() {
							var combo = this;
							combo.setValue(null);
							combo.fireEvent('change', combo, combo.getValue());
						},
						setValueById: function(id) {
							var combo = this;
							combo.store.load({
								params: {Org_id: id},
								callback: function(){
									combo.setValue(id);
									combo.fireEvent('change', combo, combo.getValue());
									wnd.document_combo.disableLinkedField();
								}
							});
						},
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var storage_combo = this.form.findField('Storage_tid');
								var storage_id = storage_combo.getValue();

								Ext.getCmp('WDUEW_create_by_ostat').setDisabled(Ext.isEmpty(newValue) || wnd.StrGrid.readOnly);

								if (this.isAptMu && newValue == this.userMedStaffFact.Org_id && wnd.WhsDocumentClass_Code == 2) {
									storage_combo.showContainer();
									storage_combo.getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
									storage_combo.getStore().baseParams.StorageForAptMuFirst = true;
									storage_combo.getStore().load({
										callback: function() {
											var record = storage_combo.getStore().getById(storage_id);
											if (!record) {
												var index = storage_combo.getStore().findBy(function(rec) { return rec.get('StorageStructLevel') == 'Lpu'; });
												record = storage_combo.getStore().getAt(index);
											}
											if (record && !Ext.isEmpty(record.get('Storage_id'))) {
												storage_combo.setValue(record.get('Storage_id'));
											}
										}
									});
								} else {
									storage_combo.hideContainer();
									storage_combo.getStore().removeAll();
									storage_combo.getStore().baseParams.Lpu_oid = null;
									storage_combo.getStore().baseParams.StorageForAptMuFirst = null;
									storage_combo.setValue(null);
								}
								this.doLayout();
							}.createDelegate(this)
						},
						width: 600
					}*/]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swstoragecombo',
						fieldLabel: 'Склад',
						hiddenName: 'Storage_tid',
						width: 600,
                        loadData: function() {
                            var combo = this;
                            combo.getStore().load({
                                callback: function(){
                                    combo.setValue(null);
                                    //combo.onLoadData();
                                }
                            });
                        }
					}]
				}]
			}]
		});

		this.inf_form_panel = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			autoHeight: true,
			border: false,
			frame: true,
			region: 'south',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentUcEditInfForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle: 'background:#DFE8F6;padding:5px;',
				labelAlign: 'right',
				border: true,
				labelWidth: 80,
				items: [{
					xtype: 'textfield',
					fieldLabel: 'Сумма',
					name: 'WhsDocumentUc_Sum',
					width: 200,
					disabled: true
				}]
			}]
		});

        this.createTooltip = function(ss) {
            var view = ss;
            view.tip = new Ext.ToolTip({
                target: view.el,
                delegate: view.itemSelector,
                trackMouse: true,
                renderTo: Ext.getBody(),
                autoHide: false,
                minWidth : 300,
                listeners: {
                    beforeshow: function (tip) {
                        var wind = wnd.StrGrid.getGrid();
                        var ss = wind.getStore();
                        var ww = wind.getView();
                        var indX = ww.findRowIndex(tip.triggerElement);
                        var bodyHint = '';
                        var br = '<br>';

                        if (indX !== false && indX >= 0) {
                            var dataObj = ss.getAt(indX);
                            if (dataObj && !Ext.isEmpty(dataObj.get('Drug_id'))) {
                                var mark = '&#8226;';
                                var str1 = dataObj.get('hintTradeName');
                                var str2 = dataObj.get('hintPackagingData');
                                var str3 = dataObj.get('hintRegistrationData');
                                var str4 = dataObj.get('hintPRUP');
                                var firmNames = dataObj.get('hintFirmNames');

                                str1 = (str1) ? str1.replace(/"/g, "&#8242;") : '';
                                if (str2) {
                                    if (firmNames) {
                                        var pr = firmNames.replace('(', '\\(');
                                        pr = firmNames.replace(')', '\\)');
                                        var re = new RegExp("(, |^)"+pr+".*");
                                        str2 = str2.replace(re, '');
                                        str2 = str2.replace(/"/g, "&#8242;");
                                    } else {
                                        str2 = str2.replace(/"/g, "&#8242;");
                                    }
                                } else {
                                    str2 = '';
                                }
                                str3 = (str3) ? str3.replace(/"/g, "&#8242;"): '';
                                str4 = (str4) ? str4.replace(/"/g, "&#8242;"): '';

                                if (str1) {
                                    bodyHint += mark + ' Торговое наименование: ' + str1 + br;
                                }
                                if (str2) {
                                    if(str2.slice(-1) == ',') str2 = str2.slice(0, -1);
                                    bodyHint += mark + ' Данные об упаковке: ' + str2 + br;
                                }
                                if (str3) {
                                    if(str3.slice(-1) == ',') str3 = str3.slice(0, -1);
                                    bodyHint += mark + ' Данные о регистрации: ' + str3 + br;
                                }
                                if (str4) {
                                    bodyHint += mark + ' Пр./Уп.: ' + str4;
                                }
                            }

                            tip.body.dom.innerHTML = bodyHint;
                        } else {
                            tip.on('show', function() {
                                tip.hide();
                            }, tip, {single: true});
                        }

                        if (Ext.isEmpty(bodyHint)) {
                            tip.hide();
                            return false;
                        }
                    }
                }
            });
        };

		this.StrGrid = new sw.Promed.ViewFrame({
			title: 'Медикаменты',
			actions: [
				{name: 'action_add', handler: function() { wnd.StrGrid.editGrid('add') }},
				{name: 'action_edit', handler: function() { wnd.StrGrid.editGrid('edit') }},
				{name: 'action_view', handler: function() { wnd.StrGrid.editGrid('view') }},
				{name: 'action_delete', handler: function() { wnd.StrGrid.deleteRecord() }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentUc&m=loadWhsDocumentSpecificationGrid',
			height: 180,
			region: 'center',
			object: 'WhsDocumentSpecification',
			editformclassname: 'swWhsDocumentSpecificationEditWindow',
			id: 'WDUEW_WhsDocumentSpecificationGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'WhsDocumentSpecification_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugNomen_id', type: 'int', hidden: true},
				{name: 'DrugComplexMnn_id', type: 'int', hidden: true},
				{name: 'Tradenames_id', type: 'int', hidden: true},
				//{name: 'Okei_id', type: 'int', hidden: true},
				{name: 'GoodsUnit_id', type: 'int', hidden: true},
				{name: 'EvnCourseTreat_id', type: 'int', hidden: true},
				{name: 'ReceptOtov_id', type: 'int', hidden: true},
				{name: 'WhsDocumentSpecification_Method', type: 'string', hidden: true},
				{name: 'DrugComplexMnnCode_Code', type: 'int', header: 'Код', width: 100},
				{name: 'Drug_Ean', type: 'int', header: 'EAN', width: 100},
				{name: 'DrugComplexMnn_Name', type: 'string', header: 'Наименование', id: 'autoexpand'},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'Drug_Name', type: 'string', header: 'Медикамент', width: 180},
				{name: 'DrugComplexMnn_Dose', type: 'string', header: 'Дозировка', width: 100},
				{name: 'RlsClsdrugforms_RusName', type: 'string', header: 'Форма выпуска', width: 120},
				{name: 'DrugTorg_Name', type: 'string', header: 'Торговое наименование', width: 200},
				//{name: 'Okei_Name', type: 'string', header: 'Ед. изм.', width: 120},
				{name: 'GoodsUnit_Name', type: 'string', header: 'Ед. уч.', width: 120},
				{name: 'WhsDocumentSpecification_Count', type: 'float', header: 'Заявлено кол-во', width: 100},
				{name: 'WhsDocumentSpecification_Cost', type: 'float', header: 'Цена', width: 100},
				{name: 'OtpuskCount', type: 'float', header: 'Отпущено', width: 100},
				{name: 'OtpuskSum', type: 'float', header: 'На сумму', width: 100},
				{name: 'Budget', type: 'string', header: 'В бюдж. заявке', width: 100},
				{name: 'WhsDocumentSpecification_Method', type: 'string', header: 'Способ применения', width: 120},
				{name: 'WhsDocumentSpecification_Note', type: 'string', header: 'Примечание', width: 260},
                {name: 'hintPackagingData', type: 'string', hidden: true},
                {name: 'hintRegistrationData', type: 'string', hidden: true},
                {name: 'hintPRUP', type: 'string', hidden: true},
                {name: 'hintFirmNames', type: 'string', hidden: true},
                {name: 'hintTradeName', type: 'string', hidden: true}
			],
			toolbar: true,
			listeners: {
                render: wnd.createTooltip
            },
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('WhsDocumentSpecification_id') > 0) {
					this.ViewActions.action_view.setDisabled(false);
				}

				if (record.get('WhsDocumentSpecification_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					//this.ViewActions.action_wdue_actions.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					//this.ViewActions.action_wdue_actions.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			editGrid: function (action, options) {
				if (action == null)	action = 'add';

				//во избежание ввода спецификации до заполнения шапки документа
				if (action != 'view' && !wnd.form.isValid()) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							wnd.findById('WhsDocumentUcEditForm').getFirstInvalidEl().focus(true);
						},
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}

				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				if (action == 'add') {
					var record_count = store.getCount();
					var params = new Object();
					if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSpecification_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					params.WhsDocumentSpecificity_id = wnd.form.findField('WhsDocumentSpecificity_id').getValue();
					params.WhsDocumentSupply_id = wnd.form.findField('WhsDocumentSupply_id').getValue();

					if (options && options.copy_data) {
						Ext.apply(params, options.copy_data);
					}

					getWnd(view_frame.editformclassname).show({
						owner: view_frame,
						action: action,
						params: params,
						WhsDocumentClass_Code: wnd.WhsDocumentClass_Code,
						userMedStaffFact: wnd.userMedStaffFact,
						onSave: function(data) {
							if (data.RecordForMerge_id && data.RecordForMerge_id > 0) {
								view_frame.updateRecordById(data.RecordForMerge_id, data);
							} else {
								if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSpecification_id') ) {
									view_frame.removeAll({ addEmptyRecord: false });
								}
								var record = new Ext.data.Record.create(view_frame.jsonData['store']);
								view_frame.clearFilter();
								data.WhsDocumentSpecification_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
								data.state = 'add';
								store.insert(record_count, new record(data));
								view_frame.setFilter();
								view_frame.updateSumm();
								view_frame.initActionPrint();
							}
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record && selected_record.get('WhsDocumentSpecification_id') > 0) {
						var params = selected_record.data;
						params.WhsDocumentSpecificity_id = wnd.form.findField('WhsDocumentSpecificity_id').getValue();
						params.WhsDocumentSupply_id = wnd.form.findField('WhsDocumentSupply_id').getValue();

						getWnd(view_frame.editformclassname).show({
							owner: view_frame,
							action: action,
							params: params,
							WhsDocumentClass_Code: wnd.WhsDocumentClass_Code,
							userMedStaffFact: wnd.userMedStaffFact,
							onSave: function(data) {
								if (data.RecordForMerge_id && data.RecordForMerge_id > 0) {
									view_frame.updateRecordById(data.RecordForMerge_id, data);
								} else {
									view_frame.updateRecordById(selected_record.get('WhsDocumentSpecification_id'), data);
								}
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
				this.updateSumm();
			},
			addRecords: function(data_arr){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var record_count = store.getCount();
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);

				if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSpecification_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
					record_count = 0;
				}

				view_frame.clearFilter();
				for (var i = 0; i < data_arr.length; i++) {
					data_arr[i].WhsDocumentSpecification_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					data_arr[i].state = 'add';
					store.insert(record_count, new record(data_arr[i]));
				}
				view_frame.setFilter();
				view_frame.updateSumm();
			},
			updateRecordById: function(record_id, data) {
				var index = this.getGrid().getStore().findBy(function(rec) { return rec.get('WhsDocumentSpecification_id') == record_id; });
				if (index == -1) {
					return false;
				}
				var record = this.getGrid().getStore().getAt(index);

				for(var key in data) {
					if (key != 'WhsDocumentSpecification_id') {
						record.set(key, data[key]);
					}
				}
				if (record.get('state') != 'add') {
					record.set('state', 'edit');
				}
				record.commit();
				this.updateSumm();
			},
			updateSumm: function() {
				var summ = 0;
				var summ_field = wnd.inf_form.findField('WhsDocumentUc_Sum');

				this.getGrid().getStore().each(function(record) {
					if(record.get('WhsDocumentSpecification_Cost') && record.get('WhsDocumentSpecification_Cost') > 0) {
						summ += record.get('WhsDocumentSpecification_Count')*record.get('WhsDocumentSpecification_Cost');
					}
				});

				summ_field.setValue(summ.toFixed(2));
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var item = record.data;
						//item.FileData = null;
						data.push(item);
					}
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			}
		});

		this.buttonSave = new Ext.Button({
			handler: function() {
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			text: BTN_FRMSAVE
		})

		Ext.apply(this, {
			layout: 'border',
			buttons: [
			this.buttonSave,
			//this.executeButton,
			{
				handler: function() {
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				text: 'Печать'
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
				form,
				this.StrGrid,
				this.inf_form_panel
			]
		});
		sw.Promed.swWhsDocumentUcEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentUcEditForm').getForm();
		this.inf_form = this.findById('WhsDocumentUcEditInfForm').getForm();
	}
});