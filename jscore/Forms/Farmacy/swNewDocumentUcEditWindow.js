/**
* swNewDocumentUcEditWindow - окно редактирования документа учета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      06.2014
* @comment
*/
sw.Promed.swNewDocumentUcEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: langs('Редактирование'),
	layout: 'border',
	id: 'NewDocumentUcEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		},
		beforehide: function() {
			if(this.isChanged) {
				sw.swMsg.show({
	                buttons: Ext.Msg.YESNO,
	                fn: function(buttonId) {
	                	if ( buttonId == 'yes' ) {
	                		this.isChanged = false;
	                		this.hide();
	                	}
	                }.createDelegate(this),
	                icon: Ext.Msg.WARNING,
	                msg: langs('Вы действительно желаете закрыть документ без сохранения?'),
	                title: langs('Предупреждение')
	            });
	            return false;
			} else {
				return true;
			}
		},
		activate: function(){
			log('ac');
			this.ScanCodeService.start();
		},
		deactivate: function() {
			log('de');
			this.ScanCodeService.stop();
		}
	},
	onHide: Ext.emptyFn,
	isSmpMainStorage: false, //Флаг создания документа из центрального склада СМП
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
					wnd.findById('NewDocumentUcEditForm').getFirstInvalidEl().focus(true);
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

		//делаем проверку на заполненность серии в позициях спецификации
		if (!options && !this.checkEmptyPrepSeries()) {
			if (confirm(langs('Не для всех медикаментов указана серия. Продолжить?'))) {
				this.submit(options);
			}
		} else {
			this.submit(options);
		}

		return true;
	},
	submit: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var wnd = this;
		wnd.getLoadMask(langs('Подождите, идет сохранение...')).show();

		if (this.DrugDocumentType_Code.inlist([15,33])) {	//Контрагент поставщика и получателя один и тот же
			wnd.form.findField('Contragent_tid').setValue(wnd.form.findField('Contragent_sid').getValue());
		}

		var params = new Object();
		params.action = wnd.action;
		params.DocumentUc_pid = wnd.form.findField('DocumentUc_pid').getValue();
		params.DocumentUc_Num = wnd.form.findField('DocumentUc_Num').getValue();
		params.DocumentUc_setDate = wnd.form.findField('DocumentUc_setDate').getValue() ? wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('d.m.Y') : '';
		params.DocumentUc_didDate = wnd.form.findField('DocumentUc_didDate').getValue() ? wnd.form.findField('DocumentUc_didDate').getValue().dateFormat('d.m.Y') : '';
		params.DocumentUc_InvoiceNum = wnd.form.findField('DocumentUc_InvoiceNum').getValue();
		params.DocumentUc_InvoiceDate = wnd.form.findField('DocumentUc_InvoiceDate').getValue() ? wnd.form.findField('DocumentUc_InvoiceDate').getValue().dateFormat('d.m.Y') : '';
		params.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
		params.Contragent_sid = wnd.form.findField('Contragent_sid').getValue();
		params.Contragent_tid = wnd.form.findField('Contragent_tid').getValue();
		params.Storage_sid = wnd.form.findField('Storage_sid').getValue();
		params.Storage_tid = wnd.form.findField('Storage_tid').getValue();
		params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		params.DrugDocumentType_Code = wnd.DrugDocumentType_Code;
		params.DocumentUcStrJSON = wnd.StrGrid.getJSONChangedData();
		params.Note_id = wnd.inf_form.findField('Note_id').getValue();
		params.Note_Text = wnd.inf_form.findField('Note_Text').getValue();

		params.EmergencyTeam_id = wnd.form.findField('EmergencyTeam_id').getValue();

		if (this.isSmpMainStorage) {
			params.Contragent_id = params.Contragent_tid;
		} else if(this.isAptMu) {
			if (this.DrugDocumentType_Code.inlist([2,10,15,20,33])) {
				params.Contragent_id = params.Contragent_sid;
			} else if (this.DrugDocumentType_Code.inlist([3,6])) {
				params.Contragent_id = params.Contragent_tid;
			}
		}
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				/*if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					} else if (action.result.Error_Msg) {
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Msg);
					}
				}*/
				/*if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.DocumentUc_id);
				}*/
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				wnd.isChanged = false;
				if (action.result && action.result.DocumentUc_id > 0) {
					wnd.DocumentUc_id = action.result.DocumentUc_id;
					if (typeof options.callback == 'function') {
						options.callback({
                            callback: function() {
                                wnd.callback(wnd.owner, action.result.DocumentUc_id);
                            }
                        });
					}
				}
				if (typeof options.callback != 'function' && typeof wnd.callback == 'function') {
					wnd.callback(wnd.owner, action.result.DocumentUc_id);
				}
			}
		});
	},
	doExecute: function() {
		var wnd = this;

		if (confirm(langs('После исполнения, редактирование документа станет недоступно. Продолжить?'))) {
			this.doSave({
				callback: function(sc_options) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (success && response_obj.success && response_obj.DrugDocumentStatus_Code) {
									wnd.form.findField('DrugDocumentStatus_Code').setValue(response_obj.DrugDocumentStatus_Code);
									if(response_obj.DrugDocumentStatus_Code == 4){
										wnd.setDisabled(true);
										alert(langs('Документ успешно исполнен'));
									} else if(response_obj.DrugDocumentStatus_Code == 11){
										wnd.setDisabled(false);
                                        wnd.loadGrid();
										alert('Документ исполнен частично');
									} else {
										alert('Документ не исполнен');
									}
								} else {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
								}
							}
                            if (sc_options && sc_options.callback && typeof sc_options.callback == 'function') {
                                sc_options.callback();
                            }
						},
						params: {
							DocumentUc_id: wnd.DocumentUc_id
						},
						url: '/?c=DocumentUc&m=executeDocumentUc'
					});
				}
			});
		}
		return false;
	},
	doPrint: function(){
		var doc_id = this.form.findField('DocumentUc_id').getValue();
		if (doc_id > 0 && this.print_report_data != null) {
			if (Ext.isArray(this.print_report_data) && this.print_report_data.length > 0) {
				//подготовка массива ссылок
				for(var i = 0; i < this.print_report_data.length; i++) {
					if (!this.print_report_data[i].report_format || this.print_report_data[i].report_format.length < 1) {
						this.print_report_data[i].report_format = 'pdf';
					}
					this.print_report_data[i].report_params = '&paramDocumentUc='+doc_id;
				}

				if (this.print_report_data.length > 1) {
					getWnd('swReportSelectWindow').show({
						ReportData: this.print_report_data
					});
				} else {
					printBirt({
						'Report_FileName': this.print_report_data[0].report_file,
						'Report_Params': this.print_report_data[0].report_params,
						'Report_Format': this.print_report_data[0].report_format
					});
				}
			} else {
				printBirt({
					'Report_FileName': this.print_report_data,
					'Report_Params': '&paramDocumentUc='+doc_id,
					'Report_Format': 'pdf'
				});
			}
		}
	},
    openBarCodeViewWindow: function() {
        var wnd = this;
        var selected_record = this.StrGrid.getGrid().getSelectionModel().getSelected();
        var str_id = selected_record ? selected_record.get('DocumentUcStr_id') : null;

        if (str_id > 0) {
            getWnd('swDrugPackageBarCodeViewWindow').show({
                action: 'view',
                DocumentUcStr_id: str_id,
                BarCodeChangedData: selected_record.get('BarCodeChangedData')
            });
        }
    },
    createDocumentUcStrListByContract: function() {
		var wnd = this;
		var finance_id = wnd.form.findField('DrugFinance_id').getValue();
		var cost_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		var supply_id = 0;
		var supply_combo = wnd.form.findField('WhsDocumentUc_id');

		if (supply_combo.getValue() > 0) {
			var index = supply_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == supply_combo.getValue(); });
			if (index == -1) {
				return false;
			}
			var record = supply_combo.getStore().getAt(index);
			supply_id = record.get('WhsDocumentSupply_id');
		}

		if ( supply_id > 0) {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (success && response_obj.success) {
							//для приходных накладных генерируем названия партий
							if (wnd.DrugDocumentType_Code == 6) {
								var max_num = 0;

								wnd.StrGrid.getGrid().getStore().each(function(record) {
									var num = record.get('DrugShipment_Name') != '' ? parseFloat(record.get('DrugShipment_Name')) : 0;
									if (!isNaN(num) && num > max_num) {
										max_num = num;
									}
								});
								max_num = Math.ceil(max_num);

								Ext.Ajax.request({
									callback: function(opt, success, resp) {
										var resp_obj = Ext.util.JSON.decode(resp.responseText);
										if (resp_obj && resp_obj[0].DrugShipment_Name != '') {
											var start_num = resp_obj[0].DrugShipment_Name;
											if (start_num < max_num+1) {
												start_num = max_num+1;
											}
										}
										for (var i = response_obj.data.length-1; i >= 0; i--) {
											response_obj.data[i].DrugShipment_Name = start_num++;
										}
										wnd.StrGrid.addRecords(response_obj.data);
									},
									url: '/?c=DocumentUc&m=generateDrugShipmentName'
								});
							} else {
								wnd.StrGrid.addRecords(response_obj.data);
							}
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При создании списка медикаментов возникла ошибка');
						}
					}
				},
				params: {
					WhsDocumentSupply_id: supply_id
				},
				url: '/?c=DocumentUc&m=getDocumentUcStrListByWhsDocumentSupply'
			});
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Для выполнения действия необходимо указать контракт'));
		}
	},
    createDocumentUcStrListByDocument: function() {
		var wnd = this;
		var doc_combo = wnd.form.findField('WhsDocumentUc_id');
        var doc_id = doc_combo.getValue();
        var type_code = null;
        var url = '';

        doc_combo.getStore().each(function(record) {
            if (record.get('WhsDocumentUc_id') == doc_id) {
                type_code = record.get('WhsDocumentType_Code');
            }
        });

        switch(type_code) {
            case '25': //Заказ на производство
                url = '/?c=DocumentUc&m=getDocumentUcStrListByWhsDocumentUcOrder';
                break;
        }

        if (Ext.isEmpty(url)) {
            sw.swMsg.alert('Ошибка', 'Для документа выбранного в поле "'+doc_combo.fieldLabel+'" не доступна функция автоматического формирования списка медикаментов.');
        }

		if (doc_id > 0) {
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
					WhsDocumentUc_id: doc_id
				},
				url: url
			});
		} else {
			sw.swMsg.alert(langs('Ошибка'), 'Для выполнения действия необходимо указать значение в поле "'+doc_combo.fieldLabel+'"');
		}
	},
    createDocumentUcStrListByOstat: function() {
        var wnd = this;

        var params = {
            Org_id: this.form.findField('Contragent_sid').getFieldValue('Org_id'),
            Storage_id: this.form.findField('Storage_sid').getValue(),
            DrugFinance_id: this.form.findField('DrugFinance_id').getValue(),
            WhsDocumentCostItemType_id: this.form.findField('WhsDocumentCostItemType_id').getValue(),
            Sort_Type: 'defect_less6',
            only_doc_str_linked: 1 //только остатки связанные с документами учета
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
                params: {
                    DrugOstatRegistryJSON: selected_data_json
                },
                url: '/?c=DocumentUc&m=getDocumentUcStrListByDrugOstatRegistry'
            });
        };

        getWnd('swDrugOstatRegistrySelectWindow').show(params);
	},
	importDocumentUcStr: function() {
		var wnd = this;
        var region_nick = getRegionNick();

        var WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
        if (Ext.isEmpty(WhsDocumentUc_id)) {
            sw.swMsg.alert(langs('Ошибка'), langs('Должен быть указан контракт'));
            return false;
        }

        if ((region_nick == 'krym' || region_nick == 'ekb') && !this.form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('NewDocumentUcEditForm').getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var formParams = {};
        formParams.DocumentUc_setDate = wnd.form.findField('DocumentUc_setDate').getValue() ? wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('d.m.Y') : '';
        formParams.DocumentUc_didDate = wnd.form.findField('DocumentUc_didDate').getValue() ? wnd.form.findField('DocumentUc_didDate').getValue().dateFormat('d.m.Y') : '';
        formParams.DocumentUc_InvoiceNum = wnd.form.findField('DocumentUc_InvoiceNum').getValue();
        formParams.DocumentUc_InvoiceDate = wnd.form.findField('DocumentUc_InvoiceDate').getValue() ? wnd.form.findField('DocumentUc_InvoiceDate').getValue().dateFormat('d.m.Y') : '';
        formParams.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
        formParams.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
        formParams.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
        formParams.Contragent_sid = wnd.form.findField('Contragent_sid').getValue();
        formParams.Contragent_tid = wnd.form.findField('Contragent_tid').getValue();
        formParams.Storage_tid = wnd.form.findField('Storage_tid').getValue();
        formParams.Mol_tid = wnd.form.findField('Mol_tid').getValue();
        formParams.Org_id = wnd.form.findField('Org_id').getValue();
        formParams.Note_id = wnd.inf_form.findField('Note_id').getValue();
        formParams.Note_Text = wnd.inf_form.findField('Note_Text').getValue();
        getWnd('swDokNakImportWindow').show({
            formParams: formParams,
            callback: function(data) {
                if (data && !Ext.isEmpty(data.DocumentUc_id)) {
                    wnd.show_arguments.DocumentUc_id = data.DocumentUc_id;
                    wnd.show_arguments.action = 'edit';
                    if (typeof wnd.callback == 'function') {
                        wnd.callback(wnd.owner, data.DocumentUc_id);
                    }
                    wnd.show(wnd.show_arguments);
                }
            }
        });

		return true;
	},
	setMol: function(field, set_fields) {
		var wnd = this;
		var field_value = field.getValue() > 0 ? field.getValue() : 0;
		var field_name = field.hiddenName && field.hiddenName.length > 0 ? field.hiddenName : field.name;
		var str_idx = field_name.indexOf('_');
		var object_name = str_idx > 0 ? field_name.substring(0, str_idx) : null;
		var object_type = str_idx+2 < field_name.length ? field_name.substring(str_idx+1, str_idx+2) : null;
        var document_date = wnd.form.findField('DocumentUc_setDate').getValue() ? wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('d.m.Y') : '';

		if (object_name != 'Contragent' && object_name != 'Storage') {
			return false;
		}
		if (set_fields) {
			wnd.form.findField('Mol_'+object_type+'id').setValue(null);
			wnd.form.findField('Mol_'+object_type+'Person').setValue(null);
		}

		if (wnd.MolData[object_type][object_name].id == field_value && wnd.MolData[object_type][object_name].Date == document_date) {
			if (set_fields) {
				wnd.setMolFields(object_type);
			}
		} else if (field_value > 0) {
			var params = new Object();
			if (object_name == 'Contragent') {
				params.Contragent_id = field_value;
			}
			if (object_name == 'Storage') {
				params.Storage_id = field_value;
			}
            params.Date = document_date;
			params.callback = function(mol_data) {
				if (!mol_data.length) {
					return false
				}
				var mol_combo = wnd.form.findField('Mol_'+object_type+'id_combo');
				mol_combo.getStore().loadData(mol_data);
				mol_combo.setValue(mol_combo.getValue());
				mol_data = mol_data[0];
				wnd.MolData[object_type][object_name].id = field_value;
				wnd.MolData[object_type][object_name].Date = document_date;
				wnd.MolData[object_type][object_name].Mol_id = mol_data.Mol_id;
				wnd.MolData[object_type][object_name].Person_Fio = mol_data.Person_Fio;
				if (set_fields) {
					wnd.setMolFields(object_type);
				}
			};
			wnd.getMolData(params);
		}
	},
	initMolData: function(init_data) {
		var wnd = this;

		wnd.MolData = {
			s: {
				Storage: new Object(),
				Contragent: new Object()
			},
			t: {
				Storage: new Object(),
				Contragent: new Object()
			}
		};

		if (init_data) {
			var field_arr = ['Contragent_sid', 'Storage_sid', 'Contragent_tid', 'Storage_tid'];
			for (var i in field_arr) {
				if (init_data[field_arr[i]] && init_data[field_arr[i]] > 0) {
					wnd.setMol(wnd.form.findField(field_arr[i]), false);
				}
			}
		}
	},
	getMolData: function(data) {
		var wnd = this;
		if (data && ((data.Contragent_id && data.Contragent_id > 0) || (data.Storage_id && data.Storage_id > 0))) {
			var params = new Object();
			if (data.Contragent_id && data.Contragent_id > 0) {
				params.Contragent_id = data.Contragent_id;
			}
			if (data.Storage_id && data.Storage_id > 0) {
				params.Storage_id = data.Storage_id;
			}
			if (!Ext.isEmpty(data.Date)) {
				params.Date = data.Date;
			}
			params.isSmpMainStorage = (wnd.isSmpMainStorage)?1:0;
            params.Date = wnd.form.findField('DocumentUc_setDate').getValue() ? wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('d.m.Y') : '';
			Ext.Ajax.request({
				params: params,
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && data.callback) {
						data.callback(result);
					}
				},
				url:'/?c=DocumentUc&m=getMolByContragentOrStorage'
			});
		}
	},
	getMolByEmergencyTeam: function (EmergencyTeam_id, combo_name) {

		var field = this.form.findField( combo_name );
		if (!EmergencyTeam_id || !field) {
			return false;
		}

		Ext.Ajax.request({
				params: {
					EmergencyTeam_id:EmergencyTeam_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					field.getStore().loadData(result);
					field.setValue(field.getValue());
				},
				url:'/?c=Storage&m=getMolByEmergencyTeam'
			});

	},
	setMolFields: function(object_type) {
		var wnd = this;
		var mol_data = {
			Mol_id: null,
			Person_Fio: null
		}
		if (wnd.MolData[object_type]['Storage'].Mol_id > 0) {
			mol_data = wnd.MolData[object_type]['Storage'];
		} else if (wnd.MolData[object_type]['Contragent'].Mol_id > 0) {
			mol_data = wnd.MolData[object_type]['Contragent'];
		}
		wnd.form.findField('Mol_'+object_type+'id').setValue(mol_data.Mol_id);
		wnd.form.findField('Mol_'+object_type+'Person').setValue(mol_data.Person_Fio);
	},
	checkStorageByWhsDocumentUc: function (WhsDocumentUc_id) {

		var field = this.form.findField('Storage_tid');
		if (!WhsDocumentUc_id || !field || !this.DrugDocumentType_Code.inlist([3,6,12,15,33])) {
			return false;
		}

		Ext.Ajax.request({
			params: {
				WhsDocumentSupply_id:WhsDocumentUc_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if(result && result[0] && result[0].Storage_id){
					field.setValue(result[0].Storage_id);
				}
			},
			url:'/?c=StorageZone&m=findStorageDocSupplyLink'
		});

	},
	setDefaultValues: function () { //заполнение формы значениями "по умолчанию"
		var current_date = new Date();

		this.form.findField('DrugDocumentStatus_Code').setValue(1);
        if (this.DrugDocumentType_Code == 34) { //34 - Разукоплектация
            this.form.findField('DocumentUc_setDate').setValue(current_date);
        }
		this.form.findField('DocumentUc_didDate').setValue(current_date);
		this.form.findField('Org_id').setValue(getGlobalOptions().org_id);

		if (this.FormParams) {
			this.form.setValues(this.FormParams);
			if (this.FormParams.Str_Data) {
				this.StrGrid.addRecords(this.FormParams.Str_Data);
			}
			if (this.FormParams.DocumentUc_pid) {
				this.drugdocument_combo.setValueById(this.FormParams.DocumentUc_pid);
			}
		}

		if (this.num_generating_enabled) {
			this.generateDocumentUcNum();
		}

		var field_arr = ['WhsDocumentUc_id', 'Contragent_sid', 'Storage_sid', 'Contragent_tid', 'Storage_tid'];
		for (var i=0; i<field_arr.length; i++) {
			if (this.FormParams[field_arr[i]] && this.FormParams[field_arr[i]] > 0) {
				if(!(!this.isSmpMainStorage && this.isAptMu && this.DrugDocumentType_Code == 6 && field_arr[i] == 'Contragent_tid')){
					this.form.findField(field_arr[i]).setValueById(this.FormParams[field_arr[i]]);
				} else {
					var Contragent_tid = this.FormParams[field_arr[i]];
					var Contragent_tid_combo = this.form.findField(field_arr[i]);
					this.form.findField('Contragent_tid').getStore().load({
						callback:function(){
							if(Contragent_tid_combo.getStore().getById(Contragent_tid)){
								Contragent_tid_combo.setValue(Contragent_tid);
							} else {
								Contragent_tid_combo.setValue(null);
							}
						}
					});
				}
			} else {
                if (this.DrugDocumentType_Code == 34) { //34 - Разукоплектация
                    if (field_arr[i] == 'Storage_sid' && !Ext.isEmpty(this.userMedStaffFact.MedService_id)) {
                        this.setStorageFieldByMedServiceId(this.form.findField(field_arr[i]), this.userMedStaffFact.MedService_id)
                    } else {
                        this.form.findField(field_arr[i]).setValueById(null);
                    }
                } else {
                    this.form.findField(field_arr[i]).setValueById(null);
                }
			}
		}

		if (this.isSmpMainStorage) {


			//Устанавливаем в Contragent_#id службу центрального склада СМП

			if (this.DrugDocumentType_Code == 6) {
				this.setContragentFieldCurrentMedService('Contragent_tid');
			}

			if (this.DrugDocumentType_Code.inlist([15,33])) {
				this.setContragentFieldCurrentMedService('Contragent_sid');
			}

		}

		if (this.DrugDocumentType_Code == 20) {
			this.setContragentFieldCurrentMedService('Contragent_sid');
		}

	},
	setContragentFieldCurrentMedService: function(contragent_fieldname) {

		var win = this;
		Ext.Ajax.request({
			url: '/?c=Storage&m=getCurrentMedServiceContragentId',
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( typeof response_obj === 'undefined' || !response_obj.length ) {
					return false;
				}

				if ( typeof response_obj[0].Contragent_id === 'undefined' || response_obj[0].Error_Msg) {
					Ext.Msg.alert(langs('Сообщение'), response_obj[0].Error_Msg || langs('Ошибка получения склада текущей службы'));
					return false;
				}

				var field = win.form.findField( contragent_fieldname );

				if (field && (typeof field.setValue == 'function') ) {
					field.setValue( response_obj[0].Contragent_id );
				}

			}
		});

	},
	setStorageFieldByMedServiceId: function(field, medservice_id) {
		Ext.Ajax.request({
			url: '/?c=Storage&m=getStorageByMedServiceId',
            params: {
                MedService_id: medservice_id
            },
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(response_obj.Storage_id)) {
				    field.setValueById(response_obj.Storage_id);
				}
			}
		});

	},
	setDrugDocumentType: function(type_code) { //настройка внешнего вида формы в зависимости от типа документа
		var form = this;
		var num_fl = langs('Документ №');
		var doc_name = langs('Документ');
		var allowed_actions = new Array(); //список доступных пунктов в меню действия
		var current_date = new Date();
		var msf_store = sw.Promed.MedStaffFactByUser.store;
		var MedService_id = null;
        var orgtype = getGlobalOptions().orgtype;
        var region_nick = getRegionNick();
		var doc_status = this.form.findField('DrugDocumentStatus_id').getValue();

		if (!type_code) {
			type_code = this.DrugDocumentType_Code;
		} else {
			this.DrugDocumentType_Code = type_code;
		}

		//значения по умолчанию
		this.print_report_data = null;
		this.num_generating_enabled = true;
		this.sync_storagezone_by_field = null; //поле, со значением которого автоматически синхронизируется поле "место хранения" в строках документа учета

		this.form.enable_blocked = false;
		this.StrGrid.enable_blocked = false;

		this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Поставщик'));
		this.setFieldLabel(this.form.findField('Contragent_tid'), langs('Получатель'));
		this.setFieldLabel(this.form.findField('Storage_tid'), langs('Склад получателя'));
		this.setFieldLabel(this.form.findField('Mol_tPerson'), langs('МОЛ получателя'));
		this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('От'));
		this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата поставки'));
		this.setFieldLabel(this.form.findField('DocumentUc_pid'), langs('Родительский документ'));
		this.setFieldLabel(this.form.findField('DocumentUc_InvoiceNum'), langs('Счет-фактура №'));
		this.setFieldLabel(this.form.findField('DocumentUc_InvoiceDate'), langs('От'));
		this.setFieldLabel(this.form.findField('Lpu_id'), 'Заказчик');

		this.form.findField('DrugFinance_id').enable_blocked = false;
		this.form.findField('WhsDocumentCostItemType_id').enable_blocked = false;
		this.form.findField('Contragent_sid').enable_blocked = false;
		this.form.findField('Contragent_tid').enable_blocked = false;
		this.form.findField('Storage_tid').enable_blocked = false;
		this.form.findField('LpuBuilding_id').enable_blocked = true;
		this.form.findField('AccountType_id').enable_blocked = true;
		this.form.findField('DocumentUc_InvoiceNum').ownerCt.hide();
		this.form.findField('DocumentUc_InvoiceDate').ownerCt.hide();
        this.form.findField('DrugFinance_id').ownerCt.show();
        this.form.findField('WhsDocumentCostItemType_id').ownerCt.show();
		this.form.findField('Contragent_sid').ownerCt.show();
		this.form.findField('Contragent_tid').ownerCt.show();
		this.form.findField('Storage_sid').ownerCt.show();
		this.form.findField('Storage_tid').ownerCt.show();
		this.form.findField('StorageZone_sid').ownerCt.hide();
		this.form.findField('StorageZone_tid').ownerCt.hide();
		this.form.findField('Mol_sPerson').ownerCt.show();
		this.form.findField('Mol_tPerson').ownerCt.show();
		this.form.findField('Mol_tid_combo').ownerCt.hide();
		this.form.findField('Mol_tid_combo').allowBlank = true;
		this.form.findField('Mol_tid_combo').disable();
		this.form.findField('Mol_tid_combo').getStore().removeAll();
		this.form.findField('Mol_sid_combo').ownerCt.hide();
		this.form.findField('Mol_sid_combo').allowBlank = true;
		this.form.findField('Mol_sid_combo').disable();
		this.form.findField('Mol_sid_combo').getStore().removeAll();
		this.form.findField('Storage_tid').disable();
		this.form.findField('LpuBuilding_id').disable();
		this.form.findField('WhsDocumentUc_id').ownerCt.show();
		this.form.findField('WhsDocumentUc_FullName').ownerCt.hide();
		this.form.findField('EvnRecept_Name').ownerCt.hide();
		this.form.findField('DocumentUc_pid').ownerCt.hide();
		this.form.findField('AccountType_id').ownerCt.hide();
		this.form.findField('StorageZoneLiable_ObjectName').ownerCt.hide();
		this.form.findField('WhsDocumentUc_id').allowBlank = true;
        this.form.findField('DrugFinance_id').allowBlank = false;
        this.form.findField('WhsDocumentCostItemType_id').allowBlank = false;
		this.form.findField('Contragent_sid').allowBlank = false;
		this.form.findField('Contragent_tid').allowBlank = false;
		this.form.findField('Storage_sid').allowBlank = true;
		this.form.findField('Storage_tid').allowBlank = true;
		this.form.findField('StorageZone_sid').allowBlank = true;
		this.form.findField('StorageZone_tid').allowBlank = true;
		this.form.findField('AccountType_id').allowBlank = true;
		this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=DocumentUc&m=loadStorageList';
		this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=DocumentUc&m=loadStorageList';
        this.form.findField('WhsDocumentUc_id').setSearchUrl('/?c=Farmacy&m=loadWhsDocumentSupplyList');
        this.form.findField('WhsDocumentUc_id').setSearchWindow('swWhsDocumentSupplySelectWindow');
		this.form.findField('WhsDocumentUc_id').getStore().baseParams.Org_cid = null;
		this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentType_Code = null;
		this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentStatusType_Code = 2; //Действующий ГК
		this.form.findField('Storage_sid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_sid').getStore().baseParams.MedService_id = null;
		this.form.findField('Storage_sid').getStore().baseParams.MolMedPersonal_id = this.MolMedPersonal_id;
		this.form.findField('Storage_tid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_tid').getStore().baseParams.MedService_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.MolMedPersonal_id = null;
		this.form.findField('Contragent_tid').getStore().baseParams.mode = null;
		this.form.findField('Contragent_tid').getStore().baseParams.ExpDate = null;
		this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = null;
		this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = null;
		this.form.findField('Contragent_sid').getStore().baseParams.ExpDate = null;
		this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = null;
		this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = null;
        this.form.findField('Storage_sid').childrenList = this.form.findField('Storage_sid').defaultChildrenList;
        this.form.findField('Storage_tid').childrenList = this.form.findField('Storage_tid').defaultChildrenList;
        this.form.findField('Storage_sid').heritageList = this.form.findField('Storage_sid').defaultHeritageList;

		this.StrGrid.setColumnHeader('GoodsUnit_Name', langs('Ед. спис.'));
        this.StrGrid.setColumnHeader('DocumentUcStr_EdCount', langs('Кол-во (ед. спис.)'));
        this.StrGrid.setColumnHeader('DocumentUcStr_Reason', langs('Причина'));
        this.StrGrid.setColumnHeader('GoodsUnit_bName', langs('Ед. учета'));
        this.StrGrid.setColumnHeader('DocumentUcStr_Count', langs('Кол-во (ед.уч.)'));
        this.StrGrid.setColumnHeader('DocumentUcStr_Price', langs('Цена (ед.уч.)'));
        this.StrGrid.setColumnHeader('DocumentUcStr_Sum', langs('Сумма'));

        //this.StrGrid.setColumnHidden('Okei_NationSymbol', false);
        this.StrGrid.setColumnHidden('DocumentUcStr_Count', false);
        this.StrGrid.setColumnHidden('DocumentUcStr_Price', false);
        this.StrGrid.setColumnHidden('DocumentUcStr_RegPrice', true);
        this.StrGrid.setColumnHidden('PostGoodsUnit_bName', true);
        this.StrGrid.setColumnHidden('PostDocumentUcStr_Count', true);
        this.StrGrid.setColumnHidden('PostDocumentUcStr_Price', true);
        this.StrGrid.setColumnHidden('PostDocumentUcStr_Sum', true);
		this.StrGrid.setColumnHidden('Person_Fio', true);
		this.StrGrid.setColumnHidden('DocumentUcStr_oName', false);
		this.StrGrid.setColumnHidden('DrugShipment_Name', true);
		this.StrGrid.setColumnHidden('DocumentUcStr_Reason', true);
		this.StrGrid.setColumnHidden('DocumentUcStr_IsNDS', false);
		this.StrGrid.setColumnHidden('DrugNds_Code', false);
		this.StrGrid.setColumnHidden('DocumentUcStr_SumNds', false);
		this.StrGrid.setColumnHidden('DocumentUcStr_NdsSum', false);
		this.StrGrid.setColumnHidden('BarCodeList', false);

		this.StrGrid.getAction('action_import').hide();
		this.StrGrid.setActionDisabled('action_add', false);

		this.form.findField('EmergencyTeam_id').ownerCt.hide();
		this.form.findField('EmergencyTeam_id').allowBlank = true;

        if (orgtype != 'lpu') {
			//this.form.findField('WhsDocumentUc_id').allowBlank = false;
            this.form.findField('Lpu_id').showContainer();
            this.form.findField('LpuBuilding_id').showContainer();
        } else {
            this.form.findField('Lpu_id').hideContainer();
            this.form.findField('LpuBuilding_id').hideContainer();
        }

        //установка видимости полей списания в альтернативных ед учета
        if (this.show_diff_gu) {
            this.StrGrid.setColumnHidden('GoodsUnit_Name', false);
            this.StrGrid.setColumnHidden('DocumentUcStr_EdCount', false);
        } else {
            this.StrGrid.setColumnHidden('GoodsUnit_Name', true);
            this.StrGrid.setColumnHidden('DocumentUcStr_EdCount', true);
        }

        this.BarCodeInputEnabled = false;
        this.BarCodeInputPanel.hide();

		this.inf_form.findField('TotalSumNds').hideContainer();
        this.inf_form.findField('TotalNdsSum').ownerCt.ownerCt.show();
        this.inf_form.findField('TotalBeforeNdsSum').ownerCt.ownerCt.hide();
		this.inf_form_panel.show();

		this.executeButton.show();
		this.buttonSave.show();

		//проверка перед сохранением по умолчанию
		this.checkDataBeforeSave = function() {
			return true;
		};

		switch(Number(type_code)) {
			case 2: //Документ списания
            case 25: //Списание медикаментов со склада на пациента. СМП
				doc_name = langs('Документ списания');
				allowed_actions = ['clear_storage_work'];
                if (type_code == 2) {
                    allowed_actions.push('create_by_store_ostat');
                }
                this.sync_storagezone_by_field = 'StorageZone_sid';
				this.print_report_data = [{
					report_label: langs('ТОРГ-16'),
					report_file: 'Torg16.rptdesign',
					report_format: 'pdf'
				}];


				if (type_code == 2) {
					this.print_report_data.push({
						report_label: langs('Сборочный лист'),
						report_file: 'DocumentUcPick.rptdesign',
						report_format: 'doc'
					});
				}

				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.StrGrid.setColumnHeader('DocumentUcStr_Reason', langs('Причина списания'));
				this.StrGrid.setColumnHidden('DocumentUcStr_Reason', false);

				if (this.isAptMu) {
					this.form.findField('Contragent_sid').enable_blocked = false;
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптека МУ
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
				break;
			case 3: //Документ ввода остатков
				doc_name = langs('Документ ввода остатков');
				allowed_actions = ['clear_storage_work'];
                this.sync_storagezone_by_field = 'StorageZone_tid';
				this.print_report_data = [{
					report_label: langs('Постеллажные карточки'),
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Ведомость приема товаров'),
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Лист размещения'),
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_tid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_tid'), langs('Склад'));
				this.form.findField('Contragent_tid').enable_blocked = true;
				this.form.findField('AccountType_id').enable_blocked = false;
				this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentType_Code = 18; //Контракт ввода остатков
				this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentStatusType_Code = null;
				this.form.findField('Contragent_sid').ownerCt.hide();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('AccountType_id').ownerCt.show();
				this.form.findField('WhsDocumentUc_id').allowBlank = false;
				this.form.findField('Contragent_sid').allowBlank = true;
				this.form.findField('Storage_tid').allowBlank = false;
				this.form.findField('AccountType_id').allowBlank = false;
				this.StrGrid.setColumnHidden('DocumentUcStr_oName', true);
				this.StrGrid.setColumnHidden('DrugShipment_Name', false);

                this.FormParams.AccountType_id = 1; //1 - балансовый учет

				if (this.isAptMu) {
					this.form.findField('WhsDocumentUc_id').getStore().baseParams.Org_cid = this.userMedStaffFact.Org_id; //Заказчик - МО
					this.form.findField('Contragent_tid').enable_blocked = false;
					this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.MolMedPersonal_id = this.MolMedPersonal_id;
					this.form.findField('Storage_tid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}

				break;
			case 6: //Приходная накладная
				doc_name = langs('Приходная накладная');
				num_fl = langs('Накладная №');
				allowed_actions = ['create_by_contract', 'copy_row', 'clear_storage_work'];
                this.sync_storagezone_by_field = 'StorageZone_tid';
				this.print_report_data = [{
					report_label: langs('ТОРГ-1'),
					report_file: 'TORG1.rptdesign'
				}, {
					report_label: langs('Акт о приеме медикаментов'),
					report_file: 'Torg1_Med.rptdesign'
				}, {
					report_label: langs('Доверенность'),
					report_file: 'Doveren.rptdesign',
					report_format: 'xls'
				}, {
					report_label: langs('Постеллажные карточки'),
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Ведомость приема товаров'),
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Лист размещения'),
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.num_generating_enabled = false;
				this.setFieldLabel(this.form.findField('Storage_tid'), langs('Склад'));
				this.setFieldLabel(this.form.findField('Mol_tPerson'), langs('МОЛ'));
                this.form.findField('WhsDocumentUc_id').allowBlank = !(getRegionNick().inlist(['kareliya']));
				this.form.findField('Contragent_tid').enable_blocked = true;
				this.form.findField('AccountType_id').enable_blocked = false;
				this.form.findField('Contragent_tid').allowBlank = false;
				this.form.findField('Storage_tid').allowBlank = false;
				this.form.findField('AccountType_id').allowBlank = false;

				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('AccountType_id').ownerCt.show();
				this.form.findField('Storage_sid').ownerCt.hide();
                this.form.findField('StorageZone_tid').ownerCt.show();
				this.form.findField('Mol_sPerson').ownerCt.hide();
                this.StrGrid.setColumnHidden('DocumentUcStr_RegPrice', false);
				this.StrGrid.setColumnHidden('DocumentUcStr_oName', true);
				this.StrGrid.setColumnHidden('DrugShipment_Name', false);

                this.FormParams.AccountType_id = 1; //1 - балансовый учет

				var allow_imp_and_act = true;
				if(Ext.isEmpty(doc_status) || doc_status == 1){
					allow_imp_and_act = false;
				}
				this.StrGrid.getAction('action_import').setHidden(
					this.action == 'view'
					|| (getRegionNick() != 'khak' && getRegionNick() != 'krym' && getRegionNick() != 'ekb')
					|| allow_imp_and_act
				);
				if(allow_imp_and_act){
					allowed_actions = [];
				}

                this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
				if (this.isSmpMainStorage) {
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
					this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpMainStorageList';
					this.form.findField('Mol_tPerson').ownerCt.hide();
					this.form.findField('Mol_tid_combo').allowBlank = true;
					this.form.findField('Mol_tid_combo').ownerCt.show();
					this.form.findField('Mol_tid_combo').enable();
				} else if (this.isAptMu) {
					this.form.findField('WhsDocumentUc_id').getStore().baseParams.Org_cid = this.userMedStaffFact.Org_id; //Заказчик - МО
					this.form.findField('Contragent_tid').enable_blocked = false;
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.MolMedPersonal_id = this.MolMedPersonal_id;
					this.form.findField('Storage_tid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}
				break;
			case 10: //Расходная накладная
				doc_name = langs('Расходная накладная');
				num_fl = langs('Накладная №');
				allowed_actions = ['copy_row', 'clear_storage_work', 'create_by_store_ostat'];
                this.sync_storagezone_by_field = 'StorageZone_sid';
				this.print_report_data = [{
					report_label: langs('ТОРГ-12'),
					report_file: 'TORG12.rptdesign'
				}, {
					report_label: langs('Реестр сертификатов'),
					report_file: 'dlo_DocumentUcStrReestrt_List.rptdesign'
				}, {
					report_label: langs('Упаковочный лист'),
					report_file: 'DocumentUcPack.rptdesign'
				}, {
					report_label: langs('Акт приема-передачи'),
					report_file: 'AktPriemaPeredacha.rptdesign'
				}, {
					report_label: langs('Сборочный лист'),
					report_file: 'DocumentUcPick.rptdesign',
					report_format: 'doc'
				}];

                this.form.findField('StorageZone_sid').ownerCt.show();
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('Storage_tid').enable_blocked = true;
				this.form.findField('Storage_sid').allowBlank = false;

				this.form.findField('Contragent_tid').getStore().baseParams.ExpDate = current_date.format('d.m.Y');
                if (region_nick == 'krym') {
                    this.form.findField('Contragent_tid').getStore().baseParams.mode = 'krym_t';
                } else {
                    this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '1,3,5,6'; //1 - Организация; 3 - Аптека; 5 - Аптека МУ; 6 - Региональный склад;
                }

				if (this.isAptMu) {
					this.form.findField('WhsDocumentUc_id').getStore().baseParams.Org_cid = this.userMedStaffFact.Org_id; //Заказчик - МО
					this.form.findField('Contragent_sid').enable_blocked = false;
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
				break;
			case 11: //Документ реализации
				doc_name = langs('Документ реализации');
				allowed_actions = ['clear_storage_work'];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
				this.form.findField('EvnRecept_Name').ownerCt.show();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}
				break;
			case 12: //Документ оприходования
            case 22: //Учет готовой продукции
				doc_name = (type_code == 12) ? langs('Документ оприходования') : 'Документ учета готовой продукции';
				allowed_actions = ['clear_storage_work'];
                this.sync_storagezone_by_field = 'StorageZone_sid';
				this.print_report_data = [{
					report_label: langs('Доверенность'),
					report_file: 'Doveren.rptdesign',
					report_format: 'xls'
				}, {
					report_label: langs('Постеллажные карточки'),
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('WhsDocumentUc_id'), langs('Основание'));
				this.setFieldLabel(this.form.findField('Contragent_tid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_tid'), langs('Склад'));
                this.form.findField('Contragent_tid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.show();
				this.form.findField('Contragent_sid').ownerCt.hide();
				this.form.findField('Storage_sid').ownerCt.hide();
                this.form.findField('StorageZone_tid').ownerCt.show();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
                this.form.findField('Mol_tid_combo').allowBlank = false;
                this.form.findField('Storage_tid').allowBlank = false;
                this.form.findField('Mol_tid_combo').ownerCt.show();
                this.form.findField('Mol_tid_combo').enable();
				this.form.findField('Contragent_sid').allowBlank = true;

                this.form.findField('AccountType_id').enable_blocked = false;
                this.FormParams.AccountType_id = 1; //1 - балансовый учет

                this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
                if (type_code == 12) {
                    this.form.findField('AccountType_id').ownerCt.show();
                    this.form.findField('AccountType_id').allowBlank = false;

					if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
						this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
					}

					this.print_report_data.push({
						report_label: langs('Ведомость приема товаров'),
						report_file: 'AcceptSheet.rptdesign',
						report_format: 'doc'
					});
					this.print_report_data.push({
						report_label: langs('Лист размещения'),
						report_file: 'WarehousingSheet.rptdesign',
						report_format: 'doc'
					});
                }
				break;
			case 15: //Накладная на внутреннее перемещение
			case 32: //Приход в отделение
			case 33: //Возврат из отделения
                if (type_code == 15) {
                    doc_name = langs('Накладная на внутреннее перемещение');
                }
                if (type_code == 32) {
                    doc_name = 'Приход в отделение';
                }
                if (type_code == 33) {
                    doc_name = 'Возврат из отделения';
                    this.only_execution = true;
                }
                this.sync_storagezone_by_field = 'StorageZone_sid';
				this.print_report_data = [{
					report_label: langs('ТОРГ-13'),
					report_file: 'Torg13.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Ведомость приема товаров'),
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Сборочный лист'),
					report_file: 'DocumentUcPick.rptdesign',
					report_format: 'doc'
				}, {
					report_label: langs('Лист размещения'),
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
                allowed_actions = ['create_by_store_ostat', 'clear_storage_work'];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
                this.setFieldLabel(this.form.findField('WhsDocumentUc_FullName'), langs('Основание'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Передать со склада'));
				this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));
				this.setFieldLabel(this.form.findField('Storage_tid'), langs('На склад'));
				this.setFieldLabel(this.form.findField('Mol_tPerson'), langs('МОЛ'));
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), langs('Основание'));

                if (orgtype == 'lpu') {
                    this.form.findField('Storage_sid').childrenList = ['StorageZone_sid', 'Storage_tid'];
                    this.form.findField('Storage_sid').heritageList = ['Storage_sid'];
                }

				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('WhsDocumentUc_FullName').ownerCt.show();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('DocumentUc_pid').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
                this.form.findField('StorageZone_tid').ownerCt.show();
				if (type_code.inlist([15,33])) { // Исключаем приход в отделение и возврат из отделения
					this.form.findField('DrugFinance_id').allowBlank = (getGlobalOptions().lpu_id > 0);
					this.form.findField('WhsDocumentCostItemType_id').allowBlank = (getGlobalOptions().lpu_id > 0);
				}
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.form.findField('Storage_tid').allowBlank = false;
				this.checkDataBeforeSave = function() {
					if (this.form.findField('Storage_sid').getValue() == this.form.findField('Storage_tid').getValue()) {
						sw.swMsg.alert(langs('Ошибка'), langs('Склад-поставщик должен быть не равен складу-получателю.'));
						return false;
					}
					return true;
				};

                //для документа "Приход в отделение" отображаем другую колонку "Партия"
                if (type_code == 32) {
                    this.StrGrid.setColumnHidden('DocumentUcStr_oName', true);
                    this.StrGrid.setColumnHidden('DrugShipment_Name', false);
                }

                this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
				if (this.isSmpMainStorage) {
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
					this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpMainStorageList';
					this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';

					this.form.findField('Mol_tPerson').ownerCt.hide();
					this.form.findField('Mol_tid_combo').allowBlank = true;
					this.form.findField('Mol_tid_combo').ownerCt.show();
					this.form.findField('Mol_tid_combo').enable();

					this.form.findField('Mol_sPerson').ownerCt.hide();
					this.form.findField('Mol_sid_combo').allowBlank = true;
					this.form.findField('Mol_sid_combo').ownerCt.show();
					this.form.findField('Mol_sid_combo').enable();
				} else if (this.isAptMu) {
					this.form.findField('Contragent_sid').enable_blocked = false;

					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;

					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
				} else {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();

					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
				}

                if (type_code != 32) {
                    this.BarCodeInputEnabled = true;
                    this.BarCodeInputPanel.show();
                }
				break;
			case 17: //Возвратная накладная (расходная)
				doc_name = langs('Возвратная накладная (расходная)');
				allowed_actions = ['clear_storage_work'];
                this.sync_storagezone_by_field = 'StorageZone_sid';
				this.print_report_data = [{
					report_label: langs('Возвратная накладная'),
					report_file: 'TorgNaklVozvr.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Передать со склада'));
				this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), langs('К приходной накладной №'));
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('DrugFinance_id').enable_blocked = true;
				this.form.findField('WhsDocumentCostItemType_id').enable_blocked = true;
				this.form.findField('DocumentUc_pid').enable_blocked = true;
				this.form.findField('DocumentUc_pid').ownerCt.show();
                this.form.findField('StorageZone_sid').ownerCt.show();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('Storage_sid').setAllowBlank(false);

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}
				if (this.isAptMu) {
					this.form.findField('WhsDocumentUc_id').getStore().baseParams.Org_cid = this.userMedStaffFact.Org_id; //Заказчик - МО
				}

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
				break;
			case 18: //Возвратная накладная (приходная)
				doc_name = langs('Возвратная накладная (приходная)');
				allowed_actions = ['clear_storage_work'];
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Storage_tid'), langs('Передать на склад'));
				this.setFieldLabel(this.form.findField('Mol_tPerson'), langs('МОЛ'));
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), langs('К приходной накладной №'));
				this.form.enable_blocked = true;
				this.StrGrid.enable_blocked = true;
				this.form.findField('DocumentUc_pid').ownerCt.show();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
                this.form.findField('StorageZone_tid').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('Storage_tid').setAllowBlank(false);

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
				}

				break;
			case 20:
				doc_name = langs('Пополнение укладки со склада подстанции');
				allowed_actions = ['clear_storage_work'];

                this.sync_storagezone_by_field = 'StorageZone_sid';

				this.form.findField('EmergencyTeam_id').ownerCt.show();
				this.form.findField('EmergencyTeam_id').allowBlank = false;

				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();

				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;

				this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
				this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
				this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';

				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tid_combo').allowBlank = false;
				this.form.findField('Mol_tid_combo').ownerCt.show();
				this.form.findField('Mol_tid_combo').enable();

				//this.executeButton.hide();
                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
				break;
			case 21: // Списание медикаментов со склада на пациента
				doc_name = langs('Списание медикаментов со склада на пациента');
				allowed_actions = ['clear_storage_work'];

                this.sync_storagezone_by_field = 'StorageZone_sid';

				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
				this.form.findField('EvnRecept_Name').ownerCt.hide();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;

				this.StrGrid.setActionDisabled('action_add', true);

				if ( this.isAptMu ) {
					this.StrGrid.setColumnHidden('Person_Fio', false);
				} else {
					// Загружаем только склад подстанции
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
					this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';
				}

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();

				this.buttonSave.hide();
			break;
            case 23: //Списание в производство
                doc_name = langs('Документ списания в производство');
                allowed_actions = ['create_by_document',  'create_by_store_ostat', 'clear_storage_work'];

                this.sync_storagezone_by_field = 'StorageZone_sid';
                //this.print_report_data = 'Torg16.rptdesign';

                //this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
                //this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
                this.setFieldLabel(this.form.findField('WhsDocumentUc_id'), langs('Основание'));
                this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
                this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
                //this.setFieldLabel(this.form.findField('Mol_sid'), langs('МОЛ'));
                this.form.findField('Mol_sid_combo').enable();
                this.form.findField('Contragent_sid').enable_blocked = true;
                this.form.findField('WhsDocumentUc_id').ownerCt.show();
                this.form.findField('StorageZone_sid').ownerCt.show();
                this.form.findField('Contragent_tid').ownerCt.hide();
                this.form.findField('Storage_tid').ownerCt.hide();
                this.form.findField('Mol_sPerson').ownerCt.hide();
                this.form.findField('Mol_sid_combo').ownerCt.show();
                this.form.findField('Mol_tPerson').ownerCt.hide();
                this.form.findField('DrugFinance_id').allowBlank = (getGlobalOptions().lpu_id <= 0);
                this.form.findField('WhsDocumentCostItemType_id').allowBlank = (getGlobalOptions().lpu_id <= 0);
                this.form.findField('Contragent_tid').allowBlank = true;
                this.form.findField('Storage_sid').allowBlank = false;
                this.form.findField('Mol_sid_combo').allowBlank = true;

                this.form.findField('WhsDocumentUc_id').setSearchUrl('/?c=DocumentUc&m=loadWhsDocumentUcOrderList');
                this.form.findField('WhsDocumentUc_id').setSearchWindow('swWhsDocumentUcSelectWindow');
                this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentType_Code = null;
                this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentStatusType_Code = null;

                if (this.isAptMu) {
                    this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
                    this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
                } else {
                    this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
                }

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
                break;
            case 26: //Списание медикаментов из укладки на пациента
                this.form.findField('StorageZone_sid').ownerCt.show();
                break;
            case 27: //Передача на подотчет
            case 28: //Возврат с подотчета
                if (orgtype == 'lpu') {
                    this.form.findField('Storage_sid').childrenList = ['StorageZone_sid', 'Storage_tid'];
                    this.form.findField('Storage_sid').heritageList = ['Storage_sid'];
                }

                this.form.findField('StorageZone_sid').ownerCt.show();
                this.form.findField('StorageZone_tid').ownerCt.show();

                this.checkDataBeforeSave = function() {
                    if (this.form.findField('StorageZone_sid').getValue() == this.form.findField('StorageZone_tid').getValue()) {
                        sw.swMsg.alert(langs('Ошибка'), 'Места хранения поставщика и получателя не должны совпадать');
                        return false;
                    }
                    return true;
                }
                break;
            case 29: //Передача укладки
                doc_name = 'Документ передачи укладки';
                this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
				this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.form.findField('StorageZoneLiable_ObjectName').ownerCt.show();
				this.action = 'view';

                break;
            case 30: //Возврат укладки
                doc_name = 'Документ возврата укладки';
                this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
				this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
				this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
				this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.form.findField('StorageZoneLiable_ObjectName').ownerCt.show();
				this.action = 'view';

                break;
            case 31: //Накладная на перемещение внутри склада
                doc_name = 'Накладная на перемещение внутри склада';
                allowed_actions = ['create_by_store_ostat'];

                this.sync_storagezone_by_field = 'StorageZone_sid';
                this.print_report_data = [{
                    report_label: langs('ТОРГ-13'),
                    report_file: 'Torg13.rptdesign',
                    report_format: 'doc'
                }, {
                    report_label: langs('Лист размещения'),
                    report_file: 'WarehousingSheet.rptdesign',
                    report_format: 'doc'
                }, {
                    report_label: langs('Сборочный лист'),
                    report_file: 'DocumentUcPick.rptdesign',
                    report_format: 'doc'
                }];

                //блок скопирован из настроек накладной на внутреннее перемещение
                this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
                this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
                this.setFieldLabel(this.form.findField('WhsDocumentUc_FullName'), langs('Основание'));
                this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
                this.setFieldLabel(this.form.findField('Storage_sid'), langs('Передать со склада'));
                this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));
                this.setFieldLabel(this.form.findField('Storage_tid'), langs('На склад'));
                this.setFieldLabel(this.form.findField('Mol_tPerson'), langs('МОЛ'));
                this.setFieldLabel(this.form.findField('DocumentUc_pid'), langs('Основание'));

                if (orgtype == 'lpu') {
                    this.form.findField('Storage_sid').childrenList = ['StorageZone_sid', 'Storage_tid'];
                    this.form.findField('Storage_sid').heritageList = ['Storage_sid'];
                }

                this.form.findField('Contragent_sid').enable_blocked = true;
                this.form.findField('WhsDocumentUc_id').ownerCt.hide();
                this.form.findField('WhsDocumentUc_FullName').ownerCt.show();
                this.form.findField('Contragent_tid').ownerCt.hide();
                this.form.findField('DocumentUc_pid').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
                this.form.findField('StorageZone_tid').ownerCt.show();
                this.form.findField('DrugFinance_id').allowBlank = (getGlobalOptions().lpu_id > 0);
                this.form.findField('WhsDocumentCostItemType_id').allowBlank = (getGlobalOptions().lpu_id > 0);
                this.form.findField('Contragent_tid').allowBlank = true;
                this.form.findField('Storage_sid').allowBlank = false;
                this.form.findField('Storage_tid').allowBlank = false;
                this.form.findField('StorageZone_sid').allowBlank = false;
                this.form.findField('StorageZone_tid').allowBlank = false;

                this.checkDataBeforeSave = function() {
                    if (this.form.findField('StorageZone_sid').getValue() == this.form.findField('StorageZone_tid').getValue()) {
                        sw.swMsg.alert(langs('Ошибка'), 'Места хранения поставщика и получателя не должны совпадать');
                        return false;
                    }
                    return true;
                };

                this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ

                //блок скопирован из настроек накладной на внутреннее перемещение
                if (this.isSmpMainStorage) {
                    this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
                    this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpMainStorageList';
                    this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';

                    this.form.findField('Mol_tPerson').ownerCt.hide();
                    this.form.findField('Mol_tid_combo').allowBlank = true;
                    this.form.findField('Mol_tid_combo').ownerCt.show();
                    this.form.findField('Mol_tid_combo').enable();

                    this.form.findField('Mol_sPerson').ownerCt.hide();
                    this.form.findField('Mol_sid_combo').allowBlank = true;
                    this.form.findField('Mol_sid_combo').ownerCt.show();
                    this.form.findField('Mol_sid_combo').enable();
                } else if (this.isAptMu) {
                    this.form.findField('Contragent_sid').enable_blocked = false;

                    this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
                    this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
                    this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;

                    this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
                } else {
                    this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
                    this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();

                    this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
                }

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();

                break;
            case 34: //Разукомплектация: списание
                doc_name = 'Разукомплектация';
                allowed_actions = ['create_by_store_ostat'];

                this.setFieldLabel(this.form.findField('DocumentUc_setDate'), langs('Дата'));
                this.setFieldLabel(this.form.findField('DocumentUc_didDate'), langs('Дата исполнения'));
                this.setFieldLabel(this.form.findField('Contragent_sid'), langs('Организация'));
                this.setFieldLabel(this.form.findField('Storage_sid'), langs('Склад'));
                this.setFieldLabel(this.form.findField('Mol_sPerson'), langs('МОЛ'));

                this.StrGrid.setColumnHeader('GoodsUnit_bName', langs('Списать<br/>Ед. учета'));
                this.StrGrid.setColumnHeader('DocumentUcStr_Count', langs('Списать<br/>Кол-во (ед.уч.)'));
                this.StrGrid.setColumnHeader('DocumentUcStr_Price', langs('Списать<br/>Цена (ед.уч.)'));
                this.StrGrid.setColumnHeader('DocumentUcStr_Sum', langs('Списать<br/>Сумма'));

                this.StrGrid.setColumnHidden('PostGoodsUnit_bName', false);
                this.StrGrid.setColumnHidden('PostDocumentUcStr_Count', false);
                this.StrGrid.setColumnHidden('PostDocumentUcStr_Price', false);
                this.StrGrid.setColumnHidden('PostDocumentUcStr_Sum', false);
                this.StrGrid.setColumnHidden('GoodsUnit_Name', true);
                this.StrGrid.setColumnHidden('DocumentUcStr_EdCount', true);
                this.StrGrid.setColumnHidden('DocumentUcStr_IsNDS', true);
                this.StrGrid.setColumnHidden('DrugNds_Code', true);
                this.StrGrid.setColumnHidden('DocumentUcStr_SumNds', true);
                this.StrGrid.setColumnHidden('DocumentUcStr_NdsSum', true);

                //this.form.findField('Contragent_sid').enable_blocked = true;
                this.form.findField('WhsDocumentUc_id').ownerCt.hide();
                this.form.findField('DrugFinance_id').ownerCt.hide();
                this.form.findField('WhsDocumentCostItemType_id').ownerCt.hide();
                this.form.findField('Contragent_tid').ownerCt.hide();
                this.form.findField('Storage_tid').ownerCt.hide();
                this.form.findField('Mol_tPerson').ownerCt.hide();
                this.form.findField('DocumentUc_pid').ownerCt.hide();
                this.form.findField('StorageZone_sid').ownerCt.show();
                this.form.findField('StorageZone_tid').ownerCt.show();
                this.form.findField('Storage_sid').allowBlank = false;
                this.form.findField('Contragent_tid').allowBlank = true;
                this.form.findField('DrugFinance_id').allowBlank = true;
                this.form.findField('WhsDocumentCostItemType_id').allowBlank = true;

                this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
                this.form.findField('Storage_sid').childrenList = ["StorageZone_sid", "StorageZone_tid"];

                this.inf_form.findField('TotalNdsSum').ownerCt.ownerCt.hide();
                this.inf_form.findField('TotalBeforeNdsSum').ownerCt.ownerCt.show();

                this.BarCodeInputEnabled = true;
                this.BarCodeInputPanel.show();
                break;
		}

		this.doLayout();

		if (this.isAptMu) {
			this.StrGrid.setColumnHidden('DocumentUcStr_IsNDS', true);
			this.StrGrid.setColumnHidden('DrugNds_Code', true);
			this.StrGrid.setColumnHidden('DocumentUcStr_SumNds', true);
			this.StrGrid.setColumnHidden('DocumentUcStr_NdsSum', true);
		}

		if (orgtype == 'lpu' && this.userMedStaffFact.MedServiceType_SysNick == 'merch') {
            this.form.findField('Storage_sid').getStore().baseParams.StructFilterPreset = 'DocumentUcEdit_Storage_sid_Lpu';
            this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
		}

		if (orgtype == 'lpu') {
			if (!this.form.findField('Storage_sid').allowBlank) { //считаем, что если поле склад "Поставщика" обязателен
                this.form.findField('Storage_tid').getStore().baseParams.StructFilterPreset = 'DocumentUcEdit_Storage_tid_Lpu_UserOrg';
			} else {
                this.form.findField('Storage_tid').getStore().baseParams.StructFilterPreset = 'DocumentUcEdit_Storage_tid_Lpu';
			}
		}

        //региональные настройки
        if (region_nick == "kz") {
            this.StrGrid.setColumnHidden('BarCodeList', true);
        }

		//натройка меню действий
		if (allowed_actions.length > 0) {
			var actions = this.StrGrid.getAction('action_ndue_actions').items[0].menu.items;
			var menu_actions = this.StrGrid.ViewContextMenu.items.get(10).menu.items;
			actions.each(function(a) {
				a.setVisible(a.name.inlist(allowed_actions));
			});
			menu_actions.each(function(a) {
				a.setVisible(a.name.inlist(allowed_actions));
			});
			this.StrGrid.getAction('action_ndue_actions').show();
		} else {
			this.StrGrid.getAction('action_ndue_actions').hide();
		}

		this.setFieldLabel(this.form.findField('DocumentUc_Num'), num_fl);

		if (this.action == "add") {
			this.setTitle(doc_name + ": Добавление");
		} else {
			this.setTitle(doc_name + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
		}

		//установка видимости кнопки печати
		if (this.print_report_data != null && this.action != "add") {
			this.buttons[2].show();
		} else {
			this.buttons[2].hide();
		}
	},
	setFieldLabel: function(field, label) {
		var el = field.el.dom.parentNode.parentNode;
		if(el.children[0].tagName.toLowerCase() === 'label') {
			el.children[0].innerHTML = label+':';
		} else if (el.parentNode.children[0].tagName.toLowerCase() === 'label') {
			el.parentNode.children[0].innerHTML = label+':';
		}
        if (field.fieldLabel) {
            field.fieldLabel = label;
        }
	},
	setDisabled: function(disable_param) {
		var wnd = this;
        var disable = disable_param;
        var doc_status = wnd.form.findField('DrugDocumentStatus_Code').getValue();

        //в режиме "только исполнение" или при статусе отличном от "новый" считаем что вся форма заблокирована
        //для режима "только исполнение" или при статусе "частично исполнен" необходимые элементы будут разблокированы в конце функции
        if (wnd.only_execution || doc_status != 1) {
            disable = true;
        }

        wnd.is_disabled = disable;

		var field_arr = [
			'DocumentUc_pid',
			'DocumentUc_Num',
			'DocumentUc_setDate',
			'DocumentUc_didDate',
			'DocumentUc_InvoiceNum',
			'DocumentUc_InvoiceDate',
			'WhsDocumentUc_id',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'Contragent_sid',
			'Storage_sid',
			'Mol_sid',
            'StorageZone_sid',
			'Contragent_tid',
			'Storage_tid',
			'Mol_tid',
            'StorageZone_tid',
			'Lpu_id',
			'LpuBuilding_id',
			'AccountType_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var combo = wnd.form.findField(field_arr[i]);
			if (disable || combo.enable_blocked || wnd.form.enable_blocked) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		if (disable) {
            wnd.buttons[0].disable();
            wnd.buttons[1].disable();
			wnd.inf_form.findField('Note_Text').disable();
		} else {
            wnd.buttons[0].enable();
            wnd.buttons[1].enable();
            wnd.inf_form.findField('Note_Text').enable();
		}

		if (wnd.WhsDocumentUcInvent_id > 0) {
			wnd.StrGrid.enable_blocked = true;
		}

		wnd.StrGrid.setReadOnly(disable || wnd.StrGrid.enable_blocked);
		wnd.StrGrid.getAction('action_ndue_actions').setDisabled(disable || wnd.StrGrid.enable_blocked);
		wnd.StrGrid.getAction('action_import').setDisabled(disable || wnd.StrGrid.enable_blocked);

        //разблокируем элементы для особых случаев
        if (!disable_param) {
            if (wnd.only_execution) {
                wnd.buttons[1].enable();
            }
            if(doc_status == 11){
                wnd.buttons[0].enable();
                wnd.buttons[1].enable();
                wnd.StrGrid.setReadOnly(false);
            }
        }
	},
	generateDocumentUcNum: function() {
		var wnd = this;

		wnd.getLoadMask().show();
		Ext.Ajax.request({
			params: {
				DrugDocumentType_Code: wnd.DrugDocumentType_Code,
				Contragent_id: getGlobalOptions().Contragent_id
			},
			callback: function(opt, success, resp) {
				wnd.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj[0].DocumentUc_Num != '') {
					var new_num = response_obj[0].DocumentUc_Num;
					var field = wnd.form.findField('DocumentUc_Num');
					field.setValue(new_num);
					field.fireEvent('change', field, new_num);
				}
			},
			url: '/?c=DocumentUc&m=generateDocumentUcNum'
		});
	},
    loadGrid: function() {
        var wnd = this;
        if (!Ext.isEmpty(wnd.DocumentUc_id)) {
            wnd.StrGrid.loadData({
                globalFilters: {
                    DocumentUc_id: wnd.DocumentUc_id
                },
                callback: function() {
                    wnd.StrGrid.updateSumm();
					wnd.refreshStorageWorkColumnVisibility();
                }
            });
        }
    },
	refreshStorageWorkColumnVisibility: function() {
		var hasStorageWork = false;
		this.StrGrid.getGrid().getStore().each(function(rec){
			if (!Ext.isEmpty(rec.get('DocumentUcStorageWork_id'))) {
				hasStorageWork = true;
				return false;
			}
		});
		this.StrGrid.setColumnHidden('DocumentUcStorageWork_FactQuantity', !hasStorageWork);
		this.StrGrid.setColumnHidden('DocumentUcStorageWork_Comment', !hasStorageWork);
	},
	loadForm: function(callback) {
		var wnd = this;
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				wnd.getLoadMask().hide();
				wnd.hide();
			},
			params:{
				DocumentUc_id: wnd.DocumentUc_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (!result[0]) {
					return false
				}

                wnd.form.setValues(result[0]);
                wnd.inf_form.setValues(result[0]);

				wnd.setDrugDocumentType(result[0].DrugDocumentType_Code);

				wnd.form.findField('Mol_tid_combo').setValue(result[0].Mol_tid);
				wnd.form.findField('Mol_sid_combo').setValue(result[0].Mol_sid);
				wnd.document_combo.setValueById(result[0].WhsDocumentUc_id);
				wnd.drugdocument_combo.setValueById(result[0].DocumentUc_pid);
                if (result[0].Contragent_tid > 0) wnd.form.findField('Contragent_tid').setValueById(result[0].Contragent_tid);
                if (result[0].Contragent_sid > 0) wnd.form.findField('Contragent_sid').setValueById(result[0].Contragent_sid);
				if (result[0].Storage_tid > 0) wnd.form.findField('Storage_tid').setValueById(result[0].Storage_tid);
				if (result[0].Storage_sid > 0) wnd.form.findField('Storage_sid').setValueById(result[0].Storage_sid);
				if (result[0].Storage_tid > 0) wnd.form.findField('StorageZone_tid').setValueById(result[0].StorageZone_tid);
				if (result[0].Storage_sid > 0) wnd.form.findField('StorageZone_sid').setValueById(result[0].StorageZone_sid);
				if (result[0].Lpu_id > 0) wnd.form.findField('Lpu_id').setValueById(result[0].Lpu_id);
				if (result[0].LpuBuilding_id > 0) wnd.form.findField('LpuBuilding_id').setValueById(result[0].LpuBuilding_id);
				if (result[0].WhsDocumentUcInvent_id > 0) wnd.WhsDocumentUcInvent_id = result[0].WhsDocumentUcInvent_id;
				wnd.initMolData(result[0]);
				wnd.form.findField('DocumentUc_didDate').setMinValue(wnd.form.findField('DocumentUc_setDate').getValue());

                wnd.loadGrid();
				wnd.setDisabled(wnd.action == 'view');
				if (typeof callback == 'function') {
					callback();
				}

				if (result[0].EmergencyTeam_id) {

					//Передаём идентификатор просмотренной бригады, чтобы она так же была в списке вместе с актуальными и перезагружаем стор
					wnd.form.findField('EmergencyTeam_id').getStore().baseParams.EmergencyTeam_id = result[0].EmergencyTeam_id;
					wnd.form.findField('EmergencyTeam_id').getStore().reload();

					if (wnd.DrugDocumentType_Code == '20') {
						//Получаем  МОЛов конкретной бригады
						wnd.getMolByEmergencyTeam(result[0].EmergencyTeam_id, 'Mol_tid_combo');
					}
				}



			},
			url:'/?c=DocumentUc&m=load'
		});
	},
	/**
	 * Очистка фактического кол-ва и примечаний в нарядах на работы по документу
	 */
	clearDocumentUcStorageWork: function() {
		if (Ext.isEmpty(this.form.findField('DocumentUc_id').getValue())) return;
		if (this.form.findField('DrugDocumentStatus_Code').getValue() != 1) return;

		var grid = this.StrGrid.getGrid();
		var params = {DocumentUc_id: this.DocumentUc_id};

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					grid.getStore().each(function(record) {
						var StorageWorkData = Ext.util.JSON.decode(record.get('DocumentUcStorageWorkData'));
						StorageWorkData.forEach(function(StorageWork) {
							StorageWork.DocumentUcStorageWork_FactQuantity = null;
							StorageWork.DocumentUcStorageWork_Comment = null;
						});

						record.set('DocumentUcStorageWorkData', Ext.util.JSON.encode(StorageWorkData));
						record.set('DocumentUcStorageWork_FactQuantity', null);
						record.set('DocumentUcStorageWork_Comment', null);
						record.commit();
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg: 'Вы действительно хотите удалить все «Кол-во факт.» и «Примечание»?',
			title:langs('Подтверждение')
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swNewDocumentUcEditWindow.superclass.show.apply(this, arguments);
		this.is_disabled = false; //признак запрета на редактирование для формы
		this.only_execution = false; //режим работы формы, в котором доступно только исполнение
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DocumentUc_id = null;
		this.WhsDocumentUcInvent_id = null; //идентификатор связанной с документом инв. ведомости
		this.DrugDocumentType_Code = 1; //документ прихода/расхода по умолчанию
		this.MolMedPersonal_id = null; //идентификатор врача соответствующий выбранному МОЛ
		this.FormParams = new Object();
		this.userMedStaffFact = {};
		this.isAptMu = false;
		this.isChanged = false;
        this.show_arguments = new Object();
        this.BarCodeInputEnabled = false;

        this.show_diff_gu = (getDrugControlOptions().doc_uc_different_goods_unit_control && getGlobalOptions().orgtype == 'lpu'); //����������� ���� �������� � �������������� ��. ���������

        if ( !arguments[0] ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { wnd.hide(); });
            return false;
        } else {
            this.show_arguments = arguments[0];
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].DrugDocumentStatus_Code && arguments[0].DrugDocumentStatus_Code == '4' ) { //Исполнен
			this.action = 'view';
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DocumentUc_id ) {
			this.DocumentUc_id = arguments[0].DocumentUc_id;
		}
		if ( arguments[0].DrugDocumentType_Code ) {
			this.DrugDocumentType_Code = arguments[0].DrugDocumentType_Code;
		}
		if ( arguments[0].MolMedPersonal_id ) {
			this.MolMedPersonal_id = arguments[0].MolMedPersonal_id;
		}
		if ( arguments[0].FormParams ) {
			this.FormParams = arguments[0].FormParams;
		}
		if ( arguments[0].userMedStaffFact ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		if (typeof arguments[0].isSmpMainStorage !== 'undefined') {
			this.isSmpMainStorage = arguments[0].isSmpMainStorage;
		} else {
			this.isSmpMainStorage = false;
		}

		if (!Ext.isEmpty(this.userMedStaffFact.Lpu_id) && this.userMedStaffFact.MedServiceType_SysNick == 'merch') {
			this.isAptMu = true;
		}

		if(!wnd.StrGrid.getAction('action_ndue_actions')) {
			wnd.StrGrid.addActions({
				name:'action_ndue_actions',
				text:langs('Действия'),
                menu: [{
					name: 'create_by_contract',
					iconCls: 'add16',
					text: langs('Cоздать на основе спецификации ГК'),
					handler: function() {
                        wnd.createDocumentUcStrListByContract();
                    }
				}, {
					name: 'copy_row',
					iconCls: 'copy16',
					text: langs('Копировать медикамент'),
					handler: function() {
						wnd.StrGrid.copyRecord();
					}
				}, {
                    name: 'create_by_document',
                    iconCls: 'add16',
                    text: langs('Сформировать по документу'),
                    handler: function() {
                        wnd.createDocumentUcStrListByDocument();
                    }
                }, {
                    name: 'create_by_store_ostat',
                    iconCls: 'add16',
                    text: langs('Сформировать по остаткам склада'),
                    handler: function() {
                        wnd.createDocumentUcStrListByOstat();
                    }
                }, {
                	name: 'clear_storage_work',
					iconCls: 'delete16',
					text: 'Выполнение работ: удалить все «Кол-во факт.» и «Примечание»',
					handler: function() {
                		wnd.clearDocumentUcStorageWork();
					}
				}],
				iconCls: 'actions16'
			});
		}

		if(!wnd.StrGrid.getAction('action_import')) {
			wnd.StrGrid.addActions({
				name: 'action_import',
				iconCls: 'add16',
				text: langs('Импорт'),
				hidden: true,
				handler: wnd.importDocumentUcStr.createDelegate(wnd)
			});
		}

		this.form.reset();
		this.inf_form.reset();
        this.BarCodeInputPanel.getForm().reset();

		this.document_combo.resetCombo();
		this.form.findField('Contragent_sid').resetCombo();
		this.form.findField('Contragent_tid').resetCombo();
		this.form.findField('Storage_sid').fullReset();
		this.form.findField('Storage_tid').fullReset();
		this.form.findField('StorageZone_sid').fullReset();
		this.form.findField('StorageZone_tid').fullReset();
		this.form.findField('EmergencyTeam_id').getStore().removeAll();
		this.form.findField('EmergencyTeam_id').getStore().load();
		this.setDrugDocumentType();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
        loadMask.show();
		switch (this.action) {
			case 'add':
				wnd.StrGrid.removeAll();
				wnd.StrGrid.updateSumm();
				wnd.setDefaultValues();
				wnd.initMolData(wnd.FormParams);
				wnd.setDisabled(false);
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				this.loadForm(function(){loadMask.hide()});
			break;
		}
	},
	initComponent: function() {
		var wnd = this;

		this.ScanCodeService = new sw.Promed.ScanCodeService({
			onGetDrugPackData: function(drugPackObject) {
				wnd.StrGrid.addRecordByBarCode(drugPackObject, 'scanner_data');
			}
		});

        var s_combo_config = {
            fieldLabel: langs('Склад'),
            hiddenName: 'Storage_id',
            displayField: 'Storage_Name',
            valueField: 'Storage_id',
            allowBlank: true,
            width: 525,
            ownerWindow: wnd,
            setLinkedFieldValues: function(event_name) {
                wnd.setMol(this, true);
            }
        };

        var s_combo_store_config = {
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'Storage_id'
            }, [
                {name: 'Storage_id', mapping: 'Storage_id'},
                {name: 'Storage_sid', mapping: 'Storage_id'}, //добавлено для организациии влияния склада -поставщика на склад-получатель
                {name: 'StorageType_id', mapping: 'StorageType_id'},
                {name: 'StorageType_Code', mapping: 'StorageType_Code'},
                {name: 'Storage_Code', mapping: 'Storage_Code'},
                {name: 'Storage_Name', mapping: 'Storage_Name'},
                {name: 'Storage_begDate', mapping: 'Storage_begDate'},
                {name: 'Storage_Code', mapping: 'Storage_Code'},
                {name: 'StorageStructLevel', mapping: 'StorageStructLevel'},
                {name: 'LpuSection_id', mapping: 'LpuSection_id'},
                {name: 'MedService_id', mapping: 'MedService_id'},
                {name: 'Org_id', mapping: 'Org_id'}
            ]),
            url: '/?c=DocumentUc&m=loadStorageList'
        };

        s_combo_config.hiddenName = 'Storage_sid';
        s_combo_config.store = new Ext.data.Store(s_combo_store_config);
        s_combo_config.defaultChildrenList = ['StorageZone_sid'];
        s_combo_config.defaultHeritageList = ['Storage_id'];
        s_combo_config.childrenList = s_combo_config.defaultChildrenList;
        s_combo_config.heritageList = s_combo_config.defaultHeritageList;
        wnd.s_s_combo = new sw.Promed.SwCustomOwnerCombo(s_combo_config);

        s_combo_config.hiddenName = 'Storage_tid';
        s_combo_config.store = new Ext.data.Store(s_combo_store_config);
        s_combo_config.defaultChildrenList = ['StorageZone_tid'];
        s_combo_config.childrenList = s_combo_config.defaultChildrenList;
        wnd.s_t_combo = new sw.Promed.SwCustomOwnerCombo(s_combo_config);

        var sz_combo_config = {
            fieldLabel: 'Место хранения',
            hiddenName: 'StorageZone_id',
            displayField: 'StorageZone_Name',
            valueField: 'StorageZone_id',
            allowBlank: true,
            width: 525,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 50%;">Адрес</td>',
                '<td style="padding: 2px; width: 50%;">Наименование</td>',
                '</tr><tpl for="."><tr class="x-combo-list-item">',
                '<td style="padding: 2px;">{StorageZone_Address}&nbsp;</td>',
                '<td style="padding: 2px;">{StorageUnitType_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            setLinkedFieldValues: function(event_name) {
                this.syncStorageZoneByField();
            },
            syncStorageZoneByField: function() { //функция синхронизации мест хранений в грриде со значением комбобокса
                if (wnd.sync_storagezone_by_field != this.hiddenName) {
                    return true;
                }

                var sz_id = this.getValue();
                var sz_name = null;
                var sz_data = this.getSelectedRecordData();
                if (sz_data && !Ext.isEmpty(sz_data.StorageZone_Name)) {
                    sz_name = sz_data.StorageZone_Name;
                }

                if (!wnd.StrGrid.readOnly) {
                    wnd.StrGrid.getGrid().getStore().each(function(record) {
                        if (!Ext.isEmpty(sz_id) && !Ext.isEmpty(record.get('Drug_id')) && record.get('StorageZone_id') != sz_id) {
                            record.set('state', record.get('state') == 'add' ? 'add' : 'edit');
                            record.set('StorageZone_id', sz_id);
                            record.set('StorageZone_Name', sz_name);
                            record.commit();
                        }
                    });
                }

                return true;
            }
        };

        var sz_combo_store_config = {
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                id: 'StorageZone_id'
            }, [
                {name: 'StorageZone_id', mapping: 'StorageZone_id'},
                {name: 'StorageZone_Name', mapping: 'StorageZone_Name'},
                {name: 'StorageZone_Address', mapping: 'StorageZone_Address'},
                {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'}
            ]),
            url: '/?c=DocumentUc&m=loadStorageZoneCombo'
        };

        sz_combo_config.hiddenName = 'StorageZone_sid';
        sz_combo_config.fieldLabel = 'С места хранения';
        sz_combo_config.store = new Ext.data.Store(sz_combo_store_config);
        wnd.sz_s_combo = new sw.Promed.SwCustomRemoteCombo(sz_combo_config);

        sz_combo_config.hiddenName = 'StorageZone_tid';
        sz_combo_config.fieldLabel = 'На место хранения';
        sz_combo_config.store = new Ext.data.Store(sz_combo_store_config);
        wnd.sz_t_combo = new sw.Promed.SwCustomRemoteCombo(sz_combo_config);

		wnd.document_combo = new sw.Promed.SwDrugComplexMnnCombo({
			width: 525,
			allowBlank: true,
			displayField: 'WhsDocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: langs('Контракт'),
			forceSelection: true,
			hiddenName: 'WhsDocumentUc_id',
			loadingText: langs('Идет поиск...'),
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			trigger2Class: 'x-form-search-trigger',
			trigger3Class: 'x-form-date-trigger',
			resizable: true,
			selectOnFocus: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;">&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'WhsDocumentUc_id',
			listeners: {
				select: function(combo, record) {
					this.setLinkedFieldValues(record);
				},
				keydown: function(combo, e) {
					if ( e.getKey() == e.DELETE)
					{
						combo.setValue(null);
						if (combo.allowBlank) {
							var record = combo.getStore().getAt(0);
							combo.setLinkedFieldValues(record);
						}
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
				if (this.disabled) {
                    return false;
                }

				var params = this.getStore().baseParams;
				var combo = this;
				combo.disableBlurAction = true;
				getWnd(combo.searchWindow).show({
					params: params,
					searchUrl: combo.searchUrl,
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
							WhsDocumentType_Code: data.WhsDocumentType_Code,
							Contragent_sid: data.Contragent_sid,
							DrugFinance_id: data.DrugFinance_id,
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id,
                            Org_pid: data.Org_pid
						}], true);

						combo.setValue(data.WhsDocumentUc_id);
						var index = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == data.WhsDocumentUc_id; });

						if (index == -1) {
							return false;
						}

						var record = combo.getStore().getAt(index);

						if ( typeof record == 'object' ) {
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, record.get('WhsDocumentUc_id'));
						}

						combo.setLinkedFieldValues(record);

						getWnd(combo.searchWindow).hide();
					}
				});
			},
            onTrigger3Click: function() {
                this.openViewForm()
            },
            openViewForm: function() {
                var combo = this;
                var record = null;
                var form_name = null;
                var form_params = new Object();

                if (this.getValue() > 0) {
                    this.getStore().each(function(r) {
                        if (r.get(combo.valueField) == combo.getValue()) {
                            record = r;
                            return true;
                        }
                    });
                }

                if (record && record.get('WhsDocumentType_Code') > 0) {
                    form_params.action = 'view';
                    switch(record.get('WhsDocumentType_Code')) {
                        case '3': //3 - Контракт на поставку
                        case '6': //6 - Контракт на поставку и отпуск
                        case '18': //18 - Контракт ввода остатков
                            form_name = 'swWhsDocumentSupplyEditWindow';
                            form_params.WhsDocumentSupply_id = record.get('WhsDocumentSupply_id');
                            break;
                        case '22': //22 - Заявка
                            form_name = 'swWhsDocumentUcEditWindow';
                            form_params[this.valueField] = this.getValue();
                            break;
                        /*case '25': //25 - Заказ на производство
                            form_name = 'swWhsDocumentUcOrderEditWindow';
                            form_params[this.valueField] = this.getValue();
                            break;*/
                    }
                }

                if (!Ext.isEmpty(form_name)) {
                    getWnd(form_name).show(form_params);
                }
            },
			resetCombo: function() {
				this.lastQuery = '';
				this.getStore().removeAll();
				this.getStore().baseParams.query = '';
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.WhsDocumentUc_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.WhsDocumentUc_id = null;
						combo.disableLinkedField();
					}
				});
			},
			setLinkedFieldValues: function(record) {
				if (!record) {
					var index = this.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == this.getValue(); }.createDelegate(this));
					if (index == -1) {
						return false;
					}
					record = this.getStore().getAt(index);
				}
				wnd.form.findField('DocumentUc_DogDate').setValue(record.get('WhsDocumentUc_Date'));
				wnd.form.findField('DocumentUc_DogNum').setValue(record.get('WhsDocumentUc_Num'));
				wnd.form.findField('DrugFinance_id').setValue(record.get('DrugFinance_id'));
				wnd.form.findField('WhsDocumentCostItemType_id').setValue(record.get('WhsDocumentCostItemType_id'));

                if (!wnd.form.findField('Contragent_sid').enable_blocked) {
                    if (!Ext.isEmpty(record.get('Contragent_sid'))) {
                        wnd.form.findField('Contragent_sid').setValueById(record.get('Contragent_sid'));
                    } else {
                        wnd.form.findField('Contragent_sid').resetCombo();
                        wnd.form.findField('Contragent_sid').getStore().load();
                    }
                }

				var contr_t_combo = wnd.form.findField('Contragent_tid');
				if (wnd.DrugDocumentType_Code.inlist([6]) && !Ext.isEmpty(getGlobalOptions().org_id)) {
					if (!Ext.isEmpty(record.get('WhsDocumentUc_id'))) {
						var contr_t_index = contr_t_combo.getStore().findBy(function(rec) { return rec.get('Org_id') == getGlobalOptions().org_id; });
						var contr_t_record = contr_t_combo.getStore().getAt(contr_t_index);
						if (contr_t_record) {
							contr_t_combo.setValue(contr_t_record.id);
						}
					} else {
						contr_t_combo.setValue(null);
					}
				}
				wnd.checkStorageByWhsDocumentUc(record.get('WhsDocumentUc_id'));

                var acc_type_combo = wnd.form.findField('AccountType_id');
                if (!acc_type_combo.enable_blocked && record.get('Org_pid') && record.get('Org_pid') != getGlobalOptions().org_id) {
                    acc_type_combo.setValue(2); //2 - забалансовый учет
                }

				this.disableLinkedField(record);
			},
			disableLinkedField: function(record) {
				if (!record) {
					var index = this.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == this.getValue(); }.createDelegate(this));
					if (index == -1) {
						return false;
					}
					record = this.getStore().getAt(index);
				}
				var fin_combo = wnd.form.findField('DrugFinance_id');
				var cost_combo = wnd.form.findField('WhsDocumentCostItemType_id');
				var contr_s_combo = wnd.form.findField('Contragent_sid');

				if (fin_combo.getValue() == record.get('DrugFinance_id')) {
					fin_combo.disable();
				} else if (wnd.action != 'view') {
					fin_combo.enable();
				}

				if (cost_combo.getValue() == record.get('WhsDocumentCostItemType_id')) {
					cost_combo.disable();
				} else if (wnd.action != 'view') {
					cost_combo.enable();
				}

				if (!Ext.isEmpty(record.get('Contragent_sid')) && contr_s_combo.getValue() == record.get('Contragent_sid')) {
					contr_s_combo.disable();
				} else if (wnd.action != 'view' && !contr_s_combo.enable_blocked) {
					contr_s_combo.enable();
				}
			},
            setSearchUrl: function(url) {
                this.getStore().proxy.conn.url = url;
                this.searchUrl = url;
            },
            setSearchWindow: function(wnd_name) {
                this.searchWindow = wnd_name;
            },
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);

                this.triggerConfig = {
                    tag:'span',
                    cls:'x-form-twin-triggers',
                    cn:[
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class},
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger3Class}
                    ]
                };

				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'WhsDocumentUc_id'
						},
						[
							{name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id'},
							{name: 'WhsDocumentType_Code', mapping: 'WhsDocumentType_Code'},
							{name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date'},
							{name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num'},
							{name: 'Contragent_sid', mapping: 'Contragent_sid'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'},
							{name: 'Org_pid', mapping: 'Org_pid'}
						]),
					url: '/?c=Farmacy&m=loadWhsDocumentSupplyList'
				});
			}
		});

		wnd.drugdocument_combo = new sw.Promed.SwBaseRemoteCombo({
			width: 542,
			allowBlank: true,
			displayField: 'DocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: langs('Родительский документ'),
			forceSelection: true,
			hiddenName: 'DocumentUc_pid',
			id: 'ndueDocumentUc_pid',
			loadingText: langs('Идет поиск...'),
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			trigger2Class: 'x-form-search-trigger',
			resizable: true,
			selectOnFocus: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{DocumentUc_Num}</h3></td><td style="width:20%;"></td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'DocumentUc_id',
			onTrigger2Click: Ext.emptyFn,
			trigger2Class: 'hideTrigger',
			resetCombo: function() {
				this.lastQuery = '';
				this.getStore().removeAll();
				this.getStore().baseParams.query = '';
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.DocumentUc_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.DocumentUc_id = null;
					}
				});
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'DocumentUc_id'
						},
						[
							{name: 'DocumentUc_id', mapping: 'DocumentUc_id'},
							{name: 'DocumentUc_Num', mapping: 'DocumentUc_Num'}
						]),
					url: '/?c=DocumentUc&m=loadList'
				});
			}
		});

        wnd.lpu_combo = new sw.Promed.SwCustomOwnerCombo({
            width: 525,
            anchor: false,
            name: 'Lpu_id',
            xtype: 'swcustomownercombo',
            fieldLabel: 'МО',
            hiddenName: 'Lpu_id',
            displayField: 'Lpu_Nick',
            valueField: 'Lpu_id',
            allowBlank: true,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Lpu_id', mapping: 'Lpu_id' },
                    { name: 'Lpu_Nick', mapping: 'Lpu_Nick' }
                ],
                key: 'Lpu_id',
                sortInfo: { field: 'Lpu_Nick' },
                url:'/?c=DocumentUc&m=loadLpuCombo'
            }),
            ownerWindow: wnd,
            childrenList: ['LpuBuilding_id'],
            onBeforeChange: function(combo, newValue) {
                if (newValue > 0 && !wnd.is_disabled) {
                    wnd.lpu_building_combo.enable_blocked = false;
                    wnd.lpu_building_combo.enable();
                }
            },
            setLinkedFieldValues: function(event_name) {
                if (Ext.isEmpty(wnd.lpu_combo.getValue()) || wnd.is_disabled) {
                    wnd.lpu_building_combo.enable_blocked = true;
                    wnd.lpu_building_combo.disable();
                } else {
                    wnd.lpu_building_combo.enable_blocked = false;
                    wnd.lpu_building_combo.enable();
                }
            }
        });

        wnd.lpu_building_combo = new sw.Promed.SwCustomOwnerCombo({
            width: 525,
            anchor: false,
            name: 'LpuBuilding_id',
            xtype: 'swcustomownercombo',
            fieldLabel: 'Подразделение',
            hiddenName: 'LpuBuilding_id',
            displayField: 'LpuBuilding_Name',
            valueField: 'LpuBuilding_id',
            allowBlank: true,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
                    { name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' }
                ],
                key: 'LpuBuilding_id',
                sortInfo: { field: 'LpuBuilding_Name' },
                url:'/?c=DocumentUc&m=loadLpuBuildingCombo'
            }),
            ownerWindow: wnd
        });

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			//height: 270,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'NewDocumentUcEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 160,
				collapsible: true,
				url:'/?c=DocumentUc&m=save',
				items: [{
					xtype: 'hidden',
					name: 'DocumentUc_id'
				},	{
					xtype: 'hidden',
					name: 'DrugDocumentType_id'
				},	{
					xtype: 'hidden',
					name: 'DrugDocumentStatus_id'
				},	{
					xtype: 'hidden',
					name: 'DrugDocumentStatus_Code'
				},	{
					xtype: 'hidden',
					name: 'Org_id'
				},	{
					xtype: 'hidden',
					name: 'DocumentUc_DogDate'
				},	{
					xtype: 'hidden',
					name: 'DocumentUc_DogNum'
				},	{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'trigger',
							fieldLabel: langs('Документ №'),
							name: 'DocumentUc_Num',
							width: 147,
							allowBlank: false,
							enableKeyEvents: true,
							triggerClass: 'x-form-plus-trigger',
							validateOnBlur: false,
							onTriggerClick: function() {
								var field = wnd.form.findField('DocumentUc_Num');
								if (!field.disabled) {
									wnd.generateDocumentUcNum();
								}
							}.createDelegate(this),
							listeners: {
								change: function (combo, newValue) {
									var inv_field = wnd.form.findField('DocumentUc_InvoiceNum');
									if (!inv_field.ownerCt.hidden && (!wnd.FormParams || !wnd.FormParams.DocumentUc_InvoiceNum) && (inv_field.getValue() == '' || inv_field.getValue() == null)) {
										inv_field.setValue(combo.getValue());
									}
								}
							}
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 46,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: langs('От'),
							name: 'DocumentUc_setDate',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							allowBlank: false,
							listeners: {
								change: function (combo, newValue) {
									wnd.form.findField('DocumentUc_didDate').setMinValue(this.getValue());

									var inv_field = wnd.form.findField('DocumentUc_InvoiceDate');
									if (!inv_field.ownerCt.hidden && (!wnd.FormParams || !wnd.FormParams.DocumentUc_InvoiceDate) && (inv_field.getValue() == '' || inv_field.getValue() == null)) {
										inv_field.setValue(combo.getValue());
									}

                                    var mol_s_field = wnd.form.findField('Mol_sid_combo');
									if (!mol_s_field.ownerCt.hidden && !mol_s_field.disabled) {
                                        var store_combo = wnd.form.findField('Storage_sid');
                                        var ctr_combo = wnd.form.findField('Contragent_sid');
                                        if (store_combo.getValue() > 0) {
                                            wnd.setMol(store_combo, true);
                                        } else if(ctr_combo.getValue() > 0) {
                                            wnd.setMol(ctr_combo, true);
                                        }
									}
								}
							}
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 120,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: langs('Дата поставки'),
							name: 'DocumentUc_didDate',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							allowBlank: false
						}]
					}]
				},	{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'trigger',
							fieldLabel: langs('Счет-фактура №'),
							name: 'DocumentUc_InvoiceNum',
							width: 147,
							allowBlank: true,
							enableKeyEvents: true,
							onTriggerClick: function() {
								var field = wnd.form.findField('DocumentUc_InvoiceNum');
								if (!field.disabled) {
									var num = wnd.form.findField('DocumentUc_Num').getValue();
									wnd.form.findField('DocumentUc_InvoiceNum').setValue(num);
								}
							}.createDelegate(this),
							triggerClass: 'x-form-equil-trigger',
							validateOnBlur: false
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 46,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: langs('От'),
							name: 'DocumentUc_InvoiceDate',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							allowBlank: true
						}]
					}]
                },	{
                    layout: 'form',
					items: [wnd.document_combo]
				},	{
					layout: 'form',
					items: [{
                        xtype: 'textfield',
                        disabled: true,
                        fieldLabel: langs('Основание'),
                        name: 'WhsDocumentUc_FullName',
                        width: 525
                    }]
				},	{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swdrugfinancecombo',
							fieldLabel: langs('Ист. финансирования'),
							name: 'DrugFinance_id',
							width: 200,
							allowBlank: false
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 118,
						items: [{
							xtype: 'swwhsdocumentcostitemtypecombo',
							fieldLabel: langs('Статья расхода'),
							name: 'WhsDocumentCostItemType_id',
							width: 200,
							allowBlank: false
						}]
					}]
				},	{
					layout: 'form',
					items: [{
						xtype: 'swcontragentcombo',
						fieldLabel: langs('Поставщик'),
						hiddenName: 'Contragent_sid',
						width: 525,
						resetCombo: function() {
							this.setValue(null);
							this.lastQuery = '';
							this.getStore().removeAll();
							this.getStore().baseParams.query = '';
							this.getStore().baseParams.Contragent_id = null;
						},
						setValueById: function(id) {
							var combo = this;
							combo.store.baseParams.Contragent_id = id;
							combo.store.load({
								callback: function(){
									combo.setValue(id);
									combo.store.baseParams.Contragent_id = null;
									wnd.document_combo.disableLinkedField();
								}
							});
						},
						listeners: {
							render: function(combo) {
								combo.getStore().proxy.conn.url = '/?c=DocumentUc&m=loadContragentList';
							},
							change: function(combo) {
								wnd.setMol(combo, true);
							}
						}
					}]
				},  {
					layout: 'form',
					items: [
                        wnd.s_s_combo
                    ]
				},  {
					layout: 'form',
					items: [{
						xtype: 'hidden',
						name: 'Mol_sid',
						width: 525
					},  {
						xtype: 'textfield',
						disabled: true,
						fieldLabel: langs('МОЛ поставщика'),
						name: 'Mol_sPerson',
						width: 525
					}]
				},	{
					layout: 'form',
					items: [{
						xtype: 'swmolcombo',
						disabled: true,
						fieldLabel: langs('МОЛ'),
						hiddenName: 'Mol_sid_combo',
						width: 525,
						listeners: {
							'change': function(combo) {
								wnd.form.findField('Mol_sid').setValue(combo.getValue());
							}
						}
					}]
				},  {
                    layout: 'form',
                    items: [
                        wnd.sz_s_combo
                    ]
                }, {
					layout: 'form',
					items: [{
						xtype: 'swcontragentcombo',
						fieldLabel: langs('Получатель'),
						hiddenName: 'Contragent_tid',
						width: 525,
						allowBlank: false,
						resetCombo: function() {
							this.lastQuery = '';
							this.getStore().removeAll();
							this.getStore().baseParams.query = '';
							this.getStore().baseParams.Contragent_id = null;
						},
						setValueById: function(id) {
							var combo = this;
							combo.store.baseParams.Contragent_id = id;
							combo.store.load({
								callback: function(){
									combo.setValue(id);
									combo.store.baseParams.Contragent_id = null;
								}
							});
						},
						listeners: {
							render: function(combo) {
								combo.getStore().proxy.conn.url = '/?c=DocumentUc&m=loadContragentList';
							},
							change: function(combo) {
								wnd.setMol(combo, true);
							}
						}
					}]
				},  {
					layout: 'form',
					items: [
                        wnd.s_t_combo
                    ]
				},	{
					layout: 'form',
					width: 525,
					items: [{
						name: 'EmergencyTeam_id',
						xtype: 'swunfinishedemergencyteamcombo',
						listeners: {
							change: function(combo) {
								wnd.getMolByEmergencyTeam(combo.getValue(), 'Mol_tid_combo');
							}
						}
					}]
				},  {
					layout: 'form',
					items: [{
						xtype: 'hidden',
						name: 'Mol_tid',
						width: 525
					},  {
						xtype: 'textfield',
						disabled: true,
						fieldLabel: langs('МОЛ'),
						name: 'Mol_tPerson',
						width: 525
					}]
				},  {
					layout: 'form',
					items: [{
						xtype: 'swmolcombo',
						disabled: true,
						fieldLabel: langs('МОЛ'),
						hiddenName: 'Mol_tid_combo',
						width: 525,
						listeners: {
							'change': function(combo) {
								wnd.form.findField('Mol_tid').setValue(combo.getValue());
							}
						}
					}]
				},  {
                    layout: 'form',
                    items: [
                        wnd.sz_t_combo
                    ]
                },  {
					layout: 'form',
					items: [wnd.lpu_combo]
				},  {
					layout: 'form',
					items: [wnd.lpu_building_combo]
				},  {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						disabled: true,
						fieldLabel: langs('Рецепт'),
						name: 'EvnRecept_Name',
						width: 525,
						listeners: {
							change: function(combo) {
								wnd.setMol(combo, true);
							}
						}
					}]
				},	{
					layout: 'form',
					items: [wnd.drugdocument_combo]
				}, {
                    layout: 'form',
                    items: [{
                        xtype: 'swcommonsprcombo',
                        fieldLabel: 'Тип учета',
                        hiddenName: 'AccountType_id',
                        comboSubject: 'AccountType',
                        anchor: false,
                        width: 200,
                        allowBlank: true
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        xtype: 'textfield',
                        readOnly: true,
                        fieldLabel: 'Ответственное лицо',
                        name: 'StorageZoneLiable_ObjectName',
                        anchor: false,
                        width: 525,
                        allowBlank: true
                    }]
                }]
			}]
		});

        this.BarCodeInputPanel = new Ext.FormPanel({
            region: 'north',
            autoHeight: true,
            frame: true,
            bodyStyle: 'margin: 5px 0',
            labelAlign: 'right',
            defaults: {
                width: 400
            },
            items: [{
                xtype: 'textfield',
                hiddenName: 'BarCodeInput_Field',
                fieldLabel: 'Штрих-код',
                border: false,
                listeners: {
                    change: function(field, newValue) {
                        if (newValue.length == 3 || newValue.length == 5 || newValue.length == 27) {
                            wnd.StrGrid.addRecordByBarCode(newValue, 'test_str');
                        }
                    }
                }
            }]
        });

        function createTooltip(ss) {
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
		                var wind = Ext.getCmp('ndueDocumentUcStrGrid').getGrid();
		                var ss = wind.getStore();
		                var ww = wind.getView();
		                var indX = ww.findRowIndex(tip.triggerElement);
		                var bodyHint = '';
		                var br = '<br>';
		                console.log('tip : '+indX);
		                if(indX !== false && indX >=0){
		                	var dataObj = ss.getAt(indX);
		                	if(dataObj){
		                		var mark = '&#8226;';
		                		var str1 = dataObj.get('hintTradeName');
		                		var str2 = dataObj.get('hintPackagingData');
		                		var str3 = dataObj.get('hintRegistrationData');
		                		var str4 = dataObj.get('hintPRUP');
		                		var firmNames = dataObj.get('FirmNames');

		                		str1 = (str1) ? str1.replace(/"/g, "&#8242;") : '';
		                		if(str2){
									if(firmNames){
										var pr = firmNames.replace('(', '\\(');
										pr = firmNames.replace(')', '\\)');
										var re = new RegExp("(, |^)"+pr+".*");
										str2 = str2.replace(re, '');
										str2 = str2.replace(/"/g, "&#8242;");
									}else{
										str2 = str2.replace(/"/g, "&#8242;");
									}
								}else{
									str2 = '';
								}
								str3 = (str3) ? str3.replace(/"/g, "&#8242;"): '';
            					str4 = (str4) ? str4.replace(/"/g, "&#8242;"): '';

            					if(str1){
									bodyHint += mark + ' Торговое наименование: ' + str1 + br;
								}
								if(str2){
									if(str2.slice(-1) == ',') str2=str2.slice(0, -1);
									bodyHint += mark + ' Данные об упаковке: ' + str2 + br;
								}
								if(str3){
									if(str3.slice(-1) == ',') str3=str3.slice(0, -1);
									bodyHint += mark + ' Данные о регистрации: ' + str3 + br;
								}
								if(str4){
									bodyHint += mark + ' Пр./Уп.: ' + str4;
								}
							}

							tip.body.dom.innerHTML = bodyHint;
							if(!bodyHint) tip.hide();
						}else{
							tip.on('show', function(){
								tip.hide();
							}, tip, {single: true});
						}
					}
				}
		    });
		}

		this.StrGrid = new sw.Promed.ViewFrame({
			title: '',
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
			border: false,
			dataUrl: '/?c=DocumentUc&m=loadDocumentUcStrList',
			height: 180,
			region: 'center',
			object: 'DocumentUcStr',
			editformclassname: 'swNewDocumentUcStrEditWindow',
			id: 'ndueDocumentUcStrGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			listeners: {
				render: createTooltip
		    },
			stringfields: [
				{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
				{name: 'PostDocumentUcStr_id', hidden: true},
				{name: 'DocumentUcStr_oid', hidden: true},
				{name: 'DocumentUcStr_oid_saved', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_Fio', type: 'string', header: langs('Пациент'), width: 180, hidden: true},
				{name: 'DrugNomen_Code', width: 80, header: langs('Код')},
				{name: 'Drug_id', hidden: true},
				{name: 'Drug_isPKU', header: langs('ПКУ'), renderer: function(v, p, record){
                    if(!v){
                        return "";
                    }
                    if (p) {
                        p.css += ' x-grid3-check-col-td';
                        var style = 'x-grid3-check-col-non-border'+((String(v)==1)?'-on':'');
                        return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
                    } else {
                        return "v";
                    }
                }},
				{name: 'Drug_Name', id: 'autoexpand', header: langs('Наименование')},
				{name: 'DocumentUcStr_Ser', width: 120, header: langs('Серия')},
				{name: 'DocumentUcStr_godnDate', hidden: true},
				{name: 'PrepSeries_id', hidden: true},
				{name: 'PrepSeries_GodnDate', width: 110, header: langs('Срок годности'), type: 'date'},
				{name: 'Lpu_id', width: 110, header: 'lpu_id', type: 'string', hidden: true},
				{name: 'PrepSeries_isDefect', width: 80, header: langs('Брак'), renderer: function(v, p, record) {
                    if(!v){
                        return "";
                    }
                    if (p) {
                        p.css += ' x-grid3-check-col-td';
                        var style = 'x-grid3-check-col'+((String(v)=='true' || String(v)=='1')?'-on-non-border-yellow':'-non-border-gray');
                        return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
                    } else {
                        return "v";
                    }
				}},

				{name: 'DocumentUcStr_PlanKolvo', type: 'float', hidden: true},

                {name: 'GoodsUnit_bName', width: 80, header: langs('Ед. учета'), type: 'string'},
				{name: 'DocumentUcStr_Count', width: 80, header: langs('Кол-во (ед.уч.)'), type: 'float'},
				{name: 'DocumentUcStr_Price', width: 100, header: langs('Цена (ед.уч.)'), type: 'money'},
				{name: 'DocumentUcStr_RegPrice', width: 100, header: langs('Цена произв.'), type: 'money'},
                {name: 'GoodsUnit_Name', width: 80, header: langs('Ед. спис.'), type: 'string'},
                {name: 'DocumentUcStr_EdCount', width: 80, header: langs('Кол-во (ед. спис.)'), type: 'float'},
                {name: 'DocumentUcStr_Sum', width: 100, header: langs('Сумма'), type: 'money'},

                {name: 'PostGoodsUnit_bName', width: 80, header: langs('Поставить<br/>Ед. учета'), type: 'string'},
                {name: 'PostDocumentUcStr_Count', width: 80, header: langs('Поставить<br/>Кол-во (ед.уч.)'), type: 'float'},
                {name: 'PostDocumentUcStr_Price', width: 100, header: langs('Поставить<br/>Цена (ед.уч.)'), type: 'money'},
                {name: 'PostDocumentUcStr_Sum', width: 100, header: langs('Поставить<br/>Сумма'), type: 'money'},

				{name: 'DocumentUcStr_IsNDS', width: 80, header: langs('НДС в т.ч.'), renderer: function(v, p, record){
					if(!v){
                        return "";
                    }
					if (p) {
                        p.css += ' x-grid3-check-col-td';
                        var style = 'x-grid3-check-col-non-border'+((String(v)==1)?'-on':'');
                        return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
                    } else {
                        return "v";
                    }
				}},
				{name: 'DrugNds_id', hidden: true},
				{name: 'DrugNds_Code', width: 80, header: langs('НДС'), type: 'string'},
				{name: 'DocumentUcStr_SumNds', width: 110, header: langs('Сумма НДС'), type: 'money'},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: langs('Сумма с НДС'), type: 'money'},
				{name: 'DocumentUcStr_oName', width: 200, header: langs('Партия'), type: 'string'},
                {name: 'AccountType_Name', hidden: true},
                {name: 'BarCodeList', header: langs('Штрих-коды'), width: 100, renderer: function(v, p, record)	{ return record.get('BarCode_Count') > 0 ? '<a href="javascript:getWnd(\'swNewDocumentUcEditWindow\').openBarCodeViewWindow();" style="cursor: pointer; color: #0000EE;">открыть список</a>' : ''; }},
                {name: 'DrugShipment_Name', width: 200, header: langs('Партия'),  renderer: function(v, p, record) {
                    var val = v;
                    if (val == 'set_name_by_id') { //скрываем техническое значение
                        val = "";
                    } else if (!Ext.isEmpty(record.get('AccountType_Name'))) {
                        val += ' ' + record.get('AccountType_Name');
                    }
                    return val;
                }},
				{name: 'StorageZone_Name', width: 200, header: 'Место хранения', type: 'string'},
				{name: 'DocumentUcStr_Reason', width: 110, header: langs('Причина'), type: 'string'},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'DocumentUcStr_CertNum', hidden: true},
				{name: 'DocumentUcStr_CertDate', hidden: true},
				{name: 'DocumentUcStr_CertGodnDate', hidden: true},
				{name: 'DocumentUcStr_CertOrg', hidden: true},
				{name: 'DrugLabResult_Name', hidden: true},
				{name: 'SavedFileCount', hidden: true},
				{name: 'FileData', hidden: true},
				{name: 'FileChangedData', hidden: true},
				{name: 'BarCodeChangedData', hidden: true},
				{name: 'DrugDocumentStatus_id', hidden: true},
				{name: 'DrugDocumentStatus_Code', hidden: true},
				{name: 'DrugDocumentStatus_Name', width: 50, header: langs('Статус'), renderer: function(v, p, record) {
                    var val = '';
                    if (!Ext.isEmpty(record.get('DrugDocumentStatus_Code'))) {
                        val = v;
                    } else if (!Ext.isEmpty(record.get('DocumentUcStr_id')))  {
                        val = 'Новый';
                    }
                    return val;
                }},
                {name: 'GoodsUnit_bid', hidden: true},
                {name: 'GoodsUnit_id', hidden: true},
                {name: 'Okei_id', hidden: true},
                {name: 'DocumentUcStr_Count_saved', hidden: true},
                {name: 'DocumentUcStr_EdPrice', hidden: true},
                {name: 'GoodsPackCount_Count', hidden: true},
				{name: 'StorageZone_id', hidden: true},
				{name: 'DocumentUcStr_RegDate', hidden: true},

				{name: 'PostStorageZone_id', hidden: true},
                {name: 'PostGoodsUnit_bid', hidden: true},
                {name: 'PostGoodsUnit_id', hidden: true},
                //{name: 'PostGoodsUnit_Name', hidden: true},
                {name: 'PostDocumentUcStr_EdCount', hidden: true},
                {name: 'PostDrugNds_id', hidden: true},
                {name: 'PostDocumentUcStr_IsNDS', hidden: true},
                {name: 'PostDocumentUcStr_NdsSum', hidden: true},
                {name: 'PostDocumentUcStr_SumNds', hidden: true},

                {name: 'SavedBarCode_Count', hidden: true},
                {name: 'BarCode_Count', hidden: true},
				{name: 'DocumentUcStorageWorkData', type: 'string', hidden: true},
				{name: 'DocumentUcStorageWork_id', type: 'int', hidden: true},
				{name: 'DocumentUcStorageWork_FactQuantity', width: 85, header: 'Кол-во (факт.)', type: 'float', hidden: true},
				{name: 'DocumentUcStorageWork_Comment', wdith: 160, header: 'Примечание', type: 'string', hidden: true},
				{name: 'hintPackagingData', type: 'string', hidden: true},
				{name: 'hintRegistrationData', type: 'string', hidden: true},
				{name: 'hintPRUP', type: 'string', hidden: true},
				{name: 'FirmNames', type: 'string', hidden: true},
				{name: 'hintTradeName', type: 'string', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DocumentUcStr_id') > 0 && !this.readOnly && record.get('DrugDocumentStatus_Code') != 4) {
					this.ViewActions.action_edit.setDisabled(false);
					//this.ViewActions.action_ndue_actions.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					//this.ViewActions.action_ndue_actions.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
            onLoadData: function() {
                var view_frame = this;
                var only_upak = false;
                this.getGrid().getStore().each(function(record) {
                    if (record.get('DocumentUcStr_oid') > 0 && record.get('state') != 'add') {
                        record.set('DocumentUcStr_oid_saved', record.get('DocumentUcStr_oid'));
                        record.set('DocumentUcStr_Count_saved', record.get('DocumentUcStr_Count'));
                        record.commit();
                    }
                });
            },
			editGrid: function (action, options) {
				if (action == null)	action = 'add';

                //во избежание ввода спецификации до заполнения шапки документа
                if (action != 'view' && !wnd.form.isValid()) {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            wnd.findById('NewDocumentUcEditForm').getFirstInvalidEl().focus(true);
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
					if ( record_count == 1 && !store.getAt(0).get('DocumentUcStr_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					params.DocumentUc_id = wnd.form.findField('DocumentUc_id').getValue();
					params.DocumentUc_pid = wnd.form.findField('DocumentUc_pid').getValue();
					params.DocumentUc_didDate = wnd.form.findField('DocumentUc_didDate').getValue();
					params.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
					params.Contragent_sid = wnd.form.findField('Contragent_sid').getValue();
					params.Storage_sid = wnd.form.findField('Storage_sid').getValue();
					params.StorageZone_sid = wnd.form.findField('StorageZone_sid').getValue();
					params.Contragent_tid = wnd.form.findField('Contragent_tid').getValue();
					params.Storage_tid = wnd.form.findField('Storage_tid').getValue();
					params.StorageZone_tid = wnd.form.findField('StorageZone_tid').getValue();
					params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
					params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();

					if (options && options.copy_data) {
						Ext.apply(params, options.copy_data);
					}

					getWnd(view_frame.editformclassname).show({
						owner: view_frame,
						action: action,
						params: params,
						DrugDocumentType_Code: wnd.DrugDocumentType_Code,
						DrugDocumentStatus_Code: wnd.form.findField('DrugDocumentStatus_Code').getValue(),
						isAptMu: wnd.isAptMu,
						onSave: function(data) {
							if (data.RecordForMerge_id && data.RecordForMerge_id > 0) {
								view_frame.updateRecordById(data.RecordForMerge_id, data);
							} else {
								if ( record_count == 1 && !store.getAt(0).get('DocumentUcStr_id') ) {
									view_frame.removeAll({ addEmptyRecord: false });
								}
								var record = new Ext.data.Record.create(view_frame.jsonData['store']);
								view_frame.clearFilter();
								data.DocumentUcStr_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
								data.state = 'add';
								store.insert(record_count, new record(data));
								view_frame.setFilter();
								view_frame.updateSumm();
								view_frame.initActionPrint();

								wnd.refreshStorageWorkColumnVisibility();
							}
							Ext.getCmp('NewDocumentUcEditWindow').isChanged = true;
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record && selected_record.get('DocumentUcStr_id') > 0) {
						var params = selected_record.data;
						params.DocumentUc_id = wnd.form.findField('DocumentUc_id').getValue();
						params.DocumentUc_pid = wnd.form.findField('DocumentUc_pid').getValue();
						params.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
						params.Contragent_sid = wnd.form.findField('Contragent_sid').getValue();
						params.Storage_sid = wnd.form.findField('Storage_sid').getValue();
						params.StorageZone_sid = wnd.form.findField('StorageZone_sid').getValue();
						params.Contragent_tid = wnd.form.findField('Contragent_tid').getValue();
						params.Storage_tid = wnd.form.findField('Storage_tid').getValue();
						params.StorageZone_tid = wnd.form.findField('StorageZone_tid').getValue();
						params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
						params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();

						getWnd(view_frame.editformclassname).show({
							owner: view_frame,
							action: action,
							params: params,
							DrugDocumentType_Code: wnd.DrugDocumentType_Code,
							DrugDocumentStatus_Code: wnd.form.findField('DrugDocumentStatus_Code').getValue(),
							isAptMu: wnd.isAptMu,
							onSave: function(data) {
								if (data.RecordForMerge_id && data.RecordForMerge_id > 0) {
									view_frame.updateRecordById(data.RecordForMerge_id, data);
								} else {
									view_frame.updateRecordById(selected_record.get('DocumentUcStr_id'), data);
								}
								Ext.getCmp('NewDocumentUcEditWindow').isChanged = true;
								wnd.refreshStorageWorkColumnVisibility();
							}
						});
					}
				}
			},
			copyRecord: function() {
				var view_frame = this;
				var selection_model = view_frame.getGrid().getSelectionModel();
				var selected_record = selection_model.getSelected();

				if (selected_record && selected_record.get('DocumentUcStr_id') > 0) {
					var copy_data = new Object();
					Ext.apply(copy_data, selected_record.data);
					copy_data.DocumentUcStr_id = null;

					this.editGrid('add', {
						copy_data: copy_data
					});

					/*var store = view_frame.getGrid().getStore();
					var id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					var record = selected_record.copy(id);

					record.set('state', 'add');
					record.commit();

					view_frame.clearFilter();
					store.insert(store.getCount(), record);
					selection_model.selectRow(store.indexOf(record));
					view_frame.setFilter();
					view_frame.updateSumm();

					wnd.StrGrid.ViewActions.action_edit.execute();*/
                    Ext.getCmp('NewDocumentUcEditWindow').isChanged = true;
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
				Ext.getCmp('NewDocumentUcEditWindow').isChanged = true;
			},
			addRecords: function(data_arr){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var record_count = store.getCount();
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);

				if ( record_count == 1 && !store.getAt(0).get('DocumentUcStr_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
					record_count = 0;
				}

				view_frame.clearFilter();
				for (var i = 0; i < data_arr.length; i++) {
					if (data_arr[i].PrepSeries_GodnDate && data_arr[i].PrepSeries_GodnDate.indexOf('.') > -1) {
						data_arr[i].PrepSeries_GodnDate = Date.parseDate(data_arr[i].PrepSeries_GodnDate, 'd.m.Y');
					}
					data_arr[i].DocumentUcStr_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					data_arr[i].state = 'add';
					store.insert(record_count, new record(data_arr[i]));
				}
				view_frame.setFilter();
				view_frame.updateSumm();
				Ext.getCmp('NewDocumentUcEditWindow').isChanged = true;
			},
            addRecordByBarCode: function(bar_code_data, mode) { //функция обработки штрих-кода. mode (режим): 'test_str' - тестовый код длинной 5 символов, 'str' - код длинной 27 символов, 'scanner_data' - данные со сканера расшифрованные сервисом
                var view_frame = this;
                var bar_code = null;
                var storage_id = wnd.form.findField('Storage_sid').getValue();

                if (Ext.isEmpty(storage_id)) {
                    Ext.Msg.alert(langs('Ошибка'), 'Не выбран склад поставщика');
                    return false;
                }

                switch(mode) {
                    case 'test_str':
                    case 'str':
                        bar_code = bar_code_data;
                        break
                    case 'scanner_data':
                        bar_code = !Ext.isEmpty(bar_code_data.indSerNum) ? bar_code_data.indSerNum : null;
                        break
                }

                if (!Ext.isEmpty(bar_code) && !Ext.isEmpty(storage_id)) {
                    Ext.Ajax.request({
                        params: {
                            DrugPackageBarCode_BarCode: bar_code,
                            Storage_id: storage_id,
                            mode: 'DocumentUcStrList'
                        },
                        url: '/?c=DocumentUc&m=getDocumentUcStrDataByBarCode',
                        callback: function(options, success, response) {
                            if (response.responseText != '') {
                                var response_obj = Ext.util.JSON.decode(response.responseText);

                                //если овтет пришел пустой, выдаем сообщение что код не найден
								if (response_obj.length == 0) {
                                    Ext.Msg.alert(langs('Ошибка'), langs('Упаковка с таким штрих-кодом не найдена на остатках склада'));
								}

                                for(var i = 0; i < response_obj.length; i++) {
                                    //сбор данных о штрих коде
                                    var bar_code_obj = new Object({
                                        DrugPackageBarCode_id: response_obj[i].DrugPackageBarCode_id,
                                        DrugPackageBarCode_BarCode: response_obj[i].DrugPackageBarCode_BarCode,
                                        DrugPackageBarCodeType_id: response_obj[i].DrugPackageBarCodeType_id,
                                        DrugPackageBarCode_GTIN: response_obj[i].DrugPackageBarCode_GTIN,
                                        DrugPackageBarCode_SeriesNum: response_obj[i].DrugPackageBarCode_SeriesNum,
                                        DrugPackageBarCode_expDT: response_obj[i].DrugPackageBarCode_expDT,
                                        DrugPackageBarCode_TNVED: response_obj[i].DrugPackageBarCode_TNVED,
                                        DrugPackageBarCode_FactNum: response_obj[i].DrugPackageBarCode_FactNum,
                                        state: 'add'
                                    });

                                    //удаление данных кода из ответа
                                    delete response_obj[i].DrugPackageBarCode_id;
                                    delete response_obj[i].DrugPackageBarCode_BarCode;
                                    delete response_obj[i].DrugPackageBarCodeType_id;
                                    delete response_obj[i].DrugPackageBarCode_GTIN;
                                    delete response_obj[i].DrugPackageBarCode_SeriesNum;
                                    delete response_obj[i].DrugPackageBarCode_expDT;
                                    delete response_obj[i].DrugPackageBarCode_TNVED;
                                    delete response_obj[i].DrugPackageBarCode_FactNum;

                                    var idx = view_frame.getGrid().getStore().findBy(function(rec) { return rec.get('DocumentUcStr_oid') == response_obj[i].DocumentUcStr_oid; });
                                    if (idx >= 0) { //не нужно повторно добавлять строку
                                        var rec = view_frame.getGrid().getStore().getAt(idx);

                                        if (!Array.isArray(rec.data.BarCodeChangedData)) {
                                            rec.data.BarCodeChangedData = new Array();
                                        }

                                        //проверяем нет ли нашего кода уже в массиве. если нет, то добавляем
                                        var bar_code_exists = false;
                                        for (var ii = 0; ii < rec.data.BarCodeChangedData.length; ii++) {
                                            if (rec.data.BarCodeChangedData[ii].DrugPackageBarCode_BarCode == bar_code_obj.DrugPackageBarCode_BarCode) {
                                                bar_code_exists = true;
                                                break;
                                            }
                                        }
                                        if (!bar_code_exists) {
                                            rec.data.BarCodeChangedData.push(bar_code_obj);

                                            //пересчет количеств
                                            rec.set('DocumentUcStr_Count', rec.get('DocumentUcStr_Count')*1+response_obj[i].DocumentUcStr_Count*1);
                                            if (!Ext.isEmpty(response_obj[i].DocumentUcStr_EdCount)) {
                                                rec.set('DocumentUcStr_EdCount', rec.get('DocumentUcStr_EdCount')*1+response_obj[i].DocumentUcStr_EdCount*1);
                                            }
                                            rec.set('DocumentUcStr_Sum', rec.get('DocumentUcStr_Sum')*1+response_obj[i].DocumentUcStr_Sum*1);
                                            rec.set('DocumentUcStr_SumNds', rec.get('DocumentUcStr_SumNds')*1+response_obj[i].DocumentUcStr_SumNds*1);
                                            rec.set('DocumentUcStr_NdsSum', rec.get('DocumentUcStr_NdsSum')*1+response_obj[i].DocumentUcStr_NdsSum*1);
                                            rec.set('BarCode_Count', rec.get('BarCode_Count')*1+1);
                                            if (rec.get('state') != 'add') {
                                                rec.set('state', 'edit');
                                            }
                                            rec.commit();
                                        }

                                        //удаляем данную строку, чтобы она не добавилась как новая
                                        response_obj.splice(i,1);
                                    } else {
                                        response_obj[i].state = 'add';
                                        response_obj[i].BarCodeChangedData = new Array();
                                        response_obj[i].BarCodeChangedData.push(bar_code_obj);
                                    }
                                }
                                view_frame.addRecords(response_obj);
                            }
                        }
                    });
                }
            },
			updateRecordById: function(record_id, data) {
				var index = this.getGrid().getStore().findBy(function(rec) { return rec.get('DocumentUcStr_id') == record_id; });
				if (index == -1) {
					return false;
				}
				var record = this.getGrid().getStore().getAt(index);

				for(var key in data) {
					if (key != 'DocumentUcStr_id') {
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
				var summ_nds = 0;
				var post_summ = 0;
				var nds_summ = 0;

				var summ_nds_field = wnd.inf_form.findField('TotalSumNds');
				var nds_summ_field = wnd.inf_form.findField('TotalNdsSum');

                var summ_before_field = wnd.inf_form.findField('TotalBeforeNdsSum');
                var summ_after_field = wnd.inf_form.findField('TotalAfterNdsSum');
                var summ_result_field = wnd.inf_form.findField('TotalResultNdsSum');

				this.getGrid().getStore().each(function(record) {
					if(record.get('DocumentUcStr_SumNds') && record.get('DocumentUcStr_SumNds') > 0) {
						summ_nds += (record.get('DocumentUcStr_SumNds') * 1);
					}
					if(record.get('DocumentUcStr_NdsSum') && record.get('DocumentUcStr_NdsSum') > 0) {
						nds_summ += (record.get('DocumentUcStr_NdsSum') * 1);
					}
					if(record.get('PostDocumentUcStr_Sum') && record.get('PostDocumentUcStr_Sum') > 0) {
						post_summ += (record.get('PostDocumentUcStr_Sum') * 1);
					}
				});

				summ_nds_field.setValue(summ_nds.toFixed(2));
				nds_summ_field.setValue(nds_summ.toFixed(2));

                summ_before_field.setValue(nds_summ.toFixed(2));
                summ_after_field.setValue(post_summ.toFixed(2));
                summ_result_field.setValue((post_summ-nds_summ).toFixed(2));

                if (summ_nds <= 0 || wnd.isAptMu) {
                    summ_nds_field.hideContainer();
                } else {
                    summ_nds_field.showContainer();
                }
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (!Ext.isEmpty(record.get('Drug_id')) && (record.get('state') == 'add' || record.get('state') == 'edit' ||  record.get('state') == 'delete')) {
						var item = record.data;
						item.FileData = null;
						// Создаем копию объекта, чтобы при изменении типа в PrepSeries_GodnDate с date на string в гриде не появилось NaN.NaN.NaN вместо даты
						var copy = item.constructor();
					    for (var attr in item) {
					        if (item.hasOwnProperty(attr)) copy[attr] = item[attr];
					    }
						if(!Ext.isEmpty(copy.PrepSeries_GodnDate)){
							copy.PrepSeries_GodnDate = Ext.util.Format.date(copy.PrepSeries_GodnDate, 'd.m.Y');
						}
						data.push(copy);
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
			}/*,
            hideEdFieldsForOnlyUpak: function() { //при учете в упаковках, проверяется содержимое грида, если ед. списания во всех значениях пусты, или соответствуюи значению "упаковка", то поля группы "ед. списания" скрываются
                if (wnd.DrugAccountingType == 'pack') {
                    var only_upak = true;
                    this.getGrid().getStore().each(function(record) {
                        if (!Ext.isEmpty(record.get('GoodsUnit_Name')) && record.get('GoodsUnit_Name') != 'упаковка' && record.get('state') != 'delete') {
                            only_upak = false;
                            return false;
                        }
                    });
                    if (only_upak) {
                        this.setColumnHidden('GoodsUnit_Name', true);
                        this.setColumnHidden('DocumentUcStr_EdCount', true);
                        this.setColumnHidden('DocumentUcStr_EdPrice', true);
                    } else {
                        this.setColumnHidden('GoodsUnit_Name', false);
                        this.setColumnHidden('DocumentUcStr_EdCount', false);
                        this.setColumnHidden('DocumentUcStr_EdPrice', false);
                    }
                }
            }*/
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
				id: 'NewDocumentUcEditInfForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 160,
				collapsible: true,
				items: [{
					layout: 'column',
					labelAlign: 'right',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'textfield',
							fieldLabel: langs('Сумма по документу'),
							name: 'TotalNdsSum',
							width: 200,
							disabled: true
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfield',
							fieldLabel: langs('в т.ч. НДС'),
							name: 'TotalSumNds',
							width: 200,
							disabled: true
						}]
					}]
				}, {
                    layout: 'column',
                    labelAlign: 'right',
                    labelWidth: 91,
                    items: [{
                        layout: 'form',
                        labelWidth: 160,
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: langs('Сумма')+': '+langs('до операции'),
                            name: 'TotalBeforeNdsSum',
                            width: 111,
                            disabled: true
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: langs('после'),
                            name: 'TotalAfterNdsSum',
                            width: 111,
                            disabled: true
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: langs('Результат'),
                            name: 'TotalResultNdsSum',
                            width: 111,
                            disabled: true
                        }]
                    }]
                }, {
					xtype: 'hidden',
					fieldLabel: 'Note_id',
					name: 'Note_id'
				}, {
					layout: 'form',
					labelAlign: 'right',
					items: [new Ext.form.TriggerField({
						fieldLabel: langs('Примечание'),
						name: 'Note_Text',
						width: 525,
						'triggerClass': 'x-form-clear-trigger',
						'onTriggerClick': function(e) {
							this.setValue(null);
						}
					})]
				}]
			}]
		});

		this.executeButton = new Ext.Button({
			handler: function() {
				this.ownerCt.doExecute();
			},
			iconCls: 'ok16',
			text: langs('Исполнить')
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
			buttons:
			[
			this.buttonSave,
			this.executeButton, {
				handler: function() {
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				text: langs('Печать')
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
                {
                    xtype: 'panel',
                    title: ' ',
                    autoHeight: true,
                    collapsible: true,
                    region: 'north',
                    layout: 'fit',
                    items: [
                        form
                    ]
                }, {
                    xtype: 'panel',
                    title: langs('Медикаменты'),
                    region: 'center',
                    layout: 'border',
                    items: [
                        this.BarCodeInputPanel,
                        this.StrGrid
                    ]
                },
				wnd.inf_form_panel
			]
		});
		sw.Promed.swNewDocumentUcEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('NewDocumentUcEditForm').getForm();
		this.inf_form = this.findById('NewDocumentUcEditInfForm').getForm();
    }
});