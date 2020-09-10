/**
* swNewDocumentUcStrEditWindow - произвольное окно редактирования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      07.2014
* @comment      
*/
sw.Promed.swNewDocumentUcStrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Медикамент',
	layout: 'border',
	id: 'NewDocumentUcStrEditWindow',
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
	checkDrugShipmentName: function(params) { //проверка уникальности номера партии, если партия уникальна, выполняется callback, иначе возвращается сообщение об ошибке
		var wnd = this;
		var is_unique = true;
		var name_field = wnd.form.findField('DrugShipment_Name');
		var sh_name = wnd.form.findField('DrugShipment_Name').getValue();
		var callback = params && params.callback && typeof params.callback == 'function' ? params.callback : Ext.emptyFn;

		if (!name_field.enable_blocked && sh_name != '' && sh_name != '1') { 
			//проверяем уникальность в пределах текущего документа
                        // Проверку на уникальность партии  пока закрываем  для sh_name == '1' (Тагир)
			wnd.owner.getGrid().getStore().each(function(record) {
				if (record.get('DocumentUcStr_id') != wnd.params.DocumentUcStr_id && record.get('DrugShipment_Name') == sh_name) {
					is_unique = false;
					return false;
				}
			});

			if (is_unique) {
				//проверяем на уникальность среди всех созданых партий
				wnd.getLoadMask().show();
				Ext.Ajax.request({
					params: {
						DrugShipment_Name: sh_name,
						DocumentUcStr_id: wnd.params.DocumentUcStr_id
					},
					callback: function(opt, success, resp) {
						wnd.getLoadMask().hide();
						var response_obj = Ext.util.JSON.decode(resp.responseText);
						if (response_obj) {
							if (response_obj[0].Check_Result) {
								callback();
							} else {
								sw.swMsg.alert('Ошибка', 'Указанный номер партии уже используется.');
							}
						} else {
							sw.swMsg.alert('Ошибка', 'При проверке номера парти возникла ошибка.');
						}
					},
					url: '/?c=DocumentUc&m=checkDrugShipmentName'
				});
			} else {
				sw.swMsg.alert('Ошибка', 'Указанный номер партии уже используется в пределах текущего документа.');
			}
		} else {
			callback();
		}
		return is_unique;
	},
	generateDrugShipmentName: function() {
		var wnd = this;
		var name_field = wnd.form.findField('DrugShipment_Name');
		var max_num = 0;

		if (wnd.owner && wnd.owner.getGrid()) {
			wnd.owner.getGrid().getStore().each(function(record) {
				if (record.get('DocumentUcStr_id') != wnd.params.DocumentUcStr_id) {
					var num = record.get('DrugShipment_Name') != '' ? parseFloat(record.get('DrugShipment_Name')) : 0;
					if (!isNaN(num) && num > max_num) {
						max_num = num;
					}
				}
			});
			max_num = Math.ceil(max_num);
		}

		if (!name_field.enable_blocked) {
			wnd.getLoadMask().show();
			Ext.Ajax.request({
				callback: function(opt, success, resp) {
					wnd.getLoadMask().hide();
					var response_obj = Ext.util.JSON.decode(resp.responseText);
					if (response_obj && response_obj[0].DrugShipment_Name != '') {
						var new_num = response_obj[0].DrugShipment_Name;
						name_field.setValue(new_num > max_num+1 ? new_num : max_num+1);
					}
				},
				url: '/?c=DocumentUc&m=generateDrugShipmentName'
			});
		}
	},
	setDefaultValues: function () { //заполнение формы значениями "по умолчанию"
		this.form.setValues(this.DefaultValues); 
		this.cert_form.setValues(this.DefaultValues);
		this.generateDrugShipmentName();
	},
	setDrugDocumentType: function(type_code) { //настройка внешнего вида формы в зависимости от типа документа
		var show_print_button = false;

		if (!type_code) {
			type_code = this.DrugDocumentType_Code;
		} else {
			this.DrugDocumentType_Code = type_code;
		}

		//значения по умолчанию
		this.DefaultValues = new Object();
		this.form.findField('DocumentUcStr_oid').enable_blocked = false;
		this.form.findField('DrugShipment_Name').enable_blocked = true;
		this.form.findField('DocumentUcStr_Price').enable_blocked = false;
		this.form.findField('DrugNds_id').enable_blocked = false;
		this.form.findField('DocumentUcStr_IsNDS').enable_blocked = false;
		this.form.findField('DocumentUcStr_Sum').enable_blocked = false;
		this.form.findField('DocumentUcStr_SumNds').enable_blocked = false;
		this.form.findField('DocumentUcStr_NdsSum').enable_blocked = false;
		this.form.findField('DocumentUcStr_Ser').enable_blocked = false;
		this.form.findField('PrepSeries_GodnDate').enable_blocked = false;
		this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = false;
		this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = false;
		this.cert_form.findField('DrugLabResult_Name').enable_blocked = false;
		this.CertDateRange.enable_blocked = false;

		this.form.findField('PrepSeries_GodnDate').allowBlank = true;
		this.form.findField('DocumentUcStr_oid').allowBlank = true;
		this.form.findField('DrugShipment_Name').allowBlank = true;
		this.form.findField('DocumentUcStr_Reason').allowBlank = true;

		this.form.findField('DrugNds_id').showContainer();
		this.form.findField('DocumentUcStr_IsNDS').showContainer();
		this.form.findField('DocumentUcStr_IsNDS').showContainer();
		this.form.findField('DocumentUcStr_SumNds').showContainer();
		this.form.findField('DocumentUcStr_NdsSum').showContainer();

		this.form.findField('DocumentUcStr_oid').ownerCt.hide();
		this.form.findField('DrugShipment_Name').ownerCt.hide();
		this.form.findField('DocumentUcStr_Reason').ownerCt.hide();

		this.base_params = new Object();
		this.base_params.DocumentUcStr_oid = new Object();
		this.drug_combo.store.baseParams = new Object();
        this.form.findField('Lpu_id').ownerCt.hide();  //  По умолчанию
		switch(type_code) {
			case 2: //Документ списания
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
				this.form.findField('DocumentUcStr_Reason').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
				this.form.findField('DocumentUcStr_Reason').ownerCt.show();
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
			case 3: //Документ ввода остатков
				show_print_button = true;
				this.DefaultValues.DrugNds_id = 2; //10%
				this.form.findField('DocumentUcStr_oid').enable_blocked = true;
				this.form.findField('DrugShipment_Name').enable_blocked = false;
				this.form.findField('PrepSeries_GodnDate').allowBlank = false;
				this.form.findField('DrugShipment_Name').allowBlank = false;
				this.form.findField('DrugShipment_Name').ownerCt.show();
				//this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				break;
			case 6: //Приходная накладная
				show_print_button = true;
				this.DefaultValues.DrugNds_id = 1; // Без НДС
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_oid').enable_blocked = true;
				this.form.findField('DrugShipment_Name').enable_blocked = false;
				this.form.findField('PrepSeries_GodnDate').allowBlank = false;
				this.form.findField('DrugShipment_Name').allowBlank = false;
				this.form.findField('DrugShipment_Name').ownerCt.hide(); 
                                this.form.findField('Lpu_id').ownerCt.show();
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				break;
                        case 17:        
			case 10: //Расходная накладная
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
			case 12: //Документ оприходования
				show_print_button = true;
				break;
			case 15: //Накладная на внутреннее перемещение
				
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
			/*
                        case 17: //Возвратная накладная (расходная)
				if (this.params.DocumentUc_pid > 0) {
					this.drug_combo.store.baseParams.DocumentUc_id = this.params.DocumentUc_pid;
					this.base_params.DocumentUcStr_oid.DocumentUc_id = this.params.DocumentUc_pid;
				}
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
                        */        
			case 20:
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
			case 21: // Списание медикаментов со склада на пациента
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
				this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
		}

		if (this.isAptMu) {
			this.DefaultValues.DrugNds_id = 1; //Без НДС
			this.form.findField('DrugNds_id').hideContainer();
			this.form.findField('DocumentUcStr_IsNDS').hideContainer();
			this.form.findField('DocumentUcStr_IsNDS').hideContainer();
			this.form.findField('DocumentUcStr_SumNds').hideContainer();
			this.form.findField('DocumentUcStr_NdsSum').hideContainer();
		}

		//установка параметров для комбобоксов
		this.form.findField('DocumentUcStr_oid').store.baseParams = this.base_params.DocumentUcStr_oid;

		//установка видимости кнопки печати
		var selected_record = null;
		if (this.owner) {
			selected_record = this.owner.getGrid().getSelectionModel().getSelected();
		}
		if (show_print_button && selected_record && selected_record.get('state') != 'add') {
			this.buttons[1].show();
		} else {
			this.buttons[1].hide();
		}
	},
	setEdCount: function() { //пересчет полей с количеством доз
		var drug_fas = this.form.findField('Drug_Fas').getValue() > 0 ? this.form.findField('Drug_Fas').getValue()*1 : 0;
		var ost_cnt = this.form.findField('DocumentUcStr_OstCount').getValue() > 0 ? this.form.findField('DocumentUcStr_OstCount').getValue()*1 : 0;
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;
		var ost_ed_cnt = null;
		var ed_cnt = null;

		if (drug_fas > 0) {
			if (ost_cnt > 0) {
				ost_ed_cnt = drug_fas*ost_cnt;
			}
			if (cnt > 0) {
				ed_cnt = drug_fas*cnt;
			}
		}

		this.form.findField('DocumentUcStr_OstEdCount').setValue(ost_ed_cnt);
		this.form.findField('DocumentUcStr_EdCount').setValue(ed_cnt);
	},
	setSumFields: function() { //пересчет полей с суммами
		var is_nds = (this.form.findField('DocumentUcStr_IsNDS').getValue() > 0);
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;
		var sum = 0;
		var sum_nds = 0;
		var nds_sum = 0;
		var nds_id = this.form.findField('DrugNds_id').getValue() > 0 ? this.form.findField('DrugNds_id').getValue()*1 : 0;
		var nds_koef = 1;
                
		var price = this.form.findField('DocumentUcStr_Price').getValue() > 0 ? this.form.findField('DocumentUcStr_Price').getValue()*1 : 0;
		var nds = 0;

		if (nds_id > 0) {
			var index = this.form.findField('DrugNds_id').getStore().findBy(function(rec) { return rec.get('DrugNds_id') == nds_id; });
			if (index > -1) {
				var record = this.form.findField('DrugNds_id').getStore().getAt(index);
				var nds = record.get('DrugNds_Code')*1;
				nds_koef = (100.0+nds)/100.0;
			}
		}

		sum = (cnt * price).toFixed(2);
		nds_sum = is_nds ? sum : (sum * nds_koef).toFixed(2);
		sum_nds = is_nds ? (sum - (sum/nds_koef)).toFixed(2) : (nds_sum - sum).toFixed(2);

		this.form.findField('DocumentUcStr_Sum').setValue(sum);
		this.form.findField('DocumentUcStr_NdsSum').setValue(nds_sum);
		this.form.findField('DocumentUcStr_SumNds').setValue(sum_nds);
	},
	setPrepSeriesFields: function(params) {
		var wnd = this;
		var callback = params && params.callback && typeof params.callback == 'function' ? params.callback : Ext.emptyFn;
		if (params && params.DocumentUcStr_Ser && params.DocumentUcStr_Ser != '' && params.Drug_id > 0) {
			Ext.Ajax.request({
				params:{
					Drug_id: params.Drug_id,
					PrepSeries_Ser: params.DocumentUcStr_Ser
				},
				failure:function () {
					params.callback(callback_params);
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					var data = new Object({
						PrepSeries_id: null,
						PrepSeries_isDefect: 0
					});
					if (result[0]) {
						data = result[0];
					}
					if (data.PrepSeries_id) {
						wnd.form.findField('PrepSeries_id').setValue(data.PrepSeries_id);
						wnd.form.findField('PrepSeries_isDefect').setValue(data.PrepSeries_isDefect);

						var godn_field = wnd.form.findField('PrepSeries_GodnDate');
						if (godn_field.getValue().length > 0 || data.PrepSeries_GodnDate.length > 0) {
							godn_field.setValue(data.PrepSeries_GodnDate);
						}
					}
					params.callback(data);
				},
				url:'/?c=DocumentUc&m=getPrepSeriesByDrugAndSeries'
			});
		}
	},
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'DrugNomen_Code',
			'Drug_id',
			'DrugShipment_Name',
			'DocumentUcStr_oid',
			'DocumentUcStr_Count',
			'DocumentUcStr_EdCount',
			'DocumentUcStr_Price',
			'DrugNds_id',
			'DocumentUcStr_IsNDS',
			'DocumentUcStr_Sum',
			'DocumentUcStr_SumNds',
			'DocumentUcStr_NdsSum',
			'DocumentUcStr_Ser',
			'PrepSeries_GodnDate',
			'DocumentUcStr_Reason'
		];

		var cert_field_arr = [
			'DocumentUcStr_CertNum',
			'DocumentUcStr_CertOrg',
			'DrugLabResult_Name'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var combo = wnd.form.findField(field_arr[i]);
			if (disable || combo.enable_blocked) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		for (i in cert_field_arr) if (wnd.cert_form.findField(cert_field_arr[i])) {
			combo = wnd.cert_form.findField(cert_field_arr[i]);
			if (disable || combo.enable_blocked) {
				combo.disable();
			} else {
				combo.enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
			wnd.CertDateRange.disable();
			wnd.FileUploadPanel.disable();
		} else {
			wnd.buttons[0].enable();
			if (!wnd.CertDateRange.enable_blocked) {
				wnd.CertDateRange.enable();
			} else {
				wnd.CertDateRange.disable();
			}
			wnd.FileUploadPanel.enable();
			wnd.FileUploadPanel.checkLimitCountCombo();
		}
	},
	checkGodnDate: function() {
		var current_date = new Date();
		var date = this.form.findField('PrepSeries_GodnDate').getValue();

		current_date.setHours(0);
		current_date.setMinutes(0);
		current_date.setSeconds(0);
		current_date.setMilliseconds(0);

		return (date == "" || (date - current_date) >= 0);
	},
	getDataForMerge: function(data) {
		var wnd = this;
		var merge_data = new Object();

		if (this.owner && this.owner.getGrid()) {
			this.owner.getGrid().getStore().each(function(record) {
				if (
					record.get('DocumentUcStr_id') != wnd.params.DocumentUcStr_id &&
					record.get('Drug_id') == data.Drug_id &&
					((record.get('DocumentUcStr_oid') <= 0 && data.DocumentUcStr_oid <= 0) || record.get('DocumentUcStr_oid') == data.DocumentUcStr_oid) &&
					record.get('DocumentUcStr_Ser') == data.DocumentUcStr_Ser &&
					((record.get('DocumentUcStr_CertNum') <= 0 && data.DocumentUcStr_CertNum <= 0) || record.get('DocumentUcStr_CertNum') == data.DocumentUcStr_CertNum) &&
					record.get('DrugNds_id') == data.DrugNds_id &&
					record.get('DocumentUcStr_IsNDS') == data.DocumentUcStr_IsNDS &&
					record.get('DocumentUcStr_Price') == data.DocumentUcStr_Price
				) {
					Ext.apply(merge_data, record.data)
					return false;
				}
			});
		}
		return merge_data;
	},
	doSave:  function() {
		var wnd = this;
		if (this.checkGodnDate() || confirm('Внимание! Указан срок годности меньше текущей даты. Продолжить сохранение?')) {
                        if (wnd.form.findField('DrugShipment_Name').ownerCt.hidden)  //  Если номер партии скрыт (Тагир)
                             wnd.form.findField('DrugShipment_Name').setValue('1');
			if ( !this.form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						wnd.findById('NewDocumentUcStrEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			var index = null;
			var record = null;
			var data = wnd.form.getValues();
			var cert_data = wnd.cert_form.getValues();
                        
			//data.DocumentUcStr_OstCount = wnd.form.findField('DocumentUcStr_OstCount').getValue();
			//data.DocumentUcStr_OstEdCount = wnd.form.findField('DocumentUcStr_OstEdCount').getValue();
                       //alert(this.base_params.DocumentUcStr_oid.Drug_id );
                       
                       if (!data.Drug_id )
                            data.Drug_id =  this.base_params.DocumentUcStr_oid.Drug_id;  //Вставил Тагир, т.к. переменная было некорректная                                 
                        // Ext.getCmp('NewDocumentUcStrEditWindow').base_params.DocumentUcStr_oid.Drug_id
                        
			data.DrugShipment_Name = this.form.findField('DrugShipment_Name').getValue();
			data.DocumentUcStr_oid = this.form.findField('DocumentUcStr_oid').getValue();
			data.DocumentUcStr_Price = this.form.findField('DocumentUcStr_Price').getValue();
			data.DocumentUcStr_Sum = this.form.findField('DocumentUcStr_Sum').getValue();
			data.DocumentUcStr_SumNds = this.form.findField('DocumentUcStr_SumNds').getValue();
			data.DocumentUcStr_NdsSum = this.form.findField('DocumentUcStr_NdsSum').getValue();
			data.DrugNds_id = this.form.findField('DrugNds_id').getValue();
			data.DocumentUcStr_IsNDS = this.form.findField('DocumentUcStr_IsNDS').getValue() ? 1 : 0;
			data.DocumentUcStr_Ser = this.form.findField('DocumentUcStr_Ser').getValue();
			if (data.DrugShipment_Name == '1' && data.DocumentUcStr_Ser != '')
			    data.DrugShipment_Name = data.DocumentUcStr_Ser;
			data.DocumentUcStr_Reason = this.form.findField('DocumentUcStr_Reason').getValue();
			data.PrepSeries_GodnDate = Ext.util.Format.date(this.form.findField('PrepSeries_GodnDate').getValue(), 'd.m.Y');
                        data.DocumentUcStr_godnDate = Ext.util.Format.date(this.form.findField('PrepSeries_GodnDate').getValue(), 'd.m.Y');
                        data.DocumentUcStr_CertNum = this.cert_form.findField('DocumentUcStr_CertNum').getValue();
			data.DocumentUcStr_CertOrg = this.cert_form.findField('DocumentUcStr_CertOrg').getValue();
			data.DrugLabResult_Name = this.cert_form.findField('DrugLabResult_Name').getValue();
                        data.Lpu_id = this.form.findField('Lpu_id').getValue();
                        console.log ( this.form.findField('Lpu_id'));
                        data.Lpu_Nick = this.form.findField('Lpu_id').lastSelectionText;
			

			data.FileData = wnd.FileUploadPanel.getDataCopy();
			data.FileChangedData = wnd.FileUploadPanel.getChangedData();
			data.Okei_NationSymbol = 'упак';
			data.Drug_Name = null;
			data.DrugNds_Code = null;
			data.DocumentUcStr_oName = null;
			data.DocumentUcStr_CertDate = Ext.util.Format.date(wnd.CertDateRange.getValue1(), 'd.m.Y');
			data.DocumentUcStr_CertGodnDate = Ext.util.Format.date(wnd.CertDateRange.getValue2(), 'd.m.Y');

			//проверка на дублирование другой строки в документе учета
			data.RecordForMerge_id = null;
			var merge_data = this.getDataForMerge(data);
			if (merge_data && merge_data.DocumentUcStr_id > 0) {
				if (confirm('Такая строка уже есть в документе учета, выполнить суммирование количества?')) {
					data.RecordForMerge_id = merge_data.DocumentUcStr_id;
					data.DocumentUcStr_Sum = data.DocumentUcStr_Sum*1 + merge_data.DocumentUcStr_Sum*1;
					data.DocumentUcStr_SumNds = data.DocumentUcStr_SumNds*1 + merge_data.DocumentUcStr_SumNds*1;
					data.DocumentUcStr_NdsSum = data.DocumentUcStr_NdsSum*1 + merge_data.DocumentUcStr_NdsSum*1;
					data.DocumentUcStr_Count = data.DocumentUcStr_Count*1 + merge_data.DocumentUcStr_Count*1;

					if (wnd.action == 'edit') {
						//удаляем текущую запись
						wnd.owner.deleteRecord();
					}
				} else {
					return false;
				}
			}

			if (data.Drug_id > 0) {
				index = wnd.drug_combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == data.Drug_id; });
				if (index == -1) {
					return false;
				}
				record = wnd.drug_combo.getStore().getAt(index);
				data.Drug_Name = record.get('Drug_Name');
			}

			if (data.DrugNds_id > 0) {
				index = wnd.form.findField('DrugNds_id').getStore().findBy(function(rec) { return rec.get('DrugNds_id') == data.DrugNds_id; });
				if (index == -1) {
					return false;
				}
				record = wnd.form.findField('DrugNds_id').getStore().getAt(index);
				data.DrugNds_Code = record.get('DrugNds_Code');
			}
                        
//                        if (wnd.form.findField('DocumentUcStr_oid').ownerCt.hidden == false)
//                            data.DocumentUcStr_oid = undefined;
			if (data.DocumentUcStr_oid > 0 && wnd.form.findField('DocumentUcStr_oid').ownerCt.hidden == false) {
				index = wnd.form.findField('DocumentUcStr_oid').getStore().findBy(function(rec) { return rec.get('DocumentUcStr_id') == data.DocumentUcStr_oid; });
				if (index == -1) {
					return false;
				}
				record = wnd.form.findField('DocumentUcStr_oid').getStore().getAt(index);
				data.DocumentUcStr_oName = record.get('DrugShipment_Name');
                                data.DrugShipment_Name = record.get('DrugShipment_Name');
                                data.DrugFinance_id = record.data.DrugFinance_id;
                                        //record.get('DrugFinance_id');
			}
			wnd.onSave(data);
			wnd.hide();
		}

		return true;		
	},
	doPrint: function(){
		var docstr_id = this.params.DocumentUcStr_id;
		if (docstr_id > 0) {
			printBirt({
				'Report_FileName': 'DocumentUcStrrptdesign.rptdesign',
				'Report_Params': '&paramDocumentUcStr=' + docstr_id,
				'Report_Format': 'pdf'
			});
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swNewDocumentUcStrEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.onSave = Ext.emptyFn;
		this.DefaultValues = new Object();
		this.DrugDocumentType_Code = null;
		this.isAptMu = false;
		this.params = new Object();

        if (!arguments[0]) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].onSave && typeof arguments[0].onSave == 'function') {
			this.onSave = arguments[0].onSave;
		}
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugDocumentType_Code ) {
			this.DrugDocumentType_Code = arguments[0].DrugDocumentType_Code;
		}
//                 var enable = this.DrugDocumentType_Code([6,17, 18]);
//                if (enable) {
//                    
//                } else {
//                    
//                }
                    
                
		if ( arguments[0].isAptMu ) {
			this.isAptMu = arguments[0].isAptMu;
		}
		if (arguments[0].params) {
			this.params = arguments[0].params;
		}

		var lab_combo = this.cert_form.findField('DrugLabResult_Name');

		this.setDrugDocumentType();

		this.form.reset();
		this.cert_form.reset();
		this.FileUploadPanel.reset();
		this.FileUploadPanel.restorePanel();

		this.drug_combo.lastQuery = null;
		this.drug_combo.store.removeAll();
		lab_combo.getStore().removeAll();

		wnd.setDisabled(wnd.action == 'view');
                
                //wnd.params.Storage_id = this.params.Storage_sid;
                wnd.params.Org_id = getGlobalOptions().org_id;
                Ext.getCmp('SEW_Lpu4FarmStorage').store.load({
                        //url: '/?c=Storage&m=loadStorageForm',
                         params:  {Org_id: getGlobalOptions().org_id}
                        })

		wnd.setTitle("Медикамент");
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (wnd.action) {
			case 'add':
				wnd.setTitle(this.title + ": Добавление");
				wnd.setDefaultValues();
			case 'edit':
			case 'view':
				if (wnd.action != 'add') {
					wnd.setTitle(this.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
				}
				this.form.setValues(wnd.params); 
				this.cert_form.setValues(wnd.params);
				wnd.drug_combo.setValueById(wnd.params.Drug_id);
				lab_combo.getStore().load({
					callback: function() {
						lab_combo.setValue(lab_combo.getValue());
					}
				});
				if (wnd.params.DocumentUcStr_CertDate && wnd.params.DocumentUcStr_CertGodnDate) {
					wnd.CertDateRange.setValue(wnd.params.DocumentUcStr_CertDate + ' - ' + wnd.params.DocumentUcStr_CertGodnDate);
				}
				loadMask.hide();

				if (this.action == 'edit') {
					if (wnd.form.findField('PrepSeries_id').getValue() > 0) {
						wnd.form.findField('PrepSeries_GodnDate').disable();
					} else if (!wnd.form.findField('PrepSeries_GodnDate').enable_blocked) {
						wnd.form.findField('PrepSeries_GodnDate').enable();
					}
				}
			break;	
		}

		loadMask.hide();
	},
	initComponent: function() {
		var wnd = this;

		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			id: 'nduseFileUploadPanel',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile',
			limitCountCombo: 1,
			listParams: {
				ObjectName: 'DocumentUcStr',
				ObjectID: null,
				callback: function () {
					var panel = wnd.FileUploadPanel;
					if (panel.disabled) {
						panel.disable();
					} else {
						panel.enable();
						panel.checkLimitCountCombo();
					}
				}
			},
			getDataCopy: function() {
				var panel = this;
				var data = new Array();
				panel.FileStore.each(function(record) {
					if (panel.findById('FileDescr' + record.data.Store_id)) {
						record.data[panel.fieldsPrefix+'_Comment'] = panel.findById('FileDescr' + record.data.Store_id).getValue();
					}
					data.push(record.data);
				});
				return data;
			},
			restorePanel: function() {
				var panel = this;
				
				if (wnd.params) {
					if (wnd.params.FileData) { //восстанавливаем из копии
						var ds_model = Ext.data.Record.create([
							'Store_id',
							panel.fieldsPrefix+'_id',
							panel.fieldsPrefix+'_FilePath',
							panel.fieldsPrefix+'_FileName',
							'state',
							panel.fieldsPrefix+'_FileLink',
							panel.fieldsPrefix+'_Comment'
						]);
						var pos = (panel.FileStore.data.first() && panel.FileStore.data.first().data[panel.fieldsPrefix+'_id'] != null) ? panel.FileStore.data.length : 0;
						
						for (var i = 0; i < wnd.params.FileData.length; i++) {
							var record = new ds_model(wnd.params.FileData[i]);
							panel.FileStore.insert(pos, record);
							panel.addCombo(true, record.data);
						}

						if (panel.disabled) {
							panel.disable();
						} else {
							panel.enable();
							panel.checkLimitCountCombo();
						}
					} else if (wnd.params.SavedFileCount > 0 && wnd.params.DocumentUcStr_id && wnd.params.DocumentUcStr_id > 0) { //загружаем данные из БД
						this.listParams.ObjectID = wnd.params.DocumentUcStr_id;
						this.loadData();
					}
				}
			}
		});

		wnd.drug_combo = new sw.Promed.SwDrugComplexMnnCombo({
			anchor: '100%',
			allowBlank: false,
			displayField: 'Drug_Name',
                        id: 'swNewDocumentUcStr_DrugName',
                        enableKeyEvents: true,
			fieldLabel: 'Торг. наим.',
			forceSelection: false,
			hiddenName: 'Drug_id',
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
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Drug_Name}</h3></td><td style="width:20%;"></td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'Drug_id',
			listeners: {
				select: function(combo, record) {
					this.setLinkedFieldValues(record);
				}
			},
			onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var searchWindow = 'swEvnPrescrDrugTorgSearchWindow';
				var combo = this;
				combo.disableBlurAction = true;
                                //console.log('baseParams');
                                //console.log(combo.store.baseParams);
				getWnd(searchWindow).show({
					hideIsFromDocumentUcOst: true,
					searchUrl: '/?c=DocumentUc&m=loadDrugComboForDocumentUcStr',
					searchParams: combo.store.baseParams,
					formParams: {
						Drug_Name: combo.getRawValue(),
						isFromDocumentUcOst: false
					},
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (drugData) {
						combo.fireEvent('beforeselect', combo);

						combo.getStore().removeAll();
						combo.getStore().loadData([{
							Drug_id: drugData.Drug_id,
							Drug_Name: drugData.Drug_Name,
							DrugNomen_Code: drugData.DrugNomen_Code,
							Drug_Fas: drugData.Drug_Fas,
							DrugForm_Name: drugData.DrugForm_Name,
							DrugUnit_Name: drugData.DrugUnit_Name,
							Price: drugData.Price
						}], true);

						combo.setValue(drugData.Drug_id);
						var index = combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drugData.Drug_id; });

						if (index == -1) {
							return false;
						}

						var record = combo.getStore().getAt(index);

						if ( typeof record == 'object' ) {
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, record.get('Drug_id'));
						}

						combo.setLinkedFieldValues(record);

						getWnd(searchWindow).hide();
					}
				});
			},
			setValueById: function(drug_id) {
				var combo = this;
				combo.store.baseParams.Drug_id = drug_id;
				combo.store.load({
					callback: function(){
						combo.setValue(drug_id);
						combo.store.baseParams.Drug_id = null;
						combo.setLinkedFieldValues();
					}
				});
			},
			setLinkedFieldValues: function(record) {
				if (!record) {
					var index = this.getStore().findBy(function(rec) { return rec.get('Drug_id') == this.getValue(); }.createDelegate(this));
					if (index == -1) {
						return false;
					}
					var record = this.getStore().getAt(index);
				}
				wnd.form.findField('DrugNomen_Code').setValue(record.get('DrugNomen_Code'));
				wnd.form.findField('Drug_Fas').setValue(record.get('Drug_Fas'));
				wnd.form.findField('DrugForm_Name').setValue(record.get('DrugForm_Name'));
				wnd.form.findField('DrugUnit_Name').setValue(record.get('DrugUnit_Name')); 
				wnd.form.findField('DocumentUcStr_Price').setValue(record.get('Price'));
				wnd.setEdCount();

				//загрузка партий
				var document_uc_str_combo = wnd.form.findField('DocumentUcStr_oid');
                                //console.log('загрузка партий');
                                
				if (!document_uc_str_combo.enable_blocked) { //Расходная накладная
                                        //console.log('загрузка партий 2');
					var usc = document_uc_str_combo.getValue();
					document_uc_str_combo.clearValue();
					document_uc_str_combo.getStore().removeAll();
					document_uc_str_combo.lastQuery = '';

					wnd.base_params.DocumentUcStr_oid.Drug_id = null;

					if (record.get('Drug_id') > 0) {
						wnd.base_params.DocumentUcStr_oid.Drug_id = record.get('Drug_id');
						wnd.base_params.DocumentUcStr_oid.DocumentUcStr_id = wnd.params.DocumentUcStr_oid > 0 ? wnd.params.DocumentUcStr_oid : null;

						document_uc_str_combo.getStore().load({
							params: wnd.base_params.DocumentUcStr_oid,
							callback: function() {
								if (usc>0) {
									var idx = document_uc_str_combo.getStore().findBy(function(rec) { return rec.get('DocumentUcStr_id') == usc; });
									document_uc_str_combo.setValue(idx > -1 ? usc : null);
									document_uc_str_combo.fireEvent('change', document_uc_str_combo, document_uc_str_combo.getValue());
								}
							}
						});
					} else {
						document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
					}
				}
                                //console.log(Ext.getCmp('swNewDocumentUcStr_DrugName').getValue());
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'Drug_id'
							/*,
							root: 'data',
							totalProperty: 'totalCount'
							*/
						},
						[
							{name: 'Drug_id', mapping: 'Drug_id'},
							{name: 'Drug_Name', mapping: 'Drug_Name'},
							{name: 'DrugNomen_Code', mapping: 'DrugNomen_Code'},
							{name: 'Drug_Fas', mapping: 'Drug_Fas'},
							{name: 'DrugForm_Name', mapping: 'DrugForm_Name'},
							{name: 'DrugUnit_Name', mapping: 'DrugUnit_Name'},
							{name: 'Price', mapping: 'Price'}
						]),
					url: '/?c=DocumentUc&m=loadDrugComboForDocumentUcStr'
				});
			}
		});

		this.CertDateRange = new Ext.form.DateRangeField({
			width: 177,
			fieldLabel: 'Период действия',
			hiddenName: 'CertDateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'NewDocumentUcStrEditForm',
				//style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 170,
				labelAlign: 'right',
				collapsible: true,
				items: [
					{
						xtype: 'textfield',
						fieldLabel: 'Код',
						name: 'DrugNomen_Code',
						listeners: {
							'change': function(field, newValue, oldValue) {
								Ext.Ajax.request({
									url: '/?c=DrugNomen&m=getDrugByDrugNomenCode',
									params: {
										DrugNomen_Code: newValue
									},
									success: function(response){
										var result = Ext.util.JSON.decode(response.responseText);
										if (result[0] && result[0].Drug_id) {
											wnd.drug_combo.setValueById(result[0].Drug_id);
										} else {
											field.setValue(oldValue);
										}
									}
								});
							}
						}
					},
					wnd.drug_combo,
					{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Ед. учета', name: 'DrugUnit_Name', disabled: true}]
						}, {
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Ед.дозировки', name: 'DrugForm_Name', width: 540, disabled: true}]
						}, {
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Кол-во в упак.', name: 'Drug_Fas', disabled: true}]
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Наименование партии',
							name: 'DrugShipment_Name',
							anchor: '100%'
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'combo',
							hiddenName: 'DocumentUcStr_oid',
							fieldLabel: 'Код ЛС',
							displayField: 'DocumentUcStr_Name',
							valueField: 'DocumentUcStr_id',
							enableKeyEvents: true,
							editable: false,
							forceSelection: true,
							triggerAction: 'all',
							allowBlank: true,
							anchor: '100%',
							listWidth: 800,
							loadingText: 'Идет поиск...',
							minChars: 1,
							minLength: 1,
							minLengthText: 'Поле должно быть заполнено',
							mode: 'local',
							resizable: true,
							selectOnFocus: true,
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'DocumentUcStr_id'
								}, [
									{name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id'},
									{name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name'},
									{name: 'DocumentUcStr_OstCount', mapping: 'DocumentUcStr_OstCount'},
									{name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price'},
									{name: 'DrugNds_id', mapping: 'DrugNds_id'},
									{name: 'DocumentUcStr_IsNDS', mapping: 'DocumentUcStr_IsNDS'},
									{name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser'},
									{name: 'PrepSeries_GodnDate', mapping: 'PrepSeries_GodnDate'},
									{name: 'PrepSeries_isDefect', mapping: 'PrepSeries_isDefect'},
									{name: 'DocumentUcStr_CertNum', mapping: 'DocumentUcStr_CertNum'},
									{name: 'DocumentUcStr_CertOrg', mapping: 'DocumentUcStr_CertOrg'},
									{name: 'DocumentUcStr_CertDate', mapping: 'DocumentUcStr_CertDate'},
									{name: 'DocumentUcStr_CertGodnDate', mapping: 'DocumentUcStr_CertGodnDate'},
									{name: 'DrugLabResult_Name', mapping: 'DrugLabResult_Name'},
									{name: 'DrugNds_Code', mapping: 'DrugNds_Code'}, 
                                                                        {name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
									{name: 'DrugFinance_Name', mapping: 'DrugFinance_Name'},
									{name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name'},
									{name: 'DrugShipment_Name', mapping: 'DrugShipment_Name'}, 
                                                                        {name: 'Drug_Code', mapping: 'Drug_Code'}, 
                                                                        {name: 'Storage_id', mapping: 'Storage_id'},
                                                                        {name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
                                                                        {name: 'DrugShipment_Name', mapping: 'DrugShipment_Name'}
								]),
								url: '/?c=DocumentUc&m=loadDocumentUcStrOidCombo'
							}),
                                                         tpl: new Ext.XTemplate(
								'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
								'<td style="padding: 2px; width: 10%;">Код ЛС</td>',
                                                                '<td style="padding: 2px; width: 10%;">Остаток</td>',
								'<td style="padding: 2px; width: 10%;">Серия</td>',
								'<td style="padding: 2px; width: 15%;">Срок годности</td>',
								'<td style="padding: 2px; width: 15%;">Цена</td>',
                                                                '<td style="padding: 2px; width: 15%;">Ист.фин.</td>',
								'<td style="padding: 2px; width: 15%;">Ст.расхода</td>',
                                                                '<td style="padding: 2px; width: 15%;">МО</td>',
                                                                '<td style="padding: 2px; width: 10%;">Партия</td>',
								'</tr><tpl for="."><tr class="x-combo-list-item">',
                                                                '<td style="padding: 2px;">{Drug_Code}&nbsp;</td>',
                                                                '<td style="padding: 2px; text-align: left;">{DocumentUcStr_OstCount}&nbsp;</td>',
                                                                '<td style="padding: 2px;">{[values.DocumentUcStr_Ser ? (values.PrepSeries_isDefect == 1 ? "<font color=#ff0000>"+values.DocumentUcStr_Ser+"</font>" : values.DocumentUcStr_Ser) : ""]}&nbsp;</td>',
								'<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
								'<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
                                                                '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
								'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
                                                                '<td style="padding: 2px;">{Lpu_Nick}&nbsp;</td>',
                                                                '<td style="padding: 2px;">{DrugShipment_Name}&nbsp;</td>',
								'</tr></tpl>',
								'</table>'
							),       
                                                                /*
							tpl: new Ext.XTemplate(
								'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
								'<td style="padding: 2px; width: 10%;">Партия</td>',
								'<td style="padding: 2px; width: 10%;">Серия</td>',
								'<td style="padding: 2px; width: 15%;">Срок годности</td>',
								'<td style="padding: 2px; width: 15%;">Цена</td>',
								'<td style="padding: 2px; width: 10%;">Ставка НДС</td>',
								'<td style="padding: 2px; width: 10%;">Остаток</td>',
								'<td style="padding: 2px; width: 15%;">Ист.фин.</td>',
								'<td style="padding: 2px; width: 15%;">Ст.расхода</td>',
								'</tr><tpl for="."><tr class="x-combo-list-item">',
								'<td style="padding: 2px;">{DrugShipment_Name}&nbsp;</td>',
								'<td style="padding: 2px;">{[values.DocumentUcStr_Ser ? (values.PrepSeries_isDefect == 1 ? "<font color=#ff0000>"+values.DocumentUcStr_Ser+"</font>" : values.DocumentUcStr_Ser) : ""]}&nbsp;</td>',
								'<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
								'<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
								'<td style="padding: 2px;">{DrugNds_Code}&nbsp;</td>',
								'<td style="padding: 2px; text-align: left;">{DocumentUcStr_OstCount}&nbsp;</td>',
								'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
								'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
								'</tr></tpl>',
								'</table>'
							),
                                                                    */
							listeners: {
								'beforeselect': function() {
									// this.findById('EREF_DrugCombo').lastQuery = '';
                                                                       return true;
								}.createDelegate(this),
								'change': function(combo, newValue, oldValue) {
									this.form.findField('DocumentUcStr_OstCount').setValue('');
									this.form.findField('DocumentUcStr_OstEdCount').setValue('');
                                                                        
									this.form.findField('DocumentUcStr_Price').setValue('');
									this.form.findField('DrugNds_id').setValue('');
									this.form.findField('DocumentUcStr_IsNDS').setValue('');
									this.form.findField('DocumentUcStr_Sum').setValue('');
									this.form.findField('DocumentUcStr_SumNds').setValue('');
									this.form.findField('DocumentUcStr_NdsSum').setValue('');
									this.form.findField('DocumentUcStr_Ser').setValue('');
									this.form.findField('PrepSeries_GodnDate').setValue('');
									this.form.findField('PrepSeries_isDefect').setValue('');
									this.cert_form.findField('DocumentUcStr_CertNum').setValue('');
									this.cert_form.findField('DocumentUcStr_CertOrg').setValue('');
									this.cert_form.findField('DrugLabResult_Name').setValue('');
									this.CertDateRange.setValue('');
									//this.form.findField('DocumentUcStr_Count').fireEvent('change', this.form.findField('DocumentUcStr_Count'), '', 1);

									var record = combo.getStore().getById(newValue);
                                                                        //console.log('record'); console.log(record);
									if (record) {;
										this.form.findField('DocumentUcStr_OstCount').setValue(record.get('DocumentUcStr_OstCount'));
										this.form.findField('DocumentUcStr_OstEdCount').setValue(record.get('DocumentUcStr_OstCount')*this.form.findField('Drug_Fas').getValue());
                                                                                
										this.form.findField('DocumentUcStr_Price').setValue(record.get('DocumentUcStr_Price'));
										this.form.findField('DrugNds_id').setValue(record.get('DrugNds_id'));
										this.form.findField('DocumentUcStr_IsNDS').setValue(record.get('DocumentUcStr_IsNDS'));
										this.form.findField('DocumentUcStr_Ser').setValue(record.get('DocumentUcStr_Ser'));
										this.form.findField('PrepSeries_GodnDate').setValue(record.get('PrepSeries_GodnDate'));
										this.form.findField('PrepSeries_isDefect').setValue(record.get('PrepSeries_isDefect'));
										this.cert_form.findField('DocumentUcStr_CertNum').setValue(record.get('DocumentUcStr_CertNum'));
										this.cert_form.findField('DocumentUcStr_CertOrg').setValue(record.get('DocumentUcStr_CertOrg'));
										this.cert_form.findField('DrugLabResult_Name').setValue(record.get('DrugLabResult_Name'));
										if (record.get('DocumentUcStr_CertDate') && record.get('DocumentUcStr_CertGodnDate')) {
											this.CertDateRange.setValue(record.get('DocumentUcStr_CertDate') + ' - ' + record.get('DocumentUcStr_CertGodnDate'));
										}
										//this.form.findField('DocumentUcStr_Count').fireEvent('change', this.form.findField('DocumentUcStr_Count'), this.form.findField('DocumentUcStr_Count').getValue(), 0);
										wnd.setSumFields();
									}

									return true;
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Остаток (ед.уч.)', name: 'DocumentUcStr_OstCount', disabled: true}]
						}, {
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Остаток (ед.доз)', name: 'DocumentUcStr_OstEdCount', disabled: true}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								fieldLabel: 'Кол-во (ед.уч.)',
								name: 'DocumentUcStr_Count',
								allowBlank: false,
								allowNegative: false,
								listeners: {
									'change': function(field, newValue) {
										if (newValue == 0) {
											field.setValue(null);
										}
										wnd.setSumFields();
										wnd.setEdCount();
									}
								}
							}]
						}, {
							layout: 'form',
							items: [{xtype: 'numberfield', fieldLabel: 'Кол-во (ед.доз)', name: 'DocumentUcStr_EdCount', allowNegative: false}]
						}]
					},
					{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								fieldLabel: 'Цена (руб.)',
								name: 'DocumentUcStr_Price',
								allowBlank: false,
								allowNegative: false,
								listeners: {
									'change': function(field, newValue, oldValue) {
										wnd.setSumFields();
									}
								}
							}]
						}, {
							layout: 'form',
							items: [{
								name: 'DocumentUcStr_IsNDS',
								fieldLabel: 'НДС в том числе',
								xtype: 'checkbox',
								listeners: {
									check: function(field, newValue) {
										wnd.setSumFields();
									}
								}
							}]
						}]
					},
					{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{xtype: 'numberfield', fieldLabel: 'Сумма (руб.)', name: 'DocumentUcStr_Sum', allowBlank: false, allowNegative: false}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'swdrugndscombo',
								fieldLabel: 'Ставка НДС',
								name: 'DrugNds_id',
								hiddenName: 'DrugNds_id',
								width: 127,
								allowBlank: false,
								listeners: {
									'change': function(field, newValue, oldValue) {
										wnd.setSumFields();
									}
								}
							}]
						}, {
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: 'Сумма НДС', name: 'DocumentUcStr_SumNds'}]
						}]
					},
					{xtype: 'textfield', fieldLabel: 'Сумма с НДС (руб.)', name: 'DocumentUcStr_NdsSum', allowBlank: false},
					{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'textfield',
								fieldLabel: 'Серия',
								name: 'DocumentUcStr_Ser',
								allowBlank: false,
								listeners: {
									change: function(field) {
										var godn_combo = wnd.form.findField('PrepSeries_GodnDate');
										wnd.setPrepSeriesFields({
											DocumentUcStr_Ser: field.getValue(),
											Drug_id: wnd.drug_combo.getValue(),
											callback: function(data) {
												if ((data && data.PrepSeries_GodnDate && data.PrepSeries_GodnDate.length > 0) || godn_combo.enable_blocked) {
													godn_combo.disable();
												} else {
													godn_combo.enable();
												}
											}
										});
									}
								}
							}]
						}, {
							layout: 'form',
							items: [{
								xtype: 'swdatefield',
								fieldLabel: 'Срок годности до',
								name: 'PrepSeries_GodnDate',
								format: 'd.m.Y',
								width: 127,
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
							}, {
								xtype: 'hidden',
								name: 'PrepSeries_isDefect'
							}, {
								xtype: 'hidden',
								name: 'PrepSeries_id'
							}]
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Причина списания',
							name: 'DocumentUcStr_Reason',
							maxLength: 100,
							anchor: '100%'
						}]
					},
                                         {
					layout: 'form',
                                        id: 'NewDocumentUc_Lpu4FarmStorageForm',
                                        hidden: true,
					items: [{
						hiddenName: 'Lpu_id',
						fieldLabel: 'МО',
                                                id: 'SEW_Lpu4FarmStorage',
                                                autoload: false,
                                                allowBlank: true,
                                                width: 430,
                                                listWidth: 430, 
                                                xtype: 'amm_Lpu4FarmStorageCombo'
                                            }]
                                    }
                                        
				]
			}, {
				xtype: 'fieldset',
				title: 'Сертификат',
                                id: 'NewDocumentUcStrEditCert',
				style: 'margin-left: 0.5em; margin-right: 0.5em; padding-top: 0px; padding-bottom: 0px;',
				autoHeight: true,
				items: [{
					xtype: 'form',
					autoHeight: true,
					id: 'NewDocumentUcStrEditCertForm',
					//style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					labelWidth: 153,
					labelAlign: 'right',
					collapsible: true,
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{xtype: 'textfield', fieldLabel: '№', name: 'DocumentUcStr_CertNum'}]
						}, {
							layout: 'form',
							labelWidth: 170,
							items: [this.CertDateRange]
						}]
					}, {
						xtype: 'textfield',
						fieldLabel: 'Выдан',
						name: 'DocumentUcStr_CertOrg',
						width: 479
					}, {
						xtype: 'swdruglabresultcombo',
						fieldLabel: 'Рез.лаб.исслед.',
						name: 'DrugLabResult_Name',
						forceSelection: false,
						width: 479
					}]
				}, {
					xtype: 'fieldset',
					title: 'Файл',
					autoHeight: true,
					items: [this.FileUploadPanel]
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					var wnd = this.ownerCt;
					wnd.checkDrugShipmentName({
						callback: function() {
							wnd.doSave();
						}
					});
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			/*
			 * Убрал Тагир: Для заказчика не актуально
			 * 
			{
				handler: function() {
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				text: 'Печать'
			},
			*/
			{
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
			items:[form]
		});
		sw.Promed.swNewDocumentUcStrEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('NewDocumentUcStrEditForm').getForm();
		this.cert_form = this.findById('NewDocumentUcStrEditCertForm').getForm();
                if (getGlobalOptions().region.nick == 'ufa') {
                    //alert('2!!!');
                    Ext.getCmp('NewDocumentUcStrEditCert').hide();
                }
                    
	}
});