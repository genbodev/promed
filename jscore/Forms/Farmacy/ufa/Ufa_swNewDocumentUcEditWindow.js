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

var $isImport = false;
var $isRegionUfa = getGlobalOptions().region.nick == 'ufa';

sw.Promed.swNewDocumentUcEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Редактирование',
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
		}
	},
	onHide: Ext.emptyFn,
	isSmpMainStorage: false, //Флаг создания документа из центрального склада СМП
	
	checkEmptyLpu: function() { //проверяет наличие в спецификации позиций без привязки к ЛПУ
		var result = true;
		this.StrGrid.getGrid().getStore().each(function(record){
			var lpu = record.get('Lpu_Nick');
			if (record.get('DocumentUcStr_id') > 0 && (!lpu || lpu == null || lpu == '')) {
				result = false;
				return false;
			}
		});
		return result;
	},
	
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
			if (confirm('Не для всех медикаментов указана серия. Продолжить?')) {
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
		wnd.getLoadMask('Подождите, идет сохранение...').show();
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
		//$Contragent_sid = wnd.form.findField('Contragent_sid')
		//console.log('Contragent_sid'); console.log(wnd.form.findField('Contragent_sid').store.data.items [0].data.Org_id); 
		params.Org_id = wnd.form.findField('Contragent_sid').store.data.items [0].data.Org_id
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
			if (this.DrugDocumentType_Code.inlist([2,10,15])) {
				params.Contragent_id = params.Contragent_sid;
			} else if (this.DrugDocumentType_Code.inlist([3,6])) {
				params.Contragent_id = params.Contragent_tid;
			}
		}
                console.log('params');
                console.log(params);
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
				if (action.result && action.result.DocumentUc_id > 0) {
					wnd.DocumentUc_id = action.result.DocumentUc_id;
					if (typeof options.callback == 'function' ) {
						options.callback();
					}
				}
				if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.DocumentUc_id);
				}
			}
		});
	},
	doExecute: function() {
		var wnd = this;
                var $lpu = '';
                var $flag = false
                var $data = Ext.getCmp('ndueDocumentUcStrGrid').getGrid().getSelectionModel().grid.store.data.items
                
                console.log($data);
                if ($isRegionUfa) {
                    for(var k in $data){
                        //console.log($data[k].data);
                        if (typeof $data[k] == "object") {
                            
                        if($data[k].data.Storage_ctrl == '0'){
                            // console.log('000');
                            $lpu = $data[k].data.Lpu_Nick;
                            console.log($data[k].data);
                            $flag = true;
                            //var a = obj[k];
                            break;
                        } 
                    }
                }
                     
                    if ($flag && 1 != 1) {
                        {
                            sw.swMsg.alert('Внимание', $lpu == '' ? 'Исполнение документа невозможно!<br/>Необходимо закрепить склад аптеки за МО "' + $lpu + '"!' : 'Исполнение документа невозможно! <br/>Необходимо закрепить склад аптеки за МО');
                            return false;
                        }
                    }
                }
              
                
		if (confirm('После исполнения, редактирование документа станет недоступно. Продолжить?')) {
			var tf = Ext.getCmp('DocumentUcEditWindow');
			this.doSave({
				callback: function() {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (success && response_obj.success) {
									wnd.setDisabled(true);
									alert('Документ успешно исполнен');
								} else {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
								}
							}
							if (wnd.owner && wnd.owner.refreshRecords) {
								wnd.owner.refreshRecords(null,0);
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
               console.log(this.print_report_data);
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
	createDocumentUcStrList: function() {
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
			sw.swMsg.alert('Ошибка', 'Для выполнения действия необходимо указать контракт');
		}
	},
	importDocumentUcStr: function() {
		var wnd = this;

		var WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
		if (Ext.isEmpty(WhsDocumentUc_id)) {
			sw.swMsg.alert('Ошибка', 'Должен быть указан контракт');
			return false;
		}

		var formParams = {};
		formParams.DocumentUc_setDate = wnd.form.findField('DocumentUc_setDate').getValue() ? wnd.form.findField('DocumentUc_setDate').getValue().dateFormat('d.m.Y') : '';
		formParams.DocumentUc_didDate = wnd.form.findField('DocumentUc_didDate').getValue() ? wnd.form.findField('DocumentUc_didDate').getValue().dateFormat('d.m.Y') : '';
		formParams.DocumentUc_InvoiceNum = wnd.form.findField('DocumentUc_InvoiceNum').getValue();
		formParams.DocumentUc_InvoiceDate = wnd.form.findField('DocumentUc_InvoiceDate').getValue() ? wnd.form.findField('DocumentUc_InvoiceDate').getValue().dateFormat('d.m.Y') : '';
		formParams.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
		formParams.Storage_tid = wnd.form.findField('Storage_tid').getValue();
		formParams.Mol_tid = wnd.form.findField('Mol_tid').getValue();
		formParams.Note_id = wnd.inf_form.findField('Note_id').getValue();
		formParams.Note_Text = wnd.inf_form.findField('Note_Text').getValue();

		getWnd('swDokNakImportWindow').show({
			formParams: formParams,
			callback: function(data) {
				if (data && !Ext.isEmpty(data.DocumentUc_id)) {
					var loadMask = new Ext.LoadMask(wnd.form.getEl(), {msg:'Загрузка...'});
					loadMask.show();
					wnd.DocumentUc_id = data.DocumentUc_id;
					wnd.action = 'edit';
					wnd.loadForm(function(){loadMask.hide()});
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

		if (object_name != 'Contragent' && object_name != 'Storage') {
			return false;
		}
		if (set_fields) {
			wnd.form.findField('Mol_'+object_type+'id').setValue(null);
			wnd.form.findField('Mol_'+object_type+'Person').setValue(null);
		}
		
		if (wnd.MolData[object_type][object_name].id == field_value) {
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
			params.callback = function(mol_data) {
				if (!mol_data.length) {
					return false
				}
				var mol_combo = wnd.form.findField('Mol_'+object_type+'id_combo');
				mol_combo.getStore().loadData(mol_data);
				mol_combo.setValue(mol_combo.getValue());
				mol_data = mol_data[0];
				wnd.MolData[object_type][object_name].id = field_value;
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
			params.isSmpMainStorage = (wnd.isSmpMainStorage)?1:0;
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
	setDefaultValues: function () { //заполнение формы значениями "по умолчанию"
		var current_date = new Date();

		this.form.findField('DrugDocumentStatus_Code').setValue(1);
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
                //var field_arr = ['WhsDocumentUc_id', 'Contragent_sid', 'Storage_sid', 'Storage_tid'];
		for (var i=0; i<field_arr.length; i++) {
			if (this.FormParams[field_arr[i]] && this.FormParams[field_arr[i]] > 0) {
				this.form.findField(field_arr[i]).setValueById(this.FormParams[field_arr[i]]);
			} else {
				this.form.findField(field_arr[i]).setValueById(null);
			}
		}
		
//                 var $formParams = this.FormParams;
//                 if ($formParams.ContragentTidType_code > 0) { 
//                    //  Находим поставщика РАС
//                    var $combo = this.form.findField('Contragent_tid') 
//                    var $ContragentType_code = $formParams.ContragentTidType_code
//                    
//                    var $index = $combo.getStore().findBy(function(rec) { return rec.get('ContragentType_id') == $ContragentType_code; });
//                    alert($index);
//                    if ($index > -1) {
//                        var record = $combo.getStore().getAt($index).data;
//                        alert(record.Contragent_id);
//                        $combo.setValue(record.Contragent_id)
//                    }
//                }
                                
		if (this.isSmpMainStorage) {
			
			
			//Устанавливаем в Contragent_#id службу центрального склада СМП
			
			if (this.DrugDocumentType_Code == 6) {
				this.setContragentFieldCurrentMedService('Contragent_tid');
			}
			
			if (this.DrugDocumentType_Code == 15) {
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
					Ext.Msg.alert('Сообщение', response_obj[0].Error_Msg || 'Ошибка получения склада текущей службы');
					return false;
				}

				var field = win.form.findField( contragent_fieldname );

				if (field && (typeof field.setValue == 'function') ) {
					field.setValue( response_obj[0].Contragent_id );
				}

			}
		});
		
	},
	setDrugDocumentType: function(type_code) { //настройка внешнего вида формы в зависимости от типа документа
		
		var form = this;
		var num_fl = 'Документ №';
		var doc_name = 'Документ';
		var allowed_actions = new Array(); //список доступных пунктов в меню действия
		var current_date = new Date();
		var msf_store = sw.Promed.MedStaffFactByUser.store;
		var MedService_id = null;

		if (!type_code) {
			type_code = this.DrugDocumentType_Code;
		} else {
			this.DrugDocumentType_Code = type_code;
		}

		//значения по умолчанию
		this.print_report_data = null;
		this.num_generating_enabled = true;

		this.form.enable_blocked = false;
		this.StrGrid.enable_blocked = false;

		this.setFieldLabel(this.form.findField('Contragent_sid'), 'Поставщик');
		this.setFieldLabel(this.form.findField('Contragent_tid'), 'Получатель');
		this.setFieldLabel(this.form.findField('Storage_tid'), 'Склад получателя');
		this.setFieldLabel(this.form.findField('Mol_tPerson'), 'МОЛ получателя');
		this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'от');
		this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата поставки');
		this.setFieldLabel(this.form.findField('DocumentUc_pid'), 'Родительский документ');
		this.setFieldLabel(this.form.findField('DocumentUc_InvoiceNum'), 'Счет-фактура №');
		this.setFieldLabel(this.form.findField('DocumentUc_InvoiceDate'), 'от');
		this.form.findField('DrugFinance_id').enable_blocked = true;
        this.form.findField('DocumentUc_Num').enable_blocked = false;
        this.form.findField('DocumentUc_setDate').enable_blocked = false;
        this.form.findField('WhsDocumentUc_id').enable_blocked = false;
        this.form.findField('DocumentUc_InvoiceNum').enable_blocked = false;
        this.form.findField('DocumentUc_InvoiceDate').enable_blocked = false;
		this.form.findField('DrugFinance_id').enable_blocked = false;
		this.form.findField('WhsDocumentCostItemType_id').enable_blocked = false;
		this.form.findField('Contragent_sid').enable_blocked = false;
		this.form.findField('Contragent_tid').enable_blocked = false;
		this.form.findField('Storage_tid').enable_blocked = false;
		this.form.findField('DocumentUc_InvoiceNum').ownerCt.hide();
		this.form.findField('DocumentUc_InvoiceDate').ownerCt.hide();
		this.form.findField('Contragent_sid').ownerCt.show();
		this.form.findField('Contragent_tid').ownerCt.show();
		this.form.findField('Storage_sid').ownerCt.show();
		this.form.findField('Storage_tid').ownerCt.show();
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
		this.form.findField('WhsDocumentUc_id').ownerCt.show();
		this.form.findField('EvnRecept_Name').ownerCt.hide();
		this.form.findField('DocumentUc_pid').ownerCt.hide();
		this.form.findField('WhsDocumentUc_id').allowBlank = true;
		this.form.findField('Contragent_sid').allowBlank = false;
		this.form.findField('Contragent_tid').allowBlank = false;
		this.form.findField('Storage_sid').allowBlank = true;
		this.form.findField('Storage_tid').allowBlank = true;
		this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=DocumentUc&m=loadStorage2LpuList';
		this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=DocumentUc&m=loadStorageList';
		this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentType_Code = null;
		this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentStatusType_Code = 2; //Действующий ГК
		this.form.findField('Storage_sid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_sid').getStore().baseParams.MedService_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.Org_id = null;
		this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = null;
		this.form.findField('Storage_tid').getStore().baseParams.MedService_id = null;
		this.form.findField('Contragent_tid').getStore().baseParams.ExpDate = null;
		this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = null;
		this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = null;
		this.form.findField('Contragent_sid').getStore().baseParams.ExpDate = null;
		this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = null;
		this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = null;
		this.StrGrid.setColumnHeader('DocumentUcStr_Reason', 'Причина');
		this.StrGrid.setColumnHidden('DocumentUcStr_oName', false);
		this.StrGrid.setColumnHidden('DrugShipment_Name', true);
		this.StrGrid.setColumnHidden('DocumentUcStr_Reason', true);
		this.StrGrid.setColumnHidden('DocumentUcStr_IsNDS', false);
		this.StrGrid.setColumnHidden('DrugNds_Code', false);
		this.StrGrid.setColumnHidden('DocumentUcStr_SumNds', false);
		this.StrGrid.setColumnHidden('DocumentUcStr_NdsSum', false);
		this.StrGrid.getAction('action_import').hide();
		
		this.form.findField('EmergencyTeam_id').ownerCt.hide();
		this.form.findField('EmergencyTeam_id').allowBlank = true;

		this.inf_form.findField('TotalSumNds').showContainer();
		this.inf_form_panel.hide();
		
		this.executeButton.show();
		this.buttonSave.show();
		
		//проверка перед сохранением по умолчанию
		this.checkDataBeforeSave = function() {
			return true;
		};
		//console.log('type_code'); console.log(type_code);
		
		// По умолчанию
		this.form.findField('Storage_tid').enable_blocked = true;
		this.form.findField('Storage_tid').ownerCt.hide();
		
		this.form.findField('Mol_sPerson').ownerCt.hide();
		this.form.findField('Mol_tPerson').ownerCt.hide();
                
		switch(type_code) {
			case 2: //Документ списания
				doc_name = 'Документ списания';
				this.print_report_data = [{
					report_label: lang['torg-16'],
					report_file: 'Torg16.rptdesign',
					report_format: 'pdf'
				}, {
					report_label: lang['sborochniy_list'],
					report_file: 'DocumentUcPick.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Contragent_sid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_sid'), 'Склад');
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.StrGrid.setColumnHeader('DocumentUcStr_Reason', 'Причина списания');
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

				break;
			case 3: //Документ ввода остатков
				doc_name = 'Документ ввода остатков';
				this.print_report_data = [{
					report_label: 'Постеллажные карточки',
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['vedomost_priema_tovarov'],
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['list_razmescheniya'],
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Contragent_tid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_tid'), 'Склад');
				this.form.findField('Contragent_tid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentType_Code = 18; //Контракт ввода остатков
				this.form.findField('WhsDocumentUc_id').getStore().baseParams.WhsDocumentStatusType_Code = null;
				this.form.findField('Contragent_sid').ownerCt.hide();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('WhsDocumentUc_id').allowBlank = false;
				this.form.findField('Contragent_sid').allowBlank = true;
				this.form.findField('Storage_tid').allowBlank = false;
				this.StrGrid.setColumnHidden('DocumentUcStr_oName', true);
				this.StrGrid.setColumnHidden('DrugShipment_Name', false);

				if (this.isAptMu) {
					this.form.findField('Contragent_tid').enable_blocked = false;
					this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}

				break;
			case 6: //Приходная накладная
				doc_name = 'Приходная накладная';
				num_fl = 'Накладная №';
				allowed_actions = ['create_by_contract', 'copy_row'];
				this.print_report_data = [{
					report_label: 'ТОРГ-1',
					report_file: 'TORG1.rptdesign'
				}, {
					report_label: 'Акт о приеме медикаментов',
					report_file: 'Torg1_Med.rptdesign'
				}, {
					report_label: 'Доверенность',
					report_file: 'Doveren.rptdesign',
					report_format: 'xls'
				}, {
					report_label: 'Постеллажные карточки',
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['vedomost_priema_tovarov'],
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['list_razmescheniya'],
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.num_generating_enabled = false;
				this.setFieldLabel(this.form.findField('Storage_tid'), 'Склад');
				this.setFieldLabel(this.form.findField('Mol_tPerson'), 'МОЛ');
				this.form.findField('Contragent_tid').enable_blocked = true;
				this.form.findField('Storage_tid').allowBlank = false;
				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.StrGrid.setColumnHidden('DocumentUcStr_oName', true);
				this.StrGrid.setColumnHidden('DrugShipment_Name', false);
				this.inf_form_panel.show();
				this.StrGrid.getAction('action_import').setHidden(this.action != 'add' || getRegionNick() != 'khak');
				this.StrGrid.getAction('action_ndue_actions').hide();  // Скрыл меню действия  Тагир

				if (this.isSmpMainStorage) {
					this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '7'; //7 - служба
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
					this.form.findField('Storage_tid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpMainStorageList';
					this.form.findField('Mol_tPerson').ownerCt.hide();
					this.form.findField('Mol_tid_combo').allowBlank = true;
					this.form.findField('Mol_tid_combo').ownerCt.show();
					this.form.findField('Mol_tid_combo').enable();
				} else if (this.isAptMu) {
					this.form.findField('Contragent_tid').enable_blocked = false;
					this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_tid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_tid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}

				/*
                                        if (getGlobalOptions().region.nick == 'ufa' && $isImport) {
					allowed_actions = new Array();

					this.StrGrid.enable_blocked = true;

					this.form.findField('DocumentUc_Num').enable_blocked = true;
					this.form.findField('DocumentUc_setDate').enable_blocked = true;
					this.form.findField('DocumentUc_InvoiceNum').enable_blocked = true;
					this.form.findField('DocumentUc_InvoiceDate').enable_blocked = true;
					this.form.findField('Contragent_sid').enable_blocked = true;
					this.document_combo.enable_blocked = true;
				}
                                */

				break;
			case 10: //Расходная накладная
				doc_name = 'Расходная накладная';
				num_fl = 'Накладная №';
				allowed_actions = ['copy_row'];
				this.print_report_data = [{
					report_label: 'ТОРГ-12',
					report_file: 'TORG12.rptdesign'
				}, {
					report_label: 'Реестр сертификатов',
					report_file: 'dlo_DocumentUcStrReestrt_List.rptdesign'
				}, {
					report_label: 'Упаковочный лист',
					report_file: 'DocumentUcPack.rptdesign'
				}, {
					report_label: 'Акт приема-передачи',
					report_file: 'AktPriemaPeredacha.rptdesign'
				}, {
					report_label: lang['sborochniy_list'],
					report_file: 'DocumentUcPick.rptdesign',
					report_format: 'doc'
				}];
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('Storage_tid').enable_blocked = true;
				this.form.findField('Storage_sid').allowBlank = false;
				this.form.findField('Contragent_tid').getStore().baseParams.ExpDate = current_date.format('d.m.Y');
				this.form.findField('Contragent_tid').getStore().baseParams.ContragentType_CodeList = '1,3,5,6'; //1 - Организация; 3 - Аптека; 5 - Аптека МУ; 6 - Региональный склад;

				if (this.isAptMu) {
					this.form.findField('Contragent_sid').enable_blocked = false;
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;
				} else {
					//this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').ownerCt.hide();
					this.form.findField('Storage_sid').allowBlank = true;
				}

				this.inf_form_panel.show();
				break;
			case 11: //Документ реализации
				doc_name = 'Документ реализации';
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Contragent_sid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_sid'), 'Склад');
				this.form.findField('EvnRecept_Name').ownerCt.show();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
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
				doc_name = 'Документ оприходования';
				this.print_report_data = [{
					report_label: 'Доверенность',
					report_file: 'Doveren.rptdesign',
					report_format: 'xls'
				}, {
					report_label: 'Постеллажные карточки',
					report_file: 'Posstelag.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['vedomost_priema_tovarov'],
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['list_razmescheniya'],
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('WhsDocumentUc_id'), 'Основание');
				this.setFieldLabel(this.form.findField('Contragent_sid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_sid'), 'Склад');
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_sid').ownerCt.hide();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_sid').allowBlank = true;

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_tid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();
				}
				break;
			case 15: //Накладная на внутреннее перемещение
				doc_name = 'Накладная на внутреннее перемещение';
				this.print_report_data = [{
					report_label: 'ТОРГ-13',
					report_file: 'Torg13.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['vedomost_priema_tovarov'],
					report_file: 'AcceptSheet.rptdesign',
					report_format: 'doc'
				}, {
					report_label: lang['list_razmescheniya'],
					report_file: 'WarehousingSheet.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Contragent_sid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_sid'), 'Передать со склада');
				this.setFieldLabel(this.form.findField('Mol_sPerson'), 'МОЛ');
				this.setFieldLabel(this.form.findField('Storage_tid'), 'На склад');
				this.setFieldLabel(this.form.findField('Mol_tPerson'), 'МОЛ');
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), 'Основание');
				this.form.findField('Contragent_sid').enable_blocked = true;
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('DocumentUc_pid').ownerCt.show();
				this.form.findField('Contragent_tid').allowBlank = true;
				this.form.findField('Storage_sid').allowBlank = true;
				this.form.findField('Storage_tid').allowBlank = false;
				this.checkDataBeforeSave = function() {
					if (this.form.findField('Storage_sid').getValue() == this.form.findField('Storage_tid').getValue()) {
						sw.swMsg.alert('Ошибка', 'Склад-поставщик должен быть не равен складу-получателю.');
						return false;
					}
					return true;
				};
				
				if (this.isSmpMainStorage) {
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '7'; //7 - служба
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
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '5'; //5 - аптка МУ
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = this.userMedStaffFact.Lpu_id;

					this.form.findField('Storage_sid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
					this.form.findField('Storage_sid').getStore().baseParams.MedService_id = this.userMedStaffFact.MedService_id;

					this.form.findField('Storage_tid').getStore().baseParams.Lpu_oid = this.userMedStaffFact.Lpu_id;
				} else {
					this.form.findField('Storage_sid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.form.findField('Storage_sid').getStore().baseParams.filterByOrgUser = !isOrgAdmin();

					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
				}
				
				this.form.findField('Storage_tid').enable_blocked = false;
				this.form.findField('Storage_tid').ownerCt.show();
								
				break;
			case 17: //Возвратная накладная (расходная)
				doc_name = 'Возвратная накладная (расходная)';
				this.print_report_data = [{
					report_label: 'Возвратная накладная',
					report_file: 'TorgNaklVozvr.rptdesign',
					report_format: 'doc'
				}];
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				//this.setFieldLabel(this.form.findField('Storage_sid'), 'Передать со склада');
                                this.form.findField('Storage_sid').ownerCt.hide();
                                Ext.getCmp('WhsDocumentUc_Num').ownerCt.hide();
				this.setFieldLabel(this.form.findField('Mol_sPerson'), 'МОЛ');
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), 'К приходной накладной №');
				this.form.findField('Contragent_sid').enable_blocked = true;
                                
				//this.form.findField('DrugFinance_id').enable_blocked = true;
				//this.form.findField('WhsDocumentCostItemType_id').enable_blocked = true;
                                
                                //this.form.findField('DrugFinance_id').ownerCt.hide();
				//this.form.findField('WhsDocumentCostItemType_id').ownerCt.hide();
                               
				this.form.findField('DocumentUc_pid').enable_blocked = true;
				//this.form.findField('DocumentUc_pid').ownerCt.show();
//				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('Storage_tid').allowBlank = false;

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
					this.FormParams.Org_id = this.form.findField('Storage_tid').getStore().baseParams.Org_id;
				}
                                

				break;
			case 18: //Возвратная накладная (приходная)
				doc_name = 'Возвратная накладная (приходная)';
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Storage_tid'), 'Передать на склад');
				this.setFieldLabel(this.form.findField('Mol_tPerson'), 'МОЛ');
				this.setFieldLabel(this.form.findField('DocumentUc_pid'), 'К приходной накладной №');
				this.form.enable_blocked = true;
				this.StrGrid.enable_blocked = true;
				this.form.findField('DocumentUc_pid').ownerCt.show();
				this.form.findField('Storage_sid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('DocumentUc_InvoiceNum').ownerCt.show();
				this.form.findField('DocumentUc_InvoiceDate').ownerCt.show();
				this.form.findField('Storage_tid').allowBlank = false;

				if (Ext.isEmpty(this.userMedStaffFact.Lpu_id)) {
					this.form.findField('Storage_tid').getStore().baseParams.Org_id = getGlobalOptions().org_id > 0 ? getGlobalOptions().org_id : null;
				}

				break;
			case 20:
				doc_name = 'Пополнение укладки со склада подстанции';

				this.form.findField('EmergencyTeam_id').ownerCt.show();
				this.form.findField('EmergencyTeam_id').allowBlank = false;

				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();

				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;

				this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '7'; //7 - служба
				this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
				this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';

				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tid_combo').allowBlank = false;
				this.form.findField('Mol_tid_combo').ownerCt.show();
				this.form.findField('Mol_tid_combo').enable();

				//this.executeButton.hide();

				break;
			case 21: // Списание медикаментов со склада на пациента
				doc_name = 'Списание медикаментов со склада на пациента';
				this.setFieldLabel(this.form.findField('DocumentUc_setDate'), 'Дата');
				this.setFieldLabel(this.form.findField('DocumentUc_didDate'), 'Дата исполнения');
				this.setFieldLabel(this.form.findField('Contragent_sid'), 'Организация');
				this.setFieldLabel(this.form.findField('Storage_sid'), 'Склад');
				this.form.findField('EvnRecept_Name').ownerCt.hide();
				this.form.findField('WhsDocumentUc_id').ownerCt.hide();
				this.form.findField('Contragent_tid').ownerCt.hide();
				this.form.findField('Storage_tid').ownerCt.hide();
				this.form.findField('Mol_sPerson').ownerCt.hide();
				this.form.findField('Mol_tPerson').ownerCt.hide();
				this.form.findField('Contragent_tid').allowBlank = true;

				if ( !this.isAptMu ) {
					// Загружаем только склад подстанции
					this.form.findField('Contragent_sid').getStore().baseParams.ContragentType_CodeList = '7'; //7 - служба
					this.form.findField('Contragent_sid').getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
					this.form.findField('Storage_sid').getStore().proxy.conn.url = '/?c=Storage&m=loadSmpSubStorageList';
				}

				this.buttonSave.hide();
			break;
		}

		if (getGlobalOptions().region.nick == 'ufa') {
			this.form.findField('DocumentUc_InvoiceNum').ownerCt.hide();
			this.form.findField('DocumentUc_InvoiceDate').ownerCt.hide();
			this.form.findField('DocumentUc_pid').ownerCt.hide();


			if ($isImport == false) {
				//var enable = type_code.inlist([6,17, 18]);
				var enable = type_code.inlist([6, 10, 15, 17]);
				if (enable == false) {
					$isImport = true;
				}
			}
			this.buttonSave.show();
			this.executeButton.show();

			if ( $isImport) {
				allowed_actions = new Array($isImport);

				this.StrGrid.enable_blocked = true;

				this.form.findField('DocumentUc_Num').enable_blocked = true;
				this.form.findField('DocumentUc_setDate').enable_blocked = true;
				this.form.findField('DocumentUc_InvoiceNum').enable_blocked = true;
				this.form.findField('DocumentUc_InvoiceDate').enable_blocked = true;
				this.form.findField('Contragent_sid').enable_blocked = true;
				//this.form.findField('DrugFinance_id').enable_blocked = true;
				this.form.findField('WhsDocumentCostItemType_id').enable_blocked = true;
				this.document_combo.enable_blocked = true;
				this.buttonSave.hide();
				if (this.action == 'add' || type_code == 18) {
					//  Если добавляется непредусмотренный документ из Уфы - то прячем кнопки

					this.executeButton.hide();

				}
			}
			/*
			this.form.findField('Storage_tid').enable_blocked = true;
			this.form.findField('Storage_tid').ownerCt.hide();
			*/
		}

		this.doLayout();

		if (this.isAptMu) {
			this.inf_form.findField('TotalSumNds').hideContainer();
			this.StrGrid.setColumnHidden('DocumentUcStr_IsNDS', true);
			this.StrGrid.setColumnHidden('DrugNds_Code', true);
			this.StrGrid.setColumnHidden('DocumentUcStr_SumNds', true);
			this.StrGrid.setColumnHidden('DocumentUcStr_NdsSum', true);
		}

		//натройка меню действий
		if (allowed_actions.length > 0 && 1 > 1) {
			 // Скрыл меню действия  Тагир
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
	},
	setDisabled: function(disable) {
		var wnd = this;

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
			'Contragent_tid',
			'Storage_tid',
			'Mol_tid',
			'DrugPeriodClose_DT'
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
			if (wnd.form.findField('DrugDocumentStatus_Code').getValue() == 1) {
				wnd.buttons[0].enable();
				wnd.buttons[1].enable();
			} else {
				wnd.buttons[0].disable();
				wnd.buttons[1].disable();
			}
			wnd.inf_form.findField('Note_Text').enable();
		}

		if (wnd.WhsDocumentUcInvent_id > 0) {
			wnd.StrGrid.enable_blocked = true;
		}

		wnd.StrGrid.setReadOnly(disable || wnd.StrGrid.enable_blocked);
		wnd.StrGrid.getAction('action_ndue_actions').setDisabled(disable || wnd.StrGrid.enable_blocked);
		wnd.StrGrid.getAction('action_import').setDisabled(disable || wnd.StrGrid.enable_blocked);
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
	loadForm: function(callback) {
		var wnd = this;
                
		Ext.Ajax.request({
			failure:function () {
				sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
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
                
				wnd.setDrugDocumentType(result[0].DrugDocumentType_Code);

				wnd.form.setValues(result[0]);
				wnd.form.findField('Mol_tid_combo').setValue(result[0].Mol_tid);
				wnd.form.findField('Mol_sid_combo').setValue(result[0].Mol_sid);
				wnd.inf_form.setValues(result[0]);
				wnd.document_combo.setValueById(result[0].WhsDocumentUc_id);
				wnd.drugdocument_combo.setValueById(result[0].DocumentUc_pid);
				if (result[0].Storage_tid > 0) wnd.form.findField('Storage_tid').setValueById(result[0].Storage_tid);
				if (result[0].Storage_sid > 0) wnd.form.findField('Storage_sid').setValueById(result[0].Storage_sid);
				if (result[0].Contragent_tid > 0) wnd.form.findField('Contragent_tid').setValueById(result[0].Contragent_tid);
				if (result[0].Contragent_sid > 0) wnd.form.findField('Contragent_sid').setValueById(result[0].Contragent_sid);
				if (result[0].WhsDocumentUcInvent_id > 0) wnd.WhsDocumentUcInvent_id = result[0].WhsDocumentUcInvent_id;
                                wnd.DocW = result[0].DocW;
				// Дата закрытия периода
				wnd.DrugPeriodClose_DT = result[0].DrugPeriodClose_DT;
                                wnd.initMolData(result[0]);
				wnd.form.findField('DocumentUc_setDate').setMinValue(wnd.DrugPeriodClose_DT);
				wnd.form.findField('DocumentUc_didDate').setMinValue(wnd.form.findField('DocumentUc_setDate').getValue());
				wnd.StrGrid.loadData({
					globalFilters: {
						DocumentUc_id: wnd.DocumentUc_id
					},
					callback: function() {
						wnd.StrGrid.updateSumm();
					}
				});
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
	show: function() {  
        var wnd = this;
		sw.Promed.swNewDocumentUcEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DocumentUc_id = null;
		this.WhsDocumentUcInvent_id = null; //идентификатор связанной с документом инв. ведомости
		this.DrugDocumentType_Code = 1; //документ прихода/расхода по умолчанию
		this.FormParams = new Object();
		this.userMedStaffFact = {};
		this.isAptMu = false;
                $isImport = false;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
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
				text:'Действия',
                                menu: [{
					name: 'create_by_contract',
					iconCls: 'add16',
					text: 'Cоздать на основе спецификации ГК',
					handler: wnd.createDocumentUcStrList.createDelegate(wnd)
				}, {
					name: 'copy_row',
					iconCls: 'copy16',
					text: 'Копировать медикамент',
					handler: function() {
						wnd.StrGrid.copyRecord();
					}
				}],
				iconCls: 'actions16'
			});
		}

		if(!wnd.StrGrid.getAction('action_import')) {
			wnd.StrGrid.addActions({
				name: 'action_import',
				iconCls: 'add16',
				text: 'Импорт',
				hidden: true,
				handler: wnd.importDocumentUcStr.createDelegate(wnd)
			});
		}

		this.setDrugDocumentType();
		this.form.reset();

		this.inf_form.reset();
		this.document_combo.resetCombo();
		this.form.findField('Contragent_sid').resetCombo();
		this.form.findField('Contragent_tid').resetCombo();
		this.form.findField('Storage_sid').resetCombo();
		this.form.findField('Storage_tid').resetCombo();
		this.form.findField('EmergencyTeam_id').getStore().removeAll();
		this.form.findField('EmergencyTeam_id').getStore().load();
		

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
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
               // this.form.findField('DocumentUc_InvoiceNum').hidden = true;
                  //  this.form.findField('DocumentUc_InvoiceDate').hidden = true;
                    
                    this.form.findField('DocumentUc_InvoiceNum').ownerCt.hide();
                    this.form.findField('DocumentUc_InvoiceDate').ownerCt.hide();
                    
	},
	initComponent: function() {
		var wnd = this;

		wnd.document_combo = new sw.Promed.SwDrugComplexMnnCombo({
			width: 525,
			allowBlank: true,
			displayField: 'WhsDocumentUc_Num',
                        id: 'WhsDocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: 'Контракт',
			forceSelection: false,
			hiddenName: 'WhsDocumentUc_id',
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
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;"></td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'WhsDocumentUc_id',
			listeners: {
				select: function(combo, record) {
					this.setLinkedFieldValues(record);
				}
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
							Contragent_sid: data.Contragent_sid,
							DrugFinance_id: data.DrugFinance_id,
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id
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
					var record = this.getStore().getAt(index);
				}
				wnd.form.findField('DocumentUc_DogDate').setValue(record.get('WhsDocumentUc_Date'));
				wnd.form.findField('DocumentUc_DogNum').setValue(record.get('WhsDocumentUc_Num'));
				wnd.form.findField('DrugFinance_id').setValue(record.get('DrugFinance_id'));
				console.log('data.DrugFinance_id = ' + record.get('DrugFinance_id'));
				wnd.form.findField('WhsDocumentCostItemType_id').setValue(record.get('WhsDocumentCostItemType_id'));
				//if (wnd.form.findField('Contragent_sid').getValue() <= 0) {
					console.log ('record = '); console.log (record);
					wnd.form.findField('Contragent_sid').setValueById(record.get('Contragent_sid'));
				//}

				this.disableLinkedField(record);
			},
			disableLinkedField: function(record) {
				if (!record) {
					var index = this.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == this.getValue(); }.createDelegate(this));
					if (index == -1) {
						return false;
					}
					var record = this.getStore().getAt(index);
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

				if (contr_s_combo.getValue() == record.get('Contragent_sid')) {
					contr_s_combo.disable();
				} else if (wnd.action != 'view' && !contr_s_combo.enable_blocked) {
					contr_s_combo.enable();
				}
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'WhsDocumentUc_id'
						},
						[
							{name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id'},
							{name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date'},
							{name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num'},
							{name: 'Contragent_sid', mapping: 'Contragent_sid'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'}
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
			fieldLabel: 'Родительский документ',
			forceSelection: false,
			hiddenName: 'DocumentUc_pid',
			id: 'ndueDocumentUc_pid',
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

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			//height: 270,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'north',
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
							fieldLabel: 'Документ №',
							name: 'DocumentUc_Num',
							width: 157,
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
						labelWidth: 50,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'от',
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
								}
							}
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 120,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'Дата поставки',
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
							fieldLabel: 'Счет-фактура №',
							name: 'DocumentUc_InvoiceNum',
							width: 157,
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
						labelWidth: 50,
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'от',
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
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swdrugfinancecombo',
							fieldLabel: 'Ист. финансирования',
							name: 'DrugFinance_id',
							width: 200,
							allowBlank: false
                                                        
						}]
					},  {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 120,
						items: [{
							xtype: 'swwhsdocumentcostitemtypecombo',
							fieldLabel: 'Статья расхода',
							name: 'WhsDocumentCostItemType_id',
							width: 200,
							allowBlank: false,
							listeners: {
								select: function(combo) {
										var $val = combo.getValue();
										//console.log ('$val = ' + $val);
										switch($val) {
											case 1: 
											case 3: 
												wnd.form.findField('DrugFinance_id').setValue(3);
												break;
											case 2: 
											case 34: 
												wnd.form.findField('DrugFinance_id').setValue(27);
												break;
										}
									}
								}
						}]
					}]
				},	{
					layout: 'form',
					items: [{
						xtype: 'swcontragentcombo',
						fieldLabel: 'Поставщик',
						hiddenName: 'Contragent_sid', 
						width: 525,
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
					items: [{
						xtype: 'swstoragecombo',
						fieldLabel: 'Склад поставщика',
						hiddenName: 'Storage_sid',
						width: 525,
						listeners: {
							change: function(combo) {
								wnd.setMol(combo, true);
							}
						}
					}]
				},  {
					layout: 'form',
					items: [{
						xtype: 'hidden',
						name: 'Mol_sid',
						width: 525
					},  {
						xtype: 'textfield',
						disabled: true,
						fieldLabel: 'МОЛ поставщика',
						name: 'Mol_sPerson',
						width: 525
					}]
				},	{
					layout: 'form',
					items: [{
						xtype: 'swmolcombo',
						disabled: true,
						fieldLabel: 'МОЛ',
						hiddenName: 'Mol_sid_combo',
						width: 525,
						listeners: {
							'change': function(combo) {
								wnd.form.findField('Mol_sid').setValue(combo.getValue());
							}
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swcontragentcombo',
						fieldLabel: 'Получатель',
						hiddenName: 'Contragent_tid',
                                                id: 'combo_Contragent_tid',
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
                                                                        var $formParams = Ext.getCmp('NewDocumentUcEditWindow').FormParams
									
									
                                                                        if (id != undefined)
                                                                            combo.setValue(id);
                                                                       else { 
                                                                           if ($formParams.ContragentTidType_code > 0) { 
                                                                            //  Находим поставщика РАС
                                                                            var $ContragentType_code = $formParams.ContragentTidType_code
                                                                            var $index = combo.getStore().findBy(function(rec) { return rec.get('ContragentType_id') == $ContragentType_code; });
                                                                            if ($index > -1) {
                                                                                var record = combo.getStore().getAt($index).data;
                                                                                combo.setValue(record.Contragent_id)
                                                                            }
                                                                        }
                                                                       } 
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
					items: [{
						xtype: 'swStorage2LpuCombo',
						fieldLabel: 'Склад',
						hiddenName: 'Storage_tid',
						width: 525,
						listeners: {
							change: function(combo) {
								wnd.setMol(combo, true);
							},
							select: function(combo, record) {
								console.log('record');
								console.log(record);
								 var wnd =  Ext.getCmp('NewDocumentUcEditWindow');
								 var comboStorage_tid = wnd.form.findField('Storage_tid');
	
								var $Lpu_id = combo.getFieldValue('Lpu_id');
								var $Lpu_Nick = comboStorage_tid.getFieldValue('Lpu_Nick');
								console.log('$Lpu_id = ' + $Lpu_id + '; $Lpu_Nick = '+ $Lpu_Nick);
								
								var wnd =  Ext.getCmp('NewDocumentUcEditWindow');
								
								wnd.StrGrid.getGrid().getStore().each(function(gridRec){
									gridRec.set('Lpu_Nick',$Lpu_Nick);
									gridRec.set('Lpu_id', $Lpu_id);
									gridRec.commit();
								});
							}
						}
					}]
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
						fieldLabel: 'МОЛ',
						name: 'Mol_tPerson',
						width: 525
					}]
				},  {
					layout: 'form',
					items: [{
						xtype: 'swmolcombo',
						disabled: true,
						fieldLabel: 'МОЛ',
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
					items: [{
						xtype: 'textfield',
						disabled: true,
						fieldLabel: 'Рецепт',
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
				}]
			}]
		});

                
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
			dataUrl: '/?c=DocumentUc&m=loadDocumentUcStrList',
			height: 180,
			region: 'center',
			object: 'DocumentUcStr',
			editformclassname: 'swNewDocumentUcStrEditWindow',
			id: 'ndueDocumentUcStrGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUcStr_oid', hidden: true},
                                {name: 'DrugFinance_id', hidden: false},
				{name: 'DrugNomen_Code', width: 80, header: 'Код'},
				{name: 'Drug_id', hidden: true},
				{name: 'Drug_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'DocumentUcStr_Ser', width: 120, header: 'Серия'},
				{name: 'PrepSeries_GodnDate', hidden: true},
				{name: 'PrepSeries_id', header: 'PrepSeries_id', hidden: true},
				{name: 'DocumentUcStr_godnDate', width: 110, header: 'Срок годности', type: 'string'},
				{name: 'Lpu_id', width: 110, header: 'lpu_id', type: 'string', hidden: true},
                                {name: 'Storage_ctrl', width: 110, header: 'Контроль наличия склада МО', type: 'string', hidden: true},
                                {name: 'Lpu_Nick', width: 110, header: 'Кому предназначено', type: 'string', hidden: !$isRegionUfa},
                                {name: 'PrepSeries_isDefect', width: 80, header: 'Брак', renderer: function(v, p, record) {
					p.css += ' x-grid3-check-col-td';
					var style = 'x-grid3-check-col'+((String(v)=='true' || String(v)=='1')?'-on-non-border-yellow':'-non-border-gray');
					return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
				}},
				{name: 'Okei_id', hidden: true},
				{name: 'Okei_NationSymbol', width: 80, header: 'Ед.изм.', type: 'string'},
				{name: 'DocumentUcStr_Count', width: 80, header: 'Кол-во', type: 'float'},
				{name: 'DocumentUcStr_EdCount', type: 'float', hidden: true},
				//{name: 'DocumentUcStr_RashCount', type: 'float', hidden: true},
				//{name: 'DocumentUcStr_RashEdCount', type: 'float', hidden: true},
				{name: 'DocumentUcStr_Price', width: 100, header: 'Цена', type: 'money'},
				{name: 'DocumentUcStr_IsNDS', width: 80, header: 'НДС в т.ч.', renderer: function(v, p, record){
					if(!v){return "";}
					p.css += ' x-grid3-check-col-td';
					var style = 'x-grid3-check-col-non-border'+((String(v)==1)?'-on':'');
					return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
				}},
				{name: 'DocumentUcStr_Sum', width: 110, header: 'Сумма', type: 'money'},
				{name: 'DrugNds_id', hidden: true},
				{name: 'DrugNds_Code', width: 80, header: 'НДС', type: 'string'},
				{name: 'DocumentUcStr_SumNds', width: 110, header: 'Сумма НДС', type: 'money'},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: 'Сумма с НДС', type: 'money'},
				{name: 'DocumentUcStr_oName', width: 110, header: 'Партия', type: 'string'},
				{name: 'DrugShipment_Name', width: 110, header: 'Партия', type: 'string'},
				{name: 'DocumentUcStr_Reason', width: 110, header: 'Причина', type: 'string'},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'DocumentUcStr_CertNum', hidden: true},
				{name: 'DocumentUcStr_CertDate', hidden: true},
				{name: 'DocumentUcStr_CertGodnDate', hidden: true},
				{name: 'DocumentUcStr_CertOrg', hidden: true},
				{name: 'DrugLabResult_Name', hidden: true},
				{name: 'SavedFileCount', hidden: true},
				{name: 'FileData', hidden: true},
				{name: 'FileChangedData', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DocumentUcStr_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					//this.ViewActions.action_ndue_actions.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					//this.ViewActions.action_ndue_actions.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			editGrid: function (action, options) {
				if (action == null)	action = 'add';
				var wnd =  Ext.getCmp('NewDocumentUcEditWindow');
				var comboStorage_tid = wnd.form.findField('Storage_tid');

				var view_frame = this;
				
				if ( !wnd.form.isValid() ) {
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
				};
				
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
					params.WhsDocumentUc_id = wnd.form.findField('WhsDocumentUc_id').getValue();
					params.Contragent_sid = wnd.form.findField('Contragent_sid').getValue();
					params.Storage_sid = wnd.form.findField('Storage_sid').getValue();
					params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
					params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
					//console.log('params'); console.log(params);
					if (options && options.copy_data) {
						Ext.apply(params, options.copy_data);
					}

					getWnd(view_frame.editformclassname).show({
						owner: view_frame,
						action: action,
						params: params,
						DrugDocumentType_Code: wnd.DrugDocumentType_Code,
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
								data.PrepSeries_id = '1';
								if ( !comboStorage_tid.ownerCt.hidden) {
									data.Lpu_Nick =  comboStorage_tid.getFieldValue('Lpu_Nick');
									data.Lpu_id = comboStorage_tid.getFieldValue('Lpu_id')
								}
								data.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
								//console.log('DrugFinance_id = ' + data.DrugFinance_id);
																
								store.insert(record_count, new record(data));
								view_frame.setFilter();
								view_frame.updateSumm();
							}
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
						params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
						params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();

						getWnd(view_frame.editformclassname).show({
							owner: view_frame,
							action: action,
							params: params,
							DrugDocumentType_Code: wnd.DrugDocumentType_Code,
							isAptMu: wnd.isAptMu,
							onSave: function(data) {
								if ( !comboStorage_tid.ownerCt.hidden) {
									data.Lpu_Nick =  comboStorage_tid.getFieldValue('Lpu_Nick');
									data.Lpu_id = comboStorage_tid.getFieldValue('Lpu_id')
								}
								if (data.RecordForMerge_id && data.RecordForMerge_id > 0) {
									view_frame.updateRecordById(data.RecordForMerge_id, data);
								} else {
									view_frame.updateRecordById(selected_record.get('DocumentUcStr_id'), data);
								}
							}
						});
					}
				}
			},
			copyRecord: function() {
				var view_frame = this;
				var selection_model = view_frame.getGrid().getSelectionModel();
				var selected_record = selection_model.getSelected();

				if (selected_record.get('DocumentUcStr_id') > 0) {
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
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected(); 
                                //console.log('state = ' + selected_record.get('state'));
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

				if ( record_count == 1 && !store.getAt(0).get('DocumentUcStr_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
					record_count = 0;
				}

				view_frame.clearFilter();
				for (var i = 0; i < data_arr.length; i++) {
					if (data_arr[i].PrepSeries_godnDate && data_arr[i].PrepSeries_godnDate.indexOf('.') > -1) {
						var d_arr = data_arr[i].PrepSeries_godnDate.split('.');
						data_arr[i].PrepSeries_godnDate = new Date(d_arr[0], d_arr[1], d_arr[2], 0,0,0);
					}
					data_arr[i].DocumentUcStr_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					data_arr[i].state = 'add';
					store.insert(record_count, new record(data_arr[i]));
				}
				view_frame.setFilter();
				view_frame.updateSumm();
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
				var nds_summ = 0;
				var summ_nds_field = wnd.inf_form.findField('TotalSumNds');
				var nds_summ_field = wnd.inf_form.findField('TotalNdsSum');

				this.getGrid().getStore().each(function(record) {
					if(record.get('DocumentUcStr_SumNds') && record.get('DocumentUcStr_SumNds') > 0) {
						summ_nds += (record.get('DocumentUcStr_SumNds') * 1);
					}
					if(record.get('DocumentUcStr_NdsSum') && record.get('DocumentUcStr_NdsSum') > 0) {
						nds_summ += (record.get('DocumentUcStr_NdsSum') * 1);
					}
				});

				summ_nds_field.setValue(summ_nds.toFixed(2));
				nds_summ_field.setValue(nds_summ.toFixed(2));

			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var item = record.data;
						item.FileData = null;
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
                
                this.StrGrid.getGrid().view = new Ext.grid.GridView(
                {
                    getRowClass: function(row, index)
                    {
                         var cls = '';
                         if ($isRegionUfa) 
                             if (row.get('Storage_ctrl') == 0)    
                                cls = cls + 'x-grid-rowred ';
                           
                         return cls;
                    
                }
       
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
				labelWidth: 150,
				collapsible: true,
				items: [{
					layout: 'column',
					labelAlign: 'right',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Сумма по накладной',
							name: 'TotalNdsSum',
							width: 200,
							disabled: true
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfield',
							fieldLabel: 'в т.ч. НДС',
							name: 'TotalSumNds',
							width: 200,
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
						fieldLabel: 'Примечание',
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
//                                $DocW =  this.ownerCt.DocW;
//                                console.log(this.ownerCt);
                                if (this.ownerCt.DocW != undefined) {
                                     sw.swMsg.alert('Внимание', 'В системе есть исполненный документ ' + this.ownerCt.DocW + '. <br>Текущий документ под этим номером исполнить нельзя!', function() { return false; });
                                }
                                else {
                                    //делаем проверку на наличие в спецификации позиций без привязки к ЛПУ
                                    if ($isRegionUfa && wnd.DrugDocumentType_Code == 6 && !this.ownerCt.checkEmptyLpu()) {
                                                    if (confirm('Не для всех медикаментов указана мед. оргагизация. Продолжить?')) {
                                                            this.ownerCt.doExecute();
                                                    }
                                    } else {
                                                    this.ownerCt.doExecute();
                                    }
                                }
                                 
                               
				//this.ownerCt.doExecute();
			},
			iconCls: 'ok16',
			text: 'Исполнить'
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
				text: 'Печать'
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					sw.swMsg.show({
                        buttons: Ext.Msg.YESNO,
                        fn: function(buttonId) {
                        	if ( buttonId == 'yes' ) {
                        		this.ownerCt.hide();
                        	}
                        }.createDelegate(this),
                        icon: Ext.Msg.WARNING,
                        msg: lang['vyi_deystvitelno_jelaete_zakryt_document_bez_sochraneniya'],
                        title: lang['preduprejdenie']
                    });
                    return false;
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				form,
				this.StrGrid,
				wnd.inf_form_panel
			]
		});
		sw.Promed.swNewDocumentUcEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('NewDocumentUcEditForm').getForm();
		this.inf_form = this.findById('NewDocumentUcEditInfForm').getForm();
            }
});